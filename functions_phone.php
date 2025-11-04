<?php
//functions for the phone interactive system (Twilio) for drivers to call in and auto-process load/dispatch/stop issues on there own.
use Twilio\Rest\Client;

function mrr_get_driver_info_array($driver_id=0) 
{
	$adder="";
	if($driver_id > 0)		$adder=" and drivers.id='".sql_friendly($driver_id)."'";
	
	$sql = "
		select drivers.id,
			drivers.name_driver_first,
			drivers.name_driver_last,
			drivers.phone_cell,
			drivers.phone_home,
			drivers.phone_other,
			drivers.attached_truck_id,
			drivers.attached_trailer_id,
			trucks.peoplenet_tracking,
			trucks.name_truck,
			trailers.trailer_name
		
		from drivers
			left join trucks on trucks.id=drivers.attached_truck_id
			left join trailers on trailers.id=drivers.attached_trailer_id
		where drivers.deleted = 0
			and drivers.active > 0
			".$adder."
		order by drivers.name_driver_last asc,
			drivers.name_driver_first asc,
			drivers.id asc
	";
	$data_drivers = simple_query($sql);
	return $data_drivers;
}

function mrr_get_driver_truck_location($truck_id)
{
	$sql = "
		select *		
		from truck_tracking
		where truck_id='".sql_friendly($truck_id)."'
		order by linedate_added desc
	";
	$data = simple_query($sql);
	return $data;	
}

function mrr_send_email_from_phone_response($subject,$message)
{
	global $defaultsarray;
	$from=$defaultsarray['company_email_address'];
	$fromname=$defaultsarray['company_name'];
		
	$monitor_email=$defaultsarray['peoplenet_hot_msg_cc'];	
	
	$monitor_email=$defaultsarray['special_email_monitor'];
	
	mrr_trucking_sendMail_PN($monitor_email,"Dispatch",$from,$fromname,$subject,$message,1,1);	
}

function send_twilio_messages($phone_number, $message) 
{	
	$message=trim($message);
	$pre = 'Conard Trucking: ';
	$post = "\n\nReply STOP to unsubscribe.";
	$message = $pre.$message.$post;
	
	$res['result']=0;
    $res['report']="";
    $res['length']=strlen($message);
     
    if($phone_number == '' || $message=="") 		return $res;
	
    //include_once("twilio/Services/Twilio.php");
    
	
    // Set our AccountSid and AuthToken     
    //$AccountSid = "AC684a891a4dc7476487da40cb6ae56c3f";
	//$AuthToken = "7dda51ae17f6a60c9a24130cf1aa2dd7";
	$account_sid = "ACad25c862951f15bd8dd11ca7e14aad5a";
	$auth_token = "dd83d49fd9e81b72b80f7d8bec1767dc";

    // Instantiate a new Twilio Rest Client
    //$client = new Services_Twilio($AccountSid, $AuthToken);
    //$from= '1-615-213-2270';
    //$from= '1-615-685-4201';
    //$from="1-615-209-9029";
	$twilio_number = '6292190125';
	 
	$client = new Client($account_sid, $auth_token);

	 
	 // make an associative array of server admins
     
     $phone_array = explode(",", $phone_number);
     
     foreach($phone_array as $pnumber) 
     {
     	if(trim($pnumber)!="")
     	{
     		$res['report'].="<br>To ".$pnumber." Chars ".strlen($message).": ".$message."";	//From ".$from." 
     		if(strlen($message) <= 140 || true)	
     		{
     			//$client->account->sms_messages->create($from, $pnumber, $message);	    //"1".	
     			$client->messages->create(
					// Where to send a text message (your cell phone?)
					$pnumber,
					array(
						'from' => $twilio_number,
						'body' => $message
					)
				);
				$res['result']++;	
     		}
     		else
     		{
     			//send more than one text if necessary...
     			$start=0;
     			$chars=140;
     			while($start < strlen($message) && strlen($message) > 0 && $chars > 0)
     			{
     				$fragment=substr($message,$start,$chars);
     				
     				$client->account->sms_messages->create($from, $pnumber, $fragment);	    //"1".	
     				
     				sleep(1);	//delay, to help the seperated messages stay in order.
     				
     				$start+=$chars;
     			}
     			$res['result']++;	
     		}
     	}
     }
     
     return $res;
	 
}

function mrr_add_twilio_call_log($phone,$cmd,$txtcode,$response,$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$city,$state,$local,$lat,$long,$speed,$head,$subject="",$message="")
{
	$sql = "
		insert into ".mrr_find_log_database_name()."twilio_call_log
			(id,
			linedate_added,
			deleted,
			phone_from,
			cmd,
			text_code,
			response,
			driver_id,
			truck_id,				
			trailer_id,
			load_id,	
			disp_id,	
			stop_id,	
			customer_id,
			city,
			state,
			location,
			latitude,
			longitude,
			truck_speed,
			truck_heading,
			subject,
			message)
		values 
			(NULL,
			NOW(),
			0,
			'".sql_friendly($phone)."',
			'".sql_friendly($cmd)."',
			'".sql_friendly($txtcode)."',
			'".sql_friendly($response)."',
			'".sql_friendly($driver_id)."',
			'".sql_friendly($truck_id)."',
			'".sql_friendly($trailer_id)."',
			'".sql_friendly($load_id)."',
			'".sql_friendly($disp_id)."',
			'".sql_friendly($stop_id)."',
			'".sql_friendly($customer_id)."',
			'".sql_friendly($city)."',
			'".sql_friendly($state)."',
			'".sql_friendly($local)."',
			'".sql_friendly($lat)."',
			'".sql_friendly($long)."',
			'".sql_friendly($speed)."',
			'".sql_friendly($head)."',
			'".sql_friendly($subject)."',
			'".sql_friendly($message)."')
	";
	simple_query($sql);				
}

function mrr_get_driver_current_load_info_for_phone($driver_id,$truck_id=0)
{
	$res['load_id']=0;
	$res['disp_id']=0;
	$res['stop_id']=0;
	$res['start_trailer_id']=0;
	$res['end_trailer_id']=0;
	$res['customer_id']=0;
	
	$adder=" and (trucks_log.driver_id='".sql_friendly($driver_id)."' or trucks_log.driver2_id='".sql_friendly($driver_id)."')";
	if($truck_id > 0)	$adder.=" and trucks_log.truck_id='".sql_friendly($truck_id)."'";
	
	$sql = "
		select trucks_log.id,
			trucks_log.customer_id,
			trucks_log.load_handler_id		
		from trucks_log
			left join load_handler on load_handler.id=trucks_log.load_handler_id
		where trucks_log.deleted=0
			".$adder."
			and trucks_log.dispatch_completed=0
			and load_handler.deleted=0
		order by trucks_log.linedate_pickup_eta asc
	";
	$data = simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$res['load_id']=$row['load_handler_id'];
		$res['disp_id']=$row['id'];
		$res['customer_id']=$row['customer_id'];
		
		//now get the stop...next not yet completed...
		$sql2 = "
     		select *		
     		from load_handler_stops
     		where load_handler_stops.deleted=0
     			and load_handler_stops.trucks_log_id='".sql_friendly($row['id'])."'
     			and load_handler_stops.load_handler_id='".sql_friendly($row['load_handler_id'])."'
     			and (load_handler_stops.linedate_completed is NULL or load_handler_stops.linedate_completed < '2014-01-01 00:00:00')
     		order by load_handler_stops.linedate_pickup_eta desc
     	";
     	$data2 = simple_query($sql2);
     	while($row2=mysqli_fetch_array($data2))
     	{
     		//if(isset($row2['linedate_completed']) &&  $row2['linedate_completed'] > '2014-01-01 00:00:00')
     		//{
     			$res['stop_id']=$row2['id'];
				$res['start_trailer_id']=$row2['start_trailer_id'];
				$res['end_trailer_id']=$row2['end_trailer_id'];	
     		//}
     	}		
	}	
	
	if($res['load_id']==0 && $res['disp_id']==0)
	{
		//may fill later if Dale wants to use this as a backup... Preplanned loads.
	}	
	
	return $res;	
}

