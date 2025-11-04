<? ini_set("max_input_vars","10000");	//must change in INI file... ?>
<? include('application.php') ?>
<?
// phone number: 615-209-9029
/*
////////////// sample form vars:
AccountSid: AC684a891a4dc7476487da40cb6ae56c3f
ToZip: 37167
FromState: TN
Called: +16152099029
FromCountry: US
CallerCountry: US
CalledZip: 37167
Direction: inbound
FromCity: SMYRNA
CalledCountry: US
CallerState: TN
CallSid: CA1724b4826138b50b1b09dad4b19516ee
CalledState: TN
From: +16154592921
CallerZip: 37122
FromZip: 37122
CallStatus: ringing
ToCity: SMYRNA
ToState: TN
To: +16152099029
ToCountry: US
CallerCity: SMYRNA
ApiVersion: 2010-04-01
Caller: +16154592921
CalledCity: SMYRNA
*/

//$_POST['Caller'] = '6155551234';

if(isset($_GET['test']))	
{	//test mode	
	$_POST['Called']="16152099029";
	$_POST['To']=="16152099029";
	
	$_POST['From']="16156913253";	//"+16154592921";		//"+16159575407";		
	//$_POST['Body']="This is Just a Driver Test.";
}

$skip_forms=0;
//if($_POST['To']=="16152099029" || $_POST['To']=="+16152099029" || $_POST['Called']=="16152099029" || $_POST['Called']=="+16152099029")		$skip_forms=1;

