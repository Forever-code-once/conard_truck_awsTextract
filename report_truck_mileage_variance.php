<? include('header.php') ?>
<?
	$rfilter = new report_filter();
	$rfilter->show_date_range 		= false;
	$rfilter->show_single_date 		= true;
	$rfilter->show_truck 			= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

	
	
	if(isset($_POST['build_report'])) {
		
		$date_start = strtotime(date("m/1/Y", strtotime($_POST['report_date'])));
		$days_in_month = date("t", $date_start);
		$date_end = strtotime(date("m/".$days_in_month."/Y", $date_start));
		
		$total_miles = 0;
		$total_odo = 0;
		$total_variance = 0;
		
		$sql = "
			select distinct trucks.name_truck,
				trucks.id,
				(select sum(miles) 
				
				from trucks_log tl 
				where tl.truck_id = trucks.id
					and tl.linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
					and tl.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))."'
				) as total_miles,
				(select odometer from trucks_odometer where trucks_odometer.truck_id = trucks.id and linedate < '".date("Y-m-d", $date_start)."' order by linedate desc limit 1) as odo_start,
				(select odometer from trucks_odometer where trucks_odometer.truck_id = trucks.id and linedate >= '".date("Y-m-d",$date_start)."' order by linedate limit 1) as odo_end
				
			
			from trucks_log, trucks
			where trucks_log.deleted = 0
				and trucks_log.truck_id = trucks.id
				and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
				and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))."'
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
			order by trucks.name_truck
		";
		$data = simple_query($sql);
		
		echo "
			<table class='admin_menu2 font_display_section' style='width:750px'>
			<tr>
				<td colspan='10'><h3>Mileage Variance Report for ".date("M, Y", $date_start)."</h3></td>
			</tr>
			<tr>
				<td><b>Truck</td>
				<td align='right'><b>Odo Start</b></td>
				<td align='right'><b>Odo End</b></td>
				<td align='right'><b>Miles Driven</b></td>
				<td align='right'><b>Odometer</b></td>
				<td align='right'><b>Variance</b></td>
				<td align='right'><b>% Difference</b></td>
			</tr>
		";
		while($row = mysqli_fetch_array($data)) {
			$odo_miles = $row['odo_end'] - $row['odo_start'];
			$odo_variance = $odo_miles - $row['total_miles'];
			
			$total_miles += $row['total_miles'];
			$total_odo += $odo_miles;
			$total_variance += $odo_variance;
			if($row['total_miles'] == 0) {
				$variance_percent = 0;
			} else {
				$variance_percent = $odo_miles / $row['total_miles'] * 100 - 100;
			}
			
			echo "
				<tr>
					<td><a href='admin_trucks.php?id=$row[id]' target='view_truck_$row[id]'>$row[name_truck]</td>
					<td align='right'>".number_format($row['odo_start'])."</td>
					<td align='right'>".number_format($row['odo_end'])."</td>
					<td align='right'>".number_format($row['total_miles'])."</td>
					<td align='right'>".number_format($odo_miles)."</td>
					<td align='right'>".number_format($odo_variance)."</td>
					<td align='right'>".number_format($variance_percent)."%</td>
					<td>".(abs($variance_percent) > 220 ? "<span class='alert'>suspected odometer typo</span>" : (abs($variance_percent) > 15 ? "<span class='alert'>alert</span>" : ""))."</td>
				</tr>
			";
		}
		echo "
			<tr>
				<td colspan='10'><hr></td>
			</tr>
			<tr>
				<td></td>
				<td align='right'>".number_format($total_miles)."</td>
				<td align='right'>".number_format($total_odo)."</td>
				<td align='right'>".number_format($total_variance)."</td>
				<td align='right'>".($total_miles > 0 ? number_format($total_odo / $total_miles * 100 - 100)."%" : "")."</td>
			</table>
		";
	}
?>
<? include('footer.php') ?>