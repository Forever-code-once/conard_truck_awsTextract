<?
$usetitle="Driver Safety Report Details";  
?>
<? include('header.php') ?>
<?
	if(isset($_GET['date_from']))	
	{
		$_GET['date_from']=str_replace("_","/",$_GET['date_from']);	
		$_POST['date_from']=$_GET['date_from'];
		
		$_POST['build_report']=1;
	}
	if(isset($_GET['date_to']))		
	{
		$_GET['date_to']=str_replace("_","/",$_GET['date_to']);
		$_POST['date_to']=$_GET['date_to'];
				
		$_POST['build_report']=1;
	}
	if(isset($_GET['driver_id']))		
	{
		$_POST['driver_id']=$_GET['driver_id'];
				
		$_POST['build_report']=1;
	}
	if(isset($_GET['truck_id']))		
	{
		$_POST['truck_id']=$_GET['truck_id'];
				
		$_POST['build_report']=1;
	}
	
	//if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	//if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("n/j/Y", time());
	
	/*
	if(isset($_GET['activate']))
	{
		$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			active='1'
		where id='".sql_friendly($_GET['activate'])."'	
		";	
		simple_query($sql);	
	}
	if(isset($_GET['deactivate']))
	{
		$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			active='0'
		where id='".sql_friendly($_GET['deactivate'])."'	
		";	
		simple_query($sql);	
	}
	*/
	if(isset($_GET['delid']))
	{
		$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			deleted='1'
		where id='".sql_friendly($_GET['delid'])."'	
		";	
		simple_query($sql);	 
	}	
?>


<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<?
		if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("m/d/Y");
		if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("m/d/Y");
		
		if($_POST['date_from']=="")		$_POST['date_from']="05/13/2013";
		if($_POST['date_to']=="")		$_POST['date_to']=date("m/d/Y");
		
		$early_notice="";
		if(date("m/d/Y",strtotime($_POST['date_from'])) <= "05/12/2013")		{	$_POST['date_from']="05/13/2013";		$early_notice="<b>Report not available before 05/12/2013.</b>";	}
		if(date("m/d/Y",strtotime($_POST['date_to'])) <= "05/12/2013")		{	$_POST['date_to']=date("m/d/Y");		$early_notice="<b>Report not available before 05/12/2013.</b>"; 	}
		     	    	
     	$rfilter = new report_filter();
     	$rfilter->show_driver 			= true;
     	$rfilter->show_employers 		= true;
     	//$rfilter->summary_only	 		= true;
     	//$rfilter->team_choice	 		= true;
     	//$rfilter->show_font_size			= true;
     	//$rfilter->mrr_special_print_button	= true;
     	$rfilter->show_filter();
     	
     ?>
	<h3><?= $usetitle ?>
	
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Show:
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(0);'>All</span>  				
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(1);'>Good Driving Only</span>    
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(2);'>Violations Only</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?= $_GET['date_from'] ?>   
	<?= $_GET['date_to'] ?>   
	<?= $early_notice ?>
	</h3>
	<br>
</div>
<form action='' method='post'>
<div style='clear:both'></div>
<div style='clear:both'></div>