if($skip_forms > 0)
{	//NEW response to forward reply txt msg from dispatch to driver, or back.
	$user=0;
	$from=mrr_clear_phone_number_extras($_POST['From']);
	$to="";
	$driver=0;
	$msg=trim($_POST['Body']);
	$reply=0;
	$msg_id=0;
	
	$reply_mode=0;
	
	//test if reply is from users...
	$sql="
		select id, name_first,name_last,txt_msg_reply_phone
		from users
		where active>0 
			and deleted=0
			and txt_msg_reply > 0
			and txt_msg_reply_phone!=''
		order by name_last asc, name_first asc
	";	//and LTRIM(RTRIM(phone_cell))!=''
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{	
		//$namer=" <span style='color:#00CC00;'><b>".trim($row['name_first'])." ".trim($row['name_last'])."</b></span>";		//<a href='admin_users.php?eid=".$row['id']."' target='_blank'>".trim($row['name_first'])." ".trim($row['name_last'])."</a>
		
		//$driver_listing=str_replace($phone_number,$namer,$driver_listing);			
		
		$phone_number=mrr_clear_phone_number_extras($row['txt_msg_reply_phone']);
		if($from==$phone_number)
		{
			$user=$row['id'];	
			$reply_mode=1;		//it is the dispatcher replying to the driver (or to the entire message last sent?)
			
			$to="Driver";
			
			//find last message reply sent...assuming driver sent a reply back first
          	$sql = "
          		select id					
          		from ".mrr_find_log_database_name()."txt_msg_reply_log
          		where deleted = 0
          			and driver_id >= 0
          			and user_id = 0
          			and txt_mode = 2
          		order by linedate_added desc, 
          			driver_id desc,
          			id desc
          	";
          	$data = simple_query($sql);
          	if($row = mysqli_fetch_array($data)) 
          	{
          		$reply=$row['id'];
          	}
		}    
	}
	
	//now see if the number is one of the drivers...
	$sql="
		select id, name_driver_first,name_driver_last,phone_cell
		from drivers
		where active>0 
			and deleted=0			
			and id!=405 and id!=345
		order by name_driver_last asc, name_driver_first asc
	";	//and LTRIM(RTRIM(phone_cell))!=''
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{	
		//$namer=" ".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."";		//<a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."</a>
		
		//$driver_listing=str_replace($phone_number,$namer,$driver_listing);	
		
		$phone_number=mrr_clear_phone_number_extras($row['phone_cell']);    
		if($from==$phone_number)
		{
			$driver=$row['id'];	
			$reply_mode=2;		//it is this driver replying to the dispatch message or the reply (or to the entire message last sent?)
			
			$to="Dispatch";
			
			//find last message reply sent...assuming driver sent a reply back first
          	$sql = "
          		select id					
          		from ".mrr_find_log_database_name()."txt_msg_reply_log
          		where deleted = 0
          			and user_id >= 0
          			and driver_id = 0
          			and txt_mode = 2
          		order by linedate_added desc, 
          			user_id desc,
          			id desc
          	";
          	$data = simple_query($sql);
          	if($row = mysqli_fetch_array($data)) 
          	{
          		$reply=$row['id'];
          	}
		}   
	}
	
	//find last message sent (BULK)
	$sql = "
		select id					
		from ".mrr_find_log_database_name()."txt_msg_log
		where deleted = 0
			and txt_mode = 2
		order by linedate_added desc, id desc
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data)) 
	{
		$msg_id=$row['id'];
	}
	
	
	
	//add to log that it has been received.
	$sql = "
		insert into ".mrr_find_log_database_name()."txt_msg_reply_log
			(id,
			linedate_added,				
			user_id,
			driver_id,				
			to_phone,
			from_phone,
			message_body,			
			reply_id,
			bulk_msg_id,
			deleted,
			txt_mode)
		values
			(NULL,
			NOW(),
			'".sql_friendly($user)."',
			'".sql_friendly($driver)."',
			'".sql_friendly($to)."',
			'".sql_friendly($from)."',
			'".sql_friendly(trim($msg))."',
			'".sql_friendly($reply)."',
			'".sql_friendly($msg_id)."',
			0,
			2)
	";
	simple_query($sql);	
	
	echo "
		<h2>Responce Call Number: ".$from."</h2>
		<br><b>From ".$from.":</b>
		<br>User ".$user." or Driver ".$driver.".
		<br>To: ".$to."
		<br>Msg: ".$msg."		
		<br>".$reply."
		<br>".$msg_id."
		<br>
		".($reply_mode==1 ? "Dispatch Reply from Msg or Driver." : "")."
		".($reply_mode==2 ? "Driver reply to Dispatch Msg or Reply." : "")."
		".($reply_mode==0 ? "No Reply Mode." : "")."
		<br>Done;
	";
}
else
{
	header("content-type: text/xml");
     echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
     echo "<Response>";
     $rval = "";
     //older form to be interactive for 
     if(isset($_GET['test']))		$_POST['From']="+16154592921";		//"+16159575407";		//test mode
     	
     $driver_name="";
     $driver_id=0;
     $truck_id=0;
     $trailer_id=0;
     $tracked=0;
     $truck_name="";
     $trailer_name="";
     
     $details="";
     
     $last_lat="";
     $last_long="";
     $last_local="";
     $last_date="";
     $last_speed="";
     $last_head="";
     
     $subject="";
     $message="";
     
     $customer_id=0;
     $load_id=0;
     $disp_id=0;
     $stop_id=0;
     $start_trailer_id=0;
     $end_trailer_id=0;
     $argvals="";
     
     if(isset($_GET['driver']))		$driver_id=$_GET['driver']; 
     if(isset($_GET['truck']))		$truck_id=$_GET['truck'];
     if(isset($_GET['trailer']))		$trailer_id=$_GET['trailer'];
     if(isset($_GET['cust']))			$customer_id=$_GET['cust'];
     if(isset($_GET['load']))			$load_id=$_GET['load'];
     if(isset($_GET['disp']))			$disp_id=$_GET['disp'];
     if(isset($_GET['stop']))			$stop_id=$_GET['stop'];
     if(isset($_GET['strail']))		$start_trailer_id=$_GET['strail'];
     if(isset($_GET['etrail']))		$end_trailer_id=$_GET['etrail'];
     
     $drivers=mrr_get_driver_info_array(0);
     while($row=mysqli_fetch_array($drivers))
     {
     	$test_phone1=trim($row['phone_cell']);
     	//$test_phone2=trim($row['phone_home']); 
     	//$test_phone3=trim($row['phone_other']);
     	
     	$test_phone1=str_replace("-","",$test_phone1);
     	$test_phone1=str_replace(".","",$test_phone1);
     	$test_phone1=str_replace("(","",$test_phone1);
     	$test_phone1=str_replace(")","",$test_phone1);
     	$test_phone1="+1".trim($test_phone1);
     		
     	if($_POST['From']==$test_phone1)
     	{
     		$driver_name=$row['name_driver_first']." ".$row['name_driver_last'];
     		$driver_id=$row['id'];
     		$truck_id=$row['attached_truck_id'];
     		$trailer_id=$row['attached_trailer_id'];
     		$tracked=$row['peoplenet_tracking'];
     		$truck_name=$row['name_truck'];
     		$trailer_name=$row['trailer_name'];
     		
     		$details="
     			<b>".$driver_name."</b> [".$truck_name." | ".$trailer_name."]
     		";
     		
     		$res=mrr_get_driver_current_load_info_for_phone($driver_id,$truck_id);		//get the next stop...that is not completed.
     		$load_id=$res['load_id'];
     		$disp_id=$res['disp_id'];
     		$stop_id=$res['stop_id'];
     		$customer_id=$res['customer_id'];
     		$start_trailer_id=$res['start_trailer_id'];
     		$end_trailer_id=$res['end_trailer_id'];
     		
     		if($tracked>0)
     		{	//get last location for this driver/truck.
     			$tracking=mrr_get_driver_truck_location($truck_id);	
     			if($row2=mysqli_fetch_array($tracking))
     			{
     				$last_lat=$row2['latitude'];
     				$last_long=$row2['longitude'];
     				$last_local=$row2['location'];
     				$last_date=date("m/d/Y H:i",strtotime($row2['linedate_added']));					
     				$last_speed=$row2['truck_speed'];
     				$last_head=$row2['truck_heading'];
     				
     				$head_mask="North";
               		if($last_head == 1)		$head_mask="Northeast ";
               		if($last_head == 2)		$head_mask="East";
               		if($last_head == 3)		$head_mask="Southeast";
               		if($last_head == 4)		$head_mask="South";
               		if($last_head == 5)		$head_mask="Southwest";
               		if($last_head == 6)		$head_mask="West";
               		if($last_head == 7)		$head_mask="Northwest";	
     				
     				$details.="
     					:
     					<br>GPS: (".$last_lat.",".$last_long.").
     					<br>Loc: ".$last_local.".
     					<br>Heading ".$head_mask." at ".$last_speed."MPH as of ".$last_date.".
     				";
     			}			
     		}		
     	}	
     }
     
     
     $argvals="&amp;driver=".$driver_id."&amp;truck=".$truck_id."&amp;trailer=".$trailer_id."&amp;cust=".$customer_id."&amp;load=".$load_id."&amp;disp=".$disp_id."&amp;stop=".$stop_id."&amp;strail=".$start_trailer_id."&amp;etrail=".$end_trailer_id."";
     
     $display_info="";
     if(isset($_GET['test']))
     {
     	$display_info=" Driver=".$driver_id.", Truck=".$truck_id.", Trailer=".$trailer_id.", Customer=".$customer_id.", Load=".$load_id.", Dispatch=".$disp_id.", Stop=".$stop_id.", Start Trailer=".$start_trailer_id.", End Trailer=".$end_trailer_id.".";
     }
     
     if(isset($_POST['Caller'])) 
     {
     	$_POST['Caller'] = str_replace("+","", $_POST['Caller']);
     }	
     
     $mess="";
     
     if($load_id==0)
     {
     	echo "
          	<Say><![CDATA[No Load Info found for ".$driver_name."]]></Say>
     			<Pause />
     		<Say><![CDATA[Please call Dispatch for help.  Good Bye.]]></Say>
     	";	
     }
     elseif(isset($_GET['cmd'])) 
     {
     	/*
     	arrival only stamps date arrival....
     	
     	drop/switch on trailer from departure
     	1-same,  2-drop&hook(switch), 3-drop&bob-tailing(drop only)
     	depart stamps completed date...	
     	*/
     	if($_GET['Digits']=="0")
     	{
     		$_GET['cmd'] ="";	
     		echo "
     			<Gather action='phone_handler.php?cmd=verify_action".$argvals."' method='GET'>
     				<Say><![CDATA[Welcome ".$driver_name.".]]></Say>
     				<Pause />
     				<Say><![CDATA[Please select an option from the list.]]></Say>
     				<Pause />
     				<Say><![CDATA[
     					Enter one if you have arrived, two if you are loaded, three if you are empty, or zero to repeat the main menu... followed by the pound sign.
     					]]></Say>
               	</Gather>
     		";
     	}
     	
     	if($_GET['cmd'] == 'verify_number') 
     	{		
     		if($_GET['Digits']=="1" || $_GET['Digits']=="2")
     		{
     			echo "
               		<Gather action='phone_handler.php?cmd=verify_action".$argvals."' method='GET'>
               			<Say><![CDATA[Thank you.]]></Say>
               			<Pause />
               			<Say><![CDATA[Please select an option from the list.]]></Say>
               			<Pause />
               			<Say><![CDATA[
               				Please enter one if you have arrived, two if you are loaded, three if you are empty, or zero to repeat the main menu... followed by the pound sign.          				
               				]]></Say>
               		</Gather>
     			";			//, two if you will be late, or zero to have Dispatch call you back, followed by the pound sign.
     			
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Validating Location',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     		
     		else
     		{
     			echo "
               		<Gather action='phone_handler.php?cmd=verify_number".$argvals."' method='GET'>
               			<Say><![CDATA[You have entered an invalid response.]]></Say>
               			<Pause />
               			<Say><![CDATA[Please enter one for yes, two for no, or zero to repeat the main menu, followed by the pound sign.]]></Say>
               		</Gather>
     			";
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Invalid Number',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}		
     		//echo "<Say><![CDATA[You entered: $_GET[Digits].  Thank you ".$driver_name." (".$truck_id." | ".$trailer_id.").]]></Say>";
     	} 
     	elseif($_GET['cmd'] == 'verify_action')
     	{
     		if($_GET['Digits']=="1")
     		{
     			$mess=mrr_stop_dispatch_completer_for_phone($stop_id,$disp_id,$load_id,$customer_id,$driver_id,0,0,1);
     			$mrr_pn_email_processor=mrr_fetch_peoplenet_email_processor($disp_id,0,1,$stop_id);
     			
     			echo "
               		<Say><![CDATA[Great. You have arrived.]]></Say>
     					<Pause />
     				<Say><![CDATA[".$mess.". Thank you. Good bye.]]></Say>
     			";
     			/*
     				<Gather action='phone_handler.php?cmd=verify_trailer".$argvals."' method='GET'>
               			<Say><![CDATA[Great. You have arrived.]]></Say>
               			<Pause />
               			<Say><![CDATA[
               				Press one if you are dropping the trailer, two if you are switching trailers, or zero if neither apply... followed by the pound sign.
               				]]></Say>
               		</Gather>
     			*/
     			
     			
     			
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Arrived...',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     		elseif($_GET['Digits']=="2" || $_GET['Digits']=="3")
     		{
     			//complete stop,dispatch,load.
     			$mess=mrr_stop_dispatch_completer_for_phone($stop_id,$disp_id,$load_id,$customer_id,$driver_id,0,0,0);
     			$mrr_pn_email_processor=mrr_fetch_peoplenet_email_processor($disp_id,1,1,$stop_id);
     			
     			echo "
     				<Say><![CDATA[You have departed. Thank you.]]></Say>
     					<Pause />
     				<Say><![CDATA[".$mess.". Good bye.]]></Say>
     			";						// Stop ".$stop_id.". Dispatch ".$disp_id.". Load ".$load_id.". Driver ".$driver_id.". Truck ".$truck_id.".
     
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Departed/Stop Completed',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     			
     			/*
     			echo "
               		<Gather action='phone_handler.php?cmd=verify_trailer".$argvals."' method='GET'>
               			<Say><![CDATA[Great. You are ready to depart.]]></Say>
               			<Pause />
               			<Say><![CDATA[
               				Press one if you are keeping the same trailer, two if you are drop hooking, three if you are bob tailing, or zero to repeat the main menu... followed by the pound sign.
               				]]></Say>
               		</Gather>
     			";
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Departed...',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     			*/
     		}
     		
     		/*
     		elseif($_GET['Digits']=="2")
     		{
     			echo "
               		<Gather action='phone_handler.php?cmd=late_minutes".$argvals."' method='GET'>
               			<Say><![CDATA[Please enter the number of minutes you think you will be late, followed by the pound sign.]]></Say>
               		</Gather>
     			";
     			
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Getting Minutes Late',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     		elseif($_GET['Digits']=="3")
     		{
     			echo "<Say><![CDATA[Thank you. Dispatch will be notified and call you back soon.]]></Say>";
     			
     			//have dispatch call driver back
     			$subject="Please call ".$driver_name." (".$_POST['From'].")";
     			$message="Driver indicated there may be a problem and requests a call from dispatch.<br><br>".$details."<br><br>Please do not reply...automated message.";
     			mrr_send_email_from_phone_response($subject,$message);
     			
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Request Callback',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     		*/
     	}
     	elseif($_GET['cmd'] == 'verify_trailer')
     	{
     		if($_GET['Digits']=="3")
     		{			
     			echo "
               		<Gather action='phone_handler.php?cmd=trailer_drop".$argvals."' method='GET'>
               			<Say><![CDATA[Please enter the numeric name of the trailer you are dropping, or zero to repeat the main menu, followed by the pound sign.]]></Say>
               		</Gather>
     			";
     			
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Getting Trailer to Drop',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     		elseif($_GET['Digits']=="2")
     		{
     			echo "
               		<Gather action='phone_handler.php?cmd=trailer_switch_drop".$argvals."' method='GET'>
               			<Say><![CDATA[Please enter the numeric name of the trailer you are dropping, or zero to repeat the main menu, followed by the pound sign.]]></Say>
               		</Gather>
     			";
     			
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Getting Trailer to Switch',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     		elseif($_GET['Digits']=="1")
     		{		
     			//complete stop,dispatch,load.
     			$mess=mrr_stop_dispatch_completer_for_phone($stop_id,$disp_id,$load_id,$customer_id,$driver_id,0,0,0);
     			$mrr_pn_email_processor=mrr_fetch_peoplenet_email_processor($disp_id,1,1,$stop_id);
     			
     			echo "
     				<Say><![CDATA[Thank you.]]></Say>
     					<Pause />
     				<Say><![CDATA[".$mess.". Good bye.]]></Say>
     			";						// Stop ".$stop_id.". Dispatch ".$disp_id.". Load ".$load_id.". Driver ".$driver_id.". Truck ".$truck_id.".
     
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Stop Completed',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     		else
     		{
     			echo "
               		<Gather action='phone_handler.php?cmd=verify_trailer".$argvals."' method='GET'>
               			<Say><![CDATA[You have entered an invalid option, please try again.]]></Say>
               			<Pause />
               			<Say><![CDATA[
               				Press one if you are keeping the same trailer, two if you are drop hooking , three if you are bob tailing, or zero to repeat the main menu... followed by the pound sign.
               				]]></Say>
               		</Gather>
     			";	
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'UNKNOWN',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     	}
     	elseif($_GET['cmd'] == 'trailer_drop')
     	{		
     		//drop the trailer and complete stop,dispatch,load.
     		$mess=mrr_stop_dispatch_completer_for_phone($stop_id,$disp_id,$load_id,$customer_id,$driver_id,$start_trailer_id,0,0);
     		$mrr_pn_email_processor=mrr_fetch_peoplenet_email_processor($disp_id,1,1,$stop_id);
     			
     		echo "
     			<Say><![CDATA[Dropping trailer.  Thank you.]]></Say>
     			<Pause />
     			<Say><![CDATA[".$mess.". Good bye.]]></Say>
     		";
     		
     		mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Dropped Trailer',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     	}
     	elseif($_GET['cmd'] == 'trailer_switch_drop')
     	{
     		echo "
               	<Gather action='phone_handler.php?cmd=trailer_switch".$argvals."' method='GET'>
               		<Say><![CDATA[Dropping trailer in progress...]]></Say>
               		<Pause />
               		<Say><![CDATA[Please enter the number of the trailer you are picking up or switching to, or zero to repeat the main menu, followed by the pound sign.]]></Say>
               	</Gather>
     		";
     		
     		mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Drop and Switch',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     	}
     	elseif($_GET['cmd'] == 'trailer_switch')
     	{				
     		//find the trailer entered...force lookup of trailer just in case.
     		$end_trailer_id=0;
     		$sql = "select id from trailers where trailer_name like '".sql_friendly($_GET['Digits'])."%' and deleted=0 and active > 0";
          	$data = simple_query($sql);
          	if($row = mysqli_fetch_array($data))	$end_trailer_id=$row['id'];	
     				
     		if($end_trailer_id==0)
     		{
     			//error, couldn't find the trailer.			
     			echo "
     				<Gather action='phone_handler.php?cmd=trailer_switch_drop".$argvals."' method='GET'>
               			<Say><![CDATA[Sorry, that trailer could not be found...]]></Say>
               			<Pause />
               			<Say><![CDATA[To cancel the trailer switch, you may enter the same numeric trailer name that you dropped on your next try, or enter the correct numeric name for the new trailer you are picking up.]]></Say>
               			<Pause />
               			<Say><![CDATA[Press one when you are ready to try again, or zero to repeat the main menu, followed by the pound sign.]]></Say>
               		</Gather>
               	";		
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Switched Trailer Not Found...loop.',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     		else
     		{		
     			//now switch trailer, then complete stop,dispatch,load.
     			$mess=mrr_stop_dispatch_completer_for_phone($stop_id,$disp_id,$load_id,$customer_id,$driver_id,$start_trailer_id,$end_trailer_id,0);
     			$mrr_pn_email_processor=mrr_fetch_peoplenet_email_processor($disp_id,1,1,$stop_id);
     		
     			echo "
     				<Say><![CDATA[Switching to trailer ".$_GET['Digits'].".  Thank you.]]></Say>
     				<Pause />
     				<Say><![CDATA[".$mess.". Good bye.]]></Say>
     			";		
     		
     			mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Switched Trailers',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     		}
     	}
     	elseif($_GET['cmd'] == 'late_minutes')
     	{
     		echo "<Say><![CDATA[Thank you. Dispatch will be notified that you are running ".$_GET['Digits']." minutes late.]]></Say>";
     		
     		//send email to dispatch that driver is running late.
     		$subject="".$driver_name." is running ".$_GET['Digits']." minutes late. (".$_POST['From'].")";
     		$message="Driver ".$driver_name." is running ".$_GET['Digits']." minutes late (".$_POST['From'].").<br><br>".$details."<br><br>Please do not reply...automated message.";
     		mrr_send_email_from_phone_response($subject,$message);
     		
     		mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'Running Late',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     	}
     	else 
     	{
     		echo "<Say><![CDATA[You have reached an unexpected area. Please try again later.]]></Say>";
     		
     		mrr_add_twilio_call_log($_POST['From'],$_GET['cmd'],'UNKNOWN',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     	}
     } 
     else 
     {
     	/*
     	echo "
     		<Gather action='phone_handler.php?cmd=verify_number".$argvals."' method='GET'>
     			<Say><![CDATA[Welcome ".$driver_name.".]]></Say>
     			<Pause />
     			<Say><![CDATA[Are you calling from $_POST[FromCity], $_POST[FromState]?]]></Say>
     			<Pause />
     			<Say><![CDATA[Please enter one for yes, or 2 for no, followed by the pound sign.]]></Say>
     		</Gather>
     	";
     	*/	
     	
     	echo "
     		<Gather action='phone_handler.php?cmd=verify_action".$argvals."' method='GET'>
     			<Say><![CDATA[Welcome ".$driver_name.".]]></Say>
     			<Pause />
     			<Say><![CDATA[Please select an option from the list.]]></Say>
     			<Pause />
     			<Say><![CDATA[
     				Enter one if you have arrived, two if you are loaded, three if you are empty, or zero to repeat the main menu... followed by the pound sign.".$display_info."
     				]]></Say>
               </Gather>
     	";	//two if you will be late, or zero to have Dispatch call you back... followed by the pound sign.
     		
     	mrr_add_twilio_call_log($_POST['From'],'','Call Started',$_GET['Digits'],$driver_id,$truck_id,$trailer_id,$load_id,$disp_id,$stop_id,$customer_id,$_POST['FromCity'],$_POST['FromState'],$last_local,$last_lat,$last_long,$last_speed,$last_head,$subject,$message);
     }
     echo "</Response>";
}
?>