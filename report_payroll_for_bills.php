<? $usetitle = "Report - Payroll for Bills" ?>
<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	
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

	if(!isset($_POST['date_from'])) $_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) $_POST['date_to'] = date("n/j/Y", time());
	if(!isset($_POST['driver_id'])) $_POST['driver_id'] = 0;
	if(!isset($_POST['employer_id'])) $_POST['employer_id'] = ''; 
	
	?>
	<form action="<?=$SCRIPT_NAME ?>" method="post">
	<table width='900'>
		<tr>
			<td valign='top'>				
          	<?	
          	
          	$rfilter = new report_filter();
          	$rfilter->show_truck 			= true;
          	$rfilter->show_employers 		= true;
          	$rfilter->show_driver			= true;
          	$rfilter->summary_only	 		= true;
          	$rfilter->team_choice	 		= false;
          	$rfilter->mrr_no_form_enclosed	= true;	
          	$rfilter->show_font_size			= true;
          	$rfilter->show_filter();
          	
          	$skip_overtime_separation=1;
          	
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
     					<td valign='top' colspan='2'><b>Notes:</b></td>
     				</tr>
     				<tr>
     					<td valign='top' width='200'><b>Select Employer:</b></td>
     					<td valign='top'>One at a time is recommended.</td>
     				</tr>
     				<tr>
     					<td valign='top'><b>Select Others:</b></td>
     					<td valign='top'>Then choose Date Range, Truck, and other fields as needed</td>
     				</tr>
     				<tr>
     					<td valign='top'><b>"Payroll Report - For Bills"</b></td>
     					<td valign='top'>This report should be identical to the "Payroll Report - By Truck" report, except that Comcheck charges are not included.</td>
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
	
	function get_master_query($driver_id_filter = 0) {
		
		$employ_adder="";
		$employ_adder2="";
		if($_POST['employer_id'] > 0)
		{
			//$employ_adder=" and (trucks_log.employer_id = '".sql_friendly($_POST['employer_id'])."' or drivers2.employer_id = '".sql_friendly($_POST['employer_id'])."')";	
			$employ_adder=" and (
				(
					(trucks_log.employer_id > 0 and trucks_log.employer_id = '".sql_friendly($_POST['employer_id'])."')
					or
					(trucks_log.employer_id=0 and drivers.employer_id='".sql_friendly($_POST['employer_id'])."')
				
				)
				or 
				(drivers2.employer_id = '".sql_friendly($_POST['employer_id'])."')
			)";	
		}
		
		$sql = "
			select trucks_log.*,
				drivers.employer_id as driver1_employer_id,
				drivers.id as driver_id,
				drivers.name_driver_first,
				drivers.name_driver_last,				
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company,
				drivers.charged_per_mile,
				drivers.charged_per_hour,
				drivers.charged_per_mile_team,
				if(trucks_log.labor_per_mile > 0, trucks_log.labor_per_mile, drivers.charged_per_mile) as dispatch_charged_per_mile,
				if(trucks_log.labor_per_hour > 0, trucks_log.labor_per_hour, drivers.charged_per_hour) as dispatch_charged_per_hour,
				trucks_log.hours_worked,
				drivers2.employer_id as driver2_employer_id,
				drivers2.name_driver_first as name2_driver_first,
				drivers2.name_driver_last as name2_driver_last,
				drivers2.charged_per_mile as driver2_charged_per_mile,
				drivers2.charged_per_hour as driver2_charged_per_hour,
				drivers2.charged_per_mile_team as driver2_charged_per_mile_team
			
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
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' " : "")."
				".($driver_id_filter ? " and (trucks_log.driver_id = '".sql_friendly($driver_id_filter)."' or trucks_log.driver2_id = '".sql_friendly($driver_id_filter)."')" : '') ."
				".$employ_adder."
				".($_POST['team_choice'] == 0 ? "" : ($_POST['team_choice'] == '1' ? " and trucks_log.driver2_id > 0 " : " and trucks_log.driver2_id = 0 "))."
						
			order by name_truck, load_handler_id
		";
		//
		//d($sql);
		$data = simple_query($sql);
		
		return $data;
	}
	function get_master_expense_query($employer=0,$driver_id=0) 
	{			
			//from drivers_expenses,drivers,option_values
			//and drivers.id = drivers_expenses.driver_id
			//and expense_type_id=option_values.id	
				
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
				and drivers_expenses.linedate between '".date("Y-m-d", strtotime($_POST['date_from']))."' and '".date("Y-m-d", strtotime($_POST['date_to']))."'
			order by drivers_expenses.linedate asc
				";	
		//d($sql);
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
		
		return $data;
	}
	
	function get_master_vacation_advance_query($employer=0,$driver_id=0) 
	{	
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
     			and driver_vacation_advances_dates.linedate between '".date("Y-m-d", strtotime($_POST['date_from']))."' and '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
     		order by driver_vacation_advances_dates.linedate asc
				";	
		//d($sql);
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
		
		return $data;
	}
	//$cntr2=0;
	
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
?>


