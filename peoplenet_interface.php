<? include('application.php') ?>
<? 
	$admin_page = 1;
	$max_packet=0;
	$serve_output2="";
	$max_msg_packet=0;
	$serve_output3="";
	
	$java_run_list="";
	
	$sdater=date("m/d/Y H:i:s");
	$diff_timer="";
	
	$serve_output="";
	$dispatch_section="";
	$mrr_message="";
	
	$map_bypass=0;
	
	
	
		
	//load section for dispatches....set rest of form if selected/set
	if(isset($_GET['find_truck_id']))		$_POST['find_truck_id']=$_GET['find_truck_id'];
     if(isset($_GET['find_truck_name']))	$_POST['find_truck_name']=$_GET['find_truck_name'];
     if(isset($_GET['find_load_id']))		$_POST['find_load_id']=$_GET['find_load_id'];
     if(isset($_GET['run_dispatch']))		$_POST['run_dispatch']=$_GET['run_dispatch'];
     if(isset($_GET['find_driver_id']))		$_POST['find_driver_id']=$_GET['find_driver_id'];
     if(isset($_GET['find_preplan']))		$_POST['find_preplan']=$_GET['find_preplan'];
     if(isset($_GET['run_preplan']))		$_POST['run_preplan']=$_GET['run_preplan'];
     
     $auto_run=0;
     if(isset($_GET['auto_run']))			$auto_run=1;
     
          
     if(!isset($_POST['find_truck_id']))	$_POST['find_truck_id']=0;
     if(!isset($_POST['find_truck_name']))	$_POST['find_truck_name']="";
     if(!isset($_POST['find_load_id']))		$_POST['find_load_id']=0;
     if(!isset($_POST['run_dispatch']))		$_POST['run_dispatch']=0;
     if(!isset($_POST['find_driver_id']))	$_POST['find_driver_id']=0;
     if(!isset($_POST['find_preplan']))		$_POST['find_preplan']=0;
     if(!isset($_POST['run_preplan']))		$_POST['run_preplan']=0;
     if(!isset($_POST['canceler']))		$_POST['canceler']=0;
          
     if($_POST['find_truck_id'] > 0)		
     {
     	$_POST['truck_id']=$_POST['find_truck_id'];
     	$_POST['truck_id2']=$_POST['find_truck_id'];
     	
     	$test_dater=date("Y-m-d H:i:s",strtotime("-7 days",strtotime($sdater)));		//replaces "DATE_SUB(NOW(),INTERVAL 7 DAY)" in query below.
     	
     	if($_POST['find_load_id'] == 0)
     	{	//no load, but there is a truck selected...attempt to find next load that is still active and not completed...but do not check too far in the past.
     		$sql="
          		select load_handler_id
          		from load_handler,trucks_log
          		where load_handler.id=trucks_log.load_handler_id
          			and trucks_log.deleted=0
          			and load_handler.deleted=0
          			and dispatch_completed=0
          			and load_handler.linedate_pickup_eta >='".$test_dater."'
          			and trucks_log.truck_id='".sql_friendly($_POST['find_truck_id'])."'
          		order by load_handler.linedate_pickup_eta asc          		
          	";	//limit 1
          	$data=simple_query($sql);
          	if($row=mysqli_fetch_array($data))
          	{
          		$_POST['find_load_id']=$row['load_handler_id'];
          	}	
     	}
     }
     if($_POST['find_truck_name']!="" )	
     {
     	$truckname=$_POST['find_truck_name'];
     }
     if($_POST['run_dispatch'] > 0)		
     {
     	$_POST['dispatch_id']=$_POST['run_dispatch'];
     }
     
     $mrrto=$defaultsarray['special_email_monitor'];
     
     if($_POST['find_load_id'] > 0)		
     {
     	if($_POST['canceler'] > 0)
     	{
     		if($_POST['run_dispatch'] > 0 && $_POST['find_load_id'] > 0 && $_POST['find_truck_id'] > 0)
     		{
     			mrr_cancel_peoplenet_complete_dispatch($_POST['find_load_id'],$_POST['run_dispatch'],$_POST['find_truck_id']);
     		}
     		elseif($_POST['run_preplan'] > 0 && $_POST['find_load_id'] > 0 && $_POST['find_truck_id'] > 0)
     		{
     			mrr_cancel_peoplenet_complete_preplan_load($_POST['find_load_id'],$_POST['find_truck_id'],$_POST['run_preplan']);
     		}
     		$_POST['canceler']=0;
     	}
     	else
     	{
     		if($_POST['find_preplan'] > 0 && $_POST['find_driver_id'] > 0)
          	{
          		//$dispatch_section="<br><br><span class='alert'>Preplaned Load under construction...</span><br>";
          		$mres=mrr_send_peoplenet_complete_preplan_load($_POST['find_load_id'],$_POST['find_driver_id'],$_POST['find_truck_id'],$_POST['run_preplan']);
          		$_POST['truck_id']=$_POST['find_truck_id'];
               	$_POST['truck_id2']=$_POST['find_truck_id'];
               	$_POST['run_preplan']=$mres['dispatch_id'];
               	
               	if($auto_run > 0)
               	{
               		//$java_run_list.="//preplan auto-run section";
               		
               		$disp_cntr=$mres['dispatch_cntr'];
     				$disp_arr=$mres['disp_arr'];
     				$disp_pre=$mres['disp_pre'];
               		for($pp=0; $pp < $disp_cntr; $pp++)
     				{	
     					//$java_run_list.="//preplan ".$pp."";
     					$java_run_list.="
     						mrr_run_preplan(".$disp_arr[$pp].");
     					";		//each is a full load
     				}
               	}
               	
               	//get output for the dispatch section.
               	$dispatch_section = $mres['output'];          	
          	}
          	else
          	{               	
               	$subjectx="";
               	$messagex="";
               	$test_fire=0;
               	//if($_SESSION['user_id']==23)		$test_fire=1;
               	
               	$mres=mrr_send_peoplenet_complete_dispatch($_POST['find_load_id'],$_POST['run_dispatch'],$_POST['find_truck_id']);
               	//assign form elements from this load
               	$_POST['truck_id']=$mres['truck_id'];
               	$_POST['truck_id2']=$mres['truck_id'];
               	$_POST['run_dispatch']=$mres['dispatch_id'];
               	
               	$subjectx.="PN AutoFlow TEST | Load ".$_POST['find_load_id']." Dispatch ".$_POST['run_dispatch']." to truck ".$_POST['find_truck_id'].".";
               	$messagex.="<br>Load ".$_POST['find_load_id']." Dispatch ".$_POST['run_dispatch'].":<br>";
               	
               	if($auto_run > 0)
               	{
               		//$java_run_list.="//dispatch auto-run section";
               		
               		$disp_cntr=$mres['dispatch_cntr'];
     				$disp_arr=$mres['disp_arr'];
     				$disp_pre=$mres['disp_pre'];
     				for($pp=0; $pp < $disp_cntr; $pp++)
     				{
     					//$java_run_list.="//dispatch ".$pp."";
     					$java_run_list.="
     						mrr_run_dispatch(".$disp_arr[$pp].");
     					";		//each is a dispatch..possible several per load
     					
     				}
     				
     				$messagex.="<br>*** P.S.: Autorun Set!.<br>";
     			}
               	//get output for the dispatch section.
               	$dispatch_section = $mres['output'];
               	
               	
               	$messagex.="---Dispatch Sent. Results:<br>".$dispatch_section."<br>Automatic message...do not reply.";		//var_dump($mres)
               	if($test_fire > 0)
               	{               	
               		@ $sent=mail($mrrto, $subjectx, $messagex, "");
               	}
          	}	
     	}
     }
     
     $edater=date("m/d/Y H:i:s");
	$diff_timer.="1. Point is ".(strtotime($edater) - strtotime($sdater)) ." seconds<br>";
	
     //normal interface...	
	if(isset($_GET['service_type']))		$_POST['service_type']=$_GET['service_type'];
	if(isset($_GET['truck_id']))			$_POST['truck_id']=$_GET['truck_id'];	
	if(isset($_GET['emessage']))			$_POST['emessage']=$_GET['emessage'];
	
	if(isset($_GET['dispatch_id']))		$_POST['dispatch_id']=$_GET['dispatch_id'];
	
	if(isset($_GET['truck_id2']))			$_POST['truck_id2']=$_GET['truck_id2'];
	if(isset($_GET['date_from']))			$_POST['date_from']=$_GET['date_from'];	
	if(isset($_GET['date_to']))			$_POST['date_to']=$_GET['date_to'];
	if(isset($_GET['packet_num']))		$_POST['packet_num']=$_GET['packet_num'];	
	if(isset($_GET['msg_packet_num']))		$_POST['msg_packet_num']=$_GET['msg_packet_num'];
	
	if(isset($_GET['service_type']) && isset($_GET['truck_id']))		$_POST['track_it']=1;	//submit the form any way
	
	
	if(!isset($_POST['emessage']))		$_POST['emessage']="";
	
	if(!isset($_POST['service_type']))		$_POST['service_type']="";
	$serve_box=mrr_peoplenet_service_selector("service_type",$_POST['service_type']);
	
	if(!isset($_POST['truck_id']))		$_POST['truck_id']=0;
	if(!isset($_POST['truck_id2']))		$_POST['truck_id2']=$_POST['truck_id'];
	
	if(!isset($_POST['dispatch_id']))		$_POST['dispatch_id']=0;
	
	if(!isset($_POST['date_from']))		$_POST['date_from']=date("m/d/Y");
	if(!isset($_POST['date_to']))			$_POST['date_to']=date("m/d/Y");
	
	if(!isset($_POST['report_type']))		$_POST['report_type']=0;
	
	if(!isset($_POST['packet_num']))		$_POST['packet_num']=0;
	if(!isset($_POST['msg_packet_num']))	$_POST['msg_packet_num']=0;
	
	$_POST['packet_num']=$max_packet;
	$_POST['msg_packet_num']=$max_msg_packet;
	
	$_POST['packet_num']=(int)$_POST['packet_num'];
	$_POST['msg_packet_num']=(int)$_POST['msg_packet_num'];
		
	$truck_box=mrr_peoplenet_truck_selector("truck_id",$_POST['truck_id']);
	$truck_box2=mrr_peoplenet_truck_selector("truck_id2",$_POST['truck_id2']);
	
	//$dispatch_box=mrr_peoplenet_truck_dispatch_selector('dispatch_id',$_POST['dispatch_id']);
	
	$truck_map=mrr_map_generator($_POST['truck_id']);
	
	$truckname="";
	if(isset($_POST['track_it']))
	{		
		
		if($_POST['truck_id']==0 && $_POST['service_type']=="imessage_send")
		{	//send messages to all trucks
			$tres=mrr_peoplenet_truck_array();
			$truck_cnt=$tres['num'];
			$truck_arr=$tres['arr'];
			$truck_namers=$tres['names'];
			
			for($t=0;$t < $truck_cnt; $t++)
			{
				$tid=$truck_arr[$t];
				$tname=$truck_namers[$t];
				$serve_output=mrr_peoplenet_find_data($_POST['service_type'],$tid,$_POST['dispatch_id'],$_POST['emessage'],$_POST['packet_num'],$_POST['msg_packet_num']);	
			}
			$serve_output="<div class='mrr_maint_recur'>Message sent to all ".$truck_cnt." PeopleNet-tracked trucks.</div>";
			$mrr_message="<div class='mrr_maint_recur'>Message sent to all ".$truck_cnt." PeopleNet-tracked trucks.</div>";
		}
		else
		{
			$serve_output=mrr_peoplenet_find_data($_POST['service_type'],$_POST['truck_id'],$_POST['dispatch_id'],$_POST['emessage'],$_POST['packet_num'],$_POST['msg_packet_num']);	
			if($_POST['service_type']=="imessage_send")
			{
				$mrr_message="<div class='mrr_maint_recur'>Message has been sent.</div>";
				$_POST['emessage']="";
			}
		}
		
		if($_POST['truck_id'] > 0)
		{
			$sql = "
     			select name_truck 
     			from trucks 
     			where id='".sql_friendly($_POST['truck_id'])."'
     		";
     		$data=simple_query($sql);
			if($row=mysqli_fetch_array($data))
			{
				$truckname="Displaying Truck ".trim($row['name_truck'])."";	
			}
			
			//test truck			
               //if($_POST['truck_id']=="1520428")		$truckname="Displaying Truck 1520428";				
		}
		else
		{
			$truckname="";		
		}				
	}	
	
	
	
	$marker_image="images/2012/mrr_truck.png";	//images/truck_info.png
	$mrr_map_points="";
	$mrr_map_bounds="";
	$map_object="special_map";
	$home_lat="36.001156";
	$home_long="-86.597328";		
	$zoom_level=5;
	
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
		
	$speed_col1="purple";
	$speed_col2="black";
	$speed_col3="orange";
	$speed_col4="green";
	$speed_col5="red";
	$speed_ledger="
		<table class='admin_menu1' width='100%'>
		<tr>
			<td valign='top'><b>Tracking Speed Ledger:</b></td>
			<td valign='top' align='right' width='200'><span style='color:".$speed_col1."; font-weight:bold;'>No Speed Reading</span></td>
			<td valign='top' align='right' width='200'><span style='color:".$speed_col2."; font-weight:bold;'>No Movement (Stopped)</span></td>
			<td valign='top' align='right' width='200'><span style='color:".$speed_col3."; font-weight:bold;'>0 - 30 MPH (Slow)</span></td>
			<td valign='top' align='right' width='200'><span style='color:".$speed_col4."; font-weight:bold;'>31 - 70 MPH (Good)</span></td>
			<td valign='top' align='right' width='200'><span style='color:".$speed_col5."; font-weight:bold;'>Over 70 MPH (Speeding?)</span></td>
		</tr>
		</table>
	";
		
	$new_style_path="images/2012/";				//?truck_id=".$_POST['truck_id']."&service_type=loc_overview
	$truck_lister="<table class='admin_menu1' width='100%'>
				<tr>
					<td valign='top' align='right' colspan='16'>".$speed_ledger."</td>
				</tr>
				<tr>
					<td valign='top' nowrap><a href='peoplenet_interface.php'><b>Update All</b></a></td>
					<td valign='top'><b>Date and Time</b></td>
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
					<td valign='top'><b>Load and Dispatch</b></td>
					<td valign='top' align='right'><b>Speed</b></td>
					<td valign='top' align='right'><b>Dir</b></td>
					<td valign='top' align='right'><b>Quality</b></td>
					<td valign='top' align='right'><b>Latitude</b></td>
					<td valign='top' align='right'><b>Longitude</b></td>
					<td valign='top'><b>Location</b></td>
					<td valign='top'><b>Fix</b></td>
					<td valign='top'><b>Ignition</b></td>
					<td valign='top' align='right'><b>Odometer</b></td>
					<td valign='top' align='right'><b>Rolling Odom</b></td>
					<td valign='top' align='right'><b>Odom</b></td>
					<td valign='top' align='right'><b>Fuel</b></td>
					<td valign='top' align='right'><b>Speed </b></td>
					<td valign='top' align='right'><b>Idle</b></td>
				</tr>
	";
	$truck_cnt=0;
	
     $edater=date("m/d/Y H:i:s");
	$diff_timer.="2. Point is ".(strtotime($edater) - strtotime($sdater)) ." seconds<br>";
	
	$mrr_adder_prime="";
	if($_POST['find_truck_id'] > 0)	$mrr_adder_prime=" and trucks.id='".sql_friendly($_POST['find_truck_id']) ."'";
	
	if($_POST['find_truck_id'] == 0 && $_POST['truck_id'] > 0)	$mrr_adder_prime=" and trucks.id='".sql_friendly($_POST['truck_id']) ."'";
	
	$sql = "
		select trucks.*
		from trucks
		where trucks.deleted = 0
			and trucks.peoplenet_tracking=1
			".$mrr_adder_prime."
		order by trucks.name_truck asc
	";		
	$data = simple_query($sql);
	$mn=mysqli_num_rows($data);
	
	$cntrx=0;
	
	while($row = mysqli_fetch_array($data))
	{
		$tdater=date("m/d/Y H:i:s");
		
		$alink="<a href='peoplenet_interface.php?truck_id=".$row['id']."'>".$row['name_truck']."</a>";	//&service_type=loc_onetruck
		$tres=mrr_find_only_location_of_this_truck($row['id']);
		$cur_long=$tres['longitude'];
		$cur_lat=$tres['latitude'];
		$cur_location=$tres['location'];
		$temp_page=$tres['temp_page'];
		$gps_location=$tres['gps_location'];
		
		
		if(strlen($gps_location)> strlen($cur_location))		$cur_location="".trim($gps_location)."";
		
		$blink="No Data Found";
		$tcntr=0;
		
     	$sql2 = "
     		select distinct(truck_tracking.linedate) as unique_stamp,
     			truck_tracking.*
     		from ".mrr_find_log_database_name()."truck_tracking
     		where truck_tracking.truck_id='".sql_friendly($row['id']) ."'
     		group by truck_tracking.linedate desc
     		limit 10
     	";     	//	//group by truck_tracking.linedate desc
		$data2 = simple_query($sql2);
		$mn2=mysqli_num_rows($data2);		
		while($row2 = mysqli_fetch_array($data2))
		{
			$add_action="&nbsp; &nbsp;";			
			$add_class="";
			$use_truck_name="";
			
			if($tcntr==0)			
			{
				$use_truck_name=$alink;
				$add_class=" always_display_rows";
				$add_action="
						<span class='mrr_link_like_on' onClick='mrr_show_these_rows(".$row['id'].");'><img src='".$new_style_path."plus.png' alt='+' width='15' height='15' border='0' title='Show'></span> 
						<span class='mrr_link_like_on' onClick='mrr_hide_these_rows(".$row['id'].");'><img src='".$new_style_path."grid-minus.jpg' alt='-' width='15' height='15' border='0' title='Hide'></span> 
				";
				
				//plot map point...add to javascript below.
				$extra_tag="";
				if($row2['latitude']==$home_lat && $row2['longitude']==$home_long)		$extra_tag="Home Sweet Home ... and ";
								
				$extra_tag=str_replace("'","",$extra_tag);
				$tlocal=str_replace("'","",$row2['location']);
				$ttruck=str_replace("'","",$row['name_truck']);
				
				$truck_cnt++;																	// icon: marker_image,
				$mrr_map_points.="					
					var pose".$truck_cnt."  = new google.maps.LatLng(".$row2['latitude'].",".$row2['longitude'].");
					var truck".$truck_cnt." = new google.maps.Marker({ position: pose".$truck_cnt.",  map: map, icon: 'http://www.googlemapsmarkers.com/v1/".$truck_cnt."/00CC00/FFFFFF/0000FF/',  title: '".$extra_tag."Truck ".$ttruck.": ".$tlocal."' });
				";	//".$row2['latitude'].",".$row2['longitude']."
				if($truck_cnt==1)	$mrr_map_bounds.="truck".$truck_cnt."";
				else				$mrr_map_bounds.=",truck".$truck_cnt."";
				
			}
			else					
			{
				$add_class=" hide_extra_rows";	
			}
			
			
			
			if($row2['linedate']!="0000-00-00 00:00:00")		$blink="".date("m/d/Y H:i:s",strtotime($row2['linedate']))."";
			
			$cmode=$speed_col1;
			if($row2['truck_speed']==0)									$cmode=$speed_col2;			
			elseif($row2['truck_speed'] > 0 && $row2['truck_speed'] <=30)		$cmode=$speed_col3;	//; text-decoration:blink
			elseif($row2['truck_speed'] > 30 && $row2['truck_speed'] <=70)		$cmode=$speed_col4;
			elseif($row2['truck_speed'] > 70)								$cmode=$speed_col5;	//; text-decoration:blink
						
			$mrr_res=mrr_find_truck_driver_by_date($row['id'],$row2['linedate']);	
			
			
			$truck_lister.="<tr class='truck_".$row['id']."".$add_class."'>
     						<td valign='top'>".$add_action."".$use_truck_name."<br><img src='http://www.googlemapsmarkers.com/v1/".$truck_cnt."/00CC00/FFFFFF/0000FF/' alt='".$truck_cnt."'></td>
     						<td valign='top'>".$blink."</td>
     						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".$row2['truck_speed']."</b></span></td>
     						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".decode_heading($row2['truck_heading'])."</b></span><div class='mrr_current_truck_local_list'>CUR</div></td>
     						<td valign='top' align='right'>".$row2['gps_quality']."<div class='mrr_current_truck_local_list'>LOC</div></td>
     						<td valign='top' align='right'>".$row2['latitude']."<div class='mrr_current_truck_local_list'>".$cur_lat."</div></td>
     						<td valign='top' align='right'>".$row2['longitude']."<div class='mrr_current_truck_local_list'>".$cur_long."</div></td>
     						<td valign='top'> <span style='color:".$cmode.";'><b>".$row2['location']."</b></span><div class='mrr_current_truck_local_list'>".$cur_location."</div></td>
     						<td valign='top'>".decode_fix($row2['fix_type'])."</td>
     						<td valign='top'>".decode_ignition($row2['ignition'])."</td>
     						<td valign='top' align='right'>".$row2['gps_odometer']."</td>
     						<td valign='top' align='right'>".$row2['gps_rolling_odometer']."</td>
     						<td valign='top' align='right'>".$row2['performx_odometer']."</td>
     						<td valign='top' align='right'>".$row2['performx_fuel']."</td>
     						<td valign='top' align='right'>".$row2['performx_speed']."</td>
     						<td valign='top' align='right'>".$row2['performx_idle']."</td>
						<tr>";
								//<br>===".$temp_page."===
				
				
			$travel_plan="";
			if($mrr_res['dispatch_id'] > 0)		$travel_plan="Delivery:".$mrr_res['origin'].", ".$mrr_res['origin_state']." to ".$mrr_res['destination'].", ".$mrr_res['destination_state']."";	
			
			if($mrr_res['load_id'] > 0 && $mrr_res['dispatch_id'] > 0)
			{	
     			$truck_lister.="<tr class='truck_".$row['id']."".$add_class."'>
          						<td valign='top'></td>
          						<td valign='top'>
          								<a href='manage_load.php?load_id=".$mrr_res['load_id']."' target='_blank'>(".$mrr_res['load_id'].")</a> 
          								<a href='add_entry_truck.php?load_id=".$mrr_res['load_id']."&id=".$mrr_res['dispatch_id']."' target='_blank'>".$mrr_res['dispatch_id']."</a>
          						</td>
          						<td valign='top' align='right'><span style='color:".$cmode.";'><b><a href='admin_trailers.php?id=".$mrr_res['trailer_id']."' target='_blank'>".$mrr_res['trailer_name']."</a></b></span></td>
          						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".$mrr_res['trailer']."</b></span></td>
          						<td valign='top' align='right'></td>
          						<td valign='top' align='right'><a href='admin_drivers.php?id=".$mrr_res['driver_id']."' target='_blank'>".$mrr_res['driver_name']."</a></td>
          						<td valign='top' align='right'><a href='admin_drivers.php?id=".$mrr_res['driver2_id']."' target='_blank'>".$mrr_res['driver2_name']."</a></td>
          						<td valign='top'> <span style='color:".$cmode.";'><b>".$travel_plan."</b></span></td>
          						<td valign='top'></td>
          						<td valign='top'></td>
          						<td valign='top' align='right'></td>
          						<td valign='top' align='right'></td>
          						<td valign='top' align='right'></td>
          						<td valign='top' align='right'></td>
          						<td valign='top' align='right'></td>
          						<td valign='top' align='right'></td>
     						<tr>";			
			}			
			$tcntr++;			
		}
		
		$cntrx++;
		$edater=date("m/d/Y H:i:s");
		$diff_timer.="3A [".$cntrx."][".$row['name_truck']."]. Point is ".(strtotime($edater) - strtotime($tdater)) ." seconds<br>";
		
		if($mn2==0)
		{
			$truck_lister.="<tr class='truck_".$row['id']."'>
     						<td valign='top'>".$alink."</td>
     						<td valign='top'>".$blink."</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
						<tr>";	
		}
		else
		{
			$truck_lister.="<tr class='truck_".$row['id']." hide_extra_rows'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='14'><hr></td>
     						<td valign='top' align='right'>&nbsp;</td>
						<tr>";
		}
		
		//messages pulled from packets
		$mcntr=0; 
		$sql3 = "
     		select truck_tracking_msg_history.*
     		from ".mrr_find_log_database_name()."truck_tracking_msg_history
     		where truck_id='".sql_friendly($row['id']) ."'
     		order by linedate_created desc
     					
     	";	//limit 10	
		$data3 = simple_query($sql3);
		$mn3=mysqli_num_rows($data3);	
		if($mn3>0)
		{
			$truck_lister.="<tr class='truck_".$row['id']." hide_extra_rows'>
     						<td valign='top'></td>
     						<td valign='top'><b>Created</b></td>
     						<td valign='top'><b>Received</b></td>
     						<td valign='top'><b>Recipient</b></td>
     						<td valign='top' colspan='12'><b>Message</b></td>
						<tr>";	
		}		
		while($row3 = mysqli_fetch_array($data3) && $mcntr < 10)
		{
			
			$truck_lister.="<tr class='truck_".$row['id']." hide_extra_rows'>
     						<td valign='top'></td>
     						<td valign='top'>".date("m/d/Y H:i:s",strtotime($row3['linedate_created']))."</td>
     						<td valign='top'>".date("m/d/Y H:i:s",strtotime($row3['linedate_received']))."</td>
     						<td valign='top'>".$row3['recipient_name']."</td>
     						<td valign='top' colspan='12'>".$row3['msg_text']."</td>
						<tr>";
			$mcntr++;
		}
		if($mn3>0)
		{
			$truck_lister.="<tr class='truck_".$row['id']." hide_extra_rows'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='14'><hr></td>
     						<td valign='top' align='right'>&nbsp;</td>
						<tr>";
		}	
		
		//send and stored messages from Peoplenet Interface
		$mcntr=0; 
		$sql3 = "
     		select truck_tracking_messages.*,
     			(select username from users where users.id=truck_tracking_messages.user_id) as username
     		from ".mrr_find_log_database_name()."truck_tracking_messages
     		where truck_tracking_messages.truck_id='".sql_friendly($row['id']) ."'
     		order by truck_tracking_messages.linedate desc
     		
     	";	//limit 10
		$data3 = simple_query($sql3);
		$mn3=mysqli_num_rows($data3);		
		while($row3 = mysqli_fetch_array($data3) && $mcntr < 10)
		{
			$clink="";
			if($row3['linedate']!="0000-00-00 00:00:00")		$clink="<span title='Message ID is ".$row3['id']."'>".date("m/d/Y H:i:s",strtotime($row3['linedate']))."</span>";
			$dlink="";
			if($row3['user_id'] > 0)						$dlink="<a href='admin_users.php?eid=".$row3['user_id']."' target='_blank'>".$row3['username']."</a>";
			
			$truck_lister.="<tr class='truck_".$row['id']." hide_extra_rows'>
     						<td valign='top'></td>
     						<td valign='top'>".$clink."</td>
     						<td valign='top' align='right'>Message</td>
     						<td valign='top' align='right'>Sent by</td>
     						<td valign='top' align='right'>".$dlink."</td>
     						<td valign='top' colspan='11'>".$row3['message']."</td>
						<tr>";
			$mcntr++;
		}
		if($mn3>0)
		{
			$truck_lister.="<tr class='truck_".$row['id']." hide_extra_rows'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='14'><hr></td>
     						<td valign='top' align='right'>&nbsp;</td>
						<tr>";
		}		
		
	}
	
     $edater=date("m/d/Y H:i:s");
	$diff_timer.="3. Point is ".(strtotime($edater) - strtotime($sdater)) ." seconds<br>";
	
	
	
	$test_truck=0;	//1520428
		
	if($test_truck>0)
	{
		$alink="<a href='peoplenet_interface.php?truck_id=".$test_truck."'>".$test_truck."</a>";	//&service_type=loc_onetruck
		$blink="No Data Found";
		$tcntr=0;
		
     	$sql2 = "
     		select distinct(truck_tracking.linedate) as unique_stamp,
     			truck_tracking.*
     		from ".mrr_find_log_database_name()."truck_tracking
     		where truck_tracking.truck_id='".sql_friendly($test_truck) ."'
     		group by truck_tracking.linedate desc
     		limit 10
     	";
		$data2 = simple_query($sql2);
		$mn2=mysqli_num_rows($data2);		
		while($row2 = mysqli_fetch_array($data2))
		{
			$add_action="&nbsp; &nbsp;";			
			$add_class="";
			$use_truck_name="";
			
			if($tcntr==0)			
			{
				$use_truck_name=$alink;
				$add_class=" always_display_rows";
				$add_action="
						<span class='mrr_link_like_on' onClick='mrr_show_these_rows(".$test_truck.");'><img src='".$new_style_path."plus.png' alt='+' width='15' height='15' border='0' title='Show'></span> 
						<span class='mrr_link_like_on' onClick='mrr_hide_these_rows(".$test_truck.");'><img src='".$new_style_path."grid-minus.jpg' alt='-' width='15' height='15' border='0' title='Hide'></span> 
				";
				
				//plot map point...add to javascript below.
				$extra_tag="";
				if($row2['latitude']==$home_lat && $row2['longitude']==$home_long)		$extra_tag="Home Sweet Home ... and ";
				
				$extra_tag=str_replace("'","",$extra_tag);
				$tlocal=str_replace("'","",$row2['location']);
				$ttruck=str_replace("'","",$test_truck);
				
				$truck_cnt++;																	// icon: marker_image,
				$mrr_map_points.="					
					var pose".$truck_cnt."  = new google.maps.LatLng(".$row2['latitude'].",".$row2['longitude'].");
					var truck".$truck_cnt." = new google.maps.Marker({ position: pose".$truck_cnt.",    map: map, icon: 'http://www.googlemapsmarkers.com/v1/".$truck_cnt."/00CC00/FFFFFF/0000FF/',  title: '".$extra_tag."Truck ".$ttruck.": ".$tlocal."' });
				";
				if($truck_cnt==1)	$mrr_map_bounds.="truck".$truck_cnt."";
				else				$mrr_map_bounds.=",truck".$truck_cnt."";
				
			}
			else					
			{
				$add_class=" hide_extra_rows";	
			}
			
			if($row2['linedate']!="0000-00-00 00:00:00")		$blink="".date("m/d/Y H:i:s",strtotime($row2['linedate']))."";
			
			$cmode=$speed_col1;
			if($row2['truck_speed']==0)									$cmode=$speed_col2;			
			elseif($row2['truck_speed'] > 0 && $row2['truck_speed'] <=30)		$cmode=$speed_col3;	//; text-decoration:blink
			elseif($row2['truck_speed'] > 30 && $row2['truck_speed'] <=70)		$cmode=$speed_col4;
			elseif($row2['truck_speed'] > 70)								$cmode=$speed_col5;	//; text-decoration:blink
			
			$truck_lister.="<tr class='truck_".$test_truck."".$add_class."'>
     						<td valign='top'>".$add_action."".$use_truck_name."<br><img src='http://www.googlemapsmarkers.com/v1/".$truck_cnt."/00CC00/FFFFFF/0000FF/' alt='".$truck_cnt."'></td>
     						<td valign='top'>".$blink."</td>
     						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".$row2['truck_speed']."</b></span></td>
     						<td valign='top' align='right'><span style='color:".$cmode.";'><b>".decode_heading($row2['truck_heading'])."</b></span></td>
     						<td valign='top' align='right'>".$row2['gps_quality']."</td>
     						<td valign='top' align='right'>".$row2['latitude']."</td>
     						<td valign='top' align='right'>".$row2['longitude']."</td>
     						<td valign='top'> <span style='color:".$cmode.";'><b>".$row2['location']."</b></span></td>
     						<td valign='top'>".decode_fix($row2['fix_type'])."</td>
     						<td valign='top'>".decode_ignition($row2['ignition'])."</td>
     						<td valign='top' align='right'>".$row2['gps_odometer']."</td>
     						<td valign='top' align='right'>".$row2['gps_rolling_odometer']."</td>
     						<td valign='top' align='right'>".$row2['performx_odometer']."</td>
     						<td valign='top' align='right'>".$row2['performx_fuel']."</td>
     						<td valign='top' align='right'>".$row2['performx_speed']."</td>
     						<td valign='top' align='right'>".$row2['performx_idle']."</td>
						<tr>";
			$tcntr++;				
		}
		
		if($mn2==0)
		{
			$truck_lister.="<tr class='truck_".$test_truck."'>
     						<td valign='top'>".$alink."</td>
     						<td valign='top'>".$blink."</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
     						<td valign='top' align='right'>&nbsp;</td>
						<tr>";	
		}
		else
		{
			$truck_lister.="<tr class='truck_".$test_truck." hide_extra_rows'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='14'><hr></td>
     						<td valign='top' align='right'>&nbsp;</td>
						<tr>";
		}
		
		//messages pulled from packets
		$mcntr=0; 
		$sql3 = "
     		select truck_tracking_msg_history.*
     		from ".mrr_find_log_database_name()."truck_tracking_msg_history
     		where truck_id='".sql_friendly($test_truck) ."'
     		order by linedate_created desc
     		limit 10				
     	";
		$data3 = simple_query($sql3);
		$mn3=mysqli_num_rows($data3);	
		if($mn3>0)
		{
			$truck_lister.="<tr class='truck_".$test_truck." hide_extra_rows'>
     						<td valign='top'></td>
     						<td valign='top'><b>Created</b></td>
     						<td valign='top'><b>Received</b></td>
     						<td valign='top'><b>Recipient</b></td>
     						<td valign='top' colspan='12'><b>Message</b></td>
						<tr>";	
		}		
		while($row3 = mysqli_fetch_array($data3))
		{
			
			$truck_lister.="<tr class='truck_".$test_truck." hide_extra_rows'>
     						<td valign='top'></td>
     						<td valign='top'>".date("m/d/Y H:i:s",strtotime($row3['linedate_created']))."</td>
     						<td valign='top'>".date("m/d/Y H:i:s",strtotime($row3['linedate_received']))."</td>
     						<td valign='top'>".$row3['recipient_name']."</td>
     						<td valign='top' colspan='12'>".$row3['msg_text']."</td>
						<tr>";
			$mcntr++;
		}
		if($mn3>0)
		{
			$truck_lister.="<tr class='truck_".$test_truck." hide_extra_rows'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='14'><hr></td>
     						<td valign='top' align='right'>&nbsp;</td>
						<tr>";
		}	
		
		//send and stored messages from Peoplenet Interface
		$mcntr=0; 
		$sql3 = "
     		select truck_tracking_messages.*,
     			users.username
     		from ".mrr_find_log_database_name()."truck_tracking_messages,users
     		where truck_tracking_messages.truck_id='".sql_friendly($test_truck) ."'
     			and truck_tracking_messages.user_id=users.id
     		order by truck_tracking_messages.linedate desc
     		limit 10
     	";
		$data3 = simple_query($sql3);
		$mn3=mysqli_num_rows($data3);			
		while($row3 = mysqli_fetch_array($data3))
		{
			$clink="";
			if($row3['linedate']!="0000-00-00 00:00:00")		$clink="<span title='Message ID is ".$row3['id']."'>".date("m/d/Y H:i:s",strtotime($row3['linedate']))."</span>";
			$dlink="";
			if($row3['user_id'] > 0)						$dlink="<a href='admin_users.php?eid=".$row3['user_id']."' target='_blank'>".$row3['username']."</a>";
			
			$truck_lister.="<tr class='truck_".$test_truck." hide_extra_rows'>
     						<td valign='top'></td>
     						<td valign='top'>".$clink."</td>
     						<td valign='top' align='right'>Message</td>
     						<td valign='top' align='right'>Sent by</td>
     						<td valign='top' align='right'>".$dlink."</td>
     						<td valign='top' colspan='11'>".$row3['message']."</td>
						<tr>";
			$mcntr++;
		}
		if($mn3>0)
		{
			$truck_lister.="<tr class='truck_".$test_truck." hide_extra_rows'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='14'><hr></td>
     						<td valign='top' align='right'>&nbsp;</td>
						<tr>";
		}		
		$mn++;			
	}
	
     $edater=date("m/d/Y H:i:s");
	$diff_timer.="4. Point is ".(strtotime($edater) - strtotime($sdater)) ." seconds<br>";
	
	
	if($mn==0)
	{
		$truck_lister.="<tr>
     					<td valign='top' colspan='16'><center>No Trucks Found with Tracking turned on.</center></td> 
					<tr>";	
	}
	
	$truck_lister.="</table>";
	
	$truck_reporter="";
	
	if(!isset($_POST['canned_message_id']))		$_POST['canned_message_id']=0;
	$canned_messages=mrr_peoplenet_select_canned_message('canned_message_id',$_POST['canned_message_id'],320);
