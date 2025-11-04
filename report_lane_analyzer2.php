<?
$usetitle = "Report - Lane Analyzer V2";
$use_title = "Report - Lane Analyzer V2";
?>
<? include('header.php') ?>
<?
	if(!isset($_POST['date_from']))			$_POST['date_from']=date("m/01/Y");
	if(!isset($_POST['date_to']))				$_POST['date_to']=date("m/d/Y");
	
	if(!isset($_POST['stop_1_name']))			$_POST['stop_1_name']="";
	if(!isset($_POST['stop_1_addr']))			$_POST['stop_1_addr']="";
	if(!isset($_POST['stop_1_city']))			$_POST['stop_1_city']="";
	if(!isset($_POST['stop_1_state']))			$_POST['stop_1_state']="";
	if(!isset($_POST['stop_1_zip']))			$_POST['stop_1_zip']="";
	
	if(!isset($_POST['stop_2_name']))			$_POST['stop_2_name']="";
	if(!isset($_POST['stop_2_addr']))			$_POST['stop_2_addr']="";
	if(!isset($_POST['stop_2_city']))			$_POST['stop_2_city']="";
	if(!isset($_POST['stop_2_state']))			$_POST['stop_2_state']="";
	if(!isset($_POST['stop_2_zip']))			$_POST['stop_2_zip']="";
	
	if(!isset($_POST['customer_id']))			$_POST['customer_id']=0;
	if(!isset($_POST['truck_id']))			$_POST['truck_id']=0;
	if(!isset($_POST['trailer_id']))			$_POST['trailer_id']=0;
	if(!isset($_POST['driver_id']))			$_POST['driver_id']=0;
	
	$mrr_sql="";
	if(isset($_POST['run_report']))
	{
     	//try dispatch stop match
     	$mrr_adder="";
     	if($_POST['date_from']!="")			$mrr_adder.=" and trucks_log.linedate_pickup_eta>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'";
     	if($_POST['date_to']!="")			$mrr_adder.=" and trucks_log.linedate_pickup_eta<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'";
     	
     	if($_POST['truck_id'] > 0)			$mrr_adder.=" and trucks_log.truck_id='".sql_friendly($_POST['truck_id'])."'";
     	if($_POST['trailer_id'] > 0)			$mrr_adder.=" and trucks_log.trailer_id='".sql_friendly($_POST['trailer_id'])."'";
     	
     	if($_POST['customer_id'] > 0)			$mrr_adder.=" and trucks_log.customer_id='".sql_friendly($_POST['customer_id'])."'";
     	
     	if($_POST['driver_id'] > 0)			$mrr_adder.=" and (trucks_log.driver_id='".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id='".sql_friendly($_POST['driver_id'])."')";
     	
     	$stop1_adder="";
     	if(trim($_POST['stop_1_city'])!="")	$stop1_adder.="and (trucks_log.origin='".sql_friendly($_POST['stop_1_city'])."' or trucks_log.origin='".sql_friendly(strtolower($_POST['stop_1_city']))."' or trucks_log.origin='".sql_friendly(strtoupper($_POST['stop_1_city']))."')";	
     	if(trim($_POST['stop_1_state'])!="")	$stop1_adder.="and (trucks_log.origin_state='".sql_friendly($_POST['stop_1_state'])."' or trucks_log.origin_state='".sql_friendly(strtolower($_POST['stop_1_state']))."' or trucks_log.origin_state='".sql_friendly(strtoupper($_POST['stop_1_state']))."')";	
     	
     	$stop2_adder="";
     	if(trim($_POST['stop_2_city'])!="")	$stop2_adder.="and (trucks_log.destination='".sql_friendly($_POST['stop_2_city'])."' or trucks_log.destination='".sql_friendly(strtolower($_POST['stop_2_city']))."' or trucks_log.destination='".sql_friendly(strtoupper($_POST['stop_2_city']))."')";	
     	if(trim($_POST['stop_2_state'])!="")	$stop2_adder.="and (trucks_log.destination_state='".sql_friendly($_POST['stop_2_state'])."' or trucks_log.destination_state='".sql_friendly(strtolower($_POST['stop_2_state']))."' or trucks_log.destination_state='".sql_friendly(strtoupper($_POST['stop_2_state']))."')";	
     	     	
     	$sql = "
     		select trucks_log.*,
     			customers.name_company as customer_name,
     			(select CONCAT(name_driver_first, ' ', name_driver_last) from drivers where drivers.id=trucks_log.driver_id) as driver_name,
     			(select CONCAT(name_driver_first, ' ', name_driver_last) from drivers where drivers.id=trucks_log.driver2_id) as driver2_name,
     			(select trailer_name from trailers where trailers.id=trucks_log.trailer_id) as trailer_name,
     			(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_name,
     			(select actual_fuel_charge_per_mile from load_handler where load_handler.id=trucks_log.load_handler_id) as fuel_per_mile,
     			(select load_handler_stops.linedate_pickup_eta from load_handler_stops where load_handler_stops.trucks_log_id=trucks_log.id order by load_handler_stops.linedate_pickup_eta limit 1) as pickup_time
     		from trucks_log
     			left join customers on customers.id=trucks_log.customer_id
     		where trucks_log.deleted = 0
     			and trucks_log.dispatch_completed > 0
     			".$mrr_adder."
     			".$stop1_adder."
     			".$stop2_adder."
     		order by trucks_log.linedate_pickup_eta asc,trucks_log.load_handler_id asc
     	";
     	$mrr_sql=$sql;
     	$data = simple_query($sql);     	
	}	
	
	ob_start();
	
	
	//get the truck list
	$sql = "
		select *		
		from trucks
		where deleted = 0
		order by active desc, name_truck
	";
	$data_trucks = simple_query($sql);
	
	//get the traier list
	$sql = "
		select *		
		from trailers
		where deleted = 0
		order by active desc, trailer_name
	";
	$data_trailers = simple_query($sql);
	
	//get the driver list
	$sql = "
		select *		
		from drivers
		where deleted = 0
		order by active desc, name_driver_last, name_driver_first
	";
	$data_drivers = simple_query($sql);
	
	//get customers
	$sql = "
		select *		
		from customers
		where deleted = 0
		order by active desc, name_company
	";
	$data_cust = simple_query($sql);
