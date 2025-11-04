<?
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set("max_input_vars","30000");	
ini_set('max_execution_time','35000');
set_time_limit(35000);
	
$use_title="Conard Logistics - State Mileage Report";
$usetitle="Conard Logistics - State Mileage Report";
?>
<? include('header.php') ?>
<?
	if(isset($_GET['date_from'])) {
		$_POST['date_from'] = $_GET['date_from'];
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['date_to'])) {
		$_POST['date_to'] = $_GET['date_to'];
		$_POST['build_report'] = 1;
	}
	
	if(!isset($_POST['date_from']))	$_POST['date_from']=date("m/01/Y");
	if(!isset($_POST['date_to']))		$_POST['date_to']=date("m/d/Y");
	
	if(!isset($_POST['truck_id'] ))	$_POST['truck_id']=0;
	
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}
	
	$uuid = createuuid();
	$excel_filename = "conard_$uuid.xls";
	$excel_link="https://trucking.conardtransportation.com/temp/$excel_filename";
?>
<div class='section_heading' style='margin-left:10px;'>State Mileage Report</div>
<?
	$rfilter = new report_filter();
	$rfilter->show_date_range 		= true;
	$rfilter->show_truck 			= true;
	//$rfilter->show_single_date 		= true;
	//$rfilter->show_leased_from		= true;
	$rfilter->mrr_show_zero_value		= true;
	$rfilter->mrr_send_email_here		= true;
	$rfilter->show_font_size			= true;
	$rfilter->show_filter();
?>
<br>
<table class='font_display_section' style='text-align:left;width:1000px;'>
<tr>
	<td>
