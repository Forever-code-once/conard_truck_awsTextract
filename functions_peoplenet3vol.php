<?
//volume 3 functions for PeopleNet...  Added Feb 2014.  

function mrr_get_messages_by_truck_mini($truck_id, $date_from, $date_to, $driver_id=0, $load_id=0,$dispatch_id=0 ,$disp_section=0,$mini_mode=0)
{	//messages pulled from packets
	$mcntr=0; 
	$tab="";	
	$tab2="";	//mini mode version.
		
	//$offset_gmt=mrr_gmt_offset_val();
		
	$date_range_msg_history=" and truck_tracking_msg_history.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking_msg_history.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	$mrr_adder="";
	if($load_id > 0)		$mrr_adder.=" and truck_tracking_msg_history.load_id='".sql_friendly($load_id)."'";
	if($dispatch_id > 0)	$mrr_adder.=" and truck_tracking_msg_history.dispatch_id='".sql_friendly($dispatch_id)."'";
	if($driver_id > 0)		$mrr_adder.=" and truck_tracking_msg_history.driver_id='".sql_friendly($driver_id)."'";
		
	$date_range_msg_history2=" and truck_tracking_messages.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking_messages.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	$mrr_adder2="";
	if($load_id > 0)		$mrr_adder2.=" and truck_tracking_messages.load_id='".sql_friendly($load_id)."'";
	if($dispatch_id > 0)	$mrr_adder2.=" and truck_tracking_messages.dispatch_id='".sql_friendly($dispatch_id)."'";
	if($driver_id > 0)		$mrr_adder2.=" and truck_tracking_messages.driver_id='".sql_friendly($driver_id)."'";
	
	$date_range_msg_history3=" and twilio_call_log.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and twilio_call_log.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	$mrr_adder3="";
	if($load_id > 0)		$mrr_adder3.=" and twilio_call_log.load_id='".sql_friendly($load_id)."'";
	if($dispatch_id > 0)	$mrr_adder3.=" and twilio_call_log.disp_id='".sql_friendly($dispatch_id)."'";
	if($driver_id > 0)		$mrr_adder3.=" and twilio_call_log.driver_id='".sql_friendly($driver_id)."'";
		
	$mydate=date("Y-m-d");		//today...
	$driver=mrr_find_pn_truck_drivers($truck_id,$mydate); 	
	
	$sql3 = "
		select truck_tracking_msg_history.id as my_id,
			truck_tracking_msg_history.load_id as my_load,
			truck_tracking_msg_history.dispatch_id as my_disp,
			truck_tracking_msg_history.linedate_added as my_date,
			truck_tracking_msg_history.msg_text as my_msg,			
			'Sent' as mrr_mode
			
		from ".mrr_find_log_database_name()."truck_tracking_msg_history
		where truck_tracking_msg_history.truck_id='".sql_friendly($truck_id) ."'
			".$date_range_msg_history."
			".$mrr_adder."
			
		union all 		
			
		select truck_tracking_messages.id,
			truck_tracking_messages.load_id,
			truck_tracking_messages.dispatch_id,
			truck_tracking_messages.linedate_added,
			truck_tracking_messages.message,			
			'Received'
			
		from ".mrr_find_log_database_name()."truck_tracking_messages
		where truck_tracking_messages.truck_id='".sql_friendly($truck_id) ."'
			".$date_range_msg_history2."
			".$mrr_adder2."
			
		union all 		
			
		select twilio_call_log.id,
			twilio_call_log.load_id,
			twilio_call_log.disp_id,
			twilio_call_log.linedate_added,
			twilio_call_log.message,			
			'Phoned'
			
		from ".mrr_find_log_database_name()."twilio_call_log
		where twilio_call_log.truck_id='".sql_friendly($truck_id) ."'
			and cmd!='' 
			".$date_range_msg_history3."
			".$mrr_adder3."
					
		order by my_date desc
	";
	$data3 = simple_query($sql3);
	//$mn3=mysqli_num_rows($data3);	
	
	$closer="<div style='float:right' class='mrr_link_like_on' onClick='mrr_close_pn_msg_displayer(".$dispatch_id.");'>Close</div>";
		
	if($load_id > 0 && $dispatch_id==0)		$closer="<div style='float:right' class='mrr_link_like_on' onClick='mrr_close_preplan_msg_displayer(".$load_id.");'>Close</div>";
	if($load_id==0 && $dispatch_id==0)			$closer="<div style='float:right' class='mrr_link_like_on' onClick='mrr_close_truck_msg_displayer(".$truck_id.");'>Close</div>";
	
	$tab.="
		<div style='color:#000000; width:750px; min-width:750px; max-width:750px;'>
				Quick Message Reply: <span id='pn_sent_message_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."'>&nbsp;</span><br>
				<textarea id='truck_msg_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."' wrap='virtual' cols='80' rows='5'></textarea>
				<br><br>
				".$closer."
				<input type='button' id='truck_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."_button' value='Send Message' onClick='mrr_send_quick_msg_form(".$truck_id.",".$load_id.",".$dispatch_id.",".$disp_section.");'>
		<br>	
		<div style='color:#000000; width:750px; min-width:750px; max-width:750px; height:300px; overflow-x:scroll; overflow-y:scroll;'>
			<table width='700' border='0' cellpadding='0' cellspacing='0'>
			<tr>
				<td valign='top' width='75'><b>Truck</b></td>
				<td valign='top' width='75'><b>Load</b></td>
				<td valign='top' width='75'><b>Disp</b></td>
				<td valign='top' width='100'><b>Date</b></td>
				<td valign='top'><b>Messages</b></td>
			</tr>
			";	
	
	$tab2.="
		<div style='color:#000000; width:100%;'>
				<b>Quick Message Reply:</b> 
				<br>
				<span id='pn_sent_message_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."'>&nbsp;</span>
				<br>
				<textarea id='truck_msg_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."' wrap='virtual' cols='20' rows='5'></textarea>
				<br><br>
				".$closer."
				<input type='button' id='truck_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."_button' value='Send Message' onClick='mrr_send_quick_msg_form(".$truck_id.",".$load_id.",".$dispatch_id.",".$disp_section.");'>
		<br>	
		<div style='color:#000000; background-color:#dddddd; padding:5px; width:100%;'>
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
			<tr>
				<td valign='top' colspan='2' align='center'><b>Messages</b></td>
			</tr>
			";	
	
	$last_msg_reply_id=0;
	while($row3 = mysqli_fetch_array($data3))
	{				     		
		if(($row3['mrr_mode']!='Sent') || ($row3['mrr_mode']=='Sent' && substr_count($row3['my_msg'],"Warning: ")==0))
		{			
			$use_date=$row3['my_date'];
			//if($row3['mrr_mode']=='Sent')		$use_date=mrr_peoplenet_time_mask_from_gmt($row3['my_date']);
			
			$last_msg_reply_id=$row3['my_id'];
			
			$row3['my_msg']=str_replace("//","/",$row3['my_msg']);
			$row3['my_msg']=str_replace("//","/",$row3['my_msg']);
			$row3['my_msg']=str_replace("//","/",$row3['my_msg']);
			$row3['my_msg']=str_replace("//","/",$row3['my_msg']);
			
			$tab.="<tr>
						<td valign='top'><span title='".$row3['my_id']."'>".$row3['mrr_mode']."</span></td>
						<td valign='top'>".$row3['my_load']."</td>
						<td valign='top'>".$row3['my_disp']."</span></td>
						<td valign='top'>".$use_date."</td>
						<td valign='top'><textarea rows='3' cols='45' wrap='virtual' disabled>".$row3['my_msg']."</textarea></td>
					</tr>";     	//	<div style='width:375px; min-width:375px; max-width:375px; height:30px; overflow-x:auto;'>".$row3['my_msg']."</div>			
			
			$tab2.="
					<tr>
						<td valign='top' colspan='2'><hr></td>
					</tr>
					<tr>
						<td valign='top' align='left'><b>".$row3['mrr_mode']."</b></td>
						<td valign='top' align='right'><b>".$use_date."</b></td>
					</tr>
					<tr>
						<td valign='top' colspan='2'>".$row3['my_msg']."</td>
					</tr>";   
			
			$mcntr++;
		}				
	}
	
	if($mcntr==0)
	{
		$tab.="<tr><td valign='top' colspan='5'>No messages found.</td></tr>";	
		
		$tab.="<tr><td valign='top' colspan='2'>No messages found.</td></tr>";
	}
	$tab.="			
		</table>
		</div>			
		</div>
	";	
	$tab2.="			
		</table>
		</div>			
		</div>
	";	
	
	if($mini_mode > 0)		return $tab2;	
	return $tab;
}



function mrr_quick_send_pn_truck_message($truck_id,$message,$msg_id=0,$driver_id=0,$load_id=0,$dispatch_id=0)
{
	$cmd="imessage_send";		
	
	$_SESSION['peoplenet_new_msg_id']=0;
	$serve_output=mrr_peoplenet_find_data($cmd,$truck_id,0,$message,0,0);	
			
	//if the session variable has been set, the message was saved.  This variable is set in the mrr_peoplenet_find_data function only for the imessage_send service.
	$new_msg_id=$_SESSION['peoplenet_new_msg_id'];
	
	$response="Message has been sent.";	
		
	if($msg_id > 0 && $new_msg_id > 0)
	{
		$sql = "
			update ".mrr_find_log_database_name()."truck_tracking_messages set
				reply_msg_id='".sql_friendly($msg_id)."'
			where id='".sql_friendly($new_msg_id)."'
			";
		simple_query($sql);	
		$sql = "
			update ".mrr_find_log_database_name()."truck_tracking_msg_history set
				user_id_reply='".sql_friendly($_SESSION['user_id'])."',
				linedate_reply=NOW()
			where id='".sql_friendly($msg_id)."'
			";
		simple_query($sql);		
		
		$response="Reply Message has been sent";							
	}	
	
	//now update to show that these messages have been read.
	$sql = "
			update ".mrr_find_log_database_name()."truck_tracking_msg_history set
				user_id_read='".sql_friendly($_SESSION['user_id'])."',
				user_id_reply='".sql_friendly($_SESSION['user_id'])."',
				linedate_reply=NOW(),
				linedate_reply=NOW()
			where truck_id='".sql_friendly($truck_id)."'
				and user_id_read=0
				and user_id_reply=0
			";
	simple_query($sql);	
	
		
	return $response;	
}

function mrr_find_trucks_current_driver_load_dispatch($truck_id)
{
	$look_back=date("Y-m-d", strtotime("-3 days",time()));
	
	$res['load_id']=0;
	$res['dispatch_id']=0;
	$res['driver_id']=0;
	$res['trailer_id']=0;
	
	$sql = "
		select id,
			driver_id,
			load_handler_id,
			trailer_id
			
		from	trucks_log
		
		where truck_id='".sql_friendly($truck_id)."'
			and linedate_pickup_eta >= '".$look_back." 00:00:00'
			and deleted=0
			and dispatch_completed = 0
		
		order by linedate_pickup_eta asc, id desc
		";
	$data=simple_query($sql);	
	if($row=mysqli_fetch_array($data))
	{
		$res['load_id']=$row['load_handler_id'];
		$res['dispatch_id']=$row['id'];		
		$res['trailer_id']=$row['trailer_id'];
				
		$res['driver_id']=mrr_find_driver_elog_duty_for_truck($truck_id);
		if($res['driver_id']==0)		$res['driver_id']=$row['driver_id'];
	}
	
	return $res;	
}
function mrr_find_driver_elog_duty_for_truck($truck_id)
{
	$look_back=date("Y-m-d", strtotime("-7 days",time()));
	$driver_id=0;
	
	$sql = "
		select driver_id
			
		from	".mrr_find_log_database_name()."driver_elog_entries
		
		where truck_id='".sql_friendly($truck_id)."'
			and linedate_added <= NOW()
			and linedate_added >= '".$look_back." 00:00:00'
			and event_id=2
			and (event_data1='1' or event_data1='2')
		
		order by linedate_added desc, id desc
	";
	$data=simple_query($sql);	
	if($row=mysqli_fetch_array($data))
	{
		$driver_id=$row['driver_id'];
	}	
	return $driver_id;
}
function mrr_fix_trucks_shift_driver_load_dispatch($cur_truck_id=0)
{
	$cur_date=date("Y-m-d",time());
	$yestrday=date("Y-m-d",strtotime("-1 day",strtotime($cur_date)));
	
	$time_start="18:30:00";
	$time_end=  "06:30:00";
		
	$rep="";
	$cntr=0;
	$rep.="<table cellpadding='0' cellspacing='0' border='0' width='1200'>";	
	$rep.="		
		<tr>
			<td valign='top'><b>DriverID</b></td>
			<td valign='top'><b>First Name</b></td>
			<td valign='top'><b>Last Name</b></td>
			<td valign='top'><b>Attached Truck</b></td>
			<td valign='top'><b>TruckID</b></td>
			<td valign='top'><b>Attached Truck2</b></td>
			<td valign='top'><b>Truck2ID</b></td>
		</tr>
	";
	
	$sql = "
		select drivers.*,
			(select t1.name_truck from trucks t1 where t1.id=drivers.attached_truck_id) as name_truck,
			(select t2.name_truck from trucks t2 where t2.id=drivers.attached2_truck_id) as name_truck2
		from	drivers		
		where drivers.night_shifter > 0
			and drivers.deleted=0
			and drivers.active>0
		order by drivers.name_driver_last asc,
			drivers.name_driver_first asc, 
			drivers.id desc
		";	//
	$data=simple_query($sql);	
	while($row=mysqli_fetch_array($data))
	{
		$rep.="
			<tr style='background-color:#".($cntr % 2 == 0 ? "eeeeee" : "dddddd").";'>
				<td valign='top'><a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
				<td valign='top'>".$row['name_driver_first']."</td>
				<td valign='top'>".$row['name_driver_last']."</td>
				<td valign='top'>".($row['attached_truck_id'] > 0 ? "<a href='admin_trucks.php?id=".$row['attached_truck_id']."' target='_blank'>".$row['name_truck']."</a>" : "N/A")."</td>
				<td valign='top'>".$row['attached_truck_id']."</td>
				<td valign='top'>".($row['attached2_truck_id'] > 0 ? "<a href='admin_trucks.php?id=".$row['attached2_truck_id']."' target='_blank'>".$row['name_truck2']."</a>" : "N/A")."</td>
				<td valign='top'>".$row['attached2_truck_id']."</td>
			</tr>
		";
		
		$truck_id=$row['attached_truck_id'];
		$driver_id=$row['id'];
		
		if($cur_truck_id > 0 && $cur_truck_id!=$truck_id)		$truck_id=0;		//turn this truck off...skip since we were looking at only one truck.
		
		
		if($truck_id > 0 && ($row['attached2_truck_id']==$truck_id || $row['attached2_truck_id']==0))
		{	//truck one is set, and truck 2 matches or is not set.			
			$cntr2=0;
			
			$messages="";
			$messages.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
			$messages.="
					<tr>
          				<td valign='top'><b>MsgID</b></td>
          				<td valign='top'><b>Added</b></td>
          				<td valign='top'><b>TruckID</b></td>
          				<td valign='top'><b>TruckName</b></td>
          				<td valign='top'><b>DriverID</b></td>
          				<td valign='top'><b>FirstName</b></td>
          				<td valign='top'><b>LastName</b></td>          				
          				<td valign='top'><b>MessageText</b></td>
          			</tr>
			";
			
			$sql2 = "
          		select truck_tracking_msg_history.*,
          			(select drivers.name_driver_first from drivers where drivers.id=truck_tracking_msg_history.driver_id) as name_driver_first,
          			(select drivers.name_driver_last from drivers where drivers.id=truck_tracking_msg_history.driver_id) as name_driver_last      			
          		from	".mrr_find_log_database_name()."truck_tracking_msg_history
          		
          		where truck_tracking_msg_history.truck_id='".sql_friendly($truck_id)."'
          			and (
          				(truck_tracking_msg_history.linedate_added >= '".$yestrday." ".$time_start."' and truck_tracking_msg_history.linedate_added <= '".$cur_date." ".$time_end."')
          				or
          				(truck_tracking_msg_history.linedate_added > '".$cur_date." ".$time_start."' and truck_tracking_msg_history.linedate_added <= '".$cur_date." 23:59:59')
          				)
          			and LOCATE('Warning: ',truck_tracking_msg_history.msg_text)=0
          			and driver_id!='".sql_friendly($driver_id)."'
          		
          		order by truck_tracking_msg_history.linedate_added desc, truck_tracking_msg_history.id desc
          	";
          	$data2=simple_query($sql2);	
          	while($row2=mysqli_fetch_array($data2))
          	{
          		$messages.="
          			<tr style='background-color:#".($cntr2 % 2 == 0 ? "aeaeae" : "bdbdbd").";'>
          				<td valign='top'>".$row2['id']."</td>
          				<td valign='top'>".$row2['linedate_added']."</td>
          				<td valign='top'>".$row2['truck_id']."</td>
          				<td valign='top'>".$row2['truck_name']."</td>
          				<td valign='top'>".($driver_id!=$row2['driver_id'] ? "<span style='color:#CC0000;'><b>".$row2['driver_id']."</b></span> - > <b>".$driver_id."</b>" : "".$row2['driver_id']."")."</td>
          				<td valign='top'>".$row2['name_driver_first']."</td>
          				<td valign='top'>".$row2['name_driver_last']."</td>          				
          				<td valign='top'>".$row2['msg_text']."</td>
          			</tr>
          		";
          		
          		if($driver_id!=$row2['driver_id'])
          		{	//update the driver ID for this message to the night shifter.
          			$sql2u = "
                    		update ".mrr_find_log_database_name()."truck_tracking_msg_history set
                    			driver_id='".sql_friendly($driver_id)."'                    		
                    		where id='".sql_friendly($row2['id'])."'
                    	";
                    	simple_query($sql2u);			
          		}          		
          		$cntr2++;
          	}
          	$messages.="</table>";	//<br>Query:".$sql2."<br>
          	if($cntr2 > 0)
          	{          	
          		$rep.="
     				<tr style='background-color:#".($cntr % 2 == 0 ? "eeeeee" : "dddddd").";'>
     					<td valign='top'>&nbsp;</td>
     					<td valign='top' colspan='6' align='center'><b><i>Messages</i></b></td>
     				</tr>
     				<tr style='background-color:#".($cntr % 2 == 0 ? "eeeeee" : "dddddd").";'>
     					<td valign='top'>&nbsp;</td>
     					<td valign='top' colspan='6'>".$messages."</td>
     				</tr>
				";
			}	
		}
		$rep.="
     		<tr style='background-color:#".($cntr % 2 == 0 ? "eeeeee" : "dddddd").";'>
     			<td valign='top' colspan='7'>&nbsp;</td>
     		</tr>
		";
		$cntr++;
	}
	$rep.="</table>";
	
	return $rep;	
}


