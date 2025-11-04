<?
$usetitle = "Report - Stop Wait Times";
$use_title = "Report - Stop Wait Times";
?>
<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	if(isset($_GET['truck_id'])) {
		$_POST['truck_id'] = $_GET['truck_id'];
		$_POST['build_report'] = 1;
	}
	if(isset($_GET['date_from'])) {
		$_POST['date_from'] = $_GET['date_from'];
		$_POST['build_report'] = 1;
	}	
	if(isset($_GET['date_to'])) {
		$_POST['date_to'] = $_GET['date_to'];
		$_POST['build_report'] = 1;
	}	


	$rfilter = new report_filter();
	$rfilter->show_customer 			= true;
	$rfilter->show_driver 			= true;
	$rfilter->show_truck 			= true;
	$rfilter->show_trailer 			= true;
	$rfilter->show_load_id 			= true;
	$rfilter->show_dispatch_id 		= true;
	$rfilter->show_shipper_name		= true;
	$rfilter->show_shipper_type		= true;
	$rfilter->show_origin	 		= true;
	//$rfilter->show_destination 		= true;
	//$rfilter->show_stops	 		= true;
	//$rfilter->show_active 			= true;
	$rfilter->mrr_send_email_here		= true;
	$rfilter->show_font_size			= true;
	$rfilter->search_sort_by_report	= true;
	$rfilter->show_filter();
	
	
	$load_counter=0;
	$load_arr[0]=0;
	
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}
	
	
	$pdf="";
		
 	if(isset($_POST['build_report'])) 
 	{				
		$search_date_range = '';
		if($_POST['dispatch_id'] != '' || $_POST['load_handler_id'] != '') 
		{
			
		} 
		else 
		{
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and load_handler_stops.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and load_handler_stops.linedate_pickup_eta <= '".date("Y-m-d", strtotime($_POST['date_to']))."'
			";
		}
		
		$sorter="load_handler.linedate_pickup_eta asc,load_handler_stops.load_handler_id asc,load_handler_stops.trucks_log_id asc";	//"trucks.name_truck asc,";
		
		if(isset($_POST['search_sort_by_report']))
		{
			if($_POST['search_sort_by_report']=='date')			$sorter="load_handler_stops.linedate_pickup_eta";
			if($_POST['search_sort_by_report']=='load')			$sorter="load_handler_stops.load_handler_id asc,";
			if($_POST['search_sort_by_report']=='dispatch')		$sorter="load_handler_stops.trucks_log_id asc,";
			if($_POST['search_sort_by_report']=='driver')		$sorter="drivers.name_driver_last asc,drivers.name_driver_first asc,";
			if($_POST['search_sort_by_report']=='customer')		$sorter="customers.name_company asc,";			
		}
		
		
		$sql = "
			select load_handler_stops.*,
				(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
				(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name,
				
				trucks_log.trailer_id as cur_trailer_id,
				
				trucks_log.linedate_pickup_eta as pickup_time,
				
				trucks.name_truck,
				trailers.trailer_name,
				trailers.id as trailer_name_id,
				
				drivers.name_driver_first,
				drivers.name_driver_last,
				
				customers.name_company
				
			from load_handler_stops
				left join load_handler on load_handler.id=load_handler_stops.load_handler_id and load_handler.deleted = 0
				left join trucks_log on trucks_log.id=load_handler_stops.trucks_log_id and trucks_log.deleted = 0
				left join customers on customers.id = trucks_log.customer_id
				left join drivers on drivers.id = trucks_log.driver_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join trailers on trailers.id = trucks_log.trailer_id
				
			where load_handler_stops.deleted = 0
				".$search_date_range."
								
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				
				".($_POST['trailer_id'] > 0 ? "and (load_handler_stops.start_trailer_id='".sql_friendly($_POST['trailer_id'])."' or load_handler_stops.end_trailer_id='".sql_friendly($_POST['trailer_id'])."')" : "")."
				
				and trucks_log.dispatch_completed > 0
				and load_handler_stops.linedate_pickup_eta >= '2010-01-01 00:00:00'
				and (load_handler_stops.linedate_dropoff_eta >= '2010-01-01 00:00:00' or load_handler_stops.linedate_arrival >= '2010-01-01 00:00:00')
				and load_handler_stops.linedate_completed >= '2010-01-01 00:00:00'
				
				".($_POST['dispatch_id'] ? " and trucks_log.id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."')" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				
				".($_POST['shipper_type'] > 0  ? "and load_handler_stops.stop_type_id='".sql_friendly($_POST['shipper_type'])."'" : "")."
				
				".(trim($_POST['shipper_name'])!=""  ? "and load_handler_stops.shipper_name like '".sql_friendly(trim($_POST['shipper_name']))."'" : "")."
				".($_POST['report_origin'] ? " and load_handler_stops.shipper_city like '%".sql_friendly($_POST['report_origin'])."%'" : '') ."
				".($_POST['report_origin_state'] ? " and load_handler_stops.shipper_state like '%".sql_friendly($_POST['report_origin_state'])."%'" : '') ."
						
			order by ".$sorter."
		";
		//".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
		//".($_POST['report_origin'] ? " and trucks_log.origin like '%".sql_friendly($_POST['report_origin'])."%'" : '') ."
		//".($_POST['report_origin_state'] ? " and trucks_log.origin_state like '%".sql_friendly($_POST['report_origin_state'])."%'" : '') ."
		//".($_POST['report_destination'] ? " and trucks_log.destination = '".sql_friendly($_POST['report_destination'])."'" : '') ."
		//".($_POST['report_destination_state'] ? " and trucks_log.destination_state = '".sql_friendly($_POST['report_destination_state'])."'" : '') ."
		
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
		
		ob_start();
	?>
	<center><span class='section_heading'><?=$use_title ?> (in Hours/Minutes)</span></center>		
	<table class='admin_menu2 font_display_section tablesorter' style='margin:0 10px;width:1300px;text-align:left'>
	<thead>
	<tr>
		<th nowrap>Stop ID</th>
		<th nowrap>Load ID</th>
		<th nowrap>Dispatch ID</th>
		<th>Driver</th>
		<th>Type</th>
		<th>Shipper</th>
		<th>City</th>
		<th>State</th>
		<th align='right'>Arrival</th>
		<th align='right'>Appointment</th>		
		<th align='right'>Departure</th>		
		<th align='right'>Early</th>
		<th align='right'>Delay</th>		
		<th align='right'>Time</th>
		<th>Truck</th>		
		<th>Trailer</th>
		<th>Customer</th>
	</tr>			
	</thead>
	<tbody>
	<?
		$counter = 0;
		$last_truck_was="";
				
		$tot_stops=0;
		$tot_timer=0;
		$tot_early=0;
		$tot_delay=0;
		
		$units=60*60;
				
		while($row = mysqli_fetch_array($data)) 
		{
			$counter++;
			
			$last_truck_was=$row['name_truck'];
			
			$trailer_disp=$row['start_trailer_name'];
			$trailer_disp1=$row['start_trailer_name'];
			$trailer_disp2=$row['end_trailer_name'];
			if($trailer_disp2 != $trailer_disp1)
			{
				$trailer_disp="<span style='color:blue;' title='This trailer was switched...".$row['start_trailer_name']." to ".$row['end_trailer_name']."'><b>".$row['end_trailer_name']."</b></span>";	
			}
			
			//$pta="".date("M j, Y H:i", strtotime($row['pickup_time']))."";				//dispatch date
			$pickup="".date("m/d/Y H:i", strtotime($row['linedate_pickup_eta']))."";		//stop ETA date...
			$arrival="".date("m/d/Y H:i", strtotime($row['linedate_arrival']))."";		//stop arrived date...
			$depart="".date("m/d/Y H:i", strtotime($row['linedate_completed']))."";		//stop completed date...			
			
			$timer=((strtotime($row['linedate_completed']) - strtotime($row['linedate_arrival']))/$units);			
			$early=((strtotime($row['linedate_pickup_eta']) - strtotime($row['linedate_arrival']))/$units);
			$delay=((strtotime($row['linedate_completed']) - strtotime($row['linedate_pickup_eta']))/$units);
			
			if($row['linedate_arrival']=="0000-00-00 00:00:00")
			{
				$arrival="".date("m/d/Y H:i", strtotime($row['linedate_pickup_eta']))."";	
				
				$timer=((strtotime($row['linedate_completed']) - strtotime($row['linedate_pickup_eta']))/$units);			
				$early=((strtotime($row['linedate_pickup_eta']) - strtotime($row['linedate_pickup_eta']))/$units);		//should always wind up 0
				$delay=((strtotime($row['linedate_completed']) - strtotime($row['linedate_pickup_eta']))/$units);
			}
			
			
			$tot_stops++;
			$tot_timer+=$timer;
			$tot_early+=$early;
			$tot_delay+=$delay;
			
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
					<td>$row[id]</a></td>
					<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
					<td><a href='add_entry_truck.php?id=$row[trucks_log_id]' target='view_dispatch_$row[trucks_log_id]'>$row[trucks_log_id]</a></td>
					<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
					<td nowrap>".($row['stop_type_id']==2 ? "Consignee" : "Shipper")."</td>
					<td nowrap>$row[shipper_name]</td>
					<td nowrap>$row[shipper_city]</td>
					<td nowrap>$row[shipper_state]</td>
					<td nowrap align='right'><span style='color:#".($row['linedate_arrival']=="0000-00-00 00:00:00" ? "CC0000" : "000000").";' title='Arrival not set, but stop completed. Using Appointment ETA instead'>".$arrival."</span></td>
					<td nowrap align='right'>".$pickup."</td>					
					<td nowrap align='right'>".$depart."</td>					
					<td nowrap align='right'><span style='color:#".($early < 0 ? "CC0000" : "000000").";'>".mrr_convert_hour_decimal_to_time($early)."</span></td>
					<td nowrap align='right'><span style='color:#".($delay > 0 ? "CC0000" : "000000").";'>".mrr_convert_hour_decimal_to_time($delay)."</span></td>
					<td nowrap align='right'><span style='color:#".($timer < 0 ? "CC0000" : "000000").";'>".mrr_convert_hour_decimal_to_time($timer)."</span></td>
					<td nowrap>$row[name_truck]</td>					
					<td nowrap>".$trailer_disp."</td>
					<td nowrap>$row[name_company]</td>
				</tr>
			";			
		}	
	?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan='17'>
			<hr>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td nowrap>Stop ID</td>
		<td nowrap>Load ID</td>
		<td nowrap>Dispatch ID</td>
		<td>Driver</td>
		<td>Type</td>
		<td>Shipper</td>
		<td>City</td>
		<td>State</td>
		<td align='right'>Arrival</td>
		<td align='right'>Appointment</td>		
		<td align='right'>Departure</td>		
		<td align='right'>Early</td>
		<td align='right'>Delay</td>
		<td align='right'>Time</td>
		<td>Truck</td>		
		<td>Trailer</td>
		<td>Customer</td>		
	</tr>
	<tr>
		<td colspan='10'><?=$tot_stops ?> Stop(s)</td>		
		<td align='right'>&nbsp;</td>		
		<td align='right'><?=mrr_convert_hour_decimal_to_time($tot_early)?></td>
		<td align='right'><?=mrr_convert_hour_decimal_to_time($tot_delay)?></td>
		<td align='right'><?=mrr_convert_hour_decimal_to_time($tot_timer)?></td>
		<td colspan='3'>(All Times shown in Hours/MInutes ... HH:mm)</td>
	</tr>
	</tfoot>
	</table>
	<?
		$pdf = ob_get_contents();
		ob_end_clean(); 
	} 
	
	echo $pdf;
	
	
if(trim($_POST['mrr_email_addr'])!="" && isset($_POST['mrr_email_report']))
{
	$user_name=$defaultsarray['company_name'];
	$From=$defaultsarray['company_email_address'];
	$Subject="";
	if(isset($use_title))			$Subject=$use_title;
	elseif(isset($usetitle))			$Subject=$use_title;
	
	$pdf=str_replace(" href="," name=",$pdf);
	//$pdf=str_replace("</a>","",$pdf); 
		
	$sentit=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject, $prefix.$pdf , $prefix.$pdf);
	
	$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
	echo "<br><br><b>This report has ".$sent_msg." to '".$_POST['mrr_email_addr_name']."' at E-Mail address '".$_POST['mrr_email_addr']."'.</b><br><br>";
	
	if(trim($_POST['mrr_email_addr'])!="" && trim($_POST['mrr_email_addr'])!="michael@sherrodcomputers.com")
	{
		//$sentit=mrr_trucking_sendMail('dconard@conardlogistics.com',"Dale Conard",$From,$user_name,'','',$Subject,$pdf,$pdf);
		$sentit=mrr_trucking_sendMail('jgriffith@conardlogistics.com',"James Griffith",$From,$user_name,'','',$Subject,$pdf,$pdf);
		//$sentit=mrr_trucking_sendMail('amassar@conardlogistics.com',"Anthony Massar",$From,$user_name,'','',$Subject,$pdf,$pdf);     		
	}
}
?>
<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	$('.tablesorter').tablesorter();
	
</script>
<? include('footer.php') ?>