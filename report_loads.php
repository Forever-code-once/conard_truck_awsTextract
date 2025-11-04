<? include('header.php') ?>
<?

	if(!isset($_POST['date_from'])) $_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) $_POST['date_to'] = date("n/j/Y", time());
	if(!isset($_POST['driver_id'])) $_POST['driver_id'] = 0;
	if(!isset($_POST['trailer_id'])) $_POST['trailer_id'] = 0;
	if(!isset($_POST['truck_id'])) $_POST['truck_id'] = 0;
	if(!isset($_POST['customer_id'])) $_POST['customer_id'] = 0;

	/* get the driver list */
	$sql = "
		select *
		
		from drivers
		where deleted = 0
		order by active desc, name_driver_last, name_driver_first
	";
	$data_drivers = simple_query($sql);
	
	/* get the customer list */
	$sql = "
		select *
		
		from customers
		where deleted = 0
		order by name_company
	";
	$data_customers = simple_query($sql);
	
	/* get the traier list */
	$sql = "
		select *
		
		from trailers
		where deleted = 0
		order by active desc, trailer_name
	";
	$data_trailers = simple_query($sql);
	
	/* get the truck list */
	$sql = "
		select *
		
		from trucks
		where deleted = 0
		order by active desc, name_truck
	";
	$data_trucks = simple_query($sql);
?>

<form action='' method='post'>
<input type='hidden' name='build_report' value='1'>
<table class='admin_menu1' style='margin:10px;text-align:left'>
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
	<td>Driver</td>
	<td>
		<select name='driver_id' id='driver_id'>
			<option value='0'>All Drivers</option>
			<?
			while($row_driver = mysqli_fetch_array($data_drivers)) { 
				echo "<option value='$row_driver[id]' ".($row_driver['id'] == $_POST['driver_id'] ? 'selected' : '').">".(!$row_driver['active'] ? '(inactive) ' : '')."$row_driver[name_driver_last], $row_driver[name_driver_first]</option>";
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
	<td>Truck</td>
	<td>
		<select name='truck_id' id='truck_id'>
			<option value='0'>All Trucks</option>
			<?
			while($row_truck = mysqli_fetch_array($data_trucks)) { 
				echo "<option value='$row_truck[id]' ".($row_truck['id'] == $_POST['truck_id'] ? 'selected' : '').">".(!$row_truck['active'] ? '(inactive) ' : '')."$row_truck[name_truck]</option>";
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td>Date From</td>
	<td><input name='date_from' id='date_from' value='<?=$_POST['date_from']?>'></td>
</tr>
<tr>
	<td>Date To</td>
	<td><input name='date_to' id='date_to' value='<?=$_POST['date_to']?>'></td>
</tr>
<tr>
	<td></td>
	<td><input type='submit' value='Submit'></td>
</tr>
</table>
</form>

<? if(isset($_POST['build_report'])) { ?>
	<?
		$sql = "
			select trucks_log.*,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company,
				(select master_load from load_handler where load_handler.id=trucks_log.load_handler_id) as mrr_master_load
			
			from trucks_log
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
			where trucks_log.linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and trucks_log.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))."'
				".($_POST['driver_id'] ? " and trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				".($_POST['date_from'] != '' ? " and linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))."' " : "")."
				".($_POST['date_to'] != '' ? " and linedate >= '".date("Y-m-d", strtotime($_POST['date_to']))."' " : "")."
		";
		$data = simple_query($sql);
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:950px;text-align:left'>
	<tr>
		<td colspan='10'>
			<center>
			<span class='section_heading'>Dispatch Report</span>
			</center>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td>Load ID</td>
		<td>Dispatch ID</td>
		<td>Driver</td>
		<td>Origin</td>
		<td>Destination</td>
		<td align='right'>Miles</td>
		<td align='right'>Deadhead</td>
		<td>Date</td>
		<td>Truck</td>
		<td>Trailer</td>
		<td>Customer</td>
		<td>MasterLoad</td>
	</tr>
	<?
		$counter = 0;
		while($row = mysqli_fetch_array($data)) {
			$counter++;
			
			$master_flag="<span class='mrr_link_like_on' onClick='mrr_graduate_master_load(".$row['load_handler_id'].",1);'>No</span>";	//make it a master load
			if($row['mrr_master_load'] > 0)
			{
				$master_flag="<span class='mrr_link_like_on' onClick='mrr_graduate_master_load(".$row['load_handler_id'].",0);'>Yes</span>";	//remove as a master load
			}
			
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
					<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
					<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
					<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
					<td nowrap>$row[origin]</td>
					<td nowrap>$row[destination]</td>
					<td align='right'>".number_format($row['miles'])."</td>
					<td align='right'>".number_format($row['miles_deadhead'])."</td>
					<td nowrap>".date("M j, Y", strtotime($row['linedate']))."</td>
					<td nowrap>$row[name_truck]</td>
					<td nowrap>$row[trailer_name]</td>
					<td nowrap>$row[name_company]</td>
					<td nowrap>".$master_flag." <span id='load_master_".$row['load_handler_id']."'></span></td>
				</tr>
			";
		}
	?>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
	function mrr_graduate_master_load(id,flg)
	{		
		flg_val="regular";
		if(flg > 0)	flg_val="Master";
		$('#load_master_'+id+'').html('');
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_graduate_load_to_master_load",
			   data: {
			   		"load_id":id,
			   		"master_load":flg
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		$.noticeAdd({text: "Load "+id+" has been set as a "+flg_val+" Load successfully."});
			   		$('#load_master_'+id+'').html(flg_val);
			   }	
		});
	}
</script>
<? include('footer.php') ?>