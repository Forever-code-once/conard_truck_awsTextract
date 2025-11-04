<? include('application.php')?>
<?

function getUserIpAddr()
{ 
	if(!empty($_SERVER['HTTP_CLIENT_IP']))
	{ 
		$ip = $_SERVER['HTTP_CLIENT_IP']; 
	}
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{ 
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
	}
	else
	{ 
		$ip = $_SERVER['REMOTE_ADDR']; 
	} 
	return $ip; 
}
$ip=getUserIpAddr();

echo "Welcome: You have found me from this IP ".$ip.".<br>";

die('Done Loading.');

include_once('functions_koch.php');
$load_id=136120;
$rate=mrr_repair_koch_edi_bill_rate($load_id);

echo "<br>Load ".$load_id." rate is $".number_format($rate,2).".<br>";

die('Done Loading.');


$externalContent = file_get_contents('http://checkip.dyndns.com/');
preg_match('/Current IP Address: \[?([:.0-9a-fA-F]+)\]?/', $externalContent, $m);
$externalIp = $m[1];
echo "Welcome: You have found me at this IP ".$externalIp.".<br>";

die('Done Loading.');

/*     
     $cntr=0;
     $sqlt="select * from customers where payment_notes!='' order by id asc";
     $datat = simple_query($sqlt);
     while($rowt = mysqli_fetch_array($datat))
     {
          $custid=$rowt['id'];
          $notes=trim($rowt['payment_notes']);
          $userid=1;
          $dater="2000-01-01 00:00:00";
          
          echo "<br>".$cntr."-- Customer ".$custid." (".$rowt['name_company'].") -- ".$notes.".";
     
          $sqli = "
                insert into payment_notes_history
                    (id,customer_id,linedate_added,user_id,payment_note,deleted)
                values 
                    (NULL,'".sql_friendly($custid)."','".$dater."','".sql_friendly($userid)."','".sql_friendly($notes)."',0)
             ";
          //simple_query($sqli);
     
          $cntr++;
     }
     */
     $From = "system@conardtransportation.com";
     $FromName = "Conard Dispatch Dev Test";
     $To = $defaultsarray['special_email_monitor'];
     $ToName = "MRR Conard Dispatch Testing";
     $Text = "Conard Dispatch Test Body: " . time();
     $Html = $Text;
     $Subject = "Conard Dispatch Test Subject : " . time();
     mrr_sendMail_attachment($From,$FromName,$To,$ToName,$Subject,$Text,$Html);
     die("<br><br>DONE.");
     /*
     function mrr_date_diff($date_1,$date_2)
     {
          $datetime1 = date_create($date_1);
          $datetime2 = date_create($date_2);
          
          $interval = date_diff($datetime1, $datetime2);
     
          $interval = abs($interval->format('%a'));         
          
          if($date_1 < $date_2)      $interval=($interval * -1);
          
          return $interval;          
     }
     
     $date1="2021-07-01";     $date2="2021-02-01";          $diff=0;
     $diff = mrr_date_diff($date1, $date2);
     echo "<br>1. Date 1 (".$date1.") - Date 2 (".$date2.") = ".$diff.".";

     $date1="2021-07-01";     $date2="2020-11-25";          $diff=0;
     $diff = mrr_date_diff($date1, $date2);
     echo "<br>2. Date 1 (".$date1.") - Date 2 (".$date2.") = ".$diff.".";
     
     $date1="2021-07-01";     $date2="2020-06-19";          $diff=0;
     $diff = mrr_date_diff($date1, $date2);
     echo "<br>3. Date 1 (".$date1.") - Date 2 (".$date2.") = ".$diff.".";
     
     $date1="2021-07-01";     $date2="2018-02-01";          $diff=0;
     $diff = mrr_date_diff($date1, $date2);
     echo "<br>4. Date 1 (".$date1.") - Date 2 (".$date2.") = ".$diff.".";
     */
     
     //test run of the new GeoTab Message processing... using keywords that Justin and I agreed on...list may build later.
     //$res2=mrr_special_ops_geotab_msg_processing2();
     $res=mrr_special_ops_geotab_msg_processing();
     
     //echo "<br><b>Messages with no Truck Device...</b><br>".$res2."<br>";
     echo "<br><b>Messages with keywords to process...</b><br>".$res."<br>";
     
     die("<br><br>DONE.");
     
     /*	
	include_once('functions_lynnco.php');
	
	$load_handler_id=88323;
	$out_file = "990_CDPN79365T_1550862502.txt";		//
	if(1==2)
	{
		if(send_lynnco_edi('/Inbound/', $in_path."/out/", $out_file, $load_handler_id,1)) 
		{
			// upload was successful, remove that file from the server
			echo "<br><font color='green'>Successful Upload of 990 file for Load ".$load_handler_id.".</font>";
			$rslt = 1;
			$msg = "LynnCo 990 EDI Sent Successfully";					
		}
		else
		{
			echo "<br><font color='red'>Failed to Upload of 990 file for Load ".$load_handler_id.".</font>";	
		}
	}
	
	$stop1=226775;
	$stop2=226776;
	$stop3=226777;
	
	echo "<br>Load ".$load_handler_id." Testing...  Below.";
	//echo "<br>Load ".$load_handler_id." Stop ".$stop1." 214 AF sent.  Result=".mrr_send_lynnco_214_update($stop1,"AF",1).".";
	//echo "<br>Load ".$load_handler_id." Stop ".$stop2." 214 D1 sent.  Result=".mrr_send_lynnco_214_update($stop2,"D1",1).".";
	echo "<br>Load ".$load_handler_id." Stop ".$stop3." 214 D1 sent.  Result=".mrr_send_lynnco_214_update($stop3,"D1",1).".";		
	
	die("<br><br>DONE.");	
	
	*/
	echo "<table>";
	echo "
	     <tr>
               <td valign='top'>ID</td>
               <td valign='top'>Truck</td>
               <td valign='top'>Oil Interval</td>
               <td valign='top'>Date</td>
               <td valign='top'>Oil</td>
               <td valign='top'>AVI Interval</td>
               <td valign='top'>AVI</td>
               <td valign='top'>Date</td>
          </tr>
	";
     $sqlt = "
          select id,name_truck,pm_miles_interval,pm_miles_last_oil,pm_miles_last_date,valve_miles_interval,valve_miles_last,valve_miles_last_date				
          from trucks
          where active>0 and deleted=0
          order by name_truck asc
     ";
     $datat = simple_query($sqlt);
     while($rowt = mysqli_fetch_array($datat))
     {    
          echo "
               <tr>
                    <td valign='top'>".$rowt['id']."</td>
                    <td valign='top'>".$rowt['name_truck']."</td>
                    <td valign='top'>".$rowt['pm_miles_interval']."</td>
                    <td valign='top'>".$rowt['pm_miles_last_date']."</td>
                    <td valign='top'>".$rowt['pm_miles_last_oil']."</td>
                    <td valign='top'>".$rowt['valve_miles_interval']."</td>
                    <td valign='top'>".$rowt['valve_miles_last']."</td>
                    <td valign='top'>".$rowt['valve_miles_last_date']."</td>
               </tr>
          ";
          
          //Make the Oild Change starting recoprd for this truck.
          $log_date=$rowt['pm_miles_last_date'];
          $log_interval=$rowt['pm_miles_interval'];
          $log_odom=$rowt['pm_miles_last_oil'];
          $log_next_date="";
          $log_next_odom=(int) $log_odom + (int) $log_interval;
     
          $sql_log = "
		        insert into avi_oil_change_log
		            (id,
		            linedate_added,
		            linedate,
		            truck_id,
		            user_id,
		            odometer,
		            linedate_next,
		            odometer_next,
		            cur_interval,
		            notice,
		            oil_change,
		            avi_mode)
		        values 
		            (NULL,
		            NOW(),
		            '".(trim($log_date)!=""  ? "".date("Y-m-d",strtotime($log_date)) :"0000-00-00")."',
		            '".sql_friendly($rowt['id'])."',
		            '23',
		            '".sql_friendly(money_strip($log_odom))."',
		            '".(trim($log_next_date)!=""  ? "".date("Y-m-d",strtotime($log_next_date)) :"0000-00-00")."',
		            '".sql_friendly(money_strip($log_next_odom))."',
		            '".sql_friendly(money_strip($log_interval))."',
		            'STARTING RECORD',
		            '1',
		            '0')
		     ";
          //simple_query($sql_log);    //add the log entry.... for the report later.
          
          //NOW do the AVI starting point.         
          $log_date=$rowt['valve_miles_last_date'];
          $log_interval=$rowt['valve_miles_interval'];
          $log_odom=$rowt['valve_miles_last'];
          $log_next_date="";
          $log_next_odom=(int) $log_odom + (int) $log_interval;
               
          $sql_log = "
		        insert into avi_oil_change_log
		            (id,
		            linedate_added,
		            linedate,
		            truck_id,
		            user_id,
		            odometer,
		            linedate_next,
		            odometer_next,
		            cur_interval,
		            notice,
		            oil_change,
		            avi_mode)
		        values 
		            (NULL,
		            NOW(),
		            '".(trim($log_date)!=""  ? "".date("Y-m-d",strtotime($log_date)) :"0000-00-00")."',
		            '".sql_friendly($rowt['id'])."',
		            '23',
		            '".sql_friendly(money_strip($log_odom))."',
		            '".(trim($log_next_date)!=""  ? "".date("Y-m-d",strtotime($log_next_date)) :"0000-00-00")."',
		            '".sql_friendly(money_strip($log_next_odom))."',
		            '".sql_friendly(money_strip($log_interval))."',
		            'STARTING RECORD',
		            '0',
		            '1')
		     ";
          //simple_query($sql_log);    //add the log entry.... for the report later.
     }
     echo "</table>";
     
     die("<br><br>DONE.");

     echo "<br><a href='#page_bottom'>Go To Link/Bottom</a>";

	if(!isset($_GET['min_id']))        $_GET['min_id']=0;
     $_GET['min_id']=(int) $_GET['min_id'];

     $last_stop_id=mrr_repair_gps_points_on_load(0,$_GET['min_id']);
     
     echo "<br><a href='sql_mrr.php?min_id=".($last_stop_id + 1)."' name='page_bottom'>Reload Page...</a>";
     die("<br><br>DONE.");
     

 /*
     $stop_id=mrr_restore_backup_load_stop_gps_points($_GET['min_id']);
     
     echo "<br><a href='sql_mrr.php?min_id=".($stop_id + 1)."' name='page_bottom'>Reload Page...</a>";
     die("<br><br>DONE.");
*/
 
	//$tab=mrr_process_current_geotab_location_of_trucks(1,0);
	//$device_id="b1";
	//$device_id="";
	//$tab=mrr_get_geotab_get_device_info($device_id,1,1);
	//echo $tab;
	//die("<br><br>DONE.");	
	
	/*
	$date_from="01/01/2019";
	$date_to="02/01/2019";
	$aging_from=0;
	$aging_to=365;
	$results=mrr_get_ar_summary_detail_info_v2(0,'',$date_from,$date_to,$aging_from,$aging_to);
     foreach($results as $key => $value )
	{
		$prt=trim($key);			$tmp=trim($value);
          if($prt=="mrrTab")			$mrr_tab=$tmp; 
          echo "<br><br>".$prt." = ".$tmp."";
	}	
	*/
	
	//$found_reviews=mrr_send_email_list_of_driver_reviews(15,0);		//0=days before now to find reviews due.  2nd arg is 0/1=test mode
	
	//mrr_send_email_list_of_maint_requests(1,0);
	//mrr_pmi_fed_trucks_due_soon(7,1,0,1);
	//mrr_pmi_fed_trailers_due_soon(7,1,1);
		
	//die("<br><br>DONE.");



     $res=mrr_pmi_fed_trucks_due_soon(7,1,0);			//Report shows all the trucks with the PMI/FED/Oil dates due within 7days.
     echo "<h2>Truck Oil/PM/FED Due Report: (".$res['num']." Found)</h2><br>".$res['report']."<br>";
     die("<br><br>DONE.");

	include_once('functions_fedex.php');	
	
	$in_path = $defaultsarray['edi_fedex_path'];								//C:\\web\\trucking.conardlogistics.com/edi/fedex_in/out/
	$complete_path="C:\\web\\"."trucking.conardlogistics.com/edi/fedex_in/out/";
	//$complete_path="".$in_path."out\\";									
	$out_file = "990_3577179_1533377701.txt";
	$load_id=82148;
	//send_fedex_edi('/FXFE/990/', $complete_path, $out_file, $load_id,1);
	
	$tab=mrr_resend_missing_fed_ex_edi_responses("03/13/2019",1);
	echo $tab."
		<br><br>In-Path:".$in_path.".<br><br>Complete Path: ".$complete_path.".		
	";	//<br><a href='".$complete_path."".trim($row['fedex_edi_input_file'])."".trim($out_file)."' target='_blank'>".$complete_path."".trim($row['fedex_edi_input_file'])."/".trim($out_file)."</a>.
		
	die("<br><br>DONE.");
	
	include_once("functions_geotab.php");
	include_once("functions_geotab_usage.php");
		
	
	if(!isset($_GET['make_zone']))		$_GET['make_zone']=0;
	$id=(int) $_GET['make_zone'];
		
	$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
	$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;
	
	$cntr=0;
	$tab="<table cellpadding='1' cellspacing='1' border='1' width='1700'>";
	$tab.="
		<tr style='font-weight:bold;'>
			<td valign='top'>#</td>
			<td valign='top'>ID</td>
			<td valign='top'>Longitude</td>
			<td valign='top'>Latitude</td>
			<td valign='top'>Shipper</td>
			<td valign='top'>Address</td>
			<td valign='top'>City</td>
			<td valign='top'>State</td>
			<td valign='top'>Zip</td>
			<td valign='top'>LongitudeW</td>
			<td valign='top'>LongitudeE</td>
			<td valign='top'>LatitudeN</td>
			<td valign='top'>LatitudeS</td>
			<td valign='top'>ZoneName</td>
		</tr>
	";
	$sql="
		select *
		from geotab_stop_zones
		where deleted=0 
			".($id > 0 ? "and id='".$id."'" : "")."		
			and address_1!='' 
			and city!=''
			and state!=''
			and zip!='' 
			
		order by zip asc
	";	//and (geotab_id_name='' or geotab_id_name='0')
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{			
		$zone_name="Add Zone";
		if(trim($row['geotab_id_name'])!="")		$zone_name="".trim($row['geotab_id_name'])."";
		
		$use_updater=0;
		if(trim($row['longitude'])!="" && trim($row['latitude'])!="" && ($row['lat_zone_n']=="" || $row['lat_zone_s']=="" || $row['long_zone_e']=="" || $row['long_zone_n']==""))
		{
			//mrr_get_promiles_gps_from_address($row['address_1'],$row['city'],$row['state'],$row['zip']);
			//$row['latitude']=$ares['lat'];
			//$row['longitude']=$ares['long'];
			
			$gres=mrr_gps_point_box_creator($row['longitude'],$row['latitude'],$x_off,$y_off);
			
			$row['lat_zone_n']="".$gres['pt0_lat_n']."";			//North
			$row['lat_zone_s']="".$gres['pt1_lat_s']."";			//South
			$row['long_zone_e']="".$gres['pt1_long_e']."";		//East
			$row['long_zone_w']="".$gres['pt0_long_w']."";		//West
			
			$res['long_zone_w']=$row['long_zone_w'];
			$res['long_zone_e']=$row['long_zone_e'];
			$res['lat_zone_n']=$row['lat_zone_n'];
			$res['lat_zone_s']=$row['lat_zone_s'];
			
			$use_updater=1;
		}
		
		
		//echo "<br>Got here...1<br>";
		if($zone_name!="Add Zone" || $use_updater > 0)
		{
			$res['geotab_id_name']=trim($zone_name);
			$res['long']=trim($row['longitude']);
			$res['lat']=trim($row['latitude']);
				
			mrr_update_geotab_stop_zones_gps_name($res,1);	
		}
		
		if($id > 0 && trim($zone_name)=="Add Zone")
		{
			//echo "<br>Got here...2<br>";
			
			$info="".$row['address_1']."; ".$row['city'].", ".$row['state']." ".$row['zip']."";
			$notes="";
			
			if($row['long_zone_w']==$row['long_zone_e'] || $row['lat_zone_n']==$row['lat_zone_s'])
			{
				$res=mrr_gps_point_box_creator($row['longitude'],$row['latitude'],$x_off,$y_off);
				
				$row['lat_zone_n']="".$res['pt0_lat_n']."";			//North
				$row['lat_zone_s']="".$res['pt1_lat_s']."";			//South
				$row['long_zone_e']="".$res['pt1_long_e']."";		//East
				$row['long_zone_w']="".$res['pt0_long_w']."";		//West
			}
			
			$res['long_zone_w']=$row['long_zone_w'];
			$res['long_zone_e']=$row['long_zone_e'];
			$res['lat_zone_n']=$row['lat_zone_n'];
			$res['lat_zone_s']=$row['lat_zone_s'];
			
			$zone_name=mrr_make_geotab_zone($row['longitude'],$row['latitude'],$row['conard_name'],$info,$notes,1,$row['long_zone_w'],$row['long_zone_e'],$row['lat_zone_n'],$row['lat_zone_s']);
			if(trim($zone_name)!="0")
			{
				$res['geotab_id_name']=trim($zone_name);
				$res['long']=trim($row['longitude']);
				$res['lat']=trim($row['latitude']);
				
				mrr_update_geotab_stop_zones_gps_name($res,1);	
			}
		}
				
		$tab.="
			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>
				<td valign='top'>".($cntr + 1)."</td>
				<td valign='top'>".$row['id']."</td>
				<td valign='top'>".$row['longitude']."</td>
				<td valign='top'>".$row['latitude']."</td>
				<td valign='top'>".$row['conard_name']."</td>
				<td valign='top'>".$row['address_1']."</td>
				<td valign='top'>".$row['city']."</td>
				<td valign='top'>".$row['state']."</td>
				<td valign='top'>".$row['zip']."</td>
				<td valign='top'>".$row['long_zone_w']."</td>
				<td valign='top'>".$row['long_zone_e']."</td>
				<td valign='top'>".$row['lat_zone_n']."</td>
				<td valign='top'>".$row['lat_zone_s']."</td>
				<td valign='top' nowrap>".($zone_name=="Add Zone" ? "<a href='sql_mrr.php?make_zone=".$row['id']."' target='_blank'>".$zone_name."</a>" : "<a href='sql_mrr.php?make_zone=".$row['id']."' target='_blank'>".$zone_name."</a>")."</td>
			</tr>
		";
		
		
		$cntr++;
	}
	$tab.="</table>";
	echo $tab;
	
	
	
	
	die("<br><br>DONE.");
	
	
	$truck_id=497;
	$mydate=date("Y-m-d",time());
	$dres=mrr_find_geotab_truck_drivers_from_elog($truck_id,$mydate,1);
	echo "
		<h2>Truck ID: ".$truck_id."</h2><br>".$mydate.":
		<br>Load ID: ".$dres['load_id']."
		<br>Disp ID: ".$dres['dispatch_id']."
		<br>Driver 1: ".$dres['driver_id_1']."
		<br>Driver 2: ".$dres['driver_id_2']."
		<br>Driver Name 1: ".$dres['driver_name_1']."
		<br>Driver Name 2: ".$dres['driver_name_2']."
		<br>SQL 1=".$dres['sql_1']."
		<br>SQL 2=".$dres['sql_2']."	
		
		<br><hr><br>	
	";
	
	
	$dres=mrr_find_pn_truck_drivers($truck_id,$mydate,1);
	echo "
		<h2>Truck ID: ".$truck_id."</h2><br>".$mydate.":
		<br>Load ID: ".$dres['load_id']."
		<br>Disp ID: ".$dres['dispatch_id']."
		<br>Driver 1: ".$dres['driver_id_1']."
		<br>Driver 2: ".$dres['driver_id_2']."
		<br>Driver Name 1: ".$dres['driver_name_1']."
		<br>Driver Name 2: ".$dres['driver_name_2']."
		<br>Driver User 1: ".$dres['driver_user_1']."
		<br>Driver User 2: ".$dres['driver_user_2']."
		<br>SQL 1=".$dres['sql_1']."
		<br>SQL 2=".$dres['sql_2']."		
	";
	
	
	
	die("<br><br>DONE.");
	
	
	die("<br><br>DONE.");
	
	$cntr=0;
	$tab="<table cellpadding='1' cellspacing='1' border='1' width='1600'>";
	$tab.="
		<tr style='font-weight:bold;'>
			<td valign='top'>#</td>
			<td valign='top'>ID</td>
			<td valign='top'>Longitude</td>
			<td valign='top'>Latitude</td>
			<td valign='top'>Shipper</td>
			<td valign='top'>Address</td>
			<td valign='top'>City</td>
			<td valign='top'>State</td>
			<td valign='top'>Zip</td>
			<td valign='top'>PickupETA</td>
			<td valign='top'>LocalID</td>
		</tr>
	";
	$sql="
		select id,shipper_name,shipper_address1,shipper_city,shipper_state,shipper_zip,longitude,latitude,linedate_pickup_eta
		from load_handler_stops
		where deleted=0 
			and longitude!=0 
			and latitude!=0
			and linedate_pickup_eta>='2017-01-01 00:00:00' and linedate_pickup_eta<='2017-01-31 23:59:59'
		order by id desc
	";
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$local_id=0;
		
		$x="".$row['longitude']."";
		$y="".$row['latitude']."";
		
		$res=mrr_find_geotab_stop_zones_by_gps($x,$y);	
		if($res['id']==0)
		{
			$gres=mrr_gps_point_box_creator($x,$y,$x_off,$y_off);
			
			$res['geotab_id_name']="";
          	$res['address_1']=$row['shipper_address1'];
          	$res['city']=$row['shipper_city'];
          	$res['state']=$row['shipper_state'];
          	$res['zip']=$row['shipper_zip'];
          	$res['long']=$x;
          	$res['lat']=$y;
          	$res['long_zone_w']="".$gres['pt0_long_w']."";
          	$res['long_zone_e']="".$gres['pt1_long_e']."";
          	$res['lat_zone_n']="".$gres['pt0_lat_n']."";
          	$res['lat_zone_s']="".$gres['pt1_lat_s']."";
          	
          	$res['conard_name']=$row['shipper_name'];
          		
          	$local_id=mrr_create_geotab_stop_zones($res);			
		}
		else
		{
			$local_id=$res['id'];
		}
		
		$tab.="
			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>
				<td valign='top'>".($cntr+1)."</td>
				<td valign='top'>".$row['id']."</td>
				<td valign='top'>".$row['longitude']."</td>
				<td valign='top'>".$row['latitude']."</td>
				<td valign='top'>".$row['shipper_name']."</td>
				<td valign='top'>".$row['shipper_address1']."</td>
				<td valign='top'>".$row['shipper_city']."</td>
				<td valign='top'>".$row['shipper_state']."</td>
				<td valign='top'>".$row['shipper_zip']."</td>
				<td valign='top' nowrap>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
				<td valign='top'>".$local_id."</td>
			</tr>
		";
		$cntr++;
	}
	$tab.="</table>";
	echo $tab;


	die("<br><br>DONE.");

	//Messages in next packet use the same basic processing... 
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
		$max_msg_packet=$row['next_msg_packet_id'];
	}
	$max_msg_packet=1;
	echo "<br>Max Message Packet=".$max_msg_packet."<br>";	
	
	//$max_msg_packet=0;
	$serve_output3="";
	
	$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_message_history",0,0,"",0,$max_msg_packet,0,0);
	
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
	}	
	echo $serve_output3;
	
