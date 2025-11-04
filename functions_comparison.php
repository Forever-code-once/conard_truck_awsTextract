<?
//Additional functions for comparison report

	//budget items.............................
	function mrr_budget_item_array_max()
	{
		$max=12;
		$value[0]="0";		$display[0]="Fuel MPG";
		$value[1]="1";		$display[1]="Insurance";
		$value[2]="2";		$display[2]="Labor (Drivers)";
		$value[3]="3";		$display[3]="Truck Maint";
		$value[4]="5";		$display[4]="Tires";
		$value[5]="7";		$display[5]="Trailer Maint";
		$value[6]="8";		$display[6]="Truck Lease";
		$value[7]="9";		$display[7]="Mileage Expenses";
		$value[8]="10";	$display[8]="Admin Expenses";
		$value[9]="12";	$display[9]="Misc. Expenses";
		$value[10]="14";	$display[10]="Trailer Rental";
		$value[11]="15";	$display[11]="Accidents";	
		
		$res['max']=$max;
		$res['value']=$value;
		$res['display']=$display;
		return $res;
	}
	function mrr_select_box_for_budget_cats($field,$pre)
	{
		$box="";
		
		$res=mrr_budget_item_array_max();		$max=$res['max'];
		$value=$res['value'];				$display=$res['display'];
				
		$box="<select name='".$field."' id='".$field."'>";
		
		//$selected="";		if($pre==0)	$selected=" selected";	
		//$box.="<option value='0'".$selected."></option>";
			
		for($i=0;$i < $max;$i++)
		{
			$selected="";			if($pre==$value[ $i ])	$selected=" selected";
						
			$box.="<option value='".$value[ $i ]."'".$selected.">".$display[ $i ]."</option>";	
		}
		
		$box.="</select>";
		return $box;
	}
	function mrr_decode_budget_cat($id)
	{
		$result="";
		
		$res=mrr_budget_item_array_max();		$max=$res['max'];
		$value=$res['value'];				$display=$res['display'];
				
		for($i=0;$i < $max;$i++)
		{
			if($id==$value[ $i ])	$result=$display[ $i ];
		}		
		
		return $result;
	}
	function mrr_load_main_budget()
	{
		$display="";
		$sql="
			select budget.*
			from budget
			where budget.deleted=0
			order by budget.linedate_start desc,
				budget.linedate_ended desc,
				budget.linedate_added desc
               ";	
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
          {
          	$id=$row['id']; 
          	$added=date("m/d/Y", strtotime($row['linedate_added'])); // H:i:s
          	$start=date("m/d/Y", strtotime($row['linedate_start'])); 
          	$ended=date("m/d/Y", strtotime($row['linedate_ended'])); 
          	$name=$row['budget_name']; 
          	$act=$row['active']; 
          	
          	//$del=$row['deleted']; 
          	$trash="<div onClick='confirm_budget_delete(".$id.");'><img src='images/delete_sm.gif' border='0'></div>";
          	
          	$actor="Active";
          	$tag1="";
          	$tag2="";
          	$linker="<div class='mrr_link_like_on' onClick='mrr_get_this_budget(".$id.")'>".$name."</div>";
          	if($act==0)
          	{
          		$linker="<div class='mrr_link_like_off' onClick='mrr_get_this_budget(".$id.")'>".$name."</div>";
          		$actor="Inactive";
          		$tag1="<span class='mrr_link_like_off'>";
          		$tag2="</span>";	
          	}
          	
          	$display.="<tr>
          				<td valign='top'>".$linker."</td>
          				<td valign='top'>".$tag1."".$added."".$tag2."</td>
          				<td valign='top'>".$tag1."".$start."".$tag2."</td>
          				<td valign='top'>".$tag1."".$ended."".$tag2."</td>
          				<td valign='top'>".$tag1."".$actor."".$tag2."</td>
          				<td valign='top'>".$trash."</td>
          			</tr>";    	
          }
          return $display;
	}
	
	function mrr_load_main_budget_items($budget_id)
	{
		$display="";
		$cntr=0;
		if($budget_id>0)
		{
     		$sql="
     			select budget_items.*
     			from budget_items
     			where budget_items.budget_id='".sql_friendly( $budget_id )."'
     			order by budget_items.budget_cat asc,budget_items.id asc
                    ";	
               $data=simple_query($sql);
               
               //test for budget items for this budget.
               $mn=mysqli_num_rows($data);
               if($mn==0)
               {
               	mrr_create_budget_items_now($budget_id);		//create the budget items...then query for the list again.
               	$sql="
     				select budget_items.*
     				from budget_items
     				where budget_items.budget_id='".sql_friendly( $budget_id )."'
     				order by budget_items.budget_cat asc,budget_items.id asc
                    ";	
              		 $data=simple_query($sql);
               }          
               
               while($row=mysqli_fetch_array($data))
               {
               	$id=$row['id'];           	
               	$cat=mrr_decode_budget_cat($row['budget_cat']);
               	$mile=$row['per_mile'];
               	$truck=$row['per_truck'];
               	$trailer=$row['per_trailer'];
               	$driver=$row['per_driver'];
               	$dispatch=$row['per_dispatch'];
               	$load=$row['per_load'];
               	$flat=$row['flat_amount'];
               	$amnt=$row['budget_amount'];
               	
               	$display.="<tr class='row_".$cntr."'>
               				<td valign='top'>".($cntr+1)."<input type='hidden' name='id_".$cntr."' id='id_".$cntr."' value='".$id."'></td>
               				<td valign='top'>".$cat."</td>
               				<td valign='top'>$ <input type='text' name='mile_".$cntr."' id='mile_".$cntr."' class='input_medium' value='".$mile."' style='text-align:right;' onBlur='mrr_update_budget_item_row(".$cntr.")'></td>
               				<td valign='top'>$ <input type='text' name='truck_".$cntr."' id='truck_".$cntr."' class='input_medium' value='".$truck."' style='text-align:right;' onBlur='mrr_update_budget_item_row(".$cntr.")'></td>
               				<td valign='top'>$ <input type='text' name='trailer_".$cntr."' id='trailer_".$cntr."' class='input_medium' value='".$trailer."' style='text-align:right;' onBlur='mrr_update_budget_item_row(".$cntr.")'></td>
               				<td valign='top'>$ <input type='text' name='driver_".$cntr."' id='driver_".$cntr."' class='input_medium' value='".$driver."' style='text-align:right;' onBlur='mrr_update_budget_item_row(".$cntr.")'></td>
               				<td valign='top'>$ <input type='text' name='dispatch_".$cntr."' id='dispatch_".$cntr."' class='input_medium' value='".$dispatch."' style='text-align:right;' onBlur='mrr_update_budget_item_row(".$cntr.")'></td>
               				<td valign='top'>$ <input type='text' name='load_".$cntr."' id='load_".$cntr."' class='input_medium' value='".$load."' style='text-align:right;' onBlur='mrr_update_budget_item_row(".$cntr.")'></td>
               				<td valign='top'>$ <input type='text' name='flatt_".$cntr."' id='flat_".$cntr."' class='input_medium' value='".$flat."' style='text-align:right;' onBlur='mrr_update_budget_item_row(".$cntr.")'></td>
               				<td valign='top'>$ <input type='text' name='amnt_".$cntr."' id='amnt_".$cntr."' class='input_medium' value='".$amnt."' style='text-align:right;' onBlur='mrr_update_budget_item_row(".$cntr.")'></td>
               			</tr>";  
               	$cntr++;  	
               }
     	}
          return $display;
	}
	
	function mrr_create_budget_items_now($budget_id)
	{
		$old_id=0;
		$sql="
			select budget.id
			from budget
			where budget.deleted=0
				and budget.id!='".sql_friendly( $budget_id )."'
			order by budget.linedate_start desc,
				budget.linedate_ended desc,
				budget.linedate_added desc
			limit 1
               ";	
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
          {
          	$old_id=$row['id'];          	 
          }
				
		$res=mrr_budget_item_array_max();		$max=$res['max'];
		$value=$res['value'];				$display=$res['display'];
		
		for($i=0;$i < $max;$i++)
		{
			$mile=0;
               $truck=0;
               $trailer=0;
               $driver=0;
               $dispatch=0;
               $load=0;
               $flat=0;
               $amnt=0; 
			
			if($old_id>0)
			{
				$sql="
     				select budget_items.*
     				from budget_items
     				where budget_items.budget_id='".sql_friendly( $old_id )."'
     					and budget_items.budget_cat='".sql_friendly( $value[ $i ] )."'
     				order by budget_items.budget_cat asc,budget_items.id asc 
     				limit 1
                    ";		
                    $data=simple_query($sql);
                    while($row=mysqli_fetch_array($data))
                    {
                    	$mile=$row['per_mile'];
                    	$truck=$row['per_truck'];
                    	$trailer=$row['per_trailer'];
                    	$driver=$row['per_driver'];
                    	$dispatch=$row['per_dispatch'];
                    	$load=$row['per_load'];
                    	$flat=$row['flat_amount'];
                    	$amnt=$row['budget_amount'];  	 
                    }	
									
				$sql="
     			insert into budget_items
     				(id,
					budget_id,
					budget_cat,
					per_mile,
					per_truck,
					per_trailer,
					per_driver,
					per_dispatch,
					per_load,
					flat_amount,
					budget_amount)
				values 
					(NULL,
					'".sql_friendly( $budget_id )."',
					'".sql_friendly( $value[ $i ] )."',
					'".sql_friendly( $mile )."',
					'".sql_friendly( $truck )."',
					'".sql_friendly( $trailer )."',
					'".sql_friendly( $driver )."',
					'".sql_friendly( $dispatch )."',
					'".sql_friendly( $load )."',
					'".sql_friendly( $flat )."',
					'".sql_friendly( $amnt )."')
                    ";	
               	simple_query($sql);
          	}
		}		
	}
	
	function get_cur_counts_for_period($from,$to)
	{
		$miles=100000;
		$trucks=20;
		$trailers=30;
		$drivers=40;
		$dispatches=2400;
		$loads=6000;
		
		$res['mile']=$miles;
		$res['truck']=$trucks;
		$res['trailer']=$trailers;
		$res['driver']=$drivers;
		$res['dispatch']=$dispatches;
		$res['load']=$loads;
		return $res;
	}
	//.....................................
	
	function mrr_get_default_variable_setting($xname,$section="")
	{
		if(trim($section)=="")		$section="Financial";
		$xvalstr=0;	
		
		//get financial section...	
     	$sql="
     		select xvalue_string 
     		from defaults 
     		where section='".sql_friendly($section)."'
     			and xname='".sql_friendly($xname)."'
     			 
     		order by display_name asc
     	";
     	$data = simple_query($sql);
     	while($row = mysqli_fetch_array($data)) {
     		$xvalstr=$row['xvalue_string'];
     	}
     	return $xvalstr;	
	}
	function mrr_get_option_variable_settings($fname)
	{
		$section="Fixed Expenses";
		$fvalstr=0;
		
		//now get fixed settings in option list
     	$sql="
     		select option_values.* 
     		from option_values,option_cat 
     		where cat_desc='".$section."'
     			and option_values.cat_id=option_cat.id
     			and option_values.deleted=0
     			and fname='".sql_friendly($fname)."'
     		order by fname asc
     	";
     	$data = simple_query($sql);
     	while($row = mysqli_fetch_array($data)) {
     		$fvalstr=$row['fvalue'];	
     	}	
		return $fvalstr;		
	}

	//comparison
	function mrr_get_daily_cost($row) {
		// if truck_id is specified, use the cost for that specific truck instead of the average
		// if trailer_id is specified, use the cost for that specific trailer instead of the average
		//this function uses only the current values for this load for comparison report
		/*
		$row['budget_average_mpg']
		$row['budget_days_in_month']
		$row['budget_labor_per_hour']
		$row['budget_labor_per_mile']
		$row['budget_labor_per_mile_team']
		$row['budget_driver_week_hours']
		$row['budget_tractor_maint_per_mile']
		$row['budget_trailer_maint_per_mile']
		$row['budget_truck_accidents_per_mile']
		$row['budget_tires_per_mile']
		$row['budget_mileage_exp_per_mile']
		$row['budget_misc_exp_per_mile']
		$row['budget_cargo_insurance']
		$row['budget_general_liability']
		$row['budget_liability_damage']
		$row['budget_payroll_admin']
		$row['budget_rent']
		$row['budget_tractor_lease']
		$row['budget_trailer_exp']
		$row['budget_trailer_lease']
		$row['budget_misc_exp']
		$row['budget_active_trucks']
		$row['budget_active_trailers']
		$row['budget_day_variance']
		
		$mrr_miles=$row['miles'] + $row['miles_deadhead'];
		if($mrr_miles==0)	$mrr_miles=$row['loaded_miles_hourly'];
		
		$mrr_tot_cost_sales=$row['actual_total_cost'];
		$mrr_billed_sales=$row['actual_bill_customer'];
		$mrr_actual_bill=$row['actual_bill_customer']-$row['actual_total_cost'];
		*/
		
		$daily_expense = 0;
		
		$daily_expense += $row['budget_payroll_admin'] / $row['budget_days_in_month'] / $row['budget_active_trucks'];
		$daily_expense += $row['budget_rent'] / $row['budget_days_in_month'] / $row['budget_active_trucks'];
		
		$daily_expense += $row['budget_cargo_insurance'] / $row['budget_days_in_month'];
		$daily_expense += $row['budget_general_liability'] / $row['budget_days_in_month'];
		$daily_expense += $row['budget_liability_damage'] / $row['budget_days_in_month'];
		$daily_expense += $row['budget_tractor_lease'] / $row['budget_days_in_month'];
		$daily_expense += $row['budget_trailer_exp'] / $row['budget_days_in_month'];
		$daily_expense += $row['budget_trailer_lease'] / $row['budget_days_in_month'];
		$daily_expense +=  $row['budget_misc_exp']/ $row['budget_days_in_month'];
		
		return $daily_expense;
	}
	
	function mrr_quick_and_easy_budget_maker($rowx,$mrr_from_date='',$mrr_to_date='',$dispatch_id=0)
	{	//take LOAD_ID and Data From Load Dispatches, and get the separate budget cost items
		$res['fuel']=0;
		$res['insur']=0;
		$res['labor']=0;
		
		$res['labor_miles']=0;
		$res['labor_hours']=0;		
		
		$res['truck_maint']=0;
		$res['tires']=0;
		$res['trailer_maint']=0;
		$res['truck_rental']=0;
		$res['truck_lease']=0;
		$res['mileage_exp']=0;
		$res['admin_exp']=0;
		$res['misc_exp']=0;
		$res['trailer_rental']=0;
		$res['accidents']=0;
		$res['trailer_mileage_exp']=0;
		
		$res['daily_cost']=0;
		$res['expenses']=0;
		$res['miles']=0;
		$res['dispatch_count']=0;
		$res['dispatch_list']="";
		$res['hours']=0;
		
		$res['fun_disp_cost']=0;
		$res['days_run']=0;
		$res['pay_rate']=0;
		$res['pay_rate2']=0;
		
		//$fuel_rate=$rowx['actual_fuel_charge_per_mile'];
		
		//get dispatches
		$mrr_adder="";
		if($mrr_from_date!='' && $mrr_to_date!='')
		{
			//$mrr_adder=" and linedate_pickup_eta >= '".$mrr_from_date."' and linedate_pickup_eta < '".$mrr_to_date."'";	
		}
		if($dispatch_id > 0)	$mrr_adder.=" and trucks_log.id='".sql_friendly($dispatch_id)."'";	
		
		$sql = "
			select trucks_log.id		
			from trucks_log
			where trucks_log.deleted = 0
				and load_handler_id='".sql_friendly($rowx['id'])."'
				".$mrr_adder."	
				and dispatch_completed = 1				
			order by id asc
		";
		$data = simple_query($sql);
		$res['sql']=$sql;	
		
		$disp_exp=0;
		$labor=0;
		$daily_cost=0;
		$truck_maint = 0;
		$trailer_maint = 0;
		$tires = 0;
		$accidents = 0;
		$mileage = 0;
		$misc = 0;
		$fuel=0;
		
		$rate=0;
		$hours_worked=0;
		$days_run_otr=0;
		while($row = mysqli_fetch_array($data))
		{
			$disp_id=$row['id'];
			$res2=mrr_quick_and_easy_budget_maker_disp($rowx,$disp_id);		//NEW FUNCTION TO GET EACH DISPATCH SEPARATELY SO TOTALS ARE NOT RESET OR ADDED TOO MANY OR FEW TIMES...
						
			//add everything to main variable totals
			$res['fuel']+=$res2['fuel'];
			$res['labor']+=$res2['labor'];
			
			$res['labor_miles']+=$res2['labor_miles'];
			$res['labor_hours']+=$res2['labor_hours'];	
			
			$res['truck_maint']+=$res2['truck_maint'];
			$res['tires']+=$res2['tires'];
			$res['trailer_maint']+=$res2['trailer_maint'];
			$res['mileage_exp']+=$res2['mileage_exp'];
			$res['misc_exp']+=$res2['misc_exp'];
			$res['accidents']+=$res2['accidents'];
			
			$res['trailer_mileage_exp']+=$res2['trailer_mileage_exp'];
		
			$res['expenses']+=$res2['expenses'];		//general...may go to misc
			$res['daily_cost']+=$res2['daily_cost'];
			$res['days_run']+=$res2['days_run'];
		
			$res['pay_rate']+=$res2['pay_rate'];
			$res['pay_rate2']+=$res2['pay_rate2'];
			$res['hours']+=$res2['hours'];
			
			$res['insur']+=$res2['insur'];
			$res['truck_rental']+=$res2['truck_rental'];	
			$res['truck_lease']+=$res2['truck_lease'];	
			
			$res['admin_exp']+=$res2['admin_exp'];
			$res['trailer_rental']+=$res2['trailer_rental'];
		
			$res['miles']+=$res2['miles'];
			$res['dispatch_count']+=$res2['dispatch_count'];
			$res['dispatch_list'].=",".$res2['dispatch_list'];			
			$res['fun_disp_cost']+=$res2['fun_disp_cost'];	
			
			$res['sql'].="<br><br>".$res2['sql'];		
		}		
		
		$var_exp=0;
		$sql = "
			select expense_amount
			from load_handler_actual_var_exp,option_values,option_cat
			where load_handler_id='".sql_friendly($rowx['id'])."'
				and load_handler_actual_var_exp.expense_type_id=option_values.id
				and option_values.cat_id=option_cat.id
				and option_cat.cat_name='expense_type'
				and option_values.fname='base_rate'
          	";	
          $data = simple_query($sql);
          while($row = mysqli_fetch_array($data))
          {
          	$var_exp+=$row['expense_amount'];	
          }
          $res['expenses']+=$var_exp;
		
		return $res;
	}
	
	function mrr_get_truck_rental_status($id)
	{
		$rental_flag=0;		//default is company owned
		
		$sql = "
			select rental,
				company_owned		
			from trucks
			where id='".sql_friendly($id)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		if($row['company_owned'] == 0)	$rental_flag=2;	//leased
		if($row['rental'] > 0)			$rental_flag=1;	//rental
		
		return $rental_flag;
	}
	function mrr_pull_default_truck_cost_if_none()
	{
		$lease_amnt=0;
		$sql = "
			select fvalue		
			from option_values
			where fname='Tractor Lease'
		";
		$data = simple_query($sql);	
		$row = mysqli_fetch_array($data);
		if($row['fvalue'] > 0)			$lease_amnt=$row['fvalue'];
		return $lease_amnt;
	}
	
	function mrr_quick_and_easy_budget_maker_disp($rowx,$dispatch_id=0)
	{	//take Dispatch_ID for the Load and get separate budget cost items...but only for this dispatch
		$res['fuel']=0;
		$res['insur']=0;
		$res['labor']=0;
		
		$res['labor_miles']=0;
		$res['labor_hours']=0;
		
		$res['truck_maint']=0;
		$res['tires']=0;
		$res['trailer_maint']=0;
		$res['truck_rental']=0;
		$res['truck_lease']=0;
		$res['mileage_exp']=0;
		$res['admin_exp']=0;
		$res['misc_exp']=0;
		$res['trailer_rental']=0;
		$res['accidents']=0;
		$res['trailer_mileage_exp']=0;
		
		$res['daily_cost']=0;
		$res['expenses']=0;
		$res['miles']=0;
		$res['dispatch_count']=0;
		$res['dispatch_list']="";
		$res['hours']=0;
		
		$res['days_run']=0;
		
		$res['pay_rate']=0;
		$res['pay_rate2']=0;
		
		$res['fun_disp_cost']=0;
		
		$def_lease_amnt=mrr_pull_default_truck_cost_if_none();
		
		global $defaultsarray;
		
		$fuel_rate=$rowx['actual_fuel_charge_per_mile'];
		//$use_admin_section=0;
		
		$sql = "
			select *		
			from trucks_log
			where trucks_log.deleted = 0
				and load_handler_id='".sql_friendly($rowx['id'])."'
				and trucks_log.id='".sql_friendly($dispatch_id)."'
				and dispatch_completed = 1				
			order by id asc
		";
		$data = simple_query($sql);
		$res['sql']=$sql;	
		
		$disp_exp=0;
		$labor=0;
		$daily_cost=0;
		$truck_maint = 0;
		$trailer_maint = 0;
		$tires = 0;
		$accidents = 0;
		$mileage = 0;
		$misc = 0;
		$fuel=0;
		$trailer_mileage=0;
		
		$rate=0;
		$rater=0;
		$hours_worked=0;
		$days_run_otr=0;
		if($row = mysqli_fetch_array($data))
		{
			// + (trucks_log.hours_worked / $defaultsarray[local_driver_workweek_hours])
			$min_val_otr = 0;
			$min_val_otr=$row['daily_run_hourly'];
			/*
			if($row['daily_run_hourly'] > 0) {
				$min_val_otr=$row['daily_run_hourly'];
			} elseif($row['hours_worked'] > 0) {
				//$min_val_otr=$row['hours_worked'] / $defaultsarray['local_driver_workweek_hours'];
			}
			*/
			
			//if($row['hours_worked'] > 0) $min_val_otr=$row['hours_worked'] / $defaultsarray['local_driver_workweek_hours'];
			
			$days_run_otr=$row['daily_run_otr'] + $min_val_otr;	//$row['daily_run_hourly']
								
			$disp_id=$row['id'];
			$driver=$row['driver_id'];
			$driver2=$row['driver2_id'];
			$hours_worked=$row['hours_worked'];
			$rater=$row['labor_per_mile'];
			$dcoster=$row['daily_cost'];
			$avg_mpg=$row['avg_mpg'];
			$rent_lease=$row['truck_rental'];
			
			$truck_cost=$row['truck_cost'];
			$trailer_cost=$row['trailer_cost'];
			
			//$valid_trip_pack=$row['valid_trip_pack'];
			//$conf_valid_trip_pack=$row['user_id_verified_trip_pack'];
			
			$res['dispatch_count']++;
			$res['dispatch_list'].="".$row['load_handler_id'].":".$disp_id." ";
			
			$mrr_miles=$row['miles'] + $row['miles_deadhead'];
			$mrr_miles_otr=$mrr_miles;
			$mrr_miles_hourly=$row['loaded_miles_hourly'] + $row['miles_deadhead_hourly'];
			
			//dispatch expenses		
			$exp_cat['scales']=0;			//id=2
			$exp_cat['stopoff']=0;			//id=19
			$exp_cat['truck_not_used']=0;		//id=56
			$exp_cat['comcheck']=0;			//id=82
			$exp_cat['misc']=0;				//id=3
							
			$sql2 = "
     			select dispatch_expenses.*,
     				option_values.fname		
     			from dispatch_expenses,option_values,option_cat
     			where dispatch_expenses.deleted = 0
     				and option_values.deleted = 0
     				and option_cat.deleted = 0
     				and dispatch_expenses.expense_type_id=option_values.id
     				and option_values.cat_id=option_cat.id
     				and dispatch_expenses.dispatch_id='".sql_friendly($disp_id)."'		
     			order by dispatch_expenses.id asc
     		";
     		$data2 = simple_query($sql2);
     		while($row2 = mysqli_fetch_array($data2))
     		{     			
     			$exp_type=$row2['expense_type_id'];
     			$exp_desc=strtolower($row2['fname']);
     			$disp_exp+=$row2['expense_amount'];   
     			  			
     			if(substr_count($exp_desc,"scale") > 0)			$exp_cat['scales']+=$row2['expense_amount'];	
     			elseif(substr_count($exp_desc,"stop") > 0)		$exp_cat['stopoff']+=$row2['expense_amount'];	
     			elseif(substr_count($exp_desc,"truck") > 0)		$exp_cat['truck_not_used']+=$row2['expense_amount'];	
     			elseif(substr_count($exp_desc,"comcheck") > 0)	$exp_cat['comcheck']+=$row2['expense_amount'];	
     			else										$exp_cat['misc']+=$row2['expense_amount'];	//if(substr_count($exp_desc,"misc") > 0)
     		}
     		
     		//get labor and total miles
     		$laborx=0;   
			$rate=$row['labor_per_mile'];
			$laborx+=($mrr_miles_otr * $rate);
			
			$res['labor_miles']+=($mrr_miles_otr * $rate);
						
			$rater=$row['labor_per_hour'];
			$mrr_miles+=$mrr_miles_hourly;
			
			if($hours_worked > 0) {
				$laborx+=($hours_worked * $rater);
				$res['labor_hours']+=($hours_worked * $rater);
			} else {
				//$laborx += ($mrr_miles_hourly * $rate);
			}
			

     		$labor+=($laborx + $exp_cat['stopoff']);
     		$res['miles']+=$mrr_miles;
     		
     		$misc += ($exp_cat['scales'] + $exp_cat['truck_not_used'] + $exp_cat['comcheck'] + $exp_cat['misc']);
     		
     		//use date to determine if other settings are used and active
     		$compute_dater=trim(substr($row['linedate_added'],0,10));			$compute_timer=trim(substr($row['linedate_added'],10));
     		$compute_dater=str_replace("-","",$compute_dater);				$compute_timer=str_replace("-","",$compute_timer);
     		$compute_dater=str_replace(":","",$compute_dater);				$compute_timer=str_replace(":","",$compute_timer);
     		$compute_dater=str_replace(" ","",$compute_dater);				$compute_timer=str_replace(" ","",$compute_timer);
     				
     		if($compute_dater < 20120210 || ($compute_dater == 20120210 && $compute_timer <= 124620))
     		{     			
     			$tires+=0;
     			$accidents+=0;
     			$mileage+=0;
     			$misc+=0;
     			
     			$trailer_mileage+=0;
     		}     		
     		else
     		{
     			$tires+=($mrr_miles * $row['tires_per_mile']);
     			$accidents+=($mrr_miles * $row['accidents_per_mile']);
     			$mileage+=($mrr_miles * $row['mile_exp_per_mile']);
     			$misc+=($mrr_miles * $row['misc_per_mile']);	
     			
     			$trailer_mileage+=($mrr_miles * $row['trailer_exp_per_mile']);
     		}
     		/*
     		elseif($days_run_otr==0)
     		{
     			$tires+=($mrr_miles_off * $row['tires_per_mile']);
     			$accidents+=($mrr_miles_off * $row['accidents_per_mile']);
     			$mileage+=($mrr_miles_off * $row['mile_exp_per_mile']);
     			$misc+=($mrr_miles_off * $row['misc_per_mile']);	
     			
     			$trailer_mileage+=($mrr_miles_off * $row['trailer_exp_per_mile']);
     		}
     		*/
     		$fuel+=($mrr_miles * $fuel_rate);	
     		$truck_maint+=($mrr_miles * $row['tractor_maint_per_mile']);
 			$trailer_maint+=($mrr_miles * $row['trailer_maint_per_mile']);   
     		if($days_run_otr>0)
          	{          		
          		$daily_cost+=($days_run_otr * $dcoster);
          	}
			if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' && isset($_GET['debug'])) {
          			echo "Fuel Value: ".$fuel."<br><br>";          		
     		}	
     		
     		if($rent_lease==0)
     		{
     			$rent_lease=mrr_get_truck_rental_status($row['truck_id']);
     			
     			$sql_xx="update trucks_log set truck_rental='".sql_friendly($rent_lease)."' where id='".sql_friendly($row['id'])."'";
     			simple_query($sql_xx);
     		}
     				
						
			if($daily_cost > 0)	
			{
				$days_in_month=$rowx['budget_days_in_month'];
     			$active_trucks=$rowx['budget_active_trucks'];
     			
     			//$trailer_cost=0;		//force default trailer cost...
     			if($truck_cost==0)		$truck_cost=$rowx['budget_tractor_lease'];
     			if($trailer_cost==0)	$trailer_cost=$rowx['budget_trailer_exp'];
     			
     			// + $rowx['budget_misc_exp']
     			     			
     			$insur=(($rowx['budget_cargo_insurance']+ $rowx['budget_general_liability'] + $rowx['budget_liability_damage']) / $days_in_month)*$days_run_otr;	// / $active_trucks
     			$admin=(($rowx['budget_payroll_admin'] + $rowx['budget_rent']) / $days_in_month) / $active_trucks * $days_run_otr;
     			
     			//added Jan 2013
     			//$truck_lease=$truck_cost / $days_in_month * $days_run_otr;
     			//$truck_rental=0;	
     			
     			
     			if($rent_lease > 0 && $truck_cost==0)
     			{
     				$truck_cost=$def_lease_amnt;
     				
     				$sql_xx="update trucks_log set truck_cost='".sql_friendly($truck_cost)."' where id='".sql_friendly($row['id'])."'";
     				simple_query($sql_xx);
     			}  			
     			
     			
     			//added Mar 2013 to separate truck lease and truck rental...
     			if($rent_lease==2)
     			{	//leased
     				$truck_lease=$truck_cost / $days_in_month * $days_run_otr;
     				$truck_rental=0;
     			}
     			elseif($rent_lease==1)
     			{	//rented
     				$truck_lease=0;
     				$truck_rental=$truck_cost / $days_in_month * $days_run_otr;
     			}
     			else
     			{	//company owned or "other" which is not yet defined.
     				$truck_lease=0;
     				$truck_rental=0;
     			}
     			
     			
     			$trailer_rent=$trailer_cost / $days_in_month * $days_run_otr;	
     			
     			//$truck_lease=$daily_cost - ($insur + $admin + $trailer_rent);
     			$color="blue; font-weight:bold";
     			if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' && isset($_GET['debug'])) {
          			echo "
          			<br>---------------------------------------------------------<br>
          			Fuel Value: ".$fuel."<br><br>
          			Day in Month: ".$days_in_month." Active Trucks: ".$active_trucks." Days OTR: ".$days_run_otr."<br>
          			<br>     			
          			Cargo: ".$rowx['budget_cargo_insurance']." Gen Liability: ".$rowx['budget_general_liability']." Damage Liability: ".$rowx['budget_liability_damage']."<br>
          			<br>     			
          			Truck Lease: ".$truck_cost." Trailer Exp: ".$trailer_cost."<br>
          			<br>     			
          			Rent : ".$rowx['budget_rent']." Payroll: ".$rowx['budget_payroll_admin']."<br>
          			<br>   			
          			";
          			if($days_in_month>0 && $active_trucks>0)
          			{
               			echo "          			
               			INS Calc: ".((($rowx['budget_cargo_insurance']+ $rowx['budget_general_liability'] + $rowx['budget_liability_damage']) / $days_in_month)*$days_run_otr).
               					" = ((<span style='color:".$color.";'>".$rowx['budget_cargo_insurance']."</span>
               					 + <span style='color:".$color.";'>".$rowx['budget_general_liability']."</span>
               					  + <span style='color:".$color.";'>".$rowx['budget_liability_damage']."</span>)
               					   / <span style='color:".$color.";'>".$days_in_month."</span>) 
               					   * <span style='color:".$color.";'>".$days_run_otr."</span><br>
               			
               			ADM Calc: ".((($rowx['budget_payroll_admin'] + $rowx['budget_rent']) / $days_in_month) / $active_trucks * $days_run_otr).
               						" = (<span style='color:".$color.";'>".$rowx['budget_payroll_admin']  ."</span>
               						 + <span style='color:".$color.";'>".$rowx['budget_rent']."</span>)
               						  / <span style='color:".$color.";'>".$days_in_month."</span>
               						   / <span style='color:".$color.";'>".$active_trucks."</span>
               						    * <span style='color:".$color.";'>".$days_run_otr."</span><br>
               						
               			TRU Calc: ".($truck_cost / $days_in_month * $days_run_otr).
               						" = <span style='color:".$color.";'>".$truck_cost ."</span>
               						 / <span style='color:".$color.";'>".$days_in_month."</span>
               						  * <span style='color:".$color.";'>".$days_run_otr."</span><br>
               			
               			TRA Calc: ".($trailer_cost / $days_in_month * $days_run_otr).
               						" = <span style='color:".$color.";'>".$trailer_cost ."</span>
               						 / <span style='color:".$color.";'>".$days_in_month."</span>
               						  * <span style='color:".$color.";'>".$days_run_otr."</span><br>
               			<br>
               			";
          			}
          			else
          			{
          				echo "<br><b>Values are zeros...no daily cost.</b></br>";	
          			}
          			echo "
          			<b>TOTAL=".($insur + $admin + $truck_lease + $truck_rental + $trailer_rent) .".</b>
          			<br>---------------------------------------------------------<br>
          			";
          			
          			//section not used...but ready if Conard wants it to reset the daily cost...
          			if(number_format($dcoster,2) != number_format(($insur + $admin + $truck_lease + $truck_rental + $trailer_rent),2) )
          			{
          				$sql2="update trucks_log set daily_cost='".sql_friendly(($insur + $admin + $truck_lease + $truck_rental+ $trailer_rent))."' where id='".sql_friendly($disp_id)."'";
						//simple_query($sql2);	//255.03
          			}          			
     			}
     			
     			$res['insur']+=$insur;
     			$res['admin_exp']+=$admin;
     			$res['truck_rental']+=$truck_lease;		//switched on purpose...see line below...
     			$res['truck_lease']+=$truck_rental;		//switched on purpose...see line above...
     			$res['trailer_rental']+=$trailer_rent;		
			}			
			$res['fun_disp_cost'] += get_dispatch_cost($disp_id);				
		}		
		$res['fuel']=$fuel;
		$res['labor']=$labor;
		$res['truck_maint']=$truck_maint;
		$res['tires']=$tires;
		$res['trailer_maint']=$trailer_maint;
		$res['mileage_exp']=$mileage;		
		$res['misc_exp']=$misc;
		$res['accidents']=$accidents;
		$res['trailer_mileage_exp']=$trailer_mileage;
		
		$res['expenses']=$disp_exp;		//general...may go to misc
		$res['daily_cost']=$daily_cost;
		$res['days_run']=$days_run_otr;
		
		$res['pay_rate']=$rate;
		$res['pay_rate2']=$rater;
		$res['hours']=$hours_worked;		
		return $res;	
	}	
		
	function mrr_quick_and_easy_daily_cost($dispatch_id=0,$cd=0)
	{	//take Dispatch_ID and get daily cost per day...not the total of days.   CD is whether or not to send it in parts as well as 
		$mrr_daily_cost=0;
		global $defaultsarray;
		
		$res['insur']=0;
		$res['truck_rental']=0;
		$res['truck_lease']=0;
		$res['admin_exp']=0;
		$res['trailer_rental']=0;
		
		$res['truck_cost']=0;
		$res['trailer_cost']=0;
		
		$res['daily_cost']=0;		
		$res['days_run']=0;		
		$res['daily_cost_tot']=0;		
		$res['daily_cost_stored']=0;
		
		$sql = "
			select *		
			from trucks_log
			where trucks_log.id='".sql_friendly($dispatch_id)."'
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$use_load_id=$row['load_handler_id'];	
			
			$sqlx = "
               	select load_handler.*			
               	from load_handler
               	where load_handler.id = '".sql_friendly($use_load_id)."'
               ";
               $datax = simple_query($sqlx);
               $rowx = mysqli_fetch_array($datax);			
               //has load level settings for defaults at the time the load was made.
               
			//$days_run_otr=$row['daily_run_otr'] + $row['daily_run_hourly'];
			$days_run_otr=0;
			if($defaultsarray['local_driver_workweek_hours'] > 0)
			{
				$days_run_otr=$row['daily_run_otr'] + ($row['hours_worked'] / $defaultsarray['local_driver_workweek_hours']);
			}
			$dcoster=$row['daily_cost'];			//stored daily cost
			
			$truck_cost=$row['truck_cost'];
			$trailer_cost=$row['trailer_cost'];
						
			if($truck_cost==0)		$truck_cost=$rowx['budget_tractor_lease'];
			
    			$trailer_cost=0;
    			if($trailer_cost==0)	$trailer_cost=$rowx['budget_trailer_exp'];
    						
			$days_in_month=$rowx['budget_days_in_month'];
     		$active_trucks=$rowx['budget_active_trucks'];
			
			$insur=(($rowx['budget_cargo_insurance']+ $rowx['budget_general_liability'] + $rowx['budget_liability_damage'] + $rowx['budget_misc_exp']) / $days_in_month);	// / $active_trucks
     		$admin=($rowx['budget_payroll_admin'] / $days_in_month) / $active_trucks;
     		$admin+=($rowx['budget_rent'] / $days_in_month) / $active_trucks;
     		
     		$truck_rental=0;		//added Jan 2013
     		$truck_lease=$truck_cost / $days_in_month;
     		$trailer_rent=$trailer_cost / $days_in_month;	
     		
			$mrr_daily_cost+=($insur + $admin + $truck_lease + $truck_rental + $trailer_rent);		//single day daily cost....for this dispatch/load		
			
			$res['insur']=$insur;
     		$res['admin_exp']=$admin;
     		$res['truck_rental']=$truck_rental;
     		$res['truck_lease']=$truck_lease;
     		$res['trailer_rental']=$trailer_rent;	
     		
     		$res['truck_cost']=$truck_cost;
			$res['trailer_cost']=$trailer_cost;
     			
     		$res['daily_cost']=$mrr_daily_cost;		
			$res['days_run']=$days_run_otr;		
			$res['daily_cost_tot']=$mrr_daily_cost * $days_run_otr;		
			$res['daily_cost_stored']=$dcoster;				
		}		
		if($cd==1)	return $res;
		else			return $mrr_daily_cost;		
	}
	
	
	function mrr_find_expense_costs()
	{
		global $defaultsarray;
		// calculate our fixed expenses
		$sql = "
			select option_values.fvalue,
				option_values.fname,
				option_values.dummy_val
			
			from option_values, option_cat
				where option_cat.id = option_values.cat_id
				and option_cat.cat_name = 'fixed_expenses'
			order by option_values.fname
		";
		$data_expenses = simple_query($sql);	
		
		$admin_expense = 0;
		$insur_expense = 0;
		$truck_rental_expense = 0;		//not used yet....
		$truck_lease_expense = 0;
		$truck_expense = 0;
		$trailer_expense = 0;
		$truck_cost = mrr_get_truck_cost(0);
		$trailer_cost = mrr_get_trailer_cost(0);
		
		$mrr_my_holder=0;
		
		while($row_expenses = mysqli_fetch_array($data_expenses)) 
		{
			if($row_expenses['fvalue'] != 0) 
			{
				$skip_expense = false;
				
				if(strtolower($row_expenses['fname']) == 'trailer lease' || strtolower($row_expenses['fname']) =='trailer expense') 
				{
					if($trailer_cost > 0) 
					{
						$trailer_expense += $trailer_cost / $defaultsarray['billable_days_in_month'];
					} 
					else 
					{
						$trailer_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
					}
					$skip_expense = true;
				}
				
				if(strtolower($row_expenses['fname']) == 'tractor lease') 
				{
					/*
					if($truck_cost > 0) {
						$truck_lease_expense += $truck_cost / $defaultsarray['billable_days_in_month'];
						$truck_expense += $truck_cost / $defaultsarray['billable_days_in_month'];
					} else {
						$truck_lease_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
						$truck_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
					}
					*/
					$mrr_my_holder+=$row_expenses['fvalue'];
					$truck_lease_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
					
					$skip_expense = true;
				}
				if(strtolower($row_expenses['fname']) == 'truck rental') 
				{
					/*
					if($truck_cost > 0) {
						$truck_rental_expense += $truck_cost / $defaultsarray['billable_days_in_month'];
						$truck_expense += $truck_cost / $defaultsarray['billable_days_in_month'];
					} else {
						$truck_rental_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
						$truck_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
					}
					*/
					
					$mrr_my_holder+=$row_expenses['fvalue'];
					$truck_rental_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
					
					$skip_expense = true;
				}
				
				if(!$skip_expense) 
				{
					// we already processed the expense ealier, skip it now so we don't double count it
					if($row_expenses['dummy_val'] == 1) 
					{
						$admin_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'] / get_active_truck_count();
					} 
					else 
					{
						$insur_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
					}
				}
			}
		}
		
		if($defaultsarray['billable_days_in_month'] > 0)
		{
			$truck_expense=$mrr_my_holder / $defaultsarray['billable_days_in_month'];
		}
				
		$res['admin']=$admin_expense;
		$res['insur']=$insur_expense;
		$res['truck_rental']=$truck_rental_expense;
		$res['truck_lease']=$truck_lease_expense;
		$res['truck']=$truck_expense;
		$res['trailer']=$trailer_expense;
		
		return $res;
	}		
	
	
	function mrr_get_month_parts($back_mons)
	{	//get all the month components for x months past current, including max number of days
		$cur_month=date("m");		$cur_year=date("Y");
		
		//Month no longer used		//Max days in month STILL used
		$months[0]="";				$maxdays[0]=0;
		$months[1]="January";		$maxdays[1]=31;
		$months[2]="February";		$maxdays[2]=28;
		$months[3]="March";			$maxdays[3]=31;
		$months[4]="April";			$maxdays[4]=30;
		$months[5]="May";			$maxdays[5]=31;
		$months[6]="June";			$maxdays[6]=30;
		$months[7]="July";			$maxdays[7]=31;
		$months[8]="August";		$maxdays[8]=31;
		$months[9]="September";		$maxdays[9]=30;
		$months[10]="October";		$maxdays[10]=31;
		$months[11]="November";		$maxdays[11]=30;
		$months[12]="December";		$maxdays[12]=31;
		
		$res="
			<table width='100%' cellpadding='0' cellspacing='0' border='0'>
			<tr>
					
		";				
		for($i=0; $i< $back_mons; $i++)
		{
			$datex=date("F",strtotime("-".$i." month",time()));
			$monx=date("m",strtotime("-".$i." month",time()));
			$yearx=date("Y",strtotime("-".$i." month",time()));			
			
			if((int) $monx==2 && (int) $yearx %4==0)	$maxdays[ $monx ]=29;			
			
			$res.="
				<td valign='top' width='16%'>
					<a href='report_comparison.php?date_from=".$monx."_01_".$yearx."&date_to=".$monx."_".$maxdays[ (int)$monx ]."_".$yearx."' target='_blank' title='View Comparison Report for ".$datex." ".$yearx."'>
						".$datex." ".$yearx."
					</a>
				</td>
			";	
		}			
		$res.="
			</tr>
			</table>
			<br>
		";
		return $res;
	}
	
	function mrr_pull_timesheet_labor_cost($date_from,$date_to,$customer_id=0)
	{
		$res['tot']=0;
		$res['sql']="";
		$res['rep']="";
		if($customer_id==0)		return $res;
				
		$tot=0;
		$report="";
		$cntr=0;
				
		$sql = "
			select trucks_log_shuttle_routes.* 
               from trucks_log_shuttle_routes 
                 	left join timesheets on timesheets.id=trucks_log_shuttle_routes.timesheet_id
               where timesheets.deleted=0
               	and timesheets.customer_id='".sql_friendly($customer_id)."'
               	and trucks_log_shuttle_routes.deleted=0
               	and trucks_log_shuttle_routes.conard_hours > 0
               	and trucks_log_shuttle_routes.linedate_from>='".date("Y-m-d",strtotime($date_from))." 00:00:00'
               	and trucks_log_shuttle_routes.linedate_from<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
               order by trucks_log_shuttle_routes.linedate_from asc
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$hrs=$row['conard_hours'];
			$rate=$row['pay_rate_hours'];
			$labor=$hrs * $rate;
			
			$cntr++;	
			$report.="<br>".$cntr.". ".$row['linedate_from']." | ".$row['driver_id'].": ".$row['conard_hours']." hrs * $".$row['pay_rate_hours']."/hr = ".$labor.".";
					
			$tot+=$labor;
		}	
		
		$res['tot']=$tot;
		$res['sql']=$sql;
		$res['rep']=$report;
		
		return $res;		
	}
	
	function mrr_pull_acct_sales_invoice_by_cust_timesheet($date_from,$date_to,$customer_id=0,$html_flag=0)
	{
		$tot=0;	
		$sicap_id=0;
		
		$tab="";
		
		//get accounting ID for customer
		$sql = "
			select sicap_id
               from customers
               where id='".sql_friendly($customer_id)."'
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{		
			$sicap_id=$row['sicap_id'];
		}		
		
		if($customer_id==0 || $sicap_id==0)		return $tot;	
		
		//now pull the customer invoice total for timesheets only.		
		$dbnamer=mrr_find_acct_database_name();
		$sql = "
			select id,total as my_tot
               from ".$dbnamer."invoice 
               where customer_id='".sql_friendly($sicap_id)."'
               	and deleted=0
               	and linedate_ship>='".date("Y-m-d",strtotime($date_from))." 00:00:00'
               	and linedate_ship<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
               	and customer_po='Timesheet Invoice'
               order by linedate_ship asc
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{		
			$oldtot=$tot;
			
			$tot+=$row['my_tot'];
			
			$tab.="<br>INV ".$row['id'].": ".$row['my_tot']." + ".$oldtot." = ".$tot.".";
		}			
		
		if($html_flag > 0)		return $tab;
		
		return $tot;
	}
	
	function mrr_pull_acct_sales_invoice_by_cust_timesheet_v2($date_from,$date_to,$customer_id=0,$html_flag=0)
	{
		$total_sales=0;
		
		$tot=0;	
		$sicap_id=0;
		
		$tab="";
		
		$invoiced2=0;
		$invoiced_amount2=0;
		$mrr_inv_amnt_tot=0;	
		
		//get accounting ID for customer
		$sql = "
			select sicap_id
               from customers
               where id='".sql_friendly($customer_id)."'
		";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{		
			$sicap_id=$row['sicap_id'];
		}		
		
		if($customer_id==0 || $sicap_id==0)		return $tot;	
		
		$include_shuttle_runs=1;
		
		//now pull the customer invoice total for timesheets only.		
		$sql = "
			select trucks_log_shuttle_routes.* ,
				timesheets.invoice_id,
				timesheets.linedate_invoiced,
				customers.name_company,
				(select trucks.name_truck from trucks where trucks.id=trucks_log_shuttle_routes.truck_id) as mytruck,
				(select trailers.trailer_name from trailers where trailers.id=trucks_log_shuttle_routes.trailer_id) as mytrailer,
				(select CONCAT(drivers.name_driver_first, ' ' ,drivers.name_driver_last) from drivers where drivers.id=trucks_log_shuttle_routes.driver_id) as mydriver,
				
				(select users.username from users where users.id=trucks_log_shuttle_routes.user_id) as myuser,
				(select option_values.fname from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myname,
				(select option_values.fvalue from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myval
			from trucks_log_shuttle_routes
				left join timesheets on timesheets.id=trucks_log_shuttle_routes.timesheet_id
				left join customers on customers.id=timesheets.customer_id
			where trucks_log_shuttle_routes.deleted=0 
				and timesheets.deleted=0
				and timesheets.customer_id = '".sql_friendly($customer_id)."'
				and timesheets.invoice_id > 0 
				and trucks_log_shuttle_routes.linedate_from >= '".date("Y-m-d",strtotime($date_from))." 00:00:00'
				and trucks_log_shuttle_routes.linedate_from <= '".date("Y-m-d",strtotime($date_to))." 23:59:59'					
				
			order by 				
					trucks_log_shuttle_routes.linedate_from,
					trucks_log_shuttle_routes.linedate_to,
					trucks_log_shuttle_routes.id
		";	
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{			
			$value=0;
			$cost=0;
			
			$use_namer1="";
			$use_namer2="";
			
			$shuttle_route=0;
			$use_pay_rate=option_value_text($row['option_id'],2);						//pay rate (for invoice amount) by shuttle run or by base settings (Carlex=145, Vietti=159).
			
			
			if(!isset($row['lunch-break']))	$row['lunch-break']=0;
			
			if($row['option_id']==159)	
			{
				$shuttle_route=0;		
				$value=$use_pay_rate * ($row['conard_hours'] - $row['lunch-break']);	//Vietti Foods...all of them use this...no shuttle runs.	
				$cost=$row['pay_rate_hours'] * ($row['conard_hours'] - $row['lunch-break']);
			}
			elseif($row['option_id'] > 0 && $row['option_id'] !=145)
			{
				$shuttle_route=1;												//this is a shuttle run location for Carlex (flat rate)...skip it?
				$value=$use_pay_rate;											// * $row2['conard_hours'];
				
				if(($row['conard_hours'] - $row['lunch-break']) > 0)
				{
					$labor=option_value_text(145,2);		//grab from the NONE - Switching ONLY rate.	
					
					$value+=($labor * ($row['conard_hours'] - $row['lunch-break']));	
				}
				
				$cost=$row['pay_rate_hours'] * ($row['hours'] - $row['lunch-break']);
				
				$use_namer1="Shuttle Run:";
				$use_namer2="".option_value_text($row['option_id'],1);
			}
			else
			{
				$value=$use_pay_rate * ($row['hours'] - $row['lunch-break']);
				$cost=$row['pay_rate_hours'] * ($row['conard_hours'] - $row['lunch-break']);
			}
			
			$cost+=($row['cost'] * $row['days_run']);
			
			if($shuttle_route==0 || $include_shuttle_runs > 0)
			{
				$inv_date="&nbsp;";
				$inv_num="&nbsp;";
				if($row['invoice_id'] > 0)
				{
					$inv_date="".date("m/d/Y H:i", strtotime($row['linedate_invoiced']))."";
					$inv_num="<a href='https://trucking.conardtransportation.com/accounting/invoice.php?invoice_id=".$row['invoice_id']."' target='_blank'>".$row['invoice_id']."</a>";	
					
					$invoiced2++;
					$invoiced_amount2+=$value;
					$mrr_inv_amnt_tot+=$value;		
				}
				else
				{
					$not_invoiced2++;
					$not_invoiced_amount2+=$value;
					$mrr_inv_diff_tot+=$value;
				}
				
				//$profit=$value - $cost;
				
				//$total_miles += $row['miles'];
				//$total_deadhead += $row['miles_deadhead'];		
				
				//$truck_miles_hr = 0;	//$row['loaded_miles_hourly'];
				//$truck_deadhead_hr = 0;	//$row['miles_deadhead_hourly'];	
				
				//$total_miles_hr += $truck_miles_hr;
				//$total_deadhead_hr += $truck_deadhead_hr;	
								
				if(trim($use_namer1)=="")
				{
					$use_namer1="Daily Cost $".number_format(($row['cost'] * $row['days_run']),2)."";	
					$use_namer2="$".number_format($use_pay_rate,2)." x ".number_format(($row['hours'] - $row['lunch-break']),2)."hrs";		
				}
				
				//$run_tot+=$value;
				/*
				echo "
					<tr class='odd'>
						<td>TimeSheet</td>
						<td nowrap>".$inv_num."</td>
						<td nowrap>".$use_namer1."</td>
						<td nowrap>".$use_namer2."</td>
						<td align='right'>".number_format($row['miles'])."</td>
						<td align='right'>".number_format($truck_miles_hr)."</td>
						<td align='right'>".number_format($row['miles_deadhead'])."</td>
						<td align='right'>".number_format($total_deadhead_hr )."</td>
						<td nowrap>".date("M j, Y", strtotime($row['linedate_from']))."</td>
						<td nowrap>".$row['name_company']."</td>
						<td align='right'>$".money_format('',$value)."</td>
						<td  nowrap align='right'>".$inv_date."</td>
						<td align='right'>$".money_format('',$value)."</td>
						<td align='right'>$".money_format('',$value)."</td>
						<td align='right'>$".money_format('',$cost)."</td>
						<td align='right'>".($profit <= 0 ? "<span style='color:red'>" : "<span>")."$".money_format('',$profit)."</span></td>												
					</tr>
				";			// <i><b>$".number_format($run_tot,2)."</b></i>
				*/
				
				//$total_profit += $profit;
				//$total_cost += $cost;
				
				$tab.="<br>INV ".$inv_num.": ".$value." + ".$total_sales." = ".($total_sales + $value).".";
				$total_sales += $value;
				//$cntr2++;
			}	
			
		}			
		$tot=$total_sales;
		
		if($html_flag > 0)		return $tab;
		
		return $tot;
	}
?>