?>


<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left; width:1800px;'>
	<div style='float:right; width:200px;'><a href='report_lane_analyzer.php'>View Version 1</a></div>
	<h1>Lane Analysis Report - V2</h1>
</div>
<br>
<br>
<br>
<table class='admin_menu1 font_display_section' style='text-align:left; width:1800px; margin:10px'>
<tr>
	<td><b>Stop</b></td>
	<td colspan='2'><b>Shipper Name</b></td>
	<td><b>Address</b></td>
	<td><b>City</b></td>
	<td><b>State</b></td>
	<td><b>Zip</b></td>
</tr>
<tr>
	<td><b>Origin</b></td>
	<td colspan='2'><input type='text' name='stop_1_name' id='stop_1_name' value='<?= $_POST['stop_1_name'] ?>' class='input_normal'></td>
	<td><input type='text' name='stop_1_addr' id='stop_1_addr' value='<?= $_POST['stop_1_addr'] ?>' class='input_normal'></td>
	<td><input type='text' name='stop_1_city' id='stop_1_city' value='<?= $_POST['stop_1_city'] ?>' class='input_medium'></td>
	<td><input type='text' name='stop_1_state' id='stop_1_state' value='<?= $_POST['stop_1_state'] ?>' class='input_short'></td>
	<td><input type='text' name='stop_1_zip' id='stop_1_zip' value='<?= $_POST['stop_1_zip'] ?>' class='input_medium'></td>
