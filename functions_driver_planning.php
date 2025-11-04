<?
//functions specifically for the admin_driver_load_planning form 

function get_week_dates_for_date($dater)
{
	$sdate=$dater;
	$edate=$dater;
	
	$weekday=date("w",strtotime($dater));
	if($weekday==0)
	{
		$sdate=$dater;	
		$edate=date("m/d/Y",strtotime("+6 day",strtotime($dater)));
	}
	elseif($weekday==1)
	{
		$sdate=date("m/d/Y",strtotime("-1 day",strtotime($dater)));
		$edate=date("m/d/Y",strtotime("+5 day",strtotime($dater)));
	}
	elseif($weekday==2)
	{
		$sdate=date("m/d/Y",strtotime("-2 day",strtotime($dater)));
		$edate=date("m/d/Y",strtotime("+4 day",strtotime($dater)));
	}
	elseif($weekday==3)
	{
		$sdate=date("m/d/Y",strtotime("-3 day",strtotime($dater)));
		$edate=date("m/d/Y",strtotime("+3 day",strtotime($dater)));
	}
	elseif($weekday==4)
	{
		$sdate=date("m/d/Y",strtotime("-4 day",strtotime($dater)));
		$edate=date("m/d/Y",strtotime("+2 day",strtotime($dater)));
	}
	elseif($weekday==5)
	{
		$sdate=date("m/d/Y",strtotime("-5 day",strtotime($dater)));
		$edate=date("m/d/Y",strtotime("+1 day",strtotime($dater)));
	}
	elseif($weekday==6)
	{
		$sdate=date("m/d/Y",strtotime("-6 day",strtotime($dater)));
		$edate=$dater;
	}
		
	$res['date_from']=$sdate;
	$res['date_to']=$edate;
	return $res;	
}

function mrr_find_driver_dot_hrs_for_planning_mrr($driver_id,$date_from,$date_to)
{
	$res['sql']=""; 
	
	$res['driven_hours']=0;
	$res['rested_hours']=0;
	$res['worked_hours']=0;
	
	$res['week_driven_hours']=0;
	$res['week_rested_hours']=0;
	$res['week_worked_hours']=0;
	
	$res['violation_l0_hr']=0;
	$res['violation_ll_hr']=0;
	$res['violation_l4_hr']=0;
	$res['violation_34_hr']=0;	
	$res['violation_70_hr']=0;
	$res['violations_dot']=0;
	$res['speeding']=0;
	$res['abrupt_shutdowns']=0;
	$res['num']=0;
	
	$cntr=0;
	
     $reset_needed_11=0;
     $reset_needed_14=0;
     $reset_needed_wk=0;
                    
     $hours_driven=0;
     $hours_worked=0;
     $hours_rested=0;
     $wk_hours_driven=0;
     $wk_hours_worked=0;
     $wk_hours_rested=0;
     
     $abrupt=0;
     $speeding=0; 
     
     $tot_11_hrs=0;
     $tot_14_hrs=0;
     $tot_10_brk=0;
     $tot_70_hrs=0;
     $tot_34_brk=0;
     
     $dot_violations=0; 
     
	$sql="
		select safety_report_violations.*
		from ".mrr_find_log_database_name()."safety_report_violations
		where safety_report_violations.deleted=0
			and safety_report_violations.driver_id='".sql_friendly($driver_id)."'          			
			and safety_report_violations.linedate>='".date("Y-m-d", strtotime($date_from))." 00:00:00'
			and safety_report_violations.linedate<='".date("Y-m-d", strtotime($date_to))." 23:59:59'
		order by safety_report_violations.linedate asc
     ";
     $res['sql']=$sql;
     
        	
    	$data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
	{
		$id=$row['id'];
		$gps_dist=$row['cur_feet'];	
				   
		//Only store last hours ...this is the sum...      		      	    	
     	if($row['cur_hours_driven'] > 0)	$hours_driven=$row['cur_hours_driven'];
     	if($row['cur_hours_worked'] > 0)	$hours_worked=$row['cur_hours_worked'];     	
     	if($row['wk_hours_driven'] > 0)	$wk_hours_driven=$row['wk_hours_driven'];
     	if($row['wk_hours_worked'] > 0)	$wk_hours_worked=$row['wk_hours_worked'];
     	
     	if($hours_worked > $hours_driven)	
     	{
     		$hours_rested=168 - $hours_worked;
     	}
     	else
     	{
     		$hours_rested=168 - $hours_driven;	
     	}
     	
     	if($wk_hours_worked > $wk_hours_driven)	
     	{
     		$wk_hours_rested= 168 - $wk_hours_worked;
     	}
     	else
     	{
     		$hours_rested=168 - $wk_hours_driven;	
     	}
     	
     	     		
		if($row['abrupt_shutdown'] > 0)
		{
			$abrupt++;			
		}
		
		if($row['violation_code']==1)		
		{
			$speeding++; 		$dot_violations++; 	
		}
		
		if($reset_needed_11==0 && $row['violation_code']==2)
		{
			$tot_11_hrs++;		$dot_violations++; 		$reset_needed_11=1;
		}
		if($reset_needed_14==0 && $row['violation_code']==3)
		{
			$tot_14_hrs++;		$dot_violations++; 		$reset_needed_14=1;	
		}
		
		if($reset_needed_wk==0 && $row['violation_code']==4)
		{
			$tot_70_hrs++;		$dot_violations++; 		$reset_needed_wk=1;  
		}
		
		if(substr_count($row['violation'],"Reset Day") > 0)
		{
			$reset_needed_11=0;
			$reset_needed_14=0;
		}
		if(substr_count($row['violation'],"Reset Week") > 0)	
		{
			$reset_needed_wk=0;
		} 
		
		$cntr++; 
	}
	
	$res['driven_hours']=$hours_driven;
	$res['rested_hours']=$hours_rested;
	$res['worked_hours']=$hours_worked;
	
	$res['week_driven_hours']=$wk_hours_driven;
	$res['week_rested_hours']=$wk_hours_rested;
	$res['week_worked_hours']=$wk_hours_worked;
	
	$res['violation_l0_hr']=$tot_10_brk;
	$res['violation_ll_hr']=$tot_11_hrs;
	$res['violation_l4_hr']=$tot_14_hrs;
	$res['violation_34_hr']=$tot_34_brk;	
	$res['violation_70_hr']=$tot_70_hrs;
	$res['violations_dot']=$dot_violations;
	$res['speeding']=$speeding;
	$res['abrupt_shutdowns']=$abrupt;
	$res['num']=$cntr;
	$res['sql']=$sql;
	return $res;	
}
function mrr_driver_hours_calc($driver_id,$date_from)
{
	$offset_days=-7;	//last week
	$miles_per_hour=60;
	//$max_hours_allowed=40;
	
	$dres1=get_week_dates_for_date($date_from);
	$date_from=$dres1['date_from'];
	$date_to=$dres1['date_to'];	
	
	$approx_hours=0;
	$approx_hours2=0;
	$preplan_hours=0;
	if($driver_id > 0)
	{
		$approx_hours=get_planning_driver_approximate_hours_by_dispatch($driver_id,$date_from,$date_to,$offset_days,$miles_per_hour);
		$approx_hours2=get_planning_driver_approximate_hours_by_dispatch($driver_id,$date_from,$date_to,0,$miles_per_hour);
		$preplan_hours=get_planning_driver_approximate_hours_by_preplan($driver_id,$date_from,$date_to,0,$miles_per_hour);
	}
	$res['hours']=$approx_hours;
	$res['hours2']=$approx_hours2;
	$res['planned']=$preplan_hours;
	$res['from']=$date_from;
	$res['to']=$date_to;
	
	return $res;
}

function get_planning_driver_approximate_hours_by_preplan($driver_id,$date_from,$date_to,$days_offset,$miles_per_hour)
{
	$approx_hours=0;
	
	$sql = "
		 select load_handler.id
		 from load_handler 
		 where load_handler.preplan_driver_id='".sql_friendly($driver_id)."'
		 	and load_handler.deleted=0
		 	and load_handler.linedate_pickup_eta>='".date("Y-m-d",strtotime("".$days_offset." day",strtotime($date_from)))." 00:00:00'
		 	and load_handler.linedate_pickup_eta<='".date("Y-m-d",strtotime("".$days_offset." day",strtotime($date_to)))." 23:59:59'
		 order by load_handler.linedate_pickup_eta asc,load_handler.id asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{
		//$load_id=$row['id'];		
		$approx_hours+=(mrr_pull_preplan_pcmiles_for_load($row['id']) / $miles_per_hour);
	}	
	
	return $approx_hours;
}
function get_planning_driver_approximate_hours_by_dispatch($driver_id,$date_from,$date_to,$days_offset,$miles_per_hour)
{
	$approx_hours=0;
	
	$sql = "
		 select trucks_log.*
		 from trucks_log 
		 where (trucks_log.driver_id='".sql_friendly($driver_id)."' or trucks_log.driver2_id='".sql_friendly($driver_id)."')
		 	and trucks_log.deleted=0
		 	and trucks_log.linedate_pickup_eta>='".date("Y-m-d",strtotime("".$days_offset." day",strtotime($date_from)))." 00:00:00'
		 	and trucks_log.linedate_pickup_eta<='".date("Y-m-d",strtotime("".$days_offset." day",strtotime($date_to)))." 23:59:59'
		 order by trucks_log.linedate_pickup_eta asc,trucks_log.id asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{
		$miles=$row['miles'] + $row['miles_deadhead'];// + $row['loaded_miles_hourly'];
		$hours=$row['hours_worked'];
		
		if($miles_per_hour>0)	$log_tot=$hours + $miles/$miles_per_hour;	else  $log_tot=$hours + $miles/$miles_per_hour;	
		
		$approx_hours+=$log_tot;
	}	
	
	return $approx_hours;
}

function get_planning_driver_pool()
{
	$arr[0]=0;
	$label[0]="";
	$phone[0]="";
	$truck[0]=0;
	$tname[0]="";
	$trail[0]=0;
	$tname2[0]="";
	$cntr=0;
	
	$sql = "
		 select drivers.*,
		 	(select name_truck from trucks where trucks.id=drivers.attached_truck_id) as truck_name,
		 	(select trailer_name from trailers where trailers.id=drivers.attached_trailer_id) as trail_name
		 from drivers 
		 where drivers.deleted=0
		 	and drivers.active>0
		 order by drivers.name_driver_last asc,drivers.name_driver_first asc,drivers.id asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{		
		$arr[$cntr]=$row['id'];
		$label[$cntr]=trim($row['name_driver_first']." ".$row['name_driver_last']);
		$phone[$cntr]=trim($row['phone_cell']);
		
		$truck[$cntr]=$row['attached_truck_id'];
		$tname[$cntr]=trim($row['truck_name']);
		
		$trail[$cntr]=$row['attached_trailer_id'];
		$tname2[$cntr]=trim($row['trail_name']);
		
		$cntr++;	
	}	
	
	$res['num']=$cntr;
	$res['arr']=$arr;
	$res['lab']=$label;
	$res['phone']=$phone;	
	$res['trucks']=$truck;
	$res['truck_names']=$tname;
	$res['trailers']=$trail;
	$res['trailer_names']=$tname2;
	return $res;
}

function mrr_get_driver_name_and_phone($id)
{
	$driver="";
	$phone="";
	$sql="
		select name_driver_first,
			name_driver_last,
			phone_cell
		from drivers 
		where id='".sql_friendly($id)."'
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data)) {
		$driver=$row['name_driver_first']." ".$row['name_driver_last'];	
		$phone=trim($row['phone_cell']);
	}
	$res['driver_name']=$driver;
	$res['driver_phone']=$phone;	
	return $res;		
}

function mrr_get_driver_pay_rates_by_id($id)
{
	$per_mile="0.00";
	$per_hour="0.00";
	$per_mile_team="0.00";
	$per_hour_team="0.00";
	
	$per_mile2="0.00";
	$per_hour2="0.00";
	$per_mile_team2="0.00";
	$per_hour_team2="0.00";
	
	$sql="
		select *
		from drivers 
		where id='".sql_friendly($id)."'
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data)) 
	{
		$per_mile=$row['pay_per_mile'];
		$per_hour=$row['pay_per_hour'];
		$per_mile_team=$row['pay_per_mile_team'];
		$per_hour_team=$row['pay_per_hour_team'];
		
		$per_mile2=$row['charged_per_mile'];
		$per_hour2=$row['charged_per_hour'];
		$per_mile_team2=$row['charged_per_mile_team'];
		$per_hour_team2=$row['charged_per_hour_team'];
	}
	$res['miles']=$per_mile;
	$res['hours']=$per_hour;
	$res['miles_team']=$per_mile_team;
	$res['hours_team']=$per_hour_team;
	
	$res['miles2']=$per_mile2;
	$res['hours2']=$per_hour2;
	$res['miles_team2']=$per_mile_team2;
	$res['hours_team2']=$per_hour_team2;
	
	return $res;		
}
	
function get_planning_loads_for_this_date($wkday,$dater,$miles_per_hour)
{
	$load_list="";
	$cntr=0;
	$today=date("Ymd");
	
	$sql = "
		 select load_handler.*,
		 	(select name_company from customers where customers.id=load_handler.customer_id) as cust_name
		 from load_handler 
		 where load_handler.deleted=0
		 	and load_handler.linedate_pickup_eta>='".date("Y-m-d",strtotime($dater))." 00:00:00'
		 	and load_handler.linedate_pickup_eta<='".date("Y-m-d",strtotime($dater))." 23:59:59'
		 order by load_handler.linedate_pickup_eta asc,load_handler.id asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{
		$id=$row['id'];
		$pickup=date("H:i",strtotime($row['linedate_pickup_eta']));	//m/d/Y 
		$from=trim($row['origin_city'].", ".$row['origin_state']);
		$to=trim($row['dest_city'].", ".$row['dest_state']);
		$cust_id=$row['customer_id'];
		$cust_name=$row['cust_name'];
		$lading=$row['load_number'];
		$preplan=$row['preplan'];
		$preplan_driver=$row['preplan_driver_id'];
		$preplan_driver2=$row['preplan_driver2_id'];
		$preplan_driver3=$row['preplan_leg2_driver_id'];
		$preplan_driver4=$row['preplan_leg2_driver2_id'];
		$preplan_leg2_stop=$row['preplan_leg2_stop_id'];
		$available=$row['load_available'];
		$dedicated=$row['dedicated'];
		
		$notary="";
		if($available>0)	$notary.=" <span style='color:green;'><b>Avail</b></span> ";
		if($preplan>0)		$notary.=" <span style='color:orange;'><b>Preplan</b></span> ";
		if($dedicated>0)	$notary.=" <span style='color:purple;'><b>Dedicated</b></span> ";
				
		$planned="";
		
		if($preplan_driver > 0)	
		{
			$dres=mrr_get_driver_name_and_phone($preplan_driver);	
			
			$planned.="
				<span class='mrr_link_like_on' onClick='mrr_preplan_cancel(".$id.",1);' style='color:red;'><b>X</b></span> 
				<b><span id='preplan_driver_".$id."'>".$dres['driver_name']."</span></b> 
				<span id='preplan_phone_".$id."'>".$dres['driver_phone']."</span>";
		}
		else
		{
			$planned.="
			<span class='mrr_link_like_on' onClick='mrr_preplan_cancel(".$id.",1);' style='color:red;'><b>X</b></span>
			<b><span id='preplan_driver_".$id."'></span></b> 
			<span id='preplan_phone_".$id."'></span>";
		}
		
		if($preplan_driver2 > 0)	
		{
			$dres=mrr_get_driver_name_and_phone($preplan_driver2);	
			
			$planned.="<br>
				<span class='mrr_link_like_on' onClick='mrr_preplan_cancel(".$id.",2);' style='color:red;'><b>X</b></span> 
				<b><span id='preplan_driver2_".$id."'>".$dres['driver_name']."</span></b> 
				<span id='preplan_phone2_".$id."'>".$dres['driver_phone']."</span>";
		}
		else
		{
			$planned.="<br>
			<span class='mrr_link_like_on' onClick='mrr_preplan_cancel(".$id.",2);' style='color:red;'><b>X</b></span>
			<b><span id='preplan_driver2_".$id."'></span></b> 
			<span id='preplan_phone2_".$id."'></span>";
		}
		
		if($preplan_leg2_stop > 0)	
		{
			
			$planned.="<br><span>Leg 2 Driver(s)</span>";
		}
		
		
		if($preplan_driver3 > 0)	
		{
			$dres=mrr_get_driver_name_and_phone($preplan_driver3);	
			
			$planned.="<br>
				<span class='mrr_link_like_on' onClick='mrr_preplan_cancel(".$id.",3);' style='color:red;'><b>X</b></span> 
				<b><span id='preplan_driver3_".$id."'>".$dres['driver_name']."</span></b> 
				<span id='preplan_phone3_".$id."'>".$dres['driver_phone']."</span>";
		}
		else
		{
			$planned.="<br>
			<span class='mrr_link_like_on' onClick='mrr_preplan_cancel(".$id.",3);' style='color:red;'><b>X</b></span>
			<b><span id='preplan_driver3_".$id."'></span></b> 
			<span id='preplan_phone3_".$id."'></span>";
		}
		if($preplan_driver4 > 0)	
		{
			$dres=mrr_get_driver_name_and_phone($preplan_driver4);	
			
			$planned.="<br>
				<span class='mrr_link_like_on' onClick='mrr_preplan_cancel(".$id.",4);' style='color:red;'><b>X</b></span> 
				<b><span id='preplan_driver4_".$id."'>".$dres['driver_name']."</span></b> 
				<span id='preplan_phone4_".$id."'>".$dres['driver_phone']."</span>";
		}
		else
		{
			$planned.="<br>
			<span class='mrr_link_like_on' onClick='mrr_preplan_cancel(".$id.",4);' style='color:red;'><b>X</b></span>
			<b><span id='preplan_driver4_".$id."'></span></b> 
			<span id='preplan_phone4_".$id."'></span>";
		}
		
		
		$load_link="<span class='mrr_link_like_on' title='Load ".$id."' onClick='mrr_load_click(".$id.");'>Load ".$id."</span>";    
		
		$mile_val=(mrr_pull_preplan_pcmiles_for_load($id) / $miles_per_hour);	
			
		$mrr_classy="";
		$dlist=mrr_pull_dispatch_drivers_from_load($id);	
		if(trim($dlist)!="")
		{
			$planned=trim($dlist);	
			$mrr_classy=" planning_driver_dispatched";
			$load_link="<b>Load ".$id."</b>";
		}
		if(date("Ymd",strtotime($row['linedate_pickup_eta'])) < $today)
		{
			$mrr_classy=" planning_driver_past";	
		}
		
		$load_list.="
			<div id='mrr_load_id_".$id."' class='planning_driver_load".$mrr_classy."'>
				<span style='float:right;'>".$lading."</span>".$load_link.": ".$pickup." <br><a href='manage_load.php?load_id=".$id."' target='_blank'>View</a><br>
				".$cust_name."<br>
				<div class='mrr_extra_info'>".$from."</div>
				<div class='mrr_extra_info'>".$to."</div>
				".$planned."<br>
				<div class='mrr_extra_info'>".number_format($mile_val,2)."-Hr Trip. ".trim($notary)."</div>			
			</div>
		";
		$cntr++;
	}	
	$load_list.="";
	
	$res['num']=$cntr;
	$res['loads']=trim($load_list);
	return $res;
}
function mrr_pull_preplan_pcmiles_for_load($load_id)
{
	$pcm_miles=0;
	$sql = "	
		select pcm_miles
		from load_handler_stops 
		where deleted=0 
			and load_handler_id='".sql_friendly($load_id)."' 
		order by linedate_pickup_eta asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{
		$pcm_miles+=$row['pcm_miles'];
	}
	return $pcm_miles;
}
function mrr_pull_dispatch_drivers_from_load($load_id)
{
	$driver_list="";
	$sql = "
		 select trucks_log.*,
		 	(select phone_cell from drivers where drivers.id=trucks_log.driver_id) as driver_phone,
		 	(select name_driver_first from drivers where drivers.id=trucks_log.driver_id) as first_name,
		 	(select name_driver_last from drivers where drivers.id=trucks_log.driver_id) as last_name,
		 	(select phone_cell from drivers where drivers.id=trucks_log.driver2_id) as driver_phone2,
		 	(select name_driver_first from drivers where drivers.id=trucks_log.driver2_id) as first_name2,
		 	(select name_driver_last from drivers where drivers.id=trucks_log.driver2_id) as last_name2
		 from trucks_log 
		 where trucks_log.deleted=0
		 	and trucks_log.load_handler_id='".sql_friendly($load_id)."'
		 order by trucks_log.linedate_pickup_eta asc,trucks_log.id asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{
		$driver_list.="
			<div class='mrr_extra_info'><b>Dispatch ".$row['id']."</b>:</div> 
			<b>".trim($row['first_name']." ".$row['last_name'])."</b> <span class='mrr_extra_info'>(".trim($row['driver_phone']).")</span>";
		if($row['driver2_id'] > 0)
		{
			$driver_list.="<br><b>".trim($row['first_name2']." ".$row['last_name2'])."</b> <span class='mrr_extra_info'>(".trim($row['driver_phone2']).")</span>";	
		}
	}
	return $driver_list;
}

