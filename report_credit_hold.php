<? include('header.php') ?>
<?
	$sql2="
		select customers.id,
			customers.name_company,
			customers.slow_pays,
			customers.override_slow_pays,
			customers.credit_hold,
			customers.override_credit_hold,
			customers.payment_notes
		from customers
		where customers.deleted = 0
			and (customers.credit_hold>0 or customers.slow_pays>0)
		order by customers.credit_hold desc,customers.name_company asc,customers.id asc";
	$data2 = simple_query($sql2);	
?>
<table class='admin_menu1' style='text-align:left;width:1500px;'>
<tr>
	<td valign='top'>	
<?	
	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_load_id 		= true;
	//$rfilter->show_dispatch_id 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
?>
	</td>
     <td valign='top'>
     	<div style='max-height:300px;height:300px; overflow:auto;'>
     	<?
     	$pdf_2="";
     	ob_start(); 
     	?>    
     	<center><h3>All Customers with Slow/Hold</h3>	</center>
     	<table class='admin_menu3 tablesorter font_display_section' id='table_sort2' style='text-align:left;width:850px;margin:10px'>
     	<thead>
          <tr>
          	<th><b>Customer</b></th>
          	<th nowrap><b>Pays</b></th>
          	<th nowrap><b>Credit</b></th>
          	<th><b>Payment Notes</b></th>
          </tr>
          </thead>
          <tbody>
          <?
          	while($row2 = mysqli_fetch_array($data2)) 
          	{
          		if(($row2['credit_hold'] > 0 && $row2['override_credit_hold']==0) || ($row2['slow_pays'] > 0 && $row2['override_slow_pays']==0))
          		{
               		echo "
               			<tr>
               				<td valign='top'><a href='admin_customers.php?eid=".$row2['id']."' target='_blank'>".$row2['name_company']."</a></td>
               				<td valign='top'>".($row2['slow_pays'] > 0 ? "<span class='alert'>Slow</span>" : "")."</td>
               				<td valign='top'>".($row2['credit_hold'] > 0 ? "<span class='alert'>Hold</span>" : "")."</td>
               				<td valign='top'>".trim($row2['payment_notes'])."</td>
               			</tr>
               		";
               		//".$row2['override_credit_hold']."
               		//".$row2['override_slow_pays']."
          		}	
          	}
          ?>	
          </tbody>
     	</table>	    	 	
    	 	<?
    	 		$pdf_2 = ob_get_contents();
			ob_end_clean();
			
			echo $pdf_2;
		?>
		</div>
     </td>
</tr>
</table>

<?
	$sql2 = "     				
     	update customers set
     		credit_hold='0',
     		slow_pays='0'
     	";
     //simple_query($sql2);
	
	$sql = "
		select load_handler.*,
			customers.name_company,
			customers.slow_pays,
			customers.override_slow_pays,
			customers.credit_hold,
			customers.override_credit_hold,
			(select sum(expense_amount) from load_handler_actual_var_exp lhave where lhave.load_handler_id = load_handler.id) as variable_expenses,
			(select actual_fuel_surcharge_per_mile * (select sum(miles) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id)) as fuel_expense
		
		from customers
			left join load_handler on load_handler.customer_id = customers.id
		where customers.deleted = 0
			and (customers.credit_hold>0 or customers.slow_pays>0)
			".($_POST['customer_id'] ? " and customers.id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
			and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
		order by load_handler.linedate_pickup_eta, load_handler.id
	";
	$data = simple_query($sql);
	
	
	
	ob_start();
?>


<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>Credit Hold and Slow Pay Customer Loads</h3>
</div>
<div style='clear:both'></div>

<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1500px;margin:10px'>
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
	<th align='right' nowrap><b>Pays</b></th>
	<th align='right' nowrap><b>Credit</b></th>
