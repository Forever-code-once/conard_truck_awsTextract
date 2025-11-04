<?
$usetitle="Driver Safety Report";   
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
	
	//if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	//if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("n/j/Y", time());
	
	if(!isset($_POST['date_from'])) 	$_POST['date_from'] = "05/13/2013";
	if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("m/d/Y");
	
	if($_POST['date_from']=="")		$_POST['date_from']="05/13/2013";
	if($_POST['date_to']=="")		$_POST['date_to']=date("m/d/Y");
	
	$early_notice="";
	if(date("Ymd",strtotime($_POST['date_from'])) <= "20130512")		{	$_POST['date_from']="05/13/2013";		$early_notice="<b>Report not available before 05/12/2013.</b>";	}
	if(date("Ymd",strtotime($_POST['date_to'])) <= "20130512")			{	$_POST['date_to']=date("m/d/Y");		$early_notice="<b>Report not available before 05/12/2013.</b>"; 	}
	/*
	if(isset($_GET['activate']))
	{
		$sql="
		update geofence_hot_load_tracking set
			active='1'
		where id='".sql_friendly($_GET['activate'])."'	
		";	
		simple_query($sql);	
	}
	if(isset($_GET['deactivate']))
	{
		$sql="
		update geofence_hot_load_tracking set
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

	
	<h3><?= $usetitle ?>
	
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Show:
	<span class='mrr_link_like_on' onClick='mrr_show_only_summary(0);'>Summary</span>  
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_only_summary(1);'>Printable</span>  				
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(0);'>Details</span>  				
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(5);'>Good Driving Only</span>    
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(6);'>Violations Only</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(1);'>Speed Only</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(2);'>11 Hr Rule Only</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(3);'>14 Hr Rule Only</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(4);'>70 Hr Rule Only</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(7);'>Abrupt Shutdowns</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?= $early_notice ?>
	</h3>
	<br>
	<a href='report_driver_safety.php'><b>Reset Report for all drivers or a different driver.</b></a>
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
               		(".$rowp['employer_name']."):
               	</div>";
               	
               //$mrr_rep=mrr_peoplenet_driver_violations_update($driver_id,$_POST['employer_id']);	//driver, employer
          	   	
          	$data_sorter.=  "<table class='admin_menu1 tablesorter font_display_section mrr_view_details' id='table_sort_report' style='text-align:left;width:1800px;margin:10px'>";
               $data_sorter.= "<thead>
               			<tr>
               				<th><b>Date</b></th>
               				<th><b>Truck</b></th> 
               				<th class='mrr_collapse_details'><b>Miles</b></th>         				
               				<th class='mrr_collapse_details'><b>Hours<br>Driven</b></th>
               				<th class='mrr_collapse_details'><b>Hours<br>Worked</b></th>
               				<th class='mrr_collapse_details'><b>Hours<br>Rest</b></th>
               				<th class='mrr_collapse_details'><b>Weekly<br>Hours<br>Driven</b></th>
               				<th class='mrr_collapse_details'><b>Weekly<br>Hours<br>Worked</b></th>
               				<th class='mrr_collapse_details'><b>Weekly<br>Hours<br>Rest</b></th>
               				<th class='mrr_collapse_details'><b>MPH</b></th>
               				<th><b>Violation</b></th>
               				<th><b>Location</b></th>
               				<th><b>Abrupt<br>Shutdown</b></th>
               				<th><b>Excused</b></th>
               			</tr>
               		</thead>
               		<tbody>
               		";
          	
          	$sql="
          		select safety_report_violations.*,
          			(select option_values.fvalue from option_values where option_values.id=safety_report_violations.employer_id) as employer_name,
          			(select username from users where users.id=safety_report_violations.excused_by) as user_name,          			
          			(select name_truck from trucks where trucks.id=safety_report_violations.truck_id) as truck_name
          		from ".mrr_find_log_database_name()."safety_report_violations
          		where safety_report_violations.deleted=0
          			and safety_report_violations.driver_id='".sql_friendly($driver_id)."'          			
          			and safety_report_violations.linedate>='".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
          			and safety_report_violations.linedate<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
          		order by safety_report_violations.linedate asc
          	";	//and safety_report_violations.truck_id>0
          	
          	$data=simple_query($sql);
          	while($row=mysqli_fetch_array($data))
          	{  		     		
          		//display...    				
          		
          		//$id=$row['id'];
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
          		
          		$data_sorter.=  "
          			<tr class='all_rows ".$mrr_classy."'>
          				<td valign='top'>".date("m/d/Y H:i", strtotime($row['linedate']))."</td>
          				   				
          				<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['truck_name']."</a></td>
          				<td valign='top' align='right' class='mrr_collapse_details'>".number_format($row['cur_miles'],2)."</td> 				 
          				<td valign='top' align='right' class='mrr_collapse_details'>".number_format($row['cur_hours_driven'],2)."</td>
          				<td valign='top' align='right' class='mrr_collapse_details'>".number_format($row['cur_hours_worked'],2)."</td>
          				<td valign='top' align='right' class='mrr_collapse_details'>".number_format($row['cur_hours_rested'],2)."</td>
          				<td valign='top' align='right' class='mrr_collapse_details'>".number_format($row['wk_hours_driven'],2)."</td>
          				<td valign='top' align='right' class='mrr_collapse_details'>".number_format($row['wk_hours_worked'],2)."</td>
          				<td valign='top' align='right' class='mrr_collapse_details'>".number_format($row['wk_hours_rested'],2)."</td>
          				<td valign='top' align='right' class='mrr_collapse_details'>".$row['cur_speed']."</td> 
          				<td valign='top'><span".$mrr_classx.">".trim($row['violation'])."</span></td>
          				<td valign='top'>".trim($row['location'])."</td>		
          				<td valign='top'>".$abrupt_flag."</td>			
          				<td valign='top'>".$flagger."<br>".$user_mask."<br>".$ex_date."<br>".$row['excused_notes']."</td>	                					
          			</tr>
          		";	//<td valign='top'>".$row['employer_name']."</td>  
          		
          		if(substr_count($gps_truck_listing,",".$row['truck_id'].",") == 0)
          		{
          			$gps_listing.="".mrr_show_gps_points_for_truck_and_dates($row['truck_id'],$_POST['date_from'],$_POST['date_to'],$row['truck_name'])."<br><br>";
               		$gps_truck_listing.=",".$row['truck_id'].",";
          		}
          		
          		$cntr++;
               	
                         		
          	}//end safety report loop
          	
          	
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
	
	/*	
     if(isset($_POST['build_report']) && trim($data_sorter)=="")
     {
     	
     	$data_sorter.=  "<table class='admin_menu1 tablesorter font_display_section' id='table_sort_report' style='text-align:left;width:1800px;margin:10px'>";
          $data_sorter.= "<thead>
          			<tr>
          				<th><b>Date Start</b></th>
          				<th><b>Date End</b></th>
          				<th><b>Driver</b></th>
          				<th><b>Truck</b></th>
          				<th><b>Hours Driven</b></th>
          				<th><b>Hours Worked</b></th>
          				<th><b>Hours Rest</b></th>
          				<th><b>Weekly Hours Driven</b></th>
          				<th><b>Weekly Hours Worked</b></th>
          				<th><b>Weekly Hours Rest</b></th>
          				<th><b>Speed Violations</b></th>
          				<th><b>11Hr Rule</b></th>
          				<th><b>14Hr Rule</b></th>
          				<th><b>10Br Rule</b></th>
          				<th><b>70Hr Rule</b></th>
          				<th><b>34Br Rule</b></th>
          				<th><b>DOT Violations</b></th>
          				<th><b>Violation Notes</b></th>
          				<th><b>Abrupt Shutdown</b></th>
          				<th><b>Excused</b></th>    
          				<th><b>Excused By</b></th>     				
          				<th><b>Excused Details</b></th>
          			</tr>
          		</thead>
          		<tbody>
          		";
     	
     	
     	$sql="
     		select safety_report.*,
     			(select name_driver_first from drivers where drivers.id=safety_report.excused_by_id) as user_name,
     			(select name_driver_first from drivers where drivers.id=safety_report.driver_id) as driver_first_name,
               	(select name_driver_last from drivers where drivers.id=safety_report.driver_id) as driver_last_name,
     			(select name_truck from trucks where trucks.id=safety_report.truck_id) as truck_name
     		from safety_report
     			left join drivers on drivers.id=safety_report.driver_id
     			left join option_values on option_values.id=drivers.employer_id
     		where safety_report.deleted=0
     			".$mrr_adder."
     			and drivers.deleted=0
     			and option_values.deleted=0
     			".($_POST['employer_id'] > 0 && $_POST['employer_id']!="" ? " and drivers.employer_id='".sql_friendly($_POST['employer_id'])."'" : "")."
     			".($_POST['driver_id'] > 0 && $_POST['driver_id']!="" ? " and safety_report.driver_id='".sql_friendly($_POST['driver_id'])."'" : "")."
     			and safety_report.linedate_start>='".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
     			and safety_report.linedate_start<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
     		order by safety_report.linedate_start asc
     	";
     	$data=simple_query($sql);
     	while($row=mysqli_fetch_array($data))
     	{  		     		
     		//display...    		
     		$user_mask="";
     		if($row['excused_by_id'] > 0)    		$user_mask= "<a href='admin_users.php?eid=".$row['excused_by_id']."' target='_blank'>".$rowd['user_name']."</a>";//
     		          
     		$d1_mask="";
     		if($row['driver_id'] > 0)			$d1_mask="<a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driver_first_name']." ".$row['driver_last_name']."</a>";
     		
     		//$id=$row['id'];
     		$gps_dist=$row['distance_feet'];
     		
     		$flagger="";
     		if($row['excuse_flag'] == 1)			$flagger="Excused";
     		
     		$mrr_classy="good_driving";
     		if($row['speeding_violations'] > 0 || $row['dot_violations'] > 0)		$mrr_classy="violations";	
     		
     		$tot_11_hrs+=$row['hour_11_violation'];
      	    	$tot_14_hrs+=$row['hour_14_violation'];
      	    	$tot_10_brk+=$row['break_10_violation'];
      	    	$tot_70_hrs+=$row['hour_70_violation'];
      	    	$tot_34_brk+=$row['break_34_violation'];
     		
     		$abrupt_flag="";
     		if($row['abrupt_shutdown_flag'] > 0)
     		{
     			$abrupt_flag="Yes";	
     			$abrupt+=$row['abrupt_shutdown_flag'];	
     		}
     		
     		$view_linker="<a href='report_peoplenet_drivers.php?driver_id=".$row['driver_id']."&date_from=".date("m_d_Y", strtotime($row['linedate_start']))."&date_to=".date("m_d_Y", strtotime($row['linedate_end']))."' target='_blank'>
     						".date("m/d/Y H:i", strtotime($row['linedate_start']))."
     					</a>";
     		
     		$data_sorter.=  "
     			<tr class='all_rows ".$mrr_classy."'>
     				<td valign='top'>".$view_linker."</td>
     				<td valign='top'>".date("m/d/Y H:i", strtotime($row['linedate_end']))."</td>
     				<td valign='top'>".$d1_mask."</td>
     				<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['truck_name']."</a></td>
     				<td valign='top'>".number_format($row['hours_driven'],2)."</td>
     				<td valign='top'>".number_format($row['hours_worked'],2)."</td>
     				<td valign='top'>".number_format($row['hours_rested'],2)."</td>
     				<td valign='top'>".number_format($row['wk_hours_driven'],2)."</td>
     				<td valign='top'>".number_format($row['wk_hours_worked'],2)."</td>
     				<td valign='top'>".number_format($row['wk_hours_rested'],2)."</td>
     				<td valign='top'>".number_format($row['speeding_violations'],0)."</td>
     				<td valign='top'>".number_format($row['hour_11_violation'],0)."</td>
     				<td valign='top'>".number_format($row['hour_14_violation'],0)."</td>
     				<td valign='top'>".number_format($row['break_10_violation'],0)."</td>
     				<td valign='top'>".number_format($row['hour_70_violation'],0)."</td>
     				<td valign='top'>".number_format($row['break_34_violation'],0)."</td>
     				<td valign='top'>".number_format($row['dot_violations'],0)."</td>				
     				<td valign='top'>".$row['violation_notes']."</td>	
     				<td valign='top'>".$abrupt_flag."</td>			
     				<td valign='top'>".$flagger."</td>
     				<td valign='top'>".$user_mask."</td>
     				<td valign='top'>".$row['excuse_notes']."</td>	                					
     			</tr>
     		";
     		
     		$cntr++;
          	
          	$hours_driven+=$row['hours_driven'];
          	$hours_worked+=$row['hours_worked'];
          	$hours_rested+=$row['hours_rested'];
          	$wk_hours_driven+=$row['wk_hours_driven'];
          	$wk_hours_worked+=$row['wk_hours_worked'];
          	$wk_hours_rested+=$row['wk_hours_rested'];
          	
          	$speeding_violations+=$row['speeding_violations']; 
          	$dot_violations+=$row['dot_violations']; 
                    		
     	}//end safety report loop
     	$data_sorter.= "
          		</tbody>
          		<tr>
     				<td valign='top'>".$cntr."</td>
     				<td valign='top' colspan='3'>Total</td>
     				<td valign='top'>".number_format($hours_driven,2)."</td>
     				<td valign='top'>".number_format($hours_worked,2)."</td>
     				<td valign='top'>".number_format($hours_rested,2)."</td>
     				<td valign='top'>".number_format($wk_hours_driven,2)."</td>
     				<td valign='top'>".number_format($wk_hours_worked,2)."</td>
     				<td valign='top'>".number_format($wk_hours_rested,2)."</td>
     				<td valign='top'>".number_format($speeding_violations,0)."</td>
     				<td valign='top'>".number_format($tot_11_hrs,0)."</td>
     				<td valign='top'>".number_format($tot_14_hrs,0)."</td>
     				<td valign='top'>".number_format($tot_10_brk,0)."</td>
     				<td valign='top'>".number_format($tot_70_hrs,0)."</td>
     				<td valign='top'>".number_format($tot_34_brk,0)."</td>
     				<td valign='top'>".number_format($dot_violations,0)."</td>
     				<td valign='top'></td>	
     				<td valign='top'>".number_format($abrupt,0)."</td>							
     				<td valign='top'></td>
     				<td valign='top'></td>
     				<td valign='top'></td>	                					
     			</tr>
          		</table>
          	";
     	
	}
	*/
	echo $data_sorter;
	
	//echo "<br><hr><br>".$mrr_rep."<br><hr>";
	?>
     
     <div style='clear:both'></div>
          <table class='admin_menu1 font_display_section mrr_view_summary' style='text-align:left; width:1400px;margin:10px'>
          	<tr>
          		<td valign='top' colspan='7'><center><b>FULL SAFETY REPORT SUMMARY</b></center></td>
          	</tr>
          	<tr>
          		<td valign='top'><b>Driver</b></td>     		
          		<td valign='top' align='right'><span class='mrr_sr_abrupt_stop'>'Abrupt Shutdowns'</span></td>
          		<td valign='top' align='right'><span class='mrr_sr_speeding'>Speeding violations</span></td>
          		<td valign='top' align='right'><span class='mrr_sr_11hr_rule'>11-Hour Driving Rule violations</span></td>
          		<td valign='top' align='right'><span class='mrr_sr_14hr_rule'>14-Hour Working Rule Violations</span></td>
          		<td valign='top' align='right'><span class='mrr_sr_70hr_rule'>70-Hour Work Week Rule violations</span></td>     		
          		<td valign='top' align='right'><b>DOT Violations</b></td>
          	</tr>
          	<?= $summary_sorter ?>
          	<tr>
          		<td valign='top' colspan='7'><hr></td>
          	</tr>
          	<tr>
          		<td valign='top'><b>Total</b></td>     
          		<td valign='top' align='right'><span class='mrr_sr_abrupt_stop'><?= $all_abrupt ?></span></td>		
          		<td valign='top' align='right'><span class='mrr_sr_speeding'><?= $all_speeding ?></span></td>
          		<td valign='top' align='right'><span class='mrr_sr_11hr_rule'><?= $all_tot_11_hrs ?></span></td>
          		<td valign='top' align='right'><span class='mrr_sr_14hr_rule'><?= $all_tot_14_hrs ?></span></td>
          		<td valign='top' align='right'><span class='mrr_sr_70hr_rule'><?= $all_tot_70_hrs ?></span></td>     		
          		<td valign='top' align='right'><b><?= $all_dot_violations ?></b></td>
          	</tr>
          </table>
     <?
     	//echo "<br>Driver Selected was ".$_POST['driver_id'].".<br>";
     ?>
     <div style='clear:both'></div>