die("<br><br>DONE.");

mrr_trucking_sendMail("trucking@conardtransportation.com",'Dispatch',"system@conardlogistics.com","Test System 3",'','',"Test message... MRR","Test message 3... MRR 5","Test message... MRR 5");
mrr_trucking_sendMail("trucking@conardtransportation.com",'Dispatch',"trucking@conardtransportation.com","Test System 4",'','',"Test message... MRR","Test message 4... MRR 6","Test message... MRR 6");

die("<br><br>DONE.");

$rep=mrr_pn_request_form_processor_alt(113688);		//form ID in PN system.  Use this form ID to process incoming messages linked to it on PN side.
echo "<h1>Processing Repair Request Forms ALT:</h1>".$rep.".<br>";

die("<br><br>DONE.");


//$from=trim($defaultsarray['company_email_address']);
//$from_name=trim($defaultsarray['company_name']);
//$sentit=mrr_trucking_sendMail($defaultsarray['special_email_monitor'],'TEST...Available Trucks',$from,$from_name,'','',"This is a test...", "This is a test...Fire at will. :)", "This is a test...yes, a test. :)");	
//$sentit=mrr_trucking_sendMail("maintenance@conardtransportation.com",'TEST...Available Trucks',$from,$from_name,'','',"This is a test...", "This is a test...Fire at will. :)", "This is a test...yes, a test. :)");	
die("<br><br>DONE.");

$res=mrr_pmi_fed_trucks_due_soon(7,1,0);			//Report shows all the trucks with the PMI/FED/Oil dates due within 7days.
echo "<h2>Truck Oil/PM/FED Due Report: (".$res['num']." Found)</h2><br>".$res['report']."<br>";

$res=mrr_pmi_fed_trucks_due_soon(7,1,1);			//Report shows all the company vehicles with the PMI/Fed/Oil dates due within 7 days.
echo "<h2>Company Vehicle Oil/PM/FED Due Report: (".$res['num']." Found)</h2><br>".$res['report']."<br>";

//die("<br><br>DONE.");

$res=mrr_pmi_fed_trailers_due_soon(7,1);			//Report shows all trailers with PMI/Fed dates due within 7 days.
echo "<h2>Trailer PMI/FED Due Report: (".$res['num']." Found)</h2><br>".$res['report']."<br>";

die("<br><br>DONE.");

$phone_number="6159699115";
$message="This is a Conard Text Message Test. :)";


echo "<br><b>Sent to:</b> ".$phone_number."<br><b>Message:</b> ".$message."<br>";
$res=send_twilio_messages($phone_number, $message);
echo "<br>Message has been ".($res > 0 ? "Sent :)" : "Killed :(")."<br>";

die("<br><br>DONE.");

$driver_id=603;
$truck_id=0;

$truck_id=mrr_find_pn_drivers_truck_from_elog($driver_id,date("Y-m-d"));			//find which truck Driver 9ID) was using today.

$dnamer="N/A";
$tnamer="N/A";

$sql="
	select *
	from drivers
	where id='".$driver_id."'
";
$data=simple_query($sql);
if($row=mysqli_fetch_array($data))
{
	$dnamer=trim($row['name_driver_first']." ".$row['name_driver_last']);
}

$sql="
	select *
	from trucks
	where id='".$truck_id."'
";
$data=simple_query($sql);
if($row=mysqli_fetch_array($data))
{
	$tnamer=trim($row['name_truck']);
}


echo "<br>Driver ".$driver_id."=<b>".$dnamer."</b> is in Truck ID ".$truck_id."=<b>".$tnamer.":</b><br>";

$driver_id2=mrr_find_pn_drivers_truck_from_elog_driver($truck_id,date("Y-m-d"));	//finds whihc driver was in Truck (ID) today.

$dnamer2="N/A";

$sql="
	select *
	from drivers
	where id='".$driver_id2."'
";
$data=simple_query($sql);
if($row=mysqli_fetch_array($data))
{
	$dnamer2=trim($row['name_driver_first']." ".$row['name_driver_last']);
}
echo "<br>Double Checker: Driver for truck ".$truck_id." <b>(".$tnamer.")</b> is ".$driver_id2."=<b>".$dnamer2."</b>.";


die("<br><br>DONE.");


echo "<h2><b>Loads with Deadhead Miles Hourly:</b></h2><br>";
$cntr=0;
$sql="
	select *
	from trucks_log
	where deleted=0
		and miles_deadhead_hourly > 0
		and linedate_pickup_eta>='2017-07-31 00:00:00'
	order by linedate_pickup_eta asc
";
$data=simple_query($sql);
while($row=mysqli_fetch_array($data))
{
	$cntr++;
	echo "<br>".$cntr." - Load ".$row['load_handler_id']." Dispatch ".$row['id'].": <a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>Update Dispatch ".$row['id']."</a>";
}

die("<br><br>DONE.");

$res=mrr_send_email_list_of_driver_reviews(0,1);		//reports on all drivers up for the review...or not reviewed at all.
echo "Driver Reviews: ".$res['found'].":<br>List 1<br>".$res['list1']."<br><br>List 2<br>".$res['list2'].".";


die("<br><br>DONE.");

//echo mrr_test_PN_last_msg_sent_date();

$driver_id=600;
$cat_id=mrr_pull_owner_operator_setup($driver_id);	//finds the driver category ID to group the owner operator rates for specific routes.
echo "Driver ".$driver_id." O.O Rates Category ID=".$cat_id.".";


die("<br><br>DONE.");

$truck=431;
$trailer=612;

$breakdown_truck=mrr_find_unit_breakdown_maint_request(58,$truck);		//find the trucks with marks for unit break-down in maint requests... (narrow to one ID if selected ID)
$breakdown_trailer=mrr_find_unit_breakdown_maint_request(59,$trailer);	//find the trailers with marks for unit break-down in maint requests... (narrow to one ID if selected ID)

echo "<br><b>Truck ".$truck.":</b><br>".$breakdown_truck."<br>";
echo "<br><b>Trailer ".$trailer.":</b><br>".$breakdown_trailer."<br>";

die("<br><br>DONE.");


$dater="01/01/2010";
echo mrr_repair_maint_request_schedule_dates($dater);

die("<br><br>DONE.");

$trailer=1;
if(isset($_GET['trailer_id']))	$trailer=$_GET['trailer_id'];	
if($trailer > 0)
{
	$date_from="02/01/2010";
	$date_to="02/28/2017";
	echo "<br><h2>".$date_from." to ".$date_to." - Trailer ID ".$trailer.":</h2><br>";
	echo mrr_update_trailer_drops_for_trailer($date_from,$date_to,$trailer,0);
}

/*
	function mrr_get_month_parts($back_mons)
	{	//get all the month components for x months past current, including max number of days
		$cur_month=date("m");		$cur_year=date("Y");
		
		//Month no longer used		//Max days in month STILL used
		$months[0]="";				$maxdays[0]=0;
		$months[1]="January";		$maxdays[1]=31;
		$months[2]="February";		$maxdays[2]=28;
		$months[3]="March";			$maxdays[3]=31;
		$months[4]="April";			$maxdays[4]=30;
		$months[5]="May";			$maxdays[5]=31;
		$months[6]="June";			$maxdays[6]=30;
		$months[7]="July";			$maxdays[7]=31;
		$months[8]="August";		$maxdays[8]=31;
		$months[9]="September";		$maxdays[9]=30;
		$months[10]="October";		$maxdays[10]=31;
		$months[11]="November";		$maxdays[11]=30;
		$months[12]="December";		$maxdays[12]=31;
		
		$res="
			<table width='100%' cellpadding='0' cellspacing='0' border='0'>
			<tr>
					
		";				
		for($i=0; $i< $back_mons; $i++)
		{
			$datex=date("F",strtotime("-".$i." month",time()));
			$monx=date("m",strtotime("-".$i." month",time()));
			$yearx=date("Y",strtotime("-".$i." month",time()));			
			
			if((int) $monx==2 && (int) $yearx %4==0)	$maxdays[ $monx ]=29;			
			
			$res.="
				<td valign='top' width='16%'>
					<a href='report_comparison.php?date_from=".$monx."_01_".$yearx."&date_to=".$monx."_".$maxdays[ (int)$monx ]."_".$yearx."' target='_blank' title='View Comparison Report for ".$datex." ".$yearx."'>
						".$datex." ".$yearx."
					</a>
				</td>
			";	
		}			
		$res.="
			</tr>
			</table>
			<br>
		";
		return $res;
	}
*/



