<? ini_set("max_input_vars","20000");	//must change in INI file...  ?>
<? include('application.php') ?>
<?
	if(!isset($_GET['load_id']))		$_GET['load_id']=0;
	if(!isset($_POST['load_id']))		$_POST['load_id']=0;
	
	if(!isset($_GET['cust_id']))		$_GET['cust_id']=0;
	if(!isset($_POST['cust_id']))		$_POST['cust_id']=0;
	
	if(!isset($_GET['copy_count']))	$_GET['copy_count']=0;
	if(!isset($_POST['copy_count']))	$_POST['copy_count']=0;
	
	if(!isset($_GET['copy_mode']))	$_GET['copy_mode']=1;
	if(!isset($_POST['copy_mode']))	$_POST['copy_mode']=1;
	
	if($_GET['load_id'] > 0 && $_POST['load_id']==0) 			$_POST['load_id'] = $_GET['load_id'];
	if($_GET['cust_id'] > 0 && $_POST['cust_id']==0) 			$_POST['cust_id'] = $_GET['cust_id'];
	if($_GET['copy_count'] > 0 && $_POST['copy_count']==0) 	$_POST['copy_count'] = $_GET['copy_count'];
	if($_GET['copy_mode'] > 0 && $_POST['copy_mode']==0) 		$_POST['copy_mode'] = $_GET['copy_mode'];
	
	$usetitle = "Duplicate Master Load";
	$use_title = "Duplicate Master Load";
	
	
	$rep_list="";
	$max_new_loads=25;
	$load_arr[0]=0;
	for($i=0;$i < $max_new_loads; $i++)		$load_arr[ $i ]=0;
	
	$dup_id=0;
	$new_loads=0;
	$mrr_activity_log_notes="";
	
	$mrr_driver_id=0;	
	$mrr_driver2_id=0;
	$mrr_truck_id=0;	
	$mrr_trailer_id=0;		
	
	$pdate1="".date("m/d/Y",time())."";
	$pdate2="".date("m/d/Y",time())."";	
	
	$mrr_load_id=$_POST['load_id'];
	$mrr_cust_id=$_POST['cust_id'];  			//...customer is only necessary to pick out the master load to copy...nothing else.
	$mrr_copy_count=$_POST['copy_count'];
	$mrr_copy_mode=$_POST['copy_mode'];
		
	$fuel_surcharge=0;
	if($mrr_cust_id > 0 && $mrr_cust_id!=7)
	{
     	$sql = "
     		select customers.fuel_surcharge,
     			customers.name_company,
     			customers.id,
     			(
     				select fuel_surcharge.fuel_surcharge 
     				from fuel_surcharge 
     				where customer_id = customers.id 
     					and fuel_surcharge.range_lower <= ".sql_friendly(trim($defaultsarray['fuel_surcharge']))." 
     				order by fuel_surcharge desc limit 1 
     			) as surcharge_list
     		
     		from customers 
     		where customers.id = '".sql_friendly($mrr_cust_id)."'
     			and customers.active = 1
     			and use_fuel_surcharge > 0
     		 	
     		 having surcharge_list > 0 or fuel_surcharge > 0
     		order by name_company
     	";
     	$data_surcharge = simple_query($sql);
     	if($row_surcharge = mysqli_fetch_array($data_surcharge)) 
     	{
     		$fuel_surcharge=$row_surcharge['surcharge_list'];
     		if($row_surcharge['fuel_surcharge'] > 0)		$fuel_surcharge=$row_surcharge['fuel_surcharge'];		//flat rate surcharge...		     		
     	}
	}
		
	if(isset($_POST['make_loads']))
	{
		$dup_id=$mrr_load_id;				//load ID to copy...complete with dispatches.
		$new_loads=$mrr_copy_count;			//number of copies to make.
		
		//$dup_id=0;						//KILL Switch
		
		for($i=0; $i < $new_loads; $i++)
		{
			$load_arr[ $i ]=0;	
			
			$has_id=(int) $_POST["made_load_".$i.""];		//see if there is already an ID made for it...to report
			
			$pickup= $_POST["pickup_".$i.""];
     		$dropoff= $_POST["dropoff_".$i.""];
     		$driver1=(int) $_POST["driver_id_".$i.""];
     		$driver2=(int) $_POST["driver2_id_".$i.""];
     		$truck=(int) $_POST["truck_id_".$i.""];
     		$trailer=(int) $_POST["trailer_id_".$i.""];
			
			if($has_id > 0)
			{
				$load_arr[ $i ]=$has_id;	
				
				$rep_list.="<br>Existing Load ". $load_arr[ $i ].".";	
			}
			else
			{				
     			$load_arr[ $i ]=mrr_carbon_copy_load($dup_id,$driver1,$driver2,$truck,$trailer,$pickup,$dropoff,$mrr_copy_mode);   
     			
     			$rep_list.="<br>Added Copy ".($i+1).": Load ". $load_arr[ $i ].". Uses Driver ".$driver1." {Driver2 ".$driver2."} with Truck ".$truck." and Trailer ".$trailer.".  Pickup ".$pickup." Dropoff ".$dropoff.".";				
			}			
		}
	}
    
	function mrr_date_diff($date_1,$date_2)
    {
         $datetime1 = date_create($date_1);
         $datetime2 = date_create($date_2);
         
         $interval = date_diff($datetime1, $datetime2);
         
         $interval = abs($interval->format('%a'));
         
         if($date_1 < $date_2)      $interval=($interval * -1);
         
         return $interval;
    }
	
	function mrr_carbon_copy_load($dup_id,$driver1=0,$driver2=0,$truck=0,$trailer=0,$pickup="",$dropoff="",$mrr_copy_mode)
	{
		$new_load_id=0;
		
		if($dup_id==0)			return $new_load_id;	
		
		//if($trailer==0)		    $trailer=551;       
		if(trim($pickup)=="")	$pickup="".date("m/d/Y",time())."";	
		if(trim($dropoff)=="")	$dropoff="".date("m/d/Y",time())."";	
				
		global $defaultsarray;
		global $fuel_surcharge;
		
		$mrr_activity_log_notes="";
         
		//$trailer=551;           //OVERRIDE, trailer is set to UNKNOWN TRAILER as of 10/19/2020 for Justin.  MRR
        $trailer=0;
				
		// get a list of variable expenses (for the dispatch level)the user can enter for the quote
     	
		
		//default settings used for budget items
		/*
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
		*/
		
		$sql = "
			select load_handler.*,
				(select count(load_handler_stops.id) from load_handler_stops where load_handler_stops.load_handler_id=load_handler.id and load_handler_stops.deleted=0) as stop_count
			
			from load_handler
			where load_handler.id = '".sql_friendly($dup_id)."'
		";
		$data_dup = simple_query($sql);
		$row_dup = mysqli_fetch_array($data_dup);
		
		$offset_days=0;
		$new_date=date("Y-m-d",strtotime($pickup))." 00:00:00";
		$old_date=date("Y-m-d",strtotime($row_dup['linedate_pickup_eta']))." 00:00:00";
		
        $cur_date_mon2=date("Y-m-d",time());
        $new_date_mon2=date("Y-m-d",strtotime($new_date));
         
        $cur_date_yr=(int) date("Y",time());
        $new_date_yr=(int) date("Y",strtotime($new_date));
         
        $cur_date_mon=(int) date("m",time());
        $new_date_mon=(int) date("m",strtotime($new_date));
         
        $new_date_yr3=(int) date("Y",strtotime($old_date));
        $new_date_mon3=(int) date("m",strtotime($old_date));
		
        if(trim($row_dup['linedate_pickup_eta'])!="0000-00-00 00:00:00" && $new_date!=$old_date)
		{
            $date1x=date("Y-m-d",strtotime($pickup));
            $date2x=date("Y-m-d",strtotime($row_dup['linedate_pickup_eta']));
            $offset_days = mrr_date_diff($date1x, $date2x);
		    	    
		    //$offset_days=(int) ((strtotime($new_date) - strtotime($old_date)) / (60*60*24));		//number of days between the current 	
			
			$before=date("Y-m-d",strtotime($row_dup['linedate_pickup_eta']));
			$after=date("Y-m-d",strtotime($pickup));
			
			//if($before <= '2016-02-28' && $after >='2016-03-01')		$offset_days++;			//account for leap year...MySQL date needs extra one.
            //if($before <= '2020-02-28' && $after >='2020-03-01')		$offset_days++;			//account for leap year...MySQL date needs extra one.
			
			//if(date("Y",strtotime($row_dup['linedate_pickup_eta'])) < date("Y",strtotime($pickup)))	$offset_days++;
             
            //if($new_date_mon2 != $cur_date_mon2)	                    $offset_days++;         //current date is not equal to the new date, so add 1 to the offset.
            
            //Cur Mon should be 3, Cur Year should be 2021
            //New Mon should be 3, New Year should be 2021  --- load 1
            //New Mon should be 4, New Year should be 2021  --- load 2 
                       
            if($new_date_yr3 < $cur_date_yr )
            {
                //don't do anything...                
            }
            else
            {   //add a day for the new year.
                //$offset_days++;
            }
            
            if($new_date_mon >= $cur_date_mon && $new_date_yr >= $cur_date_yr)
            {   //date is in the future...
                //do nothing at this time...should not need the offset incremented
                 
                 //Load 1 --- 3 >= 3 && 2021>=2021  ...both true... should use this block
                 //Load 2 --- 4 >= 3 && 2021>=2021  ...both true... should use this block.
            } 
            else
            {   //date was in the past, so needs the extra offset.
                //$offset_days++;
            }
             
		}
               		
		$new_pickup=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($row_dup['linedate_pickup_eta']));
		$new_dropoff=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($row_dup['linedate_dropoff_eta']));
		
		$new_load_id = duplicate_row('load_handler', $dup_id);
		
		$date_adder="
			linedate_pickup_eta=DATE_ADD(linedate_pickup_eta, INTERVAL ".$offset_days ." DAY),
			linedate_dropoff_eta=DATE_ADD(linedate_dropoff_eta, INTERVAL ".$offset_days ." DAY),
		";
		if($row_dup['stop_count']>=2 && $mrr_copy_mode==0)
		{	//no need for offset, just use the dates given...			
			$date_adder="
				linedate_pickup_eta='".$new_pickup."',
				linedate_dropoff_eta='".$new_dropoff."',
			";
			//$offset_days=0;
			//die("1. hit here! ".$row_dup['stop_count']." Stops Found.");
		}		
		
		$avg_surcharge=trim($defaultsarray['fuel_surcharge']);
		$avg_mpg=trim($defaultsarray['average_mpg']);
		$avg_per_mile = (floatval($avg_surcharge) / floatval($avg_mpg));
					
		$sql = "
			update load_handler set			
				
				actual_bill_customer='".sql_friendly($row_dup['actual_bill_customer'])."',
				
				actual_rate_fuel_surcharge = '".sql_friendly(money_strip($avg_surcharge))."',
				actual_fuel_charge_per_mile = '".sql_friendly(money_strip($avg_per_mile))."',
				actual_fuel_surcharge_per_mile = '".sql_friendly(money_strip($fuel_surcharge))."',
							
				master_load=0,
				master_load_label='',
				invoice_number='',
				sicap_invoice_number='',
				sicap_invoice_amount='0.00',
				
				linedate_edi_response_sent='0000-00-00 00:00:00',
				linedate_edi_invoice_sent='0000-00-00 00:00:00',
				fedex_edi_input_file='',
				fedex_edi_output_file='',
				feded_edi_invoice_file='',
				fedex_edi_invoice_file_text='',
				
				geotab_load_msg_id='',
				
				load_number='',
				pickup_number='',
				delivery_number='',
				billing_notes='',
				driver_notes='',
				
				linedate_invoiced='0000-00-00 00:00:00',
				".$date_adder."
				preplan='0',
				preplan_driver_id='".sql_friendly($driver1)."',
				preplan_driver2_id='".sql_friendly($driver2)."',				
				created_by_id='".sql_friendly($_SESSION['user_id'])."',
				linedate_added=NOW()
			
			where id='".sql_friendly($new_load_id)."'
		";		//
				//
				//linedate_dropoff_eta='".$new_dropoff."',
				//linedate_pickup_eta='".$new_pickup."',
		simple_query($sql);
				
		$mrr_activity_log_notes.="Duplicate Load ".$dup_id." info. ";
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,$new_load_id,0,0,"Duplicated Load ".$dup_id." to ".$new_load_id." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		
		
		// duplicate the variable expenses (quote wise)
		$sql = "
			select *			
			from load_handler_quote_var_exp
			where load_handler_id = '".sql_friendly($dup_id)."'
		";
		$data_var_exp = simple_query($sql);
		while($row_var_exp = mysqli_fetch_array($data_var_exp)) 
		{
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
		
		// duplicate the ACTUAL expenses
		$sql = "
			select *
     		from load_handler_actual_var_exp
			where load_handler_id = '".sql_friendly($dup_id)."'
		";
		$data_var_exp = simple_query($sql);		
		while($row_var_exp = mysqli_fetch_array($data_var_exp)) 
		{
			$sql = "
				insert into load_handler_actual_var_exp
					(load_handler_id,
					expense_type_id,
					expense_amount)
					
				values ('".sql_friendly($new_load_id)."',
					'".sql_friendly($row_var_exp['expense_type_id'])."',
					'".sql_friendly($row_var_exp['expense_amount'])."')
			";
			simple_query($sql);
		}
		
		/*
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
		while($row_expenses_variable = mysqli_fetch_array($data_expenses_variable)) 
		{     		
     		$sql = "
          		select dispatch_expenses.*
     			from dispatch_expenses, trucks_log
     			where dispatch_expenses.deleted = 0
     				and dispatch_expenses.dispatch_id = trucks_log.id
     				and trucks_log.load_handler_id = '".sql_friendly($dup_id)."'
     				and dispatch_expenses.expense_type_id = '$row_expenses_variable[id]'
     		";
     		$data_texpense = simple_query($sql);
     		while($row_texpense = mysqli_fetch_array($data_texpense))
     		{
     			//$use_expense = $row_texpense['total_expense_amount'];
     		}
     			
		}			
     	
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
     	while($row_expenses_variable = mysqli_fetch_array($data_expenses_variable_lh)) 
     	{
     		$sql = "
				select expense_amount
				
				from load_handler_quote_var_exp
				where load_handler_id = '".sql_friendly($dup_id)."'
					and expense_type_id = '".sql_friendly($row_expenses_variable['id'])."'
			";
			$data_this_expense = simple_query($sql);
			while($row_this_expense = mysqli_fetch_array($data_this_expense))
     		{
     			//$use_expense = $row_texpense['total_expense_amount'];
     			
     			$row_this_expense = mysqli_fetch_array($data_this_expense);
     			//$use_expense = $row_this_expense['expense_amount'];
     		}
     	}
		*/
		
		//Now copy all the stops...from the old load to the new one.
		$cntr=0;
		$sqlx = "
			select id,linedate_pickup_eta,linedate_dropoff_eta,linedate_appt_window_start,linedate_appt_window_end	
			from load_handler_stops
			where deleted=0 
				and load_handler_id = '".sql_friendly($dup_id)."'
			order by linedate_pickup_eta asc
		";
		$datax = simple_query($sqlx);
		while($rowx = mysqli_fetch_array($datax))
		{
			$stop_id=$rowx['id'];
			$new_stop_id = duplicate_row('load_handler_stops', $stop_id);
			
			//update fields that need to be blanked out...or modified (dates).
			
			//$new_pickup_start=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_start']));
			//$new_pickup_end=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_end']));
						
			//$new_pickup=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_pickup_eta']));
			//$new_dropoff=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($rowx['linedate_dropoff_eta']));
			
			$date_adderu="";
     		if($row_dup['stop_count']==2 && $mrr_copy_mode==0)
     		{	//no need for offset, just use the dates given...	
     			if($cntr==0)
     			{	
     				$new_pickup_start=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_start']));
					$new_pickup_end=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_end']));
						
					$new_pickup=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_pickup_eta']));
					$new_dropoff=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_dropoff_eta']));
     			}
     			elseif($cntr==1)
     			{
     				$new_pickup_start=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_start']));
					$new_pickup_end=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_end']));
						
					$new_pickup=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($rowx['linedate_pickup_eta']));
					$new_dropoff=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($rowx['linedate_dropoff_eta']));
     			}	
     			$date_adderu="
     				linedate_pickup_eta='".$new_pickup."',
     				linedate_dropoff_eta='".$new_dropoff."',
     				linedate_appt_window_start='".$new_pickup_start."',
     				linedate_appt_window_end='".$new_pickup_end."',
     			";
     		}
     		elseif($row_dup['stop_count'] > 2 && $mrr_copy_mode==0)
     		{	//no need for offset, just use the dates given...	
     			if($cntr <= 1)
     			{	
     				$new_pickup_start=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_start']));
					$new_pickup_end=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_end']));
						
					$new_pickup=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_pickup_eta']));
					$new_dropoff=date("Y-m-d",strtotime($pickup))." ".date("H:i:s",strtotime($rowx['linedate_dropoff_eta']));
     			}
     			elseif($cntr > 1)
     			{
     				$new_pickup_start=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_start']));
					$new_pickup_end=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($rowx['linedate_appt_window_end']));
						
					$new_pickup=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($rowx['linedate_pickup_eta']));
					$new_dropoff=date("Y-m-d",strtotime($dropoff))." ".date("H:i:s",strtotime($rowx['linedate_dropoff_eta']));
     			}	
     			$date_adderu="
     				linedate_pickup_eta='".$new_pickup."',
     				linedate_dropoff_eta='".$new_dropoff."',
     				linedate_appt_window_start='".$new_pickup_start."',
     				linedate_appt_window_end='".$new_pickup_end."',
     			";
     		}
     		else
     		{
     			$date_adderu="
     				linedate_pickup_eta=DATE_ADD(linedate_pickup_eta, INTERVAL ".$offset_days ." DAY),
     				linedate_dropoff_eta=DATE_ADD(linedate_dropoff_eta, INTERVAL ".$offset_days ." DAY),
     				linedate_appt_window_start=DATE_ADD(linedate_appt_window_start, INTERVAL ".$offset_days ." DAY),
     				linedate_appt_window_end  =DATE_ADD(linedate_appt_window_end,   INTERVAL ".$offset_days ." DAY),
     			";
     		}
     		
			$sqlu = "
     			update load_handler_stops set
     				load_handler_id='".sql_friendly($new_load_id)."',
     				".$date_adderu."
     				
     				linedate_completed='0000-00-00 00:00:00',
     				linedate_arrival='0000-00-00 00:00:00',
     				linedate_updater='0000-00-00 00:00:00',
     				
     				linedate_geofencing_arriving='0000-00-00 00:00:00',
     				linedate_geofencing_arrived='0000-00-00 00:00:00',
     				linedate_geofencing_departed='0000-00-00 00:00:00',
     				
     				trucks_log_id=0,
     				start_trailer_id='551',
     				end_trailer_id='551',	
     				
     				start_trailer_id='".sql_friendly($trailer)."',
     				end_trailer_id='".sql_friendly($trailer)."',	
     				stop_grade_id=0,     				
     				stop_grade_note='',
     				pn_dispatch_id='',
     				pn_stop_id='',
     				
     				lynnco_edi_status='',
     				
     				geofencing_arriving_sent=0,
     				geofencing_arrived_sent=0,
     				geofencing_departed_sent=0,
     				
     				pro_miles_dist='0.00',
     				pro_miles_eta='0.00',
     				pro_miles_due='0.00',
     				
     				grade_fault_id=0,
     				grade_fault_driver_id=0,
     				grade_fault_customer_id=0,
     				grade_fault_truck_id=0,
     				grade_fault_trailer_id=0,
     				stoplight_warning_flag=0,
     				
     				geotab_stop_msg_id='',
     							
     				created_by_user_id='".sql_friendly($_SESSION['user_id'])."',
     				linedate_added=NOW()
     			
     			where id='".sql_friendly($new_stop_id)."'
     		";
     		simple_query($sqlu);
     		$cntr++;
            
     		//set the trailer for these stops...
     		$sqlu = "
     			update load_handler_stops set
     				
     				start_trailer_id='".sql_friendly($trailer)."',
     				end_trailer_id='".sql_friendly($trailer)."'
     			
     			where id='".sql_friendly($new_stop_id)."'
     		";
            simple_query($sqlu);
		}
         
		
		
        return $new_load_id;           //Kill switch for dispatches... turned off on 09/25/2020 at Justin's request ...MRR  Only want to copy load and stops from now on.
        
        
         /*
         //Now copy all the dispatches...from the old load to the new one.
		$tcntr=0;
		$sqlx = "
			select id		
			from trucks_log
			where deleted=0 
				and load_handler_id = '".sql_friendly($dup_id)."'
		";
		$datax = simple_query($sqlx);
		while($rowx = mysqli_fetch_array($datax))
		{
			$dispatch_id=$rowx['id'];
			$new_disp_id = duplicate_row('trucks_log', $dispatch_id);
			
			$new_base_date=date("Y-m-d",strtotime($pickup))." 00:00:00";			//.date("H:i:s",strtotime($rowx['linedate']))
			$new_pickup=date("Y-m-d",strtotime($pickup))." 00:00:00";		//.date("H:i:s",strtotime($rowx['linedate_pickup_eta']))
			$new_dropoff=date("Y-m-d",strtotime($dropoff))." 00:00:00";	//.date("H:i:s",strtotime($rowx['linedate_dropoff_eta']))
			
			$date_adderu="
     			linedate_pickup_eta=DATE_ADD(linedate_pickup_eta, INTERVAL ".$offset_days ." DAY),
     			linedate_dropoff_eta=DATE_ADD(linedate_dropoff_eta, INTERVAL ".$offset_days ." DAY),
     			linedate=DATE_ADD(linedate_pickup_eta,  INTERVAL ".$offset_days ." DAY),
     		";
     		if($row_dup['stop_count']==2 && $mrr_copy_mode==0)
     		{	//no need for offset, just use the dates given...			
     			$date_adderu="
     				linedate_pickup_eta='".$new_pickup."',
     				linedate_dropoff_eta='".$new_dropoff."',
     				linedate='".$new_base_date."',
     			";
     		}	
     		elseif($row_dup['stop_count'] > 2 && $mrr_copy_mode==0)
     		{
     			if($tcntr==0)
     			{
     				$new_base_date=date("Y-m-d",strtotime($pickup))." 00:00:00";			//.date("H:i:s",strtotime($rowx['linedate']))
					$new_pickup=date("Y-m-d",strtotime($pickup))." 00:00:00";		//.date("H:i:s",strtotime($rowx['linedate_pickup_eta']))
					$new_dropoff=date("Y-m-d",strtotime($pickup))." 00:00:00";	//.date("H:i:s",strtotime($rowx['linedate_dropoff_eta']))
     			}
     			else
     			{
     				$new_base_date=date("Y-m-d",strtotime($dropoff))." 00:00:00";			//.date("H:i:s",strtotime($rowx['linedate']))
					$new_pickup=date("Y-m-d",strtotime($dropoff))." 00:00:00";		//.date("H:i:s",strtotime($rowx['linedate_pickup_eta']))
					$new_dropoff=date("Y-m-d",strtotime($dropoff))." 00:00:00";	//.date("H:i:s",strtotime($rowx['linedate_dropoff_eta']))
     			}
     			$date_adderu="
     				linedate_pickup_eta='".$new_pickup."',
     				linedate_dropoff_eta='".$new_dropoff."',
     				linedate='".$new_base_date."',
     			";
     		}		
			
			//update fields that need to be blanked out...or modified (dates).                
			
			$sqlu = "
     			update trucks_log set
     				load_handler_id='".sql_friendly($new_load_id)."',
     				driver_id='".sql_friendly($driver1)."',
     				driver2_id='".sql_friendly($driver2)."',
     				truck_id='".sql_friendly($truck)."',
     				trailer_id='".sql_friendly($trailer)."',
     				
     				".$date_adderu."
     				linedate_updated='0000-00-00 00:00:00',
     				     				
     				valid_trip_pack=0,
     				user_id_verified_trip_pack=0,
     				dispatch_completed=0,
     				dropped_trailer=0,
         
                    geotab_msg_id='',
     							
     				user_id='".sql_friendly($_SESSION['user_id'])."',
     				linedate_added=NOW()
     			
     			where id='".sql_friendly($new_disp_id)."'
     		";
     		simple_query($sqlu);
     		
     		//now update the new stops with the equivalent new dispatches...based on the last one.
     		$sqlu = "
     			update load_handler_stops set
     				trucks_log_id='".sql_friendly($new_disp_id)."'
     			where trucks_log_id='".sql_friendly($dispatch_id)."' 
     				and load_handler_id='".sql_friendly($new_load_id)."'
     		";
     		simple_query($sqlu);    
     		
     		//Dispatched, so no longer preplanned...
     		$sql = "
     			update load_handler set     				
     				preplan='0'     			
     			where id='".sql_friendly($new_load_id)."'
     		";
     		simple_query($sql); 	
     			
     		$tcntr++;
     		
     		update_origin_dest($new_load_id,1);
		}		
         */
			
		//header("Location: manage_load.php?load_id=".$new_load_id);
		//die;	
		return $new_load_id;	
	}
	
	
	
	//for summary display..................	
	$_POST['cust_addr1'] = "";
	$_POST['cust_addr2'] = "";
	$_POST['cust_city'] = "";
	$_POST['cust_state'] = "";
	$_POST['cust_zip'] = "";
	$_POST['cust_baddr1'] = "";
	$_POST['cust_baddr2'] = "";
	$_POST['cust_bcity'] = "";
	$_POST['cust_bstate'] = "";
	$_POST['cust_bzip'] = "";
	$_POST['cust_email'] = "";
	$_POST['cust_phone'] = "";
	$_POST['cust_phone2'] = "";
	$_POST['cust_fax'] = "";
	$_POST['cust_cont_name'] = "";
	$_POST['cust_cont_email'] = "";
	$_POST['cust_name'] = "";
	
	if($mrr_load_id > 0) 
	{		
		$sql = "
			select load_handler.*,
				customers.address1,
				customers.address2,
				customers.city,
				customers.state,
				customers.zip,
				customers.billing_address1,
				customers.billing_address2,
				customers.billing_city,
				customers.billing_state,
				customers.billing_zip,
				customers.phone_work,
				customers.phone2,
				customers.fax,
				customers.contact_primary,
				customers.contact_email,
				customers.name_company
			
			from load_handler
				left join customers on customers.id=load_handler.customer_id
			where load_handler.id = '".sql_friendly($mrr_load_id)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
			
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
				
		$_POST['cust_addr1'] = $row['address1'];
		$_POST['cust_addr2'] = $row['address2'];
		$_POST['cust_city'] = $row['city'];
		$_POST['cust_state'] = $row['state'];
		$_POST['cust_zip'] = $row['zip'];
		$_POST['cust_baddr1'] = $row['billing_address1'];
		$_POST['cust_baddr2'] = $row['billing_address2'];
		$_POST['cust_bcity'] = $row['billing_city'];
		$_POST['cust_bstate'] = $row['billing_state'];
		$_POST['cust_bzip'] = $row['billing_zip'];
		$_POST['cust_email'] = $row['contact_email'];
		$_POST['cust_phone'] = $row['phone_work'];
		$_POST['cust_phone2'] = $row['phone2'];
		$_POST['cust_fax'] = $row['fax'];
		$_POST['cust_cont_name'] = $row['contact_primary'];
		$_POST['cust_cont_email'] = $row['contact_email'];
		$_POST['cust_name'] = $row['name_company'];
		
		
		if(strtotime($row['linedate_pickup_eta']) > 0) {
			$_POST['pickup_eta'] = date("m/d/Y", strtotime($row['linedate_pickup_eta']));
			$mrr_my_wkday=date("w", strtotime($row['linedate_pickup_eta']));
			if(date("H:i a", strtotime($row['linedate_pickup_eta'])) > 0) $_POST['pickup_eta_time'] = date("H:i", strtotime($row['linedate_pickup_eta']));
			
			$pdate1=$_POST['pickup_eta'];			
		}
		if(strtotime($row['linedate_pickup_pta']) > 0) {
			$_POST['pickup_pta'] = date("m/d/Y", strtotime($row['linedate_pickup_pta']));
			if(date("H", strtotime($row['linedate_pickup_pta'])) > 0) $_POST['pickup_pta_time'] = date("H:i", strtotime($row['linedate_pickup_pta']));
		}
		if(strtotime($row['linedate_dropoff_eta']) > 0) {
			$_POST['dropoff_eta'] = date("m/d/Y", strtotime($row['linedate_dropoff_eta']));
			if(date("H", strtotime($row['linedate_dropoff_eta']))> 0) $_POST['dropoff_eta_time'] = date("H:i", strtotime($row['linedate_dropoff_eta']));
			
			$pdate2=$_POST['dropoff_eta'];
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
		
		//$mrr_driver_id=$row['preplan_driver_id'];	
		//$mrr_driver2_id=$row['preplan_driver2_id'];
        $mrr_driver_id=0;
        $mrr_driver2_id=0;
		$mrr_truck_id=0;	
		$mrr_trailer_id=0;
		
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
				and load_handler_id = '".sql_friendly($mrr_load_id)."'
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
		$tlog_cntr=0;
		
		while($row_disp = mysqli_fetch_array($data_disp)) 
		{			
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
			
			$truck_valid_pn=mrr_validate_peoplenet_truck($row_disp['truck_id']);
			if($truck_valid_pn > 0)		$send_pn_link=1;
			
			if($tlog_cntr==0)
			{			
				$mrr_driver_id=$row_disp['driver_id'];	
				$mrr_driver2_id=$row_disp['driver2_id'];
				$mrr_truck_id=$row_disp['truck_id'];	
				$mrr_trailer_id=$row_disp['trailer_id'];	
			}
			$tlog_cntr++;
		}
		
		if(mysqli_num_rows($data_disp)) 
		{
			$_POST['actual_miles'] = $loaded_miles;
			$_POST['actual_deadhead_miles'] = $deadhead_miles;
			$_POST['actual_days_run_otr'] = $days_run_otr;
			$_POST['actual_days_run_hourly'] = $days_run_hourly;
			$_POST['actual_loaded_miles_hourly'] = $loaded_miles_hourly;
			$_POST['actual_hours_worked'] = $hours_worked;
		}
	}
	
	function mrr_load_these_stops($load_id=0,$disp_id=0) 
	{		
		if($load_id == 0) 		return "";
		
		// get the load info
		$sql = "
			select *
			
			from load_handler
			where id = '".sql_friendly($load_id)."'
		";
		$data_load = simple_query($sql);
		$row_load = mysqli_fetch_array($data_load);
				
		$sql = "
			select *,
				(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
				(select trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name,
				date_format(linedate_completed, '%Y-%m-%d') as linedate_completed_date,
				date_format(linedate_completed, '%H:%i') as linedate_completed_time,
				date_format(linedate_arrival, '%Y-%m-%d') as linedate_arrival_date,
				date_format(linedate_arrival, '%H:%i') as linedate_arrival_time
			
			from load_handler_stops
			where load_handler_id = '".sql_friendly($load_id)."'
				and deleted = 0
				".( $disp_id > 0 ? " and (trucks_log_id is null or trucks_log_id = 0 or trucks_log_id = '".$disp_id."') " : "")."
			order by linedate_pickup_eta, linedate_pickup_pta, linedate_dropoff_eta
		";
		$data = simple_query($sql);
		
		$sql = "
			select *,
				(select trailer_name from trailers where trailers.id=trucks_log.trailer_id) as mrr_trailer_name,
				(select dedicated_trailer from trailers where trailers.id=trucks_log.trailer_id) as mrr_dedicated_trailer
			
			from trucks_log
			where load_handler_id = '".sql_friendly($load_id)."'
				and deleted = 0
		";
		$data_dispatch = simple_query($sql);
		
		$mrr_title="";
		$disphtml="";	//removed PreDispatch section above...
		
		$disphtml .= "
			<table width='100%'>
			<tr>
				<td nowrap><b>Stop ID</b></td>
				<td nowrap><b>Stop Type</b></td>
				<td><b>Name</b></td>
				<td nowrap><b>City / State</b></td>
				<td nowrap align='right'><b>Miles</b></td>
				<td><b>Appointment</b></td>
				<td><b>Dispatch ID</b></td>
				<td nowrap><b>Trailer</b></td>
				<td nowrap><b>Switch</b></td>
			</tr>
		";
	
		$last_dispatch_id = 0;
		$pcm_miles_total = 0;
		$prev_city_state = "";
		while($row = mysqli_fetch_array($data)) 
		{
			$city_state = "$row[shipper_city]".($row['shipper_state'] != '' ? ', '.$row['shipper_state'] : '');
			$mrr_city=$row['shipper_city'];
			$mrr_state=$row['shipper_state'];
			$graded=$row['stop_grade_id'];
			$graded_note=$row['stop_grade_note'];	
			
			$dispatch_completed=0;
			
			if($last_dispatch_id != $row['trucks_log_id']) {
				if($last_dispatch_id != 0) {
					$disphtml .= "
						<tr>
							<td colspan='17'><hr></td>
						</tr>
					";
				}
				$last_dispatch_id = $row['trucks_log_id'];
			}
						
			$prev_city_state = $city_state;			
			
			$disphtml .= "
				<tr style='font-size:10px' id='stop_id_$row[id]'>
					<td nowrap>$row[id]	</td>
					<td nowrap>".($row['stop_type_id'] == '1' ? "Shipper" : "Consignee")."</td>
					<td nowrap>$row[shipper_name]</td>
					<td nowrap>$city_state</td>
					<td align='right'><span class='pcm_miles'>$row[pcm_miles]</span></td>
					<td nowrap>".(strtotime($row['linedate_pickup_eta']) <= 0 ? '' : date("M d, Y", strtotime($row['linedate_pickup_eta'])))." ".time_prep($row['linedate_pickup_eta'])."</td>
			";
			
			$mrr_trailer_id=0;
			$mrr_driver_id=0;
			$mrr_customer_id=0;
			$mrr_dedicated_id=0;
			$mrr_notes="Quick Trailer Drop.";
			$mrr_sel_opt="";
			
			$mrr_start_trailer="";
			$mrr_end_trailer="";
			
			$stop_starting_trailer_id=$row['start_trailer_id'];
			$stop_starting_trailer_name=$row['start_trailer_name'];
			$stop_ending_trailer_id=$row['end_trailer_id'];
			$stop_ending_trailer_name=$row['end_trailer_name'];	
			
			if($stop_starting_trailer_id > 0)								$mrr_start_trailer="".$stop_starting_trailer_name."";
			if($stop_ending_trailer_id > 0)								$mrr_end_trailer="".$stop_ending_trailer_name."";
			if($stop_starting_trailer_id > 0 && $stop_ending_trailer_id == 0)	$mrr_end_trailer="Drop";	
							
			$disphtml .= "	
					<td nowrap>".$row['trucks_log_id']."</td>				
					<td nowrap>
						<span id='stop_".$row['id']."_trailer_start'>".$mrr_start_trailer."</span>
					</td>
					<td nowrap>
						<span id='stop_".$row['id']."_trailer_switch'>".$mrr_end_trailer."</span>
					</td>
				</tr>
			";		
		}		
		
		$disphtml .= "</table>";
		
		return $disphtml;
	}
	
	function mrr_load_these_dispatchs($load_id=0) 
	{
		if($load_id == 0) return "";
						
		$sql = "
			select trucks_log.*,
				trucks.name_truck,
				trailers.trailer_name,
				concat(drivers.name_driver_first, ' ', drivers.name_driver_last) as driver_name
			
			from trucks_log
				left join trucks on trucks_log.truck_id = trucks.id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join drivers on drivers.id = trucks_log.driver_id
			where trucks_log.load_handler_id = '".sql_friendly($load_id)."'
				and trucks_log.deleted = 0
			
			order by trucks_log.linedate_pickup_eta, trucks_log.linedate, trucks_log.id
		";
		$data_dispatch = simple_query($sql);	
		$data_dispatch2 = simple_query($sql);
		
		$html = "
			<table width='100%'>
			<tr>
				<td><b>Dispatch ID</b></td>
				<td><b>Truck</b></td>
				<td><b>Trailer</b></td>				
				<td><b>Driver</b></td>
				<td align='right'><b>PC*M</b></td>
				<td align='right'><b>Miles</b></td>
				<td align='right'><b>Deadhead</b></td>
				<td><b>Origin</b></td>
				<td><b>Dest</b></td>
				<td><b>Date</b></td>
			</tr>
		";	
		if(!isset($data_dispatch) || !mysqli_num_rows($data_dispatch) ) { 
			$html .= "
				<tr>
					<td colspan='10'>
						No dispatches associated with this load yet
					</td>
				</tr>
			";
		} else {
			$total_loaded_miles = 0;
			$total_deadhead_miles = 0;
			$last_dispatch_id = 0;
			$total_pcm_miles = 0;
			$total_profit=0;
			$total_cost=0;
						
			//determine "primary" dispatch for cost/profit display.
			$prime_dispatch_id=0;
			$prime_dispatch_miles=0;
			
			while($row_dispatch2 = mysqli_fetch_array($data_dispatch2)) 
			{					
				if($prime_dispatch_id==0)
				{
					$prime_dispatch_id=$row_dispatch2['id'];
					$prime_dispatch_miles=($row_dispatch2['miles'] + $row_dispatch2['miles_deadhead'] + $row_dispatch2['pcm_miles']);
					//$total_cost=$row_dispatch2['cost'];
					//$total_profit=$row_dispatch2['profit'];
				}
				elseif(($row_dispatch2['miles'] + $row_dispatch2['miles_deadhead'] + $row_dispatch2['pcm_miles']) > $prime_dispatch_miles)
				{
					$prime_dispatch_miles=($row_dispatch2['miles'] + $row_dispatch2['miles_deadhead'] + $row_dispatch2['pcm_miles']);
					$prime_dispatch_id=$row_dispatch2['id'];
					//$total_cost=$row_dispatch2['cost'];
					//$total_profit=$row_dispatch2['profit'];
				}								
			}
			//........................totals found for profit and cost
			
					
			while($row_dispatch = mysqli_fetch_array($data_dispatch)) 
			{
				$total_loaded_miles += $row_dispatch['miles'];
				$total_deadhead_miles += $row_dispatch['miles_deadhead'];
				$total_pcm_miles += $row_dispatch['pcm_miles'];
				$switch_notes="";
																
				$html .= "
					<tr>
						<td valign='top'>$row_dispatch[id]</td>
						<td valign='top'>$row_dispatch[name_truck]</td>
						<td valign='top'>$row_dispatch[trailer_name]</td>						
						<td valign='top'>$row_dispatch[driver_name]</td>
						<td valign='top' align='right'>".number_format($row_dispatch['pcm_miles'])."</td>
						<td valign='top' align='right'>".number_format($row_dispatch['miles'])."</td>
						<td valign='top' align='right'>".number_format($row_dispatch['miles_deadhead'])."</td>
						<td valign='top'>$row_dispatch[origin], $row_dispatch[origin_state]</td>
						<td valign='top'>$row_dispatch[destination], $row_dispatch[destination_state]</td>
						<td valign='top'>".date("n-j-Y", strtotime($row_dispatch['linedate']))."</td>
					</tr>
				";	
			}
			$html .= "
				<tr>
					<td colspan='4'></td>
					<td align='right' style='border-top:1px black solid'>".number_format($total_pcm_miles)."</td>
					<td align='right' style='border-top:1px black solid'>".number_format($total_loaded_miles)."</td>
					<td align='right' style='border-top:1px black solid'>".number_format($total_deadhead_miles)."</td>
					<td colspan='3'></td>
					
				</tr>
			";
		}
		$html .= "</table>";
		return $html;
	}	
?>
<? include('header.php') ?>
<h1><?=$use_title ?> <?=$mrr_load_id ?></h1><br>
<table>
<tr>
	<td valign='top'>	
		
		<h2>Make Multiple Copies of the selected Master Load.</h2>
		
		<form name='mainform' action='<?=$_SERVER['SCRIPT_NAME']?>' method='post' style='text-align:left'>
			
			<table width='900' cellspacing='0' cellpadding='0' border='0'>
			<tr>
				<td valign='top' colspan='3' align='center'>&nbsp;</td>
			</tr>
			<?
			if(!is_numeric($mrr_cust_id))			$mrr_cust_id=0;
			if(!is_numeric($mrr_load_id))			$mrr_load_id=0;
			if(!is_numeric($mrr_copy_count))		$mrr_copy_count=0;
			
			//customer
			$sel_cust="<select name='cust_id' id='cust_id' onChange='submit();'>";
			
			$sel="";		if($mrr_cust_id==0)		$sel=" selected";
			$sel_cust.="<option value='0'".$sel.">Select Customer</option>";	
			
			$slowpays=0;
			$override_slowpays=0;
			$dirt_bags_flag=0;
			$credit_hold=0;
			$override_credit_hold=0;								
			$document_75k_received=1;
			$document_75k_exempt=0;
								
			$date_75k1=date("Ymd",time());
			$date_75k2=date("Ymd",strtotime("+1 year",time()));
						
			$sqlx = "
				select id,name_company,slow_pays,override_slow_pays,credit_hold,override_credit_hold,
				    document_75k_received,document_75k_exempt,linedate_document_75k,linedate_expires_75k,dirt_bags_flag
				from customers
				where deleted='0'
				order by name_company asc, id asc
			";
			$datax = simple_query($sqlx);	
			while($rowx = mysqli_fetch_array($datax)) 
			{
				$sel="";	
				
				if($rowx['id']==$mrr_cust_id)
				{
					$sel=" selected";
				
					$slowpays=$rowx['slow_pays'];
					$override_slowpays=$rowx['override_slow_pays'];
					$dirt_bags_flag=$rowx['dirt_bags_flag'];
					$credit_hold=$rowx['credit_hold'];
					$override_credit_hold=$rowx['override_credit_hold'];								
					$document_75k_received=$rowx['document_75k_received'];
					$document_75k_exempt=$rowx['document_75k_exempt'];
								
					$date_75k1=date("Ymd",strtotime($rowx['linedate_document_75k']));	if($rowx['linedate_document_75k']<"2010-01-01 00:00:00")		$date_75k1="";
					$date_75k2=date("Ymd",strtotime($rowx['linedate_expires_75k']));		if($rowx['linedate_expires_75k'] <"2010-01-01 00:00:00")		$date_75k2="";
						
					if($date_75k2=="" && $date_75k1!="")
					{
						$date_75k2=date("Ymd",strtotime("+1 year",strtotime($rowx['linedate_document_75k'])));	
					}	
				}
				$sel_cust.="<option value='".$rowx['id']."'".$sel.">".$rowx['name_company']."</option>";	
			}
			$sel_cust.="</select>";
			
			
			//loads
			$sel_loads="<select name='load_id' id='load_id' onChange='submit();'>";
			
			$sel="";		if($mrr_load_id==0)		$sel=" selected";
			$sel_loads.="<option value='0'".$sel.">Select a Load</option>";
			
			$sqlx = "
				select load_handler.id,load_handler.master_load_label
				from load_handler
				where load_handler.deleted='0'
					and master_load > 0
					".($mrr_cust_id > 0 ? " and load_handler.customer_id='".$mrr_cust_id."'" : "")."
				order by load_handler.id desc
			";
			$datax = simple_query($sqlx);	
			while($rowx = mysqli_fetch_array($datax)) 
			{
				$sel="";		if($rowx['id']==$mrr_load_id)		$sel=" selected";
				
				$sel_loads.="<option value='".$rowx['id']."'".$sel.">".$rowx['id'].": ".$rowx['master_load_label']."</option>";	
			}
			$sel_loads.="</select>";
			
			
			//copy count
			$sel_nums="<select name='copy_count' id='copy_count' onChange='submit();'>";
			
			$sel="";		if($mrr_copy_count==0)		$sel=" selected";
			$sel_nums.="<option value='0'".$sel.">0</option>";
			
			for($i=1;$i<= $max_new_loads; $i++)
			{
				$sel="";		if($i==$mrr_copy_count)		$sel=" selected";
				
				$sel_nums.="<option value='".$i."'".$sel.">".$i."</option>";	
			}
			$sel_nums.="</select>";
			
			
			/* get the truck list */			//and in_the_shop=0
          	$sql_trucks = "
          		select *,
          			(select t.name_truck from equipment_history eh, trucks t where eh.equipment_id = t.id and eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_name
          		
          		from trucks
          		where deleted = 0
          			and active = 1
          		order by name_truck asc
          	";
          	//$data_trucks = simple_query($sql_trucks);
			
			/* get the trailer list */		//left join trailers_dropped td on td.trailer_id=trailers.id
          	$sql_trailers = "
          		select *
          		
          		from trailers          			
          		where deleted = 0 
          			and trailers.active = 1
          					
          		order by trailers.trailer_name
          	";									
          	//$data_trailers = simple_query($sql_trailers);
			
			/* get the driver list */
          	$sql_drivers = "
          		select *
          		
          		from drivers
          		where deleted = 0 
          			and active = 1
          		order by active desc, name_driver_last, name_driver_first
          	";
          	//$data_drivers = simple_query($sql_drivers);
			          	
			?>	
			<tr>
				<td valign='top'><b>Customer:</b></td>
				<td valign='top' colspan='2'><?=$sel_cust ?> <?= "Current Fuel Surcharge $".number_format($fuel_surcharge,2)."" ?></td>
			</tr>
			<tr>
				<td valign='top' colspan='3' align='center'>&nbsp;</td>
			</tr>
            <tr>
                <td valign='top' colspan='3' align='center'><b><span style="color:purple;">
                            NOTE: DISPATCHES no longer copied as of 9/25/2020... Loads and Stops Only... thus, TRUCK setting not used. 
                            <br>TRAILER will be set to UNKNOWN TRAILER just in case.
                            <br> DRIVER is also no longer needed.
                        </span></b></td>
            </tr>
            <tr>
                <td valign='top' colspan='3' align='center'>&nbsp;</td>
            </tr>
			<?
			$block_customer_duplication=0;
			$block_customer_msg="";
			
			if($slowpays > 0 && $override_slowpays==0)				{	$block_customer_duplication=1;		$block_customer_msg.="<div style='color:#CC0000;'><b>Customer Pays Slowly</b></div>";	}
			if($credit_hold > 0 && $override_credit_hold==0)		{	$block_customer_duplication=1;		$block_customer_msg.="<div style='color:#CC0000;'><b>Customer Credit is on Hold</b></div>";	}
			if($dirt_bags_flag > 0)									{	$block_customer_duplication=1;		$block_customer_msg.="<div style='color:#CC0000;'><b>Customer is/are DIRT BAG(S)!</b></div>";	}
			
			if($document_75k_received==0 && $document_75k_exempt==0)		
			{	
			    $block_customer_duplication=1;		
			    $block_customer_msg.="<div style='color:#CC0000;'><b>Customer's 75K Bond status unknown</b></div>";	
			}
			if($document_75k_received > 0 && $document_75k_exempt==0)	
			{
				if($date_75k1=="")		{	$block_customer_duplication=1;		$block_customer_msg.="<div style='color:#CC0000;'><b>75K Bond Inception Date is blank.</b></div>";	}
				if($date_75k2=="")		{	$block_customer_duplication=1;		$block_customer_msg.="<div style='color:#CC0000;'><b>75K Bond Expiration/Renewal Date is blank.</b></div>";	}
				if($date_75k2 < date("Ymd",time()))	
				{	//date("Ymd",strtotime("+1 year",time()))
				    $block_customer_duplication=1;		
				    $block_customer_msg.="<div style='color:#CC0000;'><b>75K Bond Expiration/Renewal Date has expired. (".$date_75k2.")</b></div>";	
				}
			}
			
			if($block_customer_duplication==0) {
			?>
			<tr>				
				<td valign='top'><b>Load ID:</b></td>
				<td valign='top' colspan='2'><?=$sel_loads ?></td>
			</tr>
			<tr>
				<td valign='top' colspan='3' align='center'>&nbsp;</td>
			</tr>
			<tr>				
				<td valign='top'><b>Duplicates:</b> <?=$sel_nums ?></td>
				
				<td valign='top'>
                    <b>Mode:</b>
					<select name='copy_mode' id='copy_mode' onChange='submit();'>
					<?
					$sel="";		if($mrr_copy_mode==0)		$sel=" selected";
					echo "<option value='0'".$sel.">Use Drop Date for last stop(s)</option>";
					
					$sel="";		if($mrr_copy_mode==1)		$sel=" selected";
					echo "<option value='1'".$sel.">Use Pickup ETA Offset</option>";
					?>
					</select>
				</td>
				<td valign='top' align='right'><input type='submit' name='reload_id' id='reload_id' value='Review Load(s)'></td>
                <!-----
                 colspan='2'
                 <td valign='top'>
					
				</td>
				<td valign='top'></td>
                ---->
			</tr>	
			<tr>
				<td valign='top' colspan='3' align='center'>&nbsp;</td>
			</tr>				
			<?
			if(trim($rep_list)!="")
			{
				echo "
					<tr>
						<td valign='top' colspan='3' align='center'><h2>Report for Dates</h2><br>".$rep_list."</td>
					</tr>				
					<tr>
						<td valign='top' colspan='3' align='center'>&nbsp;</td>
					</tr>
				";	
			}
			?>					
			<tr>
				<td valign='top' colspan='3' align='lef'><h2>Duplicated Load Base Settings:</h2></td>
			</tr>
			<tr>
				<td valign='top'><b>Load</b></td>
				<td valign='top' nowrap><b>Pickup ETA</b></td>
				<td valign='top' nowrap><b>Dropoff ETA</b></td>
                <!---
				<td valign='top'><b>Driver(s)</b></td>
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>Trailer</b></td>
				----->
			</tr>	
			<?			
			for($i=0;$i< $mrr_copy_count; $i++)
			{
				//Drivers
				if(isset($_POST["driver_id_".$i.""]))			$mrr_driver_id=trim($_POST["driver_id_".$i.""]);
				if(isset($_POST["driver2_id_".$i.""]))			$mrr_driver2_id=trim($_POST["driver2_id_".$i.""]);
				
				//$mrr_driver_id=0;	
				if($mrr_driver_id==0)		$mrr_driver_id=345;			
				$sel_d="<select name='driver_id_".$i."' id='driver_id_".$i."'>";
     			
     			$sel="";		if($mrr_driver_id==0)		$sel=" selected";
     			$sel_d.="<option value='0'".$sel.">Select a Driver</option>";
     			
     			$datax = simple_query($sql_drivers);
     			while($rowx = mysqli_fetch_array($datax)) 
     			{
     				$sel="";		if($mrr_driver_id==$rowx['id'])		$sel=" selected";
     				
     				$sel_d.="<option value='".$rowx['id']."'".$sel.">".$rowx['name_driver_first']." ".$rowx['name_driver_last']."</option>";	
     			}
     			$sel_d.="</select>";
     			
     			//$mrr_driver2_id=0;				
				$sel_d2="<select name='driver2_id_".$i."' id='driver2_id_".$i."'>";
     			
     			$sel="";		if($mrr_driver2_id==0)		$sel=" selected";
     			$sel_d2.="<option value='0'".$sel.">(Select Optional Team Driver)</option>";
     			
     			$datax = simple_query($sql_drivers);
     			while($rowx = mysqli_fetch_array($datax)) 
     			{
     				$sel="";		if($mrr_driver2_id==$rowx['id'])		$sel=" selected";
     				
     				$sel_d2.="<option value='".$rowx['id']."'".$sel.">".$rowx['name_driver_first']." ".$rowx['name_driver_last']."</option>";	
     			}
     			$sel_d2.="</select>";
     			
								
				//Trucks
				if(isset($_POST["truck_id_".$i.""]))			$mrr_truck_id=trim($_POST["truck_id_".$i.""]);
				//$mrr_truck_id=0;				
				$sel_t="<select name='truck_id_".$i."' id='truck_id_".$i."'>";
     			
     			$sel="";		if($mrr_truck_id==0)		$sel=" selected";
     			$sel_t.="<option value='0'".$sel.">Select a Truck</option>";
     			
     			$datax = simple_query($sql_trucks);
     			while($rowx = mysqli_fetch_array($datax)) 
     			{
     				$sel="";		if($mrr_truck_id==$rowx['id'])		$sel=" selected";
     				
     				$sel_t.="<option value='".$rowx['id']."'".$sel.">".$rowx['name_truck']."</option>";	
     			}
     			$sel_t.="</select>";
								
				//Trailers
				if(isset($_POST["trailer_id_".$i.""]))			$mrr_trailer_id=trim($_POST["trailer_id_".$i.""]);
				//$mrr_trailer_id=0;				
				if($mrr_trailer_id==0)		$mrr_trailer_id=551;
							
				$sel_t2="<select name='trailer_id_".$i."' id='trailer_id_".$i."'>";
     			
     			$sel="";		if($mrr_trailer_id==0)		$sel=" selected";
     			$sel_t2.="<option value='0'".$sel.">Select a Trailer</option>";
     			
     			$datax = simple_query($sql_trailers);
     			while($rowx = mysqli_fetch_array($datax)) 
     			{
     				$sel="";		if($mrr_trailer_id==$rowx['id'])		$sel=" selected";
     				
     				$sel_t2.="<option value='".$rowx['id']."'".$sel.">".$rowx['trailer_name']."</option>";	
     			}
     			$sel_t2.="</select>";
				
				$pdate1="".date("m/01/Y",time())."";
				$pdate2="".date("m/01/Y",time())."";
				
				if(isset($_POST["pickup_".$i.""]))		$pdate1=trim($_POST["pickup_".$i.""]);
				if(isset($_POST["dropoff_".$i.""]))	$pdate2=trim($_POST["dropoff_".$i.""]);
					
				
				$loader="Copy ".($i+1)."";
				if($load_arr[ $i ] > 0)  $loader="<a href='manage_load.php?load_id=".$load_arr[ $i ]."' target='_blank'><b>".$load_arr[ $i ]."</b></a>";
				
				echo "
     				<tr class='".($i%2==0 ? "even" : "odd")."'>
     					<td valign='top' nowrap>".$loader."<input type='hidden' name='made_load_".$i."' id='made_load_".$i."' value='".$load_arr[ $i ]."'></td>
     					<td valign='top'><input type='text' class='mrr_input_date' name='pickup_".$i."' id='pickup_".$i."' value='".$pdate1."'></td>
     					<td valign='top'><input type='text' class='mrr_input_date' name='dropoff_".$i."' id='dropoff_".$i."' value='".$pdate2."'></td>
     					<!----
     					<td valign='top'>".$sel_d."<br>".$sel_d2."</td>
     					<td valign='top'>".$sel_t."</td>
     					<td valign='top'>".$sel_t2."</td>
     					----->
     				</tr>
				";
			}
			?>
			<tr>
				<td valign='top'>   <!---- colspan='4'  ----->
					<br><b>Notes:</b>
					<br> Press the "Duplicate Load" button to create <?=$mrr_copy_count ?> copies of Load <?=$mrr_load_id ?> with these Base Settings.
				</td>
				<td valign='top' colspan='2' align='right'><input type='submit' name='make_loads' id='make_loads' value='Duplicate Load'></td>
			</tr>
			<? } else { ?>
			
			<tr>
				<td valign='top' colspan='3' align='center'>
					<b>Customer Load Creation/Duplication has been blocked:</b><br>
					<?= $block_customer_msg	?>
					<p>
						(Please see Dale or James to override.)
					</p>
				</td>
			</tr>
			
			<? } ?>
			</table>
		</form>
	</td>
	<td valign='top' align='right'>	
		<h2>Load <?=$mrr_load_id ?> Summary:</h2>
		
				<? if($mrr_load_id > 0)	{ ?>
						
					<div id='printable_area1'>
               			
                    		<table class='section0_long' border='0'>
                    		<tr>
                    			<td width='100' valign='top'>Load ID</td>
                    			<td valign='top'><b><?=$mrr_load_id ?></b></td>
                    			<td valign='top'>Customer</td>
                    			<td valign='top'><b><?=$_POST['cust_name']?></b></td>
                    		</tr>
                    		<tr>
                    			<td valign='top'>Contact Name</td>
                    			<td valign='top'><b><?=$_POST['cust_cont_name']?></b></td>
                    			<td valign='top'>Contact Email</td>
                    			<td valign='top'><b><?=$_POST['cust_cont_email']?></b></td>
                    		</tr>
                    		<tr>
                    			<td valign='top'>Phone Number</td>
                    			<td valign='top'><b><?=$_POST['cust_phone']?></b></td>
                    			<td valign='top'>Phone Number</td>
                    			<td valign='top'><b><?=$_POST['cust_phone2']?></b></td>
                    		</tr>
                    		<tr>
                    			<td valign='top'>Fax Number</td>
                    			<td valign='top'><b><?=$_POST['cust_fax']?></b></td>
                    			<td valign='top'></td>
                    			<td valign='top'><b></b></b></td>
                    		</tr>
                    			
                    		
                    		<tr>
                    			<td valign='top' colspan='2'>Address</td>
                    			<td valign='top' colspan='2'>Billing Address</td>
                    		</tr>
                    		<tr>
                    			<td valign='top'>Line 1</td>
                    			<td valign='top'><b><?=$_POST['cust_addr1']?></b></td>
                    			<td valign='top'>Line 1</td>
                    			<td valign='top'><b><?=$_POST['cust_baddr1']?></b></td>
                    		</tr>	
                    		<tr>
                    			<td valign='top'>Line 2</td>
                    			<td valign='top'><b><?=$_POST['cust_addr2']?></b></td>
                    			<td valign='top'>Line 2</td>
                    			<td valign='top'><b><?=$_POST['cust_baddr2']?></b></td>
                    		</tr>
                    		<tr>
                    			<td valign='top'>City, State, Zip</td>
                    			<td valign='top'><b><?=$_POST['cust_city']?>, <?=$_POST['cust_state']?> <?=$_POST['cust_zip']?></b></td>
                    			<td valign='top'>City, State, Zip</td>
                    			<td valign='top'><b><?=$_POST['cust_bcity']?>, <?=$_POST['cust_bstate']?> <?=$_POST['cust_bzip']?></b></td>
                    		</tr>		
                    		</table>
                    		
                    		<table class='section1_long' style='width:900px'>		
                    		<tr>
                    			<td colspan='5' id='stop_holder'>
                    				<? echo mrr_load_these_stops($mrr_load_id,0); ?>	
                    			</td>
                    		</tr>
                    		</table>
                    		
                    		<table class='section4_long'>
                    		<tr>
                    			<td colspan='10'>
                    				<? echo mrr_load_these_dispatchs($mrr_load_id); ?>	
                    			</td>
                    		</tr>
                    		</table>	
				
					</div>	
						
				<? } else { ?>	
					
					<span class='alert'><b>No load selected to Duplicate.  Please select the Load to copy.</b></span>
				
				<? } ?>	
	</td>
	
</tr>
</table>

<script type='text/javascript'>
	
	var load_id = <?=$mrr_load_id ?>;
	
	$().ready(function() {
		
	});
	
	//$('.input_date').datepicker();
	$('.mrr_input_date').datepicker();
	
	//$('.tablesorter').tablesorter();
		
</script>