</div>
<?
	$landscape=1;
	$form_mode=1;
?>
<script type='text/javascript'>
		
	$().ready(function() {
		
		$('.tablesorter').tablesorter();
		//$('.datepicker').datepicker();
		
		//setTimeout("location.reload();", (60 * 1000));		//ten minutes...600 seconds...1000=1 second
		
		//printing like the accounting side....
   		print_block='printable_area1';
   			
   		if(print_block!='')
   		{	
   			obj_holder = $('#'+print_block+'');
			obj_wrapper_holder = "";
			
			$(obj_holder).wrap("<div id='"+print_block+"_print_wrapper' />");
			
			obj_wrapper_holder = $('#'+print_block+'_print_wrapper');
   		}	
   		
   		mrr_show_by_code(0);
	});
	
	function mrr_print_report() 
     {
		mrr_show_only_summary(1);
		$.ajax({
			url: "print_report.php",
			dataType: "xml",
			type: "post",
			data: {
				script_name: "<?=$_SERVER['SCRIPT_NAME']?>",
				report_title: "<?=$usetitle?>",
				'display_mode':"<?=$landscape?>",
				'form_mode':"<?=$form_mode?>",
				report_contents: encodeURIComponent(html_entity_decode($(obj_wrapper_holder).html()))
			},
			error: function() {
				$.prompt("General error printing report");
				//$('#'+print_icon_holder).attr('src','images/printer.png');
			},
			success: function(xml) {
				//$('#'+print_icon_holder).attr('src','images/printer.png');
				if($(xml).find('PDFName').text() == '') {
					$.prompt("Error reading filename");
				} else {
					window.open($(xml).find('PDFName').text());
				}
			}
		});
     }
	
	function mrr_show_only_summary(cd)
	{		
		if(cd==1)
		{	//remove the parts for the printed version...
			$('.mrr_view_summary').hide();
			$('.mrr_view_details').show();
			$('.mrr_collapse_details').hide();
		}
		else
		{	//show only single line summary per driver...	
			$('.mrr_view_summary').show();
			$('.mrr_view_details').hide();
			$('.mrr_collapse_details').hide();
		}		
	}
	
	function mrr_show_by_code(cd)
	{
		$('.mrr_view_details').show();
		$('.mrr_view_summary').hide();
		$('.mrr_collapse_details').show();
		
		$('.all_rows').show();
		if(cd > 0)	$('.all_rows').hide();
		
		if(cd==1)		$('.speeding').show();
		if(cd==2)		$('.overdriven').show();
		if(cd==3)		$('.overworked').show();
		if(cd==4)		$('.overweeked').show();
		if(cd==5)		$('.good_driving').show();
		if(cd==6)		$('.violations').show();
		
		if(cd==7)		$('.abrupt_shutdown').show();	
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