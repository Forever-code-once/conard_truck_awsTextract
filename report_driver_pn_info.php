<?
$usetitle="Report - Driver Tracking Logs";   
$date_capture1="";
$date_capture2="";
	
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
	
	if(isset($_POST['date_from']) && isset($_POST['date_to']))				$date_capture1="Dates From ".$_POST['date_from']." To ".$_POST['date_to']."";
	
	if(!isset($_POST['date_from'])) 	$_POST['date_from'] = "11/13/2015";
	if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("m/d/Y");
	
	if($_POST['date_from']=="")		$_POST['date_from']="11/13/2015";
	if($_POST['date_to']=="")		$_POST['date_to']=date("m/d/Y");
	
	$early_notice="";
	if(date("Ymd",strtotime($_POST['date_from'])) <= "20151113")		{	$_POST['date_from']="11/13/2015";		$early_notice="<b>Report not available before 11/13/2015.</b>";	}
	if(date("Ymd",strtotime($_POST['date_to'])) < "20151113")			{	$_POST['date_to']=date("m/d/Y");		$early_notice="<b>Report not available before 11/13/2015.</b>"; 	}
	
	
	
	if(isset($_GET['delid']))
	{
		$sql="
		update ".mrr_find_log_database_name()."truck_tracking_driver_logs set
			deleted='1'
		where id='".sql_friendly($_GET['delid'])."'	
		";	
		simple_query($sql);	 
	}	
	
	if(isset($_POST['date_from']) && isset($_POST['date_to']))				$date_capture2="Still From ".$_POST['date_from']." To ".$_POST['date_to']."";
?>
<? include('header.php') ?>
<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<!-- <?= $date_capture1 ?><br> -->
	<!-- <?= $date_capture2 ?><br> -->
     <table style='text-align:left;width:1400px;margin:10px'>
     <tr>
     	<td valign='top' align='left'>
               <?				    	
          	$rfilter = new report_filter();
          	$rfilter->show_driver 			= true;
          	$rfilter->show_employers 		= true;
          	//$rfilter->summary_only	 		= true;
          	//$rfilter->team_choice	 		= true;
          	//$rfilter->show_font_size			= true;
          	$rfilter->mrr_special_print_button	= true;
          	//$rfilter->leave_form_open 		= true;
          	$rfilter->show_filter();     	
          	?>
          </td>
          <td valign='top' align='right' width='1000'>    
          	<?
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
     	
          	?> 
               <table class='admin_menu1 font_display_section' style='text-align:left;margin:10px' width='100%'>
               	<tr>	<td valign='top' colspan='4'><center><b>SETTINGS FOR SAFETY REPORT</b></center></td></tr>
               	<tr>	<td valign='top' colspan='4'><hr></td></tr>
               	<tr>	
               		<td valign='top'><b>Truck moved if distance between GPS points is over</b></td>	
               		<td valign='top'><?= $dot_min_move ?> ft or <?= number_format(($dot_min_move/5280),2) ?> mi</td>
               		<td valign='top'><b>Weekly work limit in </b><?= $dot_wk_days ?><b> Days is </b></td>
               		<td valign='top'><?= $dot_wk_hours ?> Hrs</td>	  
               	</tr>          	
               	<tr>	
               		<td valign='top'><b>'Abrupt Shutdown' if GPS gap is more than </b></td>
               		<td valign='top'><?= $gap_detector ?> Hrs</td>
               		<td valign='top'><b>Pre-Trip Inspection Time is </b></td>
               		<td valign='top'><?= $pre_inspection  ?> Hrs</td>
               	</tr>          		
               	<tr>	
               		<td valign='top'><b>General Speeding Violation issued if truck speed clocked over </b></td>
               		<td valign='top'><?= $dot_max_speed ?> MPH</td>
               		<td valign='top'><b>Post-Trip Inspection Time is </b></td>
               		<td valign='top'><?= $post_inspection ?> Hrs</td>
               	</tr>
               	<tr>	
               		<td valign='top'><b>Reset Day only when Rest (no movement) for </b></td>
               		<td valign='top'><?= $dot_break_hrs ?> Hrs</td>
               		<td valign='top'><b>In 24hr period, Driver cannot Work over </b></td>
               		<td valign='top'><?= $dot_work_hrs ?> Hrs</td>     </tr>          		
               	</tr>
               	<tr>	
               		<td valign='top'><b>Reset Week only when Rest for </b></td>
               		<td valign='top'><?= $dot_wk_break ?> Hrs</td>
               		<td valign='top'><b>In 24hr period, Driver cannot Drive over </b></td>
               		<td valign='top'><?= $dot_drive_hrs ?> Hrs</td>          		
               	</tr>
               </table>
     	</td>
     </tr>
     </table>
	
	<h3><?= $usetitle ?></h3>
	<br>
	<a href='report_driver_pn_info.php'><b>Reset Report for all drivers or a different driver.</b></a>
	<br>
</div>
<div style='clear:both'></div>

