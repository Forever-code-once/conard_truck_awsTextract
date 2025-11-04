<? include('header.php') ?>
<?
		$limit=50;
		$sql = "
			select load_handler.*,
				customers.name_company
			
			from load_handler
				left join customers on load_handler.customer_id = customers.id
			where load_handler.deleted = 0			
			order by id desc
			limit ".$limit."	
		";
		$data = simple_query($sql);
		
		
		
		$disphtml = "
			<center><span class='section_heading'>Newest ".$limit." Loads</span></center>
			<table class='admin_menu2 tablesorter' style='margin:10px;text-align:left;'>
			<thead>
			<tr>
				<th><b>Load ID</b></th>
				<th><b>Customer</b></th>
				<th><b>Origin City</b></th>
				<th><b>Origin State</b></th>
				<th><b>Dest. City</b></th>
				<th><b>Dest. State</b></th>
				<th><b>Pickup ETA</b></th>
				<th><b>Pickup PTA</b></th>
				<th><b>Dropoff ETA</b></th>
				<th><b>Dropoff PTA</b></th>
			</tr>
			</thead>
			<tbody>
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
		$disphtml .= "</tbody></table>";
		
		echo $disphtml;
?>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
</script>
<? include('footer.php') ?>