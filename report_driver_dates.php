<?
$usetitle = "Report - Drivers Important Dates";
$use_title = "Report - Drivers Important Dates";

$maxdays=date("t",time());

if(!isset($_POST['date_from']))		$_POST['date_from']=date("m/01/Y",time());
if(!isset($_POST['date_to']))			$_POST['date_to']=date("m/".$maxdays."/Y",time());
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
		$mrr_adder="";
		//if(!isset($_POST['date_from']))		$_POST['date_from']="";
		//if(!isset($_POST['date_to']))			$_POST['date_to']="";
		
		if(trim($_POST['date_from'])!="" && trim($_POST['date_to'])!="")
		{
			$mrr_adder="
				and (
					( DATE_FORMAT(linedate_birthday,'%m%d') >= '".date("md",strtotime($_POST['date_from']))."' and DATE_FORMAT(linedate_birthday,'%m%d') <= '".date("md",strtotime($_POST['date_to']))."' and linedate_birthday!='0000-00-00 00:00:00' )
					or
					( DATE_FORMAT(linedate_spouse,'%m%d') >= '".date("md",strtotime($_POST['date_from']))."' and DATE_FORMAT(linedate_spouse,'%m%d') <= '".date("md",strtotime($_POST['date_to']))."' and linedate_spouse!='0000-00-00 00:00:00' )
					or
					( DATE_FORMAT(linedate_anniversary,'%m%d') >= '".date("md",strtotime($_POST['date_from']))."' and DATE_FORMAT(linedate_anniversary,'%m%d') <= '".date("md",strtotime($_POST['date_to']))."' and linedate_anniversary!='0000-00-00 00:00:00' )
				)
			";	
		}
		$sql = "
			select drivers.*
			from drivers 
			where drivers.deleted='0'
				and drivers.active>0
				and drivers.id!=405
				".($_POST['driver_id'] > 0 ? " and drivers.id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".$mrr_adder."
			order by drivers.name_driver_last,
				drivers.name_driver_first,
				drivers.id
		";		
		$data = simple_query($sql);
		//$date1="";		if($row['linedate_birthday']!="0000-00-00 00:00:00")		$date1=date("m/d/Y",strtotime($row['linedate_birthday']));
		//$date2="";		if($row['linedate_spouse']!="0000-00-00 00:00:00")		$date2=date("m/d/Y",strtotime($row['linedate_spouse']));
		//$date3="";		if($row['linedate_anniversary']!="0000-00-00 00:00:00")	$date3=date("m/d/Y",strtotime($row['linedate_anniversary']));
		    			
	?>
	<span class='section_heading'><?=$use_title ?></span><br><br>
	<div style='color:purple; font-weight:bold;'>Uses the month and day (without the year) to determine if any important driver dates fall within range. Range will not work if split years (ex: Dec 2016 through Jan 2017).</div>
	<br><br>
	<table class='admin_menu2 font_display_section tablesorter' style='margin:0 10px;width:1050px;text-align:left'>	
	<thead>
	<tr>
		<th>&nbsp;</th>
		<th>ID</th>
		<th>First Name</th>
		<th>Last Name</th>
		<th>Birthday</th>
		<th>Spouse Name</th>
		<th>Spouse Birthday</th>
		<th>Anniversary</th>
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
     			
     			$date1="";		if($row['linedate_birthday']!="0000-00-00 00:00:00")		$date1=date("m/d/Y",strtotime($row['linedate_birthday']));
     			$date2="";		if($row['linedate_spouse']!="0000-00-00 00:00:00")		$date2=date("m/d/Y",strtotime($row['linedate_spouse']));
     			$date3="";		if($row['linedate_anniversary']!="0000-00-00 00:00:00")	$date3=date("m/d/Y",strtotime($row['linedate_anniversary']));
     			//$date4="";		if($row['linedate_refire']!="0000-00-00 00:00:00")		$date4=date("Y-m-d",strtotime($row['linedate_refire']));
     			
     			echo "
     				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
     					<td nowrap>".$counter."</td>
     					<td><a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
     					<td nowrap>".trim($row['name_driver_first'])."</td>
     					<td nowrap>".trim($row['name_driver_last'])."</td>
     					<td align='right'>".$date1."</td>
     					<td nowrap>".trim($row['spouse_name'])."</td>
     					<td align='right'>".$date2."</td>
     					<td align='right'>".$date3."</td>
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