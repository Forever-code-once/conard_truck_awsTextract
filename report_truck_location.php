<? include('header.php') ?>
<?
	$sql = "
		select *
		
		from trucks
		where active = 1
			and deleted = 0
		order by trucks.name_truck
	";
	$data = simple_query($sql);
	
	echo "
		<div style='float:left;margin-left:20px;text-align:left'>
			<h2>Truck Location Report</h2>
			<span class='alert'>(showing alerts for trucks not moved in the past one day)</span>
		</div>
		<div style='clear:both'></div>
		<table class='tablesorter' style='margin:10px 10px;width:1400px;text-align:left'>
		<thead>
		<tr>
			<th>Truck</th>
			<th>Location</th>
			<th>Address</th>
			<th>Address2</th>
			<th>City</th>
			<th>State</th>
			<th>Zip</th>
			<th>Date</th>
			<th>Details</th>
			<th>Alert</th>
		</tr>
		</thead>
		<tbody>
	";
	$counter = 0;
	while($row = mysqli_fetch_array($data)) {
		$counter++;
		
		$location = '';
		$addr1='';
		$addr2='';
		$city = '';
		$state = '';
		$zip = '';
		$linedate = 0;
		$details = '';

		

		// find out which dispatch it's on
		$sql = "
			select trucks_log.*,
				customers.name_company
			
			from trucks_log
				left join load_handler on load_handler.id = trucks_log.load_handler_id
				left join customers on customers.id = load_handler.customer_id
			where truck_id = '$row[id]'
				and trucks_log.deleted = 0
				and trucks_log.linedate < '".date("Y-m-d", strtotime("+1 day", time()))."'
			order by trucks_log.linedate desc
			limit 1
		";
		$data_dispatch = simple_query($sql);
		
		if(mysqli_num_rows($data_dispatch)) {
			$row_dispatch = mysqli_fetch_array($data_dispatch);
			
			// get the location from the stops for this dispatch
			$sql = "
				select load_handler_stops.*
				
				from load_handler_stops
				where trucks_log_id = '$row_dispatch[id]'
				order by linedate_completed desc, linedate_pickup_eta desc
				limit 1
			";
			$data_location = simple_query($sql);
			
			if(mysqli_num_fields($data_location)) {
				$row_location = mysqli_fetch_array($data_location);
				$location = $row_location['shipper_name'];
				$addr1=$row_location['shipper_address1'];
				$addr2=$row_location['shipper_address2'];
				$city = $row_location['shipper_city'];
				$state = $row_location['shipper_state'];
				$zip = $row_location['shipper_zip'];
				
			} else {
				$location = "Unable to determine last location";
			}
			
			
			$linedate = strtotime($row_dispatch['linedate']);
			$details = "
				Dispatch: <a href='add_entry_truck.php?id=$row_dispatch[id]' target='view_dispatch_$row_dispatch[id]'>$row_dispatch[id]</a>
				Load: <a href='manage_load.php?load_id=$row_dispatch[load_handler_id]' target='view_load_$row_dispatch[load_handler_id]'>$row_dispatch[load_handler_id]</a>
				- Customer: $row_dispatch[name_company]
			";
		}
		
		
		if(time() - $linedate > 1 * 86400 && $linedate > 0) {
			$show_alert = true;
		} else {
			$show_alert = false;
		}
		
		echo "
			<tr class='".($counter % 2 == 1 ? "odd" : "even")."'>
				<td>$row[name_truck]</td>
				<td>$location</td>
				<td>$addr1</td>
				<td>$addr2</td>
				<td>$city</td>
				<td>$state</td>
				<td>$zip</td>
				<td>".($linedate > 0 ? date("m-d-Y", $linedate) : "")."</td>
				<td>$details</td>
				<td>".($show_alert ? "<span class='alert'>(alert)</span>" : "")."</td>
			</tr>
		";
	}
	echo "
		</tbody>
		</table>
	";
?>
<script type='text/javascript'>
	$('.tablesorter').tablesorter({

        				
     });
</script>
<? include('footer.php') ?>