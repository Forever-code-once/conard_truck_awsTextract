<?
// generic functions for tracking that work for PeopleNet and GeoTab.  Created on 3/27/2018...MRR
ini_set('allow_url_include', '1');
include_once("functions_geotab.php");
include_once("functions_geotab_usage.php");

function mrr_find_quick_customer_name($id)
{		
	$cust_name="";
	
	$sql = "
		select name_company
		from customers
		where id='".sql_friendly($id) ."'
	";
	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$cust_name=$row['name_company'];
	}		
	
	return $cust_name;	
}
function mrr_get_all_customer_settings($customer_id)
{
	$sql="
		select * 
		from customers
		where id='".sql_friendly($customer_id)."'				
	";
	$data=simple_query($sql);
	$row=mysqli_fetch_array($data);
	return $row;
}
function mrr_find_quick_truck_name($id)
{		
	$truck_name="";
	
	$sql = "
		select name_truck 
		from trucks
		where id='".sql_friendly($id) ."'
	";
	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$truck_name=$row['name_truck'];
	}		
	
	return $truck_name;	
}
function mrr_find_quick_trailer_name($id)
{		
	$trailer_name="";
	
	$sql = "
		select trailer_name 
		from trailers
		where id='".sql_friendly($id) ."'
	";
	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$trailer_name=$row['trailer_name'];
	}		
	
	return $trailer_name;	
}
function mrr_peoplenet_pull_quick_user_fullname($id)
{
	$username="";
	$sql = "
		select name_first,name_last
		from users
		where id='".sql_friendly($id) ."'
	";
	$data = simple_query($sql);	
	if($row = mysqli_fetch_array($data))
	{
		$username=trim($row['name_first']." ".$row['name_last']);
	}
	return $username;
}
function mrr_peoplenet_pull_quick_username($id)
{
	$username="";
	$sql = "
		select username
		from users
		where id='".sql_friendly($id) ."'
	";
	$data = simple_query($sql);	
	if($row = mysqli_fetch_array($data))
	{
		$username=$row['username'];
	}
	return $username;
}

function mrr_find_peoplenet_trucks_on_load($load_id)
{	//set flag of each truck on PeopleNet tracking
	$arr[0]=0;
	$names[0]="";
	$pn[0]=0;
	$geotab[0]="";
	$cntr=0;
	
	$sql = "
		select truck_id,
			name_truck,
			peoplenet_tracking,
			geotab_device_id
		from trucks_log,trucks
		where load_handler_id='".sql_friendly($load_id) ."'
			and trucks_log.truck_id=trucks.id
		order by truck_id asc
	";
	
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$truck_id=$row['truck_id'];
		$found=0;
		for($x=0;$x < $cntr; $x++)
		{
			if($arr[ $x ]==$row['truck_id'])	$found=1;
		}
		if($found==0)
		{
			$arr[ $cntr ]=$row['truck_id'];
			$pn[ $cntr ]=$row['peoplenet_tracking'];
			$names[ $cntr ]=$row['name_truck'];
			$geotab[ $cntr ]="".trim($row['geotab_device_id'])."";
			$cntr++;
		}
	}
	
	$res['num']=$cntr;
	$res['arr']=$arr;
	$res['names']=$names;
	$res['pn']=$pn;
	$res['geotab']=$geotab;
	return $res;
}


function mrr_distance_between_gps_points($lat1="0",$lon1="0",$lat2="0",$lon2="0",$cd=0)
{
	$distance=0;
	
	$earth_radius = 3960.00; //# in miles
	if($lat2=="0")		$lat2 = "36.002087";
	if($lon2=="0")		$lon2 = "-86.596649";
	$delta_lat = $lat2 - $lat1 ;
	$delta_lon = $lon2 - $lon1 ;
	
	//Spherical Law of Cosines
	$distance  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($delta_lon)) ;
	$distance  = acos($distance);
	$distance  = rad2deg($distance);
	$distance  = $distance * 60 * 1.1515;
	
	if($cd==1)	$distance  = $distance * 5280;	//convert to ft instead of miles...
	
	$distance  = round($distance, 4);		
	
	return $distance;
}


function mrr_encode_geofencing_grade($grade = "")
{
	$grade=trim($grade);	$id=0;
	if($grade=="")			$id=0;
	if($grade=="Early")		$id=1;
	if($grade=="On Time")	$id=2;
	if($grade=="Late")		$id=3;
	if($grade=="Very Late")	$id=4;
	if($grade=="Epic Fail")	$id=5;
	return $id;
}
function mrr_decode_geofencing_grade($id = 0)
{		
	$id=(int) $id;		$grade="";
	if($id==0)		$grade="";
	if($id==1)		$grade="Early";
	if($id==2)		$grade="On Time";
	if($id==3)		$grade="Late";
	if($id==4)		$grade="Very Late";
	if($id==5)		$grade="Epic Fail";
	return $grade;
}



function mrr_gen_truck_local_map_by_google($load,$truckname,$truck_lat,$truck_long,$truck_id=0)
{
	$marker_image="images/2012/mrr_truck.png";	//images/truck_info.png
	$map_object="load_".$load."_map_holder";
	$home_lat="36.001156";
	$home_long="-86.597328";		
	$zoom_level=3;
	
	$pointer_type="google.maps.SymbolPath.CIRCLE";
	/*
	Symbol Types
	google.maps.SymbolPath.BACKWARD_CLOSED_ARROW 	A backward-pointing closed arrow.
	google.maps.SymbolPath.BACKWARD_OPEN_ARROW 		A backward-pointing open arrow.
	google.maps.SymbolPath.CIRCLE 				A circle.
	google.maps.SymbolPath.FORWARD_CLOSED_ARROW 		A forward-pointing closed arrow.
	google.maps.SymbolPath.FORWARD_OPEN_ARROW 		A forward-pointing open arrow.	
	*/
	
	$map_type="google.maps.MapTypeId.ROADMAP";	
	/*
	Map Types:		
	google.maps.MapTypeId.ROADMAP 				displays the default road map view
	google.maps.MapTypeId.SATELLITE 				displays Google Earth satellite images
	google.maps.MapTypeId.HYBRID 					displays a mixture of normal and satellite views
	google.maps.MapTypeId.TERRAIN 				displays a physical map based on terrain information. 
	*/ 
	
	$map_holder="";
	if($truck_id > 0)
	{
		$truck_map=mrr_map_generator($truck_id,$truck_lat,$truck_long);
		$map_holder="<br><iframe class='mrr_load_board_map_style' id='".$map_object."' src='".$truck_map."'></iframe>";
	}     	
	return $map_holder;
}
function mrr_map_generator($truck_id=0,$truck_lat="",$truck_long="")
{
	$lat="36.002396";
	$long="-86.597351";
	
	if($truck_id>0 && trim($truck_lat)=="" && trim($truck_long)=="")
	{
		$sql2 = "
			select latitude,longitude
			from ".mrr_find_log_database_name()."truck_tracking
			where truck_tracking.truck_id='".sql_friendly($truck_id) ."'
			order by truck_tracking.linedate desc
			limit 1
		";
		$data2 = simple_query($sql2);
		$mn2=mysqli_num_rows($data2);
		if($row2 = mysqli_fetch_array($data2))
		{
			$lat=$row2['latitude'];
			$long=$row2['longitude'];				
		}	
	}		
	else
	{
		$lat=trim($truck_lat);
		$long=trim($truck_long);	
	}		
	
	//https://maps.google.com/maps?q=36.002396,-86.597282&hl=en&sll=38.762226,-78.62928&sspn=0.007086,0.016512&t=m&z=17
	$linker="https://maps.google.com/maps?q=".trim($lat).",".trim($long)."&hl=en&sll=35.830521,-85.978599&sspn=3.771731,8.453979&t=m&z=17";
	
	//https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=38.762226,-78.629280&amp;aq=&amp;sll=35.830521,-85.978599&amp;sspn=3.771731,8.453979&amp;ie=UTF8&amp;t=m&amp;z=14&amp;ll=38.762226,-78.62928&amp;output=embed		
	$iframe="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=".trim($lat).",".trim($long)."&amp;aq=&amp;ie=UTF8&amp;t=m&amp;z=14&amp;ll=".trim($lat).",".trim($long)."&amp;output=embed";
		
	return $iframe;
}

function mrr_find_pn_truck_drivers($truck_id,$mydate,$cd=0)
{			
	$res['load_id']=0;
	$res['dispatch_id']=0;
	$res['driver_id_1']=0;
	$res['driver_id_2']=0;
	$res['driver_name_1']="";
	$res['driver_name_2']="";
	$res['driver_user_1']="";
	$res['driver_user_2']="";		
	$res['sql_1']="";
	$res['sql_2']="";		
	
	$drivers="";
	//fetch from dispatch
	$sql="
		select trucks_log.id,
			trucks_log.load_handler_id,
			trucks_log.driver_id,
			trucks_log.driver2_id,
			d1.name_driver_first,
			d1.name_driver_last,
			d1.geotab_use_id,
			d2.name_driver_first as name_driver_first2,
			d2.name_driver_last as name_driver_last2,
			d2.geotab_use_id as geotab_use_id2 
		from trucks_log
			left join drivers d1 on d1.id=trucks_log.driver_id
			left join drivers d2 on d2.id=trucks_log.driver2_id
		where trucks_log.deleted<=0
			and truck_id='".sql_friendly($truck_id)."'
			and linedate_pickup_eta<='".$mydate." 00:00:00' 
			and linedate_dropoff_eta>='".$mydate." 00:00:00'
		order by linedate_pickup_eta desc 
		limit 1	
	";
	$res['sql_1']=$sql;
	$data=simple_query($sql);		
	if($row = mysqli_fetch_array($data)) 	
	{
		$res['driver_id_1']=$row['driver_id'];
		$res['driver_id_2']=$row['driver2_id'];
		
		$res['load_id']=$row['load_handler_id'];
		$res['dispatch_id']=$row['id'];
		
		$driver1="";
		$driver2="";
		
		if($row['driver_id'] > 0)		$driver1="<a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."</a> ";
		if($row['driver2_id'] > 0)		$driver2="<a href='admin_drivers.php?id=".$row['driver2_id']."' target='_blank'>".trim($row['name_driver_first2'])." ".trim($row['name_driver_last2'])."</a> ";
		
		$res['driver_name_1']=$driver1;
		$res['driver_name_2']=$driver2;
		
		$res['driver_user_1']="".trim($row['geotab_use_id'])."";
		$res['driver_user_2']="".trim($row['geotab_use_id2'])."";
		
		$drivers="(from Dispatch ".$row['id'].") ".trim($driver1);
		if(trim($driver1)!="" && trim($driver2)!="" && trim($driver1)!=trim($driver2))		$drivers.=" and ".trim($driver2)."";
	}
	
	//if not found, use the basic attachment as the default.
	if(trim($drivers)=="")
	{
		$sql = "
			select drivers.* 
			from drivers
			where attached_truck_id='".sql_friendly($truck_id) ."'
				and deleted<=0
				and active > 0
				and night_shifter <= 0
			order by name_driver_last asc,
				name_driver_first asc
		";     	
		$res['sql_2']=$sql;	
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$res['driver_id_1']=$row['id'];
			$res['driver_id_2']=0;
		
			$res['load_id']=0;
			$res['dispatch_id']=0;
		
			$driver1="<a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."</a> ";
			if(trim($drivers)=="")		$drivers="(from driver attachment) ".$driver1;
			//else						$drivers.=", ".$driver1;
		
			$res['driver_name_1']=$driver1;
			$res['driver_name_2']="";
			
			$res['driver_user_1']="".trim($row['geotab_use_id'])."";
			$res['driver_user_2']="";
		}
	}	
	if($cd==1)
	{
		return $res;
	}
	return $drivers;	
}
function mrr_find_night_shift_driver_for_truck($truck_id)
{
	$driver_names="";
	if($truck_id==0)		return $driver_names;
	
	if($truck_id > 0)
	{
		$sql = "
			select drivers.* 
			from drivers
			where (attached_truck_id='".sql_friendly($truck_id) ."' or attached2_truck_id='".sql_friendly($truck_id) ."')
				and deleted<=0
				and active > 0
				and night_shifter > 0
			order by name_driver_last asc,
				name_driver_first asc
		";	
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{		
			$driver_names.="<a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."</a> ";
		}
	}	
	
	return $driver_names;
}
function mrr_find_quick_load_string($id)
{		
	$str="";
	
	$sql = "
		select * 
		from load_handler
		where id='".sql_friendly($id) ."'
	";
	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$row['special_instructions']=strip_tags($row['special_instructions']);
		
		$str.="TESTING General Information for your load. Please see Stop for Special Instructions:"; 
		$str.="<br>Conard Logistics Load ID: ".$row['id'].""; 
		$str.="<br>Customer Load No:".$row['load_number'].""; 
		$str.="<br>Load PU:".$row['pickup_number'].""; 
		$str.="<br>Load Del:".$row['delivery_number'].""; 
		$str.="<br>"; 
		$str.="<br>Origin: ".$row['origin_city'].", ".$row['origin_state'].""; 
		$str.="<br>Destination: ".$row['dest_city'].", ".$row['dest_state'].""; 
		$str.="<br>Pick Up ETA: ".date("m-d-y H:i",strtotime($row['linedate_pickup_eta'])).""; 
		$str.="<br>Drop Off ETA: ".date("m-d-y H:i",strtotime($row['linedate_dropoff_eta'])).""; 
		$str.="<br>"; 
		$str.="<br>General Load Instructions: ".$row['special_instructions'].""; 
		//$str.="<br>"; 
	}		
	$str=str_replace("&"," and ",$str);
	$str=str_replace("#"," No.",$str);
	return trim($str);	
}
function mrr_find_quick_load_string_alt($id)
{		
	$str="";
	
	$sql = "
		select * 
		from load_handler
		where id='".sql_friendly($id) ."'
	";
	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$row['special_instructions']=strip_tags($row['special_instructions']);
		
		$str.="TESTING General Information for your load. Please see Stop for Special Instructions:"; 
		$str.="<br>Conard Logistics Load ID: ".$row['id'].""; 
		$str.="<br>"; 
		$str.="<br>"; 
		$str.="<br>Load Del:".$row['delivery_number'].""; 
		$str.="<br>"; 
		$str.="<br>Origin: ".$row['origin_city'].", ".$row['origin_state'].""; 
		$str.="<br>Destination: ".$row['dest_city'].", ".$row['dest_state'].""; 
		$str.="<br>Pick Up ETA: ".date("m-d-y H:i",strtotime($row['linedate_pickup_eta'])).""; 
		$str.="<br>Drop Off ETA: ".date("m-d-y H:i",strtotime($row['linedate_dropoff_eta'])).""; 
		$str.="<br>"; 
		$str.="<br>General Load Instructions: ".$row['special_instructions'].""; 
		//$str.="<br>"; 
		
		$str=" Load No:".$row['load_number']." PU:".$row['pickup_number']." Instr: ".$row['special_instructions'].""; 
	}		
	$str=str_replace("&"," and ",$str);
	$str=str_replace("#"," No.",$str);
	return trim($str);	
}
function mrr_find_quick_load_string_special($id)
{		
	$str="";
	
	$sql = "
		select * 
		from load_handler
		where id='".sql_friendly($id) ."'
	";
	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$row['special_instructions']=strip_tags($row['special_instructions']);
		
		//$str.="TESTING Conard Load ".$row['id'].""; 
		$str.="-Load Del:".$row['delivery_number']."-Load No:".$row['load_number']."-PU:".$row['pickup_number'].""; 
		$str.="-Origin: ".$row['origin_city'].", ".$row['origin_state'].""; 
		$str.="-Destination: ".$row['dest_city'].", ".$row['dest_state'].""; 
		$str.="-Pick Up ETA: ".date("m-d-y H:i",strtotime($row['linedate_pickup_eta'])).""; 
		$str.="-Drop Off ETA: ".date("m-d-y H:i",strtotime($row['linedate_dropoff_eta'])).""; 
		$str.="-General Load Instructions: ".$row['special_instructions'].""; 
		//$str.="<br>"; 
		
		//$str=" Instr: ".$row['special_instructions'].""; 
	}		
	$str=str_replace("&"," and ",$str);
	$str=str_replace("#"," No.",$str);
	return trim($str);	
}


function mrr_find_us_zip_code_address($zip,$state)
{
	$location="";
	$zip=trim($zip);
	if($zip!="" || $zip!="0")
	{
		$url="https://tools.usps.com/go/ZipLookupResultsAction!input.action?resultMode=2&companyName=&address1=&address2=&city=&state=Select&urbanCode=&postalCode=".$zip."&zip=";	
		
		ob_end_clean();
		ob_start();
		include($url);			
		$buffer=ob_get_contents();			
		ob_end_clean();
		
		$pose1=strpos($buffer,'<p class="std-address">');
		if($pose1>0)
		{
			$pose2=strpos($buffer,'</p>',$pose1);
			
			$location=trim(substr($buffer,$pose1,($pose2 - $pose1)));
			$location=str_replace('<p class="std-address">',"",$location);
		}
		
		$buffer="";
	}
	$resval="".$zip." is <span class='good_alert'>".$location."</span>";
	if(substr_count($location, " ".strtoupper($state)."") == 0 && trim($state)!="")
	{
		$resval="".$zip." is <span class='alert'><b>".$location."</b></span>";	
	}
	
	return $resval;
}


