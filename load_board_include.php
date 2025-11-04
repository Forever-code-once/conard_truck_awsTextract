<?
	function truck_section_display_sub_list($disp_type, $id_list, $startdate) {
		
		global $datasource;
		global $driver_avail_array;
		
		$sql_filter = "";
		$sql_id_filter = "";
		$sql_filter2 = "";
		$sql_id_filter2 = "";
		$sql_filter_drop_tl_cust = "";

		$cust_id_array = explode(",",$id_list);
		if($id_list != "") {
			$sql_filter = " and trucks_log.customer_id not in ($id_list) ";
			$sql_filter2 = " and load_handler.customer_id not in ($id_list) ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id not in ($id_list) ";
		}
		
		if($disp_type > 0) {
			$sql_id_filter = " and trucks_log.customer_id = '$disp_type' ";
			$sql_id_filter2 = " and load_handler.customer_id = '$disp_type' ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id = '$disp_type' ";
		}
		?>

				
					<?
						for($i=0;$i<31;$i++) {
							echo "
								<tr id='day_heading_$i'>
									<td colspan='20'><h3 style='margin:0;padding:0'>".date("M j, Y (l)", strtotime("+$i days", $startdate))."</h3></td>
								</tr>
								<tr>
								
							";
							/* pull out all the trucks in the log to display */
							$sql = "
								select trailers.trailer_name,
									trucks_log.location,
									trucks_log.dropped_trailer,
									trucks_log.load_handler_id,
									trucks_log.driver2_id,
									drivers.name_driver_first,
									drivers.name_driver_last,
									drivers.phone_cell,
									trucks.name_truck,
									trucks_log.id,
									trailers.id as trailer_id,
									trucks.id as truck_id,
									trucks_log.linedate_updated,
									trucks_log.linedate,
									trucks_log.color,
									trucks_log.has_load_flag,
									trucks_log.dispatch_completed,
									trucks_log.destination,
									trucks_log.destination_state,
									load_handler_stops.shipper_name,
									load_handler_stops.shipper_city,
									load_handler_stops.shipper_state,
									load_handler_stops.stop_type_id,
									load_handler_stops.linedate_pickup_eta,
									load_handler_stops.id as load_handler_stop_id,
									load_handler_stops.linedate_completed
								
								from trucks_log
									left join drivers on drivers.id = trucks_log.driver_id
									left join trucks on trucks.id = trucks_log.truck_id
									left join trailers on trailers.id = trucks_log.trailer_id
									left join load_handler_stops on load_handler_stops.trucks_log_id = trucks_log.id and load_handler_stops.deleted = 0
								where linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
									and trucks_log.deleted = 0
									
									$sql_filter
									$sql_id_filter
									
								order by trucks_log.dropped_trailer, trucks.name_truck, load_handler_stops.load_handler_id, load_handler_stops.linedate_pickup_eta
							";
							$data_log = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );		
							
							$sql = "
								select load_handler.*,
									customers.name_company
								
								from load_handler
									left join customers on customers.id = load_handler.customer_id
								where date_format(load_handler.linedate_pickup_eta, '%Y-%m-%d') = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
									and load_handler.deleted = 0
									and (
										load_handler.load_available = 1
										or 
										(select count(*) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id) = 0
									)
									
									$sql_filter2
									$sql_id_filter2
									
								order by load_handler.id, load_handler.linedate_pickup_eta
							";
							//if($_SERVER['REMOTE_ADDR'] == '69.137.72.167') echo("<p>$sql");
							$data_available_loads = simple_query($sql);
							

							
							$optional_day_flag = "";
							if($i == 0 || $i == 6 || date("W", strtotime("+$i days", $startdate)) != date("W", time())) $optional_day_flag = "optional_day";
							
							$extra_class = '';
							if(date("Y-m-d", strtotime("+$i days", $startdate)) == date("Y-m-d", time())) $extra_class = 'calendar_today';
							echo "<td align='left' class='truck_drop $extra_class $optional_day_flag' valign='top' nowrap linedate='".date("Y-m-d", strtotime("+$i days", $startdate))."'>";
							
							
							// see if we have any available drivers on this day
							foreach($driver_avail_array as $darray) {

								// get a list of pre-planed loads available for this driver
								$sql = "
									select load_handler.*,
										customers.name_company
									
									from load_handler, customers
									where load_handler.preplan = 1
										and customers.id = load_handler.customer_id
										and load_handler.deleted = 0
										and load_handler.preplan_driver_id = '".sql_friendly($darray['driver_id'])."'
								";
								$data_preplan = simple_query($sql);
								
								//echo "(".$darray['customer_id'].")";
								if(count($darray) > 1 && (($disp_type > 0 && $darray['customer_id'] == $disp_type) || ($disp_type == 0 && !in_array($darray['customer_id'], $cust_id_array)))) {
									//if(date("Y-m-d", strtotime($darray['linedate_completed'])) == date("Y-m-d", strtotime("+$i days", $startdate))) {
									if(date("Y-m-d") == date("Y-m-d", strtotime("+$i days", $startdate))) {
										echo "
											<div style='background-color:#6b9cff;font-weight:bold;color:white'>
												<a class='available_load_link' href='javascript:new_load()'>
													(Available) ".date("n/d", strtotime($darray['linedate_completed']))." $darray[name_driver_first] $darray[name_driver_last] ($darray[last_city])
												</a>
										";
										while($row_preplan = mysqli_fetch_array($data_preplan)) {
											echo "
												<br>&nbsp;&nbsp;&nbsp;&nbsp;
												<a class='available_load_link' href='javascript:edit_entry_truck(0,0,$row_preplan[id])'>
													$row_preplan[id] 
													- $row_preplan[name_company] 
													- $row_preplan[origin_city], $row_preplan[origin_state] ".date("H:i", strtotime($row_preplan['linedate_pickup_eta']))."
													- $row_preplan[dest_city], $row_preplan[dest_state]  ".date("H:i", strtotime($row_preplan['linedate_dropoff_eta']))."
												</a>
											";
										}
										echo "
											</div>
										";
									}
								}
							}

							
							$sql = "
								select *
								
								from notes
								where deleted = 0
									and customer_id = '$disp_type'
									and linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
								order by linedate_added
							";
							$data_notes_day = simple_query($sql);
							
							while($row_note = mysqli_fetch_array($data_notes_day)) {
								echo "
									<div class='note_entry'>
										<div note_id='$row_note[id]' onclick='edit_note_date($row_note[id],0,0)' class='note_entry_inside'>
										".trim_string($row_note['desc_long'], 45)."
										</div>
									</div>
								";
							}
							
							$loop_count = 0;
							$lh_used_id = array();
							while($row_available = mysqli_fetch_array($data_available_loads)) {
								if(!in_array($row_available['id'],$lh_used_id)) {
									echo "
										<div class='available_load_entry'>
											<a href='manage_load.php?load_id=$row_available[id]' target='view_load_".$row_available['id']."'>Available Load ($row_available[id]): $row_available[name_company]</a>
									";

									$lh_used_id[] = $row_available['id'];
									// get a list of all the stops for this load
									$sql = "
										select *
										
										from load_handler_stops
										where load_handler_id = '".sql_friendly($row_available['id'])."'
											and deleted = 0
										order by linedate_pickup_eta
									";
									$data_avail_stops = simple_query($sql);
									
									while($row_avail_stops = mysqli_fetch_array($data_avail_stops)) {
										echo "<div>
												<div style='float:left'>
												(".($row_avail_stops['stop_type_id'] == 1 ? "S" : "C").")
												".$row_avail_stops['shipper_name'].": $row_avail_stops[shipper_city], $row_avail_stops[shipper_state]
												</div>
												<div style='float:right'>".($row_avail_stops['linedate_pickup_eta'] > 0 ? " - " . date("M j, H:i", strtotime($row_avail_stops['linedate_pickup_eta'])) : "")."</div>
											</div>
										";
									}
									echo "
										<div style='clear:both'></div>
										</div>
										
									";
								}

							}
							$last_truck_log_id = 0;
							$loop_count = 0;
							while($row_log = mysqli_fetch_array($data_log)) {
								$loop_count++;
								$last_truck_log_id = $row_log['id'];
							
							
								$date_updated = strtotime(date("m/d/Y", strtotime($row_log['linedate_updated'])));
								$date_current = strtotime(date("m/d/Y", time()));

								$extra_class = '';
								$trailer_alert = false;
								if($date_updated < $date_current || $date_updated == '') {
									// hasn't been updated today, show an 'alert' background color
									// if this is a weekend or a monday, allow the days to go back a ways
								}
								if(strtotime($row_log['linedate']) < strtotime(date("Y-m-d", time()))) {
									$extra_class = 'update_alert';
									$trailer_alert = true;
								}
								
								if(!$trailer_alert && $row_log['dropped_trailer']) {
									$use_background_color = ";background-color:#33ff00";
								} elseif($row_log['color'] != '' && !$trailer_alert && $row_log['color'] != '#f8f3e4') {
									$use_background_color = ";background-color:$row_log[color]";
								} else {
									$use_background_color = '';
								}
								
								if($row_log['location'] != '') {
									$use_location = $row_log['location'];
								} else {
									$use_location = "$row_log[destination], $row_log[destination_state]";
								}
								
								
								
								if($row_log['dropped_trailer']) {
									echo "
										<div class='truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:300px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
											<span truck_id='$row_log[truck_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'>
											<a style='margin-right:10px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">TL DR</a>
											<span style='float:left'>".trim_string($use_location,45)."</span>
											<span style='float:right'>$row_log[trailer_name]</span>
											</span>
											<div style='clear:both'></div>
										</div>
									";
								} else {
									if($row_log['dispatch_completed']) {
										$use_image = "images/good.png";
									} elseif ($row_log['has_load_flag']) {
										$use_image = "images/inventory.png";
									} else {
										$use_image = "images/note.png";
									}
									echo "
										<div class='truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:300px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
											<span truck_id='$row_log[truck_id]' load_handler_id='$row_log[load_handler_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'>
											<img log_id='$row_log[id]' class='note_image' has_load_flag='$row_log[has_load_flag]' src='$use_image' border='0' style='margin-right:5px;float:left'>
											<div id='note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;min-width:350px;background-color:#eeeeee'>
											</div>
												".($row_log['driver2_id'] > 0 ? "(TEAM DRIVER)" : "")."
												".($row_log['load_handler_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/menu_system16.png' style='float:left' onclick=\"edit_entry_truck('',$row_log[id],0)\"></div>" : "")."
												<a style='margin-right:40px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">($row_log[load_handler_id]) $row_log[name_driver_first] $row_log[name_driver_last]</a>
												<span style='float:right;margin-right:5px'>$row_log[phone_cell]</span>
											
											<br>
											<span style='width:150px;float:left;margin-right:5px'>$row_log[name_truck]</span>
											<span style='float:right;margin-right:5px'>$row_log[trailer_name]</span>
											
											<br>
											".trim_string($use_location,45)."
											</span>
									";
									
									$counter_sub = 0;
									mysqli_data_seek($data_log,$loop_count-1);
									while($row_log_sub = mysqli_fetch_array($data_log)) {
										$counter_sub++;
										//echo "($row_log[load_handler_id] | $row_log_sub[load_handler_id] | $row_log_sub[load_handler_stop_id] | loop_count: $loop_count | counter: $counter_sub)";
										if($row_log['id'] != $row_log_sub['id'] || $row_log_sub['id'] == 0 || $row_log_sub['id'] == '') {
											if($counter_sub > 1) $counter_sub--;
											$loop_count = $loop_count + $counter_sub - 1;
											
											@mysqli_data_seek($data_log,$loop_count);
											break;
										} else {
											echo "<div>
													<div style='float:left".($row_log_sub['linedate_completed'] > 0 ? ";color:#888888" : "")."'>
													(".($row_log_sub['stop_type_id'] == 1 ? "S" : "C").") ($row_log_sub[load_handler_stop_id])
													".$row_log_sub['shipper_name'].": $row_log_sub[shipper_city], $row_log_sub[shipper_state]
													</div>
													<div style='float:right'>
											";
												if($row_log_sub['linedate_completed'] > 0) {
													echo "<span style='color:#888888'>".($row_log_sub['linedate_completed'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_completed'])) : "")."&nbsp;&nbsp;&nbsp</span>";
												} else {
													echo "&nbsp;&nbsp; <a href='javascript:update_stop_complete($row_log_sub[load_handler_stop_id])'>".
														($row_log_sub['linedate_pickup_eta'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_pickup_eta'])) : "")."&nbsp;&nbsp"
														."</a>";
												}
														
											echo "
													</div>
												</div>
												<div style='clear:both'></div>
											";
										}
									}
									//@mysqli_data_seek($data_log, $loop_count-1);
									echo "
										</div>
									";
								} // end of dropped trailer if
							} // end of while statement
							
							if(date("m/d/Y", strtotime("+$i days", $startdate)) == date("m/d/Y", time())) {
								// today's date
								
								// show any dropped trailer
								//and trailers_dropped.linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
								$sql = "
									select trailers_dropped.*,
										trailers.trailer_name
									
									from trailers_dropped, trailers
									where trailers.id = trailers_dropped.trailer_id
										
										and trailers_dropped.drop_completed = 0
										and trailers_dropped.deleted = 0
										
										$sql_filter_drop_tl_cust
										
								";
								$data_trailers_dropped = simple_query($sql);
								while($row_trailer_dropped = mysqli_fetch_array($data_trailers_dropped)) {
									echo "
										<div class='truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='background-color:#33ff00;min-width:300px;'>
											<span trailer_id='$row_trailer_dropped[trailer_id]' class='entry_info' dropped_trailer_id='$row_trailer_dropped[id]'>
											<a style='margin-right:10px;float:left' href=\"javascript:edit_dropped_trailer($row_trailer_dropped[id])\">TL DR</a>
											<span style='float:left'>".trim_string("$row_trailer_dropped[location_city], $row_trailer_dropped[location_state] $row_trailer_dropped[location_zip]",45)."</span>
											<span style='float:right'>$row_trailer_dropped[trailer_name]</span>
											</span>
											<div style='clear:both'></div>
										</div>
									";
								}
							}
							
							echo "&nbsp;</td>";
							
							echo "
							</tr>
							<tr><td colspan='20'><hr></td></tr>
							";
						}
					?>
				
		<?
	}
	
	function truck_section_display_sub_list_mrr_alt($disp_type, $id_list, $startdate, $name_company) {
		
		global $datasource;
		global $driver_avail_array;
		
		$sql_filter = "";
		$sql_id_filter = "";
		$sql_filter2 = "";
		$sql_id_filter2 = "";
		$sql_filter_drop_tl_cust = "";

		$cust_id_array = explode(",",$id_list);
		if($id_list != "") {
			$sql_filter = " and trucks_log.customer_id not in ($id_list) ";
			$sql_filter2 = " and load_handler.customer_id not in ($id_list) ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id not in ($id_list) ";
		}
		
		if($disp_type > 0) {
			$sql_id_filter = " and trucks_log.customer_id = '$disp_type' ";
			$sql_id_filter2 = " and load_handler.customer_id = '$disp_type' ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id = '$disp_type' ";
		}
		?>

				
					<?
						for($i=0;$i<31;$i++) {
							echo "
								<tr id='day_heading_$i'>
									<td colspan='20'><h3 style='margin:0;padding:0'>".date("M j, Y (l)", strtotime("+$i days", $startdate))."</h3></td>
								</tr>
								<tr>
								
							";
							/* pull out all the trucks in the log to display */
							$sql = "
								select trailers.trailer_name,
									trucks_log.location,
									trucks_log.dropped_trailer,
									trucks_log.load_handler_id,
									trucks_log.driver2_id,
									drivers.name_driver_first,
									drivers.name_driver_last,
									drivers.phone_cell,
									trucks.name_truck,
									trucks_log.id,
									trailers.id as trailer_id,
									trucks.id as truck_id,
									trucks_log.linedate_updated,
									trucks_log.linedate,
									trucks_log.color,
									trucks_log.has_load_flag,
									trucks_log.dispatch_completed,
									trucks_log.destination,
									trucks_log.destination_state,
									load_handler_stops.shipper_name,
									load_handler_stops.shipper_city,
									load_handler_stops.shipper_state,
									load_handler_stops.stop_type_id,
									load_handler_stops.linedate_pickup_eta,
									load_handler_stops.id as load_handler_stop_id,
									load_handler_stops.linedate_completed
								
								from trucks_log
									left join drivers on drivers.id = trucks_log.driver_id
									left join trucks on trucks.id = trucks_log.truck_id
									left join trailers on trailers.id = trucks_log.trailer_id
									left join load_handler_stops on load_handler_stops.trucks_log_id = trucks_log.id and load_handler_stops.deleted = 0
								where linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
									and trucks_log.deleted = 0
									
									$sql_filter
									$sql_id_filter
									
								order by trucks_log.dropped_trailer, trucks.name_truck, load_handler_stops.load_handler_id, load_handler_stops.linedate_pickup_eta
							";
							$data_log = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );		
							
							$sql = "
								select load_handler.*,
									customers.name_company
								
								from load_handler
									left join customers on customers.id = load_handler.customer_id
								where date_format(load_handler.linedate_pickup_eta, '%Y-%m-%d') = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
									and load_handler.deleted = 0
									and (
										load_handler.load_available = 1
										or 
										(select count(*) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id) = 0
									)
									
									$sql_filter2
									$sql_id_filter2
									
								order by load_handler.id, load_handler.linedate_pickup_eta
							";
							//if($_SERVER['REMOTE_ADDR'] == '69.137.72.167') echo("<p>$sql");
							$data_available_loads = simple_query($sql);
							

							
							$optional_day_flag = "";
							if($i == 0 || $i == 6 || date("W", strtotime("+$i days", $startdate)) != date("W", time())) $optional_day_flag = "optional_day";
							
							$extra_class = '';
							if(date("Y-m-d", strtotime("+$i days", $startdate)) == date("Y-m-d", time())) $extra_class = 'calendar_today';
							echo "<td align='left' class='truck_drop $extra_class $optional_day_flag' valign='top' nowrap linedate='".date("Y-m-d", strtotime("+$i days", $startdate))."'>";
							
							
							// see if we have any available drivers on this day
							foreach($driver_avail_array as $darray) {

								// get a list of pre-planed loads available for this driver
								$sql = "
									select load_handler.*,
										customers.name_company
									
									from load_handler, customers
									where load_handler.preplan = 1
										and customers.id = load_handler.customer_id
										and load_handler.deleted = 0
										and load_handler.preplan_driver_id = '".sql_friendly($darray['driver_id'])."'
								";
								$data_preplan = simple_query($sql);
								
								//echo "(".$darray['customer_id'].")";
								if(count($darray) > 1 && (($disp_type > 0 && $darray['customer_id'] == $disp_type) || ($disp_type == 0 && !in_array($darray['customer_id'], $cust_id_array)))) {
									//if(date("Y-m-d", strtotime($darray['linedate_completed'])) == date("Y-m-d", strtotime("+$i days", $startdate))) {
									if(date("Y-m-d") == date("Y-m-d", strtotime("+$i days", $startdate))) {
										echo "
											<div style='background-color:#6b9cff;font-weight:bold;color:white'>
												<a class='available_load_link' href='javascript:new_load()'>
													(Available) ".date("n/d", strtotime($darray['linedate_completed']))." $darray[name_driver_first] $darray[name_driver_last] ($darray[last_city])
												</a>
										";
										while($row_preplan = mysqli_fetch_array($data_preplan)) {
											echo "
												<br>&nbsp;&nbsp;&nbsp;&nbsp;
												<a class='available_load_link' href='javascript:edit_entry_truck(0,0,$row_preplan[id])'>
													$row_preplan[id] 
													- $row_preplan[name_company] 
													- $row_preplan[origin_city], $row_preplan[origin_state] ".date("H:i", strtotime($row_preplan['linedate_pickup_eta']))."
													- $row_preplan[dest_city], $row_preplan[dest_state]  ".date("H:i", strtotime($row_preplan['linedate_dropoff_eta']))."
												</a>
											";
										}
										echo "
											</div>
										";
									}
								}
							}

							
							$sql = "
								select *
								
								from notes
								where deleted = 0
									and customer_id = '$disp_type'
									and linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
								order by linedate_added
							";
							$data_notes_day = simple_query($sql);
							
							while($row_note = mysqli_fetch_array($data_notes_day)) {
								echo "
									<div class='note_entry'>
										<div note_id='$row_note[id]' onclick='edit_note_date($row_note[id],0,0)' class='note_entry_inside'>
										".trim_string($row_note['desc_long'], 45)."
										</div>
									</div>
								";
							}
							
							$loop_count = 0;
							$lh_used_id = array();
							while($row_available = mysqli_fetch_array($data_available_loads)) {
								if(!in_array($row_available['id'],$lh_used_id)) {
									echo "
										<div class='available_load_entry'>
											<a href='manage_load.php?load_id=$row_available[id]' target='view_load_".$row_available['id']."'>Available Load ($row_available[id]): $row_available[name_company]</a>
									";

									$lh_used_id[] = $row_available['id'];
									// get a list of all the stops for this load
									$sql = "
										select *
										
										from load_handler_stops
										where load_handler_id = '".sql_friendly($row_available['id'])."'
											and deleted = 0
										order by linedate_pickup_eta
									";
									$data_avail_stops = simple_query($sql);
									
									while($row_avail_stops = mysqli_fetch_array($data_avail_stops)) {
										echo "<div>
												<div style='float:left'>
												(".($row_avail_stops['stop_type_id'] == 1 ? "S" : "C").")
												".$row_avail_stops['shipper_name'].": $row_avail_stops[shipper_city], $row_avail_stops[shipper_state]
												</div>
												<div style='float:right'>".($row_avail_stops['linedate_pickup_eta'] > 0 ? " - " . date("M j, H:i", strtotime($row_avail_stops['linedate_pickup_eta'])) : "")."</div>
											</div>
										";
									}
									echo "
										<div style='clear:both'></div>
										</div>
										
									";
								}

							}
							$last_truck_log_id = 0;
							$loop_count = 0;
							while($row_log = mysqli_fetch_array($data_log)) {
								$loop_count++;
								$last_truck_log_id = $row_log['id'];
							
							
								$date_updated = strtotime(date("m/d/Y", strtotime($row_log['linedate_updated'])));
								$date_current = strtotime(date("m/d/Y", time()));

								$extra_class = '';
								$trailer_alert = false;
								if($date_updated < $date_current || $date_updated == '') {
									// hasn't been updated today, show an 'alert' background color
									// if this is a weekend or a monday, allow the days to go back a ways
								}
								if(strtotime($row_log['linedate']) < strtotime(date("Y-m-d", time()))) {
									$extra_class = 'update_alert';
									$trailer_alert = true;
								}
								
								if(!$trailer_alert && $row_log['dropped_trailer']) {
									$use_background_color = ";background-color:#33ff00";
								} elseif($row_log['color'] != '' && !$trailer_alert && $row_log['color'] != '#f8f3e4') {
									$use_background_color = ";background-color:$row_log[color]";
								} else {
									$use_background_color = '';
								}
								
								if($row_log['location'] != '') {
									$use_location = $row_log['location'];
								} else {
									$use_location = "$row_log[destination], $row_log[destination_state]";
								}
								
								
								
								if($row_log['dropped_trailer']) {
									echo "
										<div class='truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:300px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
											<span truck_id='$row_log[truck_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'>
											<a style='margin-right:10px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">TL DR</a>
											<span style='float:left'>".trim_string($use_location,45)."</span>
											<span style='float:right'>$row_log[trailer_name]</span>
											</span>
											<div style='clear:both'></div>
										</div>
									";
								} else {
									if($row_log['dispatch_completed']) {
										$use_image = "images/good.png";
									} elseif ($row_log['has_load_flag']) {
										$use_image = "images/inventory.png";
									} else {
										$use_image = "images/note.png";
									}
									echo "
										<div class='truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:300px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
											<span truck_id='$row_log[truck_id]' load_handler_id='$row_log[load_handler_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'>
											<img log_id='$row_log[id]' class='note_image' has_load_flag='$row_log[has_load_flag]' src='$use_image' border='0' style='margin-right:5px;float:left'>
											<div id='note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;min-width:350px;background-color:#eeeeee'>
											</div>
												".($row_log['driver2_id'] > 0 ? "(TEAM DRIVER)" : "")."
												".($row_log['load_handler_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/menu_system16.png' style='float:left' onclick=\"edit_entry_truck('',$row_log[id],0)\"></div>" : "")."
												<a style='margin-right:40px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">($row_log[load_handler_id]) $row_log[name_driver_first] $row_log[name_driver_last]</a>
												<span style='float:right;margin-right:5px'>$row_log[phone_cell]</span>
											
											<br>
											<span style='width:150px;float:left;margin-right:5px'>$row_log[name_truck]</span>
											<span style='float:right;margin-right:5px'>$row_log[trailer_name]</span>
											
											<br>
											".trim_string($use_location,45)."
											</span>
									";
									
									$counter_sub = 0;
									mysqli_data_seek($data_log,$loop_count-1);
									while($row_log_sub = mysqli_fetch_array($data_log)) {
										$counter_sub++;
										//echo "($row_log[load_handler_id] | $row_log_sub[load_handler_id] | $row_log_sub[load_handler_stop_id] | loop_count: $loop_count | counter: $counter_sub)";
										if($row_log['id'] != $row_log_sub['id'] || $row_log_sub['id'] == 0 || $row_log_sub['id'] == '') {
											if($counter_sub > 1) $counter_sub--;
											$loop_count = $loop_count + $counter_sub - 1;
											
											@mysqli_data_seek($data_log,$loop_count);
											break;
										} else {
											echo "<div>
													<div style='float:left".($row_log_sub['linedate_completed'] > 0 ? ";color:#888888" : "")."'>
													(".($row_log_sub['stop_type_id'] == 1 ? "S" : "C").") ($row_log_sub[load_handler_stop_id])
													".$row_log_sub['shipper_name'].": $row_log_sub[shipper_city], $row_log_sub[shipper_state]
													</div>
													<div style='float:right'>
											";
												if($row_log_sub['linedate_completed'] > 0) {
													echo "<span style='color:#888888'>".($row_log_sub['linedate_completed'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_completed'])) : "")."&nbsp;&nbsp;&nbsp</span>";
												} else {
													echo "&nbsp;&nbsp; <a href='javascript:update_stop_complete($row_log_sub[load_handler_stop_id])'>".
														($row_log_sub['linedate_pickup_eta'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_pickup_eta'])) : "")."&nbsp;&nbsp"
														."</a>";
												}
														
											echo "
													</div>
												</div>
												<div style='clear:both'></div>
											";
										}
									}
									//@mysqli_data_seek($data_log, $loop_count-1);
									echo "
										</div>
									";
								} // end of dropped trailer if
							} // end of while statement
							
							if(date("m/d/Y", strtotime("+$i days", $startdate)) == date("m/d/Y", time())) {
								// today's date
								
								// show any dropped trailer
								//and trailers_dropped.linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
								$sql = "
									select trailers_dropped.*,
										trailers.trailer_name
									
									from trailers_dropped, trailers
									where trailers.id = trailers_dropped.trailer_id
										
										and trailers_dropped.drop_completed = 0
										and trailers_dropped.deleted = 0
										
										$sql_filter_drop_tl_cust
										
								";
								$data_trailers_dropped = simple_query($sql);
								while($row_trailer_dropped = mysqli_fetch_array($data_trailers_dropped)) {
									echo "
										<div class='truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='background-color:#33ff00;min-width:300px;'>
											<span trailer_id='$row_trailer_dropped[trailer_id]' class='entry_info' dropped_trailer_id='$row_trailer_dropped[id]'>
											<a style='margin-right:10px;float:left' href=\"javascript:edit_dropped_trailer($row_trailer_dropped[id])\">TL DR</a>
											<span style='float:left'>".trim_string("$row_trailer_dropped[location_city], $row_trailer_dropped[location_state] $row_trailer_dropped[location_zip]",45)."</span>
											<span style='float:right'>$row_trailer_dropped[trailer_name]</span>
											</span>
											<div style='clear:both'></div>
										</div>
									";
								}
							}
							
							echo "&nbsp;</td>";
							
							echo "
							</tr>
							<tr><td colspan='20'><hr></td></tr>
							";
						}
					?>
				
		<?
	}
	
?>