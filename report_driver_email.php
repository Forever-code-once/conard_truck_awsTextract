<?
$usetitle = "Report - Drivers E-Mail";
$use_title = "Report - Drivers E-Mail";
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
	$rfilter->show_date_range		= false;
	$rfilter->show_driver 			= true;
	$rfilter->show_active			= true;
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
	$excel_filename = "driver_email_$uuid.xls";
	$export_file = "";
	$use_excel=1;
	
	$email_list="";
		
 	if(isset($_POST['build_report'])) 
 	{ 	
		if(!isset($_POST['report_active']))		$_POST['report_active']=0;
		$sql = "
			select drivers.*
			from drivers 
			where drivers.deleted='0'
				and drivers.id!=405
				".($_POST['report_active'] > 0 ? " and drivers.active > 0" : '') ."
				".($_POST['driver_id'] > 0 ? " and drivers.id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
			order by drivers.name_driver_last,
				drivers.name_driver_first,
				drivers.id
		";		
		$data = simple_query($sql);
	?>
	<span class='section_heading'><?=$use_title ?></span><br><br>
	<table class='admin_menu2 font_display_section tablesorter' style='margin:0 10px;width:1050px;text-align:left'>	
		<thead>
	<tr>
		<th>&nbsp;</th>
		<th>ID</th>
		<th>First Name</th>
		<th>Last Name</th>
		<th>E-Mail</th>
		<th>Cell Phone</th>
		<th>Address</th>
		<th>Address2</th>
		<th>City</th>
		<th>State</th>
		<th>Zip</th>
		<th>Active</th>
	</tr>
	</thead>
	<tbody>
	<?
		$export_file .= "Driver Email Report".chr(9).
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
     		
     	$export_file .= "ID".chr(9).
     			"First Name".chr(9).
     			"Last Name".chr(9).
     			"E-Mail".chr(9).
     			"Cell Phone".chr(9).
     			"Address".chr(9).
     			"Address2".chr(9).
     			"City".chr(9).
     			"State".chr(9).
     			"Zip".chr(9).
     			"Active".chr(9);
     	$export_file .= chr(13);	
		
		$acntr=0;		
		$icntr=0;
		$counter = 0;				
		while($row = mysqli_fetch_array($data)) 
		{
			if(trim($row['driver_email'])!="")
			{
				if($counter>0)		$email_list.=", ";
				$email_list.="".trim($row['driver_email'])."";
			}
			
			if($row['active'] > 0) 	
			{
				$actor="Active";
				$acntr++;
			}
			else
			{
				$actor="Inactive";	
				$icntr++;	
			}
			//trim($row['phone_home'])
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
					<td nowrap>".($counter+1)."</td>
					<td><a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
					<td nowrap>".trim($row['name_driver_first'])."</td>
					<td nowrap>".trim($row['name_driver_last'])."</td>
					<td align='right'>".trim($row['driver_email'])."</td>
					<td align='right'>".trim($row['phone_cell'])."</td>
					<td align='right'>".trim($row['driver_address_1'])."</td>
					<td align='right'>".trim($row['driver_address_2'])."</td>
					<td align='right'>".trim($row['driver_city'])."</td>
					<td align='right'>".trim($row['driver_state'])."</td>
					<td align='right'>".trim($row['driver_zip'])."</td>
					<td align='right'>".$actor."</td>
				</tr>
			";	
			
			$export_file .= "".$row['id']."".chr(9).
               			"".trim($row['name_driver_first'])."".chr(9).
               			"".trim($row['name_driver_last'])."".chr(9).
               			"".trim($row['driver_email'])."".chr(9).
               			"".trim($row['phone_cell'])."".chr(9).
               			"".trim($row['driver_address_1'])."".chr(9).
               			"".trim($row['driver_address_2'])."".chr(9).
               			"".trim($row['driver_city'])."".chr(9).
               			"".trim($row['driver_state'])."".chr(9).
               			"".trim($row['driver_zip'])."".chr(9).
               			"".$actor."".chr(9);
          	$export_file .= chr(13);	
						
			$counter++;
				
		}
		echo "
			</tbody>
			</table>
			<br>".$counter." Drivers Found (<b>".$acntr." Active</b> and ".$icntr." Inactive).			
		";
		
		
		$prefix="";
		if($use_excel > 0) 
		{
			$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
			fwrite($fp, $export_file); 
			fclose($fp);
			
			$prefix="<br><br><a href=\"http://trucking.conardlogistics.com/temp/".$excel_filename."\" target='_blank'>Click for Excel Version</a><br><br>";
			echo $prefix;
		}
		
		echo "
			<br>
			<b>E-Mail Addresses Only:</b><br>
			<textarea rows='25' cols='100' wrap='virtual'>".$email_list."</textarea>
		";
	} 
?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();	
	
	$(".tablesorter").tablesorter({textExtraction: 'complex'});
</script>
<? include('footer.php') ?>