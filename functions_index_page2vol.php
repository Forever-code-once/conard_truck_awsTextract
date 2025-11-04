<?
	function mrr_conard_component_time()
	{
		//global $new_style_path;
		//global $defaultsarray;
		$res="";
				
		//$mon=date("m");
		$smon=date("M");
		$day=date("d");
		$year=date("Y");
		$time=date("H:i");
		$time2=date("g:i A");
		$wday=date("D");		
		
		$res.="<div class='left_box top'>";
		$res.=	"<span>".$day."</span>";
		$res.=	"<span>".$time."&nbsp;</span>";
		$res.=	"<small>".$wday."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font style='color:#c56100;'>".$time2."</font><br>".$smon."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$year."</small>";
		$res.="</div>";
		
		return $res;
	}	
	function mrr_conard_component_fuel()
	{
		global $new_style_path;
		global $defaultsarray;
		
		/* get a list of companies that have separate fuel surcharge prices */
		$sql = "
			select customers.fuel_surcharge,
				customers.name_company,
				customers.id,
				(select fuel_surcharge.fuel_surcharge from fuel_surcharge where customer_id = customers.id and fuel_surcharge.range_lower <= $defaultsarray[fuel_surcharge] order by fuel_surcharge desc limit 1 ) as surcharge_list
			
			from customers 
			where customers.deleted = 0
				and customers.active = 1
				and use_fuel_surcharge > 0
			 	
			 having surcharge_list > 0 or customers.fuel_surcharge > 0
			order by name_company
		";
		$data_surcharge = simple_query($sql);
		
		$rval="<br><br><br><br>";	
			
		/*
		$rval = "<span style='font-size:14px; color:white;'><b>Fuel Surcharges:</b></span><br>";
		$rval .= "
					<span style='font-size:12px; color:white;'>NATIONAL AVERAGE</span>	
					<span style='font-size:12px; color:#c56100;'>".$defaultsarray['fuel_surcharge']." ".show_help('index.php','Fuel Surcharge Ntl Avg',$new_style_path,"question_mark.png")."</span><br><br>						
			";	
		*/	
		
		$i = 0;
		while($row_surcharge = mysqli_fetch_array($data_surcharge)) 
		{
			$i++;
			$rval .= "
					<span style='font-size:12px; color:#c56100;'>".($row_surcharge['fuel_surcharge'] > 0 ? $row_surcharge['fuel_surcharge'] : $row_surcharge['surcharge_list'])."</span>	
					<span style='font-size:12px; color:white;'><b>$row_surcharge[name_company]</b></span><br>							
			";			
		}		
		$res="";				
		$res.="<div class='first-bg'>";
		
		$res.=	"<div class='full-surcharge fuel_surcharge'><p>FUEL SURCHARGE NATIONAL AVERAGE</p></div>";
		$res.=	"<div class='surcharge-rate fuel_surcharge'><span style='color:#c56100;'>".$defaultsarray['fuel_surcharge']."</span> ".show_help('index.php','Fuel Surcharge Ntl Avg',$new_style_path,"question_mark.png")."</div>";	
		$res.=	"<div id='fuel_surcharge_holder' onMouseOver='mrr_fuel_surcharge_show(\"".$defaultsarray['fuel_surcharge']."\");' onMouseOut='mrr_fuel_surcharge_hide();'>".$rval."</div>";
		
		$res.="</div>";
					
		return $res;
	}
	function mrr_conard_component_tool()
	{
		//global $new_style_path;
		//global $defaultsarray;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h4>Tools</h4>";
		$res.=	"<div class='tool-list'>";
		$res.=		"<ul>";
		$res.=			"<li><a href='javascript:new_load()'>New Load</a></li>";
		$res.=			"<li><a href='quote.php'>New Quote</a></li>";
		$res.=			"<li><a href='javascript:edit_dropped_trailer(0)'>New Drop Trailer</a></li>";
		$res.=			"<li class='actual'><div id='map_toggle'> Show Map</div></li>";
		$res.=		"</ul>";
		$res.=		"<div id='map_holder' style='display:none; z-index:1000;'>";
		$res.=			"<img src='images/timezone_map.gif'>";
		$res.=		"</div>";			
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
	}
	
	function mrr_conard_component_im_msgs()
	{		
		/* get the user list */
		$sql = "
			select *
			
			from users
			where deleted = '0'
				and active='1'
			order by username asc
		";	//name_last, name_first
		$data_users = simple_query($sql);
		
		$selbx1="<select name='mrr_dispatch_im_msg_to' id='mrr_dispatch_im_msg_to' style='width:160px;'>";
		$selbx1.="<option value='0'>All Users</option>";
		while($row_user = mysqli_fetch_array($data_users)) 
		{ 
			$selbx1.="<option value='".$row_user['id']."'>".$row_user['username']."</option>";
		}		
		$selbx1.="</select>";
		
		$res="";
		//if($_SESSION['user_id']==23 || $_SESSION['user_id']==18)
		//{
			$res.="<div class='left_box'>";
			$res.=	"<h3><span>Dispatch IM</span></h3>";
			$res.=	"<ul class='left_sec'>";
			$res.=		"<div id='dispatch_im_form'>
							To: &nbsp;&nbsp; ".$selbx1." <img src='images/2012/accordian-active.png' class='im_msg_send' alt='Send' border='0' onClick='mrr_dispatch_im_msg_send();'><br>
							Msg: <input type='text' id='mrr_dispatch_im_msg_box' name='mrr_dispatch_im_msg_box' value=\"\" style='width:180px;'><br>
							<hr>
						</div>";	
			$res.=		"<div id='dispatch_im_holder'></div>";			
			//if($_SESSION['user_id']==23)		
			$res.="<audio id='im_sound_affect' src='/sounds/FireTruck.mp3'></audio>";	
			$res.=	"</ul>";		
			$res.="</div>";					
		//}
		return $res;
	}
	
	function mrr_conard_component_truck_movement()
	{		
		global $new_style_path;
		//global $defaultsarray;
				
		global $trucks_array;
		global $data_trucks;
		
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span>These trucks have not moved in over 2 business day</span> ".show_help('index.php','Trucks Not Moved Box',$new_style_path,"question_mark.png")."</h3>";
		$res.=	"<ul class='left_sec'>";
		$res.=		"";
		
		if(mysqli_num_rows($data_trucks)) mysqli_data_seek($data_trucks,0);
		$cntr=0;
		$arr_used[0]=0;
		
		while($row_truck = mysqli_fetch_array($data_trucks)) 
		{
			$linedate = 0;
			
			$found_it=0;
			for($x=0;$x< $cntr; $x++)
			{
				if( $row_truck['attached_truck_id'] == $arr_used[ $x ] )  $found_it++;
			}
			
			if($found_it==0)
			{
     			$warning_flag="";	//mrr_get_equipment_maint_notice_warning('truck',$row_truck['id']);				
     			for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
     			{
     				if($mrr_maint_truck_arr[ $rr ] == $row_truck['id'])		$warning_flag=" ".trim($mrr_maint_truck_links[ $rr ]);
     			}
     			
     			if(strtotime($row_truck['linedate_last_moved']) > 0) 			$linedate = strtotime($row_truck['linedate_last_moved']);
     			
     			if((time() - $linedate) > (2 * 86400) && $linedate > 0) {
     				$show_alert = true;
     			} else {
     				$show_alert = false;
     			}
     			$use_dater="";
     			if($linedate > 0)		$use_dater=date("m/d", $linedate);		//-Y
     			
     			$icon="<img src='".$new_style_path."red_icon.png'  border='0' alt='' >";
     			$namer="$row_truck[name_driver_first] $row_truck[name_driver_last]";
				$user_attacher="$namer";	
				if(trim($namer)!="")
				{
					$user_attacher="$namer";	
					
					$icon="
     					<a href='javascript:detach_truck(".$row_truck['attached_truck_id'].",".$row_truck['driver_id'].")'>
							<img src='".$new_style_path."red_circle.png' width='15' height='14' alt='X'>
						</a>
     				"; 
				}  
				
					
				
				$titler=trim($user_attacher);
				$titler=str_replace("'","&apos;",$titler);
				$user_attacher=trim($user_attacher);
				if(strlen($user_attacher) > 10)		$user_attacher=substr($user_attacher,0,7)."...";
     			
     			if(trim($user_attacher)=="" && trim($warning_flag)=="")	$user_attacher="<span class='alert' title='Available Truck'><b>Available</b></span>";
     			
     			$holder="";
     			if($row_truck['hold_for_driver'] > 0)	
     			{
     				$use_titler="This Truck is on Hold";
     				if(trim($row_truck['on_hold_note'])!="")		$use_titler=str_replace("'","",trim($row_truck['on_hold_note']));
     				
     				$holder="<div class='alert' style='display:inline;' title='".$use_titler."'><b>HOLD</b></div> ";	
     				if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";		
     			}
     			
     			$shopper="";
				if($row_truck['in_the_shop'] > 0)	 
				{
					$use_titler="This Truck is in the Shop";
     				if(trim($row_truck['in_shop_note'])!="")		$use_titler=str_replace("'","",trim($row_truck['in_shop_note']));
     				
					$shopper="<div class='alert' style='display:inline;' title='".$use_titler."'><b>SHOP</b></div> ";
					if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";
					//$holder="";
				} 
				if($row_truck['in_body_shop'] > 0)	 
				{
					$use_titler="This Truck is in the Body Shop (long-term)";
     				if(trim($row_truck['in_body_note'])!="")		$use_titler=str_replace("'","",trim($row_truck['in_body_note']));
     				
					$shopper="<div class='alert' style='display:inline;' title='".$use_titler."'><b>BODY</b></div> ";
					if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";
					//$holder="";
				}     
				     			
     			if($show_alert) {
     				$res.="<li><span>$row_truck[name_truck]".$warning_flag."</span>  <strong title='".$titler."'>".$holder."".$shopper."".$user_attacher." ".$use_dater." ".$icon."</strong></li>";
     				
     				$arr_used[ $cntr ] = $row_truck['id'];
     				$cntr++;
     			}
     			
			}
		}		
		if(mysqli_num_rows($data_trucks)) mysqli_data_seek($data_trucks,0);
		
		$res.=	"</ul><br><br>";		
		$res.="</div>";
		
		if($cntr==0)	$res="";
		
		return $res;
	}
	
	function mrr_conard_component_truck_last_load()
	{		
		global $new_style_path;
		//global $defaultsarray;
				
		global $trucks_array;
		global $data_trucks;
		
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span>Trucks on Last/No Load</span> ".show_help('index.php','Trucks Last Load Box',$new_style_path,"question_mark.png")."</h3>";
		$res.=	"<ul class='left_sec'>";
		$res.=		"";
		
		if(mysqli_num_rows($data_trucks)) mysqli_data_seek($data_trucks,0);
		$cntr=0;
		$arr_used[0]=0;
		
		while($row_truck = mysqli_fetch_array($data_trucks)) 
		{
			$linedate = 0;
			
			$found_it=0;
			for($x=0;$x< $cntr; $x++)
			{
				if( $row_truck['id'] == $arr_used[ $x ] )  $found_it++;
			}
			
			if($found_it==0)
			{
     			$warning_flag="";	//mrr_get_equipment_maint_notice_warning('truck',$row_truck['id']);				
     			for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
     			{
     				if($mrr_maint_truck_arr[ $rr ] == $row_truck['id'])		$warning_flag=" ".trim($mrr_maint_truck_links[ $rr ]);
     			}
     			
     			if(!isset($row_truck['driver_id']))	$row_truck['driver_id']=0;
     			     			
     			$shopper="";
     			if($row_truck['in_the_shop'] > 0)	 	$shopper="<span class='alert' title='This Truck is in the Shop'><b>SHOP</b></span> ";
     			if($row_truck['in_body_shop'] > 0)	 	$shopper="<span class='alert' title='This Truck is in the Body Shop (long-term)'><b>BODY</b></span> ";
     			$holder="";
     			if($row_truck['hold_for_driver'] > 0)	$holder="<span class='alert' title='This Truck is on Hold'><b>HOLD</b></span> ";
								
     			$truck_name="".$row_truck['name_truck']."";
     			$driver_name="".$row_truck['name_driver_first']." ".$row_truck['name_driver_last']."";
     			
     			if(trim($driver_name)=="")			$driver_name="Available";
     			
     			$icon="<img src='".$new_style_path."red_icon.png'  border='0' alt='' >";
     			if(trim($driver_name)!="")			$icon="<a href='javascript:detach_truck(".$row_truck['attached_truck_id'].",".$row_truck['driver_id'].")'><img src='".$new_style_path."red_circle.png' width='15' height='14' alt='X'></a>"; 
				
				$titler=trim($driver_name);
				$titler=str_replace("'","&apos;",$titler);
				$driver_name=trim($driver_name);
				if(strlen($driver_name) > 15)			$driver_name=substr($driver_name,0,12)."...";
				
				if(trim($driver_name)=="Available")	$driver_name="<span class='alert' title='Available Truck'><b>Available</b></span>";
				  
     			$show_alert=1;
     			if(trim($warning_flag)!="")			$show_alert=0;		//warning, so there is reason truck not showing...
     			if(trim($shopper)!="")				$show_alert=0;		//in shop, so reason truck not showing...
     			if(trim($holder)!="")				$show_alert=0;		//on hold, so reason truck not showing...
     			if($row_truck['attached_truck_id'] > 0 || $row_truck['driver_id'] > 0)
     			{
     				if($row_truck['next_dispatch_id'] > 0)	$show_alert=0;		//has another dispatch...no need to show.
     				if($row_truck['next_load_id'] > 0 && $row_truck['driver_id'] > 0)	
     				{
     					$show_alert=0;		//on preplanned load...no need to show.
     				}
     				
     				$coder=" Dispatches: ".$row_truck['next_dispatch_id']." Loads: ".$row_truck['next_load_id']."";
     			}
     			     			
     			
     			if($show_alert > 0) 
     			{
     				$res.="<li><span>".$truck_name."</span>  <strong title='".$titler." ".$coder."'>".$driver_name."&nbsp;".$icon."</strong></li>";
     				
     				$arr_used[ $cntr ] = $row_truck['id'];
     				$cntr++;
     			}     			
			}
		}		
		if(mysqli_num_rows($data_trucks)) mysqli_data_seek($data_trucks,0);
		
		$res.=	"</ul><br><br>";		
		$res.="</div>";
		
		//if($cntr==0)	$res="";
		
		return $res;
	}
	function mrr_conard_component_load_available()
	{
		global $new_style_path;
		//global $defaultsarray;
		
		global $data_avail_loads;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span>Available Loads</span> ".show_help('index.php','Available Loads',$new_style_path,"question_mark.png")."</h3>";	// <em>No Date</em>
		$res.=	"<ul class='left_sec'>";
		
		$last_date = -1;
		$show_section = false;
		while($row_avail = mysqli_fetch_array($data_avail_loads)) {
			
			if($last_date != date("Y-m-d", strtotime($row_avail['linedate_pickup_eta']))) {
				$last_date = date("Y-m-d", strtotime($row_avail['linedate_pickup_eta']));
				
				if(strtotime($last_date) < 0) {
					$use_title = "No Date";
					$show_section = true;
				} else {
					$use_title = date("M j", strtotime($last_date));
					$show_section = false;
				}
				
				if($show_section) {
					
					$res.="<li><span><b>".$use_title."</b></span></li>";
				}
			}
						
			if($show_section) {
				
				$use_bg_color = '';
				
				if($row_avail['preplan']) {
					//$use_bg_color = '#ecffe2';
				} else {
					$use_bg_color = '';
				}
				/*
				echo "
					<div class='trailer_entry_available ".($row_avail['auto_created'] ? "entry_auto_created" : "")."'>
						<div style='background-color:#e5e9ff;width:100%'><a href='javascript:edit_entry_truck(0,0,$row_avail[id])'>($row_avail[id]) $row_avail[name_company]</a></div>
						".($row_avail['preplan'] == '1' ? "<div style='background-color:$use_bg_color'>&nbsp;&nbsp;&nbsp; PREPLAN - $row_avail[name_driver_first] $row_avail[name_driver_last]</div>" : "")."
						<div style='background-color:$use_bg_color;'>".( ? "" : "").( ? " - ". : "")."</div>
						<div style='background-color:$use_bg_color;'>".($row_avail['dest_city'] != '' ? "&nbsp;&nbsp;&nbsp; Dest - $row_avail[dest_city], $row_avail[dest_state]" : "").($row_avail['linedate_dropoff_eta'] > 0 ? " - ".date("H:i", strtotime($row_avail['linedate_dropoff_eta'])): "")."</div>
					</div>
				";
				*/
				
				$short_cust=$row_avail['name_company'];
				$titler="";
				if(strlen($short_cust)>=20)
				{
					$short_cust=substr($short_cust,0,17) . "...";
					$long_cust=trim($row_avail['name_company']);
					$long_cust=str_replace("'","&apos;",$long_cust);
					$titler=" title='".$long_cust."'";
				}
				
				$res.="<li><span><a href='javascript:edit_entry_truck(0,0,$row_avail[id])' style='color:white;'".$titler.">($row_avail[id]) ".$short_cust."</a></span></li>";
				if($row_avail['preplan'] == '1')
				{
					$res.="<li><span><div style='background-color:$use_bg_color'>&nbsp;&nbsp;&nbsp; PREPLAN - $row_avail[name_driver_first] $row_avail[name_driver_last]</div></span></li>";	
				}
				if($row_avail['origin_city'] != '' || $row_avail['linedate_pickup_eta'] > 0)
				{
					$res.="<li><span style='background-color:$use_bg_color;'>";
					if($row_avail['origin_city'] != '')
					{
						$res.="Orig - $row_avail[origin_city], $row_avail[origin_state]";
					}
					if($row_avail['linedate_pickup_eta'] > 0)
					{
						$res.=" - ".date("H:i", strtotime($row_avail['linedate_pickup_eta']))."";	
					}
					$res.="</span></li>";
				}
				
				if($row_avail['dest_city'] != '' || $row_avail['linedate_dropoff_eta'] > 0)
				{
					$res.="<li><span style='background-color:$use_bg_color;'>";
					if($row_avail['dest_city'] != '')
					{
						$res.="Dest - $row_avail[dest_city], $row_avail[dest_state]";
					}
					if($row_avail['linedate_dropoff_eta'] > 0)
					{
						$res.=" - ".date("H:i", strtotime($row_avail['linedate_dropoff_eta']))."";	
					}
					$res.="</span></li>";
				}
			}
		}
		$res.=	"</ul>";		
		$res.="</div>";
		
		return $res;
	}
	
	
	function mrr_conard_component_list_trailers()
	{
		global $new_style_path;
		//global $defaultsarray;
		
		global $trailer_available_array;
		global $trailer_array;
		global $data_trailers_used;
		global $data_trailers;
				
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span><a href='javascript:toggle_trailers()'>+ &nbsp; &nbsp; &nbsp; &nbsp;</a> Trailer List</span> ".show_help('index.php','Trailer List',$new_style_path,"question_mark.png")."</h3>";
		$res.=	"<ul class='left_sec pin'>";
		
		while($row_trailers = mysqli_fetch_array($data_trailers)) {
     		
     		$warning_flag="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_trailers['id']);		  		
     		for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
     		{
     			if($mrr_maint_trailer_arr[ $rr ] == $row_trailers['id'])		$warning_flag=trim($mrr_maint_trailer_links[ $rr ]);
     		}				
     		
     		$tmp_date = $startdate;
     		if(array_search($row_trailers['id'], $trailer_available_array) !== false) 
     		{       			
     			$titler2=trim("$row_trailers[name_driver_first] $row_trailers[name_driver_last]");
				$titler2=str_replace("'","&apos;",$titler2);
				$user_attacher2=trim("$row_trailers[name_driver_first] $row_trailers[name_driver_last]");
				if(strlen($user_attacher2) > 15)			$user_attacher2=substr($user_attacher2,0,12)."...";
     			
     			$use_namer="$row_trailers[trailer_name]";
     			if(trim($row_trailers['nick_name'])!="")	$use_namer="$row_trailers[nick_name]";
     			
     			   			
     			$titler=trim($use_namer);
     			$titler=str_replace("'","&apos;",$titler);
     			$user_attacher1=trim($use_namer);
				if(strlen($user_attacher1) > 6)		$user_attacher1=substr($user_attacher1,0,6)."";
				
     			$use_attacher="<strong title='".$titler."'>".$user_attacher1."</strong>";
     			if($row_trailers['attached_trailer_id'])
     			{
     				$use_attacher="<strong title='".$titler2."'>(".$user_attacher2.")</strong>";
     			}
     			//".$warning_flag."
     			//<a href=\"javascript:update_location($row_trailers[id], '$use_namer')\">$use_namer</a>
     			
     			$shopper="";
				if($row_trailers['in_the_shop'] > 0)	 
				{
					$shopper="<span class='alert'><b>SHOP</b></span> ";
				}
				if($row_trailers['in_body_shop'] > 0)	 
				{
					$shopper="<span class='alert'><b>BODY</b></span> ";
				}
     			
     			if(trim($use_namer)!="unknown" && trim($use_namer)!="unkown")
     			{          			     			    			
          			$res.="<li>
          					<span trailer_id='$row_trailers[id]' popup=\"Trailer: $use_namer<br>Location: $row_trailers[current_location]\">
          						<a href='javascript:void(0)' onclick=\"add_entry_truck_trailer('".date("Y-m-d", time())."',$row_trailers[id])\" style='color:#ABABAB; font-weight:normal;' title='".$titler."'>
          							".($row_trailers['special_project']  > 0 ? "<div class='special_project' style='display:inline;'>".$user_attacher1."</div>" : $user_attacher1 )." 
          						</a>     						
          					</span> 
          					<a href='javascript:detach_trailer($row_trailers[attached_trailer_id],$row_trailers[driver_id])'>
          						<img src='".$new_style_path."red_circle.png' width='15' height='14' alt='red_circle'>
          					</a>     					
          						".$shopper."".($row_trailers['special_project']  > 0 ? "<div class='special_project' style='display:inline;'>".$use_attacher."</div>" : $use_attacher )." 					
          				</li>";
          				//<span class='attached_trailer_$row_trailers[attached_trailer_id]'>
          				//</span>
          			
          			//echo "<div class='trailer_entry_available'><span trailer_id='$row_trailers[id]' title=\"Trailer: $row_trailers[trailer_name]<br>Location: Unknown\">$row_trailers[trailer_name]</span></div>";
          			
     			}
     		} 
     		else 
     		{
     			$array_pos = array_search($row_trailers['id'], $trailer_array);
     			mysqli_data_seek($data_trailers_used,$array_pos);
     			$row_trailers_used = mysqli_fetch_array($data_trailers_used);
     			
     			$titler2=trim("$row_trailers[name_driver_first] $row_trailers[name_driver_last]");
				$titler2=str_replace("'","&apos;",$titler2);
				$user_attacher2=trim("$row_trailers[name_driver_first] $row_trailers[name_driver_last]");
				if(strlen($user_attacher2) > 15)		$user_attacher2=substr($user_attacher2,0,12)."...";
     			
     			$use_namer="$row_trailers[trailer_name]";
     			if(trim($row_trailers['nick_name'])!="")		$use_namer="$row_trailers[nick_name]";
     			
     			$titler=trim($use_namer);
     			$titler=str_replace("'","&apos;",$titler);
     			$user_attacher1=trim($use_namer);
				if(strlen($user_attacher1) > 6)		$user_attacher1=substr($user_attacher1,0,6)."";
     			
     			$use_attacher="<strong title='".$titler."'>".$user_attacher1."</strong>";
     			if($row_trailers['attached_trailer_id'])
     			{
     				$use_attacher="<strong title='".$titler2."'>(".$user_attacher2.")</strong>";
     				/*
     				<span class='attached_trailer_$row_trailers[attached_trailer_id]'>
     					<a href='javascript:detach_trailer($row_trailers[attached_trailer_id],$row_trailers[driver_id])'>
     						($row_trailers[name_driver_first] $row_trailers[name_driver_last])
     					</a>
     				</span>
     				*/
     			}
     			//".$warning_flag."
     			/*
     			$res.="<li>
     					<span trailer_id='$row_trailers[id]' popup=\"Trailer: $row_trailers[trailer_name]<br>$row_trailers_used[name_truck]<br>$row_trailers_used[location]<br>".date("l", strtotime($row_trailers_used['linedate']))."\">
     						$row_trailers[trailer_name]    						
     					</span> 
     					<a href='javascript:detach_trailer($row_trailers[attached_trailer_id],$row_trailers[driver_id])'>
     						<img src='".$new_style_path."red_circle.png' width='15' height='14' alt='red_circle'>
     					</a>
     					
     						".$use_attacher."
     					
     				</li>";
     				//<span class='attached_trailer_$row_trailers[attached_trailer_id]'>
     				//</span>
     			*/
     		}     		
     	}
		$res.=	"</ul>";		
		$res.="</div>";   
		
		return $res;
	}
	
	function mrr_conard_component_list_trucks()
	{
		global $new_style_path;
		//global $defaultsarray;
				
		global $trucks_array;
		global $data_trucks;
		
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span><a href='javascript:toggle_trucks()'>+ &nbsp; &nbsp; &nbsp; &nbsp;</a> Truck List</span> ".show_help('index.php','Truck List',$new_style_path,"question_mark.png")."</h3>";
		$res.=	"<ul class='left_sec'>";
		
		while($row_trucks = mysqli_fetch_array($data_trucks)) {
			
			$warning_flag="";			
			for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
			{
				if($mrr_maint_truck_arr[ $rr ] == $row_trucks['id'])		$warning_flag=trim($mrr_maint_truck_links[ $rr ]);
			}
			
			if(!isset($row_truck['driver_id']))			$row_truck['driver_id']=0;		// && $row_truck['driver_id'] > 0
			
			if(in_array($row_trucks['id'],$trucks_array)) 
			{
				
				$titler2=trim("$row_trailers[name_driver_first] $row_trailers[name_driver_last]");
				$titler2=str_replace("'","&apos;",$titler2);
				$user_attacher2=trim("$row_trailers[name_driver_first] $row_trailers[name_driver_last]");
				if(strlen($user_attacher2) > 10)		$user_attacher2=substr($user_attacher2,0,7)."...";
     			     			
     			$titler=trim("$row_trucks[name_truck]");
     			$titler=str_replace("'","&apos;",$titler);
     			$user_attacher1=trim("$row_trucks[name_truck]");
				if(strlen($user_attacher1) > 6)		$user_attacher1=substr($user_attacher1,0,6)."";
				
				
				$user_attacher="<strong>$row_trucks[name_truck]</strong>";
				if($row_trucks['attached_truck_id'])
				{						
					$user_attacher="<strong>($row_trucks[name_driver_first] $row_trucks[name_driver_last])</strong>";	
				}
			} 
			else 
			{	     			     			
     			$shot_this_truck=1;
     			
     			$titler=trim("$row_trucks[name_truck]");
     			$titler=str_replace("'","&apos;",$titler);
     			$user_attacher1=trim("$row_trucks[name_truck]");
				if(strlen($user_attacher1) > 6)		$user_attacher1=substr($user_attacher1,0,6)."";
				
				
				$namer="$row_trucks[name_driver_first] $row_trucks[name_driver_last]";
				$titler2=trim($namer);
				$titler2=str_replace("'","&apos;",$titler2);
				$user_attacher2=trim($namer);
				if(strlen($user_attacher2) > 10)		$user_attacher2=substr($user_attacher2,0,7)."...";
				
				$user_attacher="<strong></strong>";	
				if(trim($namer)!="")
				{
					$user_attacher="<strong title='".$titler2."'>".$user_attacher2."</strong>";	
				}
				
				if(trim($user_attacher)=="<strong></strong>" && trim($warning_flag)=="")		$user_attacher="<span class='alert' title='Available Truck'><b>Available</b></span> ";
				
				$shopper="";
				if($row_trucks['in_the_shop'] > 0)	 
				{
					$use_titler="This Truck is in the Shop";
					if(trim($row_trucks['in_shop_note'])!="")		$use_titler=str_replace("'","",trim($row_trucks['in_shop_note']));
					
					$shopper="<div class='alert' style='display:inline;' title='".$use_titler."'><b>SHOP</b> &nbsp; </div>";
					if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";					
					//$shot_this_truck=0;
				}
							
				$holder="";
     			if($row_trucks['hold_for_driver'] > 0)	
     			{
     				$use_titler="This Truck is on Hold";
     				if(trim($row_trucks['on_hold_note'])!="")		$use_titler=str_replace("'","",trim($row_trucks['on_hold_note']));
     				
     				$holder="<div class='alert' style='display:inline;' title='".$use_titler."'><b>HOLD</b> &nbsp; </div> ";	
     				if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";	
     				//$shot_this_truck=0;	
     			}
     			
     			if($row_trucks['in_body_shop'] > 0)	 
				{
					$use_titler="This Truck is in the Body Shop, Long Term";
					if(trim($row_trucks['in_body_note'])!="")		$use_titler=str_replace("'","",trim($row_trucks['in_body_note']));
					
					$shopper="<div class='alert' style='display:inline;' title='".$use_titler."'><b>BODY</b> &nbsp; </div>";
					if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";
					$holder="";
					//$shot_this_truck=0;
				}
     			
     			if($shot_this_truck > 0)
     			{     			
					$res.="<li>
						<span><a href=\"javascript:add_entry_truck_id('".date("Y-m-d", time())."',$row_trucks[id])\" style='color:#ABABAB; font-weight:normal;' title='".$titler."'>".$user_attacher1."</a> ".$warning_flag."</span> 
						<a href='javascript:detach_truck($row_trucks[attached_truck_id],$row_trucks[driver_id])'>
							<img src='".$new_style_path."red_circle.png' width='15' height='14' alt='red_circle'>
						</a>						
							<div style='float:right; margin-right:10px;'>".$holder."".$shopper."".$user_attacher."</div>							
						</li>";
				}				
			}
		}
		
		$res.=	"</ul>";
		
		$res.="</div>";
		
		return $res;
	}
	
	function mrr_conard_component_calendar($curmon,$curday,$curyear,$mon,$day,$year)
	{
		global $new_style_path;
		global $defaultsarray;
		$res="";
		
		if($defaultsarray['load_board_display_calendar'] ==0)			return $res;
		
		
		$dater="".$mon."/".$day."/".$year."";		
		$startdate=date("Y-m-d",strtotime($dater));
		$fullm=strtoupper(date("M",strtotime($startdate)));		//date("F",strtotime($startdate));
		$fulld=date("l",strtotime($startdate));
		//$wkday=date("w",strtotime($startdate));
		
		$dater2="".$mon."/01/".$year."";
		$startdate2=date("Y-m-d",strtotime($dater2));
		$wkday=date("w",strtotime($startdate2));
				
		for($i=0;$i < 43;$i++)
		{
			$box[$i]="&nbsp;";	
		}
		$cntr=1;
				
		for($i=$wkday;$i < 43;$i++)
		{
			$txt="";
			
			//$txt="<a href='index.php?mCheck=1&use_day=".$cntr."&use_mon=".$mon."&use_year=".$year."&cal_day=".$day."&cal_mon=".$mon."&cal_year=".$year."' style='border:0;'>".$cntr."</a>";
			$txt="<span class='mrr_link_like_on' onClick='mrr_get_fuel_surcharge_by_date(".$mon.",".$cntr.",".$year.");'>".$cntr."</span>";
			if( date("Ymd", strtotime("".$mon."/".$cntr."/".$year."")) > date("Ymd"))
			{	//greater than now...do not show this as an active "link"...		
				$txt="<span class='mrr_link_like_off'>".$cntr."</span>";
			}
			
			if($cntr>31)											$txt="&nbsp;";			
			if($cntr>30 && ($mon==4 || $mon==6 || $mon==9 || $mon==11))		$txt="&nbsp;";	
			if($cntr>29 && $mon==2 && $year%4==0)						$txt="&nbsp;";	
			if($cntr>28 && $mon==2)									$txt="&nbsp;";		
			$box[$i]=$txt;
			$cntr++;	
		}
		
		
		$res="";
		
		$next_month=($mon+1);
		$last_month=($mon-1);
		$next_year=$year;
		$last_year=$year;
		if($mon==1)
		{
			$last_month="12";	
			$last_year=($year-1);	
		}
		if($mon==12)
		{
			$next_month="1";
			$next_year=($year+1);			
		}
		
				
		$res.="<div id='calender'>";
		$res.=	"<div class='head'>";
		//$res.=		"<a href='#' class='plus'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=		"<div class='date_change'>";
		$res.=			"<a href='index.php?mCheck=1&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$day."&cal_mon=".$last_month."&cal_year=".$last_year."'><img src='".$new_style_path."left_changer.png' alt=''/></a>";
		$res.=			"<span>".$fullm." ".$year."</span> <span class='mrr_link_like_on' onclick='mrr_swap_section(0);'>Switch</span>";
		$res.=			"<a href='index.php?mCheck=1&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$day."&cal_mon=".$next_month."&cal_year=".$next_year."'><img src='".$new_style_path."right_changer.png' alt=''/></a>";
		$res.=		"</div>";
		$res.=	"</div>";
		$res.=	"<div class='table_sec'>";
		$res.=		"<table width='100%' cellpadding='0' cellspacing='0'>";
		$res.=		"<tr>";
		$res.=			"<th class='none'>SUN</th>";
		$res.=			"<th>MON</th>";
		$res.=			"<th>TUE</th>";
		$res.=			"<th>WED</th>";
		$res.=			"<th>THU</th>";
		$res.=			"<th>FRI</th>";
		$res.=			"<th class='none'>SAT</th>";
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[0]."</td>";
		$res.=			"<td>".$box[1]."</td>";
		$res.=			"<td>".$box[2]."</td>";
		$res.=			"<td>".$box[3]."</td>";
		$res.=			"<td>".$box[4]."</td>";
		$res.=			"<td>".$box[5]."</td>";
		$res.=			"<td>".$box[6]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[7]."</td>";
		$res.=			"<td>".$box[8]."</td>";
		$res.=			"<td>".$box[9]."</td>";
		$res.=			"<td>".$box[10]."</td>";
		$res.=			"<td>".$box[11]."</td>";
		$res.=			"<td>".$box[12]."</td>";
		$res.=			"<td>".$box[13]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[14]."</td>";
		$res.=			"<td>".$box[15]."</td>";
		$res.=			"<td>".$box[16]."</td>";
		$res.=			"<td>".$box[17]."</td>";
		$res.=			"<td>".$box[18]."</td>";
		$res.=			"<td>".$box[19]."</td>";
		$res.=			"<td>".$box[20]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[21]."</td>";
		$res.=			"<td>".$box[22]."</td>";
		$res.=			"<td>".$box[23]."</td>";
		$res.=			"<td>".$box[24]."</td>";
		$res.=			"<td>".$box[25]."</td>";
		$res.=			"<td>".$box[26]."</td>";
		$res.=			"<td>".$box[27]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[28]."</td>";
		$res.=			"<td>".$box[29]."</td>";
		$res.=			"<td>".$box[30]."</td>";
		$res.=			"<td>".$box[31]."</td>";
		$res.=			"<td>".$box[32]."</td>";
		$res.=			"<td>".$box[33]."</td>";
		$res.=			"<td>".$box[34]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[35]."</td>";
		$res.=			"<td>".$box[36]."</td>";
		$res.=			"<td>".$box[37]."</td>";
		$res.=			"<td>".$box[38]."</td>";
		$res.=			"<td>".$box[39]."</td>";
		$res.=			"<td>".$box[40]."</td>";
		$res.=			"<td>".$box[41]."</td>";		
		$res.=		"</tr>";
		$res.=		"</table>";
		$res.=	"</div>";		
		$res.="</div>";
		
		
		$styler=" style='font-size:12px; padding-left:25px;'";
		
		
		$list_pmi="
			<table cellpadding='0' celspacing='0' boder='0' width='100%'>
			<tr>
				<td valign='top'".$styler."><b>Truck</b></td>
				<td valign='top'".$styler."><b>PMI</b></td>
				<td valign='top'".$styler."><b>FED</b></td>
			</tr>	
		";
		
		$days_counted=(int)$defaultsarray['trailer_pmi_date_days'];
		$feds_counted=(int)$defaultsarray['trailer_fed_date_days'];
		
		$sql = "
			select *,
				(select count(*) from equipment_history where equipment_type_id = 2 and equipment_id = trailers.id and deleted = 0) as equipment_history_entry
		
			from trailers
			where deleted = 0
				and active>0
				and (pmi_test_ignore=0 or fed_test_ignore=0)
				
			order by trailer_name
		";		//and linedate_last_pmi!='0000-00-00 00:00:00'
		$data = simple_query($sql);
		
		while($row=mysqli_fetch_array($data))
		{
			$used=0;
			$list_pmi_insert="";
			
			if($days_counted > 0)
			{
				if($row['linedate_last_pmi']=="0000-00-00 00:00:00")
				{					
					$list_pmi_insert.="<td valign='top'".$styler."><span class='alert'><b>OVERDUE!</b></span></td>";
					$used=1;
				}
				else
				{
					$now_dater=date("ymd",time());
					$next_run=date("m/d/Y", strtotime("+".$days_counted." days", strtotime($row['linedate_last_pmi'])));	
					$due_compare=date("ymd",strtotime($next_run));
					if((int) $due_compare <= (int) $now_dater)
					{
						$list_pmi_insert.="<td valign='top'".$styler."><span class='alert'><b>DUE ".$next_run."</b></span></td>";
						$used=1;
					}
					else
					{
						$list_pmi_insert.="<td valign='top'".$styler.">&nbsp;</td>";
					}
				}
			}
			
			if($feds_counted > 0)
			{
				if($row['linedate_last_fed']=="0000-00-00 00:00:00")
				{					
					$list_pmi_insert.="<td valign='top'".$styler."><span class='alert'><b>OVERDUE!</b></span></td>";
					$used=1;
				}
				else
				{
					$now_dater=date("ymd",time());
					$next_run=date("m/d/Y", strtotime("+".$feds_counted." days", strtotime($row['linedate_last_fed'])));	
					$due_compare=date("ymd",strtotime($next_run));
					if((int) $due_compare <= (int) $now_dater)
					{
						$list_pmi_insert.="<td valign='top'".$styler."><span class='alert'><b>DUE ".$next_run."</b></span></td>";
						$used=1;
					}
					else
					{
						$list_pmi_insert.="<td valign='top'".$styler.">&nbsp;</td>";
					}
				}
			}
			
			if($used==1)
			{
				$list_pmi.="<tr>";
				$list_pmi.=	"<td valign='top'".$styler."><a href='admin_trailers.php?id=".$row['id']."' target='_blank'".$styler.">".$row['trailer_name']."</a></td>";		
				$list_pmi.=	$list_pmi_insert;
				$list_pmi.="</tr>";
			}
		}	
		$list_pmi.="<tr><td valign='top' colspan='3'".$styler.">&nbsp;</td></tr>";
		$list_pmi.="</table>";	
		
		$res.="<div id='pmi_listing'>";
		$res.=	"<div class='head'>";
		//$res.=		"<a href='#' class='plus'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=		"<div class='date_change'>";
		$res.=			"<span>PMI/FED Report</span> <span class='mrr_link_like_on' onclick='mrr_swap_section(1);'>Switch</span>";
		$res.=		"</div>";
		$res.=	"</div>";
		$res.=	"<div class='table_sec'>";
		$res.=		"<table width='100%' cellpadding='0' cellspacing='0'>";
		$res.=		"<tr>";
		$res.=			"<td><div style='width:100%; height:311px; overflow:scroll; font-size:12px; text-align:left;'>".$list_pmi."</div></td>";		
		$res.=		"</tr>";
		$res.=		"</table>";
		$res.=	"</div>";	
		$res.="</div>";
		
		return $res;
	} 	
	
	function mrr_conard_component_notes()
	{
		global $new_style_path;
		global $defaultsarray;
		
		$res="";
		
		if($defaultsarray['load_board_display_notes'] ==0)			return $res;
				
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar'>";
		$res.=		"<span class='notes'>Notes</span>";
		$res.=		"<a href='javascript:edit_note(0)'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=	"</div> ";
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul>";
		
		
		// check for any dispatches that have an invalid date
		$sql = "
			select *
			
			from trucks_log
			where linedate < '1970-01-01' 
				and deleted = 0
		";
		$data_invalid_date = simple_query($sql);
		
		if(mysqli_num_rows($data_invalid_date)) {
			
			$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>Dispatches with invalid dates</span></h3>";
			$res.=	"</li>";
			
			while($row_invalid_date = mysqli_fetch_array($data_invalid_date)) {
				
				$res.=	"<li>";
				$res.=		"<h3>
								<span>
									<a href=\"javascript:edit_entry_truck('',$row_invalid_date[id],0)\">$row_invalid_date[id]</a>
								</span>
								<a href=\"javascript:edit_entry_truck('',$row_invalid_date[id],$row_invalid_date[load_handler_id])\">$row_invalid_date[load_handler_id]</a>
							</h3>";				
				$res.=	"</li>";
			}			
		}
		// check for any stops that have an invalid completion date
		
		
		$vres=mrr_warning_of_improper_date_time_for_loads();
		if($vres['num'] > 0) 
		{		
			$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>".$vres['num']." Stop(s) with invalid completion dates have been located ...<a href='report_completion_date_errors.php' target='_blank'>View Report</a></span></h3>";
			$res.=	"</li>";						
		}
		
		
     	//check for any Visual Load Plus imported loads that need to be processed...
     	$sql_vlp = "
			select load_handler.* 
			from load_handler
			where deleted=0
				and vpl_imported>0
				and vpl_import_processed=0
			order by id desc
		";
		
		$data_vlp = simple_query($sql_vlp);
		$vlp_cntr=mysqli_num_rows($data_vlp);
		if($vlp_cntr>0) {
			
			$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>".$vlp_cntr." Imported Load(s) to Process ...<a href='conard_logistics_edi.php' target='_blank'>View Imported Loads</a></span></h3>";			
			$res.=	"</li>";					
		}
		
		
		//see if any trailers have been dropped more than once...
		
		$dropped_trailers=mrr_display_trailers_dropped_multi_times();
		if(trim($dropped_trailers['html2'])!="")
		{
			$res.=$dropped_trailers['html2'];
		}
		/**/	
		
		//regular notes...
     	$sql = "
     		select notes_general,
     			id
     		
     		from site_sections
     		where site_default = 1
     	";
     	$data_notes = simple_query($sql);
     	$row_notes = mysqli_fetch_array($data_notes);
     		
     	
     	$sql = "
     		select notes.*,
     			users.username,
     			users.name_first,
     			users.name_last
     		
     		from notes, users
     		where notes.section_id = '$row_notes[id]'
     			and notes.deleted = 0
     			and notes.customer_id = 0
     			and notes.linedate = 0
     			and users.id=notes.user_id
     		order by notes.linedate_added
     	";
     	$data_notes_more = simple_query($sql);		// <b>trim($row_notes_more['username']) </b>
     	
     	if(trim($row_notes['notes_general'])=="")
     	{
     		$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>".$row_notes['notes_general']."</span></h3>
						<div id='truck_odometer_alert'></div>
						";
			$res.=	"</li>";	
     	}
     	while($row_notes_more = mysqli_fetch_array($data_notes_more)) 
     	{ 
			$res.=			"<li>";
			$res.=				"<h3><span>".date("m-d-y", strtotime($row_notes_more['linedate_added'])).": ".trim($row_notes_more['username'])."</span>";
			$res.=					"<a href='javascript:confirm_delete_note(".$row_notes_more['id'].")'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
			$res.=					"<a href='javascript:edit_note(".$row_notes_more['id'].")'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";
			$res.=				"</h3>";
			$res.=				"<p>".trim($row_notes_more['desc_long'])."</p>  ";
			$res.=			"</li>";			
		}
				
		$res.=		"</ul>";
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
		
	} 
	function mrr_conard_component_events()
	{
		global $new_style_path;
		global $defaultsarray;
		
		$result_limit = 15;
		$future_time = "30 day";	
		$res="";
		
		$today=date("Y-m-d",time())." 23:59:59";		//get end of today's date/time
		$days=0;
		$adder=" and (linedate_review_due='0000-00-00 00:00:00' or linedate_review_due<='".$today."')";
		if($days>0)	$adder=" and (linedate_review_due='0000-00-00 00:00:00' or linedate_review_due<=DATE_ADD('".$today."' ,INTERVAL ".(int) $days." DAY) )";
		
		if($defaultsarray['load_board_display_events'] ==0)			return $res;
		
		$sql = "
			select linedate_birthday as linedate,				
				concat('Birthday: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
				0 as calendar_id
				
			from drivers 
			where DATE_FORMAT(linedate_birthday,'%m-%d') <= '".date("m-d", strtotime("+5 day", time()))."' 
				and DATE_FORMAT(linedate_birthday,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
				and deleted = 0
				and active = 1
			
			union
			
			select linedate_spouse,			
				concat('Birthday: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a> Spouse <b>',spouse_name,'</b>.'),
				0
				
			from drivers 
			where DATE_FORMAT(linedate_spouse,'%m-%d') <= '".date("m-d", strtotime("+10 day", time()))."' 
				and DATE_FORMAT(linedate_spouse,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
				and deleted = 0
				and active = 1
			
			union			
			
			select linedate_anniversary,			
				concat('Anniversary: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a> and <b>',spouse_name,'</b>.'),
				0
				
			from drivers 
			where DATE_FORMAT(linedate_anniversary,'%m-%d') <= '".date("m-d", strtotime("+30 day", time()))."' 
				and DATE_FORMAT(linedate_anniversary,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
				and deleted = 0
				and active = 1
			
			union
						
			select linedate_drugtest,
				concat('Physical due for: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_drugtest <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and linedate_drugtest > 0
				and deleted = 0
				and active = 1
			
			union 
			
			select linedate_license_expires,
				concat('License Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_license_expires > 0
				and linedate_license_expires <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				and active = 1
			
			union 
			
			select linedate_cov_expires,
				concat('COV Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_cov_expires > 0
				and linedate_cov_expires <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				and active = 1
				
				
			union 
			
			select linedate_review_due as linedate,
				concat('Review Due Date: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where deleted=0
				and active > 0
				and id!=405
				".$adder."
				and linedate_review_due!='0000-00-00 00:00:00'			
							
			order by linedate asc
			
			limit $result_limit
		";	//month(linedate), day(linedate)
		$data = simple_query($sql);
		$res="";
				
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar yellow'>";
		$res.=		"<span class='notes'>Events</span>";
		$res.=		"<a href='javascript:edit_event(0)'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=	"</div> ";
		
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul>";
		
		$test_date=date("Y-m-d")." 00:00:00";
						
		//build events calendar list...		
		while($row = mysqli_fetch_array($data)) 
		{
			$tag1="";
			$tag2="";
			if($row['linedate'] < $test_date)
			{
				$tag1="<span style='color:red;'><b>";
				$tag2="</b></span>";
				$row['c_reason']=str_replace("Expires","Expired",$row['c_reason']);	
			}
			$res.=	"<li>";
     		$res.=		"<h3>";
     		$res.=			"<span>".date("M, j", strtotime($row['linedate']))."</span>";
     		//$res.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     		//$res.=			"<a href='javascript:edit_event($row[calendar_id])'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";     			
     		$res.=		"</h3>";
     		$res.=		"<p>".$tag1."".trim($row['c_reason'])."".$tag2."</p> ";
     		$res.=	"</li>";
		}
		
		$res.=		"</ul>";
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
	} 
	function mrr_conard_component_timeoff()
	{
		global $new_style_path;
		global $defaultsarray;
		$res="";
		
		if($defaultsarray['load_board_display_timeoff'] ==0)			return $res;
		
		$result_limit = 30;
		$future_time = "30 day";	
		
		$sql2 = "
			select linedate_start as linedate,
				linedate_end as linedate_to,
				driver_id as driver,
				concat('Unavailable: <a href=\"admin_drivers.php?id=',drivers.id,'\" target=\"view_driver_',drivers.id,'\">', name_driver_first, ' ', name_driver_last,'</a> ',date_format(linedate_start, '%b %e'), ' - ', date_format(linedate_end, '%b %e'),': ',reason_unavailable) as c_reason,
				reason_unavailable as c_reason2,
				1 as from_calendar,
				drivers_unavailable.id as calendar_id,
				'none' as desc_long
				
			from drivers, drivers_unavailable
			where drivers.deleted = 0
				and drivers.active = 1
				and drivers_unavailable.deleted = 0
				and drivers.id = drivers_unavailable.driver_id
				and drivers_unavailable.linedate_end >= '".date("Y-m-d")."'
			
			union 
						
			select linedate,
				'0000-00-00 00:00:00',
				0,
				desc_short,
				'',
				0 as from_calendar,				
				id,
				desc_long
			
			from calendar
			where linedate > 0
				and linedate <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				
			order by linedate asc
			
			limit $result_limit
		";	//month(linedate), day(linedate)
		$data2 = simple_query($sql2);
		
		//Events <a href='javascript:edit_event(0)'><img src='images/add.gif' style='border:0'> Add new event</a>
		
		$res="";
				
		$res.="<div class='middle_container'>";
		$res.=	"";
		$res.=	"<div class='top_bar blue'>";
		$res.=		"<span class='notes'>Time Off</span>";
		$res.=		"";	//<a href='#'><img src='".$new_style_path."add.png' alt='add'></a>
		$res.=	"</div>";
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul>";
				
		
		//add new vacation and cash advances to this section.....................Addd April 2014.......		
		$sqlv = "		
			select driver_vacation_advances.*,
     			drivers.name_driver_first,
				drivers.name_driver_last     			
     					
     		from driver_vacation_advances
     			left join drivers on drivers.id = driver_vacation_advances.driver_id
     		where driver_vacation_advances.deleted = 0
     			and driver_vacation_advances.approved_by_id >0
     			and driver_vacation_advances.cancelled_by_id =0
     			and driver_vacation_advances.cash_advance =0
     			and drivers.deleted = 0
     			and driver_vacation_advances.linedate_end >= '".date("Y-m-d")." 00:00:00'    			
     			
     		order by driver_vacation_advances.linedate_start asc
				";	
		$datav = simple_query($sqlv);
		while($rowv = mysqli_fetch_array($datav)) 
		{
				/*
				$tmp_from=date("m/d/Y",strtotime($rowv['linedate_start']));
				$tmp_to=date("m/d/Y",strtotime($rowv['linedate_end']));
				
				
				$tmp_reason=trim($rowv['comments']);
				$tmp_reason=str_replace("'","",$tmp_reason);	//&apos;
				$tmp_reason=str_replace('"',"",$tmp_reason);	//&quot;
				
				$rowv['comments']=str_replace("Unavailable: ","",$rowv['comments']);
				
				$use_java="delete_from_calendar($rowv[id]);";
				if($row2['from_calendar']==1)
				{
					$use_java="delete_driver_unavailable($rowv[id]);";	
				}
				else
				{
					$rowv['comments']="<b>".$rowv['comments'].":</b> ".trim($rowv['desc_long']);
				}
				*/
				
				$driver="<a href='admin_drivers.php?id=".$rowv['driver_id']."' target='_blank'>".trim($rowv['name_driver_first']." ".$rowv['name_driver_last'])."</a>";
				
				$vaca_label="Vacation";	
				$ranger="".date("M, j", strtotime($rowv['linedate_start']))." - ".date("M, j", strtotime($rowv['linedate_end']))."";
				if($rowv['cash_advance'] > 0)
				{
					$vaca_label="Advance";	
					$ranger="".date("M, j Y", strtotime($rowv['linedate_start']))."";
				}	
					
											
				$res.=	"<li>";
				$res.=		"<h3><span>".$vaca_label."</span>";
				$res.=			"<span style='margin-left:120px;' onclick='window.open(\"drivers_vacation_advances.php?driver_id=".$rowv['driver_id']."&use_id=0\");'>
									<img src='".$new_style_path."blue_icon1.png' alt='add'>
								</span>";	
				$res.=			"<a href='drivers_vacation_advances.php?driver_id=".$rowv['driver_id']."&use_id=".$rowv['id']."' target='_blank'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";	
				$res.=		"</h3>";
				$res.=		"<p><b>".$driver." ".$ranger."</b><br>".trim($rowv['comments'])."</p> ";		
				$res.=	"</li>";
		}
		//.............................................................................................		
		
		
		//build time off ...add editing to Unavailable Driver on load board only.		
		while($row2 = mysqli_fetch_array($data2)) 
		{			
				$tmp_reason=trim($row2['c_reason2']);
				$tmp_reason=str_replace("'","",$tmp_reason);	//&apos;
				$tmp_reason=str_replace('"',"",$tmp_reason);	//&quot;
				
				$tmp_from=date("m/d/Y",strtotime($row2['linedate']));
				$tmp_to=date("m/d/Y",strtotime($row2['linedate_to']));
				
				$row2['c_reason']=str_replace("Unavailable: ","",$row2['c_reason']);
				
				$use_java="delete_from_calendar($row2[calendar_id]);";
				if($row2['from_calendar']==1)
				{
					$use_java="delete_driver_unavailable($row2[calendar_id]);";	
				}
				else
				{
					$row2['c_reason']="<b>".$row2['c_reason'].":</b> ".trim($row2['desc_long']);
				}
											
				$res.=	"<li>";
				$res.=		"<h3><span>".date("M, j", strtotime($row2['linedate']))."</span>";
				$res.=			"<span onClick='mrr_edit_driver_unavailable(".$row2['calendar_id'].",".$row2['driver'].",\"".$tmp_from."\",\"".$tmp_to."\",\"".$tmp_reason."\");' style='margin-left:120px;'>
									<img src='".$new_style_path."blue_icon1.png' alt='add'>
								</span>";	
				$res.=			"<a href='javascript:".$use_java."'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";	
				$res.=		"</h3>";
				$res.=		"<p>".trim($row2['c_reason'])."</p> ";		
				$res.=	"</li>";
		}
		$res.=		"</ul>";		
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
	} 	
	
	function mrr_conard_component_peoplenet_msgs()
	{
		global $new_style_path;
		global $defaultsarray;
		
		$res="";
		
		if($defaultsarray['load_board_display_peoplenet'] ==0)			return $res;
		
		$offset_hrs=abs((int)$defaultsarray['gmt_offset_peoplenet']);
		$timezoning=trim($defaultsarray['gmt_offset_label']);
				
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar peoplenet'>";
		$res.=		"<span class='notes' title='Message Alerts for PeopleNet Tracking'>PN Messages</span>";
		$res.=		"<a href='peoplenet_messager.php' target='_blank'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=	"</div> ";
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul id='new_pn_messages'>";
		
		$cntr=0;
		$sql="
			select truck_tracking_msg_history.*
			from ".mrr_find_log_database_name()."truck_tracking_msg_history
			where user_id_read=0
				and no_response_needed='0'
				and msg_text NOT LIKE 'warning: %'
			order by linedate_created desc,
				truck_name asc,
				truck_id asc
		";
		$data=simple_query($sql);
		
		$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
     	//$offset_gmt=$offset_gmt * -1;
		
		$mydate=date("Y-m-d");		//today...
		
		while($row = mysqli_fetch_array($data)) 
		{
			$msg_id=$row['id'];
			$truck_id=$row['truck_id'];
			$truck_name=trim($row['truck_name']);
			//$created=date("m/d/Y H:i:s",strtotime("-".$offset_hrs." hours",strtotime($row['linedate_received'])));
			$created=date("m/d/Y H:i",strtotime($row['linedate_added']));
			$recipient=trim($row['recipient_name']);
			$msg_txt=trim($row['msg_text']);
			
			$dres=mrr_find_pn_truck_drivers($truck_id,$mydate,1);
			
			$driver=$dres['driver_name_1'];
			$load_id=$dres['load_id'];
			$disp_id=$dres['dispatch_id'];
			//$dres['driver_id_1']=0;
			//$dres['driver_id_2']=0;
			//$dres['driver_name_2']="";	
			
			$recipient=str_replace("!OIUser","<b>Dispatch</b>",$recipient);
			
			$maint_link=mrr_prep_auto_maint_link($msg_id,$truck_id,trim($msg_txt),$row['alert_sent_flag']);		////truck_tracking_msg_history.alert_sent_flag as alert_sent,	
						
			if(substr_count($msg_txt,"Warning: ")==0)
			{						
				$res.=	"<li>";
     			$res.=		"<h3>";
     			$res.=			"<span>".$created."".$timezoning." --- <a href='peoplenet_messager.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'>".$truck_name."</a></span>";     	//admin_trucks.php?id=".$truck_id."
     			//$res.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     			$res.=			"<a href='peoplenet_messager.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
     			$res.=		"</h3>";
     			$res.=		"<p>
     							Driver(s): ".$driver."<br>
     							<a href='peoplenet_messager.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'>Unread...Click here to read.</a> ".$recipient.": ".$msg_txt."
     							<span class='mrr_link_like_on' onClick='mrr_ignore_new_messages(".$msg_id.",".$_SESSION['user_id'].");' title='click to ignore, or if no response is needed.'>
     								<img src='/images/2012/red_icon1.png' alt='X' border='0' width='15' height='14'>     								     								
     							</span>
     							<img src='/images/note_msg.png' border='0' alt='Reply' width='12' height='16' onClick=\"pn_msg_box_mini_reply(".$disp_id.",".$load_id.",".$truck_id.",'".$mydate." 00:00:00');\">
     							".$maint_link."
    								<div id='pn_note_mini_holder_".$disp_id."'></div>
    							</p> ";
     			$res.=	"</li>";
     			
     			$cntr++;
     		}
		}		
		
		//key/legend for the PN and MSGS section... 
		$res.=	"<li>";
     	$res.=		"<h3>";
     	$res.=			"<br><span>PN and MSGS Legend</span>";    			
     	$res.=		"</h3>";
     	$res.=		"<p><b>PN</b>=PeopleNet Dispatch can be sent.</p> ";
     	$res.=		"<p><b>MSGS</b>=Message link for this truck.</p> ";
     	$res.=		"<p style='color:red; font-weight:bold;'>Dispatch has not been sent to truck.</p>";
     	$res.=		"<p style='color:orange; font-weight:bold;'>Updated since last send.</p>";
     	$res.=		"<p style='color:green; font-weight:bold;'>Dispatch sent to truck.</p>";
     	$res.=		"<p>Colors based on sent status. MSGS flag will always match PN link based on status of peoplenet dispatch.  MSGS is quick link to messages.</p> ";
     	$res.=		"<p>Hover on Load to see distance and current truck location.  <b>No Distance.</b> displays when dispatch has not been sent through PeopleNet system and no GPS coordinates have been calculated for the stops.  Send dispatch to fix this.</p> ";
     	$res.=	"</li>";		
		
		$res.=		"</ul>";
		$res.=	"</div>";		
		$res.="</div>";
		
		$cut_off_switch=0;	//
		if($cntr > 0 && $cut_off_switch==0)
		{
			//mrr_pn_message_warning
			$msg_marq="You have ".$cntr." unread items in the PN Messages.  Please check them.";
			$res.="
				<input type='hidden' name='mrr_pn_message_warning_cntr' id='mrr_pn_message_warning_cntr' value='".$cntr."'>
				<input type='hidden' name='mrr_pn_message_warning_txt' id='mrr_pn_message_warning_txt' value='".$msg_marq." ... ".$msg_marq." ... ".$msg_marq." ... ".$msg_marq." ... '>				
			";	
			/*			
			$.prompt('You have ".$cntr." unread items in the PN Messages.  Please check them.');
										
			$('.marquee').marquee();  
			
			$('.marquee').marquee({
                    //If you wish to always animate using jQuery
                    allowCss3Support: true,                    
                    css3easing: 'linear', 	//works when allowCss3Support is set to true - for full list see http://www.w3.org/TR/2013/WD-css3-transitions-20131119/#transition-timing-function                   
                    easing: 'linear',		//requires jQuery easing plugin. Default is 'linear'                    
                    delayBeforeStart: 1000,	//pause time before the next animation turn in milliseconds                    
                    direction: 'left',		//'left', 'right', 'up' or 'down'                    
                    duplicated: false,		//true or false - should the marquee be duplicated to show an effect of continues flow                    
                    duration: 5000,		//speed in milliseconds of the marquee in milliseconds                    
                    gap: 20,				//gap in pixels between the tickers
                    pauseOnCycle: false,	//on cycle pause the marquee                    
                    pauseOnHover: false		//on hover pause the marquee - using jQuery plugin https://github.com/tobia/Pause
               });
			
			*/
		}
		else
		{
			//mrr_pn_message_warning
			$res.="				
				<input type='hidden' name='mrr_pn_message_warning_cntr' id='mrr_pn_message_warning_cntr' value='0'>
				<input type='hidden' name='mrr_pn_message_warning_txt' id='mrr_pn_message_warning_txt' value=''>	
			";	
		}
		
		
		//if($cntr == 0)		return "<div class='middle_container'>&nbsp;</div>";	//blank out this section...
		return $res;
	} 
	
	function mrr_conard_component_geofencing_msgs()
	{		
		global $new_style_path;
		global $defaultsarray;
		
		$sql = "
			select page_html
			
			from cache_holder
			where cache_name = 'index_mrr_conard_component_geofencing_msgs'
				and linedate_added > '".date("Y-m-d H:i:s", strtotime("-1 min", time()))."'
		";
		$data_cache = simple_query($sql);
		if(mysqli_num_rows($data_cache)) {
			$row_cache = mysqli_fetch_array($data_cache);
			
			return $row_cache['page_html'];
		} else {
				
			
			if($defaultsarray['peoplenet_geofencing_mph'] ==0 && $defaultsarray['peoplenet_geofencing_tolerance'] ==0)			return $res;
						
			$res.="<div class='middle_container'>";
			$res.=	"<div class='top_bar geofence'>";
			$res.=		"<span class='notes' title='Message for Geofence'>Geofence</span>";
			$res.=		"<a href='report_peoplenet_activity.php' target='_blank'><img src='".$new_style_path."add.png' alt='View'></a>";
			$res.=	"</div> ";
			$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
			$res.=		"<ul id='new_pn_geofencing'>";
			
			$reporter=mrr_pull_all_active_geofencing_rows_alt1(1);	
			
			$res.=	$reporter;
						
			$res.=		"</ul>";
			$res.=	"</div>";		
			$res.="</div>";
			
			//if($cntr == 0)		return "<div class='middle_container'>&nbsp;</div>";	//blank out this section...
			
			// delete any old cache out
			$sql = "
				delete from cache_holder
					where cache_name = 'index_mrr_conard_component_geofencing_msgs'
			";
			simple_query($sql);
			
			// store our new cache
			$sql = "
				insert into cache_holder
					(linedate_added,
					page_html,
					cache_name)
					
					values (now(),
					'".sql_friendly($res)."',
					'index_mrr_conard_component_geofencing_msgs')
			";
			simple_query($sql);
			
		}
		return $res;
	} 
	
	function mrr_generate_dedicated_sections($mon,$day,$year,$cal_mon,$cal_day,$cal_year)
	{
		$dedicated="";
		
		$sqlg = "
			select id,
				name_company
			
			from customers
			where separate_truck_section = 1
				and deleted = 0
				and active = 1
			order by id asc
			limit 1
		";
		$datag = simple_query($sqlg);		
		while($rowg = mysqli_fetch_array($datag)) 
		{
			$dedicated.="";
			$dedicated.=mrr_conard_component_display_company($mon,$day,$year,trim($rowg['name_company']),$rowg['id'],$cal_mon,$cal_day,$cal_year);
			$dedicated.="";
		}	
		return $dedicated;
	}
	
	function mrr_conard_component_display_company($mon,$day,$year,$name_company,$disp_type=0,$cal_mon,$cal_day,$cal_year)
	{
		
		global $new_style_path;
		global $defaultsarray;
          	
		global $driver_avail_array;		
		global $query_count;
		
		//added April 2012 so that Maint flags are all found in one group/set in one query instead of every point.	
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		
		$dater="".$mon."/".$day."/".$year."";
		$startdate=date("Y-m-d",strtotime($dater));
		$fullm=date("F",strtotime($startdate));
		$fulld=date("l",strtotime($startdate));
		
		//yesterday...previous day
		$prev_day=date("d",strtotime("-1 day", strtotime($dater)));
		$prev_mon=date("m",strtotime("-1 day", strtotime($dater)));
		$prev_year=date("Y",strtotime("-1 day", strtotime($dater)));
		$linker_prev="index.php?mCheck=1&use_day=".$prev_day."&use_mon=".$prev_mon."&use_year=".$prev_year."&cal_day=".$cal_day."&cal_mon=".$cal_mon."&cal_year=".$cal_year."";
		
		//tomorrow...next day
		$next_day=date("d",strtotime("+1 day", strtotime($dater)));
		$next_mon=date("m",strtotime("+1 day", strtotime($dater)));
		$next_year=date("Y",strtotime("+1 day", strtotime($dater)));
		$linker_next="index.php?mCheck=1&use_day=".$next_day."&use_mon=".$next_mon."&use_year=".$next_year."&cal_day=".$cal_day."&cal_mon=".$cal_mon."&cal_year=".$cal_year."";
				
		$id_array = Array();
		
		/*
		$sql = "
			select id,
				name_company
			
			from customers
			where separate_truck_section = 1
				and deleted = 0
				and active = 1
		";
		$data_truck_sections = simple_query($sql);
		
		while($row_truck_sections = mysqli_fetch_array($data_truck_sections)) {
			$id_array[] = $row_truck_sections['id'];
		}
		
		$id_list = implode(",",$id_array);
		*/	
		$sql = "
     		select *
     		
     		from drivers
     		where active = 1
     			and deleted = 0
     			and hide_available = 0
     			and id not in (
     							select driver_id
     							
     							from trucks_log 
     							where deleted = 0 
     								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
     								and dispatch_completed = 0
     						)
     
     			and id not in (
     							select driver2_id
     							
     							from trucks_log 
     							where deleted = 0 
     								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
     								and dispatch_completed = 0
     						)
     
     			and id not in (
     							select driver_id
     							
     							from drivers_unavailable
     							where drivers_unavailable.deleted = 0
     								and drivers_unavailable.driver_id = drivers.id
     								and linedate_start <= '".date("Y-m-d",strtotime($startdate))." 00:00:00'
     								and linedate_end >= '".date("Y-m-d",strtotime($startdate))." 23:59:59'
     			)
     
     		order by name_driver_last, name_driver_first
     	";
     	$data_avail_drivers = simple_query($sql);
		
		$mrr_cntr_x=0;
		$mrr_cntr_y=0;
		$mrr_cntr_z=0;		
				
		$res="";
		$res1="";
		$res2="";
		$res3="";
		$res4="";
		$res5="";		
		
		$res.="<input type='hidden' name='use_mon' id='use_mon' value='".$mon."'>";
		$res.="<input type='hidden' name='use_day' id='use_day' value='".$day."'>";
		$res.="<input type='hidden' name='use_year' id='use_year' value='".$year."'>";
		$res.="<input type='hidden' name='cal_mon' id='cal_mon' value='".$cal_mon."'>";
		$res.="<input type='hidden' name='cal_day' id='cal_day' value='".$cal_day."'>";
		$res.="<input type='hidden' name='cal_year' id='cal_year' value='".$cal_year."'>";
				
		$res.="<div id='main-details".$disp_type."' style='margin-bottom:10px;'>";
		$res.="             <table width='100%' border='0' cellpadding='0' cellspacing='0' class='tbl-mrgn'>";
          $res.="                 <tr>";
          $res.="                     <td>";
          $res.="                         <table width='100%' border='0' cellpadding='0' cellspacing='0' class='detail-heading'>";
          $res.="                             <tr>";
          $res.="                                 <td width='325' class='fleft'>Trucks ".($name_company!="" ? "-".$name_company : "") ."</td>";
          $res.="                                 <td>";
          $res.="                                     <table width='100%' border='0' cellpadding='0' cellspacing='0'>";
          $res.="                                         <tr>";
          $res.="                                             <td><a href='".$linker_prev."' title='See Previous Day'><img src='".$new_style_path."left-arrow.png' alt='left-arrow'></a></td>";
          $res.="                                             <td class='txt-cntr'>".$fullm." ".(int)$day."</td>";
          $res.="                                             <td><img src='".$new_style_path."devider.png' alt='image'></td>";
          $res.="                                             <td class='txt-cntr'>".$fulld."</td>";
          $res.="                                             <td><a href='".$linker_next."' title='See next Day'><img src='".$new_style_path."right-aroe.png' alt='right-arrow'></a></td>";
          $res.="                                         </tr>";
          $res.="                                     </table>";
          $res.="                                 </td>";
          $res.="                                 <td width='325' class='fright'> 
          									<a href='/index.php?mCheck=1&mrr_calendar_mode=0' style='color:white; font-weight:bold;'>Small Calendar</a> 
          									&nbsp; 
          									<a href='/index.php?mCheck=1&mrr_calendar_mode=1' style='color:white; font-weight:bold;'>Full Calendar</a>          									 
          									<a href='javascript:new_load()'><img src='".$new_style_path."plus.png' alt='add'></a>
          								</td>";	//<a href='javascript:edit_note_date(0,$disp_type,'".date("Y-m-d", strtotime($startdate))."')'></a>
          $res.="                             </tr>"; 
          $res.="                         </table>";
          $res.="                     </td>";
          $res.="                 </tr>";
          $res.="                 <tr>";
          $res.="                     <td>";
          
          //Now create sections....for each part (drivers,loads,dropped trailers, etc.)
                    
          $res1.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res1.=	"<tr>";
		$res1.=		"<td class='miner-gray fleft' colspan='2'>AVAILABLE DRIVERS</td>";
		$res1.=		"<td class='miner-gray'>&nbsp;</td>";
		$res1.=		"<td class='miner-gray'>&nbsp;</td>";
		$res1.=		"<td class='miner-gray'>&nbsp;</td>";
		$res1.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res1.=	"</tr>";
		$res1.=	"<tr>";
          $res1.=		"<td nowrap class='dark-gray fleft' width='150'>Date</td>";
          $res1.=		"<td nowrap class='dark-gray'>Driver</td>";
          $res1.=		"<td nowrap class='dark-gray'>Location </td>";
          $res1.=		"<td nowrap class='dark-gray' width='125'>Truck No.</td>";
          $res1.=		"<td nowrap class='dark-gray' width='125'>Trailer No.</td>";
          $res1.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res1.=	"</tr>"; 
          
          $res2.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res2.=	"<tr>";
		$res2.=		"<td class='miner-gray fleft' colspan='2'>PREPLAN LOADS</td>";
		$res2.=		"<td class='miner-gray'>&nbsp;</td>";
		$res2.=		"<td class='miner-gray'>&nbsp;</td>";
		$res2.=		"<td class='miner-gray'>&nbsp;</td>";
		$res2.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res2.=	"</tr>";
		$res2.=	"<tr>";
          $res2.=		"<td nowrap class='dark-gray fleft' width='150'>Load</td>";
          $res2.=		"<td nowrap class='dark-gray'>Driver</td>";
          $res2.=		"<td nowrap class='dark-gray'>Company</td>";
          $res2.=		"<td nowrap class='dark-gray' width='125'>Origin</td>";
          $res2.=		"<td nowrap class='dark-gray' width='125'>Destination</td>";
          $res2.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res2.=	"</tr>"; 
          
          $res3.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res3.=	"<tr>";
		$res3.=		"<td class='miner-gray fleft' colspan='2'>LOADS</td>";
		$res3.=		"<td class='miner-gray'>&nbsp;</td>";
		$res3.=		"<td class='miner-gray'>&nbsp;</td>";
		$res3.=		"<td class='miner-gray'>&nbsp;</td>";
		$res3.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res3.=	"</tr>";
		$res3.=	"<tr>";
          $res3.=		"<td nowrap class='dark-gray fleft' width='150'>Load</td>";
          $res3.=		"<td nowrap class='dark-gray'>Driver</td>";
          $res3.=		"<td nowrap class='dark-gray'>Location </td>";
          $res3.=		"<td nowrap class='dark-gray' width='125'>Truck No.</td>";
          $res3.=		"<td nowrap class='dark-gray' width='125'>Trailer No.</td>";
          $res3.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res3.=	"</tr>"; 
                    
          $res4.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res4.=	"<tr>";
		$res4.=		"<td class='miner-gray fleft' colspan='2'>DROPPED TRAILERS</td>";
		$res4.=		"<td class='miner-gray'>&nbsp;</td>";
		$res4.=		"<td class='miner-gray'>&nbsp;</td>";
		$res4.=		"<td class='miner-gray'>&nbsp;</td>";
		$res4.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res4.=	"</tr>";
		$res4.=	"<tr>";
          $res4.=		"<td nowrap class='dark-gray fleft' width='150'>Link</td>";
          $res4.=		"<td nowrap class='dark-gray'>City</td>";
          $res4.=		"<td nowrap class='dark-gray'>State (Notes)</td>";
          $res4.=		"<td nowrap class='dark-gray' width='125'>Zip</td>";
          $res4.=		"<td nowrap class='dark-gray' width='125'>Trailer No.</td>";
          $res4.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res4.=	"</tr>"; 
          
          $res5.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res5.=	"<tr>";
		$res5.=		"<td class='miner-gray fleft' colspan='2'>AVAILABLE DRIVERS</td>";
		$res5.=		"<td class='miner-gray'>&nbsp;</td>";
		$res5.=		"<td class='miner-gray'>&nbsp;</td>";
		$res5.=		"<td class='miner-gray'>&nbsp;</td>";
		$res5.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res5.=	"</tr>";
		$res5.=	"<tr>";
          $res5.=		"<td nowrap class='dark-gray fleft' width='150'>Date</td>";
          $res5.=		"<td nowrap class='dark-gray'>Driver</td>";
          $res5.=		"<td nowrap class='dark-gray'>Location </td>";
          $res5.=		"<td nowrap class='dark-gray' width='125'>Truck No.</td>";
          $res5.=		"<td nowrap class='dark-gray' width='125'>Trailer No.</td>";
          $res5.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res5.=	"</tr>"; 
          
			
		$sql_filter = "";
		$sql_id_filter = "";
		$sql_filter2 = "";
		$sql_id_filter2 = "";
		$sql_filter_drop_tl_cust = "";
		
		//$name_company,$disp_type
		
		$mrr_adder="";
		$mrr_adder2="";
		if($disp_type > 0)
		{
			$mrr_adder=" and load_handler.dedicated_load='1' and load_handler.customer_id='".$disp_type."'";
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id and trailers_dropped.customer_id='".$disp_type."' order by trailers_dropped.id desc limit 1)=0";	
		}	
		else
		{
			$mrr_adder=" and load_handler.dedicated_load='0'";	
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1)=1";
		}	
		
		$cust_id_array = explode(",",$id_list);
		if($id_list != "") {
			$sql_filter = " and trucks_log.customer_id not in ($id_list) ";
			$sql_filter2 = " and load_handler.customer_id not in ($id_list) ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id not in ($id_list) ";
		}
		
		if($disp_type > 0) {
			$sql_id_filter = " and trucks_log.customer_id = '$disp_type' ";
			$sql_id_filter2 = " and load_handler.customer_id = '$disp_type' ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id = '$disp_type' ";
		}
		
		$sql = "
			select *
			
			from notes
			where deleted = 0
				and customer_id = '$disp_type'
				and linedate >= '".date("Y-m-d", $startdate)."'
				and linedate <= '".date("Y-m-d", strtotime("7 day", $startdate))."'
			order by linedate_added
		";
		$data_notes = simple_query($sql);
		while($row_notes = mysqli_fetch_array($data_notes)) {
			$note_day = date("j", strtotime($row_notes['linedate']));
			if(!isset($notes_day_array[$note_day])) $notes_day_array[$note_day] = Array();
			$notes_day_array[$note_day][] = $row_notes;
		}

						
          for($i=0;$i < 1;$i++) 
          {
               /* pull out all the trucks in the log to display */
               $sql = "
               select trailers.trailer_name,
               	trucks_log.location,
               	trucks_log.dropped_trailer,
               	trucks_log.load_handler_id,
               	trucks_log.driver2_id,
               	drivers.name_driver_first,
               	drivers.name_driver_last,
               	drivers.phone_cell,
               	trucks.name_truck,
               	trucks_log.id,
               	trailers.id as trailer_id,
               	trucks.id as truck_id,
               	trucks_log.linedate_updated,
               	trucks_log.linedate,
               	trucks_log.color,
               	trucks_log.has_load_flag,
               	trucks_log.dispatch_completed,
               	trucks_log.destination,
               	trucks_log.destination_state,
               	(select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) as dedicated_trailer,
               	load_handler_stops.shipper_name,
               	load_handler_stops.shipper_city,
               	load_handler_stops.shipper_state,
               	load_handler_stops.stop_type_id,
               	load_handler_stops.linedate_pickup_eta,
               	load_handler_stops.id as load_handler_stop_id,
               	load_handler_stops.linedate_completed,
               	load_handler.special_instructions,
               	(select max(id) as id from load_handler lh where lh.preplan = 1 and lh.preplan_driver_id = trucks_log.driver_id and lh.deleted = 0) as driver_preplanned,
               	(select count(*) from trucks_log_notes where deleted = 0 and truck_log_id = trucks_log.id) as note_count
               
               from trucks_log
               	left join drivers on drivers.id = trucks_log.driver_id
               	left join trucks on trucks.id = trucks_log.truck_id
               	left join trailers on trailers.id = trucks_log.trailer_id
               	left join load_handler_stops on load_handler_stops.trucks_log_id = trucks_log.id and load_handler_stops.deleted = 0
               	left join load_handler on load_handler.id = trucks_log.load_handler_id
               where trucks_log.linedate = '".date("Y-m-d", strtotime( $startdate))."'
               	and trucks_log.deleted = 0 
               	and dispatch_completed = 0
               	".$mrr_adder."																			
               	$sql_filter
               	$sql_id_filter
               	
               order by trucks_log.dropped_trailer, trucks.name_truck, load_handler_stops.load_handler_id, load_handler_stops.linedate_pickup_eta
               ";	//".$mrr_adder2."
               //if($_SERVER['REMOTE_ADDR'] == '70.90.229.29') d($sql);
               $data_log = simple_query($sql);
               
               $sql = "
               select load_handler.*,
               	customers.name_company,
               	drivers.name_driver_last,
               	drivers.name_driver_first
               
               from load_handler
               	left join customers on customers.id = load_handler.customer_id
               	left join drivers on drivers.id = load_handler.preplan_driver_id
               where load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($startdate))."'
               	and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime($startdate))." 23:59:59'
               	and load_handler.deleted = 0
               	and (
               		load_handler.load_available = 1
               		or 
               		(select count(*) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id) = 0
               	)
               	".$mrr_adder."	
               	$sql_filter2
               	$sql_id_filter2
               	
               order by load_handler.preplan, load_handler.linedate_pickup_eta, load_handler.id
               ";
               $data_available_loads = simple_query($sql);
                              
               $extra_class = '';
               if(date("Y-m-d", strtotime($startdate)) == date("Y-m-d", time())) 
               	$extra_class = 'calendar_today';
               
               //echo "<td align='left' class='truck_drop $extra_class' valign='top' nowrap linedate='".date("Y-m-d", strtotime($startdate))."'>";
               
               
               if(date("Y-m-d") == date("Y-m-d", strtotime($startdate))) 
               {               
                    //$res.=	"<tr><td class='miner-gray' colspan='6'><b>MSG: Dates: ".date("Y-m-d")."==".date("Y-m-d", strtotime($startdate)).". COUNT=(".count($driver_avail_array).")</b></td></tr>";
                    
                    // see if we have any available drivers on this day
                    $available_driver_count = 0;
                    foreach($driver_avail_array as $darray) 
                    {
                    	//$res.=	"<tr><td class='miner-gray' colspan='6'><b>MSG: Driver=".$darray['driver_id'].".</b></td></tr>";
                    	// get a list of pre-planed loads available for this driver
                    	$sql = "
                    		select load_handler.*,
                    			customers.name_company
                    		
                    		from load_handler, customers
                    		where load_handler.preplan = 1
                    			and customers.id = load_handler.customer_id
                    			and load_handler.deleted = 0
                    			".$mrr_adder."
                    			and load_handler.preplan_driver_id = '".sql_friendly($darray['driver_id'])."'
                    	";
                    	$data_preplan = simple_query($sql);
                    	
                    	//$res.=	"<tr><td class='miner-gray' colspan='6'><b>MSG: Company=".$name_company." Type=".$disp_type." and COUNT=(".count($darray).").</b></td></tr>";
                    	
                    	if(count($darray) > 1 && (($disp_type > 0 && $darray['customer_id'] == $disp_type) || ($disp_type == 0 && !in_array($darray['customer_id'], $cust_id_array)))) 
                    	{
                    		//if(date("Y-m-d", strtotime($darray['linedate_completed'])) == date("Y-m-d", strtotime("+$i days", $startdate))) {
                    		
                    		// get any attached trucks/trailers
                    		$sql = "
                    			select attached_truck_id,
                    				attached_trailer_id,
                    				trucks.name_truck,
                    				trailers.trailer_name
                    				
                    			from drivers
                    				left join trucks on trucks.id = drivers.attached_truck_id
                    				left join trailers on trailers.id = drivers.attached_trailer_id
                    			where drivers.id = '".sql_friendly($darray['driver_id'])."'
                    		";
                    		$data_attached = simple_query($sql);
                    		$row_attached = mysqli_fetch_array($data_attached);
                    		
                    		$last_load_dedicated=mrr_get_last_driver_load_dedicated($darray['driver_id']);
                    		
                    		//$res.=	"<tr><td class='miner-gray' colspan='6'><b>MSG: Company=".$name_company." Last Load=".$last_load_dedicated.".</b></td></tr>";
                    			
                    		
                    		// && $last_load_dedicated==0
                    		if(($name_company!="Dedicated Loads" && $last_load_dedicated==0) || ($name_company=="Dedicated Loads" && $last_load_dedicated==1) )
                    		{    // 	$name_company!="Dedicated Loads"
                    			
                    			$available_driver_count++;
                    			
                    			$truck_block="";
                    			$trailer_block="";
                    			$res1a="";
                    			$res1b="";
                    			
                    			$mrr_classy="gray";
                              	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                              	
                              	//get truck and trailer data for display in two modes...
                    			if($row_attached['attached_truck_id']) 
                    			{
                    				
                    				$warning_flag="";	//mrr_get_equipment_maint_notice_warning('truck',$row_attached['attached_truck_id']);		
                    				for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
                    				{
                    					if($mrr_maint_truck_arr[ $rr ] == $row_attached['attached_truck_id'])		
                    						$warning_flag=trim($mrr_maint_truck_links[ $rr ]);
                    				}
                    				/*
                    				echo "
                    					<div class='attached_truck_$row_attached[attached_truck_id]' 
                    								truck_name=\"$row_attached[name_truck]\" 
                    								driver_name=\"".$darray['name_driver_first'].' '.$darray['name_driver_last']."\">
                    						
                    					</div>
                    					<div style='clear:both'></div>
                    				";
                    				*/
                    				$res1a.=	"<tr onMouseOver='mrr_show_section(\"drivers\",$darray[driver_id])' onMouseOut='mrr_hide_section(\"drivers\",$darray[driver_id])' class='mrr_all_drivers mrr_drivers_$darray[driver_id]'>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'><a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'>[detach]</a> $row_attached[name_truck]".$warning_flag."</td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy." fright'>
                                   					<img src='images/truck_info.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_truck_history($row_attached[attached_truck_id])\" alt='Truck History' title='Truck History'>
                                   					<a href='search_site.php?search_term=".$row_attached['name_truck']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg' width='18' height='18' border='0'></a>
                                   					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg' width='18' height='18' border='0'></a>
                                   					<a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'><img alt='Remove' src='".$new_style_path."grid-minus.jpg' width='18' height='18' border='0'></a>
                                   				</td>";
                                   	$res1a.=	"</tr>";
                                   	
                                   	$truck_block="<a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'>[detach]</a> $row_attached[name_truck]".$warning_flag."";
                    			}
                    			if($row_attached['attached_trailer_id']) 
                    			{
                    				
                    				$warning_flag="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_attached['attached_trailer_id']);	
                    				for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                    				{
                    					if($mrr_maint_trailer_arr[ $rr ] == $row_attached['attached_trailer_id'])		$warning_flag=trim($mrr_maint_trailer_links[ $rr ]);
                    				}
                    				/*
                    				echo "
                    					<div class='attached_trailer_$row_attached[attached_trailer_id]' 
                    								trailer_name=\"$row_attached[trailer_name]\" 
                    								driver_name=\"".$darray['name_driver_first'].' '.$darray['name_driver_last']."\">    													
                    					</div>
                    					<div style='clear:both'></div>
                    				";
                    				*/
                    				$res1b.=	"<tr onMouseOver='mrr_show_section(\"drivers\",$darray[driver_id])' onMouseOut='mrr_hide_section(\"drivers\",$darray[driver_id])' class='mrr_all_drivers mrr_drivers_$darray[driver_id]'>";
                                   	$res1b.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1b.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1b.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1b.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1b.=		"<td class='".$mrr_classy."'><a href='javascript:void(0)' onclick='detach_trailer($row_attached[attached_trailer_id],$darray[driver_id])'>[detach]</a> $row_attached[trailer_name] ".$warning_flag."</td>";
                                   	$res1b.=		"<td class='".$mrr_classy." fright'>
                                   					<a href='search_site.php?search_term=".$row_attached['trailer_name']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg' width='18' height='18' border='0'></a>
                                   					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg' width='18' height='18' border='0'></a>
                                   					<a href='javascript:void(0)' onclick='detach_trailer($row_attached[attached_trailer_id],$darray[driver_id])'><img alt='Remove' src='".$new_style_path."grid-minus.jpg' width='18' height='18' border='0'></a>
                                   				</td>";
                                   	$res1b.=	"</tr>";
                                   	
                                   	$trailer_block="<a href='javascript:void(0)' onclick='detach_trailer($row_attached[attached_trailer_id],$darray[driver_id])'>[detach]</a> $row_attached[trailer_name] ".$warning_flag."";
                    			}
                    			$use_namer=str_replace("'","", $darray['name_driver_first'].' '.$darray['name_driver_last']);
                    			
                              	$res1.=	"<tr onMouseOver='mrr_show_section(\"drivers\",$darray[driver_id])' onMouseOut='mrr_hide_section(\"drivers\",$darray[driver_id])'>";
                              	$res1.=		"<td class='".$mrr_classy."'>".date("n/d", strtotime($darray['linedate_completed']))." <a href=\"javascript:has_load_toggle($darray[driver_id])\">(Available)</a> </td>";
                              	$res1.=		"<td class='".$mrr_classy."'>
                              					<a href=\"javascript:remove_available_driver($darray[driver_id],'".str_replace("'","", $darray['name_driver_first'].' '.$darray['name_driver_last'])."')\">[x]</a>
                              					<a href=\"javascript:edit_driver_notes($darray[driver_id])\">$darray[name_driver_first] $darray[name_driver_last]</a>
                              				</td>";
                              	$res1.=		"<td class='".$mrr_classy."'>($darray[last_city])</td>";
                              	$res1.=		"<td class='".$mrr_classy."'>".$truck_block."</td>";
                              	$res1.=		"<td class='".$mrr_classy."'>".$trailer_block."</td>";
                              	$res1.=		"<td class='".$mrr_classy." fright'>
                              					<a href='search_site.php?search_term=".$use_namer."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg' width='18' height='18' border='0'></a>
                              					<a href='javascript:edit_driver_notes($darray[driver_id]);'><img alt='Edit' src='".$new_style_path."grid-wright.jpg' width='18' height='18' border='0'></a>
                              					<a href='javascript:remove_available_driver($darray[driver_id],\"".$use_namer."\");'><img alt='Remove' src='".$new_style_path."grid-minus.jpg' width='18' height='18' border='0'></a>
                              				</td>";
                              	$res1.=	"</tr>";
                              	$res1.=	$res1a;
                              	$res1.=	$res1b;
                              	// class='hover_for_details_drivers' style='background-color:#6b9cff;font-weight:bold;color:white' id='available_driver_holder_$darray[driver_id]'
                              	// title='Dedicated=".$last_load_dedicated."'
                    			//<img src='images/driver_small.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_driver_history($darray[driver_id])\" alt='Driver History' title='Driver History'>
                    			//<img src='images/inventory.png' id='driver_has_load_$darray[driver_id]' style='width:16px;height:16px;float:left;margin-left:5px;".(isset($darray['driver_has_load']) && $darray['driver_has_load'] ? "" : "display:none")."' alt='Driver Has Load' title='Driver Has Load'>
                    			//<img src='images/note.png' id='driver_available_notes_$darray[driver_id]' onclick='edit_driver_notes($darray[driver_id])'style='cursor:pointer;width:16px;height:16px;float:left;margin-left:5px;".(isset($darray['available_notes']) && $darray['available_notes'] != ''? "" : "display:none")."' alt='Notes' title='Notes'>
                    			
                    			//<div class='available_driver_details' driver_id='$darray[driver_id]'>
                    			
                    			
                    			while($row_preplan = mysqli_fetch_array($data_preplan)) 
                    			{     											
                    				$res1.=	"<tr onMouseOver='mrr_show_section(\"drivers\",$darray[driver_id])' onMouseOut='mrr_hide_section(\"drivers\",$darray[driver_id])' class='mrr_all_drivers mrr_drivers_$darray[driver_id]'>";
                                   	$res1.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1.=		"<td class='".$mrr_classy."'><a href='javascript:edit_entry_truck(0,0,$row_preplan[id])'>$row_preplan[id]</a></td>";
                                   	$res1.=		"<td class='".$mrr_classy."'>$row_preplan[name_company]</td>";
                                   	$res1.=		"<td class='".$mrr_classy."'>$row_preplan[origin_city], $row_preplan[origin_state] ".date("H:i", strtotime($row_preplan['linedate_pickup_eta']))."</td>";
                                   	$res1.=		"<td class='".$mrr_classy."'>$row_preplan[dest_city], $row_preplan[dest_state]  ".date("H:i", strtotime($row_preplan['linedate_dropoff_eta']))."</td>";
                                   	$res1.=		"<td class='".$mrr_classy." fright'>
                                   					<a href='search_site.php?search_term=".$row_preplan['name_company']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg' width='18' height='18' border='0'></a>
                                   					<a href='javascript:edit_entry_truck(0,0,$row_preplan[id])'><img alt='Edit' src='".$new_style_path."grid-wright.jpg' width='18' height='18' border='0'></a>
                                   					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg' width='18' height='18' border='0'></a>
                                   				</td>";
                                   	$res1.=	"</tr>";
                    			}
                    			//</div>						
                    			
                    			$mrr_cntr_z++;
               			
               			}//end IF NOT DEDICATED LOAD check
               	
               		}
               	}
               	
               }	//end if (date check for today to show available drivers
               

                              
               $use_day = date("d", strtotime($startdate));
               if(isset($notes_day_array[$use_day])) 
               {
                    foreach($notes_day_array[$use_day] as $note_entry) 
                    {									
                    	// show notes for the day							
                    	$res2.=	"<tr>";		// class='note_entry'
                    	$res2.=		"<td class='miner-gray fleft'>NOTICE:</td>";
                    	$res2.=		"<td class='miner-gray' colspan='4'>
                    					<div note_id='$note_entry[id]' onclick='edit_note_date($note_entry[id],0,0)' class='note_entry_inside'>
                    						".trim_string($note_entry['desc_long'], 45)."
                    					</div>
                    				</td>";
                    	$res2.=		"<td class='miner-gray  fright'>&nbsp;</td>";
                    	$res2.=	"</tr>";
                    	
                         $mrr_classy="gray";
                         if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                         $res2.=	"<tr>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."  fright'>&nbsp;</td>";
                         $res2.=	"</tr>";
                         $mrr_cntr_z++;
                    }
               }
                             
               $loop_count = 0;
               $lh_used_id = array();
               $preplan_count = 0;
               $regular_count = 0;
               while($row_available = mysqli_fetch_array($data_available_loads)) 
               {
                    if(!in_array($row_available['id'],$lh_used_id)) 
                    {                    	
                    	if($row_available['preplan'] == '1') 
                    	{
                    		// preplan
                    		$preplan_count++;
                    		if($preplan_count == 1) 
                    		{
                    			//preplan
                    		}
                    	} 
                    	else 
                    	{
                    		// regular available
                    		$regular_count++;
                    		if($regular_count == 1) 
                    		{
                    			//available
                    		}
                    	}
                    	
                    	$lh_used_id[] = $row_available['id'];
                    	// get a list of all the stops for this load
                    	$sql = "
                    		select *
                    		
                    		from load_handler_stops
                    		where load_handler_id = '".sql_friendly($row_available['id'])."'
                    			and deleted = 0
                    		order by linedate_pickup_eta
                    	";
                    	$data_avail_stops = simple_query($sql);
                    	
                    	$mrr_classy="gray";
                    	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                    	
                    	$stop_count = 0;
                    	$stop_var = "";
                    	while($row_avail_stops = mysqli_fetch_array($data_avail_stops)) 
                    	{
                    		$stop_count++;
                    		$loc_holder = "$row_avail_stops[shipper_city], $row_avail_stops[shipper_state]";
                    		if($stop_count == 1) 
                    		{
                    			$loc_start = $loc_holder;
                    		}
                    		
                    		$stop_var.=	"<tr onMouseOver='mrr_show_section(\"preplan\",".$row_available['id'].")' onMouseOut='mrr_hide_section(\"preplan\",".$row_available['id'].")' class='mrr_all_preplan mrr_preplan_".$row_available['id']."'>";
                         	$stop_var.=		"<td class='".$mrr_classy."'></td>";
                         	$stop_var.=		"<td class='".$mrr_classy."'></td>";
                         	$stop_var.=		"<td class='".$mrr_classy."'>".($row_avail_stops['linedate_pickup_eta'] > 0 ? date("M j, H:i", strtotime($row_avail_stops['linedate_pickup_eta'])) : "")."</td>";
                         	$stop_var.=		"<td class='".$mrr_classy."'>(".($row_avail_stops['stop_type_id'] == 1 ? "S" : "C").") ".$row_avail_stops['shipper_name'].": $row_avail_stops[shipper_city], $row_avail_stops[shipper_state]</td>";
                         	$stop_var.=		"<td class='".$mrr_classy."'></td>";
                         	$stop_var.=		"<td class='".$mrr_classy." fright'>
                         					<a href='search_site.php?search_term=".$row_available['id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                         					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                         					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                         				</td>";
                         	$stop_var.=	"</tr>";										
                    		
                    	}
                    	$loc_end = $loc_holder;
                    	
                    	//<div class='hover_for_details ".($row_available['preplan'] > 0 ? "preplan_load_entry" : "available_load_entry")."'>
                    	
                    	$res2.=	"<tr onMouseOver='mrr_show_section(\"preplan\",".$row_available['id'].")' onMouseOut='mrr_hide_section(\"preplan\",".$row_available['id'].")'>";
                    	$res2.=		"<td class='".$mrr_classy."'>
                    					<a href='manage_load.php?load_id=$row_available[id]' log_id='$row_available[id]' target='view_load_".$row_available['id']."'>
                    						($row_available[id])
                    					</a>                    					
                    				</td>";
                    	$res2.=		"<td class='".$mrr_classy."'>
                    					<a href='manage_load.php?load_id=$row_available[id]' log_id='$row_available[id]' target='view_load_".$row_available['id']."'>"
                    					.($row_available['preplan'] > 0 ? "$row_available[name_driver_first] $row_available[name_driver_last] " : "")."
                    					</a>
                    				</td>";
                    	$res2.=		"<td class='".$mrr_classy."'>$row_available[name_company]</td>";
                    	$res2.=		"<td class='".$mrr_classy."'>$loc_start</td>";
                    	$res2.=		"<td class='".$mrr_classy."'>$loc_end</td>";
                    	$res2.=		"<td class='".$mrr_classy." fright'>
                    					<a href='search_site.php?search_term=".$row_available['id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                    					<a href='manage_load.php?load_id=$row_available[id]' target='view_load_".$row_available['id']."'><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                    					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                    				</td>";
                    	$res2.=	"</tr>";
                    	$res2.=	$stop_var;	//<div class='load_stop_details' log_id='$row_available[id]'>$stop_var</div>
                    	$mrr_cntr_z++;
                    }
                    
               
               }	//end while ($row_available)
               
               $last_truck_log_id = 0;
               $loop_count = 0;
               $dropped_trailer_count = 0;
               while($row_log = mysqli_fetch_array($data_log)) 
               {
                    $loop_count++;
                    $last_truck_log_id = $row_log['id'];
                    
                    
                    $date_updated = strtotime(date("m/d/Y", strtotime($row_log['linedate_updated'])));
                    $date_current = strtotime(date("m/d/Y", time()));
                    
                    $extra_class = '';
                    $trailer_alert = false;
                    if($date_updated < $date_current || $date_updated == '') 
                    {
                    	// hasn't been updated today, show an 'alert' background color
                    	// if this is a weekend or a monday, allow the days to go back a ways
                    }
                    if(strtotime($row_log['linedate']) < strtotime(date("Y-m-d", time()))) 
                    {
                    	$extra_class = 'update_alert';
                    	$trailer_alert = true;
                    }
                    
                    if(!$trailer_alert && $row_log['dropped_trailer']) 
                    {
                    	$use_background_color = ";background-color:#33ff00";
                    } 
                    elseif($row_log['color'] != '' && !$trailer_alert && $row_log['color'] != '#f8f3e4') 
                    {
                    	$use_background_color = ";background-color:$row_log[color]";
                    } 
                    else 
                    {
                    	$use_background_color = '';
                    }
                    
                    if($row_log['location'] != '') 
                    {
                    	$use_location = $row_log['location'];
                    } 
                    else 
                    {
                    	$use_location = "$row_log[destination], $row_log[destination_state]";
                    }
                    
                    //(select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) as dedicated_trailer,
                    
                    if($row_log['dropped_trailer']) 
                    {
                    		//<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:300px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'></div>
                    		//<span truck_id='$row_log[truck_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'></span>
                    		
                    		$mrr_classy="gray";
                         	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                         	$res3.=	"<tr>";
                         	$res3.=		"<td class='".$mrr_classy."'>
                         					<a style='margin-right:10px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">TL DR</a>
                    					</td>";
                         	$res3.=		"<td class='".$mrr_classy."'></td>";
                         	$res3.=		"<td class='".$mrr_classy."'>".trim_string($use_location,45)."</td>";
                         	$res3.=		"<td class='".$mrr_classy."'></td>";
                         	$res3.=		"<td class='".$mrr_classy."'>$row_log[trailer_name]</td>";
                         	$res3.=		"<td class='".$mrr_classy." fright'>
                         					<a href='search_site.php?search_term=".$row_log['id']." target='_blank''><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                         					<a href='javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])'><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                         					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                         				</td>";
                         	$res3.=	"</tr>";
                         	$mrr_cntr_z++;
                    } 
                    else 
                    {
                    	$has_load_flag = 0;
                    	$has_preplan = 0;
                    	if($row_log['dispatch_completed']) 
                    	{
                    		$use_image = "images/good.png";
                    	} 
                    	elseif ($row_log['driver_preplanned']) 
                    	{
                    		$use_image = "images/inventory.png";
                    		$has_preplan = $row_log['driver_preplanned'];
                    	} 
                    	elseif ($row_log['has_load_flag']) 
                    	{
                    		$use_image = "images/inventory.png";
                    		$has_load_flag = 1;
                    	} 
                    	else 
                    	{
                    		$use_image = "images/note.png";
                    	}
                    	
                    	$warning_flag1="";	//mrr_get_equipment_maint_notice_warning('truck',$row_log['truck_id']);		
                    	for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
                    	{
                    		if($mrr_maint_truck_arr[ $rr ] == $row_log['truck_id'])		$warning_flag1=trim($mrr_maint_truck_links[ $rr ]);
                    	}
                    	
                    	$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_log['trailer_id']);
                    	for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                    	{
                    		if($mrr_maint_trailer_arr[ $rr ] == $row_log['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                    	}
                    	
                    	if($loop_count == 1) {
                    		// show any loads						
                    		//
                    	}
                    	
                    	/*									
                    	<div class='hover_for_details truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:480px;".
                    		($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'></div>
                    	*/
                    	//<span truck_id='$row_log[truck_id]' load_handler_id='$row_log[load_handler_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'></span>
                    	$mrr_classy="gray";
                    	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                    	$res3.=	"<tr onMouseOver='mrr_show_section(\"loads\",$row_log[load_handler_id])' onMouseOut='mrr_hide_section(\"loads\",$row_log[load_handler_id])'>";
                    	$res3.=		"<td class='".$mrr_classy."'>
                    					<a href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">
                    						($row_log[load_handler_id])  
                    					</a>                    					
                    					<img log_id='$row_log[id]' class='note_image' preplan_load_id='$has_preplan' has_load_flag='$has_load_flag' src='$use_image' onclick='add_note($row_log[id])' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;".($row_log['note_count'] > 0 || $row_log['special_instructions'] != '' ? "border:1px red solid;" : "")."''>
                    					<div id='note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;width:425px;background-color:#eeeeee;padding:5px'></div>
                    					".($row_log['load_handler_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/menu_system16.png' style='cursor:pointer;float:left' onclick=\"edit_entry_truck('',$row_log[id],0)\" alt='Edit Dispatch' title='Edit Dispatch'></div>" : "")."
                    					".($row_log['truck_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/truck_info.png' style='cursor:pointer;float:left' onclick=\"view_truck_history($row_log[truck_id])\" alt='Truck History' title='Truck History'></div>" : "")."
                    				</td>";
                    	$res3.=		"<td class='".$mrr_classy."'>
                    					<a href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">
                    						 $row_log[name_driver_last], $row_log[name_driver_first]
                    					</a>
                    				</td>";
                    	$res3.=		"<td class='".$mrr_classy."'>".trim_string($use_location,45)."</td>";
                    	$res3.=		"<td class='".$mrr_classy."'><b>$row_log[name_truck]".$warning_flag1."</b></td>";
                    	$res3.=		"<td class='".$mrr_classy."'>".$warning_flag2."$row_log[trailer_name]</td>";
                    	$res3.=		"<td class='".$mrr_classy." fright'>
                    					<a href='search_site.php?search_term=".$row_log['id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                    					<a href='javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])' log_id='$row_log[id]'><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                    					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                    				</td>";
                    	$res3.=	"</tr>";                    	
                    									
                    	//<div class='load_stop_details' log_id='$row_log[id]'>
                    	$res3.=	"<tr onMouseOver='mrr_show_section(\"loads\",$row_log[load_handler_id])' onMouseOut='mrr_hide_section(\"loads\",$row_log[load_handler_id])' class='mrr_all_loads mrr_loads_$row_log[load_handler_id]'>";
                    	$res3.=		"<td class='".$mrr_classy."'></td>";
                    	$res3.=		"<td class='".$mrr_classy."'>
                    					".($row_log['driver2_id'] > 0 ? "(TEAM DRIVER)" : "")."
                    					<span id='phone_cell_display_$row_log[truck_id]'>
                    						<input style='background-color:transparent;border:0;width:90px;height:10px;text-align:right;cursor:pointer' readonly value=\"$row_log[phone_cell]\" onclick=\"$(this).select()\">
                    					</span>
                    				</td>";
                    	$res3.=		"<td class='".$mrr_classy."'></td>";
                    	$res3.=		"<td class='".$mrr_classy."'></td>";
                    	$res3.=		"<td class='".$mrr_classy."'></td>";
                    	$res3.=		"<td class='".$mrr_classy." fright'>
                    					<a href='search_site.php?search_term=".$row_log['load_handler_id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                    					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                    					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                    				</td>";
                    	$res3.=	"</tr>";
                    	
                    	$counter_sub = 0;
                    	mysqli_data_seek($data_log,$loop_count-1);
                    	while($row_log_sub = mysqli_fetch_array($data_log)) 
                    	{
                    		$counter_sub++;
                    		//echo "($row_log[load_handler_id] | $row_log_sub[load_handler_id] | $row_log_sub[load_handler_stop_id] | loop_count: $loop_count | counter: $counter_sub)";
                    		if($row_log['id'] != $row_log_sub['id'] || $row_log_sub['id'] == 0 || $row_log_sub['id'] == '') 
                    		{               			
                    			if($counter_sub > 1) $counter_sub--;
                    			
                    			$loop_count = $loop_count + $counter_sub - 1;
                    			
                    			@mysqli_data_seek($data_log,$loop_count);
                    			break;
                    		} 
                    		else 
                    		{
                    			
                    			$dt_completer="";
                    			if($row_log_sub['linedate_completed'] > 0) 
                    			{
                    				$dt_completer="<span style='color:#888888'>".($row_log_sub['linedate_completed'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_completed'])) : "")."&nbsp;&nbsp;&nbsp</span>";
                    			} 
                    			else 
                    			{
                    				$dt_completer="&nbsp;&nbsp; <a href='javascript:update_stop_complete($row_log_sub[load_handler_stop_id],".$row_log_sub['stop_type_id'].")'>".
                    											($row_log_sub['linedate_pickup_eta'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_pickup_eta'])) : "")."&nbsp;&nbsp"
                    										."</a>";
                    			}											
                    			//<div style='float:left".($row_log_sub['linedate_completed'] > 0 ? ";color:#888888" : "")."'></div>
                    			$res3.=	"<tr onMouseOver='mrr_show_section(\"loads\",$row_log[load_handler_id])' onMouseOut='mrr_hide_section(\"loads\",$row_log[load_handler_id])' class='mrr_all_loads mrr_loads_$row_log[load_handler_id]'>";
                              	$res3.=		"<td class='".$mrr_classy."'></td>";
                              	$res3.=		"<td class='".$mrr_classy."'>".$dt_completer."</td>";
                              	$res3.=		"<td class='".$mrr_classy."'>(".($row_log_sub['stop_type_id'] == 1 ? "S" : "C").") ($row_log_sub[load_handler_stop_id]) ".$row_log_sub['shipper_name'].": $row_log_sub[shipper_city], $row_log_sub[shipper_state]</td>";
                              	$res3.=		"<td class='".$mrr_classy."'></td>";
                              	$res3.=		"<td class='".$mrr_classy."'></td>";
                              	$res3.=		"<td class='".$mrr_classy." fright'>
                              					<a href='search_site.php?search_term=".$row_log_sub['load_handler_id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                              					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                              					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                              				</td>";
                              	$res3.=	"</tr>";
                    		}
                    	}
                    	//@mysqli_data_seek($data_log, $loop_count-1);
                    	
                    	$mrr_cntr_z++;
                    } // end of dropped trailer if
                    
               } // end of while statement
               
               
               if(date("m/d/Y", strtotime($startdate)) == date("m/d/Y", time())   ) 
               {
                    // today's date                                                            && $name_company!="Dedicated Loads"
                    
                    // show any dropped trailer
                    //and trailers_dropped.linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
                    
                    //$name_company,$disp_type
                    
                    $mrr_message="";
                    $mrr_add_dedicated=' and trailers_dropped.dedicated_trailer=0';
                    if($disp_type > 0)
                    {
                    	$mrr_add_dedicated=' and trailers_dropped.dedicated_trailer>0';
                    	$mrr_message="".trim($name_company)."";
                    }								
                    $sql = "
                    	select trailers_dropped.*,
                    		trailers.trailer_name,
                    		customers.name_company
                    	
                    	from trailers_dropped, trailers, customers
                    	where trailers.id = trailers_dropped.trailer_id
                    		and trailers_dropped.customer_id=customers.id										
                    		and trailers_dropped.drop_completed = 0
                    		and trailers_dropped.deleted = 0
                    		".$mrr_add_dedicated."
                    		
                    		$sql_filter_drop_tl_cust	
                    	order by name_company asc,trailer_name asc							
                    ";
                    $last_stater="";
                    $data_trailers_dropped = simple_query($sql);
                    $dropped_trailer_count = 0;
                    while($row_trailer_dropped = mysqli_fetch_array($data_trailers_dropped)) 
                    {
                    	
                    	$dropped_trailer_count++;
                    	// dropped trailers are always shown last. If this is the first dropped trailer, then show a general toggle for them
                    	if($dropped_trailer_count == 1) 
                    	{
                    		/*
                    		echo "
                    			<div class='load_board_section_header' onclick='mrr_toggle_dropped_trailer_setter(".$_SESSION['toggle_dropped_trailer_on'].");' style='cursor:pointer;color:#c4ffc4'>
                    				Toggle Dropped Trailers
                    			</div>
                    		";			
                    		*/
                    		
                    		/*
                    		if($_SESSION['toggle_dropped_trailer_on']==1)
                    		{
                    			echo "<script type='text/javascript'>
                    					toggle_dropped_trailers();
                    					</script>";	
                    			$_SESSION['toggle_dropped_trailer_on']=1;
                    		}
                    		*/
                    	}
                    	
                    	$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_trailer_dropped['trailer_id']);
                    	for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                    	{
                    		if($mrr_maint_trailer_arr[ $rr ] == $row_trailer_dropped['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                    	}
                    	
                    	if($last_stater!="$row_trailer_dropped[name_company]")
                    	{
                    		$last_stater="$row_trailer_dropped[name_company]";	
                    		
                    		$mrr_classy="gray";
                    		if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                    		$res4.=	"<tr>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy." fright'>&nbsp;</td>";
                    		$res4.=	"</tr>";
                    		$mrr_cntr_z++;
                    	}
                    	
                    	//<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style=';min-width:300px;'></div>
                    	//<span trailer_id='$row_trailer_dropped[trailer_id]' class='entry_info' dropped_trailer_id='$row_trailer_dropped[id]'></span>
                    	$mrr_classy="gray";
                    	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                    	$res4.=	"<tr>";
                    	$res4.=		"<td class='".$mrr_classy."'>
                    					<a href=\"javascript:edit_dropped_trailer($row_trailer_dropped[id])\">TL DR</a>
                    				</td>";
                    	$res4.=		"<td class='".$mrr_classy."'>$row_trailer_dropped[location_city]</td>";
                    	$res4.=		"<td class='".$mrr_classy."'>$row_trailer_dropped[location_state]</td>";
                    	$res4.=		"<td class='".$mrr_classy."'>$row_trailer_dropped[location_zip]</td>";
                    	$res4.=		"<td class='".$mrr_classy."'>".$mrr_message."".$warning_flag2."$row_trailer_dropped[trailer_name]</td>";
                    	$res4.=		"<td class='".$mrr_classy." fright'>
                    					<a href='search_site.php?search_term=".$row_trailer_dropped['trailer_id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                    					<a href='javascript:edit_dropped_trailer($row_trailer_dropped[id])'><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                    					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                    				</td>";
                    	$res4.=	"</tr>";
                    	$mrr_cntr_z++;
                    }// end while loop
               }    //end date check if for today                   
              
          }
          $spacer="";	//"<tr><td>&nbsp;</td></tr>";		//<tr><td colspan='6'>&nbsp;</td></tr>
          
		$res1.="</table>".$spacer."";    	
		$res2.="</table>".$spacer."";
		$res3.="</table>".$spacer."";
		$res4.="</table>".$spacer."";
		$res5.="</table>".$spacer."";
		
		$res.=$res1;		//available drivers
		$res.=$res2; 		//preplan loads
		$res.=$res3; 		//loads
		$res.=$res4; 		//dropped trailers
		//$res.=$res5; 		//
          
          $res.="                     </td>";
          $res.="                 </tr>";
          $res.="                 <tr><td><img src='".$new_style_path."bottom.png' alt='image'></td></tr>";
          $res.="             </table>";
		$res.="</div>";
		
		return $res;
	}
	
	//new functions as of 12/07/2011.........................new functions do not use SEPARATED_BY_
	function truck_section_mrr_alt() {
		global $mrr_calendar_mode;
		global $page_timer_array;
		
		$mrr_micro_seconds_partA=time();
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION 1a");		//make tracking log entry
    		
		$id_array = Array();
		
		/*
		$sql = "
			select id,
				name_company
			
			from customers
			where separate_truck_section = 1
				and deleted = 0
				and active = 1
		";
		$data_truck_sections = simple_query($sql);
		
		while($row_truck_sections = mysqli_fetch_array($data_truck_sections)) {
			$id_array[] = $row_truck_sections['id'];
		}
		
		$id_list = implode(",",$id_array);
		*/
		
		
		
		/* display the main truck section ----------------------------------------------------------------------------------------------------------- *****  */
		//if($_SERVER['REMOTE_ADDR'] != '70.90.229.29')	
		
		//order switched on August 21, 2014.....................................................show more than one dedicated section...MRR Dec 2015....order also switched again...
		
		
		//echo "<div id='mrr_pn_message_warning' class='marquee msg_marquee'></div>";
		echo "<marquee id='mrr_pn_message_warning' class='marquee msg_marquee' scrollamount='10' scrolldelay='100'></marquee>";
		
		
		truck_section_display_mrr_alt(0, $id_list, "",1);
		$page_timer_array[] = "After truck_section_mrr_alt - (other): " . show_page_time();
			
		//truck_section_display_mrr_alt(0, $id_list, "Dedicated Loads",2);
		mrr_generate_dedicated_sections_v2(0, $id_list, "Dedicated Loads",2);
		
		$page_timer_array[] = "After truck_section_mrr_alt - Dedicated Loads: " . show_page_time();
		
		/* now, display all the additional truck sections (per customers) --------------------------------------------------------------------------- *****  */
		/*
		@mysqli_data_seek($data_truck_sections,0);
		while($row_truck_sections = mysqli_fetch_array($data_truck_sections)) {
			truck_section_display_mrr_alt($row_truck_sections['id'],"", $row_truck_sections['name_company']);
		}  
		*/
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION 2a");				//make tracking log entry
		
	}
	
	function mrr_generate_dedicated_sections_v2($disp_type, $id_list, $name_company,$comp_num=0)
	{
		$start=$comp_num;
		$cntr=0;
		
		$sqlg = "
			select id,
				name_company
			
			from customers
			where separate_truck_section = 1
				and deleted = 0
				and active = 1
			order by id asc
		";
		$datag = simple_query($sqlg);		
		while($rowg = mysqli_fetch_array($datag)) 
		{
			$dedicated_only=1;					if($cntr > 0)		$dedicated_only=0;
			
			truck_section_display_mrr_alt($rowg['id'], $id_list, trim($rowg['name_company']), ($start + $cntr) ,$dedicated_only);
			
			$cntr++;
		}			
	}
	
	function truck_section_display_mrr_alt($disp_type, $id_list, $name_company,$comp_num=0,$dedicated_only=0) 
	{	
		global $mrr_calendar_mode;
		global $page_timer_array;
		$show_full_calendar=$mrr_calendar_mode;
		$mrr_micro_seconds_partA=time();
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION Display 1a");		//make tracking log entry
		    	
		?>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width='100%'>				
				<span class='standard16' style='color:black;'><b>Trucks <? if($name_company != "") echo " - $name_company"; ?></b></span><?= show_help('index.php','Calendar') ?> &nbsp;&nbsp;&nbsp;&nbsp;				
				
				<a href='javascript:toggle_details()'>Toggle Details</a> &nbsp;&nbsp;&nbsp;&nbsp;				
				<?				
				if($mrr_calendar_mode==0)
				{
					echo "
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(false,true);' id='show_small_calendar'>SMALL CALENDAR</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(true,true);' id='show_full_calendar'>Show Full Calendar</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='/index.php?mCheck=1&mrr_calendar_mode=2' id='show_new_calendar'>Show New Board</a>&nbsp;&nbsp;&nbsp;&nbsp;  
					";	
				}
				elseif($mrr_calendar_mode==1)
				{
					echo "
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(false,true);' id='show_small_calendar'>Show Small Calendar</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(true,true);' id='show_full_calendar'>FULL CALENDAR</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='/index.php?mCheck=1&mrr_calendar_mode=2' id='show_new_calendar'>Show New Board</a>&nbsp;&nbsp;&nbsp;&nbsp;  
					";	
				}
				else
				{
					echo "
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(false,true);' id='show_small_calendar'>Show Small Calendar</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(true,true);' id='show_full_calendar'>Show Full Calendar</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='/index.php?mCheck=1&mrr_calendar_mode=2' id='show_new_calendar'>Show New Board</a>&nbsp;&nbsp;&nbsp;&nbsp;  
					";		
				}				
				?>
			</td>
		</tr>
		<tr>
			<td align='center' width='100%'>
				<?
						$startdate = date("w", time());
						$startdate = strtotime("-7 day", strtotime("-$startdate days"));
						$current_day = date("j");
						$cur_timer=time();
						$mrr_diff=(int)(($cur_timer - $startdate)/86400);		//find number of days....864000=10 days....86400=1 day											
				?>
				<div style='border-bottom:0px black solid;'>
				<table class='standard12 mrr_whiter' cellspacing='0' cellpadding='0' width='100%'>
				<? 
					$all_col_headers="";
					$all_col_info="";
						
						
						if(isset($_GET['disp_type'])) {
							// show the load board as a list (function is in the load_board_include.php file)
							truck_section_display_sub_list_mrr_alt($disp_type, $id_list, $startdate, $name_company);
						} else {
							// normal load board display type
							
							for($week=0;$week < 4;$week++) {
								$use_startdate = strtotime("+$week week", $startdate);
								$use_day = date("j", $use_startdate);
															
								$mrr_diff=(int)(($cur_timer - $use_startdate)/86400);		//find number of days....
								
								$mres=truck_section_display_sub_mrr_alt($disp_type, $id_list, $use_startdate, $name_company, $mrr_diff, $week,$comp_num,$dedicated_only);	
								$page_timer_array[] = "After truck_section_display_sub_mrr_alt ($week): " . show_page_time();
								
								$all_col_headers.=$mres['column_headers'];
								$all_col_info.=$mres['column_info'];
							}
						}
					
					echo "<tr>".$all_col_headers."</tr>";
					echo "<tr>".$all_col_info."</tr>";											
					?>
				<!--
				<tr>
					<td colspan='7' style='border-top:1px black solid'>
					</td>
				</tr>
				-->
				</table>
				</div>
				<br>
			</td>
		</tr>
		<?
	
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION Display 2a");		//make tracking log entry    
	}
	
	function truck_section_display_sub_mrr_alt($disp_type, $id_list, $startdate, $name_company, $mrr_days_from_start=0,$week=0,$comp_num=0,$dedicated_only=0) 
	{		
		global $mrr_save_preloader;
		global $mrr_run_preloader;
		
		global $driver_avail_array;
		global $query_count;
		
		//added April 2012 so that Maint flags are all found in one group/set in one query instead of every point.	
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		
		$mrr_micro_seconds_partA=time();
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION Display Sub 1b");		//make tracking log entry
		
		$sql_filter = "";
		$sql_id_filter = "";
		$sql_filter2 = "";
		$sql_id_filter2 = "";
		$sql_filter_drop_tl_cust = "";
		
		$mrr_adder="";
		$mrr_adder2="";
		if($disp_type > 0)
		{
			$mrr_adder=" and load_handler.customer_id='".$disp_type."'";
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id and trailers_dropped.customer_id='".$disp_type."' order by trailers_dropped.id desc limit 1)=0";	
						
			if($dedicated_only > 0)		$mrr_adder=" and load_handler.dedicated_load='1' and load_handler.customer_id='".$disp_type."'";
		}	
		else
		{
			$mrr_adder=" and load_handler.dedicated_load='0' and customers.separate_truck_section=0";		//
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1)=1";
		}	
		
		$cust_id_array = explode(",",$id_list);
		if($id_list != "") {
			$sql_filter = " and trucks_log.customer_id not in ($id_list) ";
			$sql_filter2 = " and load_handler.customer_id not in ($id_list) ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id not in ($id_list) ";
		}
		
		if($disp_type > 0) {
			$sql_id_filter = " and trucks_log.customer_id = '$disp_type' ";
			$sql_id_filter2 = " and load_handler.customer_id = '$disp_type' ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id = '$disp_type' ";
		}
		
		$sql = "
			select *
			
			from notes
			where deleted = 0
				and customer_id = '$disp_type'
				and linedate >= '".date("Y-m-d", $startdate)."'
				and linedate <= '".date("Y-m-d", strtotime("7 day", $startdate))."'
			order by linedate
		";
		$data_notes = simple_query($sql);
		while($row_notes = mysqli_fetch_array($data_notes)) {
			$note_day = date("j", strtotime($row_notes['linedate']));
			if(!isset($notes_day_array[$note_day])) $notes_day_array[$note_day] = Array();
			$notes_day_array[$note_day][] = $row_notes;
		}
		
		$classer=" class='mrr_full_calendar'";
		if($mrr_days_from_start>=0 && $mrr_days_from_start<=6)
		{
			$classer=" class='mrr_full_calendar mrr_small_calendar'";	
		}
		
		$date_headers="";
		$info_columns="";
		$today_checker=date("Ymd");
		
		
			
		//$date_headers.="<tr".$classer.">";
		
			/* find the closest sunday to today */
		
			for($i=0;$i < 7;$i++) {
				if($i < 5) {
					$use_border = 'border_no_right';
				} else {
					$use_border = 'border_solid';
				}
				
				$optional_day_flag = "";
				if($i == 0 || $i == 6 || date("W", strtotime("+$i days", $startdate)) != date("W", time())) 
				{
					$optional_day_flag = "optional_day";
					
				}
				if($week==0)	$optional_day_flag .= " optional_week";
				
				$no_header_flag="";
				$no_info_flag="";				
				
				$no_date_disp=date("Ymd", strtotime("+$i days", $startdate));
				
				if($no_date_disp < $today_checker)	
				{
					$no_header_flag=" mrr_hdr_".$comp_num."_id='".$no_date_disp."'";		//create the attribute for this column header... to select and remove.
					$no_info_flag=" date_in_past_header mrr_date_".$no_date_disp."";	
				}
				
				//<b><a href=\"javascript:add_entry_truck('".date("Y-m-d", strtotime("+$i days", $startdate))."')\">". date("l", strtotime("+$i days", $startdate)) ."</a></b>
				$date_headers.= "
					<td class='$use_border calendar_header $optional_day_flag".$no_info_flag."' nowrap align='center'".$no_header_flag.">
						<b>". date("l", strtotime("+$i days", $startdate)) ."</b>
						<br>
						<b>". date("n-j-Y", strtotime("+$i days", $startdate)) ."</b>
						<!---
						<br>
						<b><a href=\"javascript:edit_note_date(0,$disp_type,'".date("Y-m-d", strtotime("+$i days", $startdate))."')\">Note</a></b>
						--->
					</td>
				";
			}						
						
		//$date_headers.="</tr>";
		//$info_columns.="<tr".$classer.">";
					
						$dater=date("Y-m-d");
						$mrr_tlat[0]="0";
						$mrr_tlon[0]="0";
						$mrr_tloc[0]="";	
						$mrr_tarr[0]=0;
						$mrr_tcnt=0;
						$mrr_tdates[0]="";
						
						//if(date("Y-m-d", strtotime("+$i days", $startdate)) == $dater)
						//{
						
						/*
						if($mrr_days_from_start>=0 && $mrr_days_from_start<=6)
						{	
							$mloc_res=mrr_find_all_pn_truck_time_tracking_last_local($dater);
							//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29') die("page time 1: ".show_page_time());
							$mrr_tlat=$mloc_res['latitude'];
     						$mrr_tlon=$mloc_res['longitude'];
     						$mrr_tloc=$mloc_res['location'];	
     						$mrr_tarr=$mloc_res['trucks'];
     						$mrr_tcnt=$mloc_res['num'];
     						$mrr_tdates=$mloc_res['date'];
     					}
     					*/				
     					
     					$stoplight_disp_flag=0;	
						
						for($i=0;$i < 7;$i++) 
						{
    							//}	
							$mrr_tot_disp_cntr=0;
							
							if($mrr_run_preloader > 0 && $_SERVER['REMOTE_ADDR'] == '70.90.229.29')
							{	
								$ires=mrr_load_board_from_preloader($disp_type, date("Y-m-d", strtotime("+$i days", $startdate)) );
								$mrr_tot_disp_cntr+=$ires['num'];	
								
								//$mrr_tot_disp_cntr+= mysqli_num_rows($data_available_loads);
          							
     							if($i < 5) {
     								$use_border = 'border_left';
     							} else {
     								$use_border = 'border_both_sides';
     							}
     							
     							$optional_day_flag = "";
     							if($i == 0 || $i == 6 || date("W", strtotime("+$i days", $startdate)) != date("W", time())) 		$optional_day_flag = "optional_day";
     							     							
     							if($week==0 && $mrr_tot_disp_cntr == 0)			$optional_day_flag .= " optional_week_day";	
     							
     							
     							$no_header_flag="";	
     							$no_info_flag="";
     				
     							$no_date_disp=date("Ymd", strtotime("+$i days", $startdate));
     				
     							if($no_date_disp < $today_checker  && $mrr_tot_disp_cntr == 0)
     							{
     								$no_header_flag=" mrr_col_".$comp_num."_id='".$no_date_disp."'";		//create the attribute for this column info... to select and remove.
     								$no_info_flag=" date_in_past_info mrr_date_".$no_date_disp." ";									
     							}
     							
     							$extra_class = '';
     							if(date("Y-m-d", strtotime("+$i days", $startdate)) == date("Y-m-d", time())) 		$extra_class = 'calendar_today';
								
								$info_columns.="<td align='left' class='$use_border calendar_text truck_drop $extra_class $optional_day_flag".$no_info_flag."' valign='top' nowrap linedate='".date("Y-m-d", strtotime("+$i days", $startdate))."'".$no_header_flag.">";
								
								$info_columns.=$ires['html'];		
														
								$info_columns.="&nbsp;</td>";
							}
							else
							{
								     /* pull out all the trucks in the log to display */
          							$sql = "
          								select trailers.trailer_name,
          									trucks_log.location,
          									trucks_log.dropped_trailer,
          									trucks_log.load_handler_id,
          									trucks_log.driver_id,
          									trucks_log.driver2_id,
          									trucks_log.customer_id,
          									customers.name_company as customer_namer,
          									drivers.name_driver_first,
          									drivers.name_driver_last,
          									drivers.jit_driver_flag,
          									drivers.phone_cell,
          									trucks.name_truck,
          									trucks.peoplenet_tracking,
          									trucks_log.id,
          									trailers.id as trailer_id,
          									trucks.id as truck_id,
          									trucks_log.miles,
          									trucks_log.miles_deadhead,
          									trucks_log.linedate_updated,
          									trucks_log.linedate,
          									trucks_log.color,
          									trucks_log.has_load_flag,
          									trucks_log.dispatch_completed,
          									trucks_log.origin,
          									trucks_log.origin_state,
          									trucks_log.destination,
          									trucks_log.destination_state,
          									(select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) as dedicated_trailer,
          									(
          										select ifnull(1,0) 
          										from load_handler_stops
          										where load_handler_stops.trucks_log_id=trucks_log.id 
          											and load_handler_stops.deleted=0 
          											and (load_handler_stops.linedate_completed IS NULL OR load_handler_stops.linedate_completed='0000-00-00 00:00:00')
          											and load_handler_stops.stoplight_warning_flag=1
          										limit 1
          									) as mrr_stoplight_1,
          									(
          										select ifnull(2,0)
          										from load_handler_stops 
          										where load_handler_stops.trucks_log_id=trucks_log.id 
          											and load_handler_stops.deleted=0 
          											and (load_handler_stops.linedate_completed IS NULL OR load_handler_stops.linedate_completed='0000-00-00 00:00:00')
          											and load_handler_stops.stoplight_warning_flag=2
          										limit 1
          									) as mrr_stoplight_2,
          									load_handler_stops.shipper_name,
          									load_handler_stops.shipper_city,
          									load_handler_stops.shipper_state,
          									load_handler_stops.stop_type_id,
          									load_handler_stops.linedate_pickup_eta,
          									load_handler_stops.id as load_handler_stop_id,
          									load_handler_stops.linedate_completed,
          									load_handler_stops.appointment_window,
          									load_handler_stops.linedate_appt_window_start,
          									load_handler_stops.linedate_appt_window_end,									
          									(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as mrr_start_trailer,
          									(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as mrr_end_trailer,
          									load_handler.special_instructions,
          									load_handler.load_number,
          									load_handler.deadhead_miles,
          									load_handler.estimated_miles,
          									load_handler.pickup_number,
          									load_handler.linedate_pickup_eta as mrr_linedate_eta,
          									(select max(id) as id from load_handler lh where lh.preplan = 1 and lh.preplan_driver_id = trucks_log.driver_id and lh.deleted = 0) as driver_preplanned,
          									(select count(*) from trucks_log_notes where deleted = 0 and truck_log_id = trucks_log.id) as note_count
          								
          								from trucks_log
          									left join drivers on drivers.id = trucks_log.driver_id
          									left join trucks on trucks.id = trucks_log.truck_id
          									left join trailers on trailers.id = trucks_log.trailer_id
          									left join load_handler_stops on load_handler_stops.trucks_log_id = trucks_log.id and load_handler_stops.deleted = 0
          									left join load_handler on load_handler.id = trucks_log.load_handler_id
          									left join customers on customers.id=load_handler.customer_id
          									
          								where trucks_log.deleted = 0
          									and trucks_log.linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."' 
          									and dispatch_completed = 0															
          									".$mrr_adder."																			
          									$sql_filter
          									$sql_id_filter
          									
          								order by trucks_log.dropped_trailer, drivers.name_driver_last, drivers.name_driver_first, trucks.name_truck, load_handler_stops.load_handler_id, load_handler_stops.linedate_pickup_eta
          							";	//".$mrr_adder2."
          							
          							/*
          								and (dispatch_completed = 0 or (dispatch_completed = 1 and trucks_log.dropped_trailer='1'))	
          							*/
          							
          							//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29') d($sql);
          							$data_log = simple_query($sql);
          							
          							$mrr_tot_disp_cntr+= mysqli_num_rows($data_log);
          							
          							$sql = "
          								select load_handler.*,
          									customers.name_company,
          									drivers.name_driver_last,
          									drivers.name_driver_first,
          									drivers.jit_driver_flag
          								
          								from load_handler
          									left join customers on customers.id = load_handler.customer_id
          									left join drivers on drivers.id = load_handler.preplan_driver_id
          								where load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime("+$i days", $startdate))."'
          									and load_handler.linedate_pickup_eta < '".date("Y-m-d 23:59:59", strtotime("+$i days", $startdate))."'
          									and load_handler.deleted = 0
          									and (
          										load_handler.load_available = 1
          										or 
          										(select count(*) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id) = 0
          									)
          									".$mrr_adder."	
          									$sql_filter2
          									$sql_id_filter2
          									
          								order by load_handler.preplan, drivers.name_driver_last, drivers.name_driver_first, load_handler.linedate_pickup_eta, load_handler.id
          							";							
          							//if($_SERVER['REMOTE_ADDR'] == '69.137.72.167') echo("<p>$sql");
          							$data_available_loads = simple_query($sql);
          							
          							$mrr_tot_disp_cntr+= mysqli_num_rows($data_available_loads);
          							
          							if($i < 5) {
          								$use_border = 'border_left';
          							} else {
          								$use_border = 'border_both_sides';
          							}
          							
          							$optional_day_flag = "";
          							if($i == 0 || $i == 6 || date("W", strtotime("+$i days", $startdate)) != date("W", time())) $optional_day_flag = "optional_day";
          							
          							
          							if($week==0 && $mrr_tot_disp_cntr == 0)
          							{
          								$optional_day_flag .= " optional_week_day";	
          							}
          							
          							$no_header_flag="";	
          							$no_info_flag="";
          				
          							$no_date_disp=date("Ymd", strtotime("+$i days", $startdate));
          				
          							if($no_date_disp < $today_checker  && $mrr_tot_disp_cntr == 0)
          							{
          								$no_header_flag=" mrr_col_".$comp_num."_id='".$no_date_disp."'";		//create the attribute for this column info... to select and remove.
          								$no_info_flag=" date_in_past_info mrr_date_".$no_date_disp." ";									
          							}
          							
          							$extra_class = '';
          							if(date("Y-m-d", strtotime("+$i days", $startdate)) == date("Y-m-d", time())) $extra_class = 'calendar_today';
          							$info_columns.="<td align='left' class='$use_border calendar_text truck_drop $extra_class $optional_day_flag".$no_info_flag."' valign='top' nowrap linedate='".date("Y-m-d", strtotime("+$i days", $startdate))."'".$no_header_flag.">";
          							
          
          							if(date("Y-m-d") == date("Y-m-d", strtotime("+$i days", $startdate))) {
          							
          								// see if we have any available drivers on this day
          								$available_driver_count = 0;
          								foreach($driver_avail_array as $darray) {
          	
          									$quick_quote_link=" <span style='cursor:pointer;margin-left:5px; color:#CC00CC;' onClick='mrr_quick_quote(0,".$darray['driver_id'].",0);'>Q</span>";
          									
          									// get a list of pre-planed loads available for this driver
          									$sql = "
          										select load_handler.*,
          											customers.name_company
          										
          										from load_handler, customers
          										where load_handler.preplan = 1
          											and customers.id = load_handler.customer_id
          											and load_handler.deleted = 0
          											".$mrr_adder."
          											and load_handler.preplan_driver_id = '".sql_friendly($darray['driver_id'])."'
          									";
          									$data_preplan = simple_query($sql);
          																											
          									//echo "(".$darray['customer_id'].")";
          									if(count($darray) > 1 && (($disp_type > 0 && $darray['customer_id'] == $disp_type) || ($disp_type == 0 && !in_array($darray['customer_id'], $cust_id_array)))) {
          										//if(date("Y-m-d", strtotime($darray['linedate_completed'])) == date("Y-m-d", strtotime("+$i days", $startdate))) {
          										
          										// get any attached trucks/trailers
          										$sql = "
          											select drivers.attached_truck_id,
          												drivers.attached_trailer_id,
          												trucks.name_truck,
          												trucks.peoplenet_tracking,
          												trailers.trailer_name,	
          												drivers.jit_driver_flag,
                                                       			(
                                                       				select truck_tracking.location
                                                            			from ".mrr_find_log_database_name()."truck_tracking
                                                            			where truck_tracking.truck_id=drivers.attached_truck_id
                                                            				and drivers.attached_truck_id > 0
                                                            			order by truck_tracking.linedate desc,truck_tracking.id desc
                                                            			limit 1
                                                       			) as mrr_last_city										
          											from drivers
          												left join trucks on trucks.id = drivers.attached_truck_id
          												left join trailers on trailers.id = drivers.attached_trailer_id
          											where drivers.id = '".sql_friendly($darray['driver_id'])."'
          										";
          										
          										$data_attached = simple_query($sql);
          										$row_attached = mysqli_fetch_array($data_attached);
          										
          										$last_load_dedicated=mrr_get_last_driver_load_dedicated($darray['driver_id']);
          										$mrr_last_city="$darray[last_city]";
          										
          										
          										// && $last_load_dedicated==0                          .customer_id='".$disp_type."'
          										if(($disp_type==0 && $last_load_dedicated==0) || ($disp_type > 0 && $last_load_dedicated==1) )
          										{    // 	$name_company!="Dedicated Loads"
          											
          											$available_driver_count++;
          											if($available_driver_count == 1) {
          												$info_columns.="<div class='load_board_section_header'>Available Drivers</div>";
          											}
          											
          											$pn_trucker_msg="";
          											if($row_attached['peoplenet_tracking'] > 0 )
          											{
          												//$mrr_pn_user_fun="preplan_note_image";
          												//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		$mrr_pn_user_fun="pn_note_image_full";	
          												$mrr_pn_user_fun="truck_note_image_full";	
          											
          												$pn_trucker_msg="
          													<img truck_id='".$row_attached['attached_truck_id']."' log_id='0' load_id='0' load_eta='".date("Y-m-d",time())." 00:00:00' class='".$mrr_pn_user_fun."' src='/images/note_msg.png' border='0' style='display:inline;height:16px;cursor:pointer;margin-left:5px;margin-right:5px;'>
          													<div id='truck_note_holder_".$row_attached['attached_truck_id']."' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
          												";	
          																							
          												
          												if($row_attached['attached_truck_id'] > 0)		
          												{
          													//$mrr_last_city=mrr_get_simple_pn_location_for_truck_id($row_attached['attached_truck_id']);
          													
          													$mrr_last_city="PN: ".$row_attached['mrr_last_city']."";
          													
          													$mrr_last_city=str_replace("Nashville, TN and In La Vergne, TN On I-24","La Vergne, TN",$mrr_last_city);
          													$mrr_last_city=str_replace("Nashville, TN and In La Vergne, TN Near I-24","La Vergne, TN",$mrr_last_city);
          													$mrr_last_city=str_replace("Nashville, TN and In Smyrna, TN On I-24","Smyrna, TN",$mrr_last_city);	
          												}
          											}
          											
          											$mrr_valid_driver_flag=mrr_confirm_driver_available($darray['driver_id']);
          											
          											$d_hours_display="";
          											$max_hours_allowed=40;
                                                  			$dddres=mrr_driver_hours_calc($darray['driver_id'],date("m/d/Y"));
                                                  			$approx_hours=$dddres['hours'];
                                                  			$approx_hours2=$dddres['hours2'];
                                                  			$preplan_hours=$dddres['planned'];
                                                  			
                                                  			$dddtag1="";
                                                       		$dddtag2="";
                                                       		if(($approx_hours2 + $preplan_hours) > $max_hours_allowed)
                                                       		{
                                                       			$dddtag1="<span class='alert' title='Driver may have more than ".$max_hours_allowed." hours scheduled (Dispatch Hours + Preplan Hours)...this may be an error. Possible DOT violation.'><b>";
                                                       			$dddtag2="</b></span>";	
                                                       		}
                                                       		
                                                  			$d_hours_display="
                                                       			<div style=''> 
                                                       				Driver Hours for ".$dddres['from']." - ".$dddres['to'].": 
                                                       				".number_format($approx_hours2,2)." + ".$dddtag1."".number_format($preplan_hours,2)."".$dddtag2." Preplanned. 
                                                       				Last Week: ".number_format($approx_hours,2).".
                                                       			</div>
                                                  			";
                                                  			
          											if(mysqli_num_rows($data_preplan)==0 && $mrr_valid_driver_flag==0)
          											{     											
               											$capture_avail_driver="";
               											
               											// title='Dedicated=".$last_load_dedicated."'
               											$capture_avail_driver.="
               											<div class='hover_for_details_drivers' style='background-color:#f9d42d;font-weight:bold;color:white' id='available_driver_holder_$darray[driver_id]'>
               												
               												<div style='float:left'>   													
               												
               													<a href=\"javascript:remove_available_driver($darray[driver_id],'".str_replace("'","", $darray['name_driver_first'].' '.$darray['name_driver_last'])."')\">[x]</a>
               													<a href=\"javascript:has_load_toggle($darray[driver_id])\">(Available)</a> 
               													
               													<span class='mrr_link_like_on' onClick='mrr_show_this_available(".$darray['driver_id'].");'><b>Show</b></span>
               													
               													<a href=\"javascript:edit_driver_notes($darray[driver_id])\">".date("n/d", strtotime($darray['linedate_completed']))."<span class='".($row_attached['jit_driver_flag'] > 0 ? "jit" : "non_jit")."'> $darray[name_driver_first] $darray[name_driver_last]</span> (".$mrr_last_city.")</a>
               													     													
               													".( $row_attached['peoplenet_tracking'] > 0 ? "<a href=\"peoplenet_interface.php?find_load_id=0&find_truck_id=".$row_attached['attached_truck_id']."&auto_run=1\" target='_blank' title='View PeopleNet Tracking'>PN</a>" : "")." 
               													".$pn_trucker_msg." ".$quick_quote_link."    													 
               												</div>
               												<img src='images/driver_small.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_driver_history($darray[driver_id])\" alt='Driver History' title='Driver History'>
               												<img src='images/inventory.png' id='driver_has_load_$darray[driver_id]' style='width:16px;height:16px;float:left;margin-left:5px;".(isset($darray['driver_has_load']) && $darray['driver_has_load'] ? "" : "display:none")."' alt='Driver Has Load' title='Driver Has Load'>
               														
               												
               												<div style='clear:both'></div>
               												
               												<div class='available_driver_details available_driver_details_$darray[driver_id]' driver_id='$darray[driver_id]'>
               												".$d_hours_display."
               											";	
               											
               											//<div style=''>".$row_preplan['estimated_miles']." mi + ".$row_preplan['deadhead_miles']." dh = ".($row_preplan['deadhead_miles']+$row_preplan['estimated_miles'])." Miles</div>
                    										          										
                    										/*
                    										<img src='images/note.png' id='driver_available_notes_$darray[driver_id]' onclick='edit_driver_notes($darray[driver_id])'style='cursor:pointer;width:16px;height:16px;float:left;margin-left:5px;".(isset($darray['available_notes']) && $darray['available_notes'] != ''? "" : "display:none")."' alt='Notes' title='Notes'>
                    												
                    										*/     										
               
                    										if($row_attached['attached_truck_id']) {
                    											
                    											$warning_flag="";	//mrr_get_equipment_maint_notice_warning('truck',$row_attached['attached_truck_id']);		
               												for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
                                                       				{
                                                       					if($mrr_maint_truck_arr[ $rr ] == $row_attached['attached_truck_id'])		$warning_flag=trim($mrr_maint_truck_links[ $rr ]);
                                                       				}
                                                       				
                    											$capture_avail_driver.="
                    												<div style='margin-left:30px;float:left' class='attached_truck_$row_attached[attached_truck_id]' truck_name=\"$row_attached[name_truck]\" driver_name=\"".$darray['name_driver_first'].' '.$darray['name_driver_last']."\">
                    													<div style='float:left'><a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'>[detach]</a></div>
                    													
                    													<div style='float:left;margin-left:5px;color:black'>Truck: $row_attached[name_truck]</div>
                    													<img src='images/truck_info.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_truck_history($row_attached[attached_truck_id])\" alt='Truck History' title='Truck History'> ".$warning_flag."
                    													<div style='clear:both'></div>
                    												</div>
                    												<div style='clear:both'></div>
                    											";
                    										}
                    										if($row_attached['attached_trailer_id']) {
                    											
                    											$warning_flag="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_attached['attached_trailer_id']);	
               												for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                                                       				{
                                                       					if($mrr_maint_trailer_arr[ $rr ] == $row_attached['attached_trailer_id'])		$warning_flag=trim($mrr_maint_trailer_links[ $rr ]);
                                                       				}
                    											$capture_avail_driver.="
                    												<div style='margin-left:30px;float:left' class='attached_trailer_$row_attached[attached_trailer_id]' trailer_name=\"$row_attached[trailer_name]\" driver_name=\"".$darray['name_driver_first'].' '.$darray['name_driver_last']."\">
                    													<a href='javascript:void(0)' onclick='detach_trailer($row_attached[attached_trailer_id],$darray[driver_id])'>[detach]</a> 
                    													<span style='color:black'>Trailer: $row_attached[trailer_name]</span> ".$warning_flag."
                    												</div>
                    												<div style='clear:both'></div>
                    											";
                    										}
                    									     while($row_preplan = mysqli_fetch_array($data_preplan)) 
                    									     {          											
                                   								$load_and_delivery_display="";
                                                                      	if(isset($row_preplan['load_number']) && trim($row_preplan['load_number'])!="")
                                                                      		$load_and_delivery_display.="<span class='load_and_delivery_display1'>LD# ".$row_preplan['load_number']."</span> ";
                                                                      	if(isset($row_preplan['pickup_number']) && trim($row_preplan['pickup_number'])!="")
                                                                      		$load_and_delivery_display.="<span class='load_and_delivery_display2'>PU# ".$row_preplan['pickup_number']."</span> ";
                                                                      	
                                                                      	if(trim($load_and_delivery_display)!="")		$load_and_delivery_display="<div class='load_and_delivery_display'>".$load_and_delivery_display."</div>";	
                                   								//".$load_and_delivery_display."	
                                   										
                    											$capture_avail_driver.="
                    												<div style='clear:both'></div>
                    												
                    												<div style='margin-left:30px'>
                    													<a class='available_load_link' href='javascript:edit_entry_truck(0,0,$row_preplan[id])'>
                    														$row_preplan[id] 
                    														- $row_preplan[name_company] 
                    														- $row_preplan[origin_city], $row_preplan[origin_state] ".date("H:i", strtotime($row_preplan['linedate_pickup_eta']))."
                    														- $row_preplan[dest_city], $row_preplan[dest_state]  ".date("H:i", strtotime($row_preplan['linedate_dropoff_eta']))."
                    													</a>
                    													
                    												</div>
                    												<div style='clear:both'></div>
                    											";
                    										}
                    										
                    										
                    										$capture_avail_driver.="<div class='avail_driver_hours avail_driver_".$darray['driver_id']."' mrr_driver_id='".$darray['driver_id']."'></div>";
                    										         										
                    										
                    										$capture_avail_driver.="
                    												
                    												<div class='mrr_link_like_on' onClick='mrr_hide_this_available(".$darray['driver_id'].");'><b>Hide Details</b></div>
                    												</div>
                    											<div style='clear:both'></div>
                    											</div>
                    										";
                    										
                    										$info_columns.=trim($capture_avail_driver);
                    										
                    										if($mrr_save_preloader > 0 && $disp_type==0)
                    										{
                    											$capt_board=$disp_type;
                    											$capt_special=0;
                    											$capt_load=0;
                    											$capt_disp=0;
                    											$capt_driver=$darray['driver_id'];
                    											$capt_cust=0;
                    											$capt_truck=0;
                    											$capt_trailer=0;
                    											$capt_note=0;
                    											mrr_create_load_board_preload($capt_board,$capt_special,$capt_load,$capt_disp,$capt_driver,$capt_cust,$capt_truck,$capt_trailer,date("m/d/Y"),$capture_avail_driver,$capt_note);
                    										}
                    									}
          										}//end IF NOT DEDICATED LOAD check
          									
          									}
          								}
          							}
          
          
          							$use_day = date("d", strtotime("$i day", $startdate));
          							if(isset($notes_day_array[$use_day])) 
          							{
          								foreach($notes_day_array[$use_day] as $note_entry) 
          								{
          									
          									$capture_avail_load="
          										<div class='note_entry'>
          											<div note_id='$note_entry[id]' onclick='edit_note_date($note_entry[id],0,0)' class='note_entry_inside'>
          											".trim_string($note_entry['desc_long'], 45)."
          											</div>
          										</div>
          									";
          									
          									$info_columns.= trim($capture_avail_load);
          									
          									if($mrr_save_preloader > 0)
          									{
          										$capt_board=$disp_type;
          										$capt_special=0;
          										$capt_load=0;
          										$capt_disp=0;
          										$capt_driver=0;
          										$capt_cust=0;
          										$capt_truck=0;
          										$capt_trailer=0;
          										$capt_note=$note_entry['id'];
          										mrr_create_load_board_preload($capt_board,$capt_special,$capt_load,$capt_disp,$capt_driver,$capt_cust,$capt_truck,$capt_trailer,$note_entry['linedate'],$capture_avail_load,$capt_note);
          									}
          								}
          							}
          							
          							$loop_count = 0;
          							$lh_used_id = array();
          							$preplan_count = 0;
          							$regular_count = 0;
          							while($row_available = mysqli_fetch_array($data_available_loads)) 
          							{
          								if(!in_array($row_available['id'],$lh_used_id)) 
          								{									
          									$capture_avail_load="";
          									
          									if($row_available['preplan'] == '1') {
          										// preplan
          										$preplan_count++;
          										if($preplan_count == 1) {
          											$capture_avail_load.="
          												<div class='load_board_section_header'>Preplan Loads</div>
          											";
          										}
          									} else {
          										// regular available
          										$regular_count++;
          										if($regular_count == 1) {
          											$capture_avail_load.="
          												<div class='load_board_section_header'>Available Loads</div>
          											";
          										}
          									}
          									
          									$load_and_delivery_display="";
                                                  	if(isset($row_available['load_number']) && trim($row_available['load_number'])!="")
                                                  		$load_and_delivery_display.="<span class='load_and_delivery_display1'>LD# ".$row_available['load_number']."</span> ";
                                                  	if(isset($row_available['pickup_number']) && trim($row_available['pickup_number'])!="")
                                                  	{
                                                  		if($load_and_delivery_display!="")		$load_and_delivery_display.="<br>";
                                                  		$load_and_delivery_display.="<span class='load_and_delivery_display2'>PU# ".$row_available['pickup_number']."</span> ";
                                                  	}
                                                  	if(trim($load_and_delivery_display)!="")		$load_and_delivery_display="<div class='load_and_delivery_display'>".$load_and_delivery_display."</div>";	
               								
          									
          									$lh_used_id[] = $row_available['id'];
          									// get a list of all the stops for this load
          									$sql = "
          										select load_handler_stops.*,
          											(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
          											(select trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name
          										
          										from load_handler_stops
          										where load_handler_id = '".sql_friendly($row_available['id'])."'
          											and deleted = 0
          										order by linedate_pickup_eta
          									";
          									
          									$data_avail_stops = simple_query($sql);
          									
          									$stop_count = 0;
          									$stop_var = "";
          									
          									$pn_driver_id=$row_available['preplan_driver_id'];
          									$pn_driver2_id=$row_available['preplan_driver2_id'];
          									$pn_leg2_stop_id=$row_available['preplan_leg2_stop_id'];
          									$pn_driver3_id=$row_available['preplan_leg2_driver_id'];
          									$pn_driver4_id=$row_available['preplan_leg2_driver2_id'];
          									
          									$quick_quote_link=" <span style='cursor:pointer;margin-left:5px; color:#CC00CC;' onClick='mrr_quick_quote(0,".$pn_driver_id.",0);'>Q</span>";
          									
          									while($row_avail_stops = mysqli_fetch_array($data_avail_stops)) {
          										$stop_count++;
          										$loc_holder = "$row_avail_stops[shipper_city], $row_avail_stops[shipper_state]";
          										if($stop_count == 1) {
          											$loc_start = $loc_holder;
          										}
          										
          										$tswitcher=$row_avail_stops['start_trailer_name'];
          										if($row_avail_stops['start_trailer_id'] != $row_avail_stops['end_trailer_id'])
          										{
          											if($row_avail_stops['end_trailer_id'] > 0)
          												$tswitcher.=" Dropped.";
          											else	
          												$tswitcher.=" Switched to ".$row_avail_stops['end_trailer_name'].".";
          										}
          										//&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Trailer: ".$tswitcher."
          										
          																				
          										$new_leg="";
          										if($pn_leg2_stop_id==$row_avail_stops['id'])
          										{
          											$driver3=mrr_get_driver_name($pn_driver3_id);
          											$driver4="";
          											if($pn_driver4_id > 0)	$driver4=" and ".mrr_get_driver_name($pn_driver4_id);
          											
          											$new_leg="
               											<div>
               												<div style='float:left'><span style='color:#FFFFFF;'><b>".$driver3."".$driver4."</b></span></div>
               												<div style='float:right'><span style='color:#FFFFFF;'><b>Leg 2 Starts Here</b></span></div>
               											</div>
               											<div style='clear:both'></div>
          											";	
          										}
          										
          										$stop_var .= "
          											".$new_leg."
          											<div>
          												<div style='float:left'>
          												(".($row_avail_stops['stop_type_id'] == 1 ? "S" : "C").")
          												".$row_avail_stops['shipper_name'].": $row_avail_stops[shipper_city], $row_avail_stops[shipper_state]
          												
          												</div>
          												<div style='float:right'>".($row_avail_stops['linedate_pickup_eta'] > 0 ? " - " . date("M j, H:i", strtotime($row_avail_stops['linedate_pickup_eta'])) : "")."</div>
          											</div>
          											<div style='clear:both'></div>
          										";
          									}
          									$loc_end = $loc_holder;
          									
          									$pn_preplan="";
          									$pn_preplan_msg="";
          									
          									
          									$pn_truck_id=mrr_find_peoplenet_truck_id_by_driver($pn_driver_id);
          									if($row_available['preplan'] > 0 && $pn_truck_id>0)
          									{										
          										$pn_class=mrr_peoplenet_link_class_by_date($pn_truck_id,$row_available['id'],1);
          										
          										$pn_preplan="<a class='".$pn_class."' href=\"peoplenet_interface.php?find_preplan=1&find_load_id=".$row_available['id']."&find_truck_id=".$pn_truck_id."&find_driver_id=".$pn_driver_id."&auto_run=1\" target='_blank' title='View PeopleNet Tracking'>PN</a>";	
          										
          										$pn_preplan_msg="<a class='".$pn_class."' href=\"peoplenet_messager.php?truck_id=".$pn_truck_id."\" target='_blank' title='View PeopleNet Tracking Messages'>MSGS</a>";
          									
          									
          										//$mrr_pn_user_fun="preplan_note_image";
          										//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		$mrr_pn_user_fun="pn_note_image_full";	
          										$mrr_pn_user_fun="preplan_note_image_full";	
          										
          										$pn_preplan_msg="
          											<img truck_id='".$pn_truck_id."' log_id='0' load_id='".$row_available['id']."' load_eta='".$row_available['linedate_pickup_eta']."' class='".$mrr_pn_user_fun."' src='/images/note_msg.png' border='0' style='display:inline;height:16px;cursor:pointer;margin-left:5px;margin-right:5px;'>
          											<div id='preplan_note_holder_".$row_available['id']."' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
          											";								
          									}	
          									$driver2="";
          									if($pn_driver2_id > 0)		$driver2=" and ". mrr_get_driver_name($pn_driver2_id);																
          									
          									
          									$mrr_attachment_list=mrr_display_simple_attachment_list(8,$row_available['id']);
          									
          									
          									
          									$d_hours_display="";
          									$max_hours_allowed=40;
                                        			$dddres=mrr_driver_hours_calc($pn_driver_id,date("m/d/Y"));
                                        			$approx_hours=$dddres['hours'];
                                        			$approx_hours2=$dddres['hours2'];
                                        			$preplan_hours=$dddres['planned'];
                                        			
                                        			$dddtag1="";
                                             		$dddtag2="";
                                             		if(($approx_hours2 + $preplan_hours) > $max_hours_allowed)
                                             		{
                                             			$dddtag1="<span class='alert' title='Driver may have more than ".$max_hours_allowed." hours scheduled (Dispatch Hours + Preplan Hours)...this may be an error. Possible DOT violation.'><b>";
                                             			$dddtag2="</b></span>";	
                                             		}
                                             		
                                        			$d_hours_display="
                                             			<div style='color:#000000;'> 
                                             				Driver Hours for ".$dddres['from']." - ".$dddres['to'].": 
                                             				".number_format($approx_hours2,2)." + ".$dddtag1."".number_format($preplan_hours,2)."".$dddtag2." Preplanned. 
                                             				Last Week: ".number_format($approx_hours,2).".
                                             			</div>
                                        			";
          									
          									
          									$capture_avail_load.="
          										<div class='hover_for_details ".($row_available['preplan'] > 0 ? "preplan_load_entry" : "available_load_entry")."'>
          											<span class='mrr_link_like_on' onClick='mrr_show_this_dispatch(".$row_available['id'].");'>Show</span>
          											<a href='manage_load.php?load_id=$row_available[id]' log_id='$row_available[id]' target='view_load_".$row_available['id']."'>"
          												.($row_available['preplan'] > 0 ? "($row_available[id]) <span class='".($row_attached['jit_driver_flag'] > 0 ? "jit" : "non_jit")."'>$row_available[name_driver_first] $row_available[name_driver_last]".$driver2."</span>: " : "($row_available[id]):").
          												"  $row_available[name_company]
          											</a>
          											<span style='display:inline-block;'><a href='add_entry_truck.php?load_id=".$row_available['id']."&id=0' target='preplan_".$row_available['id']."'><img src='images/menu_system16.png' style='cursor:pointer;float:left' alt='Add Dispatch' title='Add Dispatch'></a></span>
          											$loc_start / $loc_end 
          											
          											".$pn_preplan." ".$pn_preplan_msg." ".$quick_quote_link."												
          									";
          									
          									
          									$mrr_test_hours="";
          									if($row_available['preplan_driver_id'] > 0)
          									{
          										$mrr_test_hours="<div class='preplan_driver_hours preplan_driver_".$row_available['preplan_driver_id']."' mrr_driver_id='".$row_available['preplan_driver_id']."'></div>";	
          									}
          									
          									$capture_avail_load.="<div class='load_stop_details load_stop_details_$row_available[id]' log_id='$row_available[id]'>
          													<div class='mrr_link_like_on' onClick='mrr_hide_this_dispatch(".$row_available['id'].");'>Hide Details</div>
          													".$d_hours_display."
          													".$load_and_delivery_display."
          													".$stop_var."
          													".$mrr_attachment_list."															
          													".$mrr_test_hours."											
          												</div>";
          									
          									$capture_avail_load.="
          										<div style='clear:both'></div>
          										</div>
          										
          									";
          									
          									$info_columns.=trim($capture_avail_load);
          									
          									if($mrr_save_preloader > 0)
                    							{
                    								$capt_board=$disp_type;
                    								$capt_special=0;				if($row_available['preplan'] > 0)			$capt_special=1;		//0=Available Load / 1= Preplanned Load
                    								$capt_load=$row_available['id'];
                    								$capt_disp=0;
                    								$capt_driver=0;				if($row_available['preplan_driver_id'] > 0)	$capt_driver=$row_available['preplan_driver_id'];
                    								$capt_cust=$row_available['customer_id'];;
                    								$capt_truck=0;					if($pn_truck_id > 0)					$capt_truck=$pn_truck_id;
                    								$capt_trailer=0;
                    								$capt_note=0;
                    								mrr_create_load_board_preload($capt_board,$capt_special,$capt_load,$capt_disp,$capt_driver,$capt_cust,$capt_truck,$capt_trailer,date("m/d/Y",strtotime($row_available['linedate_pickup_eta'])),$capture_avail_load,$capt_note);
                    							}
          								}
          
          							}
          							
          							$last_truck_log_id = 0;
          							$loop_count = 0;
          							$dropped_trailer_count = 0;
          							
          							$mrr_load_block_started="";
          							$mrr_load_block_waiting="";
          							
          							while($row_log = mysqli_fetch_array($data_log)) {
          								$loop_count++;
          								$last_truck_log_id = $row_log['id'];
          															
          								$date_updated = strtotime(date("m/d/Y", strtotime($row_log['linedate_updated'])));
          								$date_current = strtotime(date("m/d/Y", time()));
          
          								$extra_class = '';
          								$trailer_alert = false;
          								if($date_updated < $date_current || $date_updated == '') {
          									// hasn't been updated today, show an 'alert' background color
          									// if this is a weekend or a monday, allow the days to go back a ways
          								}
          								if(strtotime($row_log['linedate']) < strtotime(date("Y-m-d", time()))) {
          									$extra_class = 'update_alert';
          									$trailer_alert = true;
          								}
          								
          								if(!$trailer_alert && $row_log['dropped_trailer'] > 0) {
          									//$use_background_color = ";background-color:#33ff00";
          								} elseif($row_log['color'] != '' && !$trailer_alert && $row_log['color'] != '#f8f3e4') {
          									//$use_background_color = ";background-color:$row_log[color]";
          								} else {
          									$use_background_color = '';
          								}
          								
          								if($row_log['location'] != '') {
          									$use_location = $row_log['location'];
          								} else {
          									$use_location = "<span class='mrr_origin_local_starter'>$row_log[origin], $row_log[origin_state]</span class='mrr_origin_local_ender'> to $row_log[destination], $row_log[destination_state]";												
          								}
          								
          								//(select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) as dedicated_trailer,
          								
          								if($row_log['dropped_trailer'] > 0 && 1==2) {
          
          										//THIS TRAILER DROP SECTION NO LONGER USED... SEE LOWER TRAILER DROP SECTION.
               									$info_columns.="
               										<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:300px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
               											<span truck_id='$row_log[truck_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'>
               											<a style='margin-right:10px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">TL DR</a>
               											<span style='float:left;color:black;'>".trim_string($use_location,45)."</span>
               											<span style='float:right;color:black;'>$row_log[trailer_name]</span>&nbsp;
               											</span>
               											<div style='clear:both'></div>
               										</div>
               									";
          								} else {
          									$has_load_flag = 0;
          									$has_preplan = 0;
          									if($row_log['dispatch_completed']) {
          										$use_image = "images/good.png";
          									} elseif ($row_log['driver_preplanned']) {
          										$use_image = "images/inventory.png";
          										$has_preplan = $row_log['driver_preplanned'];
          									} elseif ($row_log['has_load_flag']) {
          										$use_image = "images/inventory.png";
          										$has_load_flag = 1;
          									} else {
          										$use_image = "images/note.png";
          									}
          									
          									$warning_flag1="";	//mrr_get_equipment_maint_notice_warning('truck',$row_log['truck_id']);		
          									for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
                                                  	{
                                                  		if($mrr_maint_truck_arr[ $rr ] == $row_log['truck_id'])		$warning_flag1=trim($mrr_maint_truck_links[ $rr ]);
                                                  	}
                                                  	
          									$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_log['trailer_id']);
          									for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                                                  	{
                                                  		if($mrr_maint_trailer_arr[ $rr ] == $row_log['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                                                  	}
                                                  	
                                                  	if($loop_count == 1) {
                                                  		$info_columns.="<div class='load_board_section_header'>Loads</div>";
                                                  	}
                                                  	$pn_class=mrr_peoplenet_link_class_by_date($row_log['truck_id'],$row_log['id'],0);
                                                  	
                                                  	$dist_res="";
                                                  	$dist_java="";
                                                  	
                                                  	/*
                                                  	if($row_log['peoplenet_tracking'] > 0 && trim($pn_class)!="")
                                                  	{	// && date("Y-m-d", strtotime("+$i days", $startdate)) == $dater && trim($pn_class)!="peoplenet_link_not_sent"
                                                  		$mfound=0;
                                                  		$mrrindex=0;
                                                  		$longitude="0";
                                                  		$latitude="0";
                                                  		$mrrlocal="";
                                                  		$mrrstamper="";
                                                  		for($xx=0;$xx < $mrr_tcnt; $xx++)
                                                  		{
                                                  			if(	$mrr_tarr[$xx]==$row_log['truck_id'] )	
                                                  			{
                                                  				$mrrindex=$xx;		$mfound=1;
                                                  			}
                                                  		}
                                                  		if($mfound > 0)
                                                  		{
                                                  			$latitude=$mrr_tlat[ $mrrindex ];
                                                  			$longitude=$mrr_tlon[ $mrrindex ];                                        		
                                                  			$mrrlocal=$mrr_tloc[ $mrrindex ];	
                                                  			$mrrstamper=$mrr_tdates[ $mrrindex ];
                                                  		}
                                                  		
                                                  		//$dist_res=mrr_peoplenet_get_last_position_distanct_and_location($row_log['truck_id'],$row_log['id']);
                                                  		$dist_res=mrr_peoplenet_get_last_position_distanct_and_locationV2($row_log['truck_id'],$row_log['id'],$longitude,$latitude,$mrrlocal);       
                                                  		if(trim($dist_res)!="")		$dist_res.="<br>As of ".$mrrstamper."";                 		
                                                  	}
                                                  	
                                                  	*/
                                                  	
                                                  	if($row_log['peoplenet_tracking'] > 0 && trim($pn_class)!="")
                                                  	{
                                                  		//mrr_gen_truck_local_map_by_google($row_log['load_handler_id'],$row_log['name_truck'],$truck_lat,$truck_long);
                                                  		
                                                  		$dist_res="<div id='truck_local_".$row_log['load_handler_id']."' class='truck_".$row_log['truck_id']."_local'></div>";
                                                  		$dist_java=" onMouseOver='mrr_find_truck_locator(".$row_log['truck_id'].",".$row_log['load_handler_id'].");'";
                                                  	}
                                                  	
                                                  	$customer_linker="<a href='admin_customers.php?eid=".$row_log['customer_id']."' target='_blank'>".$row_log['customer_namer']."</a>";
                                                  	
                                                  	
                                                  	//Added May 2013...sorting loads based on wtops started...(first/a stop completed)
                                                  	$load_started_already=0;
                                                  	$mrr_load_block="";
                                                  	                                        	
          									$pn_msg_viewer="";	
          									$quick_quote_link="";	
          									
          									if($row_log['peoplenet_tracking'] > 0)
          									{
          										$mrr_pn_user_fun2="pn_note_image";
          										//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		$mrr_pn_user_fun="pn_note_image_full";	
          										$mrr_pn_user_fun="pn_note_image_full";	
          										
          										$pn_msg_viewer="
          											<img truck_id='".$row_log['truck_id']."' log_id='".$row_log['id']."' load_id='".$row_log['load_handler_id']."' load_eta='".$row_log['mrr_linedate_eta']."' class='".$mrr_pn_user_fun."' src='/images/note_msg.png' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;'>
          											<span style='height:16px;cursor:pointer;margin-right:5px;float:left; color:#00CC00;' truck_id='".$row_log['truck_id']."' log_id='".$row_log['id']."' load_id='".$row_log['load_handler_id']."' load_eta='".$row_log['mrr_linedate_eta']."' class='".$mrr_pn_user_fun2."'>...History</span>
          										";
          										//
          										
          										/*
          										$msg_date_from=date("Y-m-d",strtotime("-1 day", time()));
          										$msg_date_to=date("Y-m-d",time());
          										$msg_truck_id=$row_log['truck_id'];
          										$msg_truck_name=trim($row_log['name_truck']);
          										
          										if($msg_truck_id>0)
          										{
          											$mrr_tab_sent="";
          											$mrr_tab_recv="";
          											
          											$mrr_tab_sent=mrr_get_messages_sent_by_truck($msg_date_from, $msg_date_to, $msg_truck_id, $msg_truck_name, 0, 0, 3);
          											$mrr_tab_recv=mrr_get_messages_sent_out_to_truck($msg_date_from, $msg_date_to,  $msg_truck_id, $msg_truck_name, 0, 0, 3);
          											
          											$mrr_load_block.= "
          												<div class='pn_messages_block'>
          													<table cellpadding='2' cellspacing='2' border='0'> 
          														".$mrr_tab_sent."
          													</table>
          													<br>
          													<table cellpadding='2' cellspacing='2' border='0'> 
          														".$mrr_tab_recv."
          													</table>
          												</div>
          												<div style='clear:both'></div>
          											";
          											
          										}	
          										*/
          									}
          									else
          									{
          										$mrr_pn_user_fun2="phone_note_image";
          										//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		$mrr_pn_user_fun="phone_note_image_full";	
          										$mrr_pn_user_fun="phone_note_image_full";	
          										
          										$pn_msg_viewer="
          											<img truck_id='".$row_log['truck_id']."' log_id='".$row_log['id']."' load_id='".$row_log['load_handler_id']."' load_eta='".$row_log['mrr_linedate_eta']."' class='".$mrr_pn_user_fun."' src='/images/note_msg.png' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;'>
          											<span style='height:16px;cursor:pointer;margin-right:5px;float:left; color:#CC0000;' truck_id='".$row_log['truck_id']."' log_id='".$row_log['id']."' load_id='".$row_log['load_handler_id']."' load_eta='".$row_log['mrr_linedate_eta']."' class='".$mrr_pn_user_fun2."'>...History</span>
          										";	
          									}
                                                  	//	
                                                  	$load_and_delivery_display="";
                                                  	if(isset($row_log['load_number']) && trim($row_log['load_number'])!="")
                                                  		$load_and_delivery_display.="<span class='load_and_delivery_display1'>LD# ".$row_log['load_number']."</span> ";
                                                  	if(isset($row_log['pickup_number']) && trim($row_log['pickup_number'])!="")
                                                  		$load_and_delivery_display.="<span class='load_and_delivery_display2'>PU# ".$row_log['pickup_number']."</span> ";
                                                  	
                                                  	if(trim($load_and_delivery_display)!="")		$load_and_delivery_display="<div class='load_and_delivery_display'>".$load_and_delivery_display."</div>";
                                                  	
                                                  	//".trim_string($use_location,45)."
                                                  	
                                                  	$quick_quote_link=" <span style='height:16px;cursor:pointer;margin-right:5px;float:left; color:#CC00CC;' onClick='mrr_quick_quote(".$row_log['truck_id'].",".$row_log['driver_id'].",".$row_log['trailer_id'].");'>Q</span>";
                                                  	
                                                  	
                                                  	$mrr_attachment_list=mrr_display_simple_attachment_list(8,$row_log['load_handler_id']);
                                                  	
                                                  	$mrr_stoplight_img="<img src='images/stoplight_green_top.png' alt='Green' width='10' height='30' border='0'>";
                                                  	
                                                  	if($row_log['mrr_stoplight_2'] > 0)
                                                  	{	//always use red light first...
                                                  		$mrr_stoplight_img="<img src='images/stoplight_red_top.png' alt='Red' width='10' height='30' border='0'>";
                                                  	}
                                                  	elseif($row_log['mrr_stoplight_1'] > 0)
                                                  	{	//then use yellow lights
                                                  		$mrr_stoplight_img="<img src='images/stoplight_yellow_top.png' alt='Yellow' width='10' height='30' border='0'>";
                                                  	}
                                                  	
                                                  	$d_hours_display="";
          									$max_hours_allowed=40;
                                        			$dddres=mrr_driver_hours_calc($row_log['driver_id'],date("m/d/Y"));
                                        			$approx_hours=$dddres['hours'];
                                        			$approx_hours2=$dddres['hours2'];
                                        			$preplan_hours=$dddres['planned'];
                                        			
                                        			$dddtag1="";
                                             		$dddtag2="";
                                             		if(($approx_hours2 + $preplan_hours) > $max_hours_allowed)
                                             		{
                                             			$dddtag1="<span class='alert' title='Driver may have more than ".$max_hours_allowed." hours scheduled (Dispatch Hours + Preplan Hours)...this may be an error. Possible DOT violation.' style='display:inline-block;'><b>";
                                             			$dddtag2="</b></span>";	
                                             		}
                                             		
                                        			$d_hours_display="
                                             			<div style='color:#000000; float:right;'> 
                                             				Driver Hours for ".$dddres['from']." - ".$dddres['to'].": 
                                             				".number_format($approx_hours2,2)." + ".$dddtag1."".number_format($preplan_hours,2)."".$dddtag2." Preplanned. 
                                             				Last Week: ".number_format($approx_hours,2).".
                                             			</div>
                                        			";
                                        			                                        	                                        																											
          									$mrr_load_block.= "
          										<div class='hover_for_details truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:480px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
          											<span truck_id='$row_log[truck_id]' load_handler_id='$row_log[load_handler_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'>
          												<div style='float:left;width:20px;padding-left:5px;'>	
          													".$mrr_stoplight_img."
          												</div>
          												<div style='float:left;width:110px;'>													
          													<img log_id='$row_log[id]' class='note_image' preplan_load_id='$has_preplan' has_load_flag='$has_load_flag' src='$use_image' onclick='add_note($row_log[id])' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;".($row_log['note_count'] > 0 || $row_log['special_instructions'] != '' ? "border:1px red solid;" : "")."'>
          													<div id='note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;width:425px;background-color:#eeeeee;padding:5px'></div>
          													".($row_log['load_handler_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/menu_system16.png' style='cursor:pointer;float:left' onclick=\"edit_entry_truck('',$row_log[id],0)\" alt='Edit Dispatch' title='Edit Dispatch'></div>" : "")."
          													".($row_log['truck_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/truck_info.png' style='cursor:pointer;float:left' onclick=\"view_truck_history($row_log[truck_id])\" alt='Truck History' title='Truck History'></div>" : "")."
          													".( $row_log['peoplenet_tracking'] > 0 ? "<a class='".$pn_class."' href=\"peoplenet_interface.php?find_load_id=".$row_log['load_handler_id']."&find_truck_id=".$row_log['truck_id']."&auto_run=1\" target='_blank' title='View PeopleNet Tracking'>PN</a>" : "")." 
          													".$pn_msg_viewer."
          													".$quick_quote_link."
          													<div class='mrr_link_like_on' onClick='mrr_show_this_dispatch(".$row_log['id'].");'><b>Show</b></div>
          													<div id='pn_note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
          													<div id='pn_note_holder2_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
          													<div id='phone_note_holder2_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
          												</div>												
          
          												<a style='margin-right:5px;float:left;display:inline-block;width:160px;overflow:hidden' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\" log_id='$row_log[id]'>($row_log[load_handler_id]) <div class='".($row_log['jit_driver_flag'] > 0 ? "jit2" : "non_jit2")."'>$row_log[name_driver_last], $row_log[name_driver_first]</div></a>
          												<div style='float:left;color:black;'".$dist_java.">".$use_location."</div>
          												<div style='float:right;margin-right:5px;font-weight:bold;color:black;'>
          													
          													".( $row_log['peoplenet_tracking'] > 0 ? "<a class='".$pn_class."' href=\"peoplenet_messager.php?truck_id=".$row_log['truck_id']."\" target='_blank' title='View PeopleNet Tracking Messages'>MSGS</a>" : "")." 
          													<b>$row_log[name_truck]".$warning_flag1."</b> / ".$warning_flag2."$row_log[trailer_name]
          																										
          												</div>
          												
          
          												<div style='clear:both'></div>
          											</span>
          											<div class='load_stop_details load_stop_details_".$row_log['id']."' log_id='$row_log[id]' style='color:black;'>
          												".$d_hours_display."<div class='mrr_link_like_on' onClick='mrr_hide_this_dispatch(".$row_log['id'].");'>Hide Dispatch</div>
          												
          												<div style='float:right;margin-right:5px;color:black;' id='phone_cell_display_$row_log[truck_id]'>
          													<input style='background-color:transparent;border:0;width:90px;height:10px;text-align:right;cursor:pointer;color:black;' readonly value=\"$row_log[phone_cell]\" onclick=\"$(this).select()\">
          												</div>
          												".$customer_linker." ".($row_log['driver2_id'] > 0 ? "(TEAM DRIVER)" : "")." | ".$row_log['miles']." mi + ".$row_log['miles_deadhead']." dh = ".($row_log['miles'] + $row_log['miles_deadhead'])." miles. ".$dist_res."
          												".$load_and_delivery_display."
          												".$mrr_attachment_list."												
          												<div style='clear:both'></div>
          									";
          									
          									//stops for this load...
          									$counter_sub = 0;
          									mysqli_data_seek($data_log,$loop_count-1);
          									while($row_log_sub = mysqli_fetch_array($data_log)) {
          										$counter_sub++;
          										//$mrr_load_block.= "($row_log[load_handler_id] | $row_log_sub[load_handler_id] | $row_log_sub[load_handler_stop_id] | loop_count: $loop_count | counter: $counter_sub)";
          																				
          										if($row_log['id'] != $row_log_sub['id'] || $row_log_sub['id'] == 0 || $row_log_sub['id'] == '') {
          											if($counter_sub > 1) $counter_sub--;
          											$loop_count = $loop_count + $counter_sub - 1;
          											
          											@mysqli_data_seek($data_log,$loop_count);
          											break;
          										} else {
          											
          											//appointment window..........................................
                                                  			$appt_window_tag1="";
                                                  			$appt_window_tag2="";			
                                                  			$appt_window=$row_log_sub['appointment_window'];
                                                  			if($appt_window > 0)
                                                  			{				
                                                  				$ideal_time=date("M d, Y", strtotime($row_log_sub['linedate_pickup_eta']))." ".time_prep($row_log_sub['linedate_pickup_eta']);
                                                  				$appt_window_start="";
                                                  				$appt_window_start_time="";
                                                  				$appt_window_end="";
                                                  				$appt_window_end_time="";
                                                  				
                                                  				if(strtotime($row_log_sub['linedate_appt_window_start']) > 0)
                                                  				{
                                                  					$appt_window_start=date("M d, Y", strtotime($row_log_sub['linedate_appt_window_start']));
                                                  					$appt_window_start_time=time_prep($row_log_sub['linedate_appt_window_start']);
                                                  				}
                                                  				if(strtotime($row_log_sub['linedate_appt_window_end']) > 0)
                                                  				{
                                                  					$appt_window_end=date("M d, Y", strtotime($row_log_sub['linedate_appt_window_end']));
                                                  					$appt_window_end_time=time_prep($row_log_sub['linedate_appt_window_end']);
                                                  				}
                                                  				$appt_window_tag1.="<span class='mrr_link_like_on mrr_appt_windower' style='display: inline-block;' onMouseOver=\"mrr_appt_window_display(".$row_log_sub['load_handler_stop_id'].",'".$ideal_time."','".$appt_window_start." ".$appt_window_start_time."','".$appt_window_end." ".$appt_window_end_time."');\" onMouseOut=\"mrr_appt_window_no_display(".$row_log_sub['load_handler_stop_id'].");\">";				
                                                  				$appt_window_tag2.=" [Window]</span> <div id='stop_".$row_log_sub['load_handler_stop_id']."_appt_window' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>";
                                                  			}			
                                                  			//.......................................................................................
          											
          											$mrr_load_block.= "
          													<div style='float:left".($row_log_sub['linedate_completed'] > 0 ? ";color:#888888;" : ";color:black;")."'>
          													(".($row_log_sub['stop_type_id'] == 1 ? "S" : "C").") ($row_log_sub[load_handler_stop_id])
          													".$appt_window_tag1."".$row_log_sub['shipper_name'].": $row_log_sub[shipper_city], $row_log_sub[shipper_state].".$appt_window_tag2."
          													</div>
          													<div style='float:right'>
          											";											
          											
          												if($row_log_sub['linedate_completed'] > 0) {												
          													$mrr_load_block.= "<span style='color:#888888;color:black;'>".($row_log_sub['linedate_completed'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_completed'])) : "")."&nbsp;&nbsp;&nbsp</span>";
          													$load_started_already=1;
          												} else {
          													
          													$mrr_load_block.= " Trailer ".$row_log_sub['mrr_start_trailer']."";
                    											if($row_log_sub['mrr_end_trailer'] != $row_log_sub['mrr_start_trailer'] && $row_log_sub['mrr_end_trailer'] > 0)
                    											{
                    												$mrr_load_block.= " Switch to ".$row_log_sub['mrr_end_trailer']."";	
                    											}
                    											elseif($row_log_sub['mrr_end_trailer'] != $row_log_sub['mrr_start_trailer'] && $row_log_sub['mrr_end_trailer'] == 0)
                    											{
                    												$mrr_load_block.= " Drop";	
                    											}
          													
          													$mrr_load_block.= "&nbsp;&nbsp; <a href='javascript:update_stop_complete($row_log_sub[load_handler_stop_id],".$row_log_sub['stop_type_id'].")'>".
          														($row_log_sub['linedate_pickup_eta'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_pickup_eta'])) : "")."&nbsp;&nbsp"
          														."</a>";
          												}
          														
          											$mrr_load_block.= "
          													</div>
          													
          												<div style='clear:both'></div>
          											";
          										}
          									}
          									//@mysqli_data_seek($data_log, $loop_count-1);
          									
          									
          									
          									$mrr_load_block.= "
          											</div>
          										</div>
          									";
          									
          									if($load_started_already > 0)
          									{										
                                                  		//if(trim($mrr_load_block_started)=="")		$mrr_load_block="<div class='mrr_load_div_titles'>Loads already in progress:</div>".$mrr_load_block."";  
                                                  		$mrr_load_block_started.=$mrr_load_block;	                                                  		                                                							
          									}
          									else
          									{										
          										//if(trim($mrr_load_block_waiting)!="")		$mrr_load_block.="<div class='mrr_load_div_titles'>Loads not started/dispatched:</div>".$mrr_load_block."";
          										$mrr_load_block_waiting.=$mrr_load_block;	          										
          									}
          									
          									$mrr_load_block=str_replace("<span class='mrr_origin_local_starter'>","<span style='color:#999999; display:inline-block;'>",$mrr_load_block);	//<span style='color:#999999;'>
          									$mrr_load_block=str_replace("</span class='mrr_origin_local_ender'>","</span>",$mrr_load_block);						//</span>		
          									$mrr_load_block=str_replace("<span class='mrr_origin_local_starter'>","",$mrr_load_block);
          									$mrr_load_block=str_replace("</span class='mrr_origin_local_ender'>","",$mrr_load_block);
          									
          									if($mrr_save_preloader > 0)
                    							{
                    								$capt_board=$disp_type;
                    								$capt_special=$load_started_already;				
                    								$capt_load=$row_log['load_handler_id'];
                    								$capt_disp=$row_log['id'];
                    								$capt_driver=$row_log['driver_id'];
                    								$capt_cust=$row_log['customer_id'];
                    								$capt_truck=$row_log['truck_id'];		
                    								$capt_trailer=$row_log['trailer_id'];
                    								$capt_note=0;
                    								mrr_create_load_board_preload($capt_board,$capt_special,$capt_load,$capt_disp,$capt_driver,$capt_cust,$capt_truck,$capt_trailer,date("m/d/Y",strtotime($row_log['linedate'])),$mrr_load_block,$capt_note);
                    							}
          																	
          									
          								} // end of dropped trailer if
          							} // end of while statement
          							
          							$mrr_load_block_started=str_replace("<span class='mrr_origin_local_starter'>","<span style='color:#999999; display:inline-block;'>",$mrr_load_block_started);	//<span style='color:#999999;'>
          							$mrr_load_block_started=str_replace("</span class='mrr_origin_local_ender'>","</span>",$mrr_load_block_started);						//</span>
          							
          							$mrr_load_block_waiting=str_replace("<span class='mrr_origin_local_starter'>","",$mrr_load_block_waiting);
          							$mrr_load_block_waiting=str_replace("</span class='mrr_origin_local_ender'>","",$mrr_load_block_waiting);
          							
          							//sub-divided loads based on if stop(s) are completed yet...Added May 2013 (sorted this way)
          							if(trim($mrr_load_block_started)!="")		$info_columns.="<div class='mrr_load_div_titles'>Loads already in progress:</div>".$mrr_load_block_started."";
          							if(trim($mrr_load_block_waiting)!="")		$info_columns.="<div class='mrr_load_div_titles'>Loads not started/dispatched:</div>".$mrr_load_block_waiting."";
          							
          							
          							if($name_company!="Dedicated Loads")
          							{	//this is the true section to display Dropped Trailers...
               							
               							if(date("m/d/Y", strtotime("+$i days", $startdate)) == date("m/d/Y", time())   ) {
               								// today's date                                                            && $name_company!="Dedicated Loads"
               								
               								// show any dropped trailer
               								//and trailers_dropped.linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
               								$mrr_message="";
               								$mrr_add_dedicated="";
               								
               								//$mrr_add_dedicated=' and trailers_dropped.dedicated_trailer=0';
               								if($name_company=="Dedicated Loads")
               								{
               									//$mrr_add_dedicated=' and trailers_dropped.dedicated_trailer>0';
               									//$mrr_message="Dedicated... ";
               								}	
               								
               								//	
               								$ipcc_list="";							
               								$sql = "
               									select trailers_dropped.*,
               										trailers.trailer_name,
               										trailers.trailer_owner,
               										customers.name_company
               									
               									from trailers_dropped
               										left join trailers on trailers.id = trailers_dropped.trailer_id 
               										left join customers on trailers_dropped.customer_id=customers.id
               									where trailers_dropped.drop_completed = 0
               										and trailers_dropped.deleted = 0
               										and trailers_dropped.trailer_id>0
               										".$mrr_add_dedicated."
               										
               										$sql_filter_drop_tl_cust
               									order by location_city asc,location_state asc,location_zip asc,name_company asc,trailer_name asc							
               								";
               								$last_stater="";
               								$data_trailers_dropped = simple_query($sql);
               								$dropped_trailer_count = 0;
               								while($row_trailer_dropped = mysqli_fetch_array($data_trailers_dropped)) {
               									
               									$capt_trail_drop="";
               									
               									$dropped_trailer_count++;
               									// dropped trailers are always shown last. If this is the first dropped trailer, then show a general toggle for them
               									if($dropped_trailer_count == 1) 
               									{
               										/*
               										$capt_trail_drop.="
               											<div class='load_board_section_header' onclick='mrr_toggle_dropped_trailer_setter(".$_SESSION['toggle_dropped_trailer_on'].");' style='cursor:pointer;color:#c4ffc4'>
               												Toggle Dropped Trailers
               											</div>
               										";			//toggle_dropped_trailers()
               										
               										if($_SESSION['toggle_dropped_trailer_on']==1)
               										{
               											$info_columns.="<script type='text/javascript'>
               													toggle_dropped_trailers();
               													</script>";	
               											$_SESSION['toggle_dropped_trailer_on']=1;
               										}
               										*/
               									}
               									
               									$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_trailer_dropped['trailer_id']);
               									for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                                                       	{
                                                       		if($mrr_maint_trailer_arr[ $rr ] == $row_trailer_dropped['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                                                       	}
                                                       	
                                                       	if($last_stater!="$row_trailer_dropped[location_city]")
                                                       	{
                                                       		$last_stater="$row_trailer_dropped[location_city]";	
                                                       		$capt_trail_drop.="<div>&nbsp;</div>";
                                                       	}
                                                       	
                                                       	if($row_trailer_dropped['trailer_owner']=="IPCC")
                                                       	{
                                                       		$capt_trail_drop.="
                    										<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style=';min-width:300px;'>
                    											<span trailer_id='$row_trailer_dropped[trailer_id]' class='entry_info' dropped_trailer_id='$row_trailer_dropped[id]'>
                    											<a style='margin-right:10px;float:left' href=\"javascript:edit_dropped_trailer($row_trailer_dropped[id])\">TL DR</a>
                    											<span style='float:left;color:black;'>".trim_string("$row_trailer_dropped[location_city], $row_trailer_dropped[location_state] $row_trailer_dropped[location_zip]",45)."</span>
                    											<span style='float:right;color:black;'>".$mrr_message."".$warning_flag2."$row_trailer_dropped[trailer_name]</span>&nbsp;
                    											</span>
                    											<div style='clear:both'></div>
                    										</div>
               										";
               										
               										$ipcc_list.=trim($capt_trail_drop);
               										
               										if($mrr_save_preloader > 0)
                              							{
                              								$capt_board=0;				if($row_trailer_dropped['customer_id']==1 || $row_trailer_dropped['customer_id']==1669)	$capt_board=$row_trailer_dropped['customer_id'];
                              								$capt_special=1;			//0=regular Dropped trailer / 1= IPCC dropped trailer
                              								$capt_load=0;
                              								$capt_disp=0;
                              								$capt_driver=0;
                              								$capt_cust=$row_trailer_dropped['customer_id'];
                              								$capt_truck=0;	
                              								$capt_trailer=$row_trailer_dropped['trailer_id'];
                              								$capt_note=0;
                              								mrr_create_load_board_preload($capt_board,$capt_special,$capt_load,$capt_disp,$capt_driver,$capt_cust,$capt_truck,$capt_trailer,date("m/d/Y"),$capt_trail_drop,$capt_note);
                              							}
                                                       	}
                                                       	else
                                                       	{
                                                       		$capt_trail_drop="
                    										<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style=';min-width:300px;'>
                    											<span trailer_id='$row_trailer_dropped[trailer_id]' class='entry_info' dropped_trailer_id='$row_trailer_dropped[id]'>
                    											<a style='margin-right:10px;float:left' href=\"javascript:edit_dropped_trailer($row_trailer_dropped[id])\">TL DR</a>
                    											<span style='float:left;color:black;'>".trim_string("$row_trailer_dropped[location_city], $row_trailer_dropped[location_state] $row_trailer_dropped[location_zip]",45)."</span>
                    											<span style='float:right;color:black;'>".$mrr_message."".$warning_flag2."$row_trailer_dropped[trailer_name]</span>&nbsp;
                    											</span>
                    											<div style='clear:both'></div>
                    										</div>
               										";
               										
               										$info_columns.=trim($capt_trail_drop);
               										
               										if($mrr_save_preloader > 0)
                              							{
                              								$capt_board=0;				if($row_trailer_dropped['customer_id']==1 || $row_trailer_dropped['customer_id']==1669)	$capt_board=$row_trailer_dropped['customer_id'];
                              								$capt_special=0;			//0=regular Dropped trailer / 1= IPCC dropped trailer
                              								$capt_load=0;
                              								$capt_disp=0;
                              								$capt_driver=0;
                              								$capt_cust=$row_trailer_dropped['customer_id'];
                              								$capt_truck=0;	
                              								$capt_trailer=$row_trailer_dropped['trailer_id'];
                              								$capt_note=0;
                              								mrr_create_load_board_preload($capt_board,$capt_special,$capt_load,$capt_disp,$capt_driver,$capt_cust,$capt_truck,$capt_trailer,date("m/d/Y"),$capt_trail_drop,$capt_note);
                              							}
                                                       	}    	
               									
               								}
               								
               								$info_columns.="<br><span style='color:black;' class='dropped_trailer'><b>IPCC TRAILERS:</b></span><br><br>".$ipcc_list."";
               							}
          							}
          														
          							$info_columns.="&nbsp;</td>";
          							
          							
							}		//end else for fresh load...no preloader.					
							

						}
		//$info_columns.="</tr>";
		
		//echo $date_headers;
		//echo "</tr><tr>";
		//echo $info_columns;
		
		$res['column_headers']=$date_headers;
		$res['column_info']=$info_columns;
		return $res;
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION Display Sub 2b");		//make tracking log entry
	}
	
	function mrr_display_simple_attachment_list($sect,$id) 
	{
		global $defaultsarray;
		
		$sql = "
			select *
			
			from attachments
			where deleted = 0
				and section_id = '".sql_friendly($sect)."'
				and xref_id = '".sql_friendly($id)."'
			order by linedate_added desc
		";
		$data = simple_query($sql);
		
		$list="<b>Rate Sheets (and other attachments):</b>";
				
		while($row = mysqli_fetch_array($data)) 
		{			
			$list.="<br><a href=\"$defaultsarray[document_upload_dir]/$row[fname]\" target='blank_$row[id]'>$row[fname]</a>";
		}
		return $list;
	}
	
	

	
	function mrr_find_peoplenet_truck_id_by_driver($driver_id)
	{
		$truck_id=0;
		$sql="
     		select truck_id
     		from trucks_log,trucks
     		where trucks_log.truck_id=trucks.id
     			and trucks_log.driver_id='".sql_friendly($driver_id)."'
     			and trucks_log.deleted=0
     			and trucks_log.dispatch_completed=0
     			and trucks.peoplenet_tracking > 0
     		order by trucks_log.linedate_pickup_eta asc
     		limit 1
     	";
     	$data=simple_query($sql);
     	if($row=mysqli_fetch_array($data))
     	{
     		$truck_id=$row['truck_id'];
     	}
     	return $truck_id;	
	}	
	
	
	//functions to preload the load board
	function mrr_create_load_board_preload($board,$special,$load,$disp,$driver,$cust,$truck,$trailer,$date,$text,$note)
	{
		$text=trim($text);
		
		if($load==0 && $board==0 && $driver > 0 && $note ==0 && $trailer==0)
		{	//AVAILABEL DRIVERS
			//just look for available drivers section...nothing else...	
			
			//first remove older ones...PURGE OLD ONES
			$sql="
          		delete from load_board
          		where load_id=0
          			and board_section=0
          			and linedate_pickup_eta < '".date("Y-m-d")." 00:00:00'
          	";
          	simple_query($sql);
          	
			//now see if there are any for today...
			$sql="
          		select *
          		from load_board
          		where driver_id='".sql_friendly($driver)."'
          			and linedate_pickup_eta='".date("Y-m-d")." 00:00:00'
          	";
          	$data=simple_query($sql);
          	if($row=mysqli_fetch_array($data))
          	{	//found, update the text only...
          		$id=$row['id'];
          		$sqlu="
          			update load_board set
          				board_html='".sql_friendly($text)."'
          			where id='".sql_friendly($id)."'
          		";
          		simple_query($sqlu);         		
          	}	
          	else
          	{	//not found, so add it.
          		$sqlu="
          			insert into load_board 
          				(id,
          				load_id,
          				disp_id,
          				driver_id,
          				truck_id,
          				trailer_id,
          				customer_id,
          				linedate_pickup_eta,
          				board_section,
          				board_html, 
          				note_id,        				
          				special_flag)
          			values
          				(NULL,
          				'".sql_friendly($load)."',
          				'".sql_friendly($disp)."',
          				'".sql_friendly($driver)."',
          				'".sql_friendly($truck)."',
          				'".sql_friendly($trailer)."',
          				'".sql_friendly($cust)."',
          				'".date("Y-m-d",strtotime($date))." 00:00:00',
          				'".sql_friendly($board)."',
          				'".sql_friendly($text)."',
          				'".sql_friendly($note)."',
          				'".sql_friendly($special)."')
          		";
          		simple_query($sqlu);  	
          	}		
		}
		elseif($note > 0)
		{	//NOTES FOR THE DAY
			//first remove older ones...PURGE OLD ONES
			$sql="
          		delete from load_board
          		where load_id=0
          			and board_section=0
          			and note_id > 0
          			and linedate_pickup_eta < '".date("Y-m-d")." 00:00:00'
          	";
          	simple_query($sql);
          	
			//now see if there are any for today...
			$sql="
          		select *
          		from load_board
          		where note_id='".sql_friendly($note)."'
          	";
          	$data=simple_query($sql);
          	if($row=mysqli_fetch_array($data))
          	{	//found, update the text only...
          		$id=$row['id'];
          		$sqlu="
          			update load_board set
          				board_html='".sql_friendly($text)."'
          			where id='".sql_friendly($id)."'
          		";
          		simple_query($sqlu);         		
          	}	
          	else
          	{	//not found, so add it.
          		$sqlu="
          			insert into load_board 
          				(id,
          				load_id,
          				disp_id,
          				driver_id,
          				truck_id,
          				trailer_id,
          				customer_id,
          				linedate_pickup_eta,
          				board_section,
          				board_html, 
          				note_id,        				
          				special_flag)
          			values
          				(NULL,
          				'".sql_friendly($load)."',
          				'".sql_friendly($disp)."',
          				'".sql_friendly($driver)."',
          				'".sql_friendly($truck)."',
          				'".sql_friendly($trailer)."',
          				'".sql_friendly($cust)."',
          				'".date("Y-m-d",strtotime($date))." 00:00:00',
          				'".sql_friendly($board)."',
          				'".sql_friendly($text)."',
          				'".sql_friendly($note)."',
          				'".sql_friendly($special)."')
          		";
          		simple_query($sqlu);  	
          	}		
		}
		elseif($trailer > 0 && $load==0)
		{	//DROPPED TRAILERS
			//first remove older ones...PURGE OLD ONES
			$sql="
          		delete from load_board
          		where load_id=0
          			and trailer_id > 0
          			and linedate_pickup_eta < '".date("Y-m-d")." 00:00:00'
          	";
          	simple_query($sql);
          	
			//now see if there are any for today...
			$sql="
          		select *
          		from load_board
          		where load_id=0 
          			and board_section='".sql_friendly($board)."'
          			and trailer_id='".sql_friendly($trailer)."'
          	";
          	$data=simple_query($sql);
          	if($row=mysqli_fetch_array($data))
          	{	//found, update the text only...
          		$id=$row['id'];
          		$sqlu="
          			update load_board set
          				board_html='".sql_friendly($text)."'
          			where id='".sql_friendly($id)."'
          		";
          		simple_query($sqlu);         		
          	}	
          	else
          	{	//not found, so add it.
          		$sqlu="
          			insert into load_board 
          				(id,
          				load_id,
          				disp_id,
          				driver_id,
          				truck_id,
          				trailer_id,
          				customer_id,
          				linedate_pickup_eta,
          				board_section,
          				board_html, 
          				note_id,        				
          				special_flag)
          			values
          				(NULL,
          				'".sql_friendly($load)."',
          				'".sql_friendly($disp)."',
          				'".sql_friendly($driver)."',
          				'".sql_friendly($truck)."',
          				'".sql_friendly($trailer)."',
          				'".sql_friendly($cust)."',
          				'".date("Y-m-d",strtotime($date))." 00:00:00',
          				'".sql_friendly($board)."',
          				'".sql_friendly($text)."',
          				'".sql_friendly($note)."',
          				'".sql_friendly($special)."')
          		";
          		simple_query($sqlu);  	
          	}		
		}
		elseif($load > 0)
		{	//Regular LOADS and DISPATCHES
			//either preplanned load (no dispatch ID) or dispatch for the board section (company/Didicated/etc.).
			$sql="
          		select *
          		from load_board
          		where load_id='".sql_friendly($load)."'
          			and disp_id='".sql_friendly($disp)."'
          			and board_section='".sql_friendly($board)."'
          	";
          	$data=simple_query($sql);
          	if($row=mysqli_fetch_array($data))
          	{	//found, update the text only...
          		$id=$row['id'];
          		$sqlu="
          			update load_board set
          				driver_id='".sql_friendly($driver)."',
          				truck_id='".sql_friendly($truck)."',
          				trailer_id='".sql_friendly($trailer)."',
          				customer_id='".sql_friendly($cust)."',
          				board_html='".sql_friendly($text)."',
          				linedate_pickup_eta='".date("Y-m-d",strtotime($date))." 00:00:00',
          				special_flag='".sql_friendly($special)."'
          			where id='".sql_friendly($id)."'
          		";
          		simple_query($sqlu);         		
          	}
          	else
          	{	//not found at all, so add it
          		$sqlu="
          			insert into load_board 
          				(id,
          				load_id,
          				disp_id,
          				driver_id,
          				truck_id,
          				trailer_id,
          				customer_id,
          				linedate_pickup_eta,
          				board_section,
          				board_html, 
          				note_id,        				
          				special_flag)
          			values
          				(NULL,
          				'".sql_friendly($load)."',
          				'".sql_friendly($disp)."',
          				'".sql_friendly($driver)."',
          				'".sql_friendly($truck)."',
          				'".sql_friendly($trailer)."',
          				'".sql_friendly($cust)."',
          				'".date("Y-m-d",strtotime($date))." 00:00:00',
          				'".sql_friendly($board)."',
          				'".sql_friendly($text)."',
          				'".sql_friendly($note)."',
          				'".sql_friendly($special)."')
          		";
          		simple_query($sqlu);  	
          		//$board,$special,$load,$disp,$driver,$cust,$truck,$trailer,$date,$text	
          	}					
		}			
	}
	
	function mrr_load_board_from_preloader($board,$dater)
	{
		$tab="";
		$tot_cntr=0;
		
		//sections
		$driver_tab="";			$driver_cntr=0;
		$note_tab="";				$note_cntr=0;
		$avail_tab="";				$avail_cntr=0;
		$preplan_tab="";			$preplan_cntr=0;
		$disp1_tab="";				$disp1_cntr=0;
		$disp2_tab="";				$disp2_cntr=0;
		$trail_tab="";				$trail_cntr=0;
		$ipcc_tab="";				$ipcc_cntr=0;
		
		
		$mrr_tot_disp_cntr=0;
		//global $mrr_tot_disp_cntr;
		
		$show_debug=0;				//if($dater=="2016-01-18")		$show_debug=1;
		$show_debug2=0;			//if($dater=="2016-01-18")		$show_debug2=1;	
		
		if($board==0)
		{
			//Available Drivers...
			$sql="
          		select *
          		from load_board
          		where load_id=0
          			and driver_id > 0
          			and board_section='".sql_friendly($board)."'            			
          			and linedate_pickup_eta='".date("Y-m-d",strtotime($dater))." 00:00:00'	
          	";
          	
          	if($show_debug > 0)			echo "<br>Query 1: ".$sql."<br>";
          	
          	$data=simple_query($sql);
          	while($row=mysqli_fetch_array($data))
          	{	//found, update the text only...
          		$driver_tab.="".$row['board_html']."";      
          		$driver_cntr++; 		
          	}	
          	
          	if($show_debug2 > 0)		echo "Driver Count ".$driver_cntr."<br>TAB below<br>".$driver_tab."<br>End Tab<br>";
		
			//Notes
			$sql="
          		select *
          		from load_board
          		where load_id=0
          			and note_id > 0
          			and board_section='".sql_friendly($board)."'  
          	";
          	
          	if($show_debug > 0)			echo "<br>Query 2: ".$sql."<br>";
          	
          	$data=simple_query($sql);
          	while($row=mysqli_fetch_array($data))
          	{	//found, update the text only...
          		$note_tab.="".$row['board_html']."";    
          		$note_cntr++; 	   		
          	}	
          	
          	if($show_debug2 > 0)		echo "Note Count ".$note_cntr."<br>TAB below<br>".$note_tab."<br>End Tab<br>";
		}
		
						
		//Available Loads
		$sql="
          	select *
          	from load_board
          	where load_id>0
          		and disp_id=0
          		and board_section='".sql_friendly($board)."'
          		and special_flag=0
          		and linedate_pickup_eta='".date("Y-m-d",strtotime($dater))." 00:00:00'
          ";
          
          if($show_debug > 0)				echo "<br>Query 3: ".$sql."<br>";
          
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
		{
			$avail_tab.="".$row['board_html']."";
			$avail_cntr++; 	
		}
		
		if($show_debug2 > 0)			echo "Available Loads Count ".$avail_cntr."<br>TAB below<br>".$avail_tab."<br>End Tab<br>";
		
		
		//Preplanned Loads
		$sql="
          	select *
          	from load_board
          	where load_id>0
          		and disp_id=0
          		and board_section='".sql_friendly($board)."'
          		and special_flag=1
          		and linedate_pickup_eta='".date("Y-m-d",strtotime($dater))." 00:00:00'
          ";
          $data=simple_query($sql);
          
          if($show_debug > 0)				echo "<br>Query 4: ".$sql."<br>";
          
          while($row=mysqli_fetch_array($data))
		{
			$preplan_tab.="".$row['board_html']."";
			$preplan_cntr++; 	
		}
		
		if($show_debug2 > 0)			echo "Preplanned Loads Count ".$preplan_cntr."<br>TAB below<br>".$preplan_tab."<br>End Tab<br>";
		
		//Loads in Progress (dispatches present)
		$sql="
          	select *
          	from load_board
          	where load_id>0
          		and disp_id>0
          		and board_section='".sql_friendly($board)."'
          		and special_flag=0
          		and linedate_pickup_eta='".date("Y-m-d",strtotime($dater))." 00:00:00'
          ";
          
          if($show_debug > 0)				echo "<br>Query 5: ".$sql."<br>";
          
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
		{
			$disp1_tab.="".$row['board_html']."";
			$disp1_cntr++; 	
		}
		
		if($show_debug2 > 0)			echo "Loads in Progress Count ".$disp1_cntr."<br>TAB below<br>".$disp1_tab."<br>End Tab<br>";
		
		//Loads not started (but dispatches present)
		$sql="
          	select *
          	from load_board
          	where load_id>0
          		and disp_id>0
          		and board_section='".sql_friendly($board)."'
          		and special_flag=1
          		and linedate_pickup_eta='".date("Y-m-d",strtotime($dater))." 00:00:00'
          ";
          
          if($show_debug > 0)				echo "<br>Query 6: ".$sql."<br>";
          
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
		{
			$disp2_tab.="".$row['board_html']."";
			$disp2_cntr++; 	
		}
		
		if($show_debug2 > 0)			echo "Loads no Started Count ".$disp2_cntr."<br>TAB below<br>".$disp2_tab."<br>End Tab<br>";		
				
		if(date("Y-m-d",strtotime($dater)) == date("Y-m-d",time()))
		{	//$board==0
			//Dropped Trailers
			$sql="
          		select *
          		from load_board
          		where load_id=0
          			and special_flag=0
          			and trailer_id>0
          			and board_section='".sql_friendly($board)."'
          	";
          	$data=simple_query($sql);
          	
          	if($show_debug > 0)				echo "<br>Query 7: ".$sql."<br>";
          	
          	while($row=mysqli_fetch_array($data))
          	{	//found, update the text only...
          		$trail_tab.="".$row['board_html']."";    
          		$trail_cntr++; 	    		
          	}	
          	
          	if($show_debug2 > 0)			echo "Dropped Trailers Count ".$trail_cntr."<br>TAB below<br>".$trail_tab."<br>End Tab<br>";
          	
          	//Dropped Trailers (IPCC trailers)
			$sql="
          		select *
          		from load_board
          		where load_id=0
          			and special_flag=1
          			and trailer_id>0
          			and board_section='".sql_friendly($board)."'
          	";
          	
          	if($show_debug > 0)				echo "<br>Query 8: ".$sql."<br>";
          	
          	$data=simple_query($sql);
          	while($row=mysqli_fetch_array($data))
          	{	//found, update the text only...
          		$ipcc_tab.="".$row['board_html']."";      
          		$ipcc_cntr++; 	  		
          	}	
          	
          	if($show_debug2 > 0)			echo "IPCC Trailers Count ".$ipcc_cntr."<br>TAB below<br>".$ipcc_tab."<br>End Tab<br>";
		}
		
		$tab="
			".($driver_cntr > 0 ? "<div class='load_board_section_header'>Available Drivers</div>" : "")."
			".$driver_tab."
			".$note_tab."
			".$avail_tab."
			".$preplan_tab."
			".($disp1_cntr > 0 ? "<div class='mrr_load_div_titles'>Loads already in progress:</div>" : "")."			
			".$disp1_tab."
			".($disp2_cntr > 0 ? "<div class='mrr_load_div_titles'>Loads not started/dispatched:</div>" : "")."			
			".$disp2_tab."	
			".( ($trail_cntr + $ipcc_cntr) > 0 && date("Y-m-d",strtotime($dater)) == date("Y-m-d",time()) ? "<div class='load_board_section_header' onclick='mrr_toggle_dropped_trailer_setter(".$_SESSION['toggle_dropped_trailer_on'].");' style='cursor:pointer;color:#c4ffc4'>Toggle Dropped Trailers</div>" : "")."				
			".( $trail_cntr > 0 && date("Y-m-d",strtotime($dater)) == date("Y-m-d",time())   ? "".$trail_tab."" : "")."	
			".( $ipcc_cntr  > 0 && date("Y-m-d",strtotime($dater)) == date("Y-m-d",time())   ? "<br><span style='color:black;' class='dropped_trailer'><b>IPCC TRAILERS:</b></span><br><br>" : "")."
			".( $ipcc_cntr  > 0 && date("Y-m-d",strtotime($dater)) == date("Y-m-d",time())   ? "".$ipcc_tab."" : "")."		
		";
		$tot_cntr=( $driver_cntr + $note_cntr + $avail_cntr + $preplan_cntr + $disp1_cntr + $disp2_cntr + $trail_cntr + $ipcc_cntr );
		
		if($show_debug > 0)				echo "<br><b>".$tot_cntr."</b> section items found for ".$dater.".<br>";
				
		$res['num']=$tot_cntr;
		$res['html']=$tab;	
		return $res;
	}
	
?>