<?php
//new batch of functions for peoplenet interfaces (Added August 2013)

function mrr_fetch_peoplenet_email_hourly_updates()
{	//only sends the emails for the hourly intervals...such as the 1-hour Rush Trucking status updates....
	$emails_sent=0;
	$gps_too_old_minutes=15;
	$report="";
	global $defaultsarray;
	
	$fromname=$defaultsarray['company_name'];	
	$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];
	$template=(int) $defaultsarray['peoplenet_hot_msg_template_num'];
	
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];
	$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
	
	$arriving_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$arrived_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$departed_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$comp_logo = trim($defaultsarray['peoplenet_hot_msg_logo']);
		
	$monitor_email=$defaultsarray['peoplenet_hot_msg_cc'];
				
	if($mph <=0)	$mph=1;
	
	$gmttime=gmdate("m/d/Y H:i:s");
	$localtime=date("m/d/Y H:i:s");		
	
	$nowtime2=time();
	$nowtime=time();
	$gmt_off = $gmt_off * 60 * 60 * -1;
	$nowtime2+=$gmt_off;
	$nowtime+=$gmt_off;	
				
	if($template <= 0)	$template=0;
	if($template > 1)	$template=1;
	
	$load_arr[0]=0;
	$load_cntr=0;	
				
	$mrr_template="";
	
	$report.="
		<table border='0' cellpadding='0' cellspacing='0' width='1800'>
		<tr>
				<td valign='top'><b>PickUp</b></td>	
				<td valign='top'><b>LoadID</b></td>
				<td valign='top'><b>DispatchID</b></td>
				<td valign='top'><b>Customer</b></td>
				<td valign='top'><b>Driver</b></td>
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>Trailer</b></td>					
				<td valign='top'><b>Origin</b></td>
				<td valign='top'><b>State</b></td>
				<td valign='top'><b>Destination</b></td>
				<td valign='top'><b>State</b></td>
		</tr>
	";
	
	$cntr=0;
	$sql="
		select trucks_log.*,
			trucks.name_truck,
			trailers.trailer_name,
			customers.name_company,
			customers.hot_load_switch,	
			customers.hot_load_timer,
			customers.hot_load_email_arriving,
			customers.hot_load_radius_arriving,
			customers.hot_load_email_arrived,
			customers.hot_load_radius_arrived,
			customers.hot_load_email_departed,
			customers.hot_load_radius_departed,
			drivers.name_driver_first,
			drivers.name_driver_last,
			load_handler.load_number
		from trucks_log
			left join customers on customers.id=trucks_log.customer_id
			left join drivers on drivers.id=trucks_log.driver_id
			left join trucks on trucks.id=trucks_log.truck_id
			left join trailers on trailers.id=trucks_log.trailer_id 
			left join load_handler on load_handler.id=trucks_log.load_handler_id
		where trucks_log.deleted=0
			and load_handler.deleted=0
			and trucks_log.dispatch_completed=0
			and trucks.peoplenet_tracking>0
			and trucks_log.linedate_pickup_eta < NOW()
			and customers.hot_load_timer>0
		order by trucks_log.linedate_pickup_eta asc,
			trucks_log.load_handler_id asc
	";		//
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{			
		$load_num=$row['load_number'];
		$report.="
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'>".date("m/d/Y",strtotime($row['linedate_pickup_eta']))."</td>	
				<td valign='top'><a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank'>".$row['load_handler_id']."</a></td>
				<td valign='top'><a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
				<td valign='top'><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['name_company']."</a></td>
				<td valign='top'><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['name_driver_first']." ".$row['name_driver_last']."</a></td>
				<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a></td>
				<td valign='top'><a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailer_name']."</a></td>					
				<td valign='top'>".$row['origin']."</td>
				<td valign='top'>".$row['origin_state']."</td>
				<td valign='top'>".$row['destination']."</td>
				<td valign='top'>".$row['destination_state']."</td>
			</tr>
		";	
		$report.="
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top' colspan='9'>
					<table border='0' cellpadding='0' cellspacing='0' width='100%'>
					<tr>						
						<td valign='top'>Stop</td>
						<td valign='top'>Appt</td>
						<td valign='top'>Type</td>
						<td valign='top'>StartTrailer</td>
						<td valign='top'>EndTrailer</td>
						<td valign='top'>Name</td>
						<td valign='top'>Address</td>
						<td valign='top'>City</td>						
						<td valign='top'>State</td>
						<td valign='top'>Zip</td>
						<td valign='top'>Lat</td>
						<td valign='top'>Long</td>
						<td valign='top'>Completed</td>
						<td valign='top'>TruckLat</td>
						<td valign='top'>TruckLong</td>
						<td valign='top' align='right'>MilesAway</td>
						<td valign='top' align='right'>Events</td>
						<td valign='top' align='right'>Location</td>
					</tr>
		";			
		$cntrx=0;
		$kill_dispatch=0;
		$load_stopper=0;
		$comp_stopper=0;
		
		$sqlx="
			select load_handler_stops.*,
				(  TIME_TO_SEC(NOW()) - TIME_TO_SEC(load_handler_stops.linedate_geofencing_arriving)  ) as last_arrived_msg_mins,
				t1.trailer_name as trailer1,
				t2.trailer_name as trailer2
			from load_handler_stops
				left join trailers t1 on t1.id=load_handler_stops.start_trailer_id
				left join trailers t2 on t2.id=load_handler_stops.end_trailer_id
			where load_handler_stops.deleted=0
				and load_handler_stops.trucks_log_id='".sql_friendly($row['id'])."'
				and load_handler_stops.geofencing_arrived_sent=0
				and load_handler_stops.geofencing_departed_sent=0
				and (load_handler_stops.linedate_completed is NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
			order by load_handler_stops.linedate_pickup_eta asc
		";	
		$datax=simple_query($sqlx);
		while($rowx=mysqli_fetch_array($datax))
		{
			$compl=date("m/d/Y H:i",strtotime($rowx['linedate_completed']));
			if(substr_count($compl,"12/31/1969") > 0)	$compl="";
			
			$typer="Shipper";		if($rowx['stop_type_id']==2)		$typer="Consignee";
			
			$mrr_speed="";
			$mrr_heading="";
			$mrr_location="";
						
			$geo_last_sent_arriving=0;
			if($rowx['last_arrived_msg_mins'] > 0)
			{
				$geo_last_sent_arriving=$rowx['last_arrived_msg_mins'] / (60 * 60);	//make hours			
			}
			$geo_sent_arriving=$rowx['geofencing_arriving_sent'];
			$geo_sent_arrived=$rowx['geofencing_arrived_sent'];
			$geo_sent_departed=$rowx['geofencing_departed_sent'];
			
			$geo_date_arriving=$rowx['linedate_geofencing_arriving'];
			$geo_date_arrived=$rowx['linedate_geofencing_arrived'];
			$geo_date_departed=$rowx['linedate_geofencing_departed'];
						
			//Determine email mode...				
			$send_email1="";		//Event Based Email Mode
			$send_email2="";		//Location Mode Email
							
			
			//check for the event first...Event Based Email Mode
			$truck_distance1=0;
			$miles_distance1=0;
			$res1=mrr_peoplenet_find_last_event_for_dispatch($row['truck_id'],$row['id'],date("m/d/Y",strtotime($row['linedate_pickup_eta'])));
			$truck_lat1=$res1['lat'];
			$truck_long1=$res1['long'];
			$truck_age1=$res1['age'];
			$truck_date1=$res1['date'];
			$truck_heading1=$res1['closer'];
			
			if($truck_lat1!="0" && $truck_long1!="0")
			{
				$truck_distance1=mrr_distance_between_gps_points($rowx['latitude'],$rowx['longitude'],$truck_lat1,$truck_long1,1);
				$truck_distance1=abs($truck_distance1);
				$miles_distance1=$truck_distance1 / 5280;
				
				if($truck_heading1=="Near" || $truck_heading1=="")
				{
					if($truck_distance1 < ($row['hot_load_radius_arriving'] + $tolerance) && $geo_sent_arriving==0  && $geo_sent_arrived==0 && $geo_sent_departed==0)
						$send_email1="Arriving Email";
					if($truck_distance1 < ($row['hot_load_radius_arrived'] + $tolerance) && $geo_sent_arrived==0 && $geo_sent_departed==0)	
						$send_email1="Arrived Email";
				}
				elseif($truck_heading1=="Far")
				{					
					if($truck_distance1 > ($row['hot_load_radius_departed'] - $tolerance) && $geo_sent_departed==0)	
						$send_email1="Departed Email";
				}
			}				
			
			
			//check the distance by location packets...Location Mode Email...should be most accurate if polling trucks is done frequently.  Do second so that the email gets checked even after the email goes out.
			$truck_distance=0;
			$miles_distance=0;
			$res=mrr_peoplenet_email_processor_fetch_truck_lat_long($row['truck_id'],date("m/d/Y",strtotime($row['linedate_pickup_eta'])));
			$truck_lat=$res['lat'];
			$truck_long=$res['long'];
			$truck_age=$res['age'];
			$truck_date=$res['date'];
			$truck_heading=$res['closer'];
			$gps_location=$res['location'];	
			$truck_speed=$res['truck_speed'];	
			$truck_head=$res['truck_heading'];
				
			$head_mask="North";
          	if($truck_head == 1)		$head_mask="Northeast ";
          	if($truck_head == 2)		$head_mask="East";
          	if($truck_head == 3)		$head_mask="Southeast";
          	if($truck_head == 4)		$head_mask="South";
          	if($truck_head == 5)		$head_mask="Southwest";
          	if($truck_head == 6)		$head_mask="West";
          	if($truck_head == 7)		$head_mask="Northwest";				
						
			if($truck_lat!="0" && $truck_long!="0")
			{
				$truck_distance=mrr_distance_between_gps_points($rowx['latitude'],$rowx['longitude'],$truck_lat,$truck_long,1);
				$truck_distance=abs($truck_distance);
				$miles_distance=$truck_distance / 5280;
			}
			
			$mrr_speed="".$truck_speed." MPH";
			$mrr_heading=" heading ".$head_mask."";
			//$mrr_location="Current Status of Truck: ".$mrr_speed."".$mrr_heading.". Truck is about ".number_format($miles_distance,2)." miles away.  Approximate Location: ".$gps_location."...";	
			
			$mrr_location="
					<br>
					<br>Truck ".$row['name_truck']." is".$mrr_heading." at ".$mrr_speed.". 
					 
					<br>Approximate Location: ".$gps_location."...
			";	//<br>Truck is about ".number_format($miles_distance,2)." miles away. 
			
						
			//determine newest GPS point (in minutes) to see if we need a new GPS point for this truck.
			$gps_minutes_old=$truck_age1;
			$gps_aging_date=$truck_date1;
			if($truck_age < $truck_age1)	
			{
				$gps_minutes_old=$truck_age;
				$gps_aging_date=$truck_date;
			}
			$gps_current_date="N/A";
			//if last GPS point is too old....get a current location for this truck only...
			if($gps_minutes_old > $gps_too_old_minutes)
			{
				$tres=mrr_find_only_location_of_this_truck($row['truck_id']);	//$cur_location=$tres['location'];
				$truck_lat=$tres['latitude'];								//$temp_page=$tres['temp_page'];
				$truck_long=$tres['longitude'];
				$gps_current_date=date("m/d/Y H:i");
				
				$gps_location=$tres['gps_location'];
				$truck_speed=$tres['truck_speed'];
				$truck_head=$tres['truck_head'];
				
				$head_mask="North";
          		if($truck_head == 1)		$head_mask="Northeast ";
          		if($truck_head == 2)		$head_mask="East";
          		if($truck_head == 3)		$head_mask="Southeast";
          		if($truck_head == 4)		$head_mask="South";
          		if($truck_head == 5)		$head_mask="Southwest";
          		if($truck_head == 6)		$head_mask="West";
          		if($truck_head == 7)		$head_mask="Northwest";				
								
				$truck_distance=mrr_distance_between_gps_points($rowx['latitude'],$rowx['longitude'],$truck_lat,$truck_long,1);
				$truck_distance=abs($truck_distance);
				$miles_distance=$truck_distance / 5280;
				
				$mrr_speed="".$truck_speed." MPH";
				$mrr_heading=" heading ".$head_mask."";
				$mrr_location="
					<br>
					<br>Truck ".$row['name_truck']." is".$mrr_heading." at ".$mrr_speed.". 
					 
					<br>Approximate Location: ".$gps_location."...
				";	//<br>Truck is about ".number_format($miles_distance,2)." miles away. 
							
				if($truck_distance < ($row['hot_load_radius_arriving'] + $tolerance) && $geo_sent_arriving==0 && $geo_sent_arrived==0 && $geo_sent_departed==0)	
					$send_email2="Arriving Email";
				if($truck_distance < ($row['hot_load_radius_arrived'] + $tolerance) && $geo_sent_arrived==0 && $geo_sent_departed==0)	
					$send_email2="Arrived Email";
				if($truck_distance > ($row['hot_load_radius_departed'] - $tolerance) && $geo_sent_departed==0 && $geo_sent_arrived>0 )	
					$send_email2="Departed Email";
			}
			
			$cust=mrr_get_all_customer_settings($row['customer_id']);	
          				
         		$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
         		$hl_timer=$cust['hot_load_timer'];					//interval between messages
         		$hl_earriving=$cust['hot_load_email_arriving'];		//email addresses varchar
         		$hl_earrived=$cust['hot_load_email_arrived'];		//email addresses varchar
        		$hl_edeparted=$cust['hot_load_email_departed'];		//email addresses varchar
         		$hl_marriving=$cust['hot_load_email_msg_arriving'];	//email message text
         		$hl_marrived=$cust['hot_load_email_msg_arrived'];		//email message text
         		$hl_mdeparted=$cust['hot_load_email_msg_departed'];	//email message text
         		$hl_r_arriving=$cust['hot_load_radius_arriving'];		//
         		$hl_r_arrived=$cust['hot_load_radius_arrived'];		//
         		$hl_r_departed=$cust['hot_load_radius_departed'];		//
              	$hl_geo_active=$cust['geofencing_radius_active'];		//turn on actual geofencing notices
              	$hl_geo_on_all=$cust['geofencing_hot_msg_all_loads'];	//all loads set to on...
               		
               if(trim($hl_marriving)=="")	$hl_marriving=trim($arriving_comp);
         		if(trim($hl_marrived)=="")	$hl_marrived=trim($arrived_comp);
         		if(trim($hl_mdeparted)=="")	$hl_mdeparted=trim($departed_comp);
             		    			
              	if($monitor_email==$hl_earrived)		$hl_earrived="";
              	if($monitor_email==$hl_edeparted)		$hl_edeparted=""; 	
              	
              	$send_it=0;                   	
              	
              	$msg_body_header="NEW Conard Transportation Status Update";
              	$subject="";
              	$tolist="";
              	$msg_body="";
              	$msg_body_footer="<br>This is an automated message.<br>";
              	$sector=0;
              	
              	$mrr_time_lock=$geo_last_sent_arriving;
              	 
              	if(($mrr_time_lock >= $hl_timer || $mrr_time_lock==0) && $hl_timer>0)
              	{
              		$send_it=1;	
              		$send_email1="".$mrr_time_lock."";
				$send_email2="Status Update";	
				
				$sector=1; 
				
				$tolist=trim($hl_earriving); 
				
				$msg_body="<br>Load Status Update: Truck ".$row['name_truck']." is in route. ".$mrr_location."";   // to Shipper for Pickup			
					
				if($rowx['stop_type_id']==2) 		$msg_body="<br>Load Status Update: Truck ".$row['name_truck']." is in route.".$mrr_location."";  // to Consignee for Delivery   				
				if(trim($hl_marriving)!="")		$msg_body.="<br><br>".$hl_marriving."";  
				
				//$subject="NEW CTS Status Update: Load ".$row['load_handler_id']." | ".$row['name_driver_first']." ".$row['name_driver_last']." | ".$hl_timer." Hour Report";  	
				
				$subject="Check Call Load Number ".$load_num.": ".$row['name_company']." Load Notification";  	
              	}
              	else
              	{
              		$send_email1="Status Update";
				$send_email2="Not Ready";
				$send_it=0;	
				$load_arr[$load_cntr]=$row['load_handler_id'];
				$load_cntr++;
				$kill_dispatch=1;
              	}
              	     			
			if(trim($tolist)=="" && trim($monitor_email)=="")		$send_it=0;
			if(trim($msg_body)=="")							$send_it=0;
			
			
			
			for($z=0;$z < $load_cntr;$z++)
			{
				if($load_arr[$z]==$row['load_handler_id'])	$kill_dispatch=1;		//only send one notice per load...
			}
			
			if($kill_dispatch>0)	
			{
				$send_it=0;
				$send_email1="Status Update";
				$send_email2="Not Ready";	
			}     			
			
			if($send_it==1)
			{
				$load_arr[$load_cntr]=$row['load_handler_id'];
				$load_cntr++;
				
				
				//load info...
          		$sql="
          			select * 
          			from load_handler
          			where id='".sql_friendly($row['load_handler_id'])."'				
          		";
          		$data_load=simple_query($sql);
          		$row_load=mysqli_fetch_array($data_load);
          		     			
          		//dispatch info...
          		$sql="
          			select * 
          			from trucks_log
          			where id='".sql_friendly($row['id'])."'				
          		";
          		$data_dispatch=simple_query($sql);
          		$row_dispatch=mysqli_fetch_array($data_dispatch);
         			
         			//stop ...
         			$sql="
         				select * 
         				from load_handler_stops
         				where id='".sql_friendly($rowx['id'])."'				
         			";
         			$data_stop=simple_query($sql);
         			$row_stop=mysqli_fetch_array($data_stop);      
         			
         			if(trim($row_load['alt_tracking_email'])!="")		$tolist=trim($row_load['alt_tracking_email']);        			
				
				$use_msg_body="".$msg_body_header."".$msg_body."".$msg_body_footer;
          		if($template>0)
          		{
          			$mrr_template=mrr_hot_load_msg_template_V2($template,$row_load,$row_dispatch,$row_stop,$msg_body,$sector);
          			$use_msg_body=$mrr_template;	
          		} 
          		
          		$nres=mrr_geofencing_peoplnet_message($tolist,$subject,$use_msg_body);
          		$note_id=$nres['sendit'];               	//$tolister=$nres['sendto'];  
          		               		
          		//update email sent stamp...only the stamp.  The sent flag is if the radius has been crossed for the other function for Arriving/Arrival/Departed.               		
     			$sqlu="
    					update load_handler_stops set 
    						linedate_geofencing_arriving=NOW()         						
    					where id='".sql_friendly($rowx['id'])."'				
    				";
    				simple_query($sqlu);
				$emails_sent++;	
			}
			
			
			$report.="
				<tr style='background-color:#".($cntrx%2==0 ? "eeffee" : "eeeeee").";'>						
					<td valign='top'>".$rowx['id']."</td>
					<td valign='top'>".date("m/d/Y H:i",strtotime($rowx['linedate_pickup_eta']))."</td>	
					<td valign='top'>".$typer."</td>
					<td valign='top'>".$rowx['trailer1']."</td>
					<td valign='top'>".$rowx['trailer2']."</td>
					<td valign='top'>".$rowx['shipper_name']."</td>
					<td valign='top'>". trim($rowx['shipper_address1']." ".$rowx['shipper_address2'])."</td>
					<td valign='top'>".$rowx['shipper_city']."</td>						
					<td valign='top'>".$rowx['shipper_state']."</td>
					<td valign='top'>".$rowx['shipper_zip']."</td>
					<td valign='top'>".$rowx['latitude']."</td>
					<td valign='top'>".$rowx['longitude']."</td>
					<td valign='top'>".$compl."</td>
					<td valign='top'>".$truck_lat."</td>
					<td valign='top'>".$truck_long."</td>
					<td valign='top' align='right'><span title='".$truck_distance." ft. compared to ".$row['hot_load_radius_arriving'].", ".$row['hot_load_radius_arrived'].", and ".$row['hot_load_radius_departed']."'>".number_format($miles_distance,2)."</span></td>
					<td valign='top' align='right'><span style='color:purple;'><b>".$send_email1."</b></span></td>
					<td valign='top' align='right'><span style='color:brown;'><b>".$send_email2."</b></span></td>
				</tr>
			";		// <span title='Last Date was ".$gps_aging_date.", new date is ".$gps_current_date.".'>[".$gps_minutes_old."]</span>
					//(".$load_stopper.")
			$cntrx++;	
		}			
		$report.="
					</table>					
				</td>
			</tr>
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top' colspan='11'>&nbsp;</td>
			</tr>
		";	
		
		$cntr++;
	}
	$report.="
		</table>
		<br>".$cntr." Active Status Update Loads for as of ".date("m/d/Y").".<br><br>
	";
	return $report;	
}

