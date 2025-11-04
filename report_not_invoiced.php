<?php include('header.php') ?>
<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
	if(isset($_POST['load_id_array'])) 
	{
		foreach($_POST['load_id_array'] as $load_id) 
		{
			$invoice_number = trim($_POST['invoice_number_'.$load_id]);
			if($invoice_number != '') 
			{
				$sql = "
					update load_handler set
						invoice_number = '".sql_friendly($invoice_number)."',
						sicap_invoice_number = '".sql_friendly($invoice_number)."',
						linedate_invoiced = '".date("Y-m-d", strtotime($_POST['invoice_date_'.$load_id]))."'
						
					where id = '".sql_friendly($load_id)."'
				";
				simple_query($sql);
			}
		}
	}
	
	echo "<form action='' method='post'>";
	
	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_load_id 		    = true;
	$rfilter->show_dispatch_id 	    = true;
	$rfilter->show_font_size		= true;
	$rfilter->mrr_no_form_enclosed  = true;
	$rfilter->show_filter();
	
	if(!isset($_POST['mrr_load_lister']))	$_POST['mrr_load_lister']="";
	
	$filter_loads="";
	if(trim($_POST['mrr_load_lister'])!="")
	{
		$filter_loads=" and (";
		
		$_POST['mrr_load_lister']=str_replace(";",",",$_POST['mrr_load_lister']);
		$_POST['mrr_load_lister']=str_replace(" ",",",$_POST['mrr_load_lister']);
		$_POST['mrr_load_lister']=str_replace(",,",",",$_POST['mrr_load_lister']);
		$_POST['mrr_load_lister']=str_replace(",,",",",$_POST['mrr_load_lister']);
		$_POST['mrr_load_lister']=str_replace(",,",",",$_POST['mrr_load_lister']);
		$_POST['mrr_load_lister']=str_replace(",,",",",$_POST['mrr_load_lister']);
		
		$taskarr=explode(",",$_POST['mrr_load_lister']);
     	for($i=0; $i < count($taskarr); $i++)
     	{
     		if($i > 0)	$filter_loads.=" or ";
     		$filter_loads.=" load_handler.id='".sql_friendly($taskarr[$i])."'";	   		
     	}	
     	
     	$filter_loads.=" )";	
	}
	
	$sql = "
		select load_handler.*,
			customers.name_company,
			customers.invoice_discount_percent,
			customers.flat_fuel_surchage_override,
			(select sum(expense_amount) from load_handler_actual_var_exp lhave where lhave.load_handler_id = load_handler.id) as variable_expenses,
			(select actual_fuel_surcharge_per_mile * (select sum(miles) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id)) as fuel_expense,
			(select actual_fuel_surcharge_per_mile * (select sum(loaded_miles_hourly) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id)) as fuel_expense_hourly
		
		from load_handler
			left join customers on customers.id = load_handler.customer_id
		where load_handler.deleted = 0
			and load_handler.preplan = 0
			
			and (load_handler.invoice_number = '' or load_handler.invoice_number = '0' or invoice_number is null)
			and (load_handler.sicap_invoice_number = '' or load_handler.sicap_invoice_number = '0' or sicap_invoice_number is null)
			
			".($_POST['customer_id'] ? " and customers.id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
			".($filter_loads!="" ? "".$filter_loads."" : "") ."
			and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
			and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
		order by load_handler.linedate_pickup_eta, load_handler.id
	";		//and linedate_dropoff_eta < now()
	$data = simple_query($sql);	
	ob_start();	
?>
<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>Loads missing invoice numbers</h3>
	<b>Multi-Load Filter: </b><input type='text' name='mrr_load_lister' id='mrr_load_lister' value="<?=$_POST['mrr_load_lister'] ?>" size="195"> Use commas to separate Load ID numbers.
</div>
<div style='clear:both'></div>
<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left; width:95%; margin:10px;'>
<thead>
<tr>
	<th nowrap><b>Load ID</b></th>		
	<th><b>Customer</b></th>
	<th><b>Date</b></th>
	<th><b>Origin</b></th>
	<th><b>Destination</b></th>
    <th align='right'><b>Bill</b></th>
    <th align='right'><b>Detention</b></th>    
       
	<th><b>Truck(s)</b></th>	
	<th><b>Driver(s)</b></th>		
	<th nowrap><b>LoadID</b></th>
	<th align='right' nowrap><b>Invoice Number &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></th>
	<th align='right' nowrap><b>Invoice Date</b></th>	
	<th>&nbsp;</th>