/*
//scanning process code...

		if(is_file($dir.$file)) 
		{
			preg_match('/(\d+)/',$file, $matches);
	   		//echo $file." | $matches[0]<br>";
	   		$id = $matches[0];
	   		
	   		$mrr_skipit=0;
	   		$file_proper=$file;
	   		for($i=0;$i < 10; $i++)
	   		{
	   			$file=str_replace("-".$i.".",".",$file);	
	   		}
	   		
	   		$pos1=strrpos($file,".");	
	   		$file_ext=substr($file,$pos1);  		$file_ext=str_replace(".","",$file_ext);	
	   		
	   		$ftype = substr($file,0,$pos1);		// strlen($id), ($pos1 - strlen($id))
	   		$ftype = strtoupper(trim(str_replace("_","-", $ftype)));
	   		
	   		//added section to compoensate for the Conard staff not naming the files correctly...
	   		$use_file_typer="";
	   		if(substr_count($ftype,"-")==0)
	   		{
	   			//add "-" since it is not present and hope there is no other in the file in front of the code.
	   			$chrx=1;
	   			$chrv=substr($file,0,$chrx);
	   			while(is_numeric($chrv) && $chrx<=100 && substr_count($chrv,".")==0)
	   			{
	   				$chrx++;
	   				$chrv=substr($file,0,$chrx);
	   			}	
	   			
	   			$chrv=substr($file,0,($chrx-1));
	   			
	   			$mrr_tester=trim(strtoupper($file));
	   			$mrr_tester=str_replace(".".strtoupper($file_ext) , "",$mrr_tester);
	   			
	   			if(is_numeric($mrr_tester))		$mrr_skipit=1;
	   			
	   			$mrr_tester=str_replace($chrv , "",$mrr_tester);
	   			
	   			$mrr_tester=str_replace("VI","-VI",$mrr_tester);	   			$mrr_tester=str_replace("RI","-RI",$mrr_tester);	   			$mrr_tester=str_replace("TI","-TI",$mrr_tester);
	   			$mrr_tester=str_replace("CI","-CI",$mrr_tester);	   			$mrr_tester=str_replace("DI","-DI",$mrr_tester);	   			$mrr_tester=str_replace("LI","-LI",$mrr_tester);
	   			
	   			$mrr_tester=str_replace("VP","-VP",$mrr_tester);	   			$mrr_tester=str_replace("RP","-RP",$mrr_tester);	   			$mrr_tester=str_replace("TP","-TP",$mrr_tester);
	   			$mrr_tester=str_replace("CP","-CP",$mrr_tester);	   			$mrr_tester=str_replace("DP","-DP",$mrr_tester);	   			$mrr_tester=str_replace("LP","-LP",$mrr_tester);
	   			
	   			$mrr_tester=str_replace("VD","-VD",$mrr_tester);	   			$mrr_tester=str_replace("RD","-RD",$mrr_tester);	   			$mrr_tester=str_replace("TD","-TD",$mrr_tester);
	   			$mrr_tester=str_replace("CD","-CD",$mrr_tester);	   			$mrr_tester=str_replace("DD","-DD",$mrr_tester);	   			$mrr_tester=str_replace("LD","-LD",$mrr_tester);
	   			
	   			$mrr_tester=str_replace("VV","-VV",$mrr_tester);	   			$mrr_tester=str_replace("RV","-RV",$mrr_tester);	   			$mrr_tester=str_replace("TV","-TV",$mrr_tester);
	   			$mrr_tester=str_replace("CV","-CV",$mrr_tester);	   			$mrr_tester=str_replace("DV","-DV",$mrr_tester);	   			$mrr_tester=str_replace("LV","-LV",$mrr_tester);
	   			
	   			$mrr_tester=str_replace("VE","-VE",$mrr_tester);	   			$mrr_tester=str_replace("RE","-RE",$mrr_tester);	   			$mrr_tester=str_replace("TE","-TE",$mrr_tester);
	   			$mrr_tester=str_replace("CE","-CE",$mrr_tester);	   			$mrr_tester=str_replace("DE","-DE",$mrr_tester);	   			$mrr_tester=str_replace("LE","-LE",$mrr_tester);
	   			
	   			$mrr_tester=str_replace("VO","-VO",$mrr_tester);	   			$mrr_tester=str_replace("RO","-RO",$mrr_tester);	   			$mrr_tester=str_replace("TO","-TO",$mrr_tester);
	   			$mrr_tester=str_replace("CO","-CO",$mrr_tester);	   			$mrr_tester=str_replace("DO","-DO",$mrr_tester);	   			$mrr_tester=str_replace("LO","-LO",$mrr_tester);
	   			
	   			$mrr_tester=str_replace("VM","-VM",$mrr_tester);	   			$mrr_tester=str_replace("RM","-RM",$mrr_tester);	   			$mrr_tester=str_replace("TM","-TM",$mrr_tester);
	   			$mrr_tester=str_replace("CM","-CM",$mrr_tester);	   			$mrr_tester=str_replace("DM","-DM",$mrr_tester);	   			$mrr_tester=str_replace("LM","-LM",$mrr_tester);
	   			
	   			$mrr_tester=str_replace("VR","-VR",$mrr_tester);	   			$mrr_tester=str_replace("RR","-RR",$mrr_tester);	   			$mrr_tester=str_replace("TR","-TR",$mrr_tester);
	   			$mrr_tester=str_replace("CR","-CR",$mrr_tester);	   			$mrr_tester=str_replace("DR","-DR",$mrr_tester);	   			$mrr_tester=str_replace("LR","-LR",$mrr_tester);
	   			
	   			$use_file_typer="".str_replace(".".strtoupper($file_ext) , "",$mrr_tester)."";
	   			
	   			//echo "<br>".($chrx-1)." chars. \"".$chrv."\". {".$use_file_typer."} -- [".$mrr_tester."]";		//
	   		}  		
	   		//..................................................................................	   		
	   		$ftype_part_array = explode("-",$ftype);
	   		   		
	   		//for($mrr=0; $mrr < count($ftype_part_array); $mrr++)
	   		//{
	   			//echo "<br>$mrr. ".$ftype_part_array[$mrr]."...";	
	   		//}
	   		
	   		$ftype = $ftype_part_array[0];
	   		if($use_file_typer !="")
	   		{
	   			$ftype = trim($use_file_typer);
	   			$ftype = str_replace("-","",$ftype);
	   		}
	   		//foreach($matches[0] as $digit) $id .= $digit;
	   		
	   		$section_id = 0;
	   		
	   		$ftype_sub = '';
	   		if(strlen($ftype) == 2) 
	   		{
	   			$ftype_sub = substr($ftype,1,1); 	// signifies Invoice, Expense, POD, etc...
	   			$ftype = substr($ftype,0,1); 		// signifies Load, Driver, Truck, etc...
	   		}
	   		
	   		echo "<p>File: $file (".$ftype.")| ".$ftype_sub." | [".strtoupper($file_ext)."]<br></p>";
	   		
	   		if(isset($ftype_array[$ftype])) 
	   		{
	   			$section_id = $ftype_array[$ftype];
	   			
	   			if($section_id==1)
	   			{	//drivers
	   				$sql = "select id from drivers where id='".sql_friendly((int)trim($id))."' order by deleted asc, active desc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    	}
	   			}
	   			elseif($section_id==2)
	   			{	//trailers
	   				$sql = "select id from trailers where trailer_name='".sql_friendly(strtoupper(trim($id)))."' order by deleted asc, active desc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    	}
	   			}
	   			elseif($section_id==3)
	   			{	//trucks
	   				$sql = "select id from trucks where name_truck='".sql_friendly(strtoupper(trim($id)))."' order by deleted asc, active desc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    	}
	   			}
	   			elseif($section_id==5)
	   			{	//customers
	   				$sql = "select id from customers where id='".sql_friendly((int)trim($id))."' order by deleted asc, active desc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    	}
	   			}
	   			elseif($section_id==6)
	   			{	//trucks_log
	   				$sql = "select id from trucks_log where id='".sql_friendly((int)trim($id))."' order by deleted asc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    	}
	   			}
	   			elseif($section_id==8)
	   			{	//load_handler
	   				$sql = "select id from load_handler where id='".sql_friendly((int)trim($id))."' order by deleted asc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    	}
	   			}
	   			
	   			//1 - Driver		V
               	//2 - Trailer		R
               	//3 - Truck		T
               	//5 - Customer		C
               	//6 - Dispatches	D
               	//8 - Loads		L
               		
	   			if((int) $id > 0)
	   			{
     	   			if($ftype_sub != '' && isset($ftype_array2[$ftype_sub])) 
     	   			{
     					$file_ext = get_file_ext($file_proper);
     					$file_base = str_replace(".$file_ext","",$file_proper);
     	   				
     	   				$new_fname_tmp = $file_base."-".$ftype_array2[$ftype_sub].".".$file_ext;
     	   				$new_fname_tmp = get_unique_filename($dir, $new_fname_tmp);
     	   				
     	   				
     	   				rename($dir.$file_proper,$dir.$new_fname_tmp);
     	   				
     	   				$file = $new_fname_tmp;
     	   			}	
     	   			
     	   			$new_filename = get_unique_filename($dir_upload, $file);
     	   			
     	   			$sql = "
     	   				insert into attachments
     	   					(fname,
     	   					linedate_added,
     	   					section_id,
     	   					xref_id,
     	   					file_ext,
     	   					filesize,
     	   					result,
     	   					deleted,
     	   					descriptor)
     	   					
     	   				values ('".sql_friendly($new_filename)."',
     	   					now(),
     	   					'".sql_friendly($section_id)."',
     	   					'".sql_friendly($id)."',
     	   					'".sql_friendly(get_file_ext($new_filename))."',
     	   					'".filesize($dir.$file_proper)."',
     	   					1,
     	   					0,
     	   					'".($ftype_sub != '' ? $ftype_sub : "")."')
     	   			";
     	   			simple_query($sql);
     	   			//echo "$sql<br><br>";
     	   			//die("($file | $new_filename | $section_id | $ftype_sub)");
     	   			
     	   			//echo "Moving from ($dir"."$new_filename) to ($dir_upload"."$new_filename)"; 
     	   			$rslt = rename($dir.$file,$dir_upload.$new_filename);
     	   			
	   			}
	   			else
	   			{	//error since the user named the file with the wrong ID...but it otherwise would have worked.
	   				$new_filename = get_unique_filename($dir."problem/",$file);
     
     	   			$sql = "
     	   				insert into attachments
     	   					(fname,
     	   					linedate_added,
     	   					section_id,
     	   					xref_id,
     	   					file_ext,
     	   					filesize,
     	   					result,
     	   					deleted)
     	   					
     	   				values ('".sql_friendly($new_filename)."',
     	   					now(),
     	   					'0',
     	   					'0',
     	   					'".sql_friendly(get_file_ext($new_filename))."',
     	   					'".filesize($dir.$file)."',
     	   					0,
     	   					0)	   				
     	   			";
     	   			simple_query($sql);
     	   			
     	   			rename($dir.$file, $dir."problem/".$new_filename);
	   			}
	   		} 
	   		elseif($section_id == 0 || $mrr_skipit > 0)
	   		{	//move to the problem directory since we can't match it to where it should go
	   			$new_filename = get_unique_filename($dir."problem/",$file);

	   			$sql = "
	   				insert into attachments
	   					(fname,
	   					linedate_added,
	   					section_id,
	   					xref_id,
	   					file_ext,
	   					filesize,
	   					result,
	   					deleted)
	   					
	   				values ('".sql_friendly($new_filename)."',
	   					now(),
	   					'0',
	   					'0',
	   					'".sql_friendly(get_file_ext($new_filename))."',
	   					'".filesize($dir.$file)."',
	   					0,
	   					0)	   				
	   			";
	   			simple_query($sql);
	   			
	   			rename($dir.$file, $dir."problem/".$new_filename);
	   		}	   		
	   		
	   		echo "<p>(".($section_id > 0  ? "$section_id" : "ERROR").") $file | $id | $ftype [".$file_proper."]<br></p>";
	   	}
	   	else
	   	{
	   		//echo "<p>ERROR: ".$dir.$file.".<br></p>";
	   	}














*/
die("<br><br>DONE.");

	
echo "<h2>Night Shift Drivers:</h2>";
echo mrr_fix_trucks_shift_driver_load_dispatch(0);	//547=cur truck id to set....

die("<br><br>DONE.");


$rep=mrr_pn_request_form_processor(126373);		//125685
echo "<h1>Processing Repair Request Forms:</h1>".$rep.".<br>";

die("<br><br>DONE.");

$message="This is a TEST... would have Registration Form for Load 123 Dispatch 456. Reply if you can see and use the link that follows on Truck tablet no rush --MRR. https://trucking.conardtransportation.com/documents/driver_application.pdf";
$test=mrr_peoplenet_form_id_message("002",$message,0,1,1,0,126268,391);

echo "<h1>Trailer Registration Form Test:</h1><pre>".$test."</pre><br>";

$serve_output=mrr_peoplenet_find_data("imessage_send",547,0,$message,0,0,"",0);	//,126268,391

echo "<h2>Trailer Registration Response:</h2><br>".$serve_output."<br>";

/*
<!--?xml version='1.0' encoding='ISO-8859-1'?-->
<pnet_imessage_send>
	<cid><!--[CDATA[3577]]--></cid>
	<pw><!--[CDATA[35con77]]--></pw>
	<vehicle_number><!--[CDATA[002]]--></vehicle_number>
	<deliver><!--[CDATA[now]]--></deliver>
	<formdata>
		<form_id>126268</form_id>
		<im_field>
			<question_number>1</question_number>
			<data>
				<data_text><!--[CDATA[Trailer 01210 S/R: This is a TEST... would have Registration Form for Load 123 Dispatch 456.]]--></data_text>
			</data>
		</im_field>
		<im_field>
			<question_number>2</question_number>
			<data>
				<data_image_ref>
					<data_image_date><!--[CDATA[02/01/2017 15:11]]--></data_image_date>
					<data_image_transid><!--[CDATA[70239e7f83be6e17f39e3711682e8019]]--></data_image_transid>
					<data_image_name><!--[CDATA[https://trucking.conardtransportation.com/documents/driver_application.pdf]]--></data_image_name>
					<data_image_mimetype><!--[CDATA[application/pdf]]--></data_image_mimetype>
				</data_image_ref>
			</data>
		</im_field>
	</formdata>
</pnet_imessage_send>
*/


//$rep=mrr_pn_request_form_processor(125685);
//echo "<h1>Processing Repair Request Forms:</h1>".$rep.".<br>";

die("<br><br>DONE.");

/*
<formdata><form_id>125685</form_id><im_field><field_number>1</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_text>Darrell</data_text></data></im_field><im_field><field_number>2</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_numeric>002</data_numeric></data></im_field><im_field><field_number>3</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_multiple-choice><mc_choicenum>2</mc_choicenum><mc_choicetext>No</mc_choicetext></data_multiple-choice></data></im_field><im_field><field_number>4</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_multiple-choice><mc_choicenum>2</mc_choicenum><mc_choicetext>No</mc_choicetext></data_multiple-choice></data></im_field><im_field><field_number>5</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_date-time>01/24/17 12:14:00</data_date-time></data></im_field><im_field><field_number>6</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_text>none</data_text></data></im_field><im_field><field_number>7</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_auto_location>In Nashville, TN and 8.4m W of Hendersonville, TN On I-24 (Excellent GPS)</data_auto_location></data></im_field><im_field><field_number>8</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_auto_odometer>124614</data_auto_odometer><data_auto_odometer_plus_gps><data_performx_odometer>1246148</data_performx_odometer><data_gps_odometer>1193505</data_gps_odometer></data_auto_odometer_plus_gps></data></im_field></formdata>
<formdata><form_id>125685</form_id><im_field><field_number>1</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_text>Darrell</data_text></data></im_field><im_field><field_number>2</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_numeric>002</data_numeric></data></im_field><im_field><field_number>3</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_multiple-choice><mc_choicenum>2</mc_choicenum><mc_choicetext>No</mc_choicetext></data_multiple-choice></data></im_field><im_field><field_number>4</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_multiple-choice><mc_choicenum>2</mc_choicenum><mc_choicetext>No</mc_choicetext></data_multiple-choice></data></im_field><im_field><field_number>5</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_date-time>01/31/17 14:32:00</data_date-time></data></im_field><im_field><field_number>6</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_text>none</data_text></data></im_field><im_field><field_number>7</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_auto_location>Nashville, TN and In La Vergne, TN On I-24 (Excellent GPS)</data_auto_location></data></im_field><im_field><field_number>8</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_auto_odometer>126818</data_auto_odometer><data_auto_odometer_plus_gps><data_performx_odometer>1268185</data_performx_odometer><data_gps_odometer>1214506</data_gps_odometer></data_auto_odometer_plus_gps></data></im_field></formdata>
<formdata><form_id>125685</form_id><im_field><field_number>1</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_text>D</data_text></data></im_field><im_field><field_number>2</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_numeric>001</data_numeric></data></im_field><im_field><field_number>3</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_multiple-choice><mc_choicenum>1</mc_choicenum><mc_choicetext>Yes</mc_choicetext></data_multiple-choice></data></im_field><im_field><field_number>4</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_multiple-choice><mc_choicenum>2</mc_choicenum><mc_choicetext>No</mc_choicetext></data_multiple-choice></data></im_field><im_field><field_number>5</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_date-time>01/31/17 15:00:00</data_date-time></data></im_field><im_field><field_number>6</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_text>Someone will need to fix Dales broken heart after Saturday night</data_text></data></im_field><im_field><field_number>7</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_auto_location>Nashville, TN and In La Vergne, TN On I-24 (Excellent GPS)</data_auto_location></data></im_field><im_field><field_number>8</field_number><empty_at_start>yes</empty_at_start><driver_modified>yes</driver_modified><data><data_auto_odometer>181651</data_auto_odometer><data_auto_odometer_plus_gps><data_performx_odometer>1816512</data_performx_odometer><data_gps_odometer>4300425</data_gps_odometer></data_auto_odometer_plus_gps></data></im_field></formdata>
*/

//$rep=mrr_lockdown_all_truck_trailers_for_urgent_maint(0);
//echo $rep;

$myexp=mrr_pull_income_fuel_exp(573,562,"01/09/2017","01/20/2017");
echo "<br>Expenses=$".number_format($myexp,2).".<br>";


die("<br><br>DONE.");

//GPS Test.
$address="216 Parthenon Blvd, La Vergne, TN 37086";
$res = mrr_get_coordinates($address);
$lat=$res['lat'];
$long=$res['long'];
echo "<br><br>Address: '".$address."' | (<b>".$lat."</b> , <b>".$long."</b>)";

$res=mrr_get_coord_addr($lat,$long);
$numb=$res['numb'];
$addr=$res['addr'];
$city=$res['city'];
$state=$res['state'];
$zip=$res['zip'];
$cnty=$res['cnty'];
$usa=$res['usa'];

echo "<br>
	<br>Lat <b>".$lat."</b>, Long <b>".$long."</b>: Formal Address: <br>
	(".$numb.") ".$addr."<br>
	".$city.", ".$state." ".$zip."<br>
	".$usa." - ".$cnty.".<br>
";


$address="216 Parthenon Blvd";
$city="La Vergne";
$state="TN";
$zip="37086";
$res=mrr_get_promiles_gps_from_address($address,$city,$state,$zip);
	
echo "<br>Lookup using 'mrr_get_promiles_gps_from_address' function:<br>
	".$res['address']."<br>
	".$res['city'].", ".$res['state']." ".$res['zip']."<br>
	Lat <b>".$res['lat']."</b>, Long <b>".$res['long']."</b>.<br>
	Status:".$res['status'].". Error:".$res['error'].".<br>