function mrr_pull_all_active_geofencing_rows_alt_no_display($mode=0)
{
	global $new_style_path;
	global $defaultsarray;
	    	
	$gps_too_old_minutes=15;	
	
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];	
	if($mph <=0)	$mph=1;
	
	$grade_offset=0;
	if(is_numeric($defaultsarray['peoplenet_grading_offset_hrs']))
	{
		$grade_offset=$defaultsarray['peoplenet_grading_offset_hrs'];
		$grade_offset=number_format($grade_offset,2);
	}
	     	
	//get all trucks that are tracked...
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
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null)
			and trucks_log.dispatch_completed=0     			
			
		order by load_handler_stops.linedate_pickup_eta asc,
				load_handler_stops.load_handler_id asc,     				
				load_handler_stops.trucks_log_id asc,
				load_handler_stops.id asc						
	";
	$datax=simple_query($sqlx);
	$cntrx=0;
	while($rowx=mysqli_fetch_array($datax))
	{		
		$found=0;
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
			//$truck_age=$res['age'];
			//$truck_date=$res['date'];
			//$truck_heading=$res['closer'];
			//$gps_location=$res['location'];	
			//$truck_speed=$res['truck_speed'];	
			//$truck_head=$res['truck_heading'];	
			
			$due_date_mins=$rowx['stop_pickup_eta_mins'];  
     		$due_date_days=$rowx['stop_pickup_eta_days']; 
     		    		
     		//appointment window...  assumes the "real" appointment time is the end of the appt window...................................
			$appt_window=$rowx['appointment_window'];
			if($appt_window > 0 && strtotime($rowx['linedate_appt_window_start']) > 0 && strtotime($rowx['linedate_appt_window_end']) > 0)
			{
				//$due_date=$rowx['linedate_appt_window_end'];				
				$due_date_mins=$rowx['stop_pickup_end_mins'];  
     			$due_date_days=$rowx['stop_pickup_end_days']; 
			}			
			//...........................................................................................................................
     					
			$appt_time_days =$due_date_days * 24;			
			$appt_time_diff =($due_date_mins / 60) + $appt_time_days;
			
			$miles=0;
			$eta_hrs=0;
			$due_hrs=0;
			
			if($truck_lat!="0" && $truck_long!="0" && $rowx['latitude']!=0 && $rowx['longitude']!=0)
			{
				//$miles=mrr_promiles_get_file_contents($truck_lat,$truck_long,$rowx['latitude'],$rowx['longitude']);
				$miles=0;
				
				if($miles <= 0)
				{
					$miles=mrr_distance_between_gps_points($truck_lat,$truck_long,$rowx['latitude'],$rowx['longitude']);
				}
				if($mph > 0)		$eta_hrs=$miles / $mph;
				$due_hrs=$appt_time_diff - $eta_hrs;				
			}						
			mrr_quick_update_stop_pro_miles($rowx['stop_id'],$miles,$eta_hrs,$due_hrs);	
			
			$cntrx++;		
     	}              	
	}    
	return $cntrx; 	
}



function mrr_compare_truck_location_with_current_stops($truck_id=0,$load_id=0,$disp_id=0,$stop_id=0)
{
		
	$adder="and (load_handler_stops.linedate_completed is NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')";	//find incomplete stops... or 
	if($stop_id>0)		$adder=" and load_handler_stops.id='".sql_friendly($stop_id)."'";								//if selected, lock down to only this stop...prevent moving on to next one (since may already be flagged completed.
	
	if($disp_id>0)		$adder.=" and trucks_log.id='".sql_friendly($disp_id)."'";	
	if($load_id>0)		$adder.=" and load_handler.id='".sql_friendly($load_id)."'";	
	if($truck_id>0)	$adder.=" and trucks_log.truck_id='".sql_friendly($truck_id)."'";	
	
	global $defaultsarray;
	$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
	$monitor_email=$defaultsarray['peoplenet_hot_msg_cc'];
	
	$arriving_comp="";
     $arrived_comp="";
     $departed_comp="";
		
	$cntr=0;
	
	$tab="";
	$tab.="
		<table cellpadding='1' cellspacing='1' border='0' width='100%'>
		<tr>
			<td valign='top'><b>Truck</b></td>
			<td valign='top'><b>Load</b></td>
			<td valign='top'><b>Disp</b></td>
			<td valign='top'><b>Stop</b></td>
			<td valign='top'><b>Type</b></td>
			
			<td valign='top'><b>Shipper</b></td>
			<td valign='top'><b>City</b></td>
			<td valign='top'><b>State</b></td>
			<td valign='top'><b>Pickup</b></td>
			<td valign='top'><b>Arrival</b></td>
			<td valign='top'><b>Completed</b></td>			
			
			<td valign='top'><b>Arriving</b></td>
			<td valign='top'><b>Radius</b></td>			
			
			<td valign='top'><b>Arrived</b></td>
			<td valign='top'><b>Radius</b></td>	
			
			<td valign='top'><b>Departed</b></td>
			<td valign='top'><b>Radius</b></td>	
			
			<td valign='top'><b>Lat</b></td>
			<td valign='top'><b>Long</b></td>
			<td valign='top'><b>PNLat</b></td>
			<td valign='top'><b>PNLong</b></td>
			
			<td valign='top'><b>Miles</b></td>
			<td valign='top'><b>Mark</b></td>
		</tr>
	";
	
	$sql="
		select load_handler_stops.*,
			load_handler_stops.linedate_pickup_eta as my_pickup,
			trucks.name_truck,
			trucks_log.truck_id,
			trucks_log.customer_id,
			customers.name_company
		from load_handler_stops
			left join trucks_log on trucks_log.id=load_handler_stops.trucks_log_id
			left join load_handler on load_handler.id=load_handler_stops.load_handler_id
			left join trucks on trucks.id=trucks_log.truck_id
			left join customers on customers.id=trucks_log.customer_id
		where load_handler_stops.deleted=0
			and load_handler.deleted=0
			and trucks_log.deleted=0
			and trucks_log.truck_id>0
			".$adder."				
		order by trucks_log.linedate_pickup_eta asc,
			load_handler_stops.linedate_pickup_eta asc
	";
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))	
	{
		$mrr_local_displayerx="".trim($row['shipper_city']).", ".trim($row['shipper_state'])."";
		
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
          
          if($hl_r_arriving==0)		$hl_r_arriving=36960;
		if($hl_r_arrived==0)		$hl_r_arrived=15840;
		if($hl_r_departed==0)		$hl_r_departed=26400;		
          
          if(trim($hl_marriving)=="")	$hl_marriving=trim($arriving_comp);
     	if(trim($hl_marrived)=="")	$hl_marrived=trim($arrived_comp);
     	if(trim($hl_mdeparted)=="")	$hl_mdeparted=trim($departed_comp);
     	
     	if(trim($hl_marriving2)=="")	$hl_marriving2=trim($arriving_comp);
     	if(trim($hl_marrived2)=="")	$hl_marrived2=trim($arrived_comp);
     	if(trim($hl_mdeparted2)=="")	$hl_mdeparted2=trim($departed_comp);
        		    			
         	if($monitor_email==$hl_earrived)		$hl_earrived="";
         	if($monitor_email==$hl_edeparted)		$hl_edeparted=""; 	
         	         	
		
		//check the distance by location packets...Location Mode Email...should be most accurate if polling trucks is done frequently.  Do second so that the email gets checked even after the email goes out.
		$truck_distance=0;
		$miles_distance=0;
		$send_email2="";
		
		$res=mrr_peoplenet_email_processor_fetch_truck_lat_long($row['truck_id'],date("m/d/Y",strtotime($row['my_pickup'])));
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
		
		$no_gps=1;
		$sector=0;
		$tolist="";
		$subject="";
		$msg_body="";		
		
		if($row['longitude'] > 0)		$row['longitude']=$row['longitude'] * -1;
				
		if($truck_lat!="0" && $truck_long!="0" && $row['latitude']!=0 && $row['longitude']!=0)
		{
			$truck_distance=mrr_distance_between_gps_points($row['latitude'],$row['longitude'],$truck_lat,$truck_long,1);
			$truck_distance=abs($truck_distance);
			$miles_distance=$truck_distance / 5280;
						
			if($truck_distance < ($hl_r_arriving + $tolerance) && $row['geofencing_arrived_sent']==0)	
			{
				$send_email2="Arriving";
				$sector=1;
				$tolist=trim($hl_earriving);
				
				
				$subject="Arriving Load Notification: Load Number ".$row['load_handler_id'].": ".$row['name_company']."";
				//$subject="Check Call Load Number ".$row['load_handler_id'].": ".$row['name_company']."";	//".$hl_timer." Hour Report  	
				
				$msg_body="<br><b>Truck ".$row['name_truck']." is in route.</b>";   																//to Shipper for Pickup
     					
     			if($row['stop_type_id']==2 && $hl_marriving!="")
     			{
     				$msg_body="<br><b>Truck ".$row['name_truck']." ".$hl_marriving." <br>It is in route to ".$mrr_local_displayerx.".</b>";					//at Consignee for Delivery
     			}
     			elseif($row['stop_type_id']==2)
     			{
     				$msg_body="<br><b>Truck ".$row['name_truck']." is in route to ".$mrr_local_displayerx.".</b>";										//at Consignee for Delivery
     			}
     			elseif($row['stop_type_id']==1 && $hl_marriving2!="")
     			{
     				$msg_body="<br><b>Truck ".$row['name_truck']." ".$hl_marriving2." is in route..</b>";											//to Shipper for Pickup	
     			}
     			
			}
			if($truck_distance < ($hl_r_arrived + $tolerance) && $row['geofencing_arrived_sent']==0)	
			{
				$send_email2="Arrived";
				$sector=2;
				$tolist=trim($hl_earrived);
								
				$subject="Arrival Notification: Load Number ".$row['load_handler_id'].": ".$row['name_company']."";  
				$msg_body="<br><b>Truck ".$row['name_truck']." has arrived in ".$mrr_local_displayerx.".</b>";  											//at Shipper for Pickup		
     				
     			if($row['stop_type_id']==2 && $hl_marrived!="")
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_marrived." <br>It is in ".$mrr_local_displayerx.".</b>";				//at Consignee for Delivery
				}
				elseif($row['stop_type_id']==2)
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." has arrived in ".$mrr_local_displayerx.".</b>";							//at Consignee for Delivery
				}
				elseif($row['stop_type_id']==1 && $hl_marrived2!="")
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_marrived2."</b>";											//at Shipper for Pickup
				}
				
			}
			if($truck_distance > ($hl_r_departed - $tolerance) && $row['geofencing_arrived_sent'] > 0)	
			{
				$send_email2="Departed";
				$sector=3;
				$tolist=trim($hl_edeparted);
				
				$subject="Departure Notification: Load Number ".$row['load_handler_id'].": ".$row['name_company']."";  	
				$msg_body="<br><b>Truck ".$row['name_truck']." has departed ".$mrr_local_displayerx.".</b>";   											//Shipper. Pickup has been completed	
     				
     			if($row['stop_type_id']==2 && $hl_mdeparted!="")
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_mdeparted." <br>It is leaving ".$mrr_local_displayerx.".</b>";		//Consignee.  Delivery has been completed
				}
				elseif($row['stop_type_id']==2)
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." has departed ".$mrr_local_displayerx.".</b>";							//Consignee.  Delivery has been completed
				}
				elseif($row['stop_type_id']==1 && $hl_mdeparted2!="")
				{
					$msg_body="<br>Load Status Update: <b>Truck ".$row['name_truck']." ".$hl_mdeparted2."</b>";											//Shipper. Pickup has been completed
				}
				
			}
			$no_gps=0;
		}
		
		$comp_dater="&nbsp;";	
		if(!isset($row['linedate_completed'])) 				$row['linedate_completed']="0000-00-00 00:00:00";	
		if(strtotime($row['linedate_completed']) > 0) 		$comp_dater=$row['linedate_completed'];		
				
		if($no_gps==0)
		{	//if no GPS tracking, don't bother...
     		$tab.="
     			<tr style='background-color:#".($cntr %2==0 ? "eeeeee" : "dddddd")."'>
     				<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a></td>
     				<td valign='top'><a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank'>".$row['load_handler_id']."</a></td>
     				<td valign='top'><a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['trucks_log_id']."' target='_blank'>".$row['trucks_log_id']."</a></td>
     				<td valign='top'>".$row['id']."</td>
     				<td valign='top'>".($row['stop_type_id'] ==2 ? "Consignee" : "Shipper")."</td>
     				
     				<td valign='top'>".$row['shipper_name']."</td>
     				<td valign='top'>".$row['shipper_city']."</td>
     				<td valign='top'>".$row['shipper_state']."</td>
     				<td valign='top'>".$row['linedate_pickup_eta']."</td>
     				<td valign='top'>".$row['linedate_arrival']."</td>
     				<td valign='top'>".$comp_dater."</td>
     				
     				<td valign='top'>".($row['geofencing_arriving_sent'] > 0 ? "<span style='color:green;'><b>Yes</b></span>" : "")."</td>
     				<td valign='top'>".$hl_r_arriving."</td>				
     				
     				<td valign='top'>".($row['geofencing_arrived_sent'] > 0 ? "<span style='color:green;'><b>Yes</b></span>" : "")."</td>
     				<td valign='top'>".$hl_r_arrived."</td>				
     				
     				<td valign='top'>".($row['geofencing_departed_sent'] > 0 ? "<span style='color:green;'><b>Yes</b></span>" : "")."</td>
     				<td valign='top'>".$hl_r_departed."</td>					
     				
     				<td valign='top'>".$row['latitude']."</td>
     				<td valign='top'>".$row['longitude']."</td>
     				<td valign='top'>".$truck_lat."</td>
     				<td valign='top'>".$truck_long."</td>				
     				
     				<td valign='top'>".number_format($miles_distance,4)."</td>
     				<td valign='top'>".$send_email2."</td>
     			</tr>
     		";	     		
     		if($sector > 1)
     		{	//do not send arriving...only arrived(2) and departed(3) .
     			mrr_prep_load_dispatch_stop_for_message($row['load_handler_id'], $row['trucks_log_id'] , $row['id'], $tolist, $subject, $msg_body,$sector,0);
     		}			
     		
     		$cntr++;
		}
	}
	$tab.="</table><br><b>".$cntr." Stops found to validate distance.  Tolerance was ". $tolerance." ft.</b><br>";
	
	return $tab;	
}

