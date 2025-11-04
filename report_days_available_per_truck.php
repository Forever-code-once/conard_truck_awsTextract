<? include('application.php') ?>
<? include('header.php') ?>
<div style='text-align:left;margin:10px;width:920px;float:left' class='admin_menu1'>
	<div class='section_heading'>Days Available Report - Per Truck</div>
    <!----
	Enter any date for the month you would like to build the days available report for. <br>
	For example, if you wanted <?=date("M, Y")?>, select any date during that month <br>
	and the system will automatically generate the full month report.
	<br><b>Use the Show Historic Values checkbox to use the date as the end date (not the full month).</b>
	---->
	<?
		$_POST['mrr_misc_value_label']="Show Historic Values";				//label for misc checkbox so it can be used for anything		
	?>
	<div style='clear:both'></div>
	<?
		$rfilter = new report_filter();
		//$rfilter->show_date_range 		= false;
		//$rfilter->show_single_date 		= true;
		//$rfilter->mrr_show_misc_value		= true;
		$rfilter->show_font_size			= true;
		$rfilter->show_filter();
	?>
	

	<div style='clear:both'></div>
	<table class='font_display_section' style='text-align:left;width:910px;margin-left:0px'>
	<tr>
		<td>
	<?
		if(isset($_POST['build_report'])) 
		{
		    if(!isset($_POST['mrr_show_misc_values']))		$_POST['mrr_show_misc_values']=0;
             
		    $date_start = strtotime(date("m/d/Y",strtotime($_POST['date_from'])));
		    $date_end = strtotime(date("m/d/Y",strtotime($_POST['date_to'])));
		    $days_in_month=date_diff($date_end, $date_start);
		    $days_in_month+=1;
		    
		    /*
		    $date_start = strtotime(date("m/1/Y", strtotime($_POST['report_date'])));
					
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
				if($date_end > time()) $date_end = strtotime(date("m/d/Y"));
			}
			*/
			
			// figure out how many billable days are in this money
			$billable_days = 0;
			$billable_days_so_far = 0;
			$total_available = 0;
			$total_used = 0;
			$total_available_yesterday = 0;
			$total_used_yesterday = 0;
			
			// get the list of active trucks
			$sql = "
				select *
				
				from trucks
				where deleted = 0
					and active_cnt_exclude=0

				order by name_truck
			";
			$data_trucks_active = simple_query($sql);
		
			for($i=0;$i < $days_in_month;$i++) {
				$day_of_week = date("w", strtotime("$i day", $date_start));
				if($day_of_week != 0 && $day_of_week != 6) {
					if(strtotime("$i day", $date_start) <= $date_end) $billable_days_so_far++;
					$billable_days++;
				}				
			}
			
			echo "
				<table class='admin_menu2' style='width:900px;'>
				<tr>
					<td></td>
					<td><b>Truck</b></td>
					<td><b>Starting Driver</b></td>
					<td><b>Ending Driver <span class='alert'>(if different)</span></b></td>
					<td align='right'><b>Available</b></td>
					<td align='right'><b>Used</b></td>
					<td align='right'><b>Ratio</b></td>
				</tr>
			";
			/*
			echo "
				<tr>
					<td>Billable days in month:</td>
					<td align='right'>$billable_days</td>
				</tr>
				<tr>
					<td>Billable days so far:</td> 
					<td align='right'>$billable_days_so_far</td>
				</tr>
			";
			*/
			$truck_id_array = array();
			while($row_truck = mysqli_fetch_array($data_trucks_active)) {
				
				/*
				$truck_billable_days = 0;
				for($i=0;$i < $days_in_month;$i++) {
					$day_of_week = date("w", strtotime("$i day", $date_start));
					if($day_of_week != 0 && $day_of_week != 6 && strtotime("$i day", $date_start) <= $date_end) {
						$sql = "
							select trucks.id
							
							from equipment_history, trucks 
							where equipment_type_id = 1 
								and trucks.id = '".sql_friendly($row_truck['id'])."'
								and trucks.deleted = 0
								and trucks.id = equipment_history.equipment_id
								and equipment_history.deleted = 0
								and equipment_history.linedate_aquired <= '".date("Y-m-d", strtotime("$i day", $date_start))."'
								and (
									equipment_history.linedate_returned = '0000-00-00'
									or equipment_history.linedate_returned > '".date("Y-m-d", strtotime("$i day", $date_start))."'
								)
								and trucks.id not in 	(
																select eh.replacement_xref_id
																
																from equipment_history eh 
																where eh.deleted = 0
																	and eh.equipment_type_id = 1
																	and eh.replacement_xref_id = '".sql_friendly($row_truck['id'])."'
																	and eh.linedate_aquired <= '".date("Y-m-d", strtotime("$i day", $date_start))."'
																	and (
																		eh.linedate_returned = '0000-00-00'
																		or eh.linedate_returned > '".date("Y-m-d", strtotime("$i day", $date_start))."'
																	)
																	
															)
								
						";
						//d($sql);
						$data_count = simple_query($sql);
						if(mysqli_num_rows($data_count)) $truck_billable_days++;
					}
					
				}
				*/
				/*
				$sql = "
					select sum(trucks_log.daily_run_otr + (trucks_log.hours_worked / $defaultsarray[local_driver_workweek_hours])) as days_run
					
					from trucks_log, load_handler
					where trucks_log.deleted = 0
						and load_handler.deleted = 0
						and trucks_log.load_handler_id = load_handler.id
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') >= '".date("Y-m-d", $date_start)."'
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') <= '".date("Y-m-d", $date_end)."'
						and trucks_log.truck_id = '$row_truck[id]'
				";
				
				//echo "$row_truck[id]<br>";
				$data_actual = simple_query($sql);
				$row_actual = mysqli_fetch_array($data_actual);
				$days_actual = $row_actual['days_run'];
				*/
				$truck_id_array[] = $row_truck['id'];
				
				
				//Get drivers for the truck...first and last so it is easy to see if it changed during the month...(unless it changed back).
				$first_driver_id=0;			$first_driver2_id=0;
				$last_driver_id=0;			$last_driver2_id=0;
				$first_driver_name="";		$first_driver2_name="";
				$last_driver_name="";		$last_driver2_name="";
				$dcntr=0;
				$changed=0;
				$sql = "
					select driver_id,
						driver2_id					
					from trucks_log
						left join load_handler on trucks_log.load_handler_id = load_handler.id
					where trucks_log.deleted = 0
						and load_handler.deleted = 0
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') >= '".date("Y-m-d", $date_start)."'
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') <= '".date("Y-m-d", $date_end)."'
						and trucks_log.truck_id = '$row_truck[id]'
					order by trucks_log.linedate_pickup_eta asc
				";
				$data_drivers_mrr = simple_query($sql);
				while($row_drivers_mrr = mysqli_fetch_array($data_drivers_mrr))
				{
					if($dcntr==0 || $first_driver_id==0)
					{
						$first_driver_id=$row_drivers_mrr['driver_id'];		$first_driver2_id=$row_drivers_mrr['driver2_id'];
					}
					$last_driver_id=$row_drivers_mrr['driver_id'];			$last_driver2_id=$row_drivers_mrr['driver2_id'];
					
					
					if($first_driver_id!=$last_driver_id && $first_driver_id > 0)	$changed++;
					
					$dcntr++;
				}						
				$first_driver_name=mrr_get_driver_name($first_driver_id);		$first_driver2_name=mrr_get_driver_name($first_driver2_id);
				$last_driver_name=mrr_get_driver_name($last_driver_id);		$last_driver2_name=mrr_get_driver_name($last_driver2_id);				
				if($first_driver_id!=$last_driver_id)
				{
					$last_driver_name="<span class='alert'><b>".$last_driver_name;	
					$last_driver2_name.="</b></span>";
				}
				
				
				$truck_billable_days_array = get_days_available($date_start, $date_end, $row_truck['id']);
				$truck_billable_days = $truck_billable_days_array['days_available_so_far'];
				$days_actual = get_days_run($date_start, $date_end, $row_truck['id']);
				
				if(date("m/d/Y", $date_end) == date("m/d/Y")) {
					// the end date is today's date, show also show yesterday's totals
					// so today's totals don't skew the results
					// (i.e. if this is the morning, the report will show a large percentage of missing 'days' on the 
					// report, as the dispatches still need to fill in today's usage as it happens...
					$truck_billable_days_array_yesterday = get_days_available($date_start, strtotime("-1 days", $date_end), $row_truck['id']);
					$truck_billable_days_yesterday = $truck_billable_days_array_yesterday['days_available_so_far'];
					$days_actual_yesterday = get_days_run($date_start, strtotime("-1 days", $date_end), $row_truck['id']);
					
					$total_available_yesterday += $truck_billable_days_yesterday;
					$total_used_yesterday += $days_actual_yesterday;
				}

				$total_available += $truck_billable_days;
				$total_used += $days_actual;
				
				// only show the trucks that had some billable days available
				if($truck_billable_days) {
									
					// see if this truck was replaced at all
					$sql = "
						select equipment_history.*,
							t.name_truck as replacement_truck_name,
							t.id as truck_id
						
						from equipment_history
							inner join trucks on trucks.id = equipment_history.replacement_xref_id and trucks.id = '".sql_friendly($row_truck['id'])."'
							left join trucks t on t.id = equipment_history.equipment_id
						where equipment_history.linedate_aquired <= '".date("Y-m-d", $date_end)."'
							and equipment_history.equipment_type_id=1
							and equipment_history.deleted=0
							and (
								equipment_history.linedate_returned = 0
								or equipment_history.linedate_returned > '".date("Y-m-d", $date_start)."'
								)
							
					";
					$data_replacement = simple_query($sql);
					
					$days_actual_replacement = 0;
					if(mysqli_num_rows($data_replacement)) {
						while($row_replacement = mysqli_fetch_array($data_replacement)) {
							$days_actual_replacement += get_days_run($date_start, $date_end, $row_replacement['truck_id']);
						}
					}
					
					$days_display = $days_actual + $days_actual_replacement;
					
					//$truck_billable_days=(int) $defaultsarray['billable_days_in_month'];
					
					
					if($days_display == 0) {
						$ratio = 0;
					} else {
						$ratio = number_format($days_display / $truck_billable_days * 100);
					}
					
					echo "
						<tr>
							<td><a href='admin_trucks.php?id=$row_truck[id]' target='view_truck_$row_truck[id]'>view truck</a></td>
							<td><a href='report_sales_by_truck.php?date_from=".date("m/d/Y", $date_start)."&date_to=".date("m/d/Y", $date_end)."&truck_id=$row_truck[id]' target='view_truck_report_$row_truck[id]'>$row_truck[name_truck]</a></td>
							<td>".$first_driver_name."".($first_driver2_name!="" ? "<br>& ".$first_driver2_name."" : "")."</td>						
							<td>".$last_driver_name."".(($last_driver2_name!="" && $last_driver2_name!="</b></span>") || ($changed>0 && $last_driver2_name!="</b></span>") ? "<br>& ".$last_driver2_name."" : "")."</td>
							<td align='right'>$truck_billable_days</td>							
							<td align='right'>".((int)$days_display != $days_display ? number_format($days_display,2) : number_format($days_display,0))."</td>
							<td align='right'>$ratio%</td>
						</tr>						
					";	//<tr><td colspan='7'>".$sql."</td></tr>
					
					$days_actual_replacement = 0;
					if(mysqli_num_rows($data_replacement)) {
						mysqli_data_seek($data_replacement, 0);
						while($row_replacement = mysqli_fetch_array($data_replacement)) {
							$days_actual_replacement += get_days_run($date_start, $date_end, $row_replacement['truck_id']);
							echo "
								<tr>
									<td></td>
									<td>$row_replacement[replacement_truck_name]</td>
									<td colspan='4'>
										".date("m/d/Y", strtotime($row_replacement['linedate_aquired'])).($row_replacement['linedate_returned'] > 0 ? " - ".date("m/d/Y", strtotime($row_replacement['linedate_returned'])) : " - (still out)")."
									</td>
								</tr>
							";
						}
					}
				}
			}
			
			if(count($truck_id_array)) {
				// see if there are any unaccounted for days (i.e. from deleted trucks)
				$sql = "
					select sum(trucks_log.daily_run_otr + (trucks_log.hours_worked / $defaultsarray[local_driver_workweek_hours])) as days_run
					
					from trucks_log, load_handler
					where trucks_log.deleted = 0
						and load_handler.deleted = 0
						and trucks_log.load_handler_id = load_handler.id
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') >= '".date("Y-m-d", $date_start)."'
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') <= '".date("Y-m-d", $date_end)."'
						and trucks_log.truck_id not in (".implode(",", $truck_id_array).")
				";
				//echo "$row_truck[id]<br>";
				$data_actual = simple_query($sql);
				$row_actual = mysqli_fetch_array($data_actual);
				$days_actual = $row_actual['days_run'];

				$total_used += $days_actual;
				echo "
					<tr>
						<td>&nbsp;</td>
						<td colspan='4'>Other (deleted trucks)</td>
						<td align='right'>".((int)$days_actual != $days_actual ? number_format($days_actual,2) : number_format($days_actual,0))."</td>
						<td align='right'>&nbsp;</td>
					</tr>
				";
			}
			
			if(date("m/d/Y", $date_end) == date("m/d/Y")) {
				echo "
					<tr>
						<td colspan='4' align='right'>As of yesterday</td>
						<td align='right'>$total_available_yesterday</td>
						<td align='right'>$total_used_yesterday</td>
						<td align='right'>".($total_available_yesterday > 0 && $total_used_yesterday > 0 ? number_format($total_used_yesterday / $total_available_yesterday * 100): "0") ."%</td>
					</tr>
				";
			}			
			
			echo "
				<tr>
					<td colspan='7'><hr></td>
				</tr>
				<tr>
					<td colspan='4' align='right'></td>
					<td align='right'>$total_available</td>
					<td align='right'>$total_used</td>
					<td align='right'>".($total_available > 0 && $total_used > 0 ? number_format($total_used / $total_available * 100): "0") ."%</td>
				</tr>
			";
			
			
			echo "</table>";

		}
	?>
		</td>
	</tr>
	</table>
</div>