<? include('application.php') ?>
<?
if(!isset($_GET['connect_key']) || $_GET['connect_key'] != 'bas82bad98fqhbnwga8shq34908asdhbn') 
{
	//die("You have reached this page incorrectly.");
}

echo "<h3>GeoTab Test Page: ".date("m/d/Y",time())."</h3>";

echo "<br>Acct: ".mrr_find_geotab_username()."";
echo "<br>Pass: ".mrr_find_geotab_password()."";
echo "<br>GeoTab DB: ".mrr_find_geotab_database()."";
echo "<br>GeoTab Server: ".mrr_find_geotab_server_name()."";
echo "<br>GeoTab Session ID: ".mrr_find_geotab_session_id()."";
echo "<br>Conard DB: ".mrr_find_geotab_database_name()."";

echo "<br>URL 0: ".mrr_find_geotab_url()."";
echo "<br>URL 1: ".mrr_find_geotab_url_1()."";
echo "<br>URL 2: ".mrr_find_geotab_url_2()."";

echo "<br>GeoTab vs PN Switch is <b>".($_SESSION['use_geotab_vs_pn']==1 ? "ON" : "off")."</b>.<br>";

echo "<br><h3>GeoTab URLs:</h3><br>";

echo "<br>URL: ".mrr_find_geotab_url()." --- Version ".mrr_get_geotab_version()."";
echo "<br>
		<a href='".mrr_find_geotab_url()."GetVersion' target='_blank'>
			".mrr_find_geotab_url()."GetVersion
		</a>
	<br><br>
	Use the link, <a href='geotab_cronjob.php'>GeoTab CronJob</a>, for Feed Menu Options.  Or use the SPECIAL Diagnostics...<a href='geotab_special.php'>GeoTab</a>
	<br><br>
	";	
	//Use the link, <a href='".mrr_find_geotab_url_1()."#engineDiagnostics'>GeoTab Diagnostic Info</a>, for research only...and can then be used in other functions once installed.
	//<br><br>
		
/*
//for texting tests  GeoTab App for phone.
//user: 334
//pass: 334
*/

/*
echo "<br>Log-In:<br>".mrr_get_authenticate()."
	<br>Test: https://my.geotab.com/apiv1/Authenticate?database=GEOTAB&userName=USERsssssssssssss@geotab.com&password=PASSWORD
	<br>Real: https://my.geotab.com/apiv1/Authenticate?database=conard&userName=michael@sherrodcomputers.com&password=R3dS0x18
	";

*/


//echo "<br>Devices:<br>".mrr_get_geotab_get_devices()."";

//echo "<br>Drivers:<br>".mrr_get_geotab_get_drivers()."";


//echo '<br>https://my241.geotab.com/apiv1/Get?typeName=Device&credentials={"database":"conard","userName":"michael@sherrodcomputers.com","sessionId":"8930957207201481481"}';

//echo "<br>Users:<br>".mrr_get_geotab_get_users()."";


//echo "<br>Data Feed: LogRecord<br>".mrr_get_geotab_get_datafeed("LogRecord").".";		//,"2250"
//echo "<br>Data Feed: StatusData<br>".mrr_get_geotab_get_datafeed("StatusData").".";
//echo "<br>Data Feed: FaultData<br>".mrr_get_geotab_get_datafeed("FaultData").".";
//echo "<br>Data Feed: Trip<br>".mrr_get_geotab_get_datafeed("Trip").".";
//echo "<br>Data Feed: ExceptionEvent<br>".mrr_get_geotab_get_datafeed("ExceptionEvent").".";
//echo "<br>Data Feed: DutyStatusLog<br>".mrr_get_geotab_get_datafeed("DutyStatusLog").".";
//echo "<br>Data Feed: AnnotationLog<br>".mrr_get_geotab_get_datafeed("AnnotationLog").".";
//echo "<br>Data Feed: DVIRLog<br>".mrr_get_geotab_get_datafeed("DVIRLog").".";
//echo "<br>Data Feed: ShipmentLog<br>".mrr_get_geotab_get_datafeed("ShipmentLog").".";
//echo "<br>Data Feed: TrailerAttachment<br>".mrr_get_geotab_get_datafeed("TrailerAttachment").".";
//echo "<br>Data Feed: IoxAddOn<br>".mrr_get_geotab_get_datafeed("IoxAddOn").".";
//echo "<br>Data Feed: CustomData<br>".mrr_get_geotab_get_datafeed("CustomData").".";

