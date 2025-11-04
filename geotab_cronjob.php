<? include_once('application.php'); ?>
<?	

ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	echo "
		<br>
		<div style='border:1px solid #0000cc; margin:5px; padding:5px; width:1000px;'>		
			
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			<tr height='30'>
				<td valign='middle' colspan='4' align='center'><b>Standard Operations:</b></td>
			</tr>
			<tr>
				<td valign='top'>Login/Authenticate</td>	<td valign='top'><a href='geotab_cronjob.php?feed_type=99'>geotab_cronjob.php?feed_type=99</a></td>
				<td valign='top'>Auto-Complete</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=98'>geotab_cronjob.php?feed_type=98</a></td>
			</tr>
			<tr>
				<td valign='top'>Routes/Dispatch/Planned</td><td valign='top'><a href='geotab_cronjob.php?feed_type=60'>geotab_cronjob.php?feed_type=60</a></td>
				<td valign='top'>Stops/Zones</td>			<td valign='top'><a href='geotab_cronjob.php?feed_type=50'>geotab_cronjob.php?feed_type=50</a></td>
			</tr>
			<tr>
				<td valign='top'>Truck Devices</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=30'>geotab_cronjob.php?feed_type=30</a></td>
				<td valign='top'>Truck Messages</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=40'>geotab_cronjob.php?feed_type=40</a></td>
			</tr>
			<tr>
				<td valign='top'>Trailers</td>			<td valign='top'><a href='geotab_cronjob.php?feed_type=35'>geotab_cronjob.php?feed_type=35</a></td>
				<td valign='top'>Users/Drivers</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=20'>geotab_cronjob.php?feed_type=20</a></td>
			</tr>
			<tr height='30'>
				<td valign='middle' colspan='4' align='center'><b>Processing Operations:</b></td>
			</tr>
			<tr>
				<td valign='top'>Truck Locations</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=97'>geotab_cronjob.php?feed_type=97</a></td>
				<td valign='top'>Hourly Updates</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=49'>geotab_cronjob.php?feed_type=49</a></td>
			</tr>
			<tr>
				<td valign='top'>[100] Truck List</td><td valign='top'><a href='geotab_cronjob.php?feed_type=100'>[List All]</a> --- <a href='geotab_cronjob.php?feed_type=100&run_auto=1'>[Auto]</a></td>
				<td valign='top'>[101] Unit Log Records</td>	<td valign='top'>Use the [100] Truck List for unit records.</td>
			</tr>
			<tr height='30'>
				<td valign='middle' colspan='4' align='center'><b>DataFeed Operations:</b></td>
			</tr>
			<tr>
				<td valign='top'>[1]  LogRecord</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=1'>geotab_cronjob.php?feed_type=1</a></td>
				<td valign='top'>[2]  StatusData</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=2'>geotab_cronjob.php?feed_type=2</a></td>
			</tr>
			<tr>
				<td valign='top'>[3]  FaultData</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=3'>geotab_cronjob.php?feed_type=3</a></td>
				<td valign='top'>[4]  Trip</td>			<td valign='top'><a href='geotab_cronjob.php?feed_type=4'>geotab_cronjob.php?feed_type=4</a></td>
			</tr>
			<tr>
				<td valign='top'>[5]  ExceptionEvent</td>	<td valign='top'><a href='geotab_cronjob.php?feed_type=5'>geotab_cronjob.php?feed_type=5</a></td>
				<td valign='top'>[6]  DutyStatusLog</td>	<td valign='top'><a href='geotab_cronjob.php?feed_type=6'>geotab_cronjob.php?feed_type=6</a></td>
			</tr>
			<tr>
				<td valign='top'>[7]  AnnotationLog</td>	<td valign='top'><a href='geotab_cronjob.php?feed_type=7'>geotab_cronjob.php?feed_type=7</a></td>
				<td valign='top'>[8]  DVIRLog</td>			<td valign='top'><a href='geotab_cronjob.php?feed_type=8'>geotab_cronjob.php?feed_type=8</a></td>
			</tr>
			<tr>
				<td valign='top'>[9]  ShipmentLog</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=9'>geotab_cronjob.php?feed_type=9</a></td>
				<td valign='top'>[10] TrailerAttachment</td>	<td valign='top'><a href='geotab_cronjob.php?feed_type=10'>geotab_cronjob.php?feed_type=10</a></td>
			</tr>
			<tr>
				<td valign='top'>[11] IoxAddOn</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=11'>geotab_cronjob.php?feed_type=11</a></td>
				<td valign='top'>[12] CustomData</td>		<td valign='top'><a href='geotab_cronjob.php?feed_type=12'>geotab_cronjob.php?feed_type=12</a></td>
			</tr>		
			<tr height='30'>
				<td valign='middle' colspan='4' align='center'><b><a href='geotab.php'>Diagnostics</a></b></td>
			</tr>
			</table>
			
		</div>
	";	
		
	echo "<br>GeoTab vs PN Switch is <b>".($_SESSION['use_geotab_vs_pn']==1 ? "ON" : "off")."</b>.<br>";
		
	if(!isset($_GET['feed_type']))		$_GET['feed_type']=0;
	
	$feed_type=(int) $_GET['feed_type'];
	
	//$geotab_disp_id="bA";
	//mrr_kill_geotab_route("Route",trim($geotab_disp_id));    
	
	if($feed_type==1)	
	{
		//test to see if the date/time stamp is too far back.         //If date is bad, auto-advance starting date so it does not get stuck or freeze on older date.
          $max_hours=12;                                                //Max Hours to force the setting forward just in case.
          $curr_stamp=date("Y-m-d H:i:s",time());                //this will be local time, not GMT.  (5 or 6 hours behind)    
	     $last_stamp=trim($defaultsarray['geotab_last_feed_id_1']);    //2021-10-04T14:49:04.303Z
	     if(strlen($last_stamp)>19)        $last_stamp=substr($last_stamp,0,19);
          $last_stamp=str_replace("T"," ", $last_stamp);
          $diff_stamp="CORRECT";
          if($curr_stamp > $last_stamp) 
          {    //local time has caught up with, and passed, the last time stamp.  Something is wrong...change the date stamp to advance past this point.
               $new_stamp=date("Y-m-d H:i:s",strtotime("+".$max_hours." hours",strtotime($curr_stamp)));
               $new_stamp=str_replace(" ","T", $new_stamp);
               $new_stamp.=".000Z";
     
               $sqlu="
                    update defaults set
                        xvalue_string='".$new_stamp."'
                    where xname='geotab_last_feed_id_1'
               ";
               simple_query($sqlu);
               
               $diff_stamp="DELAYED...OVERRIDE. Max Hours pushed ahead ".$max_hours.". Rest to ".$new_stamp.".";          
          }
	     
	     echo "<br>Current Stamp is ".$curr_stamp.". Last Stamp is ".$last_stamp.". Difference = ".$diff_stamp.".<br>";
	     echo "<br>[".$feed_type."] Data Feed: LogRecord<br>".mrr_get_geotab_get_datafeed("LogRecord").".";		
		
		mrr_pull_geotab_active_geofencing_rows_no_display(0);
	}
	if($feed_type==100)	
	{	//print the list only...
		$run_auto=0;
		if(isset($_GET['run_auto']))		$run_auto=1;
		
		$lowest_date="0000-00-00 00:00:00";
		$lowest_device="";
		
		echo "<br><b>Trucks available for GeoTab Tracking... with Device ID filled in.</b><br>";
		echo "<table cellpadding='1' cellspacing='1' border='0' width='600'>";
		echo "
			<tr>
				<td valign='top'>ID</td>
				<td valign='top'>Truck Name</td>
				<td valign='top'>GeoTab Device ID</td>
				<td valign='top'>Last Run</td>
			<tr>
		";
		$sql="
			select geotab_device_id,id,name_truck,geotab_last_location_date			
			from trucks
			where deleted=0 
				and active > 0
				and geotab_device_id!=''
			order by name_truck asc,id asc
		";
		$data = simple_query($sql);	
		while($row = mysqli_fetch_array($data))
		{			
			if($row['geotab_last_location_date']!="0000-00-00 00:00:00")
			{
				$last_dated=strtotime($row['geotab_last_location_date']);
			}
			else
			{
				$last_dated=strtotime("-1 day",time());	
			}
			
			if($lowest_date > $row['geotab_last_location_date'] || $lowest_date=="0000-00-00 00:00:00")
			{
				$lowest_date=$row['geotab_last_location_date'];
				$lowest_device=trim($row['geotab_device_id']);	
			}
			
			echo "
				<tr>
					<td valign='top'><a href='admin_trucks.php?id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
					<td valign='top'>".$row['name_truck']."</td>
					<td valign='top'><a href='geotab_cronjob.php?feed_type=101&device_id=".trim($row['geotab_device_id'])."&last_time=".$last_dated."' target='_blank'>".trim($row['geotab_device_id'])."</a></td>
					<td valign='top'>".$row['geotab_last_location_date']." --- ".$last_dated."</td>
				<tr>
			";
		}
		echo "</table>";
		
		echo "<br>Auto Run: ".($run_auto > 0 ? "ON" : "off").".  Device ID=".$lowest_device.", Last Time=".$lowest_date.".<br>";
				
		if($run_auto==1 && $lowest_device!="")
		{
			$last_time=strtotime($lowest_date);
			
			echo "<br>[".$feed_type."]--AUTORUN-- DEVICE ".$lowest_device." ONLY Since ".$last_time." |  Data Feed: LogRecord<br>".mrr_get_geotab_get_datafeed("LogRecord",$lowest_device,$last_time).".";		
			mrr_pull_geotab_active_geofencing_rows_no_display(0);			
		}
	}
	if($feed_type==101)	
	{	// get the locations of this truck only...
		if(!isset($_GET['device_id']))		$_GET['device_id']="";
		if(!isset($GET['last_time']))			$_GET['last_time']="";		
		$cur_device=trim($_GET['device_id']);
		$last_time=trim($_GET['last_time']);
		if($last_time=="")		$last_time=strtotime(date("Y-m-d",time())." 00:00:00");
		
		if($cur_device!="")
		{
			echo "<br>[".$feed_type."] DEVICE ".$cur_device." ONLY Since ".$last_time." |  Data Feed: LogRecord<br>".mrr_get_geotab_get_datafeed("LogRecord",$cur_device,$last_time).".";		
			mrr_pull_geotab_active_geofencing_rows_no_display(0);
		}
	}
	if($feed_type==2)	echo "<br>[".$feed_type."] Data Feed: StatusData<br>".mrr_get_geotab_get_datafeed("StatusData").".";
	if($feed_type==3)	echo "<br>[".$feed_type."] Data Feed: FaultData<br>".mrr_get_geotab_get_datafeed("FaultData").".";
	if($feed_type==4)	echo "<br>[".$feed_type."] Data Feed: Trip<br>".mrr_get_geotab_get_datafeed("Trip").".";
	if($feed_type==5)	echo "<br>[".$feed_type."] Data Feed: ExceptionEvent<br>".mrr_get_geotab_get_datafeed("ExceptionEvent").".";
	if($feed_type==6)	echo "<br>[".$feed_type."] Data Feed: DutyStatusLog<br>".mrr_get_geotab_get_datafeed("DutyStatusLog").".";
	if($feed_type==7)	echo "<br>[".$feed_type."] Data Feed: AnnotationLog<br>".mrr_get_geotab_get_datafeed("AnnotationLog").".";
	if($feed_type==8)	echo "<br>[".$feed_type."] Data Feed: DVIRLog<br>".mrr_get_geotab_get_datafeed("DVIRLog").".";
	if($feed_type==9)	echo "<br>[".$feed_type."] Data Feed: ShipmentLog<br>".mrr_get_geotab_get_datafeed("ShipmentLog").".";
	if($feed_type==10)	echo "<br>[".$feed_type."] Data Feed: TrailerAttachment<br>".mrr_get_geotab_get_datafeed("TrailerAttachment").".";
	if($feed_type==11)	echo "<br>[".$feed_type."] Data Feed: IoxAddOn<br>".mrr_get_geotab_get_datafeed("IoxAddOn").".";
	if($feed_type==12)	echo "<br>[".$feed_type."] Data Feed: CustomData<br>".mrr_get_geotab_get_datafeed("CustomData").".";
	
	if($feed_type==20)	echo "<br>[".$feed_type."] Users/Drivers List<br>".mrr_get_geotab_get_users().".";	
	
	if($feed_type==30)	echo "<br>[".$feed_type."] Truck Device List<br>".mrr_get_geotab_get_devices().".";	
	if($feed_type==35)	echo "<br>[".$feed_type."] Trailer List<br>".mrr_get_geotab_get_trailers().".";	
	
	if($feed_type==40)	
	{
		echo "<br>[".$feed_type."] Truck Messages<br>".mrr_get_geotab_get_txtmsg(0,"","").".";
		
		//Add new Msg processing for special ops keywords and commands that will allow drivers to use the messages to move dispatches along...Added 5/19/2021 MRR for Justin.
          $res=mrr_special_ops_geotab_msg_processing();
          echo "<br><b>Messages with keywords to process...</b><br>".$res."<br>";
				
		//piggy-back the FedEx EDI warnings here since hopefully, there will be no need to have too many at a time...
          echo "<br><br><h3>FedEx EDI Repair:  </h3<br>";
		include_once('functions_fedex.php');	
		$tab=mrr_resend_missing_fed_ex_edi_responses(date("m/d/Y",time()),1);		//     "06/01/2018"
		echo $tab;
	}
	if($feed_type==49)	
	{
		$mrr_pn_email_processor2=mrr_fetch_geotab_email_hourly_updates(0);	
		echo "<br>[".$feed_type."] Hourly Updates<br>".$mrr_pn_email_processor2.".";		
	}
	
	if($feed_type==50)	echo "<br>[".$feed_type."] Stops/Zones<br>".mrr_get_geotab_zones("","").".";
	if($feed_type==60)	echo "<br>[".$feed_type."] Routes/Dispatch/Planned<br>".mrr_get_geotab_routes("","").".";
	
	
	if($feed_type==97)	echo "<br>[".$feed_type."] Processing: Truck Locations<br>".mrr_process_current_geotab_location_of_trucks(1,0).".";		//0=view only.  1=Save new locations.
		
	if($feed_type==98)	
	{		
		mrr_deactivate_completed_geofence_rows();	
		
		//update timezones for stop locations...
		$resultmrr=mrr_update_stop_GPS_timezones();
		echo "<br>[".$feed_type."] Geofencing TimeZones<br>".$resultmrr.".";	
	
		//update the message driver settings for night shift drivers...
		//mrr_fix_trucks_shift_driver_load_dispatch(0);
		
		
		//update stop geofencing info
		//$debugger="Geofencing Update Off";
		//$debugger=mrr_run_full_geofencing_update_for_truck_V2(0);	
		//echo "<br>Geofencing Update<br>".$debugger.".";	
		
		$mrr_pn_email_processor=mrr_compare_geotab_location_with_current_stops(0,0,0,0);		//$truck_id,$load_id,$disp_id,$stop_id
		echo "<br>[".$feed_type."] GeTab Email Processor<br>".$mrr_pn_email_processor.".";	
				
		mrr_pull_geotab_active_geofencing_rows_no_display(0);							//run current stops to get the Pro Miles...
				
		echo "<br><br>[".$feed_type."] Auto-Complete Done.";	
	}
		
	if($feed_type==99)	echo "<br>[".$feed_type."] Log-In:<br>".mrr_get_authenticate().".";	
	//<br>Real: https://my.geotab.com/apiv1/Authenticate?database=conard&userName=michael@sherrodcomputers.com&password=R3dS0x18
		
	
	mrr_trim_old_geotab_datafeed_log_points(30,$feed_type,0);         //delete from geotab_datafeed_log in X days
		
	if($feed_type==0)	
	{
		$comp_name="QVC";
		$geotab_stop_id="";
     	//$geotab_stop_id=mrr_get_geotab_zones("",$comp_name);
     	//echo "<br>Getting Zone(s) for <b>".$comp_name."</b>:<br>".$geotab_stop_id.".";
		
		echo "<br><b>ERROR:</b> [".$feed_type."] No operation has been selected.<br>";
	}
	echo "<br>Feed Type [".$feed_type."]<br>DONE.<br>";
	
	
	
	//check the FedEx EDI loads for any that are created, but not sent back to the FedEx response that it is picked up and not still tendered.
	
	
?>