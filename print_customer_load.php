<? include('application.php') ?>
<?	
	if(isset($_GET['load_id'])) $_POST['load_id'] = $_GET['load_id'];
	if(!isset($_POST['load_id'])) $_POST['load_id'] = 0;
		
	$mrr_load_id=$_GET['load_id'];
	$_POST['load_id']=$_GET['load_id'];	
	$mrr_my_wkday=date("w");	
	$send_pn_link=0;
	
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
		
		// Set detention amount
		$_POST['misc_detention'] = (!empty($row['misc_detention']) ? $row['misc_detention'] : 0);

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
	
	if (!isset($_POST['misc_detention'])) $_POST['misc_detention'] = 0;	

	$profit = $_POST['rate_base'] - $actual_total_cost;
	if($profit > 0 && $_POST['rate_base'] > 0) {
		$profit_percent = number_format(($profit / $_POST['rate_base']) * 100, 2);
	} else {
		$profit_percent = 0;
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
<? $no_header = 1 ?>
<? include('header.php') ?>
<?
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
?>
<table>
<tr>
	<td valign='top'>			
	<!--	
	<span class='section_heading'>Conard Load Printout</span>
	<input type='button' value='Print Load' name='print' onClick='mrr_print_report();'>
	-->	
	<form name='mainform' action='<?=$_SERVER['SCRIPT_NAME']?><?=($_POST['load_id'] > 0 ? "?load_id=".$_POST['load_id']."" : "") ?>' method='post' style='text-align:left'>
	
	<input type='hidden' name='load_id' id='load_id' value="<?=$_POST['load_id']?>">
		
	<div id='printable_area1'>
			
		<table class='section0_long' border='0'>
		<tr>
			<td width='100' valign='top'>Load ID</td>
			<td valign='top'><b><?=$_POST['load_id']?></b></td>
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
				<? echo mrr_load_these_stops($_POST['load_id'],0); ?>	
			</td>
		</tr>
		</table>
		
		<table class='section4_long'>
		<tr>
			<td colspan='10'>
				<? echo mrr_load_these_dispatchs($_POST['load_id']); ?>	
			</td>
		</tr>
		</table>		
		
		<table border='0' cellpadding='0' cellspacing='0'>
		<tr>
			<td valign='top'>
		     		
     		<table class='section3_long'>
     		<tr>
     			<td colspan='2'>
     				<b>Special Instructions</b><br>
     				<?=$_POST['special_instructions']?>
     				<hr>
     			</td>
     		</tr>
     		<tr>
     			<td colspan='2'>
     				<b>Billing Notes</b><br>
     				<?=$_POST['billing_notes']?>
     				<hr>
     			</td>
     		</tr>
     		<tr>
     			<td colspan='2'>
     				<b>Driver Notes</b><br>
     				<?=$_POST['driver_notes']?>
     				<hr>
     			</td>
     		</tr>
     		<tr>
     			<td valign='top'>
     				
     				<table style='border:1px black solid;'>
     				<tr>
     					<td colspan='3'><span class='section_heading'>Billing Section</span></td>
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
     					<td align='right' colspan='2'><?=$_POST['invoice_number']?></td>
     				</tr>
     				<tr>
     					<td width='100'>Date Invoiced</td>
     					<td align='right' colspan='2'><?=($_POST['linedate_invoiced'] > 0 ? date("m/d/Y", strtotime($_POST['linedate_invoiced'])) : "")?></td>
     				</tr>
     				<tr>
     					<td width='100'>Load #</td>
     					<td align='right' colspan='2'><?=$_POST['load_number']?></td>
     				</tr>
     				<tr>
     					<td width='100'>Pick Up #</td>
     					<td align='right' colspan='2'><?=$_POST['pickup_number']?></td>
     				</tr>
     				<tr>
     					<td width='100'>Delivery #</td>
     					<td align='right' colspan='2'><?=$_POST['delivery_number']?></td>
     				</tr>
     				<tr>
     					<td width='100'>Update Surcharge</td>
     					<td align='right' colspan='2'><?= $_POST['update_fuel_surcharge'] ?></td>
     				</tr>
     				<tr>
     					<td colspan='3'><hr></td>
     				</tr>
     				<tr>
     					<td width='100'>Loaded Miles</td>
     					<td align='right'></td>
     					<td align='right'><?=$_POST['actual_miles']?>
     					</td>
     				</tr>
     				<tr>
     					<td>Deadhead Miles</td>
     					<td align='right'></td>
     					<td align='right'><?=$_POST['actual_deadhead_miles']?></td>
     				</tr>
     				<tr>
     					<td>Fuel Avg $</td>
     					<td align='right'></td>
     					<td align='right'>$<?=$_POST['actual_rate_fuel_surcharge']?></td>
     				</tr>
     				<?
     					$f_p_m=$_POST['actual_fuel_charge_per_mile'] * ($_POST['actual_miles'] + $_POST['actual_deadhead_miles']);
     					//$f_s_p_m=$_POST['actual_fuel_surcharge_per_mile'] * ($_POST['actual_miles'] + $_POST['actual_deadhead_miles'] + $_POST['actual_loaded_miles_hourly']);
     					$f_s_p_m=$_POST['actual_fuel_surcharge_per_mile'] * ($_POST['actual_miles'] + $_POST['actual_loaded_miles_hourly']);
     					//$f_s_p_m = 0;
     				?>
     				<tr>
     					<td>Fuel per Mile </td>
     					<td align='right'><?=number_format($_POST['actual_fuel_charge_per_mile'],2)?></td>
     					<td align='right'>$<?=number_format($f_p_m,2) ?></td>
     				</tr>
     				<tr>
     					<td nowrap>Fuel Surcharge per Mile </td>
     					<td align='right'><?=number_format($_POST['actual_fuel_surcharge_per_mile'],2)?></td>
     					<td align='right'>$<?=number_format($f_s_p_m,2)?> </td>
     				</tr>
     				<tr>
     					<td colspan='3'>
     						<a href='javascript:void(0)' onclick="$('.actual_details').toggle()">toggle details</a>
     					</td>
     				</tr>
     				<tr class='actual_details'>
     					<td>Days Run (OTR) </td>
     					<td align='right'><?=$_POST['actual_days_run_otr']?></td>
     					<td align='right'>$<?=(isset($actual_days_run_otr_total) ? money_format('',$actual_days_run_otr_total) : "0.00")?></td>
     				</tr>
     				<tr class='actual_details'>
     					<td>Days Run (Hourly)</td>
     					<td align='right'><?=$_POST['actual_days_run_hourly']?></td>
     					<td align='right'>$<?=(isset($actual_days_run_hourly_total) ? money_format('',$actual_days_run_hourly_total) : "0.00")?></td>
     				</tr>
     				<tr class='actual_details'>
     					<td nowrap>Loaded Miles for Hourly<br>(local work)</td>
     					<td align='right'><?=$_POST['actual_loaded_miles_hourly']?></td>
     					<td align='right'>$<?=(isset($actual_loaded_miles_hourly_total) ? money_format('',$actual_loaded_miles_hourly_total) : "0.00")?></td>
     				</tr>
     				<tr class='actual_details'>
     					<td>Hours Worked </td>
     					<td align='right'><?=$_POST['actual_hours_worked']?></td>
     					<td align='right'>$<?=(isset($actual_hours_worked_total) ? money_format('',$actual_hours_worked_total) : "0.00")?></td>
     				</tr>
     				<tr>
     					<td colspan='3'><hr></td>
     				</tr>
     				<?
     				$exp_cntr=0;
     				mysqli_data_seek($data_expenses_variable,0);
     				while($row_expenses_variable = mysqli_fetch_array($data_expenses_variable)) 
     				{     					
     					$use_expense = 0;
     					if($_POST['load_id'] > 0) 
     					{
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
     					}
     					if($use_expense > 0)
     					{
     						echo "
     							<tr>
     								<td>$row_expenses_variable[fvalue]</td>
     								<td align='right' colspan='2'>$".money_format('',$use_expense)."'</td>
     							</tr>
     						";
     						$exp_cntr++;
     					}
     				}
     				if($exp_cntr > 0)		echo "<tr><td colspan='10'><hr></td></tr>";
     				
     				mysqli_data_seek($data_expenses_variable_lh,0);
     				while($row_expenses_variable = mysqli_fetch_array($data_expenses_variable_lh)) 
     				{
     					$use_expense = 0;
     					if($_POST['load_id'] > 0) 
     					{
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
     					if($use_expense > 0)
     					{
     						echo "
     							<tr>
     								<td>$row_expenses_variable[fvalue]</td>
     								<td align='right' colspan='2'>
     									$use_expense
     									
     								</td>
     							</tr>
     						";	//$row_expenses_variable[id]
     					}
     				}
     				
     				?>
     				
     				<tr>
     					<td>Fuel Surcharge</td>
     					<td colspan='2'>$<?=number_format(($_POST['actual_bill_customer']),2)?></td>
     				</tr>
     				<tr>
     					<td>Flat Fuel Rate for Bill<!--<input type='hidden' name='fuel_surcharge_override' id='fuel_surcharge_override' value='0.00'>--></td>
     					<td colspan='2'>$<?=number_format(($_POST['actual_bill_customer']),2)?></td>
     				</tr>
     				<tr>
     					<td colspan='3'><hr></td>
     				</tr>
     				<tr>
     					<td>Bill Customer</td>
     					<td colspan='2'><b>$<?=number_format(($_POST['actual_bill_customer'] + $_POST['misc_detention']) ,2)?></b></td>
     				</tr>     				
     				</table>
     			</td>	
     		</tr>
     		</table>
     		</td>
     	</tr>
     	</table>
     	
     	</form>
     </div>
	</td>
</tr>
</table>

<script type='text/javascript'>
	var load_id = <?=(isset($_GET['load_id']) ? $_GET['load_id'] : 0)?>;
	
	$().ready(function() 
	{			
		//calc_totals();	
		//calc_all();	
		
		//printing like the accounting side....
   		print_block='printable_area1';
   			
   		if(print_block!='')
   		{	
   			obj_holder = $('#'+print_block+'');
			obj_wrapper_holder = "";
			
			$(obj_holder).wrap("<div id='"+print_block+"_print_wrapper' />");
			
			obj_wrapper_holder = $('#'+print_block+'_print_wrapper');
   		}	
   		
   		window.print();	
	});
	
	function mrr_print_report() 
	{
		//print_icon_holder = "print_icon";
		//$('#'+print_icon_holder).attr('src','images/loader.gif');
		//alert("entering special print, coming soon...");
		$.ajax({
			url: "print_report.php",
			dataType: "xml",
			type: "post",
			data: {
				script_name: "<?=$_SERVER['SCRIPT_NAME']?>",
				report_title: "Conard Load Printout",
				'display_mode':"0",
				'form_mode':"1",
				report_contents: encodeURIComponent(html_entity_decode($(obj_wrapper_holder).html()))
			},
			error: function() {
				$.prompt("General error printing load");
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
	
	function calc_totals() {
		var total = 0;
		$('.rate_field').each(function() {
			total += get_amount($(this).val());
			$(this).val(formatCurrency($(this).val()));
		});
		$('#quote').val(formatCurrency(total));
	}
		
	function calc_all() {
		
		// calc the quote side
		var daily_cost = <?=get_daily_cost()?>;
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
	
	
</script>