function mrr_update_stop_GPS_timezones()
{
	$res="<table cellpadding='0' cellspacing='0' border='0' width='1400'>
			<tr>
				<td valign='top' width='100'><b>No.</b></td>
				<td valign='top' width='100'><b>Load</b></td>
				<td valign='top' width='100'><b>Dispatch</b></td>
				<td valign='top' width='100'><b>Stop</b></td>
				<td valign='top' width='100'><b>Lat</b></td>
				<td valign='top' width='100'><b>Long</b></td>
				<td valign='top' width='100'><b>Offset</b></td>
				<td valign='top' width='100' nowrap><b>DST Offset</b></td>
				<td valign='top' width='100'><b>TimeZone</b></td>
				<td valign='top' width='100'><b>LocalTime</b></td>
			</tr>
	";
	$cntr=0;
	$now=time();
	
	$sql="
		select id,
			latitude,
			longitude,
			load_handler_id,
			trucks_log_id
		from load_handler_stops
		where linedate_pickup_eta>='2013-01-01 00:00:00'
			and deleted<=0
			and (
				linedate_completed is null 
				or linedate_completed='0000-00-00 00:00:00'
			)
			and (
				latitude !='0.000000'
				or longitude !='0.000000'     				
			)
			and (
				linedate_last_timezone='0000-00-00 00:00:00'
				or linedate_last_timezone < linedate_updater
			)
		order by linedate_pickup_eta asc, id asc		
	";	
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$load_id=$row['load_handler_id'];
		$disp_id="N/A";
		if(isset($row['trucks_log_id']))
		{
			$disp_id=$row['trucks_log_id'];	
		}
		$stop_id=$row['id'];
		$lat=$row['latitude'];
		$long=$row['longitude'];
		
		$offset=0;
		$offset_dst=0;
		$offset_name="";
		if($lat!=0 || $long!=0)
		{
     		$cntr++;
     		
     		$url="maps.googleapis.com/maps/api/timezone/xml?location=".$lat.",".$long."&timestamp=".$now."&sensor=false";	//
     		$mrr_res=mrr_fetch_google_gps_timezone($url,$lat,$long,$now);
     		
     		if($mrr_res['status'] == "OK")
     		{
     			$offset=(int) $mrr_res['raw'];
				$offset_dst=(int) $mrr_res['dst'];
				$offset_name=trim($mrr_res['name']);
     		}
     		
     		$local_timer=$now + $offset + $offset_dst;		//local time = timestamp + offset + dst_offset
     		
     		$res.="
     			<tr>
     				<td valign='top'>".$cntr."</td>
     				<td valign='top'>".$load_id."</td>
     				<td valign='top'>".$disp_id."</td>
     				<td valign='top'>".$stop_id."</td>
     				<td valign='top'>".$lat."</td>
     				<td valign='top'>".$long."</td>
     				<td valign='top'>".$offset."</td>
     				<td valign='top'>".$offset_dst."</td>
     				<td valign='top'>".$offset_name."</td>
     				<td valign='top'>".date("m/d/Y H:i",$local_timer)."</td>
     			</tr>
     		";	//$url
     		
     		$sqlu="
     			update load_handler_stops set
     				timezone_offset='".sql_friendly($offset)."',
     				timezone_offset_dst='".sql_friendly($offset_dst)."',
     				linedate_last_timezone=NOW()
     			where id='".sql_friendly($stop_id)."'
     		";
     		simple_query($sqlu);
     		
		}
	}
	$res.="</table>";
	return $res;
}


function mrr_fetch_google_gps_timezone($url,$lat,$long,$now)
{	
	$page="";
	
	if(trim($url)!="")
	{
		$page=mrr_generic_get_file_contents($url);
	}
	$html="<br>Page Below:<br>=====<br>".$url."<br>".$page."<br>=====<br>";
	
	$res=trim($page);
	$res=str_replace("<TimeZoneResponse>","",$res);
	$res=str_replace("</TimeZoneResponse>","",$res);
	$res=trim($res);		
	     	
	$status="";
	$raw="";
	$dst="";
	$zone="";
	$name="";
	     	
	if(substr_count($res,"status") > 0)
	{
		$pos1=strpos($res,"<status>");
		$pos2=strpos($res,"</status>",$pos1+5);	
		$tmp=substr($res,$pos1,($pos2 - $pos1));
		$tmp=str_replace("</","",$tmp);
		$tmp=str_replace("<","",$tmp);
		$tmp=str_replace(">","",$tmp);
		$tmp=str_replace("status","",$tmp);
		$status=trim($tmp);
	}
	if(substr_count($res,"raw_offset") > 0)
	{
		$pos1=strpos($res,"<raw_offset>");
		$pos2=strpos($res,"</raw_offset>",$pos1+5);	
		$tmp=substr($res,$pos1,($pos2 - $pos1));
		$tmp=str_replace("</","",$tmp);
		$tmp=str_replace("<","",$tmp);
		$tmp=str_replace(">","",$tmp);
		$tmp=str_replace("raw_offset","",$tmp);
		$raw=trim($tmp);
	}
	if(substr_count($res,"dst_offset") > 0)
	{
		$pos1=strpos($res,"<dst_offset>");
		$pos2=strpos($res,"</dst_offset>",$pos1+5);	
		$tmp=substr($res,$pos1,($pos2 - $pos1));
		$tmp=str_replace("</","",$tmp);
		$tmp=str_replace("<","",$tmp);
		$tmp=str_replace(">","",$tmp);
		$tmp=str_replace("dst_offset","",$tmp);
		$dst=trim($tmp);
	}
	if(substr_count($res,"time_zone_id") > 0)
	{
		$pos1=strpos($res,"<time_zone_id>");
		$pos2=strpos($res,"</time_zone_id>",$pos1+5);
		$tmp=substr($res,$pos1,($pos2 - $pos1));
		$tmp=str_replace("</","",$tmp);
		$tmp=str_replace("<","",$tmp);
		$tmp=str_replace(">","",$tmp);
		$tmp=str_replace("time_zone_id","",$tmp);
		$zone=trim($tmp);
	}
	if(substr_count($res,"time_zone_name") > 0)
	{
		$pos1=strpos($res,"<time_zone_name>",$tstart);
		$pos2=strpos($res,"</time_zone_name>",$pos1+2);
		$tmp=substr($res,$pos1,($pos2 - $pos1));
		$tmp=str_replace("</","",$tmp);
		$tmp=str_replace("<","",$tmp);
		$tmp=str_replace(">","",$tmp);
		$tmp=str_replace("time_zone_name","",$tmp);
		$name=trim($tmp);	
	}
	    		
	$mrr_res['html']=$html;
	$mrr_res['status']=strtoupper($status);
	$mrr_res['raw']=$raw;
	$mrr_res['dst']=$dst;
	$mrr_res['zone']=$zone;
	$mrr_res['name']=$name;
		
	return $mrr_res;
}	

function mrr_generic_get_file_contents($prime_url)
{	
	$curl_handle=curl_init();
	
	curl_setopt($curl_handle, CURLOPT_URL,$prime_url);
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); // we don't want to stop the page from loading if there is ever an issue with the SSL 
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);
	
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);			
	return $buffer;
}

function mrr_fetch_gps_timezone($lat,$long)
{
	//kept only if used elsewhere...
	$res['time']='0000-00-00 00:00:00';
	$res['html']="";
	return $res;
}
function mrr_decode_proper_timezone_label($gmt_offset,$dst_offset)
{	//GMT Offsets....  Add 3600 seconds if DST to make it 1 hour earlier.
	/*	
	AST=-14400
	EST=-18000
	CST=-21600
	MST=-25200
	PST=-28800		
	*/
	$label="";
	
	if($gmt_offset==-14400)		$label="AST";
	if($gmt_offset==-18000)		$label="EST";
	if($gmt_offset==-21600)		$label="CST";
	if($gmt_offset==-25200)		$label="MST";
	if($gmt_offset==-28800)		$label="PST";
	
	if($dst_offset==3600)		$label=str_replace("S","D",$label);
	
	return $label;
}
function mrr_compute_days_diff_by_dates($date_from="",$date_to="")
{
	$days=0;
	if($date_from=="")		$date_from=date("Y-01-01",time());
	if($date_to=="")		$date_to=date("Y-12-31",time());
	
	$sql = "select DATEDIFF('".date("Y-m-d",strtotime($date_to))."','".date("Y-m-d",strtotime($date_from))."') as mrr_days";  	
	$data=simple_query($sql); 	
	if($row=mysqli_fetch_array($data))
	{
		$days=$row['mrr_days'];	
	}
	return $days;
}

function mrr_test_drive_gps_distance($truck_id,$truck_name,$site_lat=0,$site_long=0,$gps_lat=0,$gps_long=0)
{		
	global $defaultsarray;		
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];
	$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
	
	$resx="";
	$munits=5280;
	$tunits=60;
	
	$resx.=" <div>Truck ID='".$truck_id."', Truck Name='".$truck_name."'.</div>"; 	
	$resx.=" <div>Current GPS (Lat,Long): ".$gps_lat.",".$gps_long.".</div>"; 	
	$resx.=" <div>Dest GPS (Lat,Long): ".$site_lat.",".$site_long.".</div>"; 	
	
	$gps_dist=mrr_distance_between_gps_points($site_lat,$site_long,$gps_lat,$gps_long);		//has MILES...
	$gps_dist=abs($gps_dist);
	$overall_hrs= round(($gps_dist / $mph), 4);
	$overall_min= round(($gps_dist / $mph * $tunits), 4);
	$gps_dist_feet=$gps_dist * $munits;					//convert distance in miles to feet....
	
	$resx.=" <div>Distance: ".$gps_dist." Miles = ".$gps_dist_feet." Feet.</div>"; 
	$resx.=" <div>Time: ".$overall_hrs." Hours or ".$overall_min." Minutes. </div>"; 
			
	$resx.=" <div>&nbsp;</div>"; 	
	$resx.=" <div>&nbsp;</div>"; 	
	return $resx;	
}

function mrr_check_if_dispatch_driver_confirm_email_sent($disp_id)
{
	$driver_confirm_email_sent=1;
	if($disp_id > 0)
	{
		$sql="
     		select driver_confirm_email_sent
     		from trucks_log
     		where id='".sql_friendly($disp_id)."'     		
     	";	
     	$data = simple_query($sql);
          if($row = mysqli_fetch_array($data)) 
          {
          	$driver_confirm_email_sent=$row['driver_confirm_email_sent'];
          }
     }
     return $driver_confirm_email_sent;	
}

//REPAIR REQUEST FORMS
function mrr_auto_create_maint_request($type,$equip_id,$desc,$urgent,$odom,$scheduled,$local="")
{
	global $defaultsarray;
	global $datasource;
	
	$mydate=date("m/d/Y");
	if(trim($desc)=="")		$desc="Maintenance Request ".$mydate.".";
	
	//first check if the maint_request already exists...
     $sql="
          select *
          from maint_requests
          where maint_desc='".sql_friendly($desc)."'
               and equip_type='".sql_friendly($type)."'
			and ref_id='".sql_friendly($equip_id)."'
			and deleted=0
            order by id desc
     ";
     $data=simple_query($sql);
     if($row = mysqli_fetch_array($data))
     {
          //return $row['id'];       //already saved, so no need to add the request again.  Same truck or trailer, and same desc.
     }
	
     if($type==58 && $equip_id==0)      return 0;
     
	
	$sql = "
		insert into maint_requests
			(id,
			linedate_added,
			maint_desc,
			recur_days,
			recur_mileage,
			recur_flag,
			recur_ref,
			linedate_scheduled,
			linedate_completed,
			odometer_reading,
			urgent,	
			equip_type,
			ref_id,
			active,
			user_id,
			cur_location,
			down_time_hours,
			cost,
			deleted)
				
		values (NULL,
			now(),
			'".sql_friendly($desc)."',
			0,
			0,
			0,
			0,
			'".date("Y-m-d H:i:s",strtotime($scheduled))."',
			'0000-00-00 00:00:00',
			'".sql_friendly($odom)."',
			'".sql_friendly($urgent)."',
			'".sql_friendly($type)."',
			'".sql_friendly($equip_id)."',
			1,
			1,
			'".sql_friendly(trim($local))."',
			'0.00',
			'0.00',
			0)
	";			
	simple_query($sql);
	$req_id = mysqli_insert_id($datasource);
	if($req_id > 0)
	{
		$send_email=1;
		
		if($send_email ==1)
		{
			$send_to=$defaultsarray['company_email_address'];
			//$send_to=$defaultsarray['peoplenet_hot_msg_cc'];
						
			$equip_type=get_option_name_by_id($type);				
			$name=identify_truck_trailer($type , $equip_id);
			
			$updater_label="added";
						
			$req_date="Request Scheduled for ".date("m/d/Y", strtotime($scheduled)).".";
			
			$subj="Maintenance Request ".$req_id." has been ".$updater_label.".";
			$msg1="Maintenance request has been ".$updater_label.".  ".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id." ".$equip_type." #".$name.". ".$req_date." for ".$desc.".";
			$msg2="Maintenance request has been ".$updater_label.":  <a href='".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id."'>".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id."</a> <b>".$equip_type." #".$name."</b> for <b>".$desc."</b>.".$req_date."";
			
			mrr_trucking_sendMail($send_to,'Dispatch',$defaultsarray['company_email_address'],$defaultsarray['company_name'],'','',$subj,$msg1,$msg2);
			mrr_trucking_sendMail('Bfinley@conardtransportation.com','Bfinley',$defaultsarray['company_email_address'],$defaultsarray['company_name'],'','',$subj,$msg1,$msg2);			
			mrr_trucking_sendMail('dconard@conardtransportation.com','Dale Coanrd',$defaultsarray['company_email_address'],$defaultsarray['company_name'],'','',$subj,$msg1,$msg2);
			
		}	
		if($send_email ==2)
		{	//TESTING...mode 2.
			$send_to=$defaultsarray['company_email_address'];
			//$send_to=$defaultsarray['peoplenet_hot_msg_cc'];
						
			$equip_type=get_option_name_by_id($type);				
			$name=identify_truck_trailer($type , $equip_id);
			
			$updater_label="added";
						
			$req_date="Request Scheduled for ".date("m/d/Y", strtotime($scheduled)).".";
			
			$subj="Maintenance Request ".$req_id." has been ".$updater_label.".";
			$msg1="Maintenance request has been ".$updater_label.".  ".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id." ".$equip_type." #".$name.". ".$req_date." for ".$desc.".";
			$msg2="Maintenance request has been ".$updater_label.":  <a href='".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id."'>".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id."</a> <b>".$equip_type." #".$name."</b> for <b>".$desc."</b>.".$req_date."";
						
			mrr_trucking_sendMail($defaultsarray['special_email_monitor'],'Master Jedi',$defaultsarray['company_email_address'],$defaultsarray['company_name'],'','',$subj,$msg1,$msg2);
		}	
	}	
	return $req_id;
}


function mrr_pc_miler_distance($zip1=0,$zip2=0,$hub_run=0)
{	
	/*
	Function commented out so the license does not keep getting dropped...MRR Jan 2014
	*/
	
	//use formula much like pcmiler ajax page to get to the distance via roads between stops (zip-code listing)		
	$miles=-1;
	/*
	
	//global $mrr_pcm;
	$mrr_pcm = new COM("PCMServer.PCMServer");		//inside function to have local call
	
	//get two zips into formula expected...
	$zip_listing="".$zip1.",".$zip2."";
	$ziparray = explode(",",$zip_listing);
	
	if(isset($mrr_pcm))
	{
		$traveldist = 0;
		$travel_time = 0;
		$stoparray_dist = array();
		$stoparray_time = array();
		
		try {
			$trip = $mrr_pcm->NewTrip("NA");
			
			$last_zip = "";
			$stop_counter = 0;
			$counter = 0;
			$first_stop = "";
			$stop_minutes = 0;
			foreach($ziparray as $zip) {
				if($first_stop == '' && $zip != '') $first_stop = $zip;
				if($zip != '') {
					if($stop_counter) {
						if($hub_run == '1') {
							$stoparray_dist[$counter] = $mrr_pcm->CalcDistance3($first_stop, $zip, 0, $stop_minutes) / 10;
						} else {
							$stoparray_dist[$counter] = $mrr_pcm->CalcDistance3($last_zip, $zip, 0, $stop_minutes) / 10;
						}
					}
					$trip->AddStop($zip);
					$last_zip = $zip;
					$stop_counter++;
				}
				$counter++;
			}
			
			$options = $trip->GetOptions();
			$options->RouteType = 0;
			$options->Hub = ($hub_run == '1' ? true : false);
			
			$traveldist = $trip->TravelDistance() / 10;
			$travel_time = $trip->TravelTime() / 60.0;
		
			$miles=abs($traveldist);
			
		} catch (Exception $e) {
			//no need to do anything...  if miles still -1...error in function or PC Miler...	
		}
	}	
	*/	
	return $miles;
}


