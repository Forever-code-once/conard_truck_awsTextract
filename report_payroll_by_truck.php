<? $usetitle = "Report - Payroll by Truck" ?>
<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id']; 
		$_POST['build_report'] = 1;
	}

	if(!isset($_POST['date_from'])) $_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) $_POST['date_to'] = date("n/j/Y", time());
	if(!isset($_POST['driver_id'])) $_POST['driver_id'] = 0;
	if(!isset($_POST['employer_id'])) $_POST['employer_id'] = '';
	

	$rfilter = new report_filter();
	$rfilter->show_truck 		= true;
	$rfilter->show_employers 	= true;
	$rfilter->summary_only	 	= true;
	$rfilter->team_choice	 	= false;
	$rfilter->show_font_size			= true;
	$rfilter->show_filter();
	
	$skip_overtime_separation=1;
	
	function get_master_query($driver_id_filter = 0) 
	{
		$employ_adder="";
		if($_POST['employer_id'] > 0)
		{
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
				drivers.id as driver_id,
				drivers.name_driver_first,
				drivers.name_driver_last,
				drivers.employer_id as driver1_employer_id,
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
		//d($sql); 
		$data = simple_query($sql);
		
		return $data;
	}
?>


<? if(isset($_POST['build_report'])) { ?>
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
			<td align='right'>Labor Mile</td>
			<td align='right'>Pay Mile</td>
			<td align='right'>Labor Hour</td>
			<td align='right'>Pay Hour</td>
			<td>Date</td>
			<td>Driver</td>
			<td>Trailer</td>
			<td>Customer</td>
		</tr>	
	";

	$data_master = get_master_query();
		
	ob_start();
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;text-align:left'>
	<tr>
		<td colspan='10'>
			<center>
			<span class='section_heading'>Payroll Report - By Truck</span>
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
		
		$driver_arr_num=0;
		$driver_arr_ids[0]=0;
		$driver_arr_hrs[0]=0;
		
		$max_overtime_hours= (int)$defaultsarray['overtime_hours_min'];
		$def_overtime_pay_rate=1.00;								//likely will be changed to 1.50 (time and a half)
		if(is_numeric($defaultsarray['overtime_def_rate']))
		{
			$def_overtime_pay_rate=$defaultsarray['overtime_def_rate'];
		}

		while($row = mysqli_fetch_array($data_master)) {
			
			$driver1_employer_id=$row['driver1_employer_id'];
			$driver2_employer_id=$row['driver2_employer_id'];
			
			$temp_employer_id=$row['employer_id'];
			$mrr_drivers_switched="";
			$mrr_temp_switch_emp=0;
			
			$mrr_temp_switch_cpermile=$row['charged_per_mile'];
			$mrr_temp_switch_cperhour=$row['charged_per_hour'];
			$mrr_temp_switch_cperteam=$row['charged_per_mile_team'];
			
			$driver_found=-1;
			$driver2_found=-1;
			
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
				
				if($temp_employer_id!=$_POST['employer_id'] && $temp_employer_id==$driver2_employer_id)
				{
					$temp_employer_id=$_POST['employer_id'];
					$row['employer_id']=$_POST['employer_id'];
				}
				
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
								
				
				
				// get the list of driver expenses
				$sql = "
					select drivers_expenses.*,
						option_values.fvalue as expense_type
					
					from drivers_expenses
						left join option_values on option_values.id = drivers_expenses.expense_type_id
					where drivers_expenses.driver_id = '$row[driver_id]'
						and drivers_expenses.deleted = 0
						and drivers_expenses.payroll = 1
						and drivers_expenses.linedate between '".date("Y-m-d", strtotime($_POST['date_from']))."' and '".date("Y-m-d", strtotime($_POST['date_to']))."'
					order by linedate
				";
				$data_driver_expenses = simple_query($sql);
				

				
				echo "
					
					<tr>
						<td colspan='3'>Truck <a href='admin_drivers.php?id=$row[driver_id]' target='manage_driver_$row[driver_id]'>$current_truck_name</a></td>
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
     						echo "
     							<tr style='background-color:#d0ffdb'>
     								<td colspan='2'>".$account."</td>
     								<td>$row_driver_expenses[expense_type]</td>
     								<td align='right'>$".money_format('',$row_driver_expenses['amount_billable'])."</td>
     								<td></td>
     								<td colspan='14'>".date("m-d-Y", strtotime($row_driver_expenses['linedate']))." - $row_driver_expenses[desc_long]</td>
     							</tr>
     						";
     					}
					}
				}
				
				
     				
     				//add driver vacation and cash advances here...Added April 2014...................................
     				$use_driver_merger="and driver_vacation_advances_dates.driver_id = '$row[driver_id]'";
     				if($row['driver2_id'] > 0)
     				{
     					//$use_driver_merger="and (driver_vacation_advances_dates.driver_id = '$row[driver_id]' or driver_vacation_advances_dates.driver_id = '$row[driver2_id]')";	
     				}
     				$sql = "
     					select driver_vacation_advances_dates.*,
     						driver_vacation_advances.comments
     					
     					from driver_vacation_advances_dates
     						left join driver_vacation_advances on driver_vacation_advances.id=driver_vacation_advances_dates.dva_id
     					where driver_vacation_advances_dates.deleted = 0
     						and driver_vacation_advances.deleted = 0
     						".$use_driver_merger."
     						and driver_vacation_advances_dates.linedate between '".date("Y-m-d", strtotime($_POST['date_from']))."' and '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
     					order by driver_vacation_advances_dates.linedate
     				";
     				$data_vacations = simple_query($sql);   				
     				
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
     								<td colspan='14'>".date("m-d-Y", strtotime($row_vacations['linedate']))." - ".$row_vacations['comments']."</td>
     							</tr>
     						";
     					}
     				}
     				//................................................................................................
     				/**/
				
			}
			$counter++;
			$this_driver_count++;
			
			/*
				if($row['driver_id'] > 0 && $row['hours_worked'] > 0)
				{
					$driver_found=-1;
					for($d=0; $d < $driver_arr_num; $d++)
					{
						if($driver_arr_ids[$d] == $row['driver_id'])		$driver_found=$d;
					}	
					
					if($driver_found >=0)
					{
						$driver_arr_hrs[ $driver_found ] += $row['hours_worked'];	
					}
					else
					{						
						$driver_arr_ids[ $driver_arr_num ]=$row['driver_id'];
						$driver_arr_hrs[ $driver_arr_num ]=$row['hours_worked'];	
						$driver_arr_num++;
					}
				}
				if($row['driver2_id'] > 0 && $row['hours_worked'] > 0)
				{
					$driver2_found=-1;
					for($d=0; $d < $driver_arr_num; $d++)
					{
						if($driver_arr_ids[$d] == $row['driver2_id'])		$driver2_found=$d;
					}	
					
					if($driver2_found >=0)
					{
						$driver_arr_hrs[ $driver2_found ] += $row['hours_worked'];	
					}
					else
					{						
						$driver_arr_ids[ $driver_arr_num ]=$row['driver2_id'];
						$driver_arr_hrs[ $driver_arr_num ]=$row['hours_worked'];	
						$driver_arr_num++;
					}
				}		
			*/
			
			//NEW calculation for pay based on settings from the dispatch for labor...not the general driver settings, which may have changed since then... Added May 2013
			$mrr_per_hour_labor=$row['labor_per_hour'];
			$mrr_per_mile_labor=$row['labor_per_mile'];
			
     		if($row['driver2_id'] > 0 && $_POST['employer_id'] > 0)
     		{
     			$mrr_per_hour_labor=$mrr_temp_switch_cperhour;	//
				$mrr_per_mile_labor=$mrr_temp_switch_cperteam;	//
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
     		
     		//.............................................
     			if($row['driver_id'] > 0)
				{
					$driver_found=0;
					for($dv=0; $dv < $driver_arr_num; $dv++)
					{
						if($driver_arr_ids[$dv] == $row['driver_id'])
						{
							$driver_found=1;
							$driver_arr_hrs[ $dv ] += $row['hours_worked'];	
						}
					}					
					if($driver_found ==0)
					{
						$driver_arr_ids[ $driver_arr_num ]=$row['driver_id'];
						$driver_arr_hrs[ $driver_arr_num ]=$row['hours_worked'];	
						$driver_arr_num++;
					}
				}
     		//............................................
     		     			
			$mrr_tot_per_mile+=(($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor);
			$mrr_tot_per_hour+=$tmp_hourly_pay;			//($row['hours_worked'] *$mrr_per_hour_labor);
			
			$mrr_driver_pay_miles += (($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor);
			$mrr_driver_pay_hours += $tmp_hourly_pay;		//($row['hours_worked'] *$mrr_per_hour_labor);
			
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
				
				//$driver_pay += $row['hours_worked'] * $mrr_per_hour_labor;		//$row['dispatch_charged_per_hour']
				//$driver_pay_hours += $row['hours_worked'] * $mrr_per_hour_labor;	//$row['dispatch_charged_per_hour']
				$driver_hourly_miles += $row['miles'];
				$driver_hourly_miles_deadhead += $row['miles_deadhead'];	
				
				if($line_miles>0)
				{
     				if($row['driver2_id'] > 0) {
     					$line_pay_miles = $line_miles * ($mrr_per_mile_labor);
     					$driver_pay_team += $line_pay_miles;
     					//echo "<tr><td>$line_miles | $line_pay_miles</td></tr>";
     				} else {
     					$driver_pay += $line_miles * $mrr_per_mile_labor;		//$row['dispatch_charged_per_mile']
     					$driver_pay_miles += $line_miles * $mrr_per_mile_labor;	//$row['dispatch_charged_per_mile']
     					//echo "<tr><td>$line_miles | ".($line_miles * $row['dispatch_charged_per_mile'])."</td></tr>";
     				}
     				
     				$total_mile_charge += ($line_miles * $mrr_per_mile_labor);	//$row['dispatch_charged_per_mile']
     				$driver_miles += $row['miles'];
				}				
				
			} else {
				if($row['driver2_id'] > 0) {
					$line_pay_miles = $line_miles * ($mrr_per_mile_labor);
					$driver_pay_team += $line_pay_miles;
					//echo "<tr><td>$line_miles | $line_pay_miles</td></tr>";
				} else {
					$driver_pay += $line_miles * $mrr_per_mile_labor;		//$row['dispatch_charged_per_mile']
					$driver_pay_miles += $line_miles * $mrr_per_mile_labor;	//$row['dispatch_charged_per_mile']
					//echo "<tr><td>$line_miles | ".($line_miles * $row['dispatch_charged_per_mile'])."</td></tr>";
				}
				
				$total_mile_charge += ($line_miles * $mrr_per_mile_labor);	//$row['dispatch_charged_per_mile']
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
					and trucks_log.truck_id = '".sql_friendly($current_truck)."'
					and trucks_log.id = $row[id]
					and dispatch_expenses.deleted = 0
			";
			$data_expenses = simple_query($sql);
			
			$hrs_wrked=0;
			if($driver_found >=0)		$hrs_wrked=$driver_arr_hrs[ $driver_found ];
						
			if(!isset($_POST['summary_only'])) 
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
						<td align='right'>".(number_format($row['hours_worked']) == $row['hours_worked'] ? number_format($row['hours_worked']) : $row['hours_worked'])."</td>
						
						<td align='right'>$".number_format($mrr_per_mile_labor,2)."</td>
						<td align='right'>$".number_format((($row['miles_deadhead'] + $row['miles']) * $mrr_per_mile_labor),2)."</td>
						<td align='right'>$".number_format($mrr_per_hour_labor,2)."</td>
						<td align='right'>$".number_format($tmp_hourly_pay,2)."</td>
						
						<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
						<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
						<td nowrap>$row[trailer_name]</td>
						<td nowrap>$row[name_company]</td>
					</tr>
				";									//($row['hours_worked'] *$mrr_per_hour_labor ) (".$hrs_wrked.") [".$driver_found."]
				if($row['driver2_id'] > 0) {
					echo "
						<tr class='".($this_driver_count % 2 == 1 ? 'odd' : 'even')."' style='background-color:#ebc8c8'>
							<td colspan='2'>&nbsp;</td>
							<td>Team Run</td>
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
          			    				
     				if(!isset($_POST['summary_only'])) {
     					echo "
     						<tr style='background-color:#d0ffdb'>
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

		}
		show_truck_totals();
		
		
		/*
		//---------------------------------------------------------------------NOW SHOW EXPENSES FOR OTHER DRIVERS NOT ON THE LOAD LIST------------------------------------------------------------------------------
		
		$mrr_cntr=0;
		$sql = "
			select drivers.*			
			from drivers
			where deleted=0
			".($_POST['driver_id'] ? " and id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
			".($_POST['employer_id'] != 0 ? " and employer_id = '".sql_friendly($_POST['employer_id'])."'" : '') ."
			order by name_driver_last, name_driver_first
		";
		
		$data_drivers = simple_query($sql);
		while($row = mysqli_fetch_array($data_drivers)) {
			
			$mrr_found_driver=0;
			for($i=0;$i < $mrr_all_drivers; $i++)
			{
				if(	$mrr_all_drivers_arr[ $i ] == $row['id'] )		$mrr_found_driver=1;
			}
			if($mrr_found_driver==0)
			{
				$mrr_all_drivers_arr[ $mrr_all_drivers ]=$row['id'];
				$mrr_all_drivers++;	
				
				$mrr_temp="";
			
				if($current_driver != '') {
					//show_driver_totals($driver_miles, $driver_miles_deadhead, $driver_hours_worked, $driver_per_mile, $driver_per_hour);
					if($mrr_cntr > 0 && $driver_expenses > 0)	
					{
						show_driver_totals();
						echo "<tr><td colspan='20'><hr></td></tr>";
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
						and drivers_expenses.linedate between '".date("Y-m-d", strtotime($_POST['date_from']))."' and '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
					order by linedate
				";
				//echo $sql."<br>";
				
				$data_driver_expenses = simple_query($sql);
								
				$mrr_temp.= "
					<tr>
						<td colspan='8'><br>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='3'>Driver: <a href='admin_drivers.php?id=$row[driver_id]' target='manage_driver_$row[id]'>$current_driver_name</a></td>
						<td align='right' colspan='2' nowrap><span style='font-weight:bold; color:#AA0000;'>Expenses Only</span></td>
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
     								<td colspan='10'>".date("m-d-Y", strtotime($row_driver_expenses['linedate']))." - $row_driver_expenses[desc_long]</td>
     							</tr>
     						";
     					}
					}
				}
				
			}
			$counter++;
			$this_driver_count++;
						
			// get any driver expenses (tied to dispatches)
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
     							<td colspan='10'>$row_expense[expense_desc]</td>
     						</tr>
     					";
     				}
				}
			}
               if($driver_expenses != 0)
               {
               	echo $mrr_temp;
               }
		}
		if($total_expenses != 0)
          {
             	//show_driver_totals();
          }
		*/
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
	<!---
	<tr style='font-weight:bold'>
		<td align='right' colspan='7'>$<?=money_format('',$total_mile_charge)?></td>
		<td align='right' colspan='1'>$<?=money_format('',$total_hours_charge)?></td>
	</tr>
	--->
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
				<td style='width:100px'>Driver Pay</td>
				<td style='width:100px' align='right'>$<?=money_format('',$total_driver_pay_miles + $total_hours_charge)?></td>
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
	<?
	$pdf = ob_get_contents();
	ob_end_clean();
	
	echo $pdf;
	?>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
</script>
<? include('footer.php') ?>