</tr>
<tr>
	<td><b>Destination</b></td>
	<td colspan='2'><input type='text' name='stop_2_name' id='stop_2_name' value='<?= $_POST['stop_2_name'] ?>' class='input_normal'></td>
	<td><input type='text' name='stop_2_addr' id='stop_2_addr' value='<?= $_POST['stop_2_addr'] ?>' class='input_normal'></td>
	<td><input type='text' name='stop_2_city' id='stop_2_city' value='<?= $_POST['stop_2_city'] ?>' class='input_medium'></td>
	<td><input type='text' name='stop_2_state' id='stop_2_state' value='<?= $_POST['stop_2_state'] ?>' class='input_short'></td>
	<td><input type='text' name='stop_2_zip' id='stop_2_zip' value='<?= $_POST['stop_2_zip'] ?>' class='input_medium'></td>
</tr>
<tr>
	<td colspan='7'>&nbsp;NOTE: Shipper Name, Address, and Zip are not used at this time.  City, State, Date Range, and truck/trailer/driver optional select boxes are.</td>
</tr>
<tr>
	<td><b>Date Range</b></td>
	<td>
		From <input type='text' class='datepicker' name='date_from' id='date_from' value='<?= $_POST['date_from'] ?>' style='width:75px;'> 
		To <input type='text' class='datepicker' name='date_to' id='date_to' value='<?= $_POST['date_to'] ?>' style='width:75px;'>
	</td>
	<td>
		<select name='customer_id' id='customer_id'>
			<option value='0'>All Customers</option>
			<?
			while($row_cust = mysqli_fetch_array($data_cust)) 
			{ 
				echo "<option value='$row_cust[id]' ".($row_cust['id'] == $_POST['customer_id'] ? 'selected' : '').">".(!$row_cust['active'] ? '(inactive) ' : '')."$row_cust[name_company]</option>";
			}
			?>
		</select>
	</td>
	<td>
		<select name='driver_id' id='driver_id'>
			<option value='0'>All Drivers</option>
			<?
			while($row_driver = mysqli_fetch_array($data_drivers)) 
			{ 
				echo "<option value='$row_driver[id]' ".($row_driver['id'] == $_POST['driver_id'] ? 'selected' : '').">".(!$row_driver['active'] ? '(inactive) ' : '')."$row_driver[name_driver_last], $row_driver[name_driver_first]</option>";
			}
			?>
		</select>
	</td>	
	<td>
		<select name='truck_id' id='truck_id'>
			<option value='0'>All Trucks</option>
			<?
			while($row_truck = mysqli_fetch_array($data_trucks)) 
			{ 
				echo "<option value='$row_truck[id]' ".($row_truck['id'] == $_POST['truck_id'] ? 'selected' : '').">".(!$row_truck['active'] ? '(inactive) ' : '')."$row_truck[name_truck]</option>";
			}
			?>
		</select>	
	</td>
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
	<td><input type='submit' name='run_report' id='run_report' value='Run'></td>
</tr>
</table>
<?
 	//echo "<br><b>Query Run:</b><br>".$mrr_sql."<br>"
?>
<div style='clear:both'></div>
<table class='admin_menu1 font_display_section' style='text-align:left; width:1800px; margin:10px'>
<tr>
	<td><b>Load</b></td>
	<td><b>Dispatch</b></td>
	<td><b>PickupETA</b></td>
	<td><b>Driver</b></td>
	<td><b>Truck</b></td>
	<td><b>Trailer</b></td>
	<td><b>Customer</b></td>
	<td><b>Origin</b></td>
	<td><b>State</b></td>
	<td><b>Dest</b></td>
	<td><b>State</b></td>
	<td align='right'><b>Miles</b></td>
	<td align='right'><b>Gals</b></td>
	<td align='right'><b>Fuel</b></td>
	<td align='right'><b>DailyCost</b></td>
	<td align='right'><b>Labor(Miles)</b></td>
	<td align='right'><b>Labor(Hourly)</b></td>
	<td align='right'><b>Full Cost</b></td>
	<td align='right'><b>Profit</b></td>