function mrr_prep_load_dispatch_stop_for_message($load_id,$disp_id,$stop_id,$tolist="",$subject="",$msg_body="",$sector=0, $phoned=0)
{
	global $defaultsarray;
	$template=(int) $defaultsarray['peoplenet_hot_msg_template_num'];
	
	if($template <= 0)	$template=0;
	if($template > 1)	$template=1;
	$mrr_template="";
		
	$msg_body_header="Automated Conard Transportation Notice";
     $msg_body_footer="<br>This is an automated message. Thank you for using Conard Transportation.<br>";
     	
	//load info...
	$sql="
		select * 
		from load_handler
		where id='".sql_friendly($load_id)."'				
	";
	$data_load=simple_query($sql);
	$row_load=mysqli_fetch_array($data_load);
	     			
	//dispatch info...
	$sql="
		select * 
		from trucks_log
		where id='".sql_friendly($disp_id)."'				
	";
	$data_dispatch=simple_query($sql);
	$row_dispatch=mysqli_fetch_array($data_dispatch);
	
	//stop ...
	$sql="
		select *,
			TIMESTAMPDIFF(MINUTE,'".date("Y-m-d H:i:s",time())."',linedate_pickup_eta) as mrr_time_diff
		from load_handler_stops
		where id='".sql_friendly($stop_id)."'				
	";
	$data_stop=simple_query($sql);
	$row_stop=mysqli_fetch_array($data_stop);
	
	$send_it=1;
	$use_departed=0;		
	if($sector==3)			$use_departed=1;
		
	if(trim($tolist)=="")		$send_it=0;
     if(trim($msg_body)=="")		$send_it=0;
		
	if($send_it ==1)
	{
     	$use_msg_body="".$msg_body_header."".$msg_body."".$msg_body_footer;
     	if($template>0)
     	{
     		$mrr_template=mrr_hot_load_msg_template_V2($template,$row_load,$row_dispatch,$row_stop,$msg_body,$sector);
     		$use_msg_body=$mrr_template;	
     	}   
     	
     	$geo_sent_arriving=$row_stop['geofencing_arriving_sent'];
		$geo_sent_arrived=$row_stop['geofencing_arrived_sent'];
		$geo_sent_departed=$row_stop['geofencing_departed_sent'];   	
     	
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
				where id='".sql_friendly($stop_id)."'				
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
					
				where id='".sql_friendly($stop_id)."'				
			";
			//linedate_arrival=NOW(),
			simple_query($sqlu);
		}
		elseif($sector==3)
		{
			//autograde this stop     		
     		$calc_stop_grade=0;
     		$calc_stop_reason="";
     		
     		if($row_stop['mrr_time_diff'] >= 0 && $row_stop['mrr_time_diff'] <= 30)		{	$calc_stop_grade=5;		$calc_stop_reason="Auto-Graded: 0-30 minutes early.";		}	//On Time
     		elseif($row_stop['mrr_time_diff'] > 30 && $row_stop['mrr_time_diff'] >= 120)	{	$calc_stop_grade=7;		$calc_stop_reason="Auto-Graded: 30-120 minutes early.";	}	//Early
     		elseif($row_stop['mrr_time_diff'] > 120)								{	$calc_stop_grade=8;		$calc_stop_reason="Auto-Graded: more than 2 hrs early.";	}	//Very Early	
     		elseif($row_stop['mrr_time_diff'] < 0 && $row_stop['mrr_time_diff'] >=-60)		{	$calc_stop_grade=4;		$calc_stop_reason="Auto-Graded: 0-60 minutes late.";		}	//Late
     		elseif($row_stop['mrr_time_diff'] < -60 && $row_stop['mrr_time_diff'] >=-180)	{	$calc_stop_grade=3;		$calc_stop_reason="Auto-Graded: 1-3 hours late.";			}	//Very Late
     		elseif($row_stop['mrr_time_diff'] < -180 && $row_stop['mrr_time_diff'] >=-600)	{	$calc_stop_grade=2;		$calc_stop_reason="Auto-Graded: 3-10 hours late.";		}	//Past Due
     		elseif($row_stop['mrr_time_diff'] < -600)								{	$calc_stop_grade=1;		$calc_stop_reason="Auto-Graded: more than 10 hours late.";	}	//Epic Fail
     		     		
     		$sqlu="
				update load_handler_stops set 
					geofencing_arriving_sent='1',
					linedate_geofencing_arriving=NOW(),
					geofencing_arrived_sent='1',              						
					linedate_geofencing_arrived=NOW(),					    				
					geofencing_departed_sent='1',              						
					linedate_geofencing_departed=NOW()
					
				where id='".sql_friendly($stop_id)."'				
			";	//linedate_completed=NOW(), 
			
			//turned off the auto_grading for James...June 2015...using the newer Admin Load Grading page...
			//stop_grade_id='".sql_friendly($calc_stop_grade)."',
			//stop_grade_note='".sql_friendly($calc_stop_reason)."',			
			simple_query($sqlu);
			
			//update_origin_dest($row_stop['load_handler_id']);			
									
			//update the dispatch if necessary.
          	$last_date="0000-00-00";	
          	$sql = "
               	select *		
               	from load_handler_stops
               	where deleted=0
               		and trucks_log_id='".sql_friendly($disp_id)."'
               		and load_handler_id='".sql_friendly($load_id)."'
               		and (linedate_completed is NULL or linedate_completed < '2014-01-01 00:00:00')
               	order by linedate_pickup_eta desc
               ";
               $data = simple_query($sql);
               $mn=mysqli_num_rows($data);
               if($mn == 0)
               {	//no more stops, so flag as completed....
               	$sql2 = "
               		update trucks_log set
               			dispatch_completed='1'
               		where id='".sql_friendly($disp_id)."'
              	 	";
               	//simple_query($sql2);						//turned off the auto_complete for the dispatch...driver may not have gotten loaded/unloaded.  Sept 2015...MRR
               	               	
               	//attempt to make next dispatch...
               	$cur_driver1=0;
               	$cur_driver2=0;
               	$cur_truck=0;
               	$cur_trailer=0;
               	$cur_cust=0;
               	$cur_date="0000-00-00 00:00:00";
               	
               	$stop_name="";
               	$stop_addr1="";
               	$stop_addr2="";
               	$stop_city="";
               	$stop_state="";
               	$stop_zip="";
               	$stop_lat="";
               	$stop_long="";
               	
               	
               	//get basic dispatch settings for later...
               	if($disp_id > 0)
               	{
               		$sql = "
                    	select driver_id,
                    		driver2_id,
                    		truck_id,
                    		trailer_id,
                    		customer_id,
                    		linedate_pickup_eta
                    	from trucks_log
                    	where id='".sql_friendly($disp_id)."'
                    	";
                    	$data = simple_query($sql);
                    	if($row = mysqli_fetch_array($data))
                    	{
                    		$cur_driver1=$row['driver_id'];
               			$cur_driver2=$row['driver2_id'];
               			$cur_truck=$row['truck_id'];
               			$cur_trailer=$row['trailer_id'];	
               			$cur_cust=$row['customer_id'];
               			$cur_date=$row['linedate_pickup_eta'];
                    	}
               	}
               	
               	$now_date="".date("Y-m-d H:i:s",time())."";
               	
               	$sql1 = "
                    	select load_handler_stops.*,
                    		TIMESTAMPDIFF(MINUTE,'".$now_date."',linedate_pickup_eta) as mrr_time_diff	
                    	from load_handler_stops
                    	where id='".sql_friendly($stop_id)."'
                    ";
                    $data1 = simple_query($sql1);
                    $row1 = mysqli_fetch_array($data1);
                    
                    //get basic stop settings for later
                    $stop_name=trim($row1['shipper_name']);
               	$stop_addr1=trim($row1['shipper_address1']);
               	$stop_addr2=trim($row1['shipper_address2']);
               	$stop_city=trim($row1['shipper_city']);
               	$stop_state=trim($row1['shipper_state']);
               	$stop_zip=trim($row1['shipper_zip']);
               	$stop_lat=trim($row1['latitude']);
               	$stop_long=trim($row1['longitude']);
               	
               	$arrv_time=trim($row1['linedate_arrival']);
               	
               	if($row1['end_trailer_id']!=$cur_trailer)		$cur_trailer=$row1['end_trailer_id'];                                            	
                              	
               	$next_disp_text="";
               	
               	//TRUNED OFF AT DONOVAN'S REQUEST...August 2015...MRR
				//$next_disp_text=mrr_find_and_create_next_load_dispatch($disp_id,$cur_driver1,$cur_driver2,$cur_truck,$cur_trailer,$cur_cust,$cur_date,$stop_name,$stop_addr1,$stop_addr2,$stop_city,$stop_state,$stop_zip,$stop_lat,$stop_long);     
               	               	  	
               } //end if		
		}//end sector if
	}//end send_it if
}

function mrr_get_simple_pn_location_for_truck_id($truck_id)
{		
	$mrr_last_city="";
	if($truck_id > 0)
	{
		$res=mrr_find_only_location_of_this_truck($truck_id);
		
		$long=$res['longitude'];
		$lat=$res['longitude'];
		$location=$res['location'];
		$truck_name=$res['truck_name'];
		$map="";
		
		$mrr_last_city="PN: ".$location."".$map."";	// Lat: (".$lat.") and Long: (".$long.")
		
		$mrr_last_city=str_replace("Nashville, TN and In La Vergne, TN On I-24","La Vergne, TN",$mrr_last_city);
		$mrr_last_city=str_replace("Nashville, TN and In La Vergne, TN Near I-24","La Vergne, TN",$mrr_last_city);
		$mrr_last_city=str_replace("Nashville, TN and In Smyrna, TN On I-24","Smyrna, TN",$mrr_last_city);
	}
	return $mrr_last_city;	
}

