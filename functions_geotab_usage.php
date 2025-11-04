<?
//Functions to link tracking code to/from dispatch side and GeoTab API using the core functions in functions_geotab.php file.
//Most of these functions are equivalent to the previous PeopleNet version only use the GeoTab API instead of those from PN.
//Created on 3/30/2018 by MRR

function mrr_geotab_truck_selector($field,$pre=0)
{
	$selbox="<select name='".$field."' id='".$field."' style='width:150px;'>";	
	$sel="";		if($pre==0)		$sel=" selected";	
	$selbox.="<option value='0'".$sel.">All Trucks</option>";
	//$sel="";		if($pre==-1)		$sel=" selected";	
	//$selbox.="<option value='-1'".$sel.">Pick From List</option>";
			 
	$sql = "
		select *
		from trucks
		where deleted <= 0
			and geotab_device_id!=''
			and peoplenet_tracking > 0
			and active > 0
		order by name_truck asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		$sel="";		if($pre==$row['id'])		$sel=" selected";	
		$selbox.="<option value='".$row['id']."'".$sel.">".$row['name_truck']."</option>";
	}	
	
	//test truck added
	//$sel="";		if($pre==1520428)		$sel=" selected";	
	//$selbox.="<option value='". 1520428 ."'".$sel.">". 1520428 ."</option>";
			
	$selbox.="</select>";
	return $selbox;	
}
function mrr_geotab_trailer_selector($field,$pre=0)
{
	$selbox="<select name='".$field."' id='".$field."' style='width:150px;'>";	
	$sel="";		if($pre==0)		$sel=" selected";	
	$selbox.="<option value='0'".$sel.">All Trailers</option>";
	//$sel="";		if($pre==-1)		$sel=" selected";	
	//$selbox.="<option value='-1'".$sel.">Pick From List</option>";
		 
	$sql = "
		select *
		from trailers
		where deleted <= 0
			and geotab_trailer_id!=''
			and active > 0
		order by name_truck asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		$sel="";		if($pre==$row['id'])		$sel=" selected";	
		$selbox.="<option value='".$row['id']."'".$sel.">".$row['name_truck']."</option>";
	}	
	
	//test truck added
	//$sel="";		if($pre==1520428)		$sel=" selected";	
	//$selbox.="<option value='". 1520428 ."'".$sel.">". 1520428 ."</option>";
			
	$selbox.="</select>";
	return $selbox;	
}
function mrr_find_geotab_trailer_by_id($geotab_id,$cd)
{	
	$name="";
	$id=0;
	
	$sql="
		select id,trailer_name,nick_name
		from trailers
		where geotab_trailer_id='".sql_friendly(trim($geotab_id))."'	
	";	
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$name=trim($row['trailer_name']);
		if($cd==2)		$name=trim($row['nick_name']);
		$id=$row['id'];
	}
	if($cd > 0)		return $name;
	return $id;
}
function mrr_find_geotab_trailer_id_by_id($id)
{	
	$geotab_id="";
	
	$sql="
		select geotab_trailer_id
		from trailers
		where id='".sql_friendly($id)."'	
	";	
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$geotab_id=trim($row['geotab_trailer_id']);
	}
	return $geotab_id;
}

function mrr_find_geotab_driver_by_id($id,$cd=0)
{	
	$geotab_id="";
	$truck_id=0;
	$truck2_id=0;
	
	$sql="
		select geotab_use_id,attached_truck_id,attached2_truck_id
		from drivers
		where id='".sql_friendly($id)."'	
	";	
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$geotab_id=trim($row['geotab_use_id']);
		$truck_id=$row['attached_truck_id'];
		$truck2_id=$row['attached2_truck_id'];
	}
	if($cd==1)	return $truck_id;
	if($cd==2)	return $truck2_id;
	return $geotab_id;
}

function mrr_find_geotab_truck_by_id($geotab_id,$cd)
{	
	$name="";
	$id=0;	
	$sql="
		select id,name_truck
		from trucks
		where geotab_device_id='".sql_friendly(trim($geotab_id))."'	
	";	
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$name=trim($row['name_truck']);
		//if($cd==2)		$name=trim($row['nick_name']);
		$id=$row['id'];
	}
	if($cd > 0)		return $name;
	return $id;
}
function mrr_find_geotab_truck_id_by_id($id)
{	
	$geotab_id="";	
	$sql="
		select geotab_device_id
		from trucks
		where id='".sql_friendly($id)."'	
	";	
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$geotab_id=trim($row['geotab_device_id']);
	}
	return $geotab_id;
}
function mrr_geotab_truck_grouper($field,$pre)
{	//pre is the group of selected trucks...
	$selbox="<div style='width:600px; height:200px; padding:5px; margin:5px; overflow:auto; border:1px solid #ffcc00; background-color:#ffffff;'>";	
	$selbox.="<table cellpadding='0' cellspacing='0' width='100%' border='0'>";	 
	$selbox.="<tr>";
	
	$cntr=0;
	$sql = "
		select *
		from trucks
		where deleted <= 0
			and geotab_device_id!=''
			and active > 0
		order by name_truck asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		if($cntr%4==0 and $cntr > 0)		$selbox.="</tr><tr>";
		
		$sel="";	
		for($i=0; $i < count($pre); $i++)
		{
			if($pre[$i]==$row['id'])		$sel=" checked";	
		}
		$selbox.="
			<td valign='top' width='25%'>
				<label><input type='checkbox' name='".$field."[]' value='".$row['id']."'".$sel."> ".$row['name_truck']."</label>
				<input type='hidden' name='".$field."_names[]' value='".$row['name_truck']."'>
			</td>
		";	// id='".$field."[]'
		
		$cntr++;
	}	
	$selbox.="</tr>";
	$selbox.="</table>";	
	$selbox.="</div>";
	return $selbox;	
}
function mrr_geotab_truck_array()
{	//get all trucks in the array that have peoplenet tracking....built name list with it...
	$arr[0]=0;
	$names[0]="";
	$device[0]="";
	$cntr=0;
		 
	$sql = "
		select *
		from trucks
		where deleted <= 0
			and geotab_device_id!=''
			and peoplenet_tracking > 0
			and active > 0
		order by name_truck asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		$arr[ $cntr ]=$row['id'];
		$device[ $cntr ]=trim($row['geotab_device_id']);
		$names[ $cntr ]=str_replace(" (Team Rate)","",trim($row['name_truck']));
		$cntr++;
	}	
	
	$res['num']=$cntr;
	$res['arr']=$arr;
	$res['device']=$device;
	$res['names']=$names;
	return $res;	
}

function mrr_geotab_datestring_display_alt($dstr)
{
	$gmt_offset= mrr_gmt_offset_val();
	
	$dstr=trim($dstr);
	if(strlen($dstr) > 19)	$dstr=substr($dstr,0,19);
	$dstr=trim(str_replace("T"," ",$dstr));
	
	$dstr=date("m/d/Y H:i:s", strtotime("-".$gmt_offset." hours",strtotime($dstr)));
	return $dstr;
}

//ZONES
function mrr_create_geotab_stop_zones($res)
{	
	global $datasource;

	//makes a zone so that the API does not have to be run every single time the stop is reused...especially when there are so many like "Conard Terminal"
	$sql="
		insert into geotab_stop_zones
			(id,
			geotab_id_name,
			conard_name,
			address_1,
			city, 
			state,
			zip,
			longitude,
			latitude,
			long_zone_w,
			long_zone_e,
			lat_zone_n,
			lat_zone_s,
			linedate_added,
			deleted)			
		values
			(NULL,
			'".sql_friendly(trim($res['geotab_id_name']))."',
			'".sql_friendly(trim($res['conard_name']))."',
			'".sql_friendly(trim($res['address_1']))."',
			'".sql_friendly(trim($res['city']))."',
			'".sql_friendly(trim($res['state']))."',
			'".sql_friendly(trim($res['zip']))."',
			'".sql_friendly(trim($res['long']))."',
			'".sql_friendly(trim($res['lat']))."',
			'".sql_friendly(trim($res['long_zone_w']))."',
			'".sql_friendly(trim($res['long_zone_e']))."',
			'".sql_friendly(trim($res['lat_zone_n']))."',
			'".sql_friendly(trim($res['lat_zone_s']))."',			
			NOW(),
			0)		
	";	
	simple_query($sql);
	$new_id=mysqli_insert_id($datasource);
	return $new_id;
}

function mrr_update_geotab_stop_zones_points_name($res)
{	//makes a zone so that the API does not have to be run every single time the stop is reused...especially when there are so many like "Conard Terminal"
	$sql="
		update geotab_stop_zones set
		
			geotab_id_name='".sql_friendly(trim($res['geotab_id_name']))."'
			
		where deleted<=0
			and long_zone_w='".sql_friendly(trim($res['long_zone_w']))."'
			and long_zone_e='".sql_friendly(trim($res['long_zone_e']))."'
			and lat_zone_n='".sql_friendly(trim($res['lat_zone_n']))."'
			and lat_zone_s='".sql_friendly(trim($res['lat_zone_s']))."'	
	";	
	simple_query($sql);
}

function mrr_update_geotab_stop_zones_gps_name($res,$zone=0)
{	//makes a zone so that the API does not have to be run every single time the stop is reused...especially when there are so many like "Conard Terminal"
		
	$sql="
		update geotab_stop_zones set
			".($zone > 0 ? "long_zone_w='".sql_friendly(trim($res['long_zone_w']))."'," : "")."
			".($zone > 0 ? "long_zone_e='".sql_friendly(trim($res['long_zone_e']))."'," : "")."
			".($zone > 0 ? "lat_zone_n='".sql_friendly(trim($res['lat_zone_n']))."'," : "")."
			".($zone > 0 ? "lat_zone_s='".sql_friendly(trim($res['lat_zone_s']))."'," : "")."
			geotab_id_name='".sql_friendly(trim($res['geotab_id_name']))."'
			
		where deleted<=0
			and longitude='".sql_friendly(trim($res['long']))."'
			and latitude='".sql_friendly(trim($res['lat']))."'	
	";	
	simple_query($sql);
}
function mrr_update_geotab_stop_zones_gps_shipper($res)
{	//makes a zone so that the API does not have to be run every single time the stop is reused...especially when there are so many like "Conard Terminal"
	$sql="
		update geotab_stop_zones set
		
			conard_name='".sql_friendly(trim($res['conard_name']))."'
			
		where deleted<=0
			and longitude='".sql_friendly(trim($res['long']))."'
			and latitude='".sql_friendly(trim($res['lat']))."'	
	";	
	simple_query($sql);
}
function mrr_update_geotab_stop_zones_points_name_id($res)
{	//makes a zone so that the API does not have to be run every single time the stop is reused...especially when there are so many like "Conard Terminal"
	$sql="
		update geotab_stop_zones set
		
			geotab_id_name='".sql_friendly(trim($res['geotab_id_name']))."'
			
		where deleted<=0
			and longitude='".sql_friendly(trim($res['long']))."'
			and latitude='".sql_friendly(trim($res['lat']))."'	
	";	
	simple_query($sql);
}

function mrr_update_geotab_stop_zones_gps_addr($res)
{	//makes a zone so that the API does not have to be run every single time the stop is reused...especially when there are so many like "Conard Terminal"
	$sql="
		update geotab_stop_zones set
		
			address_1='".sql_friendly(trim($res['address_1']))."',
			city='".sql_friendly(trim($res['city']))."', 
			state='".sql_friendly(trim($res['state']))."',
			zip='".sql_friendly(trim($res['zip']))."'
			
		where deleted<=0
			and longitude='".sql_friendly(trim($res['long']))."'
			and latitude='".sql_friendly(trim($res['lat']))."'	
	";	
	simple_query($sql);
}
function mrr_update_geotab_stop_zones_gps_shipper_by_id($res)
{	//makes a zone so that the API does not have to be run every single time the stop is reused...especially when there are so many like "Conard Terminal"
	$sql="
		update geotab_stop_zones set
		
			conard_name='".sql_friendly(trim($res['conard_name']))."'
			
		where id='".sql_friendly($res['id'])."'	
	";	
	simple_query($sql);
}
function mrr_update_geotab_stop_zones_points_name_by_id($res)
{	//makes a zone so that the API does not have to be run every single time the stop is reused...especially when there are so many like "Conard Terminal"
	$sql="
		update geotab_stop_zones set
		
			geotab_id_name='".sql_friendly(trim($res['geotab_id_name']))."'
			
		where id='".sql_friendly($res['id'])."'	
	";	
	simple_query($sql);
}
function mrr_find_geotab_stop_zones_by_id($id)
{	//Gets the name based on hte zone ID given.
	$name="";
	
	$sql="
		select geotab_id_name 
		from geotab_stop_zones
		where id='".sql_friendly($id)."'	
	";	
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$name=trim($row['geotab_id_name']);
	}
	return $name;
}
function mrr_find_geotab_stop_zones_by_gps_from_id($id)
{	//uses exact GPS match to find this zone...long and lat are for the center point., the other four represent the min/max of each direction.
	$res['id']=0;
	$res['geotab_id_name']="";
	$res['conard_name']="";
	$res['address_1']="";
	$res['city']="";
	$res['state']="";
	$res['zip']="";
	$res['long']="";
	$res['lat']="";
	$res['long_zone_w']="";
	$res['long_zone_e']="";
	$res['lat_zone_n']="";
	$res['lat_zone_s']="";
	
	$sql="
		select * 
		from geotab_stop_zones
		where id='".sql_friendly($id)."'	
	";	
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$res['id']=$row['id'];
		$res['geotab_id_name']=trim($row['geotab_id_name']);
		$res['address_1']=trim($row['address_1']);
		$res['city']=trim($row['city']);
		$res['state']=trim($row['state']);
		$res['zip']=trim($row['zip']);
		$res['long']=trim($row['longitude']);
		$res['lat']=trim($row['latitude']);
		$res['long_zone_w']=trim($row['long_zone_w']);
		$res['long_zone_e']=trim($row['long_zone_e']);
		$res['lat_zone_n']=trim($row['lat_zone_n']);
		$res['lat_zone_s']=trim($row['lat_zone_s']);
		
		$res['conard_name']=trim($row['conard_name']);
	}
	return $res;
}
function mrr_find_geotab_stop_zones_by_gps($long,$lat)
{	//uses exact GPS match to find this zone...long and lat are for the center point., the other four represent the min/max of each direction.
	$res['id']=0;
	$res['geotab_id_name']="";
	$res['conard_name']="";
	$res['address_1']="";
	$res['city']="";
	$res['state']="";
	$res['zip']="";
	$res['long']="";
	$res['lat']="";
	$res['long_zone_w']="";
	$res['long_zone_e']="";
	$res['lat_zone_n']="";
	$res['lat_zone_s']="";
	
	$sql="
		select * 
		from geotab_stop_zones
		where deleted<=0
			and longitude='".sql_friendly(trim($long))."'
			and latitude='".sql_friendly(trim($lat))."'			
		order by geotab_id_name desc,linedate_added asc		
	";	
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$res['id']=$row['id'];
		$res['geotab_id_name']=trim($row['geotab_id_name']);
		$res['address_1']=trim($row['address_1']);
		$res['city']=trim($row['city']);
		$res['state']=trim($row['state']);
		$res['zip']=trim($row['zip']);
		$res['long']=trim($row['longitude']);
		$res['lat']=trim($row['latitude']);
		$res['long_zone_w']=trim($row['long_zone_w']);
		$res['long_zone_e']=trim($row['long_zone_e']);
		$res['lat_zone_n']=trim($row['lat_zone_n']);
		$res['lat_zone_s']=trim($row['lat_zone_s']);
		
		$res['conard_name']=trim($row['conard_name']);
	}
	return $res;
}
function mrr_find_geotab_stop_zones_by_addr($addr,$city,$state,$zip)
{	//use the street address to find the rest of the GPS zone...long and lat are center of stop, the other four represent the min/max of each direction.
	$res['id']=0;
	$res['geotab_id_name']="";
	$res['conard_name']="";
	$res['address_1']="";
	$res['city']="";
	$res['state']="";
	$res['zip']="";
	$res['long']="";
	$res['lat']="";
	$res['long_zone_w']="";
	$res['long_zone_e']="";
	$res['lat_zone_n']="";
	$res['lat_zone_s']="";
		
	$sql="
		select * 
		from geotab_stop_zones
		where deleted<=0
			and address_1='".sql_friendly(trim($addr))."'
			and city='".sql_friendly(trim($city))."'
			and state='".sql_friendly(trim($state))."'
			and zip='".sql_friendly(trim($zip))."'
			
		order by geotab_id_name desc,linedate_added asc		
	";	
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$res['id']=$row['id'];
		$res['geotab_id_name']=trim($row['geotab_id_name']);
		$res['address_1']=trim($row['address_1']);
		$res['city']=trim($row['city']);
		$res['state']=trim($row['state']);
		$res['zip']=trim($row['zip']);
		$res['long']=trim($row['longitude']);
		$res['lat']=trim($row['latitude']);
		$res['long_zone_w']=trim($row['long_zone_w']);
		$res['long_zone_e']=trim($row['long_zone_e']);
		$res['lat_zone_n']=trim($row['lat_zone_n']);
		$res['lat_zone_s']=trim($row['lat_zone_s']);
		
		$res['conard_name']=trim($row['conard_name']);
	}
	return $res;
}

function mrr_find_geotab_location_of_this_truck($truck_id=0,$poll_now=0)
{	//this function gets the GeoTab feed for truck locations and 	
	
	if($poll_now > 0)		$polled=mrr_get_geotab_get_datafeed("LogRecord");
	
	$long="0";
	$lat="0";
	$truck_speed=0;
	$location="";
	$gps_location="";
	$date="";
	
	$truck_name="";
	$geotab_id="";
		
	if($truck_id>0)
	{
		$sql = "
			select *
			from trucks
			where deleted <= 0
				and trucks.id = '".sql_friendly($truck_id) ."'
			order by name_truck asc
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$truck_name=$row['name_truck'];
			$geotab_id="".trim($row['geotab_device_id'])."";
		}		
	}
							
	//if($truck_id==1520428)		$truck_name="1520428";		
	
	if(trim($geotab_id)!="" && trim($truck_name)!="")
	{
		$sql="
			select geotab_id,linedate_added,speed_mph,latitude,longitude
			from ".mrr_find_log_database_name()."geotab_datafeed_log
			where device_id='".sql_friendly($geotab_id)."'
				and feed_type=1
			order by id desc
			limit 1			
		";
		//$gps_location=$sql;
		$data = simple_query($sql);
          if($row = mysqli_fetch_array($data))
          {          	
          	$long=$row['longitude'];	
          	$lat=$row['latitude'];
          	$truck_speed=$row['speed_mph'];
          	
          	$truck_speed=$truck_speed / 1.6;
          	
          	$date="".date("m/d/Y H:i",strtotime($row['linedate_added']))."";
          	          	
          	$res=mrr_geotab_reverse_geocode_address_from_point($long,$lat);
          	
          	$location="".$res['address_1']."; ".$res['city'].", ".$res['state']." ".$res['zip']."";
          	$gps_location="".$res['address_1']."; ".$res['city'].", ".$res['state']." ".$res['zip']."";
          }			
		
	}
	
	$res['truck_name']=$truck_name;
	$res['device_id']=$geotab_id;
	
	$res['date']=$date;
	
	$res['longitude']=$long;
	$res['latitude']=$lat;
	$res['truck_speed']=$truck_speed;
	$res['location']=$location;
	$res['gps_location']=$gps_location;	
	return $res;
}

