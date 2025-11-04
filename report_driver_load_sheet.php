<? include('header.php') ?>
<?
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['date_from']))	
	{
		$_GET['date_from']=str_replace("_","/",$_GET['date_from']);	
		$_POST['date_from']=$_GET['date_from'];
		
		$_POST['build_report']=1;
	}
	if(isset($_GET['date_to']))		
	{
		$_GET['date_to']=str_replace("_","/",$_GET['date_to']);
		$_POST['date_to']=$_GET['date_to'];
				
		$_POST['build_report']=1;
	}

	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_driver 		= true;
	$rfilter->show_truck 		= true;
	$rfilter->show_trailer 		= true;
	$rfilter->show_load_id 		= true;
	$rfilter->show_dispatch_id 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
	

	
	
	//ob_start();
?>


<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>Driver Dispatch Load Sheet</h3>
</div>
<div style='clear:both'></div>
<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left; width:900px; margin:10px'>
<thead>
<tr>
	<th><b>Date</b></th>
	<th nowrap><b>Load ID</b></th>
	<th nowrap><b>Disp ID</b></th>
	<th><b>Customer</b></th>
	<th><b>Driver</b></th>
	<th><b>Driver2</b></th>
	<th><b>Truck</b></th>
	<th><b>Trailer</b></th>		
	<th><b>Origin</b></th>
	<th><b>Destination</b></th>
	<th><b>Completed</b></th>
	<th align='right'><b>Miles</b></th>
	<th align='right'><b>Deadhead</b></th>
	<th align='right'><b>Total</b></th>
	<th align='right'><b>Hours</b></th>
</tr>
</thead>
<tbody>
<? 	
	if(isset($_POST['build_report'])) { 
     	
          $sql = "
     		select trucks_log.*,
     			customers.name_company,
     			(select name_driver_first from drivers where drivers.id=trucks_log.driver_id) as driver1_first_name,
     			(select name_driver_last from drivers where drivers.id=trucks_log.driver_id) as driver1_last_name,
     			(select name_driver_first from drivers where drivers.id=trucks_log.driver2_id) as driver2_first_name,
     			(select name_driver_last from drivers where drivers.id=trucks_log.driver2_id) as driver2_last_name,
     			trucks.name_truck,
     			trailers.trailer_name
     		
     		from trucks_log
     			left join customers on customers.id = trucks_log.customer_id
     			left join trucks on trucks.id = trucks_log.truck_id
     			left join trailers on trailers.id = trucks_log.trailer_id
     		where trucks_log.deleted = 0
     			
     			".($_POST['driver_id'] ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."')" : '') ."
     			
     			".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
     			".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
     			
     			".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
     			".($_POST['load_handler_id'] ? " and trucks_log.load_handler_id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
     			".($_POST['dispatch_id'] ? " and trucks_log.id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
     			
     			and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
     			and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
     		order by trucks_log.linedate_pickup_eta asc, 
     				trucks_log.load_handler_id asc
     	";
     	
     	//echo "<tr><td colspan='14'>".$sql."</td></tr>";
     	
     	$data = simple_query($sql);
     	
     	$counter = 0;
     	$total_miles = 0;
     	$total_dh_miles = 0;
     	$total_tot = 0;
     	$total_hours = 0;
     	$total_completed = 0;
     	while($row = mysqli_fetch_array($data)) 
     	{    		
     		$total_miles += $row['miles'];
     		$total_dh_miles += $row['miles_deadhead'];
     		$total_tot += $row['miles'] + $row['miles_deadhead'];
     		$total_hours += $row['hours_worked'];
     		
     		$d1_mask="<a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driver1_first_name']." ".$row['driver1_last_name']."</a>";
     		$d2_mask="";
     		if($row['driver2_id'] > 0)		$d2_mask="<a href='admin_drivers.php?id=".$row['driver2_id']."' target='_blank'>".$row['driver2_first_name']." ".$row['driver2_last_name']."</a>";
     		
     		$completed="";
     		if($row['dispatch_completed'] > 0)
     		{
     			$completed="Completed";
     			$total_completed++;	
     		}     		
     		
     		echo "
     			<tr>
     				<td nowrap>".date("m-d-Y", strtotime($row['linedate_pickup_eta']))."</td>
     				<td><a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank'>".$row['load_handler_id']."</a></td>
     				<td><a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
     				<td nowrap><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['name_company']."</a></td>
     				<td nowrap>".$d1_mask."</td>
     				<td nowrap>".$d2_mask."</td>				
     				<td nowrap><a href='".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a></td>
     				<td nowrap><a href='".$row['trailer_id']."' target='_blank'>".$row['trailer_name']."</a></td>				
     				<td nowrap>".$row['origin'].", ".$row['origin_state']."</td>
     				<td nowrap>".$row['destination'].", ".$row['destination_state']."</td>
     				<td nowrap>".$completed."</td>		
     				<td nowrap align='right'>".number_format($row['miles'],0)."</td>
     				<td nowrap align='right'>".number_format($row['miles_deadhead'],0)."</td>
     				<td nowrap align='right'>".number_format(($row['miles'] + $row['miles_deadhead']),0)."</td>
     				<td nowrap align='right'>".number_format($row['hours_worked'],2)."</span></td>				
     			</tr>
     		";
     		$counter++;
     	}
	}
	
	$comp_percent=0;
	if($counter > 0)
	{
		$comp_percent =($total_completed / $counter) * 100;
	}
?>
</tbody>
<tr>
	<td style='background-color:#bed3ec' colspan='10'><b>Total</b>&nbsp;</td>
	<td style='background-color:#bed3ec' align='right'><b><?=number_format($comp_percent,2)?>%</b></td>
	<td style='background-color:#bed3ec' align='right'><b><?=number_format($total_miles,0)?></b></td>
	<td style='background-color:#bed3ec' align='right'><b><?=number_format($total_dh_miles,0)?></b></td>
	<td style='background-color:#bed3ec' align='right'><b><?=number_format($total_tot,0)?></b></td>
	<td style='background-color:#bed3ec' align='right'><b><?=number_format($total_hours,2)?></b></td>
</tr>
</table>
</form>

<?
	//$pdf = ob_get_contents();
	//ob_end_clean();
?>
<script type='text/javascript'>
	
	$('.tablesorter').tablesorter();
	
	$('.datepicker').datepicker();
	
	$().ready(function() {
		
	});
</script>
<? include('footer.php') ?>