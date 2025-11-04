<?
$usetitle = "Report - Dispatch History";
$use_title = "Report - Dispatch History";
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
	$rfilter->show_origin	 		= true;
	$rfilter->show_destination 		= true;
	$rfilter->show_stops	 		= true;
	$rfilter->show_active 			= true;
	$rfilter->show_font_size			= true;
	$rfilter->search_sort_by_report	= true;
	$rfilter->show_filter();
	
	
	$load_counter=0;
	$load_arr[0]=0;
	
	$range_all=0;
	$range_100=0;		//  0-100
	$range_300=0;		//101-300
	$range_500=0;		//301-500  ...updated, and now uses 200 as minimum
	$range_501=0;		//501-?

    $range_50=0;		//  0-50
    $range_200=0;		// 51-200
	
	$range_375=0;		//  0-375
	$range_575=0;		//376-575
	$range_576=0;		//576-?
		
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
				and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and trucks_log.linedate_pickup_eta<= '".date("Y-m-d", strtotime($_POST['date_to']))."'
			";
		}
		
		$mrr_trailer_find="";
		/*
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
		
		$sorter="";	//"trucks.name_truck asc,";
		
		if(isset($_POST['search_sort_by_report']))
		{
			if($_POST['search_sort_by_report']=='date')			$sorter="";
			if($_POST['search_sort_by_report']=='load')			$sorter="trucks_log.load_handler_id asc,";
			if($_POST['search_sort_by_report']=='dispatch')		$sorter="trucks_log.id asc,";
			if($_POST['search_sort_by_report']=='driver')		$sorter="drivers.name_driver_last asc,drivers.name_driver_first asc,";
			if($_POST['search_sort_by_report']=='customer')		$sorter="customers.name_company asc,";			
		}
		
		$mrr_shipper_find="";
		if(trim($_POST['shipper_name'])!="")
		{
			$mrr_shipper_find="
				and 	(
					select count(*)
					from load_handler_stops 
					where load_handler_stops.load_handler_id=load_handler.id 
						and load_handler_stops.trucks_log_id=trucks_log.id 
						and load_handler_stops.shipper_name like '".sql_friendly(trim($_POST['shipper_name']))."'
						and load_handler_stops.deleted=0
				) > 0
			";	
		}
		
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
				load_handler.id as load_handler_id,
				(select load_handler_stops.linedate_pickup_eta from load_handler_stops where load_handler_stops.trucks_log_id=trucks_log.id order by load_handler_stops.linedate_pickup_eta limit 1) as pickup_time
			
			from load_handler
				left join trucks_log on load_handler.id = trucks_log.load_handler_id and trucks_log.deleted = 0
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				
			where load_handler.deleted = 0
				".(($_POST['report_active'] == 0 || (int) $_POST['dispatch_id'] > 0 || (int) $_POST['load_handler_id'] > 0 ) ? "" : "and trucks_log.dispatch_completed > 0")."
				
				$search_date_range
				
				".($_POST['dispatch_id'] ? " and trucks_log.id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."')" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				
				".$mrr_trailer_find2."
				
				".$mrr_shipper_find."
				
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				".($_POST['report_origin'] ? " and trucks_log.origin like '%".sql_friendly($_POST['report_origin'])."%'" : '') ."
				".($_POST['report_origin_state'] ? " and trucks_log.origin_state like '%".sql_friendly($_POST['report_origin_state'])."%'" : '') ."
				".($_POST['report_destination'] ? " and trucks_log.destination = '".sql_friendly($_POST['report_destination'])."'" : '') ."
				".($_POST['report_destination_state'] ? " and trucks_log.destination_state = '".sql_friendly($_POST['report_destination_state'])."'" : '') ."
			
			order by ".$sorter."trucks_log.linedate_pickup_eta
		";
		//".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
		
		//echo "<br>".$sql."<br>";
		$data = simple_query($sql);
	?>
	<center><span class='section_heading'><?=$use_title ?></span></center>		
	<div style=color:purple;margin:10px;'><b>Use the MasterLoad column on the right to switch this setting on or off...  Click to see all <a href='master_load_listing.php'>Master Loads</a>.</b></div>
	<table class='admin_menu2 font_display_section tablesorter' style='margin:0 10px;width:1300px;text-align:left'>
	<thead>
	<tr>
		<th nowrap>Load ID</th>
		<th nowrap>Dispatch ID</th>
		<th>Driver</th>
		<th>Origin</th>
		<th>Destination</th>
		<th align='right'>Miles</th>
		<th align='right'>Deadhead</th>
		<th align='right'>Date</th>
		<th><span title='Dispatch has been marked completed.'>Done</span></th>
		<th>Truck</th>
		<th>OTR</th>
		<th>Hourly</th>		
		<th>Trailer</th>
		<th>Customer</th>
		<th align='right'>Profit</th>
		<th>MasterLoad</th>
	</tr>			
	</thead>
	<tbody>
	<?
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_profit = 0;
    
        $long_hall_min=375;
		
		$short_haul=0;
		$long_haul=0;
		$total_haul=0;
		
		$hourly_days=0;
		$otr_days=0;
		$last_truck_was="";
				
		$truck_loads=0;
		$truck_miles=0;
		$truck_deadhead=0;
		$truck_otr=0;
		$truck_hours=0;
		$truck_profit=0;	
				
		while($row = mysqli_fetch_array($data)) 
		{
			$counter++;
			
			if($sorter=="trucks.name_truck asc," && $last_truck_was!="" && $last_truck_was!=$row['name_truck'])
			{
				/*
				echo "
					<tr>
     					<td colspan='5' align='right'><b>Truck ".$last_truck_was." Totals: (".$truck_loads.")</b> </td>
     					<td align='right'><b>".number_format($truck_miles)."</b></td>
     					<td align='right'><b>".number_format($truck_deadhead)."</b></td>
     					<td nowrap></td>
     					<td nowrap>&nbsp;</td>
     					<td nowrap align='right'><b>".$truck_otr."</b></td>
     					<td nowrap align='right'><b>".$truck_hours."</b></td>
     					<td nowrap>&nbsp;</td>
     					<td nowrap>&nbsp;</td>
     					<td align='right'><b>$".money_format('',$truck_profit)."</b></td>
     					<td nowrap>&nbsp;</td>
					</tr>
					<tr>
						<td colpsan='15'>&nbsp;</td>
					</tr>
				";	
				
				$truck_loads=0;
				$truck_miles=0;
				$truck_deadhead=0;
				$truck_otr=0;
				$truck_hours=0;
				$truck_profit=0;	
				*/
			}
			$last_truck_was=$row['name_truck'];
			
			
			$master_flag="<span class='mrr_link_like_on' onClick='mrr_graduate_master_load(".$row['load_handler_id'].",1);' title='Graduate this load into a Master Load with one click.'>No</span>";	//make it a master load
			if($row['master_load'] > 0)
			{
				$master_flag="<span class='mrr_link_like_on' onClick='mrr_graduate_master_load(".$row['load_handler_id'].",0);' title='Reset this Master Load to a regular load.'>Yes</span>";	//remove as a master load
			}
			
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
			
			
			$vmiles=($row['miles'] + $row['loaded_miles_hourly']);
			$dhmiles=($row['miles_deadhead'] + $row['miles_deadhead_hourly']);
			
			if(($vmiles + $dhmiles) == 0)		$vmiles=1;	//don't allow 0 miles for anything here...use 1...for insurance report since they did travel a little within the same town.
			
			
			$total_miles += $vmiles;
			$total_deadhead += $dhmiles;
			
			$mrr_use_profit=$row['profit'];
			//$mrr_use_profit=mrr_figure_profit_for_load_dispatch($row['load_handler_id'],$row['id']);		//see functions.php file for this profit.
			
			$total_profit += $mrr_use_profit;
			
			$show_this_set_of_stops=0;
			$trailer_disp=$row['trailer_name'];
			if($_POST['trailer_id'] > 0 && $row['cur_trailer_id']!=$_POST['trailer_id'])
			{
				$show_this_set_of_stops=1;
				$trailer_disp="<span style='color:blue;' title='This trailer was switched...'><b>".$row['trailer_name']."</b></span>";	
			}
			
			$pta="".date("M j, Y H:i", strtotime($row['pickup_time']))."";			//pickup_time
			//$pta="".date("M j, Y H:i", strtotime($row['linedate_pickup_eta']))."";	//dispatch date...
						
			
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
					<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
					<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
					<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
					<td nowrap>$row[origin]</td>
					<td nowrap>$row[destination]</td>
					<td align='right'>".number_format($vmiles)."</td>
					<td align='right'>".number_format($dhmiles)."</td>
					<td nowrap align='right'>".$pta."</td>
					<td nowrap>".($row['dispatch_completed'] > 0 ? "Yes" : "<span style='color:#cc0000;'><b>No</b></span>")."</td>
					<td nowrap>$row[name_truck]</td>
					<td nowrap align='right'>$row[daily_run_otr]</td>
					<td nowrap align='right'>$row[daily_run_hourly]</td>
					<td nowrap>".$trailer_disp."</td>
					<td nowrap>$row[name_company]</td>
					<td align='right'>$".money_format('',$mrr_use_profit)."</td>
					<td nowrap>".$master_flag." <span id='load_master_".$row['load_handler_id']."'></span></td>
				</tr>
			";
			
			$truck_loads++;
			$truck_miles+=$vmiles;
			$truck_deadhead+=$dhmiles;
			$truck_otr+=$row['daily_run_otr'];
			$truck_hours+=$row['daily_run_hourly'];
			$truck_profit+=$mrr_use_profit;	
			
			$hourly_days+=$row['daily_run_hourly'];
			$otr_days+=$row['daily_run_otr'];
			
			$total_haul++;
					
			if(($vmiles + $dhmiles) > $long_hall_min)
			{				
				$long_haul++;
			}
			else
			{
				$short_haul++;
			}
			
			if(($vmiles + $dhmiles) >= 0)			$range_all++;
             
            if(($vmiles + $dhmiles) >=   0 && ($vmiles + $dhmiles) <= 50)		    $range_50++;		//  0-50
            if(($vmiles + $dhmiles) >= 51 && ($vmiles + $dhmiles) <= 200)		    $range_200++;		// 51-200
            if(($vmiles + $dhmiles) >= 201 && ($vmiles + $dhmiles) <= 500)		    $range_500++;		//201-500
            if(($vmiles + $dhmiles) >= 501)								            $range_501++;		//501-?
			
			//if(($vmiles + $dhmiles) >=   0 && ($vmiles + $dhmiles) <= 100)		$range_100++;		//  0-100
			//if(($vmiles + $dhmiles) >= 101 && ($vmiles + $dhmiles) <= 300)		$range_300++;		//101-300
			//if(($vmiles + $dhmiles) >= 301 && ($vmiles + $dhmiles) <= 500)		$range_500++;		//301-500
			//if(($vmiles + $dhmiles) >= 501)								        $range_501++;		//501-?
			
			//if(($vmiles + $dhmiles) >=   0 && ($vmiles + $dhmiles) <= 375)		$range_375++;		//  0-375
			//if(($vmiles + $dhmiles) >= 376 && ($vmiles + $dhmiles) <= 575)		$range_575++;		//376-575
			//if(($vmiles + $dhmiles) >= 576)								        $range_576++;		//576-?
								
			// if the user selected to show the detailed stops for this dispatch, load them and show them
			if(isset($_POST['show_stops']) || $show_this_set_of_stops==1) {
				$sql = "
					select load_handler_stops.*,
						(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
						(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name
					
					from load_handler_stops
					where load_handler_stops.deleted = 0
						and load_handler_stops.trucks_log_id = '$row[id]'
					order by load_handler_stops.linedate_pickup_eta
				";
				$data_stops = simple_query($sql);
				
				while($row_stop = mysqli_fetch_array($data_stops)) {
					
					$stop_typer="Shipper";
					if($row_stop['stop_type_id']==2)		$stop_typer="Consignee";
					
					$mrr_show_switch="";
					if($row_stop['start_trailer_name'] != $row_stop['end_trailer_name'])
					{
						$mrr_show_switch="Trailer <b>".$row_stop['start_trailer_name']."</b><br>Switched to ".$row_stop['end_trailer_name'];	
					}
					
					echo "
						<tr style='background-color:#e2ffe4'>
							<td valign='top' nowrap align='right'>Stop:</td>
							<td valign='top' colspan='2' nowrap align='right'>".$stop_typer.": $row_stop[shipper_name]</td>
							<td valign='top' colspan='3' nowrap>$row_stop[shipper_address1] $row_stop[shipper_city], $row_stop[shipper_state] $row_stop[shipper_zip]</td>
							<td valign='top' colspan='3'>Pickup: ".date("M j, Y H:i", strtotime($row_stop['linedate_pickup_eta']))."</td>
							<td valign='top' colspan='4'>".$mrr_show_switch."</td>							
							<td valign='top' colspan='2'>
								".($row_stop['linedate_completed'] > 0 ? "Completed: ".date("M j, Y H:i", strtotime($row_stop['linedate_completed'])) : "")."
							</td>
						</tr>
					";
				}
			}
		}
		
		//echo mrr_pull_unavailable_driver_days_and_codes($_POST['date_from'],$_POST['date_to'],$_POST['driver_id']);		//Turned off for James on 3/20/2019... but not sure why or when this was created for the report.
		
		if($sorter=="trucks.name_truck asc,")
		{					// && $last_truck_was!="" && $last_truck_was!=$row['name_truck']
			echo "
				<tr>
					<td colspan='5' align='right'><b>Truck ".$last_truck_was." Totals: (".$truck_loads.")</b> </td>
					<td align='right'><b>".number_format($truck_miles)."</b></td>
					<td align='right'><b>".number_format($truck_deadhead)."</b></td>
					<td nowrap>&nbsp;</td>
					<td nowrap>&nbsp;</td>
					<td nowrap>&nbsp;</td>
					<td nowrap align='right'><b>".$truck_otr."</b></td>
					<td nowrap align='right'><b>".$truck_hours."</b></td>
					<td nowrap>&nbsp;</td>
					<td nowrap>&nbsp;</td>
					<td align='right'><b>$".money_format('',$truck_profit)."</b></td>
					<td nowrap>&nbsp;</td>
				</tr>
				<tr>
					<td colpsan='16'>&nbsp;</td>
				</tr>
			";	
			
			$truck_loads=0;
			$truck_miles=0;
			$truck_deadhead=0;
			$truck_otr=0;
			$truck_hours=0;
			$truck_profit=0;	
		}
		
		
		$short_percent_haul=0;
		$long_percent_haul=0;
		if($total_haul>0)
		{
			$short_percent_haul=$short_haul / $total_haul * 100;
			$long_percent_haul=$long_haul / $total_haul * 100;	
		}
		
		
		$percent_100=0;
		$percent_300=0;
		$percent_500=0;
		$percent_501=0;
    
        $percent_50=0;
        $percent_200=0;
		
		$percent_375=0;
		$percent_575=0;
		$percent_576=0;
		
		if($range_all>0)
		{
			$percent_100= $range_100 / $range_all * 100;
			$percent_300= $range_300 / $range_all * 100;
			$percent_500= $range_500 / $range_all * 100;
			$percent_501= $range_501 / $range_all * 100;
             
            $percent_50= $range_50 / $range_all * 100;
            $percent_200= $range_200 / $range_all * 100;
			
			$percent_375= $range_375 / $range_all * 100;
			$percent_575= $range_575 / $range_all * 100;
			$percent_576= $range_576 / $range_all * 100;	
		}	
	?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan='16'>
			<hr>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td nowrap>Load ID</td>
		<td nowrap>Dispatch ID</td>
		<td>Driver</td>
		<td>Origin</td>
		<td>Destination</td>
		<td align='right'>Miles</td>
		<td align='right'>Deadhead</td>
		<td>Date</td>
		<td><span title='Dispatch has been marked completed.'>Done</span></td>
		<td>Truck</td>
		<td>OTR</td>
		<td>Hourly</td>		
		<td>Trailer</td>
		<td>Customer</td>
		<td align='right'>Profit</td>
		<td>MasterLoad</td>
	</tr>
	<tr>
		<td colspan='5'><?=number_format($load_counter)?> Load(s)</td>
		
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
		<td colspan='7'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan='4'><?=number_format($counter)?> dispatch(es)</td>
		
		<td align='right'><?=number_format($total_miles)?></td>
		<td align='right'><?=number_format($total_deadhead)?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td align='right'><?= $otr_days ?></td>
		<td align='right'><?= $hourly_days ?></td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
		
		<td align='right'>$<?=money_format('',$total_profit)?></td>
		<td align='right'>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='16'>
			<hr>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan='4'>Total Miles (including Deadhead and Hourly...)</td>
		<td colspan='2' align='right'><b><?=number_format(($total_miles+$total_deadhead))?></b></td>
		<td colspan='6'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='16'>
			<hr>
		</td>
	</tr>	
	<tr>
		<td>&nbsp;</td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='6'><?=number_format($short_haul)?> short haul(s) <span class='alert'>0 - <?=$long_hall_min ?></span> out of <?= $total_haul ?></td>
		<td align='right'><?=number_format($short_percent_haul,2)?>%</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='6'><?=number_format($long_haul)?> long haul(s) <span class='alert'><?=($long_hall_min +1) ?>+</span> out of <?= $total_haul ?></td>
		<td align='right'><?=number_format($long_percent_haul,2)?>%</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan='16'>
			<hr>
		</td>
	</tr>

    <tr>
        <td>&nbsp;</td>
        <td colspan='6'><b>Radius of Operations</b></td>
        <td colspan='6'><?=number_format($range_50)?> <span class='alert'>0 - 50</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
        <td align='right'><?=number_format($percent_50,2)?>%</td>
        <td align='right'>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan='6'>&nbsp;</td>
        <td colspan='6'><?=number_format($range_200)?> <span class='alert'>51 - 200</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
        <td align='right'><?=number_format($percent_200,2)?>%</td>
        <td align='right'>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan='6'>&nbsp;</td>
        <td colspan='6'><?=number_format($range_500)?> <span class='alert'>201 - 500</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
        <td align='right'><?=number_format($percent_500,2)?>%</td>
        <td align='right'>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan='6'>&nbsp;</td>
        <td colspan='6'><?=number_format($range_501)?> <span class='alert'>501+</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
        <td align='right'><?=number_format($percent_501,2)?>%</td>
        <td align='right'>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <!---
	<tr>
		<td>&nbsp;</td>
		<td colspan='6'><b>Radius of Operations</b></td>
		<td colspan='6'><?=number_format($range_375)?> Loaded Mile haul(s) <span class='alert'>0 - 375</span> out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_375,2)?>%</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='6'><?=number_format($range_575)?> Loaded Mile haul(s) <span class='alert'>376 - 575</span> out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_575,2)?>%</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='6'><?=number_format($range_576)?> Loaded Mile haul(s) <span class='alert'>576+</span> out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_576,2)?>%</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>	
	
	
	<tr>
		<td>&nbsp;</td>
		<td colspan='6'><b>Radius of Operations</b></td>
		<td colspan='6'><?=number_format($range_100)?> <span class='alert'>0 - 100</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_100,2)?>%</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='6'><?=number_format($range_300)?> <span class='alert'>101 - 300</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_300,2)?>%</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='6'><?=number_format($range_500)?> <span class='alert'>301 - 500</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_500,2)?>%</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan='6'>&nbsp;</td>
		<td colspan='6'><?=number_format($range_501)?> <span class='alert'>501+</span> Loaded Mile haul(s) out of <?= $range_all ?></td>
		<td align='right'><?=number_format($percent_501,2)?>%</td>
		<td align='right'>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	---->
	<tr>
		<td colspan='16'>
			&nbsp;
		</td>
	</tr>
	</tfoot>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	$('.tablesorter').tablesorter();
	
	function mrr_graduate_master_load(id,flg)
	{		
		flg_val="regular";
		if(flg > 0)	flg_val="Master";
		$('#load_master_'+id+'').html('');
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_graduate_load_to_master_load",
			   data: {
			   		"load_id":id,
			   		"master_load":flg
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		$.noticeAdd({text: "Load "+id+" has been set as a "+flg_val+" Load successfully."});
			   		$('#load_master_'+id+'').html(flg_val);
			   		//alert("Load "+id+" has been set as a "+flg_val+" Load successfully.");
			   }	
		});
	}
</script>
<? include('footer.php') ?>