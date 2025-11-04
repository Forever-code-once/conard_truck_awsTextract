<? include('header.php') ?>
<?
		$sql = "
			select load_handler.*,
				customers.name_company
			
			from load_handler
				left join customers on load_handler.customer_id = customers.id
			where load_handler.deleted = 0
				and (load_available = 1
					or (select count(*) from trucks_log where load_handler_id = load_handler.id and trucks_log.deleted = 0) = 0)				
			order by origin_state, origin_city, dest_state, dest_city
				
		";
		$data = simple_query($sql);
		
		
		
		$disphtml = "
			<table class='admin_menu2' style='margin:10px;text-align:left;'>
			<tr>
				<td colspan='10'><center><span class='section_heading'>Available Loads</span></center></td>
			</tr>
			<tr>
				<td><b>Load ID</b></td>
				<td><b>Customer</b></td>
				<td><b>Origin City</b></td>
				<td><b>Origin State</b></td>
				<td><b>Dest. City</b></td>
				<td><b>Dest. State</b></td>
				<td><b>Pickup ETA</b></td>
				<td><b>Pickup PTA</b></td>
				<td><b>Dropoff ETA</b></td>
				<td><b>Dropoff PTA</b></td>
			</tr>
		";
		$counter = 0;
		while($row = mysqli_fetch_array($data)) {
			$counter++;
			$disphtml .= "
				<tr style='font-size:10px;' class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
					<td><a href='manage_load.php?load_id=$row[id]' target='view_load_$row[id]'>$row[id]</a></td>
					<td>$row[name_company]</td>
					<td nowrap>$row[origin_city]</td>
					<td nowrap>$row[origin_state]</td>
					<td nowrap>$row[dest_city]</td>
					<td nowrap>$row[dest_state]</td>
					<td nowrap>".(strtotime($row['linedate_pickup_eta']) ? date("M d, Y H:i a", strtotime($row['linedate_pickup_eta'])) : '')."</td>
					<td nowrap style='color:#cb2b00'>".(strtotime($row['linedate_pickup_pta']) ? date("M d, Y H:i a", strtotime($row['linedate_pickup_pta'])) : '')."</td>
					<td nowrap>".(strtotime($row['linedate_dropoff_eta']) ? date("M d, Y H:i a", strtotime($row['linedate_dropoff_eta'])) : '')."</td>
					<td nowrap style='color:#cb2b00'>".(strtotime($row['linedate_dropoff_pta']) ? date("M d, Y H:i a", strtotime($row['linedate_dropoff_pta'])) : '')."</td>
				</tr>
			";
		}
		$disphtml .= "</table>";
		
		echo $disphtml;
?>
<? include('footer.php') ?>