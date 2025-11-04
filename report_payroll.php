<? $usetitle = "Report - Payroll" ?>
<? include('header.php') ?>
<?
	$use_title = "Report - Payroll";
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	
	if(!isset($_POST['date_from'])) 			$_POST['date_from'] = date("n/1/Y", time());
	if(!isset($_POST['date_to'])) 			    $_POST['date_to'] = date("n/j/Y", time());
	if(!isset($_POST['driver_id'])) 			$_POST['driver_id'] = 0;
	if(!isset($_POST['employer_id'])) 			$_POST['employer_id'] = 0;
	if(!isset($_POST['payroll_mode']))			$_POST['payroll_mode']=0;
	
	//new email sending line to submit and send the email directly from this report. 
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))		$_POST['build_report'] = 1;
	
	$mileage_match_perc=trim($defaultsarray['payroll_mileage_percentage']);
	
	if(!isset($_POST['report_payroll_date'])) 	$_POST['report_payroll_date'] ="";
		
	$truck_pay_array[] = array();
	$total_driver_pay = 0;
	$running_total = 0;
	
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
	
	$driver_pay_mode=$_POST['payroll_mode'];
	
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
		
		$driver_hours_worked-=$driver_pre_hours_worked;
         
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
					<td colspan='19'>&nbsp;</td>
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
        
        if($driver_pay_ooic) 
		{
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Driver O.O./I.C.: </td>
					
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
									
					<td colspan='11'><b>< - - Excluded from Payroll Bills</b></td>
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
					
					<td colspan='10'>&nbsp;</td>
				</tr>
			";
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
		elseif($driver_pay_hours) {
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
								
					<td colspan='10'>&nbsp;</td>
				</tr>
			";
		}
		
		$total_driver_pay += ($driver_pay_miles + $driver_pay_hours + $driver_pay_team);	//$driver_pay + $driver_pay_ooic
		//$total_driver_pay += $driver_miles + $driver_miles_deadhead + $driver_hourly_miles + $driver_hourly_miles_deadhead + $driver_team_miles_deadhead;
		$running_total += ($driver_pay_miles + $driver_pay_hours + $driver_pay_team);		//$driver_pay + $driver_pay_ooic
		echo "
			<tr style='background-color:#d0deff'>
				<td colspan='3'>Total Driver Pay: </td>
				
				<td align='right'>$".money_format('',$driver_pay + $driver_pay_team + $amnt)."</td>
				<td align='right'>".number_format($driver_miles)."</td>
				<td align='right'>".number_format($driver_miles_deadhead + $driver_team_miles_deadhead)."</td>
				<td align='right'>".number_format($driver_miles + $driver_miles_deadhead + $driver_team_miles_deadhead)."</td>
				
				<td align='right'>".(number_format($driver_hours_worked) == $driver_hours_worked ? number_format($driver_hours_worked) : $driver_hours_worked)."</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>$".money_format('',$mrr_driver_pay_miles)."</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>$".money_format('',$mrr_driver_pay_hours)."</td>				
				<td align='right'>$".money_format('',$mrr_driver_pay_ooic)."</td>
				
				<td colspan='10'>&nbsp;</td>
			</tr>			
		";
	}
	
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
		$pay_header4="Pay Hour";
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

	$data_master = get_master_query();
		
	ob_start();
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;text-align:left'>
        $defaultsarray
	<tr>
		<td colspan='16'>
			<center>
                <p>Dispatches in <span class='mrr_highlight_ooic_unmatched'>Red Text</span> do not match the current <span class='mrr_highlight_ooic_unmatched'><?=$mileage_match_perc ?>%</span> Payroll Mileage Percentage</p>

                <br>
                <span class='section_heading'>Payroll Report</span>
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
     				if($current_driver != '') {
     					//show_driver_totals($driver_miles, $driver_miles_deadhead, $driver_hours_worked, $driver_per_mile, $driver_per_hour);
     					show_driver_totals($current_truck_id,$current_driver_id);
     					echo "<tr><td colspan='24'><hr></td></tr>";
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
          								<td colspan='16'>".date("m-d-Y", strtotime($row_driver_expenses['linedate']))." - $row_driver_expenses[desc_long]</td>
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
     						echo "
     							<tr style='background-color:#d0ffdb'>
     								<td colspan='2'>".$account."</td>
     								<td>".$exp_typer."</td>
     								<td align='right'>$".money_format('',$dva_pay)."</td>
     								<td></td>
     								<td colspan='16'>".date("m-d-Y", strtotime($row_vacations['linedate']))." - ".$row_vacations['comments']."</td>
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
     				elseif($smp > 0)						    $mrr_per_mile_labor=$smp;		
     				elseif($row['charged_per_mile'] > 0)		$mrr_per_mile_labor=$row['charged_per_mile'];	
     				
     				$shp=mrr_grab_driver_payroll_item($row['driver_id'],date("m/d/Y", strtotime($row['linedate_pickup_eta'])),1);		//single hour pay
     				
     				if($row['driver1_pay_per_hour'] > 0)		$mrr_per_hour_labor=$row['driver1_pay_per_hour'];	
     				elseif($shp > 0)						    $mrr_per_hour_labor=$shp;	
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
     						elseif($smp > 0)						    $mrr_per_mile_labor=$smp;
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
                    $miles_diff=abs(round((($row['miles'] + $row['loaded_miles_hourly']) - $row['pcm_miles']), 2));
                    $miles_perc=0;
                    if($row['pcm_miles'] > 0)       $miles_perc=($miles_diff/ $row['pcm_miles']) * 100;
                
                    $mrr_classy="";
                    $mrr_title_disp="Difference between Miles (".($row['miles'] + $row['loaded_miles_hourly']).") and PC*M Miles ".$row['pcm_miles']." is ".$miles_diff." miles... ".round($miles_perc,2)."% of PC*M.";
                     
                    if($miles_perc > $mileage_match_perc)       $mrr_classy=" mrr_highlight_ooic_unmatched";
                     
     				echo "
     					<tr class='".($this_driver_count % 2 == 1 ? 'odd' : 'even')."".$mrr_classy."'>
     						<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
     						<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
     						<td nowrap>$row[origin]</td>
     						<td nowrap>$row[destination]</td>
     						<td align='right' title='".$mrr_title_disp."'>".number_format($row['miles'])."</td>
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
     					";
     				}
     			}
     			
     			while($row_expense = mysqli_fetch_array($data_expenses)) {
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
               			     				
          				if(!isset($_POST['summary_only'])) {
          					echo "
          						<tr style='background-color:#d0ffdb'>
          							<td colspan='2'>".$account."</td>
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
			}
		}
		if($mrr_all_drivers > 0)			show_driver_totals($current_truck_id,$current_driver_id);
		
		//---------------------------------------------------------------------NOW SHOW EXPENSES FOR OTHER DRIVERS NOT ON THE LOAD LIST...or vacation pay------------------------------------------------------------------------------
		$expenses_checked=0;
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
     						show_driver_totals();
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
     							<tr style='background-color:#d0ffdb'>
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
		}//end check for expenses...
		
		
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
	
	if($_POST['payroll_mode']!=1)
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
     						echo $mrr_temp;
     						show_driver_totals();
     						echo "<tr><td colspan='24'><hr></td></tr>";	
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
          			{	//shuttle run...flat pay...NOT FOR PAYROLL...............Only for Carlex Invoice
          				//$driver_pay += $exp_amnt[ $i ];
          				
          				
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
     						echo "<tr><td colspan='24'><hr></td></tr>";	
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
     			{	//shuttle run...flat pay...NOT FOR PAYROLL....only for Caelex Invoice
     				//$driver_pay += $exp_amnt[ $i ];
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
     		echo "<tr><td colspan='24'><hr></td></tr>";	
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
	<tr>
		<td colspan='15'>
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
	<tr>
		<td colspan='19'>
			<table style='font-weight:bold'>
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
			<!--
			<tr>
				<td nowrap>Total Driver Pay (shuttle runs)</td>
				<td align='right'>$<?=money_format('',$carlex_total2)?></td>
			</tr>
			-->
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
				<td colspan='5'><hr></td>
			</tr>
			<tr>
				<td>Total</td>
				<td style='width:100px' align='right'>$<?=money_format('',$total_driver_pay + $total_expenses)?></td>
			</tr> 
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
</script>
<? include('footer.php') ?>