<? include('header.php') ?>
<?
	if(isset($_GET['invoice_number'])) $_POST['report_invoice_number'] = $_GET['invoice_number'];

	if(isset($_POST['load_id_array'])) {
		foreach($_POST['load_id_array'] as $load_id) {
			$invoice_number = $_POST['invoice_number_'.$load_id];
			if($invoice_number != '') {
				$sql = "
					update load_handler
					set invoice_number = '".sql_friendly($invoice_number)."',
						linedate_invoiced = '".date("Y-m-d", strtotime($_POST['invoice_date_'.$load_id]))."'
						
					where id = '".sql_friendly($load_id)."'
				";
				simple_query($sql);
			}
		}
	}
	
	$skip_check=0;
	if(isset($_GET['customer_id'])) 	{	$_POST['customer_id'] = $_GET['customer_id'];		$skip_check=1;	}
	if(isset($_GET['date_from'])) 		$_POST['date_from'] = date("m/d/Y",strtotime($_GET['date_from']));
	if(isset($_GET['date_to'])) 			$_POST['date_to'] = date("m/d/Y",strtotime($_GET['date_to']));
    
    echo "<form action='' method='post'>";
    
	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_load_id 		    = true;
	$rfilter->show_dispatch_id 	    = true;
	$rfilter->show_invoice_number   = true;
	$rfilter->show_font_size		= true;
    $rfilter->mrr_no_form_enclosed  = true;
	$rfilter->show_filter();

    if(!isset($_POST['mrr_inv_lister']))	$_POST['mrr_inv_lister']="";
    
    $filter_invs="";
    if(trim($_POST['mrr_inv_lister'])!="")
    {
         $filter_invs=" and (";
         
         $_POST['mrr_inv_lister']=str_replace(";",",",$_POST['mrr_inv_lister']);
         $_POST['mrr_inv_lister']=str_replace(" ",",",$_POST['mrr_inv_lister']);
         $_POST['mrr_inv_lister']=str_replace(",,",",",$_POST['mrr_inv_lister']);
         $_POST['mrr_inv_lister']=str_replace(",,",",",$_POST['mrr_inv_lister']);
         $_POST['mrr_inv_lister']=str_replace(",,",",",$_POST['mrr_inv_lister']);
         $_POST['mrr_inv_lister']=str_replace(",,",",",$_POST['mrr_inv_lister']);
         
         $taskarr=explode(",",$_POST['mrr_inv_lister']);
         for($i=0; $i < count($taskarr); $i++)
         {
              if($i > 0)	$filter_invs.=" or ";
              $filter_invs.=" load_handler.invoice_number='".sql_friendly($taskarr[$i])."'";
         }
         
         $filter_invs.=" )";
    }

	$sql = "
		select load_handler.*,
			customers.name_company,
			customers.invoice_discount_percent,
			(select sum(actual_bill_customer + flat_fuel_rate_amount) from load_handler lh where lh.invoice_number = load_handler.invoice_number) as invoice_total,
			(select sum(expense_amount) from load_handler_actual_var_exp lhave where lhave.load_handler_id = load_handler.id) as variable_expenses,
			(select actual_fuel_surcharge_per_mile * (select sum(miles) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id)) as fuel_expense
		
		from load_handler
			left join customers on customers.id = load_handler.customer_id
		where load_handler.deleted = 0
			".($skip_check==0 ? "and (load_handler.invoice_number <> '' and invoice_number is not null)" : "")."
			".($filter_invs!="" ? "".$filter_invs."" : "") ."
			".($_POST['customer_id'] ? " and customers.id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
			".($_POST['report_invoice_number'] ? " and load_handler.invoice_number = '".sql_friendly($_POST['report_invoice_number'])."'" : '') ."
			".($_POST['report_invoice_number'] == "" ? " and load_handler.linedate_invoiced >= '".date("Y-m-d", strtotime($_POST['date_from']))."' " : "")."
			".($_POST['report_invoice_number'] == "" ? " and load_handler.linedate_invoiced < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."' " : "")."
		order by load_handler.invoice_number, load_handler.id
	";
	//d($sql);
	$data = simple_query($sql);
	
	ob_start();

	$invoice_number_array = array();
	while($row = mysqli_fetch_array($data)) 
	{
		if(!in_array($row['invoice_number'], $invoice_number_array) || $skip_check==1) $invoice_number_array[] = $row['invoice_number'];
	}
	if(mysqli_num_rows($data)) 	mysqli_data_seek($data, 0);
?>
    <div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
         <?
         //echo "<br>Query: ".$sql."<br>";
         ?>
        <h3>Find loads with these invoice numbers...</h3>
        <b>Multi-Invoice Filter: </b><input type='text' name='mrr_inv_lister' id='mrr_inv_lister' value="<?=$_POST['mrr_inv_lister'] ?>" size="195"> Use commas to separate Accounting Invoice ID numbers.
    </div>
    <div style='clear:both'></div>


<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>Invoice Report <?=(isset($data) ? " - " . count($invoice_number_array)." unique invoice numbers found - ".mysqli_num_rows($data)." total invoiced load(s) found" : "")?></h3>
</div>
<div style='clear:both'></div>
<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1300px;margin:10px'>
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
	<th align='right' nowrap><b>Discount%</b></th>
	<th align='right' nowrap><b>Discount$</b></th>
	<th align='right' nowrap><b>Invoice Number</b></th>
	<th align='right' nowrap><b>Invoice Date</b></th>
	<th align='right' nowrap><b>Invoice Total</b></th>
	<th align='right' nowrap><b>Invoice Paid</b></th>