function mrr_get_driver_miles_per_period($driver_id,$date_from="",$date_to="")
{
	if($date_from=="")		$date_from=date("Y-01-01 00:00:00",time());
	if($date_to=="")		$date_to=date("Y-12-31 23:59:59",time());
	
	$res['html']="";
	$res['miles']=0;
	$res['miles_deadhead']=0;
	$res['hours']=0;
	$res['pay']=0;
	$res['hired']="";
	
	$res['charged_per_hour']=0;
	$res['charged_per_mile']=0;
	$res['pay_per_hour']=0;
	$res['pay_per_mile']=0;
	
	$res['overtime_hourly_charged']=0;
	$res['overtime_hourly_paid']=0;
	
	$res['charged_per_hour_team']=0;
	$res['charged_per_mile_team']=0;
	$res['pay_per_hour_team']=0;
	$res['pay_per_mile_team']=0;
	
	$sql="
		select *
		from drivers 
		where id='".sql_friendly($driver_id)."'
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data)) 
	{
		$res['hired']=date("m/d/Y",strtotime($row['linedate_started']));	
		
		$res['charged_per_hour']=$row['charged_per_hour'];
		$res['charged_per_mile']=$row['charged_per_mile'];
		$res['pay_per_hour']=$row['pay_per_hour'];
		$res['pay_per_mile']=$row['pay_per_mile'];
		
		$res['overtime_hourly_charged']=$row['overtime_hourly_charged'];
		$res['overtime_hourly_paid']=$row['overtime_hourly_paid'];
		
		$res['charged_per_hour_team']=$row['charged_per_hour_team'];
		$res['charged_per_mile_team']=$row['charged_per_mile_team'];
		$res['pay_per_hour_team']=$row['pay_per_hour_team'];
		$res['pay_per_mile_team']=$row['pay_per_mile_team'];
	}	
	
	$res['avg_miles']=0;
	$res['avg_miles_deadhead']=0;
	$res['avg_hours']=0;
	$res['avg_pay']=0;
	
	$res['cntr']=0;
	
	$html="";
	$cntr=0;	
	
	$days=mrr_compute_days_diff_by_dates($date_from,$date_to);
	$days++;											//+1 is to allow the end date to be counted in the difference..  20140227 - 20140201 = 26, but the first does count... making it 27 for the range.
	
	$res['days']=$days;
	$res['avg_days_miles']=0;
	$res['avg_days_miles_deadhead']=0;
	$res['avg_days_hours']=0;
	$res['avg_days_pay']=0;
	
	//return $res;
	
	if($driver_id > 0)
	{
		$miles=0;
		$dh_miles=0;
		$hours=0;
		$pay=0;
		
		$html.="
		<table width='1500' cellpadding='0' cellspacing='0' border='0'>
			<tr>
				<td valign='top'>No.</td>
				<td valign='top'>DispID</td>
				<td valign='top'>PickupETA</td>
				<td valign='top' align='right'><b>Driver</b></td>
				<td valign='top' align='right'>Labor/Mile</td>
				<td valign='top' align='right'>Labor/Hour</td>
				<td valign='top' align='right'><b>Driver2</b></td>
				<td valign='top' align='right'>Labor/Mile</td>
				<td valign='top' align='right'>Labor/Hour</td>
				<td valign='top' align='right'>Hours</td>
				<td valign='top' align='right'>Miles</td>
				<td valign='top' align='right'>DHMiles</td>
				<td valign='top' align='right'>Pay</td>
				<td valign='top' align='right'>MTot</td>
				<td valign='top' align='right'>DHTot</td>
				<td valign='top' align='right'>HTot</td>
				<td valign='top' align='right'>PTot</td>
			</tr>
		";
		
		$sql = "
     		 select trucks_log.*
     		 from trucks_log 
     		 where trucks_log.deleted=0
     		 	and trucks_log.linedate_pickup_eta >='".date("Y-m-d",strtotime($date_from))." 00:00:00'
     		 	and trucks_log.linedate_pickup_eta <='".date("Y-m-d",strtotime($date_to))." 23:59:59'
     		 	and (trucks_log.driver_id='".sql_friendly($driver_id)."' or trucks_log.driver2_id='".sql_friendly($driver_id)."')
     		 	and trucks_log.dispatch_completed > 0
     		 order by trucks_log.linedate_pickup_eta asc,trucks_log.id asc
		";  	
		$data=simple_query($sql); 	
		
		while($row=mysqli_fetch_array($data))
		{
			$pay_rate_miles=$row['labor_per_mile'];
			$pay_rate_hour=$row['labor_per_hour'];
			$pay_line=0;
			
			if($row['driver2_id'] > 0)
			{
				if($driver_id==$row['driver2_id'])
				{
					$pay_rate_miles=$row['driver_2_labor_per_mile'];
					$pay_rate_hour=$row['driver_2_labor_per_hour'];
				}
				else
				{
					$pay_rate_miles=($row['labor_per_mile'] - $row['driver_2_labor_per_mile']);
					$pay_rate_hour=($row['labor_per_hour'] - $row['driver_2_labor_per_hour']);	
				}
			}
			
			if(($row['miles'] + $row['miles_deadhead']) > 0)
			{
				$miles+=$row['miles'];
				$dh_miles+=$row['miles_deadhead'];
				$pay_line=(($row['miles'] + $row['miles_deadhead']) * $pay_rate_miles);	
			}
			if($row['hours_worked'] > 0)
			{
				$hours+=$row['hours_worked'];	
				$pay_line+=($row['hours_worked'] * $pay_rate_hour);
			}
				
			
			$pay+=$pay_line;		
			
			$html.="
			<tr class='".($cntr % 2 == 0 ? "even" : "odd") ."'>
				<td valign='top'>".($cntr + 1)."</td>
				<td valign='top'>".$row['id']."</td>
				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
				<td valign='top' align='right'><b>".$row['driver_id']."</b></td>
				<td valign='top' align='right'>".$row['labor_per_mile']."</td>
				<td valign='top' align='right'>".$row['labor_per_hour']."</td>
				<td valign='top' align='right'><b>".$row['driver2_id']."</b></td>
				<td valign='top' align='right'>".$row['driver_2_labor_per_mile']."</td>
				<td valign='top' align='right'>".$row['driver_2_labor_per_hour']."</td>
				<td valign='top' align='right'>".$row['hours_worked']."</td>
				<td valign='top' align='right'>".$row['miles']."</td>
				<td valign='top' align='right'>".$row['miles_deadhead']."</td>
				<td valign='top' align='right'>$".number_format($pay_line,2)."</td>
				<td valign='top' align='right'>".number_format($miles,2)."</td>
				<td valign='top' align='right'>".number_format($dh_miles,2)."</td>
				<td valign='top' align='right'>".number_format($hours,2)."</td>
				<td valign='top' align='right'>$".number_format($pay,2)."</td>
			</tr>
			";
			
			$cntr++;
		}
		
		$html.="</table>";
		
		
		
		$res['html']=$html;
		$res['miles']=$miles;
		$res['miles_deadhead']=$dh_miles;
		$res['hours']=$hours;
		$res['pay']=$pay;
		$res['cntr']=$cntr;
		
		if($cntr > 0)
		{
			$res['avg_miles']=($miles / $cntr);
			$res['avg_miles_deadhead']=($dh_miles / $cntr);
			$res['avg_hours']=($hours / $cntr);
			$res['avg_pay']=($pay / $cntr);
		}
		
		if($days > 0)
		{
			$res['avg_days_miles']=($miles / $days)*(7/5);
			$res['avg_days_miles_deadhead']=($dh_miles / $days)*(7/5);
			$res['avg_days_hours']=($hours / $days)*(7/5);
			$res['avg_days_pay']=($pay / $days)*(7/5);
		}
	}
	return $res;
	
}

function mrr_get_driver_miles_per_period_preplan($driver_id,$date_from="",$date_to="")
{
	if($date_from=="")		$date_from=date("Y-01-01 00:00:00",time());
	if($date_to=="")		$date_to=date("Y-12-31 23:59:59",time());
	
	$res['html']="";
	$res['miles']=0;
	$res['miles_deadhead']=0;
	$res['hours']=0;
	$res['pay']=0;
	$res['hired']="";
	
	$sql="
		select linedate_started
		from drivers 
		where id='".sql_friendly($driver_id)."'
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data)) 
	{
		$res['hired']=date("m/d/Y",strtotime($row['linedate_started']));	
	}	
	
	$res['avg_miles']=0;
	$res['avg_miles_deadhead']=0;
	$res['avg_hours']=0;
	$res['avg_pay']=0;
	
	$res['cntr']=0;
	
	$html="";
	$cntr=0;	
	
	$days=mrr_compute_days_diff_by_dates($date_from,$date_to);
	$days++;											//+1 is to allow the end date to be counted in the difference..  20140227 - 20140201 = 26, but the first does count... making it 27 for the range.
	
	$res['days']=$days;
	$res['avg_days_miles']=0;
	$res['avg_days_miles_deadhead']=0;
	$res['avg_days_hours']=0;
	$res['avg_days_pay']=0;
	
	//return $res;
	
	if($driver_id > 0)
	{
		$miles=0;
		$dh_miles=0;
		$hours=0;
		$pay=0;
		
		$html.="
		<table width='1500' cellpadding='0' cellspacing='0' border='0'>
			<tr>
				<td valign='top'>No.</td>
				<td valign='top'>LoadID</td>
				<td valign='top'>PickupETA</td>
				<td valign='top' align='right'><b>Driver</b></td>
				<td valign='top' align='right'>Labor/Mile</td>
				<td valign='top' align='right'>Labor/Hour</td>
				<td valign='top' align='right'><b>Driver2</b></td>
				<td valign='top' align='right'>Labor/Mile</td>
				<td valign='top' align='right'>Labor/Hour</td>
				<td valign='top' align='right'>Hours</td>
				<td valign='top' align='right'>Miles</td>
				<td valign='top' align='right'>Pay</td>
				<td valign='top' align='right'>MTot</td>
				<td valign='top' align='right'>HTot</td>
				<td valign='top' align='right'>PTot</td>
			</tr>
		";
		
		$sql = "
     		 select load_handler.*,
     		 	(select sum(pcm_miles) from load_handler_stops where load_handler_stops.load_handler_id=load_handler.id and load_handler_stops.deleted=0) as pcm_miles 
     		 from load_handler 
     		 where load_handler.deleted=0
     		 	and load_handler.linedate_pickup_eta >='".date("Y-m-d",strtotime($date_from))." 00:00:00'
     		 	and load_handler.linedate_pickup_eta <='".date("Y-m-d",strtotime($date_to))." 23:59:59'
     		 	and (load_handler.preplan_driver_id='".sql_friendly($driver_id)."' 
     		 		or load_handler.preplan_driver2_id='".sql_friendly($driver_id)."' 
     		 		or load_handler.preplan_leg2_driver_id='".sql_friendly($driver_id)."' 
     		 		or load_handler.preplan_leg2_driver2_id='".sql_friendly($driver_id)."')
     		 	and load_handler.preplan > 0
     		 order by load_handler.linedate_pickup_eta asc,load_handler.id asc
		";  	
		$data=simple_query($sql); 	
		
		while($row=mysqli_fetch_array($data))
		{
			$pay_rate_miles=$row['budget_labor_per_mile'];
			$pay_rate_hour=$row['budget_labor_per_hour'];
			$pay_line=0;
			
			if($row['preplan_driver2_id'] > 0)
			{
				$pay_rate_miles=$row['budget_labor_per_mile_team'] / 2;				
			}
			
			if($row['pcm_miles'] > 0)
			{
				$miles+=$row['pcm_miles'];
				$pay_line=($row['pcm_miles'] * $pay_rate_miles);
			}
			if($row['hours_worked'] > 0)
			{
				$hours+=$row['hours_worked'];	
				$pay_line+=($row['hours_worked'] * $pay_rate_hour);
			}				
			
			$pay+=$pay_line;		
			
			$html.="
			<tr class='".($cntr % 2 == 0 ? "even" : "odd") ."'>
				<td valign='top'>".($cntr + 1)."</td>
				<td valign='top'>".$row['id']."</td>
				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
				<td valign='top' align='right'><b>".$row['preplan_driver_id']."</b></td>
				<td valign='top' align='right'>".$pay_rate_miles."</td>
				<td valign='top' align='right'>".$pay_rate_hour."</td>
				<td valign='top' align='right'><b>".$row['preplan_driver2_id']."</b></td>
				<td valign='top' align='right'>".$pay_rate_miles."</td>
				<td valign='top' align='right'>".$pay_rate_hour."</td>
				<td valign='top' align='right'>".$row['hours_worked']."</td>
				<td valign='top' align='right'>".$row['pcm_miles']."</td>
				<td valign='top' align='right'>$".number_format($pay_line,2)."</td>
				<td valign='top' align='right'>".number_format($miles,2)."</td>
				<td valign='top' align='right'>".number_format($hours,2)."</td>
				<td valign='top' align='right'>$".number_format($pay,2)."</td>
			</tr>
			";
			
			$cntr++;
		}		
		$html.="</table>";		
		
		$res['html']=$html;
		$res['miles']=$miles;
		$res['miles_deadhead']=0;
		$res['hours']=$hours;
		$res['pay']=$pay;
		$res['cntr']=$cntr;
		
		if($cntr > 0)
		{
			$res['avg_miles']=($miles / $cntr);
			$res['avg_miles_deadhead']=0;
			$res['avg_hours']=($hours / $cntr);
			$res['avg_pay']=($pay / $cntr);
		}
		
		if($days > 0)
		{
			$res['avg_days_miles']=($miles / $days);
			$res['avg_days_miles_deadhead']=0;
			$res['avg_days_hours']=($hours / $days);
			$res['avg_days_pay']=($pay / $days);
		}
	}
	return $res;	
}

