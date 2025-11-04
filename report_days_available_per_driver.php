<? include('application.php') ?>
<? include('header.php') ?>
<div style='text-align:left;margin:10px;width:920px;float:left' class='admin_menu1'>
	<div class='section_heading'>Days Available Report - Per Driver</div>
    <!----
	Enter any date for the month you would like to build the days available report for. <br>
	For example, if you wanted <?=date("M, Y")?>, select any date during that month <br>
	and the system will automatically generate the full month report.
	<br><b>Use the Show Historic Values checkbox to use the date as the end date (not the full month).</b>
    ----->
    <br><b>Range number is for the Days / Days Available Ratio to look for ratio percentages.</b>
	<?
		$_POST['mrr_misc_value_label']="Show Historic Values";				//label for misc checkbox so it can be used for anything		
	?>
	<div style='clear:both'></div>
	<?
		$rfilter = new report_filter();
		//$rfilter->show_date_range 		= false;
		//$rfilter->show_single_date 		= true;
		//$rfilter->mrr_show_misc_value		= true;
		$rfilter->mrr_show_num_range		= true;
		$rfilter->show_font_size			= true;
		$rfilter->show_filter();
	?>
	

	<div style='clear:both'></div>
	<table class='font_display_section' style='text-align:left;width:1100px;margin-left:0px'>
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
			
			$tot_otr=0;
			$tot_otr_hourly=0;
			
			$tot_miles=0;
			$tot_bonus=0;
			
			// get the list of active trucks
			$sql = "
				select *
				
				from drivers
				where deleted = 0
					and id!='405'	
					and active=1				

				order by active desc, name_driver_last asc,name_driver_first asc,id asc
			";
			$data_drivers_active = simple_query($sql);
		
			for($i=0;$i < $days_in_month;$i++) {
				$day_of_week = date("w", strtotime("$i day", $date_start));
				if($day_of_week != 0 && $day_of_week != 6) {
					if(strtotime("$i day", $date_start) <= $date_end) $billable_days_so_far++;
					$billable_days++;
				}
				
			}
						
			$gres=get_days_available($date_start, $date_end, 0);	//(int) $defaultsarray['billable_days_in_month'];
			$truck_billable_days=$gres['billable_days'];
			
			echo "
				<table class='admin_menu2' style='width:900px;'>
				<tr>
					<td></td>
					<td><b>Driver</b></td>
					<td><b>Dispatch</b></td>
					<td><b>Starting Truck</b></td>					
					<td><b>Ending Truck <span class='alert'>(if different)</span></b></td>
					<td align='right'><b>Miles</b></td>
					<td align='right'><b>OTR</b></td>
					<td align='right'><b>Hourly</b></td>
					<td align='right'><b>Available</b></td>
					<td align='right'><b>Used</b></td>
					<td align='right'><b>Ratio</b></td>
					<td align='right'><b>Bonus</b></td>
				</tr>
			";
			
			$drivers_id_array = array();
			while($row_drivers = mysqli_fetch_array($data_drivers_active)) 
			{					
				$driver_namer="".$row_drivers['name_driver_first']." ".$row_drivers['name_driver_last']."";
				
				$drivers_id_array[]=$row_drivers['id'];
				$otr=0;
				$otr_hourly=0;
				
				//Get trucks for the driver...first and last so it is easy to see if it changed during the month...(unless it changed back).
				$first_truck_id=0;		$first_truck_name="";
				$last_truck_id=0;		$last_truck_name="";
				
				$team_runs=0;		
				
				$cur_miles=0;
				
				$dcntr=0;
				$changed=0;
					//(trucks_log.hours_worked / ".(int) $defaultsarray['local_driver_workweek_hours'].") as day_run_hourly,
				$sql = "
					select truck_id,
						miles,
						miles_deadhead,
						daily_run_otr,
						(daily_run_hourly) as day_run_hourly,
						(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_name,
						driver_id,
						driver2_id				
					from trucks_log
						left join load_handler on trucks_log.load_handler_id = load_handler.id
					where trucks_log.deleted = 0
						and load_handler.deleted = 0
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') >= '".date("Y-m-d", $date_start)."'
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') <= '".date("Y-m-d", $date_end)."'
						and (trucks_log.driver_id = '$row_drivers[id]' or trucks_log.driver2_id = '$row_drivers[id]')
					order by trucks_log.linedate_pickup_eta asc
				";
				$data_trucks_mrr = simple_query($sql);
				while($row_trucks_mrr = mysqli_fetch_array($data_trucks_mrr))
				{					
					$team_runs=0;	
					
					$cur_miles+=($row_trucks_mrr['miles'] + $row_trucks_mrr['miles_deadhead']);	
					
					if($dcntr==0 || $first_truck_id==0)
					{
						$first_truck_id=$row_trucks_mrr['truck_id'];				$first_truck_name=$row_trucks_mrr['truck_name'];
					}
					$last_truck_id=$row_trucks_mrr['truck_id'];					$last_truck_name=$row_trucks_mrr['truck_name'];
										
					if($first_truck_id!=$last_truck_id && $first_truck_id > 0 && $last_truck_id > 0)		$changed++;
					
					
					if($row_trucks_mrr['driver2_id'] > 0)		$team_runs=1;		//this was a team run, so split the thing in half...
					
					if($team_runs > 0)
     				{
     					//$otr+= ($row_trucks_mrr['daily_run_otr']/2);
						//$otr_hourly+= ($row_trucks_mrr['day_run_hourly']/2);
						
						$otr+=$row_trucks_mrr['daily_run_otr'];
						$otr_hourly+=$row_trucks_mrr['day_run_hourly'];
     				}
     				else
     				{
     					$otr+=$row_trucks_mrr['daily_run_otr'];
						$otr_hourly+=$row_trucks_mrr['day_run_hourly'];
     				}
														
					$dcntr++;
				}						
				$first_driver_name=mrr_get_driver_name($first_driver_id);		$first_driver2_name=mrr_get_driver_name($first_driver2_id);
				$last_driver_name=mrr_get_driver_name($last_driver_id);		$last_driver2_name=mrr_get_driver_name($last_driver2_id);				
				if($first_truck_id!=$last_truck_id || $changed > 0)
				{	//
					$last_truck_name="<span class='alert'><b>".$last_truck_name."</b></span>";
				}				
				
				$days_actual=$otr + $otr_hourly;
				
				
								
				// only show the trucks that had some billable days available
				if($truck_billable_days > 0 && $first_truck_id > 0) 
				{						
					$days_display=$days_actual;
					
					if($days_display == 0) {
						$ratio = 0;
					} else {
						$ratio = number_format($days_display / $truck_billable_days * 100);
					}
					
					$show_it=0;
					if($_POST['mrr_show_num_range_min']==0 && $_POST['mrr_show_num_range_max']==0)
					{
						$show_it=1;		//show all
					}
					elseif($ratio >= $_POST['mrr_show_num_range_min'] && $ratio <= $_POST['mrr_show_num_range_max'])
					{
						$show_it=1;		//show only those in range...
					}
					
					if($show_it > 0)
					{     						
     					$tot_otr+=$otr;
     					$tot_otr_hourly+=$otr_hourly;
     					$total_used+=($otr + $otr_hourly);
     					
     					$total_available+=$truck_billable_days;
     					
     					$tot_miles+=$cur_miles;
     					$bonus_cash=($cur_miles * 0.03);
     					if($ratio>=100)		$tot_bonus+=$bonus_cash;
     					
     					echo "
     						<tr>
     							<td><a href='admin_drivers.php?id=$row_drivers[id]' target='view_driver_$row_drivers[id]'>view driver</a></td>
     							<td><a href='report_sales_by_driver.php?date_from=".date("m/d/Y", $date_start)."&date_to=".date("m/d/Y", $date_end)."&driver_id=$row_drivers[id]' target='view_driver_report_$row_drivers[id]'>".$driver_namer."</a></td>
     							<td><a href='report_dispatch.php?date_from=".date("m/d/Y", $date_start)."&date_to=".date("m/d/Y", $date_end)."&driver_id=$row_drivers[id]' target='view_driver_report2_$row_drivers[id]'>Report</a></td>
     							<td><a href='admin_trucks.php?id=".$first_truck_id."' target='view_truck_".$first_truck_id."'>".$first_truck_name."</a></td>
     							<td><a href='admin_trucks.php?id=".$last_truck_id."' target='view_truck_".$last_truck_id."'>".$last_truck_name."</a></td>							
     							<td align='right'>$cur_miles</td>	
     							<td align='right'>$otr</td>	
     							<td align='right'>$otr_hourly</td>	
     							<td align='right'>$truck_billable_days</td>							
     							<td align='right'>".((int)$days_display != $days_display ? number_format($days_display,2) : number_format($days_display,0))."</td>
     							<td align='right'>$ratio%</td>
     							<td align='right'>$".($ratio>=100 ? number_format($bonus_cash,2) : "0.00")."</td>	
     						</tr>						
     					";	//<tr><td colspan='7'>".$sql."</td></tr>
     				}
				}
			}
			
			//$drivers2_id_array=$drivers_id_array;
			if(count($drivers_id_array) && 1==2) {
				
				// see if there are any unaccounted for days (i.e. from deleted trucks)
					//sum(trucks_log.hours_worked / $defaultsarray[local_driver_workweek_hours]) as days_run_hourly
				$sql = "
					select 
						sum(trucks_log.miles + miles_deadhead) as days_miles,
						sum(trucks_log.daily_run_otr) as days_run_otr,
						sum(trucks_log.daily_run_hourly) as days_run_hourly
					
					from trucks_log, load_handler
					where trucks_log.deleted = 0
						and load_handler.deleted = 0
						and trucks_log.load_handler_id = load_handler.id
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') >= '".date("Y-m-d", $date_start)."'
						and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') <= '".date("Y-m-d", $date_end)."'
						and trucks_log.driver_id > 0
						and trucks_log.driver_id not in (".implode(",", $drivers_id_array).")		
						and (
							(trucks_log.driver2_id > 0 and trucks_log.driver2_id not in (0,".implode(",", $drivers_id_array).")	)
							or
							trucks_log.driver2_id=0
							)								
				";
				$data_actual = simple_query($sql);
				$row_actual = mysqli_fetch_array($data_actual);
				$days_actual = $row_actual['days_run_otr'];
				$days_actual += $row_actual['days_run_hourly'];
				
				$days_display=$days_actual;
					
				if($days_display == 0) {
					$ratio = 0;
				} else {
					$ratio = number_format($days_display / $truck_billable_days * 100);
				}
				
				$show_it=0;
				if($_POST['mrr_show_num_range_min']==0 && $_POST['mrr_show_num_range_max']==0)
				{
					$show_it=1;		//show all
				}
				elseif($ratio >= $_POST['mrr_show_num_range_min'] && $ratio <= $_POST['mrr_show_num_range_max'])
				{
					$show_it=1;		//show only those in range...
				}
				
				if($show_it > 0)
				{
     				$tot_otr+=$row_actual['days_run_otr'];
     				$tot_otr_hourly+=$row_actual['days_run_hourly'];
     				
     				$total_used += $days_actual;
     				
     				$tot_miles+= $row_actual['days_miles'];
     				$tot_bonus+=0;
     			
     				echo "
     					<tr>
     						<td>&nbsp;</td>
     						<td colspan='4'>Other (deleted drivers)</td>
     						<td align='right'>".$row_actual['days_miles']."</td>
     						<td align='right'>".$row_actual['days_run_otr']."</td>
     						<td align='right'>".$row_actual['days_run_hourly']."</td>
     						<td align='right'>&nbsp;</td>
     						<td align='right'>".((int)$days_actual != $days_actual ? number_format($days_actual,2) : number_format($days_actual,0))."</td>
     						<td align='right'>$ratio%</td>
     						<td align='right'>&nbsp;</td>
     					</tr>
     				";
				}
			}
			
			if(date("m/d/Y", $date_end) == date("m/d/Y")) {
				/*
				echo "
					<tr>
						<td colspan='9' align='right'>As of yesterday</td>						
						<td align='right'>$total_available_yesterday</td>
						<td align='right'>$total_used_yesterday</td>
						<td align='right'>".($total_available_yesterday > 0 && $total_used_yesterday > 0 ? number_format($total_used_yesterday / $total_available_yesterday * 100): "0") ."%</td>
					</tr>
				";
				*/
			}			
			echo "
				<tr>
					<td colspan='12'><hr></td>
				</tr>
				<tr>
					<td colspan='5' align='right'></td>
					<td align='right'>$tot_miles</td>
					<td align='right'>$tot_otr</td>
					<td align='right'>$tot_otr_hourly</td>
					<td align='right'>$total_available</td>
					<td align='right'>$total_used</td>
					<td align='right'>".($total_available > 0 && $total_used > 0 ? number_format($total_used / $total_available * 100): "0") ."%</td>
					<td align='right'>$".number_format($tot_bonus,2)."</td>
				</tr>
			";			
			echo "</table>";

		}
	?>
		</td>
	</tr>
	</table>
</div>