function mrr_stop_dispatch_completer_for_phone($stop_id,$disp_id,$load_id,$customer_id=0,$driver_id=0,$start_trailer_id=0,$end_trailer_id=0,$moder=0)
{
	global $datasource;

	$operator_message="";
	
	$adder="";
	
	//drop/switch trailer if needed
	$notes="";
	$dedicated_trailer=0;
	$drop_mode=0;
	
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
     
         
     if($moder==1)
     {	//only need to set the arrival time, nothing else... 
     	$sql2 = "
     		update load_handler_stops set
     			linedate_arrival=NOW(),
     			geofencing_arriving_sent='1',
				linedate_geofencing_arriving=NOW(),
				geofencing_arrived_sent='1',
				linedate_geofencing_arrived=NOW()
     		where id='".sql_friendly($stop_id)."'
     	";
     	simple_query($sql2); 
     	
     	$operator_message.="Updated Stop ".$stop_id." Arrival";
     	return $operator_message; 
     }
     else
     {	//still update the stop arrival time, but that is it... and only if not already set.
     	$sql2 = "
     		update load_handler_stops set
     			linedate_arrival=NOW(),
     			geofencing_arriving_sent='1',
				linedate_geofencing_arriving=NOW(),
				geofencing_arrived_sent='1',
				linedate_geofencing_arrived=NOW()
     		where id='".sql_friendly($stop_id)."'
     			and linedate_arrival<='2014-01-01 00:00:00'
     	";
     	simple_query($sql2); 	
     }
     
     if($moder!=1 && $arrv_time=="0000-00-00 00:00:00")
     {	//set arrival info if not already set...
     	$sql2 = "
     		update load_handler_stops set
     			linedate_arrival=NOW(),
     			geofencing_arriving_sent='1',
				linedate_geofencing_arriving=NOW(),
				geofencing_arrived_sent='1',
				linedate_geofencing_arrived=NOW()
     		where id='".sql_friendly($stop_id)."'
     	";
     	simple_query($sql2); 	
     }    
     	
	if($start_trailer_id > 0 && $end_trailer_id > 0)
	{	//switch trailer
		$tname1="";
		$tname2="";
		
		$sql = "
     			select trailer_name,dedicated_trailer from trailers where id='".sql_friendly($start_trailer_id)."'
     		";
     	$data = simple_query($sql);
     	if($row = mysqli_fetch_array($data))
		{
			$tname1=$row['trailer_name'];	
			$dedicated_trailer=$row['dedicated_trailer'];
		}
		
		$sql = "
     			select trailer_name,dedicated_trailer from trailers where id='".sql_friendly($end_trailer_id)."'
     	";
     	$data = simple_query($sql);
     	if($row = mysqli_fetch_array($data))
		{
			$tname2=$row['trailer_name'];	
			//$dedicated_trailer=$row['dedicated_trailer'];
		}
		
		$notes="Switched Trailer ".$tname1." to  Trailer ".$tname2." from phone system.";
		$drop_mode=3;
		$adder=",end_trailer_id='".sql_friendly($end_trailer_id)."'";
	}
	elseif($start_trailer_id > 0 && $end_trailer_id == 0)
	{	//drop trailer
		$sql = "
     			select trailer_name,dedicated_trailer from trailers where id='".sql_friendly($start_trailer_id)."'
     		";
     	$data = simple_query($sql);
     	if($row = mysqli_fetch_array($data))
		{
			$notes="Dropped Trailer ".$row['trailer_name']." from phone system.";
			$dedicated_trailer=$row['dedicated_trailer'];
		}
		$drop_mode=2;
		$adder=",end_trailer_id=0";		
	}
	
	//autograde this stop 
     $calc_stop_grade=$row1['stop_grade_id'];
     $calc_stop_reason="";
	$grade_adder="";
	$grade_msg="";
	
	if($calc_stop_grade==0)
	{
		$row1['mrr_time_diff']=(int) $row1['mrr_time_diff'];
		
		if($row1['mrr_time_diff'] >= 0 && $row1['mrr_time_diff'] <= 30)		{	$calc_stop_grade=5;		$calc_stop_reason="Auto-Graded: 0-30 minutes early.";		}	//On Time
     	elseif($row1['mrr_time_diff'] > 30 && $row1['mrr_time_diff'] >= 120)	{	$calc_stop_grade=7;		$calc_stop_reason="Auto-Graded: 30-120 minutes early.";	}	//Early
     	elseif($row1['mrr_time_diff'] > 120)							{	$calc_stop_grade=8;		$calc_stop_reason="Auto-Graded: more than 2 hrs early.";	}	//Very Early	
     	elseif($row1['mrr_time_diff'] < 0 && $row1['mrr_time_diff'] >=-60)	{	$calc_stop_grade=4;		$calc_stop_reason="Auto-Graded: 0-60 minutes late.";		}	//Late
     	elseif($row1['mrr_time_diff'] < -60 && $row1['mrr_time_diff'] >=-180)	{	$calc_stop_grade=3;		$calc_stop_reason="Auto-Graded: 1-3 hours late.";			}	//Very Late
     	elseif($row1['mrr_time_diff'] < -180 && $row1['mrr_time_diff'] >=-600){	$calc_stop_grade=2;		$calc_stop_reason="Auto-Graded: 3-10 hours late.";		}	//Past Due
     	elseif($row1['mrr_time_diff'] < -600)							{	$calc_stop_grade=1;		$calc_stop_reason="Auto-Graded: more than 10 hours late.";	}	//Epic Fail
		else														{	$calc_stop_grade=0;		$calc_stop_reason="Auto-Graded: Unknown ".$row1['mrr_time_diff']." minutes.";	}	//error
		//turned off the auto_grading for James...June 2015...using the newer Admin Load Grading page...
		/*
		$grade_adder="
			stop_grade_id = '".sql_friendly($calc_stop_grade)."',
     		stop_grade_note = '".sql_friendly($calc_stop_reason)."',
     	";	
     	*/
     	//$grade_msg=" and Graded ".$calc_stop_grade." with ".$calc_stop_reason."";
	}
	
	//update the current stop completion.	
	$sql2 = "
     	update load_handler_stops set
     		".$grade_adder."
     		geofencing_departed_sent='1',
			linedate_geofencing_departed=NOW(),
     		linedate_completed=NOW()
     		".$adder."     		
     	where id='".sql_friendly($stop_id)."'     		
     ";
     //and trucks_log_id='".sql_friendly($disp_id)."'
     //and load_handler_id='".sql_friendly($load_id)."'
     simple_query($sql2);
     
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
     	simple_query($sql2);	
     	
     	$operator_message.=" Dispatch Complete.";
     	
     	//create next dispatch here...
		//if($cur_driver1==371 || $cur_driver2==371)
		//{
			$next_disp_text="";
			$next_disp_text=mrr_find_and_create_next_load_dispatch($disp_id,$cur_driver1,$cur_driver2,$cur_truck,$cur_trailer,$cur_cust,$cur_date,$stop_name,$stop_addr1,$stop_addr2,$stop_city,$stop_state,$stop_zip,$stop_lat,$stop_long);
			$operator_message.=" ".$next_disp_text."";
		//}     	
     }
     
     update_origin_dest($load_id);
     
     $operator_message.=" Updated Stop ".$stop_id."".$grade_msg."";
	
     
     while($row=mysqli_fetch_array($data))
     {	//change the trailer for the rest of the dispatches still active
     	    	
     	if($start_trailer_id > 0 && $end_trailer_id > 0)
     	{
     		$sql2 = "
          		update load_handler_stops set
          			start_trailer_id='".sql_friendly($end_trailer_id)."',
          			end_trailer_id='".sql_friendly($end_trailer_id)."'
          		where id='".sql_friendly($row['id'])."'
          			and trucks_log_id='".sql_friendly($disp_id)."'
          			and load_handler_id='".sql_friendly($load_id)."'
          			and start_trailer_id='".sql_friendly($start_trailer_id)."'
     		";
     		simple_query($sql2);
     		
     		$last_date=date("Y-m-d",strtotime($row['linedate_pickup_eta']));
     		
     		//$operator_message.=" Updated Trailer ".$start_trailer_id." to ".$end_trailer_id.".";
     	}
     }
          
     //dettach trailer from driver and these dispatches...and use the new one from there on....or at least as long as the starting trailer was going to be used.
     if($start_trailer_id > 0 && $end_trailer_id > 0 && $driver_id>0)
     {
     	//update driver attachment
     	$sql2 = "
     		update drivers set
     			attached_trailer_id='".sql_friendly($end_trailer_id)."'
     		where id='".sql_friendly($driver_id)."'
    	 	";
     	simple_query($sql2);
     	
          
          //if($last_date!="0000-00-00 00:00:00")
          //{
          	//update all future dispatch stops for this load to use the new trailer instead of the old one.
          	
          	$sql2 = "
               	select *		
               	from trucks_log
               	where deleted=0
               		and id!='".sql_friendly($disp_id)."'
               		and load_handler_id='".sql_friendly($load_id)."'
               		and dispatch_completed=0
               		and trailer_id='".sql_friendly($start_trailer_id)."'
               	order by linedate_pickup_eta asc
         		";
          	$data2 = simple_query($sql2);
          	while($row2=mysqli_fetch_array($data2))
          	{
          		$disp2=$row2['id'];
          		
          		//update the stops for this dispatch
          		$sql3 = "
               		update load_handler_stops set
               			start_trailer_id='".sql_friendly($end_trailer_id)."',
               			end_trailer_id='".sql_friendly($end_trailer_id)."'
               		where load_handler_id='".sql_friendly($load_id)."'
               			and start_trailer_id='".sql_friendly($start_trailer_id)."'
               			and trucks_log_id = '".sql_friendly($disp2)."'
               			and linedate_pickup_eta > '".$last_date."'          			
               	";
               	simple_query($sql3);
               	
               	//update the dispatches that follow...if any
               	$sql3 = "
               		update trucks_log set
               			trailer_id='".sql_friendly($end_trailer_id)."'
               		where id = '".sql_friendly($disp2)."'  			
               	";
               	simple_query($sql3);
               	
          	}
          	
          	
               //update the current dispatch
               $sql3 = "
               	update trucks_log set
               		trailer_id='".sql_friendly($end_trailer_id)."'
               	where id = '".sql_friendly($disp_id)."'  			
               ";
               simple_query($sql3);
          //}	 
          
          //$operator_message.=" Updated Stop Trailers.";     	
          
          //un-drop this trailer
          $sql = "
			update trailers_dropped set 
				drop_completed = 1, linedate_completed=NOW()
			where trailer_id = '".sql_friendly($end_trailer_id)."'	
				and deleted = 0
				and drop_completed=0		
		";
		simple_query($sql);
     }  
     
     if($start_trailer_id > 0)
     {	//drop the starting trailer_id
		
		if($end_trailer_id == 0)
		{	//dropped without switching, so driver has no trailer now.
			$sql2 = "
     			update drivers set
     				attached_trailer_id='0'
     			where id='".sql_friendly($driver_id)."'
    	 		";
     		simple_query($sql2);
		}
		
		/*
		MRR Drop Mode Key
		1=Trailer Dropped from this form
		2=Trailer Dropped from Manage Load or Load Board form (Load Stops Ajax)...or from phone call
		3=Trailer Switched on Manage Load or Load Board form (Load Stops Ajax)...or from phone call
		*/
		
		$sql = "
			insert into trailers_dropped
				(linedate_added,
				created_by_user_id,
				mrr_drop_mode)
				
			values (now(),
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($drop_mode)."')
		";
		simple_query($sql);
		$drop_id = mysqli_insert_id($datasource);
		
		
		//complete all prior drops for this trailer so that trailer does not get dropped in more than one location at the same time...
		$sql = "
			update trailers_dropped set 
				drop_completed = 1, linedate_completed=NOW()
			where id != '".sql_friendly($drop_id)."'
				and trailer_id = '".sql_friendly($start_trailer_id)."'	
				and deleted = 0	
				and drop_completed=0	
		";
		simple_query($sql);
				
		$sql = "
			update trailers_dropped set
			
				linedate = '".date("Y-m-d")."',
				trailer_id = '".sql_friendly($start_trailer_id)."',
				customer_id = '".sql_friendly($customer_id)."',
				location_city = '".sql_friendly($row1['shipper_city'])."',
				location_state = '".sql_friendly($row1['shipper_state'])."',
				location_zip = '".sql_friendly($row1['shipper_zip'])."',
				notes = '".sql_friendly($notes)."',
				drop_completed = '0', 
				linedate_completed='0000-00-00 00:00:00',
				dedicated_trailer = '0'
				
			where id = '".sql_friendly($drop_id)."'
		";
		simple_query($sql);
		
		//$operator_message.=" Dropped Trailer ".$start_trailer_id.".";	
			
		//create switch record...
		$trailer1_cost=mrr_get_trailer_cost($start_trailer_id);
		if($trailer1_cost > 0)		$trailer1_cost=mrr_get_option_variable_settings('Trailer Expense');
		
		$trailer2_cost=0;
		if($end_trailer_id > 0)	
		{
			$trailer2_cost=mrr_get_trailer_cost($end_trailer_id);
			if($trailer2_cost > 0)	$trailer2_cost=mrr_get_option_variable_settings('Trailer Expense');
		}
		
		//add trailer switch_note
		$sql2 = "
			insert into trailer_switched
				(id,
				linedate_added,
				linedate,
				dispatch_id,
				stop_id,
				deleted,
				old_trailer_id,
				new_trailer_id,
				old_trailer_cost,
				new_trailer_cost)
			values
				(NULL,
				NOW(),
				NOW(),
				'".sql_friendly($disp_id)."',
				'".sql_friendly($stop_id)."',
				0,
				'".sql_friendly($start_trailer_id)."',
				'".sql_friendly($end_trailer_id)."',
				'".sql_friendly($trailer1_cost)."',
				'".sql_friendly($trailer2_cost)."')
		";
		simple_query($sql2);
		
		//$operator_message.=" Recorded Switch Trailer Log Entry.";
     }  
     
     return $operator_message; 
}
function mrr_get_last_stop_deadhead_alt($driver_id,$disp_id)
{
     $res['miles']=0;
     $res['last_stop']="";
     $res['disp_id']=0;
     $res['sql']="";
     
     // get the driver info
     $sql = "
			select *			
			from drivers
			where id = '".sql_friendly($driver_id)."'
		";
     //$data_driver = simple_query($sql);
     //$row_driver = mysqli_fetch_array($data_driver);
     
     $to_city="";
     $to_state="";
     $to_zip="";
     $date="";
     $from_city="";
     $from_state="";
     $from_zip="";
     
     
     //get first stop for this Dispatch
     $sqlx="
			select * 
			from load_handler_stops
			where trucks_log_id='".sql_friendly($disp_id)."'
			order by linedate_pickup_eta asc 
		";
     $datax=simple_query($sqlx);
     if($rowx=mysqli_fetch_array($datax))
     {
          $to_city=trim($rowx['shipper_city']);
          $to_state=trim($rowx['shipper_state']);
          $to_zip=trim($rowx['shipper_zip']);
          $date = $rowx['linedate_pickup_eta'];  
     }
     
     
     $found_disp=0;
     // get the loads this driver ran
     $sql = "
          select customers.name_company,
               trailers.trailer_name,
               trucks.name_truck,
               trucks_log.id,
               trucks_log.linedate,
               (
                    select load_handler_stops.linedate_completed
                    from load_handler_stops 
                    where load_handler_stops.deleted=0 and load_handler_stops.trucks_log_id=trucks_log.id
                    order by load_handler_stops.linedate_completed desc 
                    limit 1
               ) as pickup_eta,
               (
                    select (CASE WHEN load_handler_stops.linedate_completed IS NULL THEN 0 ELSE 1 END) 
                    from load_handler_stops 
                    where load_handler_stops.deleted=0 and load_handler_stops.trucks_log_id=trucks_log.id
                    order by load_handler_stops.linedate_completed desc 
                    limit 1
               ) as pickup_eta_is_null,
               trucks_log.origin,
               trucks_log.origin_state,
               trucks_log.destination,
               trucks_log.destination_state,
               trucks_log.profit
          
          from trucks_log
               left join customers on trucks_log.customer_id = customers.id
               left join trailers on trailers.id = trucks_log.trailer_id
               left join trucks on trucks.id = trucks_log.truck_id
          where trucks_log.driver_id = '".sql_friendly($driver_id)."'
               and linedate > '".date("Y-m-d", strtotime("-7 day", strtotime($date)))."'
               and trucks_log.deleted<=0
          order by linedate desc, pickup_eta_is_null asc, pickup_eta desc
     ";
     $res['sql']=$sql;
     $data = simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {
          /*
          $stop_dater="".date("m/d/y H:i", strtotime($row['pickup_eta']))."";
          
          if(!isset($row['pickup_eta']) || $row['pickup_eta']=="0000-00-00 00:00:00")    $stop_dater="N/A";
          
          $disphtml .= "
				<tr style='font-size:10px'>
					<td nowrap>$row[id]</td>
					<td nowrap>$row[name_truck]</td>
					<td nowrap>$row[trailer_name]</td>
					<td nowrap>".date("M, j", strtotime($row['linedate']))."</td>
					<td nowrap><span style='color:purple;'>".$stop_dater."</span></td>
					<td nowrap>$row[origin]".($row['origin_state'] != '' ? ', '.$row['origin_state'] : '')."</td>
					<td nowrap>$row[destination]".($row['destination_state'] != '' ? ', '.$row['destination_state'] : '')."</td>
					<td align='right'>$".money_format('', $row['profit'])."</td>
				</tr>
			";
          */
          
          if($found_disp==0 && $row['id']==$disp_id)
          {    //found the current dispatch... mark it to use the next dispatch as the primary stop.
               $found_disp=$disp_id;
          }
          elseif($found_disp == $disp_id && $from_city=="")
          {    //use this first dispatch after the match has been found to find hte last stop... for this dispatch.
               $from_city="";
               $from_state="";
               $from_zip="";
     
               //get last completed stop for this Dispatch...this should be the last stop before the Dispatch ID being searched.
               $sqlx="
                    select * 
                    from load_handler_stops
                    where trucks_log_id='".sql_friendly($row['id'])."'
                         and deleted<=0
                    order by linedate_pickup_eta desc,linedate_completed desc
               ";
               $datax=simple_query($sqlx);
               if($rowx=mysqli_fetch_array($datax))
               {
                    $from_city=trim($rowx['shipper_city']);
                    $from_state=trim($rowx['shipper_state']);
                    $from_zip=trim($rowx['shipper_zip']);
                    //$date = $rowx['linedate_pickup_eta'];
     
                    $res['disp_id']=$row['id'];
     
                    $res['last_stop']="<a href='add_entry_truck.php?id=".$row['id']."' target='_blank'>".$from_city.",".$from_state."</a>";	//".$from_zip."
               }
               
          }
          
     }
     
     $run_mileage=0;
     if( $pcm = new COM("PCMServer.PCMServer") )		$run_mileage=1;
     //$run_mileage=1;
     
     $dead_head_miles=0;
     $prev_city_state="".trim($from_city.", ".$from_state)."";		//".$from_zip."
     $city_state="".trim($to_city.", ".$to_state)."";				//".$to_zip."
     
     $time=time();
     
     if($prev_city_state != $city_state && $prev_city_state!="" && $city_state!="" && $run_mileage==1)
     {
          try {
               $dead_head_miles = $pcm->CalcDistance3($prev_city_state, $city_state, 0, $time) / 10;
          } catch (Exception $e) {
               $dead_head_miles = 0;
          }
          
          
     }
     
     $res['miles']=$dead_head_miles;
     
     return $res;
}
function mrr_get_last_stop_deadhead($disp_id)
{	
	$res['miles']=0;
	$res['last_stop']="";
     $res['disp_id']=0;
     $res['sql']="";
	
	if($disp_id==0)		return $res;		//don't bother...
	
	$from_city="";
	$from_state="";
	$from_zip="";	
	
	$to_city="";
	$to_state="";
	$to_zip="";	
	
	$sql="
		select * 
		from trucks_log 
		where id='".sql_friendly($disp_id)."'
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{		
		$driver1=$row['driver_id'];
		$driver2=$row['driver2_id'];
		$date=$row['linedate_pickup_eta'];
          
          $date2=$row['linedate_pickup_eta'];
          
          $comp_date=$row['linedate_pickup_eta'];
		
		//get first stop for this Dispatch
		$sqlx="
			select * 
			from load_handler_stops
			where trucks_log_id='".sql_friendly($disp_id)."'
			order by linedate_pickup_eta asc 
		";
		$datax=simple_query($sqlx);
		if($rowx=mysqli_fetch_array($datax))
		{
			$to_city=trim($rowx['shipper_city']);
			$to_state=trim($rowx['shipper_state']);
			$to_zip=trim($rowx['shipper_zip']);
			
               if(isset($rowx['linedate_completed']) && strtotime($rowx['linedate_completed']) > 0) 
               {
                    $date2 = $rowx['linedate_completed'];         //linedate_pickup_eta
               }
		}
						
		//find last dispatch
		$sql2="
			select trucks_log.*,
			     (
					select load_handler_stops.linedate_completed 
					from load_handler_stops 
					where load_handler_stops.deleted=0 and load_handler_stops.trucks_log_id=trucks_log.id
					   and load_handler_stops.linedate_completed<'".sql_friendly($date2)."'
					   and load_handler_stops.linedate_completed is not null
					   and load_handler_stops.linedate_completed!='0000-00-00 00:00:00'
					order by load_handler_stops.linedate_completed desc 
					limit 1
				) as pickup_eta,
				(
					select (CASE WHEN load_handler_stops.linedate_completed IS NULL THEN 0 ELSE 1 END) 
					from load_handler_stops 
					where load_handler_stops.deleted=0 and load_handler_stops.trucks_log_id=trucks_log.id
					   and load_handler_stops.linedate_completed<'".sql_friendly($date2)."'
					   and load_handler_stops.linedate_completed is not null
					   and load_handler_stops.linedate_completed!='0000-00-00 00:00:00'
					order by load_handler_stops.linedate_completed desc 
					limit 1
				) as pickup_eta_is_null
			from trucks_log 
			where trucks_log.deleted<=0
			     and trucks_log.linedate_pickup_eta>='2020-10-01 00:00:00' and trucks_log.linedate_pickup_eta<='".sql_friendly($date2)."'
				and (
					(trucks_log.driver_id='".sql_friendly($driver1)."' and trucks_log.driver2_id='".sql_friendly($driver2)."')
						or
					(trucks_log.driver_id='".sql_friendly($driver2)."' and trucks_log.driver2_id='".sql_friendly($driver1)."')
					)
				and (
					select load_handler_stops.linedate_completed 
					from load_handler_stops 
					where load_handler_stops.deleted=0 and load_handler_stops.trucks_log_id=trucks_log.id
					   and load_handler_stops.linedate_completed<'".sql_friendly($date2)."'
					   and load_handler_stops.linedate_completed is not null
					   and load_handler_stops.linedate_completed!='0000-00-00 00:00:00'
					order by load_handler_stops.linedate_completed desc 
					limit 1
				) is not null
				and trucks_log.dispatch_completed > 0
				and trucks_log.id <> '".sql_friendly($disp_id)."'
			order by trucks_log.linedate_pickup_eta desc, pickup_eta_is_null asc, pickup_eta desc
		";        //trucks_log.linedate_pickup_eta
          $res['sql']=$sql2;
		$data2=simple_query($sql2);
		if($row2=mysqli_fetch_array($data2))
		{
			$disp2_id=$row2['id'];
			$load=$row2['load_handler_id'];
               
               $res['disp_id']=$disp2_id;
               
               //$comp_date=$row2['pickup_eta'];
			
			//get last stop from that dispatch
			$sqlx="
				select * 
				from load_handler_stops
				where trucks_log_id='".sql_friendly($disp2_id)."'
				order by linedate_pickup_eta desc 
			";
			$datax=simple_query($sqlx);
			if($rowx=mysqli_fetch_array($datax))
			{
				$from_city=trim($rowx['shipper_city']);
				$from_state=trim($rowx['shipper_state']);
				$from_zip=trim($rowx['shipper_zip']);
                    
                    //$comp_date=$rowx['linedate_pickup_eta'];
				
				$res['last_stop']="<a href='add_entry_truck.php?load_id=".$load."&id=".$disp2_id."' target='_blank'>".$from_city.",".$from_state."</a>";	//".$from_zip."
			}
			
			//check for a later stop that is for the same driver that is today...and replace it if it is found.  But only use the current date of the load.
               $sqlx2="
				select load_handler_stops.* 
				from load_handler_stops
				    left join trucks_log on trucks_log.id=load_handler_stops.trucks_log_id
				    left join load_handler on load_handler.id=load_handler_stops.load_handler_id
				where load_handler_stops.trucks_log_id!='".sql_friendly($disp2_id)."'
				     and load_handler_stops.linedate_completed>='".date("Y-m-d",strtotime($comp_date))." 00:00:00'
				     and load_handler_stops.linedate_completed<='".date("Y-m-d",strtotime($comp_date))." 23:59:59'
				     and load_handler_stops.linedate_completed is not null
				     and load_handler_stops.linedate_completed!='0000-00-00 00:00:00'
				     and load_handler.deleted<=0
				     and trucks_log.deleted<=0
				     and trucks_log.driver_id='".sql_friendly($driver1)."'
				     and load_handler_stops.deleted<=0
				order by load_handler_stops.linedate_completed desc 
			";
               $datax2=simple_query($sqlx2);
               if($rowx2=mysqli_fetch_array($datax2))
               {
                    $res['sql']=$sqlx2;
                    
                    $from_city=trim($rowx2['shipper_city']);
                    $from_state=trim($rowx2['shipper_state']);
                    $from_zip=trim($rowx2['shipper_zip']);
                    
                    $load=$rowx2['load_handler_id'];
                    $disp2_id=$rowx2['trucks_log_id'];   
     
                    $res['disp_id']=$disp2_id;
                    
                    $res['last_stop']="<a href='add_entry_truck.php?load_id=".$load."&id=".$disp2_id."' target='_blank'>".$from_city.",".$from_state."</a>";	//".$from_zip."
               }
		}
			
		$run_mileage=0;		
		if( $pcm = new COM("PCMServer.PCMServer") )		$run_mileage=1;
		//$run_mileage=1;
		
		$dead_head_miles=0;
		$prev_city_state="".trim($from_city.", ".$from_state)."";		//".$from_zip."
		$city_state="".trim($to_city.", ".$to_state)."";				//".$to_zip."
		
		$time=time();
		
		if($prev_city_state != $city_state && $prev_city_state!="" && $city_state!="" && $run_mileage==1) 
		{		
			try {
				$dead_head_miles = $pcm->CalcDistance3($prev_city_state, $city_state, 0, $time) / 10;
			} catch (Exception $e) {
				$dead_head_miles = 0;
			}
			
			
		}
		
		$res['miles']=$dead_head_miles;
	}
	
	return $res;
	
}
function mrr_find_and_create_next_load_dispatch($disp_id=0,$driver1=0,$driver2=0,$truck=0,$trailer=0,$cust=0,$date="0000-00-00 00:00:00",$stop_name="",$addr1="",$addr2="",$city="",$state="",$zip="",$lat="",$long="")
{	
	global $datasource;

	//DATE should already be in yyyy-mm-dd hh:ii:ss
	$report="";
	
	return $report;	//deactivated the function....Sep. 2015 MRR
		
	if($trailer==0)		$trailer=187;		//use the "Unknown" Trailer...
	//global $defaultsarray;
	
	//first, see if there is already a dispatch for this driver (after the date and disp_id that is).
	$sql="
		select * 
		from trucks_log 
		where deleted=0
			and dispatch_completed=0
			and (driver_id='".sql_friendly($driver1)."' or driver2_id='".sql_friendly($driver1)."')
			and linedate_pickup_eta >='".sql_friendly($date)."'
		order by linedate_pickup_eta asc
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$report=" You are already scheduled for Load ".$row['load_handler_id']." Dispatch ".$row['id']." from ".$row['origin']." ".$row['origin_state']." to ".$row['destination']." ".$row['destination_state']." on ".date("l F j g:i A",strtotime($row['linedate_pickup_eta'])).".";
		return $report;		//exit now, no need to go further...already has the dispatch...next call should load this one as the current dispatch...just tell driver what it will be.
	}
	
	
	//if no other dispatches, check for next preplanned load...make next dispatch from settings...calculate deadhead miles if possible.
	$loadid=0;
	$sql="
		select * 
		from load_handler 
		where deleted=0
			and preplan > 0
			and (preplan_driver_id='".sql_friendly($driver1)."' or preplan_driver2_id='".sql_friendly($driver1)."')
			and linedate_pickup_eta >='".date("Y-m-d",strtotime($date))." 00:00:00'
		order by linedate_pickup_eta asc
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$new_cust=$row['customer_id'];
		$loadid=$row['id'];
		
		$report=" You are scheduled for Load ".$loadid." from ".$row['origin_city']." ".$row['origin_state']." to ".$row['dest_city']." ".$row['dest_state']." on ".date("l F j g:i A",strtotime($row['linedate_pickup_eta'])).".";
		
		$city_state=trim("".$city.", ".$state."");	//city and state from last stop...
		$prev_city_state="";
		$stop_cntr=0;
		
		$run_mileage=0;		
		if( $pcm = new COM("PCMServer.PCMServer") )		$run_mileage=1;
		//$run_mileage=1;
		
		$dead_head_miles = 0;
		$loaded_miles = 0;
		
		$first_city="";
		$first_state="";
		$first_date="";
		
		$last_city="";		
		$last_state="";
		$last_date="";
			
		$sqlh="
			select id,
				shipper_city,
				shipper_state,
				linedate_pickup_eta 
			
			from load_handler_stops 
			where deleted=0
				and load_handler_id='".sql_friendly($loadid)."'
			order by linedate_pickup_eta asc
		";
		$datah=simple_query($sqlh);
		if($rowh=mysqli_fetch_array($datah))
		{
			$prev_city_state=trim("".$rowh['shipper_city'].", ".$rowh['shipper_state']."");		//first stop city and state
			$last_city=trim($rowh['shipper_city']);
			$last_state=trim($rowh['shipper_state']);
			$last_date=$rowh['linedate_pickup_eta'];
			
			if($stop_cntr==0)
			{
				if($prev_city_state != $city_state && $prev_city_state!="" && $city_state!="" && $run_mileage==1) 
          		{
          			try {
          				$dead_head_miles = $pcm->CalcDistance3($prev_city_state, $city_state, 0, $time) / 10;
          			} catch (Exception $e) {
          				$dead_head_miles = 0;
          			}
          		}	
          		$city_state=trim("".$rowh['shipper_city'].", ".$rowh['shipper_state']."");		//for next stop....          		
          		$first_city=trim($rowh['shipper_city']);		
				$first_state=trim($rowh['shipper_state']);	
				$first_date=$rowh['linedate_pickup_eta'];	
			}	
			else
			{
				$pcm_miler=0;
				if($prev_city_state != $city_state && $prev_city_state!="" && $city_state!="" && $run_mileage==1) 
          		{
          			try {
          				$pcm_miler= $pcm->CalcDistance3($prev_city_state, $city_state, 0, $time) / 10;
          			} catch (Exception $e) {
          				$pcm_miler=0;
          			}
          		}	
          		$loaded_miles += $pcm_miler;
          		
          		$sqlu = "
					update load_handler_stops set
						pcm_miles = '".$pcm_miler."',
						linedate_updater=NOW()
					
					where id = '".sql_friendly($rowh['id'])."'
				";
				simple_query($sqlu);
          		
          		$city_state=trim("".$rowh['shipper_city'].", ".$rowh['shipper_state']."");		//for next stop....	
			}		
			
			$stop_cntr++;
		}
				
		//default settings used for budget items
     	$mrr_average_mpg=mrr_get_default_variable_setting('average_mpg');
          //$mrr_billable_days_in_month=mrr_get_default_variable_setting('billable_days_in_month');
          $mrr_labor_per_hour=mrr_get_default_variable_setting('labor_per_hour');
          $mrr_labor_per_mile=mrr_get_default_variable_setting('labor_per_mile');
          $mrr_labor_per_mile_team=mrr_get_default_variable_setting('labor_per_mile_team');
          //$mrr_local_driver_workweek_hours=mrr_get_default_variable_setting('local_driver_workweek_hours');
          $mrr_tractor_maint_per_mile=mrr_get_default_variable_setting('tractor_maint_per_mile');
          $mrr_trailer_maint_per_mile=mrr_get_default_variable_setting('trailer_maint_per_mile');
          
          $mrr_truck_accidents_per_mile=mrr_get_default_variable_setting('truck_accidents_per_mile');
     	$mrr_tires_per_mile=mrr_get_default_variable_setting('tires_per_mile');
     	$mrr_mileage_expense_per_mile=mrr_get_default_variable_setting('mileage_expense_per_mile');
     	$mrr_misc_expense_per_mile=mrr_get_default_variable_setting('misc_expense_per_mile');
     	
     	$mrr_trailer_mile_exp_per_mile=mrr_get_default_variable_setting('trailer_mile_exp_per_mile');
     	
     	//$mrr_cargo_insurance=mrr_get_option_variable_settings('Cargo Insurance');
          //$mrr_general_liability=mrr_get_option_variable_settings('General Liability');
          //$mrr_liability_phy_damage=mrr_get_option_variable_settings('Liability/Phy Damage');
          //$mrr_payroll___admin=mrr_get_option_variable_settings('Payroll & Admin');
          //$mrr_rent=mrr_get_option_variable_settings('Rent');
          //$mrr_tractor_lease=mrr_get_option_variable_settings('Tractor Lease');
          //$mrr_trailer_expense=mrr_get_option_variable_settings('Trailer Expense');
          //$mrr_trailer_lease=mrr_get_option_variable_settings('Trailer Lease');
          //$mrr_misc_expenses=mrr_get_option_variable_settings('Misc Expenses');
			
		
		//found the first preplanned load...
		
		$mrr_truck_cost=mrr_get_truck_cost($truck);
		$trent_flag=mrr_get_truck_rental_status($truck);
		$mrr_trailer_cost=mrr_get_trailer_cost($trailer);
		$emp_id=mrr_fetch_set_employer_id($driver1);
		$emp_id2=mrr_fetch_set_employer_id($driver2);
		
		if($mrr_truck_cost==0)		$mrr_truck_cost=mrr_pull_default_truck_cost_if_none();	
		
		$mrr_labor_per_mile=mrr_get_driver_pay_rate($driver1,4);
		$mrr_labor_per_hour=mrr_get_driver_pay_rate($driver1,5);
		$cur_driver_labor_miles2=0;
		$cur_driver_labor_hours2=0;
		
		if($driver2 > 0)
		{
			//$mrr_labor_per_mile=mrr_get_driver_pay_rate($driver1,6);
			//$mrr_labor_per_hour=mrr_get_driver_pay_rate($driver1,7);			
			
			$cur_driver_labor_miles2=mrr_get_driver_pay_rate($driver2,6);		
			$cur_driver_labor_hours2=mrr_get_driver_pay_rate($driver2,7);
		}
				
		$sqli="
			insert into trucks_log
				(id,
				linedate_added,
				linedate,
				linedate_updated,
				linedate_pickup_eta,
				linedate_dropoff_eta,
				truck_id,
				trailer_id,
				driver_id,
				driver2_id,
				customer_id,
				origin,
				origin_state,
				destination,
				destination_state,
				location,
				user_id,
				notes,
				miles,
				miles_deadhead,
				
				load_id,
				load_handler_id,
				dispatch_completed,
				daily_run_otr,
				daily_run_hourly,
				loaded_miles_hourly,
				hours_worked,
				tires_per_mile,
				accidents_per_mile,
				mile_exp_per_mile,
				misc_per_mile,
				employer_id,
				trailer_exp_per_mile,
				manual_miles_flag,
				dropped_trailer,
				valid_trip_pack,
				has_load_flag,				
				trailer_maint_per_mile,
				tractor_maint_per_mile,
				labor_per_mile,
				
				labor_per_hour,				
				avg_mpg,					
				pcm_miles,				
				driver_2_labor_per_mile,
				driver_2_labor_per_hour,							
				daily_cost,
				cost,
				profit,
				otr_daily_cost,				
				deleted)
			values	
				(NULL,
				NOW(),
				'".date("Y-m-d",strtotime($first_date))." 00:00:00',
				NOW(),
				'".date("Y-m-d H:i",strtotime($first_date)).":00',
				'".date("Y-m-d H:i",strtotime($last_date)).":00',
				'".sql_friendly($truck)."',
				'".sql_friendly($trailer)."',
				'".sql_friendly($driver1)."',
				'".sql_friendly($driver2)."',
				'".sql_friendly($new_cust)."',
				'".sql_friendly($first_city)."',
				'".sql_friendly($first_state)."',
				'".sql_friendly($last_city)."',
				'".sql_friendly($last_state)."',
				'',
				'".sql_friendly($_SESSION['user_id'])."',
				'',
				'".sql_friendly($loaded_miles)."',
				'".sql_friendly($dead_head_miles)."',
				
				0,
				'".sql_friendly($loadid)."',
				0,
				'1.00',
				'0.00',
				'0.00',
				'0.00',
				'".sql_friendly(money_strip($mrr_tires_per_mile))."',
				'".sql_friendly(money_strip($mrr_truck_accidents_per_mile))."',
				'".sql_friendly(money_strip($mrr_mileage_expense_per_mile))."',
				'".sql_friendly(money_strip($mrr_misc_expense_per_mile))."',
				'".sql_friendly($emp_id)."',
				'".sql_friendly(money_strip($mrr_trailer_mile_exp_per_mile))."',
				0,
				0,
				0,
				0,
				'".sql_friendly(money_strip($mrr_trailer_maint_per_mile))."',
				'".sql_friendly(money_strip($mrr_tractor_maint_per_mile))."',
				'".sql_friendly(money_strip($mrr_labor_per_mile))."',
				
				'".sql_friendly(money_strip($mrr_labor_per_hour))."',				
				'".sql_friendly($mrr_average_mpg)."',				
				'".sql_friendly($loaded_miles)."',				
				'".sql_friendly(money_strip($cur_driver_labor_miles2))."',
				'".sql_friendly(money_strip($cur_driver_labor_hours2))."',				
				'0.00',
				'0.00',
				'0.00',
				'0.00',				
				0)
		";					//$loaded_miles + $dead_head_miles 
		$new_disp_id=0;
		simple_query($sqli);
		$new_disp_id= mysql_insert_id($datasource);
		
		if($new_disp_id > 0)
		{				
			//update all the stops in this load to be part of this dispatch...
			$sqlu = "
				update load_handler_stops set
					trucks_log_id = '".sql_friendly($new_disp_id)."'
				where trucks_log_id = 0
					and deleted=0
					and load_handler_id= '".sql_friendly($loadid)."'
			";
			simple_query($sqlu);
					
			//update the cost for this dispatch when settigns have been saved.		
			$mrr_cd=0;
          	$mrr_day_cost=mrr_quick_and_easy_daily_cost($new_disp_id,$mrr_cd);		//if MRR_CD==1, array is returned with each part of the daily cost including a total with the Days Run included....
			
			$sqlu = "
				update trucks_log set
					daily_cost = '".sql_friendly($mrr_day_cost)."'
				where id = '".sql_friendly($new_disp_id)."'
			";
			simple_query($sqlu);
						
			update_origin_dest($loadid);
		}
	}
	return $report;
}


