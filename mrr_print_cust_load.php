<? include('application.php') ?>
<?	
	if(isset($_GET['load_id'])) $_POST['load_id'] = $_GET['load_id'];
	if(!isset($_POST['load_id'])) $_POST['load_id'] = 0;
		
	$mrr_load_id=$_POST['load_id'];		
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
		$_POST['cust_email'] = $row['email'];
		$_POST['cust_phone'] = $row['phone'];
		$_POST['cust_phone2'] = $row['phone2'];
		$_POST['cust_fax'] = $row['fax'];
		$_POST['cust_cont_name'] = $row['contact_primary'];
		$_POST['cust_cont_email'] = $row['contact_email'];
		$_POST['cust_name'] = $row['name_company'];
         
        $_POST['misc_detention']=$row['misc_detention'];
		
		
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
	
	if(!isset($_POST['misc_detention']))            $_POST['misc_detention']="0.00";
	
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
?>
<? $no_header = 1 ?>
<? include('header.php') ?>

<table>
<tr>
	<td valign='top'>	
		<form name='mainform' action='<?=$_SERVER['SCRIPT_NAME']?><?=($mrr_mledit > 0 ? "?load_id=".$_POST['load_id']."" : "") ?>' method='post' style='text-align:left'>
		
		<input type='hidden' name='load_id' id='load_id' value="<?=$_POST['load_id']?>">
		
		<table class='section0_long' border='0'>
		<tr>
			<td width='100' valign='top'>Load ID</td>
			<td valign='top'><?=$_POST['load_id']?></td>
			<td valign='top'></td>
			<td valign='top'><?=$_POST['cust_name']?></td>
		</tr>
		<tr>
			<td valign='top'>Contact Name</td>
			<td valign='top'><?=$_POST['cust_cont_name']?></td>
			<td valign='top'>Contact Email</td>
			<td valign='top'><?=$_POST['cust_cont_email']?></td>
		</tr>
		<tr>
			<td valign='top'>Phone Number</td>
			<td valign='top'><?=$_POST['cust_phone']?></td>
			<td valign='top'>Phone Number</td>
			<td valign='top'><?=$_POST['cust_phone2']?></td>
		</tr>
		<tr>
			<td valign='top'>Fax Number</td>
			<td valign='top'><?=$_POST['cust_fax']?></td>
			<td valign='top'></td>
			<td valign='top'></td>
		</tr>
			
		
		<tr>
			<td valign='top' colspan='2'>Address</td>
			<td valign='top' colspan='2'>Billing Address</td>
		</tr>
		<tr>
			<td valign='top'>Line 1</td>
			<td valign='top'><?=$_POST['cust_addr1']?></td>
			<td valign='top'>Line 1</td>
			<td valign='top'><?=$_POST['cust_baddr1']?></td>
		</tr>	
		<tr>
			<td valign='top'>Line 2</td>
			<td valign='top'><?=$_POST['cust_addr2']?></td>
			<td valign='top'>Line 2</td>
			<td valign='top'><?=$_POST['cust_baddr2']?></td>
		</tr>
		<tr>
			<td valign='top'>City, State, Zip</td>
			<td valign='top'><?=$_POST['cust_city']?>, <?=$_POST['cust_state']?> <?=$_POST['cust_zip']?></td>
			<td valign='top'>City, State, Zip</td>
			<td valign='top'><?=$_POST['cust_bcity']?>, <?=$_POST['cust_bstate']?> <?=$_POST['cust_bzip']?></td>
		</tr>		
		</table>
		<table class='section1_long' style='width:900px'>		
		<tr>
			<td colspan='5' id='stop_holder'></td>
		</tr>
		</table>
		<table class='section4_long'>
		<tr>
			<td colspan='10'>
				<span id='dispatch_holder'></span>
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
     					<td colspan='2'><span class='section_heading'>Billing Section</span></td>
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
     					<td width='100'>Update Surcharge</td>
     					<td><input name='update_fuel_surcharge' id='update_fuel_surcharge' value='<?= $_POST['update_fuel_surcharge'] ?>' class='input_medium mrr_input_date'></td>
     				</tr>
     				<tr>
     					<td colspan='2'><hr></td>
     				</tr>
     				<tr>
     					<td width='100'>Loaded Miles</td>
     					<td>
     						<input style='text-align:right' name='actual_miles' id='actual_miles' value='<?=$_POST['actual_miles']?>' class='xlocked'>
     					</td>
     				</tr>
     				<tr>
     					<td>Deadhead Miles</td>
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
     					<td nowrap>Fuel Surcharge per Mile <span id='actual_fuel_surcharge_per_mile_holder'></span></td>
     					<td>
     						<input style='text-align:right;width:58px' name='actual_fuel_surcharge_per_mile' id='actual_fuel_surcharge_per_mile' value='<?=$_POST['actual_fuel_surcharge_per_mile']?>' class='rate_field calc_watch'>
     						<input style='text-align:right;width:76px' name='actual_fuel_surcharge_per_mile_total' id='actual_fuel_surcharge_per_mile_total' value='' class='rate_field calc_watch xlocked'>
     					</td>
     				</tr>
     				<tr>
     					<td colspan='2'>
     						<a href='javascript:void(0)' onclick="$('.actual_details').toggle()">toggle details</a>
     					</td>
     				</tr>
     				<tr class='actual_details'>
     					<td>Days Run (OTR) </td>
     					<td>
     						<input name='actual_days_run_otr' id='actual_days_run_otr' value='<?=$_POST['actual_days_run_otr']?>' style='text-align:right;width:60px' class='xlocked'>
     						<input name='actual_days_run_otr_total' id='actual_days_run_otr_total' value='$<?=(isset($actual_days_run_otr_total) ? money_format('',$actual_days_run_otr_total) : "0.00")?>' style='text-align:right;width:80px' class='xlocked'>
     					</td>
     				</tr>
     				<tr class='actual_details'>
     					<td>Days Run (Hourly)</td>
     					<td>
     						<input name='actual_days_run_hourly' id='actual_days_run_hourly' value='<?=$_POST['actual_days_run_hourly']?>' style=';background-color:#ffa569;text-align:right;width:60px' class='xlocked'>
     						<input name='actual_days_run_hourly_total' id='actual_days_run_hourly_total' value='$<?=(isset($actual_days_run_hourly_total) ? money_format('',$actual_days_run_hourly_total) : "0.00")?>' style=';background-color:#ffa569;text-align:right;width:80px' class='xlocked'>
     					</td>
     				</tr>
     				<tr class='actual_details'>
     					<td nowrap>Loaded Miles for Hourly<br>(local work)</td>
     					<td>
     						<input name='actual_loaded_miles_hourly' id='actual_loaded_miles_hourly' value='<?=$_POST['actual_loaded_miles_hourly']?>' style='text-align:right;width:60px' class='xlocked'>
     						<input name='actual_loaded_miles_hourly_total' id='actual_loaded_miles_hourly_total' value='$<?=(isset($actual_loaded_miles_hourly_total) ? money_format('',$actual_loaded_miles_hourly_total) : "0.00")?>' style='text-align:right;width:80px' class='xlocked'>
     					</td>
     				</tr>
     				<tr class='actual_details'>
     					<td>Hours Worked </td>
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
                         <td valign='top'>Detention</td>
                         <td valign='top'>
                             <input name='misc_detention' id='misc_detention' style='text-align:right' value='$<?=number_format($_POST['misc_detention'],2) ?>' class='rate_field calc_watch xlocked'>
                         </td>
                    </tr>
     				<tr>
     					<td>Fuel Surcharge</td>
     					<td><input style='text-align:right' name='fuel_surcharge_holder' id='fuel_surcharge_holder' value='' class='rate_field calc_watch xlocked'></td>
     				</tr>
     				<tr>
     					<td>Flat Fuel Rate for Bill<input type='hidden' name='fuel_surcharge_override' id='fuel_surcharge_override' value='0.00'></td>
     					<td><input style='text-align:right' name='flat_fuel_rate_amount' id='flat_fuel_rate_amount' value='0.00' class='rate_field calc_watch xlocked'></td>
     				</tr>
     				<tr>
     					<td colspan='4'><hr></td>
     				</tr>
     				<tr>
     					<td>Bill Customer </td>
     					<td><input name='actual_bill_customer' id='actual_bill_customer_calc' style='text-align:right;background-color:#8fff8f;' value='' class='rate_field calc_watch xlocked'></td>
     				</tr>     				
     				</table>
     			</td>	
     		</tr>
     		</table>
     		</td>
     	</tr>
     	</table>
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>
	var load_id = <?=(isset($_GET['load_id']) ? $_GET['load_id'] : 0)?>;
</script>