function mrr_fetch_peoplenet_email_processor($cur_disp_id=0,$use_departed=0,$phoned=0,$stop_id=0,$test_orverride=0)
{
	$emails_sent=0;
	$gps_too_old_minutes=15;
	$report="";
	global $defaultsarray;
	
	$fromname=$defaultsarray['company_name'];	
	$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];
	$template=(int) $defaultsarray['peoplenet_hot_msg_template_num'];
	
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];
	$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
	
	$arriving_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$arrived_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$departed_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$comp_logo = trim($defaultsarray['peoplenet_hot_msg_logo']);
		
	$monitor_email=$defaultsarray['peoplenet_hot_msg_cc'];
		
	//$defaultsarray['peoplenet_geofencing_arriving']
	//$defaultsarray['peoplenet_geofencing_arrived']
	//$defaultsarray['peoplenet_geofencing_departed']
		
	if($mph <=0)	$mph=1;
	
	$gmttime=gmdate("m/d/Y H:i:s");
	$localtime=date("m/d/Y H:i:s");		
	
	$nowtime2=time();
	$nowtime=time();
	$gmt_off = $gmt_off * 60 * 60 * -1;
	$nowtime2+=$gmt_off;
	$nowtime+=$gmt_off;	
				
	if($template <= 0)	$template=0;
	if($template > 1)	$template=1;
			
	$mrr_template="";
	
	$report.="
		<table border='0' cellpadding='0' cellspacing='0' width='1800'>
		<tr>
				<td valign='top'><b>PickUp</b></td>	
				<td valign='top'><b>LoadID</b></td>
				<td valign='top'><b>DispatchID</b></td>
				<td valign='top'><b>Customer</b></td>
				<td valign='top'><b>Driver</b></td>
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>Trailer</b></td>					
				<td valign='top'><b>Origin</b></td>
				<td valign='top'><b>State</b></td>
				<td valign='top'><b>Destination</b></td>
				<td valign='top'><b>State</b></td>
		</tr>
	";
	
	$cntr=0;
	$adder=" and trucks.peoplenet_tracking>0 ";
	if($cur_disp_id > 0)
	{
		$adder=" and trucks_log.id='".sql_friendly($cur_disp_id)."'";	//limit to this dispatch only...added for Phone Handler processing...
		//$adder.=" and trucks.peoplenet_tracking>0 "; 
	}
	$sql="
		select trucks_log.*,
			trucks.name_truck,
			trailers.trailer_name,
			customers.name_company,
			customers.hot_load_switch,	
			customers.hot_load_timer,
			customers.hot_load_email_arriving,
			customers.hot_load_radius_arriving,
			customers.hot_load_email_arrived,
			customers.hot_load_radius_arrived,
			customers.hot_load_email_departed,
			customers.hot_load_radius_departed,
			drivers.name_driver_first,
			drivers.name_driver_last,
			load_handler.load_number
		from trucks_log
			left join customers on customers.id=trucks_log.customer_id
			left join drivers on drivers.id=trucks_log.driver_id
			left join trucks on trucks.id=trucks_log.truck_id
			left join trailers on trailers.id=trucks_log.trailer_id 
			left join load_handler on load_handler.id=trucks_log.load_handler_id
		where trucks_log.deleted=0
			and load_handler.deleted=0
			and trucks_log.dispatch_completed=0			
			and trucks_log.linedate_pickup_eta < NOW()
			".$adder."
		order by trucks_log.linedate_pickup_eta asc,
			trucks_log.load_handler_id asc
	";		//
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{			
		$load_num=$row['load_number'];
		$report.="
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'>".date("m/d/Y",strtotime($row['linedate_pickup_eta']))."</td>	
				<td valign='top'><a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank'>".$row['load_handler_id']."</a></td>
				<td valign='top'><a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
				<td valign='top'><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['name_company']."</a></td>
				<td valign='top'><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['name_driver_first']." ".$row['name_driver_last']."</a></td>
				<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a></td>
				<td valign='top'><a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailer_name']."</a></td>					
				<td valign='top'>".$row['origin']."</td>
				<td valign='top'>".$row['origin_state']."</td>
				<td valign='top'>".$row['destination']."</td>
				<td valign='top'>".$row['destination_state']."</td>
			</tr>
		";	
		$report.="
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top' colspan='9'>
					<table border='0' cellpadding='0' cellspacing='0' width='100%'>
					<tr>						
						<td valign='top'>Stop</td>
						<td valign='top'>Appt</td>
						<td valign='top'>Type</td>
						<td valign='top'>StartTrailer</td>
						<td valign='top'>EndTrailer</td>
						<td valign='top'>Name</td>
						<td valign='top'>Address</td>
						<td valign='top'>City</td>						
						<td valign='top'>State</td>
						<td valign='top'>Zip</td>
						<td valign='top'>Lat</td>
						<td valign='top'>Long</td>
						<td valign='top'>Completed</td>
						<td valign='top'>TruckLat</td>
						<td valign='top'>TruckLong</td>
						<td valign='top' align='right'>MilesAway</td>
						<td valign='top' align='right'>Events</td>
						<td valign='top' align='right'>Location</td>
					</tr>
		";			
		$cntrx=0;
		$kill_dispatch=0;
		$load_stopper=0;
		$comp_stopper=0;
		
		$adderx="";
		//$adderx="and (load_handler_stops.linedate_completed is NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')";	//find incomplete stops... or 
		if($stop_id>0)		$adderx=" and load_handler_stops.id='".sql_friendly($stop_id)."'";									//if selected, lock down to only this stop...prevent moving on to next one (since may already be flagged completed.
		
		$sqlx="
			select load_handler_stops.*,
				TIME_TO_SEC(DATE_FORMAT(TIMEDIFF(NOW(),load_handler_stops.linedate_geofencing_arriving),'%H:%i:%s')) as last_arrived_msg_mins,
				t1.trailer_name as trailer1,
				t2.trailer_name as trailer2
			from load_handler_stops
				left join trailers t1 on t1.id=load_handler_stops.start_trailer_id
				left join trailers t2 on t2.id=load_handler_stops.end_trailer_id
			where load_handler_stops.deleted=0
				and load_handler_stops.trucks_log_id='".sql_friendly($row['id'])."'
				and (
				 	load_handler_stops.geofencing_arriving_sent=0
					or load_handler_stops.geofencing_arrived_sent=0
					or load_handler_stops.geofencing_departed_sent=0
				)
				".$adderx."				
			order by load_handler_stops.linedate_pickup_eta asc
		";
		$datax=simple_query($sqlx);
		if($rowx=mysqli_fetch_array($datax))
		{	//changed the WHILE to IF so only the first stop is processed...no need to process the rest until the first stop has been completed.
			$compl=date("m/d/Y H:i",strtotime($rowx['linedate_completed']));
			if(substr_count($compl,"12/31/1969") > 0)	$compl="";
			
			$typer="Shipper";		if($rowx['stop_type_id']==2)		$typer="Consignee";
			
			$geo_last_sent_arriving=$rowx['last_arrived_msg_mins'] / (60 * 60);	//make hours			
			
			$geo_sent_arriving=$rowx['geofencing_arriving_sent'];
			$geo_sent_arrived=$rowx['geofencing_arrived_sent'];
			$geo_sent_departed=$rowx['geofencing_departed_sent'];
			
			$geo_date_arriving=$rowx['linedate_geofencing_arriving'];
			$geo_date_arrived=$rowx['linedate_geofencing_arrived'];
			$geo_date_departed=$rowx['linedate_geofencing_departed'];
			
			$mrr_local_displayerx="".trim($rowx['shipper_city']).", ".trim($rowx['shipper_state'])."";
						
			//Determine email mode...				
			$send_email1="";		//Event Based Email Mode
			$send_email2="";		//Location Mode Email
							
			
			//check for the event first...Event Based Email Mode
			$truck_distance1=0;
			$miles_distance1=0;
			$res1=mrr_peoplenet_find_last_event_for_dispatch($row['truck_id'],$row['id'],date("m/d/Y",strtotime($row['linedate_pickup_eta'])));
			$truck_lat1=$res1['lat'];
			$truck_long1=$res1['long'];
			$truck_age1=$res1['age'];
			$truck_date1=$res1['date'];
			$truck_heading1=$res1['closer'];
			
			if($truck_lat1!="0" && $truck_long1!="0")
			{
				$truck_distance1=mrr_distance_between_gps_points($rowx['latitude'],$rowx['longitude'],$truck_lat1,$truck_long1,1);
				$truck_distance1=abs($truck_distance1);
				$miles_distance1=$truck_distance1 / 5280;
				
				if(($truck_heading1=="Near" || $truck_heading1=="") && $use_departed==0)
				{
					if($truck_distance1 < ($row['hot_load_radius_arriving'] + $tolerance) && $geo_sent_arriving==0  && $geo_sent_arrived==0 && $geo_sent_departed==0)
						$send_email1="Arriving Email";
					if($truck_distance1 < ($row['hot_load_radius_arrived'] + $tolerance) && $geo_sent_arrived==0 && $geo_sent_departed==0)	
						$send_email1="Arrived Email";
				}
				elseif(($truck_heading1=="Far" && $geo_sent_arrived > 0) || $use_departed > 0)
				{	//don't try to send departed for this stop unless arrived is selected.				
					if($truck_distance1 > ($row['hot_load_radius_departed'] - $tolerance) && $geo_sent_departed==0)	
						$send_email1="Departed Email";
				}
			}				
			
			
			//check the distance by location packets...Location Mode Email...should be most accurate if polling trucks is done frequently.  Do second so that the email gets checked even after the email goes out.
			$truck_distance=0;
			$miles_distance=0;
			$res=mrr_peoplenet_email_processor_fetch_truck_lat_long($row['truck_id'],date("m/d/Y",strtotime($row['linedate_pickup_eta'])));
			$truck_lat=$res['lat'];
			$truck_long=$res['long'];
			$truck_age=$res['age'];
			$truck_date=$res['date'];
			$truck_heading=$res['closer'];
			$gps_location=$res['location'];	
			$truck_speed=$res['truck_speed'];	
			$truck_head=$res['truck_heading'];
							
			$head_mask="North";
          	if($truck_head == 1)		$head_mask="Northeast ";
          	if($truck_head == 2)		$head_mask="East";
          	if($truck_head == 3)		$head_mask="Southeast";
          	if($truck_head == 4)		$head_mask="South";
          	if($truck_head == 5)		$head_mask="Southwest";
          	if($truck_head == 6)		$head_mask="West";
          	if($truck_head == 7)		$head_mask="Northwest";				
				
			$mrr_speed="".$truck_speed." MPH";
			$mrr_heading=" heading ".$head_mask."";
			$mrr_location="".$gps_location."";	
			
			if($truck_lat!="0" && $truck_long!="0")
			{
				$truck_distance=mrr_distance_between_gps_points($rowx['latitude'],$rowx['longitude'],$truck_lat,$truck_long,1);
				$truck_distance=abs($truck_distance);
				$miles_distance=$truck_distance / 5280;
				
				if(($truck_heading=="Near" || $truck_heading=="")  && $use_departed==0)
				{
					if($truck_distance < ($row['hot_load_radius_arriving'] + $tolerance) && $geo_sent_arriving==0 && $geo_sent_arrived==0 && $geo_sent_departed==0)	
						$send_email2="Arriving Email";
					if($truck_distance < ($row['hot_load_radius_arrived'] + $tolerance) && $geo_sent_arrived==0 && $geo_sent_departed==0)	
						$send_email2="Arrived Email";
				}
				elseif(($truck_heading=="Far" && $geo_sent_arrived > 0) || $use_departed > 0)
				{					
					if($truck_distance > ($row['hot_load_radius_departed'] - $tolerance) && $geo_sent_departed==0)	
						$send_email2="Departed Email";
				}
			}
			
			//determine newest GPS point (in minutes) to see if we need a new GPS point for this truck.
			$gps_minutes_old=$truck_age1;
			$gps_aging_date=$truck_date1;
			if($truck_age < $truck_age1)	
			{
				$gps_minutes_old=$truck_age;
				$gps_aging_date=$truck_date;
			}
			$gps_current_date="N/A";
			//if last GPS point is too old....get a current location for this truck only...
			if($gps_minutes_old > $gps_too_old_minutes)
			{
				$tres=mrr_find_only_location_of_this_truck($row['truck_id']);	//$cur_location=$tres['location'];
				$truck_lat=$tres['latitude'];								//$temp_page=$tres['temp_page'];
				$truck_long=$tres['longitude'];							//$gps_location=$tres['gps_location'];
				$gps_current_date=date("m/d/Y H:i");
				
				$truck_distance=mrr_distance_between_gps_points($rowx['latitude'],$rowx['longitude'],$truck_lat,$truck_long,1);
				$truck_distance=abs($truck_distance);
				$miles_distance=$truck_distance / 5280;
				
				if($truck_distance < ($row['hot_load_radius_arriving'] + $tolerance) && $use_departed==0 && $geo_sent_arriving==0 && $geo_sent_arrived==0 && $geo_sent_departed==0)	
					$send_email2="Arriving Email";
				if($truck_distance < ($row['hot_load_radius_arrived'] + $tolerance) && $use_departed==0 && $geo_sent_arrived==0 && $geo_sent_departed==0)	
					$send_email2="Arrived Email";
				if($truck_distance > ($row['hot_load_radius_departed'] - $tolerance) && $geo_sent_departed==0 && ($geo_sent_arrived>0 || $use_departed > 0) )	
					$send_email2="Departed Email";
			}
			
			
			if($cur_disp_id > 0)
			{
				$send_email2="Arrived Email";	
				if($use_departed > 0)	$send_email2="Departed Email";	
			}
			if(trim($compl)=="")
			{				
				//previous message may have been sent, but still valid to send next message...check and count as completed stop if not sent
				$count_last_stop=1;
				
				if($comp_stopper > 0)
				{
					if(($send_email1=="Arriving Email" || $send_email2=="Arriving Email") && $use_departed==0 && $geo_sent_arriving==0 && $geo_sent_arrived==0 && $geo_sent_departed==0)	
						$count_last_stop=0;
					if(($send_email1=="Arrived Email" || $send_email2=="Arrived Email") && $use_departed==0 && $geo_sent_arrived==0 && $geo_sent_departed==0)		
						$count_last_stop=0;
					if(($send_email1=="Departed Email" || $send_email2=="Departed Email") && ($geo_sent_departed==0 || $use_departed > 0))	
						$count_last_stop=0;
					
					if($count_last_stop==1)		$comp_stopper++;		//count this stop as first processing (not completed)			
				}
				else
				{
					$comp_stopper++;	
				}
				if($comp_stopper > 1)
				{
					$send_email1="";
					$send_email2="";	
				}
				
				if(trim($send_email1)=="" && trim($send_email2)=="" && $cntrx==0)	$kill_dispatch++;
			}
			
			if((trim($compl)!="" || $rowx['latitude']==0 || $rowx['longitude']==0 || $load_stopper > 0) && $cur_disp_id==0)
			{	//do not  send this email...already completed...or no GPS points for stop itself...or truck is on equator in GM (not likely--EVER)
				$send_email1="";
				$send_email2="";	
			}
						
			if($send_email1!="" || $send_email2!="")		$load_stopper++;
			
			$cust=mrr_get_all_customer_settings($row['customer_id']);	
          				
         		$hl_active=$cust['hot_load_switch'];						//turn messages on or off going via email
         		$hl_timer=$cust['hot_load_timer'];							//interval between messages
         		$hl_earriving=$cust['hot_load_email_arriving'];				//email addresses varchar
         		$hl_earrived=$cust['hot_load_email_arrived'];				//email addresses varchar
        		$hl_edeparted=$cust['hot_load_email_departed'];				//email addresses varchar
        		
        		$hl_marriving=$cust['hot_load_email_msg_arriving'];			//email message text
         		$hl_marrived=$cust['hot_load_email_msg_arrived'];				//email message text
         		$hl_mdeparted=$cust['hot_load_email_msg_departed'];			//email message text
         		
         		$hl_marriving2=$cust['hot_load_email_msg_arriving_shipper'];	//email message text
         		$hl_marrived2=$cust['hot_load_email_msg_arrived_shipper'];		//email message text
         		$hl_mdeparted2=$cust['hot_load_email_msg_departed_shipper'];	//email message text
         		
         		$hl_r_arriving=$cust['hot_load_radius_arriving'];				//
         		$hl_r_arrived=$cust['hot_load_radius_arrived'];				//
         		$hl_r_departed=$cust['hot_load_radius_departed'];				//
              	$hl_geo_active=$cust['geofencing_radius_active'];				//turn on actual geofencing notices
              	$hl_geo_on_all=$cust['geofencing_hot_msg_all_loads'];			//all loads set to on...
               		
               if(trim($hl_marriving)=="")	$hl_marriving=trim($arriving_comp);
         		if(trim($hl_marrived)=="")	$hl_marrived=trim($arrived_comp);
         		if(trim($hl_mdeparted)=="")	$hl_mdeparted=trim($departed_comp);
         		
         		if(trim($hl_marriving2)=="")	$hl_marriving2=trim($arriving_comp);
         		if(trim($hl_marrived2)=="")	$hl_marrived2=trim($arrived_comp);
         		if(trim($hl_mdeparted2)=="")	$hl_mdeparted2=trim($departed_comp);
             		    			
              	if($monitor_email==$hl_earrived)		$hl_earrived="";
              	if($monitor_email==$hl_edeparted)		$hl_edeparted=""; 	
              	
              	$send_it=1;
              	
              	
              	$msg_body_header="NEW Conard Transportation Notice";
              	$subject="";
              	$tolist="";
              	$msg_body="";
              	$msg_body_footer="<br>This is an automated message. Thank you for using Conard Transportation.<br>";
              	$sector=0;
              	 
              	if($geo_last_sent_arriving >= $hl_timer && $hl_timer>0)
              	{
              		$send_email1="Arriving Email";
				$send_email2="Arriving Email";
              	}
              	
			if($send_email1=="Arriving Email" || $send_email2=="Arriving Email")
			{
				$tolist=trim($hl_earriving);     				
				if($hl_timer==0)
				{
					$msg_body="<br><b>Truck ".$row['name_truck']." is in route.</b>";   																//to Shipper for Pickup
					
					if($rowx['stop_type_id']==2 && $hl_marriving!="")
					{
						$msg_body="<br><b>Truck ".$row['name_truck']." ".$hl_marriving." <br>It is in route to ".$mrr_local_displayerx.".</b>";					//at Consignee for Delivery
					}
					elseif($rowx['stop_type_id']==2)
					{
						$msg_body="<br><b>Truck ".$row['name_truck']." is in route to ".$mrr_local_displayerx.".</b>";										//at Consignee for Delivery
					}
					elseif($rowx['stop_type_id']==1 && $hl_marriving2!="")
					{
						$msg_body="<br><b>Truck ".$row['name_truck']." ".$hl_marriving2." is in route..</b>";											//to Shipper for Pickup	
					}
					
					$subject="Arriving Load Notification: Load Number ".$load_num.": ".$row['name_company']."";  	
				}
				elseif($hl_timer>0)
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." is in route.</b>";   												//to Shipper for Pickup	
					
					if($rowx['stop_type_id']==2 && $hl_marriving!="")
					{
						$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_marriving." <br>It is in route to ".$mrr_local_displayerx.".</b>";	//at Consignee for Delivery
					}
					elseif($rowx['stop_type_id']==2)
					{
						$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." is in route to ".$mrr_local_displayerx.".</b>";  					//at Consignee for Delivery
					}
					elseif($rowx['stop_type_id']==1 && $hl_marriving2!="")
					{
						$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_marriving2."</b>";										//to Shipper for Pickup	
					}
					
					$subject="Check Call Load Number ".$load_num.": ".$row['name_company']."";	//".$hl_timer." Hour Report  	
				}
				$sector=1; 
				$send_it=0;				
			}
			elseif($send_email1=="Arrived Email" || $send_email2=="Arrived Email")
			{
				$tolist=trim($hl_earrived);
				
				$msg_body="<br><b>Truck ".$row['name_truck']." has arrived in ".$mrr_local_displayerx.".</b>";  											//at Shipper for Pickup		
				
				if($rowx['stop_type_id']==2 && $hl_marrived!="")
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_marrived." <br>It is in ".$mrr_local_displayerx.".</b>";				//at Consignee for Delivery
				}
				elseif($rowx['stop_type_id']==2)
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." has arrived in ".$mrr_local_displayerx.".</b>";							//at Consignee for Delivery
				}
				elseif($rowx['stop_type_id']==1 && $hl_marrived2!="")
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_marrived2."</b>";											//at Shipper for Pickup
				}
				
				$subject="Arrival Notification: Load Number ".$load_num.": ".$row['name_company']."";  	
				
				$sector=2; 
			}
			elseif(($send_email1=="Departed Email" || $send_email2=="Departed Email") && ($geo_sent_arrived > 0 || $use_departed > 0))
			{
				$tolist=trim($hl_edeparted);
				
				$msg_body="<br><b>Truck ".$row['name_truck']." has departed ".$mrr_local_displayerx.".</b>";   											//Shipper. Pickup has been completed	
				
				if($rowx['stop_type_id']==2 && $hl_mdeparted!="")
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_mdeparted." <br>It is leaving ".$mrr_local_displayerx.".</b>";		//Consignee.  Delivery has been completed
				}
				elseif($rowx['stop_type_id']==2)
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." has departed ".$mrr_local_displayerx.".</b>";							//Consignee.  Delivery has been completed
				}
				elseif($rowx['stop_type_id']==1 && $hl_mdeparted2!="")
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_mdeparted2."</b>";											//Shipper. Pickup has been completed
				}
				
				$subject="Departure Notification: Load Number ".$load_num.": ".$row['name_company']."";  	
				
				$sector=3;
			}
			
			if(trim($tolist)=="" && trim($monitor_email)=="")		$send_it=0;
			if(trim($msg_body)=="")							$send_it=0;
			
			if($kill_dispatch>0)	
			{
				$send_it=0;
				$send_email1="";
				$send_email2="";	
			}
			
			if($test_orverride > 0)		$tolist=$defaultsarray['special_email_monitor'];
			
			if($send_it==1)
			{
				//load info...
          		$sql="
          			select * 
          			from load_handler
          			where id='".sql_friendly($row['load_handler_id'])."'				
          		";
          		$data_load=simple_query($sql);
          		$row_load=mysqli_fetch_array($data_load);
          		     			
          		//dispatch info...
          		$sql="
          			select * 
          			from trucks_log
          			where id='".sql_friendly($row['id'])."'				
          		";
          		$data_dispatch=simple_query($sql);
          		$row_dispatch=mysqli_fetch_array($data_dispatch);
         			
         			//stop ...
         			$sql="
         				select * 
         				from load_handler_stops
         				where id='".sql_friendly($rowx['id'])."'				
         			";
         			$data_stop=simple_query($sql);
         			$row_stop=mysqli_fetch_array($data_stop);
         			
				if(trim($row_load['alt_tracking_email'])!="")		$tolist=trim($row_load['alt_tracking_email']);
				
				$use_msg_body="".$msg_body_header."".$msg_body."".$msg_body_footer;
          		if($template>0)
          		{
          			$mrr_template=mrr_hot_load_msg_template_V2($template,$row_load,$row_dispatch,$row_stop,$msg_body,$sector);
          			$use_msg_body=$mrr_template;	
          		} 
          		
          		$note_id=0;
          		if(($geo_sent_arriving==0 && $sector==1) || ($geo_sent_arrived==0 && $sector==2) || ($geo_sent_departed==0 && $sector==3))
          		{
          			$nres=mrr_geofencing_peoplnet_message($tolist,$subject,$use_msg_body,$phoned,$use_departed);
          			$note_id=$nres['sendit'];               	//$tolister=$nres['sendto'];  
          		}
          		
          		//update email sent and stamp the datetime
          		if($sector==1)
          		{
          			$sqlu="
         					update load_handler_stops set 
         						geofencing_arriving_sent='1',
         						linedate_geofencing_arriving=NOW()
         						
         					where id='".sql_friendly($rowx['id'])."'				
         				";
         				simple_query($sqlu);
          		}
          		elseif($sector==2)
          		{
          			$sqlu="
         					update load_handler_stops set 
         						geofencing_arriving_sent='1',
         						linedate_geofencing_arriving=NOW(),              						
         						geofencing_arrived_sent='1',              						
         						linedate_geofencing_arrived=NOW()
         						
         					where id='".sql_friendly($rowx['id'])."'				
         				";
         				// linedate_arrival=NOW(),
         				simple_query($sqlu);
          		}
          		elseif($sector==3)
          		{
          			$sqlu="
         					update load_handler_stops set 
         						geofencing_arriving_sent='1',
         						linedate_geofencing_arriving=NOW(),
         						geofencing_arrived_sent='1',              						
         						linedate_geofencing_arrived=NOW(),              						
         						geofencing_departed_sent='1',              						
         						linedate_geofencing_departed=NOW()
         						
         					where id='".sql_friendly($rowx['id'])."'				
         				";
         				//linedate_completed=NOW(),
         				simple_query($sqlu);
         				              				
         				$sqlu="
         					update load_handler_stops set 
         						linedate_arrival=NOW()              						
         					where id='".sql_friendly($rowx['id'])."' 
         						and linedate_arrival<'2014-01-01 00:00:00'				
         				";	
         				//simple_query($sqlu);
         				
         				
         				//update the dispatch if necessary.
                    	$last_date="0000-00-00";	
                    	$sqlxxx = "
                         	select *		
                         	from load_handler_stops
                         	where deleted=0
                         		and trucks_log_id='".sql_friendly($row['id'])."'
                         		and load_handler_id='".sql_friendly($row['load_handler_id'])."'
                         		and (linedate_completed is NULL or linedate_completed < '2014-01-01 00:00:00')
                         	order by linedate_pickup_eta desc
                         ";
                         $dataxxx = simple_query($sqlxxx);
                         $mnxxx=mysqli_num_rows($dataxxx);
                         if($mnxxx == 0)
                         {	//no more stops, so flag as completed....
                         	$sqlxxx2 = "
                         		update trucks_log set
                         			dispatch_completed='1'
                         		where id='".sql_friendly($row['id'])."'
                        	 	";
                         	//simple_query($sqlxxx2);	//turned off the auto_complete for the dispatch...driver may not have gotten loaded/unloaded.  Sept 2015...MRR
                         }             				
          		}
          		 				
				$emails_sent++;	
			}
		
			
			$report.="
				<tr style='background-color:#".($cntrx%2==0 ? "eeffee" : "eeeeee").";'>						
					<td valign='top'>".$rowx['id']."</td>
					<td valign='top'>".date("m/d/Y H:i",strtotime($rowx['linedate_pickup_eta']))."</td>	
					<td valign='top'>".$typer."</td>
					<td valign='top'>".$rowx['trailer1']."</td>
					<td valign='top'>".$rowx['trailer2']."</td>
					<td valign='top'>".$rowx['shipper_name']."</td>
					<td valign='top'>". trim($rowx['shipper_address1']." ".$rowx['shipper_address2'])."</td>
					<td valign='top'>".$rowx['shipper_city']."</td>						
					<td valign='top'>".$rowx['shipper_state']."</td>
					<td valign='top'>".$rowx['shipper_zip']."</td>
					<td valign='top'>".$rowx['latitude']."</td>
					<td valign='top'>".$rowx['longitude']."</td>
					<td valign='top'>".$compl."</td>
					<td valign='top'>".$truck_lat."</td>
					<td valign='top'>".$truck_long."</td>
					<td valign='top' align='right'><span title='".$truck_distance." ft. compared to ".$row['hot_load_radius_arriving'].", ".$row['hot_load_radius_arrived'].", and ".$row['hot_load_radius_departed']."'>".number_format($miles_distance,2)."</span></td>
					<td valign='top' align='right'><span style='color:purple;'><b>".$send_email1."</b></span></td>
					<td valign='top' align='right'><span style='color:brown;'><b>".$send_email2."</b></span></td>
				</tr>
			";		// <span title='Last Date was ".$gps_aging_date.", new date is ".$gps_current_date.".'>[".$gps_minutes_old."]</span>
					//(".$load_stopper.")
			$cntrx++;	
		}			
		$report.="
					</table>					
				</td>
			</tr>
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top' colspan='11'>&nbsp;</td>
			</tr>
		";	
		
		$cntr++;
	}
	$report.="
		</table>
		<br>".$cntr." Active Dispatches Found for as of ".date("m/d/Y").".<br><br>
	";
	return $report;	
}