//Dispatches...
function mrr_send_geotab_complete_dispatch($find_load_id,$run_dispatch=0,$find_truck_id=0,$test_mode=0)
{
	global $defaultsarray;
	$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
	$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;

	$geotab_load_id="";
	$geotab_disp_id="";
	$geotab_stop_id="";
	$geotab_zone_id="";
		     
     $disp_cntr=0;
     $disp_arr[0]=0;
	$disp_pre[0]=0;
     
	$offset_gmt=mrr_gmt_offset_val(); 		//mrr_get_server_time_offset();
	$offset_gmt=$offset_gmt * -1;
	
	$disp_reports="";
	
	//see if there are multiple trucks to check..................................................................................................................................................     	
	$mres=mrr_find_peoplenet_trucks_on_load($find_load_id);
	$cntr=$mres['num'];
	$arr=$mres['arr'];
	$names=$mres['names'];
	//$pn=$mres['pn'];
	$geotab=$mres['geotab'];
	
	$multi_truck="";
	if($cntr>1)
	{
		$multi_truck.="<table class='admin_menu3' border='0' cellpadding='0' cellspacing='0' width='1600'>";
		$multi_truck.="<tr>";
		$multi_truck.=		"<td valign='top'>Multiple Trucks on Load ".$find_load_id."<td>";
		
		for($x=0; $x < $cntr;$x++)
		{
			$geotab_notice="";		if($geotab[ $x ] > 0)	$pn_notice=" GeoTab Active";
			
			$multi_truck.=		"<td valign='top'>Truck ".$names[ $x ]."".$geotab_notice."<td>";	//<a href='peoplenet_interface.php?find_load_id=".$find_load_id."&find_truck_id=".$arr[ $x ]."'></a>
		}
					
		$multi_truck.="</tr>";
		$multi_truck.="</table>";			
		$multi_truck.="<br>";
	}     	
	//...........................................................................................................................................................................................
	
	$geotab_device_id="";
	$truckname="1520428";
	if($find_truck_id==0)
	{
		$find_truck_id=1520428;
		$sql = "
     		select truck_id,name_truck,geotab_device_id
     		from trucks_log,trucks 
     		where load_handler_id='".sql_friendly($find_load_id)."'
     			and trucks_log.truck_id=trucks.id
     		limit 1
     	";
     	$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$truckname="".trim($row['name_truck'])."";
			$geotab_device_id="".trim($row['geotab_device_id'])."";
			$find_truck_id=$row['truck_id'];	
		}
	}
	else
	{
		$sql = "
     		select name_truck,geotab_device_id 
     		from trucks 
     		where id='".sql_friendly($find_truck_id)."'
     	";
     	$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$truckname="".trim($row['name_truck'])."";
			$geotab_device_id="".trim($row['geotab_device_id'])."";
		}	
	}
	
	//settings.....................................................................................
	$voided[0]=0;			//place holder for general sevice functions.... skipped for dispatch create, remove and status checks.
		
	$truckname=trim(str_replace(" (Team Rate)","",$truckname));
	
	$xcntr=0;
	
	//<input type='button' value='Check Status' onClick='mrr_get_dispatch();'>
	
	//".($test_mode > 0 ? "TEST MODE" : "")."
	
	$output="".$multi_truck."<table class='admin_menu3' border='0' cellpadding='0' cellspacing='0' width='1600'>
			<tr>
				<td valign='top' colspan='20'>
					<table border='0' cellpadding='0' cellspacing='0' width='100%'>
					<tr>
						<td valign='top'><span class='section_heading'>GeoTab Dispatch(es): </span></td>
						
						<td valign='top'>Truck ID: (".$find_truck_id.") <input type='hidden' id='find_truck_id' name='find_truck_id' value='".$find_truck_id."'></td>
						<td valign='top'>Truck Name: ".$truckname." <input type='hidden' id='find_truck_name' name='find_truck_name' value='".$truckname."'></td>
						<td valign='top'>Load ID: ".$find_load_id." <input type='hidden' id='find_load_id' name='find_load_id' value='".$find_load_id."'></td>
						<td valign='top'>Process Dispatch ID: ".$run_dispatch." <input type='hidden' id='run_dispatch' name='run_dispatch' value='".$run_dispatch."'></td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td valign='top'><b>DispID</b></td>
				<td valign='top'><b>StopID</b></td>
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>Trailer</b></td>
				<td valign='top'><b>StopType</b></td>
				<td valign='top'><b>Customer</b></td>
				<td valign='top'><b>StopPhone</b></td>
				
				<td valign='top'><b>Shipper</b></td>
				<td valign='top'><b>Address1</b></td>
				<td valign='top'><b>Address2</b></td>
				<td valign='top'><b>City</b></td>
				<td valign='top'><b>State</b></td>
				<td valign='top'><b>Zip</b></td>			
				
				<td valign='top'><b>PickupETA</b></td>
				
				<td valign='top'><b>Longitude</b></td>
				<td valign='top'><b>Latitude</b></td>
								
				<td valign='top'><b>GeoTabID</b></td>
				<td valign='top'><b>GeoStopID</b></td>
				<td valign='top'><b>GeoZoneID</b></td>
				<td valign='top'><b>GeoMsgID</b></td>
				<td valign='top'><b>Stops</b></td>
			</tr>
		";
	if($find_load_id > 0)
	{     		
		$stops=0;
		$disp_stops=0;
		$stops_xml="";
		$first_stop_date=date("m/d/Y")." 00:00";
		$last_disp=0;
		
		$load_str=mrr_find_quick_load_string($find_load_id);
		$load_str2=mrr_find_quick_load_string_alt($find_load_id);
		$load_str3=mrr_find_quick_load_string_special($find_load_id);
		
		$route_info[0]["id"]="";	
          $route_info[0]["from"]="";
          $route_info[0]["to"]="";	
          $route_info[0]["date"]="";
		
		$dnamer="";
		$dnotes="";
				
		$mrr_goetab_zoned_stops=0;
		$mrr_stop_cntr=0;
		$mrr_stop_list[0]=0;
		
		$stop_string="";
		
		$sql="
			select load_handler_stops.*,
				(select commodity from load_handler where load_handler.id=load_handler_stops.load_handler_id) as pu_num,
				(select load_number from load_handler where load_handler.id=load_handler_stops.load_handler_id) as bol_num,
				 trucks_log.driver_id,
				 trucks_log.truck_id,
				 trucks_log.trailer_id,
				 trucks_log.dropped_trailer,
				 trucks_log.miles,
				 trucks_log.miles_deadhead,
				 trucks_log.origin,
				 trucks_log.destination,
				 trucks_log.customer_id,
				 trucks_log.geotab_disp_id,
				 trucks_log.geotab_msg_id,
				 customers.name_company
			from load_handler_stops,
				trucks_log,
				customers
			where trucks_log.id=load_handler_stops.trucks_log_id
				and customers.id=trucks_log.customer_id
				and load_handler_stops.deleted<=0
				and trucks_log.deleted<=0
				and customers.deleted<=0
				and trucks_log.truck_id='".sql_friendly($find_truck_id)."'
				and load_handler_stops.load_handler_id='".sql_friendly($find_load_id)."'
				".($run_dispatch > 0 ? "and trucks_log.id='".sql_friendly($run_dispatch)."'" : "")."
				".($test_mode==0 ? "and (load_handler_stops.linedate_completed IS NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')" : "")."
				
			order by trucks_log_id asc,load_handler_stops.linedate_pickup_eta asc, load_handler_stops.id asc		
		";	//
		$data=simple_query($sql);
		$mn=mysqli_num_rows($data);
		
		$prev_msg_text="";
          $terminal_loc="Conard Terminal";
          $terminal_link="Conard Terminal|200 International Blvd|La Vergne|37086|TN|TN|36.01502991|-86.59250640869140";
          $prev_loc="";
          $prev_link="";
          
		while($row=mysqli_fetch_array($data))
		{				
			$bol_comp=trim($row['name_company']);
			$bol_number=trim($row['bol_num']);
			$commodity=trim($row['pu_num']);
						
			//appointment window...  assumes the "real" appointment time is the end of the appt window... but the trip can start based on the beginning of the window...
			$att_window_label="";
			$att_window_starter=$row['linedate_pickup_eta'];
			$att_window_ender=$row['linedate_pickup_eta'];
					
			$appt_window=$row['appointment_window'];
			if($appt_window > 0)
			{				
				$appt_window_start="";
				$appt_window_start_time="";
				$appt_window_end="";
				$appt_window_end_time="";
                    				
				if(strtotime($row['linedate_appt_window_start']) > 0)
				{
					$appt_window_start=date("M d, Y", strtotime($row['linedate_appt_window_start']));
					$appt_window_start_time=time_prep($row['linedate_appt_window_start']);
				}
				if(strtotime($row['linedate_appt_window_end']) > 0)
				{
					$appt_window_end=date("M d, Y", strtotime($row['linedate_appt_window_end']));
					$appt_window_end_time=time_prep($row['linedate_appt_window_end']);
				}
				
				//$ideal_time=date("M d, Y", strtotime($row['linedate_pickup_eta']))." ".time_prep($row['linedate_pickup_eta']);
				
				$att_window_starter=$row['linedate_appt_window_start'];
				$att_window_ender=$row['linedate_appt_window_end'];
				
				$att_window_label="ApptWindow:".$appt_window_start." ".$appt_window_start_time."-".$appt_window_end_time.". ";		//".$appt_window_end."    ...window should be in the same date...truncated out for display length.
                    
                    
                    //new version of code...04-09-2021...MRR
                    $att_window_starter_x = date("m/d H:i",strtotime($row['linedate_appt_window_start']));
                    $att_window_ender_x = date("m/d H:i",strtotime($row['linedate_appt_window_end']));
                    
                    $att_window_label="WINDOW: ".$att_window_starter_x." to ".$att_window_ender_x.".";
			}			
			//.....................
						
			
			if($last_disp==0)	$last_disp=$row['trucks_log_id'];
			
			$new_offset_gmt=$row['timezone_offset'] + $row['timezone_offset_dst'];
			$new_offset_gmt=abs($new_offset_gmt/3600);		//convert to hours from local stop timezone
			
			if($new_offset_gmt==0)		$new_offset_gmt=abs($offset_gmt);
			
			$stamp0=date("m/d/Y H:i",strtotime("".($new_offset_gmt-1)." hours",strtotime($att_window_starter)));	     //trip start time
			$stamp =date("m/d/Y H:i",strtotime("".($new_offset_gmt  )." hours",strtotime($att_window_ender)));		//arriving time     			
			$stamp2=date("m/d/Y H:i",strtotime("".($new_offset_gmt )." hours",strtotime($att_window_ender)));		//arrived time
			$stamp3=date("m/d/Y H:i",strtotime("".($new_offset_gmt+1)." hours",strtotime($att_window_ender)));		//departed time
			     			
			$cust=mrr_get_all_customer_settings($row['customer_id']);	
			
			$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
			$hl_timer=$cust['hot_load_timer'];					//interval between messages
			
			$hl_geo_active=$cust['geofencing_radius_active'];		//turn on actual geofencing notices
			$hl_geo_on_all=$cust['geofencing_hot_msg_all_loads'];	//all loads set to on...
			     			     			
			$trailer_name=mrr_find_quick_trailer_name($row['trailer_id']);
			$t_drop="";
			if($row['dropped_trailer'] > 0)	$t_drop="-Drop";
			
			
						
			if($row['trucks_log_id']!=$last_disp)
			{
          		$geotab_disp_id=$row['geotab_disp_id'];	
          		$saved_stops=0;						
          		               		
          		$disp_status="";
				
				$disp_status2=$disp_status;
				/*    				
				$output.="
					<tr>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='18'>".$disp_status2."</td>
						<td valign='top' align='right'><input type='button' value='Dispatch ".$last_disp."' onClick='mrr_run_dispatch(".$last_disp.");'></td>
					</tr>
				";	
				*/
				$disp_arr[$disp_cntr]=$last_disp;
				$disp_pre[$disp_cntr]=0;
				$disp_cntr++;
				
                    //add new dispatch  
          		$dispatch_message="".$load_str."";		//".($test_mode > 0 ? "TEST " : "")." 
          		$dispatch_message2="".$load_str2."";		//".($test_mode > 0 ? "TEST " : "")." 
          		$first_stop_date=$stamp0;
          		$page="";	         		
                    
                    for($z=0; $z<=$disp_stops; $z++)
                    {
                    	$route_info[$z]["id"]="";	
          			$route_info[$z]["from"]="";
          			$route_info[$z]["to"]="";	
          			$route_info[$z]["date"]="";	
                    }
                    
                    //reset counter
				$disp_stops=0;	
				$stops_xml="";
				
				$last_disp=$row['trucks_log_id'];
			}
						
			if($disp_stops==0)	$first_stop_date=$stamp;	
						
			$stops++;
			$disp_stops++;
			
			//stop info.   ".($test_mode > 0 ? "TEST " : "")."
			$label="Stop ".$disp_stops." - ".$row['id'].":  ".date("Y-m-d H:i",strtotime($row['linedate_pickup_eta']))." Trailer ".$trailer_name."".$t_drop.": ".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')."";
			
			$namer=trim($row['shipper_name']);
			$namer=str_replace("&"," and ",$namer);
			$namer=str_replace("'","",$namer);
			$namer=str_replace("#","",$namer);
			
			$bol_comp=$namer;
			
			$info="".trim($row['name_company'])."";
			$notes="Phone ".trim($row['stop_phone'])."";
									
			$address=trim($row['shipper_address1']);
			if(trim($row['shipper_address2'])!="")            $address.=", ".trim($row['shipper_address2'])."";
			$city=trim($row['shipper_city']);
			$state=trim($row['shipper_state']);
			$zip=trim($row['shipper_zip']);
			
			
			$stop_string.="-".$label.": ".$att_window_label." ".$namer." - ".$address."; ".$city.", ".$state." ".$zip.". ".$notes.".";
			$stop_string=str_replace(chr(9)," ",$stop_string);
     		$stop_string=str_replace(chr(10)," ",$stop_string);
     		$stop_string=str_replace(chr(13)," ",$stop_string);
			
						
			$stop_long=trim($row['longitude']);
			$stop_lat=trim($row['latitude']);
			
			$geotab_stop_id=trim($row['geotab_stop_id']);
			$geotab_zone_id=$row['geotab_zone_id'];		
			$geotab_stop_msg_id=trim($row['geotab_stop_msg_id']);
						
			if($stop_lat=="" || $stop_lat=="0" || $stop_long=="" || $stop_long=="0")
			{	//no GPS point, so find one.
				$res=mrr_geotab_get_coordinate_from_addr($address,$city,$state,$zip);
				$stop_lat=trim($res['lat']);
				$stop_long=trim($res['long']);
			}
			
			if(floatval($stop_long)!=0 && floatval($stop_long)!=0)
			{				
				$sres=mrr_find_geotab_stop_zones_by_gps($stop_long,$stop_lat);
				$geotab_zone_id=$sres['id'];
     			if($geotab_zone_id > 0 && trim($sres['geotab_id_name'])=="")
     			{	//Zone found by GPS point, but blank... try again by address.
     				$sres=mrr_find_geotab_stop_zones_by_addr($address,$city,$state,$zip);   
     				$geotab_zone_id=$sres['id']; 				
     				//$geotab_stop_id=trim($sres['geotab_id_name']);  				
     			}
     			
     			
     			if($geotab_zone_id > 0)
     			{
     				$geotab_stop_id=mrr_find_geotab_stop_zones_by_id($geotab_zone_id);
     				
     				if($geotab_stop_id=="")
     				{
     					$geotab_stop_id=mrr_get_geotab_zones("",trim($namer),1);
     					if($geotab_stop_id=="")
     					{
     						$gres=mrr_gps_point_box_creator($stop_long,$stop_lat,$x_off,$y_off);
          					$res['long_zone_w']="".$gres['pt0_long_w']."";
               				$res['long_zone_e']="".$gres['pt1_long_e']."";
               				$res['lat_zone_n']="".$gres['pt0_lat_n']."";
               				$res['lat_zone_s']="".$gres['pt1_lat_s']."";
          					
          					$geotab_stop_id=mrr_make_geotab_zone($stop_long,$stop_lat,trim($namer),"","",1,trim($gres['pt0_long_w']),trim($gres['pt1_long_e']),trim($gres['pt0_lat_n']),trim($gres['pt1_lat_s']));
          					          					
          					$res['geotab_id_name']=trim($geotab_stop_id);
          					$res['conard_name']=trim($namer);          					     									
          					
          					$geotab_zone_id=mrr_create_geotab_stop_zones($res);	
     					}
     									
     					$res['geotab_id_name']=trim($geotab_stop_id);
     					$res['conard_name']=trim($namer);
     					$res['id']=$geotab_zone_id;
     					$res['long']=$stop_long;
          				$res['lat']=$stop_lat;	
          				
          				$res['address_1']=trim($address);
          				$res['city']=trim($city);
          				$res['state']=trim($state);
          				$res['zip']=trim($zip);   
     					
     					mrr_update_geotab_stop_zones_gps_shipper_by_id($res);
     					mrr_update_geotab_stop_zones_points_name_by_id($res);
     				}
     				else
     				{
     					mrr_set_geotab_zones_displayed($geotab_stop_id,1);	
     				}
     			}
     			else
     			{	//not already saved, so see if the zone exists.
     				$res=mrr_find_geotab_stop_zones_by_addr(trim($address),trim($city),trim($state),trim($zip));
     				$geotab_zone_id=$res['id'];
     				
     				if($geotab_zone_id > 0)
     				{
     					$geotab_stop_id=trim($res['geotab_id_name']);
     										
     					if($geotab_stop_id=="")
     					{          					
     						$gres=mrr_gps_point_box_creator($stop_long,$stop_lat,$x_off,$y_off);
          					$res['long_zone_w']="".$gres['pt0_long_w']."";
               				$res['long_zone_e']="".$gres['pt1_long_e']."";
               				$res['lat_zone_n']="".$gres['pt0_lat_n']."";
               				$res['lat_zone_s']="".$gres['pt1_lat_s']."";
          					
          					$geotab_stop_id=mrr_make_geotab_zone($stop_long,$stop_lat,trim($namer),"","",1,trim($gres['pt0_long_w']),trim($gres['pt1_long_e']),trim($gres['pt0_lat_n']),trim($gres['pt1_lat_s']));
          					          					
          					$res['geotab_id_name']=trim($geotab_stop_id);
          					$res['conard_name']=trim($namer);      					
          					          					
          					$geotab_zone_id=mrr_create_geotab_stop_zones($res);	
     					}	
     					else
     					{
     						mrr_set_geotab_zones_displayed($geotab_stop_id,1);	
     					}	
     														
     					$res['geotab_id_name']=trim($geotab_stop_id);
     					$res['conard_name']=trim($namer);
     					$res['id']=$geotab_zone_id;
     					$res['long']=$stop_long;
          				$res['lat']=$stop_lat;		
     					
     					$res['address_1']=trim($address);
          				$res['city']=trim($city);
          				$res['state']=trim($state);
          				$res['zip']=trim($zip);   
          				
     					mrr_update_geotab_stop_zones_gps_shipper_by_id($res);
     					mrr_update_geotab_stop_zones_points_name_by_id($res);			
     				}
     				else
     				{	//not stored, so create on GeoTab side.
     					$gres=mrr_gps_point_box_creator($stop_long,$stop_lat,$x_off,$y_off);
     					$res['long_zone_w']="".$gres['pt0_long_w']."";
          				$res['long_zone_e']="".$gres['pt1_long_e']."";
          				$res['lat_zone_n']="".$gres['pt0_lat_n']."";
          				$res['lat_zone_s']="".$gres['pt1_lat_s']."";
     					
     					$geotab_stop_id=mrr_make_geotab_zone($stop_long,$stop_lat,trim($namer),"","",1,trim($gres['pt0_long_w']),trim($gres['pt1_long_e']),trim($gres['pt0_lat_n']),trim($gres['pt1_lat_s']));
     					     					
     					$res['geotab_id_name']=trim($geotab_stop_id);
     					$res['conard_name']=trim($namer);
     					$res['long']=$stop_long;
     					$res['lat']=$stop_lat; 
     					
     					$res['address_1']=trim($address);
          				$res['city']=trim($city);
          				$res['state']=trim($state);
          				$res['zip']=trim($zip);       					    										
     					
     					$geotab_zone_id=mrr_create_geotab_stop_zones($res);					
     				}
     			}       			
			}
			else
			{	//NO GPS was created...so SKIP it.
     			
			}	
					
			$driver_id=$row['driver_id'];
			$geotab_disp_id=trim($row['geotab_disp_id']);	
			$geotab_msg_id=trim($row['geotab_msg_id']);
			$geotab_shipment_log_id=trim($row['geotab_shipment_log_id']);
			//$geotab_stop_id=trim($row['geotab_stop_id']);
          	$saved_stops=0;
          	
          	//if dispatch is already there, remove it first...          		
          	if(trim($geotab_disp_id)!="")
          	{
          		$killed=mrr_kill_geotab_route("Route",trim($geotab_disp_id));          			
          	}
          	
          	if(trim($geotab_stop_id)!="")
          	{
          		$mrr_goetab_zoned_stops++;
          		
          		$appt_start="".date("Y-m-d")." 00:00";								//these dates are always for the message delivery to the device...not the appointment slot.
          		$appt_ended="".date("Y-m-d",strtotime("+7 days",time()))." 23:59";		//these dates are always for the message delivery to the device...not the appointment slot.
          		
          		//$appt_start="".date("Y-m-d H:i",strtotime("+".abs(mrr_gmt_offset_val())." hours",strtotime($row['linedate_pickup_eta'])))."";
          		//$appt_ended="".date("Y-m-d H:i",strtotime("+".(abs(mrr_gmt_offset_val())+48)." hours",strtotime($row['linedate_pickup_eta'])))."";
          		if($row['appointment_window'] > 0)
          		{
          			//$appt_start="".date("Y-m-d H:i",strtotime("+".abs(mrr_gmt_offset_val())." hours",strtotime($row['linedate_appt_window_start'])))."";
          			//$appt_ended="".date("Y-m-d H:i",strtotime("+".abs(mrr_gmt_offset_val())." hours",strtotime($row['linedate_appt_window_end'])))."";	
          		} 
          		$appt_start=str_replace(" ","T",$appt_start);  
          		$appt_ended=str_replace(" ","T",$appt_ended);       		
          		
          		$route_info[($disp_stops -1)]["id"]=trim($geotab_stop_id);	
          		$route_info[($disp_stops -1)]["from"]="".$appt_start.":00.000Z";
          		$route_info[($disp_stops -1)]["to"]  ="".$appt_ended.":00.000Z";	
          		$route_info[($disp_stops -1)]["date"]="".date("Y-m-d",strtotime("+".abs(mrr_gmt_offset_val())." hours",strtotime($row['linedate_pickup_eta'])))."";   
          		
          		if($disp_stops > 1)		$dnotes.=", ";
          		$dnotes.="".$label."";
          		
          		
          		if(trim($geotab_stop_msg_id)!="")
          		{
          			//$killed=mrr_kill_geotab_route("TextMessage",trim($geotab_stop_msg_id)); 
          		}
          		$test_msg="".(trim($geotab_stop_msg_id)!="" ? "UPDATE " : "")."".$label.": ".$att_window_label." ".$namer." - ".$address."; ".$city.", ".$state." ".$zip.". ".$notes.". Dispatch ".$row['trucks_log_id']." (Load ".$row['load_handler_id']."): ".$load_str3."";
                              
          		$test_msg=str_replace(chr(9),"",$test_msg);
     			$test_msg=str_replace(chr(10),"",$test_msg);
     			$test_msg=str_replace(chr(13),"",$test_msg);
          
                    if(trim($prev_msg_text)!="")            $test_msg.=" ... ... ... ".$prev_msg_text;
          
                    //$terminal_loc="Conard Terminal";
                    //$terminal_link="Conard Terminal|200 International Blvd|La Vergne|37086|TN|TN|36.01502991|-86.59250640869140";
          
                    ///address should be separated by commas... and URL encoded with "%2C"  
          
                    if($namer!=$terminal_loc)
                    {
                         $copilot_msg = "" . (trim($prev_loc) != "" ? $prev_loc : $terminal_loc) . " To " . $namer . " " . $address . "; " . $city . ", " . $state . " " . $zip . "";
                         $copilot_link = "copilot://options?type=STOPS&stop=" . (trim($prev_link) != "" ? $prev_link : $terminal_link) . "&Stop=" . $namer . "|" . $address . "|" . $city . "|" . $zip . "|" . $state . "|" . $state . "|" . $stop_lat . "|" . $stop_long . "";
     
                         //CoPilot     
                         mrr_add_geotab_url_log("GpsTextMessageSend", "Date: ".date("Y-m-d H:i:s",time())." | URL: ".$copilot_link);
                              
                         $copilot_addr = "" . $namer . ", " . $address . ", " . $city . ", " . $state . ", " . $zip . "";
                         $copilot_msg = "" . $namer . ", " . $address . ", " . $city . ", " . $state . ", " . $zip . "";  //" . $namer . "             " . $namer . "|    |" . $stop_lat . "|" . $stop_long . "
                         $copilot_link = "https://www.google.com/maps/place/" . $address . ", " . $city . ", " . $state . ", " . $zip . "";
                    }
                    else
                    {
                         $copilot_msg = " " . $namer . " " . $address . "; " . $city . ", " . $state . " " . $zip . "";
                         $copilot_link = "copilot://options?type=STOPS&stop=" . $namer . "|" . $address . "|" . $city . "|" . $zip . "|" . $state . "|" . $state . "|" . $stop_lat . "|" . $stop_long . "";
     
                         //CoPilot     
                         mrr_add_geotab_url_log("GpsTextMessageSend", "Date: ".date("Y-m-d H:i:s",time())." | URL: ".$copilot_link);
                              
                         $copilot_addr = "" . $namer . ", " . $address . ", " . $city . ", " . $state . ", " . $zip . "";
                         $copilot_msg = "" . $namer . ", " . $address . ", " . $city . ", " . $state . ", " . $zip . "";      // " . $namer . "      " . $namer . "|  |" . $stop_lat . "|" . $stop_long . "
                         $copilot_link = "https://www.google.com/maps/place/" . $address . ", " . $city . ", " . $state . ", " . $zip . "";
                    }
                    
                    
                    $mrr_copilot_switcher=1;                    
                    if($mrr_copilot_switcher == 0)
                    {
                         $geotab_stop_msg_id=mrr_send_geotab_text_message_dispatch($find_truck_id,$test_msg,0,0,0,$geotab_stop_id,$stop_long,$stop_lat,$driver_id,$row['id']);
                    }
                    else
                    {
                         $geotab_stop_msg_id=mrr_send_geotab_text_message_dispatch($find_truck_id,$test_msg,0,0,0,$copilot_addr,$stop_long,$stop_lat,$driver_id,$row['id']);
                    }
                    
                    /*
          		$geotab_stop_msg_id=mrr_send_geotab_text_message_dispatch($find_truck_id,$test_msg,0,0,0,$geotab_stop_id,$stop_long,$stop_lat,$driver_id);
                    
          		$test_copilot=mrr_send_geotab_text_message_dispatch_copilot($find_truck_id,$copilot_msg,$copilot_link,0,0,$stop_long,$stop_lat,$driver_id,$copilot_addr);
                              //  mrr_send_geotab_text_message_dispatch_copilot($truck_id,$message,$copilot,$from=0,$sess_user_id=0,$long=0,$lat=0,$driver_id=0,$addr="")
                    */
                    
                    //$prev_msg_text=$test_msg;
                    $prev_msg_text="";            //removes the previous stop from this message...so that all X stops aren't sent on last message.  Should now only send one stop per message.  Added 11/30/2020 ..MRR.
                    $prev_loc="".$namer." ".$address."; ".$city.", ".$state." ".$zip."";
                    $prev_link="".$namer."|".$address."|".$city."|".$zip."|".$state."|".$state."|".$stop_lat."|".$stop_long."";
                    
          		$geotab_msg_id=trim($geotab_stop_msg_id);
          		
          		if($row['stop_type_id']==1)
          		{	//shipper...no need for ShipmentLog
          			//$geotab_shipment_log_id="";
          		}
          		else
          		{         			
          			$geotab_shipment_log_id=mrr_send_geotab_shipment_log($find_truck_id,$driver_id,$bol_comp,$bol_number,$commodity,$geotab_shipment_log_id);
          		}
          		          		
          		$sqlu="
                    	update load_handler_stops set
                    		geotab_shipment_log_id='".sql_friendly(trim($geotab_shipment_log_id))."',
                    		geotab_stop_msg_id='".sql_friendly(trim($geotab_stop_msg_id))."',
                    		geotab_stop_id='".sql_friendly(trim($geotab_stop_id))."',
                    		geotab_zone_id='".sql_friendly(trim($geotab_zone_id))."'
                    	where id='".sql_friendly($row['id'])."'
                    ";
                    simple_query($sqlu);       		
          	}
          	
			$output.="
				<tr>
					<td valign='top'>".$row['trucks_log_id']."</td>
					<td valign='top'>".$row['id']."</td>
					<td valign='top'>".$truckname."</td>
					<td valign='top'>".$trailer_name."".$t_drop."</td>
					
					<td valign='top'>".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')."</td>
					<td valign='top'>".$row['name_company']."</td>
					<td valign='top'>".$row['stop_phone']."</td>
					
					<td valign='top'>".$namer."</td>
					<td valign='top'>".$row['shipper_address1']."</td>
					<td valign='top'>".$row['shipper_address2']."</td>
					<td valign='top'>".$row['shipper_city']."</td>
					<td valign='top'>".$row['shipper_state']."</td>
					<td valign='top'>".$row['shipper_zip']."</td>
										
					<td valign='top'>".$att_window_starter."</td>
					
					<td valign='top'>".$stop_long."</td>
					<td valign='top'>".$stop_lat."</td>
					
					<td valign='top'>".$geotab_disp_id."</td>
					<td valign='top'>".$geotab_stop_id."</td>
					<td valign='top'>".$geotab_zone_id."</td>
					<td valign='top'>".$geotab_stop_msg_id."</td>
					<td valign='top'>".$saved_stops."</td>
				</tr>
			";			
			$xcntr++;
			
			if($hl_geo_on_all > 0)
			{
				//
			}
			
			$mrr_stop_list[$mrr_stop_cntr]=$row['id'];
			$mrr_stop_cntr++;			
			
			//if all stops accounted for (with Zones) and only one dispatch selected to send to GeoTab, package and send it out.
			if($disp_stops==$mrr_goetab_zoned_stops && $run_dispatch > 0)
			{	// && $disp_stops==$mn
				$dnamer=$load_str3;
				//$dnotes="";		
				
				$output.="
					<tr>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='18'>
							Dispatch Name: ".$dnamer."
							<br>
							Dispatch Notes: ".$dnotes."
						</td>
						<td valign='top' align='right'></td>
					</tr>
				";		
				
				$geotab_msg_id=0;
				/*
				$test_msg="".(trim($geotab_msg_id)!="" ? "UPDATE " : "")." Dispatch ".$row['trucks_log_id']." (Load ".$row['load_handler_id']."): ".$stop_string." Info: ".$load_str3." Notes: ".$dnotes."";
				$msg_urgent=1;
				$geotab_msg_id=mrr_send_geotab_text_message($find_truck_id,$test_msg,$msg_urgent);
				if(trim($geotab_msg_id)!="" && trim($geotab_msg_id)!="0")
				{
					$sqluv="
                    		update trucks_log set
                    			geotab_msg_id='".sql_friendly(trim($geotab_msg_id))."'
                    		where id='".sql_friendly($row['trucks_log_id'])."'
                    	";
                    	simple_query($sqluv);  	
				}	
				*/
				
				
				$geotab_disp_id=mrr_make_geotab_route($find_truck_id,$route_info,$dnamer,$dnotes,1,0);
				if(trim($geotab_disp_id)!="" && trim($geotab_disp_id)!="0")
				{
										
					$sqluv="
                    		update trucks_log set
                    			geotab_msg_id='".sql_friendly(trim($geotab_msg_id))."',
                    			geotab_disp_id='".sql_friendly(trim($geotab_disp_id))."'
                    		where id='".sql_friendly($row['trucks_log_id'])."'
                    	";
                    	simple_query($sqluv);  
				}
			}			
		}	//end main while loop
	}	//end if load_id>0 check
	
	$friendly_message="";
	if($xcntr==0)		$friendly_message="<div class='alert' align='center'><b>There are no current dispatch stops for this load and truck.  If multiple trucks are on this load, please click the truck above in the Multiple Truck section.</b></div>";
	    	
	$output.="
			<tr>
				<td valign='top' colspan='20'><br><br>".$disp_reports."".$friendly_message."</td>
			</tr>
		</table>
		<br>";
	     	
	$res['truck_id']=$find_truck_id;
	$res['truck_name']=$truckname;
	$res['dispatch_id']=$run_dispatch;
	$res['dispatch_cntr']=$disp_cntr;
	$res['disp_arr']=$disp_arr;
	$res['disp_pre']=$disp_pre;
	$res['output']=$output;
	$res['geotab_id']=$geotab_disp_id;
	
	return $res;	
}

