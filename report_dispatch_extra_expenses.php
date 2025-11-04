<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}


	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_driver 		= true;
	$rfilter->show_truck 		= true;
	$rfilter->show_trailer 		= true;
	$rfilter->show_load_id 		= true;
	$rfilter->show_dispatch_id 	= true;
	$rfilter->show_dispatches	= true;
	$rfilter->show_only_invoiced	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

 	if(isset($_POST['build_report'])) { 
	
		$search_date_range = '';
		if($_POST['dispatch_id'] != '' || $_POST['load_handler_id'] != '') {
		} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and trucks_log.linedate_pickup_eta<= '".date("Y-m-d", strtotime($_POST['date_to']))."'
			";
		}
	
		$sql = "
			select trucks_log.*,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company,
				load_handler.actual_bill_customer,
				load_handler.actual_total_cost,
				load_handler.origin_city,
				load_handler.origin_state,
				load_handler.dest_city,
				load_handler.dest_state,
				load_handler.linedate_pickup_eta,
				load_handler.actual_bill_customer - load_handler.actual_total_cost as load_profit,
				(select sum(expense_amount) from dispatch_expenses where dispatch_id = trucks_log.id and deleted = 0) as extra_expenses
			
			from trucks_log
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				left join load_handler on load_handler.id = trucks_log.load_handler_id
			where trucks_log.deleted = 0
			
				$search_date_range
				
				".($_POST['dispatch_id'] ? " and trucks_log.id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
				".($_POST['load_handler_id'] ? " and trucks_log.load_handler_id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				".(isset($_POST['show_only_invoiced']) ? " and load_handler.invoice_number != '' " : '') ."
			
			
			order by ".(isset($_POST['group_by_truck']) ? "trucks_log.truck_id " : " trucks_log.load_handler_id ")."
		";
		$data = simple_query($sql);
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:950px;text-align:left'>
	<tr>
		<td colspan='10'>
			<center>
			<span class='section_heading'>Sales Report</span>
			</center>
		</td>
	</tr>
	<? 
		$header_column = "
			<tr style='font-weight:bold'>
				<td nowrap>Load ID</td>
				<td nowrap>Dispatch ID</td>
				<td>Driver</td>
				<td>Origin</td>
				<td>Destination</td>
				<td align='right'>Miles</td>
				<td align='right'>Deadhead</td>
				<td>Date</td>
				<td>Truck</td>
				<td>Trailer</td>
				<td>Customer</td>
				<td nowrap align='right' style='padding-right:10px'>Extra Expenses</td>
				<td align='right' style='padding-right:10px'>Cost</td>
				<td align='right' nowrap>Bill Customer</td>
				<td align='right'>Profit</td>
			</tr>
		";
		
		//echo $header_column;
	
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_profit = 0;
		$total_cost = 0;
		$total_extra = 0;
		$total_sales = 0;
		$last_load_id = 0;
		$last_truck_id = 0;
		while($row = mysqli_fetch_array($data)) {
			$counter++;
			
			$total_miles += $row['miles'];
			$total_deadhead += $row['miles_deadhead'];

			if($counter % 20 == 1) echo $header_column;
			
			if($last_load_id != $row['load_handler_id']) {
				$last_load_id = $row['load_handler_id'];
				
				$load_miles = $row['miles'];
				$load_miles_deadhead = $row['miles_deadhead'];
				while($row = mysqli_fetch_array($data)) {
					if($row['load_handler_id'] != $last_load_id) break;
					$load_miles += $row['miles'];
					$load_miles_deadhead += $row['miles_deadhead'];
				}
				mysqli_data_seek($data, $counter - 1);
				$row = mysqli_fetch_array($data);
				
				echo "
					<tr class='odd'>
						<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td nowrap>$row[origin_city], $row[origin_state]</td>
						<td nowrap>$row[dest_city], $row[dest_state]</td>
						<td align='right'>".number_format($load_miles)."</td>
						<td align='right'>".number_format($load_miles_deadhead)."</td>
						<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td nowrap>$row[name_company]</td>
						<td align='right'>$".money_format('',$row['extra_expenses'])."</td>
						<td align='right'>$".money_format('',$row['actual_total_cost'])."</td>
						<td align='right'>$".money_format('',$row['actual_bill_customer'])."</td>
						<td align='right'>".($row['load_profit'] <= 0 ? "<span style='color:red'>" : "<span>")."$".money_format('',$row['load_profit'])."</span></td>
					</tr>
				";
			}
			
			$use_bill_customer = $row['cost'] + $row['profit'];
			$total_profit += $row['profit'];
			$total_cost += $row['cost'];
			$total_extra += $row['extra_expenses'];
			$total_sales += $use_bill_customer;
			
			if(isset($_POST['show_dispatches'])) {
				echo "
					<tr class='even'>
						<td>&nbsp;</td>
						<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
						<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
						<td nowrap>$row[origin], $row[origin_state]</td>
						<td nowrap>$row[destination], $row[destination_state]</td>
						<td align='right' style='color:#aaaaaa'>".number_format($row['miles'])."</td>
						<td align='right' style='color:#aaaaaa'>".number_format($row['miles_deadhead'])."</td>
						<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
						<td nowrap>$row[name_truck]</td>
						<td nowrap>$row[trailer_name]</td>
						<td nowrap>&nbsp;</td>
						<td align='right' style='color:#aaaaaa'>$".money_format('',$row['cost'])."</td>
						<td align='right'>&nbsp;</td>
						<td align='right' style='color:#aaaaaa'>$".money_format('',$row['profit'])."</td>
					</tr>
				";
			}
			
			// if the user selected to show the detailed stops for this dispatch, load them and show them
			if(isset($_POST['show_stops'])) {
				$sql = "
					select *
					
					from load_handler_stops
					where deleted = 0
						and trucks_log_id = '$row[id]'
					order by linedate_pickup_eta
				";
				$data_stops = simple_query($sql);
				
				while($row_stop = mysqli_fetch_array($data_stops)) {
					echo "
						<tr style='background-color:#e2ffe4'>
							<td></td>
							<td nowrap align='right'>Stop:</td>
							<td colspan='2' nowrap>Shipper: $row_stop[shipper_name]</td>
							<td colspan='4' nowrap>$row_stop[shipper_address1] $row_stop[shipper_city], $row_stop[shipper_state] $row_stop[shipper_zip]</td>
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
		<td colspan='15'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='4'></td>
		
		<td align='right' nowrap><b>Total Miles</b></td>
		<td align='right' nowrap><b>Total Deadhead</b></td>
		<td colspan='4'>&nbsp;</td>
		<td align='right' nowrap><b>Total Extra</b></td>
		<td align='right' nowrap><b>Total Cost</b></td>
		<td align='right' nowrap><b>Total Sales</b></td>
		<td align='right' nowrap><b>Total Profit</b></td>
	</tr>
	<tr>
		<td></td>
		<td colspan='4'><?=number_format($counter)?> dispatch(es)</td>
		
		<td align='right'><?=number_format($total_miles)?></td>
		<td align='right'><?=number_format($total_deadhead)?></td>
		<td colspan='4'>&nbsp;</td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_extra)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_cost)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_sales)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_profit)?></td>
	</tr>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
</script>
<? include('footer.php') ?>