$trailer_id=884;
//$geotab_trailer_id=mrr_find_geotab_trailer_id_by_id($trailer_id);
//echo "<br>Trailer <b>".$trailer_id."</b> has GeoTab ID <b>".$geotab_trailer_id."</b>.";
//echo "<br>GeoTab Trailer ID <b>".$geotab_trailer_id."</b> is for Trailer ID <b>".mrr_find_geotab_trailer_by_id($geotab_trailer_id,0)."</b>.";
//echo "<br>GeoTab Trailer ID <b>".$geotab_trailer_id."</b> is for Trailer Name <b>".mrr_find_geotab_trailer_by_id($geotab_trailer_id,1)."</b>.";
//echo "<br>GeoTab Trailer ID <b>".$geotab_trailer_id."</b> is for Trailer Nick-Named <b>".mrr_find_geotab_trailer_by_id($geotab_trailer_id,2)."</b>.";

$truck_id=668;		//device ID = "b1"


$diagnotics_save=1;

if(!isset($_GET['diagnotics']))	$_GET['diagnotics']=0;
$_GET['diagnotics']=(int) $_GET['diagnotics'];
if($_GET['diagnotics']==0)	{	$_GET['diagnotics']=1;		$diagnotics_save=0;		}

if(!isset($_GET['mrr_debugger']))	$_GET['mrr_debugger']=0;
$_GET['mrr_debugger']=(int) $_GET['mrr_debugger'];

if(isset($_GET['devide_id']))		$_GET['device_id']=$_GET['device_id'];

if(!isset($_GET['device_id']))	$_GET['device_id']="";
$_GET['device_id']=str_replace("'","",$_GET['device_id']);
$_GET['device_id']=str_replace('"',"",$_GET['device_id']);
$_GET['device_id']=str_replace(";","",$_GET['device_id']);
$_GET['device_id']=str_replace(":","",$_GET['device_id']);
$_GET['device_id']=str_replace(" ","",$_GET['device_id']);
$_GET['device_id']=str_replace("_","",$_GET['device_id']);
$_GET['device_id']=str_replace(",","",$_GET['device_id']);
$_GET['device_id']=str_replace(".","",$_GET['device_id']);