<div id='printable_area1'>
	<div style='clear:both'></div>

	<?	
     $data_sorter="";
     $summary_sorter="";
		
	$all_abrupt=0;
     $all_speeding=0; 
     
     $all_tot_11_hrs=0;
     $all_tot_14_hrs=0;
     $all_tot_10_brk=0;
     $all_tot_70_hrs=0;
     $all_tot_34_brk=0;
     
     $all_dot_violations=0; 
		
     if(isset($_POST['build_report']))
     {          
     	//281=John Baker  312=Grady Hough   342=Wesley Barlow (Teamed)     	
     	$sqlp="
     		select drivers.*,
     			(select option_values.fvalue from option_values where option_values.id=drivers.employer_id) as employer_name,
     			(select name_truck from trucks where trucks.id=drivers.attached_truck_id) as truck_name
     		from drivers
     		where drivers.active=1
     			and drivers.deleted=0
     			".($_POST['driver_id'] > 0 && $_POST['driver_id']!="" ? " and drivers.id='".sql_friendly($_POST['driver_id'])."'" : "")."
     			".($_POST['employer_id'] > 0 && $_POST['employer_id']!="" ? " and drivers.employer_id='".sql_friendly($_POST['employer_id'])."'" : "")."
     		order by drivers.name_driver_last asc,
     			drivers.name_driver_first asc
     	";
     	$datap=simple_query($sqlp);
         	while($rowp=mysqli_fetch_array($datap))
     	{
               $cntr=0;
               
               $gps_listing="";
               $gps_truck_listing="";
               
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
     		
               $driver_id=$rowp['id'];               
               $data_sorter.= "
               	<div class='mrr_view_details' style='text-align:left;width:1800px;margin:10px'>
               		<a href='admin_drivers.php?id=".$driver_id."' target='_blank'>".$rowp['name_driver_first']." ".$rowp['name_driver_last']."</a> 
               		(".$rowp['employer_name']."): ".$_POST['date_from']." thru ".$_POST['date_to']." EDriver Log History
               	</div>";
               	
               //$mrr_rep=mrr_peoplenet_driver_violations_update($driver_id,$_POST['employer_id']);	//driver, employer
               /*
               <th><b>HOS Rules</b></th>
               
               <th align='right' style='background-color:#E0B0FF;'><b>USA<br>607</b></th>
               <th align='right' style='background-color:#E0B0FF;'><b>USA<br>607s</b></th>
                    			<th align='right' style='background-color:#E0B0FF;'><b>USA<br>708s</b></th>
                    			<th align='right' style='background-color:#E0B0FF;'><b>CAN<br>607</b></th>
                    			<th align='right' style='background-color:#E0B0FF;'><b>CAN<br>708</b></th>
                    			<th align='right' style='background-color:#E0B0FF;'><b>CAN<br>12014</b></th>
                    			<th align='right' style='background-color:#E0B0FF;'><b>Ala<br>707</b></th>
                    			<th align='right' style='background-color:#E0B0FF;'><b>Ala<br>808</b></th>
                    			<th align='right' style='background-color:#E0B0FF;'><b>Spec</b></th>
               
               <th align='right' style='background-color:#CC99FF;'><b>USA<br>607</b></th>
               
               
                    			<th align='right' style='background-color:#CC99FF;'><b>USA<br>607s</b></th>
                    			<th align='right' style='background-color:#CC99FF;'><b>USA<br>708s</b></th>
                    			<th align='right' style='background-color:#CC99FF;'><b>CAN<br>607</b></th>
                    			<th align='right' style='background-color:#CC99FF;'><b>CAN<br>708</b></th>
                    			<th align='right' style='background-color:#CC99FF;'><b>CAN<br>12014</b></th>
                    			<th align='right' style='background-color:#CC99FF;'><b>Ala<br>707</b></th>
                    			<th align='right' style='background-color:#CC99FF;'><b>Ala<br>808</b></th>
                    			<th align='right' style='background-color:#CC99FF;'><b>Spec</b></th>
                    			
               <th><b>24hr<br>Reset</b></th>
               <th><b>36hr<br>Reset</b></th>
                    			<th><b>72hr<br>Reset</b></th>
               */
          	
          	//
          	$data_sorter.=  "<table class='admin_menu1 tablesorter font_display_section mrr_view_details' id='table_sort_report' style='text-align:left;width:1800px;margin:10px'>";
               $data_sorter.= "
               		<thead>   	
                    		<tr>	
                    			<th><b>Date</b></th>  
                    			<th><b>Truck</b></th>             			
                    			<th><b>Driver</b></th>     			
                    			<th><b>Trailer</b></th>                    			               			
                    			<th><b>Shipping Info</b></th>   	
                    			<th align='right' style='background-color:#E0B0FF;'><b>USA708 Driving Hrs</b></th>    
                    			<th align='right' style='background-color:#CC99FF;'><b>USA708 On-Duty Hrs</b></th>
                    			<th align='right'><b>30min Break</b></th>
                    			<th><b>Last 34hr Reset</b></th>
                    			<th><b>Duty Status</b></th>
                    			<th><b>Duty Status Date</b></th>                    			
                    		</tr>
                    		
               		</thead>
               		<tbody>
               ";   
               $gmt_offset=abs((int) $defaultsarray['gmt_offset_peoplenet']);       	
          	$sql="
          		select truck_tracking_driver_logs.*,
          			DATE_SUB(truck_tracking_driver_logs.linedate_gmt,INTERVAL ".$gmt_offset." HOUR) as offset_date,
          			DATE_SUB(truck_tracking_driver_logs.last_34h_reset,INTERVAL ".$gmt_offset." HOUR) as offset_date2,
          			DATE_SUB(truck_tracking_driver_logs.last_duty_status_date,INTERVAL ".$gmt_offset." HOUR) as offset_date3
          		from ".mrr_find_log_database_name()."truck_tracking_driver_logs
          		where truck_tracking_driver_logs.deleted=0
          			and truck_tracking_driver_logs.driver_id='".sql_friendly($driver_id)."'          			
          			and DATE_SUB(truck_tracking_driver_logs.linedate_gmt,INTERVAL ".$gmt_offset." HOUR)>='".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
          			and DATE_SUB(truck_tracking_driver_logs.linedate_gmt,INTERVAL ".$gmt_offset." HOUR)<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
          		order by truck_tracking_driver_logs.linedate_gmt asc
          	";	
          	
          	$data=simple_query($sql);
          	while($row=mysqli_fetch_array($data))
          	{  		     		
          		/*	          		
          		$id=$row['id'];
          		$gps_dist=$row['cur_feet'];
          		
          		
          		$mrr_classy="good_driving";
          		if($row['violation_code'] > 0)		$mrr_classy="violations";	
          		          		      	    	
               	$hours_driven+=$row['cur_hours_driven'];
               	$hours_worked+=$row['cur_hours_worked'];
               	$hours_rested+=$row['cur_hours_rested'];
               	$wk_hours_driven+=$row['wk_hours_driven'];
               	$wk_hours_worked+=$row['wk_hours_worked'];
               	$wk_hours_rested+=$row['wk_hours_rested'];
               	     		
          		$abrupt_flag="";
          		if($row['abrupt_shutdown'] > 0)
          		{
          			$abrupt_flag="<span class='mrr_sr_abrupt_stop'>Yes</span>";	
          			$abrupt++;	
          			
          			$mrr_classy.=" abrupt_shutdown";
          		}
          		
          		$mrr_classx="";
          		$vflag="";
          		if($row['violation_code']==1)		
          		{
          			$vflag="<span class='mrr_sr_speeding'>Speed</span>";		$mrr_classx=" class='mrr_sr_speeding'";		$mrr_classy.=" speeding";	$speeding++; 		$dot_violations++; 	
          		}
          		
          		if($reset_needed_11==0 && $row['violation_code']==2)
          		{
          			$vflag="<span class='mrr_sr_11hr_rule'>11Hour</span>";		$mrr_classx=" class='mrr_sr_11hr_rule'";	$mrr_classy.=" overdriven";	$tot_11_hrs++;		$dot_violations++; $reset_needed_11=1;
          		}
          		if($reset_needed_14==0 && $row['violation_code']==3)
          		{
          			$vflag="<span class='mrr_sr_14hr_rule'>14Hour</span>";		$mrr_classx=" class='mrr_sr_14hr_rule'";	$mrr_classy.=" overworked";	$tot_14_hrs++;		$dot_violations++; $reset_needed_14=1;	
          		}
          		
          		if($reset_needed_wk==0 && $row['violation_code']==4)
          		{
          			$vflag="<span class='mrr_sr_70hr_rule'>70Hour</span>";		$mrr_classx=" class='mrr_sr_70hr_rule'";	$mrr_classy.=" overweeked";	$tot_70_hrs++;		$dot_violations++; $reset_needed_wk=1;  
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
          		   		
          		   		
          		//$tot_10_brk+=0;      	    
           	    	//$tot_34_brk+=0;
          		
          		$user_mask="";
          		if($row['excused_by'] > 0)    		$user_mask= "<a href='admin_users.php?eid=".$row['excused_by']."' target='_blank'>".$rowd['user_name']."</a>";//
          		
          		$flagger="";
          		if($row['excused'] == 1)				$flagger="Excused";
          		
          		$view_linker="<a href='report_peoplenet_drivers.php?driver_id=".$row['driver_id']."&date_from=".date("m_d_Y", strtotime($row['linedate']))."&date_to=".date("m_d_Y", strtotime($row['linedate']))."' target='_blank'>
          						".date("m/d/Y H:i", strtotime($row['linedate']))."
          					</a>";		//$view_linker
          		
          		$ex_date=date("m/d/Y H:i", strtotime($row['excused_date']));
          		if(substr_count($ex_date,"12/31/1969")> 0)		$ex_date="";
          		*/
          		
          		$mrr_classy="";
          											//linedate_gmt
          		/*
          		<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_usa607'])."</td>
          		
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_usa607short'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_usa708short'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_can607'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_can708'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_can12014'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_alaska707'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_alaska808'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_spec'])."</td>
          		
          		<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_usa607'])."</td>
          		
          		<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_usa607short'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_usa708short'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_can607'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_can708'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_can12014'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_alaska707'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_alaska808'])."</td>
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_spec'])."</td>
          		
          		<td valign='top'>".mrr_pn_decode_date_string($row['last_24h_reset'])."</td>
          		<td valign='top'>".mrr_pn_decode_date_string($row['last_36h_reset'])."</td>
               			<td valign='top'>".mrr_pn_decode_date_string($row['last_72h_reset'])."</td>
          		
          		<td valign='top'>".mrr_pn_decode_hos_rules($row['rules'])."</td>
          		*/
          		$data_sorter.=  "
          			<tr class='all_rows ".$mrr_classy."'>
               			<td valign='top' nowrap>".mrr_pn_decode_date_string($row['offset_date'])."</td>   
               			<td valign='top'><b>".$row['truck']."</b></td>          			
               			<td valign='top' nowrap><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['drivername']."</a></td>               			
               			<td valign='top'><b>".$row['trailer']."</b></td>
               			
               			           			
               			<td valign='top'>".$row['shipping_info']."</td>               			
               			
               			
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['dsa_usa708'])."</td>
               			
               			
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['osa_usa708'])."</td>
               			
               			
               			<td valign='top' align='right'>".mrr_pn_decode_avail_secs($row['on_sec_until_30minbreak'])."</td>
               			
               			<td valign='top'>".mrr_pn_decode_date_string($row['offset_date2'])."</td>               			
               			
               			<td valign='top'>".mrr_pn_decode_duty_status($row['last_duty_status'])."</td>
               			<td valign='top'>".mrr_pn_decode_date_string($row['offset_date3'])."</td> 
               		</tr>
          			  
          		";	//<td valign='top'>".$row['id']."</td>  
          		
          		$cntr++;                        		
          	}//end driver report loop
          	
          	$data_sorter.= "
               		</tbody>          		
               		</table>          		
               ";
               
               $cntrv=0;
               $data_sorter.= "
               	<div class='mrr_view_details' style='text-align:left;width:1800px;margin:10px'>
               		<a href='admin_drivers.php?id=".$driver_id."' target='_blank'>".$rowp['name_driver_first']." ".$rowp['name_driver_last']."</a> 
               		(".$rowp['employer_name']."): ".$_POST['date_from']." thru ".$_POST['date_to']." EDriver Log Events
               	</div>";
               $data_sorter.=  "<table class='admin_menu1 tablesorter font_display_section mrr_view_details' id='table_sort_report_v' style='text-align:left;width:1800px;margin:10px'>";
               $data_sorter.= "
               		<thead>   	
                    		<tr>	
                    			<th><b>Date</b></th>  
                    			<th><b>Event</b></th>             			
                    			<th><b>Data1</b></th>     			
                    			<th><b>Data2</b></th>                    			               			
                    			<th><b>Data3</b></th>
                    			<th><b>Data4</b></th>
                    			<th><b>Setting1</b></th>
                    			<th><b>Setting2</b></th>
                    			<th><b>Setting3</b></th>
                    			<th><b>Setting4</b></th>                  			
                    		</tr>                    		
               		</thead>
               		<tbody>
               ";   
          	
          	$sql="
          		select
          			(select event_name from ".mrr_find_log_database_name()."driver_elog_event_types where driver_elog_event_types.event_id=driver_elog_entries.event_id) as event_name,
          			driver_elog_entries.*,
          			DATE_SUB(driver_elog_entries.linedate_created,INTERVAL ".$gmt_offset." HOUR) as offset_date
          		from ".mrr_find_log_database_name()."driver_elog_entries
          		where driver_elog_entries.deleted=0
          			and driver_elog_entries.driver_id='".sql_friendly($driver_id)."'          			
          			and DATE_SUB(driver_elog_entries.linedate_created,INTERVAL ".$gmt_offset." HOUR)>='".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
          			and DATE_SUB(driver_elog_entries.linedate_created,INTERVAL ".$gmt_offset." HOUR)<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
          		order by driver_elog_entries.linedate_created asc
          	";	          	
          	$data=simple_query($sql);
          	while($row=mysqli_fetch_array($data))
          	{
          		$event1="&nbsp;";		if(trim($row['event_data1'])!="")		$event1=trim($row['event_data1']);
          		$event2="&nbsp;";		if(trim($row['event_data2'])!="")		$event2=trim($row['event_data2']);
          		$event3="&nbsp;";		if(trim($row['event_data3'])!="")		$event3=trim($row['event_data3']);
          		$event4="&nbsp;";		if(trim($row['event_data4'])!="")		$event4=trim($row['event_data4']);
          		
          		$set1="&nbsp;";		if(trim($row['setting1'])!="")		$set1=trim($row['setting1']);
          		$set2="&nbsp;";		if(trim($row['setting2'])!="")		$set2=trim($row['setting2']);
          		$set3="&nbsp;";		if(trim($row['setting3'])!="")		$set3=trim($row['setting3']);
          		$set4="&nbsp;";		if(trim($row['setting4'])!="")		$set4=trim($row['setting4']);
          		
          		//now update the specs if specific event-types need it.
          		$etype=$row['event_id'];
          		if($etype==2)
          		{
          			$duty_type=(int) $event1;
          			$event1="Not Found";
          			if($duty_type==0)		$event1="Unknown";
          			if($duty_type==1)		$event1="Driving";
          			if($duty_type==2)		$event1="On Duty (not driving)";
          			if($duty_type==3)		$event1="Off Duty";
          			if($duty_type==4)		$event1="Sleeper Berth";
          		}
          		
          		
          		
          		//<td valign='top' nowrap><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['drivername']."</a></td> 
          		$data_sorter.=  "
          			<tr class='all_rows ".$mrr_classy."'>
               			<td valign='top' nowrap>".mrr_pn_decode_date_string($row['offset_date'])."</td>   
               			<td valign='top'>".$row['event_name']."</td>               			              			
               			<td valign='top'>".$event1."</td>
               			<td valign='top'>".$event2."</td>
               			<td valign='top'>".$event3."</td>
               			<td valign='top'>".$event4."</td>
               			<td valign='top'>".$set1."</td>            			
               			<td valign='top'>".$set2."</td>               			
               			<td valign='top'>".$set3."</td>  
               			<td valign='top'>".$set4."</td>  
               		</tr>          			  
          		";	          		
          		$cntrv++; 
          	}
          	
          	
          	
          	//$tot_10_brk=0;
         	 	//$tot_34_brk=0;
          	
          	$all_abrupt+=$abrupt;
     		$all_speeding+=$speeding; 
               
               $all_tot_11_hrs+=$tot_11_hrs;
               $all_tot_14_hrs+=$tot_14_hrs;
               //$all_tot_10_brk+=$tot_10_brk;
               $all_tot_70_hrs+=$tot_70_hrs;
               //$all_tot_34_brk+=$tot_34_brk;
               
               $all_dot_violations+=$dot_violations;          	
               
               
               
               
               
               
               
               
               
               
               
               
               
               
               
               
               
               
               
               
               
          	
          	$data_sorter.= "
               		</tbody>          		
               		</table>
               		
               		<div style='clear:both'></div>
               		<table class='admin_menu1 font_display_section mrr_view_details' style='text-align:left;margin:10px' width='1800'> 
               		<tr>
                              <td valign='top' width='300'>              			
                         		<table width='100%' border='0'>
                                   	<tr>
                                   		<td valign='top' colspan='2' width='350'><center><b>".strtoupper($rowp['name_driver_first']." ".$rowp['name_driver_last'])." SUMMARY</b></center></td>
                                   	</tr>
                                   	<tr>
                                   		<td valign='top'>".$dot_violations."</td>
                                   		<td valign='top'><b>DOT Violations found.</b> ".show_help('drivers_vacation_advances.php','DOT total violations')."</td>
                                   	</tr>
                                   	<tr class='all_rows violations speeding'>
                                   		<td valign='top'>".$speeding."</td>
                                   		<td valign='top'><span class='mrr_sr_speeding'>Speeding violations</span> ".show_help('drivers_vacation_advances.php','system speeding violation')."</td>
                                   	</tr>
                                   	<tr class='all_rows violations overdriven'>
                                   		<td valign='top'>".$tot_11_hrs."</td>
                                   		<td valign='top'><span class='mrr_sr_11hr_rule'>11-Hour Driving Rule violations</span> ".show_help('drivers_vacation_advances.php','DOT 11 hour violation')."</td>
                                   	</tr>
                                   	<tr class='all_rows violations overworked'>
                                   		<td valign='top'>".$tot_14_hrs."</td>
                                   		<td valign='top'><span class='mrr_sr_14hr_rule'>14-Hour Working Rule Violations</span> ".show_help('drivers_vacation_advances.php','DOT 14 hour violation')."</td>
                                   	</tr>
                                   	<tr class='all_rows violations overweeked'>
                                   		<td valign='top'>".$tot_70_hrs."</td>
                                   		<td valign='top'><span class='mrr_sr_70hr_rule'>70-Hour Work Week Rule violations</span> ".show_help('drivers_vacation_advances.php','DOT 70 hour violation')."</td>
                                   	</tr>
                                   	<tr class='all_rows violations abrupt_shutdown'>
                                   		<td valign='top'>".$abrupt."</td>
                                   		<td valign='top'><span class='mrr_sr_abrupt_stop'>Abrupt Shutdown instances.</span> ".show_help('drivers_vacation_advances.php','Abrupt Shutdowns')."</td>
                                   	</tr>
                              	</table>  
                              </td>
                              <td valign='top'>
                              	&nbsp;
                              </td>
                         </tr>
                         </table> 	 <br><br>
                         ".$gps_listing."                    
                         <div style='clear:both'></div>
                         <div style='clear:both'></div>            		
               	";
               	
               $summary_sorter.="
     			<tr>
     				<td valign='top'>".$rowp['name_driver_first']." ".$rowp['name_driver_last']."</td>
     				<td valign='top' align='right'><span class='mrr_sr_abrupt_stop'>".$abrupt."</span></td>
     				<td valign='top' align='right'><span class='mrr_sr_speeding'>".$speeding."</span></td>
     				<td valign='top' align='right'><span class='mrr_sr_11hr_rule'>".$tot_11_hrs."</span></td>
     				<td valign='top' align='right'><span class='mrr_sr_14hr_rule'>".$tot_14_hrs."</span></td>
     				<td valign='top' align='right'><span class='mrr_sr_70hr_rule'>".$tot_70_hrs."</span></td>     				
     				<td valign='top' align='right'>".$dot_violations."</td>
     			</tr>	
     		";
              	
			
          }//end driver loop
     	
		echo $data_sorter;
	
		$data_sorter="";
		$show_both=0;
		if($show_both==1)
		{
     	
     		$sqlp="
          		select drivers.*,
          			(select option_values.fvalue from option_values where option_values.id=drivers.employer_id) as employer_name,
          			(select name_truck from trucks where trucks.id=drivers.attached_truck_id) as truck_name
          		from drivers
          		where drivers.active=1
          			and drivers.deleted=0
          			".($_POST['driver_id'] > 0 && $_POST['driver_id']!="" ? " and drivers.id='".sql_friendly($_POST['driver_id'])."'" : "")."
          			".($_POST['employer_id'] > 0 && $_POST['employer_id']!="" ? " and drivers.employer_id='".sql_friendly($_POST['employer_id'])."'" : "")."
          		order by drivers.name_driver_last asc,
          			drivers.name_driver_first asc
          	";
          	$datap=simple_query($sqlp);
              	while($rowp=mysqli_fetch_array($datap))
          	{
                    $cntr=0;
                    
                    $gps_listing="";
                    $gps_truck_listing="";
                    
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
          		
                    $driver_id=$rowp['id'];               
                    $data_sorter.= "
                    	<div class='mrr_view_details' style='text-align:left;width:1800px;margin:10px'>
                    		<a href='admin_drivers.php?id=".$driver_id."' target='_blank'>".$rowp['name_driver_first']." ".$rowp['name_driver_last']."</a> 
                    		(".$rowp['employer_name']."):
                    	</div>";
                    	
                    //$mrr_rep=mrr_peoplenet_driver_violations_update($driver_id,$_POST['employer_id']);	//driver, employer
               	   	
               	$data_sorter.=  "<table class='admin_menu1 tablesorter font_display_section mrr_view_details' id='table_sort_report' style='text-align:left;width:1800px;margin:10px'>";
                    $data_sorter.= "
                    		<thead>               			
                    			<tr>
                         			<th><b><span title='PFM Driver ID'>PFMID</span></b></th>	
                         			<th><b><span title='Conard Driver ID'>DriverID</span></b></th>	
                         			<th><b><span title='Stored Date'>Stored</span></b></th>	
                         			<th><b><span title='GMT Date'>GMT Date</span></b></th>	
                         			<th><b><span title='Full name from driver profile'>Driver Name</span></b></th>	
                         			<th><b><span title='HOS Reg. US Fed'>USA HOS</span></b></th>	
                         			<th><b><span title='HOS Reg. Canada'>CAN HOS</span></b></th>	
                         			<th><b><span title='HOS Reg. US State'>USA HOS</span></b></th>	
                         			<th><b><span title='HOS Reg. Canada Province'>CAN HOS</span></b></th>	
                         			<th><b><span title='HOS Reg. Specialty'>Special</span></b></th>	
                         			<th><b><span title='Terminal ID'>Terminal</span></b></th>	
                         			<th><b><span title='Current Personal Conveyance US Miles'>Curr USA Miles</span></b></th>	
                         			<th><b><span title='Pending Personal Conveyance US Miles'>Pend CAN Miles</span></b></th>		
                         			<th><b><span title='Current Personal Conveyance Km Canada'>Curr CAN KM</span></b></th>	
                         			<th><b><span title='Pending Personal Conveyance Km Canada'>Pend CAN KM</span></b></th>	
                         			<th><b><span title='Cust-defined field 1'>Custom1</span></b></th>	
                         			<th><b><span title='Cust-defined field 2'>Custom2</span></b></th>	
                         			<th><b><span title='Cust-defined field 3'>Custom3</span></b></th>	
                         			<th><b><span title='Cust-defined field 4'>Custom4</span></b></th>		
                         			<th><b><span title='Notes pulled from system'>Notes</span></b></th>	
                         		</tr>
                    		</thead>
                    		<tbody>
                    ";          	
               	$sql="
               		select truck_tracking_drivers.*
               		from ".mrr_find_log_database_name()."truck_tracking_drivers
               		where truck_tracking_drivers.deleted=0
               			and truck_tracking_drivers.driver_id='".sql_friendly($driver_id)."'          			
               			and truck_tracking_drivers.linedate_gmt>='".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
               			and truck_tracking_drivers.linedate_gmt<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
               		order by truck_tracking_drivers.linedate_gmt asc
               	";	
               	
               	$data=simple_query($sql);
               	while($row=mysqli_fetch_array($data))
               	{  		     		
               		/*	          		
               		$id=$row['id'];
               		$gps_dist=$row['cur_feet'];
               		
               		
               		$mrr_classy="good_driving";
               		if($row['violation_code'] > 0)		$mrr_classy="violations";	
               		          		      	    	
                    	$hours_driven+=$row['cur_hours_driven'];
                    	$hours_worked+=$row['cur_hours_worked'];
                    	$hours_rested+=$row['cur_hours_rested'];
                    	$wk_hours_driven+=$row['wk_hours_driven'];
                    	$wk_hours_worked+=$row['wk_hours_worked'];
                    	$wk_hours_rested+=$row['wk_hours_rested'];
                    	     		
               		$abrupt_flag="";
               		if($row['abrupt_shutdown'] > 0)
               		{
               			$abrupt_flag="<span class='mrr_sr_abrupt_stop'>Yes</span>";	
               			$abrupt++;	
               			
               			$mrr_classy.=" abrupt_shutdown";
               		}
               		
               		$mrr_classx="";
               		$vflag="";
               		if($row['violation_code']==1)		
               		{
               			$vflag="<span class='mrr_sr_speeding'>Speed</span>";		$mrr_classx=" class='mrr_sr_speeding'";		$mrr_classy.=" speeding";	$speeding++; 		$dot_violations++; 	
               		}
               		
               		if($reset_needed_11==0 && $row['violation_code']==2)
               		{
               			$vflag="<span class='mrr_sr_11hr_rule'>11Hour</span>";		$mrr_classx=" class='mrr_sr_11hr_rule'";	$mrr_classy.=" overdriven";	$tot_11_hrs++;		$dot_violations++; $reset_needed_11=1;
               		}
               		if($reset_needed_14==0 && $row['violation_code']==3)
               		{
               			$vflag="<span class='mrr_sr_14hr_rule'>14Hour</span>";		$mrr_classx=" class='mrr_sr_14hr_rule'";	$mrr_classy.=" overworked";	$tot_14_hrs++;		$dot_violations++; $reset_needed_14=1;	
               		}
               		
               		if($reset_needed_wk==0 && $row['violation_code']==4)
               		{
               			$vflag="<span class='mrr_sr_70hr_rule'>70Hour</span>";		$mrr_classx=" class='mrr_sr_70hr_rule'";	$mrr_classy.=" overweeked";	$tot_70_hrs++;		$dot_violations++; $reset_needed_wk=1;  
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
               		   		
               		   		
               		//$tot_10_brk+=0;      	    
                	    	//$tot_34_brk+=0;
               		
               		$user_mask="";
               		if($row['excused_by'] > 0)    		$user_mask= "<a href='admin_users.php?eid=".$row['excused_by']."' target='_blank'>".$rowd['user_name']."</a>";//
               		
               		$flagger="";
               		if($row['excused'] == 1)				$flagger="Excused";
               		
               		$view_linker="<a href='report_peoplenet_drivers.php?driver_id=".$row['driver_id']."&date_from=".date("m_d_Y", strtotime($row['linedate']))."&date_to=".date("m_d_Y", strtotime($row['linedate']))."' target='_blank'>
               						".date("m/d/Y H:i", strtotime($row['linedate']))."
               					</a>";		//$view_linker
               		
               		$ex_date=date("m/d/Y H:i", strtotime($row['excused_date']));
               		if(substr_count($ex_date,"12/31/1969")> 0)		$ex_date="";
               		*/
               		
               		$mrr_classy="";
               		
               		$data_sorter.=  "
               			<tr class='all_rows ".$mrr_classy."'>
                    			<td valign='top'>".$row['pn_id']."</td>	
                    			<td valign='top'>".$row['driver_id']."</td>
                    			<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
                    			<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_gmt']))."</td>
                    			<td valign='top'><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driver_name']."</a></td>
                    			<td valign='top'>".number_format($row['hos_usa_fed'],2)."</td>
                    			<td valign='top'>".number_format($row['hos_can_fed'],2)."</td>	
                    			<td valign='top'>".number_format($row['hos_usa_state'],2)."</td>
                    			<td valign='top'>".number_format($row['hos_can_state'],2)."</td>
                    			<td valign='top'>".number_format($row['hos_specialty'],2)."</td>
                    			<td valign='top'>".$row['terminal_id']."</td>
                    			<td valign='top'>".number_format($row['curr_usa_miles'],2)."</td>
                    			<td valign='top'>".number_format($row['pend_usa_miles'],2)."</td>	
                    			<td valign='top'>".number_format($row['curr_can_km'],2)."</td>
                    			<td valign='top'>".number_format($row['pend_can_km'],2)."</td>
                    			<td valign='top'>".trim($row['custom1'])."</td>
                    			<td valign='top'>".trim($row['custom2'])."</td>
                    			<td valign='top'>".trim($row['custom3'])."</td>
                    			<td valign='top'>".trim($row['custom4'])."</td>	
                    			<td valign='top'>".trim($row['notes'])."</td>
                    		</tr>
               			  
               		";	//<td valign='top'>".$row['id']."</td>  
               		
               		$cntr++;                        		
               	}//end driver report loop
               	
               	
               	//$tot_10_brk=0;
              	 	//$tot_34_brk=0;
               	
               	$all_abrupt+=$abrupt;
          		$all_speeding+=$speeding; 
                    
                    $all_tot_11_hrs+=$tot_11_hrs;
                    $all_tot_14_hrs+=$tot_14_hrs;
                    //$all_tot_10_brk+=$tot_10_brk;
                    $all_tot_70_hrs+=$tot_70_hrs;
                    //$all_tot_34_brk+=$tot_34_brk;
                    
                    $all_dot_violations+=$dot_violations;          	
               	
               	$data_sorter.= "
                    		</tbody>          		
                    		</table>
                    		
                    		<div style='clear:both'></div>
                    		<table class='admin_menu1 font_display_section mrr_view_details' style='text-align:left;margin:10px' width='1800'> 
                    		<tr>
                                   <td valign='top' width='300'>              			
                              		<table width='100%' border='0'>
                                        	<tr>
                                        		<td valign='top' colspan='2' width='350'><center><b>".strtoupper($rowp['name_driver_first']." ".$rowp['name_driver_last'])." SUMMARY</b></center></td>
                                        	</tr>
                                        	<tr>
                                        		<td valign='top'>".$dot_violations."</td>
                                        		<td valign='top'><b>DOT Violations found.</b> ".show_help('drivers_vacation_advances.php','DOT total violations')."</td>
                                        	</tr>
                                        	<tr class='all_rows violations speeding'>
                                        		<td valign='top'>".$speeding."</td>
                                        		<td valign='top'><span class='mrr_sr_speeding'>Speeding violations</span> ".show_help('drivers_vacation_advances.php','system speeding violation')."</td>
                                        	</tr>
                                        	<tr class='all_rows violations overdriven'>
                                        		<td valign='top'>".$tot_11_hrs."</td>
                                        		<td valign='top'><span class='mrr_sr_11hr_rule'>11-Hour Driving Rule violations</span> ".show_help('drivers_vacation_advances.php','DOT 11 hour violation')."</td>
                                        	</tr>
                                        	<tr class='all_rows violations overworked'>
                                        		<td valign='top'>".$tot_14_hrs."</td>
                                        		<td valign='top'><span class='mrr_sr_14hr_rule'>14-Hour Working Rule Violations</span> ".show_help('drivers_vacation_advances.php','DOT 14 hour violation')."</td>
                                        	</tr>
                                        	<tr class='all_rows violations overweeked'>
                                        		<td valign='top'>".$tot_70_hrs."</td>
                                        		<td valign='top'><span class='mrr_sr_70hr_rule'>70-Hour Work Week Rule violations</span> ".show_help('drivers_vacation_advances.php','DOT 70 hour violation')."</td>
                                        	</tr>
                                        	<tr class='all_rows violations abrupt_shutdown'>
                                        		<td valign='top'>".$abrupt."</td>
                                        		<td valign='top'><span class='mrr_sr_abrupt_stop'>Abrupt Shutdown instances.</span> ".show_help('drivers_vacation_advances.php','Abrupt Shutdowns')."</td>
                                        	</tr>
                                   	</table>  
                                   </td>
                                   <td valign='top'>
                                   	&nbsp;
                                   </td>
                              </tr>
                              </table> 	 <br><br>
                              ".$gps_listing."                    
                              <div style='clear:both'></div>
                              <div style='clear:both'></div>            		
                    	";
                    	
                    $summary_sorter.="
          			<tr>
          				<td valign='top'>".$rowp['name_driver_first']." ".$rowp['name_driver_last']."</td>
          				<td valign='top' align='right'><span class='mrr_sr_abrupt_stop'>".$abrupt."</span></td>
          				<td valign='top' align='right'><span class='mrr_sr_speeding'>".$speeding."</span></td>
          				<td valign='top' align='right'><span class='mrr_sr_11hr_rule'>".$tot_11_hrs."</span></td>
          				<td valign='top' align='right'><span class='mrr_sr_14hr_rule'>".$tot_14_hrs."</span></td>
          				<td valign='top' align='right'><span class='mrr_sr_70hr_rule'>".$tot_70_hrs."</span></td>     				
          				<td valign='top' align='right'>".$dot_violations."</td>
          			</tr>	
          		";
                   	
     			
               }//end driver loop
          	
     	}//end submit button check	
     	echo $data_sorter;
	}
	?>     
     <div style='clear:both'></div>