//PN driver logs....
function mrr_pn_driver_dot_list()
{
	$tab="";
	
	$test_mode=0;
	$use_mode=0;							//0=non CURL, 1 = CURL
	if($use_mode==0 && $test_mode==0)
	{	
		$res=mrr_peoplenet_find_data_for_cron_job("oi_pnet_driver_view",0,0,"",0,0,0,0);		//get list of all drivers...
          
          $buffer=trim($res['xml']);
          $xml_page=json_decode($buffer);
	}
	else
	{
		$xml_page=NULL;	
	}
     
     //should have the page from above...
     if($xml_page==NULL || !isset($xml_page))
     {
     	$tab.="<b>Error:</b> No information available at this time. {USE MODE=".$use_mode." | TEST MODE=".$test_mode."}";
     }
     elseif(substr_count($xml_page,"Bad Request") > 0)
     {
     	$tab.="<b>Error:</b> ".$xml_page.". {USE MODE=".$use_mode." | TEST MODE=".$test_mode."}";
     }
     else
     {          
          $processing="";          
          $all_page = $xml_page;
          
     	$processing.="
     		<table cellpadding='0' cellspacing='0' border='0'>
     		<tr>
     			<td valign='top' colspan='3'><b><span title='CID of company'>Company</span></b></td>	
     			<td valign='top' colspan='2'><b><span title='1=Active, 2=Inactive, 3=All Drivers'>Mode</span></b></td>
     			<td valign='top' colspan='2'><b><span title='Terminal ID'>Terminal</span></b></td>
     			<td valign='top' colspan='3'><b><span title='Total drivers in response'>Drivers</span></b></td>
     			<td valign='top' colspan='3'><b><span title='Total active drivers in response'>Active</span></b></td>
     			<td valign='top' colspan='3'><b><span title='Total inactive drivers in response'>Inactive</span></b></td>
     			<td valign='top' colspan='4'><b><span title='UTC time of response'>Date</span></b></td>
     		</tr>
     		<tr>
     			<td valign='top' colspan='3'>".(string) $all_page->companyId."</td>	
     			<td valign='top' colspan='2'>".(int) $all_page->requestDriverType."</td>
     			<td valign='top' colspan='2'>".(string) $all_page->requestTerminal."</td>
     			<td valign='top' colspan='3'>".(int) $all_page->totalDrivers."</td>
     			<td valign='top' colspan='3'>".(int) $all_page->totalActiveDrivers."</td>
     			<td valign='top' colspan='3'>".(int) $all_page->totalInactiveDrivers."</td>
     			<td valign='top' colspan='4'>".(string) $all_page->responseTimestamp."</td>
     		</tr>
     		<tr>
     			<td valign='top' colspan='20'>&nbsp;</td>
     		</tr>
     		<tr>
     			<td valign='top'><b><span title='PFM Driver ID'>PFMID</span></b></td>	
     			<td valign='top'><b><span title='Customer assigned driver ID'>DriverID</span></b></td>	
     			<td valign='top'><b><span title='Datetime driver was created'>Created</span></b></td>	
     			<td valign='top'><b><span title='Active (1) or Inactive (2)'>Active</span></b></td>	
     			<td valign='top'><b><span title='Full name from driver profile'>Driver Name</span></b></td>	
     			<td valign='top'><b><span title='HOS Reg. US Fed'>USA HOS</span></b></td>	
     			<td valign='top'><b><span title='HOS Reg. Canada'>CAN HOS</span></b></td>	
     			<td valign='top'><b><span title='HOS Reg. US State'>USA HOS</span></b></td>	
     			<td valign='top'><b><span title='HOS Reg. Canada Province'>CAN HOS</span></b></td>	
     			<td valign='top'><b><span title='HOS Reg. Specialty'>Special</span></b></td>	
     			<td valign='top'><b><span title='Terminal ID'>Terminal</span></b></td>	
     			<td valign='top'><b><span title='Current Personal Conveyance US Miles'>Curr USA Miles</span></b></td>	
     			<td valign='top'><b><span title='Pending Personal Conveyance US Miles'>Pend CAN Miles</span></b></td>		
     			<td valign='top'><b><span title='Current Personal Conveyance Km Canada'>Curr CAN KM</span></b></td>	
     			<td valign='top'><b><span title='Pending Personal Conveyance Km Canada'>Pend CAN KM</span></b></td>	
     			<td valign='top'><b><span title='Cust-defined field 1'>Custom1</span></b></td>	
     			<td valign='top'><b><span title='Cust-defined field 2'>Custom2</span></b></td>	
     			<td valign='top'><b><span title='Cust-defined field 3'>Custom3</span></b></td>	
     			<td valign='top'><b><span title='Cust-defined field 4'>Custom4</span></b></td>		
     			<td valign='top'><b><span title='Notes pulled from system'>Notes</span></b></td>	
     		</tr>
     	";
          
          $json_driver = $all_page->Drivers;          
          foreach($json_driver as $driver)
          {
          	$did=(string) $driver->driverID;
          	$did=str_replace("#","",$did);
          	$driver_id=(int) trim($did);
          	
          	$act=(int) $driver->driverStatus;
          	$active="Inactive";		if($act==1)	$active="Active";	
          	
          	$dater=date("Y-m-d H:i:s",strtotime( (string) $driver->driverCreatedDate));
          	
          	$notes=(string)$driver->notes;
          	$notes=trim(str_replace("#","",$notes));          	
          	          	
          	if($act==1)
          	{          	
               	$processing.="
               		<tr>
               			<td valign='top'>".$driver->driverInternalID."</td>	
               			<td valign='top'>".$driver_id."</td>
               			<td valign='top'>".$dater."</td>
               			<td valign='top'>".$active."</td>
               			<td valign='top'><a href='admin_drivers.php?id=".$driver_id."' target='_blank'>".$driver->driverName."</a></td>
               			<td valign='top'>".$driver->hosRegUSFederal."</td>
               			<td valign='top'>".$driver->hosRegCanada."</td>	
               			<td valign='top'>".$driver->hosRegUSState."</td>
               			<td valign='top'>".$driver->hosRegCanProvincial."</td>
               			<td valign='top'>".$driver->hosRegSpecialty."</td>
               			<td valign='top'>".$driver->homeTerminalId."</td>
               			<td valign='top'>".$driver->personalMilesUS."</td>
               			<td valign='top'>".$driver->personalMilesPendingUS."</td>	
               			<td valign='top'>".$driver->personalKilometersCanada."</td>
               			<td valign='top'>".$driver->personalKilometersPendingCanada."</td>
               			<td valign='top'>".$driver->driverUserData1."</td>
               			<td valign='top'>".$driver->driverUserData2."</td>
               			<td valign='top'>".$driver->driverUserData3."</td>
               			<td valign='top'>".$driver->driverUserData4."</td>	
               			<td valign='top'>".$notes."</td>
               		</tr>
          		";
          		
          		
          		$sql = "
          			insert into ".mrr_find_log_database_name()."truck_tracking_drivers
          				(id,
          				driver_id,
          				linedate_added,
          				linedate_gmt,
          				driver_name,
          				notes,
          				hos_usa_fed,
          				hos_can_fed,
          				hos_usa_state,
          				hos_can_state,
          				hos_specialty,
          				terminal_id,
          				pn_id,
          				curr_usa_miles,
          				pend_usa_miles,
          				curr_can_km,
          				pend_can_km,
          				custom1,
          				custom2,
          				custom3,
          				custom4,
          				deleted)
          			values
          				(NULL,
          				'".sql_friendly($driver_id)."',
          				NOW(),
          				'".sql_friendly($dater)."',
          				'".sql_friendly(trim($driver->driverName))."',
          				'".sql_friendly($notes)."',
          				'".sql_friendly($driver->hosRegUSFederal)."',
          				'".sql_friendly($driver->hosRegCanada)."',
          				'".sql_friendly($driver->hosRegUSState)."',
          				'".sql_friendly($driver->hosRegCanProvincial)."',
          				'".sql_friendly($driver->hosRegSpecialty)."',
          				'".sql_friendly(trim($driver->homeTerminalId))."',
          				'".sql_friendly(trim($driver->driverInternalID))."',
          				'".sql_friendly($driver->personalMilesUS)."',
          				'".sql_friendly($driver->personalMilesPendingUS)."',
          				'".sql_friendly($driver->personalKilometersCanada)."',
          				'".sql_friendly($driver->personalKilometersPendingCanada)."',
          				'".sql_friendly(trim($driver->driverUserData1))."',
          				'".sql_friendly(trim($driver->driverUserData2))."',
          				'".sql_friendly(trim($driver->driverUserData3))."',
          				'".sql_friendly(trim($driver->driverUserData4))."',
          				0)
     			";
				simple_query($sql);
     		}      	
          }
          $processing.="</table>";       
          
          $tab.="<br><hr><br><b>Processing: </b><br><p>".$processing."</p>";	// {USE MODE=".$use_mode." | TEST MODE=".$test_mode."}
          
          //$tab.="<br><hr><br><b>Output:</b><br><p>".$output."</p>";
     }	
	return $tab;	
}

function mrr_pn_decode_hos_rules($code="")
{
	$decoder="";
	if(trim($code)=="")		return $decoder;
	
	if(substr_count($code,"30") > 0)	{	$decoder.=" Alberta Provincial";					$code=str_replace("30","",$code);	}
	if(substr_count($code,"29") > 0)	{	$decoder.=" US Federal 70/8 SH - No 30 Min Break*";	$code=str_replace("29","",$code);	}
	if(substr_count($code,"28") > 0)	{	$decoder.=" US Federal 60/7 SH - No 30 Min Break*";	$code=str_replace("28","",$code);	}
	if(substr_count($code,"27") > 0)	{	$decoder.=" USA Oilfield 70/8 - No 30 Min Break";		$code=str_replace("27","",$code);	}
	if(substr_count($code,"26") > 0)	{	$decoder.=" Undefined";							$code=str_replace("26","",$code);	}
	if(substr_count($code,"25") > 0)	{	$decoder.=" US Federal 70/8 LH - No 30 Min Break*";	$code=str_replace("25","",$code);	}
	if(substr_count($code,"24") > 0)	{	$decoder.=" US Federal 60/7 LH - No 30 Min Break*";	$code=str_replace("24","",$code);	}
	if(substr_count($code,"23") > 0)	{	$decoder.=" Canada 60N Cycle2 120/14";				$code=str_replace("23","",$code);	}
	if(substr_count($code,"22") > 0)	{	$decoder.=" Canada 60N Cycle1 80/7";				$code=str_replace("22","",$code);	}
	if(substr_count($code,"21") > 0)	{	$decoder.=" USA Oilfield 70/8";					$code=str_replace("21","",$code);	}
	if(substr_count($code,"20") > 0)	{	$decoder.=" USA Pasngr 70/8";						$code=str_replace("20","",$code);	}
	if(substr_count($code,"19") > 0)	{	$decoder.=" USA Pasngr 60/7";						$code=str_replace("19","",$code);	}
	//if(substr_count($code,"") > 0)	{	$decoder.=" ";		$code=str_replace("","",$code);	}
	if(substr_count($code,"14") > 0)	{	$decoder.=" California 80/8";						$code=str_replace("14","",$code);	}
	if(substr_count($code,"13") > 0)	{	$decoder.=" Florida 80/8";						$code=str_replace("13","",$code);	}
	if(substr_count($code,"12") > 0)	{	$decoder.=" Florida 70/7";						$code=str_replace("12","",$code);	}
	if(substr_count($code,"11") > 0)	{	$decoder.=" Texas 70/7";							$code=str_replace("11","",$code);	}
	if(substr_count($code,"10") > 0)	{	$decoder.=" Canada Cycle2 120/14";					$code=str_replace("10","",$code);	}
	if(substr_count($code,"9") > 0)	{	$decoder.=" Canada Cycle1 70/7";					$code=str_replace("9","",$code);	}
	if(substr_count($code,"8") > 0)	{	$decoder.=" US 70/8 - 16Hr Exemption 395.1(o)";		$code=str_replace("8","",$code);	}
	if(substr_count($code,"7") > 0)	{	$decoder.=" US 60/7 - 16Hr Exemption 395.1(o)";		$code=str_replace("7","",$code);	}
	if(substr_count($code,"6") > 0)	{	$decoder.=" Alaska 80/8";						$code=str_replace("6","",$code);	}
	if(substr_count($code,"5") > 0)	{	$decoder.=" Alaska 70/7";						$code=str_replace("5","",$code);	}
	//if(substr_count($code,"") > 0)	{	$decoder.=" ";		$code=str_replace("","",$code);	}
	if(substr_count($code,"1") > 0)	{	$decoder.=" US Federal 70/8 Long Haul";				$code=str_replace("1","",$code);	}
	if(substr_count($code,"0") > 0)	{	$decoder.=" US Federal 60/7 Long Haul";				$code=str_replace("0","",$code);	}
	
	return trim($decoder);
}
function mrr_pn_decode_country($code="")
{
	$decoder="";
	if(trim($code)=="")		return $decoder;
	
	if(substr_count($code,"0") > 0)	$decoder.="???";	
	if(substr_count($code,"1") > 0)	$decoder.="USA";	
	if(substr_count($code,"2") > 0)	$decoder.="CAN";	
	if(substr_count($code,"3") > 0)	$decoder.="Ala";	
	if(substr_count($code,"4") > 0)	$decoder.="Mex";	
	
	return $decoder;
}
function mrr_pn_decode_avail_secs($secs=0)
{
	$hrs=0;
	if($secs==0)		return $hrs;	
	
	$hrs=$secs / (60 * 60);		//hours
	
	return number_format($hrs,2);	
	
	/*
	ProfileList
	
	(Did NOT change...)
	
	S-1-5-18  has %systemroot%\system32\config\systemprofile
	S-1-5-19  has C:\Windows\ServiceProfiles\LocalService
	S-1-5-20  has C:\Windows\ServiceProfiles\NetworkService
	
	S-1-5-21...  has C:\Users\csherrod
	S-1-5-21...  has C:\Users\Administrator	
	
	Changed all others in the ProfileList directory you set me up to look in...Registry Editor
	Most are some way System100...but there were a few exceptions...but they have been changed.  This is just a warning in case they should not have been.
		
	d:\Users\aboverealitybook.com
	d:\Users\s14.sherrodcomputers.com
	d:\Users\promisebox.com
	
	*/
}
function mrr_pn_decode_date_string($dater="",$mysql=0)
{
	$stamp="";
	if($mysql > 0)				$stamp="0000-00-00 00:00:00";	
	if(trim($dater)=="")		return $stamp;
	
	$stamp=date("Y-m-d H:i:s",strtotime($dater));
	
	return $stamp;
}
function mrr_pn_decode_duty_status($code="")
{
	$decoder="";
	if(trim($code)=="")		return $decoder;
	
	if(substr_count($code,"-1") > 0)	$decoder.="Not Found";
	if(substr_count($code,"0") > 0)	$decoder.="Unknown";	
	if(substr_count($code,"1") > 0)	$decoder.="Driving";	
	if(substr_count($code,"2") > 0)	$decoder.="On Duty";	
	if(substr_count($code,"3") > 0)	$decoder.="Off Duty";	
	if(substr_count($code,"4") > 0)	$decoder.="Sleeper Berth";	
	
	return $decoder;
}