function mrr_find_driver_dot_hrs_for_planning_mrr_dispatches($driver_id,$date_from,$date_to)
{	//try to compute the hours the driver is driving, "working", and resting only by using the basic dispatch time.
	$cntr=0;
	$cur_work=0;
	$cur_drive=0;
	
	$res['sql']=""; 
	
	$res['week_driven_hours']=0;
	$res['week_rested_hours']=0;
	$res['week_worked_hours']=0;
	
	$res['violation_l0_hr']=0;
	$res['violation_ll_hr']=0;
	$res['violation_l4_hr']=0;
	$res['violation_34_hr']=0;	
	$res['violation_70_hr']=0;
	$res['violations_dot']=0;
	
	$max_hours=604800;		//168;		Max is 7 days time 24 hours=168 hours (times 3600 seconds per hour).
	$worked_given=1800;		//0.5 hr credit for working (inspection time pre and post).   
	
	
	//$pnres=mrr_find_driver_dot_hrs_for_planning_mrr($driver_id,$date_from,$date_to);
	  
	$sql="
		select TIME_TO_SEC(TIMEDIFF(trucks_log.linedate_dropoff_eta,trucks_log.linedate_pickup_eta)) as mrr_time
		from trucks_log
		where trucks_log.deleted=0
			and trucks_log.driver_id='".sql_friendly($driver_id)."'          			
			and trucks_log.linedate_pickup_eta>='".date("Y-m-d", strtotime($date_from))." 00:00:00'
			and trucks_log.linedate_pickup_eta<='".date("Y-m-d", strtotime($date_to))." 23:59:59'
		order by trucks_log.linedate_pickup_eta asc
     ";
     $res['sql']=$sql;
     
        	
    	$data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
	{
		$secs=$row['mrr_time'];
		
		$cur_drive+=$secs;
		$cur_work+=$worked_given;
		
		$cntr++; 
	}
	
	$cur_work+=$cur_drive;
	
	$rested=$max_hours - $cur_work;
		
	$wk_max=252000;		//70 hours work week max
	$day_max=50400;		//14 hours work day max
	$drive_max=39600;		//11 hours drive day max
	
	$wk_rest=122400;		//34 hours rest per week
	$day_rest=36000;		//10 hours per day rest
	
	$vio_10=0;
	$vio_11=0;
	$vio_14=0;
	$vio_34=0;
	$vio_70=0;
	$vios=0;
	
	if($rested < $wk_rest)			{	$vio_10=1;	$vios++;	}
	if($rested < ($day_rest * 7))		{	$vio_34=1;	$vios++;	}
	
	if($cur_work  > $wk_max)			{	$vio_70=1;	$vios++;	}
	if($cur_work > ($day_max * 7))	{	$vio_14=1;	$vios++;	}
	if($cur_drive > ($drive_max * 7))	{	$vio_11=1;	$vios++;	}
	
	$res['week_driven_hours']=round(($cur_drive / (60*60)),2);
	$res['week_rested_hours']=round(($rested / (60*60)),2);
	$res['week_worked_hours']=round(($cur_work / (60*60)),2);
	
	$res['violation_l0_hr']=$vio_10;
	$res['violation_ll_hr']=$vio_11;
	$res['violation_l4_hr']=$vio_14;
	$res['violation_34_hr']=$vio_34;	
	$res['violation_70_hr']=$vio_70;
	$res['violations_dot']=$vios;
	
	$res['num']=$cntr;
	$res['sql']=$sql;
	return $res;	
}




