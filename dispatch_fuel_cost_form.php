<?
$usetitle = "Dispatch Fuel Cost Form";
$use_title = "Dispatch Fuel Cost Form";
?>
<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	if(isset($_GET['truck_id'])) {
		$_POST['truck_id'] = $_GET['truck_id'];
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
	$rfilter->show_customer 			= true;
	$rfilter->show_driver 			= true;
	$rfilter->show_truck 			= true;
	$rfilter->show_trailer 			= true;
	$rfilter->show_load_id 			= true;
	$rfilter->show_dispatch_id 		= true;
	$rfilter->show_origin	 		= true;
	$rfilter->show_destination 		= true;
	$rfilter->show_stops	 		= true;
	$rfilter->show_font_size			= true;
	$rfilter->search_sort_by_report	= true;
	$rfilter->show_filter();
	
	
	$load_counter=0;
	$load_arr[0]=0;
	
	$range_all=0;
	$range_100=0;		//  0-100
	$range_300=0;		//101-300
	$range_500=0;		//301-500
	$range_501=0;		//501-?
		
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
		
		$mrr_trailer_find="";
		/*
		if($_POST['trailer_id'] > 0)
		{
			$mrr_trailer_find="
				(
					select load_handler_stops.id 
					from load_handler_stops 
					where load_handler_stops.load_handler_id=load_handler.id 
						and load_handler_stops.trucks_log_id=trucks_log.id 
						and load_handler_stops.start_trailer_id='".sql_friendly($_POST['trailer_id'])."'
						and load_handler_stops.deleted=0
				) as mrr_trailer_switched,
			";	
		}
		*/
		$mrr_trailer_find2="";
		if($_POST['trailer_id'] > 0)
		{
			$mrr_trailer_find2="
				and 
				(
					trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'
					or (
					     select count(*)
     					from load_handler_stops 
     					where load_handler_stops.load_handler_id=load_handler.id 
     						and load_handler_stops.trucks_log_id=trucks_log.id 
     						and load_handler_stops.start_trailer_id='".sql_friendly($_POST['trailer_id'])."'
     						and load_handler_stops.deleted=0
					) > 0
				)
			";	
		}
		
		$sorter="";	//"trucks.name_truck asc,";
		
		if(isset($_POST['search_sort_by_report']))
		{
			if($_POST['search_sort_by_report']=='date')			$sorter="";
			if($_POST['search_sort_by_report']=='load')			$sorter="trucks_log.load_handler_id asc,";
			if($_POST['search_sort_by_report']=='dispatch')		$sorter="trucks_log.id asc,";
			if($_POST['search_sort_by_report']=='driver')		$sorter="drivers.name_driver_last asc,drivers.name_driver_first asc,";
			if($_POST['search_sort_by_report']=='customer')		$sorter="customers.name_company asc,";			
		}
		
		
		$sql = "
			select trucks_log.*,
				trucks_log.trailer_id as cur_trailer_id,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trailers.id as trailer_name_id,
				trucks.name_truck,
				customers.name_company,	
				load_handler.actual_bill_customer,
				load_handler.master_load,			
				".$mrr_trailer_find."
				load_handler.id as load_handler_id
			
			from load_handler
				left join trucks_log on load_handler.id = trucks_log.load_handler_id and trucks_log.deleted = 0
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				
			where load_handler.deleted = 0
				
				$search_date_range
				
				".($_POST['dispatch_id'] ? " and trucks_log.id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."')" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				
				".$mrr_trailer_find2."
				
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				".($_POST['report_origin'] ? " and trucks_log.origin like '%".sql_friendly($_POST['report_origin'])."%'" : '') ."
				".($_POST['report_origin_state'] ? " and trucks_log.origin_state like '%".sql_friendly($_POST['report_origin_state'])."%'" : '') ."
				".($_POST['report_destination'] ? " and trucks_log.destination = '".sql_friendly($_POST['report_destination'])."'" : '') ."
				".($_POST['report_destination_state'] ? " and trucks_log.destination_state = '".sql_friendly($_POST['report_destination_state'])."'" : '') ."
			
			order by ".$sorter."trucks_log.linedate_pickup_eta
		";
		//".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
		
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
	?>
	<div style=color:purple;margin:10px;'>
		<b>Use this form to set the fuel cost or edit the special flat rate cost for special Owner-Operator type dispatches.</b><br>
		For this report/form, the Flat Rate is the cost for the dispatch ...to be used as the Pay for the Driver (Owner Operator)... and deducted from the billed amount to figure the profit.<br>
		The Fuel Card amount will be deducted from the drivers pay, which would be the amount agreed upon by the Indepentent Contractor/Owner Operator, but will not affect the profit.<br>
		Profit is the Actual Bill Amount (from the Load) minus the Flat Rate (cost) for the Dispatch.  Fuel Card amount is for tracking purposes and drive payroll calculations only.<br>	
	</div>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1200px;text-align:left'>
	<tr>
		<td colspan='13' align='left'>
			<center><span class='section_heading'>Dispatch Fuel Cost Form</span></center>			
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td nowrap>Load ID</td>
		<td nowrap>Dispatch ID</td>
		<td>Date</td>
		<td>Customer</td>
		<td>Origin</td>
		<td>Destination</td>		
		<td>Driver</td>
		<td>Truck</td>
		<td>Trailer</td>
		<td align='right' nowrap>Load Billed</td>
		<td align='right' nowrap>Flat Rate</td>		
		<td align='right' nowrap>Fuel Card</td>		
		<td align='right'>Profit</td>
	</tr>
	<?
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_profit = 0;
		$total_billed=0;
		$total_coster=0;
		$total_fueler=0;
		
		$short_haul=0;
		$long_haul=0;
		$total_haul=0;
		
		$hourly_days=0;
		$otr_days=0;
		$last_truck_was="";
		
		$truck_billed=0;
		$truck_coster=0;
		$truck_fueler=0;
		$truck_loads=0;
		$truck_miles=0;
		$truck_deadhead=0;
		$truck_otr=0;
		$truck_hours=0;
		$truck_profit=0;	
				
		while($row = mysqli_fetch_array($data)) {
			$counter++;
			
			if($sorter=="trucks.name_truck asc," && $last_truck_was!="" && $last_truck_was!=$row['name_truck'])
			{
				/*
				echo "
					<tr>
						<td colspan='9' align='right'><b>Truck ".$last_truck_was." Totals: (".$truck_loads.")</b> </td>
						<td align='right'><b>$".number_format($truck_billed,2)."</b></td>
						<td align='right'><b>$".number_format($truck_coster,2)."</b></td>
						<td align='right'><b>$".number_format($truck_fueler,2)."</b></td>
						<td align='right'><b>$".money_format('',$truck_profit)."</b></td>
					</tr>
					<tr>
						<td colpsan='13'>&nbsp;</td>
					</tr>
				";	
				
				$truck_loads=0;
				$truck_miles=0;
				$truck_deadhead=0;
				$truck_otr=0;
				$truck_hours=0;
				$truck_profit=0;	
				
				$truck_billed=0;
				$truck_coster=0;
				$truck_fueler=0;
				
				*/
			}
			$last_truck_was=$row['name_truck'];
			
			$load_found=0;
			for($z=0;$z < $load_counter; $z++)
			{
				if(	$load_arr[ $z ] == $row['load_handler_id'])	$load_found=1;
			}
			if($load_found==0)
			{
				$load_arr[ $load_counter ]=$row['load_handler_id'];	
				$load_counter++;	
			}
			
			$total_miles += $row['miles'];
			$total_deadhead += $row['miles_deadhead'];
			
			
			$show_this_set_of_stops=0;
			$trailer_disp=$row['trailer_name'];
			if($_POST['trailer_id'] > 0 && $row['cur_trailer_id']!=$_POST['trailer_id'])
			{
				$show_this_set_of_stops=1;
				$trailer_disp="<span style='color:blue;' title='This trailer was switched...'><b>".$row['trailer_name']."</b></span>";	
			}
			
			
			$mrr_use_profit=$row['actual_bill_customer'] - $row['flat_cost_rate'];		//$row['flat_cost_fuel_rate']
			
			//$mrr_use_profit=$row['profit'];
			//$mrr_use_profit=mrr_figure_profit_for_load_dispatch($row['load_handler_id'],$row['id']);		//see functions.php file for this profit.
			
			$total_profit += $mrr_use_profit;
			
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
					<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
					<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
					<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
					<td nowrap>$row[name_company]</td>
					<td nowrap>$row[origin]</td>
					<td nowrap>$row[destination]</td>
					<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
					<td nowrap>$row[name_truck]</td>
					<td nowrap>".$trailer_disp."</td>
					<td align='right'>$".number_format($row['actual_bill_customer'],2)."</td>					
					<td align='right'><input type='text' name='disp_".$row['id']."_cost' id='disp_".$row['id']."_cost' value='$".number_format($row['flat_cost_rate'],2)."' onBlur='mrr_update_dispatch_flat_costs(".$row['id'].");' style='text-align:right; width:75px;'></td>
					<td align='right'><input type='text' name='disp_".$row['id']."_fuel' id='disp_".$row['id']."_fuel' value='$".number_format($row['flat_cost_fuel_rate'],2)."' onBlur='mrr_update_dispatch_flat_costs(".$row['id'].");' style='text-align:right; width:75px;'></td>
					<td align='right'>$".money_format('',$mrr_use_profit)."</td>
				</tr>
			";
						
			$truck_billed+=$row['actual_bill_customer'];
			$truck_coster+=$row['flat_cost_rate'];
			$truck_fueler+=$row['flat_cost_fuel_rate'];
			$truck_loads++;
			$truck_miles+=$row['miles'];
			$truck_deadhead+=$row['miles_deadhead'];
			$truck_otr+=$row['daily_run_otr'];
			$truck_hours+=$row['daily_run_hourly'];
			$truck_profit+=$mrr_use_profit;			
			
			
			$total_billed+=$row['actual_bill_customer'];
			$total_coster+=$row['flat_cost_rate'];
			$total_fueler+=$row['flat_cost_fuel_rate'];
				
			
			$hourly_days+=$row['daily_run_hourly'];
			$otr_days+=$row['daily_run_otr'];
			
			$total_haul++;
			if(($row['miles']+$row['miles_deadhead']) > 200)
			{				
				$long_haul++;
			}
			else
			{
				$short_haul++;
			}
			
			if($row['miles'] >= 0)			$range_all++;
			
			if($row['miles'] >=	 0 && $row['miles'] <= 100)			$range_100++;		//  0-100
			if($row['miles'] >= 101 && $row['miles'] <= 300)			$range_300++;		//101-300
			if($row['miles'] >= 301 && $row['miles'] <= 500)			$range_500++;		//301-500
			if($row['miles'] >= 501)								$range_501++;		//501-?
								
			// if the user selected to show the detailed stops for this dispatch, load them and show them
			if(isset($_POST['show_stops']) || $show_this_set_of_stops==1) {
				$sql = "
					select load_handler_stops.*,
						(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
						(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name
					
					from load_handler_stops
					where load_handler_stops.deleted = 0
						and load_handler_stops.trucks_log_id = '$row[id]'
					order by load_handler_stops.linedate_pickup_eta
				";
				$data_stops = simple_query($sql);
				
				while($row_stop = mysqli_fetch_array($data_stops)) {
					
					$stop_typer="Shipper";
					if($row_stop['stop_type_id']==2)		$stop_typer="Consignee";
					
					$mrr_show_switch="";
					if($row_stop['start_trailer_name'] != $row_stop['end_trailer_name'])
					{
						$mrr_show_switch="Trailer <b>".$row_stop['start_trailer_name']."</b><br>Switched to ".$row_stop['end_trailer_name'];	
					}
					
					echo "
						<tr style='background-color:#e2ffe4'>
							<td valign='top' nowrap align='right'>Stop:</td>
							<td valign='top' colspan='2' nowrap align='right'>".$stop_typer.": $row_stop[shipper_name]</td>
							<td valign='top' colspan='3' nowrap>$row_stop[shipper_address1] $row_stop[shipper_city], $row_stop[shipper_state] $row_stop[shipper_zip]</td>
							<td valign='top' colspan='2'>Pickup: ".date("M j, Y H:i", strtotime($row_stop['linedate_pickup_eta']))."</td>
							<td valign='top' colspan='3'>".$mrr_show_switch."</td>							
							<td valign='top' colspan='2'>
								".($row_stop['linedate_completed'] > 0 ? "Completed: ".date("M j, Y H:i", strtotime($row_stop['linedate_completed'])) : "")."
							</td>
						</tr>
					";
				}
			}
		}
		
		echo mrr_pull_unavailable_driver_days_and_codes($_POST['date_from'],$_POST['date_to'],$_POST['driver_id']);
		
		if($sorter=="trucks.name_truck asc,")
		{					// && $last_truck_was!="" && $last_truck_was!=$row['name_truck']
			echo "
				<tr>
					<td colspan='9' align='right'><b>Truck ".$last_truck_was." Totals: (".$truck_loads.")</b> </td>
					<td align='right'><b>$".number_format($truck_billed,2)."</b></td>
					<td align='right'><b>$".number_format($truck_coster,2)."</b></td>
					<td align='right'><b>$".number_format($truck_fueler,2)."</b></td>
					<td align='right'><b>$".money_format('',$truck_profit)."</b></td>
				</tr>
				<tr>
					<td colpsan='13'>&nbsp;</td>
				</tr>
			";	
			
			$truck_loads=0;
			$truck_miles=0;
			$truck_deadhead=0;
			$truck_otr=0;
			$truck_hours=0;
			$truck_profit=0;	
			
			$truck_billed=0;
			$truck_coster=0;
			$truck_fueler=0;
		}
		else
		{	/*
			echo "
				<tr>
					<td colspan='13'><hr></td>
				</tr>
				<tr>
					<td colspan='9' align='right'><b>Total</b> </td>
					<td align='right'><b>$".number_format($truck_billed,2)."</b></td>
					<td align='right'><b>$".number_format($truck_coster,2)."</b></td>
					<td align='right'><b>{$".number_format($truck_fueler,2)."}</b></td>
					<td align='right'><b>$".money_format('',$truck_profit)."</b></td>
				</tr>
				<tr>
					<td colpsan='13'>&nbsp;</td>
				</tr>
			";	*/
		}
		
		
		$short_percent_haul=0;
		$long_percent_haul=0;
		if($total_haul>0)
		{
			$short_percent_haul=$short_haul / $total_haul * 100;
			$long_percent_haul=$long_haul / $total_haul * 100;	
		}
		
		
		$percent_100=0;
		$percent_300=0;
		$percent_500=0;
		$percent_501=0;
		
		if($range_all>0)
		{
			$percent_100= $range_100 / $range_all * 100;
			$percent_300= $range_300 / $range_all * 100;
			$percent_500= $range_500 / $range_all * 100;
			$percent_501= $range_501 / $range_all * 100;	
		}		
	?>
	<tr>
		<td colspan='13'>
			<hr>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td nowrap>Load ID</td>
		<td nowrap>Dispatch ID</td>
		<td>Date</td>
		<td>Customer</td>
		<td>Origin</td>
		<td>Destination</td>		
		<td>Driver</td>
		<td>Truck</td>
		<td>Trailer</td>
		<td align='right' nowrap>Load Billed</td>
		<td align='right'>Flat Rate</td>		
		<td align='right'>Fuel Card</td>		
		<td align='right'>Profit</td>
	</tr>
	<tr>
		<td colspan='5'><?=number_format($load_counter)?> Load(s)</td>			
		<td colspan='4'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='8'><?=number_format($counter)?> dispatch(es)</td>	
		<td align='right'>$<?=money_format('',$total_billed)?></td>
		<td align='right'>$<?=money_format('',$total_coster)?></td>	
		<td align='right'>{$<?=money_format('',$total_fueler)?>}</td>
		<td align='right'>$<?=money_format('',$total_profit)?></td>
	</tr>
	<tr>
		<td colspan='13'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='5'>&nbsp;</td>
		<td colspan='5'><?=number_format($short_haul)?> short haul(s) out of <?= $total_haul ?></td>
		<td align='right'><?=number_format($short_percent_haul,2)?>%</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='5'>&nbsp;</td>
		<td colspan='5'><?=number_format($long_haul)?> long haul(s) out of <?= $total_haul ?></td>
		<td align='right'><?=number_format($long_percent_haul,2)?>%</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='13'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='5'><b>Radius of Operations</b></td>
		<td colspan='5'><?=number_format($range_100)?> <span class='alert'>0 - 100</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_100,2)?>%</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='5'>&nbsp;</td>
		<td colspan='5'><?=number_format($range_300)?> <span class='alert'>101 - 300</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_300,2)?>%</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='5'>&nbsp;</td>
		<td colspan='5'><?=number_format($range_500)?> <span class='alert'>301 - 500</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_500,2)?>%</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='5'>&nbsp;</td>
		<td colspan='5'><?=number_format($range_501)?> <span class='alert'>501+</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_501,2)?>%</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='13'>
			&nbsp;
		</td>
	</tr>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
	function mrr_update_dispatch_flat_costs(id)
	{		
		coster=get_amount($('#disp_'+id+'_cost').val());
		fueler=get_amount($('#disp_'+id+'_fuel').val());
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_update_dispatch_flat_costs",
			   data: {
			   		"disp_id":id,
			   		"cost":coster,
			   		"fuel":fueler
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		$.noticeAdd({text: "Updated Cost/Fuel for Dispatch "+id+" successfully."});
			   }	
		});
	}
</script>
<? include('footer.php') ?>