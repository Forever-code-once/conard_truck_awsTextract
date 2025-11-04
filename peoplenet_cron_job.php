<? include('application.php') ?>
<?	//NOTE:  This file is backup copy...  Please make changes to peoplenet.php for Cron Job to take affect.

if(!isset($_GET['connect_key']) || $_GET['connect_key'] != 'bas82bad98fqhbnwga8shq34908asdhbn') {
	die("You have reached this page incorrectly.");
}
	//die("Temporarily Off.");

	/*******************************************************************************************\ 
	NOTICE:
	Page to get current packets from PeopleNet Tracking.  
	This page is run by cron-job or scheduled tasks.
	\*******************************************************************************************/
	
	//load next packet when page is loaded so that the data is current........................
	$load_start=date("U");
	$max_packet=0;
	
	mrr_check_drivers_for_loads();
	mrr_trim_old_truck_tracking_plot_points(7);	//remove truck_tracking points older than 7 days 
	mrr_deactivate_completed_geofence_rows();
	
	// simple query to make sure any 'lost' dispatches are moved to the current day
	$sql = "
		update trucks_log
		set linedate = '".date("Y-m-d")."'
		where linedate = 0
			and deleted = 0
	";
	simple_query($sql);	
	
	$sql="
		select next_packet_id 
		from ".mrr_find_log_database_name()."truck_tracking_packets
		order by next_packet_id desc 
		limit 1
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$max_packet=$row['next_packet_id'];
	}
	echo "<br>Max Location Packet=".$max_packet."<br>";	
		
	$pres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_location_history",0,0,"",$max_packet,0,0,0);
	
	$run_again=0;
	$next_packet_run=$max_packet + 1;	
	
	if($pres['more_data'] > 0)
	{
		$run_again=1;
		$npacket_label="<br> Next Location Packet from XML is ".$next_packet_run.".<br><br>";
	}
	while($run_again>0)
	{
		$pres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_location_history",0,0,"", $next_packet_run,0,0,0);	
		if($pres['more_data'] > 0)
		{
			$run_again=1;
			$next_packet_run++;
			$npacket_label="<br> Next Location Packet from XML is ".$next_packet_run.".<br><br>";			
		}
		else
		{
			$run_again=0;	
		}		
	}
	$serve_output2=$pres['output']."".$npacket_label."";	
	
	
	//dispatch events packets...
	$max_event_packet=0;
	$sql="
		select next_event_packet_id 
		from ".mrr_find_log_database_name()."truck_tracking_packets
		order by next_event_packet_id desc 
		limit 1
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$max_event_packet=$row['next_event_packet_id'];
	}
	echo "<br>Max Event Packet=".$max_event_packet."<br>";
	$dres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_dispatch_events",0,0,"",0,0,$max_event_packet,0);
	
	$run_again=0;
	$next_packet_run=$max_event_packet + 1;	
	
	if($dres['more_data'] > 0)
	{
		$run_again=1;
		$npacket_label="<br> Next Event Packet from XML is ".$next_packet_run.".<br><br>";
	}
	while($run_again>0)
	{
		$dres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_dispatch_events",0,0,"",0,0, $next_packet_run,0);	
		if($dres['more_data'] > 0)
		{
			$run_again=1;
			$next_packet_run++;
			$npacket_label="<br> Next Event Packet from XML is ".$next_packet_run.".<br><br>";			
		}
		else
		{
			$run_again=0;	
		}		
	}
	$serve_output1=$dres['output']."".$npacket_label."";
	
		
	//E-Log Event packets...
	$serve_output4="";
	$elog_event_packet_id=0;
	$sql="
		select elog_event_packet_id 
		from ".mrr_find_log_database_name()."truck_tracking_packets
		order by elog_event_packet_id desc 
		limit 1
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$elog_event_packet_id=$row['elog_event_packet_id'];
	}
	echo "<br>Max Elog Event Packet=".$elog_event_packet_id."<br>";
	$dres=mrr_peoplenet_find_data_for_cron_job("elog_events",0,0,"",0,0,0,$elog_event_packet_id);
	
	$run_again=0;
	$next_packet_run=$elog_event_packet_id + 1;	
	
	if($dres['more_data'] > 0)
	{
		$run_again=1;
		$npacket_label="<br> Next Elog Event Packet from XML is ".$next_packet_run.".<br><br>";
	}
	while($run_again>0)
	{
		$dres=mrr_peoplenet_find_data_for_cron_job("elog_events",0,0,"",0,0,0, $next_packet_run);	
		if($dres['more_data'] > 0)
		{
			$run_again=1;
			$next_packet_run++;
			$npacket_label="<br> Next Elog Event Packet from XML is ".$next_packet_run.".<br><br>";			
		}
		else
		{
			$run_again=0;	
		}		
	}
	$serve_output4=$dres['output']."".$npacket_label."";
		
			
	//Messages in next packet use the same basic processing... 
	$max_msg_packet=0;
	$npacket_label="";
	$sql="
		select next_msg_packet_id 
		from ".mrr_find_log_database_name()."truck_tracking_packets
		order by next_msg_packet_id desc 
		limit 1
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$max_msg_packet=$row['next_msg_packet_id'];
	}
	echo "<br>Max Message Packet=".$max_msg_packet."<br>";	
	$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_message_history",0,0,"",0,$max_msg_packet,0,0);
	
	$run_again=0;
	$next_packet_run=$max_msg_packet + 1;	
	
	if($sres['more_data'] > 0)
	{
		$run_again=1;
		$npacket_label="<br> Next Message Packet from XML is ".$next_packet_run.".<br><br>";
	}
	while($run_again>0)
	{
		$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_message_history",0,0,"",0, $next_packet_run,0,0);	
		if($sres['more_data'] > 0)
		{
			$run_again=1;
			$next_packet_run++;
			$npacket_label="<br> Next Message Packet from XML is ".$next_packet_run.".<br><br>";			
		}
		else
		{
			$run_again=0;	
		}		
	}
	$serve_output3=$sres['output']."".$npacket_label."";
		
	
	
	//log page load...
	$load_end=date("U");
	$load_time=$load_end - $load_start;
	$sql="
		insert into ".mrr_find_log_database_name()."log_page_loads
			(id,
			time_stamp,
			ip_address,
			page_url,
			user_id,
			start_load,
			end_load,
			load_time)
		values 
			(NULL,
			NOW(),			
			'".$_SERVER['REMOTE_ADDR']."',
			'".$SCRIPT_NAME."',
			'".$_SESSION['user_id']."',
			'".$load_start."',
			'".$load_end."',
			'".$load_time."')
	";
	$id=simple_query($sql);
		
	//update timezones for stop locations...
	$resultmrr=mrr_update_stop_GPS_timezones();
	
	//update stop geofencing info
	$debugger="Geofencing Update Off";
	$debugger=mrr_run_full_geofencing_update_for_truck_V2(0);	//mrr_run_full_geofencing_update_for_truck
	
	
	//send mail out based on event packets...
	$send_messages1=""; //mrr_trigger_email_by_event_type(1,"arrived");
	//echo $send_messages1;
	
	$send_messages2=""; //mrr_trigger_email_by_event_type(1,"departed");
	//echo $send_messages2;
		
	mrr_pn_data_lag_checker(1);	//location packet data log check
	mrr_pn_data_lag_checker(2);	//msg packet data log check
	mrr_pn_data_lag_checker(3);	//dispatch events packet data log check
	mrr_pn_data_lag_checker(5);	//elog events packet data log check
		
	
	//update safety report
	$mrr_start_time=time();
	$mrr_rep=mrr_peoplenet_driver_violations_update(0,0);	//driver, employer
	$mrr_end_time=time();
	$mrr_diff_time=$mrr_end_time - $mrr_start_time;	
	
	
	$mrr_pn_email_processor="Disabled for now...";		//mrr_fetch_peoplenet_email_processor(0,0,0);		//$cur_disp_id=0,$use_departed=0,$phoned=0
	
	$mrr_pn_email_processor=mrr_compare_truck_location_with_current_stops(0,0,0,0);		//$truck_id,$load_id,$disp_id,$stop_id
	
	$mrr_pn_email_processor2=mrr_fetch_peoplenet_email_hourly_updates();
	
	
	//run current stops to get the Pro Miles...
	mrr_pull_all_active_geofencing_rows_alt_no_display(0);
	
	//now auto-update any stop to be completed that has arrived more the HR_MAX ago...  
	//This is to catch the load/dispatches/stops where the end of one is the same as the start of the next... so the PN system can depart the next one without being on the last stop.
	//Ex: Disp A ends in LaVergne,TN...Disp B begins in LaVergne,TN.  PN does not mark stop/Disp A completed until outside radius, but when it happens it will be for both.  
	$hr_max=10;
	$mrr_updated_stops=0;
	$mrr_updated_disps=0;
	$sql = "
     	select id,
     		trucks_log_id,
     		load_handler_id
     	from load_handler_stops
     	where deleted=0
     		and stop_type_id='2'
     		and linedate_arrival > '2014-12-01 00:00:00'
     		and linedate_arrival < '".date("Y-m-d",strtotime("-".$hr_max." hour",time()))." 00:00:00'
     		and (linedate_completed is NULL or linedate_completed < '2014-12-01 00:00:00')
     	order by linedate_pickup_eta desc
     ";
     $data = simple_query($sql);
     while($row=mysqli_fetch_array($data))
	{
		//update this stop
		$sql2 = "
          	update load_handler_stops set
          		geofencing_arriving_sent='1',
				linedate_geofencing_arriving=NOW(),
				geofencing_arrived_sent='1',              						
				linedate_geofencing_arrived=NOW(),
				linedate_completed=NOW(),
				geofencing_departed_sent='1',              						
				linedate_geofencing_departed=NOW()
          	where id='".sql_friendly($row['id'])."'
          ";
          simple_query($sql2);
          $mrr_updated_stops++;
          
         	//see if more stops are still needed.
          $sqlx = "
          	select *		
          	from load_handler_stops
          	where deleted=0
          		and trucks_log_id='".sql_friendly($row['trucks_log_id'])."'
          		and (linedate_completed is NULL or linedate_completed < '2014-12-01 00:00:00')
          	order by linedate_pickup_eta desc
          ";
          $datax = simple_query($sqlx);
          $mn=mysqli_num_rows($datax);
          if($mn == 0)
          {	//no more stops, so flag as completed....
          	$sql2 = "
          		update trucks_log set
          			dispatch_completed='1'
          		where id='".sql_friendly($row['trucks_log_id'])."'
         	 	";
          	simple_query($sql2);	
          	
          	$mrr_updated_disps++;
		}
	}
	//....................................................................................................................................................................................................  
	
			
	echo '<br>
		Dispatch Packet is '.$max_event_packet.'. 
		<br>
		==========================
		<br>
		<br>'.$serve_output1.'
		<br>
		======
		Location Packet is '.$max_packet.'. 
		<br>
		==========================
		<br>
		<br>'.$serve_output2.'
		<br>
		======
		<br>	
		Message Packet is '.$max_msg_packet.'. 
		<br>
		==========================
		<br>
		<br>'.$serve_output3.'
		<br>
		==========================
		<br>
		<br>	
		E-Log Event Packet is '.$elog_event_packet_id.'. 
		<br>
		==========================
		<br>
		<br>'.$serve_output4.'
		<br>
		==========================
		<br>
		<br>
		<b>Update TimeZone for updated Stops:</b>
		<br>
		<br>'.$resultmrr.'
		<br>
		<br>---------------------------------------
		<br>
		<br>
		<b>Update Geofencing for All Active Stops:</b>
		<br>
		==========================
		<br>
		<br>'.$debugger.'
		<br>	
		<br>---------------------------------------
		<br>
		<br>
		<b>Arrived Notices:</b>
		<br>
		==========================
		<br>
		<br>'.$send_messages1.'
		<br>	
		<br>---------------------------------------
		<br>
		<br>
		<b>Departed Notices:</b>
		<br>
		==========================
		<br>
		<br>'.$send_messages2.'
		<br>		
		<br>---------------------------------------
		<br>
		<br>
		<b>Driver Safety Report: (generated in '.$mrr_diff_time.' seconds)</b>
		<br>
		==========================
		<br>
		<br>'.$mrr_rep.'
		<br>	
		<br>---------------------------------------
		<br>
		<br>
		<b>New PN Email Processor (Radius Arriving/Arrived/Arrival Msgs)</b>
		<br>
		==========================
		<br>
		<br>'.$mrr_pn_email_processor.'
		<br>	
		<br>---------------------------------------
		<br>
		<br>
		<b>New PN Email Processor (Status Update Msgs)</b>
		<br>
		==========================
		<br>
		<br>'.$mrr_pn_email_processor2.'
		<br>	
		<br>---------------------------------------
		<br>
		<br>
		<b>Auto-Process PN Stops Lags...done so that if Disp A ends where Disp B starts, Disp A is completed and Disp B processing stop 1 arrival/departure.</b>		
		<br>
		<br>'.$mrr_updated_stops.' Stops and '.$mrr_updated_disps.' Dispatches have been completed that have arrived more than '.$hr_max.' hours ago.
		<br>	
		<br>---------------------------------------';
		
		
	echo mrr_pn_driver_dot_list();
	echo mrr_pn_driver_dot_list_v2(0);		//Driver ID
	
	
	//include_once('mrr_load_auto_saver.php');
	
	//add user action to log...
     //mrr_set_user_action_log(9999,'peoplenet.php','mrr_load_auto_saver.php','mrr_load_auto_saver.php',0,0,0,0,0,0,'peoplenet.php');		//values initialized in application.php
?>
<script type='text/javascript'>
	window.location.assign("mrr_load_auto_saver.php?mode=1");
</script>