function mrr_quick_update_stop_pro_miles($stop_id,$miles,$eta_hrs,$due_hrs)
{
	$sql="
		update load_handler_stops set
			pro_miles_dist='".sql_friendly($miles)."',
			pro_miles_eta='".sql_friendly($eta_hrs)."',
			pro_miles_due='".sql_friendly($due_hrs)."'			
		where id='".sql_friendly($stop_id)."'		
     ";
     simple_query($sql);	
}
function mrr_promiles_get_file_contents($lat1,$long1,$lat2,$long2)
{	
	$miles=0;
	return $miles;
	/*
	$prime_url="http://prime.promiles.com/Webservices/v1_1/PRIMEStandardV1_1.asmx?wsdl";
	$cmd = "GetTripDistance";
	
	$credentials=mrr_get_promiles_credentials();
	
	$trip_legs['TripLeg'][0]['ProMilesLocationID']="0";
	$trip_legs['TripLeg'][0]['State']="";
	$trip_legs['TripLeg'][0]['PostalCode']="";
	$trip_legs['TripLeg'][0]['Latitude']="".$lat1."";
	$trip_legs['TripLeg'][0]['Longitude']="".$long1."";
	$trip_legs['TripLeg'][0]['LocationText']="";
	$trip_legs['TripLeg'][0]['Label']="";
	$trip_legs['TripLeg'][0]['Type']="PROMILES";
	$trip_legs['TripLeg'][0]['LocationProperties']="";
	$trip_legs['TripLeg'][0]['PerMileRate']="0";
	$trip_legs['TripLeg'][0]['FlatRate']="0";
	$trip_legs['TripLeg'][0]['Comments']="";
	
	$trip_legs['TripLeg'][1]['ProMilesLocationID']="0";
	$trip_legs['TripLeg'][1]['State']="";
	$trip_legs['TripLeg'][1]['PostalCode']="";
	$trip_legs['TripLeg'][1]['Latitude']="".$lat2."";
	$trip_legs['TripLeg'][1]['Longitude']="".$long2."";
	$trip_legs['TripLeg'][1]['LocationText']="";
	$trip_legs['TripLeg'][1]['Label']="";
	$trip_legs['TripLeg'][1]['Type']="PROMILES";
	$trip_legs['TripLeg'][1]['LocationProperties']="";
	$trip_legs['TripLeg'][1]['PerMileRate']="0";
	$trip_legs['TripLeg'][1]['FlatRate']="0";
	$trip_legs['TripLeg'][1]['Comments']="";
	
			
	$basictrip['RoutingMethod'] = 'PRACTICAL';
	$basictrip['BorderOpen'] = 'true';
	$basictrip['AvoidTollRoads'] = 'false';
	$basictrip['TripMiles'] = '0';
	$basictrip['TripMinutes'] = '0';
	$basictrip['TripLegs']=$trip_legs;
	$basictrip['VehicleType'] = 'Tractor3AxleTrailer2Axle';
	$basictrip['AllowRelaxRestrictions'] = 'false';
	$basictrip['HasRelaxedRestrictions'] = 'false';
	$basictrip['TollCharges'] = '0';
	$basictrip['TripCharges'] = '0';
	
	$basictrip['TripStartDate'] = '0001-01-01T00:00:00';
	$basictrip['TripEndDate'] = '0001-01-01T00:00:00';
	$basictrip['UnitMPG'] = '0';
	
	$basictrip['GetMapPoints'] = 'false';
	$basictrip['StartOdometer'] = '0.00';
	$basictrip['EndOdometer'] = '0.00';
	$basictrip['GetStaticTripMap'] = 'false';
	
	$basictrip['ResponseStatus'] = 'SUCCESS';
	
	
	$params['c'] = $credentials;
	$params['Trip'] = $basictrip;
	
	try {
		//echo "<pre>";
		//var_dump($params);
		//echo "</pre>";
		
		$client = new SoapClient($prime_url);
		
		$xml = $client->__soapCall($cmd, array($params));
	} catch (Exception $e) {
		//echo "Exception: ";
		//var_dump($e->__toString());
	}
	
	//echo "<pre>";
	//var_dump($xml);
	//echo "</pre>";
	
	return (string)$xml->GetTripDistanceResult;
	*/
}


function mrr_promiles_get_file_contents_multi_leg($legs,$gps)
{			
	$miles='0';
	return $miles;
	
	/*
	$prime_url="http://prime.promiles.com/Webservices/v1_1/PRIMEStandardV1_1.asmx?wsdl";
	$cmd = "GetTripDistance";
	
	$credentials=mrr_get_promiles_credentials();
	
	$trip_legs['TripLeg'][ 0 ]['ProMilesLocationID']="0";
	$trip_legs['TripLeg'][ 0 ]['State']="";
	$trip_legs['TripLeg'][ 0 ]['PostalCode']="";
	$trip_legs['TripLeg'][ 0 ]['Latitude']="".$gps[ 0 ]['lat']."";
	$trip_legs['TripLeg'][ 0 ]['Longitude']="".$gps[ 0 ]['long']."";
	$trip_legs['TripLeg'][ 0 ]['LocationText']="";
	$trip_legs['TripLeg'][ 0 ]['Label']="";
	$trip_legs['TripLeg'][ 0 ]['Type']="PROMILES";
	$trip_legs['TripLeg'][ 0 ]['LocationProperties']="";
	$trip_legs['TripLeg'][ 0 ]['PerMileRate']="0";
	$trip_legs['TripLeg'][ 0 ]['FlatRate']="0";
	$trip_legs['TripLeg'][ 0 ]['Comments']="";	
	
	for($i=1;	$i < $legs; $i++)
	{
		$trip_legs['TripLeg'][ $i ]['ProMilesLocationID']="0";
		$trip_legs['TripLeg'][ $i ]['State']="";
		$trip_legs['TripLeg'][ $i ]['PostalCode']="";
		$trip_legs['TripLeg'][ $i ]['Latitude']="".$gps[ $i ]['lat']."";
		$trip_legs['TripLeg'][ $i ]['Longitude']="".$gps[ $i ]['long']."";
		$trip_legs['TripLeg'][ $i ]['LocationText']="";
		$trip_legs['TripLeg'][ $i ]['Label']="";
		$trip_legs['TripLeg'][ $i ]['Type']="PROMILES";
		$trip_legs['TripLeg'][ $i ]['LocationProperties']="";
		$trip_legs['TripLeg'][ $i ]['PerMileRate']="0";
		$trip_legs['TripLeg'][ $i ]['FlatRate']="0";
		$trip_legs['TripLeg'][ $i ]['Comments']="";	
	}
	
	$basictrip['RoutingMethod'] = 'PRACTICAL';
	$basictrip['BorderOpen'] = 'true';
	$basictrip['AvoidTollRoads'] = 'false';
	$basictrip['TripMiles'] = '0';
	$basictrip['TripMinutes'] = '0';
	$basictrip['TripLegs']=$trip_legs;
	$basictrip['VehicleType'] = 'Tractor3AxleTrailer2Axle';
	$basictrip['AllowRelaxRestrictions'] = 'false';
	$basictrip['HasRelaxedRestrictions'] = 'false';
	$basictrip['TollCharges'] = '0';
	$basictrip['TripCharges'] = '0';
	
	$basictrip['TripStartDate'] = '0001-01-01T00:00:00';
	$basictrip['TripEndDate'] = '0001-01-01T00:00:00';
	$basictrip['UnitMPG'] = '0';
	
	$basictrip['GetMapPoints'] = 'false';
	$basictrip['StartOdometer'] = '0.00';
	$basictrip['EndOdometer'] = '0.00';
	$basictrip['GetStaticTripMap'] = 'false';
	
	$basictrip['ResponseStatus'] = 'SUCCESS';
	
	
	$params['c'] = $credentials;
	$params['Trip'] = $basictrip;
			
    	//echo "<pre>LEGS=".$legs.".</pre>";	
     
	if($legs > 1)
	{
     	try {
     		//echo "<pre>";
     		//var_dump($params);
     		//echo "</pre>";
     		
     		$client = new SoapClient($prime_url);
     		
     		$xml = $client->__soapCall($cmd, array($params));
     	} catch (Exception $e) {
     		//echo "Exception: ";
     		//var_dump($e->__toString());
     	}
     	
     	//echo "<pre>";
     	//var_dump($xml);
     	//echo "</pre>";
     	
     	$miles=(string)$xml->GetTripDistanceResult;
     	//die("got here ($miles)");
     	
     	if($miles=="-1" || $miles < 0)
     	{
     		echo "<pre>ProMiles Interface is not working.</pre>";	
     	}
	}
	else
	{
		//echo "hit2";
		$miles='-2';
	}
	//$miles.=".".$legs."";
	return $miles;
	*/
}

function mrr_promiles_get_file_contents_multi_leg2($legs,$gps)
{		
	$miles='0';
	return $miles;
	
	/*	
	$prime_url="http://prime.promiles.com/Webservices/v1_1/PRIMEStandardV1_1.asmx?wsdl";
	$cmd = "GetTripDistance";
	
	$credentials=mrr_get_promiles_credentials();
		
	$trip_legs['TripLeg'][ 0 ]['ProMilesLocationID']="0";
	$trip_legs['TripLeg'][ 0 ]['City']="".$gps[ 0 ]['city']."";
	$trip_legs['TripLeg'][ 0 ]['State']="".$gps[ 0 ]['state']."";
	$trip_legs['TripLeg'][ 0 ]['PostalCode']="".$gps[ 0 ]['zip']."";
	$trip_legs['TripLeg'][ 0 ]['Latitude']="".$gps[ 0 ]['lat']."";
	$trip_legs['TripLeg'][ 0 ]['Longitude']="".$gps[ 0 ]['long']."";
	$trip_legs['TripLeg'][ 0 ]['LocationText']="";
	$trip_legs['TripLeg'][ 0 ]['Label']="";
	$trip_legs['TripLeg'][ 0 ]['Type']="PROMILES";
	$trip_legs['TripLeg'][ 0 ]['LocationProperties']="";
	$trip_legs['TripLeg'][ 0 ]['PerMileRate']="0";
	$trip_legs['TripLeg'][ 0 ]['FlatRate']="0";
	$trip_legs['TripLeg'][ 0 ]['Comments']="";	
	
	for($i=1;	$i < $legs; $i++)
	{
		$trip_legs['TripLeg'][ $i ]['ProMilesLocationID']="".$i."";
		$trip_legs['TripLeg'][ $i ]['City']="".$gps[ $i ]['city']."";
		$trip_legs['TripLeg'][ $i ]['State']="".$gps[ $i ]['state']."";
		$trip_legs['TripLeg'][ $i ]['PostalCode']="".$gps[ $i ]['zip']."";
		$trip_legs['TripLeg'][ $i ]['Latitude']="".$gps[ $i ]['lat']."";
		$trip_legs['TripLeg'][ $i ]['Longitude']="".$gps[ $i ]['long']."";
		$trip_legs['TripLeg'][ $i ]['LocationText']="";
		$trip_legs['TripLeg'][ $i ]['Label']="";
		$trip_legs['TripLeg'][ $i ]['Type']="PROMILES";
		$trip_legs['TripLeg'][ $i ]['LocationProperties']="";
		$trip_legs['TripLeg'][ $i ]['PerMileRate']="0";
		$trip_legs['TripLeg'][ $i ]['FlatRate']="0";
		$trip_legs['TripLeg'][ $i ]['Comments']="";	
	}
	
	$basictrip['RoutingMethod'] = 'PRACTICAL';
	$basictrip['BorderOpen'] = 'true';
	$basictrip['AvoidTollRoads'] = 'false';
	$basictrip['TripMiles'] = '0';
	$basictrip['TripMinutes'] = '0';
	$basictrip['TripLegs']=$trip_legs;
	$basictrip['VehicleType'] = 'Tractor3AxleTrailer2Axle';
	$basictrip['AllowRelaxRestrictions'] = 'false';
	$basictrip['HasRelaxedRestrictions'] = 'false';
	$basictrip['TollCharges'] = '0';
	$basictrip['TripCharges'] = '0';
	
	$basictrip['TripStartDate'] = '0001-01-01T00:00:00';
	$basictrip['TripEndDate'] = '0001-01-01T00:00:00';
	$basictrip['UnitMPG'] = '0';
	
	$basictrip['GetMapPoints'] = 'false';
	$basictrip['StartOdometer'] = '0.00';
	$basictrip['EndOdometer'] = '0.00';
	$basictrip['GetStaticTripMap'] = 'false';
	
	$basictrip['ResponseStatus'] = 'SUCCESS';
	
	
	$params['c'] = $credentials;
	$params['Trip'] = $basictrip;
			
     	
	if($legs > 1)
	{
     	try {
     		//echo "<pre>";
     		//var_dump($params);
     		//echo "</pre>";
     		
     		$client = new SoapClient($prime_url);
     		
     		$xml = $client->__soapCall($cmd, array($params));
     	} catch (Exception $e) {
     		//echo "Exception: ";
     		//var_dump($e->__toString());
     	}
     	
     	//echo "<pre>";
     	//var_dump($xml);
     	//echo "</pre>";
     	
     	$miles=(string)$xml->GetTripDistanceResult;
     	
     	if($miles=="-1" || $miles < 0)
     	{
     		echo "<pre>ProMiles Interface is not working.</pre>";	
     	}
	}
	else
	{
		$miles='-2';
	}
	return $miles;
	*/
}