<? if(isset($_POST['build_report']) || $mrr_process > 0) { ?>
	<?
	
	function show_truck_totals() {
		global $driver_pay;
		global $driver_miles;
		global $driver_pay_miles;
		global $driver_pay_hours;
		
		global $mrr_driver_pay_miles;
		global $mrr_driver_pay_hours;				
		
		global $driver_miles_deadhead;
		global $current_truck;
		global $driver_expenses;
		global $driver_hours_worked;
		global $driver_team_miles;
		global $driver_pay_team;
		global $driver_team_miles_deadhead;
		global $driver_hourly_miles;
		global $total_driver_pay_miles;
		global $driver_hourly_miles_deadhead;
		
		if($driver_expenses > 0) {
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Truck Expenses</td>
					<td align='right'>$".money_format('',$driver_expenses)."</td>
					<td></td>
					<td colspan='14'></td>
				</tr>
			";
		}
		
		if($driver_pay_miles && $driver_pay_hours) {
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Truck Pay for Miles: </td>
					<td align='right'>$".money_format('',$driver_pay_miles + $driver_pay_team)."</td>
					<td align='right'>".number_format($driver_miles)."</td>
					<td align='right'>".number_format($driver_miles_deadhead)."</td>
					<td align='right'>".number_format($driver_miles + $driver_miles_deadhead)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>$".money_format('',$mrr_driver_pay_miles)."</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>&nbsp;</td>
					<td colspan='10'>&nbsp;</td>
				</tr>
			";
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Truck Pay for Hours: </td>
					<td align='right'>$".money_format('',$driver_pay_hours)."</td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'></td>
					<td align='right'>".(number_format($driver_hours_worked) == $driver_hours_worked ? number_format($driver_hours_worked) : $driver_hours_worked)."</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>					
					<td align='right'>$".money_format('',$mrr_driver_pay_hours)."</td>
					<td colspan='10'>&nbsp;</td>
				</tr>
			";
		}
		elseif($driver_pay_miles) {
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Truck Pay for Miles: </td>
					<td align='right'>$".money_format('',$driver_pay_miles + $driver_pay_team)."</td>
					<td align='right'>".number_format($driver_miles)."</td>
					<td align='right'>".number_format($driver_miles_deadhead)."</td>
					<td align='right'>".number_format($driver_miles + $driver_miles_deadhead)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>$".money_format('',$mrr_driver_pay_miles)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td colspan='10'>&nbsp;</td>
				</tr>
			";
		}		
		elseif($driver_pay_hours) {
			echo "
				<tr style='background-color:#d0deff'>
					<td colspan='3'>Total Truck Pay for Hours: </td>
					<td align='right'>$".money_format('',$driver_pay_hours)."</td>
					<td align='right'>".number_format($driver_hourly_miles)."</td>
					<td align='right'>".number_format($driver_hourly_miles_deadhead)."</td>
					<td align='right'>".number_format($driver_hourly_miles + $driver_hourly_miles_deadhead)."</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>&nbsp;</td>
					<td align='right'>$".money_format('',$mrr_driver_pay_hours)."</td>
					<td align='right'>&nbsp;</td>
					<td colspan='10'>&nbsp;</td>
				</tr>
			";
		}
		
		$total_driver_pay_miles += $driver_pay_miles + $driver_pay_team;		//$driver_pay + $driver_pay_team;
		
		echo "
			<tr style='background-color:#d0deff'>
				<td colspan='3'>Total Truck Pay: </td>
				<td align='right'>$".money_format('',$driver_pay + $driver_pay_team)."</td>
				<td align='right'>".number_format($driver_miles)."</td>
				<td align='right'>".number_format($driver_miles_deadhead + $driver_team_miles_deadhead)."</td>
				<td align='right'>".number_format($driver_miles + $driver_miles_deadhead + $driver_team_miles_deadhead)."</td>
				<td align='right'>".(number_format($driver_hours_worked) == $driver_hours_worked ? number_format($driver_hours_worked) : $driver_hours_worked)."</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>$".money_format('',$mrr_driver_pay_miles)."</td>
				<td align='right'>&nbsp;</td>
				<td align='right'>$".money_format('',$mrr_driver_pay_hours)."</td>
				<td colspan='10'>&nbsp;</td>
			</tr>
		";
		/*
		echo "
			<tr style='background-color:#d0deff'>
				<td colspan='3'>Total Truck Pay: </td>
				<td align='right'>$".money_format('',$driver_pay + $driver_pay_team)."</td>
				<td align='right'>".number_format($driver_miles + $driver_hourly_miles)."</td>
				<td align='right'>".number_format($driver_miles_deadhead + $driver_team_miles_deadhead + $driver_hourly_miles_deadhead)."</td>
				<td align='right'>".number_format($driver_miles + $driver_miles_deadhead + $driver_hourly_miles + $driver_hourly_miles_deadhead)."</td>
				<td align='right'>".(number_format($driver_hours_worked) == $driver_hours_worked ? number_format($driver_hours_worked) : $driver_hours_worked)."</td>
				<td colspan='10'>&nbsp;</td>
			</tr>
		";
		*/		
	}
	
	$max_overtime_hours= (int)$defaultsarray['overtime_hours_min'];
	$def_overtime_pay_rate=1.00;								//likely will be changed to 1.50 (time and a half)
	if(is_numeric($defaultsarray['overtime_def_rate']))
	{
		$def_overtime_pay_rate=$defaultsarray['overtime_def_rate'];
	}
	
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
	
		$empl_counter++;
     }		
	//$myname=mrr_get_employer_by_id($emp_id);
	
	$header_columns = "
		<tr style='font-weight:bold'>
			<td valign='top'>Load ID</td>
			<td valign='top'>Dispatch ID</td>
			<td valign='top'>Origin</td>
			<td valign='top'>Destination</td>
			<td valign='top' align='right'>Miles</td>
			<td valign='top' align='right'>Deadhead</td>
			<td valign='top' align='right'>Total Miles</td>
			<td valign='top' align='right'>Hours Worked</td>
			<td align='right'>Labor Mile</td>
			<td align='right'>Pay Mile</td>
			<td align='right'>Labor Hour</td>
			<td align='right'>Pay Hour</td>
			<td valign='top'>Date</td>
			<td valign='top'>Driver</td>
			<td valign='top'>Trailer</td>
			<td valign='top'>Customer</td>
			<td valign='top'></td>
		</tr>	
	";

	$data_master = get_master_query($_POST['driver_id']);	
		
	ob_start();
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;text-align:left'>
	<tr>
		<td colspan='13'>
			<center>
			<span class='section_heading'>Payroll Report - For Bills</span>
			</center>
		</td>
	</tr>

	<?
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_all_miles = 0;
		$total_mile_charge = 0;
		$current_truck = '';
		$total_driver_hours = 0;
		$total_hours = 0;
				
		$mrr_tot_per_mile=0;
		$mrr_tot_per_hour=0;		
		
		$driver_pay_miles = 0;
		$driver_pay_hours = 0;
		$total_hours_charge = 0;
		$total_driver_pay_miles = 0;
		$total_expenses = 0;
		
		$mrr_output2="";	
		$mrr_enum=0;
		
		while($row = mysqli_fetch_array($data_master)) 
		{
			$mrr_output2.="";
			
			$driver1_employer_id=$row['driver1_employer_id'];
			$driver2_employer_id=$row['driver2_employer_id'];
			
			$temp_employer_id=$row['employer_id'];
			
			$driver1_employer_id=$row['employer_id'];
			
			if($row['employer_id']==0 && $row['driver1_employer_id'] > 0)
			{
				$row['employer_id']=$row['driver1_employer_id'];
				$temp_employer_id=$row['driver1_employer_id'];;	
				$driver1_employer_id=$row['driver1_employer_id'];
			}
						
			$mrr_drivers_switched="";
			$mrr_temp_switch_emp=0;
			
			if($_POST['employer_id'] > 0 && $_POST['employer_id']==$driver2_employer_id && $_POST['employer_id']!=$driver1_employer_id)
			{	//driver2 is focus for this employer, not driver1... they work for different companies...so switching the drivers for this function should make processing easier
				
				//store driver 2 info
				$mrr_temp_switch_id=$row['driver2_id'];
				$mrr_temp_switch_fname=$row['name2_driver_first'];
				$mrr_temp_switch_lname=$row['name2_driver_last'];
				$mrr_temp_switch_cpermile=$row['driver2_charged_per_mile'];
				$mrr_temp_switch_cperhour=$row['driver2_charged_per_hour'];
				$mrr_temp_switch_cperteam=$row['driver2_charged_per_mile_team'];
				$mrr_temp_switch_emp=$driver2_employer_id;
				
				//make driver 2 from driver 1 info					
				$row['driver2_id']=$row['driver_id'];
				$row['name2_driver_first']=$row['name_driver_first'];
				$row['name2_driver_last']=$row['name_driver_last'];
				$row['driver2_charged_per_mile']=$row['charged_per_mile'];
				$row['driver2_charged_per_hour']=$row['charged_per_hour'];
				$row['driver2_charged_per_mile_team']=$row['charged_per_mile_team'];
				$driver2_employer_id=$driver1_employer_id;
				
				//make driver 1 from stored driver 2 info
				$row['driver_id']=$mrr_temp_switch_id;
				$row['name_driver_first']=$mrr_temp_switch_fname;
				$row['name_driver_last']=$mrr_temp_switch_lname;
				$row['charged_per_mile']=$mrr_temp_switch_cpermile;
				$row['charged_per_hour']=$mrr_temp_switch_cperhour;
				$row['charged_per_mile_team']=$mrr_temp_switch_cperteam;					
				$driver1_employer_id=$mrr_temp_switch_emp;
				
				//$mrr_drivers_switched="".$_POST['employer_id'].":".$driver1_employer_id.":".$driver2_employer_id.":".$temp_employer_id."";
				
				
				
				//if($temp_employer_id!=$_POST['employer_id'] && $temp_employer_id==$driver2_employer_id)
				//{
				//	$temp_employer_id=$_POST['employer_id'];
				//	$row['employer_id']=$_POST['employer_id'];
				//}
				
			}			
			
			if($current_truck != $row['truck_id']) {
				if($current_truck != '') {
					//show_truck_totals($driver_miles, $driver_miles_deadhead, $driver_hours_worked, $driver_per_mile, $driver_per_hour);
					show_truck_totals();
					echo "<tr><td colspan='24'><hr></td></tr>";
					
				}
								
				
				$current_truck = $row['truck_id'];
				$current_truck_name = $row['name_truck'];
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
								
				$exps_cntr=0;		$exps_val=0;		$exps_list="";		$exps_prices="";
				$exps_cntr2=0;		$exps_val2=0;		$exps_list2="";	$exps_prices2="";
				// get the list of driver expenses
				$sql = "
					select drivers_expenses.*,
						drivers.name_driver_first,
     					drivers.name_driver_last,
						option_values.fvalue as expense_type
					
					from drivers_expenses
						left join drivers on drivers.id = drivers_expenses.driver_id
						left join option_values on option_values.id = drivers_expenses.expense_type_id
					where drivers_expenses.driver_id = '$row[driver_id]'
						and drivers_expenses.deleted = 0
						and drivers.deleted = 0
						and option_values.deleted = 0
						and drivers_expenses.payroll = 1
						and drivers_expenses.linedate between '".date("Y-m-d", strtotime($_POST['date_from']))."' and '".date("Y-m-d", strtotime($_POST['date_to']))."'
						
					order by linedate
				";	//
				$data_driver_expenses = simple_query($sql);
												
				echo "
					
					<tr>
						<td colspan='3'>Truck <a href='admin_drivers.php?id=$row[driver_id]' target='manage_driver_$row[driver_id]'>$current_truck_name</a><br></td>
					</tr>
				";
				echo "
					
					<tr>
						<td colspan='3'>&nbsp;</td>
					</tr>
				";
				if(!isset($_POST['summary_only'])) {
					echo $header_columns;
				}
				
				$exps_cntr_mrr=mysqli_num_rows($data_driver_expenses);
				while($row_driver_expenses = mysqli_fetch_array($data_driver_expenses)) {
					//track ones that show up in proper date range... this is to skip them later on the next search for expenses not directly attached to loads in range...but still needed in date span.
     				$mrr_found_exp=0;
     				$mrr_use_index=-1;
     				for($xcx=0;$xcx < $mrr_enum; $xcx++)
     				{
     					if($mrr_exps[ $xcx ]==$row_driver_expenses['id'])
     					{
     						$mrr_found_exp=1;
     						$mrr_use_index=$xcx;
     					}	
     				}
     				if($mrr_found_exp==0)
     				{
     					if(trim($row_driver_expenses['expense_type'])!='Comcheck')
     					{
          					//$driver_expenses += $row_driver_expenses['amount_billable'];
          					//$total_expenses += $row_driver_expenses['amount_billable'];
          					  					
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
               				
          					//$exps_cntr++;
          					//$exps_val+=$row_driver_expenses['amount_billable'];
               				//$exps_list.="(".$account.") ";
               				//$exps_prices.="(".$row_driver_expenses['amount_billable'].") ";        				
               				  					
     					}	
     				} //end if not found				

					
				}
				
				
			}
						
			$mile_cntr=0;		$mile_val=0;
			$hour_cntr=0;		$hour_val=0;
			$stop_cntr=0;		$stop_val=0;
			$bonus_cntr=0;		$bonus_val=0;
			$disp_cntr=0;		$disp_val=0;
			$team_cntr=0;		$team_val=0;
			
			$tm_cntr=0;
			$tm_miles=0;
			$single_rate=$row['dispatch_charged_per_mile'];	
			$single_hr_rate=$row['dispatch_charged_per_hour'];
			//$single_rate2=(float)$row['dispatch_charged_per_mile'];	
			//$single_hr_rate2=(float)$row['dispatch_charged_per_hour'];
			//$single_rate2=(float)$row['dispatch_charged_per_mile'];	
			//$single_hr_rate2=(float)$row['dispatch_charged_per_hour'];
			
			$tm_rate=$row['dispatch_charged_per_mile'];		$tm_hr_rate=$row['dispatch_charged_per_hour'];
			$tm_rate2=$row['driver2_charged_per_mile'];		$tm_hr_rate2=$row['driver2_charged_per_hour'];
			//second driver count
			$mrr_driver2_id=0;
			$mrr_driver2_name="";			
			$mile_cntr2=0;		$mile_val2=0;
			$hour_cntr2=0;		$hour_val2=0;
			$stop_cntr2=0;		$stop_val2=0;
			$bonus_cntr2=0;	$bonus_val2=0;
			$disp_cntr2=0;		$disp_val2=0;
			$team_cntr2=0;		$team_val2=0;
								
			$counter++;
			$this_driver_count++;
			
			
			
			
			//NEW calculation for pay based on settings from the dispatch for labor...not the general driver settings, which may have changed since then... Added May 2013
			$mrr_per_hour_labor=$row['labor_per_hour'];
			$mrr_per_mile_labor=$row['labor_per_mile'];
			
			//$single_rate=$row['labor_per_mile'];	
			//$single_hr_rate=$row['labor_per_hour'];
			
			if($row['driver2_id'] > 0)
     		{
     			$mrr_per_hour_labor1 = $mrr_per_hour_labor / 2;
     			$mrr_per_hour_labor2 = $mrr_per_hour_labor / 2;	
     			$mrr_per_mile_labor1 = $mrr_per_mile_labor / 2;
     			$mrr_per_mile_labor2 = $mrr_per_mile_labor / 2;
     			if($row['driver_2_labor_per_mile'] > 0)
     			{     					
     				$mrr_per_mile_labor1 = $mrr_per_mile_labor - $row['driver_2_labor_per_mile'];
     				$mrr_per_mile_labor2 = $row['driver_2_labor_per_mile'];
     			}
     			if($row['driver_2_labor_per_hour'] > 0)
     			{     					
     				$mrr_per_hour_labor1 = $mrr_per_hour_labor - $row['driver_2_labor_per_hour'];
     				$mrr_per_hour_labor2 = $row['driver_2_labor_per_hour'];
     			}
     			
     			if($mrr_temp_switch_emp>0)
     			{	//driver 1 is now driver 2
     				
     				$mrr_per_mile_labor2 = 0;
     				$mrr_per_hour_labor2 = 0;
     				
     				$mrr_per_mile_labor1 = $row['driver_2_labor_per_mile'];
     				$mrr_per_hour_labor1 = $row['driver_2_labor_per_hour'];     				
     			}
     		}
     		     		
     		$tmp_cur_hours=$driver_hours_worked;						//hold hours before this set is added....
     		$tmp_hourly_pay=0;
     		     		
     		$driver_hours_worked += $row['hours_worked'];
			$total_hours += $row['hours_worked'];
			
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
			$mrr_tot_per_hour+=$tmp_hourly_pay;		//($row['hours_worked'] *$mrr_per_hour_labor);
			
			$mrr_driver_pay_miles += (($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor);
			$mrr_driver_pay_hours += $tmp_hourly_pay;	//($row['hours_worked'] *$mrr_per_hour_labor);
						
			$driver_pay_hours += $tmp_hourly_pay;
    			$driver_pay += $tmp_hourly_pay;
    			//$truck_pay_array[$row['name_truck']] += $tmp_hourly_pay;
			//.............................................................................................................................................................
			
			
			$line_miles = $row['miles'] + $row['miles_deadhead'];
			if($row['hours_worked'] > 0) {
				// if this driver was paid hourly, then we don't pay by the mile ===================================FALSE...Calculate both of them....
				/*
				$driver_pay += $row['hours_worked'] * $row['dispatch_charged_per_hour'];
				$driver_pay_hours += $row['hours_worked'] * $row['dispatch_charged_per_hour'];;
				$driver_hourly_miles += $row['miles'];
				$driver_hourly_miles_deadhead = $row['miles_deadhead'];
				*/
				
				//$driver_pay += $row['hours_worked'] * $row['dispatch_charged_per_hour'];
				//$driver_pay_hours += $row['hours_worked'] * $row['dispatch_charged_per_hour'];
				$driver_hourly_miles += $row['miles'];
				$driver_hourly_miles_deadhead += $row['miles_deadhead'];	
				
				if($line_miles>0)
				{
     				if($row['driver2_id'] > 0 && $driver2_employer_id==$temp_employer_id) 
     				{
     					//separate for report for bills
     					$tm_rate=$mrr_per_mile_labor1;
     					$tm_rate2=$mrr_per_mile_labor2;
     					$tm_miles=$line_miles;
     					
     					$team_cntr=$row['miles'];
						$team_val=($tm_rate*$row['miles']);
					
						$team_cntr2=$row['miles'];
						$team_val2=($tm_rate2*$row['miles']);
     					
     					//normal calculation....
     					$line_pay_miles = $line_miles * ($mrr_per_mile_labor1 + $mrr_per_mile_labor2);
     					$driver_pay_team += $line_pay_miles;
     					//echo "<tr><td>$line_miles | $line_pay_miles</td></tr>";
     				} 
     				elseif($row['driver2_id'] > 0 && $driver1_employer_id!=$temp_employer_id) 
     				{
     					//separate for report for bills
     					$tm_rate=0;
     					$tm_rate2=$mrr_per_mile_labor2;
     					$tm_miles=$line_miles;
     					
     					$team_cntr=0;
						$team_val=0;
					
						$team_cntr2=$row['miles'];
						$team_val2=($tm_rate2*$row['miles']);
     					
     					//normal calculation....
     					$line_pay_miles = $line_miles * (0 + $mrr_per_mile_labor2);
     					$driver_pay_team += $line_pay_miles;
     					//echo "<tr><td>$line_miles | $line_pay_miles</td></tr>";
     				}
     				elseif($driver1_employer_id==$temp_employer_id) 
     				{
     					if($row['driver2_id'] > 0 && $driver2_employer_id!=$temp_employer_id)
     					{
     						//separate for report for bills
     						$tm_rate=$mrr_per_mile_labor1;
     						$tm_rate2=0;
     						$tm_miles=$line_miles;
     						
     						$team_cntr=$row['miles'];
							$team_val=($tm_rate*$row['miles']);
						
							$team_cntr2=0;
							$team_val2=0;
     						
     						//normal calculation....
     						$line_pay_miles = $line_miles * ($row['charged_per_mile_team'] + 0);
     						$driver_pay_team += $line_pay_miles;
     						//echo "<tr><td>$line_miles | $line_pay_miles</td></tr>";
     					}
     					else
     					{     					
     						$driver_pay += $line_miles * $row['dispatch_charged_per_mile'];
     						$driver_pay_miles += $line_miles * $row['dispatch_charged_per_mile'];
     						//echo "<tr><td>$line_miles | ".($line_miles * $row['charged_per_mile'])."</td></tr>";
     					}
     				}
     				
     				$total_mile_charge += ($line_miles * $row['dispatch_charged_per_mile']);
     				$driver_miles += $row['miles'];
				}	
			} else {
				if($row['driver2_id'] > 0 && $driver2_employer_id==$temp_employer_id) 
				{
					//separate for report for bills
					$tm_rate=$mrr_per_mile_labor1;
     				$tm_rate2=$mrr_per_mile_labor2;
     				$tm_miles=$line_miles;
     				     				
					$team_cntr=$row['miles'];
					$team_val=($tm_rate*$row['miles']);
					
					$team_cntr2=$row['miles'];
					$team_val2=($tm_rate2*$row['miles']);
     						
					$line_pay_miles = $line_miles * ($mrr_per_mile_labor1 + $mrr_per_mile_labor2);
					$driver_pay_team += $line_pay_miles;
					//echo "<tr><td>$line_miles | $line_pay_miles</td></tr>";
				} 
				elseif($row['driver2_id'] > 0 && $driver1_employer_id!=$temp_employer_id) 
				{
					//separate for report for bills
					$tm_rate=0;
     				$tm_rate2=$mrr_per_mile_labor2;
     				$tm_miles=$line_miles;
     				     				
					$team_cntr=0;
					$team_val=0;
					
					$team_cntr2=$row['miles'];
					$team_val2=($tm_rate2*$row['miles']);
     						
					$line_pay_miles = $line_miles * (0 + $mrr_per_mile_labor2);
					$driver_pay_team += $line_pay_miles;
					//echo "<tr><td>$line_miles | $line_pay_miles</td></tr>";
				} 
				elseif($driver1_employer_id==$temp_employer_id)
				{
					if($row['driver2_id'] > 0 && $driver2_employer_id!=$temp_employer_id)
					{
						//separate for report for bills
     					$tm_rate=$mrr_per_mile_labor1;
          				$tm_rate2=0;
          				$tm_miles=$line_miles;
          				     				
     					$team_cntr=$row['miles'];
     					$team_val=($tm_rate*$row['miles']);
     					
     					$team_cntr2=0;
     					$team_val2=0;
          						
     					$line_pay_miles = $line_miles * ($mrr_per_mile_labor1 + 0);
     					$driver_pay_team += $line_pay_miles;
     					//echo "<tr><td>$line_miles | $line_pay_miles</td></tr>";
					}
					else
					{
     					$driver_pay += $line_miles * $row['dispatch_charged_per_mile'];
     					$driver_pay_miles += $line_miles * $row['dispatch_charged_per_mile'];
     					//echo "<tr><td>$line_miles | ".($line_miles * $row['charged_per_mile'])."</td></tr>";
					}
					
					
					
				}
				
				$total_mile_charge += ($line_miles * $row['dispatch_charged_per_mile']);
				$driver_miles += $row['miles'];
			}
			
			if($row['driver2_id'] > 0 && $driver2_employer_id==$temp_employer_id) 
			{
				$driver_team_miles_deadhead += $row['miles_deadhead'];
								
				$team_cntr+=$row['miles_deadhead'];
				$team_val+=($tm_rate*$row['miles_deadhead']);
					
				$team_cntr2+=$row['miles_deadhead'];
				$team_val2+=($tm_rate2*$row['miles_deadhead']);
				
			} 
			elseif($row['driver2_id'] > 0 && $driver1_employer_id!=$temp_employer_id) 
			{
				$driver_team_miles_deadhead += $row['miles_deadhead'];
								
				$team_cntr+=0;
				$team_val+=0;
					
				$team_cntr2+=$row['miles_deadhead'];
				$team_val2+=($tm_rate2*$row['miles_deadhead']);
				
			} 
			elseif($driver1_employer_id==$temp_employer_id)
			{
				if($row['driver2_id'] > 0 && $driver2_employer_id!=$temp_employer_id)
				{
					$driver_team_miles_deadhead += $row['miles_deadhead'];
								
					$team_cntr+=$row['miles_deadhead'];
					$team_val+=($tm_rate*$row['miles_deadhead']);
					
					$team_cntr2+=0;
					$team_val2+=0;
				}
				else
				{
					$driver_miles_deadhead += $row['miles_deadhead'];	
				}
			}
			$total_all_miles += $line_miles;
			if($row['driver2_id'] > 0 && $driver2_employer_id==$temp_employer_id) $driver_team_miles += $row['miles'];
			
			
			
			$total_miles += $row['miles'];
			$total_deadhead += $row['miles_deadhead'];
			
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
					and trucks_log.truck_id = '".sql_friendly($current_truck)."'
					and trucks_log.id = $row[id]
					and dispatch_expenses.deleted = 0
			";
			$data_expenses = simple_query($sql);
			
			$mrr_output="";
						
			if($row['driver2_id'] > 0 && $driver2_employer_id==$temp_employer_id)
			{
				$mile_val+=($tm_miles * $tm_rate);
				$mile_cntr+=($tm_miles);	
				
				$mile_val2+=($tm_miles * $tm_rate2);
				$mile_cntr2+=($tm_miles);	
				
					
				$hour_cntr+=$row['hours_worked'];
				$hour_val+=$tmp_hourly_pay;				// + $driver_pay_team	
							//($row['hours_worked'] * $row['dispatch_charged_per_hour'])
				$hour_cntr2+=$row['hours_worked'];
				$hour_val2+=$tmp_hourly_pay;		// + $driver_pay_team	
						//($row['hours_worked'] * $row['driver2_charged_per_hour'])
			}
			elseif($row['driver2_id'] > 0 && $driver2_employer_id==$temp_employer_id)
			{
				$mile_val+=($tm_miles * $tm_rate);
				$mile_cntr+=($tm_miles);	
				
				$mile_val2+=($tm_miles * $tm_rate2);
				$mile_cntr2+=($tm_miles);	
				
					
				$hour_cntr+=$row['hours_worked'];
				$hour_val+=$tmp_hourly_pay;				// + $driver_pay_team	
							//($row['hours_worked'] * $row['dispatch_charged_per_hour'])
				$hour_cntr2+=$row['hours_worked'];
				$hour_val2+=$tmp_hourly_pay;		// + $driver_pay_team	
						//($row['hours_worked'] * $row['driver2_charged_per_hour'])
			}
			elseif($driver1_employer_id==$temp_employer_id)
			{
				if($row['driver2_id'] > 0 && $driver2_employer_id!=$temp_employer_id)
				{
					$mile_val+=($tm_miles * $tm_rate);
					$mile_cntr+=($tm_miles);	
					
					$mile_val2+=0;
					$mile_cntr2+=0;
				
					$hour_cntr+=$row['hours_worked'];
					$hour_val+=$tmp_hourly_pay;				// + $driver_pay_team	
							//($row['hours_worked'] * $row['dispatch_charged_per_hour'])
					$hour_cntr2+=0;
					$hour_val2+=0;		// + $driver_pay_team	
				}
				else
				{
					$mile_val+=(($row['miles_deadhead'] + $row['miles']) * $row['dispatch_charged_per_mile']);
					$mile_cntr+=($row['miles_deadhead'] + $row['miles']);			
					$hour_cntr+=$row['hours_worked'];
					$hour_val+=$tmp_hourly_pay;				// + $driver_pay_team
							//($row['hours_worked'] * $row['dispatch_charged_per_hour'])
				}
			}
			
			
			
			
			if(!isset($_POST['summary_only'])) {
				$mrr_output.= "
					<tr class='".($this_driver_count % 2 == 1 ? 'odd' : 'even')."'>
						<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
						<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
						<td nowrap>$row[origin]</td>
						<td nowrap>$row[destination]</td>
						<td align='right'>".number_format($row['miles'])."</td>
						<td align='right'>".number_format($row['miles_deadhead'])."</td>
						<td align='right'>".number_format($row['miles_deadhead'] + $row['miles'])."</td>
						<td align='right'>".(number_format($row['hours_worked']) == $row['hours_worked'] ? number_format($row['hours_worked']) : $row['hours_worked'])."</td>
						
						<td align='right'>$".number_format($mrr_per_mile_labor,2)."</td>
						<td align='right'>$".number_format((($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor),2)."</td>
						<td align='right'>$".number_format($mrr_per_hour_labor,2)."</td>
						<td align='right'>$".number_format($tmp_hourly_pay,2)."</td>
						
						<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
						<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
						<td nowrap>$row[trailer_name]</td>
						<td nowrap>$row[name_company]</td>
						<td></td>
					</tr>
				";									//($row['hours_worked'] *$mrr_per_hour_labor )
				// && $driver2_employer_id==$temp_employer_id
				if($row['driver2_id'] > 0) {
					$mrr_driver2_name="$row[name2_driver_first] $row[name2_driver_last]";
					$mrr_driver2_id=$row['driver2_id'];
					
					$mrr_output.="
						<tr class='".($this_driver_count % 2 == 1 ? 'odd' : 'even')."' style='background-color:#ebc8c8'>
							<td colspan='2'>&nbsp;</td>
							<td>Team Run ".$mrr_drivers_switched."</td>
							<td colspan='15'>$row[name2_driver_first] $row[name2_driver_last]</td>
						</tr>
					";
				}
			}
			
			
			
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
          				
     				if($row['driver2_id'] > 0 && $driver2_employer_id==$temp_employer_id)
     				{
     					if(trim($row_expense['expense_type'])=='Panther bonus')
     					{
     						$bonus_cntr++;		$bonus_val+=$row_expense['expense_amount']/2;
     						$bonus_cntr2++;	$bonus_val2+=$row_expense['expense_amount']/2;
     					}
     					else
     					{
     						$stop_cntr++;		$stop_val+= $row_expense['expense_amount']/2;
     						$stop_cntr2++;		$stop_val2+= $row_expense['expense_amount']/2;
     					}    					
     				}
     				elseif($row['driver2_id'] > 0 && $driver1_employer_id!=$temp_employer_id)
     				{
     					if(trim($row_expense['expense_type'])=='Panther bonus')
     					{
     						//$bonus_cntr++;	$bonus_val+=$row_expense['expense_amount']/2;
     						$bonus_cntr2++;	$bonus_val2+=$row_expense['expense_amount']/2;
     					}
     					else
     					{
     						//$stop_cntr++;	$stop_val+= $row_expense['expense_amount']/2;
     						$stop_cntr2++;		$stop_val2+= $row_expense['expense_amount']/2;
     					} 
     				}
     				elseif($driver1_employer_id==$temp_employer_id)
     				{
     					//$stop_cntr++;		$stop_val+=$row_expense['expense_amount'];     					
     					//$bonus_cntr++;		$bonus_val+=$row_expense['expense_amount'];
     					
     					if(trim($row_expense['expense_type'])=='Panther bonus')
     					{
     						$bonus_cntr++;		$bonus_val+=$row_expense['expense_amount'];
     					}
     					else
     					{
     						$stop_cntr++;		$stop_val+= $row_expense['expense_amount'];
     					} 
     				}
     				    					
     				if(!isset($_POST['summary_only'])) {
     					$mrr_output.="
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
			
			
			//find employer info and add row to table if needed------------------------------------
			
			
     		
			//$cntr2++;
			$emp_id=$row['employer_id'];
			//$driver2_employer_id
			
			$emp2_id=$row['employer_id'];
			if($driver2_employer_id!=$row['employer_id'])	$emp2_id=$driver2_employer_id;
						
			
			$mrr_use_emp=0;						$mrr_found=0;
			$mrr_use_emp2=0;						$mrr_found2=0;
			for($i=0;$i < $empl_counter; $i++)
			{	//see if already in the loop
				if(	$employers[$i] == $emp_id )	
				{
					$mrr_use_emp=$i;				$mrr_found=1;
				}
				if(	$employers[$i] == $emp2_id )	
				{
					$mrr_use_emp2=$i;				$mrr_found2=1;
				}
			}
						
			if(	$employers[0] == $emp_id )	
			{
				$mrr_use_emp=0;				$mrr_found=1;
			}
			
			if($mrr_found==1)
			{	//in the loop so add to the loop
				//$employers[ $mrr_use_emp ] = $emp_id;	
				//$employer_names[ $mrr_use_emp ]=mrr_get_employer_by_id($emp_id);
				//$employer_rows[ $mrr_use_emp ] .=$mrr_output;
								
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
          							<td colspan='11'>(".$et_dfname." ".$et_dlname.") ".$et_dater." - ".$et_descript."</td>
          						</tr>
          					";
          					$total_expenses+=$et_amnt;
          					
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
          							<td colspan='11'>(".$et_dfname." ".$et_dlname.") ".$et_dater." - ".$et_descript."</td>
          						</tr>
          					";
          					$total_expenses+=$et_amnt;
          					
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
     			/**/
     											
				//drivers for each truck
				$mrr_use_driver=0;					$mrr_driver_found=0;	
				for($d=0;$d < $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck]; $d++)
				{
					if($row['driver_id'] == $employer_truck_driver_ids[ $mrr_use_emp ][$mrr_use_truck][ $d ] )
					{
						$mrr_use_driver=$d;			$mrr_driver_found=1;
						if($row['driver2_id']==0)
						{
							$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=(float)$single_hr_rate;
							$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=(float)$single_rate;
						}               			
					}
				}
								
				$mrr_driver2_name="$row[name2_driver_first] $row[name2_driver_last]";
				$mrr_driver2_id=$row['driver2_id'];
				if($mrr_driver2_id > 0 )
				{
					//$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=(float)$single_hr_rate;
               		//$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=(float)$single_rate;
					
					if($mrr_driver_found==0)
     				{
     					$employer_truck_driver_ids[ $mrr_use_emp ][$mrr_use_truck][ ( $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck] ) ]  =$row['driver_id'];
     					$employer_truck_driver_emps[ $mrr_use_emp ][$mrr_use_truck][ ( $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck] ) ]  =$row['driver1_employer_id'];
     					$mrr_use_driver=$employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck];
     					$employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck]++;
     					
     					//reset counters for this new driver...
     					$employer_truck_driver_names[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$row['name_driver_first']." ". $row['name_driver_last'];
               			//$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=(float)$single_hr_rate;
               			//$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=(float)$single_rate;
               			
               			$employer_truck_driver_hours_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_hr_rate;
						$employer_truck_driver_miles_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_rate;
               			
               			$employer_truck_driver_hours[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_miles[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_stops[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_expenses[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_expense_acct[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]="";
						$employer_truck_driver_expense_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_hours_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			$employer_truck_driver_stops_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
               			
               			$employer_truck_driver_expenses[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_cntr;
						$employer_truck_driver_expense_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_val;
						$employer_truck_driver_expense_acct[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_list;
						$employer_truck_driver_expense_prices[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_prices;	
						
						$employer_truck_driver_miles_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
						$employer_truck_driver_miles_rate_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
												
						$exps_cntr=0;
						$exps_val=0;
						$exps_list="";
						$exps_prices="";				
     				}
     				
               		$employer_truck_driver_hours[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$hour_cntr;
               		$employer_truck_driver_miles[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$mile_cntr;
               		$employer_truck_driver_stops[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$stop_cntr;
               		$employer_truck_driver_bonus[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$bonus_cntr;
               		$employer_truck_driver_hours_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$hour_val;
               		
               		$employer_truck_driver_miles_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$team_cntr;
					$employer_truck_driver_miles_rate_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$team_val;
					               		
               		if($driver_pay_team > 0)
               		{
               			//$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$driver_pay_team;
               			$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$mile_val;
               		}
               		else
               		{
               			$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$mile_val;
               		}
               		
               		$employer_truck_driver_stops_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$stop_val;	
               		$employer_truck_driver_bonus_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$bonus_val;	             		
					
					//$exps_cntr=0;
					//$exps_val=0;
					//$exps_list="";
					//$exps_prices="";
               		               		
               		//now add driver 2 stats............
               		$mrr_use_driver=0;					$mrr_driver_found=0;	
     				for($d=0;$d < $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck]; $d++)
     				{
     					if($mrr_driver2_id == $employer_truck_driver_ids[ $mrr_use_emp ][$mrr_use_truck][ $d ] )
     					{
     						$mrr_use_driver=$d;			$mrr_driver_found=1;
     					}
     				}
               		
               		if($mrr_driver_found==0)
     				{
     					$employer_truck_driver_ids[ $mrr_use_emp ][$mrr_use_truck][ ( $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck] ) ]  =$mrr_driver2_id;
     					$employer_truck_driver_emps[ $mrr_use_emp ][$mrr_use_truck][ ( $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck] ) ]  =$row['driver2_employer_id'];
     					$mrr_use_driver=$employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck];
     					$employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck]++;
     					
     					//reset counters for this new driver...
     					$employer_truck_driver_names[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$mrr_driver2_name;
               			//$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_hr_rate2;
               			//$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_rate2;
               			
               			//$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$single_hr_rate2;
               			//$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$single_rate2;
               			
               			$employer_truck_driver_hours_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_hr_rate2;
						$employer_truck_driver_miles_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_rate2;
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
               			
               			$employer_truck_driver_expenses[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_cntr2;
						$employer_truck_driver_expense_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_val2;
						$employer_truck_driver_expense_acct[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_list2;	
						$employer_truck_driver_expense_prices[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_prices2;  
						
						$employer_truck_driver_miles_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
						$employer_truck_driver_miles_rate_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
																	
						$exps_cntr2=0;
						$exps_val2=0;
						$exps_list2="";
						$exps_prices2="";	  						
     				}
     				
               		$employer_truck_driver_hours[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$hour_cntr2;
               		$employer_truck_driver_miles[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$mile_cntr2;
               		$employer_truck_driver_stops[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$stop_cntr2;
               		$employer_truck_driver_bonus[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$bonus_cntr2;
               		$employer_truck_driver_hours_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$hour_val2;
               		               		
					$employer_truck_driver_miles_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$team_cntr2;
					$employer_truck_driver_miles_rate_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$team_val2;
					
               		if($driver_pay_team > 0)
               		{
               			//$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$driver_pay_team;
               			$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$mile_val2;
               		}
               		else
               		{
               			$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$mile_val2;
               		}
               		$employer_truck_driver_stops_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$stop_val2;
               		$employer_truck_driver_bonus_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$bonus_val2;
               		
										
					//$exps_cntr2=0;
					//$exps_val2=0;
					//$exps_list2="";
					//$exps_prices2="";
				}
				else
				{
					if($mrr_driver_found==0)
     				{
     					$employer_truck_driver_ids[ $mrr_use_emp ][$mrr_use_truck][ ( $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck] ) ]  =$row['driver_id'];
     					$employer_truck_driver_emps[ $mrr_use_emp ][$mrr_use_truck][ ( $employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck] ) ]  =$row['driver1_employer_id'];
     					$mrr_use_driver=$employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck];
     					$employer_truck_driver_cntr[ $mrr_use_emp ][$mrr_use_truck]++;
     					
     					//reset counters for this new driver...
     					$employer_truck_driver_names[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$row['name_driver_first']." ". $row['name_driver_last'];
               			$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$single_hr_rate;	//$row['dispatch_charged_per_hour'];	//
               			$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$single_rate;		//$row['dispatch_charged_per_mile'];	//
               			
               			$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$row['dispatch_charged_per_hour'];	//$single_hr_rate;	//
               			$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$row['dispatch_charged_per_mile'];	//$single_rate;		//
               			
               			$employer_truck_driver_hours_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_hr_rate;
						$employer_truck_driver_miles_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_rate;
						
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
             				               		
               			$employer_truck_driver_expenses[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_cntr;
						$employer_truck_driver_expense_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_val;
						$employer_truck_driver_expense_acct[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_list;	
						$employer_truck_driver_expense_prices[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$exps_prices;
												
						$employer_truck_driver_miles_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
						$employer_truck_driver_miles_rate_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=0;
												
						$exps_cntr=0;
						$exps_val=0;
						$exps_list="";
						$exps_prices="";		
     				}
     				
               		$employer_truck_driver_hours[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$hour_cntr;
               		$employer_truck_driver_miles[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$mile_cntr;
               		$employer_truck_driver_stops[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$stop_cntr;
               		$employer_truck_driver_bonus[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$bonus_cntr;
               		$employer_truck_driver_hours_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$hour_val;
               		               		
					$employer_truck_driver_miles_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$team_cntr;
					$employer_truck_driver_miles_rate_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$team_val;
					
               		if($driver_pay_team > 0)
               		{
               			$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$driver_pay_team;
               		}
               		else
               		{
               			$employer_truck_driver_miles_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$mile_val;
               		}
               		$employer_truck_driver_stops_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$stop_val; 
               		$employer_truck_driver_bonus_rate[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]+=$bonus_val;               			
					
				}//end else	
										
			}		
			//-------------------------------------------------------------------------------------	
			
									
			echo $mrr_output;					
			
		}
		show_truck_totals();
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
	</tr>
	<?
		echo "
			<tr>
				<td colspan='3'>Other Expenses</td>
			</tr>
		";		
		echo $mrr_output2;	
	?>
	<tr>
		<td colspan='15'>
			<table style='font-weight:bold'>
			<tr>
				<td nowrap>Total Truck Pay (miles)</td>
				<td align='right'>$<?=money_format('',$total_driver_pay_miles)?></td>
			</tr>
			<tr>
				<td nowrap>Total Truck Pay (hours)</td>
				<td align='right'>$<?=money_format('',$total_hours_charge)?></td>
			</tr> 
			<tr>
				<td colspan='5'><hr></td>
			</tr>
			<tr>
				<td>Driver Pay</td>
				<td align='right'>$<?=money_format('',$total_driver_pay_miles + $total_hours_charge)?></td>
			</tr>
			 <tr>
				<td style='width:100px'>Expenses</td>
				<td style='width:100px' align='right'>$<?=money_format('',$total_expenses)?></td>
			</tr>
			
			<tr>
				<td colspan='5'><hr></td>
			</tr>
			<tr>
				<td>Total</td>
				<td align='right'>$<?=money_format('',$total_driver_pay_miles + $total_hours_charge + $total_expenses)?></td>
			</tr> 
			</table>
		</td>
	</tr>
	</table>
	
	
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
	$mrr_today=date("m/d/Y", time());
	$mrr_days30=date("m/d/Y", strtotime("+1 month", time()));
	$date_ranger=$_POST['date_from']." through ".$_POST['date_to'].""; 
	
	$mrr_split_load_driver_emps=0;	
		
	for($i=0;$i < $empl_counter; $i++)
	{	//print each employer section individually...which is how the bill will get made		
		
		if($employer_used[$i] == 1)
		{
			$my_emp_id=$employers[ $i ];
			$my_emp_name=$employer_names[ $i ];
			$mrr_mask_employer=0;
			/*		
			if($_POST['employer_id'] > 0 && $employers[ $i ]!=$_POST['employer_id'])	
			{
				//$my_emp_id=$_POST['employer_id'];
				//$mrr_mask_employer=$_POST['employer_id'];
				
				$mrr_mask_employer=1;
				
				$sql2="
					select option_values.id as use_val,
                         		option_values.fvalue as use_disp
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
			*/
			
			if($_POST['employer_id'] > 0 && $employers[ $i ]!=$_POST['employer_id'])	
			{
				//$my_emp_id=$_POST['employer_id'];
				//$mrr_mask_employer=$_POST['employer_id'];
				
				//$mrr_mask_employer=1;
				
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
			
			
			$employer_vendor_bridge=mrr_get_last_employer_vendor_bridge($my_emp_id,$_POST['date_from'],$_POST['date_to']);
						
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
				
     		$results=mrr_get_vendor_bill_list($my_emp_name,$_POST['date_from'],$_POST['date_to'],$employer_vendor_bridge);
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
     			
     			//get each driver for this truck          				
     			for($d=0;$d < $employer_truck_driver_cntr[ $i ][$t]; $d++)
     			{
     				//$employer_truck_driver_emps[ $i ][ $t ][ $d ]
     				//$employers[ $i ]
     				//$mrr_mask_employer > 0 && $mrr_mask_employer==$employer_truck_driver_emps[ $i ][ $t ][ $d ]) && $employers[ $i ]!=$mrr_mask_employer  
     				
     				if(  ($mrr_mask_employer==0 && $my_emp_id==$employer_truck_driver_emps[ $i ][ $t ][ $d ]) || 
     					($mrr_mask_employer > 0 && $_POST['employer_id']==$employer_truck_driver_emps[ $i ][ $t ][ $d ]) )
     				{          				
          				//separate team miles
          				$employer_truck_driver_miles[ $i ][ $t ][ $d ] -= $employer_truck_driver_miles_team[ $i ][ $t ][ $d ];
          				//adjust rate and recalculate individual pay
          				$employer_truck_driver_miles_rate[ $i ][ $t ][ $d ] = $employer_truck_driver_miles[ $i ][ $t ][ $d ] * $employer_truck_driver_miles_charge[ $i ][ $t ][ $d ];
          				
          				
          				$note1="".$employer_truck_driver_names[ $i ][ $t ][ $d ]." ".$employer_truck_driver_miles[ $i ][ $t ][ $d ]." miles @ ".$employer_truck_driver_miles_charge[ $i ][ $t ][ $d ]."";
                    		//$employer_truck_driver_hours_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$single_hr_rate2;
                    		//$employer_truck_driver_miles_charge[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$single_rate2;
                    		//$employer_truck_driver_hours_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_hr_rate2;
     					//$employer_truck_driver_miles_charge_team[ $mrr_use_emp ][$mrr_use_truck][$mrr_use_driver]=$tm_rate2;
     						               		
                    		if($employer_truck_driver_miles_rate[ $i ][ $t ][ $d ] != 0)
                    		{
                         		echo "<tr>
               						<td valign='top'>".$search_chart_name."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_miles[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_miles_charge[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>$".number_format($employer_truck_driver_miles_rate[ $i ][ $t ][ $d ],2)." </td>
               						<td valign='top'>".$note1." [[".$employer_truck_driver_emps[ $i ][ $t ][ $d ]."]]</td>
               					</tr>";
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
                         		$tmp_charge="0.000";
                         		if($employer_truck_driver_miles_team[ $i ][ $t ][ $d ]!=0)
                         		{
                         			$tmp_charge=$employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ] / abs($employer_truck_driver_miles_team[ $i ][ $t ][ $d ]);
                         		}
                         		
                         		$note1a="".$employer_truck_driver_names[ $i ][ $t ][ $d ]." ".$employer_truck_driver_miles_team[ $i ][ $t ][ $d ]." team miles @ ".number_format($tmp_charge,3)."";
                         		
                         		echo "<tr>
               						<td valign='top'>".$search_chart_name."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_miles_team[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>".number_format($tmp_charge,2)."</td>
               						<td valign='top' align='right'>$".number_format($employer_truck_driver_miles_rate_team[ $i ][ $t ][ $d ],2)." </td>
               						<td valign='top'>".$note1a." [[".$employer_truck_driver_emps[ $i ][ $t ][ $d ]."]]</td>
               					</tr>";
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
               				echo "<tr>
               						<td valign='top'>".$search_chart_name."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_hours[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_hours_charge[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>$".number_format($employer_truck_driver_hours_rate[ $i ][ $t ][ $d ],2)." </td>
               						<td valign='top'>".$note2." [[".$employer_truck_driver_emps[ $i ][ $t ][ $d ]."]]</td>
               					</tr>";
          					$etot_hours+=$employer_truck_driver_hours[ $i ][ $t ][ $d ];
          					$etot_hour_value+=$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ];
          					
          					$bill_items[ ($bill_header['items']) ]['account_name']=$search_chart_name;
          					$bill_items[ ($bill_header['items']) ]['units']="".$employer_truck_driver_hours[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['rate']="".$employer_truck_driver_hours_charge[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['amount']="".$employer_truck_driver_hours_rate[ $i ][ $t ][ $d ]."";
          					$bill_items[ ($bill_header['items']) ]['memo']=$note2;
          					
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
               				echo "<tr>
               						<td valign='top'>".$search_stop_name."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_stops[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>".number_format($mrr_avg,2)."</td>
               						<td valign='top' align='right'>$".number_format($employer_truck_driver_stops_rate[ $i ][ $t ][ $d ],2)." </td>
               						<td valign='top'>".$note3."</td>
               					</tr>";
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
               				echo "<tr>
               						<td valign='top'>".$search_bonus_name."</td>
               						<td valign='top' align='right'>".number_format($employer_truck_driver_bonus[ $i ][ $t ][ $d ],2)."</td>
               						<td valign='top' align='right'>".number_format($mrr_avg3a,2)."</td>
               						<td valign='top' align='right'>$".number_format($employer_truck_driver_bonus_rate[ $i ][ $t ][ $d ],2)." </td>
               						<td valign='top'>".$note3a."</td>
               					</tr>";
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
               						
               						echo "<tr>
                         						<td valign='top'>".$acct_name[$xx]."</td>
                         						<td valign='top' align='right'>1</td>
                         						<td valign='top' align='right'>1</td>
                         						<td valign='top' align='right'>$".number_format($charger[$xx],2)." </td>
                         						<td valign='top'>".$note4."</td>
                         					</tr>";
                         				
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
     		$add_exp_amnt=0;
     		for($et=0; $et< $employer_extras[ $i ]; $et++)
     		{     			
				$note_et="".$employer_extra_expenses_driver_name[ $i ][ $et ]." - ".$employer_extra_expenses_desc[ $i ][ $et ]."";
				echo "<tr style='background-color:#d0ffdb'>
          				<td valign='top'>".$employer_extra_expenses_acct_name[ $i ][ $et ]."</td>
          				<td valign='top' align='right'>1</td>
          				<td valign='top' align='right'>".number_format($employer_extra_expenses_amnt[ $i ][ $et ],2)."</td>
          				<td valign='top' align='right'>$".number_format($employer_extra_expenses_amnt[ $i ][ $et ],2)." </td>
          				<td valign='top'>".$note_et."</td>
          			</tr>";
     				
     			$etot_exps_value+=$employer_extra_expenses_amnt[ $i ][ $et ];
     			$etot_exps++;
     					
				$bill_items[ ($bill_header['items']) ]['account_name']=$employer_extra_expenses_acct_name[ $i ][ $et ];
				$bill_items[ ($bill_header['items']) ]['units']="1";
				$bill_items[ ($bill_header['items']) ]['rate']="".$employer_extra_expenses_amnt[ $i ][ $et ]."";
				$bill_items[ ($bill_header['items']) ]['amount']="".$employer_extra_expenses_amnt[ $i ][ $et ]."";
				$bill_items[ ($bill_header['items']) ]['memo']=$note_et;
				
				$bill_header['items']++;  	
     		}
     		     		
     		
     		if(($etot_mile_value + $etot_hour_value + $etot_stop_value + $etot_bonus_value + $etot_exps_value)!=0)
     		{
     			echo "
     			<tr>
     				<td colspan='5'><hr></td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Pay-per-Mile</b></td>
     				<td valign='top' align='right'>".$etot_miles."</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format($etot_mile_value,2)." </td>
     				<td valign='top'>&nbsp;</td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Pay-per-Hour</b></td>
     				<td valign='top' align='right'>".$etot_hours."</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format($etot_hour_value,2)." </td>
     				<td valign='top'>&nbsp;</td>
     			</tr>     			
     			<tr>
     				<td colspan='5'><hr></td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Driver Pay</b></td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format(($etot_mile_value + $etot_hour_value),2)." </td>
     				<td valign='top'></td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Expenses</b></td>
     				<td valign='top' align='right'>".($etot_stops+$etot_bonus+$etot_exps)."</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format(($etot_stop_value+$etot_bonus_value+$etot_exps_value),2)."  </td>
     				<td valign='top'>&nbsp;</td>
     			</tr>
     			<tr>
     				<td colspan='5'><hr></td>
     			</tr>
     			<tr>
     				<td valign='top'><b>Total</b></td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format(($etot_mile_value + $etot_hour_value + $etot_stop_value + $etot_bonus_value + $etot_exps_value),2)."</td>
     				<td valign='top' align='right'><input type='submit' name='mrr_employer_".$i."_go_btn' id='mrr_employer_".$i."_go_btn' value='Create this Bill'></td>
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
     					<br><br><br>
     					</center>
     				</td>
     			</tr>
     		";
     		
		}//END EMPLOYER_USED CHECK...
	} //end employer while
	
	?>
	
	</table><input type='hidden' name='employer_row_cntr' id='employer_row_cntr' value='<?= $empl_counter ?>'>
	<?
	$pdf = ob_get_contents();
	ob_end_clean();
	
	echo $pdf;
	?>
<? } ?>
</form>
<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	$('.tablesorter').tablesorter();
</script>
<? include('footer.php') ?>