function mrr_send_geotab_complete_preplan_load($find_load_id,$find_truck_id,$run_preplan,$find_driver_id=0,$test_mode=0)
{
	global $defaultsarray;
	
	$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
	$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;
	
	$geotab_load_id="";
	$geotab_disp_id="";
	$geotab_stop_id="";
	$geotab_zone_id="";
	
     $disp_cntr=0;
	$disp_arr[0]=0;
	$disp_pre[0]=0;
	
	$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
	$offset_gmt=$offset_gmt * -1;
	
	$disp_reports="";
	
	$truckname="1520428";
	$geotab_device_id="";
	
	$sql = "
     	select name_truck,geotab_device_id 
     	from trucks 
     	where id='".sql_friendly($find_truck_id)."'
     ";
     $data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$truckname="".trim($row['name_truck'])."";
		$geotab_device_id="".trim($row['geotab_device_id'])."";
	}
	
	//settings.....................................................................................
	$voided[0]=0;			//place holder for general sevice functions.... skipped for dispatch create, remove and status checks.
	
	$label="";
	
	$truckname=trim(str_replace(" (Team Rate)","",$truckname));
	
	//".($test_mode > 0 ? "TEST MODE" : "")."
	
	$output="<table class='admin_menu3' border='0' cellpadding='0' cellspacing='0' width='1600'>
			<tr>
				<td valign='top' colspan='20'>
					<table border='0' cellpadding='0' cellspacing='0' width='100%'>
					<tr>
						<td valign='top'><span class='section_heading'>GeoTab Dispatch(es): Preplan Loads </span></td>
						
						<td valign='top'>Truck ID: (".$find_truck_id.") <input type='hidden' id='find_truck_id' name='find_truck_id' value='".$find_truck_id."'></td>
						<td valign='top'>Truck Name: ".$truckname." <input type='hidden' id='find_truck_name' name='find_truck_name' value='".$truckname."'></td>
						<td valign='top'>Load ID: ".$find_load_id." <input type='hidden' id='find_load_id' name='find_load_id' value='".$find_load_id."'></td>
						<td valign='top'>Process Preplan ID: ".$run_preplan." <input type='hidden' id='run_preplan' name='run_preplan' value='".$run_preplan."'></td>
						
						<td valign='top' align='right'>
							
							<input type='hidden' id='find_preplan' name='find_preplan' value='1'>
							<input type='hidden' id='find_driver_id' name='find_driver_id' value='".$find_driver_id."'>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td valign='top'><b>&nbsp;</b></td>
				<td valign='top'><b>StopID</b></td>
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>&nbsp;</b></td>
				<td valign='top'><b>StopType</b></td>
				<td valign='top'><b>Customer</b></td>
				<td valign='top'><b>StopPhone</b></td>
				
				<td valign='top'><b>Shipper</b></td>
				<td valign='top'><b>Address1</b></td>
				<td valign='top'><b>Address2</b></td>
				<td valign='top'><b>City</b></td>
				<td valign='top'><b>State</b></td>
				<td valign='top'><b>Zip</b></td>			
				
				<td valign='top'><b>PickupETA</b></td>
				
				<td valign='top'><b>Longitude</b></td>
				<td valign='top'><b>Latitude</b></td>
				
				<td valign='top'><b>GeoTabID</b></td>
				<td valign='top'><b>GeoStopID</b></td>
				<td valign='top'><b>GeoZoneID</b></td>
				<td valign='top'><b>Stops</b></td>
			</tr>
		";
	if($find_load_id > 0)
	{     		
		$stops=0;
		$disp_stops=0;
		$stops_xml="";
		$first_stop_date=date("m/d/Y")." 00:00";
		$last_disp=$find_load_id;	//$run_preplan
		
		$load_str=mrr_find_quick_load_string($find_load_id);
		$load_str2=mrr_find_quick_load_string_alt($find_load_id);
		$load_str3=mrr_find_quick_load_string_special($find_load_id);
		
		$route_info[0]["id"]="";	
          $route_info[0]["from"]="";
          $route_info[0]["to"]="";	
          $route_info[0]["date"]="";
		
		$dnamer="";
		$dnotes="";
				
		$mrr_goetab_zoned_stops=0;     		
		$mrr_stop_cntr=0;
		$mrr_stop_list[0]=0;
		
		$stop_string="";
		
		$sql="
			select load_handler_stops.*,
     			(select pickup_number from load_handler where load_handler.id=load_handler_stops.load_handler_id) as pu_num,
				 load_handler.origin_city,
				 load_handler.origin_state,
				 load_handler.dest_city,
				 load_handler.dest_state,
				 load_handler.estimated_miles,
				 load_handler.deadhead_miles,
				 load_handler.customer_id,
				 load_handler.geotab_load_id,
				 load_handler.geotab_load_msg_id,
				 customers.name_company
			from load_handler,
				load_handler_stops,
				customers
			where load_handler.id=load_handler_stops.load_handler_id
				and customers.id=load_handler.customer_id
				and load_handler_stops.deleted<=0
				and load_handler.deleted<=0
				and customers.deleted<=0
				and load_handler_stops.load_handler_id='".sql_friendly($find_load_id)."'				
				".($test_mode==0 ? "and (load_handler_stops.linedate_completed IS NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')" : "")."
			order by load_handler_stops.linedate_pickup_eta asc, load_handler_stops.id asc		
		";	//
		$data=simple_query($sql);
		$mn=mysqli_num_rows($data);
		while($row=mysqli_fetch_array($data))
		{				
			//appointment window...  assumes the "real" appointment time is the end of the appt window... but the trip can start based on the beginning of the window...
			$att_window_label="";
			$att_window_starter=$row['linedate_pickup_eta'];
			$att_window_ender=$row['linedate_pickup_eta'];
					
			$appt_window=$row['appointment_window'];
			if($appt_window > 0)
			{				
				$appt_window_start="";
				$appt_window_start_time="";
				$appt_window_end="";
				$appt_window_end_time="";
				
				if(strtotime($row['linedate_appt_window_start']) > 0)
				{
					$appt_window_start=date("M d, Y", strtotime($row['linedate_appt_window_start']));
					$appt_window_start_time=time_prep($row['linedate_appt_window_start']);
				}
				if(strtotime($row['linedate_appt_window_end']) > 0)
				{
					$appt_window_end=date("M d, Y", strtotime($row['linedate_appt_window_end']));
					$appt_window_end_time=time_prep($row['linedate_appt_window_end']);
				}
				
				//$ideal_time=date("M d, Y", strtotime($row['linedate_pickup_eta']))." ".time_prep($row['linedate_pickup_eta']);
				
				$att_window_starter=$row['linedate_appt_window_start'];
				$att_window_ender=$row['linedate_appt_window_end'];
				
				$att_window_label="ApptWindow:".$appt_window_start." ".$appt_window_start_time."-".$appt_window_end_time.". ";		//".$appt_window_end."    ...window should be in the same date...truncated out for display length.
			}			
			//.....................
			     			     			
			
			$new_offset_gmt=$row['timezone_offset'] + $row['timezone_offset_dst'];
			$new_offset_gmt=abs($new_offset_gmt/3600);		//convert to hours from local stop timezone
			
			if($new_offset_gmt==0)		$new_offset_gmt=abs($offset_gmt);
			
			$stamp0=date("m/d/Y H:i",strtotime("".($new_offset_gmt-1)." hours",strtotime($att_window_starter)));		//trip start time
			$stamp =date("m/d/Y H:i",strtotime("".($new_offset_gmt  )." hours",strtotime($att_window_ender)));		//arriving time     			
			$stamp2=date("m/d/Y H:i",strtotime("".($new_offset_gmt )." hours",strtotime($att_window_ender)));		//arrived time
			$stamp3=date("m/d/Y H:i",strtotime("".($new_offset_gmt+1)." hours",strtotime($att_window_ender)));		//departed time
			
			$cust=mrr_get_all_customer_settings($row['customer_id']);	
			
			$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
			$hl_timer=$cust['hot_load_timer'];					//interval between messages
			
			$hl_geo_active=$cust['geofencing_radius_active'];		//turn on actual geofencing notices
			$hl_geo_on_all=$cust['geofencing_hot_msg_all_loads'];	//all loads set to on...
			  			
			$trailer_name="N/A";
			$t_drop="";
			     						
			if($disp_stops==0)	$first_stop_date=$stamp0;	
			$stops++;
			$disp_stops++;		
			
				
			//".($test_mode > 0 ? "TEST " : "")."
			$label="Stop ".$disp_stops.": ".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')." ".date("Y-m-d H:i",strtotime($row['linedate_pickup_eta']))."";
			
			$namer=trim($row['shipper_name']);
			$namer=str_replace("&"," and ",$namer);
			$namer=str_replace("'","",$namer);
			$namer=str_replace("#","",$namer);
			
			$info="".trim($row['name_company'])."";
			$notes="Phone ".trim($row['stop_phone'])."";
			
			$address=trim($row['shipper_address1']);
			$city=trim($row['shipper_city']);
			$state=trim($row['shipper_state']);
			$zip=trim($row['shipper_zip']);
			
			
			$stop_string.="-".$label.": ".$namer." - ".$address."; ".$city.", ".$state." ".$zip.". ".$notes.".";
			
						
			$stop_long=trim($row['longitude']);
			$stop_lat=trim($row['latitude']);
			$geotab_stop_id=trim($row['geotab_stop_id']);
			$geotab_zone_id=$row['geotab_zone_id'];		
						
			if($stop_lat=="" || $stop_lat=="0" || $stop_long=="" || $stop_long=="0")
			{	//no GPS point, so find one.
				$res=mrr_geotab_get_coordinate_from_addr($address,$city,$state,$zip);
				$stop_lat=trim($res['lat']);
				$stop_long=trim($res['long']);
			}
			
			if(floatval($stop_long)!=0 && floatval($stop_long)!=0)
			{				
				$sres=mrr_find_geotab_stop_zones_by_gps($stop_long,$stop_lat);
				$geotab_zone_id=$sres['id'];
     			if($geotab_zone_id > 0 && trim($sres['geotab_id_name'])=="")
     			{	//Zone found by GPS point, but blank... try again by address.
     				$sres=mrr_find_geotab_stop_zones_by_addr($address,$city,$state,$zip);   
     				$geotab_zone_id=$sres['id']; 				
     				//$geotab_stop_id=trim($sres['geotab_id_name']);  				
     			}
     			
     			
     			if($geotab_zone_id > 0)
     			{
     				$geotab_stop_id=mrr_find_geotab_stop_zones_by_id($geotab_zone_id);
     				
     				if($geotab_stop_id=="")
     				{
     					$geotab_stop_id=mrr_get_geotab_zones("",trim($namer),1);
     					if($geotab_stop_id=="")
     					{
     						$gres=mrr_gps_point_box_creator($stop_long,$stop_lat,$x_off,$y_off);
          					$res['long_zone_w']="".$gres['pt0_long_w']."";
               				$res['long_zone_e']="".$gres['pt1_long_e']."";
               				$res['lat_zone_n']="".$gres['pt0_lat_n']."";
               				$res['lat_zone_s']="".$gres['pt1_lat_s']."";
          					
          					$geotab_stop_id=mrr_make_geotab_zone($stop_long,$stop_lat,trim($namer),"","",1,trim($gres['pt0_long_w']),trim($gres['pt1_long_e']),trim($gres['pt0_lat_n']),trim($gres['pt1_lat_s']));
          					          					
          					$res['geotab_id_name']=trim($geotab_stop_id);
          					$res['conard_name']=trim($namer);          					     									
          					
          					$geotab_zone_id=mrr_create_geotab_stop_zones($res);	
     					}
     					     									
     					$res['geotab_id_name']=trim($geotab_stop_id);
     					$res['conard_name']=trim($namer);
     					$res['id']=$geotab_zone_id;
     					$res['long']=$stop_long;
          				$res['lat']=$stop_lat;	
          				
          				$res['address_1']=trim($address);
          				$res['city']=trim($city);
          				$res['state']=trim($state);
          				$res['zip']=trim($zip);   
     					
     					mrr_update_geotab_stop_zones_gps_shipper_by_id($res);
     					mrr_update_geotab_stop_zones_points_name_by_id($res);
     				}
     				else
     				{
     					mrr_set_geotab_zones_displayed($geotab_stop_id,1);	
     				}
     			}
     			else
     			{	//not already saved, so see if the zone exists.
     				$res=mrr_find_geotab_stop_zones_by_addr(trim($address),trim($city),trim($state),trim($zip));
     				$geotab_zone_id=$res['id'];
     				
     				if($geotab_zone_id > 0)
     				{
     					$geotab_stop_id=trim($res['geotab_id_name']);
     										
     					if($geotab_stop_id=="")
     					{          					
     						$gres=mrr_gps_point_box_creator($stop_long,$stop_lat,$x_off,$y_off);
          					$res['long_zone_w']="".$gres['pt0_long_w']."";
               				$res['long_zone_e']="".$gres['pt1_long_e']."";
               				$res['lat_zone_n']="".$gres['pt0_lat_n']."";
               				$res['lat_zone_s']="".$gres['pt1_lat_s']."";
          					
          					$geotab_stop_id=mrr_make_geotab_zone($stop_long,$stop_lat,trim($namer),"","",1,trim($gres['pt0_long_w']),trim($gres['pt1_long_e']),trim($gres['pt0_lat_n']),trim($gres['pt1_lat_s']));
          					          					
          					$res['geotab_id_name']=trim($geotab_stop_id);
          					$res['conard_name']=trim($namer);      					
          					          					
          					$geotab_zone_id=mrr_create_geotab_stop_zones($res);	
     					}	
     					else
     					{
     						mrr_set_geotab_zones_displayed($geotab_stop_id,1);	
     					}	
     														
     					$res['geotab_id_name']=trim($geotab_stop_id);
     					$res['conard_name']=trim($namer);
     					$res['id']=$geotab_zone_id;
     					$res['long']=$stop_long;
          				$res['lat']=$stop_lat;		
     					
     					$res['address_1']=trim($address);
          				$res['city']=trim($city);
          				$res['state']=trim($state);
          				$res['zip']=trim($zip);   
          				
     					mrr_update_geotab_stop_zones_gps_shipper_by_id($res);
     					mrr_update_geotab_stop_zones_points_name_by_id($res);			
     				}
     				else
     				{	//not stored, so create on GeoTab side.
     					$gres=mrr_gps_point_box_creator($stop_long,$stop_lat,$x_off,$y_off);
     					$res['long_zone_w']="".$gres['pt0_long_w']."";
          				$res['long_zone_e']="".$gres['pt1_long_e']."";
          				$res['lat_zone_n']="".$gres['pt0_lat_n']."";
          				$res['lat_zone_s']="".$gres['pt1_lat_s']."";
     					
     					$geotab_stop_id=mrr_make_geotab_zone($stop_long,$stop_lat,trim($namer),"","",1,trim($gres['pt0_long_w']),trim($gres['pt1_long_e']),trim($gres['pt0_lat_n']),trim($gres['pt1_lat_s']));
     					     					
     					$res['geotab_id_name']=trim($geotab_stop_id);
     					$res['conard_name']=trim($namer);
     					$res['long']=$stop_long;
     					$res['lat']=$stop_lat; 
     					
     					$res['address_1']=trim($address);
          				$res['city']=trim($city);
          				$res['state']=trim($state);
          				$res['zip']=trim($zip);       					    										
     					
     					$geotab_zone_id=mrr_create_geotab_stop_zones($res);					
     				}
     			}       			
			}
			else
			{	//NO GPS was created...so SKIP it.
     			
			}
			
			$geotab_load_id=trim($row['geotab_load_id']);
			$geotab_msg_id=trim($row['geotab_load_msg_id']);	
			//$geotab_stop_id=trim($row['geotab_stop_id']);
          	$saved_stops=0;			
			
			if(trim($geotab_stop_id)!="")
          	{
          		$mrr_goetab_zoned_stops++;
          		
          		$appt_start="".date("Y-m-d")." 00:00";								//these dates are always for the message delivery to the device...not the appointment slot.
          		$appt_ended="".date("Y-m-d",strtotime("+7 days",time()))." 23:59";		//these dates are always for the message delivery to the device...not the appointment slot.
          		
          		//$appt_start="".date("Y-m-d H:i",strtotime("+".abs(mrr_gmt_offset_val())." hours",strtotime($row['linedate_pickup_eta'])))."";
          		//$appt_ended="".date("Y-m-d H:i",strtotime("+".(abs(mrr_gmt_offset_val())+48)." hours",strtotime($row['linedate_pickup_eta'])))."";
          		if($row['appointment_window'] > 0)
          		{
          			//$appt_start="".date("Y-m-d H:i",strtotime("+".abs(mrr_gmt_offset_val())." hours",strtotime($row['linedate_appt_window_start'])))."";
          			//$appt_ended="".date("Y-m-d H:i",strtotime("+".abs(mrr_gmt_offset_val())." hours",strtotime($row['linedate_appt_window_end'])))."";	
          		} 
          		$appt_start=str_replace(" ","T",$appt_start);  
          		$appt_ended=str_replace(" ","T",$appt_ended);       		
          		
          		$route_info[($disp_stops -1)]["id"]=trim($geotab_stop_id);	
          		$route_info[($disp_stops -1)]["from"]="".$appt_start.":00.000Z";
          		$route_info[($disp_stops -1)]["to"]  ="".$appt_ended.":00.000Z";	
          		$route_info[($disp_stops -1)]["date"]="".date("Y-m-d",strtotime("+".abs(mrr_gmt_offset_val())." hours",strtotime($row['linedate_pickup_eta'])))."";   
          		
          		if($disp_stops > 1)		$dnotes.=", ";
          		$dnotes.="".$label."";
          		
          		$sqlu="
                    	update load_handler_stops set
                    		geotab_stop_id='".sql_friendly( trim( $geotab_stop_id))."',
                    		geotab_zone_id='".sql_friendly( (int) $geotab_zone_id)."'
                    	where id='".sql_friendly($row['id'])."'
                    ";
                    simple_query($sqlu);       		
          	}
			
			$output.="
				<tr>
					<td valign='top'>PREPLAN</td>
					<td valign='top'>".$row['id']."</td>
					<td valign='top'>".$truckname."</td>
					<td valign='top'>&nbsp;</td>
					
					<td valign='top'>".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')."</td>
					<td valign='top'>".$row['name_company']."</td>
					<td valign='top'>".$row['stop_phone']."</td>
					
					<td valign='top'>".$namer."</td>
					<td valign='top'>".$row['shipper_address1']."</td>
					<td valign='top'>".$row['shipper_address2']."</td>
					<td valign='top'>".$row['shipper_city']."</td>
					<td valign='top'>".$row['shipper_state']."</td>
					<td valign='top'>".$row['shipper_zip']."</td>
										
					<td valign='top'>".$att_window_starter."</td>
					
					<td valign='top'>".$row['longitude']."</td>
					<td valign='top'>".$row['latitude']."</td>
					
					<td valign='top'>".$geotab_load_id."</td>
					<td valign='top'>".$geotab_stop_id."</td>
					<td valign='top'>".$geotab_zone_id."</td>
					<td valign='top'>".$saved_stops."</td>
				</tr>
			";
			// ".$load_str."
			
			if($hl_geo_on_all > 0)
			{
				//	
			}
			
			$mrr_stop_list[$mrr_stop_cntr]=$row['id'];
			$mrr_stop_cntr++;
			
			//if dispatch is already there, remove it first...          		
          	if(trim($geotab_load_id)!="")
          	{
          		$killed=mrr_kill_geotab_route("Route",trim($geotab_load_id));          			
          	}
			
			//if all stops accounted for (with Zones) and only one preplaned selected to send to GeoTab, package and send it out.
			if($disp_stops==$mrr_goetab_zoned_stops && $run_preplan > 0)
			{	// && $disp_stops==$mn
				$dnamer="PREPLAN ".$load_str3;
				//$dnotes="";		
				
				$output.="
					<tr>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='18'>
							Load Name: ".$dnamer."
							<br>
							Load Notes: ".$dnotes."
						</td>
						<td valign='top' align='right'></td>
					</tr>
				";	
				
				
				$test_msg="".(trim($geotab_msg_id)!="" ? "UPDATE " : "")."PREPLAN Load ".$row['load_handler_id'].": ".$stop_string." Info: ".$load_str3." Notes: ".$dnotes."";
				$msg_urgent=1;
				$geotab_msg_id=mrr_send_geotab_text_message($find_truck_id,$test_msg,$msg_urgent);
				if(trim($geotab_msg_id)!="" && trim($geotab_msg_id)!="0")
				{
					$sqluv="
                    		update load_handler set
                    			geotab_load_msg_id='".sql_friendly(trim($geotab_msg_id))."'
                    		where id='".sql_friendly($row['load_handler_id'])."'
                    	";
                    	simple_query($sqluv);  	
				}	
				
				
				$geotab_load_id=mrr_make_geotab_route($find_truck_id,$route_info,$dnamer,$dnotes,1,0);
				if(trim($geotab_load_id)!="" && trim($geotab_load_id)!="0")
				{				
					$sqluv="
                    		update load_handler set
                    			geotab_load_id='".sql_friendly(trim($geotab_load_id))."'
                    		where id='".sql_friendly($row['load_handler_id'])."'
                    	";
                    	simple_query($sqluv);  	
				}
				
				$disp_arr[$disp_cntr]=$find_load_id;
				$disp_pre[$disp_cntr]=1;
				$disp_cntr++;
			}
			
		}	//end main while loop
	}	//end if load_id>0 check
	
	$output.="
			<tr>
				<td valign='top' colspan='120'><br><br>".$disp_reports."</td>
			</tr>
		</table>
		<br>";
	     	
	$res['truck_id']=$find_truck_id;
	$res['truck_name']=$truckname;
	$res['load_id']=$find_load_id;
	$res['dispatch_cntr']=$disp_cntr;
	$res['disp_arr']=$disp_arr;
	$res['disp_pre']=$disp_pre;
	$res['output']=$output;
	$res['geotab_id']=$geotab_load_id;
	
	return $res;	
}