function mrr_get_promiles_gps_from_address($address,$city,$state,$zip)
{
	$res['status']=0;
	$res['address']=trim($address);
	$res['city']=trim($city);
	$res['state']=trim($state);
	$res['zip']=trim($zip);
	$res['lat']="";
	$res['long']="";
	$res['error']="";
	
	$address_str="".trim($address).",".trim($city).",".trim($state)." ".trim($zip)."";	
	$ares = mrr_get_coordinates($address_str);
	$res['lat']=$ares['lat'];
	$res['long']=$ares['long'];
	$res['status']=1;
	if(!is_numeric($res['lat']) || !is_numeric($res['long']))	
	{
		$res['status']=0;
		$res['error']="ProMiles Geocoding Interface is not working.";		//actually GOOGLE API
	}
	return $res;
	
	/*
	//DISABLED PROMILES VERSION...
	
	$prime_url="http://prime.promiles.com/Webservices/v1_1/PRIMEStandardV1_1.asmx?wsdl";
	$cmd = "Geocode";
	
	$credentials=mrr_get_promiles_credentials();
	
	if(is_numeric($zip) && trim($city)=="" && trim($state)=="")
	{
		$city=$zip;
		$zip="";
	}	
	
	$params['c'] = $credentials;
	$params['Address']=trim($address);
	$params['City']=trim($city);
	$params['StateAbbreviation']=trim($state);
	$params['PostalCode']=trim($zip);
		     	
	if(trim($city)!="" || trim($state)!="" || trim($zip)!="")
	{
     	try {
     		//echo "<pre>";
     		//var_dump($params);
     		//echo "</pre>";
     		
     		$client = new SoapClient($prime_url);
     		
     		$xml = $client->__soapCall($cmd, array($params));
     	} catch (Exception $e) {
     		//echo "Exception: ";
     		$res['error']="Exception: ";
     		//var_dump($e->__toString());
     		
     		$res['error'].=$e->__toString();
     		
     	}
     	
     	
     	$gres=$xml->GeocodeResult;
     	$gloc=$gres->GeocodedLocation;
     	$gstatus=$gres->IsGeocoded;
     	$gres2=$gres->ResponseStatus;
     	
     	//echo "<pre>";
     	//var_dump($gloc);
     	//echo "</pre>";
     	     	
     	if($gstatus)
     	{
     		$res['status']=1;
     		
     		$res['address']=(string)$gloc->Address;
			$res['city']=(string)$gloc->City;
			$res['state']=(string)$gloc->State;
			$res['zip']=(string)$gloc->PostalCode;
			$res['lat']=(string)$gloc->Latitude;
			$res['long']=(string)$gloc->Longitude;
			
			//echo "<pre>Geocoded=YES</pre>";	     		
     		//echo "<br>...<br>Location Info:<br><pre>";
     		//var_dump($res);
     		//echo "</pre>";	
     	}
     	else
     	{
     		$res['error']="ProMiles Geocoding Interface is not working.";
     	}
	}
	return $res;
	*/
}
function mrr_pro_miles_gps_lookup($lat=0,$long=0)
{
	$res['city']="";
	$res['state']="";
	$res['zip']="";
	$res['latitude']="";
	$res['longitude']="";
	$res['found']=0;
	
	$adder="";
	
	if($lat!=0 && is_numeric($lat))	
	{
		$lat=round($lat,6);
		$adder.=" and latitude='".sql_friendly($lat)."'";
	}
	if($long!=0 && is_numeric($long))
	{
		$long=round($long,6);
		if($long > 0)	$long=(-1 * $long);		//USA is always negative...
		
		$adder.=" and longitude='".sql_friendly($long)."'";
	}
	
	$sql="
     		select *				
     		from ".mrr_find_log_database_name()."pro_miles_zip_codes     			
     		where deleted<=0	
     			".$adder."     			
     		order by linedate_added asc						
     ";	
    	$data=simple_query($sql);
    	while($row=mysqli_fetch_array($data))
    	{
    		$res['city']=$row['city'];
		$res['state']=$row['state'];
		$res['zip']=$row['zip'];
		$res['latitude']=$row['latitude'];
		$res['longitude']=$row['longitude'];
		$res['found']=1;
    	}
    	return $res;
}
function mrr_pro_miles_addr_lookup($city="",$state="",$zip="")
{
	$res['city']="";
	$res['state']="";
	$res['zip']="";
	$res['latitude']="";
	$res['longitude']="";
	$res['found']=0;
	
	$adder="";
	
	if(trim($city)!="")		$adder.=" and city='".sql_friendly(strtoupper($city))."'";
	if(trim($state)!="")	$adder.=" and state='".sql_friendly(strtoupper($state))."'";
	if(trim($zip)!="")		$adder.=" and zip='".sql_friendly(strtoupper($zip))."'";
		
	$sql="
     		select *				
     		from ".mrr_find_log_database_name()."pro_miles_zip_codes     			
     		where deleted<=0	
     			".$adder."     			
     		order by linedate_added asc						
     ";	
    	$data=simple_query($sql);
    	while($row=mysqli_fetch_array($data))
    	{
    		$res['city']=$row['city'];
		$res['state']=$row['state'];
		$res['zip']=$row['zip'];
		$res['latitude']=$row['latitude'];
		$res['longitude']=$row['longitude'];
		$res['found']=1;
    	}
    	return $res;
}
function mrr_pro_miles_new_location($lat=0,$long=0,$city="",$state="",$zip="")
{		
	global $datasource;

	$lat=round($lat,6);
	$long=round($long,6);
	if($long > 0)	$long=(-1 * $long);		//USA is always negative...
	
	$city=trim(strtoupper($city));	
	$state=trim(strtoupper($state));	
	$zip=trim(strtoupper($zip));	
	
	$newid=0;
	$sql="
     	insert into ".mrr_find_log_database_name()."pro_miles_zip_codes 
     		(id,
     		linedate_added,
     		deleted,
     		city,
     		state,
     		zip,
     		latitude,
     		longitude)
     	values
     		(NULL,
     		NOW(),
     		0,
     		'".sql_friendly($city)."',
     		'".sql_friendly($state)."',
     		'".sql_friendly($zip)."',
     		'".sql_friendly($lat)."',
     		'".sql_friendly($long)."')					
     ";	
    	simple_query($sql);
	$newid=mysqli_insert_id($datasource);
	return $newid;
}
/*
function mrr_find_pro_miles_location($lat=0,$long=0,$city="",$state="",$zip="")
{
	$res['city']="";
	$res['state']="";
	$res['zip']="";
	$res['latitude']="";
	$res['longitude']="";
	$res['found']=0;
	
	if($lat==0 && $long==0 && (trim($city)!="" || trim($state)!="" || trim($zip)!=""))
	{	//look up GPS with address
		$gps_res=mrr_pro_miles_addr_lookup(trim($city),trim($state),trim($zip));
		if($gps_res['latitude'] != "" || $gps_res['longitude'] != "")
		{
			$res['city']=$gps_res['city'];
			$res['state']=$gps_res['state'];
			$res['zip']=$gps_res['zip'];
			$res['latitude']=$gps_res['latitude'];
			$res['longitude']=$gps_res['longitude'];
			$res['found']=1;	
		}
	}
	if($lat!=0 && $long!=0 && (trim($city)=="" || trim($state)=="" || trim($zip)==""))
	{	//look up address using GPS
		$gps_res=mrr_pro_miles_gps_lookup($lat,$long);	
		if($gps_res['city'] != "" || $gps_res['state'] != "" || $gps_res['zip'] != "")
		{
			$res['city']=$gps_res['city'];
			$res['state']=$gps_res['state'];
			$res['zip']=$gps_res['zip'];
			$res['latitude']=$gps_res['latitude'];
			$res['longitude']=$gps_res['longitude'];
			$res['found']=1;	
		}
	}
	return $res;
}
*/

function mrr_get_promiles_credentials()
{
	$credentials['Username'] = 'sherrcom';
	$credentials['Password'] = 'rod2890';
	$credentials['CompanyCode'] = 'SCC';	
	
	return $credentials;
}
function mrr_get_promiles_reverse_geocode_from_gps($lat,$long)
{
	$res['status']=0;
	$res['address']="";
	$res['city']="";
	$res['state']="";
	$res['zip']="";
	$res['lat']=$lat;
	$res['long']=$long;
	$res['error']="";
	
	$ares=mrr_get_coord_addr($lat,$long);
	$res['address']=$ares['numb']." ".$ares['addr'];
     $res['city']=$ares['city'];
     $res['state']=$ares['state'];
     $res['zip']=$ares['zip'];
     $res['status']=1;
     
     if(trim($res['address'])=="" || trim($res['city'])=="")
     {
     	$res['status']=0;
     	$res['error']="ProMiles Geocoding Interface is not working.";	//actually GOOGLE API
     }
     //$ares['cnty'] = "";
     //$ares['usa']  = "";
	
	return $res;
	
	/*
	$prime_url="http://prime.promiles.com/Webservices/v1_1/PRIMEStandardV1_1.asmx?wsdl";
	$cmd = "ReverseGeocode";
	
	$credentials=mrr_get_promiles_credentials();
	
	if(is_numeric($long) && $long > 0)
	{
		$long = -1 * $long;
	}	
	
	$params['c'] = $credentials;
	$params['Latitude']=trim($lat);
	$params['Longitude']=trim($long);
	
	
	     	
	if(trim($lat)!="" || trim($long)!="")
	{
     	try {
     		//echo "<pre>";
     		//var_dump($params);
     		//echo "</pre>";
     		
     		$client = new SoapClient($prime_url);
     		
     		$xml = $client->__soapCall($cmd, array($params));
     	} catch (Exception $e) {
     		//echo "Exception: ";
     		$res['error']="Exception: ";
     		//var_dump($e->__toString());
     		
     		$res['error'].=$e->__toString();
     		
     	}
     	//echo "<pre>";
     	//var_dump($xml);
     	//echo "</pre>";
     	
     	$gloc=$xml->ReverseGeocodeResult;
     	    	
     	//echo "<br>Location Info<br><pre>";
     	//var_dump($gloc);
     	//echo "</pre>";
     	     	
     	if(isset($gloc))
     	{
     		$res['status']=1;
     		
     		$res['address']=(string)$gloc->Address;
			$res['city']=(string)$gloc->City;
			$res['state']=(string)$gloc->State;
			$res['zip']=(string)$gloc->PostalCode;
			$res['lat']=(string)$gloc->Latitude;
			$res['long']=(string)$gloc->Longitude;
     	}
     	else
     	{
     		$res['error']="ProMiles Geocoding Interface is not working.";
     	}
	}
	return $res;
	*/
}
function mrr_promiles_get_file_contents_runtrip($legs,$gps)
{		
	$res['tot_miles']=0;
	$res['legs']=0;
	$res['miles']=array();
	$res['times']=array();
	$res['city']=array();;
	$res['state']=array();;
	$res['zip']=array();;
	$res['directions']=array();;
	$res['truckstops']=array();;
	return $res;
	
	/*
	$prime_url="http://prime.promiles.com/Webservices/v1_1/PRIMEStandardV1_1.asmx?wsdl";
	$cmd = "RunTrip";
	
	$credentials=mrr_get_promiles_credentials();
	
	$miles='0';
	
	$trip_legs['TripLeg'][ 0 ]['ProMilesLocationID']="0";
	$trip_legs['TripLeg'][ 0 ]['City']="".$gps[ 0 ]['city']."";
	$trip_legs['TripLeg'][ 0 ]['State']="".$gps[ 0 ]['state']."";
	$trip_legs['TripLeg'][ 0 ]['PostalCode']="".$gps[ 0 ]['zip']."";
	$trip_legs['TripLeg'][ 0 ]['Latitude']="".$gps[ 0 ]['lat']."";
	$trip_legs['TripLeg'][ 0 ]['Longitude']="".$gps[ 0 ]['long']."";
	$trip_legs['TripLeg'][ 0 ]['LocationText']="".trim($gps[ 0 ]['city']." ".$gps[ 0 ]['state']." ".$gps[ 0 ]['zip'])."";
	$trip_legs['TripLeg'][ 0 ]['Label']="Stop 0";
	$trip_legs['TripLeg'][ 0 ]['Type']="PROMILES";
	$trip_legs['TripLeg'][ 0 ]['PerMileRate']="0.00";
	$trip_legs['TripLeg'][ 0 ]['FlatRate']="0.00";
	$trip_legs['TripLeg'][ 0 ]['Comments']="Test 1";	
	
	$arr[0]=0;
	$tarr[0]=0;
	$city[0]="";
	$state[0]="";
	$zip[0]="";
	
	
	for($i=1;	$i < $legs; $i++)
	{
		$trip_legs['TripLeg'][ $i ]['ProMilesLocationID']="".$i."";
		$trip_legs['TripLeg'][ $i ]['City']="".$gps[ $i ]['city']."";
		$trip_legs['TripLeg'][ $i ]['State']="".$gps[ $i ]['state']."";
		$trip_legs['TripLeg'][ $i ]['PostalCode']="".$gps[ $i ]['zip']."";
		$trip_legs['TripLeg'][ $i ]['Latitude']="".$gps[ $i ]['lat']."";
		$trip_legs['TripLeg'][ $i ]['Longitude']="".$gps[ $i ]['long']."";
		$trip_legs['TripLeg'][ $i ]['LocationText']="".trim($gps[ $i ]['city']." ".$gps[ $i ]['state']." ".$gps[ $i ]['zip'])."";
		$trip_legs['TripLeg'][ $i ]['Label']="Stop ".$i."";
		$trip_legs['TripLeg'][ $i ]['Type']="PROMILES";
		$trip_legs['TripLeg'][ $i ]['PerMileRate']="0.00";
		$trip_legs['TripLeg'][ $i ]['FlatRate']="0.00";
		$trip_legs['TripLeg'][ $i ]['Comments']="Stop ".$i."";	
		
		$arr[$i]=0;
		$tarr[$i]=0;
		$city[$i]="";
		$state[$i]="";
		$zip[$i]="";
	}
	
	
	//new version below...
	
	//...Trip Options
	$t_routes['RoutingMethod']="PRACTICAL";					//PRACTICAL, SHORTEST, INTERSTATE, NATIONAL_NETWORK
	$t_routes['BorderOpen']='true';
	$t_routes['DoRouteOptimization']='true';
	$t_routes['RouteOptimizationMethod']='NO_OPTIMIZATION';	//NO_OPTIMIZATION, DESTINATION_FIXED, RESEQUENCE_ALL
	$t_routes['IsHUBMode']='false';
	$t_routes['IsHazmat']='false';
	$t_routes['AvoidTollRoads']='false';
	$t_routes['AllowRelaxRestrictions']='false';
	
	$t_itinerary['MinutesAtOrigin']='0';
	$t_itinerary['MinutesAtFuelStop']='0';
	$t_itinerary['MinutesAtDOTRest']='0';
	$t_itinerary['MinutesAtStop']='0';
	$t_itinerary['MilesBetweenFuelStops']='0';
	$t_itinerary['MinutesDrivingBetweenRests']='0';
	$t_itinerary['ItineraryAnchorTime']='0001-01-01T00:00:00';
	$t_itinerary['AnchorTimeIsTripStart']='false';
	$t_itinerary['UseTeamMode']='false';
	$t_itinerary['MinutesUntilFirstDOTRest']='0';
	
	$t_fuel_opt['UnitMPG']='5.50';
	$t_fuel_opt['UnitTankCapacity']='0';
	$t_fuel_opt['StartGallons']='0';
	$t_fuel_opt['DesiredEndGallons']='0';
	$t_fuel_opt['DistanceOOR']='0.00';
	$t_fuel_opt['MinimumGallonsToPurchase']='0';
	$t_fuel_opt['MinimumTankGallonsDesired']='0';
	$t_fuel_opt['UseAllStopsNetwork']='false';
	
	$trip_opts['Routing']=$t_routes;
	$trip_opts['Itinerary']=$t_itinerary;
	$trip_opts['FuelOptimization']=$t_fuel_opt;
	
	//...Fuel Purchase
	$fuel_purchases['PurchaseState']='';
	$fuel_purchases['AmountPurchased']='0.00';
	$fuel_purchases['IsLiters']='false';
	$fuel_purchases['Station']='';
	$fuel_purchases['City']='';
	$fuel_purchases['Invoice']='';
	$fuel_purchases['FuelCost']='0.00';
	$fuel_purchases['TypeOfFuel']='DIESEL';					//DIESEL, BIO_DIESEL, GASOLINE
	
	$vehicle_config['VehicleType']='Tractor3AxleTrailer2Axle';	//Truck2Axle,Truck3Axle,Tractor2AxleBobtail,Tractor3AxleBobtail,Tractor2AxleTrailer1Axle,Tractor2AxleTrailer2Axle,Tractor3AxleTrailer1Axle,
													//Tractor3AxleTrailer2Axle,Tractor3AxleTrailerSplit2Axle,Tractor3AxleTrailer3Axle,Tractor2AxleDouble,Tractor3AxleDouble,Tractor2Axle6Tires
	$vehicle_config['UseDefaultsForVehicleType']='';
	$vehicle_config['LoadIsHazmat']='false';
	$vehicle_config['LoadIsHouseholdGoods']='false';
	$vehicle_config['LoadIsSandAndGravel']='false';
	$vehicle_config['Height']='14.00';
	$vehicle_config['TrailerLength']='53.00';
	$vehicle_config['CompleteLength']='80.00';
	$vehicle_config['GrossWeight']='0';
	$vehicle_config['Width']='27.00';
	$vehicle_config['NumberOfAxles']='5';
	$vehicle_config['AxleWeight']='0';
	$vehicle_config['EmptyWeight']='0';
	$vehicle_config['ExtremeAxleLength']='0.00';
	$vehicle_config['HasKingpinLength']='false';
	$vehicle_config['KingpinLengthToCenterTandem']='0.00';
	$vehicle_config['KingpinLengthToEndTrailer']='0.00';
	$vehicle_config['KingpinLengthToLastAxle']='0.00';
	$vehicle_config['TandemWeight']='0';
	$vehicle_config['TridemWeight']='0';
	
	$trip_res['TripMiles']='0.00';
	$trip_res['TripMinutes']='0';
	$trip_res['TollCharges']='0.00';
	$trip_res['TripCharges']='0.00';
	$trip_res['TripTaxes']='0.00';
	$trip_res['AverageCostPerGallon']='0.00';
	$trip_res['HasRelaxedRestrictions']='false';
	
	
	$basictrip['Options']=$trip_opts;
	$basictrip['FuelPurchases']=$fuel_purchases;
	$basictrip['VehicleAndLoadDescription']=$vehicle_config;
	$basictrip['GetDrivingDirections']='false';
	$basictrip['GetStateBreakout']='false';
	$basictrip['GetFuelOptimization']='false';
	$basictrip['GetTripSummary']='true';
	$basictrip['GetItinerary']='false';
	$basictrip['GetTruckStopsOnRoute']='false';
	$basictrip['GetTaxSummary']='false';
	$basictrip['Results']=$trip_res;	
		
	$basictrip['TripLegs'] = $trip_legs;
		
	$basictrip['TripStartDate'] = '0001-01-01T00:00:00';
	$basictrip['TripEndDate'] = '0001-01-01T00:00:00';
	$basictrip['UnitMPG'] = '5.5';
	
	$basictrip['GetMapPoints'] = 'false';
	$basictrip['StartOdometer'] = '0.00';
	$basictrip['EndOdometer'] = '0.00';
	$basictrip['GetStaticTripMap'] = 'false';
	
	$basictrip['ResponseStatus'] = 'SUCCESS';
	
	$params['c'] = $credentials;
	$params['Trip'] = $basictrip;
			
     $turn_by_turn="
     	<table width='100%' border='0' cellpadding='1' cellspacing='1'>
     		<tr>
				<td valign='top'><b>Maneuver</b></td>
				<td valign='top' align='right'><b>Distance At Start</b></td>
				<td valign='top' align='right'><b>Leg Distance</b></td>     				
				<td valign='top' align='right'><b>Time</b></td>
				<td valign='top'>&nbsp;&nbsp;&nbsp;<b>Road</b></td>   				
				<td valign='top' align='right'><b>Toll Charge</b></td>     				
			</tr> 
     ";
	
	$truckstops="
		<table width='100%' border='0' cellpadding='1' cellspacing='1'>
			<tr>    				
				<td valign='top'><b>Name</b></td>
				<td valign='top'><b>City</b></td>
				<td valign='top'><b>State</b></td>     				
				<td valign='top'><b>Location</b></td>
				<td valign='top'><b>Chain</b></td>
				<td valign='top' align='right'><b>Distance From Origin</b></td>
				<td valign='top' align='right'><b>Distance OOR</b></td>
				<td valign='top' align='right'><b>Retail Price</b></td>
				<td valign='top' align='right'><b>Cost Price</b></td>
				<td valign='top' align='right'><b>Price Date</b></td>     						
			</tr>  
	";	
	if($legs > 1)
	{
     	try {
     		//echo "<pre>";
     		//var_dump($params);
     		//echo "</pre>";
     		
     		$client = new SoapClient($prime_url);
     		
     		$xml = $client->__soapCall($cmd, array($params));
     	} catch (Exception $e) {
     		//echo "Exception: ";
     		//var_dump($e->__toString());
     	}
     	echo "<br><hr><br>";
     	//echo "<pre>";
     	//var_dump($xml);
     	//echo "</pre>";
     	
     	
     	$all=$xml->RunTripResult;     	
     	
     	$tlegs=$all->TripLegs;
     	
     	$status=(string)$all->ResponseStatus;
     	$message=(string)$all->ResponseMessage;
     	
     	$resultx= $all->Results;
     	
     	//get trip parts...
     	$legres=$resultx->TripSummary;     	
     	$aleg_res=$legres->TripSummaryRow;     	
     	$aleg_cnt=count($aleg_res);
     	for($i=0; $i < $aleg_cnt; $i++)
     	{
     		$myleg=$aleg_res[ $i ];
     		$arr[$i]=(string) $myleg->LegMiles;
     		$tarr[$i]=(string) $myleg->LegMinutes;
     		$city[$i]=(string) $myleg->City;
			$state[$i]=(string) $myleg->State;
			$zip[$i]=(string) $myleg->PostalCode;
     		
     		//echo "<br><b>Summary Stop ".$i.": ".$arr[$i]." miles, ".$tarr[$i]." minutes.</b> ".$city[$i].", ".$state[$i]." ".$zip[$i]."<br>";    	//	<pre>".var_dump($myleg)."</pre>
     	}
     	
     	//get turn-by-turn directions
     	$directions=$resultx->DrivingDirections;
     	$direct=$directions->DirectionRow;
     	$direct_cnt=count($direct);
     	for($x=0; $x < $direct_cnt; $x++)
     	{
     		$turn=$direct[$x];
     		
     		$toll_charged=((string) $turn->IsToll );
     		
     		$turn_by_turn.="
     			<tr style='background-color:#".($x%2==0 ? "eeeeee" : "dddddd").";'>
     				<td valign='top'>".((string) $turn->Maneuver )."</td>
     				<td valign='top' align='right'>".number_format(((string) $turn->DistanceAtStart ),2)."</td>
     				<td valign='top' align='right'>".number_format(((string) $turn->LegDistance ),2)."</td>     				
     				<td valign='top' align='right'>".number_format(((string) $turn->Time ),2)."</td>
     				<td valign='top'>&nbsp;&nbsp;&nbsp;".((string) $turn->Road )."</td>     				   				
     				<td valign='top' align='right'>$".number_format(((string) $turn->TollCharge ),2)."</td>     				
     			</tr>     		
     		";    			
     	}     	
     	     	
     	//get truck stops in route
     	$truckstoppers=$resultx->TruckStopsOnRoute;
     	$tstopper=$truckstoppers->TruckStop;
     	$tstopper_cnt=count($tstopper);
     	for($x=0; $x < $tstopper_cnt; $x++)
     	{
     		$tstop=$tstopper[$x];
     		
     		$price_date=((string) $tstop->PriceDate );     		
     		$price_date=str_replace("T"," ",$price_date);
     		$price_date=date("m/d/Y H:i", strtotime($price_date));
     		
     		$truckstops.="
     			<tr style='background-color:#".($x%2==0 ? "eeeeee" : "dddddd").";'>    				
     				<td valign='top'>".((string) $tstop->Name )."</td>
     				<td valign='top'>".((string) $tstop->City )."</td>
     				<td valign='top'>".((string) $tstop->State )."</td>     				
     				<td valign='top'>".((string) $tstop->Location )."</td>
     				<td valign='top'>".((string) $tstop->Chain )."</td>
     				<td valign='top' align='right'>".number_format(((string) $tstop->DistanceFromOrigin ),2)."</td>
     				<td valign='top' align='right'>".number_format(((string) $tstop->DistanceOOR ),2)."</td>
     				<td valign='top' align='right'>$".number_format(((string) $tstop->RetailPrice ),2)."</td>
     				<td valign='top' align='right'>$".number_format(((string) $tstop->CostPrice ),2)."</td>
     				<td valign='top' align='right'>".$price_date."</td>     						
     			</tr>     		
     		";     				
     	} 
     	
     	$miles=( (string) $resultx->TripMiles ) * -1 * -1;  	
     	
     	if($miles=="-1" || $miles < 0)
     	{
     		//echo "<pre>ProMiles Interface is not working.</pre>";	
     	}
     	elseif($miles > 0)
     	{
     		//echo "<pre>ProMiles ".$miles." Miles.</pre>";		
     	}
	}
	else
	{
		$miles='-2';
	}
	
	$turn_by_turn.="</table>";
	
	$truckstops.="</table>";
	
	$res['tot_miles']=$miles;
	$res['legs']=$legs;
	$res['miles']=$arr;
	$res['times']=$tarr;
	$res['city']=$city;
	$res['state']=$state;
	$res['zip']=$zip;
	$res['directions']=$turn_by_turn;
	$res['truckstops']=$truckstops;
	return $res;
	*/
}


