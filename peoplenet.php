<? include('application.php') ?>
<?
if(!isset($_GET['connect_key']) || $_GET['connect_key'] != 'bas82bad98fqhbnwga8shq34908asdhbn') {
	die("You have reached this page incorrectly.");
}	
	/*******************************************************************************************\ 
	NOTICE:
	Page to get current packets from PeopleNet Tracking.  
	This page is run by cron-job or scheduled tasks.
	\*******************************************************************************************/
		
	
	die('<br>Done.<br>');	//Turned this off, and just in case it runs somewhere else, disabling the page.  As of 6/28/2018...MRR.  Replaced by GeoTab
	
				
	//$found_raises=mrr_process_scheduled_pay_raises(1);
	//echo '<br><b>Driver Raises Processed:</b> '.$found_raises.'.<br>';
	
	mrr_deactivate_completed_geofence_rows();	
	
	//load next packet when page is loaded so that the data is current........................
	$load_start=date("U");
	$max_packet=0;
		
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
	
	//echo "<br>GPS TRACKING OUTPUT:<br>".$pres['output']."<br>";
	
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
	
	$save_blocker=0;
	$max_msg_packet=0;
	$npacket_label="";
	$sql="
		select next_msg_packet_id 
		from ".mrr_find_log_database_name()."truck_tracking_packets
		where next_msg_packet_id > 0
		order by id desc, next_msg_packet_id desc 
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$max_msg_packet=(int) $row['next_msg_packet_id'];
	}
	echo "<br>Max Message Packet=".$max_msg_packet."<br>";	
	
	//$max_msg_packet=0;
	
	$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_message_history",0,0,"",0,$max_msg_packet,0,0,$save_blocker);
	
	if($max_msg_packet > 0)
	{
     	$run_again=0;
     	$next_packet_run=$max_msg_packet + 1;	
     	
     	if($sres['more_data'] > 0)
     	{
     		$run_again=1;		
     		$npacket_label="<br> Next Message Packet from XML is ".$next_packet_run.".<br><br>";
     	}
     	while($run_again>0)
     	{	//turned off. PN limits requests to one per minute, so no point in doing this...will catch the rest next time.
     		$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_message_history",0,0,"",0, $next_packet_run,0,0,$save_blocker);	
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
	}		
	
	
	//update timezones for stop locations...
	$resultmrr=mrr_update_stop_GPS_timezones();
	
	
	//update the message driver settings for night shift drivers...
	mrr_fix_trucks_shift_driver_load_dispatch(0);
	
	
	//update stop geofencing info
	$debugger="Geofencing Update Off";
	$debugger=mrr_run_full_geofencing_update_for_truck_V2(0);	//mrr_run_full_geofencing_update_for_truck
	
	
	//send mail out based on event packets...
	$send_messages1=""; //mrr_trigger_email_by_event_type(1,"arrived");
	//echo $send_messages1;
	
	$send_messages2=""; //mrr_trigger_email_by_event_type(1,"departed");
	//echo $send_messages2;
	
	$mrr_pn_email_processor="Disabled for now...";		//mrr_fetch_peoplenet_email_processor(0,0,0);		//$cur_disp_id=0,$use_departed=0,$phoned=0
	
	$mrr_pn_email_processor=mrr_compare_truck_location_with_current_stops(0,0,0,0);		//$truck_id,$load_id,$disp_id,$stop_id
	
	$mrr_pn_email_processor2=mrr_fetch_peoplenet_email_hourly_updates();
		
	mrr_pull_all_active_geofencing_rows_alt_no_display(0);							//run current stops to get the Pro Miles...
	
			
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
		<br>---------------------------------------';
	
		
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
	
	mrr_trim_old_truck_tracking_plot_points(7);	//remove truck_tracking points older than 7 days
?>