";

$lat=$res['lat'];
$long=$res['long'];

$res=mrr_get_promiles_reverse_geocode_from_gps($lat,$long);

echo "<br>Reverse Lookup using mrr_get_promiles_reverse_geocode_from_gps' function:<br>
	".$res['address']."<br>
	".$res['city'].", ".$res['state']." ".$res['zip']."<br>
	Lat <b>".$res['lat']."</b>, Long <b>".$res['long']."</b>.<br>
	Status:".$res['status'].". Error:".$res['error'].".<br>
";


die("<br><br>DONE.");

$load_start=date("U");

$xref_id=547;
$tester=mrr_find_fed_inspection_last_completed(1,$xref_id);
echo "<br>Truck ".$xref_id."=".$tester.".";

$xref_id=14;
$tester=mrr_find_fed_inspection_last_completed(2,$xref_id);
echo "<br>Trailer ".$xref_id."=".$tester.".";

//$driver_id=575;
//$date_start="11/14/2016";
//$rep=mrr_backprocess_payroll_raise_dispatches($driver_id,$date_start,0);
//echo $rep;

//$start_date="2016-11-01";
//$end_date = "2016-11-07";
//$rep=mrr_purge_all_truck_odometer_readings($start_date,$end_date);
//echo $rep;


$load_end=date("U");
$load_time=$load_end - $load_start;

die("<br><br>Finished. Load Time: ".$load_start." - ".$load_end." = <b>".$load_time." seconds</b>.");
//$load_id=59795;
//echo "<br>Load ".$load_id." Invoice Creation: <br>";
//$rslt=sicap_create_invoice($load_id,0);
//echo "Result is ".$rslt.".";
//die("<br><br>Finished.");

		$email_tester=$defaultsarray['special_email_monitor'];
		$email_tester_name="Michael Richardson";
		
		$pdf="<br><h1>Test 1, Test 2, Test 3:</h1><p>This is just a test.</p>";
		$use_title="Test Email from Billing Report.";
		
		$user_name=$defaultsarray['company_name'];
		$From=$defaultsarray['company_email_address'];
		$Subject="";
		if(isset($use_title))			$Subject=$use_title;
		elseif(isset($usetitle))			$Subject=$use_title;
		
		$pdf=str_replace(" href="," name=",$pdf);
		//$pdf=str_replace("</a>","",$pdf);
			
		$sentit=mrr_trucking_sendMail($email_tester,$email_tester_name,$From,$user_name,'','',$Subject,$pdf,$pdf);
		
		$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
		echo "<br><br><b>This report has ".$sent_msg." to '".$email_tester_name."' at E-Mail address '".$email_tester."'.</b><br><br>";

die("<br><br>Finished.");

function mrr_find_all_replacement_trucks_for_truck($id,$date_start,$date_end)
{
	$list_cntr=0;
	$list_ids[0]=0;
	$list_names[0]="";
	$list_from[0]="";
	$list_to[0]="";
	$list_report="";
	
	if($id > 0)
	{
		$sql="
     		select equipment_history.*,
     			(select trucks.name_truck from trucks where trucks.id=equipment_history.equipment_id) as truck_name
     		from equipment_history
     		where equipment_history.deleted=0 
     			and equipment_history.replacement_xref_id='".sql_friendly($id)."'
     			and equipment_history.linedate_aquired <= '".date("Y-m-d", strtotime($date_end))." 23:59:59'
				and (equipment_history.linedate_returned = 0 or equipment_history.linedate_returned >= '".date("Y-m-d", strtotime($date_end))." 00:00:00')
     			and equipment_history.equipment_type_id = 1
     			and equipment_history.replacement = 1
     		order by equipment_history.linedate_aquired asc,
     			equipment_history.linedate_returned asc,
     			equipment_history.id desc		
     	";
     	$data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
          {
			$list_ids[$list_cntr]=$row['equipment_id'];
			$list_names[$list_cntr]=trim($row['truck_name']);
			
			$list_from[$list_cntr]="".date("m/d/Y",strtotime($row['linedate_aquired']))."";
			$list_to[$list_cntr]="".( $row['linedate_returned']!="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($row['linedate_returned']))."" : "Current")."";
			
          	$list_cntr++;          	
          	$list_report.="<br>
          		Equip ID=".$row['equipment_id'].", Truck ".trim($row['truck_name']).". 
          		
          		From ".date("m/d/Y",strtotime($row['linedate_aquired']))." To ".( $row['linedate_returned']!="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($row['linedate_returned']))."" : "Current").".
          	";	//  Xref=".$row['xref_id'].", ReplaceXref=".$row['replacement_xref_id'].", Replacement=".$row['replacement'].".      	
          }
	}	
	
	$res['num']=$list_cntr;
	$res['arr']=$list_ids;
	$res['names']=$list_names;
	
	$res['from']=$list_from;
	$res['to']=$list_to;
	
	$res['rep']=$list_report;
	
	return $res;
}

$id=269;
$date_start="04/01/2016";
$date_end="04/30/2016";

$res=mrr_find_all_replacement_trucks_for_truck($id,$date_start,$date_end);
$list_cntr=$res['num'];
$list_ids=$res['arr'];
$list_names=$res['names'];
$list_from=$res['from'];
$list_to=$res['to'];
$list_report=$res['rep'];

echo "<br>(".$date_start." - ".$date_end.")";
echo "<br><b>".$list_cntr." Replacement Truck(s) Found For Truck ".$id.":</b><br>";
for($i=0; $i < $list_cntr; $i++)
{	
	echo "<br>[".$list_ids[$i]."] ".$list_names[$i].".  (".$list_from[$i]." - ".$list_to[$i].")";	
}
echo "<br><br>REPORT:".$list_report."<br>";	

die("<br><br>Finished.");

$load_id=53355;		//53474
echo mrr_dispatch_completion_updates($load_id,1);
update_origin_dest($load_id);

die("<br><br>Finished.");

include('mrr_load_auto_saver.php');

echo "<br><br><a href='sql_mrr.php'>Reload</a><br><br>";

die("<br><br>Finished.");

$load_id=52715;
$tab=mrr_update_load_profit_setting($load_id,0);
echo $tab;

die("<br><br>Finished.");
/*
		$disp_driver_id=292;
		$rep=mrr_get_payroll_api_driver_settings($disp_driver_id);	
		echo $rep;
		
		$check_id=6904;
		$rep=mrr_get_payroll_api_driver_check_display($check_id);
		echo $rep;
		

die("<br><br>Finished.");
*/