function mrr_simple_note_display($section_id,$xfer_id)
{
	$note_pad="";		
	$sql="
		select notes_main.*,
			(select users.username from users where users.id=notes_main.created_by_user_id) as user_name
		
		from notes_main
		where deleted <= 0
			and note_type_id = '".sql_friendly($section_id)."'
			and xref_id = '".sql_friendly($xfer_id)."'
			and access_level<=0
		order by linedate_added desc			
	";	
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$note_pad.="<br><b>".date("m/d/Y H:i",strtotime($row['linedate_added']))." - ".$row['user_name']."</b><br>".trim($row['note'])."<br>";
	}
	return $note_pad;
}


function mrr_warning_of_improper_date_time_for_loads()
{
	// check for stops that are entered out of order (i.e. stop 2 is completed for before stop 1)
	$use_month="".date("m", strtotime("-1 month",time()))."";
			
	$tab="";
	$tab_rows="";
	$cntr=0;
	
	$last_truck_id=0;
	
	$last_complete_date=0;
	$last_complete_time=0;
	$load_cntr=0;		
	$stop_cntr=0;
	
	// changed from 1 month to 1 week on 10/10/2014 - by CS and MR
	$sql="
		select load_handler_stops.*,
			trucks_log.id,
			trucks_log.load_handler_id,
			trucks_log.truck_id,
			trucks.name_truck as truck_name
			
		from trucks_log
			left join trucks on trucks.id = trucks_log.truck_id
			left join load_handler_stops on load_handler_stops.trucks_log_id=trucks_log.id
			
		where trucks_log.deleted<=0	
			and trucks_log.linedate_pickup_eta>='".date("Y-m-d", strtotime("-1 week", time()))." 00:00:00'     					  			
			and load_handler_stops.deleted<=0
			
		order by trucks_log.linedate_pickup_eta asc,load_handler_stops.linedate_pickup_eta asc						
	";
	//and trucks_log.id=26886
	//return;
	$data=simple_query($sql);
	
	while($row=mysqli_fetch_array($data))
	{     		
		$load_id=$row['load_handler_id'];
		$truck_id=$row['truck_id'];
		$truck_name=$row['truck_name'];
		//$start_date=date("Ymd",strtotime($row['linedate_pickup_eta']));
		//$start_time=date("Hi",strtotime($row['linedate_pickup_eta']));     		
		
		if($last_truck_id!=$truck_id)
		{
			$last_complete_date=0;
			$last_complete_time=0;
			$load_cntr=0;		//count for load timing issues
			$stop_cntr=0;
		}     		
		$last_truck_id=$truck_id;
		
		if($load_id > 0)
		{
			$dater="0000-00-00 00:00:00";
			if(isset($rows['linedate_completed']))		$dater=$row['linedate_completed'];
			     			
			if($dater!="0000-00-00 00:00:00")
			{
     			$completed=date("m/d/Y H:i",strtotime($dater));
     			$complete_date=date("Ymd",strtotime($dater));
     			$complete_time=date("Hi",strtotime($dater));
     			
     			if($last_complete_date==0)
     			{
     				$last_completed=date("m/d/Y H:i",strtotime($dater));
     				$last_complete_date=$complete_date;
     				$last_complete_time=$complete_time;	
     			}
     			elseif($row['stop_type_id']==2)
     			{
     				if($complete_date < $last_complete_date || ($complete_date == $last_complete_date && $complete_time < $last_complete_time))
     				{
     					$load_cntr++;
     					
     					$tab_rows.="
							<tr class='".($cntr%2==0 ? "even" : "odd")."'>
								<td valign='top'><a href='manage_load.php?load_id=".$load_id."' target='_blank'>".$load_id."</a></td>
								<td valign='top'><a href='add_entry_truck.php?load_id=".$load_id."&id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
								<td valign='top'>".$row['id']."</td>
								<td valign='top'><a href='admin_trucks.php?id=".$truck_id."' target='_blank'>".$truck_name."</a></td>
								<td valign='top'>".($row['stop_type_id']==2 ? "(C)" : "(S)")."</td>
								<td valign='top'>".$row['shipper_name']."</td>
								<td valign='top'>".$row['shipper_city']."</td>
								<td valign='top'>".$row['shipper_state']."</td>
								<td valign='top'>".$last_completed."</td>
								<td valign='top'>".$completed."</td>     								
							</tr>
						";
     					
     					$last_completed=date("m/d/Y H:i",strtotime($dater));
     					$last_complete_date=$complete_date;
     					$last_complete_time=$complete_time;
     				}
     			}          			
			}     			
		}
		if($load_cntr > 0)	$cntr++;		   		
	}
		
	if($cntr>0)
	{
		$tab="
			<table width='100%' cellpadding='0' cellspacing='0' border='0'>
			<tr>
				<td valign='top'><b>Load ID</b></td>
				<td valign='top'><b>Dispatch ID</b></td>     				
				<td valign='top'><b>Stop</b></td>
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>Type</b></td>
				<td valign='top'><b>Shipper</b></td>
				<td valign='top'><b>City</b></td>
				<td valign='top'><b>State</b></td>
				<td valign='top'><b>Last Stop Completes</b></td>
				<td valign='top'><b>This Stop Completed</b></td>
			</tr>
			".$tab_rows."
			</table>
		";	
	}
	
	$res['html']=$tab;
	$res['num']=$cntr;
	return $res;	
}
	
function mrr_get_stop_grading_notes($load_id=0,$dispatch=0,$stop_id=0)
{
	$grading_notes="";
	$sqlx="
		select load_handler_stops.load_handler_id,
			load_handler_stops.trucks_log_id as stop_id,
			load_handler_stops.id as stop_id,
			load_handler_stops.stop_grade_id,
			load_handler_stops.stop_grade_note
			
		from load_handler_stops
			
		where load_handler_stops.deleted<=0 	
			".($load_id > 0 ? " and load_handler_stops.load_handler_id='".sql_friendly($load_id)."'" :"")."
			".($dispatch > 0 ? " and load_handler_stops.trucks_log_id='".sql_friendly($dispatch)."'" :"")."
			".($stop_id > 0 ? " and load_handler_stops.id='".sql_friendly($stop_id)."'" :"")."   			
			
		order by load_handler_stops.linedate_pickup_eta asc,
				load_handler_stops.load_handler_id asc,     				
				load_handler_stops.trucks_log_id asc,
				load_handler_stops.id asc						
	";
	$datax=simple_query($sqlx);
	while($rowx=mysqli_fetch_array($datax))
	{     		
		if($rowx['stop_grade_id'] > 0 || trim($rowx['stop_grade_note'])!="")
		{
			$grading_notes.="<br>Stop ".$rowx['stop_id'].": ".mrr_load_stop_grade_decoder($rowx['stop_grade_id']).": ".trim($rowx['stop_grade_note'])."";
		}
	}
	return $grading_notes;     	
}
	
function mrr_find_gps_from_zip_code($zip)
{			
	/*
	NOTICE:  Labels are wrong for the gps_to_zip_code table...  Lat=longitude and Long=Latitude...
	*/				
	$city="";
	$state="";
	$lat="0";	
	$long="0";	
	$zip=(int) $zip;
	
	//test for 6 decimal places get first one
	$sql="
		select zip_code,city,state,latitude,longitude 
		from gps_to_zip_code
		where deleted<=0 and zip_code='".sql_friendly($zip)."'
		limit 1		
	";	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$city=$row['city'];		$state=$row['state'];		
		$lat=$row['longitude'];	$long=$row['latitude'];	//yes, they are backwards...labels reversed, but info is good.	They will be placed in the right label for all uses of this function.
	}	

	$res['lat']=$lat;
	$res['long']=$long;
	$res['city']=$city;
	$res['state']=$state;
	$res['zip']=$zip;
	$res['all']="".$city.", ".$state." ".$zip." (".$lat.", ".$long.")";
	
	return $res;
}


function mrr_find_zip_code_from_gps($lat,$long)
{		
	$lat=abs($lat);							$long=abs($long);					
	$lat5="".round($lat,6,PHP_ROUND_HALF_DOWN);		$long5="".round($long,6,PHP_ROUND_HALF_DOWN);
	$lat4="".round($lat,5,PHP_ROUND_HALF_DOWN);		$long4="".round($long,5,PHP_ROUND_HALF_DOWN);
	$lat3="".round($lat,4,PHP_ROUND_HALF_DOWN);		$long3="".round($long,4,PHP_ROUND_HALF_DOWN);
	$lat2="".round($lat,3,PHP_ROUND_HALF_DOWN);		$long2="".round($long,3,PHP_ROUND_HALF_DOWN);
	$lat1="".round($lat,2,PHP_ROUND_HALF_DOWN);		$long1="".round($long,2,PHP_ROUND_HALF_DOWN);
	$lat0="".round($lat,1,PHP_ROUND_HALF_DOWN);		$long0="".round($long,1,PHP_ROUND_HALF_DOWN);
	
	$latA=number_format($lat,4);					$longA=number_format($long,4);
	$lenA=strlen($latA);						$lenB=strlen($longA);
	$tmpA=substr($latA,0,($lenA-3));				$tmpB=substr($longA,0,($lenB-3));
	$latA=$tmpA;								$longA=$tmpB;
	
	/*
	NOTICE:  Labels are wrong for the gps_to_zip_code table...  Lat=longitude and Long=Latitude...
	*/
			
	$city="";
	$state="";
	$zip="";
	$swoop=1;
	
	
	//test for 6 decimal places get first one
	$sql="
		select zip_code,city,state 
		from gps_to_zip_code
		where deleted<=0 and latitude like '".sql_friendly($lat)."%' and longitude like '".sql_friendly($long)."%'	
		order by latitude asc,longitude asc,zip_code asc			
	";	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$city=$row['city'];		$state=$row['state'];		$zip=$row['zip_code'];	
	}	
	
	if($zip=="")
	{
		//test for 5 decimal places get first one if still blank
		$sql="
			select zip_code,city,state 
			from gps_to_zip_code
			where deleted<=0 and latitude like '".sql_friendly($latA)."%' and longitude like '".sql_friendly($longA)."%'	
			order by latitude asc,longitude asc,zip_code asc				
		";	
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$city=$row['city'];		$state=$row['state'];		$zip=$row['zip_code'];	
		}	
		$swoop=2;
	}
	
	$res['city']=$city;
	$res['state']=$state;
	$res['zip']=$zip;
	$res['swoop']=$swoop;
	$res['sql']=$sql;
	$res['all']="".$city.", ".$state." ".$zip." (swoop ".$swoop.")";
	
	return $res;
}

function mrr_gmt_offset_val()
{		
	global $defaultsarray;
	$val=(int) $defaultsarray['gmt_offset_peoplenet'];	//was -6	(CDT=-5, CST=-6	
	return $val;	
}

