<? include('application.php') ?>
<?
	$mrr_debug_time_start=date("His");	//for page load speed checks...used at bottom of the page.
	
	
	if(isset($_GET['load_id'])) $_POST['load_id'] = $_GET['load_id'];
	if(!isset($_POST['load_id'])) $_POST['load_id'] = 0;
	
	$auto_update_pn=0;
	if(isset($_GET['update_pn_dispatches']))	$auto_update_pn=1;
	
	$mrr_mledit=0;		//Master Load Edito only, no dispatch sent via PN or other special things...just saving data.
	
	if(isset($_GET['master_load_edit']))		$mrr_mledit=1;
	
	mrr_check_drivers_for_loads();
	
	$pn_auto_dispatch_link="";
	
	$mrr_load_id=$_POST['load_id'];
	
	//default settings used for budget items
	$mrr_average_mpg=mrr_get_default_variable_setting('average_mpg');
     $mrr_billable_days_in_month=mrr_get_default_variable_setting('billable_days_in_month');
     $mrr_labor_per_hour=mrr_get_default_variable_setting('labor_per_hour');
     $mrr_labor_per_mile=mrr_get_default_variable_setting('labor_per_mile');
     $mrr_labor_per_mile_team=mrr_get_default_variable_setting('labor_per_mile_team');
     $mrr_local_driver_workweek_hours=mrr_get_default_variable_setting('local_driver_workweek_hours');
     $mrr_tractor_maint_per_mile=mrr_get_default_variable_setting('tractor_maint_per_mile');
     $mrr_trailer_maint_per_mile=mrr_get_default_variable_setting('trailer_maint_per_mile');
     
     $mrr_truck_accidents_per_mile=mrr_get_default_variable_setting('truck_accidents_per_mile');
	$mrr_tires_per_mile=mrr_get_default_variable_setting('tires_per_mile');
	$mrr_mileage_expense_per_mile=mrr_get_default_variable_setting('mileage_expense_per_mile');
	$mrr_misc_expense_per_mile=mrr_get_default_variable_setting('misc_expense_per_mile');
	
	$mrr_trailer_mile_exp_per_mile=mrr_get_default_variable_setting('trailer_mile_exp_per_mile');
	
	$mrr_cargo_insurance=mrr_get_option_variable_settings('Cargo Insurance');
     $mrr_general_liability=mrr_get_option_variable_settings('General Liability');
     $mrr_liability_phy_damage=mrr_get_option_variable_settings('Liability/Phy Damage');
     $mrr_payroll___admin=mrr_get_option_variable_settings('Payroll & Admin');
     $mrr_rent=mrr_get_option_variable_settings('Rent');
     $mrr_tractor_lease=mrr_get_option_variable_settings('Tractor Lease');
     $mrr_trailer_expense=mrr_get_option_variable_settings('Trailer Expense');
     $mrr_trailer_lease=mrr_get_option_variable_settings('Trailer Lease');
     $mrr_misc_expenses=mrr_get_option_variable_settings('Misc Expenses');
			
	if(isset($_GET['dup_id'])) 
	{
		$sql = "
			select *
			
			from load_handler
			where id = '".sql_friendly($_GET['dup_id'])."'
		";
		$data_dup = simple_query($sql);
		$row_dup = mysqli_fetch_array($data_dup);
		
		$mrr_activity_log_notes.="Duplicate Load ".$_GET['dup_id']." info. ";
					
		$sql = "
			insert into load_handler
				(linedate_added,
				created_by_id,
				customer_id,
				special_instructions,
				estimated_miles,
				deadhead_miles,
				deleted,
				quote,
				fuel_charge_per_mile,
				rate_unloading,
				rate_stepoff,
				rate_misc,
				rate_fuel_surcharge_per_mile,
				rate_fuel_surcharge_total,
				rate_base,
				rate_lumper,
				rate_fuel_surcharge,
				actual_rate_fuel_surcharge,
				actual_bill_customer,
				days_run_otr,
				days_run_hourly,
				loaded_miles_hourly,
				hours_worked,
				update_fuel_surcharge,
				master_load,
          		budget_average_mpg,
				budget_days_in_month,
				budget_labor_per_hour,
				budget_labor_per_mile,
				budget_labor_per_mile_team,
				budget_driver_week_hours,
				budget_tractor_maint_per_mile,
				budget_trailer_maint_per_mile,
				budget_truck_accidents_per_mile,
				budget_tires_per_mile,
				budget_mileage_exp_per_mile,
				budget_misc_exp_per_mile,
				budget_cargo_insurance,
				budget_general_liability,
				budget_liability_damage,
				budget_payroll_admin,
				budget_rent,
				budget_tractor_lease,
				budget_trailer_exp,
				budget_trailer_lease,
				budget_misc_exp,
				budget_active_trucks,
				budget_active_trailers,
				budget_day_variance,
				dedicated_load,
				load_number,
				pickup_number,
				delivery_number,
				billing_notes,
				driver_notes,
				master_load_label,
				preplan_driver2_id,
				preplan_leg2_driver_id,
				preplan_leg2_driver2_id,
				preplan_leg2_stop_id)
				
			values (now(),
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($row_dup['customer_id'])."',
				'',
				'".sql_friendly($row_dup['estimated_miles'])."',
				'".sql_friendly($row_dup['deadhead_miles'])."',
				'0',
				'".sql_friendly($row_dup['quote'])."',
				'".sql_friendly($row_dup['fuel_charge_per_mile'])."',
				'".sql_friendly($row_dup['rate_unloading'])."',
				'".sql_friendly($row_dup['rate_stepoff'])."',
				'".sql_friendly($row_dup['rate_misc'])."',
				'".sql_friendly($row_dup['rate_fuel_surcharge_per_mile'])."',
				'".sql_friendly($row_dup['rate_fuel_surcharge_total'])."',
				'".sql_friendly($row_dup['rate_base'])."',
				'".sql_friendly($row_dup['rate_lumper'])."',
				'".sql_friendly($row_dup['rate_fuel_surcharge'])."',
				'".sql_friendly($defaultsarray['fuel_surcharge'])."',
				'".sql_friendly($row_dup['actual_bill_customer'])."',
				'".sql_friendly($row_dup['days_run_otr'])."',
				'".sql_friendly($row_dup['days_run_hourly'])."',
				'".sql_friendly($row_dup['loaded_miles_hourly'])."',
				'".sql_friendly($row_dup['hours_worked'])."',
				'".sql_friendly($row_dup['update_fuel_surcharge'])."',
				'0',
				'".sql_friendly($mrr_average_mpg)."',
				'".sql_friendly($mrr_billable_days_in_month)."',
				'".sql_friendly($mrr_labor_per_hour)."',
				'".sql_friendly($mrr_labor_per_mile)."',	
				'".sql_friendly($mrr_labor_per_mile_team)."',
				'".sql_friendly($mrr_local_driver_workweek_hours)."',	
				'".sql_friendly($mrr_tractor_maint_per_mile)."',
				'".sql_friendly($mrr_trailer_maint_per_mile)."',	
				'".sql_friendly($mrr_truck_accidents_per_mile)."',
				'".sql_friendly($mrr_tires_per_mile)."',	
				'".sql_friendly($mrr_mileage_expense_per_mile)."',
				'".sql_friendly($mrr_misc_expense_per_mile)."',	
				'".sql_friendly($mrr_cargo_insurance)."',
				'".sql_friendly($mrr_general_liability)."',	
				'".sql_friendly($mrr_liability_phy_damage)."',
				'".sql_friendly($mrr_payroll___admin)."',	
				'".sql_friendly($mrr_rent)."',
				'".sql_friendly($mrr_tractor_lease)."',	
				'".sql_friendly($mrr_trailer_expense)."',
				'".sql_friendly($mrr_trailer_lease)."',	
				'".sql_friendly($mrr_misc_expenses)."',				
				'".sql_friendly( get_active_truck_count() )."',
				'".sql_friendly( get_active_trailer_count() )."',
				'".sql_friendly( get_daily_cost(0,0) )."',							
				'".sql_friendly($row_dup['dedicated_load'])."',
				'',
				'',
				'',
				'',
				'',
				'',
				0,
				0,
				0,
				0)
		";
		simple_query($sql);
		
		$new_load_id = mysqli_insert_id($datasource);		
		
		$mrr_activity_log_notes.=" New Load is ".$new_load_id.". ";
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$new_load_id,0,0,"Duplicated Load ".$_GET['dup_id']." to ".$new_load_id." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		
		// get a list of the stops, and duplicate those
		$sql = "
			select *
			
			from load_handler_stops
			where load_handler_id = '".sql_friendly($_GET['dup_id'])."'
				and deleted = 0
			order by linedate_pickup_eta
		";
		$data_dup_stops = simple_query($sql);
		
		while($row_dup_stop = mysqli_fetch_array($data_dup_stops)) 
		{
			$sql = "
				insert into load_handler_stops
					(load_handler_id,
					trucks_log_id,
					shipper_name,
					shipper_address1,
					shipper_address2,
					shipper_city,
					shipper_state,
					shipper_zip,
					deleted,
					linedate_added,
					created_by_user_id,
					stop_type_id,
					stop_phone,
					directions)
					
				values ('".sql_friendly($new_load_id)."',
					0,
					'".sql_friendly($row_dup_stop['shipper_name'])."',
					'".sql_friendly($row_dup_stop['shipper_address1'])."',
					'".sql_friendly($row_dup_stop['shipper_address2'])."',
					'".sql_friendly($row_dup_stop['shipper_city'])."',
					'".sql_friendly($row_dup_stop['shipper_state'])."',
					'".sql_friendly($row_dup_stop['shipper_zip'])."',
					0,
					now(),
					'".sql_friendly($_SESSION['user_id'])."',
					'".sql_friendly($row_dup_stop['stop_type_id'])."',
					'".sql_friendly($row_dup_stop['stop_phone'])."',
					'')
			";
			simple_query($sql);
			
		}
		
		// duplicate the variable expenses (quote wise)
		$sql = "
			select *
			
			from load_handler_quote_var_exp
			where load_handler_id = '".sql_friendly($_GET['dup_id'])."'
		";
		$data_var_exp = simple_query($sql);
		
		while($row_var_exp = mysqli_fetch_array($data_var_exp)) {
			$sql = "
				insert into load_handler_quote_var_exp
					(load_handler_id,
					expense_type_id,
					expense_amount)
					
				values ('".sql_friendly($new_load_id)."',
					'".sql_friendly($row_var_exp['expense_type_id'])."',
					'".sql_friendly($row_var_exp['expense_amount'])."')
			";
			simple_query($sql);
		}
		
		update_origin_dest($new_load_id);
		
		header("Location: manage_load.php?load_id=".$new_load_id);
		die;		
	}
	
	if(isset($_GET['create_from_dispatch_id'])) {
		// create a load handler from a dispatch
		
		// get the raw information fromt the dispatch
		$sql = "
			select *
			
			from trucks_log
			where id = '".sql_friendly($_GET['create_from_dispatch_id'])."'
		";
		$data_dispatch = simple_query($sql);
		$row_dispatch = mysqli_fetch_array($data_dispatch);
		
		$mrr_activity_log_notes.="Create Load from Dispatch ".$_GET['create_from_dispatch_id'].". ";
		
		//get current pay rate for now...regardless of when source load was made
          $mrr_labor_mile="0.000";
          $mrr_labor_hour="0.000";
          if($row_dispatch['driver2_id'] > 0)
          {
          	$mrr_labor_mile=mrr_get_driver_pay_rate($row_dispatch['driver2_id'],6);
          	$mrr_labor_hour=mrr_get_driver_pay_rate($row_dispatch['driver2_id'],7);	
          }
				
		// create the load handler
		$sql = "
			insert into load_handler
				(linedate_added,
					created_by_id,
					customer_id,
					origin_city,
					origin_state,
					dest_city,
					dest_state,
					estimated_miles,
					budget_average_mpg,
     				budget_days_in_month,
     				budget_labor_per_hour,
     				budget_labor_per_mile,
     				budget_labor_per_mile_team,
     				budget_driver_week_hours,
     				budget_tractor_maint_per_mile,
     				budget_trailer_maint_per_mile,
     				budget_truck_accidents_per_mile,
     				budget_tires_per_mile,
     				budget_mileage_exp_per_mile,
     				budget_misc_exp_per_mile,
     				budget_cargo_insurance,
     				budget_general_liability,
     				budget_liability_damage,
     				budget_payroll_admin,
     				budget_rent,
     				budget_tractor_lease,
     				budget_trailer_exp,
     				budget_trailer_lease,
     				budget_misc_exp,
     				budget_active_trucks,
					budget_active_trailers,
					budget_day_variance,
					deadhead_miles,
					pickup_number,
					delivery_number)
					
				values (now(),
					'".$_SESSION['user_id']."',
					'$row_dispatch[customer_id]',
					'".sql_friendly($row_dispatch['origin'])."',
					'".sql_friendly($row_dispatch['origin_state'])."',
					'".sql_friendly($row_dispatch['destination'])."',
					'".sql_friendly($row_dispatch['destination_state'])."',
					'".sql_friendly($row_dispatch['miles'])."',
					'".sql_friendly($mrr_average_mpg)."',
     				'".sql_friendly($mrr_billable_days_in_month)."',
     				'".sql_friendly($mrr_labor_per_hour)."',
     				'".sql_friendly($mrr_labor_per_mile)."',	
     				'".sql_friendly($mrr_labor_per_mile_team)."',
     				'".sql_friendly($mrr_local_driver_workweek_hours)."',	
     				'".sql_friendly($mrr_tractor_maint_per_mile)."',
     				'".sql_friendly($mrr_trailer_maint_per_mile)."',	
     				'".sql_friendly($mrr_truck_accidents_per_mile)."',
     				'".sql_friendly($mrr_tires_per_mile)."',	
     				'".sql_friendly($mrr_mileage_expense_per_mile)."',
     				'".sql_friendly($mrr_misc_expense_per_mile)."',	
     				'".sql_friendly($mrr_cargo_insurance)."',
     				'".sql_friendly($mrr_general_liability)."',	
     				'".sql_friendly($mrr_liability_phy_damage)."',
     				'".sql_friendly($mrr_payroll___admin)."',	
     				'".sql_friendly($mrr_rent)."',
     				'".sql_friendly($mrr_tractor_lease)."',	
     				'".sql_friendly($mrr_trailer_expense)."',
     				'".sql_friendly($mrr_trailer_lease)."',	
     				'".sql_friendly($mrr_misc_expenses)."',
     				'".sql_friendly( get_active_truck_count() )."',
					'".sql_friendly( get_active_trailer_count() )."',
					'".sql_friendly( get_daily_cost(0,0) )."',	
					'".sql_friendly($row_dispatch['miles_deadhead'])."',
					'',
					'')
		";
		simple_query($sql);
		
		$load_handler_id = mysqli_insert_id($datasource);
		$mrr_activity_log_notes.="New Load from Dispatch  is ".$load_handler_id.". ";
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$load_handler_id,0,0,"New Load created from Dispatch is ".$load_handler_id." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		// create the dispatch
		$sql = "
			insert into trucks_log
				(truck_id,
				trailer_id,
				driver_id,
				customer_id,
				location,
				linedate_added,
				user_id,
				linedate,
				color,
				origin,
				origin_state,
				destination,
				destination_state,
				miles,
				miles_deadhead,
				load_handler_id,
				driver2_id,
				tires_per_mile,
				accidents_per_mile,
				mile_exp_per_mile,
				misc_per_mile,
				trailer_exp_per_mile)
				
			select
					truck_id,
					trailer_id,
					driver_id,
					customer_id,
					location,
					now(),
					'$_SESSION[user_id]',
					linedate,
					color,
					origin,
					origin_state,
					destination,
					destination_state,
					miles,
					miles_deadhead,
					'$load_handler_id',
					driver2_id,
					tires_per_mile,
					accidents_per_mile,
					mile_exp_per_mile,
					misc_per_mile,
					trailer_exp_per_mile
					
				from trucks_log where id = '".sql_friendly($_GET['create_from_dispatch_id'])."'		
		";
		simple_query($sql);
		
		$sql="
			update trucks_log set
				driver_2_labor_per_mile='".sql_friendly($mrr_labor_mile)."',
				driver_2_labor_per_hour='".sql_friendly($mrr_labor_hour)."'
			where load_handler_id='".sql_friendly($load_handler_id)."'
		";
		simple_query($sql);
		
		
		javascript_redirect('manage_load.php?load_id='.$load_handler_id);
		
		die;
	}
	
	if(isset($_GET['delid'])) {
		// delete the load handler (and all dispatches associated with it)
		$sql = "
			update load_handler
			set deleted = 1
			where id = '".sql_friendly($_GET['delid'])."'
		";
		simple_query($sql);
		
		$sql = "
			update trucks_log
			set deleted = 1
			where load_handler_id = '".sql_friendly($_GET['delid'])."'
		";
		simple_query($sql);
		
		$sql = "
			update ".mrr_find_log_database_name()."geofence_hot_load_tracking
			set deleted = 1
			where load_id = '".sql_friendly($_GET['delid'])."'
		";
		simple_query($sql);
		
		$mrr_activity_log_notes.="Deleted Load ".$_GET['delid'].". ";
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$_GET['delid'],0,0,"Deleted Load ".$_GET['delid']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
				
		javascript_redirect("manage_load.php?deldone=".$_GET['delid']);
	}
	
	if(isset($_POST['customer_name']) && $_POST['customer_name'] != '') {
		$sql = "
			insert into customers
				(name_company,
				contact_email,
				phone_work,
				address1,
				address2,
				city,
				state,
				zip,
				active,
				deleted)
				
			values ('".sql_friendly($_POST['customer_name'])."',
				'".sql_friendly($_POST['customer_email'])."',
				'".sql_friendly($_POST['customer_phone'])."',
				'".sql_friendly($_POST['customer_address1'])."',
				'".sql_friendly($_POST['customer_address2'])."',
				'".sql_friendly($_POST['customer_city'])."',
				'".sql_friendly($_POST['customer_state'])."',
				'".sql_friendly($_POST['customer_zip'])."',
				1,
				0)
		";
		simple_query($sql);
		
		$_POST['customer_id'] =mysqli_insert_id($datasource);
		
		$mrr_activity_log_notes.="Customer added is ".$_POST['customer_id'].". ";
		
		mrr_add_user_change_log($_SESSION['user_id'],$_POST['customer_id'],0,0,0,0,0,0,"Customer ".$_POST['customer_id']." from load.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		
		if($defaultsarray['sicap_integration'] == 1) sicap_update_customers($_GET['eid']);
	}
	
	if(isset($_POST['customer_id']) && $_POST['customer_id'] > 0) {
		$extra_param = "";
		if($_POST['load_id'] == 0) {
			$sql = "
				insert into load_handler
					(linedate_added,
					budget_average_mpg,
     				budget_days_in_month,
     				budget_labor_per_hour,
     				budget_labor_per_mile,
     				budget_labor_per_mile_team,
     				budget_driver_week_hours,
     				budget_tractor_maint_per_mile,
     				budget_trailer_maint_per_mile,
     				budget_truck_accidents_per_mile,
     				budget_tires_per_mile,
     				budget_mileage_exp_per_mile,
     				budget_misc_exp_per_mile,
     				budget_cargo_insurance,
     				budget_general_liability,
     				budget_liability_damage,
     				budget_payroll_admin,
     				budget_rent,
     				budget_tractor_lease,
     				budget_trailer_exp,
     				budget_trailer_lease,
     				budget_misc_exp,
     				budget_active_trucks,
					budget_active_trailers,
					budget_day_variance,
					created_by_id,
					pickup_number,
					delivery_number,
					billing_notes,
					driver_notes)
					
				values (now(),
     				'".sql_friendly($mrr_average_mpg)."',
     				'".sql_friendly($mrr_billable_days_in_month)."',
     				'".sql_friendly($mrr_labor_per_hour)."',
     				'".sql_friendly($mrr_labor_per_mile)."',	
     				'".sql_friendly($mrr_labor_per_mile_team)."',
     				'".sql_friendly($mrr_local_driver_workweek_hours)."',	
     				'".sql_friendly($mrr_tractor_maint_per_mile)."',
     				'".sql_friendly($mrr_trailer_maint_per_mile)."',	
     				'".sql_friendly($mrr_truck_accidents_per_mile)."',
     				'".sql_friendly($mrr_tires_per_mile)."',	
     				'".sql_friendly($mrr_mileage_expense_per_mile)."',
     				'".sql_friendly($mrr_misc_expense_per_mile)."',	
     				'".sql_friendly($mrr_cargo_insurance)."',
     				'".sql_friendly($mrr_general_liability)."',	
     				'".sql_friendly($mrr_liability_phy_damage)."',
     				'".sql_friendly($mrr_payroll___admin)."',	
     				'".sql_friendly($mrr_rent)."',
     				'".sql_friendly($mrr_tractor_lease)."',	
     				'".sql_friendly($mrr_trailer_expense)."',
     				'".sql_friendly($mrr_trailer_lease)."',	
     				'".sql_friendly($mrr_misc_expenses)."',
     				'".sql_friendly( get_active_truck_count() )."',
					'".sql_friendly( get_active_trailer_count() )."',
					'".sql_friendly( get_daily_cost(0,0) )."',	
					'".sql_friendly($_SESSION['user_id'])."',
					'',
					'',
					'',
					'')
			";
			simple_query($sql);
			$edit_id = mysqli_insert_id($datasource);
			
			$mrr_activity_log_notes.="View New Load ".$edit_id.". ";
			
			mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$edit_id,0,0,"New Load is ".$edit_id." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
			
			if($_POST['add_dispatch_after_submit']) $extra_param = "&add_dispatch=1";
		} else {
			$edit_id = $_POST['load_id'];
			$mrr_activity_log_notes.="View Existing Load ".$edit_id.". ";
		}
		
		if($_POST['pickup_eta_time'] != '') 					$_POST['pickup_eta'] .= $_POST['pickup_eta_time'];
		if($_POST['pickup_pta_time'] != '') 					$_POST['pickup_pta'] .= $_POST['pickup_pta_time'];
		
		if(!isset($_POST['appointment_window']))				$_POST['appointment_window']=0;
		
		if($_POST['linedate_appt_window_start_time'] != '') 		$_POST['linedate_appt_window_start'] .= $_POST['linedate_appt_window_start_time'];
		if($_POST['linedate_appt_window_end_time'] != '') 		$_POST['linedate_appt_window_end'] .= $_POST['linedate_appt_window_end_time'];
		
		
		if(!isset($_POST['flat_fuel_rate_amount']))		$_POST['flat_fuel_rate_amount']=0;
		
		if($_SERVER['REMOTE_ADDR'] == '50.76.161.186') {
			//echo $_POST['actual_bill_customer'];
			//die('hit');
		}
		
		/*
		if($_POST['dropoff_eta_time'] != '') $_POST['dropoff_eta'] .= $_POST['dropoff_eta_time'];
		if($_POST['dropoff_pta_time'] != '') $_POST['dropoff_pta'] .= $_POST['dropoff_pta_time'];
		
		
				origin_address1 = '".sql_friendly($_POST['origin_address1'])."',
				origin_address2 = '".sql_friendly($_POST['origin_address2'])."',
				origin_city = '".sql_friendly($_POST['origin_city'])."',
				origin_state = '".sql_friendly($_POST['origin_state'])."',
				origin_zip = '".sql_friendly($_POST['origin_zip'])."',
				dest_address1 = '".sql_friendly($_POST['dest_address1'])."',
				dest_address2 = '".sql_friendly($_POST['dest_address2'])."',
				dest_city = '".sql_friendly($_POST['dest_city'])."',
				dest_state = '".sql_friendly($_POST['dest_state'])."',
				dest_zip = '".sql_friendly($_POST['dest_zip'])."',
				shipper = '".sql_friendly($_POST['shipper'])."',
				consignee = '".sql_friendly($_POST['consignee'])."',
				linedate_pickup_eta = '".date("Y-m-d H:i:s", strtotime($_POST['pickup_eta']))."',
				linedate_pickup_pta = '".date("Y-m-d H:i:s", strtotime($_POST['pickup_pta']))."',
				linedate_dropoff_eta = '".date("Y-m-d H:i:s", strtotime($_POST['dropoff_eta']))."',
				linedate_dropoff_pta = '".date("Y-m-d H:i:s", strtotime($_POST['dropoff_pta']))."',
		
				quote = '".($_POST['quote'] == '' ? '0' : sql_friendly(money_strip(money_strip($_POST['quote']))))."',
				
				rate_unloading = '".($_POST['rate_unloading'] == '' ? '0' : sql_friendly(money_strip($_POST['rate_unloading'])))."',
				rate_stepoff = '".($_POST['rate_stepoff'] == '' ? '0' : sql_friendly(money_strip($_POST['rate_stepoff'])))."',
				rate_lumper = '".($_POST['rate_lumper'] == '' ? '0' : sql_friendly(money_strip($_POST['rate_lumper'])))."',
				rate_misc = '".($_POST['rate_misc'] == '' ? '0' : sql_friendly(money_strip($_POST['rate_misc'])))."',
				rate_fuel_surcharge_total = '".($_POST['rate_fuel_surcharge_total'] == '' ? '0' : sql_friendly(money_strip($_POST['rate_fuel_surcharge_total'])))."',
		*/
		
		
		$was_master_load=0;
		$sql = "
			select * 
			from load_handler
			where id = '".sql_friendly($edit_id)."'
		";
		$data_old=simple_query($sql);
		if($row_old=mysqli_fetch_array($data_old))
		{
			$was_master_load=$row_old['master_load'];
			
			if($was_master_load==0 && isset($_POST['master_load']))
			{
				$sql = "
					update load_handler_stops set
						master_load_include='1',
						master_load_pickup_eta=linedate_pickup_eta
					where load_handler_id = '".sql_friendly($edit_id)."'
				";
				simple_query($sql);
			}
			elseif($was_master_load==1 && !isset($_POST['master_load']))
			{
				$sql = "
					update load_handler_stops set
						master_load_include='0',
						master_load_pickup_eta='0000-00-00 00:00:00'
					where load_handler_id = '".sql_friendly($edit_id)."'
				";
				simple_query($sql);	
			}				
		}		
		
		if($_POST['actual_fuel_surcharge_per_mile']=="0.00" || $_POST['actual_fuel_surcharge_per_mile']=="$0.00" || $_POST['actual_fuel_surcharge_per_mile']==0)	
     	{
     		$temp_sur=mrr_auto_calculate_surcharge($_POST['customer_id'], money_strip($_POST['actual_rate_fuel_surcharge']));
     		if(trim($temp_sur)!="$0.00" )		$_POST['actual_fuel_surcharge_per_mile']=$temp_sur;
     	}
		
		$sql = "
			update load_handler
			set customer_id = '".sql_friendly($_POST['customer_id'])."',
				invoice_number = '".sql_friendly($_POST['invoice_number'])."',
				linedate_invoiced = '".($_POST['linedate_invoiced'] != '' ? date("Y-m-d-", strtotime($_POST['linedate_invoiced'])) : "0000-00-00")."',
				load_number = '".sql_friendly($_POST['load_number'])."',
				special_instructions = '".sql_friendly($_POST['special_instructions'])."',
				load_available = '".(isset($_POST['load_available']) ? '1' : '0')."',
				preplan = '".(isset($_POST['preplan']) ? '1' : '0')."',
				preplan_driver_id = '".(!isset($_POST['preplan']) ? 0 : sql_friendly($_POST['preplan_driver_id']))."',
				rate_fuel_surcharge = '".($_POST['rate_fuel_surcharge'] == '' ? '0' : sql_friendly(money_strip($_POST['rate_fuel_surcharge'])))."',
				days_run_otr = '".($_POST['days_run_otr'] == '' ? '0' : sql_friendly(money_strip($_POST['days_run_otr'])))."',
				days_run_hourly = '".($_POST['days_run_hourly'] == '' ? '0' : sql_friendly(money_strip($_POST['days_run_hourly'])))."',
				loaded_miles_hourly = '".($_POST['loaded_miles_hourly'] == '' ? '0' : sql_friendly(money_strip($_POST['loaded_miles_hourly'])))."',
				hours_worked = '".($_POST['hours_worked'] == '' ? '0' : sql_friendly(money_strip($_POST['hours_worked'])))."',
				actual_rate_fuel_surcharge = '".($_POST['actual_rate_fuel_surcharge'] == '' ? '0' : sql_friendly(money_strip($_POST['actual_rate_fuel_surcharge'])))."',
				actual_fuel_charge_per_mile = '".($_POST['actual_fuel_charge_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['actual_fuel_charge_per_mile'])))."',
				actual_fuel_surcharge_per_mile = '".($_POST['actual_fuel_surcharge_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['actual_fuel_surcharge_per_mile'])))."',
				fuel_charge_per_mile = '".($_POST['fuel_charge_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['fuel_charge_per_mile'])))."',
				actual_bill_customer = '".($_POST['actual_bill_customer'] == '' ? '0' : sql_friendly(money_strip($_POST['actual_bill_customer'])))."',
				rate_base = '".($_POST['rate_base'] == '' ? '0' : sql_friendly(money_strip($_POST['rate_base'])))."',
				deadhead_miles = '".($_POST['deadhead_miles'] == '' ? '0' : sql_friendly(money_strip($_POST['deadhead_miles'])))."',
				estimated_miles = '".($_POST['estimated_miles'] == '' ? '0' : sql_friendly(money_strip($_POST['estimated_miles'])))."',
				update_fuel_surcharge = '".($_POST['update_fuel_surcharge'] != '' ? date("Y-m-d-", strtotime($_POST['update_fuel_surcharge'])) : "0000-00-00")."',
				master_load = '".(isset($_POST['master_load']) ? '1' : '0')."',
				master_load_label = '".sql_friendly($_POST['master_load_label'])."',
				dedicated_load = '".(isset($_POST['dedicated_load']) ? '1' : '0')."',
				pickup_number = '".sql_friendly($_POST['pickup_number'])."',
				delivery_number = '".sql_friendly($_POST['delivery_number'])."',
				billing_notes = '".sql_friendly($_POST['billing_notes'])."',
				driver_notes = '".sql_friendly($_POST['driver_notes'])."',
				flat_fuel_rate_amount = '".sql_friendly(money_strip($_POST['flat_fuel_rate_amount']))."',
				preplan_driver2_id='".sql_friendly($_POST['preplan_driver2_id'])."',
				preplan_leg2_driver_id='".sql_friendly($_POST['preplan_leg2_driver_id'])."',
				preplan_leg2_driver2_id='".sql_friendly($_POST['preplan_leg2_driver2_id'])."',
				preplan_leg2_stop_id='".sql_friendly($_POST['preplan_leg2_stop_id'])."'
			
			where id = '".sql_friendly($edit_id)."'
		";
		//if($_SERVER['REMOTE_ADDR'] == '50.76.161.186') d($sql);
		simple_query($sql);
				
		if($edit_id > 0 && $mrr_mledit == 0)
		{
			$pn_auto_dispatch_link="peoplenet_interface.php?find_load_id=".$edit_id."&auto_run=1";
		}
		
		/************************************************************************\
		|            May require changes to edi_fedex_in.php as well ???         |
		\************************************************************************/
				
		$mrr_activity_log_notes.="Updated Load ".$edit_id.". ";
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$edit_id,0,0,"Updated Load ".$edit_id." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		// clear out our quote variable expenses and add the new ones in
		$sql = "
			delete from load_handler_quote_var_exp
			where load_handler_id = '".sql_friendly($edit_id)."'
		";
		simple_query($sql);
		
		// clear out our actual variable expenses and add the new ones in
		$sql = "
			delete from load_handler_actual_var_exp
			where load_handler_id = '".sql_friendly($edit_id)."'
		";
		simple_query($sql);		
		
		if(isset($_POST['variable_expense_id_array'])) {
			foreach($_POST['variable_expense_id_array'] as $value) {
				$sql = "
					insert into load_handler_quote_var_exp
						(load_handler_id,
						expense_type_id,
						expense_amount)
						
					values ('".sql_friendly($edit_id)."',
						'".sql_friendly($value)."',
						'".money_strip($_POST['variable_'.$value])."')
				";
				simple_query($sql);
			}
		}
		
		if(isset($_POST['actual_variable_expense_id_array'])) {
			foreach($_POST['actual_variable_expense_id_array'] as $value) {
				$sql = "
					insert into load_handler_actual_var_exp
						(load_handler_id,
						expense_type_id,
						expense_amount)
						
					values ('".sql_friendly($edit_id)."',
						'".sql_friendly($value)."',
						'".money_strip($_POST['actual_variable_'.$value])."')
				";
				simple_query($sql);
			}
		}		
		
		update_origin_dest($edit_id);
		
		//header("Location: ".$_SERVER['SCRIPT_NAME']."?load_id=$edit_id");
		
		$mrr_master_load_editor_flag="&update_pn_dispatches=1";
		if($mrr_mledit > 0)		$mrr_master_load_editor_flag="&master_load_edit=1";
		
		javascript_redirect($_SERVER['SCRIPT_NAME']."?load_id=$edit_id".$extra_param."".$mrr_master_load_editor_flag."");
		die;
	}
	
	/* get the customer list */
	$sql = "
		select customers.*,
			(
				select count(*) 
				from attachments 
				where attachments.section_id='".SECTION_CUSTOMER."' 
					and attachments.deleted='0' 
					and attachments.xref_id=customers.id 
					and attachments.descriptor='M'
			) as doc_cntr
		
		from customers
		where customers.deleted = 0
			and customers.active = 1
		order by customers.name_company
	";
	$data_customers = simple_query($sql);
	
	/* get the driver list */
	$sql = "
		select *
		
		from drivers
		where deleted = 0
		order by active desc, name_driver_last, name_driver_first
	";
	$data_drivers = simple_query($sql);
	$data_drivers2 = simple_query($sql);		//preplan driver 2
	$data_drivers3 = simple_query($sql);		//preplan leg 2 driver 1
	$data_drivers4 = simple_query($sql);		//preplan leg 2 driver 2
	
	$mrr_my_wkday=date("w");
	
	$send_pn_link=0;
	$first_truck_id=0;
		
	if($_POST['load_id'] > 0) {
		/* Get stop list for select box only*/
     	$sql = "
     		select id,
     			linedate_pickup_eta,
     			shipper_name
     		
     		from load_handler_stops 
     		where deleted = 0
     			and load_handler_id='".sql_friendly($_POST['load_id'])."'
     		order by linedate_pickup_eta asc, shipper_name asc
     	";
     	$data_stopper = simple_query($sql);
		
		$sql = "
			select *
			
			from load_handler
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$mrr_activity_log_notes.="View Load ".$_POST['load_id']." info. ";
		$mrr_activity_log_load=$_POST['load_id'];
	
		$_POST['customer_id'] = $row['customer_id'];
		$_POST['origin_address1'] = $row['origin_address1'];
		$_POST['origin_address2'] = $row['origin_address2'];
		$_POST['origin_city'] = $row['origin_city'];
		$_POST['origin_state'] = $row['origin_state'];
		$_POST['origin_zip'] = $row['origin_zip'];
		$_POST['dest_address1'] = $row['dest_address1'];
		$_POST['dest_address2'] = $row['dest_address2'];
		$_POST['dest_city'] = $row['dest_city'];
		$_POST['dest_state'] = $row['dest_state'];
		$_POST['dest_zip'] = $row['dest_zip'];
		
		
		if(strtotime($row['linedate_pickup_eta']) > 0) {
			$_POST['pickup_eta'] = date("m/d/Y", strtotime($row['linedate_pickup_eta']));
			$mrr_my_wkday=date("w", strtotime($row['linedate_pickup_eta']));
			if(date("H:i a", strtotime($row['linedate_pickup_eta'])) > 0) $_POST['pickup_eta_time'] = date("H:i", strtotime($row['linedate_pickup_eta']));
		}
		if(strtotime($row['linedate_pickup_pta']) > 0) {
			$_POST['pickup_pta'] = date("m/d/Y", strtotime($row['linedate_pickup_pta']));
			if(date("H", strtotime($row['linedate_pickup_pta'])) > 0) $_POST['pickup_pta_time'] = date("H:i", strtotime($row['linedate_pickup_pta']));
		}
		if(strtotime($row['linedate_dropoff_eta']) > 0) {
			$_POST['dropoff_eta'] = date("m/d/Y", strtotime($row['linedate_dropoff_eta']));
			if(date("H", strtotime($row['linedate_dropoff_eta']))> 0) $_POST['dropoff_eta_time'] = date("H:i", strtotime($row['linedate_dropoff_eta']));
		}
		if(strtotime($row['linedate_dropoff_pta']) > 0) {
			$_POST['dropoff_pta'] = date("m/d/Y", strtotime($row['linedate_dropoff_pta']));
			if(date("H", strtotime($row['linedate_dropoff_pta'])) > 0) $_POST['dropoff_pta_time'] = date("H:i", strtotime($row['linedate_dropoff_pta']));
		}
		$_POST['special_instructions'] = $row['special_instructions'];
		$_POST['estimated_miles'] = number_format($row['estimated_miles']);
		$_POST['deadhead_miles'] = number_format($row['deadhead_miles']);
		$_POST['quote'] = money_format('%i', $row['quote']);
		$_POST['days_run_otr'] = $row['days_run_otr'];
		$_POST['days_run_hourly'] = $row['days_run_hourly'];
		$_POST['loaded_miles_hourly'] = $row['loaded_miles_hourly'];
		$_POST['hours_worked'] = $row['hours_worked'];
		$_POST['shipper'] = $row['shipper'];
		$_POST['consignee'] = $row['consignee'];
		$_POST['fuel_charge_per_mile'] = money_format('%i', $row['fuel_charge_per_mile']);
		$_POST['actual_fuel_charge_per_mile'] = money_format('%i', $row['actual_fuel_charge_per_mile']);
		$_POST['actual_fuel_surcharge_per_mile'] = money_format('%i', $row['actual_fuel_surcharge_per_mile']);
		$_POST['invoice_number'] = $row['invoice_number'];
		$_POST['sicap_invoice_number'] = $row['sicap_invoice_number'];		
		$_POST['load_number'] = $row['load_number'];
		$_POST['load_available'] = $row['load_available'];
		$_POST['preplan'] = $row['preplan'];
		$_POST['rate_unloading'] = $row['rate_unloading'];
		$_POST['rate_stepoff'] = $row['rate_stepoff'];
		$_POST['rate_misc'] = $row['rate_misc'];
		$_POST['rate_fuel_surcharge_per_mile'] = $row['rate_fuel_surcharge_per_mile'];
		$_POST['rate_fuel_surcharge_total'] = $row['rate_fuel_surcharge_total'];
		$_POST['rate_base'] = $row['rate_base'];
		$_POST['rate_lumper'] = $row['rate_lumper'];
		
		$_POST['preplan_driver_id'] = $row['preplan_driver_id'];
		$_POST['preplan_driver2_id'] = $row['preplan_driver2_id'];
		$_POST['preplan_leg2_driver_id'] = $row['preplan_leg2_driver_id'];
		$_POST['preplan_leg2_driver2_id'] = $row['preplan_leg2_driver2_id'];
		$_POST['preplan_leg2_stop_id'] = $row['preplan_leg2_stop_id'];
		
		$_POST['rate_fuel_surcharge'] = $row['rate_fuel_surcharge'];
		$_POST['actual_rate_fuel_surcharge'] = ($row['actual_rate_fuel_surcharge'] > 0 ? $row['actual_rate_fuel_surcharge'] : $defaultsarray['fuel_surcharge']);
		$_POST['actual_bill_customer'] = ($row['actual_bill_customer'] > 0 ? $row['actual_bill_customer'] : $row['rate_base']);
		$_POST['linedate_invoiced'] = $row['linedate_invoiced'];
		$_POST['master_load'] = $row['master_load'];
		$_POST['master_load_label']=$row['master_load_label'];
		$_POST['dedicated_load']= $row['dedicated_load'];
		
		$_POST['pickup_number']= $row['pickup_number'];
		$_POST['delivery_number']= $row['delivery_number'];
		
		$_POST['billing_notes']= $row['billing_notes'];
		$_POST['driver_notes']= $row['driver_notes'];
				
		$_POST['update_fuel_surcharge'] = date("m/d/Y", strtotime($row['update_fuel_surcharge']));
		if(strtotime($row['update_fuel_surcharge']) == 0) 	$_POST['update_fuel_surcharge'] = "";
		
		$_POST['flat_fuel_rate_amount']=$row['flat_fuel_rate_amount'];
		//$_POST['actual_bill_customer'] -= $_POST['flat_fuel_rate_amount'];	//will get added again in Javascript. 
		
		// get sum totals from our dispatches for this load
		$sql = "
			select *
			
			from trucks_log
			where deleted = 0
				and load_handler_id = '".sql_friendly($_POST['load_id'])."'
		";
		$data_disp = simple_query($sql);
		
		$loaded_miles = 0;
		$deadhead_miles = 0;
		$actual_total_cost = 0;
		$days_run_otr = 0;
		$days_run_hourly = 0;
		$loaded_miles_hourly = 0;
		$hours_worked = 0;
		$actual_days_run_otr_total = 0;
		$actual_hours_worked_total = 0;
		$actual_loaded_miles_hourly_total = 0;
		$actual_days_run_hourly_total = 0;
		
		while($row_disp = mysqli_fetch_array($data_disp)) {			
			$disp_total = get_dispatch_cost($row_disp['id']);
			$actual_total_cost += $disp_total;
			$loaded_miles += $row_disp['miles'];
			$deadhead_miles += $row_disp['miles_deadhead'];
			$days_run_otr += $row_disp['daily_run_otr'];
			$days_run_hourly += $row_disp['daily_run_hourly'];
			$loaded_miles_hourly += $row_disp['loaded_miles_hourly'];
			$hours_worked += $row_disp['hours_worked'];
			$actual_days_run_otr_total += ($row_disp['daily_cost'] * $row_disp['daily_run_otr']);
			$actual_hours_worked_total += ($row_disp['hours_worked'] * $row_disp['labor_per_hour']);
			$actual_loaded_miles_hourly_total = "$0.00";
			$actual_days_run_hourly_total += ($row_disp['daily_cost'] * $row_disp['daily_run_hourly']);
			
			if($first_truck_id==0)	$first_truck_id=$row_disp['truck_id'];
			
			$truck_valid_pn=mrr_validate_peoplenet_truck($row_disp['truck_id']);
			if($truck_valid_pn > 0)		$send_pn_link=1;
		}
		
		if(mysqli_num_rows($data_disp)) {
			$_POST['actual_miles'] = $loaded_miles;
			$_POST['actual_deadhead_miles'] = $deadhead_miles;
			$_POST['actual_days_run_otr'] = $days_run_otr;
			$_POST['actual_days_run_hourly'] = $days_run_hourly;
			$_POST['actual_loaded_miles_hourly'] = $loaded_miles_hourly;
			$_POST['actual_hours_worked'] = $hours_worked;
		}
	}
	
	if($mrr_mledit > 0)	
	{
		$auto_update_pn=0;	
		$send_pn_link=0;
	}
	
	if($_POST['load_id'] > 0 && $auto_update_pn==1)
	{
		if($send_pn_link > 0)
		{
			$pn_auto_dispatch_link="peoplenet_interface.php?find_load_id=".$_POST['load_id']."&auto_run=1";
		}
	}
		
	if(!isset($_POST['customer_id'])) $_POST['customer_id'] = 0;
	if(!isset($_POST['origin_address1'])) $_POST['origin_address1'] = "";
	if(!isset($_POST['origin_address2'])) $_POST['origin_address2'] = "";
	if(!isset($_POST['origin_city'])) $_POST['origin_city'] = "";
	if(!isset($_POST['origin_state'])) $_POST['origin_state'] = "";
	if(!isset($_POST['origin_zip'])) $_POST['origin_zip'] = "";
	if(!isset($_POST['dest_address1'])) $_POST['dest_address1'] = "";
	if(!isset($_POST['dest_address2'])) $_POST['dest_address2'] = "";
	if(!isset($_POST['dest_city'])) $_POST['dest_city'] = "";
	if(!isset($_POST['dest_state'])) $_POST['dest_state'] = "";
	if(!isset($_POST['dest_zip'])) $_POST['dest_zip'] = "";
	if(!isset($_POST['pickup_pta'])) $_POST['pickup_pta'] = "";
	if(!isset($_POST['pickup_eta'])) $_POST['pickup_eta'] = "";
	if(!isset($_POST['pickup_pta_time'])) $_POST['pickup_pta_time'] = "";
	if(!isset($_POST['pickup_eta_time'])) $_POST['pickup_eta_time'] = "";
	if(!isset($_POST['dropoff_pta'])) $_POST['dropoff_pta'] = "";
	if(!isset($_POST['dropoff_pta_time'])) $_POST['dropoff_pta_time'] = "";
	if(!isset($_POST['dropoff_eta'])) $_POST['dropoff_eta'] = "";
	if(!isset($_POST['dropoff_eta_time'])) $_POST['dropoff_eta_time'] = "";
	if(!isset($_POST['special_instructions'])) $_POST['special_instructions'] = "";
	if(!isset($_POST['estimated_miles'])) $_POST['estimated_miles'] = "";
	if(!isset($_POST['deadhead_miles'])) $_POST['deadhead_miles'] = "";
	if(!isset($_POST['quote'])) $_POST['quote'] = "";
	if(!isset($_POST['shipper'])) $_POST['shipper'] = "";
	if(!isset($_POST['consignee'])) $_POST['consignee'] = "";
	if(!isset($_POST['invoice_number'])) $_POST['invoice_number'] = "";
	if(!isset($_POST['sicap_invoice_number'])) $_POST['sicap_invoice_number'] = "";
	if(!isset($_POST['load_number'])) $_POST['load_number'] = "";
	if(!isset($_POST['fuel_charge_per_mile'])) $_POST['fuel_charge_per_mile'] = "";
	if(!isset($_POST['actual_fuel_charge_per_mile'])) $_POST['actual_fuel_charge_per_mile'] = "";
	if(!isset($_POST['actual_fuel_surcharge_per_mile'])) $_POST['actual_fuel_surcharge_per_mile'] = "";
	if(!isset($_POST['load_available'])) $_POST['load_available'] = 0;
	if(!isset($_POST['rate_unloading'])) $_POST['rate_unloading'] = 0;
	if(!isset($_POST['rate_stepoff'])) $_POST['rate_stepoff'] = 0;
	if(!isset($_POST['rate_misc'])) $_POST['rate_misc'] = 0;
	if(!isset($_POST['rate_fuel_surcharge_per_mile'])) $_POST['rate_fuel_surcharge_per_mile'] = 0;
	if(!isset($_POST['rate_fuel_surcharge_total'])) $_POST['rate_fuel_surcharge_total'] = 0;
	if(!isset($_POST['rate_base'])) $_POST['rate_base'] = 0;
	if(!isset($_POST['rate_lumper'])) $_POST['rate_lumper'] = 0;
	if(!isset($_POST['preplan'])) $_POST['preplan'] = '';
	
	if(!isset($_POST['preplan_driver_id'])) 		$_POST['preplan_driver_id'] = "";
	if(!isset($_POST['preplan_driver2_id'])) 		$_POST['preplan_driver2_id'] = "";
	if(!isset($_POST['preplan_leg2_driver_id'])) 	$_POST['preplan_leg2_driver_id'] ="";
	if(!isset($_POST['preplan_leg2_driver2_id'])) 	$_POST['preplan_leg2_driver2_id'] = "";
	if(!isset($_POST['preplan_leg2_stop_id'])) 		$_POST['preplan_leg2_stop_id'] = 0;
	
	if(!isset($_POST['days_run_otr'])) $_POST['days_run_otr'] = 0;
	if(!isset($_POST['days_run_hourly'])) $_POST['days_run_hourly'] = 0;
	if(!isset($_POST['loaded_miles_hourly'])) $_POST['loaded_miles_hourly'] = 0;
	if(!isset($_POST['hours_worked'])) $_POST['hours_worked'] = 0;
	if(!isset($_POST['rate_fuel_surcharge'])) $_POST['rate_fuel_surcharge'] = $defaultsarray['fuel_surcharge'];
	if(!isset($_POST['actual_rate_fuel_surcharge'])) $_POST['actual_rate_fuel_surcharge'] = $defaultsarray['fuel_surcharge'];
	if(!isset($_POST['actual_days_run_otr'])) $_POST['actual_days_run_otr'] = 0;
	if(!isset($_POST['actual_days_run_hourly'])) $_POST['actual_days_run_hourly'] = 0;
	if(!isset($_POST['actual_loaded_miles_hourly'])) $_POST['actual_loaded_miles_hourly'] = 0;
	if(!isset($_POST['actual_hours_worked'])) $_POST['actual_hours_worked'] = 0;
	if(!isset($_POST['actual_miles'])) $_POST['actual_miles'] = 0;
	if(!isset($_POST['actual_deadhead_miles'])) $_POST['actual_deadhead_miles'] = 0;
	if(!isset($actual_total_cost)) $actual_total_cost = 0;
	if(!isset($actual_extra_cost)) $actual_extra_cost = 0;
	if(!isset($_POST['actual_bill_customer'])) $_POST['actual_bill_customer'] = 0;
	if(!isset($_POST['linedate_invoiced'])) $_POST['linedate_invoiced'] = '';
	if(!isset($_POST['master_load'])) 		$_POST['master_load'] = 0;
	if(!isset($_POST['master_load_label']))	$_POST['master_load_label']="";
	if(!isset($_POST['dedicated_load'])) 	$_POST['dedicated_load'] = 0;
	
	if(!isset($_POST['flat_fuel_rate_amount']))	$_POST['flat_fuel_rate_amount']=0;
	
	if(!isset($_POST['pickup_number'])) 	$_POST['pickup_number']= "";
	if(!isset($_POST['delivery_number'])) 	$_POST['delivery_number']= "";
	
	if(!isset($_POST['billing_notes'])) 	$_POST['billing_notes']= "";
	if(!isset($_POST['driver_notes'])) 	$_POST['driver_notes']= "";
		
	if(!isset($_POST['update_fuel_surcharge'])) $_POST['update_fuel_surcharge'] = '';
	
	if($_POST['update_fuel_surcharge'] == "12/31/1969") 	$_POST['update_fuel_surcharge'] = "";
	if($_POST['update_fuel_surcharge'] == "00/00/0000") 	$_POST['update_fuel_surcharge'] = "";
	
	if(!isset($_POST['appointment_window'])) 			$_POST['appointment_window'] = 0;
	if(!isset($_POST['linedate_appt_window_start'])) 		$_POST['linedate_appt_window_start'] = "";
	if(!isset($_POST['linedate_appt_window_end'])) 		$_POST['linedate_appt_window_end'] = "";
	if(!isset($_POST['linedate_appt_window_start_time'])) 	$_POST['linedate_appt_window_start_time'] = "";
	if(!isset($_POST['linedate_appt_window_end_time'])) 	$_POST['linedate_appt_window_end_time'] = "";
	
	
	$profit = $_POST['rate_base'] - $actual_total_cost;
	if($profit > 0 && $_POST['rate_base'] > 0) {
		$profit_percent = number_format(($profit / $_POST['rate_base']) * 100, 2);
	} else {
		$profit_percent = 0;
	}
	
	$copy_stop_sel=mrr_get_master_load_stops_selector('mrr_copy_stops_selector',0,$_POST['customer_id']);
	
	
	// get a list of variable expenses (for the dispatch level)the user can enter for the quote
	$sql = "
		select option_values.*
		
		from option_values, option_cat
		where option_values.cat_id = option_cat.id
			and option_cat.cat_name = 'expense_type'
			and option_cat.deleted = 0
			and option_values.deleted = 0
		order by option_values.zorder, option_values.fvalue
	";
	$data_expenses_variable = simple_query($sql);
	
	// get a list of variable expenses (for the load handler level) the user can enter for the quote
	$sql = "
		select option_values.*
		
		from option_values, option_cat
		where option_values.cat_id = option_cat.id
			and option_cat.cat_name = 'expense_type_lh'
			and option_cat.deleted = 0
			and option_values.deleted = 0
		order by option_values.zorder, option_values.fvalue
	";
	$data_expenses_variable_lh = simple_query($sql);
	
	
	$terminal=mrr_get_terminal_hub_address();
	
	
	
