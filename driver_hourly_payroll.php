<? include('application.php') ?>
<?

	$rfilter = new report_filter();
	$rfilter->handle_quick_dates();

	if(isset($_GET['report_date']) && !isset($_POST['report_date'])) {
		$_POST['build_report'] = 1;
		$_POST['date_from'] = $_GET['date_from'];
		$_POST['date_to'] = $_GET['date_to'];
	}

	if(isset($_POST['build_report'])) {
		/*
		$day_of_week = date("w", strtotime($_POST['report_date']));
		
		$date_start = strtotime("-$day_of_week day", strtotime($_POST['report_date']));
		$date_end = strtotime("6 day", $date_start);
		
		// we split the weeks to make sure they're in the current month, so if the end of one month, and the start of the next occurs during the week
		// then change the start/end date to stay within the selected month
		if(date("m", $date_start) != date("m", strtotime($_POST['report_date']))) $date_start = strtotime(date("m/1/Y", strtotime($_POST['report_date'])));
		if(date("m", $date_end) != date("m", strtotime($_POST['report_date']))) $date_end = strtotime(date("m/t/Y", strtotime($_POST['report_date'])));
		*/
		$date_start = strtotime($_POST['date_from']);
		$date_end = strtotime($_POST['date_to']);
		
		//die($_POST['date_from']);
	}

	if(isset($_POST['id_list'])) 
	{
		echo "<br><br><br><br>";
		foreach($_POST['id_list'] as $driver_id) 
		{
			$days_charged = $_POST['days_charged_'.$driver_id];
			$days_logged = $_POST['days_logged_'.$driver_id];
			if($days_charged > 0 && $days_charged != $days_logged) 
			{
				$days_difference = $days_charged - $days_logged;
				
				// get the list of dispatches that could be updated
				$sql = "
					select trucks_log.id,trucks_log.load_handler_id
					
					from trucks_log
						left join trucks on trucks.id=trucks_log.truck_id
					where trucks_log.deleted = 0
						and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
						and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))."'
						and trucks.owner_operated=0
						and (
							trucks_log.driver_id = '".sql_friendly($driver_id)."'
							or trucks_log.driver2_id = '".sql_friendly($driver_id)."'
						)
				";
				$data_log = simple_query($sql);
				
				$per_dispatch = $days_difference / mysqli_num_rows($data_log);
				
				// we need to loop through in quarter hour increments until we get a number less than the number of dispatches
				// then we can use that to update our dispatch list
				if($days_difference < 0) 
				{
					$decrement_loop = true;
					for($i=1;$i<1000;$i++) 
					{
						$increment_amount = $i * -.25;
						if($increment_amount <= $per_dispatch) 		break;
					}
				} 
				else 
				{
					$decrement_loop = false;
					for($i=1;$i<1000;$i++) 
					{
						$increment_amount = $i * .25;
						if($increment_amount >= $per_dispatch) 		break;
					}
				}
				
				//echo "Difference: $days_difference<br>increment_amount: $increment_amount";				
				//die;
				
				/*
				echo "
					Driver: $driver_id: $hours_difference<br>
					Dispatches: ".mysqli_num_rows($data_log)."<br>
					Per Dispatch: $increment_amount<br>
					<p>
				";
				*/
				
				if($days_difference != 0) 
				{					
					// loop through all the dispatches, and add our increment amount
					while($row_log = mysqli_fetch_array($data_log)) 
					{
						$load_id=$row_log['load_handler_id'];
						$sql = "
							update trucks_log set
								daily_run_hourly = daily_run_hourly + $increment_amount
							where id = '$row_log[id]'
							limit 1
						";
						//echo "$sql<p>";
						simple_query($sql);
						
						if($decrement_loop) 
						{
							$days_difference -= $increment_amount;
							if($days_difference > $increment_amount) 	$increment_amount = $days_difference;
							if($days_difference >= 0) 				break;
						} 
						else 
						{
							$days_difference -= $increment_amount;
							if($days_difference < $increment_amount) 	$increment_amount = $days_difference;
							if($days_difference <= 0) 				break;
						}
												
						//now update the cost...Added July 2015................................................................
						$dispatch_cost = get_dispatch_cost($row_log['id']);               			
               			$sqlu = "update trucks_log set cost='".$dispatch_cost."' where id='".sql_friendly($row_log['id'])."'";
               			simple_query($sqlu);	
               			
               			//also flag the load to be updated...since cost is now different...added Feb 2016...MRR
						$sqlu2 = "update load_handler set auto_save_requested=1 where id = '".sql_friendly($load_id)."'";
               			simple_query($sqlu2);               			
						//.....................................................................................................
					}
				}
			}
			
			$hours_charged = $_POST['hours_charged_'.$driver_id];
			$hours_logged = $_POST['hours_logged_'.$driver_id];
			$skip_entry = false;
			
			if($hours_charged == '') 		$skip_entry = true;

			if($hours_charged != $hours_logged && !$skip_entry) 
			{
				$hours_difference = $hours_charged - $hours_logged;
				
				// get the list of dispatches that could be updated
				$sql = "
					select trucks_log.id
					
					from trucks_log
						left join trucks on trucks.id=trucks_log.truck_id
					where trucks_log.deleted = 0
						and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
						and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))."'
						and trucks.owner_operated=0
						and (
							trucks_log.driver_id = '$driver_id'
							or trucks_log.driver2_id = '$driver_id'
						)
				";
				$data_log = simple_query($sql);
				
				$per_dispatch = $hours_difference / mysqli_num_rows($data_log);
				
				// we need to loop through in quarter hour increments until we get a number less than the number of dispatches
				// then we can use that to update our dispatch list
				if($hours_difference < 0) 
				{
					$decrement_loop = true;
					for($i=1;$i<1000;$i++) 
					{
						$increment_amount = $i * -.25;
						if($increment_amount <= $per_dispatch) break;
					}
				} 
				else 
				{
					$decrement_loop = false;
					for($i=1;$i<1000;$i++) 
					{
						$increment_amount = $i * .25;
						if($increment_amount >= $per_dispatch) break;
					}
				}				
				
				/*
				echo "
					Driver: $driver_id: $hours_difference<br>
					Dispatches: ".mysqli_num_rows($data_log)."<br>
					Per Dispatch: $increment_amount<br>
					<p>
				";
				*/
				
				if($hours_difference != 0) 
				{
					// loop through all the dispatches, and add our increment amount
					while($row_log = mysqli_fetch_array($data_log)) 
					{	
						$sql = "
							update trucks_log
							set hours_worked = hours_worked + $increment_amount
							where id = '$row_log[id]'
							limit 1
						";
						//echo "$sql<p>";
						simple_query($sql);
						
						if($decrement_loop) 
						{
							$hours_difference -= $increment_amount;
							if($hours_difference > $increment_amount) 	$increment_amount = $hours_difference;
							if($hours_difference >= 0) 				break;
						} 
						else 
						{
							$hours_difference -= $increment_amount;
							if($hours_difference < $increment_amount) 	$increment_amount = $hours_difference;
							if($hours_difference <= 0) 				break;
						}
						
						//now update the cost...Added July 2015................................................................
						$dispatch_cost = get_dispatch_cost($row_log['id']);               			
               			$sqlu = "update trucks_log set cost='".$dispatch_cost."' where id='".sql_friendly($row_log['id'])."'";
               			simple_query($sqlu);	
						//.....................................................................................................
					}
				}
			}
		}
		/*
		header("Location: driver_hourly_payroll.php?date_from=$_POST[date_from]&date_to=$_POST[date_to]");
		die;
		*/
	}