function mrr_get_server_time_offset()
{
	$offset_gmt=5;			//hardcoded DST time for Central timezone.
	$mon_test=date("m");
	$day_test=date("d");
	if( $mon_test ==3 && $day_test < 10)
	{	//march 10 2013
		$offset_gmt=6;	
	}   
	elseif( $mon_test ==11 && $day_test>=3)
	{	//Nov 3 2013 (and Nov 4 2012)
		$offset_gmt=6;	
	} 
	elseif($mon_test < 3 || $mon_test > 10)
	{
		$offset_gmt=6;	
	}  	
	
	return $offset_gmt;	
}
function mrr_time_travel($str,$year,$offset=0)
{
	//$offset_gmt=mrr_get_server_time_offset(); 	
	
	$offset_gmt=mrr_gmt_offset_val();
	$offset_gmt=$offset_gmt * -1;
	
	$time_offset="-".$offset_gmt." hour";     	
	
	$year=(int)$year;		
		
	$str=str_replace("/".$year." ",		"/".$year."/",	 	$str);	
	$str=str_replace("/".($year+1)." ",	"/".($year+1)."/",	$str);
	$str=str_replace("Time ","Time",$str);
	
	$pose1=0;
	$pose2=0;
	$times=substr_count($str,"Time");
	
	$test_str="";  //"<br>Offset='".$time_offset."'. Year=".$year.".";
	
	
	for($x=0; $x < $times; $x++)
	{
		//find beginning of next time block...
		if($pose2>0)
		{	
			$pose1=strpos($str,"Time",$pose2);	
		}
		else
		{
			$pose1=strpos($str,"Time");	
		}
		//find end of block...first space after a Time string
		$pose2=strpos($str," ",$pose1);
		
		$sub=substr($str,$pose1,($pose2 - $pose1));	//this is a time string
		if(substr_count($sub,"/") > 2)
		{    //time block has already been replaced if not > 2.  If it has not, run substitution code			
			$real_time=trim($sub);
			
			//get to a time that can be converted by strtotime function....
			$real_time=str_replace("Time","",$real_time);
			
			$real_time=str_replace("/".$year."/",		"/".$year." ",		$real_time);
			$real_time=str_replace("/".($year+1)."/",	"/".($year+1)." ",	$real_time);     			
			
			$real_time ="Time".date("m/d/Y H:i",strtotime($time_offset, strtotime(trim($real_time))));		//adjust time back to local time... 
			$test_str.="<br>X=".$x.". Sub='".trim($sub)."' Timeset='".$real_time."'.";
			
			$str=str_replace($sub,$real_time,$str);	   		
		}	
		$pose2+=20;			
	}	
	
	//reverse str replacers...likely already done by the code above...
	$str=str_replace("/".$year."/",		"/".$year." ",	 	$str);	
	$str=str_replace("/".($year+1)."/",	"/".($year+1)." ",	$str);
	$str=str_replace("Time","Time ",$str);	
	
	return $str."".$test_str;	
}

function mrr_find_closest_dispatch_stop_by_gps($load,$disp,$lat,$long,$pn_stop_id)
{
	$stop_id=0;
			
	$adder="";
	if($disp>0 && $disp!=$load)	$adder=" and trucks_log_id='".sql_friendly($disp)."'";
			
	$sql="
		select id,
			latitude,
			longitude
		from load_handler_stops
		where deleted<=0 
			and load_handler_id='".sql_friendly($load)."' 
			".$adder."
		order by linedate_pickup_eta asc,
			id asc
	";
	$cntr=0;
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		if(number_format($lat,0) == number_format($row['latitude'],0) || number_format($long,0) == number_format($row['longitude'],0))
		{
			$stop_id=$row['id'];
		}
		if($pn_stop_id==$cntr  && $stop_id==0)
		{
			$stop_id=$row['id'];
		}
		$cntr++;	
	}		
	$res['stop_id']=$stop_id;
	$res['sql']=$sql;
	
	return $res;
}


function mrr_find_dispatch_for_driver_by_date($driver_id,$date_check)
{		
	$cust_id=0;
	$truck_id=0;
	$trailer_id=0;
	$load_id=0;
	$disp_id=0;
	$stop_id=0;
	
	//get the last dispatch this driver was on before the date to be checked...this is likely good enough for all settings...		
	$sql = "
		select trucks_log.*			
		from trucks_log
			left join load_handler on load_handler.id=trucks_log.load_handler_id
		where (trucks_log.driver_id='".sql_friendly($driver_id)."' or driver2_id='".sql_friendly($driver_id)."')
			and trucks_log.deleted<=0
			and load_handler.deleted<=0
			and trucks_log.linedate_pickup_eta <='".date("Y-m-d H:i",strtotime($date_check))."'
		order by trucks_log.linedate_pickup_eta desc 
		limit 1
	";
	$data = simple_query($sql);	
	if($row=mysqli_fetch_array($data))
	{
		$cust_id=$row['customer_id'];
		$truck_id=$row['truck_id'];
		$trailer_id=$row['trailer_id'];
		$load_id=$row['load_handler_id'];
		$disp_id=$row['id'];	
		
		//now get stop (estimated) and perhaps switched Trailer
		$sql2 = "
			select load_handler_stops.*			
			from load_handler_stops
			where load_handler_stops.trucks_log_id='".sql_friendly($disp_id)."'
				and load_handler_stops.deleted<=0
				and load_handler_stops.linedate_pickup_eta <='".date("Y-m-d H:i",strtotime($date_check))."'
			order by load_handler_stops.linedate_pickup_eta desc 
			limit 1
		";
		$data2 = simple_query($sql2);	
		if($row2=mysqli_fetch_array($data2))
		{
			$trailer_id=$row2['end_trailer_id'];
			$stop_id=$row2['id'];	
		}
		else
		{
			$sql2 = "
				select load_handler_stops.*			
				from load_handler_stops
				where load_handler_stops.trucks_log_id='".sql_friendly($disp_id)."'
					and load_handler_stops.deleted<=0
					and load_handler_stops.linedate_pickup_eta >='".date("Y-m-d H:i",strtotime($date_check))."'
				order by load_handler_stops.linedate_pickup_eta asc 
				limit 1
			";
			$data2 = simple_query($sql2);	
			if($row2=mysqli_fetch_array($data2))
			{
				$trailer_id=$row2['start_trailer_id'];
				$stop_id=$row2['id'];	
			}	
		}
				
	}
	
	$res['customer_id']=$cust_id;
	$res['truck_id']=$truck_id;
	$res['trailer_id']=$trailer_id;
	$res['load_handler_id']=$load_id;
	$res['trucks_log_id']=$disp_id;
	$res['stop_id']=$stop_id;
	return $res;
}


function mrr_spec_notice($truck_id,$trailer_id)
{
     $res="/";
     $cntr=0;
     
     //if($truck_id==0 && $trailer_id==0)      return $res;     
      
     $sql="
          select equipment_special_notices.*,
               (select trucks.name_truck from trucks where trucks.id=equipment_special_notices.truck_id) as truck_name,
               (select trailers.trailer_name from trailers where trailers.id=equipment_special_notices.trailer_id) as tname
          from equipment_special_notices
          where equipment_special_notices.deleted=0 
               and equipment_special_notices.active > 0
               and equipment_special_notices.truck_id > 0
               and equipment_special_notices.truck_id = '".$truck_id."'
               and equipment_special_notices.trailer_id = '0'
          order by equipment_special_notices.active desc,
               equipment_special_notices.truck_id asc, 
               equipment_special_notices.trailer_id asc, 
               equipment_special_notices.id asc
      ";
     $data = simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {
          //$html.="<p>".trim($row['special_notice'])."</p>";
          $cntr++;
     
          $res="<font style='color:#00CC00; cursor:pointer;' onClick='mrr_check_special_notices(".$row['truck_id'].",0);' title='Click for Special Equipment Note on this Truck [".$row['truck_id']."], [".$row['trailer_id']."]'><b>-SN</b></font> ".$res."";  
     }
     
     $sql="
          select equipment_special_notices.*,
               (select trucks.name_truck from trucks where trucks.id=equipment_special_notices.truck_id) as truck_name,
               (select trailers.trailer_name from trailers where trailers.id=equipment_special_notices.trailer_id) as tname
          from equipment_special_notices
          where equipment_special_notices.deleted=0 
               and equipment_special_notices.active > 0
               and equipment_special_notices.trailer_id > 0
               and equipment_special_notices.trailer_id = '".$trailer_id."'
               and equipment_special_notices.truck_id = '0'
          order by equipment_special_notices.active desc,
               equipment_special_notices.truck_id asc, 
               equipment_special_notices.trailer_id asc, 
               equipment_special_notices.id asc
      ";
     $data = simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {
          //$html.="<p>".trim($row['special_notice'])."</p>";
          $cntr++;
          
          $res="".$res." <font style='color:#00CC00; cursor:pointer;' onClick='mrr_check_special_notices(0,".$row['trailer_id'].");' title='Click for Special Equipment Note on this Trailer [".$row['truck_id']."], [".$row['trailer_id']."]'><b>SN-</b></font>";
     }
     
     //$res.=" (".$cntr.")";
     
     return $res;
}


function mrr_prep_auto_maint_link($msg_id,$truck_id,$msg_text="",$send_mail_off=0)
{
	$txt="&nbsp;";
	if($truck_id==0)		return $txt;
	
	global $defaultsarray;
	
	$notices="";
	$send_mailer=0;
	$first_id=0;
	
	$trailer_id=0;
	
	$msg_text=strtoupper($msg_text);
	if(substr_count($msg_text,"TRUCK MAINT") > 0 && substr_count($msg_text,"TRK MAINT") > 0)	
	{
		//create the link for truck maintenance...no trailer needed.
		$txt="<a href='https://trucking.conardtransportation.com/maint.php?id=0&truck_id=".$truck_id."&trailer_id=".$trailer_id."&msg_id=".$msg_id."' target='_blank'>MAINT</a>";
		
		$notices.="".$txt." Request for Truck sent by driver.<br>";
		$send_mailer++;
		
		$sqlu="update ".mrr_find_log_database_name()."truck_tracking_msg_history set alert_sent_flag='1' where id='".sql_friendly($msg_id)."'";
		simple_query($sqlu);
		
		if($first_id==0)		$first_id=$msg_id;
	}
	elseif((substr_count($msg_text,"TRAILER ") > 0 || substr_count($msg_text,"TRL ") > 0) && substr_count($msg_text," MAINT") > 0)	
	{
		//get trailer first
		$trial="";
		$poser=strpos($msg_text,"MAINT");
		if($poser>0)
		{
			$trail=substr($msg_text,0,$poser);
			$trail=str_replace("TRAILER","",$trail);	
			$trail=str_replace("TRL","",$trail);
			$trail=str_replace("MAINT","",$trail);
			$trail=str_replace("#","",$trail);
			$trail=trim($trail);				
		}
		if($trail!="")
		{
			$sql="		     			
          		select id
          		from trailers
          		where trailer_name like '".sql_friendly($trail)."'
          		order by trailer_name asc,deleted asc,id asc
			";
			$data = simple_query($sql);
			while($row = mysqli_fetch_array($data))
			{
				$trailer_id=$row['id'];
			}
			
			//blank, so see if there is a similar trailer
			if($trailer_id==0)
			{
				$sql="		     			
               		select id
               		from trailers
               		where trailer_name like '%".sql_friendly($trail)."'
               		order by trailer_name asc,deleted asc,id asc
     			";
				$data = simple_query($sql);
				while($row = mysqli_fetch_array($data))
				{
					$trailer_id=$row['id'];
				}	
			}
		}
		if($trailer_id > 0)
		{	
			//create the link for truck maintenance...
			$txt="<a href='https://trucking.conardtransportation.com/maint.php?id=0&truck_id=".$truck_id."&trailer_id=".$trailer_id."&msg_id=".$msg_id."' target='_blank'>MAINT</a>";
			
			$notices.="".$txt." Request for Trailer sent by driver.<br>";
			$send_mailer++;
			
			$sqlu="update ".mrr_find_log_database_name()."truck_tracking_msg_history set alert_sent_flag='1' where id='".sql_friendly($msg_id)."'";
			simple_query($sqlu);
			
			if($first_id==0)		$first_id=$msg_id;
		}
	}
	
	
	if(trim($notices)=="" || $send_mail_off > 0)		$send_mailer=0;
	if($send_mailer > 0)
	{
		$email_from="system@conardtransportation.com";
		$email_from_name="PN Alert";
		$subject="Maint Requests from PN Message Sent (Msg ".$first_id.")";			
							
		$email_to="jdgriffith@conardtransportation.com";
		$email_to_name="Justin Griffith";
		
		//$ccemail_to="atomlin@conardtransportation.com";
		//$ccemail_to_name="Adam Tomlin";
		
		$ccemail_to2=$defaultsarray['special_email_monitor'];
		$ccemail_to_name2="Lord Vader";
		
		$ccemail_to3="jgriffith@conardtransportation.com";
		$ccemail_to_name3="James Griffith";
		
		$ccemail_to4="Rgriffith@conardtransportation.com";
		$ccemail_to_name4="Rusty Griffith";
		
		mrr_trucking_sendMail($email_to,$email_to_name,$email_from,$email_from_name,"","",$subject,$notices,$notices);	
		//mrr_trucking_sendMail($ccemail_to,$ccemail_to_name,$email_from,$email_from_name,"","",$subject,$notices,$notices);	
		//mrr_trucking_sendMail($ccemail_to2,$ccemail_to_name2,$email_from,$email_from_name,"","",$subject,$notices,$notices);	
		//mrr_trucking_sendMail($ccemail_to3,$ccemail_to_name3,$email_from,$email_from_name,"","",$subject,$notices,$notices);
		mrr_trucking_sendMail($ccemail_to4,$ccemail_to_name4,$email_from,$email_from_name,"","",$subject,$notices,$notices);				
	}
	
	return $txt;
}