<?	
	//get states array
	$states[0]="";		$snames[0]="";		$miles_tot[0]=0;	$stops[0]=0;
	$scntr=0;
	
	$sql = "
		select states.*		
		from states	
		where order_by_code >= 2 and order_by_code <= 53
		order by state_code asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$scntr++;		//increment so that 0 is left blank.... = "state not found"
		
		$states[$scntr]=trim($row['state_code']);
		$snames[$scntr]=trim($row['state_description']);
		$stops[$scntr]=0;
		$miles_tot[$scntr]=0;
	}
	
	//Pricing Export File
	$export_file = "PickupETA".chr(9).
				"Load".chr(9).
				"Dispatch".chr(9).
				"Driver".chr(9).
				"Truck".chr(9).
				"Type".chr(9).
				"Shipper".chr(9).
				"Address".chr(9).
				"City".chr(9).
				"State".chr(9).
				"Zip".chr(9).
				"Origin".chr(9).
				"Destination".chr(9).
				"PC Miles".chr(9);				
	$export_file .= chr(13);	
			
	$stylex=" style='font-weight:bold;'";
	$mrr_total_head = " style='font-weight:bold; width:1000px; text-align:right;'";
	$tablex=" border='1' cellpadding='1' cellspacing='1' width='1200'";
	$headerx=" style='background-color:#CCCCFF;'";
	
	function mrr_find_distance_traveled($start_date,$end_date,$truck_id=0)
	{
		global $scntr;
		global $states;
		
		global $stylex;
		global $mrr_total_head;
		global $tablex;
		global $headerx;
		global $mrr_use_styles;
				
		$res['miles']=0;
		
		$cntr=0;
		$arr[0]="";
		$mil[0]=0;
		
		$prev_date="";
		
		$tab="<br>
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">PN Tracking Details</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter' width='1600'")." cellpadding='0' cellspacing='0' border='0'>
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th valign='top'><b>DateTime</b></th>
				<th valign='top'><b>Latitude</b></th>
				<th valign='top'><b>Longitude</b></th>
				<th valign='top'><b>Location</b></th>
				<th valign='top' align='right'><b>Odometer</b></th>
				<th valign='top' align='right'><b>States</b></th>
				<th valign='top' align='right'><b>Found In</b></th>
				<th valign='top' align='right'><b>Split Miles</b></th>
				<th valign='top' align='right'><b>Running</b></th>
			</tr>
			</thead>
			<tbody>
		";
		$counter=0;
		
		$sql = "
			select DISTINCT(  DATE_FORMAT(linedate_added,'%Y-%m-%d %H:%i')   ) as stamp,
				latitude,longitude,
				location,
				gps_odometer,
				gps_rolling_odometer,
				performx_odometer
               from conard_trucking_logs.truck_tracking 
               where gps_odometer > 0
               	and linedate_added >= '".date("Y-m-d",strtotime($start_date))." 00:00:00' 
                 	and linedate_added <= '".date("Y-m-d",strtotime($end_date))." 23:59:59' 
                 	".($truck_id > 0 ? " and truck_id = '".sql_friendly($truck_id)."'" : "")."
               order by stamp asc,gps_odometer desc 
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			if($prev_date!=$row['stamp'])
			{     			
     			$test=trim($row['location']);
     			$cur_miles=$row['gps_odometer'];
     			$use_miles=0;
     			
     			$prev_date=$row['stamp'];
     			
     			//see how many states should be included based on location text.
     			$found_count=0;
     			$located="";
     			for($i=1;$i <=$scntr; $i++)
     			{
     				if(substr_count($test,", ".$states[$i]."") > 0)
     				{
     					$found_count++;	
     					$located.=" ".$states[$i];
     				}	
     			}
     			
     			//see how mileage should be divided...
     			if($found_count==0)
     			{
     				$use_miles=$cur_miles;
     			}
     			else
     			{
     				$use_miles=$cur_miles / $found_count;	
     			}		
     			
     			//add miles to state(s)
     			for($i=1;$i <=$scntr; $i++)
     			{
     				if(substr_count($test,", ".$states[$i]."") > 0)
     				{
     					$found_it=0;
     					for($x=0;$x < $cntr; $x++)
     					{
     						if($arr[$x] == $states[$i])
     						{
     							$found_it=1;
     							$mil[$x]	+= $use_miles;
     						}
     					}
     					if($found_it==0)
     					{	//not found, so make the state entry
     						$arr[$cntr] = trim($states[$i]);
     						$mil[$cntr] = $use_miles;	
     						$cntr++;
     					}	
     				}	
     			}
     			$res['miles']+=$cur_miles;
     			
     			$tab.="
     				<tr class='".($counter%2==0 ? "even" : "odd")."'>
     					<td valign='top'>".date("m/d/Y H:i",strtotime($row['stamp']))."</td>	
     					<td valign='top'>".$row['latitude']."</td>	
     					<td valign='top'>".$row['longitude']."</td>	
     					<td valign='top'>".$row['location']."</td>	
     					<td valign='top' align='right'>".$row['gps_odometer']."</td>
     					<td valign='top' align='right'>".$found_count."</td>
     					<td valign='top' align='right'>".$located."</td>
     					<td valign='top' align='right'>".number_format($use_miles,2)."</td>
     					<td valign='top' align='right'>".number_format($res['miles'],2)."</td>
     				</tr>			
     			";			
     					
     			$counter++;
			}
		}
		$tab.="
				<tr>
					<td valign='top' colspan='4'><b>".$counter." PN points</b></td>
					<td valign='top' align='right'><b>".number_format($res['miles'],2)."</b></td>
					<td valign='top' align='right'>&nbsp;</td>
					<td valign='top' align='right'>&nbsp;</td>
					<td valign='top' align='right'>&nbsp;</td>
					<td valign='top' align='right'>&nbsp;</td>
				</tr>
				</tbody>	
			</table>
		";
		
		
		$res['states']=$cntr;
		$res['scodes']=$arr;
		$res['smiles']=$mil;
		
		$res['tab']=$tab;
		
		return $res;
	}
	
	function mrr_get_next_state_mileage_stop($id,$load_id,$disp_id,$date)
	{	//Date should be MYSQL formated already...
		$res['miles']=0;
		$res['state']="";
		$res['long']=0;
		$res['lat']=0;
		
		$sql="
			select lhs.pcm_miles,
				lhs.latitude,
				lhs.longitude,
				lhs.shipper_state 
			from load_handler_stops lhs 
			where lhs.deleted=0 
				and lhs.linedate_pickup_eta >= '".$date."'
				and lhs.id!='".sql_friendly($id)."'
				and lhs.load_handler_id='".sql_friendly($load_id)."'
				and lhs.trucks_log_id='".sql_friendly($disp_id)."' 
			order by lhs.linedate_pickup_eta asc
		";
		$data = simple_query($sql);	
		if($row = mysqli_fetch_array($data)) 
		{
			$res['miles']=$row['pcm_miles'];
			$res['state']=$row['shipper_state'];
			
			$res['long']=$row['latitude'];
			$res['lat']=$row['longitude'];
		}
		return $res;
	}
	
	$rep_tab="";
	$rep_tab2="";			//this section is not used...at the moment
	
	if(isset($_POST['build_report'])) 
	{
		$show_zeros=0;
		if(isset($_POST['mrr_show_zero_values']) && $_POST['mrr_show_zero_values'] > 0)		$show_zeros=1;
		
		
		$sql = "
			select load_handler_stops.*,
				trucks_log.truck_id,
				trucks_log.driver_id,
				
				trucks_log.origin,
				trucks_log.destination,
				trucks_log.origin_state,
				trucks_log.destination_state,
				
				(select CONCAT(drivers.name_driver_first, ' ', drivers.name_driver_last) from drivers where drivers.id=trucks_log.driver_id) as driver_name,
				trucks_log.linedate_pickup_eta as disp_date,
				load_handler.customer_id,
				trucks.name_truck
			
			from load_handler_stops				
				left join load_handler on load_handler.id=load_handler_stops.load_handler_id
				left join trucks_log on trucks_log.id=load_handler_stops.trucks_log_id
				left join trucks on trucks.id=trucks_log.truck_id
			where load_handler_stops.deleted=0
				and load_handler_stops.trucks_log_id > 0
				and trucks_log.deleted=0
				and load_handler.deleted=0
				and linedate_completed>'2000-01-01 00:00:00'
				and load_handler_stops.linedate_pickup_eta >= '".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'
				and load_handler_stops.linedate_pickup_eta<= '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
				".($_POST['truck_id'] > 0 ? " and trucks_log.truck_id='".sql_friendly($_POST['truck_id'])."'" : "")."
			order by load_handler_stops.linedate_pickup_eta asc
		";
		$data = simple_query($sql);
		
		$rep_tab.="<br><br>
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">Stop Breakdown report for ".date("M j, Y",strtotime($_POST['date_from']))." to " . date("M j, Y",strtotime($_POST['date_to']))."<br></div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter' width='1600'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b>PickupETA</b></th>
				<th><b>Load</b></th>
				<th><b>Dispatch</b></th>
				<th><b>Driver</b></th>				
				<th><b>Truck</b></th>
				<th><b>Type</b></th>
				<th><b>Shipper</b></th>
				<th><b>Address</b></th>
				<th><b>City</b></th>				
				<th><b>State</b></th>
				<th><b>Zip</b></th>
				<th><b>Origin</b></th>
				<th><b>Destination</b></th>				
				<th align='right'><b>PC Miles</b></th>				
				
			</tr>
			</thead>
			<tbody>
		";	//<th align='right'><b>Cum.State</b></th>
		$counter = 0;
		$gtot=0;
		
		$first_stop_date="";
		$last_stop_date="";
		
		while($row = mysqli_fetch_array($data)) 
		{
			
			if(($show_zeros==0 && $row['pcm_miles'] > 0) || $show_zeros==1)
     		{
     				
     			$state_found=0;
     			
     			$lat=$row['latitude'];
     			$long=$row['longitude'];
     			
     			$res=mrr_get_next_state_mileage_stop($row['id'],$row['load_handler_id'],$row['trucks_log_id'],$row['linedate_pickup_eta']);
     			
     			if(trim($res['state']) == trim($row['shipper_state']) || trim($res['state'])=="" )
     			{	//started and finished in same state...assume only this state.
          			for($i=1;$i <=$scntr; $i++)
          			{
          				if($states[$i] == trim($row['shipper_state']) || $snames[$i]==trim($row['shipper_state']))		$state_found=$i;	
          			}
          			$miles_tot[$state_found]+=$row['pcm_miles'];
     				$stops[$state_found]++;
     				
     				$gtot+=$row['pcm_miles'];
     			}
     			else
     			{	//use multiple states...much more complicated to find miles in each state crossed.
     				for($i=1;$i <=$scntr; $i++)
          			{
          				if($states[$i] == trim($row['shipper_state']) || $snames[$i]==trim($row['shipper_state']))		$state_found=$i;	
          			}
          			$miles_tot[$state_found]+=$row['pcm_miles'];
     				$stops[$state_found]++;
     				
     				$gtot+=$row['pcm_miles'];
     			}
     			
     			$origin="".trim($row['origin']).", ".trim($row['origin_state'])."";
     			$dest="".trim($row['destination']).", ".trim($row['destination_state'])."";
     			
     			$rep_tab.="
     				<tr class='".($counter%2==0 ? "even" : "odd")."'>
     					<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>					
     					<td valign='top'><a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank'>".$row['load_handler_id']."</a></td>
     					<td valign='top'><a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['trucks_log_id']."' target='_blank'>".$row['trucks_log_id']."</a></td>
     					<td valign='top'><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driver_name']."</a></td>					
     					<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a></td>
     					<td valign='top'>".($row['stop_type_id']==1 ? "(S)" : "(C)")."</td>
     					<td valign='top'>".$row['shipper_name']."</td>
     					<td valign='top'>".$row['shipper_address1']."</td>
     					<td valign='top'>".$row['shipper_city']."</td>
     					<td valign='top'>".$row['shipper_state']."</td>
     					<td valign='top'>".$row['shipper_zip']."</td>
     					<td valign='top'>".$origin."</td>
     					<td valign='top'>".$dest."</td>     					
     					<td valign='top' align='right'>".$row['pcm_miles']."</td>     					
     				</tr>
     			";		//	<td valign='top' align='right'>".number_format($miles_tot[$state_found] , 2)."</td>	
     			
     			$export_file .= "".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."".chr(9);
				$export_file .= "".$row['load_handler_id']."".chr(9);
				$export_file .= "".$row['trucks_log_id']."".chr(9);
				$export_file .= "".$row['driver_name']."".chr(9);
				$export_file .= "".$row['name_truck']."".chr(9);
				$export_file .= "".($row['stop_type_id']==1 ? "(S)" : "(C)")."".chr(9);
				$export_file .= "".$row['shipper_name']."".chr(9);
				$export_file .= "".$row['shipper_address1']."".chr(9);
				$export_file .= "".$row['shipper_city']."".chr(9);
				$export_file .= "".$row['shipper_state']."".chr(9);
				$export_file .= "".$row['shipper_zip']."".chr(9);
				$export_file .= "".$origin."".chr(9);
				$export_file .= "".$dest."".chr(9);
				$export_file .= "".$row['pcm_miles']."".chr(9);
				$export_file .= chr(13);
     			
     			if($counter==0)		$first_stop_date=$row['linedate_pickup_eta'];
     			$last_stop_date=$row['linedate_pickup_eta'];			
     					
     			$counter++;
     			
			}
			
		}
		$export_file .= chr(13);
		$export_file .= "".$counter." Total".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".number_format($gtot,2)."".chr(9);
		$export_file .= chr(13);
		
		$rep_tab.="
			<tr>
				<td valign='top' colspan='15'><hr></td>
			</tr>
			<tr>
				<td valign='top' colspan='13'><b>".$counter." Total</b></td>
				<td valign='top' align='right'><b>".number_format($gtot,2)."</b></td>
				
			</tr>
			</tbody>
			</table>
		";	//<td valign='top' align='right'>&nbsp;</td>
				
		//now show all states summary..no tracking...
		$stops_all=0;
		$miles_all=0;
		$states_used=0;
																								// (No Tracking)
		$rep_tab.="<br><div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">State Summary:</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter' width='500'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b>State</b></th>
				<th><b>Abbr</b></th>
				<th align='right'><b>Stops</b></th>
				<th align='right'><b>Miles</b></th>
			</tr>
			</thead>
			<tbody>
		";
		
		$export_file .= chr(13);
		$export_file .= "State".chr(9);
		$export_file .= "Abbr".chr(9);
		$export_file .= "Stops".chr(9);
		$export_file .= "Miles".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= chr(13);
		
		for($i=1;$i <=$scntr; $i++)
		{
			$stops_all+=$stops[$i];
			$miles_all+=$miles_tot[$i];
			
			if($stops[$i] > 0 || $miles_tot[$i] > 0)
			{		
				$rep_tab.="
				<tr class='".($states_used%2==0 ? "even" : "odd")."'>
					<td valign='top'>".$snames[$i]."</td>
					<td valign='top'>".$states[$i]."</td>
					<td valign='top' align='right'>".$stops[$i]."</td>
					<td valign='top' align='right'>".number_format($miles_tot[$i],2)."</td>
				</tr>
				";
				
				$export_file .= chr(13);
          		$export_file .= "".$snames[$i]."".chr(9);
          		$export_file .= "".$states[$i]."".chr(9);
          		$export_file .= "".$stops[$i]."".chr(9);
          		$export_file .= "".number_format($miles_tot[$i],2)."".chr(9);
          		$export_file .= "".chr(9);
          		$export_file .= "".chr(9);
          		$export_file .= "".chr(9);
          		$export_file .= "".chr(9);
          		$export_file .= "".chr(9);
          		$export_file .= "".chr(9);
          		$export_file .= "".chr(9);
          		$export_file .= "".chr(9);
          		$export_file .= "".chr(9);
          		$export_file .= "".chr(9);
				
				$states_used++;
			}		
		}
		
		$export_file .= chr(13);
		$export_file .= "".$states_used." States".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".$stops_all."".chr(9);
		$export_file .= "".number_format($miles_all,2)."".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= chr(13);
		
		$rep_tab.="
			<tr>
				<td valign='top' colspan='4'><hr></td>
			</tr>
			<tr>
				<td valign='top' colspan='2'><b>".$states_used." States</b></td>
				<td valign='top' align='right'><b>".$stops_all."</b></td>
				<td valign='top' align='right'><b>".number_format($miles_all,2)."</b></td>
			</tr>
			</tbody>
			</table>
		";
		
		
		//now show all states summary...with PN tracking
		$resrep=mrr_find_distance_traveled($first_stop_date,$last_stop_date,$_POST['truck_id']);
		$miles_all=$resrep['miles'];		
		$states_used=$resrep['states'];
		$arr=$resrep['scodes'];
		$mil=$resrep['smiles'];
		$details=$resrep['tab'];
		
		
		$rep_tab2.="<br><hr><br>
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">State Summary (PN Tracking):</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter' width='500'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b>State</b></th>
				<th><b>Abbr</b></th>
				<th align='right'><b>&nbsp;</b></th>
				<th align='right'><b>Miles</b></th>
			</tr>
			</thead>
			<tbody>
		";
		$colcntr=0;
		
		for($i=1;$i <=$scntr; $i++)
		{
			for($x=0;$x < $states_used; $x++)
			{
				if($arr[$x] == $states[$i])
				{
					$rep_tab2.="
     				<tr class='".($colcntr%2==0 ? "even" : "odd")."'>
     					<td valign='top'>".$snames[$i]."</td>
     					<td valign='top'>".$states[$i]."</td>
     					<td valign='top' align='right'>&nbsp;</td>
     					<td valign='top' align='right'>".number_format($mil[$x],2)."</td>
     				</tr>
     				";
					
					$colcntr++;	
				}	
			}		
		}
		
		$rep_tab2.="
			<tr>
				<td valign='top' colspan='4'><hr></td>
			</tr>
			<tr>
				<td valign='top' colspan='2'><b>".$states_used." States</b></td>
				<td valign='top' align='right'><b>&nbsp;</b></td>
				<td valign='top' align='right'><b>".number_format($miles_all,2)."</b></td>
			</tr>
			</tbody>
			</table>
		";
				
		$rep_tab2.=$details;
		
		
		ob_start();
		
		echo $rep_tab;
		
		
		$pdf = ob_get_contents();
		ob_end_clean();
	
		echo $pdf;
		
		//susana@lpinsurance.com
		
		//$excel_link="https://trucking.conardtransportation.com/temp/$excel_filename";
		$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
		fwrite($fp, $export_file); 
		fclose($fp);
		
		if(trim($_POST['mrr_email_addr'])!="" && isset($_POST['mrr_email_report']))
     	{
     		$user_name=$defaultsarray['company_name'];
     		$From=$defaultsarray['company_email_address'];
     		$Subject="";
     		if(isset($use_title))			$Subject=$use_title;
     		elseif(isset($usetitle))			$Subject=$use_title;
     		
     		$pdf=str_replace(" href="," name=",$pdf);
     		//$pdf=str_replace("</a>","",$pdf);
     		$pdf_len=strlen($pdf);
     		$pdf = wordwrap($pdf,70);
     		
     		$pdf="<a href='".$excel_link."'>Excel File</a>".$pdf;
     				
     			
     		$sres=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject,$pdf,$pdf,1);
     		
     		$sentit=$sres['sent'];
     		$reason=$sres['msg'];
     		
     		$sent_msg="<span class='alert'>not been sent</span>";		if($sentit==1)		$sent_msg="been sent";	
     		echo "<br><br><b>This report has ".$sent_msg." to '".$_POST['mrr_email_addr_name']."' at E-Mail address '".$_POST['mrr_email_addr']."'.  Size of document is ".$pdf_len.".</b><br>".$reason."<br>";
     		
     		if(strtolower(trim($_POST['mrr_email_addr']))!="michael@sherrodcomputers.com")
     		{
     			//$sentit=mrr_trucking_sendMail('dconard@conardlogistics.com',"Dale Conard",$From,$user_name,'','',$Subject,$pdf,$pdf);
     			$sentit=mrr_trucking_sendMail('jgriffith@conardlogistics.com',"James Griffith",$From,$user_name,'','',$Subject,$pdf,$pdf);
     			$sentit=mrr_trucking_sendMail('michael@sherrodcomputers.com',"Michael Richardson",$From,$user_name,'','',$Subject,$pdf,$pdf);
     			//$sentit=mrr_trucking_sendMail('amassar@conardlogistics.com',"Anthony Massar",$From,$user_name,'','',$Subject,$pdf,$pdf);     		
     		}
     	}
     	
     	echo "<a href='".$excel_link."'>Excel File</a>";
		?>
	</td>
</tr>
</table>	
	<? } ?>	
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	<? if($pn_track_tab==0) { ?>
		$('.pn_tracker_tab').hide();
	<? } ?>
</script>
<? include('footer.php') ?>