function mrr_get_messages_sent_by_truck_phone_only($date_from, $date_to, $truck_id=0, $truck_name="",$limit=0, $archived=0,$mode=0,$load_id=0,$dispatch_id=0)
	{	//messages pulled from packets
		global $new_style_path;
		global $defaultsarray;
		
		$date_range_msg_history=" and truck_tracking_msg_history.linedate_created>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking_msg_history.linedate_created<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
		
		$lim_txt="";
		if($limit>0)	$lim_txt=" limit ".$limit."";
		
		$archiver=" and archived='".sql_friendly($archived)."'";
		
		
		
		$date_range_msg_history3=" and twilio_call_log.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and twilio_call_log.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
		$mrr_adder3="";
		if($load_id > 0)		$mrr_adder3.=" and twilio_call_log.load_id='".sql_friendly($load_id)."'";
		if($dispatch_id > 0)	$mrr_adder3.=" and twilio_call_log.disp_id='".sql_friendly($dispatch_id)."'";
		//if($driver_id > 0)	$mrr_adder3.=" and twilio_call_log.driver_id='".sql_friendly($driver_id)."'";
		
		
		$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
     	//$offset_gmt=$offset_gmt * -1;
				
		//send and stored messages from Peoplenet Interface
		$mcntr=0; 
		$mcntr3=0;
		$tab="";		//
		$tab2="";		//load board version
		
		$tab3="";		//load board  simple version
		
		/*
		
		select truck_tracking_msg_history.id as my_id,
				truck_tracking_msg_history.load_id as my_load,
				truck_tracking_msg_history.dispatch_id as my_disp,
				truck_tracking_msg_history.linedate_added as my_date,
				truck_tracking_msg_history.msg_text as my_msg,	
				
				truck_tracking_msg_history.user_id_read as my_user_id_read,
				truck_tracking_msg_history.linedate_read as my_linedate_read,
				truck_tracking_msg_history.user_id_reply as my_user_id_reply,
				truck_tracking_msg_history.linedate_reply as my_linedate_reply,
				truck_tracking_msg_history.truck_id as my_truck_id,
				truck_tracking_msg_history.truck_name as my_truck_name,
				truck_tracking_msg_history.recipient_name as my_recipient_name,
				truck_tracking_msg_history.linedate_created as my_linedate_created,
				truck_tracking_msg_history.linedate_received as my_linedate_received,
						
				'Sent' as mrr_mode
				
     		from truck_tracking_msg_history
     		where truck_id='".sql_friendly($truck_id) ."'
     			".$date_range_msg_history."
     			".$archiver."
     			     			
     		union all 		
     			
		
		*/
				
		$sql3 = "     		
     		select twilio_call_log.id as my_id,
     			twilio_call_log.load_id as my_load,
     			twilio_call_log.disp_id as my_disp,
     			'0000-00-00 00:00:00' as my_date,
     			twilio_call_log.message as my_msg,	
     			0 as my_user_id_read,
     			'0000-00-00 00:00:00' as my_linedate_read,
     			0 as my_user_id_reply,
     			'0000-00-00 00:00:00' as my_linedate_reply,
     			0 as my_truck_id,
     			twilio_call_log.text_code as my_truck_name,
     			twilio_call_log.stop_id as my_recipient_name,
     			twilio_call_log.linedate_added as my_linedate_created,
     			'0000-00-00 00:00:00' as my_linedate_received,     					
     			'Phoned' as mrr_mode
     			
     		from ".mrr_find_log_database_name()."twilio_call_log
     		where twilio_call_log.truck_id='".sql_friendly($truck_id) ."'
     			and cmd!='' 
     			".$date_range_msg_history3."
     			".$mrr_adder3."	    			
     			
     		order by my_linedate_created desc
     		".$lim_txt."
     	";
		$data3 = simple_query($sql3);
		$mn3=mysqli_num_rows($data3);	
		if($mn3==0)
		{	//try it without the specific dispatch
			$mrr_adder3="";
			if($load_id > 0)		$mrr_adder3.=" and twilio_call_log.load_id='".sql_friendly($load_id)."'";
			//if($dispatch_id > 0)	$mrr_adder3.=" and twilio_call_log.disp_id='".sql_friendly($dispatch_id)."'";
			//if($driver_id > 0)	$mrr_adder3.=" and twilio_call_log.driver_id='".sql_friendly($driver_id)."'";
			
			$sql3 = "     		
          		select twilio_call_log.id as my_id,
          			twilio_call_log.load_id as my_load,
          			twilio_call_log.disp_id as my_disp,
          			'0000-00-00 00:00:00' as my_date,
          			twilio_call_log.message as my_msg,	
          			0 as my_user_id_read,
          			'0000-00-00 00:00:00' as my_linedate_read,
          			0 as my_user_id_reply,
          			'0000-00-00 00:00:00' as my_linedate_reply,
          			0 as my_truck_id,
          			twilio_call_log.text_code as my_truck_name,
          			twilio_call_log.stop_id as my_recipient_name,
          			twilio_call_log.linedate_added as my_linedate_created,
          			'0000-00-00 00:00:00' as my_linedate_received,     					
          			'Phoned' as mrr_mode
          			
          		from ".mrr_find_log_database_name()."twilio_call_log
          		where twilio_call_log.truck_id='".sql_friendly($truck_id) ."'
          			and cmd!='' 
          			".$date_range_msg_history3."
          			".$mrr_adder3."	    			
          			
          		order by my_linedate_created desc
          		".$lim_txt."
          	";
     		$data3 = simple_query($sql3);
     		$mn3=mysqli_num_rows($data3);	
		}	
		$mn3=mysqli_num_rows($data3);	
		if($mn3==0)
		{	//try it again without the specific load
			
			$mrr_adder3=" and twilio_call_log.linedate_added>='".date("Y-m-d",time())." 00:00:00'";
			//if($load_id > 0)		$mrr_adder3.=" and twilio_call_log.load_id='".sql_friendly($load_id)."'";
			//if($dispatch_id > 0)	$mrr_adder3.=" and twilio_call_log.disp_id='".sql_friendly($dispatch_id)."'";
			//if($driver_id > 0)	$mrr_adder3.=" and twilio_call_log.driver_id='".sql_friendly($driver_id)."'";
						
			$sql3 = "     		
          		select twilio_call_log.id as my_id,
          			twilio_call_log.load_id as my_load,
          			twilio_call_log.disp_id as my_disp,
          			'0000-00-00 00:00:00' as my_date,
          			twilio_call_log.message as my_msg,	
          			0 as my_user_id_read,
          			'0000-00-00 00:00:00' as my_linedate_read,
          			0 as my_user_id_reply,
          			'0000-00-00 00:00:00' as my_linedate_reply,
          			0 as my_truck_id,
          			twilio_call_log.text_code as my_truck_name,
          			twilio_call_log.stop_id as my_recipient_name,
          			twilio_call_log.linedate_added as my_linedate_created,
          			'0000-00-00 00:00:00' as my_linedate_received,     					
          			'Phoned' as mrr_mode
          			
          		from ".mrr_find_log_database_name()."twilio_call_log
          		where twilio_call_log.truck_id='".sql_friendly($truck_id) ."'
          			and cmd!='' 
          			and twilio_call_log.load_id > 0
          			".$date_range_msg_history3."
          			".$mrr_adder3."	    			
          			
          		order by my_linedate_created desc
          		".$lim_txt."
          	";
     		$data3 = simple_query($sql3);
     		$mn3=mysqli_num_rows($data3);	
		}	
		
		if($mn3>0)
		{
			$tab.="<tr>
					<td valign='top'><b>ID</b></td>
					<td valign='top'><b>Created</b></td>
					<td valign='top'><b>Received</b></td>
					<td valign='top'><b>Recipient</b></td>
					<td valign='top'><b>Read By</b></td>
					<td valign='top'><b>Read Date</b></td>
					<td valign='top'><b>Reply By</b></td>
					<td valign='top'><b>Reply Date</b></td>
					<td valign='top' colspan='8'><b>Message sent from truck ".$truck_name."</b></td>
				</tr>";	
				
			$tab3.="<tr>					
					<td valign='top' width='50'><b>MsgID</b></td>
					<td valign='top' width='100'><b>Received</b></td>
					<td valign='top' width='100'><b>Read By</b></td>
					<td valign='top' width='100'><b>Read Date</b></td>
					<td valign='top' width='100'><b>Reply By</b></td>
					<td valign='top' width='100'><b>Reply Date</b></td>
					<td valign='top' width='300'><b>Message sent by ".$truck_name."</b></td>
				</tr>";	
		}		
		
		$mydate=date("Y-m-d");		//today...
		
		while($row3 = mysqli_fetch_array($data3))
		{
			if($row3['mrr_mode']=="Sent")
			{     			
     			/*
     			$read_user=mrr_peoplenet_pull_quick_username($row3['my_user_id_read']);
     			$read_date=date("m/d/Y H:i:s",strtotime($row3['my_linedate_read']));			if($row3['my_linedate_read']=="0000-00-00 00:00:00") 	$read_date="";
     			$reply_user=mrr_peoplenet_pull_quick_username($row3['my_user_id_reply']);
     			$reply_date=date("m/d/Y H:i:s",strtotime($row3['my_linedate_reply']));		if($row3['my_linedate_reply']=="0000-00-00 00:00:00") 	$reply_date="";
     			
     			$driver=mrr_find_pn_truck_drivers($row3['my_truck_id'],$mydate);     			
          		$row3['my_recipient_name']=str_replace("!OIUser","<b>Dispatch</b>",$row3['my_recipient_name']);
          		
     			if(substr_count($row3['my_msg'],"Warning: ")==0)
     			{			
     				$tab.="<tr>
          						<td valign='top'><span class='mrr_link_like_on' id='msg_list_id_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$row3['my_id']."</span></td>
          						<td valign='top'><span class='mrr_link_like_on' id='msg_list_created_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".mrr_peoplenet_time_mask_from_gmt($row3['my_linedate_created'])."</span></td>
          						<td valign='top'><span class='mrr_link_like_on' id='msg_list_received_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".mrr_peoplenet_time_mask_from_gmt($row3['my_linedate_received'])."</span></td>
          						<td valign='top'><span class='mrr_link_like_on' id='msg_list_recipient_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$row3['my_recipient_name']."</span></td>
          						<td valign='top'><span class='mrr_link_like_on' id='msg_list_read_user_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$read_user."</span></td>
          						<td valign='top'><span class='mrr_link_like_on' id='msg_list_read_date_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$read_date."</span></td>
          						<td valign='top'><span class='mrr_link_like_on' id='msg_list_reply_user_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$reply_user."</span></td>
          						<td valign='top'><span class='mrr_link_like_on' id='msg_list_reply_date_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$reply_date."</span></td>
          						<td valign='top' colspan='8'><span class='mrr_link_like_on' id='msg_list_msg_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$row3['my_msg']."</span></td>
     						</tr>";
     				
     				//load board version...
     				$tab2.=	"<li>";
          			$tab2.=		"<h3>";
          			$tab2.=			"<span>".date("m/d/Y H:i:s",strtotime("-6 hours",strtotime($row3['my_linedate_received'])))." --- <a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".trim($row3['my_truck_name'])."</a></span>";     	//admin_trucks.php?id=".$row3['truck_id']."
          			//$tab2.=			"<a href='javascript:delete_event($row[my_calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
          			$tab2.=			"<a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
          			$tab2.=		"</h3>";
          			$tab2.=		"<p>Driver(s): ".$driver."<br><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>Unread...Click here to read.</a> ".trim($row3['my_recipient_name']).": ".$row3['my_msg']."</p> ";
          			$tab2.=	"</li>";
     						
     				$tab3.="<tr>
          						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".$row3['my_id']."</a></td>
          						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".mrr_peoplenet_time_mask_from_gmt($row3['my_linedate_received'])."</a></td>
          						<td valign='top'>".$read_user."</td>
          						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".$read_date."</a></td>
          						<td valign='top'>".$reply_user."</td>
          						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".$reply_date."</a></td>
          						<td valign='top'>".$row3['my_msg']."</td>
     						</tr>";
     						
     				$mcntr++;
     			}
     			*/
			}	
			elseif($row3['mrr_mode']=="Phoned")
			{	
				//".$row3['my_id']."
				$tab.="<tr>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top'>".date("m/d/Y H:i", strtotime($row3['my_linedate_created']))."</td>
     						<td valign='top'>PHONED</td>
     						<td valign='top'>".$row3['my_truck_name']."</td>
     						<td valign='top'>Load ".$row3['my_load']."</td>
     						<td valign='top'>Disp ".$row3['my_disp']."</td>
     						<td valign='top' colspan='2'>Stop ".$row3['my_recipient_name']."</td>
     						<td valign='top' colspan='8'>".$row3['my_msg']."</td>
						</tr>";
				if(substr_count($row3['my_truck_name'],"Departed") > 0)
				{
					$sqlu = "     		
               			update load_handler_stops set
               				linedate_completed='".sql_friendly($row3['my_linedate_created'])."'
               			where id='".sql_friendly($row3['my_recipient_name']) ."'
               		";
          			simple_query($sqlu);
				}
				/*
				
				//load board version...
				$tab2.=	"<li>";
     			$tab2.=		"<h3>";
     			$tab2.=			"<span>".date("m/d/Y H:i", strtotime($row3['my_linedate_created']))." --- <a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=0'>".trim($row3['my_truck_name'])."</a></span>";     	//admin_trucks.php?id=".$row3['truck_id']."
     			//$tab2.=			"<a href='javascript:delete_event($row[my_calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     			$tab2.=			"<a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=0'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
     			$tab2.=		"</h3>";
     			$tab2.=		"<p>Driver(s): ".$driver."<br><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=0'>Unread...Click here to read.</a> ".trim($row3['my_recipient_name']).": ".$row3['my_msg']."</p> ";
     			$tab2.=	"</li>";
				*/		
				
				//<a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=0'>".$row3['my_id']."</a>
				$tab3.="<tr>
     						<td valign='top'>PHONED</td>
     						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=0'>".date("m/d/Y H:i", strtotime($row3['my_linedate_created']))."</a></td>
     						<td valign='top'>".$row3['my_truck_name']."</td>
     						<td valign='top'>Load ".$row3['my_load']."</td>
     						<td valign='top'>Disp ".$row3['my_disp']."</td>
     						<td valign='top'>Stop ".$row3['my_recipient_name']."</td>
     						<td valign='top'>".$row3['my_msg']."</td>
						</tr>";
						
				$mcntr3++;	
			}	
		}
		
		if($mcntr==0 && $mcntr3==0)
		{
			$tab="<tr><td valign='top' colspan='16'>No messages found.</td></tr>";	
		}
		/*
		$tab2.=	"<li>";
     	$tab2.=		"<h3>";
     	$tab2.=			"<br><span>PN and MSGS Legend</span>";    			
     	$tab2.=		"</h3>";
     	$tab2.=		"<p><b>PN</b>=PeopleNet Dispatch can be sent.</p> ";
     	$tab2.=		"<p><b>MSGS</b>=Message link for this truck.</p> ";
     	$tab2.=		"<p style='color:red; font-weight:bold;'>Dispatch has not been sent to truck.</p>";
     	$tab2.=		"<p style='color:orange; font-weight:bold;'>Updated since last send.</p>";
     	$tab2.=		"<p style='color:green; font-weight:bold;'>Dispatch sent to truck.</p>";
     	$tab2.=		"<p>Colors based on sent status. MSGS flag will always match PN link based on status of peoplenet dispatch.  MSGS is quick link to messages.</p> ";
     	$tab2.=		"<p>Hover on Load to see distance and current truck location.  <b>No Distance.</b> displays when dispatch has not been sent through PeopleNet system and no GPS coordinates have been calculated for the stops.  Send dispatch to fix this.</p> ";
     	$tab2.=	"</li>";	
		*/
		
		if($mode==0)	return $tab;
		if($mode==1)	return $tab2;
		if($mode==3)	return $tab3;
	}
?>