</tr>
<? 
	$hub_city="la vergne";			$hub_city2="lavergne";				$hub_state="tn";				$hub_zip="37086";			//back to hub info...
	
	
	$tot_miles=0;
	$tot_gals=0;
	$tot_fuel=0;
	$tot_dcost=0;
	$tot_labor=0;
	$tot_hourly=0;
	$tot_fcost=0;
	$tot_profit=0;	
	
	$counter = 0;
	while($row = mysqli_fetch_array($data)) 
	{
		$sub_miles=0;
		$sub_gals=0;
		$sub_fuel=0;
		$sub_dcost=0;
		$sub_labor=0;
		$sub_hourly=0;
		$sub_fcost=0;
		$sub_profit=0;			
		
		$all_miles=($row['miles'] + $row['miles_deadhead'] + $row['loaded_miles_hourly'] + $row['miles_deadhead_hourly']);
		$labor_miles=($row['miles'] + $row['miles_deadhead']);
		$pta="".date("m/d/Y H:i", strtotime($row['pickup_time']))."";			//pickup_time
		//$pta="".date("m/d/Y H:i", strtotime($row['linedate_pickup_eta']))."";	//dispatch date...
		echo "
			<tr class='".($counter%2==0 ? "even" : "odd")."'>
				<td valign='top'><a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank'>".$row['load_handler_id']."</a></td>
				<td valign='top'><a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
				<td valign='top'>".$pta."</td>
								
				<td valign='top'>
					<a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driver_name']."</a>
					".($row['driver2_id'] > 0  ? "<br><a href='admin_drivers.php?id=".$row['driver2_id']."' target='_blank'>".$row['driver2_name']."</a>" : "")."
				</td>
				<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['truck_name']."</a></td>
				<td valign='top'><a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailer_name']."</a></td>
				
				<td valign='top'>".$row['customer_name']."</td>				
				<td valign='top'>".$row['origin']."</td>
				<td valign='top'>".$row['origin_state']."</td>
				<td valign='top'>".$row['destination']."</td>
				<td valign='top'>".$row['destination_state']."</td>
				<td valign='top' align='right'>".number_format($all_miles,2)."</td>
				<td valign='top' align='right'>".($row['avg_mpg'] > 0 ? number_format(($all_miles / $row['avg_mpg']),2) : "0.00")."</td>
				<td valign='top' align='right'>$".number_format(($all_miles * $row['fuel_per_mile']),2)."</td>
				<td valign='top' align='right'>$".($row['daily_run_otr'] > 0 ? number_format(($row['daily_run_otr'] * $row['daily_cost']),2) : "0.00")."</td>
				<td valign='top' align='right'>$".($labor_miles > 0 ? number_format(($labor_miles * $row['labor_per_mile']),2) : "0.00")."</td>
				<td valign='top' align='right'>$".($row['hours_worked'] > 0 ? number_format(($row['hours_worked'] * $row['labor_per_hour']),2) : "0.00")."</td>
				<td valign='top' align='right'>$".number_format($row['cost'],2)."</td>
				<td valign='top' align='right'>$".number_format($row['profit'],2)."</td>
			</tr>
		";	
		
		$sub_miles+=$all_miles;
		if($row['avg_mpg'] > 0)			$sub_gals+=($all_miles/$row['avg_mpg']);
		$sub_fuel+=($all_miles * $row['fuel_per_mile']);
		if($row['daily_run_otr'] > 0)		$sub_dcost+=($row['daily_run_otr'] * $row['daily_cost']);
		if($labor_miles > 0)			$sub_labor+=($labor_miles * $row['labor_per_mile']);
		if($row['hours_worked'] > 0)		$sub_hourly+=($row['hours_worked'] * $row['labor_per_hour']);
		$sub_fcost+=$row['cost'];
		$sub_profit+=$row['profit'];	
		
		//this is a load that starts with the trip, so now track it back to the conard terminal....or at least 
		$not_at_hub=1;
		if(trim(strtolower($row['destination']))==$hub_city  && trim(strtolower($row['destination_state']))==$hub_state)		$not_at_hub=0;		//ALREADY home town...hub
          if(trim(strtolower($row['destination']))==$hub_city2 && trim(strtolower($row['destination_state']))==$hub_state)		$not_at_hub=0;		//ALREADY home town...hub
		
		
		$cntr2=0;
		$sql2 = "
     		select trucks_log.*,
     			customers.name_company as customer_name,
     			(select CONCAT(name_driver_first, ' ', name_driver_last) from drivers where drivers.id=trucks_log.driver_id) as driver_name,
     			(select CONCAT(name_driver_first, ' ', name_driver_last) from drivers where drivers.id=trucks_log.driver2_id) as driver2_name,
     			(select trailer_name from trailers where trailers.id=trucks_log.trailer_id) as trailer_name,
     			(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_name,
     			(select actual_fuel_charge_per_mile from load_handler where load_handler.id=trucks_log.load_handler_id) as fuel_per_mile,
     			(select load_handler_stops.linedate_pickup_eta from load_handler_stops where load_handler_stops.trucks_log_id=trucks_log.id order by load_handler_stops.linedate_pickup_eta limit 1) as pickup_time
     		from trucks_log
     			left join customers on customers.id=trucks_log.customer_id
     		where trucks_log.deleted = 0
     			and trucks_log.dispatch_completed > 0
     			and trucks_log.truck_id='".sql_friendly($row['truck_id'])."'
     			and trucks_log.id!='".sql_friendly($row['id'])."'
     			and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($row['linedate_pickup_eta']))." 00:00:00'
     		order by trucks_log.linedate_pickup_eta asc,trucks_log.load_handler_id asc
     	";
     	$data2 = simple_query($sql2); 
		while($row2 = mysqli_fetch_array($data2))
		{			
			if($not_at_hub==1)
			{
     			$all_miles=($row2['miles'] + $row2['miles_deadhead'] + $row2['loaded_miles_hourly'] + $row2['miles_deadhead_hourly']);
				$labor_miles=($row2['miles'] + $row2['miles_deadhead']);
     			
     			$pta="".date("m/d/Y H:i", strtotime($row2['pickup_time']))."";			//pickup_time
				//$pta="".date("m/d/Y H:i", strtotime($row2['linedate_pickup_eta']))."";	//dispatch date...
				
     			echo "
          			<tr style='background-color:#FFFFFF;'>
          				<td valign='top'>-----<a href='manage_load.php?load_id=".$row2['load_handler_id']."' target='_blank'>".$row2['load_handler_id']."</a></td>
          				<td valign='top'><a href='add_entry_truck.php?load_id=".$row2['load_handler_id']."&id=".$row2['id']."' target='_blank'>".$row2['id']."</a></td>
          				<td valign='top'>".$pta."</td>
          				
          				<td valign='top'>
							<a href='admin_drivers.php?id=".$row2['driver_id']."' target='_blank'>".$row2['driver_name']."</a>
							".($row2['driver2_id'] > 0  ? "<br><a href='admin_drivers.php?id=".$row2['driver2_id']."' target='_blank'>".$row2['driver2_name']."</a>" : "")."
						</td>
          				<td valign='top'><a href='admin_trucks.php?id=".$row2['truck_id']."' target='_blank'>".$row2['truck_name']."</a></td>
          				<td valign='top'><a href='admin_trailers.php?id=".$row2['trailer_id']."' target='_blank'>".$row2['trailer_name']."</a></td>
          				
          				<td valign='top'>".$row2['customer_name']."</td>			
          				<td valign='top'>".$row2['origin']."</td>
          				<td valign='top'>".$row2['origin_state']."</td>
          				<td valign='top'>".$row2['destination']."</td>
          				<td valign='top'>".$row2['destination_state']."</td>
          				<td valign='top' align='right'>".number_format($all_miles,2)."</td>
          				<td valign='top' align='right'>".($row2['avg_mpg'] > 0 ? number_format(($all_miles / $row2['avg_mpg']),2) : "0.00")."</td>
          				<td valign='top' align='right'>$".number_format(($all_miles * $row2['fuel_per_mile']),2)."</td>
          				<td valign='top' align='right'>$".($row2['daily_run_otr'] > 0 ? number_format(($row2['daily_run_otr'] * $row2['daily_cost']),2) : "0.00")."</td>
          				<td valign='top' align='right'>$".($labor_miles > 0 ? number_format(($labor_miles * $row2['labor_per_mile']),2) : "0.00")."</td>
          				<td valign='top' align='right'>$".($row2['hours_worked'] > 0 ? number_format(($row2['hours_worked'] * $row2['labor_per_hour']),2) : "0.00")."</td>
          				<td valign='top' align='right'>$".number_format($row2['cost'],2)."</td>
          				<td valign='top' align='right'>$".number_format($row2['profit'],2)."</td>
          			</tr>
          		";	
          		
          		if(trim(strtolower($row2['destination']))==$hub_city  && trim(strtolower($row2['destination_state']))==$hub_state)		$not_at_hub=0;		//home town...hub
          		if(trim(strtolower($row2['destination']))==$hub_city2 && trim(strtolower($row2['destination_state']))==$hub_state)		$not_at_hub=0;		//home town...hub
          		
          		$sub_miles+=$all_miles;
          		if($row2['avg_mpg'] > 0)			$sub_gals+=($all_miles / $row2['avg_mpg']);
          		$sub_fuel+=($all_miles * $row2['fuel_per_mile']);
          		if($row2['daily_run_otr'] > 0)	$sub_dcost+=($row2['daily_run_otr'] * $row2['daily_cost']);
          		if($labor_miles > 0)			$sub_labor+=($labor_miles * $row2['labor_per_mile']);
          		if($row2['hours_worked'] > 0)		$sub_hourly+=($row2['hours_worked'] * $row2['labor_per_hour']);
          		$sub_fcost+=$row2['cost'];
          		$sub_profit+=$row2['profit'];	
          		
          		$cntr2++;
			}
		}
		if($cntr2 > 0)
		{
     		echo "
     			<tr style='background-color:#FFFFFF;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' colspan='3'><b>".($not_at_hub==0  ? "Returned to Hub" : "Still Running...")."</b></td>
     				<td valign='top' colspan='3'>{".$cntr2." Dispatch(es) found.}</td>     				
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' align='right'><b>".number_format($sub_miles,2)."</b></td>
     				<td valign='top' align='right'><b>".number_format($sub_gals,2)."</b></td>
     				<td valign='top' align='right'><b>$".number_format($sub_fuel,2)."</b></td>
     				<td valign='top' align='right'><b>$".number_format($sub_dcost,2)."</b></td>
     				<td valign='top' align='right'><b>$".number_format($sub_labor,2)."</b></td>
     				<td valign='top' align='right'><b>$".number_format($sub_hourly,2)."</b></td>
     				<td valign='top' align='right'><b>$".number_format($sub_fcost,2)."</b></td>
     				<td valign='top' align='right'><b>$".number_format($sub_profit,2)."</b></td>
     			</tr>
     			<tr style='background-color:#FFFFFF;'>
     				<td valign='top' colspan='19'>&nbsp;</td>
     			</tr>
     		";		
		}
		else
		{
			echo "
     			<tr style='background-color:#FFFFFF;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' colspan='18'><b>No More Dispatches for this truck.</b></td>
     			</tr>
     			<tr style='background-color:#FFFFFF;'>
     				<td valign='top' colspan='19'>&nbsp;</td>
     			</tr>
     		";	
		}	
				
		$tot_miles+=$sub_miles;
		$tot_gals+=$sub_gals;
		$tot_fuel+=$sub_fuel;
		$tot_dcost+=$sub_dcost;
		$tot_labor+=$sub_labor;
		$tot_hourly+=$sub_hourly;
		$tot_fcost+=$sub_fcost;
		$tot_profit+=$sub_profit;	
		
		$counter++;
	}