</tr>
</thead>
<tbody>
<? 
	$counter = 0;
	$total_cost = 0;
	$total_bill = 0;
	$total_profit = 0;
	while($row = mysqli_fetch_array($data)) {
		if(($row['credit_hold'] > 0 && $row['override_credit_hold']==0) || ($row['slow_pays'] > 0 && $row['override_slow_pays']==0))
		{     		
     		$counter++;
     		   		
     		$profit = $row['actual_bill_customer'] - $row['actual_total_cost'];
     		
     		$cust_id=$row['customer_id'];
     		$slowpayer="";
     		$creditholder="";
     		$credit_hold=$row['credit_hold'];
     		$slow_pays=$row['slow_pays'];
     		if($credit_hold>0)		$creditholder="<span class='alert'>HOLD</span>";
     		if($slow_pays>0)		$slowpayer="<span class='alert'>SLOW</span>";
     				
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
     				and trucks_log.deleted = 0
     			order by trucks_log.linedate_pickup_eta
     		";
     		$data_dispatch = simple_query($sql);
     		$truck_array = array();
     		$driver_array = array();
     		while($row_dispatch = mysqli_fetch_array($data_dispatch)) {
     			$truck_array[] = " " . trim($row_dispatch['name_truck']);
     			$driver_array[] = " " . trim($row_dispatch['name_driver_last']);
     		}
     		
     		$total_cost += $row['actual_total_cost'];
     		$total_bill += $row['actual_bill_customer'];
     		$total_profit += $profit;
     		
     		$total_expense = $row['variable_expenses'] + $row['fuel_expense'];
     		
     		$sicap_invoice_text = "";
     		if($defaultsarray['sicap_integration'] == '1') {
     			$sicap_invoice_text = "
     					<div style='float:left;margin-left:5px'>
     						<b>
     						<div id='sicap_create_holder_$row[id]' style='".($row['sicap_invoice_number'] != '' ? ";display:none;" : "")."margin-left:5px;float:left' class='create_update_invoice_$row[id]'>
     							<a href='javascript:sicap_create_invoice($row[id])'><img src='images/new.png' style='border:0;width:16px' alt='Create invoice in the accounting system' title='Create invoice in the accounting system'></a>
     						</div>
     						<div style='float:left' id='sicap_invoice_holder_$row[id]'></div>
     						<div id='sicap_invoice_number_holder_$row[id]' style='cursor:pointer;color:blue;float:left' onclick='sicap_view_invoice_handler($row[id])'>$row[sicap_invoice_number]</div>
     						<div style='margin-left:10px;float:left;cursor:pointer;color:blue' class='create_update_invoice_$row[id]'>
     							<a href='javascript:sicap_add_to_invoice($row[id])'><img src='images/menu_departments16.png' style='border:0;width:16px' alt='Add to an existing invoice in the accounting system' title='Add to an existing invoice in the accounting system'></a>
     						</div>
     						<div id='sicap_invoice_number_holder_$row[id]' style='margin-left:10px;margin-right:10px;float:left;cursor:pointer;color:blue;display:none' class='delete_invoice_link_$row[id]'>
     							<a href='javascript:sicap_delete_invoice($row[id])'><img src='images/delete.gif' style='border:0;width:16px' alt='Add to an existing invoice in the accounting system' title='Add to an existing invoice in the accounting system'></a>
     						</div>
     						</b>
     						<div style='clear:both'></div>
     					</div>
     			";
     		} 
     		
     		echo "
     			<tr>
     				<td><a href='manage_load.php?load_id=$row[id]' target='edit_load_$row[id]'>$row[id]</a></td>
     				<td nowrap><a href='/admin_customers.php?eid=".$cust_id."' title=''>$row[name_company]</a></td>
     				<td nowrap>".date("m-d-Y", strtotime($row['linedate_pickup_eta']))."</td>
     				<td nowrap>$row[origin_city], $row[origin_state]</td>
     				<td nowrap>$row[dest_city], $row[dest_state]</td>			
     				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_total_cost'])."</td>
     				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_bill_customer'])."</td>
     				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; ".($profit <= 0 ? "<span class='alert'>" : "<span class=''>")."$".money_format('',$profit)."</span></td>
     				<td>".implode(",", $truck_array)."</td>
     				<td nowrap>".implode(",", $driver_array)."</td>
     				<td nowrap>".$slowpayer."</td>
     				<td nowrap>".$creditholder."</td>
     			</tr>
     		";
		}
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

	$link = print_contents('customer_payment_'.createuuid(), $pdf_2."<br><br>".$pdf);

	echo "
		<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
			<a href='$link'>Printed Version</a>
		</div>
		<div style='clear:both'></div>
		
	";

	echo $pdf;
	
?>
<? include('footer.php') ?>