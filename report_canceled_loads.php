<? include('header.php') ?>

<table style='text-align:left;width:900px;margin:10px'>
<tr>
	<td valign='top'>
<?
	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_load_id 		= true;
	//$rfilter->show_dispatch_id 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

	$sql = "
		select load_handler.*,
			customers.name_company,
			(select sum(expense_amount) from load_handler_actual_var_exp lhave where lhave.load_handler_id = load_handler.id) as variable_expenses,
			(select actual_fuel_surcharge_per_mile * (select sum(miles) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id)) as fuel_expense
		
		from load_handler
			left join customers on customers.id = load_handler.customer_id
		where load_handler.deleted >0		
			".($_POST['customer_id'] ? " and customers.id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
			and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
			and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
		order by load_handler.linedate_pickup_eta desc, load_handler.id desc
	";	
	$data = simple_query($sql);
	
	ob_start();
?>
	</td>	
	<td valign='top' width='600'>
		<div id='mrr_load_details'></div>
	</td>
</tr>
</table>

<div style='clear:both'></div>

<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>Canceled Loads</h3>
</div>
<div style='clear:both'></div>
<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:900px;margin:10px'>
<thead>
<tr>
	<th nowrap><b>Load ID</b></th>
	<th><b>Customer</b></th>
	<th><b>Date</b></th>
	<th><b>Origin</b></th>
	<th><b>Destination</b></th>	
	<th align='right'><b>Cost</b></th>
	<th align='right'><b>Bill</b></th>
	<th align='right'><b>Profit</b></th>
	<th><b>Truck(s)</b></th>
	<th><b>Driver(s)</b></th>
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
		
		// get the trucks and drivers for this load
		$sql = "
			select trucks_log.*,
				trucks.name_truck,
				drivers.name_driver_first,
				drivers.name_driver_last
			
			from trucks_log
				left join trucks on trucks.id = trucks_log.truck_id
				left join drivers on drivers.id = trucks_log.driver_id
			where load_handler_id = '$row[id]'
				
			order by trucks_log.linedate_pickup_eta
		";	//and trucks_log.deleted = 0
		$data_dispatch = simple_query($sql);
		$truck_array = array();
		$driver_array = array();
		while($row_dispatch = mysqli_fetch_array($data_dispatch)) {
			$truck_array[] = " " . trim($row_dispatch['name_truck']);
			$driver_array[] = " " . trim($row_dispatch['name_driver_last']);
		}
		$deleted=$row['deleted'];
		$total_cost += $row['actual_total_cost'];
		$total_bill += $row['actual_bill_customer'];
		$total_profit += $profit;
		
		$total_expense = $row['variable_expenses'] + $row['fuel_expense'];
		
		$activator="<span class='mrr_link_like_on' title='Click to restore this Load to active status' onClick='mrr_restore_load(".$row['id'].",0);'>Restore</span>";		
		echo "
			<tr class='load_".$row['id']."'>
				<td><a href='manage_load.php?load_id=$row[id]' target='edit_load_$row[id]'>$row[id]</a></td>
				<td nowrap>$row[name_company]</td>
				<td nowrap>".date("m-d-Y", strtotime($row['linedate_pickup_eta']))."</td>
				<td nowrap>$row[origin_city], $row[origin_state]</td>
				<td nowrap>$row[dest_city], $row[dest_state]</td>			
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_total_cost'])."</td>
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_bill_customer'])."</td>
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; ".($profit <= 0 ? "<span class='alert'>" : "<span class=''>")."$".money_format('',$profit)."</span></td>
				<td>".implode(",", $truck_array)."</td>
				<td nowrap>".implode(",", $driver_array)."</td>
				<td>&nbsp;".$activator."&nbsp;</td>
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
	
	function sicap_view_invoice_handler(load_id) {
		sicap_view_invoice($('#sicap_invoice_number_holder_'+load_id).html());
	}
	
	function mrr_restore_load(loadid,moder)
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_restore_canceled_load",
			data: {
					"load_id" : loadid,
					"moder" : moder         					
				},
			type: "POST",
			cache:false,
			async:false,
			dataType: "xml",
			success: function(xml) {
				if($(xml).find('mrrTab').text() != "Restored") {
					$.prompt("No Load found.");					
				}
				else
				{
					$('.load_'+loadid).hide();
				}				
			}
     	});	
	}
	function mrr_display_load_dispatches(loadid)
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_restore_canceled_load",
			data: {
					"load_id" : loadid,
					"moder" : moder         					
				},
			type: "POST",
			cache:false,
			async:false,
			dataType: "xml",
			success: function(xml) {
				if($(xml).find('mrrTab').text() != "Restored") {
					$.prompt("No Load found.");					
				}
				else
				{
					$('.load_'+loadid).hide();
				}				
			}
     	});	
	}
	
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