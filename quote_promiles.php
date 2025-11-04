<? include('header.php') ?>
<?

	if(isset($_GET['del'])) {
		$sql = "
			update quotes
			set deleted = 1
			where id = '".sql_friendly($_GET['del'])."'
		";
		simple_query($sql);
	}

	if(isset($_POST['build_report'])) {
		if(!isset($_GET['id'])) {
			$sql = "
				insert into quotes
					(created_by_user_id,
					deleted,
					linedate_added)
					
				values ('".sql_friendly($_SESSION['user_id'])."',
					0,
					now())
			";
			simple_query($sql);
			$_GET['id'] = mysqli_insert_id($datasource);
		}
		
		
		$sql = "
			update quotes
			set customer_id = '".sql_friendly($_POST['customer_id'])."',
				driver_id = '".sql_friendly($_POST['driver_id'])."',
				trailer_id = '".sql_friendly($_POST['trailer_id'])."',
				truck_id = '".sql_friendly($_POST['truck_id'])."',
				linedate = '".date("Y-m-d", strtotime($_POST['linedate']))."',
				quote_name = '".sql_friendly($_POST['quote_name'])."',
				quote_notes = '".sql_friendly($_POST['quote_notes'])."',
				days_run_otr = '".sql_friendly(money_strip($_POST['days_run_otr']))."',
				days_run_hourly = '".sql_friendly(money_strip($_POST['days_run_hourly']))."',
				miles_loaded = '".sql_friendly(money_strip($_POST['loaded_miles']))."',
				miles_pcm = '".sql_friendly(money_strip($_POST['pcm_loaded_miles']))."',
				miles_deadhead = '".sql_friendly(money_strip($_POST['deadhead_miles']))."',
				miles_hourly = '".sql_friendly(money_strip($_POST['loaded_miles_hourly']))."',
				hours_worked = '".sql_friendly(money_strip($_POST['hours_worked']))."',
				bill_customer = '".sql_friendly(money_strip($_POST['bill_customer']))."',
				fuel_avg = '".sql_friendly(money_strip($_POST['doe_fuel_avg']))."',
				daily_cost = '".sql_friendly(money_strip($_POST['daily_cost']))."',
				average_mpg = '".sql_friendly(money_strip($_POST['avg_mpg']))."',
				labor_per_mile = '".sql_friendly(money_strip($_POST['labor_per_mile']))."',
				labor_per_hour = '".sql_friendly(money_strip($_POST['labor_per_hour']))."',
				maint_per_mile_tractor = '".sql_friendly(money_strip($_POST['tractor_maint_per_mile']))."',
				maint_per_mile_trailer = '".sql_friendly(money_strip($_POST['trailer_maint_per_mile']))."',
				total_cost = '".sql_friendly(money_strip($_POST['total_cost']))."',
				profit = '".sql_friendly(money_strip($_POST['profit']))."',
				team_driver = '".sql_friendly($_POST['team_drivers'])."',
				load_taken = '".(isset($_POST['load_taken']) ? '1' : '0')."',				
				tires_per_mile = '".sql_friendly(money_strip($_POST['tires_per_mile']))."',
				accidents_per_mile = '".sql_friendly(money_strip($_POST['accidents_per_mile']))."',
				mile_exp_per_mile = '".sql_friendly(money_strip($_POST['mile_exp_per_mile']))."',
				misc_per_mile = '".sql_friendly(money_strip($_POST['misc_per_mile']))."',
				linedate_expires = '".date("Y-m-d", strtotime($_POST['expire_date']))."'
				
			where id = '".sql_friendly($_GET['id'])."'
		";
		simple_query($sql);
		
		$quote_id = $_GET['id'];
		
		$sql = "
			delete from quotes_stops
			where quote_id = '".sql_friendly($_GET['id'])."'
		";
		simple_query($sql);
		
		$sql = "
			delete from quotes_expenses
			where quote_id = '".sql_friendly($_GET['id'])."'
		";
		simple_query($sql);
		
		$stop_counter = 0;
		for($i=1;$i < 9999;$i++) {
			if(!isset($_POST['local_'.$i])) break;
			if($_POST['local_'.$i] != '') {
				$stop_counter++;
				$sql = "
					insert into quotes_stops
						(quote_id,
						deleted,
						stop_location,
						stop_order_id,
						stop_city,
						stop_state)
						
					values ('$quote_id',
						0,
						'00000',
						$stop_counter,
						'".sql_friendly($_POST['local_'.$i.''])."',
						'')
				";
				simple_query($sql);
			}
		}
		
		foreach($_POST['variable_id_list'] as $variable_id) {
			$sql = "
				insert into quotes_expenses
					(quote_id,
					expense_type_id,
					amount)
					
				values ('$quote_id',
					'".sql_friendly($variable_id)."',
					'".sql_friendly(money_strip($_POST['variable_'.$variable_id]))."')
			";
			simple_query($sql);
		}
	}

	if(isset($_GET['id'])) {
		$sql = "
			select *
			
			from quotes
			where id = '".sql_friendly($_GET['id'])."'
		";
		$data_quote = simple_query($sql);
		$row = mysqli_fetch_array($data_quote);
		
		// get the stops
		$sql = "
			select *
			
			from quotes_stops
			where quote_id = '".sql_friendly($row['id'])."'
				and deleted = 0
			order by stop_order_id
		";
		$data_stops = simple_query($sql);
		
		while($row_stop = mysqli_fetch_array($data_stops)) 
		{
			$_POST['local_'.$row_stop['stop_order_id'].''] = $row_stop['stop_city'];
			
			if(!isset($row_stop['stop_city']) || (isset($row_stop['stop_city']) && trim($row_stop['stop_city'])==""))
			{
				$_POST['stop_'.$row_stop['stop_order_id']] = $row_stop['stop_location'];	
			}			
		}
		
		// get the variable expenses
		$sql = "
			select *
			
			from quotes_expenses
			where quote_id = '".sql_friendly($row['id'])."'
		";
		$data_expenses = simple_query($sql);
		
		while($row_expense = mysqli_fetch_array($data_expenses)) 
		{
			$_POST['variable_'.$row_expense['expense_type_id']] = $row_expense['amount'];
		}
		
		$_POST['linedate'] = date("n/j/Y", strtotime($row['linedate']));
		$_POST['quote_name'] = $row['quote_name'];
		$_POST['bill_customer'] = $row['bill_customer'];
		$_POST['quote_notes'] = $row['quote_notes'];
		$_POST['customer_id'] = $row['customer_id'];
		$_POST['driver_id'] = $row['driver_id'];
		$_POST['trailer_id'] = $row['trailer_id'];
		$_POST['truck_id'] = $row['truck_id'];
		$_POST['days_run_otr'] = $row['days_run_otr'];
		$_POST['days_run_hourly'] = $row['days_run_hourly'];
		$_POST['loaded_miles'] = $row['miles_loaded'];
		$_POST['pcm_loaded_miles'] = $row['miles_pcm'];
		$_POST['deadhead_miles'] = $row['miles_deadhead'];
		$_POST['loaded_miles_hourly'] = $row['miles_hourly'];
		$_POST['hours_worked'] = $row['hours_worked'];
		$_POST['trailer_maint_per_mile'] = $row['maint_per_mile_trailer'];
		$_POST['tractor_maint_per_mile'] = $row['maint_per_mile_tractor'];
		$_POST['avg_mpg'] = $row['average_mpg'];
		$_POST['doe_fuel_avg'] = $row['fuel_avg'];
		$_POST['daily_cost'] = $row['daily_cost'];
		$_POST['labor_per_mile'] = $row['labor_per_mile'];
		$_POST['labor_per_hour'] = $row['labor_per_hour'];
		$_POST['team_drivers'] = $row['team_driver'];
		$_POST['load_taken'] = $row['load_taken'];
		$_POST['expire_date'] = date("n/j/Y", strtotime($row['linedate_expires']));
		$_POST['tires_per_mile']=$row['tires_per_mile'];
		$_POST['accidents_per_mile']=$row['accidents_per_mile'];
		$_POST['mile_exp_per_mile']=$row['mile_exp_per_mile'];
		$_POST['misc_per_mile']=$row['misc_per_mile'];
		$_POST['quote_emailer']=mrr_get_customer_id_email($row['customer_id']);
	}

	if(isset($_GET['driver_id']) && isset($_GET['truck_id']) && isset($_GET['trailer_id']))
	{
		if(!isset($_POST['driver_id']))	$_POST['driver_id'] = $_GET['driver_id'];
		if(!isset($_POST['truck_id']))	$_POST['truck_id'] = $_GET['truck_id'];
		if(!isset($_POST['trailer_id']))	$_POST['trailer_id'] = $_GET['trailer_id'];
		
		$_POST['build_report'] = 1;
	}
	elseif(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}

	if(!isset($_POST['linedate'])) $_POST['linedate'] = date("n/j/Y", time());
	if(!isset($_POST['driver_id'])) $_POST['driver_id'] = 0;
	if(!isset($_POST['trailer_id'])) $_POST['trailer_id'] = 0;
	if(!isset($_POST['truck_id'])) $_POST['truck_id'] = 0;
	if(!isset($_POST['customer_id'])) $_POST['customer_id'] = 0;
	if(!isset($_POST['bill_customer'])) $_POST['bill_customer'] = 0;
	if(!isset($_POST['quote_name'])) $_POST['quote_name'] = '';
	if(!isset($_POST['quote_notes'])) $_POST['quote_notes'] = '';
	if(!isset($_POST['days_run_otr'])) $_POST['days_run_otr'] = '';
	if(!isset($_POST['days_run_hourly'])) $_POST['days_run_hourly'] = '';
	if(!isset($_POST['loaded_miles'])) $_POST['loaded_miles'] = 0;
	if(!isset($_POST['pcm_loaded_miles'])) $_POST['pcm_loaded_miles'] = '';
	if(!isset($_POST['deadhead_miles'])) $_POST['deadhead_miles'] = '';
	if(!isset($_POST['loaded_miles_hourly'])) $_POST['loaded_miles_hourly'] = '';
	if(!isset($_POST['hours_worked'])) $_POST['hours_worked'] = '';	
	if(!isset($_POST['trailer_maint_per_mile'])) $_POST['trailer_maint_per_mile'] = $defaultsarray['trailer_maint_per_mile'];
	if(!isset($_POST['tractor_maint_per_mile'])) $_POST['tractor_maint_per_mile'] = $defaultsarray['tractor_maint_per_mile'];
	if(!isset($_POST['avg_mpg'])) $_POST['avg_mpg'] = $defaultsarray['average_mpg'];
	if(!isset($_POST['doe_fuel_avg'])) $_POST['doe_fuel_avg'] = $defaultsarray['fuel_surcharge'];
	if(!isset($_POST['daily_cost'])) $_POST['daily_cost'] = get_daily_cost();
	if(!isset($_POST['fuel_per_mile'])) $_POST['fuel_per_mile'] = '';
	if(!isset($_POST['labor_per_mile'])) $_POST['labor_per_mile'] = $defaultsarray['labor_per_mile'];
	if(!isset($_POST['labor_per_hour'])) $_POST['labor_per_hour'] = $defaultsarray['labor_per_hour'];
	if(!isset($_POST['team_drivers'])) $_POST['team_drivers'] = 0;
	if(!isset($_POST['load_taken'])) $_POST['load_taken'] = 0;
	if(!isset($_POST['expire_date'])) $_POST['expire_date'] = date("n/j/Y", time());
	if(!isset($_POST['quote_emailer'])) $_POST['quote_emailer'] = '';	
	
	if(!isset($_POST['tires_per_mile']))  		$_POST['tires_per_mile']= $defaultsarray['tires_per_mile'];
	if(!isset($_POST['accidents_per_mile']))  	$_POST['accidents_per_mile']= $defaultsarray['truck_accidents_per_mile'];
	if(!isset($_POST['mile_exp_per_mile']))  	$_POST['mile_exp_per_mile']= $defaultsarray['mileage_expense_per_mile'];
	if(!isset($_POST['misc_per_mile']))  		$_POST['misc_per_mile']= $defaultsarray['misc_expense_per_mile'];
	
	/* get the driver list */
	$sql = "
		select *
		
		from drivers
		where deleted = 0
		order by active desc, name_driver_last, name_driver_first
	";
	$data_drivers = simple_query($sql);
	
	/* get the customer list */
	$sql = "
		select *
		
		from customers
		where deleted = 0
		order by name_company
	";
	$data_customers = simple_query($sql);
	
	/* get the traier list */
	$sql = "
		select *
		
		from trailers
		where deleted = 0
		order by active desc, trailer_name
	";
	$data_trailers = simple_query($sql);
	
	/* get the truck list */
	$sql = "
		select *
		
		from trucks
		where deleted = 0
		order by active desc, name_truck
	";
	$data_trucks = simple_query($sql);
	
	// calculate our fixed expenses
	$sql = "
		select option_values.fvalue,
			option_values.fname,
			option_values.dummy_val
		
		from option_values, option_cat
			where option_cat.id = option_values.cat_id
			and option_cat.cat_name = 'fixed_expenses'
		order by option_values.fname
	";
	$data_expenses = simple_query($sql);
	
	// get a list of variable expenses the user can enter for the quote
	$sql = "
		select option_values.*
		
		from option_values, option_cat
		where option_values.cat_id = option_cat.id
			and option_cat.cat_name = 'expense_type'
			and option_cat.deleted = 0
			and option_values.deleted = 0
		order by option_values.zorder, option_values.fvalue
	";
	$data_expenses_variable = simple_query($sql);