</tr>
</thead>
<tbody>
<?php
	$counter = 0;
	$total_cost = 0;
	$total_bill = 0;
    $total_detention=0;
	$total_profit = 0;
	$tot_discount_amnt=0;
	
	while($row = mysqli_fetch_array($data)) 
	{
		$counter++;
		
		$cust_discount_amnt=0;
		$cust_discount_rate=$row['invoice_discount_percent'];
		
		$block_invoicing=$row['load_detention'];
		
		$mrr_use_fuel=$row['fuel_expense'];
		
		$alt_fuel=0;
		if($row['flat_fuel_surchage_override'] > 0)		$alt_fuel=$row['flat_fuel_rate_amount'];		//$alt_fuel=$row['actual_rate_fuel_surcharge'];
		
		//get fuel charge if miles hourly entered instead of normal mileage.		
		$mrr_hourly_fuel=0;
		if($mrr_use_fuel==0 && $row['fuel_expense_hourly'] > 0 && $row['flat_fuel_surchage_override']==0)
		{	
			$mrr_use_fuel=$row['fuel_expense_hourly'];
			$mrr_hourly_fuel=$row['fuel_expense_hourly'];
		}		
		
		// disabled the alt_fuel and mrr_hourly_fuel - CS - 5/27/2015. 
		// patti called and said the 'bill amount' wasn't matching the manage load or print load screen
		// taking these two lines out fixed it.
		/*
		$row['actual_bill_customer']+=$alt_fuel;
		$row['actual_bill_customer']+=$mrr_hourly_fuel;
		*/
		
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
				and trucks_log.deleted = 0
			order by trucks_log.linedate_pickup_eta
		";
		$data_dispatch = simple_query($sql);
		$truck_array = array();
		$driver_array = array();
		$replacement_list = array();
		
		while($row_dispatch = mysqli_fetch_array($data_dispatch)) 
		{
			$truck_array[] = " " . trim($row_dispatch['name_truck']);
			$driver_array[] = " " . trim($row_dispatch['name_driver_last']);	
			//$replacement_list[] =mrr_find_replacement_truck_name($row_dispatch['truck_id'],$_POST['date_from'],$_POST['date_to']);		
		}
		
		
		$total_cost += $row['actual_total_cost'];
		$total_bill += $row['actual_bill_customer'] ;
		$total_profit += $profit;
		
		
		$total_expense = $row['variable_expenses'] + $mrr_use_fuel;
		
		$sicap_invoice_text = "";
		if($defaultsarray['sicap_integration'] == '1') 
		{
			if($block_invoicing > 0)
			{
				$sicap_invoice_text = "
     					<div style='float:left;margin-left:5px' onClick='mrr_show_detention_notes(".$row['id'].");'>
     						<b>Detention Needed</b>
     						<div style='clear:both'></div>
     					</div>
     			";
			}
			else
			{
     			$sicap_invoice_text = "
     					<div style='float:left;margin-left:5px'>
     						<b>
     						<div id='sicap_create_holder_$row[id]' style='".($row['sicap_invoice_number'] != '' && $row['sicap_invoice_number'] != '0'? ";display:none;" : "")."margin-left:5px;float:left' class='create_update_invoice_$row[id]'>
     							<a href='javascript:sicap_create_invoice($row[id])'><img src='images/new.png' style='border:0;width:16px' alt='Create invoice in the accounting system' title='Create invoice in the accounting system'></a>
     						</div>
     						<div style='float:left' id='sicap_invoice_holder_$row[id]'></div>
     						<div id='sicap_invoice_number_holder_$row[id]' style='cursor:pointer;color:blue;float:right; margin-left:10px;' onclick='sicap_view_invoice_handler($row[id])'>$row[sicap_invoice_number]</div>
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
		} 
		$cust_discount_amnt=($row['actual_bill_customer'] * $cust_discount_rate);
		
		$tot_discount_amnt+= $cust_discount_amnt;
         
		$total_detention+=$row['misc_detention'];
		
		echo "
			<tr>
				<td class='mrr_load_$row[id]'><a href='manage_load.php?load_id=$row[id]' target='edit_load_$row[id]' onMouseover='mrr_hover_highlight($row[id]);' onMouseout='mrr_hover_nohighlight($row[id]);'>$row[id]</a></td>				
				<td class='mrr_load_$row[id]' nowrap>$row[name_company]</td>
				<td class='mrr_load_$row[id]' nowrap>".date("m-d-Y", strtotime($row['linedate_pickup_eta']))."</td>
				<td class='mrr_load_$row[id]' nowrap>$row[origin_city], $row[origin_state]</td>
				<td class='mrr_load_$row[id]' nowrap>$row[dest_city], $row[dest_state]</td>		
				<td class='mrr_load_$row[id]' nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_bill_customer'])."</td>	
				
				<td class='mrr_load_$row[id]' nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['misc_detention'])."</td>
				
				<td class='mrr_load_$row[id]' nowrap>".implode(",", $truck_array)."</td>				
				<td class='mrr_load_$row[id]' nowrap>".implode(",", $driver_array)."</td>
				
				<td class='mrr_load_$row[id]'><a href='manage_load.php?load_id=$row[id]' target='edit_load_$row[id]' onMouseover='mrr_hover_highlight($row[id]);' onMouseout='mrr_hover_nohighlight($row[id]);'>$row[id]</a></td>
				<td class='mrr_load_$row[id]' nowrap align='right'>
					<div style='float:left'>
						".($block_invoicing == 0 ? "<input name='invoice_number_$row[id]' id='invoice_number_$row[id]' size='10'>" : "")."
						".($block_invoicing == 0 ? "<input type='hidden' name='load_id_array[]' value='$row[id]'>" : "")."					
					</div>
					$sicap_invoice_text
				</td>				
				<td class='mrr_load_$row[id]'><input name='invoice_date_$row[id]' id='invoice_date_$row[id]' size='10' value='".date("m/d/Y")."' class='datepicker'></td>		
				<td class='mrr_load_$row[id]'>
					".(round($total_expense,2) != round($row['actual_bill_customer'],2) ? "<span class='alert'>(price error $". money_format('',$total_expense) .")</span>" : "")."
					".($counter % 5 == 1 ? "<input type='submit' value='Update'>" : "")."
				</td>	
			</tr>
		";
		/*				
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; $".money_format('',$row['actual_total_cost'])."</td>
				<td nowrap align='right'>&nbsp;&nbsp;&nbsp;&nbsp; ".($profit <= 0 ? "<span class='alert'>" : "<span class=''>")."$".money_format('',$profit)."</span></td>
		
				<td nowrap align='right'>".number_format($cust_discount_rate,2)."%</td>
				<td nowrap align='right'>$".number_format($cust_discount_amnt,2)."</td>	
		*/
		//<td>".implode(",", $replacement_list)."</td>
	}
