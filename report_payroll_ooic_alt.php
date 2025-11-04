<? $usetitle = "Report - Owner Operators V2" ?>
<? include('header.php') ?>
<?
	$use_title = "Report - Owner Operator V2";
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	
	if(!isset($_POST['date_from'])) 			$_POST['date_from'] = date("n/1/Y", time());
	if(!isset($_POST['date_to'])) 			$_POST['date_to'] = date("n/j/Y", time());
	if(!isset($_POST['driver_id'])) 			$_POST['driver_id'] = 0;
	if(!isset($_POST['employer_id'])) 			$_POST['employer_id'] = 0;
	if(!isset($_POST['payroll_mode']))			$_POST['payroll_mode']=0;
	
	//new email sending line to submit and send the email directly from this report. 
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))		$_POST['build_report'] = 1;
	
	
	if(!isset($_POST['report_payroll_date'])) 	$_POST['report_payroll_date'] ="";
		
	$truck_pay_array[] = array();
	$total_driver_pay = 0;
	$running_total = 0;
    
    $driver_ooic_rate=(int)$defaultsarray['ooic_rate_load_percentage'];
	
	$mrr_warning_required=0;
	
	echo "<table border='0' width='1200'>";
    echo "<tr><td valign='bottom'>";
    
	$rfilter = new report_filter();
	$rfilter->show_driver 		= true;
	$rfilter->show_payroll_date	= true;
	$rfilter->show_employers 	= true;
	$rfilter->summary_only	 	= true;
	$rfilter->payroll_mode	 	= true;
	$rfilter->team_choice	 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->mrr_send_email_here	= true;
	$rfilter->show_filter();
    
	echo "</td><td valign='bottom'>";
    echo "<p>Dispatches in <span class='mrr_highlight_ooic_unmatched'>Red Text</span> do not match the current <span class='mrr_highlight_ooic_unmatched'>".$driver_ooic_rate."%</span> Payroll Percentage Rate</p>";
    echo "</td></tr>";
    echo "</table>";

	
	$driver_pay_mode=$_POST['payroll_mode'];
	
	$driver_ooic_rate=(int)$defaultsarray['ooic_rate_load_percentage'];
	
	$skip_overtime_separation=0;
	
	if( date("Ymd",strtotime($_POST['date_to'])) >= "20151123")	$skip_overtime_separation=1;
	
	$use_starting_point=$_POST['date_from'];			//save starting point for what should be displayed...what is really on this payroll report.
	if($_POST['report_payroll_date']=="") 	$_POST['report_payroll_date'] = $_POST['date_from'];
	$_POST['date_from'] = $_POST['report_payroll_date'];	//now use the start of the payroll period in the filter to get all the items, even if prior to the starting point.
		
	function get_master_query() 
	{
		global $driver_pay_mode;
		
		$employ_adder="";
		$employ_adder2="";
		if($_POST['employer_id'] > 0)
		{
			$employ_adder=" and (trucks_log.employer_id = '".sql_friendly($_POST['employer_id'])."')";		// or drivers2.employer_id = '".sql_friendly($_POST['employer_id'])."'
			$employ_adder=" and (
					(trucks_log.employer_id > 0 and trucks_log.employer_id = '".sql_friendly($_POST['employer_id'])."')
					or
					(trucks_log.employer_id=0 and drivers.employer_id='".sql_friendly($_POST['employer_id'])."')
			)";			
			$employ_adder2=" and (drivers2.employer_id = '".sql_friendly($_POST['employer_id'])."')";		// trucks_log.employer_id = '".sql_friendly($_POST['employer_id'])."' or 
		}
		
		$field01="labor_per_mile";
		$field02="labor_per_hour";
		
		$field1="charged_per_mile";
		$field2="charged_per_hour";
		$field3="charged_per_mile_team";
		$field4="charged_per_hour_team";
		
		if($driver_pay_mode ==1)
		{
			$field01="driver1_pay_per_mile";
			$field02="driver1_pay_per_mile";
			
			$field1="pay_per_mile";
			$field2="pay_per_hour";
			$field3="pay_per_mile_team";
			$field4="pay_per_hour_team";	
		}
				
				//and trucks.owner_operated > 0
		$sql = "
			select trucks_log.*,
				trucks.owner_operated as truck_owner_operator,
				drivers.owner_operator as driver_owner_operator,
				drivers.id as driver_id,
				drivers.driver_email,
				drivers.name_driver_first,
				drivers.name_driver_last,
				drivers.employer_id as driver1_employer_id,				
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company,
				drivers.".$field1." as charged_per_mile,
				drivers.".$field2." as charged_per_hour,
				drivers.".$field3." as charged_per_mile_team,
				drivers.".$field4." as charged_per_hour_team,
				if(trucks_log.".$field01." > 0, trucks_log.".$field01.", drivers.".$field1.") as dispatch_charged_per_mile,
				if(trucks_log.".$field02." > 0, trucks_log.".$field02.", drivers.".$field2.") as dispatch_charged_per_hour,
				trucks_log.hours_worked,
				drivers2.employer_id as driver2_employer_id,
				drivers2.driver_email as driver2_email,
				drivers2.name_driver_first as name2_driver_first,
				drivers2.name_driver_last as name2_driver_last,
				drivers2.".$field1." as driver2_charged_per_mile,
				drivers2.".$field2." as driver2_charged_per_hour,
				drivers2.".$field3." as driver2_charged_per_mile_team,
				drivers2.".$field4." as driver2_charged_per_hour_team,
				
				load_handler.actual_bill_customer,
				(select ifnull(CONCAT(t2.origin,', ',t2.origin_state,' to ',t2.destination,', ',t2.destination_state),'') from trucks_log t2 where t2.load_handler_id=load_handler.id and t2.deleted=0 and t2.driver_id!=drivers.id and t2.driver2_id!=drivers.id order by t2.id desc limit 1) as disp_route,  
				
				1 as driver_position
			
			from trucks_log
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				left join load_handler on load_handler.id = trucks_log.load_handler_id
				left join drivers drivers2 on drivers2.id = trucks_log.driver2_id
			where trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."' 
				and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
				and trucks_log.deleted = 0
				
				
				and drivers.owner_operator > 0
				
				".($_POST['driver_id'] > 0 ? " and trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".$employ_adder."
				".($_POST['team_choice'] == 0 ? "" : ($_POST['team_choice'] == '1' ? " and trucks_log.driver2_id > 0 " : " and trucks_log.driver2_id = 0 "))."
			
			union all
			
			select trucks_log.*,
				trucks.owner_operated as truck_owner_operator,
				drivers2.owner_operator as driver_owner_operator,
				drivers2.id as driver_id,
				drivers2.driver_email,
				drivers2.name_driver_first,
				drivers2.name_driver_last,
				drivers2.employer_id as driver1_employer_id,	
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company,
				drivers2.".$field1.",
				drivers2.".$field2.",
				drivers2.".$field3.",
				drivers2.".$field4.",
				if(trucks_log.".$field01." > 0, trucks_log.".$field01.", drivers.".$field1.") as dispatch_charged_per_mile,
				if(trucks_log.".$field02." > 0, trucks_log.".$field02.", drivers.".$field2.") as dispatch_charged_per_hour,
				trucks_log.hours_worked,
				drivers.employer_id as driver2_employer_id,
				drivers.driver_email as driver2_email,
				drivers.name_driver_first as name2_driver_first,
				drivers.name_driver_last as name2_driver_last,
				drivers.".$field1." as driver2_charged_per_mile,
				drivers.".$field2." as driver2_charged_per_hour,
				drivers.".$field3." as driver2_charged_per_mile_team,
				drivers.".$field4." as driver2_charged_per_hour_team,
				
				load_handler.actual_bill_customer,
				(select ifnull(CONCAT(t2.origin,', ',t2.origin_state,' to ',t2.destination,', ',t2.destination_state),'') from trucks_log t2 where t2.load_handler_id=load_handler.id and t2.deleted=0 and t2.driver_id!=drivers.id and t2.driver2_id!=drivers.id order by t2.id desc limit 1) as disp_route,  
				
				2
			
			from trucks_log
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				left join load_handler on load_handler.id = trucks_log.load_handler_id
				left join drivers drivers2 on drivers2.id = trucks_log.driver2_id
			where trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."' 
				and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
				and trucks_log.deleted = 0
				and trucks_log.driver2_id > 0
				
				
				and drivers2.owner_operator > 0
				and drivers.owner_operator > 0
				
				".($_POST['driver_id'] > 0 ? " and trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".$employ_adder2."
				".($_POST['team_choice'] == 0 ? "" : ($_POST['team_choice'] == '1' ? " and trucks_log.driver2_id > 0 " : " and trucks_log.driver2_id = 0 "))."
			
			order by name_driver_last, name_driver_first, linedate
		";
				//and trucks.owner_operated > 0
		
		//echo "<br><br><br>Query is:<br>---".$sql."---<br>";
		$data = simple_query($sql);
		
		return $data;
	}
?>


