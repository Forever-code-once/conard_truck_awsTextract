<?
$usetitle = "Report - Dispatches Cost Comparison";
$use_title = "Report - Dispatches Cost Comparison";
?>
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
	//$rfilter->show_origin	 	= true;
	//$rfilter->show_destination 	= true;
	//$rfilter->show_stops	 	= true;
	$rfilter->show_font_size		= true;
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
	
		$sql = "
			select trucks_log.*,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company,
				load_handler.id as load_handler_id
			
			from load_handler
				left join trucks_log on load_handler.id = trucks_log.load_handler_id
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id 
			
			where load_handler.deleted = 0 
				and trucks_log.deleted = 0 
				and trucks_log.load_handler_id = load_handler.id
				".$search_date_range."
				
				
				".($_POST['dispatch_id'] ? " and trucks_log.id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."')" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				".($_POST['report_origin'] ? " and trucks_log.origin like '%".sql_friendly($_POST['report_origin'])."%'" : '') ."
				".($_POST['report_origin_state'] ? " and trucks_log.origin_state like '%".sql_friendly($_POST['report_origin_state'])."%'" : '') ."
				".($_POST['report_destination'] ? " and trucks_log.destination = '".sql_friendly($_POST['report_destination'])."'" : '') ."
				".($_POST['report_destination_state'] ? " and trucks_log.destination_state = '".sql_friendly($_POST['report_destination_state'])."'" : '') ."
				
			order by trucks_log.load_handler_id asc,trucks_log.id asc	
		";	//and (trucks_log.daily_run_hourly > 0 or trucks_log.hours_worked > 0)
		
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:950px;text-align:left'>
	<tr>
		<td colspan='15'>
			<center>
			<span class='section_heading'>Dispatch Report</span>
			</center>
		</td>
	</tr>
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
		<td>DaysOTR</td>
		<td>DaysHrly</td>
		<td>HrlyWrkd</td>
		<td align='right'>Profit</td>
	</tr>
	<?
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_profit = 0;
		
		$tot_otr=0;
		$tot_hrly=0;
		$tot_worked=0;
		$tot_no_otrs=0;
		
		$short_haul=0;
		$long_haul=0;
		$total_haul=0;
		
		while($row = mysqli_fetch_array($data)) {
			$counter++;
			
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
			
			//$mrr_use_profit=$row['profit'];
			$mrr_use_profit=mrr_figure_profit_for_load_dispatch($row['load_handler_id'],$row['id']);		//see functions.php file for this profit.
			
			$total_profit += $mrr_use_profit;
			
			$tag1="";
			$tag2="";
			if( ($row['daily_run_otr'] + $row['daily_run_hourly'] + ($row['hours_worked']/$defaultsarray['local_driver_workweek_hours']) )==0)
			{
				$tag1="<span class='alert'>";
				$tag2="</span>";
				$tot_no_otrs++;
			}
			
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
					<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
					<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
					<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
					<td nowrap>$row[origin]</td>
					<td nowrap>$row[destination]</td>
					<td align='right'>".number_format($row['miles'])."</td>
					<td align='right'>".number_format($row['miles_deadhead'])."</td>
					<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
					<td nowrap>$row[name_truck]</td>
					<td nowrap>$row[trailer_name]</td>
					<td nowrap>$row[name_company]</td>
					<td align='right'>".$tag1."".number_format($row['daily_run_otr'],2)."".$tag2."</td>
					<td align='right'>".$tag1."".number_format($row['daily_run_hourly'],2)."".$tag2."</td>
					<td align='right'>".$tag1."".number_format( ($row['hours_worked']/$defaultsarray['local_driver_workweek_hours']) ,2)."".$tag2."</td>
					<td align='right'>$".money_format('',$mrr_use_profit)."</td>
				</tr>
			";
			$tot_otr+=$row['daily_run_otr'];
			$tot_hrly+=$row['daily_run_hourly'];
			$tot_worked+=($row['hours_worked']/$defaultsarray['local_driver_workweek_hours']);
						
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
							<td colspan='5'>
								".($row_stop['linedate_completed'] > 0 ? "Completed: ".date("M j, Y H:i", strtotime($row_stop['linedate_completed'])) : "")."
							</td>
						</tr>
					";
				}
			}
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
		<td colspan='15'>
			<hr>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td nowrap>&nbsp;</td>
		<td nowrap>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td align='right'>Miles</td>
		<td align='right'>Deadhead</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>DaysOTR</td>
		<td>DaysHrly</td>
		<td>HrlyWrkd</td>
		<td align='right'>Profit</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='4'><?=number_format($counter)?> dispatch(es)</td>
		
		<td align='right'><?=number_format($total_miles)?></td>
		<td align='right'><?=number_format($total_deadhead)?></td>
		<td colspan='4'>&nbsp;</td>
		<td align='right'><?=number_format($tot_otr,2)?></td>
		<td align='right'><?=number_format($tot_hrly,2)?></td>
		<td align='right'><?=number_format($tot_worked,2)?></td>
		<td align='right'>$<?=money_format('',$total_profit)?></td>
	</tr>
	<tr>
		<td colspan='5'><?=number_format($load_counter)?> Load(s)</td>
		
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
		<td colspan='7'>&nbsp;</td>
		
		<td align='right'>&nbsp;</td>
	</tr>
	
	<tr>
		<td colspan='15'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'><b>Days Run</b></td>
		<td colspan='4'><?= $tot_no_otrs ?> Dispatches with <span class='alert'>no Run</span>.</td>
		<td colspan='3' align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='4'><?=number_format($tot_otr,2)?> Days Run OTR + <?= number_format($tot_hrly,2) ?> Days Run Hourly</td>
		<td colspan='3' align='right'><?=number_format($tot_otr + $tot_hrly,2)?></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='4'><?=number_format($tot_otr,2)?> Days Run OTR + <?= number_format($tot_worked,2) ?> HoursWorked/WorkWeekHrs(<?=$defaultsarray['local_driver_workweek_hours']?>)</td>
		<td colspan='3' align='right'><?=number_format($tot_otr + $tot_worked,2)?></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='4'>Hourly Run to Hours Worked Days Difference</td>
		<td colspan='3' align='right' style='border-top:1px black solid'><?=number_format((($tot_otr + $tot_hrly) -($tot_otr + $tot_worked)) ,2)?></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='15'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='7'><?=number_format($short_haul)?> short haul(s) out of <?= $total_haul ?></td>
		<td align='right'><?=number_format($short_percent_haul,2)?>%</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='7'><?=number_format($long_haul)?> long haul(s) out of <?= $total_haul ?></td>
		<td align='right'><?=number_format($long_percent_haul,2)?>%</td>
	</tr>
	<tr>
		<td colspan='15'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'><b>Radius of Operations</b></td>
		<td colspan='7'><?=number_format($range_100)?> <span class='alert'>0 - 100</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_100,2)?>%</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='7'><?=number_format($range_300)?> <span class='alert'>101 - 300</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_300,2)?>%</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='7'><?=number_format($range_500)?> <span class='alert'>301 - 500</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_500,2)?>%</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='7'><?=number_format($range_501)?> <span class='alert'>501+</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_501,2)?>%</td>
	</tr>
	
	
	<tr>
		<td colspan='15'>
			&nbsp;
		</td>
	</tr>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
</script>
<? include('footer.php') ?>