<? include("application.php") ?>
<?

// ************ NOT USED ANYMORE **************
// USE AJAX.PHP instead

error_reporting(E_ALL);
ini_set('display_errors', '1');

	switch ($_GET['cmd']) {
		case 'move_truck_day':
			move_truck_day();
			break;
		case 'update_trailer_location':
			update_trailer_location();
			break;
		case 'move_note_day':
			move_note_day();
			break;
		case 'load_notes':
			load_notes();
			break;
		case 'delete_note':
			delete_note();
			break;
		case 'display_notes':
			display_notes();
			break;
		case 'display_attachments':
			display_attachments();
			break;
		case 'delete_attachment':
			delete_attachment();
			break;
		case 'set_calendar_display_mode':
			set_calendar_display_mode();
			break;
		case 'load_driver_history':
			load_driver_history();
			break;
		case 'load_truck_history':
			load_truck_history();
			break;
		case 'load_customer_brief':
			load_customer_brief();
			break;
		case 'add_note_entry':
			add_note_entry();
			break;
		case 'delete_note_entry':
			delete_note_entry();
			break;
		case 'set_dispatch_display_mode':
			set_dispatch_display_mode();
			break;
	 	case 'load_available_loads':
	 		load_available_loads();
	 		break;
	 	case 'load_stops':
	 	 	load_stops();
	 	 	break;
	 	case 'manage_stop':
	 		manage_stop();
	 		break;
	 	case 'load_stop_id':
	 		load_stop_id();
	 		break;
	 	case 'delete_stop':
	 		delete_stop();
	 		break;
	 	case 'update_stop_dispatch':
	 		update_stop_dispatch();
	 		break;
	 	case 'update_stop_completed':
	 		update_stop_completed();
	 		break;
	 	case 'load_handler_quick_create':
	 		load_handler_quick_create();
	 		break;
	 	case 'add_dispatch_expense':
	 		add_dispatch_expense();
	 		break;
	 	case 'load_dispatch_expenses':
	 		load_dispatch_expenses();
	 		break;
	 	case 'delete_dispatch_expense':
	 		delete_dispatch_expense();
	 		break;
	 	case 'load_dispatchs':
	 		load_dispatchs();
	 		break;
	 	case 'search_stop_address':
	 		search_stop_address();
	 		break;
	 	case 'load_address_by_stop_id':
	 		load_address_by_stop_id();
	 		break;
	 	case 'clear_address_history':
	 		clear_address_history();
	 		break;
	 	case 'load_driver_expenses':
	 		load_driver_expenses();
	 		break;
	 	case 'add_driver_expense':
	 		add_driver_expense();
	 		break;
	 	case 'delete_driver_expense':
	 		delete_driver_expense();
	 		break;
	 	case 'get_daily_cost_ajax':
	 		get_daily_cost_ajax();
	 		break;
	 	case 'save_odometer_reading':
	 	 	save_odometer_reading();
	 	 	break;
	 	case 'truck_odometer_alert':
	 	 	truck_odometer_alert();
	 	 	break;
	 	case 'load_odometer_history':
	 		load_odometer_history();
	 		break;
	 	case 'delete_odometer_entry':
	 		delete_odometer_entry();
	 		break;
	 	case 'load_driver_unavailable':
	 		load_driver_unavailable();
	 		break;
	 	case 'add_driver_unavailability':
	 		add_driver_unavailability();
	 		break;
	 	case 'delete_driver_unavailability':
	 		delete_driver_unavailability();
	 		break;
	 	case 'driver_unavailable':
	 		driver_unavailable();
	 		break;
	 	case 'driver_unavailable_range':
	 		driver_unavailable_range();
	 		break;
	 	case 'add_truck_note':
	 		add_truck_note();
	 		break;
	 	case 'detach_truck':
	 		detach_truck();
	 		break;
	 	case 'detach_trailer':
	 		detach_trailer();
	 		break;
	 	case 'load_attached_equipment':
	 		load_attached_equipment();
	 		break;
	 	case 'get_detach_info':
	 		get_detach_info();
	 		break;
	 	case 'rename_scanned_load':
	 		rename_scanned_load();
	 		break;
	 	case 'delete_scanned_load':
	 		delete_scanned_load();
	 		break;
	 	case 'driver_load_flag':
	 		driver_load_flag();
	 		break;
	 	case 'ajax_sicap_create_invoice':
	 		ajax_sicap_create_invoice();
	 		break;
	 	case 'update_driver_notes':
	 		update_driver_notes();
	 		break;
	 	case 'get_driver_notes':
	 		get_driver_notes();
	 		break;
	 	case 'ajax_sicap_delete_invoice':
	 		ajax_sicap_delete_invoice();
	 		break;
	 	case 'get_driver_rate_per_mile':
	 		get_driver_rate_per_mile();
	 		break;
	 	case 'update_stop_odometer':
	 		update_stop_odometer();
	 		break;
	 	case 'update_predispatch':
		 	update_predispatch();
		 	break;
	}
	
	
	function move_truck_day() {
		$sql = "
			update trucks_log
			set linedate = '".sql_friendly($_POST['linedate'])."',
				linedate_updated = now()
				
			where id = '".sql_friendly($_POST['log_id'])."'
			limit 1
		";
		simple_query($sql);
	}
	
	function update_trailer_location() {
		$sql = "
			update trailers
			set current_location = '".sql_friendly($_POST['new_location'])."',
				location_updated = now()
			where id = '".sql_friendly($_POST['trailer_id'])."'
		";
		simple_query($sql);
	}
	
	function move_note_day() {
		$sql = "
			update notes
			set linedate = '".date("Y-m-d", strtotime($_POST['linedate']))."'
			where id = '".sql_friendly($_POST['note_id'])."'
		";
		simple_query($sql);
	}
	
	function add_truck_note() {
		$sql = "
			insert into trucks_log_notes
				(truck_log_id,
				linedate_added,
				note,
				user_id,
				deleted)
				
			values ('".sql_friendly($_POST['dispatch_id'])."',
				now(),
				'".sql_friendly($_POST['note'])."',
				'".sql_friendly($_SESSION['user_id'])."',
				0)
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	
	function load_notes() {
		$sql = "
			select trucks_log_notes.linedate_added,
				trucks_log_notes.note
			
			from trucks_log_notes
			where trucks_log_notes.truck_log_id = '".sql_friendly($_POST['log_id'])."'
				and trucks_log_notes.linedate_added >= '".date("Y-m-d", strtotime("-7 day", time()))."'
				and trucks_log_notes.deleted = 0
				
			order by trucks_log_notes.linedate_added desc
			limit 20
		";
		$data = simple_query($sql);
		
		$sql = "
			select load_handler_id
			
			from trucks_log
			where id = '".sql_friendly($_POST['log_id'])."'
		";
		$data_load_id = simple_query($sql);
		$row_load_id = mysqli_fetch_array($data_load_id);
		
		$sql = "
			select special_instructions
			
			from load_handler
			where id = '".$row_load_id['load_handler_id']."'
		";
		$data_load = simple_query($sql);
		$row_load = mysqli_fetch_array($data_load);
		
		if($row_load['special_instructions'] != '') {
			echo "
				<b>Special Instructions:</b> ".force_line_wrap($row_load['special_instructions'], 50)."<p>
			";
		}
		
		if(!mysqli_num_rows($data)) {
			echo "No notes found";
		} else {
			echo "
				<table style='border:0px red solid;width:425px;'>
				<tr>
					<td style='width:50px'><b>Day</b></td>
					<td style='width:75px' align='right'><b>Date</b></td>
					<td style='width:50px' align='right'><b>Time</b></td>
					<td style='width:20px'></td>
					<td style='margin-left:20px'><b>Note</b></td>
				</tr>
			";
			while($row_notes = mysqli_fetch_array($data)) {
				
				// force wrap lines, since for some reason, the table won't do it itself (even with a forced width ste				
				$note_holder = force_line_wrap($row_notes['note'], 50);

				// end of code to force a line wrap
				
				echo "
					<tr>
						<td>".date("D", strtotime($row_notes['linedate_added']))."</td>
						<td align='right'>".date("n-j-y", strtotime($row_notes['linedate_added']))."</td>
						<td align='right'>".date("H:i", strtotime($row_notes['linedate_added']))."</td>
						<td></td>
						<td>
							$note_holder
						</td>
					</tr>
				";
			}
			echo "
				</table>
			";
		}
	}
	
	function delete_note() {
		$sql = "
			update trucks_log_notes
			set deleted = 1
			where id = '".sql_friendly($_POST['note_id'])."'
		";
		simple_query($sql);
	}
	
	function display_notes() {
		$sql = "
			select *
			
			from notes_main
			where deleted = 0
				and note_type_id = '".sql_friendly($_POST['section_id'])."'
				and xref_id = '".sql_friendly($_POST['xref_id'])."'
			order by linedate_added desc
		";
		
		$data = simple_query($sql);
		
		
		// add_note_entry js funtion is located in includes/functions.js 
		echo "
			 

			<table width='100%'>
			<tr>
				<td>Add Note</td>
				<td colspan='2' align='right' nowrap>
					<span id='note_entry_loading' style='display:none'><img src='images/loader.gif'></span>
					<input type='button' value='Add Note' onclick=\"add_note_entry($_POST[section_id],$_POST[xref_id],$('#new_note_entry').val())\">
				</td>
			</tr>
			<tr>
				<td colspan='3'><textarea name='new_note_entry' id='new_note_entry' style='width:98%'></textarea></td>
			</tr>
			<tr>
				<td valign='top'><b>Note</b></td>
				<td align='right' valign='top'><b>Date</b></td>
				<td valign='top'></td>
			</tr>
		";
		if(!mysqli_num_rows($data)) {
			echo "
				<tr>
					<td colspan='5'><i>No Notes</i></td>
				</tr>
			";
		}
		
		while($row = mysqli_fetch_array($data)) {
			echo "
				<tr id='note_entry_$row[id]'>
					<td valign='top'>$row[note]</td>
					<td valign='top' align='right'>".date("m/d/Y", strtotime($row['linedate_added']))."</td>
					<td valign='top' nowrap>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:delete_note_entry($row[id])'><img src='images/delete_sm.gif' alt='Delete Note' title='Delete Note' border='0'></a></td>
				</tr>
			";
		}
		echo "</table>";
	}
	
	function display_attachments() {
		global $defaultsarray;
		
		$sql = "
			select *
			
			from attachments
			where deleted = 0
				and section_id = '".sql_friendly($_POST['section_id'])."'
				and xref_id = '".sql_friendly($_POST['xref_id'])."'
			order by linedate_added desc
		";
		$data = simple_query($sql);
		
		echo "
			<table width='100%'>
			<tr>
				<td><b>Filename</b></td>
				<td align='right'><b>Date Uploaded</b></td>
				<td></td>
			</tr>
		";
		
		while($row = mysqli_fetch_array($data)) {
			echo "
				<tr id='attachment_row_$row[id]'>
					<td><a href=\"$defaultsarray[document_upload_dir]/$row[fname]\" target='blank_$row[id]'>$row[fname]</a></td>
					<td align='right'>".date("m/d/Y", strtotime($row['linedate_added']))."</td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:delete_attachment($row[id])'><img src='images/delete_sm.gif' alt='Delete Attachment' title='Delete Attachment' border='0'></a></td>
				</tr>
			";
		}
		echo "</table>";
	}
	
	
	function delete_attachment() {
		$sql = "
			update attachments
			set deleted = 1
			where id = '".sql_friendly($_POST['id'])."'
			limit 1
		";
		simple_query($sql);
	}
	
	function set_calendar_display_mode() {
		if($_POST['full_view'] == '1') {
			$_SESSION['full_calendar_view_flag'] = true;
			echo "full view";
		} else {
			$_SESSION['full_calendar_view_flag'] = false;
			echo "small view";
		}
	}
	
	function load_available_loads() {
		
		
		// get the available loads
		$sql = "
			select load_handler.*,
				customers.name_company,
				concat(drivers.name_driver_last, ', ', drivers.name_driver_first) as driver_name
			
			from load_handler
				left join customers on load_handler.customer_id = customers.id
				left join drivers on drivers.id = load_handler.preplan_driver_id
			where load_handler.deleted = 0
				and (load_available = 1
					or (select count(*) from trucks_log where load_handler_id = load_handler.id and trucks_log.deleted = 0) = 0)				
			order by origin_state, origin_city, dest_state, dest_city
				
		";
		$data = simple_query($sql);
		
		
		$return_var = "<rslt>".mysqli_num_rows($data)."</rslt>";
		
		$disphtml = "
			<table>
			<tr>
				<td nowrap><b>Load ID</b></td>
				<td><b>Customer</b></td>
				<td><b>Origin</b></td>
				<td><b>Destination</b></td>
				<td nowrap><b>Preplan Flag</b></td>
			</tr>
		";
		while($row = mysqli_fetch_array($data)) {
			$disphtml .= "
				<tr style='font-size:10px'>
					<td><a href='manage_load.php?load_id=$row[id]'>$row[id]</a></td>
					<td nowrap>$row[name_company]</td>
					<td nowrap>$row[origin_city]".($row['origin_state'] != '' ? ', '.$row['origin_state'] : '')."</td>
					<td nowrap>$row[dest_city]".($row['dest_state'] != '' ? ', '.$row['dest_state'] : '')."</td>
					<td nowrap>".($row['preplan'] ? $row['driver_name'] : "")."</td>
				</tr>
			";
		}
		$disphtml .= "</table><div style='clear:both'></div>";
		
		
		$return_var .= "
			<DispHTML><![CDATA[$disphtml]]></DispHTML>
			
		";
		display_xml_response($return_var);
		
	}

	function load_driver_history() {
		
		// get the driver info
		$sql = "
			select *
			
			from drivers
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		$data_driver = simple_query($sql);
		$row_driver = mysqli_fetch_array($data_driver);
		
		
		// get the loads this driver ran
		$sql = "
			select customers.name_company,
				trailers.trailer_name,
				trucks.name_truck,
				trucks_log.linedate,
				trucks_log.origin,
				trucks_log.origin_state,
				trucks_log.destination,
				trucks_log.destination_state,
				trucks_log.profit
			
			from trucks_log
				left join customers on trucks_log.customer_id = customers.id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
			where trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."'
				and linedate > '".date("Y-m-d", strtotime("-14 day", time()))."'
			order by linedate desc
		";
		$data = simple_query($sql);
		
		
		$return_var = "<rslt>".mysqli_num_rows($data)."</rslt>";
		
		$disphtml = "
			<table>
			<tr>
				<td><b>Driver:</b></td>
				<td colspan='5'>$row_driver[name_driver_first] $row_driver[name_driver_last]</td>
			</tr>
			<tr>
				<td><b>Employer:</b></td>
				<td colspan='5'>$row_driver[employer]</td>
			</tr>
			<tr>
				<td><b>Cell Phone:</b></td>
				<td colspan='5'>$row_driver[phone_cell]</td>
			</tr>
			<tr>
				<td colspan='10'><hr></td>
			</tr>
			<tr>
				<td><b>Truck</b></td>
				<td><b>Trailer</b></td>
				<td><b>Date</b></td>
				<td><b>Origin</b></td>
				<td><b>Destination</b></td>
				<td align='right'><b>Profit</b></td>
			</tr>
		";
		while($row = mysqli_fetch_array($data)) {
			$disphtml .= "
				<tr style='font-size:10px'>
					<td nowrap>$row[name_truck]</td>
					<td nowrap>$row[trailer_name]</td>
					<td nowrap>".date("M, j", strtotime($row['linedate']))."</td>
					<td nowrap>$row[origin]".($row['origin_state'] != '' ? ', '.$row['origin_state'] : '')."</td>
					<td nowrap>$row[destination]".($row['destination_state'] != '' ? ', '.$row['destination_state'] : '')."</td>
					<td align='right'>$".money_format('', $row['profit'])."</td>
				</tr>
			";
		}
		$disphtml .= "</table>";
		
		
		$return_var .= "
			<DispHTML><![CDATA[$disphtml]]></DispHTML>
			<PayPerMile>$row_driver[charged_per_mile]</PayPerMile>
			<PayPerMileTeam>$row_driver[charged_per_mile_team]</PayPerMileTeam>
			<PayPerHour>$row_driver[charged_per_hour]</PayPerHour>
		";
		display_xml_response($return_var);
		
	}
	
	function load_truck_history() {
		
		global $defaultsarray;
		$disphtml = "";
		
		// get the truck info
		$sql = "
			select *
			
			from trucks
			where id = '".sql_friendly($_POST['truck_id'])."'
		";
		$data_truck = simple_query($sql);
		$row_truck = mysqli_fetch_array($data_truck);
		
		
		// get the loads this truck ran
		$sql = "
			select customers.name_company,
				trailers.trailer_name,
				trucks.name_truck,
				trucks_log.linedate_pickup_eta,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trucks_log.origin,
				trucks_log.origin_state,
				trucks_log.destination,
				trucks_log.destination_state,
				trucks_log.profit,
				trucks_log.daily_run_otr,
				trucks_log.id
			
			from trucks_log
				left join customers on trucks_log.customer_id = customers.id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join drivers on drivers.id = trucks_log.driver_id
			where trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'
				and linedate_pickup_eta >= '".date("Y-m-d", strtotime("-45 day", time()))."'
			order by linedate_pickup_eta
		";
		$data = simple_query($sql);
		
		// get the profit for past x day ranges
		
		$last_week_start = strtotime("-".(date("w") + 7)." day", time());
		$last_week_end = strtotime("-".(date("w"))." day", time());
		$last_month = strtotime("-1 month", time());
		
		// find the first sunday of the month
		$sdate = strtotime(date("m/1/Y"));
		for($i=0;$i<7;$i++) {
			if(date("w", strtotime("$i day", $sdate)) == '0') break;
		}
		
		$date_week0 = strtotime(date("m/1/Y", time()));
		$date_week1 = strtotime("$i day", $sdate);
		$date_week2 = strtotime("7 day", $date_week1);
		$date_week3 = strtotime("7 day", $date_week2);
		$date_week4 = strtotime("7 day", $date_week3);
		$date_week5 = strtotime("7 day", $date_week4);
		$date_week6 = strtotime("7 day", $date_week5);
		
		//$disphtml .= date("m/d/Y", $date_week0)." | " .date("m/d/Y", $date_week1)."<br>";
		
		/*
						(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta > '".date("Y-m-d", strtotime("-7 day", time()))."') as profit7,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta > '".date("Y-m-d", strtotime("-14 day", time()))."') as profit14,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta > '".date("Y-m-d", strtotime("-30 day", time()))."') as profit30,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-01")."' and linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", time()))."') as profit_mtd,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-01", $last_month)."' and linedate_pickup_eta < '".date("Y-m-01")."') as profit_last_month,
				
			<!---
			<tr>
				<td colspan='3'><b>Profit Month to Date</b></td>
				<td>$".money_format('', $row_profit['profit_mtd'])."</span></td>
			</tr>
			<tr>
				<td colspan='3'><b>Profit Last Month</b></td>
				<td>$".money_format('', $row_profit['profit_last_month'])."</span></td>
			</tr>
			--->
		*/
		$sql = "
			select 
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", strtotime("-".date("w")." day", time()))."') as profit_wtd,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $last_week_start)."' and linedate_pickup_eta <= '".date("Y-m-d", $last_week_end)."') as profit_last_week,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week0)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week1)."') as profit_week0,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week1)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week2)."') as profit_week1,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week2)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week3)."') as profit_week2,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week3)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week4)."') as profit_week3,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week4)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week5)."') as profit_week4,
				(select ifnull(sum(trucks_log.profit),0) as profit from trucks_log where deleted = 0 and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."' and linedate_pickup_eta >= '".date("Y-m-d", $date_week5)."' and linedate_pickup_eta < '".date("Y-m-d", $date_week6)."') as profit_week5
		";
		
		//echo $sql;
		
		$data_profit = simple_query($sql);
		$row_profit = mysqli_fetch_array($data_profit);

		$return_var = "<rslt>".mysqli_num_rows($data)."</rslt>";
		
		$daily_cost = get_daily_cost($_POST['truck_id']);
		
		$disphtml .= "
			<table style='width:100%'>
			<tr>
				<td colspan='5'><b>Truck: $row_truck[name_truck]</b></td>
			</tr>
			<tr>
				<td></td>
				<td align='right'><b>Profit</b></td>
				<td align='right' nowrap><b>&nbsp;&nbsp; Days Run</b></td>
				<td align='right'><b>Days Avail</b></td>
				<td align='center'><b>Variance</b></td>
				<td align='right' nowrap><b>Est Profit</b></td>
			</tr>
		";
		
		for($i=0;$i<6;$i++) {
			$date_week_start = ${'date_week'.$i};
			$date_week_end = strtotime("6 day", $date_week_start);
			$days_available_array = get_days_available($date_week_start, $date_week_end, $_POST['truck_id']);
			$days_actual = get_days_run($date_week_start, strtotime("-1 day", $date_week_end), $_POST['truck_id']);
			$days_variance = $days_actual - $days_available_array['days_available_so_far'];
			
			$variance_cost = $days_variance * $daily_cost;
			
			$disphtml .= "

				<tr>
					<td colspan='10'>
						<table style='border:1px black solid;margin-bottom:5px;width:100%'>

			";
			
				$disphtml .= "
					<tr class='even'>
						<td width='33%'><b>Driver</b></td>
						<td width='33%'><b>Trailer</b></td>
						<td width='33%'><b>Date</b></td>
						<td width='33%' align='center'><b>Days</b></td>
						<td><b>Origin</b></td>
						<td><b>Destination</b></td>
						<td align='right'><b>Profit</b></td>
					</tr>
				";
				if(mysqli_num_rows($data)) mysqli_data_seek($data,0);
				while($row = mysqli_fetch_array($data)) {
					if(strtotime(date("m/d/Y", strtotime($row['linedate_pickup_eta']))) >= ${'date_week'.$i} && strtotime($row['linedate_pickup_eta']) < ${'date_week'.($i+1)}) {
						$disphtml .= "
							<tr style='font-size:10px'>
								<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
								<td nowrap>$row[trailer_name]</td>
								<td nowrap>".date("M, j", strtotime($row['linedate_pickup_eta']))."</td>
								<td nowrap align='center'>".number_format($row['daily_run_otr'])."</td>
								<td nowrap>$row[origin]".($row['origin_state'] != '' ? ', '.$row['origin_state'] : '')."</td>
								<td nowrap>$row[destination]".($row['destination_state'] != '' ? ', '.$row['destination_state'] : '')."</td>
								<td align='right'>$".money_format('', $row['profit'])."</td>
							</tr>
						";
					}
				}
		
			$disphtml .= "
						<tr class='odd'>
							<td nowrap><b>".(time() > ${'date_week'.$i} && time() <= strtotime("6 day", ${'date_week'.$i}) ? " <span class='alert'>" : "<span>")."Week $i (".date("n/d", ${'date_week'.$i}).")</span></b></td>
							<td align='center' colspan='2'>$".money_format('', $row_profit['profit_week'.$i])."</span></td>
							<td align='center'>".number_format($days_actual)."</td>
							<td align='center'>$days_available_array[days_available_so_far]</td>
							<td align='right' nowrap>$days_variance = $".money_format('', $variance_cost)."</td>
							<td align='right' colspan='2'>$".money_format('',$variance_cost + $row_profit['profit_week'.$i])."</td>
						</tr>
			
						</table>
					</td>
				</tr>
				
			";
		}

		$disphtml .= "</table>";
		
		
		$return_var .= "
			<DispHTML><![CDATA[$disphtml]]></DispHTML>
			
		";
		display_xml_response($return_var);
	}
	
	function load_customer_brief() {
		// function to show some simple information like the customer contact, email, and phone number
		// (used right now on the load handler page)
		
		global $defaultsarray;
		
		$sql = "
			select *
			
			from customers
			where id = '".sql_friendly($_POST['customer_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		// get the fuel surcharge
		$sql = "
			select ifnull(nullif(customers.fuel_surcharge,0),fuel_surcharge.fuel_surcharge) as fuel_surcharge
			from fuel_surcharge, customers
			where range_lower <= '".(money_strip($_POST['fuel_avg']) == 0 ? sql_friendly(money_strip($defaultsarray['fuel_surcharge'])) : sql_friendly(money_strip($_POST['fuel_avg'])))."'
				and customer_id = '".sql_friendly($_POST['customer_id'])."'
				and customers.id = fuel_surcharge.customer_id
				
			order by range_lower desc
			limit 1
		";
		$data_surcharge = simple_query($sql);
		if(!mysqli_num_rows($data_surcharge)) {
			$use_surcharge = 0;
		} else {
			$row_surcharge = mysqli_fetch_array($data_surcharge);
			$use_surcharge = $row_surcharge['fuel_surcharge'];
		}
		
		$return_html = "
			<table>
			<tr>
				<td>Contact:</td>
				<td><b>$row[contact_primary]</b></td>
			</tr>
			<tr>
				<td>E-Mail:</td>
				<td><a href='mailto:$row[contact_email]'>$row[contact_email]</a></td>
			</tr>
			<tr>
				<td>Phone:</td>
				<td><b>$row[phone_work]</b></td>
			</tr>
			</table>
		";
		
		$return_var = "
			<ReturnHTML><![CDATA[$return_html]]></ReturnHTML>
			<Contact><![CDATA[$row[contact_primary]]]></Contact>
			<ContactEMail><![CDATA[$row[contact_email]]]></ContactEMail>
			<PhoneWork><![CDATA[$row[phone_work]]]></PhoneWork>
			<FuelPerMile><![CDATA[$use_surcharge]]></FuelPerMile>
		";
		display_xml_response($return_var);
	}
	
	function add_note_entry() {
		$sql = "
			insert into notes_main
				(linedate_added,
				deleted,
				created_by_user_id,
				note_type_id,
				xref_id,
				note)
				
			values (now(),
				0,
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($_POST['section_id'])."',
				'".sql_friendly($_POST['xref_id'])."',
				'".sql_friendly($_POST['note'])."')
		";
		simple_query($sql);
		
		echo "1";
	}
	
	function delete_note_entry() {
		$sql = "
			update notes_main
			set deleted = 1
			where id = '".sql_friendly($_POST['note_id'])."'
		";
		simple_query($sql);
		
		echo "1";
	}
	
	function set_dispatch_display_mode() {
		if($_POST['show_all'] == '1') {
			$_SESSION['show_all_dispatches'] = true;
			echo "Show all";
		} else {
			$_SESSION['show_all_dispatches'] = false;
			echo "Show Open";
		}
	}
	
	function load_stops() {
		
		
		if($_POST['load_id'] == 0) {
			$return_var = "<rslt>0</rslt>";
			display_xml_response($return_var);
		}
		
		// get the load info
		$sql = "
			select *
			
			from load_handler
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		$data_load = simple_query($sql);
		$row_load = mysqli_fetch_array($data_load);
		
		$sql = "
			select *,
				date_format(linedate_completed, '%Y-%m-%d') as linedate_completed_date,
				date_format(linedate_completed, '%H:%i') as linedate_completed_time
			
			from load_handler_stops
			where load_handler_id = '".sql_friendly($_POST['load_id'])."'
				and deleted = 0
				".(isset($_POST['dispatch_id']) ? " and (trucks_log_id is null or trucks_log_id = 0 or trucks_log_id = '$_POST[dispatch_id]') " : "")."
			order by linedate_pickup_eta, linedate_pickup_pta, linedate_dropoff_eta
		";
		$data = simple_query($sql);
		
		$sql = "
			select *
			
			from trucks_log
			where load_handler_id = '".sql_friendly($_POST['load_id'])."'
				and deleted = 0
		";
		$data_dispatch = simple_query($sql);
		
		// predispatch
		
		$disphtml = "
			<table width='100%'>
			<tr>
				<td><b>Predispatch</b></td>
				<td><b>Odometer</b></td>
				<td><b>City</b></td>
				<td><b>State</b></td>
				<td><b>Zip</b></td>
			</tr>
			<tr>
				<td></td>
				<td><input class='odometer_update' name='predispatch_odometer' id='predispatch_odometer' value=\"$row_load[predispatch_odometer]\" style='width:80px' onchange=\"js_update_predispatch($_POST[load_id])\"></td>
				<td><input class='odometer_update' name='predispatch_city' id='predispatch_city' value=\"$row_load[predispatch_city]\" onchange=\"js_update_predispatch($_POST[load_id])\"></td>
				<td><input class='odometer_update' name='predispatch_state' id='predispatch_state' value=\"$row_load[predispatch_state]\" onchange=\"js_update_predispatch($_POST[load_id])\"></td>
				<td><input class='odometer_update' name='predispatch_zip' id='predispatch_zip' value=\"$row_load[predispatch_zip]\" style='width:80px' onchange=\"js_update_predispatch($_POST[load_id])\"></td>
			</tr>
			</table>
		";
		
		$disphtml .= "
			<table width='100%'>
			<tr>
				".(isset($_POST['dispatch_id']) ? "<td></td>" : "")."
				<td nowrap><b>Stop ID</b></td>
				<td nowrap><b>Stop Type</b></td>
				<td nowrap><b>Odometer</b></td>
				<td><b>Name</b></td>
				<td nowrap><b>City / State</b></td>
				<td nowrap align='right'><b>PC*M</b></td>
				<td><b>Appointment</b></td>
				<td nowrap><b>Projected Time</b></td>
				".(isset($_POST['dispatch_id']) ? '' : "<td><b>Dispatch ID</b></td>")."
				<td nowrap><b>Date Completed</b></td>
				<td nowrap><b>Time Completed</b></td>
			</tr>
		";


		$last_dispatch_id = 0;
		$pcm_miles_total = 0;
		$prev_city_state = "";
		while($row = mysqli_fetch_array($data)) {
			$city_state = "$row[shipper_city]".($row['shipper_state'] != '' ? ', '.$row['shipper_state'] : '');
			if($last_dispatch_id != $row['trucks_log_id']) {
				if($last_dispatch_id != 0) {
					$disphtml .= "
						<tr>
							<td colspan='15'><hr></td>
						</tr>
					";
				}
				//$prev_city_state = $city_state;
				$last_dispatch_id = $row['trucks_log_id'];
			}

			$prev_city_state = $city_state;
			$disphtml .= "
				<tr style='font-size:10px' id='stop_id_$row[id]'>
					".(isset($_POST['dispatch_id']) ? "<td><input type='checkbox' name='checkbox_stop[]' ".($row['trucks_log_id'] == $_POST['dispatch_id'] ? "checked" : "")." value='$row[id]'></td>" : "")."
					<td nowrap>
						".(isset($_POST['dispatch_id']) ? $row['id'] : "<a href='javascript:load_stop_id($row[id])'>$row[id]</a>")."
						<input type='hidden' name='stop_id_array[]' value='$row[id]'>
					</td>
					<td nowrap>".($row['stop_type_id'] == '1' ? "Shipper" : "Consignee")."</td>
					<td nowrap><input name='odometer_reading_$row[id]' id='odometer_reading_$row[id]' style='width:80px' value='".number_format($row['odometer_reading'],0)."' onchange=\"js_update_stop_odometer($row[id], $_POST[load_id])\"></td>
					<td nowrap>$row[shipper_name]</td>
					<td nowrap>$city_state</td>
					<td align='right'><span class='pcm_miles'>$row[pcm_miles]</span></td>
					<td nowrap>".(strtotime($row['linedate_pickup_eta']) <= 0 ? '' : date("M d, Y", strtotime($row['linedate_pickup_eta'])))." ".time_prep($row['linedate_pickup_eta'])."</td>
					<td nowrap>".(strtotime($row['linedate_pickup_pta']) <= 0 ? '' : date("M d, Y", strtotime($row['linedate_pickup_pta'])))." ".time_prep($row['linedate_pickup_pta'])."</td>
			";
			if(isset($_POST['dispatch_id'])) {
				
			} else {
				$disphtml .= "
						<td nowrap>
							<select name='stop_dispatch_$row[id]' id='stop_dispatch_$row[id]' class='stop_dispatch' stop_id='$row[id]' onchange='update_stop_dispatch($row[id])'>
							<option value='0'>select one</option>
				";
							@mysqli_data_seek($data_dispatch,0);
							while($row_dispatch = mysqli_fetch_array($data_dispatch)) {
								$disphtml .= "<option value='$row_dispatch[id]' ".($row_dispatch['id'] == $row['trucks_log_id'] ? 'selected' : '').">$row_dispatch[id]</option>";
							}
				$disphtml .= "
							</select>
						</td>
				";
			}
			$disphtml .= "
					<td nowrap><input name='linedate_completed_$row[id]' id='linedate_completed_$row[id]' style='width:80px' class='date_picker_completed' value='".(strtotime($row['linedate_completed_date']) > 0 ? date("m/d/Y", strtotime($row['linedate_completed'])) : "")."' onchange=\"js_update_stop_commpleted($row[id])\"></td>
					<td nowrap><input name='linedate_completed_time_$row[id]' id='linedate_completed_time_$row[id]' style='width:80px' class='time_picker_completed' value='".($row['linedate_completed_time'] > 0 ? $row['linedate_completed_time'] : "")."' onblur=\"js_update_stop_commpleted($row[id])\"></td>
					".(isset($_POST['dispatch_id']) ? "" : "<td><a href='javascript:delete_stop($row[id])'><img src='images/delete_small.png' alt='Delete Stop' title='Delete Stop' style='border:0'></a></td>")."
				</tr>
			";
		}
		$disphtml .= "</table>";
		
		
		$return_var = "
			<DispHTML><![CDATA[$disphtml]]></DispHTML>
			
		";
		display_xml_response($return_var);
	}
	

	
	function manage_stop() {
		global $datasource;

		if($_POST['pickup_eta'] == '') $_POST['pickup_eta'] = '0000-00-00 ';
		if($_POST['pickup_pta'] == '') $_POST['pickup_pta'] = '0000-00-00 ';
		if($_POST['pickup_eta_time'] != '') $_POST['pickup_eta'] .= $_POST['pickup_eta_time'];
		if($_POST['pickup_pta_time'] != '') $_POST['pickup_pta'] .= $_POST['pickup_pta_time'];
		//if($_POST['dropoff_eta_time'] != '') $_POST['dropoff_eta'] .= $_POST['dropoff_eta_time'];
		//if($_POST['dropoff_pta_time'] != '') $_POST['dropoff_pta'] .= $_POST['dropoff_pta_time'];
		
		if($_POST['stop_id'] == 0) {
			// new stop, add the initial entry, then update the others
			$sql = "
				insert into load_handler_stops
					(load_handler_id,
					created_by_user_id,
					deleted,
					linedate_added)
					
				values ('".sql_friendly($_POST['load_id'])."',
					'".sql_friendly($_SESSION['user_id'])."',
					0,
					now())
			";
			simple_query($sql);
			
			$_POST['stop_id'] = mysqli_insert_id($datasource);
		}
		
		$sql = "
			update load_handler_stops
			set shipper_name = '".sql_friendly($_POST['shipper'])."',
				shipper_address1 = '".sql_friendly($_POST['shipper_address1'])."',
				shipper_address2 = '".sql_friendly($_POST['shipper_address2'])."',
				shipper_city = '".sql_friendly($_POST['shipper_city'])."',
				shipper_state = '".sql_friendly($_POST['shipper_state'])."',
				shipper_zip = '".sql_friendly($_POST['shipper_zip'])."',
				stop_phone = '".sql_friendly($_POST['stop_phone'])."',
				directions = '".sql_friendly($_POST['directions'])."',
				stop_type_id = '".sql_friendly($_POST['stop_type'])."',
				linedate_pickup_eta = '".(strtotime($_POST['pickup_eta']) > 0 ? date("Y-m-d H:i:s", strtotime($_POST['pickup_eta'])) : '0000-00-00')."',
				linedate_pickup_pta = '".(strtotime($_POST['pickup_pta']) > 0 ? date("Y-m-d H:i:s", strtotime($_POST['pickup_pta'])) : '0000-00-00')."'
				
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		simple_query($sql);
		
		update_origin_dest($_POST['load_id']);
		
		$return_var = "
			<rslt><![CDATA[1]]></rslt>
			<StopID>$_POST[stop_id]</StopID>
		";
		display_xml_response($return_var);			
		
	}
	
	function load_stop_id() {
		$sql = "
			select *
			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$return_var = "
			<rslt></rslt>
			<StopType><![CDATA[$row[stop_type_id]]]></StopType>
			<ShipperName><![CDATA[$row[shipper_name]]]></ShipperName>
			<ShipperAddress1><![CDATA[$row[shipper_address1]]]></ShipperAddress1>
			<ShipperAddress2><![CDATA[$row[shipper_address2]]]></ShipperAddress2>
			<ShipperCity><![CDATA[$row[shipper_city]]]></ShipperCity>
			<ShipperState><![CDATA[$row[shipper_state]]]></ShipperState>
			<ShipperZip><![CDATA[$row[shipper_zip]]]></ShipperZip>
			<ShipperPhone><![CDATA[$row[stop_phone]]]></ShipperPhone>
			<Directions><![CDATA[$row[directions]]]></Directions>
			<time>".strtotime($row['linedate_pickup_eta'])."</time>
			<PickupETA>".(strtotime($row['linedate_pickup_eta']) <= 0 ? '' : date("m/d/Y", strtotime($row['linedate_pickup_eta'])))."</PickupETA>
			<PickupETATime>".time_prep($row['linedate_pickup_eta'])."</PickupETATime>
			<PickupPTA>".(strtotime($row['linedate_pickup_pta']) <= 0 ? '' : date("m/d/Y", strtotime($row['linedate_pickup_pta'])))."</PickupPTA>
			<PickupPTATime>".time_prep($row['linedate_pickup_pta'])."</PickupPTATime>
		";
		
		display_xml_response($return_var);	
	}
	
	function delete_stop() {
		$sql = "
			update load_handler_stops
			set deleted = 1
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		simple_query($sql);
		
		$sql = "
			select load_handler_id
			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data_load_id = simple_query($sql);
		$row_load_id = mysqli_fetch_array($data_load_id);		
		update_origin_dest($row_load_id['load_handler_id']);
		
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);	
	}
	
	function update_stop_dispatch() {
		$sql = "
			update load_handler_stops
			set trucks_log_id = '".sql_friendly($_POST['trucks_log_id'])."'
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		simple_query($sql);

		$sql = "
			select load_handler_id
			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data_load_id = simple_query($sql);
		$row_load_id = mysqli_fetch_array($data_load_id);		
		update_origin_dest($row_load_id['load_handler_id']);
		
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);
	}
	
	function update_predispatch() {
		
		$_POST['predispatch_odometer'] = money_strip($_POST['predispatch_odometer']);
		
		$sql = "
			update load_handler
			set predispatch_odometer = ".(is_numeric($_POST['predispatch_odometer']) ? "'$_POST[predispatch_odometer]'" : 0).",
				predispatch_city = '".sql_friendly($_POST['predispatch_city'])."',
				predispatch_state = '".sql_friendly($_POST['predispatch_state'])."',
				predispatch_zip = '".sql_friendly($_POST['predispatch_zip'])."'
				
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function update_stop_odometer() {
		
		$_POST['odometer_reading'] = money_strip($_POST['odometer_reading']);
		
		if(!is_numeric($_POST['odometer_reading'])) {
			display_xml_response("<rslt>0</rslt>");
			die;
		}
		
		$sql = "
			update load_handler_stops
			set odometer_reading = '".sql_friendly($_POST['odometer_reading'])."'
			where id = '".sql_friendly($_POST['stop_id'])."'
			limit 1
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}

	function update_stop_completed() {
		if($_POST['linedate_completed'] == '') {
			$_POST['linedate_completed'] = '0000-00-00';
		} else {
			$_POST['linedate_completed'] = date("Y-m-d", strtotime($_POST['linedate_completed']));
		}
		
		if($_POST['linedate_completed_time'] == '') $_POST['linedate_completed_time'] = '';
		
		$sql = "
			update load_handler_stops
			set linedate_completed = '".sql_friendly($_POST['linedate_completed'])." ".sql_friendly($_POST['linedate_completed_time'])."'
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		simple_query($sql);
		
		$sql = "
			select load_handler_id
			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data_load_id = simple_query($sql);
		$row_load_id = mysqli_fetch_array($data_load_id);
		update_origin_dest($row_load_id['load_handler_id']);
		
		$return_var = "<rslt>1</rslt><SQL><![CDATA[$sql]]></SQL><DT>$_POST[linedate_completed] $_POST[linedate_completed_time]</DT>";
		
		display_xml_response($return_var);
	}	
	
	
	function load_handler_quick_create() {
		global $defaultsarray;
		
		if($_POST['customer_name'] != '') {
			// adding a new customer, add a quick customer
			$sql = "
				insert into customers
					(name_company,
					contact_email,
					phone_work,
					address1,
					address2,
					city,
					state,
					zip,
					active,
					deleted)
					
				values ('".sql_friendly($_POST['customer_name'])."',
					'".sql_friendly($_POST['customer_email'])."',
					'".sql_friendly($_POST['customer_phone'])."',
					'".sql_friendly($_POST['customer_address1'])."',
					'".sql_friendly($_POST['customer_address2'])."',
					'".sql_friendly($_POST['customer_city'])."',
					'".sql_friendly($_POST['customer_state'])."',
					'".sql_friendly($_POST['customer_zip'])."',
					1,
					0)
			";
			simple_query($sql);
			
			$_POST['customer_id'] = mysql_insert_id();
			
			if($defaultsarray['sicap_integration'] == 1) sicap_update_customers($_GET['eid']);
		}
		
		$sql = "
			insert into load_handler
				(linedate_added,
				customer_id,
				created_by_id)
				
			values (now(),
				'".sql_friendly($_POST['customer_id'])."',
				'".sql_friendly($_SESSION['user_id'])."')
		";
		simple_query($sql);
		
		$load_id = mysql_insert_id();
		
		$return_var = "<rslt>1</rslt><LoadID>$load_id</LoadID><CustomerID>$_POST[customer_id]</CustomerID>";
		
		display_xml_response($return_var);
	}
	
	function add_dispatch_expense() {
		$sql = "
			insert into dispatch_expenses
				(linedate_added,
				added_by_user_id,
				dispatch_id,
				expense_type_id,
				expense_amount,
				expense_desc,
				deleted)
				
			values (now(),
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($_POST['dispatch_id'])."',
				'".sql_friendly($_POST['expense_type'])."',
				'".sql_friendly($_POST['expense_amount'])."',
				'".sql_friendly($_POST['expense_desc'])."',
				0)
		";
		simple_query($sql);
		
		$expense_id = mysql_insert_id();
		
		$load_id = get_load_id_from_dispatch_id($_POST['dispatch_id']);
		update_origin_dest($load_id);
		
		$return_var = "<rslt>1</rslt><ExpenseID>$expense_id</ExpenseID>";
		
		display_xml_response($return_var);
	}
	
	function load_dispatch_expenses() {
		$sql = "
			select dispatch_expenses.*,
				option_values.fvalue as expense_type
			
			from dispatch_expenses, option_values
			where dispatch_expenses.dispatch_id = '".sql_friendly($_POST['dispatch_id'])."'
				and dispatch_expenses.deleted = 0
				and dispatch_expenses.expense_type_id = option_values.id
			order by dispatch_expenses.linedate_added
		";
		$data = simple_query($sql);
		
		
		$return_var = '';
		if(!mysqli_num_rows($data)) {
			$return_var .= "No expenses found for this dispatch";
		} else {
			$return_var .= "
				<table class='section2' style='width:100%'>
				<tr>
					<td><b>Expense Type</b></td>
					<td><b>Description</b></td>
					<td align='right'><b>Amount</b></td>
				</tr>
			";
			$expense_total = 0;
			while($row = mysqli_fetch_array($data)) {
				$expense_total += $row['expense_amount'];
				$return_var .= "
					<tr id='row_expense_$row[id]'>
						<td>$row[expense_type]</td>
						<td>$row[expense_desc]</td>
						<td align='right'>".money_format('',$row['expense_amount'])."</td>
						<td><a href='javascript:delete_dispatch_expense($row[id])'><img src='images/delete_sm.gif' title='Delete Expense' alt='Delete Expense' style='border:0'></a></td>
					</tr>
				";
			}
			$return_var .= "
				<tr>
					<td colspan='5'><hr></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td align='right'><b>Total Expenses: ".money_format('',$expense_total)."</b></td>
				</tr>
				</table>
			";
		}
		
		$return_var = "<rslt>1</rslt><HTML><![CDATA[$return_var]]></HTML>";
		
		display_xml_response($return_var);
	}
	
	function delete_dispatch_expense() {
		$sql = "
			update dispatch_expenses
			set deleted = 1
			where id = '".sql_friendly($_POST['expense_id'])."'
		";
		simple_query($sql);
		
		$sql = "
			select dispatch_id
			
			from dispatch_expenses
			where id = '".sql_friendly($_POST['expense_id'])."'
		";
		$data_dispatch_id = simple_query($sql);
		$row_dispatch_id = mysqli_fetch_array($data_dispatch_id);
		
	
		$load_id = get_load_id_from_dispatch_id($row_dispatch_id['dispatch_id']);
		update_origin_dest($load_id);
		
		$return_var = "<rslt>1</rslt>";
		
		display_xml_response($return_var);
	}
	
	function load_dispatchs() {
		
		$pcm = new COM("PCMServer.PCMServer") or die ("connection create fail");
		
		if($_POST['load_id'] == '' || $_POST['load_id'] == 0) {
			// no load specified, don't go any further
			$return_var = "<rslt>1</rslt>";
			
			display_xml_response($return_var);
			return;
		}
		
		$sql = "
			select trucks_log.*,
				trucks.name_truck,
				trailers.trailer_name,
				concat(drivers.name_driver_first, ' ', drivers.name_driver_last) as driver_name
			
			from trucks_log
				left join trucks on trucks_log.truck_id = trucks.id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join drivers on drivers.id = trucks_log.driver_id
			where trucks_log.load_handler_id = '".sql_friendly($_POST['load_id'])."'
				and trucks_log.deleted = 0
			
			order by trucks_log.linedate
		";
		$data_dispatch = simple_query($sql);		
		
		$html = "
			<table width='100%'>
			<tr>
				<td><b>ID</b></td>
				<td><b>Truck</b></td>
				<td><b>Trailer</b></td>
				<td><b>Driver</b></td>
				<td align='right'><b>PC*M</b></td>
				<td align='right'><b>Miles</b></td>
				<td align='right'><b>Deadhead</b></td>
				<td>&nbsp;</td>
				<td><b>Origin</b></td>
				<td><b>Dest</b></td>
				<td><b>Date</b></td>
				<td><b>Cost</b></td>
				<td><b>Profit</b></td>
			</tr>
		";
		if(!isset($data_dispatch) || !mysqli_num_rows($data_dispatch) ) { 
			$html .= "
				<tr>
					<td colspan='10'>
						No dispatches associated with this load yet
					</td>
				</tr>
			";
		} else {
			$total_loaded_miles = 0;
			$total_deadhead_miles = 0;
			$last_dispatch_id = 0;
			$total_pcm_miles = 0;
			while($row_dispatch = mysqli_fetch_array($data_dispatch)) {
				$total_loaded_miles += $row_dispatch['miles'];
				$total_deadhead_miles += $row_dispatch['miles_deadhead'];
				$total_pcm_miles += $row_dispatch['pcm_miles'];
				$html .= "
					<tr>
						<td><a href='javascript:void(0)' onclick='manage_dispatch($row_dispatch[id])' ".($row_dispatch['dispatch_completed'] ? "class='dispatch_completed'" : "").">$row_dispatch[id]</a></td>
						<td>$row_dispatch[name_truck]</td>
						<td>$row_dispatch[trailer_name]</td>
						<td>$row_dispatch[driver_name]</td>
						<td align='right'>".number_format($row_dispatch['pcm_miles'])."</td>
						<td align='right'>".number_format($row_dispatch['miles'])."</td>
						<td align='right'>".number_format($row_dispatch['miles_deadhead'])."</td>
						<td></td>
						<td>$row_dispatch[origin], $row_dispatch[origin_state]</td>
						<td>$row_dispatch[destination], $row_dispatch[destination_state]</td>
						<td>".date("n-j-Y", strtotime($row_dispatch['linedate']))."</td>
						<td>$".money_format('',$row_dispatch['cost'])."</td>
						<td>$".money_format('',$row_dispatch['profit'])."</td>
					</tr>
				";
			}
			$html .= "
				<tr>
					<td colspan='4'></td>
					<td align='right' style='border-top:1px black solid'>".number_format($total_pcm_miles)."</td>
					<td align='right' style='border-top:1px black solid'>".number_format($total_loaded_miles)."</td>
					<td align='right' style='border-top:1px black solid'>".number_format($total_deadhead_miles)."</td>
				</tr>
			";
		}
		$html .= "</table>";
				
		$return_var = "<rslt>1</rslt><HTML><![CDATA[$html]]></HTML>";
		
		display_xml_response($return_var);
	}
	
	function search_stop_address() {
		$sql = "
			select load_handler_stops.*
				
			from load_handler_stops, load_handler
			where load_handler.id = load_handler_stops.load_handler_id
				and shipper_name like '%".sql_friendly($_GET['q'])."%'
				and load_handler_stops.deleted = 0
				and load_handler.deleted = 0
				and load_handler_stops.ignore_address = 0
			order by shipper_name, shipper_address1, id desc
		";
		$data = simple_query($sql);
		
		$last_shipper_check = '';
		
		while($row = mysqli_fetch_array($data)) {
			$shipper_check = "$row[shipper_name] $row[shipper_address1]";
			// create a simple bit of code to check for and remove duplicates
			if($last_shipper_check != $shipper_check) {
				$last_shipper_check = $shipper_check;
				echo "$row[shipper_name]|$row[shipper_address1] ($row[shipper_city], $row[shipper_state])|$row[id]\n";
			}
		}
	}
	
	function load_address_by_stop_id() {
		$sql = "
			select *
			
			from load_handler_stops
			where id = '".sql_friendly($_POST['stop_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$return_var = "
			<rslt>1</rslt>
			<Address1><![CDATA[$row[shipper_address1]]]></Address1>
			<Address2><![CDATA[$row[shipper_address2]]]></Address2>
			<City><![CDATA[$row[shipper_city]]]></City>
			<State><![CDATA[$row[shipper_state]]]></State>
			<Zip><![CDATA[$row[shipper_zip]]]></Zip>
			<Directions><![CDATA[$row[directions]]]></Directions>
			<Phone><![CDATA[$row[stop_phone]]]></Phone>
		";
		
		display_xml_response($return_var);	
	}
	
	function clear_address_history() {
		// clear out a specific address from the load_handler_stops (in case of a typo, or other error) 
		
		$sql = "
			select shipper_name,
				shipper_address1
			
			from load_handler_stops
			where id = '".sql_friendly($_POST['address_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);		
		
		$sql = "
			update load_handler_stops
			set ignore_address = 1
			where shipper_name = '".sql_friendly($row['shipper_name'])."'
				and shipper_address1 = '".sql_friendly($row['shipper_address1'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");	
	}
	
	function load_driver_expenses() {
		$sql = "
			select *
			
			from drivers
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		$data_info = simple_query($sql);
		$row_info = mysqli_fetch_array($data_info);
		
		$sql = "
			select drivers_expenses.*,
				option_values.fvalue as expense_type
			
			from drivers_expenses
				left join option_values on option_values.id = drivers_expenses.expense_type_id
			where drivers_expenses.driver_id = '".sql_friendly($_POST['driver_id'])."'
				and drivers_expenses.deleted = 0
			order by drivers_expenses.linedate desc
			limit 20
		";
		$data = simple_query($sql);
		
		$html = "
			<table class='admin_menu2'>
			<tr>
				<td colspan='5'>
					<div class='section_heading'>
						Driver Expenses<br>
						Driver: $row_info[name_driver_first] $row_info[name_driver_last]
					</div>
					
				</td>
			</tr>
		";
		
		if(!mysqli_num_rows($data)) {
			$html .= "
				<tr>
					<td colspan='10'>
						<span class='alert'>No expenses found for this driver</span>
					</td>
				</tr>
			";
		} else {
		
			$html .= "
				<tr>
					<th>Type</th>
					<th>Date</th>
					<th align='right'>Cost</th>
					<th align='right'>Billable</th>
					
					<th>Description</th>
					<th></th>
				</tr>
			";
			while($row = mysqli_fetch_array($data)) {
				$html .= "
					<tr id='driver_expense_entry_$row[id]'>
						<td>$row[expense_type]</td>
						<td>".date("M j, Y", strtotime($row['linedate']))."</td>
						<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $".money_format('',$row['amount'])."</td>
						<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $".money_format('',$row['amount_billable'])."</td>
						<td>$row[desc_long]</td>
						<td>
							<a href='javascript:void(0)' onclick='confirm_delete_driver_expense($row[id])'><img src='images/delete_small.png' alt='Delete driver expense' title='Delete driver expense' style='border:0'>
						</td>
					</tr>
				";
			}
		}
		$html .= "</table>";
		
		display_xml_response("<rslt>1</rslt><html><![CDATA[$html]]></html>");	
	}
	
	function add_driver_expense() {
		
		if(!isset($_POST['driver_expense_id'])) $_POST['driver_expense_id'] = 0;
		
		if($_POST['driver_expense_id'] == 0) {
			$sql = "
				insert into drivers_expenses
					(linedate_added,
					created_by_user_id)
					
				values (now(),
					'".sql_friendly($_SESSION['user_id'])."')
			";
			simple_query($sql);
			
			$_POST['driver_expense_id'] = mysql_insert_id();
		}
		
		$sql = "
			update drivers_expenses
			set driver_id = '".sql_friendly($_POST['driver_id'])."',
				linedate = '".date("Y-m-d", strtotime($_POST['linedate']))."',
				desc_long = '".sql_friendly($_POST['desc_long'])."',
				expense_type_id = '".sql_friendly($_POST['expense_type_id'])."',
				amount = '".sql_friendly($_POST['amount'])."',
				amount_billable = '".sql_friendly($_POST['amount_billable'])."',
				payroll = '".(isset($_POST['payroll']) && $_POST['payroll'] == '1' ? '1' : '0')."'
			
			where id = '".sql_friendly($_POST['driver_expense_id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function delete_driver_expense() {
		$sql = "
			update drivers_expenses
			
			set deleted = 1
			where id = '".sql_friendly($_POST['driver_expense_id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function get_daily_cost_ajax() {
		if(!isset($_POST['truck_id'])) $_POST['truck_id'] = 0;
		if(!isset($_POST['trailer_id'])) $_POST['trailer_id'] = 0;
		$daily_cost = get_daily_cost($_POST['truck_id'], $_POST['trailer_id']);
		
		display_xml_response("<rslt>1</rslt><DailyCost>$daily_cost</DailyCost>");
	}
	
	function save_odometer_reading() {
		$sql = "
			insert trucks_odometer
				(truck_id,
				linedate_added,
				linedate,
				odometer)
				
			values ('".sql_friendly($_POST['truck_id'])."',
				now(),
				'".date("Y-m-d", strtotime($_POST['linedate']))."',
				'".sql_friendly($_POST['odometer'])."')
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function truck_odometer_alert() {
		// we're within 4 days of the end of the month, start prompting for odometer readings

		$disp_limit = 100000;
		if(isset($_POST['disp_limit'])) $disp_limit = $_POST['disp_limit'];

		$sql = "
			select *
			
			from trucks
			where active = 1
				and deleted = 0
				and trucks.id not in (select trucks_odometer.truck_id from trucks_odometer where deleted = 0 and linedate >= '".date("Y-m-1")."' and linedate <= '".date("Y-m-t")."')
			order by name_truck
		";
		$data_trucks = simple_query($sql);

		$return_var = "<b>".mysqli_num_rows($data_trucks)." truck(s) need odometer readings</b><br>";

		$counter = 0;
		while($row_truck = mysqli_fetch_array($data_trucks)) {
			$counter++;
			if($counter > $disp_limit) {
				break;
			}
			$return_var .= "<a href='javascript:void(0)' onclick=\"enter_odo($row_truck[id],'$row_truck[name_truck]')\">$row_truck[name_truck]</a><br>";
		}

		display_xml_response("<rslt>1</rslt><TruckList><![CDATA[$return_var]]></TruckList><TruckCount>".mysqli_num_rows($data_trucks)."</TruckCount>");
	}
	
	function load_odometer_history() {
		$sql = "
			select *
			
			from trucks
			where id = '".sql_friendly($_POST['truck_id'])."'
		";
		$data_truck = simple_query($sql);
		$row_truck = mysqli_fetch_array($data_truck);
		
		$sql = "
			select *
			
			from trucks_odometer
			where truck_id = '".sql_friendly($_POST['truck_id'])."'
				and deleted = 0
			order by linedate desc
			limit 12
		";
		$data = simple_query($sql);
		
		$return_var = "
			<table class='admin_menu1' style='width:350px'>
			<tr>
				<td colspan='3' class='border_bottom'><div class='section_heading'>Odometer History - <a href='javascript:void(0)' onclick=\"enter_odo($_POST[truck_id],'$row_truck[name_truck]')\">Add Odometer Reading</a></div></td>
			</tr>

			<tr>
				<td><b>Date</b></td>
				<td><b>Odometer</b></td>
				<td></td>
			</tr>
			
		";
		while($row = mysqli_fetch_array($data)) {
			$return_var .= "
				<tr>
					<td>".date("M j, Y", strtotime($row['linedate']))."</td>
					<td>".number_format($row['odometer'])."</td>
					<td><a href='javascript:void(0)' onclick='confirm_delete_odometer_reading($row[id])'><img src='images/delete_sm.gif' style='border:0'></a></td>
				</tr>
			";
		}
		$return_var .= "</table>
			<script type='text/javascript'>
				$('#odometer_date_entry').datepicker();
			</script>
		";
		
		display_xml_response("<rslt>1</rslt><OdometerHistory><![CDATA[$return_var]]></OdometerHistory>");
	}
	
	function delete_odometer_entry() {
		$sql = "
			update trucks_odometer
			set deleted = 1
			where id = '".sql_friendly($_POST['odometer_entry_id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function load_driver_unavailable() {
		$sql = "
			select *
			
			from drivers_unavailable
			where driver_id = '".sql_friendly($_POST['driver_id'])."'
				and deleted = 0
			
			order by linedate_start
		";
		$data = simple_query($sql);
		
		$html = "
			<table class='admin_menu2'>
			<tr>
				<td colspan='5'>
					<div class='section_heading'>
						Driver Unavailable History<br>
					</div>
					
				</td>
			</tr>
			<tr>
				<td colspan='5'>
					<a href='javascript:add_driver_unavailable($_POST[driver_id])'><img src='images/add.gif' style='border:0'> Add new unavailability</a>
				</td>
			</tr>
		";
		
		if(!mysqli_num_rows($data)) {
			$html .= "
				<tr>
					<td colspan='10'>
						<span class='alert'>No entries found for this driver</span>
					</td>
				</tr>
			";
		} else {
		
			$html .= "
				<tr>
					<th>Date Start</th>
					<th>Date End</th>
					<th></th>
				</tr>
			";
			while($row = mysqli_fetch_array($data)) {
				$html .= "
					<tr id='driver_unavailability_entry_$row[id]'>
						<td>".date("M j, Y", strtotime($row['linedate_start']))."</td>
						<td>".date("M j, Y", strtotime($row['linedate_end']))."</td>
						<td>
							<a href='javascript:void(0)' onclick='confirm_delete_driver_unavailable($row[id])'<img src='images/delete_small.png' alt='Delete driver unavailablity' title='Delete driver unavailablity' style='border:0'>
						</td>
					</tr>
				";
			}
		}
		$html .= "</table>";
		
		display_xml_response("<rslt>1</rslt><html><![CDATA[$html]]></html>");	
	}
	
	function add_driver_unavailability() {
		$sql = "
			insert into drivers_unavailable
				(driver_id,
				deleted,
				linedate_added,
				linedate_start,
				linedate_end,
				added_by)
				
			values ('".sql_friendly($_POST['driver_id'])."',
				0,
				now(),
				'".date("Y-m-d", strtotime($_POST['linedate_start']))."',
				'".date("Y-m-d", strtotime($_POST['linedate_end']))."',
				'".sql_friendly($_SESSION['user_id'])."')
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function delete_driver_unavailability() {
		$sql = "
			update drivers_unavailable
			set deleted = 1
			where id = '".sql_friendly($_POST['id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function driver_unavailable() {
		// set a flag so that the driver won't show on the available driver list
		
		$sql = "
			update drivers
			set hide_available = 1
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function detach_truck() {
		$sql = "
			update drivers
			set attached_truck_id = 0
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function detach_trailer() {
		$sql = "
			update drivers
			set attached_trailer_id = 0
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function load_attached_equipment() {
		// if the driver changes on the manage dispatch screen
		// check to see if the new driver has a truck or trailer attached to him
		
		
		$sql = "
			select *
			
			from drivers
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$retvar = "
			<AttachedTruckID>$row[attached_truck_id]</AttachedTruckID>
			<AttachedTrailerID>$row[attached_trailer_id]</AttachedTrailerID>
		";
		
		display_xml_response("<rslt>1</rslt>$retvar");
	}
	
	function get_detach_info() {
		
		$retvar = "";
		
		$sql = "
			select *
			
			from drivers
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		$data_driver = simple_query($sql);
		$row_driver = mysqli_fetch_array($data_driver);
		
		$retvar .= "<DriverName><![CDATA[$row_driver[name_driver_first] $row_driver[name_driver_last]]]></DriverName>";
		
		if(isset($_POST['truck_id'])) {
			$sql = "
				select *
				
				from trucks
				where id = '".sql_friendly($_POST['truck_id'])."'
			";
			$data_truck = simple_query($sql);
			$row_truck = mysqli_fetch_array($data_truck);
			
			$retvar .= "<TruckName><![CDATA[$row_truck[name_truck]]]></TruckName>";
		}
		
		
		if(isset($_POST['trailer_id'])) {
			$sql = "
				select *
				
				from trailers
				where id = '".sql_friendly($_POST['trailer_id'])."'
			";
			$data_trailer = simple_query($sql);
			$row_trailer = mysqli_fetch_array($data_trailer);
			
			$retvar .= "<TrailerName><![CDATA[$row_trailer[trailer_name]]]></TrailerName>";
		}
		
		display_xml_response("<rslt>1</rslt>$retvar");
	}
	
	function rename_scanned_load() {
		$sql = "
			update log_scan_loads
			set load_id = '".sql_friendly($_POST['load_number'])."'
			where id = '".sql_friendly($_POST['id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function delete_scanned_load() {
		$sql = "
			update log_scan_loads
			set deleted = 1
			where id = '".sql_friendly($_POST['id'])."'
				and filename = '".sql_friendly($_POST['filename'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function driver_load_flag() {
		$sql = "
			update drivers
			set driver_has_load = '".sql_friendly($_POST['load_flag'])."',
				linedate_driver_has_load = ".($_POST['load_flag'] == 1 ? "now()" : "'0000-00-00'")."
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function ajax_sicap_create_invoice() {
		
		if(!isset($_POST['load_id'])) {
			display_xml_response("<rslt>0</rslt><rsltmsg>Missing Load ID Field</rsltmsg>");
			die;
		}
		
		if(isset($_POST['invoice_id']) && $_POST['invoice_id'] > 0) {
			// add to an existing sicap_invoice
			sicap_create_invoice($_POST['load_id'], $_POST['invoice_id']);
		} else {
			// create a new sicap_invoice
			sicap_create_invoice($_POST['load_id']);
		}
		
		// get the invoice number
		$sql = "
			select sicap_invoice_number,
				linedate_invoiced
			
			from load_handler
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		$data = simple_query($sql);
		
		$row = mysqli_fetch_array($data);
		
		display_xml_response("<rslt>1</rslt><SICAPInvoiceNumber>$row[sicap_invoice_number]</SICAPInvoiceNumber><InvoiceDate><![CDATA[".date("m/d/Y", strtotime($row['linedate_invoiced']))."]]></InvoiceDate>");
	}

	function update_driver_notes() {
		$sql = "
			update drivers
			set available_notes = '".sql_friendly($_POST['driver_notes'])."',
				linedate_available_notes = now()
				
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		simple_query($sql);
		
		display_xml_response("<rslt>1</rslt>");
	}
	
	function get_driver_notes() {
		$sql = "
			select *
			
			from drivers
			where id = '".sql_friendly($_POST['driver_id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		display_xml_response("<rslt>1</rslt><html><![CDATA[$row[available_notes]]]></html><modified_date>".date("M j Y - h:i a", strtotime($row['linedate_available_notes']))."</modified_date>");
	}
	
	function ajax_sicap_delete_invoice() {
		$msg = '';
		$sql = "
			select sicap_invoice_number
			
			from load_handler
			where id = '".sql_friendly($_POST['load_id'])."'
		";
		$data = simple_query($sql);
		
		if(!mysqli_num_rows($data)) {
			display_xml_response("<rslt>1</rslt><rsltmsg>Could not locate load</rsltmsg>");
			die;
		}
		
		$row = mysqli_fetch_array($data);
		
		if($row['sicap_invoice_number'] == '') {
			display_xml_response("<rslt>1</rslt><rsltmsg>Could not locate an accounting Invoice associated with this load</rsltmsg>");
			die;
		}
		
		$sql = "
			update load_handler
			set sicap_invoice_number = '',
				linedate_invoiced = '0000-00-00',
				invoice_number = ''
			where id = '".sql_friendly($_POST['load_id'])."'
			limit 1
		";
		simple_query($sql);
		
		// see if there are any other loads that use this SICAP invoice number, if so, alert the user that manual updates will be necessary
		$sql = "
			select id
			
			from load_handler
			where deleted = 0
				and sicap_invoice_number = '".sql_friendly($row['sicap_invoice_number'])."'
		";
		$data_check = simple_query($sql);
		
		if(mysqli_num_rows($data_check)) {
			$msg = "
				This invoice has multiple loads associated with it. So, the invoice was not deleted, but the link between the trucking
				system has been cleared. You will need to go into the accounting system and delete entries associated with this load manually.
			";
			simple_query($sql);
		} else {
			// this is the only load with this invoice number, so go ahead and delete the invoice
			$api = new sicap_api_connector();
			
			$api->addParam("InvoiceID", $row['sicap_invoice_number']);
			$api->command = "delete_invoice";
			
			$rslt = $api->execute();
		}
		
		display_xml_response("<rslt>1</rslt><rsltmsg>$msg</rsltmsg>");
	}
	
	function get_driver_rate_per_mile() {
		$sql = "
		";
	}
?>