function mrr_pull_geotab_active_geofencing_rows($mode=0)
{
	global $new_style_path;
	global $defaultsarray;
	
	$mydate=date("Y-m-d");		//today...
			
	$tab="";				//activity report
	$tab2="";				//load board notices  MODE 1
	
	$tab_pickup="";		//activity report split
	$tab_delivery="";		//activity report split
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
          	<td><b>Away</b></td>
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
			
			trucks.geotab_last_longitude as truck_long,
			trucks.geotab_last_latitude as truck_lat,
			trucks.geotab_gps_date,
			trucks.geotab_truck_speed,
			trucks.geotab_current_location,
			
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
			load_handler_stops.geotab_zone_id,
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
			
		where load_handler.deleted<=0  
			and load_handler_stops.deleted<=0 	
			and trucks_log.deleted<=0	
			and trucks.deleted<=0
			and drivers.deleted<=0
			and customers.deleted<=0
			
			and trucks.geotab_device_id=''
			
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null)
			and trucks_log.dispatch_completed<=0     			
			
		order by load_handler_stops.linedate_pickup_eta asc,
				load_handler_stops.load_handler_id asc,     				
				load_handler_stops.trucks_log_id asc,
				load_handler_stops.id asc						
	";		//and (trucks.geotab_device_id='' or trucks_log.truck_id!=497)
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
				and trucks_log_notes.deleted <= 0
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
			
			trucks.geotab_last_longitude as truck_long,
			trucks.geotab_last_latitude as truck_lat,
			trucks.geotab_gps_date,
			trucks.geotab_truck_speed,
			trucks.geotab_current_location,
			
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
			load_handler_stops.geotab_zone_id,
			load_handler_stops.stop_grade_id,
			load_handler_stops.stop_grade_note,	
			load_handler_stops.latitude,
			load_handler_stops.longitude,	
			load_handler_stops.pro_miles_dist,
			load_handler_stops.pro_miles_eta,
			load_handler_stops.pro_miles_due,    
			load_handler_stops.geofencing_arriving_sent,	 			
			load_handler_stops.geofencing_arrived_sent,	
			load_handler_stops.geofencing_departed_sent,	
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
			
		where load_handler.deleted<=0  
			and load_handler_stops.deleted<=0 	
			and trucks_log.deleted<=0	
			and trucks.deleted<=0
			and drivers.deleted<=0
			and customers.deleted<=0
			and trucks.geotab_device_id!='' 
			
			
			
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
			and trucks_log.dispatch_completed<=0  			
			
		order by load_handler_stops.linedate_pickup_eta asc,
				load_handler_stops.load_handler_id asc,     				
				load_handler_stops.trucks_log_id asc,
				load_handler_stops.id asc						
	";		//and trucks_log.truck_id=497
	$datax=simple_query($sqlx);
	
	while($rowx=mysqli_fetch_array($datax))
	{     		
		
		$nt_cntr=0;
		$sqlnt = "
			select count(*) as mycntr
			
			from trucks_log_notes
			where trucks_log_notes.truck_log_id = '".sql_friendly($rowx['trucks_log_id'])."'
				and trucks_log_notes.linedate_added >= '".date("Y-m-d", strtotime("-7 day", time()))."'
				and trucks_log_notes.deleted <= 0
		";
		$datant = simple_query($sqlnt);
		if($rownt=mysqli_fetch_array($datant))
		{
			$nt_cntr=$rownt['mycntr'];	
		}
		
		$stop_lat=$rowx['latitude'];		
		$stop_long=$rowx['longitude'];
		
		
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
		
		$geotab_arriving=$rowx['geofencing_arriving_sent'];
		$geotab_arrived=$rowx['geofencing_arrived_sent'];
		$geotab_departed=$rowx['geofencing_departed_sent'];
		
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
			//$res=mrr_peoplenet_email_processor_fetch_truck_lat_long_alt1($rowx['truckid'],date("m/d/Y",strtotime($rowx['dispatch_pickup_eta'])));
			
			//$res=mrr_find_geotab_location_of_this_truck($rowx['truckid'],0);		//$poll_now=0
			
			//$res['truck_name']=$truck_name;
			//$res['device_id']=$geotab_id;
			
			//$res['gps_location']=$gps_location;
			
			$truck_lat=$rowx['truck_lat'];		
			$truck_long=$rowx['truck_long'];
			$truck_date=$rowx['geotab_gps_date'];
			$truck_speed=number_format( ((int) $rowx['geotab_truck_speed'] / 1.6) ,2);
			$gps_location=trim($rowx['geotab_current_location']);
			if($gps_location=="")
			{
				$gps_location=mrr_update_geotab_truck_location($rowx['truckid'],$truck_long,$truck_lat);
			}
			$truck_lat2=floatval($truck_lat);		
			$truck_long2=floatval($truck_long);
			
			//$truck_lat=$res['latitude'];		//$res['lat'];
			//$truck_long=$res['longitude'];	//$res['long'];
			////$truck_age=$res['age'];
			//$truck_date=$res['date'];
			////$truck_heading=$res['closer'];
			//$gps_location=$res['location'];	
			//$truck_speed=$res['truck_speed'];	
			$truck_head=0;				//$res['truck_heading'];
			
				
			$head_mask="";				//"North";
          	if($truck_head == 1)		$head_mask="NE";
          	if($truck_head == 2)		$head_mask="East";
          	if($truck_head == 3)		$head_mask="SE";
          	if($truck_head == 4)		$head_mask="South";
          	if($truck_head == 5)		$head_mask="SW";
          	if($truck_head == 6)		$head_mask="West";
          	if($truck_head == 7)		$head_mask="NW";	
          	
			$head_mask=mrr_distance_between_gps_points($stop_lat,$stop_long,$truck_lat,$truck_long,0);
			$head_mask=round(abs(floatval($head_mask)),2);		
			
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
			$mrr_heading="";	//" heading ".$head_mask."";
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
						
			$geotab_zone_id=$rowx['geotab_zone_id'];
			if($geotab_zone_id > 0)
			{
				$gres=mrr_find_geotab_stop_zones_by_gps_from_id($geotab_zone_id);
				
               	//$gres['id']=0;
               	//$gres['geotab_id_name']="";
               	//$gres['conard_name']="";
               	//$gres['address_1']="";
               	//$gres['city']="";
               	//$gres['state']="";
               	//$gres['zip']="";
               	//$gres['long']="";
               	//$gres['lat']="";
               	
               	if($geotab_arriving==0 && $tracking_dist <=5)
               	{
               		//arriving...within 5 miles.
               		$geotab_arriving=1;
               	}  
               	
               	if($geotab_arriving>0 && $geotab_arrived==0 && $truck_lat2 >= floatval($gres['long_zone_w'])  && $truck_lat2 <= floatval($gres['long_zone_e'])  &&  $truck_long2 >= floatval($gres['lat_zone_s'])  && $truck_long2 <= floatval($gres['lat_zone_n']) )
               	{
               		//arrived...now in the zone.	
               		$geotab_arrived=1;
               	}   
               	
               	if($geotab_arrived > 0 && $geotab_departed==0 && ($truck_lat2 < floatval($gres['long_zone_w'])  || $truck_lat2 > floatval($gres['long_zone_e']) ||  $truck_long2 < floatval($gres['lat_zone_s'])  || $truck_long2 > floatval($gres['lat_zone_n']) ))
               	{
               		//departed... had arrived...now out of the zone.	
               		$geotab_departed=1;
               	}             	
			}
			$geotab_display="
				<br>GeoTab Arriving: ".($geotab_arriving > 0 ? "YES":"no").".
				<br>GeoTab Arrived: ".($geotab_arrived > 0 ? "YES":"no").".
				<br>GeoTab Departed: ".($geotab_departed > 0 ? "YES":"no").".
			";
			$geotab_display="<br>".$stop_long.",".$stop_lat."  |  ".$truck_long.",".$truck_lat."";
			$geotab_display="";     			
			
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
               			<td>".$tracking_local."".$geotab_display."</td> 
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
               			<td>".$tracking_local."".$geotab_display."</td>            
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
          			$tab2.=			"<span>".date("m/d/Y H:i",strtotime($tracking_date))." --- ".$linker1."</span>";
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
		<tr><td colspan='".$cwidth."'><b>NON_GEOTAB:  NO TRACKING AVAILABLE.</b></td></tr>
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
	$tab2.=		"<p>Dispatch must have been sent via GeoTab.</p> ";
	//$tab2.=		"<p>Hot Load Tracking must be turned on for each Load.</p> ";
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

function mrr_pull_geotab_active_geofencing_rows_no_display($mode=0)
{
	global $new_style_path;
	global $defaultsarray;
	    	
	$gps_too_old_minutes=15;		$pn_truck_cntr=0;	
	
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
			
		where load_handler.deleted<=0  
			and load_handler_stops.deleted<=0 	
			and trucks_log.deleted<=0	
			and trucks.deleted<=0
			and drivers.deleted<=0
			and customers.deleted<=0
			and trucks.geotab_device_id!='' 
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null)
			and trucks_log.dispatch_completed<=0     			
			
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
			
			$stop_lat=$rowx['latitude'];		
			$stop_long=$rowx['longitude'];		
			     			
			//check the distance by location packets...Location Mode Email...should be most accurate if polling trucks is done frequently.  Do second so that the email gets checked even after the email goes out.
			$truck_distance=0;
			$miles_distance=0;
			
			$res=mrr_find_geotab_location_of_this_truck($rowx['truckid'],0);		//$poll_now=0
			
			//$res['truck_name']=$truck_name;
			//$res['device_id']=$geotab_id;
			
			//$res['gps_location']=$gps_location;
			
			
			$truck_lat=$res['latitude'];		//$res['lat'];
			$truck_long=$res['longitude'];	//$res['long'];
			
			//$res=mrr_peoplenet_email_processor_fetch_truck_lat_long_alt1($rowx['truckid'],date("m/d/Y",strtotime($rowx['dispatch_pickup_eta'])));
			//$truck_lat=$res['lat'];
			//$truck_long=$res['long'];
			////$truck_age=$res['age'];
			////$truck_date=$res['date'];
			////$truck_heading=$res['closer'];
			////$gps_location=$res['location'];	
			////$truck_speed=$res['truck_speed'];	
			////$truck_head=$res['truck_heading'];	
			
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

function mrr_compare_geotab_location_with_current_stops($truck_id=0,$load_id=0,$disp_id=0,$stop_id=0)
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
			load_handler.alt_tracking_email,
			trucks.name_truck,
			trucks_log.truck_id,
			trucks_log.customer_id,
			customers.name_company
		from load_handler_stops
			left join trucks_log on trucks_log.id=load_handler_stops.trucks_log_id
			left join load_handler on load_handler.id=load_handler_stops.load_handler_id
			left join trucks on trucks.id=trucks_log.truck_id
			left join customers on customers.id=trucks_log.customer_id
		where load_handler_stops.deleted<=0
			and load_handler.deleted<=0
			and trucks_log.deleted<=0
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
						
		$res=mrr_find_geotab_location_of_this_truck($rowx['truckid'],0);		//$poll_now=0
		
		//$res['truck_name']=$truck_name;
		//$res['device_id']=$geotab_id;
		
		//$res['gps_location']=$gps_location;
			
		$truck_lat=$res['latitude'];		//$res['lat'];
		$truck_long=$res['longitude'];	//$res['long'];
		//$truck_age=$res['age'];
		$truck_date=$res['date'];
		//$truck_heading=$res['closer'];
		$gps_location=$res['location'];	
		$truck_speed=$res['truck_speed'];	
		$truck_head=0;				//$res['truck_heading'];
		
		//$res=mrr_peoplenet_email_processor_fetch_truck_lat_long($row['truck_id'],date("m/d/Y",strtotime($row['my_pickup'])));
		//$truck_lat=$res['lat'];
		//$truck_long=$res['long'];
		//$truck_age=$res['age'];
		//$truck_date=$res['date'];
		//$truck_heading=$res['closer'];
		//$gps_location=$res['location'];	
		//$truck_speed=$res['truck_speed'];	
		//$truck_head=$res['truck_heading'];
						
		$head_mask="";				//North
     	if($truck_head == 1)		$head_mask="Northeast ";
     	if($truck_head == 2)		$head_mask="East";
     	if($truck_head == 3)		$head_mask="Southeast";
     	if($truck_head == 4)		$head_mask="South";
     	if($truck_head == 5)		$head_mask="Southwest";
     	if($truck_head == 6)		$head_mask="West";
     	if($truck_head == 7)		$head_mask="Northwest";				
			
		$mrr_speed="".$truck_speed." MPH";
		$mrr_heading="";	//" heading ".$head_mask."";
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
						
			if(trim($row['alt_tracking_email'])!="")		$tolist=trim($row['alt_tracking_email']);
			
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


function mrr_geotab_duel_msg_archiver($days=0)
{	//archive messages > DAYS old 
	if($days>0)
	{
		$sql = "
			update ".mrr_find_log_database_name()."geotab_messages_received set
				archived='1'
			where linedate_added < DATE_SUB(NOW(),INTERVAL ".$days." DAY)
				and archived <= 0
		";     		
		simple_query($sql);	
		
		$sql = "
			update ".mrr_find_log_database_name()."geotab_messages_sent set
				archived='1'
			where linedate_added < DATE_SUB(NOW(),INTERVAL ".$days." DAY)
				and archived <= 0
		";     		
		simple_query($sql); 
	}
}


function mrr_find_geotab_drivers_truck_from_elog($geotab_user_id,$mydate)
{
	$truck_id=0;
	
	if(trim($geotab_user_id)=="")		return $truck_id;	
	
	$device_id="";		
	$sql="
		select device_id
		from ".mrr_find_log_database_name()."geotab_datafeed_log
		where geotab_user='".sql_friendly(trim($geotab_user_id))."'
			and linedate_added>='".$mydate." 00:00:00'
			and feed_type>=4
		order by linedate_added desc,id desc
	";
	$data=simple_query($sql);		
	if($row = mysqli_fetch_array($data)) 
	{	
		$device_id=trim($row['device_id']);
	}
	if(trim($device_id)=="")
	{
		$sql = "
			select id
			from trucks 
			where geotab_device_id='".sql_friendly(trim($device_id))."'
			order by active desc,id asc
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$truck_id=$row['id'];
		}
	}
	return $truck_id;	
}
function mrr_find_geotab_truck_drivers_from_elog($truck_id,$mydate,$cd=0)
{
	$res['load_id']=0;
	$res['dispatch_id']=0;
	$res['driver_id_1']=0;
	$res['driver_id_2']=0;
	$res['driver_name_1']="";
	$res['driver_name_2']="";
	
	$res['sql_1']="";
	$res['sql_2']="";	
	
	$last_driver=0;
	$dcntr=0;
	$used=0;
	$drivers="";
	
	$geotab_device_id="";
	$sql = "
		select name_truck,geotab_device_id 
		from trucks 
		where id='".sql_friendly($truck_id)."'
	";
	$res['sql_1']=$sql;
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$geotab_device_id=trim($row['geotab_device_id']);
	}
			
	$sql="
		select geotab_user		
		from ".mrr_find_log_database_name()."geotab_datafeed_log
		where device_id='".sql_friendly(trim($geotab_device_id))."'
			and linedate_added>='".$mydate." 00:00:00'
			and feed_type >= 4
			and geotab_user!=''
			and is_driver > 0
		order by linedate_added desc,id desc
	";
	$res['sql_2']=$sql;
	$data=simple_query($sql);		
	while($row = mysqli_fetch_array($data)) 
	{				
		$sqlx = "
			select id,name_driver_first,name_driver_last				 
			from drivers
			where geotab_use_id='".sql_friendly(trim($row['geotab_user']))."'
		";
		$datax=simple_query($sqlx);
		if($rowx=mysqli_fetch_array($datax))
		{
			if($dcntr==0 && $last_driver!=$rowx['id'])
     		{
     			$res['driver_id_1']=$rowx['id'];
     			$res['driver_name_1']="".trim($rowx['name_driver_first'])." ".trim($rowx['name_driver_last'])."";
     			
     			$drivers="<a href='admin_drivers.php?id=".$rowx['id']."' target='_blank'>".trim($rowx['name_driver_first'])." ".trim($rowx['name_driver_last'])."</a> ";
     			$used=1;
     		}
     		elseif($dcntr > 0 && $last_driver!=$rowx['id'] && $rowx['id']!=$res['driver_id_1'] && $used==1)
     		{
     			$res['driver_id_2']=$rowx['id'];
     			$res['driver_name_2']="".trim($rowx['name_driver_first'])." ".trim($rowx['name_driver_last'])."";
     			
     			$drivers.=" and <a href='admin_drivers.php?id=".$rowx['id']."' target='_blank'>".trim($rowx['name_driver_first'])." ".trim($rowx['name_driver_last'])."</a>2 ";
     			$used++;
     		}
     		
     		$last_driver=$rowx['id'];
     		$dcntr++;		
		}
	}
	if($cd==1)
	{
		return $res;
	}
	return $drivers;	
}

function mrr_trim_old_geotab_datafeed_log_points($days,$feed_type=0,$kill_all=0)
{
	$test_dater=date("Y-m-d",strtotime("-".$days." days",time()));		//replaces "DATE_SUB(NOW(),INTERVAL 7 DAY)" in query below.
	
	if($kill_all > 0)
	{
		$sql="delete from ".mrr_find_log_database_name()."geotab_datafeed_log where linedate_added < '".$test_dater." 00:00:00'";
		simple_query($sql);
	}
	elseif($feed_type > 0)
	{
		$sql="delete from ".mrr_find_log_database_name()."geotab_datafeed_log where linedate_added < '".$test_dater." 00:00:00' and feed_type='".(int) $feed_type."'";
		simple_query($sql);
	}
	
	//now, purge odometer/diagnostic reading table...
	$sql="delete from ".mrr_find_log_database_name()."geotab_odometer_diagnostics where linedate_added < '".$test_dater." 00:00:00'";
	simple_query($sql);
	
	//set odometer readings by last log entry	
}

function mrr_get_geotab_dispatches_sent_to_truck($date_from, $date_to, $truck_id=0, $truck_name="",$limit=0, $archived=0)
{	//dispatches sent to PN for given truck... 
	global $new_style_path;
	global $defaultsarray;
	
	$date_range_msg_history=" and trucks_log.linedate_pickup_eta>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and trucks_log.linedate_pickup_eta<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	
	$lim_txt="";
	if($limit>0)	$lim_txt=" limit ".$limit."";
	
	$archiver=" and trucks_log.linedate_pickup_eta>='".date("Y-m-01",strtotime("-60 day",time()))." 00:00:00'";		//only go from the beginning of the current month
	if($archived > 0)	$archiver="";	//$archiver=" and trucks_log.linedate_pickup_eta>='".date("Y-01-01",time())." 00:00:00'";		//archive goes back to beginning of year
			
	$mcntr=0; 
	$tab="";	
		
	$sql3 = "
		select trucks_log.id,
			trucks_log.geotab_disp_id,
			trucks_log.load_handler_id,
			trucks_log.origin,
			trucks_log.origin_state,
			trucks_log.destination,
			trucks_log.destination_state,
			trucks_log.linedate_pickup_eta,
			(select preplan from load_handler where load_handler.id=trucks_log.load_handler_id) as preplan_use_load_id,
			(select count(*) from load_handler_stops where load_handler_stops.trucks_log_id=trucks_log.id) as stops,
			customers.name_company,
			drivers.name_driver_first as driver_first_name,
			drivers.name_driver_last as driver_last_name,
			trailers.trailer_name
			
		from trucks_log
			left join customers on customers.id=trucks_log.customer_id
			left join trailers on trailers.id=trucks_log.trailer_id
			left join drivers on drivers.id=trucks_log.driver_id
		where trucks_log.truck_id='".sql_friendly($truck_id) ."'
			and trucks_log.geotab_disp_id!=''
			".$date_range_msg_history."
			".$archiver."
		order by trucks_log.linedate_pickup_eta desc
		".$lim_txt."
	";
	$data3 = simple_query($sql3);
	$mn3=mysqli_num_rows($data3);	
	if($mn3>0)
	{
		$tab.="<tr>
				<td valign='top'><b>Load</b></td>
				<td valign='top'><b>Dispatch</b></td>						
				<td valign='top'><b>GeoTabID</b></td>
				<td valign='top'><b>Driver</b></td>						
				<td valign='top'><b>Stops</b></td>
				<td valign='top'><b>ETA</b></td>	
				<td valign='top'><b>Trailer</b></td>
				<td valign='top'><b>Customer</b></td>
				<td valign='top'><b>Origin</b></td>
				<td valign='top'><b>Origin State</b></td>
				<td valign='top'><b>Dest</b></td>					
				<td valign='top'><b>Dest State</b></td>
			</tr>";					
				//<td valign='top'><b>User</b></td>
				//<td valign='top'><b>Sent</b></td>
				//<td valign='top'><b>Date</b></td>
	}		
	
	while($row3 = mysqli_fetch_array($data3))
	{
		//$read_user=mrr_peoplenet_pull_quick_username($row3['user_id']);
		//$sent_date=date("m/d/Y H:i:s",strtotime($row3['linedate_added']));			if($row3['linedate_added']=="0000-00-00 00:00:00") 	$sent_date="";
		//$disp_date=date("m/d/Y H:i:s",strtotime($row3['linedate_pickup_eta']));		if($row3['linedate_pickup_eta']=="0000-00-00 00:00:00") 		$disp_date="";
		$driver=$row3['driver_first_name']." ".$row3['driver_last_name']; 
		
		$pick_date=date("m/d/Y H:i:s",strtotime($row3['linedate_pickup_eta']));			
		
		$alink="<a href='manage_load.php?load_id=".$row3['load_handler_id']."' target='_blank'>".$row3['load_handler_id']."</a>";
		$blink="<a href='add_entry_truck.php?load_id=".$row3['load_handler_id']."&id=".$row3['id']."' target='_blank'>".$row3['id']."</a>";
		if($row3['preplan_use_load_id'] > 0)
		{
			$alink="<a href='manage_load.php?load_id=".$row3['id']."' target='_blank'>".$row3['id']."</a>";
			$blink="<b>Preplan</b>";		
		}
								
		$tab.="<tr>
				<td valign='top'>".$alink."</td>
				<td valign='top'>".$blink."</td>				
				<td valign='top'>".$row3['geotab_disp_id']."</td>
				<td valign='top'>".$driver."</td>						
				<td valign='top'>".$row3['stops']."</td>
				<td valign='top'>".$pick_date."</td>	
				<td valign='top'>".$row3['trailer_name']."</td>
				<td valign='top'>".$row3['name_company']."</td>
				<td valign='top'>".$row3['origin']."</td>
				<td valign='top'>".$row3['origin_state']."</td>
				<td valign='top'>".$row3['destination']."</td>
				<td valign='top'>".$row3['destination_state']."</td>
			</tr>";	
				//<td valign='top'>".$read_user."</td>
				//<td valign='top'>".$sent_date."</td>	
				//<td valign='top'>".$disp_date."</td>
		$mcntr++;					
	}		
	if($mcntr==0)
	{
		$tab="<tr><td valign='top' colspan='12'>No dispatches found.</td></tr>";	
	}
	//$tab.="<tr><td valign='top' colspan='12'>Query: ".$sql3.".</td></tr>";	
					
	return $tab;
}