?>
<? include('header.php') ?>
<?
	//echo "<br><br>Page Load speed check: <br>".$diff_timer."<br>"; 
?>
<form name='peoplenet_dipatcher' action='<?= $SCRIPT_NAME ?>' method='post'>
	<?= $dispatch_section ?>
<table class='admin_menu2' style='width:1600px'>
<tr>
	<td valign='top' colspan='5'>	
		<div id='special_map' style="width:1500px; height:800px; margin-top:10px; margin-bottom:10px;"></div>
	</td>
</tr>
<tr>
	<td valign='top' colspan='2'>	
		<div class='section_heading'>PeopleNet Interface</div>
	</td>
	<td valign='top' align='right'>	
		<input type='submit' name='refresh_it' id='refresh_it' value='Refresh Page'>
	</td>
	<td valign='top' colspan='2' rowspan='7'>			
		<div style='margin-top:15px; width:1000px;'>
			<? if(isset($_GET['auto_run']) || $map_bypass > 0) { ?>
			
			<? } else { ?>
				<iframe width='1000' height='400' id='map_frame' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='<?= $truck_map ?>' style='color:#0000FF;text-align:left;border:1px black solid'></iframe>
				<div id='frame_caption' style='text-align:center; font-weight:bold;'><?= $truckname ?></div>
			<? } ?>
		</div>
	</td>