function mrr_update_dispatch_labor_costs_from_date($driver_id,$date_from)
     {
     	if($driver_id==0 || $date_from=="")	return "";
     	
     	$tab="";
     	
     	$per_mile=0;
     	$per_hour=0;
     	$team_mile=0;
     	$team_hour=0;
     	$per_mile2=0;
     	$per_hour2=0;
     	$team_mile2=0;
     	$team_hour2=0;
     	
     	$sql2="
     		select * 
     		from drivers 
     		where id='".sql_friendly($driver_id)."'
		";
		$data2=simple_query($sql2);
		if($row2=mysqli_fetch_array($data2))
     	{
     		$per_mile=$row2['charged_per_mile'];
     		$per_hour=$row2['charged_per_hour'];
     		$team_mile=$row2['charged_per_mile_team'];
     		$team_hour=$row2['charged_per_hour_team'];
     		
     		$per_mile2=$row2['pay_per_mile'];
     		$per_hour2=$row2['pay_per_hour'];
     		$team_mile2=$row2['pay_per_mile_team'];
     		$team_hour2=$row2['pay_per_hour_team'];
     		
     		$tab.="
     			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
     			<tr style='font-weight:bold;'>						
						<td valign='top'>".$row2['id']."</td>
						<td valign='top' colspan='6'>".$row2['name_driver_first']." ".$row2['name_driver_last']."</td>
						<td valign='top' align='right' colspan='2'>Charged/Mile</td>
						<td valign='top' align='right' colspan='2'>Charged/Hour</td>
						<td valign='top' align='right' colspan='2'>Pay/Mile</td>
						<td valign='top' align='right' colspan='2'>Pay/Hour</td>
				</tr>
				<tr>						
						<td valign='top' colspan='7' align='right' style='font-weight:bold;'>Current Single Rates</td>
						<td valign='top' align='right' colspan='2'>$".number_format($row2['charged_per_mile'],2)."</td>
						<td valign='top' align='right' colspan='2'>$".number_format($row2['charged_per_hour'],2)."</td>
						<td valign='top' align='right' colspan='2'>$".number_format($row2['pay_per_mile'],2)."</td>
						<td valign='top' align='right' colspan='2'>$".number_format($row2['pay_per_hour'],2)."</td>
				</tr>
				<tr>						
						<td valign='top' colspan='7' align='right' style='font-weight:bold;'>Current Team Rates</td>
						<td valign='top' align='right' colspan='2'>$".number_format($row2['charged_per_mile_team'],2)."</td>
						<td valign='top' align='right' colspan='2'>$".number_format($row2['charged_per_hour_team'],2)."</td>
						<td valign='top' align='right' colspan='2'>$".number_format($row2['pay_per_mile_team'],2)."</td>
						<td valign='top' align='right' colspan='2'>$".number_format($row2['pay_per_hour_team'],2)."</td>
				</tr>
				<tr style='font-weight:bold;'>											
						<td valign='top'>Load</td>
						<td valign='top'>Dispatch</td>
						<td valign='top'>PickupETA</td>
						<td valign='top'>Truck</td>
						<td valign='top'>Trailer</td>
						<td valign='top'>Driver1</td>
						<td valign='top' align='right'>Labor/Mile</td>
						<td valign='top' align='right'>Labor/Hour</td>
						<td valign='top' align='right'>Pay/Mile</td>
						<td valign='top' align='right'>Pay/Hour</td>
						<td valign='top'>Driver2</td>
						<td valign='top' align='right'>Labor/Mile</td>
						<td valign='top' align='right'>Labor/Hour</td>
						<td valign='top' align='right'>Pay/Mile</td>
						<td valign='top' align='right'>Pay/Hour</td>				
				<tr>	
     		";
     		
     		   		
     		//now get dispatches with driver used...to update the rates...but only non-completed dispatches.     		
     		$sql="
     			select id,
     				load_handler_id,
     				(select trucks.name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_name,
     				(select trailers.trailer_name from trailers where trailers.id=trucks_log.trailer_id) as trailer,
     				driver_id,
     				driver2_id,
     				labor_per_hour,
     				labor_per_mile,
     				driver_2_labor_per_hour,
     				driver_2_labor_per_mile,
     				
     				driver1_pay_per_hour,
     				driver1_pay_per_mile,
     				driver2_pay_per_hour,
     				driver2_pay_per_mile,
     				
     				linedate_pickup_eta
     				
     			from trucks_log 
     			where deleted=0 
     				and dispatch_completed=0
     				and (driver_id='".sql_friendly($driver_id)."' or driver2_id='".sql_friendly($driver_id)."')
     				and linedate_pickup_eta>='".date("Y-m-d",strtotime($date_from))." 00:00:00'
     			order by linedate_pickup_eta asc
			";
			$data=simple_query($sql);
			while($row=mysqli_fetch_array($data))
			{
				$position="";
				$position2="";
				if($row['driver_id']==$driver_id)
				{	//update driver 1 slot
					$position="Driver1[".$row['driver_id']."]";
				}
				elseif($row['driver2_id']==$driver_id)
				{	//
					$position2="Driver2[".$row['driver2_id']."]";
				}
				if($row['driver2_id']==0)	$position2="";	
				
				$tab.="
					<tr>						
						<td valign='top'>".$row['load_handler_id']."</td>
						<td valign='top'>".$row['id']."</td>
						<td valign='top'>".date("Y-m-d H:i",strtotime($row['linedate_pickup_eta']))."</td>
						<td valign='top'>".$row['truck_name']."</td>
						<td valign='top'>".$row['trailer']."</td>
						<td valign='top'>".$position."</td>						
						<td valign='top' align='right'>$".number_format($row['labor_per_mile'],2)."</td>
						<td valign='top' align='right'>$".number_format($row['labor_per_hour'],2)."</td>
						<td valign='top' align='right'>$".number_format($row['driver1_pay_per_mile'],2)."</td>
						<td valign='top' align='right'>$".number_format($row['driver1_pay_per_hour'],2)."</td>
						<td valign='top'>".$position2."</td>
						<td valign='top' align='right'>$".number_format($row['driver_2_labor_per_mile'],2)."</td>
						<td valign='top' align='right'>$".number_format($row['driver_2_labor_per_hour'],2)."</td>						
						<td valign='top' align='right'>$".number_format($row['driver2_pay_per_mile'],2)."</td>
						<td valign='top' align='right'>$".number_format($row['driver2_pay_per_hour'],2)."</td>
					</tr>
				";
			}
			$tab.="</table>";
     	}
     	     	
     	return $tab;
     }



$date_from="12/28/2015";
    
$driver_id=292;			//Daryl Eaddy
//$date_from="11/24/2015";
echo mrr_update_dispatch_labor_costs_from_date($driver_id,$date_from);

$driver_id=416;			//Steve Gannon
//$date_from="12/20/2015";
echo mrr_update_dispatch_labor_costs_from_date($driver_id,$date_from);


die("<br><br>Finished.");

/* 
//CSV file working...
$list = array (
    array('aaa', 'bbb', 'ccc', 'dddd'),
    array('123', '456', '789'),
    array('"aaa"', '"bbb"')
);
$fp = fopen('peachtree_import.csv', 'w');
foreach ($list as $fields) {    fputcsv($fp, $fields);	}
fclose($fp);

//....................peachtree_import.csv file contents of code above would be........................\\
aaa,bbb,ccc,dddd
123,456,789
"""aaa""","""bbb"""
\\.....................................................................................................//
*/
$employer_id=0;
$acct_code="Checking101";
$gl_chart="90100-OH";
$method="1=Cash";
//each entry should have 17 fields in the array...even if they are blank strings.

$import_name="";	//'Chris Sherrod'
$results=mrr_get_peachtree_import_name("Chris","Sherrod");
foreach($results as $key => $value )
{
	$prt=trim($key);		$tmp=trim($value);
	if($prt=="ImportName")	$import_name=$tmp;
}


$payroll_array=array (
    array('Michael Richardson', '151 Heritage Park Dr', 'Suite 301', 'Murfreesboro','TN','37062','','10001',date("m/d/Y"), 'Payroll for MRR',$acct_code,'','1','Payroll Period for Blah Blah',$gl_chart,'2000.00',$method),
    array($import_name, '151 Heritage Park Dr', 'Suite 301', 'Murfreesboro','TN','37062','','10002',date("m/d/Y"), 'Payroll for CS',$acct_code,'','1','Payroll Period for Blah Blah',$gl_chart,'3000.00',$method),
    array('James Holloway', '151 Heritage Park Dr', 'Suite 301', 'Murfreesboro','TN','37062','','10003',date("m/d/Y"), 'Payroll for JH',$acct_code,'','1','Payroll Period for Blah Blah',$gl_chart,'1000.00',$method)
);
$res_file=mrr_csv_payroll_export_file($employer_id,"",$payroll_array);
echo "<br>
	<br>Export File is <a href='".$res_file['public_path']."' target='_blank'>".$res_file['public_path']."</a>. 
	<br>Full Path is ".$res_file['direct_path'].".
	<br>Lines Added=".$res_file['lines_added'].".
	<br><div width='1400'>".$res_file['html']."</div><br>
";

die("<br><br>Finished.");

//trailer update section
$sql = "
	select *
	
	from
	trailers
	where trailer_name='5321'
	order by trailer_name asc, id asc
";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) 
{
	if(is_numeric($row['trailer_name'])) 
	{	// && $row['trailer_name']=='464694'
		echo "$row[trailer_name]<br>";
		
		$trailer_name = $row['trailer_name'];
		
		$old_name = $trailer_name;
		
		$trailer_name=mrr_make_numeric2($trailer_name);
		
		update_coa_mrr_link("",$trailer_name);
	}
}
die("<br><br>Finished.");
/*
//truck update section		and name_truck<'132809'
$sql = "
	select *	
	from	trucks
	where deleted > 0

	order by name_truck asc, id asc
";
$data = simple_query($sql);

$api = new sicap_api_connector();
$income_id = $api->getChartTypeIDByName("Income");		
$cost_of_sales_id = $api->getChartTypeIDByName("Cost of Sales");

$old_name = '';

$ignore_int_name=1;

while($row = mysqli_fetch_array($data)) 
{
	if(is_numeric($row['name_truck']) ) 
	{	// && $row['name_truck']=='464694'
		echo "$row[name_truck]<br>";
		
		$truck_name = $row['name_truck'];
		
		$old_name = $truck_name;
		
		$truck_name=mrr_make_numeric2($truck_name);
		
		update_coa_mrr_link($truck_name,"");
	}	
}


die("<br><br>Finished.");
*/
/*
//truck update section
$sql = "
	select *	
	from	trucks
	where deleted = 0
		and name_truck='126031'
	order by name_truck asc, id asc
";
$data = simple_query($sql);

$api = new sicap_api_connector();
$income_id = $api->getChartTypeIDByName("Income");		
$cost_of_sales_id = $api->getChartTypeIDByName("Cost of Sales");

$old_name = '';

$ignore_int_name=1;

while($row = mysqli_fetch_array($data)) 
{
	if(is_numeric($row['name_truck']) || $ignore_int_name==1) 
	{	// && $row['name_truck']=='464694'
		echo "$row[name_truck]<br>";
		
		$truck_name = $row['name_truck'];
		$truck_id = $row['id'];
		$rental=$row['rental'];
		$rent_lab="";
		if($rental > 0)
		{
			$rent_lab=" (Rental)";
			if(trim($row['leased_from'])!="")		$rent_lab=" (Rental) ".$row['leased_from']."";
		}
		$old_name = $truck_name;
		
		$truck_name=mrr_make_numeric2($truck_name);
		
		$active=$row['active'];
		
		update_coa("Income-Truck #$truck_name".$rent_lab."", "41000-$truck_name", $income_id, ($old_name != '' ? "Income-Truck #$old_name" : ""),$active,$truck_name,"");
		update_coa("Discount-Truck #$truck_name".$rent_lab."", "46000-$truck_name", $income_id, ($old_name != '' ? "Discount-Truck #$old_name" : ""),$active,$truck_name,"");
		update_coa("Fuel - #$truck_name".$rent_lab."", "58800-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Fuel - #$old_name" : ""),$active,$truck_name,"");
		update_coa("Layover Expense - #$truck_name".$rent_lab."", "65000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Layover Expense - #$old_name" : ""),$active,$truck_name,"");
		update_coa("Lease Drivers - #$truck_name".$rent_lab."", "67000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lease Drivers - #$old_name" : ""),$active,$truck_name,"");
		update_coa("Lumper #$truck_name".$rent_lab."", "68270-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lumper #$old_name" : ""),$active,$truck_name,"");
		update_coa("Truck Repairs & Maint - #$truck_name".$rent_lab."", "74500-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Repairs & Maint - #$old_name" : ""),$active,$truck_name,"");

          update_coa("Truck Tires - #$truck_name".$rent_lab."", "74510-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Tires - #$old_name" : ""),$active,$truck_name,"");
                     
		update_coa("Stop off - #$truck_name".$rent_lab."", "75500-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Stop off - #$old_name" : ""),$active,$truck_name,"");
		
		update_coa("Lease Drivers Panther Bonus - #$truck_name".$rent_lab."", "67100-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lease Drivers Panther Bonus - #$old_name" : ""),$active,$truck_name,"");
		
		if($rental > 0)
		{			
			update_coa("Truck Rental - Fixed - #$truck_name".$rent_lab."", "77950-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Rental - Fixed - #$old_name" : ""),$active,$truck_name,"");
		}
		else
		{
			update_coa("Truck Lease Fixed - #$truck_name".$rent_lab."", "78000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Lease Fixed - #$old_name" : ""),$active,$truck_name,"");
		}
		update_coa("Truck Rental Mileage Exp - #$truck_name".$rent_lab."", "78050-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Rental Mileage Exp - #$old_name" : ""),$active,$truck_name,"");
		update_coa("Truck Lease Mileage Exp - #$truck_name".$rent_lab."", "78100-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Lease Mileage Exp - #$old_name" : ""),$active,$truck_name,"");
		update_coa("Weigh Ticket Expense - #$truck_name".$rent_lab."", "79000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Weigh Ticket Expense - #$old_name" : ""),$active,$truck_name,"");
		update_coa("Truck Accidents - #$truck_name".$rent_lab."", "74000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Accidents - #$old_name" : ""),$active,$truck_name,"");
		
		update_coa("Truck Cleaning - #$truck_name".$rent_lab."", "74900-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Cleaning - #$old_name" : ""),$active,$truck_name,"");
		
		$inventory_item_id = 0;
		
		$chart_id_sales = $api->getChartIDByName("Truck Repairs - #$truck_name");
		$chart_id_inv = $chart_id_sales;
		$chart_id_cost = $chart_id_sales;
		
		$api->clearParams();
		$api->command = "update_inventory_item";
		$api->addParam("ItemName", $truck_name."-Truck Repairs");
		$api->addParam("ItemID",$inventory_item_id);
		$api->addParam("ChartIDSales", $chart_id_sales);
		$api->addParam("Active", $active);
		$api->addParam("ChartIDInventory", $chart_id_inv);
		$api->addParam("ChartIDCOGS", $chart_id_cost);
		
		$api->execute();		
	}
}
die("<br><br>Finished.");


//trailer update section
$sql = "
	select *
	
	from
	trailers
	where deleted = 0
	order by trailer_name asc, id asc
";
$data = simple_query($sql);

$api = new sicap_api_connector();
$income_id = $api->getChartTypeIDByName("Income");		
$cost_of_sales_id = $api->getChartTypeIDByName("Cost of Sales");

$old_name = '';

$ignore_int_name=1;

while($row = mysqli_fetch_array($data)) 
{
	if(is_numeric($row['trailer_name']) || $ignore_int_name==1) 
	{	// && $row['trailer_name']=='464694'
		echo "$row[trailer_name]<br>";
		
		$trailer_name = $row['trailer_name'];
		$truck_id = $row['id'];
		$rental=$row['rental_flag'];
		$rent_lab="";
		if($rental > 0)
		{
			$rent_lab=" (R) Rental";
			if(trim($row['trailer_owner'])!="")		$rent_lab=" (L) ".$row['trailer_owner']."";
		}
		$old_name = $trailer_name;
		
		$trailer_name=mrr_make_numeric2($trailer_name);
		
		$active=$row['active'];
			
		////sicap_update_trucks($row['id'], $truck_name, $quick_update = false, $rental = 0)
		
		update_coa("Trailer Repairs & Maint - #$trailer_name".$rent_lab."", "77500-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Repairs & Maint - #$old_name" : ""),$active,"",$trailer_name);			
		update_coa("Tires #$trailer_name".$rent_lab."", "77600-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Tires #$old_name" : ""),$active,"",$trailer_name);
		update_coa("Trailer Wash - #$trailer_name".$rent_lab."", "77800-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Wash - #$old_name" : ""),$active,"",$trailer_name);
		update_coa("Trailer Accidents - #$trailer_name".$rent_lab."", "77485-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Accidents - #$old_name" : ""),$active,"",$trailer_name);
		
		update_coa("Trailer Mileage Expenses - #$trailer_name".$rent_lab."", "77475-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Mileage Expenses - #$old_name" : ""),$active,"",$trailer_name);
		
		update_coa("Truck Cleaning - #$truck_name".$rent_lab."", "74900-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Cleaning - #$old_name" : ""),$active,"",$trailer_name);
		
		$inventory_item_id = 0;
		
		$chart_id_sales = $api->getChartIDByName("Trailer Repairs - #$trailer_name");
		$chart_id_inv = $chart_id_sales;
		$chart_id_cost = $chart_id_sales;
		
		$api->clearParams();
		$api->command = "update_inventory_item";
		$api->addParam("ItemName", $trailer_name."-Trailer Repairs");
		$api->addParam("ItemID",$inventory_item_id);
		$api->addParam("ChartIDSales", $chart_id_sales);
		$api->addParam("Active", $active);
		$api->addParam("ChartIDInventory", $chart_id_inv);
		$api->addParam("ChartIDCOGS", $chart_id_cost);
		
		$api->execute();
		
	}
}
die("<br><br>Finished.");


$api = new sicap_api_connector();
$income_id = $api->getChartTypeIDByName("Income");		
$cost_of_sales_id = $api->getChartTypeIDByName("Cost of Sales");

		$inventory_item_id = 0;
		
		$chart_id_sales = $api->getChartIDByName("Trailer Repairs & Maint - EXTRA");
		$chart_id_inv = $chart_id_sales;
		$chart_id_cost = $chart_id_sales;
		
		$api->clearParams();
		$api->command = "update_inventory_item";
		$api->addParam("ItemName","IPCC-Trailer Repairs");
		$api->addParam("ItemID",$inventory_item_id);
		$api->addParam("ChartIDSales", $chart_id_sales);
		$api->addParam("Active", 1);
		$api->addParam("ChartIDInventory", $chart_id_inv);
		$api->addParam("ChartIDCOGS", $chart_id_cost);
		
		$api->execute();

die("<br><br>Finished.");



*/
die("<br><br>Finished.");


//echo mrr_pn_driver_dot_list();		//0=fetch from PN, 1= test mode....grab from test file.

echo mrr_pn_driver_dot_list_v2(0);		//Driver ID
die("<br><br>Finished.");

$mrr_from_date="2015-01-01 00:00:00";
$mrr_to_date="2015-01-31 23:59:59";

$search_date_range = "
				and load_handler.linedate_pickup_eta >= '".$mrr_from_date."'
				and load_handler.linedate_pickup_eta < '".$mrr_to_date."'
			";
$search_date_range2 = "
				and trucks_log.linedate_pickup_eta >= '".$mrr_from_date."'
				and trucks_log.linedate_pickup_eta < '".$mrr_to_date."'
			";

//https://wwws003.pfmlogin.com/pfm-main/main/index

$total_sales=0;			
$load_tot=0;
$dispatch_tot=0;
			
$sql = "
			select DISTINCT(load_handler.id) AS mrr_unique_id,
				load_handler.*,
				customers.name_company,
				(load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount) as actual_bill_customer,
				load_handler.actual_bill_customer - load_handler.actual_total_cost as load_profit,
				(select ifnull(sum(trucks_log.loaded_miles_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as loaded_miles_hourly,
				(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
				(select ifnull(sum(trucks_log.driver2_id),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as driver_cnt,
				(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead			
			from load_handler,customers,trucks_log
			where load_handler.deleted = 0
				and trucks_log.deleted = 0	
				and customers.id = load_handler.customer_id
				and trucks_log.load_handler_id = load_handler.id		
				".$search_date_range."
			order by load_handler.linedate_pickup_eta asc,load_handler.id asc
		";
//$mrr_capture_sql=$sql;
$data = simple_query($sql);
while($row = mysqli_fetch_array($data))
{
	$total_sales += $row['actual_bill_customer'];
}

$load_tot=$total_sales;
$total_sales=0;

/*
$sql = "
			select DISTINCT(load_handler.id) AS mrr_unique_id,
				load_handler.*,
				customers.name_company,
				(load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount) as actual_bill_customer,
				load_handler.actual_bill_customer - load_handler.actual_total_cost as load_profit,
				(select ifnull(sum(trucks_log.loaded_miles_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as loaded_miles_hourly,
				(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
				(select ifnull(sum(trucks_log.driver2_id),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as driver_cnt,
				(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead			
			from load_handler,customers,trucks_log
			where load_handler.deleted = 0
				and trucks_log.deleted = 0	
				and customers.id = load_handler.customer_id
				and trucks_log.load_handler_id = load_handler.id		
				".$search_date_range2."
			order by load_handler.linedate_pickup_eta asc,load_handler.id asc
		";
//$mrr_capture_sql=$sql;
$data = simple_query($sql);
while($row = mysqli_fetch_array($data))
{
	$total_sales += $row['actual_bill_customer'];
}
$dispatch_tot=$total_sales;
*/

$net_profit_inc=0;
$net_profit_inc_list="";
$net_profit_inc_list2="";
$mrr_from_date="2015-01-01";
$mrr_to_date="2015-01-31";

$results=mrr_fetch_comparison_data_alt(99,$mrr_from_date,$mrr_to_date, '0' ,'99999');	//income	
foreach($results as $key => $value )
{
  	$prt=trim($key);			$tmp=trim($value);
   	if($prt=="Comparison")	{	$net_profit_inc+=(float)$tmp;		$net_profit_inc_list.="<br>".$prt." : $".number_format((float)$tmp,2).".";		}
   	if($prt=="invChart")	{	$net_profit_inc_list2.="<br><div>".$prt." :<br>".$tmp."<br>END</div>";		}
}


echo "<br>Sales By Load: $".number_format($load_tot,2)."";
//echo "<br>Sales By Dispatch: $".number_format($dispatch_tot,2)."";
echo "<br>Net Income (acct): $".number_format($net_profit_inc,2)."";
echo "<br><hr><br>";
//echo "<br>Load - Disp Sales: $".number_format(($load_tot - $dispatch_tot),2)."";
echo "<br>Load - Acct Sales: $".number_format(($load_tot - $net_profit_inc),2)."";

echo "<br><hr><br>".$net_profit_inc_list2."<br>";

die("<br><br>Finished.");

$res=mrr_compute_load_tracking_avgs("05/01/2015","05/05/2015",1423);

echo "<table cellpadding='0' cellspacing='0' border='0' width='1200'>
		<tr><td>".$res['tab']."</td></tr>
		<tr><td>".$res['sql']."</td></tr>
	</table>";

die("<br>Finished.");

echo mrr_auto_calculate_surcharge(1584);

die("<br>Finished.");

$log_db=mrr_find_log_database_name();
echo "Log Database Prefix=<b>".$log_db."</b>.<br>";

die("<br>Finished.");

$truck_id=0;
$load_id=0;	//41316
$disp_id=0;
$stop_id=0;

$tab=mrr_compare_truck_location_with_current_stops($truck_id,$load_id,$disp_id,$stop_id);

echo "Stop List...TruckID=".$truck_id." | LoadID=".$load_id." | DispID=".$disp_id." | StopID=".$stop_id.":<br><br>".$tab."<br>";

die("<br>Finished...");




$dstart="11/01/2014";
$dend="11/30/2014";
$date_start=strtotime($dstart);
$date_end=strtotime($dend);
$billable=0;

$mrr_holidays=mrr_get_holiday_option_list();

echo "<br><b>Billable Days from ".$dstart." to ".$dend.":</b><br>";

for($i=0;$i< 1100;$i++) 
{
	if(strtotime("$i day", $date_start) > $date_end) break;
	//echo date("m/d/Y", $date_start)."<br>";
	$day_of_week = date("w", strtotime("$i day", $date_start));			
	
	$is_holiday=in_array("".date("Y-m-d", strtotime("$i day", $date_start))."" , $mrr_holidays);			
	
	if($day_of_week != 0 && $day_of_week != 6 && $is_holiday==false) 
	{
		$billable++;
		
		echo "<br>".$billable." -- ".date("D, m/d/Y", strtotime("$i day", $date_start))." is billable.";
	}	
	elseif($day_of_week != 0 && $day_of_week != 6)
	{
		echo "<br><b>Holiday!</b> -- ".date("D, m/d/Y", strtotime("$i day", $date_start))."";	
	}
}
echo "<br><br><b>".$billable." Total Billable Days.</b><br>";

die("<br>Finished...");



$res=mrr_get_last_stop_deadhead(39326);

echo "<br><b>Miles:</b> ".$res['miles']."";
echo "<br><b>Last Stop: </b> ".$res['last_stop']."";

die("<br>Finished...");


//code to update the fuel surcharges 
$today=date("Y-m-d")." 00:00:00";
$new_fuel=$defaultsarray['fuel_surcharge'];

echo "
<b>TEST CASE:</b><br>
Assumes Loads starting from ".$today.", 
Customer uses the fuel surcharge ranges, 
and that the new rate is going to be $".$new_fuel." from this point on...until the next change that is.<br>
<br>
<hr>
<br>
Loads that should be updated:<br>
";

$tab=mrr_update_load_fuel_surcharges($new_fuel,$today);
echo $tab;

die("<br>Finished...");

//truck update section
$sql = "
	select *	
	from	trucks
	where deleted >= 0
		and name_truck>'636421'
	order by name_truck asc, id asc
";
$data = simple_query($sql);

$api = new sicap_api_connector();
$income_id = $api->getChartTypeIDByName("Income");		
$cost_of_sales_id = $api->getChartTypeIDByName("Cost of Sales");

$old_name = '';

$ignore_int_name=1;

while($row = mysqli_fetch_array($data)) 
{
	if(is_numeric($row['name_truck']) || $ignore_int_name==1) 
	{	// && $row['name_truck']=='464694'
		echo "$row[name_truck]<br>";
		
		$truck_name = $row['name_truck'];
		$truck_id = $row['id'];
		$rental=$row['rental'];
		$rent_lab="";
		if($rental > 0)
		{
			$rent_lab=" (Rental)";
			if(trim($row['leased_from'])!="")		$rent_lab=" (Rental) ".$row['leased_from']."";
		}
		$old_name = $truck_name;
		
		$active=$row['active'];
			
		//sicap_update_trucks($row['id'], $truck_name, $quick_update = false, $rental = 0)
		
		update_coa("Income-Truck #$truck_name".$rent_lab."", "41000-$truck_name", $income_id, ($old_name != '' ? "Income-Truck #$old_name" : ""),$active);
		update_coa("Discount-Truck #$truck_name".$rent_lab."", "46000-$truck_name", $income_id, ($old_name != '' ? "Discount-Truck #$old_name" : ""),$active);
		update_coa("Fuel - #$truck_name".$rent_lab."", "58800-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Fuel - #$old_name" : ""),$active);
		update_coa("Layover Expense - #$truck_name".$rent_lab."", "65000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Layover Expense - #$old_name" : ""),$active);
		update_coa("Lease Drivers - #$truck_name".$rent_lab."", "67000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lease Drivers - #$old_name" : ""),$active);
		update_coa("Lumper #$truck_name".$rent_lab."", "68270-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lumper #$old_name" : ""),$active);
		update_coa("Truck Repairs & Maint - #$truck_name".$rent_lab."", "74500-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Repairs & Maint - #$old_name" : ""),$active);
		update_coa("Stop off - #$truck_name".$rent_lab."", "75500-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Stop off - #$old_name" : ""),$active);
		
		update_coa("Lease Drivers Panther Bonus - #$truck_name".$rent_lab."", "67100-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lease Drivers Panther Bonus - #$old_name" : ""),$active);
		
		if($rental > 0)
		{			
			update_coa("Truck Rental - Fixed - #$truck_name".$rent_lab."", "77950-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Rental - Fixed - #$old_name" : ""),$active);
		}
		else
		{
			update_coa("Truck Lease Fixed - #$truck_name".$rent_lab."", "78000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Lease Fixed - #$old_name" : ""),$active);
		}
		update_coa("Truck Rental Mileage Exp - #$truck_name".$rent_lab."", "78050-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Rental Mileage Exp - #$old_name" : ""),$active);
		update_coa("Truck Lease Mileage Exp - #$truck_name".$rent_lab."", "78100-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Lease Mileage Exp - #$old_name" : ""),$active);
		update_coa("Weigh Ticket Expense - #$truck_name".$rent_lab."", "79000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Weigh Ticket Expense - #$old_name" : ""),$active);
		update_coa("Truck Accidents - #$truck_name".$rent_lab."", "74000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Accidents - #$old_name" : ""),$active);
		
		update_coa("Truck Cleaning - #$truck_name".$rent_lab."", "74900-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Cleaning - #$old_name" : ""),$active);
		
		$inventory_item_id = 0;
		
		$chart_id_sales = $api->getChartIDByName("Truck Repairs - #$truck_name");
		$chart_id_inv = $chart_id_sales;
		$chart_id_cost = $chart_id_sales;
		
		$api->clearParams();
		$api->command = "update_inventory_item";
		$api->addParam("ItemName", $truck_name."-Truck Repairs");
		$api->addParam("ItemID",$inventory_item_id);
		$api->addParam("ChartIDSales", $chart_id_sales);
		$api->addParam("Active", $active);
		$api->addParam("ChartIDInventory", $chart_id_inv);
		$api->addParam("ChartIDCOGS", $chart_id_cost);
		//$api->show_output = true;
		$api->execute();
		
	}
}
die("Finished...");

