<?
$usetitle = "Report - Drivers Active";
$use_title = "Report - Drivers Active";
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
	
	$uuid = createuuid();
	$excel_filename = "report_active_drivers_$uuid.xls";
	$export_file = "";
	$use_excel=1;
	$mrr_use_styles=0;	
	
	$stylex=" style='font-weight:bold;'";
	$mrr_total_head = " style='font-weight:bold; margin:0 10px; width:1050px; text-align:left;'";
	$tablex=" border='1' cellpadding='1' cellspacing='1' width='1050'";
	$headerx=" style='background-color:#CCCCFF;'";
		
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
		
		$export_file .= "Report - Drivers Active".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
		
		$export_file .= "".chr(9).
			"ID".chr(9).
			"First Name".chr(9).
			"Last Name".chr(9).
			"DOB".chr(9).
			"State".chr(9).
			"License".chr(9).			
			"Years".chr(9).
			"Hired".chr(9).
			"Terminated".chr(9).
			"Re-Hired".chr(9).
			"Re-Leave".chr(9).
			"Dispatches".chr(9);
		$export_file .= chr(13);	
		
	?>
	<span <?=( $mrr_use_styles > 0 ? "".$stylex."" : "class='section_heading'")?>><?=$use_title ?></span><br><br>
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <i>Use this report to find those who had at least one dispatch within the selected date range.</i><br><br>
	<table <?=( $mrr_use_styles > 0 ? "".$mrr_total_head."" : " class='admin_menu2 font_display_section' style='margin:0 10px;width:1050px;text-align:left;'")?>>	
		<thead>
	<tr>
		<th>&nbsp;</th>
		<th>ID</th>
		<th>First Name</th>
		<th>Last Name</th>
		<th>DOB</th>
        <th>Shirt</th>
		<th>State</th>
		<th>License</th>		
		<th>Years</th>
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
			
			if($disp_cntr > 0)
			{
     			$counter++;
     			/*
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
     				active='1',
     			*/
     			
     			$date1="";		if($row['linedate_started']!="0000-00-00 00:00:00")		$date1=date("m/d/Y",strtotime($row['linedate_started']));
     			$date2="";		if($row['linedate_terminated']!="0000-00-00 00:00:00")		$date2=date("m/d/Y",strtotime($row['linedate_terminated']));
     			$date3="";		if($row['linedate_rehire']!="0000-00-00 00:00:00")		$date3=date("m/d/Y",strtotime($row['linedate_rehire']));
     			$date4="";		if($row['linedate_refire']!="0000-00-00 00:00:00")		$date4=date("m/d/Y",strtotime($row['linedate_refire']));
     			
     			$dob="";		if($row['linedate_birthday']!="0000-00-00 00:00:00")		$dob=date("m/d/Y",strtotime($row['linedate_birthday']));
     			$lic=trim($row['dl_number']);
     			$st=trim($row['dl_state']);
     			$yrs=0;			if($row['linedate_started']!="0000-00-00 00:00:00")		$yrs=number_format(     ((time() - strtotime($row['linedate_started'])) / (60*60*24*365)),2);
                 
                $shirt="";
                if($row['shirt_size'] == "xs")         $shirt="X-Small";
                if($row['shirt_size'] == "s")          $shirt="Small";
                if($row['shirt_size'] == "m")          $shirt="Medium";
                if($row['shirt_size'] == "l")          $shirt="Large";
                if($row['shirt_size'] == "xl")         $shirt="X-Large";
                if($row['shirt_size'] == "xxl")        $shirt="2X-Large";
                if($row['shirt_size'] == "xxxl")       $shirt="3X-Large";
                if($row['shirt_size'] == "xxxxl")      $shirt="4X-Large";
                if($row['shirt_size'] == "xxxxxl")     $shirt="5X-Large";
                if($row['shirt_size'] == "xxxxxxl")    $shirt="6X-Large";
                
     			echo "
     				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
     					<td nowrap>".$counter."</td>
     					<td><a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
     					<td nowrap>".trim($row['name_driver_first'])."</td>
     					<td nowrap>".trim($row['name_driver_last'])."</td>     					
     					<td align='right'>".$dob."</td>
     					<td align='right'>".$shirt."</td>
     					<td align='right'>".$st."</td>
     					<td align='right'>".$lic."</td>     					
     					<td align='right'>".$yrs."</td>     					
     					<td align='right'>".$date1."</td>
     					<td align='right'>".$date2."</td>
     					<td align='right'>".$date3."</td>
     					<td align='right'>".$date4."</td>
     					<td align='right'>".$disp_cntr."</td>
     				</tr>
     			";	
     			
     			$export_file .= "".$counter."".chr(9).
          			"".$row['id']."".chr(9).
          			"".trim($row['name_driver_first'])."".chr(9).
          			"".trim($row['name_driver_last'])."".chr(9).
          			"".$dob."".chr(9).
          			"".$st."".chr(9).
          			"".$lic."".chr(9).          			
          			"".$yrs."".chr(9).          			
          			"".$date1."".chr(9).
          			"".$date2."".chr(9).
          			"".$date3."".chr(9).
          			"".$date4."".chr(9).
          			"".$disp_cntr."".chr(9);
				$export_file .= chr(13);	
			}		
		}
		
	?>
	</tbody>
	</table>
<? 
} 
	$used_excel_filename="";	
	$prefix="";
	if($use_excel > 0) 
	{
		$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
		fwrite($fp, $export_file); 
		fclose($fp);
		
		$prefix="<br><br><a href=\"http://trucking.conardlogistics.com/temp/".$excel_filename."\" target='_blank'>Click for Excel Version</a><br><br>";
		
		$used_excel_filename="http://trucking.conardlogistics.com/temp/".$excel_filename."";	
		
		echo $prefix;
	}
?>
<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();	
	
	$(".tablesorter").tablesorter({textExtraction: 'complex'});
</script>
<? include('footer.php') ?>