?>
<? $no_header = 1 ?>
<? include('header.php') ?>

		<div class='nav_bar' style='width:100%;position:fixed;top:0px;left;0px;margin:0px 0 0 -3px;font-family:arial;font-size:12px'>
			<div style='float:left;margin-left:40px'>&nbsp;</div>
			<div class='toolbar_button' onclick="parent_window_refresh(true);">
				<div><img src='images/return.png'></div>
				<div>Close</div>
			</div>
			<div class='toolbar_button' onclick='new_load_handler()'>
				<div><img src='images/new.png'></div>
				<div>New</div>
			</div>
			<div class='toolbar_button' onclick='duplicate_master_load_handler()' title='Copy from Master load'>
					<div><img src='images/convert_type1.png'></div>
					<div>Master</div>
				</div>
			<div class='toolbar_button' onclick='save_load_handler()'>
				<div><img src='images/file.png'></div>
				<div>Save</div>
			</div>
			<span style='<?=(!isset($_GET['load_id']) ? "display:none;" : "")?>' class='loaded_load'>
				<div class='toolbar_button' onclick='delete_load_handler()'>
					<div><img src='images/delete.png'></div>
					<div>Delete</div>
				</div>
				<div class='toolbar_button' onclick='duplicate_load_handler()'>
					<div><img src='images/copy.png'></div>
					<div>Duplicate</div>
				</div>
				<div class='toolbar_button' onclick='window.open("print_customer_load.php?load_id=<?=$_GET['load_id']?>");' title='Print Conard Load Summary'>
          			<div><img src='images/printer.png' id='print_icon'></div>
          			<div>Print</div>
          		</div>
			</span>

			<!---
			<div class='toolbar_button' onclick="search_full();">
				<div><img src='images/formupdate.png' alt='History' title='History'></div>
				<div>History</div>
			</div>
			--->
		</div>
		