?>
<tr>
	<td><b>Load</b></td>
	<td><b>Dispatch</b></td>
	<td><b>PickupETA</b></td>
	<td><b>Driver</b></td>
	<td><b>Truck</b></td>
	<td><b>Trailer</b></td>
	<td><b>Customer</b></td>
	<td><b>Origin</b></td>
	<td><b>State</b></td>
	<td><b>Dest</b></td>
	<td><b>State</b></td>
	<td align='right'><b>Miles</b></td>
	<td align='right'><b>Gals</b></td>
	<td align='right'><b>Fuel</b></td>
	<td align='right'><b>DailyCost</b></td>
	<td align='right'><b>Labor(Miles)</b></td>
	<td align='right'><b>Labor(Hourly)</b></td>
	<td align='right'><b>Full Cost</b></td>
	<td align='right'><b>Profit</b></td>
</tr>
<?	
	echo "
			<tr>
				<td valign='top'><b>Total</b></td>
				<td valign='top'><b>".$counter."</b></td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top' align='right'>".number_format($tot_miles,2)."</td>
				<td valign='top' align='right'>".number_format($tot_gals,2)."</td>
				<td valign='top' align='right'>$".number_format($tot_fuel,2)."</td>
				<td valign='top' align='right'>$".number_format($tot_dcost,2)."</td>
				<td valign='top' align='right'>$".number_format($tot_labor,2)."</td>
				<td valign='top' align='right'>$".number_format($tot_hourly,2)."</td>
				<td valign='top' align='right'>$".number_format($tot_fcost,2)."</td>
				<td valign='top' align='right'>$".number_format($tot_profit,2)."</td>
			</tr>
		";	
	if($counter>0)
	{
		echo "
			<tr>
				<td valign='top'><b>Average</b></td>
				<td valign='top'><b>&nbsp;</b></td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top' align='right'>".number_format(($tot_miles/$counter),2)."</td>
				<td valign='top' align='right'>".number_format(($tot_gals/$counter),2)."</td>
				<td valign='top' align='right'>$".number_format(($tot_fuel/$counter),2)."</td>
				<td valign='top' align='right'>$".number_format(($tot_dcost/$counter),2)."</td>
				<td valign='top' align='right'>$".number_format(($tot_labor/$counter),2)."</td>
				<td valign='top' align='right'>$".number_format(($tot_hourly/$counter),2)."</td>
				<td valign='top' align='right'>$".number_format(($tot_fcost/$counter),2)."</td>
				<td valign='top' align='right'>$".number_format(($tot_profit/$counter),2)."</td>
			</tr>
		";	
	}	
?>
</table>
</form>

<?
	$pdf = ob_get_contents();
	ob_end_clean();
?>
<script type='text/javascript'>
	
	
	$().ready(function() {
		/*
		$('.tablesorter').tablesorter({
		   			headers: {         				
		   				5: {sorter:'currency'},
		   				6: {sorter:'currency'},
		   				7: {sorter:'currency'},
		   				11: {sorter: false}
		   			}
		   				
		});
		*/
		$('.datepicker').datepicker();
	});
	
</script>
<?
	$link = print_contents('lane_analyzer_'.createuuid(), $pdf);

	echo "
		<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
			<a href='$link'>Printed Version</a>
		</div>
		<div style='clear:both'></div>
		
	";

	echo $pdf;
	
?>
<? include('footer.php') ?>