/*
$truck_id=332;
$trailer_id=255; 

$truck_cost = mrr_get_truck_cost($truck_id);
$trailer_cost = mrr_get_trailer_cost($trailer_id);

$truck_cost0 = mrr_get_truck_cost(0);
$trailer_cost0 = mrr_get_trailer_cost(0);

$daily_cost1 = get_daily_cost(0,0);
$daily_cost2 = get_daily_cost($truck_id,0);
$daily_cost3 = get_daily_cost($truck_id,$trailer_id);

//echo "<br>Truck Cost0 ".$truck_cost0."<br>";
echo "<br>Trailer Cost0 ".$trailer_cost0."<br>";

//echo "<br>Truck Cost ".$truck_cost."<br>";
echo "<br>Trailer Cost ".$trailer_cost."<br>";


//echo "<br>Daily Cost1 ".$daily_cost1."<br>";

echo "<br>Daily Cost2 ".$daily_cost2." (truck only)<br>";

echo "<br>Daily Cost3 ".$daily_cost3." (truck and Trialer)<br>";


die('<br><br>...Done');
*/

/*
$from = "Fairview, TN 37062";
$to = "Franklin, TN 37064";

$from = urlencode($from);
$to = urlencode($to);

$datad = file_get_contents("http://maps.googleapis.com/maps/api/distancematrix/json?origins=$from&destinations=$to&language=en-EN&sensor=false");
$datad = json_decode($datad);

$time = 0;
$distance = 0;

foreach($datad->rows[0]->elements as $road) 
{
    $time += $road->duration->value;
    $distance += $road->distance->value;
}

$minutes=$time/60;
$hours=$time/(60*60);

$kmeters=$distance/1000;

$miles=$kmeters * 0.621371;

echo "To: ".$datad->destination_addresses[0];
echo "<br/>";
echo "From: ".$datad->origin_addresses[0];
echo "<br/>";
echo "Time: ".$time." seconds... ".number_format($minutes,2)." minutes... ".number_format($hours,2)." hours.";
echo "<br/>";
echo "Distance: ".$distance." meters... ".number_format($kmeters,2)." KM... ".number_format($miles,2)." miles.";

die('<br>...Done<br>');
*/

$mrr_debug_time_start=time();		//for page load speed checks...used at bottom of the page.

$legs=4;

//$gps[0]['lat']="35.979998";	$gps[0]['long']="-87.120002";	
$gps[0]['lat']="0";			$gps[0]['long']="0";
$gps[0]['city']="";			$gps[0]['state']="";	$gps[0]['zip']="37062";	
	

//$gps[1]['lat']="35.919998";	$gps[1]['long']="-86.860010";	
$gps[1]['lat']="0";			$gps[1]['long']="0";		
$gps[1]['city']="";			$gps[1]['state']="";	$gps[1]['zip']="37064";	


//$gps[2]['lat']="36.089994";	$gps[2]['long']="-86.850001";	
$gps[2]['lat']="0";			$gps[2]['long']="0";		
$gps[2]['city']="";			$gps[2]['state']="";	$gps[2]['zip']="37221";	


//$gps[3]['lat']="36.208096";	$gps[3]['long']="-86.291105";	
$gps[3]['lat']="0";			$gps[3]['long']="0";		
$gps[3]['city']="Lebanon";	$gps[3]['state']="TN";	$gps[3]['zip']="";	


echo "<br><b>Test Page Data Given for ".$legs." Stops</b><br>";

for($i=0; $i < $legs; $i++)
{
	echo "Stop ".$i.":  ".$gps[$i]['city'].",  ".$gps[$i]['state']."   ".$gps[$i]['zip']." (".$gps[$i]['lat'].",".$gps[$i]['long'].")<br>";
}

//echo "<br><hr><br>";

$mres=mrr_promiles_get_file_contents_runtrip($legs,$gps);
/*
	$mres['tot_miles']=$miles;
	$mres['legs']=$legs;
	$mres['miles']=$arr;
	$mres['times']=$tarr;
	$mres['city']=$city;
	$mres['state']=$state;
	$mres['zip']=$zip;
	$mres['directions']
	$mres['truckstops']
*/	

echo "<br><b>ProMiles Results</b><br>";

$minutes=0;
for($i=0; $i < $mres['legs']; $i++)
{
	echo "<br>-----Stop ".$i." <b>".$mres['miles'][$i]." Miles.</b> ".$mres['times'][$i]." Minutes. Location is ".$mres['city'][$i].", ".$mres['state'][$i]." ".$mres['zip'][$i]."<br>";	
	$minutes+=$mres['times'][$i];
}
echo "<br><b>".$mres['tot_miles']." Total Miles.  Total Time is ".$minutes." Minutes (".number_format(($minutes / 60),2)." Hrs)</b><br>";

//echo "<br><div style='width:1200px; border:solid blue 1px; margin:10px; padding:10px;'><center>Turn-By-Turn Directions:</center>".$mres['directions']."</div></br>";

//echo "<br><div style='width:1200px; border:solid blue 1px; margin:10px; padding:10px;'><center>Truck Stops In Route:</center>".$mres['truckstops']."</div></br>";




$mrr_debug_time_end=time();
echo "
	<br><b>PHP Page Load:</b>
	<br>Start Time: ".$mrr_debug_time_start."
	<br>End Time: ".$mrr_debug_time_end."
	<br>Load Time: ".number_format(($mrr_debug_time_end - $mrr_debug_time_start),4)." Seconds.
	<br><div id='ajax_time_keeper'></div>
";
die('<br>...<br>Done.<br>');

//$mycity="Fairview";
//$mystate="TN";
//$myzip="37062";

$mycity="37062";
$mystate="";
$myzip="";

$calc_res="";

$res=mrr_get_promiles_gps_from_address('',trim($mycity),trim($mystate),trim($myzip));
if($res['status']==1)
{	
	$calc_res.="GPS From Address: ".$res['city'].", ".$res['state']." ".$res['zip']." (".$res['lat'].",".$res['long'].")<br>Address From GPS: ";
	
	$testing_lat=$res['lat'];
	$testing_long=$res['long'];
	
	if(strlen($res['lat']) > 10)		$testing_lat=substr($res['lat'],0,7);
	if(strlen($res['long']) > 10)		$testing_long=substr($res['long'],0,7);
	
	
	$gps_res=mrr_find_zip_code_from_gps($testing_lat,$testing_long);
	if(trim($gps_res['city'])!="" && trim($gps_res['state'])!="" && trim($gps_res['zip'])!="")
	{
		$calc_res.="(1). ".$gps_res['city'].", ".$gps_res['state']." ".$gps_res['zip']." (".$testing_lat.",".$testing_long.")";
	}
	else
	{
		$gps_res2=mrr_get_promiles_reverse_geocode_from_gps($testing_lat,$testing_long);
		$calc_res.="(2). ".$gps_res2['city'].", ".$gps_res2['state']." ".$gps_res2['zip']." (".$testing_lat.",".$testing_long.")";
	}
}
if(trim($res['error'])!="")
{
	$calc_res.=" ...Geocode Error [".trim($res['error'])."]...  ";
}

echo "City:<span style='color:green;'>".$mycity."</span>, State:<span style='color:green;'>".$mystate."</span>, Zip:<span style='color:green;'>".$myzip."</span> Returns <span style='color:red;'><b>".$calc_res."</b></span><br>";


die('<br>...<br>Done.<br>');



//Driver info...
		$dot_summary="";
		$driver_id=440;
		$from_date="4/06/2014";
		$to_date="4/19/2014";
		
		$dres=mrr_find_driver_dot_hrs_for_planning_mrr($driver_id,$from_date,$to_date);
     	//$dres['violation_l0_hr']=$tot_10_brk;		//...this one is implied by the 11-hr rule and the 14-hr rule
     	//$dres['violation_34_hr']=$tot_34_brk;		//...this one is implied by the 70-hr rule
     	
     	$report_from=str_replace("/","_",$from_date);
     	$report_to=str_replace("/","_",$to_date);
     	$report_url="report_driver_safety.php?driver_id=".$driver_id."&date_from=".$report_from."&date_to=".$report_to."";
     	
     	$days=mrr_compute_days_diff_by_dates($from_date,$to_date);
		$days++;											//+1 is to allow the end date to be counted in the difference..  20140227 - 20140201 = 26, but the first does count... making it 27 for the range.
	
     	
		$dot_summary="
		Calendar Days: ".$days.".<br>
		<table cellpadding='2' cellspacing='2' border=1>
		<tr>
			<td valign='top' align='left'><a href='".$report_url."' target='_blank'><b>DOT Violations</b></a></td>
			<td valign='top' colspan='3' align='center'><b>(".$from_date." -- ".$to_date.")</b></td>
		</tr>
		<tr>
			<td valign='top' colspan='3'><b>11-Hr Driving (per 10hrs Rest Break)</b></td>
			<td valign='top' align='right'>".number_format($dres['violation_ll_hr'],2)."</td>
		</tr>
		<tr>
			<td valign='top' colspan='3'><b>14-Hr Working (per 10hrs Rest Break)</b></td>
			<td valign='top' align='right'>".number_format($dres['violation_l4_hr'],2)."</td>
		</tr>
		<tr>
			<td valign='top' colspan='3'><b>70-Hr Driving/Working (per 34hrs Rest Break)</b></td>
			<td valign='top' align='right'>".number_format($dres['violation_70_hr'],2)."</td>
		</tr>
		<tr>
			<td valign='top' colspan='3'><b>System Speeding Violations</b></td>
			<td valign='top' align='right'>".number_format($dres['speeding'],2)."</td>
		</tr>
		<tr>
			<td valign='top' colspan='3'><b>DOT Violation Marks</b></td>
			<td valign='top' align='right'>".number_format($dres['violations_dot'],2)."</td>
		</tr>		
		<tr>
			<td valign='top' colspan='3'><b>System Abrupt Shutdown Gaps</b></td>
			<td valign='top' align='right'>".number_format($dres['abrupt_shutdowns'],2)."</td>
		</tr>
		<tr>
			<td valign='top' colspan='3'><b>System Data Points</b></td>
			<td valign='top' align='right'>".number_format($dres['num'],2)."</td>
		</tr>
		<tr>
			<td valign='top' align='right'><b>(Hours)</b></td>
			<td valign='top' align='right'><b>Driven</b></td>
			<td valign='top' align='right'><b>Rested</b></td>
			<td valign='top' align='right'><b>Worked</b></td>
		</tr>
		<tr>
			<td valign='top'><b>Actual</b></td>
			<td valign='top' align='right'>".number_format($dres['driven_hours'],2)."</td>
			<td valign='top' align='right'>".number_format($dres['rested_hours'],2)."</td>
			<td valign='top' align='right'>".number_format($dres['worked_hours'],2)."</td>
		</tr>
		<tr>
			<td valign='top'><b>Week</b></td>
			<td valign='top' align='right'>".number_format($dres['week_driven_hours'],2)."</td>
			<td valign='top' align='right'>".number_format($dres['week_rested_hours'],2)."</td>
			<td valign='top' align='right'>".number_format($dres['week_worked_hours'],2)."</td>
		</tr>
		</table>
		";

echo "".$dot_summary."";

$arr[0][0]="";
for($i=0;$i < $days; $i++)
{
	for($j=0;$j < 24; $j++)
	{
		$arr[$i][$j]="<div style='background-color:green; width='50' height='50'>&nbsp;</div>";	
	}	
}


$pres=get_planning_loads_for_this_date_driver($driver_id,$from_date,$to_date);
$pcntr=$pres['num'];
$ploads=$pres['loads'];
$pdisps=$pres['dispatches'];
$pdates=$pres['dates'];
$pstarts=$pres['starts'];
$phours=$pres['hours'];




$calendar="<br><br>Preplanned Loads Found: ".$pcntr.".<br><br>";

$calendar.="<table cellpadding='0' cellspacing='0' border='1'>";
$calendar.="<tr>";
	
	$calendar.="<td valign='top' align='left'>Date</td>";
	
	for($j=0;$j < 24; $j++)
	{		
		$calendar.="<td valign='top' align='right' width='50'>".$j.":00</td>";	
	}
	
$calendar.="</tr>";

$rule_11_hrs=0;
$rule_14_hrs=0;
$rule_70_hrs=0;
$break_10_hrs=0;
$break_34_hrs=0;

$rest_on10=0;
$rest_on34=0;
$work_on11=0;
$work_on14=0;
$work_on70=0;


for($i=0;$i < $days; $i++)
{
	$print_date=date("m/d/Y", strtotime("+".$i." day",strtotime($from_date)));	
	
	$calendar.="<tr class='".($i %2==0 ? "even" : "odd")."'>";
	$calendar.="<td valign='top' align='left'>".$print_date."</td>";
	
	$found=0;
	for($x=0;$x < $pcntr; $x++)
	{
		if($pdates[$x]==$print_date)
		{
     		$y=$pstarts[$x];
     		$arr[$i][$y]="<b>".$ploads[$x]."</b>: <i>".$pdisps[$x]."</i> (".ceil($phours[$x]).")";	//{".$pdates[$x]."} 
     		
     		if($phours[$x] > 1)
     		{
     			$left=ceil($phours[$x]) - 1;
     			$day_left=24 - $y;
     			
     			$new_i = $i;
     			$new_z = $y;
     				
     			for($z=($y+1);$z < ($left + $y + 1); $z++)
     			{        				
     				//$new_z = $z;				
     				
     				$new_z++;
     				
     				while($new_z >=24)
     				{
     					$new_i++;	
     					$new_z-=24;     					
     				}
     				
     				$arr[$new_i][$new_z]="- - >";	
     			}
     				
     		}
     		
		}
	}	
	
	for($j=0;$j < 24; $j++)
	{
		$use_load=$arr[$i][$j];
		
		$tag1="";
		$tag2="";
		
		if(substr_count($use_load,"&nbsp;") > 0)
		{
			$rest_on10++;
			$rest_on34++;
			
			if($rest_on34 ==34)
			{	
				$break_34_hrs++;
				$work_on70=0;
				
				$rest_on34=0;
				$rest_on10=0;
			}
			if($rest_on10 ==10)
			{
				$break_10_hrs++;
				$work_on11=0;
				$work_on14=0;
				
				$rest_on10=0;
			}
		}
		else
		{
			$work_on11++;
			$work_on14++;
			$work_on70++;
			$rest_on10=0;
			$rest_on34=0;
			if($work_on11 > 11)		{	$rule_11_hrs++;	$tag1="<div style='color:red; font-weight:bold;' title='Begins violation of 11-hr rule'>";		$tag2="11</div>";	$work_on11=0;	}
			if($work_on14 > 14)		{	$rule_14_hrs++;	$tag1="<div style='color:orange; font-weight:bold;' title='Begins violation of 14-hr rule'>";		$tag2="14</div>";	$work_on14=0;	}
			if($work_on70 > 70)		{	$rule_70_hrs++;	$tag1="<div style='color:purple; font-weight:bold;' title='Begins violation of 70-hr rule'>";		$tag2="70</div>";	$work_on70=0;	}
		}
				
		$calendar.="<td valign='top' align='right' width='50'>".$tag1."".$use_load."".$tag2."</td>";	
	}
	
	$calendar.="</tr>";
}
$calendar.="<table>";