<div style='clear:both;height:80px'>&nbsp;</div>

<table>
<tr>
	<td valign='top'>


		<div class="panel panel-primary">
			<div class="panel-heading">Manage Load</div>
			  <div class="panel-body">		
						
						<form name='mainform' action='<?=$_SERVER['SCRIPT_NAME']?><?=($mrr_mledit > 0 ? "?load_id=".$_POST['load_id']."&master_load_edit=1" : "") ?>' method='post' style='text-align:left'>
						<input type='hidden' id='add_dispatch_after_submit' name='add_dispatch_after_submit' value='0'>
						<input type='hidden' name='load_id' id='load_id' value="<?=$_POST['load_id']?>">
								<!---
								<label><input type='checkbox' name='load_available' id='load_available' <?=($_POST['load_available'] ? 'checked' : '')?>> Load Available</label>
								--->
						<table class='section0_long' border='0'>
						<tr>
							<td width='100' valign='top'>Load ID</td>
							<td valign='top'>
								<span id='load_id_holder'><?=($_POST['load_id'] == 0 ? 'New' : $_POST['load_id'])?></span> <?=($mrr_mledit > 0 ? "<span style='color:brown'><b>{Master Load Edit}</b></span>" : "") ?>
							</td>
							<td width='50%'>
								<label><input type='checkbox' name='preplan' id='preplan' value='1' <?=($_POST['preplan'] ? 'checked' : '')?>> Pre-Plan</label> <?= show_help('manage_load.php','pre plan checkbox') ?>
							</td>
						</tr>
						<tr>
							<td valign='top'><label for='master_load'>Master Load</label></td>
							<td valign='top'><input type='hidden' name='master_load_id' id='master_load_id' value='0'>
								<input type='checkbox' name='master_load' id='master_load' value='1' <?=($_POST['master_load'] ? 'checked' : '')?>> <?= show_help('manage_load.php','master_load checkbox') ?>
								
								<input type='text' name='master_load_label' id='master_load_label' value="<?= $_POST['master_load_label'] ?>" class='input_normal'>
							</td>
							<td>
								Pre-plan driver: 
								<select name="preplan_driver_id" id="preplan_driver_id" class='standard12'>
									<option value="0">Choose Driver</option>
								<? while($row_driver = mysqli_fetch_array($data_drivers)) { ?>
									<option value="<?=$row_driver['id']?>"<?=($_POST['preplan_driver_id'] == $row_driver['id'] ? " selected" : "")?>>
										<?=(!$row_driver['active'] ? '(inactive) ' : '')?><?=$row_driver['name_driver_last']?>, <?=$row_driver['name_driver_first']?>
									</option>
								<? } ?>
								</select> <?= show_help('manage_load.php','pre plan driver select') ?>
								
								( Team Driver: 
								<select name="preplan_driver2_id" id="preplan_driver2_id" class='standard12'>
									<option value="0">Choose Driver</option>
								<? while($row_driver2 = mysqli_fetch_array($data_drivers2)) { ?>
									<option value="<?=$row_driver2['id']?>" <?=($_POST['preplan_driver2_id'] == $row_driver2['id'] ? " selected" : "")?>>
										<?=(!$row_driver2['active'] ? '(inactive) ' : '')?><?=$row_driver2['name_driver_last']?>, <?=$row_driver2['name_driver_first']?>
									</option>
								<? } ?>
								</select> <?= show_help('manage_load.php','pre plan driver2 select') ?>
								)
								
								<? if($_POST['load_id'] > 0) { ?>
				     				<br>Leg 2: 
				     				
				     				<select name="preplan_leg2_stop_id" id="preplan_leg2_stop_id" class='standard12'>
				     					<option value="0">Choose Leg2 First Stop ID</option>
				     				<? while($row_stopper = mysqli_fetch_array($data_stopper)) { ?>
				     					<option value="<?=$row_stopper['id']?>" <?=($_POST['preplan_leg2_stop_id'] == $row_stopper['id'] ? " selected" : "")?>>
				     						<?=$row_stopper['id'] ?>:  <?=date("m/d/Y H:i", strtotime($row_stopper['linedate_pickup_eta']))?>; <?=$row_stopper['shipper_name']?>
				     					</option>
				     				<? } ?>
				     				</select>
				     				
				     				<br>Leg 2 driver(s):
				     				<select name="preplan_leg2_driver_id" id="preplan_leg2_driver_id" class='standard12'>
				     					<option value="0">Choose Driver</option>
				     				<? while($row_driver3 = mysqli_fetch_array($data_drivers3)) { ?>
				     					<option value="<?=$row_driver3['id']?>" <?=($_POST['preplan_leg2_driver_id'] == $row_driver3['id'] ? " selected" : "")?>>
				     						<?=(!$row_driver3['active'] ? '(inactive) ' : '')?><?=$row_driver3['name_driver_last']?>, <?=$row_driver3['name_driver_first']?>
				     					</option>
				     				<? } ?>
				     				</select> <?= show_help('manage_load.php','pre plan Leg 2 driver select') ?> 
				     				
				     				( Team Driver:  
				     				<select name="preplan_leg2_driver2_id" id="preplan_leg2_driver2_id" class='standard12'>
				     					<option value="0">Choose Driver</option>
				     				<? while($row_driver4 = mysqli_fetch_array($data_drivers4)) { ?>
				     					<option value="<?=$row_driver4['id']?>" <?=($_POST['preplan_leg2_driver2_id'] == $row_driver4['id'] ? " selected" : "")?>>
				     						<?=(!$row_driver4['active'] ? '(inactive) ' : '')?><?=$row_driver4['name_driver_last']?>, <?=$row_driver4['name_driver_first']?>
				     					</option>
				     				<? } ?>
				     				</select> <?= show_help('manage_load.php','pre plan Leg 2 driver2 select') ?> 
				     				)				
								<? } else { ?>	
									<input type='hidden' name='preplan_leg2_stop_id' id='preplan_leg2_stop_id' value='0'>
									<input type='hidden' name='preplan_leg2_driver_id' id='preplan_leg2_driver_id' value='0'>
									<input type='hidden' name='preplan_leg2_driver2_id' id='preplan_leg2_driver2_id' value='0'>
								<? } ?>		
												
							</td>
						</tr>
						<tr>
							<td valign='top'><label for='dedicated_load'>Dedicated Load</label></td>
							<td valign='top'>
								<input type='checkbox' name='dedicated_load' id='dedicated_load' <?=($_POST['dedicated_load'] > 0 ? 'checked' : '')?>> <?= show_help('manage_load.php','dedicated_load checkbox') ?>
							</td>
							<td><div id='geofencing_load'></div> <span id='geo_update' class='good_alert'></span></td><!--  <span class='alert'>--Please, Do Not use Hot Load Tracking yet.</span> -->
						</tr>
						<tr>
							<td colspan='10'>
								<table class='section1' style='width:100%'>
								<tr>
									<td valign='top'>Customer</td>
									<td valign='top'>
										<div id='customer_id_holder'>
											<select name="customer_id" class='standard12' id="customer_id" onChange='mrr_api_aging_hunt(<?= $mrr_load_id ?>);'>
											<option value="">--Unspecified--
											<? 
											while($row_customers = mysqli_fetch_array($data_customers)) 
											{
												echo "<option";
												echo " slowpays='".$row_customers['slow_pays']."'";
												echo " override_slowpays='".$row_customers['override_slow_pays']."'";
												echo " credit_hold='".$row_customers['credit_hold']."'";
												echo " override_credit_hold='".$row_customers['override_credit_hold']."'";
												
												echo " document_75k_received='".$row_customers['document_75k_received']."'";
												echo " document_75k_exempt='".$row_customers['document_75k_exempt']."'";	
												
												echo " cm_docs='".$row_customers['doc_cntr']."'";							
												
												echo " value=\"".$row_customers['id']."\"";
												
												if($_POST['customer_id'] == $row_customers['id']) echo " selected";
												
												echo ">".$row_customers['name_company']."</option>";
											} 
											?>
											</select>
											
											&nbsp;&nbsp;&nbsp;
											<a href="javascript:add_customer_switch(true)">Add Customer</a>
										</div>
										<div id='mrr_customer_aging_info'></div>
										<div id='mrr_customer_payment_notes'></div>
										<div id='customer_id_holder_new'>
											<table>
											<tr>
												<td>Cust Name</td>
												<td><input name='customer_name' id='customer_name' class='input_normal'></td>
											</tr>
												<td>E-Mail</td>
												<td><input name='customer_email' id='customer_email' class='input_normal'></td>
											</tr>
											</tr>
												<td>Phone</td>
												<td><input name='customer_phone' id='customer_phone' class='input_normal'></td>
											</tr>
											</tr>
												<td>Address 1</td>
												<td><input name='customer_address1' id='customer_address1' class='input_normal'></td>
											</tr>
											</tr>
												<td>Address 2</td>
												<td><input name='customer_address2' id='customer_address2' class='input_normal'></td>
											</tr>
											</tr>
												<td>City</td>
												<td><input name='customer_city' id='customer_city' class='input_normal'></td>
											</tr>
											</tr>
												<td>State</td>
												<td><input name='customer_state' id='customer_state' class='input_normal'></td>
											</tr>
											</tr>
												<td>Zip</td>
												<td><input name='customer_zip' id='customer_zip' class='input_normal'></td>
											</tr>
											</table>
											<a href="javascript:add_customer_switch(false)">Cancel</a>
										</div>
										<div id='flag_slow_pay' class='alert' style='display:none'>Customer is slow to pay</div>
									</td>
									<td>
										<div id='cust_info' style='width:300px'></div>
									</td>
								</tr>
								
								</table>

							</td>
						</tr>
						</table>

					</div>
				</div>


		
		<table class='section1_long' style='width:900px'>
		<tr id='stop_holder_new'>
			<td valign='top' style='border:1px black solid' colspan='2' class='admin_menu3'>
				<table style='float:left'>
				<tr>
					<td>
						<div id='add_stop_holder' style='float:left'><a href="javascript:void(0)" onclick="add_stop(0)"><img src='images/add.gif' alt='Add New Stop' title='Add New Stop' style="border:0">Add New Stop</a></div> 
						 <?= show_help('manage_load.php','Add New Stop') ?>
					</td>
					<td>
						<div id='update_stop_holder' style='float:right;'><a href="javascript:void(0)" onclick="save_stop()"><img src='images/add.gif' alt='Update Stop' title='Update Stop' style="border:0">Save Stop</a></div>
					</td>
				</tr>
				<tr>
					<td>Stop ID</td>
					<td><span id='stop_id_holder'>New Stop</span></td>
				</tr>
				<tr>
					<td nowrap>Stop Type <?= show_help('manage_load.php','Stop Type') ?></td>
					<td colspan='2' nowrap>
						<select name='stop_type' id='stop_type'>
							<option value='1'>Shipper</option>
							<option value='2'>Consignee</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Appointment Time</td>
					<td>
						Date: <input name='pickup_eta' id='pickup_eta' value='<?=$_POST['pickup_eta']?>' class='input_date'>
						Time: <input name='pickup_eta_time' id='pickup_eta_time' value='<?=$_POST['pickup_eta_time']?>' class='input_time'>
					</td>
				</tr>
				<tr>
					<td>Projected Time</td>
					<td>
						Date: <input name='pickup_pta' id='pickup_pta' value='<?=$_POST['pickup_pta']?>' class='input_date'>
						Time: <input name='pickup_pta_time' id='pickup_pta_time' value='<?=$_POST['pickup_pta_time']?>' class='input_time'>
					</td>
				</tr>
				<tr>
					<td>Appointment Window</td>
					<td>
						<input type='checkbox' name='appointment_window' id='appointment_window' <?=($_POST['appointment_window'] > 0 ? 'checked' : '')?>> <?= show_help('manage_load.php','appointment_window') ?>
					</td>
				</tr>
				<tr>
					<td>Appt. Window Start</td>
					<td>
						Date: <input name='linedate_appt_window_start' id='linedate_appt_window_start' value='<?=$_POST['linedate_appt_window_start']?>' class='input_date'>
						Time: <input name='linedate_appt_window_start_time' id='linedate_appt_window_start_time' value='<?=$_POST['linedate_appt_window_start_time']?>' class='input_time'>
					</td>
				</tr>
				<tr>
					<td>Appt. Window End</td>
					<td>
						Date: <input name='linedate_appt_window_end' id='linedate_appt_window_end' value='<?=$_POST['linedate_appt_window_end']?>' class='input_date'>
						Time: <input name='linedate_appt_window_end_time' id='linedate_appt_window_end_time' value='<?=$_POST['linedate_appt_window_end_time']?>' class='input_time'>
					</td>
				</tr>
				</table>

				<table style='float:left;margin-top:0px'>
				<tr>
					<td valign='top'>&nbsp;</td>
					<td valign='top'>
						<?
						if($terminal['name']!="")
						{
							echo "
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<span class='mrr_link_like_on' onClick='mrr_fill_stop_as_terminal();'><b>Fill as Terminal</b></span>
								";	
						}
						?>	
					</td>						
				</tr>
				<tr>
					<td>Name</td>
					<td>
						<input name='shipper' id='shipper' value="" class='input_normal'>
						<a id='clear_loaded_address' href='javascript:clear_address_history()' style='display:none'>Clear this address from history</a>
					</td>
				</tr>
				<tr>
					<td>Address 1</td>
					<td><input name='origin_address1' id='origin_address1' value="" class='input_normal' onblur="toTitleCase(this)"></td>
				</tr>
				<tr>
					<td>Address 2</td>
					<td><input name='origin_address2' id='origin_address2' value="" class='input_normal' onblur="toTitleCase(this)"></td>
				</tr>
				<? /*
				 state...   mrr_find_city_and_state();
				 zip.....   onblur='mrr_find_city_and_state();'
				*/ ?>
				<tr>
					<td>City / State / Zip</td>
					<td>
						<input name='origin_city' id='origin_city' value="" onblur="initialCap(this)">
						<input name='origin_state' id='origin_state' value="" style='width:30px' onblur="fullCap(this); mrr_find_city_and_state();">
						<input name='origin_zip' id='origin_zip' value="" style='width:60px' onblur='mrr_find_city_and_state();'>
						<span id='mrr_zip_location'></span>
					</td>
				</tr>
				<tr>
					<td>Phone</td>
					<td><input name='origin_phone' id='origin_phone' value="" class='input_normal'></td>
				</tr>
				</table>
				
				<table style='float:left;margin-top:0px'>
				<tr>
					<td valign='top'><b>Special Notes (Internal) <?= show_help('manage_load.php','Special Notes for Stop') ?></b></td>					
				</tr>
				<tr>
					<td valign='top'>
						<textarea style='width:400px;height:100px;margin-bottom:10px' name='stop_spec_notes' id='stop_spec_notes'></textarea>
					</td>					
				</tr>
				</table>
				
				<table style='float:left;margin-top:0px' class='mrr_hide_this'>
				<tr>
					<td valign='top' colspan='2'><b>Special Operations</b></td>					
				</tr>
				<!--
				<tr>
					<td valign='top' colspan='2'><span class='alert'><b>Please, Do Not use Special Ops yet.</b></span></td>					
				</tr>
				-->
				<tr>
					<td valign='top'>Select Master Load Template</td>
					<td valign='top'>
						<?= $copy_stop_sel ?>
					</td>						
				</tr>	
				<tr>
					<td valign='top' colspan='2'>
						<span class='mrr_link_like_on' onClick='mrr_special_ops_copy_stops(0);'><b>Copy ALL Stops from Selected Load to this one.</b></span> <?= show_help('manage_load.php','Copy All Stops from Load') ?>
					</td>					
				</tr>
				<tr>
					<td valign='top' colspan='2'>
						<span class='mrr_link_like_on' onClick='mrr_special_ops_copy_stops(1);'><b>Fill Missing Stops from Selected Load to this one.</b></span> <?= show_help('manage_load.php','Copy Missing Stops from Load') ?>
					</td>					
				</tr>			
				</table>
				
				
				<div style='clear:both'></div>
				<div width='820'>
					<div style='float:right;'><span id='char_counter_alert' class='alert'></span> <span id='char_counter'></span></div>
					Special Instructions <?= show_help('manage_load.php','Special Instructions for Stop') ?>
					<br>
					<textarea style='width:820px;height:40px;margin-bottom:10px' name='stop_directions' id='stop_directions' onBlur='mrr_text_box_char_counter("stop_directions",180,"char_counter");'></textarea>
				</div>
				
				
			</td>
			<!--
			<td valign='top'>
				<table style='border:1px black solid'>
				<tr>
					<td width='100'></td>
					<td><div class='section_heading'>Consignee Information</div></td>
				</tr>
				<tr>
					<td>Dropoff ETA</td>
					<td>
						Date: <input name='dropoff_eta' id='dropoff_eta' value='<?=$_POST['dropoff_eta']?>' class='input_date'>
						Time: <input name='dropoff_eta_time' id='dropoff_eta_time' value='<?=$_POST['dropoff_eta_time']?>' class='input_time'>
					</td>
				</tr>
				<tr>
					<td>Dropoff PTA</td>
					<td>
						Date: <input name='dropoff_pta' id='dropoff_pta' value='<?=$_POST['dropoff_pta']?>' class='input_date'>
						Time: <input name='dropoff_pta_time' id='dropoff_pta_time' value='<?=$_POST['dropoff_pta_time']?>' class='input_time'>
					</td>
				</tr>
				<tr>
					<td>Consignee</td>
					<td><input name='consignee' id='consignee' value="<?=$_POST['consignee']?>" class='input_normal' onblur="toTitleCase(this)"></td>
				</tr>
				<tr>
					<td>Address 1</td>
					<td><input name='dest_address1' id='dest_address1' value="<?=$_POST['dest_address1']?>" class='input_normal' onblur="toTitleCase(this)"></td>
				</tr>
				<tr>
					<td>Address 2</td>
					<td><input name='dest_address2' id='dest_address2' value="<?=$_POST['dest_address2']?>" class='input_normal' onblur="toTitleCase(this)"></td>
				</tr>
				<tr>
					<td>City / State / Zip</td>
					<td>
						<input name='dest_city' id='dest_city' value="<?=$_POST['dest_city']?>" onblur="initialCap(this)">
						<input name='dest_state' id='dest_state' value="<?=$_POST['dest_state']?>" style='width:30px' onblur="fullCap(this)">
						<input name='dest_zip' id='dest_zip' value="<?=$_POST['dest_zip']?>" style='width:60px'>
					</td>
				</tr>
				</table>
			</td>
			-->
		</tr>
		<tr>
			<td colspan='5' id='stop_holder'>
			</td>
		</tr>
		</table>
		<table class='section4_long'>
		<tr>
			<td colspan='2'>
				<a href="javascript:void(0)" onclick="manage_dispatch(0)"><img src='images/add.gif' alt='Add Dispatch' title='Add Dispatch' style="border:0">Add Dispatch</a>
			</td>
		</tr>
		<tr>
			<td colspan='10'>
				<span id='dispatch_holder'></span>
			</td>
		</tr>
		</table>
		
		
		<table border='0' cellpadding='0' cellspacing='0'>
		<tr>
			<td valign='top'>
		
     		
     		<div id='upload_section' style='width:900px;margin-left:5px'></div>
     		
     		<table class='section3_long'>
     		<tr>
     			<td colspan='2'>
     				<b>Special Instructions</b> <?= show_help('manage_load.php','Special Instructions') ?><br>
     				<textarea name='special_instructions' style='width:850px;height:90px' onblur="initialCap(this)"><?=$_POST['special_instructions']?></textarea>
     				<hr>
     			</td>
     		</tr>
     		<tr>
     			<td colspan='2'>
     				<b>Billing Notes</b> <?= show_help('manage_load.php','Billing Notes') ?><br>
     				<textarea name='billing_notes' style='width:850px;height:30px' onblur="initialCap(this)"><?=$_POST['billing_notes']?></textarea>
     				<hr>
     			</td>
     		</tr>
     		<tr>
     			<td colspan='2'>
     				<b>Driver Notes</b> <?= show_help('manage_load.php','Driver Notes') ?><br>
     				<textarea name='driver_notes' style='width:850px;height:30px' onblur="initialCap(this)"><?=$_POST['driver_notes']?></textarea>
     				<hr>
     			</td>
     		</tr>
     		<tr>
     			<td valign='top'>
     				
     				
     				
     				<!---<div style='float:right'><a href="javascript:void(0)" onclick="$('.budget_display').toggle();">click to toggle budget table</a></div>--->
     				<div style='clear:both'></div>
     				<table style='border:1px black solid;display:none;float:right' class='budget_display'>
     				<tr>
     					<td colspan='2'><span class='section_heading'>BUDGET</span></td>
     				</tr>
     				<tr>
     					<td colspan='2'><hr></td>
     				</tr>
     				<tr>
     					<td width='100'>Loaded Miles <?= show_help('manage_load.php','Loaded Miles') ?></td>
     					<td>
     						<input style='text-align:right' name='estimated_miles' id='estimated_miles' value='<?=$_POST['estimated_miles']?>' class='input_medium calc_watch'>
     					</td>
     				</tr>
     				<tr>
     					<td>Deadhead Miles <?= show_help('manage_load.php','Deadhead Miles') ?></td>
     					<td><input style='text-align:right' name='deadhead_miles' id='deadhead_miles' value='<?=$_POST['deadhead_miles']?>' class='input_medium calc_watch'></td>
     				</tr>
     				<tr>
     					<td>Fuel Avg $</td>
     					<td><input style='text-align:right' name='rate_fuel_surcharge' id='rate_fuel_surcharge' value='<?=$_POST['rate_fuel_surcharge']?>' class='input_medium calc_watch'></td>
     				</tr>
     				<tr>
     					<td>Fuel per Mile</td>
     					<td><input style='text-align:right' name='fuel_charge_per_mile' id='fuel_charge_per_mile' value='<?=$_POST['fuel_charge_per_mile']?>' class='input_medium rate_field calc_watch'></td>
     				</tr>
     				<tr>
     					<td>Days Run (OTR) <?= show_help('manage_load.php','Days Run OTR') ?></td>
     					<td><input name='days_run_otr' id='days_run_otr' value='<?=$_POST['days_run_otr']?>' style='text-align:right' class='input_medium calc_watch'></td>
     				</tr>
     				<tr>
     					<td>Days Run (Hourly) <?= show_help('manage_load.php','Days Run Hourly') ?></td>
     					<td><input name='days_run_hourly' id='days_run_hourly' value='<?=$_POST['days_run_hourly']?>' style=';background-color:#ffa569;text-align:right' class='input_medium calc_watch'></td>
     				</tr>
     				<tr>
     					<td nowrap>Loaded Miles for Hourly<br>(local work) <?= show_help('manage_load.php','Loaded Miles Hourly') ?></td>
     					<td><input name='loaded_miles_hourly' id='loaded_miles_hourly' value='<?=$_POST['loaded_miles_hourly']?>' style='text-align:right' class='input_medium calc_watch'></td>
     				</tr>
     				<tr>
     					<td>Hours Worked <?= show_help('manage_load.php','Hours Worked') ?></td>
     					<td><input name='hours_worked' id='hours_worked' value='<?=$_POST['hours_worked']?>' style='text-align:right' class='input_medium calc_watch'></td>
     				</tr>
     				<tr>
     					<td colspan='2'><hr></td>
     				</tr>
     				<?
     				while($row_expenses_variable = mysqli_fetch_array($data_expenses_variable)) {
     					$use_expense = 0;
     					if($_POST['load_id'] > 0) {
     						// see if we have a value for this variable expense from a previous quote for this LH
     						$sql = "
     							select expense_amount
     							
     							from load_handler_quote_var_exp
     							where load_handler_id = '".sql_friendly($_GET['load_id'])."'
     								and expense_type_id = '".sql_friendly($row_expenses_variable['id'])."'
     						";
     						$data_this_expense = simple_query($sql);
     						if(mysqli_num_rows($data_this_expense)) {
     							$row_this_expense = mysqli_fetch_array($data_this_expense);
     							$use_expense = $row_this_expense['expense_amount'];
     						}
     					}
     					echo "
     						<tr>
     							<td>$row_expenses_variable[fvalue] ". show_help('manage_load.php',$row_expenses_variable['fvalue']) ."</td>
     							<td>
     								<input name='variable_$row_expenses_variable[id]' id='variable_$row_expenses_variable[id]' class='variable_expenses_quote rate_field input_medium calc_watch' style='text-align:right' value='$use_expense'>
     								<input type='hidden' name='variable_expense_id_array[]' value='$row_expenses_variable[id]'>
     							</td>
     						</tr>
     					";
     				}
     				
     				echo "<tr><td colspan='10'><hr></td></tr>";
     				
     				while($row_expenses_variable = mysqli_fetch_array($data_expenses_variable_lh)) {
     					$use_expense = 0;
     					if($_POST['load_id'] > 0) {
     						// see if we have a value for this variable expense from a previous quote for this LH
     						$sql = "
     							select expense_amount
     							
     							from load_handler_quote_var_exp
     							where load_handler_id = '".sql_friendly($_GET['load_id'])."'
     								and expense_type_id = '".sql_friendly($row_expenses_variable['id'])."'
     						";
     						$data_this_expense = simple_query($sql);
     						if(mysqli_num_rows($data_this_expense)) {
     							$row_this_expense = mysqli_fetch_array($data_this_expense);
     							$use_expense = $row_this_expense['expense_amount'];
     						}
     					}
     					echo "
     						<tr>
     							<td>$row_expenses_variable[fvalue] ". show_help('manage_load.php',$row_expenses_variable['fvalue']) ."</td>
     							<td>
     								<input name='variable_$row_expenses_variable[id]' id='variable_$row_expenses_variable[id]' class='variable_expenses_quote rate_field input_medium calc_watch' style='text-align:right' value='$use_expense'>
     								<input type='hidden' name='variable_expense_id_array[]' value='$row_expenses_variable[id]'>
     							</td>
     						</tr>
     					";
     				}
     				?>
     				<tr>
     					<td colspan='2'><hr></td>
     				</tr>
     				<tr>
     					<td>Bill Customer <?= show_help('manage_load.php','Bill Customer') ?></td>
     					<td><input name='rate_base' id='rate_base' style='text-align:right;background-color:#8fff8f;' value='<?=$_POST['rate_base']?>' class='rate_field input_medium calc_watch'></td>
     				</tr>
     				<tr>
     					<td colspan='4'>
     						<hr>
     					</td>
     				</tr>
     				<tr>
     					<td>Total Cost</td>
     					<td><input name='total_cost' id='total_cost' style='text-align:right;background-color:#ffa569' class='xlocked input_medium' value=''></td>
     				</tr>
     				<tr>
     					<td>Profit $</td>
     					<td><input name='profit' id='profit' style='text-align:right;background-color:#8fff8f;' class='xlocked rate_field input_medium' value='<?=money_format('', $profit)?>'></td>
     				</tr>
     				<tr>
     					<td>Profit %</td>
     					<td><input name='profit_percent' id='profit_percent' style='text-align:right' class='xlocked input_medium' value='<?=$profit_percent?>%'></td>
     				</tr>
     				<!--
     				<tr>
     					<td>Bill Customer</td>
     					<td><input name='bill_customer' id='bill_customer' style='background-color:#8fff8f'></td>
     				</tr>
     				<tr>
     					<td>Fuel Surcharge $</td>
     					<td>
     						<input style='text-align:right' name='rate_fuel_surcharge_total' id='rate_fuel_surcharge_total' value='<?=$_POST['rate_fuel_surcharge_total']?>' class='input_medium rate_field'>
     						(total) / <input style='text-align:right' name='fuel_charge_per_mile' id='fuel_charge_per_mile' value='<?=$_POST['fuel_charge_per_mile']?>' class='input_short'> (per mile)
     					</td>
     				</tr>
     				<tr>
     					<td>Unloading $</td>
     					<td><input style='text-align:right' name='rate_unloading' id='rate_unloading' value='<?=$_POST['rate_unloading']?>' class='input_medium rate_field'></td>
     				</tr>
     				<tr>
     					<td>Stop Off $</td>
     					<td><input style='text-align:right' name='rate_stepoff' id='rate_stepoff' value='<?=$_POST['rate_stepoff']?>' class='input_medium rate_field'></td>
     				</tr>
     				<tr>
     					<td>Lumper $</td>
     					<td><input style='text-align:right' name='rate_lumper' id='rate_lumper' value='<?=$_POST['rate_lumper']?>' class='input_medium rate_field'></td>
     				</tr>
     				<tr>
     					<td>Misc $</td>
     					<td><input style='text-align:right' name='rate_misc' id='rate_misc' value='<?=$_POST['rate_misc']?>' class='input_medium rate_field'></td>
     				</tr>
     				<tr>
     					<td>Total $</td>
     					<td><input style='text-align:right;background-color:#eeeeee' name='quote' id='quote' value='<?=$_POST['quote']?>' class='input_medium' readonly></td>
     				</tr>
     				<tr>
     					<td>Rate $</td>
     					<td><input style='text-align:right' name='rate_base' id='rate_base' value='<?=$_POST['rate_base']?>' class='input_medium rate_field'></td>
     				</tr>
     				-->
     				</table>
     				
     				
     				
     				
     				
     				<table style='border:1px black solid;'>
     				<tr>
     					<td colspan='2'><span class='section_heading'>ACTUAL</span></td>
     				</tr>
     				<tr>
     					<td width='100'>
     						<?
     							if($defaultsarray['sicap_integration'] == '1' && $_POST['sicap_invoice_number'] != '') {
     								echo "
     									<div style='float:left'>
     										<a href='javascript:sicap_view_invoice($_POST[sicap_invoice_number])'>Invoice Number</a>
     									</div>
     									<div style='float:left;margin-left:15px' class='delete_invoice_link_".$_GET['load_id']."'>
     										<a href='javascript:sicap_delete_invoice($row[id])'><img src='images/delete.gif' style='border:0;width:16px' alt='Delete invoice in the accounting system' title='Delete invoice in the accounting system'></a>
     									</div>
     								";
     								
     								if(trim($_POST['invoice_number'])=="")		$_POST['invoice_number']=$_POST['sicap_invoice_number'];
     								
     							} else {
     								echo "Invoice Number";
     							}     							
     											
     							if($_POST['update_fuel_surcharge'] == "12/31/1969") 	$_POST['update_fuel_surcharge'] = "";
     							if($_POST['update_fuel_surcharge'] == "00/00/0000") 	$_POST['update_fuel_surcharge'] = "";
     						?>     						
     					</td>
     					<td><input name='invoice_number' id='invoice_number' value='<?=$_POST['invoice_number']?>' class='input_medium invoice_number_holder_load_<?=$_GET['load_id']?>'></td>
     				</tr>
     				<tr>
     					<td width='100'>Date Invoiced</td>
     					<td><input name='linedate_invoiced' id='linedate_invoiced' value='<?=($_POST['linedate_invoiced'] > 0 ? date("m/d/Y", strtotime($_POST['linedate_invoiced'])) : "")?>' class='input_medium input_date invoice_date_holder_load_<?=$_GET['load_id']?>'></td>
     				</tr>
     				<tr>
     					<td width='100'>Load #</td>
     					<td><input name='load_number' id='load_number' value='<?=$_POST['load_number']?>' class='input_medium' onBlur='mrr_lading_number_search();'></td>
     				</tr>
     				<tr>
     					<td width='100'>Pick Up #</td>
     					<td><input name='pickup_number' id='pickup_number' value='<?=$_POST['pickup_number']?>' class='input_medium'></td>
     				</tr>
     				<tr>
     					<td width='100'>Delivery #</td>
     					<td><input name='delivery_number' id='delivery_number' value='<?=$_POST['delivery_number']?>' class='input_medium'></td>
     				</tr>
     				<tr>
     					<td width='100'>Update Surcharge <?= show_help('manage_load.php','Update Fuel Surcharge') ?></td>
     					<td><input name='update_fuel_surcharge' id='update_fuel_surcharge' value='<?= $_POST['update_fuel_surcharge'] ?>' class='input_medium mrr_input_date'></td>
     				</tr>
     				<tr>
     					<td colspan='2'><hr></td>
     				</tr>
     				<tr>
     					<td width='100'>Loaded Miles <?= show_help('manage_load.php','Actual Loaded Miles') ?></td>
     					<td>
     						<input style='text-align:right' name='actual_miles' id='actual_miles' value='<?=$_POST['actual_miles']?>' class='xlocked'>
     					</td>
     				</tr>
     				<tr>
     					<td>Deadhead Miles <?= show_help('manage_load.php','Actual Deadhead Miles') ?></td>
     					<td><input style='text-align:right' name='actual_deadhead_miles' id='actual_deadhead_miles' value='<?=$_POST['actual_deadhead_miles']?>' class='xlocked'></td>
     				</tr>
     				<tr>
     					<td>Fuel Avg $</td>
     					<td><input style='text-align:right' name='actual_rate_fuel_surcharge' id='actual_rate_fuel_surcharge' value='<?=$_POST['actual_rate_fuel_surcharge']?>' class='calc_watch xlocked'></td>
     				</tr>
     				<tr>
     					<td>Fuel per Mile <span id='actual_fuel_per_mile_holder'></span></td>
     					<td>
     						<input style='text-align:right;width:60px' name='actual_fuel_charge_per_mile' id='actual_fuel_charge_per_mile' value='<?=$_POST['actual_fuel_charge_per_mile']?>' class='xlocked rate_field calc_watch'>
     						<input style='text-align:right;width:80px' name='actual_fuel_charge_per_mile_total' id='actual_fuel_charge_per_mile_total' value='' class='rate_field calc_watch xlocked'>
     					</td>
     				</tr>
     				<tr>
     					<td nowrap>Fuel Surcharge per Mile <span id='actual_fuel_surcharge_per_mile_holder'></span> <?= show_help('manage_load.php','Actual Fuel Surcharge per Mile') ?></td>
     					<?
     					if($_POST['actual_fuel_surcharge_per_mile']=="0.00" || $_POST['actual_fuel_surcharge_per_mile']=="$0.00" || $_POST['actual_fuel_surcharge_per_mile']==0)	
     					{
     						$_POST['actual_fuel_surcharge_per_mile']=mrr_auto_calculate_surcharge($_POST['customer_id'], money_strip($_POST['actual_rate_fuel_surcharge']));
     					}
     					?>
     					<td>
     						<input style='text-align:right;width:58px' name='actual_fuel_surcharge_per_mile' id='actual_fuel_surcharge_per_mile' value='<?=$_POST['actual_fuel_surcharge_per_mile']?>' class='rate_field calc_watch'>
     						<input style='text-align:right;width:76px' name='actual_fuel_surcharge_per_mile_total' id='actual_fuel_surcharge_per_mile_total' value='' class='rate_field calc_watch xlocked'>
     					</td>
     				</tr>
     				<tr>
     					<td colspan='2'>
     						<a href='javascript:void(0)' onclick="$('.actual_details').toggle()">toggle details</a> <?= show_help('manage_load.php','Actual Toggle Details') ?>
     					</td>
     				</tr>
     				<tr class='actual_details'>
     					<td>Days Run (OTR) <?= show_help('manage_load.php','Actual Days Run OTR') ?></td>
     					<td>
     						<input name='actual_days_run_otr' id='actual_days_run_otr' value='<?=$_POST['actual_days_run_otr']?>' style='text-align:right;width:60px' class='xlocked'>
     						<input name='actual_days_run_otr_total' id='actual_days_run_otr_total' value='$<?=(isset($actual_days_run_otr_total) ? money_format('',$actual_days_run_otr_total) : "0.00")?>' style='text-align:right;width:80px' class='xlocked'>
     					</td>
     				</tr>
     				<tr class='actual_details'>
     					<td>Days Run (Hourly) <?= show_help('manage_load.php','Actual Days Run Hourly') ?></td>
     					<td>
     						<input name='actual_days_run_hourly' id='actual_days_run_hourly' value='<?=$_POST['actual_days_run_hourly']?>' style=';background-color:#ffa569;text-align:right;width:60px' class='xlocked'>
     						<input name='actual_days_run_hourly_total' id='actual_days_run_hourly_total' value='$<?=(isset($actual_days_run_hourly_total) ? money_format('',$actual_days_run_hourly_total) : "0.00")?>' style=';background-color:#ffa569;text-align:right;width:80px' class='xlocked'>
     					</td>
     				</tr>
     				<tr class='actual_details'>
     					<td nowrap>Loaded Miles for Hourly<br>(local work) <?= show_help('manage_load.php','Actual Loaded Miles Hourly') ?></td>
     					<td>
     						<input name='actual_loaded_miles_hourly' id='actual_loaded_miles_hourly' value='<?=$_POST['actual_loaded_miles_hourly']?>' style='text-align:right;width:60px' class='xlocked'>
     						<input name='actual_loaded_miles_hourly_total' id='actual_loaded_miles_hourly_total' value='$<?=(isset($actual_loaded_miles_hourly_total) ? money_format('',$actual_loaded_miles_hourly_total) : "0.00")?>' style='text-align:right;width:80px' class='xlocked'>
     					</td>
     				</tr>
     				<tr class='actual_details'>
     					<td>Hours Worked <?= show_help('manage_load.php','Actual Hours Worked') ?></td>
     					<td>
     						<input name='actual_hours_worked' id='actual_hours_worked' value='<?=$_POST['actual_hours_worked']?>' style='text-align:right;width:60px' class='xlocked'>
     						<input name='actual_hours_worked_total' id='actual_hours_worked_total' value='$<?=(isset($actual_hours_worked_total) ? money_format('',$actual_hours_worked_total) : "0.00")?>' style='text-align:right;width:80px' class='xlocked'>
     					</td>
     				</tr>
     				<tr>
     					<td colspan='4'><hr></td>
     				</tr>
     				<?
     				mysqli_data_seek($data_expenses_variable,0);
     				while($row_expenses_variable = mysqli_fetch_array($data_expenses_variable)) {
     					
     					if($_POST['load_id'] > 0) {
     						$sql = "
     							select sum(expense_amount) as total_expense_amount
     							
     							from dispatch_expenses, trucks_log
     							where dispatch_expenses.deleted = 0
     								and dispatch_expenses.dispatch_id = trucks_log.id
     								and trucks_log.load_handler_id = '".sql_friendly($_GET['load_id'])."'
     								and dispatch_expenses.expense_type_id = '$row_expenses_variable[id]'
     						";
     						$data_texpense = simple_query($sql);
     						$row_texpense = mysqli_fetch_array($data_texpense);
     						
     						$use_expense = $row_texpense['total_expense_amount'];
     						$actual_extra_cost += $use_expense;
     					} else {
     						$use_expense = 0;
     					}
     					echo "
     						<tr>
     							<td>$row_expenses_variable[fvalue] ". show_help('manage_load.php',$row_expenses_variable['fvalue']) ."</td>
     							<td><input name='actual_variable_$row_expenses_variable[id]' id='actual_variable_$row_expenses_variable[id]' value='$".money_format('',$use_expense)."' class='xlocked variable_expenses' style='text-align:right'></td>
     						</tr>
     					";
     				}
     				
     				echo "<tr><td colspan='10'><hr></td></tr>";
     				mysqli_data_seek($data_expenses_variable_lh,0);
     				while($row_expenses_variable = mysqli_fetch_array($data_expenses_variable_lh)) {
     					$use_expense = 0;
     					if($_POST['load_id'] > 0) {
     						// see if we have a value for this variable expense from a previous quote for this LH
     						$sql = "
     							select expense_amount
     							
     							from load_handler_actual_var_exp
     							where load_handler_id = '".sql_friendly($_GET['load_id'])."'
     								and expense_type_id = '".sql_friendly($row_expenses_variable['id'])."'
     						";
     						$data_this_expense = simple_query($sql);
     						if(mysqli_num_rows($data_this_expense)) {
     							$row_this_expense = mysqli_fetch_array($data_this_expense);
     							$use_expense = $row_this_expense['expense_amount'];
     						}
     					}
     					echo "
     						<tr>
     							<td>$row_expenses_variable[fvalue] ". show_help('manage_load.php',$row_expenses_variable['fvalue']) ."</td>
     							<td>
     								<input name='actual_variable_$row_expenses_variable[id]' id='actual_variable_$row_expenses_variable[id]' class='actual_variable_expenses rate_field calc_watch' style='text-align:right' value='$use_expense'>
     								<input type='hidden' name='actual_variable_expense_id_array[]' value='$row_expenses_variable[id]'>
     							</td>
     						</tr>
     					";
     				}
     				
     				?>
     				
     				<tr>
     					<td>Fuel Surcharge <?= show_help('manage_load.php','Actual Fuel Surcharge') ?></td>
     					<td><input style='text-align:right' name='fuel_surcharge_holder' id='fuel_surcharge_holder' value='' class='rate_field calc_watch xlocked'></td>
     				</tr>
     				<tr>
     					<td>Flat Fuel Rate for Bill <?= show_help('manage_load.php','flat_fuel_rate_amount') ?> <input type='hidden' name='fuel_surcharge_override' id='fuel_surcharge_override' value='0.00'></td>
     					<td><input style='text-align:right' name='flat_fuel_rate_amount' id='flat_fuel_rate_amount' value='0.00' class='rate_field calc_watch xlocked'></td>
     				</tr>
     				<tr>
     					<td colspan='4'><hr></td>
     				</tr>
     				<tr>
     					<td>Bill Customer <?= show_help('manage_load.php','Actual Bill Customer') ?></td>
     					<td><input name='actual_bill_customer' id='actual_bill_customer_calc' style='text-align:right;background-color:#8fff8f;' value='' class='rate_field calc_watch xlocked'></td>
     				</tr>
     				<? 
     				/*
     				<tr>
     					<td>Bill Customer</td>
     					<td><input name='actual_bill_customer' id='actual_bill_customer' style='text-align:right;background-color:#8fff8f;' value='<?=$_POST['actual_bill_customer']?>' class='rate_field calc_watch'></td>
     				</tr>
     				
     				*/
     				?>
     				<tr>
     					<td colspan='4'><hr></td>
     				</tr>
     				<tr>
     					<td>Base Cost <?= show_help('manage_load.php','Actual Base Cost') ?></td>
     					<td><input name='actual_base_cost' id='actual_base_cost' style='text-align:right;background-color:#ffa569' class='xlocked' value="$<?=money_format(false,($actual_total_cost - $actual_extra_cost))?>"></td>
     				</tr>
     				<tr>
     					<td>Extra Cost <?= show_help('manage_load.php','Actual Extra Cost') ?></td>
     					<td><input name='actual_extra_cost' id='actual_extra_cost' style='text-align:right;background-color:#ffa569' class='xlocked' value='$<?=money_format('',$actual_extra_cost)?>'></td>
     				</tr>
     				<tr>
     					<td>Total Cost <?= show_help('manage_load.php','Actual Total Cost') ?></td>
     					<td><input name='actual_total_cost' id='actual_total_cost' style='text-align:right;background-color:#ffa569' class='xlocked' value='$<?=money_format('',$actual_total_cost)?>'></td>
     				</tr>
     				<tr>
     					<td>Profit $ <?= show_help('manage_load.php','Actual Profit Amount') ?></td>
     					<td><input name='actual_profit' id='actual_profit' style='text-align:right;background-color:#8fff8f;' class='xlocked rate_field' value=''></td>
     				</tr>
     				<tr>
     					<td>Profit % <?= show_help('manage_load.php','Actual Profit Percent') ?></td>
     					<td><input name='actual_profit_percent' id='actual_profit_percent' style='text-align:right' class='xlocked' value=''></td>
     				</tr>
     				<!--
     				<tr>
     					<td colspan='2'><a href='print_customer_load.php?load_id=<?=$_GET['load_id']?>' target='_blank'>Print Conard Load Summary</a></td>
     				</tr>
     				-->
     				</table>
     			</td>	
     		</tr>
     		</table>
     		</td>
     		<td valign='top' width='600'>
     			<div class='change_log' width='600'>
     				<?
					if($_GET['load_id'] > 0) echo  mrr_get_user_change_log(" and user_change_log.load_id='".sql_friendly($_GET['load_id'])."'"," order by user_change_log.linedate_added asc","",1); 
					?>
     			</div>
     			
     			<div id='note_section'></div>
     		</td>
     	</tr>
     	</table>
	</td>
	<!---
	<td valign='top'>
		<div style='clear:both;height:80px'>&nbsp;</div>
		<div style='border:1px black solid;margin:5px 0 0 3px;text-align:left'>
			<p style='text-align:center'>Available Loads</p>
			
			<div id='available_loads' style=';text-align:left;float:left;margin:5px'></div>
			<div style='clear:both'></div>
		</div>
	</td>
	--->