<? if(isset($_POST['build_report'])) { ?>
	<?
	
	$print_company="All Employers";
	$print_driver="All Drivers";
	$print_range="All";
	if(isset($_POST['employer_id']) && $_POST['employer_id'] > 0)
	{
		$print_company=get_option_value_by_id($_POST['employer_id']);	
	}
	if(isset($_POST['driver_id']) && $_POST['driver_id'] > 0)
	{
		$print_driver=mrr_get_driver_name($_POST['driver_id']);
	}
	if(isset($_POST['date_from']) && isset($_POST['date_to']))
	{			
		$print_range="".date("m/d/Y", strtotime($use_starting_point))." to ".date("m/d/Y", strtotime($_POST['date_to']))."";
	}
	$print_header_label="<b>".$print_company."</b><br>".$print_driver."<br>Date Range: ".$print_range."<br>";
	
	$mrr_all_drivers=0;
	$mrr_all_drivers_arr[0]=0;
	
	$mrr_report_capture="";
	
	$grand_tot_ooic=0;
	$grand_tot_ooic_fuel=0;
	$grand_tot_ooic_insurance=0;
	$grand_tot_ooic_expenses=0;
	
	function mrr_find_ooic_driver_time_off($driver_id,$date_from,$date_to)
	{
		$time_off_display="";
		$driver_id=(int) $driver_id;
		
		//<a href=\"admin_drivers.php?id=',drivers.id,'\" target=\"view_driver_',drivers.id,'\">', name_driver_first, ' ', name_driver_last,'</a>
		$sql2 = "
			select linedate_start as linedate,
				linedate_end as linedate_to,
				driver_id as driver,
				concat('Unavailable:  ',date_format(linedate_start, '%b %e'), ' - ', date_format(linedate_end, '%b %e'),': <b>',reason_unavailable,'</b>') as c_reason,
				reason_unavailable as c_reason2,
				1 as from_calendar,
				drivers_unavailable.id as calendar_id,
				'none' as desc_long
				
			from drivers, drivers_unavailable
			where drivers.deleted <= 0
				and drivers.active > 0
				and drivers_unavailable.deleted <= 0
				and drivers.id = drivers_unavailable.driver_id
				and (
					(drivers_unavailable.linedate_start >= '".date("Y-m-d",strtotime($date_from))." 00:00:00' and drivers_unavailable.linedate_start <= '".date("Y-m-d",strtotime($date_to))." 23:59:59')
					or
					(drivers_unavailable.linedate_end >= '".date("Y-m-d",strtotime($date_from))." 00:00:00' and drivers_unavailable.linedate_end <= '".date("Y-m-d",strtotime($date_to))." 23:59:59')
					or
					(drivers_unavailable.linedate_start < '".date("Y-m-d",strtotime($date_from))." 00:00:00' and drivers_unavailable.linedate_end >= '".date("Y-m-d",strtotime($date_from))." 23:59:59')
					or
					(drivers_unavailable.linedate_start <= '".date("Y-m-d",strtotime($date_to))." 00:00:00' and drivers_unavailable.linedate_end > '".date("Y-m-d",strtotime($date_to))." 23:59:59')
				)
				and drivers.id='".$driver_id."'			
			
			order by linedate asc
		";	//month(linedate), day(linedate)
		$data2 = simple_query($sql2);	
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
				/*			
				if($row2['from_calendar']!=2)
				{
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
				else
				{
					$res.=	"<li>";
					$res.=		"<h3><span>".date("M, j", strtotime($row2['linedate']))."</span>";
					
					//$res.=			"<span onClick='mrr_edit_driver_unavailable(".$row2['calendar_id'].",".$row2['driver'].",\"".$tmp_from."\",\"".$tmp_to."\",\"".$tmp_reason."\");' style='margin-left:120px;'>
					//					<img src='".$new_style_path."blue_icon1.png' alt='add'>
					//				</span>";	
					//$res.=			"<a href='javascript:".$use_java."'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";	
					
					$res.=		"</h3>";
					$res.=		"<p>".trim($row2['c_reason'])."</p> ";		
					$res.=	"</li>";
				}
				*/
				
				if(!isset($_POST['summary_only'])) 
     			{
     				$time_off_display.="
     					<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$driver_id."_reg'>
     						<td colspan='3'>".date("M, j Y", strtotime($row2['linedate']))." - ".date("M, j Y", strtotime($row2['linedate_to']))."</td>
     						<td align='right'>TIME OFF</td>
     						<td></td>
     						<td colspan='15'>".trim($row2['c_reason2'])."</td>
     					</tr>
     				";
     			}
		}
		//$time_off_display="";
		
		
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
     			and drivers.id='".$driver_id."'
     			and (
					(driver_vacation_advances.linedate_start >= '".date("Y-m-d",strtotime($date_from))." 00:00:00' and driver_vacation_advances.linedate_start <= '".date("Y-m-d",strtotime($date_to))." 23:59:59')
					or
					(driver_vacation_advances.linedate_end >= '".date("Y-m-d",strtotime($date_from))." 00:00:00' and driver_vacation_advances.linedate_end <= '".date("Y-m-d",strtotime($date_to))." 23:59:59')
					or
					(driver_vacation_advances.linedate_start < '".date("Y-m-d",strtotime($date_from))." 00:00:00' and driver_vacation_advances.linedate_end >= '".date("Y-m-d",strtotime($date_from))." 23:59:59')
					or
					(driver_vacation_advances.linedate_start <= '".date("Y-m-d",strtotime($date_to))." 00:00:00' and driver_vacation_advances.linedate_end > '".date("Y-m-d",strtotime($date_to))." 23:59:59')
				) 			
     			
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
					
				/*							
				$res.=	"<li>";
				$res.=		"<h3><span>".$vaca_label."</span>";
				$res.=			"<span style='margin-left:120px;' onclick='window.open(\"drivers_vacation_advances.php?driver_id=".$rowv['driver_id']."&use_id=0\");'>
									<img src='".$new_style_path."blue_icon1.png' alt='add'>
								</span>";	
				$res.=			"<a href='drivers_vacation_advances.php?driver_id=".$rowv['driver_id']."&use_id=".$rowv['id']."' target='_blank'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";	
				$res.=		"</h3>";
				$res.=		"<p><b>".$driver." ".$ranger."</b><br>".trim($rowv['comments'])."</p> ";		
				$res.=	"</li>";
				*/
				
				
				if(!isset($_POST['summary_only'])) 
     			{
     				$time_off_display.="
     					<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$driver_id."_reg'>
     						<td colspan='3'>".$ranger."</td>
     						<td align='right'>".$vaca_label."</td>
     						<td></td>
     						<td colspan='15'>".trim($rowv['comments'])."</td>
     					</tr>     					
     				";	//
     			}
		}
		
		$sql="
			select driver_absenses.*,
				option_values.fname,
				option_values.fvalue
				
			from driver_absenses
				left join option_values on option_values.id=driver_absenses.driver_code
			where driver_absenses.driver_id='".sql_friendly($driver_id)."'
				and driver_absenses.linedate >= '".date("Y-m-d",strtotime($date_from))." 00:00:00' and driver_absenses.linedate <= '".date("Y-m-d",strtotime($date_to))." 23:59:59'
				and driver_absenses.deleted=0
			order by driver_absenses.linedate desc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{			
			if(!isset($_POST['summary_only'])) 
     		{
     			$time_off_display.="
     				<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$driver_id."_reg'>
     					<td colspan='3'>".date("m/d/Y",strtotime($row['linedate']))."</td>
     					<td align='right'>".$row['fname']."</td>
     					<td></td>
     					<td colspan='15'>".$row['driver_reason']."</td>
     				</tr>     					
     			";	//
     		}
		}
				
		return $time_off_display;
	}
	
	function mrr_find_if_emailed_driver($driver_id)
    {
         $last_date_sent="";
         $sqlv = "		
			select *   					
     		from driver_ooic_payroll_emails
     		where deleted <= 0
     			and driver_id='".$driver_id."'
     			and linedate_start = '".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00' 
     			and linedate_end = '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
     			
     		order by linedate_added desc
				";
         $datav = simple_query($sqlv);
         if($rowv = mysqli_fetch_array($datav)) 
         {
              $last_date_sent=date("m/d/Y H:i",strtotime($rowv['linedate_added']));
         }
         return $last_date_sent;
    }
		
	function show_driver_totals($truck_id=0,$driver_id=0) 
	{
		global $driver_pay;
		global $driver_miles;
		global $driver_pay_miles;
		global $driver_pay_hours;	
		global $driver_pay_ooic;	
		
		global $mrr_driver_pay_miles;
		global $mrr_driver_pay_hours;		
		global $mrr_driver_pay_ooic;			
		
		global $driver_miles_deadhead;
		global $current_driver;
		global $driver_expenses;
		global $driver_hours_worked;
		global $driver_pre_hours_worked;
		global $driver_team_miles;
		global $driver_pay_team;
		global $driver_team_miles_deadhead;
		global $driver_hourly_miles;
		global $total_driver_pay;
		global $driver_hourly_miles_deadhead;
		global $running_total;
		
		global $grand_tot_ooic;
		global $grand_tot_ooic_fuel;
		global $grand_tot_ooic_insurance;
		global $grand_tot_ooic_expenses;
		
		global $mrr_report_capture;
		
		global $mrr_warning_required;

		global $datasource;
		
		$grand_tot_ooic+=$mrr_driver_pay_ooic;
		
		
		$show_html="";
		
				
		$time_off_disp=mrr_find_ooic_driver_time_off($driver_id,$_POST['date_from'],$_POST['date_to']);
		$show_html.=$time_off_disp;
  
		$misc_id=0;
		$amnt=0;
		$amnt_desc="";
        $sqld="
			select *
			from driver_ooic_misc_exp
			where driver_id='".sql_friendly($driver_id)."'
				and linedate_from = '".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00' and linedate_to = '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
			order by id desc
		";
        $datad=simple_query($sqld);
        if($rowd=mysqli_fetch_array($datad))
        {
            $misc_id=$rowd['id'];
            $amnt=$rowd['misc_amount'];
            $amnt_desc=trim($rowd['misc_desc']);
        }
        else
        {
            $sqld="
                insert into driver_ooic_misc_exp
                    (id,driver_id,linedate_from,linedate_to,misc_amount,misc_desc)
                values
                    (NULL,'".sql_friendly($driver_id)."','".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00','".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59','0.00','')
		    ";
            simple_query($sqld);
            $misc_id = mysqli_insert_id($datasource);
        }
        $show_html.="
			<tr style='background-color:#d0deff' class='next_driver_reg begin_driver_".$driver_id."_reg'>
				<td colspan='3'>Misc Driver Pay</td>
				<td align='right'>$ <input type='text' name='update_ooic_misc_amnt_".$misc_id."' id='update_ooic_misc_amnt_".$misc_id."' value='".number_format($amnt,2)."' style='text-align:right; width:75px;' onChange='mrr_ooic_auto_update_misc(".$misc_id.");'></td>
				<td colspan='18'><input type='text' name='update_ooic_misc_".$misc_id."' id='update_ooic_misc_".$misc_id."' value='".$amnt_desc."' style='width:475px;' onChange='mrr_ooic_auto_update_misc(".$misc_id.");'></td>
			</tr>
		";
		
  
  
		
		//Find Fuel expenses
		$ooic_reg_exp=0;
		$ooic_fuel_exp=0;		
		$ooic_insurance=0;
		if($truck_id > 0 || $driver_id > 0)
		{
			$ooic_fuel_exp=mrr_pull_income_fuel_exp($truck_id,$driver_id,$_POST['date_from'],$_POST['date_to']);
			//$ooic_insurance=mrr_pull_ooic_insurance_exp($truck_id,$driver_id,$_POST['date_from'],$_POST['date_to']);
			//$ooic_reg_exp=mrr_pull_ooic_exp($truck_id,$driver_id,$_POST['date_from'],$_POST['date_to']);
		}	
			
		$grand_tot_ooic_fuel+=$ooic_fuel_exp;
		$grand_tot_ooic_insurance+=$ooic_insurance;
		$grand_tot_ooic_expenses+=$ooic_reg_exp;
		
		$driver_hours_worked-=$driver_pre_hours_worked;
			
		if($driver_expenses > 0) 
		{
			$show_html.="
				<tr style='background-color:#d0deff' class='next_driver_reg begin_driver_".$driver_id."_reg'>
					<td colspan='3'>Total Driver Expenses</td>
					<td align='right'>$".money_format('',$driver_expenses)."</td>
					<td colspan='18'>&nbsp;</td>
				</tr>
			";
             $mrr_warning_required++;
		}
		
		if($driver_team_miles > 0) 
		{
			$show_html.="
				<tr style='background-color:#d0deff' class='next_driver_reg begin_driver_".$driver_id."_reg'>
					<td colspan='3'>Team Driver Totals: </td>
					
					<td align='right'>$".money_format('', $driver_pay_team)."</td>
					<td align='right'>".number_format($driver_team_miles)."</td>
					<td align='right'>".number_format($driver_team_miles_deadhead)."</td>
					<td align='right'>".number_format($driver_team_miles + $driver_team_miles_deadhead)."</td>		
								
					<td align='right'>&nbsp;</td>		
					<td align='right'>&nbsp;</td>				
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					
					<td colspan='9'>&nbsp;</td>
				</tr>
			";
		}
		
		if($driver_pay_ooic) 
		{
			/*	
			$show_html.="
				<tr style='background-color:#d0deff' class='next_driver_reg begin_driver_".$driver_id."_reg'>
					<td colspan='3'>Driver O.O./I.C. Rate Total: </td>
					
					<td align='right'>$".money_format('',$driver_pay_ooic)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>$".money_format('',$mrr_driver_pay_ooic)."</td>
									
					<td colspan='10'>
						&nbsp;
					</td>
				</tr>	
			";	
				
			$show_html.="
				<tr style='background-color:#d0deff' class='next_driver_reg begin_driver_".$driver_id."_reg'>
					<td colspan='3'>O.O./I.C. Fuel Expenses: </td>
					
					<td align='right'>$".money_format('',$ooic_fuel_exp)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>$".money_format('',$ooic_fuel_exp)."</td>
									
					<td colspan='10'>
						<b>Fuel</b> 			
					</td>
				</tr>	
			";
			
			$show_html.="
				<tr style='background-color:#d0deff' class='next_driver_reg begin_driver_".$driver_id."_reg'>
					<td colspan='3'>O.O./I.C. Expenses: </td>
					
					<td align='right'>$".money_format('',$ooic_reg_exp)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>$".money_format('',$ooic_reg_exp)."</td>
									
					<td colspan='10'>
						<b>Expenses</b> 					
					</td>
				</tr>	
			";
			$show_html.="
				<tr style='background-color:#d0deff' class='next_driver_reg begin_driver_".$driver_id."_reg'>
					<td colspan='3'>O.O./I.C. Insurance: </td>
					
					<td align='right'>$".money_format('',$ooic_insurance)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>$".money_format('',$ooic_insurance)."</td>
									
					<td colspan='10'>
						<b>Insurance</b> 
											
					</td>
				</tr>	
			";
			*/
		}
        
        $driver_pay_ooic+=$amnt;
        $mrr_driver_pay_ooic+=$amnt;
		
		if(!isset($ooic_expenses))      $ooic_expenses=0;
		$total_driver_pay += ($driver_pay_miles + $driver_pay_hours + $driver_pay_team + $amnt);	//$driver_pay + $driver_pay_ooic
		//$total_driver_pay += $driver_miles + $driver_miles_deadhead + $driver_hourly_miles + $driver_hourly_miles_deadhead + $driver_team_miles_deadhead;
		$running_total += ($driver_pay_miles + $driver_pay_hours + $driver_pay_team + $amnt);		//$driver_pay + $driver_pay_ooic
		$show_html.="
			<tr style='background-color:#d0deff' class='next_driver_reg begin_driver_".$driver_id."_reg'>
				<td colspan='3'>Total Driver O.O./I.C.: </td>
				
				<td align='right'>$".money_format('',$driver_pay_ooic - $ooic_expenses)."</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>&nbsp;</td>
				
				<td align='right'>&nbsp;</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>&nbsp;</td>				
				<td align='right'>$".money_format('',$mrr_driver_pay_ooic - $ooic_expenses)."</td>
				
				<td colspan='9'>
					
					<input type='button' value='E-Mail Driver' onClick='mrr_email_ooic_report(".$driver_id.");'>	
					<a name='driver_".$driver_id."_marker' class='driver_".$driver_id."_marker'>&nbsp;</a>	
					
				</td>
			</tr>			
		";      //		".mrr_find_if_emailed_driver($driver_id)."
		//<input type='button' value='Reload Report' onClick='mrr_reload_ooic_report(".$driver_id.");'>
		//echo $mrr_report_capture;
		echo $show_html;
		
		$html=$mrr_report_capture.$show_html;
		
		mrr_add_driver_ooic_payroll_emails($driver_id,$_POST['date_from'],$_POST['date_to'],$html,$driver_pay_ooic,$ooic_fuel_exp,$ooic_reg_exp,$ooic_insurance);
		
		$mrr_report_capture="";
	}
	
	$pay_header1="Labor Mile";
	$pay_header2="Pay Mile";
	$pay_header3="Labor Hour";
	$pay_header4="Billed Customer";
	$pay_header5="O.O.I.C. Rate";
	if($_POST['employer_id'] == 170)	
	{
		$pay_header1="&nbsp;";
		$pay_header2="Price";
		$pay_header3="Fuel";
		$pay_header4="Billed Customer";
		$pay_header5="O.O.I.C. Rate";
	}
	
	$header_columns = "
			<td>Load ID</td>
			<td>Dispatch ID</td>
			<td>Origin</td>
			<td>Destination</td>
			<td align='left' colspan='6'>O.O.I.C. Shared or Switch</td>
			<td align='right' nowrap title='Switch Expense for this O.O.I.C. Shared Route'>SwExp</td>
			<td align='right' nowrap>".$pay_header4."</td>
			<td align='right' nowrap>".$pay_header5."</td>
			<td>Date</td>
			<td>Truck</td>
			<td>Trailer</td>
			<td>Customer</td>			
	";
	
	//$_SESSION['user_id']
	/*
			<td align='right'>Miles</td>
			<td align='right'>Deadhead</td>
			<td align='right'>Total Miles</td>
			<td align='right'>Hours Worked</td>
			<td align='right'>".$pay_header1."</td>
			<td align='right'>".$pay_header2."</td>
			<td align='right'>".$pay_header3."</td>
			
			
			<td>Employer</td>
	*/

	$data_master = get_master_query();
	$unique_x=0;
		
	ob_start();
	?>
    <!----
	<center>
		
		<input type='button' value='Show All O.O.I.C. Drivers if Hidden' onClick='mrr_show_all_ooic();'>
		
		<span style='color:purple;'><b>Important Note:</b> Please reload the page or resubmit the form after making changes to the Drivers' O.O.I.C. Rates... or the updated email will retain the original value it had when loaded.</span>
		<br>
		<b>Recommendation:</b> Update all the O.O.I.C. Rates for the driver(s) you plan to send the email(s), and either hit the main "Submit" button at the top or the other "Reload Report" buttons before using the "E-Mail Driver" buttons. 
		<br>
		<i>Email messages to drivers are now sent from the last stored database copies of the section (by pay period), not directly from the report.</i>
		<br>&nbsp;<br>
		
	</center>
    ----->
	<table class='admin_menu2 font_display_section' style='margin:0 10px;text-align:left'>
	<tr>
		<td colspan='17'>
			<center>
			<span class='section_heading'>Owner Operator Report V2</span>
			<br>
			<?= $print_header_label ?>
			</center>
			<br>
		</td>
	</tr>

	<?
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_all_miles = 0;
		$total_mile_charge = 0;
		$current_driver = '';
		$total_driver_hours = 0;
		$total_hours = 0;
		
		$current_driver_id = 0;
		$current_truck_id = 0;
		
		$mrr_tot_per_mile=0;
		$mrr_tot_per_hour=0;	
		$mrr_tot_per_ooic=0;	
		
		$driver_pay_miles = 0;
		$driver_pay_hours = 0;
		$driver_pay_ooic = 0;
		$total_hours_charge = 0;
		$total_expenses = 0;
		
		$label_prefix="Charged ";
		if($driver_pay_mode ==1)		$label_prefix="Paid ";
		
		$max_overtime_hours= (int)$defaultsarray['overtime_hours_min'];
		$def_overtime_pay_rate=1.00;								//likely will be changed to 1.50 (time and a half)
		if(is_numeric($defaultsarray['overtime_def_rate']))
		{
			$def_overtime_pay_rate=$defaultsarray['overtime_def_rate'];
		}
		
		$expenses_checked=0;
		
		$mrr_display="";
		
		while($row = mysqli_fetch_array($data_master)) 
		{
			if(!isset($truck_pay_array[$row['name_truck']])) $truck_pay_array[$row['name_truck']] = 0;
			
			$driver1_employer_id=$row['driver1_employer_id'];
			$driver2_employer_id=$row['driver2_employer_id'];
			
			$temp_employer_id=$row['employer_id'];
			
			$truck_oo_ic=$row['truck_owner_operator'];
			$driver_oo_ic=$row['driver_owner_operator'];
			$flat_cost_rate=$row['flat_cost_rate'] - $row['flat_cost_fuel_rate'];
			if($flat_cost_rate < 0)		$flat_cost_rate=0;
			
			$flat_cost_rate_lock=$row['flat_cost_rate_lock'];
			
			$mrr_divide_exp=0;
			$mrr_employer_skip=0;	
			
			
			if($row['driver2_id'] > 0)		$mrr_divide_exp=1;
			
			if($_POST['employer_id'] > 0 && $row['driver2_id'] > 0)
			{	//used as a bypass for this driver, not the right employer...					
				if($row['driver_position']==1 && $row['driver1_employer_id']!=$_POST['employer_id'])		$mrr_employer_skip=1;
				if($row['driver_position']==2 && $row['driver1_employer_id']!=$_POST['employer_id'])		$mrr_employer_skip=1;		
			}
			
			$expenses_checked=0;
			if($row['driver_position']==2)	$expenses_checked=1;
					
			if($mrr_employer_skip==0)
			{     			
     			if($current_driver != $row['driver_id']) 
     			{
     				if($current_driver != '') 
     				{
     					//show_driver_totals($driver_miles, $driver_miles_deadhead, $driver_hours_worked, $driver_per_mile, $driver_per_hour);
     					$mrr_report_capture=$mrr_display;
     					echo $mrr_display;     					
     					show_driver_totals($current_truck_id,$current_driver_id);
     					$mrr_display="<tr class='next_driver_reg'><td colspan='24'><hr></td></tr>";
     				}
     				
     				$mrr_found_driver=0;
     				for($i=0;$i < $mrr_all_drivers; $i++)
     				{
     					if(	$mrr_all_drivers_arr[ $i ] == $row['driver_id'] )		$mrr_found_driver=1;
     				}
     				     				
     				if($mrr_found_driver==0)
     				{
     					$mrr_all_drivers_arr[ $mrr_all_drivers ]=$row['driver_id'];
     					$mrr_all_drivers++;	
     				}
     				     				
     				$current_driver_id = $row['driver_id'];
					$current_truck_id = $row['truck_id'];
     				
     				$current_driver = $row['driver_id'];
     				$current_driver_name = $row['name_driver_last'].", ".$row['name_driver_first'];
     				$this_driver_count = 0;
     				$driver_hours_worked = 0;
     				$driver_pre_hours_worked = 0;		//these are hours within the Payroll start period, but outside prior to the Date From value (...earlier in the week for a split month)...Added April 2015.
     				$driver_miles = 0;
     				$driver_miles_deadhead = 0;
     				$driver_pay = 0;
     				$driver_pay_miles = 0;
     				$driver_pay_hours = 0;
     				$driver_pay_ooic = 0;
     				
     				$mrr_driver_pay_miles = 0;
     				$mrr_driver_pay_hours = 0;
     				$mrr_driver_pay_ooic = 0;
     				
     				$driver_expenses = 0;
     				$driver_team_miles = 0;
     				$driver_pay_team = 0;
     				$driver_team_miles_deadhead = 0;
     				$driver_hourly_miles = 0;
     				$driver_hourly_miles_deadhead = 0;
     				
     				// get the list of driver expenses
     				$sql = "
     					select drivers_expenses.*,
     						option_values.fvalue as expense_type
     					
     					from drivers_expenses
     						left join option_values on option_values.id = drivers_expenses.expense_type_id
     					where drivers_expenses.driver_id = '$row[driver_id]'
     						and drivers_expenses.deleted = 0
     						and drivers_expenses.payroll = 1
     						and drivers_expenses.linedate>='".date("Y-m-d", strtotime($use_starting_point))." 00:00:00' 
     						and drivers_expenses.linedate<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
     					order by linedate
     				";
     				$data_driver_expenses = simple_query($sql);
     				
     				
     					//		[".$row['driver1_employer_id'].", ".$row['driver2_employer_id']."] 
     				$mrr_display.="    
     					<tr class='next_driver_reg begin_driver_".$row['driver_id']."_reg'>
     						<td colspan='17'>&nbsp;</td>
     					</tr> 					
     					<tr class='next_driver_reg begin_driver_".$row['driver_id']."_reg'>
     						<td colspan='3'>
     							Driver: <a href='admin_drivers.php?id=$row[driver_id]' target='manage_driver_$row[driver_id]'>$current_driver_name</a>  (".$label_prefix.")		     							
     						</td>
     						<td align='right' colspan='2' nowrap>&nbsp;&nbsp;&nbsp;</td>
     						<td align='right' nowrap colspan='8'>&nbsp;&nbsp;&nbsp;</td>
     						<td align='right' nowrap colspan='4'>&nbsp;&nbsp;&nbsp; <b>E-Mail:</b> ".trim($row['driver_email'])."</td>
     					</tr>
     				";
     				/*
     				// ".$label_prefix."Single Per Mile: ".($row['charged_per_mile'] == 0 ? "<span class='alert'>" : "<span>")."$".number_format($row['charged_per_mile'], 3)."</span>
     				// ".$label_prefix."Per Hour: $".money_format('',$row['charged_per_hour'])."
     				
     				echo "     					
     					<tr class='next_driver_reg begin_driver_".$row['driver_id']."_reg'>
     						<td colspan='3'>&nbsp;</td>
     						<td align='right' colspan='2' nowrap>&nbsp;&nbsp;&nbsp; ".$label_prefix."Team Per Mile: ".($row['charged_per_mile_team'] == 0 ? "<span class='alert'>" : "<span>")."$".number_format($row['charged_per_mile_team'], 3)."</span></td>
     						<td align='right' nowrap colspan='8'>&nbsp;&nbsp;&nbsp; ".$label_prefix."Team Per Hour: ".($row['charged_per_hour_team'] == 0 ? "<span class='alert'>" : "<span>")."$".number_format($row['charged_per_hour_team'], 2)."</span></td>
     					</tr>
     				";
     				*/
     				if(!isset($_POST['summary_only'])) 
     				{
     					$mrr_display.="<tr class='next_driver_reg begin_driver_".$row['driver_id']."_reg'>".$header_columns."</tr>";
     				}
     				
     				while($row_driver_expenses = mysqli_fetch_array($data_driver_expenses)) 
     				{
     					if(trim($row_driver_expenses['expense_type'])!='Comcheck')
     					{     					
               				$total_expenses+=$row_driver_expenses['amount_billable'];
               				$driver_expenses += $row_driver_expenses['amount_billable'];
               				
               				$account="";
               				if($row_driver_expenses['chart_id'] > 0)
               				{
               					$results=mrr_get_coa_list($row_driver_expenses['chart_id'],'');		//67000	//first arg is $chart_id, second arg is $chart_number	
               		
                                   	foreach($results as $key2 => $value2 )
                                   	{
                                   		if($key2=="ChartEntry")
                                   		{
                                        		foreach($value2 as $key => $value )
                                   			{         		
                                             		$prt=trim($key);		$tmp=trim($value);
                                             		//if($prt=="ID")		$chart_id=$tmp;
                                             		if($prt=="Name")		$account=$tmp;
                                             		//if($prt=="Number")	$chart_acct=$tmp;                              		
                                        		}//end for loop for each chart entry
                                   		}//end if
                                   	}//end for loop for each result returned
               				}//end if ID check		$row['chart_id']
               				  					
          					if(!isset($_POST['summary_only'])) 
          					{
          						$mrr_display.="
          							<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$row['driver_id']."_reg'>
          								<td colspan='2'>".$account."</td>
          								<td>$row_driver_expenses[expense_type]</td>
          								<td align='right'>$".money_format('',$row_driver_expenses['amount_billable'])."</td>
          								<td></td>
          								<td colspan='15'>".date("m-d-Y", strtotime($row_driver_expenses['linedate']))." - $row_driver_expenses[desc_long]</td>
          							</tr>
          						";
          					}
     					}
          				
     				}
     				
     				
     				//add driver vacation and cash advances here...Added April 2014...................................
     				$sql = "
     					select driver_vacation_advances_dates.*,
     						driver_vacation_advances.comments
     					
     					from driver_vacation_advances_dates
     						left join driver_vacation_advances on driver_vacation_advances.id=driver_vacation_advances_dates.dva_id
     					where driver_vacation_advances_dates.driver_id = '$row[driver_id]'
     						and driver_vacation_advances_dates.deleted = 0
     						and driver_vacation_advances.deleted = 0
     						and driver_vacation_advances_dates.linedate between '".date("Y-m-d", strtotime($use_starting_point))."' and '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
     					order by driver_vacation_advances_dates.linedate
     				";
     				$data_vacations = simple_query($sql);      				
     				while($row_vacations = mysqli_fetch_array($data_vacations)) 
     				{
     					$account="Vacation Pay";
     					$dva_chart_id=0;
     					$exp_typer="&nbsp;";     					
     					$dva_note="Vacation Pay";
     					
     					if($row_vacations['cash_advance']!=0)
     					{	//cash advance
     						$dva_pay=$row_vacations['cash_advance'];
     						
     						$dva_chart_id=0;
     						$account="Cash Advance";
     						$exp_typer="&nbsp;";
     						
     						$dva_note="Cash Advance";
     					}
     					elseif($row_vacations['driver_insurance_amnt_rate']!=0)
     					{	//driver insurance expense
     						$dva_pay=$row_vacations['driver_insurance_amnt_rate'];
     						
     						$dva_chart_id=0;
     						$account="Insurance";
     						$exp_typer="&nbsp;";
     						
     						$dva_note="Insurance";
     					}
     					else
     					{	//everything else is a vacation pay
     						//$dva_pay=$row_vacations['miles_pay_rate'];
     						//$dva_pay+=$row_vacations['hours_pay_rate'];
     						
     						if($_POST['payroll_mode']==1)
     						{	//driver pay    							
     							$dva_pay=$row_vacations['driver_paid_per_mile_rate'] * $row_vacations['miles'];
     							
     						}
     						else
     						{	//charged
     							$dva_pay=$row_vacations['driver_charged_per_mile_rate'] * $row_vacations['miles'];
     						}     						
     					}
     					
     					if($row_vacations['driver_holiday_pay_rate'] != 0)
     					{
     						$dva_pay+=$row_vacations['driver_holiday_pay_rate'];
     						$dva_note.=" and Holiday";
     					}     					
     					
     					$total_expenses+=$dva_pay;
               			$driver_expenses+=$dva_pay;
               			
     					if($dva_chart_id > 0)
          				{
          					$results=mrr_get_coa_list($dva_chart_id,'');		//
          		
                              	foreach($results as $key2 => $value2 )
                              	{
                              		if($key2=="ChartEntry")
                              		{
                                   		foreach($value2 as $key => $value )
                              			{         		
                                        		$prt=trim($key);		$tmp=trim($value);
                                        		//if($prt=="ID")		$chart_id=$tmp;
                                        		if($prt=="Name")		$account=$tmp;
                                        		//if($prt=="Number")	$chart_acct=$tmp;                              		
                                   		}//end for loop for each chart entry
                              		}//end if
                              	}//end for loop for each result returned
          				}//end if ID check	
          				
     					if(!isset($_POST['summary_only'])) 
     					{
     						$mrr_display.="
     							<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$row['driver_id']."_reg'>
     								<td colspan='2'>".$account."</td>
     								<td>".$exp_typer."</td>
     								<td align='right'>$".money_format('',$dva_pay)."</td>
     								<td></td>
     								<td colspan='15'>".date("m-d-Y", strtotime($row_vacations['linedate']))." - ".$row_vacations['comments']."</td>
     							</tr>
     						";
     					}
     				}
     				//................................................................................................
     				/**/
     			}
     			     						
     			$counter++;
     			$this_driver_count++;
     			 
     			
     			     			
     			//NEW calculation for pay based on settings from the dispatch for labor...not the general driver settings, which may have changed since then... Added May 2013
     			$mrr_per_mile_labor=$row['labor_per_mile'];
     			$mrr_per_hour_labor=$row['labor_per_hour'];
     			if($driver_pay_mode ==1)
     			{
     				$smp=mrr_grab_driver_payroll_item($row['driver_id'],date("m/d/Y", strtotime($row['linedate_pickup_eta'])),2);		//single mile pay 
     				
     				if($row['driver1_pay_per_mile'] > 0)		$mrr_per_mile_labor=$row['driver1_pay_per_mile'];	
     				elseif($smp > 0)						$mrr_per_mile_labor=$smp;		
     				elseif($row['charged_per_mile'] > 0)		$mrr_per_mile_labor=$row['charged_per_mile'];	
     				
     				$shp=mrr_grab_driver_payroll_item($row['driver_id'],date("m/d/Y", strtotime($row['linedate_pickup_eta'])),1);		//single hour pay
     				
     				if($row['driver1_pay_per_hour'] > 0)		$mrr_per_hour_labor=$row['driver1_pay_per_hour'];	
     				elseif($shp > 0)						$mrr_per_hour_labor=$shp;	
     				elseif($row['charged_per_hour'] > 0)		$mrr_per_hour_labor=$row['charged_per_hour'];
     			}
     			
     			if($row['driver2_id'] > 0)
     			{
     				if($row['driver_position']==1)
     				{
     					if($row['driver_2_labor_per_hour'] > 0)			$mrr_per_hour_labor = $row['labor_per_hour'] - $row['driver_2_labor_per_hour'];		else		$mrr_per_hour_labor = $mrr_per_hour_labor / 2;
     					if($row['driver_2_labor_per_mile'] > 0)			$mrr_per_mile_labor = $row['labor_per_mile'] - $row['driver_2_labor_per_mile'];		else 	$mrr_per_mile_labor = $mrr_per_mile_labor / 2;	
     					
     					if($driver_pay_mode ==1)
     					{
     						$smp=mrr_grab_driver_payroll_item($row['driver_id'],date("m/d/Y", strtotime($row['linedate_pickup_eta'])),2);		//single mile pay
     						
     						if($row['driver1_pay_per_mile'] > 0)		$mrr_per_mile_labor=$row['driver1_pay_per_mile'];			
     						elseif($smp > 0)						$mrr_per_mile_labor=$smp;
     						elseif($row['charged_per_mile_team'] > 0)	$mrr_per_mile_labor=$row['charged_per_mile_team'];
     						
     						
     						$shp=mrr_grab_driver_payroll_item($row['driver_id'],date("m/d/Y", strtotime($row['linedate_pickup_eta'])),1);		//single hour pay
     							
     						if($row['driver1_pay_per_hour'] > 0)		$mrr_per_hour_labor=$row['driver1_pay_per_hour'];			
     						elseif($shp > 0)						$mrr_per_hour_labor=$shp;
     						elseif($row['charged_per_hour_team'] > 0)	$mrr_per_hour_labor=$row['charged_per_hour_team'];	
     					}     					
     				}
     				elseif($row['driver_position']==2)
     				{
     					if($row['driver_2_labor_per_hour'] > 0)			$mrr_per_hour_labor = $row['driver_2_labor_per_hour'];		else		$mrr_per_hour_labor = $mrr_per_hour_labor / 2;
     					if($row['driver_2_labor_per_mile'] > 0)			$mrr_per_mile_labor = $row['driver_2_labor_per_mile'];		else 	$mrr_per_mile_labor = $mrr_per_mile_labor / 2;	
     					
     					if($driver_pay_mode ==1)
     					{
     						$smp=mrr_grab_driver_payroll_item($row['driver2_id'],date("m/d/Y", strtotime($row['linedate_pickup_eta'])),2);		//single mile pay
     						
     						if($row['driver2_pay_per_mile'] > 0)				$mrr_per_mile_labor=$row['driver2_pay_per_mile'];		
     						elseif($smp > 0)								$mrr_per_mile_labor=$smp;
     						elseif($row['driver2_charged_per_mile_team'] > 0)		$mrr_per_mile_labor=$row['driver2_charged_per_mile_team'];	
     						
     						
     						$shp=mrr_grab_driver_payroll_item($row['driver2_id'],date("m/d/Y", strtotime($row['linedate_pickup_eta'])),1);		//single hour pay
     						
     						if($row['driver2_pay_per_hour'] > 0)				$mrr_per_hour_labor=$row['driver2_pay_per_hour'];			
     						elseif($shp > 0)								$mrr_per_hour_labor=$shp;
     						elseif($row['driver2_charged_per_hour_team'] > 0)		$mrr_per_hour_labor=$row['driver2_charged_per_hour_team'];	
     					} 
     				}     				
     			}
     			
     			$overtime_highlight="";
     			   			
     			$tmp_cur_hours=$driver_hours_worked;						//hold hours before this set is added....
     			$tmp_hourly_pay=0;
     			
     			$driver_hours_worked += $row['hours_worked'];     			
     			$total_hours += $row['hours_worked'];
     			
     			$use_this_dispatch_row=0;
     			if(date("Ymd",strtotime($use_starting_point)) <=  date("Ymd",strtotime($row['linedate_pickup_eta'])))
     			{
     				$use_this_dispatch_row=1;
     			}
     			else
     			{
     				$driver_pre_hours_worked+=$row['hours_worked'];
     				$total_hours-=$row['hours_worked'];
     			}
     			
     			//$truck_oo_ic > 0 && 
     			if($driver_oo_ic > 0)	
     			{	//pull flat rate from this load's settings...
     				$tmp_hourly_pay=$flat_cost_rate;
     				
     				
     				$mrr_driver_pay_ooic += $tmp_hourly_pay;	
     				$driver_pay_ooic += $tmp_hourly_pay;
          			$driver_pay += $tmp_hourly_pay;
          			
          			$truck_pay_array[$row['name_truck']] += $tmp_hourly_pay;
          			
     				$total_miles += $row['miles'];
          			$total_deadhead += $row['miles_deadhead'];
     			}
     			elseif($use_this_dispatch_row==1)
     			{          				
          			if($driver_hours_worked > $max_overtime_hours && $skip_overtime_separation==0)
          			{	//with these hours, at least some of it is overtime pay...calculate.    
          				$overtime_highlight=" style='color:#CC0000;'";
          				
          				$tmp_hours_left=$max_overtime_hours - $tmp_cur_hours;		//this is the rest of the regular hourly pay...
          				
          				if($tmp_hours_left > 0)
          				{
          					$tmp_hours_over=$row['hours_worked'] - $tmp_hours_left;	//this is the amount of hours for overtime pay rate...ONLY SOME OF IT
          				}
          				else
          				{
          					$tmp_hours_over=$row['hours_worked'];					//this is the amount of hours for overtime pay rate...	ALL OF IT
          					$tmp_hours_left=0;									//none left at normal pay...
          				}
          				   				 				     				
          				$tmp_hourly_pay=$tmp_hours_left * $mrr_per_hour_labor;							//get the regular pay     				
          				
          				$tmp_hourly_pay+=($tmp_hours_over * $mrr_per_hour_labor * $def_overtime_pay_rate);	//add the overtime pay
          			}
          			else
          			{	//normal hourly pay...
          				$tmp_hourly_pay=$row['hours_worked'] * $mrr_per_hour_labor;
          			}          			
          			$total_hours_charge += $tmp_hourly_pay;
          			
          			$mrr_tot_per_mile+=(($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor);
          			$mrr_tot_per_hour+=$tmp_hourly_pay;	//($row['hours_worked'] *$mrr_per_hour_labor);
          			
          			$mrr_driver_pay_miles += (($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor);
          			$mrr_driver_pay_hours += $tmp_hourly_pay;	//($row['hours_worked'] *$mrr_per_hour_labor);
          			$driver_pay_hours += $tmp_hourly_pay;
          			$driver_pay += $tmp_hourly_pay;
          			$truck_pay_array[$row['name_truck']] += $tmp_hourly_pay;
          			//.............................................................................................................................................................          			
     				
     				          			
          			$line_miles = $row['miles'] + $row['miles_deadhead'];
          			if($row['hours_worked'] > 0) {
          				// if this driver was paid hourly, then we don't pay by the mile ===================================FALSE...Calculate both of them....
          				/*
          				$truck_pay_array[$row['name_truck']] += $row['hours_worked'] * $row['charged_per_hour'];
          				$driver_pay += $row['hours_worked'] * $row['charged_per_hour'];
          				$driver_pay_hours += $row['hours_worked'] * $row['charged_per_hour'];;
          				$driver_hourly_miles += $row['miles'];
          				$driver_hourly_miles_deadhead = $row['miles_deadhead'];
          				*/
          				
          				//$truck_pay_array[$row['name_truck']] += $row['hours_worked'] * $row['dispatch_charged_per_hour'];
          				//$driver_pay += $row['hours_worked'] * $row['dispatch_charged_per_hour'];
          				//$driver_pay_hours += $row['hours_worked'] * $row['dispatch_charged_per_hour'];
          				$driver_hourly_miles += $row['miles'];
          				$driver_hourly_miles_deadhead += $row['miles_deadhead'];	
          				
          				if($line_miles>0)
          				{
          					if($row['driver2_id'] > 0) {
               					$driver_pay_team += $line_miles * $mrr_per_mile_labor;
               					$truck_pay_array[$row['name_truck']] += $line_miles * $mrr_per_mile_labor;
               				} 
               				else {
               					$truck_pay_array[$row['name_truck']] += $line_miles * $row['dispatch_charged_per_mile'];
               					$driver_pay += $line_miles * $row['dispatch_charged_per_mile'];
               					$driver_pay_miles += $line_miles * $row['displatch_charged_per_mile'];
               				}
               				
               				$total_mile_charge += ($line_miles * $row['dispatch_charged_per_mile']);
               				$driver_miles += $row['miles'];
          				}				
          			}
          			else
          			{
          				if($row['driver2_id'] > 0) {
          					$driver_pay_team += $line_miles * $mrr_per_mile_labor;
          					$truck_pay_array[$row['name_truck']] += $line_miles * $mrr_per_mile_labor;
          				} 
          				else {
          					$truck_pay_array[$row['name_truck']] += $line_miles * $row['dispatch_charged_per_mile'];
          					$driver_pay += $line_miles * $row['dispatch_charged_per_mile'];
          					$driver_pay_miles += $line_miles * $row['dispatch_charged_per_mile'];
          				}
          				
          				$total_mile_charge += ($line_miles * $row['dispatch_charged_per_mile']);
          				$driver_miles += $row['miles'];
          			}
          			
          			if($row['driver2_id'] > 0) {
          				$driver_team_miles_deadhead += $row['miles_deadhead'];
          			} else {
          				$driver_miles_deadhead += $row['miles_deadhead'];
          			}
          			$total_all_miles += $line_miles;
          			if($row['driver2_id'] > 0) $driver_team_miles += $row['miles'];
          			
          			
          			$total_miles += $row['miles'];
          			$total_deadhead += $row['miles_deadhead'];
     			}
     			
     			
     			// get any dispatch expenses (tied to dispatches and driver)
     			$sql = "
     				select expense_desc,
     					expense_amount,
     					dispatch_expenses.chart_id,
     					option_values.fvalue as expense_type
     					
     				from dispatch_expenses
     					left join option_values on option_values.id = dispatch_expenses.expense_type_id
     					left join trucks_log on trucks_log.id = dispatch_expenses.dispatch_id
     					
     				where trucks_log.deleted = 0
     					and (trucks_log.driver_id = '".sql_friendly($current_driver)."' or trucks_log.driver2_id='".sql_friendly($current_driver)."')
     					and trucks_log.id = $row[id]
     					and dispatch_expenses.deleted = 0
     			";
     			$data_expenses = simple_query($sql);
     			
     			
     			$use_main_employer=$row['employer_id'];
     			if($row['employer_id']==0 && $row['driver1_employer_id'] > 0)	$use_main_employer=$row['driver1_employer_id'];
     			
     			$normal_labor_miles="$".number_format($mrr_per_mile_labor,3)."";
     			$normal_pay_miles="$".number_format((($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor),2)."";
     			$normal_labor_hours="$".number_format($mrr_per_hour_labor,2)."";
     			$show_pay_reg=$tmp_hourly_pay;
     			$show_pay_ooic=0;
     			
     			//$truck_oo_ic > 0 && 
     			if($driver_oo_ic > 0)	
     			{
     				$normal_labor_miles="O.O./I.C.";
     				$normal_pay_miles="$0.00";
     				$normal_labor_hours="$0.00";  
     				$show_pay_reg=0;
     				$show_pay_ooic=$tmp_hourly_pay;				
     			}
     			     			
     			if(!isset($_POST['summary_only']) && $use_this_dispatch_row==1) 
     			{
     			     //actual_bill_customer, disp_route  ....form to change flat_cost_rate (and maybe flat_cost_fuel_rate) in database trucks_log table.
                     //$driver_ooic_rate
                     $switch_expenses=0.00;
                     $sw_exp_id=0;
                     if(trim($row['disp_route'])!="")
                     {
                         $sql = "
                                select id,fvalue
                                from option_values
                                where deleted = 0
                                    and cat_id=40
                                    and fname like '".sql_friendly(trim($row['disp_route']))."'
                                order by fvalue desc
                         ";
                         $data_route = simple_query($sql);
                         if($row_route = mysqli_fetch_array($data_route))
                         {
                             $sw_exp_id=$row_route['id'];
                             $switch_expenses=trim($row_route['fvalue']);
                             if(!is_numeric($switch_expenses))     $switch_expenses=0.00;
                         }
                         else
                         {
                              //does not exist, so make it.
                              $sqlu="
                                    insert into option_values
                                        (id, fname,fvalue,cat_id,deleted)
                                    values
                                        (NULL,'".sql_friendly(trim($row['disp_route']))."','0.00',40,0)
                              ";
                              simple_query($sqlu);
                              $sw_exp_id=mysqli_insert_id($datasource);
                         }
                     }
                     
                     //$show_pay_ooic   =600
                     //$switch_expenses  =150
                     //$driver_ooic_rate = 70%
                     //Example: (600 (billed) - 150 (SwExp) * 0.70 (70/100 = 70%))  =315 (O.O.I.C rate)
                                          
                     if($show_pay_ooic==0)
                     {
                          if($flat_cost_rate_lock==0)
                          { //should now allow $0 values...Added Feb 2021...MRRR.  Requested by Justin and confirmed by Dale.
                              $show_pay_ooic =(( $row['actual_bill_customer'] - $switch_expenses) * $driver_ooic_rate) / 100;
                          }
                          
                          $sqlu = "
            				update trucks_log set
		            			flat_cost_rate='".sql_friendly($show_pay_ooic)."'
				            where  id='".sql_friendly($row['id'])."'
			              ";
                          simple_query($sqlu);
                          
                          $tmp_hourly_pay+=$show_pay_ooic;
     
                          $driver_expenses += $show_pay_ooic;
                          $total_expenses += $show_pay_ooic;
                     }
                
                     if($show_pay_ooic > 0 && $switch_expenses==0 && trim($row['disp_route'])!="")
                     {
                          $switch_expenses = $row['actual_bill_customer'] - ($show_pay_ooic / ($driver_ooic_rate / 100));
                          $sqlu="
                                update option_values set
                                    fvalue='".sql_friendly(number_format($switch_expenses,2))."'
                                where deleted = 0
                                    and fname like '".sql_friendly(trim($row['disp_route']))."'
                              ";
                          simple_query($sqlu);
                     }
                     
                     $mrr_test_date=mrr_find_if_emailed_driver($row['driver_id']);
                     $mrr_test_value=(($row['actual_bill_customer'] - $switch_expenses) * $driver_ooic_rate / 100);
                     $mrr_title_disp="";
                     $mrr_classy="";
                     if(round($mrr_test_value,2) != round($show_pay_ooic,2))  
                     {   // && $mrr_test_date==""
                         $mrr_classy=" mrr_highlight_ooic_unmatched";
                         $mrr_title_disp=" title='Current calculated OOIC Rate for this would be $".round($mrr_test_value,2).".'";
                     }
                     
                     $allow_sw_exp_override=0;
                     //if($_SESSION['user_id'] == 15 || $_SESSION['user_id'] == 19 || $_SESSION['user_id'] == 23)       $allow_sw_exp_override=1;
                     
                     $mrr_display.="
     					<tr class='".($this_driver_count % 2 == 1 ? 'odd' : 'even')." next_driver_reg begin_driver_".$row['driver_id']."_reg".$mrr_classy."'>
     						<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
     						<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
     						<td nowrap".$mrr_title_disp.">$row[origin]</td>
     						<td nowrap".$mrr_title_disp.">$row[destination]</td>
     						<td align='left' colspan='6'".$mrr_title_disp.">".trim($row['disp_route'])."</td>
     						<td align='right'".$mrr_title_disp.">$
     						    ".(((($switch_expenses > 0 && $mrr_test_date!="") || trim($row['disp_route'])=="") && $allow_sw_exp_override==0) ? "".number_format($switch_expenses,2)."" : "<input type='text' name='update_switch_expense_".$sw_exp_id."_".$unique_x."' id='update_switch_expense_".$sw_exp_id."_".$unique_x."' value='".number_format($switch_expenses,2)."' style='text-align:right; width:75px;' onChange='mrr_ooic_auto_update_route(".$sw_exp_id.",".$unique_x.",".$row['id'].",\"".$row['actual_bill_customer']."\");'>")."
     						</td>
     						<td align='right'".$overtime_highlight."".$mrr_title_disp.">$".number_format($row['actual_bill_customer'],2)."</td>
     						<td align='right'".$overtime_highlight.">
     							$<input type='text' name='update_rate_".$row['id']."' id='update_rate_".$row['id']."' value='".number_format($show_pay_ooic,2)."' style='text-align:right; width:75px;' onChange='mrr_ooic_auto_update(".$row['id'].");'>
     						</td>     						
     						<td nowrap".$mrr_title_disp.">".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
     						<td nowrap".$mrr_title_disp.">$row[name_truck]</td>
     						<td nowrap".$mrr_title_disp.">$row[trailer_name]</td>
     						<td nowrap".$mrr_title_disp.">$row[name_company]</td>
     						
     					</tr>
     				";														//($row['hours_worked'] *$mrr_per_hour_labor )
                    $unique_x++;
                    
     						/*
     						<td align='right'>".number_format($row['miles'])."</td>
     						<td align='right'>".number_format($row['miles_deadhead'])."</td>
     						<td align='right'>".number_format($row['miles_deadhead'] + $row['miles'])."</td>
     						<td align='right'".$overtime_highlight.">".(number_format($row['hours_worked']) == $row['hours_worked'] ? number_format($row['hours_worked']) : $row['hours_worked'])."</td>
     						
     						<td align='right'".$overtime_highlight.">".$normal_labor_miles."</td>
     						<td align='right'".$overtime_highlight.">".$normal_pay_miles."</td>
     						<td align='right'".$overtime_highlight.">".$normal_labor_hours."</td>   						
     						*/
     						
     						//<td nowrap>".mrr_fetch_set_employer($use_main_employer)."</td>
     						
     				if($row['driver2_id'] > 0) 
     				{
     					$mrr_display.="
     						<tr class='".($this_driver_count % 2 == 1 ? 'odd' : 'even')." next_driver_reg begin_driver_".$row['driver_id']."_reg' style='background-color:#ebc8c8'>
     							<td colspan='2'>&nbsp;</td>
     							<td>Team Run</td>
     							<td colspan='15'>$row[name2_driver_first] $row[name2_driver_last]</td>
     						</tr>
     					";
     				}
     			}
     			
     			while($row_expense = mysqli_fetch_array($data_expenses)) 
     			{
     				if(trim($row_expense['expense_type'])!='Comcheck' && $use_this_dispatch_row==1)
     				{
          				$exp_amntr=$row_expense['expense_amount'];
          				if($row['driver2_id'] > 0)	$exp_amntr=$exp_amntr/2;	
          				          				
          				$driver_expenses += $exp_amntr;
          				$total_expenses += $exp_amntr;
          				
     					$account="";
          				if($row_expense['chart_id'] > 0)
          				{
          					$results=mrr_get_coa_list($row_expense['chart_id'],'');		//67000	//first arg is $chart_id, second arg is $chart_number	
          		
                              	foreach($results as $key2 => $value2 )
                              	{
                              		if($key2=="ChartEntry")
                              		{
                                   		foreach($value2 as $key => $value )
                              			{         		
                                        		$prt=trim($key);		$tmp=trim($value);
                                        		//if($prt=="ID")		$chart_id=$tmp;
                                        		if($prt=="Name")		$account=$tmp;
                                        		//if($prt=="Number")	$chart_acct=$tmp;                              		
                                   		}//end for loop for each chart entry
                              		}//end if
                              	}//end for loop for each result returned
          				}//end if ID check		$row['chart_id']
               			     				
          				if(!isset($_POST['summary_only'])) 
          				{
          					$mrr_display.="
          						<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$row['driver_id']."_reg'>
          							<td colspan='2'>".$account."</td>
          							<td>$row_expense[expense_type]</td>
          							<td align='right'>$".money_format('',$exp_amntr)."</td>
          							<td></td>
          							<td></td>
          							<td colspan='10'>$row_expense[expense_desc]</td>
          						</tr>
          					";
          				}
     				}
     			}
			}
			$mrr_report_capture.=$mrr_display;
		}
		if($mrr_all_drivers > 0)		
		{
			$mrr_report_capture=$mrr_display;
			echo $mrr_display; 
			show_driver_totals($current_truck_id,$current_driver_id);
			$mrr_display=""; 

		}
		
		//---------------------------------------------------------------------NOW SHOW EXPENSES FOR OTHER DRIVERS NOT ON THE LOAD LIST...or vacation pay------------------------------------------------------------------------------
		$expenses_checked=1;
		if($expenses_checked==0)
		{
			$mrr_temp="";
     		$mrr_cntr=0;
     		
     		$mrr_driver_cntr=0; 
     		
     		$mrr_found_new=0;		//this driver was only added for expenses...and vacation, but had no loads.
     		
     		$sql = "
     			select drivers.*			
     			from drivers
     			where deleted=0
     			".($_POST['driver_id'] ? " and id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
     			".($_POST['employer_id'] != 0 ? " and employer_id = '".sql_friendly($_POST['employer_id'])."'" : '') ."
     			order by name_driver_last, name_driver_first
     		";
     		$xxx_cntr=0;
     		$data_drivers = simple_query($sql);
     		while($row = mysqli_fetch_array($data_drivers)) 
     		{
     			$mrr_driver_cntr++;
     			
     			
     			$mrr_found_driver=0;
     			for($i=0;$i < $mrr_all_drivers; $i++)
     			{
     				if(	$mrr_all_drivers_arr[ $i ] == $row['id'] )		$mrr_found_driver=1;
     			}
     			if($mrr_found_driver==0)
     			{
     				$mrr_all_drivers_arr[ $mrr_all_drivers ]=$row['id'];
     				$mrr_all_drivers++;	
     				
     				$mrr_found_new=1;
     				
     				$mrr_temp="";
     			
     				if($current_driver != '' && $mrr_cntr>0) {
     					//show_driver_totals($driver_miles, $driver_miles_deadhead, $driver_hours_worked, $driver_per_mile, $driver_per_hour);
     					if($mrr_cntr > 0 && $driver_expenses > 0)	
     					{
     						$mrr_report_capture=$mrr_display;
     						echo $mrr_display; 
     						show_driver_totals($current_truck_id,$current_driver_id);
     						$mrr_display="<tr class='next_driver_reg begin_driver_".$row['id']."_reg'><td colspan='23'><hr></td></tr>";
     					}
     				}
     				$mrr_cntr++;
     				
     				$current_truck_id=$row['attached_truck_id'];
     				$current_driver_id=$row['id'];
     				
     				$current_driver = $row['id'];
     				$current_driver_name = $row['name_driver_last'].", ".$row['name_driver_first'];
     				$this_driver_count = 0;
     				$driver_hours_worked = 0;
     				$driver_miles = 0;
     				$driver_miles_deadhead = 0;
     				$driver_pay = 0;
     				$driver_pay_miles = 0;
     				$driver_pay_hours = 0;
     								
     				$mrr_driver_pay_miles = 0;
     				$mrr_driver_pay_hours = 0;
     								
     				$driver_expenses = 0;
     				$driver_team_miles = 0;
     				$driver_pay_team = 0;
     				$driver_team_miles_deadhead = 0;
     				$driver_hourly_miles = 0;
     				$driver_hourly_miles_deadhead = 0;
     								
     				
     				// get the list of driver expenses
     				$sql = "
     					select drivers_expenses.*,
     						option_values.fvalue as expense_type
     					
     					from drivers_expenses
     						left join option_values on option_values.id = drivers_expenses.expense_type_id
     					where drivers_expenses.driver_id = '$row[id]'
     						and drivers_expenses.deleted = 0
     						and drivers_expenses.payroll = 1
     						and drivers_expenses.linedate>='".date("Y-m-d", strtotime($use_starting_point))." 00:00:00' 
     						and drivers_expenses.linedate<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
     					order by linedate
     				";
     				//echo $sql."<br>";
     				
     				$data_driver_expenses = simple_query($sql);
     							
     				$mrr_temp.= "
     					<tr class='next_driver_exp begin_driver_".$row['id']."_exp'>
     						<td colspan='8'><br>&nbsp;</td>
     					</tr>
     					<tr class='next_driver_reg begin_driver_".$row['id']."_reg'>
     						<td colspan='3'>Driver: <a href='admin_drivers.php?id=$row[driver_id]' target='manage_driver_$row[id]'>$current_driver_name</a> (".$label_prefix.")</td>
     						<td align='right' colspan='6' nowrap><span style='font-weight:bold; color:#AA0000;'>Expenses Only</span></td>
     						<td align='right' nowrap colspan='2'><b>E-Mail:</b> ".trim($row['driver_email'])."</td>
     					</tr>
     				";
     				
     				if(!isset($_POST['summary_only'])) 
     				{
     					$mrr_temp.= "<tr class='next_driver_reg begin_driver_".$row['id']."_reg'>".$header_columns."</tr>";
     				}
     				
     				while($row_driver_expenses = mysqli_fetch_array($data_driver_expenses)) {
     					if(trim($row_driver_expenses['expense_type'])!='Comcheck')
     					{
          					$driver_expenses += $row_driver_expenses['amount_billable'];
          					$total_expenses += $row_driver_expenses['amount_billable'];
          					     					
               				$account="";
               				if($row_driver_expenses['chart_id'] > 0)
               				{
               					$results=mrr_get_coa_list($row_driver_expenses['chart_id'],'');		//67000	//first arg is $chart_id, second arg is $chart_number	
               		
                                   	foreach($results as $key2 => $value2 )
                                   	{
                                   		if($key2=="ChartEntry")
                                   		{
                                        		foreach($value2 as $key => $value )
                                   			{         		
                                             		$prt=trim($key);		$tmp=trim($value);
                                             		//if($prt=="ID")		$chart_id=$tmp;
                                             		if($prt=="Name")		$account=$tmp;
                                             		//if($prt=="Number")	$chart_acct=$tmp;                              		
                                        		}//end for loop for each chart entry
                                   		}//end if
                                   	}//end for loop for each result returned
               				}//end if ID check		$row['chart_id']
                    			     					
          					if(!isset($_POST['summary_only'])) {
          						
          						$mrr_temp.= "
          							<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$row['id']."_reg'>
          								<td colspan='2'>".$account."</td>
          								<td>$row_driver_expenses[expense_type]</td>
          								<td align='right'>$".money_format('',$row_driver_expenses['amount_billable'])."</td>
          								<td></td>
          								<td colspan='14'>".date("m-d-Y", strtotime($row_driver_expenses['linedate']))." - $row_driver_expenses[desc_long]</td>
          							</tr>
          						";
          						/**/
          						$xxx_cntr++;
          					}
     					}
     				}
     				
     			}
     			$counter++;
     			$this_driver_count++;
     					
     			// get any dispatch expenses (tied to dispatches and driver)
     			$sql = "
     				select expense_desc,
     					expense_amount,
     					dispatch_expenses.chart_id,
     					option_values.fvalue as expense_type
     					
     				from dispatch_expenses
     					left join option_values on option_values.id = dispatch_expenses.expense_type_id
     					left join trucks_log on trucks_log.id = dispatch_expenses.dispatch_id
     					
     				where trucks_log.deleted = 0
     					and trucks_log.driver_id = '".sql_friendly($current_driver)."'
     					and trucks_log.id = $row[id]
     					and dispatch_expenses.deleted = 0
     			";
     			$data_expenses = simple_query($sql);
     						
     			while($row_expense = mysqli_fetch_array($data_expenses)) {
     				if(trim($row_expense['expense_type'])!='Comcheck')
     				{
          				$driver_expenses += $row_expense['expense_amount'];
          				$total_expenses += $row_expense['expense_amount'];
          				     					
          				$account="";
          				if($row_expense['chart_id'] > 0)
          				{
          					$results=mrr_get_coa_list($row_expense['chart_id'],'');		//67000	//first arg is $chart_id, second arg is $chart_number	
          		
                              	foreach($results as $key2 => $value2 )
                              	{
                              		if($key2=="ChartEntry")
                              		{
                                   		foreach($value2 as $key => $value )
                              			{         		
                                        		$prt=trim($key);		$tmp=trim($value);
                                        		//if($prt=="ID")		$chart_id=$tmp;
                                        		if($prt=="Name")		$account=$tmp;
                                        		//if($prt=="Number")	$chart_acct=$tmp;                              		
                                   		}//end for loop for each chart entry
                              		}//end if
                              	}//end for loop for each result returned
          				}//end if ID check		$row['chart_id']
                    			 
          				if(!isset($_POST['summary_only'])) {
          					$mrr_temp.= "
          						<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$row['id']."_reg'>
          							<td colspan='2'>".$account."</td>
          							<td>$row_expense[expense_type]</td>
          							<td align='right'>$".money_format('',$row_expense['expense_amount'])."</td>
          							<td></td>
          							<td colspan='14'>$row_expense[expense_desc]</td>
          						</tr>
          					";
          				}
     				}
     			}
     			
                    //add driver vacation and cash advances here...Added April 2014...................................
                    if(($_POST['driver_id']==0 && $current_driver ==$row['id']) || ($_POST['driver_id'] > 0 && $_POST['driver_id'] ==$row['id'] && $mrr_found_new==1))
                    {
     				$sql = "
     					select driver_vacation_advances_dates.*,
     						driver_vacation_advances.comments
     					
     					from driver_vacation_advances_dates
     						left join driver_vacation_advances on driver_vacation_advances.id=driver_vacation_advances_dates.dva_id
     					where driver_vacation_advances_dates.driver_id = '".sql_friendly($current_driver)."'
     						and driver_vacation_advances_dates.deleted = 0
     						and driver_vacation_advances.deleted = 0
     						and driver_vacation_advances_dates.linedate between '".date("Y-m-d", strtotime($use_starting_point))."' and '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
     					order by driver_vacation_advances_dates.linedate
     				";
     				$data_vacations = simple_query($sql);   				
     				
     				while($row_vacations = mysqli_fetch_array($data_vacations)) 
     				{
     					$account="Vacation Pay";
     					$dva_chart_id=0;
     					$exp_typer="&nbsp;";
     					$dva_note="Vacation Pay";
     					
     					if($row_vacations['cash_advance']!=0)
     					{	//cash advance
     						$dva_pay=$row_vacations['cash_advance'];
     						
     						$dva_chart_id=0;
     						$account="Cash Advance";
     						$exp_typer="&nbsp;";
     						
     						$dva_note="Cash Advance";
     					}
     					elseif($row_vacations['driver_insurance_amnt_rate']!=0)
     					{	//driver insurance expense
     						$dva_pay=$row_vacations['driver_insurance_amnt_rate'];
     						
     						$dva_chart_id=0;
     						$account="Insurance";
     						$exp_typer="&nbsp;";
     						
     						$dva_note="Insurance";
     					}
     					else
     					{	//everything else is a vacation pay 
     						//$dva_pay=$row_vacations['miles_pay_rate'];
     						//$dva_pay+=$row_vacations['hours_pay_rate'];
     						
     						if($_POST['payroll_mode']==1)
     						{	//driver pay    							
     							$dva_pay=$row_vacations['driver_paid_per_mile_rate'] * $row_vacations['miles'];
     							
     						}
     						else
     						{	//charged
     							$dva_pay=$row_vacations['driver_charged_per_mile_rate'] * $row_vacations['miles'];
     						}     						
     					}
     					
     					if($row_vacations['driver_holiday_pay_rate'] != 0)
     					{
     						$dva_pay+=$row_vacations['driver_holiday_pay_rate'];
     						$dva_note.=" and Holiday";
     					}   
     					
     					$total_expenses+=$dva_pay;
               			$driver_expenses+=$dva_pay;
               			
               			//$total_expenses_mrr+=$dva_pay;
               			          			
     					if($dva_chart_id > 0)
          				{
          					$results=mrr_get_coa_list($dva_chart_id,'');		//
          		
                              	foreach($results as $key2 => $value2 )
                              	{
                              		if($key2=="ChartEntry")
                              		{
                                   		foreach($value2 as $key => $value )
                              			{         		
                                        		$prt=trim($key);		$tmp=trim($value);
                                        		//if($prt=="ID")		$chart_id=$tmp;
                                        		if($prt=="Name")		$account=$tmp;
                                        		//if($prt=="Number")	$chart_acct=$tmp;                              		
                                   		}//end for loop for each chart entry
                              		}//end if
                              	}//end for loop for each result returned
          				}//end if ID check	
          				
     					if(!isset($_POST['summary_only'])) 
     					{					//".$mrr_driver_cntr." 
     						$mrr_temp.="
     							<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$row['driver_id']."_reg'>
     								<td colspan='2'>".$account."</td>
     								<td>".$exp_typer."</td>
     								<td align='right'>$".money_format('',$dva_pay)."</td>
     								<td></td>
     								<td colspan='14'>".date("m-d-Y", strtotime($row_vacations['linedate']))." - ".$row_vacations['comments']."</td>
     							</tr>
     						";
     					}
     				}
     				//................................................................................................
                    }
                                                            
                    if($driver_expenses != 0)
                    {
                    	$mrr_display.=$mrr_temp;
                    	//echo $mrr_temp;                    	
                    }
                    $mrr_temp="";
     		}
     		if($total_expenses != 0)
               {
                  	//$mrr_report_capture=$mrr_display;
                  	//echo $mrr_display;
                  	//show_driver_totals($current_truck_id,$current_driver_id);
                  	//$mrr_display="";
               }
		}//end check for expenses...
		
		//echo $mrr_display;
	?>
	<?
	
	//---------------------------------------Now show Carlex/Vietti Time Sheet section....since these drivers may not even have proper loads/dispatches to find above.......Jan 2016..................
	//$use_starting_point=$_POST['date_from']
	$res=mrr_find_timsheet_entries_for_payroll_period($use_starting_point,$_POST['date_to'],$_POST['employer_id'],$_POST['driver_id']);
	
	$carlex_total=$res['total'];		//timesheet version
	$carlex_total2=0;				//$res['total2'];	//shuttle run version
	$carlex_section=$res['html'];
		
	$exps=$res['num'];
	$exp_chart=$res['chart'];
	$exp_acct=$res['acct'];
	$exp_driver=$res['driver'];
	$exp_employ=$res['employ'];
	$exp_name=$res['name'];
	$exp_notes=$res['notes'];
	$exp_hrs=$res['hrs'];
	$exp_pay=$res['pay'];
	$exp_runs=$res['runs'];
	$exp_shuttle=$res['shuttle'];
	$exp_amnt=$res['amnt'];
	
	$current_driver=0;
	$current_driver_name = "";
	
	$this_driver_count = 0;
	$driver_hours_worked = 0;
	$driver_miles = 0;
	$driver_miles_deadhead = 0;
	$driver_pay = 0;
	$driver_pay_miles = 0;
	$driver_pay_hours = 0;
					
	$mrr_driver_pay_miles = 0;
	$mrr_driver_pay_hours = 0;
					
	$driver_expenses = 0;
	$driver_team_miles = 0;
	$driver_pay_team = 0;
	$driver_team_miles_deadhead = 0;
	$driver_hourly_miles = 0;
	$driver_hourly_miles_deadhead = 0;
	$mrr_temp="";
	
	if($_POST['payroll_mode']!=1 && 1==2)
	{
     	for($i=0; $i < $exps; $i++)
     	{
     		/*
     		$exp_driver[ $i ]
     		$exp_name[ $i ]
     		$exp_chart[ $i ]
     		$exp_acct[ $i ]
     		
     		$exp_notes[ $i ]
     		$exp_hrs[ $i ]
     		$exp_pay[ $i ]
     		$exp_runs[ $i ]
     		$exp_shuttle[ $i ]
     		$exp_amnt[ $i ]
     		*/	
     		
     		$mrr_found_driver=0;
     		for($x=0;$x < $mrr_all_drivers; $x++)
     		{
     			if(	$mrr_all_drivers_arr[ $x ] == $exp_driver[ $i ] )	
     			{
     				$mrr_found_driver=1;			
     				
     				if(trim($current_driver_name) != trim($exp_name[ $i ]))
     				{
     					if($i > 0)
     					{
     						$mrr_display.=$mrr_temp;    
     						$mrr_report_capture=$mrr_display; 						
     						echo $mrr_display;
     						show_driver_totals();
     						$mrr_display="<tr class='next_driver_reg'><td colspan='24'><hr></td></tr>";	
     					}
     					$mrr_temp="";	
     					
     					$current_driver = $exp_driver[ $i ];
          				$current_driver_name = trim($exp_name[ $i ]);  
     					
     					$this_driver_count = 0;
               			$driver_hours_worked = 0;
               			$driver_miles = 0;
               			$driver_miles_deadhead = 0;
               			$driver_pay = 0;
               			$driver_pay_miles = 0;
               			$driver_pay_hours = 0;
               							
               			$mrr_driver_pay_miles = 0;
               			$mrr_driver_pay_hours = 0;
               							
               			$driver_expenses = 0;
               			$driver_team_miles = 0;
               			$driver_pay_team = 0;
               			$driver_team_miles_deadhead = 0;
               			$driver_hourly_miles = 0;
               			$driver_hourly_miles_deadhead = 0;	
     					
     					
     					$mrr_temp= "
          					<tr class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'>
          						<td colspan='8'><br>&nbsp;</td>
          					</tr>
          					<tr class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'>
          						<td colspan='3'>Driver: <a href='admin_drivers.php?id=".$exp_driver[ $i ]."' target='manage_driver_".$exp_driver[ $i ]."'>".$exp_name[ $i ]."</a></td>
          						<td align='right' colspan='6' nowrap><span style='font-weight:bold; color:#AA0000;'>Company Time Sheet and Shuttle Routes</span></td>
          						<td align='right' nowrap colspan='2'></td>
          					</tr>
     					";
     					if(!isset($_POST['summary_only'])) 
     					{
     						$mrr_temp.= "<tr class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'>".$header_columns."</tr>";
     					}
     				}
     				
     				
     				
     				if($exp_runs[ $i ] > 0)			
          			{	//shuttle run...flat pay...NOT FOR PAYROLL...............Only for Carlex Invoice
          				//$driver_pay += $exp_amnt[ $i ];
          				
          				
          				
          				//Update:  Carlex Nashille location adds the hours worked EVEN if it is a shuttle run as of about April 
          				if($exp_hrs[ $i ] > 0)
          				{
          					$driver_pay_hours += $exp_pay[ $i ];	
          					$driver_hours_worked += $exp_hrs[ $i ];	
          					
          					$mrr_temp.= "
          					<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'>
          						<td colspan='2'>".$exp_acct[ $i ]."</td>
          						<td>Shuttle Run Hrs</td>
          						<td align='right'>$".money_format('',$exp_pay[ $i ])."</td>
          						<td></td>
          						<td colspan='14'>".$exp_hrs[ $i ]."hrs worked.</td>
          					</tr>
          					";
          				}
          			}
          			else
          			{	//normal timesheet, not shuttle run... use hourly pay.
          				$driver_pay_hours += $exp_amnt[ $i ];	
          				$driver_hours_worked += $exp_hrs[ $i ];
          				
          				
          				          								
          				$mrr_temp.= "
          					<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'>
          						<td colspan='2'>".$exp_acct[ $i ]."</td>
          						<td>".($exp_runs[ $i ] > 0 ? "Shuttle Run" : "Time Sheet")."</td>
          						<td align='right'>$".money_format('',$exp_amnt[ $i ])."</td>
          						<td></td>
          						<td colspan='14'>".$exp_notes[ $i ]."</td>
          					</tr>
          				";
          				
          			}
     				//echo "<tr><td colspan='24'><b>Found: ".trim($exp_name[ $i ])."</b></td></tr>";	

     				$current_driver=$exp_driver[ $i ];		
     				$current_driver_name = trim($exp_name[ $i ]);
     			}
     		}
     		if($mrr_found_driver==0)
     		{
     			$mrr_all_drivers_arr[ $mrr_all_drivers ]=$exp_driver[ $i ];
     			$mrr_all_drivers++;	
     			
     			//$mrr_cntr++;
     			//$mrr_found_new=1;		
     			
     			//echo "<tr><td colspan='24'><b>Not Found: ".trim($exp_name[ $i ])."</b></td></tr>";	
     		
     			if(trim($exp_name[ $i ])!="" && (trim($current_driver_name) != trim($exp_name[ $i ]) || $i==0)) 
     			{			
     				
     				if(trim($current_driver_name) != trim($exp_name[ $i ]))
     				{
     					if($i > 0)
     					{
     						$mrr_display.=$mrr_temp;
     						$mrr_report_capture=$mrr_display;
     						echo $mrr_display;
     						show_driver_totals();
     						$mrr_display="<tr class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'><td colspan='24'><hr></td></tr>";	
     					}
     					$mrr_temp="";	
     					
     					$current_driver = $exp_driver[ $i ];
          				$current_driver_name = trim($exp_name[ $i ]);  
     					
     					$this_driver_count = 0;
               			$driver_hours_worked = 0;
               			$driver_miles = 0;
               			$driver_miles_deadhead = 0;
               			$driver_pay = 0;
               			$driver_pay_miles = 0;
               			$driver_pay_hours = 0;
               							
               			$mrr_driver_pay_miles = 0;
               			$mrr_driver_pay_hours = 0;
               							
               			$driver_expenses = 0;
               			$driver_team_miles = 0;
               			$driver_pay_team = 0;
               			$driver_team_miles_deadhead = 0;
               			$driver_hourly_miles = 0;
               			$driver_hourly_miles_deadhead = 0;	
     				}    			  						
     				
     				$mrr_temp= "
     					<tr class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'>
     						<td colspan='8'><br>&nbsp;</td>
     					</tr>
     					<tr class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'>
     						<td colspan='3'>Driver: <a href='admin_drivers.php?id=".$exp_driver[ $i ]."' target='manage_driver_".$exp_driver[ $i ]."'>".$exp_name[ $i ]."</a></td>
     						<td align='right' colspan='6' nowrap><span style='font-weight:bold; color:#AA0000;'>Company Time Sheet and Shuttle Routes</span></td>
     						<td align='right' nowrap colspan='2'></td>
     					</tr>
     				";
     				if(!isset($_POST['summary_only'])) 
     				{     					
     					$mrr_temp.= "<tr class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'>".$header_columns."</tr>";
     				}
     			}
     				
     			
     			if($exp_runs[ $i ] > 0)			
     			{	//shuttle run...flat pay...NOT FOR PAYROLL....only for Caelex Invoice
     				//$driver_pay += $exp_amnt[ $i ];
     			}
     			else
     			{	//normal timesheet, not shuttle run... use hourly pay.
     				$driver_pay_hours += $exp_amnt[ $i ];	
     				$driver_hours_worked += $exp_hrs[ $i ];
     				          			
          			$mrr_temp.= "
          				<tr style='background-color:#d0ffdb' class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'>
          					<td colspan='2'>".$exp_acct[ $i ]."</td>
          					<td>".($exp_runs[ $i ] > 0 ? "Shuttle Run" : "Time Sheet")."</td>
          					<td align='right'>$".money_format('',$exp_amnt[ $i ])."</td>
          					<td></td>
          					<td colspan='14'>".$exp_notes[ $i ]."</td>
          				</tr>
          			";    				
     			}     			
     			$current_driver=$exp_driver[ $i ];		
     			$current_driver_name = trim($exp_name[ $i ]);
     		}		
     	}	
     	if(trim($mrr_temp)!="")
     	{
     		$mrr_display.=$mrr_temp;
     		$mrr_report_capture=$mrr_display;
     		echo $mrr_display;
     		show_driver_totals();
     		$mrr_display.="<tr class='next_driver_reg begin_driver_".$exp_driver[ $i ]."_reg'><td colspan='24'><hr></td></tr>";	
     	}     	
	}
	else
	{
		$carlex_total=0;
		$carlex_total2=0;	
	}
	//<h2>Carlex Time Sheet and Shuttle Runs:</h2>	
	//echo $carlex_section;
	
	//...........................................................................................................................................................................................	
	?>
	<tr class='next_driver_reg'><td colspan='17'><hr><br>SUMMARY:</td></tr>	
	<!---
	<tr class='next_driver_reg' style='font-weight:bold'>
		<td></td>
		<td colspan='3'>
			
			<?=$counter?> dispatch(es)
			
		</td>
		
		<td align='right'><?=number_format($total_miles)?></td>
		<td align='right'><?=number_format($total_deadhead)?></td>
		<td align='right'><?=number_format($total_all_miles)?></td>
		
		<td align='right'><?=$total_hours?></td>
		<td colspan='3'></td>
	</tr>
	
	<tr style='font-weight:bold'>
		<td align='right' colspan='7'>$<?=money_format('',$total_mile_charge)?></td>
		<td align='right' colspan='1'>$<?=money_format('',$total_hours_charge)?></td>
	</tr>
	--->
	<tr class='next_driver_reg'>
		<td colspan='18'>
			<table style='font-weight:bold'>
			<tr>
				<td nowrap>Total Driver O.O./I.C.</td>
				<td align='right'>$<?=money_format('',$grand_tot_ooic)?></td>
			</tr> 
			<!--	$mrr_driver_pay_ooic			$grand_tot_ooic
			<tr>
				<td nowrap>Total Driver Pay <?=($_POST['employer_id']==170 ? "(Flat Rate)" : "(miles)")?> </td>
				<td align='right'>$<?=money_format('',$total_driver_pay - $total_hours_charge - $carlex_total2 - $carlex_total)?></td>
			</tr>
			
			<tr>
				<td nowrap>Total Driver Pay (hours)</td>
				<td align='right'>$<?=money_format('',$total_hours_charge)?></td>
			</tr> 
			<tr>
				<td nowrap>Total Driver Pay (time sheets)</td>
				<td align='right'>$<?=money_format('',$carlex_total)?></td>
			</tr>
			
			<tr>
				<td nowrap>Total Driver Pay (shuttle runs)</td>
				<td align='right'>$<?=money_format('',$carlex_total2)?></td>
			</tr>
			
			<tr>
				<td colspan='5'><hr></td>
			</tr>
			
			<tr>
				<td>Driver Pay</td>
				<td align='right'>$<?=money_format('',$total_driver_pay )?></td>
			</tr> 
			<tr>
				<td>Expenses</td>
				<td style='width:100px' align='right'>$<?=money_format('',$total_expenses)?></td>
			</tr> 
			
			
			
			<tr>
				<td>O.O./I.C. Expenses</td>
				<td style='width:100px' align='right'>$<?=money_format('',$grand_tot_ooic_expenses)?></td>
			</tr> 
			<tr>
				<td>O.O./I.C. Insurance</td>
				<td style='width:100px' align='right'>$<?=money_format('',$grand_tot_ooic_insurance)?></td>
			</tr> 
			
			
			<tr>
				<td>O.O./I.C. Fuel Expenses</td>
				<td style='width:100px' align='right'>$<?=money_format('',$grand_tot_ooic_fuel)?></td>
			</tr> 
			<tr>
				<td colspan='5'><hr></td>
			</tr>
			
			
			<tr>
				<td>Total</td>
				<td style='width:100px' align='right'>$<?=money_format('',$grand_tot_ooic - $grand_tot_ooic_expenses)?></td>
			</tr> 
			---->
			</table>
		</td>
	</tr>
	</table>
	<?
	$pdf = ob_get_contents();
	ob_end_clean();
	
	echo $pdf;
	
	if(trim($_POST['mrr_email_addr'])!="" && isset($_POST['mrr_email_report']))
	{
		$user_name=$defaultsarray['company_name'];
		$From=$defaultsarray['company_email_address'];
		$Subject="";
		if(isset($use_title))			$Subject=$use_title;
		elseif(isset($usetitle))			$Subject=$use_title;
		
		$pdf=str_replace(" href="," name=",$pdf);
		//$pdf=str_replace("</a>","",$pdf);
			
		$sentit=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject,$pdf,$pdf);
		
		$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
		echo "<br><br><b>This report has ".$sent_msg." to '".$_POST['mrr_email_addr_name']."' at E-Mail address '".$_POST['mrr_email_addr']."'.</b><br><br>";
	}
	?>
	
<? } ?>
<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
	function mrr_reload_ooic_report(driver_id)
	{
		window.location.hash = "#driver_"+$driver_id+"_marker";
		window.location.reload();		
	}
	function mrr_show_all_ooic()
	{
		$('.next_driver_reg').show();	
	}
	function mrr_email_ooic_report(driver_id)
	{	
		//$('.next_driver_reg').hide();
		//$('.begin_driver_'+driver_id+'_reg').show();
		
		//$.prompt("Preparing to E-Mail Driver "+driver_id+"...  Just Kidding!  Coming Soon, though :)");
		
		//var blob=$('.admin_menu2').html();
		
		//"html": blob,
		
		$.ajax({
			url: "ajax.php?cmd=mrr_ooic_auto_emailer",
			type: "post",
			dataType: "xml",
			data: {
				
				"driver_id": driver_id
			},
			error: function() {
				$.prompt("Error: Unable to E-Mail Driver ID "+driver_id+" his/her O.O.I.C. section from this payroll report. :( ");
			},
			success: function(xml) {				
				if($(xml).find("rslt").text()=="0")
				{
					$.prompt("Error: E-Mail FAILED for Driver ID "+driver_id+" his/her O.O.I.C. section from this payroll report. :( <br><br>"+$(xml).find("Report").text()+"");
				}
				else
				{				
					$.prompt("E-Mailed Driver ID "+driver_id+" his/her O.O.I.C. section from this payroll report. :) ");
					//mrr_show_all_ooic();	
					
					$('.driver_'+driver_id+'_marker').focus();
				}
				
			}
		});		
	}
		
    function mrr_ooic_auto_update_route(id,x,lineid,base_bill)
    {
        var rt_rate=parseFloat($('#update_switch_expense_'+id+'_'+x+'').val());
        var rt_ooic_rate=parseFloat(base_bill);        //$('#update_rate_'+lineid+'').val()

        //$.prompt("Preparing to Update Route/Option ID "+id+" (v="+x+") with $"+rt_rate+" for the Route Rate Switch Expense...  Just Kidding!  Coming Soon, though :)");

        $.ajax({
            url: "ajax.php?cmd=mrr_ooic_auto_update_route",
            type: "post",
            dataType: "xml",
            data: {
                "rate": rt_rate,
                "rt_ooic_rate": rt_ooic_rate,
                "dispatch_id": lineid,
                "id": id
            },
            error: function() {
                $.prompt("Error: Cannot Update Route/Option ID "+id+" (v="+x+") with $"+rt_rate+" for the Switch Expense. :( ");
            },
            success: function(xml) {
                $.prompt("Updated Route/Option ID "+id+" (v="+x+") with $"+rt_rate+" for the Switch Expense. :) ");
                if($(xml).find('NewRate').text()!="")       $('#update_rate_'+lineid+'').val($(xml).find('NewRate').text());   
            }
        });
    }
    
	function mrr_ooic_auto_update(id)
	{
		var disp_rate=get_amount($('#update_rate_'+id+'').val());
		
		//$.prompt("Preparing to Update Dispatch ID "+id+" with $"+disp_rate+" for the Special Flat Rate Cost...  Just Kidding!  Coming Soon, though :)");
		
		$.ajax({
			url: "ajax.php?cmd=mrr_ooic_auto_update",
			type: "post",
			dataType: "xml",
			data: {
				"rate": disp_rate,
				"id": id
			},
			error: function() {
				$.prompt("Error: Cannot Update Dispatch ID "+id+" with $"+disp_rate+" for the Special Flat Rate Cost. :( ");
			},
			success: function(xml) {				
				$.prompt("Updated Dispatch ID "+id+" with $"+disp_rate+" for the Special Flat Rate Cost. :) ");			
			}
		});	
	}
	
	function mrr_ooic_auto_update_misc(id)
    {
        var misc_amnt=get_amount($('#update_ooic_misc_amnt_'+id+'').val());
        var mis_desc=$('#update_ooic_misc_'+id+'').val();

        $.ajax({
            url: "ajax.php?cmd=mrr_ooic_auto_update_misc",
            type: "post",
            dataType: "xml",
            data: {
                "amnt": misc_amnt,
                "desc": mis_desc,
                "id": id
            },
            error: function() {
                $.prompt("Error: Cannot Update this Driver's Misc Pay with $"+misc_amnt+". :( ");
            },
            success: function(xml) {
                $.prompt("Updated this Driver's Misc Pay entry with $"+misc_amnt+".<br><br>Reload this report to see it in the driver total(s) :) ");
            }
        });
    }
    
    <? if($mrr_warning_required > 0) { ?>
        $.prompt("This report has made some corrections in this run through...<br><br>Press the submit button again to rerun the report to see the updated totals. :) ");
    <? } ?>
</script>
<? include('footer.php') ?>