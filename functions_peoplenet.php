<?
//Functions for PeopleNet interface.

function mrr_peoplenet_duel_msg_archiver($days=0)
{	//archive messages > DAYS old 
	if($days>0)
	{
		$sql = "
			update ".mrr_find_log_database_name()."truck_tracking_msg_history set
				archived='1'
			where linedate_created < DATE_SUB(NOW(),INTERVAL ".$days." DAY)
				and user_id_read > 0
				and archived = 0
		";     		
		simple_query($sql);	
		
		$sql = "
			update ".mrr_find_log_database_name()."truck_tracking_messages set
				archived='1'
			where linedate < DATE_SUB(NOW(),INTERVAL ".$days." DAY)
				and archived = 0
		";     		
		simple_query($sql); 
	}
}
function mrr_find_pn_truck_drivers_from_elog($truck_id,$mydate,$cd=0)
{
	$res['load_id']=0;
	$res['dispatch_id']=0;
	$res['driver_id_1']=0;
	$res['driver_id_2']=0;
	$res['driver_name_1']="";
	$res['driver_name_2']="";	
	
	$last_driver=0;
	$dcntr=0;
	$used=0;
	$drivers="";
	
	$sql="
		select driver_elog_entries.driver_id,
			drivers.name_driver_first,
			drivers.name_driver_last
		from ".mrr_find_log_database_name()."driver_elog_entries
			left join drivers on drivers.id=driver_elog_entries.driver_id
		where driver_elog_entries.truck_id='".sql_friendly($truck_id)."'
			and driver_elog_entries.linedate_added>='".$mydate." 00:00:00'
		order by driver_elog_entries.linedate_added desc,
			driver_elog_entries.id desc
	";
	$data=simple_query($sql);		
	while($row = mysqli_fetch_array($data)) 
	{				
		if($dcntr==0)
		{
			$res['driver_id_1']=$row['driver_id'];
			$res['driver_name_1']="".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."";
			
			$drivers="".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."";
			$used=1;
		}
		elseif($dcntr > 0 && $last_driver!=$row['driver_id'] && $used==1)
		{
			$res['driver_id_2']=$row['driver_id'];
			$res['driver_name_2']="".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."";
			
			$drivers.=" and ".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."";
			$used++;
		}
		
		$last_driver=$row['driver_id'];
		$dcntr++;
	}
	if($cd==1)
	{
		return $res;
	}
	return $drivers;	
}

function mrr_find_pn_drivers_truck_from_elog($driver_id,$mydate,$cd=0)
{
	$truck_id=0;
	
	if($driver_id==0)		return $truck_id;	
			
	$sql="
		select truck_id
		from ".mrr_find_log_database_name()."driver_elog_entries
		where driver_id='".sql_friendly($driver_id)."'
			and linedate_added>='".$mydate." 00:00:00'
		order by linedate_added desc,id desc
	";
	$data=simple_query($sql);		
	if($row = mysqli_fetch_array($data)) 
	{	
		$truck_id=$row['truck_id'];
	}
	return $truck_id;	
}
function mrr_find_pn_drivers_truck_from_elog_driver($truck_id,$mydate,$cd=0)
{
	$driver_id=0;
	
	if($truck_id==0)		return $driver_id;	
			
	$sql="
		select driver_id
		from ".mrr_find_log_database_name()."driver_elog_entries
		where truck_id='".sql_friendly($truck_id)."'
			and linedate_added>='".$mydate." 00:00:00'
		order by linedate_added desc,id desc
	";
	$data=simple_query($sql);		
	if($row = mysqli_fetch_array($data)) 
	{	
		$driver_id=$row['driver_id'];
	}
	return $driver_id;	
}
	

function mrr_cancel_this_load_dispatch($dispatch,$preplan=0)
{
	$adder=" and preplan_use_load_id='0'";		
	if($preplan>0)
	{
		$adder=" and preplan_use_load_id='".sql_friendly($preplan) ."'";	
	}
	
	$sql = "
		update ".mrr_find_log_database_name()."truck_tracking_dispatches set
			canceled='1'
		where dispatch_id='".sql_friendly($dispatch) ."'
			".$adder."
	";		
	simple_query($sql);	
}

function mrr_find_truck_tracking_dispatch_record($dispatch,$preplan=0)
{		
	$res['peoplenet_id']=0;
	$res['stops']=0;
	
	$adder=" and preplan_use_load_id='0'";		
	if($preplan>0)
	{
		$adder=" and preplan_use_load_id='".sql_friendly($preplan) ."'";	
	}
	
	$sql = "
		select peoplenet_id,
			stops 
		from ".mrr_find_log_database_name()."truck_tracking_dispatches
		where dispatch_id='".sql_friendly($dispatch) ."'
			".$adder."
			and canceled='0'
		order by id desc 
		limit 1
	";
	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$res['peoplenet_id']=$row['peoplenet_id'];
		$res['stops']=$row['stops'];	
	}		
	
	return $res;	
}
function mrr_find_truck_tracking_dispatch_record_all($truck,$dispatch=0,$preplan=0,$use_date="")
{		
	$days=30;
	$labeler="Dispatches for this Truck: (Last ".$days." Days)";
	
	if($use_date=="")		$use_date="".date("m/d/Y",strtotime("-".$days." days",time()))."";
	
	$adder=" and truck_tracking_dispatches.linedate_added>='".date("Y-m-d",strtotime($use_date))."'";		
	if($preplan>0)
	{
		$adder=" and truck_tracking_dispatches.preplan_use_load_id='".sql_friendly($preplan) ."'";	
		$labeler="Load ".$preplan." Dispatches for this truck:";
	}
	$adder2="";		
	if($dispatch>0)
	{
		$adder2=" and truck_tracking_dispatches.dispatch_id='".sql_friendly($dispatch) ."'";	
		$labeler="Dispatch ".$dispatch." for this truck:";
	}
	
	$table="
		<table cellpadding='0' cellspacing='0' border='0'>
		<tr>
			<td valign='top' colspan='6'><b>".$labeler."</b></td>
		</tr>
		<tr>
			<td valign='top' width='100'><b>Date</b></td>
			<td valign='top' width='100'><b>PeopleNetID</b></td>
			<td valign='top' width='100' align='right'><b>LoadID</b></td>
			<td valign='top' width='100' align='right'><b>DispatchID</b></td>
			<td valign='top' width='50' align='right'><b>Stops</b></td>
			<td valign='top'><b>&nbsp;Note</b></td>
		</tr>     		
	";
			
	$cntr=0;
	
	$sql = "
		select truck_tracking_dispatches.*,
			(select trucks_log.load_handler_id from trucks_log where trucks_log.id=truck_tracking_dispatches.dispatch_id) as xload_id
		from ".mrr_find_log_database_name()."truck_tracking_dispatches
		where truck_tracking_dispatches.truck_id='".sql_friendly($truck)."'
			".$adder."
			".$adder2."				
		order by truck_tracking_dispatches.linedate_added desc 
	";
	
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{			
		$load_link="";
		$disp_link="";
		$sent_xml="";
		
		$xload_id=$row['dispatch_id'];
		if($row['xload_id'] > 0)			$xload_id=$row['xload_id'];
		
		if($row['preplan_use_load_id'] > 0)
		{
			$load_link="<a href='manage_load.php?load_id=".$row['dispatch_id']."' target='_blank'>".$row['dispatch_id']."</a>";
			$disp_link="(PREPLAN)";
			$sent_xml=mrr_truck_tracking_dispatch_xml_fetch($row['linedate_added'],$row['dispatch_id'],0);
		}
		else
		{
			$load_link="<a href='manage_load.php?load_id=".$xload_id."' target='_blank'>".$xload_id."</a>";
			$disp_link="<a href='add_entry_truck.php?load_id=".$xload_id."&id=".$row['dispatch_id']."' target='_blank'>".$row['dispatch_id']."</a>";
			$sent_xml=mrr_truck_tracking_dispatch_xml_fetch($row['linedate_added'],$xload_id,$row['dispatch_id']);
		}
		
		$table.="
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'><span class='mrr_link_like_on' onClick='mrr_toggle_xml_string(".$row['id'].");'>".date("m/d/Y H:i", strtotime($row['linedate_added']))."</span></td>
				<td valign='top'>".$row['peoplenet_id']."</td>
				<td valign='top' align='right'>".$load_link."</td>
				<td valign='top' align='right'>".$disp_link."</td>
				<td valign='top' align='right'>".$row['stops']."</td>
				<td valign='top'>&nbsp;".($row['canceled'] > 0 ? "Canceled" : "")."</td>
			</tr>	
			<tr class='".($cntr%2==0 ? "even" : "odd")." xml_details xml_string_".$row['id']."'>
				<td valign='top' colspan='6'>".$sent_xml."</td>
			</tr>		
		";
		$cntr++;
	}	
	$table.="</table>";	
	
	return $table;	
}
function mrr_truck_tracking_dispatch_xml_fetch($date,$load=0,$dispatch=0)
{
	$section="";
	
	$adder="";
	if($load > 0)			$adder.=" and (load_id='".sql_friendly($load)."' or load_id=0)";
	if($dispatch > 0)		$adder.=" and (dispatch_id='".sql_friendly($dispatch)."' or dispatch_id=0)";
	
	$sql="
		select xml_string 
		from ".mrr_find_log_database_name()."truck_tracking_dispatch_xml
		where linedate_added>='".sql_friendly($date)."'
			".$adder."
		order by linedate_added asc
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$section=trim($row['xml_string']);
		$section=str_replace("<pnet_dispatch>","",$section);
		$section=str_replace("</pnet_dispatch>","",$section);
		
		$section=str_replace("<![CDATA[","",$section);
		$section=str_replace("]]>","",$section);
		
		$section=str_replace("><","><br><",$section);
					
		//$section=str_replace("?","",$section);
		//$section=str_replace("</","<br></",$section);
		//$section=str_replace("/>","/><br>",$section);
		
		$section=str_replace("<","[",$section);
		$section=str_replace(">","]",$section);
		
		//$section=str_replace("<","&lt;",$section);
		//$section=str_replace(">","&gt;",$section);
		
		$pos1=0;
		if(substr_count($section,"[vehicle_number]") > 0)			$pos1=strpos($section,"[vehicle_number]");
		
		$tmp=substr($section,$pos1);
		
		$tmp=str_replace("[br]","<br>",$tmp);
		$tmp=str_replace("<br><br>","<br>",$tmp);
		$tmp=str_replace("<br><br>","<br>",$tmp);
		$tmp=str_replace("<br><br>","<br>",$tmp);
		
		$tmp=str_replace("["," [<b>",$tmp);
		$tmp=str_replace("]","</b>] ",$tmp);
		
		$section="".$tmp."";		//<pre></pre>
		
		//$section=strip_tags($section,"<br>");
		/*
		<?xml version='1.0' encoding='ISO-8859-1'?>
		<!DOCTYPE pnet_dispatch PUBLIC '-//PeopleNet//pnet_dispatch' 'http://open.pfmlogin.com/dtd/pnet_dispatch.dtd'>
		<pnet_dispatch>
			<cid>3577</cid>
			<pw><![CDATA[35con77]]></pw>
			<vehicle_number><![CDATA[141683]]></vehicle_number>
			<deliver>now</deliver>
			<dispatch_name><![CDATA[Load 41003: Truck 141683: Trailer 96806 Vented: Miles 0 + 0DH:  Cust:East Coast Transport]]></dispatch_name>
			<dispatch_description><![CDATA[From La Vergne To Mt Juliet.  Load No:649426 PU:MTJ-94771 Instr:]]></dispatch_description>
			<dispatch_userdata><![CDATA[Load 41003: Dispatch 39906]]></dispatch_userdata>
			<trip_data>
				<trip_start_time><![CDATA[11/21/2014 13:00]]></trip_start_time>
				<call_on_start/>
				<call_on_end/>
				<enable_auto_start/>
				<disable_driver_end/>
			</trip_data>
			<stop>
				<stop_head>
					<stop_userdata><![CDATA[Stop 1: Trailer 96806 Vented: Shipper]]></stop_userdata>
					<custom_stop>
						<name><![CDATA[Conard Terminal]]></name>
						<description><![CDATA[Ph 615-213-2270; 216 Parthenon Blvd, La Vergne, TN 37086. .]]></description>
						<latitude><![CDATA[36.02]]></latitude>
						<longitude><![CDATA[-86.592]]></longitude>
					</custom_stop>
					<sequenced/>
				</stop_head>
				<advanced_actions>
					<arriving_action>
						<action_general>
							<radius_feet>36960</radius_feet>
							<occur_by><![CDATA[11/21/2014 13:00]]></occur_by>
							<call_on_occur/>
							<disp_message_on_late/>
						</action_general>
					</arriving_action>
					<arrived_action>
						<action_general>
							<radius_feet>15840</radius_feet>
							<occur_by><![CDATA[11/21/2014 13:00]]></occur_by>
							<call_on_occur/>
							<disp_message_on_late/>
						</action_general>
						<driver_negative_guf/>
					</arrived_action>
					<departed_action>
						<action_general>
							<radius_feet>26400</radius_feet>
							<occur_by><![CDATA[11/21/2014 14:00]]></occur_by>
							<call_on_occur/>
							<disp_message_on_late/>
						</action_general>
						<driver_negative_guf/>
					</departed_action>
				</advanced_actions>
			</stop>
		</pnet_dispatch>			
		*/
		
		
	}
	else
	{
		$section=$sql;	
	}
	return $section;
}

function mrr_peoplenet_truck_selector($field,$pre=0)
{
	$selbox="<select name='".$field."' id='".$field."' style='width:150px;'>";	
	$sel="";		if($pre==0)		$sel=" selected";	
	$selbox.="<option value='0'".$sel.">All Trucks</option>";
	$sel="";		if($pre==-1)		$sel=" selected";	
	$selbox.="<option value='-1'".$sel.">Pick From List</option>";
		 
	$sql = "
		select *
		from trucks
		where deleted = 0
			and peoplenet_tracking=1
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
function mrr_peoplenet_truck_grouper($field,$pre)
{	//pre is the group of selected trucks...
	$selbox="<div style='width:600px; height:200px; padding:5px; margin:5px; overflow:auto; border:1px solid #ffcc00; background-color:#ffffff;'>";	
	$selbox.="<table cellpadding='0' cellspacing='0' width='100%' border='0'>";	 
	$selbox.="<tr>";
	
	$cntr=0;
	$sql = "
		select *
		from trucks
		where deleted = 0
			and peoplenet_tracking=1
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
function mrr_peoplenet_truck_array()
{	//get all trucks in the array that have peoplenet tracking....built name list with it...
	$arr[0]=0;
	$names[0]="";
	$cntr=0;
		 
	$sql = "
		select *
		from trucks
		where deleted = 0
			and peoplenet_tracking=1
		order by name_truck asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		$arr[ $cntr ]=$row['id'];
		$names[ $cntr ]=str_replace(" (Team Rate)","",trim($row['name_truck']));
		$cntr++;
	}	
	
	$res['num']=$cntr;
	$res['arr']=$arr;
	$res['names']=$names;
	return $res;	
}

function mrr_peoplenet_truck_tracking_msg_history_update($id,$track_arr)
{						 
	$res['load_id']=0;
	$res['dispatch_id']=0;
	$res['driver_id']=0;
	
	$driver_id=0;
	if($track_arr[0] > 0)
	{
		$res=mrr_find_trucks_current_driver_load_dispatch($track_arr[0]);
		$driver_id=$res['driver_id'];
	}
	$driver_id=mrr_find_pn_drivers_truck_from_elog_driver($track_arr[0],date("Y-m-d"));
			
	$sql = "
		update ".mrr_find_log_database_name()."truck_tracking_msg_history set					
			truck_id='".sql_friendly($track_arr[0]) ."',
			linedate_created='".sql_friendly($track_arr[1]) ."',
			linedate_received='".sql_friendly($track_arr[2]) ."',
			recipient_id='".sql_friendly($track_arr[3]) ."',
			recipient_name='".sql_friendly($track_arr[4]) ."',
			msn='".sql_friendly($track_arr[5]) ."',
			base_msn='".sql_friendly($track_arr[6]) ."',
			msg_type='".sql_friendly($track_arr[7]) ."',
			msg_text='".sql_friendly($track_arr[8]) ."',
			truck_name='".sql_friendly($track_arr[9]) ."',
			driver_id='".sql_friendly($driver_id) ."',	
			dispatch_id='".sql_friendly($res['dispatch_id']) ."',	
			load_id='".sql_friendly($res['load_id']) ."'
		where id='".sql_friendly($id)."'
	";		
	simple_query($sql);	
}
function mrr_peoplenet_truck_tracking_msg_history_add($track_arr,$packet=0,$form_req_text="",$form_ins_text="")
{			
	global $datasource;

	$res['load_id']=0;
	$res['dispatch_id']=0;
	$res['driver_id']=0;
	
	$driver_id=0;
	if($track_arr[0] > 0)
	{
		$res=mrr_find_trucks_current_driver_load_dispatch($track_arr[0]);
		$driver_id=$res['driver_id'];
	}
	$driver_id=mrr_find_pn_drivers_truck_from_elog_driver($track_arr[0],date("Y-m-d"));
					
	$sql = "
		insert into ".mrr_find_log_database_name()."truck_tracking_msg_history
			(id,
			linedate_added,
			packet_id,					
			truck_id,
			linedate_created,
			linedate_received,
			recipient_id,
			recipient_name,
			msn,
			base_msn,
			msg_type,
			msg_text,
			truck_name,
			driver_id,
			dispatch_id,
			load_id,
			request_text,
			insepct_text)
		values
			(NULL,
			NOW(),
			'".sql_friendly($packet) ."',				
			'".sql_friendly($track_arr[0]) ."',
			'".sql_friendly($track_arr[1]) ."',
			'".sql_friendly($track_arr[2]) ."',
			'".sql_friendly($track_arr[3]) ."',
			'".sql_friendly($track_arr[4]) ."',
			'".sql_friendly($track_arr[5]) ."',
			'".sql_friendly($track_arr[6]) ."',
			'".sql_friendly($track_arr[7]) ."',
			'".sql_friendly($track_arr[8]) ."',
			'".sql_friendly($track_arr[9]) ."',
			'".sql_friendly($driver_id) ."',	
			'".sql_friendly($res['dispatch_id']) ."',					
			'".sql_friendly($res['load_id']) ."',
			'".sql_friendly(trim($form_req_text)) ."',
			'".sql_friendly(trim($form_ins_text)) ."')
	";
	
	simple_query($sql);		
	$newid= mysqli_insert_id($datasource);
	
	return $newid;	
}

function mrr_peoplenet_odometer_reading($truck,$date_last)
{
	$odometer="";	
	$odom_date="";
		
	$sql = "
		select performx_odometer,
			linedate
		from ".mrr_find_log_database_name()."truck_tracking
		where truck_id = '".sql_friendly($truck) ."'
			and linedate <= '".date("Y-m-d",strtotime($date_last)) ." 23:59:59'
		order by linedate desc limit 1
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$odometer=$row['performx_odometer'];
		$odom_date=date("m/d/Y",strtotime($row['linedate']));
	}
	
	$res['odometer']=$odometer; 
	$res['odom_date']=$odom_date;
	return $res;    		
}

function mrr_peoplenet_truck_tracking_add($track_arr)
{
	global $datasource;

	for($x=0; $x <= 19; $x++)
	{
		if(	$track_arr[$x] == "unavailable" )		$track_arr[$x]=0;
	}
	
	$sql = "
		insert into ".mrr_find_log_database_name()."truck_tracking
			(id,
			linedate_added,
			truck_id,
			linedate,
			truck_speed,
			truck_heading,
			gps_quality,
			latitude,
			longitude,
			location,
			fix_type,
			ignition,
			gps_odometer,
			gps_rolling_odometer,
			performx_odometer,
			performx_fuel,
			performx_speed,
			performx_idle,
			serial_number)
		values
			(NULL,
			NOW(),
			'".sql_friendly($track_arr[0]) ."',
			NOW(),
			'".sql_friendly($track_arr[2]) ."',
			'".sql_friendly($track_arr[3]) ."',
			'".sql_friendly($track_arr[4]) ."',
			'".sql_friendly($track_arr[5]) ."',
			'".sql_friendly($track_arr[6]) ."',
			'".sql_friendly($track_arr[7]) ."',
			'".sql_friendly($track_arr[8]) ."',
			'".sql_friendly($track_arr[9]) ."',
			'".sql_friendly($track_arr[10]) ."',
			'".sql_friendly($track_arr[11]) ."',
			'".sql_friendly($track_arr[12]) ."',
			'".sql_friendly($track_arr[13]) ."',
			'".sql_friendly($track_arr[14]) ."',
			'".sql_friendly($track_arr[15]) ."',
			'".sql_friendly($track_arr[16]) ."')
	";
	
	simple_query($sql);		
	$newid=mysqli_insert_id($datasource);
	
	$driver_id=$track_arr[18];
	if($track_arr[0] > 0)		$driver_id=mrr_find_pn_drivers_truck_from_elog_driver($track_arr[0],date("Y-m-d"));
	
	$sql = "
		update ".mrr_find_log_database_name()."truck_tracking set
			packet_id='".sql_friendly($track_arr[17]) ."',
			driver_id='".sql_friendly($driver_id) ."',
			driver2_id='".sql_friendly($track_arr[19]) ."'
		
		where id='".sql_friendly($newid) ."'
	";		
	simple_query($sql);	
	
	return $newid;	
}
function mrr_peoplenet_truck_tracking_update($track_arr,$id)
{
	for($x=0; $x <= 19; $x++)
	{
		if(	$track_arr[$x] == "unavailable" )		$track_arr[$x]=0;
	}
	
	$driver_id=$track_arr[18];
	if($track_arr[0] > 0)		$driver_id=mrr_find_pn_drivers_truck_from_elog_driver($track_arr[0],date("Y-m-d"));
	
	$sql = "
		update ".mrr_find_log_database_name()."truck_tracking set
			truck_speed='".sql_friendly($track_arr[2]) ."',
			truck_heading='".sql_friendly($track_arr[3]) ."',
			gps_quality='".sql_friendly($track_arr[4]) ."',
			latitude='".sql_friendly($track_arr[5]) ."',
			longitude='".sql_friendly($track_arr[6]) ."',
			location='".sql_friendly($track_arr[7]) ."',
			fix_type='".sql_friendly($track_arr[8]) ."',
			ignition='".sql_friendly($track_arr[9]) ."',
			gps_odometer='".sql_friendly($track_arr[10]) ."',
			gps_rolling_odometer='".sql_friendly($track_arr[11]) ."',
			performx_odometer='".sql_friendly($track_arr[12]) ."',
			performx_fuel='".sql_friendly($track_arr[13]) ."',
			performx_speed='".sql_friendly($track_arr[14]) ."',
			performx_idle='".sql_friendly($track_arr[15]) ."',
			serial_number='".sql_friendly($track_arr[16]) ."',
			packet_id='".sql_friendly($track_arr[17]) ."',
			driver_id='".sql_friendly($driver_id) ."',
			driver2_id='".sql_friendly($track_arr[19]) ."'
		
		where id='".sql_friendly($id) ."'
	";		
	simple_query($sql);	
}

//these function are for the peoplenet_interface.php page
function mrr_peoplenet_service_selector($field,$pre="")
{	//used commands on the peoplenet_interface.php page
	$section[0]="";					$arr[0]="";						$service[0]="Select Service";
			
	$section[]="DRIVERS";				$arr[]="oi_pnet_driver_view";			$service[]="Request a list of all, active, or inactive drivers. Defaults to active drivers.";
	//$section[]="DRIVERS";				$arr[]="oi_pnet_terminal_view";		$service[]="Request a list of terminals for a specific user and company.";
     
     //$section[]="LOCATION";				$arr[]="odl";						$service[]="Forces the PeopleNet system to make a data call to a truck";
     $section[]="LOCATION";				$arr[]="loc_overview";				$service[]="Return the current location of all trucks in your fleet";
     //$section[]="LOCATION";				$arr[]="loc_onetruck";				$service[]="Return the current location of one truck in your fleet";
     $section[]="LOCATION";				$arr[]="oi_pnet_location_history";		$service[]="Return all location history data gathered since the last time a packet was loaded";

     $section[]="MESSAGING";				$arr[]="imessage_send";				$service[]="Send a message to vehicle(s) using the IMessage API";
     //$section[]="MESSAGING";				$arr[]="get_formdef";				$service[]="Return the form definition or structure of a specific form";
     //$section[]="MESSAGING";				$arr[]="oi_pnet_mes_checks";			$service[]="Return message status by packets";
     $section[]="MESSAGING";				$arr[]="oi_pnet_message_history";		$service[]="Return messages by packets using the imessage API";
     //$section[]="MESSAGING";				$arr[]="get_signature";				$service[]="Return the image of a signature";
 
     //$section[]="PERFORMX";				$arr[]="oi_pnet_get_performx";		$service[]="Return a packet of PerformX information for vehicles";
     //$section[]="PERFORMX";				$arr[]="oi_performx_driver_packet";	$service[]="Retrieve new driver PerformX records"; 

     //$section[]="PACOS";					$arr[]="pnet_dispatch";				$service[]="Create a PACOS dispatch";
     //$section[]="PACOS";					$arr[]="pnet_dispatch_status";		$service[]="Get PACOS dispatch statuses";
     //$section[]="PACOS";					$arr[]="pnet_dispatch_edit";			$service[]="Add and/or remove stops from a PACOS dispatch";
     //$section[]="PACOS";					$arr[]="pnet_geocode_address";		$service[]="Geocode the latitude/longitude position of a street address";
     //$section[]="PACOS";					$arr[]="oi_pnet_dispatch_events";		$service[]="Request PACOS dispatch events";

     //$section[]="LANDMARK MANAGEMENT";		$arr[]="pnet_landmark_add";			$service[]="Create a new landmark";
     //$section[]="LANDMARK MANAGEMENT";		$arr[]="pnet_landmark_remove";		$service[]="Remove an existing landmark";
     //$section[]="LANDMARK MANAGEMENT";		$arr[]="pnet_landmark_view";			$service[]="View one or all landmarks";

     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_add";				$service[]="Create a new vehicle group";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_remove";			$service[]="Remove an existing vehicle group";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_list";			$service[]="List existing vehicle groups";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_detail";			$service[]="View members of an existing vehicle group";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_vehicle_add";		$service[]="Add vehicle(s) to an existing vehicle group";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_vehicle_remove";	$service[]="Remove vehicle(s) from an existing vehicle group";

     //$section[]="EDRIVER LOGS";			$arr[]="duty_status";				$service[]="Make on-duty/off-duty status changes to a driver's log";
     $section[]="EDRIVER LOGS";			$arr[]="elog_dispatch_info";			$service[]="Return current driver log information for all drivers";
     //$section[]="EDRIVER LOGS";			$arr[]="elog_events";				$service[]="Return historical log events, useful for tracking driver log activity";
     //$section[]="EDRIVER LOGS";			$arr[]="oi_pnet_elog_payroll";		$service[]="Return historical information about drivers that can be used for payroll systems";

     //$section[]="MISC";					$arr[]="pnet_vehicle_type_change";		$service[]="Change the vehicle type";
     //$section[]="MISC";					$arr[]="oi_alarms_packet";			$service[]="Retrieve new alarm data";
	
	$selbox="<select name='".$field."' id='".$field."' style='width:300px;'>";	
	for($i=0;$i< count($arr); $i++)
	{
		$sel="";		if($pre==$arr[$i])		$sel=" selected";
		$selbox.="<option value='".$arr[$i]."'".$sel.">".$section[$i]." - ".$service[$i]."</option>";
	}		
	$selbox.="</select>";
	return $selbox;	
}
function mrr_peoplenet_find_data($cmd="",$truck=0,$dispatch_id=0,$message="",$packet=0,$msg_packet=0,$pn_stop_num="",$special_msg_id=0,$special_form_id=0,$trailer_id=0)
{	//command processing for peoplenet_interface.php page only... 
	//see version 2 of this function below for the peoplenet_landmarks.php page
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];	//"3577";
	$pn_pw = $defaultsarray['peoplenet_account_password'];	//"35con77";
	$pn_cid=trim($pn_cid);
	$pn_pw=trim($pn_pw);
	
	$mrr_convert_to_dst=1;
	
	//$prime_url="http://open.peoplenetonline.com/scripts/open.dll";
	$prime_url="http://open.pfmlogin.com/scripts/open.dll";
	
	$moder=2;
	
	$truck_name="";
	if($truck>0)
	{
		$sql = "
			select *
			from trucks
			where deleted = 0
				and trucks.id = '".sql_friendly($truck) ."'
			order by name_truck asc
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$truck_name=$row['name_truck'];
		}		
	}		
	$output="";
	
	if($truck==1520428)		$truck_name="1520428";
	
	
	//$output.="<div class='section_heading'>Command: ".$cmd." &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;";
	
	//if($truck_name!="")		$output.=" Truck: ".$truck_name." (ID=".$truck.")";
	
	//$output.="</div>";
	
	$url="";
	//creat URL for tracking based on command
	if($cmd=="loc_overview")	
	{
		$url="http://open.pfmlogin.com/scripts/open.dll?cid=".$pn_cid."&pw=".$pn_pw."&service=".$cmd."&compat_level=".$moder."";
		$page=mrr_peoplenet_get_file_contents($url);
		$page=strip_tags($page);
		//$output.="<div style='color:green;'>Link Used: <a href='".$url."' target='_blank'>".$url."</a></div>";
		//$output.="<div style='color:red;'>'".$page."'</div>";
		$page=str_replace("success","",$page);
		$page=str_replace("unavailable","0",$page);
		
		$page=str_replace("\t"," --- ",$page);
		$page=str_replace("\r","<br>",$page);
		$page=str_replace("\n","<br>",$page);
		$page=str_replace("\l","<br>",$page);
		//$output.="<div style='color:blue;'>'".$page."'</div>";
		
		$output.="<div style='color:green;'>Found current location of all trucks:</div>";
		
		$output.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
		$output.="
				<tr>
					<td valign='top'><b>&nbsp;</b></td>
					<td valign='top'><b>Date</b></td>
					<td valign='top' align='right' align='right'><b>MPH</b></td>
					<td valign='top' align='right' align='right'><b>&nbsp;</b></td>
					<td valign='top' align='right' colspan='3><b>GPS</b></td>
					<td valign='top' align='right'><b>&nbsp;</b></td>
					<td valign='top' align='right'><b>&nbsp;</b></td>
					<td valign='top' align='right' colspan='3'><b>GPS</b></td>
					<td valign='top' align='right' colspan='4'><b>PerformX</b></td>
				</tr>
				<tr>
					<td valign='top'><b>Truck</b></td>
					<td valign='top'><b>Time</b></td>
					<td valign='top' align='right'><b>Speed</b></td>
					<td valign='top' align='right'><b>Dir</b></td>
					<td valign='top' align='right'><b>Quality</b></td>
					<td valign='top' align='right'><b>Latitude</b></td>
					<td valign='top' align='right'><b>Longitude</b></td>
					<td valign='top'> <b>Location</b></td>
					<td valign='top' align='right'><b>Fix</b></td>
					<td valign='top' align='right'><b>Ignition</b></td>
					<td valign='top' align='right'><b>Odometer</b></td>
					<td valign='top' align='right'><b>Rolling Odom</b></td>
					<td valign='top' align='right'><b>Odom</b></td>
					<td valign='top' align='right'><b>Fuel</b></td>
					<td valign='top' align='right'><b>Speed </b></td>
					<td valign='top' align='right'><b>Idle</b></td>
				</tr>";
		
		$pieces = explode("<br>", $page);
		for($i=0; $i< count($pieces); $i++)
		{
			if($pieces[$i]!="" && $pieces[$i]!="<br>")
			{
				$output.="<tr>";
				$cntr=0;
				$track_arr[0]=0;
				
				$cols = explode(" --- ", $pieces[$i] );
				for($j=0; $j< count($cols); $j++)
				{
					if($cols[$j]!="" && $cols[$j]!=" --- ")
					{							
						$adder="";
						$adder2="";
						if($j!=0 && $j!=1 && $j!=7)
						{
							$adder=" align='right'";	
						}
						if($j==7)
						{
							$adder=" style='font-weight:bold;'";	
							$adder2=" ";
						}
													
						$output.="<td valign='top'".$adder.">".$adder2."".$cols[$j]."</td>";	
						
						$track_arr[$cntr] = trim($cols[$j]);
						
						if($j==0)
						{
							$cols[$j]=str_replace(" (Team Rate)","",$cols[$j]);
							
							$sql2 = "
                         			select id
                         			from trucks
                         			where deleted = 0
                         				and trucks.name_truck = '".sql_friendly(trim($cols[$j])) ."'
                         			order by name_truck asc
                         		";
                         		$data2 = simple_query($sql2);
                         		if($row2 = mysqli_fetch_array($data2))
                         		{
                         			$cols[$j]=$row2['id'];
                         			$track_arr[0] = $row2['id'];
                         		}	
                         		
                         		//for test truck only
                         		if(trim($cols[$j])=="1520428")
                         		{
                         			$cols[$j]="1520428";
                         			$track_arr[0] = "1520428";
                         		}
						}
						
						$cntr++;							
					}							
				}
				$track_arr[17]=0;
          		$track_arr[18]=0;
          		$track_arr[19]=0;
				
				/*
          		$track_arr[0]		//truck_id
          		$track_arr[1]		//linedate
          		$track_arr[2]		//truck_speed
          		$track_arr[3]		//truck_heading
          		$track_arr[4]		//gps_quality
          		$track_arr[5]		//latitude
          		$track_arr[6]		//longitude
          		$track_arr[7]		//location
          		$track_arr[8]		//fix_type
          		$track_arr[9]		//ignition
          		$track_arr[10]		//gps_odometer
          		$track_arr[11]		//gps_rolling_odometer
          		$track_arr[12]		//performx_odometer
          		$track_arr[13]		//performx_fuel
          		$track_arr[14]		//performx_speed
          		$track_arr[15]		//performx_idle
          		//$track_arr[16]		//serial_number
          		$track_arr[17]		//packet_number
          		$track_arr[18]		//driver_id
          		$track_arr[19]		//driver2_id
          		*/
          		if($track_arr[0] > 0 || $track_arr[0]!="")	$newid=mrr_peoplenet_truck_tracking_add($track_arr);
          		
				$output.="</tr>";	
			}	
		}
		
		$output.="</table>";
	}
	elseif($cmd=="loc_onetruck")
	{
		$url="http://open.pfmlogin.com/scripts/open.dll?cid=".$pn_cid."&pw=".$pn_pw."&service=".$cmd."&trucknum=".trim($truck_name)."&compat_level=".$moder."";
		$page=mrr_peoplenet_get_file_contents($url);
		$page=strip_tags($page);
		//$output.="<div style='color:green;'>Link Used: <a href='".$url."' target='_blank'>".$url."</a></div>";
		//$output.="<div style='color:red;'>'".$page."'</div>";			
		
		$page=str_replace("success","",$page);
		$page=str_replace("unavailable","0",$page);
		
		$page=str_replace("\t"," --- ",$page);
		$page=str_replace("\r","<br>",$page);
		$page=str_replace("\n","<br>",$page);
		$page=str_replace("\l","<br>",$page);
		//$output.="<div style='color:blue;'>'".$page."'</div>";
		
		$output.="<div style='color:green;'>Found current location of truck:</div>";
		
		$output.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
		$output.="
				<tr>
					<td valign='top'><b>&nbsp;</b></td>
					<td valign='top'><b>Date</b></td>
					<td valign='top' align='right' align='right'><b>MPH</b></td>
					<td valign='top' align='right' align='right'><b>&nbsp;</b></td>
					<td valign='top' align='right' colspan='3><b>GPS</b></td>
					<td valign='top' align='right'><b>&nbsp;</b></td>
					<td valign='top' align='right'><b>&nbsp;</b></td>
					<td valign='top' align='right' colspan='3'><b>GPS</b></td>
					<td valign='top' align='right' colspan='4'><b>PerformX</b></td>
				</tr>
				<tr>
					<td valign='top'><b>Truck</b></td>
					<td valign='top'><b>Time</b></td>
					<td valign='top' align='right'><b>Speed</b></td>
					<td valign='top' align='right'><b>Dir</b></td>
					<td valign='top' align='right'><b>Quality</b></td>
					<td valign='top' align='right'><b>Latitude</b></td>
					<td valign='top' align='right'><b>Longitude</b></td>
					<td valign='top'> <b>Location</b></td>
					<td valign='top' align='right'><b>Fix</b></td>
					<td valign='top' align='right'><b>Ignition</b></td>
					<td valign='top' align='right'><b>Odometer</b></td>
					<td valign='top' align='right'><b>Rolling Odom</b></td>
					<td valign='top' align='right'><b>Odom</b></td>
					<td valign='top' align='right'><b>Fuel</b></td>
					<td valign='top' align='right'><b>Speed </b></td>
					<td valign='top' align='right'><b>Idle</b></td>
				</tr>";
		
		$pieces = explode("<br>", $page);
		for($i=0; $i< count($pieces); $i++)
		{
			if($pieces[$i]!="" && $pieces[$i]!="<br>")
			{
				$output.="<tr>";
				$cntr=0;
				$track_arr[0]=0;
				
				$cols = explode(" --- ", $pieces[$i] );
				for($j=0; $j< count($cols); $j++)
				{
					if($cols[$j]!="" && $cols[$j]!=" --- ")
					{							
						$adder="";
						$adder2="";
						if($j!=0 && $j!=1 && $j!=7)
						{
							$adder=" align='right'";	
						}
						if($j==7)
						{
							$adder=" style='font-weight:bold;'";
							$adder2=" ";	
						}
						
						$output.="<td valign='top'".$adder.">".$adder2."".$cols[$j]."</td>";		
						
						$track_arr[$cntr] = trim($cols[$j]);
						
						if($j==0)
						{
							$cols[$j]=str_replace(" (Team Rate)","",$cols[$j]);
							
							$sql2 = "
                         			select id
                         			from trucks
                         			where deleted = 0
                         				and trucks.name_truck = '".sql_friendly(trim($cols[$j])) ."'
                         			order by name_truck asc
                         		";
                         		$data2 = simple_query($sql2);
                         		if($row2 = mysqli_fetch_array($data2))
                         		{
                         			$cols[$j]=$row2['id'];
                         			$track_arr[0] = $row2['id'];
                         		}	
                         		
                         		//for test truck only
                         		if(trim($cols[$j])=="1520428")
                         		{
                         			$cols[$j]="1520428";
                         			$track_arr[0] = "1520428";
                         		}
						}
						
						$cntr++;							
					}							
				}
				$track_arr[17]=0;
          		$track_arr[18]=0;
          		$track_arr[19]=0;
				
				/*
          		$track_arr[0]		//truck_id
          		$track_arr[1]		//linedate
          		$track_arr[2]		//truck_speed
          		$track_arr[3]		//truck_heading
          		$track_arr[4]		//gps_quality
          		$track_arr[5]		//latitude
          		$track_arr[6]		//longitude
          		$track_arr[7]		//location
          		$track_arr[8]		//fix_type
          		$track_arr[9]		//ignition
          		$track_arr[10]		//gps_odometer
          		$track_arr[11]		//gps_rolling_odometer
          		$track_arr[12]		//performx_odometer
          		$track_arr[13]		//performx_fuel
          		$track_arr[14]		//performx_speed
          		$track_arr[15]		//performx_idle
          		//$track_arr[16]	//serial_number
          		$track_arr[17]		//packet_number
          		$track_arr[18]		//driver_id
          		$track_arr[19]		//driver2_id
          		*/
          		if($track_arr[0] > 0 || $track_arr[0]!="")	$newid=mrr_peoplenet_truck_tracking_add($track_arr);
          		
				$output.="</tr>";	
			}	
		}
		
		$output.="</table>";
				
	}
	elseif($cmd=="imessage_send")
	{
		//real processing
		$truck_name=str_replace(" (Team Rate)","",$truck_name);
		$xml=mrr_peoplenet_form_message($truck_name,$message,0,1);				//$truck,$message,$urgent=0,$delivery=0,$force_it=0,$force_mode=0
					
		//&#x0D;&#x0A;  XML version of \r\n
		$message=str_replace(CHR(10),"&#x0D;",$message);
		$message=str_replace(chr(13),"&#x0A;",$message);
		$message=str_replace("\r","&#x0D;",$message);
		$message=str_replace("\n","&#x0A;",$message);
		$message=str_replace("<br>","&#x0D; &#x0A;",$message);
					
		if($special_form_id==126268)
		{	//PN Trailer Registration Form
										//$truck,$message,$urgent=0,$delivery=0,$force_it=0,$force_mode=0,$trailer=0
			$xml=mrr_peoplenet_form_id_message($truck_name,$message,0,1,1,0,$special_form_id,$trailer_id);
		}
		if($special_msg_id > 0)
		{
			$xml=mrr_peoplenet_form_message($truck_name,$message,0,1,1,2);		//($truck,$message,$urgent=0,$delivery=0,$force_it=0,$force_mode=0)							
		}			
					
		//processing for sample link
		$not_raw_xml=mrr_peoplenet_form_message_not_raw($truck_name,$message);	//$truck,$message,$urgent=0,$delivery=0,$force_it=0,$force_mode=0
		$not_raw_xml=htmlentities($not_raw_xml);
		$url="".$prime_url."?service=".$cmd."&amp;xml=".$not_raw_xml."";
							
		//now send through Curl and 
		$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd);		
		
		$page2=trim($xml);
		
		$sent_text="Not Sent";			
		$page=strip_tags($page);	
		if(substr_count($page,"success") > 0)
		{
			$page=str_replace("success","",$page);	
			
			$mess_num=trim($page);				
			$sent_text="Message Sent: ID=".$mess_num."";	
			$page="";
			
			$sending=mrr_peoplenet_store_message($message,$truck);		//record of message to be sent	
			$_SESSION['peoplenet_new_msg_id']=$sending;		
		}			
		
		$output.="<div style='color:green;'>".$sent_text."".$page."</div>";
		
		mrr_peoplenet_use_the_force_call($truck_name);
	}
	elseif($cmd=="oi_pnet_message_history")
	{
		$msg_packet=(int)$msg_packet;
		$found_packet=0;
		
		$url="http://open.pfmlogin.com/scripts/oi_pnet_message_history.dll?cid=".$pn_cid."&pw=".$pn_pw."&packet_id=".$msg_packet."";
		$page=mrr_peoplenet_get_file_contents($url);
		
		if(substr_count($page,"<more_data/>") > 0)
		{	//if packet is not empty...save data to table.
			
			$page=str_replace("<packet_id>","Packet (<b>",$page);
			$page=str_replace("</packet_id>","</b>)<br>",$page);
			//$page=str_replace("<more_data>","<more_data><br>Start here 1:<br>",$page);
			//$page=str_replace("<more_data>","</more_data><br>Start here 2:<br>",$page);
			$page=str_replace("<more_data/>","<more_data/><br>Start here 3:<br>",$page);
			
			$get_pack_num="";
			if(substr_count($page,"<br>Start here 3:<br>") > 0)
			{
				$pose=strpos($page,"<br>Start here 3:<br>");
				
				$get_pack_num=substr($page,0,($pose+21));
				$get_pack_num=str_replace("<br>Start here 3:<br>","",$get_pack_num);
				$get_pack_num=strip_tags($get_pack_num);
				
				$get_pack_num=str_replace("Packet (","",$get_pack_num);
				$get_pack_num=str_replace(")","",$get_pack_num);
				$get_pack_num=str_replace("-","",$get_pack_num);
				$get_pack_num=str_replace(" ","",$get_pack_num);
				$get_pack_num=trim($get_pack_num);
				
				$tmp_page=substr($page,($pose+21));
				$page=trim($tmp_page);
			}
			
			$form_req_text="";
			$form_ins_text="";
			$form_id=0;
			
			$page=str_replace("<form_id>125685</form_id>","<form_id>126373</form_id>",$page);
			
			$form_count=0;
			while(substr_count($page,"<form_id>126373</form_id>") > 0 && $form_count < 100)	
			{
				$form_id=125685;		//Repair Request Form
				$form_id=126373;		//new verision with the trailer.
				
				//pull out the request form....
				$form_page="";
				$fpos1=strpos($page,"<formdata>");
				$fpos2=strpos($page,"</formdata>",$fpos1);
				if($fpos1 > 0 && $fpos2 > 0 && $fpos2 > $fpos1)
				{
					$fpos2+=11;
					$form_page=substr($page,$fpos1,($fpos2 - $fpos1));
					
					$page=str_replace($form_page,"<freeform_message>Repair Request Form sent.</freeform_message>",$page);
				}     				
				$form_req_text=$form_page;
				
				$sqli="
					insert into ".mrr_find_log_database_name()."pn_request_forms
						(id,
						linedate_added,
						processed,
						form_req_text,
						form_ins_text,
						form_id,
						truck_id,
						driver_name)
					values
						(NULL,
						NOW(),
						0,
						'".sql_friendly(trim($form_req_text))."',
						'',
						'".sql_friendly($form_id)."',
						0,
						'')
				";
				simple_query($sqli);     				
				
				$form_count++;
			}
			
			$form_count=0;
			while(substr_count($page,"<form_id>113688</form_id>") > 0 && $form_count < 100)	
			{
				$form_id=113688;		//Inspection.
				     				
				//pull out the request form....
				$form_page="";
				$fpos1=strpos($page,"<formdata>");
				$fpos2=strpos($page,"</formdata>",$fpos1);
				if($fpos1 > 0 && $fpos2 > 0 && $fpos2 > $fpos1)
				{
					$fpos2+=11;
					$form_page=substr($page,$fpos1,($fpos2 - $fpos1));
					
					$page=str_replace($form_page,"<freeform_message>Inspection Request sent.</freeform_message>",$page);
				}
				     				
				$form_ins_text=$form_page;
				     				
				$sqli="
					insert into ".mrr_find_log_database_name()."pn_request_forms
						(id,
						linedate_added,
						processed,
						form_req_text,
						form_ins_text,
						form_id,
						truck_id,
						driver_name)
					values
						(NULL,
						NOW(),
						0,
						'',
						'".sql_friendly(trim($form_ins_text))."',     						
						'".sql_friendly($form_id)."',
						0,
						'')
				";
				simple_query($sqli);
				
				$form_count++;
			}
			/*
			$page=str_replace("<formdata>","{formdata}",$page);   				$page=str_replace("</formdata>","{/formdata}",$page);
			
			$page=str_replace("<form_id>","{{",$page);	     				$page=str_replace("</form_id>","}}",$page);
			
			$page=str_replace("<im_field>","((",$page);	     				$page=str_replace("</im_field>","))",$page);
			
			$page=str_replace("<field_number>","...--...",$page);				$page=str_replace("</field_number>","",$page);
			$page=str_replace("<empty_at_start>","...--...",$page);			$page=str_replace("</empty_at_start>","",$page);
			$page=str_replace("<driver_modified>","...--...",$page);			$page=str_replace("</driver_modified>","",$page);
			$page=str_replace("<data>","",$page);							$page=str_replace("</data>","",$page);
			$page=str_replace("<data_numeric>","...--...",$page);				$page=str_replace("</data_numeric>","",$page);
			$page=str_replace("<data_text>","...--...",$page);				$page=str_replace("</data_text>","",$page);
			$page=str_replace("<data_multiple-choice>","...--...",$page);		$page=str_replace("</data_multiple-choice>","",$page);
			$page=str_replace("<mc_choicenum>","",$page);					$page=str_replace("</mc_choicenum>",":",$page);
			$page=str_replace("<mc_choicetext>","",$page);					$page=str_replace("</mc_choicetext>","",$page);
			$page=str_replace("<data_password>","...--...",$page);				$page=str_replace("</data_password>","",$page);
			$page=str_replace("<data_time>","...--...",$page);				$page=str_replace("</data_time>","",$page);
			$page=str_replace("<data_date-time>","...--...",$page);			$page=str_replace("</data_date-time>","",$page);
			$page=str_replace("<data_auto_drivername>","...--...",$page);		$page=str_replace("</data_auto_drivername>","",$page);
			$page=str_replace("<data_auto_location>","...--...",$page);			$page=str_replace("</data_auto_location>","",$page);
			$page=str_replace("<data_auto_latlong>","...--...",$page);			$page=str_replace("</data_auto_latlong>","",$page);
			$page=str_replace("<latitude>","",$page);						$page=str_replace("</latitude>",",",$page);
			$page=str_replace("<longitude>","",$page);						$page=str_replace("</longitude>","",$page);
			$page=str_replace("<data_auto_odometer>","...--...",$page);			$page=str_replace("</data_auto_odometer>","",$page);
			$page=str_replace("<data_auto_odometer_plus_gps>","...--...",$page);	$page=str_replace("</data_auto_odometer_plus_gps>","",$page);
			$page=str_replace("<data_performx_odometer>","",$page);			$page=str_replace("</data_performx_odometer>",":",$page);
			$page=str_replace("<data_gps_odometer>","...--...",$page);			$page=str_replace("</data_gps_odometer>","",$page);
			$page=str_replace("<data_sigcap>","...--...",$page);				$page=str_replace("</data_sigcap>","",$page);
			$page=str_replace("<data_barcode>","...--...",$page);				$page=str_replace("</data_barcode>","",$page);
			$page=str_replace("<bc_type>","",$page);						$page=str_replace("</bc_type>"," - ",$page);
			$page=str_replace("<bc_type_description>","",$page);				$page=str_replace("</bc_type_description>",":",$page);
			$page=str_replace("<bc_edited>","",$page);						$page=str_replace("</bc_edited>",":",$page);
			$page=str_replace("<bc_data>","",$page);						$page=str_replace("</bc_data>","",$page);
			$page=str_replace("<data_auto_fuel>","...--...",$page);			$page=str_replace("</data_auto_fuel>","",$page);
			$page=str_replace("<data_numeric-enhanced>","...--...",$page);		$page=str_replace("</data_numeric-enhanced>","",$page);
			$page=str_replace("<num_formatted>","",$page);					$page=str_replace("</num_formatted>",":(",$page);
			$page=str_replace("<num_raw>","",$page);						$page=str_replace("</num_raw>",")",$page);
			$page=str_replace("<data_date>","...--...",$page);				$page=str_replace("</data_date>","",$page);
			$page=str_replace("<data_auto_date-time>","...--...",$page);		$page=str_replace("</data_auto_date-time>","",$page);
			$page=str_replace("<data_image_ref>","...--...",$page);			$page=str_replace("</data_image_ref>","",$page);
			$page=str_replace("<data_image_date>","",$page);					$page=str_replace("</data_image_date>"," - ",$page);
			$page=str_replace("<data_image_transid>","",$page);				$page=str_replace("</data_image_transid>"," - ",$page);
			$page=str_replace("<data_image_name>","",$page);					$page=str_replace("</data_image_name>",":(",$page);
			$page=str_replace("<data_image_mimetype>","",$page);				$page=str_replace("</data_image_mimetype>",")",$page);
			//$page=str_replace("<>","...--...",$page);						$page=str_replace("</>","",$page);
			*/
			$page=str_replace("</","[[/",$page);
			$page=str_replace("<","[[=",$page);
			$page=str_replace(">","]",$page);    			
						
			$page=str_replace("[[=imessage]","<br>[[=imessage]",$page);
			
			$page=str_replace('?xml version="1.0" encoding="ISO-8859-1"?',"",$page);
			$page=str_replace('!DOCTYPE pnet_message_history_packet_response PUBLIC "-//PeopleNet//pnet_message_history_packet_response" "http://open.pfmlogin.com/dtd/pnet_message_history_packet_response.dtd"',"",$page);
			$page=str_replace("pnet_message_history_packet_response","",$page);
			$page=str_replace("more_data","",$page);
			
			$page=str_replace("][","] --- [",$page);
			$page=str_replace("[[=imessage] --- [","[[=imessage][",$page);
			
			//clear out empty sections
			$page=str_replace("[[/]","",$page);
			$page=str_replace("[[=]","",$page);
			$page=str_replace("[[=/]","",$page);
			
			
			$page=str_replace("[[/","</",$page);
			$page=str_replace("[[=","<",$page);
			$page=str_replace("]",">",$page);
						
			$page=strip_tags($page,"<br><b>");
			
			$output.="<div style='color:blue;'>Next Message Packet=".$get_pack_num."</div>";
			//$output.="<div style='color:blue;'>".$page."</div>";
			
			$output.="<div style='color:green;'>Found History Records for packet number ".$msg_packet.":</div>";
			
			$output.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
			
			$output.="     					
					<tr>
						<td valign='top'><b>Truck</b></td>
						<td valign='top'><b>Created</b></td>
						<td valign='top'><b>Received</b></td>
						<td valign='top'><b>RecipientID</b></td>
						<td valign='top'><b>RecipientName</b></td>
						<td valign='top'><b>MSN</b></td>
						<td valign='top'><b>BaseMSN</b></td>
						<td valign='top'><b>MsgType</b></td>
						<td valign='top'><b>Message</b></td>
					</tr>
					";
			
			$pieces = explode("<br>", $page);
			for($i=0; $i< count($pieces); $i++)
			{
				if($pieces[$i]!="" && $pieces[$i]!="<br>")
				{
					$output.="<tr>";
					$cntr=0;
					$track_arr[0]=0;
					
					$found_packet=1;
					
					$cols = explode(" --- ", $pieces[$i] );
					for($j=0; $j< count($cols); $j++)
					{
						if($cols[$j]!="" && $cols[$j]!=" --- ")
						{							
							$adder="";
							$adder2="";
							     							
							$output.="<td valign='top'".$adder.">".$adder2."".$cols[$j]."</td>";		
							
							$track_arr[$cntr] = trim($cols[$j]);
							
							if($j==0)
							{
								$cols[$j]=str_replace(" (Team Rate)","",$cols[$j]);
								
								$sql2 = "
                              			select id
                              			from trucks
                              			where deleted = 0
                              				and trucks.name_truck = '".sql_friendly(trim($cols[$j])) ."'
                              			order by name_truck asc
                              		";
                              		$data2 = simple_query($sql2);
                              		if($row2 = mysqli_fetch_array($data2))
                              		{
                              			$track_arr[9] = trim($cols[$j]);
                              			$cols[$j]=$row2['id'];
                              			$track_arr[0] = $row2['id'];                                   			
                              		}	
                              		
                              		//for test truck only
                              		if(trim($cols[$j])=="1520428")
                              		{
                              			$track_arr[9] = "1520428";
                              			$cols[$j]="1520428";
                              			$track_arr[0] = "1520428";                                   			
                              		}
							}
							
							//time_zone_adjuster
							if($cntr==2 && $mrr_convert_to_dst > 0)
							{
								$track_arr[2]=date("Y-m-d H:i:s",strtotime("1 hour",strtotime($track_arr[2])));
							}
							
							$cntr++;							
						}							
					}
					                    		
               		$useid=mrr_find_this_truck_tracking_msg_history($track_arr[0],$track_arr[9],$track_arr[2],$track_arr[3]);
               		if($useid>0)
               		{
               			$track_arr[1]=date("Y-m-d H:i:s",strtotime($track_arr[1]));
               			$track_arr[2]=date("Y-m-d H:i:s",strtotime($track_arr[2]));							
               			
               			mrr_peoplenet_truck_tracking_msg_history_update($useid,$track_arr);	
               		}
               		elseif($track_arr[0] > 0 )	
               		{
               			$track_arr[1]=date("Y-m-d H:i:s",strtotime($track_arr[1]));
               			$track_arr[2]=date("Y-m-d H:i:s",strtotime($track_arr[2]));							
						
               			$newid=mrr_peoplenet_truck_tracking_msg_history_add($track_arr,$msg_packet,$form_req_text,$form_ins_text);
					}
					
					$form_id=0;
					$form_req_text="";
					$form_ins_text="";
					
					$output.="</tr>";	
				}	
			}			
			$output.="</table>";	
			
			if( $get_pack_num > $msg_packet)
			{
				
				$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes)
				values
					(NULL,
					NOW(),
					'".sql_friendly($msg_packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'0',
					'".sql_friendly($get_pack_num)."',
					'Updated: oi_pnet_message_history: ".sql_friendly($_SERVER['REQUEST_URI'])."')
				";
				simple_query($sql);
				
			}
			else
			{
				$output.="<div style='color:green;'>Message Packet ".$msg_packet." is not available at this time.</div>";	
			}
		}
		$output.="<div style='color:green;'>Packet ".$msg_packet." scanned, next packet is ".($msg_packet + 1).". ".date("m/d/Y H:i:s").".</div>";	
		if($found_packet==1)
		{
			$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes)
				values
					(NULL,
					NOW(),
					'".sql_friendly($msg_packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'0',
					'".sql_friendly(($msg_packet+1))."',
					'Updated: oi_pnet_message_history: ".sql_friendly($_SERVER['REQUEST_URI'])."')
				";
				simple_query($sql);
		}	
		
	}
	elseif($cmd=="oi_pnet_location_history")
	{
		$packet=(int)$packet;	
		$found_packet=0;		
		
		$url="http://open.pfmlogin.com/scripts/oi_pnet_location_history.dll?cid=".$pn_cid."&pw=".$pn_pw."&packet_id=".$packet."";
		
		$page=mrr_peoplenet_get_file_contents($url);
		
		if(substr_count($page,"<more_data/>") > 0)
		{	//if packet is not empty...save data to table.
			
			$page=str_replace("<packet_id>","Packet (<b>",$page);
			$page=str_replace("</packet_id>","</b>)<br>",$page);
			//$page=str_replace("<more_data>","<more_data><br>Start here 1:<br>",$page);
			//$page=str_replace("<more_data>","</more_data><br>Start here 2:<br>",$page);
			$page=str_replace("<more_data/>","<more_data/><br>Start here 3:<br>",$page);
			
			$get_pack_num="";
			if(substr_count($page,"<br>Start here 3:<br>") > 0)
			{
				$pose=strpos($page,"<br>Start here 3:<br>");
				
				$get_pack_num=substr($page,0,($pose+21));
				$get_pack_num=str_replace("<br>Start here 3:<br>","",$get_pack_num);
				$get_pack_num=strip_tags($get_pack_num);
				
				$get_pack_num=str_replace("Packet (","",$get_pack_num);
				$get_pack_num=str_replace(")","",$get_pack_num);
				$get_pack_num=str_replace("-","",$get_pack_num);
				$get_pack_num=str_replace(" ","",$get_pack_num);
				$get_pack_num=trim($get_pack_num);
				
				$tmp_page=substr($page,($pose+21));
				$page=trim($tmp_page);
			}
			
			$page=str_replace("</","[[/",$page);
			$page=str_replace("<","[[=",$page);
			$page=str_replace(">","]",$page);
						
			$page=str_replace("[[=loc_history]","<br>[[=loc_history]",$page);
			
			$page=str_replace('?xml version="1.0" encoding="ISO-8859-1"?',"",$page);
			$page=str_replace('!DOCTYPE pnet_loc_history_packet PUBLIC "-//PeopleNet//pnet_loc_history_packet" "http://open.pfmlogin.com/dtd/pnet_loc_history_packet.dtd"',"",$page);
			$page=str_replace("pnet_loc_history_packet","",$page);
			$page=str_replace("more_data","",$page);
			
			$page=str_replace("][","] --- [",$page);
			$page=str_replace("[[=loc_history] --- [","[[=loc_history][",$page);
			
			//clear out empty sections
			$page=str_replace("[[/]","",$page);
			$page=str_replace("[[=]","",$page);
			$page=str_replace("[[=/]","",$page);
			
			
			$page=str_replace("[[/","</",$page);
			$page=str_replace("[[=","<",$page);
			$page=str_replace("]",">",$page);
						
			$page=strip_tags($page,"<br><b>");
			
			$output.="<div style='color:blue;'>Next Packet=".$get_pack_num."</div>";
			//$output.="<div style='color:blue;'>".$page."</div>";
			
			$output.="<div style='color:green;'>Found History Records for packet number ".$packet.":</div>";
			
			$output.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
			$output.="
					<tr>
						<td valign='top'><b>&nbsp;</b></td>
						<td valign='top'><b>Date</b></td>
						<td valign='top' align='right' align='right'><b>MPH</b></td>
						<td valign='top' align='right' align='right'><b>&nbsp;</b></td>
						<td valign='top' align='right' colspan='3><b>GPS</b></td>
						<td valign='top' align='right'><b>&nbsp;</b></td>
						<td valign='top' align='right'><b>&nbsp;</b></td>
						<td valign='top' align='right' colspan='3'><b>GPS</b></td>
						<td valign='top' align='right' colspan='4'><b>PerformX</b></td>
					</tr>
					<tr>
						<td valign='top'><b>Truck</b></td>
						<td valign='top'><b>Time</b></td>
						<td valign='top' align='right'><b>Speed</b></td>
						<td valign='top' align='right'><b>Dir</b></td>
						<td valign='top' align='right'><b>Quality</b></td>
						<td valign='top' align='right'><b>Latitude</b></td>
						<td valign='top' align='right'><b>Longitude</b></td>
						<td valign='top'> <b>Location</b></td>
						<td valign='top' align='right'><b>Fix</b></td>
						<td valign='top' align='right'><b>Ignition</b></td>
						<td valign='top' align='right'><b>Odometer</b></td>
						<td valign='top' align='right'><b>Rolling Odom</b></td>
						<td valign='top' align='right'><b>Odom</b></td>
						<td valign='top' align='right'><b>Fuel</b></td>
						<td valign='top' align='right'><b>Speed </b></td>
						<td valign='top' align='right'><b>Idle</b></td>
					</tr>";
			
			$pieces = explode("<br>", $page);
			for($i=0; $i< count($pieces); $i++)
			{
				if($pieces[$i]!="" && $pieces[$i]!="<br>")
				{
					$output.="<tr>";
					$cntr=0;
					$track_arr[0]=0;
					
					$found_packet=1;
					
					$cols = explode(" --- ", $pieces[$i] );
					for($j=0; $j< count($cols); $j++)
					{
						if($cols[$j]!="" && $cols[$j]!=" --- ")
						{							
							$adder="";
							$adder2="";
							if($j!=0 && $j!=1 && $j!=7)
							{
								$adder=" align='right'";	
							}
							if($j==7)
							{
								$adder=" style='font-weight:bold;'";
								$adder2=" ";	
							}
							
							$output.="<td valign='top'".$adder.">".$adder2."".$cols[$j]."</td>";		
							
							$track_arr[$cntr] = trim($cols[$j]);
							
							if($j==0)
							{
								$cols[$j]=str_replace(" (Team Rate)","",$cols[$j]);
								
								$sql2 = "
                              			select id
                              			from trucks
                              			where deleted = 0
                              				and trucks.name_truck = '".sql_friendly(trim($cols[$j])) ."'
                              			order by name_truck asc
                              		";
                              		$data2 = simple_query($sql2);
                              		if($row2 = mysqli_fetch_array($data2))
                              		{
                              			$cols[$j]=$row2['id'];
                              			$track_arr[0] = $row2['id'];
                              		}	
                              		
                              		//for test truck only
                              		if(trim($cols[$j])=="1520428")
                              		{
                              			$cols[$j]="1520428";
                              			$track_arr[0] = "1520428";
                              		}
							}
							
							$cntr++;							
						}							
					}
					$track_arr[17]=$packet;
               		$track_arr[18]=0;
               		$track_arr[19]=0;			
					/*
               		$track_arr[0]		//truck_id
               		$track_arr[1]		//linedate
               		$track_arr[2]		//truck_speed
               		$track_arr[3]		//truck_heading
               		$track_arr[4]		//gps_quality
               		$track_arr[5]		//latitude
               		$track_arr[6]		//longitude
               		$track_arr[7]		//location
               		$track_arr[8]		//fix_type
               		$track_arr[9]		//ignition
               		$track_arr[10]		//gps_odometer
               		$track_arr[11]		//gps_rolling_odometer
               		$track_arr[12]		//performx_odometer
               		$track_arr[13]		//performx_fuel
               		$track_arr[14]		//performx_speed
               		$track_arr[15]		//performx_idle
               		$track_arr[16]		//serial_number
               		$track_arr[17]		//packet_number
               		$track_arr[18]		//driver_id
               		$track_arr[19]		//driver2_id
               		
               		*/
               		
               		$useid=mrr_find_this_truck_time_tracking($track_arr[0],$track_arr[1]);
               		if($useid>0)
               		{
               			mrr_peoplenet_truck_tracking_update($track_arr,$useid);	
               		}
               		elseif($track_arr[0] > 0 || $track_arr[0]!="")	
               		{
               			$newid=mrr_peoplenet_truck_tracking_add($track_arr);
					}
					$output.="</tr>";	
				}	
			}			
			$output.="</table>";	
			
			if( $get_pack_num > $packet)
			{
				$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes)
				values
					(NULL,
					NOW(),
					'".sql_friendly($packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'".sql_friendly($get_pack_num)."',
					'0',
					'Updated: oi_pnet_location_history: ".sql_friendly($_SERVER['REQUEST_URI'])."')
				";
				simple_query($sql);
			}
			else
			{
				$output.="<div style='color:green;'>Packet ".$packet." is not available at this time.</div>";	
			}
		}	
		
		if($found_packet==1)
		{
			$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes)
				values
					(NULL,
					NOW(),
					'".sql_friendly($packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'".sql_friendly(($packet + 1))."',
					'0',
					'Updated: oi_pnet_location_history: ".sql_friendly($_SERVER['REQUEST_URI'])."')
				";
				simple_query($sql);		
		}
	}
	elseif($cmd=="pnet_dispatch_edit")
	{	//edit stop in PACOS dispatch...only allows removing of the stop right now 
					
		//processing for sample link
		$not_raw_xml=mrr_peoplenet_dispatch_stopper_not_raw($truck_name,1,$dispatch_id,$pn_stop_num);	//$truck,$delivery=0,$pn_disp_id="",$stop_num=""
		$not_raw_xml=htmlentities($not_raw_xml);
					
		$prime_url="http://open.pfmlogin.com/scripts/open.dll";
		$url="".$prime_url."?service=".$cmd."&amp;xml=".$not_raw_xml."&compat_level=1";
		$page=mrr_peoplenet_get_file_contents($url);
		$output="";	
	}
	elseif($cmd=="odl")
	{
		$url="http://open.pfmlogin.com/scripts/open.dll?cid=".$pn_cid."&pw=".$pn_pw."&service=".$cmd."&trucknum=".trim($truck_name)."";
		$page=mrr_peoplenet_get_file_contents($url);
		$output="";
	}
	
	//$output.="";
	return $output;
}

//functions of version 2 are used on the peoplenet_landmarks.php page. 
function mrr_peoplenet_service_selector2($field,$pre="")
{	//used commands on peoplenet_landmarks.php page.
	$section[0]="";					$arr[0]="";						$service[0]="Select Service";
			
	$section[]="DRIVERS";				$arr[]="oi_pnet_driver_view";			$service[]="Request a list of all, active, or inactive drivers. Defaults to active drivers.";
	//$section[]="DRIVERS";				$arr[]="oi_pnet_terminal_view";		$service[]="Request a list of terminals for a specific user and company.";
     
     //$section[]="LOCATION";				$arr[]="odl";						$service[]="Forces the PeopleNet system to make a data call to a truck";
     //$section[]="LOCATION";				$arr[]="loc_overview";				$service[]="Return the current location of all trucks in your fleet";
     //$section[]="LOCATION";				$arr[]="loc_onetruck";				$service[]="Return the current location of one truck in your fleet";
     //$section[]="LOCATION";				$arr[]="oi_pnet_location_history";		$service[]="Return all location history data gathered since the last time a packet was loaded";

     //$section[]="MESSAGING";				$arr[]="imessage_send";				$service[]="Send a message to vehicle(s) using the IMessage API";
     //$section[]="MESSAGING";				$arr[]="get_formdef";				$service[]="Return the form definition or structure of a specific form";
     //$section[]="MESSAGING";				$arr[]="oi_pnet_mes_checks";			$service[]="Return message status by packets";
     //$section[]="MESSAGING";				$arr[]="oi_pnet_message_history";		$service[]="Return messages by packets using the imessage API";
     //$section[]="MESSAGING";				$arr[]="get_signature";				$service[]="Return the image of a signature";
 
     //$section[]="PERFORMX";				$arr[]="oi_pnet_get_performx";		$service[]="Return a packet of PerformX information for vehicles";
     //$section[]="PERFORMX";				$arr[]="oi_performx_driver_packet";	$service[]="Retrieve new driver PerformX records";

     //$section[]="PACOS";					$arr[]="pnet_dispatch";				$service[]="Create a PACOS dispatch";
     //$section[]="PACOS";					$arr[]="pnet_dispatch_status";		$service[]="Get PACOS dispatch statuses";
     //$section[]="PACOS";					$arr[]="pnet_dispatch_edit";			$service[]="Add and/or remove stops from a PACOS dispatch";
     //$section[]="PACOS";					$arr[]="pnet_geocode_address";		$service[]="Geocode the latitude/longitude position of a street address";
     //$section[]="PACOS";					$arr[]="oi_pnet_dispatch_events";		$service[]="Request PACOS dispatch events";

     $section[]="LANDMARK MANAGEMENT";		$arr[]="pnet_landmark_add";			$service[]="Create a new landmark";
     //$section[]="LANDMARK MANAGEMENT";		$arr[]="pnet_landmark_remove";		$service[]="Remove an existing landmark";
     $section[]="LANDMARK MANAGEMENT";		$arr[]="pnet_landmark_view";			$service[]="View one or all landmarks";

     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_add";				$service[]="Create a new vehicle group";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_remove";			$service[]="Remove an existing vehicle group";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_list";			$service[]="List existing vehicle groups";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_detail";			$service[]="View members of an existing vehicle group";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_vehicle_add";		$service[]="Add vehicle(s) to an existing vehicle group";
     //$section[]="GROUP MANAGEMENT";		$arr[]="pnet_group_vehicle_remove";	$service[]="Remove vehicle(s) from an existing vehicle group";

     //$section[]="EDRIVER LOGS";			$arr[]="duty_status";				$service[]="Make on-duty/off-duty status changes to a driver's log";
     $section[]="EDRIVER LOGS";			$arr[]="elog_dispatch_info";			$service[]="Return current driver log information for all drivers";
     //$section[]="EDRIVER LOGS";			$arr[]="elog_events";				$service[]="Return historical log events, useful for tracking driver log activity";
     //$section[]="EDRIVER LOGS";			$arr[]="oi_pnet_elog_payroll";		$service[]="Return historical information about drivers that can be used for payroll systems";

     //$section[]="MISC";					$arr[]="pnet_vehicle_type_change";		$service[]="Change the vehicle type";
     //$section[]="MISC";					$arr[]="oi_alarms_packet";			$service[]="Retrieve new alarm data";
	
	$selbox="<select name='".$field."' id='".$field."' style='width:300px;'>";	
	for($i=0;$i< count($arr); $i++)
	{
		$sel="";		if($pre==$arr[$i])		$sel=" selected";
		$selbox.="<option value='".$arr[$i]."'".$sel.">".$section[$i]." - ".$service[$i]."</option>";
	}		
	$selbox.="</select>";
	return $selbox;	
}

function mrr_peoplenet_find_data2($cmd="",$arr,$type=0,$landmark_id=0,$delete_confirm=0,$truck=0)
{	//command processing for peoplenet_landmarks.php page only... 
	//see version 1 of this function below for the peoplenet_interface.php page
	
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];	//"3577";
	$pn_pw = $defaultsarray['peoplenet_account_password'];	//"35con77";
	$pn_cid=trim($pn_cid);
	$pn_pw=trim($pn_pw);
	
	$prime_url="http://open.pfmlogin.com/scripts/open.dll";
	
	$moder=2;
	
	$truck_name="";
	if($truck>0)
	{
		$sql = "
			select *
			from trucks
			where deleted = 0
				and trucks.id = '".sql_friendly($truck) ."'
			order by name_truck asc
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$truck_name=$row['name_truck'];
		}		
	}		
	$output="";
	
	if($truck==1520428)		$truck_name="1520428";
					
	//$output.="<div class='section_heading'>Command: ".$cmd." &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;";
	
	//if($truck_name!="")		$output.=" Truck: ".$truck_name." (ID=".$truck.")";
	
	//$output.="</div>";
	
	$url="";
	//creat URL for tracking based on command
	if($cmd=="pnet_landmark_add")
	{	//add a landmark
		$xml=mrr_add_peoplenet_landmark_xml($type,$arr);
		$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd);
		
		$page2="";
		
		$poser1=strpos($page,"<pnet_landmark_add_response>");
		$poser2=strpos($page,"</pnet_landmark_add_response>");
		if($poser1 > 0 && $poser2 > 0)
		{
			$page2=substr($page, $poser1, ($poser2 - $poser1));
			
			$page2=str_replace("<landmark_id>","<td valign='top'>",$page2);		$page2=str_replace("</landmark_id>","</td>",$page2);
			$page2=str_replace("<quality>","<td valign='top'>",$page2);			$page2=str_replace("</quality>","</td>",$page2);
		}
		
		$output.="<table border='0' cellpadding='0' cellspacing='0' width='200'>";
		$output.="	<tr>
						<td valign='top'><b>New Landmark</b></td>
						<td valign='top'><b>Quality</b></td>
					</tr>";
		
		$output.=$page2;
		
		$output.="</table>";
	}
	elseif($cmd=="pnet_landmark_remove")
	{	//remove a landmark
		if($landmark_id==0 && $delete_confirm > 0)	$lmarker="<remove_all/>";
		else									$lmarker="<landmark_id>".$landmark_id."</landmark_id>";
		
		$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
          "<!DOCTYPE pnet_landmark_remove PUBLIC '-//PeopleNet//pnet_landmark_remove' 'http://open.pfmlogin.com/dtd/pnet_landmark_remove.dtd'>".
          "<pnet_landmark_remove>".
             	"<cid>".$pn_cid."</cid>".
			"<pw><![CDATA[".$pn_pw."]]></pw>".
             	"".$lmarker."".
          "</pnet_landmark_remove>";
          
          $page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd);
          
          $page2="";
		
		$poser1=strpos($page,"<pnet_response>");
		$poser2=strpos($page,"</pnet_response>");
		if($poser1 > 0 && $poser2 > 0)
		{
			$page2=substr($page, $poser1, ($poser2 - $poser1));
			
			$page2=str_replace("<sendresult>","<td valign='top'>",$page2);		$page2=str_replace("</sendresult>","</td>",$page2);
			$page2=str_replace("<error_message>","<td valign='top'>",$page2);	$page2=str_replace("</error_message>","</td>",$page2);
		}
		
		$output.="<table border='0' cellpadding='0' cellspacing='0' width='200'>";
		$output.="	<tr>
						<td valign='top'><b>Result</b></td>
						<td valign='top'><b>Error Message</b></td>
					</tr>";
		
		$output.=$page2;
		
		$output.="</table>";
	}
	elseif($cmd=="pnet_landmark_view")
	{	//View landmark(s)
		$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
			"<!DOCTYPE pnet_landmark_view PUBLIC '-//PeopleNet//pnet_landmark_view' 'http://open.pfmlogin.com/dtd/pnet_landmark_view.dtd'>".
			"<pnet_landmark_view>".
				"<cid>".$pn_cid."</cid>".
				"<pw>".$pn_pw."</pw>".
			"</pnet_landmark_view>";
		
		$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd);
		
		$page2="";
		$page3="";
		$lms=0;
		
		$lms=substr_count($page,"<landmark>");
		$output.="<div style='color:blue;'>Landmarks Found (".$lms."):</div>";
		
		$poser1=strpos($page,"<pnet_landmark_view_response>");
		$poser2=strpos($page,"</pnet_landmark_view_response>");
		if($poser1 > 0 && $poser2 > 0)
		{
			$page2=substr($page, $poser1, ($poser2 - $poser1));
			$page2=str_replace("</pnet_landmark_view_response>","",$page2);		$page2=str_replace("<pnet_landmark_view_response>","",$page2);
			
			$page2=str_replace("<landmark_id>","<b><landmark_id>",$page2);		$page2=str_replace("</landmark_id>","</landmark_id></b>",$page2);
			
			$marks=explode("<landmark>",$page2);
			$landm=count($marks);
			$mycntr=0;
			for($i=0;$i < $landm; $i++)
			{
				$str=trim($marks[$i]);
				$str=str_replace("<landmark>"," ",$str);					$str=str_replace("</landmark>"," ",$str);
				if(trim($str)!="")
				{
					//$page3.="<tr><td><b>".$i.": '".$str."'</b></td></tr>";
					
					$page3.="<tr class='".($mycntr%2==0 ? 'even' : 'odd' )."'>";
					
					$var[0]="";
					for($j=0;$j <= 15; $j++)		$var[$j]="";
					
					$str.=" ".$str;
					
					if(substr_count($str,"<landmark_id>") > 0 && substr_count($str,"</landmark_id>") > 0)
					{
						$poser1=strpos($str,"<landmark_id>");
						$poser2=strpos($str,"</landmark_id>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[1]=$val_str;	
						}
					}
					if(substr_count($str,"<name>") > 0 && substr_count($str,"</name>") > 0)
					{
						$poser1=strpos($str,"<name>");			
						$poser2=strpos($str,"</name>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[2]=$val_str;	
						}
					}
					if(substr_count($str,"<type>") > 0 && substr_count($str,"</type>") > 0)
					{
						$poser1=strpos($str,"<type>");			
						$poser2=strpos($str,"</type>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[3]=$val_str;	
						}
					}
					if(substr_count($str,"<description>") > 0 && substr_count($str,"</description>") > 0)
					{
						$poser1=strpos($str,"<description>");			
						$poser2=strpos($str,"</description>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[4]=$val_str;	
						}
					}
					if(substr_count($str,"<addr1>") > 0 && substr_count($str,"</addr1>") > 0)
					{
						$poser1=strpos($str,"<addr1>");			
						$poser2=strpos($str,"</addr1>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[5]=$val_str;	
						}
					}
			
					if(substr_count($str,"<addr2>") > 0 && substr_count($str,"</addr2>") > 0)
					{
						$poser1=strpos($str,"<addr2>");			
						$poser2=strpos($str,"</addr2>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[6]=$val_str;	
						}
					}
					if(substr_count($str,"<city>") > 0 && substr_count($str,"</city>") > 0)
					{
						$poser1=strpos($str,"<city>");			
						$poser2=strpos($str,"</city>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[7]=$val_str;	
						}
					}
					if(substr_count($str,"<state>") > 0 && substr_count($str,"</state>") > 0)
					{
						$poser1=strpos($str,"<state>");			
						$poser2=strpos($str,"</state>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[8]=$val_str;	
						}
					}
					if(substr_count($str,"<zip>") > 0 && substr_count($str,"</zip>") > 0)
					{
						$poser1=strpos($str,"<zip>");			
						$poser2=strpos($str,"</zip>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[9]=$val_str;	
						}
					}		
					if(substr_count($str,"<custom1>") > 0 && substr_count($str,"</custom1>") > 0)
					{
						$poser1=strpos($str,"<custom1>");			
						$poser2=strpos($str,"</custom1>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[10]=$val_str;	
						}
					}
					if(substr_count($str,"<custom2>") > 0 && substr_count($str,"</custom2>") > 0)
					{
						$poser1=strpos($str,"<custom2>");			
						$poser2=strpos($str,"</custom2>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[11]=$val_str;	
						}
					}
					if(substr_count($str,"<custom3>") > 0 && substr_count($str,"</custom3>") > 0)
					{
						$poser1=strpos($str,"<custom3>");			
						$poser2=strpos($str,"</custom3>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[12]=$val_str;	
						}
					}
					if(substr_count($str,"<custom4>") > 0 && substr_count($str,"</custom4>") > 0)
					{
						$poser1=strpos($str,"<custom4>");			
						$poser2=strpos($str,"</custom4>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[13]=$val_str;	
						}
					}
					if(substr_count($str,"<latitude>") > 0 && substr_count($str,"</latitude>") > 0)
					{
						$poser1=strpos($str,"<latitude>");			
						$poser2=strpos($str,"</latitude>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[14]=$val_str;	
						}
					}
					if(substr_count($str,"<longitude>") > 0 && substr_count($str,"</longitude>") > 0)
					{
						$poser1=strpos($str,"<longitude>");			
						$poser2=strpos($str,"</longitude>");
						if($poser1 > 0 && $poser2 > 0)
						{
							$val_str=substr($str,$poser1,($poser2 - $poser1));	$val_str=strip_tags($val_str);		$val_str=trim($val_str);			$var[15]=$val_str;	
						}
					}
					
					$pnet_id=$var[1];
					$page3.="<td valign='top'><span class='mrr_link_like_on' onClick='mrr_load_landmark(".trim($pnet_id).");'>".trim($pnet_id)."</span></td>";
					for($j=2;$j <= 15; $j++)
					{
						$page3.="<td valign='top'><span id='row_".trim($pnet_id)."_field_".$j."'>".$var[$j]."</span></td>";	//".$j." 
					}
					$page3.="<td valign='top'><span onClick='mrr_landmark_remover(".trim($pnet_id).");'><img src='images/delete_sm.gif' border='0' alt='X' width='12' height='12'></span></td>";
					$page3.="</tr>";	
					
					$mycntr++;
				}	
			}							
		}
		 
		$output.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
		$output.="	<tr>
						<td valign='top'><b>ID</b></td>
						<td valign='top'><b>Landmark</b></td>
						<td valign='top'><b>Type</b></td>
						<td valign='top'><b>Description</b></td>
						<td valign='top'><b>Address1</b></td>
						<td valign='top'><b>Address2</b></td>
						<td valign='top'><b>City</b></td>
						<td valign='top'><b>State</b></td>
						<td valign='top'><b>Zip</b></td>
						<td valign='top'><b>Custom1</b></td>
						<td valign='top'><b>Custom2</b></td>
						<td valign='top'><b>Custom3</b></td>
						<td valign='top'><b>Custom4</b></td>
						<td valign='top'><b>Latitude</b></td>
						<td valign='top'><b>Longitude</b></td>
						<td valign='top'></td>
					</tr>";
		
		$output.=$page3;
		
		$output.="</table>";
	}
	elseif($cmd=="pnet_dispatch_status")
	{	//Get PACOS dispatch statuses
		$dispatch_id=$landmark_id;
		$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
               "<!DOCTYPE pnet_dispatch_status PUBLIC '-//PeopleNet//pnet_dispatch_status' 'http://open.pfmlogin.com/dtd/pnet_dispatch_status.dtd'>".
               "<pnet_dispatch_status>".
                  	"<cid>".$pn_cid."</cid>".
				"<pw>".$pn_pw."</pw>".
                  	"<dispid>".$dispatch_id."</dispid>".
               "</pnet_dispatch_status>";
		
		$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd);
		
		$page2="";
		
		$page3=$page;
		
		$span_style_arriving="";		//" style='background-color:grey;'";
		$span_style_arrived="";		//" style='background-color:purple;'";
		$span_style_departed="";		//" style='background-color:blue;'";
		
		$poser1=strpos($page,"<dispatch_status>");
		$poser2=strpos($page,"</dispatch_status>");
		if($poser1 > 0 && $poser2 > 0)
		{
			$page2=substr($page, $poser1, ($poser2 - $poser1));
			
			$page2=str_replace("<dispatch_status>","<span class='mrr_peoplenet_disp'>Status -- ",$page2);		$page2=str_replace("</dispatch_status>","</span>",$page2);	
			
			$page2=str_replace("<dispid>","DispID ",$page2);											$page2=str_replace("</dispid>"," ",$page2);
			
			$page2=str_replace("<dispatch_userdata>","(",$page2);										$page2=str_replace("</dispatch_userdata>",") ",$page2);
			
			$page2=str_replace("<vehicle_number>","Truck ",$page2);									$page2=str_replace("</vehicle_number>"," ",$page2);
			
			$page2=str_replace("<stop_status>","<br><span class='mrr_peoplenet_stop'>",$page2);				$page2=str_replace("</stop_status>","</span> ",$page2);
			
			$page2=str_replace("<stopid>","Stop ",$page2);											$page2=str_replace("</stopid>"," ",$page2);
			
			$page2=str_replace("<stop_userdata>","(",$page2);											$page2=str_replace("</stop_userdata>",") ",$page2);
			
			$page2=str_replace("<arriving_status>","Arriving: <span".$span_style_arriving.">",$page2);		$page2=str_replace("</arriving_status>","</span> ",$page2);
			
			$page2=str_replace("<action_status>","",$page2);											$page2=str_replace("</action_status>"," ",$page2);
			
			$page2=str_replace("<action_occurrence>","<span class='mrr_peoplenet_occur'>",$page2);			$page2=str_replace("</action_occurrence>","</span> ",$page2);
			
			$page2=str_replace("<datetime>","Time ",$page2);											$page2=str_replace("</datetime>"," ",$page2);
			
			$page2=str_replace("<px_odo>","Odom:",$page2);											$page2=str_replace("</px_odo>"," miles ",$page2);
			
			$page2=str_replace("<px_fuel>","Fuel: ",$page2);											$page2=str_replace("</px_fuel>"," gallons ",$page2);
			
			$page2=str_replace("<action_late/>"," <span class='mrr_peoplenet_note'>- Late</span>",$page2);					
			
			$page2=str_replace("<arrived_status>","Arrived: <span".$span_style_arrived.">",$page2);			$page2=str_replace("</arrived_status>","</span> ",$page2);
			
			$page2=str_replace("<departed_status>","Departed: <span".$span_style_departed.">",$page2);		$page2=str_replace("</departed_status>","</span> ",$page2);
							
			$page2=str_replace("<arriving_status/>","Arriving: N/A ",$page2);
			
			$page2=str_replace("<arrived_status/>","Arrived: N/A ",$page2);
			
			$page2=str_replace("<departed_status/>","Departed: N/A ",$page2);				
		}	
		
		$output.=$page2."";			
	}
	
	//$output.="";
	return $output;
}


//back to functions for all peoplenet sections...	
function mrr_peoplenet_use_the_force_call($truck=0)
{
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];	//"3577";
	$pn_pw = $defaultsarray['peoplenet_account_password'];	//"35con77";
	$pn_cid=trim($pn_cid);
	$pn_pw=trim($pn_pw);
	
	if($truck>0)
	{
		$url="http://open.pfmlogin.com/scripts/open.dll?cid=".$pn_cid."&pw=".$pn_pw."&service=odl&trucknum=".$truck."";
		mrr_peoplenet_get_file_contents($url);
	}
}
function mrr_get_geocode_for_address($address1,$address2,$city,$state,$zip)
{	//get the latitude and longitude coordinates so that the landmarks can be geocoded...
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];
	$pn_pw = $defaultsarray['peoplenet_account_password'];	
	
	$prime_url="http://open.pfmlogin.com/scripts/open.dll";
	$cmd="pnet_geocode_address";
	
	$state=strtoupper($state);
	if(strlen($state) > 2)	$state=substr($state,0,2);
	$zip=strtoupper($zip);
	if(strlen($zip) > 5)	$zip=substr($zip,0,2);
	
	$res['latitude']=0;
	$res['longitude']=0;
	$res['quality']=2;
	
	$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
		"<!DOCTYPE pnet_geocode_address PUBLIC '-//PeopleNet//pnet_dispatch_status' 'http://open.pfmlogin.com/dtd/pnet_geocode_address.dtd'>".
		"<pnet_geocode_address>".
		 	"<cid>".$pn_cid."</cid>".
			"<pw><![CDATA[".$pn_pw."]]></pw>".
			"<address><![CDATA[".$address1."]]></address>".
			"<city><![CDATA[".$city."]]></city>".
			"<state><![CDATA[".$state."]]></state>".
			"<zip><![CDATA[".$zip."]]></zip>".
		"</pnet_geocode_address>";
	
	$curl_handle=curl_init();
	
	curl_setopt($curl_handle, CURLOPT_URL,$prime_url);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);	
	
	if(trim($xml)!="")
	{			
		$post_cmd="service=".$cmd."&xml=".$xml."";	//$not_raw_xml
		curl_setopt($curl_handle, CURLOPT_POST,1);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); // we don't want to stop the page from loading if there is ever an issue with the SSL
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_cmd);
	}
	$page = curl_exec($curl_handle);
	curl_close($curl_handle);
	
	$poser1=strpos($page,"<latitude>");
	$poser2=strpos($page,"</latitude>");
	$poser3=strpos($page,"<longitude>");
	$poser4=strpos($page,"</longitude>");
	$poser5=strpos($page,"<quality>");
	$poser6=strpos($page,"</quality>");
	
	if($poser1 > 0 && $poser2 > 0)
	{
		$res['latitude']=substr($page,$poser1,($poser2 - $poser1));	
		$res['latitude']=str_replace("</latitude>","",$res['latitude']);
		$res['latitude']=str_replace("<latitude>","",$res['latitude']);
		if(!is_numeric($res['latitude']))		$res['latitude']=0.00;
		$res['latitude']=floatval($res['latitude']);
	}
	if($poser3 > 0 && $poser4 > 0)
	{
		$res['longitude']=substr($page,$poser3,($poser4 - $poser3));	
		$res['longitude']=str_replace("</longitude>","",$res['longitude']);
		$res['longitude']=str_replace("<longitude>","",$res['longitude']);
		if(!is_numeric($res['longitude']))		$res['longitude']=0.00;
		$res['longitude']=floatval($res['longitude']);
	}
	if($poser5 > 0 && $poser6 > 0)
	{
		$res['quality']=substr($page,$poser5,($poser6 - $poser5));
		$res['quality']=str_replace("</quality>","",$res['quality']);
		$res['quality']=str_replace("<quality>","",$res['quality']);	
		$res['quality']=(int)$res['quality'];
	}		
	return $res;
}
function mrr_add_peoplenet_landmark_xml($type,$arr)
{
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];
	$pn_pw = $defaultsarray['peoplenet_account_password'];	
	
	$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
	"<!DOCTYPE pnet_landmark_add PUBLIC '-//PeopleNet//pnet_landmark_add' 'http://open.pfmlogin.com/dtd/pnet_landmark_add.dtd'>".
     "<pnet_landmark_add>".
     	"<cid>".$pn_cid."</cid>".
        	"<pw><![CDATA[".$pn_pw."]]></pw>".
		"<name><![CDATA[".$arr['name']."]]></name>".
        	"<type>".$type."</type>".
        	"<description><![CDATA[".$arr['description']."]]></description>".
        	"<addr1><![CDATA[".$arr['addr1']."]]></addr1>".
        	"<addr2><![CDATA[".$arr['addr2']."]]></addr2>".
        	"<city><![CDATA[".$arr['city']."]]></city>".
        	"<state><![CDATA[".$arr['state']."]]></state>".
        	"<zip><![CDATA[".$arr['zip']."]]></zip>".
        	"<custom1><![CDATA[".$arr['custom1']."]]></custom1>".
        	"<custom2><![CDATA[".$arr['custom2']."]]></custom2>".
        	"<custom3><![CDATA[".$arr['custom3']."]]></custom3>".
        	"<custom4><![CDATA[".$arr['custom4']."]]></custom4>".
        	"<geocode_address/>". 
     "</pnet_landmark_add>";
     
	return $xml;		//<![CDATA[  ]]>
}

/*
//PACO or NAVI-GO XML builders
function mrr_get_dispatch_into_array($dispatch_id)
{
	$arr['dispatch_id']=$dispatch_id;
	$arr['load_id']=0;
	
	//PACOS dispatch-wide flags
	$arr['call_on_start']=1;	
	$arr['call_on_end']=1;	
	$arr['enable_auto_start']=1;	
	$arr['disable_driver_end']=1;	
	$arr['disable_auto_end']=0;	
	$arr['auto_start_driver_negative_guf']=0;	
	//stop flags
	$arr['auto_correct']=1;
	
	$arr['dispatch_name']="";
	$arr['dispatch_description']="";
	$arr['dispatch_userdata'="";
	$arr['trip_start_time']="";
	
	//stops for dispatch
	$arr['load_stops_cntr']=0;
	
	$arr['stop_userdata'][ 0 ]="";
	$arr['load_stop'][ 0 ]=0;
		
	$arr['shipper_name'][ 0 ]="";
	$arr['shipper_address1'][ 0 ]="";
	$arr['shipper_address2'][ 0 ]="";
	$arr['shipper_city'][ 0 ]="";
	$arr['shipper_state'][ 0 ]="";
	$arr['shipper_zip'][ 0 ]="";
	$arr['shipper_eta'][ 0 ]="";
	$arr['shipper_pta'][ 0 ]="";
	
	$arr['dest_name'][ 0 ]="";
	$arr['dest_address1'][ 0 ]="";
	$arr['dest_address2'][ 0 ]="";
	$arr['dest_city'][ 0 ]="";
	$arr['dest_state'][ 0 ]="";
	$arr['dest_zip'][ 0 ]="";
	$arr['dest_eta'][ 0 ]="";
	$arr['dest_pta'][ 0 ]="";
	
	$arr['linedate_pickup_eta'][ 0 ]="";
	$arr['linedate_pickup_pta'][ 0 ]="";
	$arr['linedate_dropoff_eta'][ 0 ]="";
	$arr['linedate_dropoff_pta'][ 0 ]="";
	
	$arr['stop_type_id'][ 0 ]="";
	$arr['directions'][ 0 ]="";
	$arr['stop_phone'][ 0 ]="";
	
	//stop flag (for each stop)setting for XML		
	$arr['stop_userdata'][ 0 ]="";
	$arr['landmark_id'][ 0 ]="";
	$arr['name_override'][ 0 ]="";
	$arr['description_override'][ 0 ]="";
	$arr['arrival_time'][ 0 ]="";
			
	$sql = "
		select trucks_log.*,
			 customers.name_company,
			 drivers.name_driver_first,
			 drivers.name_driver_last,
			 truck.name_truck,
			 trailers.trailer_name
		from trucks_log
			left join customers on customers.id=trucks_log.customer_id
			left join drivers on drivers.id=trucks_log.driver_id
			left join trucks on trucks.id=trucks_log.truck_id
			left join trailers on trailers.id=trucks_log.trailer_id
		where trucks_log.id='".sql_friendly($dispatch_id) ."'
	";		
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$arr['load_id']=$row['load_handler_id'];	
		
		$arr['name_truck']=$row['name_truck'];
		$arr['trailer_name']=$row['trailer_name'];
		
		$arr['name_company']=$row['name_company'];
		$arr['name_driver_first']=$row['name_driver_first'];
		$arr['name_driver_last']=$row['name_driver_last'];
		
		$arr['origin']=$row['origin'];
		$arr['dest']=$row['destination'];
		$arr['origin_state']=$row['origin_state'];
		$arr['dest_state']=$row['destination_state'];
		//$arr['dropped_trailer']=$row['dropped_trailer'];
		
		$arr['pickup_eta']=$row['linedate_pickup_eta'];
		$arr['pickup_pta']=$row['linedate_pickup_pta'];
		$arr['dropoff_eta']=$row['linedate_dropoff_eta'];
		$arr['dropoff_pta']=$row['linedate_dropoff_pta'];
		
		$arr['notes']=$row['notes'];	
		
		$arr['dispatch_name']="".$arr['name_company']." (Trailer ".$arr['trailer_name'].")";
		$arr['dispatch_description']="".$arr['origin']." ".$arr['origin_state']." to ".$arr['dest']." ".$arr['dest_state'].". ".$arr['notes']."";
		$arr['dispatch_userdata']="Load ".$arr['load_id'].": Dispatch ".$arr['dispatch_id'].".";
				
		$arr['trip_start_time']="".date("m/d/Y",strtotime($row['linedate']))." 00:00";
		
		$sql2 = "
			select * 
			from load_handler_stops
			where trucks_log_id='".sql_friendly($dispatch_id) ."'
				and load_handler_id='".sql_friendly( $arr['load_id'] ) ."'
				and deleted=0
				and linedate_completed > 0
		";		
		$data2=simple_query($sql2);
		//$mn2=mysqli_num_rows($data2);
		//$arr['load_stops']=$mn2;
		$icntr=0;
		
		//each stop details....................................................
		while($row2=mysqli_fetch_array($data2))
		{
			$arr['stop_userdata'][ $icntr ]="Stop ".($icntr+1)."";
			
			$arr['load_stop'][ $icntr ]=$row2['id'];
				
			$arr['shipper_name'][ $icntr ]=$row2['shipper_name'];
			$arr['shipper_address1'][ $icntr ]=$row2['shipper_address1'];
			$arr['shipper_address2'][ $icntr ]=$row2['shipper_address2'];
			$arr['shipper_city'][ $icntr ]=$row2['shipper_city'];
			$arr['shipper_state'][ $icntr ]=$row2['shipper_state'];
			$arr['shipper_zip'][ $icntr ]=$row2['shipper_zip'];
			$arr['shipper_eta'][ $icntr ]=$row2['shipper_eta'];
			$arr['shipper_pta'][ $icntr ]=$row2['shipper_pta'];
			
			$arr['dest_name'][ $icntr ]=$row2['dest_name'];
			$arr['dest_address1'][ $icntr ]=$row2['dest_address1'];
			$arr['dest_address2'][ $icntr ]=$row2['dest_address2'];
			$arr['dest_city'][ $icntr ]=$row2['dest_city'];
			$arr['dest_state'][ $icntr ]=$row2['dest_state'];
			$arr['dest_zip'][ $icntr ]=$row2['dest_zip'];
			$arr['dest_eta'][ $icntr ]=$row2['dest_eta'];
			$arr['dest_pta'][ $icntr ]=$row2['dest_pta'];
			
			$arr['linedate_pickup_eta'][ $icntr ]=$row2['linedate_pickup_eta'];
			$arr['linedate_pickup_pta'][ $icntr ]=$row2['linedate_pickup_pta'];
			$arr['linedate_dropoff_eta'][ $icntr ]=$row2['linedate_dropoff_eta'];
			$arr['linedate_dropoff_pta'][ $icntr ]=$row2['linedate_dropoff_pta'];
			
			$arr['stop_type_id'][ $icntr ]=$row2['stop_type_id'];
			$arr['directions'][ $icntr ]=$row2['directions'];
			$arr['stop_phone'][ $icntr ]=$row2['stop_phone'];
			//$arr[''][ $icntr ]=$row2[''];
			//$arr[''][ $icntr ]=$row2[''];
			//$arr[''][ $icntr ]=$row2[''];
			    			
			$icntr++;
		}
		$arr['load_stops_cntr']=$icntr;
		
	}
	return $arr;
}
function mrr_peoplenet_paco_dispatch_create($truck,$dispatch,$message,$delivery=0)
{	//ARR array is data for virtually all items... 
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];
	$pn_pw = $defaultsarray['peoplenet_account_password'];	
	
	$arr=mrr_get_dispatch_into_array($dispatch);
	
	$delivery_mode[0]="later";
	$delivery_mode[1]="now";	
	
	//falgs for trip
	$sect1="";		if($arr['call_on_start'] > 0)					$sect1="<call_on_start/>";	
	$sect2="";		if($arr['call_on_end'] > 0)					$sect2="<call_on_end/>";	
	$sect3="";		if($arr['enable_auto_start'] > 0)				$sect3="<enable_auto_start/>";	
	$sect4="";		if($arr['disable_driver_end'] > 0)				$sect4="<disable_driver_end/>";	
	$sect5="";		if($arr['disable_auto_end'] > 0)				$sect5="<disable_auto_end/>";	
	$sect6="";		if($arr['auto_start_driver_negative_guf'] > 0)	$sect6="<auto_start_driver_negative_guf/>";	
	//stop flags
	$sect7="";		if($arr['auto_correct'] > 0)					$sect7="<auto_correct/>";
	
	//dispatch entity
	$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
		"<!DOCTYPE pnet_imessage_send PUBLIC '-//PeopleNet//pnet_imessage_send' 'http://open.pfmlogin.com/dtd/pnet_imessage_send.dtd'>".
		"<pnet_dispatch>".
               "<cid>".$pn_cid."</cid>".
			"<pw><![CDATA[".$pn_pw."]]></pw>".
			"<vehicle_number><![CDATA[".$truck."]]></vehicle_number>".
			"<deliver>". $delivery_mode[ $delivery ] ."</deliver>".
			"<dispatch_name><![CDATA[".$arr['dispatch_name']."]]></dispatch_name>".
               "<dispatch_description><![CDATA[".$arr['dispatch_description']."]]></dispatch_description>".
               "<dispatch_userdata><![CDATA[".$arr['dispatch_userdata']."]]></dispatch_userdata>".
               "<trip_data>".
                 	"<trip_start_time><![CDATA[".$arr['trip_start_time']."]]></trip_start_time>".
                    "".$sect1."".
                    "".$sect2."".
                    "".$sect3."".
                    "".$sect4."".
                    "".$sect5."".
                    "".$sect6."".
               "</trip_data>".
               "<stop>".
               	"<stop_head>".                   
                      	"<stop_userdata><![CDATA[".$arr['stop_userdata']."]]></stop_userdata>".
                      	"<landmark>".
                      		"<landmark_id><![CDATA[".$arr['landmark_id']."]]></landmark_id>".
                        		"".$sect7."".
                        		"<name_override><![CDATA[".$arr['name_override']."]]></name_override>".
                        		"<description_override><![CDATA[".$arr['description_override']."]]></description_override>".
                      	"</landmark>".
                    "</stop_head>".
                    "<basic_actions>".
                        "<arrival_time><![CDATA[".$arr['arrival_time']."]]></arrival_time>".
                    "</basic_actions>".
          	"</stop>".
          "</pnet_dispatch>".
		"";	
		//<![CDATA[]]>	
	return $xml;
}

*/


function mrr_pull_all_active_geofencing_rows($mode=0)
{
	global $new_style_path;
	global $defaultsarray;
	mrr_deactivate_completed_geofence_rows();
	
	$tab="";		//activity report
	$tab2="";		//load board notices
	
	$tab_pickup="";	//activity report split
	$tab_delivery="";	//activity report split
	$tab_no_pn="";
	
	
	$no_pn_truck_cntr=0;
	$no_pn_truck_arr[0]=0;
	
	$sqlx="
		select load_handler.*,
			trucks_log.customer_id as cust_id,
			trucks_log.driver_id as driverid,
			trucks_log.truck_id as truckid,
			trucks.name_truck as truckname,
			trailers.trailer_name as trailername,
			load_handler_stops.start_trailer_id as trailerid,
			drivers.name_driver_first as driverfname,
			drivers.name_driver_last as driverlname,
			customers.name_company as compname,
			load_handler_stops.load_handler_id,
			load_handler_stops.trucks_log_id,
			load_handler_stops.id as stop_id,
			load_handler_stops.linedate_pickup_eta as stop_pickup_eta,
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
			
		where load_handler.deleted=0  
			and load_handler_stops.deleted=0 	
			and trucks_log.deleted=0	
			and trucks.deleted=0
			and drivers.deleted=0
			and customers.deleted=0
			and trucks.peoplenet_tracking=0
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null)
			and trucks_log.dispatch_completed=0     			
			
		order by load_handler_stops.linedate_pickup_eta asc,
				load_handler_stops.load_handler_id asc,     				
				load_handler_stops.trucks_log_id asc,
				load_handler_stops.id asc						
	";
	$datax=simple_query($sqlx);
	while($rowx=mysqli_fetch_array($datax))
	{
		$due_date=$rowx['stop_pickup_eta'];
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
		
		if($found==0)
		{     		
			$tab_no_pn.="
          		<tr>
          			<td><a href='manage_load.php?load_id=".$rowx['load_handler_id']."' target='_blank'>".$rowx['load_handler_id']."</a></td>
          			<td><a href='add_entry_truck.php?load_id=".$rowx['load_handler_id']."&id=".$rowx['trucks_log_id']."' target='_blank'>".$rowx['trucks_log_id']."</a></td>
          			<td>".$rowx['stop_id']." ".$stop_typer."</td>
          			<td><a href='admin_customers.php?eid=".$rowx['cust_id']."' target='_blank'>".$rowx['compname']."</a></td>
          			<td><a href='admin_drivers.php?id=".$rowx['driverid']."' target='_blank'>".$rowx['driverfname']." ".$rowx['driverlname']."</a></td>
          			<td><a href='admin_trucks.php?id=".$rowx['truckid']."' target='_blank'>".$rowx['truckname']."</a></td>
          			<td><a href='admin_trailers.php?id=".$rowx['trailerid']."' target='_blank'>".$rowx['trailername']."</a></td>
          			<td>&nbsp;</td>
          			<td>".date("m/d/Y H:i",strtotime($due_date))."</td>
          			<td>".$suffix."</td>			
          			<td>".$rowx['stopcity'].", ".$rowx['stopstate']."</td>
          			<td>".$rowx['pcm_miles']."</td>
          			<td>".$rowx['stopname']."</td>
          			<td>&nbsp;</td>
          			<td>&nbsp;</td>
          			<td>&nbsp;</td>
          			<td>&nbsp;</td>
          			<td>&nbsp;</td>                   			
          		</tr>
     		"; 
     		          		
			$no_pn_truck_arr[$no_pn_truck_cntr]=$rowx['truckid'];
			$no_pn_truck_cntr++;
     	}              	
	}		
	
	
	
	$nowtime=time();
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];	
	if($mph <=0)	$mph=1;
	
	$grade_offset=0;
	if(is_numeric($defaultsarray['peoplenet_grading_offset_hrs']))
	{
		$grade_offset=$defaultsarray['peoplenet_grading_offset_hrs'];
		$grade_offset=number_format($grade_offset,2);
	}
	$adder="";
	if($mode==1)	$adder="and geofence_hot_load_tracking.active>0";
	$diff_time=0;
	
	$load_list="";
		
	$sql="
		select geofence_hot_load_tracking.*,
			trucks.name_truck as truckname,
			trucks_log.truck_id as truckid,
			(select trailer_name from trailers where trailers.id=geofence_hot_load_tracking.trailer_id) as trailername,
			(select start_trailer_id from load_handler_stops where load_handler_stops.id=geofence_hot_load_tracking.stop_id) as trailerid,
			drivers.name_driver_first as driverfname,
			drivers.name_driver_last as driverlname,
			customers.name_company as compname,
			(select stop_type_id from load_handler_stops where load_handler_stops.id=geofence_hot_load_tracking.stop_id) as stop_mode,
			(select shipper_name from load_handler_stops where load_handler_stops.id=geofence_hot_load_tracking.stop_id) as stopname,
			(select shipper_city from load_handler_stops where load_handler_stops.id=geofence_hot_load_tracking.stop_id) as stopcity,
			(select shipper_state from load_handler_stops where load_handler_stops.id=geofence_hot_load_tracking.stop_id) as stopstate 
		from ".mrr_find_log_database_name()."geofence_hot_load_tracking
			left join trucks_log on trucks_log.id=geofence_hot_load_tracking.dispatch_id
			left join trucks on trucks.id=trucks_log.truck_id
			left join drivers on drivers.id=trucks_log.driver_id
			left join customers on customers.id=trucks_log.customer_id
			left join load_handler_stops on load_handler_stops.id=geofence_hot_load_tracking.stop_id
			left join load_handler on load_handler.id=geofence_hot_load_tracking.load_id
		where geofence_hot_load_tracking.load_id>0
			and geofence_hot_load_tracking.dispatch_id>0
			and geofence_hot_load_tracking.stop_id>0
			and geofence_hot_load_tracking.deleted=0
			".$adder."     		
			and (load_handler_stops.linedate_completed<'2013-01-01 00:00:00' or load_handler_stops.linedate_completed is null)
			and trucks_log.dispatch_completed=0
			and trucks_log.deleted=0
			and load_handler.deleted=0
			and trucks.deleted=0
			and drivers.deleted=0
			and customers.deleted=0
		order by 	geofence_hot_load_tracking.linedate asc,
				geofence_hot_load_tracking.load_id asc,     				
				geofence_hot_load_tracking.dispatch_id asc,
				geofence_hot_load_tracking.stop_id asc,					
				geofence_hot_load_tracking.id desc							
	";
	/*
	
		and geofence_hot_load_tracking.linedate_last_gps > '0000-00-00 00:00:00'
		and geofence_hot_load_tracking.stop_completed=0	
		and (
				geofence_hot_load_tracking.dest_arriving=0
				or geofence_hot_load_tracking.dest_arrived=0
				or geofence_hot_load_tracking.dest_departed=0
				)
	*/
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$id=$row['id'];
		$act=$row['active'];
		$grade=$row['dispatch_grade'];
		$long=$row['dest_longitude'];
		$lat=$row['dest_latitude'];
		
		$my_trailer_id=$row['trailerid'];
		$my_trailer="";
		$sql2="select trailer_name from trailers where id='".sql_friendly($my_trailer_id)."'";
		$data2=simple_query($sql2);
		if($row2=mysqli_fetch_array($data2))
		{
			$my_trailer=$row2['trailer_name'];	
		}
		
		$destlong=$row['last_gps_longitude'];
		$destlat=$row['last_gps_latitude'];
		$dist=$row['dest_distance'];
		
		$has_arriving_date=$row['linedate_last_arriving'];
     	$has_arrived_date=$row['linedate_last_arrived'];
     	$has_departed_date=$row['linedate_last_departed'];
     					
     	$msg_sent_for_arriving=$row['msg_last_arriving'];
     	$msg_sent_for_arrived=$row['msg_last_arrived'];
     	$msg_sent_for_departed=$row['msg_last_departed'];
     	
     	$memo_line="
     		Date Arriving=".$has_arriving_date.",
     		Arriving Sent=".$msg_sent_for_arriving.",
     		Date Arrived=".$has_arrived_date.",
     		Arrived Sent=".$msg_sent_for_arrived.",
     		Date Departed=".$has_departed_date.",
     		Departed Sent=".$msg_sent_for_departed.".
     	";
     				
		//stop ...
		$destination="";
		$due_date=$row['linedate'];
     	$sql="
			select * 
			from load_handler_stops
			where id='".sql_friendly($row['stop_id'])."'				
		";
		$data_stop=simple_query($sql);
		if($row_stop=mysqli_fetch_array($data_stop))
		{
			$destination=$row_stop['shipper_city'].", ".$row_stop['shipper_state'];
			$due_date=$row_stop['linedate_pickup_eta'];
		}
		
		$last_date=$row['linedate_last_gps'];
		$comp=$row['compname'];
		$dest_name="".$row['stopname']." (".$row['stopcity'].", ".$row['stopstate'].")";
		$dmode="";
		$dnote="";
		
		$suffix=" hrs";		
		$use_miles=0;
		$use_timer=0;
		$use_minutes=0;
		$sect=0;
		$timer_x=strtotime($due_date);
		$grader="";
		
		if($row['dest_arriving'] == 0)
		{
			$use_miles=abs($row['dest_remaining_arriving']);
			$use_timer=$row['dest_time_arriving'].$suffix;
			$sect=1;
			
			if($mph>0)	$use_timer=$use_miles / $mph;
     		$use_minutes=$use_timer * 60; 
			
			//grade it...		
			$diff_time=($timer_x - $nowtime)/(60 * 60);		//due in X hours...appointment minus NOW.  If negative...past due. positive value is howm many hours left before late...
			//compare time until late (now verses appointment with the hours it will take to complete the trip to get the grade... 
			if($diff_time < 0)										$grader="<span class='geofencing_past_due'>Past Due</span>";
			elseif($diff_time < $use_timer && $use_timer > $grade_offset)	$grader="<span class='geofencing_very_late'>Very Late</span>";
			elseif($diff_time < $use_timer)							$grader="<span class='geofencing_late'>Late</span>";
			elseif($diff_time > $use_timer && $use_timer < $grade_offset)	$grader="<span class='geofencing_early'>Little Early</span>";
			elseif($diff_time > $use_timer)							$grader="<span class='geofencing_very_early'>Very Early</span>";
			else													$grader="On Time";
			
			$dmode="Delivery heading to facility -- ".$grader.""; 
		}
		elseif($row['dest_arrived'] == 0)
		{
			$use_miles=abs($row['dest_remaining_arrived']);
			$use_timer=$row['dest_time_arrived'].$suffix;
			$sect=2;
			
			if($mph>0)	$use_timer=$use_miles / $mph;
     		$use_minutes=$use_timer * 60; 
						
			//grade it...		
			$diff_time=($timer_x - $nowtime)/(60 * 60);		//due in X hours...appointment minus NOW.  If negative...past due. positive value is howm many hours left before late...
			//compare time until late (now verses appointment with the hours it will take to complete the trip to get the grade... 
			if($diff_time < 0)										$grader="<span class='geofencing_past_due'>Past Due</span>";
			elseif($diff_time < $use_timer && $use_timer > $grade_offset)	$grader="<span class='geofencing_very_late'>Very Late</span>";
			elseif($diff_time < $use_timer)							$grader="<span class='geofencing_late'>Late</span>";
			elseif($diff_time > $use_timer && $use_timer < $grade_offset)	$grader="<span class='geofencing_early'>Little Early</span>";
			elseif($diff_time > $use_timer)							$grader="<span class='geofencing_very_early'>Very Early</span>";
			else													$grader="On Time";
			
			$dmode="Delivery is approaching facility -- ".$grader."";
		}
		elseif($row['dest_departed'] == 0)
		{
			$use_miles=abs($row['dest_remaining_departed']);
			$use_timer=$row['dest_time_departed'].$suffix;
			$sect=3;
			
			if($mph>0)	$use_timer=$use_miles / $mph;
     		$use_minutes=$use_timer * 60; 
     		
     		$grader="Delivered";
     		
     		$dmode="Delivery is able to leave facility -- ";
		}
		
		if($long==0 && $lat==0)	
		{
			$dnote="<span class='alert'>No Dest GPS</span>";		//No PN Dispatch
			$use_miles="";
			$use_timer="";
			$use_minutes="";
			$grader="";
			$suffix="";
		}
		
		$act_link="<a href='report_peoplenet_activity.php?activate=".$row['id']."' title='turn this tracking stop back on.' style='color:green;'>Turn On</a>";
		if($act>0)	$act_link="<a href='javascript:confirm_deactivate(".$row['id'].")' title='turn this tracking stop off.' style='color:red;'>Turn Off</a>";
		
		
		//$long=$row['dest_longitude'];
		//$lat=$row['dest_latitude'];
		
		$destlong=$row['last_gps_longitude'];
		$destlat=$row['last_gps_latitude'];
		
		// title='".$memo_line."'
		$ut_masker="";
		if(is_numeric($use_timer))	$ut_masker="".number_format($use_timer,2)."";
		$utm_masker="";
		if(is_numeric($use_minutes))	$utm_masker="".number_format($use_minutes,2)."";
		
		//<a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailername']."</a>
		
		if(substr_count($load_list,  ",".$row['load_id'].",")==0)
		{
     		$load_list.=",".$row['load_id'].",";
     		$stop_typer="(S)";        		
     		
     		$use_truck=$row['truck_id'];
     		if($row['truck_id'] != $row['truckid'])		$use_truck=$row['truckid'];
     		
     		if($row['stop_mode']==2)
     		{
          		$stop_typer="(C)";
          		$tab_delivery.="
               		<tr>
               			<td><a href='manage_load.php?load_id=".$row['load_id']."' target='_blank' name='".$row['id']."'>".$row['load_id']."</a></td>
               			<td><a href='add_entry_truck.php?load_id=".$row['load_id']."&id=".$row['dispatch_id']."' target='_blank'>".$row['dispatch_id']."</a></td>
               			<td><span onClick='mrr_check_geofence_location(".$row['id'].",\"".$row['truck_id']."\",\"".$row['truckname']."\",\"".$lat."\",\"".$long."\",\"".$destlat."\",\"".$destlong."\");'>".$row['stop_id']." ".$stop_typer."</span></td>
               			<td><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['compname']."</a></td>
               			<td><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driverfname']." ".$row['driverlname']."</a></td>
               			<td><a href='admin_trucks.php?id=".$use_truck."' target='_blank'>".$row['truckname']."</a></td>
               			<td><a href='admin_trailers.php?id=".$my_trailer_id."' target='_blank'>".$my_trailer."</a></td>
               			<td>".$dnote."</td>
               			<td>".date("m/d/Y H:i",strtotime($due_date))."</td>
               			<td>".number_format($diff_time,2)."".$suffix."</td>			
               			<td>".$destination."</td>
               			<td>".abs($use_miles)."</td>
               			<td>".$row['dest_message']."</td>
               			<td>".$ut_masker."".$suffix."</td>
               			<td>".$grader."</td>
               			<td><span class='mrr_link_like_on' onClick='send_email_hot_tracking(".$row['id'].",".$sect.");'>Email</span></td>
               			<td>".$act_link."</td>
               			<td><a href='javascript:confirm_delete(".$row['id'].")'><img src='images/delete_sm.gif' border='0'></a></td>                   			
               		</tr>
          		";
          	}
          	else
          	{	
          		$tab_pickup.="
               		<tr>
               			<td><a href='manage_load.php?load_id=".$row['load_id']."' target='_blank' name='".$row['id']."'>".$row['load_id']."</a></td>
               			<td><a href='add_entry_truck.php?load_id=".$row['load_id']."&id=".$row['dispatch_id']."' target='_blank'>".$row['dispatch_id']."</a></td>
               			<td><span onClick='mrr_check_geofence_location(".$row['id'].",\"".$row['truck_id']."\",\"".$row['truckname']."\",\"".$lat."\",\"".$long."\",\"".$destlat."\",\"".$destlong."\");'>".$row['stop_id']." ".$stop_typer."</span></td>
               			<td><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['compname']."</a></td>
               			<td><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driverfname']." ".$row['driverlname']."</a></td>
               			<td><a href='admin_trucks.php?id=".$use_truck."' target='_blank'>".$row['truckname']."</a></td>
               			<td><a href='admin_trailers.php?id=".$my_trailer_id."' target='_blank'>".$my_trailer."</a></td>
               			<td>".$dnote."</td>
               			<td>".date("m/d/Y H:i",strtotime($due_date))."</td>
               			<td>".number_format($diff_time,2)."".$suffix."</td>			
               			<td>".$destination."</td>
               			<td>".abs($use_miles)."</td>
               			<td>".$row['dest_message']."</td>
               			<td>".$ut_masker."".$suffix."</td>
               			<td>".$grader."</td>
               			<td><span class='mrr_link_like_on' onClick='send_email_hot_tracking(".$row['id'].",".$sect.");'>Email</span></td>
               			<td>".$act_link."</td>
               			<td><a href='javascript:confirm_delete(".$row['id'].")'><img src='images/delete_sm.gif' border='0'></a></td>                    			
               		</tr>
          		";	
     		}
     		
     		//load baord version     		
     		$base_msg="".$dmode." ".$use_miles." Miles (at least ".$ut_masker." hours or ".$utm_masker." minutes).";
     		     		
     		$tlinker="<a href='manage_load.php?load_id=".$row['load_id']."' target='_blank'>".$row['truckname']."</a>";	//admin_trucks.php?id=".$row['truck_id']."
     		if($long==0 && $lat==0)	
     		{
     			$base_msg="<span class='alert'>No PN Dispatch</span>";
     			$tlinker="<a href='manage_load.php?load_id=".$row['load_id']."' target='_blank'>".$row['truckname']."</a>";	//peoplenet_messager.php?truck_id=".$row['truck_id']."
     		}
     		
     		if($last_date=="0000-00-00 00:00:00")	$last_date="GPS info TBA";
     						
			$tab2.=	"<li>";
			$tab2.=		"<h3>";
			$tab2.=			"<span>".$last_date." --- ".$tlinker."</span>";
			$tab2.=			"<a href='report_peoplenet_activity.php#".$row['id']."' target='_blank'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
			$tab2.=		"</h3>";
			$tab2.=		"<p>Load: <a href='manage_load.php?load_id=".$row['load_id']."' target='_blank' name='".$row['id']."'>".$row['load_id']."</a>: <a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driverlname']." ".$row['driverfname']."</a>
						<br>".$dest_name." for <a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['compname']."</a> Stop ".$row['stop_id']."
						<br>".$base_msg."</p> ";
			$tab2.=	"</li>";
		}
		
	}
	
	$tab="
		<tr><td colspan='18'><b>Delivery</b></td></tr>
		".$tab_delivery."
		<tr><td colspan='18'><b>&nbsp;</b></td></tr>
		<tr><td colspan='18'><b>Pickup</b></td></tr>
		".$tab_pickup."   
		<tr><td colspan='18'><b>&nbsp;</b></td></tr>  		
		<tr><td colspan='18'><b>Non-PeopleNet:  No tracking available.</b></td></tr>
		".$tab_no_pn."     		
	";	
	
	
	$tab2.=	"<li>";
	$tab2.=		"<h3>";
	$tab2.=			"<br><span>Geofence Legend</span>";    			
	$tab2.=		"</h3>";
	$tab2.=		"<p><span style='color:purple;'>This section now only shows the current/first stop (by appointment time) for each truck.</span></p>";
	$tab2.=		"<p>Grading Scale uses these colors</p>";
	$tab2.=		"<p><span class='geofencing_past_due'>Past Due</span>: After appointment</p>";
	$tab2.=		"<p><span class='geofencing_very_late'>Very Late</span>: >".$grade_offset." hrs after</p>";
	$tab2.=		"<p><span class='geofencing_late'>Late</span>: <=".$grade_offset." hrs after</p>";
	$tab2.=		"<p><span class='geofencing_early'>Little Early</span>: <=".$grade_offset." hrs before</p>";
	$tab2.=		"<p><span class='geofencing_very_early'>Very Early</span>: >".$grade_offset." hrs before</p>";
	$tab2.=		"<p>Dispatch must have been sent via PN.</p> ";
	$tab2.=		"<p>Hot Load Tracking must be turned on for each Load.</p> ";
	$tab2.=	"</li>";	
	
	
	if($mode==0)	return $tab;
	if($mode==1)	return $tab2;
}

function mrr_pull_all_active_geofencing_event_rows($load_id=0,$dispatch_id=0,$stop_id=0)
{		
	$tab="";		//activity report
	
	$adder="";
	if($load_id > 0)		$adder.="and truck_tracking_event_history.load_id='".sql_friendly($load_id)."'";
	if($dispatch_id > 0)	$adder.="and truck_tracking_event_history.disptach_id='".sql_friendly($dispatch_id)."'";
	if($stop_id > 0)		$adder.="and truck_tracking_event_history.stop_id='".sql_friendly($stop_id)."'";
	
	$orderer="
		order by truck_tracking_event_history.linedate_added asc,
				truck_tracking_event_history.load_id asc,
				truck_tracking_event_history.disptach_id asc,
				truck_tracking_event_history.stop_id asc,
				truck_tracking_event_history.id desc	
	";
	
	if($load_id==0 && $dispatch_id==0 && $stop_id==0)	
	{
		$adder=" and truck_tracking_event_history.linedate_added>='".date("Y-m-d")." 00:00:00'";
	}
	else
	{
		$orderer="
			order by truck_tracking_event_history.pn_dispatch_id asc,
				truck_tracking_event_history.pn_stop_id asc,
				truck_tracking_event_history.load_id asc,
				truck_tracking_event_history.disptach_id asc,
				truck_tracking_event_history.stop_id asc,
				truck_tracking_event_history.linedate_added asc,
				truck_tracking_event_history.id desc	
		";	
	}
	
	$tab.="<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1800px;margin:10px'>";
	$tab.="
		<thead>
               <tr>
               	<th><b>Date</b></th>
               	<th><b>Truck</b></th> 
               	<th><b>Trailer</b></th>
               	<th><b>Driver</b></th>
               	<th><b>Customer</b></th> 
               	<th><b>S/C</b></th>
               	<th><b>Destination</b></th>                 	
               	<th nowrap><b>Load</b></th>
               	<th nowrap><b>Disp</b></th>
               	<th nowrap><b>Stop</b></th>
               	<th><b>Packet#</b></th>
               	<th nowrap><b>PN Disp#</b></th>
               	<th nowrap><b>PN Stop#</b></th>
               	<th><b>Event</b></th>
               	<th><b>Reason</b></th>
               	<th><b>Lat</b></th>
               	<th><b>Long</b></th>
               	<th><b>Fuel</b></th>
               	<th><b>Odom</b></th>
               	<th><b>Info</b></th>                  	
               	<th><b>Emailed</b></th>
               </tr>
         </thead>
         <tbody>
	";
	     	     		
	$sql="
		select truck_tracking_event_history.*,
			name_driver_first,
			name_driver_last,
			driver_id,
			customer_id,
			name_company,
			trailer_name,
			start_trailer_id,
			shipper_name,
			shipper_city,
			shipper_state
			
		from ".mrr_find_log_database_name()."truck_tracking_event_history
			left join trucks_log on trucks_log.id=truck_tracking_event_history.disptach_id
			left join load_handler_stops on load_handler_stops.id=truck_tracking_event_history.stop_id
			left join trailers on trailers.id=load_handler_stops.start_trailer_id
			left join drivers on drivers.id=trucks_log.driver_id
			left join customers on customers.id=trucks_log.customer_id
			
		where truck_tracking_event_history.packet_id>0
			and trucks_log.deleted=0
			and load_handler_stops.deleted=0
			and trailers.deleted=0
			and drivers.deleted=0
			and customers.deleted=0     			
			".$adder."
		".$orderer."     								
	";
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$id=$row['id'];
		$date_added=date("m/d/Y H:i",strtotime($row['linedate_added']));
     	$date_gmt=date("m/d/Y H:i",strtotime($row['linedate']));
		$packet=$row['packet_id'];
		$pn_disp=$row['pn_dispatch_id'];
		$pn_stop=$row['pn_stop_id'];
		$typer=$row['e_type'];
		$reason=$row['e_reason'];
		$lat=$row['e_latitude'];
		$long=$row['e_longitude'];
		$gallons=$row['px_fuel'];
     	$odom=$row['px_odometer'];
     	$odo_type=$row['px_odo_type'];
		$info=$row['stop_data'];     		
		$load=$row['load_id'];
		$disp=$row['disptach_id'];
		$stop=$row['stop_id'];
		$tid=$row['truck_id'];
		$truck=$row['truck_name'];     		
		$sent=$row['email_sent'];
		$date_email=date("m/d/Y H:i",strtotime($row['email_sent_date']));
		
		$driver=$row['name_driver_first']." ".$row['name_driver_last'];
		$driver_id=$row['driver_id'];
		$cust_id=$row['customer_id'];
		$cust=$row['name_company'];
		$trailer_id=$row['start_trailer_id'];
		$trailer=$row['trailer_name'];
		$shipper=$row['shipper_name'];
		$city=$row['shipper_city'];
		$state=$row['shipper_state'];
		if(substr_count($date_email,"12/31/1969") > 0)		$date_email=""; 
		
		if($disp==$load)	$disp=0;	//dispatch is not available.
		
		$disp_link="<a href='add_entry_truck.php?load_id=".$load."&id=".$disp."' target='_blank'>".$disp."</a>";
		
		$tag1="";			$tag2="";    		
		
		if($sent==1)	{	$tag1="<span style='color:#cc00cc;'>";			$tag2="</span>";		}
		if(substr_count(strtolower(trim($typer)),"departed") > 0)		{	$tag1="<span style='color:#00cccc;'>";			$tag2="</span>";		}
		if($disp==0)	{	$tag1="<span style='color:#cc0000;'>";			$tag2="</span>";		$disp_link="";		}
		
		     				
		$tab.="
     		<tr>
     			<td><span class='mrr_link_like_on' onClick='mrr_reset_load_disp_stop(".$load.");'>".$date_added."</span></td>
     			<td><a href='admin_trucks.php?id=".$tid."' target='_blank'>".$truck."</a></td>     
     			<td><a href='admin_trailers.php?id=".$trailer_id."' target='_blank'>".$trailer."</a></td> 
     			<td><a href='admin_drivers.php?id=".$driver_id."' target='_blank'>".$driver."</a></td>
     			<td><a href='admin_customers.php?eid=".$cust_id."' target='_blank'>".$cust."</a></td>
     			<td>".$tag1."".$shipper."".$tag2."</td> 
     			<td>".$tag1."".$city.", ".$state."".$tag2."</td> 
     			<td><a href='manage_load.php?load_id=".$load."' target='_blank' name='".$load."'>".$load."</a></td>
     			<td>".$disp_link."</td>
     			<td>".$tag1."".$stop."".$tag2."</td>
     			<td>".$tag1."".$packet."".$tag2."</td>
     			<td>".$tag1."".$pn_disp."".$tag2."</td>
     			<td>".$tag1."".$pn_stop."".$tag2."</td>
     			<td>".$tag1."".$typer."".$tag2."</td>          			
     			<td>".$tag1."".$reason."".$tag2."</td>
     			<td>".$tag1."".$lat."".$tag2."</td>
     			<td>".$tag1."".$long."".$tag2."</td>  
     			<td>".$tag1."".$gallons."".$tag2."</td>
     			<td>".$tag1."".$odom."".$tag2."</td>
     			<td>".$tag1."".$info."".$tag2."</td>         			
     			<td>".$tag1."".$date_email."".$tag2."</td>
     		</tr>
		";	
		
	}  
	$tab.="</tbody>"; 	 
	$tab.="</table>"; 	
	return $tab;
}



function mrr_find_this_truck_tracking_msg_history($truck_id,$truck_name,$created,$received)
{
	$id=0;
	$sql="
		select id 
		from ".mrr_find_log_database_name()."truck_tracking_msg_history
		where truck_id='".sql_friendly($truck_id)."'
			and truck_name='".sql_friendly($truck_name)."'
			and linedate_created='".date("Y-m-d H:i:s",strtotime($created))."'
			and linedate_received='".date("Y-m-d H:i:s",strtotime($received))."'
		order by id desc
		limit 1
	";
	$data = simple_query($sql);
     if($row = mysqli_fetch_array($data))
     {
     	$id=$row['id'];	
     }
	return $id;	
}

function mrr_find_this_truck_time_tracking($truck_id,$dater)
{
	$id=0;
	$sql="
		select id 
		from ".mrr_find_log_database_name()."truck_tracking
		where truck_id='".sql_friendly($truck_id)."'
			and linedate='".date("Y-m-d H:i:s",strtotime($dater))."'
		order by id desc
		limit 1
	";
	$data = simple_query($sql);
     if($row = mysqli_fetch_array($data))
     {
     	$id=$row['id'];	
     }
	return $id;
}
function mrr_peoplenet_get_file_contents($prime_url,$xml="",$cmd="")
{			
	$xml=str_replace("&"," and ",$xml);
	$xml=str_replace("&","&amp;",$xml);
	//$xml=str_replace("#","No.",$xml);
	$xml=str_replace("\r"," ",$xml);
	$xml=str_replace("\n"," ",$xml);
	$xml=str_replace("\l"," ",$xml);
	$xml=str_replace("\t"," ",$xml);
	
	$xml_tmp=$xml;
	$xml_tmp=str_replace("<","[",$xml_tmp);
	$xml_tmp=str_replace(">","]",$xml_tmp);
	$xml_tmp=str_replace("][","]<br>[",$xml_tmp);
	//if(strlen($xml_tmp) > 500)		echo "<br>This is the XML included...debuging only:<br>".trim($xml_tmp)."<br>";
	
	//$prime_url=htmlentities($prime_url);
	
	$curl_handle=curl_init();		
	
	curl_setopt($curl_handle, CURLOPT_URL,$prime_url);
	//	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);	
	
	if(trim($xml)!="")
	{			
		//$xml=htmlentities($xml);	
		
		$post_cmd="service=".$cmd."&xml=".$xml."";
		//echo "<br>".$prime_url."?".$post_cmd."<br>XML File: <pre>".$xml."</pre><br>";		//<![CDATA[]]>
								
		curl_setopt($curl_handle, CURLOPT_POST,1);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); // we don't want to stop the page from loading if there is ever an issue with the SSL
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_cmd);
	}
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);	
	//echo "($buffer)";			
	//die('<br><br><br>'.$prime_url);
	return $buffer;
}

function decode_heading($heading)
{
	$arr[0]="N";
	$arr[1]="NE";
	$arr[2]="E";
	$arr[3]="SE";
	$arr[4]="S";
	$arr[5]="SW";
	$arr[6]="W";
	$arr[7]="NW";
	
	return 	$arr[$heading];
}
function decode_fix($fix)
{
	$arr[0]="Normal GPS";
	$arr[1]="Auto Position";
	$arr[2]="Vehicle Start";
	$arr[3]="Vehicle Stop";
	
	return 	$arr[$fix];
}
function decode_ignition($ignition)
{
	$arr[0]="Off";
	$arr[1]="On";
	
	return 	$arr[$ignition];
}

function mrr_peoplenet_form_message_not_raw($truck,$message,$urgent=0,$delivery=0,$force_it=0,$force_mode=0)
{
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];
	$pn_pw = $defaultsarray['peoplenet_account_password'];	
	
	$delivery_mode[0]="later";
	$delivery_mode[1]="now";
	
	$action_urgent[0]="no";
	$action_urgent[1]="yes";
	
	//reply mode and action
	$reply_mode[0]="reply_with_freeform";
	$reply_mode[1]="";	//"reply_with_new";
	$reply_mode[2]="reply_with_same";
	
	$reply_id_box="";		//$reply_id_box="&lt;reply_form_id&gt;10&lt;/reply_form_id&gt;";
	
	$force_reply="&lt;action&gt;".
					"&lt;action_type&gt;". $reply_mode[ $force_mode ] ."&lt;/action_type&gt;".
					"&lt;urgent_reply&gt;". $action_urgent[ $urgent ] ."&lt;/urgent_reply&gt;".
					"".$reply_id_box."".
				"&lt;/action&gt;";
	if($force_it==0)	$force_reply="";
	
	//freeform message
	$xml="&lt;?xml version='1.0' encoding='ISO-8859-1'?&gt;".
		"&lt;!DOCTYPE pnet_imessage_send PUBLIC '-//PeopleNet//pnet_imessage_send' 'http://open.pfmlogin.com/dtd/pnet_imessage_send.dtd'&gt;".
		"&lt;pnet_imessage_send&gt;".
			"&lt;cid&gt;".$pn_cid."&lt;/cid&gt;".
			"&lt;pw&gt;".$pn_pw."&lt;/pw&gt;".
			"&lt;vehicle_number&gt;".$truck."&lt;/vehicle_number&gt;".
			"&lt;deliver&gt;". $delivery_mode[ $delivery ] ."&lt;/deliver&gt;".
			"&lt;freeform_message&gt;".$message."&lt;/freeform_message&gt;".
		"&lt;/pnet_imessage_send&gt;";	//&lt;![CDATA[]]&gt;		".$force_reply."
	return $xml;
}

function mrr_peoplenet_dispatch_stopper_not_raw($truck,$delivery=0,$pn_disp_id="",$stop_num="")
{
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];
	$pn_pw = $defaultsarray['peoplenet_account_password'];	
	
	$delivery_mode[0]="later";
	$delivery_mode[1]="now";
	
	//remove a stop form PN dispatch
	$xml="&lt;?xml version='1.0' encoding='ISO-8859-1'?&gt;".
		"&lt;!DOCTYPE pnet_dispatch_edit PUBLIC '-//PeopleNet//pnet_dispatch_edit' 'http://open.pfmlogin.com/dtd/pnet_dispatch_edit.dtd'&gt;".
		"&lt;pnet_dispatch_edit&gt;".
			"&lt;cid&gt;".$pn_cid."&lt;/cid&gt;".
			"&lt;pw&gt;".$pn_pw."&lt;/pw&gt;".
			"&lt;vehicle_number&gt;".$truck."&lt;/vehicle_number&gt;".
			"&lt;deliver&gt;". $delivery_mode[ $delivery ] ."&lt;/deliver&gt;".
			"&lt;dispid&gt;".$pn_disp_id."&lt;/dispids&gt;".
			"&lt;remove_stops&gt;".
				"&lt;stopid&gt;".$stop_num."&lt;/stopid&gt;".
			"&lt;/remove_stops&gt;".
		"&lt;/pnet_dispatch_edit&gt;";	
	return $xml;
}
function mrr_peoplenet_form_id_message($truck,$message,$urgent=0,$delivery=0,$force_it=0,$force_mode=0,$form_id=0,$trailer=0)
{
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];
	$pn_pw = $defaultsarray['peoplenet_account_password'];	
	
	$message=str_replace(chr(10),"&#x0A;",$message);
	$message=str_replace(chr(13),"&#x0D;",$message);
	$message=str_replace("\r","&#x0D;",$message);
	$message=str_replace("\l","&#x0D;",$message);
	$message=str_replace("\n","&#x0D;",$message);
	$message=str_replace("\t","&#x0D;",$message);
	$message=str_replace("<br>","&#x0D;",$message);	//&#x0D; &#x0A;
	$message=str_replace("<p>","&#x0D;",$message);
	$message=str_replace("&nbsp;"," ",$message);
	//$message=str_replace("&","&amp;",$message);
	$message=trim($message);
	$message=strip_tags($message);
	//$message=htmlspecialchars($message, ENT_XML1);
	
	//$message="Test Message";
	
	$pdf_file="";	
	$pdf_date=date("m/d/y H:i",time());
	$pdf_key=createuuid();
	$trailer_name="";
	if($form_id==126268 && $trailer>0)
	{
		$sql2 = "
			select trailer_name,nick_name,trailer_regist_file
			from trailers
			where id='".sql_friendly($trailer) ."'
		";
		$data2 = simple_query($sql2);
		$mn2=mysqli_num_rows($data2);
		if($row2 = mysqli_fetch_array($data2))
		{
			$trailer_name=$row2['trailer_name'];
			$trailer_name=$row2['nick_name'];		
			if(trim($row2['trailer_regist_file'])!="")	
			{
				$pdf_file="https://trucking.conardtransportation.com/documents/".$row2['trailer_regist_file'];	
				$pdf_hdrs=get_headers($pdf_file);
				$pdf_key=$pdf_hdrs[4];
				$pdf_key=str_replace("ETag:","",$pdf_key);
				$pdf_key=str_replace(" ","",$pdf_key);
				$pdf_key=str_replace("'","",$pdf_key);
				$pdf_key=str_replace('"',"",$pdf_key);
			}
		}	
	}
	
	$message="Trailer ".$trailer_name.": ".$message;
	
	$delivery_mode[0]="later";
	$delivery_mode[1]="now";
	
	$action_urgent[0]="no";
	$action_urgent[1]="yes";
	
	//reply mode and action
	$reply_mode[0]="reply_with_freeform";
	$reply_mode[1]="";	//"reply_with_new";
	$reply_mode[2]="reply_with_same";
	
	$reply_id_box="";		//$reply_id_box="<reply_form_id>10</reply_form_id>";
	
	$force_reply="<action>".
					"<action_type>". $reply_mode[ $force_mode ] ."</action_type>".
					"<urgent_reply>". $action_urgent[ $urgent ] ."</urgent_reply>".
					"".$reply_id_box."".
				"</action>";
	if($force_it==0)	$force_reply="";
	
	//freeform message
	$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
		"<!DOCTYPE pnet_imessage_send PUBLIC '-//PeopleNet//pnet_imessage_send' 'http://open.pfmlogin.com/dtd/pnet_imessage_send.dtd'>".
		"<pnet_imessage_send>".
			"<cid><![CDATA[".$pn_cid."]]></cid>".
			"<pw><![CDATA[".$pn_pw."]]></pw>".
			"<vehicle_number><![CDATA[".$truck."]]></vehicle_number>".
			"<deliver><![CDATA[". $delivery_mode[ $delivery ] ."]]></deliver>".				
			"<formdata>".
				"<form_id>".$form_id."</form_id>".
				"<im_field>".
					"<question_number>1</question_number>".				
					"<data>".
						"<data_text><![CDATA[".$message."]]></data_text>".
					"</data>".
				"</im_field>".
				"<im_field>".
					"<question_number>2</question_number>".				
					"<data>".
						"<data_image_ref>".
               				"<data_image_date><![CDATA[".$pdf_date."]]></data_image_date>".
               				"<data_image_transid><![CDATA[".$pdf_key."]]></data_image_transid>".
							"<data_image_name><![CDATA[".$pdf_file."]]></data_image_name>".
							"<data_image_mimetype><![CDATA[application/pdf]]></data_image_mimetype>".
						"</data_image_ref>".
					"</data>".
				"</im_field>".					
			"</formdata>".
		"</pnet_imessage_send>";	
		/*
				"<im_field>".
					"<question_number>3</question_number>".				
					"<data>".
						"<data_date-time>".$pdf_date."</data_date-time>".
					"</data>".
				"</im_field>".
		*/
		//"<freeform_message><![CDATA[".$message."]]></freeform_message>".
		//<![CDATA[]]>		".$force_reply."
		
		//d($xml);
		
	if($pdf_file=="")		$xml="";
	return $xml;
}
function mrr_peoplenet_form_message($truck,$message,$urgent=0,$delivery=0,$force_it=0,$force_mode=0)
{
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];
	$pn_pw = $defaultsarray['peoplenet_account_password'];	
	
	$message=str_replace(chr(10),"&#x0A;",$message);
	$message=str_replace(chr(13),"&#x0D;",$message);
	$message=str_replace("\r","&#x0D;",$message);
	$message=str_replace("\l","&#x0D;",$message);
	$message=str_replace("\n","&#x0D;",$message);
	$message=str_replace("\t","&#x0D;",$message);
	$message=str_replace("<br>","&#x0D;",$message);	//&#x0D; &#x0A;
	$message=str_replace("<p>","&#x0D;",$message);
	$message=str_replace("&nbsp;"," ",$message);
	//$message=str_replace("&","&amp;",$message);
	$message=trim($message);
	$message=strip_tags($message);
	//$message=htmlspecialchars($message, ENT_XML1);
	
	//$message="Test Message";
	
	$delivery_mode[0]="later";
	$delivery_mode[1]="now";
	
	$action_urgent[0]="no";
	$action_urgent[1]="yes";
	
	//reply mode and action
	$reply_mode[0]="reply_with_freeform";
	$reply_mode[1]="";	//"reply_with_new";
	$reply_mode[2]="reply_with_same";
	
	$reply_id_box="";		//$reply_id_box="<reply_form_id>10</reply_form_id>";
	
	$force_reply="<action>".
					"<action_type>". $reply_mode[ $force_mode ] ."</action_type>".
					"<urgent_reply>". $action_urgent[ $urgent ] ."</urgent_reply>".
					"".$reply_id_box."".
				"</action>";
	if($force_it==0)	$force_reply="";
	
	//freeform message
	$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
		"<!DOCTYPE pnet_imessage_send PUBLIC '-//PeopleNet//pnet_imessage_send' 'http://open.pfmlogin.com/dtd/pnet_imessage_send.dtd'>".
		"<pnet_imessage_send>".
			"<cid><![CDATA[".$pn_cid."]]></cid>".
			"<pw><![CDATA[".$pn_pw."]]></pw>".
			"<vehicle_number><![CDATA[".$truck."]]></vehicle_number>".
			"<deliver><![CDATA[". $delivery_mode[ $delivery ] ."]]></deliver>".
			"<freeform_message><![CDATA[".$message."]]></freeform_message>".
		"</pnet_imessage_send>";	
		//<![CDATA[]]>		".$force_reply."
		
		//d($xml);
	return $xml;
}


function mrr_peoplenet_truck_dispatch_selector($field,$pre=0)
{
	$selbox="<select name='".$field."' id='".$field."' style='width:300px;'>";	
	$sel="";		if($pre==0)		$sel=" selected";	
	$selbox.="<option value='0'".$sel.">Dispatches</option>";
		 
	$sql = "
		select trucks.name_truck,
			trucks_log.truck_id,
			trucks_log.load_handler_id,
			trucks_log.id as dispatch_id
		from trucks_log,trucks
		where trucks.id=trucks_log.truck_id
			and trucks.deleted = 0
			and trucks.peoplenet_tracking=1
			and trucks_log.dispatch_completed=0
			and trucks_log.deleted=0
		order by trucks.name_truck asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		$sel="";		if($pre==$row['dispatch_id'])		$sel=" selected";	
		$selbox.="<option value='".$row['dispatch_id']."'".$sel.">Truck ".$row['name_truck'].": Load ".$row['load_handler_id'].": Dispatch".$row['dispatch_id']."</option>";
	}		
	$selbox.="</select>";
	return $selbox;	
}
function mrr_add_truck_tracking_dispatch_record($truck,$dispatch,$disp_stops,$peoplenet,$preplan=0)
{		
	global $datasource;

	$sql = "
		insert into ".mrr_find_log_database_name()."truck_tracking_dispatches
			(id,
			linedate_added,
			user_id,
			truck_id,
			dispatch_id,
			stops,
			linedate,
			peoplenet_id,
			preplan_use_load_id)
		values
			(NULL,
			NOW(),
			'".sql_friendly($_SESSION['user_id']) ."',
			'".sql_friendly($truck) ."',
			'".sql_friendly($dispatch) ."',
			'".sql_friendly($disp_stops)."',
			NOW(),
			'".sql_friendly($peoplenet) ."',
			'".sql_friendly($preplan)."')
	";
	
	simple_query($sql);		
	$newid=mysqli_insert_id($datasource);
	return $newid;	
}
function mrr_update_truck_tracking_dispatch_record($truck,$dispatch,$peoplenet)
{		
	$sql = "
		update ".mrr_find_log_database_name()."truck_tracking_dispatches set
			user_id='".sql_friendly($_SESSION['user_id']) ."',
			truck_id='".sql_friendly($truck) ."',
			dispatch_id='".sql_friendly($dispatch) ."',
			stops='".sql_friendly($disp_stops)."',
			linedate=NOW()				
		where peoplenet_id='".sql_friendly($peoplenet) ."'
	";		
	simple_query($sql);	
}

function mrr_peoplenet_store_message($message,$truck_id=0)
{			
	global $datasource;

	$message_id=0;	
	
	$tres['load_id']=0;
	$tres['dispatch_id']=0;
	$tres['driver_id']=0;
	
	if($truck_id > 0)	$tres=mrr_find_trucks_current_driver_load_dispatch($truck_id);
			
	if(trim($message)!="")
	{			
		$sql = "
			insert into ".mrr_find_log_database_name()."truck_tracking_messages
				(id,
				linedate_added,
				truck_id,
				user_id,
				linedate,
				message,
				load_id,
				dispatch_id,
				driver_id)
			values
				(NULL,
				NOW(),
				'".sql_friendly($truck_id) ."',
				'".sql_friendly($_SESSION['user_id']) ."',
				NOW(),
				'".sql_friendly( trim($message) ) ."',
				'".sql_friendly($tres['load_id']) ."',
				'".sql_friendly($tres['dispatch_id']) ."',
				'".sql_friendly($tres['driver_id']) ."')
		";
		simple_query($sql);
		$message_id=mysqli_insert_id($datasource);
	}
	return $message_id;
}
function mrr_send_peoplenet_complete_dispatch($find_load_id,$run_dispatch=0,$find_truck_id=0)
{
	global $defaultsarray;
     $pn_cid = $defaultsarray['peoplenet_account_number'];	
     $pn_pw = $defaultsarray['peoplenet_account_password'];
     $pn_cid=trim($pn_cid);
     $pn_pw=trim($pn_pw);
     
     $disp_cntr=0;
	$disp_arr[0]=0;
	$disp_pre[0]=0;
          
     $prime_url="http://open.pfmlogin.com/scripts/open.dll"; 
	
	$offset_gmt=mrr_gmt_offset_val(); 		//mrr_get_server_time_offset();
	$offset_gmt=$offset_gmt * -1;
	
	$disp_reports="";
	
	//see if there are multiple trucks to check..................................................................................................................................................     	
	$mres=mrr_find_peoplenet_trucks_on_load($find_load_id);
	$cntr=$mres['num'];
	$arr=$mres['arr'];
	$names=$mres['names'];
	$pn=$mres['pn'];
	
	$multi_truck="";
	if($cntr>1)
	{
		$multi_truck.="<table class='admin_menu3' border='0' cellpadding='0' cellspacing='0' width='1600'>";
		$multi_truck.="<tr>";
		$multi_truck.=		"<td valign='top'>Multiple Trucks on Load ".$find_load_id."<td>";
		
		for($x=0; $x < $cntr;$x++)
		{
			$pn_notice="";		if($pn[ $x ] > 0)	$pn_notice=" PeopleNet Active";
			
			$multi_truck.=		"<td valign='top'>Truck <a href='peoplenet_interface.php?find_load_id=".$find_load_id."&find_truck_id=".$arr[ $x ]."'>".$names[ $x ]."</a>".$pn_notice."<td>";	
		}
					
		$multi_truck.="</tr>";
		$multi_truck.="</table>";			
		$multi_truck.="<br>";
	}     	
	//...........................................................................................................................................................................................
	
	$truckname="1520428";
	if($find_truck_id==0)
	{
		$find_truck_id=1520428;
		$sql = "
     		select truck_id,name_truck 
     		from trucks_log,trucks 
     		where load_handler_id='".sql_friendly($find_load_id)."'
     			and trucks_log.truck_id=trucks.id
     		limit 1
     	";
     	$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$truckname="".trim($row['name_truck'])."";
			$find_truck_id=$row['truck_id'];	
		}
	}
	else
	{
		$sql = "
     		select name_truck 
     		from trucks 
     		where id='".sql_friendly($find_truck_id)."'
     	";
     	$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$truckname="".trim($row['name_truck'])."";
		}	
	}
	
	//settings.....................................................................................
	$voided[0]=0;			//place holder for general sevice functions.... skipped for dispatch create, remove and status checks.
	
	$arriving_radius=(int) $defaultsarray['peoplenet_geofencing_arriving'];	//5280;	// 1 miles
	$arrived_radius=(int) $defaultsarray['peoplenet_geofencing_arrived'];		//1320;	// 1/4 mile
	$departing_radius=(int) $defaultsarray['peoplenet_geofencing_departed'];	//2740;	// 1/2 mile
	
	$deliver=1;
	$call_on_start=1;
	$call_on_end=1;
	$enable_auto_start=1;
	$disable_driver_end=1;
	$detention_warning=0;	//15;
	
	$extra1="later";	if($deliver > 0)			$extra1="now";
	$extra2="";		if($call_on_start > 0)		$extra2="<call_on_start/>";
	$extra3="";		if($call_on_end > 0)		$extra3="<call_on_end/>";
	$extra4="";		if($enable_auto_start > 0)	$extra4="<enable_auto_start/>";
	$extra5="";		if($disable_driver_end > 0)	$extra5="<disable_driver_end/>";
	$extra6="";		if($detention_warning > 0)	$extra6="<detention_warning><interval>".$detention_warning."</interval><method>1</method></detention_warning>";	
	$extra7="";		//"<disable_auto_end/>";
	$extra8="";		//"<auto_start_driver_negative_guf/>";
	$extra9="<driver_negative_guf/>";
	
	$truckname=trim(str_replace(" (Team Rate)","",$truckname));
	
	$xcntr=0;
	
	$output="".$multi_truck."<table class='admin_menu3' border='0' cellpadding='0' cellspacing='0' width='1600'>
			<tr>
				<td valign='top' colspan='18'>
					<table border='0' cellpadding='0' cellspacing='0' width='100%'>
					<tr>
						<td valign='top'><span class='section_heading'>PeopleNet Dispatch(es)</span></td>
						
						<td valign='top'>Truck ID: (".$find_truck_id.") <input type='hidden' id='find_truck_id' name='find_truck_id' value='".$find_truck_id."'></td>
						<td valign='top'>Truck Name: ".$truckname." <input type='hidden' id='find_truck_name' name='find_truck_name' value='".$truckname."'></td>
						<td valign='top'>Load ID: ".$find_load_id." <input type='hidden' id='find_load_id' name='find_load_id' value='".$find_load_id."'></td>
						<td valign='top'>Process Dispatch ID: ".$run_dispatch." <input type='hidden' id='run_dispatch' name='run_dispatch' value='".$run_dispatch."'></td>
						
						<td valign='top' align='right'><input type='button' value='Check Status' onClick='mrr_get_dispatch();'></td>
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
				
				<td valign='top'><b>Latitude</b></td>
				<td valign='top'><b>Longitude</b></td>
				
				<td valign='top'><b>PN ID</b></td>
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
		
		$mrr_stop_cntr=0;
		$mrr_stop_list[0]=0;
		
		$sql="
			select load_handler_stops.*,
				(select pickup_number from load_handler where load_handler.id=load_handler_stops.load_handler_id) as pu_num,
				 trucks_log.truck_id,
				 trucks_log.trailer_id,
				 trucks_log.dropped_trailer,
				 trucks_log.miles,
				 trucks_log.miles_deadhead,
				 trucks_log.origin,
				 trucks_log.destination,
				 trucks_log.customer_id,
				 customers.name_company
			from load_handler_stops,
				trucks_log,
				customers
			where trucks_log.id=load_handler_stops.trucks_log_id
				and customers.id=trucks_log.customer_id
				and load_handler_stops.deleted=0
				and trucks_log.deleted=0
				and customers.deleted=0
				and trucks_log.truck_id='".sql_friendly($find_truck_id)."'
				and load_handler_stops.load_handler_id='".sql_friendly($find_load_id)."'
				and (load_handler_stops.linedate_completed IS NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
			order by trucks_log_id asc,load_handler_stops.linedate_pickup_eta asc, load_handler_stops.id asc		
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
			
			
			if($last_disp==0)	$last_disp=$row['trucks_log_id'];
			
			$new_offset_gmt=$row['timezone_offset'] + $row['timezone_offset_dst'];
			$new_offset_gmt=abs($new_offset_gmt/3600);		//convert to hours from local stop timezone
			
			if($new_offset_gmt==0)		$new_offset_gmt=abs($offset_gmt);
			
			//$stamp0=date("m/d/Y H:i",strtotime("".($offset_gmt-1)." hours",strtotime($att_window_starter)));		//trip start time
			//$stamp =date("m/d/Y H:i",strtotime("".($offset_gmt  )." hours",strtotime($att_window_ender)));		//arriving time     			
			//$stamp2=date("m/d/Y H:i",strtotime("".($offset_gmt+1)." hours",strtotime($att_window_ender)));		//arrived time
			//$stamp3=date("m/d/Y H:i",strtotime("".($offset_gmt+2)." hours",strtotime($att_window_ender)));		//departed time
			
			$stamp0=date("m/d/Y H:i",strtotime("".($new_offset_gmt-1)." hours",strtotime($att_window_starter)));	//trip start time
			$stamp =date("m/d/Y H:i",strtotime("".($new_offset_gmt  )." hours",strtotime($att_window_ender)));		//arriving time     			
			$stamp2=date("m/d/Y H:i",strtotime("".($new_offset_gmt )." hours",strtotime($att_window_ender)));		//arrived time
			$stamp3=date("m/d/Y H:i",strtotime("".($new_offset_gmt+1)." hours",strtotime($att_window_ender)));		//departed time
			     			
			$cust=mrr_get_all_customer_settings($row['customer_id']);	
			
			$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
			$hl_timer=$cust['hot_load_timer'];					//interval between messages
			//$hl_earriving=$cust['hot_load_email_arriving'];		//email addresses varchar
			//$hl_earrived=$cust['hot_load_email_arrived'];		//email addresses varchar
			//$hl_edeparted=$cust['hot_load_email_departed'];		//email addresses varchar
			//$hl_marriving=$cust['hot_load_email_msg_arriving'];	//email message text
			//$hl_marrived=$cust['hot_load_email_msg_arrived'];	//email message text
			//$hl_mdeparted=$cust['hot_load_email_msg_departed'];	//email message text
			$hl_r_arriving=$cust['hot_load_radius_arriving'];		//
			$hl_r_arrived=$cust['hot_load_radius_arrived'];		//
			$hl_r_departed=$cust['hot_load_radius_departed'];		//
			$hl_geo_active=$cust['geofencing_radius_active'];		//turn on actual geofencing notices
			$hl_geo_on_all=$cust['geofencing_hot_msg_all_loads'];	//all loads set to on...
			
			$arriving_radius=$hl_r_arriving;	
			$arrived_radius=$hl_r_arrived;
			$departing_radius=$hl_r_departed;     			
			     			     			
			$trailer_name=mrr_find_quick_trailer_name($row['trailer_id']);
			$t_drop="";
			if($row['dropped_trailer'] > 0)	$t_drop="-Drop";
			
			
			if($row['trucks_log_id']!=$last_disp)
			{
				$mrr_res=mrr_find_truck_tracking_dispatch_record($row['trucks_log_id'],0);
          		$peoplenet_id=$mrr_res['peoplenet_id'];
          		$saved_stops=$mrr_res['stops'];
          		               		
          		$disp_status="";
          		if($peoplenet_id>0)
          		{
					$disp_status=mrr_peoplenet_find_data2("pnet_dispatch_status",$voided,0,$peoplenet_id,0);	
				}
				
				$disp_status2=$disp_status;
				$disp_status2=str_replace("Arriving:","<br>-----Arriving:",$disp_status2);
				$disp_status2=str_replace("Arrived:", "<br>------Arrived:",$disp_status2);
				$disp_status2=str_replace("Departed:","<br>---Departed:",$disp_status2);
				$disp_status2=mrr_time_travel($disp_status2,  date("Y",strtotime($att_window_starter)) );
				//$disp_status2=str_replace("Time ","Time (GMT) ",$disp_status2);
				     				
				$output.="
					<tr>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='16'>".$disp_status2."</td>
						<td valign='top' align='right'><input type='button' value='Dispatch ".$last_disp."' onClick='mrr_run_dispatch(".$last_disp.");'></td>
					</tr>
				";	
				$disp_arr[$disp_cntr]=$last_disp;
				$disp_pre[$disp_cntr]=0;
				$disp_cntr++;		
          		          		
          		//if dispatch is already there, remove it first...
          		if($peoplenet_id>0)
          		{
          			$cmd_mode="pnet_dispatch_edit";	
          			
          			$stop_list="";
          			for($z=0; $z < $saved_stops; $z++)
          			{
          				$stop_list.="<stopid>".$z."</stopid>";	
          			}
          			
          			$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                         "<!DOCTYPE pnet_dispatch_edit PUBLIC '-//PeopleNet//pnet_dispatch_edit' 'http://open.pfmlogin.com/dtd/pnet_dispatch_edit.dtd'>".
                         "<pnet_dispatch_edit>".
                            	"<cid>".$pn_cid."</cid>".
          				"<pw><![CDATA[".$pn_pw."]]></pw>".
                       		"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                            	"<deliver>now</deliver>".
                              "<dispid>".$peoplenet_id."</dispid>".
                            	"<remove_stops>".
                              	"".$stop_list."".
                            	"</remove_stops>".
                         "</pnet_dispatch_edit>";
                                                  
                         $disp_reports.="<br>XML: ".$xml."<br>";
                         
                         mrr_capture_pn_dispatch_xml(2,$xml);
                         
          			$page=""; 
          			if($last_disp==$run_dispatch)
                    	{
                    		$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
                    	}
          			
          			if(substr_count($page,"<pnet_dispatch_edit_response>") > 0)
          			{
          				$disp_reports.="<br>Removed PeopleNet Dispatch ID ".$page2."."; 
          			}
          			
          		}
				
                    //add new dispatch             		
          		$cmd_mode="pnet_dispatch";
          		$dispatch_message=" ".$load_str."";
          		$dispatch_message2=" ".$load_str2."";
          		$first_stop_date=$stamp0;	
          		
				$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                    "<!DOCTYPE pnet_dispatch PUBLIC '-//PeopleNet//pnet_dispatch' 'http://open.pfmlogin.com/dtd/pnet_dispatch.dtd'>".
                    "<pnet_dispatch>".
                       	"<cid>".$pn_cid."</cid>".
          			"<pw><![CDATA[".$pn_pw."]]></pw>".
                       	"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                       	"<deliver>".$extra1."</deliver>".
                       	"<dispatch_name><![CDATA[Load ".$find_load_id.": Truck ".$truckname.": Trailer ".$trailer_name.": Miles ".$row['miles']." + ".$row['miles_deadhead']."DH:".( trim($row['name_company'])!="" ? " Cust:".trim($row['name_company'])."" : "" )."]]></dispatch_name>".
                       	"<dispatch_description><![CDATA[From ".$row['origin']." To ".$row['destination'].". ".$dispatch_message2."]]></dispatch_description>".
                       	"<dispatch_userdata><![CDATA[Load ".$find_load_id.": Dispatch ".$row['trucks_log_id']."]]></dispatch_userdata>".
                       	"<trip_data>".
                         	"<trip_start_time><![CDATA[".$first_stop_date."]]></trip_start_time>".
                         	"".$extra2."".
                         	"".$extra3."".
                         	"".$extra4."".
                         	"".$extra5."".
                         	"".$extra7."".
                         	"".$extra8."".
                       	"</trip_data>".
                       	"".$stops_xml."".
                    "</pnet_dispatch>";	
                    
                    $page="";	                  
                    if($last_disp==$run_dispatch)
                    {
                    	$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);                         	
                    }
                    
                    mrr_capture_pn_dispatch_xml(1,$xml);	
                    if(substr_count($page,"<pnet_dispatch_response>") > 0)
                    {
                    	$peoplenet_id=0;
                    	
                    	$page2="";
          			
          			$poser1=strpos($page,"<dispid>");
          			$poser2=strpos($page,"</dispid>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<dispid>","",$page2);					$page2=str_replace("</dispid>","",$page2);
          				$peoplenet_id=$page2;
          			}
                    	
                    	$disp_reports.="<br>Added PeopleNet Dispatch ID ".$peoplenet_id."."; 
                    	                       	
                    	$logged=mrr_add_truck_tracking_dispatch_record($find_truck_id,$row['trucks_log_id'],$disp_stops,$peoplenet_id,0);                    	
                    }
                    else
                    {
                    	$page2="";
          			
          			$poser1=strpos($page,"<pnet_response>");
          			$poser2=strpos($page,"</pnet_response>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<sendresult>","<b>Error:</b> ",$page2);		$page2=str_replace("</sendresult>","",$page2);
          				$page2=str_replace("<error_message>","",$page2);				$page2=str_replace("</error_message>","",$page2);
          			}
          			if(trim($page2)!="") 	$disp_reports.="<br><span class='alert'>ERROR: ".$page2.".</span>.";		
                    }                      
                    
                    //reset counter
				$disp_stops=0;	
				$stops_xml="";
				
				$last_disp=$row['trucks_log_id'];
			}
						
			if($disp_stops==0)	$first_stop_date=$stamp;	
						
			$stops++;
			$disp_stops++;
			$label="Stop ".$disp_stops.": Trailer ".$trailer_name."".$t_drop.": ".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')."";
			
			if($row['latitude']==0 || $row['longitude']==0)
			{	//not saved, get and save...
				$res=mrr_get_geocode_for_address($row['shipper_address1'],$row['shipper_address2'],$row['shipper_city'],$row['shipper_state'],$row['shipper_zip']);
				$latitude=$res['latitude'];
				$longitude=$res['longitude'];
				//$res['quality']=2;
								
				if($row['latitude']==0)		$row['latitude']=$latitude;
				if($row['longitude']==0)		$row['longitude']=$longitude;	
				
				$sql="
					update load_handler_stops set 
						latitude= '".sql_friendly($latitude)."',
						longitude='".sql_friendly($longitude)."'
					where load_handler_stops.id='".sql_friendly($row['id'])."'	
				";	//
				simple_query($sql);
			}
			
			$mrr_res=mrr_find_truck_tracking_dispatch_record($row['trucks_log_id'],0);
          	$peoplenet_id=$mrr_res['peoplenet_id'];
          	$saved_stops=$mrr_res['stops'];			
			
			$output.="
				<tr>
					<td valign='top'>".$row['trucks_log_id']."</td>
					<td valign='top'>".$row['id']."</td>
					<td valign='top'>".$truckname."</td>
					<td valign='top'>".$trailer_name."".$t_drop."</td>
					
					<td valign='top'>".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')."</td>
					<td valign='top'>".$row['name_company']."</td>
					<td valign='top'>".$row['stop_phone']."</td>
					
					<td valign='top'>".$row['shipper_name']."</td>
					<td valign='top'>".$row['shipper_address1']."</td>
					<td valign='top'>".$row['shipper_address2']."</td>
					<td valign='top'>".$row['shipper_city']."</td>
					<td valign='top'>".$row['shipper_state']."</td>
					<td valign='top'>".$row['shipper_zip']."</td>
										
					<td valign='top'>".$att_window_starter."</td>
					
					<td valign='top'>".$row['latitude']."</td>
					<td valign='top'>".$row['longitude']."</td>
					
					<td valign='top'>".$peoplenet_id."</td>
					<td valign='top'>".$saved_stops."</td>
				</tr>
			";
			$xcntr++;
			
			// ".$load_str."
			
			if($hl_geo_on_all > 0)
			{
				$load_id=$row['load_handler_id'];
     			$disp_id=$row['trucks_log_id'];
     			$stop_id=$row['id'];
     			$geo_id=0;
     			
     			$sql2="
               		select geofence_hot_load_tracking.id
               		from ".mrr_find_log_database_name()."geofence_hot_load_tracking
               		where geofence_hot_load_tracking.load_id='".sql_friendly($load_id)."'
               			and geofence_hot_load_tracking.dispatch_id='".sql_friendly($disp_id)."'
               			and geofence_hot_load_tracking.stop_id='".sql_friendly($stop_id)."'
               		limit 1	
          		";	
          		$data2=simple_query($sql2);
          		if($row2=mysqli_fetch_array($data2))
          		{
          			$geo_id=$row2['id'];     //this has been entered already, and/or modified, so do not remakle it...			
          		}
     			
     			if($geo_id==0)
     			{
     				mrr_add_this_stop_dispatch_load_geofencing($load_id,$disp_id,$stop_id);	//make one...	
     			}	
			}
			
			$mrr_stop_list[$mrr_stop_cntr]=$row['id'];
			$mrr_stop_cntr++;
			
																								
			$stops_xml.=
				"<stop>".     					
               		"<stop_head>".                  
               			"<stop_userdata><![CDATA[".$att_window_label."".$label."]]></stop_userdata>".
               			
               			"<custom_stop>".
         						"<name><![CDATA[".$row['shipper_name']."]]></name>".
         						"<description><![CDATA[".$att_window_label."Ph ".$row['stop_phone']."; ".(trim($row['pu_num'])!="" ? "PU-No:".$row['pu_num']." " :"")." INST: ".trim($row['directions'])." ADDR: ".$row['shipper_address1'].", ".$row['shipper_city'].", ".$row['shipper_state']." ".$row['shipper_zip'].".]]></description>".
         						"<latitude><![CDATA[".$row['latitude']."]]></latitude>".
         						"<longitude><![CDATA[".$row['longitude']."]]></longitude>".
       					"</custom_stop>". 
       					"<sequenced/>".                		
               		"</stop_head>".
               		"<advanced_actions>".
                           	"<arriving_action>".
                             		"<action_general>".
                               		"<radius_feet>".$arriving_radius."</radius_feet>".                    
                               		"<occur_by><![CDATA[".$stamp."]]></occur_by>".  
                               		"<call_on_occur/>".                           
                               		"<disp_message_on_late/>".                 
                             		"</action_general>".
                           	"</arriving_action>".
                           	"<arrived_action>".
                             		"<action_general>".
                               		"<radius_feet>".$arrived_radius."</radius_feet>".
                               		"<occur_by><![CDATA[".$stamp2."]]></occur_by>".
                               		"<call_on_occur/>".
                               		"<disp_message_on_late/>".
                             		"</action_general>".
                             		"".$extra9."".
                           	"</arrived_action>".
                           	"<departed_action>".
                             		"<action_general>".
                               		"<radius_feet>".$departing_radius."</radius_feet>".
                               		"<occur_by><![CDATA[".$stamp3."]]></occur_by>".
                               		"<call_on_occur/>".
                               		"<disp_message_on_late/>".
                             		"</action_general>".
                             		"".$extra9."".
                        		"</departed_action>".
                        	"</advanced_actions>".
             		"</stop>";
             		
               if($disp_stops>0 && $stops==$mn)
     		{     		
          		$mrr_res=mrr_find_truck_tracking_dispatch_record($row['trucks_log_id'],0);
          		$peoplenet_id=$mrr_res['peoplenet_id'];
          		$saved_stops=$mrr_res['stops'];
          		
          		$disp_status="";
          		if($peoplenet_id>0)
          		{
					$disp_status=mrr_peoplenet_find_data2("pnet_dispatch_status",$voided,0,$peoplenet_id,0);	
				}
				
				$disp_status2=$disp_status;
				$disp_status2=str_replace("Arriving:","<br>-----Arriving:",$disp_status2);
				$disp_status2=str_replace("Arrived:", "<br>------Arrived:",$disp_status2);
				$disp_status2=str_replace("Departed:","<br>---Departed:",$disp_status2);
				$disp_status2=mrr_time_travel($disp_status2,  date("Y",strtotime($att_window_starter)) );
				//$disp_status2=str_replace("Time ","Time (GMT) ",$disp_status2);
				
				$output.="
					<tr>
						<td valign='top'><input type='button' value='Cancel ".$last_disp."' onClick='mrr_cancel_dispatch(".$last_disp.");'></td>
						<td valign='top' colspan='16'>".$disp_status2."</td>
						<td valign='top' align='right'><input type='button' value='Dispatch ".$last_disp."' onClick='mrr_run_dispatch(".$last_disp.");'></td>
					</tr>
				";
				$disp_arr[$disp_cntr]=$last_disp;
				$disp_pre[$disp_cntr]=0;
				$disp_cntr++;
          		          		          		
          		//if dispatch is already there, remove it first...
          		if($peoplenet_id>0)
          		{
          			$cmd_mode="pnet_dispatch_edit";	
          			
          			$stop_list="";
          			for($z=0; $z < $saved_stops; $z++)
          			{
          				$stop_list.="<stopid>".$z."</stopid>";	
          			}
          			
          			$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                         "<!DOCTYPE pnet_dispatch_edit PUBLIC '-//PeopleNet//pnet_dispatch_edit' 'http://open.pfmlogin.com/dtd/pnet_dispatch_edit.dtd'>".
                         "<pnet_dispatch_edit>".
                            	"<cid>".$pn_cid."</cid>".
          				"<pw><![CDATA[".$pn_pw."]]></pw>".
                       		"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                            	"<deliver>now</deliver>".
                              "<dispid>".$peoplenet_id."</dispid>".
                            	"<remove_stops>".
                              	"".$stop_list."".
                            	"</remove_stops>".
                         "</pnet_dispatch_edit>";
                         
                         $disp_reports.="";//<br>XML: ".$xml."<br>";
                         
                         mrr_capture_pn_dispatch_xml(2,$xml);
          			$page=""; 
          			if($last_disp==$run_dispatch)
                    	{
                    		$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
                    			
                    	}
          			
          			if(substr_count($page,"<pnet_dispatch_edit_response>") > 0)
          			{
          				$disp_reports.="<br>Removed PeopleNet Dispatch ID ".$page2."."; 
          			}
          			
          		}
          		
          		//add new dispatch             		
          		$cmd_mode="pnet_dispatch";
          		$dispatch_message=" ".$load_str."";
          		$dispatch_message2=" ".$load_str2."";
          		
          		$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                    "<!DOCTYPE pnet_dispatch PUBLIC '-//PeopleNet//pnet_dispatch' 'http://open.pfmlogin.com/dtd/pnet_dispatch.dtd'>".
                    "<pnet_dispatch>".
                       	"<cid>".$pn_cid."</cid>".
          			"<pw><![CDATA[".$pn_pw."]]></pw>".
                       	"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                       	"<deliver>".$extra1."</deliver>".  
                       	"<dispatch_name><![CDATA[Load ".$find_load_id.": Truck ".$truckname.": Trailer ".$trailer_name.": Miles ".$row['miles']." + ".$row['miles_deadhead']."DH: ".  ( trim($row['name_company'])!="" ? " Cust:".trim($row['name_company'])."" : "" )  ."]]></dispatch_name>".
                       	"<dispatch_description><![CDATA[From ".$row['origin']." To ".$row['destination'].". ".$dispatch_message2."]]></dispatch_description>".
                       	"<dispatch_userdata><![CDATA[Load ".$find_load_id.": Dispatch ".$row['trucks_log_id']."]]></dispatch_userdata>".
                       	"<trip_data>".
                         	"<trip_start_time><![CDATA[".$first_stop_date."]]></trip_start_time>".
                         	"".$extra2."".
                              "".$extra3."".
                              "".$extra4."".
                              "".$extra5."".
                              "".$extra7."".
                         	"".$extra8."".
                       	"</trip_data>".
                       	"".$stops_xml."".
                    "</pnet_dispatch>";	
                    
                    $disp_reports.="";	//"<br>XML: ".$xml."<br>";                   
                    
                    $page="";		                 
                    if($last_disp==$run_dispatch)
                    {
                    	$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
                    		
                    }
                    mrr_capture_pn_dispatch_xml(1,$xml);
                    
                    if(substr_count($page,"<pnet_dispatch_response>") > 0)
                    {
                    	$peoplenet_id=0;
                    	
                    	$page2="";
          			
          			$poser1=strpos($page,"<dispid>");
          			$poser2=strpos($page,"</dispid>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<dispid>","",$page2);					$page2=str_replace("</dispid>","",$page2);
          				$peoplenet_id=$page2;
          			}
                    	
                    	$disp_reports.="<br>Added PeopleNet Dispatch ID ".$peoplenet_id."."; 
                    	                       	
                    	$logged=mrr_add_truck_tracking_dispatch_record($find_truck_id,$row['trucks_log_id'],$disp_stops,$peoplenet_id,0);  
                    	
                    	//update stops...
                    	for($v=0;$v < $mrr_stop_cntr;$v++)
                    	{
                    		$v_id=$mrr_stop_list[$v];
                    		$sql_v="
                    			update load_handler_stops set
                    				pn_dispatch_id='".sql_friendly($peoplenet_id)."',
                    				pn_stop_id='".sql_friendly($v)."'
                    			where id='".sql_friendly($v_id)."'
                    		";
                    		simple_query($sql_v);
                    	}   
                    	                         	
                    	//FOLLOW DISPATCH with a confirmation message     
                    	$notice_sent=mrr_check_if_dispatch_driver_confirm_email_sent($row['trucks_log_id']);
                    	if($notice_sent==0)
                    	{
                    		$_SESSION['peoplenet_new_msg_id']=0;
          				$canned_msg=mrr_fetch_special_canned_message(26,$find_load_id,$row['trucks_log_id']);	//26 is confirmation message...   
     					$disp_reports=mrr_peoplenet_find_data("imessage_send",$find_truck_id,0,$canned_msg,0,0,"",26);
     					$disp_reports=""; 
     					
     					$sql_uv="
                    			update trucks_log set
                    				driver_confirm_email_sent=1
                    			where id='".sql_friendly($row['trucks_log_id'])."'
                    		";
                    		simple_query($sql_uv);         	
     				}
                    }
                    else
                    {
                    	$page2="";
          			
          			$poser1=strpos($page,"<pnet_response>");
          			$poser2=strpos($page,"</pnet_response>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<sendresult>","<b>Error:</b> ",$page2);		$page2=str_replace("</sendresult>","",$page2);
          				$page2=str_replace("<error_message>","",$page2);				$page2=str_replace("</error_message>","",$page2);
          			}	
          			if(trim($page2)!="") 	$disp_reports.="<br><span class='alert'>ERROR: ".$page2.".</span>.";	
                    }                            
     		}
		}	//end main while loop
	}	//end if load_id>0 check
	
	$friendly_message="";
	if($xcntr==0)		$friendly_message="<div class='alert' align='center'><b>There are no current dispatch stops for this load and truck.  If multiple trucks are on this load, please click the truck above in the Multiple Truck section.</b></div>";
	    	
	$output.="
			<tr>
				<td valign='top' colspan='18'><br><br>".$disp_reports."".$friendly_message."</td>
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
	
	return $res;	
}

function mrr_send_peoplenet_complete_preplan_load($find_load_id,$find_driver_id,$find_truck_id,$run_preplan)
{
	global $defaultsarray;
     $pn_cid = $defaultsarray['peoplenet_account_number'];	
     $pn_pw = $defaultsarray['peoplenet_account_password'];
     $pn_cid=trim($pn_cid);
     $pn_pw=trim($pn_pw);
     
     $disp_cntr=0;
	$disp_arr[0]=0;
	$disp_pre[0]=0;
	
     $prime_url="http://open.pfmlogin.com/scripts/open.dll";
	
	$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
	$offset_gmt=$offset_gmt * -1;
	
	$disp_reports="";
	
	$truckname="1520428";
	$sql = "
     	select name_truck 
     	from trucks 
     	where id='".sql_friendly($find_truck_id)."'
     ";
     $data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$truckname="".trim($row['name_truck'])."";
	}
	
	//settings.....................................................................................
	$voided[0]=0;			//place holder for general sevice functions.... skipped for dispatch create, remove and status checks.
	
	
	$arriving_radius=(int) $defaultsarray['peoplenet_geofencing_arriving'];	//5280;	// 1 miles
	$arrived_radius=(int) $defaultsarray['peoplenet_geofencing_arrived'];		//1320;	// 1/4 mile
	$departing_radius=(int) $defaultsarray['peoplenet_geofencing_departed'];	//2740;	// 1/2 mile
	
	$deliver=1;
	$call_on_start=1;
	$call_on_end=1;
	$enable_auto_start=1;
	$disable_driver_end=1;
	$detention_warning=0;	//15;
	
	$extra1="later";	if($deliver > 0)			$extra1="now";
	$extra2="";		if($call_on_start > 0)		$extra2="<call_on_start/>";
	$extra3="";		if($call_on_end > 0)		$extra3="<call_on_end/>";
	$extra4="";		if($enable_auto_start > 0)	$extra4="<enable_auto_start/>";
	$extra5="";		if($disable_driver_end > 0)	$extra5="<disable_driver_end/>";
	$extra6="";		if($detention_warning > 0)	$extra6="<detention_warning><interval>".$detention_warning."</interval><method>1</method></detention_warning>";	
	$extra7="";		//"<disable_auto_end/>";
	$extra8="";		//"<auto_start_driver_negative_guf/>";
	$extra9="<driver_negative_guf/>";
	     	
	$peoplenet_id=0;
	
	$truckname=trim(str_replace(" (Team Rate)","",$truckname));
	
	$output="<table class='admin_menu3' border='0' cellpadding='0' cellspacing='0' width='1600'>
			<tr>
				<td valign='top' colspan='18'>
					<table border='0' cellpadding='0' cellspacing='0' width='100%'>
					<tr>
						<td valign='top'><span class='section_heading'>PeopleNet Dispatch(es): Preplan Loads</span></td>
						
						<td valign='top'>Truck ID: (".$find_truck_id.") <input type='hidden' id='find_truck_id' name='find_truck_id' value='".$find_truck_id."'></td>
						<td valign='top'>Truck Name: ".$truckname." <input type='hidden' id='find_truck_name' name='find_truck_name' value='".$truckname."'></td>
						<td valign='top'>Load ID: ".$find_load_id." <input type='hidden' id='find_load_id' name='find_load_id' value='".$find_load_id."'></td>
						<td valign='top'>Process Preplan ID: ".$run_preplan." <input type='hidden' id='run_preplan' name='run_preplan' value='".$run_preplan."'></td>
						
						<td valign='top' align='right'>
							<input type='button' value='Check Status' onClick='mrr_get_preplan();'>
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
				
				<td valign='top'><b>Latitude</b></td>
				<td valign='top'><b>Longitude</b></td>
				
				<td valign='top'><b>PN ID</b></td>
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
		     		
		$mrr_stop_cntr=0;
		$mrr_stop_list[0]=0;
		
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
				 customers.name_company
			from load_handler,
				load_handler_stops,
				customers
			where load_handler.id=load_handler_stops.load_handler_id
				and customers.id=load_handler.customer_id
				and load_handler_stops.deleted=0
				and load_handler.deleted=0
				and customers.deleted=0
				and load_handler_stops.load_handler_id='".sql_friendly($find_load_id)."'
				and (load_handler_stops.linedate_completed IS NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
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
			
			//$stamp0=date("m/d/Y H:i",strtotime("".($offset_gmt-1)." hours",strtotime($att_window_starter)));		//trip start time
			//$stamp =date("m/d/Y H:i",strtotime("".($offset_gmt  )." hours",strtotime($att_window_ender)));		//arriving time     			
			//$stamp2=date("m/d/Y H:i",strtotime("".($offset_gmt+1)." hours",strtotime($att_window_ender)));		//arrived time
			//$stamp3=date("m/d/Y H:i",strtotime("".($offset_gmt+2)." hours",strtotime($att_window_ender)));		//departed time
			
			$stamp0=date("m/d/Y H:i",strtotime("".($new_offset_gmt-1)." hours",strtotime($att_window_starter)));		//trip start time
			$stamp =date("m/d/Y H:i",strtotime("".($new_offset_gmt  )." hours",strtotime($att_window_ender)));		//arriving time     			
			$stamp2=date("m/d/Y H:i",strtotime("".($new_offset_gmt )." hours",strtotime($att_window_ender)));		//arrived time
			$stamp3=date("m/d/Y H:i",strtotime("".($new_offset_gmt+1)." hours",strtotime($att_window_ender)));		//departed time
			
			$cust=mrr_get_all_customer_settings($row['customer_id']);	
			
			$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
			$hl_timer=$cust['hot_load_timer'];					//interval between messages
			//$hl_earriving=$cust['hot_load_email_arriving'];		//email addresses varchar
			//$hl_earrived=$cust['hot_load_email_arrived'];		//email addresses varchar
			//$hl_edeparted=$cust['hot_load_email_departed'];		//email addresses varchar
			//$hl_marriving=$cust['hot_load_email_msg_arriving'];	//email message text
			//$hl_marrived=$cust['hot_load_email_msg_arrived'];	//email message text
			//$hl_mdeparted=$cust['hot_load_email_msg_departed'];	//email message text
			$hl_r_arriving=$cust['hot_load_radius_arriving'];		//
			$hl_r_arrived=$cust['hot_load_radius_arrived'];		//
			$hl_r_departed=$cust['hot_load_radius_departed'];		//
			$hl_geo_active=$cust['geofencing_radius_active'];		//turn on actual geofencing notices
			$hl_geo_on_all=$cust['geofencing_hot_msg_all_loads'];	//all loads set to on...
			
			$arriving_radius=$hl_r_arriving;	
			$arrived_radius=$hl_r_arrived;
			$departing_radius=$hl_r_departed; 
			  			
			$trailer_name="N/A";
			$t_drop="";
			     						
			if($disp_stops==0)	$first_stop_date=$stamp0;	
			$stops++;
			$disp_stops++;			
			
			$label="Stop ".$disp_stops.": ".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')."";
			
			if($row['latitude']==0 || $row['longitude']==0)
			{	//not saved, get and save...
				$res=mrr_get_geocode_for_address($row['shipper_address1'],$row['shipper_address2'],$row['shipper_city'],$row['shipper_state'],$row['shipper_zip']);
				$latitude=$res['latitude'];
				$longitude=$res['longitude'];
				//$res['quality']=2;
								
				if($row['latitude']==0)		$row['latitude']=$latitude;
				if($row['longitude']==0)		$row['longitude']=$longitude;	
				
				$sql="
					update load_handler_stops set 
						latitude= '".sql_friendly($latitude)."',
						longitude='".sql_friendly($longitude)."'
					where load_handler_stops.id='".sql_friendly($row['id'])."'	
				";	//
				simple_query($sql);
			}
			
			$mrr_res=mrr_find_truck_tracking_dispatch_record($find_load_id,1);
          	$peoplenet_id=$mrr_res['peoplenet_id'];
          	$saved_stops=$mrr_res['stops'];			
			
			$output.="
				<tr>
					<td valign='top'>&nbsp;</td>
					<td valign='top'>".$row['id']."</td>
					<td valign='top'>".$truckname."</td>
					<td valign='top'>&nbsp;</td>
					
					<td valign='top'>".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')."</td>
					<td valign='top'>".$row['name_company']."</td>
					<td valign='top'>".$row['stop_phone']."</td>
					
					<td valign='top'>".$row['shipper_name']."</td>
					<td valign='top'>".$row['shipper_address1']."</td>
					<td valign='top'>".$row['shipper_address2']."</td>
					<td valign='top'>".$row['shipper_city']."</td>
					<td valign='top'>".$row['shipper_state']."</td>
					<td valign='top'>".$row['shipper_zip']."</td>
										
					<td valign='top'>".$att_window_starter."</td>
					
					<td valign='top'>".$row['latitude']."</td>
					<td valign='top'>".$row['longitude']."</td>
					
					<td valign='top'>".$peoplenet_id."</td>
					<td valign='top'>".$saved_stops."</td>
				</tr>
			";
			// ".$load_str."
			
			if($hl_geo_on_all > 0)
			{
				$load_id=$row['load_handler_id'];
     			$disp_id=$row['trucks_log_id'];
     			$stop_id=$row['id'];
     			$geo_id=0;
     			
     			$sql2="
               		select geofence_hot_load_tracking.id
               		from ".mrr_find_log_database_name()."geofence_hot_load_tracking
               		where geofence_hot_load_tracking.load_id='".sql_friendly($load_id)."'
               			and geofence_hot_load_tracking.dispatch_id='".sql_friendly($disp_id)."'
               			and geofence_hot_load_tracking.stop_id='".sql_friendly($stop_id)."'
               		limit 1	
          		";	
          		$data2=simple_query($sql2);
          		if($row2=mysqli_fetch_array($data2))
          		{
          			$geo_id=$row2['id'];     //this has been entered already, and/or modified, so do not remakle it...			
          		}
     			
     			if($geo_id==0)
     			{
     				mrr_add_this_stop_dispatch_load_geofencing($load_id,$disp_id,$stop_id);	//make one...	
     			}	
			}
			
			$mrr_stop_list[$mrr_stop_cntr]=$row['id'];
			$mrr_stop_cntr++;
			
			$stops_xml.=
				"<stop>".
               		"<stop_head>".                  
               			"<stop_userdata><![CDATA[".$att_window_label." ".$label."]]></stop_userdata>".
               			"<custom_stop>".
         						"<name><![CDATA[".$row['shipper_name']."]]></name>".
         						"<description><![CDATA[".$att_window_label."Ph ".$row['stop_phone']."; ".(trim($row['pu_num'])!="" ? "PU-No:".$row['pu_num']." " :"")." ".$row['shipper_address1'].", ".$row['shipper_city'].", ".$row['shipper_state']." ".$row['shipper_zip'].". ".$row['directions'].".]]></description>".
         						"<latitude><![CDATA[".$row['latitude']."]]></latitude>".
         						"<longitude><![CDATA[".$row['longitude']."]]></longitude>".
       					"</custom_stop>". 
       					"<sequenced/>".                    		
               		"</stop_head>".
               		"<advanced_actions>".
                           	"<arriving_action>".
                             		"<action_general>".
                               		"<radius_feet>".$arriving_radius."</radius_feet>".                    
                               		"<occur_by><![CDATA[".$stamp."]]></occur_by>".  
                               		"<call_on_occur/>".                           
                               		"<disp_message_on_late/>".                 
                             		"</action_general>".
                           	"</arriving_action>".
                           	"<arrived_action>".
                             		"<action_general>".
                               		"<radius_feet>".$arrived_radius."</radius_feet>".
                               		"<occur_by><![CDATA[".$stamp2."]]></occur_by>".
                               		"<call_on_occur/>".
                               		"<disp_message_on_late/>".
                             		"</action_general>".
                             		"".$extra9."".
                           	"</arrived_action>".
                           	"<departed_action>".
                             		"<action_general>".
                               		"<radius_feet>".$departing_radius."</radius_feet>".
                               		"<occur_by><![CDATA[".$stamp3."]]></occur_by>".
                               		"<call_on_occur/>".
                               		"<disp_message_on_late/>".
                             		"</action_general>".
                             		"".$extra9."".
                        		"</departed_action>".
                        	"</advanced_actions>".
             		"</stop>";
             		
               if($disp_stops>0 && $stops==$mn)
     		{     		
          		$mrr_res=mrr_find_truck_tracking_dispatch_record($find_load_id,1);
          		$peoplenet_id=$mrr_res['peoplenet_id'];
          		$saved_stops=$mrr_res['stops'];
          		
          		$disp_status="";
          		if($peoplenet_id>0)
          		{
					$disp_status=mrr_peoplenet_find_data2("pnet_dispatch_status",$voided,0,$peoplenet_id,0);	
				}
				
				$disp_status2=$disp_status;
				$disp_status2=str_replace("Arriving:","<br>-----Arriving:",$disp_status2);
				$disp_status2=str_replace("Arrived:", "<br>------Arrived:",$disp_status2);
				$disp_status2=str_replace("Departed:","<br>---Departed:",$disp_status2);
				$disp_status2=mrr_time_travel($disp_status2,  date("Y",strtotime($att_window_starter)) );
				//$disp_status2=str_replace("Time ","Time (GMT) ",$disp_status2);
				
				$output.="
					<tr>
						<td valign='top'><input type='button' value='Cancel ".$find_load_id."' onClick='mrr_cancel_preplan(".$find_load_id.");'></td>
						<td valign='top' colspan='16'>".$disp_status2."</td>
						<td valign='top' align='right'><input type='button' value='Preplan Load ".$find_load_id."' onClick='mrr_run_preplan(".$find_load_id.");'></td>
					</tr>
				";
				$disp_arr[$disp_cntr]=$find_load_id;
				$disp_pre[$disp_cntr]=1;
				$disp_cntr++;
	
          		          		          		
          		//if dispatch is already there, remove it first...
          		if($peoplenet_id>0)
          		{
          			$cmd_mode="pnet_dispatch_edit";	
          			
          			$stop_list="";
          			for($z=0; $z < $saved_stops; $z++)
          			{
          				$stop_list.="<stopid>".$z."</stopid>";	
          			}
          			
          			$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                         "<!DOCTYPE pnet_dispatch_edit PUBLIC '-//PeopleNet//pnet_dispatch_edit' 'http://open.pfmlogin.com/dtd/pnet_dispatch_edit.dtd'>".
                         "<pnet_dispatch_edit>".
                            	"<cid>".$pn_cid."</cid>".
          				"<pw><![CDATA[".$pn_pw."]]></pw>".
                       		"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                            	"<deliver>now</deliver>".
                              "<dispid>".$peoplenet_id."</dispid>".
                            	"<remove_stops>".
                              	"".$stop_list."".
                            	"</remove_stops>".
                         "</pnet_dispatch_edit>";
                         
                         $disp_reports.="";//<br>XML: ".$xml."<br>";
                         
          			$page=""; 
          			if($find_load_id==$run_preplan)
                    	{	
                    		$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);
                    			
                    	}
          			mrr_capture_pn_dispatch_xml(2,$xml);	
          			
          			if(substr_count($page,"<pnet_dispatch_edit_response>") > 0)
          			{
          				$disp_reports.="<br>Removed PeopleNet Preplan Load ID ".$page2."."; 
          			}
          			
          		}
          		
          		//add new dispatch             		
          		$cmd_mode="pnet_dispatch";
          		$dispatch_message=" ".$load_str."";
          		$dispatch_message2=" ".$load_str2."";
          		
          		$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                    "<!DOCTYPE pnet_dispatch PUBLIC '-//PeopleNet//pnet_dispatch' 'http://open.pfmlogin.com/dtd/pnet_dispatch.dtd'>".
                    "<pnet_dispatch>".
                       	"<cid>".$pn_cid."</cid>".
          			"<pw><![CDATA[".$pn_pw."]]></pw>".
                       	"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                       	"<deliver>".$extra1."</deliver>".  
                       	"<dispatch_name><![CDATA[Load ".$find_load_id.": Truck ".$truckname.": Miles ".$row['estimated_miles']." + ".$row['deadhead_miles']."DH:".  ( trim($row['name_company'])!="" ? " Cust:".trim($row['name_company'])."" : "" )  ."]]></dispatch_name>".
                       	"<dispatch_description><![CDATA[From ".$row['origin_city'].", ".$row['origin_state']." To ".$row['dest_city'].", ".$row['dest_state'].". ".$dispatch_message2."]]></dispatch_description>".
                       	"<dispatch_userdata><![CDATA[Load ".$find_load_id.": Preplan Load ".$find_load_id."]]></dispatch_userdata>".
                       	"<trip_data>".
                         	"<trip_start_time><![CDATA[".$first_stop_date."]]></trip_start_time>".
                         	"".$extra2."".
                              "".$extra3."".
                              "".$extra4."".
                              "".$extra5."".
                              "".$extra7."".
                         	"".$extra8."".
                       	"</trip_data>".
                       	"".$stops_xml."".
                    "</pnet_dispatch>";	
                    
                    $disp_reports.="";	//"<br>XML: ".$xml."<br>";                   
                    
                    $page="";		                 
                    if($find_load_id==$run_preplan)
                    {
                    	$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
                    		
                    }
                    mrr_capture_pn_dispatch_xml(1,$xml);
                    
                    if(substr_count($page,"<pnet_dispatch_response>") > 0)
                    {
                    	$peoplenet_id=0;
                    	
                    	$page2="";
          			
          			$poser1=strpos($page,"<dispid>");
          			$poser2=strpos($page,"</dispid>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<dispid>","",$page2);					$page2=str_replace("</dispid>","",$page2);
          				$peoplenet_id=$page2;
          			}
                    	
                    	$disp_reports.="<br>Added PeopleNet Preplan Load ID ".$peoplenet_id."."; 
                    	                       	
                    	$logged=mrr_add_truck_tracking_dispatch_record($find_truck_id,$find_load_id,$disp_stops,$peoplenet_id,1); //load ID takes place of dispatch ID in preplanned loads... no dispatches yet.  
                    	
                    	//update stops...
                    	for($v=0;$v < $mrr_stop_cntr;$v++)
                    	{
                    		$v_id=$mrr_stop_list[$v];
                    		$sql_v="
                    			update load_handler_stops set
                    				pn_dispatch_id='".sql_friendly($peoplenet_id)."',
                    				pn_stop_id='".sql_friendly($v)."'
                    			where id='".sql_friendly($v_id)."'
                    		";
                    		simple_query($sql_v);
                    	}  
                    	
                    	                         	
                    	//FOLLOW DISPATCH with a confirmation message 
                    	$notice_sent=mrr_check_if_dispatch_driver_confirm_email_sent(0); 		//DONT SEND TO PREPLANNED...
                    	if($notice_sent==0 && 1==2)
                    	{  
                    		$_SESSION['peoplenet_new_msg_id']=0;
          				$canned_msg=mrr_fetch_special_canned_message(26,$find_load_id,0);	//26 is confirmation message...   
     					$disp_reports=mrr_peoplenet_find_data("imessage_send",$find_truck_id,0,$canned_msg,0,0,"",26);
     					$disp_reports="";
     					
     					$sql_uv="
                    			update trucks_log set
                    				driver_confirm_email_sent=1
                    			where id='".sql_friendly(0)."'
                    		";
                    		simple_query($sql_uv); 
     				}                  	
                    }
                    else
                    {
                    	$page2="";
          			
          			$poser1=strpos($page,"<pnet_response>");
          			$poser2=strpos($page,"</pnet_response>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<sendresult>","<b>Error:</b> ",$page2);		$page2=str_replace("</sendresult>","",$page2);
          				$page2=str_replace("<error_message>","",$page2);				$page2=str_replace("</error_message>","",$page2);
          			}	
          			if(trim($page2)!="") 	$disp_reports.="<br><span class='alert'>ERROR: ".$page2.".</span>.";	
                    }                            
     		}
		}	//end main while loop
	}	//end if load_id>0 check
	
	$output.="
			<tr>
				<td valign='top' colspan='18'><br><br>".$disp_reports."</td>
			</tr>
		</table>
		<br>";
	     	
	$res['truck_id']=$find_truck_id;
	$res['truck_name']=$truckname;
	$res['dispatch_id']=$peoplenet_id;
	$res['dispatch_cntr']=$disp_cntr;
	$res['disp_arr']=$disp_arr;
	$res['disp_pre']=$disp_pre;
	$res['output']=$output;
	
	return $res;	
}

//cancel dispatch and preplan loads
function mrr_cancel_peoplenet_complete_dispatch($load_id,$run_dispatch=0,$find_truck_id=0)
{
	global $defaultsarray;
     $pn_cid = $defaultsarray['peoplenet_account_number'];	
     $pn_pw = $defaultsarray['peoplenet_account_password'];
     $pn_cid=trim($pn_cid);
     $pn_pw=trim($pn_pw);
     
     $prime_url="http://open.pfmlogin.com/scripts/open.dll";
	
	$disp_reports="";
	
	$truckname="1520428";
	
	$sql = "
		select name_truck 
		from trucks 
		where id='".sql_friendly($find_truck_id)."'
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$truckname="".trim($row['name_truck'])."";
	}	
	
	//if there is a dispatch, remove it from peoplenet dispatches
	if($run_dispatch > 0)
	{		
		$mrr_res=mrr_find_truck_tracking_dispatch_record($run_dispatch,0);
     	$peoplenet_id=$mrr_res['peoplenet_id'];
     	$saved_stops=$mrr_res['stops'];          
     	
     	//found a PeopleNet ID, so we should be able to remove it with all of its stops.   This is a dispatch ID, but from PEOPLENET
     	if($peoplenet_id > 0)
     	{
     		$cmd_mode="pnet_dispatch_edit";	
          	
          	//get stops		
			$stop_list="";
			for($z=0; $z < $saved_stops; $z++)
			{
				$stop_list.="<stopid>".$z."</stopid>";	
			}
			
			//build XML
			$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
               "<!DOCTYPE pnet_dispatch_edit PUBLIC '-//PeopleNet//pnet_dispatch_edit' 'http://open.pfmlogin.com/dtd/pnet_dispatch_edit.dtd'>".
               "<pnet_dispatch_edit>".
                  	"<cid>".$pn_cid."</cid>".
				"<pw><![CDATA[".$pn_pw."]]></pw>".
             		"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                  	"<deliver>now</deliver>".
                    "<dispid>".$peoplenet_id."</dispid>".
                  	"<remove_stops>".
                    	"".$stop_list."".
                  	"</remove_stops>".
               "</pnet_dispatch_edit>";
               
			$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
			
			if(substr_count($page,"<pnet_dispatch_edit_response>") > 0)
			{	//response found, so flag it as canceled...
				mrr_cancel_this_load_dispatch($run_dispatch,0);
			}
	     			
			//now send canned message with basic load/dispatch info... only..
			$canned_msg="";
			
			$sql = "
          		select *
          		from trucks_log 
          		where truck_id='".sql_friendly($find_truck_id)."'
	              		and load_handler_id='".sql_friendly($load_id)."'
          			and id='".sql_friendly($run_dispatch)."'
          	";
          	$data=simple_query($sql);
     		while($row=mysqli_fetch_array($data))
     		{          			
     			$canned_msg.="CANCELED DISPATCH: Load ".$load_id." Dispatch ".$run_dispatch.".  ".$row['origin'].", ".$row['origin_state']." to ".$row['destination'].", ".$row['destination_state'].
     							" has been canceled.  Please await further instructions or your next load.";
         		}
			
			$cmd_mode="imessage_send";
			$_SESSION['peoplenet_new_msg_id']=0;
			$disp_reports=mrr_peoplenet_find_data($cmd_mode,$find_truck_id,0,$canned_msg,0,0);
			$disp_reports="";
     	}	//end peoplenet dispatch check
	}    //end conard dispatch check	
	
}
function mrr_cancel_peoplenet_complete_preplan_load($find_load_id,$find_truck_id=0,$run_preplan=0)
{
	global $defaultsarray;
     $pn_cid = $defaultsarray['peoplenet_account_number'];	
     $pn_pw = $defaultsarray['peoplenet_account_password'];
     $pn_cid=trim($pn_cid);
     $pn_pw=trim($pn_pw);
     
     $prime_url="http://open.pfmlogin.com/scripts/open.dll";
	
	$disp_reports="";
	
	$truckname="1520428";
	$sql = "
     	select name_truck 
     	from trucks 
     	where id='".sql_friendly($find_truck_id)."'
     ";
     $data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$truckname="".trim($row['name_truck'])."";
	}
	
	//if there is a dispatch...in this case a preplanned load ID, remove it from peoplenet dispatches where [ $find_load_id       "should equal"        $run_preplan ]
	if($run_preplan > 0)
	{		
		$mrr_res=mrr_find_truck_tracking_dispatch_record($find_load_id,1);
     	$peoplenet_id=$mrr_res['peoplenet_id'];
     	$saved_stops=$mrr_res['stops'];          
     	
     	//found a PeopleNet ID, so we should be able to remove it with all of its stops.   This is a dispatch ID, but from PEOPLENET
     	if($peoplenet_id > 0)
     	{
     		$cmd_mode="pnet_dispatch_edit";	
          	
          	//get stops		
			$stop_list="";
			for($z=0; $z < $saved_stops; $z++)
			{
				$stop_list.="<stopid>".$z."</stopid>";	
			}
			
			//build XML
			$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
               "<!DOCTYPE pnet_dispatch_edit PUBLIC '-//PeopleNet//pnet_dispatch_edit' 'http://open.pfmlogin.com/dtd/pnet_dispatch_edit.dtd'>".
               "<pnet_dispatch_edit>".
                  	"<cid>".$pn_cid."</cid>".
				"<pw><![CDATA[".$pn_pw."]]></pw>".
             		"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                  	"<deliver>now</deliver>".
                    "<dispid>".$peoplenet_id."</dispid>".
                  	"<remove_stops>".
                    	"".$stop_list."".
                  	"</remove_stops>".
               "</pnet_dispatch_edit>";
               
			$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
			
			if(substr_count($page,"<pnet_dispatch_edit_response>") > 0)
			{	//response found, so flag it as canceled...
				mrr_cancel_this_load_dispatch($run_preplan,1);
			}
	     			
			//now send canned message with basic load/dispatch info... only..
			$canned_msg="";
			
			$sql = "
          		select *
          		from load_handler 
          		where id='".sql_friendly($find_load_id)."'
          	";
          	$data=simple_query($sql);
     		while($row=mysqli_fetch_array($data))
     		{          			
     			$canned_msg.="CANCELED PREPLAN LOAD: Load ".$find_load_id.".  ".$row['origin_city'].", ".$row['origin_state']." to ".$row['dest_city'].", ".$row['dest_state'].
     						" has been canceled.  Please await further instructions or your next load.";
         		}
			$cmd_mode="imessage_send";
			$_SESSION['peoplenet_new_msg_id']=0;
			$disp_reports=mrr_peoplenet_find_data($cmd_mode,$find_truck_id,0,$canned_msg,0,0);
			$disp_reports="";
		}	//end peoplenet dispatch check
	}    //end conard preplan load check	    	
	
}
	
function mrr_peoplenet_link_class_by_date($truck_id=0,$dispatch_id=0,$preplan=0)
{
	$classer="peoplenet_link_not_sent";
	//$classer="peoplenet_link_sent";
	
	$sql="";		
	if($preplan==1)
	{	//preplan loads...skip dispatch...use dispatch as load_id and go to the stops...
		$sql="
			select truck_tracking_dispatches.linedate,
                 IFNULL(
                   (select 
                     IFNULL(
                       load_handler_stops.linedate_updater,
                       '0000-00-00 00:00:00'
                     ) > IFNULL(
                       truck_tracking_dispatches.linedate,
                       '0000-00-00 00:00:00'
                     ) 
                   from load_handler_stops 
                   where load_handler_stops.load_handler_id = truck_tracking_dispatches.dispatch_id 
                     and load_handler_stops.deleted = 0
                    order by load_handler_stops.linedate_updater desc
                    limit 1),
                   0
                 ) as updated_later 
			from ".mrr_find_log_database_name()."truck_tracking_dispatches 
			where truck_tracking_dispatches.preplan_use_load_id='1' 
				and truck_tracking_dispatches.dispatch_id='".sql_friendly($dispatch_id)."'
			order by truck_tracking_dispatches.linedate desc 
			limit 1
		";
	}
	else
	{
		$sql="
			select truck_tracking_dispatches.linedate,
                 IFNULL(
                   (select 
                     IFNULL(
                       trucks_log.linedate_updated,
                       '0000-00-00 00:00:00'
                     ) > IFNULL(
                       truck_tracking_dispatches.linedate,
                       '0000-00-00 00:00:00'
                     ) 
                   from trucks_log 
                   where trucks_log.id = truck_tracking_dispatches.dispatch_id 
                     and trucks_log.deleted = 0
                   order by trucks_log.linedate_updated desc
                   limit 1),
                   0
                 ) as updated_later 
			from ".mrr_find_log_database_name()."truck_tracking_dispatches 
			where truck_tracking_dispatches.dispatch_id='".sql_friendly($dispatch_id)."'
			order by truck_tracking_dispatches.linedate desc 
			limit 1
		";
	}
	$data = simple_query($sql);
	$mn=mysqli_num_rows($data);
	if($row=mysqli_fetch_array($data))
	{
		$classer="peoplenet_link_sent";
		
		$dated=date("m/d/Y H:i",strtotime($row['linedate']));
		$updated=$row['updated_later'];
		if($updated > 0)	$classer="peoplenet_link_update";
	}	
		
	return $classer;
}


function mrr_get_past_dispatches_sent_by_truck($date_from, $date_to, $truck_id=0, $truck_name="",$limit=0, $archived=0,$mode=0)
{	//dispatches sent to PN for given truck...  MODE is not used yet.
	global $new_style_path;
	global $defaultsarray;
	
	$date_range_msg_history=" and truck_tracking_dispatches.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking_dispatches.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	
	$lim_txt="";
	if($limit>0)	$lim_txt=" limit ".$limit."";
	
	$archiver=" and truck_tracking_dispatches.linedate_added>='".date("Y-m-01",strtotime("-60 day",time()))." 00:00:00'";		//only go fro mthe beginning of the current month
	if($archived > 0)	$archiver="";	//$archiver=" and truck_tracking_dispatches.linedate_added>='".date("Y-01-01",time())." 00:00:00'";		//archive goes back to beginning of year
			
	$mcntr=0; 
	$tab="";	
		
	$sql3 = "
		select truck_tracking_dispatches.*,
			trucks_log.load_handler_id,
			trucks_log.origin,
			trucks_log.origin_state,
			trucks_log.destination,
			trucks_log.destination_state,
			trucks_log.linedate_pickup_eta,
			customers.name_company,
			drivers.name_driver_first as driver_first_name,
			drivers.name_driver_last as driver_last_name,
			trailers.trailer_name
			
		from ".mrr_find_log_database_name()."truck_tracking_dispatches
			left join trucks_log on trucks_log.id=truck_tracking_dispatches.dispatch_id
			left join customers on customers.id=trucks_log.customer_id
			left join trailers on trailers.id=trucks_log.trailer_id
			left join drivers on drivers.id=trucks_log.driver_id
		where truck_tracking_dispatches.truck_id='".sql_friendly($truck_id) ."'
			".$date_range_msg_history."
			".$archiver."
		order by truck_tracking_dispatches.linedate_added desc
		".$lim_txt."
	";
	$data3 = simple_query($sql3);
	$mn3=mysqli_num_rows($data3);	
	if($mn3>0)
	{
		$tab.="<tr>
				<td valign='top'><b>Load</b></td>
				<td valign='top'><b>Dispatch</b></td>
				<td valign='top'><b>User</b></td>
				<td valign='top'><b>Sent</b></td>
				<td valign='top'><b>Date</b></td>
				<td valign='top'><b>PN ID</b></td>
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
	}		
	
	while($row3 = mysqli_fetch_array($data3))
	{
		$read_user=mrr_peoplenet_pull_quick_username($row3['user_id']);
		$sent_date=date("m/d/Y H:i:s",strtotime($row3['linedate_added']));			if($row3['linedate_added']=="0000-00-00 00:00:00") 	$sent_date="";
		$disp_date=date("m/d/Y H:i:s",strtotime($row3['linedate']));				if($row3['linedate']=="0000-00-00 00:00:00") 		$disp_date="";
		$driver=$row3['driver_first_name']." ".$row3['driver_last_name']; 
		
		$pick_date=date("m/d/Y H:i:s",strtotime($row3['linedate_pickup_eta']));			
		
		$alink="<a href='manage_load.php?load_id=".$row3['load_handler_id']."' target='_blank'>".$row3['load_handler_id']."</a>";
		$blink="<a href='add_entry_truck.php?load_id=".$row3['load_handler_id']."&id=".$row3['dispatch_id']."' target='_blank'>".$row3['dispatch_id']."</a>";
		if($row3['preplan_use_load_id'] > 0)
		{
			$alink="<a href='manage_load.php?load_id=".$row3['dispatch_id']."' target='_blank'>".$row3['dispatch_id']."</a>";
			$blink="<b>Preplan</b>";		
		}
								
		$tab.="<tr>
				<td valign='top'>".$alink."</td>
				<td valign='top'>".$blink."</td>
				<td valign='top'>".$read_user."</td>
				<td valign='top'>".$sent_date."</td>
				<td valign='top'>".$disp_date."</td>
				<td valign='top'>".$row3['peoplenet_id']."</td>
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
		$mcntr++;					
	}		
	if($mcntr==0)
	{
		$tab="<tr><td valign='top' colspan='15'>No dispatches found.</td></tr>";	
	}
	//$tab.="<tr><td valign='top' colspan='15'>Query: ".$sql3.".</td></tr>";	
					
	return $tab;
}

function mrr_get_messages_sent_by_truck($date_from, $date_to, $truck_id=0, $truck_name="",$limit=0, $archived=0,$mode=0)
{	//messages pulled from packets
	global $new_style_path;
	global $defaultsarray;
	
	$date_range_msg_history=" and truck_tracking_msg_history.linedate_created>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking_msg_history.linedate_created<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	
	$lim_txt="";
	if($limit>0)	$lim_txt=" limit ".$limit."";
	
	$archiver=" and archived='".sql_friendly($archived)."'";
	
	$timezoning=trim($defaultsarray['gmt_offset_label']);
	
	$date_range_msg_history3=" and twilio_call_log.linedate_added>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and twilio_call_log.linedate_added<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";
	$mrr_adder3="";
			
	$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
	//$offset_gmt=$offset_gmt * -1;
			
	//send and stored messages from Peoplenet Interface
	$mcntr=0; 
	$mcntr3=0;
	$tab="";		//
	$tab2="";		//load board version
	
	$tab3="";		//load board  simple version
			
	$sql3 = "
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
			truck_tracking_msg_history.linedate_added as my_linedate_added,
			truck_tracking_msg_history.linedate_received as my_linedate_received,
			truck_tracking_msg_history.alert_sent_flag as alert_sent,	
			(select name_driver_first from drivers where drivers.id=truck_tracking_msg_history.driver_id) as driver_first,	
			(select name_driver_last from drivers where drivers.id=truck_tracking_msg_history.driver_id) as driver_last,
			'Sent' as mrr_mode
			
		from ".mrr_find_log_database_name()."truck_tracking_msg_history
		where truck_id='".sql_friendly($truck_id) ."'
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
				<td valign='top' colspan='7'><b>PN Message sent from truck ".$truck_name."</b></td>
				<td valign='top'>&nbsp;</td>
			</tr>";	
			
		$tab3.="<tr>					
				<td valign='top' width='50'><b>MsgID</b></td>
				<td valign='top' width='100'><b>Received</b></td>
				<td valign='top' width='100'><b>Read By</b></td>
				<td valign='top' width='100'><b>Read Date</b></td>
				<td valign='top' width='100'><b>Reply By</b></td>
				<td valign='top' width='100'><b>Reply Date</b></td>
				<td valign='top' width='300'><b>PN Message sent by ".$truck_name."</b></td>
			</tr>";	
	}		
	
	$mydate=date("Y-m-d");		//today...
	
	while($row3 = mysqli_fetch_array($data3))
	{
		if($row3['mrr_mode']=="Sent")
		{     			
			$read_user=mrr_peoplenet_pull_quick_username($row3['my_user_id_read']);
			$read_date=date("m/d/Y H:i:s",strtotime($row3['my_linedate_read']));			if($row3['my_linedate_read']=="0000-00-00 00:00:00") 	$read_date="";
			$reply_user=mrr_peoplenet_pull_quick_username($row3['my_user_id_reply']);
			$reply_date=date("m/d/Y H:i:s",strtotime($row3['my_linedate_reply']));		if($row3['my_linedate_reply']=="0000-00-00 00:00:00") 	$reply_date="";
			
			$dres=mrr_find_pn_truck_drivers($row3['my_truck_id'],$mydate,1);    			
			$driver=$dres['driver_name_1'];
			$load_id=$dres['load_id'];
			$disp_id=$dres['dispatch_id'];
			//$dres['driver_id_1']=0;
			//$dres['driver_id_2']=0;
			//$dres['driver_name_2']="";	
			
			$driver_name=trim("".$row3['driver_first']." ".$row3['driver_last']."");
			 			
     		$row3['my_recipient_name']=str_replace("!OIUser","<b>Dispatch</b>",$row3['my_recipient_name']);
     		
     		$maint_link=mrr_prep_auto_maint_link($row3['my_id'],$row3['my_truck_id'],trim($row3['my_msg']),$row3['alert_sent']);
     		
			if(substr_count($row3['my_msg'],"Warning: ")==0)
			{			
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
     			$tab2.=			"<span>".date("m/d/Y H:i",strtotime($row3['my_linedate_added']))."".$timezoning." --- <a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".trim($row3['my_truck_name'])."</a></span>";     	//admin_trucks.php?id=".$row3['truck_id']."
     			//$tab2.=			"<a href='javascript:delete_event($row[my_calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     			$tab2.=			"<a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
     			$tab2.=		"</h3>";
     			$tab2.=		"<p>
     							Driver(s): ".$driver."<br>
     							<a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>Unread...Click here to read.</a> ".trim($row3['my_recipient_name'])." [".$driver_name."]: ".$row3['my_msg']."
     							
     							<img src='/images/note_msg.png' border='0' alt='Reply' width='12' height='16' onClick=\"pn_msg_box_mini_reply(".$disp_id.",".$load_id.",".$row3['my_truck_id'].",'".$mydate." 00:00:00');\">
								<div id='pn_note_mini_holder_".$disp_id."'></div>   
     						</p> ";
     			$tab2.=	"</li>";
						
				$tab3.="<tr>
     						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".$row3['my_id']."</a></td>
     						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".date("m/d/Y H:i",strtotime($row3['my_linedate_added']))."".$timezoning."</a></td>
     						<td valign='top'>".$read_user."</td>
     						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".$read_date."</a></td>
     						<td valign='top'>".$reply_user."</td>
     						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['my_truck_id']."&reply_id=".$row3['my_id']."'>".$reply_date."</a></td>
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
						<td valign='top'>PHONEDX</td>
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
						<td valign='top' colspan='2'>".$row3['my_truck_name']."</td>
						<td valign='top' colspan='2'>Stop ".$row3['my_recipient_name']."</td>
						<td valign='top'>".$row3['my_msg']."</td>
					</tr>";
					
			$mcntr3++;	
		}	
	}
	
	if($mcntr==0 && $mcntr3==0)
	{
		$tab="<tr><td valign='top' colspan='16'>No messages found.</td></tr>";	
	}
	
	$tab2.=	"<li>";
	//$tab2.=		"<a href='index.php?geotab_on=1'>View GeoTab Messages</a>";
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
	
	if($mode==0)	return $tab;
	if($mode==1)	return $tab2;
	if($mode==3)	return $tab3;
}
function mrr_get_unread_messages_sent_by_all_trucks($limit=0,$mode=0)
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
	
	$dater=date("Y-m-d H:i:s",strtotime("-1 week",time()));
	
	$sql3 = "
		select truck_tracking_msg_history.*,
			(select name_driver_first from drivers where drivers.id=truck_tracking_msg_history.driver_id) as driver_first,	
			(select name_driver_last from drivers where drivers.id=truck_tracking_msg_history.driver_id) as driver_last
		from ".mrr_find_log_database_name()."truck_tracking_msg_history
		where user_id_read='0'
			and archived='0'
			and no_response_needed='0'
			and linedate_received>='".$dater."'
		order by linedate_created desc
		".$lim_txt."
	";     	
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
	
	$mydate=date("Y-m-d");		//today...
	
	while($row3 = mysqli_fetch_array($data3))
	{
		$read_user=mrr_peoplenet_pull_quick_username($row3['user_id_read']);
		$read_date=date("m/d/Y H:i:s",strtotime($row3['linedate_read']));			if($row3['linedate_read']=="0000-00-00 00:00:00") 	$read_date="";
		$reply_user=mrr_peoplenet_pull_quick_username($row3['user_id_reply']);
		$reply_date=date("m/d/Y H:i:s",strtotime($row3['linedate_reply']));			if($row3['linedate_reply']=="0000-00-00 00:00:00") 	$reply_date="";
		
		$dres=mrr_find_pn_truck_drivers($row3['truck_id'],$mydate,1);  			
		$driver=$dres['driver_name_1'];
		$load_id=$dres['load_id'];
		$disp_id=$dres['dispatch_id'];
		
		$driver_name=trim("".$row3['driver_first']." ".$row3['driver_last']."");
		  			
		$row3['recipient_name']=str_replace("!OIUser","<b>Dispatch</b>",$row3['recipient_name']);
		
		$truck_id=$row3['truck_id'];
		$msg_id=$row3['id'];
		
		$maint_link=mrr_prep_auto_maint_link($msg_id,$truck_id,trim($row3['msg_text']),$row3['alert_sent_flag']);		//truck_tracking_msg_history.alert_sent_flag as alert_sent,	
		
		if(substr_count($row3['msg_text'],"Warning: ")==0)
		{			
			$tab.="<tr>
						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'>".$row3['id']."</a></td>
						<td valign='top'>".$row3['recipient_name']."</td>
						<td valign='top'>".date("m/d/Y H:i",strtotime($row3['linedate_added']))."".$timezoning."</td>						
						<td valign='top'><b>".$row3['truck_name']."<b></td>
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
						<td valign='top'><a href='peoplenet_messager.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'>".$row3['msg_text']."</a></td>
						<td valign='top'>".$maint_link."</a></td>
					</tr>";
			
			$tab2.=	"<li>";
			$tab2.=		"<h3>";
			$tab2.=			"<span>".date("m/d/Y H:i",strtotime($row3['linedate_added']))."".$timezoning." --- <a href='peoplenet_messager.php?truck_id=".$row3['truck_id']."&reply_id=".$row3['id']."'>".trim($row3['truck_name'])."</a></span>";     	//admin_trucks.php?id=".$row3['truck_id']."
			//$tab2.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
			$tab2.=			"<a href='peoplenet_messager.php?truck_id=".$row3['truck_id']."&reply_id=".$row3['id']."'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
			$tab2.=		"</h3>";
			$tab2.=		"<p>Driver(s): ".$driver."<br>
							<a href='peoplenet_messager.php?truck_id=".$row3['truck_id']."&reply_id=".$row3['id']."'>Unread...Click here to read.</a> ".trim($row3['recipient_name']).": ".$row3['msg_text']."
							<span class='mrr_link_like_on' onClick='mrr_ignore_new_messages(".$msg_id.",".$_SESSION['user_id'].");' title='click to ignore, or if no response is needed.'>
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
	//$tab2.=		"<a href='index.php?geotab_on=1'>View GeoTab Messages</a>";
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
	
	if($mode==0)	return $tab;
	if($mode==1)	return $tab2;
}

function mrr_get_messages_sent_out_to_truck($date_from, $date_to, $truck_id=0, $truck_name="",$limit=0, $archived=0,$mode=0)
{	//sent and stored messages from Peoplenet Interface or Messager
	$date_range_message=" and truck_tracking_messages.linedate>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and truck_tracking_messages.linedate<='".date("Y-m-d",strtotime($date_to))." 23:59:59'";	
	
	$lim_txt="";
	if($limit>0)	$lim_txt=" limit ".$limit."";
	
	$archiver=" and archived='".sql_friendly($archived)."'";
	
	$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
	//$offset_gmt=$offset_gmt * -1;
	
	$mcntr=0; 
	$tab="";
	$tab3="";
	
	$sql3 = "
		select truck_tracking_messages.*,
			users.username
		from ".mrr_find_log_database_name()."truck_tracking_messages,users
		where truck_tracking_messages.truck_id='".sql_friendly($truck_id) ."'
			and truck_tracking_messages.user_id=users.id
			".$date_range_message."
			".$archiver."
		order by truck_tracking_messages.linedate desc
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
						<td valign='top' colspan='10'><b>PN Message</b></td>
					</tr>
		";
		$tab3.="
					<tr>
						<td valign='top' width='150'><b>Sent Date</b></td>
						<td valign='top' width='100' align='right'><b>Reply Msg</b></td>
						<td valign='top' width='200' align='right'><b>Replied to MsgID</b></td>     						
						<td valign='top' width='400'><b>PN Message sent to ".$truck_name."</b></td>
					</tr>
		";	
	}			
	while($row3 = mysqli_fetch_array($data3))
	{
		$clink="";
		if($row3['linedate']!="0000-00-00 00:00:00")		$clink="".date("m/d/Y H:i:s",strtotime($row3['linedate']))."";
		$dlink="";
		if($row3['user_id'] > 0)						$dlink="".$row3['username']."";
		
		$rlink="";
		if($row3['reply_msg_id'] > 0)					$rlink="<span class='mrr_link_like_on' onClick='mrr_fill_reader(".$row3['reply_msg_id'].");'>".$row3['reply_msg_id']."</span>";
		
		
		$tab.="<tr>
				<td valign='top'><span class='mrr_link_like_on' id='msg_view_send_id_".$row3['id']."' onClick='mrr_view_reader(".$row3['id'].");'>".$row3['id']."</span></td>
				<td valign='top'><span class='mrr_link_like_on' id='msg_view_send_date_".$row3['id']."' onClick='mrr_view_reader(".$row3['id'].");'>".$clink."</span></td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'><span class='mrr_link_like_on' id='msg_view_replied_".$row3['id']."'>".$rlink."</span></td>
				<td valign='top' align='right'><span class='mrr_link_like_on' id='msg_view_sender_".$row3['id']."' onClick='mrr_view_reader(".$row3['id'].");'>".$dlink."</span></td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' colspan='10'><span class='mrr_link_like_on' id='msg_view_msg_".$row3['id']."' onClick='mrr_view_reader(".$row3['id'].");'>".$row3['message']."</span></td>
			</tr>";
		
		$tab3.="<tr>
				<td valign='top'><a href='peoplenet_messager.php?truck_id=".$row3['truck_id']."'>".date("m/d/Y H:i:s",strtotime($row3['linedate']))."</a></td>
				<td valign='top' align='right'>".$row3['username']."</td>
				<td valign='top' align='right'><a href='peoplenet_messager.php?truck_id=".$row3['truck_id']."&reply_id=".$row3['reply_msg_id']."'>".$row3['reply_msg_id']."</a></td>					
				<td valign='top'>".$row3['message']."</td>
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

function mrr_peoplenet_select_canned_message($field,$pre=0,$width=300)
{
	$selbx="";
	
	$selbx.="<select name='".$field."' id='".$field."' style='width:".$width."px;'>";
	$sel="";		if($pre==0 || $pre=="")		$sel=" selected";
	$selbx.="<option value='0'".$sel.">Use canned message</option>";
	
	$sql3 = "
		select truck_tracking_canned_message.*
		from truck_tracking_canned_message
		where truck_tracking_canned_message.deleted=0
			and truck_tracking_canned_message.active=1
		order by truck_tracking_canned_message.canned_subject asc
	";
	$data3 = simple_query($sql3);
	$mn3=mysqli_num_rows($data3);
	while($row3 = mysqli_fetch_array($data3))
	{
		$sel="";			if($pre==$row3['id'])		$sel=" selected";
		$selbx.="<option value='".$row3['id']."'".$sel.">".$row3['canned_subject']."</option>";
	}
	$selbx.="</select>";
	return $selbx;
}


function mrr_find_this_truck_time_tracking_last_local($truck_id,$dater)
{		
	$res['latitude']="0";
     $res['longitude']="0";
     $res['location']="";	
     	
	$sql="
		select latitude,
			longitude,
			location 
		from ".mrr_find_log_database_name()."truck_tracking
		where truck_id='".sql_friendly($truck_id)."'
			and linedate>='".date("Y-m-d",strtotime($dater))." 00:00:00'
			and linedate<='".date("Y-m-d",strtotime($dater))." 23:59:59'
		order by id desc
		limit 1
	";
	$data = simple_query($sql);
     if($row = mysqli_fetch_array($data))
     {
     	$res['latitude']=$row['latitude'];
     	$res['longitude']=$row['longitude'];
     	$res['location']=$row['location'];	
     }
     
	return $res;		
}
function mrr_peoplenet_get_last_position_distanct_and_location($truck_id,$dispatch_id)
{
	$txt="";
	$completed="";	
	$location="";
	$shipper="Home Base";
	
	$lat_1="0";
	$lon_1="0";
	$lat_2="0";
	$lon_2="0";	
	
	$dater=date("m/d/Y");
	$mres=mrr_find_this_truck_time_tracking_last_local($truck_id,$dater);
	
	$lat_1=$mres['latitude'];
	$lon_1=$mres['longitude'];
	$location=$mres['location'];			
	
	$sql3 = "
		select load_handler_stops.*
		from load_handler_stops
		where load_handler_stops.deleted=0
			and load_handler_stops.trucks_log_id='".sql_friendly($dispatch_id)."'
			and (load_handler_stops.linedate_completed IS NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
		order by load_handler_stops.linedate_pickup_eta asc,
			load_handler_stops.id asc
		limit 1
	";
	$data3 = simple_query($sql3);
	$mn3=mysqli_num_rows($data3);
	if($row3 = mysqli_fetch_array($data3))
	{
		$shipper="".$row3['shipper_name']."";
		$lat_2="".$row3['latitude']."";
		$lon_2="".$row3['longitude']."";
	}
	else
	{
		$completed=" [Completed]";	
	}
	
	$distance=mrr_distance_between_gps_points($lat_1,$lon_1,$lat_2,$lon_2);
	$distance=abs($distance);
	
	$label="".$distance." Miles from ".$shipper."".$completed.".";
	if($lat_2=="0" || $lon_2=="0")
	{
		$label="No PN GPS Stop Coordinates Found";
	}
	
	$txt="".$label." Current Position: ".$location."";	
	
	return $txt;	
}

function mrr_find_all_pn_truck_time_tracking_last_local($dater,$debug=0)
{				
	$tcnt=0;
	$tarr[0]=0;
	$lat[0]="0";
	$lon[0]="0";
	$loc[0]="";		
	$datestamp[0]="";		
	$mrr_sql="";
	
	$sql="		
			select latitude,
				longitude,
				location,
				truck_tracking.truck_id as id,
				truck_tracking.linedate as datestamp
				
			from trucks
				inner join ".mrr_find_log_database_name()."truck_tracking on truck_tracking.truck_id = trucks.id 

			where trucks.active > 0
				and trucks.deleted = 0
				and trucks.peoplenet_tracking > 0
				and truck_tracking.id = (
						select ifnull(max(truck_tracking.id),0) 
						from ".mrr_find_log_database_name()."truck_tracking 
						where truck_tracking.truck_id = trucks.id
		     				and truck_tracking.linedate>='".date("Y-m-d",strtotime($dater))." 00:00:00'
		     				and truck_tracking.linedate<='".date("Y-m-d",strtotime($dater))." 23:59:59'
		     		)
	";
	$mrr_sql="<br>Prime=".$sql.".";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		$truck_id=$row['id'];
		$tarr[ $tcnt ]=$truck_id;
		$lat[ $tcnt ]="0";
		$lon[ $tcnt ]="0";
		$loc[ $tcnt ]="";
		$datestamp[ $tcnt ]="";

		$mrr_sql.="<br>Truck ".$truck_id." (".$mn2.")=".$sql2.".";
		
     	$lat[ $tcnt ]=$row['latitude'];
		$lon[ $tcnt ]=$row['longitude'];
		$loc[ $tcnt ]=$row['location'];
		
		$datestamp[ $tcnt ]=date("m/d/Y G:i",strtotime($row['datestamp']));
          
          $tcnt++;			
	}
	$res['latitude']=$lat;
     $res['longitude']=$lon;
     $res['location']=$loc;	
     $res['trucks']=$tarr;
     $res['num']=$tcnt;
     $res['date']=$datestamp;	  
     
     if($debug>0)
     {
     	echo "<br>QUERIES: <br>".$mrr_sql."<br>";	
     }        
	return $res;		
}
function mrr_peoplenet_get_last_position_distanct_and_locationV2($truck_id,$dispatch_id,$longi="0",$lati="0",$local="")
{		
	$txt="";
	/*
	$completed="";	
	$location="";
	$shipper="Home Base";
	
	//$lat_1="0";
	//$lon_1="0";
	$lat_2="0";
	$lon_2="0";	
	
	//$dater=date("m/d/Y");
	//$mres=mrr_find_this_truck_time_tracking_last_local($truck_id,$dater);
	
	$lat_1=$lati;
	$lon_1=$longi;
	$location=trim($local);			
	
	$sql3 = "
		select load_handler_stops.*
		from load_handler_stops
		where load_handler_stops.deleted=0
			and load_handler_stops.trucks_log_id='".sql_friendly($dispatch_id)."'
			and (load_handler_stops.linedate_completed IS NULL or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
		order by load_handler_stops.linedate_pickup_eta asc,
			load_handler_stops.id asc
		limit 1
	";
	$data3 = simple_query($sql3);
	$mn3=mysqli_num_rows($data3);
	if($row3 = mysqli_fetch_array($data3))
	{
		$shipper="".$row3['shipper_name']."";
		$lat_2="".$row3['latitude']."";
		$lon_2="".$row3['longitude']."";
	}
	else
	{
		$completed=" [Completed]";	
	}
	
	$distance=mrr_distance_between_gps_points($lat_1,$lon_1,$lat_2,$lon_2);
	$distance=abs($distance);
	
	$label="".$distance." Miles from ".$shipper."".$completed.". ";
	if($lat_2=="0" || $lon_2=="0")
	{
		$label="No Distance. ";
	}
	
	$txt="".$label." Current Position: ".$location."";	// Lat: (".$lat_1."-".$lat_2.") and Long: (".$lon_1."-".$lon_2.")
	
	
	*/
	
	/*
	$res=mrr_find_only_location_of_this_truck($truck_id);
	
	$long=$res['longitude'];
	$lat=$res['longitude'];
	$location=$res['location'];
	$truck_name=$res['truck_name'];
	
	$txt="Current Position: ".$location."";	// Lat: (".$lat.") and Long: (".$long.")
	*/
	
	$txt="Current Location Loading...";
	return $txt;	
}

function mrr_trim_old_truck_tracking_plot_points($days)
{
	$test_dater=date("Y-m-d H:i:s",strtotime("-".$days." days",time()));		//replaces "DATE_SUB(NOW(),INTERVAL 7 DAY)" in query below.
	
	$sql="delete from ".mrr_find_log_database_name()."truck_tracking where linedate < '".date("Y-m-d",strtotime("-14 days",time()))." 00:00:00'";
	simple_query($sql);
	
	$sql = "
		select distinct DATE_FORMAT(linedate,'%Y-%m-%d') as mrr_date, 
			truck_id
		from ".mrr_find_log_database_name()."truck_tracking
		where linedate < '".$test_dater."' and truck_id>0
		order by truck_id asc,linedate asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		$truck_id=$row['truck_id'];
		$linedate=$row['mrr_date'];
		$odometer=0;
		
		$test_linedate=date("Y-m-d",strtotime($linedate));
		
		$sql2 = "
			select performx_odometer
			from ".mrr_find_log_database_name()."truck_tracking
			where linedate>='".$test_linedate." 00:00:00'
				and linedate<='".$test_linedate." 23:59:59'
				and truck_id='".sql_friendly($truck_id)."'
				and linedate < '".$test_dater."'
			order by linedate desc
			
		";	//limit 1
		$data2 = simple_query($sql2);
		if($row2 = mysqli_fetch_array($data2))
		{
			$odometer=$row2['performx_odometer'];	
		}
		$odometer=(int)$odometer;
		
		if($odometer > 0)
		{
			$sql3 = "
				insert into ".mrr_find_log_database_name()."truck_tracking_odometer
					(id,
					linedate,
					truck_id,
					odometer)
				values	
					(NULL,
					'".sql_friendly($linedate)." 00:00:00',
					'".sql_friendly($truck_id)."',   					
					'".sql_friendly($odometer)."')
			";
			simple_query($sql3);
		}		
	}	
}

function mrr_find_only_location_of_this_truck($truck_id=0)
{	//this function gets current location without saving the dat...display only for load board...		
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];	//"3577";
	$pn_pw = $defaultsarray['peoplenet_account_password'];	//"35con77";
	$pn_cid=trim($pn_cid);
	$pn_pw=trim($pn_pw);
	
	$cmd="loc_onetruck";
	$moder=2;
	$long="0";
	$lat="0";
	$location="";
	$truck_name="";
	$temp_page="";
	$gps_location="";
	
	$truck_speed=0;
	$truck_head=0;
	
	if($truck_id>0)
	{
		$sql = "
			select *
			from trucks
			where deleted = 0
				and trucks.id = '".sql_friendly($truck_id) ."'
			order by name_truck asc
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$truck_name=$row['name_truck'];
			$gps_location=$row['geotab_current_location'];
			$location=$row['geotab_current_location'];
			$long=$row['geotab_last_longitude'];
			$lat=$row['geotab_last_latitude'];
			$truck_speed=$row['geotab_truck_speed'];;
		}		
	}
			
	if($truck_id==1520428)		$truck_name="1520428";		
	
	if($cmd=="loc_onetruck" && trim($truck_name)!="" && 1==2)
	{
		$url="http://open.pfmlogin.com/scripts/open.dll?cid=".$pn_cid."&pw=".$pn_pw."&service=".$cmd."&trucknum=".trim($truck_name)."&compat_level=".$moder."";
		$page=mrr_peoplenet_get_file_contents($url);
		$page=strip_tags($page);	
		
		$temp_page=$page;		
		
		$page=str_replace("success","",$page);
		$page=str_replace("unavailable","0",$page);
		
		$page=str_replace("\t"," --- ",$page);
		$page=str_replace("\r","<br>",$page);
		$page=str_replace("\n","<br>",$page);
		$page=str_replace("\l","<br>",$page);
		
		$pieces = explode("<br>", $page);
		for($i=0; $i< count($pieces); $i++)
		{
			if($pieces[$i]!="" && $pieces[$i]!="<br>")
			{					
				$cols = explode(" --- ", $pieces[$i] );
				for($j=0; $j< count($cols); $j++)
				{
					if($cols[$j]!="" && $cols[$j]!=" --- ")
					{							
						if($j==5)
						{
							$lat=trim($cols[$j]);
								
						}
						if($j==6)
						{
							$long=trim($cols[$j]);	
						}
						if($j==7)
						{
							$location=trim($cols[$j]);	
						}								
					}							
				}	//end for loop J
				
				/*
          		$track_arr[0]		//truck_id
          		$track_arr[1]		//linedate
          		$track_arr[2]		//truck_speed
          		$track_arr[3]		//truck_heading
          		$track_arr[4]		//gps_quality
          		$track_arr[5]		//latitude
          		$track_arr[6]		//longitude
          		$track_arr[7]		//location
          		$track_arr[8]		//fix_type
          		$track_arr[9]		//ignition
          		$track_arr[10]		//gps_odometer
          		$track_arr[11]		//gps_rolling_odometer
          		$track_arr[12]		//performx_odometer
          		$track_arr[13]		//performx_fuel
          		$track_arr[14]		//performx_speed
          		$track_arr[15]		//performx_idle
          		$track_arr[16]		//serial_number
          		$track_arr[17]		//packet_number
          		$track_arr[18]		//driver_id
          		$track_arr[19]		//driver2_id
          		*/	
          		
			}	//end if	
		} //end for loop I			
		
		$adder_lat="and latitude>='".sql_friendly(number_format($lat,3))."000' and latitude<='".sql_friendly(number_format($lat,3))."999'";
		$adder_long="and longitude>='".sql_friendly(number_format($long,3))."000' and longitude<='".sql_friendly(number_format($long,3))."999'";
		
		if($lat < 0)		$adder_lat="and latitude>='".sql_friendly(number_format($lat,3))."999' and latitude<='".sql_friendly(number_format($lat,3))."000'";			
		if($long < 0)		$adder_long="and longitude>='".sql_friendly(number_format($long,3))."999' and longitude<='".sql_friendly(number_format($long,3))."000'";
		
		$lat=number_format($lat,3);
		$long=number_format($long,3);	
		
		$sql="
			select location,truck_speed,truck_heading
			from ".mrr_find_log_database_name()."truck_tracking
			where truck_id='".sql_friendly($truck_id)."'
				".$adder_lat."
				".$adder_long."     				
			order by linedate desc,id desc
			limit 1
		";
		//$gps_location=$sql;
		$data = simple_query($sql);
          if($row = mysqli_fetch_array($data))
          {          	
          	$gps_location=$row['location'];	
          	$truck_speed=$row['truck_speed'];
			$truck_head=$row['truck_heading'];
          }			
		
	}	//end CMD and TRUCK_NAME if
	
	//39.626560	-77.705635
	//39.6265609	-77.7056347
	//----------	 ----------
	//00.000000 9	-00.000000 3		
	
	$res['longitude']=$long;
	$res['latitude']=$lat;
	$res['location']=$location;
	$res['truck_name']=$truck_name;
	$res['temp_page']=$temp_page;
	$res['gps_location']=$gps_location;
	$res['truck_speed']=$truck_speed;
	$res['truck_head']=$truck_head;
	return $res;
}


function mrr_capture_pn_dispatch_xml($moder=0,$xml="",$load_id=0,$dispatch_id=0)
{	//mode is the type of XML being sent...   1=add dispatch, 2=remove dispatch
	//xml is full XML string...
	$sql="
		insert into ".mrr_find_log_database_name()."truck_tracking_dispatch_xml 
			(id,linedate_added,run_code,xml_string,load_id,dispatch_id)
		values 
			(NULL, NOW(),'".sql_friendly($moder)."','".sql_friendly($xml)."','".sql_friendly($load_id)."','".sql_friendly($dispatch_id)."')
	";	
	simple_query($sql);
}

function mrr_get_active_dispatch_ids($truck_id=0)
{
	$cnt=0;
	$load[0]=0;
	$arr[0]=0;
	$pnid[0]="";
	$trucks[0]=0;
	$trucknames[0]="";
	$code_arriving[0]=0;
	$date_arriving[0]="0000-00-00 00:00:00";
	$code_arrived[0]=0;
	$date_arrived[0]="0000-00-00 00:00:00";
	$code_departed[0]=0;
	$date_departed[0]="0000-00-00 00:00:00";		
	$output[0]="";
	
	$mrr_adder="";
	if($truck_id>0)
	{
		$mrr_adder=" and truck_id='".sql_friendly($truck_id)."'";	
	}
	$sql="
		select *,
			(select name_truck from trucks where trucks.id=truck_tracking_dispatches.truck_id) as truck_name,
			(select load_handler_id from trucks_log where trucks_log.id=truck_tracking_dispatches.dispatch_id and dispatch_completed=0) as load_id
		from ".mrr_find_log_database_name()."truck_tracking_dispatches
		where departed_code<=stops
			and canceled=0   
			".$mrr_adder."				
		order by dispatch_id asc
	";
	//$gps_location=$sql;
	$data = simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {          	
     	$peoplenet_id=$row['peoplenet_id'];
     	$myid=$row['id'];          	
     	$dispatch_id=$row['dispatch_id'];
          $saved_stops=$row['stops'];
          
     	$disp_status="";
     	$disp_status2="";
		if($peoplenet_id>0 && (int)$row['load_id'] > 0)
		{
			$disp_status=mrr_peoplenet_find_data2("pnet_dispatch_status",0,0,$peoplenet_id,0);	
			$mres=mrr_process_status_result($disp_status,$saved_stops,$dispatch_id);
			
			$row['arriving_code']=0;
			$row['arriving_date']="0000-00-00 00:00:00";
			$row['arrived_code']=0;
			$row['arrived_date']="0000-00-00 00:00:00";
			$row['departed_code']=0;
			$row['departed_date']="0000-00-00 00:00:00";				
			
			for($x=0;$x < $saved_stops;$x++)
			{
				if($mres['arriving_code'][$x] > 0)		{	$row['arriving_date']=$mres['arriving_date'][$x];		$row['arriving_code']=$mres['arriving_code'][$x];		}
				if($mres['arrived_code'][$x] > 0)		{	$row['arrived_date']=$mres['arrived_date'][$x];		$row['arrived_code']=$mres['arrived_code'][$x];		}
				if($mres['departed_code'][$x] > 0)		{	$row['departed_date']=$mres['departed_date'][$x];		$row['departed_code']=$mres['departed_code'][$x];		}
			}
			$disp_status2=$mres['output'];
							
			$sql2="
				update ".mrr_find_log_database_name()."truck_tracking_dispatches set
					arriving_code='".sql_friendly($row['arriving_code'])."',
					arriving_date='".date("Y-m-d H:i:s",strtotime($row['arriving_date']))."',
					arrived_code='".sql_friendly($row['arrived_code'])."',
					arrived_date='".date("Y-m-d H:i:s",strtotime($row['arrived_date']))."',
					departed_code='".sql_friendly($row['departed_code'])."',
					departed_date='".date("Y-m-d H:i:s",strtotime($row['departed_date']))."'
					
				where id='".sql_friendly($myid)."'
			";
			simple_query($sql2);
			
			$load[$cnt]=$row['load_id'];		
          	$arr[$cnt]=$row['dispatch_id'];
			$pnid[$cnt]=$row['peoplenet_id'];
			$trucks[$cnt]=$row['truck_id'];
			$trucknames[$cnt]=$row['truck_name'];
			$code_arriving[$cnt]=$row['arriving_code'];
			$date_arriving[$cnt]=$row['arriving_date'];
			$code_arrived[$cnt]=$row['arrived_code'];
			$date_arrived[$cnt]=$row['arrived_date'];
			$code_departed[$cnt]=$row['departed_code'];
			$date_departed[$cnt]=$row['departed_date'];
			
			$output[$cnt]="".$disp_status2;	     						
			$cnt++;
		}
     }
	
	$res['num']=$cnt;
	$res['loads']=$load;
	$res['dispatches']=$arr;
	$res['pn_ids']=$pnid;
	$res['trucks']=$trucks;
	$res['trucknames']=$trucknames;
	$res['arriving_code']=$code_arriving;
	$res['arriving_date']=$date_arriving;
	$res['arrived_code']=$code_arrived;
	$res['arrived_date']=$date_arrived;
	$res['departed_code']=$code_departed;
	$res['departed_date']=$date_departed;
	$res['output']=$output;
	
	return $res;
}

function mrr_process_status_result($output,$saved_stops=0,$disp_id=0)
{
	$output=trim($output);
	$output=strip_tags($output);
	
	$fullstr="";		
	$str1="0000-00-00 00:00";
	$str2="0000-00-00 00:00";
	$str3="0000-00-00 00:00";
	$code1=0;
	$code2=0;
	$code3=0;   
	
	$res['arriving_code'][0]=$code1;
	$res['arriving_date'][0]=$str1;
	$res['arrived_code'][0]=$code2;
	$res['arrived_date'][0]=$str2;
	$res['departed_code'][0]=$code3;
	$res['departed_date'][0]=$str3;  	
	$res['details'][0]=""; 
	$res['completed'][0]=""; 
	
	$subbers[0]="";
	$pose1=strpos($output," (Stop ");
	for($x=0;$x < $saved_stops; $x++)
	{
		$pose0=strpos($output,") ",$pose1);
		$pose1=strpos($output," (Stop ",($pose0+1));
		
		$subbers[$x]=substr($output,$pose0,($pose1 - $pose0));	
		$res['arriving_code'][$x]=$code1;
		$res['arriving_date'][$x]=$str1;
		$res['arrived_code'][$x]=$code2;
		$res['arrived_date'][$x]=$str2;
		$res['departed_code'][$x]=$code3;
		$res['departed_date'][$x]=$str3; 
		$res['details'][$x]="";
		$res['completed'][$x]=""; 
	}
	
	$y=0;
	$sql="
		select * 
		from load_handler_stops 
		where trucks_log_id='".sql_friendly($disp_id)."' 
			and deleted=0		
		order by linedate_pickup_eta asc,id asc
	";
	$data = simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {
     	//this field is inserted into a row...with other table cells... adjust header in calling function to compensate changes
     	$stopper="Shipper";
     	if($row['stop_type_id'] > 1) $stopper="Consignee";
     	
     	$res['details'][$y]="
     		<td valign='top'>(".$row['id'].") ".$stopper."</td>
     		<td valign='top'>".$row['shipper_name']."</td>
     		<td valign='top'>".$row['shipper_address1']."</td>
     		<td valign='top'>".$row['shipper_address2']."</td>
     		<td valign='top'>".$row['shipper_city']."</td>
     		<td valign='top'>".$row['shipper_state']."</td>
     		<td valign='top'>".$row['shipper_zip']."</td>
     		";
     		
     	if($row['linedate_completed'] >="2012-01-01 00:00:00")
     	{	
     		$res['completed'][$y]=$row['linedate_completed']; 
     	}
     	$y++;
     }
	
	$pose3=0;
	for($x=0;$x < $saved_stops; $x++)
	{
		$myoutput=trim($subbers[$x]);
		
		$str1="0000-00-00 00:00";
		$str2="0000-00-00 00:00";
		$str3="0000-00-00 00:00";
		$code1=0;
		$code2=0;
		$code3=0;  
		
		$pose0=strpos($myoutput,") ",$pose3);
		$pose1=strpos($myoutput,"Arriving: ",$pose0);
		$pose2=strpos($myoutput,"Arrived: ",$pose1);
		$pose3=strpos($myoutput,"Departed: ",$pose2);			
					
		if($pose1>0 && substr_count($myoutput,"Arriving: N/A")==0)
		{
			$str1=substr($myoutput,$pose1,31);
			$str1=str_replace("Arriving: ","",$str1);
			$str1=str_replace("Time ","",$str1);
			$str1=trim($str1);
			if(substr_count($str1,"N/A") == 0)	$code1=($x+1);
		}
		if($pose2>0 && substr_count($myoutput,"Arrived: N/A")==0)
		{
			$str2=substr($myoutput,$pose2,30);
			$str2=str_replace("Arrived: ","",$str2);
			$str2=str_replace("Time ","",$str2);
			$str2=trim($str2);
			if(substr_count($str2,"N/A") == 0)	$code2=($x+1);
		}
		if($pose3>0 && substr_count($myoutput,"Departed: N/A")==0)
		{
			$str3=substr($myoutput,$pose3,31);
			$str3=str_replace("Departed: ","",$str3);
			$str3=str_replace("Time ","",$str3);
			$str3=trim($str3);
			if(substr_count($str3,"N/A") == 0)	$code3=($x+1);
		}
		
		$res['arriving_code'][$x]=$code1;
		$res['arriving_date'][$x]=$str1;
		$res['arrived_code'][$x]=$code2;
		$res['arrived_date'][$x]=$str2;
		$res['departed_code'][$x]=$code3;
		$res['departed_date'][$x]=$str3;
		
		if($res['completed'][$x]!="")	
		{
			$res['arriving_code'][$x]=($x+1);
			$res['arriving_date'][$x]=$res['completed'][$x];
			$res['arrived_code'][$x]=($x+1);
			$res['arrived_date'][$x]=$res['completed'][$x];	
			
			$str1=$res['completed'][$x];
			$str2=$res['completed'][$x];
			$str3="Completed";
		}	
		
		$str1x=$str1;
		$str2x=$str2;
		$str3x=$str3;
		
		if($str1=="0000-00-00 00:00")			$str1x="";	else	$str1x=mrr_peoplenet_time_mask_from_gmt($str1);
		if($str2=="0000-00-00 00:00")			$str2x="";	else	$str2x=mrr_peoplenet_time_mask_from_gmt($str2);
		if($str3!="Completed")
		{
			if($str3=="0000-00-00 00:00")		$str3x="";	else	$str3x=mrr_peoplenet_time_mask_from_gmt($str3);
		}
		
		$fullstr.="<tr>
					<td valign='top'>Stop ".($x+1)."</td>
					".$res['details'][$x]."
					<td valign='top'>".$str1x."</td>
					<td valign='top'>".$str2x."</td>
					<td valign='top'>".$str3x."</td>
				</tr>";	
				
	}
	$res['output']=$fullstr;
	
	return $res;	
}

function mrr_display_current_dispatch_tracking($truck_id=0)
{
	$tab="";
	$res=mrr_get_active_dispatch_ids($truck_id);
	
	$cnt=$res['num'];
	$load=$res['loads'];
	$arr=$res['dispatches'];
	$pnid=$res['pn_ids'];
	$trucks=$res['trucks'];
	$trucknames=$res['trucknames'];		
	$code_arriving=$res['arriving_code'];
	$date_arriving=$res['arriving_date'];
	$code_arrived=$res['arrived_code'];
	$date_arrived=$res['arrived_date'];
	$code_departed=$res['departed_code'];
	$date_departed=$res['departed_date'];
	$output=$res['output'];
	
	$tab.="<span class='mrr_link_like_on' onClick='mrr_toggle_show();'><b>Show All Stops</b></span> &nbsp;&nbsp; <span class='mrr_link_like_on' onClick='mrr_toggle_hide();'><b>Hide All Stops</b></span><br>";
	$tab.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
	$tab.="<tr>";
	$tab.=	"<td valign='top'><b>#</b></td>";
	$tab.=	"<td valign='top'><b>Load</b></td>";
	$tab.=	"<td valign='top'><b>Dispatch</b></td>";
	$tab.=	"<td valign='top'><b>PN ID</b></td>";
	$tab.=	"<td valign='top'><b>Truck Name</b></td>";
	$tab.=	"<td valign='top'><b>Arriving</b></td>";
	$tab.=	"<td valign='top'><b>Date</b></td>";
	$tab.=	"<td valign='top'><b>Arrived</b></td>";
	$tab.=	"<td valign='top'><b>Date</b></td>";
	$tab.=	"<td valign='top'><b>Departed</b></td>";
	$tab.=	"<td valign='top'><b>Date</b></td>";
	$tab.="</tr>";
	for($x=0;$x < $cnt;$x++)
	{
		$bg_class="odd";		if($x%2==0) 	$bg_class="even";
		
		$str1x=$date_arriving[$x];
		$str2x=$date_arrived[$x];
		$str3x=$date_departed[$x];			
		
		if(substr_count($str1x,"0000-00-00") == 0)	$str1x=mrr_peoplenet_time_mask_from_gmt($str1x); 	else	$str1x="";
		if(substr_count($str2x,"0000-00-00") == 0)	$str2x=mrr_peoplenet_time_mask_from_gmt($str2x); 	else	$str2x="";
		if(substr_count($str3x,"0000-00-00") == 0)	$str3x=mrr_peoplenet_time_mask_from_gmt($str3x); 	else	$str3x="";
					
		$tab.="<tr class='".$bg_class."'>";
		$tab.=	"<td valign='top'>".($x+1)."</td>";
		$tab.=	"<td valign='top'>".$load[$x]."</td>";
		$tab.=	"<td valign='top'>".$arr[$x]." <span class='mrr_link_like_on' onClick='mrr_toggle_block(".$arr[$x].");'><b>Stops</b></span></td>";
		$tab.=	"<td valign='top'>".$pnid[$x]."</td>";
		$tab.=	"<td valign='top'>".$trucknames[$x]."</td>";	//ID=$trucks[$x]
		$tab.=	"<td valign='top'>Stop ".$code_arriving[$x]."</td>";
		$tab.=	"<td valign='top'>".$str1x."</td>";
		$tab.=	"<td valign='top'>Stop ".$code_arrived[$x]."</td>";
		$tab.=	"<td valign='top'>".$str2x."</td>";
		$tab.=	"<td valign='top'>Stop ".$code_departed[$x]."</td>";
		$tab.=	"<td valign='top'>".$str3x."</td>";
		$tab.="</tr>";
		$tab.="<tr class='".$bg_class." mrr_block_details' id='mrr_block_".$arr[$x]."'>";
		$tab.=	"<td valign='top'>&nbsp;</td>";
		$tab.=	"<td valign='top' colspan='10'>
					<table border='0' cellpadding='0' cellspacing='0' width='100%'>
					<tr>
						<td valign='top'><b># <span class='mrr_link_like_on' onClick='mrr_toggle_block(".$arr[$x].");'>Hide</span></b></td>
						<td valign='top'><b>Stop ID</b></td>
     					<td valign='top'><b>Company</b></td>
     					<td valign='top'><b>Addr1</b></td>
     					<td valign='top'><b>Addr2</b></td>
     					<td valign='top'><b>City</b></td>
     					<td valign='top'><b>State</b></td>
     					<td valign='top'><b>Zip</b></td>
						<td valign='top'><b>Arriving</b></td>
						<td valign='top'><b>Arrived</b></td>
						<td valign='top'><b>Departed</b></td>
					</tr>
						".$output[$x]."
					</table>
				</td>";
		$tab.="</tr>";
	}		
	$tab.="</table>";
	
	return $tab;	
}

function mrr_peoplenet_time_mask_from_gmt($gmt_date)
{		
	global $defaultsarray;
	$adjuster=(int) $defaultsarray['gmt_offset_peoplenet'];
	$adjuster-=1;
	
	$gmt_date=date("m/d/Y H:i:s",strtotime("".$adjuster." hours",strtotime($gmt_date)));
	return $gmt_date;
}

function mrr_run_full_geofencing_update_for_truck($truck_id=0)
{
	$mrr_adder="";
	if($truck_id>0)		$mrr_adder.=" and geofence_hot_load_tracking.truck_id='".sql_friendly($truck_id)."'";
	
	$full_debugger="";
			
	$sql="
	select geofence_hot_load_tracking.*
	from ".mrr_find_log_database_name()."geofence_hot_load_tracking
	where geofence_hot_load_tracking.deleted=0
		and geofence_hot_load_tracking.active>0
		and geofence_hot_load_tracking.stop_completed=0
		".$mrr_adder."
	order by geofence_hot_load_tracking.id desc	
	";	
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$load_id=$row['load_id'];
		$dispatch_id=$row['dispatch_id'];
		$stop_id=$row['stop_id'];
		
		$full_debugger.="<br><hr><br>".mrr_run_updater_for_load_stop_dispatch($load_id,$dispatch_id,$stop_id);
	}	
	return $full_debugger;
}

function mrr_run_updater_for_load_stop_dispatch($load_id,$dispatch_id=0,$stop_id=0)
{		
	$mrr_adder="";
	if($dispatch_id>0)		$mrr_adder.=" and dispatch_id='".sql_friendly($dispatch_id)."'";
	if($stop_id>0)			$mrr_adder.=" and stop_id='".sql_friendly($stop_id)."'";
	
	$truck_id=0;
	
	$nowtime=time();
	
	global $defaultsarray;		
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];
	$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
	
	if($mph<=0)	$mph=1;
	
	$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];
	$gmt_off = $gmt_off * 60 * 60 * -1;
	$nowtime+=$gmt_off;
	
	$munits=5280;
	$debugger="";
			
	if($load_id > 0)
	{
		$sql="
			select * 
			from load_handler
			where id='".sql_friendly($load_id)."'				
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{					
			$cust=mrr_get_all_customer_settings($row['customer_id']);	
			
			$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
			$hl_timer=$cust['hot_load_timer'];					//interval between messages
			$hl_r_arriving=$cust['hot_load_radius_arriving'];		//
			$hl_r_arrived=$cust['hot_load_radius_arrived'];		//
			$hl_r_departed=$cust['hot_load_radius_departed'];		//
			$hl_geo_active=$cust['geofencing_radius_active'];		//turn on actual geofencing notices
			$hl_geo_on_all=$cust['geofencing_hot_msg_all_loads'];	//all loads set to on...	
			
			if($dispatch_id>0)
			{
				$sql2="
					select trucks_log.*,
						(select peoplenet_tracking from trucks where trucks.id=trucks_log.truck_id) as peoplenet_active
					from trucks_log
					where trucks_log.load_handler_id='".sql_friendly($load_id)."'
						and trucks_log.id='".sql_friendly($dispatch_id)."'				
				";
			}
			else
			{
				$sql2="
					select trucks_log.*,
						(select peoplenet_tracking from trucks where trucks.id=trucks_log.truck_id) as peoplenet_active
					from trucks_log
					where trucks_log.load_handler_id='".sql_friendly($load_id)."'				
				";	
			}
			
			$data2=simple_query($sql2);
			while($row2=mysqli_fetch_array($data2))
			{
				$truck_id=$row2['truck_id'];
				$people_net=$row2['peoplenet_active'];
				
				$people_net_adder="";
				if($people_net>0)
				{
					$people_net_adder=",
						(select latitude from ".mrr_find_log_database_name()."truck_tracking where truck_id='".sql_friendly($truck_id)."' order by linedate desc,id desc limit 1) as pn_lat,
						(select longitude from ".mrr_find_log_database_name()."truck_tracking where truck_id='".sql_friendly($truck_id)."' order by linedate desc,id desc limit 1) as pn_long,
						(select location from ".mrr_find_log_database_name()."truck_tracking where truck_id='".sql_friendly($truck_id)."' order by linedate desc,id desc limit 1) as pn_local
					";
				}
														
				if($stop_id>0)
				{
					$sql3="
						select load_handler_stops.*".$people_net_adder."
							from load_handler_stops
						where load_handler_stops.load_handler_id='".sql_friendly($row['id'])."'
							and load_handler_stops.trucks_log_id='".sql_friendly($row2['id'])."'
							and load_handler_stops.id='".sql_friendly($stop_id)."'			
					";
				}
				else
				{
					$sql3="
						select load_handler_stops.*".$people_net_adder." 
							from load_handler_stops
						where load_handler_stops.load_handler_id='".sql_friendly($row['id'])."'
							and load_handler_stops.trucks_log_id='".sql_friendly($row2['id'])."'	
					";
				}	
				$data3=simple_query($sql3);
				while($row3=mysqli_fetch_array($data3))
				{
					$lat=$row3['latitude'];
					$long=$row3['longitude'];
					$timezone_offset=$row3['timezone_offset'];
					$timezone_offset_dst=$row3['timezone_offset_dst'];
					$their_time=$nowtime + $timezone_offset + $timezone_offset_dst;					
					
					$doner=$row3['linedate_completed'];
					$dater=$row3['linedate_pickup_eta'];
					$finish_time=(strtotime($row3['linedate_pickup_eta']) - $their_time) / (60 * 60);	//hours left until delivery is late...
					
					$their_time=date("Y-m-d H:i:s",$their_time);	
					
					//get original settings...
					$sql4="
						select * 
						from ".mrr_find_log_database_name()."geofence_hot_load_tracking
						where load_id='".sql_friendly($load_id)."'
							and dispatch_id='".sql_friendly($row2['id'])."'
							and stop_id='".sql_friendly($row3['id'])."'
							and deleted='0'
							and active='1'
							and stop_completed='0'
					";
					$data4=simple_query($sql4);
					while($row4=mysqli_fetch_array($data4))
					{	
						$old_lat=$row4['dest_latitude'];
						$old_long=$row4['dest_longitude'];
						
						$last_run=date("m/d/Y H:i:s", strtotime($row4['linedate_last_gps']));
						$activator=$row4['active'];
						
						$last_gps_lat=$row4['last_gps_latitude'];
						$last_gps_long=$row4['last_gps_longitude'];
						$gps_dist=$row4['dest_distance'];
						$gps_message=$row4['dest_message'];
						$has_arriving=$row4['dest_arriving'];
						$has_arrived=$row4['dest_arrived'];
						$has_departed=$row4['dest_departed'];
						$miles_remain_arriving=$row4['dest_remaining_arriving'];
						$miles_remain_arrived=$row4['dest_remaining_arrived'];
						$miles_remain_departed=$row4['dest_remaining_departed'];
						$approx_arriving=$row4['dest_time_arriving'];
						$approx_arrived=$row4['dest_time_arrived'];
						$approx_departed=$row4['dest_time_departed'];
						$conard_grade=$row4['dispatch_grade'];
						
						$has_arriving_date=$row4['linedate_last_arriving'];
						$has_arrived_date=$row4['linedate_last_arrived'];
						$has_departed_date=$row4['linedate_last_departed'];
						
						$msg_sent_for_arriving=$row4['msg_last_arriving'];
						$msg_sent_for_arrived=$row4['msg_last_arrived'];
						$msg_sent_for_departed=$row4['msg_last_departed'];
						
						//update controls for what to worry about changing in the current geofencing section
						$update_flag_arriving=0;
						$update_flag_arrived=0;
						$update_flag_departed=0;
						
						if( number_format($lat,2) != number_format($old_lat,2) || number_format($long,2) != number_format($old_long,2))
						{
							//new location...update everything
							$update_flag_arriving=1;
							$update_flag_arrived=1;
							$update_flag_departed=1;
						}
						elseif($has_arriving==1)
						{
							$update_flag_arrived=1;
							$update_flag_departed=1;
						}
						elseif($has_arrived==1)
						{
							$update_flag_departed=1;     							
						}
						elseif($has_departed==1)
						{
							$activator=0;
						}
						else
						{
							$update_flag_arriving=1;	
						}
						
						    						
						if($doner>'2013-01-01 00:00:00')
						{	//already completed...
							$has_arriving=1;
							$has_arrived=1;
							$has_departed=1;
							$activator=0;	
							$update_flag_arriving=1;
							$update_flag_arrived=1;
							$update_flag_departed=1;
						}
						else
						{	//not completed, but has GPS point for stop             if($lat>0 && $long>0)
							$get_local=mrr_find_only_location_of_this_truck($truck_id);
							
							$last_gps_lat=$get_local['latitude'];
							$last_gps_long=$get_local['longitude'];
							$last_gps_local=$get_local['location'];
							
							if($last_gps_lat==0 && $last_gps_long==0)
							{
								$last_gps_lat=$row3['pn_lat'];
								$last_gps_long=$row3['pn_long'];	
								$last_gps_local=$row3['pn_local'];
							}
							
							$gps_message="Truck ".$get_local['truck_name']." Is ".$last_gps_local."";//$get_local['gps_location']
							     							
							$gps_dist=mrr_distance_between_gps_points($lat,$long,$last_gps_lat,$last_gps_long);		//has MILES...
							$gps_dist=abs($gps_dist);
							$overall_hrs= $gps_dist / $mph;
							
							$gps_dist2=$gps_dist * $munits;					//convert distance in miles to feet....
							
							if($has_arriving==0)
							{
								$update_flag_arriving=1;
								if($gps_dist2 <= ($hl_r_arriving + $tolerance))								
								{
									$has_arriving=1;	//this part is done...
								}
								else
								{
									$miles_remain_arriving=($gps_dist - ($hl_r_arriving / $mph));
									$approx_arriving=$miles_remain_arriving / $mph;
								}	
							}
							if($has_arrived==0)
							{
								$update_flag_arrived=1;
								if($gps_dist2 <= ($hl_r_arrived + $tolerance))
								{
									$has_arrived=1;	//this part is done...
								}
								else
								{
									$miles_remain_arrived=($gps_dist - ($hl_r_arrived / $mph));
									$approx_arrived=$miles_remain_arrived / $mph;
								}	
							}
							if($has_departed==0)
							{	//leaving, so on way out and works backwards...
								$update_flag_departed=1;
								if(($gps_dist2 + $tolerance) >= $hl_r_departed && $has_arrived==1)
								{
									$has_departed=1;	//this part is done...but only if arrived first...
								}
								else
								{
									$miles_remain_departed=($gps_dist - ($hl_r_departed / $mph));
									$approx_departed=$miles_remain_departed / $mph;
								}	
							}
							
							$debugger.="
								<div>
									Load ".$load_id.", Dispatch ".$row2['id'].", Stop ".$row3['id'].": Distance ".$gps_dist." Miles (".$gps_dist2." Feet)
									, Section 1 complete=".$has_arriving." - Radius=".($hl_r_arriving + $tolerance)." Remaining ".$miles_remain_arriving." ft
									, Section 2 complete=".$has_arrived." - Radius=".($hl_r_arrived + $tolerance)." Remaining ".$miles_remain_arrived." ft
									, Section 3 complete=".$has_departed." - Radius=".($hl_r_departed + $tolerance)." Remaining ".$miles_remain_departed." ft
									.
								</div>";
							
							if($activator > 0)
							{
								if(($overall_hrs * 2) > $finish_time)		$conard_grade=mrr_encode_geofencing_grade("Epic Fail");
								elseif(($overall_hrs * 2) > $finish_time)	$conard_grade=mrr_encode_geofencing_grade("Very Late");
								elseif($overall_hrs > $finish_time)		$conard_grade=mrr_encode_geofencing_grade("Late");
								elseif(($overall_hrs * 2) < $finish_time)	$conard_grade=mrr_encode_geofencing_grade("Early");
								elseif($overall_hrs <= $finish_time)		$conard_grade=mrr_encode_geofencing_grade("On Time");													
							}
							if( number_format($lat,2) != number_format($old_lat,2) || number_format($long,2) != number_format($old_long,2))
							{
								$conard_grade=0;	
							}
						}
																				
						
						$note_driver=0;
						$arriving_adder="";
						$arrived_adder="";
						$departed_adder="";
						if($update_flag_arriving > 0)
						{	// && $msg_sent_for_arriving==0
							$arriving_adder="
     							dest_arriving='".sql_friendly($has_arriving)."',
     							dest_remaining_arriving='".sql_friendly($miles_remain_arriving)."',
     							dest_time_arriving='".sql_friendly($approx_arriving)."',
     						";
     						if($has_arriving > 0)
     						{
     							$arriving_adder.="
     								linedate_last_arriving='".date("Y-m-d H:i:s",strtotime($their_time))."',
     							";	
     						}
     						$note_driver=1;
						}
						elseif($update_flag_arrived > 0)
						{	// && $msg_sent_for_arrived==0
							$arrived_adder="
     							dest_arrived='".sql_friendly($has_arrived)."',
     							dest_remaining_arrived='".sql_friendly($miles_remain_arrived)."',
     							dest_time_arrived='".sql_friendly($approx_arrived)."',
     						";
     						if($has_arrived > 0)
     						{
     							$arrived_adder.="
     								linedate_last_arrived='".date("Y-m-d H:i:s",strtotime($their_time))."',
     							";	
     						}
     						$note_driver=2;
     						if($update_flag_arriving > 0)		$note_driver=1;
						}
						elseif($update_flag_departed > 0)
						{	// && $msg_sent_for_departed==0
							$departed_adder="
     							dest_departed='".sql_friendly($has_departed)."',
     							dest_remaining_departed='".sql_friendly($miles_remain_departed)."',
     							dest_time_departed='".sql_friendly($approx_departed)."',
     						";
     						if($has_departed > 0)
     						{
     							$departed_adder.="
     								linedate_last_departed='".date("Y-m-d H:i:s",strtotime($their_time))."',
     							";		
     						}
     						$note_driver=3;
     						if($update_flag_arrived > 0)		$note_driver=2;
     						if($update_flag_arriving > 0)		$note_driver=1;
						}  
						
						$sql5="
							update ".mrr_find_log_database_name()."geofence_hot_load_tracking set 
								active='".sql_friendly($activator)."',
								dest_longitude='".sql_friendly($long)."',
               					dest_latitude='".sql_friendly($lat)."',					
               					linedate_last_gps=NOW(),
               					last_gps_longitude='".sql_friendly($last_gps_long)."',
               					last_gps_latitude='".sql_friendly($last_gps_lat)."',					
               					dest_distance='".sql_friendly($gps_dist)."',
               					dest_message='".sql_friendly($gps_message)."',					
               					".$arriving_adder."
               					".$arrived_adder."
               					".$departed_adder."
               					dispatch_grade='".sql_friendly($conard_grade)."'
               					
							where load_id='".sql_friendly($load_id)."'
								and dispatch_id='".sql_friendly($row2['id'])."'
								and stop_id='".sql_friendly($row3['id'])."'
								and deleted=0
						";						
						simple_query($sql5);    						
						
						if($hl_active > 0)
						{
							$notice=mrr_send_geofencing_note($row4['id'],$note_driver);		//NOTE DRIVER controls which note is sent, assuming customer settings are on...
						}
					}						
				}
			}				
				
		}
	}
	return $debugger;	
}
function mrr_update_active_loads_for_this_customer($id,$activate=0)
{
	$sql="
		select load_handler_stops.* 
		from load_handler_stops,
			load_handler
		where load_handler.customer_id='".sql_friendly($id)."'
			and load_handler.deleted=0
			and load_handler_stops.deleted=0
			and load_handler_stops.stop_grade_id=0
			and load_handler_stops.load_handler_id=load_handler.id
			and load_handler_stops.linedate_pickup_eta>='2013-01-01 00:00:00'
			and longitude!=0
			and latitude!=0
		order by linedate_pickup_eta asc								
	";
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))	
	{
		$load_id=$row['load_handler_id'];
		$disp_id=$row['trucks_log_id'];
		$stop_id=$row['id'];
		$geo_id=0;
		
		$sql2="
     		select geofence_hot_load_tracking.id
     		from ".mrr_find_log_database_name()."geofence_hot_load_tracking
     		where geofence_hot_load_tracking.load_id='".sql_friendly($load_id)."'
     			and geofence_hot_load_tracking.dispatch_id='".sql_friendly($disp_id)."'
     			and geofence_hot_load_tracking.stop_id='".sql_friendly($stop_id)."'
     		limit 1	
		";	
		$data2=simple_query($sql2);
		if($row2=mysqli_fetch_array($data2))
		{
			$geo_id=$row2['id'];     //this has been entered already, and/or modified, so do not remakle it...			
		}
		
		if($activate==1 && $geo_id==0)
		{
			mrr_add_this_stop_dispatch_load_geofencing($load_id,$disp_id,$stop_id);	//make one...	
		}
	}
}
function mrr_add_this_stop_dispatch_load_geofencing($load_id,$dispatch_id=0,$stop_id=0)
{
	$driver_id=0;
	$truck_id=0;
	$trailer_id=0;
	
	$nowtime=time();
	
	global $defaultsarray;		
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];
	$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
	
	if($mph<=0)	$mph=1;
	
	$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];
	$gmt_off = $gmt_off * 60 * 60 * -1;
	$nowtime+=$gmt_off;
	
	$munits=5280;
	$debugger="";
	
	if($load_id>0)
	{
		$sql="
			select * 
			from load_handler
			where id='".sql_friendly($load_id)."'				
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{					
			$cust=mrr_get_all_customer_settings($row['customer_id']);			
			
			$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
			$hl_timer=$cust['hot_load_timer'];					//interval between messages
			$hl_r_arriving=$cust['hot_load_radius_arriving'];		//
			$hl_r_arrived=$cust['hot_load_radius_arrived'];		//
			$hl_r_departed=$cust['hot_load_radius_departed'];		//
			$hl_geo_active=$cust['geofencing_radius_active'];		//turn on actual geofencing notices	
			
			if($dispatch_id>0)
			{
				$sql2="
					select trucks_log.*,
						(select peoplenet_tracking from trucks where trucks.id=trucks_log.truck_id) as peoplenet_active
					from trucks_log
					where trucks_log.load_handler_id='".sql_friendly($load_id)."'
						and trucks_log.id='".sql_friendly($dispatch_id)."'				
				";
			}
			else
			{
				$sql2="
					select trucks_log.*,
						(select peoplenet_tracking from trucks where trucks.id=trucks_log.truck_id) as peoplenet_active
					from trucks_log
					where trucks_log.load_handler_id='".sql_friendly($load_id)."'				
				";	
			}
			$data2=simple_query($sql2);
			while($row2=mysqli_fetch_array($data2))
			{
				$driver_id=$row2['driver_id'];
				$truck_id=$row2['truck_id'];
				$trailer_id=$row2['trailer_id'];
				$customer_id=$row2['customer_id'];
				$people_net=$row2['peoplenet_active'];
				
				$people_net_adder="";
				if($people_net>0)
				{
					$people_net_adder=",
						(select latitude from ".mrr_find_log_database_name()."truck_tracking where truck_id='".sql_friendly($truck_id)."' order by linedate desc,id desc limit 1) as pn_lat,
						(select longitude from ".mrr_find_log_database_name()."truck_tracking where truck_id='".sql_friendly($truck_id)."' order by linedate desc,id desc limit 1) as pn_long,
						(select location from ".mrr_find_log_database_name()."truck_tracking where truck_id='".sql_friendly($truck_id)."' order by linedate desc,id desc limit 1) as pn_local
					";
				}
				
									
				if($stop_id>0)
				{
					$sql3="
						select load_handler_stops.*".$people_net_adder."
							from load_handler_stops
						where load_handler_stops.load_handler_id='".sql_friendly($row['id'])."'
							and load_handler_stops.trucks_log_id='".sql_friendly($row2['id'])."'
							and load_handler_stops.id='".sql_friendly($stop_id)."'			
					";
				}
				else
				{
					$sql3="
						select load_handler_stops.*".$people_net_adder." 
							from load_handler_stops
						where load_handler_stops.load_handler_id='".sql_friendly($row['id'])."'
							and load_handler_stops.trucks_log_id='".sql_friendly($row2['id'])."'
							and load_handler_stops.deleted=0	
					";
				}	
				$data3=simple_query($sql3);
				while($row3=mysqli_fetch_array($data3))
				{
					$lat=$row3['latitude'];
					$long=$row3['longitude'];
					$doner=$row3['linedate_completed'];
					$dater=$row3['linedate_pickup_eta'];
					
					$timezone_offset=$row3['timezone_offset'];
					$timezone_offset_dst=$row3['timezone_offset_dst'];
					$their_time=$nowtime + $timezone_offset + $timezone_offset_dst;	
					
					$finish_time=(strtotime($row3['linedate_pickup_eta']) - $their_time) / (60 * 60);	//hours left until delivery is late...
					
					$their_time=date("Y-m-d H:i:s",$their_time);	
										
					$activator=1;
					
					$last_gps_lat=0;
					$last_gps_long=0;
					$gps_dist=0;
					$gps_message="";
					$has_arriving=0;
					$has_arrived=0;
					$has_departed=0;
					$miles_remain_arriving=0;
					$miles_remain_arrived=0;
					$miles_remain_departed=0;
					$approx_arriving=0;
					$approx_arrived=0;
					$approx_departed=0;
					$conard_grade=0;
					
					if($doner>'2013-01-01 00:00:00')
					{	//already completed...
						$has_arriving=1;
						$has_arrived=1;
						$has_departed=1;
						$activator=0;	
					}
					else
					{	//not completed, but has GPS point for stop             if($lat>0 && $long>0)
						$get_local=mrr_find_only_location_of_this_truck($truck_id);
						
						$last_gps_lat=$get_local['latitude'];
						$last_gps_long=$get_local['longitude'];
						$last_gps_local=$get_local['location'];
						
						if($last_gps_lat==0 && $last_gps_long==0)
						{
							$last_gps_lat=$row3['pn_lat'];
							$last_gps_long=$row3['pn_long'];	
							$last_gps_local=$row3['pn_local'];
						}
						
						$gps_message="Truck ".$get_local['truck_name']." Is ".$last_gps_local."";//$get_local['gps_location']
													
						$gps_dist=mrr_distance_between_gps_points($lat,$long,$last_gps_lat,$last_gps_long);		//has MILES...
						$overall_hrs= $gps_dist / $mph;
						
						$gps_dist2=$gps_dist * $munits;					//convert distance in miles to feet....
						
						if($has_arriving==0)
						{
							if($gps_dist2 <= ($hl_r_arriving + $tolerance))								
							{
								$has_arriving=1;	//this part is done...
							}
							else
							{
								$miles_remain_arriving=($gps_dist - ($hl_r_arriving / $mph));
								$approx_arriving=$miles_remain_arriving / $mph;
							}	
						}
						if($has_arrived==0)
						{
							if($gps_dist2 <= ($hl_r_arrived + $tolerance))
							{
								$has_arrived=1;	//this part is done...
							}
							else
							{
								$miles_remain_arrived=($gps_dist - ($hl_r_arrived / $mph));
								$approx_arrived=$miles_remain_arrived / $mph;
							}	
						}
						if($has_departed==0)
						{	//leaving, so on way out and works backwards...
							if(($gps_dist2 + $tolerance) >= $hl_r_departed && $has_arrived==1)
							{
								$has_departed=1;	//this part is done...but only if arrived first...
							}
							else
							{
								$miles_remain_departed=( $gps_dist - ($hl_r_departed / $mph));
								$approx_departed=$miles_remain_departed / $mph;
							}	
						}
						
						$debugger.="
							<div>
								Load ".$load_id.", Dispatch ".$row2['id'].", Stop ".$row3['id'].": Distance ".$gps_dist." Miles (".$gps_dist2." Feet)
								, Section 1 complete=".$has_arriving." - Radius=".($hl_r_arriving + $tolerance)." Remaining ".$miles_remain_arriving." ft
								, Section 2 complete=".$has_arrived." - Radius=".($hl_r_arrived + $tolerance)." Remaining ".$miles_remain_arrived." ft
								, Section 3 complete=".$has_departed." - Radius=".($hl_r_departed + $tolerance)." Remaining ".$miles_remain_departed." ft
								.
							</div>";
													
						if(($overall_hrs * 2) > $finish_time)		$conard_grade=mrr_encode_geofencing_grade("Epic Fail");
						elseif(($overall_hrs * 2) > $finish_time)	$conard_grade=mrr_encode_geofencing_grade("Very Late");
						elseif($overall_hrs > $finish_time)		$conard_grade=mrr_encode_geofencing_grade("Late");
						elseif(($overall_hrs * 2) < $finish_time)	$conard_grade=mrr_encode_geofencing_grade("Early");
						elseif($overall_hrs <= $finish_time)		$conard_grade=mrr_encode_geofencing_grade("On Time");	
						
						if( $lat==0 || $long==0)
						{
							$conard_grade=0;	//if not dispatched through peoplenet, not long,lat GPS points, so do not grade it...
						}												
					}						
					
					$sql4="
						insert into ".mrr_find_log_database_name()."geofence_hot_load_tracking
							(id, 
          					linedate_added,
          					linedate,
          					deleted,
          					active,					
          					load_id,
          					dispatch_id,
          					stop_id,
          					driver_id,
          					truck_id,
          					trailer_id,
          					customer_id,					
          					dest_longitude,
          					dest_latitude,					
          					linedate_last_gps,
          					last_gps_longitude,
          					last_gps_latitude,					
          					dest_distance,
          					dest_message,					
          					dest_arriving,
          					dest_arrived,
          					dest_departed,					
          					dest_remaining_arriving,
          					dest_remaining_arrived,
          					dest_remaining_departed,					
          					dest_time_arriving,
          					dest_time_arrived,
          					dest_time_departed,		
          					dispatch_grade)
						values
							(NULL,
							NOW(),
							'".date("Y-m-d",strtotime($dater))."',
							0,
							'".sql_friendly($activator)."',
							'".sql_friendly($load_id)."',
							'".sql_friendly($row2['id'])."',
							'".sql_friendly($row3['id'])."',
							'".sql_friendly($driver_id)."',
							'".sql_friendly($truck_id)."',
							'".sql_friendly($trailer_id)."',
							'".sql_friendly($customer_id)."',
							'".sql_friendly($long)."',
							'".sql_friendly($lat)."',
							NOW(),
							'".sql_friendly($last_gps_long)."',
							'".sql_friendly($last_gps_lat)."',
							'".sql_friendly($gps_dist)."',
							'".sql_friendly($gps_message)."',
							'".sql_friendly($has_arriving)."',
							'".sql_friendly($has_arrived)."',
							'".sql_friendly($has_departed)."',
							'".sql_friendly($miles_remain_arriving)."',
							'".sql_friendly($miles_remain_arrived)."',
							'".sql_friendly($miles_remain_departed)."',
							'".sql_friendly($approx_arriving)."',
							'".sql_friendly($approx_arrived)."',
							'".sql_friendly($approx_departed)."',
							'".sql_friendly($conard_grade)."'								
							)	
					";						
					simple_query($sql4);						
				}
			}				
				
		}	
	}		
}

function mrr_send_geofencing_note($id,$sector=0)
{	//ID is the geofencing table, sector is which section (arriving,arrived,departed) 
	$note_id=0;
	$nowtime=time();
	
	$sector=(int)$sector;	
	$msg_body="";
	
	$res['sent']=$note_id;
	$res['sect']=$sector;
	$res['msg']="N/A";
			
	global $defaultsarray;
	$fromname=$defaultsarray['company_name'];	
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];	
	if($mph <=0)	$mph=1;
	
	$nowtime2=time();
	$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];
	$gmt_off = $gmt_off * 60 * 60 * -1;
	$nowtime2+=$gmt_off;	
	
	$template=(int) $defaultsarray['peoplenet_hot_msg_template_num'];
	if($template <= 0)	$template=0;
	if($template > 1)	$template=1;
	
	$arriving_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$arrived_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$departed_comp= $defaultsarray['peoplenet_hot_msg_arriving_insert'];
	$comp_logo = trim($defaultsarray['peoplenet_hot_msg_logo']);	
	$monitor_email=$defaultsarray['peoplenet_hot_msg_cc'];
		
	$mrr_template="";
	
	//$sector=0; //kill switch		
	if($id > 0 && $sector > 0)
	{		
		$sql="
			select * 
			from ".mrr_find_log_database_name()."geofence_hot_load_tracking
			where id='".sql_friendly($id)."'				
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$customer_id=$row['customer_id'];
			$load_id=$row['load_id'];
			$dispatch_id=$row['dispatch_id'];
			$stop_id=$row['stop_id'];	
			
			$has_arriving_date=$row['linedate_last_arriving'];
			$has_arrived_date=$row['linedate_last_arrived'];
			$has_departed_date=$row['linedate_last_departed'];
					
			$msg_sent_for_arriving=$row['msg_last_arriving'];
			$msg_sent_for_arrived=$row['msg_last_arrived'];
			$msg_sent_for_departed=$row['msg_last_departed'];
			
			$update_sql_adder="";
			
			$last_run=strtotime($row['linedate_last_msg']);	
			
			$time_run=($nowtime-$last_run)/(60*60);				//how many hours since last run...
			
			//customer settings....
			$cust=mrr_get_all_customer_settings($customer_id);
				
			$hl_compname=$cust['name_company'];
			$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
			$hl_timer=$cust['hot_load_timer'];					//interval between messages
			$hl_earriving=$cust['hot_load_email_arriving'];		//email addresses varchar
			$hl_earrived=$cust['hot_load_email_arrived'];		//email addresses varchar
			$hl_edeparted=$cust['hot_load_email_departed'];		//email addresses varchar
			$hl_marriving=$cust['hot_load_email_msg_arriving'];	//email message text
			$hl_marrived=$cust['hot_load_email_msg_arrived'];		//email message text
			$hl_mdeparted=$cust['hot_load_email_msg_departed'];	//email message text
			
			if(trim($hl_marriving)=="")	$hl_marriving=trim($arriving_comp);
			if(trim($hl_marrived)=="")	$hl_marrived=trim($arrived_comp);
			if(trim($hl_mdeparted)=="")	$hl_mdeparted=trim($departed_comp);
			
			$send_email_out=0;					//do not send if 0
			
			if($monitor_email==$hl_earrived)		$hl_earrived="";
			if($monitor_email==$hl_edeparted)		$hl_edeparted="";     			
			
			if($sector==2 && $msg_sent_for_arrived==0)
			{
				if(trim($hl_earrived)=="")		$hl_earrived=$monitor_email;	//bypass the skip for internal use....
				$send_email_out=1;
				$update_sql_adder="msg_last_arrived=1,";
				
				if($sector==2 && trim($hl_earrived)=="")
				{
					$res['msg']="No Email addresses set to send Arrived message.";	
					$send_email_out=0;
				}
			}
			if($sector==3 && $msg_sent_for_departed==0)
			{
				if(trim($hl_edeparted)=="")		$hl_edeparted=$monitor_email;	//bypass the skip for internal use....
				$send_email_out=1;
				$update_sql_adder="msg_last_departed=1,";
				
				if($sector==3 && trim($hl_edeparted)=="")
				{
					$res['msg']="No Email addresses set to send Departed message.";	
					$send_email_out=0;
				}
			}
			     			
			if($sector==1 && $hl_timer > 0)
			{
				$send_email_out=1;
				
				if($sector==1 && trim($hl_earriving)=="")
				{
					$res['msg']="No Email addresses set to send Arriving message.";	
					$send_email_out=0;
				}
				if($time_run < $hl_timer)
				{
					$res['msg']="Bad Timing: interval for next message is not yet ready.";	
					$send_email_out=0;
				}
				
			}
			     			
			//only do the rest if it is necessary to send an email...
			if($send_email_out==1)
			{     			
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
					where id='".sql_friendly($dispatch_id)."'				
				";
				$data_dispatch=simple_query($sql);
				$row_dispatch=mysqli_fetch_array($data_dispatch);
				
				//stop ...
     			$sql="
					select * 
					from load_handler_stops
					where id='".sql_friendly($stop_id)."'				
				";
				$data_stop=simple_query($sql);
				$row_stop=mysqli_fetch_array($data_stop);
				
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
     			
     			//create or use message.
     			$msg_body="";
     			$subject="CTS Geofence Update: Load ".$load_id." Status Update";
               	$tolist="";  
               	
               	$sc_label=" is on its way to pickup shipment from shipper.";  
     			
               	if($row_stop['stop_type_id']==2)
              		{
              			$sc_label=" is on its way to deliver shipment from consignee.";  
              		} 
              		
          		$msg_body_header="Conard Transportation Notice";
          		$msg_body="<br>Truck ".$truck_name."".$sc_label."";
          		$msg_body_footer="<br>This is an automated message.<br>";
     			
     			
     			
     			if($sector==1)
     			{
     				$tolist=trim($hl_earriving);	
     				
     				if($row['dest_arriving']==0)
     				{
     					$time_guesser=$row['dest_time_arriving'];
     					if($mph>0)	$time_guesser=abs($row['dest_remaining_arriving'])/$mph;
     					$mph_minutes=$time_guesser * 60;
     					
     					$msg_body.="<br><br>It is approximately ".abs($row['dest_remaining_arriving'])." Miles (at least ".number_format($time_guesser,2)." hours or ".number_format($mph_minutes,0)." minutes) away from arriving.";
     				}
     				else
     				{
     					$time_guesser=$row['dest_time_arrived'];
     					if($mph>0)	$time_guesser=abs($row['dest_remaining_arrived'])/$mph;
     					$mph_minutes=$time_guesser * 60;
     					
     					$msg_body.="<br><br>It is approaching your facility and is ".abs($row['dest_remaining_arrived'])." Miles (about ".number_format($time_guesser,2)." hours or ".number_format($mph_minutes,0)." minutes) away.";	
     				}    
     				
     				 $msg_body.=trim($hl_marriving);
     			}
     			if($sector==2)
     			{     					
     				$tolist=trim($hl_earrived);	
     				
     				if($row['dest_arrived']==0)
     				{
     					$time_guesser=$row['dest_time_arrived'];
     					if($mph>0)	$time_guesser=abs($row['dest_remaining_arrived'])/$mph;
     					$mph_minutes=$time_guesser * 60;
     					
     					$msg_body.="<br><br>It is approaching your facility and is ".abs($row['dest_remaining_arrived'])." Miles (about ".number_format($time_guesser,2)." hours or ".number_format($mph_minutes,0)." minutes) away.";
     					$send_email_out=0;
     				}
     				else
     				{
     					$msg_body.="<br><br>It is at your facility. Approximate Arrival Time: ".date("m/d/Y H:i",strtotime($row['linedate_last_arrived'])).".";
     					$send_email_out=1;	
     				}
     				
     				$msg_body.=trim($hl_marrived);
     			}
     			if($sector==3)
     			{          					
     				$tolist=trim($hl_edeparted);	
     				          				
     				$time_guesser=$row['dest_time_departed'];
     				
     				if($row['dest_departed']==0)
     				{
     					$msg_body.="<br><br>It is at your facility. Approximate Arrival Time: ".date("m/d/Y H:i",strtotime($row['linedate_last_arrived'])).".";
     					$send_email_out=0;
     				}
     				else
     				{
     					$msg_body.="<br><br>It is at your facility, and the driver has left the facility. Approximate Departure was: ".date("m/d/Y H:i",strtotime($row['linedate_last_departed'])).".";	
     					$send_email_out=1;
     				}
     				$msg_body.=trim($hl_mdeparted);
     			}
     			
     			$use_msg_body="".$msg_body_header."".$msg_body."".$msg_body_footer;
          		if($template>0)
          		{
          			$mrr_template=mrr_hot_load_msg_template_V2($template,$row_load,$row_dispatch,$row_stop,$msg_body);
          			$use_msg_body=$mrr_template;	
          		} 
			}
		}
	}
				
	$res['sent']=$note_id;
	$res['sect']=$sector;
	$res['msg']=$msg_body;
	$res['template']=$mrr_template;
	return $res;	
}


function mrr_pn_data_lag_checker($packet_type=0)
{	//sends email to developer to verify data lag for PN packet processing.
	$send_mail=0;
	$field_check="next_packet_id";							//locations
     
     global $defaultsarray;
		
	if($packet_type==2)		$field_check="next_msg_packet_id";		//messages
	if($packet_type==3)		$field_check="next_event_packet_id";	//dispatch events
	if($packet_type==4)		$field_check="elog_event_packet_id";	//driver elog events
	
	$ToName="Lord Vader";
	$To=$defaultsarray['special_email_monitor'];
	$From="mrr@conardtransportation.com";
	$FromName="C and C";
	
	$Subject="";
	$Html="";
	
	$sql="
		select truck_tracking_packets.*,
			(DATEDIFF(NOW(),truck_tracking_packets.linedate_added)) as days_old
		from ".mrr_find_log_database_name()."truck_tracking_packets
		where packet_id>0 and ".$field_check.">0
		order by ".$field_check." desc
		limit 1		
	";	
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		if($rows['days_old'] > 0)	$send_mail=1;
		
		$Subject="PN Packet Delay: ".$rows['packet_id']." last loaded on ".date("m/d/Y H:i:s", strtotime($rows['linedate_added'])).".";
		$Html="".$ToName.", <br><br> There is a delay retrieving the next packet ".$rows[ $field_check ].".  Please investigate. <br><br>".$FromName."";
	}
	else
	{
		$send_mail=1;
		$Subject="No record of packet type found.  ".$field_check." is zero.";
		$Html="".$ToName.", <br><br> No records are present ot determine next packet id for this type. Please investigate. <br><br>".$FromName."";	
	}
	
	if($send_mail==1)		mrr_trucking_sendMail_PN($To,$ToName,$From,$FromName,$Subject,$Html);	 
}



function mrr_deactivate_completed_geofence_rows()
{
	$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			stop_completed='1'
		where deleted=0  
			and dest_arriving>0 
			and dest_arrived>0 
			and dest_departed>0	
			and stop_completed=0			
	";
	simple_query($sql);	
}

function mrr_run_force_data_call_for_truck($truck_id=0)
{
	$mrr_adder="";
	if($truck_id>0)		$mrr_adder.=" and truck_id='".sql_friendly($truck_id)."'";
	
	$full_debugger="<br>";
			
	$sql="
	select id,name_truck
	from trucks
	where deleted=0
		and active>0
		and peoplenet_tracking>0
		".$mrr_adder."
	order by name_truck asc	
	";	
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$truck_id=$row['id'];
		$truck_name=$row['name_truck'];
		
		$full_debugger.="<br> Truck ID=".$truck_id.": ".$truck_name.".  ";
		$full_debugger.=mrr_peoplenet_find_data("odl",$truck_id,0,"",0,0);
		
	}
	$full_debugger.="<br>";	
	return $full_debugger;
}


function mrr_peoplenet_find_data_for_cron_job($cmd="",$truck=0,$dispatch_id=0,$message="",$packet=0,$msg_packet=0,$event_packet=0,$elog_event_packet=0,$save_blocker=0)
{	//command processing for peoplenet.php page only...a cron job...
	//stripped down version to debug and to send more output variables out...ONLY for messages
	//Also has 5 retries built in for the msg packets...		
	global $defaultsarray;
	$pn_cid = $defaultsarray['peoplenet_account_number'];	//"3577";
	$pn_pw = $defaultsarray['peoplenet_account_password'];	//"35con77";
	$pn_cid=trim($pn_cid);
	$pn_pw=trim($pn_pw);
	
	$mrr_convert_to_dst=1;
	$cntr_packet=0;
			
	$more_data=0;
	
	$packet_response_tag="";
	
	$prime_url="http://open.pfmlogin.com/scripts/open.dll";
	
	$moder=2;
	
	$truck_name="";
	if($truck>0)
	{
		$sql = "
			select *
			from trucks
			where deleted = 0
				and trucks.id = '".sql_friendly($truck) ."'
			order by name_truck asc
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$truck_name=$row['name_truck'];
			$truck_name=str_replace(" (Team Rate)","",$truck_name);
		}		
	}		
	$output="";
	
	if($truck==1520428)		$truck_name="1520428";
					
	$packet_response="";		
	$r_packet=0;
	
	$xml_page="";
	
	$url="";
	//creat URL for tracking based on command
	if($cmd=="oi_pnet_message_history")
	{
		$msg_packet=(int)$msg_packet;
		$found_packet=0;
		
		$url="http://open.pfmlogin.com/scripts/oi_pnet_message_history.dll?cid=".$pn_cid."&pw=".$pn_pw."&packet_id=".$msg_packet."";
		
		echo "PN URL for Packets: ".$url."<br>";
		
		$page=mrr_peoplenet_get_file_contents($url);
		
		$page_copy=$page;
		
		if(substr_count($page,"<packet_id>") > 0 && substr_count($page,"</packet_id>") > 0)
		{
			$packet_response_tag="NONE";	
			$opener=strpos($page,"<packet_id>");
			$closer=strpos($page,"</packet_id>",$opener) + 12;
			if($opener > 0 && $closer > 0)
			{
				$tmp_substr=substr($page,$opener,($closer - $opener));	
				$tmp_substr=str_replace("</packet_id>","",$tmp_substr);	
				$packet_response_tag=str_replace("<packet_id>","",$tmp_substr);	
			}
			
		}			
		
		if(substr_count($page,"<packet_id>-1</packet_id>") > 0)				$packet_response=-1;
		if(substr_count($page,"<packet_id>".$msg_packet."</packet_id>") > 0)		$packet_response=$msg_packet;
		if(substr_count($page,"<packet_id>".($msg_packet + 1)."</packet_id>") > 0)	$packet_response=($msg_packet + 1);
		if(substr_count($page,"<packet_id>".($msg_packet + 2)."</packet_id>") > 0)	$packet_response=($msg_packet + 2);
		if(substr_count($page,"<packet_id>".($msg_packet + 3)."</packet_id>") > 0)	$packet_response=($msg_packet + 3);
		if(substr_count($page,"<packet_id>".($msg_packet + 4)."</packet_id>") > 0)	$packet_response=($msg_packet + 4);
					
		if(substr_count($page,"<more_data/>") > 0)	$more_data=1;
		if(substr_count($page,"<more_data>") > 0)	$more_data=1;
		
		if(substr_count($page,"<pnet_imessage_data>") > 0 || substr_count($page,"<imessage>") > 0 )
		{	//if packet is not empty...save data to table.
			$xml_page=$page;
			$page=str_replace("</packet_id>","</packet_id><br>Start here 3:<br>",$page);
			$page=str_replace("<packet_id>","Packet (<b>",$page);
			$page=str_replace("</packet_id>","</b>)<br>",$page);
			
			$get_pack_num="";
			if(substr_count($page,"<br>Start here 3:<br>") > 0)
			{
				$pose=strpos($page,"<br>Start here 3:<br>");
				
				$get_pack_num=substr($page,0,($pose+21));
				$get_pack_num=str_replace("<br>Start here 3:<br>","",$get_pack_num);
				$get_pack_num=strip_tags($get_pack_num);
				
				$get_pack_num=str_replace("Packet (","",$get_pack_num);
				$get_pack_num=str_replace(")","",$get_pack_num);
				$get_pack_num=str_replace("-","",$get_pack_num);
				$get_pack_num=str_replace(" ","",$get_pack_num);
				$get_pack_num=trim($get_pack_num);
				
				$tmp_page=substr($page,($pose+21));
				$page=trim($tmp_page);
			}
			
			$form_req_text="";
			$form_ins_text="";
			$form_id=0;
			
			$page=str_replace("<form_id>125685</form_id>","<form_id>126373</form_id>",$page);
			
			$form_count=0;
			while(substr_count($page,"<form_id>126373</form_id>") > 0 && $form_count < 100)	
			{
				$form_id=125685;		//Repair Request Form
				$form_id=126373;	
				
				//pull out the request form....
				$form_page="";
				$fpos1=strpos($page,"<formdata>");
				$fpos2=strpos($page,"</formdata>",$fpos1);
				if($fpos1 > 0 && $fpos2 > 0 && $fpos2 > $fpos1)
				{
					$fpos2 += 11;
					$form_page=substr($page,$fpos1,($fpos2 - $fpos1));
					
					$page=str_replace($form_page,"<freeform_message>Repair Request Form sent.</freeform_message>",$page);
				}     				
				$form_req_text=$form_page;
				
				$sqli="
					insert into ".mrr_find_log_database_name()."pn_request_forms
						(id,
						linedate_added,
						processed,
						form_req_text,
						form_ins_text,
						form_id,
						truck_id,
						driver_name)
					values
						(NULL,
						NOW(),
						0,
						'".sql_friendly(trim($form_req_text))."',
						'',
						'".sql_friendly($form_id)."',
						0,
						'')
				";
				if($save_blocker==0)	simple_query($sqli);     				
				
				$form_count++;
			}
			$form_count=0;
			while(substr_count($page,"<form_id>113688</form_id>") > 0 && $form_count < 100)	
			{
				$form_id=113688;		//Inspection.
				     				
				//pull out the request form....
				$form_page="";
				$fpos1=strpos($page,"<formdata>");
				$fpos2=strpos($page,"</formdata>",$fpos1);
				if($fpos1 > 0 && $fpos2 > 0 && $fpos2 > $fpos1)
				{
					$fpos2 += 11;
					$form_page=substr($page,$fpos1,($fpos2 - $fpos1));
					
					$page=str_replace($form_page,"<freeform_message>Inspection Request sent.</freeform_message>",$page);
				}
				     				
				$form_ins_text=$form_page;
				     				
				$sqli="
					insert into ".mrr_find_log_database_name()."pn_request_forms
						(id,
						linedate_added,
						processed,
						form_req_text,
						form_ins_text,
						form_id,
						truck_id,
						driver_name)
					values
						(NULL,
						NOW(),
						0,
						'',
						'".sql_friendly(trim($form_ins_text))."',     						
						'".sql_friendly($form_id)."',
						0,
						'')
				";
				if($save_blocker==0)	simple_query($sqli);
				
				$form_count++;
			}
			/*		
			$page=str_replace("<formdata>","{formdata}",$page);   				$page=str_replace("</formdata>","{/formdata}",$page);
			
			$page=str_replace("<form_id>","{{",$page);	     				$page=str_replace("</form_id>","}}",$page);
			
			$page=str_replace("<im_field>","((",$page);	     				$page=str_replace("</im_field>","))",$page);
			
			$page=str_replace("<field_number>","...--...",$page);				$page=str_replace("</field_number>","",$page);
			$page=str_replace("<empty_at_start>","...--...",$page);			$page=str_replace("</empty_at_start>","",$page);
			$page=str_replace("<driver_modified>","...--...",$page);			$page=str_replace("</driver_modified>","",$page);
			$page=str_replace("<data>","",$page);							$page=str_replace("</data>","",$page);
			$page=str_replace("<data_numeric>","...--...",$page);				$page=str_replace("</data_numeric>","",$page);
			$page=str_replace("<data_text>","...--...",$page);				$page=str_replace("</data_text>","",$page);
			$page=str_replace("<data_multiple-choice>","...--...",$page);		$page=str_replace("</data_multiple-choice>","",$page);
			$page=str_replace("<mc_choicenum>","",$page);					$page=str_replace("</mc_choicenum>",":",$page);
			$page=str_replace("<mc_choicetext>","",$page);					$page=str_replace("</mc_choicetext>","",$page);
			$page=str_replace("<data_password>","...--...",$page);				$page=str_replace("</data_password>","",$page);
			$page=str_replace("<data_time>","...--...",$page);				$page=str_replace("</data_time>","",$page);
			$page=str_replace("<data_date-time>","...--...",$page);			$page=str_replace("</data_date-time>","",$page);
			$page=str_replace("<data_auto_drivername>","...--...",$page);		$page=str_replace("</data_auto_drivername>","",$page);
			$page=str_replace("<data_auto_location>","...--...",$page);			$page=str_replace("</data_auto_location>","",$page);
			$page=str_replace("<data_auto_latlong>","...--...",$page);			$page=str_replace("</data_auto_latlong>","",$page);
			$page=str_replace("<latitude>","",$page);						$page=str_replace("</latitude>",",",$page);
			$page=str_replace("<longitude>","",$page);						$page=str_replace("</longitude>","",$page);
			$page=str_replace("<data_auto_odometer>","...--...",$page);			$page=str_replace("</data_auto_odometer>","",$page);
			$page=str_replace("<data_auto_odometer_plus_gps>","...--...",$page);	$page=str_replace("</data_auto_odometer_plus_gps>","",$page);
			$page=str_replace("<data_performx_odometer>","",$page);			$page=str_replace("</data_performx_odometer>",":",$page);
			$page=str_replace("<data_gps_odometer>","...--...",$page);			$page=str_replace("</data_gps_odometer>","",$page);
			$page=str_replace("<data_sigcap>","...--...",$page);				$page=str_replace("</data_sigcap>","",$page);
			$page=str_replace("<data_barcode>","...--...",$page);				$page=str_replace("</data_barcode>","",$page);
			$page=str_replace("<bc_type>","",$page);						$page=str_replace("</bc_type>"," - ",$page);
			$page=str_replace("<bc_type_description>","",$page);				$page=str_replace("</bc_type_description>",":",$page);
			$page=str_replace("<bc_edited>","",$page);						$page=str_replace("</bc_edited>",":",$page);
			$page=str_replace("<bc_data>","",$page);						$page=str_replace("</bc_data>","",$page);
			$page=str_replace("<data_auto_fuel>","...--...",$page);			$page=str_replace("</data_auto_fuel>","",$page);
			$page=str_replace("<data_numeric-enhanced>","...--...",$page);		$page=str_replace("</data_numeric-enhanced>","",$page);
			$page=str_replace("<num_formatted>","",$page);					$page=str_replace("</num_formatted>",":(",$page);
			$page=str_replace("<num_raw>","",$page);						$page=str_replace("</num_raw>",")",$page);
			$page=str_replace("<data_date>","...--...",$page);				$page=str_replace("</data_date>","",$page);
			$page=str_replace("<data_auto_date-time>","...--...",$page);		$page=str_replace("</data_auto_date-time>","",$page);
			$page=str_replace("<data_image_ref>","...--...",$page);			$page=str_replace("</data_image_ref>","",$page);
			$page=str_replace("<data_image_date>","",$page);					$page=str_replace("</data_image_date>"," - ",$page);
			$page=str_replace("<data_image_transid>","",$page);				$page=str_replace("</data_image_transid>"," - ",$page);
			$page=str_replace("<data_image_name>","",$page);					$page=str_replace("</data_image_name>",":(",$page);
			$page=str_replace("<data_image_mimetype>","",$page);				$page=str_replace("</data_image_mimetype>",")",$page);
			//$page=str_replace("<>","...--...",$page);						$page=str_replace("</>","",$page);
			*/
			
			$page=str_replace("</","[[/",$page);
			$page=str_replace("<","[[=",$page);
			$page=str_replace(">","]",$page);
						
			$page=str_replace("[[=imessage]","<br>[[=imessage]",$page);
			
			$page=str_replace('?xml version="1.0" encoding="ISO-8859-1"?',"",$page);
			$page=str_replace('!DOCTYPE pnet_message_history_packet_response PUBLIC "-//PeopleNet//pnet_message_history_packet_response" "http://open.pfmlogin.com/dtd/pnet_message_history_packet_response.dtd"',"",$page);
			$page=str_replace("pnet_message_history_packet_response","",$page);
			$page=str_replace("more_data","",$page);
			
			$page=str_replace("][","] --- [",$page);
			$page=str_replace("[[=imessage] --- [","[[=imessage][",$page);
			
			//clear out empty sections
			$page=str_replace("[[/]","",$page);
			$page=str_replace("[[=]","",$page);
			$page=str_replace("[[=/]","",$page);
			
			
			$page=str_replace("[[/","</",$page);
			$page=str_replace("[[=","<",$page);
			$page=str_replace("]",">",$page);
						
			$page=strip_tags($page,"<br><b>");
			
			$output.="<div style='color:blue;'>Next Message Packet=".$get_pack_num."</div>";
			
			$output.="<div style='color:green;'>Found History Records for packet number ".$msg_packet.": ".($more_data > 0 ? "<b>More Data Found!!!</b>" : "")."</div>";
			
			$output.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
			
			$output.="     					
					<tr>
						<td valign='top'><b>Truck</b></td>
						<td valign='top'><b>Created</b></td>
						<td valign='top'><b>Received</b></td>
						<td valign='top'><b>RecipientID</b></td>
						<td valign='top'><b>RecipientName</b></td>
						<td valign='top'><b>MSN</b></td>
						<td valign='top'><b>BaseMSN</b></td>
						<td valign='top'><b>MsgType</b></td>
						<td valign='top'><b>Message</b></td>
					</tr>
					";
			
			$pieces = explode("<br>", $page);
			for($i=0; $i< count($pieces); $i++)
			{
				if($pieces[$i]!="" && $pieces[$i]!="<br>")
				{
					$output.="<tr>";
					$cntr=0;
					$track_arr[0]=0;
					
					$found_packet=1;
					
					$cols = explode(" --- ", $pieces[$i] );
					for($j=0; $j< count($cols); $j++)
					{
						if($cols[$j]!="" && $cols[$j]!=" --- ")
						{							
							$adder="";
							$adder2="";
							
							$cols[$j]=str_replace(" (Team Rate)","",$cols[$j]);
							
							$output.="<td valign='top'".$adder.">".$adder2."".$cols[$j]."</td>";		    							
							
							$track_arr[$cntr] = trim($cols[$j]);
							
							if($j==0)
							{
								$sql2 = "
                              			select id
                              			from trucks
                              			where deleted = 0
                              				and (trucks.name_truck = '".sql_friendly(trim($cols[$j])) ."' or trucks.name_truck = '".sql_friendly(trim($cols[$j])) ." (Team Rate)')
                              			order by name_truck asc
                              		";
                              		$data2 = simple_query($sql2);
                              		if($row2 = mysqli_fetch_array($data2))
                              		{
                              			$track_arr[9] = trim($cols[$j]);
                              			$cols[$j]=$row2['id'];
                              			$track_arr[0] = $row2['id'];                                   			
                              		}	
                              		
                              		//for test truck only
                              		if(trim($cols[$j])=="1520428")
                              		{
                              			$track_arr[9] = "1520428";
                              			$cols[$j]="1520428";
                              			$track_arr[0] = "1520428";                                   			
                              		}
							}
							
							//time_zone_adjuster
							if($cntr==2 && $mrr_convert_to_dst > 0)
							{
								$track_arr[2]=date("Y-m-d H:i:s",strtotime("1 hour",strtotime($track_arr[2])));
							}
							
							$cntr++;							
						}							
					}
					                   		
               		$useid=mrr_find_this_truck_tracking_msg_history($track_arr[0],$track_arr[9],$track_arr[2],$track_arr[3]);
               		if($useid>0)
               		{
               			$track_arr[1]=date("Y-m-d H:i:s",strtotime($track_arr[1]));
               			$track_arr[2]=date("Y-m-d H:i:s",strtotime($track_arr[2]));							
               			
               			if($save_blocker==0)	
               			{
               				mrr_peoplenet_truck_tracking_msg_history_update($useid,$track_arr);	
               			}
               		}
               		elseif($track_arr[0] > 0 )	
               		{
               			$track_arr[1]=date("Y-m-d H:i:s",strtotime($track_arr[1]));
               			$track_arr[2]=date("Y-m-d H:i:s",strtotime($track_arr[2]));							
						
						if($save_blocker==0)	
						{
               				$newid=mrr_peoplenet_truck_tracking_msg_history_add($track_arr,$msg_packet,$form_req_text,$form_ins_text);
               			}
               			$cntr_packet++;
					}
					
					$form_id=0;
					$form_req_text="";
					$form_ins_text="";
					
					$output.="</tr>";	
				}	
			}			
			$output.="</table>";	
			
			if($save_blocker > 0)		echo $output;
			
			if( $get_pack_num > $msg_packet)
			{
				
				$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes,
					next_event_packet_id,
					elog_event_packet_id)
				values
					(NULL,
					NOW(),
					'".sql_friendly($msg_packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'0',
					'".sql_friendly($get_pack_num)."',
					'Updated: oi_pnet_message_history: ".sql_friendly($_SERVER['REQUEST_URI'])."',
					'0',
					'0')
				";
				if($save_blocker==0)	simple_query($sql);
				
			}
			else
			{
				$output.="<div style='color:green;'>Message Packet ".$msg_packet." is not available at this time.</div>";	
			}
		}
		$output.="<div style='color:green;'>Packet ".$msg_packet." scanned, next packet is ".($msg_packet + 1).". ".date("m/d/Y H:i:s").".</div>";	
		if($found_packet==1)
		{
			$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes,
					next_event_packet_id,
					elog_event_packet_id)
				values
					(NULL,
					NOW(),
					'".sql_friendly($msg_packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'0',
					'".sql_friendly(($msg_packet+1))."',
					'Updated: oi_pnet_message_history: ".sql_friendly($_SERVER['REQUEST_URI'])."',
					'0',
					'0')
				";
				if($save_blocker==0)	simple_query($sql);
		}	
		
		$sql="
			insert into ".mrr_find_log_database_name()."truck_tracking_packet_xml
				(id,
				linedate_added,
				packet_id,
				packet_type,
				packet_xml)
			values
				(NULL,
				NOW(),
				'".sql_friendly($msg_packet)."',
				2,
				'".sql_friendly($page_copy)."')
		";
		//simple_query($sql);
		
		
		$r_packet=$msg_packet;
	}
	elseif($cmd=="oi_pnet_location_history")
	{
		$packet=(int)$packet;	
		$found_packet=0;		
		
		$url="http://open.pfmlogin.com/scripts/oi_pnet_location_history.dll?cid=".$pn_cid."&pw=".$pn_pw."&packet_id=".$packet."";
		
		$page=mrr_peoplenet_get_file_contents($url);
		$page_copy=$page;
		
		$sql="
			insert into ".mrr_find_log_database_name()."truck_tracking_packet_xml
				(id,
				linedate_added,
				packet_id,
				packet_type,
				packet_xml)
			values
				(NULL,
				NOW(),
				'".sql_friendly($packet)."',
				1,
				'".sql_friendly($page_copy)."')
		";
		//simple_query($sql);
		
		if(substr_count($page,"<loc_history>") > 0)
		{	//if packet is not empty...save data to table.
			$xml_page=$page;
			if(substr_count($page,"<more_data/>") > 0)	$more_data=1;
			if(substr_count($page,"<more_data>") > 0)	$more_data=1;
			
			$page=str_replace("</packet_id>","</packet_id><br>Start here 3:<br>",$page);
			$page=str_replace("<packet_id>","Packet (<b>",$page);
			$page=str_replace("</packet_id>","</b>)<br>",$page);
			
			$get_pack_num="";
			if(substr_count($page,"<br>Start here 3:<br>") > 0)
			{
				$pose=strpos($page,"<br>Start here 3:<br>");
				
				$get_pack_num=substr($page,0,($pose+21));
				$get_pack_num=str_replace("<br>Start here 3:<br>","",$get_pack_num);
				$get_pack_num=strip_tags($get_pack_num);
				
				$get_pack_num=str_replace("Packet (","",$get_pack_num);
				$get_pack_num=str_replace(")","",$get_pack_num);
				$get_pack_num=str_replace("-","",$get_pack_num);
				$get_pack_num=str_replace(" ","",$get_pack_num);
				$get_pack_num=trim($get_pack_num);
				
				$tmp_page=substr($page,($pose+21));
				$page=trim($tmp_page);
			}
			
			$page=str_replace("</","[[/",$page);
			$page=str_replace("<","[[=",$page);
			$page=str_replace(">","]",$page);
						
			$page=str_replace("[[=loc_history]","<br>[[=loc_history]",$page);
			
			$page=str_replace('?xml version="1.0" encoding="ISO-8859-1"?',"",$page);
			$page=str_replace('!DOCTYPE pnet_loc_history_packet PUBLIC "-//PeopleNet//pnet_loc_history_packet" "http://open.pfmlogin.com/dtd/pnet_loc_history_packet.dtd"',"",$page);
			$page=str_replace("pnet_loc_history_packet","",$page);
			$page=str_replace("more_data","",$page);
			
			$page=str_replace("][","] --- [",$page);
			$page=str_replace("[[=loc_history] --- [","[[=loc_history][",$page);
			
			//clear out empty sections
			$page=str_replace("[[/]","",$page);
			$page=str_replace("[[=]","",$page);
			$page=str_replace("[[=/]","",$page);
			
			
			$page=str_replace("[[/","</",$page);
			$page=str_replace("[[=","<",$page);
			$page=str_replace("]",">",$page);
						
			$page=strip_tags($page,"<br><b>");
			
			$output.="<div style='color:blue;'>Next Packet=".$get_pack_num."</div>";
			
			$output.="<div style='color:green;'>Found History Records for packet number ".$packet.":</div>";
			
			$output.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
			$output.="
					<tr>
						<td valign='top'><b>&nbsp;</b></td>
						<td valign='top'><b>Date</b></td>
						<td valign='top' align='right' align='right'><b>MPH</b></td>
						<td valign='top' align='right' align='right'><b>&nbsp;</b></td>
						<td valign='top' align='right' colspan='3><b>GPS</b></td>
						<td valign='top' align='right'><b>&nbsp;</b></td>
						<td valign='top' align='right'><b>&nbsp;</b></td>
						<td valign='top' align='right' colspan='3'><b>GPS</b></td>
						<td valign='top' align='right' colspan='4'><b>PerformX</b></td>
					</tr>
					<tr>
						<td valign='top'><b>Truck</b></td>
						<td valign='top'><b>Time</b></td>
						<td valign='top' align='right'><b>Speed</b></td>
						<td valign='top' align='right'><b>Dir</b></td>
						<td valign='top' align='right'><b>Quality</b></td>
						<td valign='top' align='right'><b>Latitude</b></td>
						<td valign='top' align='right'><b>Longitude</b></td>
						<td valign='top'> <b>Location</b></td>
						<td valign='top' align='right'><b>Fix</b></td>
						<td valign='top' align='right'><b>Ignition</b></td>
						<td valign='top' align='right'><b>Odometer</b></td>
						<td valign='top' align='right'><b>Rolling Odom</b></td>
						<td valign='top' align='right'><b>Odom</b></td>
						<td valign='top' align='right'><b>Fuel</b></td>
						<td valign='top' align='right'><b>Speed </b></td>
						<td valign='top' align='right'><b>Idle</b></td>
					</tr>";
			
			$pieces = explode("<br>", $page);
			for($i=0; $i< count($pieces); $i++)
			{
				if($pieces[$i]!="" && $pieces[$i]!="<br>")
				{
					$output.="<tr>";
					$cntr=0;
					$track_arr[0]=0;
					
					$found_packet=1;
					
					$cols = explode(" --- ", $pieces[$i] );
					for($j=0; $j< count($cols); $j++)
					{
						if($cols[$j]!="" && $cols[$j]!=" --- ")
						{							
							$adder="";
							$adder2="";
							if($j!=0 && $j!=1 && $j!=7)
							{
								$adder=" align='right'";	
							}
							if($j==7)
							{
								$adder=" style='font-weight:bold;'";
								$adder2=" ";	
							}
							
							$output.="<td valign='top'".$adder.">".$adder2."".$cols[$j]."</td>";		
							
							$track_arr[$cntr] = trim($cols[$j]);
							
							if($j==0)
							{
								$sql2 = "
                              			select id
                              			from trucks
                              			where deleted = 0
                              				and trucks.name_truck = '".sql_friendly(trim($cols[$j])) ."'
                              			order by name_truck asc
                              		";
                              		$data2 = simple_query($sql2);
                              		if($row2 = mysqli_fetch_array($data2))
                              		{
                              			$cols[$j]=$row2['id'];
                              			$track_arr[0] = $row2['id'];
                              		}	
                              		
                              		//for test truck only
                              		if(trim($cols[$j])=="1520428")
                              		{
                              			$cols[$j]="1520428";
                              			$track_arr[0] = "1520428";
                              		}
							}
							
							$cntr++;							
						}							
					}
					$track_arr[17]=$packet;
               		$track_arr[18]=0;
               		$track_arr[19]=0;			
					/*
               		$track_arr[0]		//truck_id
               		$track_arr[1]		//linedate
               		$track_arr[2]		//truck_speed
               		$track_arr[3]		//truck_heading
               		$track_arr[4]		//gps_quality
               		$track_arr[5]		//latitude
               		$track_arr[6]		//longitude
               		$track_arr[7]		//location
               		$track_arr[8]		//fix_type
               		$track_arr[9]		//ignition
               		$track_arr[10]		//gps_odometer
               		$track_arr[11]		//gps_rolling_odometer
               		$track_arr[12]		//performx_odometer
               		$track_arr[13]		//performx_fuel
               		$track_arr[14]		//performx_speed
               		$track_arr[15]		//performx_idle
               		$track_arr[16]		//serial_number
               		$track_arr[17]		//packet_number
               		$track_arr[18]		//driver_id
               		$track_arr[19]		//driver2_id
               		
               		*/
               		
               		$useid=mrr_find_this_truck_time_tracking($track_arr[0],$track_arr[1]);
               		if($useid>0)
               		{
               			mrr_peoplenet_truck_tracking_update($track_arr,$useid);	
               		}
               		elseif($track_arr[0] > 0 || $track_arr[0]!="")	
               		{
               			$newid=mrr_peoplenet_truck_tracking_add($track_arr);
					}
					$output.="</tr>";	
				}	
			}			
			$output.="</table>";	
			
			if( $get_pack_num > $packet)
			{
				$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes,
					next_event_packet_id,
					elog_event_packet_id)
				values
					(NULL,
					NOW(),
					'".sql_friendly($packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'".sql_friendly($get_pack_num)."',
					'0',
					'Updated: oi_pnet_location_history: ".sql_friendly($_SERVER['REQUEST_URI'])."',
					'0',
					'0')
				";
				simple_query($sql);
			}
			else
			{
				$output.="<div style='color:green;'>Packet ".$packet." is not available at this time.</div>";	
			}
		}	
		
		if($found_packet==1)
		{
			$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes,
					next_event_packet_id,
					elog_event_packet_id)
				values
					(NULL,
					NOW(),
					'".sql_friendly($packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'".sql_friendly(($packet + 1))."',
					'0',
					'Updated: oi_pnet_location_history: ".sql_friendly($_SERVER['REQUEST_URI'])."',
					'0',
					'0')
				";
				simple_query($sql);		
		}
		$r_packet=$packet;
		
		$sql="
			insert into ".mrr_find_log_database_name()."truck_tracking_packet_xml
				(id,
				linedate_added,
				packet_id,
				packet_type,
				packet_xml)
			values
				(NULL,
				NOW(),
				'".sql_friendly($packet)."',
				1,
				'".sql_friendly($page_copy)."')
		";
		//simple_query($sql);
	}
	elseif($cmd=="oi_pnet_dispatch_events")
	{
		$event_packet=(int)$event_packet;	
		$found_packet=0;		
		
		$url="http://open.pfmlogin.com/scripts/oi_pnet_dispatch_events.dll?cid=".$pn_cid."&pw=".$pn_pw."&packet_id=".$event_packet."";
		
		$page=mrr_peoplenet_get_file_contents($url);
		
		if(substr_count($page,"<more_data/>") > 0)	$more_data=1;
		if(substr_count($page,"<more_data>") > 0)	$more_data=1;
					
		if(substr_count($page,"<dispatch_event_cl1>") > 0 && substr_count($page,"failure") == 0)
		{	//if packet is not empty...save data to table.
						
			$ecm_miles=0;							//just in case we need this later...
			if(substr_count($page,"</ecm_miles>") > 0)	$ecm_miles=1;
			if(substr_count($page,"<ecm_miles>") > 0)	$ecm_miles=1;
			if(substr_count($page,"<ecm_miles/>") > 0)	$ecm_miles=1;
							
			$page=str_replace("</packet_id>","</packet_id><br>Start here 3:<br>",$page);
			$page=str_replace("<packet_id>","Packet (<b>",$page);
			$page=str_replace("</packet_id>","</b>)<br>",$page);
			
			//add the optional ones back...with no data...    			
			if(substr_count($page,"<stopid>") == 0)		$page=str_replace("</e_detail>","</e_detail><stopid>0</stopid>",$page); 
			if(substr_count($page,"<s_udata>") == 0)	$page=str_replace("</stopid>","</stopid><s_udata>0</s_udata>",$page); 
			
			//remove unneeded tags
			$page=str_replace("<e_detail>","",$page);
			$page=str_replace("</e_detail>","",$page);  
			$page=str_replace("<e_info>","",$page);
			$page=str_replace("</e_info>","",$page);  
               
               $xml_page=$page;
			
			$get_pack_num="";
			if(substr_count($page,"<br>Start here 3:<br>") > 0)
			{
				$pose=strpos($page,"<br>Start here 3:<br>");
				
				$get_pack_num=substr($page,0,($pose+21));
				$get_pack_num=str_replace("<br>Start here 3:<br>","",$get_pack_num);
				$get_pack_num=strip_tags($get_pack_num);
				
				$get_pack_num=str_replace("Packet (","",$get_pack_num);
				$get_pack_num=str_replace(")","",$get_pack_num);
				$get_pack_num=str_replace("-","",$get_pack_num);
				$get_pack_num=str_replace(" ","",$get_pack_num);
				$get_pack_num=trim($get_pack_num);
				
				$tmp_page=substr($page,($pose+21));
				$page=trim($tmp_page);
			}
			   			
			
			$page=str_replace("</","[[/",$page);
			$page=str_replace("<","[[=",$page);
			$page=str_replace(">","]",$page);
						
			$page=str_replace("[[=dispatch_event_cl1]","<br>[[=dispatch_event_cl1]",$page);
			
			$page=str_replace('?xml version="1.0" encoding="ISO-8859-1"?',"",$page);
			$page=str_replace('!DOCTYPE pnet_dispatch_events_packet_response PUBLIC "-//PeopleNet//pnet_dispatch_events_packet_response" "http://open.peoplenetoline.com/dtd/pnet_dispatch_events_packet_response.dtd"',"",$page);
			$page=str_replace('!DOCTYPE pnet_dispatch_events_packet_response PUBLIC "-//PeopleNet//pnet_dispatch_events_packet_response" "http://open.pfmlogin.com/dtd/pnet_dispatch_events_packet_response.dtd"',"",$page);
			$page=str_replace("pnet_dispatch_events_packet_response","",$page);
			$page=str_replace("more_data","",$page);
			
			$page=str_replace("][","] --- [",$page);
			$page=str_replace("[[=dispatch_event_cl1] --- [","[[=dispatch_event_cl1][",$page);
			
			//clear out empty sections
			$page=str_replace("[[/]","",$page);
			$page=str_replace("[[=]","",$page);
			$page=str_replace("[[=/]","",$page);
			
			
			$page=str_replace("[[/","</",$page);
			$page=str_replace("[[=","<",$page);
			$page=str_replace("]",">",$page);
						
			$page=strip_tags($page,"<br><b>");
			
			$output.="<div style='color:blue;'>Next Packet=".$get_pack_num."</div>";
			
			$output.="<div style='color:green;'>Found History Records for packet number ".$event_packet.":</div>";
			
			$output.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
			$output.="
					<tr>
						<td valign='top'><b>PN Disp</b></td>
						<td valign='top'><b>PN Data</b></td>
						<td valign='top'><b>Truck Name</b></td>
						<td valign='top'><b>Date</b></td>
						<td valign='top'><b>eType</b></td>
						<td valign='top'><b>eReason</b></td>
						<td valign='top' align='right'><b>Latitude</b></td>
						<td valign='top' align='right'><b>Longitude</b></td>
						<td valign='top' align='right'><b>Fuel</b></td>
						<td valign='top' align='right'><b>Odometer</b></td>
						<td valign='top' align='right'><b>Odom Type</b></td>
						<td valign='top'><b>PN Stop</b></td> 
						<td valign='top'> <b>Stop Data</b></td>  
						<td valign='top' align='right'><b>Load</b></td>
						<td valign='top' align='right'><b>Disp</b></td>
						<td valign='top' align='right'><b>Stop</b></td>
						<td valign='top' align='right'><b>TruckID</b></td>
						<td valign='top' align='right'><b>PacketID</b></td>
					</tr>
			";      
			
			$pieces = explode("<br>", $page);
			for($i=0; $i< count($pieces); $i++)
			{
				if($pieces[$i]!="" && $pieces[$i]!="<br>")
				{
					$output.="<tr>";
					$cntr=0;
					$track_arr[0]=0;
					
					$found_packet=1;
					
					$cols = explode(" --- ", $pieces[$i] );
					for($j=0; $j< count($cols); $j++)
					{
						if($cols[$j]!="" && $cols[$j]!=" --- ")
						{							
							$adder="";
							$adder2="";
							if($j!=0 && $j!=1 && $j!=7)
							{
								$adder=" align='right'";	
							}
							if($j==7)
							{
								$adder=" style='font-weight:bold;'";
								$adder2=" ";	
							}
							
							$output.="<td valign='top'".$adder.">".$adder2."".$cols[$j]."</td>";		
							
							$track_arr[$cntr] = trim($cols[$j]);
							
							if($j==2)
							{
								$sql2 = "
                              			select id
                              			from trucks
                              			where deleted = 0
                              				and trucks.name_truck = '".sql_friendly(trim($cols[$j])) ."'
                              			order by name_truck asc
                              		";
                              		$data2 = simple_query($sql2);
                              		if($row2 = mysqli_fetch_array($data2))
                              		{
                              			$cols[$j]=$row2['id'];
                              			$track_arr[16] = $row2['id'];
                              		}	
                              		
                              		//for test truck only
                              		if(trim($cols[$j])=="1520428")
                              		{
                              			$cols[$j]="1520428";
                              			$track_arr[16] = "1520428";
                              		}
							}
							
							//time_zone_adjuster
							if($cntr==3 && $mrr_convert_to_dst > 0)
							{
								$track_arr[3]=date("Y-m-d H:i:s",strtotime("1 hour",strtotime($track_arr[3])));
							}
							
							$cntr++;							
						}							
					}
					$sql2 = "
						select dispatch_id
						from ".mrr_find_log_database_name()."truck_tracking_dispatches 
						where peoplenet_id='".sql_friendly(trim($track_arr[0]))."'
						order by id desc 
					";
                         $data2 = simple_query($sql2);
                         if($row2 = mysqli_fetch_array($data2))
                         {
                              $track_arr[14] = (int) $row2['dispatch_id'];
                         }                                   		
					$track_arr[13]=str_replace("Load","",$track_arr[1]);		
					$track_arr[13]=str_replace(": Dispatch ".$track_arr[14]."" , "" , $track_arr[13]);	
					$track_arr[13]=trim($track_arr[13]);
										
               		$track_arr[15]=0;
					$track_arr[17]=$event_packet;		
					/*
					$track_arr[0]		//pn_dispatch_id
               		$track_arr[1]		//pn_data....not saved...
               		$track_arr[2]		//truck name/number (not ID)
               		$track_arr[3]		//linedate
               		                    		
               		$track_arr[4]		//e_type
               		$track_arr[5]		//e_reason
               		$track_arr[6]		//e_latitude
               		$track_arr[7]		//e_longitude
               		$track_arr[8]		//px_fuel
               		$track_arr[9]		//px_odometer
               		$track_arr[10]		//px_odo_type
               		
               		$track_arr[11]		//pn_stop_id
               		$track_arr[12]		//stop_data
               		
               		$track_arr[13]		//load_id
               		$track_arr[14]		//disptach_id
               		$track_arr[15]		//stop_id
               		$track_arr[16]		//truck_id
					$track_arr[17]		//packet_id                    		
               		*/
               		
               		$track_arr[3]=date("Y-m-d H:i:s",strtotime($track_arr[3]));
               		
					$newid=mrr_peoplenet_truck_tracking_events_add($track_arr);
					
					$output.="
							<td valign='top'>".$track_arr[13]."</td>
							<td valign='top'>".$track_arr[14]."</td>
							<td valign='top'>".$track_arr[15]."</td>
							<td valign='top'>".$track_arr[16]."</td>
							<td valign='top'>".$track_arr[17]."</td>
						</tr>
					";	
				}	
			}			
			$output.="</table>";	
			
			if( $get_pack_num > $event_packet || $more_data > 0)
			{
				$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes,
					next_event_packet_id,
					elog_event_packet_id)
				values
					(NULL,
					NOW(),
					'".sql_friendly($event_packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'0',
					'0',
					'Updated: oi_pnet_dispatch_events: ".sql_friendly($_SERVER['REQUEST_URI'])."',
					'".sql_friendly($get_pack_num)."',
					0)
				";
				simple_query($sql);
			}
			else
			{
				$output.="<div style='color:green;'>Packet ".$event_packet." is not available at this time.</div>";	
			}
		}
					
		if($found_packet==1 || $more_data>0)
		{
			$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes,
					next_event_packet_id,
					elog_event_packet_id)
				values
					(NULL,
					NOW(),
					'".sql_friendly($event_packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'0',
					'0',
					'Updated: oi_pnet_dispatch_events: ".sql_friendly($_SERVER['REQUEST_URI'])."',
					'".sql_friendly(($event_packet + 1))."',
					0)
				";
				simple_query($sql);		
		}
		
		$r_packet=$event_packet;
	}
	elseif($cmd=="elog_events")
	{
		$elog_event_packet=(int)$elog_event_packet;	
		$found_packet=0;		
			 
		$url="http://open.pfmlogin.com/scripts/open.dll?cid=".$pn_cid."&pw=".$pn_pw."&service=elog_events&packet_id=".$elog_event_packet."&compat_level=2";
		
		$page=mrr_peoplenet_get_file_contents($url);
		
		if(substr_count($page,"<more_data/>") > 0)	$more_data=1;
		if(substr_count($page,"<more_data>") > 0)	$more_data=1;
					
		if(substr_count($page,"<elog_data>") > 0 && substr_count($page,"failure") == 0)
		{	//if packet is not empty...save data to table.				
							
			$page=str_replace("</packet_id>","</packet_id><br>Start here 3:<br>",$page);
			$page=str_replace("<packet_id>","Packet (<b>",$page);
			$page=str_replace("</packet_id>","</b>)<br>",$page);  			
			     			
			//remove unneeded tags <OtherSettings>
			$page=str_replace('?xml version="1.0" encoding="ISO-8859-1"?',"",$page);
			$page=str_replace('!DOCTYPE pnet_oi_elog_events_rich PUBLIC "-//PeopleNet//pnet_oi_elog_events_rich" "http://open.peoplenetonline.com/dtd/pnet_oi_elog_events_rich.dtd"',"",$page);
			$page=str_replace('!DOCTYPE pnet_oi_elog_events_rich PUBLIC "-//PeopleNet//pnet_oi_elog_events_rich" "http://open.pfmlogin.com/dtd/pnet_oi_elog_events_rich.dtd"',"",$page);
			$page=str_replace("<pnet_oi_elog_events_rich>","",$page);      			
			$page=str_replace("</pnet_oi_elog_events_rich>","",$page); 
			$page=str_replace("<ds_flag>1</ds_flag>","",$page); 
			$page=str_replace("<ds_flag>2</ds_flag>","",$page); 
			
			$page=str_replace("</effective>","</effective><mrrblob>",$page); 
			$page=str_replace("<OtherSettings>","</mrrblob><mrrsettings>",$page); 
			$page=str_replace("</OtherSettings>","</mrrsettings>",$page); 
			$page=str_replace("<othersettings>","",$page); 
			$page=str_replace("</othersettings>","",$page); 
			
			$page=str_replace('<setting label="',"Setting{",$page); 
			$page=str_replace('">',"}={",$page); 
			$page=str_replace("</setting>","}. ",$page); 
			
			$page=str_replace("<event_data/>","<event_data></event_data>",$page); //convert single empty tag to empty container
			$page=str_replace("<event_data>","Event({ ",$page); 
			$page=str_replace("</event_data>"," }}",$page); 
			
			$page=str_replace("<remark>","<remark>MRR_Remark: ",$page); 
			
               $xml_page=$page;
			
			$get_pack_num="";
			if(substr_count($page,"<br>Start here 3:<br>") > 0)
			{
				$pose=strpos($page,"<br>Start here 3:<br>");
				
				$get_pack_num=substr($page,0,($pose+21));
				$get_pack_num=str_replace("<br>Start here 3:<br>","",$get_pack_num);
				$get_pack_num=strip_tags($get_pack_num);
				
				$get_pack_num=str_replace("Packet (","",$get_pack_num);
				$get_pack_num=str_replace(")","",$get_pack_num);
				$get_pack_num=str_replace("-","",$get_pack_num);
				$get_pack_num=str_replace(" ","",$get_pack_num);
				$get_pack_num=trim($get_pack_num);
				
				$tmp_page=substr($page,($pose+21));
				$page=trim($tmp_page);
			}
			   			
			
			$page=str_replace("</","[[/",$page);
			$page=str_replace("<","[[=",$page);
			$page=str_replace(">","]",$page);
						
			$page=str_replace("[[=elog_data]","<br>[[=elog_data]",$page);
			
			$page=str_replace('?xml version="1.0" encoding="ISO-8859-1"?',"",$page);
			$page=str_replace('!DOCTYPE pnet_dispatch_events_packet_response PUBLIC "-//PeopleNet//pnet_dispatch_events_packet_response" "http://open.pfmlogin.com/dtd/pnet_oi_elog_events_rich.dtd"',"",$page);
			$page=str_replace("pnet_oi_elog_events_rich","",$page);
			$page=str_replace("more_data","",$page);
			
			$page=str_replace("][","] --- [",$page);
			$page=str_replace("[[=elog_data] --- [","[[=elog_data][",$page);
			
			//clear out empty sections
			$page=str_replace("[[/]","",$page);
			$page=str_replace("[[=]","",$page);
			$page=str_replace("[[=/]","",$page);
			
			
			$page=str_replace("[[/","</",$page);
			$page=str_replace("[[=","<",$page);
			$page=str_replace("]",">",$page);
						
			$page=strip_tags($page,"<br><b>");
			
			$output.="<div style='color:blue;'>Next Elog Event Packet=".$get_pack_num."</div>";
			
			$output.="<div style='color:green;'>Found Elog Event Records for packet number ".$elog_event_packet.":</div>";
			
			$output.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
			$output.="
					<tr>
						<td valign='top'><b>Elog Event</b></td>
						<td valign='top'><b>Driver</b></td>
						<td valign='top'><b>Created Date</b></td>
						<td valign='top'><b>Effective Date</b></td>
						<td valign='top'><b>EventData</b></td> 
						<td valign='top'><b>Settings</b></td> 
						<td valign='top'><b>Remark</b></td>
					</tr>
			";      
			
			$pieces = explode("<br>", $page);
			for($i=0; $i< count($pieces); $i++)
			{
				$used_remarks=0;     				if(substr_count($pieces[$i], "MRR_Remark: ") > 0)		{	$used_remarks=1;		$pieces[$i]=str_replace("MRR_Remark: ","",$pieces[$i]);	}
				$used_events=0;     				if(substr_count($pieces[$i], "Event({") > 0)			{	$used_events=1;			}
				$used_settings=0;     				if(substr_count($pieces[$i], "Setting{") > 0)		{	$used_settings=1;			}
				
				if($pieces[$i]!="" && $pieces[$i]!="<br>")
				{
					$output.="<tr>";
					$cntr=0;
					$track_arr[0]=0;
					$track_arr[1]="0";    					
					$track_arr[2]="";
               		$track_arr[3]="";
					$track_arr[4]="";
					$track_arr[5]="";
					
					$found_packet=1;
					
					$e1="";
					$e2="";
					$e3="";
					$e4="";
					$s1="";
					$s2="";
					$s3="";
					$s4="";
					
					$cols = explode(" --- ", $pieces[$i] );
					for($j=0; $j< count($cols); $j++)
					{
						if($cols[$j]!="" && $cols[$j]!=" --- ")
						{	    							
							$track_arr[$cntr] = trim($cols[$j]);
							
							//time_zone_adjuster
							if($cntr==2 && $mrr_convert_to_dst > 0)
							{
								$track_arr[2]=date("Y-m-d H:i:s",strtotime("1 hour",strtotime($track_arr[2])));
							}
							if($cntr==3 && $mrr_convert_to_dst > 0)
							{
								$track_arr[3]=date("Y-m-d H:i:s",strtotime("1 hour",strtotime($track_arr[3])));
							}
							
							$cntr++;							
						}							
					}
               		
               		$track_arr[2]=date("Y-m-d H:i:s",strtotime($track_arr[2]));
               		$track_arr[3]=date("Y-m-d H:i:s",strtotime($track_arr[3]));
					
					$track_arr[1]=str_replace("#","",$track_arr[1]);
					
					if($track_arr[1]=="3577")	$track_arr[1]="371";
					if($track_arr[1]=="6969")	$track_arr[1]="0";
					if($track_arr[1]=="test")	$track_arr[1]="0";
					
					$output.="<td valign='top'>".$track_arr[0]." (".mrr_elog_event_types($track_arr[0]).")</td>";	//Event ID
					$output.="<td valign='top'>".$track_arr[1]."</td>";	//Driver
					$output.="<td valign='top'>".$track_arr[2]."</td>";	//Created
					$output.="<td valign='top'>".$track_arr[3]."</td>";	//Effective 
					
					//Event Data
					if($used_events==0)		
					{
						$output.="<td valign='top'>&nbsp;</td>";	
					}
					else
					{
						$e_list="";
						$eres=mrr_parse_elog_event_data($track_arr[4]);
						for($e=0; $e< $eres['num']; $e++)
						{
							$e_list.="".$eres['val'][$e]."<br>";
							
							if($e==0)	$e1=$eres['val'][$e];
							if($e==1)	$e2=$eres['val'][$e];
							if($e==2)	$e3=$eres['val'][$e];
							//if($e==3)	$e4=$eres['val'][$e];
						}     						
						$output.="<td valign='top'>".$e_list."</td>";	
					}
					
					//Event Settings
					if($used_settings==0)
					{
						$output.="<td valign='top'>&nbsp;</td>";
					}
					else
					{
						$s_list="";
						$e4="Settings: ";     						
						
						$sres=mrr_parse_elog_event_settings($track_arr[5]);
						for($s=0; $s< $sres['num']; $s++)
						{
							$s_list.="".$sres['key'][$s]." = ".$sres['val'][$s]."<br>";
							
							if($s==0)	{	$s1=$sres['val'][$s];	$e4.="".$sres['key'][$s]."";		}
							if($s==1)	{	$s2=$sres['val'][$s];	$e4.=", ".$sres['key'][$s]."";	}
							if($s==2)	{	$s3=$sres['val'][$s];	$e4.=", ".$sres['key'][$s]."";	}
							if($s==3)	{	$s4=$sres['val'][$s];	$e4.=", ".$sres['key'][$s]."";	}
						}
						$output.="<td valign='top'>".$s_list."</td>";	     					
					}
					if($used_remarks==0)	$output.="<td valign='top'>&nbsp;</td>";
					     					
					$output.="     							
						</tr>
					";
					
					$newid=mrr_add_elog_event_entry($track_arr[0], $track_arr[1], $track_arr[2], $track_arr[3],$e1,$e2,$e3,$e4,$s1,$s2,$s3,$s4,$elog_event_packet);
				}	
			}			
			$output.="</table>";	
			
			if( $get_pack_num > $elog_event_packet || $more_data>0)
			{
				
				$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes,
					next_event_packet_id,
					elog_event_packet_id)
				values
					(NULL,
					NOW(),
					'".sql_friendly($elog_event_packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'0',
					'0',
					'Updated: elog_events: ".sql_friendly($_SERVER['REQUEST_URI'])."',
					'0',
					'".sql_friendly($get_pack_num)."')
				";
				simple_query($sql);
				
			}
			else
			{
				$output.="<div style='color:green;'>Packet ".$elog_event_packet." is not available at this time.</div>";	
			}
		}	
		
		if($found_packet==1 || $more_data>0)
		{
			$sql="
				insert into ".mrr_find_log_database_name()."truck_tracking_packets
					(id,
					linedate_added,
					packet_id,					
					user_id,
					linedate,
					next_packet_id,
					next_msg_packet_id,
					page_load_notes,
					next_event_packet_id,
					elog_event_packet_id)
				values
					(NULL,
					NOW(),
					'".sql_friendly($elog_event_packet)."',
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'0',
					'0',
					'Updated: elog_events: ".sql_friendly($_SERVER['REQUEST_URI'])."',
					'0',
					'".sql_friendly(($elog_event_packet + 1))."')
				";
				simple_query($sql);		
		}
		
		$r_packet=$elog_event_packet;
	}
	elseif($cmd=="oi_pnet_driver_view")
	{			 
		$url="http://oi.pfmlogin.com/scripts/oi_pnet_driver_view.dll?cid=".$pn_cid."&pw=".$pn_pw."&requestDrivertype=3";		//&requestTerminal=601  ...Terminal number...
		
		$page=mrr_peoplenet_get_file_contents($url);
					
		$output=$page;
		$xml_page=$page;
		
	}	
	elseif($cmd=="elog_dispatch_info")
	{
		$url="http://oi.pfmlogin.com/scripts/open.dll?cid=".$pn_cid."&pw=".$pn_pw."&service=elog_dispatch_info";		//&driverid=22315
		
		$page=mrr_peoplenet_get_file_contents($url);
					
		$output=$page;
		$xml_page=$page;
	}
	$res['packet_response_id']=$packet_response;
	$res['packet_response_id_tag']=$packet_response_tag;
	$res['more_data']=$more_data; 
	$res['packet_id']=$r_packet; 
	$res['packet_cntr']=$cntr_packet;
	$res['next_packet_id']=($r_packet+1);
	$res['output']=$output;
	$res['xml']=$xml_page;
	return $res;
}
	

function mrr_create_packet_xml_record($packet_id,$packet_type,$packet_xml)
{
	global $datasource;

	$sql = "
		insert into ".mrr_find_log_database_name()."truck_tracking_packet_xml
			(id,
			packet_id,
               packet_type, 
               packet_xml,                     	
               linedate_added)
		values
			(NULL,
			'".sql_friendly($packet_id)."',
			'".sql_friendly($packet_type)."',
			'".sql_friendly($packet_xml)."',
			NOW())
	";
	simple_query($sql);
	$newid=mysqli_insert_id($datasource);
	return $newid;		
}

	
function mrr_add_elog_event_entry($eid,$driver_id,$created,$effective,$e1,$e2,$e3,$e4,$s1,$s2,$s3,$s4,$packet_id=0)
{
	global $datasource;

	$newid=0;
	//check for previous entry...
	$sql="
		select id 
		from ".mrr_find_log_database_name()."driver_elog_entries
		where event_id='".sql_friendly($eid)."'
			and driver_id='".sql_friendly($driver_id)."'
			and linedate_created='".date("Y-m-d H;i:s",strtotime($created))."'
	";
	$data=simple_query($sql);
	$mn=mysqli_num_rows($data);
	if($row=mysqli_fetch_array($data))
	{
		$newid=$row['id'];		//found, use this ID and do not add again for the same time, driver, and event type.
	}
	
	if($mn == 0)
	{    //not found, so add the row. 		
		$tres=mrr_find_dispatch_for_driver_by_date($driver_id,$created);
		
		$sql="
			insert into ".mrr_find_log_database_name()."driver_elog_entries
				(id,
				event_id,
				driver_id,
				linedate_added,
				linedate_created,
				linedate_effective,
				event_data1,
				event_data2,
				event_data3,
				event_data4,
				setting1,
				setting2,
				setting3,
				setting4,
				active,
				deleted,
				pardoned,
				pardoned_by_user,
				pardoned_reason,
				customer_id,
				truck_id,
				trailer_id,
				load_id,
				dispatch_id,
				stop_id,
				packet_id)
			values
				(NULL,
				'".sql_friendly($eid)."',
				'".sql_friendly($driver_id)."',
				NOW(),
				'".date("Y-m-d H;i:s",strtotime($created))."',
				'".date("Y-m-d H;i:s",strtotime($effective))."',
				'".sql_friendly($e1)."',
				'".sql_friendly($e2)."',
				'".sql_friendly($e3)."',
				'".sql_friendly($e4)."',
				'".sql_friendly($s1)."',
				'".sql_friendly($s2)."',
				'".sql_friendly($s3)."',
				'".sql_friendly($s4)."',
				1,
				0,
				0,
				0,
				'',
				'".sql_friendly($tres['customer_id'])."',
				'".sql_friendly($tres['truck_id'])."',
				'".sql_friendly($tres['trailer_id'])."',
				'".sql_friendly($tres['load_handler_id'])."',
				'".sql_friendly($tres['trucks_log_id'])."',
				'".sql_friendly($tres['stop_id'])."',
				'".sql_friendly($packet_id)."')
		";
		
		simple_query($sql);
		$newid=mysqli_insert_id($datasource);
	}
	return $newid;
}

function mrr_elog_data_mask($id,$cd,$col,$data)
{	//function decodes data from event data and setting values based on parameters.  
	//ID=event id, CD=event or setting, COL=event/setting number, and DATA is the value coming in...an integer code to be masked for the proper meaning.
	
	$val=$data;
	
	if($id==2 && $cd==1 && $col==1)	
	{	//Duty Status
		if(trim($data)=="-1")		$val="Not Found";
		if(trim($data)=="0")		$val="Unknown";
		if(trim($data)=="1")		$val="Driving";
		if(trim($data)=="2")		$val="On Duty (not driving)";
		if(trim($data)=="3")		$val="Off Duty";
		if(trim($data)=="4")		$val="Sleeper Berth";
	}
	if($id==11 && $cd==1 && $col==1)	
	{	//Country Code
		if(trim($data)=="0")		$val="Unknown";
		if(trim($data)=="1")		$val="USA - Mainland";
		if(trim($data)=="2")		$val="Canada";
		if(trim($data)=="3")		$val="USA - Alaska";
		if(trim($data)=="4")		$val="Mexico";
	}
	if($id==12 && $cd==1 && $col==2)	
	{	//DST On
		if(trim($data)=="0")		$val="No";
		if(trim($data)=="1")		$val="Yes";
	}
	if($id==16 && $cd==1 && $col==1)	
	{	//Inspection Type
		if(trim($data)=="0")		$val="Undefined";
		if(trim($data)=="1")		$val="Pre-trip inspection";
		if(trim($data)=="2")		$val="During trip inspection";
		if(trim($data)=="3")		$val="Post-trip inspection";
	}
	
	if($id==18 && $cd==1 && $col==1)	
	{	//Hours of Service HOS
		$val="Undefined";
		if(trim($data)=="0")		$val="USA Mainland 60/7 Long Haul";
		if(trim($data)=="1")		$val="USA Mainland 70/8 Long Haul";
		if(trim($data)=="5")		$val="USA Alaska 70/7";
		if(trim($data)=="6")		$val="USA Alaska 80/8";
		if(trim($data)=="7")		$val="USA Mainland 60/7 Short Haul";
		if(trim($data)=="8")		$val="USA Mainland 70/8 Short Haul";
		if(trim($data)=="9")		$val="Canada Cycle1: 70/7";
		if(trim($data)=="10")		$val="Canada Cycle2: 120/14";
		if(trim($data)=="11")		$val="USA Texas 70/7";
		if(trim($data)=="12")		$val="USA Florida 70/7";
		if(trim($data)=="13")		$val="USA Florida 80/8";
		if(trim($data)=="14")		$val="USA California";
		if(trim($data)=="19")		$val="USA Pasngr 60/7";
		if(trim($data)=="20")		$val="USA Pasngr 70/8";
		if(trim($data)=="24")		$val="Oilfield 70/8";
		if(trim($data)=="22")		$val="Canada 60n Cycle1 80/7";
		if(trim($data)=="23")		$val="Canada 60N Cycle2 120/14";
	}
	if($id==31 && $cd==1 && $col==2)	
	{	//Deferral Day1
		if(trim($data)=="0")		$val="No";
		if(trim($data)=="1")		$val="Yes";
	}
	if($id==32 && $cd==1 && $col==2)	
	{	//Deferral Day2
		if(trim($data)=="0")		$val="No";
		if(trim($data)=="1")		$val="Yes";
	}
	if($id==33 && $cd==1 && $col==1)	
	{	//Connection Code
		if(trim($data)=="0")		$val="Disconnect";
		if(trim($data)=="1")		$val="Connect";
	}
	if($id==33 && $cd==2 && $col==3)	
	{	//Inspection Type
		if(trim($data)=="0")		$val="No fix available";
		if(trim($data)=="1")		$val="Fair";
		if(trim($data)=="2")		$val="Good";
		if(trim($data)=="3")		$val="Excellent";
	}
	if($id==39 && $cd==1 && $col==1)	
	{	//New Border Crossing
		$val="Undefined";
		if(trim($data)=="1")		$val="USA Federal 60/7 Long Haul";
		if(trim($data)=="2")		$val="USA Federal 70/8 Long Haul";
		if(trim($data)=="6")		$val="USA Alaska 70/7";
		if(trim($data)=="7")		$val="USA Alaska 80/8";
		if(trim($data)=="4")		$val="USA Federal 60/7 Short Haul";
		if(trim($data)=="5")		$val="USA Federal 70/8 Short Haul";
		if(trim($data)=="12")		$val="Canada Cycle1: 70/7";
		if(trim($data)=="13")		$val="Canada Cycle2: 120/14";
		if(trim($data)=="8")		$val="USA Texas 70/7";
		if(trim($data)=="9")		$val="USA Florida 70/7";
		if(trim($data)=="10")		$val="USA Florida 80/8";
		if(trim($data)=="11")		$val="USA California";
		if(trim($data)=="14")		$val="Mexico";
		if(trim($data)=="18")		$val="USA Pasngr 60/7";
		if(trim($data)=="19")		$val="USA Pasngr 70/8";
		if(trim($data)=="21")		$val="Oilfield 70/8";
		if(trim($data)=="22")		$val="Canada 60n Cycle1 80/7";
		if(trim($data)=="23")		$val="Canada 60N Cycle2 120/14";
	}
	if($id==39 && $cd==1 && $col==2)	
	{	//Deferral Day2
		if(trim($data)=="0")		$val="Manual";
		if(trim($data)=="1")		$val="Automatic";
	}
	
	if($id==41 && $cd==2 && $col==1)	
	{	//Deferral Day2
		if(trim($data)=="0")		$val="Driver Profile";
		if(trim($data)=="1")		$val="Terminal Setting";
	}
	
	return $val;
}
	
function mrr_elog_event_types($id=0,$cd=0)
{
	$event_type="";
	
	$data_1_means="";
	$data_2_means="";
	$data_3_means="";
	$data_4_means="";
	
	$setting_1_means="";
	$setting_2_means="";
	$setting_3_means="";
	$setting_4_means="";
	
	/*
     if($id==2)		$event_type="Duty Status Change";
     if($id==3)		$event_type="Trailer Information Change";
     if($id==4)		$event_type="Co-Driver Change";
     if($id==5)		$event_type="Shipping Information Change";
     if($id==6)		$event_type="Company Information Change";
     if($id==7)		$event_type="Home Terminal Change";
     if($id==8)		$event_type="Citation";
     if($id==9)		$event_type="Remarks";
     if($id==11)		$event_type="Border Cross";
     if($id==12)		$event_type="Timezone";
     if($id==14)		$event_type="Certification";
     if($id==16)		$event_type="Vehicle Inspection";
     if($id==17)		$event_type="Odometer Reading";
     if($id==18)		$event_type="Hours of Service Change";
     if($id==19)		$event_type="Miles Override";
     if($id==20)		$event_type="Co-Driver Override";
     if($id==24)		$event_type="Forced Driver Exit";
     if($id==26)		$event_type="Driver Login";
     if($id==27)		$event_type="Driver Logout";
     if($id==31)		$event_type="Deferral Day1";
     if($id==32)		$event_type="Deferral Day2";
     if($id==33)		$event_type="eDriver Log System Disconnect/Connect";
     if($id==34)		$event_type="Start Ferry";
     if($id==35)		$event_type="On Ferry";
     if($id==36)		$event_type="End Ferry";
     if($id==39)		$event_type="New Border Crossing";
     if($id==40)		$event_type="License Plate";
     if($id==41)		$event_type="Personal Conveyance Configuration Change";
	*/
	
	$sql = "
		select *
		
		from driver_elog_events
		where id='".sql_friendly($id)."'
	";
	$data = simple_query($sql);	
	if($row=mysqli_fetch_array($data))
	{
		$event_type=trim($row['elog_event']);
		
		$data_1_means=trim($row['event_data1']);
		$data_2_means=trim($row['event_data2']);
		$data_3_means=trim($row['event_data3']);
		$data_4_means=trim($row['event_data4']);
		
		$setting_1_means=trim($row['setting1']);
		$setting_2_means=trim($row['setting2']);
		$setting_3_means=trim($row['setting3']);
		$setting_4_means=trim($row['setting4']);
	}
	
	$res['event_type']=$event_type;
	
	$res['data_1_means']=$data_1_means;
	$res['data_2_means']=$data_2_means;
	$res['data_3_means']=$data_3_means;
	$res['data_4_means']=$data_4_means;
	
	$res['setting_1_means']=$setting_1_means;
	$res['setting_2_means']=$setting_2_means;
	$res['setting_3_means']=$setting_3_means;
	$res['setting_4_means']=$setting_4_means;
	
	if($cd>0)	return $res;
	
	return $event_type;
}

function mrr_parse_elog_event_data($list)
{	//take the event(s) and return array   Ex:   Event({ 1 }}Event({ 313361.4 }}Event({ +41.890N -87.731W 2.6 miles NE from Oak Pk, IL }}
	$cntr=0;
	$val[0]="";
	$list=trim($list);
	
	$debug=$list."<br>";		
	
	$pieces = explode("Event({", $list);
	for($i=0; $i< count($pieces); $i++)
	{
		$pieces[$i]=str_replace("Event({","",$pieces[$i]);
		$pieces[$i]=str_replace("}}","",$pieces[$i]);
		$pieces[$i]=trim($pieces[$i]);
		
		$debug.="[".$i."]='".$pieces[$i]."'<br>";
		
		if($pieces[$i]!="")
		{
			$val[$cntr]="".$pieces[$i]."";
			$cntr++;	
		}
	}
	$res['rep']=$debug;		
	$res['num']=$cntr;
	$res['val']=$val;
	return $res;		
}
function mrr_parse_elog_event_settings($list)
{	//take the setting(s) and return array   Ex:    Setting{created_by}={Driver}.
	$cntr=0;
	$arr[0]="";
	$val[0]="";
	$list=trim($list);
	$list=str_replace("}.","}",$list);
			
	$debug=$list."<br>";
			
	$pieces = explode("Setting{", $list);
	for($i=0; $i< count($pieces); $i++)
	{
		$pieces[$i]=str_replace("Setting{","",$pieces[$i]);
		$pieces[$i]=str_replace("}={","=",$pieces[$i]);
		$pieces[$i]=str_replace("}","",$pieces[$i]);
		$pieces[$i]=trim($pieces[$i]);
		
		$debug.="[".$i."]='".$pieces[$i]."'<br>";
		
		if($pieces[$i]!="" && substr_count($pieces[$i],"=") > 0)
		{
			
			$pos=strpos($pieces[$i],"=");
			$len=strlen($pieces[$i]);
			$key=substr($pieces[$i],0,$pos);
			$value=substr($pieces[$i],$pos,($len-$pos));
			
			$key=str_replace("=","",$key);
			$value=str_replace("=","",$value);
			
			$arr[$cntr]=$key;
			$val[$cntr]=$value;
			$cntr++;	
		}     		
	}
	$res['rep']=$debug;		
	$res['num']=$cntr;
	$res['key']=$arr;
	$res['val']=$val;
	return $res;
}
	
function mrr_peoplenet_truck_tracking_events_add($track_arr)
{
	global $datasource;

	for($x=0; $x <= 17; $x++)
	{
		if(	$track_arr[$x] == "unavailable" )		$track_arr[$x]=0;
	}
	
	$sql = "
		insert into ".mrr_find_log_database_name()."truck_tracking_event_history
			(id,
			linedate_added,
			linedate,
			packet_id,	
			pn_dispatch_id,
			pn_stop_id,
			e_type,
			e_reason,
			e_latitude,
			e_longitude,
			px_fuel,
			px_odometer,
			px_odo_type,
			stop_data,					
			load_id,
			disptach_id,
			stop_id,
			truck_id,
			truck_name)
		values
			(NULL,
			NOW(),
			'".sql_friendly($track_arr[3]) ."',
			'".sql_friendly($track_arr[17]) ."',
			'".sql_friendly($track_arr[0]) ."',
			'".sql_friendly($track_arr[11]) ."',
			
			'".sql_friendly($track_arr[4]) ."',
			'".sql_friendly($track_arr[5]) ."',
			'".sql_friendly($track_arr[6]) ."',
			'".sql_friendly($track_arr[7]) ."',
			'".sql_friendly($track_arr[8]) ."',
			'".sql_friendly($track_arr[9]) ."',
			'".sql_friendly($track_arr[10]) ."',				
			'".sql_friendly($track_arr[12]) ."',
			'".sql_friendly($track_arr[13]) ."',
			'".sql_friendly($track_arr[14]) ."',
			'".sql_friendly($track_arr[15]) ."',
			'".sql_friendly($track_arr[16]) ."',
			'".sql_friendly($track_arr[2]) ."')
	";		
	simple_query($sql);		
	$newid=mysqli_insert_id($datasource);
	return $newid;	
}

function mrr_new_geofence_hot_load_tracking_update_by_id($geo_id)
{
	$resx="<div class='alert'>Error</div>";
	if($geo_id>0)
	{
		$resx="<div>Loading...</div>";			
		
		global $defaultsarray;		
		$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];
		$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
		
		$nowtime2=time();
		$nowtime=time();
		
		$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];
		$gmt_off = $gmt_off * 60 * 60 * -1;
		$nowtime+=$gmt_off;			
		$resx.="<div>CST Now...".$nowtime2.". CST Date...".date("Y-m-d H:i:s",$nowtime2).".</div>";
		$resx.="<div>GMT Now...".$nowtime.". GMT Date...".date("Y-m-d H:i:s",$nowtime).".</div>";
		
		if($mph<=0)	$mph=1;
		
		$munits=5280;
		$debugger="";
		
		$sql="
			select geofence_hot_load_tracking.*,
				(select linedate_pickup_eta from load_handler_stops where load_handler_stops.id=geofence_hot_load_tracking.stop_id) as alt_due_date,
				(select timezone_offset from load_handler_stops where load_handler_stops.id=geofence_hot_load_tracking.stop_id) as mrr_timezone_offset,
				(select timezone_offset_dst from load_handler_stops where load_handler_stops.id=geofence_hot_load_tracking.stop_id) as mrr_timezone_offset_dst
			from ".mrr_find_log_database_name()."geofence_hot_load_tracking
			where geofence_hot_load_tracking.id='".sql_friendly($geo_id)."'
			
		";	
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$load_id=$row['load_id'];
			$dispatch_id=$row['dispatch_id'];
			$stop_id=$row['stop_id'];
			$truck_id=$row['truck_id'];
								
			$alt_due_date=$row['alt_due_date'];
			$timezone_offset=$row['mrr_timezone_offset'];
			$timezone_offset_dst=$row['mrr_timezone_offset_dst'];
			$their_time=$nowtime + $timezone_offset + $timezone_offset_dst;
			
			$finish_time=(strtotime($alt_due_date) - $their_time) / (60 * 60);	//hours left until delivery is late...
			
			$their_time=date("Y-m-d H:i:s",$their_time);	
			
			$cust=mrr_get_all_customer_settings($row['customer_id']);	
			
			$hl_active=$cust['hot_load_switch'];				//turn messages on or off going via email
			$hl_timer=$cust['hot_load_timer'];					//interval between messages
			$hl_r_arriving=$cust['hot_load_radius_arriving'];		//
			$hl_r_arrived=$cust['hot_load_radius_arrived'];		//
			$hl_r_departed=$cust['hot_load_radius_departed'];		//
			$hl_geo_active=$cust['geofencing_radius_active'];		//turn on actual geofencing notices
			$hl_geo_on_all=$cust['geofencing_hot_msg_all_loads'];	//all loads set to on...	
			
			
			$resx.=" <div>GeoF ID=".$row['id'].".</div>";
			$resx.=" <div>Load ID=".$load_id.".</div>";
			$resx.=" <div>Disp ID=".$dispatch_id.".</div>";
			$resx.=" <div>Stop ID=".$stop_id.".</div>";
						
			     			
			if($row['active'] > 0 && $row['deleted']==0 && $row['stop_completed']==0)
			{
				$resx.=" <div>Created Date=".date("m/d/Y H:i",strtotime($row['linedate_added'])).".</div>";
				$resx.=" <div>Appointment Date=".date("m/d/Y H:i",strtotime($alt_due_date)).".</div>";     				
				
				$gps_dist=$row['dest_distance'];
				$lat=$row['dest_latitude'];
				$long=$row['dest_longitude'];     				
				
				$get_local=mrr_find_only_location_of_this_truck($truck_id);     							
				$last_gps_lat=$get_local['latitude'];
				$last_gps_long=$get_local['longitude'];
				$last_gps_local=$get_local['location'];
				
				if($last_gps_lat==0 && $last_gps_long==0)
				{
					$last_gps_lat=$row['last_gps_latitude'];
					$last_gps_long=$row['last_gps_longitude'];
					$last_gps_local=$row['dest_message'];
				}
				$urlx="http://ws.geonames.org/timezone?lat=".$lat."&lng=".$long."&style=full";
				
				$gps_dist=mrr_distance_between_gps_points($lat,$long,$last_gps_lat,$last_gps_long);		//has MILES...
				$gps_dist=abs($gps_dist);
				$overall_hrs= $gps_dist / $mph;
				$gps_dist_feet=$gps_dist * $munits;					//convert distance in miles to feet....
				
				$mrr_updater="active='1'";
				
				$resx.=" <div>&nbsp;</div>"; 
				$resx.=" <div>Due Date=".date("m/d/Y H:i",strtotime($row['linedate'])).".</div>";
				$resx.=" <div>Dest GPS=".$lat.",".$long." (Lat,Long).</div>";
				$resx.=" <div>Last GPS=".$last_gps_lat.",".$last_gps_long." (Lat,Long).</div>";
				$resx.=" <div>Last GPS Date=".date("m/d/Y H:i",strtotime($row['linedate_last_gps'])).".</div>";
				     				
				$resx.=" <div>&nbsp;</div>"; 
				$resx.=" <div>Arriving=[".$row['dest_arriving']."] --- ".$row['dest_remaining_arriving']." Miles Left --- ".$row['dest_time_arriving']." Hrs --- ".$row['msg_last_arriving'].".</div>";
				$resx.=" <div>Arrived=[".$row['dest_arrived']."] --- ".$row['dest_remaining_arrived']." Miles Left --- ".$row['dest_time_arrived']." Hrs --- ".$row['msg_last_arrived'].".</div>";
				$resx.=" <div>Departed=[".$row['dest_departed']."] --- ".$row['dest_remaining_departed']." Miles Left --- ".$row['dest_time_departed']." Hrs --- ".$row['msg_last_departed'].".</div>";
				    				
				$resx.=" <div>&nbsp;</div>"; 
				$resx.=" <div>Distance=".$gps_dist." Miles.</div>";
				$resx.=" <div>Distance2=".$gps_dist." Miles =".number_format($gps_dist_feet,2)." in Feet.</div>";
				$resx.=" <div>Distance Time=".number_format($overall_hrs,2)." Hrs.</div>";
				$resx.=" <div>Report=".$last_gps_local.".</div>";
				$resx.=" <div>Local Time=".date("Y-m-d H:i:s",strtotime($their_time)).".</div>";
				
				$send_email_out=0;
				
				$resx.=" <div>&nbsp;</div>"; 
				if($row['dest_arriving']==0)
				{
					$eval_dist=$hl_r_arriving + $tolerance;
					if($gps_dist_feet <= $eval_dist)
					{
						$row['dest_arriving']=1;
						$row['dest_time_arriving']=0;	
						$row['dest_remaining_arriving']=0;
						$row['linedate_last_arriving']=$their_time;
						
						$mrr_updater.="
							,dest_arriving='1'
							,dest_time_arriving='0'
							,dest_remaining_arriving='0'
							,linedate_last_arriving='".date("Y-m-d H:i:s",strtotime($their_time))."'   							
						";     						
					}
					else
					{
						$row['dest_time_arriving']=number_format($overall_hrs,2);	
						$row['dest_remaining_arriving']=number_format($gps_dist,0);
						
						$mrr_updater.="
							,dest_time_arriving='".str_replace(",","",number_format($overall_hrs,0))."'
							,dest_remaining_arriving='".str_replace(",","",number_format($gps_dist,0))."' 							
						";	
					}	
					$resx.=" <div>New Arriving=[".$row['dest_arriving']."] --- ".$row['dest_remaining_arriving']." Miles Left --- ".$row['dest_time_arriving']." Hrs --- ".date("m/d/Y H:i",strtotime($row['linedate_last_arriving']))." --- ".$row['msg_last_arriving'].".</div>";
									
				}
				     				
				$resx.=" <div>&nbsp;</div>"; 
				if($row['dest_arrived']==0)
				{
					$eval_dist=$hl_r_arrived + $tolerance;
					if($gps_dist_feet <= $eval_dist)
					{
						$row['dest_arrived']=1;
						$row['dest_time_arrived']=0;	
						$row['dest_remaining_arrived']=0;
						$row['linedate_last_arrived']=$their_time;
						
						$mrr_updater.="
							,dest_arrived='1'
							,dest_time_arrived='0'
							,dest_remaining_arrived='0'
							,linedate_last_arrived='".date("Y-m-d H:i:s",strtotime($their_time))."'   							
						";	
						$send_email_out=1;
					}	
					else
					{
						$row['dest_time_arrived']=number_format($overall_hrs,2);	
						$row['dest_remaining_arrived']=number_format($gps_dist,0);
						
						$mrr_updater.="
							,dest_time_arrived='".str_replace(",","",number_format($overall_hrs,0))."'
							,dest_remaining_arrived='".str_replace(",","",number_format($gps_dist,0))."' 							
						";		
					}
					$resx.=" <div>New Arrived=[".$row['dest_arrived']."] --- ".$row['dest_remaining_arrived']." Miles Left --- ".$row['dest_time_arrived']." Hrs --- ".date("m/d/Y H:i",strtotime($row['linedate_last_arrived']))." --- ".$row['msg_last_arrived'].".</div>";
				
				}
				
				$resx.=" <div>&nbsp;</div>"; 
				if($row['dest_departed']==0)
				{
					$eval_dist=$hl_r_departed + $tolerance;
					if($gps_dist_feet >= $eval_dist && $row['dest_arrived']==1)
					{
						$row['dest_departed']=1;
						$row['dest_time_departed']=0;	
						$row['dest_remaining_departed']=0;
						$row['linedate_last_departed']=$their_time;
						
						$mrr_updater.="
							,dest_departed='1'
							,dest_time_departed='0'
							,dest_remaining_departed='0'
							,linedate_last_departed='".date("Y-m-d H:i:s",strtotime($their_time))."'   							
						";	
						$send_email_out=1;
					}	
					else
					{
						$row['dest_time_departed']=number_format($overall_hrs,2);	
						$row['dest_remaining_departed']=number_format($gps_dist,0);
						
						$mrr_updater.="
							,dest_time_departed='".str_replace(",","",number_format($overall_hrs,0))."'
							,dest_remaining_departed='".str_replace(",","",number_format($gps_dist,0))."'   							
						";		
					}
					$resx.=" <div>New Departed=[".$row['dest_departed']."] --- ".$row['dest_remaining_departed']." Miles Left --- ".$row['dest_time_departed']." Hrs --- ".date("m/d/Y H:i",strtotime($row['linedate_last_departed']))." --- ".$row['msg_last_departed'].".</div>";
				
				}
				
				if($row['dest_arriving']==1 && $row['dest_arrived']==1 && $row['dest_departed']==1)
				{
					$mrr_updater.=",stop_completed='1'";  	
				}
				
			} 
			$resx.=" <div>&nbsp;</div>";
			$resx.=" <div>Send Mail=".$send_email_out.".</div>"; 
			
			
			$sqlu="
				update ".mrr_find_log_database_name()."geofence_hot_load_tracking set 
					".$mrr_updater." 
				where id='".sql_friendly($geo_id)."'
			";
			simple_query($sqlu);
			$resx.=" <div>Updater=".$sqlu.".</div>";    			 	
			
			$resx.=" <div>&nbsp;</div>"; 	
			$resx.=" <div>&nbsp;</div>"; 		
			    			
		}					
	}
	return $resx;			
}


function mrr_run_full_geofencing_update_for_truck_V2($truck_id=0)
{
	$mrr_adder="";
	if($truck_id>0)		$mrr_adder.=" and (geofence_hot_load_tracking.truck_id='".sql_friendly($truck_id)."' or trucks_log.truck_id='".sql_friendly($truck_id)."')";
	
	$full_debugger="";
	
	$munits=5280;
	$tunits=60;
	
	$cntr=0;
	$truck_cntr=0;
	$trucks_found[0]=0;
	
	global $defaultsarray;		
	$mph = (int) $defaultsarray['peoplenet_geofencing_mph'];
	$tolerance = (int) $defaultsarray['peoplenet_geofencing_tolerance'];
	
	$fromname=$defaultsarray['company_name'];	
	$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];			
	
	$template=(int) $defaultsarray['peoplenet_hot_msg_template_num'];		
	
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
		
	$mrr_template="";
			
	$sql="
		select geofence_hot_load_tracking.*,
			load_handler_stops.linedate_completed,
			trucks_log.truck_id as truckid
		from ".mrr_find_log_database_name()."geofence_hot_load_tracking
			left join load_handler_stops on load_handler_stops.id=geofence_hot_load_tracking.stop_id
			left join trucks_log on trucks_log.id=geofence_hot_load_tracking.dispatch_id
			left join load_handler on load_handler.id=geofence_hot_load_tracking.load_id
		where geofence_hot_load_tracking.deleted=0
			and geofence_hot_load_tracking.active>0
			and geofence_hot_load_tracking.stop_completed=0
			and trucks_log.deleted=0
			and load_handler.deleted=0
			and load_handler_stops.deleted=0
			".$mrr_adder."
			and (load_handler_stops.linedate_completed is null or load_handler_stops.linedate_completed < load_handler_stops.linedate_added)
		order by geofence_hot_load_tracking.linedate asc	
	";	
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$geo_id=$row['id'];
		
		$load_id=$row['load_id'];
		$dispatch_id=$row['dispatch_id'];
		$stop_id=$row['stop_id'];
		
		$lat=$row['dest_latitude'];
		$long=$row['dest_longitude'];  
		$true_dist=$row['dest_distance'];
		
		$truckid=$row['truck_id'];
		if($row['truck_id'] != $row['truckid'])    		$truckid=$row['truckid'];
		     				
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
			where id='".sql_friendly($dispatch_id)."'				
		";
		$data_dispatch=simple_query($sql);
		$row_dispatch=mysqli_fetch_array($data_dispatch);
		
		//stop ...
		$sql="
			select * 
			from load_handler_stops
			where id='".sql_friendly($stop_id)."'				
		";
		$data_stop=simple_query($sql);
		$row_stop=mysqli_fetch_array($data_stop);
		
		$geo_sent_arriving=$row_stop['geofencing_arriving_sent'];
		$geo_sent_arrived=$row_stop['geofencing_arrived_sent'];
		$geo_sent_departed=$row_stop['geofencing_departed_sent'];   
		
		$appointment=$row_stop['linedate_pickup_eta'];
		$completed_already=$row_stop['linedate_completed'];   			
		
		$tfound=0;
		
		for($x=0;$x< $truck_cntr; $x++)
		{
			if($trucks_found[$x]==$truckid)	$tfound=1;
		}
		
		$adder_x="";
		if(isset($completed_already) && $completed_already>='2016-01-01 00:00:00')
		{	//this stop is done, go ahead and set these as completed...but do not use this load stop as the current truck's first stop.
			$tfound=1;
			
			$adder_x.="
     			,stop_completed='1'
     			,active='0'
     		";	          			
		}
		     		
		if($tfound==0)
		{	//only update the first truck
			$trucks_found[$truck_cntr]=$truckid;
			$truck_cntr++;
			
			if(!isset($row['last_gps_lat']))		$row['last_gps_lat']=0;
			if(!isset($row['last_gps_long']))		$row['last_gps_long']=0;
			if(!isset($truck_name))				$truck_name="";
			
     		$last_gps_lat=$row['last_gps_lat'];
     		$last_gps_long=$row['last_gps_long'];
     		$last_gps_local=$row['dest_message'];
     		
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
     		     		
     		$get_local=mrr_find_only_location_of_this_truck($truckid);     							
     		$last_gps_lat=$get_local['latitude'];
     		$last_gps_long=$get_local['longitude'];
     		$last_gps_local=$get_local['location'];
     		
     		$gps_dist=mrr_distance_between_gps_points($lat,$long,$last_gps_lat,$last_gps_long);		//has MILES...
     		$gps_dist=abs($gps_dist);
     		$overall_hrs= round(($gps_dist / $mph), 4);
     		$overall_min= round(($gps_dist / $mph * $tunits), 4);
     		$gps_dist_feet=$gps_dist * $munits;					//convert distance in miles to feet....
     		
     		$sent_arriving=$row['msg_last_arriving'];
     		$sent_arrived=$row['msg_last_arrived'];
     		$sent_departed=$row['msg_last_departed'];
     		          		
     		$due_offset="+".(int)$overall_min." minutes";
     		$due_offset=str_replace("+-","-",$due_offset);
     		
     		$localtime=date("m/d/Y H:i:s",strtotime("".($row_stop['timezone_offset']+$row_stop['timezone_offset_dst'])." seconds",strtotime($gmttime)));
			$localsuffix=mrr_decode_proper_timezone_label($row_stop['timezone_offset'],$row_stop['timezone_offset_dst']);  	
			$due_timer=date("m/d/Y H:i",strtotime($due_offset,strtotime($localtime)));
     		          		
     		$msg_tracker="";
			
			$send_email=0;
     		$mode_txt="Arriving";     	
     		$sector=1;	
     		$radius_feet=$hl_r_arriving + $tolerance;
     		
     		if($row['dest_arriving'] > 0)		$sector=2;	//in arriving already, go to the arrived section...
     		if($row['dest_arrived'] > 0)		$sector=3;	//in arrived already, go to the departed section...
     		if($row['dest_departed'] > 0)		$sector=4;	//done...update stop_completed only..
     		
			$subject="CTS Geofence Update: Load ".$load_id." Status Update";
          	$tolist="";  
          	
          	$sc_label=" is on its way to pickup shipment from Shipper.";  
			
          	if($row_stop['stop_type_id']==2)
         		{
         			$sc_label=" is on its way to deliver shipment to Consignee.";  
         		} 
         		
     		$msg_body_header="Conard Transportation Notice";
     		$msg_body="<br>Truck ".$truck_name."".$sc_label."";
     		$msg_body_footer="<br>This is an automated message.<br>";
     		
     		//process arriving section  
     		if($sector==1)
     		{
	    			$tolist=trim($hl_earriving);
	    			
	    			if($gps_dist_feet <= $radius_feet)
     			{
     				$msg_tracker.="<br>Truck is within <b>Arriving</b> Radius.  Send <b>Arriving</b> Message if on and timer is set.";	
     				$sent_arriving=1;
     				
     				if(trim($hl_earriving)!="")		$send_email=1;
     				$sector=2;
     				
     				$adder_x.="
     					,dest_arriving='1'
     					,linedate_last_arriving=NOW()
     					,dest_remaining_arriving='0'
     					,dest_time_arriving='0'
     				";
     					
     				$msg_body.="<br><br>".$s_c_label." is on approach ".abs(round($gps_dist,2))." Miles (about ".number_format($overall_hrs,2)." hours or ".number_format($overall_min,0)." minutes) away from the facility.";
     				$msg_body.="<br><br>Arrival time is approximately ".$due_timer." ".$localsuffix.".";
     			}
     			else
     			{
     				$adder_x.="
     					,dest_remaining_arriving='".sql_friendly($gps_dist)."'
     					,dest_time_arriving='".sql_friendly($overall_hrs)."'
     				";
     				
     				$msg_body.="<br><br>".$s_c_label." is approximately ".abs(round($gps_dist,2))." Miles (at least ".number_format($overall_hrs,2)." hours or ".number_format($overall_min,0)." minutes) away from arriving.";
     				$msg_body.="<br><br>Arrival time is approximately ".$due_timer." ".$localsuffix.".";
     			}  
     			
     			$msg_body_footer=trim($hl_marriving);   			
     		}     
     		
     		//process arrived section		
     		if($sector==2)
     		{
     			$tolist=trim($hl_earrived);
     			
     			$radius_feet=$hl_r_arrived + $tolerance;
     			$mode_txt="Arrived";
     			if($gps_dist_feet <= $radius_feet)
     			{
     				$msg_tracker.="<br>Truck is within <b>Arrived</b> Radius.  Send <b>Arrived</b> Message if on.";
     				$sent_arrived=1;
     				
     				if(trim($hl_earrived)!="" || trim($monitor_email)!="")		$send_email=1;
     				$sector=3;
     				
     				$adder_x.=" 
     					,dest_arrived='1'
     					,linedate_last_arrived=NOW()
     					,dest_remaining_arrived='0'
     					,dest_time_arrived='0'
     				";
     			}
     			else
     			{
     				$adder_x.="
     					,dest_remaining_arrived='".sql_friendly($gps_dist)."'
     					,dest_time_arrived='".sql_friendly($overall_hrs)."'
     				";          				
     			} 
     			
     			$msg_body_footer=trim($hl_marriving);
     		} 
     		
     		//process departed section   
     		if($sector==3)
     		{
     			$tolist=trim($hl_edeparted);
     			
     			$radius_feet=$hl_r_departed - $tolerance;
     			$mode_txt="Departed";
     			if($gps_dist_feet > $radius_feet)
     			{
     				$msg_tracker.="<br>Truck is heading out of <b>Departed</b> Radius.  Send <b>Departed</b> Message if on.";
     				$sent_departed=1;
     				
     				if(trim($hl_edeparted)!="" || trim($monitor_email)!="")		$send_email=1;
     				
     				$adder_x.=" 
     					,dest_departed='1'
     					,linedate_last_departed=NOW()
     					,dest_remaining_departed='0'
     					,dest_time_departed='0'
     					,stop_completed='1'
     					,active='0'
     				";
     			} 
     			else
     			{
     				$adder_x.="
     					,dest_remaining_departed='".sql_friendly($gps_dist)."'
     					,dest_time_departed='".sql_friendly($overall_hrs)."'
     				";
     			}  
     			
     			$msg_body_footer=trim($hl_marriving);   	
     		}
     		
     		if(trim($row_load['alt_tracking_email'])!="")		$tolist=trim($row_load['alt_tracking_email']);
     		
     		//all done so make sure it is marked...no message should be sent
     		if($sector >= 4)
     		{
     			$send_email=0;
     		}
     		
          	
          	if($hl_active==0 || $hl_geo_active==0)	$send_email=0;
          	if($hl_timer==0 && $sector != 1)		$send_email=0;	//arrving control timer has been turned off.  Timer only controls messages on the way.
          	               	
          	$send_email=0;
          	
          	if($send_email > 0 && $sector < 4)
          	{     
     			$use_msg_body="".$msg_body_header."".$msg_body."".$msg_body_footer;
          		if($template>0)
          		{
          			$mrr_template=mrr_hot_load_msg_template_V2($template,$row_load,$row_dispatch,$row_stop,$msg_body);
          			$use_msg_body=$mrr_template;	
          		}     			
     		   	
     		   	$note_id=0;
     		   	$tolister="";
          		if(($geo_sent_arriving==0 && $sector==1) || ($geo_sent_arrived==0 && $sector==2) || ($geo_sent_departed==0 && $sector==3))
          		{
          			$nres=mrr_geofencing_peoplnet_message($tolist,$subject,$use_msg_body);
          			$note_id=$nres['sendit'];               	
          			$tolister=$nres['sendto']; 
          		}
     		   	          			 			
     			$adder_x.="
     				,linedate_last_msg=NOW()
     			";
     		}
     		
          	$sql_x="
     			update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
     				linedate_last_gps=NOW()
     				,linedate='".date("Y-m-d H:i:s",strtotime($appointment))."'
     				,last_gps_latitude='".sql_friendly($last_gps_lat)."'
     				,last_gps_longitude='".sql_friendly($last_gps_long)."'
     				,dest_message='".sql_friendly($last_gps_local)."'
     				".$adder_x."
     			where id='".sql_friendly($geo_id)."'
     		";
     		simple_query($sql_x);
     		
			$cntr++;
			
			$full_debugger.="<br><hr><br>";
			$full_debugger.="".$cntr." Load ".$load_id." Dispatch ".$dispatch_id." Stop ".$stop_id." <b>Truck ".$truckid."</b> Dest (".$lat.", ".$long.") Current (".$last_gps_lat.", ".$last_gps_long.") ".$last_gps_local."<br>";
			$full_debugger.="Distance: ".$gps_dist." Miles = <b>".$gps_dist_feet." Feet</b>.  Time: ".$overall_hrs." Hours or ".$overall_min." Minutes. Send Mode=".$mode_txt." when radius is <b>".$radius_feet." Feet.  Sending='".$send_email."'.</b>.";
			$full_debugger.="".$msg_tracker."";
			$full_debugger.="<br>Local Time is ".$localtime." ".$localsuffix.".  Delivery due there in ".$overall_min." minutes, or ".$due_timer." ".$localsuffix."";
			
		}
		else
		{
			$sql_x="
     			update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
     				linedate_last_gps=NOW()
     				,linedate='".date("Y-m-d H:i:s",strtotime($appointment))."'
     				,dest_remaining_arriving='".sql_friendly($true_dist)."'
     				,dest_remaining_arrived='".sql_friendly($true_dist)."'
     				,dest_remaining_departed='".sql_friendly($true_dist)."'
     				".$adder_x."
     			where id='".sql_friendly($geo_id)."'
     		";
     		simple_query($sql_x);	
		}
	}	
	return $full_debugger;
}

//new packet message system based on event packets.
function mrr_trigger_email_by_event_type($cd=0,$event_type="")
{
	$table="";
	$sql_list="";
	
	global $defaultsarray;		
	
	$fromname=$defaultsarray['company_name'];	
	$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];			
	$template=(int) $defaultsarray['peoplenet_hot_msg_template_num'];		
	
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
		
	$mrr_template="";
	
	$stops_sent="";
	
	$table.="
		<div style='margin:10px;'><b>".strtoupper($event_type)." Notices</b></div>
		<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1000px;margin:10px'>
		<thead>
          <tr>
          	<th nowrap><b>Checkpoint</b></th>
          	<th nowrap><b>Load ID</b></th>
          	<th nowrap><b>Dispatch</b></th>
          	<th nowrap><b>Stop ID</b></th>
          	<th nowrap><b>Lat</b></th>
          	<th nowrap><b>Long</b></th>
          	<th><b>Customer</b></th>
          	<th><b>Driver</b></th>
          	<th><b>Truck</b></th>
          	<th><b>Trailer</b></th>		
          	<th><b>Note</b></th>
          	<th><b>Due</b></th>
          </tr>
          </thead>
          <tbody>
	";
	
	$adder="";
	if(trim($event_type)!="")		$adder.=" and (LOCATE('".sql_friendly($event_type)."-occurred',e_type)>0 or LOCATE('".sql_friendly($event_type)."-late',e_type)>0)";		//
	else							$adder.=" and (e_type='arrived-occurred' or e_type='departed-occurred' or e_type='arrived-late' or e_type='departed-late')";
	$sql="
		select * 
		from ".mrr_find_log_database_name()."truck_tracking_event_history
		where email_sent = 0			  
			".$adder."
	";	
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$geo_id=$row['id'];
		$pn_stop_id=$row['pn_stop_id'];
		$pn_disp_id=$row['pn_dispatch_id'];
		$dater=$row['linedate'];
		$etyper=$row['e_type'];
		$reason=$row['e_reason'];
		$lat=$row['e_latitude'];
		$long=$row['e_longitude'];
		$fuel_gals=$row['px_fuel'];
		$odometer=$row['px_odometer'];
		$odo_type=$row['px_odo_type'];
		$stop_info=$row['stop_data'];
		$load_id=$row['load_id'];
		$disp_id=$row['disptach_id'];
		$stop_id=$row['stop_id'];
		$truck_id=$row['truck_id'];
		$truck_name=$row['truck_name'];
		$email_sent=$row['email_sent'];
		$sent_date=$row['email_sent_date'];
		$send_email=0;
		
		if($disp_id==$load_id)		$disp_id=0;
		
		
		//use newest version of stop tracking from PN to Conard systems...
		if($stop_id==0)
		{
			$sqlu="
				select id
				from load_handler_stops
				where pn_dispatch_id='".sql_friendly($pn_disp_id)."'
					and pn_stop_id='".sql_friendly($pn_stop_id)."'
				order by id desc
				";
			$datau=simple_query($sqlu);
			if($rowu=mysqli_fetch_array($rowu))
			{
				$stop_id=$rowu['id'];	
				
				$sqlu="
					 update ".mrr_find_log_database_name()."truck_tracking_event_history set
					 	stop_id='".sql_friendly($stop_id)."'
					 where id='".sql_friendly($geo_id)."'
				";
				simple_query($sqlu);	
			}	
		}
		
		//update stop info if possible...
		if($stop_id==0)
		{				
			$stopper=mrr_find_closest_dispatch_stop_by_gps($load_id,$disp_id,$lat,$long,$pn_stop_id);
			$stop_id=$stopper['stop_id'];
			
			if($stop_id>0)
			{
				$sqlu="
					 update ".mrr_find_log_database_name()."truck_tracking_event_history set
					 	stop_id='".sql_friendly($stop_id)."'
					 where id='".sql_friendly($geo_id)."'
				";
				simple_query($sqlu);
			}	
		}
					
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
			select * 
			from load_handler_stops
			where id='".sql_friendly($stop_id)."'				
		";
		$data_stop=simple_query($sql);
		$row_stop=mysqli_fetch_array($data_stop);
		
		$geo_sent_arriving=$row_stop['geofencing_arriving_sent'];
		$geo_sent_arrived=$row_stop['geofencing_arrived_sent'];
		$geo_sent_departed=$row_stop['geofencing_departed_sent']; 
		    			
		//customer ...
		$sql="
			select *
			from customers
			where id='".sql_friendly($row_load['customer_id'])."'				
		";
		$data_cust=simple_query($sql);
		$row_cust=mysqli_fetch_array($data_cust);
		
		$cust=mrr_get_all_customer_settings($row_load['customer_id']);	
				
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
		
		$driver_1="";
		$sql="
			select name_driver_first,
				name_driver_last
			from drivers
			where id='".sql_friendly($row_dispatch['driver_id'])."'				
		";
		$data_driver=simple_query($sql);    			
		if($row_driver=mysqli_fetch_array($data_driver))
		{
			$driver_1=$row_driver['name_driver_first']." ".$row_driver['name_driver_last']."";	
		}
		
		$driver_2="";
		$sql="
			select name_driver_first,
				name_driver_last
			from drivers
			where id='".sql_friendly($row_dispatch['driver2_id'])."'				
		";
		$data_driver=simple_query($sql);    			
		if($row_driver=mysqli_fetch_array($data_driver))
		{
			$driver_2=$row_driver['name_driver_first']." ".$row_driver['name_driver_last']."";	
		}
		
		$trailer="";
		$sql="
			select trailer_name
			from trailers
			where id='".sql_friendly($row_stop['start_trailer_id'])."'				
		";
		$data_trailer=simple_query($sql);    			
		if($row_trailer=mysqli_fetch_array($data_trailer))
		{
			$trailer=$row_trailer['trailer_name'];	
		}
					
		
		//compose message....
		$send_email=1;
		
		$subject="CTS Geofence Update: Load ".$load_id." | ".$driver_1." | ".$etyper." | ".$reason."";
     	$tolist="";  
     	
     	$sc_label=" is on its way.";         	         		
     	$sc_label2=" Prepare to load shipment.";
     	
     	$tolist=trim($hl_earriving);         		
    		
    		if(trim($tolist)=="" && trim($monitor_email)=="")		$send_email=0;
		
     	if($row_stop['stop_type_id']==2)
    		{
    			$sc_label2=" Prepare to receive shipment.";	
    		}
     	
     	if(strtoupper($event_type)=="ARRIVED")
     	{
     		$sc_label=" has arrived";         	         		
     		$sc_label2=" for pickup from Shipper.";
     		
     		$tolist=trim($hl_earrived);
     		
     		if($row_stop['stop_type_id']==2)
     		{
     			$sc_label2=" for delivery to Consignee.";	
     		}
     	}
     	elseif(strtoupper($event_type)=="DEPARTED")
     	{
     		$sc_label=" has departed";         	         		
     		$sc_label2=" from pickup from Shipper..";
     		
     		$tolist=trim($hl_edeparted);
     		
     		if($row_stop['stop_type_id']==2)
     		{
     			$sc_label2=" from delivery to Consignee.";		
     		}
     	}
     	          	
     	$msg_body_header="Conard Transportation Notice";
     	$msg_body="<br>Truck ".$truck_name."".$sc_label."".$sc_label2."";
     	$msg_body_footer="<br>This is an automated message.<br>";
     	
     	if(strtolower(trim($etyper))=="trip-start")			$send_email=2;		//Indicates a trip start event
     	if(strtolower(trim($etyper))=="trip-end")			$send_email=2;		//Indicates a trip end event
     	if(strtolower(trim($etyper))=="approaching-late")		$send_email=2;		//Indicates a trip stop approaching action is late
     	if(strtolower(trim($etyper))=="arrived-late")		$send_email=2;		//Indicates a trip stop arrived action is late
     	if(strtolower(trim($etyper))=="departed-late")		$send_email=2;		//Indicates a trip stop departed action is late
     	
     	if($disp_id==0)								$send_email=2;		//skip, load and dispatch id are the same...one is wrong and is liekly a preplanned load.
     	
     	
     	if(substr_count($stops_sent,  ",".$stop_id.",") > 0 || substr_count($etyper, "-late") > 0 || substr_count($etyper, "-LATE") > 0 || substr_count($etyper, "-Late") > 0)	
     	{
     		$send_email=2;		//skip, notice has already been sent for this stop, or it is a notice and not the true arrival/departure
     	}
     	elseif($send_email==1)
     	{
     		$stops_sent.= ",".$stop_id.",";					//add to this list...if it is being sent.
     	}
     	
     	if(trim($row_load['alt_tracking_email'])!="")		$tolist=trim($row_load['alt_tracking_email']);
     	
     	mrr_truck_tracking_msg_record($truck_id,$load_id,$disp_id,$stop_id,$send_email,$msg_body);
     	
     	if($geo_sent_arriving==0 && $send_email==1)
     	{
     		$use_msg_body="".$msg_body_header."".$msg_body."".$msg_body_footer;
          	if($template>0)
          	{
          		$mrr_template=mrr_hot_load_msg_template_V2($template,$row_load,$row_dispatch,$row_stop,$msg_body);
          		$use_msg_body=$mrr_template;	
          	}     			
     		
     		$nres=mrr_geofencing_peoplnet_message($tolist,$subject,$use_msg_body);
          	$note_id=$nres['sendit'];               	 
          	
          	$sql_x="
     			update ".mrr_find_log_database_name()."truck_tracking_event_history set
     				email_sent_date=NOW(),
     				email_sent='1'
     			where id='".sql_friendly($geo_id)."'
     		";
     		simple_query($sql_x);
     	}
     	if($geo_sent_arrived==0 && $send_email==2)
     	{
     		//skipped
               $sql_x="
          		update ".mrr_find_log_database_name()."truck_tracking_event_history set
          			email_sent='2'
          		where id='".sql_friendly($geo_id)."'
          	";
          	simple_query($sql_x);	
     	}
     	        	
     	
     	if($hl_active==0)
     	{	//customer is not active, so turn them off...
     		$sql_x="
          		update ".mrr_find_log_database_name()."truck_tracking_event_history set
          			email_sent='2'
          		where id='".sql_friendly($geo_id)."'
          	";
          	simple_query($sql_x);	
     	}
		
		$table.="
			<tr>
				<td valign='top'>".date("m/d/Y H:i",strtotime($dater))."</td>
               	<td valign='top'>".$load_id."</td>
               	<td valign='top'>".$disp_id."</td>
               	<td valign='top'>".$stop_id."</td>
               	<td valign='top'>".$lat."</td>
               	<td valign='top'>".$long."</td>
               	<td valign='top'>".$row_cust['name_company']."</td>
               	<td valign='top'>".$driver_1."".$driver_2."</td>
               	<td valign='top'>".$truck_name."</td>
               	<td valign='top'>".$trailer."</td>		
               	<td valign='top'>".$etyper."</td>
               	<td valign='top'>".date("m/d/Y H:i",strtotime($row_stop['linedate_pickup_eta']))."</td>
               </tr>
		";			
	}
	
	$table.="</tbody>
	</table>
	".$sql_list."
	";
	return $table;
}
function mrr_truck_tracking_msg_record($truck_id,$load_id,$disp_id,$stop_id,$sent_flag,$msg)
{
	$sql="
		insert into ".mrr_find_log_database_name()."truck_tracking_msg_record
			(id,
			linedate_added,
            	truck_id,                	
            	deleted,
            	active,
            	load_id,
            	dispatch_id,
            	stop_id,
            	sent_msg,    
            	msg)
		values
			(NULL,
			NOW(),
			'".sql_friendly($truck_id)."',
			0,
			1,
			'".sql_friendly($load_id)."',
			'".sql_friendly($disp_id)."',
			'".sql_friendly($stop_id)."',
			'".sql_friendly($sent_flag)."',
			'".sql_friendly($msg)."')
	";
	simple_query($sql);			
}
	
function mrr_validate_peoplenet_truck($truck_id=0)
{
	$valid=0;
	$sql = "
			select peoplenet_tracking
			from trucks
			where trucks.id = '".sql_friendly($truck_id) ."'
		";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$valid=$row['peoplenet_tracking'];
	}	
	return $valid;
}


function mrr_gps_speed_limit_finder($lat=0,$long=0)
{
	global $datasource;

	$precision=2;		
	
	$speed_limits=mrr_gps_speed_limit_values($lat,$long,$precision);
	
	$res['output']="<b>URL:</b> <a href='".$speed_limits['url']."' target='_blank'>".$speed_limits['url']."</a><br>";
	$res['output'].="<br><b>XML:</b><br>".$speed_limits['output']."";
	$res['output'].="<br><b>Max Speed:</b> ".$speed_limits['max']." MPH.";
	$res['output'].="<br><b>Min Speed:</b> ".$speed_limits['min']." MPH.";
	$res['output'].="<br><b>Avg Speed:</b> ".$speed_limits['avg']." MPH.";
	$res['output'].="<br><b>Speed Signs:</b> ".$speed_limits['num'].".";
	$res['output'].="<br><b>Sign Display:</b> ".$speed_limits['signs'].".<br>";
	
	$id=$speed_limits['id'];
	
	if($speed_limits['id']==0 && $speed_limits['max'] > 0)
	{
		$sql="
			insert into ".mrr_find_log_database_name()."safety_report_signs 
				(id,
				linedate_added,
				deleted,
				ne_latitude,
				ne_longitude,
				sw_latitude,
				sw_longitude,
				sign_info,
				sign_list,
				max_speed,
				min_speed,
				avg_speed,
				sign_count,
				url)
			values	
				(NULL,
				NOW(),
				'0',
				'".sql_friendly($speed_limits['ne_lat'])."',
				'".sql_friendly($speed_limits['ne_long'])."',
				'".sql_friendly($speed_limits['sw_lat'])."',
				'".sql_friendly($speed_limits['sw_long'])."',
				'".sql_friendly($speed_limits['output'])."',
				'".sql_friendly($speed_limits['signs'])."',
				'".sql_friendly($speed_limits['max'])."',
				'".sql_friendly($speed_limits['min'])."',
				'".sql_friendly($speed_limits['avg'])."',
				'".sql_friendly($speed_limits['num'])."',
				'".sql_friendly($speed_limits['url'])."')
		";
		simple_query($sql);
		$id=mysqli_insert_id($datasource);
	}
	
	$res['sign_id']=$id;
	$res['speed_limit']=$speed_limits['max'];
	return $res;
}

function mrr_gps_speed_limit_values($lat=0,$long=0,$precision=0)
{
	$page="";
	$signs="";
	$max=0;
	$min=0;
	$avg=0;
	$cntr=0;
	$tot=0;
	$url="";
	
	$ne_lat=$lat;
	$ne_long=$long;
	$sw_lat=$lat;     	
	$sw_long=$long;     	
	
	$id=0;
	
	if($lat != 0 || $long != 0)
	{   		
		$temp_lat1=number_format(  ( ceil($lat*100)/100 ) ,$precision);
		$temp_long1=number_format(  ( ceil($long*100)/100 ) ,$precision);
		
		$temp_lat2=number_format(  ( ceil($lat*100)/100 ) ,$precision);
		$temp_long2=number_format(  ( ceil($long*100)/100 ) ,$precision);
		
		$found=0;
		
		$ne_lat="".$temp_lat1."9999";	
     	$ne_long="".$temp_long1."0000";
     	$sw_lat="".$temp_lat2."0000";          		
     	$sw_long="".$temp_long2."9999";  
		
		//try to find it in system...
		$sql="
			select * 
			from ".mrr_find_log_database_name()."safety_report_signs
			where deleted=0
				and round(ne_latitude,2)=round('".sql_friendly($ne_lat)."',2)
				and round(ne_longitude,2)=round('".sql_friendly($ne_long)."',2)
				and round(sw_latitude,2)=round('".sql_friendly($sw_lat)."',2)
				and round(sw_longitude,2)=round('".sql_friendly($sw_long)."',2)
			order by id asc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$found++;
			
			$url=$row['url'];
				
			$ne_lat=$row['ne_latitude'];
			$ne_long=$row['ne_longitude'];
			$sw_lat=$row['sw_latitude'];     			
			$sw_long=$row['sw_longitude'];
			
			$page=$row['sign_info'];
			$signs=$row['sign_list'];
			$max=$row['max_speed'];
			$min=$row['min_speed'];
			$avg=$row['avg_speed'];
			$cntr=$row['sign_count'];
			
			$id=$row['id'];			//found...signals to save in control function...	
		}
		
		//not found, go find it...
		if($found==0)
		{         	
     		$id=0;
     		$url="https://www.wikispeedia.org/a/marks_bb2.php?name=all&nelat=".$ne_lat."&swlat=".$sw_lat."&nelng=".$ne_long."&swlng=".$sw_long."";
     		     	
     		$page=mrr_peoplenet_get_file_contents($url);
     		$page=str_replace("<","[",$page);
     		$page=str_replace(">","]<br>",$page);	
     		
     		$page=str_replace('"','',$page);
     		$page=str_replace("[markers]","",$page);
     		$page=str_replace("[/markers]","",$page);
     		
     		$page=str_replace("[","",$page);
     		$page=str_replace("/","",$page);
     		$page=str_replace("]",",",$page);	
     		
     		$signs_found=substr_count($page,"marker label");
     		
     		$pieces = explode(",",$page);
     		for($i=0; $i < $signs_found; $i++)
     		{
     			$tmp=trim(strtolower($pieces[$i]));
     			$pos1=strpos($tmp," mph=",0) + 5;
     			$pos2=0;
     			$sub="";
     			if($pos1>0)
     			{
     				$pos2=strpos($tmp," kph=", $pos1);
     				$tmpsub=trim( substr($tmp, $pos1, ($pos2-$pos1) ) );
     				$tmpsub=(int)$tmpsub;
     				
     				if($max==0 || $tmpsub > $max)		$max=$tmpsub;
     				if($min==0 || $tmpsub < $min)		$min=$tmpsub;
     				
     				$tot+=$tmpsub;
     				$cntr++;
     				
     				$sub="<br>[".$tmpsub."]";
     			}    			
     			$signs.=$sub;
     		}  
     		
     		//tidy up for output
     		$page=str_replace("marker label=","Sign Submited by ",$page);
     		$page=str_replace("lat=","Latitude=",$page);
     		$page=str_replace("lng=","Longitude=",$page);
     		$page=str_replace("cog=","COG=",$page);
     		$page=str_replace("alt_meters=","Alt Meters=",$page);     				
		}
	}	
	
	$avg=0;
	if($cntr > 0)	$avg=number_format(($tot/$cntr),0);
	
	$res['id']=$id;		
	$res['url']=$url;
	
	$res['ne_lat']=$ne_lat;
	$res['ne_long']=$ne_long;
	$res['sw_lat']=$sw_lat;     	
	$res['sw_long']=$sw_long;
	
	$res['output']=$page;
	$res['signs']=$signs;
	$res['max']=$max;
	$res['min']=$min;
	$res['avg']=$avg;
	$res['num']=$cntr;		
	
	return $res;
}

//PN Driver violation tracking...

function mrr_store_driver_safety_info($dater,$driver,$truck,$employer,$vcode,$vlabel,$gap,$sign,$speed,$feet,$hrs_driven,$hrs_worked,$hrs_rested,$wk_driven,$wk_worked,$wk_rested,$lat,$long,$local)
{
	global $datasource;

	$miles=$feet/5280;
	
	$id=0;
	
	$sqli="
		insert into ".mrr_find_log_database_name()."safety_report_violations
			(id,
			linedate_added,
			linedate,               								
			deleted,
			truck_id,
			driver_id,
			employer_id,
			violation_code,
			violation,
			abrupt_shutdown,
			sign_id,
			cur_speed,
			cur_miles,
			cur_feet,
			cur_hours_driven,
			cur_hours_worked,
			cur_hours_rested,
			wk_hours_driven,
			wk_hours_worked,
			wk_hours_rested,
			excused,
			excused_by,
			excused_date,
			excused_notes,
			latitude,
			longitude,
			location)     			
		values
			(NULL,
			NOW(),
			'".date("Y-m-d H:i:s",strtotime($dater))."',
			'0',
			'".sql_friendly($truck)."',
			'".sql_friendly($driver)."',
			'".sql_friendly($employer)."',
			'".sql_friendly($vcode)."',
			'".sql_friendly($vlabel)."',
			'".sql_friendly($gap)."',
			'".sql_friendly($sign)."',
			'".sql_friendly($speed)."',
			'".sql_friendly($miles)."',
			'".sql_friendly($feet)."',
			'".sql_friendly($hrs_driven)."',
			'".sql_friendly($hrs_worked)."',
			'".sql_friendly($hrs_rested)."',
			'".sql_friendly($wk_driven)."',
			'".sql_friendly($wk_worked)."',
			'".sql_friendly($wk_rested)."',
			0,
			0,
			'0000-00-00 00:00:00',
			'',
			'".sql_friendly($lat)."',
			'".sql_friendly($long)."',
			'".sql_friendly($local)."')
	";
	simple_query($sqli);	
	$id=mysqli_insert_id($datasource);
	return $id;
}
function mrr_update_driver_safety_info($id,$flag,$user,$reason)
{
	$sqli="
		update ".mrr_find_log_database_name()."safety_report_violations set
			excused='".sql_friendly($flag)."',
			excused_by='".sql_friendly($user)."',
			excused_date=NOW(),
			excused_notes='".sql_friendly(trim($reason))."'
		where id='".sql_friendly($id)."'
	";
	simple_query($sqli);	
}

function mrr_peoplenet_driver_violations_update($use_driver_id=0,$use_employer_id=0)
{	//peoplenet driver update info...	
	$report="";
	
	global $defaultsarray;
	
	$dot_min_move = (int) $defaultsarray['pn_dot_driver_min_movement'];
	$dot_max_speed = (int) $defaultsarray['pn_dot_driver_max_speed'];
	$dot_drive_hrs = (int) $defaultsarray['pn_dot_driver_drive_rule'];
	$dot_work_hrs = (int) $defaultsarray['pn_dot_driver_work_rule'];
	$dot_break_hrs = (int) $defaultsarray['pn_dot_driver_break_rule'];
	$dot_wk_days = (int) $defaultsarray['pn_dot_driver_week_days'];
	$dot_wk_hours = (int) $defaultsarray['pn_dot_driver_week_hours'];
	$dot_wk_break = (int) $defaultsarray['pn_dot_driver_week_break'];
	
	$pre_inspection = 0 + $defaultsarray['pn_dot_inspection_pre'];
	$post_inspection = 0 + $defaultsarray['pn_dot_inspection_post'];
	
	$gap_detector = 0 + $defaultsarray['pn_dot_gap_detection'];		//this holds amount of time between GPS points to be considered a flagged warning for misuse or abrupt shutdown
	
	$wk_hours_driven=0;
	$wk_hours_worked=0;
	$wk_hours_rested=0; 
	
	$hours_driven=0;
	$hours_worked=0;
	$hours_rested=0;
	
	$sqld="
		select drivers.*,
			option_values.fvalue
		from drivers
			left join option_values on option_values.id=drivers.employer_id
		where drivers.active=1
			and drivers.deleted=0
			and drivers.id!=345
			and drivers.id!=392
			".($use_driver_id > 0 && $use_driver_id!="" ? " and drivers.id='".sql_friendly($use_driver_id)."'" : "")."
			".($use_employer_id > 0 && $use_employer_id!="" ? " and drivers.employer_id='".sql_friendly($use_employer_id)."'" : "")."
		order by drivers.name_driver_last asc,
			drivers.name_driver_first asc
	";
	$datad=simple_query($sqld);
	while($rowd=mysqli_fetch_array($datad))
	{
		$employer_id=$rowd['employer_id'];
		$driver_id=$rowd['id'];
		     		
		$min_date="2013-05-13 00:00:00";
		$starting_id=0;
		
		//get the last record...
		$sql_last="
     		select id,
     			linedate
     		from ".mrr_find_log_database_name()."safety_report_violations
     		where driver_id='".sql_friendly($driver_id)."'
     			and deleted=0
     		order by linedate desc
     	";
     	$data_last=simple_query($sql_last);
     	if($row_last=mysqli_fetch_array($data_last))
     	{
     		$starting_id=$row_last['id'];
     		$min_date=$row_last['linedate'];
     	}
     	else
     	{	//no records...start this driver from the beginning.
     		$starting_id=mrr_store_driver_safety_info($min_date,$driver_id,0,$employer_id,0,"",0,0,0,0,0,0,0,0,0,0,0,0,"");
     	}
		     		
		$sql_last="
     		select *
     		from ".mrr_find_log_database_name()."safety_report_violations
     		where id='".sql_friendly($starting_id)."'
     	";
     	$data_last=simple_query($sql_last);
     	if($row_last=mysqli_fetch_array($data_last))
     	{
     		$starting_id=$row_last['id'];
     		$min_date=$row_last['linedate'];
     		
     		$last_time=strtotime($row_last['linedate']);
			$start_time=strtotime($row_last['linedate']);
			
			$wk_last_time=strtotime($row_last['linedate']);
			$wk_start_time=strtotime($row_last['linedate']);
     		
     		$hours_driven=$row_last['cur_hours_driven'];
			$hours_worked=$row_last['cur_hours_worked'];
     		$hours_rested=$row_last['cur_hours_rested'];
     		
     		$wk_hours_driven=$row_last['wk_hours_driven'];
     		$wk_hours_worked=$row_last['wk_hours_worked'];
     		$wk_hours_rested=$row_last['wk_hours_rested'];
     		
     		$last_latitude=$row_last['latitude'];
          	$last_longitude=$row_last['longitude'];   	
          	
          	$prev_date=date("m/d/Y H:i", strtotime($row_last['linedate']));	
               $prev_lat=$row_last['latitude'];
               $prev_long=$row_last['longitude'];
               $prev_truck=$row_last['truck_id'];  	
               $truck_moved=0;
               
               $report.="<br>Starting from ".$min_date." for Driver ".$driver_id.".";
     		          		
     		$sqlt="
          		select distinct trucks_log.truck_id,
          			trucks_log.driver_id,
          			trucks_log.driver2_id,
          			(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_name
          		from trucks_log
          		where trucks_log.deleted=0
          			and (trucks_log.driver_id='".sql_friendly($driver_id)."' or trucks_log.driver2_id='".sql_friendly($driver_id)."')
          			and trucks_log.linedate_pickup_eta>='".date("Y-m-d H:i:s", strtotime($min_date))."'
          		order by trucks_log.linedate_pickup_eta asc
          	";
          	$datat=simple_query($sqlt);
          	if($rowt=mysqli_fetch_array($datat))
          	{               		               		
          		$truck_id=$rowt['truck_id'];  		
          		$truck_name=$rowt['truck_name'];
          		
          		$report.="<br>Truck ".$truck_name." (".$truck_id.")";
          		
          		$sql="
                    	select truck_tracking.*,
                    		(select name_driver_first from drivers where drivers.id=truck_tracking.driver_id) as driver_first_name,
                    		(select name_driver_last from drivers where drivers.id=truck_tracking.driver_id) as driver_last_name,
                    		(select name_driver_first from drivers where drivers.id=truck_tracking.driver2_id) as driver2_first_name,
                    		(select name_driver_last from drivers where drivers.id=truck_tracking.driver2_id) as driver2_last_name,
                    		(select name_truck from trucks where trucks.id=truck_tracking.truck_id) as truck_name
                    	from ".mrr_find_log_database_name()."truck_tracking
                    	where truck_tracking.linedate>='".date("Y-m-d H:i:s", strtotime($min_date))."'
                    		 and truck_tracking.truck_id='".sql_friendly($truck_id)."'
                    	order by truck_tracking.linedate asc,truck_tracking.id desc
                    	
                    ";	
                    $data=simple_query($sql);
                    while($row=mysqli_fetch_array($data))
                    {
          			if($rowt['driver2_id']==$driver_id && $row['driver2_id']==0)
                    	{
                    		$sqlu=" update truck_tracking set driver2_id='".sql_friendly($driver_id)."' where id='".sql_friendly($rowd['id'])."'";
                    		//simple_query($sqlu);	
                    	}
                    	elseif($rowt['driver_id']==$driver_id && $row['driver_id']==0)
                    	{
                    		$sqlu=" update truck_tracking set driver_id='".sql_friendly($driver_id)."' where id='".sql_friendly($rowd['id'])."'";
                    		//simple_query($sqlu);		
                    	} 
          			
          			if(date("m/d/Y H:i", strtotime($row['linedate'])) != $prev_date)
          			{
               			$gps_dist=0;  
               			$gps_dist2=0;  
                    		$prev_date=date("m/d/Y H:i", strtotime($row['linedate']));			//used to not show duplicate date points...caused from packets reloading...always use the last one.  Query above is sorted that way already.
                    		
                    		$report.="<br>GPS Date='".$prev_date."'";
                    		
                    		//Truck Switched...
               			if($prev_truck!=$truck_id)
               			{               				
               				$tlabel="Truck Switched to ".$truck_name.".";
               				$starting_id=mrr_store_driver_safety_info($row['linedate'],$driver_id,$prev_truck,$employer_id,0,$tlabel,0,0,0,0,0,0,0,0,0,0,0,0,"");
               				
               				$last_latitude=0;
          					$last_longitude=0;	
               				$prev_truck=$truck_id;	
               			}
                    		
                    		if($last_latitude==0 && $last_longitude==0)	
                    		{
                    			$last_latitude=$row['latitude'];
          					$last_longitude=$row['longitude'];
          					$prev_lat=$row['latitude'];
               				$prev_long=$row['longitude']; 
          					
          					$hours_worked+=$pre_inspection + $post_inspection;
          					$wk_hours_worked+=$pre_inspection + $post_inspection; 
          					
          					
          					$last_time=strtotime($row['linedate']);
          					$start_time=strtotime($row['linedate']);
          					$wk_last_time=strtotime($row['linedate']);
          					$wk_start_time=strtotime($row['linedate']);
          					
          					$truck_moved=0;
                    		}
                    		else
                    		{
                    			$gps_dist=mrr_distance_between_gps_points($row['latitude'],$row['longitude'],$prev_lat,$prev_long,1);				//has MILES...CD=1 converts to ft before rounding.
                    			$gps_dist2=mrr_distance_between_gps_points($row['latitude'],$row['longitude'],$last_latitude,$last_longitude,1);		//has MILES...CD=1 converts to ft before rounding.                   			
                    			
                    			$timer= (strtotime($row['linedate']) - $last_time)/3600;
                    			$stop_watch=(strtotime($row['linedate']) - $start_time)/3600;
                    			
                    			$wk_timer= (strtotime($row['linedate']) - $wk_last_time)/3600;
                    			$wk_stop_watch=(strtotime($row['linedate']) - $wk_start_time)/3600;	
                    			
                    			if($gps_dist >= $dot_min_move)
                    			{
                    				//count this as movement...
                    				$hours_driven+=$timer;  
                    				$wk_hours_driven+=$wk_timer;
                    				
                    				$truck_moved=1;             				
                    			}
                    			else
                    			{
                    				//not counted as movement...
                    				$hours_worked+=$timer;	              				
                    				$hours_rested+=$timer; 
                    				
                    				$wk_hours_worked+=$wk_timer;
                    				$wk_hours_rested+=$wk_timer; 
                    				$truck_moved=0;
                    			} 
                    			
          					$last_time=strtotime($row['linedate']);
          					$wk_last_time=strtotime($row['linedate']); 
                    		}
               			
                    		if($truck_moved>0)
               			{
               				$hours_rested=0;		//reset rest to zero...work done before DOT hrs rule reached for break.	
               				$wk_hours_rested=0;
               			}
               			
               			$sign_id=0;
						//$speed_sign="";     						
						if($row['latitude']!=0 || $row['longitude']!=0)
						{
							$speed_check=mrr_gps_speed_limit_finder($row['latitude'],$row['longitude']);
							
							$sign_id=$speed_check['sign_id'];
                    		} 
               			
               			$abrupt_shutdown_flag=0;
                    		if($timer > $gap_detector)
                    		{
                    			$abrupt_shutdown_flag=1;
                    		}
               			
               			//issue speed violations
               			if($row['truck_speed'] > $dot_max_speed)
               			{                    				
               				$vlabel="Speeding ".$row['truck_speed']."MPH.";							//1=Speeding...
               				
               				mrr_store_driver_safety_info($row['linedate'],$driver_id,$truck_id,$employer_id,1,$vlabel,$abrupt_shutdown_flag,$sign_id,$row['truck_speed'],$gps_dist,
               					$hours_driven,$hours_worked,$hours_rested,$wk_hours_driven,$wk_hours_worked,$wk_hours_rested,$row['latitude'],$row['longitude'],$row['location']);
               			}
               			
               			
               			//check if reset is possible....WEEKLY HOURS only
                    		if($wk_hours_rested > $dot_wk_break)
               			{
               				$wk_last_time=strtotime($row['linedate']);
     						$wk_start_time=strtotime($row['linedate']);	
     						
     						$wk_hours_driven=0;
     						$wk_hours_worked=0;
     						$wk_hours_rested=0; 
     						
     						$hours_driven=0;
							$hours_worked=0;
     						$hours_rested=0;
     						
     						$last_time=strtotime($row['linedate']);
     						$start_time=strtotime($row['linedate']);
     						
     						$vlabel="Reset Week.";
                    			mrr_store_driver_safety_info($row['linedate'],$driver_id,$truck_id,$employer_id,0,$vlabel,$abrupt_shutdown_flag,$sign_id,$row['truck_speed'],$gps_dist,
               					$hours_driven,$hours_worked,$hours_rested,$wk_hours_driven,$wk_hours_worked,$wk_hours_rested,$row['latitude'],$row['longitude'],$row['location']);
               				
               			}               			
               			                    			
               			
                    		//attempt DAILY reset
                    		if($hours_rested > $dot_break_hrs)
                    		{	
                    			$hours_driven=0;
							$hours_worked=$pre_inspection + $post_inspection;
							$wk_hours_worked=$pre_inspection + $post_inspection;
     						$hours_rested=0;
     						
     						$last_time=strtotime($row['linedate']);
     						$start_time=strtotime($row['linedate']);
     						
     						$vlabel="Reset Day. Inspections added.";
                    			mrr_store_driver_safety_info($row['linedate'],$driver_id,$truck_id,$employer_id,0,$vlabel,$abrupt_shutdown_flag,$sign_id,$row['truck_speed'],$gps_dist,
               					$hours_driven,$hours_worked,$hours_rested,$wk_hours_driven,$wk_hours_worked,$wk_hours_rested,$row['latitude'],$row['longitude'],$row['location']);
                    		}
                    		
                    		//Daily violations
               			if($hours_driven > $dot_drive_hrs)
               			{
     						$vlabel="Drove more than ".$dot_drive_hrs." hrs.";							//2=over-driven for day...
               				
               				mrr_store_driver_safety_info($row['linedate'],$driver_id,$truck_id,$employer_id,2,$vlabel,$abrupt_shutdown_flag,$sign_id,$row['truck_speed'],$gps_dist,
               					$hours_driven,$hours_worked,$hours_rested,$wk_hours_driven,$wk_hours_worked,$wk_hours_rested,$row['latitude'],$row['longitude'],$row['location']);
               			}
               			if($hours_worked > $dot_work_hrs)
               			{                    				
               				$vlabel="Worked more than ".$dot_work_hrs." hrs.";							//3=overworked for day...
               				
               				mrr_store_driver_safety_info($row['linedate'],$driver_id,$truck_id,$employer_id,3,$vlabel,$abrupt_shutdown_flag,$sign_id,$row['truck_speed'],$gps_dist,
               					$hours_driven,$hours_worked,$hours_rested,$wk_hours_driven,$wk_hours_worked,$wk_hours_rested,$row['latitude'],$row['longitude'],$row['location']);
               			}
               			
               			//Weekly violations
               			if($wk_hours_worked > $dot_wk_hours && $wk_timer <= (24*$dot_wk_days))
               			{
     						$vlabel="Worked more than ".$dot_wk_hours." hrs in Week.";						//4=overworked for week...
               				
               				mrr_store_driver_safety_info($row['linedate'],$driver_id,$truck_id,$employer_id,4,$vlabel,$abrupt_shutdown_flag,$sign_id,$row['truck_speed'],$gps_dist,
               					$hours_driven,$hours_worked,$hours_rested,$wk_hours_driven,$wk_hours_worked,$wk_hours_rested,$row['latitude'],$row['longitude'],$row['location']);
               			}
                    		if(!isset($row['location']))		$row['location']="";
                    		
                    		$report.="".$gps_dist2."(".$gps_dist2.") -- Movement=".$truck_moved." --".$timer."[".$stop_watch."] ... ".$wk_timer."[".$wk_stop_watch."] == 
                    				".$hours_driven."D ".$hours_worked."W ".$hours_rested."R (".$row['latitude'].",".$row['longitude'].") ".$row['location']."";
                    		
                    		$prev_lat=$row['latitude'];
               			$prev_long=$row['longitude']; 
               			
          			}	//end if 3
          			
          		}	//end while 2
          		
     		}	//end if 2          
     				          				
     	}	//end if 1		
     	
	}//end while 1
	return $report; 
}	

function mrr_show_gps_points_for_truck_and_dates($truck_id,$date_start,$date_end,$truck_name)
{		
	$last_date="";
	$prev_date="";
	$prev_lat=0;
	$prev_long=0;
	
	$prev_odom=0;
	
	$cur_hours_driven=0;
	$cur_hours_worked=0;
	$cur_hours_rested=0;
	
	$wk_hours_driven=0;
	$wk_hours_worked=0;
	$wk_hours_rested=0;
	
	global $defaultsarray;
	
	$dot_min_move = (int) $defaultsarray['pn_dot_driver_min_movement'];
	$dot_max_speed = (int) $defaultsarray['pn_dot_driver_max_speed'];
	$dot_drive_hrs = (int) $defaultsarray['pn_dot_driver_drive_rule'];
	$dot_work_hrs = (int) $defaultsarray['pn_dot_driver_work_rule'];
	$dot_break_hrs = (int) $defaultsarray['pn_dot_driver_break_rule'];
	$dot_wk_days = (int) $defaultsarray['pn_dot_driver_week_days'];
	$dot_wk_hours = (int) $defaultsarray['pn_dot_driver_week_hours'];
	$dot_wk_break = (int) $defaultsarray['pn_dot_driver_week_break'];
	
	$pre_inspection = 0 + $defaultsarray['pn_dot_inspection_pre'];
	$post_inspection = 0 + $defaultsarray['pn_dot_inspection_post'];
	
	$gap_detector = 0 + $defaultsarray['pn_dot_gap_detection'];		//this holds amount of time between GPS points to be considered a flagged warning for misuse or abrupt shutdown
	
	$res="
		<table class='admin_menu1 font_display_section mrr_view_details' style='text-align:left;margin:10px' width='1800'>
		<tr>
			<td valign='top'><b>GPS Date</b></td>
			<td valign='top'><b>Abrupt</b></td>
			<td valign='top'><b>Moved</b></td>
			<td valign='top'><b>Latitude</b></td>
			<td valign='top'><b>Longitude</b></td>
			<td valign='top'><b>Location of Truck ".$truck_name."</b></td>
			<td valign='top'><b>Heading</b></td>
			<td valign='top'><b>MPH</b></td>
			<td valign='top'><b>Ignition</b></td>
			<td valign='top'><b>GPS<br>Odom</b></td>
			<td valign='top'><b>Roll<br>Odom</b></td>
			<td valign='top'><b>PX<br>Odom</b></td>
			<td valign='top'><b>PX<br>Fuel</b></td>
			<td valign='top'><b>PX<br>Idle</b></td>
			<td valign='top'><b>DOT</b></td>
			<td valign='top'><b>DyWork</b></td>
			<td valign='top'><b>DyRest</b></td>
			<td valign='top'><b>WkWork</b></td>
			<td valign='top'><b>WkRest</b></td>
		</tr>
	";
	$cntr=0;
	$reset_needed_11=0;
     $reset_needed_14=0;
     $reset_needed_wk=0;
	
	$sql="
     	select truck_tracking.*,
     		(select name_driver_first from drivers where drivers.id=truck_tracking.driver_id) as driver_first_name,
     		(select name_driver_last from drivers where drivers.id=truck_tracking.driver_id) as driver_last_name,
     		(select name_driver_first from drivers where drivers.id=truck_tracking.driver2_id) as driver2_first_name,
     		(select name_driver_last from drivers where drivers.id=truck_tracking.driver2_id) as driver2_last_name,
     		(select name_truck from trucks where trucks.id=truck_tracking.truck_id) as truck_name
     	from ".mrr_find_log_database_name()."truck_tracking
     	where truck_tracking.linedate>='".date("Y-m-d", strtotime($date_start))." 00:00:00'
     		and truck_tracking.linedate<='".date("Y-m-d", strtotime($date_end))." 23:59:59'
     		and truck_tracking.truck_id='".sql_friendly($truck_id)."'
     	order by truck_tracking.linedate asc,truck_tracking.id desc
     	
     ";	
     $data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
	{
		//$row['id']
		
		$is_first=0;
		if($prev_date=="" || $last_date=="")
		{
			$last_date=date("m/d/Y H:i", strtotime($row['linedate']));
			$prev_date=date("m/d/Y H:i", strtotime($row['linedate']));	
			$prev_lat=$row['latitude'];
			$prev_long=$row['longitude'];
			$prev_odom=$row['performx_odometer'];
			$is_first=1;
			
			$cur_hours_worked=($pre_inspection + $post_inspection);
			$wk_hours_worked=($pre_inspection + $post_inspection);
		}
		
		if(date("m/d/Y H:i", strtotime($row['linedate'])) != $prev_date || $is_first==1)
		{				
			$gps_dist=($row['performx_odometer'] - $prev_odom) * 5280;				
			$time_diff=(strtotime($row['linedate']) - strtotime($prev_date))/3600;
			
			$ig_mask="off";
     		if($row['ignition'] > 0)				$ig_mask="ON";
     		
     		$head_mask="North";
     		if($row['truck_heading'] == 1)		$head_mask="Northeast ";
     		if($row['truck_heading'] == 2)		$head_mask="East";
     		if($row['truck_heading'] == 3)		$head_mask="Southeast";
     		if($row['truck_heading'] == 4)		$head_mask="South";
     		if($row['truck_heading'] == 5)		$head_mask="Southwest";
     		if($row['truck_heading'] == 6)		$head_mask="West";
     		if($row['truck_heading'] == 7)		$head_mask="Northwest";
			
			$flagged="";
			
			$time_mask="";
			if($time_diff > $gap_detector)		$time_mask="<span class='alert'><b>Yes</b></span>";
			
			$move_mask="";
			$moved_flag=0;
			if($gps_dist >= $dot_min_move)
			{
				$move_mask="<span style='color:#00cc00;'><b>Moved</b></span>";
				$moved_flag=1;
				
				$cur_hours_driven+=$time_diff;
				$wk_hours_driven+=$time_diff;	
				
				$cur_hours_rested=0;
				$wk_hours_rested=0;				
			}
			else
			{
				$cur_hours_worked+=$time_diff;
				$cur_hours_rested+=$time_diff;
				     				
				$wk_hours_worked+=$time_diff;
				$wk_hours_rested+=$time_diff;	
			}
			
			//resets
			if($wk_hours_rested>=$dot_wk_break)
			{					
				$wk_hours_driven=0;
				$wk_hours_worked=0;
				$wk_hours_rested=0;	
				$flagged.="<div style='color:#00cc00;'><b>Week Reset</b></span>";
				
				$reset_needed_wk=0;
			}
			if($cur_hours_rested>=$dot_break_hrs)
			{
				$cur_hours_driven=0;
				
				$cur_hours_worked=($pre_inspection + $post_inspection);
				$wk_hours_worked=($pre_inspection + $post_inspection);
				
				$cur_hours_rested=0;
				$flagged.="<div style='color:#00cc00;'><b>Day Reset</b></span>";	
				
				$reset_needed_11=0;
				$reset_needed_14=0;
			}
			
			//violations...
			if($cur_hours_driven > $dot_drive_hrs && $reset_needed_11==0 && $moved_flag==1)
			{
				$flagged.="<div class='alert'><b>11-Hour</b></span>";	
				$reset_needed_11=1;	
			}
			if($cur_hours_worked > $dot_work_hrs && $reset_needed_14==0 && $moved_flag==1)
			{
				$flagged.="<div class='alert'><b>14-Hour</b></span>";	
				$reset_needed_14=1;	
			}
			if($wk_hours_worked > $dot_wk_hours && $reset_needed_wk==0 && $moved_flag==1)
			{
				$flagged.="<div class='alert'><b>70-Hour</b></span>";	
				$reset_needed_wk=1;	
			}
			
			if($row['truck_speed'] > $dot_max_speed)
			{
				$flagged.="<div class='alert'><b>Speeding</b></span>";	
			}
						
			$res.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>
					<td valign='top'>".date("m/d/Y H:i", strtotime($row['linedate']))."</td>
					<td valign='top'>".$time_mask."</td>
					<td valign='top'>".$move_mask."</td>
					<td valign='top'>".$row['latitude']."</td>
					<td valign='top'>".$row['longitude']."</td>
					<td valign='top'>".$row['location']."</td>
					<td valign='top'>".$head_mask."</td>
					<td valign='top'><b>".$row['truck_speed']."</b></td>					
					<td valign='top'>".$ig_mask."</td>
					<td valign='top'>".$row['gps_odometer']."</td>
					<td valign='top'>".$row['gps_rolling_odometer']."</td>
					<td valign='top'>".$row['performx_odometer']."</td>
					<td valign='top'>".$row['performx_fuel']."</td>
					<td valign='top'>".$row['performx_idle']."</td>
					<td valign='top'>".$flagged."</td>
					<td valign='top'>".number_format($cur_hours_worked,2)."</td>
					<td valign='top'>".number_format($cur_hours_rested,2)."</td>
					<td valign='top'>".number_format($wk_hours_worked,2)."</td>
					<td valign='top'>".number_format($wk_hours_rested,2)."</td>
				</tr>
			";
			$cntr++;	
						
			$prev_date=date("m/d/Y H:i", strtotime($row['linedate']));
			$prev_lat=$row['latitude'];
			$prev_long=$row['longitude'];	
			$prev_odom=$row['performx_odometer'];	
		}	
	}
	$res.="</table>";		
	return $res;
}

function mrr_peoplenet_email_processor_fetch_truck_lat_long_alt1($truck_id,$dispatch_start)
{
	$lat="0";
	$long="0";
	$stamp="0000-00-00 00:00:00";	
	$near_far="";
	$age=0;
	$last_gps_dist=0;
	$location="";	
	$speed=0;	
	$heading=0;
	
	$sql="
     	select truck_tracking.*,
     		UNIX_TIMESTAMP(NOW()) as secs_now,
     		UNIX_TIMESTAMP(truck_tracking.linedate) as secs_aged
     	from ".mrr_find_log_database_name()."truck_tracking
     	where truck_tracking.linedate>='".date("Y-m-d", strtotime($dispatch_start))." 00:00:00'
     		and truck_tracking.truck_id='".sql_friendly($truck_id)."'
     	order by truck_tracking.linedate desc
     	limit 1          	
     ";	
     $data=simple_query($sql);
     if($row=mysqli_fetch_array($data))
     {
     	//attempt to find out if the truck is moving further away or closer... "Near...Far"        	
     	$lat=$row['latitude'];
		$long=$row['longitude'];
		$stamp=$row['linedate'];	
		
		$location=$row['location'];	
		$speed=$row['truck_speed'];	
		$heading=$row['truck_heading'];	
		
		$age=($row['secs_now'] - $row['secs_aged'])/60;	// make minutes old for age...				
     }	          
     $res['lat']=$lat;
     $res['long']=$long;
     $res['date']=$stamp;
     $res['age']=abs($age);
     $res['closer']="";
     $res['location']=$location;	
	$res['truck_speed']=$speed;	
	$res['truck_heading']=$heading;
     
     return $res;
}

function mrr_peoplenet_email_processor_fetch_truck_lat_long($truck_id,$dispatch_start)
{
	$lat="0";
	$long="0";
	$stamp="0000-00-00 00:00:00";	
	$near_far="";
	$age=0;
	$last_gps_dist=0;
	$location="";	
	$speed=0;	
	$heading=0;
	
	$sql="
     	select truck_tracking.*,
     		UNIX_TIMESTAMP(NOW()) as secs_now,
     		UNIX_TIMESTAMP(truck_tracking.linedate) as secs_aged
     	from ".mrr_find_log_database_name()."truck_tracking
     	where truck_tracking.linedate>='".date("Y-m-d", strtotime($dispatch_start))." 00:00:00'
     		and truck_tracking.truck_id='".sql_friendly($truck_id)."'
     	order by truck_tracking.linedate asc,truck_tracking.id desc
     	
     ";	
     $data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {
     	//attempt to find out if the truck is moving further away or closer... "Near...Far"
     	if($lat!="0" && $long !="0")
     	{
     		$gps_dist=mrr_distance_between_gps_points($row['latitude'],$row['longitude'],$lat,$long,1);	
     		if($last_gps_dist!=0)
     		{
     			if(abs($last_gps_dist) > abs($gps_dist))		$near_far="Near";
     			elseif(abs($last_gps_dist) > abs($gps_dist))		$near_far="Far";
     		}          		
     		$last_gps_dist=abs($gps_dist);
     	}          	
     	$lat=$row['latitude'];
		$long=$row['longitude'];
		$stamp=$row['linedate'];	
		
		$location=$row['location'];	
		$speed=$row['truck_speed'];	
		$heading=$row['truck_heading'];	
		
		$age=($row['secs_now'] - $row['secs_aged'])/60;	// make minutes old for age...				
     }	          
     $res['lat']=$lat;
     $res['long']=$long;
     $res['date']=$stamp;
     $res['age']=abs($age);
     $res['closer']=$near_far;
     $res['location']=$location;	
	$res['truck_speed']=$speed;	
	$res['truck_heading']=$heading;
     
     return $res;
}

function mrr_peoplenet_find_last_event_for_dispatch($truck_id,$dispatch_id,$dispatch_start)
{
	$lat="0";
	$long="0";
	$stamp="0000-00-00 00:00:00";	
	$near_far="";
	$age=0;
	$last_gps_dist=0;
	
	$sql="
     	select truck_tracking_event_history.*,
     		UNIX_TIMESTAMP(NOW()) as secs_now,
     		UNIX_TIMESTAMP(truck_tracking_event_history.linedate_added) as secs_aged
     	from ".mrr_find_log_database_name()."truck_tracking_event_history
     	where truck_tracking_event_history.truck_id='".sql_friendly($truck_id)."'
     		and truck_tracking_event_history.disptach_id='".sql_friendly($dispatch_id)."'
     		and truck_tracking_event_history.linedate>='".date("Y-m-d", strtotime($dispatch_start))." 00:00:00'
     	order by truck_tracking_event_history.linedate asc,truck_tracking_event_history.id desc          	
     ";	
     $data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {
     	//attempt to find out if the truck is moving further away or closer... "Near...Far"
     	if($lat!="0" && $long !="0")
     	{
     		$gps_dist=mrr_distance_between_gps_points($row['e_latitude'],$row['e_longitude'],$lat,$long,1);	
     		if($last_gps_dist!=0)
     		{
     			if(abs($last_gps_dist) > abs($gps_dist))		$near_far="Near";
     			elseif(abs($last_gps_dist) > abs($gps_dist))		$near_far="Far";
     		}          		
     		$last_gps_dist=abs($gps_dist);
     	}          	
     	$lat=$row['e_latitude'];
		$long=$row['e_longitude'];
		$stamp=$row['linedate'];	
		
		$age=($row['secs_now'] - $row['secs_aged'])/60;	// make minutes old for age...					
     }	          
     $res['lat']=$lat;
     $res['long']=$long;
     $res['date']=$stamp;
     $res['age']=abs($age);
     $res['closer']=$near_far;
     return $res;	
}

function mrr_purge_all_truck_odometer_readings($start_date,$end_date)
{
     $rep="<table cellpadding='1' cellspacing='1' border='0' width='800'>";
     $rep.="
     		<tr>
     			<td valign='top'>ID</td>	
     			<td valign='top'>DATE</td>
     			<td valign='top'>TRUCK ID</td>
     			<td valign='top'>ODOMETER</td>
     		</tr>
     	";	
     $cntr=0;	
     $sql3="
     	select distinct(truck_id)
     	from ".mrr_find_log_database_name()."truck_tracking_odometer
          where linedate>='".date("Y-m-d",strtotime($start_date))." 00:00:00'
          	and linedate<='".date("Y-m-d",strtotime($end_date))." 23:59:59'
     	order by truck_id asc
     ";
     $data3=simple_query($sql3);
     while($row3=mysqli_fetch_array($data3))
     {
     	$truck_id=$row3['truck_id'];
     	
     	$sql2="
          	select distinct(linedate)
          	from ".mrr_find_log_database_name()."truck_tracking_odometer
               where truck_id='".sql_friendly($truck_id)."' 
               	and linedate>='".date("Y-m-d",strtotime($start_date))." 00:00:00'
          		and linedate<='".date("Y-m-d",strtotime($end_date))." 23:59:59'
          	order by linedate asc
          ";
          $data2=simple_query($sql2);
          while($row2=mysqli_fetch_array($data2))
          {
          	$sql="
               	select *
               	from ".mrr_find_log_database_name()."truck_tracking_odometer
                    where truck_id='".sql_friendly($truck_id)."' and linedate='".$row2['linedate']."'
               	order by id desc     	
               ";
               $data=simple_query($sql);
               if($row=mysqli_fetch_array($data))
               {
               	$rep.="
               		<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd")."'>
               			<td valign='top'>".$row['id']."</td>	
               			<td valign='top'>".$row['linedate']."</td>
               			<td valign='top'>".$row['truck_id']."</td>
               			<td valign='top'>".$row['odometer']."</td>
               		</tr>
               	";	
               	
               	$sqld="delete from ".mrr_find_log_database_name()."truck_tracking_odometer where truck_id='".sql_friendly($truck_id)."' and linedate='".$row['linedate']."' and id!='".sql_friendly($row['id'])."'";
               	simple_query($sqld);
               	
               	$cntr++;
               }
          }
    }
    $rep.="</table>";
    return $rep;
}

function mrr_peoplenet_last_msg_sent($val="")
{
	$stamp=date("Y-m-d H:i:s",time());
	if(trim($val)!="")		$stamp=trim($val);
	$sql="update defaults set xvalue_string='".$stamp."' where xname='peoplenet_last_msg_sent'";
     simple_query($sql);
}
function mrr_test_PN_last_msg_sent_date()
{
	global $defaultsarray;
	$stamp=trim($defaultsarray['peoplenet_last_msg_sent']);
	$warn=(int) trim($defaultsarray['peoplenet_last_msg_sent_warn']);
	$monitor=trim($defaultsarray['peoplenet_hot_msg_cc']);
	$FromName=trim($defaultsarray['company_name']);
	$From=trim($defaultsarray['company_email_address']);
		
	$rep="Last Date PN Message Sent for Customers has been reset to <b>0000-00-00 00:00:00</b>.";
	if($stamp!="0000-00-00 00:00:00")
	{     	
     	$stamp=date("m/d/Y H:i:s",strtotime($stamp));
     	
     	//$stamp=date("m/d/Y H:i:s",strtotime("4/01/2017 00:00:00"));		//test line....
     	
     	$diff = (time() - strtotime($stamp)) / (60*60);
     	
     	$rep="Last Date PN Message Sent for Customers was <b>".$stamp."</b>. Difference = <b>".$diff." hours</b>.";
     	
     	if($diff >= $warn && $warn > 0)
     	{
     		$headers  = 'MIME-Version: 1.0' . "\r\n";
          	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";          
          	$headers .= 'From: '.$FromName.' <'.$From.'>' . "\r\n";     		
     		
     		@ $sent=mail($monitor, "PN Message Sending Delay...check for clogs.", $rep, $headers);
     		mrr_peoplenet_last_msg_sent("0000-00-00 00:00:00");
     	}
	}
	return $rep;
}
?>