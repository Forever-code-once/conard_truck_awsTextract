<?  
$usetitle = "Driver Safety E-Log Report";
$use_title = "Driver Safety E-Log Report";
?>
<? include('header.php') ?>
<?
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}


	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_driver 		= true;
	$rfilter->show_truck 		= true;
	$rfilter->show_trailer 		= true;
	$rfilter->show_load_id 		= true;
	$rfilter->show_dispatch_id 	= true;
	//$rfilter->show_origin	 	= true;
	//$rfilter->show_destination 	= true;
	$rfilter->show_stops	 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
	$mrr_table="";
	
	if(isset($_POST['build_report'])) 
	{ 
		$tm_off_set=(int) $defaultsarray['gmt_offset_peoplenet'] - 1;
		
		
		$search_date_range = '';
		if($_POST['dispatch_id'] != '' || $_POST['load_handler_id'] != '') {
		} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and DATE_ADD(driver_elog_entries.linedate_created,INTERVAL ".$tm_off_set." HOUR) >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
				and DATE_ADD(driver_elog_entries.linedate_created,INTERVAL ".$tm_off_set." HOUR) <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
			";
		}	
		$cntr=0;
		
		$sql = "
			select driver_elog_entries.*,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company				
			
			from ".mrr_find_log_database_name()."driver_elog_entries
				left join drivers on drivers.id = driver_elog_entries.driver_id
				left join trailers on trailers.id = driver_elog_entries.trailer_id
				left join trucks on trucks.id = driver_elog_entries.truck_id
				left join customers on customers.id = driver_elog_entries.customer_id			
				left join load_handler on load_handler.id = driver_elog_entries.load_id
				left join trucks_log on trucks_log.id= driver_elog_entries.dispatch_id			
				
			where driver_elog_entries.deleted = 0
				and trucks_log.deleted = 0
				and load_handler.deleted = 0
				
				".$search_date_range."
				
				".($_POST['load_handler_id'] ? " and driver_elog_entries.load_id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['dispatch_id'] ? " and driver_elog_entries.dispatch_id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."				
				".($_POST['driver_id'] ? " and driver_elog_entries.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".($_POST['truck_id'] ? " and driver_elog_entries.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."				
				".($_POST['trailer_id'] ? " and driver_elog_entries.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."				
				".($_POST['customer_id'] ? " and driver_elog_entries.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."				
			
			order by driver_elog_entries.linedate_created asc
		";		
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$res=mrr_elog_event_types($row['event_id'],1);
			
			$emeans1="";		if(trim($res['data_1_means'])!="")		$emeans1="<b>".$res['data_1_means']."</b><br>";
			$emeans2="";		if(trim($res['data_2_means'])!="")		$emeans2="<b>".$res['data_2_means']."</b><br>";
			$emeans3="";		if(trim($res['data_3_means'])!="")		$emeans3="<b>".$res['data_3_means']."</b><br>";
			
			$smeans1="";		if(trim($res['setting_1_means'])!="")	$smeans1="<b>".$res['setting_1_means']."</b><br>";
			$smeans2="";		if(trim($res['setting_2_means'])!="")	$smeans2="<b>".$res['setting_2_means']."</b><br>";
			$smeans3="";		if(trim($res['setting_3_means'])!="")	$smeans3="<b>".$res['setting_3_means']."</b><br>";
			$smeans4="";		if(trim($res['setting_4_means'])!="")	$smeans4="<b>".$res['setting_4_means']."</b><br>";
			
			$efiller1="";		if($emeans1!="")	$efiller1=mrr_elog_data_mask($row['event_id'],1,1,$row['event_data1']);
			$efiller2="";		if($emeans2!="")	$efiller2=mrr_elog_data_mask($row['event_id'],1,2,$row['event_data2']);
			$efiller3="";		if($emeans3!="")	$efiller3=mrr_elog_data_mask($row['event_id'],1,3,$row['event_data3']);
			
			$sfiller1="";		if($smeans1!="")	$sfiller1=mrr_elog_data_mask($row['event_id'],2,1,$row['setting1']);
			$sfiller2="";		if($smeans2!="")	$sfiller2=mrr_elog_data_mask($row['event_id'],2,2,$row['setting2']);
			$sfiller3="";		if($smeans3!="")	$sfiller3=mrr_elog_data_mask($row['event_id'],2,3,$row['setting3']);
			$sfiller4="";		if($smeans4!="")	$sfiller4=mrr_elog_data_mask($row['event_id'],2,4,$row['setting4']);
			
			$mrr_table.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>					
					<td valign='top'><span title='Event ".$row['id']." came from packet ".$row['packet_id']."'>".$res['event_type']."</span></td>
					<td valign='top'><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['name_driver_first']." ".$row['name_driver_last']."</a></td>					
					<td valign='top'>".date("m/d/Y H:i",strtotime("".$tm_off_set." hours",strtotime($row['linedate_created'])))."</td>					
					<td valign='top'>".$emeans1."".$efiller1."</td>
					<td valign='top'>".$emeans2."".$efiller2."</td>
					<td valign='top'>".$emeans3."".$efiller3."</td>					
					<td valign='top'>".$smeans1."".$sfiller1."</td>
					<td valign='top'>".$smeans2."".$sfiller2."</td>
					<td valign='top'>".$smeans3."".$sfiller3."</td>
					<td valign='top'>".$smeans4."".$sfiller4."</td>					
					<td valign='top'><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['name_company']."</a></td>
					<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a></td>
					<td valign='top'><a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailer_name']."</a></td>
					<td valign='top'><a href='manage_load.php?load_id=".$row['load_id']."' target='_blank'>".$row['load_id']."</a></td>
					<td valign='top'><a href='add_entry_truck.php?load_id=".$row['load_id']."&id=".$row['dispatch_id']."' target='_blank'>".$row['dispatch_id']."</a></td>
					<td valign='top'>".$row['stop_id']."</td>
					<td valign='top'>".$row['active']."</td>					
					<td valign='top'>".$row['pardoned']."</td>
					<td valign='top'>".$row['pardoned_by_user']."</td>
					<td valign='top'>".$row['pardoned_reason']."</td>
					<td valign='top'>&nbsp;</td>
				<tr>
			";	
			//<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td><td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_effective']))."</td>
			//<td valign='top'>".str_replace("Settings: ","",$row['event_data4'])."</td><td valign='top'>".$row['deleted']."</td>
		}
	}	
