<?
$usetitle = "Report - Drivers Inactive";
$use_title = "Report - Drivers Inactive";
?>
<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	/*
	if(isset($_GET['truck_id'])) {
		$_POST['truck_id'] = $_GET['truck_id'];
		$_POST['build_report'] = 1;
	}
	*/
	if(isset($_GET['date_from'])) {
		$_POST['date_from'] = $_GET['date_from'];
		$_POST['build_report'] = 1;
	}	
	if(isset($_GET['date_to'])) {
		$_POST['date_to'] = $_GET['date_to'];
		$_POST['build_report'] = 1;
	}	


	$rfilter = new report_filter();
	//$rfilter->show_customer 			= true;
	$rfilter->show_driver 			= true;
	//$rfilter->show_truck 			= true;
	//$rfilter->show_trailer 			= true;
	//$rfilter->show_load_id 			= true;
	//$rfilter->show_dispatch_id 		= true;
	//$rfilter->show_origin	 		= true;
	//$rfilter->show_destination 		= true;
	//$rfilter->show_stops	 		= true;
	$rfilter->show_font_size			= true;
	//$rfilter->search_sort_by_report	= true;
	$rfilter->show_filter();
	
	$_POST['build_report'] = 1;
		
 	if(isset($_POST['build_report'])) 
 	{ 	
		
		$sql = "
			select drivers.*,
				(
				select count(*) 
				from trucks_log 
				where trucks_log.deleted=0 
					and trucks_log.driver_id>0
					and trucks_log.dispatch_completed>0
					and trucks_log.linedate_pickup_eta is not null
					and trucks_log.linedate_pickup_eta!='0000-00-00 00:00:00'
					and trucks_log.driver_id = drivers.id
					and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
					and trucks_log.linedate_pickup_eta <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
				) as disp_cntr,
				(
				select count(*) 
				from trucks_log 
				where trucks_log.deleted=0 
					and trucks_log.driver_id>0
					and trucks_log.dispatch_completed>0
					and trucks_log.linedate_pickup_eta is not null
					and trucks_log.linedate_pickup_eta!='0000-00-00 00:00:00'
					and trucks_log.driver2_id = drivers.id
					and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
					and trucks_log.linedate_pickup_eta <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
				) as disp_cntr2
			from drivers 
			where drivers.deleted='0'
				and drivers.id!=405
				".($_POST['driver_id'] > 0 ? " and drivers.id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
			order by drivers.name_driver_last,
				drivers.name_driver_first,
				drivers.id
		";		
		$data = simple_query($sql);
	?>
	<span class='section_heading'><?=$use_title ?></span><br><br>
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <i>Use this report to find those who had no dispatches within the selected date range.</i><br><br>
	<table class='admin_menu2 font_display_section tablesorter' style='margin:0 10px;width:1050px;text-align:left'>	
	<thead>
	<tr>
		<th>&nbsp;</th>
		<th>ID</th>
		<th>First Name</th>
		<th>Last Name</th>
		<th title='First date the driver was hired or came to our employment. (A.K.A. started.)'>Hired</th>
		<th title='First date the driver was terminated or left our employment.'>Terminated</th>
		<th title='Last date the driver was hired or came back to our employment.'>Re-Hired</th>
		<th title='Last date the driver was terminated or left our employment.'>Re-Leave</th>
		<th align='right'>Dispatches</th>
	</tr>
	</thead>
	<tbody>
	<?
		$counter = 0;				
		while($row = mysqli_fetch_array($data)) 
		{
			$disp_cntr=0;	
			
			$disp_cntr=$row['disp_cntr'] + $row['disp_cntr2'];	
			
			if($disp_cntr == 0)
			{
     			$counter++;
     			/*
     				linedate_birthday = '0000-00-00',
     				linedate_drugtest = '0000-00-00',				
     				linedate_driver_has_load = '0000-00-00 00:00:00',
     				linedate_license_expires = '0000-00-00 00:00:00',
     				attached_truck_id='0',
     				attached2_truck_id='0',
     				attached_trailer_id='0',
     				driver_has_load='0',
     				available_notes='',
     				linedate_available_notes = '0000-00-00 00:00:00',
     				linedate_cov_expires = '0000-00-00 00:00:00',				
     				linedate_review_due = '0000-00-00 00:00:00',
     				linedate_spouse = '0000-00-00 00:00:00',
     				linedate_anniversary = '0000-00-00 00:00:00',
     				dl_number='',
     				dl_state='',
     				active='1',
     			*/
     			
     			$date1="";		if($row['linedate_started']!="0000-00-00 00:00:00")		$date1=date("Y-m-d",strtotime($row['linedate_started']));
     			$date2="";		if($row['linedate_terminated']!="0000-00-00 00:00:00")		$date2=date("Y-m-d",strtotime($row['linedate_terminated']));
     			$date3="";		if($row['linedate_rehire']!="0000-00-00 00:00:00")		$date3=date("Y-m-d",strtotime($row['linedate_rehire']));
     			$date4="";		if($row['linedate_refire']!="0000-00-00 00:00:00")		$date4=date("Y-m-d",strtotime($row['linedate_refire']));
     			
     			echo "
     				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
     					<td nowrap>".$counter."</td>
     					<td><a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
     					<td nowrap>".trim($row['name_driver_first'])."</td>
     					<td nowrap>".trim($row['name_driver_last'])."</td>
     					<td align='right'>".$date1."</td>
     					<td align='right'>".$date2."</td>
     					<td align='right'>".$date3."</td>
     					<td align='right'>".$date4."</td>
     					<td align='right'>".$disp_cntr."</td>
     				</tr>
     			";	
			}		
		}
	?>
	</tbody>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();	
	
	$(".tablesorter").tablesorter({textExtraction: 'complex'});
</script>
<? include('footer.php') ?>