function mrr_new_truck_odometer_finder($truck_id,$dater)
{		
	$linedate_start="";
	$odometer_start=0;
	
	$linedate_end="";	
	$odometer_end=0;
	
	$cntr=0;	
	
	$month=date("m",strtotime($dater));
	$year=date("Y",strtotime($dater));
	$next_month=(int)$month +1;
	$next_year=$year;
	if($month==12)
	{
		$next_month=1;	
		$next_year++;
	}
	$starter="".$year."-".$month."-01 00:00:00";
	$ender="".$next_year."-".$next_month."-01 00:00:00";
	
	$sql = "
		select *		
		from ".mrr_find_log_database_name()."truck_tracking
		where truck_id = '".sql_friendly($truck_id)."'
			and linedate_added >= '".sql_friendly($starter)."'
			and linedate_added < '".sql_friendly($ender)."'
		order by linedate asc, id asc
	";
	$data_odom = simple_query($sql);
	while($row_odom = mysqli_fetch_array($data_odom))
	{
		if($cntr==0)
		{
			$linedate_start=date("m/d/Y",strtotime($row_odom['linedate_added']));
			$odometer_start=$row_odom['performx_odometer'];		
		}		
		$linedate_end=date("m/d/Y",strtotime($row_odom['linedate_added']));
		$odometer_end=$row_odom['performx_odometer'];	
			
		$cntr++;
	}
	
	$res['date_start']=$linedate_start;	
	$res['odom_start']=$odometer_start;
	
	$res['date_end']=$linedate_end;
	$res['odom_end']=$odometer_end;
	return $res;	
}
       