?>

<form name='quote_form' action='' method='post'>
<input type='hidden' name='build_report' value='1'>
<table>
<tr>
	<td valign='top'>
		<table class='admin_menu1' style='margin:10px;text-align:left'>
		<tr>
			<td><label for='load_taken'>Load Taken</label></td>
			<td><input type='checkbox' name='load_taken' id='load_taken' <?=($_POST['load_taken'] == '1' ? 'checked' : "")?>> <b>PRO MILES VERSION</b></td>
		</tr>
		<tr>
			<td>Quote Name</td>
			<td><input name='quote_name' id='quote_name' style='width:300px' value="<?=$_POST['quote_name']?>"></td>
		</tr>
		<tr>
			<td>Notes</td>
			<td><textarea name='quote_notes' id='quote_notes' style='width:300px;height:75px'><?=$_POST['quote_notes']?></textarea></td>
		</tr>
		<tr>
			<td>Customer</td>
			<td>
				<select name='customer_id' id='customer_id'>
					<option value='0'>All Customers</option>
					<?
					while($row_customer = mysqli_fetch_array($data_customers)) { 
						echo "<option value='$row_customer[id]' ".($row_customer['id'] == $_POST['customer_id'] ? 'selected' : '').">$row_customer[name_company]</option>";
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Driver</td>
			<td>
				<select name='driver_id' id='driver_id'>
					<option value='0'>All Drivers</option>
					<?
					while($row_driver = mysqli_fetch_array($data_drivers)) { 
						echo "<option value='$row_driver[id]' ".($row_driver['id'] == $_POST['driver_id'] ? 'selected' : '').">".(!$row_driver['active'] ? '(inactive) ' : '')."$row_driver[name_driver_last], $row_driver[name_driver_first]</option>";
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Trailer</td>
			<td>
				<select name='trailer_id' id='trailer_id'>
					<option value='0'>All Trailers</option>
					<?
					while($row_trailer = mysqli_fetch_array($data_trailers)) { 
						echo "<option value='$row_trailer[id]' ".($row_trailer['id'] == $_POST['trailer_id'] ? 'selected' : '').">".(!$row_trailer['active'] ? '(inactive) ' : '')."$row_trailer[trailer_name]</option>";
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Truck</td>
			<td>
				<select name='truck_id' id='truck_id'>
					<option value='0'>All Trucks</option>
					<?
					while($row_truck = mysqli_fetch_array($data_trucks)) { 
						echo "<option value='$row_truck[id]' ".($row_truck['id'] == $_POST['truck_id'] ? 'selected' : '').">".(!$row_truck['active'] ? '(inactive) ' : '')."$row_truck[name_truck]</option>";
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan='4'><hr></td>
		</tr>
		<tr>
			<td>Team Drivers?</td>
			<td>
				<label><input type='radio' id='team_driver_no' name='team_drivers' value='0'<?= ($_POST['team_drivers'] == 0 ? " checked" : "") ?>> No</label>
				 / 
				 <label><input type='radio' id='team_driver_yes' name='team_drivers' value='1'<?= ($_POST['team_drivers'] > 0 ? " checked" : "") ?>> Yes</label>
			</td>
		</tr>
		<tr>
			<td>Date</td>
			<td><input name='linedate' id='linedate' value='<?=$_POST['linedate']?>'></td>
		</tr>
		<tr>
			<td>Quote Expires</td>
			<td><input name='expire_date' id='expire_date' value='<?=$_POST['expire_date']?>'>
				<div style='float:right'><span id='total_hours' style='font-weight:bold;font-size:14px'></span> Total hours</div>
			</td>
		</tr>
		<tr>
			<td colspan='2'><div id='mrr_msg'></div></td>
		</tr>
		<tr>
			<td colspan='2'>
				<table width='100%'>
				<tr>
					<td></td>
					<td>Location</td>
					<td>City</td>	
					<!--
					<td>Zip</td>
					
					<td>State</td>					
					-->
					<td align='right'>Miles</td>
					<td align='right'><span class='alert'>Total</span></td>
				</tr>
				<? for($i=1;$i < 11;$i++) { ?>
					<?
					$local_val="";	
					if(isset($_POST['local_'.$i.'']))
					{
						$local_val="".trim($_POST['local_'.$i.''])."";	
					}
					?>
					
					<tr>
						<td>Stop <?=$i?></td>
						<td><input name='local_<?=$i?>' id='local_<?=$i?>' value="<?= $local_val ?>" class='local_entry' style='width:200px;' line_number="<?= $i ?>" onChange='calc_run();'></td>
						<td><span class='data_disp' id='city_<?=$i?>'></span><input type='hidden' name='stop_<?=$i?>_city' id='stop_<?=$i?>_city' value="<?=(isset($_POST['stop_'.$i.'_city']) ? $_POST['stop_'.$i.'_city'] : '')?>"></td>	
						
						<!--	
						<td><input name='stop_<?=$i?>' id='stop_<?=$i?>' value="<?=(isset($_POST['stop_'.$i]) ? $_POST['stop_'.$i] : '')?>" class='stop_entry' style='width:50px' line_number="<?= $i ?>"></td>					
						<td><span class='data_disp' id='state_<?=$i?>'></span><input type='hidden' name='stop_<?=$i?>_state' id='stop_<?=$i?>_state' value="<?=(isset($_POST['stop_'.$i.'_state']) ? $_POST['stop_'.$i.'_state'] : '')?>"></td>		
						-->				
						<td align='right'><span class='data_disp' id='miles_<?=$i?>'></span></td>
						<td align='right'><span class='data_disp alert' id='total_<?=$i?>'></span></td>						
					</tr>
				<? } ?>
				</table>
			</td>
		</tr>
		<tr>
			<td>Days Run (OTR)</td>
			<td><input name='days_run_otr' id='days_run_otr' value='<?=$_POST['days_run_otr']?>'></td>
		</tr>
		<tr>
			<td>Days Run (Hourly)</td>
			<td><input name='days_run_hourly' id='days_run_hourly' value='<?=$_POST['days_run_hourly']?>' style=';background-color:#ffa569;'></td>
		</tr>
		<tr>
			<td>Loaded Miles</td>
			<td>
				<input name='loaded_miles' id='loaded_miles' value='<?=$_POST['loaded_miles']?>'>
				ProMiles <input name='pcm_loaded_miles' id='pcm_loaded_miles' value='<?=$_POST['pcm_loaded_miles']?>' style='width:100px;'>
			</td>
		</tr>
		<tr>
			<td>Deadhead Miles</td>
			<td><input name='deadhead_miles' id='deadhead_miles' value='<?=$_POST['deadhead_miles']?>' style='background-color:#ffff97;'></td>
		</tr>
		<tr>
			<td nowrap>Loaded Miles for Hourly (local work)</td>
			<td><input name='loaded_miles_hourly' id='loaded_miles_hourly' value='<?=$_POST['loaded_miles_hourly']?>'></td>
		</tr>
		<tr>
			<td>Hours Worked</td>
			<td><input name='hours_worked' id='hours_worked' value='<?=$_POST['hours_worked']?>'></td>
		</tr>
		<tr>
			<td colspan='2'><hr></td>
		</tr>
		<?
		while($row_expenses_variable = mysqli_fetch_array($data_expenses_variable)) {
			echo "
				<tr>
					<td>$row_expenses_variable[fvalue]</td>
					<td>
						<input name='variable_$row_expenses_variable[id]' id='variable_$row_expenses_variable[id]' value='".(isset($_POST['variable_'.$row_expenses_variable['id']]) ? $_POST['variable_'.$row_expenses_variable['id']] : '')."' class='variable_expenses'>
						<input type='hidden' name='variable_id_list[]' value='$row_expenses_variable[id]'>
					</td>
				</tr>
			";
		}
		?>
		<tr>
			<td colspan='2'><hr></td>
		</tr>
		<tr>
			<td>Bill Customer</td>
			<td><input name='bill_customer' id='bill_customer' value='<?=$_POST['bill_customer']?>' style='background-color:#8fff8f'></td>
		</tr>
		<!---
		<tr>
			<td></td>
			<td><input type='submit' value='Save Quote'></td>
		</tr>
		--->
		</table>
	</td>
	<td valign='top' style='text-align:left'>

		<table class='admin_menu2' style='margin-top:10px;text-align:left;' width='550'>
		<tr>
			<td>
				<table>
				<tr valign='top'>
					<td>DOE Nat. Avg.</td>
					<td><input name='doe_fuel_avg' id='doe_fuel_avg' style='text-align:right' class='xlocked'></td>
				</tr>
				<tr>
					<td>Avg MPG</td>
					<td><input name='avg_mpg' id='avg_mpg' style='text-align:right' class='xlocked'></td>
				</tr>
				<tr>
					<td><span onClick='mrr_see_daily_cost();'>Daily Cost</span></td>
					<td><input name='daily_cost' id='daily_cost' style='text-align:right' class='xlocked'></td>
				</tr>
				<tr>
					<td>Fuel Per Mile</td>
					<td>
						<input name='fuel_per_mile' id='fuel_per_mile' style='text-align:right;width:60px' class='xlocked'>
						<input name='fuel_total' id='fuel_total' style='text-align:right;width:80px' class='xlocked'>
					</td>
				</tr>
				<tr>
					<td>Labor Per Mile</td>
					<td>
						<input name='labor_per_mile' id='labor_per_mile' style='text-align:right;width:60px' class='xlocked'>
						<input name='labor_total' id='labor_total' style='text-align:right;width:80px' class='xlocked'>
					</td>
				</tr>
				<tr>
					<td>Labor Per Hour</td>
					<td>
						<input name='labor_per_hour' id='labor_per_hour' style='text-align:right;width:60px' class='xlocked_mrr'>
						<input name='labor_per_hour_total' id='labor_per_hour_total' style='text-align:right;width:80px' class='xlocked_mrr'>
					</td>
				</tr>
				<tr>
					<td nowrap>Tractor Maint Per Mile</td>
					<td>
						<input name='tractor_maint_per_mile' id='tractor_maint_per_mile' style='text-align:right;width:60px' class='xlocked'>
						<input name='tractor_maint_per_mile_total' id='tractor_maint_per_mile_total' style='text-align:right;width:80px' class='xlocked'>
					</td>
				</tr>
				<tr>
					<td>Trailer Maint Per Mile</td>
					<td>
						<input name='trailer_maint_per_mile' id='trailer_maint_per_mile' style='text-align:right;width:60px' class='xlocked'>
						<input name='trailer_maint_per_mile_total' id='trailer_maint_per_mile_total' style='text-align:right;width:80px' class='xlocked'>
					</td>
				</tr>
				<tr>
					<td>Tires Per Mile</td>
					<td>
						<input name='tires_per_mile' id='tires_per_mile' style='text-align:right;width:60px' class='xlocked'>
						<input name='tires_per_mile_total' id='tires_per_mile_total' style='text-align:right;width:80px' class='xlocked'>
					</td>
				</tr>
				<tr>
					<td>Accidents Per Mile</td>
					<td>
						<input name='accidents_per_mile' id='accidents_per_mile' style='text-align:right;width:60px' class='xlocked'>
						<input name='accidents_per_mile_total' id='accidents_per_mile_total' style='text-align:right;width:80px' class='xlocked'>
					</td>
				</tr>
				<tr>
					<td>Mileage Exp Per Mile</td>
					<td>
						<input name='mile_exp_per_mile' id='mile_exp_per_mile' style='text-align:right;width:60px' class='xlocked'>
						<input name='mile_exp_per_mile_total' id='mile_exp_per_mile_total' style='text-align:right;width:80px' class='xlocked'>
					</td>
				</tr>
				<tr>
					<td>Misc. Per Mile</td>
					<td>
						<input name='misc_per_mile' id='misc_per_mile' style='text-align:right;width:60px' class='xlocked'>
						<input name='misc_per_mile_total' id='misc_per_mile_total' style='text-align:right;width:80px' class='xlocked'>
					</td>
				</tr>
				
				<tr>
					<td>Total per mile</td>
					<td>
						<input name='total_per_mile' id='total_per_mile' style='text-align:right;width:60px' class='xlocked'>
						<input name='total_per_mile_total' id='total_per_mile_total' style='text-align:right;width:80px' class='xlocked'>
					</td>
				</tr>
				<tr>
					<td>Deadhead Cost</td>
					<td><input name='deadhead_cost' id='deadhead_cost' style='text-align:right' class='xlocked'></td>
				</tr>
				</table>
			</td>
			<td valign='top'>
				<table>
				<tr>
					<td>Break Even OTR</td>
					<td><input name='breakeven_otr' id='breakeven_otr' style='text-align:right' class='xlocked'></td>
				</tr>
				<tr>
					<td nowrap>Break Even Hourly</td>
					<td><input name='breakeven_hourly' id='breakeven_hourly' style='text-align:right' class='xlocked'></td>
				</tr>
				<tr>
					<td colspan='4'>
						<hr>
					</td>
				</tr>
				<tr>
					<td>Total Cost</td>
					<td><input name='total_cost' id='total_cost' style='text-align:right;background-color:#ffa569' class='xlocked'></td>
				</tr>
				<tr>
					<td>Bill Customer</td>
					<td><input name='bill_customer_disp' id='bill_customer_disp' style='text-align:right;background-color:#8fff8f;' class='xlocked'></td>
				</tr>
				<tr>
					<td colspan='4'>
						<hr>
					</td>
				</tr>
				<tr>
					<td>Profit $</td>
					<td><input name='profit' id='profit' style='text-align:right;background-color:#8fff8f;' class='xlocked'></td>
				</tr>
				<tr>
					<td>Profit %</td>
					<td><input name='profit_percent' id='profit_percent' style='text-align:right' class='xlocked'></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='2' style='text-align:center'>
						<div style='float:left'><input type='button' value='Save Quote' onclick='save_quote()'></div>
						<? 
						if(isset($_GET['id']) && $_GET['id'] > 0) { 
							echo "<div style='float:right'><input type='button' value='Delete Quote' onclick='delete_quote($_GET[id])'></div>";
						}
						?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<? 
		if(isset($_GET['id']) && $_GET['id'] > 0) 
		{ 
		?>
		<tr>
			<td valign='top' colspan="2">Email Quote to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input name='quote_emailer' id='quote_emailer' style='width:300px' value="<?=$_POST['quote_emailer']?>">&nbsp;
				<input type='button' value='Email Quote' onclick='email_this_quote()'>
			</td>			
		</tr>	
		<?
		}
		?>
		
		</table>
		
		<div style='margin-top:10px;'>
			<iframe width="550" height="350" id="map_frame" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="" style="color:#0000FF;text-align:left;border:1px black solid"></iframe>
		</div>		
	</td>
	<td valign='top' style='text-align:left'>		
		
		<div style='border:1px black solid;margin:5px 0 0 3px;text-align:left'>
			<p style='text-align:center'>Driver History</p>			
			<div id='driver_history' style=';text-align:left;float:left;margin:5px'></div>			
			<div style='clear:both'></div>
			
		</div>
		
		<div style=';border:1px black solid;margin:5px 0 0 3px;text-align:left'>
			<p style='text-align:center'>Truck History</p>
			<div id='truck_history' style=';text-align:left;float:left;margin:5px'></div>
			<div style='clear:both'></div>
		</div>		
	</td>
</tr>
</table>
</form>

<script type='text/javascript'>

	var truck_daily_cost = 0;
	var daily_cost = <?=$_POST['daily_cost']?>;
	var fuel_avg = <?=$_POST['doe_fuel_avg']?>;	
	var avg_mpg = <?=$_POST['avg_mpg']?>;
	var fuel_per_mile = fuel_avg / avg_mpg;
	var labor_per_mile = <?=$_POST['labor_per_mile']?>;
	var labor_per_mile_team = <?=$defaultsarray['labor_per_mile_team']?>;

	var quote_id = <?=( isset($_GET['id'])	? $_GET['id'] : 0) ?>;
	var map_on = 0;
	
	$('#doe_fuel_avg').val(<?=$_POST['doe_fuel_avg']?>);
	$('#avg_mpg').val(avg_mpg);
	$('#daily_cost').val(formatCurrency(daily_cost));
	$('#fuel_per_mile').val(formatCurrency(fuel_per_mile));
	$('#labor_per_mile').val(formatCurrency(labor_per_mile));
	$('#labor_per_hour').val(formatCurrency(<?=$_POST['labor_per_hour']?>));
	$('#tractor_maint_per_mile').val(formatCurrency(<?=$_POST['tractor_maint_per_mile']?>));
	$('#trailer_maint_per_mile').val(formatCurrency(<?=$_POST['trailer_maint_per_mile']?>));
	
	$('#tires_per_mile').val(formatCurrency(<?=$_POST['tires_per_mile']?>));
	$('#accidents_per_mile').val(formatCurrency(<?=$_POST['accidents_per_mile']?>));
	$('#mile_exp_per_mile').val(formatCurrency(<?=$_POST['mile_exp_per_mile']?>));
	$('#misc_per_mile').val(formatCurrency(<?=$_POST['misc_per_mile']?>));
		
	$('#linedate').datepicker();
	$('#expire_date').datepicker();
	
	$('input').change(calc_all);
	
	$('#driver_id').change(function() {
		// load the driver history
		load_driver_history($(this).val());
	});
	$('#truck_id').change(function() {
		//load selected driver history
		load_truck_history($(this).val());
	});
	
	function mrr_see_daily_cost()
	{
		alert('Truck ID '+$('#truck_id').val()+' has daily cost of $'+truck_daily_cost+'.  Standard Daily Cost is $'+daily_cost+' and New Cost is $'+new_daily_cost+'.');	
	}
	
	function load_driver_history(driver_id) {
		$('#driver_history').html("<img src='images/loader.gif'>");
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_driver_history",
		   data: {"driver_id":driver_id},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
			$('#driver_history').html($(xml).find('DispHTML').text());
			/*
			pay_per_mile = $(xml).find('PayPerMile').text();
			pay_per_hour = $(xml).find('PayPerHour').text();
			
			if(get_amount($('#labor_per_mile').val()) == 0) $('#labor_per_mile').val(pay_per_mile);
			if(get_amount($('#labor_per_hour').val()) == 0) $('#labor_per_hour').val(formatCurrency(pay_per_hour));
			
			if(get_amount(pay_per_mile) != get_amount($('#labor_per_mile').val())) {
				$('#labor_per_mile_holder').addClass('alert');
			} else {
				$('#labor_per_mile_holder').removeClass('alert');
			}
			
			if(get_amount(pay_per_hour) != get_amount($('#labor_per_hour').val())) {
				$('#labor_per_hour_holder').addClass('alert');
			} else {
				$('#labor_per_hour_holder').removeClass('alert');
			}				
			
			$('#labor_per_mile_holder').html("("+$(xml).find('PayPerMile').text()+")");
			$('#labor_per_hour_holder').html("("+formatCurrency($(xml).find('PayPerHour').text())+")");
			*/
			
		   }
		 });
	}
	
	function load_truck_history(truck_id) {
		$('#truck_history').html("<img src='images/loader.gif'>");
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_truck_history",
		   data: {"truck_id":truck_id},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   	
			$('#truck_history').html($(xml).find('DispHTML').text());			
			
			truck_daily_cost = get_amount($(xml).find('DailyCost').text());	
			
			if(truck_daily_cost > 0)
			{
				$('#daily_cost').val(formatCurrency(truck_daily_cost));
			}
			else
			{
				$('#daily_cost').val(formatCurrency(daily_cost));	
			}
			
			calc_all();
		   }
		 });
	}
		
	$('#quote_emailer').blur(function() {
		get_email_address_from_customer();
	});
	function get_email_address_from_customer()
	{
		var email_addr=$('#quote_emailer').val();
		var cust_id=$('#customer_id').val();
		
		if(email_addr=='' || email_addr=='Enter Email Address')
		{
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_get_customer_email",
			   data: {"cust_id": cust_id},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
					$('#quote_emailer').val($(xml).find("MRRCustomerEmail").text());
			   }
			 });
		}
	}
	
	function email_this_quote()
	{
		var email_addr=$('#quote_emailer').val();
		var qid=quote_id;
				
		if(email_addr!='' && email_addr!='Enter Email Address')
		{
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_email_this_quote",
			   data: {
			   		"email_address": email_addr,
			   		"quote_id": qid
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
					//$.noticeAdd({text: ""+$(xml).find("MRRSendMailMessage").text()+""});
					$.noticeAdd({text: "Email message sent to "+ email_addr +"."});		
			   }
			 });
		}
		else
		{
			$.noticeAdd({text: "Please enter an email address to use this feature."});	
			$('#quote_emailer').focus();
		}		
	}
	function update_map_storage(isrc)
	{
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_store_google_map",
			   data: {
			   		"map_html": isrc,
			   		"quote_id": quote_id
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
					//$.noticeAdd({text: "Email message sent to "+ email_addr +"."});		
			   }
			 });	
	}
	function update_quotes_stop_miles(stopper,miler)
	{
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_store_stop_miles",
			   data: {
			   		"stop_number": stopper,
			   		"stop_miles": miler,
			   		"quote_id": quote_id
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
					//$.noticeAdd({text: "Email message sent to "+ email_addr +"."});		
			   }
			 });	
	}
	
	function calc_all() {
		if(eval($("input['name=team_drivers']:checked").val())) {
			// team driver selected
			$('#labor_per_mile').val(formatCurrency(labor_per_mile_team));
		} else {
			// single driver selected
			$('#labor_per_mile').val(formatCurrency(labor_per_mile));
		}
		
		mrr_daily_cost=get_amount($('#daily_cost').val());
				
		// get all the variable expenses
		variable_expenses_total = 0;
		$('.variable_expenses').each(function() {
			variable_expenses_total += get_amount($(this).val());
		});
		
		hourly_use=get_amount($('#days_run_hourly').val());
		
		// if manual miles aren't specified, then use pc*miler miles
		if(get_amount($('#loaded_miles').val()) > 0) {
			use_miles = get_amount($('#loaded_miles').val());
		} else {
			use_miles = get_amount($('#pcm_loaded_miles').val())
		}
		total_miles = use_miles + get_amount($('#deadhead_miles').val());
		
		if(hourly_use > 0)
		{
			total_miles = get_amount($('#loaded_miles_hourly').val()) + get_amount($('#deadhead_miles').val());	
		}
		
		fuel_avg = get_amount($('#doe_fuel_avg').val());
		avg_mpg = get_amount($('#avg_mpg').val());
		fuel_per_mile = parseFloat((fuel_avg / avg_mpg).toFixed(2));
		$('#fuel_total').val(formatCurrency(fuel_per_mile * total_miles));
		
		tractor_maint_per_mile = get_amount($('#tractor_maint_per_mile').val());
		trailer_maint_per_mile = get_amount($('#trailer_maint_per_mile').val());
	
		tires_per_mile = get_amount($('#tires_per_mile').val());
		accidents_per_mile = get_amount($('#accidents_per_mile').val());
		mile_exp_per_mile = get_amount($('#mile_exp_per_mile').val());
		misc_per_mile = get_amount($('#misc_per_mile').val());	
		
		total_maint_per_mile = tractor_maint_per_mile + trailer_maint_per_mile + tires_per_mile + accidents_per_mile + mile_exp_per_mile + misc_per_mile;	
			
		labor_per_hour = get_amount($('#labor_per_hour').val());
		bill_customer = get_amount($('#bill_customer').val());
		
		total_per_mile = get_amount($('#labor_per_mile').val()) + total_maint_per_mile + fuel_per_mile;
		
		deadhead_cost = total_per_mile * get_amount($('#deadhead_miles').val());
		breakeven_otr = total_per_mile * use_miles + deadhead_cost + (mrr_daily_cost * get_amount($('#days_run_otr').val()));
		breakeven_hourly = get_amount($('#loaded_miles_hourly').val()) * (fuel_per_mile + total_maint_per_mile) + (get_amount($('#hours_worked').val()) * labor_per_hour) + (mrr_daily_cost * get_amount($('#days_run_hourly').val()));
		
		total_cost = breakeven_otr + breakeven_hourly + variable_expenses_total;
		profit = bill_customer - total_cost;
				
		if(hourly_use > 0)
		{
			$('#labor_total').val(formatCurrency(0 * total_miles));			
			$('#labor_per_hour_total').val(formatCurrency(get_amount($('#hours_worked').val()) * labor_per_hour));
		}
		else
		{
			$('#labor_total').val(formatCurrency(get_amount($('#labor_per_mile').val()) * total_miles));
			$('#labor_per_hour_total').val(formatCurrency(0 * labor_per_hour));			
		}
		
		$('#trailer_maint_per_mile_total').val(formatCurrency(get_amount($('#trailer_maint_per_mile').val()) * total_miles));
		$('#tractor_maint_per_mile_total').val(formatCurrency(get_amount($('#tractor_maint_per_mile').val()) * total_miles));
			
		$('#tires_per_mile_total').val(formatCurrency(get_amount($('#tires_per_mile').val()) * total_miles));
		$('#accidents_per_mile_total').val(formatCurrency(get_amount($('#accidents_per_mile').val()) * total_miles));
		$('#mile_exp_per_mile_total').val(formatCurrency(get_amount($('#mile_exp_per_mile').val()) * total_miles));
		$('#misc_per_mile_total').val(formatCurrency(get_amount($('#misc_per_mile').val()) * total_miles));
	
		$('#total_per_mile_total').val(formatCurrency(get_amount($('#total_per_mile').val()) * total_miles));
				
		$('#bill_customer').val(formatCurrency(bill_customer));
		
		$('#fuel_per_mile').val(formatCurrency(fuel_per_mile));
		
		$('#tires_per_mile').val(formatCurrency(tires_per_mile));
		$('#accidents_per_mile').val(formatCurrency(accidents_per_mile));
		$('#mile_exp_per_mile').val(formatCurrency(mile_exp_per_mile));
		$('#misc_per_mile').val(formatCurrency(misc_per_mile));
		
		$('#total_per_mile').val(formatCurrency(total_per_mile));

		$('#deadhead_cost').val(formatCurrency(deadhead_cost));
		$('#breakeven_otr').val(formatCurrency(breakeven_otr));

		$('#breakeven_hourly').val(formatCurrency(breakeven_hourly));
		
		$('#total_cost').val(formatCurrency(total_cost));
		
		$('#bill_customer_disp').val(formatCurrency(bill_customer));
		
		$('#profit').val(formatCurrency(profit));
		if(profit > 0) {
			$('#profit_percent').val((profit / bill_customer * 100).toFixed(2) + '%');
		} else {
			$('#profit_percent').val(0 + '%');
		}
	}
	
	<?
		if(isset($_GET['debug'])) {
			echo "
				$('#deadhead_miles').val(100);
				$('#loaded_miles').val(1400);
				$('#days_run_otr').val(2);
				$('#days_run_hourly').val(2);
				$('#loaded_miles_hourly').val(0);
				$('#bill_customer').val(3400);
				$('#hours_worked').val(20);
			";
		}
	?>
	
	
	function calc_run() 
	{
		if(map_on == 1)		return;
		
		map_on = 1;
		
		$('#mrr_msg').html("<span class='alert'><b>Recalculating...</b></span>");
		$('.data_disp').html('');
		
		locallist="";
		$('.local_entry').each(function() {	
			if($(this).val() != '' )
			{				
				locallist += $(this).val()+";";			
				
			}
			else
			{
				locallist += ";";		
			}
		});
		
		hub_run = 0
		//if($('#hub').attr('checked')) hub_run = 1;
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_build_run_miles_by_zip_alt2",
		   data: {
		   		"locallist":locallist,	  		
		   		hub_run:hub_run
		   		},
		   dataType: "xml",
		   cache:false,
		   error: function() {
		   		// error
			},
		   success: function(xml) {
				$('#total_miles').html($(xml).find('Miles').text());
				$('#total_hours').html(parseFloat($(xml).find('TravelTime').text()).toFixed(2));

				total_miles = 0;
				total_timer = 0;
				
				mrr_cntr=0;
				
				stopcounter = 1;
				
				$(xml).find("StopEntry").each(function() {
					
					if($(this).find('Errors').text()!="")
					{
						map_on = 0;	
						
						//$.prompt("Opps! "+$(this).find('Errors').text()+"  Try again.");
						
						
						$('#mrr_msg').html("<span class='alert'><b>Opps! "+$(this).find('Errors').text()+"  Try again.</b></span>");					
						return;
					}
					
					
					if(mrr_cntr==0)
					{
						$('#city_'+stopcounter).html($(this).find('StopFull2').text());
						//$('#city_'+stopcounter).html($(this).find('StopLoc2').text());
						//$('#state_'+stopcounter).html($(this).find('StopState2').text());
						//$('#lat_'+stopcounter).val($(this).find('StopLat2').text());
						//$('#long_'+stopcounter).val($(this).find('StopLong2').text());
					}
					else
					{
						miles = parseFloat($(this).find('StopDistance').text());
						total_miles += miles;
						
						timer = parseFloat($(this).find('StopHours').text());
						total_timer += timer;
						
						$('#city_'+stopcounter).html($(this).find('StopFull2').text());
						//$('#city_'+stopcounter).html($(this).find('StopLoc2').text());
						//$('#state_'+stopcounter).html($(this).find('StopState2').text());
						//$('#lat_'+stopcounter).val($(this).find('StopLat2').text());
						//$('#long_'+stopcounter).val($(this).find('StopLong2').text());
						
						$('#miles_'+stopcounter).html(miles.toFixed(2));
						$('#total_'+stopcounter).html(total_miles.toFixed(2));
					}
					mrr_cntr++;
					stopcounter++;
				});
				
				$('#pcm_loaded_miles').val(total_miles);
				
				$('#total_hours').html(parseFloat(total_timer).toFixed(2));
				
						
				map_link = generate_map_url(true);
				$('#map_frame').attr('src',map_link);
								
				update_map_storage(map_link);
				
				$('#mrr_msg').html("<span style='color:#00CC00;'><b>Route Planned Done.</b></span>");
				map_on = 0;
				calc_all();				
		   }
		 });		 
		
	}

	function generate_map_url(embed_flag) {
		address_array = new Array();
		counter = 0;
				
		$('.local_entry').each(function() {	
			if($(this).val() != '' )
			{				
				address_array[counter] = $(this).val();
				counter++;	
			}
		});
			
		
		if(address_array.length == 1) {
			// only one address, so show exact spot, not driving directions
			map_link = "https://maps.google.com/maps?f=q&q="+address_array[0];
		} else {
			
			map_link = "https://maps.google.com/maps?f=d&source=s_d&saddr="+address_array[0];
			
			if(address_array.length > 1) {
				map_link += "&daddr=";
				for(i=1;i<address_array.length;i++) {
					if(i > 1) map_link += "+to:";
					map_link += address_array[i];
				}
			}		
		}
		
		if(address_array.length < 1) {
			return '';
		}
		
		if(embed_flag) map_link += "&output=embed";
		
		return map_link;
	}
	
	function view_map() {
		map_link = generate_map_url(false);
		
		if(map_link == '') {
			$.prompt("You must enter at least one valid stop in order to view the map");
			return false;
		}

		window.open(map_link);
	}

	function save_quote() {
		if($('#quote_name').val() == '') {
			$.prompt("You must specify a quote name before you can save a quote");
			return false;
		}
		
		document.quote_form.submit();
	}
	
	function delete_quote(id) {
		$.prompt("Are you sure you want to <span class='alert'>delete</span> this quote?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						window.location = 'quote_promiles.php?del='+id;
					}
				}
			}
		);
	}
	
	<?
	if(isset($_GET['id']) && $_GET['id'] > 0) {
		echo "calc_run();";
	}
	?>
	
	calc_run();
	calc_all();
	
	if($('#truck_id').val() > 0)
	{
		//load selected driver history
		load_truck_history($('#truck_id').val());	
	}
	
	if($('#driver_id').val() > 0)
	{
		// load the driver history
		load_driver_history($('#driver_id').val());
	}
		
	$('.xlocked').attr('readonly','readonly');
</script>

<? include('footer.php') ?>
