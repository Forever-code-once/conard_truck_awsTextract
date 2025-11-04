<?
	function mrr_conard_component_time()
	{
		$res="";
				
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
		
		//get a list of companies that have separate fuel surcharge prices 
		$sql = "
			select customers.fuel_surcharge,
				customers.name_company,
				customers.id,
				(select fuel_surcharge.fuel_surcharge from fuel_surcharge where customer_id = customers.id and fuel_surcharge.range_lower <= $defaultsarray[fuel_surcharge] order by fuel_surcharge desc limit 1 ) as surcharge_list
			
			from customers 
			where customers.deleted <= 0
				and customers.active > 0
				and use_fuel_surcharge > 0
			 	
			 having surcharge_list > 0 or customers.fuel_surcharge > 0
			order by name_company
		";
		$data_surcharge = simple_query($sql);
		
		$rval="<br><br><br><br>";	
		
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
		//$res.=		"<li><a href='https://www.pfmlogin.com/pfm-main/main/index' target='_blank' title='Must be Logged in...or you will be prompted by their site to do so.'>PeopleNet Login</a></li>"; 
		$res.=			"<li><a href='javascript:new_load()'>New Load</a></li>";
		$res.=			"<li><a href='quote.php'>New Quote</a></li>";
		//$res.=			"<li><a href='javascript:edit_dropped_trailer(0)'>New Drop Trailer</a></li>";		
		$res.=			"<li><a href='https://www.truckdown.com/account/log-on' target='_blank'>Log In</a> <span style='color:red;'>or</span> <a href='https://www.truckdown.com/' target='_blank'>Truckdown</a> </li>";	
								//?Username=Jgriffith@conardtransportation.com&Password=truckdown.com@1956  ....  "truckddown.com@1956" ?
		                        //?Username=jgriffith@conardtransportation.com&Password=truckdown@1956
		$res.=			"<li><a href='https://fps.quikq.com:13146/SaaS/core/login' target='_blank'>Log In</a> <span style='color:red;'>or</span> <a href='https://fps.quikq.com:13146/SaaS/core/mainMenu' target='_blank'>QuikQ</a></li>";
         
         
		$sql = "
			select *			
			from home_pg_links
			where deleted <= 0
				and active > 0
			order by zorder asc,link_label asc,id asc
		";	//
		$data = simple_query($sql);
        while($row = mysqli_fetch_array($data))
        {
             $res.=	    "<li><a href='".trim($row['link_url'])."' title='".trim($row['link_title'])."' target='_blank'>".trim($row['link_label'])."</a></li>"; 
        }
        /*      //placed in table on 1/20/2022...MRR.  This will make adding them easier, and may be a user tool for Justin later...but not yet.
              
              //Added more links for Justin here...5/11/2021...MRR		
        $res.=			"<li><a href='https://docs.google.com/spreadsheets/d/1vT0h1Vx3Kn2dTf_2NaG-DjQOkN8wEFl5gFAyfh8DoyU/edit#gid=0' title='Usernames and Passwords - Google Sheets' target='_blank'>Google Password Sheet</a></li>";
        $res.=			"<li><a href='https://insight.skybitz.com/LAABSearch?event=menuSearchAssets&requestorUrl=/LAABSearch?event=menustartsearch&dispatchTo=/LocateAssets/NewAdvAssetSearchResults.jsp&map=no&optMulTerminal=AllAssets' title='SkyBitz InSight – Login Page' target='_blank'>Skybitz Trailer Tracking</a></li>";
        $res.=			"<li><a href='https://services.roadreadysystem.com/#/site/login' title='[ROAD READY] Login (roadreadysystem.com)' target='_blank'>Star Leasing Trailer Tracking</a></li>";
        $res.=			"<li><a href='https://www.lbtelematics.net/track1/Track' title='LB Telematics GPS Tracking' target='_blank'>Fleet Leasing Tracking</a></li>";
        $res.=			"<li><a href='https://geodislogistics.mercurygate.net/MercuryGate/login/mgLogin.jsp?inline=true' title='Geodis Logistics - Mercury Gate' target='_blank'>Geodis Portal</a></li>";
        $res.=			"<li><a href='https://www.lynnco-scs.com/' title='Lynnco-SCS' target='_blank'>Lynnco Portal</a></li>";
        $res.=			"<li><a href='https://ssoauth.jbhunt.com/cas-server/logout' title='ssoauth.jbhunt.com' target='_blank'>JB Hunt Portal</a></li>";
        $res.=			"<li><a href='https://rushcare.rushtruckcenters.com/' title='RushCare Connect (rushtruckcenters.com)' target='_blank'>Rush Truck Portal</a></li>";
        $res.=			"<li><a href='https://www.fleetinsight.com/' title='Fleet Insight' target='_blank'>Penske Portal</a></li>";
        $res.=			"<li><a href='https://rydergyde.com/login' title='RyderGyde' target='_blank'>Ryder Portal</a></li>";
		//...end new tool links.
                  
        // other new ones... 
        $res.=			"<li><a href='https://driverconnect.randmcnally.com/DriverConnect/' title='DriverConnect - Login (randmcnally.com)' target='_blank'>Rand Mcnally Trackers</a></li>";
        */
        
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
		//get the user list
		$sql = "
			select *			
			from users
			where deleted <= 0
				and active > 0
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
	
	function mrr_conard_component_needs_appt()
	{	
		//$dlist="<li><span style='color:#FFFFFF;'><b>Load</b></span><strong style='color:#FFFFFF;'>Pickup ETA</strong></li>";
        $dlist="";
        
        $arr[0]=0;
        $cntr=0;
         
		$sql="
			select load_handler_stops.id,
			    load_handler_stops.load_handler_id,
			    load_handler_stops.linedate_pickup_eta
			from load_handler_stops
			    left join load_handler on load_handler.id=load_handler_stops.load_handler_id
			where load_handler_stops.deleted <= 0
				and load_handler.deleted <= 0
				and (load_handler_stops.linedate_completed = '0000-00-00 00:00:00' or load_handler_stops.linedate_completed is NULL)
				and load_handler_stops.needs_appt > 0
				and load_handler_stops.trucks_log_id > 0
			order by load_handler_stops.linedate_pickup_eta asc,
				load_handler_stops.load_handler_id asc
		";	
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{ 	
            $found_it=0;
            for($i=0; $i < $cntr; $i++)
            {
                if($arr[$i]==$row['load_handler_id'])      $found_it=1;  
            }           
            
            if($found_it == 0) 
            {     
                $dlist .= "
                    <li>
                        <span><a href='manage_load.php?load_id=" . $row['load_handler_id'] . "' target='_blank' style='color:#FFFFFF;'>" . $row['load_handler_id'] . "</a></span>
                        <span style='color:orange;'>Pickup ETA</span>
                         <strong style='color:orange;'>" . date("m/d/Y H:i", strtotime($row['linedate_pickup_eta'])) . "</strong>
                    </li>
                ";
                $arr[$cntr]=$row['load_handler_id'];
                $cntr++;
            }
		}		
		
		$res="";
		//if($_SESSION['user_id']==23 || $_SESSION['user_id']==18)
		//{
			$res.="<div class='left_box'>";
			$res.=	"<h3><span>Needs Appointment</span></h3>";
			$res.=	"<ul class='driver_status'>";			
			$res.=		$dlist;			
			$res.=	"</ul>";		
			$res.="</div>";					
		//}
		return $res;
	}

    function mrr_conard_component_driver_status()
    {
         $dlist="";
         $rightnow=date("Y-m-d",time());
         
         $sql="
                select *
                from drivers
                where deleted <= 0
                    and active > 0
                    and driver_status > 0
                    and driver_status_date < NOW()
                order by name_driver_last asc, 
                    name_driver_first asc,
                    id asc
            ";
         $data = simple_query($sql);
         while($row = mysqli_fetch_array($data))
         {
              $days_left=0;
              
              $type=option_value_text($row['driver_status'],2);
              $totdays=ceil($row['driver_status_days']);
              
              if($row['driver_status_date']=='0000-00-00 00:00:00' || strtotime($row['driver_status_date'])==0)
              {	//date not set...cannot calculate.
                   $days_left=9999;
              }
              elseif($totdays==9999)
              {
                   $days_left=9999;
              }
              else
              {	//date set...try to use it.
                   $starting=date("Y-m-d",strtotime($row['driver_status_date']));
                   
                   
                   $popper=trim($row['driver_status_notes']);
                   $popper=str_replace("'","",$popper);
                   
                   $ending=date("Y-m-d",strtotime("+".$totdays." days",strtotime($row['driver_status_date'])));
                   
                   $date1=strtotime($ending);
                   $date2=strtotime($rightnow);
                   $days_left=($date1 - $date2)/(60*60*24);
                   
                   if($starting==$ending && $starting==$rightnow)		$days_left=1;
              }
              
              if($days_left != 0)
              {
                   $dlist.="
                        <li>
                            <div class='driver_status_name'><a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".$row['name_driver_first']." ".$row['name_driver_last']."</a></div>
                            <div class='driver_status_days' title='".$days_left." days left out of ".$totdays.", starting ".$starting." and ending on ".$ending.".'>".($days_left!=9999 ? number_format($days_left,2)."" : "Forever")."</div>
                            <div class='driver_status_type' title='".$popper."'>".$type."</div>
                        </li>
                    ";
              }
         }
         
         $res="";
         //if($_SESSION['user_id']==23 || $_SESSION['user_id']==18)
         //{
         $res.="<div class='left_box'>";
         $res.=	"<h3><span>Driver Status</span></h3>";
         $res.=	"<ul class='driver_status'>";
         $res.=		$dlist;
         $res.=	"</ul>";
         $res.="</div>";
         //}
         return $res;
    }
	
	function mrr_conard_component_driver_txt()
	{	//display the load board TXT Messages from phone (Twilio).
		$dlist="";
		//$rightnow=date("Y-m-d",time())." 00:00:00";					//today ONLY
		$rightnow=date("Y-m-d H:i:s",strtotime("-24 hours",time()));		//last 24 hours
		
		$cntr=0;
		$sql="
			select txt_msg_reply_log.*,
          		(select CONCAT(name_driver_first,' ',name_driver_last) from drivers where txt_msg_reply_log.driver_id=drivers.id) as driver_name,
          		(select username from users where txt_msg_reply_log.user_id=users.id) as user_name         						
          	from ".mrr_find_log_database_name()."txt_msg_reply_log
          	where txt_msg_reply_log.deleted <= 0   
          		and txt_msg_reply_log.linedate_added >='".$rightnow."'  				
          	order by txt_msg_reply_log.linedate_added desc, 
          		txt_msg_reply_log.id desc
          	limit 5
		";	
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{ 	
			$sent_by="";          			
          	if($row['user_id'] > 0)			$sent_by=trim($row['user_name']);	
          	if($row['driver_id'] > 0)		$sent_by=trim($row['driver_name']);	
          	/*		          			
     			echo "
     				<tr style='background-color:#".($cntr % 2 == 0 ? "eeeeee" : "dddddd").";'>
     					<td valign='top'>".$row['id']."</td>
     					<td valign='top'>".date("m/d/Y",strtotime($row['linedate_added']))."</td>   
     					<td valign='top'>".trim($row['from_phone'])."</td>      					
     					<td valign='top'>".$sent_by."</td>          					
     					<td valign='top'>".trim($row['to_phone'])."</td>
     					<td valign='top'>".trim(strip_tags($row['message_body']))."</td>
     				</tr>
     			";	     			    			
     				//<a href='text_messages.php?txt_msg_id=".$row['id']."'>".$row['id']."</a>
     				//<td valign='top'>".$sent_mode."</td>
          	*/				
			$dlist.="
				<li>
					<div class='driver_txt_name'><a href='text_messages.php#reply_messages' target='_blank'>".$sent_by."</a></div>
					<div class='driver_txt_phone'>".trim($row['from_phone'])."</div>
					<div class='driver_txt_date'>".date("m/d H:i",strtotime($row['linedate_added']))."</div>					
					<div class='driver_txt_msg'>".trim(strip_tags($row['message_body']))."</div>
				</li>
			";	
			$cntr++;		
		}
		if($cntr==0)
		{
			$dlist.="
				<li>
					<div class='driver_txt_phone'><a href='text_messages.php#reply_messages' target='_blank' style='color:#CC0000;'>History</a></div>
					<div class='driver_txt_date'>0 Current Messages</div>	
				</li>
			";		
		}		
		$res="";
		//if($_SESSION['user_id']==23 || $_SESSION['user_id']==18)
		//{
			$res.="<div class='left_box'>";
			$res.=	"<h3><span>Driver Text Messages</span></h3>";
			$res.=	"<ul class='driver_txt'>";			
			$res.=		$dlist;			
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

		$sqluu="delete from mrr_not_truck_movement where linedate='".date("Y-m-d",time())."' and truck_mode='0'";
		simple_query($sqluu);
		
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
     			
     			if((time() - $linedate) > (2 * 86400) && $linedate > 0) 
     			{
     				$show_alert = true;
     			} 
     			else 
     			{
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
     				$holder="<div class='alert' style='display:inline;' title='This Truck is on Hold'><b>HOLD</b></div> ";	
     				if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";		
     			}
                if($row_truck['truck_is_sold'] > 0)     $holder="<div class='alert' style='display:inline;' title='This Truck has been Sold'><b>SOLD</b></div> ";
     			
     			$shopper="";
				if($row_truck['in_the_shop'] > 0)	 
				{
					$shopper="<div class='alert' style='display:inline;' title='This Truck is in the Shop'><b>SHOP</b></div> ";					
					//$holder="";
					
					if(substr_count(trim($row_truck['in_shop_note']),"Safety Shut Down!") > 0)
					{
						$shopper="<div class='alert' style='display:inline;' title='This Truck is Shut Down for Safety... please do not use until fixed.'><b>SAFETY</b></div> ";		
					}
					
					if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";
				} 
				if($row_truck['in_body_shop'] > 0)	 
				{
					$shopper="<div class='alert' style='display:inline;' title='This Truck is in the Body Shop (long-term)'><b>BODY</b></div> ";
					if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";
					//$holder="";
				}     
				
				$tester=mrr_find_fed_inspection_last_completed(1,$row_truck['id']);
				if(trim($tester)!="")		$warning_flag=trim($tester);
				     			
     			if($show_alert) 
     			{
     				$res.="<li><span>$row_truck[name_truck]".$warning_flag."</span>  <strong title='".$titler."'>".$holder."".$shopper."".$user_attacher." ".$use_dater." ".$icon."</strong></li>";
     				
				$sqluu="
					insert into mrr_not_truck_movement
						(id,
						linedate,
						truck_id, 
						truck_name, 
						maint_marks,
						truck_mode)
					values
						(NULL,
						'".date("Y-m-d",time())."',
						'".$row_truck['id']."',
						'".sql_friendly($row_truck['name_truck']."".$warning_flag)."',
						'".sql_friendly($holder."".$shopper."".$user_attacher." ".$use_dater." ".$icon)."',
						0)
				";
				simple_query($sqluu);

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

		$sqluu="delete from mrr_not_truck_movement where linedate='".date("Y-m-d",time())."' and truck_mode='1'";
		simple_query($sqluu);
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span onClick='window.open(\"report_truck_no_load.php\");' style='cursor:pointer;'>Trucks on Last/No Load</span> ".show_help('index.php','Trucks Last Load Box',$new_style_path,"question_mark.png")."</h3>";
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
                if($row_truck['truck_is_sold'] > 0)	    $holder="<span class='alert' title='This Truck has been Sold'><b>SOLD</b></span> ";
     			
     			if(substr_count(trim($row_truck['in_shop_note']),"Safety Shut Down!") > 0)
				{
					$shopper="<span class='alert' title='This Truck is Shut Down for Safety... please do not use until fixed.'><b>SAFETY</b></span> ";		
				}
								
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
     			
     			$tester=mrr_find_fed_inspection_last_completed(1,$row_truck['id']);
				if(trim($tester)!="")		$warning_flag=trim($tester);		
     			
     			if($show_alert > 0) 
     			{
     				$res.="<li><span>".$truck_name."</span>  <strong title='".$titler." ".$coder."'>".$driver_name."&nbsp;".$icon."</strong></li>";
     								
				$sqluu="
					insert into mrr_not_truck_movement
						(id,
						linedate,
						truck_id, 
						truck_name, 
						maint_marks,
						truck_mode)
					values
						(NULL,
						'".date("Y-m-d",time())."',
						'".$row_truck['id']."',
						'".sql_friendly($truck_name)."',
						'".sql_friendly($driver_name."&nbsp;".$icon)."',
						1)
				";
				simple_query($sqluu);

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
     		
     		//$tmp_date = $startdate;
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
     			
     			$shopper="";
				if($row_trailers['in_the_shop'] > 0)	 
				{
					$shopper="<span class='alert'><b>SHOP</b></span> ";
				}
     			if(substr_count(trim($row_trailers['in_shop_notes']),"Safety Shut Down!") > 0)
				{
					$shopper="<span class='alert' title='This Trailer is Shut Down for Safety... please do not use until fixed.'><b>SAFETY</b></span> ";		
				}
     			
     			
     			$tester=mrr_find_fed_inspection_last_completed(2,$row_trailers['id']);
				if(trim($tester)!="")		$shopper=trim($tester);
				
     			if(trim($use_namer)!="unknown" && trim($use_namer)!="unkown")
     			{    
     			    //swapped trailer admin page link with dropped trailer link for Justin....5/11....MRR.
     				$res.="<li>
          					<span trailer_id='$row_trailers[id]' popup=\"Trailer: $use_namer<br>Location: $row_trailers[current_location]\">
          						<!----
          						<a href='admin_trailers.php?id=".$row_trailers['id']."' style='color:#ABABAB; font-weight:normal;' title='".$titler."'>
          							".($row_trailers['special_project']  > 0 ? "<div class='special_project' style='display:inline;'>".$user_attacher1."</div>" : $user_attacher1 )." 
          						</a>  
          						<a href='trailer_drop.php?id=0&trailer_id=".$row_trailers['id']."' target='_blank' style='color:#ABABAB; font-weight:normal;'>
          						    ".($row_trailers['special_project']  > 0 ? "<div class='special_project' style='display:inline;'>".$user_attacher1."</div>" : $user_attacher1 )." 
          						</a> 
          						----->
          						<a href='#' onclick='window.open(\"trailer_drop.php?id=0&trailer_id=".$row_trailers['id']."\"); return false;' style='color:#ABABAB; font-weight:normal;'>
          						    ".($row_trailers['special_project']  > 0 ? "<div class='special_project' style='display:inline;'>".$user_attacher1."</div>" : $user_attacher1 )."
          						</a>          						 						
          					</span> 
          					<a href='javascript:detach_trailer($row_trailers[attached_trailer_id],$row_trailers[driver_id])'>
          						<img src='".$new_style_path."red_circle.png' width='15' height='14' alt='red_circle'>
          					</a>     					
          						".$shopper."".($row_trailers['special_project']  > 0 ? "<div class='special_project' style='display:inline;'>".$use_attacher."</div>" : $use_attacher )." 					
          				</li>";
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
     			}     			
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
				
				$titler2=trim("$row_trucks[name_driver_first] $row_trucks[name_driver_last]");
				$titler2=str_replace("'","&apos;",$titler2);
				$user_attacher2=trim("$row_trucks[name_driver_first] $row_trucks[name_driver_last]");
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
					$shopper="<div class='alert' style='display:inline;' title='This Truck is in the Shop'><b>SHOP</b> &nbsp; </div>";
					
					if(substr_count(trim($row_trucks['in_shop_note']),"Safety Shut Down!") > 0)
					{
						$shopper="<div class='alert' style='display:inline;' title='This Truck is Shut Down for Safety... please do not use until fixed.'><b>SAFETY</b> &nbsp; </div> ";		
					}
										
					if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";					
					//$shot_this_truck=0;
				}
							
				$holder="";                
                if($row_trucks['hold_for_driver'] > 0)	
     			{
     				$holder="<div class='alert' style='display:inline;' title='This Truck is on Hold'><b>HOLD</b> &nbsp; </div> ";	
     				if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";	
     				//$shot_this_truck=0;	
     			}
                if($row_trucks['truck_is_sold'] > 0)    $holder="<div class='alert' style='display:inline;' title='This Truck has been Sold'><b>SOLD</b> &nbsp; </div> "; 
     			
     			if($row_trucks['in_body_shop'] > 0)	 
				{
					$shopper="<div class='alert' style='display:inline;' title='This Truck is in the Body Shop, Long Term'><b>BODY</b> &nbsp; </div>";
					if(trim($user_attacher)=="<span class='alert' title='Available Truck'><b>Available</b></span>")		$user_attacher="";
					//$holder="";
					//$shot_this_truck=0;
				}
     			
     			$tester=mrr_find_fed_inspection_last_completed(1,$row_trucks['id']);
				if(trim($tester)!="")		$warning_flag=trim($tester);
     			
     			if($shot_this_truck > 0)
     			{     	
     				//	javascript:add_entry_truck_id('".date("Y-m-d", time())."',$row_trucks[id])	
					$res.="<li>
						<span>
							<a href=\"admin_trucks.php?id=".$row_trucks['id']."\" style='color:#ABABAB; font-weight:normal;' title='".$titler."'>".$user_attacher1."</a> 
							".$warning_flag."
						</span> 
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
		
		$last_day=$day;
		$next_day=$day;
		
		$next_month=($mon+1);
		$last_month=($mon-1);
		$next_year=$year;
		$last_year=$year;
		if($mon==1)
		{
			$last_month="12";	
			$last_year=($year-1);	
			
			if($day>28) $next_day=28;
		}
        if($mon==3 && $day>28)      $last_day=28;
		if($mon==12)
		{
			$next_month="1";
			$next_year=($year+1);			
		}
         
        if(($mon==3 || $mon==5 || $mon==8 || $mon==10) && $day>30)     $next_day=30;
        if(($mon==5 || $mon==7 || $mon==10 || $mon==12) && $day>30)     $last_day=30;
				
		$res.="<div id='calender'>";
		$res.=	"<div class='head'>";
		//$res.=		"<a href='#' class='plus'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=		"<div class='date_change'>";
		$res.=			"<a href='index.php?mCheck=1&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$last_day."&cal_mon=".$last_month."&cal_year=".$last_year."'><img src='".$new_style_path."left_changer.png' alt=''/></a>";
		$res.=			"<span>".$fullm." ".$year."</span> <span class='mrr_link_like_on' onclick='mrr_swap_section(1);'>Switch</span>";
		$res.=			"<a href='index.php?mCheck=1&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$next_day."&cal_mon=".$next_month."&cal_year=".$next_year."'><img src='".$new_style_path."right_changer.png' alt=''/></a>";
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
		
		
		$styler=" style='font-size:12px; padding-left:10px;'";
		
		$days_counted=(int)$defaultsarray['trailer_pmi_date_days'];
		$feds_counted=(int)$defaultsarray['trailer_fed_date_days'];
		
			
		$list_pmi2="
			<table cellpadding='0' celspacing='0' boder='0' width='100%'>
			<tr>
				<td valign='top'".$styler."><b>Truck</b></td>
				<td valign='top'".$styler."><b>PM</b></td>
				<td valign='top'".$styler."><b>FED</b></td>
			</tr>	
		";
				
		$sql = "
			select *		
			from trucks
			where deleted <= 0
				and active > 0			
			order by name_truck
		";		//and pm_inspection_date!='0000-00-00 00:00:00'
		$data = simple_query($sql);
		
		while($row=mysqli_fetch_array($data))
		{
			$used=0;
			$list_pmi_insert="";
			
			if($days_counted > 0)
			{
				if($row['pm_inspection_date']=="0000-00-00 00:00:00")
				{					
					$list_pmi_insert.="<td valign='top'".$styler."><a href='maint.php?id=0&e_type=1&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",time())."' target='_blank' style='font-size:10px;'><span class='alert'><b>OVERDUE!</b></span></a></td>";
					$used=1;
				}
				else
				{
					$now_dater=date("ymd",time());
					$next_run=date("m/d/Y", strtotime("+".$days_counted." days", strtotime($row['pm_inspection_date'])));	
					$due_compare=date("ymd",strtotime($next_run));
					if((int) $due_compare <= (int) $now_dater)
					{
						$list_pmi_insert.="<td valign='top'".$styler."><a href='maint.php?id=0&e_type=1&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' style='font-size:10px;'><span class='alert'><b>DUE ".$next_run."</b></span></a></td>";
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
				if($row['fd_inspection_date']=="0000-00-00 00:00:00")
				{					
					$list_pmi_insert.="<td valign='top'".$styler."><a href='maint.php?id=0&e_type=1&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",time())."' target='_blank' style='font-size:10px;'><span class='alert'><b>OVERDUE!</b></span></a></td>";
					$used=1;
				}
				else
				{
					$now_dater=date("ymd",time());
					$next_run=date("m/d/Y", strtotime("+".$feds_counted." days", strtotime($row['fd_inspection_date'])));	
					$due_compare=date("ymd",strtotime($next_run));
					if((int) $due_compare <= (int) $now_dater)
					{
						$list_pmi_insert.="<td valign='top'".$styler."><a href='maint.php?id=0&e_type=1&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' style='font-size:10px;'><span class='alert'><b>DUE ".$next_run."</b></span></a></td>";
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
				$list_pmi2.="<tr>";
				$list_pmi2.=	"<td valign='top'".$styler."><a href='admin_trucks.php?id=".$row['id']."' target='_blank'".$styler.">".$row['name_truck']."</a></td>";		
				$list_pmi2.=	$list_pmi_insert;
				$list_pmi2.="</tr>";
			}
		}	
		$list_pmi2.="<tr><td valign='top' colspan='3'".$styler.">&nbsp;</td></tr>";
		$list_pmi2.="</table>";	
		
		
		
		
		$list_pmi="
			<table cellpadding='0' celspacing='0' boder='0' width='100%'>
			<tr>
				<td valign='top'".$styler."><b>Trailer</b></td>
				<td valign='top'".$styler."><b>PMI</b></td>
				<td valign='top'".$styler."><b>FED</b></td>
			</tr>	
		";
		
		$sql = "
			select *		
			from trailers
			where deleted <= 0
				and active > 0
				and (pmi_test_ignore<=0 or fed_test_ignore<=0)				
			order by trailer_name
		";		//and linedate_last_pmi!='0000-00-00 00:00:00'
		// removed  the select count(*) from the query since the "equipment_history_entry" variable didn't look like it was being used (CS - 1/25/2016)
		// (select count(*) from equipment_history where equipment_type_id = 2 and equipment_id = trailers.id and deleted = 0) as equipment_history_entry
		$data = simple_query($sql);		
		while($row=mysqli_fetch_array($data))
		{
			$used=0;
			$list_pmi_insert="";
			
			if($days_counted > 0 && $row['pmi_test_ignore'] ==0)
			{
				if($row['linedate_last_pmi']=="0000-00-00 00:00:00")
				{					
					$list_pmi_insert.="<td valign='top'".$styler."><a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",time())."' target='_blank' style='font-size:10px;'><span class='alert'><b>OVERDUE!</b></span></a></td>";
					$used=1;
				}
				else
				{
					$now_dater=date("ymd",time());
					$next_run=date("m/d/Y", strtotime("+".$days_counted." days", strtotime($row['linedate_last_pmi'])));	
					$due_compare=date("ymd",strtotime($next_run));
					if((int) $due_compare <= (int) $now_dater)
					{
						$list_pmi_insert.="<td valign='top'".$styler."><a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' style='font-size:10px;'><span class='alert'><b>DUE ".$next_run."</b></span></a></td>";
						$used=1;
					}
					else
					{
						$list_pmi_insert.="<td valign='top'".$styler.">&nbsp;</td>";
					}
				}
			}
			else
			{
				$list_pmi_insert.="<td valign='top'".$styler.">&nbsp;</td>";
			}
			
			if($feds_counted > 0 && $row['fed_test_ignore'] ==0)
			{
				if($row['linedate_last_fed']=="0000-00-00 00:00:00")
				{					
					$list_pmi_insert.="<td valign='top'".$styler."><a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",time())."' target='_blank' style='font-size:10px;'><span class='alert'><b>OVERDUE!</b></span></a></td>";
					$used=1;
				}
				else
				{
					$now_dater=date("ymd",time());
					$next_run=date("m/d/Y", strtotime("+".$feds_counted." days", strtotime($row['linedate_last_fed'])));	
					$due_compare=date("ymd",strtotime($next_run));
					if((int) $due_compare <= (int) $now_dater)
					{
						$list_pmi_insert.="<td valign='top'".$styler."><a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' style='font-size:10px;'><span class='alert'><b>DUE ".$next_run."</b></span></a></td>";
						$used=1;
					}
					else
					{
						$list_pmi_insert.="<td valign='top'".$styler.">&nbsp;</td>";
					}
				}
			}	
			else
			{
				$list_pmi_insert.="<td valign='top'".$styler.">&nbsp;</td>";
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
		
		$res.=	"<div class='head truck_pmi'>";
		//$res.=		"<a href='#' class='plus'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=		"<div class='date_change'>";
		$res.=			"<span>Truck PM/FED</span> <span class='mrr_link_like_on' onclick='mrr_swap_section(2);'>Switch</span>";
		$res.=		"</div>";
		$res.=	"</div>";
		$res.=	"<div class='table_sec truck_pmi'>";
		$res.=		"<table width='100%' cellpadding='0' cellspacing='0'>";
		$res.=		"<tr>";
		$res.=			"<td><div style='width:100%; height:311px; overflow:scroll; font-size:12px; text-align:left;'>".$list_pmi2."</div></td>";		
		$res.=		"</tr>";
		$res.=		"</table>";
		$res.=	"</div>";	
		
		$res.=	"<div class='head trailer_pmi'>";
		//$res.=		"<a href='#' class='plus'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=		"<div class='date_change'>";
		$res.=			"<span>Trailer PMI/FED</span> <span class='mrr_link_like_on' onclick='mrr_swap_section(0);'>Switch</span>";
		$res.=		"</div>";
		$res.=	"</div>";
		$res.=	"<div class='table_sec trailer_pmi'>";
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
		
		
		//check for any broken down trucks/trailers to show...they may be in the middle of a dispatch or not..so they may nto otherwise be obvious to dispatchers.
		$sqlx = "
			select maint_requests.*,
				trucks.name_truck		
			from maint_requests
				left join trucks on trucks.id=maint_requests.ref_id
			where (maint_requests.active > 0 and maint_requests.linedate_completed='0000-00-00 00:00:00')
				and maint_requests.unit_breakdown > 0
				and maint_requests.deleted <= 0
				and (maint_requests.equip_type=1 or maint_requests.equip_type=58)
		";
		$datax = simple_query($sqlx);
		
		if(mysqli_num_rows($datax)) 
		{			
			$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>Broken Down Trucks:</span></h3>";
			$res.=	"</li>";
			
			while($rowx = mysqli_fetch_array($datax)) 
			{				
				$res.=	"<li>";
				$res.=		"<h3>
								<span>
									<a href=\"admin_trucks.php?id=$rowx[ref_id]\" target='_blank' title='View Truck ID $rowx[ref_id] Admin Page'>".$rowx['name_truck']."</a>
								</span>
								<a href=\"maint.php?id=$rowx[id]\" target='_blank' title='View Maintenance Request ID $rowx[id]'>MR $rowx[id]</a>
							</h3>";				
				$res.=	"</li>";
			}			
		}
		
		$days=15;
		$alerts=mrr_show_old_dispatches_opened_count($days);
		if($alerts > 0)
		{
			$res.=	"<li>";
			$res.=		"<h3><span style='color:purple;'>Old Dispatches Opened:</span></h3>";
			$res.=	"</li>";	
			$res.=	"<li>";
			$res.=		"<h3>
							<span>
								<b>".$alerts." Dispatches Found</b>
							</span>
							<a href=\"report_dispatch_alerts.php\" target='_blank' title='View Old Dispatches Opened to see alerts'>View</a>
						</h3>";				
			$res.=	"</li>";
		}
		
		$sqlx = "
			select maint_requests.*,
				trailers.trailer_name	
			from maint_requests
				left join trailers on trailers.id=maint_requests.ref_id
			where (maint_requests.active > 0 and maint_requests.linedate_completed='0000-00-00 00:00:00')
				and maint_requests.unit_breakdown > 0
				and maint_requests.deleted <= 0
				and maint_requests.recur_flag <= 0
				and (maint_requests.equip_type=2 or maint_requests.equip_type=59)
		";
		$datax = simple_query($sqlx);
		
		if(mysqli_num_rows($datax)) 
		{			
			$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>Broken Down Trailers:</span></h3>";
			$res.=	"</li>";
			
			while($rowx = mysqli_fetch_array($datax)) 
			{				
				$res.=	"<li>";
				$res.=		"<h3>
								<span>
									<a href=\"admin_trailers.php?id=$rowx[ref_id]\" target='_blank' title='View Trailer ID $rowx[ref_id] Admin Page'>".$rowx['trailer_name']."</a>
								</span>
								<a href=\"maint.php?id=$rowx[id]\" target='_blank' title='View Maintenance Request ID $rowx[id]'>MR $rowx[id]</a>
							</h3>";				
				$res.=	"</li>";
			}			
		}
		
		
		// check for any dispatches that have an invalid date
		$sql = "
			select *
			
			from trucks_log
			where linedate < '1970-01-01' 
				and deleted <= 0
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
		
		
		$vres=mrr_warning_of_improper_date_time_for_loads(); // located in the functions_peoplenet2vol.php file
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
			where deleted<=0
				and vpl_imported>0
				and vpl_import_processed<=0
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
     			and notes.deleted <= 0
     			and notes.customer_id <= 0
     			and notes.linedate <= 0
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
		
		$result_limit = 100;
		$future_time = "+30 day";	
		$res="";
		
		$today=date("Y-m-d",time())." 23:59:59";		//get end of today's date/time
		$days=15;
		$adder=" and (linedate_review_due='0000-00-00 00:00:00' or linedate_review_due<='".$today."')";
		if($days>0)	$adder=" and (linedate_review_due='0000-00-00 00:00:00' or linedate_review_due<=DATE_ADD('".$today."' ,INTERVAL ".(int) $days." DAY) )";
		
		if($defaultsarray['load_board_display_events'] ==0)			return $res;
		
		$sql = "
			select mrr_linedate,mrr_linedate2,c_reason,calendar_id
			from (
     			
     			select linedate_license_expires as mrr_linedate,
     				DATE_FORMAT(linedate_license_expires, '%m-%d') as mrr_linedate2,
     				concat('License Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers
     			where deleted <= 0
     				and active > 0
     				and (linedate_license_expires <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_license_expires > 0)
     			
     			union all	
     						
     			select linedate_drugtest as mrr_linedate,
     				DATE_FORMAT(linedate_drugtest, '%m-%d') as mrr_linedate2,
     				concat('Physical due for: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers
     			where deleted <= 0
     				and active > 0
     				and (linedate_drugtest <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_drugtest > 0)    
     				
     			union all 
     			     			
     			select linedate_misc_cert as mrr_linedate,	
     				DATE_FORMAT(linedate_misc_cert, '%m-%d') as mrr_linedate2,		
     				concat(misc_cert_name, ' Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>.') as c_reason,
     				0 as calendar_id
     				
     			from drivers 
     			where (linedate_misc_cert <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_misc_cert > 0)
     				and misc_cert_name!=''			
     				and deleted <= 0
     				and active > 0
     				     				
     			union all 
     			
     						
     			select linedate_own_op_ins_exp as mrr_linedate,
     				DATE_FORMAT(linedate_own_op_ins_exp, '%m-%d') as mrr_linedate2,
     				concat('O.O. Insurance Expires: <a href=\"admin_trucks.php?id=',id,'\" target=\"view_truck_',id,'\">Truck ', name_truck,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from trucks
     			where linedate_own_op_ins_exp > 0
     				and linedate_own_op_ins_exp <= '".date("Y-m-d", strtotime($future_time, time()))."'
     				and (own_op_ins_flag > 0 or owner_operated>0)
     				and deleted <= 0
     				and active > 0
     			
     			union all
     						
     			select linedate_own_op_ins_exp as mrr_linedate,
     				DATE_FORMAT(linedate_own_op_ins_exp, '%m-%d') as mrr_linedate2,
     				concat('No O.O. Insurance: <a href=\"admin_trucks.php?id=',id,'\" target=\"view_truck_',id,'\">Truck ', name_truck,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from trucks
     			where linedate_own_op_ins_exp ='0000-00-00 00:00:00'
     				and (own_op_ins_flag > 0 or owner_operated>0)
     				and deleted <= 0
     				and active > 0
     				
     			union all	
     			
     			
     						
     			select linedate_own_op_acc_ins_exp as mrr_linedate,
     				DATE_FORMAT(linedate_own_op_acc_ins_exp, '%m-%d') as mrr_linedate2,
     				concat('O.O. A.C.C. Insurance Expires: <a href=\"admin_trucks.php?id=',id,'\" target=\"view_truck_',id,'\">Truck ', name_truck,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from trucks
     			where linedate_own_op_acc_ins_exp > 0
     				and linedate_own_op_acc_ins_exp <= '".date("Y-m-d", strtotime($future_time, time()))."'
     				and (own_op_acc_ins_flag > 0 or owner_operated>0)
     				and deleted <= 0
     				and active > 0
     			
     			union all
     						
     			select linedate_own_op_acc_ins_exp as mrr_linedate,
     				DATE_FORMAT(linedate_own_op_acc_ins_exp, '%m-%d') as mrr_linedate2,
     				concat('No O.O. A.C.C. Insurance: <a href=\"admin_trucks.php?id=',id,'\" target=\"view_truck_',id,'\">Truck ', name_truck,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from trucks
     			where linedate_own_op_acc_ins_exp ='0000-00-00 00:00:00'
     				and (own_op_acc_ins_flag > 0 or owner_operated>0)
     				and deleted <= 0
     				and active > 0
     				
     			union all	
     						
     			select linedate_ifta as mrr_linedate,
     				DATE_FORMAT(linedate_ifta, '%m-%d') as mrr_linedate2,
     				concat('IFTA Decal Exp.: <a href=\"admin_trucks.php?id=',id,'\" target=\"view_truck_',id,'\">Truck ', name_truck,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from trucks
     			where linedate_ifta !='0000-00-00 00:00:00'
     				and (linedate_ifta <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_ifta > 0)
     				and deleted <= 0
     				and active > 0
     				
     			union all	
     						
     			select linedate_cab_card as mrr_linedate,
     				DATE_FORMAT(linedate_cab_card, '%m-%d') as mrr_linedate2,
     				concat('Cab Card Exp.: <a href=\"admin_trucks.php?id=',id,'\" target=\"view_truck_',id,'\">Truck ', name_truck,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from trucks
     			where linedate_cab_card !='0000-00-00 00:00:00'
     				and (linedate_cab_card <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_cab_card > 0)
     				and deleted <= 0
     				and active > 0	
     				
     			union all	     					
     			
     			select linedate_cov_expires as mrr_linedate,
     				DATE_FORMAT(linedate_cov_expires, '%m-%d') as mrr_linedate2,
     				concat('MVR Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers
     			where deleted <= 0
     				and active = 1
     				and (linedate_cov_expires <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_cov_expires > 0)
     							
     			union all 
     			
     			select linedate_review_due as mrr_linedate,
     				DATE_FORMAT(linedate_review_due, '%m-%d') as mrr_linedate2,
     				concat('Review Due Date: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers
     			where deleted <= 0
     				and active > 0
     				and id!=405
     				".$adder." 
     				and linedate_review_due!='0000-00-00 00:00:00'	
     				
     			union all
     			
     			select linedate_birthday as mrr_linedate,	
     				DATE_FORMAT(linedate_birthday, '%m-%d') as mrr_linedate2,			
     				concat('Birthday: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers 
     			where DATE_FORMAT(linedate_birthday,'%m-%d') <= '".date("m-d", strtotime("+5 day", time()))."' 
     				and DATE_FORMAT(linedate_birthday,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
     				and deleted <= 0
     				and active > 0
     			
     			
     			union all			
     			
     			select linedate_spouse as mrr_linedate,			
     				DATE_FORMAT(linedate_spouse, '%m-%d') as mrr_linedate2,
     				concat('Birthday: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a> Spouse <b>',spouse_name,'</b>.') as c_reason,
     				0 as calendar_id
     				
     			from drivers 
     			where DATE_FORMAT(linedate_spouse,'%m-%d') <= '".date("m-d", strtotime("+10 day", time()))."' 
     				and DATE_FORMAT(linedate_spouse,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
     				and deleted <= 0
     				and active > 0     				
     			
     			union all			
     			
     			select linedate_anniversary as mrr_linedate,		
     				DATE_FORMAT(linedate_anniversary, '%m-%d') as mrr_linedate2,	
     				concat('Anniversary: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a> and <b>',spouse_name,'</b>.') as c_reason,
     				0 as calendar_id
     				
     			from drivers 
     			where DATE_FORMAT(linedate_anniversary,'%m-%d') <= '".date("m-d", strtotime("+30 day", time()))."' 
     				and DATE_FORMAT(linedate_anniversary,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
     				and deleted <= 0
     				and active > 0
     			
			) tds		
							
			order by mrr_linedate2 asc,mrr_linedate asc			
		";	//month(linedate), day(linedate)		
		//order by mrr_linedate asc
		//limit $result_limit		
		/*
			select linedate_own_op_ins as mrr_linedate,
				concat('O.O. Insurance Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_own_op_ins > 0
				and DATE_ADD(linedate_own_op_ins,INTERVAL 1 YEAR) <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and (own_op_ins_flag > 0 or  owner_operator>0)
				and deleted <= 0
				and active > 0		
			
			union 
		*/		
		$data = simple_query($sql);
		
		/*
		$last_date="";
		$arr_cntr=31;
		$arr_date[0]="";
		$arr_block[0]="";
		for($i=0; $i < $arr_cntr; $i++)
		{
			$arr_date[$i]="".date("Y-m-d",strtotime("+".$i." days",time()))."";
			$arr_block[$i]="";
		}		
		
		//driver's birthday soon
		$sql="
				select linedate_birthday as mrr_linedate,				
     				concat('Birthday: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers 
     			where DATE_FORMAT(linedate_birthday,'%m-%d') <= '".date("m-d", strtotime("+5 day", time()))."' 
     				and DATE_FORMAT(linedate_birthday,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
     				and deleted <= 0
     				and active > 0
     				
     			order by mrr_linedate asc
     			limit $result_limit
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$block="";
			
			$tag1="";
			$tag2="";
			if($row['mrr_linedate'] < $test_date)
			{
				$tag1="<span style='color:red;'><b>";
				$tag2="</b></span>";
				$row['c_reason']=str_replace("Expires","Expired",$row['c_reason']);	
			}
			
			$block.=	"<li>";
     		$block.=		"<h3>";
     		$block.=			"<span>".date("M, j", strtotime($row['mrr_linedate']))."</span>";
     		//$block.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     		//$block.=			"<a href='javascript:edit_event($row[calendar_id])'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";     			
     		$block.=		"</h3>";
     		$block.=		"<p>".$tag1."".trim($row['c_reason'])."".$tag2."</p> ";
     		$block.=	"</li>";
     		
			for($i=0; $i < $arr_cntr; $i++)
			{
				if(date("Y-m-d",strtotime($row['mrr_linedate'])) <= $arr_date[$i])
				{
					$arr_block[$i].="".trim($block)."<li><h3>".$i."</h3><p>".date("Y-m-d",strtotime($row['mrr_linedate']))." | ".$arr_date[$i]."</p></li>";	//add to this block.
					$block="";						//clear to stop from adding twice.
				}
			}
		}
		
		//spouse's birthday coming soon
		$sql="
				select linedate_spouse as mrr_linedate,			
     				concat('Birthday: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a> Spouse <b>',spouse_name,'</b>.') as c_reason,
     				0 as calendar_id
     				
     			from drivers 
     			where DATE_FORMAT(linedate_spouse,'%m-%d') <= '".date("m-d", strtotime("+10 day", time()))."' 
     				and DATE_FORMAT(linedate_spouse,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
     				and deleted <= 0
     				and active > 0 
     				
     			order by mrr_linedate asc
     			limit $result_limit
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$block="";
			
			$tag1="";
			$tag2="";
			if($row['mrr_linedate'] < $test_date)
			{
				$tag1="<span style='color:red;'><b>";
				$tag2="</b></span>";
				$row['c_reason']=str_replace("Expires","Expired",$row['c_reason']);	
			}
			
			$block.=	"<li>";
     		$block.=		"<h3>";
     		$block.=			"<span>".date("M, j", strtotime($row['mrr_linedate']))."</span>";
     		//$block.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     		//$block.=			"<a href='javascript:edit_event($row[calendar_id])'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";     			
     		$block.=		"</h3>";
     		$block.=		"<p>".$tag1."".trim($row['c_reason'])."".$tag2."</p> ";
     		$block.=	"</li>";
     		
			for($i=0; $i < $arr_cntr; $i++)
			{
				if(date("Y-m-d",strtotime($row['mrr_linedate'])) <= $arr_date[$i])
				{
					$arr_block[$i].="".trim($block)."<li><h3>".$i."</h3><p>".date("Y-m-d",strtotime($row['mrr_linedate']))." | ".$arr_date[$i]."</p></li>";	//add to this block.
					$block="";						//clear to stop from adding twice.
				}
			}
		}
		
		//anniversary approaching
		$sql="
				select linedate_anniversary as mrr_linedate,			
     				concat('Anniversary: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a> and <b>',spouse_name,'</b>.') as c_reason,
     				0 as calendar_id
     				
     			from drivers 
     			where DATE_FORMAT(linedate_anniversary,'%m-%d') <= '".date("m-d", strtotime("+30 day", time()))."' 
     				and DATE_FORMAT(linedate_anniversary,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
     				and deleted <= 0
     				and active > 0
     				
     			order by mrr_linedate asc
     			limit $result_limit
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$block="";
			
			$tag1="";
			$tag2="";
			if($row['mrr_linedate'] < $test_date)
			{
				$tag1="<span style='color:red;'><b>";
				$tag2="</b></span>";
				$row['c_reason']=str_replace("Expires","Expired",$row['c_reason']);	
			}
			
			$block.=	"<li>";
     		$block.=		"<h3>";
     		$block.=			"<span>".date("M, j", strtotime($row['mrr_linedate']))."</span>";
     		//$block.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     		//$block.=			"<a href='javascript:edit_event($row[calendar_id])'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";     			
     		$block.=		"</h3>";
     		$block.=		"<p>".$tag1."".trim($row['c_reason'])."".$tag2."</p> ";
     		$block.=	"</li>";
     		
			for($i=0; $i < $arr_cntr; $i++)
			{
				if(date("Y-m-d",strtotime($row['mrr_linedate'])) <= $arr_date[$i])
				{
					$arr_block[$i].="".trim($block)."<li><h3>".$i."</h3><p>".date("Y-m-d",strtotime($row['mrr_linedate']))." | ".$arr_date[$i]."</p></li>";	//add to this block.
					$block="";						//clear to stop from adding twice.
				}
			}
		}
		
		//drug testing expires/due soon
		$sql="
				select linedate_drugtest as mrr_linedate,
     				concat('Physical due for: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers
     			where deleted <= 0
     				and active > 0
     				and (linedate_drugtest <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_drugtest > 0)    
     				
     			order by mrr_linedate asc
     			limit $result_limit
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			
		}
		
		//Drivers License expires/coming due
		$sql="
				select linedate_license_expires as mrr_linedate,
     				concat('License Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers
     			where deleted <= 0
     				and active > 0
     				and (linedate_license_expires <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_license_expires > 0)
     				
     			order by mrr_linedate asc
     			limit $result_limit
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			
		}
		
		//Misc. Certs
		$sql="
     			select linedate_misc_cert as mrr_linedate,			
     				concat(misc_cert_name, ' Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>.') as c_reason,
     				0 as calendar_id
     				
     			from drivers 
     			where (linedate_misc_cert <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_misc_cert > 0)
     				and misc_cert_name!=''			
     				and deleted <= 0
     				and active > 0   	
     				
     			order by mrr_linedate asc
     			limit $result_limit			
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			
		}
		
		//O.O. Insurance Expired
		$sql="
				select linedate_own_op_ins as mrr_linedate,
     				concat('O.O. Insurance Expires: <a href=\"admin_trucks.php?id=',id,'\" target=\"view_truck_',id,'\">Truck ', name_truck,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from trucks
     			where linedate_own_op_ins > 0
     				and DATE_ADD(linedate_own_op_ins,INTERVAL 1 YEAR) <= '".date("Y-m-d", strtotime($future_time, time()))."'
     				and (own_op_ins_flag > 0 or owner_operated>0)
     				and deleted <= 0
     				and active > 0
     				
     			order by mrr_linedate asc
     			limit $result_limit
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			
		}
		
		//No O.O. Insurnace, but should have it.
		$sql="
				select linedate_own_op_ins as mrr_linedate,
     				concat('No O.O. Insurance: <a href=\"admin_trucks.php?id=',id,'\" target=\"view_truck_',id,'\">Truck ', name_truck,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from trucks
     			where linedate_own_op_ins ='0000-00-00 00:00:00'
     				and (own_op_ins_flag > 0 or owner_operated>0)
     				and deleted <= 0
     				and active > 0
     				
     			order by mrr_linedate asc
     			limit $result_limit
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			
		}
		
		//COV Expires or approaching...now MVR as of 5/9/2022...MRR
		$sql="
				select linedate_cov_expires as mrr_linedate,
     				concat('MVR Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers
     			where deleted <= 0
     				and active > 0
     				and (linedate_cov_expires <= '".date("Y-m-d", strtotime($future_time, time()))."' and linedate_cov_expires > 0)
     				
     			order by mrr_linedate asc
     			limit $result_limit
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			
		}
		
		//Review Date coming up or past due
		$sql="
				select linedate_review_due as mrr_linedate,
     				concat('Review Due Date: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
     				0 as calendar_id
     				
     			from drivers
     			where deleted<=0
     				and active > 0
     				and id!=405
     				".$adder." 
     				and linedate_review_due!='0000-00-00 00:00:00'	
     				
     			order by mrr_linedate asc
     			limit $result_limit
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			
		}
		*/
		
		
		
		
		$res="";
				
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar yellow'>";
		$res.=		"<span class='notes'>Events</span>";
		$res.=		"<a href='javascript:edit_event(0)'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=	"</div> ";
		
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul>";
		
		$test_date=date("Y-m-d")." 00:00:00";
		
		
		$cntr=0;
		//build events calendar list...		
		while($row = mysqli_fetch_array($data)) 
		{
			if($cntr < $result_limit)
			{
     			$tag1="";
     			$tag2="";
     			if($row['mrr_linedate'] < $test_date)
     			{
     				$tag1="<span style='color:red;'><b>";
     				$tag2="</b></span>";
     				$row['c_reason']=str_replace("Expires","Expired",$row['c_reason']);	
     			}
     			$res.=	"<li>";
          		$res.=		"<h3>";
          		$res.=			"<span>".($row['mrr_linedate'] =="0000-00-00 00:00:00" || $row['mrr_linedate'] =="1969-12-31 00:00:00" || !isset($row['mrr_linedate']) ? "N/A" : date("M, j", strtotime($row['mrr_linedate'])))."</span>";
          		//$res.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
          		//$res.=			"<a href='javascript:edit_event($row[calendar_id])'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";     			
          		$res.=		"</h3>";
          		$res.=		"<p>".$tag1."".trim($row['c_reason'])."".$tag2."</p> ";
          		$res.=	"</li>";
          		$cntr++;
     		}
		}
		
		/*
		for($i=0; $i < $arr_cntr; $i++)
		{
			$res.=trim($arr_block[$i]);		//$arr_date[$i]
		}
		*/
		
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
		
		$result_limit = 2000;
		$future_time = "365 days";	//was 30 days
		$future_time2 = "-3 days";	
				
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
			where drivers.deleted <= 0
				and drivers.active > 0
				and drivers_unavailable.deleted <= 0
				and drivers.id = drivers_unavailable.driver_id
				and drivers_unavailable.linedate_end >= '".date("Y-m-d")."'
			
			union 
			
			select linedate as linedate,
				linedate as linedate_to,
				user_id as user,
				concat('User: <a href=\"admin_users.php?eid=',users.id,'\" target=\"view_user_',users.id,'\">', name_first, ' ', name_last,'</a>: ',driver_reason) as c_reason,
				driver_reason as c_reason2,
				2 as from_calendar,
				driver_absenses.id as calendar_id,
				'staff' as desc_long
				
			from users, driver_absenses
			where users.deleted  <= 0
				and users.active > 0
				and driver_absenses.deleted = 0
				and users.id = driver_absenses.user_id
				and driver_absenses.linedate <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and driver_absenses.linedate >= '".date("Y-m-d", time())." 00:00:00'
						
						
			union

            select
              linedate AS linedate,
              linedate AS linedate_to,
              driver_id AS user,
              CONCAT('Driver: <a href=\"admin_drivers.php?id=',driver_id,'\" target=\"view_driver_', driver_id,'\">',name_driver_first,' ',name_driver_last,'</a>: ',driver_reason) AS c_reason,
              driver_reason AS c_reason2,
              0 AS from_calendar,
              driver_absenses.id AS calendar_id,
              'calendar' AS desc_long
            from drivers,
              driver_absenses
            where drivers.deleted <= 0
              and driver_absenses.deleted <= 0
              and drivers.id = driver_absenses.driver_id
              and driver_absenses.linedate <= '".date("Y-m-d", strtotime($future_time, time()))."'
			  and driver_absenses.linedate >= '".date("Y-m-d", time())." 00:00:00'		
						
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
				and deleted <= 0
				
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
		
        //$res.=		"<span style='color:#FFFFFF;'><b>";
        //$res.=		    "<span onclick='mrr_swap_time_off(1);'>Admin</span>-";
        //$res.=		    "<span onclick='mrr_swap_time_off(0);'>Drivers</span>";
        //$res.=		"</b></span>";
         
        $res.=		"<b><span id='mrr_time_off_switcher' style='color:#FFFFFF; cursor:pointer; margin-top:10px;' onclick='mrr_swap_time_off();'></span></b>";
        
        
		//$res.=		"";	//<a href='#'><img src='".$new_style_path."add.png' alt='add'></a>
		$res.=	"</div>";
		
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul>";
         
        $res.=	"<li class='mrr_time_off_drivers'>";
        $res.=		"<h3><span>DRIVERS</span>";
        //$res.=			"";
        //$res.=			"";
        $res.=		"</h3>";
        $res.=		"<p></p> ";
        $res.=	"</li>";
        $res.=	"<li class='mrr_time_off_users'>";
        $res.=		"<h3><span>ADMIN USERS</span>";
        //$res.=			"";
        //$res.=			"";
        $res.=		"</h3>";
        $res.=		"<p></p> ";
        $res.=	"</li>";		
		
		//add new vacation and cash advances to this section.....................Addd April 2014.......		
		$sqlv = "		
			select driver_vacation_advances.*,
     			drivers.name_driver_first,
				drivers.name_driver_last     			
     					
     		from driver_vacation_advances
     			left join drivers on drivers.id = driver_vacation_advances.driver_id
     		where driver_vacation_advances.deleted <= 0
     			and driver_vacation_advances.approved_by_id >0
     			and driver_vacation_advances.cancelled_by_id <=0
     			and driver_vacation_advances.cash_advance =0
     			and drivers.deleted <= 0
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
					
											
				$res.=	"<li class='mrr_time_off_drivers'>";
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
				elseif($row2['from_calendar']==2)
				{
					//$row2['c_reason']="<b>".$row2['c_reason'].":</b> ".trim($row2['desc_long']);
				}
				else
				{
					$row2['c_reason']="<b>".$row2['c_reason'].":</b> ".trim($row2['desc_long']);
				}
							
				if($row2['from_calendar']!=2)
				{
					$res.=	"<li class='mrr_time_off_".( trim($row2['desc_long'])=="staff" ? "users" : "drivers") ."'>";
					$res.=		"<h3><span>".date("M, j", strtotime($row2['linedate']))."</span>";
					$res.=			"<span onClick='mrr_edit_driver_unavailable(".$row2['calendar_id'].",".$row2['driver'].",\"".$tmp_from."\",\"".$tmp_to."\",\"".$tmp_reason."\");' style='margin-left:120px;'>
										<img src='".$new_style_path."blue_icon1.png' alt='add'>
									</span>";	
					$res.=			"<a href='javascript:".$use_java."'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";	
					$res.=		"</h3>";
					$res.=		"<p>".trim($row2['c_reason'])."</p> ";		
					$res.=	"</li>";
				}
				else
				{
					$res.=	"<li class='mrr_time_off_".( trim($row2['desc_long'])=="staff" ? "users" : "drivers") ."'>";
					$res.=		"<h3><span>".date("M, j", strtotime($row2['linedate']))."</span>";
					/*
					$res.=			"<span onClick='mrr_edit_driver_unavailable(".$row2['calendar_id'].",".$row2['driver'].",\"".$tmp_from."\",\"".$tmp_to."\",\"".$tmp_reason."\");' style='margin-left:120px;'>
										<img src='".$new_style_path."blue_icon1.png' alt='add'>
									</span>";	
					$res.=			"<a href='javascript:".$use_java."'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";	
					*/
					$res.=		"</h3>";
					$res.=		"<p>".trim($row2['c_reason'])."</p> ";		
					$res.=	"</li>";
				}
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
			where user_id_read<=0
				and no_response_needed<=0
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
		//$res.=		"<a href='index.php?geotab_on=1'>View GeoTab Messages</a>";
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
	function mrr_conard_component_geotab_msgs()
	{
		global $new_style_path;
		global $defaultsarray;
		
		$res="";
		
		if($defaultsarray['load_board_display_peoplenet'] ==0)			return $res;
		
		$offset_hrs=abs((int)$defaultsarray['gmt_offset_peoplenet']);
		$timezoning=trim($defaultsarray['gmt_offset_label']);
				
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar peoplenet'>";
		$res.=		"<span class='notes' title='Message Alerts from GeoTab Tablets'>GeoTab Msgs</span>";
		$res.=		"<a href='geotab_messenger.php' target='_blank'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=	"</div> ";
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul id='new_geotab_messages'>";
		
		$cntr=0;
		$sql="
			select geotab_messages_received.*,
				(select name_driver_first from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_first,	
				(select name_driver_last from drivers where drivers.geotab_use_id=geotab_messages_received.geotab_user order by active desc limit 1) as driver_last,
				trucks.id as my_truck_id,
				trucks.name_truck as my_truck_name
			from ".mrr_find_log_database_name()."geotab_messages_received
				left join trucks on trucks.geotab_device_id=geotab_messages_received.device_id
				
			where geotab_messages_received.msg_to_truck<=0
				and geotab_messages_received.archived<=0
				and geotab_messages_received.read_user_id<=0
				and geotab_messages_received.no_response_needed<=0
			order by geotab_messages_received.linedate_added desc,geotab_messages_received.id desc
		";
			//and msg_body NOT LIKE 'warning: %'
		$data=simple_query($sql);
		
		$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
     	//$offset_gmt=$offset_gmt * -1;
		
		$mydate=date("Y-m-d",time());		//today...
			
		while($row = mysqli_fetch_array($data)) 
		{
			//mrr_peoplenet_pull_quick_username($row['read_user_id'])
			
			$msg_id=$row['id'];
			$truck_id=$row['my_truck_id'];
			$truck_name=trim($row['my_truck_name']);
			$created=date("m/d/Y H:i",strtotime($row['linedate_added']));
			$msg_txt=trim($row['msg_body']);
			
			$driver_name=trim("".$row['driver_first']." ".$row['driver_last']."");
			
			$dres=mrr_find_pn_truck_drivers($truck_id,$mydate,1);			
			$driver=$dres['driver_name_1'];
			$load_id=$dres['load_id'];
			$disp_id=$dres['dispatch_id'];	
			//$dres['driver_id_1']=0;
			//$dres['driver_id_2']=0;
			//$dres['driver_name_2']="";	
						
			//$driver_name=trim($driver);
						
			$maint_link="";
			//$maint_link=mrr_prep_auto_maint_link($msg_id,$truck_id,trim($msg_txt),$row['alert_sent_flag']);		////truck_tracking_msg_history.alert_sent_flag as alert_sent,		
			$recipient="<b>DISPATCH</b>";
			
			if(1==1)
			{
				$res.=	"<li>";
     			$res.=		"<h3>";
     			$res.=			"<span>".$created." --- <a href='geotab_messenger.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'>".$truck_name."</a></span>";     	//admin_trucks.php?id=".$truck_id."
     			//$res.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     			$res.=			"<a href='geotab_messenger.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
     			$res.=		"</h3>";
     			$res.=		"<p>
     							Driver(s): ".$driver_name."<br>
     							<a href='geotab_messenger.php?truck_id=".$truck_id."&reply_id=".$msg_id."' target='_blank'>Unread...Click here to read.</a> ".$recipient.": ".$msg_txt."
     							<span class='mrr_link_like_on' onClick='mrr_ignore_new_messages_geotab(".$msg_id.",".$_SESSION['user_id'].");' title='click to ignore, or if no response is needed.'>
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
		
		//key/legend for the GeoTab and MSGS section... 
		$res.=	"<li>";
		//$res.=		"<a href='index.php?geotab_on=0'>View PeopleNet Messages</a>";
     	$res.=		"<h3>";
     	$res.=			"<br><span>GeoTab and MSGS Legend</span>";    			
     	$res.=		"</h3>";
     	$res.=		"<p><b>GT</b>=GeoTab Dispatch can be sent.</p> ";
     	$res.=		"<p><b>MSGS</b>=Message link for this truck.</p> ";
     	$res.=		"<p style='color:red; font-weight:bold;'>Dispatch has not been sent to truck.</p>";
     	$res.=		"<p style='color:orange; font-weight:bold;'>Updated since last send.</p>";
     	$res.=		"<p style='color:green; font-weight:bold;'>Dispatch sent to truck.</p>";
     	$res.=		"<p>Colors based on sent status. MSGS flag will always match GT link based on status of GeoTab dispatch.  MSGS is quick link to messages.</p> ";
     	$res.=		"<p>Hover on Load to see distance and current truck location.  <b>No Distance.</b> displays when dispatch has not been sent through GeoTab system and no GPS coordinates have been calculated for the stops.  Send dispatch to fix this.</p> ";
     	$res.=	"</li>";		
		
		$res.=		"</ul>";
		$res.=	"</div>";		
		$res.="</div>";
		
		$cut_off_switch=0;	//
		if($cntr > 0 && $cut_off_switch==0)
		{
			//mrr_pn_message_warning
			$msg_marq="You have ".$cntr." unread items in the GeoTab Messages.  Please check them.";
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
	function mrr_conard_component_fun_box()
	{
		global $new_style_path;
		global $defaultsarray;
		global $datasource;
				
		$res="";
		
		$header=trim($defaultsarray['fun_box_header']);
		$image=trim($defaultsarray['fun_box_img']);				$image=str_replace("'","",$image);
		$caption=trim($defaultsarray['fun_box_caption']);		$caption=str_replace("'","",$caption);
		$text=trim($defaultsarray['fun_box_text']);
         
        $task_types[0]="Other/Misc.";
        $task_types[1]="Truck";
        $task_types[2]="Trailer";
        $task_types[3]="Driver";
        $task_types[3]="Customer";
        $task_types[3]="User";
         
		$tasks="";
        $sql = "
            select internal_tasks.*
            from internal_tasks
            where internal_tasks.deleted <= 0 and internal_tasks.active>0 and internal_tasks.last_active_cntr > 0
            order by internal_tasks.active desc, internal_tasks.task_name asc,internal_tasks.id asc
	    ";
        $data = mysqli_query($datasource, $sql);
        while($row = mysqli_fetch_array($data))
        {
             $tasks.="<br>".$row['last_active_cntr']." ".$task_types[ $row['task_type'] ]."s: <a href='internal_tasks.php?task=".$row['id']."' target='_blank'><b>".$row['task_name']."</b></a>";
        }
        $text.="".$tasks."";
				
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar green'>";
		$res.=		"<span class='notes_green' title='Are you working hard or hardly working?'>".$header."</span>";		// style='border: 1px solid #cc0000;'
		$res.=	"</div> ";
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul>";
		
		//key/legend for the PN and MSGS section... 
		$res.=			"<li>";
     	$res.=				"<p><img src='/images/".$image."' alt='".$image."' width='200' border='0' title='".$caption."'></p> ";
     	$res.=				"<p>".$text."</p> ";
     	$res.=			"</li>";		
		
		$res.=		"</ul>";
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
	}
	
	function mrr_conard_component_geofencing_msgs($use_geotab=0)
	{		
		global $new_style_path;
		global $defaultsarray;
		
		$use_geotab=0;		
		//if($_SESSION['use_geotab_vs_pn']==1)		$use_geotab=1;	
		
		$res="";
		
		$sql = "
			select page_html
			
			from cache_holder
			where cache_name = 'index_mrr_conard_component_geofencing_msgs'
				and linedate_added > '".date("Y-m-d H:i:s", strtotime("-1 min", time()))."'
		";
		$data_cache = simple_query($sql);
		if(mysqli_num_rows($data_cache)) 
		{
			$row_cache = mysqli_fetch_array($data_cache);
			
			return $row_cache['page_html'];
		} 
		else 
		{			
			if($defaultsarray['peoplenet_geofencing_mph'] ==0 && $defaultsarray['peoplenet_geofencing_tolerance'] ==0)			return $res;
						
			$res.="<div class='middle_container'>";
			$res.=	"<div class='top_bar geofence'>";
			$res.=		"<span class='notes' title='Message for Geofence'>Geofence</span>";
			//$res.=		"<a href='report_peoplenet_activity.php' target='_blank'><img src='".$new_style_path."add.png' alt='View'></a>";
			$res.=		"<a href='report_geotab_activity.php' target='_blank'><img src='".$new_style_path."add.png' alt='View'></a>";
			$res.=	"</div> ";
			$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
			$res.=		"<ul id='new_pn_geofencing'>";
			
			//$reporter=mrr_pull_all_active_geofencing_rows_alt1(1);	
			$reporter=mrr_pull_all_active_geofencing_rows_alt_geotab(1);	
			
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
			where separate_truck_section > 0
				and deleted <= 0
				and active > 0
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
     		where active > 0
     			and deleted <= 0
     			and hide_available <= 0
     			and id not in (
     							select driver_id
     							
     							from trucks_log 
     							where deleted <= 0 
     								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
     								and dispatch_completed <= 0
     						)
     
     			and id not in (
     							select driver2_id
     							
     							from trucks_log 
     							where deleted <= 0 
     								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
     								and dispatch_completed <= 0
     						)
     
     			and id not in (
     							select driver_id
     							
     							from drivers_unavailable
     							where drivers_unavailable.deleted <= 0
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
			$mrr_adder=" and load_handler.dedicated_load > 0 and load_handler.customer_id='".$disp_type."'";
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id and trailers_dropped.customer_id='".$disp_type."' order by trailers_dropped.id desc limit 1)<=0";	
		}	
		else
		{
			$mrr_adder=" and load_handler.dedicated_load <= 0";	
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) > 0";
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
			where deleted <= 0
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
               	(select max(id) as id from load_handler lh where lh.preplan > 0 and lh.preplan_driver_id = trucks_log.driver_id and lh.deleted <= 0) as driver_preplanned,
               	(select count(*) from trucks_log_notes where deleted <= 0 and truck_log_id = trucks_log.id) as note_count,
				(select trucks_log_notes.linedate_added from trucks_log_notes where trucks_log_notes.deleted <= 0 and truck_log_id = trucks_log.id order by trucks_log_notes.linedate_added desc limit 1) as last_note_date
               
               from trucks_log
               	left join drivers on drivers.id = trucks_log.driver_id
               	left join trucks on trucks.id = trucks_log.truck_id
               	left join trailers on trailers.id = trucks_log.trailer_id
               	left join load_handler_stops on load_handler_stops.trucks_log_id = trucks_log.id and load_handler_stops.deleted = 0
               	left join load_handler on load_handler.id = trucks_log.load_handler_id
               where trucks_log.linedate = '".date("Y-m-d", strtotime( $startdate))."'
               	and trucks_log.deleted <= 0 
               	and dispatch_completed <= 0
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
               	and load_handler.deleted <= 0
               	and (
               		load_handler.load_available > 0
               		or 
               		(select count(*) from trucks_log where trucks_log.deleted <= 0 and trucks_log.load_handler_id = load_handler.id) <= 0
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
                    		where load_handler.preplan > 0
                    			and customers.id = load_handler.customer_id
                    			and load_handler.deleted <= 0
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
                    				
                    				$profit_history="";
                    				$last_profit=mrr_fetch_truck_profit_history($row_attached['attached_truck_id'],"",0);
                    				if($last_profit['profit'] !="0.00" && trim($last_profit['date'])!="")
                    				{
                    					$profit_history="...Profit $".number_format($last_profit['profit'],2)." as of ".date("m/d/Y",strtotime($last_profit['date'])).".";	
                    				}
                    				
                    				$res1a.=	"<tr onMouseOver='mrr_show_section(\"drivers\",$darray[driver_id])' onMouseOut='mrr_hide_section(\"drivers\",$darray[driver_id])' class='mrr_all_drivers mrr_drivers_$darray[driver_id]'>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'><a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'>[detach]</a> $row_attached[name_truck]".$warning_flag."</td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy." fright'>
                                   					<img src='images/truck_info.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_truck_history($row_attached[attached_truck_id])\" alt='Truck History' title='Truck History".$profit_history."'>
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
                    			and deleted <= 0
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
                    	//$use_background_color = ";background-color:#00cc00";
                    } 
                    elseif($row_log['color'] != '' && !$trailer_alert && $row_log['color'] != '#f8f3e4') 
                    {
                    	$use_background_color = ";background-color:$row_log[color]";
                    	//$use_background_color = ";background-color:#00cc00";
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
                    		
                    		$tnamer=trim($row_log['trailer_name']);
                    		if(trim($row_log['nick_name'])!="")	$tnamer=trim($row_log['nick_name']);
                    		
                    		$mrr_classy="gray";
                         	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                         	$res3.=	"<tr>";
                         	$res3.=		"<td class='".$mrr_classy."'>
                         					<a style='margin-right:10px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">TL DR</a>
                    					</td>";
                         	$res3.=		"<td class='".$mrr_classy."'></td>";
                         	$res3.=		"<td class='".$mrr_classy."'>".trim_string($use_location,45)."</td>";
                         	$res3.=		"<td class='".$mrr_classy."'></td>";
                         	$res3.=		"<td class='".$mrr_classy."'>".$tnamer."</td>";
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
                    	$use_image = "images/note.png";
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
                    		if($has_load_flag==0 && ($row_log['truck_id'] > 0 || $row_log['driver_id'] > 0))
						{	//double check if there is another load/dispatch for this truck...
							$hasloadflag=mrr_double_check_driver_has_load($row_log['driver_id'],$row_log['truck_id'],$row_log['id'],0);
							if($hasloadflag['load_id'] > 0 || $hasloadflag['disp_id'] > 0)
							{
								$use_image = "images/inventory.png";	
								$has_load_flag = 1;
								$has_load_id = $hasloadflag['load_id'];
								$has_disp_id = $hasloadflag['disp_id'];
							}
						}
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
                    	
                    	$profit_history="";
                    	$last_profit=mrr_fetch_truck_profit_history($row_log['truck_id'],"",0);
                    	if($last_profit['profit'] !="0.00" && trim($last_profit['date'])!="")
                    	{
                    		$profit_history="...Profit $".number_format($last_profit['profit'],2)." as of ".date("m/d/Y",strtotime($last_profit['date'])).".";	
                    	}
                    				
                    	$mrr_classy="gray";
                    	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
     
                         $use_background_color="";
     
                         if($row_log['note_count']==0 && $row_log['linedate']==date("Y-m-d",time()))
                         {
                              $use_background_color="background-color:#F1D592;";
                         }
                         if($row_log['note_count'] > 0 && $row_log['linedate']==date("Y-m-d",time()) && $row_log['last_note_date']<date("Y-m-d",strtotime("-2 hours",time())) )
                         {
                              $use_background_color="background-color:#F1D592;";
                         }
                    	
                    	
                    	$res3.=	"<tr onMouseOver='mrr_show_section(\"loads\",$row_log[load_handler_id])' onMouseOut='mrr_hide_section(\"loads\",$row_log[load_handler_id])'>";
                    	$res3.=		"<td class='".$mrr_classy."' style='".$use_background_color."'>
                    					<a href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">
                    						($row_log[load_handler_id])  
                    					</a>                    					
                    					<img log_id='$row_log[id]' class='note_image' preplan_load_id='$has_preplan' has_load_flag='$has_load_flag' has_load_id='$has_load_id' has_disp_id='$has_disp_id' src='$use_image' onclick='add_note($row_log[id])' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;".($row_log['note_count'] > 0 || $row_log['special_instructions'] != '' ? "border:1px red solid;" : "")."''>
                    					<div id='note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;width:425px;background-color:#eeeeee;padding:5px'></div>
                    					".($row_log['load_handler_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/menu_system16.png' style='cursor:pointer;float:left' onclick=\"edit_entry_truck('',$row_log[id],0)\" alt='Edit Dispatch' title='Edit Dispatch'></div>" : "")."
                    					".($row_log['truck_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/truck_info.png' style='cursor:pointer;float:left' onclick=\"view_truck_history($row_log[truck_id])\" alt='Truck History' title='Truck History".$profit_history."'></div>" : "")."
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
                    			
                    			$dt_completer.= "&nbsp;&nbsp; <a href='javascript:update_stop_arrival($row_log_sub[load_handler_stop_id])' style='color:purple;'>".
												($row_log_sub['linedate_arrival'] >="2010-01-01 00:00:00" ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_arrival'])) : "Arrival")."&nbsp;&nbsp"
										."</a>";
                    			
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
                    		and trailers_dropped.drop_completed <= 0
                    		and trailers_dropped.deleted <= 0
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
		$id_list="";
		/*
		$sql = "
			select id,
				name_company
			
			from customers
			where separate_truck_section > 0
				and deleted <= 0
				and active > 0
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
				
		
		$split_dedicated_calendar=0;
		if($split_dedicated_calendar > 0)
		{
			//show as main and dedicated companies separated.
			
			//...........................($disp_type, $id_list, $name_company,$comp_num=0,$dedicated_only=0) 
			truck_section_display_mrr_alt(0, $id_list, "",1);
			$page_timer_array[] = "After truck_section_mrr_alt - (other): " . show_page_time();
			
			//truck_section_display_mrr_alt(0, $id_list, "Dedicated Loads",2);
			mrr_generate_dedicated_sections_v2(0, $id_list, "Dedicated Loads",2);		
			$page_timer_array[] = "After truck_section_mrr_alt - Dedicated Loads: " . show_page_time();
			
			// now, display all the additional truck sections (per customers) ---------------------------------------------------------------------------
			/*
			@mysqli_data_seek($data_truck_sections,0);
			while($row_truck_sections = mysqli_fetch_array($data_truck_sections)) 
			{
				truck_section_display_mrr_alt($row_truck_sections['id'],"", $row_truck_sections['name_company']);
			}  
			*/
			//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION 2a");				//make tracking log entry
		}
		else
		{
			//show as all one board.  Al lDedicated companies will be merged within the regular/main board.
			truck_section_display_mrr_alt(-1, $id_list, "",1);
			$page_timer_array[] = "After truck_section_mrr_alt - (other): " . show_page_time();	
		}	
	}
	
	function mrr_generate_dedicated_sections_v2($disp_type, $id_list, $name_company,$comp_num=0)
	{
		$start=$comp_num;
		$cntr=0;
		
		$sqlg = "
			select id,
				name_company
			
			from customers
			where separate_truck_section > 0
				and deleted <= 0
				and active > 0
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
				
				<a href='javascript:toggle_avail_drivers()'>Toggle Drivers</a> &nbsp;&nbsp;&nbsp;&nbsp;	
				
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
								$page_timer_array[] = "After truck_section_display_sub_mrr_alt (Week $week): " . show_page_time();
								
								$all_col_headers.=$mres['column_headers'];
								$all_col_info.=$mres['column_info'];
								
								/*								
								if($show_full_calendar>0)
								{
									truck_section_display_sub_mrr_alt($disp_type, $id_list, $use_startdate, $name_company, $mrr_diff);	
								}
								elseif($mrr_diff>=0 && $mrr_diff<=7)
								{
									if()
									{
										truck_section_display_sub_mrr_alt($disp_type, $id_list, $use_startdate, $name_company, $mrr_diff);	
									}
								}
								*/
								
								/*
								// code to only show the current week
								if(($current_day >= $use_day && $current_day <= ($use_day + 7)) || $show_full_calendar>0) 
								{	//
									truck_section_display_sub_mrr_alt($disp_type, $id_list, $use_startdate, $name_company);
								}
								*/
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
		global $driver_avail_array;
		global $query_count;
		
		//added April 2012 so that Maint flags are all found in one group/set in one query instead of every point.	
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		global $peoplenet_geofencing_mph;
		
		
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
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id and trailers_dropped.customer_id='".$disp_type."' order by trailers_dropped.id desc limit 1)<=0";	
						
			if($dedicated_only > 0)		$mrr_adder=" and load_handler.dedicated_load='1' and load_handler.customer_id='".$disp_type."'";
		}	
		elseif($disp_type == 0)
		{
			$mrr_adder=" and load_handler.dedicated_load='0' and customers.separate_truck_section<=0";		//
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) > 0";
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
			where deleted <= 0
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
									trucks.geotab_device_id,
									trucks.geotab_current_location,
									trucks.geotab_last_latitude,
									trucks.geotab_last_longitude,
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
											and load_handler_stops.deleted<=0 
											and (load_handler_stops.linedate_completed IS NULL OR load_handler_stops.linedate_completed='0000-00-00 00:00:00')
											and load_handler_stops.stoplight_warning_flag=1
										limit 1
									) as mrr_stoplight_1,
									(
										select ifnull(2,0)
										from load_handler_stops 
										where load_handler_stops.trucks_log_id=trucks_log.id 
											and load_handler_stops.deleted<=0 
											and (load_handler_stops.linedate_completed IS NULL OR load_handler_stops.linedate_completed='0000-00-00 00:00:00')
											and load_handler_stops.stoplight_warning_flag=2
										limit 1
									) as mrr_stoplight_2,
									(
										select lhs.linedate_pickup_eta
										from load_handler_stops lhs 
										where lhs.trucks_log_id=trucks_log.id 
											and lhs.deleted<=0 
											and lhs.stop_type_id=2
										order by lhs.linedate_pickup_eta desc
										limit 1
									) as linedate_last_eta,
									(
										select lhs.appointment_window
										from load_handler_stops lhs 
										where lhs.trucks_log_id=trucks_log.id 
											and lhs.deleted<=0 
											and lhs.stop_type_id=2
										order by lhs.linedate_pickup_eta desc
										limit 1
									) as last_appointment_window,
									load_handler_stops.linedate_pickup_pta,
									load_handler_stops.pro_miles_dist,
									load_handler_stops.pro_miles_eta,
									load_handler_stops.pro_miles_due,
									load_handler_stops.shipper_name,
									load_handler_stops.shipper_city,
									load_handler_stops.shipper_state,
									load_handler_stops.latitude,
									load_handler_stops.longitude,
									load_handler_stops.stop_type_id,
									load_handler_stops.linedate_arrival,
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
									load_handler.preplan_marker,
									load_handler.linedate_pickup_eta as mrr_linedate_eta,
									(select max(id) as id from load_handler lh where lh.preplan >0 and lh.preplan_driver_id = trucks_log.driver_id and lh.deleted <= 0) as driver_preplanned,
									(select count(*) from trucks_log_notes where deleted <= 0 and truck_log_id = trucks_log.id) as note_count,
									(select trucks_log_notes.linedate_added from trucks_log_notes where trucks_log_notes.deleted <= 0 and truck_log_id = trucks_log.id order by trucks_log_notes.linedate_added desc limit 1) as last_note_date
								
								from trucks_log
									left join drivers on drivers.id = trucks_log.driver_id
									left join trucks on trucks.id = trucks_log.truck_id
									left join trailers on trailers.id = trucks_log.trailer_id
									left join load_handler_stops on load_handler_stops.trucks_log_id = trucks_log.id and load_handler_stops.deleted <= 0
									left join load_handler on load_handler.id = trucks_log.load_handler_id
									left join customers on customers.id=load_handler.customer_id
									
								where trucks_log.deleted <= 0
									and trucks_log.linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."' 
									and dispatch_completed <= 0															
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
									drivers.attached_truck_id,
									drivers.attached2_truck_id,
									drivers.jit_driver_flag,
									(select count(*) from notes_main where notes_main.xref_id=load_handler.id and notes_main.note_type_id='8' and notes_main.deleted=0) as notes_count,
									(select notes_main.linedate_added 
									 from notes_main 
									 where notes_main.xref_id=load_handler.id and notes_main.note_type_id='8' and notes_main.deleted=0 
									 order by notes_main.linedate_added desc 
									 limit 1) as last_note_date
								
								from load_handler
									left join customers on customers.id = load_handler.customer_id
									left join drivers on drivers.id = load_handler.preplan_driver_id
								where load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime("+$i days", $startdate))."'
									and load_handler.linedate_pickup_eta < '".date("Y-m-d 23:59:59", strtotime("+$i days", $startdate))."'
									and load_handler.deleted <= 0
									and (
										load_handler.load_available >0
										or 
										(select count(*) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id) <= 0
									)									
									and (
									    select count(*) 
									    from load_handler_stops 
									    where load_handler_stops.deleted<=0 
									        and load_handler_stops.needs_appt > 0 
									        and load_handler_stops.load_handler_id = load_handler.id
									        and load_handler_stops.trucks_log_id > 0
									) <=0									
									
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
							

							if(date("Y-m-d") == date("Y-m-d", strtotime("+$i days", $startdate))) 
							{							
								// see if we have any available drivers on this day
								$available_driver_count = 0;
								foreach($driver_avail_array as $darray) 
								{
	
									$quick_quote_link=" <span style='cursor:pointer;margin-left:5px; color:#CC00CC;' onClick='mrr_quick_quote(0,".$darray['driver_id'].",0);'>Q</span>";
									
									// get a list of pre-planed loads available for this driver
									$sql = "
										select load_handler.*,
											customers.name_company
										
										from load_handler, customers
										where load_handler.preplan > 0
											and customers.id = load_handler.customer_id
											and load_handler.deleted <= 0
											".$mrr_adder."
											and (load_handler.preplan_driver_id = '".sql_friendly($darray['driver_id'])."' or load_handler.preplan_driver2_id = '".sql_friendly($darray['driver_id'])."')
									";
									$data_preplan = simple_query($sql);
																											
									//echo "(".$darray['customer_id'].")";
									if(count($darray) > 1 && (($disp_type > 0 && $darray['customer_id'] == $disp_type) || ($disp_type == 0 && !in_array($darray['customer_id'], $cust_id_array)))) 
									{
										//if(date("Y-m-d", strtotime($darray['linedate_completed'])) == date("Y-m-d", strtotime("+$i days", $startdate))) {
										
										// get any attached trucks/trailers
										$sql = "
											select drivers.attached_truck_id,
												drivers.attached_trailer_id,
												trucks.name_truck,
												trucks.peoplenet_tracking,
												trucks.geotab_device_id,
												trucks.geotab_current_location,
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
											if($available_driver_count == 1) 
											{
												$info_columns.="<div class='load_board_section_header'>Available Drivers</div>";
											}
											
											$pn_trucker_msg="";
											if($row_attached['geotab_device_id'] !="" )
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
													$mrr_last_city="Geo: ".$row_attached['geotab_current_location']."";
													
													$mrr_last_city=str_replace("Nashville, TN and In La Vergne, TN On I-24","La Vergne, TN",$mrr_last_city);
													$mrr_last_city=str_replace("Nashville, TN and In La Vergne, TN Near I-24","La Vergne, TN",$mrr_last_city);
													$mrr_last_city=str_replace("Nashville, TN and In Smyrna, TN On I-24","Smyrna, TN",$mrr_last_city);	
												}
											}
											elseif($row_attached['peoplenet_tracking'] > 0 )
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
     											$has_load=0;
     											if(isset($darray['driver_has_load']))		$has_load=$darray['driver_has_load'];
     											if($has_load==0 && ($row_attached['attached_truck_id'] > 0 || $darray['driver_id'] > 0))
     											{	//double check if there is another load/dispatch for this truck...
     												$hasload=mrr_double_check_driver_has_load($darray['driver_id'],$row_attached['attached_truck_id'],0,0);
     												if($hasload['load_id'] > 0 || $hasload['disp_id'] > 0)		$has_load=$hasload['load_id'];
     											}
     											
     											$pn_disp_line="";
     											$geotab_line="";
     											
     											if($row_attached['peoplenet_tracking'] > 0)		$pn_disp_line="<a href=\"peoplenet_interface.php?find_load_id=0&find_truck_id=".$row_attached['attached_truck_id']."&auto_run=1\" target='_blank' title='View PeopleNet Tracking'>PN</a>";
     											if($row_attached['geotab_device_id'] !="")		$geotab_line="Geo";
     											
     											
     											
     											// title='Dedicated=".$last_load_dedicated."'
     											$info_columns.="
     											<div class='hover_for_details_drivers' style='background-color:#f9d42d;font-weight:bold;color:white' id='available_driver_holder_$darray[driver_id]'>
     												
     												<div style='float:left'>   													
     												
     													<a href=\"javascript:remove_available_driver($darray[driver_id],'".str_replace("'","", $darray['name_driver_first'].' '.$darray['name_driver_last'])."')\">[x]</a>
     													<a href=\"javascript:has_load_toggle($darray[driver_id])\">(Available)</a> 
     													
     													<span class='mrr_link_like_on' onClick='mrr_show_this_available(".$darray['driver_id'].");'><b>Show</b></span>
     													
     													<a href=\"javascript:edit_driver_notes($darray[driver_id])\">".date("n/d", strtotime($darray['linedate_completed']))."<span class='".($row_attached['jit_driver_flag'] > 0 ? "jit" : "non_jit")."'> $darray[name_driver_first] $darray[name_driver_last]</span> (".$mrr_last_city.")</a>
     													     													
     													".( $geotab_line!="" ? "".$geotab_line."" : "".$pn_disp_line."")." 
     													".$pn_trucker_msg." ".$quick_quote_link."    													 
     												</div>
     												<img src='images/driver_small.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_driver_history($darray[driver_id])\" alt='Driver History' title='Driver History'>
     												<img src='images/inventory.png' id='driver_has_load_$darray[driver_id]' style='width:16px;height:16px;float:left;margin-left:5px;".($has_load > 0 ? "" : "display:none")."' alt='Driver Has Load' title='Driver Has Load'>
     														
     												
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
                                             				
                                             				$tester=mrr_find_fed_inspection_last_completed(1,$row_attached['attached_truck_id']);
                                             				if(trim($tester)!="")		$warning_flag=$tester;
                                             				
                                             				$profit_history="";
                                                            	$last_profit=mrr_fetch_truck_profit_history($row_attached['attached_truck_id'],"",0);
                                                            	if($last_profit['profit'] !="0.00" && trim($last_profit['date'])!="")
                                                            	{
                                                            		$profit_history="...Profit $".number_format($last_profit['profit'],2)." as of ".date("m/d/Y",strtotime($last_profit['date'])).".";	
                                                            	}
                                                            	
          											$info_columns.="
          												<div style='margin-left:30px;float:left' class='attached_truck_$row_attached[attached_truck_id]' truck_name=\"$row_attached[name_truck]\" driver_name=\"".$darray['name_driver_first'].' '.$darray['name_driver_last']."\">
          													<div style='float:left'><a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'>[detach]</a></div>
          													
          													<div style='float:left;margin-left:5px;color:black'>Truck: $row_attached[name_truck]</div>
          													<img src='images/truck_info.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_truck_history($row_attached[attached_truck_id])\" alt='Truck History' title='Truck History".$profit_history."'> ".$warning_flag."
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
                                             				
                                             				$tester=mrr_find_fed_inspection_last_completed(2,$row_attached['attached_trailer_id']);
                                             				if(trim($tester)!="")		$warning_flag=$tester;
                                             				
          											$info_columns.="
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
                         										
          											$info_columns.="
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
          										
          										
          										$info_columns.="<div class='avail_driver_hours avail_driver_".$darray['driver_id']."' mrr_driver_id='".$darray['driver_id']."'></div>";
          										         										
          										
          										$info_columns.="
          												
          												<div class='mrr_link_like_on' onClick='mrr_hide_this_available(".$darray['driver_id'].");'><b>Hide Details</b></div>
          												</div>
          											<div style='clear:both'></div>
          											</div>
          										";
          									}
										}//end IF NOT DEDICATED LOAD check
									
									}
								}
							}


							$use_day = date("d", strtotime("$i day", $startdate));
							if(isset($notes_day_array[$use_day])) {
								foreach($notes_day_array[$use_day] as $note_entry) {
									$info_columns.="
										<div class='note_entry'>
											<div note_id='$note_entry[id]' onclick='edit_note_date($note_entry[id],0,0)' class='note_entry_inside'>
											".trim_string($note_entry['desc_long'], 45)."
											</div>
										</div>
									";
								}
							}
							
							$loop_count = 0;
							$lh_used_id = array();
							$preplan_count = 0;
							$regular_count = 0;
							while($row_available = mysqli_fetch_array($data_available_loads)) {
								if(!in_array($row_available['id'],$lh_used_id)) {
																		
									$preplan_marker=mrr_select_preplan_marker($row_available['id'],$row_available['preplan_marker']);
                                     
                                     $preplan_marker.="&nbsp;".mrr_select_preplan_driver_by_id($row_available['id'],0);
									
									if($row_available['preplan'] == '1') {
										// preplan
										$preplan_count++;
										if($preplan_count == 1) {
											$info_columns.="
												<div class='load_board_section_header'>Preplan Loads</div>
											";
										}
										$preplan_marker="";
									} else {
										// regular available
										$regular_count++;
										if($regular_count == 1) {
											$info_columns.="
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
											and deleted <= 0
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
									
									$attached_truck1=$row_available['attached_truck_id'];
									$attached_truck2=$row_available['attached2_truck_id'];
									
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
									
									$geotab_device_id="";
									if($attached_truck1 > 0)
									{
										$geotab_device_id=mrr_find_geotab_truck_id_by_id($attached_truck1);
										if($geotab_device_id!="")		$pn_truck_id=$attached_truck1;
									}
									elseif($attached_truck2 > 0)
									{
										$geotab_device_id=mrr_find_geotab_truck_id_by_id($attached_truck2);
										if($geotab_device_id!="")		$pn_truck_id=$attached_truck2;
									}
									else
									{
										$pn_truck_id=mrr_find_peoplenet_truck_id_by_driver($pn_driver_id);	
									}
                                     
									$mrr_gen_load_note="";
									if($row_available['preplan'] > 0 && $pn_truck_id>0 && $geotab_device_id!="")
									{
										$pn_class=mrr_geotab_link_class_by_date($row_available['id'],0);		//$load_id=0,$dispatch_id=0
																				
										$pn_preplan="<a class='".$pn_class."' href=\"manage_load.php?load_id=".$row_available['id']."\" target='_blank' title='View GeoTab Tracking'>Geo</a>";	
										
										$pn_preplan_msg="<a class='".$pn_class."' href=\"geotab_messenger.php?truck_id=".$pn_truck_id."\" target='_blank' title='View GeoTab Tracking Messages'>MSGS</a>";
										
										//$mrr_pn_user_fun="preplan_note_image";
										//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		$mrr_pn_user_fun="pn_note_image_full";	
										$mrr_pn_user_fun="preplan_note_image_full";	
										
										$pn_preplan_msg="
											<img truck_id='".$pn_truck_id."' log_id='0' load_id='".$row_available['id']."' load_eta='".$row_available['linedate_pickup_eta']."' class='".$mrr_pn_user_fun."' src='/images/note_msg.png' border='0' style='display:inline;height:16px;cursor:pointer;margin-left:5px;margin-right:5px;'>
											<div id='preplan_note_holder_".$row_available['id']."' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
											";	
									}
									elseif($row_available['preplan'] > 0 && $pn_truck_id>0)
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
                                     
                                     $pre_background_color="";    // style='background-color:#F1D592;'
                                     $use_hrs_from_now=60;               //120.00;           //Timer of Hours from being ready... MRR  Updated for Justin on 11/02//2020.
                                     //if(mrr_gmt_offset_val()=="-5")      $use_hrs_from_now=120.00; 
                                     $this_moment=strtotime("-".$use_hrs_from_now." minutes",time());
                                     $this_note_date=strtotime($row_available['last_note_date']);
                                     $this_diff_minutes = abs(round((($this_note_date - $this_moment) / 60.00),0));
                                     
                                     $is_today_load=0;
                                     if(date("Y-m-d",strtotime($row_available['linedate_pickup_eta']))==date("Y-m-d",time())."")       $is_today_load=1;
                                     
                                     if($is_today_load)
                                     {  //if today....current date
                                          if($row_available['notes_count'] == 0 || $this_diff_minutes > $use_hrs_from_now || ($this_diff_minutes - $use_hrs_from_now) > 0)
                                          {     //no notes...highlight it
                                               $pre_background_color=" style='background-color:#F1D592;'";
                                          }   
                                     }
                                     
									$mrr_gen_load_note="
										    <img pre_load_id='$row_available[id]' class='note_image2' src='images/note.png' onclick='add_note_pre($row_available[id])' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;".($row_available['notes_count'] > 0 || $row_available['special_instructions'] != '' ? "border:1px red solid;" : "")."'>
											<div id='note_holder2_$row_available[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;width:425px;background-color:#eeeeee;padding:5px'></div>
									";
									
                                    // title='Difference is ".$this_diff_minutes." minutes from being stale. ".$this_diff_minutes." > ".$use_hrs_from_now."? = ".($this_diff_minutes - $use_hrs_from_now).". Today=".$is_today_load.".'
									//Notes found = ".$row_available['notes_count'].". Is ".date("Y-m-d H:i:s",strtotime($row_available['last_note_date']))."
                                    // Note less than ".date("Y-m-d H:i:s",strtotime("-".$use_hrs_from_now." minutes",time()))." time? 
                                     
                                     
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
									
									
									$info_columns.="
										<div class='hover_for_details ".($row_available['preplan'] > 0 ? "preplan_load_entry" : "available_load_entry")."'".$pre_background_color.">
										    ".$mrr_gen_load_note."
											<span class='mrr_link_like_on' onClick='mrr_show_this_dispatch(".$row_available['id'].");'>Show</span>
											<a href='manage_load.php?load_id=$row_available[id]' log_id='$row_available[id]' target='view_load_".$row_available['id']."'>
												".($row_available['preplan'] > 0 ? "($row_available[id]) <span class='".($row_available['jit_driver_flag'] > 0 ? "jit" : "non_jit")."'>$row_available[name_driver_first] $row_available[name_driver_last]".$driver2."</span>: " : "($row_available[id]):")."  
												
											</a>											
											".$preplan_marker."
											$row_available[name_company]
											$loc_start / $loc_end ".$pn_preplan." ".$pn_preplan_msg." ".$quick_quote_link."
												
									";
									
									
									$mrr_test_hours="";
									if($row_available['preplan_driver_id'] > 0)
									{
										$mrr_test_hours="<div class='preplan_driver_hours preplan_driver_".$row_available['preplan_driver_id']."' mrr_driver_id='".$row_available['preplan_driver_id']."'></div>";	
									}
									
									$info_columns.="<div class='load_stop_details load_stop_details_$row_available[id]' log_id='$row_available[id]'>
													<div class='mrr_link_like_on' onClick='mrr_hide_this_dispatch(".$row_available['id'].");'>Hide Details</div>
													".$d_hours_display."
													".$load_and_delivery_display."
													".$stop_var."
													".$mrr_attachment_list."															
													".$mrr_test_hours."											
												</div>";
									
									$info_columns.="
										<div style='clear:both'></div>
										</div>
										
									";
								}

							}
							
							$last_truck_log_id = 0;
							$loop_count = 0;
							$dropped_trailer_count = 0;
							
							$mrr_load_block_started="";
							$mrr_load_block_waiting="";
							
							while($row_log = mysqli_fetch_array($data_log)) 
							{
								
								$my_latitude=trim($row_log['latitude']);
								$my_longitude=trim($row_log['longitude']);
								
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
     									$tnamer=trim($row_log['trailer_name']);
                    						if(trim($row_log['nick_name'])!="")	$tnamer=trim($row_log['nick_name']);
     									
     									$info_columns.="
     										<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:300px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
     											<span truck_id='$row_log[truck_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'>
     											<a style='margin-right:10px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">TL DR</a>
     											<span style='float:left;color:black;'>".trim_string($use_location,45)."</span>
     											<span style='float:right;color:black;'>".$tnamer."</span>&nbsp;
     											</span>
     											<div style='clear:both'></div>
     										</div>
     									";
								} else {
									
									$use_image = "images/note.png";
									$has_preplan = 0;									
									$has_load_flag = 0;
									$has_load_id = 0;
									$has_disp_id = 0;
																		
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
										if($has_load_flag==0 && ($row_log['truck_id'] > 0 || $row_log['driver_id'] > 0))
     									{	//double check if there is another load/dispatch for this truck...
     										$hasloadflag=mrr_double_check_driver_has_load($row_log['driver_id'],$row_log['truck_id'],$row_log['id'],0);
     										if($hasloadflag['load_id'] > 0 || $hasloadflag['disp_id'] > 0)
     										{
     											$use_image = "images/inventory.png";	
     											$has_load_flag = 1;
     											$has_load_id = $hasloadflag['load_id'];
												$has_disp_id = $hasloadflag['disp_id'];
     										}
     									}	
									}								
									
									$warning_flag1="";	//mrr_get_equipment_maint_notice_warning('truck',$row_log['truck_id']);		
									for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
                                        	{
                                        		if($mrr_maint_truck_arr[ $rr ] == $row_log['truck_id'])		$warning_flag1=trim($mrr_maint_truck_links[ $rr ]);
                                        	}
                                        	
                                        	$tester1=mrr_find_fed_inspection_last_completed(1,$row_log['truck_id']);
                             				if(trim($tester1)!="")		$warning_flag1=$tester1;
                                        	                                      	
                                        	
									$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_log['trailer_id']);
									for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                                        	{
                                        		if($mrr_maint_trailer_arr[ $rr ] == $row_log['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                                        	}
                                        	
                                        	$tester2=mrr_find_fed_inspection_last_completed(2,$row_log['trailer_id']);
                             				if(trim($tester2)!="")		$warning_flag2=$tester2;
                                        	                                        	
                                        	
                                        	if($loop_count == 1) 
                                        	{
                                        		$info_columns.="<div class='load_board_section_header'>Loads</div>";
                                        	}
                                        	
                                        	
                                        	$geotab_device_id=trim($row_log['geotab_device_id']);
									if($row_log['truck_id'] > 0 && $geotab_device_id=="")		$geotab_device_id=mrr_find_geotab_truck_id_by_id($row_log['truck_id']);
                                        	
                                        	$pn_class=mrr_geotab_link_class_by_date($row_log['load_handler_id'],$row_log['id']);		//$load_id=0,$dispatch_id=0                                       	
                                        	if($geotab_device_id=="")		$pn_class=mrr_peoplenet_link_class_by_date($row_log['truck_id'],$row_log['id'],0);
                                        	                                        	
                                        	$dist_res="";
                                        	$dist_java="";
                                        	
                                        	if($geotab_device_id!="" && trim($pn_class)!="")
                                        	{
                                        		//mrr_gen_truck_local_map_by_google($row_log['load_handler_id'],$row_log['name_truck'],$truck_lat,$truck_long);
                                        		
                                        		$dist_res="<div id='truck_local_".$row_log['load_handler_id']."' class='truck_".$row_log['truck_id']."_local'></div>";
                                        		$dist_java=" onClick='mrr_find_truck_locator_geotab(".$row_log['truck_id'].",".$row_log['load_handler_id'].");'";
                                        	}
                                        	elseif($row_log['peoplenet_tracking'] > 0 && trim($pn_class)!="")
                                        	{
                                        		//mrr_gen_truck_local_map_by_google($row_log['load_handler_id'],$row_log['name_truck'],$truck_lat,$truck_long);
                                        		
                                        		$dist_res="<div id='truck_local_".$row_log['load_handler_id']."' class='truck_".$row_log['truck_id']."_local'></div>";
                                        		$dist_java=" onClick='mrr_find_truck_locator(".$row_log['truck_id'].",".$row_log['load_handler_id'].");'";
                                        	}
                                        	
                                        	$customer_linker="<a href='admin_customers.php?eid=".$row_log['customer_id']."' target='_blank'>".$row_log['customer_namer']."</a>";
                                        	
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
                                        	
                                        	
                                        	//Added May 2013...sorting loads based on stops started...(first/a stop completed)
                                        	$load_started_already=0;
                                        	$mrr_load_block="";
                                        	                                        	
									$pn_msg_viewer="";	
									$quick_quote_link="";	
																											
									if($geotab_device_id!="")
									{	
										$mrr_pn_user_fun2="pn_note_image";
										//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		$mrr_pn_user_fun="pn_note_image_full";	
										$mrr_pn_user_fun="pn_note_image_full";	
										
										$pn_msg_viewer="
											<img truck_id='".$row_log['truck_id']."' log_id='".$row_log['id']."' load_id='".$row_log['load_handler_id']."' load_eta='".$row_log['mrr_linedate_eta']."' class='".$mrr_pn_user_fun."' src='/images/note_msg.png' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;'>
											<span style='height:16px;cursor:pointer;margin-right:5px;float:left; color:#00CC00;' truck_id='".$row_log['truck_id']."' log_id='".$row_log['id']."' load_id='".$row_log['load_handler_id']."' load_eta='".$row_log['mrr_linedate_eta']."' class='".$mrr_pn_user_fun2."'>...History</span>
										";
									}
									elseif($row_log['peoplenet_tracking'] > 0)
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
									
									if($row_log['truck_id']==0)
									{
										$pn_msg_viewer="&nbsp;&nbsp;&nbsp;&nbsp;";	
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
                              			
                              			$pn_final_dispatcher="&nbsp;&nbsp;&nbsp;&nbsp;";
                              			if($row_log['truck_id'] > 0 && $geotab_device_id!="")
                              			{
                              				$pn_final_dispatcher="<a class='".$pn_class."' href=\"add_entry_truck.php?load_id=".$row_log['load_handler_id']."&id=".$row_log['id']."&auto_runner=1\" target='_blank' title='View GeoTab Tracking'>Geo</a>";
                              			}
                              			elseif($row_log['truck_id'] > 0 && $row_log['peoplenet_tracking'] > 0)
                              			{
                              				$pn_final_dispatcher="<a class='".$pn_class."' href=\"peoplenet_interface.php?find_load_id=".$row_log['load_handler_id']."&find_truck_id=".$row_log['truck_id']."&auto_run=1\" target='_blank' title='View PeopleNet Tracking'>PN</a>";
                              			}
                              			
                              			$add_pta_details_main="";
                              			
                              			$profit_history="";
                                             $last_profit=mrr_fetch_truck_profit_history($row_log['truck_id'],"",0);
                                             if($last_profit['profit'] !="0.00" && trim($last_profit['date'])!="")
                                             {
                                             	$profit_history="...Profit $".number_format($last_profit['profit'],2)." as of ".date("m/d/Y",strtotime($last_profit['date'])).".";	
                                             }
                                             
                                             
                                             $breakdown_truck="";
                                             if(trim($warning_flag1)!="")
                                             {
                                             	$breakdown_truck=mrr_find_unit_breakdown_maint_request(58,$row_log['truck_id']);

                                             }
                                             $breakdown_trailer="";
                                             if(trim($warning_flag2)!="")
                                             {
                                             	$breakdown_trailer=mrr_find_unit_breakdown_maint_request(59,$row_log['trailer_id']);
                                             }
                                             
                                             
                                             $pn_disp_line="";
                                             $geotab_line="";
                                             if($row_log['peoplenet_tracking'] > 0)	$pn_disp_line="<a class='".$pn_class."' href=\"peoplenet_messager.php?truck_id=".$row_log['truck_id']."\" target='_blank' title='View PeopleNet Tracking Messages'>MSGS</a>";
                                             if($geotab_device_id!="")			$geotab_line="<a class='".$pn_class."' href=\"geotab_messenger.php?truck_id=".$row_log['truck_id']."\" target='_blank' title='View GeoTab Tracking Messages'>MSGS</a>";
                                             
                                             // border:1px solid #000000;
                                             //$use_background_color = ";background-color:#00cc00";
                                     //background-color:#a17d0a;
                                     
                                    $add_pta_details="";
                                    
                                    $use_hrs_from_now=60;               //120.00;           //Timer of Hours from being ready... MRR  Updated for Justin on 11/02//2020.
                                    //if(mrr_gmt_offset_val()=="-5")      $use_hrs_from_now=120.00; 
                                    $this_moment=strtotime("-".$use_hrs_from_now." minutes",time());
                                    $this_note_date=strtotime($row_log['last_note_date']);
                                    $this_diff_minutes = round((($this_note_date - $this_moment) / 60.00),0);
                                    if($row_log['note_count']==0 && $row_log['linedate']==date("Y-m-d",time())." 00:00:00" )   
                                    {
                                        $use_background_color="background-color:#F1D592;";
                                        $warning_flag1=str_replace("ffcc00","CC0000",$warning_flag1);
                                        $warning_flag2=str_replace("ffcc00","CC0000",$warning_flag2);
                                    }                                    
                                    elseif($row_log['note_count'] > 0 && $row_log['linedate']==date("Y-m-d",time())." 00:00:00" && ($this_diff_minutes > $use_hrs_from_now || $this_diff_minutes < 0))   
                                    {
                                        $use_background_color="background-color:#F1D592;";
                                        $warning_flag1=str_replace("ffcc00","CC0000",$warning_flag1);
                                        $warning_flag2=str_replace("ffcc00","CC0000",$warning_flag2);
                                    }
                                    if($row_log['note_count'] > 0 && $this_diff_minutes>=0 && $this_diff_minutes<=$use_hrs_from_now)
                                    {
                                         $use_background_color="";
                                         //$warning_flag1=str_replace("ffcc00","CC0000",$warning_flag1);
                                         //$warning_flag2=str_replace("ffcc00","CC0000",$warning_flag2);
                                    }
                                     
                                    $sp_no="/";
                                    $sp_no=mrr_spec_notice($row_log['truck_id'],$row_log['trailer_id']);
                                     
									$mrr_load_block.= "
										<div class='hover_for_details truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:480px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
											<span style='' truck_id='$row_log[truck_id]' load_handler_id='$row_log[load_handler_id]' trailer_id='$row_log[trailer_id]' class='entry_info mrr_load_".$row_log['load_handler_id']."_disp_".$row_log['id']."_styles' log_id='$row_log[id]'>
												<div style='float:left;width:20px;padding-left:5px;'>	
													".$mrr_stoplight_img."
												</div>
												<div style='float:left;width:110px;'>													
													<img log_id='$row_log[id]' class='note_image' preplan_load_id='$has_preplan' has_load_flag='$has_load_flag' has_load_id='$has_load_id' has_disp_id='$has_disp_id' src='$use_image' onclick='add_note($row_log[id])' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;".($row_log['note_count'] > 0 || $row_log['special_instructions'] != '' ? "border:1px red solid;" : "")."'>
													<div id='note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;width:425px;background-color:#eeeeee;padding:5px'></div>
													".($row_log['load_handler_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/menu_system16.png' style='cursor:pointer;float:left' onclick=\"edit_entry_truck('',$row_log[id],0)\" alt='Edit Dispatch' title='Edit Dispatch'></div>" : "")."
													".($row_log['truck_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/truck_info.png' style='cursor:pointer;float:left' onclick=\"view_truck_history($row_log[truck_id])\" alt='Truck History' title='Truck History".$profit_history."'></div>" : "")."
													".$pn_final_dispatcher." 
													".$pn_msg_viewer."
													".$quick_quote_link."
													
													<div class='mrr_link_like_on' onClick='mrr_show_this_dispatch(".$row_log['id'].");' title='Notes found = ".$row_log['note_count'].". Is ".date("Y-m-d H:i:s",strtotime($row_log['last_note_date']))." Note less than ".date("Y-m-d H:i:s",strtotime("-".$use_hrs_from_now." minutes",time()))." time? Difference is ".$this_diff_minutes." minutes from being stale.'><b>Show</b>
														
													
													  </div>
													".($row_log['last_appointment_window'] > 0 ? "<div style='display:inline; color:#00CC00;'><b>Appt Window ETA: ".date("H:i", strtotime($row_log['linedate_last_eta']))."</b></div>" : "<div style='display:inline; color:purple;'><b>Strict Appt ETA: ".date("H:i", strtotime($row_log['linedate_last_eta']))."</b></div>")."
													<div id='pn_note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
													<div id='pn_note_holder2_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
													<div id='phone_note_holder2_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
												</div>												

												<a style='margin-right:5px;float:left;display:inline-block;width:60px;overflow:hidden' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\" log_id='$row_log[id]'>($row_log[load_handler_id])</a>
												<a style='margin-right:5px;float:left;display:inline-block;width:100px;overflow:hidden' href='admin_drivers.php?id=".$row_log['driver_id']."' target='_blank'><div class='".($row_log['jit_driver_flag'] > 0 ? "jit2" : "non_jit2")." mrr_load_".$row_log['load_handler_id']."_disp_".$row_log['id']."_driver'>$row_log[name_driver_last], $row_log[name_driver_first]</div></a>
												<div style='float:left;color:black;'".$dist_java.">".$use_location."</div>
												<div style='float:right;margin-right:5px;font-weight:bold;color:black;'>
													
													".( $geotab_line!="" ? "".$geotab_line."" : "".$pn_disp_line."")." 
													<b><a href='admin_trucks.php?id=".$row_log['truck_id']."' target='_blank'>$row_log[name_truck]</a>".$warning_flag1."</b>
													 ".$sp_no." 
													 ".$warning_flag2."<a href='admin_trailers.php?id=".$row_log['trailer_id']."' target='_blank'>$row_log[trailer_name]</a>																										
												</div>
												

												<div style='clear:both'></div>
											</span>
											".$breakdown_truck."
											".$breakdown_trailer."
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
                                    $this_diff_minutes=0;
									//stops for this load...
									$counter_sub = 0;
									mysqli_data_seek($data_log,$loop_count-1);
									while($row_log_sub = mysqli_fetch_array($data_log)) 
									{										
										$my_latitude=trim($row_log_sub['latitude']);
										$my_longitude=trim($row_log_sub['longitude']);
										
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
											
											$add_pta_details="";
											if($row_log_sub['linedate_completed'] > 0) 
											{
												$add_pta_details="";
											}
											else
											{
												//pro_miles_dist,
												//pro_miles_eta,
												//pro_miles_due,
												if(!isset($row_log_sub['linedate_pickup_pta']))		$row_log_sub['linedate_pickup_pta']="0000-00-00 00:00:00";
												
												$add_pta_details="
     												<span style='color:brown;'>
     													&nbsp;&nbsp;&nbsp
     													<a href='javascript:update_stop_arrival(".$row_log_sub['load_handler_stop_id'].")' style='color:purple;'>PTA:</a> ".($row_log_sub['linedate_pickup_pta'] !="0000-00-00 00:00:00" ? "<font style='color:purple;'><b>".date("M j, H:i", strtotime($row_log_sub['linedate_pickup_pta']))."</b></font>" : "<font class='mrr_alert'><b>NOT SET</b></font>")."
     													<b>Miles: ".$row_log_sub['pro_miles_dist']."</b>
     													ETA: <b>".$row_log_sub['pro_miles_eta']." hrs</b> - Due in <b>".$row_log_sub['pro_miles_due']." hrs</b>.
     												</span>
												";
												
												if($geotab_device_id!="" && trim($row_log_sub['geotab_last_longitude'])!="" && trim($row_log_sub['geotab_last_latitude'])!="")
												{	//GeoTab is use, so uses the curetn truck location to find distance away.
													$geotab_distance=mrr_distance_between_gps_points($my_latitude,$my_longitude,trim($row_log_sub['geotab_last_latitude']),trim($row_log_sub['geotab_last_longitude']),0);
																										
													$geotab_eta=0;
													if($peoplenet_geofencing_mph > 0)	
													{
														$geotab_eta=$geotab_distance / $peoplenet_geofencing_mph;
													}	
																						
													// (Lat:".trim($row_log_sub['geotab_last_latitude']).", Long:".trim($row_log_sub['geotab_last_longitude'])." and Lat:".$my_latitude.", Long:".trim($my_longitude).")
													
													$add_pta_details="
     												<span style='color:brown;'>
     													&nbsp;&nbsp;&nbsp
     													<a href='javascript:update_stop_arrival(".$row_log_sub['load_handler_stop_id'].")' style='color:purple;'>PTA:</a> ".($row_log_sub['linedate_pickup_pta'] !="0000-00-00 00:00:00" ? "<font style='color:purple;'><b>".date("M j, H:i", strtotime($row_log_sub['linedate_pickup_pta']))."</b></font>" : "<font class='mrr_alert'><b>NOT SET</b></font>")."
     													<b>Miles: ".number_format($geotab_distance,2)." [GeoTab]</b>
     													ETA: <b>".number_format($geotab_eta,2)." hrs</b> - Due in <b>".$row_log_sub['pro_miles_due']." hrs</b>.
     												</span>
													";
												}
											}	
											
											if($add_pta_details_main=="" && $row_log_sub['linedate_completed'] == 0)
											{
												if(!isset($row_log_sub['linedate_pickup_pta']))		$row_log_sub['linedate_pickup_pta']="0000-00-00 00:00:00";

												$add_pta_details_main="
													<span style='color:#000000;'>
     													&nbsp;&nbsp;&nbsp - 
     													APPT ETA: ".date("M j, H:i", strtotime($row_log_sub['linedate_pickup_eta']))."
     													&nbsp;&nbsp;&nbsp
     													PTA: ".($row_log_sub['linedate_pickup_pta'] !="0000-00-00 00:00:00" ? "".date("M j, H:i", strtotime($row_log_sub['linedate_pickup_pta'])) : "<font class='mrr_alert'><b>NOT SET</b></font>")."
     													<b>Miles: ".$row_log_sub['pro_miles_dist']."</b>
     													&nbsp;&nbsp;&nbsp
     													ETA: <b>".$row_log_sub['pro_miles_eta']." hrs</b> - Due in <b>".$row_log_sub['pro_miles_due']." hrs</b>.
     												</span>
												";	
											}
											
											
											$add_pta_details_main="";			//kill switch for section...
																						
											//".$add_pta_details."
											$mrr_load_block.= "
													<div style='float:left".($row_log_sub['linedate_completed'] > 0 ? ";color:#888888;" : ";color:black;")."'>
													(".($row_log_sub['stop_type_id'] == 1 ? "S" : "C").") ($row_log_sub[load_handler_stop_id])
													".$appt_window_tag1."".$row_log_sub['shipper_name'].": $row_log_sub[shipper_city], $row_log_sub[shipper_state].".$appt_window_tag2."
													
													</div>
													<div style='float:right'>
											";											
											
												if($row_log_sub['linedate_completed'] > 0) 
												{												
													$mrr_load_block.= "<span style='color:#888888;color:black;'>".($row_log_sub['linedate_completed'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_completed'])) : "")."&nbsp;&nbsp;&nbsp</span>";
													$load_started_already=1;
												} 
												else 
												{
													//linedate_pickup_pta,
													
													
													$mrr_load_block.= " Trailer ".$row_log_sub['mrr_start_trailer']."";
          											if($row_log_sub['mrr_end_trailer'] != $row_log_sub['mrr_start_trailer'] && $row_log_sub['mrr_end_trailer'] > 0)
          											{
          												$mrr_load_block.= " Switch to ".$row_log_sub['mrr_end_trailer']."";	
          											}
          											elseif($row_log_sub['mrr_end_trailer'] != $row_log_sub['mrr_start_trailer'] && $row_log_sub['mrr_end_trailer'] == 0)
          											{
          												$mrr_load_block.= " Drop";	
          											}
													
													$mrr_load_block.= "&nbsp;&nbsp; <a href='javascript:update_stop_arrival($row_log_sub[load_handler_stop_id])' style='color:purple;'>".
														($row_log_sub['linedate_arrival'] >= "2010-01-01 00:00:00" ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_arrival'])) : "Arrival/PTA")."&nbsp;&nbsp"
														."</a>";
														
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
									
									
									//
									$mrr_load_block.= "
											</div>
											".$add_pta_details_main."
											".$add_pta_details."
										</div>
									";
									
									if($load_started_already > 0)
									{										
                                        		$mrr_load_block_started.=$mrr_load_block;									
									}
									else
									{										
										$mrr_load_block_waiting.=$mrr_load_block;		
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
     										trailers.nick_name,
     										trailers.trailer_owner,
     										customers.name_company
     									
     									from trailers_dropped
     										left join trailers on trailers.id = trailers_dropped.trailer_id 
     										left join customers on trailers_dropped.customer_id=customers.id
     									where trailers_dropped.drop_completed <= 0
     										and trailers_dropped.deleted <= 0
     										and trailers_dropped.trailer_id>0
     										".$mrr_add_dedicated."
     										
     										$sql_filter_drop_tl_cust
     									order by location_city asc,location_state asc,location_zip asc,name_company asc,trailer_name asc							
     								";
     								//if($_SERVER['REMOTE_ADDR'] == '173.10.208.206') d($sql);
     								$last_stater="";
     								$data_trailers_dropped = simple_query($sql);
     								$dropped_trailer_count = 0;
     								while($row_trailer_dropped = mysqli_fetch_array($data_trailers_dropped)) 
     								{     									
     									$dropped_trailer_count++;
     									// dropped trailers are always shown last. If this is the first dropped trailer, then show a general toggle for them
     									if($dropped_trailer_count == 1) 
     									{
     										$onflag=0;		
     										if(isset($_SESSION['toggle_dropped_trailer_on']))		$onflag=$_SESSION['toggle_dropped_trailer_on'];
     										
     										$info_columns.="
     											<div class='load_board_section_header' onclick='mrr_toggle_dropped_trailer_setter(".$onflag.");' style='cursor:pointer;color:#c4ffc4'>
     												Toggle Dropped Trailers
     											</div>
     										";			//toggle_dropped_trailers()
     										/*
     										if($_SESSION['toggle_dropped_trailer_on']==1)
     										{
     											$info_columns.="<script type='text/javascript'>
     													toggle_dropped_trailers();
     													</script>";	
     											$_SESSION['toggle_dropped_trailer_on']=1;
     										}
     										*/
     									}
     									
     									$tnamer=trim($row_trailer_dropped['trailer_name']);
                    						if(trim($row_trailer_dropped['nick_name'])!="")	$tnamer=trim($row_trailer_dropped['nick_name']);
     									
     									$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_trailer_dropped['trailer_id']);
     									for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                                             	{
                                             		if($mrr_maint_trailer_arr[ $rr ] == $row_trailer_dropped['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                                             	}
                                             	
                                             	if($last_stater!="$row_trailer_dropped[location_city]")
                                             	{
                                             		$last_stater="$row_trailer_dropped[location_city]";	
                                             		$info_columns.="<div>&nbsp;</div>";
                                             	}
                                             	
                                             	$is_empty="";
                                                if($row_trailer_dropped['is_empty'] > 0)
                                                {
                                                     $is_empty="<span style='color:#cc0000; display:inline-block;'><b>-EMPTY </b></span>";
                                                }
                                                
                                             	if($row_trailer_dropped['trailer_owner']=="IPCC")
                                             	{
                                             		$ipcc_list.="
          										<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style=';min-width:300px;'>
          											<span trailer_id='$row_trailer_dropped[trailer_id]' class='entry_info' dropped_trailer_id='$row_trailer_dropped[id]'>
          											<a style='margin-right:10px;float:left' href=\"javascript:edit_dropped_trailer($row_trailer_dropped[id])\">TL DR</a>
          											<span style='float:left;color:black;'>".trim_string("$row_trailer_dropped[location_city], $row_trailer_dropped[location_state] $row_trailer_dropped[location_zip]",45)."</span>
          											<span style='float:right;color:black;'>".$mrr_message."".$warning_flag2."".$tnamer."</span>&nbsp;
          											</span>
          											<div style='clear:both'></div>
          										</div>
     										";
                                             	}
                                             	else
                                             	{
													$dispatch_completed = isset($row_log['dispatch_completed']) ? $row_log['dispatch_completed'] : '';

                                             		$info_columns.="
          										<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$dispatch_completed' style=';min-width:300px;'>
          											<span trailer_id='$row_trailer_dropped[trailer_id]' class='entry_info' dropped_trailer_id='$row_trailer_dropped[id]'>
          											<a style='margin-right:10px;float:left' href=\"javascript:edit_dropped_trailer($row_trailer_dropped[id])\">TL DR</a>
          											".$is_empty."
          											<span style='float:left;color:black;'>".trim_string("$row_trailer_dropped[location_city], $row_trailer_dropped[location_state] $row_trailer_dropped[location_zip]",45)."</span>
          											<span style='float:right;color:black;'>".$mrr_message."".$warning_flag2."".$tnamer."</span>&nbsp;
          											</span>
          											<div style='clear:both'></div>
          										</div>
     										";
                                             	}    	
     									
     								}
     								
     								//$info_columns.="<br><span style='color:black;' class='dropped_trailer'><b>IPCC TRAILERS:</b></span><br><br>".$ipcc_list."";
     							}
							}
							
							$info_columns.="&nbsp;</td>";
							
							
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
			where deleted <= 0
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
     			and trucks_log.deleted<=0
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
?>