$cur_tab="<b>Current Planned and Dispatched Loads</b>
		<table cellpadding='2' cellspacing='2' border=1>
		<tr>
			<td valign='top'><b>10-Hour Rest Breaks</b></td>
			<td valign='top' align='right'>".number_format($break_10_hrs,2)."</td>
		</tr>
		<tr>
			<td valign='top'><b>34-Hour Rest Breaks</b></td>
			<td valign='top' align='right'>".number_format($break_34_hrs,2)."</td>
		</tr>
		<tr>
			<td valign='top' colspan='2'>&nbsp;</td>
		</tr>
		<tr>
			<td valign='top'><span style='color:red; font-weight:bold;'>11-Hr Driving (per 10hrs Rest Break)</span></td>
			<td valign='top' align='right'>".number_format($rule_11_hrs,2)."</td>
		</tr>
		<tr>
			<td valign='top'><span style='color:orange; font-weight:bold;'>14-Hr Working (per 10hrs Rest Break)</span></td>
			<td valign='top' align='right'>".number_format($rule_14_hrs,2)."</td>
		</tr>
		<tr>
			<td valign='top'><span style='color:purple; font-weight:bold;'>70-Hr Driving/Working (per 34hrs Rest Break)</span></td>
			<td valign='top' align='right'>".number_format($rule_70_hrs,2)."</td>
		</tr>
		<tr>
			<td valign='top'><b>Total DOT Violations</b></td>
			<td valign='top' align='right'>".number_format(( $rule_11_hrs + $rule_14_hrs + $rule_70_hrs ),2)."</td>
		</tr>
		</table>
";

echo "<br><br>".$cur_tab."".$calendar."";

function get_planning_loads_for_this_date_driver($driver_id,$date_from,$date_to)
{
	$loads[0]=0;
	$disps[0]=0;
	$hours[0]=0;
	$eta_hours[0]=0;
	$starts[0]=0;
	$dates[0]="";
	$cntr=0;
		
	$miles_per_hour=60;
	
	//get preplan loads
	$sql = "
		 select load_handler.*,
		 	TIMEDIFF(load_handler.linedate_dropoff_eta,load_handler.linedate_pickup_eta) as hours_diff
		 from load_handler 
		 where load_handler.deleted=0
		 	and load_handler.linedate_pickup_eta>='".date("Y-m-d",strtotime($date_from))." 00:00:00'
		 	and load_handler.linedate_pickup_eta<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
		 	and preplan > 0
		 	and (
		 		preplan_driver_id = '".sql_friendly($driver_id)."'
		 		or
		 		preplan_driver2_id = '".sql_friendly($driver_id)."'
		 		or
		 		preplan_leg2_driver_id = '".sql_friendly($driver_id)."'
		 		or
		 		preplan_leg2_driver2_id = '".sql_friendly($driver_id)."'
		 	)
		 order by load_handler.linedate_pickup_eta asc,load_handler.id asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{
		$id=$row['id'];		//Load ID
		$pickup=date("m/d/Y",strtotime($row['linedate_pickup_eta']));	//m/d/Y  
		$pickup_hr=(int) date("H",strtotime($row['linedate_pickup_eta']));	//m/d/Y    
		
		$mile_val=(mrr_pull_preplan_pcmiles_for_load($id) / $miles_per_hour);	
		
		$loads[$cntr]=$id;
		$disps[$cntr]=0;		//no displatches for these...
		$dates[$cntr]=$pickup;
		$starts[$cntr]=$pickup_hr;
		$eta_hours[$cntr]=(int) date("H",strtotime($row['hours_diff']));
		$hours[$cntr]=$mile_val;		
		
		$cntr++;
	}	
	
	//get actual dispatches...
	/*
			(select load_handler_stops.linedate_pickup_eta from load_handler_stops where load_handler_stops.load_handler_id=trucks_log.load_handler_id and load_handler_stops.deleted=0 order by load_handler_stops.linedate_pickup_eta asc limit 1) as first_eta,
		 	(select load_handler_stops.linedate_pickup_eta from load_handler_stops where load_handler_stops.load_handler_id=trucks_log.load_handler_id and load_handler_stops.deleted=0 order by load_handler_stops.linedate_pickup_eta asc limit 1) as last_eta
	*/
	
	$sql = "
		 select trucks_log.*,
		 	load_handler.linedate_pickup_eta as first_eta,
		 	load_handler.linedate_dropoff_eta as last_eta,
		 	TIMEDIFF(load_handler.linedate_dropoff_eta,load_handler.linedate_pickup_eta) as hours_diff
		 from trucks_log 
		 	left join load_handler on load_handler.id=trucks_log.load_handler_id
		 where trucks_log.deleted=0
		 	and trucks_log.linedate_pickup_eta>='".date("Y-m-d",strtotime($date_from))." 00:00:00'
		 	and trucks_log.linedate_pickup_eta<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
		 	and (
		 		trucks_log.driver_id = '".sql_friendly($driver_id)."'
		 		or
		 		trucks_log.driver2_id = '".sql_friendly($driver_id)."'
		 	)
		 order by trucks_log.linedate_pickup_eta asc,trucks_log.id asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{
		$disp_id=$row['id'];										//Disp ID
		$id=$row['load_handler_id'];									//Load ID
		$pickup=date("m/d/Y",strtotime($row['first_eta']));		//m/d/Y  
		$pickup_hr=(int) date("H",strtotime($row['first_eta']));	//m/d/Y 
		//$pickup=date("m/d/Y",strtotime($row['linedate_pickup_eta']));		//m/d/Y  
		//$pickup_hr=(int) date("H",strtotime($row['linedate_pickup_eta']));	//m/d/Y    
		
		$mile_val=(($row['miles'] + $row['miles_deadhead']) / $miles_per_hour);	
		
		$loads[$cntr]=$id;
		$disps[$cntr]=$disp_id;		
		$dates[$cntr]=$pickup;
		$starts[$cntr]=$pickup_hr;
		$eta_hours[$cntr]=(int) date("H",strtotime($row['hours_diff']));
		$hours[$cntr]=$mile_val;		
		
		$cntr++;
	}
	
	
	$res['num']=$cntr;
	$res['loads']=$loads;
	$res['dispatches']=$disps;
	$res['dates']=$dates;
	$res['starts']=$starts;
	$res['eta_hours']=$eta_hours;
	$res['hours']=$hours;	
	
	return $res;
}

die;

$driver_id=410;
$date_from="02/27/2013";
$date_to="02/27/2014";

echo "<br><b>".mrr_get_driver_name($driver_id)." [".$driver_id."]</b> (".$date_from." - - > ".$date_to.")<br>";

$res=mrr_get_driver_miles_per_period($driver_id,$date_from,$date_to);	
echo "Dispatches ".$res['cntr']."<br>";

echo "Tot Miles ".$res['miles']."<br>";
echo "Tot DeadHead ".$res['miles_deadhead']."<br>";
echo "Tot Hours ".$res['hours']."<br>";
echo "Tot Pay $".number_format($res['pay'],2)."<br>";

echo "AVG Miles ".number_format($res['avg_miles'],2)."<br>";
echo "AVG DeadHead ".number_format($res['avg_miles_deadhead'],2)."<br>";
echo "AVG Hours ".number_format($res['avg_hours'],2)."<br>";
echo "AVG Pay $".number_format($res['avg_pay'],2)."<br>";

echo "Days in date range ".$res['days']."<br>";
echo "AVG Miles per Day ".number_format($res['avg_days_miles'],2)."<br>";
echo "AVG DeadHead per Day ".number_format($res['avg_days_miles_deadhead'],2)."<br>";
echo "AVG Hours per Day ".number_format($res['avg_days_hours'],2)."<br>";
echo "AVG Pay per Day $".number_format($res['avg_days_pay'],2)."<br>";

echo "<br>Report:<br>".$res['html']."<br>";
/**/
die;

$legs=3;
$gps[0]['lat']="36.012714";
$gps[0]['long']="-86.559967";
$gps[1]['lat']="35.975529";
$gps[1]['long']="-87.132065";
$gps[2]['lat']="36.012714";
$gps[2]['long']="-86.559967";
$gps[3]['lat']="35.975529";
$gps[3]['long']="-87.132065";

$dist=mrr_promiles_get_file_contents_multi_leg($legs,$gps);
echo "Distance from ";
for($i=0;$i < $legs; $i++)
{
	if($i!=0)		echo " to ";
	
	echo "(".$gps[$i]['lat'].",".$gps[$i]['long'].")";	
}
echo " is ".$dist.".<br><br>";
die;

$lat1="36.012714";
$long1="-86.559967";
$lat2="35.975529";
$long2="-87.132065";
$dist=mrr_promiles_get_file_contents($lat1,$long1,$lat2,$long2);
echo "Distance from (".$lat1.",".$long1.") to (".$lat2.",".$long2.") is ".$dist.".";
die;


echo "<div width='500'>".mrr_get_messages_by_truck_mini(295, '2014-02-14', '2014-02-14',0,0,0,999) ."</div>";		//last three are driverID Load ID and dispatch ID, then disp section... 
die;






//die("<br><br>Done.");

$zip1="40109";
$zip2="40228";
$dres=mrr_process_zip_code_to_zip_code($zip1,$zip2);
echo "<br>(".$zip1.") to (".$zip2.") = ".$dres['miles']." Miles. Time: ".$dres['time'].".";
echo "<br>(".$dres['city1'].",".$dres['state1'].") to (".$dres['city2'].",".$dres['state2'].").";
echo "<br>LINK: ".$dres['link']."";
if($dres['miles']==0 && $zip1!=$zip2)
{	
	echo "<br>SUB: ".$dres['sub']."";
	echo "<br>SUB2: ".$dres['sub2']."";
}

die("<br><br>Done.");

$dropped_trailers=mrr_display_trailers_dropped_multi_times();
echo $dropped_trailers['html'];
echo $dropped_trailers['html2'];

die("<br><br>Done.");

//36.017128,-86.593567] [35.998001,-86.584999 


$lat="86.943672";
$long="36.071510";

$lat="-86.593567";
$long="36.017128";
$result=mrr_find_zip_code_from_gps($lat,$long);
echo "Location 1:  [".$lat.", ".$long."] = <b>".$result['zip']."</b>.  --".$result['all'].". SQL=".$result['sql']."<br>";

$lat2="87.132065";
$long2="35.975529";

$lat2="-86.584999";
$long2="35.998001";

$result2=mrr_find_zip_code_from_gps($lat2,$long2);
echo "Location 2:  [".$lat2.", ".$long2."] = <b>".$result2['zip']."</b>.  --".$result2['all'].". SQL=".$result2['sql']."<br>";

$truck_distance=mrr_distance_between_gps_points($lat,$long,$lat2,$long2,1);
$truck_distance=abs($truck_distance);
$miles_distance=$truck_distance / 5280;
echo "Crow Flies:  ".number_format($miles_distance,2)." Miles (".number_format($truck_distance,2)." Feet).<br>";

$pc_miler=mrr_pc_miler_distance($result['zip'],$result2['zip'],0);
echo "PC Miler:  ".number_format($pc_miler,2)." Miles.<br>";


die("<br><br>Done.");


//build zip code database
$zips= array();
$states= array();
$cities= array();
$lats= array();
$longs= array();
$pops= array();

ob_start();
include('zip_listing.php');					//THIS IS A LARGE FILE....29472 lines...
$zip_listing = ob_get_contents();
ob_end_clean();

$zip_listing=str_replace(chr(13),"",$zip_listing);
$zip_listing=str_replace(chr(10),"</td></tr><tr><td valign='top'>",$zip_listing);
$zip_listing=str_replace(chr(9),"</td><td valign='top'>",$zip_listing);

/*
echo "
	<br><b>Unprocessed</b>
	<br><br>
	<table width='1000'>
	<tr>
		<td valign='top'><b>Zip</b></td>
		<td valign='top'><b>State</b></td>
		<td valign='top'><b>City</b></td>
		<td valign='top'><b>Lat</b></td>
		<td valign='top'><b>Long</b></td>
		<td valign='top'><b>Population</b></td>
	</tr>
";

echo $zip_listing ."</table><br><br><hr><br><br>";
*/

echo "	
	<br><b>Processed</b>
	<br><br>
	<table width='1000'>
	<tr>
		<td valign='top'><b>Zip</b></td>
		<td valign='top'><b>State</b></td>
		<td valign='top'><b>City</b></td>
		<td valign='top'><b>Lat</b></td>
		<td valign='top'><b>Long</b></td>
		<td valign='top'><b>Population</b></td>
	</tr>
";

$cntr=0;
$lines=explode("<tr>",$zip_listing);
foreach($lines as $key => $val) 
{
     $value=$val;
     $ind=$key;
     
     $value=str_replace("<tr>","",$value);
     $value=str_replace("</tr>","",$value);
     
     if(trim($value)!="")
     {
     	$cols=explode("<td valign='top'>",$value);	
     	$lcntr=0;
     	foreach($cols as $key2 => $val2) 
     	{
     		$value2=$val2;
     		$ind2=$key2;	
     		
     		$value2=str_replace("<td valign='top'>","",$value2);
     		$value2=str_replace("</td>","",$value2);
     		
     		if(trim($value2)!="")
     		{
     			if($lcntr==0)	$zips[$cntr]="".$value2."";
     			if($lcntr==1)	$states[$cntr]="".$value2."";
     			if($lcntr==2)	$cities[$cntr]="".$value2."";
     			if($lcntr==3)	$lats[$cntr]="".$value2."";
     			if($lcntr==4)	$longs[$cntr]="".$value2."";
     			if($lcntr==5)	$pops[$cntr]="".$value2."";
     			$lcntr++;
     		}
     	}
     	
     	$cntr++;
     }
}


for($i=0; $i < $cntr; $i++)
{
  	if(trim($zips[$i])!="" || $zips[$i]!=0)
  	{
  		$sql = "
  			insert into gps_to_zip_code
  				(id,
  				linedate_added, 
				deleted,
                    zip_code,
                    city,
				state,
                    latitude,
				longitude,                     	
                    population)
           	values 
           		(NULL,
           		NOW(),
           		0,
           		'".sql_friendly($zips[$i])."',
           		'".sql_friendly($cities[$i])."',
           		'".sql_friendly($states[$i])."',           		
           		'".sql_friendly($lats[$i])."',
           		'".sql_friendly($longs[$i])."',
           		'".sql_friendly($pops[$i])."')
		";
		//simple_query($sql);
  	}
  	
  	echo "
  		<tr>
			<td valign='top'>".$zips[$i]."</td>
			<td valign='top'>".$states[$i]."</td>
			<td valign='top'>".$cities[$i]."</td>
			<td valign='top'>".$lats[$i]."</td>
			<td valign='top'>".$longs[$i]."</td>
			<td valign='top'>".$pops[$i]."</td>
		</tr>
  	";
} 


echo "
	</table>
";


//Zip 		St 		City 		Latatude 		Longitude 		Pop.
//$zips[]=35004;	$states[]="AL";   $cities[]="ACMAR"; 	$lats[]="86.515570";	$longs[]="33.584132";	// 	6055


die("<br><br>Done.");

//driver phone number recovery
$tab="
	<b>Drivers Phone Number Listing:</b>
	<br><br>
	<table cellpadding='0' cellspacing='0' border='0' width='1800'>
	<tr>
		<td valign='top'><b>ID</b></td>
		<td valign='top'><b>Last Name</b></td>
		<td valign='top'><b>First Name</b></td>
		<td valign='top'><b>Cell Phone</b></td>
		<td valign='top'><b>Home Phone</b></td>
		<td valign='top'><b>Other Phone</b></td>
		<td valign='top'><b>Cell Phone2</b></td>
		<td valign='top'><b>Home Phone2</b></td>
		<td valign='top'><b>Other Phone2</b></td>
		<td valign='top'><b>Cell Phone3</b></td>
		<td valign='top'><b>Home Phone3</b></td>
		<td valign='top'><b>Other Phone3</b></td>
	</tr>
";
$cntr=0;
$acntr=0;
$sql = "
	select *
	
	from drivers
	where deleted = 0
	order by name_driver_last,name_driver_first,id