</tr>
</table>
</form>
<div id='hide_table_15'></div>
<div id='hide_table_30'></div>
<div id='hide_table_45'></div>
<div id='hide_table_46'></div>
<?
	if(!isset($first_truck_id))		$first_truck_id=0;
	
	//add user action to log...
     $mrr_activity_log_user=(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0');
     $mrr_activity_log_self=(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
     $mrr_activity_log_query=(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');
     mrr_set_user_action_log($mrr_activity_log_user,$mrr_activity_log_self,$mrr_activity_log_query,$mrr_activity_log_refer,$mrr_activity_log_driver,$mrr_activity_log_truck,$mrr_activity_log_trailer,
     						$mrr_activity_log_load,$mrr_activity_log_dispatch,$mrr_activity_log_stop,$mrr_activity_log_notes);		//values initialized in application.php
?>

<script type='text/javascript'>
	var startTime = new Date();
	var load_id = <?=(isset($_GET['load_id']) ? $_GET['load_id'] : 0)?>;
	var loaded_stop = 0;
	var loaded_address_id = 0;
	var actual_total_cost_holder = <?=$actual_total_cost?>;
	var mrr_user_id = <?=$_SESSION['user_id'] ?> ;
	var mrr_now = '<?= date("m/d/Y"); ?>' ;
	var mrr_load_change = <?= $mrr_load_id ?>;
	var debug = <?=($_SERVER['REMOTE_ADDR'] == '50.76.161.186' ? 1 : 0)?>;
	
	$('.mrr_hide_this').hide();
	function mrr_fetch_spec_notes_from_address_match()
	{
		namer=$('#shipper').val();
		
   		if(namer!="" && $('#stop_spec_notes').val()=="")
   		{
   			$.ajax({
     		   type: "POST",
     		   url: "ajax.php?cmd=mrr_fetch_spec_notes",
     		   data: {
     		   		"pickup_date": $('#pickup_eta').val(),
     		   		"pickup_time": $('#pickup_eta_time').val(),
     		   		"name": namer,
     		   		"address1": $('#origin_address1').val(),
     		   		"city": $('#origin_city').val(),
     		   		"state": $('#origin_state').val(),
     		   		"zip_code": get_amount($('#origin_zip').val())
     		   		},		   
     		   dataType: "xml",
     		   cache:false,
     		   async:false,
     		   success: function(xml) {
     		   		if($(xml).find('SpecNotes').text()!="")
     		   		{			   				
     		   			$('#stop_spec_notes').val($(xml).find('SpecNotes').text());		   			
     		   		}	
     		   }	
     		});	
     	}
	}
	
	$().ready(function() {
		$('#ajax_time_keeper').html('');		
				
		<?
			if(isset($_GET['add_dispatch'])) {
				echo "manage_dispatch(0);";				
			}			
		?>
		
		var startTime1 = new Date();
		if($('#customer_id').val() > 0) 
		{
			load_cust_info($('#customer_id').val());
			mrr_api_aging_hunt(mrr_load_change);
		}
		var endTime1 = new Date();
		
		
		
		var startTime2 = new Date();
		//load_available_loads();
		load_stops();
		mrr_text_box_char_counter("stop_directions",180,"char_counter");
		var endTime2 = new Date();
		
		
		
		
		var startTime3 = new Date();
		<?
		if($mrr_mledit==0)
		{
			echo "
				//geofencing
				//mrr_show_geofencing_status(load_id,0,0);
			";		
		}		
		if(trim($pn_auto_dispatch_link)!="" && $mrr_mledit==0)
		{
			echo "
				mrr_update_pn_dispatch_by_link();
			";
		}
		?>	
		var endTime3 = new Date();
		
		
		
		var startTime4 = new Date();
		$('.rate_field').change(calc_totals);
		calc_totals();
		load_dispatchs();
		pull_surcharge_updateif_flagged();	
		var endTime4 = new Date();
		
		<?			
			if(isset($_GET['load_id']) && $_GET['load_id'] > 0) {
				echo " create_note_section('#note_section', 8, $_GET[load_id]); "; 
			}
		?>	
		
		var endTime = new Date();		//getTime function returns milliseconds.  1 milli = 0.001 seconds
		mrr_cur_timer_diff=endTime.getTime()/1000 - startTime.getTime()/1000;
		
		mrr_cur_timer_diff1=endTime1.getTime()/1000 - startTime1.getTime()/1000;		
		mrr_cur_timer_diff2=endTime2.getTime()/1000 - startTime2.getTime()/1000;		
		mrr_cur_timer_diff3=endTime3.getTime()/1000 - startTime3.getTime()/1000;		
		mrr_cur_timer_diff4=endTime4.getTime()/1000 - startTime4.getTime()/1000;
			
		
		
		time_report="";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>Customer Section Start Time = "+startTime1.getTime()/1000+".";		
		time_report= time_report + "<br>Customer Section End Time = "+endTime1.getTime()/1000+".";
		time_report= time_report + "<br>Customer Section Total Time = <b>"+mrr_cur_timer_diff1+"</b> Seconds.";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>CharStop Section Start Time = "+startTime2.getTime()/1000+".";		
		time_report= time_report + "<br>CharStop Section End Time = "+endTime2.getTime()/1000+".";
		time_report= time_report + "<br>CharStop Section Total Time = <b>"+mrr_cur_timer_diff2+"</b> Seconds.";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>PN Geofencing Section Start Time = "+startTime3.getTime()/1000+".";		
		time_report= time_report + "<br>PN Geofencing Section End Time = "+endTime3.getTime()/1000+".";
		time_report= time_report + "<br>PN Geofencing Section Total Time = <b>"+mrr_cur_timer_diff3+"</b> Seconds.";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>Dispatch/Surcharge Section Start Time = "+startTime4.getTime()/1000+".";		
		time_report= time_report + "<br>Dispatch/Surcharge Section End Time = "+endTime4.getTime()/1000+".";
		time_report= time_report + "<br>Dispatch/Surcharge Section Total Time = <b>"+mrr_cur_timer_diff4+"</b> Seconds.";
		time_report= time_report + "<br><hr>";	
		time_report= time_report + "<br>Ajax Start Time = "+startTime.getTime()/1000+".";		
		time_report= time_report + "<br>Ajax End Time = "+endTime.getTime()/1000+".";
		time_report= time_report + "<br>Ajax Total Time = <b>"+mrr_cur_timer_diff+"</b> Seconds.";
		
		$('#ajax_time_keeper').html('<b>Ajax Ready Time Report:</b> '+time_report+'');
		
	});
	
	$('.input_date').datepicker();
	//$('.input_time').timeEntry({show24Hours:true});
	$('.input_time').blur(simple_time_check);
	$('.mrr_input_date').datepicker();
	
	$('.toolbar_button').hover(
		function() {
			$(this).addClass('toolbar_button_hover');
		},
		function() {
			$(this).removeClass('toolbar_button_hover');
		}
	);
	
	function mrr_special_ops_copy_stops(moder)
	{
		cur_load=load_id;
		sel_load=$('#mrr_copy_stops_selector').val();
				
		if(moder==1)
		{
			//alert('This is not available yet...  You have selected to copy missing stops from Load '+sel_load+' to Load '+cur_load+'.');
		}
		else
		{
			//alert('This is not available yet...  You have selected to copy all stops from Load '+sel_load+' to Load '+cur_load+'.');	
		}
		
		//if(cur_load>0 && sel_load>0)
		//{
			$.ajax({
     		   type: "POST",
     		   url: "ajax.php?cmd=mrr_special_ops_copy_stops_from_load",
     		   data: {
     		   		"cur_load": cur_load,
     		   		"sel_load": sel_load,
     		   		"moder": moder
     		   		},		   
     		   dataType: "xml",
     		   cache:false,
     		   async:false,
     		   success: function(xml) {
     		   		//if($(xml).find('mrrAddr').text()!="")
     		   		//{			   				
     		   		//	$('#mrr_zip_location').html($(xml).find('mrrAddr').text());		   			
     		   		//}	
     		   		load_stops();
     		   }	
     		});		
		//}
	}
	
	function mrr_fill_stop_as_terminal()
	{
		mrr_hub_phone='<?=$terminal["phone"] ?>';
		mrr_hub_name='<?=$terminal["name"] ?>';	
		mrr_hub_addr='<?=$terminal["address"] ?>';	
		mrr_hub_city='<?=$terminal["city"] ?>';	
		mrr_hub_state='<?=$terminal["state"] ?>';	
		mrr_hub_zip='<?=$terminal["zip"] ?>';	
		
		
		$('#shipper').val(mrr_hub_name);	
		$('#origin_address1').val(mrr_hub_addr);	
		$('#origin_address2').val('');	
		$('#origin_city').val(mrr_hub_city);	
		$('#origin_state').val(mrr_hub_state);	
		$('#origin_zip').val(mrr_hub_zip);	
		$('#origin_phone').val(mrr_hub_phone);
	}	
	
	function mrr_find_city_and_state()
	{		
		$('#mrr_zip_location').html('');	
		if($('#origin_state').val() !="" && $('#origin_zip').val()!="" && get_amount($('#origin_zip').val())!=0)
		{
		     $.ajax({
     		   type: "POST",
     		   url: "ajax.php?cmd=check_city_state_zip_info",
     		   data: {
     		   		"zip_code": get_amount($('#origin_zip').val()),
     		   		"state": $('#origin_state').val()
     		   		},		   
     		   dataType: "xml",
     		   cache:false,
     		   async:false,
     		   success: function(xml) {
     		   		if($(xml).find('mrrAddr').text()!="")
     		   		{			   				
     		   			$('#mrr_zip_location').html($(xml).find('mrrAddr').text());		   			
     		   		}	
     		   }	
     		});
		}	
	}
	
	//GeoFencing functions...added Feb 2013.......................................................
	function mrr_show_geofencing_status(loadid,dispatchid,stopid)
	{
			
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_get_hot_load_tracking",
		   data: {
		   		"load_id":loadid,
		   		"dispatch_id":dispatchid,
		   		"stop_id":stopid
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		if($(xml).find('mrrTab').text()!="")
		   		{			   				
		   			$('#geofencing_load').html($(xml).find('mrrTab').text());
		   			$('#geo_update').html('');		   			
		   		}	
		   }	
		});	
	}
	function activate_hot_tracking(loadid,dispatchid,stopid)
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_add_hot_load_tracking",
		   data: {
		   		"load_id":loadid,
		   		"dispatch_id":dispatchid,
		   		"stop_id":stopid
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		if($(xml).find('mrrTab').text()!="")
		   		{	
		   			mrr_show_geofencing_status(loadid,dispatchid,stopid);		
		   			$('#geo_update').html('Activated.');   			
		   		}	
		   }	
		});		
	}
	function deactivate_hot_tracking(loadid,dispatchid,stopid,remmode)
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_remove_hot_load_tracking",
		   data: {
		   		"load_id":loadid,
		   		"dispatch_id":dispatchid,
		   		"stop_id":stopid,
		   		"remove_mode":remmode
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		if($(xml).find('mrrTab').text()!="")
		   		{			   				
		   			mrr_show_geofencing_status(loadid,dispatchid,stopid);	
		   			$('#geo_update').html('Deactivated');	   			
		   		}	
		   }	
		});
	}
	function update_hot_tracking(loadid,dispatchid,stopid)
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_update_hot_load_tracking",
		   data: {
		   		"load_id":loadid,
		   		"dispatch_id":dispatchid,
		   		"stop_id":stopid
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		if($(xml).find('mrrTab').text()!="")
		   		{			   				
		   			mrr_show_geofencing_status(loadid,dispatchid,stopid);
		   			$('#geo_update').html('Updated');		   			
		   		}	
		   }	
		});
	}
	//..........................................................................................
	
	
	
	function mrr_lading_number_search()
	{		
		if($('#load_number').val()!='' && $('#load_number').val()!='0' && load_id > 0)
		{
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_lading_number_search",
			   data: {
			   		"load_id":load_id,
			   		"lading_number": $('#load_number').val()
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		if($(xml).find('mrrTab').text()!="")
			   		{			   				
			   			$.prompt($(xml).find('mrrTab').text());
			   		}	
			   }	
			 });	
		}
	}
	
	//-----select customer shows aging from API
	$('.tablesorter').tablesorter();
	function mrr_show_hide_group_span_alt(grp)
	{
		$('#hide_table_'+grp+'').show();
		txt=$('#hide_table_'+grp+'').html();		
		$('#hide_table_'+grp+'').hide();
		
		$.prompt(""+txt+"", {
			buttons: {Cancel:false},
			submit: function(v, m, f) {
				if(v) {						
					//txt=$('.mrr_delay_group_'+grp+'').show(); 
				}
			}
		});
		
	}
	function mrr_show_hide_group_span(grp)
	{
		$('.mrr_delay_group_15').hide(); 
		$('.mrr_delay_group_30').hide(); 
		$('.mrr_delay_group_45').hide(); 
		$('.mrr_delay_group_46').hide(); 	
		
		$('.mrr_delay_group_'+grp+'').show(); 		
	}
		
	function mrr_api_aging_hunt(id)
	{
		if($('#customer_id').val() > 0) 
		{
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_get_ar_summary_info_find",
			   data: {
			   		"cust_id":$('#customer_id').val(),
			   		"cust_name":'',
			   		"date_from":mrr_now,
			   		"date_to":mrr_now
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		if($(xml).find('mrrTab').text())
			   		{			   				
			   			tmp=$(xml).find('mrrTab').text();
			   			tmp2=$(xml).find('paymentNotes').text();
			   			$('#mrr_customer_aging_info').html(tmp); 
			   			$('#mrr_customer_payment_notes').html(tmp2); 
			   			
			   			if(id==0)		$.prompt($(xml).find('mrrTab').text());
			   			
			   			//$('.mrr_delay_group_15').hide(); 
			   			//$('.mrr_delay_group_30').hide(); 
			   			//$('.mrr_delay_group_45').hide(); 
			   			//$('.mrr_delay_group_46').hide(); 
			   			
			   			tmpA=$(xml).find('mrrA').text();
			   			tmpB=$(xml).find('mrrB').text();
			   			tmpC=$(xml).find('mrrC').text();
			   			tmpD=$(xml).find('mrrD').text();
			   			
			   			$('#hide_table_15').html(tmpA);
			   			$('#hide_table_30').html(tmpB);
			   			$('#hide_table_45').html(tmpC);
			   			$('#hide_table_46').html(tmpD);
			   						   			
			   			$('#hide_table_15').hide();
			   			$('#hide_table_30').hide();
			   			$('#hide_table_45').hide();
			   			$('#hide_table_46').hide();	   			
			   		}	
			   }	
			 });
		}		
	}
	//-----------------------------------------
	
	function new_load_handler() {
		window.location = "manage_load.php";
	}
	
	function mrr_update_pn_dispatch_by_link()
	{		
		url=	"<?= $pn_auto_dispatch_link ?>";
		//window.open(url,"_blank" );
		//window.opener.location.href = url;
		
		$.ajax({
			   type: "POST",
			   url: url,
			   data: {
			   		
			   		},		   
			   dataType: "html",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		$.noticeAdd({text: "PeopleNet Dispatch updated successfully."});
     					
			   }	
			 });
		
	}
	
	
	//----------------------------------------------------------------------------------------------------------------------------------------------
	function mrr_run_copy_from_master()
	{
		var masterid=	$('#master_load_id').val();
		if(masterid==0)
		{
			$.prompt("No Master Load selected to make copy.");	
		}
		else
		{
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_copy_load_handler_from_master_load",
			   data: {
			   		"load_id":masterid
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		newloadid = parseInt($(xml).find('NewLoadID').text());	
			   		if(newloadid>0)
			   		{     					
     					$.noticeAdd({text: "New load "+newloadid+" created from Master Load "+masterid+" successfully."});
     					windowname = window.open("manage_load.php?load_id="+newloadid,'customer_id','height=650,width=980,menubar=no,location=no,resizable=yes,status=no,scrollbars=yes')
						windowname.focus();
     				}
     				else
     				{
     					$.prompt("Failed to make copy of Master Load "+masterid+".");		
     				}
			   }	
			 });
		}
	}
	function mrr_set_master_load(loadid)
	{
		$('#mrr_master_load').val(loadid);	
		$('#master_load_id').val(loadid);
	}
	function mrr_run_copy_from_master_new(loadid)
	{
		var masterid=	loadid;
		if(masterid==0)
		{
			$.prompt("No Master Load selected to make copy.");	
		}
		else
		{
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_copy_load_handler_from_master_load",
			   data: {
			   		"load_id":masterid
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		newloadid = parseInt($(xml).find('NewLoadID').text());	
			   		if(newloadid > 0)
			   		{     					
     					$.noticeAdd({text: "New load "+newloadid+" created from Master Load "+masterid+" successfully."});     					
     					windowname = window.open("manage_load.php?load_id="+newloadid,'customer_id','height=650,width=980,menubar=no,location=no,resizable=yes,status=no,scrollbars=yes')
						windowname.focus();
     				}
     				else
     				{
     					$.prompt("Failed to make a copy of Master Load "+masterid+".");		
     				}
			   }	
			 });
		}
	}
	function duplicate_master_load_handler()
	{
		txt="";
		master_load="0";
		
		/*
		if(mrr_user_id!=23)
		{
			$.prompt("This function is not available yet.");	
		}
		*/	
		

			//$.prompt("This function is not available yet.");	
			//prompt messages
			txt1="";
			txt1+="<table class='admin_menu1' style='margin-top:10px' border='0'>";
			txt1+="<tr>";
			txt1+="<td valign='middle'><b>Duplicate from Selected Master Load:</b></td>";
			txt1+="<td valign='middle' align='right'><input style='text-align:right;width=100px;' id='mrr_master_load' name='mrr_master_load'></td>";
			txt1+="<td valign='middle' align='right'><input type='button' id='mrr_master_loader' name='mrr_master_loader' value='Copy Master Load' onClick='mrr_run_copy_from_master()'>";
			txt1+="</td>";
			txt1+="</tr>";
			txt1+="</table>";
			txt1+="<br>";
			
			//customer form...			
			//txt+=txt1;
			txt+="<table class='admin_menu1' style='margin-top:10px; width:575px;' border='0'>";
	          txt+="<tr>";
	          // txt+="<td style='width:30px'></td>";
	          txt+="<td style='width:50px'><b>LoadID</b></td>";
	          txt+="<td><b>Customer</b></td>";
	          txt+="<td><b>Label/Origin</b></td>";
	          txt+="<td><b>Destination</b></td>";
	          //txt+="<td><b>Driver</b></td>";
	          //txt+="<td><b>Truck</b></td>";
	          //txt+="<td><b>Trailer</b></td>";
	          txt+="</tr>"; 			
		
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_list_master_loads",			   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   	$(xml).find('MasterLoad').each(function() {
						loadid = $(this).find('LoadID').text();	
						//linker = $(this).find('LoadLink').text();	
						//clinker = $(this).find('LoadCustLink').text();
						//dlinker = $(this).find('LoadDriverLink').text();
						//trulinker = $(this).find('LoadTruckLink').text();
						//tralinker = $(this).find('LoadTrailerLink').text();
						
						linker = loadid;	
						loadlabel=$(this).find('LoadLabel').text();						
						clinker = $(this).find('LoadCustName').text();
						dlinker = $(this).find('LoadDriverName').text();
						trulinker = $(this).find('LoadTruckName').text();
						tralinker = $(this).find('LoadTrailerName').text();
						
						org = $(this).find('LoadOrigin').text();
						org_state = $(this).find('LoadOriginState').text();
						dest = $(this).find('LoadDest').text();
						dest_state = $(this).find('LoadDestState').text();
						
						txt+="<tr>";
	                         txt+="<td><span class='mrr_link_like_on' onClick='mrr_run_copy_from_master_new("+loadid+")'><b>"+loadid+"</b></span>";
	                         // txt+="<td><input type='checkbox' id='mrr_checkbox' class='mrr_checkbox' onClick='mrr_set_master_load("+loadid+")'></td>";
	                         // txt+="<td>"+linker+"</td>";
	                         txt+="<td>"+clinker+"</td>";
	                         
	                         if(loadlabel=="")
	                         {                         
	                         	txt+="<td>"+org+","+org_state+"</td>";
	                         	txt+="<td>"+dest+","+dest_state+"</td>";
	                         }
	                         else
	                         {
	                         	txt+="<td colspan='2'>"+loadlabel+"</td>";		
	                         }
	                         
	                         // txt+="<td>"+dlinker+"</td>";
	                         // txt+="<td>"+trulinker+"</td>";
	                         // txt+="<td>"+tralinker+"</td>";
	                         txt+="</tr>"; 
				});
			   		
						//<LoadID><![CDATA[$id]]></LoadID>
						//<LoadCustID><![CDATA[$cust_id]]></LoadCustID>
						//<LoadCustName><![CDATA[$cname]]></LoadCustName>
						//<LoadDriverID><![CDATA[$driver_id]]></LoadDriverID>
						//<LoadDriverName><![CDATA[$fname $lname]]></LoadDriverName>
						//<LoadTruckID><![CDATA[$truck_id]]></LoadTruckID>
						//<LoadTruckName><![CDATA[$tru_name]]></LoadTruckName>
						//<LoadTrailerID><![CDATA[$trailer_id]]></LoadTrailerID>
						//<LoadTrailerName><![CDATA[$tra_name]]></LoadTrailerName>
				
			   }	
			 });
			 
			 txt+="</table>";
			 $.prompt(""+txt+"", {
				buttons: {Cancel:false},
				submit: function(v, m, f) {
					if(v) {						
						mrr_run_copy_from_master();		//Done: true, 
					}
				}
			}
		);
					
	}
	//--------------------------------------------------------------------------------------------------------------------------------
	
	function save_load_handler() {
		if($('#customer_id').val() == '' && $('#customer_name').val() == '') {
			$.prompt("Customer is a required field");
			$('#add_dispatch_after_submit').val(0);
			return;
		}
		
		if($('#invoice_number').val() != '' && $('#linedate_invoiced').val() == '') {
			$.prompt("You must enter the 'Date Invoiced' if the 'Inoivce Number' is filled in");
			return false;
		}
		
		document.mainform.submit();
	}
	
	function delete_load_handler() {
		$.prompt("Are you sure you want to <span class='alert'>delete</span> this load?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						window.location = "<?=$_SERVER['SCRIPT_NAME']?>?delid="+load_id;
					}
				}
			}
		);	
	}
	
	function duplicate_load_handler() {
		if(!load_id) {
			$.prompt("You must save the load before you can duplicate it");
			return false;
		}
		
		$.prompt("Are you sure you want to <span class='alert'>duplicate</span> this load?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						window.location = "<?=$_SERVER['SCRIPT_NAME']?>?dup_id="+load_id;
					}
				}
			}
		);	
	}
	
	function manage_dispatch(dispatch_id) {
		
		if(!load_id) {
			// save this entry first, then do the dispatch
			if(!quick_create_lh()) return false;
			
			/*
			$('#add_dispatch_after_submit').val(1);
			save_load_handler();
			//$.prompt("You must save this Load Handler before adding a dispatch to it");
			return;
			*/
		}
		
		
		windowname = window.open('add_entry_truck.php?load_id='+load_id+'&id='+dispatch_id,'add_entry_truck_'+dispatch_id,'height=650,width=1600,menubar=no,location=no,resizable=yes,status=no,scrollbars=yes')
		windowname.focus();
	}
	
	function add_customer_switch(new_flag) {
		if(new_flag) {
			$('#customer_id_holder').hide();
			$('#customer_id_holder_new').show();
			$('#cust_info').hide();
			$('#customer_name').focus();
		} else {
			$('#customer_id_holder_new').hide();
			$('#customer_id_holder').show();
			$('#cust_info').show();
			$('#customer_name').val("");
		}
	}
	
	$('#customer_id').change(function() {
		slowpay = $('#customer_id option:selected').attr('slowpays');
		credit_hold = $('#customer_id option:selected').attr('credit_hold');
		override_credit_hold = $('#customer_id option:selected').attr('override_credit_hold');			
		override_slowpays= $('#customer_id option:selected').attr('override_slowpays');	
		
		document_75k_received= $('#customer_id option:selected').attr('document_75k_received');	
		document_75k_exempt= $('#customer_id option:selected').attr('document_75k_exempt');	
		
		cm_docs=$('#customer_id option:selected').attr('cm_docs');	
		
		
		if(document_75k_received=='0' && document_75k_exempt=='0')
		{
			$.prompt("<span class='alert'>Danger! Danger!:</span> '"+$('#customer_id option:selected').text()+"' is missing the <span class='alert'>75K Bond Document</span> - no new loads can be created for this customer. (Please see Dale or James to override.)");
			$('#customer_id').val(0);
			return false;	
		}
		/*
		if(cm_docs=='0' && document_75k_exempt=='0')
		{
			$.prompt("<span class='alert'>Danger! Danger!:</span> '"+$('#customer_id option:selected').text()+"' has no <span class='alert'>CM Document</span> - no new loads can be created until this is scanned into the customer record. (Please see Dale or James to override.)");
			$('#customer_id').val(0);
			return false;	
		}
		*/
		
		if(credit_hold == '1' && override_credit_hold=='0') {
			$.prompt("<span class='alert'>Danger! Danger!:</span> '"+$('#customer_id option:selected').text()+"' has a <span class='alert'>credit hold</span> - no new loads can be created for this customer. (Please see Dale or James to override.)");
			$('#customer_id').val(0);
			return false;
		}
		else
		{	
			if(credit_hold == '1' && override_credit_hold=='1') 
			{
			$.prompt("<span class='alert'>Please be aware:</span> '"+$('#customer_id option:selected').text()+"' has a <span class='alert'>credit hold</span> - no new loads <span class='alert'>should</span> be created, but credit hold override is on.");
			//$('#customer_id').val(0);
			//return false;
			}
			else
			{
				if(slowpay == '1' && override_slowpays=='0') 
				{
					$.prompt("'"+$('#customer_id option:selected').text()+"' is a <span class='alert'>slow paying</span> customer.  Please remind them they have an outstanding balance that needs to be paid.");
				}
			}
		}
		check_slowpay();
	});
	
	function check_slowpay() {
		slowpay = $('#customer_id option:selected').attr('slowpays');
		override_slowpays= $('#customer_id option:selected').attr('override_slowpays');	
		if(slowpay == '1' && override_slowpays=='0') {
			$('#flag_slow_pay').show();
		} else {
			$('#flag_slow_pay').hide();
		}
	}
	
	check_slowpay();
	
	
	$('#preplan_driver_id').change(function() {
		// verify driver not off on vacation....
		mrr_validate_driver_not_off($(this).val());
	});
	$('#preplan_driver2_id').change(function() {
		// verify driver not off on vacation....
		mrr_validate_driver_not_off($(this).val());
	});
	$('#preplan_leg2_driver_id').change(function() {
		// verify driver not off on vacation....
		mrr_validate_driver_not_off($(this).val());
	});
	$('#preplan_leg2_driver2_id').change(function() {
		// verify driver not off on vacation....
		mrr_validate_driver_not_off($(this).val());
	});
	
	function mrr_validate_driver_not_off(driver_id)
	{
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_get_driver_timeoff",
			   data: {"driver_id":driver_id},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {				
				if($(xml).find('warnings').text() != "")
				{				
					$.prompt("<span class='alert'>Please be aware:</span> "+$(xml).find('DriverName').text()+" <span class='alert'>will be Unavailable</span>:<br>"+$(xml).find('warnings').text()+"<br>Do not schedule if there is a conflict.");
				}				
			   }
		});
	}
	
	
	$('#customer_id').change(function() {
		// load the driver history
		load_cust_info($(this).val());
	});
	
	function load_cust_info(customer_id) {
		$('#cust_info').html("<img src='images/loader.gif'>");
		weekday=<?= (int) $mrr_my_wkday ?>;
		//alert('Weekday='+weekday+'.');
		
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=load_customer_brief",
			   data: {"customer_id":customer_id,
			   		fuel_avg:get_amount($('#actual_rate_fuel_surcharge').val())},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
				$('#cust_info').html($(xml).find('ReturnHTML').text());
				fuel_per_mile = $(xml).find('FuelPerMile').text();
				if(get_amount($('#actual_fuel_charge_per_mile').val()) == 0 && load_id == 0) {
					$('#actual_fuel_surcharge_per_mile').val(formatCurrency(fuel_per_mile));
				}
				$('#actual_fuel_surcharge_per_mile_holder').html("("+formatCurrency(fuel_per_mile)+")");
				 			
				
				fuel_per_mile_override = $(xml).find('FlatRateOverride').text();
				if(fuel_per_mile_override==1)
				{
					if(weekday==0)	$('#flat_fuel_rate_amount').val($(xml).find('FlatRateSun').text());
					if(weekday==1)	$('#flat_fuel_rate_amount').val($(xml).find('FlatRateMon').text());
					if(weekday==2)	$('#flat_fuel_rate_amount').val($(xml).find('FlatRateTue').text());
					if(weekday==3)	$('#flat_fuel_rate_amount').val($(xml).find('FlatRateWed').text());
					if(weekday==4)	$('#flat_fuel_rate_amount').val($(xml).find('FlatRateThu').text());
					if(weekday==5)	$('#flat_fuel_rate_amount').val($(xml).find('FlatRateFri').text());
					if(weekday==6)	$('#flat_fuel_rate_amount').val($(xml).find('FlatRateSat').text());	
					
					//mrr_flat_fuel_rate = get_amount($('#flat_fuel_rate_amount').val());
					$('#fuel_surcharge_override').val(fuel_per_mile_override);	
					
					/*	
					
					//alert('FR Override='+mrr_flat_fuel_rate+'.');
					
									
					$('#actual_fuel_charge_per_mile').val(formatCurrency(0));	
					*/
				}
				
			   }
			 });
	}
	
	function load_available_loads() {
		$('#driver_history').html("<img src='images/loader.gif'>");
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_available_loads",
		   data: {},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
			$('#available_loads').html($(xml).find('DispHTML').text());
		   }
		 });
	}
	
	function add_stop() {
		$('#stop_id_holder').html('New Stop');
		loaded_stop = 0;
		
   		$('#pickup_eta').val('');
   		$('#pickup_eta_time').val('');
   		$('#pickup_pta').val('');
   		$('#pickup_pta_time').val('');
   		
   		$('#appointment_window').attr('checked','');
   		$('#linedate_appt_window_start').val('');
   		$('#linedate_appt_window_start_time').val('');
   		$('#linedate_appt_window_end').val('');
   		$('#linedate_appt_window_end_time').val('');
		
   		$('#shipper').val('');
   		$('#origin_address1').val('');
   		$('#origin_phone').val('');
   		$('#origin_address2').val('');
   		$('#origin_city').val('');
   		$('#origin_state').val('');
   		$('#origin_zip').val('');
   		$('#stop_directions').val('');
   		$('#stop_spec_notes').val('');
   		
   		loaded_address_id = 0;
   		$('#clear_loaded_address').hide();
   		
   		mrr_text_box_char_counter("stop_directions",180,"char_counter");
	}
	
	
	function save_stop() {
		
		appt_window=0;
		if($('#linedate_appt_window_start').val() != '' && $('#linedate_appt_window_start_time').val() != '' && $('#linedate_appt_window_end').val() != '' && $('#linedate_appt_window_end_time').val() != '') 
		{
			//appointment window used, so set appointment as the last part of the window if blank, and flag to bypass warning.
			if($('#linedate_appt_window_end_time').val() != '' && $('#pickup_eta_time').val()=='')	$('#pickup_eta_time').val( ''+$('#linedate_appt_window_end_time').val()+'');
			if($('#linedate_appt_window_end').val() != '' && $('#pickup_eta').val()=='')			$('#pickup_eta').val( ''+$('#linedate_appt_window_end').val()+'');
			
			appt_window=1;
		}
		
		
		if(($('#pickup_eta').val() == '' || $('#pickup_eta_time').val() == '') && appt_window==0) 
		{
			$.prompt("Appointment (ETA) Date and time are required fields. Or, use the Appointment Window set of date and times to use a range.");
			return false;
		}
		
		manage_stop(loaded_stop);
		
		add_stop(); // call this to clear the fields after the save stop
	}
	
	function manage_stop(stop_id) {
		
		if(!load_id) {
			// save this entry first, then do the dispatch
			if(!quick_create_lh()) return false;
			/*
			$('#add_dispatch_after_submit').val(1);
			save_load_handler();
			//$.prompt("You must save this Load Handler before adding a dispatch to it");
			return;
			*/
		}
		
		mrr_checked_appt_window=0;
		if($('#appointment_window').attr('checked'))	mrr_checked_appt_window=1;	
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=manage_stop",
		   data: {load_id:load_id,
		   		stop_id:stop_id,
		   		stop_type:$('#stop_type').val(),
		   		shipper:$('#shipper').val(),
		   		shipper_address1:$('#origin_address1').val(),
		   		shipper_address2:$('#origin_address2').val(),
		   		shipper_city:$('#origin_city').val(),
		   		shipper_state:$('#origin_state').val(),
		   		shipper_zip:$('#origin_zip').val(),
		   		pickup_eta:$('#pickup_eta').val(),
		   		pickup_eta_time:$('#pickup_eta_time').val(),
		   		pickup_pta:$('#pickup_pta').val(),
		   		pickup_pta_time:$('#pickup_pta_time').val(),
		   		directions:$('#stop_directions').val(),
		   		spec_notes:$('#stop_spec_notes').val(),
		   		stop_phone:$('#origin_phone').val(),
		   		
		   		appt_window_start:$('#linedate_appt_window_start').val(),
		   		appt_window_start_time:$('#linedate_appt_window_start_time').val(),
		   		appt_window_end:$('#linedate_appt_window_end').val(),
		   		appt_window_end_time:$('#linedate_appt_window_end_time').val(),
		   		use_appt_window: mrr_checked_appt_window		   		
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
			
			load_stops();
			load_dispatchs();
		   }
		 });	
		
		
	}
	
	function mrr_reset_stop_times(stopid)
	{
		$.ajax({
		   	type: "POST",
		   	url: "ajax.php?cmd=mrr_reset_stop_times",
		   	data: {
		   		"stop_id":stopid,
		   		"load_id":load_id
		   		},
		   	dataType: "xml",
		   	cache:false,
		   	success: function(xml) {
				load_stops();
		   	}
		 });		
	}
	
	function load_stops() {
		
		if(load_id == 0) return false;
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_stops",
		   data: {load_id:load_id},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
			$('#stop_holder').html($(xml).find('DispHTML').text());
			
			$('.date_picker_completed').datepicker();
			//$('.time_picker_completed').timeEntry({show24Hours:true});
			$('.time_picker_completed').blur(simple_time_check);
			
			mrr_text_box_char_counter("stop_directions",180,"char_counter");
			
			
			if($(xml).find('DispWarn').text() !="")
			{
				$.prompt( $(xml).find('DispWarn').text() );	
			}
			
		   }
		 });	
	}

	
	function load_stop_id(stop_id) {
		
		loaded_stop = stop_id;
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_stop_id",
		   data: {stop_id:stop_id},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   	$('#stop_id_holder').html(stop_id);
		   	$('#stop_type').val($(xml).find('StopType').text());
			$('#shipper').val($(xml).find('ShipperName').text());
			$('#origin_address1').val($(xml).find('ShipperAddress1').text());
			$('#origin_address2').val($(xml).find('ShipperAddress2').text());
			$('#origin_phone').val($(xml).find('ShipperPhone').text());
			$('#origin_city').val($(xml).find('ShipperCity').text());
			$('#origin_state').val($(xml).find('ShipperState').text());
			$('#origin_zip').val($(xml).find('ShipperZip').text());
			$('#pickup_eta').val($(xml).find('PickupETA').text());
			$('#pickup_eta_time').val($(xml).find('PickupETATime').text());
			$('#pickup_pta').val($(xml).find('PickupPTA').text());
			$('#pickup_pta_time').val($(xml).find('PickupPTATime').text());
			$('#stop_directions').val($(xml).find('Directions').text());
			$('#stop_spec_notes').val($(xml).find('SpecNotes').text());
			
			mrr_appt_win='';
			if($(xml).find('ApptWindow').text() > 0)	mrr_appt_win=' checked';
			
			$('#appointment_window').attr('checked',mrr_appt_win);
   			$('#linedate_appt_window_start').val($(xml).find('ApptWindowStart').text());
   			$('#linedate_appt_window_start_time').val($(xml).find('ApptWindowStartTime').text());
   			$('#linedate_appt_window_end').val($(xml).find('ApptWindowEnd').text());
   			$('#linedate_appt_window_end_time').val($(xml).find('ApptWindowEndTime').text());
			
			mrr_text_box_char_counter("stop_directions",180,"char_counter");
		   }
		 });	
	}
	
	function mrr_appt_window_display(stop_id,appt_true,appt_start,appt_end)
	{
		if(stop_id>0)
		{
			$('#stop_'+stop_id+'_appt_window').html("<img src='images/loader.gif'> Loading Appt Window...");			
			$('#stop_'+stop_id+'_appt_window').show();
			
			txt="";
			txt = txt + "<div><b>Appointment Window for Stop "+stop_id+"</b></div>";
			txt = txt + "<div>Ideal Appt Time: "+appt_true+"</div>";
			txt = txt + "<div>&nbsp;</div>";
			txt = txt + "<div><b>Customer expects arrival within range below.</b></div>";
			txt = txt + "<div>Appt Window Start Time: "+appt_start+"</div>";
			txt = txt + "<div>Appt Window End Time: "+appt_end+"</div>";
			txt = txt + "<div>&nbsp;</div>";
									
			$('#stop_'+stop_id+'_appt_window').html(txt);		
		}
	}
	function mrr_appt_window_no_display(stop_id)
	{
		if(stop_id>0)
		{
			$('#stop_'+stop_id+'_appt_window').hide();	 	
		}
	}
	
	$('.stop_appt_window').hover(
		function() {
			stop_id = $(this).attr('stop_id');
			appt_true = $(this).attr('appt_win_ideal');
			appt_start = $(this).attr('appt_win_start');
			appt_end = $(this).attr('appt_win_end');			
			
			$('#stop_'+stop_id+'_appt_window').html("<img src='images/loader.gif'> Loading Appt Window...");			
			$('#stop_'+stop_id+'_appt_window').show();
			
			txt="";
			txt = txt + "<div><b>Appointment Window for Stop "+stop_id+"</b></div>";
			txt = txt + "<div>Ideal Appt Time: "+appt_true+"</div>";
			txt = txt + "<div>&nbsp;</div>";
			txt = txt + "<div><b>Customer expects arrival within range below.</b></div>";
			txt = txt + "<div>Appt Window Start Time: "+appt_start+"</div>";
			txt = txt + "<div>Appt Window End Time: "+appt_end+"</div>";
			txt = txt + "<div>&nbsp;</div>";
			
			txt2="";
			txt2 = txt2 + "Appointment Window for Stop "+stop_id+". ";
			txt2 = txt2 + "Ideal Appt Time: "+appt_true+". ";
			txt2 = txt2 + "Customer expects arrival within range below. ";
			txt2 = txt2 + "Appt Window Start Time: "+appt_start+". ";
			txt2 = txt2 + "Appt Window End Time: "+appt_end+". ";
			
			alert(txt2);
						
			$('#stop_'+stop_id+'_appt_window').html(txt);			
		},
		function() {
			$('#stop_'+$(this).attr('stop_id')+'_appt_window').hide();
		}
	);
	
	function mrr_odometer_grab(load_stop)
	{
		stop_id = load_stop;
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_load_stop_odometer_grab",
		   data: {stop_id:stop_id},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {		   	
			$('#odometer_reading_'+stop_id+'').val($(xml).find('OdometerValue').text());
		   }
		 });	
	}
	
	function delete_stop(stop_id) {
		$.prompt("Are you SURE you want to <span class='alert'>delete</span> this stop?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						 $.ajax({
						   type: "POST",
						   url: "ajax.php?cmd=delete_stop",
						   data: {stop_id:stop_id},
						   dataType: "xml",
						   cache:false,
						   success: function(xml) {
							
						   }
						 });	
						 
						 $('#stop_id_'+stop_id).hide();
						 load_dispatchs();
					}
					
				}
			}
		);
	}
	
	function update_stop_dispatch(stop_id) {
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=update_stop_dispatch",
		   data: {stop_id:stop_id,
		   		trucks_log_id:$('#stop_dispatch_'+stop_id).val()},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
			load_dispatchs();
		   }
		 });	
	}
	
	function calc_totals() {
		var total = 0;
		$('.rate_field').each(function() {
			total += get_amount($(this).val());
			$(this).val(formatCurrency($(this).val()));
		});
		$('#quote').val(formatCurrency(total));
	}
	
	function quick_create_lh() {
		
		if($('#customer_id').val() == '' && $('#customer_name').val() == '') {
			$.prompt("You must specify the customer before you can continue.");
			return false;
		}

		rslt = false;
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_handler_quick_create",
		   data: {customer_id:$('#customer_id').val(),
		   		customer_name:$('#customer_name').val(),
		   		customer_email:$('#customer_email').val(),
		   		customer_phone:$('#customer_phone').val(),
		   		customer_address1:$('#customer_address1').val(),
		   		customer_address2:$('#customer_address2').val(),
		   		customer_city:$('#customer_city').val(),
		   		customer_state:$('#customer_state').val(),
		   		customer_zip:$('#customer_zip').val() },
		   dataType: "xml",
		   async:false,
		   cache:false,
		   success: function(xml) {
				if($(xml).find('rslt').text() == 0) {
					$.prompt("Error saving the load handler, please try again");
				} else {
					load_id = $(xml).find('LoadID').text();
					
					if($('#customer_name').val() != '') {
						// new customer, get the new cust id
						customer_id = $(xml).find('CustomerID').text();
						$('#customer_id').append($("<option></option>").attr("value",customer_id).text($('#customer_name').val()));
						add_customer_switch(false);
						$('#customer_id').val(customer_id);
					}
					
					$('.loaded_load').show();
					$('#load_id').val(load_id);
					$('#load_id_holder').html(load_id);
					rslt = true;
				}
		   }
		 });	
		 
		 return rslt;
	}
		
	function pull_surcharge_updateif_flagged()
	{
		var new_charge='0.000';
		
		$.ajax({
          		   type: "POST",
          		   url: "ajax.php?cmd=mrr_pull_surcharge",
          		   data: { 
          		   		"cust_id": $('#customer_id').val(),
          		   		"alert_date": $('#update_fuel_surcharge').val()
          		   	 },
          		   dataType: "xml",
          		   cache:false,
          		   success: function(xml) {
          		   	$(xml).find('Fuel').each(function() {    
          		   		mrr_tmp=$(this).find('Dated').text();
          		   		if(mrr_tmp != "VOID")
          		   		{
          		   			new_charge=$(this).find('Surcharge').text() ;   
     						$.prompt("This load has been flagged for Fuel Surcharge Updates.<br>Current Fuel Surcharge for this customer is $ "+ new_charge +".");	
          		   		}    					
     				});      			
          		   }
		});			
		
	}
	
	function load_dispatchs() {
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_dispatchs",
		   data: {"load_id":load_id},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
			$('#dispatch_holder').html($(xml).find('HTML').text());
		   }
		 });
	}
	
	function mrr_clear_trailer_switch(switch_id) 
	{
		$.prompt("Are you SURE you want to <span class='alert'>cancel</span> this trailer switch/drop?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
     					$.ajax({
                    		   	type: "POST",
                    		   	url: "ajax.php?cmd=mrr_clear_switch_id",
                    		   	data: {
                    		   		"switch_id":switch_id
                    		   		},
                    		   	dataType: "xml",
                    		   	cache:false,
                    		   	success: function(xml) {
                    				$('#mrr_switcher_'+switch_id+'').hide();
                    				load_stops();
                    				load_dispatchs();
                    		   	}
                    		 }); 
					}
					
				}
			}
		);
	}
	
	/*
	$('#customer_id').change(function() {
		
		
		$('#shipper').autocomplete('ajax.php?cmd=search_stop_address&customer_id='+$('#customer_id').val(),{
																formatItem:formatItem, 
																onItemSelect:load_address
															});
	});
	*/
	$('#shipper').autocomplete('ajax.php?cmd=search_stop_address',{
																
																formatItem:formatItem, 
																onItemSelect:load_address
	});
	
	/*
	function get_customer_id() {
		alert('customer_id: ' + $('#customer_id').val());
		return $('#customer_id').val();
	}
	*/

	function load_address(row, input, extra) {
		load_stop_address_id = extra[1];
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_address_by_stop_id",
		   data: {"stop_id":load_stop_address_id},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   	loaded_address_id = load_stop_address_id;
			$('#origin_address1').val($(xml).find('Address1').text());
			$('#origin_address2').val($(xml).find('Address2').text());
			$('#origin_city').val($(xml).find('City').text());
			$('#origin_state').val($(xml).find('State').text());
			$('#origin_zip').val($(xml).find('Zip').text());
			$('#origin_phone').val($(xml).find('Phone').text());
			$('#stop_directions').val($(xml).find('Directions').text());
			$('#stop_spec_notes').val($(xml).find('SpecNotes').text());
			$('#clear_loaded_address').show();
			
			mrr_fetch_spec_notes_from_address_match();
		   }
		 });
	}
	
	function clear_address_history() {
		$.prompt("Are you sure you want to <span class='alert'>clear</span> this address from history?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						 $.ajax({
						   type: "POST",
						   url: "ajax.php?cmd=clear_address_history",
						   data: {"address_id":loaded_address_id},
						   dataType: "xml",
						   cache:false,
						   success: function(xml) {
						   	loaded_address_id = 0;
						   	$('#shipper').val('');
							$('#origin_address1').val('');
							$('#origin_address2').val('');
							$('#origin_city').val('');
							$('#origin_state').val('');
							$('#origin_zip').val('');
							$('#origin_phone').val('');
							$('#stop_directions').val('');
							$('#stop_spec_notes').val('');
							$('#clear_loaded_address').hide();
						   }
						 });
					}
				}
			}
		);	
	}
	
	<? if(isset($_GET['load_id']) && $_GET['load_id'] > 0) echo " create_upload_section('#upload_section', 8, $_GET[load_id]); "; ?>
	
	function calc_all() {
		
		// calc the quote side
		var daily_cost = <?=get_daily_cost($first_truck_id)?>;
		var fuel_avg = get_amount($('#rate_fuel_surcharge').val());
		var avg_mpg = <?=$defaultsarray['average_mpg']?>;
		var fuel_per_mile = fuel_avg / avg_mpg;
		$('#fuel_charge_per_mile').val(formatCurrency(fuel_per_mile))
		//var fuel_per_mile = get_amount($('#fuel_charge_per_mile').val());
		var labor_per_mile = <?=$defaultsarray['labor_per_mile']?>;
		var labor_per_mile_team = <?=$defaultsarray['labor_per_mile_team']?>;
		
		weekday=<?= (int) $mrr_my_wkday ?>;
		
		variable_expenses_total = 0;
		$('.variable_expenses_quote').each(function() {
			variable_expenses_total += get_amount($(this).val());
		});

		tractor_maint_per_mile = get_amount('<?=$defaultsarray['tractor_maint_per_mile']?>');
		trailer_maint_per_mile = get_amount('<?=$defaultsarray['trailer_maint_per_mile']?>');
		total_maint_per_mile = tractor_maint_per_mile + trailer_maint_per_mile;

		total_per_mile = labor_per_mile + total_maint_per_mile + fuel_per_mile;

		deadhead_cost = total_per_mile * get_amount($('#deadhead_miles').val());
		breakeven_otr = total_per_mile * get_amount($('#estimated_miles').val()) + deadhead_cost + (daily_cost * get_amount($('#days_run_otr').val()));
		labor_per_hour = get_amount('<?=$defaultsarray['labor_per_hour']?>');
		breakeven_hourly = get_amount($('#loaded_miles_hourly').val()) * (fuel_per_mile + total_maint_per_mile) + (get_amount($('#hours_worked').val()) * labor_per_hour) + (daily_cost * get_amount($('#days_run_hourly').val()));
		bill_customer = get_amount($('#rate_base').val());
		total_cost = breakeven_otr + breakeven_hourly + variable_expenses_total;
		profit = bill_customer - total_cost;
		if(profit > 0) {
			$('#profit_percent').val((profit / bill_customer * 100).toFixed(2) + '%');
		} else {
			$('#profit_percent').val(0 + '%');
		}		
		
		$('#total_cost').val(formatCurrency(total_cost));
		$('#profit').val(formatCurrency(profit));

		// end of the quote side
		
		
		// calc the 'actual' side
		
		
		actual_variable_expenses_total = 0;
		$('.actual_variable_expenses').each(function() {
			actual_variable_expenses_total += get_amount($(this).val());			
		});
		
		
		avg_per_mile = get_amount($('#actual_rate_fuel_surcharge').val()) / <?=$defaultsarray['average_mpg']?>;
		
		$('#actual_fuel_per_mile_holder').html("("+formatCurrency(avg_per_mile)+")");
		$('#actual_fuel_charge_per_mile').val(formatCurrency(avg_per_mile));
		//if(debug) alert(avg_per_mile);
		
		total_actual_miles = get_amount($('#actual_deadhead_miles').val()) + get_amount($('#actual_miles').val());
		$('#actual_fuel_charge_per_mile_total').val(formatCurrency(avg_per_mile * total_actual_miles));
		fuel_surcharge_per_mile_total = get_amount($('#actual_fuel_surcharge_per_mile').val()) * get_amount($('#actual_miles').val());
				
		if(get_amount($('#actual_loaded_miles_hourly').val()) > 0)
		{
			//			
			fuel_surcharge_per_mile_total = get_amount($('#actual_fuel_surcharge_per_mile').val()) * get_amount($('#actual_loaded_miles_hourly').val());	
			
			if(get_amount($('#actual_miles').val()) > 0)
			{
				fuel_surcharge_per_mile_total += get_amount($('#actual_fuel_surcharge_per_mile').val()) * get_amount($('#actual_miles').val());	
			}
		}
		
		
		mrr_flat_fuel_rate=0;
		if($('#customer_id').val() > 0)
		{
			load_cust_info($('#customer_id').val());
			
			mrr_flat_fuel_rate=	get_amount($('#flat_fuel_rate_amount').val());	
			if(mrr_flat_fuel_rate > 0)
			{	
				$('#flat_fuel_rate_amount').val(formatCurrency(mrr_flat_fuel_rate));
			}
		}
		
		$('#actual_fuel_surcharge_per_mile_total').val(formatCurrency(fuel_surcharge_per_mile_total));
		$('#fuel_surcharge_holder').val(formatCurrency(fuel_surcharge_per_mile_total));
		$('#actual_bill_customer_calc').val(formatCurrency(actual_variable_expenses_total + fuel_surcharge_per_mile_total + mrr_flat_fuel_rate));
		
		actual_total_cost = actual_total_cost_holder;
		$('#actual_total_cost').val(formatCurrency(actual_total_cost));
		
		profit = get_amount($('#actual_bill_customer_calc').val()) - get_amount($('#actual_total_cost').val());
		
		if(profit > 0) {
			profit_percent = profit /  get_amount($('#actual_bill_customer_calc').val()) * 100;
		} else {
			profit_percent = 0;
		}
		
		$('#actual_profit').val(formatCurrency(profit));
		$('#actual_profit_percent').val(profit_percent.toFixed(2)+'%');
	}
	
	calc_all();
	$('.calc_watch').change(calc_all);
	$('#actual_rate_fuel_surcharge').change(function() {
		load_cust_info($('#customer_id').val());
		calc_all();
	});
	
	$('.xlocked').attr('readonly','readonly');
	
	function mrr_save_stoplight_notes(cust)
	{
		new_info=($('#cust_stoplight_warn_notes').val());
		
		$.ajax({
			url: "ajax.php?cmd=mrr_save_stoplight_warning_notes",
			data: {
					"cust_id" : cust,
					"cust_notes": new_info          					
				},
			type: "POST",
			cache:false,
			async:false,
			dataType: "xml",
			success: function(xml) {
				if($(xml).find('mrrTab').text() == "Not Found") {
					$.prompt("Error: Stoplight Notes could not be updated.");					
				}
				else
				{
					$.noticeAdd({text: "Success - Stoplight Notes have been updated."});			
				}				
			}
     	});
		
	}	
</script>
<?
/* Page Load speed checking code. */
$mrr_debug_time_end=date("His");
echo "
	<br><b>PHP Page Load:</b>
	<br>Start Time: ".$mrr_debug_time_start."
	<br>End Time: ".$mrr_debug_time_end."
	<br>Load Time: ".number_format(($mrr_debug_time_end - $mrr_debug_time_start),4)." Seconds.
	<br><div id='ajax_time_keeper'></div>
";
?>