function mrr_pn_driver_dot_list_v2($driver_id=0)
{	//this version uses a different service than the one above...  elog_dispatch_info instead of oi_pnet_driver_view
	$tab="";
	
	$page_loaded="";
	
	$test_mode=0;
	$use_mode=1;							//0=non CURL, 1 = CURL
	if($use_mode==0 && $test_mode==0)
	{	
		$res=mrr_peoplenet_find_data_for_cron_job("elog_dispatch_info",0,0,"",0,0,0,0);		//get list of all drivers...
          
          $buffer=trim($res['xml']);
          $xml_page=$buffer;
	}
	elseif($use_mode==1 && $test_mode==0)
	{	
		global $defaultsarray;
		$pn_cid = $defaultsarray['peoplenet_account_number'];	//"3577";
		$pn_pw = $defaultsarray['peoplenet_account_password'];	//"35con77";
		$pn_cid=trim($pn_cid);
		$pn_pw=trim($pn_pw);
		
		$url="http://oi.pfmlogin.com/scripts/open.dll?cid=".$pn_cid."&pw=".$pn_pw."&service=elog_dispatch_info";		//&driverid=22315
		if($driver_id > 0)		$url.="&driverid=".$driver_id."";
		
		$page_loaded=$url;
		
		$headers = array(  
        		"Content-Type: text/xml"
      	); 	
		
		$curl_handle=curl_init();
		
		curl_setopt($curl_handle, CURLOPT_URL,$url);
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);	
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,10);		
		
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); // we don't want to stop the page from loading if there is ever an issue with the SSL
		
		$xml_page = @curl_exec($curl_handle);
		curl_close($curl_handle);
		
		$xml_page = str_replace('<?xml version="1.0" encoding="ISO-8859-1"?>','',$xml_page);
		$xml_page = str_replace('<!DOCTYPE elog_dispatch_info PUBLIC "-//PeopleNet//elog_dispatch_info" "http://open.peoplenetonline.com/dtd/elog_dispatch_info.dtd">','',$xml_page);
		$xml_page = str_replace(chr(13),'',$xml_page);
		$xml_page = str_replace(chr(10),'',$xml_page);
		$xml_page= simplexml_load_string($xml_page);				
	}
	else
	{
		$xml_page=NULL;	
	}
     
     //should have the page from above...
     if($xml_page==NULL || !isset($xml_page))
     {
     	$tab.="<b>Error:</b> No information available at this time. {USE MODE=".$use_mode." | TEST MODE=".$test_mode."}";
     }
     elseif(substr_count($xml_page,"Bad Request") > 0)
     {
     	$tab.="<b>Error:</b> ".$xml_page.". {USE MODE=".$use_mode." | TEST MODE=".$test_mode."}";
     }
     elseif(substr_count($xml_page,"failure") > 0)
     {
     	$xml_page=str_replace("failure","Failure - ",$xml_page);
     	$xml_page=strip_tags($xml_page);
     	
     	$tab.="<b>Error:</b> ".trim($xml_page).". {USE MODE=".$use_mode." | TEST MODE=".$test_mode."}";
     }
     else
     {          
          $processing="";          
          $all_page = $xml_page;
          
          $cntr=0;
     	$processing.="
     		<table cellpadding='0' cellspacing='0' border='0'>     		
     		<tr>
     			<td valign='top' colspan='6'><b>&nbsp;</b></td>
     			<td valign='top' colspan='10' align='center' style='background-color:#E0B0FF;'><b>Driving Seconds Available (converted hrs)</b></td>
     			<td valign='top' colspan='10' align='center' style='background-color:#CC99FF;'><b>On-duty Seconds Available  (converted hrs)</b></td>
     			<td valign='top' colspan='7'><b>&nbsp;</b></td>
     		</tr>	
     		<tr>	
     			<td valign='top'><b>Date</b></td>  
     			<td valign='top'><b>Truck</b></td>             			
     			<td valign='top'><b>Driver</b></td>     			
     			<td valign='top'><b>Trailer</b></td>
     			<td valign='top'><b>HOS Rules</b></td>
     			               			
     			<td valign='top'><b>Shipping Info</b></td>     	
     					
     			<td valign='top' style='background-color:#E0B0FF;'><b>USA<br>607</b></td>
     			<td valign='top' style='background-color:#E0B0FF;'><b>USA<br>708</b></td>
     			<td valign='top' style='background-color:#E0B0FF;'><b>USA<br>607s</b></td>
     			<td valign='top' style='background-color:#E0B0FF;'><b>USA<br>708s</b></td>
     			<td valign='top' style='background-color:#E0B0FF;'><b>CAN<br>607</b></td>
     			<td valign='top' style='background-color:#E0B0FF;'><b>CAN<br>708</b></td>
     			<td valign='top' style='background-color:#E0B0FF;'><b>CAN<br>12014</b></td>
     			<td valign='top' style='background-color:#E0B0FF;'><b>Ala<br>707</b></td>
     			<td valign='top' style='background-color:#E0B0FF;'><b>Ala<br>808</b></td>
     			<td valign='top' style='background-color:#E0B0FF;'><b>Special</b></td>
     			     			
     			<td valign='top' style='background-color:#CC99FF;'><b>USA<br>607</b></td>
     			<td valign='top' style='background-color:#CC99FF;'><b>USA<br>708</b></td>
     			<td valign='top' style='background-color:#CC99FF;'><b>USA<br>607s</b></td>
     			<td valign='top' style='background-color:#CC99FF;'><b>USA<br>708s</b></td>
     			<td valign='top' style='background-color:#CC99FF;'><b>CAN<br>607</b></td>
     			<td valign='top' style='background-color:#CC99FF;'><b>CAN<br>708</b></td>
     			<td valign='top' style='background-color:#CC99FF;'><b>CAN<br>12014</b></td>
     			<td valign='top' style='background-color:#CC99FF;'><b>Ala<br>707</b></td>
     			<td valign='top' style='background-color:#CC99FF;'><b>Ala<br>808</b></td>
     			<td valign='top' style='background-color:#CC99FF;'><b>Special</b></td>
     			
     			<td valign='top'><b>30min<br>Break</b></td>
     			<td valign='top'><b>24hr<br>Reset</b></td>
     			<td valign='top'><b>34hr<br>Reset</b></td>
     			<td valign='top'><b>36hr<br>Reset</b></td>
     			<td valign='top'><b>72hr<br>Reset</b></td>
     			
     			<td valign='top'><b>Duty<br>Status</b></td>
     			<td valign='top'><b>Status<br>Date</b></td>
     			
     		</tr>
     	";
          
          $json_driver = $all_page->driver_hos_data;          
          foreach($json_driver as $driver)
          {
          	$did=(string) $driver->driverid;
          	$did=str_replace("#","",$did);
          	$driver_id=(int) trim($did);
          	
          	$act=1;
          	          	
          	if($act==1)
          	{          	
               	$processing.="
               		<tr style='background-color:#".($cntr % 2==0 ? "eeeeee" : "dddddd").";'>
               			<td valign='top' nowrap>".mrr_pn_decode_date_string($driver->data_end_date)."</td>   
               			<td valign='top'><b>".$driver->vehicle_number."</b></td>          			
               			<td valign='top' nowrap><a href='admin_drivers.php?id=".$driver_id."' target='_blank'>".$driver->drivername."</a></td>               			
               			<td valign='top'><b>".$driver->trailer_number."</b></td>
               			<td valign='top'>".mrr_pn_decode_hos_rules($driver->hos_rules)."</td>
               			           			
               			<td valign='top'>".$driver->shipping_info."</td>               			
               			
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_usa607)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_usa708)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_usa607short)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_usa708short)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_can607)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_can708)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_can12014)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_alaska707)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_alaska808)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->dsa_spec)."</td>
               			
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_usa607)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_usa708)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_usa607short)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_usa708short)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_can607)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_can708)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_can12014)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_alaska707)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_alaska808)."</td>
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->osa_spec)."</td>
               			
               			<td valign='top'>".mrr_pn_decode_avail_secs($driver->on_sec_until_30minbreak)."</td>
               			<td valign='top'>".mrr_pn_decode_date_string($driver->last_24h_reset)."</td>
               			<td valign='top'>".mrr_pn_decode_date_string($driver->last_34h_reset)."</td>
               			<td valign='top'>".mrr_pn_decode_date_string($driver->last_36h_reset)."</td>
               			<td valign='top'>".mrr_pn_decode_date_string($driver->last_72h_reset)."</td>
               			
               			<td valign='top'>".mrr_pn_decode_duty_status($driver->last_duty_status)."</td>
               			<td valign='top'>".mrr_pn_decode_date_string($driver->last_duty_status_date)."</td>               			
               		</tr>
          		";
          		$sqlx="
          			select id
          			from ".mrr_find_log_database_name()."truck_tracking_driver_logs
          			where deleted=0 
          				and driver_id='".sql_friendly($driver_id)."'
          				and linedate_gmt='".mrr_pn_decode_date_string($driver->data_end_date,1)."'
          		";
          		$datax=simple_query($sqlx);
          		if(mysqli_num_rows($datax) == 0)
          		{          		
               		$sql = "
               			insert into ".mrr_find_log_database_name()."truck_tracking_driver_logs
               				(id,
               				driver_id,
               				linedate_added,
               				linedate_gmt,
               				          				
               				drivername,
               				truck,
               				trailer,
               				rules,
               				shipping_info,
               				country,
               				curr_reg_id,
               				
               				dsa_usa607,
               				dsa_usa708,
               				dsa_usa607short,
               				dsa_usa708short,
               				dsa_can607,
               				dsa_can708,
               				dsa_can12014,
               				dsa_alaska707,
               				dsa_alaska808,
               				dsa_spec,
               				
               				osa_usa607,
               				osa_usa708,
               				osa_usa607short,
               				osa_usa708short,
               				osa_can607,
               				osa_can708,
               				osa_can12014,
               				osa_alaska707,
               				osa_alaska808,
               				osa_spec,
               				          				
               				on_sec_until_30minbreak,
               				last_duty_status,
               				last_duty_status_date,
                    			last_24h_reset,
                    			last_34h_reset,
                    			last_36h_reset,
                    			last_72h_reset,
               				
               				deleted)
               			values
               				(NULL,
               				'".sql_friendly($driver_id)."',
               				NOW(),
               				'".mrr_pn_decode_date_string($driver->data_end_date,1)."',
               				
               				'".sql_friendly(trim($driver->drivername))."',           				
                    			'".sql_friendly(trim($driver->vehicle_number))."',               			           			
                    			'".sql_friendly(trim($driver->trailer_number))."',
                    			'".sql_friendly(trim($driver->hos_rules))."',               			           			
                    			'".sql_friendly(trim($driver->shipping_info))."',      
                    			'".sql_friendly(trim($driver->country))."', 
                    			'".sql_friendly(trim($driver->curr_reg_id))."',  
                    			
                    			'".sql_friendly(trim($driver->dsa_usa607))."',
                    			'".sql_friendly(trim($driver->dsa_usa708))."',
                    			'".sql_friendly(trim($driver->dsa_usa607short))."',
                    			'".sql_friendly(trim($driver->dsa_usa708short))."',
                    			'".sql_friendly(trim($driver->dsa_can607))."',
                    			'".sql_friendly(trim($driver->dsa_can708))."',
                    			'".sql_friendly(trim($driver->dsa_can12014))."',
                    			'".sql_friendly(trim($driver->dsa_alaska707))."',
                    			'".sql_friendly(trim($driver->dsa_alaska808))."',
                    			'".sql_friendly(trim($driver->dsa_spec))."',
                    			
                    			'".sql_friendly(trim($driver->osa_usa607))."',
                    			'".sql_friendly(trim($driver->osa_usa708))."',
                    			'".sql_friendly(trim($driver->osa_usa607short))."',
                    			'".sql_friendly(trim($driver->osa_usa708short))."',
                    			'".sql_friendly(trim($driver->osa_can607))."',
                    			'".sql_friendly(trim($driver->osa_can708))."',
                    			'".sql_friendly(trim($driver->osa_can12014))."',
                    			'".sql_friendly(trim($driver->osa_alaska707))."',
                    			'".sql_friendly(trim($driver->osa_alaska808))."',
                    			'".sql_friendly(trim($driver->osa_spec))."',
                    			
                    			'".sql_friendly(trim($driver->on_sec_until_30minbreak))."',               			
                    			'".sql_friendly(trim($driver->last_duty_status))."',
                    			'".mrr_pn_decode_date_string($driver->last_duty_status_date,1)."',
                    			'".mrr_pn_decode_date_string($driver->last_24h_reset,1)."',
                    			'".mrr_pn_decode_date_string($driver->last_34h_reset,1)."',
                    			'".mrr_pn_decode_date_string($driver->last_36h_reset,1)."',
                    			'".mrr_pn_decode_date_string($driver->last_72h_reset,1)."',
                    			
                    			0)
          			";
     				simple_query($sql);
				}
				$cntr++;
     		}      	
          }
          
          $processing.="</table>";       
          
          $tab.="<br><hr><br><b>Processing: </b><br><p>".$processing."</p>";	// {USE MODE=".$use_mode." | TEST MODE=".$test_mode."}
          
          //$tab.="<br><hr><br><b>Output:</b><br><p>".$output."</p>";
     }	
     $tab.="<br><hr><br><b>URL:</b> <a href='".$page_loaded."'>".$page_loaded."</a><br>";
     
	return $tab;	
}

function mrr_send_manual_pn_cust_emails($stop_id,$test_orverride=0)
{
	if($stop_id==0)		return;
	
	//if($stop_id!=135063)	return;
	
	$sql="
		select load_handler_id,
			trucks_log_id
		from load_handler_stops
		where id = '".sql_friendly($stop_id)."'
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$load_id=$row['load_handler_id'];
		$disp_id=$row['trucks_log_id'];
	}
	else
	{
		return;	
	}
	
	if($load_id==0 || $disp_id==0)		return;
	
	
	global $defaultsarray;
	
	//$fromname=$defaultsarray['company_name'];	
	//$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];
	$template=(int) $defaultsarray['peoplenet_hot_msg_template_num'];
	
	//$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];
	//$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
	
	//$arriving_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	//$arrived_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$departed_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$comp_logo = trim($defaultsarray['peoplenet_hot_msg_logo']);
		
	$monitor_email=$defaultsarray['peoplenet_hot_msg_cc'];
	
	if($template <= 0)	$template=0;
	if($template > 1)	$template=1;
			
	$mrr_template="";
	$msg_body_header="NEW Conard Transportation Notice";
     $subject="";
     $tolist="";
     $msg_body="";
     $msg_body_footer="<br>This is an automated message. Thank you for using Conard Transportation.<br>";
			
	$send_it=1;
	
	if($send_it > 0)
	{	
		
		$sector=3;
		$phoned=0;
		$use_departed=1;
		
		//dispatch info...
		$sql="
			select trucks_log.*,
				customers.name_company,
				customers.send_manual_depart_notice,
				customers.hot_load_switch,
				customers.geofencing_radius_active,
				customers.hot_load_email_departed,
				customers.hot_load_email_msg_departed,
				customers.hot_load_radius_departed,
				trucks.name_truck
			from trucks_log
				left join customers on customers.id=trucks_log.customer_id
				left join trucks on trucks.id=trucks_log.truck_id
			where trucks_log.id='".sql_friendly($disp_id)."'				
		";
		$data_dispatch=simple_query($sql);
		$row_dispatch=mysqli_fetch_array($data_dispatch);
		
		$on_flag1=$row_dispatch['geofencing_radius_active'];
		$on_flag2=$row_dispatch['hot_load_switch'];
		$tolist=trim($row_dispatch['hot_load_email_departed']);
		
		if($row_dispatch['send_manual_depart_notice']==0)		return;
		
		
		if($test_orverride > 0)		$tolist=$defaultsarray['special_email_monitor'];
		if(trim($tolist)=="" && trim($monitor_email)=="")		return;			//no email...turn off.
		if($on_flag1==0 || $on_flag2==0)					return;			//no use of geofence or hot load, turn off.
					
		//load info...
		$sql="
			select * 
			from load_handler
			where id='".sql_friendly($load_id)."'				
		";
		$data_load=simple_query($sql);
		$row_load=mysqli_fetch_array($data_load);
			
		//stop ...
		$sql="
			select * 
			from load_handler_stops
			where id='".sql_friendly($stop_id)."'				
		";
		$data_stop=simple_query($sql);
		$row_stop=mysqli_fetch_array($data_stop);
		
		$subject="Departure Notification: Load Number ".$load_id.": ".$row_dispatch['name_company']."";  	
		
		$mrr_local="".trim($row_stop['shipper_city']).", ".trim($row_stop['shipper_state'])."";
		
				
		$msg_body="<br>Load Status Update: <b>Truck ".$row_dispatch['name_truck']." has departed ".$mrr_local.".</b>";							//Consignee.  Delivery has been completed
		
		if(trim($row_dispatch['hot_load_email_msg_departed'])!="")		$msg_body.="<br><br>".trim($row_dispatch['hot_load_email_msg_departed'])."<br>";
		
		
		$use_msg_body="".$msg_body_header."".$msg_body."".$msg_body_footer;
		if($template>0)
		{
			$mrr_template=mrr_hot_load_msg_template_V2($template,$row_load,$row_dispatch,$row_stop,$msg_body,$sector);
			$use_msg_body=$mrr_template;	
		} 
		
		$note_id=0;
		
		$nres=mrr_geofencing_peoplnet_message($tolist,$subject,$use_msg_body,$phoned,$use_departed);
		$note_id=$nres['sendit'];               
		
		if($note_id > 0)
		{          
               //update sent message     		
          	$sqlu="
     			update load_handler_stops set              						
     				geofencing_departed_sent='1',              						
     				linedate_geofencing_departed=NOW()              						
     			where id='".sql_friendly($stop_id)."'				
     		";
     		simple_query($sqlu);   
     		
     		//mrr_geofencing_peoplnet_message($defaultsarray['special_email_monitor'],"MRR COPY | ".$subject,$use_msg_body,$phoned,$use_departed);
     		mrr_geofencing_peoplnet_message("atomlin@conardtransportation.com","ADAM COPY | ".$subject,$use_msg_body,$phoned,$use_departed);
		}
		else
		{
			mrr_geofencing_peoplnet_message($defaultsarray['special_email_monitor'],"MRR FAIL COPY | ".$subject,$use_msg_body,$phoned,$use_departed);
		}
	}
	
	return;	
}