</tr>
</thead>
<tbody>
<? 
	$counter = 0;
	$total_cost = 0;
	$total_bill = 0;
	$total_profit = 0;
	$total_invoice_amount = 0;
	
	$tot_discount_amnt=0;
	
	$invoice_array = array();
	while($row = mysqli_fetch_array($data)) 
	{
		$counter++;
		if($counter > 3000) break; // infinite loop check
		
		$flat_rate_fuel_charge=$row['flat_fuel_rate_amount'];
		
		$cust_discount_amnt=0;
		$cust_discount_rate=$row['invoice_discount_percent'];
		
		$profit = $row['actual_bill_customer'] + $flat_rate_fuel_charge - $row['actual_total_cost'];
		
		// get the trucks and drivers for this load
		/*
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
		*/
		
		
		// make sure we only count the invoice once
		if(!in_array($row['invoice_number'], $invoice_array)) 
		{
			$invoice_array[] = $row['invoice_number'];
			$total_invoice_amount += $row['invoice_total'];
		}
		
		$total_cost += $row['actual_total_cost'];
		$total_bill += $row['actual_bill_customer'] + $flat_rate_fuel_charge;
		$total_profit += $profit;
		
		$total_expense = $row['variable_expenses'] + $row['fuel_expense'];
		
		$paid_mark="";
		$invoice_id=(int) $row['invoice_number'];
		if($invoice_id > 0)
		{
			$mark="";
			$days=0;
			$pd_amnt=0;
			
			$results=mrr_get_cust_has_paid($invoice_id);
			foreach($results as $key => $value )
			{
				$prt=trim($key);			$tmp=trim($value);
    				if($prt=="paidAge")			$days=$tmp; 
    				//if($prt=="paidAlt")		$days_alt=$tmp; 
    				if($prt=="paidAmnt")		$pd_amnt=$tmp;
			}
			if($pd_amnt >= $row['invoice_total'])	$paid_mark="".$days." Days";
			else $paid_mark="$".number_format(($row['invoice_total'] - $pd_amnt),2)." Due";
		}
		
		$cust_discount_amnt=($row['actual_bill_customer'] * $cust_discount_rate);
		
		$tot_discount_amnt+= $cust_discount_amnt;
		
		echo "
			<tr>
				<td><a href='manage_load.php?load_id=$row[id]' target='edit_load_$row[id]'>$row[id]</a></td>
				<td nowrap>$row[name_company]</td>
				<td nowrap>".date("m-d-Y", strtotime($row['linedate_pickup_eta']))."</td>
				<td nowrap>$row[origin_city], $row[origin_state]</td>
				<td nowrap>$row[dest_city], $row[dest_state]</td>			
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_total_cost'])."</td>
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_bill_customer'] + $flat_rate_fuel_charge)."</td>
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; ".($profit <= 0 ? "<span class='alert'>" : "<span class=''>")."$".money_format('',$profit)."</span></td>
				<td nowrap align='right'>".number_format($cust_discount_rate,2)."%</td>
				<td nowrap align='right'>$".number_format($cust_discount_amnt,2)."</td>				
				<td nowrap align='right'>
					<span class='mrr_link_like_on' onClick='mrr_clear_load_from_invoice($row[id]);' title='Remove this load from the invoice...to move on another or repair' on>Clear</span>&nbsp;&nbsp;
					".($total_expense != $row['actual_bill_customer'] ? "<span class='alert'>(price error)</span>" : "")."
					<span id='mrr_invoice_num_$row[id]'>$row[invoice_number]</span>
				</td>
				<td nowrap>".date("m-d-Y", strtotime($row['linedate_invoiced']))."</td>
				<td nowrap align='right'>$".money_format('',$row['invoice_total'])."</td>
				<td nowrap align='right'>".$paid_mark."</td>
			</tr>
		";
	}
?>
</tbody>
<tr>
	<td style='background-color:#bed3ec' colspan='5'><b>Total</b>&nbsp;</td>
	<td style='background-color:#bed3ec' align='right'><b>$<?=money_format('',$total_cost)?></b></td>
	<td style='background-color:#bed3ec' align='right'><b>$<?=money_format('',$total_bill)?></b></td>
	<td style='background-color:#bed3ec' align='right'><b>$<?=money_format('',$total_profit)?></b></td>
	<td style='background-color:#bed3ec'>&nbsp;</td>
	<td style='background-color:#bed3ec' align='right'><b>-$<?=number_format($tot_discount_amnt,2)?></b></td>	
	<td style='background-color:#bed3ec' colspan='2'><b>= Invoiced $<?=number_format(($total_bill - $tot_discount_amnt),2)?></b></td>
	<td style='background-color:#bed3ec' align='right'><b>$<?=money_format('',$total_invoice_amount)?></b></td>
	<td style='background-color:#bed3ec'>&nbsp;</td>
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
	function mrr_clear_load_from_invoice(id)
	{
		inv_num=parseInt($('#mrr_invoice_num_'+id+'').html());
		
		$.prompt("Are you sure you want to detach Load '"+id+"' from Invoice '"+inv_num+"'?", {
			buttons: {Yes: true, No: false},
			submit: function (v, m, f) {
				if(v) {
					$.ajax({
               			   type: 'POST',
               			   url: 'ajax.php?cmd=mrr_sicap_clear_from_invoice',
               			   dataType:"xml",
               			   async: false,
               			   data: {
               			   	"load_id":id,
               			   	"invoice_id": inv_num
               			   	},
               			   	success: function(xml) {
               					 //window.location.reload();
               					 $('#mrr_invoice_num_'+id+'').html('');	
               			   	}
               		});
				}
			}
		});
	}
</script>
<?
	$link = print_contents('invoiced_'.createuuid(), $pdf);

	echo "
		<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
			<a href='$link'>Printed Version</a>
		</div>
		<div style='clear:both'></div>
		
	";

	echo $pdf;
	
?>
<? include('footer.php') ?>