</tr>
<tr>
	<td valign='top'>	
		Service Type  <?= show_help('peoplenet_interface.php','Service Type') ?>
	</td>
	<td valign='top' colspan='2'>	
		<?= $serve_box ?>
	</td>
</tr>
<tr>
	<td valign='top'>	
		Truck  <?= show_help('peoplenet_interface.php','Truck Select') ?>
	</td>
	<td valign='top' colspan='2'>	
		<?= $truck_box ?>
	</td>	
</tr>
<tr>
	<td valign='top'>	
		For Message  <?= show_help('peoplenet_interface.php','Message Text') ?>
	</td>
	<td valign='top' colspan='2'>	
		<?= $canned_messages ?> <input type='button' name='insert_canned_msg' id='insert_canned_msg' value='Add Text' onClick='insert_canned_message();'>  <?= show_help('peoplenet_interface.php','Canned Message') ?>	
		<br>
		<textarea name='emessage' id='emessage' rows='8' cols='50' wrap='virtual'><?= $_POST['emessage'] ?></textarea>
	</td>
</tr>
<tr>
	<td valign='top'>	
		&nbsp;
	</td>
	<td valign='top' colspan='2'>	
		<?= $mrr_message  ?>
	</td>
</tr>
<tr>
	<td valign='top'>	
		Packet  <?= show_help('peoplenet_interface.php','Get Next History Packet') ?>
	</td>
	<td valign='top'>			
		 <input type='text' name='packet_num' id='packet_num' value='<?= (int)$_POST['packet_num'] ?>' size='5' style='text-align:right;'> Next History
	</td>
	<td valign='top' align='right'>			
		 &nbsp;
	</td>
