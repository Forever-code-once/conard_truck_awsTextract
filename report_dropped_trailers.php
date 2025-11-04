<? include('header.php') ?>
<?

	if(!isset($_POST['customer_id'])) $_POST['customer_id'] = 0;
	if(!isset($_POST['trailer_id'])) $_POST['trailer_id'] = 0;
	if(!isset($_POST['date_from'])) $_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) $_POST['date_to'] = date("n/j/Y", time());
	
	if(!isset($_POST['filter_city']))		$_POST['filter_city']="";
	if(!isset($_POST['filter_state']))		$_POST['filter_state']="";
	if(!isset($_POST['filter_zip']))		$_POST['filter_zip']="";

	$data_customers = get_customers();
	$data_trailers = get_trailers();
?>

<form action='' method='post'>
<input type='hidden' name='build_report' value='1'>
<table class='admin_menu1 font_display_section' style='margin:10px;text-align:left'>
<tr>
	<td>Customer</td>
	<td>
		<select name='customer_id' id='customer_id'>
			<option value='0'>All Customers</option>
			<?
			while($row_customer = mysqli_fetch_array($data_customers)) { 
				echo "<option value='$row_customer[id]' ".($row_customer['id'] == $_POST['customer_id'] ? 'selected' : '').">$row_customer[name_company]</option>";
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td>Trailer</td>
	<td>
		<select name='trailer_id' id='trailer_id'>
			<option value='0'>All Trailers</option>
			<?
			while($row_trailer = mysqli_fetch_array($data_trailers)) { 
				echo "<option value='$row_trailer[id]' ".($row_trailer['id'] == $_POST['trailer_id'] ? 'selected' : '').">".(!$row_trailer['active'] ? '(inactive) ' : '')."$row_trailer[trailer_name]</option>";
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td>Date From</td>
	<td><input class='date_picker' name='date_from' id='date_from' value='<?=$_POST['date_from']?>'></td>
</tr>
<tr>
	<td>Date To</td>
	<td><input class='date_picker' name='date_to' id='date_to' value='<?=$_POST['date_to']?>'></td>
</tr>
<tr>
	<td>City</td>
	<td><input class='long' name='filter_city' id='filter_city' value='<?=$_POST['filter_city']?>'></td>
</tr>
<tr>
	<td>State</td>
	<td><input class='long' name='filter_state' id='filter_state' value='<?=$_POST['filter_state']?>'></td>
</tr>
<tr>
	<td>Zip</td>
	<td><input class='long' name='filter_zip' id='filter_zip' value='<?=$_POST['filter_zip']?>'></td>
</tr>
<tr>
	<td></td>
	<td><input type='submit' value='Submit' name='build_report'></td>
</tr>
</table>
</form>
<!---
<h2>&nbsp;&nbsp;Dropped Trailer Report</h2>&nbsp;<br>
----->
<? if(isset($_POST['build_report'])) { 
	$sql = "
		select trailers_dropped.*,
			customers.name_company,
			trailers.trailer_name
		
		from trailers_dropped
			left join customers on customers.id = trailers_dropped.customer_id
			left join trailers on trailers.id = trailers_dropped.trailer_id
		where trailers_dropped.deleted = 0
			".($_POST['date_from'] != '' ? " and trailers_dropped.linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' " : "")."
			".($_POST['date_to'] != '' ? " and trailers_dropped.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59' " : "")."
			".($_POST['trailer_id'] ? " and trailers_dropped.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
			".($_POST['customer_id'] ? " and trailers_dropped.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			".($_POST['filter_city']!="" ? " and trailers_dropped.location_city='".sql_friendly($_POST['filter_city'])."'" : "")."
			".($_POST['filter_state']!="" ? " and trailers_dropped.location_state='".sql_friendly($_POST['filter_state'])."'" : "")."
			".($_POST['filter_zip']!="" ? " and trailers_dropped.location_zip='".sql_friendly($_POST['filter_zip'])."'" : "")."
			
		order by trailers_dropped.trailer_id, trailers_dropped.linedate
	";
	$data = simple_query($sql);
	
	$days_cntr=0;
	$days_tot=0;
	$secs_to_days=(60 * 60 * 24);
	echo "
		<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1200px;text-align:left'>
		<tr>
			<td><b>Trailer Name</b></td>
			<td><b>Location</b></td>
			<td><b>Customer</b></td>
			<td><b>Date</b></td>
			<td><b>Notes</b></td>
			<td><b>Status</b></td>
			<td><b>Completed</b></td>
			<!--
			<td align='right'><b>Days</b></td>
			--->
		</tr>
	";
	while($row = mysqli_fetch_array($data)) 
	{
		$days=0;
		if($row['drop_completed']  > 0)
		{
			$linedate = strtotime(date("m/d/Y",strtotime($row['linedate'])));
     		$linedate_completed = strtotime(date("m/d/Y",strtotime($row['linedate_completed'])));
     		if($row['linedate_completed']=="0000-00-00 00:00:00")		$linedate_completed=time();	
     		
     		$days=0;	
     		
		}
		else
		{
			$linedate = strtotime(date("m/d/Y",strtotime($row['linedate'])));
     		$linedate_completed=time();	
		}
		$arg1=$linedate_completed;
          $arg2=$linedate;  
          				
          $days=(int) ( ($arg1/$secs_to_days) - ($arg2/$secs_to_days));	// + 1;
		
		$days_tot+=$days;
		echo "
			<tr>
				<td><a href='trailer_drop.php?id=$row[id]' target='view_drop_$row[id]'>$row[trailer_name]</a></td>
				<td>$row[location_city], $row[location_state] $row[location_zip]</td>
				<td>".trim($row['name_company'])."</td>
				<td>".date("M j, Y", strtotime($row['linedate']))."</td>
				<td>$row[notes]</td>
				<td>".($row['drop_completed'] > 0 ? 'Completed' : '')."</td>
				<td>".($row['linedate_completed'] > 0 ? date("M j, Y", strtotime($row['linedate_completed'])) : "")."</td>
				<!---
				<td align='right'><span style='color:#".($days > 7 ? "CC0000" : "000000").";'>".$days."</span></td>
				---->
			</tr>
		";
		$days_cntr++;
	}
	echo "<!---
			<tr>
				<td><b>".$days_cntr."</b></td>
				<td colspan='6'><b>Total</b></td>
				
				<td align='right'><b>".$days_tot."</b></td>
				
			</tr>---->
		";
	echo "</table>";
?>
<? } ?>
<script type='text/javascript'>
	$('.date_picker').datepicker();
</script>
<? include('footer.php') ?>