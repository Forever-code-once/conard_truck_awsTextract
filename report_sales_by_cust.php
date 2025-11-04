<? $usetitle="Sales Report - By Customer"; ?>
<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['show_dispatches'] = 1;
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['truck_id'])) {
		$_POST['truck_id'] = $_GET['truck_id'];
		$_POST['show_dispatches'] = 1;
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['date_from'])) {
		$_POST['date_from'] = $_GET['date_from'];
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['date_to'])) {
		$_POST['date_to'] = $_GET['date_to'];
		$_POST['build_report'] = 1;
	}	
	
	$rfilter = new report_filter();
	$rfilter->show_customer 				= true;
	$rfilter->show_driver 				= true;
	$rfilter->show_truck 				= true;
	$rfilter->show_trailer 				= true;
	$rfilter->show_dispatches			= true;
	$rfilter->show_load_id 				= true;
	//$rfilter->group_by_truck			= true;
	$rfilter->first_dispatch_all_credit 	= true;
	$rfilter->hide_non_first_dispatch 		= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

	function get_line_amount($row, $mrr_use_base=0) 
	{
		global $defaultsarray;
		
		$line_bill_cust = $row['cost'] + $row['profit'];
		$line_profit = $row['profit'];
		$line_cost = $row['cost'];
		$line_miles = $row['miles'];
		$line_miles_deadhead = $row['miles_deadhead'];
		
		$line_miles_hr = $row['loaded_miles_hourly'];
		$line_deadhead_hr = $row['miles_deadhead_hourly'];	
		
		$line_mpg = $row['avg_mpg'];
		$line_fuel_charge = $row['actual_rate_fuel_surcharge'];
		$line_days = $row['daily_run_otr'] + $row['daily_run_hourly'];
		$line_first_dispatch = 1;
			
		$bill_cust_diff = money_strip(number_format($row['actual_bill_customer'] - $line_bill_cust,2));
		if(isset($_POST['first_dispatch_all_credit']) && $bill_cust_diff != 0) 
		{
			// user wants to give all the credit to the first truck dispatch on this load, all future dispatches/trucks associated with this load will
			// show a $0.00 amount when the user has this selected
			
			// this load has multiple dispatches, so check to see if there were any dispatches before this one, if not, then we'll use this one
			$sql = "
				select id
				
				from load_handler_stops
				where load_handler_id = '".sql_friendly($row['load_handler_id'])."'
					and trucks_log_id <> '".sql_friendly($row['id'])."'
					and linedate_pickup_eta < '".date("Y-m-d H:i:s", strtotime($row['linedate_pickup_eta_dispatch']))."'
					and deleted = 0
			";
			$data_check_earlier = simple_query($sql);
			//if($row['load_handler_id'] == 1064) echo $sql."  | ($line_bill_cust) | (".mysqli_num_rows($data_check_earlier).")<br>";
			if(mysqli_num_rows($data_check_earlier)) 
			{
				// there was an earlier load, so zero this load out
				$line_bill_cust = 0;
				$line_profit = 0;
				$line_cost = 0;
				$line_miles = 0;
				$line_mpg = 0;
				$line_fuel_charge = 0;
				$line_miles_deadhead = 0;
				$line_days = 0;
				$line_first_dispatch = 0;
				
				$line_miles_hr = 0;
				$line_deadhead_hr = 0;	
			} 
			else 
			{
				// this is the earliest dispatch, so apply the full credit to this one
				$line_bill_cust = $row['actual_bill_customer'];
				$line_profit = $row['load_profit'];
				$line_cost = $row['actual_total_cost'];
				$line_miles = $row['miles'];
				$line_mpg = $row['avg_mpg'];
				$line_fuel_charge = $row['actual_rate_fuel_surcharge'];
				$line_miles_deadhead = $row['miles_deadhead'];
				$line_days = $row['daily_run_otr'] + $row['daily_run_hourly'];
				
				$line_miles_hr = $row['loaded_miles_hourly'];
				$line_deadhead_hr = $row['miles_deadhead_hourly'];	
			}
		}
		
		if($line_mpg == 0) $line_mpg = $defaultsarray['average_mpg'];
		
		$line_array['bill_cust'] = $line_bill_cust;
		$line_array['profit'] = $line_profit;
		$line_array['cost'] = $line_cost;
		$line_array['miles'] = $line_miles;
		$line_array['miles_deadhead'] = $line_miles_deadhead;
		$line_array['avg_mpg'] = $line_mpg;
		$line_array['fuel_charge'] = $line_fuel_charge;
		$line_array['days'] = $line_days;
		$line_array['first_dispatch'] = $line_first_dispatch;
         
        $line_array['base_rate'] = $mrr_use_base;
		
		$line_array['loaded_miles_hourly'] = $line_miles_hr;
		$line_array['miles_deadhead_hourly'] = $line_deadhead_hr;
		
		return $line_array;		
	}

 	if(isset($_POST['build_report'])) 
 	{ 
		/*
 	    $date_ranger="
			and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
			and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'	
		";
        */
        $date_ranger="
		    and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
			and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
		";
        
		if($_POST['load_handler_id'] != '' || $_POST['load_handler_id'] > 0)			$date_ranger="and trucks_log.load_handler_id='".sql_friendly($_POST['load_handler_id'])."'";
		
		$sql = "
			select trucks_log.*,
				(select min(linedate_pickup_eta) from load_handler_stops where load_handler_stops.trucks_log_id = trucks_log.id and deleted = 0) as linedate_pickup_eta_dispatch,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company,
				load_handler.actual_bill_customer,
				load_handler.flat_fuel_rate_amount,
				load_handler.actual_total_cost,
				load_handler.origin_city,
				load_handler.origin_state,
				load_handler.dest_city,
				load_handler.dest_state,
				load_handler.invoice_number,
				load_handler.linedate_pickup_eta,
				load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount - load_handler.actual_total_cost as load_profit,
				load_handler.actual_rate_fuel_surcharge,
				(
				    select expense_amount 
				    from load_handler_actual_var_exp 
				    where load_handler_actual_var_exp.load_handler_id = trucks_log.load_handler_id and load_handler_actual_var_exp.expense_type_id='25'
				) as mrr_base_rate,
				trucks_log.daily_run_otr,
				trucks_log.daily_run_hourly
			
			from trucks_log
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				left join load_handler on load_handler.id = trucks_log.load_handler_id
			where trucks_log.deleted = 0
			
				".$date_ranger."
								
				".($_POST['driver_id'] ? " and trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			
			order by customers.name_company, load_handler.id, drivers.name_driver_last, drivers.name_driver_first, trucks.name_truck				
		";  //order by customers.name_company, drivers.name_driver_last, drivers.name_driver_first, trucks.name_truck
		$data = simple_query($sql);
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1200px;text-align:left'>
	<tr>
		<td colspan='19'>
			<center>
			<span class='section_heading'><?=$usetitle ?></span>
			</center>
		</td>
	</tr>
	<? 
		echo "
			<tr class='odd' style='font-size:14px;font-weight:bold'>
				<td colspan='6' style='font-size:16px;font-weight:bold'>Customer</td>				
				<td align='right'>Miles</td>
				<td align='right'>Hr Miles</td>
				<td align='right'>Deadhead</td>				
				<td align='right'>Hr Deadhead</td>
				<td align='right' nowrap>Fuel Cost</td>
				<td align='center'>Days</td>
				<td colspan='3'>&nbsp;</td>
				<td align='right'>Base</td>
				<td align='right'>Sales</td>
				<td align='right'>Cost</td>
				<td align='right'>Profit</td>
			</tr>
		";
		//<td colspan='6' style='font-size:16px;font-weight:bold'>Driver</td>
	
		$header_column = "
			<tr style='font-weight:bold'>
				<td nowrap>Load ID</td>
				<td nowrap>Dispatch ID</td>
				<td nowrap>Invoice</td>
				<td>Driver</td>
				<td>Origin</td>
				<td>Destination</td>
				<td align='right'>Miles</td>
				<td align='right'>Hr Miles</td>
				<td align='right'>Deadhead</td>
				<td align='right'>Hr Deadhead</td>
				<td>Date</td>
				<td align='center'>Days</td>
				<td>Truck</td>
				<td>Trailer</td>
				<td>Customer</td>
				<td align='right' nowrap>Base Rate</td>
				<td align='right' nowrap>Bill Customer</td>
				<td align='right' style='padding-right:10px'>Cost</td>
				<td align='right'>Profit</td>
			</tr>
		";
		
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		
		$total_miles_hr = 0;
		$total_deadhead_hr = 0;
		
		$total_profit = 0;
		$total_cost = 0;
		$total_sales = 0;
		$total_fuel = 0;
		$total_days = 0;
    
        $total_base = 0;
        $total_loads = 0;	
        $loads_arr[0]=0;
    
        $truck_loads = 0;
        $loads_cust[0]=0;
		
		$last_load_id = 0;
		$last_truck_id = 0;
		$last_driver_id = 0;
		$last_customer="";
    
        $truck_base=0;
        $mrr_use_base=0;
        $mrr_running_tot = 0;
		
		while($row = mysqli_fetch_array($data)) 
		{
			$counter++;
			
			$total_miles += $row['miles'];
			$total_deadhead += $row['miles_deadhead'];
			
			$total_miles_hr += $row['loaded_miles_hourly'];
			$total_deadhead_hr += $row['miles_deadhead_hourly'];
			
			$flat_rate_fuel_charge=$row['flat_fuel_rate_amount'];
			
			if($last_driver_id != $row['customer_id']) 
			{	//$last_driver_id != $row['driver_id']
				if($last_driver_id > 0)         echo "<tr><td colspan='19'><hr></td></tr>";
												
				$truck_profit = 0;
				$truck_cost = 0;
				$truck_sales = 0;
				$truck_miles = 0;
				$truck_miles_deadhead = 0;
				$truck_miles_hr =0;
				$truck_deadhead_hr =0;
				$truck_fuel = 0;
				$truck_days = 0;
				
                $truck_base=0;                
                //$mrr_use_base=0;
                $truck_loads = 0;
                $loads_cust[0]=0;
                 
                $total_counter = 0;
                 
                $mrr_running_tot=0;
                				
				mysqli_data_seek($data, $counter - 1);
				while($row_tmp = mysqli_fetch_array($data)) 
				{
					$total_counter++;
					//if($row_tmp['truck_id'] != $row['truck_id']) break; 
                    if($row_tmp['customer_id'] != $row['customer_id']) break;
                    
					$line_array = get_line_amount($row_tmp);      //,$truck_base,$mrr_use_base
					
					$truck_profit += ($line_array['profit'] + $flat_rate_fuel_charge);
					$truck_cost += $line_array['cost'];
					//$truck_sales += ($line_array['bill_cust'] + $flat_rate_fuel_charge);
					$truck_miles += $line_array['miles'];
					$truck_miles_deadhead += $line_array['miles_deadhead'];
					
					$truck_miles_hr += $line_array['loaded_miles_hourly'];
					$truck_deadhead_hr += $line_array['miles_deadhead_hourly'];
                     
                    //$truck_base = $line_array['base_rate'];                   
                     
                    $y_found=0;
                    for($y=0; $y < $truck_loads; $y++)
                    {
                         if($loads_cust[$y] == $row_tmp['load_handler_id'])       $y_found=1;
                    }
                    if($y_found == 0)
                    {   //not already found, so add the load base amount and increment the loads...  Reminder: 1 load can have more than 1 dispatch.
                         $loads_cust[$truck_loads] = $row_tmp['load_handler_id'];
     
                         $truck_base+=$row_tmp['mrr_base_rate'];
                         //$truck_base+=$row_tmp['mrr_base_rate'];
                         //$mrr_use_base=$row_tmp['mrr_base_rate'];
     
                         //$truck_sales += ($line_array['bill_cust'] + $flat_rate_fuel_charge);
     
                         $truck_sales += ($row_tmp['actual_bill_customer']);
     
                         $truck_loads++;
                    }
                     
                    $last_customer=trim($row_tmp['name_company']);
										
					$truck_days += $line_array['days'];
					if($line_array['avg_mpg'] > 0) $truck_fuel += ($line_array['miles'] + $line_array['miles_deadhead'] + $line_array['loaded_miles_hourly'] + $line_array['miles_deadhead_hourly']) / $line_array['avg_mpg'] * $line_array['fuel_charge'];
				}
				@mysqli_data_seek($data, $counter);
                 
                if($last_driver_id==0 || $last_customer=="")      $last_customer=trim($row['name_company']);
                 
                //$truck_sales=$mrr_running_tot;
								
				echo "
					<tr class='odd' style='font-size:14px;font-weight:bold'>
					    <td colspan='6' style='font-size:16px;font-weight:bold'><a href='admin_customers.php?eid=$last_driver_id' target='view_driver_$last_driver_id'>$last_customer</a></td>
						<td align='right'>".number_format($truck_miles)."</td>
						<td align='right'>".number_format($truck_miles_hr)."</td>
						<td align='right'>".number_format($truck_miles_deadhead)."</td>
						<td align='right'>".number_format($truck_deadhead_hr)."</td>
						<td align='right'>$".money_format('', $truck_fuel)."</td>
						<td align='center'>".$truck_days."</td>
						<td align='right' colspan='3'>".$truck_loads." Load(s)</td>
						<td align='right'>$".number_format($truck_base,2)."</td>
						<td align='right'>$".money_format('', $truck_sales)."</td>
						<td align='right'>$".money_format('', $truck_cost)."</td>
						<td align='right'>$".money_format('', $truck_profit)."</td>
					</tr>
				";	//drivers.name_driver_last, drivers.name_driver_first, trucks.name_truck
                 //<td>Truck  <a href='admin_trucks.php?id=$row_tmp[truck_id]' target='view_truck_$row_tmp[truck_id]'>$row_tmp[name_truck]</a></td>
                 //<td colspan='6' style='font-size:16px;font-weight:bold'><a href='admin_drivers.php?id=$row[driver_id]' target='view_driver_$row[driver_id]'>$row[name_driver_first] $row[name_driver_last]</a></td>
				if(isset($_POST['show_dispatches'])) echo $header_column;
				//$last_driver_id = $row['driver_id'];
                $last_driver_id = $row['customer_id'];
                 
                $last_customer=trim($row['name_company']);
                 
                $truck_base=0;
                //$mrr_running_tot=0;
			}
			
            //$mrr_use_base=0;
            $z_found=0;
            for($z=0; $z < $total_loads; $z++)
            {
                  if($loads_arr[$z] == $row['load_handler_id'])       $z_found=1;
            }
            if($z_found == 0)
            {   //not already found, so add the load base amount and increment the loads...  Reminder: 1 load can have more than 1 dispatch.
                  $loads_arr[$total_loads] = $row['load_handler_id'];
                  
                  $total_base+=$row['mrr_base_rate'];
                  //$truck_base+=$row['mrr_base_rate'];
                  $mrr_use_base=$row['mrr_base_rate'];
                  
                  $total_loads++;
            }
             
            $line_array = get_line_amount($row,$truck_base);      //,$mrr_use_base
             
            $line_bill_cust = $line_array['bill_cust'] + $flat_rate_fuel_charge;
			$line_profit = $line_array['profit'] + $flat_rate_fuel_charge;
			$line_cost = $line_array['cost'];
			$line_fuel = 0;
			$line_days = $line_array['days'];
			if($line_array['avg_mpg'] > 0) $line_fuel = ($line_array['miles'] + $line_array['miles_deadhead'] + $line_array['loaded_miles_hourly'] + $line_array['miles_deadhead_hourly']) / $line_array['avg_mpg'] * $line_array['fuel_charge'];
			
			//$use_bill_customer = $line_bill_cust;
            $use_bill_customer = $row['actual_bill_customer'];
            if($z_found > 0)
            {
                 $use_bill_customer=0;
            }
            $mrr_running_tot+=$use_bill_customer;
            		
			
			$total_profit += $line_profit;
			$total_cost += $line_cost;
			$total_fuel += $line_fuel;
			$total_days += $line_days;
			$total_sales += $use_bill_customer;
             
            $last_driver_id = $row['customer_id'];
            $last_customer=trim($row['name_company']);

			if(isset($_POST['show_dispatches'])) 
			{
				if($line_array['first_dispatch'] == 0 && isset($_POST['hide_non_first_dispatch'])) 
				{
					// not the first dispatch, so don't show it, since we're already giving credit to the first one, no need to show the $0 dispatchs
				} 
				else 
				{
					echo "
						<tr class='even'>
							<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
							<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
							<td nowrap>$row[invoice_number]</td>
							<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
							<td nowrap>$row[origin], $row[origin_state]</td>
							<td nowrap>$row[destination], $row[destination_state]</td>
							<td align='right'>".number_format($row['miles'])."</td>
							<td align='right'>".number_format($row['loaded_miles_hourly'])."</td>
							<td align='right'>".number_format($row['miles_deadhead'])."</td>
							<td align='right'>".number_format($row['miles_deadhead_hourly'])."</td>
							<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
							<td nowrap align='center'>".($row['daily_run_otr'] + $row['daily_run_hourly'])."</td>
							<td><a href='admin_trucks.php?id=$row[truck_id]' target='view_truck_$row[truck_id]'>$row[name_truck]</a></td>
							<td nowrap>$row[trailer_name]</td>
							<td nowrap>$row[name_company]</td>
							<td align='right' nowrap>$".money_format('',$mrr_use_base)."</td>
							<td align='right' nowrap>$".money_format('',$use_bill_customer)."</td>
							<td align='right' nowrap>$".money_format('',$line_cost)."</td>
							<td align='right'>$".money_format('',$line_profit)."</td>
						</tr>
					";                                //  (Load ".$row['load_handler_id'].") Total=$".number_format($mrr_running_tot,2)."
                    $mrr_use_base=0;
				}
			}
			
			// if the user selected to show the detailed stops for this dispatch, load them and show them
			if(isset($_POST['show_stops'])) 
			{
				$sql = "
					select *					
					from load_handler_stops
					where deleted = 0
						and trucks_log_id = '$row[id]'
					order by linedate_pickup_eta
				";
				$data_stops = simple_query($sql);				
				while($row_stop = mysqli_fetch_array($data_stops)) 
				{
					echo "
						<tr style='background-color:#e2ffe4'>
							<td></td>
							<td nowrap align='right'>Stop:</td>
							<td colspan='3' nowrap>Shipper: $row_stop[shipper_name]</td>
							<td colspan='7' nowrap>$row_stop[shipper_address1] $row_stop[shipper_city], $row_stop[shipper_state] $row_stop[shipper_zip]</td>
							<td colspan='2'>Pickup: ".date("M j, Y H:i", strtotime($row_stop['linedate_pickup_eta']))."</td>
							<td colspan='2'>
								".($row_stop['linedate_completed'] > 0 ? "Completed: ".date("M j, Y H:i", strtotime($row_stop['linedate_completed'])) : "")."
							</td>
						</tr>
					";
				}
			}
		}
	?>
	<tr>
		<td colspan='19'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='5'></td>
		<td align='right' nowrap><b>Total Miles</b></td>
		<td align='right' nowrap><b>Total Hr Miles</b></td>
		<td align='right' nowrap><b>Total Deadhead</b></td>		
		<td align='right' nowrap><b>Total Hr Deadhead</b></td>
		<td align='right' nowrap><b>Total Fuel</b></td>
		<td align='right' nowrap><b>Total Days</b></td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align='right' nowrap><b>Total Loads</b></td>
        <td align='right' nowrap><b>Total Base</b></td>
		<td align='right' nowrap><b>Total Sales</b></td>
		<td align='right' nowrap><b>Total Cost</b></td>
		<td align='right' nowrap><b>Total Profit</b></td>
	</tr>
	<tr>
		<td></td>
		<td colspan='5'><?=number_format($counter)?> dispatch(es)</td>		
		<td align='right'><?=number_format($total_miles)?></td>
		<td align='right'><?=number_format($total_miles_hr)?></td>
		<td align='right'><?=number_format($total_deadhead)?></td>		
		<td align='right'><?=number_format($total_deadhead_hr)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_fuel)?></td>
		<td align='center'><?=number_format($total_days,2) ?></td>
		<td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align='right'><?=$total_loads ?></td>
        <td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_base)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_sales)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_cost)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_profit)?></td>
	</tr>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
</script>
<? include('footer.php') ?>