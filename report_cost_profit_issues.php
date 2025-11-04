<? $usetitle="Report - Cost and Profit Issues"; ?>
<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['date_from']))	
	{
		$_GET['date_from']=str_replace("_","/",$_GET['date_from']);	
		$_POST['date_from']=$_GET['date_from'];
		
		$_POST['build_report']=1;
	}
	if(isset($_GET['date_to']))		
	{
		$_GET['date_to']=str_replace("_","/",$_GET['date_to']);
		$_POST['date_to']=$_GET['date_to'];
				
		$_POST['build_report']=1;
	}
	
	if(!isset($_POST['mrr_run_api']))		$_POST['mrr_run_api']=0;
	$run_invoice_api=$_POST['mrr_run_api'];

	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_customer_grp	= true;
	$rfilter->show_driver 		= true;
	$rfilter->show_truck 		= true;
	//$rfilter->show_trailer 		= true;
	$rfilter->show_load_id 		= true;
	$rfilter->show_only_invoiced	= true;
	$rfilter->show_font_size		= true;
	$rfilter->mrr_special_run_api = true;
	$rfilter->show_filter();

	

 	if(isset($_POST['build_report'])) { 
	
		$search_date_range = '';
		if((isset($_POST['dispatch_id']) && $_POST['dispatch_id'] != '') || $_POST['load_handler_id'] != '') {
		} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
			";
		}
		
		$driver_search = "";
		if($_POST['driver_id'] > 0) {
			$driver_search = "
				and load_handler.id in 
						(
							select load_handler_id 
							
							from trucks_log 
							where (driver_id = '".sql_friendly($_POST['driver_id'])."' or driver2_id = '".sql_friendly($_POST['driver_id'])."')
								and trucks_log.deleted = 0
						)
			";
		}
		$truck_search = "";
		if($_POST['truck_id'] > 0) {
			$truck_search = "
				and load_handler.id in 
						(
							select load_handler_id 
							
							from trucks_log 
							where truck_id = '".sql_friendly($_POST['truck_id'])."' and trucks_log.deleted = 0
						)
			";
		}
	
		$sql = "
			select load_handler.*,
				customers.name_company,
				load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount - load_handler.actual_total_cost as load_profit,
				(select ifnull(sum(trucks_log.cost),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as mrr_cost,
				(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
				(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead
			
			from load_handler
				left join customers on customers.id = load_handler.customer_id
			where load_handler.deleted = 0
				and customers.deleted = 0
				
				$search_date_range
				
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['customer_id'] > 0 ? " and load_handler.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				
				".($_POST['customer_id'] < 0 ? " and customers.name_company like 'Bridgestone%'" : '') ."				
				
				".(isset($_POST['show_only_invoiced']) ? " and load_handler.invoice_number != '' " : '') ."
				$driver_search
				$truck_search
			order by load_handler.id
		";	//load_handler.linedate_pickup_eta asc,		
		$data = simple_query($sql);
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:950px;text-align:left'>
	<tr>
		<td colspan='14'>
			<center>
			<span class='section_heading'><?=$usetitle ?></span>
			</center>
		</td>
	</tr>
	<? 
		$header_column = "
			<tr style='font-weight:bold'>
				<td nowrap>Load ID</td>
				<td>Invoice</td>
				<td>Origin</td>
				<td>Destination</td>
				<td align='right'>Miles</td>
				<td align='right'>Deadhead</td>
				<td>Date</td>
				<td>Customer</td>
				<td align='right' nowrap><span title='Bill Customer'>Sales</span></td>
				<td align='right'><span class='color:green' title='Date of invoice.'>InvDate</span></td>
				<td align='right'><span class='color:green' title='Show actual invoice total, not the Bill Customer value.'>InvAmnt</span></td>
				<td align='right'><span class='color:purple' title='Invoice Difference between Sales and Accounting'>InvDiff</span></td>
				<td align='right' style='padding-right:10px'>Cost</td>
				<td align='right'>Profit</td>		
				<td align='right'>&nbsp;</td>								
			</tr>
		";	
		$mrr_inv_amnt_tot=0;
	
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_profit = 0;
		$total_cost = 0;
		$total_extra = 0;
		$total_sales = 0;
		$last_load_id = 0;
		$last_truck_id = 0;
		$not_invoiced = 0;
		$invoiced = 0;
		$invoiced_amount = 0;
		$not_invoiced_amount = 0;
		$fuel_charge = 0;
		
		$mrr_new_cost=0;
		$mrr_new_profit=0;
		
		$mrr_inv_diff_tot=0;
		
		while($row = mysqli_fetch_array($data)) 
		{
			if(number_format($row['mrr_cost'],2) != number_format($row['actual_total_cost'],2) || $row['auto_save_requested'] > 0)
			{		
     			
     			$counter++;
     			
     			//$total_miles += $row['miles'];
     			//$total_deadhead += $row['miles_deadhead'];
     			
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
     			*/
     			
     			$tot_coster=$row['mrr_cost'];
     			
     			$flat_rate_fuel_charge=$row['flat_fuel_rate_amount'];
     
     			if($counter % 20 == 1) 		echo $header_column;
     			
     			if($last_load_id != $row['id']) 
     			{
     				$last_load_id = $row['id'];
     				
     				$load_miles = $row['miles'];
     				$load_miles_deadhead = $row['miles_deadhead'];
     				$total_miles += $load_miles;
     				$total_deadhead += $load_miles_deadhead;				
     				
     				//if($row['budget_average_mpg'] > 0)
     				//{
     				//	$fuel_charge += ($row['miles'] + $row['miles_deadhead']) * $row['actual_rate_fuel_surcharge'] / $row['budget_average_mpg'];	
     				//}
     				//else
     				//{
     					$fuel_charge += ($row['miles'] + $row['miles_deadhead']) * $row['actual_rate_fuel_surcharge'] / $defaultsarray['average_mpg'];	
     				//}				
     				//echo $fuel_charge."<br>";
     				
     				
     				
     				//evaluate actually invoiced.................................................................................
     				$mrr_inv_amnt=0;
     				$mrr_inv_hider="";	
     				$mrr_invoicer=(int) $row['invoice_number'];
     				$mrr_classy="green";
     				
     				$mrr_inv_amnt=$row['sicap_invoice_amount'];
     				
     				if($run_invoice_api > 0)
     				{					
          				$mrr_finder=mrr_get_load_invoice_info($mrr_invoicer, $row['id']);
          				foreach($mrr_finder as $key => $value )
          				{
          					$prt=trim($key);		$tmp=trim($value);
          					if($prt=="Total")		$mrr_inv_amnt=$tmp;
          					if($prt=="HTML")		$mrr_inv_hider=$tmp;	
          				}
          				$mrr_inv_amnt_tot+=$mrr_inv_amnt;				
          				
          				if( number_format($mrr_inv_amnt,2) != number_format(($row['actual_bill_customer'] + $flat_rate_fuel_charge),2)  )	$mrr_classy="red";	
          				
          				//update the invoice amount on each load for later display			
          				$sqlu = "update load_handler set sicap_invoice_amount='".sql_friendly($mrr_inv_amnt)."' where id='".sql_friendly($row['id'])."'";	
     					simple_query($sqlu);     				
     				}
     				//...........................................................................................................
     				
     				$sales_differ=($row['actual_bill_customer'] + $flat_rate_fuel_charge) - $mrr_inv_amnt;
     				
     				$mrr_inv_diff_tot+=$sales_differ;
     				
     				$inv_date="&nbsp;";
     				if($row['linedate_invoiced']>='2010-01-01 00:00:00')		$inv_date="".date("M j, Y", strtotime($row['linedate_invoiced']))."";
     				
     				$repair_link="<a href='manage_load.php?load_id=".$row['id']."&auto_save_trigger=1' target='_blank'>Repair</a>";
     				
     				echo "
     					<tr class='odd' style='color:".($row['auto_save_requested'] > 0  ? "purple" : "#000000").";'>
     						<td><a href='manage_load.php?load_id=".$row['id']."' target='view_load_".$row['id']."'>".$row['id']."</a></td>
     						<td nowrap>$row[invoice_number]</td>
     						<td nowrap>$row[origin_city], $row[origin_state]</td>
     						<td nowrap>$row[dest_city], $row[dest_state]</td>
     						<td align='right'>".number_format($load_miles)."</td>
     						<td align='right'>".number_format($load_miles_deadhead)."</td>
     						<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
     						<td nowrap>$row[name_company]</td>
     						<td align='right'>$".money_format('',$row['actual_bill_customer'] + $flat_rate_fuel_charge)."</td>
     						<td nowrap align='right'><span style='color:".$mrr_classy.";' onClick='mrr_pop_invoice_summary(".$row['id'] .");'>".$inv_date."</span></td>
     						<td align='right'><span style='color:".$mrr_classy.";' onClick='mrr_pop_invoice_summary(".$row['id'] .");'>$".money_format('',$mrr_inv_amnt)."</span>".$mrr_inv_hider."</td>
     						<td align='right'><span style='color:purple;' onClick='mrr_pop_invoice_summary(".$row['id'] .");'>$".money_format('',$sales_differ)."</span></td>
     						<td align='right'><span style='color:#".($tot_coster > $row['actual_total_cost'] ? "CC0000" : "000000").";'>$".money_format('',$row['actual_total_cost'])."</span></td>
     						<td align='right'>".($row['load_profit'] <= 0 ? "<span style='color:red'>" : "<span>")."$".money_format('',$row['load_profit'])."</span></td>		
     						<td align='right'>".$repair_link."</td>										
     					</tr>
     				";
     				/*
     						<td align='right'>$".money_format('',$mrr_new_cost)."</td>
     						<td align='right'>".($mrr_new_profit <= 0 ? "<span style='color:red'>" : "<span>")."$".money_format('',$mrr_new_profit)."</span></td>
     				*/
     				if($row['invoice_number'] == '') 
     				{
     					$not_invoiced++;
     					$not_invoiced_amount += $row['actual_bill_customer'] + $flat_rate_fuel_charge;
     				} 
     				else 
     				{
     					$invoiced++;
     					$invoiced_amount += $row['actual_bill_customer'] + $flat_rate_fuel_charge;
     				}
     				
     				//show dispatch breakdown for this load.
     				if($row['id'] > 0)
     				{				
          				$ld_cost_tot=$row['actual_total_cost'];
          				
          				if($tot_coster > $ld_cost_tot)		$ld_cost_tot=$tot_coster;	//cost of multiple dispatches is more than what the load has...hourly driver payroll update probably not included...
          				
          				$ld_profit_tot=$row['load_profit'];
          				
          				$sql2="
          					select * 
          					from trucks_log
          					where deleted='0' and load_handler_id='".sql_friendly($row['id'])."'
          					order by linedate_pickup_eta asc, id asc
          				";
          				$data2 = simple_query($sql2);
          				while($row2 = mysqli_fetch_array($data2)) 
          				{
          					$variable_expenses_total=0;
                    			$sql = "
                         			select *          			
                         			from dispatch_expenses
                         			where dispatch_id = '".sql_friendly($row2['id'])."'
                         				and deleted = 0
                         		";
                         		$data_expenses = simple_query($sql);              		
                         		while($row_expense = mysqli_fetch_array($data_expenses)) 
                         		{
                         			$variable_expenses_total += $row_expense['expense_amount'];
                         		}
                         		
                         		//$tot_coster+=$variable_expenses_total;
                         		//$ld_cost_tot+=$variable_expenses_total;
                         		
          					
          					$ld_disp_per=0;
          					if($ld_cost_tot > 0)		$ld_disp_per=(($row2['cost']) / $ld_cost_tot);		//+$variable_expenses_total
          					     					
          					if($tot_coster > $row['actual_total_cost'])
          					{
          						if($tot_coster > 0)		$ld_disp_per=(($row2['cost']) / $tot_coster);		//+$variable_expenses_total	
          						
          						$diff_coster=$tot_coster - $ld_cost_tot;
          						
          						$div_profit=($ld_profit_tot - $diff_coster) * $ld_disp_per;					// - $variable_expenses_total
          					}
          					else
          					{
          						$div_profit=($ld_profit_tot) * $ld_disp_per;								// - $variable_expenses_total
          					}
          					
          					//$div_profit-=$variable_expenses_total;
          					     					
          					//+$variable_expenses_total     					
          					echo "
               					<tr class='even'>
               						<td>&nbsp;</td>
               						<td nowrap><a href='add_entry_truck.php?id=$row2[id]' target='view_disp_$row2[id]'>$row2[id]</a></td>
               						<td nowrap>$row2[origin], $row2[origin_state]</td>
               						<td nowrap>$row2[destination], $row2[destination_state]</td>
               						<td align='right'>".($row2['miles'] + $row2['miles_deadhead'])."</td>
               						<td align='right'>".number_format($row2['hours_worked'], 2)."</td>
               						<td nowrap>".date("M j, Y", strtotime($row2['linedate_pickup_eta']))."</td>
               						<td align='right'><span style='color:#".($tot_coster > $row['actual_total_cost'] ? "CC0000" : "000000").";'>Cost</span></td>
               						<td align='right'><span style='color:#".($tot_coster > $row['actual_total_cost'] ? "CC0000" : "000000").";'>$".money_format('',($row2['cost']))."</span></td>
               						<td align='right'><span style='color:#".($tot_coster > $row['actual_total_cost'] ? "CC0000" : "000000").";'><b>[".number_format(($ld_disp_per*100), 2)."%]</b></span></td> 
               						<td align='right'>Profit</td>	         						
               						<td align='right'><i>$".money_format('',$row2['profit'])."</i></td>
               						<td align='right'>$".money_format('',$div_profit)."</td>
               						<td align='right'>&nbsp;</td>          																	
               					</tr>
               				";	          				
          				}				
     				}
     			}
     						
     			$total_profit += $row['actual_bill_customer'] + $flat_rate_fuel_charge - $row['actual_total_cost'];
     			$total_cost += $row['actual_total_cost'];
     			$total_sales += $row['actual_bill_customer'] + $flat_rate_fuel_charge;
     		}//end if
		}//end while loop		
		
		
		$not_invoiced2 = 0;
		$invoiced2 = 0;
		$invoiced_amount2 = 0;
		$not_invoiced_amount2 = 0;
		
		
		$days_run = get_days_available(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		$days_actual = get_days_run(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		$days_actual2 = get_days_run_v2(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		$days_actual3 = get_days_run_v3(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		
		
		$days_variance = $days_actual - $days_run['days_available_so_far'];
		
		$daily_cost = get_daily_cost();
		$usage_difference = $daily_cost * $days_variance;
		
		$gross_profit = $total_profit + $usage_difference;
		
		$gallons_used = ($total_miles + $total_deadhead) / $defaultsarray['average_mpg'];
		$cost_per_gallon = $fuel_charge / $gallons_used;
	?>
	<tr>
		<td colspan='14'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='3'></td>
		
		<td align='right' nowrap><b>Miles</b></td>
		<td align='right' nowrap><b>Deadhead</b></td>
		<td align='right' nowrap><b>Total Miles</b></td>
		
		<td align='right' nowrap><b>Total Extra</b></td>
		<td align='right' nowrap><b>Total Sales</b></td>
		<td align='right' nowrap><b>&nbsp;</b></td>
		<td align='right' nowrap><b>Total InvAmnt</b></td>
		<td align='right' nowrap><b>Total InvDiff</b></td>		
		<td align='right' nowrap><b>Total Cost</b></td>
		<td align='right' nowrap><b>Total Profit</b></td>
	</tr>
	<tr>
		<td></td>
		<td colspan='3'><?=number_format($counter)?> Load(s)</td>
		
		<td align='right'><?=number_format($total_miles)?></td>
		<td align='right'><?=number_format($total_deadhead)?></td>
		<td align='right'><?=number_format($total_miles + $total_deadhead)?></td>
		<td></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_sales)?></td>
		<td align='right' nowrap><b>&nbsp;</b></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$mrr_inv_amnt_tot)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$mrr_inv_diff_tot)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_cost)?></td>
		<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $<?=money_format('',$total_profit)?></td>
	</tr>
	<tr>
		<td colspan='10' align='right'>Days Available</td>
		<td align='right'><?=$days_run['days_available_so_far']?></td>
	</tr>
	<tr>
		<td colspan='10' align='right'>Days Run</td>
		<td align='right'>Total: <?=number_format($days_actual,2)?><br>OTR: <?=number_format($days_actual2,2)?><br>Hourly:<?=number_format($days_actual3,2)?></td>
	</tr>
	<tr>
		<td colspan='10' align='right'>Days Variance</td>
		<td align='right'><?=($days_variance < 0 ? "<span class='alert'>" : "")?><?=number_format($days_variance,2)?></td>
		<td>@ $<?=money_format('',$daily_cost)?></td>
		<td align='right'><?=($usage_difference < 0 ? "<span class='alert'>" : "")?>$<?=money_format('', $usage_difference)?></td>
	</tr>
	<tr>
		<td colspan='14'>
			<hr>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td colspan='12' align='right'>Estimated Gross Profit</td>
		<td align='right'><?=($gross_profit < 0 ? "<span class='alert'>" : "")?>$<?=money_format('', $gross_profit)?></span></td>
	</tr>
	<tr style='font-weight:bold'>
		<td colspan='12' align='right'>Bill - InvAmnt</td>
		<td align='right'>$<?= money_format('',($total_sales - $mrr_inv_amnt_tot) )?></td>
	</tr>
	</table>
	
	<table class='admin_menu3' style='width:600px;margin:10px 0 20px 10px'>
	<tr>
		<td>&nbsp;</td>
		<td align='right'>Loads</td>
		<td align='right'>Load Amnt</td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td>Invoiced</td>
		<td align='right'><?= $invoiced ?></td>
		<td align='right'>$<?=money_format('',$invoiced_amount)?></td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td>Not Invoiced</td>
		<td align='right'><?=$not_invoiced?></td>
		<td align='right'>$<?=money_format('',$not_invoiced_amount)?></td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='5'><hr></td>
	</tr>
	<tr>
		<td>Total</td>
		<td align='right'><?=($not_invoiced + $invoiced)?></td>
		<td align='right'>$<?=money_format('',($not_invoiced_amount + $invoiced_amount))?></td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='5'><hr></td>
	</tr>
	<tr>
		<td colspan='5'><b>Stats</b></td>
	</tr>
	<tr>
		<td>Estimated Gallons of Fuel used @ <?=$defaultsarray['average_mpg']?> mpg:</td>
		<td align='right'></td>
		<td align='right'><?=number_format($gallons_used)?></td>
		<td align='right'></td>
		<td align='right'></td>
	</tr>
	<tr>
		<td>Estimated Cost of Fuel used:</td>
		<td align='right'></td>
		<td align='right'>$<?=money_format('', $fuel_charge)?></td>
		<td align='right'></td>
		<td align='right'></td>
	</tr>
	<tr>
		<td>Estimated Avg Cost per Gallon:</td>
		<td align='right'></td>
		<td align='right'>$<?=money_format('', $cost_per_gallon)?></td>
		<td align='right'></td>
		<td align='right'></td>
	</tr>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
		
	$().ready(function() {	
		
	});	
</script>
<? include('footer.php') ?>