function mrr_hot_load_msg_template($template,$row_load,$row_dispatch,$row_stop,$msg_body,$row_geo,$sector=0)
{
	global $defaultsarray;
	$from=$defaultsarray['company_email_address'];
	$fromname=$defaultsarray['company_name'];	
			
	$comp_logo = trim($defaultsarray['peoplenet_hot_msg_logo']);
	//$comp_logo2 = trim($defaultsarray['company_logo']);	
	
	$footer_msg="";		
	$footer_arriving=$defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$footer_arrived=$defaultsarray['peoplenet_hot_msg_arrived_insert'];
	$footer_departed=$defaultsarray['peoplenet_hot_msg_departed_insert'];
	if($sector==1)		$footer_msg=trim($footer_arriving);
	if($sector==2)		$footer_msg=trim($footer_arrived);
	if($sector==3)		$footer_msg=trim($footer_departed);
	
	$mrr_main_path="https://trucking.conardtransportation.com/";
	
	$stopper="Shipper";
	if($row_stop['stop_type_id']==2)	$stopper="Consignee";
					
	//truck, trailers and drivers
	$nametruck="";
	$sql="
		select * 
		from trucks
		where id='".sql_friendly($row_dispatch['truck_id'])."'				
	";
	$data_truck=simple_query($sql);
	$row_truck=mysqli_fetch_array($data_truck);
	$nametruck=$row_truck['name_truck'];
	
	$trailername1="";
	$trailername2="";
	$sql="
		select * 
		from trailers
		where id='".sql_friendly($row_stop['start_trailer_id'])."'				
	";
	$data_trailer=simple_query($sql);
	$row_trailer=mysqli_fetch_array($data_trailer);
	$trailername1=$row_trailer['trailer_name'];
	
	$namedriver1="";
	$namedriver2="";
	$sql="
		select * 
		from drivers
		where id='".sql_friendly($row_dispatch['driver_id'])."'				
	";
	$data_driver=simple_query($sql);
	$row_driver=mysqli_fetch_array($data_driver);
	$namedriver1=$row_driver['name_driver_first']." ".$row_driver['name_driver_last'];
	if($row_dispatch['driver2_id'] > 0)
	{
		$sql="
			select * 
			from drivers
			where id='".sql_friendly($row_dispatch['driver2_id'])."'				
		";
		$data_driver=simple_query($sql);
		$row_driver=mysqli_fetch_array($data_driver);
		$namedriver2=$row_driver['name_driver_first']." ".$row_driver['name_driver_last'];
	}
			
	$gmttime=gmdate("m/d/Y H:i:s");
	$localtime=date("m/d/Y H:i:s");	
	$localtime=date("m/d/Y H:i:s",strtotime("".($row_stop['timezone_offset']+$row_stop['timezone_offset_dst'])." seconds",strtotime($gmttime)));
	$localsuffix=mrr_decode_proper_timezone_label($row_stop['timezone_offset'],$row_stop['timezone_offset_dst']);  
	
	//all times for stamp are in CST/CDT=-21600 time
	$in_range="&nbsp;";
	$arr_time="&nbsp;";
	$dep_time="&nbsp;";	
			
	if($row_geo['linedate_last_arriving'] > '0000-00-00 00:00:00')
	{
		$in_range=date("m/d/Y H:i",strtotime($row_geo['linedate_last_arriving']));
		if($row_stop['timezone_offset'] != -21600)
		{
			$tmp_time=strtotime("+21600 seconds",strtotime($row_geo['linedate_last_arriving']));			//go back to GMT
			$tmp_date=strtotime("".$row_stop['timezone_offset']." seconds",strtotime($tmp_time));		//go to local time	
			$in_range=date("m/d/Y H:i",strtotime($tmp_date));
		}
	}
	if($row_geo['linedate_last_arrived'] > '0000-00-00 00:00:00')
	{
		$arr_time=date("m/d/Y H:i",strtotime($row_geo['linedate_last_arrived']));
		if($row_stop['timezone_offset'] != -21600)
		{
			$tmp_time=strtotime("+21600 seconds",strtotime($row_geo['linedate_last_arrived']));			//go back to GMT
			$tmp_date=strtotime("".$row_stop['timezone_offset']." seconds",strtotime($tmp_time));		//go to local time	
			$arr_time=date("m/d/Y H:i",strtotime($tmp_date));
		}
	}
	if($row_geo['linedate_last_departed'] > '0000-00-00 00:00:00')
	{
		$dep_time=date("m/d/Y H:i",strtotime($row_geo['linedate_last_departed']));
		if($row_stop['timezone_offset'] != -21600)
		{
			$tmp_time=strtotime("+21600 seconds",strtotime($row_geo['linedate_last_departed']));			//go back to GMT
			$tmp_date=strtotime("".$row_stop['timezone_offset']." seconds",strtotime($tmp_time));		//go to local time	
			$dep_time=date("m/d/Y H:i",strtotime($tmp_date));
		}
	}	
	if(substr_count($in_range,"12/31/1969") > 0)		$in_range="&nbsp;";	
	if(substr_count($arr_time,"12/31/1969") > 0)		$arr_time="&nbsp;";	
	if(substr_count($dep_time,"12/31/1969") > 0)		$dep_time="&nbsp;";	
	
	$mrr_template="
		<br><br>
		<table cellpadding='0' cellspacing='0' border='0' width='800'>
		<tr bgcolor='#000000' height='50'>
			<td valign='middle' colspan='4'><center><img src='".$mrr_main_path."".$comp_logo."' border='0' width='154' height='43' alt='".$fromname."'></center></td>
		<tr>
		<tr>
			<td valign='top' colspan='4'><center><b>".$fromname."</b><br>".$from."</center></td>
		<tr>     		
		<tr>
			<td valign='top' colspan='4'><br><center><b>Delivery Notification:</b></center><br><br></td>
		<tr>      		   		
		<tr>
			<td valign='top'>".$stopper."</td>
			<td valign='top'>".$row_stop['shipper_name']."</td>
			<td valign='top'>Your Load Number</td>
			<td valign='top'>".$row_load['load_number']."</td>
		<tr>  
		<tr>
			<td valign='top'>Address</td>
			<td valign='top'>".$row_stop['shipper_address1']."</td>
			<td valign='top'>Pickup Number</td>
			<td valign='top'>".$row_load['pickup_number']."</td>
		<tr>
		<tr>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>".$row_stop['shipper_address2']."</td>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
		<tr>
		<tr>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>".$row_stop['shipper_city'].", ".$row_stop['shipper_state']." ".$row_stop['shipper_zip']."</td>
			<td valign='top'>&nbsp;</td>
			<td valign='top'>&nbsp;</td>
		<tr>
		<tr>
			<td valign='top'>Phone</td>
			<td valign='top'>".$row_stop['stop_phone']."</td>
			<td valign='top'>Appointment Time</td>
			<td valign='top'>".date("m/d/Y H:i",strtotime($row_stop['linedate_pickup_eta']))." ".$localsuffix."</td>
		<tr>  		
		<tr>
			<td valign='top' colspan='4'><br><hr><br></td>
		<tr>     		
		<tr>
			<td valign='top'>Conard Load Number</td>
			<td valign='top'>".$row_load['id']."</td>
			<td valign='top'>Truck</td>
			<td valign='top'>".$nametruck."</td>
		<tr>
		
		<tr>
			<td valign='top'>Conard Dispatch Number</td>
			<td valign='top'>".$row_dispatch['id']."</td>
			<td valign='top'>Trailer</td>
			<td valign='top'>".$trailername1."</td>
		<tr>
		<tr>
			<td valign='top'>Conard Stop Number</td>
			<td valign='top'>".$row_stop['id']."</td>
			<td valign='top'>Driver(s)</td>
			<td valign='top'>".$namedriver1."<br>".$namedriver2."</td>
		<tr>
		
		<tr>
			<td valign='top' colspan='4'><br><hr><br></td>
		<tr> 
		<tr>
			<td valign='top' colspan='4'>Notice Date: ".$localtime." ".$localsuffix."</td>
		<tr> 
		<tr>
			<td valign='top' colspan='4'>".$msg_body."</td>
		<tr>  
		
		<tr>
			<td valign='top' colspan='4'>&nbsp;</td>
		<tr> 
		<tr>
			<td valign='top' colspan='4'>".$footer_msg."</td>
		<tr>		
		</table>
		<br><br>
	";		
	return $mrr_template;
	
}
function mrr_hot_load_msg_template_V2($template,$row_load,$row_dispatch,$row_stop,$msg_body,$sector=0)
{
	global $defaultsarray;
	$from=$defaultsarray['company_email_address'];
	$fromname=$defaultsarray['company_name'];	
			
	$comp_logo = trim($defaultsarray['peoplenet_hot_msg_logo']);
	
	$footer_msg="";		
	$footer_arriving=$defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$footer_arrived=$defaultsarray['peoplenet_hot_msg_arrived_insert'];
	$footer_departed=$defaultsarray['peoplenet_hot_msg_departed_insert'];
	if($sector==1)		$footer_msg=trim($footer_arriving);
	if($sector==2)		$footer_msg=trim($footer_arrived);
	if($sector==3)		$footer_msg=trim($footer_departed);
	
	if(trim($footer_msg)!="" && substr_count($msg_body,$footer_msg) > 0)	$footer_msg="";
	
	
	$mrr_main_path="https://trucking.conardtransportation.com/";		
			
	$stopper="Shipper";
	if($row_stop['stop_type_id']==2)	$stopper="Consignee";		
					
	//customer, truck, trailers and drivers
	$namecust="";
	$sql="
		select * 
		from customers
		where id='".sql_friendly($row_load['customer_id'])."'				
	";
	$data_cust=simple_query($sql);
	$row_cust=mysqli_fetch_array($data_cust);
	$namecust=$row_cust['name_company'];
	$mrr_cust_track_link="";
	if(trim($row_cust['customer_login_name'])!="" && trim($row_cust['customer_login_pass'])!="")
	{
		$mrr_cust_track_link="
		<tr>
			<td valign='top' colspan='2'>
				<center>
				<a href='https://trucking.conardtransportation.com/customer_loads.php?u=".$row_cust['customer_login_name']."&p=".$row_cust['customer_login_pass']."' target='_blank'>Track Currently Open and Recent Loads</a>
				</center>
				<br>
			</td>
		</tr>
		";		
	}
			
	$nametruck="";
	$sql="
		select * 
		from trucks
		where id='".sql_friendly($row_dispatch['truck_id'])."'				
	";
	$data_truck=simple_query($sql);
	$row_truck=mysqli_fetch_array($data_truck);
	$nametruck=$row_truck['name_truck'];
	
	$trailername1="";
	$trailername2="";
	$sql="
		select * 
		from trailers
		where id='".sql_friendly($row_stop['start_trailer_id'])."'				
	";
	$data_trailer=simple_query($sql);
	$row_trailer=mysqli_fetch_array($data_trailer);
	$trailername1=$row_trailer['trailer_name'];
	
	$namedriver1="";
	$namedriver2="";
	$sql="
		select * 
		from drivers
		where id='".sql_friendly($row_dispatch['driver_id'])."'				
	";
	$data_driver=simple_query($sql);
	$row_driver=mysqli_fetch_array($data_driver);
	$namedriver1=$row_driver['name_driver_first']." ".$row_driver['name_driver_last'];
	if($row_dispatch['driver2_id'] > 0)
	{
		$sql="
			select * 
			from drivers
			where id='".sql_friendly($row_dispatch['driver2_id'])."'				
		";
		$data_driver=simple_query($sql);
		$row_driver=mysqli_fetch_array($data_driver);
		$namedriver2=$row_driver['name_driver_first']." ".$row_driver['name_driver_last'];
	}
			
	$gmttime=gmdate("m/d/Y H:i");
	$localtime=date("m/d/Y H:i");	
	
	if(!isset($row_stop['timezone_offset']) || $row_stop['timezone_offset']==0)
	{	//if there is no timezone...to make sure we do not get GMT time...
		$row_stop['timezone_offset']=  -21600;
		$row_stop['timezone_offset_dst']= 3600;
	}
	
	$localtime=date("m/d/Y H:i",strtotime("".($row_stop['timezone_offset']+$row_stop['timezone_offset_dst'])." seconds",strtotime($gmttime)));
	$localsuffix=mrr_decode_proper_timezone_label($row_stop['timezone_offset'],$row_stop['timezone_offset_dst']);  
	
	$stops_table=mrr_quick_display_all_load_handler_stops($row_load['id'],$row_dispatch['id'],$row_stop['id']);
				
	$mrr_template="
		<br><br>
		<table cellpadding='0' cellspacing='0' border='0' width='1200'>
		<tr bgcolor='#000000' height='50'>
			<td valign='middle' colspan='2'><center><img src='".$mrr_main_path."".$comp_logo."' border='0' width='154' height='43' alt='".$fromname."'></center></td>
		</tr>  
		<tr>
			<td valign='top' colspan='2'><br><center><b>".$namecust." Load Notification: Load Number ".$row_load['load_number']."</b></center><br><br></td>
		</tr>  		
		".$mrr_cust_track_link." 	     		
		<tr>
			<td valign='top' width='450'>
				<div style='border:1px solid #000000; margin:10px; padding:10px; width:400px; min-width:400px; max-width:400px; height:250px; min-height:250px; max-height:250px;'>
					As of ".$localtime." ".$localsuffix.", <span style='font-size:14px;'>".$msg_body."</span>    					
					<div style='margin-top:15px;'>".$footer_msg." This is an automated message.</div>
				</div>
			</td>
			<td valign='top'>
				<br>
				<br>
				<table cellpadding='0' cellspacing='0' border='0' width='100%'>
				<tr>
          			<td valign='top'>From: </td>
          			<td valign='top'>".$from."</td>
          			<td valign='top'>&nbsp;</td>
          			<td valign='top'>&nbsp;</td>               			
          		</tr> 
				<tr>
          			<td valign='top'>".$stopper.": </td>
          			<td valign='top'>".$row_stop['shipper_name']."</td>
          			<td valign='top'>".$namecust." Load Number: </td>
          			<td valign='top'>".$row_load['load_number']."</td>
          		</tr>  
          		<tr>
          			<td valign='top'>Address: </td>
          			<td valign='top'>".$row_stop['shipper_address1']."</td>
          			<td valign='top'>Pickup Number: </td>
          			<td valign='top'>".$row_load['pickup_number']."</td>
          		</tr>
          		<tr>     			
          			<td valign='top'>&nbsp;</td>
          			<td valign='top'>".$row_stop['shipper_address2']."</td>
          			<td valign='top'>&nbsp;</td>
          			<td valign='top'>&nbsp;</td>
          		</tr>
          		<tr>     			
          			<td valign='top'>&nbsp;</td>
          			<td valign='top'>".$row_stop['shipper_city'].", ".$row_stop['shipper_state']." ".$row_stop['shipper_zip']."</td>
          			<td valign='top'>&nbsp;</td>
          			<td valign='top'>&nbsp;</td>
          		</tr>
          		<tr>     	
          			<td valign='top'>Phone: </td>
          			<td valign='top'>".$row_stop['stop_phone']."</td>
          			<td valign='top'>Appointment Time: </td>
          			<td valign='top'>".date("m/d/Y H:i",strtotime($row_stop['linedate_pickup_eta']))." ".$localsuffix."</td>
          		</tr>      		
          		<tr>     			
          			<td valign='top'>Conard Load Number: </td>
          			<td valign='top'>".$row_load['id']."</td>
          			<td valign='top'>Truck: </td>
          			<td valign='top'>".$nametruck."</td>
          		</tr>     		
          		<tr>
          			<td valign='top'>Conard Dispatch Number: </td>
          			<td valign='top'>".$row_dispatch['id']."</td>
          			<td valign='top'>Trailer: </td>
          			<td valign='top'>".$trailername1."</td>
          		</tr>
          		<tr>
          			<td valign='top'>Conard Stop Number: </td>
          			<td valign='top'>".$row_stop['id']."</td>
          			<td valign='top'>Driver(s): </td>
          			<td valign='top'>".$namedriver1."<br>".$namedriver2."</td>
          		</tr>
				</table>
			</td>
		</tr> 
		<tr>
			<td valign='top' colspan='2'><br><hr><br></td>
		</tr>
		<tr>
			<td valign='top' colspan='2' align='center'><b>Complete Load Stop List</b></td>
		</tr>
		<tr>
			<td valign='top' colspan='2'><br></td>
		</tr>
		<tr>
			<td valign='top' colspan='2'>".$stops_table."</td>
		</tr>     		
		</table>
		<br><br>
	";
	return $mrr_template;
	
}