</tr>
<tr>
	<td valign='top'>	
		Message Packet  <?= show_help('peoplenet_interface.php','Get Next Message History Packet') ?>
	</td>
	<td valign='top'>			
		 <input type='text' name='msg_packet_num' id='msg_packet_num' value='<?= (int)$_POST['msg_packet_num'] ?>' size='5' style='text-align:right;'> Next Message Pack
	</td>
	<td valign='top' align='right'>			
		 <input type='submit' name='track_it' id='track_it' value='Track/Send'>
		 <input type='hidden' name='canceler' id='canceler' value='0'>
	</td>  
</tr>
<tr>
	<td valign='top' colspan='5'>		
		<?= $serve_output ?>
	</td>
</tr>
<tr>
	<td valign='top' colspan='5'>		
		&nbsp;
	</td>
</tr>
<tr>
	<td valign='top' colspan='5'>		
		<?= $truck_lister ?>
	</td>
</tr>
<tr>
	<td valign='top' colspan='5'>			
		<br>
		<div class='section_heading'>PeopleNet Tracking Report</div>
		<table class='admin_menu2' width='100%'>
			<tr>
				<td valign='top'>Select Truck <?= show_help('peoplenet_interface.php','Select Report truck') ?></td>
				<td valign='top'><?= $truck_box2 ?></td>
				<td valign='top'>Date From<?= show_help('peoplenet_interface.php','Date from for polled date') ?></td>
				<td valign='top'><input type='text' id='date_from' name='date_from' value='<?= $_POST['date_from'] ?>'></td>
				<td valign='top'>Date To<?= show_help('peoplenet_interface.php','Date from for polled date') ?></td>
				<td valign='top'><input type='text' id='date_to' name='date_to' value='<?= $_POST['date_to'] ?>'></td>
				<td valign='top'>Report Type<?= show_help('peoplenet_interface.php','Report Type') ?></td>
				<td valign='top'>
					<select id='report_type' name='report_type'>
						<option value='0'<?= ( $_POST['report_type']==0 ? " selected" : "" ) ?>>All Data</option>
						<option value='1'<?= ( $_POST['report_type']==1 ? " selected" : "" ) ?>>Location Stats</option>
						<option value='2'<?= ( $_POST['report_type']==2 ? " selected" : "" ) ?>>Message Packets</option>
						<option value='3'<?= ( $_POST['report_type']==3 ? " selected" : "" ) ?>>Sent Messages</option>
						<option value='4'<?= ( $_POST['report_type']==4 ? " selected" : "" ) ?>>All Messages</option>
					</select>
				</td>
				<td valign='top'><input type='button' id='mrr_search' name='mrr_search' value='Search' onClick='mrr_report_truck_tracking();'></td>
			</tr>
		</table>	
		<div id='truck_report'><?= $truck_reporter ?></div>
		<br>
		<br>
		
		<br>
		<br>
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>	
	$().ready(function() 
	{			
		//listing of dispatches (preplanned loads) to auto-run	
		<?= $java_run_list ?>	
	});
	
	//mapping functions....
	function map_initialize() 
	{		
     	<? if(isset($_GET['auto_run']) || $map_bypass > 0) { ?>
          
     	<? } else { ?>	
          	var mapOptions = { center: new google.maps.LatLng(<?= $home_lat ?>, <?= $home_long ?>), zoom: <?= $zoom_level ?>, mapTypeId: <?= $map_type ?> };
             	var map = new google.maps.Map(document.getElementById('<?= $map_object ?>'), mapOptions);
             	
               //var marker_image = '{ path: < ?= $pointer_type ? >, scale: 10 }, draggable: true,'; 
               //var marker_image = '< ?= $marker_image ? >';   
               
             	//blue-ish styles
     		var styleArray = [
                 {
                   featureType: "all",
                   stylers: [
                     { saturation: -80 }
                   ]
                 },{
                   featureType: "road.arterial",
                   elementType: "geometry",
                   stylers: [
                     { hue: "#00ffee" },
                     { saturation: 50 }
                   ]
                 },{
                   featureType: "poi.business",
                   elementType: "labels",
                   stylers: [
                     { visibility: "off" }
                   ]
                 }
               ];
               //default
               var stylex = [
                 {
                   stylers: [
                     { hue: "#00ffe6" },
                     { saturation: -20 }
                   ]
                 },{
                   featureType: "road",
                   elementType: "geometry",
                   stylers: [
                     { lightness: 100 },
                     { visibility: "simplified" }
                   ]
                 },{
                   featureType: "road",
                   elementType: "labels",
                   stylers: [
                     { visibility: "off" }
                   ]
                 }
               ];
               
               //map.setOptions({styles: stylex});
               //map.setOptions({styles: styleArray});                 
     
             	var marker = new google.maps.Marker({ position: map.getCenter(), map: map,	title: 'Home Sweet Home'	});
     		<?= $mrr_map_points ?>
     		var bounds = new google.maps.LatLngBounds(<?= $mrr_map_bounds ?>);
     		map.fitBounds(bounds);	
		<? } ?>
     }
	
	
	function insert_canned_message()
	{
		msgid=$('#canned_message_id').val();
		otxt=$('#emessage').val();
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_fetch_canned_message",
			   data: {
			   		"message_id":msgid
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrrtab=$(xml).find("mrrTab").text();
					if(otxt=="")	
					{
						otxt = mrrtab;
					}
					else
					{
						otxt = otxt + " " + mrrtab;	
					}					
					$('#emessage').val(otxt);
			   }
		});	
	}
	
	//....................
	function mrr_fill_reader(id)
	{
		//		
	}
	function mrr_view_reader(id)
	{
		//
	}
	
	$('#date_from').datepicker();	
	$('#date_to').datepicker();
	
	//$('.tablesorter').tablesorter();
	$().ready(function() 
	{			
		$('.hide_extra_rows').hide();	
		map_initialize();	
	});
	
	function mrr_show_these_rows(id)
	{
		//$('.hide_extra_rows').hide();
		$('.truck_'+id+'').show();			
	}
	function mrr_hide_these_rows(id)
	{
		$('.truck_'+id+'').hide();
		$('.always_display_rows').show();			
	}
	
	function mrr_selected_this_truck(id,truckname)
	{
		$('#truck_id').val(id);		
		$('#frame_caption').html('Displaying Truck '+truckname+'');	
	}
	
	function mrr_report_truck_tracking()
	{
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_truck_tracking_report",
			   data: {"truck_id": $('#truck_id2').val(),
			   		"date_from": $('#date_from').val(),
			   		"date_to":  $('#date_to').val(),
			   		"report_type": $('#report_type').val()
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrrtab=$(xml).find("mrrTab").text();
					$('#truck_report').html(mrrtab);
					
					mrr_map_run();
			   }
		});	
	}	
	function mrr_map_run() 
	{		
		$('#map_frame').attr('src','');  
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_plot_truck_tracking_report",
			   data: {"truck_id": $('#truck_id2').val(),
			   		"date_from": $('#date_from').val(),
			   		"date_to":  $('#date_to').val()
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					map_link=$(xml).find("mrrSRC").text();
					$('#map_frame').attr('src',map_link);
					
					map_namer="Displaying Route for Truck " + $(xml).find("mrrNamer").text()+ ".";
					$('#frame_caption').html(map_namer);
			   }
		});		 
	}	
	
	//load/dispatch functions below
	function mrr_run_dispatch(id)
	{
		$('#run_dispatch').val(id);
		document.peoplenet_dipatcher.submit();
	}
	function mrr_get_dispatch()
	{
		$('#run_dispatch').val('0');
		document.peoplenet_dipatcher.submit();	
	}
	
	//preplan load/dispatch functions below
	function mrr_run_preplan(id)
	{
		$('#run_preplan').val(id);
		document.peoplenet_dipatcher.submit();
	}
	function mrr_get_preplan()
	{
		$('#run_preplan').val('0');
		document.peoplenet_dipatcher.submit();	
	}
	
	//cancel functions
	function mrr_cancel_preplan(id)
	{
		$.prompt("Are you sure you want to cancel the PeopleNet dispatch for this Preplan Load?", 
		{
			buttons: {Yes: true, No: false},
			submit: function (v, m, f) 
			{
				if(v) 
				{
					$('#run_preplan').val(id);
					$('#canceler').val('1');	
					
					document.peoplenet_dipatcher.submit();	
				}
			}
		});			
	}
	function mrr_cancel_dispatch(id)
	{		
		$.prompt("Are you sure you want to cancel the PeopleNet dispatch for this Load Dispatch?", 
		{
			buttons: {Yes: true, No: false},
			submit: function (v, m, f) 
			{
				if(v) 
				{
					$('#run_dispatch').val(id);
					$('#canceler').val('1');					
					
					document.peoplenet_dipatcher.submit();	
				}
			}
		});	
	}
</script>
<? include('footer.php') ?>