function mrr_fetch_special_canned_message($msg_id,$load_id,$disp_id=0)
{
	$tab="";
	
	if($msg_id > 0)
	{
		$sql="
     		select *
     		from truck_tracking_canned_message
     		where id='".sql_friendly($msg_id)."'     		
     	";	
     	$data = simple_query($sql);
          if($row = mysqli_fetch_array($data)) 
          {
          	$msg=trim($row['canned_message']);
          	
          	if($load_id > 0)	
          	{
          		$msg=str_replace("[load_id]",$load_id,$msg);
          	}
          	else
          	{
          		$msg=str_replace("Load [load_id]","",$msg);
          	}
          	
          	if($disp_id > 0)	
          	{
          		$msg=str_replace("[dispatch_id]",$disp_id,$msg);
          	}
          	else
          	{
          		$msg=str_replace("Dispatch [dispatch_id]","(Preplanned)",$msg);	
          	}
          	          	
          	$msg=str_replace("[load_id]","",$msg);
          	$msg=str_replace("[dispatch_id]","",$msg);	
          	
          	$tab=$msg;
          }
	}	
	return $tab;		
}


//REPAIR REQUEST FORMS
function mrr_pn_request_form_processor($form_id)
{
	$rep="";
	
	if($form_id==113688 && 1==2)	
	{
		$rep=mrr_pn_request_form_processor_alt($form_id);		//send the PN Inspection Form out to alternate processign function.
		return $rep;	
	}
	
	$cntr=0;
	$rep.="
		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
		<tr>
			<td valign='top'><b>#</b></td>
			<td valign='top'><b>Driver</b></td>
			<td valign='top'><b>Truck</b></td>
			<td valign='top'><b>Trailer</b></td>
			<td valign='top'><b>Urgent</b></td>
			<td valign='top'><b>Due</b></td>
			<td valign='top'><b>Date</b></td>
			<td valign='top'><b>Description</b></td>
			<td valign='top'><b>Location</b></td>
			<td valign='top'><b>Odometer</b></td>
			<td valign='top'><b>Pfx</b></td>
			<td valign='top'><b>GPS</b></td>
		</tr>
	";	
		
	$sql="
     	select * 
     	from ".mrr_find_log_database_name()."pn_request_forms
     	where processed=0
     		and form_id='".sql_friendly($form_id)."'
     	order by linedate_added asc,id asc
     ";
     $data=simple_query($sql); 
	while($row=mysqli_fetch_array($data))
	{
		$request_id=$row['id'];
		$received=date("m/d/Y H:i",strtotime($row['linedate_added']));
		
		$form="";
		if($form_id==125685)	$form=trim($row['form_req_text']);
		if($form_id==126373)	$form=trim($row['form_req_text']);
		
		//if($form_id==113688)	$form=trim($row['form_ins_text']);
		
		$form=str_replace("<formdata>","",$form);					$form=str_replace("</formdata>","",$form);		
		$form=str_replace("<im_field>","",$form);					$form=str_replace("</im_field>","",$form);
		$form=str_replace("<data>","",$form);						$form=str_replace("</data>","",$form);
		
		$form=str_replace("<form_id>".$form_id."</form_id>","",$form);
		$form=str_replace("<empty_at_start>yes</empty_at_start>","",$form);
		$form=str_replace("<empty_at_start>no</empty_at_start>","",$form);
		$form=str_replace("<driver_modified>yes</driver_modified>","",$form);
		$form=str_replace("<driver_modified>no</driver_modified>","",$form);
		$form=str_replace("<mc_choicenum>1</mc_choicenum>","",$form);
		$form=str_replace("<mc_choicenum>2</mc_choicenum>","",$form);
		
		$form=str_replace("<field_number>","<br>[",$form);			$form=str_replace("</field_number>","] ",$form);
		$form=str_replace("<data_text>","",$form);					$form=str_replace("</data_text>","",$form);
		$form=str_replace("<data_numeric>","",$form);				$form=str_replace("</data_numeric>","",$form);
		$form=str_replace("<data_multiple-choice>","",$form);			$form=str_replace("</data_multiple-choice>","",$form);
		$form=str_replace("<mc_choicetext>","",$form);				$form=str_replace("</mc_choicetext>","",$form);
		$form=str_replace("<data_date-time>","",$form);				$form=str_replace("</data_date-time>","",$form);
		$form=str_replace("<data_auto_location>","",$form);			$form=str_replace("</data_auto_location>","",$form);
		$form=str_replace("<data_auto_odometer>","",$form);			$form=str_replace("</data_auto_odometer>","",$form);
		$form=str_replace("<data_auto_odometer_plus_gps>","",$form);	$form=str_replace("</data_auto_odometer_plus_gps>","",$form);
		$form=str_replace("<data_performx_odometer>"," {",$form);		$form=str_replace("</data_performx_odometer>","}",$form);
		$form=str_replace("<data_gps_odometer>"," - {",$form);			$form=str_replace("</data_gps_odometer>","}",$form);
		
		//$form=str_replace("","",$form);			$form=str_replace("","",$form);
		//$form=str_replace("","",$form);			$form=str_replace("","",$form);
		//$form=str_replace("","",$form);			$form=str_replace("","",$form);
		
		/*
		//Trailer 10365 is ID=452
		
		<formdata>
		<form_id>126373</form_id>
		<im_field>
			<field_number>1</field_number>
			<empty_at_start>yes</empty_at_start>
			<driver_modified>yes</driver_modified>
			<data>
				<data_text>Darrell</data_text>
			</data>
		</im_field>
		<im_field>
			<field_number>2</field_number>
			<empty_at_start>yes</empty_at_start>
			<driver_modified>yes</driver_modified>
			<data>
				<data_numeric>002</data_numeric>
			</data>
		</im_field>
		<im_field>
			<field_number>3</field_number>
			<empty_at_start>yes</empty_at_start>
			<driver_modified>yes</driver_modified>
			<data>
				<data_numeric>10365</data_numeric>
			</data>
		</im_field>
		<im_field>
			<field_number>4</field_number>
			<empty_at_start>yes</empty_at_start>
			<driver_modified>yes</driver_modified>
			<data>
				<data_multiple-choice>
					<mc_choicenum>2</mc_choicenum>
					<mc_choicetext>No</mc_choicetext>
				</data_multiple-choice>
			</data>
		</im_field>
		<im_field>
			<field_number>5</field_number>
			<empty_at_start>yes</empty_at_start>
			<driver_modified>yes</driver_modified>
			<data>
				<data_multiple-choice>
					<mc_choicenum>2</mc_choicenum>
					<mc_choicetext>No</mc_choicetext>
				</data_multiple-choice>
			</data>
		</im_field>
		<im_field>
			<field_number>6</field_number>
			<empty_at_start>yes</empty_at_start>
			<driver_modified>yes</driver_modified>
			<data>
				<data_date-time>02/06/17 11:37:00</data_date-time>
			</data>
		</im_field>
		<im_field>
			<field_number>7</field_number>
			<empty_at_start>yes</empty_at_start>
			<driver_modified>yes</driver_modified>
			<data>
				<data_text>none</data_text>
			</data>
		</im_field>
		<im_field>
			<field_number>8</field_number>
			<empty_at_start>yes</empty_at_start>
			<driver_modified>yes</driver_modified>
			<data>
				<data_auto_location>Nashville, TN and In Lebanon, TN On US Hwy 70 (Excellent GPS)</data_auto_location>
			</data>
		</im_field>
		<im_field>
			<field_number>9</field_number>
			<empty_at_start>yes</empty_at_start>
			<driver_modified>yes</driver_modified>
			<data>
				<data_auto_odometer>129515</data_auto_odometer>
				<data_auto_odometer_plus_gps>
					<data_performx_odometer>1295150</data_performx_odometer>
					<data_gps_odometer>1240165</data_gps_odometer>
				</data_auto_odometer_plus_gps>
			</data>
		</im_field>
		</formdata>
		*/
		//Form Field Number Key for Repair Request...Jan 31, 2017.
		//1=Driver Name
		//2=Vehicle Number (not ID)
		//3=Trailer
		//4=Urgent
		//5=Due
		//6=Arrival Date/time
		//7=Description of Request...
		//8=Location of truck (not seen by driver)
		//9=Odometer (not seen by driver)
		
		$driver_name="";
		$truck_name="";
		$trailer_name="";
		$urgent="";
		$due="";
		$date="";
		$desc="";
		$local="";
		$odometer1="";
		$odometer2="";
		$odometer3="";
		
		if(substr_count($form,"[1]") > 1)
		{
			$forms = explode("[1]", $form);
			for($i=0; $i < count($forms); $i++)
			{
				$driver_name="";
     			$truck_name="";
     			$trailer_name="";
     			$urgent="";
     			$due="";
     			$date="";
     			$desc="";
     			$local="";
     			$odometer1="";
     			$odometer2="";
     			$odometer3="";
				
				if(trim($forms[$i])!="")
				{
					$line=trim($forms[$i]);
					$line=str_replace("<br>","",$line);
					
					$pos1=0;	//strpos($line,"[2]");
					$pos2=strpos($line,"[2]");
					if($pos2 > 0)
					{
						$driver_name=substr($line,$pos1,($pos2 - $pos1));
						$driver_name=str_replace("[1]","",$driver_name);
					}
										
					$pos1=strpos($line,"[2]");
					$pos2=strpos($line,"[3]");
					//if($pos2 == 0)		$pos2=strpos($line,"[4]");
					if($pos2 > 0)
					{
						$truck_name=substr($line,$pos1,($pos2 - $pos1));
						$truck_name=str_replace("[2]","",$truck_name);
					}
					
					$pos1=strpos($line,"[3]");
					$pos2=strpos($line,"[4]");
					if($pos2 > 0)
					{
						$trailer_name=substr($line,$pos1,($pos2 - $pos1));
						$trailer_name=str_replace("[3]","",$trailer_name);
					}
										
					$pos1=strpos($line,"[4]");
					$pos2=strpos($line,"[5]");
					if($pos2 > 0)
					{
						$urgent=substr($line,$pos1,($pos2 - $pos1));
						$urgent=str_replace("[4]","",$urgent);
					}
										
					$pos1=strpos($line,"[5]");
					$pos2=strpos($line,"[6]");
					if($pos2 > 0)
					{
						$due=substr($line,$pos1,($pos2 - $pos1));
						$due=str_replace("[5]","",$due);
					}
										
					$pos1=strpos($line,"[6]");
					$pos2=strpos($line,"[7]");
					if($pos2 > 0)
					{
						$date=substr($line,$pos1,($pos2 - $pos1));
						$date=str_replace("[6]","",$date);
					}
										
					$pos1=strpos($line,"[7]");
					$pos2=strpos($line,"[8]");
					if($pos2 > 0)
					{
						$desc=substr($line,$pos1,($pos2 - $pos1));
						$desc=str_replace("[7]","",$desc);
					}
										
					$pos1=strpos($line,"[8]");
					$pos2=strpos($line,"[9]");
					if($pos2 > 0)
					{
						$local=substr($line,$pos1,($pos2 - $pos1));
						$local=str_replace("[8]","",$local);
					}
					
					$pos1=strpos($line,"[9]");
					//$pos2=strpos($line,"[2]");
					if($pos1 > 0)
					{
						$odometer1=substr($line,$pos1);		//rest of the string...
						$odometer1=str_replace("[9]","",$odometer1);
						
     					$pos1=strpos($odometer1," {");
     					$pos2=strpos($odometer1,"} - {");
     					if($pos2 > 0)
     					{
     						$odometer2=substr($odometer1,$pos1,($pos2 - $pos1));
     						$odometer2=str_replace("} - {","",$odometer2);
     						$odometer2=str_replace("}","",$odometer2);
     						$odometer2=str_replace(" {","",$odometer2);
     						
     						$odometer3=substr($odometer1,$pos2);
     						$odometer3=str_replace("} - {","",$odometer3);
     						$odometer3=str_replace("}","",$odometer3);
     						$odometer3=str_replace(" {","",$odometer3);
     					}
     					$odometer1=str_replace($odometer3,"",$odometer1);
     					$odometer1=str_replace($odometer2,"",$odometer1);
     					$odometer1=str_replace("} - {","",$odometer1);
     					$odometer1=str_replace("}","",$odometer1);
     					$odometer1=str_replace(" {","",$odometer1);
					}
          					
          					
          			$driver_id=0;		// [".$driver_id."]
          			$truck_id=0;
          			$trailer_id=0;
          			
          			if(trim($truck_name)=="(skipped)")		$truck_name="";
          			if(trim($truck_name)!="")
          			{
          				$sql2 = "select id from trucks where deleted = 0 and (name_truck = '".sql_friendly(trim($truck_name)) ."' or name_truck = '".sql_friendly(trim($truck_name)) ." (Team Rate)') order by name_truck asc,id asc";
               			$data2 = simple_query($sql2);
               			if($row2 = mysqli_fetch_array($data2))
               			{
               				$truck_id=$row2['id'];                                 			
               			}
               		}
               		
               		if(trim($trailer_name)=="(skipped)")		$trailer_name="";
               		if(trim($trailer_name)!="")
               		{
               			$sql2 = "select id from trailers where deleted = 0 and (trailer_name = '".sql_friendly(trim($trailer_name)) ."' or nick_name = '".sql_friendly(trim($trailer_name)) ."') order by trailer_name asc,id asc";
               			$data2 = simple_query($sql2);
               			if($row2 = mysqli_fetch_array($data2))
               			{
               				$trailer_id=$row2['id'];                                 			
               			}
               		}
               		
               		//$is_due=0;		if(trim($due)=="Yes")		$is_due=1;
               		$is_urgent=0;		if(trim($urgent)=="Yes")		$is_urgent=1;
               		
               		$maint_mode=58;	$equip_id=$truck_id;
               		if($trailer_id > 0 && trim($trailer_name)!="")	
               		{	//58=truck, 59=trailer...
               			$maint_mode=59;	$equip_id=$trailer_id;
               		}
               		
               		if($equip_id > 0)
               		{
                    		$sqlu="
               				update ".mrr_find_log_database_name()."pn_request_forms set 
               					truck_id='".sql_friendly($truck_id) ."',
               					trailer_id='".sql_friendly($trailer_id) ."',
               					driver_name='".sql_friendly(trim($driver_name)) ."',
               					driver_id='".sql_friendly($driver_id) ."' 
               				where id='".sql_friendly($request_id) ."'";
                    		simple_query($sqlu);
                    		
                    		// --Trailer ".trim($trailer_name)."
                    		$new_req_id=mrr_auto_create_maint_request($maint_mode,$equip_id,trim($desc)." --".trim($driver_name),$is_urgent,$odometer1,trim($date),trim($local));		
                    		if($new_req_id > 0)
                    		{
                    			$sqlu="update ".mrr_find_log_database_name()."pn_request_forms set processed='1',request_id='".sql_friendly($new_req_id)."' where id='".sql_friendly($request_id)."'";
                    			simple_query($sqlu);		
                    		}
               		}
               						
					$rep.="
               			<tr style='background-color:#".($cntr % 2==0 ? "eeeeee" : "dddddd").";'>
               				<td valign='top'>".($cntr + 1)." (B)</td>
               				<td valign='top'>".trim($driver_name)."</td>
               				<td valign='top'>".trim($truck_name)." [".$truck_id."]</td>
               				<td valign='top'>".trim($trailer_name)." [".$trailer_id."]</td>
               				<td valign='top'>".trim($urgent)."</td>
               				<td valign='top'>".trim($due)."</td>
               				<td valign='top'>".trim($date)."</td>
               				<td valign='top'>".trim($desc)."</td>
               				<td valign='top'>".trim($local)."</td>
               				<td valign='top'>".trim($odometer1)."</td>
               				<td valign='top'>".trim($odometer2)."</td>
               				<td valign='top'>".trim($odometer3)."</td>
               			</tr>
               		";				
               		//$rep.="<tr style='background-color:#".($cntr % 2==0 ? "eeeeee" : "dddddd").";'><td valign='top' colspan='11'>".trim($line)."</td></tr>";	
					$cntr++;
					//end multi-form in one block.						
				}	
			}
		}
		elseif(substr_count($form,"[1]") > 0)
		{
			$line=trim($form);
			$line=str_replace("<br>","",$line);
			
			$pos1=strpos($line,"[1]");
			$pos2=strpos($line,"[2]");
			if($pos2 > 0)
			{
				$driver_name=substr($line,$pos1,($pos2 - $pos1));
				$driver_name=str_replace("[1]","",$driver_name);
			}
								
			$pos1=strpos($line,"[2]");
			$pos2=strpos($line,"[3]");
			//if($pos2 == 0)		$pos2=strpos($line,"[4]");
			if($pos2 > 0)
			{
				$truck_name=substr($line,$pos1,($pos2 - $pos1));
				$truck_name=str_replace("[2]","",$truck_name);
			}
			
			$pos1=strpos($line,"[3]");
			$pos2=strpos($line,"[4]");
			if($pos2 > 0)
			{
				$trailer_name=substr($line,$pos1,($pos2 - $pos1));
				$trailer_name=str_replace("[3]","",$trailer_name);
			}
							
			$pos1=strpos($line,"[4]");
			$pos2=strpos($line,"[5]");
			if($pos2 > 0)
			{
				$urgent=substr($line,$pos1,($pos2 - $pos1));
				$urgent=str_replace("[4]","",$urgent);
			}
								
			$pos1=strpos($line,"[5]");
			$pos2=strpos($line,"[6]");
			if($pos2 > 0)
			{
				$due=substr($line,$pos1,($pos2 - $pos1));
				$due=str_replace("[5]","",$due);
			}
								
			$pos1=strpos($line,"[6]");
			$pos2=strpos($line,"[7]");
			if($pos2 > 0)
			{
				$date=substr($line,$pos1,($pos2 - $pos1));
				$date=str_replace("[6]","",$date);
			}
								
			$pos1=strpos($line,"[7]");
			$pos2=strpos($line,"[8]");
			if($pos2 > 0)
			{
				$desc=substr($line,$pos1,($pos2 - $pos1));
				$desc=str_replace("[7]","",$desc);
			}
								
			$pos1=strpos($line,"[8]");
			$pos2=strpos($line,"[9]");
			if($pos2 > 0)
			{
				$local=substr($line,$pos1,($pos2 - $pos1));
				$local=str_replace("[8]","",$local);
			}
			
			$pos1=strpos($line,"[9]");
			//$pos2=strpos($line,"[2]");
			if($pos1 > 0)
			{
				$odometer1=substr($line,$pos1);		//rest of the string...
				$odometer1=str_replace("[9]","",$odometer1);
				
				$pos1=strpos($odometer1," {");
				$pos2=strpos($odometer1,"} - {");
				if($pos2 > 0)
				{
					$odometer2=substr($odometer1,$pos1,($pos2 - $pos1));
					$odometer2=str_replace("} - {","",$odometer2);
					$odometer2=str_replace("}","",$odometer2);
					$odometer2=str_replace(" {","",$odometer2);
					
					$odometer3=substr($odometer1,$pos2);
					$odometer3=str_replace("} - {","",$odometer3);
					$odometer3=str_replace("}","",$odometer3);
					$odometer3=str_replace(" {","",$odometer3);
				}
				$odometer1=str_replace($odometer3,"",$odometer1);
				$odometer1=str_replace($odometer2,"",$odometer1);
				$odometer1=str_replace("} - {","",$odometer1);
				$odometer1=str_replace("}","",$odometer1);
				$odometer1=str_replace(" {","",$odometer1);
			}
			
			$driver_id=0;		// [".$driver_id."]
			$truck_id=0;
			$trailer_id=0;
			
			if(trim($truck_name)=="(skipped)")		$truck_name="";
			if(trim($truck_name)!="")	
			{
     			$sql2 = "select id from trucks where deleted = 0 and (name_truck = '".sql_friendly(trim($truck_name)) ."' or name_truck = '".sql_friendly(trim($truck_name)) ." (Team Rate)') order by name_truck asc,id asc";
          		$data2 = simple_query($sql2);
          		if($row2 = mysqli_fetch_array($data2))
          		{
          			$truck_id=$row2['id'];                                 			
          		}
     		}
     		
     		if(trim($trailer_name)=="(skipped)")		$trailer_name="";
     		if(trim($trailer_name)!="")
     		{
     			$sql2 = "select id from trailers where deleted = 0 and (trailer_name = '".sql_friendly(trim($trailer_name)) ."' or nick_name = '".sql_friendly(trim($trailer_name)) ."') order by trailer_name asc,id asc";
     			$data2 = simple_query($sql2);
     			if($row2 = mysqli_fetch_array($data2))
     			{
     				$trailer_id=$row2['id'];                                 			
     			}
     		}
			
     		//$is_due=0;		if(trim($due)=="Yes")		$is_due=1;
     		$is_urgent=0;		if(trim($urgent)=="Yes")		$is_urgent=1;
               $maint_mode=58;	$equip_id=$truck_id;
               if($trailer_id > 0 && trim($trailer_name)!="")	
               {	//58=truck, 59=trailer...
               	$maint_mode=59;	$equip_id=$trailer_id;
               }
               if($equip_id > 0)
               {
     			$sqlu="
     				update ".mrr_find_log_database_name()."pn_request_forms set 
     					truck_id='".sql_friendly($truck_id) ."',
     					trailer_id='".sql_friendly($trailer_id) ."',
     					driver_name='".sql_friendly(trim($driver_name)) ."',
     					driver_id='".sql_friendly($driver_id) ."' 
     				where id='".sql_friendly($request_id) ."'";
          		simple_query($sqlu);         		
                    		
                    //--Trailer ".trim($trailer_name)."
          		$new_req_id=mrr_auto_create_maint_request($maint_mode,$equip_id,trim($desc)."  --".trim($driver_name),$is_urgent,$odometer1,trim($date),trim($local));	
          		if($new_req_id > 0)
          		{
          			$sqlu="update ".mrr_find_log_database_name()."pn_request_forms set processed='1',request_id='".sql_friendly($new_req_id)."' where id='".sql_friendly($request_id)."'";
          			simple_query($sqlu);		
          		}
     		}
     		$rep.="
     			<tr style='background-color:#".($cntr % 2==0 ? "eeeeee" : "dddddd").";'>
     				<td valign='top'>".($cntr + 1)." (A)</td>
     				<td valign='top'>".trim($driver_name)."</td>
     				<td valign='top'>".trim($truck_name)." [".$truck_id."]</td>
     				<td valign='top'>".trim($trailer_name)." [".$trailer_id."]</td>
     				<td valign='top'>".trim($urgent)."</td>
     				<td valign='top'>".trim($due)."</td>
     				<td valign='top'>".trim($date)."</td>
     				<td valign='top'>".trim($desc)."</td>
     				<td valign='top'>".trim($local)."</td>
     				<td valign='top'>".trim($odometer1)."</td>
     				<td valign='top'>".trim($odometer2)."</td>
     				<td valign='top'>".trim($odometer3)."</td>
     			</tr>
     		";				
     		//$rep.="<tr style='background-color:#".($cntr % 2==0 ? "eeeeee" : "dddddd").";'><td valign='top' colspan='11'>".trim($form)."</td></tr>";
     		$cntr++;
		}		
	}	
	$rep.="
		<tr>
			<td valign='top' colspan='11'><b>".$cntr." Requests found to process.</b></td>
		</tr>
	</table><br>";
		
	return $rep;
}