if($_GET['diagnotics'] > 100)
{
	$geotab_stop_id=trim($_GET['geotab_stop_id']);
     $displayed=(int) $_GET['displayed'];
     mrr_set_geotab_zones_displayed($geotab_stop_id,$displayed);
     ?>
     <script type='text/javascript'>
	
	$().ready(function() {
		//window.close();	
	});
	
	function close_window() 
	{
    		window.top.close();	 
	}
	</script>
	<div width='80%'><div style='float:right; width:300px;'><a href="#" onclick="close_window(); return false;">close</a></div><br><br><br></div>
	<?
}
elseif($_GET['diagnotics'] > 0)
{
	$device_id=trim($_GET['device_id']);
	//$device_id="b1";
	//$device_id="b1F";
	
	if($_GET['diagnotics'] ==2 && 1==2)
	{
		$device_id="b1F";	//find special truck 34...
		$device_id="b2C";	//find truck 614847...
		echo "<br>Getting Device Diagnostics [1]:<br>".mrr_get_geotab_get_device_info($device_id,1,$diagnotics_save)."<br>";		//second arg is mode (0=ALL, 1=odometer, etc.)	...always fetch this if MODE ==2...truck 34 can only use this one...
		
		$device_id="";		//clear it for the rest of them
	}
	elseif($_GET['diagnotics'] ==1 && 1==2)
	{
		$device_id="b1F";	//find special truck 34... Only need this one for now.
		$device_id="b2C";	//find truck 614847...
	}	
	
	//$device_id="b2C";	//find truck 614847...
	
	echo "<br>Getting Device Diagnostics [".$_GET['diagnotics']."]:<br>
			Date From: ".trim($defaultsarray['geotab_last_odometer'])." or [1] ".trim($defaultsarray['geotab_last_odometer_special']).".<br>
			".mrr_get_geotab_get_device_info($device_id,$_GET['diagnotics'],$diagnotics_save,$_GET['mrr_debugger'])."<br>
		";		//second arg is mode (0=ALL, 1=odometer, etc.)
}
elseif($_GET['diagnotics'] < 0)
{
     $geotab_stop_id="bBC54E0";
     $displayed=1;
     mrr_set_geotab_zones_displayed($geotab_stop_id,$displayed);
     
     
     //$geotab_shipment_log_id="";
     //$find_truck_id=497;
     //$driver_id=476;
     //$bol_comp="Kaut Drive Yards";
     //$bol_number="192837";
     //$commodity="TEST Wookies and Cream";
     //$geotab_shipment_log_id=mrr_send_geotab_shipment_log($find_truck_id,$driver_id,$bol_comp,$bol_number,$commodity,$geotab_shipment_log_id);
     
     // //echo "<br>GeoTab ShipmentLog Id=[".$geotab_shipment_log_id."].<br>";
     
     // //$geotab_shipment_log_id="aGrFrS_1ZY0CYeRf3wogNcQ";
     // //mrr_set_geotab_shipment_log($geotab_shipment_log_id,"");
     
     //echo "<br>GeoTab ShipmentLog Id=[".$geotab_shipment_log_id."].<br>";
     
          
     
     //$geotab_msg_id=7623336;
     //mrr_geotab_repair_maint_request($geotab_msg_id,1);
         
     
     $x="-86.3962631";
     $y="36.4038391";
     /*
     $res=mrr_geotab_reverse_geocode_address_from_point($x,$y);
     echo "
     	<br>B. GPS Point: [ ".$x." , ".$y." ].
     	<br>Address 1: ".$res['address_1'].".
     	<br>City: ".$res['city'].".
     	<br>State: ".$res['state'].".
     	<br>Zip: ".$res['zip'].".	
     ";    
     
     
     
     
     
     $test_msg="May the Force be with us.  Test Message...MRR.";
     $msg_urgent=0;
     
     $res=mrr_find_geotab_location_of_this_truck($truck_id,0);
     echo "
     	<br>Truck ".$res['truck_name']." Last Location.
     	<br>Device ".$res['device_id'].".
     	<br>GPS: [ ".$res['longitude']." , ".$res['latitude']." ] (Long,Lat).
     	<br>Speed MPH: ".$res['truck_speed'].".
     	<br>Location: ".$res['location'].".
     	<br>GPS Location: ".$res['gps_location'].".	
     ";
     */
     
     //echo "<br>Send Text Message: ".mrr_send_geotab_text_message($truck_id,$test_msg,$msg_urgent)."";
     
     //echo "<br>Getting Messages:<br>".mrr_get_geotab_get_txtmsg(0,"","").".";
     
     $x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
     $y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;
     
     //$comp_name="QVC";
     //$geotab_stop_id=mrr_get_geotab_zones("",$comp_name,1);
     //echo "<br>Getting Zone(s) for <b>".$comp_name."</b>:<br>".$geotab_stop_id.".";
     
     /*
     $x="-86.59220123291016";		//East/West part
     $y="36.02058029";			//North/South part
     
     $res=mrr_gps_point_box_creator($x,$y,$x_off,$y_off);
     
     echo "<br><hr><br>Conard GPS: [".$y." , ".$x."] <i>Offset Long=".$y_off." and Offset Lat=".$x_off.".</i>";
     echo "
     	<br>North: ".$res['pt0_lat_n']." South: ".$res['pt1_lat_s']." East: ".$res['pt1_long_e']." West: ".$res['pt0_long_w'].".
     	<br>Center Point: ".$res['center'].".
     	<br>NW Point: ".$res['pt0'].".
     	<br>NE Point: ".$res['pt1'].".
     	<br>SE Point: ".$res['pt2'].".
     	<br>SW Point: ".$res['pt3'].".
     	<br>NW Point: ".$res['pt4'].".	
     ";
     
     $long=$y;
     $lat=$x;
     $in_bounds=mrr_gps_point_inside_bounds($long,$lat,$res['pt0_lat_n'],$res['pt1_lat_s'],$res['pt1_long_e'],$res['pt0_long_w']);		
     
     echo "<br>Graphic: ".$res['graphic']."<br><hr><br>GPS point [".$long." , ".$lat."] is ".($in_bounds > 0 ? "In Bounds" : "Out-of_bounds").".";
     */
     
     $long="-93.99220123291016";
     $lat="41.92058029";
     $name="KDY Shipyards";
     $info="This is a test to make zones with the generic box coordinate points.";
     $notes="Test Zone 1...4-9-2018...MRR.";
     
     //echo "<br>Adding Zone: ".mrr_make_geotab_zone($long,$lat,$name,$info,$notes).".";
     
     $namer="Conard Terminal";
     $address="200 International Blvd";
     $city="La Vergne";
     $state="TN";
     $zip="37086";
     $found_long="";
     $found_lat="";
     
     /*
     $namer="Sherrod";
     $address="151 Heritage Park Drive";
     $city="Murfreesboro";
     $state="TN";
     $zip="37129";
     
     $res=mrr_find_geotab_stop_zones_by_addr($address,$city,$state,$zip);
     if($res['id']==0)
     {	//not found, so make the zone from the address.
     	$res=mrr_geotab_get_coordinate_from_addr($address,$city,$state,$zip);
     	echo "
     		<br>A. GPS Point: [ ".$res['long']." , ".$res['lat']." ].
     		<br>Address 1: ".$address.".
     		<br>City: ".$city.".
     		<br>State: ".$state.".
     		<br>Zip: ".$zip.".	
     	";
     	
     	$found_long=$res['long'];
     	$found_lat=$res['lat'];
     	
     	
     	//Now create the saved library zone so we don't have to use the API every single time.	
     	$gres=mrr_gps_point_box_creator($found_long,$found_lat,$x_off,$y_off);
     		
     	//$res['id']=0;
     	$res['geotab_id_name']="";
     	$res['address_1']=$address;
     	$res['city']=$city;
     	$res['state']=$state;
     	$res['zip']=$zip;
     	//$res['long']="";
     	//$res['lat']="";
     	$res['long_zone_w']="".$gres['pt0_long_w']."";
     	$res['long_zone_e']="".$gres['pt1_long_e']."";
     	$res['lat_zone_n']="".$gres['pt0_lat_n']."";
     	$res['lat_zone_s']="".$gres['pt1_lat_s']."";
     	
     	$res['conard_name']=$namer;
     		
     	$res['id']=mrr_create_geotab_stop_zones($res);
     	
     	echo "
     		<br><br>
     		<h3><b>NEW ZONE ID ".$res['id']." a.k.a. '".$res['geotab_id_name']."':</b></h3>
     		<br>GPS Center [".$res['long']." , ".$res['lat']."]
     		<br>".$res['conard_name']."
     		<br>".$res['address_1']."
     		<br>".$res['city'].", ".$res['state']." ".$res['zip']."
     		<br>W ".$res['long_zone_w']." to E ".$res['long_zone_e']." E
     		<br>N ".$res['lat_zone_n']." to ".$res['lat_zone_s']." S
     	";	
     	
     }
     else
     {	//found, so just use the one in hte library of zones.
     	echo "
     		<br><br>
     		<h3><b>ZONE ID ".$res['id']." a.k.a. '".$res['geotab_id_name']."':</b></h3>
     		<br>GPS Cetner [".$res['long']." , ".$res['lat']."]
     		<br>".$res['conard_name']."
     		<br>".$res['address_1']."
     		<br>".$res['city'].", ".$res['state']." ".$res['zip']."
     		<br>W ".$res['long_zone_w']." to E ".$res['long_zone_e']." E
     		<br>N ".$res['lat_zone_n']." to ".$res['lat_zone_s']." S
     	";	
     	
     	$found_long=$res['long'];
     	$found_lat=$res['lat'];
     }
     
     
     
     
     $x="-93.59220123291016";
     $y="41.52058029";
     
     $x=$res['long'];		//East/West part
     $y=$res['lat'];		//North/South part
     
     $x=$found_long;		//long (E and W)
     $y=$found_lat;			//lat (N and S)
     $res=mrr_find_geotab_stop_zones_by_gps($x,$y);	
     if($res['id']==0)
     {	//not found, so make the zone from the GPS point.
     	$res=mrr_geotab_reverse_geocode_address_from_point($x,$y);
     	echo "
     		<br>B. GPS Point: [ ".$x." , ".$y." ].
     		<br>Address 1: ".$res['address_1'].".
     		<br>City: ".$res['city'].".
     		<br>State: ".$res['state'].".
     		<br>Zip: ".$res['zip'].".	
     	";
     	
     	
     	//Now create the saved library zone so we don't have to use the API every single time.
     	$gres=mrr_gps_point_box_creator($x,$y,$x_off,$y_off);
     	
     	//$res['id']=0;
     	$res['geotab_id_name']="";
     	//$res['address_1']=$address;
     	//$res['city']=$city;
     	//$res['state']=$state;
     	//$res['zip']=$zip;
     	$res['long']=$x;
     	$res['lat']=$y;
     	$res['long_zone_w']="".$gres['pt0_long_w']."";
     	$res['long_zone_e']="".$gres['pt1_long_e']."";
     	$res['lat_zone_n']="".$gres['pt0_lat_n']."";
     	$res['lat_zone_s']="".$gres['pt1_lat_s']."";
     	
     	$res['conard_name']=$namer;
     		
     	$res['id']=mrr_create_geotab_stop_zones($res);
     	echo "
     		<br><br>
     		<h3><b>NEW ZONE ID ".$res['id']." a.k.a. '".$res['geotab_id_name']."':</b></h3>
     		<br>GPS Center [".$res['long']." , ".$res['lat']."]
     		<br>".$res['conard_name']."
     		<br>".$res['address_1']."
     		<br>".$res['city'].", ".$res['state']." ".$res['zip']."
     		<br>W ".$res['long_zone_w']." to E ".$res['long_zone_e']." E
     		<br>N ".$res['lat_zone_n']." to ".$res['lat_zone_s']." S
     	";		
     	
     }
     else
     {	//found, so just use the one in hte library of zones.
     	echo "
     		<br><br>
     		<h3><b>ZONE ID ".$res['id']." a.k.a. '".$res['geotab_id_name']."':</b></h3>
     		<br>GPS Cetner [".$res['long']." , ".$res['lat']."]
     		<br>".$res['conard_name']."
     		<br>".$res['address_1']."
     		<br>".$res['city'].", ".$res['state']." ".$res['zip']."
     		<br>W ".$res['long_zone_w']." to E ".$res['long_zone_e']." E
     		<br>N ".$res['lat_zone_n']." to ".$res['lat_zone_s']." S
     	";	
     }
     */
     
     
     //echo "<br>Getting Zones:<br>".mrr_get_geotab_zones("","").".";
     
     $name="This is a test route";
     $notes="Using test stops as zones....making the ";
     //ZONE						//From Datetime									//To Datetime									//Date
     $route_info[0]["id"]="b1A86";		$route_info[0]["from"]="2018-03-19T13:00:00.000Z";		$route_info[0]["to"]="2018-03-19T14:00:00.000Z";		$route_info[0]["date"]="2018-03-19";		//Conard Command Center
     $route_info[1]["id"]="b1A87";		$route_info[1]["from"]="2018-03-19T15:30:00.000Z";		$route_info[1]["to"]="2018-03-19T16:30:00.000Z";		$route_info[1]["date"]="2018-03-19";		//Imperial Command Center
     $route_info[2]["id"]="b1A88";		$route_info[2]["from"]="2018-03-20T10:00:00.000Z";		$route_info[2]["to"]="2018-03-20T12:00:00.000Z";		$route_info[2]["date"]="2018-03-20";		//Imperial Command Bunker
     $route_info[3]["id"]="b1A89";		$route_info[3]["from"]="2018-03-21T08:30:00.000Z";		$route_info[3]["to"]="2018-03-21T09:30:00.000Z";		$route_info[3]["date"]="2018-03-21";		//Imperial Command Base
     
     //echo "<br>Adding Route: ".mrr_make_geotab_route($truck_id,$route_info,$name,$notes).".";
     
     //echo "<br>Getting Routes:<br>".mrr_get_geotab_routes("","").".";
     
     //echo "<br>Getting Rules:<br>".mrr_get_geotab_rules("","").".";		//aZp8Xj-72B0KG6VciYaCGjg
     /*
     $load_id=78498;
     $disp_id=90079;
     
     $load_id=78710;
     $disp_id=90114;
     
     $dres=mrr_send_geotab_complete_dispatch($load_id,$disp_id,$truck_id,1);
     echo "
     	<br><h3>Testing Dispatch ".$disp_id." (Load ".$load_id.") for Truck ID ".$truck_id.":</h3>
     	<br>Truck ID: ".$dres['truck_id']."
     	<br>Truck Name: ".$dres['truck_name']."
     	<br>Dispatch ID: ".$dres['dispatch_id']."
     	<br>Counter: ".$dres['dispatch_cntr']."
     	<br>Output: ".$dres['output']."
     	<br>GeoTab Dispatch ID: ".$dres['geotab_id'].".
     ";
     
     //$load_id=78701;
     
     $load_id=78720;
     $dres=mrr_send_geotab_complete_preplan_load($load_id,$truck_id,1,0,1);
     echo "
     	<br><h3>Testing PREPLANNED Load ".$load_id." for Truck ID ".$truck_id.":</h3>
     	<br>Truck ID: ".$dres['truck_id']."
     	<br>Truck Name: ".$dres['truck_name']."
     	<br>Load ID: ".$dres['load_id']."
     	<br>Counter: ".$dres['dispatch_cntr']."
     	<br>Output: ".$dres['output']."
     	<br>GeoTab Load ID: ".$dres['geotab_id'].".
     ";
     */
     
     $entity_name="Route";
     $entity_geotab_id="b2";
     //$killed=mrr_kill_geotab_route($entity_name,$entity_geotab_id);
     //echo "<br>Removed ".$entity_name." '".$entity_geotab_id."'... Result is ".($killed > 0 ? "successful" : "FAILED").".";
     
     //echo "<br>Getting Routes:<br>".mrr_get_geotab_routes("","").".";
     
     echo "
     <br>
     <br>
     Done.";
     
     /*
     $selection="";
     
     $selection=str_replace(" --- Value "," ",$selection);
     $selection=str_replace(". ID=","",$selection);
     $selection=str_replace(". (Version ","",$selection);
     $selection=str_replace(") Device b1 | "," ",$selection);
     $selection=str_replace("2018-05-03T","",$selection);
     $selection=str_replace(chr(9),chr(13),$selection);
     $selection=str_replace(chr(10),chr(13),$selection);
     
     $cntr=0;
     $arr[0]="";
     
     $sel=explode(chr(13),$selection);
     for($i=0; $i < count($sel); $i++)
     {
     	$stringlet=trim($sel[$i]);
     	if(trim($stringlet)!="")
     	{
     		$pos1=strpos($stringlet,"Diagnostic ID (Name) ");
     		$pos2=strpos($stringlet," DateTime ",$pos1);
     		$pos3=strpos($stringlet,"Z ",$pos2);
     		
     		$sub_name=substr($stringlet,$pos1,($pos2 - $pos1));		$sub_name=str_replace("Diagnostic ID (Name) ","",$sub_name);
     		$sub_date=substr($stringlet,$pos2,($pos3 - $pos2));		$sub_date=str_replace(" DateTime ","",$sub_date);
     		$sub_value=substr($stringlet,$pos3);					$sub_value=str_replace("Z ","",$sub_value);
     		
     		$founder=0;
     		for($j=0; $j < $cntr; $j++)
     		{
     			if(trim($arr[$j])==trim($sub_name))		$founder=1;
     		}
     		
     		if($founder==0)
     		{		
     			echo "<br>".($i+1).". ".$sub_name." --- ".$sub_date." --- ".$sub_value.".";
     			
     			$arr[$cntr]="".trim($sub_name)."";
     			$cntr++;
     		}
     	}
     }
     */
}
echo "<br><br>Done.<br>";
?>