?>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3><?=$usetitle ?></h3>
	<!--
	<div style='color:purple;'>
		&nbsp;
	</div>
	-->
</div>
<div style='clear:both'></div>

<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1800px;margin:10px'>
<thead>
<tr>
	<th nowrap><b>Event</b></th>
	<th nowrap><b>Driver</b></th>
	<th nowrap><b>Created</b></th>
	<th nowrap><b>Data1</b></th>
	<th nowrap><b>Data2</b></th>
	<th nowrap><b>Data3</b></th>
	<th nowrap><b>Setting1</b></th>
	<th nowrap><b>Setting2</b></th>
	<th nowrap><b>Setting3</b></th>
	<th nowrap><b>Setting4</b></th>
	<th nowrap><b>Customer</b></th>
	<th nowrap><b>Truck</b></th>
	<th nowrap><b>Trailer</b></th>
	<th nowrap><b>Load ID</b></th>
	<th nowrap><b>Dispatch</b></th>
	<th nowrap><b>Stop ID</b></th>
	<th nowrap><b>Active</b></th>
	<th nowrap><b>Ignore</b></th>
	<th nowrap><b>User</b></th>
	<th nowrap><b>Reason</b></th>
	<th nowrap><b>&nbsp;</b></th>
</tr>
</thead>
<tbody>
<? 
	echo $mrr_table;
?>
</tbody>
</table>
<br>     

<script type='text/javascript'>
	//$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
	});
	
</script>
<? include('footer.php') ?>
<?
/*
	<?xml version="1.0" encoding="ISO-8859-1"?>
	<!DOCTYPE pnet_oi_elog_events_rich PUBLIC "-//PeopleNet//pnet_oi_elog_events_rich" "http://open.peoplenetonline.com/dtd/pnet_oi_elog_events_rich.dtd">
	<pnet_oi_elog_events_rich>
	<packet_id>847</packet_id>
	<elog_data>
		<eid>17</eid>
		<did>331</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>207910.1</event_data>
	</elog_data>
	<elog_data>
		<eid>17</eid>
		<did>400</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>207910.1</event_data>
	</elog_data>
	<elog_data>
		<eid>17</eid>
		<did>365</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>141302.2</event_data>
	</elog_data>
	<elog_data>
		<eid>17</eid>
		<did>375</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>23409881.4</event_data>
	</elog_data>
	<elog_data>
		<eid>17</eid>
		<did>370</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>152777.0</event_data>
	</elog_data>
	<elog_data>
		<eid>17</eid>
		<did>6969</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>5191.4</event_data>
	</elog_data>
	<elog_data>
		<eid>2</eid>
		<did>#095</did>
		<created>07/04/13 04:55:26</created>
		<effective>07/04/13 04:55:26</effective>
		<event_data>2</event_data>
		<event_data>137747.5</event_data>
		<event_data>+38.965N -78.440W 1.9 miles N from Toms Brook, VA</event_data>
		<OtherSettings>
			<setting label="created_by">Driver</setting>
		</OtherSettings>
		<remark>Fuel</remark>
	</elog_data>
	<elog_data>
		<eid>17</eid>
		<did>#095</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>137747.5</event_data>
	</elog_data>
	<elog_data>
		<eid>17</eid>
		<did>386</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>214525.7</event_data>
	</elog_data>
	<elog_data>
		<eid>17</eid>
		<did>385</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>94820.6</event_data>
	</elog_data>
	<elog_data>
		<eid>17</eid>
		<did>#316</did>
		<created>07/04/13 05:00:00</created>
		<effective>07/04/13 05:00:00</effective>
		<event_data>153732.2</event_data>
	</elog_data>
	<elog_data>
		<eid>2</eid>
		<did>#095</did>
		<created>07/04/13 05:21:14</created>
		<effective>07/04/13 05:21:14</effective>
		<event_data>1</event_data>
		<event_data>137747.5</event_data>
		<event_data>+38.965N -78.440W 1.9 miles N from Toms Brook, VA</event_data>
		<OtherSettings>
			<setting label="created_by">Driver</setting>
		</OtherSettings>
	</elog_data>
	</pnet_oi_elog_events_rich>
*/
?>