$usetitle = "Driver - Hourly Payroll";
$use_title = 'Driver - Hourly Payroll';
$use_bootstrap = true;
?>
<? include('header.php') ?>
<div class='container col-md-12'>
	<div class='col-md-6'>
		
		<div class="panel panel-primary">
			<div class="panel-heading">Driver - Hourly Payroll</div>
			  <div class="panel-body">
			  	<p>
			  	Enter any date for the week you would like to pull up the driver hourly payroll report for. <br>
				For example, if you wanted <?=date("M j, Y")?>, select any date during that week. <br>	
			  	</p>
			  	<p>
			  	<?
			  	$rfilter->show_date_range 		= true;
				$rfilter->show_single_date 		= false;
				$rfilter->show_filter();
			  	?>
			  	</p>
			  	
			  	<? if(isset($_POST['build_report'])) { ?>			  	
			  	
			  		<table class='table well table-bordered' width='100%'>
			  		<thead>
     				<tr>
     					<th>Date Start</th>
     					<th>Date End</th>
     				</tr>
     				</thead>
     				<tbody>			  		
			  		<tr>
						<td><?=date("m/d/Y", $date_start) ?></td>
						<td><?=date("m/d/Y", $date_end) ?></td>
					</tr>
					</tbody>
					</table>
					
					<br><br>
					
					<form action='' method='post'>
						
					<input type='hidden' name='date_from' value='<?=$_POST['date_from'] ?>'>
               		<input type='hidden' name='date_to' value='<?=$_POST['date_to'] ?>'>
               		<input type='hidden' name='build_report' value='1'>
               		
               		<table class='table well table-bordered' width='100%'>
               		<thead>
               		<tr>
               			<th>Driver</th>
               			<th>Dispatches</th>
               			<th>Days</th>
               			<th>Hours Logged</th>
               			<th>Carlex Hours</th>
               			<th>Hours Adjusted</th>
               			<th>Hours Charged</th>
               			<th>Days<br><i>Disabled</i></th>
               			<th>&nbsp;</th>
               		</tr>
               		</thead>
               		<tbody>	
               		<?
               			// get a list of drivers for this date range
               			$sql = "
               				select driver_id,
               					drivers.name_driver_first,
               					drivers.name_driver_last
               				
               				from trucks_log
               					inner join drivers on drivers.id = trucks_log.driver_id
               				where trucks_log.deleted = 0
               					and linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
               					and linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))."'
               				
               				union 
               				
               				select driver2_id,
               					drivers.name_driver_first,
               					drivers.name_driver_last
               				
               				from trucks_log
               					inner join drivers on drivers.id = trucks_log.driver2_id
               					
               				where trucks_log.deleted = 0
               					and driver2_id > 0
               					and linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
               					and linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))."'
               				order by name_driver_last, name_driver_first
               			";
               			$data = simple_query($sql);
               			//echo $sql;               			
               			//echo "";
               			
               			$driver_array = array();
               			$counter = 0;
               			while($row = mysqli_fetch_array($data)) 
               			{
               				$counter++;
               				if(!in_array($row['driver_id'], $driver_array)) 
               				{
               					$driver_array[] = $row['driver_id'];               					
               					
               					// get the total hours this driver worked (according to our system)
               					$sql = "
               						select ifnull(sum(trucks_log.hours_worked),0) as hours_worked,
               							count(*) as dispatch_count,
               							ifnull(sum(trucks_log.daily_run_hourly),0) as days_run
               						
               						from trucks_log
               							left join trucks on trucks.id=trucks_log.truck_id
               						where trucks_log.deleted = 0
               							and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
               							and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))."'
               							and trucks.owner_operated=0
               							and (
               								trucks_log.driver_id = '$row[driver_id]'
               								or trucks_log.driver2_id = '$row[driver_id]'
               							)
               					";
               					
               					$data_hours = simple_query($sql);
               					$row_hours = mysqli_fetch_array($data_hours);
               					
               					$buttonizer="";
               					if($counter % 5 == 1)
               					{
               						$buttonizer="
               							<button type='submit' class='btn btn-primary'><span class='glyphicon glyphicon-floppy-disk'></span> Update</button>
               						";	//<input type='submit' value='Update'>
               					}
               					
               					$cres=mrr_find_total_timesheet_hours($_POST['date_from'],$_POST['date_to'],$row['driver_id']);
               					$carlex_hrs=$cres['hours'];
               					$hrs_adjust=$row_hours['hours_worked'] + $carlex_hrs;
               					
               					echo "
               						<tr class='row_hover_highlight'>
               							<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
               							<td align='right'>$row_hours[dispatch_count]</td>
               							<td nowrap align='right'>".($row_hours['days_run'] == 0 ? "<span class='show_inactive'>" : "<span>")."$row_hours[days_run]</span></td>
               							<td nowrap align='right'>".($hrs_adjust == 0 ? "<span class='show_inactive'>" : "<span>")."$row_hours[hours_worked]</span></td>
               							
               							<td nowrap align='right'>".($hrs_adjust == 0 ? "<span class='show_inactive'>" : "<span>")."".$carlex_hrs."</span></td>
               							<td nowrap align='right'>".($hrs_adjust == 0 ? "<span class='show_inactive'>" : "<span>")."".$hrs_adjust."</span></td>
               							               							
               							<td align='right'>
               								<input name='hours_charged_$row[driver_id]' class='form-control' id='hours_charged_$row[driver_id]'>
               								<input type='hidden' name='id_list[]' value='$row[driver_id]'>
               								<input type='hidden' name='hours_logged_$row[driver_id]' value='$row_hours[hours_worked]'>
               							</td>
               							<td align='right'>
               								<input name='days_charged_$row[driver_id]' class='form-control' id='days_charged_$row[driver_id]' disabled>
               								<input type='hidden' name='days_logged_$row[driver_id]' value='$row_hours[days_run]'>
               							</td>
               							<td>".$buttonizer."</td>
               						</tr>
               					";
               					//if($row['driver_id']==476)	echo "<tr><td colspan='9'>".$cres['sql']."</td></span>";	
               				}
               			}
               		?>
               		</tbody>
               		</table>
               		</form>
			  	
				<? } ?>
			  	
			  </div>
				
	</div>
	<div class='col-md-6'>
		&nbsp;
	</div>
</div>
<div style='clear:both;'></div>
<? include('footer.php') ?>