</div>
<script type='text/javascript'>
		
	$().ready(function() {		
		$('.tablesorter').tablesorter();
	});
	
</script>
<? include('footer.php') ?>
<?
/*
Aubrey Miles Jr348514182758011/13/15 17:32:10178717--QuadGraffics--Nashville 178717--QuadGraffics-Nashville, 178717--qaudgrafics--Nashville,TN932813960039600396003960046800468004680000504005040050400504005040050400504000011/13/15 17:32:1011/13/15 17:32:1011/13/15 17:32:10310/26/15 23:59:5811/13/15 17:32:10002880034Barry Grimes475514182745211/13/15 17:39:483960039600396003960046800468004680000504005040050400504005040050400504000011/10/15 04:40:4011/10/15 04:40:4010/04/15 16:00:37411/12/15 17:37:3411/10/15 04:40:40002880034Bob Norton484514155848611/13/15 17:41:44lancaster p to westlake perryburg oh952942343623436234362343629946299462994600285882858828588285882994629946299460011/08/15 22:48:2611/08/15 22:48:2610/23/15 18:53:52311/13/15 16:48:2411/08/15 22:48:2600698834Brian Dunlap#077514111/13/15 16:39:46la vergne tn to c burg pa and return072483960039600396003960046800468004680000504005040050400504005040050400504000011/13/15 16:39:4611/13/15 16:39:4611/13/15 16:39:46311/06/15 05:53:4611/13/15 16:39:46002880034Byron Glenn Perkins485514148985611/13/15 17:38:00ALAMANCE FDS. Cool/Pops Burlington NC. Bol. scv 35827826m7185022850828508285082850835708357083570800352583525835258352583904639046390460011/08/15 23:12:5111/08/15 23:12:5110/07/15 12:59:46311/13/15 16:57:1411/08/15 23:12:51002109234Candiss Tweedie512514155849411/13/15 17:32:1049812 murfreesboro tn pallets gaylord bol 278585309-003hbk2nd966752317123171231712317130371303713037100295962959629596295963309633096330960011/09/15 11:40:3711/09/15 11:40:37111/13/15 16:53:2611/09/15 11:40:37002723234Charles Hardee331514111/13/15 15:19:06bobtail14563774137741377413774144941449414494100444684446844468444684685146851468510011/10/15 12:35:0311/10/15 12:35:0309/20/15 20:56:07311/13/15 15:18:3811/10/15 12:35:03002624034Clark Lyons412514148985911/13/15 17:36:463960039600396003960046800468004680000503785037850378503785037850378503780011/09/15 17:16:3311/09/15 17:16:3310/19/15 16:21:09211/13/15 17:09:5011/09/15 17:16:33002877834Conley Necessary372514155848911/13/15 17:37:28BOL #1642035531663034030340303403034035644356443564400356443564435644356443564435644356440011/11/15 15:13:1511/08/15 20:55:5207/20/15 04:50:10111/13/15 15:32:0311/11/15 15:13:15001404434Darrell Murphy476536871424110/07/15 21:36:122271922719227192271916812168121681200239952399523995239951681216812168120010/05/15 12:20:3210/05/15 12:20:3210/05/15 12:20:32310/07/15 21:35:5510/05/15 12:20:320038Darren Isenberger334536871424100111/13/15 17:39:123434634346343463434638654386543865400458544585445854458543865438654386540011/09/15 13:47:5911/09/15 13:47:5910/12/15 13:14:02111/13/15 15:56:3711/09/15 13:47:590038Daryl Eaddy292514182765011/13/15 17:30:44BL WM00023291W095183215032150321503215039350393503935000363443634436344363444227242272422720011/08/15 13:53:2811/08/15 13:53:2811/08/15 13:53:28111/13/15 16:06:5811/08/15 13:53:28002519434David Barlow410514182766411/13/15 17:21:40graphic packaging 220 industrial dr perry,ga535483106531065310653106531065310653106500310653106531065310653106531065310650011/09/15 16:57:0711/09/15 16:57:0711/06/15 09:38:19211/13/15 16:44:0811/09/15 16:57:0700946534David Smithson436514182758611/13/15 17:14:26toy0196262086208620862081975219752197520062086208620862082176721767217670011/08/15 22:54:5711/08/15 22:54:5711/08/15 22:54:57411/13/15 14:55:4211/08/15 22:54:57002790534Dennis Ferral394536871424182761011/13/15 17:31:38softex paper inc. , paper product , rock hill, sc / bol # 7707u932813475534755347553475534364343643436400415644156441564415643436434364343640011/12/15 12:14:4711/12/15 12:14:4709/08/15 10:05:52111/13/15 16:51:1711/12/15 12:14:470038Dennis Quimby505514148985311/13/15 17:38:42#49991 lancaster pa to walker mi/lancaster east buhrle whse-paper inserts bol 9552258968061726917269172691726924469244692446900208922089220892208922545025450254500011/08/15 17:40:5011/08/15 17:40:5009/08/15 09:40:37211/13/15 16:05:1111/08/15 17:40:50002680134Derel Scales507536871424148985211/13/15 17:39:34Morrison,Tn /Bridgestone/tires/7822295mW109903680236802368023680241593415934159300415934159341593415934159341593415930011/09/15 08:18:4211/09/15 08:18:4209/08/15 12:07:36211/13/15 16:55:2111/09/15 08:18:420038Don Stanley425514111/12/15 18:21:162092720927209272092728127281272812700290002900029000290002903429034290340011/08/15 12:13:4511/08/15 12:13:4510/27/15 03:42:32311/12/15 18:20:5911/08/15 12:13:4500740034Eric King516514155849311/13/15 17:30:10281572153213601136011360113601143211432114321100424414244142441424414507245072450720010/30/15 23:32:48111/13/15 16:49:5011/09/15 02:29:40002347234Gary Womack406514148985911/13/15 17:36:461371313713137131371320913209132091300189471894718947189472299022990229900011/09/15 08:09:0311/09/15 08:09:0310/13/15 16:40:17311/13/15 17:08:1911/09/15 08:09:0300395734Hal Wagner#095514182765211/13/15 17:39:10BOL# N009957310662631623162316231621036210362103620073337333733373331168911689116890011/11/15 04:34:4111/11/15 04:34:4111/11/15 04:34:41211/13/15 16:48:3611/11/15 04:34:4100704934Hansen White501514155849211/13/15 17:33:44Quad Graphics ,Nashville , TN ,Inserts 21pllts,BOL#1363349932811642616426164261642623626236262362600223382233822338223382677726777267770011/12/15 08:52:0811/01/15 22:45:2309/08/15 17:04:11311/13/15 15:51:3311/12/15 08:52:08002880034Homer Cates#346514155849011/13/15 17:29:34quad graphics53052678126781267812678133981339813398100276112761127611276113481134811348110011/09/15 15:28:0311/09/15 15:28:0310/17/15 14:23:15311/13/15 15:05:3411/09/15 15:28:03002880034James DeMaree422514182765711/13/15 17:33:10bl 1678652 weigh 233485303,5308 emty00005561556155610000005561556155610011/08/15 05:17:1811/08/15 05:17:18311/13/15 13:51:2211/08/15 05:17:18002880034Jeffery Roberts517514111/06/15 00:15:073960039600396003960046800468004680000504005040050400504005040050400504000011/02/15 18:03:4211/02/15 18:03:42311/06/15 00:14:5611/02/15 18:03:42002880034Juanita Smithson437514182758611/13/15 17:14:26toy019623960039600396003960046800468004680000504005040050400504005040050400504000011/08/15 22:05:2011/08/15 22:05:2009/20/15 20:50:12411/13/15 02:04:4111/08/15 22:05:20002880034Julius Tucker384514110/22/15 19:17:44280706912852104221042210422104228242282422824200267002670026700267002986929869298690010/18/15 13:57:1410/18/15 13:57:1409/14/15 11:10:27310/22/15 18:51:0410/18/15 13:57:14002880034Lesly Antoine509514148985811/13/15 17:34:103280732807328073280740007400074000700429014290142901429014290142901429010011/09/15 14:40:3311/09/15 14:40:3311/02/15 13:49:15111/13/15 15:34:2511/09/15 14:40:33002130134Letonia Cousins190514182765111/13/15 17:11:38Aleris--CW92197--Coldwater, MI5316610821082108210828282828282820010821082108210828282828282820011/08/15 21:31:0511/08/15 21:31:0511/01/15 20:16:14411/13/15 17:03:2111/08/15 21:31:05001542834Margaret Payne451514111/06/15 00:21:093960039600396003960046800468004680000504005040050400504005040050400504000011/06/15 00:21:0911/06/15 00:21:0911/06/15 00:21:09311/06/15 00:21:0111/06/15 00:21:09002880034Maurice Williams481536871424148986011/13/15 17:19:182873428734287342873421554215542155400419534195341953419532155421554215540011/09/15 11:38:5511/02/15 10:03:1210/12/15 11:03:46111/13/15 15:09:0911/09/15 11:38:550038Patrick Carver419514182761311/13/15 17:37:26bridgestone965382721827218272182721834418344183441800376733767337673376733767337673376730011/09/15 17:34:4611/09/15 17:34:4611/09/15 17:34:46111/13/15 13:42:2211/09/15 17:34:46001607334Philip Anderson487514110/19/15 02:41:542478424784247842478431984319843198400347773477734777347773482034820348200010/16/15 22:21:0410/16/15 22:21:0410/16/15 22:21:04310/19/15 02:41:3810/16/15 22:21:04001317734Reggie Maddox166514111/06/15 07:54:02chambersburg pa2462524625246252462531825318253182500319503195031950319503355733557335570011/03/15 05:14:5611/03/15 05:14:5611/03/15 05:14:56311/06/15 07:53:1111/03/15 05:14:56001035034Ron Payne447514111/06/15 00:19:3720222022202220229222922292220020222022202220229222922292220011/05/15 10:53:1911/02/15 11:49:4910/20/15 03:21:55311/06/15 00:18:4011/05/15 10:53:19001468334Ronald Bellamy508514148985411/13/15 17:33:14volvo trks lndependence va tires bol#7713490m0153052681426814268142681432288322883228800322883228832288322883228832288322880011/12/15 16:20:5111/12/15 16:20:5111/12/15 16:20:51111/13/15 15:23:3011/12/15 16:20:51001068834Ronald Guthrie386514111/13/15 00:44:23bl 082709119 weight 13815 23 bskt seal 47566753177187187187187519551955195005895895895895195519551950011/09/15 14:28:3011/09/15 14:28:3010/21/15 14:25:30311/13/15 00:44:1011/09/15 14:28:3000929134Steve Coleman385514111/13/15 01:20:40BOL # EmptyTRL# 9638726232623262326239823982398230026232623262326239823982398230011/08/15 15:21:5311/08/15 15:21:5310/26/15 14:44:03311/13/15 01:19:2611/08/15 15:21:53002256634Steven Gannon416514182765611/13/15 17:26:36.956972097920979209792097928179281792817900225952259522595225952979529795297950011/09/15 11:00:5011/09/15 11:00:5010/26/15 13:45:44311/13/15 16:28:3811/09/15 11:00:50002698934Steven Keenan492514155849111/13/15 17:41:02carpenter co / expanded polystyrene block / wght /2895 /w104571704117041170411704124241242412424100184611846118461184612465724657246570011/09/15 16:04:1011/09/15 16:04:1010/01/15 16:21:53311/13/15 17:03:2411/09/15 16:04:10002307834Steven Stanulis#070514155849511/13/15 17:31:36bl#1690519956963418034180341803418041380413804138000421844218442184421844415944159441590011/08/15 10:54:0211/02/15 16:09:1409/07/15 13:54:14311/13/15 16:16:3711/08/15 10:54:02002880034Wesley Barlow342514148985111/13/15 17:36:30graphic packaging 220 industrial dr perry,ga535482360623606236062360629825298252982500236062360623606236062982529825298250011/09/15 11:59:0011/09/15 11:59:0010/08/15 12:15:15211/13/15 17:30:5611/09/15 11:59:00002846634William Skinner442514111/12/15 21:42:562404024040240402404024040240402404000240402404024040240402404024040240400011/12/15 14:23:3611/12/15 14:23:3611/12/15 14:23:36211/12/15 21:42:4411/12/15 14:23:3600244034
*/
?>