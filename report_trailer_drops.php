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
			while($row_customer = mysqli_fetch_array($data_customers)) 
			{ 
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
			while($row_trailer = mysqli_fetch_array($data_trailers)) 
			{ 
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
<? if(isset($_POST['build_report'])) 
{ 
	/*
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
	
	echo "<b>Trailer Location Report (V2):</b><br>
		<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1100px;text-align:left'>
		<tr>
			<td><b>Trailer Name</b></td>
			<td><b>Location</b></td>
			<td><b>Customer</b></td>
			<td><b>Date</b></td>
			<td><b>Notes</b></td>
			<td><b>Status</b></td>
			<td><b>Completed</b></td>
		</tr>
	";
	while($row = mysqli_fetch_array($data)) 
	{
		echo "
			<tr>
				<td><a href='trailer_drop.php?id=$row[id]' target='view_drop_$row[id]'>$row[trailer_name]</a></td>
				<td>$row[location_city], $row[location_state] $row[location_zip]</td>
				<td>".trim($row['name_company'])."</td>
				<td>".date("M j, Y", strtotime($row['linedate']))."</td>
				<td>$row[notes]</td>
				<td>".($row['drop_completed'] ? 'Completed' : '')."</td>
				<td>".($row['linedate_completed'] > 0 ? date("M j, Y", strtotime($row['linedate_completed'])) : "")."</td>
			</tr>
		";
	}
	echo "</table>";
	*/
	
	
	$sql = "
		select trailers.id,
			trailers.trailer_name
		from trailers
		where trailers.active>0 and trailers.deleted=0
			".($_POST['trailer_id'] ? " and trailers.id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
		order by trailers.trailer_name asc,
			trailers.id asc
	";
	$data = simple_query($sql);
	
	echo "<b>Trailer Location Report (V2):</b><br>
		<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1600px;text-align:left'>
		<tr>
			<td rowspan='2'><b>Trailer Name</b></td>
			<td colspan='6'><b>Last Drop</b></td>
			<td colspan='6'><b>Last Stop</b></td>
		</tr>
		<tr>			
			<td><b>Location</b></td>
			<td><b>Customer</b></td>
			<td><b>Date</b></td>
			<td><b>Notes</b></td>
			<td><b>Status</b></td>
			<td><b>Completed</b></td>
			
			<td><b>Location</b></td>
			<td><b>Customer</b></td>
			<td><b>Date</b></td>
			<td><b>Notes</b></td>
			<td><b>Status</b></td>
			<td><b>Completed</b></td>
		</tr>
	";
	while($row = mysqli_fetch_array($data)) 
	{
		$location="&nbsp;";	
		$comp="&nbsp;";
		$dater="&nbsp;";
		$notes="&nbsp;";
		$drop_done="&nbsp;";
		$comp_date="&nbsp;";
		
		$location2="&nbsp;";
		$comp2="&nbsp;";	
		$dater2="&nbsp;";	
		$notes2="&nbsp;";	
		$drop_done2="&nbsp;";
		$comp_date2="&nbsp;";	
		
		$res=mrr_find_special_trailer_location($row['id']);
		
		//$res['truck_id']=$row['truck_id'];
		//$res['driver_id']=$row['driver_id'];
		//$res['linedate_added']=$row['linedate_added'];
					
		if($res['linedate'] > 0)	
		{
			$location="".$res['location_city'].", ".$res['location_state']." ".$res['location_zip']."";	
			$comp="<a href='admin_customers.php?eid=".$res['customer_id']."' target='_blank'>".$res['name_company']."</a>";
			
			$dater="".date("M j, Y", strtotime($res['linedate']))."";	
			$notes="Dropped";		//$row[notes]
			
			$drop_done="".($res['drop_completed'] > 0 ? 'Completed' : '')."";	
			$comp_date="".($res['linedate_completed'] > 0 ? date("M j, Y", strtotime($res['linedate_completed'])) : "")."";	
		}
		
		//$res['truckid']=$row['truck_id'];
		//$res['driverid']=$row['driver_id'];
					
		if($res['pickup_eta'] > 0)
		{
			$location2="".$res['city']." ".$res['state']." ".$res['zip']."";		
			$comp2="<a href='admin_customers.php?eid=".$res['cust_id']."' target='_blank'>".$res['cust_name']."</a>";
			
			$dater2="".date("M j, Y", strtotime($res['pickup_eta']))."";	
			$notes2="Disp ".($res['stop_type'] > 1 ? "(S)" : "(C)")."";		//$row[notes]
			
			$drop_done2="".($res['stop_completed'] > 0 ? 'Completed' : '')."";	
			$comp_date2="".($res['stop_completed'] > 0 ? date("M j, Y", strtotime($res['stop_completed'])) : "")."";	
		}
		
				
		echo "
			<tr>
				<td><a href='trailer_drop.php?id=".$row['id']."' target='trailer_".$row['id']."'>".$row['trailer_name']."</a></td>
				
				<td>".$location."</td>
				<td>".$comp."</td>
				<td>".$dater."</td>
				<td>".$notes."</td>
				<td>".$drop_done."</td>
				<td>".$comp_date."</td>
				
				<td>".$location2."</td>
				<td>".$comp2."</td>
				<td>".$dater2."</td>
				<td>".$notes2."</td>
				<td>".$drop_done2."</td>
				<td>".$comp_date2."</td>
			</tr>
		";
	}
	echo "</table>";
} 

function mrr_find_special_trailer_location($trailer_id)
{
	//trailer drops
	$res['customer_id']=0;
	$res['name_company']="";
	$res['truck_id']=0;
	$res['driver_id']=0;
	$res['linedate_added']="";
	$res['linedate']="";
	$res['linedate_completed']="";
	$res['drop_completed']=0;
	$res['location_city']="";
	$res['location_state']="";
	$res['location_zip']="";	
	
	//trailer dispatches
	$res['cust_id']=0;
	$res['cust_name']="";
	$res['truckid']=0;
	$res['driverid']=0;
	$res['pickup_eta']="";
	$res['stop_completed']="";			
	$res['stop_type']=0;
	$res['city']="";
	$res['state']="";
	$res['zip']="";	
	
	
	if($trailer_id<=0)		return $res;
	
	//get drops
	$sql = "
		select trailers_dropped.*,
			customers.name_company
		
		from trailers_dropped
			left join customers on customers.id = trailers_dropped.customer_id
		where trailers_dropped.deleted = 0
			and trailers_dropped.trailer_id = '".sql_friendly($trailer_id)."'
			
			".($_POST['date_from'] != '' ? " and trailers_dropped.linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' " : "")."
			".($_POST['date_to'] != '' ? " and trailers_dropped.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59' " : "")."
			
			".($_POST['customer_id'] ? " and trailers_dropped.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			".($_POST['filter_city']!="" ? " and trailers_dropped.location_city='".sql_friendly($_POST['filter_city'])."'" : "")."
			".($_POST['filter_state']!="" ? " and trailers_dropped.location_state='".sql_friendly($_POST['filter_state'])."'" : "")."
			".($_POST['filter_zip']!="" ? " and trailers_dropped.location_zip='".sql_friendly($_POST['filter_zip'])."'" : "")."
			
		order by trailers_dropped.linedate desc
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data)) 
	{
		$res['customer_id']=$row['customer_id'];
		$res['name_company']=$row['name_company'];
		$res['truck_id']=$row['truck_id'];
		$res['driver_id']=$row['driver_id'];
		$res['linedate_added']=$row['linedate_added'];
		$res['linedate']=$row['linedate'];
		$res['linedate_completed']=$row['linedate_completed'];
		$res['drop_completed']=$row['drop_completed'];
		$res['location_city']=$row['location_city'];
		$res['location_state']=$row['location_state'];
		$res['location_zip']=$row['location_zip'];		
	}
	
	//get stops
	$sql = "
		select load_handler_stops.*,
			
			trucks_log.truck_id,
			trucks_log.driver_id,
			trucks_log.customer_id,
			customers.name_company
		
		from load_handler_stops
			left join trucks_log on trucks_log.id = load_handler_stops.trucks_log_id
			left join customers on customers.id = trucks_log.customer_id
			left join load_handler on load_handler.id = load_handler_stops.load_handler_id
		where trucks_log.deleted = 0
			and trucks_log.trailer_id = '".sql_friendly($trailer_id)."'
			and load_handler_stops.deleted = 0
			and load_handler.deleted = 0
			
			".($_POST['date_from'] != '' ? " and load_handler_stops.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' " : "")."
			".($_POST['date_to'] != '' ? " and load_handler_stops.linedate_pickup_eta <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59' " : "")."
			
			".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			".($_POST['filter_city']!="" ? " and load_handler_stops.shipper_city='".sql_friendly($_POST['filter_city'])."'" : "")."
			".($_POST['filter_state']!="" ? " and load_handler_stops.shipper_state='".sql_friendly($_POST['filter_state'])."'" : "")."
			".($_POST['filter_zip']!="" ? " and load_handler_stops.shipper_zip='".sql_friendly($_POST['filter_zip'])."'" : "")."
			
		order by load_handler_stops.linedate_pickup_eta desc
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data)) 
	{
		$res['cust_id']=$row['customer_id'];
		$res['cust_name']=$row['name_company'];
		$res['truckid']=$row['truck_id'];
		$res['driverid']=$row['driver_id'];
		$res['pickup_eta']=$row['linedate_pickup_eta'];
		$res['stop_completed']=$row['linedate_completed'];			
		$res['stop_type']=$row['stop_type_id'];
		$res['city']=$row['shipper_city'];
		$res['state']=$row['shipper_state'];
		$res['zip']=$row['shipper_zip'];		
	}	
	
	return $res;	
}
?>
<script type='text/javascript'>
	$('.date_picker').datepicker();
</script>
<? include('footer.php') ?>