function mrr_driver_absense_calendar($curmon,$curday,$curyear,$mon,$day,$year,$driver_id=0,$user_id=0)
	{
		global $new_style_path;
		global $defaultsarray;
		$res="";
		
		if($driver_id==0)
		{		
			if(!isset($_GET['id']))		$_GET['id']=0;
			$driver_id=$_GET['id'];
		}	
		
		if($user_id==0)
		{		
			if(!isset($_GET['eid']))		$_GET['eid']=0;
			$user_id=$_GET['eid'];
		}
		
				
		$dater="".$mon."/".$day."/".$year."";		
		$startdate=date("Y-m-d",strtotime($dater));
		$fullm=date("F",strtotime($startdate));
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
			$codes="&nbsp;";
			
			$thisdate=date("Ymd", strtotime("".$mon."/".$cntr."/".$year.""));
			
			$sql="
				select driver_absenses.*,
					drivers.name_driver_last,
					drivers.name_driver_first,
     				option_values.fname,
     				option_values.fvalue     				
     			from driver_absenses
     				left join option_values on option_values.id=driver_absenses.driver_code
     				left join drivers on drivers.id=driver_absenses.driver_id
     			where driver_absenses.deleted=0
     				and driver_absenses.driver_id = '".sql_friendly($driver_id)."' 
     				and driver_absenses.user_id = '".sql_friendly($user_id)."'     			 
					and driver_absenses.linedate >= '".date("Y-m-d", strtotime($thisdate))." 00:00:00' and driver_absenses.linedate <= '".date("Y-m-d", strtotime($thisdate))." 23:59:59'     			
     			order by driver_absenses.linedate desc
			";
			$data=simple_query($sql);
               while($row=mysqli_fetch_array($data))
          	{
          		$codes.="".$row['fvalue']." ";
          	}
			
			$txt="<span class='mrr_link_like_on'>".$cntr."<span class='absent_code'>".trim($codes)."</span></span>";
			if( $thisdate > date("Ymd"))
			{	//greater than now...do not show this as an active "link"...		
				$txt="<span class='mrr_link_like_off'>".$cntr."<span class='absent_code'>".trim($codes)."</span></span>";
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
		
				
		$res.="<div id='driver_calender'>";
		$res.=	"<div class='driver_head'>";
		//$res.=		"<a href='#' class='plus'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=		"<div class='driver_date_change'>";
		if($driver_id > 0)
		{	//for drivers
			$res.=			"<a href='admin_drivers.php?id=".$driver_id."&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$day."&cal_mon=".$last_month."&cal_year=".$last_year."'><img src='".$new_style_path."left_changer.png' alt=''/></a>";
			$res.=			"<span>".$fullm." ".$year."</span>";
			$res.=			"<a href='admin_drivers.php?id=".$driver_id."&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$day."&cal_mon=".$next_month."&cal_year=".$next_year."'><img src='".$new_style_path."right_changer.png' alt=''/></a>";
		}
		
		if($user_id>0)
		{	//for staff/users
			$res.=			"<a href='admin_users.php?eid=".$user_id."&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$day."&cal_mon=".$last_month."&cal_year=".$last_year."'><img src='".$new_style_path."left_changer.png' alt=''/></a>";
			$res.=			"<span>".$fullm." ".$year."</span>";
			$res.=			"<a href='admin_users.php?eid=".$user_id."&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$day."&cal_mon=".$next_month."&cal_year=".$next_year."'><img src='".$new_style_path."right_changer.png' alt=''/></a>";
		}
		
		$res.=		"</div>";
		$res.=	"</div>";
		$res.=	"<div class='driver_table_sec'>";
		$res.=		"<table width='100%' cellpadding='0' cellspacing='0'>";
		$res.=		"<tr>";
		$res.=			"<th class='driver_none'>SUN</th>";
		$res.=			"<th>MON</th>";
		$res.=			"<th>TUE</th>";
		$res.=			"<th>WED</th>";
		$res.=			"<th>THU</th>";
		$res.=			"<th>FRI</th>";
		$res.=			"<th class='driver_none'>SAT</th>";
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
		
		return $res;
	} 	
	function mrr_pull_unavailable_driver_days_and_codes($date_from,$date_to,$driver_id=0)
	{
		$tab="";
		
		//unavailable
		$sql="
			select drivers_unavailable.*,
				(select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last) from drivers where drivers.id=drivers_unavailable.driver_id) as my_driver_name
			from drivers_unavailable
			where drivers_unavailable.deleted=0
				and drivers_unavailable.driver_id='".sql_friendly($driver_id)."'
				and (
					drivers_unavailable.linedate_start<='".date("Y-m-d",strtotime($date_to))." 23:59:59' and drivers_unavailable.linedate_end>='".date("Y-m-d",strtotime($date_from))." 00:00:00'
				)
			order by drivers_unavailable.linedate_start asc,drivers_unavailable.linedate_end,drivers_unavailable.id
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$tab.="
				<tr class='mrr_time_off'>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td nowrap>".trim($row['my_driver_name'])."</td>
					<td nowrap>&nbsp;</td>
					<td nowrap>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td nowrap>".date("M j, Y", strtotime($row['linedate_start']))."</td>
					<td nowrap>".date("M j, Y", strtotime($row['linedate_end']))."</td>
					<td nowrap align='right'>&nbsp;</td>
					<td nowrap align='right'>&nbsp;</td>
					<td nowrap>Unavail</td>
					<td nowrap colspan='3'>".trim($row['reason_unavailable'])."</td>
				</tr>
			";
		}
		
		//absence calendar
		$sql="
			select driver_absenses.*,
				(select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last) from drivers where drivers.id=driver_absenses.driver_id) as my_driver_name,
				(select fname from option_values where option_values.id=driver_absenses.driver_code) as my_reason
				
			from driver_absenses
			where driver_absenses.deleted=0
				and driver_absenses.driver_id='".sql_friendly($driver_id)."'
				and (
					driver_absenses.linedate>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and driver_absenses.linedate<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
				)
			order by driver_absenses.linedate,driver_absenses.id
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$tab.="
				<tr class='mrr_time_off'>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td nowrap>".trim($row['my_driver_name'])."</td>
					<td nowrap>&nbsp;</td>
					<td nowrap>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td nowrap>".date("M j, Y", strtotime($row['linedate']))."</td>
					<td nowrap>Absence</td>
					<td nowrap align='right'>&nbsp;</td>
					<td nowrap align='right'>&nbsp;</td>
					<td nowrap>".trim($row['my_reason'])."</td>
					<td nowrap colspan='3'>".trim($row['driver_reason'])."</td>
				</tr>
			";
		}
		
		//vacation advances
		$sql="
			select driver_vacation_advances_dates.*,
				(select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last) from drivers where drivers.id=driver_vacation_advances_dates.driver_id) as my_driver_name
				
			from driver_vacation_advances_dates
			where driver_vacation_advances_dates.deleted=0
				and driver_vacation_advances_dates.driver_id='".sql_friendly($driver_id)."'
				and (
					driver_vacation_advances_dates.linedate>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and driver_vacation_advances_dates.linedate<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
				)
			order by driver_vacation_advances_dates.linedate,driver_vacation_advances_dates.id
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$tab.="
				<tr class='mrr_time_off'>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td nowrap>".trim($row['my_driver_name'])."</td>
					<td nowrap>&nbsp;</td>
					<td nowrap>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td nowrap>".date("M j, Y", strtotime($row['linedate']))."</td>
					<td nowrap>Vac Adv</td>
					<td nowrap align='right'>&nbsp;</td>
					<td nowrap align='right'>&nbsp;</td>
					<td nowrap>&nbsp;</td>
					<td nowrap colspan='3'>&nbsp;</td>
				</tr>
			";
		}
		
		return $tab;
	}
	function mrr_pull_unavailable_driver_days_and_codes_symbols($date_from,$date_to,$driver_id=0,$mode=0)
	{
		$tab="";
		if($driver_id==0)		return $tab;
		
		$res['unavail']=0;
		$res['absent']=0;
		$res['vaca']=0;
		$res['tot']=0;
		
		//unavailable
		$sql="
			select drivers_unavailable.*,
				(select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last) from drivers where drivers.id=drivers_unavailable.driver_id) as my_driver_name
			from drivers_unavailable
			where drivers_unavailable.deleted=0
				and drivers_unavailable.driver_id='".sql_friendly($driver_id)."'
				and (
					(drivers_unavailable.linedate_start<='".date("Y-m-d",strtotime($date_from))." 00:00:00' and drivers_unavailable.linedate_end>='".date("Y-m-d",strtotime($date_to))." 23:59:59')
					or
					(drivers_unavailable.linedate_start<='".date("Y-m-d",strtotime($date_from))." 00:00:00' and drivers_unavailable.linedate_end='".date("Y-m-d",strtotime($date_to))." 00:00:00')
					or
					(drivers_unavailable.linedate_start='".date("Y-m-d",strtotime($date_from))." 00:00:00' and drivers_unavailable.linedate_end>='".date("Y-m-d",strtotime($date_to))." 23:59:59')
				)
			order by drivers_unavailable.linedate_start asc,drivers_unavailable.linedate_end,drivers_unavailable.id
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$tab.="<span class='mrr_unavailable' title='Unavailable from ".date("M j, Y", strtotime($row['linedate_start']))." thru ".date("M j, Y", strtotime($row['linedate_end'])).".'>Unavailable: ".trim($row['reason_unavailable'])."</span> ";
			$res['unavail']++;
			$res['tot']++;
		}
		
		//absence calendar
		$sql="
			select driver_absenses.*,
				(select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last) from drivers where drivers.id=driver_absenses.driver_id) as my_driver_name,
				(select fname from option_values where option_values.id=driver_absenses.driver_code) as my_reason
				
			from driver_absenses
			where driver_absenses.deleted=0
				and driver_absenses.driver_id='".sql_friendly($driver_id)."'
				and (
					driver_absenses.linedate>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and driver_absenses.linedate<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
				)
			order by driver_absenses.linedate,driver_absenses.id
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$tab.="<span class='mrr_absent' title='Absent on ".date("M j, Y", strtotime($row['linedate'])).": ".trim($row['driver_reason']).".'>Absence: ".trim($row['my_reason'])."</span> ";		
			$res['absent']++;	
			$res['tot']++;	
		}
		
		//vacation advances
		$sql="
			select driver_vacation_advances_dates.*,
				(select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last) from drivers where drivers.id=driver_vacation_advances_dates.driver_id) as my_driver_name
				
			from driver_vacation_advances_dates
			where driver_vacation_advances_dates.deleted=0
				and driver_vacation_advances_dates.driver_id='".sql_friendly($driver_id)."'
				and (
					driver_vacation_advances_dates.linedate>='".date("Y-m-d",strtotime($date_from))." 00:00:00' and driver_vacation_advances_dates.linedate<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
				)
			order by driver_vacation_advances_dates.linedate,driver_vacation_advances_dates.id
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$tab.="<span class='mrr_vacation' title='Vacation Day on ".date("M j, Y", strtotime($row['linedate'])).": ".trim($row['my_driver_name']).".'>Vacation Day</span> ";		
			$res['vaca']++;
			$res['tot']++;			
		}
		if($mode > 0)	return $res;
		return $tab;
	}
	
	function mrr_find_total_timesheet_hours($date_from,$date_to,$driver_id=0)
	{
		$hours=0;
		if(trim($date_from)=="")		$date_from=date("m/d/Y");
		if(trim($date_to)=="")		$date_to=date("m/d/Y");
		
		$sql = "
			select trucks_log_shuttle_routes.hours,
				trucks_log_shuttle_routes.lunch_break
			from trucks_log_shuttle_routes
				left join timesheets on timesheets.id=trucks_log_shuttle_routes.timesheet_id
			where trucks_log_shuttle_routes.deleted=0 
				and timesheets.deleted=0
				".($driver_id > 0 ? " and trucks_log_shuttle_routes.driver_id='".sql_friendly($driver_id)."'" : "")."
				and trucks_log_shuttle_routes.linedate_from >='".date("Y-m-d",strtotime($date_from))." 00:00:00'
				and trucks_log_shuttle_routes.linedate_from <='".date("Y-m-d",strtotime($date_to))." 23:59:59'
		";
		$data= simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$hours+=($row['hours'] - $row['lunch_break']);	
		}
		$res['hours']=$hours;
		$res['sql']=$sql;
		return $res;
	}
	
	//Payroll export to PeachTree (Peach Tree system is accounting software that gets imported to accounting side of Conard).
	//From here:  Dispatch gets pay - - - - > to PeachTree for checks/taxes/withholdings/etc.  - - - - > Imported later to Accounting side from PeachTree for accurate P&L, etc.
	function mrr_csv_payroll_export_file($employer_id,$date_to,$payroll_array,$date_from="",$use_hourly_bonus_calculator=0)
     {	     	
     	global $defaultsarray;
     	$filepath = trim($defaultsarray['base_path'])."www/payroll_export/";
     	
     	if(trim($date_to)=="")		$date_to=date("m/d/Y");
     	$filename="peachtree_".$employer_id."_".date("mdY",strtotime($date_to)).".csv";	
     	
     	if(trim($date_from)=="")		$date_from=$date_to;
     	
     	
     	$html="";
     	$cntr=0;
     	$res['lines_added']=0;
     	$res['html']="";
     	$res['direct_path']=$filepath.$filename;
     	$res['public_path']="/payroll_export/".$filename;
     	
     	if(!isset($payroll_array) || count($payroll_array)==0)		return $res;
     	
     	$pt_check_gtot=0;
     	
     	//column for Peachtree payroll import (CSV file).
     	/*
     			<tr>
     				<th>Vendor Name</th>			CustVendId=33 		...Description=Steven B. Finley  ...TrxName=  or ShipToName=
     				<th>Check Address1</th>			TrxAddress1=  		or ShipToAddress1=
     				<th>Check Address2</th>			TrxAddress2=  		or ShipToAddress2=
     				<th>Check City</th>				TrxCity=      		or ShipToCity=
     				<th>Check State</th>			TrxState=     		or ShipToState=
     				<th>Check Zip</th>				TrxZIP=       		or ShipToZIP=
     				<th>Check Country</th>			TrxCountry=   		or ShipToCountry=
     				<th>Check Number</th>			Reference=11749
     				<th>Date</th>					TransactionDate=2015-12-22
     				<th>Memo</th>					
     				<th>Cash Account</th>			
     				<th>Detailed Payments</th>		
     				<th>Number of Distributions</th>	
     				<th>Description</th>			Description=Steven B. Finley
     				<th>G/L Account</th>			GLAcntNumber=701
     				<th>Amount</th>				MainAmount=-1004.3400000000000000000
     				<th>Payment Method</th>			PayMethod=8  ...older version???  PaymentMethod=
     			</tr>
     			
     			could also use:
     			EndOfPayPeriod=2015-12-26 
     			WeeksWorked=1
     	*/
     	$html.="
     		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
     		<thead>
     			<tr>
     				<th>Vendor Name</th>
     				<th>Check Address1</th>
     				<th>Check Address2</th>
     				<th>Check City</th>
     				<th>Check State</th>
     				<th>Check Zip</th>
     				<th>Check Country</th>
     				<th>Check Number</th>
     				<th>Date</th>
     				<th>Memo</th>
     				<th>Cash Account</th>
     				<th>Detailed Payments</th>
     				<th>Number of Distributions</th>
     				<th>Description</th>
     				<th>G/L Account</th>
     				<th>Amount</th>
     				<th>Payment Method</th>
     			</tr>
     		</thead>
     		<tbody>
     	";
     		
     	$fp = fopen($filepath.$filename, 'w');
     	
     	foreach ($payroll_array as $fields) 
     	{
         		fputcsv($fp, $fields);
         		$cntr=1;
         		
         		$driver_id=0;        		
         		$val_tot=0;
               $tmp_hrs_worked=0;
          
               $cntrx=1;          
               foreach($fields as $valx)
               {
                    if($cntrx==19)       $tmp_hrs_worked=number_format(trim($valx),2);
                    if($cntrx==28)		$driver_id=$valx;
                    $cntrx++;
               }
          
               //New Bonus Pay calculator ....MRR Added 8/31/2021 for Dale, but only for Sept 6th and later of 2021.  Switch turns the calculator off.
               $new_bonus_pay=0;
               $extra_bonus_hours=0;
               if($use_hourly_bonus_calculator > 0)
               {
                    $new_bonus_pay=mrr_calculate_bonus_pay_from_hours($tmp_hrs_worked,$driver_id);
               
                    $bonus_reg_hours=mrr_get_driver_bonus_pay_min_hrs();
                    $extra_bonus_hours = $tmp_hrs_worked - $bonus_reg_hours;
               
                    if($new_bonus_pay > 0 && $extra_bonus_hours > 0)
                    {
                         //$extra_bonus_hours
                         $val_tot+= $new_bonus_pay;
                    }
               }
               //.....................................................................................................................................
                              
               $html.="<tr>";
         		foreach ($fields as $vals) 
         		{
         			if($cntr <=17)
         			{	
         				if($cntr==16)				$html.="<td align='right'>$".number_format(($vals + $new_bonus_pay),2)."</td>";
         				elseif($cntr==17)			$html.="<td align='right'>".$vals."</td>";
         				elseif(is_numeric($vals))	$html.="<td align='right'>".$vals."</td>";
         				else						$html.="<td>".trim($vals)."</td>";
         				   			
         				if($cntr==16)				$val_tot+=$vals;         				
         			}
         			
         			if($cntr==28)		$driver_id=$vals;
         			
         			if($cntr==17)
         			{
         				$html.="
         					</tr>
         					<tr>
         						<td valign='top' colspan='8'>&nbsp;</td>
         						<td valign='top' colspan='9' style='background-color:#ffffff;'>
         							<b>Details:</b><br> 
         							<table cellpadding='0' cellspacing='0' border='0' width='100%'>        							
         				";
         			}
         			if($cntr > 17)
         			{	//$pt_driver_miles,$pt_driver_hours,$pt_driver_taxed,$pt_driver_notax,$pt_driver_tolls,$pt_driver_layover,$pt_driver_scale,$pt_driver_holdiay,$pt_driver_vacation,$pt_driver_stopoff,$pt_driver_id
         				if($cntr==18)		$html.="<tr><td valign='top'>Miles</td>";		
         				if($cntr==19)		$html.="<tr><td valign='top'>Hours</td>";		
         				if($cntr==20)		$html.="<tr><td valign='top'>Misc. Taxed</td>";		
         				if($cntr==21)		$html.="<tr><td valign='top'>Misc. Non-Tax</td>";	
         				if($cntr==22)		$html.="<tr><td valign='top'>Tolls</td>";		
         				if($cntr==23)		$html.="<tr><td valign='top'>Layover</td>";		
         				if($cntr==24)		$html.="<tr><td valign='top'>Scale</td>";		
         				if($cntr==25)		$html.="<tr><td valign='top'>Holiday</td>";		
         				if($cntr==26)		$html.="<tr><td valign='top'>Vacation</td>";	
         				if($cntr==27)		$html.="<tr><td valign='top'>Stop Offs</td>";	
         				if($cntr==28)	
         				{
         					$carlex=mrr_find_timsheet_entries_for_payroll_period_check($date_from,$date_to,$driver_id);	
         					
         					if($carlex['hours'] > 0)		$html.="<tr><td valign='top'>Carlex Time Sheet Hour(s): ".$carlex['hours']."</td><td valign='top' align='right' width='25%'>$".number_format($carlex['total'],2)."</td></tr>";  
         					if($carlex['runs'] > 0)		$html.="<tr><td valign='top'>Carlex Shuttle Run(s): ".$carlex['runs']." </td><td valign='top' align='right' width='25%'>$".number_format($carlex['total2'],2)."</td></tr>";  
         					$val_tot+=($carlex['total'] + $carlex['total2']);       					
         				}        				
         				
         				if($cntr!=28)	$html.="<td valign='top' align='right' width='25%'>".($cntr >=20 ? "$" : "")."".number_format(trim($vals),2)."</td></tr>";
         			}			
         			
         			$cntr++;
         		}
               //$html.="<tr><td valign='top'>Driver ID</td><td valign='top' align='right'>[".$tmp_hrs_worked." of ".$bonus_reg_hours." hrs] ".$driver_id."</td></tr>";
               //$html.="<tr><td valign='top'>BONUS HRS</td><td valign='top' align='right'>".number_format($extra_bonus_hours,2)."</td></tr>";
               //$html.="<tr><td valign='top'>BONUS PAY</td><td valign='top' align='right'>$".number_format($new_bonus_pay,2)."</td></tr>";
         		if($cntr > 17)		$html.="</table></td>";
         		$html.="</tr>";
         		
         		$pt_check_gtot+=$val_tot;         		
         		
         		$cntr++;
     	}
     	
     	$html.="
     			<tr style='font-weight:bold;'>
     				<td colspan='17'><hr></td> 
     			</tr>
     			<tr style='font-weight:bold;'>
     				<td colspan='15'>".$cntr." Checks to be imported...</td>     				
     				<td align='right'>$".number_format($pt_check_gtot,2)."</td>
     				<td align='right'>TOTAL</td>
     			</tr>
     		</tbody>
     		</table>
     	";
     	
     	fclose($fp);
     	$res['html']=$html;
     	$res['lines_added']=$cntr;
     	return $res;
     }
     function mrr_get_driver_check_address_info($id)
     {
     	$res['first']="";
		$res['last']="";
		$res['addr1']="";
		$res['addr2']="";
		$res['city']="";
		$res['state']="";
		$res['zip']="";
		
     	$sql = "
			select *
			from drivers
			where id='".sql_friendly($id)."'
		";
		$data= simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			$res['first']=trim($row['name_driver_first']);
			$res['last']=trim($row['name_driver_last']);
			$res['addr1']=trim($row['driver_address_1']);
			$res['addr2']=trim($row['driver_address_2']);
			$res['city']=trim($row['driver_city']);
			$res['state']=trim($row['driver_state']);
			$res['zip']=trim($row['driver_zip']);
		}	
		return $res;
     }
     
     
     function mrr_find_timsheet_entries_for_payroll_period($date_from,$date_to,$employer_id=0,$driver_id=0)
	{			
		$tab="
			<table cellpadding='0' cellspacing='0' width='100%' border='0'>
			<thead>
			<tr>
				<th>&nbsp;</th>
				<th>Date</th>
				<th>Time In</th>
				<th>Time Out</th>		
				<th>Chart Number</th>
				<th>Account</th>
				<th>Driver</th>	
				<th>Type</th>
				<th>Hours/Route</th>												
				<th>Rate</th>
				<th>Amount</th>
				<th>&nbsp;</th>
			</tr>
			</thead>
          	<tbody>
		";
		$bill_cntr=0;
		$bill_tot=0;	
		$bill_tot2=0;	
		$cntr=0;
		
		$exps=0;
		$exp_chart[0]="";
		$exp_acct[0]="";		
		$exp_employ[0]=0;
		$exp_driver[0]=0;
		$exp_name[0]="";
		$exp_notes[0]="";		
		$exp_hrs[0]=0;
		$exp_pay[0]=0;
		$exp_runs[0]=0;
		$exp_shuttle[0]=0;		
		$exp_amnt[0]=0;
		
		$sql = "
			select trucks_log_shuttle_routes.* ,
				timesheets.customer_id as cust_id,
				(select name_company from customers where customers.id=timesheets.customer_id) as cust_name,
				CONCAT(drivers.name_driver_first, ' ' ,drivers.name_driver_last) as mydriver,
				drivers.employer_id,
				(select trucks.name_truck from trucks where trucks.id=trucks_log_shuttle_routes.truck_id) as mytruck,
				(select trailers.trailer_name from trailers where trailers.id=trucks_log_shuttle_routes.trailer_id) as mytrailer,				
				
				(select option_values.fname from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myname,
				(select option_values.fvalue from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myval
			from trucks_log_shuttle_routes
				left join timesheets on timesheets.id=trucks_log_shuttle_routes.timesheet_id
				left join drivers on drivers.id=trucks_log_shuttle_routes.driver_id
			where trucks_log_shuttle_routes.deleted=0 
				and timesheets.deleted=0
				".($employer_id > 0 ? " and drivers.employer_id='".sql_friendly($employer_id)."'" : "")."
				".($driver_id > 0 ? " and trucks_log_shuttle_routes.driver_id='".sql_friendly($driver_id)."'" : "")."
				and trucks_log_shuttle_routes.linedate_from>='".date("Y-m-d",strtotime($date_from))." 00:00:00'
				and trucks_log_shuttle_routes.linedate_from<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
			order by drivers.name_driver_last asc,
					drivers.name_driver_first asc,
					trucks_log_shuttle_routes.linedate_from,
					trucks_log_shuttle_routes.linedate_to,
					trucks_log_shuttle_routes.id
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{				
			$use_pay_rate=$row['pay_rate_hours'];
			$use_pay_rate=option_value_text(145,2);			//grab from the NONE - Switching ONLY rate.	
			
			if($row['cust_id'] ==1687)	$use_pay_rate=option_value_text(159,2);			//grab from the NONE - Switching ONLY rate.	
					
			
			$use_pay_rate=$row['pay_charged_per_hour'];		//pay_charged_per_mile
			
			$use_pay_hours=$row['conard_hours'];
			//$use_pay_hours=($row['conard_hours'] - $row['lunch_break']);
			
			$my_cust_id=$row['cust_id'];
			$my_cust="&nbsp;";
			if($row['cust_id'] > 0)			$my_cust=substr(trim($row['cust_name']),0,6);
									
			if($row['truck_id'] > 0)
			{
     			$exp_chart[$exps]="";
				$exp_acct[$exps]="";		
				$exp_driver[$exps]=0;				
				$exp_employ[$exps]=0;
				$exp_name[$exps]="";
				
				$exp_notes[$exps]="";		
				$exp_hrs[$exps]=0;
				$exp_pay[$exps]=0;
				$exp_runs[$exps]=0;
				$exp_shuttle[$exps]=0;		
				$exp_amnt[$exps]=0;
				
				
     			
     			if($row['option_id'] > 0 && $row['option_id'] !=145 && $row['option_id'] !=159)	
     			{
     				//Shuttle Routes ===============NOT FOR PAYROLL...only for invoice to Carlex.
     				
     				$tab.="	
          				<tr class='carlex_timesheet shuttle_run'>	
          					<td valign='top'>&nbsp;</td>	
          					<td valign='top'>".date("m/d/Y", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_to']))."</td>	          															
          					<td valign='top'>67000-".mrr_make_numeric2(trim($row['mytruck']))."</td>	
          					<td valign='top'>Lease Drivers - #".mrr_make_numeric2(trim($row['mytruck']))."</td>	          					
          					<td valign='top'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>
          					<td valign='top'>Shuttle Run</td>				
          					<td valign='top'>".trim($row['myname'])."</td>
          					<td valign='top' align='right'>$".number_format($row['route_rate'],2)."</td>         								
          					<td valign='top' align='right'>$".number_format(($row['route_rate']),2)."</td>					
          					<td valign='top' align='right'>".$my_cust."</td>				
          				</tr>
          			";	   
          			$exp_notes[$exps]="".date("m/d/Y", strtotime($row['linedate_from']))." Shuttle Run: ".trim($row['myname'])."";
          								
					//Addeed May 4th, 2018 now that Carlex uses hrs even on shuttle runs.
					//$exp_hrs[$exps]=0;
					//$exp_pay[$exps]=0;
					$exp_hrs[$exps]=$use_pay_hours;
					$exp_pay[$exps]=($use_pay_rate * $use_pay_hours);
					
					$exp_runs[$exps]=1;
					$exp_shuttle[$exps]=$row['route_rate'];		
					$exp_amnt[$exps]=$row['route_rate'];
          			       			
          			//$bill_tot2+=($row['route_rate']);
          			$bill_cntr++;			
          				
     			}
     			else
     			{
     				//"Carlex" Hours (Non-Shuttle)
     				$tab.="	
          				<tr class='carlex_timesheet'>	
          					<td valign='top'>&nbsp;</td>	
          					<td valign='top'>".date("m/d/Y", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_to']))."</td>	 
          					<td valign='top'>67000-".mrr_make_numeric2(trim($row['mytruck']))."</td>	
          					<td valign='top'>Lease Drivers - #".mrr_make_numeric2(trim($row['mytruck']))."</td>
          					<td valign='top'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>  
          					<td valign='top'>Time Sheet</td>
          					<td valign='top' align='right'>".number_format($use_pay_hours,2)." Hrs</td>
          					<td valign='top' align='right'>$".number_format($use_pay_rate,2)."/hr</td>    					
          					<td valign='top' align='right'>$".number_format(($use_pay_rate * $use_pay_hours),2)."</td>					
          					<td valign='top' align='right'>".$my_cust."</td>				
          				</tr>
          			";	
          			$exp_notes[$exps]="".date("m/d/Y", strtotime($row['linedate_from']))." ".$my_cust." Labor: ".number_format($use_pay_hours,2)." Hrs at $".number_format($use_pay_rate,2)."/hr";
          																	//".date("H:i", strtotime($row['linedate_from']))."-".date("H:i", strtotime($row['linedate_to']))."
          			$exp_hrs[$exps]=$use_pay_hours;
					$exp_pay[$exps]=($use_pay_rate * $use_pay_hours);
					$exp_runs[$exps]=0;
					$exp_shuttle[$exps]=0;		
					$exp_amnt[$exps]=($use_pay_rate * $use_pay_hours);
          			
          			$bill_tot+=($use_pay_rate * $use_pay_hours);
          			$bill_cntr++;	
     			}	
     			$exp_employ[$exps]=$row['employer_id'];
				$exp_name[$exps]="".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."";
     			$exp_chart[$exps]="67000-".mrr_make_numeric2(trim($row['mytruck']))."";
				$exp_acct[$exps]="Lease Drivers - #".mrr_make_numeric2(trim($row['mytruck']))."";		
				$exp_driver[$exps]=$row['driver_id'];	
					
     			$exps++;
			}
			else
			{	//no truck = no chart                 
				$tab.="	
          				<tr class='carlex_timesheet mrr_alert'>	
          					<td valign='top'>&nbsp;</td>	
          					<td valign='top'>".date("m/d/Y", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_to']))."</td>	 
          					<td valign='top'>ERROR</td>	
          					<td valign='top'>No Account Found. (See Truck.)</td>	
          					<td valign='top'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>   
          					<td valign='top'>Payroll Item</td>	
          					<td valign='top' align='right'>".number_format($use_pay_hours,2)." Hrs</td>
          					<td valign='top' align='right'>$".number_format($use_pay_rate,2)."/hr</td> 
          					<td valign='top' align='right'>$".number_format(($use_pay_rate * $use_pay_hours),2)."</td>					
          					<td valign='top' align='right'>&nbsp;</td>				
          				</tr>
          		";	          			
          		//$bill_tot+=($use_pay_rate * $use_pay_hours);
          		//$bill_cntr++;	
			}
			$cntr++;
		}
		$tab.="
			<tr>
				<td valign='top'>&nbsp;</td>	
				<td valign='top' colspan='7'>".$bill_cntr." Payroll Item(s) Total </td>
				<td valign='top' colspan='3' align='right'>$".number_format($bill_tot,2)."</td>
				<td valign='top'>&nbsp;</td>					
			</tr>
			</tbody>
		";	
		$tab.="</table>";	
				
		$res['cntr']=$bill_cntr;
		$res['total']=$bill_tot;
		$res['total2']=$bill_tot2;
		$res['html']=$tab;		
		
		$res['num']=$exps;
		$res['chart']=$exp_chart;
		$res['acct']=$exp_acct;
		$res['driver']=$exp_driver;
		$res['name']=$exp_name;
		$res['employ']=$exp_employ;
		$res['notes']=$exp_notes;
		$res['hrs']=$exp_hrs;
		$res['pay']=$exp_pay;
		$res['runs']=$exp_runs;
		$res['shuttle']=$exp_shuttle;
		$res['amnt']=$exp_amnt;
		
		return $res;
	}
	function mrr_find_timsheet_entries_for_payroll_period_check($date_from,$date_to,$driver_id)
	{			
		$tab="
			<table cellpadding='0' cellspacing='0' width='100%' border='0'>
			<thead>
			<tr>
				<th>&nbsp;</th>
				<th>Date</th>
				<th>Time In</th>
				<th>Time Out</th>		
				<th>Chart Number</th>
				<th>Account</th>
				<th>Driver</th>	
				<th>Type</th>
				<th>Hours/Route</th>												
				<th>Rate</th>
				<th>Amount</th>
				<th>&nbsp;</th>
			</tr>
			</thead>
          	<tbody>
		";
		$bill_cntr=0;
		$bill_tot=0;	
		$bill_tot2=0;	
		$bill_hrs=0;	
		$bill_runs=0;	
		
		$cntr=0;
		
		/*		
				load_id,				
				trucks_log_id,				
				(select users.username from users where users.id=trucks_log_shuttle_routes.user_id) as myuser,
		*/
		$sql = "
			select trucks_log_shuttle_routes.* ,
				CONCAT(drivers.name_driver_first, ' ' ,drivers.name_driver_last) as mydriver,
				drivers.employer_id,
				(select trucks.name_truck from trucks where trucks.id=trucks_log_shuttle_routes.truck_id) as mytruck,
				(select trailers.trailer_name from trailers where trailers.id=trucks_log_shuttle_routes.trailer_id) as mytrailer,				
				
				(select option_values.fname from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myname,
				(select option_values.fvalue from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myval
			from trucks_log_shuttle_routes
				left join timesheets on timesheets.id=trucks_log_shuttle_routes.timesheet_id
				left join drivers on drivers.id=trucks_log_shuttle_routes.driver_id
			where trucks_log_shuttle_routes.deleted=0 
				and timesheets.deleted=0
				and trucks_log_shuttle_routes.driver_id='".sql_friendly($driver_id)."'
				and trucks_log_shuttle_routes.linedate_from>='".date("Y-m-d",strtotime($date_from))." 00:00:00'
				and trucks_log_shuttle_routes.linedate_from<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
			order by drivers.name_driver_last asc,
					drivers.name_driver_first asc,
					trucks_log_shuttle_routes.linedate_from,
					trucks_log_shuttle_routes.linedate_to,
					trucks_log_shuttle_routes.id
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{				
			$use_pay_rate=$row['pay_rate_hours'];
			//$use_pay_rate=option_value_text(145,2);		//grab from the NONE - Switching ONLY rate.
			
			$use_pay_hours=$row['conard_hours'];
			//$use_pay_hours=($row['conard_hours'] - $row['lunch_break']);
						
			if($row['truck_id'] > 0)
			{     			
     			if($row['option_id'] > 0 && $row['option_id'] !=145)	
     			{
     				//Shuttle Routes====================================NOT FOR PAYROLL....only for Carlex Invoice out.
     				$tab.="	
          				<tr class='carlex_timesheet shuttle_run'>	
          					<td valign='top'>&nbsp;</td>	
          					<td valign='top'>".date("m/d/Y", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_to']))."</td>	          															
          					<td valign='top'>67000-".mrr_make_numeric2(trim($row['mytruck']))."</td>	
          					<td valign='top'>Lease Drivers - #".mrr_make_numeric2(trim($row['mytruck']))."</td>	          					
          					<td valign='top'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>
          					<td valign='top'>Shuttle Run</td>				
          					<td valign='top'>".trim($row['myname'])."</td>
          					<td valign='top' align='right'>$".number_format($row['route_rate'],2)."</td>         								
          					<td valign='top' align='right'>$".number_format(($row['route_rate']),2)."</td>					
          					<td valign='top' align='right'>&nbsp;</td>				
          				</tr>
          			";	   
          			
					//$bill_runs++;	    			
          			//$bill_tot2+=($row['route_rate']);
          			$bill_cntr++;					
     			}
     			else
     			{
     				//"Carlex" Hours (Non-Shuttle)
     				$tab.="	
          				<tr class='carlex_timesheet'>	
          					<td valign='top'>&nbsp;</td>	
          					<td valign='top'>".date("m/d/Y", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_to']))."</td>	 
          					<td valign='top'>67000-".mrr_make_numeric2(trim($row['mytruck']))."</td>	
          					<td valign='top'>Lease Drivers - #".mrr_make_numeric2(trim($row['mytruck']))."</td>
          					<td valign='top'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>  
          					<td valign='top'>Time Sheet</td>
          					<td valign='top' align='right'>".number_format($use_pay_hours,2)." Hrs</td>
          					<td valign='top' align='right'>$".number_format($use_pay_rate,2)."/hr</td>    					
          					<td valign='top' align='right'>$".number_format(($use_pay_rate * $use_pay_hours),2)."</td>					
          					<td valign='top' align='right'>&nbsp;</td>				
          				</tr>
          			";	
          			
          			$bill_hrs+=$use_pay_hours;	
          			$bill_tot+=($use_pay_rate * $use_pay_hours);
          			$bill_cntr++;	
     			}    			
			}
			else
			{	//no truck = no chart                 
				$tab.="	
          				<tr class='carlex_timesheet mrr_alert'>	
          					<td valign='top'>&nbsp;</td>	
          					<td valign='top'>".date("m/d/Y", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_from']))."</td>	
          					<td valign='top'>".date("H:i", strtotime($row['linedate_to']))."</td>	 
          					<td valign='top'>ERROR</td>	
          					<td valign='top'>No Account Found. (See Truck.)</td>	
          					<td valign='top'>".($row['driver_id'] > 0 ? trim($row['mydriver']) : "")."</td>   
          					<td valign='top'>Payroll Item</td>	
          					<td valign='top' align='right'>".number_format($use_pay_hours,2)." Hrs</td>
          					<td valign='top' align='right'>$".number_format($use_pay_rate,2)."/hr</td> 
          					<td valign='top' align='right'>$".number_format(($use_pay_rate * $use_pay_hours),2)."</td>					
          					<td valign='top' align='right'>&nbsp;</td>				
          				</tr>
          		";	          			
          		//$bill_tot+=($use_pay_rate * $use_pay_hours);
          		//$bill_cntr++;	
			}
			$cntr++;
		}
		$tab.="
			<tr>
				<td valign='top'>&nbsp;</td>	
				<td valign='top' colspan='7'>".$bill_cntr." Payroll Item(s) Total </td>
				<td valign='top' colspan='3' align='right'>$".number_format($bill_tot,2)."</td>
				<td valign='top'>&nbsp;</td>					
			</tr>
			</tbody>
		";	
		$tab.="</table>";	
				
		$res['cntr']=$bill_cntr;
		
		$res['hours']=$bill_hrs;
		$res['runs']=$bill_runs;
		
		$res['total']=$bill_tot;
		$res['total2']=$bill_tot2;
		$res['html']=$tab;	
		
		return $res;
	}
	
	
	function mrr_find_driver_load_use($driver_id)
	{
		$res['preplanned']=0;
		$res['next_preplan']="";	
		$res['next_preplan_id']="";
		
		$res['last_dispatch_id']="";
		$res['last_dispatch']="";
			
		$res['next_dispatch_id']="";
		$res['next_dispatch']="";	
		$res['dispatches']=0;
		
		if($driver_id==0)		return $res;
		
		//get preplanned for this driver
		$sql = "
			select *
			from load_handler
			where deleted = 0
				and preplan > 0
				and (preplan_driver_id='".sql_friendly($driver_id)."' or preplan_driver2_id='".sql_friendly($driver_id)."' or preplan_leg2_driver_id='".sql_friendly($driver_id)."' or preplan_leg2_driver2_id='".sql_friendly($driver_id)."')
			order by linedate_pickup_eta desc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$res['preplanned']++;
			$res['next_preplan']="".$row['origin_city'].", ".$row['origin_state']." to ".$row['dest_city'].", ".$row['dest_state'].".";	
			$res['next_preplan_id']="Next Preplan Load <a href='manage_load.php?load_id=".$row['id']."' target='_blank'><b>".$row['id']."</b></a>";
		}
		
		//get dispatches for this driver
		$sql = "
			select trucks_log.*,
				(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_name
			from trucks_log
				left join load_handler on load_handler.id=trucks_log.load_handler_id
			where trucks_log.deleted = 0
				and load_handler.deleted = 0
				and trucks_log.dispatch_completed=0				
				and (trucks_log.driver_id='".sql_friendly($driver_id)."' or trucks_log.driver2_id='".sql_friendly($driver_id)."')
			order by trucks_log.linedate_pickup_eta desc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$res['dispatches']++;
			$res['next_dispatch']="
				PU ETA ".date("m/d/Y",strtotime($row['linedate_pickup_eta'])).": 
				Truck <a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'><b>".trim($row['truck_name'])."</b></a> 
				".$row['origin'].", ".$row['origin_state']." to ".$row['destination'].", ".$row['destination_state'].".
			";	
			$res['next_dispatch_id']="Next Dispatch <a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'><b>".$row['id']."</b></a>";
		}
		
		//get last dispatch for this driver
		$sql = "
			select trucks_log.*,
				(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_name
			from trucks_log
				left join load_handler on load_handler.id=trucks_log.load_handler_id
			where trucks_log.deleted = 0
				and load_handler.deleted = 0
				and trucks_log.dispatch_completed > 0				
				and (trucks_log.driver_id='".sql_friendly($driver_id)."' or trucks_log.driver2_id='".sql_friendly($driver_id)."')
			order by trucks_log.linedate_pickup_eta desc
			limit 1
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$res['last_dispatch']="
				As of ".date("m/d/Y",strtotime($row['linedate_pickup_eta'])).": 
				Truck <a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'><b>".trim($row['truck_name'])."</b></a> 
				from ".$row['origin'].", ".$row['origin_state']." to ".$row['destination'].", ".$row['destination_state'].".
			";	
			$res['last_dispatch_id']="Last Dispatch <a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'><b>".$row['id']."</b></a>";
		}
		
		return $res;
	}
	
	
	//driver DOT checklist functions....	
	function mrr_list_driver_dot_checklist_forms($driver_id)
	{
		$tab="";
		if($driver_id==0)		return $tab;
				
		$dname=mrr_get_driver_name($driver_id);
		$dl_num=mrr_get_driver_license_number($driver_id);
		
		$tab="<br><hr><br>";		
		$tab.="	
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>		
			<tr>
				<td valign='top' colspan='7'><b>DRIVER DOT PERSONNEL CHECK LIST FILE ARCHIVE</b></td>
			</tr>
			<tr>
     			<td valign='top'><a href='admin_drivers.php?id=".$driver_id."'>NEW</a></td>     				
     			<td valign='top'><b>Entered by</b></td>
     			<td valign='top'><b>Date Entered</b></td>
     			<td valign='top'><b>Received</b></td>
     			<td valign='top'><b>Date Received</b></td>
     			<td valign='top'><b>Reviewed by</b></td>
     			<td valign='top'><b>Date Reviewed</b></td>
    			</tr>
		";
		$cntr=0;
		
		//list the records...
		$sql = "
			select driver_dot_checklist.*,
				users.name_first,
				users.name_last,
				users.username
				
			from driver_dot_checklist
				left join users on users.id=driver_dot_checklist.user_id
			where driver_dot_checklist.deleted = 0
				and driver_dot_checklist.driver_id='".sql_friendly($driver_id)."'
								
			order by driver_dot_checklist.linedate_added desc
		";
		$data = simple_query($sql);
		while($post = mysqli_fetch_array($data)) 	
		{			
			
			$username=mrr_peoplenet_pull_quick_username($post['user_id']);
			$fullname=mrr_peoplenet_pull_quick_user_fullname($post['user_id']);	
			
			$username2="";		
			$fullname2="";
			$username3="";		
			$fullname3="";
			
			if($post['received_user_id'] > 0)		$username2=mrr_peoplenet_pull_quick_username($post['received_user_id']);		
			if($post['received_user_id'] > 0)		$fullname2=mrr_peoplenet_pull_quick_username($post['received_user_id']);
			if($post['reviewed_user_id'] > 0)		$username3=mrr_peoplenet_pull_quick_username($post['reviewed_user_id']);		
			if($post['reviewed_user_id'] > 0)		$fullname3=mrr_peoplenet_pull_quick_username($post['reviewed_user_id']);
					
			$tab.="
     			<tr style='background-color:#".($cntr % 2==0 ? "eeeeee" : "dddddd").";'>
     				<td valign='top'><a href='admin_drivers.php?id=".$driver_id."&checklist_id=".$post['id']."'>".$post['id']."</a></td>     				
     				<td valign='top'>".$fullname."</td>
     				<td valign='top'>".date("m/d/Y H :i",strtotime($post['linedate_added']))."</td>
     				<td valign='top'>".$fullname2."</td>
     				<td valign='top'>".($post['linedate_received']!="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($post['linedate_received']))."" : "")."</td>
     				<td valign='top'>".$fullname3."</td>
     				<td valign='top'>".($post['linedate_reviewed']!="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($post['linedate_reviewed']))."" : "")."</td>
     			</tr>
     		";
			$cntr++;
		}
		
		$tab.="</table>";
		return $tab;		
	}
	
	function mrr_load_driver_dot_checklist_form($driver_id,$id)
	{
		$tab="";
		if($driver_id==0)		return $tab;
				
		$dname=mrr_get_driver_name($driver_id);
		$dl_num=mrr_get_driver_license_number($driver_id);
		$med_carder=mrr_get_driver_med_card_exp($driver_id);
		
		$username=mrr_peoplenet_pull_quick_username($_SESSION['user_id']);
		$fullname=mrr_peoplenet_pull_quick_user_fullname($_SESSION['user_id']);		
		$username2=$username;		
		$fullname2=$fullname;
		$username3=$username;		
		$fullname3=$fullname;
		
		//get the selected id for the Form...or get last one that is current..a.k.a. "newer_record"
		$sql = "
			select driver_dot_checklist.*,
				users.name_first,
				users.name_last,
				users.username
				
			from driver_dot_checklist
				left join users on users.id=driver_dot_checklist.user_id
			where driver_dot_checklist.deleted = 0
				and driver_dot_checklist.driver_id='".sql_friendly($driver_id)."'
				".($id > 0  ? " and driver_dot_checklist.id='".sql_friendly($id)."'" : " and driver_dot_checklist.newer_record=1")."
				
			order by driver_dot_checklist.linedate_added desc
			limit 1
		";
		$data = simple_query($sql);
		if($post = mysqli_fetch_array($data)) 	
		{
			//already have info in my post variable...field name is the array item name...
			
			$username=mrr_peoplenet_pull_quick_username($post['user_id']);
			$fullname=mrr_peoplenet_pull_quick_user_fullname($post['user_id']);	
			$username2=mrr_peoplenet_pull_quick_username($post['received_user_id']);		
			$fullname2=mrr_peoplenet_pull_quick_username($post['received_user_id']);
			$username3=mrr_peoplenet_pull_quick_username($post['reviewed_user_id']);		
			$fullname3=mrr_peoplenet_pull_quick_username($post['reviewed_user_id']);
		}
		else
		{
			$post['id']=0;
			$post['driver_id']=0;
			$post['user_id']=0;
			$post['deleted']=0;
			$post['linedate_added']="0000-00-00 00:00:00";
			$post['newer_record']=0;
			
			$post['received_user_id']=0;
			$post['reviewed_user_id']=0;
			
			$post['linedate_reviewed']="0000-00-00 00:00:00";
			$post['linedate_received']="0000-00-00 00:00:00";
			
			$post['master_info_sheet']=0;
			$post['mandatory_psp']=0;
			$post['psp_report']=0;
			$post['driving_record_auth']=0;
			$post['mvr_report']=0;
			$post['employee_application']=0;
			$post['driver_data_sheet']=0;
			$post['amvdcv_done']=0;
			$post['road_test']=0;
			$post['drug_test_consent']=0;
			$post['pre_employ_drug_screen']=0;
			$post['pre_employ_drug_result']=0;
			$post['employee_loan']=0;
			$post['controlled_sub_abuse']=0;
			$post['form_1_9']=0;
			$post['form_w_4']=0;
			$post['new_hire_pay_info']=0;
			$post['wage_deduct_policy']=0;
			$post['direct_deposit']=0;
			$post['aflac_page']=0;
			$post['med_exam_cert']=0;
			$post['med_exam_form']=0;
			$post['med_card']=0;
			$post['med_card_info']="0000-00-00 00:00:00";
			$post['driver_license']=0;
			$post['driver_license_num']="";
			$post['ssn_copy']=0;
			$post['ssn_num']="";
			$post['driver_point_system']=0;
			$post['camera_acknowledged']=0;
			$post['bio_page']=0;
			$post['driver_safety_policy']=0;
			$post['meet_and_greet']=0;
			$post['fuel_card']=0;
			$post['peoplenet_training']=0;
			$post['phone_list']=0;
			$post['after_hours']=0;
			$post['assign_truck']=0;
			$post['employee_handbook']=0;
			$post['pre_post_trips']=0;
			$post['employer_1']="";
			$post['employer_2']="";
			$post['employer_3']="";
			$post['employer_4']="";
			$post['emp_1_sent_1']="0000-00-00 00:00:00";
			$post['emp_1_sent_2']="0000-00-00 00:00:00";
			$post['emp_1_sent_3']="0000-00-00 00:00:00";
			$post['emp_1_received']="0000-00-00 00:00:00";
			$post['emp_2_sent_1']="0000-00-00 00:00:00";
			$post['emp_2_sent_2']="0000-00-00 00:00:00";
			$post['emp_2_sent_3']="0000-00-00 00:00:00";
			$post['emp_2_received']="0000-00-00 00:00:00";
			$post['emp_3_sent_1']="0000-00-00 00:00:00";
			$post['emp_3_sent_2']="0000-00-00 00:00:00";
			$post['emp_3_sent_3']="0000-00-00 00:00:00";
			$post['emp_3_received']="0000-00-00 00:00:00";
			$post['emp_4_sent_1']="0000-00-00 00:00:00";
			$post['emp_4_sent_2']="0000-00-00 00:00:00";
			$post['emp_4_sent_3']="0000-00-00 00:00:00";
			$post['emp_4_received']="0000-00-00 00:00:00";
			
			//added on 7/20/2017
			$post['driver_photo']=0;
			$post['driver_door_code']=0;
			$post['driver_box_setup']=0;
			$post['driver_should_haves']=0;
			$post['driver_fleet_one']=0;
			$post['driver_add_pn']=0;
			$post['driver_key_tags']=0;
			$post['driver_speed_space']=0;
		}
		
		$text_width=200;
		$date_width=80;
		
		$version1=280;
		
		$tab="";		
		$tab.="
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			<tr>
				<td valign='top' colspan='6'><b>DRIVER DOT PERSONNEL FILE CHECK LIST</b></td>
			</tr>
		";
		//header/summary section
		$tab.="
			<tr style='background-color:#eeeeee;'>
				<td valign='top' colspan='2'>Driver's Name</td>
				<td valign='top'>".$dname."</td>
				<td valign='top' colspan='2'>Date entered into File</td>
				<td valign='top'>".($post['linedate_added']!="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($post['linedate_added']))."" : "".date("m/d/Y",time())."")."</td>
			</tr>
			<tr style='background-color:#dddddd;'>
				<td valign='top' colspan='2'>Employee Number</td>
				<td valign='top'>".$driver_id."</td>
				<td valign='top' colspan='2'>Entered by</td>
				<td valign='top'>".$fullname."</td>
			</tr>
			<tr style='background-color:#eeeeee; display:none;'>				
				<td valign='top' colspan='2'>Received by</td>
				<td valign='top'>".$fullname2." (".($post['linedate_received']!="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($post['linedate_received']))."" : "".date("m/d/Y",time())."").")</td>
				<td valign='top' colspan='2'>Reviewed by</td>
				<td valign='top'>".$fullname3." (".($post['linedate_reviewed']!="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($post['linedate_reviewed']))."" : "".date("m/d/Y",time())."").")</td>
			</tr>
				
			<tr>
				<td valign='top' colspan='6'>&nbsp;<br>&nbsp;</td>
			</tr>
		";	//$username	//$username2	//$username3
				
		if(trim($post['driver_license_num'])=="")										$post['driver_license_num']=$dl_num;
		if(trim($post['med_card_info'])=="" || $post['med_card_info']=="0000-00-00 00:00:00")	$post['med_card_info']=$med_carder;
		
		//main checklist section...
		if($post['id'] > 0 && $post['id']<=$version1)
		{	//older form
     		$tab.="
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>1</td>
     				<td valign='top'><input type='checkbox' name='master_info_sheet' id='master_info_sheet' value='1'".($post['master_info_sheet'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='master_info_sheet'>Driver Master Information sheet</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>2</td>
     				<td valign='top'><input type='checkbox' name='mandatory_psp' id='mandatory_psp' value='1'".($post['mandatory_psp'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='mandatory_psp'>Mandatory Consent & release PSP</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' align='right'>A</td>
     				<td valign='top'><input type='checkbox' name='psp_report' id='psp_report' value='1'".($post['psp_report'] > 0  ? " checked" : "")."></td>			
     				<td valign='top' colspan='3'><label for='psp_report'>PSP REPORT</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>3</td>
     				<td valign='top'><input type='checkbox' name='driving_record_auth' id='driving_record_auth' value='1'".($post['driving_record_auth'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='driving_record_auth'>Authorization for Check of Driving Record</label></td>
     				<td valign='top'>(49 CFR 391.23)</td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' align='right'>A</td>
     				<td valign='top'><input type='checkbox' name='mvr_report' id='mvr_report' value='1'".($post['mvr_report'] > 0  ? " checked" : "")."></td>				
     				<td valign='top' colspan='3'><label for='mvr_report'>MVR report</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>4</td>
     				<td valign='top'><input type='checkbox' name='employee_application' id='employee_application' value='1'".($post['employee_application'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='employee_application'>Employee Application</label></td>
     				<td valign='top'>(49 CFR 391.21)</td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>5</td>
     				<td valign='top'><input type='checkbox' name='driver_data_sheet' id='driver_data_sheet' value='1'".($post['driver_data_sheet'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='driver_data_sheet'>Driver Data Sheet</label></td>
     				<td valign='top'>(49 CFR 395.8)</td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>6</td>
     				<td valign='top'><input type='checkbox' name='amvdcv_done' id='amvdcv_done' value='1'".($post['amvdcv_done'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='amvdcv_done'>Annual motor Vehicle Drivers Certification of Violations</label></td>
     				<td valign='top'>(49 CFR 391.27)</td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>7</td>
     				<td valign='top'><input type='checkbox' name='road_test' id='road_test' value='1'".($post['road_test'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='road_test'>ROAD TEST</label></td>
     				<td valign='top'>(49 CFR 391.31)</td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>8</td>
     				<td valign='top'><input type='checkbox' name='drug_test_consent' id='drug_test_consent' value='1'".($post['drug_test_consent'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='drug_test_consent'>Drug Test Consent Form</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='pre_employ_drug_screen' id='pre_employ_drug_screen' value='1'".($post['pre_employ_drug_screen'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='pre_employ_drug_screen'>Pre-Employment Drug Screening (send for Test)</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='pre_employ_drug_result' id='pre_employ_drug_result' value='1'".($post['pre_employ_drug_result'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='pre_employ_drug_result'>Pre-Employment Drug Screen Results</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>9</td>
     				<td valign='top'><input type='checkbox' name='employee_loan' id='employee_loan' value='1'".($post['employee_loan'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='employee_loan'>Employee loan agreement</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>10</td>
     				<td valign='top'><input type='checkbox' name='controlled_sub_abuse' id='controlled_sub_abuse' value='1'".($post['controlled_sub_abuse'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='controlled_sub_abuse'>Controlled Substance Abuse Form</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>11</td>
     				<td valign='top'><input type='checkbox' name='form_1_9' id='form_1_9' value='1'".($post['form_1_9'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='form_1_9'>I-9 FORM</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>12</td>
     				<td valign='top'><input type='checkbox' name='form_w_4' id='form_w_4' value='1'".($post['form_w_4'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='form_w_4'>W-4</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>13</td>
     				<td valign='top'><input type='checkbox' name='new_hire_pay_info' id='new_hire_pay_info' value='1'".($post['new_hire_pay_info'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='new_hire_pay_info'>New Hire Pay Information</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>14</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='wage_deduct_policy' id='wage_deduct_policy' value='1'".($post['wage_deduct_policy'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='wage_deduct_policy'>Conard Wage deduction policy</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>15</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='direct_deposit' id='direct_deposit' value='1'".($post['direct_deposit'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='direct_deposit'>Direct Deposit</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>16</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='aflac_page' id='aflac_page' value='1'".($post['aflac_page'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='aflac_page'>AFLAC PAGE</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='med_exam_cert' id='med_exam_cert' value='1'".($post['med_exam_cert'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='med_exam_cert'>Medical Exam Report & Certification</td>
     				<td valign='top'>(49 CFR 391.43 & 391.49)</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='med_exam_form' id='med_exam_form' value='1'".($post['med_exam_form'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='med_exam_form'>Medical Exam Long Form</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='med_card' id='med_card' value='1'".($post['med_card'] > 0  ? " checked" : "")."></td>
     				<td valign='top'><label for='med_card'>Med Card</label></td>
     				<td valign='top' colspan='2'><input type='text' name='med_card_info' id='med_card_info' value=\"".($post['med_card_info'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['med_card_info'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='driver_license' id='driver_license' value='1'".($post['driver_license'] > 0  ? " checked" : "")."></td>
     				<td valign='top'><label for='driver_license'>Drivers License</label></td>
     				<td valign='top' colspan='2'><input type='text' name='driver_license_num' id='driver_license_num' value=\"".$post['driver_license_num']."\" style='width:".$text_width."px;' placeholder='".$dl_num."'></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='ssn_copy' id='ssn_copy' value='1'".($post['ssn_copy'] > 0  ? " checked" : "")."></td>
     				<td valign='top'><label for='ssn_copy'>Social Security Card</label></td>
     				<td valign='top' colspan='2'><input type='text' name='ssn_num' id='ssn_num' value=\"".$post['ssn_num']."\" style='width:".$text_width."px;'></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>17</td>
     				<td valign='top'><input type='checkbox' name='driver_point_system' id='driver_point_system' value='1'".($post['driver_point_system'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_point_system'>Driver Point System</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>18</td>
     				<td valign='top'><input type='checkbox' name='camera_acknowledged' id='camera_acknowledged' value='1'".($post['camera_acknowledged'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='camera_acknowledged'>Camera in truck acknowledgement</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>19</td>
     				<td valign='top'><input type='checkbox' name='bio_page' id='bio_page' value='1'".($post['bio_page'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='bio_page'>We want to know more about you page</label></td>
     			</tr>
     			<tr>
     				<td valign='top' colspan='6'>&nbsp;<br>&nbsp;</td>
     			</tr>
     		";
     	}
     	else
     	{	//newer form version...as of 7/20/2017 12PM
     		$tab.="
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>1</td>
     				<td valign='top'><input type='checkbox' name='master_info_sheet' id='master_info_sheet' value='1'".($post['master_info_sheet'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='master_info_sheet'>Driver Master Information sheet</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>2</td>
     				<td valign='top'><input type='checkbox' name='mandatory_psp' id='mandatory_psp' value='1'".($post['mandatory_psp'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='mandatory_psp'>Mandatory Consent & release PSP</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' align='right'>A</td>
     				<td valign='top'><input type='checkbox' name='psp_report' id='psp_report' value='1'".($post['psp_report'] > 0  ? " checked" : "")."></td>			
     				<td valign='top' colspan='3'><label for='psp_report'>PSP REPORT</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>3</td>
     				<td valign='top'><input type='checkbox' name='driving_record_auth' id='driving_record_auth' value='1'".($post['driving_record_auth'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='driving_record_auth'>Authorization for Check of Driving Record</label></td>
     				<td valign='top'>(49 CFR 391.23)</td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' align='right'>A</td>
     				<td valign='top'><input type='checkbox' name='mvr_report' id='mvr_report' value='1'".($post['mvr_report'] > 0  ? " checked" : "")."></td>				
     				<td valign='top' colspan='3'><label for='mvr_report'>MVR report</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>4</td>
     				<td valign='top'><input type='checkbox' name='drug_test_consent' id='drug_test_consent' value='1'".($post['drug_test_consent'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='drug_test_consent'>Drug Test Consent Form</label></td>
     				<td valign='top'>(49 CFR 391.21)</td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='pre_employ_drug_screen' id='pre_employ_drug_screen' value='1'".($post['pre_employ_drug_screen'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='pre_employ_drug_screen'>Pre-Employment Drug Screening (send for Test)</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='pre_employ_drug_result' id='pre_employ_drug_result' value='1'".($post['pre_employ_drug_result'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='pre_employ_drug_result'>Pre-Employment Drug Screen Results</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>5</td>
     				<td valign='top'><input type='checkbox' name='controlled_sub_abuse' id='controlled_sub_abuse' value='1'".($post['controlled_sub_abuse'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='controlled_sub_abuse'>Controlled Substance Abuse and Alcohol Questioniar</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>6</td>
     				<td valign='top'><input type='checkbox' name='employee_application' id='employee_application' value='1'".($post['employee_application'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='employee_application'>Employee Application</label></td>
     				
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>7</td>
     				<td valign='top'><input type='checkbox' name='driver_data_sheet' id='driver_data_sheet' value='1'".($post['driver_data_sheet'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='driver_data_sheet'>Driver Data Sheet</label></td>
     				<td valign='top'>(49 CFR 395.8)</td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>8</td>
     				<td valign='top'><input type='checkbox' name='amvdcv_done' id='amvdcv_done' value='1'".($post['amvdcv_done'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='amvdcv_done'>Annual motor Vehicle Drivers Certification of Violations</label></td>
     				<td valign='top'>(49 CFR 391.27)</td>
     			</tr>     			
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>9</td>
     				<td valign='top'><input type='checkbox' name='employee_loan' id='employee_loan' value='1'".($post['employee_loan'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='employee_loan'>Previous Employment Verification</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>10</td>
     				<td valign='top'><input type='checkbox' name='new_hire_pay_info' id='new_hire_pay_info' value='1'".($post['new_hire_pay_info'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='new_hire_pay_info'>New Hire Pay Information</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='form_1_9' id='form_1_9' value='1'".($post['form_1_9'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='form_1_9'>I-9 FORM</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='form_w_4' id='form_w_4' value='1'".($post['form_w_4'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='form_w_4'>W-4</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>11</td>
     				<td valign='top'><input type='checkbox' name='wage_deduct_policy' id='wage_deduct_policy' value='1'".($post['wage_deduct_policy'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='wage_deduct_policy'>Conard Wage deduction policy</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>12</td>
     				<td valign='top'><input type='checkbox' name='direct_deposit' id='direct_deposit' value='1'".($post['direct_deposit'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='direct_deposit'>Direct Deposit</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>13</td>
     				<td valign='top'><input type='checkbox' name='aflac_page' id='aflac_page' value='1'".($post['aflac_page'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='aflac_page'>AFLAC PAGE</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>14</td>
     				<td valign='top'><input type='checkbox' name='med_exam_cert' id='med_exam_cert' value='1'".($post['med_exam_cert'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='med_exam_cert'>Medical Exam Report & Certification</td>
     				<td valign='top'>(49 CFR 391.43 & 391.49)</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='med_exam_form' id='med_exam_form' value='1'".($post['med_exam_form'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='med_exam_form'>Medical Exam Long Form</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='med_card' id='med_card' value='1'".($post['med_card'] > 0  ? " checked" : "")."></td>
     				<td valign='top'><label for='med_card'>Med Card</label></td>
     				<td valign='top' colspan='2'><input type='text' name='med_card_info' id='med_card_info' value=\"".($post['med_card_info'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['med_card_info'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='driver_license' id='driver_license' value='1'".($post['driver_license'] > 0  ? " checked" : "")."></td>
     				<td valign='top'><label for='driver_license'>Drivers License</label></td>
     				<td valign='top' colspan='2'><input type='text' name='driver_license_num' id='driver_license_num' value=\"".$post['driver_license_num']."\" style='width:".$text_width."px;' placeholder='".$dl_num."'></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='ssn_copy' id='ssn_copy' value='1'".($post['ssn_copy'] > 0  ? " checked" : "")."></td>
     				<td valign='top'><label for='ssn_copy'>Social Security Card</label></td>
     				<td valign='top' colspan='2'><input type='text' name='ssn_num' id='ssn_num' value=\"".$post['ssn_num']."\" style='width:".$text_width."px;'></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>15</td>
     				<td valign='top'><input type='checkbox' name='camera_acknowledged' id='camera_acknowledged' value='1'".($post['camera_acknowledged'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='camera_acknowledged'>Camera in truck acknowledgement</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>16</td>
     				<td valign='top'><input type='checkbox' name='bio_page' id='bio_page' value='1'".($post['bio_page'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='bio_page'>Important Information</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>17</td>
     				<td valign='top'><input type='checkbox' name='road_test' id='road_test' value='1'".($post['road_test'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='3'><label for='road_test'>Road Test</label></td>
     				<td valign='top'>(49 CFR 391.31)</td>
     			</tr> 
     			<tr>
     				<td valign='top' colspan='6'>
     					&nbsp;<br>&nbsp;   
     					<input type='hidden' name='driver_point_system' id='driver_point_system' value='".$post['driver_point_system']."'>
     				</td>
     			</tr>
     			";    			
     			
     		//not used in new form... retaining for old versions or to use a different way.  Loan reused as Prev Employ Verification.  Points System input hidden above.
     		$tab2="    			    			
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>9</td>
     				<td valign='top'><input type='checkbox' name='employee_loan' id='employee_loan' value='1'".($post['employee_loan'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='employee_loan'>Employee loan agreement</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>17</td>
     				<td valign='top'><input type='checkbox' name='driver_point_system' id='driver_point_system' value='1'".($post['driver_point_system'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_point_system'>Driver Point System</label></td>
     			</tr>     			
     		";	
     	}
				
		//employer section
		$tab.="
			<tr>
				<td valign='top'></td>
				<td valign='top' colspan='2'><b>Inquiry to Previous Employers</b></td>
				<td valign='top'><b>(49 CFR 391.23)</b></td>
				<td valign='top'>Date</td>
				<td valign='top'>Received</td>
			</tr>			
			<tr>
				<td valign='top'></td>
				<td valign='top'></td>
				<td valign='top'>Sent</td>
				<td valign='top'>Sent</td>
				<td valign='top'>Sent</td>
				<td valign='top'></td>
			</tr>			
			<tr style='background-color:#eeeeee;'>
				<td valign='top' align='right'>Employer</td>
				<td valign='top'><input type='text' name='employer_1' id='employer_1' value=\"".$post['employer_1']."\" style='width:".$text_width."px;'></td>
				<td valign='top'><input type='text' name='emp_1_sent_1' id='emp_1_sent_1' value=\"".($post['emp_1_sent_1'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_1_sent_1'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_1_sent_2' id='emp_1_sent_2' value=\"".($post['emp_1_sent_2'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_1_sent_2'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_1_sent_3' id='emp_1_sent_3' value=\"".($post['emp_1_sent_3'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_1_sent_3'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_1_received' id='emp_1_received' value=\"".($post['emp_1_received'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_1_received'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
			</tr>
			<tr style='background-color:#dddddd;'>
				<td valign='top' align='right'>Employer</td>
				<td valign='top'><input type='text' name='employer_2' id='employer_2' value=\"".$post['employer_2']."\" style='width:".$text_width."px;'></td>
				<td valign='top'><input type='text' name='emp_2_sent_1' id='emp_2_sent_1' value=\"".($post['emp_2_sent_1'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_2_sent_1'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_2_sent_2' id='emp_2_sent_2' value=\"".($post['emp_2_sent_2'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_2_sent_2'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_2_sent_3' id='emp_2_sent_3' value=\"".($post['emp_2_sent_3'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_2_sent_3'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_2_received' id='emp_2_received' value=\"".($post['emp_2_received'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_2_received'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
			</tr>
			<tr style='background-color:#eeeeee;'>
				<td valign='top' align='right'>Employer</td>
				<td valign='top'><input type='text' name='employer_3' id='employer_3' value=\"".$post['employer_3']."\" style='width:".$text_width."px;'></td>
				<td valign='top'><input type='text' name='emp_3_sent_1' id='emp_3_sent_1' value=\"".($post['emp_3_sent_1'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_3_sent_1'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_3_sent_2' id='emp_3_sent_2' value=\"".($post['emp_3_sent_2'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_3_sent_2'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_3_sent_3' id='emp_3_sent_3' value=\"".($post['emp_3_sent_3'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_3_sent_3'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_3_received' id='emp_3_received' value=\"".($post['emp_3_received'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_3_received'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
			</tr>
			<tr style='background-color:#dddddd;'>
				<td valign='top' align='right'>Employer</td>
				<td valign='top'><input type='text' name='employer_4' id='employer_4' value=\"".$post['employer_4']."\" style='width:".$text_width."px;'></td>
				<td valign='top'><input type='text' name='emp_4_sent_1' id='emp_4_sent_1' value=\"".($post['emp_4_sent_1'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_4_sent_1'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_4_sent_2' id='emp_4_sent_2' value=\"".($post['emp_4_sent_2'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_4_sent_2'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_4_sent_3' id='emp_4_sent_3' value=\"".($post['emp_4_sent_3'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_4_sent_3'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
				<td valign='top'><input type='text' name='emp_4_received' id='emp_4_received' value=\"".($post['emp_4_received'] !="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($post['emp_4_received'])) : "")."\" style='width:".$date_width."px;' class='mrr_datepicker'></td>
			</tr>
			<tr>
				<td valign='top' colspan='6'>&nbsp;<br>&nbsp;</td>
			</tr>
		";
			
		//final section	
		if($post['id'] > 0 && $post['id']<=$version1)
		{	//older form				
     		$tab.="	
     			<tr>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' colspan='4'><b>Orientation Check list</b></td>
     			</tr>		
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>20</td>
     				<td valign='top'><input type='checkbox' name='driver_safety_policy' id='driver_safety_policy' value='1'".($post['driver_safety_policy'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_safety_policy'>Driver Safety Policy Manual</label></td>
     			</tr>	
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='meet_and_greet' id='meet_and_greet' value='1'".($post['meet_and_greet'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='meet_and_greet'>Meet & Greet</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='fuel_card' id='fuel_card' value='1'".($post['fuel_card'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='fuel_card'>Fuel Card assigned and discussion of where we fuel</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='peoplenet_training' id='peoplenet_training' value='1'".($post['peoplenet_training'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='peoplenet_training'>GeoTab training and set up in system</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>21</td>
     				<td valign='top'><input type='checkbox' name='phone_list' id='phone_list' value='1'".($post['phone_list'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='phone_list'>List of phone numbers and after hours</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='after_hours' id='after_hours' value='1'".($post['after_hours'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='after_hours'>Hours of operations for driver what to do after hours</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='assign_truck' id='assign_truck' value='1'".($post['assign_truck'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='assign_truck'>Assign truck</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='employee_handbook' id='employee_handbook' value='1'".($post['employee_handbook'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='employee_handbook'>Provide employee hand book go over and signed</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'><input type='checkbox' name='pre_post_trips' id='pre_post_trips' value='1'".($post['pre_post_trips'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='pre_post_trips'>Pre and Post trips</label></td>
     			</tr>
     			<tr>
     				<td valign='top' colspan='6'>
     					&nbsp;<br>&nbsp;
     					<input type='hidden' name='driver_photo' id='driver_photo' value='".$post['driver_photo']."'>
     					<input type='hidden' name='driver_door_code' id='driver_door_code' value='".$post['driver_door_code']."'>
     					<input type='hidden' name='driver_box_setup' id='driver_box_setup' value='".$post['driver_box_setup']."'>
     					<input type='hidden' name='driver_should_haves' id='driver_should_haves' value='".$post['driver_should_haves']."'>
     					<input type='hidden' name='driver_fleet_one' id='driver_fleet_one' value='".$post['driver_fleet_one']."'>
     					<input type='hidden' name='driver_add_pn' id='driver_add_pn' value='".$post['driver_add_pn']."'>
     					<input type='hidden' name='driver_key_tags' id='driver_key_tags' value='".$post['driver_key_tags']."'>
     					<input type='hidden' name='driver_speed_space' id='driver_speed_space' value='".$post['driver_speed_space']."'>
     				</td>
     			</tr>		
     		";	//new items added after this point... added as blank entries that are hidden.
		}
		else
		{	//newer version... as of 07/20/2017
			$tab.="	
     			<tr>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' colspan='4'><b>Orientation Check list</b></td>
     			</tr>		
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>18</td>
     				<td valign='top'><input type='checkbox' name='driver_safety_policy' id='driver_safety_policy' value='1'".($post['driver_safety_policy'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_safety_policy'>Driver Safety Policy Manual</label></td>
     			</tr>	
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>19</td>
     				<td valign='top'><input type='checkbox' name='meet_and_greet' id='meet_and_greet' value='1'".($post['meet_and_greet'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='meet_and_greet'>Meet & Greet</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>20</td>
     				<td valign='top'><input type='checkbox' name='fuel_card' id='fuel_card' value='1'".($post['fuel_card'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='fuel_card'>Fuel Card assigned and discussion of where we fuel</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>21</td>
     				<td valign='top'><input type='checkbox' name='peoplenet_training' id='peoplenet_training' value='1'".($post['peoplenet_training'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='peoplenet_training'>GeoTab training and set up in system</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>22</td>
     				<td valign='top'><input type='checkbox' name='phone_list' id='phone_list' value='1'".($post['phone_list'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='phone_list'>List of phone numbers and after hours</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>23</td>
     				<td valign='top'><input type='checkbox' name='after_hours' id='after_hours' value='1'".($post['after_hours'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='after_hours'>Hours of operations for driver what to do after hours</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>24</td>
     				<td valign='top'><input type='checkbox' name='assign_truck' id='assign_truck' value='1'".($post['assign_truck'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='assign_truck'>Assign truck</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>25</td>
     				<td valign='top'><input type='checkbox' name='employee_handbook' id='employee_handbook' value='1'".($post['employee_handbook'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='employee_handbook'>Provide employee hand book go over and signed</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>26</td>
     				<td valign='top'><input type='checkbox' name='pre_post_trips' id='pre_post_trips' value='1'".($post['pre_post_trips'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='pre_post_trips'>Pre and Post trips</label></td>
     			</tr>
     			
     			
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>27</td>
     				<td valign='top'><input type='checkbox' name='driver_photo' id='driver_photo' value='1'".($post['driver_photo'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_photo'>Driver Photo</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>28</td>
     				<td valign='top'><input type='checkbox' name='driver_door_code' id='driver_door_code' value='1'".($post['driver_door_code'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_door_code'>Code for Driver Door</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>29</td>
     				<td valign='top'><input type='checkbox' name='driver_box_setup' id='driver_box_setup' value='1'".($post['driver_box_setup'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_box_setup'>Set Up box</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>30</td>
     				<td valign='top'><input type='checkbox' name='driver_should_haves' id='driver_should_haves' value='1'".($post['driver_should_haves'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_should_haves'>Go over what all Should be in Truck</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>31</td>
     				<td valign='top'><input type='checkbox' name='driver_fleet_one' id='driver_fleet_one' value='1'".($post['driver_fleet_one'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_fleet_one'>Q-Checks</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>32</td>
     				<td valign='top'><input type='checkbox' name='driver_add_pn' id='driver_add_pn' value='1'".($post['driver_add_pn'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_add_pn'>Add Driver to Peoplenet</label></td>
     			</tr>
     			<tr style='background-color:#dddddd;'>
     				<td valign='top' align='right'>33</td>
     				<td valign='top'><input type='checkbox' name='driver_key_tags' id='driver_key_tags' value='1'".($post['driver_key_tags'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_key_tags'>Key Tag Labels</label></td>
     			</tr>
     			<tr style='background-color:#eeeeee;'>
     				<td valign='top' align='right'>34</td>
     				<td valign='top'><input type='checkbox' name='driver_speed_space' id='driver_speed_space' value='1'".($post['driver_speed_space'] > 0  ? " checked" : "")."></td>
     				<td valign='top' colspan='4'><label for='driver_speed_space'>Speed and Space Training</label></td>
     			</tr>
     			
     			<tr>
     				<td valign='top' colspan='6'>&nbsp;<br>&nbsp;</td>
     			</tr>		
     		";
		}
		
		
		if($id==0)
		{
			$tab.="
				<tr>
					<td valign='top' colspan='6' align='center'>
						<input type='submit' name='save_checklist' id='save_checklist' value='Save DOT Checklist'>
					</td>
				</tr>
			";	
		}
		else
		{
			$tab.="
				<tr>
					<td valign='top' colspan='6' align='center'>
						<b>Archived Version of Driver DOT Checklist.  Update the current Checklist to make changes.</b>
					</td>
				</tr>
			";	
		}
		
		$tab.="</table>";
		return $tab;		
	}
	function mrr_clear_driver_dot_checklist($id)
	{
		if($id > 0)
		{
			$sql="
				update driver_dot_checklist set
					deleted=1
				where id='".sql_friendly($id)."' and newer_record=0
			";
			simple_query($sql);		
		}
	}
	function mrr_add_driver_dot_checklist($driver_id,$post)
	{
		global $datasource;

		$id=0;
		if($driver_id>0)	
		{
			$sql="
				insert into driver_dot_checklist
					(id,driver_id,user_id,deleted,linedate_added,newer_record)
				values
					(NULL,'".sql_friendly($driver_id)."','".sql_friendly($_SESSION['user_id'])."',0,NOW(),1)
			";
			simple_query($sql);
			$id=mysqli_insert_id($datasource);
			
			if($id > 0)
			{
				//update the record
				$sql="
					update driver_dot_checklist set
						
						reviewed_user_id='".sql_friendly($post['reviewed_user_id'])."',
						received_user_id='".sql_friendly($post['received_user_id'])."',
						
						".($post['linedate_reviewed']!="" && $post['linedate_reviewed']!="0000-00-00 00:00:00" ? "linedate_reviewed='".date("Y-m-d",strtotime($post['linedate_reviewed']))." 00:00:00'" : "linedate_reviewed='0000-00-00 00:00:00'").",
                              ".($post['linedate_received']!="" && $post['linedate_received']!="0000-00-00 00:00:00" ? "linedate_received='".date("Y-m-d",strtotime($post['linedate_received']))." 00:00:00'" : "linedate_received='0000-00-00 00:00:00'").",
                              
                              master_info_sheet='".sql_friendly($post['master_info_sheet'])."',
                              mandatory_psp='".sql_friendly($post['mandatory_psp'])."',
                              psp_report='".sql_friendly($post['psp_report'])."',
                              driving_record_auth='".sql_friendly($post['driving_record_auth'])."',
                              mvr_report='".sql_friendly($post['mvr_report'])."',
                              employee_application='".sql_friendly($post['employee_application'])."',
                              driver_data_sheet='".sql_friendly($post['driver_data_sheet'])."',
                              amvdcv_done='".sql_friendly($post['amvdcv_done'])."',
                              road_test='".sql_friendly($post['road_test'])."',
                              drug_test_consent='".sql_friendly($post['drug_test_consent'])."',
                              pre_employ_drug_screen='".sql_friendly($post['pre_employ_drug_screen'])."',
                              pre_employ_drug_result='".sql_friendly($post['pre_employ_drug_result'])."',
                              employee_loan='".sql_friendly($post['employee_loan'])."',
                              controlled_sub_abuse='".sql_friendly($post['controlled_sub_abuse'])."',
                              form_1_9='".sql_friendly($post['form_1_9'])."',
                              form_w_4='".sql_friendly($post['form_w_4'])."',
                              new_hire_pay_info='".sql_friendly($post['new_hire_pay_info'])."',
                              wage_deduct_policy='".sql_friendly($post['wage_deduct_policy'])."',
                              direct_deposit='".sql_friendly($post['direct_deposit'])."',
                              aflac_page='".sql_friendly($post['aflac_page'])."',
                              med_exam_cert='".sql_friendly($post['med_exam_cert'])."',
                              med_exam_form='".sql_friendly($post['med_exam_form'])."',
                              med_card='".sql_friendly($post['med_card'])."',
                              med_card_info='".sql_friendly(trim($post['med_card_info']))."',
                              driver_license='".sql_friendly($post['driver_license'])."',
                              driver_license_num='".sql_friendly(trim($post['driver_license_num']))."',
                              ssn_copy='".sql_friendly($post['ssn_copy'])."',
                              ssn_num='".sql_friendly(trim($post['ssn_num']))."',  
                              driver_point_system='".sql_friendly($post['driver_point_system'])."',
                              camera_acknowledged='".sql_friendly($post['camera_acknowledged'])."',
                              bio_page='".sql_friendly($post['bio_page'])."',
                              driver_safety_policy='".sql_friendly($post['driver_safety_policy'])."',
                              meet_and_greet='".sql_friendly($post['meet_and_greet'])."',
                              fuel_card='".sql_friendly($post['fuel_card'])."',
                              peoplenet_training='".sql_friendly($post['peoplenet_training'])."',
                              phone_list='".sql_friendly($post['phone_list'])."',
                              after_hours='".sql_friendly($post['after_hours'])."',
                              assign_truck='".sql_friendly($post['assign_truck'])."',
                              employee_handbook='".sql_friendly($post['employee_handbook'])."',
                              pre_post_trips='".sql_friendly($post['pre_post_trips'])."',
                              
                              employer_1='".sql_friendly(trim($post['employer_1']))."',
                              employer_2='".sql_friendly(trim($post['employer_2']))."',
                              employer_3='".sql_friendly(trim($post['employer_3']))."',
                              employer_4='".sql_friendly(trim($post['employer_4']))."',                              
                              ".($post['emp_1_sent_1']!="" && $post['emp_1_sent_1']!="0000-00-00 00:00:00" ? "emp_1_sent_1='".date("Y-m-d",strtotime($post['emp_1_sent_1']))." 00:00:00'" : "emp_1_sent_1='0000-00-00 00:00:00'").",
                              ".($post['emp_1_sent_2']!="" && $post['emp_1_sent_2']!="0000-00-00 00:00:00" ? "emp_1_sent_2='".date("Y-m-d",strtotime($post['emp_1_sent_2']))." 00:00:00'" : "emp_1_sent_2='0000-00-00 00:00:00'").",
                              ".($post['emp_1_sent_3']!="" && $post['emp_1_sent_3']!="0000-00-00 00:00:00" ? "emp_1_sent_3='".date("Y-m-d",strtotime($post['emp_1_sent_3']))." 00:00:00'" : "emp_1_sent_3='0000-00-00 00:00:00'").",
                              ".($post['emp_1_received']!="" && $post['emp_1_received']!="0000-00-00 00:00:00" ? "emp_1_received='".date("Y-m-d",strtotime($post['emp_1_received']))." 00:00:00'" : "emp_1_received='0000-00-00 00:00:00'").",
                              ".($post['emp_2_sent_1']!="" && $post['emp_2_sent_1']!="0000-00-00 00:00:00" ? "emp_2_sent_1='".date("Y-m-d",strtotime($post['emp_2_sent_1']))." 00:00:00'" : "emp_2_sent_1='0000-00-00 00:00:00'").",
                              ".($post['emp_2_sent_2']!="" && $post['emp_2_sent_2']!="0000-00-00 00:00:00" ? "emp_2_sent_2='".date("Y-m-d",strtotime($post['emp_2_sent_2']))." 00:00:00'" : "emp_2_sent_2='0000-00-00 00:00:00'").",
                              ".($post['emp_2_sent_3']!="" && $post['emp_2_sent_3']!="0000-00-00 00:00:00" ? "emp_2_sent_3='".date("Y-m-d",strtotime($post['emp_2_sent_3']))." 00:00:00'" : "emp_2_sent_3='0000-00-00 00:00:00'").",
                              ".($post['emp_2_received']!="" && $post['emp_2_received']!="0000-00-00 00:00:00" ? "emp_2_received='".date("Y-m-d",strtotime($post['emp_2_received']))." 00:00:00'" : "emp_2_received='0000-00-00 00:00:00'").",
                              ".($post['emp_3_sent_1']!="" && $post['emp_3_sent_1']!="0000-00-00 00:00:00" ? "emp_3_sent_1='".date("Y-m-d",strtotime($post['emp_3_sent_1']))." 00:00:00'" : "emp_3_sent_1='0000-00-00 00:00:00'").",
                              ".($post['emp_3_sent_2']!="" && $post['emp_3_sent_2']!="0000-00-00 00:00:00" ? "emp_3_sent_2='".date("Y-m-d",strtotime($post['emp_3_sent_2']))." 00:00:00'" : "emp_3_sent_2='0000-00-00 00:00:00'").",
                              ".($post['emp_3_sent_3']!="" && $post['emp_3_sent_3']!="0000-00-00 00:00:00" ? "emp_3_sent_3='".date("Y-m-d",strtotime($post['emp_3_sent_3']))." 00:00:00'" : "emp_3_sent_3='0000-00-00 00:00:00'").",
                              ".($post['emp_3_received']!="" && $post['emp_3_received']!="0000-00-00 00:00:00" ? "emp_3_received='".date("Y-m-d",strtotime($post['emp_3_received']))." 00:00:00'" : "emp_3_received='0000-00-00 00:00:00'").",
                              ".($post['emp_4_sent_1']!="" && $post['emp_3_sent_1']!="0000-00-00 00:00:00" ? "emp_4_sent_1='".date("Y-m-d",strtotime($post['emp_4_sent_1']))." 00:00:00'" : "emp_4_sent_1='0000-00-00 00:00:00'").",
                              ".($post['emp_4_sent_2']!="" && $post['emp_4_sent_2']!="0000-00-00 00:00:00" ? "emp_4_sent_2='".date("Y-m-d",strtotime($post['emp_4_sent_2']))." 00:00:00'" : "emp_4_sent_2='0000-00-00 00:00:00'").",
                              ".($post['emp_4_sent_3']!="" && $post['emp_4_sent_3']!="0000-00-00 00:00:00" ? "emp_4_sent_3='".date("Y-m-d",strtotime($post['emp_4_sent_3']))." 00:00:00'" : "emp_4_sent_3='0000-00-00 00:00:00'").",
                              ".($post['emp_4_received']!="" && $post['emp_4_received']!="0000-00-00 00:00:00" ? "emp_4_received='".date("Y-m-d",strtotime($post['emp_4_received']))." 00:00:00'" : "emp_4_received='0000-00-00 00:00:00'").",
						
						driver_photo='".sql_friendly($post['driver_photo'])."',
						driver_door_code='".sql_friendly($post['driver_door_code'])."',
						driver_box_setup='".sql_friendly($post['driver_box_setup'])."',
						driver_should_haves='".sql_friendly($post['driver_should_haves'])."',
						driver_fleet_one='".sql_friendly($post['driver_fleet_one'])."',
						driver_add_pn='".sql_friendly($post['driver_add_pn'])."',
						driver_key_tags='".sql_friendly($post['driver_key_tags'])."',
						driver_speed_space='".sql_friendly($post['driver_speed_space'])."',
											
						newer_record=1
					where id='".sql_friendly($id)."'
				";
				simple_query($sql);	
				
				//archive other records for this driver.
				$sql="
					update driver_dot_checklist set
						newer_record=0
					where driver_id='".sql_friendly($driver_id)."' and id!='".sql_friendly($id)."'
				";
				simple_query($sql);
			}
		}
		return $id;
	}
	
	
	
	function mrr_find_truck_has_more_loads($load_id,$truck_id,$driver_id,$start_date="")
	{
		$loads=0;
		if($truck_id > 0 && trim($start_date)!="")
		{
			$sql="
				select count(*) as my_cnt
               	from trucks_log
               	where deleted=0               		
               		and truck_id='".sql_friendly($truck_id)."'
               		and load_handler_id!='".sql_friendly($load_id)."'
               		and linedate_pickup_eta >= '".date("Y-m-d", strtotime($start_date))." 00:00:00'
			";                                      // H:i:s
			$data=simple_query($sql);
			if($row = mysqli_fetch_array($data)) 
			{
				if($row['my_cnt'] > 0)	$loads++;	
			}
		}	
		//now look for preplanned loads
		if($driver_id>0 && trim($start_date)!="")
		{
			$sql="
				select count(*) as my_cnt
               	from load_handler
               		left join drivers on load_handler.preplan_driver_id=drivers.id
               	where load_handler.deleted=0
               		and load_handler.preplan > 0
               		and load_handler.preplan_driver_id ='".sql_friendly($driver_id)."'
               		and load_handler.id!='".sql_friendly($load_id)."'
               		and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($start_date))." 00:00:00'
			";                                                          // H:i:s
			$data=simple_query($sql);
			if($row = mysqli_fetch_array($data)) 
			{
				$loads+=$row['my_cnt'];	
			}
		}		
		return $loads;	
	}
	
	function mrr_lockdown_all_truck_trailers_for_urgent_maint($mode=0)
	{
		$rep="";
		if($mode==0 || $mode==1)
		{	//URGETN truck maintenance
			$rep.="TRUCKS:<br>";
			//unset them all.
			$sql="update trucks set maint_req_lock=0";			// and id!=582
			simple_query($sql);
			
			$sql="				
				select ref_id,urgent
				from maint_requests 
				where (equip_type=1 or equip_type=58) 
					and ref_id>0 
					and deleted=0 
					and linedate_completed='0000-00-00 00:00:00' 
				order by id asc
			";
			$data=simple_query($sql);
			while($row = mysqli_fetch_array($data)) 
			{
				$unit_id=$row['ref_id'];	
				$resetter=0;
				
				if($row['urgent'] > 0)
				{				
					$sqlu="update trucks set maint_req_lock=0 where id ='".sql_friendly($unit_id)."'";
					simple_query($sqlu);
					
					$resetter=1;
				}
				$rep.="Maint Lock Check on Truck ID ".$unit_id.".".($resetter > 0 ? " -- SET!" : "")."<br>";
			}
		}
		if($mode==0 || $mode==2)
		{	//URGETN trailer maintenance
			$rep.="TRAILERS:<br>";
			//unset them all.
			$sql="update trailers set maint_req_lockdown=0";		// and id!=425
			simple_query($sql);
			
			$sql="				
				select ref_id,urgent
				from maint_requests 
				where (equip_type=2 or equip_type=59) 
					and ref_id>0 
					and deleted=0 
					and linedate_completed='0000-00-00 00:00:00' 
				order by id asc
			";
			$data=simple_query($sql);
			while($row = mysqli_fetch_array($data)) 
			{
				$unit_id=$row['ref_id'];	
				$resetter=0;
				
				if($row['urgent'] > 0)
				{				
					$sqlu="update trailers set maint_req_lockdown=0 where id ='".sql_friendly($unit_id)."'";
					simple_query($sqlu);
					
					$resetter=1;
				}
				$rep.="Maint Lock Check on Trailer ID ".$unit_id.".".($resetter > 0 ? " -- SET!" : "")."<br>";
			}
		}
		return $rep;
	}
	function mrr_auto_create_maint_request_for_pm_fed_oil_valve($truck_id,$trailer_id,$pm=0,$fed=0,$oil=0,$valve=0,$pmtxt="",$fedtxt="",$oiltxt="",$valvetxt="",$drain=0,$draintxt="")
	{	
		global $datasource;

		//tries to make a MR if the unit does not already have one for that unit for one of the three (trailer) or four (truck) notices.		
		$res="";
		
		$pm_flag="";			if($pm==1)		$pm_flag="PMI Soon";				if($pm==2)		$pm_flag="PMI OVERDUE";	
		$fed_flag="";			if($fed==1)		$fed_flag="FED Soon";				if($fed==2)		$fed_flag="FED OVERDUE";	
		$oil_flag="";			if($oil==1)		$oil_flag="Oil Change Soon";			if($oil==2)		$oil_flag="Oil Change OVERDUE";	
		$valve_flag="";		if($valve==1)		$valve_flag="Valve Adjustment Soon";	if($valve==2)		$valve_flag="Valve Adjustment OVERDUE";
          $drain_flag="";		if($drain==1)		$drain_flag="Drain Separator Soon";	if($drain==2)		$drain_flag="Drain Separator OVERDUE";
          
          $sql="				
			select *
			from maint_requests 
			where deleted=0
				and equip_type > 0
				and ref_id > 0
				".($truck_id > 0 ? 	 "and ref_id='".(int) $truck_id."' and (equip_type=1 or equip_type=58)  " : "")."		
				".($trailer_id > 0 ? "and ref_id='".(int) $trailer_id."' and (equip_type=2 or equip_type=59)  " : "")."	
				and linedate_completed='0000-00-00 00:00:00' 
				and active > 0
				and auto_created > 0
			order by id desc
		";
		$data=simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{	//Active MR already found.  Use this one, and add any sections now already present.
			$req_id=$row['id'];
			$desc=trim($row['maint_desc']);
			
			$res="<br><b>Found MR ID ".$row['id'].".</b>";
				
			$res.="<br>Desc: ".$desc."";
			
			//remove all the prefixes...so the ones used will be accurate.
			$desc=str_replace("AUTO - ","",$desc);					$desc=str_replace(" | ","",$desc);		
			$desc=str_replace("...","",$desc);						$desc=str_replace("  "," ",$desc);						$desc=str_replace("  "," ",$desc);
			$desc=str_replace("PMI Soon","",$desc);					$desc=str_replace("PMI OVERDUE","",$desc);
			$desc=str_replace("FED Soon","",$desc);					$desc=str_replace("FED OVERDUE","",$desc);
			$desc=str_replace("Oil Change Soon","",$desc);			$desc=str_replace("Oil Change OVERDUE","",$desc);
			$desc=str_replace("Valve Adjustment Soon","",$desc);		$desc=str_replace("Valve Adjustment OVERDUE","",$desc);
               $desc=str_replace("Drain Separator Soon","",$desc);		$desc=str_replace("Drain Separator OVERDUE","",$desc);
			
			//now add the prefix back to the MR description... so it is current.
			$prefix="AUTO - ";
			$appender="";
			$updater="";
			if($pm > 0)	
			{
				$desc=str_replace(trim($pmtxt),"",$desc);
				$desc=str_replace("PMI - ","",$desc);
				
				$prefix.="".$pm_flag." ";
				$appender.=" PMI - ".trim($pmtxt)."... ";
				if($pm!=$row['auto_pm'])			$updater.=",auto_pm='".(int) $pm."'";
			}
			if($fed > 0)
			{
				$desc=str_replace(trim($fedtxt),"",$desc);
				$desc=str_replace("FED - ","",$desc);
				
				$prefix.="".$fed_flag." ";
				$appender.=" FED - ".trim($fedtxt)."... ";
				if($fed!=$row['auto_fed'])		$updater.=",auto_fed='".(int) $fed."'";
			}
			if($oil > 0)
			{
				$desc=str_replace(trim($oiltxt),"",$desc);
				$desc=str_replace("Oil - ","",$desc);
				
				$prefix.="".$oil_flag." ";
				$appender.=" Oil - ".trim($oiltxt)."... ";
				if($oil!=$row['auto_oil'])		$updater.=",auto_oil='".(int) $oil."'";
			}
			if($valve > 0)
			{
				$desc=str_replace(trim($valvetxt),"",$desc);
				$desc=str_replace("Valve - ","",$desc);
				
				$prefix.="".$valve_flag." ";
				$appender.=" Valve - ".trim($valvetxt)."... ";
				if($valve!=$row['auto_valve'])	$updater.=",auto_valve='".(int) $valve."'";
			}
               if($drain > 0)
               {
                    $desc=str_replace(trim($draintxt),"",$desc);
                    $desc=str_replace("Drain  - ","",$desc);
                    
                    $prefix.="".$drain_flag." ";
                    $appender.=" Drain - ".trim($draintxt)."... ";
                    if($drain!=$row['auto_drain'])	$updater.=",auto_drain='".(int) $drain."'";
               }
			
			$request_description="".$prefix." | ".strip_tags($appender)."";       // | ".trim($desc)."
						
			$res.="<br>NewD: ".$request_description."";
						
			$sql = "
					update maint_requests set
						maint_desc='".sql_friendly( $request_description )."'
						".$updater."
						,active='1'					
					where id='".sql_friendly( $req_id )."'
				";				
			simple_query($sql);
			$res.="<br>SQL: ".$sql."<br>";			
		}
		else
		{	//not found, so make a new MR for this unit for this/these reason(s).
			$res="<br><b>MR NOT Found... Creating new one.</b>";
			
			$prefix="AUTO - ";
			$appender="";
			if($pm > 0)	
			{
				$prefix.="".$pm_flag." ";
				$appender.=" PMI - ".trim($pmtxt)."... ";
			}
			if($fed > 0)
			{
				$prefix.="".$fed_flag." ";
				$appender.=" FED - ".trim($fedtxt)."... ";
			}
			if($oil > 0)
			{
				$prefix.="".$oil_flag." ";
				$appender.=" Oil - ".trim($oiltxt)."... ";
			}
			if($valve > 0)
			{
				$prefix.="".$valve_flag." ";
				$appender.=" Valve - ".trim($valvetxt)."... ";
			}
               if($drain > 0)
               {
                    $prefix.="".$drain_flag." ";
                    $appender.=" Valve - ".trim($draintxt)."... ";
               }
			
			$request_description="".$prefix." | ".strip_tags($appender)."";
			
			$res.="<br>New Desc: ".$request_description."";
			
			$pm_odom=0;
			$etype=0;
			$ref_id=0;
			
			if($truck_id > 0)	
			{
				$etype=58;
				$ref_id=$truck_id;
				$pm_odom=mrr_fetch_last_PN_odometer_reading($truck_id);
			}
			if($trailer_id > 0)	
			{
				$etype=59;
				$ref_id=$trailer_id;
			}
			
			$sql = "
					insert into maint_requests
						(id,
						linedate_added,
						linedate_scheduled,
						maint_desc,
						recur_days,
						recur_mileage,
						recur_flag,
						recur_ref,
						urgent,	
						safety_shutdown,
						active,
						unit_breakdown,
						auto_created,
						auto_drain,
						auto_valve,
						auto_oil,
						auto_fed,
						auto_pm,
						deleted)
							
					values (NULL,
						NOW(),
						NOW(),
						'Auto MR...',
						0,
						0,
						0,
						0,
						0,
						0,
						1,
						0,
						1,
						0,
						0,
						0,
						0,
						0,
						0)
				";		
				
			simple_query($sql);
			$req_id=0;
			$req_id = mysqli_insert_id($datasource);				
			
			$res.="<br>SQL: ".$sql."<br>";
			
			$sql = "
					update maint_requests set
						user_id='1',
						odometer_reading='".sql_friendly( $pm_odom )."',
						equip_type='".sql_friendly( $etype )."',
						ref_id='".sql_friendly( $ref_id )."',
						down_time_hours='0.00',
						cost='0.00',
						maint_desc='".sql_friendly( $request_description )."',
						auto_pm='".sql_friendly($pm)."',
						auto_fed='".sql_friendly($fed)."',
						auto_oil='".sql_friendly($oil)."',
						auto_valve='".sql_friendly($valve)."',
						auto_drain='".sql_friendly($drain)."',
						auto_created='1'
					
					where id='".sql_friendly( $req_id )."'
				";		//linedate_scheduled='".sql_friendly($scheduled)."',
			
			simple_query($sql);
			
			$res.="<br><b>MR ID ".$req_id." Created.</b>";
			$res.="<br>SQL: ".$sql."<br>";
		}
		return $res;
	}
		
	
	//Driver Payroll function for OOIC drivers... Added 6/17/2019...MRR.
	function mrr_add_driver_ooic_payroll_emails($driver_id,$start,$end,$html,$total,$fuel,$exp,$insur)
	{
		$name="";
		$email="";
		
		$sql = "
			select *				
			from drivers
			where id='".sql_friendly($driver_id)."'	
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			$name=trim($row['name_driver_first'])." ".trim($row['name_driver_last']);
			$email=trim($row['driver_email']);
		}
		
		$sqlu = "
			update driver_ooic_payroll_emails set
				deleted='1'
			where deleted=0 
				and driver_id='".sql_friendly($driver_id)."' 
				and driver_email='".sql_friendly($email)."'
				and linedate_start='".date("Y-m-d",strtotime($start))." 00:00:00' 
				and linedate_end='".date("Y-m-d",strtotime($end))." 23:59:59'
		";
		simple_query($sqlu);
		
		$sql = "
			insert into driver_ooic_payroll_emails
				(id,
				linedate_added,
				linedate_start,
				linedate_end,
				driver_id,
				driver_email,
				driver_content,
				ooic_total,
				ooic_fuel,
				ooic_exp,
				ooic_insur,
				deleted)
					
			values (NULL,
				NOW(),
				'".date("Y-m-d",strtotime($start))." 00:00:00',
				'".date("Y-m-d",strtotime($end))." 23:59:59',
				'".sql_friendly( $driver_id )."',
				'".sql_friendly( $email )."',
				'".sql_friendly( $html )."',
				'".sql_friendly( $total )."',
				'".sql_friendly( $fuel )."',
				'".sql_friendly( $exp )."',
				'".sql_friendly( $insur )."',
				0)
		";						
		simple_query($sql);
	}
?>