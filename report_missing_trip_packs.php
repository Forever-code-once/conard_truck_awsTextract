<? include('header.php') ?>
<?
	if(isset($_POST['trip_pack'])) {
		foreach($_POST['trip_pack'] as $dispatch_id) {
			
			$sql = "
				update trucks_log
				set valid_trip_pack = '1',
					user_id_verified_trip_pack = '".sql_friendly($_SESSION['user_id'])."'
					
				where id = '".sql_friendly($dispatch_id)."'
			";
			simple_query($sql);
			
		}
	}

	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_load_id 		= true;
	$rfilter->show_dispatch_id 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

	$sql = "
		select trucks_log.*,
			load_handler.actual_bill_customer,
			load_handler.actual_total_cost,
			customers.name_company,
			trucks.name_truck,
			drivers.name_driver_first,
			drivers.name_driver_last
		
		from trucks_log
			left join load_handler on load_handler.id = trucks_log.load_handler_id
			left join customers on customers.id = load_handler.customer_id
			left join drivers on drivers.id = trucks_log.driver_id
			left join trucks on trucks.id = trucks_log.truck_id
		where load_handler.deleted = 0
			and load_handler.preplan = 0
			and (load_handler.invoice_number is null or load_handler.invoice_number = '')
			and (load_handler.sicap_invoice_number = '' or sicap_invoice_number is null)
			and valid_trip_pack = 0
			and load_handler.linedate_dropoff_eta < now()
			and trucks_log.deleted = 0
			".($_POST['customer_id'] ? " and customers.id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
			and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
			and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime("+1 day", strtotime($_POST['date_to'])))."'
		order by load_handler.linedate_pickup_eta, load_handler.id
	";
	$data = simple_query($sql);
	
	ob_start();
?>


<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>Loads missing Trip Packs</h3>
</div>
<div style='clear:both'></div>
<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:900px;margin:10px'>
<thead>
<tr>
	<th nowrap><b>Load ID</b></th>
	<th nowrap><b>Dispatch ID</b></th>
	<th><b>Customer</b></th>
	<th><b>Date</b></th>
	<th><b>Origin</b></th>
	<th><b>Destination</b></th>	
	<th align='right'><b>Cost</b></th>
	<th align='right'><b>Bill</b></th>
	<th align='right'><b>Profit</b></th>
	<th>Truck</th>
	<th>Driver</th>
	<th align='right' nowrap><b>Has Trip Pack?</b></th>
	<th>&nbsp;</th>
</tr>
</thead>
<tbody>
<? 
	$counter = 0;
	$total_cost = 0;
	$total_bill = 0;
	$total_profit = 0;
	while($row = mysqli_fetch_array($data)) {
		$counter++;
		$profit = $row['actual_bill_customer'] - $row['actual_total_cost'];
		
		
		$total_cost += $row['actual_total_cost'];
		$total_bill += $row['actual_bill_customer'];
		$total_profit += $profit;
		
		echo "
			<tr class='line_entry'>
				<td><a href='manage_load.php?load_id=$row[load_handler_id]' target='edit_load_$row[load_handler_id]'>$row[load_handler_id]</a></td>
				<td><a href='add_entry_truck.php?id=$row[id]' target='edit_load_$row[id]'>$row[id]</a></td>
				<td nowrap>$row[name_company]</td>
				<td nowrap>".date("m-d-Y", strtotime($row['linedate_pickup_eta']))."</td>
				<td nowrap>$row[origin], $row[origin_state]</td>
				<td nowrap>$row[destination], $row[destination_state]</td>			
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_total_cost'])."</td>
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_bill_customer'])."</td>
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; ".($profit <= 0 ? "<span class='alert'>" : "<span class=''>")."$".money_format('',$profit)."</span></td>
				<td>".$row['name_truck']."</td>
				<td nowrap>".$row['name_driver_first']." ".$row['name_driver_last']."</td>
				<td nowrap align='center'>
					<input type='checkbox' name='trip_pack[]' value='$row[id]'>
					<input type='hidden' name='load_id_array[]' value='$row[id]'>
				</td>
				<td>".($counter % 5 == 1 ? "<input type='submit' value='Update'>" : "")."</td>
			</tr>
		";
	}
?>
</tbody>
<tr>
	<td style='background-color:#bed3ec' colspan='5'>&nbsp;</td>
	<td style='background-color:#bed3ec' align='right'><b>$<?=money_format('',$total_cost)?></b></td>
	<td style='background-color:#bed3ec' align='right'><b>$<?=money_format('',$total_bill)?></b></td>
	<td style='background-color:#bed3ec' align='right'><b>$<?=money_format('',$total_profit)?></b></td>
	<td style='background-color:#bed3ec' colspan='5'>&nbsp;</td>
</tr>
</table>
</form>
<?
	$pdf = ob_get_contents();
	ob_end_clean();
?>
<script type='text/javascript'>
	
	
	$().ready(function() {

		
		$('.tablesorter').tablesorter({
		   			headers: {         				
		   				5: {sorter:'currency'},
		   				6: {sorter:'currency'},
		   				7: {sorter:'currency'},
		   				11: {sorter: false}
		   			}
		   				
		});
		
		$('.datepicker').datepicker();
	});
</script>
<?

	$link = print_contents('not_invoiced_'.createuuid(), $pdf);

	echo "
		<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
			<a href='$link'>Printed Version</a>
		</div>
		<div style='clear:both'></div>
		
	";

	echo $pdf;
	
?>
<? include('footer.php') ?>