function mrr_pn_request_form_processor_alt($form_id)
{
	$rep="";
	
	$cntr=0;
	$rep.="
		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
		<tr>
			<td valign='top'><b>#</b></td>
			<td valign='top'><b>Driver</b></td>
			<td valign='top'><b>Truck</b></td>
			<td valign='top'><b>Trailer</b></td>
			<td valign='top'><b>&nbsp;</b></td>
			<td valign='top'><b>&nbsp;</b></td>
			<td valign='top'><b>Date</b></td>
			<td valign='top'><b>Description</b></td>
			<td valign='top'><b>GPS/Location</b></td>
			<td valign='top'><b>&nbsp;</b></td>
			<td valign='top'><b>&nbsp;</b></td>
			<td valign='top'><b>Odometer</b></td>
		</tr>
	";	
	
	//Question 18 (cat wrong) (Trailer Defects)
	$trailer_defect[0]="";
	$trailer_defect[1]="Brake Connections";
	$trailer_defect[2]="Brakes";
	$trailer_defect[3]="Coupling Devices";
	$trailer_defect[4]="Coupling Pin";
	$trailer_defect[5]="Doors";
	$trailer_defect[6]="Hitch";
	$trailer_defect[7]="Landing Gear";
	$trailer_defect[8]="Lights";
	$trailer_defect[9]="Suspension";
	$trailer_defect[10]="Tarpaulin";
	$trailer_defect[11]="Tires";
	$trailer_defect[12]="Wheels Rims";
	$trailer_defect[13]="Other";
	
	//(Tractor Defects)
	$truck_defect[0]="";
	$truck_defect[1]="Air Compressor";
	$truck_defect[2]="Air Lines";
	$truck_defect[3]="Battery";
	$truck_defect[4]="Body";
	$truck_defect[5]="Brake Accessories";
	$truck_defect[6]="Parking Brakes";
	$truck_defect[7]="Service Brakes";
	$truck_defect[8]="Clutch";
	$truck_defect[9]="Coupling Devices";
	$truck_defect[10]="Defroster or Heater";
	$truck_defect[11]="Drive Line";
	$truck_defect[12]="Engine";
	$truck_defect[13]="Exhaust";
	$truck_defect[14]="Fifth Wheel";
	$truck_defect[15]="Frame and Assembly";
	$truck_defect[16]="Front Axle";
	$truck_defect[17]="Fuel Tanks";
	$truck_defect[18]="Horn";
	$truck_defect[19]="Lights";
	$truck_defect[20]="Mirrors";
	$truck_defect[21]="Muffler";
	$truck_defect[22]="Oil Pressure";
	$truck_defect[23]="Radiator";
	$truck_defect[24]="Rear End";
	$truck_defect[25]="Reflectors";
	$truck_defect[26]="Safety Equipment";
	$truck_defect[27]="Suspension System";
	$truck_defect[28]="Starter";
	$truck_defect[29]="Steering";
	$truck_defect[30]="Tachograph";
	$truck_defect[31]="Tires";
	$truck_defect[32]="Transmission";
	$truck_defect[33]="Wheels and Rims";
	$truck_defect[34]="Windows";
	$truck_defect[35]="Windshield Wipers";
	$truck_defect[36]="Other";
		
	$sql="
     	select * 
     	from ".mrr_find_log_database_name()."pn_request_forms
     	where processed=0
     		and form_id='".sql_friendly($form_id)."'
     	order by linedate_added asc,id asc
     ";
     $data=simple_query($sql); 
	while($row=mysqli_fetch_array($data))
	{
		$request_id=$row['id'];
		$received=date("m/d/Y H:i",strtotime($row['linedate_added']));
		
		$form="";
		//if($form_id==125685)	$form=trim($row['form_req_text']);
		//if($form_id==126373)	$form=trim($row['form_req_text']);
		
		if($form_id==113688)	$form=trim($row['form_ins_text']);
		
		$form=str_replace("<formdata>","",$form);					$form=str_replace("</formdata>","",$form);		
		$form=str_replace("<im_field>","",$form);					$form=str_replace("</im_field>","",$form);
		$form=str_replace("<data>","",$form);						$form=str_replace("</data>","",$form);
		
		$form=str_replace("<form_id>".$form_id."</form_id>","",$form);
		$form=str_replace("<empty_at_start>yes</empty_at_start>","",$form);
		$form=str_replace("<empty_at_start>no</empty_at_start>","",$form);
		$form=str_replace("<driver_modified>yes</driver_modified>","",$form);
		$form=str_replace("<driver_modified>no</driver_modified>","",$form);
		$form=str_replace("<mc_choicenum>1</mc_choicenum>","",$form);
		$form=str_replace("<mc_choicenum>2</mc_choicenum>","",$form);
		
		$form=str_replace("<field_number>","<br>[",$form);			$form=str_replace("</field_number>","] ",$form);
		$form=str_replace("<data_text>","",$form);					$form=str_replace("</data_text>","",$form);
		$form=str_replace("<data_numeric>","",$form);				$form=str_replace("</data_numeric>","",$form);
		$form=str_replace("<data_multiple-choice>","",$form);			$form=str_replace("</data_multiple-choice>","",$form);
		$form=str_replace("<mc_choicetext>","",$form);				$form=str_replace("</mc_choicetext>","",$form);
		$form=str_replace("<data_date-time>","",$form);				$form=str_replace("</data_date-time>","",$form);
		$form=str_replace("<data_auto_location>","",$form);			$form=str_replace("</data_auto_location>","",$form);
		$form=str_replace("<data_auto_odometer>","",$form);			$form=str_replace("</data_auto_odometer>","",$form);
		$form=str_replace("<data_auto_odometer_plus_gps>","",$form);	$form=str_replace("</data_auto_odometer_plus_gps>","",$form);
		$form=str_replace("<data_performx_odometer>"," {",$form);		$form=str_replace("</data_performx_odometer>","}",$form);
		$form=str_replace("<data_gps_odometer>"," - {",$form);			$form=str_replace("</data_gps_odometer>","}",$form);
		
		$form=str_replace("<data_auto_date-time>","",$form);			$form=str_replace("</data_auto_date-time>","",$form);
		$form=str_replace("<data_auto_fuel>","",$form);				$form=str_replace("</data_auto_fuel>","",$form);
			
		$form=str_replace("<data_auto_latlong>","",$form);			$form=str_replace("</data_auto_latlong>","",$form);
		$form=str_replace("<latitude>","",$form);					$form=str_replace("</latitude>",",",$form);
		$form=str_replace("<longitude>","",$form);					$form=str_replace("</longitude>","",$form);
		/*
		//		
		[formdata]
			[form_id]113688[/form_id]
			[im_field]
				[field_number]2[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_text]96364[/data_text]
				[/data]
			[/im_field]
			[im_field]
				[field_number]3[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_multiple-choice]
						[mc_choicenum]2[/mc_choicenum]
						[mc_choicetext]Yes[/mc_choicetext]
					[/data_multiple-choice]
				[/data]
			[/im_field]
			[im_field]
				[field_number]4[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_multiple-choice]
						[mc_choicenum]1[/mc_choicenum]
						[mc_choicetext]No[/mc_choicetext]
					[/data_multiple-choice]
				[/data]
			[/im_field]
			[im_field]
				[field_number]14[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_multiple-choice]
						[mc_choicenum]2[/mc_choicenum]
						[mc_choicetext]Yes[/mc_choicetext]
					[/data_multiple-choice]
				[/data]
			[/im_field]
			[im_field]
				[field_number]15[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_multiple-choice]
						[mc_choicenum]1[/mc_choicenum]
						[mc_choicetext]No[/mc_choicetext]
					[/data_multiple-choice]
				[/data]
			[/im_field]
			[im_field]
				[field_number]18[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_multiple-choice]
						[mc_choicenum]13[/mc_choicenum]
						[mc_choicetext]Other[/mc_choicetext]
					[/data_multiple-choice]
				[/data]
			[/im_field]
			[im_field]
				[field_number]20[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_text]4 roof bows need rivets[/data_text]
				[/data]
			[/im_field]
			[im_field]
				[field_number]22[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_multiple-choice]
						[mc_choicenum]1[/mc_choicenum]
						[mc_choicetext]No[/mc_choicetext]
					[/data_multiple-choice]
				[/data]
			[/im_field]
			[im_field]
				[field_number]27[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_multiple-choice]
						[mc_choicenum]2[/mc_choicenum]
						[mc_choicetext]Yes[/mc_choicetext]
					[/data_multiple-choice]
				[/data]
			[/im_field]
			[im_field]
				[field_number]28[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_text]grady hough[/data_text]
				[/data]
			[/im_field]
			[im_field]
				[field_number]30[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_auto_date-time]02/13/2017 14:30:53 GMT[/data_auto_date-time]
				[/data]
			[/im_field]
			[im_field]
				[field_number]31[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_auto_fuel]13556[/data_auto_fuel]
				[/data]
			[/im_field]
			[im_field]
				[field_number]32[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_auto_odometer]92621[/data_auto_odometer]
					[data_auto_odometer_plus_gps]
						[data_performx_odometer]926219[/data_performx_odometer]
						[data_gps_odometer]880822[/data_gps_odometer]
					[/data_auto_odometer_plus_gps]
				[/data]
			[/im_field]
			[im_field]
				[field_number]33[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_auto_latlong]
						[latitude]36.0922658443451 [/latitude]
						[longitude]-86.7487120628357 [/longitude]
					[/data_auto_latlong]
				[/data]
			[/im_field]
			[im_field]
				[field_number]34[/field_number]
				[empty_at_start]yes[/empty_at_start]
				[driver_modified]yes[/driver_modified]
				[data]
					[data_auto_location]In Nashville, TN and 4.4m N of Brentwood, TN On I-65 (Excellent GPS)[/data_auto_location]
				[/data]
			[/im_field]
		[/formdata]
		*/
		//Form Field Number Key for Repair Request...Jan 31, 2017.
		//2=Unit Number (Trailer)
		//3=Defect Found
		//4=Tractor Defect
		//14=Is Tractor Safe
		//15=
		//18=Cat Wrong (see below)		
		//20=Description of Request...
		//22=
		//27=
		//28=Driver Name
		//30=Date sent (auto)
		//31=Fuel (not seen by driver)
		//32=Odometer (not seen by driver)
		//33=GPS (Long/Lat)
		//34=Location of truck (not seen by driver)
		
		//Question 18 (cat wrong) (Trailer Defects)
		//$trailer_defect[0]="";
		
		//(Tractor Defects)
		//$truck_defect[0]="";
				
		$driver_name="";
		$truck_name="";
		$trailer_name="";
		$urgent="";
		$due="";
		$date="";
		$desc="";
		$local="";
		$odometer1="";
		$odometer2="";
		$odometer3="";
		$fuel="";
		$long="";
		$lat="";
		
		$flag_3="";
		$flag_4="";
		$flag_14="";
		$flag_15="";
		$flag_22="";
		$flag_27="";
		
		$cat_id=0;
		$cat_name="";
		$tcat_id=0;
		$tcat_name="";
						
		if(substr_count($form,"[1]") > 0 || substr_count($form,"[2]") > 0)
		{
			$line=trim($form);
			$line=str_replace("<br>","",$line);
			
			$pos1=strpos($line,"[1]");
			$pos2=strpos($line,"[2]",$pos1);
			if($pos2 > 0)
			{
				$truck_name=substr($line,$pos1,($pos2 - $pos1));
				$truck_name=str_replace("[1]","",$truck_name);
			}
								
			$pos1=strpos($line,"[2]");
			$pos2=strpos($line,"[3]",$pos1);
			//if($pos2 == 0)		$pos2=strpos($line,"[4]");
			if($pos2 > 0)
			{
				$trailer_name=substr($line,$pos1,($pos2 - $pos1));
				$trailer_name=str_replace("[2]","",$trailer_name);
			}
			
			if(substr_count($form,"[4]") > 0)
			{
				$pos1=strpos($line,"[3]");
				$pos2=strpos($line,"[4]",$pos1);
				if($pos2 > 0)
				{
					$flag_3=substr($line,$pos1,($pos2 - $pos1));
					$flag_3=str_replace("[3]","",$flag_3);				
				}
				
				$pos1=strpos($line,"[4]");
				$pos2=strpos($line,"[14]",$pos1);
				if($pos2 > 0)
				{
					$flag_4=substr($line,$pos1,($pos2 - $pos1));
					$flag_4=str_replace("[4]","",$flag_4);					
				}
				
				$pos1=strpos($line,"[14]");
				$pos2=strpos($line,"[15]",$pos1);
				if($pos2 > 0)
				{
					$flag_14=substr($line,$pos1,($pos2 - $pos1));
					$flag_14=str_replace("[14]","",$flag_14);					
				}
				
				$pos1=strpos($line,"[15]");
				$pos2=strpos($line,"[18]",$pos1);
				if($pos2 > 0)
				{
					$flag_15=substr($line,$pos1,($pos2 - $pos1));
					$flag_15=str_replace("[15]","",$flag_15);
				}
								
				$pos1=strpos($line,"[18]");
				$pos2=strpos($line,"[20]",$pos1);
				if($pos2 > 0)
				{
					$temp=substr($line,$pos1,($pos2 - $pos1));
					$temp=str_replace("[18]","",$temp);
					
					$cat_id=0;
					$cat_name="";
					
					for($x=1;$x <= 13; $x++)
					{
						if(substr_count($temp,"".$x." ") > 0 || substr_count($temp,trim($trailer_defect[$x])) > 0)
						{
							$cat_id=$x;
							$cat_name=trim($trailer_defect[$x]);	
						}	
					}
				}
								
				$pos1=strpos($line,"[20]");
				$pos2=strpos($line,"[22]",$pos1);
				if($pos2 > 0)
				{
					$desc=substr($line,$pos1,($pos2 - $pos1));
					$desc=str_replace("[20]","",$desc);					
				}
								
				$pos1=strpos($line,"[22]");
				$pos2=strpos($line,"[27]",$pos1);
				if($pos2 > 0)
				{
					$flag_22=substr($line,$pos1,($pos2 - $pos1));
					$flag_22=str_replace("[22]","",$flag_22);					
				}
			}
			else
			{
				$pos1=strpos($line,"[3]");
				$pos2=strpos($line,"[27]",$pos1);
				if($pos2 > 0)
				{
					$flag_3=substr($line,$pos1,($pos2 - $pos1));
					$flag_3=str_replace("[3]","",$flag_3);
				}	
			}
			
			$pos1=strpos($line,"[27]");
			$pos2=strpos($line,"[28]",$pos1);
			if($pos2 > 0)
			{
				$flag_27=substr($line,$pos1,($pos2 - $pos1));
				$flag_27=str_replace("[27]","",$flag_27);
			}
			
			$pos1=strpos($line,"[28]");
			$pos2=strpos($line,"[30]",$pos1);
			if($pos2 > 0)
			{
				$driver_name=substr($line,$pos1,($pos2 - $pos1));
				$driver_name=str_replace("[28]","",$driver_name);
			}
			
			$pos1=strpos($line,"[30]");
			$pos2=strpos($line,"[31]",$pos1);
			if($pos2 > 0)
			{
				$date=substr($line,$pos1,($pos2 - $pos1));
				$date=str_replace("[30]","",$date);
			}
			
			$pos1=strpos($line,"[31]");
			$pos2=strpos($line,"[32]",$pos1);
			if($pos2 > 0)
			{
				$fuel=substr($line,$pos1,($pos2 - $pos1));
				$fuel=str_replace("[31]","",$fuel);
			}
			
			$pos1=strpos($line,"[32]");
			$pos2=strpos($line,"[33]",$pos1);
			if($pos2 > 0)
			{
				$odometer1=substr($line,$pos1,($pos2 - $pos1));		//rest of the string...
				$odometer1=str_replace("[32]","",$odometer1);
				
				$posa=strpos($odometer1," {");
				$posb=strpos($odometer1,"} - {");
				if($posb > 0 && $posa > 0)
				{
					$odometer2="".substr($odometer1,$posa,($posb - $posa));
					$odometer2=str_replace("} - {","",$odometer2);
					$odometer2=str_replace("}","",$odometer2);
					$odometer2=str_replace(" {","",$odometer2);
					
					$odometer3="".substr($odometer1,$posb);	
					$odometer3=str_replace("} - {","",$odometer3);
					$odometer3=str_replace("}","",$odometer3);
					$odometer3=str_replace(" {","",$odometer3);
				}
				$odometer1=str_replace(trim($odometer3),"",$odometer1);
				$odometer1=str_replace(trim($odometer2),"",$odometer1);
				$odometer1=str_replace("} - {","",$odometer1);
				$odometer1=str_replace("}","",$odometer1);
				$odometer1=str_replace(" {","",$odometer1);
				
			}
			
			$pos1=strpos($line,"[33]");
			$pos2=strpos($line,"[34]");
			if($pos2 > 0)
			{
				$temp=substr($line,$pos1,($pos2 - $pos1));
				$temp=trim(str_replace("[33]","",$temp));
				
				$posx=strpos($temp,",");
				
				$lat=trim(substr($temp,0,$posx));
				$long=trim(substr($temp,$posx));
				
				$lat=str_replace(",","",$lat);
				$long=str_replace(",","",$long);
			}
			
			$pos1=strpos($line,"[34]");
			//$pos2=strpos($line,"[34]");
			if($pos1 > 0)
			{
				$local=substr($line,$pos1);		//rest of the string...
				$local=str_replace("[34]","",$local);
			}
			
			
			
			
			$driver_id=0;		// [".$driver_id."]
			$truck_id=0;
			$trailer_id=0;
						
			if(trim($truck_name)=="(skipped)" || trim($truck_name)=="0" || trim($truck_name)=="n/a")			$truck_name="";
			if(trim($truck_name)!="")	
			{
     			$sql2 = "select id from trucks where deleted = 0 and (name_truck = '".sql_friendly(trim($truck_name)) ."' or name_truck = '".sql_friendly(trim($truck_name)) ." (Team Rate)') order by name_truck asc,id asc";
          		$data2 = simple_query($sql2);
          		if($row2 = mysqli_fetch_array($data2))
          		{
          			$truck_id=$row2['id'];                                 			
          		}
     		}
     		
     		if(trim($trailer_name)=="(skipped)" || trim($trailer_name)=="0" || trim($trailer_name)=="n/a")		$trailer_name="";
     		if(trim($trailer_name)!="")
     		{
     			$sql2 = "select id from trailers where deleted = 0 and (trailer_name = '".sql_friendly(trim($trailer_name)) ."' or nick_name = '".sql_friendly(trim($trailer_name)) ."') order by trailer_name asc,id asc";
     			$data2 = simple_query($sql2);
     			if($row2 = mysqli_fetch_array($data2))
     			{
     				$trailer_id=$row2['id'];                                 			
     			}
     		}
			
			
			
     		//$is_due=0;		if(trim($due)=="Yes")		$is_due=1;
     		//$is_urgent=0;	if(trim($urgent)=="Yes")		$is_urgent=1;
               $maint_mode=58;	$equip_id=$truck_id;
               if($trailer_id > 0 && trim($trailer_name)!="")	
               {	//58=truck, 59=trailer...
               	$maint_mode=59;	$equip_id=$trailer_id;
               }
               if($equip_id > 0)
               {
     			$sqlu="
     				update ".mrr_find_log_database_name()."pn_request_forms set 
     					truck_id='".sql_friendly($truck_id) ."',
     					trailer_id='".sql_friendly($trailer_id) ."',
     					driver_name='".sql_friendly(trim($driver_name)) ."',
     					driver_id='".sql_friendly($driver_id) ."' 
     				where id='".sql_friendly($request_id) ."'";
          		//simple_query($sqlu);         		
                    		
                    //--Trailer ".trim($trailer_name)."
          		//$new_req_id=mrr_auto_create_maint_request($maint_mode,$equip_id,trim($desc)."  --".trim($driver_name),$is_urgent,$odometer1,trim($date),trim($local));	
          		if($new_req_id > 0)
          		{
          			$sqlu="update ".mrr_find_log_database_name()."pn_request_forms set processed='1',request_id='".sql_friendly($new_req_id)."' where id='".sql_friendly($request_id)."'";
          			//simple_query($sqlu);		
          		}
     		}
     		
     		//".trim($urgent)."
     		//".trim($due)."
     		
     		$rep.="
     			<tr style='background-color:#".($cntr % 2==0 ? "eeeeee" : "dddddd").";'>
     				<td valign='top'>".($cntr + 1)." (".$request_id.")</td>
     				<td valign='top'>".trim($driver_name)."</td>
     				<td valign='top'>".trim($truck_name)." [".$truck_id."]</td>
     				<td valign='top'>".trim($trailer_name)." [".$trailer_id."]</td>
     				<td valign='top'>Flag 3:".$flag_3."<br>Flag 4:".$flag_4."</td>
     				<td valign='top'>Truck Cat ".$tcat_id."<br>".$tcat_name."<br>Trailer Cat ".$cat_id."<br>".$cat_name."</td>
     				<td valign='top'>".trim($date)."<br>".$received."</td>
     				<td valign='top'>".trim($desc)."</td>
     				<td valign='top'>Long ".$long.", Lat ".$lat."<br>".trim($local)."</td>
     				<td valign='top'>Flag 14:".$flag_14."<br>Flag 15:".$flag_15."</td>
     				<td valign='top'>Flag 22:".$flag_22."<br>Flag 27:".$flag_27."</td>
     				<td valign='top'>Fuel: ".$fuel."<br>".trim($odometer1)."<br>".trim($odometer2)."<br>".trim($odometer3)."</td>
     			</tr>
     		";				
     		$rep.="<tr style='background-color:#".($cntr % 2==0 ? "eeeeee" : "dddddd").";'><td valign='top' colspan='12'>".trim($form)."</td></tr>";
     		$cntr++;
		}		
	}	
	$rep.="
		<tr>
			<td valign='top' colspan='12'><b>".$cntr." Requests found to process.</b></td>
		</tr>
	</table><br>";
		
	return $rep;
}
?>