<?
	$driver_id=0;		//281=John Baker  312=Grady Hough   342=Wesley Barlow (Teamed)
	$mrr_adder="";
     if($driver_id>0)	$mrr_adder=" and drivers.id='".sql_friendly($driver_id)."'";
	
	/*
	$sqld="
		select drivers.*,
			(select name_truck from trucks where trucks.id=drivers.attached_truck_id) as truck_name
		from drivers
		where drivers.active=1
			and drivers.deleted=0
			".$mrr_adder."
		order by drivers.name_driver_last asc,
			drivers.name_driver_first asc
	";
	*/
	
	$data_sorter="";
	
	//global $defaultsarray;
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
	
	
	$mrr_block=1;
	if(isset($_POST['build_report']) && $mrr_block==0)
	{
     	
     	
     	//get all active drivers
     	$sqld="
     		select drivers.*,
     			option_values.fvalue
     		from drivers
     			left join option_values on option_values.id=drivers.employer_id
     		where drivers.active=1
     			and drivers.deleted=0
     			".$mrr_adder."
     			".($_POST['employer_id'] > 0 && $_POST['employer_id']!="" ? " and drivers.employer_id='".sql_friendly($_POST['employer_id'])."'" : "")."
     			".($_POST['driver_id'] > 0 && $_POST['driver_id']!="" ? " and drivers.id='".sql_friendly($_POST['driver_id'])."'" : "")."
     		order by drivers.name_driver_last asc,
     			drivers.name_driver_first asc
     	";
     	$datad=simple_query($sqld);
     	while($rowd=mysqli_fetch_array($datad))
     	{  
     		//get every truck that this driver has used in date range
          	$sqlt="
          		select distinct trucks_log.truck_id,
          			trucks_log.driver_id,
          			trucks_log.driver2_id,
          			(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_name
          		from trucks_log
          		where trucks_log.deleted=0
          			and (trucks_log.driver_id='".sql_friendly($rowd['id'])."' or trucks_log.driver2_id='".sql_friendly($rowd['id'])."')
          			and trucks_log.linedate_pickup_eta>='".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
          			and trucks_log.linedate_pickup_eta<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
          		order by trucks_log.linedate_pickup_eta asc
          	";
          	$datat=simple_query($sqlt);
          	$mn=mysqli_num_rows($datat);
          	
          	
          	$wk_hours_driven=0;
          	$wk_hours_worked=0;
          	$wk_hours_rested=0; 
          	$wk_last_time=0; 
          	$wk_start_time=0;
          	$wk_timer=0;
               $wk_stop_watch=0;
          	
          	$hours_driven=0;
          	$hours_worked=0;
          	$hours_rested=0; 
          	$last_time=0; 
          	$start_time=0;
          	$timer=0;
               $stop_watch=0;
          	
          	$last_latitude=0;    
          	$last_longitude=0; 	     	
          	$truck_moved=0;
          	$last_truck=0;
          	
          	$speeding_violations=0; 
          	$dot_violations=0; 
          	$time_violations=""; 
          	
          	$hr11_violations=0;
          	$hr14_violations=0;
          	$br10_violations=0;
          	$hr70_violations=0;
          	$br34_violations=0;
          	
          	
          	if($mn > 0)
          	{
               	$data_sorter.= "<div style='margin-left:20px;'><h3><a href='admin_drivers.php?id=".$rowd['id']."' target='_blank'>".$rowd['name_driver_first']." ".$rowd['name_driver_last']."</a>: ".$rowd['fvalue']."</h3></div>";//
          		   		
          		$data_sorter.=  "<table class='admin_menu1 tablesorter font_display_section' id='table_sort_".$rowd['id']."' style='text-align:left;width:1800px;margin:10px'>";
               	$data_sorter.= "<thead>
               			<tr>
               				<th><b>Date</b></th>
               				<th><b>Driver</b></th>
               				<th><b>Driver2</b></th>
               				<th><b>Truck</b></th>
               				<th><b>Ign</b></th>
               				<th><b>Hd</b></th>
               				<th><b>MPH</b></th>
               				<th><b>Sign</b></th>         				
               				<th><b>Lat</b></th>
               				<th><b>Long</b></th>
               				<th><b>Location</b></th>
               				<th><b>Dist(Mi)</b></th>
               				<th><b>Dist(Ft)</b></th>
               				<th><b>Time(Hrs)</b></th>
               				<th><b>StopWatch</b></th>
               				<th><b>HrsDriven</b></th>
               				<th><b>HrsWorked</b></th>
               				<th><b>HrsRest</b></th>
               				<th><b>Speeding</b></th>
               				<th><b>Notice</b></th>	
               				<th><b>WkTime(Hrs)</b></th>
               				<th><b>WkStopWatch</b></th>
               				<th><b>Moved</b></th>
               			</tr>
               		</thead>
               		<tbody>
               		";//<th><b>Fix</b></th>
               	          	
          		while($rowt=mysqli_fetch_array($datat))
          		{
               		//$truck_id=$rowd['attached_truck_id'];
                    	$truck_id=$rowt['truck_id']; 
                    	
                    	$prev_date=""; 
                    	$prev_lat=0;
                    	$prev_long=0;      	
                    	
                    	$mrr_adder1="";     	
                    	if($truck_id>0)	$mrr_adder1=" and truck_tracking.truck_id='".sql_friendly($truck_id)."'";
                    	
                    	$mrr_adder2="";
                    	//$mrr_adder2=" and (truck_tracking.driver_id='".sql_friendly($rowd['id'])."' or truck_tracking.driver2_id='".sql_friendly($rowd['id'])."')";
                    		
                    	//now get GPS points for this one...	
                    	$sql="
                    		select truck_tracking.*,
                    			(select name_driver_first from drivers where drivers.id=truck_tracking.driver_id) as driver_first_name,
                    			(select name_driver_last from drivers where drivers.id=truck_tracking.driver_id) as driver_last_name,
                    			(select name_driver_first from drivers where drivers.id=truck_tracking.driver2_id) as driver2_first_name,
                    			(select name_driver_last from drivers where drivers.id=truck_tracking.driver2_id) as driver2_last_name,
                    			(select name_truck from trucks where trucks.id=truck_tracking.truck_id) as truck_name
                    		from ".mrr_find_log_database_name()."truck_tracking
                    		where truck_tracking.linedate>='".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
                    			and truck_tracking.linedate<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
                    			".$mrr_adder1."
                    			".$mrr_adder2."
                    			and truck_tracking.safety_report_made=0
                    		order by truck_tracking.linedate asc,truck_tracking.id desc
                    		
                    	";	//limit 25
                    	$data=simple_query($sql);
                    	while($row=mysqli_fetch_array($data))
                    	{
                         	if($rowt['driver2_id']==$rowd['id'] && $row['driver2_id']==0)
                         	{
                         		$sqlu=" update ".mrr_find_log_database_name()."truck_tracking set driver2_id='".sql_friendly($rowd['id'])."' where id='".sql_friendly($rowd['id'])."'";
                         		simple_query($sqlu);	
                         	}
                         	elseif($rowt['driver_id']==$rowd['id'] && $row['driver_id']==0)
                         	{
                         		$sqlu=" update ".mrr_find_log_database_name()."truck_tracking set driver_id='".sql_friendly($rowd['id'])."' where id='".sql_friendly($rowd['id'])."'";
                         		simple_query($sqlu);		
                         	}              	
                         	
                         	
                         	if(date("m/d/Y H:i", strtotime($row['linedate'])) != $prev_date)
                         	{                    		                    		
                         		$gps_dist=0;    
                         		$prev_date=date("m/d/Y H:i", strtotime($row['linedate']));			//used to not show duplicate date points...caused from packets reloading...always use the last one.  Query above is sorted that way already.            		
                         		
                         		$time_violations="";
                         		
                         		if($last_truck==0)
                         		{
                         			$wk_last_time=strtotime($row['linedate']);
               					$wk_start_time=strtotime($row['linedate']);	
                         		}	// && $last_latitude==0 && $last_longitude==0
                         		
                         		if($last_truck != $truck_id)
                         		{	//truck changed, so reset last...
                         			$last_latitude=0;
               					$last_longitude=0;	
               					$last_truck=$row['truck_id'];
                         		}
                         		          		
                         		if($last_latitude==0 && $last_longitude==0)	
                         		{
                         			$last_latitude=$row['latitude'];
               					$last_longitude=$row['longitude'];
               					$last_time=strtotime($row['linedate']);
               					$start_time=strtotime($row['linedate']);
               					//$wk_last_time=strtotime($row['linedate']);
               					$wk_start_time=strtotime($row['linedate']);
               					
               					$time_violations="Pre and Post Inspections";
               					
               					$prev_lat=$row['latitude'];
                    				$prev_long=$row['longitude']; 
               					
               					$hours_worked+=$pre_inspection + $post_inspection;
               					$wk_hours_worked+=$pre_inspection + $post_inspection;     				
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
                         		
                         		//check if reset is possible....WEEKLY HOURS only
                         		if($wk_hours_rested > $dot_wk_break)
                    			{
                    				//$wk_last_time=strtotime($row['linedate']);
          						$wk_start_time=strtotime($row['linedate']);	
          						
          						$wk_hours_driven=0;
          						$wk_hours_worked=0;
          						$wk_hours_rested=0; 
                    			}
                    			
                    			//issue speed violations
                    			if($row['truck_speed'] > $dot_max_speed)
                    			{
                    				$speeding_violations++;	
                    				$time_violations.=" <span class='alert'>Speeding ".$row['truck_speed']."MPH.</span>";	
                    			}
                    			
                    			$abrupt_shutdown_flag=0;
                    			$abrupt_shutdown="";
                         		if($timer > $gap_detector)
                         		{
                         			$abrupt_shutdown_flag++;
                        				$abrupt_shutdown="<br><span class='alert' title='GPS point gap detected... may be abrupt shutdown, misuse, or other error with system.'>Abrupt Shutdown!</span>";
                         		}
                         		
                         		//check if reset is possible....DAILY HOURS only                       || $abrupt_shutdown_flag==1
                    			if($hours_rested > $dot_break_hrs)
                    			{
                    				//see if row is already in table before adding it again.
                    				$sqli_checker="
                    					select id 
                    					from safety_report 
                    					where deleted=0
                    						and driver_id='".sql_friendly($rowd['id'])."'
                    						and linedate_start='".date("Y-m-d H:i:s",$start_time)."'
                    						and linedate_end='".date("Y-m-d H:i:s",strtotime($row['linedate']))."'
                    				";
                    				$data_checker=simple_query($sqli_checker);
                    				$mn_checker=mysqli_num_rows($data_checker);
                    				
                    				if($mn_checker == 0)
                    				{	//only add if not already present.                    				
                         				$sqli="
               							insert into safety_report
               								(id,
                              					linedate_added,
                              					linedate_start,
                              					linedate_end,	
                              					driver_id,
                              					truck_id,
                              					distance_feet,
                              					hours_driven,
                              					hours_worked,
                              					hours_rested,
                              					wk_hours_driven,
                              					wk_hours_worked,
                              					wk_hours_rested,
                              					speeding_violations,
                              					dot_violations,
                              					violation_notes,
                              					excuse_flag,
                              					excused_by_id,
                              					excuse_notes,
                              					deleted,
                              					hour_11_violation,
                              					hour_14_violation,
                              					break_10_violation,
                              					hour_70_violation,
                              					break_34_violation,
                              					abrupt_shutdown_flag)
               							values
               								(NULL,
               								NOW(),
               								'".date("Y-m-d H:i:s",$start_time)."',
               								'".date("Y-m-d H:i:s",strtotime($row['linedate']))."',
               								'".sql_friendly($rowd['id'])."',
               								'".sql_friendly($truck_id)."',
               								'".sql_friendly($gps_dist2)."',
               								'".sql_friendly($hours_driven)."',
               								'".sql_friendly($hours_worked)."',
               								'".sql_friendly($hours_rested)."',
               								'".sql_friendly($wk_hours_driven)."',
               								'".sql_friendly($wk_hours_worked)."',
               								'".sql_friendly($wk_hours_rested)."',
               								'".sql_friendly($speeding_violations)."',
               								'".sql_friendly($dot_violations)."',
               								'".sql_friendly($time_violations)."',
               								'0',
               								'',
               								'0',
               								'0',
               								'".sql_friendly($hr11_violations)."',
               								'".sql_friendly($hr14_violations)."',
               								'".sql_friendly($br10_violations)."',
               								'".sql_friendly($hr70_violations)."',
               								'".sql_friendly($br34_violations)."',
               								'".sql_friendly($abrupt_shutdown_flag)."')
               						";
                              			//simple_query($sqli);
                         			}
                         			
                         			$last_time=strtotime($row['linedate']);
          						$start_time=strtotime($row['linedate']);	
          						
          						$hours_driven=0;
          						$hours_worked=0;
          						$hours_rested=0;   
          						$dot_violations=0;  
          						$speeding_violations=0;	
          						$time_violations=0;	
          						
          						$hr11_violations=0;
          						$hr14_violations=0;
          						$br10_violations=0;
          						$hr70_violations=0;
          						$br34_violations=0;				
                    			}
                    			
                    			if($truck_moved>0)
                    			{
                    				$hours_rested=0;		//reset rest to zero...work done before DOT hrs rule reached for break.	
                    				$wk_hours_rested=0;
                    			}
                    			
                    			
                    			//Daily violations
                    			if($hours_driven > $dot_drive_hrs)
                    			{
                    				$time_violations.=" <span class='alert'>Drove more than ".$dot_drive_hrs." hrs.</span>";	
                    				$dot_violations++;
                    				$hr11_violations++;     						
          						//$br10_violations++;
          						
                    			}
                    			if($hours_worked > $dot_work_hrs)
                    			{
                    				$time_violations.=" <span class='alert'>Worked more than ".$dot_work_hrs." hrs.</span>";	
                    				$dot_violations++;
                    				$hr14_violations++;
                    				//$br10_violations++;
                    			}
                    			
                    			//Weekly violations
                    			if($wk_hours_worked > $dot_wk_hours && $wk_timer <= (24*$dot_wk_days))
                    			{
                    				$time_violations.=" <span class='alert'>Worked more than ".$dot_wk_hours." hrs in Week.</span>";	
                    				$dot_violations++;
                    				$hr70_violations++;
          						//$br34_violations++;
                    			}
                    			
                         		
                         		//display...            		
                         		
                         		$d1_mask="";
                         		$d2_mask="";
                         		if($row['driver_id'] > 0)			$d1_mask="<a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driver_first_name']." ".$row['driver_last_name']."</a>";
                         		if($row['driver2_id'] > 0)			$d2_mask="<a href='admin_drivers.php?id=".$row['driver2_id']."' target='_blank'>".$row['driver2_first_name']." ".$row['driver2_last_name']."</a>";
                         		
                         		$ig_mask="off";
                         		if($row['ignition'] > 0)				$ig_mask="ON";
                         		
                         		$fix_mask="Normal GPS fix";
                         		if($row['fix_type']	== 1)			$fix_mask="Automated Position Update fix";
                         		if($row['fix_type']	== 2)			$fix_mask="Vehicle start fix";
                         		if($row['fix_type']	== 3)			$fix_mask="Vehicle stop fix";
                         		
                         		$head_mask="North";
                         		if($row['truck_heading'] == 1)		$head_mask="Northeast ";
                         		if($row['truck_heading'] == 2)		$head_mask="East";
                         		if($row['truck_heading'] == 3)		$head_mask="Southeast";
                         		if($row['truck_heading'] == 4)		$head_mask="South";
                         		if($row['truck_heading'] == 5)		$head_mask="Southwest";
                         		if($row['truck_heading'] == 6)		$head_mask="West";
                         		if($row['truck_heading'] == 7)		$head_mask="Northwest";
                         		
                         		
     						$mrr_classy="good_driving";
     						if($speeding_violations > 0 || $dot_violations > 0)		$mrr_classy="violations";	
     						
     						$speed_sign="";
     						
     						if($row['latitude']!=0 || $row['longitude']!=0)
     						{
     							$speed_check=mrr_gps_speed_limit_finder($row['latitude'],$row['longitude']);
     							$speed_sign="
     								<span class='mrr_link_like_on' onClick='here_is_your_sign(".$row['id'].");'>".$speed_check['speed_limit']."</span>
     								<div class='hidden_signs'><div id='mrr_hidden_signs_".$row['id']."'>".$speed_check['output']."</div></div>
     							";
                         		} 
                         		/**/       
                         		
                         		            		
                         		$data_sorter.=  "
                         			<tr class='all_rows ".$mrr_classy."'>
                         				<td valign='top'>".date("m/d/Y H:i", strtotime($row['linedate']))."</td>
                         				<td valign='top'>".$d1_mask."</td>
                         				<td valign='top'>".$d2_mask."</td>
                         				<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['truck_name']."</a></td>
                         				<td valign='top'>".$ig_mask."</td>
                         				<td valign='top'>".$head_mask."</td>
                         				<td valign='top'>".$row['truck_speed']."</td>
                         				<td valign='top'>".$speed_sign."</td>
                         				<td valign='top'>".$row['latitude']."</td>
                         				<td valign='top'>".$row['longitude']."</td>
                         				<td valign='top'>".$row['location']."</td>
                         				<td valign='top' align='right'>".number_format(($gps_dist/5280),2)."</td>
                         				<td valign='top' align='right'>".number_format($gps_dist,2)."</td>
                         				<td valign='top' align='right'>".number_format($timer,2)."</td>	
                         				<td valign='top' align='right'>".number_format($stop_watch,2)."</td>	
                         				<td valign='top' align='right'>".number_format($hours_driven,2)."</td>
                         				<td valign='top' align='right'>".number_format($hours_worked,2)."</td>
                         				<td valign='top' align='right'>".number_format($hours_rested,2)."</td>
                         				<td valign='top' align='right'>".$speeding_violations."</td>
                         				<td valign='top' align='right'>".$time_violations."</td>
                         				<td valign='top' align='right'>".number_format($wk_timer,2)."</td>
                         				<td valign='top' align='right'>".number_format($wk_stop_watch,2)."</td>
                         				<td valign='top' align='right'>".($truck_moved > 0 ? "YES" : "")."".$abrupt_shutdown."</td>	                    					
                         			</tr>
                         		";	//<td valign='top'>".$fix_mask."</td>  <br>".($wk_last_time/3600)."<br>".($wk_start_time/3600)."
                         		
                         		$prev_lat=$row['latitude'];
                    			$prev_long=$row['longitude']; 
                         		
                         	}//end check for duplicate_date
                    	}
               	
               		
               	}//end trucks finder loop
               	
               	$data_sorter.= "
               		</tbody>
               		</table>
               	";
          	}//end check on rows
          	
     	}//end driver loop
	
	}
	
	echo $data_sorter;
	
?>

<div style='clear:both'></div>

<table class='admin_menu1 font_display_section' style='text-align:left;width:400px;margin:10px'>
	<tr>	<td valign='top' colspan='2'><center><b>SETTINGS FOR SAFETY REPORT</b></center></td></tr>
	<tr>	<td valign='top'><b>Truck moved if distance is over over </b></td>			<td valign='top'><?= $dot_min_move ?> ft</td>     </tr>
	<tr>	<td valign='top'><b>General Speeding Violation issued if over </b></td>		<td valign='top'><?= $dot_max_speed ?> MPH</td>   </tr>
	<tr>	<td valign='top'><b>Driver cannot Drive over </b></td>						<td valign='top'><?= $dot_drive_hrs ?> Hrs</td>     </tr>
	<tr>	<td valign='top'><b>Driver cannot Work over </b></td>						<td valign='top'><?= $dot_work_hrs ?> Hrs</td>     </tr>
	<tr>	<td valign='top'><b>Reset Day only when Rest for </b></td>					<td valign='top'><?= $dot_break_hrs ?> Hrs</td>     </tr>
	<tr>	<td valign='top'><b>Days per Week</b></td>								<td valign='top'><?= $dot_wk_days ?></td>     	</tr>
	<tr>	<td valign='top'><b>Weekly work limit is </b></td>						<td valign='top'><?= $dot_wk_hours ?> Hrs</td>     </tr>
	<tr>	<td valign='top'><b>Reset Week only when Rest for </b></td>					<td valign='top'><?= $dot_wk_break ?> Hrs</td>     </tr>
	<tr>	<td valign='top'><b>Pre-Trip Inspection Time is </b></td>					<td valign='top'><?= $pre_inspection  ?> Hrs</td>     </tr>
	<tr>	<td valign='top'><b>Post-Trip Inspection Time is </b></td>					<td valign='top'><?= $post_inspection ?> Hrs</td>     </tr>
</table>

<div style='clear:both'></div>
<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1800px;margin:10px'>
<thead>
<tr>
	<th nowrap><b>Load ID</b></th>
	<th nowrap><b>Dispatch</b></th>
	<th nowrap><b>Stop ID</b></th>
	<th><b>Customer</b></th>
	<th><b>Driver</b></th>
	<th><b>Truck</b></th>
	<th><b>Trailer</b></th>		
	<th><b>Note</b></th>
	<th><b>Due</b></th>
	<th><b>Hours</b></th>
	<th><b>Dest</b></th>
	<th><b>Miles</b></th>
	<th><b>Position</b></th>
	<th nowrap><b>ETA</b></th>
	<th><b>Grade</b></th>
	<th><b>Test</b></th>
	<th><b>Active</b></th>
	<th><b>&nbsp;</b></th>
</tr>
</thead>
<tbody>
<? 
	//$full_report=mrr_pull_all_active_geofencing_rows(0);	//moved to function to use in ajax as well.
	//echo $full_report;
?>
</tbody>
</table>
<br>     
<div>
<?
	//$debugger=mrr_run_full_geofencing_update_for_truck(200);		//
	//echo $debugger;	
?>
</div>

<?
	//$send_messages=mrr_trigger_email_by_event_type(1,"arrived");
	//echo $send_messages;
	
	//$send_messages=mrr_trigger_email_by_event_type(1,"departed");
	//echo $send_messages;
?>

<div style='margin-left:20px;' id='check_for_geo_id'></div>


</form>
<script type='text/javascript'>
	//$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
		$('.hidden_signs').hide();
		
		//$('.datepicker').datepicker();
		
		//setTimeout("location.reload();", (60 * 1000));		//ten minutes...600 seconds...1000=1 second
	});
	
	function send_email_hot_tracking(geoid,sectid)
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_send_email_for_hot_load_tracking",
		   data: {
		   		"geo_id":geoid,
		   		"geo_sector":sectid
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		txt="";
		   		
		   		if($(xml).find('mrrTab').text()=="Done")
		   		{			   				
		   			txt=txt + "<span class='good_alert'><b>DONE sending email.</b></span>";
		   		}
		   		else
		   		{
		   			txt=txt + "<span class='alert'><b>ERROR sending email.</b></span>";		
		   		}
		   		
		   		//txt=txt + " (Sector "+$(xml).find('sector').text() +" from "+sectid+".)";
		   		txt=txt + " "+$(xml).find('msg').text() +"";
		   		$('#geo_message').html(txt);
		   }	
		});
	}
	
	function here_is_your_sign(id)
	{		
		$('.hidden_signs').hide();
		sign_text=$('#mrr_hidden_signs_'+id+'').html(); 
          
          display_nice_dialog_prompt(800,700,'Here is the sign information local to the area...',sign_text);
		//$('.tablesorter').tablesorter();	
	}
	
	function mrr_show_by_code(cd)
	{
		$('.all_rows').show();
		if(cd==1)		$('.violations').hide();	
		if(cd==2)		$('.good_driving').hide();
	}
	
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete the tracking for this stop?")) {
			window.location = 'report_peoplenet_activity.php?delid=' + id;
		}
	}
	function confirm_deactivate(id) {
		if(confirm("Are you sure you want to deactivate the tracking for this stop?")) {
			window.location = 'report_peoplenet_activity.php?deactivate=' + id;
		}
	}
</script>
<? include('footer.php') ?>