";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) 
{	
	$tag1="";
	$tag2="";
	if($row['active'] > 0)
	{
		$acntr++;
		$tag1="<b>";
		$tag2="</b>";
	}
	$phone_cell2="";
	$phone_home2="";
	$phone_other2="";
	$phone_cell3="";
	$phone_home3="";
	$phone_other3="";
	
	$sql2 = "
		select phone_cell,phone_home,phone_other		
		from drivers_old
		where id='".sql_friendly($row['id'])."'
	";
	$data2 = simple_query($sql2);
	if($row2 = mysqli_fetch_array($data2)) 
	{
		$phone_cell2=$row2['phone_cell'];
		$phone_home2=$row2['phone_home'];
		$phone_other2=$row2['phone_other'];
	}
	$sql3 = "
		select phone_cell,phone_home,phone_other		
		from drivers_older
		where id='".sql_friendly($row['id'])."'
	";
	$data3 = simple_query($sql3);
	if($row3 = mysqli_fetch_array($data3)) 
	{
		$phone_cell3=$row3['phone_cell'];
		$phone_home3=$row3['phone_home'];
		$phone_other3=$row3['phone_other'];
	}
	
	//update numbers from older tables...
	if(trim($row['phone_cell'])=="" && trim($phone_cell2)!="")
	{  
		$sqlu="update drivers set phone_cell='".sql_friendly($phone_cell2)."' where id='".sql_friendly($row['id'])."'";		
		simple_query($sqlu); 
	}
	elseif(trim($row['phone_cell'])=="" && trim($phone_cell3)!="")	
	{  
		$sqlu="update drivers set phone_cell='".sql_friendly($phone_cell3)."' where id='".sql_friendly($row['id'])."'";		
		simple_query($sqlu); 
	}
	
	if(trim($row['phone_home'])=="" && trim($phone_home2)!="")
	{  
		$sqlu="update drivers set phone_home='".sql_friendly($phone_home2)."' where id='".sql_friendly($row['id'])."'";		
		simple_query($sqlu); 
	}
	elseif(trim($row['phone_home'])=="" && trim($phone_home3)!="")	
	{  
		$sqlu="update drivers set phone_home='".sql_friendly($phone_home3)."' where id='".sql_friendly($row['id'])."'";		
		simple_query($sqlu); 
	}
	
	if(trim($row['phone_other'])=="" && trim($phone_other2)!="")
	{  
		$sqlu="update drivers set phone_other='".sql_friendly($phone_other2)."' where id='".sql_friendly($row['id'])."'";		
		simple_query($sqlu); 
	}
	elseif(trim($row['phone_other'])=="" && trim($phone_other3)!="")	
	{  
		$sqlu="update drivers set phone_other='".sql_friendly($phone_other3)."' where id='".sql_friendly($row['id'])."'";		
		simple_query($sqlu); 
	}
	
	$tab.="
		<tr bgcolor='".($cntr%2==0 ? "#eeeeee" : "#dddddd")."'>
			<td valign='top'>".$tag1."".$row['id']."".$tag2."</td>
			<td valign='top'>".$tag1."".$row['name_driver_first']."".$tag2."</td>
			<td valign='top'>".$tag1."".$row['name_driver_last']."".$tag2."</td>
			<td valign='top'>".$row['phone_cell']."</td>
			<td valign='top'>".$row['phone_home']."</td>
			<td valign='top'>".$row['phone_other']."</td>
			<td valign='top'>".$phone_cell2."</td>
			<td valign='top'>".$phone_home2."</td>
			<td valign='top'>".$phone_other2."</td>
			<td valign='top'>".$phone_cell3."</td>
			<td valign='top'>".$phone_home3."</td>
			<td valign='top'>".$phone_other3."</td>
		</tr>
	";
	$cntr++;
}
$tab.="
		<tr>
			<td valign='top'>".$cntr."</td>
			<td valign='top' colspan='2'> Total Drivers</td>
			<td valign='top' colspan='9'>(".$acntr ." Active)</td>
		</tr>
	</table>
";

echo $tab;

die("Done.");

//trailer update section
$sql = "
	select *
	
	from
	trailers
	where deleted = 0
	order by trailer_name asc, id asc
";
$data = simple_query($sql);

$api = new sicap_api_connector();
$income_id = $api->getChartTypeIDByName("Income");		
$cost_of_sales_id = $api->getChartTypeIDByName("Cost of Sales");

$old_name = '';

$ignore_int_name=1;

while($row = mysqli_fetch_array($data)) 
{
	if(is_numeric($row['trailer_name']) || $ignore_int_name==1) 
	{	// && $row['trailer_name']=='464694'
		echo "$row[trailer_name]<br>";
		
		$trailer_name = $row['trailer_name'];
		$truck_id = $row['id'];
		$rental=$row['rental_flag'];
		$rent_lab="";
		if($rental > 0)
		{
			$rent_lab=" (R) Rental";
			if(trim($row['trailer_owner'])!="")		$rent_lab=" (L) ".$row['trailer_owner']."";
		}
		$old_name = $trailer_name;
		
		$active=$row['active'];
			
		////sicap_update_trucks($row['id'], $truck_name, $quick_update = false, $rental = 0)
		
		//update_coa("Trailer Repairs & Maint - #$trailer_name".$rent_lab."", "77500-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Repairs & Maint - #$old_name" : ""),$active);			
		//update_coa("Tires #$trailer_name".$rent_lab."", "77600-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Tires #$old_name" : ""),$active);
		//update_coa("Trailer Wash - #$trailer_name".$rent_lab."", "77800-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Wash - #$old_name" : ""),$active);
		//update_coa("Trailer Accidents - #$trailer_name".$rent_lab."", "77485-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Accidents - #$old_name" : ""),$active);
		
		update_coa("Trailer Mileage Expenses - #$trailer_name".$rent_lab."", "77475-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Mileage Expenses - #$old_name" : ""),$active);
		
		////update_coa("Truck Cleaning - #$truck_name".$rent_lab."", "74900-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Cleaning - #$old_name" : ""),$active);
		
		$inventory_item_id = 0;
		
		$chart_id_sales = $api->getChartIDByName("Trailer Repairs - #$trailer_name");
		$chart_id_inv = $chart_id_sales;
		$chart_id_cost = $chart_id_sales;
		
		$api->clearParams();
		$api->command = "update_inventory_item";
		$api->addParam("ItemName", $trailer_name."-Trailer Repairs");
		$api->addParam("ItemID",$inventory_item_id);
		$api->addParam("ChartIDSales", $chart_id_sales);
		$api->addParam("Active", $active);
		$api->addParam("ChartIDInventory", $chart_id_inv);
		$api->addParam("ChartIDCOGS", $chart_id_cost);
		//$api->show_output = true;
		$api->execute();
		
	}
}


die("Finished...");

	$max_packet=0;
	$sres=mrr_peoplenet_find_data_for_cron_job("elog_events",0,0,"",0,0,0,$max_packet);
	echo "<br>Output below:<br>".$sres['output']."<br><hr><br><pre>".$sres['xml']."</pre>";	//
	
	//$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_driver_view",0,0,"",0,0,0,0);
	//echo "<br><br><hr><br><br>Drivers below:<br><pre>".$sres['output']."</pre><br><hr><br><br>".$sres['output']."<br>";	//  
	
die("Done"); 	
	$lat="36.015434";
	$long="-86.593552";
	
	$speed_check=mrr_gps_speed_limit_finder($lat,$long);
	echo "<b>Ouput:</b><br><hr><br>".$speed_check['output']."";
	echo "<br><b>Max Speed:</b> ".$speed_check['speed_limit']." MPH.<br>";
	
die("Done"); 

	$stop_table=mrr_quick_display_all_load_handler_stops(17427,22729,48257);
	echo $stop_table;


die("Done"); 

	$date_start="1/01/2013";
	$date_end="1/31/2013";
	$res=get_active_truck_count_ranged($date_start,$date_end);
	
	echo "<br>Date Range= ".$res['date_start']." -- ".$res['date_end'].".";
	echo "<br>Trucks=".$res['trucks'].".";
	echo "<br>Billable=".$res['billable'].".";
	echo "<br>Replaced=".$res['replaced'].".";
	echo "<br>Tot Val=".$res['total_value'].".";
	echo "<br>Monthly Val=".$res['monthly_value'].".";
	echo "<br>SQL=".$res['sql'].".";
	
	$date_start="2/01/2013";
	$date_end="2/28/2013";
	$res=get_active_truck_count_ranged($date_start,$date_end);
	
	echo "<br>Date Range= ".$res['date_start']." -- ".$res['date_end'].".";
	echo "<br>Trucks=".$res['trucks'].".";
	echo "<br>Billable=".$res['billable'].".";
	echo "<br>Replaced=".$res['replaced'].".";
	echo "<br>Tot Val=".$res['total_value'].".";
	echo "<br>Monthly Val=".$res['monthly_value'].".";
	echo "<br>SQL=".$res['sql'].".";
	
	$date_start="3/01/2013";
	$date_end="3/31/2013";
	$res=get_active_truck_count_ranged($date_start,$date_end);
	
	echo "<br>Date Range= ".$res['date_start']." -- ".$res['date_end'].".";
	echo "<br>Trucks=".$res['trucks'].".";
	echo "<br>Billable=".$res['billable'].".";
	echo "<br>Replaced=".$res['replaced'].".";
	echo "<br>Tot Val=".$res['total_value'].".";
	echo "<br>Monthly Val=".$res['monthly_value'].".";
	echo "<br>SQL=".$res['sql'].".";


die("Done"); 

	$addr1="1331 Airport Freeway";
	$addr2="";
	$city="Irving";
	$state="TX";
	$zip="75062";
	$stop_id="47781";
	$res=mrr_get_geocode_for_address($addr1,$addr2,$city,$state,$zip);
     $latitude=$res['latitude'];
     $longitude=$res['longitude'];
	echo "<br>".$stop_id." GPS(".$latitude.",".$longitude.")<br>";
	$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			dest_longitude='".sql_friendly($longitude)."',
			dest_latitude='".sql_friendly($latitude)."'
		where stop_id='".sql_friendly($stop_id)."'
	";
	simple_query($sql);
	
	$addr1="3657 Trousdale Drive";
	$addr2="";
	$city="Nashville";
	$state="TN";
	$zip="37204";
	$stop_id="47848";
	$res=mrr_get_geocode_for_address($addr1,$addr2,$city,$state,$zip);
     $latitude=$res['latitude'];
     $longitude=$res['longitude'];
	echo "<br>".$stop_id." GPS(".$latitude.",".$longitude.")<br>";
	$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			dest_longitude='".sql_friendly($longitude)."',
			dest_latitude='".sql_friendly($latitude)."'
		where stop_id='".sql_friendly($stop_id)."'
	";
	simple_query($sql);
	
	$addr1="216 Parthenon Blvd";
	$addr2="";
	$city="Lavergne";
	$state="TN";
	$zip="37086";
	$stop_id="47899";
	$res=mrr_get_geocode_for_address($addr1,$addr2,$city,$state,$zip);
     $latitude=$res['latitude'];
     $longitude=$res['longitude'];
	echo "<br>".$stop_id." GPS(".$latitude.",".$longitude.")<br>";
	$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			dest_longitude='".sql_friendly($longitude)."',
			dest_latitude='".sql_friendly($latitude)."'
		where stop_id='".sql_friendly($stop_id)."'
	";
	simple_query($sql);

die("Done"); 

	$max_packet=0;
	$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_dispatch_events",0,0,"",0,0,$max_packet);
	echo "<br>Output below:<br>".$sres['output']."<br>";	//<pre>".$sres['xml']."</pre>
	
die("Done");	
	
	$debugger=mrr_run_full_geofencing_update_for_truck_V2(0);
	echo $debugger."<br><br>";


die("Done");
	$reporter=mrr_run_force_data_call_for_truck(0);
	echo $reporter;

die("Done");	
	$result=mrr_update_stop_GPS_timezones();
	echo "<br>".$result."<br><br>";


die("Done");

	$lat="47.01";
	$long="10.2";
	$mrr_res=mrr_fetch_gps_timezone($lat,$long);
	echo "
		<div>".$mrr_res['html']."</div>
		<div>Time Zone=".$mrr_res['tz'].".</div>
		<div>DST Offset=".$mrr_res['dst'].".</div>
		<div>GMT Offset=".$mrr_res['gmt'].".</div>
		<div>Raw Offset=".$mrr_res['raw'].".</div>
		<div>Their Time=".$mrr_res['time'].".</div>
	";	
	
die('Done');	
	//this section gives every completed stop a grade
	$starter="2013-01-01";
	$ender="2013-12-31";
	$html=mrr_self_grading_completed_stops($starter,$ender);
	echo $html;
	
die('Done');
	//this section sets up the trucks that were rental,leased, and company owned
	$starter="2013-01-01 00:00:00";
	echo "<table border='0' cellpadding='0' cellspacing='0' width='800'>
			<tr>
				<td valign='top' width='50'><b>ID</b></td>
				<td valign='top'><b>Truck</b></td>
				<td valign='top' width='50'><b>Owned</b></td>
				<td valign='top' width='50'><b>Rental</b></td>
				<td valign='top' width='50'><b>Leased</b></td>
				<td valign='top' width='50'><b>Active</b></td>				
				<td valign='top' width='100'><b>Dispatches</b></td>
				<td valign='top' width='50'><b>Updated</b></td>
			</tr>";
	
	$tot_trucks=0;
	$tot_owned=0;
	$tot_rental=0;
	$tot_leased=0;
	$tot_active=0;
	
	$tot_disp=0;
	$tot_updated=0;
	
	$sql="select id,
			name_truck,
			company_owned,
			rental,
			active
		from trucks
		where deleted=0
		order by name_truck asc,id asc
		"; 
	$data = simple_query($sql); 
	while($row = mysqli_fetch_array($data)) 
	{		
		$is_leased=0;
		$dispatches=0;
		$updated=0;
		$set_rental=0;
		
		if($row['company_owned']==0 && $row['rental']==0)
		{
			$is_leased=1;
			$tot_leased++;	
			$set_rental=2;
		}
		elseif($row['company_owned'] > 0)
		{
			$tot_owned++;
		}
		elseif($row['rental'] > 0)
		{
			$tot_rental++;	
			$set_rental=1;
		}
		
		if($row['active'] > 0)		$tot_active++;
		
		
		$sql2="
			select id 
			from trucks_log
			where linedate>='".$starter."' 
				and deleted=0 
				and truck_id='".$row['id']."'
			order by linedate asc
		";
		$data2 = simple_query($sql2);
		$dispatches=mysqli_num_rows($data2);	
		
		if($dispatches > 0)
		{
			$tot_disp+=$dispatches;
			while($row2 = mysqli_fetch_array($data2)) 
			{
				$sql3="update trucks_log set truck_rental='".$set_rental."' where id='".$row2['id']."'";
				simple_query($sql3);	
				$updated++;	
				$tot_updated++;
			}	
		}		
		
		echo "
			<tr>
				<td valign='top'>".$row['id']."</td>
				<td valign='top'>".$row['name_truck']."</td>
				<td valign='top'>".$row['company_owned']."</td>
				<td valign='top'>".$row['rental']."</td>
				<td valign='top'>".$is_leased."</td>
				<td valign='top'>".$row['active']."</td>				
				<td valign='top'>".$dispatches."</td>
				<td valign='top'>".$updated."</td>
			</tr>
		";		
		$tot_trucks++;		
	}
	echo "
		<tr>
				<td valign='top'><b>Id</b></td>
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>Owned</b></td>
				<td valign='top'><b>Rental</b></td>
				<td valign='top'><b>Leased</b></td>
				<td valign='top'><b>Active</b></td>				
				<td valign='top'><b>Dispatches</b></td>
				<td valign='top'><b>Updated</b></td>
		</tr>
		<tr>
				<td valign='top'><b>Total</b></td>
				<td valign='top'>".$tot_trucks."</td>
				<td valign='top'>".$tot_owned."</td>
				<td valign='top'>".$tot_rental."</td>
				<td valign='top'>".$tot_leased."</td>
				<td valign='top'>".$tot_active."</td>
				<td valign='top'>".$tot_disp."</td>
				<td valign='top'>".$tot_updated."</td>
		</tr>
	</table>";

die;
	$_POST['date_from'] = "2/01/2012";
	$_POST['date_to'] 	= "2/29/2012";
	
		$mrr_from_date=date("Y-m-d", strtotime($_POST['date_from']));
		$mrr_to_date=date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])));
		
		$mrr_from_date2=date("Y-m-d", strtotime("-15 day", strtotime($_POST['date_from'])));
		$mrr_to_date2=date("Y-m-d", strtotime("15 day", strtotime($_POST['date_to'])));
		
		$search_date_range = "
				and load_handler.linedate_pickup_eta >= '".$mrr_from_date."'
				and load_handler.linedate_pickup_eta < '".$mrr_to_date."'
			";
		$search_date_range2 = "
				and trucks_log.linedate_pickup_eta >= '".$mrr_from_date."'
				and trucks_log.linedate_pickup_eta < '".$mrr_to_date."'
			";
		$driver_search = "";
		/*
		  AND trucks_log.linedate_pickup_eta >= '2012-01-01 00:00:00' 
  		  AND trucks_log.linedate_pickup_eta <= '2012-01-31 23:59:59' 
		*/
		$sql = "
			select DISTINCT(load_handler.id) AS mrr_unique_id,
				load_handler.*,
				customers.name_company,
				load_handler.actual_bill_customer - load_handler.actual_total_cost as load_profit,
				(select ifnull(sum(trucks_log.loaded_miles_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as loaded_miles_hourly,
				(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
				(select ifnull(sum(trucks_log.driver2_id),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as driver_cnt,
				(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead			
			from load_handler,customers,trucks_log
			where load_handler.deleted = 0
				and customers.deleted = 0
				and trucks_log.deleted = 0	
				and customers.id = load_handler.customer_id
				and trucks_log.load_handler_id = load_handler.id		
				".$search_date_range2."			
			order by load_handler.linedate_pickup_eta asc,load_handler.id asc
		";
		$mrr_capture_sql=$sql;
		//$data = simple_query($sql);
		echo $mrr_capture_sql;
	
?>