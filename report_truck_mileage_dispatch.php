<?
$usetitle = "Truck Dispatch Mileage Report";
$use_title = "Truck Dispatch Mileage Report";
?>
<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
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
	//$rfilter->show_customer 			= true;
	//$rfilter->show_driver 			= true;
	$rfilter->show_truck 			= true;
	//$rfilter->show_trailer 			= true;
	//$rfilter->show_load_id 			= true;
	//$rfilter->show_dispatch_id 		= true;
	//$rfilter->show_origin	 		= true;
	//$rfilter->show_destination 		= true;
	//$rfilter->show_stops	 		= true;
	$rfilter->show_font_size			= true;
	//$rfilter->search_sort_by_report	= true;
	$rfilter->show_filter();
	
	
	$load_counter=0;
	$load_arr[0]=0;
	
	$range_all=0;
	$range_100=0;		//  0-100
	$range_300=0;		//101-300
	$range_500=0;		//301-500
	$range_501=0;		//501-?
	
	
	function mrr_get_all_replaced_replacement_trucks($truck_id,$date_from,$date_to,$mode=0)
	{	//MODE changes if finding replacement trucks or replaced trucks...
		$list="";	
		if($truck_id==0)		return $list;
				
		$special_adderx="or equipment_history.linedate_returned >= '".date("Y-m-d", strtotime($date_to))." 00:00:00'";		
		if(date("Y-m", strtotime($date_to))=="2014-06")
		{
			$special_adderx="or equipment_history.linedate_returned > '".date("Y-m-d", strtotime($date_to))." 00:00:00' or equipment_history.linedate_returned = '2014-06-30 00:00:00'";	
		}
		/*
		$search_date_range="
			and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($date_from))."'
			and trucks_log.linedate_pickup_eta<= '".date("Y-m-d", strtotime($date_to))."'
		";
		*/
		
		$list.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";			
		$cntr=0;
		$sql = "
			select equipment_id,
				equipment_history.replacement_xref_id,
				(select t1.name_truck from trucks t1 where t1.deleted=0 and t1.id=equipment_history.equipment_id) as tname1,
				(select t2.name_truck from trucks t2 where t2.deleted=0 and t2.id=equipment_history.replacement_xref_id) as tname2,
				equipment_history.linedate_aquired,
				equipment_history.linedate_returned,
				equipment_history.equipment_value
			
			from equipment_history
			where equipment_type_id = 1
				
				and equipment_history.replacement_xref_id > 0
				and equipment_history.equipment_id > 0
				
				and (
					(equipment_history.linedate_aquired < '".date("Y-m-d", strtotime($date_from))." 00:00:00' and (equipment_history.linedate_returned = 0 or equipment_history.linedate_returned >'".date("Y-m-d", strtotime($date_to))." 23:59:59'))
					or
					(equipment_history.linedate_aquired <= '".date("Y-m-d", strtotime($date_to))." 23:59:59' and equipment_history.linedate_returned > '".date("Y-m-d", strtotime($date_to))." 23:59:59')
					or
					(equipment_history.linedate_aquired < '".date("Y-m-d", strtotime($date_from))." 00:00:00' and equipment_history.linedate_returned >= '".date("Y-m-d", strtotime($date_from))." 00:00:00')
					or
					(equipment_history.linedate_aquired >= '".date("Y-m-d", strtotime($date_from))." 00:00:00' and equipment_history.linedate_returned <= '".date("Y-m-d", strtotime($date_to))." 23:59:59')
					
				)		
				and equipment_history.deleted = 0
				".($mode > 0 ? "and equipment_history.replacement_xref_id='".sql_friendly($truck_id)."'" : "and equipment_history.equipment_id='".sql_friendly($truck_id)."'")."
			
			order by equipment_history.linedate_aquired asc, 
					equipment_history.linedate_returned asc, 
					equipment_history.id asc
		";
		/*
				
					
					
					
					(equipment_history.linedate_aquired < '".date("Y-m-d", strtotime($date_from))." 00:00:00' and (equipment_history.linedate_returned = 0 or equipment_history.linedate_returned>='".date("Y-m-d", strtotime($date_from))." 00:00:00'))
					or
					(equipment_history.linedate_aquired >= '".date("Y-m-d", strtotime($date_from))." 00:00:00' and (equipment_history.linedate_returned = 0 or equipment_history.linedate_returned>='".date("Y-m-d", strtotime($date_to))." 00:00:00'))
					or
					(equipment_history.linedate_aquired >= '".date("Y-m-d", strtotime($date_from))." 00:00:00' and equipment_history.linedate_returned<='".date("Y-m-d", strtotime($date_from))." 23:59:59')
				
				
				
				and equipment_history.linedate_aquired <= '".date("Y-m-d", strtotime($date_from))." 23:59:59'
				and (
					equipment_history.linedate_returned = 0
					".$special_adderx."
					)	
		*/
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			if($row['replacement_xref_id']==$truck_id && trim($row['tname1'])!="" && trim($row['tname2'])!="")
			{
				$list.="
     				<tr>
     					<td valign='top'><a href='admin_trucks.php?id=".$row['equipment_id']."' target='_blank'>".$row['tname1']."</a></td>
     					<td valign='top'><b>Replacing</b></td>
     					<td valign='top'>".$row['tname2']."</td>
     					<td valign='top'>From ".date("m/d/Y",strtotime($row['linedate_aquired']))."</td>
     					<td valign='top'>".($row['linedate_returned']!='0000-00-00 00:00:00' ? "To ".date("m/d/Y",strtotime($row['linedate_returned'])) : "To Now")."</td>
     				</tr>
     			";	
			}
			elseif(trim($row['tname1'])!="" && trim($row['tname2'])!="")
			{
				$list.="
     				<tr>
     					<td valign='top'>".$row['tname1']."</td>
     					<td valign='top'><b>Replaced By</b></td>
     					<td valign='top'><a href='admin_trucks.php?id=".$row['replacement_xref_id']."' target='_blank'>".$row['tname2']."</a></td>
     					<td valign='top'>From ".date("m/d/Y",strtotime($row['linedate_aquired']))."</td>
     					<td valign='top'>".($row['linedate_returned']!='0000-00-00 00:00:00' ? "To ".date("m/d/Y",strtotime($row['linedate_returned'])) : "To Now")."</td>
     				</tr>
     			";	
			}
			
     		$cntr++;	
		}		
		$list.="</table>";	
		//if($truck_id==517)		$list.="<br>".$sql."<br>";
		
		if($cntr==0)		$list="";
		return $list;
	}
		
 	if(isset($_POST['build_report'])) { 
	
		$search_date_range = '';
		if($_POST['dispatch_id'] != '' || $_POST['load_handler_id'] != '') {
		} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and trucks_log.linedate_pickup_eta<= '".date("Y-m-d", strtotime($_POST['date_to']))."'
			";
		}
		
		$mrr_trailer_find="";
		/*
		//This first section is already not being used...
		if($_POST['trailer_id'] > 0)
		{
			$mrr_trailer_find="
				(
					select load_handler_stops.id 
					from load_handler_stops 
					where load_handler_stops.load_handler_id=load_handler.id 
						and load_handler_stops.trucks_log_id=trucks_log.id 
						and load_handler_stops.start_trailer_id='".sql_friendly($_POST['trailer_id'])."'
						and load_handler_stops.deleted=0
				) as mrr_trailer_switched,
			";	
		}
		*/
		$mrr_trailer_find2="";
		/*
		if($_POST['trailer_id'] > 0)
		{
			$mrr_trailer_find2="
				and 
				(
					trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'
					or (
					     select count(*)
     					from load_handler_stops 
     					where load_handler_stops.load_handler_id=load_handler.id 
     						and load_handler_stops.trucks_log_id=trucks_log.id 
     						and load_handler_stops.start_trailer_id='".sql_friendly($_POST['trailer_id'])."'
     						and load_handler_stops.deleted=0
					) > 0
				)
			";	
		}
		*/
		
		$sorter="trucks.name_truck asc,";	//"";
		/*
		if(isset($_POST['search_sort_by_report']))
		{
			if($_POST['search_sort_by_report']=='date')			$sorter="";
			if($_POST['search_sort_by_report']=='load')			$sorter="trucks_log.load_handler_id asc,";
			if($_POST['search_sort_by_report']=='dispatch')		$sorter="trucks_log.id asc,";
			if($_POST['search_sort_by_report']=='driver')		$sorter="drivers.name_driver_last asc,drivers.name_driver_first asc,";
			if($_POST['search_sort_by_report']=='customer')		$sorter="customers.name_company asc,";			
		}
		*/
		
		$sql = "
			select trucks_log.*,
				trucks_log.trailer_id as cur_trailer_id,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trailers.id as trailer_name_id,
				trucks.name_truck,
				customers.name_company,	
				load_handler.master_load,			
				".$mrr_trailer_find."
				load_handler.id as load_handler_id
			
			from load_handler
				left join trucks_log on load_handler.id = trucks_log.load_handler_id and trucks_log.deleted = 0
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				
			where load_handler.deleted = 0
				
				$search_date_range			
				
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				
			
			order by ".$sorter."trucks_log.linedate_pickup_eta
		";
		/*
				//".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."			///already not used...
				
				//below are all used on dispatch history report.
				".($_POST['dispatch_id'] ? " and trucks_log.id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."')" : '') ."
		
		
				".$mrr_trailer_find2."
				
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				".($_POST['report_origin'] ? " and trucks_log.origin like '%".sql_friendly($_POST['report_origin'])."%'" : '') ."
				".($_POST['report_origin_state'] ? " and trucks_log.origin_state like '%".sql_friendly($_POST['report_origin_state'])."%'" : '') ."
				".($_POST['report_destination'] ? " and trucks_log.destination = '".sql_friendly($_POST['report_destination'])."'" : '') ."
				".($_POST['report_destination_state'] ? " and trucks_log.destination_state = '".sql_friendly($_POST['report_destination_state'])."'" : '') ."		
		*/
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:600px;text-align:left'>
	<tr>
		<td colspan='6' align='left'>
			<center><span class='section_heading'>Truck Dispatch Mileage Report</span></center>			
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td>Truck</td>
		<td align='right'>Loads</td>
		<td align='right'>Dispatches</td>
		<td align='right' title='OTR and Hourly miles'>Miles</td>
		<td align='right' title='OTR and Hourly Deadhead miles'>Deadhead</td>
		<td align='right'>Total Miles</td>
	</tr>
	<?
		$trucks_tot=0;
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_loads=0;
		$total_disps=0;
		
		$last_truck_was="";	
		$last_truck_id=0;	
		
		$truck_loads=0;
		$truck_disps=0;
		$truck_miles=0;
		$truck_deadhead=0;
				
		while($row = mysqli_fetch_array($data)) 
		{
			$counter++;
			
			if($sorter=="trucks.name_truck asc," && $last_truck_was!="" && $last_truck_was!=$row['name_truck'])
			{
				$alert_me="";
				if(($truck_miles + $truck_deadhead)==0)		$alert_me=" class='alert'";	
				
				$tsubs1=mrr_get_all_replaced_replacement_trucks($last_truck_id,$_POST['date_from'],$_POST['date_to'],0);
				$tsubs2=mrr_get_all_replaced_replacement_trucks($last_truck_id,$_POST['date_from'],$_POST['date_to'],1);
				
				echo "
					<tr".$alert_me.">
     					<td><a href='admin_trucks.php?id=".$last_truck_id."' target='_blank'><b>".$last_truck_was."</b></a>...
          						<a href='report_dispatch.php?truck_id=".$last_truck_id."&date_from=".$_POST['date_from']."&date_to=".$_POST['date_to']."' target='_blank'>Details</a> </td>
     					<td align='right'>".number_format($truck_loads)."</td>
     					<td align='right'>".number_format($truck_disps)."</td>
     					<td align='right'>".number_format($truck_miles)."</td>
     					<td align='right'>".number_format($truck_deadhead)."</td>
     					<td align='right'>".number_format($truck_miles + $truck_deadhead)."</td>
					</tr>
					
				";	
				if(trim($tsubs1)!="")
				{
					echo "
						<tr".$alert_me.">
     						<td>&nbsp;</td>
     						<td colspan='5'>".$tsubs1."</td>
						</tr>
					";
				}
				if(trim($tsubs2)!="")
				{
					echo "
						<tr".$alert_me.">
     						<td>&nbsp;</td>
     						<td colspan='5'>".$tsubs2."</td>
						</tr>
					";
				}
				
				$total_loads+=$truck_loads;
				$total_disps+=$truck_disps;
				
				$truck_disps=0;
				$truck_loads=0;
				$truck_miles=0;
				$truck_deadhead=0;
				$trucks_tot++;
			}
			$last_truck_was=$row['name_truck'];
			$last_truck_id=$row['truck_id'];	
			
			$load_found=0;
			for($z=0;$z < $load_counter; $z++)
			{
				if(	$load_arr[ $z ] == $row['load_handler_id'])	$load_found=1;
			}
			if($load_found==0)
			{
				$load_arr[ $load_counter ]=$row['load_handler_id'];	
				$load_counter++;	
			}
			$truck_disps++;
			
			$alert_me="";
			if(($row['miles'] + $row['loaded_miles_hourly']) + ($row['miles_deadhead'] + $row['miles_deadhead_hourly']))		$alert_me=" alert";
			
			if($sorter!="trucks.name_truck asc,")
			{		
     			
     			echo "
     				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."".$alert_me."'>
     					<td nowrap><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a>...
     						<a href='report_dispatch.php?truck_id=".$row['truck_id']."&date_from=".$_POST['date_from']."&date_to=".$_POST['date_to']."' target='_blank'>Details</a></td>
     					<td align='right'>&nbsp;</td>
     					<td align='right'>&nbsp;</td>
     					<td align='right'>".number_format(($row['miles'] + $row['loaded_miles_hourly']))."</td>
     					<td align='right'>".number_format(($row['miles_deadhead'] + $row['miles_deadhead_hourly']))."</td>
     					<td align='right'>".number_format(($row['miles'] + $row['loaded_miles_hourly']) + ($row['miles_deadhead'] + $row['miles_deadhead_hourly']))."</td>
     				</tr>
     			";
			}
			//add miles to grand total
			$total_miles += ($row['miles'] + $row['loaded_miles_hourly']);
			$total_deadhead += ($row['miles_deadhead'] + $row['miles_deadhead_hourly']);
			
			//add miles to truck total
			$truck_loads++;
			$truck_miles+=($row['miles'] + $row['loaded_miles_hourly']);
			$truck_deadhead+=($row['miles_deadhead'] + $row['miles_deadhead_hourly']);			
		}
		
		if($sorter=="trucks.name_truck asc,")
		{					// && $last_truck_was!="" && $last_truck_was!=$row['name_truck']
			$alert_me="";
			if(($truck_miles + $truck_deadhead)==0)		$alert_me=" class='alert'";
			
			$tsubs1=mrr_get_all_replaced_replacement_trucks($last_truck_id,$_POST['date_from'],$_POST['date_to'],0);
			$tsubs2=mrr_get_all_replaced_replacement_trucks($last_truck_id,$_POST['date_from'],$_POST['date_to'],1);
			
			echo "
				<tr".$alert_me.">
					<td><a href='admin_trucks.php?id=".$last_truck_id."' target='_blank'><b>".$last_truck_was."</b></a>...
     						<a href='report_dispatch.php?truck_id=".$last_truck_id."&date_from=".$_POST['date_from']."&date_to=".$_POST['date_to']."' target='_blank'>Details</a> </td>
					<td align='right'>".number_format($truck_loads)."</td>
					<td align='right'>".number_format($truck_disps)."</td>
					<td align='right'>".number_format($truck_miles)."</td>
					<td align='right'>".number_format($truck_deadhead)."</td>
					<td align='right'>".number_format($truck_miles + $truck_deadhead)."</td>
				</tr>
			";	
			
			if(trim($tsubs1)!="")
			{
				echo "
					<tr".$alert_me.">
						<td>&nbsp;</td>
						<td colspan='5'>".$tsubs1."</td>
					</tr>
				";
			}
			if(trim($tsubs2)!="")
			{
				echo "
					<tr".$alert_me.">
						<td>&nbsp;</td>
						<td colspan='5'>".$tsubs2."</td>
					</tr>
				";
			}
			
			
			$total_loads+=$truck_loads;
			$total_disps+=$truck_disps;
			
			$truck_disps=0;
			$truck_loads=0;
			$truck_miles=0;
			$truck_deadhead=0;
			$trucks_tot++;
		}		
	?>
	<tr>
		<td colspan='6'>
			<hr>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td>Truck</td>
		<td align='right'>Loads</td>
		<td align='right'>Dispatches</td>
		<td align='right' title='OTR and Hourly miles'>Miles</td>
		<td align='right' title='OTR and Hourly Deadhead miles'>Deadhead</td>
		<td align='right'>Total Miles</td>
	</tr>
	<tr>
		<td colspan='6'><?=number_format($load_counter)?> Unique Load(s)</td>
	</tr>
	<tr>
		<td colspan='6'><?=number_format($counter)?> dispatch(es)</td>
	</tr>
	<tr>
		<td><?=number_format($trucks_tot)?> Truck(s)</td>	
		<td align='right'><?=number_format($total_loads)?></td>
		<td align='right'><?=number_format($total_disps)?></td>	
		<td align='right'><?=number_format($total_miles)?></td>
		<td align='right'><?=number_format($total_deadhead)?></td>
		<td align='right'><?=number_format($total_miles + $total_deadhead)?></td>
	</tr>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
</script>
<? include('footer.php') ?>