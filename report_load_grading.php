<?
$usetitle = "Report - Load Grading";
$use_title = "Report - Load Grading";
?>
<? include('header.php') ?>
<?
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}

	$rfilter = new report_filter();
	$rfilter->show_customer 			= true;
	$rfilter->show_driver 			= true;
	$rfilter->show_truck 			= true;
	$rfilter->show_trailer 			= true;
	$rfilter->show_load_id 			= true;
	$rfilter->show_dispatch_id 		= true;
	$rfilter->show_origin	 		= true;
	$rfilter->show_destination 		= true;
	$rfilter->show_stops	 		= true;
	$rfilter->show_late_loads_only		= true;
	$rfilter->show_font_size			= true;
	$rfilter->search_sort_by_report	= true;
	$rfilter->show_filter();
	
	$load_counter=0;
	$load_arr[0]=0;
			
 	if(isset($_POST['build_report'])) { 
		
		?>
		<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1800px;text-align:left'>
     	<tr>
     		<td colspan='16' align='left'>
     			<center><span class='section_heading'><?=$use_title ?></span></center>			
     		</td>
     	</tr>
		<?
		
		$search_date_range = '';
		if($_POST['dispatch_id'] != '' || $_POST['load_handler_id'] != '') {
			
		} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and load_handler_stops.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
				and load_handler_stops.linedate_pickup_eta <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
			";
		}
		
		$mrr_trailer_find="";
		$mrr_trailer_find2="";
		
		if($_POST['trailer_id'] > 0)
		{			
			$mrr_trailer_find2=" and (load_handler_stops.start_trailer_id='".sql_friendly($_POST['trailer_id'])."' or load_handler_stops.end_trailer_id='".sql_friendly($_POST['trailer_id'])."') ";			
		}
		
		$sorter="";	
		
		if(isset($_POST['search_sort_by_report']))
		{
			if($_POST['search_sort_by_report']=='date')			$sorter="";
			if($_POST['search_sort_by_report']=='load')			$sorter="load_handler_stops.load_handler_id asc,";
			if($_POST['search_sort_by_report']=='dispatch')		$sorter="load_handler_stops.trucks_log_id asc,";
			if($_POST['search_sort_by_report']=='driver')		$sorter="drivers.name_driver_last asc,drivers.name_driver_first asc,";
			if($_POST['search_sort_by_report']=='customer')		$sorter="customers.name_company asc,";			
		}
		
		$sql = "
			select load_handler_stops.*, 
				(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
				(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name,
				load_handler_stops.start_trailer_id as cur_trailer_id,
				load_handler.preplan_driver_id as driver_id,
				load_handler.preplan_driver2_id as driver2_id,				
				load_handler.origin_city,
				load_handler.origin_state,
				load_handler.dest_city,
				load_handler.dest_state,
				drivers.name_driver_first,
				drivers.name_driver_last,
				d2.name_driver_first as name_driver_first2,
				d2.name_driver_last as name_driver_last2,
				'' as name_truck,
				0 as truck_id,
				load_handler.customer_id,
				customers.name_company,	
				load_handler.master_load	
							
			from load_handler_stops
				left join load_handler on load_handler.id = load_handler_stops.load_handler_id and load_handler.deleted = 0	
				left join trucks_log on trucks_log.id = load_handler_stops.trucks_log_id and trucks_log.deleted = 0	
				left join drivers on drivers.id = load_handler.preplan_driver_id
				left join drivers d2 on d2.id = load_handler.preplan_driver2_id
				left join customers on customers.id = load_handler.customer_id
				
			where load_handler_stops.deleted = 0
				and load_handler_stops.trucks_log_id=0
				and load_handler.deleted = 0
				 
				$search_date_range
				
				".($_POST['dispatch_id'] ? " and load_handler_stops.trucks_log_id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
				".($_POST['load_handler_id'] ? " and load_handler_stops.load_handler_id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and (load_handler.preplan_driver_id = '".sql_friendly($_POST['driver_id'])."' or load_handler.preplan_driver2_id = '".sql_friendly($_POST['driver_id'])."')" : '') ."
								
				".$mrr_trailer_find2."
				
				".(isset($_POST['late_loads_only']) ? "and load_handler_stops.stop_grade_id='4'" : "")."
				
				".($_POST['customer_id'] ? " and load_handler.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			
			order by ".$sorter."load_handler_stops.linedate_pickup_eta
		";
		$data = simple_query($sql);
		$mn=mysqli_num_rows($data);
	
     	//PREPLANNED LOADS display.
     	if($mn > 0) 
     	{
     	?>
     	<tr style='font-weight:bold'>
     		<td valign='top'>LoadID</td>
            <td valign='top'>DispID</td>
     		<td valign='top'>Customer</td>
     		<td valign='top'>Stop</td>
     		<td valign='top'>Shipper</td>
     		<td valign='top'>Origin</td>
     		<td valign='top'>Destination</td>
     		<td valign='top'>Truck</td>
     		<td valign='top'>Trailer</td>		
     		<td valign='top'>Driver</td>
     		<td valign='top'>Pickup</td>
     		<td valign='top'>Completed</td>
     		<td valign='top'>Grade</td>
     		<td valign='top'>Fault</td>
     		<td valign='top'>Reason</td>
     		<td valign='top'>Score</td>
     	</tr>
     	<?
     		$counterp=0;
     		while($row = mysqli_fetch_array($data)) 
     		{
     			$counterp++;
     			     			
     			$stop_typer="Shipper";
     			if($row['stop_type_id']==2)		$stop_typer="Consignee";
     			
     			$mrr_show_switch="";
     			if($row['start_trailer_name'] != $row['end_trailer_name'])
     			{
     				$mrr_show_switch="<br><b>Switched: ".$row['end_trailer_name']."</b>";	
     			}
     			
     			$graded=$row['stop_grade_id'];
     			$reason=trim($row['stop_grade_note']);
     			$fault=$row['grade_fault_id'];
     			
				if($graded==1 || $graded==2 || $graded==3 || $graded==4)		$graded=4;
				if($graded==5 || $graded==6 || $graded==7 || $graded==8)		$graded=5;
			
				$stop_grader=mrr_load_stop_grade_decoder($graded);
				$stop_fault=mrr_load_stop_fault_decoder($fault);	
     									
     			$comp_date="";
     			if($row['trucks_log_id']==0)			$comp_date="<span class='alert'>No Dispatch</span>";
     			if(isset($row['linedate_completed']) && $row['linedate_completed']!="0000-00-00 00:00:00")	$comp_date="".date("m/d/Y H:i", strtotime($row['linedate_completed']))."";
     			
     			$cname="<b>".mrr_find_quick_customer_name($row['grade_fault_customer_id'])."</b>";
     			$dname="<b>".mrr_fetch_driver_name($row['grade_fault_driver_id'])."</b>";
     			$tr1_name="<b>".mrr_find_quick_truck_name($row['grade_fault_truck_id'])."</b>";
     			$tr2_name="<b>".mrr_find_quick_trailer_name($row['grade_fault_trailer_id'])."</b>";     			
     			
				$selbx_cust="<input type='hidden' name='grade_fault_".$row['id']."_preplan_customer_id' id='grade_fault_".$row['id']."_preplan_customer_id' value='".$row['grade_fault_customer_id']."'>".$cname."";
				$selbx_driver="<input type='hidden' name='grade_fault_".$row['id']."_preplan_driver_id' id='grade_fault_".$row['id']."_preplan_driver_id' value='".$row['grade_fault_driver_id']."'>".$dname."";
				$selbx_truck="<input type='hidden' name='grade_fault_".$row['id']."_preplan_truck_id' id='grade_fault_".$row['id']."_preplan_truck_id' value='".$row['grade_fault_truck_id']."'>".$tr1_name."";
				$selbx_trail="<input type='hidden' name='grade_fault_".$row['id']."_preplan_trailer_id' id='grade_fault_".$row['id']."_preplan_trailer_id' value='".$row['grade_fault_trailer_id']."'>".$tr2_name."";
				
     			
     			echo "
     				<tr class='".($counterp % 2 == 1 ? 'odd' : 'even')."'>
     					<td valign='top'>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=".$row['load_handler_id']."' target='view_load_".$row['load_handler_id']."'>".$row['load_handler_id']."</a>" : "")."</td>					
     					<td valign='top'>".($row['trucks_log_id'] > 0 ? "<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['trucks_log_id']."' target='view_disp_".$row['trucks_log_id']."'>".$row['trucks_log_id']."</a>" : "")."</td>
     					<td valign='top' nowrap>".$row['name_company']."</td>
     					<td valign='top'>".$row['id']." <input type='hidden' name='my_stop_".$counterp."_preplan' id='my_stop_".$counterp."_preplan' value='".$row['id']."'><br>".$stop_typer."</td>
     					<td valign='top'>".$row['shipper_name']."<br>".$row['shipper_city'].", ".$row['shipper_state']." ".$row['shipper_zip']."</td>
     					<td valign='top'>".$row['origin_city'].", ".$row['origin_state']."</td>
     					<td valign='top'>".$row['dest_city'].", ".$row['dest_state']."</td>
     					<td valign='top'>PREPLANNED</td>
     					<td valign='top'>".$row['start_trailer_name']."".$mrr_show_switch."</td>	
     					<td valign='top' nowrap>".$row['name_driver_first']." ".$row['name_driver_last']."".($row['driver2_id'] > 0 ? "<br>".$row['name_driver_first2']." ".$row['name_driver_last2']."" : "")."</td>
     					<td valign='top' nowrap>".date("m/d/Y H:i", strtotime($row['linedate_pickup_eta']))."</td>
     					<td valign='top' nowrap>".$comp_date."</td>
     					<td valign='top' nowrap><input type='hidden' name='stop_grade_id_".$row['id']."_preplan' id='stop_grade_id_".$row['id']."_preplan' value='".$graded."'>".$stop_grader."</td>
						<td valign='top' nowrap><input type='hidden' name='stop_fault_id_".$row['id']."_preplan' id='stop_fault_id_".$row['id']."_preplan' value='".$fault."'>".$stop_fault."</td>
     					<td valign='top'>".$reason."</td>
     					<td valign='top' align='right'><span id='stop_grade_score_".$row['id']."_preplan'></span></td>
     				</tr>
     				<tr class='".($counterp % 2 == 1 ? 'odd' : 'even')."'>
     					<td valign='top'>&nbsp;</td>	
     					<td valign='top'>&nbsp;</td>				
     					<td valign='top' colspan='5' align='right'>Fault: ".$selbx_cust."</td>
     					<td valign='top'>".$selbx_truck."</td>
     					<td valign='top'>".$selbx_trail."</td>	
     					<td valign='top'>".$selbx_driver."</td>
     					<td valign='top'>&nbsp;<input type='hidden' name='stop_".$row['id']."_preplan_cust' id='stop_".$row['id']."_preplan_cust' value='".$row['customer_id']."'></td>	
     					<td valign='top'>&nbsp;
     						<input type='hidden' name='stop_".$row['id']."_preplan_driver' id='stop_".$row['id']."_preplan_driver' value='".$row['preplan_driver_id']."'>
     						<input type='hidden' name='stop_".$row['id']."_preplan_driver2' id='stop_".$row['id']."_preplan_driver2' value='".$row['preplan_driver2_id']."'>
     					</td>	
     					<td valign='top'>&nbsp;<input type='hidden' name='stop_".$row['id']."_preplan_truck' id='stop_".$row['id']."_preplan_truck' value='".$row['truck_id']."'></td>	
     					<td valign='top'>&nbsp;<input type='hidden' name='stop_".$row['id']."_preplan_trailer' id='stop_".$row['id']."_preplan_trailer' value='".$row['start_trailer_id']."'></td>	
     					<td valign='top'>&nbsp;</td>	
     					<td valign='top'>&nbsp;</td>	
     				</tr>
     			";	    					
     		}	
     	    	echo "<tr><td>&nbsp;</td></tr>";
     	}	//END PREPLANNED LOADS
     		
		$sql = "
			select load_handler_stops.*, 
				(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
				(select trailers.trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name,
				trucks_log.trailer_id as cur_trailer_id,
				trucks_log.driver_id,
				trucks_log.driver2_id,
				trucks_log.origin,
				trucks_log.origin_state,
				trucks_log.destination,
				trucks_log.destination_state,
				drivers.name_driver_first,
				drivers.name_driver_last,
				d2.name_driver_first as name_driver_first2,
				d2.name_driver_last as name_driver_last2,
				trucks_log.truck_id,
				trucks.name_truck,
				load_handler.customer_id,
				customers.name_company,	
				load_handler.master_load	
							
			from load_handler_stops
				left join load_handler on load_handler.id = load_handler_stops.load_handler_id and load_handler.deleted = 0
				left join trucks_log on trucks_log.id = load_handler_stops.trucks_log_id and trucks_log.deleted = 0				
				left join drivers on drivers.id = trucks_log.driver_id
				left join drivers d2 on d2.id = trucks_log.driver2_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				
			where load_handler_stops.deleted = 0
				and load_handler.deleted = 0
				and trucks_log.deleted = 0
				
				$search_date_range
				
				".($_POST['dispatch_id'] ? " and load_handler_stops.trucks_log_id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
				".($_POST['load_handler_id'] ? " and load_handler_stops.load_handler_id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."')" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				
				".$mrr_trailer_find2."
				
				".(isset($_POST['late_loads_only']) ? "and load_handler_stops.stop_grade_id='4'" : "")."
				
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			
			order by ".$sorter."load_handler_stops.linedate_pickup_eta
		";
		$data = simple_query($sql);
     	?>
     	
     	<tr style='font-weight:bold'>
     		<td valign='top'>LoadID</td>
            <td valign='top'>DispID</td>
     		<td valign='top'>Customer</td>
     		<td valign='top'>Stop</td>
     		<td valign='top'>Shipper</td>
     		<td valign='top'>Origin</td>
     		<td valign='top'>Destination</td>
     		<td valign='top'>Truck</td>
     		<td valign='top'>Trailer</td>		
     		<td valign='top'>Driver</td>
     		<td valign='top'>Pickup</td>
     		<td valign='top'>Completed</td>
     		<td valign='top'>Grade</td>
     		<td valign='top'>Fault</td>
     		<td valign='top'>Reason</td>
     		<td valign='top'>Score</td>
     	</tr>
     	<?
		$counter = 0;
		$last_truck_was="";
		
		
		while($row = mysqli_fetch_array($data)) 
		{
			$counter++;
			
			if($sorter=="trucks.name_truck asc," && $last_truck_was!="" && $last_truck_was!=$row['name_truck'])
			{
				//
			}
			$last_truck_was=$row['name_truck'];
			
			
			$stop_typer="Shipper";
			if($row['stop_type_id']==2)		$stop_typer="Consignee";
			
			$mrr_show_switch="";
			if($row['start_trailer_name'] != $row['end_trailer_name'])
			{
				$mrr_show_switch="<br><b>Switched: ".$row['end_trailer_name']."</b>";	
			}
			
			$graded=$row['stop_grade_id'];
			$reason=trim($row['stop_grade_note']);
			$fault=$row['grade_fault_id'];
			
			if($graded==1 || $graded==2 || $graded==3 || $graded==4)		$graded=4;
			if($graded==5 || $graded==6 || $graded==7 || $graded==8)		$graded=5;
			
			$stop_grader=mrr_load_stop_grade_decoder($graded);	//mrr_select_load_stop_grades("stop_grade_id_".$row['id']."",$graded,"Ungraded"," onChange='mrr_calc_score(".$row['id'].");'");	// onChange='js_update_stop_commpleted(".$row['id'].")'
			$stop_fault=mrr_load_stop_fault_decoder($fault);		//mrr_select_load_stop_faults("stop_fault_id_".$row['id']."",$fault,""," onChange='mrr_calc_score(".$row['id'].");'");
			
			$cname="<b>".mrr_find_quick_customer_name($row['grade_fault_customer_id'])."</b>";
     		$dname="<b>".mrr_fetch_driver_name($row['grade_fault_driver_id'])."</b>";
     		$tr1_name="<b>".mrr_find_quick_truck_name($row['grade_fault_truck_id'])."</b>";
     		$tr2_name="<b>".mrr_find_quick_trailer_name($row['grade_fault_trailer_id'])."</b>";  
     			
			$selbx_cust="<input type='hidden' name='grade_fault_".$row['id']."_customer_id' id='grade_fault_".$row['id']."_customer_id' value='".$row['grade_fault_customer_id']."'>".$cname."";
			$selbx_driver="<input type='hidden' name='grade_fault_".$row['id']."_driver_id' id='grade_fault_".$row['id']."_driver_id' value='".$row['grade_fault_driver_id']."'>".$dname."";
			$selbx_truck="<input type='hidden' name='grade_fault_".$row['id']."_truck_id' id='grade_fault_".$row['id']."_truck_id' value='".$row['grade_fault_truck_id']."'>".$tr1_name."";
			$selbx_trail="<input type='hidden' name='grade_fault_".$row['id']."_trailer_id' id='grade_fault_".$row['id']."_trailer_id' value='".$row['grade_fault_trailer_id']."'>".$tr2_name."";
			
						
			$comp_date="";
			if($row['trucks_log_id']==0)			$comp_date="<span class='alert'>No Dispatch</span>";
			if(isset($row['linedate_completed']) && $row['linedate_completed']!="0000-00-00 00:00:00")	$comp_date="".date("m/d/Y H:i", strtotime($row['linedate_completed']))."";
			
			//<a href='add_entry_truck.php?id=".$row['trucks_log_id']."' target='view_dispatch_".$row['trucks_log_id']."'>".$row['trucks_log_id']."</a>
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
					<td valign='top'>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=".$row['load_handler_id']."' target='view_load_".$row['load_handler_id']."'>".$row['load_handler_id']."</a>" : "")."</td>
					<td valign='top'>".($row['trucks_log_id'] > 0 ? "<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['trucks_log_id']."' target='view_disp_".$row['trucks_log_id']."'>".$row['trucks_log_id']."</a>" : "")."</td>
					<td valign='top' nowrap>".$row['name_company']."</td>
					<td valign='top'>".$row['id']." <input type='hidden' name='my_stop_".$counter."' id='my_stop_".$counter."' value='".$row['id']."'><br>".$stop_typer."</td>					
					<td valign='top'>".$row['shipper_name']."<br>".$row['shipper_city'].", ".$row['shipper_state']." ".$row['shipper_zip']."</td>
					<td valign='top'>".$row['origin'].", ".$row['origin_state']."</td>
					<td valign='top'>".$row['destination'].", ".$row['destination_state']."</td>
					<td valign='top'>".$row['name_truck']."</td>
					<td valign='top'>".$row['start_trailer_name']."".$mrr_show_switch."</td>	
					<td valign='top' nowrap>".$row['name_driver_first']." ".$row['name_driver_last']."".($row['driver2_id'] > 0 ? "<br>".$row['name_driver_first2']." ".$row['name_driver_last2']."" : "")."</td>
					<td valign='top' nowrap>".date("m/d/Y H:i", strtotime($row['linedate_pickup_eta']))."</td>
					<td valign='top' nowrap>".$comp_date."</td>
					<td valign='top' nowrap><input type='hidden' name='stop_grade_id_".$row['id']."' id='stop_grade_id_".$row['id']."' value='".$graded."'>".$stop_grader."</td>
					<td valign='top' nowrap><input type='hidden' name='stop_fault_id_".$row['id']."' id='stop_fault_id_".$row['id']."' value='".$fault."'>".$stop_fault."</td>
					<td valign='top'>".$reason."</td>
					<td valign='top' align='right'><span id='stop_grade_score_".$row['id']."'></span></td>
				</tr>
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
     				<td valign='top'>&nbsp;</td>	
     				<td valign='top'>&nbsp;</td>				
     				<td valign='top' colspan='5' align='right'>Fault: ".$selbx_cust."</td>
     				<td valign='top'>".$selbx_truck."</td>
     				<td valign='top'>".$selbx_trail."</td>	
     				<td valign='top'>".$selbx_driver."</td>
     				<td valign='top'>&nbsp;<input type='hidden' name='stop_".$row['id']."_cust' id='stop_".$row['id']."_cust' value='".$row['customer_id']."'></td>	
     				<td valign='top'>&nbsp;
     					<input type='hidden' name='stop_".$row['id']."_driver' id='stop_".$row['id']."_driver' value='".$row['driver_id']."'>
     					<input type='hidden' name='stop_".$row['id']."_driver2' id='stop_".$row['id']."_driver2' value='".$row['driver2_id']."'>
     				</td>	
     				<td valign='top'>&nbsp;<input type='hidden' name='stop_".$row['id']."_truck' id='stop_".$row['id']."_truck' value='".$row['truck_id']."'></td>	
     				<td valign='top'>&nbsp;<input type='hidden' name='stop_".$row['id']."_trailer' id='stop_".$row['id']."_trailer' value='".$row['start_trailer_id']."'></td>
     				<td valign='top'>&nbsp;</td>	
     				<td valign='top'>&nbsp;</td>	
     			</tr>
			";	
		}				
     	?>
     	<tr>
     		<td colspan='16'><input type='hidden' name='my_stops' id='my_stops' value='<?= $counter ?>'><hr></td>
     	</tr>
     	<tr>
     		<td valign='top' colspan='15' align='right'><b>Total Score</b></td>
     		<td valign='top' align='right'><span id='stop_grade_score_tot'></span></td>
     	</tr>
     	<tr>
     		<td valign='top' colspan='15' align='right'><b>Ungraded Stops Skipped</b></td>
     		<td valign='top' align='right'><span id='stop_grade_score_skip'></span></td>
     	</tr>
     	<tr>
     		<td valign='top' colspan='15' align='right'><b>Delivered Timely</b></td>
     		<td valign='top' align='right'><span id='stop_grade_score_avg' style='color:green; font-weight:bold;'></span></td>
     	</tr>
     	<tr>
     		<td valign='top' colspan='15' align='right'><b>Delivered Late</b></td>
     		<td valign='top' align='right'><span id='stop_grade_score_avg2' style='color:red; font-weight:bold;'></span></td>
     	</tr>
     	
		<tr>
     		<td colspan='16'><hr><br></td>
     	</tr>
     	<tr>
     		<td valign='top' colspan='154' align='right'><b>Preplan Total Score</b></td>
     		<td valign='top' align='right'><span id='stop_grade_score_tot_preplan'></span></td>
     	</tr>
     	<tr>
     		<td valign='top' colspan='15' align='right'><b>Preplan Stops Skipped</b></td>
     		<td valign='top' align='right'><span id='stop_grade_score_skip_preplan'></span></td>
     	</tr>
     	<tr>
     		<td valign='top' colspan='15' align='right'><b>Preplan Delivered Timely</b></td>
     		<td valign='top' align='right'><span id='stop_grade_score_avg_preplan' style='color:green; font-weight:bold;'></span></td>
     	</tr>
     	<tr>
     		<td valign='top' colspan='15' align='right'><b>Preplan Delivered Late</b></td>
     		<td valign='top' align='right'><span id='stop_grade_score_avg2_preplan' style='color:red; font-weight:bold;'></span></td>
     	</tr>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
	
	function mrr_calc_score_preplan(update_stop_id)
	{
		$('#stop_grade_score_tot_preplan').html('0');
		$('#stop_grade_score_skip_preplan').html('0');
		$('#stop_grade_score_avg_preplan').html('0%');
		$('#stop_grade_score_avg2_preplan').html('0%');
		
		var cntr=0;
		var skip=0;
		
		var tot=0;
		var tot2=0;
		var v=parseInt($('#my_stops_preplan ').val());
		for (i=1; i <= v; i++)
		{
			stopid=parseInt($('#my_stop_'+i+'_preplan').val());
			
			gradeid=parseInt($('#stop_grade_id_'+stopid+'_preplan').val());
			$('#stop_grade_score_'+stopid+'_preplan').html('');
			
			custid=parseInt($('#grade_fault_'+stopid+'_preplan_customer_id').val());
			driverid=parseInt($('#grade_fault_'+stopid+'_preplan_driver_id').val());
			truckid=parseInt($('#grade_fault_'+stopid+'_preplan_truck_id').val());
			trailerid=parseInt($('#grade_fault_'+stopid+'_preplan_trailer_id').val());
			
			old_cust=parseInt($('#stop_'+stopid+'_preplan_cust').val());
			old_driver=parseInt($('#stop_'+stopid+'_preplan_driver').val());
			old_driver2=parseInt($('#stop_'+stopid+'_preplan_driver2').val());
			old_truck=parseInt($('#stop_'+stopid+'_preplan_truck').val());
			old_trailer=parseInt($('#stop_'+stopid+'_preplan_trailer').val());
						
			if(custid > 0)													gradeid=0;		// && custid != old_cust  ...if it is them at all...do not count it for us.
			if(driverid > 0 && driverid != old_driver && driverid != old_driver2)		gradeid=0;
			if(truckid > 0 && truckid != old_truck)								gradeid=0;
			if(trailerid > 0 && trailerid != old_trailer)						gradeid=0;
			
			if(gradeid > 9)	gradeid=0;			
			
			if(gradeid > 0 && gradeid!=9)
			{	//counts one way or the other...
				if(gradeid > 0 && gradeid < 5)
				{
					//these are bad... no points...equally weighted    Epic Fail,Past Due,Very Late,Late (1-4)
					tot2++;
					cntr++;
					$('#stop_grade_score_'+stopid+'_preplan').html('0');
				}
				else				
				{	//these are good...points...equally weighted       On Time,Within Window,Early,Very Early (5-8)
					$('#stop_grade_score_'+stopid+'_preplan').html('1');	
					tot++;
					cntr++;
				}
			}	
			else
			{	//these do not count at all...Ungraded,Cancelled (0,9)
				cntr++;
				skip++;
			}	
			
			if(stopid==update_stop_id)
			{
				//mrr_update_stop_fault_grade_preplan(stopid);	
			}
			
		} 
		avg=0;
		avg2=0;
		
		if( (cntr - skip) > 0)
		{
			avg = tot / (cntr - skip) * 100;
			avg2 = tot2 / (cntr - skip) * 100;
		}		
		
		$('#stop_grade_score_tot_preplan').html(''+tot+'/'+(cntr - skip)+'');
		$('#stop_grade_score_skip_preplan').html(''+skip+'/'+cntr+'');
		$('#stop_grade_score_avg_preplan').html(''+mrrformatNumber(avg)+'%');
		$('#stop_grade_score_avg2_preplan').html(''+mrrformatNumber(avg2)+'%');		
	}	
	/*
	function mrr_update_stop_fault_grade_preplan(stopid)
	{
		gradeid=$('#stop_grade_id_'+stopid+'_preplan').val();
		faultid=$('#stop_fault_id_'+stopid+'_preplan').val();
		stopnote=$('#stop_grade_note_'+stopid+'_preplan').val();	
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_update_stop_fault_grade",
			   data: {
			   		"stop_id":stopid,
			   		"grade_id":gradeid,
			   		"fault_id":faultid,
			   		"stop_reason":stopnote
			   		},		   
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
			   		//$.noticeAdd({text: "Load "+id+" has been set as a "+flg_val+" Load successfully."});
			   		//$('#load_master_'+id+'').html(flg_val);
			   		//alert("Load "+id+" has been set as a "+flg_val+" Load successfully.");
			   }	
		});
	}
	*/
	
	
	
	function mrr_calc_score(update_stop_id)
	{
		$('#stop_grade_score_tot').html('0');
		$('#stop_grade_score_skip').html('0');
		$('#stop_grade_score_avg').html('0%');
		$('#stop_grade_score_avg2').html('0%');
		
		var cntr=0;
		var skip=0;
		
		var tot=0;
		var tot2=0;
		var v=parseInt($('#my_stops').val());
		for (i=1; i <= v; i++)
		{
			stopid=parseInt($('#my_stop_'+i+'').val());
			
			gradeid=parseInt($('#stop_grade_id_'+stopid+'').val());
			$('#stop_grade_score_'+stopid+'').html('');
						
			old_cust=parseInt($('#stop_'+stopid+'_cust').val());
			old_driver=parseInt($('#stop_'+stopid+'_driver').val());
			old_driver2=parseInt($('#stop_'+stopid+'_driver2').val());
			old_truck=parseInt($('#stop_'+stopid+'_truck').val());
			old_trailer=parseInt($('#stop_'+stopid+'_trailer').val());
						
			custid=parseInt($('#grade_fault_'+stopid+'_customer_id').val());
			driverid=parseInt($('#grade_fault_'+stopid+'_driver_id').val());
			truckid=parseInt($('#grade_fault_'+stopid+'_truck_id').val());
			trailerid=parseInt($('#grade_fault_'+stopid+'_trailer_id').val());
			
			if(custid > 0)													gradeid=0;		// && custid != old_cust  ...if it is them at all...do not count it for us.
			if(driverid > 0 && driverid != old_driver && driverid != old_driver2)		gradeid=0;
			if(truckid > 0 && truckid != old_truck)								gradeid=0;
			if(trailerid > 0 && trailerid != old_trailer)						gradeid=0;
			
			if(gradeid > 9)	gradeid=0;
						
			if(gradeid > 0 && gradeid!=9)
			{	//counts one way or the other...
				if(gradeid > 0 && gradeid < 5)
				{
					//these are bad... no points...equally weighted    Epic Fail,Past Due,Very Late,Late (1-4)
					tot2++;
					cntr++;
					$('#stop_grade_score_'+stopid+'').html('0');
				}
				else				
				{	//these are good...points...equally weighted       On Time,Within Window,Early,Very Early (5-8)
					$('#stop_grade_score_'+stopid+'').html('1');	
					tot++;
					cntr++;
				}
			}	
			else
			{	//these do not count at all...Ungraded,Cancelled (0,9)
				cntr++;
				skip++;
			}	
			
			if(stopid==update_stop_id)
			{
				//mrr_update_stop_fault_grade(stopid);			//do not run on the report page... copied from admin page...
			}
			
		} 
		avg=0;
		avg2=0;
		
		if( (cntr - skip) > 0)
		{
			avg = tot / (cntr - skip) * 100;
			avg2 = tot2 / (cntr - skip) * 100;
		}		
		
		$('#stop_grade_score_tot').html(''+tot+'/'+(cntr - skip)+'');
		$('#stop_grade_score_skip').html(''+skip+'/'+cntr+'');
		$('#stop_grade_score_avg').html(''+mrrformatNumber(avg)+'%');
		$('#stop_grade_score_avg2').html(''+mrrformatNumber(avg2)+'%');		
	}
	/*
	function mrr_update_stop_fault_grade(stopid)
	{
		gradeid=$('#stop_grade_id_'+stopid+'').val();
		faultid=$('#stop_fault_id_'+stopid+'').val();
		stopnote=$('#stop_grade_note_'+stopid+'').val();	
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_update_stop_fault_grade",
			   data: {
			   		"stop_id":stopid,
			   		"grade_id":gradeid,
			   		"fault_id":faultid,
			   		"stop_reason":stopnote
			   		},		   
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
			   		//$.noticeAdd({text: "Load "+id+" has been set as a "+flg_val+" Load successfully."});
			   		//$('#load_master_'+id+'').html(flg_val);
			   		//alert("Load "+id+" has been set as a "+flg_val+" Load successfully.");
			   }	
		});
	}
	*/
	
	mrr_calc_score(0);
	mrr_calc_score_preplan(0);
</script>
<? include('footer.php') ?>