function mrr_pull_all_active_geofencing_rows_alt1($mode=0)
{
	global $new_style_path;
	global $defaultsarray;
	
	$mydate=date("Y-m-d");		//today...
	
	// moved to people cron job 
	//mrr_deactivate_completed_geofence_rows(); 
	
	$tab="";		//activity report
	$tab2="";		//load board notices  MODE 1
	
	$tab_pickup="";	//activity report split
	$tab_delivery="";	//activity report split
	$pn_truck_cntr=0;
	$pn_truck_arr[0]=0;
	$pn_truck_notes[0]="";
	
	$mrr_header_label="
		<tr>
          	<td nowrap><b>Load ID</b></td>
          	<td nowrap><b>Dispatch</b></td>
          	<td nowrap><b>Stop ID</b></td>
          	<td><b>Customer</b></td>
          	<td><b>Driver</b></td>
          	<td><b>Truck</b></td>
          	<td><b>Trailer</b></td>	
          	<td><b>DueDate</b></td>
          	<td><b>Hours</b></td>
          	<td><b>Dest</b></td>
          	<td><b>Miles</b></td>
          	<td><b>Position</b></td>
          	<td><b>GPSDate</b></td>
          	<td><b>Head</b></td>
          	<td><b>MPH</b></td>
          	<td><b>Location</b></td>
          	<td><b>Distance</b></td>
          	<td><b>ETA</b></td>
          	<td><b>Due</b></td>
          	<td><b>Grade</b></td>
          	<td><b>Notes</b></td>
          </tr>
	";
	     	
	$rcounter_delivery=0;
	$rcounter_pickup=0;
	$rcounter_non_pn=0;
	
	//find all untracked PN trucks and the loads attached to them...
	$tab_no_pn="";
	$no_pn_truck_cntr=0;
	$no_pn_truck_arr[0]=0;
	
	$spec_java_script="";
	
	$sqlx="
		select load_handler.*,
			trucks_log.customer_id as cust_id,
			trucks_log.driver_id as driverid,
			trucks_log.truck_id as truckid,
			trucks.name_truck as truckname,
			trailers.trailer_name as trailername,
			load_handler_stops.start_trailer_id as trailerid,
			drivers.name_driver_first as driverfname,
			drivers.name_driver_last as driverlname,
			customers.name_company as compname,
			load_handler_stops.load_handler_id,
			load_handler_stops.trucks_log_id,
			load_handler_stops.id as stop_id,
			load_handler_stops.linedate_pickup_eta as stop_pickup_eta,
			
			load_handler_stops.appointment_window,
			load_handler_stops.linedate_appt_window_start,
			load_handler_stops.linedate_appt_window_end,
			
			load_handler_stops.timezone_offset,
			load_handler_stops.timezone_offset_dst,     	
			load_handler_stops.pcm_miles,   		
			load_handler_stops.stop_type_id as stop_mode,
			load_handler_stops.shipper_name as stopname,
			load_handler_stops.shipper_city as stopcity,
			load_handler_stops.shipper_state as stopstate 
			
		from load_handler
			left join trucks_log on trucks_log.load_handler_id=load_handler.id
			left join load_handler_stops on load_handler_stops.load_handler_id=load_handler.id     			
			left join trucks on trucks.id=trucks_log.truck_id
			left join trailers on trailers.id=load_handler_stops.start_trailer_id
			left join drivers on drivers.id=trucks_log.driver_id
			left join customers on customers.id=trucks_log.customer_id
			
		where load_handler.deleted=0  
			and load_handler_stops.deleted=0 	
			and trucks_log.deleted=0	
			and trucks.deleted=0
			and drivers.deleted=0
			and customers.deleted=0
			and trucks.peoplenet_tracking=0
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null)
			and trucks_log.dispatch_completed=0     			
			
		order by load_handler_stops.linedate_pickup_eta asc,
				load_handler_stops.load_handler_id asc,     				
				load_handler_stops.trucks_log_id asc,
				load_handler_stops.id asc						
	";
	$datax=simple_query($sqlx);
	while($rowx=mysqli_fetch_array($datax))
	{
		$due_date=$rowx['stop_pickup_eta'];
		     		
		//appointment window...  assumes the "real" appointment time is the end of the appt window...................................
		$appt_window=$rowx['appointment_window'];
		if($appt_window > 0 && strtotime($rowx['linedate_appt_window_start']) > 0 && strtotime($rowx['linedate_appt_window_end']) > 0)
		{
			$due_date=$rowx['linedate_appt_window_end'];
		}			
		//...........................................................................................................................
		
		
		$suffix="";
		if($rowx['timezone_offset']=="-14400")		$suffix="AST";
		if($rowx['timezone_offset']=="-18000")		$suffix="EST";
		if($rowx['timezone_offset']=="-21600")		$suffix="CST";
		if($rowx['timezone_offset']=="-25200")		$suffix="MST";
		if($rowx['timezone_offset']=="-28800")		$suffix="PST";
		
		if($rowx['timezone_offset_dst']=="3600")	$suffix=str_replace("S","D",$suffix);
		
		$stop_typer="(S)";        		
		if($rowx['stop_mode']==2)		$stop_typer="(C)";
		
		$found=0;
		for($x=0;$x < $no_pn_truck_cntr; $x++)
		{     		
			if($no_pn_truck_arr[$x]==$rowx['truckid'])		$found=1;
		}
		
		$nt_cntr=0;
		$sqlnt = "
			select count(*) as mycntr
			
			from trucks_log_notes
			where trucks_log_notes.truck_log_id = '".sql_friendly($rowx['trucks_log_id'])."'
				and trucks_log_notes.linedate_added >= '".date("Y-m-d", strtotime("-7 day", time()))."'
				and trucks_log_notes.deleted = 0
		";
		$datant = simple_query($sqlnt);
		if($rownt=mysqli_fetch_array($datant))
		{
			$nt_cntr=$rownt['mycntr'];	
		}
		
		if($found==0)
		{     		
			if($rcounter_non_pn==0)	$tab_no_pn.=$mrr_header_label;   
			
			$grading_notes_hider="<span class='mrr_link_like_on' onClick='add_note(".$rowx['trucks_log_id'].");'>Edit Notes</span>";	//THIS SECTION NEVER HAD THE PN NOTE...no PN tracking after all...MRR
			$grading_notes_hider2="<br><span class='mrr_link_like_on' onClick='mrr_view_note(".$rowx['trucks_log_id'].");'>View Notes</span>";
			
			$tab_no_pn.="
          		<tr>
          			<td><a href='manage_load.php?load_id=".$rowx['load_handler_id']."' target='_blank'>".$rowx['load_handler_id']."</a></td>
          			<td><a href='add_entry_truck.php?load_id=".$rowx['load_handler_id']."&id=".$rowx['trucks_log_id']."' target='_blank'>".$rowx['trucks_log_id']."</a></td>
          			<td>".$rowx['stop_id']." ".$stop_typer."</td>
          			<td><a href='admin_customers.php?eid=".$rowx['cust_id']."' target='_blank'>".$rowx['compname']."</a></td>
          			<td><a href='admin_drivers.php?id=".$rowx['driverid']."' target='_blank'>".$rowx['driverfname']." ".$rowx['driverlname']."</a></td>
          			<td><a href='admin_trucks.php?id=".$rowx['truckid']."' target='_blank'>".$rowx['truckname']."</a></td>
          			<td><a href='admin_trailers.php?id=".$rowx['trailerid']."' target='_blank'>".$rowx['trailername']."</a></td>
          			<td>".date("m/d/Y H:i",strtotime($due_date))."</td>
          			<td>".$suffix."</td>			
          			<td>".$rowx['stopcity'].", ".$rowx['stopstate']."</td>
          			<td align='right'>".$rowx['pcm_miles']."</td>
          			<td>".$rowx['stopname']."</td>
          			<td>&nbsp;</td>
          			<td>&nbsp;</td> 
          			<td>&nbsp;</td>
          			<td>&nbsp;</td>
          			<td>&nbsp;</td>
          			<td>&nbsp;</td> 
          			<td>&nbsp;</td>
          			<td>&nbsp;</td>  
          			<td".($nt_cntr > 0 ? " style='background-color:#ffcc00;'" : "").">".$grading_notes_hider."".$grading_notes_hider2."</td>                  			
          		</tr>
     		"; 
     		
     		$rcounter_non_pn++;
     		if($rcounter_non_pn==5)		$rcounter_non_pn=0;          		
     		          		
			$no_pn_truck_arr[$no_pn_truck_cntr]=$rowx['truckid'];
			$no_pn_truck_cntr++;
     	}              	
	}	
	
	$gps_too_old_minutes=15;	
	
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];	
	if($mph <=0)	$mph=1;
	
	$grade_offset=0;
	if(is_numeric($defaultsarray['peoplenet_grading_offset_hrs']))
	{
		$grade_offset=$defaultsarray['peoplenet_grading_offset_hrs'];
		$grade_offset=number_format($grade_offset,2);
	}
	     	
	//now get all trucks that are tracked...
	$sqlx="
		select load_handler.*,
			trucks_log.id as trucks_log_id,
			trucks_log.customer_id as cust_id,
			trucks_log.driver_id as driverid,
			trucks_log.truck_id as truckid,
			trucks_log.linedate_pickup_eta as dispatch_pickup_eta,
			trucks.name_truck as truckname,
			trailers.trailer_name as trailername,
			load_handler_stops.start_trailer_id as trailerid,
			drivers.name_driver_first as driverfname,
			drivers.name_driver_last as driverlname,
			customers.name_company as compname,
			load_handler_stops.load_handler_id,
			load_handler_stops.id as stop_id,
			load_handler_stops.linedate_pickup_eta as stop_pickup_eta,
			(TIMESTAMPDIFF(MINUTE,NOW(),load_handler_stops.linedate_pickup_eta)) as stop_pickup_eta_mins,
			(DATEDIFF(load_handler_stops.linedate_pickup_eta,NOW())) as stop_pickup_eta_days,
			load_handler_stops.appointment_window,
			load_handler_stops.linedate_appt_window_start,
			load_handler_stops.linedate_appt_window_end,
			(TIMESTAMPDIFF(MINUTE,NOW(),load_handler_stops.linedate_appt_window_end)) as stop_pickup_end_mins,
			(DATEDIFF(load_handler_stops.linedate_appt_window_end,NOW())) as stop_pickup_end_days,
			load_handler_stops.timezone_offset,
			load_handler_stops.timezone_offset_dst,     	
			load_handler_stops.pcm_miles, 
			load_handler_stops.stop_grade_id,
			load_handler_stops.stop_grade_note,	
			load_handler_stops.latitude,
			load_handler_stops.longitude,	
			load_handler_stops.pro_miles_dist,
			load_handler_stops.pro_miles_eta,
			load_handler_stops.pro_miles_due,     			
			load_handler_stops.geofencing_arrived_sent,	
			load_handler_stops.stop_type_id as stop_mode,
			load_handler_stops.shipper_name as stopname,
			load_handler_stops.shipper_city as stopcity,
			load_handler_stops.shipper_state as stopstate 
			
		from load_handler
			left join trucks_log on trucks_log.load_handler_id=load_handler.id
			left join load_handler_stops on load_handler_stops.load_handler_id=load_handler.id     			
			left join trucks on trucks.id=trucks_log.truck_id
			left join trailers on trailers.id=load_handler_stops.start_trailer_id
			left join drivers on drivers.id=trucks_log.driver_id
			left join customers on customers.id=trucks_log.customer_id
			
		where load_handler.deleted=0  
			and load_handler_stops.deleted=0 	
			and trucks_log.deleted=0	
			and trucks.deleted=0
			and drivers.deleted=0
			and customers.deleted=0
			and trucks.peoplenet_tracking > 0
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
			and trucks_log.dispatch_completed=0  			
			
		order by load_handler_stops.linedate_pickup_eta asc,
				load_handler_stops.load_handler_id asc,     				
				load_handler_stops.trucks_log_id asc,
				load_handler_stops.id asc						
	";
	$datax=simple_query($sqlx);
	
	while($rowx=mysqli_fetch_array($datax))
	{     		
		
		$nt_cntr=0;
		$sqlnt = "
			select count(*) as mycntr
			
			from trucks_log_notes
			where trucks_log_notes.truck_log_id = '".sql_friendly($rowx['trucks_log_id'])."'
				and trucks_log_notes.linedate_added >= '".date("Y-m-d", strtotime("-7 day", time()))."'
				and trucks_log_notes.deleted = 0
		";
		$datant = simple_query($sqlnt);
		if($rownt=mysqli_fetch_array($datant))
		{
			$nt_cntr=$rownt['mycntr'];	
		}
		
		$pro_miles_dist=$rowx['pro_miles_dist'];
		$pro_miles_eta=$rowx['pro_miles_eta'];
		$pro_miles_due=$rowx['pro_miles_due'];
		
		$due_date=$rowx['stop_pickup_eta'];     
		$due_date_mins=$rowx['stop_pickup_eta_mins'];  
		$due_date_days=$rowx['stop_pickup_eta_days'];  		
		     		
		//appointment window...  assumes the "real" appointment time is the end of the appt window...................................
		$appt_label="";
		$appt_window=$rowx['appointment_window'];
		if($appt_window > 0 && strtotime($rowx['linedate_appt_window_start']) > 0 && strtotime($rowx['linedate_appt_window_end']) > 0)
		{
			$due_date=$rowx['linedate_appt_window_end'];				
			$due_date_mins=$rowx['stop_pickup_end_mins'];  
			$due_date_days=$rowx['stop_pickup_end_days']; 
			$appt_label=" <b>ApptWindow</b>";
		}			
		//...........................................................................................................................
		     		
		$suffix="";
		if($rowx['timezone_offset']=="-14400")		$suffix="AST";
		if($rowx['timezone_offset']=="-18000")		$suffix="EST";
		if($rowx['timezone_offset']=="-21600")		$suffix="CST";
		if($rowx['timezone_offset']=="-25200")		$suffix="MST";
		if($rowx['timezone_offset']=="-28800")		$suffix="PST";
		
		if($rowx['timezone_offset_dst']=="3600")	$suffix=str_replace("S","D",$suffix);
		
		$stop_typer="(S)";        		
		if($rowx['stop_mode']==2)		$stop_typer="(C)";
		
		$found=0;
		for($x=0;$x < $pn_truck_cntr; $x++)
		{     		
			if($pn_truck_arr[$x]==$rowx['truckid'])	
			{
				$found=1;
			}
		}
		
		if($found==0)
		{     		
			$pn_truck_arr[$pn_truck_cntr]=$rowx['truckid'];
			$pn_truck_cntr++;
			
			$tracking_lat="";
			$tracking_long="";
			$tracking_date="";
			$tracking_dist=0;
			$tracking_head="";
			$tracking_speed="";
			$tracking_local="";
			$tracking_eta=0;        			
			     			
			//check the distance by location packets...Location Mode Email...should be most accurate if polling trucks is done frequently.  Do second so that the email gets checked even after the email goes out.
			$truck_distance=0;
			$miles_distance=0;
			$res=mrr_peoplenet_email_processor_fetch_truck_lat_long_alt1($rowx['truckid'],date("m/d/Y",strtotime($rowx['dispatch_pickup_eta'])));
			$truck_lat=$res['lat'];
			$truck_long=$res['long'];
			$truck_age=$res['age'];
			$truck_date=$res['date'];
			$truck_heading=$res['closer'];
			$gps_location=$res['location'];	
			$truck_speed=$res['truck_speed'];	
			$truck_head=$res['truck_heading'];
				
			$head_mask="North";
          	if($truck_head == 1)		$head_mask="NE";
          	if($truck_head == 2)		$head_mask="East";
          	if($truck_head == 3)		$head_mask="SE";
          	if($truck_head == 4)		$head_mask="South";
          	if($truck_head == 5)		$head_mask="SW";
          	if($truck_head == 6)		$head_mask="West";
          	if($truck_head == 7)		$head_mask="NW";			
			
			$pc_miler_fail=0;
			$zipcode1="";
			$zipcode2="";
			$pc_miler_val=0;
			//$pc_miler_val=-1;
			
    			$disp_pc_miler="N/A";	    			
			
			$appt_time_days =$due_date_days * 24;
			
			$appt_time_diff =($due_date_mins / 60) + $appt_time_days;
			     			
			if($suffix=="EDT" || $suffix=="EST")	$appt_time_diff-=1;
			//if($suffix=="CDT" || $suffix=="CST")	$appt_time_diff-=0;
			if($suffix=="MDT" || $suffix=="MST")	$appt_time_diff+=1;
			if($suffix=="PDT" || $suffix=="PST")	$appt_time_diff+=2;
			
			$appt_arrived=$rowx['geofencing_arrived_sent'];
			$tracking_grade="";	 
			    			
			$mrr_speed="".$truck_speed." MPH";
			$mrr_heading=" heading ".$head_mask."";
			$mrr_location="Current Status of Truck: ".$mrr_speed."".$mrr_heading.".  Approximate Location: ".$gps_location."...";	// Truck is about ".number_format($miles_distance,2)." miles away.
			
			$tracking_lat=$truck_lat;
			$tracking_long=$truck_long;
			$tracking_date=$truck_date;
			$tracking_dist=$miles_distance;
			$tracking_head="".$head_mask."";
			$tracking_speed=$truck_speed;
			$tracking_local="".$gps_location.""; 
			    			
			$tracking_dist=$rowx['pro_miles_dist'];
			$tracking_eta=$rowx['pro_miles_eta'];
			$track_diff=$rowx['pro_miles_due'];      	
			     			
			
			if($rowx['stop_grade_id'] > 0)
			{
				$tracking_grade=mrr_load_stop_grade_decoder($rowx['stop_grade_id']);    				
			}
			else
			{
				if($appt_arrived==1 || ($tracking_dist < 1  && $tracking_eta <= 0.01))										$tracking_grade="Arrived";	     			  			
     			elseif($appt_arrived==0 && $appt_time_diff < 0)															$tracking_grade.="<span class='geofencing_past_due'>Late</span>";		//geofencing_past_due
     			elseif($appt_arrived==0 && $appt_time_diff > 0 && $track_diff < 0 && abs($track_diff) > $grade_offset)				$tracking_grade.="<span class='geofencing_past_due'>Late</span>";		//geofencing_very_late
     			elseif($appt_arrived==0 && $appt_time_diff > 0 && $track_diff < 0 && abs($track_diff) <= $grade_offset)				$tracking_grade.="<span class='geofencing_past_due'>Late</span>";		//geofencing_late
     			elseif($appt_arrived==0 && $appt_time_diff > 0 && $track_diff > 0 && abs($track_diff) <= $grade_offset)				$tracking_grade.="<span class='geofencing_very_early'>On Time</span>";	//geofencing_early
     			elseif($appt_arrived==0 && $appt_time_diff > 0 && $track_diff > 0 && abs($track_diff) > $grade_offset)				$tracking_grade.="<span class='geofencing_very_early'>On Time</span>";	//geofencing_very_early
			}
			
			
			//elseif($appt_arrived==0 && $appt_window > 0 && $track_diff < 0 && abs($track_diff) > $grade_offset)				$tracking_grade.="<span class='geofencing_very_late'>Very Late</span>";  
			$grading_notes_hider="<span class='mrr_link_like_on' onClick='add_note(".$rowx['trucks_log_id'].");'>Edit Notes</span>";	//<br>".$track_diff."<br>".$grade_offset."
														//mrr_toggle_pn_load_notes(".$rowx['load_handler_id'].");   ...old method, but now using the same notes as the load board...
														
			$grading_notes_hider2="<br><span class='mrr_link_like_on' onClick='mrr_view_note(".$rowx['trucks_log_id'].");'>View Notes</span>";	//added to show truck log notes
			
			$misc_notes="";
			if($rowx['load_handler_id'] > 0)
			{
				$misc_notes=mrr_simple_note_display(8,$rowx['load_handler_id']);
			}
			if(trim($misc_notes)!="")
			{
				$grading_notes_hider.="<div id='pn_activity_notes_".$rowx['stop_id']."' class='all_pn_activity_notes'>".$misc_notes."</div>";	
			}
			   	
			if($rowx['stop_mode']==2)
			{
				if($rcounter_delivery==0)	$tab_delivery.=$mrr_header_label;     	//$rcounter_non_pn=0;
				
				$tab_delivery.="
               		<tr>
               			<td><a href='manage_load.php?load_id=".$rowx['load_handler_id']."' target='_blank' name='".$rowx['load_handler_id']."'>".$rowx['load_handler_id']."</a></td>
               			<td><a href='add_entry_truck.php?load_id=".$rowx['load_handler_id']."&id=".$rowx['trucks_log_id']."' target='_blank'>".$rowx['trucks_log_id']."</a></td>
               			<td>".$rowx['stop_id']." ".$stop_typer."".$appt_label."</td>
               			<td><a href='admin_customers.php?eid=".$rowx['cust_id']."' target='_blank'>".$rowx['compname']."</a></td>
               			<td><a href='admin_drivers.php?id=".$rowx['driverid']."' target='_blank'>".$rowx['driverfname']." ".$rowx['driverlname']."</a></td>
               			<td><a href='admin_trucks.php?id=".$rowx['truckid']."' target='_blank'>".$rowx['truckname']."</a></td>
               			<td><a href='admin_trailers.php?id=".$rowx['trailerid']."' target='_blank'>".$rowx['trailername']."</a></td>                    			
               			<td>".date("m/d/Y H:i",strtotime($due_date))."</td>
               			<td>".$suffix."</td>			
               			<td>".$rowx['stopcity'].", ".$rowx['stopstate']."</td>
               			<td align='right'>".$rowx['pcm_miles']." </td>
               			<td>".$rowx['stopname']."</td>
               			<td>".date("m/d/Y H:i",strtotime($tracking_date))."</td>
               			<td>".$tracking_head."</td>
               			<td align='right'>".$tracking_speed." </td>
               			<td>".$tracking_local."</td> 
               			<td align='right'>".$pro_miles_dist."</td>               			
               			<td align='right'>".$pro_miles_eta."</td> 
               			<td align='right'>".$pro_miles_due."</td> 
               			<td align='right'>".$tracking_grade."</td>    
               			<td".($nt_cntr > 0 ? " style='background-color:#ffcc00;'" : "").">".$grading_notes_hider."".$grading_notes_hider2."</td>           			
               		</tr>
          		"; 
          		
          		$rcounter_delivery++;
          		if($rcounter_delivery==5)	$rcounter_delivery=0;
			}
			else
			{
				if($rcounter_pickup==0)		$tab_pickup.=$mrr_header_label;    				   				 
				 
				$tab_pickup.="
               		<tr>
               			<td><a href='manage_load.php?load_id=".$rowx['load_handler_id']."' target='_blank' name='".$rowx['load_handler_id']."'>".$rowx['load_handler_id']."</a></td>
               			<td><a href='add_entry_truck.php?load_id=".$rowx['load_handler_id']."&id=".$rowx['trucks_log_id']."' target='_blank'>".$rowx['trucks_log_id']."</a></td>
               			<td>".$rowx['stop_id']." ".$stop_typer."</td>
               			<td><a href='admin_customers.php?eid=".$rowx['cust_id']."' target='_blank'>".$rowx['compname']."</a></td>
               			<td><a href='admin_drivers.php?id=".$rowx['driverid']."' target='_blank'>".$rowx['driverfname']." ".$rowx['driverlname']."</a></td>
               			<td><a href='admin_trucks.php?id=".$rowx['truckid']."' target='_blank'>".$rowx['truckname']."</a></td>
               			<td><a href='admin_trailers.php?id=".$rowx['trailerid']."' target='_blank'>".$rowx['trailername']."</a></td>
               			<td>".date("m/d/Y H:i",strtotime($due_date))."</td>
               			<td>".$suffix."</td>			
               			<td>".$rowx['stopcity'].", ".$rowx['stopstate']."</td>
               			<td align='right'>".$rowx['pcm_miles']." </td>
               			<td>".$rowx['stopname']."</td>
               			<td>".date("m/d/Y H:i",strtotime($tracking_date))."</td>
               			<td>".$tracking_head."</td>
               			<td align='right'>".$tracking_speed." </td>
               			<td>".$tracking_local."</td>            
               			<td align='right'>".$pro_miles_dist."</td>               			
               			<td align='right'>".$pro_miles_eta."</td> 
               			<td align='right'>".$pro_miles_due."</td> 
               			<td align='right'>".$tracking_grade."</td>  
               			<td".($nt_cntr > 0 ? " style='background-color:#ffcc00;'" : "").">".$grading_notes_hider."".$grading_notes_hider2."</td>                   			
               		</tr>
          		"; 	  
          		
          		             		
          		$rcounter_pickup++;
          		if($rcounter_pickup==5)		$rcounter_pickup=0;
			}    			
			
			//load board version       			
			if($tracking_grade!="Arrived" && (substr_count($tracking_grade,"Past Due") > 0 || substr_count($tracking_grade,"Very Late") > 0 || substr_count($tracking_grade,"Late") > 0))
			{                 		
               		$linker1="<a href='manage_load.php?load_id=".$rowx['load_handler_id']."' target='_blank'>".$rowx['load_handler_id']."</a>";
               		$linker2="<a href='manage_load.php?load_id=".$rowx['load_handler_id']."' target='_blank'>".$rowx['truckname']."</a>";	
               		
               		$base_msg="".$tracking_local."";                   		
               		if($rowx['longitude']==0 && $rowx['latitude']==0)	
               		{	
               			$base_msg="<span class='alert'>No PN Dispatch</span>";
               		} 
               		
               		$past_due="";
               		$typer="(S)";
               		if($rowx['stop_mode']==2)    					$typer="(C)";               		
               		if(substr_count($tracking_grade,"Past Due") > 0)	$past_due=" style='color:red;'";	
               		if(trim($tracking_grade)=="")					$tracking_grade="On Time";
               		
               		$tab2.=	"<li>";
          			$tab2.=		"<h3>";
          			$tab2.=			"<span>".date("m/d/Y H:i",strtotime($tracking_date))." --1-- ".$linker1."</span>";
          			$tab2.=			"<a href='report_peoplenet_activity.php#".$rowx['load_handler_id']."' target='_blank'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
          			$tab2.=		"</h3>";
          			$tab2.=		"<p>
          							<a href='admin_drivers.php?id=".$rowx['driverid']."' target='_blank'>".$rowx['driverfname']." ".$rowx['driverlname']."</a> (".$tracking_grade.")
          							<br>".$linker2.": ".$tracking_speed." MPH ".$tracking_head." <b>ETA ".number_format($pro_miles_eta, 2)." hrs ".number_format($pro_miles_dist, 2)." miles.</b>, 
          							<span class='tracking_due_display'".$past_due.">Due in ".$pro_miles_due." hrs</span>. ".$base_msg."
          							              							
          							<br>".$rowx['stopname']." ".$typer."
          							<br>".$rowx['stopcity'].", ".$rowx['stopstate']."
          							<br><a href='admin_customers.php?eid=".$rowx['customer_id']."' target='_blank'>".$rowx['compname']."</a>                       							    						
          						</p> ";	
          			$tab2.=	"</li>";  
          			  
          			if($nt_cntr==0 && $appt_window==0)
          			{
          				$spec_java_script.="
          					mrr_highlight_load_id(".$rowx['load_handler_id'].",".$rowx['trucks_log_id'].");
          				"; 
          			} 			
			}
			
			$stoplight_code=0;
			if(substr_count($tracking_grade,"Late") > 0)		$stoplight_code=1;
			if(substr_count($tracking_grade,"Past Due") > 0 || substr_count($tracking_grade,"Very Late") > 0)		$stoplight_code=2;
			
			if($rowx['stop_id'] > 0)
			{
				$sqlu="update load_handler_stops set stoplight_warning_flag='".sql_friendly($stoplight_code)."' where id='".sql_friendly($rowx['stop_id'])."'";
				simple_query($sqlu);
			}
     	}              	
	}
	$cwidth=23;
	$tab="
		<tr><td colspan='".$cwidth."'><b>DELIVERY</b></td></tr>
		".$tab_delivery."
		<tr><td colspan='".$cwidth."'><b>&nbsp;</b></td></tr>
		<tr><td colspan='".$cwidth."'><b>PICKUP</b></td></tr>
		".$tab_pickup."   
		<tr><td colspan='".$cwidth."'><b>&nbsp;</b></td></tr>  		
		<tr><td colspan='".$cwidth."'><b>NON_PEOPLENET:  NO TRACKING AVAILABLE.</b></td></tr>
		".$tab_no_pn."     		
	";	
	
	
	$tab2.=	"<li>";
	$tab2.=		"<h3>";
	$tab2.=			"<br><span>Geofence Legend</span>";    			
	$tab2.=		"</h3>";
	$tab2.=		"<p><span style='color:purple;'>This section now only shows the current/first stop (by appointment time) for each truck.</span></p>";
	$tab2.=		"<p>Grading Scale uses these colors</p>";
	$tab2.=		"<p><span class='geofencing_past_due'>Late</span>: After appointment</p>";
	//$tab2.=		"<p><span class='geofencing_very_late'>Very Late</span>: >".$grade_offset." hrs after</p>";
	//$tab2.=		"<p><span class='geofencing_late'>Late</span>: <=".$grade_offset." hrs after</p>";
	//$tab2.=		"<p><span class='geofencing_early'>Little Early</span>: <=".$grade_offset." hrs before</p>";
	$tab2.=		"<p><span class='geofencing_very_early'>On Time</span>: On Time or Early</p>";		//>".$grade_offset." hrs before
	$tab2.=		"<p>Dispatch must have been sent via PN.</p> ";
	$tab2.=		"<p>Hot Load Tracking must be turned on for each Load.</p> ";
	$tab2.=	"</li>";	
	
	if(trim($spec_java_script)!="")
	{
		$tab2.=	"
					<script language='javascript'>
					$().ready(function() {
						".$spec_java_script."
					});		
					</script>
				";	
	}
	
	
	if($mode==0)	return $tab;
	if($mode==1)	return $tab2;
}	
?>