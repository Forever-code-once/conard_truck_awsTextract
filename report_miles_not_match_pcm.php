<? include('header.php') ?>
<?
	$rfilter = new report_filter();
	$rfilter->show_truck 			= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
	if(isset($_POST['build_report'])) {
		
		$total_pcm = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		
		$date_start = strtotime($_POST['date_from']);
		$date_end = strtotime($_POST['date_to']);
		
		$sql = "
			select trucks_log.*,
				trucks.name_truck
			
			from trucks_log
				left join trucks on trucks_log.truck_id = trucks.id
			where trucks_log.miles <> pcm_miles
				and trucks_log.deleted = 0
				and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
				and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))."'
				and trucks_log.manual_miles_flag
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
			order by load_handler_id, id
		";
		$data = simple_query($sql);
		
		echo "
			<table class='admin_menu2 font_display_section' style='width:550px'>
			<tr>
				<td colspan='10'><h3>Manual Mileage not matching PC*Miler Miles for ".date("n/j/Y", $date_start)." - ".date("n/j/Y", $date_end)."</h3></td>
			</tr>
			<tr>
				<td><b>Load</td>
				<td><b>Dispatch</td>
				<td align='right'><b>Manual Miles</b></td>
				<td align='right'><b>PC*M Miles</b></td>
				<td align='right'><b>Variance</b></td>
			</tr>
		";
		while($row = mysqli_fetch_array($data)) {
			$line_miles = $row['miles'] + $row['miles_deadhead'];
			
			$total_miles += $line_miles;
			$total_pcm += $row['pcm_miles'];
			
			
			$line_variance = $line_miles - $row['pcm_miles'];
			
			echo "
				<tr>
					<td><a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[id]'>$row[load_handler_id]</td>
					<td><a href='add_entry_truck.php?linedate=&id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</td>
					<td align='right'>".number_format($row['miles'])."</td>
					<td align='right'>".number_format($row['pcm_miles'])."</td>
					<td align='right'>".number_format($line_variance)."</td>
					
				</tr>
			";
		}
		echo "
			<tr>
				<td colspan='10'><hr></td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td align='right'>".number_format($total_miles)."</td>
				<td align='right'>".number_format($total_pcm)."</td>
				<td align='right'>".number_format($total_miles - $total_pcm)."</td>
				
			</table>
		";
	}
?>
<? include('footer.php') ?>