function mrr_quick_display_all_load_handler_stops($load_id,$disp_id=0,$stop_id=0)
{
	$stops_table="";
	if($load_id > 0)
	{
		$stops_table.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>
					<tr>							
						<td valign='top'><b>Stop Type</b></td>
						<td valign='top'><b>Stop</b></td>
						<td valign='top'><b>Address</b></td>
						<td valign='top'><b>City</b></td>
						<td valign='top'><b>State</b></td>
						<td valign='top'><b>Zip</b></td>
						<td valign='top'><b>Due Date</b></td>
						<td valign='top'><b>Driver</b></td>
						<td valign='top'><b>Truck</b></td>
						<td valign='top'><b>Trailer</b></td>
						<td valign='top'><b>Arrival</b></td>
						<td valign='top'><b>Completed</b></td>
					</tr>
				";
		$stop_cntr=0;
		//get all dispatches
		$sqlx="
			select trucks_log.*,
				(select name_driver_first from drivers where drivers.id=trucks_log.driver_id) as driver_first_namer,
				(select name_driver_last from drivers where drivers.id=trucks_log.driver_id) as driver_last_namer,
				(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_namer
			from trucks_log
			where trucks_log.load_handler_id='".sql_friendly($load_id)."'
				and trucks_log.deleted<=0
			order by trucks_log.linedate_pickup_eta asc				
		";
		$datax=simple_query($sqlx);
		while($rowx=mysqli_fetch_array($datax))
		{
			$disp_selected=0;
			if($rowx['id'] == $disp_id)	
			{
				$disp_selected=1;	
			}
			
			//get all stops in this dispatch
			$sqly="
				select load_handler_stops.*,
					(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as trailer_namer
				from load_handler_stops
				where load_handler_stops.load_handler_id='".sql_friendly($load_id)."'
					and load_handler_stops.trucks_log_id='".sql_friendly($rowx['id'])."'
					and load_handler_stops.deleted<=0
				order by load_handler_stops.linedate_pickup_eta asc				
			";
			$datay=simple_query($sqly);
			while($rowy=mysqli_fetch_array($datay))
			{				
				$stop_selected=0;
				$tag1="";
				$tag2="";
				if($rowy['id'] == $stop_id)	
				{
					$stop_selected=1;	
					
					$tag1="<span style='color:blue; font-weight:bold;'>";
					$tag2="</span>";
				}
				
				$ship_type="Shipper";
				if($rowy['stop_type_id']==2)		$ship_type="Consignee";
				
				$localsuffix=mrr_decode_proper_timezone_label($rowy['timezone_offset'],$rowy['timezone_offset']);
				$localtime=date("m/d/Y H:i",strtotime($rowy['linedate_pickup_eta']));
                    $arrivaltime=date("m/d/Y H:i",strtotime($rowy['linedate_arrival']));
                    $delivtime=date("m/d/Y H:i",strtotime($rowy['linedate_completed']));
                    
                    $appoint_mask="".$localtime." ".$localsuffix."";					if(substr_count($appoint_mask,"12/31/1969")> 0)		$appoint_mask="";
				$arrival_mask="".$arrivaltime." ".$localsuffix.""; 				if(substr_count($arrival_mask,"12/31/1969")> 0)	     $arrival_mask="";
                    $complete_mask="".$delivtime." ".$localsuffix.""; 				if(substr_count($complete_mask,"12/31/1969")> 0)	$complete_mask="";
                    
				$driver_display=trim($rowx['driver_first_namer']." ".$rowx['driver_last_namer']);
				$truck_display=trim($rowx['truck_namer']);
				$trailer_display=trim($rowy['trailer_namer']);
				
				if(substr_count(strtolower($driver_display),"load reminder") > 0)	$driver_display="&nbsp;";
				if(substr_count(strtolower($driver_display),"***appt***") > 0)		$driver_display="&nbsp;";
				
				if(substr_count(strtolower($truck_display),"unknown") > 0)			$truck_display="&nbsp;";
				
				if(substr_count(strtolower($trailer_display),"test trailer") > 0)	$trailer_display="&nbsp;";
				if(substr_count(strtolower($trailer_display),"unknown") > 0)		$trailer_display="&nbsp;";
				
				$stops_table.="
					<tr".($stop_cntr%2==0 ? " style='background-color:#eeeeee;'" : "").">							
						<td valign='top'>".$tag1."".$ship_type."".$tag2."</td>							
						<td valign='top'>".$tag1."".$rowy['shipper_name']."".$tag2."</td>
						<td valign='top'>".$tag1."".$rowy['shipper_address1']." ".$rowy['shipper_address2']."".$tag2."</td>
						<td valign='top'>".$tag1."".$rowy['shipper_city']."".$tag2."</td>
						<td valign='top'>".$tag1."".$rowy['shipper_state']."".$tag2."</td>
						<td valign='top'>".$tag1."".$rowy['shipper_zip']."".$tag2."</td>
						<td valign='top' nowrap>".$tag1."".$appoint_mask."".$tag2."</td>
						<td valign='top'>".$tag1."".$driver_display."".$tag2."</td>
						<td valign='top'>".$tag1."".$truck_display."".$tag2."</td>
						<td valign='top'>".$tag1."".$trailer_display."".$tag2."</td>
						<td valign='top' nowrap>".$tag1."".$arrival_mask."".$tag2."</td>							
						<td valign='top' nowrap>".$tag1."".$complete_mask."".$tag2."</td>
					</tr>
				";	
				$stop_cntr++;
			}
		}
		$stops_table.="</table>";	
	}
	return $stops_table;
}


function mrr_geofencing_peoplnet_message($tolist,$subject,$message,$phoned=0,$use_departed=0)
{
	$sentit=0;
	
	$res['sendit']=$sentit;
	$res['sendto']=$tolist;
	
	global $defaultsarray;
	$from=$defaultsarray['company_email_address'];
	$fromname=$defaultsarray['company_name'];
	
	$monitor_email=$defaultsarray['peoplenet_hot_msg_cc'];
			
	$tolist=str_replace("<"," | ",$tolist);
	$tolist=str_replace("["," | ",$tolist);
	$tolist=str_replace(";",",",$tolist);
	$tolist=str_replace(">",",",$tolist);
	$tolist=str_replace("]",",",$tolist);
			
	$tos=explode(",",$tolist);
	$tomax=sizeof($tos);
	
	$sender=0;
	
	for($i=0;$i < $tomax; $i++)
	{
		$toline=trim($tos[$i]);
		$toname="";
		if(substr_count($toline,"|") > 0)
		{
			$poser=strpos($toline,"|");
			$len=strlen($toline);
			$toline=substr($toline,0,$poser);	
			$toname=substr($toline,$poser);
			$toline=trim($toline);
							
			$toname=str_replace("|","",$toname);
			$toname=trim($toname);
		}
		if(trim($toline)!="" && trim($toline)!=$defaultsarray['special_email_monitor'])
		{
			$sender=mrr_trucking_sendMail_PN($toline,$toname,$from,$fromname,$subject,$message,0,$phoned,$use_departed);
			mrr_peoplenet_last_msg_sent();
		}
		if($sender>0)		$sentit++;
		$res['sendit']=$sentit;
	}
	if($sentit==0 && trim($tolist)!="")
	{	//try it with only one email address
		$toline=trim($tolist);
		$toname="";
		if(substr_count($toline,"|") > 0)
		{
			$poser=strpos($toline,"|");
			$len=strlen($toline);
			$toline=substr($toline,0,$poser);	
			$toname=substr($toline,$poser);
			$toline=trim($toline);
							
			$toname=str_replace("|","",$toname);
			$toname=trim($toname);
		}
		if(trim($toline)!="")
		{
			$sender=mrr_trucking_sendMail_PN($toline,$toname,$from,$fromname,$subject,$message,0,$phoned,$use_departed);
			mrr_peoplenet_last_msg_sent();
		}
		if($sender>0)		$sentit++;	
		$res['sendit']=$sentit;
	}
	//send copy to monitor email...regardless
	if(trim($monitor_email)!="")
	{
		//$sender=mrr_trucking_sendMail_PN($monitor_email,"Dispatch",$from,$fromname,$subject,$message,0,$phoned,$use_departed);
		//mrr_peoplenet_last_msg_sent();
	}
		
	return $res;
}

function mrr_trucking_sendMail_PN($To,$ToName,$From,$FromName,$Subject,$Html,$send_it=0,$phoned=0,$use_departed=0)
{			
	/* mail using PHPMailer ...alternate function to show attachments and message in body of email.*/
	$to      = $To;		
	$subject = $Subject;		
	$message = trim($Html);
	
	global $defaultsarray;
	
	// To send HTML mail, the Content-type header must be set
     $headers  = 'MIME-Version: 1.0' . "\r\n";
     $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";          
     // Additional headers
     
     if($ToName!="")          $headers .= 'To: '.$ToName.' <'.$To.'>' . "\r\n";	//, Kelly <kelly@example.com>
     
     $headers .= 'From: '.$FromName.' <'.$From.'>' . "\r\n";
	
	if($ToName=="Dispatch")	$subject="Dispatch Monitor: ".($use_departed > 0  ? " [DEPARTED]" : "")."".($phoned > 0 ? " [PHONED] " : "")."".$subject;
	
	//SWITCH CONTROLS...
	
	$send_it=1;															//send all emails...
	
	$send_test=0;															//use test mode...
	
	if($send_test==1 && $to!=$defaultsarray['special_email_monitor'])		$send_it=0;		//cancel send mode unless dispatch monitor....	
	
	if($to==$defaultsarray['special_email_monitor'])		{	$send_it=1;	$send_test=1;	}	//always send to me... test switch turned on...
	if(substr_count($subject,"Check Call") > 0)	{	$send_it=1;	$send_test=0;	}	//always send check call... testing mode off.
	
	
	//SEND IF CONTROLS ALLOW...
	/*if($send_it > 0)
	{			
		if($send_test > 0)
		{
			@ $sent=mail($to, "TEST ".$subject, $message, $headers);
		}
		else
		{
			@ $sent=mail($to, $subject, $message, $headers);
		}
	}
	else
	{	//deactivated
		$sent=1;
	}*/


	if ($send_it > 0) {
		if (!empty($to) && filter_var($to, FILTER_VALIDATE_EMAIL)) {
			if ($send_test > 0) {
				$sent = @mail($to, "TEST " . $subject, $message, $headers);
			} else {
				$sent = @mail($to, $subject, $message, $headers);
			}
		} else {
			error_log("Invalid or empty email address: '$to'");
			$sent = 0; // or false
		}
	} else {
		// email sending deactivated
		$sent = 1;
	}

	return $sent;
}

function mrr_repair_gps_points_on_load($load_id=0,$min_id=0)
{
     $show_output=0;
     if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' && $load_id > 0)     $show_output=1;
     
     $last_stop_id=0;
     $latitude=0.000000;
     $longitude=0.000000;
     
     $sql="
          select id,load_handler_id,shipper_address1,shipper_city,shipper_state,shipper_zip
          from load_handler_stops
          where deleted<=0
               and latitude=0
               and longitude=0
               ".($load_id > 0 ? "and load_handler_id='".sql_friendly($load_id)."'" : "")."
               ".($min_id > 0 ? "and id > '".sql_friendly($min_id)."'" : "and linedate_pickup_eta>='".date("Y-m",time())."-01 00:00:00'")."
          order by id asc
          limit 200
     ";
     $data=simple_query($sql);
     if(mysqli_num_rows($data) > 0 && $show_output > 0 && $min_id==0)
     {
          echo "<br><br><br><br><br><br>Repairing Load ".$load_id.".";
          echo "<br>Query=".$sql.".";
     }
     while($row=mysqli_fetch_array($data))
     {
          $stop_id=$row['id'];
          $my_id=$row['load_handler_id'];
          $addr=trim($row['shipper_address1']);
          $city=trim($row['shipper_city']);
          $state=trim($row['shipper_state']);
          $zip=trim($row['shipper_zip']);
     
          $last_stop_id=$stop_id;
     
          $sql2="
			select latitude,longitude,linedate_pickup_eta
			from load_handler_stops
			where deleted<=0
			     and latitude > 0
			     and longitude < 0
				and load_handler_id!='".sql_friendly($load_id)."'
				and shipper_address1='".sql_friendly($addr)."'
				and shipper_city='".sql_friendly($city)."'
				and shipper_state='".sql_friendly($state)."'
				and shipper_zip='".sql_friendly($zip)."'
			order by linedate_pickup_eta desc
		";
          if($show_output > 0 && $min_id==0)          echo "<br>Sub-Query for ".$stop_id.". Query is ".$sql2."";
          $data2=simple_query($sql2);
          if($row2=mysqli_fetch_array($data2))
          {
               $latitude=$row2['latitude'];
               $longitude=$row2['longitude'];
     
               $sqlu="
                    update load_handler_stops set
                         latitude='".$latitude."',
                         longitude='".$longitude."'
                    where id='".sql_friendly($stop_id)."'
                         and load_handler_id='".sql_friendly($my_id)."'
                         and latitude=0
                         and longitude=0
               ";
               if($show_output > 0 && $min_id==0)          echo "<br>Update (1) Stop Query=".$sqlu.".";
               if($min_id > 0)     echo "<br>Update (1) Stop ".$stop_id." GPS {".$latitude.", ".$longitude."} for Load ".$my_id."...";   // Query=".$sqlu.".
               simple_query($sqlu);
          }
          else
          {
               $sql2="
                    select id,latitude,longitude
                    from geotab_stop_zones
                    where deleted<=0
                         and latitude > 0
                         and longitude < 0
                         and address_1='".sql_friendly($addr)."'
                         and city='".sql_friendly($city)."'
                         and state='".sql_friendly($state)."'
                         and zip='".sql_friendly($zip)."'
                    order by id desc
               ";
               if($show_output > 0 && $min_id==0)          echo "<br>Sub-Query for ".$stop_id.". Query is ".$sql2."";
               $data2=simple_query($sql2);
               if($row2=mysqli_fetch_array($data2))
               {
                    $latitude=$row2['latitude'];
                    $longitude=$row2['longitude'];
     
                    $sqlu="
                         update load_handler_stops set
                              latitude='".$latitude."',
                              longitude='".$longitude."'
                         where id='".sql_friendly($stop_id)."'
                              and load_handler_id='".sql_friendly($my_id)."'
                              and latitude=0
                              and longitude=0
                    ";
                    if($show_output > 0 && $min_id==0)          echo "<br>Update (2) Stop Query=".$sqlu.".";
                    if($min_id > 0)     echo "<br>Update (2) Stop ".$stop_id." GPS {".$latitude.", ".$longitude."} for Load ".$my_id."...";   // Query=".$sqlu.".
                    simple_query($sqlu);
               }
          }
          
          //see if the GPS point is still NOT set... If still zeros, look for simple address GPS.
          if($latitude==0.000000 && $longitude==0.000000)
          {
               $sql2="
                    select id,latitude,longitude
                    from geotab_stop_zones
                    where deleted<=0
                         and latitude > 0
                         and longitude < 0
                         and city='".sql_friendly($city)."'
                         and state='".sql_friendly($state)."'
                         and zip='".sql_friendly($zip)."'
                    order by id desc
               ";
               if($show_output > 0 && $min_id==0)          echo "<br>Sub-Query for ".$stop_id.". Query is ".$sql2."";
               $data2=simple_query($sql2);
               if($row2=mysqli_fetch_array($data2))
               {
                    $latitude=$row2['latitude'];
                    $longitude=$row2['longitude'];
          
                    $sqlu="
                         update load_handler_stops set
                              latitude='".$latitude."',
                              longitude='".$longitude."'
                         where id='".sql_friendly($stop_id)."'
                              and load_handler_id='".sql_friendly($my_id)."'
                              and latitude=0
                              and longitude=0
                    ";
                    if($show_output > 0 && $min_id==0)          echo "<br>Update (3) Stop Query=".$sqlu.".";
                    if($min_id > 0)     echo "<br>Update (3) Stop ".$stop_id." GPS {".$latitude.", ".$longitude."} for Load ".$my_id."...";   // Query=".$sqlu.".
                    simple_query($sqlu);
               }
          }
          
          //still blank GPS coordinate points... so look it up directly from GeoTab API.
          if($latitude==0.000000 && $longitude==0.000000)
          {
               $res=mrr_geotab_get_coordinate_from_addr($addr,$city,$state,$zip);
               $latitude=$res['lat'];
               $longitude=$res['long'];
     
               $sqlu="
                         update load_handler_stops set
                              latitude='".$latitude."',
                              longitude='".$longitude."'
                         where id='".sql_friendly($stop_id)."'
                              and load_handler_id='".sql_friendly($my_id)."'
                              and latitude=0
                              and longitude=0
                    ";
               if($show_output > 0 && $min_id==0)          echo "<br>Update (4) Stop Query=".$sqlu.".";
               if($min_id > 0)     echo "<br>Update (4) Stop ".$stop_id." GPS {".$latitude.", ".$longitude."} for Load ".$my_id."...";   // Query=".$sqlu.".
               simple_query($sqlu);
          }
     }
     return $last_stop_id;
}

function mrr_restore_backup_load_stop_gps_points($min_id=0)
{
     //$restore_db="ct_backup091517.";
     $restore_db="";
     $table_name="load_handler_stops_bk";
     //$min_id=54623;
     $stop_id=0;
     $sql="
          select id,latitude,longitude
          from ".$restore_db."".$table_name."
          where latitude>0 and longitude<0
               and id>='".$min_id."'
          order by id asc
          limit 2500
     ";
     $data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {
          $stop_id=$row['id'];
          $latitude=$row['latitude'];
          $longitude=$row['longitude'];
          
          $sqlu="
                    update load_handler_stops set
                         latitude='".$latitude."',
                         longitude='".$longitude."'
                    where id='".sql_friendly($stop_id)."'
               ";
          simple_query($sqlu);
          echo "<br>Updated Load Stop ID ".$stop_id." for GPS {".$latitude." , ".$longitude."}.";
     }
     return $stop_id;
}


//FUNCTIONS HERE ARE FOR TRACKING WHEN CONARD USERS ARE USING THE SAME PAGES (LOADS AND DISPATCHES FOR NOW)...added on 1/31/2020...MRR
function mrr_user_page_editing_decoder($cd=0)
{
     if($cd > 2)         $cd=0;
     $arr[0]="N/A";
     $arr[1]="Load";
     $arr[2]="Dispatch";
     
     return $arr[$cd];     
}
function mrr_user_page_editing_add($pg_type=0,$pg_id=0,$user=0,$url="")
{
	global $datasource;

     mrr_user_page_editing_del($pg_type,0,$user);           //force them all closed...
     
     $sql="
          insert into user_page_editing 
                (id,
                linedate_added,
                user_id,
                page_type,
                page_id,
                page_url,
                deleted)
          values 
                (NULL,
                NOW(),
                '".sql_friendly($user)."',
                '".sql_friendly($pg_type)."',
                '".sql_friendly($pg_id)."',
                '".sql_friendly($url)."',
                0)
     ";
     simple_query($sql);
     return mysqli_insert_id($datasource);
}
function mrr_user_page_editing_del($pg_type=0,$pg_id=0,$user=0)
{
     if($pg_type > 0 && $pg_id==0 && $user > 0)
     {    //clear ALL pages for this user that are in this type...sicne the USER OBVIOUSLY is not closing them correctly...
          $sql="
            update user_page_editing set
                    deleted='1'
            where user_id='".(int) $user."' 
                    and page_type='".(int) $pg_type."' 
          "; 
     }
     else
     {    //just update the indicated page.
          $sql="
            update user_page_editing set
                    deleted='1'
            where user_id='".(int) $user."' 
                    and page_type='".(int) $pg_type."' 
                    and page_id='".(int) $pg_id."'
          ";
     }
     
     
     simple_query($sql);
     $sql="
          delete from user_page_editing
          where linedate_added<'".date("Y-m-d",time())." 00:00:00' or user_id='23'
     ";
     simple_query($sql);
}
function mrr_user_page_editing_finder($pg_type=0,$pg_id=0,$user=0,$not_user=0)
{
     $tab="";
     $cntr=0;
     
     $tab.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
     $tab.="
               <tr>
                    <td valign='top'>Use#</td>
                    <td valign='top'>Date</td>
                    <td valign='top'>First Name</td>
                    <td valign='top'>Last Name</td>
                    <td valign='top'>Section</td>
                    <td valign='top'>ID</td>
                    <td valign='top'>URL</td>
               </tr>
          ";
     
     $sql="
          select user_page_editing.*,
                users.name_first,
                users.name_last
          from user_page_editing 
                left join users on users.id=user_page_editing.user_id
          where user_page_editing.deleted=0
                ".($pg_type > 0 && $pg_id > 0 ? " and user_page_editing.page_type='".(int) $pg_type."' and user_page_editing.page_id='".(int) $pg_id."'" : "")."
                ".($user > 0 ? "and user_page_editing.user_id='".(int) $user."'" : "")."
                ".($not_user > 0 ? "and user_page_editing.user_id!='".(int) $not_user."'" : "")."
          order by user_page_editing.linedate_added asc,user_page_editing.id
     ";
     $data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {
          $tab.="
               <tr class='".($cntr % 2==0 ? "even" : "odd")."'>
                    <td valign='top'>".($cntr+1)."</td>
                    <td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
                    <td valign='top'>".$row['name_first']."</td>
                    <td valign='top'>".$row['name_last']."</td>
                    <td valign='top'>".mrr_user_page_editing_decoder($row['page_type'])."</td>
                    <td valign='top'>".$row['page_id']."</td>
                    <td valign='top'>".$row['page_url']."</td>
               </tr>
          ";
          $cntr++;
     }
     $tab.="</table><br>You can update these on <a href='report_dispatch_load_users.php'>Page User Link Report</a> if you know they are no longer in it.";
     if($cntr==0)   $tab="";
     return $tab;
}

?>