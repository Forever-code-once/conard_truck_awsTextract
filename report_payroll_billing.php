<? $usetitle = "Report - Payroll Billing" ?>
<? include('header.php') ?>
<?
	$use_title = "Report - Payroll Billing";
	
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}

	if(!isset($_POST['date_from'])) 			$_POST['date_from'] = date("n/1/Y",time());
	if(!isset($_POST['date_to'])) 			$_POST['date_to'] = date("n/j/Y", time());
	if(!isset($_POST['driver_id'])) 			$_POST['driver_id'] = 0;
	if(!isset($_POST['employer_id'])) 			$_POST['employer_id'] = 0;
	if(!isset($_POST['payroll_mode']))			$_POST['payroll_mode']=0;
	
	//new email sending line to submit and send the email directly from this report. 
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))		$_POST['build_report'] = 1;
		
	if(!isset($_POST['report_payroll_date'])) 	$_POST['report_payroll_date'] ="";

    $total_bonus_pay = 0;
	$use_hourly_bonus_calculator=0;
    if(date("Y-m-d", strtotime($_POST['date_to'])) >= "2021-09-06")     $use_hourly_bonus_calculator=1;
		
	$truck_pay_array[] = array();
	$total_driver_pay = 0;
	$running_total = 0;
	$driver_pay_mode=$_POST['payroll_mode'];
	
	$skip_overtime_separation=0;
	
	if( date("Ymd",strtotime($_POST['date_to'])) >= "20151123")	$skip_overtime_separation=1;
	
	?>
	<form action="<?=$SCRIPT_NAME ?>" method="post">
	<table width='1200'>
		<tr>
			<td valign='top' width='600'>	
			<?	
          	$rfilter = new report_filter();
          	$rfilter->show_driver 			= true;
          	//$rfilter->show_truck 			= true;
          	$rfilter->show_payroll_date		= true;
          	$rfilter->show_employers 		= true;
          	$rfilter->summary_only	 		= true;
          	$rfilter->payroll_mode	 		= true;
          	$rfilter->mrr_no_form_enclosed	= true;	
          	$rfilter->team_choice	 		= true;
          	$rfilter->show_font_size			= true;
          	$rfilter->mrr_send_email_here		= true;
          	$rfilter->show_filter();
          	?>
          	</td>
			<td valign='top'>
				<?	
				if(!isset($_POST['print']))
				 {
				 ?>
     				<br><br>
     				<table class='admin_menu2 font_display_section' width='600'>
     				<tr>
     					<td valign='top'><b>Notes:</b></td>
     					<td valign='top'>
     						<span class='alert'><b>
     							<?=($driver_pay_mode==0 ? "Using Driver Charged Mode." : "Using Driver Paid Mode.") ?>
     							<?=($skip_overtime_separation==0 ? "<br>Overtime Is Included With Pay." : "") ?>     							
     						</b></span>
     					</td>
     				</tr>
     				<tr>
     					<td valign='top' width='200'><b>Select Employer:</b></td>
     					<td valign='top'>One at a time is recommended.</td>
     				</tr>
     				<tr>
     					<td valign='top'><b>Select Others:</b></td>
     					<td valign='top'>Then choose Date Range, Driver, and other fields as needed</td>
     				</tr>
     				<tr>
     					<td valign='top'><b>"Payroll Report - Billing"</b></td>
     					<td valign='top'>This report should be identical to the "Payroll Report" report.</td>
     				</tr>
     				<tr>
     					<td valign='top'><b>"Bill Preview"</b></td>
     					<td valign='top'>Shows a preview of what final items will be on the bill.  
     							Press "Create this Bill" to make the bill for that employer. "Processing... Saved as bill." 
     							at the bottome of the Bill Preview confirms it has been created.</td>
     				</tr>
     				</table>
				<?
				}
				?>
			</td>
		</tr>
	</table>
	<?
	
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
				
		$sql = "
			select trucks_log.*,
				trucks.owner_operated as truck_owner_operator,
				drivers.owner_operator as driver_owner_operator,
				drivers.id as driver_id,
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
				drivers2.name_driver_first as name2_driver_first,
				drivers2.name_driver_last as name2_driver_last,
				drivers2.".$field1." as driver2_charged_per_mile,
				drivers2.".$field2." as driver2_charged_per_hour,
				drivers2.".$field3." as driver2_charged_per_mile_team,
				drivers2.".$field4." as driver2_charged_per_hour_team,
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
				".($_POST['driver_id'] > 0 ? " and trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".$employ_adder."
				".($_POST['team_choice'] == 0 ? "" : ($_POST['team_choice'] == '1' ? " and trucks_log.driver2_id > 0 " : " and trucks_log.driver2_id = 0 "))."
			
			union all
			
			select trucks_log.*,
				trucks.owner_operated as truck_owner_operator,
				drivers2.owner_operator as driver_owner_operator,
				drivers2.id as driver_id,
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
				drivers.name_driver_first as name2_driver_first,
				drivers.name_driver_last as name2_driver_last,
				drivers.".$field1." as driver2_charged_per_mile,
				drivers.".$field2." as driver2_charged_per_hour,
				drivers.".$field3." as driver2_charged_per_mile_team,
				drivers.".$field4." as driver2_charged_per_hour_team,
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
				".($_POST['driver_id'] > 0 ? " and trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".$employ_adder2."
				".($_POST['team_choice'] == 0 ? "" : ($_POST['team_choice'] == '1' ? " and trucks_log.driver2_id > 0 " : " and trucks_log.driver2_id = 0 "))."
			
			order by name_driver_last, name_driver_first, linedate
		";
		
		//echo "<br>Query is:<br>---".$sql."---<br>";
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
	
	
	$mrr_process=0;
	if(isset($_POST['employer_row_cntr']))
	{
		for($i=0;$i < $_POST['employer_row_cntr']; $i++)
		{
			if(isset($_POST["mrr_employer_".$i."_go_btn"]))	
			{
				$mrr_process++;
				$_POST['build_report'] = 1;
     		}
     	}
	}
	
	$mrr_enum=0;
	$mrr_exps[0]=0;
	
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
		
		global $cur_overtime_hours;
     	global $cur_overtime_paid;
         
        global $use_hourly_bonus_calculator;
        global $total_bonus_pay;
     	
     	$driver_hours_worked-=$driver_pre_hours_worked;
     	
     	$overtime_disp="&nbsp;";     	
     	if($cur_overtime_hours > 0 || $cur_overtime_paid > 0)
     	{
     		$overtime_disp="<span class='alert'><b>".number_format($cur_overtime_hours,2)." Hours Overtime Included...$".number_format($cur_overtime_paid,2)."</b></span>";  	
     	}
         
         //$misc_id=0;
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
              //$misc_id=$rowd['id'];
              $amnt=$rowd['misc_amount'];
              $amnt_desc=trim($rowd['misc_desc']);
         }
         echo "
			<tr style='background-color:#d0deff'>
				<td colspan='3'>Misc Driver Pay</td>
				<td align='right'>$".number_format($amnt,2)."</td>
				<td colspan='16'>".$amnt_desc."</td>
			</tr>
		";
     	
		if($driver_expenses > 0) {
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Driver Expenses</td>
					<td align='right'>$".money_format('',$driver_expenses)."</td>
					<td>&nbsp;</td>
					<td colspan='9'>&nbsp;</td>
					<td align='right'>$".money_format('',$driver_expenses)."</td>
					<td colspan='5'>&nbsp;</td>
				</tr>
			";
		}
		
		if($driver_team_miles > 0) {
			echo "
				<tr style='background-color:#d0deff'>
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
					
					<td colspan='10'>&nbsp;</td>
				</tr>
			";
		}
         
        $driver_pay_ooic+=$amnt;
        $mrr_driver_pay_ooic+=$amnt;
        $new_bonus_pay=0;
        
        if($driver_pay_ooic) 
		{
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Driver O.O./I.C.: </td>
					
					<td align='right'>$".money_format('',$driver_pay_ooic)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'></td>
					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>$".money_format('',$mrr_driver_pay_ooic)."</td>
					
					<td colspan='10'><b>< - - Excluded from Payroll Bills</b></td>
				</tr>
			";
		}
		
		
		if($driver_pay_miles && $driver_pay_hours) {
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Driver Pay for Miles: </td>
					
					<td align='right'>$".money_format('',$driver_pay_miles)."</td>
					<td align='right'>".number_format($driver_miles - $driver_team_miles)."</td>
					<td align='right'>".number_format($driver_miles_deadhead)."</td>
					<td align='right'>".number_format($driver_miles - $driver_team_miles + $driver_miles_deadhead)."</td>
					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>$".money_format('',$mrr_driver_pay_miles)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					
					<td colspan='10'>&nbsp;</td>
				</tr>
			";
             
             //New Bonus Pay calculator ....MRR Added 8/31/2021 for Dale, but only for Sept 6th and later of 2021.  Switch turns the calculator off.
             $new_bonus_pay=0;             
             $use_hourly_bonus_calculator=0;     //disabled for Dale on 08/03/2023...MRR...KILL SWITCH
             if($use_hourly_bonus_calculator > 0)
             {
                  $new_bonus_pay=mrr_calculate_bonus_pay_from_hours($driver_hours_worked,$driver_id);
                  
                  $bonus_reg_hours=mrr_get_driver_bonus_pay_min_hrs();
                  $extra_bonus_hours = $driver_hours_worked - $bonus_reg_hours;
                  
                  if($new_bonus_pay > 0 && $extra_bonus_hours > 0)
                  {          // style='background-color:#d0deff'
                       echo "
                        <tr style='background-color:#d0ffdb'>
                            <td colspan='3'>Driver Pay for Hours BONUS: </td>
                            
                            <td align='right'>$" . money_format('', $new_bonus_pay) . "</td>
                            <td align='right'></td>
                            <td align='right'></td>
                            <td align='right'></td>                            
                            
                            <td align='right'>" . number_format($extra_bonus_hours,2) . "</td>	
                            <td align='right'>&nbsp;</td>				
                            <td align='right'>&nbsp;</td>
                            <td align='right'>&nbsp;</td>
                            <td align='right'>$" . money_format('', $new_bonus_pay) . "</td>
                            <td align='right'>&nbsp;</td>
                            
                            <td colspan='10'>BONUS</td>
                        </tr>
                    ";
                       $total_bonus_pay+=$new_bonus_pay;
                  }
             }
             //.....................................................................................................................................
			
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Driver Pay for Hours: </td>
					
					<td align='right'>$".money_format('',$driver_pay_hours)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'></td>
					
					<td align='right'>&nbsp;</td>
					<td align='right'>".(number_format($driver_hours_worked) == $driver_hours_worked ? number_format($driver_hours_worked) : $driver_hours_worked)."</td>					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>$".money_format('',$mrr_driver_pay_hours)."</td>
					<td align='right'>&nbsp;</td>
					
					<td colspan='10'>".$overtime_disp."</td>
				</tr>
			";
             
            $mrr_driver_pay_hours += $new_bonus_pay;
            $driver_pay_hours += $new_bonus_pay;
		}
		elseif($driver_pay_miles) {
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Driver Pay for Miles: </td>
					
					<td align='right'>$".money_format('',$driver_pay_miles)."</td>
					<td align='right'>".number_format($driver_miles - $driver_team_miles)."</td>
					<td align='right'>".number_format($driver_miles_deadhead)."</td>
					<td align='right'>".number_format($driver_miles - $driver_team_miles + $driver_miles_deadhead)."</td>
					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>$".money_format('',$mrr_driver_pay_miles)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					
					<td colspan='10'>&nbsp;</td>
				</tr>
			";
		}
		elseif($driver_pay_hours) 
        {
             //New Bonus Pay calculator ....MRR Added 8/31/2021 for Dale, but only for Sept 6th and later of 2021.  Switch turns the calculator off.
             $new_bonus_pay=0;
             $use_hourly_bonus_calculator=0;     //disabled for Dale on 08/03/2023...MRR...KILL SWITCH
             if($use_hourly_bonus_calculator > 0)
             {
                  $new_bonus_pay = mrr_calculate_bonus_pay_from_hours($driver_hours_worked, $driver_id);
          
                  $bonus_reg_hours = mrr_get_driver_bonus_pay_min_hrs();
                  $extra_bonus_hours = $driver_hours_worked - $bonus_reg_hours;
          
                  if($new_bonus_pay > 0 && $extra_bonus_hours > 0)
                  {            // style='background-color:#d0deff'           
                       echo "
                        <tr style='background-color:#d0ffdb'>
                            <td colspan='3'>Driver Pay for Hours BONUS: </td>
                            
                            <td align='right'>$" . money_format('', $new_bonus_pay) . "</td>
                            <td align='right'></td>
                            <td align='right'></td>
                            <td align='right'></td>
                            
                            <td align='right'>" . number_format($extra_bonus_hours,2) . "</td>
                            <td align='right'>&nbsp;</td>                            					
                            <td align='right'>&nbsp;</td>
                            <td align='right'>&nbsp;</td>
                            <td align='right'>$" . money_format('', $new_bonus_pay) . "</td>
                            <td align='right'>&nbsp;</td>
                            
                            <td colspan='10'>BONUS</td>
                        </tr>
                    ";
                       $total_bonus_pay+=$new_bonus_pay;
                  }
             }
             //.....................................................................................................................................
             
            echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Driver Pay for Hours: </td>
					
					<td align='right'>$".money_format('',$driver_pay_hours)."</td>
					<td align='right'>".number_format($driver_hourly_miles)."</td>
					<td align='right'>".number_format($driver_hourly_miles_deadhead)."</td>
					<td align='right'>".number_format($driver_hourly_miles + $driver_hourly_miles_deadhead)."</td>
					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>$".money_format('',$mrr_driver_pay_hours)."</td>
					<td align='right'>&nbsp;</td>
					
					<td colspan='10'>".$overtime_disp."</td>
				</tr>
			";
            $mrr_driver_pay_hours += $new_bonus_pay;
            $driver_pay_hours += $new_bonus_pay;
		}
		
		$total_driver_pay += ($driver_pay_miles + $driver_pay_hours + $driver_pay_team);		//$driver_pay + $driver_expenses + $driver_pay_ooic
		//$total_driver_pay += $driver_miles + $driver_miles_deadhead + $driver_hourly_miles + $driver_hourly_miles_deadhead + $driver_team_miles_deadhead;
		$running_total += ($driver_pay_miles + $driver_pay_hours + $driver_pay_team);			//$driver_pay + $driver_expenses + $driver_pay_ooic
		echo "
			<tr style='background-color:#d0deff'>
				<td colspan='3'>Total Driver Pay: </td>
				
				<td align='right'>$".money_format('',$driver_pay + $driver_pay_team + $driver_expenses + $amnt + $new_bonus_pay)."</td>
				<td align='right'>".number_format($driver_miles)."</td>
				<td align='right'>".number_format($driver_miles_deadhead + $driver_team_miles_deadhead)."</td>
				<td align='right'>".number_format($driver_miles + $driver_miles_deadhead + $driver_team_miles_deadhead)."</td>
				
				<td align='right'>".(number_format($driver_hours_worked) == $driver_hours_worked ? number_format($driver_hours_worked) : $driver_hours_worked)."</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>$".money_format('',$mrr_driver_pay_miles)."</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>$".money_format('',$mrr_driver_pay_hours)."</td>
				<td align='right'>$".money_format('',$mrr_driver_pay_ooic)."</td>
				
				<td colspan='10'>".($driver_expenses > 0 ? "+ $".number_format($driver_expenses,2)." Expenses" : "&nbsp;")."</td>
			</tr>			
		";
	}
	function get_master_expense_query($employer=0,$driver_id=0) 
	{			
			//from drivers_expenses,drivers,option_values
			//and drivers.id = drivers_expenses.driver_id
			//and expense_type_id=option_values.id	
		global $use_starting_point;		
		$sql = "
			select drivers_expenses.*,
				drivers.name_driver_first,
				drivers.name_driver_last,
				option_values.fvalue as exp_type_name							
			from drivers_expenses
     				left join drivers on drivers.id = drivers_expenses.driver_id
     				left join option_values on option_values.id = drivers_expenses.expense_type_id
			where drivers_expenses.deleted = 0				
				and drivers_expenses.payroll = 1				
				and drivers.deleted = 0
				and option_values.deleted = 0
				".($employer != 0 ? " and drivers.employer_id = '".sql_friendly($employer)."'" : '') ."
				".($driver_id != 0 ? " and drivers.id = '".sql_friendly($driver_id)."'" : '') ."
				and drivers_expenses.linedate>='".date("Y-m-d", strtotime($use_starting_point))." 00:00:00' 
     			and drivers_expenses.linedate<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
			order by drivers_expenses.linedate asc
				";	
		//d($sql);
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
		
		return $data;
	}
	
	function get_master_vacation_advance_query($employer=0,$driver_id=0) 
	{	
		global $use_starting_point;
		
		$sql = "		
			select driver_vacation_advances_dates.*,
     			driver_vacation_advances.comments,
     			drivers.name_driver_first,
				drivers.name_driver_last     			
     					
     		from driver_vacation_advances_dates
     			left join driver_vacation_advances on driver_vacation_advances.id=driver_vacation_advances_dates.dva_id
     			left join drivers on drivers.id = driver_vacation_advances_dates.driver_id
     		where driver_vacation_advances_dates.deleted = 0
     			and driver_vacation_advances.deleted = 0
     			".($employer != 0 ? " and drivers.employer_id = '".sql_friendly($employer)."'" : '') ."
				".($driver_id != 0 ? " and drivers.id = '".sql_friendly($driver_id)."'" : '') ."
     			and drivers.deleted = 0
     			and driver_vacation_advances_dates.linedate>='".date("Y-m-d", strtotime($use_starting_point))." 00:00:00' 
     			and driver_vacation_advances_dates.linedate<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
     		order by driver_vacation_advances_dates.linedate asc
				";	
		//d($sql);
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
		
		return $data;
	}
	
	$mrr_output2="";
	
	$pay_header1="Labor Mile";
	$pay_header2="Pay Mile";
	$pay_header3="Labor Hour";
	$pay_header4="Pay Hour";
	$pay_header5="O.O.I.C.";
	if($_POST['employer_id'] == 170)	
	{
		$pay_header1="&nbsp;";
		$pay_header2="Price";
		$pay_header3="Fuel";
		$pay_header4="Flat Rate";
		$pay_header5="O.O.I.C.";
	}
	
	$header_columns = "
		<tr style='font-weight:bold'>
			<td>Load ID</td>
			<td>Dispatch ID</td>
			<td>Origin</td>
			<td>Destination</td>
			<td align='right'>Miles</td>
			<td align='right'>Deadhead</td>
			<td align='right'>Total Miles</td>
			<td align='right'>Hours Worked</td>
			<td align='right'>".$pay_header1."</td>
			<td align='right'>".$pay_header2."</td>
			<td align='right'>".$pay_header3."</td>
			<td align='right'>".$pay_header4."</td>
			<td align='right'>".$pay_header5."</td>
			<td>Date</td>
			<td>Truck</td>
			<td>Trailer</td>
			<td>Customer</td>
			<td>Employer</td>
		</tr>	
	";
	//echo "QUERY: ".get_master_query()."<br><br>";
	$data_master = get_master_query();
	
	$uuid = createuuid();
	$export_filename = "payroll_$uuid";
		
	ob_start();
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;text-align:left'>
	<tr>
		<td colspan='16'>
			<center>
			<span class='section_heading'>Payroll Report</span>
			<br>
			<?= $print_header_label ?>
			</center>
			<br>
		</td>
	</tr>
	<?
	//store employer settings...
	$empl_counter=0;
	$employers[0]=0;
	$employer_used[0]=0;
	$employer_names[0]="";
	$employer_truck_cntr[ 0 ]=0;
	$employer_truck_ids[ 0 ][0]=0;
	$employer_truck_names[ 0 ][0]="";
	//drivers for each truck
	$employer_truck_driver_cntr[ 0 ][0][0]=0;
	$employer_truck_driver_ids[ 0 ][0][0]=0;
	$employer_truck_driver_emps[ 0 ][0][0]=0;
	$employer_truck_driver_names[ 0 ][0][0]="";
	$employer_truck_driver_hours[ 0 ][0][0]=0;
	$employer_truck_driver_miles[ 0 ][0][0]=0;
	$employer_truck_driver_miles_team[ 0 ][0][0]=0;
	$employer_truck_driver_stops[ 0 ][0][0]=0;
	$employer_truck_driver_bonus[ 0 ][0][0]=0;
	$employer_truck_driver_expense_acct[ 0 ][0][0]="";
	$employer_truck_driver_expense_prices[ 0 ][0][0]="";
	$employer_truck_driver_expenses[ 0 ][0][0]=0;
	$employer_truck_driver_expense_rate[ 0 ][0][0]=0;
	$employer_truck_driver_hours_rate[ 0 ][0][0]=0;
	$employer_truck_driver_miles_rate[ 0 ][0][0]=0;
	$employer_truck_driver_miles_rate_team[ 0 ][0][0]=0;
	$employer_truck_driver_stops_rate[ 0 ][0][0]=0;
	$employer_truck_driver_bonus_rate[ 0 ][0][0]=0;
	
	$employer_truck_driver_miles_charge[ 0 ][0][0]=0;
	$employer_truck_driver_miles_charge_team[ 0 ][0][0]=0;
	$employer_truck_driver_hours_charge[ 0 ][0][0]=0;
	$employer_truck_driver_hours_charge_team[ 0 ][0][0]=0;
	
	$employer_extras[ 0 ]=0;
	$employer_extra_expenses[ 0 ][0]=0;
	$employer_extra_expenses_acct[ 0 ][0]=0;
	$employer_extra_expenses_acct_name[ 0 ][0]=0;
	$employer_extra_expenses_desc[ 0 ][0]="";
	$employer_extra_expenses_driver[ 0 ][0]=0;
	$employer_extra_expenses_driver_name[ 0 ][0]="";
	$employer_extra_expenses_amnt[ 0 ][0]=0;
	
	$overtime_paid=0;
	$employer_truck_driver_overtime[ 0 ][0][0]=0;
	$employer_truck_driver_overtime_amnt[ 0 ][0][0]=0;
	
	$employer_truck_driver_full_hours[ 0 ][0][0]=0;
	$employer_truck_driver_full_hours_pay[ 0 ][0][0]=0;
	
	$employer_truck_driver_full_miles[ 0 ][0][0]=0;
	$employer_truck_driver_full_miles_pay[ 0 ][0][0]=0;
	
	// get the list of employers
     $sql2="
			select option_values.id as use_val,
          		option_values.fvalue as use_disp
          	from option_values,option_cat
          	where option_values.deleted=0
          		and option_cat.id=option_values.cat_id
          		and option_cat.cat_name='employer_list'
          	order by option_values.fvalue asc
          ";          
     $data_emp = simple_query($sql2);
     while($row_emp = mysqli_fetch_array($data_emp)) 
     {
		$employers[ $empl_counter ]=$row_emp['use_val'];
		$employer_names[ $empl_counter ]=$row_emp['use_disp'];
		//trucks array
		$employer_truck_cntr[ $empl_counter ]=0;
		$employer_truck_ids[ $empl_counter ][0]=0;
		$employer_truck_names[ $empl_counter ][0]="";
		//drivers for each truck
		$employer_truck_driver_cntr[ $empl_counter ][0]=0;
		$employer_truck_driver_ids[ $empl_counter ][0][0]=0;
		$employer_truck_driver_emps[ $empl_counter ][0][0]=0;
		$employer_truck_driver_names[ $empl_counter ][0][0]="";
		$employer_truck_driver_hours_charge[ $empl_counter ][0][0]=0;
		$employer_truck_driver_miles_charge[ $empl_counter ][0][0]=0;
		
		$employer_truck_driver_hours_charge_team[ $empl_counter ][0][0]=0;
		$employer_truck_driver_miles_charge_team[ $empl_counter ][0][0]=0;
		
		$employer_truck_driver_hours[ $empl_counter ][0][0]=0;
		$employer_truck_driver_miles[ $empl_counter ][0][0]=0;
		$employer_truck_driver_stops[ $empl_counter ][0][0]=0;
		$employer_truck_driver_bonus[ $empl_counter ][0][0]=0;
		$employer_truck_driver_expenses[ $empl_counter ][0][0]=0;
		$employer_truck_driver_expense_acct[ $empl_counter ][0][0]="";
		$employer_truck_driver_expense_prices[ $empl_counter ][0][0]="";
		$employer_truck_driver_expense_rate[ $empl_counter ][0][0]=0;
		$employer_truck_driver_hours_rate[ $empl_counter ][0][0]=0;
		$employer_truck_driver_miles_rate[ $empl_counter ][0][0]=0;
		$employer_truck_driver_stops_rate[ $empl_counter ][0][0]=0;
		$employer_truck_driver_bonus_rate[ $empl_counter ][0][0]=0;
		
		$employer_truck_driver_miles_team[ $empl_counter ][0][0]=0;
		$employer_truck_driver_miles_rate_team[ $empl_counter ][0][0]=0;
		
		$employer_extras[ $empl_counter ]=0;
		$employer_extra_expenses[ $empl_counter ][0]=0;
		$employer_extra_expenses_acct[ $empl_counter ][0]=0;
		$employer_extra_expenses_acct_name[ $empl_counter ][0]=0;
		$employer_extra_expenses_desc[ $empl_counter ][0]="";
		$employer_extra_expenses_driver[ $empl_counter ][0]=0;
		$employer_extra_expenses_driver_name[ $empl_counter ][0]="";
		$employer_extra_expenses_amnt[ $empl_counter ][0]=0;
				
		$employer_truck_driver_overtime[ $empl_counter ][0][0]=0;
		$employer_truck_driver_overtime_amnt[ $empl_counter ][0][0]=0;
		
		$employer_truck_driver_full_hours[ $empl_counter ][0][0]=0;
		$employer_truck_driver_full_hours_pay[ $empl_counter ][0][0]=0;
	
		$employer_truck_driver_full_miles[ $empl_counter ][0][0]=0;
		$employer_truck_driver_full_miles_pay[ $empl_counter ][0][0]=0;
		
		$empl_counter++;
     }	
     //..................................................................................................
	
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
		
		$driver_pay_miles = 0;
		$driver_pay_hours = 0;
		$driver_pay_ooic = 0;
		
		$total_hours_charge = 0;
		$total_expenses = 0;
		$total_expenses_mrr=0;
		
		$cur_overtime_hours=0;
		$cur_overtime_paid=0;
		
		$label_prefix="Charged ";
		if($driver_pay_mode ==1)		$label_prefix="Paid ";
		
		$max_overtime_hours= (int)$defaultsarray['overtime_hours_min'];
		$def_overtime_pay_rate=1.00;								//likely will be changed to 1.50 (time and a half)
		if(is_numeric($defaultsarray['overtime_def_rate']))
		{
			$def_overtime_pay_rate=$defaultsarray['overtime_def_rate'];
		}

		while($row = mysqli_fetch_array($data_master)) 
		{
			if(!isset($truck_pay_array[$row['name_truck']])) $truck_pay_array[$row['name_truck']] = 0;
			
			$driver1_employer_id=$row['driver1_employer_id'];
			$driver2_employer_id=$row['driver2_employer_id'];
			
			$truck_oo_ic=$row['truck_owner_operator'];
			$driver_oo_ic=$row['driver_owner_operator'];
			$flat_cost_rate=$row['flat_cost_rate'] - $row['flat_cost_fuel_rate'];
			if($flat_cost_rate < 0)		$flat_cost_rate=0;
			
			$temp_employer_id=$row['employer_id'];
			
			$mrr_divide_exp=0;
			$mrr_employer_skip=0;	
			
			if($row['driver2_id'] > 0)		$mrr_divide_exp=1;
			
			if($_POST['employer_id'] > 0 && $row['driver2_id'] > 0)
			{	//used as a bypass for this driver, not the right employer...					
				if($row['driver_position']==1 && $row['driver1_employer_id']!=$_POST['employer_id'])		$mrr_employer_skip=1;
				if($row['driver_position']==2 && $row['driver1_employer_id']!=$_POST['employer_id'])		$mrr_employer_skip=1;							
			}
			
			if($row['driver_position']==2 && $row['driver1_employer_id']==$_POST['employer_id'])
			{	//second driver is employed by the selected driver (even if the first driver is not)
				$mrr_employer_skip=0;
			}
						
			if($mrr_employer_skip==0)
			{     			
     			if($current_driver != $row['driver_id']) 
     			{
     				if($current_driver != '') 
     				{
     					//show_driver_totals($driver_miles, $driver_miles_deadhead, $driver_hours_worked, $driver_per_mile, $driver_per_hour);
     					show_driver_totals($current_truck_id,$current_driver_id);
     					echo "<tr><td colspan='24'><hr></td></tr>";
     				}
     				$cur_overtime_hours=0;
     				$cur_overtime_paid=0;
     				
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
                    
     				/*
                     //$misc_id=0;
                     $amnt=0;
                     $amnt_desc="";
                     $sqld="
			            select *
			            from driver_ooic_misc_exp
			            where driver_id='".sql_friendly($current_driver_id)."'
				            and linedate_from = '".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00' and linedate_to = '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
			            order by id desc
		             ";
                     $datad=simple_query($sqld);
                     if($rowd=mysqli_fetch_array($datad))
                     {
                          //$misc_id=$rowd['id'];
                          $amnt=$rowd['misc_amount'];
                          $amnt_desc=trim($rowd['misc_desc']);
                     }
                     $driver_pay_ooic += $amnt;
                     $mrr_driver_pay_ooic+=$amnt;
     				*/
     				
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
     						and drivers_expenses.linedate between '".date("Y-m-d", strtotime($use_starting_point))."' and '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
     					order by linedate
     				";
     				$data_driver_expenses = simple_query($sql);
     				
     				
     					//		[".$row['driver1_employer_id'].", ".$row['driver2_employer_id']."] 
     				echo "     					
     					<tr>
     						<td colspan='3'>
     							Driver: <a href='admin_drivers.php?id=$row[driver_id]' target='manage_driver_$row[driver_id]'>$current_driver_name</a>  (".$label_prefix.") 							
     							
     						</td>
     						<td align='right' colspan='2' nowrap>&nbsp;&nbsp;&nbsp; ".$label_prefix."Single Per Mile: ".($row['charged_per_mile'] == 0 ? "<span class='alert'>" : "<span>")."$".number_format($row['charged_per_mile'], 3)."</span></td>
     						<td align='right' nowrap colspan='8'>&nbsp;&nbsp;&nbsp; ".$label_prefix."Per Hour: $".money_format('',$row['charged_per_hour'])."</td>
     					</tr>
     				";
     				echo "
     					
     					<tr>
     						<td colspan='3'>&nbsp;</td>
     						<td align='right' colspan='2' nowrap>&nbsp;&nbsp;&nbsp; ".$label_prefix."Team Per Mile: ".($row['charged_per_mile_team'] == 0 ? "<span class='alert'>" : "<span>")."$".number_format($row['charged_per_mile_team'], 3)."</span></td>
     						<td align='right' nowrap colspan='8'>&nbsp;&nbsp;&nbsp; ".$label_prefix."Team Per Hour: ".($row['charged_per_hour_team'] == 0 ? "<span class='alert'>" : "<span>")."$".number_format($row['charged_per_hour_team'], 2)."</span></td>
     					</tr>
     				";
     				if(!isset($_POST['summary_only'])) {
     					echo $header_columns;
     				}
     				
     				while($row_driver_expenses = mysqli_fetch_array($data_driver_expenses)) {
     					if(trim($row_driver_expenses['expense_type'])!='Comcheck')
     					{     					
               				$total_expenses+=$row_driver_expenses['amount_billable'];
               				$driver_expenses += $row_driver_expenses['amount_billable'];
               				$total_expenses_mrr+=$row_driver_expenses['amount_billable'];
               				
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
          						echo "
          							<tr style='background-color:#d0ffdb'>
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
               			
               			$total_expenses_mrr+=$dva_pay;
               			
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
     						echo "
     							<tr style='background-color:#d0ffdb'>
     								<td colspan='2'> ".$account."</td>
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
     			$tmp_hours_over=0;
     			$tmp_hours_paid_over=0;
     			
     			$cur_driver_tmp_hours=0;
     			$cur_driver_tmp_hours_pay=0;
     			$cur_driver_tmp_miles=0;
     			$cur_driver_tmp_miles_pay=0;     			
     			
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
          			
          			$cur_driver_tmp_hours=0;
          			$cur_driver_tmp_hours_pay=$tmp_hourly_pay;
          			$cur_driver_tmp_miles=($row['miles_deadhead'] + $row['miles']);
          			//$cur_driver_tmp_miles_pay=$tmp_hourly_pay;          			          			
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
          				   				 				     				
          				$tmp_hourly_pay=$tmp_hours_left * $mrr_per_hour_labor;			//get the regular pay     
          				
          				$tmp_hours_paid_over=($tmp_hours_over * $mrr_per_hour_labor * $def_overtime_pay_rate);
          				
          				$tmp_hourly_pay+=$tmp_hours_paid_over;						//add the overtime pay
          				$overtime_paid+=$tmp_hours_paid_over;     				
     					
     					$cur_overtime_hours+=$tmp_hours_over;
          				$cur_overtime_paid+=$tmp_hours_paid_over;
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
          			
          			$cur_driver_tmp_hours=$row['hours_worked'];
          			$cur_driver_tmp_hours_pay=$tmp_hourly_pay;
          			$cur_driver_tmp_miles=($row['miles_deadhead'] + $row['miles']);
          			$cur_driver_tmp_miles_pay=(($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor);          			
          			//.............................................................................................................................................................
          			
          			if(!isset($row['dispatch_charged_per_mile']))		$row['dispatch_charged_per_mile']=0;
          			if(!isset($row['dispatch_charged_per_hour']))		$row['dispatch_charged_per_hour']=0;
          			
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
     			
     			
     			$total_miles=abs($total_miles);
     			$total_deadhead=abs($total_deadhead);
     			
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
     			
     			if($row['driver_position']==2 && $row['driver1_employer_id']==$_POST['employer_id'])		$use_main_employer=$row['driver1_employer_id'];		//allow driver 2 to be part of this employer... even if driver 1 was not.
     			
     			
     			//find employer info and add row to table if needed, the driver and trucks(COA account).....
     			$emp_id=$use_main_employer;
     			$mrr_use_emp=0;						$mrr_found=0;
     			$mrr_use_emp2=0;						$mrr_found2=0;
     			for($i=0;$i < $empl_counter; $i++)
     			{	//see if already in the loop
     				if(	$employers[$i] == $emp_id )	
     				{
     					$mrr_use_emp=$i;				$mrr_found=1;
     				}
     				if(	$employers[$i] == $emp_id )	
     				{
     					$mrr_use_emp2=$i;				$mrr_found2=1;
     				}
     			}
     						
     			if(	$employers[0] == $emp_id )	
     			{
     				$mrr_use_emp=0;					$mrr_found=1;
     			}
     			
     			$current_truck = $row['truck_id'];
				$current_truck_name = $row['name_truck'];
     			
     			if($mrr_found==1)
     			{	//in the loop so add to the loop     								
     				$mrr_use_truck=0;					$mrr_truck_found=0;		
     				$employer_used[$mrr_use_emp]=1;
     				
     				//driver1...
     				for($t=0;$t < $employer_truck_cntr[ $mrr_use_emp ]; $t++)
     				{
     					if($current_truck == $employer_truck_ids[ $mrr_use_emp ][$t] )	
     					{
     						$mrr_use_truck=$t;			$mrr_truck_found=1;
     					}
     				}
     				if($mrr_truck_found==0)
     				{
     					$employer_truck_ids[ $mrr_use_emp ][ ( $employer_truck_cntr[ $mrr_use_emp ] )] =$current_truck ;
     					$mrr_use_truck = $employer_truck_cntr[ $mrr_use_emp ];
     					$employer_truck_cntr[ $mrr_use_emp ]++;						
     					
     					$employer_truck_names[ $mrr_use_emp ][ $mrr_use_truck ]=$current_truck_name;					
     					
     					//make sure this truck is ready for driver list
     					$employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck]=0;
               			$employer_truck_driver_ids[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_emps[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_names[ $mrr_use_emp ][$mrr_use_truck][0]="";
               			$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_hours_charge_team[ $mrr_use_emp ][$mrr_use_truck][0]=0;
     					$employer_truck_driver_miles_charge_team[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			
               			$employer_truck_driver_hours[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_miles[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_stops[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_expenses[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_expense_acct[ $mrr_use_emp ][$mrr_use_truck][0]="";
               			$employer_truck_driver_expense_prices[ $mrr_use_emp ][$mrr_use_truck][0]="";
     					$employer_truck_driver_expense_rate[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_hours_rate[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			$employer_truck_driver_stops_rate[ $mrr_use_emp ][$mrr_use_truck][0]=0;
               			
               			$employer_truck_driver_miles_team[ $mrr_use_emp ][$mrr_use_truck][0]=0;
     					$employer_truck_driver_miles_rate_team[ $mrr_use_emp ][$mrr_use_truck][0]=0;
     				}
     									
          			//get expenses that are not on truck/load/dispatch dates but are still in the right span.
          			$mycnt=0;
          			
          			$use_driver_only=0;
          			if($_POST['driver_id'] > 0)		$use_driver_only=$_POST['driver_id'];
          			
          			$use_emp_id=$emp_id;     			
          			if($_POST['employer_id'] > 0)		$use_emp_id=$_POST['employer_id'];
          			
          			$data_dexp=get_master_expense_query($use_emp_id,$use_driver_only);
          			$exps_cntr_mrr=mysqli_num_rows($data_dexp);
          			while($row_dexp = mysqli_fetch_array($data_dexp)) 
          			{
          				$et_dexp=$row_dexp['id'];
          				$et_id=$row_dexp['driver_id'];
          				$et_dfname=$row_dexp['name_driver_first'];
          				$et_dlname=$row_dexp['name_driver_last'];
          				$et_dater=date("m/d/Y",strtotime($row_dexp['linedate']));
          				$et_descript=$row_dexp['desc_long'];
          				$et_name=$row_dexp['exp_type_name'];
          				$et_amnt=$row_dexp['amount_billable'];	//
          				$et_billable=$row_dexp['amount_billable'];
          				$et_chart=$row_dexp['chart_id'];
          				$account="";
          				$mycnt++;
          							
          				$mrr_found_exp=0;
          				$mrr_use_index=-1;
          				for($xcx=0;$xcx < $mrr_enum;$xcx++)
          				{
          					if($mrr_exps[ $xcx ]==$et_dexp)
          					{
          						$mrr_found_exp=1;
          						$mrr_use_index=$xcx;
          					}	
          				}
          				
          				if($mrr_found_exp==0)
          				{
          					$mrr_exps[ $mrr_enum ]=$et_dexp;
          					$mrr_enum++;	
          					
               				$results=mrr_get_coa_list($et_chart,'');		//67000	//first arg is $chart_id, second arg is $chart_number	
                    		
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
               				
               				if(!isset($_POST['summary_only'])) 
               				{
               					$mrr_output2.="
               						<tr style='background-color:#d0ffdb'>
               							<td colspan='2'>".$account."</td>
               							<td>".$et_name."</td>
               							<td align='right'>$".money_format('',$et_amnt)."</td>
               							<td></td>
               							<td></td>
               							<td colspan='11'>(".$et_dfname." ".$et_dlname.") ".$et_dater." - ".$et_descript."</td>
               						</tr>
               					";
               					$total_expenses+=$et_amnt;
               					
               					//$total_expenses_mrr+=$et_amnt;
               					
               					$cntr_exp=$employer_extras[ $mrr_use_emp ];
               					$employer_extra_expenses[ $mrr_use_emp ][ $cntr_exp ]=$et_dexp;
          						$employer_extra_expenses_acct[ $mrr_use_emp ][ $cntr_exp ]=$et_chart;
          						$employer_extra_expenses_acct_name[ $mrr_use_emp ][ $cntr_exp ]="".$account."";
          						$employer_extra_expenses_desc[ $mrr_use_emp ][ $cntr_exp ]="".$et_dater." - ".$et_descript."";
          						$employer_extra_expenses_driver[ $mrr_use_emp ][ $cntr_exp ]=$et_id;
          						$employer_extra_expenses_driver_name[ $mrr_use_emp ][ $cntr_exp ]="".$et_dfname." ".$et_dlname."";
          						$employer_extra_expenses_amnt[ $mrr_use_emp ][ $cntr_exp ]=$et_amnt;
          						          					
               					$employer_extras[ $mrr_use_emp ]++;
                    			}
                    		}               		
          			}
     			
     			   	//add driver vacation and cash advances here...Added April 2014...................................
     				$data_vacations=get_master_vacation_advance_query($emp_id,$use_driver_only);  			
     				$mycnt2=0;
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
     					/*     					
     					if($row_vacations['cash_advance']!=0)
     					{	//cash advance
     						$dva_pay=$row_vacations['cash_advance'];
     						
     						$dva_chart_id=0;
     						$account="Cash Advance";
     						$exp_typer="&nbsp;";
     					}
     					else
     					{	//everything else is a vacation pay
     						$dva_pay=$row_vacations['miles_pay_rate'];
     						$dva_pay+=$row_vacations['hours_pay_rate'];
     					}
     					*/
     					
     					//$total_expenses+=$dva_pay;
               			//$driver_expenses+=$dva_pay;     				
          					
          				$et_dexp=$row_vacations['id'];
          				$et_id=$row_vacations['driver_id'];
          				$et_dfname=$row_vacations['name_driver_first'];
          				$et_dlname=$row_vacations['name_driver_last'];
          				$et_dater=date("m/d/Y",strtotime($row_vacations['linedate']));
          				$et_descript=$row_vacations['comments'];
          				$et_name=$exp_typer;
          				$et_amnt=$dva_pay;
          				$et_billable=$dva_pay;
          				$et_chart=$dva_chart_id;
          				
          				$mycnt2++;
          							
          				$mrr_found_exp=0;
          				$mrr_use_index=-1;
          				for($xcx=0;$xcx < $mrr_enum;$xcx++)
          				{
          					if($mrr_exps[ $xcx ]==$et_dexp)
          					{
          						$mrr_found_exp=1;
          						$mrr_use_index=$xcx;
          					}	
          				}
          				
          				if($mrr_found_exp==0)
          				{
          					$mrr_exps[ $mrr_enum ]=$et_dexp;
          					$mrr_enum++;	
          					
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
               					$mrr_output2.="
               						<tr style='background-color:#d0ffdb'>
               							<td colspan='2'>".$account."</td>
               							<td>".$et_name."</td>
               							<td align='right'>$".money_format('',$et_amnt)."</td>
               							<td></td>
               							<td></td>
               							<td colspan='11'>(".$et_dfname." ".$et_dlname.") ".$et_dater." - ".$et_descript."</td>
               						</tr>
               					";
               					$total_expenses+=$et_amnt;
               					//$total_expenses_mrr+=$et_amnt;
               					
               					$cntr_exp=$employer_extras[ $mrr_use_emp ];
               					$employer_extra_expenses[ $mrr_use_emp ][ $cntr_exp ]=$et_dexp;
          						$employer_extra_expenses_acct[ $mrr_use_emp ][ $cntr_exp ]=$et_chart;
          						$employer_extra_expenses_acct_name[ $mrr_use_emp ][ $cntr_exp ]="".$account."";
          						$employer_extra_expenses_desc[ $mrr_use_emp ][ $cntr_exp ]="".$et_dater." - ".$et_descript."";
          						$employer_extra_expenses_driver[ $mrr_use_emp ][ $cntr_exp ]=$et_id;
          						$employer_extra_expenses_driver_name[ $mrr_use_emp ][ $cntr_exp ]="".$et_dfname." ".$et_dlname."";
          						$employer_extra_expenses_amnt[ $mrr_use_emp ][ $cntr_exp ]=$et_amnt;
          						          					
               					$employer_extras[ $mrr_use_emp ]++;
                    			}
                    		}  
          			}
          			//................................................................................................		
          			
          			$single_rate=$mrr_per_mile_labor;
          			$single_hr_rate=$mrr_per_hour_labor;
          			
          			$bonus_cntr=0;          			$bonus_val=0;
          			$stop_cntr=0;          			$stop_val=0;
          			
          			$expense_display="";
          			
          			$mrr_divider=1;
          			if($row['driver2_id'] > 0)		$mrr_divider=2;
          			
          			
          			// get any dispatch expenses (tied to dispatches & drivers)
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
          			";		//and trucks_log.truck_id = '".sql_friendly($current_truck)."' 
          			$data_expenses = simple_query($sql);
          			while($row_expense = mysqli_fetch_array($data_expenses)) 
          			{
          				if(trim($row_expense['expense_type'])!='Comcheck' && $use_this_dispatch_row==1)
          				{
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
               				
               				
               				$mrr_exp_amnt=$row_expense['expense_amount']/$mrr_divider;
               				
               				
               				if(trim($row_expense['expense_type'])=='Panther bonus')
          					{
          						$bonus_cntr++;		$bonus_val+=$mrr_exp_amnt;
          						
          					}
          					else
          					{
          						$stop_cntr++;		$stop_val+= $mrr_exp_amnt;
          					} 
          					
               				$total_expenses_mrr+=$mrr_exp_amnt;
               				$driver_expenses+=$mrr_exp_amnt;
               				
               				if(!isset($_POST['summary_only'])) 
               				{
          						$expense_display.="
          							<tr style='background-color:#d0ffdb'>
          								<td colspan='2'>".$account."</td>
          								<td>$row_expense[expense_type]</td>
          								<td align='right'>$".money_format('',$mrr_exp_amnt)."</td>
          								<td></td>
          								<td></td>
          								<td colspan='11'>$row_expense[expense_desc]</td>
          							</tr>
          						";
          					}
          				}
          			
          			}
          			
          			
          			
          			
          			
          			//drivers for each truck
     				$mrr_use_driver=0;					$mrr_driver_found=0;	
     				for($d=0;$d < $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck]; $d++)
     				{
     					if($row['driver_id'] == $employer_truck_driver_ids[ $mrr_use_emp ][$mrr_use_truck][ $d ] )
     					{
     						$mrr_use_driver=$d;			$mrr_driver_found=1;
     						
     						//$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=(float)$single_hr_rate;
     						//$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=(float)$single_rate;             			
     					}
     				}
          			if($truck_oo_ic > 0 && $driver_oo_ic > 0)
          			{
          				//not needed for payroll...SKIP THIS ONE...	
          			}
          			elseif($mrr_driver_found==0)
     				{
     					$employer_truck_driver_ids[ $mrr_use_emp ][$mrr_use_truck][ ( $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck] ) ]  =$row['driver_id'];
     					$employer_truck_driver_emps[ $mrr_use_emp ][$mrr_use_truck][ ( $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck] ) ]  =$use_emp_id;
     					$mrr_use_driver=$employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck];
     					$employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck]++;
     					
     					//reset counters for this new driver...
     					$namerx=trim($row['name_driver_first']." ". $row['name_driver_last']);
     					//$namerx=str_replace("-O.O./I.C.","",$namerx);
     					
     					
     					$employer_truck_driver_names[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$namerx;
               			$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$single_hr_rate;
               			$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$single_rate;	
               			               			
               			$employer_truck_driver_hours_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;	//$tm_hr_rate;
						$employer_truck_driver_miles_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;	//$tm_rate;
						
               			$employer_truck_driver_hours[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_miles[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_stops[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_bonus[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_expenses[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_expense_acct[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]="";
						$employer_truck_driver_expense_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_hours_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_stops_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_bonus_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			
               			$employer_truck_driver_overtime[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
     					$employer_truck_driver_overtime_amnt[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
     					
     					$employer_truck_driver_full_hours[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
     					$employer_truck_driver_full_hours_pay[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
     	
     					$employer_truck_driver_full_miles[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
     					$employer_truck_driver_full_miles_pay[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;             				         				
             				               		
               			$employer_truck_driver_expenses[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
						$employer_truck_driver_expense_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
						$employer_truck_driver_expense_acct[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]="";	
						$employer_truck_driver_expense_prices[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
												
						$employer_truck_driver_miles_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
						$employer_truck_driver_miles_rate_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
								
     				}
     				
     				if($truck_oo_ic > 0 && $driver_oo_ic > 0)
          			{
          				//not needed for payroll...SKIP THIS ONE...	
          			}
          			elseif($use_this_dispatch_row==1)
     				{
          				if($row['driver2_id'] > 0)
          				{	//team rate
     						$employer_truck_driver_miles_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=abs($row['miles_deadhead'] + $row['miles']);
     						$employer_truck_driver_miles_rate_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$team_val;	     						
          				}
          				else
          				{	//single rate
          					$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=(abs($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor);	
          					$employer_truck_driver_miles[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=abs($row['miles_deadhead'] + $row['miles']);         					
          				}          				          				
          				//$truck_oo_ic > 0 && 
          				if($driver_oo_ic > 0)
          				{
          					$employer_truck_driver_hours[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=1;
          				}
          				else
          				{
          					$employer_truck_driver_hours[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=abs($row['hours_worked']);	
          				}
          				$employer_truck_driver_hours_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$tmp_hourly_pay;											//$single_hr_rate;
          				                    		
                    		$employer_truck_driver_stops[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$stop_cntr;
                    		$employer_truck_driver_stops_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$stop_val; 
                    		
                    		$employer_truck_driver_bonus[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$bonus_cntr;
                    		$employer_truck_driver_bonus_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$bonus_val;  
                    		
                    		$employer_truck_driver_overtime[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$tmp_hours_over;
     					$employer_truck_driver_overtime_amnt[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$tmp_hours_paid_over;
     					
     					$employer_truck_driver_full_hours[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$cur_driver_tmp_hours;
     					$employer_truck_driver_full_hours_pay[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$cur_driver_tmp_hours_pay;
     	
     					$employer_truck_driver_full_miles[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$cur_driver_tmp_miles;
     					$employer_truck_driver_full_miles_pay[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$cur_driver_tmp_miles_pay;     					
					}
					   			
     			}
     			
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
     				echo "
     					<tr class='".($this_driver_count % 2 == 1 ? 'odd' : 'even')."'>
     						<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
     						<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
     						<td nowrap>$row[origin]</td>
     						<td nowrap>$row[destination]</td>
     						<td align='right'>".number_format($row['miles'])."</td>
     						<td align='right'>".number_format($row['miles_deadhead'])."</td>
     						<td align='right'>".number_format($row['miles_deadhead'] + $row['miles'])."</td>
     						<td align='right'".$overtime_highlight.">".(number_format($row['hours_worked']) == $row['hours_worked'] ? number_format($row['hours_worked']) : $row['hours_worked'])."</td>
     						
     						<td align='right'".$overtime_highlight.">".$normal_labor_miles."</td>
     						<td align='right'".$overtime_highlight.">".$normal_pay_miles."</td>
     						<td align='right'".$overtime_highlight.">".$normal_labor_hours."</td>
     						<td align='right'".$overtime_highlight.">$".number_format($show_pay_reg,2)."</td>
     						<td align='right'".$overtime_highlight.">$".number_format($show_pay_ooic,2)."</td>
     						
     						<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
     						<td nowrap>$row[name_truck]</td>
     						<td nowrap>$row[trailer_name]</td>
     						<td nowrap>$row[name_company]</td>
     						<td nowrap>".mrr_fetch_set_employer($use_main_employer)."</td>
     					</tr>
     				";														//($row['hours_worked'] *$mrr_per_hour_labor )
     				if($row['driver2_id'] > 0) {
     					echo "
     						<tr class='".($this_driver_count % 2 == 1 ? 'odd' : 'even')."' style='background-color:#ebc8c8'>
     							<td colspan='2'>&nbsp;</td>
     							<td>Team Run</td>
     							<td colspan='16'>$row[name2_driver_first] $row[name2_driver_last]</td>
     						</tr>
     					";					// (".$row['driver1_employer_id']." | ".$row['driver2_employer_id'].")
     				}
     				
     				echo $expense_display;
     			}
     			/*
     			while($row_expense = mysqli_fetch_array($data_expenses)) {
     				if(trim($row_expense['expense_type'])!='Comcheck')
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
               			     				
          				if(!isset($_POST['summary_only'])) {
          					echo "
          						<tr style='background-color:#d0ffdb'>
          							<td colspan='2'>mrr1 ".$account."</td>
          							<td>$row_expense[expense_type]</td>
          							<td align='right'>$".money_format('',$exp_amntr)."</td>
          							<td></td>
          							<td></td>
          							<td colspan='11'>$row_expense[expense_desc]</td>
          						</tr>
          					";
          				}
     				}
     			}
     			*/
			}
		}
		if($mrr_all_drivers > 0)			show_driver_totals($current_truck_id,$current_driver_id);
		
		//---------------------------------------------------------------------NOW SHOW EXPENSES FOR OTHER DRIVERS NOT ON THE LOAD LIST------------------------------------------------------------------------------
		
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
			
				if($current_driver != '') {
					//show_driver_totals($driver_miles, $driver_miles_deadhead, $driver_hours_worked, $driver_per_mile, $driver_per_hour);
					if($mrr_cntr > 0 && $driver_expenses > 0)	
					{
						show_driver_totals($current_truck_id,$current_driver_id);
						echo "<tr><td colspan='24'><hr></td></tr>";
					}
				}
				$mrr_cntr++;
				$current_driver = $row['id'];
				$current_driver_name = $row['name_driver_last'].", ".$row['name_driver_first'];
				$this_driver_count = 0;
				$driver_hours_worked = 0;
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
					where drivers_expenses.driver_id = '$row[id]'
						and drivers_expenses.deleted = 0
						and drivers_expenses.payroll = 1
						and drivers_expenses.linedate between '".date("Y-m-d", strtotime($use_starting_point))."' and '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
					order by linedate
				";
				//echo $sql."<br>";
				
				$data_driver_expenses = simple_query($sql);
								
				$mrr_temp.= "
					<tr>
						<td colspan='8'><br>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='3'>Driver: <a href='admin_drivers.php?id=$row[id]' target='manage_driver_$row[id]'>$current_driver_name</a> (".$label_prefix.")</td>
						<td align='right' colspan='6' nowrap><span style='font-weight:bold; color:#AA0000;'>Expenses Only</span></td>
						<td align='right' nowrap colspan='3'></td>
					</tr>
				";
				
				if(!isset($_POST['summary_only'])) {
					$mrr_temp.= $header_columns;
				}
				
				while($row_driver_expenses = mysqli_fetch_array($data_driver_expenses)) {
					if(trim($row_driver_expenses['expense_type'])!='Comcheck')
					{
     					$driver_expenses += $row_driver_expenses['amount_billable'];
     					$total_expenses += $row_driver_expenses['amount_billable'];
     					
     					$total_expenses_mrr+=$row_driver_expenses['amount_billable'];
     					     					
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
     							<tr style='background-color:#d0ffdb'>
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
						
			while($row_expense = mysqli_fetch_array($data_expenses)) 
			{
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
     						<tr style='background-color:#d0ffdb'>
     							<td colspan='2'>".$account."</td>
     							<td>$row_expense[expense_type]</td>
     							<td align='right'>$".money_format('',$row_expense['expense_amount'])."</td>
     							<td></td>
     							<td colspan='15'>$row_expense[expense_desc]</td>
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
          			
          			$total_expenses_mrr+=$dva_pay;
          			          			
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
     				{			//".$mrr_driver_cntr." 
     					$mrr_temp.="
     						<tr style='background-color:#d0ffdb'>
     							<td colspan='2'> ".$account."</td>
     							<td>".$exp_typer."</td>
     							<td align='right'>$".money_format('',$dva_pay)."</td>
     							<td></td>
     							<td colspan='15'>".date("m-d-Y", strtotime($row_vacations['linedate']))." - ".$row_vacations['comments']."</td>
     						</tr>
     					";
     				}
     			}
     			//................................................................................................			
			}
               if($driver_expenses != 0)
               {
               	echo $mrr_temp;               	
               }
               $mrr_temp="";
		}
		if($total_expenses != 0)
          {
             	//show_driver_totals();
          }						
	?>
	<tr>
		<td colspan='19'>
			<hr>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td></td>
		<td colspan='3'>
			<!---
			<?=$counter?> dispatch(es)
			--->
		</td>
		
		<td align='right'><?=number_format($total_miles)?></td>
		<td align='right'><?=number_format($total_deadhead)?></td>
		<td align='right'><?=number_format($total_all_miles)?></td>
		
		<td align='right'><?=$total_hours?></td>
		<td colspan='4'></td>
	</tr>
	<!---
	<tr style='font-weight:bold'>
		<td align='right' colspan='7'>$<?=money_format('',$total_mile_charge)?></td>
		<td align='right' colspan='1'>$<?=money_format('',$total_hours_charge)?></td>
	</tr>
	--->	
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
	$current_chart_name="";
	$current_acct_name="";
	
	$this_driver_count = 0;
	$driver_hours_worked = 0;
	$driver_runs_worked=0;
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
	$mrr_temp="";
	
	$mrr_emp_used=0;
	$mrr_emp_found=0;
     
     if($_POST['payroll_mode']!=1)
     {
     	for($i=0; $i < $exps; $i++)
     	{
     		/*
     		$exp_driver[ $i ]
     		$exp_name[ $i ]
     		$exp_chart[ $i ]
     		$exp_acct[ $i ]
     		$exp_employ[ $i ]
     		$exp_notes[ $i ]
     		$exp_hrs[ $i ]
     		$exp_pay[ $i ]
     		$exp_runs[ $i ]
     		$exp_shuttle[ $i ]
     		$exp_amnt[ $i ]
     		*/	
     		
     		for($z=0;$z < $empl_counter; $z++)
          	{	//see if already in the loop
          		if(	$employers[$z] == $exp_employ[ $i ] )	
          		{
          			$mrr_emp_used=$z;				$mrr_emp_found=1;
          		}
          	}
     		
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
     						echo $mrr_temp;
     						show_driver_totals();
     						
     						$results=mrr_get_coa_list(0,$current_chart_name);		//67000	//first arg is $chart_id, second arg is $chart_number	
                         		$et_chart_id=0;
                              	foreach($results as $key2 => $value2 )
                              	{
                              		if($key2=="ChartEntry")
                              		{
                                   		foreach($value2 as $key => $value )
                              			{         		
                                        		$prt=trim($key);		$tmp=trim($value);
                                        		if($prt=="ID")			$et_chart_id=$tmp;
                                        		//if($prt=="Name")		$account=$tmp;
                                        		//if($prt=="Number")	$chart_acct=$tmp;                              		
                                   		}//end for loop for each chart entry
                              		}//end if
                              	}//end for loop for each result returned
     						
     						if($driver_pay > 0)
     						{
     							//No Shuttle Runs used for Payroll side...only for Caelex Invoice.
     							/*
          						$cntr_exp=$employer_extras[ $mrr_emp_used ];
               					$employer_extra_expenses[ $mrr_emp_used ][ $cntr_exp ]=($i * -1);
          						$employer_extra_expenses_acct[ $mrr_emp_used ][ $cntr_exp ]=$et_chart_id;
          						$employer_extra_expenses_acct_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_acct_name."";
          						$employer_extra_expenses_desc[ $mrr_emp_used ][ $cntr_exp ]="".$driver_runs_worked." Shuttle Run".($driver_runs_worked > 1 ? "s" : "")."";
          						$employer_extra_expenses_driver[ $mrr_emp_used ][ $cntr_exp ]=$current_driver;
          						$employer_extra_expenses_driver_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_driver_name."";
          						$employer_extra_expenses_amnt[ $mrr_emp_used ][ $cntr_exp ]=$driver_pay;
          						          					
               					$employer_extras[ $mrr_emp_used ]++;
               					*/
          					}	
          					if($driver_pay_hours > 0)
     						{          						
          						$cntr_exp=$employer_extras[ $mrr_emp_used ];
               					$employer_extra_expenses[ $mrr_emp_used ][ $cntr_exp ]=($i * -1);
          						$employer_extra_expenses_acct[ $mrr_emp_used ][ $cntr_exp ]=$et_chart_id;
          						$employer_extra_expenses_driver[ $mrr_emp_used ][ $cntr_exp ]=$current_driver;
          						if(substr_count($current_acct_name,"-O.O./I.C.") > 0)
          						{
          							$current_acct_name=str_replace("-O.O./I.C.","",$current_acct_name);
          							
          							$employer_extra_expenses_acct_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_acct_name."";
          							$employer_extra_expenses_driver_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_driver_name."";
          							$employer_extra_expenses_desc[ $mrr_emp_used ][ $cntr_exp ]="".$driver_pay_hours."";
          						}
          						else
          						{
          							$employer_extra_expenses_acct_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_acct_name."";
          							$employer_extra_expenses_driver_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_driver_name."";
          							$employer_extra_expenses_desc[ $mrr_emp_used ][ $cntr_exp ]="".$driver_hours_worked." Hour".($driver_hours_worked > 1 ? "s" : "")."";
          						}
          						$employer_extra_expenses_amnt[ $mrr_emp_used ][ $cntr_exp ]=$driver_pay_hours;            						
          						          					
               					$employer_extras[ $mrr_emp_used ]++;
          					}					
     												
     						echo "<tr><td colspan='24'><hr></td></tr>";	
     					}
     					$mrr_temp="";	
     					
     					$current_driver = $exp_driver[ $i ];
          				$current_driver_name = trim($exp_name[ $i ]);  
          				
          				$current_chart_name=trim($exp_chart[ $i ]);
     					$current_acct_name=trim($exp_acct[ $i ]);
     					
     					$this_driver_count = 0;
               			$driver_hours_worked = 0;
               			$driver_runs_worked = 0;
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
     					
     					
     					$mrr_temp= "
          					<tr>
          						<td colspan='8'><br>&nbsp;</td>
          					</tr>
          					<tr>
          						<td colspan='3'>Driver: <a href='admin_drivers.php?id=".$exp_driver[ $i ]."' target='manage_driver_".$exp_driver[ $i ]."'>".$exp_name[ $i ]."</a></td>
          						<td align='right' colspan='6' nowrap><span style='font-weight:bold; color:#AA0000;'>Company Time Sheet and Shuttle Routes</span></td>
          						<td align='right' nowrap colspan='3'></td>
          					</tr>
     					";
     					if(!isset($_POST['summary_only'])) {
     						$mrr_temp.= $header_columns;
     					}
     				}
     				
     				
     				
     				if($exp_runs[ $i ] > 0)			
          			{	//shuttle run...flat pay...
          				//$driver_pay += $exp_amnt[ $i ];
          				//$driver_runs_worked+=$exp_runs[ $i ];    				
          				
          				
          				//Update:  Carlex Nashille location adds the hours worked EVEN if it is a shuttle run as of about April 
          				if($exp_hrs[ $i ] > 0)
          				{
          					$driver_pay_hours += $exp_pay[ $i ];	
          					$driver_hours_worked += $exp_hrs[ $i ];	
          					
          					$mrr_temp.= "
          					<tr style='background-color:#d0ffdb'>
          						<td colspan='2'>".$exp_acct[ $i ]."</td>
          						<td>Shuttle Run Hrs</td>
          						<td align='right'>$".money_format('',$exp_pay[ $i ])."</td>
          						<td></td>
          						<td colspan='15'>".$exp_hrs[ $i ]."hrs worked.</td>
          					</tr>
          					";
          				}
          			}
          			else
          			{	//normal timesheet, not shuttle run... use hourly pay.
          				$driver_pay_hours += $exp_amnt[ $i ];	
          				$driver_hours_worked += $exp_hrs[ $i ];          				
          				          								
          				$mrr_temp.= "
          					<tr style='background-color:#d0ffdb'>
          						<td colspan='2'>".$exp_acct[ $i ]."</td>
          						<td>".($exp_runs[ $i ] > 0 ? "Shuttle Run" : "Time Sheet")."</td>
          						<td align='right'>$".money_format('',$exp_amnt[ $i ])."</td>
          						<td></td>
          						<td colspan='15'>".$exp_notes[ $i ]."</td>
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
     						echo $mrr_temp;
     						show_driver_totals();
     												
     						$results=mrr_get_coa_list(0,$current_chart_name);		//67000	//first arg is $chart_id, second arg is $chart_number	
                         		$et_chart_id=0;
                              	foreach($results as $key2 => $value2 )
                              	{
                              		if($key2=="ChartEntry")
                              		{
                                   		foreach($value2 as $key => $value )
                              			{         		
                                        		$prt=trim($key);		$tmp=trim($value);
                                        		if($prt=="ID")			$et_chart_id=$tmp;
                                        		//if($prt=="Name")		$account=$tmp;
                                        		//if($prt=="Number")	$chart_acct=$tmp;                              		
                                   		}//end for loop for each chart entry
                              		}//end if
                              	}//end for loop for each result returned
     						
     						if($driver_pay > 0)
     						{
          						//No shuttle runs used for Payroll...only for Carlex Invoice.
          						/*
          						$cntr_exp=$employer_extras[ $mrr_emp_used ];
               					$employer_extra_expenses[ $mrr_emp_used ][ $cntr_exp ]=($i * -1);
          						$employer_extra_expenses_acct[ $mrr_emp_used ][ $cntr_exp ]=$et_chart_id;
          						$employer_extra_expenses_acct_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_acct_name."";
          						$employer_extra_expenses_desc[ $mrr_emp_used ][ $cntr_exp ]="".$driver_runs_worked." Shuttle Run".($driver_runs_worked > 1 ? "s" : "")."";
          						$employer_extra_expenses_driver[ $mrr_emp_used ][ $cntr_exp ]=$current_driver;
          						$employer_extra_expenses_driver_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_driver_name."";
          						$employer_extra_expenses_amnt[ $mrr_emp_used ][ $cntr_exp ]=$driver_pay;
          						          					
               					$employer_extras[ $mrr_emp_used ]++;
               					*/
          					}	
          					if($driver_pay_hours > 0)
     						{
               					$cntr_exp=$employer_extras[ $mrr_emp_used ];
               					$employer_extra_expenses[ $mrr_emp_used ][ $cntr_exp ]=($i * -1);
          						$employer_extra_expenses_acct[ $mrr_emp_used ][ $cntr_exp ]=$et_chart_id;
          						$employer_extra_expenses_driver[ $mrr_emp_used ][ $cntr_exp ]=$current_driver;
          						if(substr_count($current_acct_name,"-O.O./I.C.") > 0)
          						{
          							$current_acct_name=str_replace("-O.O./I.C.","",$current_acct_name);
          							
          							$employer_extra_expenses_acct_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_acct_name."";
          							$employer_extra_expenses_driver_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_driver_name."";
          							$employer_extra_expenses_desc[ $mrr_emp_used ][ $cntr_exp ]="".$driver_pay_hours."";
          						}
          						else
          						{
          							$employer_extra_expenses_acct_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_acct_name."";
          							$employer_extra_expenses_driver_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_driver_name."";
          							$employer_extra_expenses_desc[ $mrr_emp_used ][ $cntr_exp ]="".$driver_hours_worked." Hour".($driver_hours_worked > 1 ? "s" : "")."";
          						}
          						$employer_extra_expenses_amnt[ $mrr_emp_used ][ $cntr_exp ]=$driver_pay_hours;            						
          						          					
               					$employer_extras[ $mrr_emp_used ]++;
          					}				
     												
     						echo "<tr><td colspan='24'><hr></td></tr>";	
     					}
     					$mrr_temp="";	
     					
     					$current_driver = $exp_driver[ $i ];
          				$current_driver_name = trim($exp_name[ $i ]);  
     					
     					$current_chart_name=trim($exp_chart[ $i ]);
     					$current_acct_name=trim($exp_acct[ $i ]);
     					
     					$this_driver_count = 0;
               			$driver_hours_worked = 0;
               			$driver_runs_worked = 0;
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
     					<tr>
     						<td colspan='8'><br>&nbsp;</td>
     					</tr>
     					<tr>
     						<td colspan='3'>Driver: <a href='admin_drivers.php?id=".$exp_driver[ $i ]."' target='manage_driver_".$exp_driver[ $i ]."'>".$exp_name[ $i ]."</a></td>
     						<td align='right' colspan='6' nowrap><span style='font-weight:bold; color:#AA0000;'>Company Time Sheet and Shuttle Routes</span></td>
     						<td align='right' nowrap colspan='3'></td>
     					</tr>
     				";
     				if(!isset($_POST['summary_only'])) {
     					$mrr_temp.= $header_columns;
     				}
     			}
     				
     			
     			if($exp_runs[ $i ] > 0)			
     			{	//shuttle run...flat pay...
     				//$driver_pay += $exp_amnt[ $i ];
     				//$driver_runs_worked+=$exp_runs[ $i ];
     			}
     			else
     			{	//normal timesheet, not shuttle run... use hourly pay.
     				$driver_pay_hours += $exp_amnt[ $i ];	
     				$driver_hours_worked += $exp_hrs[ $i ];
     				
     				          			
          			$mrr_temp.= "
          				<tr style='background-color:#d0ffdb'>
          					<td colspan='2'>".$exp_acct[ $i ]."</td>
          					<td>".($exp_runs[ $i ] > 0 ? "Shuttle Run" : "Time Sheet")."</td>
          					<td align='right'>$".money_format('',$exp_amnt[ $i ])."</td>
          					<td></td>
          					<td colspan='15'>".$exp_notes[ $i ]."</td>
          				</tr>
          			";
     			}

     			
     			$current_driver=$exp_driver[ $i ];		
     			$current_driver_name = trim($exp_name[ $i ]);
     		}		
     	}	
     	if(trim($mrr_temp)!="")
     	{
     		echo $mrr_temp;
     		show_driver_totals();
     		/* */
     		$results=mrr_get_coa_list(0,$current_chart_name);		//67000	//first arg is $chart_id, second arg is $chart_number	
     		$et_chart_id=0;
          	foreach($results as $key2 => $value2 )
          	{
          		if($key2=="ChartEntry")
          		{
               		foreach($value2 as $key => $value )
          			{         		
                    		$prt=trim($key);		$tmp=trim($value);
                    		if($prt=="ID")			$et_chart_id=$tmp;
                    		//if($prt=="Name")		$account=$tmp;
                    		//if($prt=="Number")	$chart_acct=$tmp;                              		
               		}//end for loop for each chart entry
          		}//end if
          	}//end for loop for each result returned
     		
     		if($driver_pay > 0)
     		{
     			//No Shuttle Runs used for Payroll...only for Carlex Invoice
     			/*
     			$cntr_exp=$employer_extras[ $mrr_emp_used ];
     			$employer_extra_expenses[ $mrr_emp_used ][ $cntr_exp ]=(($exps + 1) * -1);
     			$employer_extra_expenses_acct[ $mrr_emp_used ][ $cntr_exp ]=$et_chart_id;
     			$employer_extra_expenses_acct_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_acct_name."";
     			$employer_extra_expenses_desc[ $mrr_emp_used ][ $cntr_exp ]="".$driver_runs_worked." Shuttle Run".($driver_runs_worked > 1 ? "s" : "")."";
     			$employer_extra_expenses_driver[ $mrr_emp_used ][ $cntr_exp ]=$current_driver;
     			$employer_extra_expenses_driver_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_driver_name."";
     			$employer_extra_expenses_amnt[ $mrr_emp_used ][ $cntr_exp ]=$driver_pay;
     			          					
     			$employer_extras[ $mrr_emp_used ]++;
     			*/
     		}	
     		if($driver_pay_hours > 0)
     		{
     			$cntr_exp=$employer_extras[ $mrr_emp_used ];
				$employer_extra_expenses[ $mrr_emp_used ][ $cntr_exp ]=(($exps + 1) * -1);
				$employer_extra_expenses_acct[ $mrr_emp_used ][ $cntr_exp ]=$et_chart_id;
				$employer_extra_expenses_driver[ $mrr_emp_used ][ $cntr_exp ]=$current_driver;
				if(substr_count($current_acct_name,"-O.O./I.C.") > 0)
				{
					$current_acct_name=str_replace("-O.O./I.C.","",$current_acct_name);
					
					$employer_extra_expenses_acct_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_acct_name."";
					$employer_extra_expenses_driver_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_driver_name."";
					$employer_extra_expenses_desc[ $mrr_emp_used ][ $cntr_exp ]="".$driver_pay_hours."";
				}
				else
				{
					$employer_extra_expenses_acct_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_acct_name."";
					$employer_extra_expenses_driver_name[ $mrr_emp_used ][ $cntr_exp ]="".$current_driver_name."";
					$employer_extra_expenses_desc[ $mrr_emp_used ][ $cntr_exp ]="".$driver_hours_worked." Hour".($driver_hours_worked > 1 ? "s" : "")."";
				}
				$employer_extra_expenses_amnt[ $mrr_emp_used ][ $cntr_exp ]=$driver_pay_hours;            						
				          					
				$employer_extras[ $mrr_emp_used ]++;
     		}					
     		
     		echo "<tr><td colspan='24'><hr></td></tr>";	
     	}
     	
	}
	else
	{
		$carlex_total=0;
		$carlex_total2=0;
	}
	//...........................................................................................................................................................................................	
	
	//<h2>Carlex Time Sheet and Shuttle Runs:</h2>	
	//echo $carlex_section;
	?>
	<tr>
		<td colspan='19'>
			<hr>
		</td>
	</tr>
	<tr>
		<td colspan='19'>
			<table style='font-weight:bold'>
			<tr>
				<td nowrap>Total Driver Pay <?=($_POST['employer_id']==170 ? "(Flat Rate)" : "(miles)")?></td>
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
			<!--
			<tr>
				<td nowrap>Total Driver Pay (shuttle runs)</td>
				<td align='right'>$<?=money_format('',$carlex_total2)?></td>
			</tr>
			-->
			<tr>
				<td nowrap><span class='alert'><b>Overtime Included</b></span></td>
				<td align='right'><span class='alert'><b>$<?=money_format('',$overtime_paid)?></b></span></td>
			</tr>
            <tr>
                <td nowrap><span class='alert'><b>BONUS Total</b></span></td>
                <td align='right'><span class='alert'><b>$<?=money_format('',$total_bonus_pay)?></b></span></td>
            </tr>
            <tr>
				<td colspan='5'><hr></td>
			</tr>
			<tr>
				<td>Driver Pay</td>
				<td align='right'>$<?=money_format('',($total_driver_pay))?></td>
			</tr> 
			<tr>
				<td>Expenses</td>
				<td style='width:100px' align='right'>$<?=money_format('',$total_expenses_mrr)?></td>
				<?
					//$total_expenses
				?>
			</tr> 
			<tr>
				<td colspan='5'><hr></td>
			</tr>
			<tr>
				<td>Total</td>
				<td style='width:100px' align='right'>$<?=money_format('',$total_driver_pay + $total_expenses_mrr)?></td>
			</tr> 
			</table>
		</td>
	</tr>
	</table>
	<?
	$pdf = ob_get_contents();
	ob_end_clean();
	
	ob_start();
	?>
	<br><br>
	<table class='admin_menu2 font_display_section' style='margin:0 10px; text-align:left'>
	<tr>
		<td colspan='11'>
			<center>
			<span class='section_heading'>Bill Preview</span>
			</center>
		</td>
	</tr>	
	<?		
	$mrr_split_load_driver_emps=0;
    
    $coa_bonus_pay="15352";       //chart of accounts for new Bonus Pay COA created today...MRR 9/14/2021
	
	$mrr_today=date("m/d/Y", time());
	$mrr_days30=date("m/d/Y", strtotime("+1 month", time()));
	$date_ranger=$use_starting_point." through ".$_POST['date_to'].""; 
	
	 //PeachTree Payroll settings..........................................
	 $pt_employer_id=0;
	 $pt_acct_code="1026";	    //acctGL=1026 	acctID=90170-OH 	acctType=24 	acctDesc=Wages-Drivers
     $pt_gl_chart="701";		//acctGL=701 	acctID=10200 		acctType=0 	acctDesc=Checking- Regions Bank
     $pt_method="2";		    //Options: 2,8
     $pt_starting_check=0;     
     $check_account=0;	
     if(trim($defaultsarray['accounting_database_name'])!="")
     {
          $sql2="
     		select xvalue
               from ".trim($defaultsarray['accounting_database_name']).".defaults
               where xname='default_starting_deposit_account'
               ";          
          $data2 = simple_query($sql2);
          if($row2 = mysqli_fetch_array($data2)) 
          {
     		$check_account=(int) trim($row2['xvalue']);
     		
     		$sql2="
          		select next_check_number
                    from ".trim($defaultsarray['accounting_database_name']).".chart 
                    where id='".sql_friendly($check_account)."'
                    ";          
               $data2 = simple_query($sql2);
               if($row2 = mysqli_fetch_array($data2)) 
               {
          		$pt_starting_check=$row2['next_check_number'];
          		$pt_starting_check++;
          	}
     	}
	}
	
	$pt_drivers=0;
	$pt_driver_id[0]=0;
	$pt_driver_name[0]="";
	$pt_driver_tot[0]=0;
	
	$driver_bonus_hours[0]=0;       //store the drivers total hours.  THe index is the driver's ID, not a random integer or count of dirvers.
	//....................................................................
			
	for($i=0;$i < $empl_counter; $i++)
	{	//print each employer section individually...which is how the bill will get made		
		
		if(isset($employer_used[$i]) && $employer_used[$i] == 1)
		{
			$my_emp_id=$employers[ $i ];
			$my_emp_name=$employer_names[ $i ];
			$mrr_mask_employer=0;
			
			$pt_employer_id=$my_emp_id;
			
			if($_POST['employer_id'] > 0 && $employers[ $i ]!=$_POST['employer_id'])	
			{				
				$sql2="
					select option_values.fvalue as use_disp
                         	from option_values,option_cat
                         	where option_values.deleted=0
                         		and option_cat.id=option_values.cat_id
                         		and option_cat.cat_name='employer_list'
                         		and option_values.id='".sql_friendly($_POST['employer_id'])."'
                         ";          
                    $data_emp = simple_query($sql2);
                    if($row_emp = mysqli_fetch_array($data_emp)) 
                    {
               		$my_emp_name=$row_emp['use_disp'];
				}				
			}	
			
			$employer_vendor_bridge=mrr_get_last_employer_vendor_bridge($my_emp_id,$use_starting_point,$_POST['date_to']);
						
     		$bill_header['vendor_id']=$my_emp_id;
     		$bill_header['vendor']=$my_emp_name;
     		$bill_header['bridge_vendor_id']=$employer_vendor_bridge;
     		
     		$bill_header['date']=$mrr_today;
     		$bill_header['memo']=$date_ranger;
     		$bill_header['date_due']=$mrr_days30;
     		$bill_header['bill_date']=$_POST['date_to'];
     		$bill_header['reference_number']="";
     		$bill_header['stops']=0;
     		$bill_header['hours']=0;
     		$bill_header['miles']=0;
     		$bill_header['stops_val']=0;
     		$bill_header['hours_val']=0;
     		$bill_header['miles_val']=0;
     		
     		$bill_header['items']=0;
     		
     		$bill_items[0]['account_name']="";
     		$bill_items[0]['units']="";
     		$bill_items[0]['rate']="";
     		$bill_items[0]['amount']="";
     		$bill_items[0]['memo']="";
     		
     		$acct_bills="<div style='border:#aaaaaa 1px solid; overflow:auto; max-height:300px;'>&nbsp; Bills already in the system:";     		
     		$acct_bills.="		<table class='tablesorter' width='100%'>
     						<thead>
							<tr>
								<th valign='top'>ID</th>
								<th valign='top'>Date</th>
								<th valign='top'>Ref#</th>
								<th valign='top'>Memo</th>
								<th valign='top'>Due</th>
								<th valign='top' align='right'>Amount &nbsp;</th>
							</tr>
							</thead>
							<tbody>
			";     		
     		$acct_bills_cnt=0;
     		$acct_bills_val=0;
     			//each bill
     			$abill_id=0;
				$abill_val=0;
				$abill_due="";
				$abill_date="";
				$abill_memo="";
				$abill_ref="";
				
     		$results=mrr_get_vendor_bill_list($my_emp_name,$use_starting_point,$_POST['date_to'],$employer_vendor_bridge);
     		foreach($results as $key => $value )
			{
				$prt=trim($key);		$tmp=trim($value);
				if($prt=="BillCnt")		$acct_bills_cnt=$tmp;
				if($prt=="BillTot")		$acct_bills_val="$".number_format($tmp,2);
				
				if($prt=="BillID")			$abill_id=$tmp;
				if($prt=="BillAmount")		$abill_val="$".number_format($tmp,2);
				if($prt=="BillDateDue")		$abill_due=$tmp;
				if($prt=="BillDate")		$abill_date=$tmp;
				if($prt=="BillMemo")		$abill_memo=$tmp;
				if($prt=="BillMemoDetails")	$abill_memo.=$tmp;
				if($prt=="BillRefer")		$abill_ref=$tmp;
									
				if($prt=="BillRefer")
				{
					$acct_bills.="
						<tr>
							<td valign='top'>".$abill_id."</td>
							<td valign='top'>".$abill_date."</td>
							<td valign='top'>".$abill_ref."</td>
							<td valign='top'>".$abill_memo."</td>
							<td valign='top'>".$abill_due."</td>
							<td valign='top' align='right'>".$abill_val." &nbsp;&nbsp;</td>
						</tr>	
					";	
				}
			}			
			
			$acct_bills.="
					</tbody>
				</table>
				<table border='0' width='100%'>
				<tr>
					<td valign='top' colspan='5'>".$acct_bills_cnt." Bill(s) Total</td>
					<td valign='top' align='right'>".$acct_bills_val." &nbsp;&nbsp;</td>
				</tr>
				</table>
				</div>
			";
			
			$vendor_bridge_name="";
			if($employer_vendor_bridge > 0)
			{
				$vendor_bridge_name="
					<tr>
     					<td valign='top' colspan='5'><span class='alert'>Notice: SICAP Vendor for this Bill is now ".mrr_get_sicap_vendor_name($employer_vendor_bridge).".</span><br></td>
     				</tr>
				";
			}
			$running_tot=0;	
			$running_tot1=0; 
			$running_tot2=0;     		
     		echo "
     			<tr>
     				<td valign='top' colspan='4'>Vendor: <b>".$my_emp_name."</b></td>
     				<td valign='top'>Memo: <b>".$date_ranger."</b><br></td>
     			</tr>
     			".$vendor_bridge_name."	
     			<tr>
     				<td valign='top'><b>Account (COA)</b></td>
     				<td valign='top' width='100' align='right'><b>Units</b></td>
     				<td valign='top' width='100' align='right'><b>Rate</b></td>
     				<td valign='top' width='100' align='right'><b>Inv. Amount</b></td>
     				<td valign='top'><b>Memo</b></td>
     			</tr>
     		";	
     		
     		$cur_driver_bonus_hrs=0;
            $tmp_driver_id=0;
             
     		$etot_miles=0;
     		$etot_hours=0;
     		$etot_stops=0;
     		$etot_bonus=0;
     		$etot_exps=0;
     		$etot_mile_value=0;	
     		$etot_hour_value=0;	
     		$etot_stop_value=0;
     		$etot_bonus_value=0;
     		$etot_exps_value=0;			
     		//get each truck for this employer
     		for($t=0;$t < $employer_truck_cntr[ $i ]; $t++)
     		{
     			//for each truck
     			$id=$employer_truck_ids[ $i ][ $t ];
     			$search_chart_name="Lease Drivers - #".$employer_truck_names[ $i ][ $t ]."";
     			$search_stop_name="Stop Off - #".$employer_truck_names[ $i ][ $t ]."";
     			$search_bonus_name="Lease Drivers Panther Bonus - #".$employer_truck_names[ $i ][ $t ]."";
     			$search_exps_name="";
     			
     			$special_note_adder="";     			
     			
     			//get each driver for this truck          				
     			for($d=0;$d < $employer_truck_driver_cntr[ $i ][$t]; $d++)
     			{
     				if(  ($mrr_mask_employer==0 && $my_emp_id==$employer_truck_driver_emps[ $i ][ $t ][ $d ]) || 
     					($mrr_mask_employer > 0 && $_POST['employer_id']==$employer_truck_driver_emps[ $i ][ $t ][ $d ]) )
     				{          				
          				//separate team miles
          				$employer_truck_driver_miles[ $i ][ $t ][ $d ] -= $employer_truck_driver_miles_team[ $i ][ $t ][ $d ];
          				$employer_truck_driver_miles[ $i ][ $t ][ $d ]=abs($employer_truck_driver_miles[ $i ][ $t ][ $d ]);
          				
          				//adjust rate and recalculate individual pay
          				$employer_truck_driver_miles_rate[ $i ][ $t ][ $d ] = $employer_truck_driver_miles[ $i ][ $t ][ $d ] * $employer_truck_driver_miles_charge[ $i ][ $t ][ $d ];
          				
          				
          				//PeachTree secion...to group all values under the same check............................................................................................
          				$v_found=0;
          				$v_use_index=0;
                    
                        $d_id=$employer_truck_driver_ids[ $i ][ $t ][ $d ];
                         
          				for($v=0; $v < $pt_drivers; $v++)
          				{
          					if( $employer_truck_driver_ids[ $i ][ $t ][ $d ] == $pt_driver_id[ $v ])	
          					{	//found, so already created.
          					    $v_found=1; 	
          					    $v_use_index=$v;	
          					}
          				}
          				if($v_found==0)
          				{	//not found in check set yet, so add to it.
          					$pt_driver_id[ $pt_drivers ]=$employer_truck_driver_ids[ $i ][ $t ][ $d ];
							$pt_driver_name[ $pt_drivers ]=trim($employer_truck_driver_names[ $i ][ $t ][ $d ]);
							$pt_driver_tot[ $pt_drivers ]=0;
														
                            $driver_bonus_hours[$d_id]=0;   //set this drivers total TO 0 hours for bonus so far... will add to it by line items...no matter the truck COA.
							
							$v_use_index=$pt_drivers;
							$v_found=1;
							                  
                             //$misc_id=0;
                             $amnt=0;
                             //$amnt_desc="";
                             $sqld="
			                    select *
			                    from driver_ooic_misc_exp
			                    where driver_id='".sql_friendly($pt_driver_id[ $pt_drivers ])."'
				                    and linedate_from = '".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00' and linedate_to = '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
			                    order by id desc
		                     ";
                             $datad=simple_query($sqld);
                             if($rowd=mysqli_fetch_array($datad))
                             {
                                  //$misc_id=$rowd['id'];
                                  $amnt=$rowd['misc_amount'];
                                  //$amnt_desc=trim($rowd['misc_desc']);
                             }
                             //echo "<br>".$pt_drivers." Driver [".$pt_driver_tot[ $pt_drivers ]."] Query is ".$sqld." for the amount of ".$amnt.".<br>";
                             //$pt_driver_tot[ $pt_drivers ]+=$amnt;
                             $employer_truck_driver_hours_rate[ $i ][ $t ][ $d ]+=$amnt;
                   
                             $pt_drivers++;
          				}
          				
          				if($v_use_index > 0 || ($v_found > 0 && $v_use_index==0) )
          				{
          					$pt_driver_tot[ $v_use_index ] += $employer_truck_driver_miles_rate[ $i ][ $t ][ $d ];
          					$pt_driver_tot[ $v_use_index ] += $employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ];
          					
          					$pt_driver_tot[ $v_use_index ] += $employer_truck_driver_hours_rate[ $i ][ $t ][ $d ];
          					$pt_driver_tot[ $v_use_index ] += $employer_truck_driver_stops_rate[ $i ][ $t ][ $d ];
          					$pt_driver_tot[ $v_use_index ] += $employer_truck_driver_bonus_rate[ $i ][ $t ][ $d ];
          				}
          				//........................................................................................................................................................
          				
          				
          				          				
          				$note1="".$employer_truck_driver_names[ $i ][ $t ][ $d ]." ".$employer_truck_driver_miles[ $i ][ $t ][ $d ]." miles @ ".$employer_truck_driver_miles_charge[ $i ][ $t ][ $d ]."";
                    		
                    		//$employer_truck_driver_full_hours_pay		... will be swapped if different...driver has both rates
                    		//$employer_truck_driver_full_miles_pay  	... will be swapped if different...driver has both rates
                    		   						               		
                    		if($employer_truck_driver_miles_rate[ $i ][ $t ][ $d ] != 0)
                    		{
                         		$special_note_adder="";
                         		if(round($employer_truck_driver_miles_rate[ $i ][ $t ][ $d ],2) < round($employer_truck_driver_full_miles_pay[ $i ][ $t ][ $d ],2))
                         		{
                         			$employer_truck_driver_miles_rate[ $i ][ $t ][ $d ] = $employer_truck_driver_full_miles_pay[ $i ][ $t ][ $d ];
                         			$special_note_adder=" ...Mixed Rate";
                         		}
                         		$running_tot+=$employer_truck_driver_miles_rate[ $i ][ $t ][ $d ];
                         		$running_tot1+=$employer_truck_driver_miles_rate[ $i ][ $t ][ $d ];
                         		echo "<tr>
               						<td valign='top'>".$search_chart_name."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_miles[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_miles_charge[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>$".number_format($employer_truck_driver_miles_rate[ $i ][ $t ][ $d ],2)." </td>
               						<td valign='top'>".$note1."".$special_note_adder."</td>
               					</tr>";						// [[".$employer_truck_driver_emps[ $i ][ $t ][ $d ]."]]		...Running Total=$".number_format($running_tot,2)."
          					$etot_miles+=$employer_truck_driver_miles[ $i ][ $t ][ $d ];
          					$etot_mile_value+=$employer_truck_driver_miles_rate[ $i ][ $t ][ $d ];
          					
          					$bill_items[ ($bill_header['items']) ]['account_name']=$search_chart_name;
          					$bill_items[ ($bill_header['items']) ]['units']="".$employer_truck_driver_miles[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['rate']="".$employer_truck_driver_miles_charge[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['amount']="".$employer_truck_driver_miles_rate[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['memo']=$note1;
          					
          					$bill_header['items']++;     					
          				}
          				
          				if($employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ] != 0)
                        {
                            $special_note_adder="";
                            if(round($employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ],2) < round($employer_truck_driver_full_miles_pay[ $i ][ $t ][ $d ],2))
                            {
                                $employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ] = $employer_truck_driver_full_miles_pay[ $i ][ $t ][ $d ];
                                $special_note_adder=" ...Mixed Rate";
                            }
                            
                            $tmp_charge="0.000";
                            if($employer_truck_driver_miles_team[ $i ][ $t ][ $d ]!=0)
                            {
                                $tmp_charge=$employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ] / abs($employer_truck_driver_miles_team[ $i ][ $t ][ $d ]);
                            }
                            
                            $note1a="".$employer_truck_driver_names[ $i ][ $t ][ $d ]." ".$employer_truck_driver_miles_team[ $i ][ $t ][ $d ]." team miles @ ".number_format($tmp_charge,3)."";
                            
                            $running_tot+=$employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ];
                            $running_tot1+=$employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ];
                            
                            echo "<tr>
                                <td valign='top'>".$search_chart_name."</td>
                                <td valign='top' align='right'>".number_format($employer_truck_driver_miles_team[ $i ][ $t ][ $d ],2)."</td>
                                <td valign='top' align='right'>".number_format($tmp_charge,2)."</td>
                                <td valign='top' align='right'>$".number_format($employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ],2)." </td>
                                <td valign='top'>".$note1a."".$special_note_adder."</td>
                            </tr>";						// [[".$employer_truck_driver_emps[ $i ][ $t ][ $d ]."]]		...Running Total=$".number_format($running_tot,2)."
          					$etot_miles+=$employer_truck_driver_miles_team[ $i ][ $t ][ $d ];
          					$etot_mile_value+=$employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ];
          					
          					$bill_items[ ($bill_header['items']) ]['account_name']=$search_chart_name;
          					$bill_items[ ($bill_header['items']) ]['units']="".$employer_truck_driver_miles_team[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['rate']="".$tmp_charge."";
          					$bill_items[ ($bill_header['items']) ]['amount']="".$employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['memo']=$note1a;
          					
          					$bill_header['items']++;     					
          				}
          				
          				$note2="".$employer_truck_driver_names[ $i ][ $t ][ $d ]." ".$employer_truck_driver_hours[ $i ][ $t ][ $d ]." hours @ ".$employer_truck_driver_hours_charge[ $i ][ $t ][ $d ]."";
                    		
                    		
          				if($employer_truck_driver_hours_rate[ $i ][ $t ][ $d ] != 0)
                    	{  
               				$special_note_adder="";
               				if(round($employer_truck_driver_hours_rate[ $i ][ $t ][ $d ],2) < round($employer_truck_driver_full_hours_pay[ $i ][ $t ][ $d ],2))
                         		{
                         			$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ] = $employer_truck_driver_full_hours_pay[ $i ][ $t ][ $d ];
                         			$special_note_adder=" ...Mixed Rate";
                         		}
               				$running_tot+=$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ];
               				$running_tot2+=$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ];
               				
               				if(substr_count($note2,"-O.O./I.C.") > 0)
               				{               					
               					$note2="".$employer_truck_driver_names[ $i ][ $t ][ $d ]." - Flat Rate Total";
               					$note2=str_replace("-O.O./I.C.","",$note2);
               					$special_note_adder="";
               					
               					$etot_hours+=1;
          						$etot_hour_value+=$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ];
          					
          						$bill_items[ ($bill_header['items']) ]['account_name']=$search_chart_name;
          						$bill_items[ ($bill_header['items']) ]['units']="1";
          						$bill_items[ ($bill_header['items']) ]['rate']="".$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ]."";
          						$bill_items[ ($bill_header['items']) ]['amount']="".$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ]."";
          						$bill_items[ ($bill_header['items']) ]['memo']=$note2;
          						
          						echo "
          							<tr>
               							<td valign='top'>".$search_chart_name."</td>
               							<td valign='top' align='right'>1.00</td>
               							<td valign='top' align='right'>".number_format($employer_truck_driver_hours_rate[ $i ][ $t ][ $d ],2)."</td>
               							<td valign='top' align='right'>$".number_format($employer_truck_driver_hours_rate[ $i ][ $t ][ $d ],2)." </td>
               							<td valign='top'>".$note2."".$special_note_adder."</td>
               						</tr>
               					";						// [[".$employer_truck_driver_emps[ $i ][ $t ][ $d ]."]]		...Running Total=$".number_format($running_tot,2)."
               				}
               				else
               				{
               					$etot_hours+=$employer_truck_driver_hours[ $i ][ $t ][ $d ];
          						$etot_hour_value+=$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ];
          					
          						$bill_items[ ($bill_header['items']) ]['account_name']=$search_chart_name;
          						$bill_items[ ($bill_header['items']) ]['units']="".$employer_truck_driver_hours[ $i ][ $t ][ $d ]."";
          						$bill_items[ ($bill_header['items']) ]['rate']="".$employer_truck_driver_hours_charge[ $i ][ $t ][ $d ]."";
          						$bill_items[ ($bill_header['items']) ]['amount']="".$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ]."";
          						$bill_items[ ($bill_header['items']) ]['memo']=$note2;
                  
                                $driver_bonus_hours[$d_id]+=$employer_truck_driver_hours[ $i ][ $t ][ $d ];
          						
          						echo "
          							<tr>
               							<td valign='top'>".$search_chart_name."</td>
               							<td valign='top' align='right'>".number_format($employer_truck_driver_hours[ $i ][ $t ][ $d ],2)."</td>
               							<td valign='top' align='right'>".number_format($employer_truck_driver_hours_charge[ $i ][ $t ][ $d ],2)."</td>
               							<td valign='top' align='right'>$".number_format($employer_truck_driver_hours_rate[ $i ][ $t ][ $d ],2)." </td>
               							<td valign='top'>".$note2."".$special_note_adder."</td>
               						</tr>
               					";						// [[".$employer_truck_driver_emps[ $i ][ $t ][ $d ]."]]		...Running Total=$".number_format($running_tot,2)."
                  
                                $skip_bonus_this_time=0;
                                $cur_driver_id=$employer_truck_driver_ids[ $i ][ $t ][ $d ];
                                $next_driver_id=0;
                                if($d < ($employer_truck_driver_cntr[ $i ][$t] - 1))
                                {
                                     $next_driver_id=$employer_truck_driver_ids[ $i ][ $t ][ ($d+1) ];
                                }
                  
                                if($tmp_driver_id!=$cur_driver_id)        $cur_driver_bonus_hrs=0;
                  
                                $tmp_hrs_worked=$employer_truck_driver_hours[ $i ][ $t ][ $d ];
                                //$cur_driver_bonus_hrs+=$tmp_hrs_worked;
                  
                                $cur_driver_bonus_hrs=$driver_bonus_hours[$cur_driver_id];
                  
                                if($next_driver_id > 0 && $next_driver_id==$cur_driver_id)
                                {
                                     $skip_bonus_this_time=1;   //same driver coming next, so skip bonus display now...add to existing hours.
                                }
                                 
                                if($use_hourly_bonus_calculator > 0 && $skip_bonus_this_time==0)
                                {
                                     //$coa_bonus_pay="15352";
                                     //Driver Overtime Bonus
     
                                     //$driver_bonus_hours[$d_id]+=$employer_truck_driver_hours[ $i ][ $t ][ $d ];
                                     $tmp_driver_id=$employer_truck_driver_ids[ $i ][ $t ][ $d ];
                                     $tmp_hrs_worked=$driver_bonus_hours[$tmp_driver_id];    //$employer_truck_driver_hours[ $i ][ $t ][ $d ];                                      
                                     $tmp_driver_name=trim($employer_truck_driver_names[ $i ][ $t ][ $d ]);
                                                                          
                                     $new_bonus_pay=mrr_calculate_bonus_pay_from_hours($cur_driver_bonus_hrs,$tmp_driver_id);
     
                                     $bonus_reg_hours=mrr_get_driver_bonus_pay_min_hrs();
                                     $extra_bonus_hours = $cur_driver_bonus_hrs - $bonus_reg_hours;
     
                                     if($new_bonus_pay > 0 && $extra_bonus_hours > 0)
                                     {
                                         $bill_header['items']++;
                                         
                                         $bonus_rate_was=$new_bonus_pay / $extra_bonus_hours;
     
                                         //subtract this amount so that if another line for the same driver comes up with more bonus hours,
                                         //the pay does not re-include the same hours already printed as well as these......................  
                                         $driver_bonus_hours[$cur_driver_id]-=$extra_bonus_hours;     
                                         //.................................................................................................
                                         
                                         echo "
                                            <tr style='background-color:#d0ffdb;'>
                                                <td valign='top'>Driver Overtime Bonus</td>
                                                <td valign='top' align='right'>".number_format($extra_bonus_hours,2)."</td>
                                                <td valign='top' align='right'>".number_format($bonus_rate_was,2)."</td>
                                                <td valign='top' align='right'>$".number_format($new_bonus_pay,2)." </td>
                                                <td valign='top'>".$tmp_driver_name." at ".$extra_bonus_hours." over ".$bonus_reg_hours." hours BONUS.</td>
                                            </tr>
                                        ";     
     
                                        $bill_items[ ($bill_header['items']) ]['account_name']="Driver Overtime Bonus";
                                        $bill_items[ ($bill_header['items']) ]['units']="".$extra_bonus_hours."";
                                        $bill_items[ ($bill_header['items']) ]['rate']="".$bonus_rate_was."";
                                        $bill_items[ ($bill_header['items']) ]['amount']="".$new_bonus_pay."";
                                        $bill_items[ ($bill_header['items']) ]['memo']="".$tmp_driver_name." at ".$extra_bonus_hours." over ".$bonus_reg_hours." hours BONUS.";
     
                                        $running_tot+=$new_bonus_pay;
                                        $running_tot1+=$new_bonus_pay;
                                        
                                        //$etot_hours+=$extra_bonus_hours;     //not used yet. Does not increase the drivers hours. Only adds a bonus pay to those hours.
                                        $etot_hour_value+=$new_bonus_pay;
     
                                        $cur_driver_bonus_hrs=0;        //clear it out for the next driver.
                                     }
                                }
               				}  
               				 
          					$bill_header['items']++;
          				}
          				
          				$mrr_avg=0;
          				if($employer_truck_driver_stops[ $i ][ $t ][ $d ] > 0)
          				{
          					$mrr_avg=$employer_truck_driver_stops_rate[ $i ][ $t ][ $d ] / $employer_truck_driver_stops[ $i ][ $t ][ $d ];
          				}
          				$mrr_avg3a=0;
          				if($employer_truck_driver_bonus[ $i ][ $t ][ $d ] > 0)
          				{
          					$mrr_avg3a=$employer_truck_driver_bonus_rate[ $i ][ $t ][ $d ] / $employer_truck_driver_bonus[ $i ][ $t ][ $d ];
          				}
          				
          				$note3="".$employer_truck_driver_names[ $i ][ $t ][ $d ]." ".$employer_truck_driver_stops[ $i ][ $t ][ $d ]." stops @ ".number_format($mrr_avg,2)." (avg)";
          				$note3a="".$employer_truck_driver_names[ $i ][ $t ][ $d ]." ".$employer_truck_driver_bonus[ $i ][ $t ][ $d ]." Panther bonus @ ".number_format($mrr_avg3a,2)." (avg)";
          				
          				if($employer_truck_driver_stops_rate[ $i ][ $t ][ $d ] != 0)
                    		{
               				$running_tot+=$employer_truck_driver_stops_rate[ $i ][ $t ][ $d ];
               				$running_tot1+=$employer_truck_driver_stops_rate[ $i ][ $t ][ $d ];
               				echo "<tr>
               						<td valign='top'>".$search_stop_name."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_stops[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>".number_format($mrr_avg,2)."</td>
               						<td valign='top' align='right'>$".number_format($employer_truck_driver_stops_rate[ $i ][ $t ][ $d ],2)." </td>
               						<td valign='top'>".$note3."</td>
               					</tr>";						//...Running Total=$".number_format($running_tot,2)."
          					$etot_stops+=$employer_truck_driver_stops[ $i ][ $t ][ $d ];
          					$etot_stop_value+=$employer_truck_driver_stops_rate[ $i ][ $t ][ $d ];
          					
          					$bill_items[ ($bill_header['items']) ]['account_name']=$search_stop_name;
          					$bill_items[ ($bill_header['items']) ]['units']="".$employer_truck_driver_stops[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['rate']="".number_format($mrr_avg,2)."";
          					$bill_items[ ($bill_header['items']) ]['amount']="".$employer_truck_driver_stops_rate[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['memo']=$note3;
          					
          					$bill_header['items']++;
          				}
          				if($employer_truck_driver_bonus_rate[ $i ][ $t ][ $d ] != 0)
                    		{
               				$running_tot+=$employer_truck_driver_bonus_rate[ $i ][ $t ][ $d ];
               				$running_tot1+=$employer_truck_driver_bonus_rate[ $i ][ $t ][ $d ];
               				echo "<tr>
               						<td valign='top'>".$search_bonus_name."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_bonus[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>".number_format($mrr_avg3a,2)."</td>
               						<td valign='top' align='right'>$".number_format($employer_truck_driver_bonus_rate[ $i ][ $t ][ $d ],2)." </td>
               						<td valign='top'>".$note3a."</td>
               					</tr>";						//...Running Total=$".number_format($running_tot,2)."
          					$etot_bonus+=$employer_truck_driver_bonus[ $i ][ $t ][ $d ];
          					$etot_bonus_value+=$employer_truck_driver_bonus_rate[ $i ][ $t ][ $d ];
          					
          					$bill_items[ ($bill_header['items']) ]['account_name']=$search_bonus_name;
          					$bill_items[ ($bill_header['items']) ]['units']="".$employer_truck_driver_bonus[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['rate']="".number_format($mrr_avg3a,2)."";
          					$bill_items[ ($bill_header['items']) ]['amount']="".$employer_truck_driver_bonus_rate[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['memo']=$note3a;
          					
          					$bill_header['items']++;
          				}
          				if($employer_truck_driver_expense_rate[ $i ][ $t ][ $d ] != 0)
                    		{
          					$tmp_list=$employer_truck_driver_expense_acct[ $i ][ $t ][ $d ];	
     						$tmp_prices=$employer_truck_driver_expense_prices[ $i ][ $t ][ $d ];	
     						
     						$acct_name[0]="";
     						$charger[0]="";
     						
     						$tmp_list=str_replace("(","",$tmp_list);			$tmp_list=str_replace(")",";",$tmp_list);         				$tmp_list=trim($tmp_list);
     						$tmp_prices=str_replace("(","",$tmp_prices);			$tmp_prices=str_replace(")",";",$tmp_prices);    				$tmp_prices=trim($tmp_prices);
     						
     						$vars=explode(";",$tmp_list);		$icntr=0;
     						foreach($vars as $value )
     						{
     							$tmp=trim($value);
     							if(trim($tmp)!="")
     							{
     								$acct_name[$icntr]=$tmp;
     								$icntr++;
     							}
     						}
     						$vars=explode(";",$tmp_prices);		$jcntr=0;
     						foreach($vars as $value )
     						{
     							$tmp=trim($value);
     							if(trim($tmp)!="")
     							{
     								$charger[$jcntr]=$tmp;
     								$jcntr++;
     							}
     						}
     						if($icntr==$icntr)
     						{
     							for($xx=0; $xx < $icntr; $xx++)
     							{
               						$note4="".$employer_truck_driver_names[ $i ][ $t ][ $d ]." ".$charger[$xx]." expenses";
               						
               						$etot_exps++;
          							$etot_exps_value+=$charger[$xx];
               						
               						$running_tot+=$charger[$xx];
               						$running_tot1+=$charger[$xx];
               						
               						echo "<tr>
                         						<td valign='top'>".$acct_name[$xx]."</td>
                         						<td valign='top' align='right'>1</td>
                         						<td valign='top' align='right'>1</td>
                         						<td valign='top' align='right'>$".number_format($charger[$xx],2)." </td>
                         						<td valign='top'>".$note4."</td>
                         					</tr>";					//...Running Total=$".number_format($running_tot,2)."
                         				
                         				                         				
                         				if($v_use_index > 0 || ($v_found > 0 && $v_use_index==0) )		$pt_driver_tot[ $v_use_index ] += $charger[$xx];			//PeachTree check section...add the expense to the total                         				
                         				
                         				
          							$bill_items[ ($bill_header['items']) ]['account_name']=$acct_name[$xx];
          							$bill_items[ ($bill_header['items']) ]['units']="1";
          							$bill_items[ ($bill_header['items']) ]['rate']="1";
          							$bill_items[ ($bill_header['items']) ]['amount']="".$charger[$xx]."";
          							$bill_items[ ($bill_header['items']) ]['memo']=$note4;
          					
          							$bill_header['items']++;
     							}
     						}
     						
          				}
     				}
     				else
     				{
     					$mrr_split_load_driver_emps++;	
     				}
     			}//end driver while
     					
     		}	//end truck while 
     		
     		//<tr><td colspan='5'><b>$".number_format($running_tot,2)."</b> = $".number_format($running_tot1,2)." by miles and $".number_format($running_tot2,2)." hourly.</td></tr>
     		echo "     			
     			<tr>
     				<td colspan='5'><hr></td>
     			</tr>
     			<tr>
     				<td valign='top' colspan='5'><b>Other Expenses</b></td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Account (COA)</b></td>
     				<td valign='top' width='100' align='right'><b>Units</b></td>
     				<td valign='top' width='100' align='right'><b>Rate</b></td>
     				<td valign='top' width='100' align='right'><b>Inv. Amount</b></td>
     				<td valign='top'><b>Memo</b></td>
     			</tr>
     		";	
     		$running_tot=0;
     		$carlex_exp_amnt=0;
     		$carlex_run_amnt=0;
     		$add_exp_amnt=0;
     		for($et=0; $et< $employer_extras[ $i ]; $et++)
     		{     			
				//PeachTree secion...to group all values under the same check............................................................................................
				$v_found=0;
				$v_use_index=0;
				for($v=0; $v < $pt_drivers; $v++)
				{
					if( $employer_extra_expenses_driver[ $i ][ $et ] == $pt_driver_id[ $v ])	{	$v_found=1; 	$v_use_index=$v;	}	//found, so already created.
				}
				if($v_found==0)
				{	//not found in check set yet, so add to it.
					$pt_driver_id[ $pt_drivers ]=$employer_extra_expenses_driver[ $i ][ $et ];
					$pt_driver_name[ $pt_drivers ]=trim($employer_extra_expenses_driver_name[ $i ][ $et ]);
					$pt_driver_tot[ $pt_drivers ]=0;
					
					$v_use_index=$pt_drivers;
					
					$pt_drivers++;								
				}
				
				if($v_use_index > 0 || ($v_found > 0 && $v_use_index==0) )
				{
					$pt_driver_tot[ $v_use_index ] += $employer_extra_expenses_amnt[ $i ][ $et ];
				}
				//........................................................................................................................................................
				
				$running_tot+=$employer_extra_expenses_amnt[ $i ][ $et ];
				
				$note_et="".$employer_extra_expenses_driver_name[ $i ][ $et ]." - ".$employer_extra_expenses_desc[ $i ][ $et ]."";
				echo "<tr style='background-color:#d0ffdb'>
          				<td valign='top'>".$employer_extra_expenses_acct_name[ $i ][ $et ]."</td>
          				<td valign='top' align='right'>1</td>
          				<td valign='top' align='right'>".number_format($employer_extra_expenses_amnt[ $i ][ $et ],2)."</td>
          				<td valign='top' align='right'>$".number_format($employer_extra_expenses_amnt[ $i ][ $et ],2)." </td>
          				<td valign='top'>".$note_et."</td>
          			</tr>";			//...Running Total=$".number_format($running_tot,2)."
     			
     			
     			if($employer_extra_expenses[ $i ][ $et ] < 0)
     			{
     				if(substr_count($note_et,"Shuttle Run") > 0)	
     					$carlex_run_amnt+=$employer_extra_expenses_amnt[ $i ][ $et ];
     				else	
     					$carlex_exp_amnt+=$employer_extra_expenses_amnt[ $i ][ $et ];
     			}
     			
     			$etot_exps_value+=$employer_extra_expenses_amnt[ $i ][ $et ];
     			$etot_exps++;
     					
				$bill_items[ ($bill_header['items']) ]['account_name']=$employer_extra_expenses_acct_name[ $i ][ $et ];
				$bill_items[ ($bill_header['items']) ]['units']="1";
				$bill_items[ ($bill_header['items']) ]['rate']="".$employer_extra_expenses_amnt[ $i ][ $et ]."";
				$bill_items[ ($bill_header['items']) ]['amount']="".$employer_extra_expenses_amnt[ $i ][ $et ]."";
				$bill_items[ ($bill_header['items']) ]['memo']=$note_et;
				
				$bill_header['items']++;  	
     		}
     		
     		$eval_tot=($etot_mile_value + $etot_hour_value + $etot_stop_value + $etot_bonus_value + $etot_exps_value - $carlex_run_amnt);		// + $carlex_exp_amnt
     		 		
     		if($eval_tot!=0)
     		{     			
     			$show_tot_warning="";
     			$eval_my_tot=($total_driver_pay + $total_expenses_mrr);
     			
     			if(round($eval_my_tot,2) != round($eval_tot,2))
     			{
     				$show_tot_warning="<span class='alert' title='".$eval_tot." is not equal to ".$eval_my_tot." Above.'><b>Warning, Totals OFF!!!</b></span> ";
     			}   			
     			
     			// 2/15/2015 - CS - I wasn't sure why the "+ $etot_exps_value" was commented out. Adding it back in corrected the total in this section
     			// to match the total in the section above, so I put it back in.    			
     			$final_tot=$eval_tot;
     			
     			echo "
     			<tr>
     				<td colspan='5'><hr></td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Pay-per-Mile</b></td>
     				<td valign='top' align='right'>".$etot_miles."</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format(($etot_mile_value - $carlex_run_amnt + $carlex_exp_amnt - $carlex_exp_amnt),2)." </td>
     				<td valign='top'>&nbsp;</td>
     			</tr>
     			<tr>
     				<td valign='top'><b>".($_POST['employer_id']==170 ? "Flat Rate Total" : "Pay-per-Hour")."</b></td>
     				<td valign='top' align='right'>".$etot_hours."</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format(($etot_hour_value),2)." </td>
     				<td valign='top'>&nbsp;</td>
     			</tr> 
     			<tr>
     				<td valign='top'><b>Pay Added (Hourly)</b></td>
     				<td valign='top' align='right'>Time Sheet</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format($carlex_exp_amnt,2)." </td>
     				<td valign='top'>&nbsp;</td>
     			</tr> 
     			<!--
     			<tr>
     				<td valign='top'><b>Pay Added (Route)</b></td>
     				<td valign='top' align='right'>Shuttle Run</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format($carlex_run_amnt,2)." </td>
     				<td valign='top'>&nbsp;</td>
     			</tr>   
     			--> 			
     			<tr>
     				<td colspan='5'><hr></td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Driver Pay</b></td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format(($etot_mile_value + $etot_hour_value + $carlex_exp_amnt),2)." </td>
     				<td valign='top'></td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Expenses</b></td>
     				<td valign='top' align='right'>".($etot_stops+$etot_bonus+$etot_exps)."</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format(($etot_stop_value+$etot_bonus_value+$etot_exps_value - $carlex_exp_amnt - $carlex_run_amnt),2)."  </td>
     				<td valign='top'>&nbsp;</td>
     			</tr>
     			<tr>
     				<td colspan='5'><hr></td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Total</b></td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format($final_tot,2)."</td>
     				<td valign='top' align='right'>".$show_tot_warning."<input type='submit' name='mrr_employer_".$i."_go_btn' id='mrr_employer_".$i."_go_btn' value='Create this Bill'></td>
     			</tr>
     			<tr>
     				<td colspan='5'>
     					".$acct_bills."<br>
     					<center>
     					<br><br><br>
     					</center>
     				</td>
     			</tr>		
     			";	
     		}
			$bill_header['stops']=$etot_stops;
			$bill_header['bonus']=$etot_bonus;
			$bill_header['hours']=$etot_hours;
			$bill_header['miles']=$etot_miles;
			$bill_header['stops_val']=$etot_stop_value;
			$bill_header['bonus_val']=$etot_bonus_value;
			$bill_header['hours_val']=$etot_hour_value;
			$bill_header['miles_val']=$etot_mile_value;
     		
     		if(isset($_POST["mrr_employer_".$i."_go_btn"]))
     		{
     			$results=mrr_unpack_bill_details($bill_header,$bill_items);	
     			$tmp=0;
				foreach($results as $key => $value )
				{
					$prt=trim($key);		$tmp=trim($value);
				}
				$mrr_billing=$tmp;
     			
     			
     			if(strlen($mrr_billing) > 0)
     			{
     				$mrr_billing=str_replace("<rslt>1</rslt>","",$mrr_billing);
     				$mrr_billing=str_replace("<BillID>","",$mrr_billing);
     				$mrr_billing=str_replace("</BillID>","",$mrr_billing);
     				$mrr_billing=trim($mrr_billing);
     			}
     			
     			echo "
     			<tr>
     				<td colspan='5'>
     					<center>
     					Processing... Saved as bill #".$mrr_billing.".
     					</center>
     				</td>
     			</tr>
     			";
     				
     			
     			/*  Add payroll lock section here...  */
     			$sqlxx = "
					insert into last_payroll_report
						(id,
						linedate_added, 
						linedate, 
						employer_id,                   	
                      		user_id,   
                      		deleted)
					values
						(NULL,
						NOW(),
						'".date("Y-m-d",strtotime($_POST['date_to']))."',
						'".sql_friendly( $employers[ $i ] )."',
						'".sql_friendly( $_SESSION['user_id'] )."',
						0)
				";
				simple_query($sqlxx);   			
     		}
     		
     		echo "
     			<tr>
     				<td colspan='5'>
     					<center>
     					<br><br><hr><br>
     		";
     		
     		// && $_SERVER['REMOTE_ADDR'] == '70.90.229.29'
     		if($pt_drivers > 0 && $pt_starting_check > 0 && 1==2)
     		{
     			//Create Payroll array for PeachTree checks...
     			$payroll_array=array();     		
     			for($v=0; $v < $pt_drivers; $v++)
     			{
     				if($pt_driver_tot[ $v ] > 0)
     				{
          				$pt_res=mrr_get_driver_check_address_info($pt_driver_id[ $v ]); 
     					
     					$import_name=trim($pt_driver_name[ $v ]);	
                              $results=mrr_get_peachtree_import_name( trim($pt_res['first']), trim($pt_res['last']) );
                              foreach($results as $key => $value )
                              {
                              	$prt=trim($key);		$tmp=trim($value);
                              	if($prt=="ImportName")	$import_name=$tmp;
                              }
                              if(trim($import_name)=="")	$import_name=trim($pt_driver_name[ $v ]);
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
                              
     					//Example				array('Michael Richardson', '151 Heritage Park Dr', 'Suite 301', 'Murfreesboro','TN','37062','','10001',date("m/d/Y"),
     					// 						'Payroll for MRR',$pt_acct_code,'','1','Payroll Period for Blah Blah',$pt_gl_chart,'2000.00',$pt_method)				
          				$payroll_array[ $v ] = 	array($import_name, $pt_res['addr1'], $pt_res['addr2'], $pt_res['city'], $pt_res['state'], $pt_res['zip'],'', ($pt_starting_check + $v) ,date("m/d/Y"), 
          										$pt_driver_name[ $v ],$pt_acct_code,'','1',"Payroll Period for ".$date_ranger."",$pt_gl_chart, $pt_driver_tot[ $v ] ,$pt_method);
     					
     				}
     				elseif($pt_driver_tot[ $v ] == 0)
     				{
     					echo "<br><span class='mrr_alert'>Notice:</span> ".trim($pt_driver_name[ $v ])." was found in list, but has <span class='mrr_alert'>$0.00</span> for payment amount.  No Check will be written.";
     				}
     				else
     				{
     					echo "<br><span class='mrr_alert'>WARNING:</span> ".trim($pt_driver_name[ $v ])." was found with a negative check amount... <span class='mrr_alert'>$".number_format($pt_driver_tot[ $v ],2)."</span>.  No Check will be written.";
     				}
     			}
     			
     			
                    $res_file=mrr_csv_payroll_export_file($pt_employer_id,$_POST['date_to'],$payroll_array);
                    echo "<hr><br>
                    	<br>Export File is <a href='".$res_file['public_path']."' target='_blank'>".$res_file['public_path']."</a>. 
                    	<br>Full Path is ".$res_file['direct_path'].".
                    	<br>Lines Added=".$res_file['lines_added'].".
                    	<br><br><b>Checks to be Exported:</b><br><div width='1400' style='border:1px solid #0000CC; padding:5px;'>".$res_file['html']."</div><br>
                    ";
                    	
     		}
     		elseif($pt_drivers > 0 && $pt_starting_check==0)
     		{	// && $_SERVER['REMOTE_ADDR'] == '70.90.229.29'
     			echo "<span class='mrr_alert'>Notice:</span> Cannot locate last check number.";
     		}
     		elseif($pt_drivers == 0)
     		{	// && $_SERVER['REMOTE_ADDR'] == '70.90.229.29'
     			echo "<span class='mrr_alert'>Notice:</span> No Drivers found... so no checks to be written.";
     		}
     		echo "
     					</center>
     				</td>
     			</tr>
     		";
     		
		}	//END EMPLOYER_USED CHECK...
	} 	//end employer while
	
	?>
	
	</table><input type='hidden' name='employer_row_cntr' id='employer_row_cntr' value='<?= $empl_counter ?>'>
		
	<?
	$pdf2 = ob_get_contents();
	ob_end_clean();
	
	echo $pdf;
	echo $pdf2;
	
	$pdf.=$pdf2;
	
	if(trim($_POST['mrr_email_addr'])!="" && isset($_POST['mrr_email_report']))
	{
		$mrr_page_numbers=true;
		$display_mode=0;
		
		$fname = print_contents($export_filename, $pdf, $display_mode, '', '',$mrr_page_numbers);
		$fname=str_replace("./temp/","/temp/",$fname);
		
		//$fp = fopen(getcwd() . "/temp/$export_filename", "w");
		//fwrite($fp, $pdf); 
		//fclose($fp);
				
		$prefix="<a href=\"http://trucking.conardlogistics.com".$fname."\" target='_blank'>".$fname."</a>";	//http://trucking.conardlogistics.com/temp/
				
		
		$user_name=$defaultsarray['company_name'];
		$From=$defaultsarray['company_email_address'];
		$Subject="";
		if(isset($use_title))			$Subject=$use_title;
		elseif(isset($usetitle))			$Subject=$use_title;
		
		$pdf=str_replace(" href="," name=",$pdf);
		//$pdf=str_replace("</a>","",$pdf);
		
		$pdf2="<b>Payroll Billing Report:</b> <a href=\"http://trucking.conardlogistics.com".$fname."\" target='_blank'>Click for Payroll Billing Report</a><br>";
		
		//$export_filename
			
		$sentit=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject,$pdf2,$pdf2);
		
		$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
		echo "<br><br><b>This report has ".$sent_msg." to '".$_POST['mrr_email_addr_name']."' at E-Mail address '".$_POST['mrr_email_addr']."'.</b> ".$prefix." <br><br>";
	}
} ?>
</form>
<?
// for debug testing
/*
$tmp_total = 0;
ksort($truck_pay_array);
foreach($truck_pay_array as $key => $value) {
	if(is_numeric($value)) $tmp_total += $value;
	echo "$key | ".(is_numeric($value) ? number_format($value) : "")."<br>";
}
echo "tmp total: ".number_format($tmp_total)."<br>";
echo "tmp driver pay: ".number_format($total_driver_pay_miles)."<br>";
*/
?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	$('.tablesorter').tablesorter();
</script>
<? include('footer.php') ?>