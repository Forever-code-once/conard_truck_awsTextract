<? include('application.php') ?>
<? include('header.php') ?>
<div style='text-align:left;margin:10px;width:510px;float:left' class='admin_menu1'>
	<div class='section_heading'>Days Available Report</div>
	Enter any date for the month you would like to build the days available report for. <br>
	For example, if you wanted <?=date("M, Y")?>, select any date during that month <br>
	and the system will automatically generate the full month report.  
	<br><b>Use the Show Historic Values checkbox to use the date as the end date (not the full month).</b>
	<?
		$_POST['mrr_misc_value_label']="Show Historic Values";				//label for misc checkbox so it can be used for anything		
	?>
	<div style='clear:both'></div>
	<?
		$rfilter = new report_filter();
		$rfilter->show_date_range 		= false;
		$rfilter->show_single_date 		= true;
		$rfilter->mrr_show_misc_value		= true;
		$rfilter->show_font_size			= true;
		$rfilter->show_filter();
	?>
	

	<div style='clear:both'></div>
	<table style='text-align:left;width:500px;margin-left:0px'>
	<tr>
		<td>
	<?
		if(isset($_POST['build_report'])) 
		{
			$date_start = strtotime(date("m/1/Y", strtotime($_POST['report_date'])));
			
			if(!isset($_POST['mrr_show_misc_values']))		$_POST['mrr_show_misc_values']=0;
			
			if($_POST['mrr_show_misc_values'] > 0)
			{
				$days_in_month = date("t", $date_start);
				$date_end = strtotime($_POST['report_date']);
				if($date_end > time()) 		$date_end = strtotime(date("m/d/Y"));
			}
			else
			{
				$days_in_month = date("t", $date_start);
				$date_end = strtotime(date("m/".$days_in_month."/Y", $date_start));
				if($date_end > time()) 		$date_end = strtotime(date("m/d/Y"));
			}
			

			$days_detailed = "
				<table class='admin_menu3 font_display_section' style='width:500px;margin-top:10px'>
				<tr>
					<td><b>Day</b></td>
					<td><b>Trucks Active</b></td>
				</tr>
			";	
			
			// figure out how many billable days are in this month
			$days_available_array = get_days_available($date_start, $date_end);
			
			$billable_days = $days_available_array['billable_days'];
			$billable_days_so_far = $days_available_array['billable_days_so_far'];			
			$days_available = $days_available_array['days_available'];
			$days_available_so_far = $days_available_array['days_available_so_far'];
			$days_detailed .= $days_available_array['days_detailed_html'];
			
			$days_detailed .= "</table>";
			

			$days_actual = get_days_run($date_start, $date_end);

			$ratio = 0;
			if($days_actual > 0 && $days_available_so_far > 0) $ratio = number_format($days_actual / $days_available_so_far * 100);
						
			echo "
				<table class='admin_menu2 font_display_section' style='width:500px'>
				<tr>
					<td>Billable days in month:</td>
					<td align='right'>$billable_days</td>
				</tr>
				<tr>
					<td>Billable days so far:</td> 
					<td align='right'>$billable_days_so_far</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Truck days available in month:</td>
					<td align='right'>$days_available</td>
				</tr>
				<tr>
					<td>Truck days available (so far):</td>
					<td align='right'>$days_available_so_far</td>
				</tr>
				<tr>
					<td>Actual days run (so far):</td>
					<td align='right'>".((int)$days_actual != $days_actual ? number_format($days_actual,2) : number_format($days_actual,0))."</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Percentage:</td>
					<td align='right'>$ratio%</td>
				</tr>
				</table>
				
				$days_detailed
			";
			$sql = "
				select trucks_log.*
				
				from trucks_log, load_handler
				where trucks_log.deleted = 0
					and load_handler.deleted = 0
					and trucks_log.load_handler_id = load_handler.id
					and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') >= '".date("Y-m-d", $date_start)."'
					and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') <= '".date("Y-m-d", $date_end)."'
				order by linedate_pickup_eta
			";
			$data_log = simple_query($sql);
			
			echo "
				<table class='admin_menu3 font_display_section' style='width:500px;margin-top:10px'>
				<tr>
					<td><b>Load ID</b></td>
					<td><b>Dispatch ID</b></td>
					<td><b>Pickup ETA</b></td>
					<td align='right'><b>Days OTR</b></td>
					<td align='right'><b>Days Hourly</b></td>					
					<td align='right'><b>Total</b></td>
				</tr>
			";
			$otr_total = 0;
			$total_hours = 0;
			$total_otr = 0;
			while($row_log = mysqli_fetch_array($data_log)) {
				$hours_worked = ($row_log['hours_worked'] / $defaultsarray['local_driver_workweek_hours']);
				$otr_total += $row_log['daily_run_otr'] + $hours_worked;
				$total_hours += $hours_worked;
				$total_otr += $row_log['daily_run_otr'];
				echo "
					<tr>
						<td><a href='manage_load.php?load_id=$row_log[load_handler_id]' target='view_load_$row_log[id]'>$row_log[load_handler_id]</a></td>
						<td><a href='add_entry_truck.php?load_id=$row_log[load_handler_id]&id=$row_log[id]' target='view_dispatch_$row_log[id]'>$row_log[id]</a></td>
						<td>".date("m/d/Y", strtotime($row_log['linedate_pickup_eta']))."</td>
						<td align='right'>".($row_log['daily_run_otr'] == 0 ? "<span class='show_inactive'>" : "").number_format($row_log['daily_run_otr'])."</span></td>
						<td align='right'>".($hours_worked == 0 ? "<span class='show_inactive'>" : "").$hours_worked."</span></td>
						<td align='right'>$otr_total</td>
					</tr>
				";
			}
			echo "
				<tr>
					<td colspan='10'><hr></td>
				</tr>
				<tr>
					<td colspan='3'>&nbsp;</td>
					<td align='right'><b>".number_format($total_otr)."</b></td>
					<td align='right'><b>".$total_hours."</b></td>
					<td align='right'><b>".$otr_total."</b></td>
				</tr>
				</table>
			";
		}
	?>
		</td>
	</tr>
	</table>
</div>