?>
</tbody>
<tr>
	<td style='background-color:#bed3ec'><b><?=$counter ?></b>&nbsp;</td>
	<td style='background-color:#bed3ec' colspan='4'><b>Total</b>&nbsp;</td>    
    <td style='background-color:#bed3ec' align='right'><b>$<?=money_format('',$total_bill)?></b></td>
    <td style='background-color:#bed3ec' align='right'><b>$<?=money_format('',$total_detention)?></b></td>	
	<td style='background-color:#bed3ec' colspan='6'>&nbsp;</td>
</tr>
</table>
</form>

<?php
	$pdf = ob_get_contents();
	ob_end_clean();
?>
<script type='text/javascript'>
	
	function sicap_view_invoice_handler(load_id) {
		sicap_view_invoice($('#sicap_invoice_number_holder_'+load_id).html());
	}
	
	function mrr_hover_highlight(id)
	{
		$('.mrr_load_'+id+'').css("background-color","yellow");	
	}
	function mrr_hover_nohighlight(id)
	{
		$('.mrr_load_'+id+'').css("background-color","white");	
	}
	
	function mrr_show_detention_notes(load_id) 
    {
        $.ajax({
            type: "POST",
            url: "ajax.php?cmd=mrr_show_detention_notes",
            data: {"load_id":load_id},
            dataType: "xml",
            cache:false,
            success: function(xml) {
                //$('#detention_note').val($(xml).find('mrrLast').text());
                //$('#show_detention_notes').html($(xml).find('mrrTab').text());

                $.prompt("<h2>Detention Notes for Load "+load_id+"</h2><br>"+$(xml).find('mrrTab').text()+"");
            }
        });
    }
	
	$().ready(function() {
		$('.tablesorter').tablesorter({
		   			headers: {         				
		   				5: {sorter:'currency'},
		   				9: {sorter: false},
		   				10: {sorter: false},
		   				11: {sorter: false}
		   			}
		   				
		});
		/*
						5: {sorter:'currency'},
		   				6: {sorter:'currency'},
		   				7: {sorter:'currency'},
		   				11: {sorter: false}
		*/
		
		$('.datepicker').datepicker();
	});
</script>
<?php

	$link = print_contents('not_invoiced_'.createuuid(), $pdf);

	echo "
		<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
			<a href='$link'>Printed Version</a>
		</div>
		<div style='clear:both'></div>
		
	";

	echo $pdf;
	
?>
<?php include('footer.php'); ?>