function mrr_get_geotab_messages_sent_by_truck($date_from, $date_to, $truck_id=0, $truck_name="",$limit=0, $archived=0,$mode=0,$cd=0)
{	//messages pulled from GeoTab API
	global $new_style_path;
	global $defaultsarray;
	
	$lim_txt="";
	if($limit>0)	$lim_txt=" limit ".$limit."";
	
	$date_range_msg_history=" and geotab_messages_received.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and geotab_messages_received.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
		
	$archiver=" and archived='".sql_friendly($archived)."'";
	if($archived > 0)
	{
		$date_range_msg_history=" and geotab_messages_received.linedate_added < '".date("Y-m-d",strtotime($date_to))." 23:59:59'";	
		$archiver="";
	}
	
	$timezoning=trim($defaultsarray['gmt_offset_label']);
	
	$date_range_msg_history3=" and twilio_call_log.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and twilio_call_log.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	$mrr_adder3="";
			
	$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
	//$offset_gmt=$offset_gmt * -1;
	
	
	//Find driver GeoTab ID from truck ID
	$dres=mrr_find_pn_truck_drivers($truck_id,"".date("Y-m-d",strtotime($date_to))."",1);
	$driver_user_id=trim($dres['driver_user_1']);
	
	$msg_truck_name="";
	$geotab_device_id="";
	if($truck_id > 0)
	{
		$sql = "
			select name_truck,geotab_device_id		
			from trucks
			where id = '".sql_friendly($truck_id)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		$msg_truck_name=trim($row['name_truck']);
		$geotab_device_id=trim($row['geotab_device_id']);
	}
	
			
	//send and stored messages from Peoplenet Interface
	$mcntr=0; 
	$mcntr3=0;
	$tab="";		//
	$tab2="";		//load board version
	
	$tab3="";		//load board  simple version
			/*
				id,
				linedate_added,
				geotab_id,
				device_id,
											
				msg_to_truck,
				date_sent,
				date_delivered,
				
				geotab_user,
				is_driver,
				date_read,
				read_by,
				read_by_driver,
				
				msg_type,
				reply_required,
				msg_body,
				msg_options,
				
				archived,
				no_response_needed,
				linedate_read,
				read_user_id,
				read_reply_id,
				read_reply_user_id
			*/
			
			//and trucks.id='".sql_friendly($truck_id) ."'
			//trucks.id
			//trucks.name_truck
			//left join trucks on trucks.geotab_device_id=geotab_messages_received.device_id
	$sql3 = "
		select geotab_messages_received.id as my_id,
			0 as my_load,
			0 as my_disp,
			geotab_messages_received.linedate_added as my_date,
			geotab_messages_received.msg_body as my_msg,					
			
			geotab_messages_received.read_user_id as my_user_id_read,
			geotab_messages_received.linedate_read as my_linedate_read,
			geotab_messages_received.read_reply_user_id as my_user_id_reply,
			geotab_messages_received.linedate_read as my_linedate_reply,
			'' as my_truck_id,
			'' as my_truck_name,
			geotab_messages_received.geotab_user as my_recipient_name,
			geotab_messages_received.linedate_added as my_linedate_created,
			geotab_messages_received.linedate_added as my_linedate_added,
			geotab_messages_received.linedate_added as my_linedate_received,
			geotab_messages_received.reply_required as alert_sent,	
			(select name_driver_first from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_first,	
			(select name_driver_last from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_last,
			'Sent' as mrr_mode
			
		from ".mrr_find_log_database_name()."geotab_messages_received
			left join drivers on drivers.geotab_use_id=geotab_messages_received.geotab_user
			
			
		where geotab_messages_received.msg_to_truck<=0
			".($geotab_device_id!="" ? "and geotab_messages_received.device_id='".sql_friendly($geotab_device_id) ."'" : "and drivers.geotab_use_id='".sql_friendly($driver_user_id) ."'")."
			
			".$date_range_msg_history."
			".$archiver."
			     			
		union all 		
			
		select twilio_call_log.id,
			twilio_call_log.load_id,
			twilio_call_log.disp_id,
			'0000-00-00 00:00:00',
			twilio_call_log.message,	
			0,
			'0000-00-00 00:00:00',
			0,
			'0000-00-00 00:00:00',
			0,
			twilio_call_log.text_code,
			twilio_call_log.stop_id,
			twilio_call_log.linedate_added,
			twilio_call_log.linedate_added,
			'0000-00-00 00:00:00',     	
			1,	
			'',		
			'',		
			'Phoned'
			
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
	if($mn3>0)
	{
		$tab.="<tr>
				<td valign='top'><b>ID</b></td>
				<td valign='top'><b>Received</b></td>					
				<td valign='top'><b>Recipient</b></td>
				<td valign='top'><b>Driver</b></td>
				<td valign='top'><b>Read By</b></td>
				<td valign='top'><b>Read Date</b></td>
				<td valign='top'><b>Reply By</b></td>
				<td valign='top'><b>Reply Date</b></td>
				<td valign='top' colspan='7'><b>GeoTab Message sent from truck ".$truck_name."</b></td>
				<td valign='top'>&nbsp;</td>
			</tr>";	
			
		$tab3.="<tr>					
				<td valign='top' width='50'><b>MsgID</b></td>
				<td valign='top' width='100'><b>Received</b></td>
				<td valign='top' width='100'><b>Read By</b></td>
				<td valign='top' width='100'><b>Read Date</b></td>
				<td valign='top' width='100'><b>Reply By</b></td>
				<td valign='top' width='100'><b>Reply Date</b></td>
				<td valign='top' width='300'><b>GeoTab Message sent by ".$truck_name."</b></td>
			</tr>";	
			
		//if($cd > 0)		$tab3.="<tr><td colspan='7'>Query: ".$sql3."</td></tr>";
	}		
	
	$mydate=date("Y-m-d");		//today...
	
	while($row3 = mysqli_fetch_array($data3))
	{
		if($row3['mrr_mode']=="Sent")
		{     			
			$read_user=mrr_peoplenet_pull_quick_username($row3['my_user_id_read']);
			$read_date=date("m/d/Y H:i:s",strtotime($row3['my_linedate_read']));			if($row3['my_linedate_read']=="0000-00-00 00:00:00") 	$read_date="";
			
			$reply_date="";
			$reply_user=mrr_peoplenet_pull_quick_username($row3['my_user_id_reply']);
			//$reply_date=date("m/d/Y H:i:s",strtotime($row3['my_linedate_reply']));		if($row3['my_linedate_reply']=="0000-00-00 00:00:00") 	$reply_date="";
			
			$dres=mrr_find_pn_truck_drivers($row3['my_truck_id'],$mydate,1);    			
			$driver=$dres['driver_name_1'];
			$load_id=$dres['load_id'];
			$disp_id=$dres['dispatch_id'];
			//$dres['driver_id_1']=0;
			//$dres['driver_id_2']=0;
			//$dres['driver_name_2']="";	
			
			$driver_name=trim("".$row3['driver_first']." ".$row3['driver_last']."");
			 			
     		//$row3['my_recipient_name']=str_replace("b11","<b>Dispatch</b>",$row3['my_recipient_name']);
               if($row3['my_recipient_name']=="b11")        $row3['my_recipient_name']="<b>Dispatch</b>";
     		
     		//$maint_link=mrr_prep_auto_maint_link($row3['my_id'],$row3['my_truck_id'],trim($row3['my_msg']),$row3['alert_sent']);
     		$maint_link="";
     		
			if(substr_count($row3['my_msg'],"Warning: ")==0)
			{			
				/*
     			geotab_messages_received.id as my_id,
     			0 as my_load,
     			0 as my_disp,
     			geotab_messages_received.linedate_added as my_date,
     			geotab_messages_received.msg_body as my_msg,					
     			
     			geotab_messages_received.read_user_id as my_user_id_read,
     			geotab_messages_received.linedate_read as my_linedate_read,
     			geotab_messages_received.read_reply_user_id as my_user_id_reply,
     			geotab_messages_received.linedate_read as my_linedate_reply,
     			trucks.id as my_truck_id,
     			trucks.name_truck as my_truck_name,
     			geotab_messages_received.geotab_user as my_recipient_name,
     			geotab_messages_received.linedate_added as my_linedate_created,
     			geotab_messages_received.linedate_added as my_linedate_added,
     			geotab_messages_received.linedate_added as my_linedate_received,
     			geotab_messages_received.reply_required as alert_sent,	
     			(select name_driver_first from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_first,	
     			(select name_driver_last from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_last,
     			'Sent' as mrr_mode
				*/
				
				
				$tab.="<tr>
     						<td valign='top'><span class='mrr_link_like_on' id='msg_list_id_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$row3['my_id']."</span></td>
     						<td valign='top'><span class='mrr_link_like_on' id='msg_list_created_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".date("m/d/Y H:i",strtotime($row3['my_linedate_added']))."".$timezoning."</span></td>
     						<td valign='top'><span class='mrr_link_like_on' id='msg_list_recipient_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$row3['my_recipient_name']."</span></td>
     						<td valign='top'><span class='mrr_link_like_on' id='msg_list_driver_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'><b>".$driver_name."</b></span></td>
     						<td valign='top'><span class='mrr_link_like_on' id='msg_list_read_user_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$read_user."</span></td>
     						<td valign='top'><span class='mrr_link_like_on' id='msg_list_read_date_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$read_date."</span></td>
     						<td valign='top'><span class='mrr_link_like_on' id='msg_list_reply_user_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$reply_user."</span></td>
     						<td valign='top'><span class='mrr_link_like_on' id='msg_list_reply_date_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$reply_date."</span></td>
     						<td valign='top' colspan='7'><span class='mrr_link_like_on' id='msg_list_msg_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".$row3['my_msg']."</span></td>
     						<td valign='top'>".$maint_link."</td>
						</tr>";
						//<td valign='top'><span class='mrr_link_like_on' id='msg_list_received_".$row3['my_id']."' onClick='mrr_fill_reader(".$row3['my_id'].");'>".mrr_peoplenet_time_mask_from_gmt($row3['my_linedate_received'])."</span></td>
				
				//load board version...      date("m/d/Y H:i:s",strtotime("-6 hours",strtotime($row3['my_linedate_received'])))
				
				$tab2.=	"<li>";
     			$tab2.=		"<h3>";
     			$tab2.=			"<span>".date("m/d/Y H:i",strtotime($row3['my_linedate_added']))."".$timezoning." --- <a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".trim($row3['my_truck_name'])."</a></span>";     	//admin_trucks.php?id=".$row3['truck_id']."
     			//$tab2.=			"<a href='javascript:delete_event($row[my_calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     			$tab2.=			"<a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
     			$tab2.=		"</h3>";
     			$tab2.=		"<p>
     							Driver(s): ".$driver."<br>
     							<a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>Unread...Click here to read.</a> ".trim($row3['my_recipient_name'])." [".$driver_name."]: ".$row3['my_msg']."
     							
     							<img src='/images/note_msg.png' border='0' alt='Reply' width='12' height='16' onClick=\"pn_msg_box_mini_reply(".$disp_id.",".$load_id.",".$row3['my_truck_id'].",'".$mydate." 00:00:00');\">
								<div id='pn_note_mini_holder_".$disp_id."'></div>   
     						</p> ";
     			$tab2.=	"</li>";
						
				$tab3.="<tr>
     						<td valign='top'><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".$row3['my_id']."</a></td>
     						<td valign='top'><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".date("m/d/Y H:i",strtotime($row3['my_linedate_added']))."".$timezoning."</a></td>
     						<td valign='top'>".$read_user."</td>
     						<td valign='top'><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".$read_date."</a></td>
     						<td valign='top'>".$reply_user."</td>
     						<td valign='top'><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".$reply_date."</a></td>
     						<td valign='top'>".$row3['my_msg']."</td>
						</tr>";
						
				$mcntr++;
			}
		}	
		elseif($row3['mrr_mode']=="Phoned")
		{	
			$tab.="<tr>
						<td valign='top'>&nbsp;</td>
						<td valign='top'>".date("m/d/Y H:i", strtotime($row3['my_linedate_created']))."</td>
						<td valign='top'>PHONED</td>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='2'>".$row3['my_truck_name']."</td>
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
     			//simple_query($sqlu);		
			}
			
			/*
			//load board version...
			$tab2.=	"<li>";
			$tab2.=		"<h3>";
			$tab2.=			"<span>".date("m/d/Y H:i", strtotime($row3['my_linedate_created']))." --- <a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=0'>".trim($row3['my_truck_name'])."</a></span>";     	//admin_trucks.php?id=".$row3['truck_id']."
			//$tab2.=			"<a href='javascript:delete_event($row[my_calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
			$tab2.=			"<a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=0'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
			$tab2.=		"</h3>";
			$tab2.=		"<p>Driver(s): ".$driver."<br><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=0'>Unread...Click here to read.</a> ".trim($row3['my_recipient_name']).": ".$row3['my_msg']."</p> ";
			$tab2.=	"</li>";
			*/		
			
			//<a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=0'>".$row3['my_id']."</a>
			$tab3.="<tr>
						<td valign='top'>PHONED</td>
						<td valign='top'><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=0'>".date("m/d/Y H:i", strtotime($row3['my_linedate_created']))."</a></td>
						<td valign='top' colspan='2'>".$row3['my_truck_name']."</td>
						<td valign='top' colspan='2'>Stop ".$row3['my_recipient_name']."</td>
						<td valign='top'>".$row3['my_msg']."</td>
					</tr>";
					
			$mcntr3++;	
		}	
	}
	
	if($mcntr==0 && $mcntr3==0)
	{
		$tab="<tr><td valign='top' colspan='16'>No messages found.</td></tr>";	//<br>".$sql3."
	}
	
	$tab2.=	"<li>";
	//$tab2.=		"<a href='index.php?geotab_on=0'>View PeopleNet Messages</a>";
	$tab2.=		"<h3>";
	$tab2.=			"<br><span>GeoTab and MSGS Legend</span>";    			
	$tab2.=		"</h3>";
	$tab2.=		"<p><b>Geo</b>=GeoTab Dispatch can be sent.</p> ";
	$tab2.=		"<p><b>MSGS</b>=Message link for this truck.</p> ";
	$tab2.=		"<p style='color:red; font-weight:bold;'>Dispatch has not been sent to truck.</p>";
	$tab2.=		"<p style='color:orange; font-weight:bold;'>Updated since last send.</p>";
	$tab2.=		"<p style='color:green; font-weight:bold;'>Dispatch sent to truck.</p>";
	$tab2.=		"<p>Colors based on sent status. MSGS flag will always match GeoTab link based on status of GeoTab dispatch.  MSGS is quick link to messages.</p> ";
	$tab2.=		"<p>Hover on Load to see distance and current truck location.  <b>No Distance.</b> displays when dispatch has not been sent through GeoTab system and no GPS coordinates have been calculated for the stops.  Send dispatch to fix this.</p> ";
	//$tab2.=		"<p style='color:purple; font-weight:bold;'>Query: ".$sql3."</p>";
	$tab2.=	"</li>";	
	
	if($mode==0)	return $tab;
	if($mode==1)	return $tab2;
	if($mode==3)	return $tab3;
}

function mrr_get_geotab_unread_messages_sent_by_all_trucks($limit=0,$mode=0)
{	//messages pulled from packets...only new messages (unread) from all trucks
	global $new_style_path;
	global $defaultsarray;
	
	$lim_txt="";
	if($limit>0)	$lim_txt=" limit ".$limit."";
	
	
	$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
	//$offset_gmt=$offset_gmt * -1;
	
	$timezoning=trim($defaultsarray['gmt_offset_label']);
			
	//send and stored messages from Peoplenet Interface
	$mcntr=0; 
	$tab="";		//
	$tab2="";		//load board version
	
	$dater=date("Y-m-d",strtotime("-1 week",time()));
	
	$sql3 = "
		select geotab_messages_received.id as my_id,
			0 as my_load,
			0 as my_disp,
			geotab_messages_received.linedate_added as my_date,
			geotab_messages_received.msg_body as my_msg,					
			
			geotab_messages_received.read_user_id as my_user_id_read,
			geotab_messages_received.linedate_read as my_linedate_read,
			geotab_messages_received.read_reply_user_id as my_user_id_reply,
			geotab_messages_received.linedate_read as my_linedate_reply,
			trucks.id as my_truck_id,
			trucks.name_truck as my_truck_name,
			geotab_messages_received.geotab_user as my_recipient_name,
			geotab_messages_received.linedate_added as my_linedate_created,
			geotab_messages_received.linedate_added as my_linedate_added,
			geotab_messages_received.linedate_added as my_linedate_received,
			geotab_messages_received.reply_required as alert_sent,	
			(select name_driver_first from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_first,	
			(select name_driver_last from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_last,
			'Sent' as mrr_mode
			
		from ".mrr_find_log_database_name()."geotab_messages_received
			left join trucks on trucks.geotab_device_id=geotab_messages_received.device_id
		where geotab_messages_received.msg_to_truck<=0			
			and geotab_messages_received.archived<=0
			and geotab_messages_received.read_user_id<=0
			and geotab_messages_received.no_response_needed<=0
		order by geotab_messages_received.linedate_added desc,geotab_messages_received.id desc
		".$lim_txt."
	";     	//and geotab_messages_received.linedate_added>='".$dater." 00:00:00'
	$data3 = simple_query($sql3);
	$mn3=mysqli_num_rows($data3);	
	if($mn3>0)
	{
		$tab.="<tr>
				<td valign='top'><b>ID</b></td>
				<td valign='top'><b>Recipient</b></td>   						
				<td valign='top'><b>Received</b></td>     						
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>Driver</b></td>
				<td valign='top'><b>Read By</b></td>
				<td valign='top'><b>Read Date</b></td>
				<td valign='top'><b>Reply By</b></td>
				<td valign='top'><b>Reply Date</b></td>
				<td valign='top'><b>Message</b></td>
				<td valign='top'><b>&nbsp;</b></td>
			</tr>
		";	
	}		
	
	$mydate=date("Y-m-d",time());		//today...
	
	while($row3 = mysqli_fetch_array($data3))
	{
		$read_user=mrr_peoplenet_pull_quick_username($row3['my_user_id_read']);
		$read_date=date("m/d/Y H:i:s",strtotime($row3['my_linedate_read']));			if($row3['my_linedate_read']=="0000-00-00 00:00:00") 	$read_date="";
		$reply_date="";
		$reply_user=mrr_peoplenet_pull_quick_username($row3['my_user_id_reply']);
		//$reply_date=date("m/d/Y H:i:s",strtotime($row3['my_linedate_reply']));			if($row3['my_linedate_reply']=="0000-00-00 00:00:00") 	$reply_date="";
		$driver_name=trim("".$row3['driver_first']." ".$row3['driver_last']."");
		
		$dres=mrr_find_pn_truck_drivers($row3['my_truck_id'],$mydate,1);  	
				
		$driver=$dres['driver_name_1'];
		$load_id=$dres['load_id'];
		$disp_id=$dres['dispatch_id'];
		//$dres['driver_id_1']=0;
		//$dres['driver_id_2']=0;
		//$dres['driver_name_2']="";	
		
		//$driver_name=trim($driver);
		
		
		  			
		//$recipient=str_replace("b11","<b>Dispatch</b>",$row3['my_recipient_name']);
		
		$recipient="<b>DISPATCH</b>";
		
		$msg_id=$row3['my_id'];
		$truck_id=(int) $row3['my_truck_id'];
		$truck_name=trim($row3['my_truck_name']);
		
		$dres=mrr_find_pn_truck_drivers($truck_id,$mydate,1);			
		$driver=$dres['driver_name_1'];
		$load_id=$dres['load_id'];
		$disp_id=$dres['dispatch_id'];	
		
		
		
		$maint_link="";
		//$maint_link=mrr_prep_auto_maint_link($msg_id,$truck_id,trim($row3['my_msg']),$row3['alert_sent']);		//truck_tracking_msg_history.alert_sent_flag as alert_sent,	
		
		
		if(1==1)
		{			
			$tab.="<tr>
						<td valign='top'><a href='geotab_messenger.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'>".$msg_id."</a></td>
						<td valign='top'>".$recipient."</td>
						<td valign='top'>".date("m/d/Y H:i",strtotime($row3['my_linedate_added']))."".$timezoning."</td>						
						<td valign='top'><b>".trim($truck_name)."<b></td>
						<td valign='top'><b>".$driver_name."<b></td>
						<td valign='top'>".$read_user."</td>
						<td valign='top'>".$read_date."</td>
						<td valign='top'>".$reply_user."</td>
						<td valign='top'>
							".$reply_date." 
							<span class='mrr_link_like_on' onClick='mrr_ignore_new_messages(".$msg_id.",".$_SESSION['user_id'].");' title='click to ignore, or if no response is needed.'>
								<img src='/images/2012/red_icon1.png' alt='X' border='0' width='15' height='14'>
							</span>
						</td>
						<td valign='top'><a href='geotab_messenger.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'>".$row3['my_msg']."</a></td>
						<td valign='top'>".$maint_link."</a></td>
					</tr>";
			
			
			
			$tab2.=	"<li>";
			$tab2.=		"<h3>";
			$tab2.=			"<span>".date("m/d/Y H:i",strtotime($row3['my_linedate_added']))."".$timezoning." --- <a href='geotab_messenger.php?truck_id=".$truck_id."&reply_id=".$msg_id."'>".trim($truck_name)."</a></span>";     	//admin_trucks.php?id=".$row3['truck_id']."
			//$tab2.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
			$tab2.=			"<a href='geotab_messenger.php?truck_id=".$truck_id."&reply_id=".$msg_id."'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
			$tab2.=		"</h3>";
			$tab2.=		"<p>Driver(s): ".$driver_name."<br>
							<a href='geotab_messenger.php?truck_id=".$truck_id."&reply_id=".$msg_id."'>Unread...Click here to read.</a> ".$recipient.": ".$row3['my_msg']."
							<span class='mrr_link_like_on' onClick='mrr_ignore_new_messages_geotab(".$msg_id.",".$_SESSION['user_id'].");' title='click to ignore, or if no response is needed.'>
								<img src='/images/2012/red_icon1.png' alt='X' border='0' width='15' height='14'>
							</span>	
							
							<img src='/images/note_msg.png' border='0' alt='Reply' width='12' height='16' onClick=\"pn_msg_box_mini_reply(".$disp_id.",".$load_id.",".$truck_id.",'".$mydate." 00:00:00');\">
							".$maint_link."
							<div id='pn_note_mini_holder_".$disp_id."'></div>
						</p> ";
			$tab2.=	"</li>";
					
					
			$mcntr++;
		}

		
	}
	
	if($mcntr==0)
	{
		$tab="<tr><td valign='top' colspan='16'>No messages found.</td></tr>";	
	}
	
	$tab2.=	"<li>";
	//$tab2.=		"<a href='index.php?geotab_on=0'>View PeopleNet Messages</a>";
	$tab2.=		"<h3>";
	$tab2.=			"<br><span>GeoTab and MSGS Legend</span>";    			
	$tab2.=		"</h3>";
	$tab2.=		"<p><b>Geo</b>=GeoTab Dispatch can be sent.</p> ";
	$tab2.=		"<p><b>MSGS</b>=Message link for this truck.</p> ";
	$tab2.=		"<p style='color:red; font-weight:bold;'>Dispatch has not been sent to truck.</p>";
	$tab2.=		"<p style='color:orange; font-weight:bold;'>Updated since last send.</p>";
	$tab2.=		"<p style='color:green; font-weight:bold;'>Dispatch sent to truck.</p>";
	$tab2.=		"<p>Colors based on sent status. MSGS flag will always match GeoTab link based on status of GeoTab dispatch.  MSGS is quick link to messages.</p> ";
	$tab2.=		"<p>Hover on Load to see distance and current truck location.  <b>No Distance.</b> displays when dispatch has not been sent through GeoTab system and no GPS coordinates have been calculated for the stops.  Send dispatch to fix this.</p> ";
	$tab2.=	"</li>";	
	
	if($mode==0)	return $tab;
	if($mode==1)	return $tab2;
}

function mrr_get_geotab_messages_sent_to_truck($date_from, $date_to, $truck_id=0, $truck_name="",$limit=0, $archived=0,$mode=0)
{	//sent and stored messages from GeoTab Interface or Messager
	if($mode > 0)
	{
		$date_range_message="";
	}
	else
	{
		$date_range_message=" and geotab_messages_received.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and geotab_messages_received.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";	
	}
	
	$lim_txt="";
	if($limit>0)	$lim_txt=" limit ".$limit."";
	
	$archiver=" and geotab_messages_received.archived='".sql_friendly($archived)."'";
	
	$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
	//$offset_gmt=$offset_gmt * -1;
	
	$mcntr=0; 
	$tab="";
	$tab3="";
	
	$sql3 = "
		select geotab_messages_received.id as my_id,
			0 as my_load,
			0 as my_disp,
			
			0 as reply_msg_id,
			
			geotab_messages_received.linedate_added as my_date,
			geotab_messages_received.msg_body as my_msg,					
			
			geotab_messages_received.read_user_id as my_user_id_read,
			geotab_messages_received.linedate_read as my_linedate_read,
			geotab_messages_received.read_reply_user_id as my_user_id_reply,
			geotab_messages_received.linedate_read as my_linedate_reply,
			trucks.id as my_truck_id,
			trucks.name_truck as my_truck_name,
			geotab_messages_received.geotab_user as my_recipient_name,
			geotab_messages_received.linedate_added as my_linedate_created,
			geotab_messages_received.linedate_added as my_linedate_added,
			geotab_messages_received.linedate_added as my_linedate_received,
			geotab_messages_received.reply_required as alert_sent,	
			(select name_driver_first from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_first,	
			(select name_driver_last from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_last,
			'Sent' as mrr_mode
			
		from ".mrr_find_log_database_name()."geotab_messages_received
			left join trucks on trucks.geotab_device_id=geotab_messages_received.device_id
		where geotab_messages_received.msg_to_truck > 0
			".($truck_id > 0 ? "and trucks.id='".sql_friendly($truck_id) ."'" : "")."			
			".$date_range_message."
			".$archiver."
			and geotab_messages_received.read_user_id<=0
			and geotab_messages_received.no_response_needed<=0
			
			
		order by geotab_messages_received.linedate_added desc,geotab_messages_received.id desc
		".$lim_txt."
	";
	$data3 = simple_query($sql3);
	$mn3=mysqli_num_rows($data3);
	if($mn3>0)
	{
		$tab.="
					<tr>
						<td valign='top'><b>Msg<b></td>
						<td valign='top'><b>Sent Date</b></td>
						<td valign='top' align='right'><b>&nbsp;</b></td>
						<td valign='top' align='right'><b>Replied</b></td>
						<td valign='top' align='right'><b>Sent by</b></td>
						<td valign='top' align='right'><b>&nbsp;</b></td>
						<td valign='top' colspan='10'><b>Message</b></td>
					</tr>
		";
		$tab3.="
					<tr>
						<td valign='top' width='150'><b>Sent Date</b></td>
						<td valign='top' width='100' align='right'><b>Reply Msg</b></td>
						<td valign='top' width='200' align='right'><b>Replied to MsgID</b></td>     						
						<td valign='top' width='400'><b>Message sent to ".$truck_name."</b></td>
					</tr>
		";	
	}			
	while($row3 = mysqli_fetch_array($data3))
	{
		$clink="";
		if($row3['my_linedate_added']!="0000-00-00 00:00:00")		$clink="".date("m/d/Y H:i:s",strtotime($row3['my_linedate_added']))."";
		$dlink="";
		if($row3['my_user_id_read'] > 0)						$dlink="".mrr_peoplenet_pull_quick_username($row3['my_user_id_read'])."";
		
		$rlink="";
		if($row3['reply_msg_id'] > 0)							$rlink="<span class='mrr_link_like_on' onClick='mrr_fill_reader(".$row3['reply_msg_id'].");'>".$row3['reply_msg_id']."</span>";
		
		
		$tab.="<tr>
				<td valign='top'><span class='mrr_link_like_on' id='msg_view_send_id_".$row3['my_id']."' onClick='mrr_view_reader(".$row3['my_id'].");'>".$row3['my_id']."</span></td>
				<td valign='top'><span class='mrr_link_like_on' id='msg_view_send_date_".$row3['my_id']."' onClick='mrr_view_reader(".$row3['my_id'].");'>".$clink."</span></td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'><span class='mrr_link_like_on' id='msg_view_replied_".$row3['my_id']."'>".$rlink."</span></td>
				<td valign='top' align='right'><span class='mrr_link_like_on' id='msg_view_sender_".$row3['my_id']."' onClick='mrr_view_reader(".$row3['my_id'].");'>".$dlink."</span></td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' colspan='10'><span class='mrr_link_like_on' id='msg_view_msg_".$row3['my_id']."' onClick='mrr_view_reader(".$row3['my_id'].");'>".$row3['my_msg']."</span></td>
			</tr>";
		
		$tab3.="<tr>
				<td valign='top'><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."'>".date("m/d/Y H:i:s",strtotime($row3['my_linedate_added']))."</a></td>
				<td valign='top' align='right'>".mrr_peoplenet_pull_quick_username($row3['my_user_id_read'])."</td>
				<td valign='top' align='right'><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['reply_msg_id']."'>".$row3['reply_msg_id']."</a></td>					
				<td valign='top'>".$row3['my_msg']."</td>
			</tr>";
					
		$mcntr++;
	}
	
	if($mcntr==0)
	{
		$tab="<tr><td valign='top' colspan='16'>No messages found.</td></tr>";	
	}
	
	if($mode==3)		return $tab3;
	return $tab;
}

function mrr_get_geotab_messages_sent_to_truck_true($date_from, $date_to, $truck_id=0, $truck_name="",$limit=0, $archived=0,$mode=0,$cd=0)
{	//sent and stored messages from GeoTab Interface or Messager
	$lim_txt="";
	if($limit>0)	$lim_txt=" limit ".$limit."";
	
	$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
	//$offset_gmt=$offset_gmt * -1;
	
	$mcntr=0; 
	$tab="";
	$tab3="";
	
	
	/*
	$date_range_message=" and geotab_messages_sent.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and geotab_messages_sent.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";	
	
	$archiver=" and geotab_messages_sent.archived='".sql_friendly($archived)."'";
		
	$sql3 = "
		select geotab_messages_sent.*,
			trucks.name_truck as my_truck_name,
			(select username from users where users.id=geotab_messages_sent.actual_user_id) as user_name
			
		from ".mrr_find_log_database_name()."geotab_messages_sent
			left join trucks on trucks.id=geotab_messages_sent.truck_id
		where geotab_messages_sent.truck_id='".sql_friendly($truck_id) ."'
			".$date_range_message."
			".$archiver."			
		order by geotab_messages_sent.linedate_added desc,geotab_messages_sent.id desc
		".$lim_txt."
	";
	$data3 = simple_query($sql3);
	$mn3=mysqli_num_rows($data3);
	if($mn3>0)
	{
		$tab.="
					<tr>
						<td valign='top'><b>Msg<b></td>
						<td valign='top'><b>Sent Date</b></td>
						<td valign='top' align='right'><b>&nbsp;</b></td>
						<td valign='top' align='right'><b>To</b></td>
						<td valign='top' align='right'><b>From</b></td>
						<td valign='top' align='right'><b>&nbsp;</b></td>
						<td valign='top' colspan='10'><b>GeoTab Message</b></td>
					</tr>
		";
		$tab3.="
					<tr>
						<td valign='top' width='150'><b>Sent Date</b></td>
						<td valign='top' width='100' align='right'><b>Reply Msg</b></td>
						<td valign='top' width='200' align='right'><b>Replied to MsgID</b></td>     						
						<td valign='top' width='400'><b>GeoTab Message sent to ".$truck_name."</b></td>
					</tr>
		";	
	}			
	while($row3 = mysqli_fetch_array($data3))
	{
		$clink="";
		if($row3['linedate_added']!="0000-00-00 00:00:00")		$clink="".date("m/d/Y H:i:s",strtotime($row3['linedate_added']))."";
		$dlink="";
		//if($row3['my_user_id_read'] > 0)						$dlink="".mrr_peoplenet_pull_quick_username($row3['my_user_id_read'])."";
		
		$rlink="".trim($row3['my_truck_name'])."";
		//if($row3['reply_msg_id'] > 0)						$rlink="<span class='mrr_link_like_on' onClick='mrr_fill_reader(".$row3['reply_msg_id'].");'>".$row3['reply_msg_id']."</span>";
		
		$sent_msg=trim($row3['message_sent']);	//Stop 2 - 20
		
		if((substr_count($sent_msg,"Stop 1 - ") == 0 && substr_count($sent_msg,"Stop 2 - ") == 0 && substr_count($sent_msg,"Stop 3 - ") == 0 
			&& substr_count($sent_msg,"Stop 4 - ") == 0 && substr_count($sent_msg,"Stop 5 - ") == 0 && substr_count($sent_msg,"Stop 6 - ") == 0)
			|| $cd==0)
		{
     		$tab.="<tr>
     				<td valign='top'><span class='mrr_link_like_on' id='msg_view_send_id_".$row3['id']."' onClick='mrr_view_reader(".$row3['id'].");'>".$row3['id']."</span></td>
     				<td valign='top'><span class='mrr_link_like_on' id='msg_view_send_date_".$row3['id']."' onClick='mrr_view_reader(".$row3['id'].");'>".$clink."</span></td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'><span class='mrr_link_like_on' id='msg_view_replied_".$row3['id']."'>".$rlink."</span></td>
     				<td valign='top' align='right'><span class='mrr_link_like_on' id='msg_view_sender_".$row3['id']."' onClick='mrr_view_reader(".$row3['id'].");'>".trim($row3['user_name'])."</span></td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' colspan='10'><span class='mrr_link_like_on' id='msg_view_msg_".$row3['id']."' onClick='mrr_view_reader(".$row3['id'].");'>".$sent_msg."</span></td>
     			</tr>";
     		
     		$tab3.="<tr>
     				<td valign='top'><a href='geotab_messenger.php?truck_id=".$row3['truck_id']."'>".date("m/d/Y H:i:s",strtotime($row3['linedate_added']))."</a></td>
     				<td valign='top' align='right'>".trim($row3['user_name'])."</td>
     				<td valign='top' align='right'><a href='geotab_messenger.php?truck_id=".$row3['truck_id']."&reply_id=".$row3['id']."'>".$rlink."</a></td>					
     				<td valign='top'>".$sent_msg."</td>
     			</tr>";
     					
     		$mcntr++;
		}
	}
	*/
	
	if($mode > 0)
	{
		$date_range_message="";
	}
	else
	{
		$date_range_message=" and geotab_messages_received.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and geotab_messages_received.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";	
	}
	
	$archiver=" and geotab_messages_received.archived='".sql_friendly($archived)."'";
	
	$sql3="
		select geotab_messages_received.id as my_id,
			0 as my_load,
			0 as my_disp,
			
			0 as reply_msg_id,
			
			geotab_messages_received.linedate_added as my_date,
			geotab_messages_received.msg_body as my_msg,		
			
			geotab_messages_received.msg_type as my_type,		
			geotab_messages_received.msg_options as my_opts,	
			
			geotab_messages_received.read_user_id as my_user_id_read,
			geotab_messages_received.linedate_read as my_linedate_read,
			geotab_messages_received.read_reply_user_id as my_user_id_reply,
			geotab_messages_received.linedate_read as my_linedate_reply,
			trucks.id as my_truck_id,
			trucks.name_truck as my_truck_name,
			geotab_messages_received.geotab_user as my_recipient_name,
			geotab_messages_received.linedate_added as my_linedate_created,
			geotab_messages_received.linedate_added as my_linedate_added,
			geotab_messages_received.linedate_added as my_linedate_received,
			geotab_messages_received.reply_required as alert_sent,	
			(select name_driver_first from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_first,	
			(select name_driver_last from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_last,
			'Sent' as mrr_mode
			
		from ".mrr_find_log_database_name()."geotab_messages_received
			left join trucks on trucks.geotab_device_id=geotab_messages_received.device_id
		where geotab_messages_received.msg_to_truck > 0
			".($truck_id > 0 ? "and trucks.id='".sql_friendly($truck_id) ."'" : "")."			
			".$date_range_message."
			".$archiver."
			
		order by geotab_messages_received.linedate_added desc,geotab_messages_received.id desc
		".$lim_txt."
	";
		//and geotab_messages_received.read_user_id='0'
		//and geotab_messages_received.no_response_needed='0'
		
	$data3 = simple_query($sql3);	
	$mn3=mysqli_num_rows($data3);
	if($mn3>0)
	{
		$tab.="
					<tr>
						<td valign='top'><b>Msg<b></td>
						<td valign='top'><b>Sent Date</b></td>
						<td valign='top' align='right'><b>&nbsp;</b></td>
						<td valign='top' align='right'><b>Replied</b></td>
						<td valign='top' align='right'><b>Sent by</b></td>
						<td valign='top' align='right'><b>Type</b></td>
						<td valign='top' colspan='10'><b>Message</b></td>
					</tr>
		";
		$tab3.="
					<tr>
						<td valign='top' width='150'><b>Sent Date</b></td>
						<td valign='top' width='100' align='right'><b>Reply Msg</b></td>
						<td valign='top' width='100' align='right'><b>Replied to MsgID</b></td> 
						<td valign='top' width='100' align='right'><b>Type</b></td>    						
						<td valign='top'><b>Message sent to ".$truck_name."</b></td>
					</tr>
		";	
	}	
	while($row3 = mysqli_fetch_array($data3))
	{
		$clink="";
		if($row3['my_linedate_added']!="0000-00-00 00:00:00")		$clink="".date("m/d/Y H:i:s",strtotime($row3['my_linedate_added']))."";
		$dlink="";
		if($row3['my_user_id_read'] > 0)						$dlink="".mrr_peoplenet_pull_quick_username($row3['my_user_id_read'])."";
		
		$rlink="";
		if($row3['reply_msg_id'] > 0)							$rlink="<span class='mrr_link_like_on' onClick='mrr_fill_reader(".$row3['reply_msg_id'].");'>".$row3['reply_msg_id']."</span>";
		
		$msg_typer=trim($row3['my_type']);
		$msg_typer=str_replace("CannedResponse","Canned",$msg_typer);
		
		$msg_opts="<b>".trim($row3['my_opts'])."</b>";
		
		if($msg_typer!="Location" || 1==1)
		{
     		$tab.="<tr>
     				<td valign='top'><span class='mrr_link_like_on' id='msg_view_send_id_".$row3['my_id']."' onClick='mrr_view_reader(".$row3['my_id'].");'>".$row3['my_id']."</span></td>
     				<td valign='top'><span class='mrr_link_like_on' id='msg_view_send_date_".$row3['my_id']."' onClick='mrr_view_reader(".$row3['my_id'].");'>".$clink."</span></td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'><span class='mrr_link_like_on' id='msg_view_replied_".$row3['my_id']."'>".$rlink."</span></td>
     				<td valign='top' align='right'><span class='mrr_link_like_on' id='msg_view_sender_".$row3['my_id']."' onClick='mrr_view_reader(".$row3['my_id'].");'>".$dlink."</span></td>
     				<td valign='top' align='right'>".$msg_typer."</td>
     				<td valign='top' colspan='10'><span class='mrr_link_like_on' id='msg_view_msg_".$row3['my_id']."' onClick='mrr_view_reader(".$row3['my_id'].");'>".$row3['my_msg']."</span>".$msg_opts."</td>
     			</tr>";
     		
     		$tab3.="<tr>
     				<td valign='top'><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."'>".date("m/d/Y H:i:s",strtotime($row3['my_linedate_added']))."</a></td>
     				<td valign='top' align='right'>".mrr_peoplenet_pull_quick_username($row3['my_user_id_read'])."</td>
     				<td valign='top' align='right'><a href='geotab_messenger.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['reply_msg_id']."'>".$row3['reply_msg_id']."</a></td>	
     				<td valign='top' align='right'>".$msg_typer."</td>				
     				<td valign='top'>".$row3['my_msg']."".$msg_opts."</td>
     			</tr>";
     					
     		$mcntr++;
		}
	}
	
	
	if($mcntr==0)
	{
		$tab="<tr><td valign='top' colspan='16'>No messages found.</td></tr>";	
		$tab3="";
	}
	
	if($mode==3)		return $tab3;
	return $tab;
}



function mrr_process_current_geotab_location_of_trucks($save_local=0,$truck_id=0)
{
	global $defaultsarray;	
	$last_processed=trim($defaultsarray['geotab_process_last_date']);	
	if($last_processed=="")		$last_processed=date("Y-m-d H:i:s",time());	
	
	$last_processed=date("Y-m-d H:i:s",strtotime("-2 hour", strtotime($last_processed)));		// H:i:s         -12 hour
	
	$tab="<h2>".$last_processed." - ".($truck_id > 0 ? "Truck ID = ".(int) $truck_id.":" : "All Trucks:")."</h2>
		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
		<tr>
			<td valign='top'><b>ID<b></td>
			<td valign='top'><b>Date</b></td>
			<td valign='top'><b>GeoTab<b></td>
			<td valign='top'><b>Device<b></td>
			<td valign='top'><b>Truck<b></td>
			<td valign='top'><b>Date<b></td>
			<td valign='top'><b>Longitude<b></td>
			<td valign='top'><b>Latitude<b></td>
			<td valign='top'><b>MPH<b></td>
			<td valign='top'><b>Location<b></td>
		</tr>
	";
	
	$feed_type=1;
	$lim_txt="";
	$adder="";
	if($truck_id == 0)	
	{
		$adder=" and mrr_processed=0";
		$lim_txt=" limit 10000";
	}
	
	$last_truck=0;
	$last_long="";
	$last_lat="";
	$last_speed=0.00;
	$last_location="";
	$cntr=0;
			
	$sql = "
		select geotab_datafeed_log.*,
			trucks.id as my_truck_id,
			trucks.name_truck as my_truck_name
			
		from ".mrr_find_log_database_name()."geotab_datafeed_log
			left join trucks on trucks.geotab_device_id=geotab_datafeed_log.device_id
		where geotab_datafeed_log.feed_type='".sql_friendly($feed_type) ."'
			and geotab_datafeed_log.linedate_added>='".$last_processed."'
			".($truck_id > 0 ? " and trucks.id='".(int) $truck_id."'" : "")."
			".$adder."
		order by geotab_datafeed_log.device_id asc,
			geotab_datafeed_log.linedate_added asc,
			geotab_datafeed_log.date_from asc,
			geotab_datafeed_log.id asc
		".$lim_txt."
	";

	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		if($last_truck!=$row['my_truck_id'] && $last_truck > 0)
		{
			$last_location=mrr_update_geotab_truck_location($last_truck,$last_long,$last_lat);
			
			$sqlu="
				update trucks set 
					geotab_last_longitude='".sql_friendly($last_long) ."',
					geotab_last_latitude='".sql_friendly($last_lat) ."',
					geotab_truck_speed='".sql_friendly($last_speed) ."',
					geotab_gps_date=NOW(),
					geotab_current_location='".sql_friendly($last_location) ."'
				where id='".sql_friendly($last_truck)."'
			";
			if($save_local > 0)		simple_query($sqlu);	
		}		
		
		$tab.="
			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
				<td valign='top'>".$row['id']."</td>
				<td valign='top'>".$row['linedate_added']."</td>
				<td valign='top'>".$row['geotab_id']."</td>
				<td valign='top'>".$row['device_id']."</td>				
				<td valign='top'><a href='admin_trucks.php?id=".$row['my_truck_id']."' target='_blank'>".trim($row['my_truck_name'])."</a></td>
				<td valign='top'>".trim($row['date_from'])."</td>
				<td valign='top'>".trim($row['longitude'])."</td>
				<td valign='top'>".trim($row['latitude'])."</td>
				<td valign='top'>".$row['speed_mph']."</td>
				<td valign='top'>".$last_location."</td>
			</tr>
		";
		
		$last_truck=$row['my_truck_id'];
		$last_long=trim($row['longitude']);
		$last_lat=trim($row['latitude']);
		$last_speed=$row['speed_mph'];
		
		$sqlu="
				update ".mrr_find_log_database_name()."geotab_datafeed_log set 
					mrr_processed=1
				where id='".sql_friendly($row['id'])."'
		";
		if($save_local > 0)		simple_query($sqlu);
			
		$cntr++;
	}
	//update the last truck...
	$last_location=mrr_update_geotab_truck_location($last_truck,$last_long,$last_lat);
	
	$sqlu="
		update trucks set 
			geotab_last_longitude='".sql_friendly($last_long) ."',
			geotab_last_latitude='".sql_friendly($last_lat) ."',
			geotab_truck_speed='".sql_friendly($last_speed) ."',
			geotab_gps_date=NOW(),
			geotab_current_location='".sql_friendly($last_location) ."'
		where id='".sql_friendly($last_truck)."'
	";
	if($save_local > 0)		simple_query($sqlu);	
	
	
	$sqlu2="update defaults set xvalue_string='".date("Y-m-d H:i:s",time())."' where xname='geotab_process_last_date'";
     if($save_local > 0 && $truck_id==0)		simple_query($sqlu2);
	
	
	$tab.="</table><b>".$cntr."</b> Location Points found to processed.<br>";	//Update Query: ".$sqlu.".<br>
	return $tab;
}
function mrr_update_geotab_truck_location($truck_id,$long="",$lat="")
{
	$location="";
	$long=trim($long);
	$lat=trim($lat);
	
	if($long!="" && $lat!="")
	{     	
     	$res=mrr_geotab_reverse_geocode_address_from_point($long,$lat);
          	
          $location="".$res['address_1']."; ".$res['city'].", ".$res['state']." ".$res['zip']."";
          //$gps_location="".$res['address_1']."; ".$res['city'].", ".$res['state']." ".$res['zip']."";
	}
	if($truck_id > 0)
	{
		$sqlu="
			update trucks set 
				geotab_current_location='".sql_friendly(trim($location)) ."'
			where id='".sql_friendly($truck_id)."'
		";
		simple_query($sqlu);
	}	
	return $location;	
}


function mrr_geotab_repair_maint_request($geotab_msg_id,$cd=0)
{
	$def_id=8;
     $sql="select * from ".mrr_find_log_database_name()."geotab_datafeed_log where id='".sql_friendly(trim($geotab_msg_id))."' and feed_type='".sql_friendly($def_id)."'";
	$data=simple_query($sql);		
	if($row = mysqli_fetch_array($data)) 
     {    	
     	if($cd > 0)	echo "<br>Feed ID: ".$row['id']." Saved on ".$row['linedate_added'].".<br>User=".$row['geotab_user'].".  Device=".$row['device_id'].". ".$row['date_from']."<br><b>MR:</b><br>".$row['data_body']."<br>MR ID=".$row['maint_request_id'].".";
     	
     	$request_id=$row['maint_request_id'];
     	$geotab_user=trim($row['geotab_user']);
     	$device_id=trim($row['device_id']);
     	$desc=trim($row['data_body']);
     	$truck_id=0;
     	$trailer_id=0;
     	$type="Truck";
     	$type_id=58;
     	$equip_id=0;
     	
     	$odometer=0;
		$local="";
     	
     	//get truck if not selected.
     	if($device_id=="" && $geotab_user!="")
     	{
     		$sqlx="select attached_truck_id,attached2_truck_id from drivers where geotab_use_id='".sql_friendly(trim($geotab_user))."'";
			$datax=simple_query($sqlx);		
			if($rowx = mysqli_fetch_array($datax))
			{
				$truck_id=$rowx['attached_truck_id'];	
				if($truck_id==0)		$truck_id=$rowx['attached2_truck_id'];	
				
				$sqlt="select id,name_truck,geotab_last_odometer_reading,geotab_current_location from trucks where id='".sql_friendly($truck_id)."'";
				$datat=simple_query($sqlt);		
				if($rowt = mysqli_fetch_array($datat))
				{
					$odometer=$rowt['geotab_last_odometer_reading'];
					$local=trim($rowt['geotab_current_location']);
				} 
			} 			
     	}
     	elseif($device_id!="")
     	{
     		$sqlx="select id,name_truck,geotab_last_odometer_reading,geotab_current_location from trucks where geotab_device_id='".sql_friendly(trim($device_id))."'";
			$datax=simple_query($sqlx);		
			if($rowx = mysqli_fetch_array($datax))
			{
				$truck_id=$rowx['id'];	
				$odometer=$rowx['geotab_last_odometer_reading'];
				$local=trim($rowx['geotab_current_location']);
			} 
     	}
     	     	
     	$equip_id=$truck_id;
     	
     	//now check if this is really for the trailer, not the truck.
     	$pos1=-1;
     	
     	if(substr_count($desc,"TRAILER") > 0)			$pos1=strpos($desc,"TRAILER");	
     	elseif(substr_count($desc,"trailer") > 0)		$pos1=strpos($desc,"trailer");
     	elseif(substr_count($desc,"Trailer") > 0)		$pos1=strpos($desc,"Trailer");
     	elseif(substr_count($desc,"TRL") > 0)    		$pos1=strpos($desc,"TRL");
     	elseif(substr_count($desc,"trl") > 0)    		$pos1=strpos($desc,"Trl");
     	elseif(substr_count($desc,"Trl") > 0)			$pos1=strpos($desc,"trl");
     	
     	if($pos1 >= 0)
     	{
     		$type="Trailer";
     		$type_id=59;     		
     		
     		$pos2=0;
			$pos3=0;
			if(substr_count($desc,"TRAILER ") > 0 || substr_count($desc,"TRL ") > 0 || substr_count($desc,"trailer ") > 0 || substr_count($desc,"trl ") > 0 || substr_count($desc,"Trailer ") > 0 || substr_count($desc,"Trl ") > 0)
			{
				$pos2=strpos($desc," ",($pos1+1));
				$pos3=strpos($desc," ",($pos2+1));
			}
			elseif(substr_count($desc,"TRAILER") > 0 || substr_count($desc,"TRL") > 0 || substr_count($desc,"trailer") > 0 || substr_count($desc,"trl") > 0 || substr_count($desc,"Trailer") > 0 || substr_count($desc,"Trl") > 0)
			{
				$pos2=$pos1;
				$pos3=strpos($desc,"-",($pos2+1));
				if($pos3==0 || ($pos3 - $pos2) > 8)	$pos3=strpos($desc," ",($pos2+1));
			}
			elseif(substr_count($desc,"TRAILER#") > 0 || substr_count($desc,"TRL#") > 0 || substr_count($desc,"trailer#") > 0 || substr_count($desc,"trl#") > 0 || substr_count($desc,"Trailer#") > 0 || substr_count($desc,"Trl#") > 0)
			{
				$pos2=strpos($desc,"#",($pos1+1));
				$pos3=strpos($desc,"-",($pos2+1));
				if($pos3==0 || ($pos3 - $pos2) > 8)	$pos3=strpos($desc," ",($pos2+1));
			}
						
			//$pos2=strpos($desc," ",($pos1+1));
			//$pos3=strpos($desc," ",($pos2+1));
			
			if($cd > 0)	echo "<br>Positions found: Pos1=".$pos1.", Pos2=".$pos2.", Pos3=".$pos3.".";
			
			
			if($pos3 > $pos2 && $pos2 > $pos1)
			{
				$sub=substr($desc,$pos2,($pos3 - $pos2));
								
				$trailer=trim($sub);
				$trailer=str_replace("#","",$trailer);
				$trailer=str_replace("-","",$trailer);
				
				if($cd > 0)	echo "<br>Testing Trailer Name '".$trailer."' for match...";
				
				$sql="
					select id,trailer_name
					from trailers
					where trailer_name='".sql_friendly($trailer)."'	
				";	
				$data=simple_query($sql);
				if($row = mysqli_fetch_array($data))
				{
					$trailer_id=$row['id'];
					$equip_id=$trailer_id;
					if($cd > 0)	echo "<br>Trailer Name '".$trailer."' match found (ID=".$trailer_id.").";
				}
				else
				{
					$equip_id=0;	
					$type_id=0;
				}
			}
			
			$odometer=0;
			$local="";
     	}
     	     	
     	if($cd > 0)	echo "<br>Equipment Type (".$type_id.") = ".$type.".  <br><b>ID=".$equip_id."</b> (Truck ID=".$truck_id."  | Trailer ID=".$trailer_id." ).<br>Odometer: ".$odometer.".<br>Location: ".$local.".";
     	
     	$auto_equip=0;
     	$auto_id=0;
     	if($request_id > 0)
     	{	//determine if Maint Request (MR) is already set...
     		$sqlx="
				select id,equip_type,ref_id
				from maint_requests
				where id='".sql_friendly($request_id)."'
			";	
			$datax=simple_query($sqlx);
			if($rowx = mysqli_fetch_array($datax))
			{
				$auto_equip=$rowx['equip_type'];
				$auto_id=$rowx['ref_id'];
			}
     	}
     	
     	if($request_id > 0 && ($auto_id==0 || $auto_equip==0))
     	{	//only update if the MR was not already set.
     		$sqlu="
     			update maint_requests set 
     				odometer_reading='".sql_friendly((int) $odometer)."',
     				cur_location='".sql_friendly(trim($local))."',
     				equip_type='".sql_friendly($type_id)."',
     				ref_id='".sql_friendly($equip_id)."'
     				
     			where id='".sql_friendly($request_id)."'
     		";
			simple_query($sqlu);		
     	}    	
     	
     }
     else
     {
     	if($cd > 0)	echo "<br>Feed ID not found.";	
     }
}
function mrr_auto_create_maint_request_from_geotab($device="",$driver="",$desc="",$trailer_id=0,$trailer_name="")
{
	$device=trim($device);
	$driver=trim($driver);
	$desc=trim($desc);
	
	$truck_id=0;
	$truck_name="";
	$odometer=0;
	$local="";
	
	$driver_id=0;
	$driver_name="";
	
	$is_urgent=0;
	$maint_mode=58;		//truck is default
	$equip_id=0;
	
	//$trailer_id=0;
	
	$date=date("Y-m-d H:i:s",time());
	
	if($device!="")
	{
		$sql="
			select id,name_truck,geotab_last_odometer_reading,geotab_current_location
			from trucks
			where geotab_device_id='".sql_friendly($device)."'	
		";	
		$data=simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$truck_name=trim($row['name_truck']);
			//if($cd==2)		$name=trim($row['nick_name']);
			$truck_id=$row['id'];
               $equip_id=$truck_id;
			
			$odometer=$row['geotab_last_odometer_reading'];
			$local=trim($row['geotab_current_location']);
		}
	}
	//$equip_id=$truck_id;
	
	if($driver!="")
	{
		$sql = "
			select id,name_driver_first,name_driver_last				 
			from drivers
			where geotab_use_id='".sql_friendly($driver)."'
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$driver_id=$row['id'];
			$driver_name=trim($row['name_driver_first'] ." ". $row['name_driver_last']);
		}
	}
	
	if($trailer_id > 0)
	{	
		$desc="[Trailer ".$trailer_name."] ".$desc."";
		
		$odometer=0;
		$local="";
		$equip_id=$trailer_id;
		$maint_mode=59;		//Trailer request found, so switch modes and equipment ID.
	}
	elseif($desc!="")
	{
		$pos1=-1;
     	
     	if(substr_count($desc,"TRAILER") > 0)		$pos1=strpos($desc,"TRAILER");	
     	if(substr_count($desc,"trailer") > 0)		$pos1=strpos($desc,"trailer");
     	if(substr_count($desc,"Trailer") > 0)		$pos1=strpos($desc,"Trailer");
     	if(substr_count($desc,"TRL") > 0)    		$pos1=strpos($desc,"TRL");
     	if(substr_count($desc,"trl") > 0)    		$pos1=strpos($desc,"Trl");
     	if(substr_count($desc,"Trl") > 0)		$pos1=strpos($desc,"trl");
		
		if($pos1>=0 || substr_count($desc,"TRAILER") > 0 || substr_count($desc,"TRL") > 0 || substr_count($desc,"trailer") > 0 || substr_count($desc,"trl") > 0 || substr_count($desc,"Trailer") > 0 || substr_count($desc,"Trl") > 0)
		{
			$pos2=0;
			$pos3=0;
			if(substr_count($desc,"TRAILER ") > 0 || substr_count($desc,"TRL ") > 0 || substr_count($desc,"trailer ") > 0 || substr_count($desc,"trl ") > 0 || substr_count($desc,"Trailer ") > 0 || substr_count($desc,"Trl ") > 0)
			{
				$pos2=strpos($desc," ",$pos1);
				$pos3=strpos($desc," ",($pos2 + 1));
			}
			elseif(substr_count($desc,"TRAILER") > 0 || substr_count($desc,"TRL") > 0 || substr_count($desc,"trailer") > 0 || substr_count($desc,"trl") > 0 || substr_count($desc,"Trailer") > 0 || substr_count($desc,"Trl") > 0)
			{
				$pos2=$pos1;
				$pos3=strpos($desc,"-",($pos2 + 1));
				if($pos3==0 || ($pos3 - $pos2) > 8)	$pos3=strpos($desc," ",$pos2);
			}
			elseif(substr_count($desc,"TRAILER#") > 0 || substr_count($desc,"TRL#") > 0 || substr_count($desc,"trailer#") > 0 || substr_count($desc,"trl#") > 0 || substr_count($desc,"Trailer#") > 0 || substr_count($desc,"Trl#") > 0)
			{
				$pos2=strpos($desc,"#",$pos1);
				$pos3=strpos($desc,"-",($pos2 + 1));
				if($pos3==0 || ($pos3 - $pos2) > 8)	$pos3=strpos($desc," ",$pos2);
			}
						
			//$pos2=strpos($desc," ",$pos1);
			//$pos3=strpos($desc," ",($pos2 + 1));
			
			if($pos3 > $pos2 && $pos2 > $pos1)
			{
				$sub=substr($desc,$pos2,($pos3 - $pos2));
				
				$trailer=str_replace("#","",$trailer);
				$trailer=str_replace("-","",$trailer);
				$trailer=trim($sub);
								
				$sql="
					select id,trailer_name
					from trailers
					where trailer_name='".sql_friendly($trailer)."'	
				";	
				$data=simple_query($sql);
				if($row = mysqli_fetch_array($data))
				{
					$trailer_id=$row['id'];
					
					$odometer=0;
					$local="";
					$equip_id=$trailer_id;
					$maint_mode=59;		//Trailer request found, so switch modes and equipment ID.
				}
			}
		}		
	}
	
	$new_req_id=mrr_auto_create_maint_request($maint_mode,$equip_id,trim($desc)." --".trim($driver_name),$is_urgent,$odometer,trim($date),trim($local));		
     
     return $new_req_id;
}
function mrr_find_truck_device_form_geotab_user_id($user_id)
{
     $device_id="";
     $driver_id=0;
     $truck_id=0;
     
     $sql="
          select id
          from drivers
          where geotab_use_id='".sql_friendly($user_id)."'	
     ";
     $data=simple_query($sql);
     if($row = mysqli_fetch_array($data))
     {
          $driver_id=$row['id'];
     }
     
     if($driver_id==0)        return "N";          //No driver match, exit function now.
     
     
     //first look for current ACTIVE dispatch... and use truck if found.
     $sql="
          select truck_id
          from trucks_log
          where driver_id='".sql_friendly($driver_id)."'
               and linedate_pickup_eta>='".date("Y-m-d",time())." 00:00:00'
               and linedate_pickup_eta<='".date("Y-m-d",time())." 23:59:59'
               and dispatch_completed=0	
          order by linedate_pickup_eta asc
     ";
     $data=simple_query($sql);
     if($row = mysqli_fetch_array($data))
     {
          $truck_id=$row['truck_id'];
     }
     
     //Second attempt, find any dispatch for today...even if it is completed....
     if($truck_id==0)
     {
          $sql="
               select truck_id
               from trucks_log
               where driver_id='".sql_friendly($driver_id)."'
                    and linedate_pickup_eta>='".date("Y-m-d",time())." 00:00:00'
                    and linedate_pickup_eta<='".date("Y-m-d",time())." 23:59:59'
               order by linedate_pickup_eta asc
          ";
          $data=simple_query($sql);
          if($row = mysqli_fetch_array($data))
          {
               $truck_id=$row['truck_id'];
          }
     }
     
     //Still not found, try last truck...would be completed...only look 7 days back MAX.
     if($truck_id==0)
     {
          $sql="
               select truck_id
               from trucks_log
               where driver_id='".sql_friendly($driver_id)."'
                    and linedate_pickup_eta>='".date("Y-m-d",strtotime("-7 days",time()))." 00:00:00'
                    and dispatch_completed=1	
               order by linedate_pickup_eta desc
          ";
          $data=simple_query($sql);
          if($row = mysqli_fetch_array($data))
          {
               $truck_id=$row['truck_id'];
          }
     }
     //Still not found, try next truck...any dispatch coming soon... hopefully the truck on that dispatch is the correct truck.
     if($truck_id==0)
     {
          $sql="
               select truck_id
               from trucks_log
               where driver_id='".sql_friendly($driver_id)."'
                    and linedate_pickup_eta>='".date("Y-m-d",time())." 00:00:00'
                    and linedate_pickup_eta>='".date("Y-m-d",time())." 23:59:59'
               order by linedate_pickup_eta asc
          ";
          $data=simple_query($sql);
          if($row = mysqli_fetch_array($data))
          {
               $truck_id=$row['truck_id'];
          }
     }
     
     
     //now look up the truck device ID based on the ID
     $sql="
          select geotab_device_id
          from trucks
          where id='".sql_friendly($truck_id)."'	
     ";
     $data=simple_query($sql);
     if($row = mysqli_fetch_array($data))
     {
          $device_id=$row['geotab_device_id'];
     }
     
     if($device_id=="")       $device_id="N";          //if not found at all, set to value to kill it in calling function.
     
     return $device_id;
}

function mrr_special_ops_geotab_msg_processing2()
{
     $tab="<table cellpadding='1' cellspacing='1' border='1' width='1600'>";
     $tab.="
           <tr>
               <td valign='top'><b>Added</b></td>
               <td valign='top'><b>GeoTabID</b></td>
               <td valign='top'><b>User/Driver</b></td>
               <td valign='top'><b>Device/Truck</b></td>  
               <td valign='top'><b>New Device</b></td>    
               <td valign='top'><b>TruckID</b></td>  
               <td valign='top'><b>Truck</b></td>         
               <td valign='top'><b>Message</b></td>
           </tr>
     ";
     
     $cntr=0;
     $sql="
        select geotab_messages_received.*,
            (select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last) from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user) as driver_name,
            (select drivers.id from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user) as driver_id,
            (select trucks.name_truck from trucks where trucks.geotab_device_id=geotab_messages_received.device_id AND trucks.active>0 LIMIT 1) as truck_name,
            (select trucks.id from trucks where trucks.geotab_device_id=geotab_messages_received.device_id AND trucks.active>0 LIMIT 1) as truck_id
        from ".mrr_find_log_database_name()."geotab_messages_received 
        where geotab_messages_received.device_id='N'
             and geotab_messages_received.linedate_added >= '".date("Y-m-d",strtotime("-7 days",time()))." 00:00:00'
             and geotab_messages_received.msg_to_truck=0
        order by geotab_messages_received.linedate_added asc
     ";
     $data=simple_query($sql);
     while($row = mysqli_fetch_array($data))
     { 
          $new_device=mrr_find_truck_device_form_geotab_user_id($row['geotab_user']);
          $msg=trim($row['msg_body']);
          
          $truck_id=0;
          $truck_namr="";
     
          $sql2="
               select id, name_truck
               from trucks
               where geotab_device_id='".sql_friendly($new_device)."'	
               order by active desc,id desc
          ";
          $data2=simple_query($sql2);
          if($row2 = mysqli_fetch_array($data2))
          {
               $truck_id=$row2['id'];
               $truck_name=trim($row2['name_truck']);
          }
          
          if($row['geotab_user']=='b11' || $row['geotab_user']=='b25' || $row['geotab_user']=='b26' || 
               $row['geotab_user']=='b27' || $row['geotab_user']=='bE5' || $row['geotab_user']=='b127')
          {
               $sqlu="
                   update ".mrr_find_log_database_name()."geotab_messages_received set 
                       msg_to_truck='1'
                   where id='".$row['id']."'
               ";
               simple_query($sqlu);
               //$new_device='';
          }
          
          if($truck_id > 0)
          {
               $sqlu="
                   update ".mrr_find_log_database_name()."geotab_messages_received set 
                       device_id='".sql_friendly($new_device)."'
                   where id='".$row['id']."'
               ";
               simple_query($sqlu);
          }
     
          $tab.="
                     <tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
                         <td valign='top' nowrap>".$row['linedate_added']."</td>
                         <td valign='top'>".$row['geotab_id']."</td>
                         <td valign='top'>".(trim($row['driver_name'])!="" ? "<a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".trim($row['driver_name'])."</a>" : $row['geotab_user'])."</td>
                         <td valign='top'>".(trim($row['truck_name'])!="" ? "<a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".trim($row['truck_name'])."</a>" : $row['device_id'])."</td>       
                         <td valign='top'>".$new_device."</td>    
                         <td valign='top'>".$truck_id."</td>   
                         <td valign='top'>".$truck_name."</td>           
                         <td valign='top'>".$msg."</td>
                     </tr>
               ";
          $cntr++;
     }
     $tab.="</table><br><b>".$cntr." Message(s) found.</b>";
     
     return $tab;
}

function mrr_special_ops_geotab_msg_processing()
{
     global $defaultsarray;     
     $last_date_processed=trim($defaultsarray['last_geotab_message_processed']);
     
     $codes[0]="UNLD";         //Unloaded/Empty
     $codes[1]="LDD";          //Loaded
     $codes[2]="ARV";          //Arrive
     $codes[3]="DPT";          //Depart
     $codes[4]="TRLR-DROP";    //TRLR-DROP ---drops current trailer at current stop location
     $codes[5]="TRLR-SWAP";    //TRLR-SWAP [Trailer Name]  --drops any current trailer at current stop location and attaches trailer [Trailer Name] ...numbers only.  Ex: "TRLR-SWAP 1637676".  Current trailer will be dropped at the stop location. Added trailer could have all previous drops marked completed.
     $codes[6]="TRLR-HOOK";    //TRLR-HOOK [Trailer Name] --adds trailer... just attaches this trailer without trying to drop any.  Ex: "TRLR-HOOK 1436450".    Added trailer could have all previous drops marked completed.
     
     $tab="";
     
     $tab=mrr_special_ops_geotab_msg_processing2();
     
     $tab.="<br><hr><br><table cellpadding='1' cellspacing='1' border='1' width='1600'>";
     $tab.="
           <tr>
               <td valign='top'><b>Added</b></td>
               <td valign='top'><b>GeoTabID</b></td>
               <td valign='top'><b>User/Driver</b></td>
               <td valign='top'><b>Device/Truck</b></td>  
               <td valign='top'><b>Mode</b></td>     
               <td valign='top'><b>Load</b></td> 
               <td valign='top'><b>Dispatch</b></td> 
               <td valign='top'><b>Stop</b></td> 
               <td valign='top'><b>Trailer</b></td>              
               <td valign='top'><b>Message</b></td>
           </tr>
     ";
     
     $cntr=0;
     $sql="
        select geotab_messages_received.*,
            (select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last) from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user) as driver_name,
            (select drivers.id from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user) as driver_id,
            (select trucks.name_truck from trucks where trucks.geotab_device_id=geotab_messages_received.device_id AND trucks.active>0 LIMIT 1) as truck_name,
            (select trucks.id from trucks where trucks.geotab_device_id=geotab_messages_received.device_id AND trucks.active>0 LIMIT 1) as truck_id
        from ".mrr_find_log_database_name()."geotab_messages_received 
        where geotab_messages_received.linedate_added > '".date("Y-m-d H:i:s",strtotime($last_date_processed))."'
               and geotab_messages_received.geotab_user!='b11'
               and geotab_messages_received.geotab_user!='b25'
               and geotab_messages_received.geotab_user!='b26'
               and geotab_messages_received.geotab_user!='bE5'
               and geotab_messages_received.geotab_user!='b127'
               and geotab_messages_received.mrr_msg_done='0'
        order by geotab_messages_received.linedate_added asc
     ";
     $data=simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {
          $typer=-1;   
          $moder="";
          $flagged="";
     
          $msg=trim($row['msg_body']);
          for($i=0; $i<count($codes); $i++)
          {
               if(substr_count($msg,$codes[$i]) > 0 || substr_count(strtoupper($msg),$codes[$i]) > 0 || substr_count(strtolower($msg),$codes[$i]) > 0)
               {    //
                    $typer=$i;
               }
          }
          $stop_id=0;
          $trailer_id=0;
          $cust_id=0;
          $disp_id=0;
          $load_id=0;
     
          $load="";
          $disp="";
          $stop="";
          $trailer="";
          if($row['truck_id'] > 0)
          {    //find current/next load/dispatch/stop
               $sqlx="
                  select trucks_log.id,
                        trucks_log.customer_id,
                        (
                            select load_handler_stops.start_trailer_id 
                            from load_handler_stops 
                            where load_handler_stops.deleted=0
                                and load_handler_stops.load_handler_id=trucks_log.load_handler_id
                                and load_handler_stops.trucks_log_id=trucks_log.id
                                and (load_handler_stops.linedate_completed is NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
                            order by load_handler_stops.linedate_pickup_eta asc 
                            limit 1
                        ) as cur_trailer_id,
                        (
                            select load_handler_stops.id 
                            from load_handler_stops 
                            where load_handler_stops.deleted=0
                                and load_handler_stops.load_handler_id=trucks_log.load_handler_id
                                and load_handler_stops.trucks_log_id=trucks_log.id
                                and (load_handler_stops.linedate_completed is NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
                            order by load_handler_stops.linedate_pickup_eta asc 
                            limit 1
                        ) as cur_stop_id,
                        trucks_log.load_handler_id
                  from trucks_log 
                  where trucks_log.deleted=0
                        and trucks_log.dispatch_completed=0
                        and trucks_log.truck_id='".sql_friendly($row['truck_id'])."'
                        and (trucks_log.driver_id='".sql_friendly($row['driver_id'])."' or trucks_log.driver2_id='".sql_friendly($row['driver_id'])."')
                  order by trucks_log.linedate_pickup_eta asc
               ";
               $datax=simple_query($sqlx);
               if($rowx = mysqli_fetch_array($datax))
               {
                    $load="<a href='manage_load.php?load_id=".$rowx['load_handler_id']."' target='_blank'>".$rowx['load_handler_id']."</a>";
                    $disp="<a href='add_entry_truck.php?load_id=".$rowx['load_handler_id']."&id=".$rowx['id']."' target='_blank'>".trim($rowx['id'])."</a>";
                    $stop="".$rowx['cur_stop_id']."";
     
                    $disp_id=$rowx['id'];
                    $load_id=$rowx['load_handler_id'];     
                    $cust_id=$rowx['customer_id'];     
                    $stop_id=$rowx['cur_stop_id'];
                    $trailer_id=$rowx['cur_trailer_id'];
                    
                    if($rowx['cur_trailer_id'] > 0) 
                    {
                         $tnamer = mrr_find_quick_trailer_name($rowx['cur_trailer_id']);
                         $trailer = "<a href='admin_trailers.php?id=".$rowx['cur_trailer_id']."' target='_blank'>".$tnamer."</a>";
                    }
               }
               else
               {    //find preplan load...if determined to be necessary.
                    
               }
          }               
          
          //now use the mode
          if($typer==2)
          {
               $flagged=" color:#00CC00;";        $moder="Arrived";
               if($stop_id > 0)
               {    //mark date and time for arrival on the current stop.   But, only for the current day.
                    $sqlu="
                        update load_handler_stops set 
                            linedate_arrival=NOW() 
                        where id='".$stop_id."'
                            and linedate_pickup_eta>='".date("Y-m-d",time())." 00:00:00' 
                            and linedate_pickup_eta<='".date("Y-m-d",time())." 23:59:59'
                    ";
                    simple_query($sqlu);
               }
          }
          elseif($typer==3 || $typer==0 || $typer==1)
          {    //DPT, UNLD, and LDD all do the same thing...
               $flagged=" color:#00CC00;";        $moder="Departed";
               if($typer==0)       {    $flagged=" color:#CC0000;";        $moder="Unloaded/Empty";     }
               if($typer==1)       {    $flagged=" color:#CC0000;";        $moder="Loaded";     }
               
               if($stop_id > 0)
               {    //mark date and time for stop completed on the current stop.   But, only for the current day.
                    $sqlu="
                        update load_handler_stops set 
                            linedate_completed=NOW() 
                        where id='".$stop_id."' 
                            and linedate_pickup_eta>='".date("Y-m-d",time())." 00:00:00' 
                            and linedate_pickup_eta<='".date("Y-m-d",time())." 23:59:59'
                    ";
                    simple_query($sqlu);
                    //check to see if this is the last stop... and if so, complete the dispatch so that the next message uses the next active dispatch...
                    $sqld="
                         select count(*) as mrr_cnt 
                         from load_handler_stops 
                         where id!='".$stop_id."'
                              and deleted=0
                              and load_handler_id='".sql_friendly($load_id)."'
                              and trucks_log_id='".sql_friendly($disp_id)."'
                              and (linedate_completed is NULL or linedate_completed='0000-00-00 00:00:00')                              
                         order by linedate_pickup_eta asc
                    ";
                    $datad=simple_query($sqld);
                    if($rowd = mysqli_fetch_array($datad))
                    {
                         $more_stops=$rowd['mrr_cnt'];
                         if($more_stops==0) 
                         {    //no more stops on this dispatch... so mark dispatch as complete...
                              $sqlu="update trucks_log set dispatch_completed='1' where id='".sql_friendly($disp_id)."'";
                              simple_query($sqlu);
                         }
                    }
               }
          }
          elseif($typer==4)
          {
               $flagged=" color:#0000CC;";        $moder="TRAILER-DROP";
               if($stop_id > 0 && $trailer_id > 0)
               {    //drop the current trailer at this location... and indicate this on the stop.                    
                    $is_empty=0;
                    $city="";
                    $state="";
                    $zip="";
                    
                    //first get some stop info for the drop info...
                    $sqld="
                         select shipper_city,shipper_state,shipper_zip 
                         from load_handler_stops 
                         where id='".$stop_id."'
                    ";
                    $datad=simple_query($sqld);
                    if($rowd = mysqli_fetch_array($datad))
                    {
                         $city=trim($rowd['shipper_city']);
                         $state=trim($rowd['shipper_state']);
                         $zip=trim($rowd['shipper_zip']);
                    }
                    
                    //now drop the trailer.                    
                    $sqli="
                        insert into trailers_dropped 
                            (id,
                            trailer_id,
                            linedate_added,
                            linedate,
                            created_by_user_id, 
                            location_city,
                            location_state,
                            location_zip,
                            customer_id,
                            notes,
                            drop_completed,
                            is_empty,
                            deleted)
                        values 
                            (NULL,
                            '".sql_friendly($trailer_id)."',
                            NOW(),
                            '".date("Y-m-d",time())." 00:00:00',
                            1,
                            '".sql_friendly($city)."',
                            '".sql_friendly($state)."',
                            '".sql_friendly($zip)."',
                            '".sql_friendly($cust_id)."',
                            'Auto-dropped trailer from GeoTab Message.',
                            0,
                            '".sql_friendly($is_empty)."',                            
                            0)
                    ";
                    simple_query($sqli);
                    
                    //mark the stop to show the trailer is dropped...                    
                    $sqlu="update load_handler_stops set end_trailer_id='0' where id='".$stop_id."'";
                    simple_query($sqlu);
               }               
          }
          elseif($typer==5)
          {
               $flagged=" color:#0000CC;";        $moder="TRAILER-SWAP";
               //find the new trailer first...then complete the drop for it.
               $end_trailer_id=0;
               $test_msg=trim($msg);
               $test_msg=trim(str_replace("TRLR-SWAP","",$test_msg));
               if($test_msg!="")
               {
                    $sqld="
                         select id 
                         from trailers 
                         where deleted=0 
                            and trailer_name like '".sql_friendly($test_msg)."'
                         order by active desc,id asc
                    ";
                    $datad=simple_query($sqld);
                    if($rowd = mysqli_fetch_array($datad))
                    {
                         $end_trailer_id=trim($rowd['id']);
     
                         //completed the last drop(s) for this trailer...since it is now being attached to this stop.
                         $sqlu="update trailers_dropped set drop_completed='1' where trailer_id='".sql_friendly($end_trailer_id)."'";
                         simple_query($sqlu);
                    }
               }  
               
               if($stop_id > 0 && $trailer_id > 0 && $end_trailer_id > 0)
               {    //drop the current trailer at this location... and indicate this on the stop. Then add the new trailer to the stop.                   
                    $is_empty=0;
                    $city="";
                    $state="";
                    $zip="";
          
                    //first get some stop info for the drop info...
                    $sqld="
                         select shipper_city,shipper_state,shipper_zip 
                         from load_handler_stops 
                         where id='".$stop_id."'
                    ";
                    $datad=simple_query($sqld);
                    if($rowd = mysqli_fetch_array($datad))
                    {
                         $city=trim($rowd['shipper_city']);
                         $state=trim($rowd['shipper_state']);
                         $zip=trim($rowd['shipper_zip']);
                    }
          
                    //now drop the trailer.                    
                    $sqli="
                        insert into trailers_dropped 
                            (id,
                            trailer_id,
                            linedate_added,
                            linedate,
                            created_by_user_id, 
                            location_city,
                            location_state,
                            location_zip,
                            customer_id,
                            notes,
                            drop_completed,
                            is_empty,
                            deleted)
                        values 
                            (NULL,
                            '".sql_friendly($trailer_id)."',
                            NOW(),
                            '".date("Y-m-d",time())." 00:00:00',
                            1,
                            '".sql_friendly($city)."',
                            '".sql_friendly($state)."',
                            '".sql_friendly($zip)."',
                            '".sql_friendly($cust_id)."',
                            'Auto-dropped trailer from GeoTab Message.',
                            0,
                            '".sql_friendly($is_empty)."',                            
                            0)
                    ";
                    simple_query($sqli);
          
                    //mark the stop to show the trailer is swapped with the new trailer...                    
                    $sqlu="update load_handler_stops set end_trailer_id='".sql_friendly($end_trailer_id)."' where id='".$stop_id."'";
                    simple_query($sqlu);
               }
          }
          elseif($typer==6)
          {
               $flagged=" color:#0000CC;";        $moder="TRAILER-HOOK";
               //find the new trailer first...then complete the drop for it.
               $end_trailer_id=0;
               $test_msg=trim($msg);
               $test_msg=trim(str_replace("TRLR-HOOK","",$test_msg));
               if($test_msg!="")
               {
                    $sqld="
                         select id 
                         from trailers 
                         where deleted=0 
                            and trailer_name like '".sql_friendly($test_msg)."'
                         order by active desc,id asc
                    ";
                    $datad=simple_query($sqld);
                    if($rowd = mysqli_fetch_array($datad))
                    {
                         $end_trailer_id=trim($rowd['id']);
               
                         //completed the last drop(s) for this trailer...since it is now being attached to this stop.
                         $sqlu="update trailers_dropped set drop_completed='1' where trailer_id='".sql_friendly($end_trailer_id)."'";
                         simple_query($sqlu);
                    }
               }
               
               if($stop_id > 0 && $end_trailer_id > 0)
               {    //Add the new trailer to the stop.                    
                    $sqlu="update load_handler_stops set end_trailer_id='".sql_friendly($end_trailer_id)."' where id='".$stop_id."'";
                    simple_query($sqlu);
               }
          }
     
          $sqlu="
              update ".mrr_find_log_database_name()."geotab_messages_received set 
                  mrr_msg_done='1'
              where id='".$row['id']."'
          ";
          simple_query($sqlu);
     
          $tab.="
                <tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";".$flagged."'>
                    <td valign='top' nowrap>".$row['linedate_added']."</td>
                    <td valign='top'>".$row['geotab_id']."</td>
                    <td valign='top'>".(trim($row['driver_name'])!="" ? "<a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".trim($row['driver_name'])."</a>" : $row['geotab_user'])."</td>
                    <td valign='top'>".(trim($row['truck_name'])!="" ? "<a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".trim($row['truck_name'])."</a>" : $row['device_id'])."</td>       
                    <td valign='top'>".$moder."</td>  
                    <td valign='top'>".$load."</td> 
                    <td valign='top'>".$disp."</td> 
                    <td valign='top'>".$stop."</td> 
                    <td valign='top'>".$trailer."</td>            
                    <td valign='top'>".$msg."</td>
                </tr>
          ";
          $cntr++;
     }
     $tab.="</table><br><b>".$cntr." Message(s) found.</b>"; 
          
     $sqlu="
          update defaults set 
               xvalue_string='".date("Y-m-d H:i:s",time())."'         						
          where xname='last_geotab_message_processed'				
     ";
     simple_query($sqlu);
     
     return $tab;
}

function mrr_fetch_geotab_email_hourly_updates($test_mode=0)
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
			load_handler.alt_tracking_email,
			load_handler.load_number
		from trucks_log
			left join customers on customers.id=trucks_log.customer_id
			left join drivers on drivers.id=trucks_log.driver_id
			left join trucks on trucks.id=trucks_log.truck_id
			left join trailers on trailers.id=trucks_log.trailer_id 
			left join load_handler on load_handler.id=trucks_log.load_handler_id
		where trucks_log.deleted<=0
			and load_handler.deleted<=0
			and trucks_log.dispatch_completed<=0
			and trucks.geotab_device_id!=''
			and trucks_log.linedate_pickup_eta < NOW()
			and customers.hot_load_timer>0
		order by trucks_log.linedate_pickup_eta asc,
			trucks_log.load_handler_id asc
	";		//and trucks.peoplenet_tracking>0
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
				and load_handler_stops.geofencing_arrived_sent<=0
				and load_handler_stops.geofencing_departed_sent<=0
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
					
			//check the distance by location packets...Location Mode Email...should be most accurate if polling trucks is done frequently.  Do second so that the email gets checked even after the email goes out.
			$truck_distance=0;
			$miles_distance=0;
						
			/*
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
			*/
					
     						
     		$res=mrr_find_geotab_location_of_this_truck($row['truck_id'],0);		//$poll_now=0
     		
     		//$res['truck_name']=$truck_name;
     		//$res['device_id']=$geotab_id;
     		
     		//$res['gps_location']=$gps_location;
     			
     		$truck_lat=$res['latitude'];		//$res['lat'];
     		$truck_long=$res['longitude'];	//$res['long'];
     		//$truck_age=$res['age'];
     		$truck_date=$res['date'];
     		//$truck_heading=$res['closer'];
     		$gps_location=$res['location'];	
     		$truck_speed=$res['truck_speed'];	
     		$truck_head=0;				//$res['truck_heading'];
     		
     		$truck_age="0";
     		$truck_heading="";
     		
     		//$res=mrr_peoplenet_email_processor_fetch_truck_lat_long($row['truck_id'],date("m/d/Y",strtotime($row['linedate_pickup_eta'])));
			//$truck_lat=$res['lat'];
			//$truck_long=$res['long'];
			//$truck_age=$res['age'];
			//$truck_date=$res['date'];
			//$truck_heading=$res['closer'];
			//$gps_location=$res['location'];	
			//$truck_speed=$res['truck_speed'];	
			//$truck_head=$res['truck_heading'];
			
				
			$head_mask="";		//North
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
				
				if($truck_heading=="Near" || $truck_heading=="")
				{
					if($truck_distance < ($row['hot_load_radius_arriving'] + $tolerance) && $geo_sent_arriving==0  && $geo_sent_arrived==0 && $geo_sent_departed==0)
						$send_email1="Arriving Email";
					if($truck_distance < ($row['hot_load_radius_arrived'] + $tolerance) && $geo_sent_arrived==0 && $geo_sent_departed==0)	
						$send_email1="Arrived Email";
				}
				elseif($truck_heading=="Far")
				{					
					if($truck_distance > ($row['hot_load_radius_departed'] - $tolerance) && $geo_sent_departed==0)	
						$send_email1="Departed Email";
				}
				
				if($truck_distance < ($row['hot_load_radius_arriving'] + $tolerance) && $geo_sent_arriving==0 && $geo_sent_arrived==0 && $geo_sent_departed==0)	
					$send_email2="Arriving Email";
				if($truck_distance < ($row['hot_load_radius_arrived'] + $tolerance) && $geo_sent_arrived==0 && $geo_sent_departed==0)	
					$send_email2="Arrived Email";
				if($truck_distance > ($row['hot_load_radius_departed'] - $tolerance) && $geo_sent_departed==0 && $geo_sent_arrived>0 )	
					$send_email2="Departed Email";
			}
						
			$mrr_speed="".$truck_speed." MPH";
			$mrr_heading="";	// heading ".$head_mask."
			//$mrr_location="Current Status of Truck: ".$mrr_speed."".$mrr_heading.". Truck is about ".number_format($miles_distance,2)." miles away.  Approximate Location: ".$gps_location."...";	
			
			$mrr_location="
					<br>
					<br>Truck ".$row['name_truck']." is".$mrr_heading." at ".$mrr_speed.". 
					 
					<br>Approximate Location: ".$gps_location."...
			";	//<br>Truck is about ".number_format($miles_distance,2)." miles away. 
			
						
			//determine newest GPS point (in minutes) to see if we need a new GPS point for this truck.
			//$gps_minutes_old=$truck_age1;
			//$gps_aging_date=$truck_date1;
			//if($truck_age < $truck_age1)	
			//{
				$gps_minutes_old=$truck_age;
				$gps_aging_date=$truck_date;
			//}
			//$gps_current_date="N/A";
			
			$gps_current_date=date("m/d/Y H:i");
			
			//if last GPS point is too old....get a current location for this truck only...
			if($gps_minutes_old > $gps_too_old_minutes && 1==2)
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
					<br>Truck ".$row['name_truck']." current speed is ".$mrr_speed.". 
					 
					<br>Approximate Location: ".$gps_location."...
				";	//<br>Truck is about ".number_format($miles_distance,2)." miles away. 			//".$mrr_heading." at
							
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
						
			
			if(trim($row['alt_tracking_email'])!="")	$tolist=trim($row['alt_tracking_email']);
			
			if($test_mode > 0)			$tolist=$defaultsarray['special_email_monitor'];
			
					
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
    				if($test_mode==0)	simple_query($sqlu);
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

function mrr_geotab_link_class_by_date($load_id=0,$dispatch_id=0)
{
	$classer="peoplenet_link_not_sent";
	//$classer="peoplenet_link_sent";
	
	$sql="";		
	if($load_id > 0 && $dispatch_id==0)
	{	//preplan loads...skip dispatch...
		$sql="
			select load_handler.geotab_load_msg_id,
				load_handler.geotab_load_id,
				load_handler_stops.geotab_stop_msg_id 
			from load_handler_stops
				left join load_handler on load_handler.id=load_handler_stops.load_handler_id 
			where load_handler.id='".sql_friendly($load_id)."'
				and load_handler.deleted<=0
				and load_handler_stops.deleted<=0 	
				and load_handler_stops.trucks_log_id<=0
				and (load_handler_stops.geotab_stop_msg_id!='' or load_handler.geotab_load_id!=''  or geotab_load_msg_id!='')	
			limit 1	
		";
	}
	elseif($load_id > 0 && $dispatch_id > 0)
	{
		$sql="
			select load_handler.geotab_load_msg_id,
				load_handler.geotab_load_id,
				load_handler_stops.geotab_stop_msg_id 
			from load_handler_stops
				left join load_handler on load_handler.id=load_handler_stops.load_handler_id 
			where load_handler.id='".sql_friendly($load_id)."'
				and load_handler.deleted<=0
				and load_handler_stops.deleted<=0 	
				and load_handler_stops.trucks_log_id='".sql_friendly($dispatch_id)."'
				and load_handler_stops.geotab_stop_msg_id!=''
			limit 1
		";
	}
	else
	{
		return $classer;
	}
	$data = simple_query($sql);
	$mn=mysqli_num_rows($data);
	if($row=mysqli_fetch_array($data))
	{
		$classer="peoplenet_link_sent";
		
		//$dated=date("m/d/Y H:i",strtotime($row['linedate']));
		//$updated=$row['updated_later'];
		//if($updated > 0)	$classer="peoplenet_link_update";
	}	
		
	return $classer;
}


function mrr_get_messages_by_truck_mini_geotab($truck_id, $date_from, $date_to, $driver_id=0, $load_id=0,$dispatch_id=0 ,$disp_section=0,$mini_mode=0)
{	//messages pulled from packets
	$mcntr=0; 
	$tab="";	
	$tab2="";	//mini mode version.
	
	//$offset_gmt=mrr_gmt_offset_val();
	
	$device_id=mrr_find_geotab_truck_id_by_id($truck_id);
		
	$date_range_msg_history=" and geotab_messages_sent.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and geotab_messages_sent.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	$mrr_adder="";
	//if($load_id > 0)		$mrr_adder.=" and geotab_messages_sent.load_id='".sql_friendly($load_id)."'";
	//if($dispatch_id > 0)	$mrr_adder.=" and geotab_messages_sent.dispatch_id='".sql_friendly($dispatch_id)."'";
	//if($driver_id > 0)	$mrr_adder.=" and geotab_messages_sent.driver_id='".sql_friendly($driver_id)."'";
		
	$date_range_msg_history2=" and geotab_messages_received.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and geotab_messages_received.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	$mrr_adder2="";
	//if($load_id > 0)		$mrr_adder2.=" and geotab_messages_received.load_id='".sql_friendly($load_id)."'";
	//if($dispatch_id > 0)	$mrr_adder2.=" and geotab_messages_received.dispatch_id='".sql_friendly($dispatch_id)."'";
	//if($driver_id > 0)	$mrr_adder2.=" and geotab_messages_received.driver_id='".sql_friendly($driver_id)."'";
	
	$date_range_msg_history3=" and twilio_call_log.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and twilio_call_log.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	$mrr_adder3="";
	if($load_id > 0)		$mrr_adder3.=" and twilio_call_log.load_id='".sql_friendly($load_id)."'";
	if($dispatch_id > 0)	$mrr_adder3.=" and twilio_call_log.disp_id='".sql_friendly($dispatch_id)."'";
	if($driver_id > 0)		$mrr_adder3.=" and twilio_call_log.driver_id='".sql_friendly($driver_id)."'";
		
	$mydate=date("Y-m-d");		//today...
	$driver=mrr_find_pn_truck_drivers($truck_id,$mydate); 	
	
	$sql3 = "
		select geotab_messages_sent.id as my_id,
			geotab_messages_sent.device_id as my_device,
			geotab_messages_sent.linedate_added as my_date,
			geotab_messages_sent.message_sent as my_msg,	
			'' as my_typer,		
			'Sent' as mrr_mode
			
		from ".mrr_find_log_database_name()."geotab_messages_sent
		where geotab_messages_sent.device_id='".sql_friendly($device_id) ."'
			".$date_range_msg_history."
			".$mrr_adder."
			
		union all 		
			
		select geotab_messages_received.id,
			geotab_messages_received.device_id,
			geotab_messages_received.linedate_added,
			geotab_messages_received.msg_body,	
			geotab_messages_received.msg_type,			
			'Received'
			
		from ".mrr_find_log_database_name()."geotab_messages_received
		where geotab_messages_received.device_id='".sql_friendly($device_id) ."'
			".$date_range_msg_history2."
			".$mrr_adder2."
			
		order by my_date desc
	";
		/*
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
		*/
	$data3 = simple_query($sql3);
	//$mn3=mysqli_num_rows($data3);	
	
	$closer="<div style='float:right' class='mrr_link_like_on' onClick='mrr_close_pn_msg_displayer(".$dispatch_id.");'>Close</div>";
		
	if($load_id > 0 && $dispatch_id==0)		$closer="<div style='float:right' class='mrr_link_like_on' onClick='mrr_close_preplan_msg_displayer(".$load_id.");'>Close</div>";
	if($load_id==0 && $dispatch_id==0)			$closer="<div style='float:right' class='mrr_link_like_on' onClick='mrr_close_truck_msg_displayer(".$truck_id.");'>Close</div>";
	
	$tab.="
		<div style='color:#000000; width:750px; min-width:750px; max-width:750px;'>
				GEOTAB Quick Message Reply: <span id='pn_sent_message_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."'>&nbsp;</span><br>
				<textarea id='truck_msg_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."' wrap='virtual' cols='80' rows='5'></textarea>
				<br><br>
				".$closer."
				<input type='button' id='truck_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."_button' value='Send Message' onClick='mrr_send_quick_msg_form_geotab(".$truck_id.",".$load_id.",".$dispatch_id.",".$disp_section.");'>
		<br>	
		<div style='color:#000000; width:750px; min-width:750px; max-width:750px; height:300px; overflow-x:scroll; overflow-y:scroll;'>
			<table width='700' border='0' cellpadding='0' cellspacing='0'>
			<tr>
				<td valign='top' width='75'><b>Truck</b></td>
				<td valign='top' width='75'><b>Device</b></td>
				<td valign='top' width='75'><b>Type</b></td>
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
				<input type='button' id='truck_".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."_button' value='Send Message' onClick='mrr_send_quick_msg_form_geotab(".$truck_id.",".$load_id.",".$dispatch_id.",".$disp_section.");'>
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
			
			$body=trim($row3['my_msg']);
			
			$device=trim($row3['my_device']);
			$typer=trim($row3['my_typer']);
			
			
			$tab.="<tr>
						<td valign='top'><span title='".$row3['my_id']."'>".$row3['mrr_mode']."</span></td>
						<td valign='top'>".$device."</td>
						<td valign='top'>".$typer."</span></td>
						<td valign='top'>".$use_date."</td>
						<td valign='top'><textarea rows='3' cols='45' wrap='virtual' disabled>".$body."</textarea></td>
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

function mrr_pull_all_active_geofencing_rows_alt_geotab($mode=0)
{
	global $new_style_path;
	global $defaultsarray;
	
	$peoplenet_geofencing_mph=(int) trim($defaultsarray['peoplenet_geofencing_mph']);
	
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
			trucks.geotab_device_id,
			trucks.geotab_current_location,
			trucks.geotab_last_latitude,
			trucks.geotab_last_longitude,		
			trucks.geotab_truck_speed,	
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
			load_handler_stops.latitude,
			load_handler_stops.longitude,			
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
			
		where load_handler.deleted<=0  
			and load_handler_stops.deleted<=0 	
			and trucks_log.deleted<=0	
			and trucks.deleted<=0
			and drivers.deleted<=0
			and customers.deleted<=0
			and trucks.geotab_device_id=''
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null)
			and trucks_log.dispatch_completed<=0     			
			
		order by load_handler_stops.linedate_pickup_eta asc,
				load_handler_stops.load_handler_id asc,     				
				load_handler_stops.trucks_log_id asc,
				load_handler_stops.id asc						
	";
	$datax=simple_query($sqlx);
	while($rowx=mysqli_fetch_array($datax))
	{
		$due_date=$rowx['stop_pickup_eta'];
		
		$stop_lat=trim($rowx['latitude']);
		$stop_long=trim($rowx['longitude']);
		
		$geotab_last_latitude=trim($rowx['geotab_last_latitude']);
		$geotab_last_longitude=trim($rowx['geotab_last_longitude']);
		$geotab_device_id=trim($rowx['geotab_device_id']);
		$geotab_current_location=trim($rowx['geotab_current_location']);
		$geotab_truck_speed=number_format( ((int) $rowx['geotab_truck_speed'] / 1.6) ,2);
		
		     		
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
				and trucks_log_notes.deleted <= 0
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
			trucks.geotab_device_id,
			trucks.geotab_gps_date,
			trucks.geotab_current_location,
			trucks.geotab_last_latitude,
			trucks.geotab_last_longitude,	
			trucks.geotab_truck_speed,		
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
			
		where load_handler.deleted<=0  
			and load_handler_stops.deleted<=0 	
			and trucks_log.deleted<=0	
			and trucks.deleted<=0
			and drivers.deleted<=0
			and customers.deleted<=0
			and trucks.geotab_device_id!=''
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
			and trucks_log.dispatch_completed<=0  			
			
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
				and trucks_log_notes.deleted <= 0
		";
		$datant = simple_query($sqlnt);
		if($rownt=mysqli_fetch_array($datant))
		{
			$nt_cntr=$rownt['mycntr'];	
		}
		
		$pro_miles_dist=$rowx['pro_miles_dist'];
		$pro_miles_eta=$rowx['pro_miles_eta'];
		$pro_miles_due=$rowx['pro_miles_due'];
		
		$stop_lat=trim($rowx['latitude']);
		$stop_long=trim($rowx['longitude']);
		
		$geotab_last_latitude=trim($rowx['geotab_last_latitude']);
		$geotab_last_longitude=trim($rowx['geotab_last_longitude']);
		$geotab_device_id=trim($rowx['geotab_device_id']);
		$geotab_current_location=trim($rowx['geotab_current_location']);
		$geotab_truck_speed=number_format( ((int) $rowx['geotab_truck_speed'] / 1.6) ,2);
		$geotab_gps_date=$rowx['geotab_gps_date'];
		
		
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
			
			if(trim($geotab_device_id)=="")
			{	//not linked via GeoTab (which should be an issue in itself), so try to find it.		
     			$res=mrr_find_geotab_location_of_this_truck($rowx['truckid'],0);		//$poll_now=0
     		
          		//$res['truck_name']=$truck_name;
          		//$res['device_id']=$geotab_id;
          		
          		//$res['gps_location']=$gps_location;
          			
          		$truck_lat=$res['latitude'];		//$res['lat'];
          		$truck_long=$res['longitude'];	//$res['long'];
          		//$truck_age=$res['age'];
          		$truck_date=$res['date'];
          		//$truck_heading=$res['closer'];
          		$gps_location=$res['location'];	
          		$truck_speed=$res['truck_speed'];	
          		$truck_head=0;				//$res['truck_heading'];
          				
     			//$res=mrr_peoplenet_email_processor_fetch_truck_lat_long_alt1($rowx['truckid'],date("m/d/Y",strtotime($rowx['dispatch_pickup_eta'])));
     			//$truck_lat=$res['lat'];
     			//$truck_long=$res['long'];
     			//$truck_age=$res['age'];
     			//$truck_date=$res['date'];
     			//$truck_heading=$res['closer'];
     			//$gps_location=$res['location'];	
     			//$truck_speed=$res['truck_speed'];	
     			//$truck_head=$res['truck_heading'];
			}
			else
			{	//already stored in truck...don't need to look it up by API	$geotab_device_id is set.
				$truck_lat=$geotab_last_latitude=trim($rowx['geotab_last_latitude']);
				$truck_long=$geotab_last_longitude=trim($rowx['geotab_last_longitude']);
				$gps_location=$geotab_current_location=trim($rowx['geotab_current_location']);
				$truck_speed=$geotab_truck_speed=number_format( ((int) $rowx['geotab_truck_speed'] / 1.6) ,2);
				$truck_date=$geotab_gps_date;
			}
			
			if($geotab_device_id!="" && $geotab_last_latitude!="" && $geotab_last_longitude!="")
			{	//GeoTab is use, so uses the curetn truck location to find distance away.
				$truck_lat=$geotab_last_latitude;
				$truck_long=$geotab_last_longitude;
				$gps_location=$geotab_current_location;				
				$truck_speed=$geotab_truck_speed;	
				
				$pro_miles_dist=mrr_distance_between_gps_points($stop_lat,$stop_long,$geotab_last_latitude,$geotab_last_longitude,0);
				$truck_distance=$pro_miles_dist;	
															
				$pro_miles_eta=0;
				if($peoplenet_geofencing_mph > 0)	
				{
					$pro_miles_eta=$pro_miles_dist / $peoplenet_geofencing_mph;
				}	
			}
			
			if(!isset($truck_head)) $truck_head = 0;
			
			$head_mask="North";
          	if($truck_head == 1)		$head_mask="NE";
          	if($truck_head == 2)		$head_mask="East";
          	if($truck_head == 3)		$head_mask="SE";
          	if($truck_head == 4)		$head_mask="South";
          	if($truck_head == 5)		$head_mask="SW";
          	if($truck_head == 6)		$head_mask="West";
          	if($truck_head == 7)		$head_mask="NW";		
          	
          	$head_mask="";	
			
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
          			$tab2.=			"<span>".date("m/d/Y H:i",strtotime($tracking_date))." --- ".$linker1."</span>";
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
	$tab2.=			"<br><span>Geofence Legend [GeoTab]</span>";    			
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