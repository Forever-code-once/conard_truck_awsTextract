<? include('application.php') ?>
<?
	$use_title="Conard Equipment List Report";
?>
<? include('header.php') ?>
<table class='font_display_section' style='text-align:left;width:1200px'>
	
<? if(!isset($_GET['print']) && !isset($_POST['print'])) { ?>
	<?
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}
	?>
	<tr>
		<td valign='top'>
          	<div style='text-align:left;margin:10px'>
          		<div class='section_heading'>Equipment Listing</div>
          		Enter any date for the month you would like to build the equipment list for. <br>
          		For example, if you wanted <?=date("M, Y")?>, select any date during that month <br>
          		and the system will automatically generate the full month report.<br>
          	</div>
          </td>
          <td valign='top' align='right'>
          	<?
          		$rfilter = new report_filter();
          		$rfilter->show_date_range 		= false;
          		$rfilter->show_single_date 		= true;
          		$rfilter->show_font_size			= true;	
          		$rfilter->mrr_send_email_here		= true;
          		$rfilter->show_filter();
          	?>
          </td>
     </tr>
<? } ?>


<tr>
	<td colspan='2'>
<?
	$pn_track_tab=1;
	$report_tab="";
	$mrr_sql2="";
	
	$uuid = createuuid();
	$excel_filename = "equipment_list_$uuid.xls";
	$export_file = "";
	$use_excel=1;
	
	if(isset($_POST['build_report'])) 
	{
		// get our insurance rates
		
		
		//$primary_liability = get_option_value('expense_type_insurance', 'primary_liability', true);
		//$general_liability = get_option_value('expense_type_insurance', 'general_liability', true);
		//$physical_damage_liability = get_option_value('expense_type_insurance', 'physical_damage_liability', true);
		//$cargo_liability = get_option_value('expense_type_insurance', 'cargo_liability', true);
		
		$date_start = strtotime(date("m/1/Y", strtotime($_POST['report_date'])));
		$days_in_month = date("t", $date_start);
		$date_end = strtotime(date("m/".$days_in_month."/Y", $date_start));
		
		$mrr_excluded=0;
		
		$special_adderx="or equipment_history.linedate_returned >= '".date("Y-m-d", $date_end)."'";
		$special_addery="or eh.linedate_returned >= '".date("Y-m-d", $date_end)."'";
		if(date("Y-m", $date_end)=="2014-06")
		{
			$special_adderx="or equipment_history.linedate_returned > '".date("Y-m-d", $date_end)."' or equipment_history.linedate_returned = '2014-06-30 00:00:'";	
			//$special_addery="or eh.linedate_returned >= '".date("Y-m-d", $date_end)."' or eh.linedate_returned = '2014-06-30'";	
		}
		
		if(date("Ym", $date_end)<="201406")		$pn_track_tab=0;
		
		// get the number of trucks that were active for that date range
		$sql = "
			select equipment_id,
				trucks.*,
				equipment_history.linedate_aquired,
				equipment_history.linedate_returned,
				equipment_history.equipment_value,
					(
						select equipment_id
						
						from equipment_history eh
						where eh.deleted = 0
							and eh.replacement_xref_id = trucks.id
							and eh.linedate_aquired <= '".date("Y-m-d", $date_end)."'
							and (
								eh.linedate_returned = 0
								".$special_addery."
								)
						order by equipment_id desc
						limit 1
					) as replacement_truck_id
			
			from equipment_history, trucks
			where equipment_type_id = 1
				and equipment_history.linedate_aquired <= '".date("Y-m-d", $date_end)."'
				and (
					equipment_history.linedate_returned = 0
					".$special_adderx."
					)
				and equipment_history.deleted = 0
				and trucks.deleted = 0	
				and trucks.no_insurance = 0			
				and trucks.id = equipment_history.equipment_id

				
			order by trucks.truck_year, trucks.truck_make, trucks.name_truck
		";
		//$report_tab.= "<br>Query=".$sql.".<br>";
		$data_trucks = simple_query($sql);
		
		$act_truck_list[0]=0;
		$act_truck_cntr=0;
		
		
		// get a list of all the trucks used in the entire month
		$sql = "
			select equipment_history.id as myid,
				equipment_id,
				trucks.*,
				equipment_history.linedate_aquired,
				equipment_history.linedate_returned,
				equipment_history.equipment_value

			
			from equipment_history, trucks
			where equipment_type_id = '1'
				and equipment_history.linedate_returned >= '".date("Y-m-d", $date_start)."'
				and equipment_history.linedate_returned <= '".date("Y-m-d", $date_end)."'
				and equipment_history.linedate_returned != '2014-06-30'
				and equipment_history.deleted = 0
				and trucks.deleted = 0	
				and trucks.no_insurance = 0			
				and trucks.id = equipment_history.equipment_id

			order by trucks.truck_year, trucks.truck_make, trucks.name_truck
		";
		$data_trucks_all = simple_query($sql);		//
		
		
		// interchange_flag trailers
		$sql = "
			select equipment_id,
				trailers.*,
				equipment_history.linedate_aquired,
				equipment_history.linedate_returned,
				equipment_history.equipment_value
			
			from equipment_history, trailers
			where equipment_type_id = 2
				and equipment_history.linedate_aquired < '".date("Y-m-d", strtotime("1 day", $date_end))."'
				and (
					equipment_history.linedate_returned = 0
					or equipment_history.linedate_returned > '".date("Y-m-d", strtotime("1 day", $date_start))."'
						
					)
				and equipment_history.deleted = 0
				and trailers.id = equipment_history.equipment_id
				and trailers.no_insurance = 0
				and trailers.interchange_flag > 0
				and trailers.deleted = 0
			order by trailers.trailer_year, trailer_make, trailer_name
		";	
		$data_interchange = simple_query($sql);
		
		
		// get the number of trailers that were active for that date range
		$sql = "
			select equipment_id,
				trailers.*,
				equipment_history.linedate_aquired,
				equipment_history.linedate_returned,
				equipment_history.equipment_value
			
			from equipment_history, trailers
			where equipment_type_id = 2
				and equipment_history.linedate_aquired < '".date("Y-m-d", strtotime("1 day", $date_end))."'
				and (
					equipment_history.linedate_returned = 0
					or equipment_history.linedate_returned > '".date("Y-m-d", strtotime("1 day", $date_start))."'
						
					)
				and equipment_history.deleted = 0
				and trailers.id = equipment_history.equipment_id
				and trailers.no_insurance = 0
				and trailers.interchange_flag = 0
				and trailers.deleted = 0
			order by trailers.trailer_year, trailer_make, trailer_name
		";	
		$data_trailers = simple_query($sql);
		
		// get the active driver list for the month...405 is a reminder load generic and should not be part of insurance report tally...
		$sql = "
			select 
				(select COUNT(*) 
                     from trucks_log 
                     where trucks_log.deleted = 0 
                     	and trucks_log.driver_id = drivers.id 
                         and linedate_pickup_eta >= '".date("Y-m-d", strtotime("1 day", $date_start))." 00:00:00' 
                         and linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))." 00:00:00') as drive_usage,
				drivers.*			
			from drivers
			where deleted = 0
				and id!=405
				and id!=371
				and active > 0
			order by name_driver_last, name_driver_first
		";
		$mrr_sql2=$sql;
		$data_drivers = simple_query($sql);
		/*
			and (
					(linedate_started!='0000-00-00 00:00:00' and linedate_started<'".date("Y-m-d", strtotime("1 day", $date_end))."' and linedate_terminated>='".date("Y-m-d", strtotime("0 day", $date_start))."')
					or
					(linedate_started!='0000-00-00 00:00:00' and linedate_started<'".date("Y-m-d", strtotime("1 day", $date_end))."' and linedate_terminated='0000-00-00 00:00:00')
					or
					(linedate_rehire!='0000-00-00 00:00:00' and linedate_rehire<'".date("Y-m-d", strtotime("1 day", $date_end))."' and linedate_refire>='".date("Y-m-d", strtotime("0 day", $date_start))."')
					or
					(linedate_rehire!='0000-00-00 00:00:00' and linedate_rehire<'".date("Y-m-d", strtotime("1 day", $date_end))."' and linedate_refire='0000-00-00 00:00:00')
				)
			
			
			and (select COUNT(*) 
                     	from trucks_log 
                     	where trucks_log.deleted = 0 
                     		and trucks_log.driver_id = drivers.id 
                         	and linedate_pickup_eta >= '".date("Y-m-d", strtotime("0 day", $date_start))." 00:00:00' 
                         	and linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))." 00:00:00') > 0
			
			
			
			
			
			
			
			
			select distinct drivers.id,
				drivers.*
			
			from drivers, trucks_log
			where trucks_log.deleted = 0
				and linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
				and linedate_pickup_eta <= '".date("Y-m-d", $date_end)."'
				and (
					drivers.id = trucks_log.driver_id
					or drivers.id = trucks_log.driver2_id
				)
				and driver_id!=405
				and driver_id!=371

			order by drivers.name_driver_last, drivers.name_driver_first
		*/
		
		$sql = "
			select distinct(trucks.apu_serial), trucks.apu_value, trucks.apu_number, trucks.name_truck, trucks.id
			from trucks
			where trucks.deleted = 0				
				and trucks.peoplenet_tracking > 0	
				and trucks.apu_serial !='' 
				and trucks.apu_value > 0
			order by trucks.name_truck,trucks.apu_serial
		";
		$data_pn = simple_query($sql);	// 
		
		$billable_trucks = 0;
		$total_value = 0;
		$pn_value = 0;
		$replaced_trucks = 0;
		$interchange_trialers = 0;
		$interchange_value = 0;
		
		while($row_truck = mysqli_fetch_array($data_trucks)) {
			
			$e_value=$row_truck['equipment_value'];
			
			$act_truck_list[$act_truck_cntr]=$row_truck['id'];
			$act_truck_cntr++;
			
			
			if($row_truck['replacement_truck_id'] > 0 && $row_truck['linedate_returned']!="2014-06-30 00:00:00") {
				$replaced_trucks++;
			} else {
				$billable_trucks++;
				
				
				//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
				$vres=mrr_display_equipment_depreciation_final($date_start,$row_truck['id'],0);		//date, truck ID, trailer ID
				if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
				//if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
								
				$total_value += $e_value;
			}
			if($row_truck['insurance_exclude'] > 0)		$mrr_excluded += $e_value;
			
		}
		
		// get our total values interchange trailers
		while($row_val = mysqli_fetch_array($data_interchange)) {
			if(strtotime($row_val['linedate_returned']) > 0 && strtotime($row_val['linedate_returned']) < $date_end) {
			} else {
				
				$e_value=$row_val['equipment_value'];
     			
     			//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
     			$vres=mrr_display_equipment_depreciation_final($date_start,0,$row_val['id']);		//date, truck ID, trailer ID
     			if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
     			//if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
     			
				$total_value += $e_value;
				$interchange_value+=$e_value;
				$interchange_trialers++;
			}
		}
		
		// get our total values regular trailers...
		while($row_val = mysqli_fetch_array($data_trailers)) {
			if(strtotime($row_val['linedate_returned']) > 0 && strtotime($row_val['linedate_returned']) < $date_end) {
			} else {
				
				$e_value=$row_val['equipment_value'];
     			
     			//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
     			$vres=mrr_display_equipment_depreciation_final($date_start,0,$row_val['id']);		//date, truck ID, trailer ID
     			if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
     			//if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
     			
				
				$total_value += $e_value;
			}
		}
		
		// get our total values PN tracking unit...
		while($row_pn = mysqli_fetch_array($data_pn)) {
			if(strtotime($row_pn['linedate_returned']) > 0 && strtotime($row_pn['linedate_returned']) < $date_end) {
			} else {
				$e_value=$row_pn['apu_value'];
			
				//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
				$vres=mrr_display_equipment_depreciation_final($date_start,$row_pn['id'],0);		//date, truck ID, trailer ID
				//if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
				if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
			
				$pn_value += $e_value;			
				//$total_value += $e_value;
			}
		}
		
		@mysqli_data_seek($data_pn,0);
		@mysqli_data_seek($data_trucks,0);
		@mysqli_data_seek($data_trailers,0);
		@mysqli_data_seek($data_interchange,0);
		
		$total_value -= $mrr_excluded;
		/*
		if(date("Ym", $date_start) >= "201407")
		{
			$primary_liability=459.00;
			$general_liability=0;
			$physical_damage_liability=0.00229;
			$cargo_liability=69.00;
		}
		*/
		if(date("Ym", $date_start) <= "201406")
		{
			$primary_liability=357.536;
			$general_liability=0;
			$physical_damage_liability=0.00236;
			$cargo_liability=82.303;
		}
				
		//$primary_liability_cost = money_strip($primary_liability) * $billable_trucks;
		//$general_liability_cost = money_strip($general_liability) * $billable_trucks;
		//$physical_damage_liability_cost = money_strip($physical_damage_liability) * $total_value;
		//$cargo_liability_cost = money_strip($cargo_liability) * $billable_trucks;
		
		
		
		//$total_premium = $primary_liability_cost + $general_liability_cost + $physical_damage_liability_cost + $cargo_liability_cost;
		
		ob_start();
		
		$stylex=" style='font-weight:bold;'";
		$mrr_total_head = " style='font-weight:bold; width:1000px; text-align:right;'";
		$tablex=" border='1' cellpadding='1' cellspacing='1' width='1200'";
		$headerx=" style='background-color:#CCCCFF;'";
		
		$report_tab.="<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">Equipment Listing report for ".date("M j, Y", $date_start)." to " . date("M j, Y", $date_end)."</div>";
		/*
		//$report_tab.= $date_end;
		$report_tab.="			
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b>Type</b></th>
				<th align='right'><b>Amount</b></th>
				<th><b>Coverage Rates</b></th>
				<th align='right'><b>Earned Premium</b></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>NU</td>
				<td align='center'>".$billable_trucks."</td>
				<td>Primary Liabilty x $".number_format(money_strip($primary_liability),3)."</td>
				<td align='right'>$".number_format($primary_liability_cost,2)."</td>
			</tr>
			<tr>
				<td>NU</td>
				<td align='center'>".$billable_trucks."</td>
				<td>General Liabilty x $".money_format('',money_strip($general_liability))."</td>
				<td align='right'>$".money_format('', $general_liability_cost)."</td>
			</tr>
			<tr>
				<td>STV</td>
				<td align='center'>$".money_format('', $total_value)."</td>
				<td>Physical Damage x $physical_damage_liability <span style='color:orange;'>Excluded $".number_format($mrr_excluded,2)."</span></td>
				<td align='right'>$".money_format('', $physical_damage_liability_cost)."</td>
			</tr>
			<tr>
				<td>NU</td>
				<td align='center'>".$billable_trucks."</td>
				<td>Cargo x $".number_format(money_strip($cargo_liability),3)."</td>
				<td align='right'>$".number_format($cargo_liability_cost,2)."</td>
			</tr>
			<!--
			<tr>
				<td>Interchange</td>
				<td align='center'>".$interchange_trialers."</td>
				<td>Cargo x $".number_format(money_strip($cargo_liability),3)."</td>
				<td align='right'>$".number_format($cargo_liability_cost,2)."</td>
			</tr>
			-->
			</tbody>
			</table>
			<div".( $mrr_use_styles > 0 ? "".$mrr_total_head."" : " class='section_heading mrr_total_head'").">Total Earned Premium: $".money_format('',$total_premium)."</div>
			
			<div>&nbsp;</div>
		";
		*/
		
		$export_file .= "Conard Transportation Equipment List".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
		
		
		$export_file .= "Tractors".chr(9).
			"".$billable_trucks."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
		$export_file .= "".chr(9).
			"Year".chr(9).
			"Make".chr(9).
			"Model".chr(9).
			"VIN".chr(9).
			"Truck #".chr(9).
			"License Plate #".chr(9).
			"Value".chr(9);
		$export_file .= chr(13);	
		
		$report_tab.="	
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">Tractor Count: ".$billable_trucks."</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section tablesorter'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b></b></th>
				<th><b>Year</b></th>
				<th><b>Make</b></th>
				<th><b>Model</b></th>
				<th><b>VIN</b></th>
				<th nowrap><b>Truck #</b></th>
				<th nowrap><b>License Plate #</b></th>
				<th align='right'><b>Value</b></th>
			</tr>
			</thead>
			<tbody>
		";
		$counter = 0;
		$truck_value = 0;
		while($row_truck = mysqli_fetch_array($data_trucks)) {
			if($row_truck['replacement_truck_id'] > 0 && $row_truck['linedate_returned']!="2014-06-30 00:00:00") {
			} else {
				
				$e_value=$row_truck['equipment_value'];
				
				//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
				$vres=mrr_display_equipment_depreciation_final($date_start,$row_truck['id'],0);		//date, truck ID, trailer ID
				if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
				//if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
				
				/*
				if($row_truck['insurance_exclude'] > 0) {
					// don't count the value
				} else {
					$truck_value += $row_truck['equipment_value'];
				}
				*/
				$truck_value += $e_value;
				
				$mrr_mask1="";				$mrr_mask2="";
				if($row_truck['insurance_exclude'] > 0)	
				{
					$mrr_mask1="<span style='color:orange;'>";
					$mrr_mask2="</span>";
				}
				
				$owner_flag="Leased";
				if($row_truck['rental'] > 0)				$owner_flag="Rental";
				if($row_truck['company_owned'] > 0)		$owner_flag="<b>Conard</b>";
				
				$counter++;
				$report_tab.= "
					<tr>
						<td>$counter</td>
						<td>$row_truck[truck_year]</td>
						<td>$row_truck[truck_make]</td>
						<td>$row_truck[truck_model]</td>
						<td>$row_truck[vin]</td>
						<td>$row_truck[name_truck]</td>
						<td nowrap>$row_truck[license_plate_no] ".($row_truck['rental'] && $row_truck['insurance_exclude'] ? "(Rental)" : "")."</td>		
						<td align='right'>".$mrr_mask1."".($e_value > 0 ? "$".money_format('', $e_value) : "")."".$mrr_mask2."</td>
					</tr>
				";
				
				if($row_truck['id']==259 && 1==2)
				{
					$report_tab.= "
					<tr>
						<td colspan='13'>".$vres['sql']."</td>
					</tr>
				";	
				}
				
				$export_file .= "$counter".chr(9).
					"$row_truck[truck_year]".chr(9).
					"$row_truck[truck_make]".chr(9).
					"$row_truck[truck_model]".chr(9).
					"$row_truck[vin]".chr(9).
					"$row_truck[name_truck]".chr(9).
					"$row_truck[license_plate_no] ".($row_truck['rental'] && $row_truck['insurance_exclude'] ? "(Rental)" : "")."".chr(9).
					"".$mrr_mask1."".($e_value > 0 ? "$".money_format('', $e_value) : "")."".$mrr_mask2."".chr(9);
				$export_file .= chr(13);	
				
			}
		}		
		$export_file .= "Tractor".chr(9).
			"Total".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"$".money_format('',$truck_value)."".chr(9);
		$export_file .= chr(13);	
		
		
		$export_file .= chr(13);
		$export_file .= "Substituted Tractors".chr(9).
			"".$replaced_trucks."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
		$export_file .= "".chr(9).
			"Year".chr(9).
			"Make".chr(9).
			"Model".chr(9).
			"VIN".chr(9).
			"Truck #".chr(9).
			"License Plate #".chr(9).
			"Value".chr(9);
		$export_file .= chr(13);	
		
		$report_tab.= "
			</tbody>
			</table>
			<div".( $mrr_use_styles > 0 ? "".$mrr_total_head."" : " class='section_heading mrr_total_head'").">Total Tractor Value: $".money_format('',$truck_value)."</div>
			
			<div>&nbsp;</div>
			
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">Substituted Tractors: $replaced_trucks</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section tablesorter'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b></b></th>
				<th><b>Year</b></th>
				<th><b>Make</b></th>
				<th><b>Model</b></th>
				<th><b>VIN</b></th>
				<th><b>Truck #</b></th>
				<th nowrap><b>License Plate #</b></th>	
				<th align='right'><b>Value</b></th>				
			</tr>
			</thead>
			<tbody>
		";
		@mysqli_data_seek($data_trucks,0);
		$counter = 0;
		while($row_truck = mysqli_fetch_array($data_trucks)) {
			if($row_truck['replacement_truck_id'] > 0) {
				
				$counter++;
				
				$sql = "
					select name_truck
					
					from trucks
					where id = '".sql_friendly($row_truck['replacement_truck_id'])."'
				";
				$data_truck_name = simple_query($sql);
				$row_truck_name = mysqli_fetch_array($data_truck_name);
								
				$e_value=$row_truck['equipment_value'];
				
				//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
				$vres=mrr_display_equipment_depreciation_final($date_start,$row_truck['replacement_truck_id'],0);		//date, truck ID, trailer ID
				if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
				//if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
				
				$truck_value += $e_value;
				
				$report_tab.= "
					<tr>
						<td>$counter</td>
						<td>$row_truck[truck_year]</td>
						<td>$row_truck[truck_make]</td>
						<td>$row_truck[truck_model]</td>
						<td>$row_truck[vin]</td>
						<td>$row_truck[name_truck]</td>
						<td>$row_truck[license_plate_no]</td>						
						<td align='right'>".($e_value > 0 ? "$".money_format('', $e_value) : "")."</td>
					</tr>
				";
				
				$export_file .= "$counter".chr(9).
					"$row_truck[truck_year]".chr(9).
					"$row_truck[truck_make]".chr(9).
					"$row_truck[truck_model]".chr(9).
					"$row_truck[vin]".chr(9).
					"$row_truck[name_truck]".chr(9).
					"$row_truck[license_plate_no]".chr(9).
					"".($e_value > 0 ? "$".money_format('', $e_value) : "")."".chr(9);
				$export_file .= chr(13);	
			}
		}	
		
		
		$export_file .= chr(13);
		$export_file .= "Returned Tractors".chr(9).
			"".mysqli_num_rows($data_trucks_all)."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
		$export_file .= "".chr(9).
			"Year".chr(9).
			"Make".chr(9).
			"Model".chr(9).
			"VIN".chr(9).
			"Truck #".chr(9).
			"License Plate #".chr(9).
			"Value".chr(9);
		$export_file .= chr(13);	
			
		$report_tab.= "
			</tbody>
			</table>			
			
			<div>&nbsp;</div>
			
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">Returned Tractors: ".mysqli_num_rows($data_trucks_all)."</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b></b></th>
				<th><b>Year</b></th>
				<th><b>Make</b></th>
				<th><b>Model</b></th>
				<th><b>VIN</b></th>
				<th><b>Truck #</b></th>
				<th nowrap><b>Plate#</b></th>				
				<th align='right'><b>Value</b></th>
			</tr>
			</thead>
			<tbody>
		";
		
		$counter = 0;
		while($row_truck = mysqli_fetch_array($data_trucks_all)) {
			$counter++;
										
			$e_value=$row_truck['equipment_value'];
			
			//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
			$vres=mrr_display_equipment_depreciation_final($date_start,$row_truck['id'],0);		//date, truck ID, trailer ID
			if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
			//if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
			
			$truck_value += $e_value;				
			
			$report_tab.= "
				<tr>
					<td><span title='Equipment History ID=".$row_truck['myid']."'>$counter</span></td>
					<td>$row_truck[truck_year]</td>
					<td>$row_truck[truck_make]</td>
					<td>$row_truck[truck_model]</td>
					<td>$row_truck[vin]</td>
					<td>$row_truck[name_truck]</td>
					<td>$row_truck[license_plate_no]</td>					
					<td align='right'>".($e_value > 0 ? "$".money_format('', $e_value) : "")."</td>
				</tr>
			";
						
			$export_file .= "$counter".chr(9).
				"$row_truck[truck_year]".chr(9).
				"$row_truck[truck_make]".chr(9).
				"$row_truck[truck_model]".chr(9).
				"$row_truck[vin]".chr(9).
				"$row_truck[name_truck]".chr(9).
				"$row_truck[license_plate_no]".chr(9).
				"".($e_value > 0 ? "$".money_format('', $e_value) : "")."".chr(9);
			$export_file .= chr(13);	
		}		
		
		$export_file .= chr(13);
		$export_file .= "Tracking Units".chr(9).
			"".mysqli_num_rows($data_pn)."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
		$export_file .= "".chr(9).
			"Reference".chr(9).
			"".chr(9).
			"".chr(9).
			"Unit Serial Number".chr(9).
			"".chr(9).
			"".chr(9).
			"Value".chr(9);
		$export_file .= chr(13);	
		
		$report_tab.= "</tbody>
			</table>
			
			<div>&nbsp;</div>
			
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading pn_tracker_tab'").">PeopleNet Tracking Unit count: ".mysqli_num_rows($data_pn)."</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter pn_tracker_tab'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b></b></th>
				<th><b>Reference</b></th>
				<th><b>PeopleNet Tracking Unit Serial Number</b></th>
				<th align='right'><b>Value</b></th>
			</tr>
			</thead>
			<tbody>
		";
		
		$pn_value = 0;
		$pn_counter = 0;
		while($row_pn = mysqli_fetch_array($data_pn)) {
			$pn_counter++;
													
			$e_value=$row_pn['apu_value'];
			
			//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
			$vres=mrr_display_equipment_depreciation_final($date_start,$row_pn['id'],0);		//date, truck ID, trailer ID
			//if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
			if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
			
			$pn_value += $e_value;
			
			$found=0;
			for($i=0;$i < $act_truck_cntr;$i++)
			{
				if($act_truck_list[$i] == $row_pn['id'])	$found=1;	
			}
			
			$class_mrr="";
			if($found==0)		$class_mrr=" style='color:#CC0000;'";
			
			$report_tab.= "
				<tr>
					<td>$pn_counter</td>
					<td><span".$class_mrr.">$row_pn[name_truck]</span></td>
					<td>$row_pn[apu_serial]</td>
					<td align='right'>".($e_value > 0 ? "$".money_format('', $e_value) : "")."</td>
				</tr>
			";
					
			$export_file .= "$pn_counter".chr(9).
				"$row_pn[name_truck]".chr(9).
				"".chr(9).
				"".chr(9).
				"$row_pn[apu_serial]".chr(9).
				"".chr(9).
				"".chr(9).
				"".($e_value > 0 ? "$".money_format('', $e_value) : "")."".chr(9);
			$export_file .= chr(13);	
		}	
		
		$export_file .= "Tracking".chr(9).
			"Total".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"$".money_format('',$pn_value)."".chr(9);
		$export_file .= chr(13);		
		
		$export_file .= chr(13);
		$export_file .= "Trailers".chr(9).
			"".mysqli_num_rows($data_trailers)."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
		$export_file .= "".chr(9).
			"Year".chr(9).
			"Make".chr(9).
			"Model".chr(9).
			"VIN".chr(9).
			"Trailer #".chr(9).
			"License Plate #".chr(9).
			"Value".chr(9);
		$export_file .= chr(13);	
		
		$report_tab.= "</tbody>
			</table>
			
			<div".( $mrr_use_styles > 0 ? "".$mrr_total_head."" : " class='section_heading mrr_total_head'").">(PeopleNet Unit Value: $".money_format('',$pn_value).")</div>
			
			<div>&nbsp;</div>
			
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">Trailer count: ".mysqli_num_rows($data_trailers)."</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b></b></th>
				<th><b>Year</b></th>
				<th><b>Make</b></th>
				<th><b>Model</b></th>
				<th><b>VIN</b></th>
				<th><b>Trailer #</b></th>
				<th><b>License Plate #</b></th>
				<th align='right'><b>Value</b></th>
			</tr>
			</thead>
			<tbody>
		";
		
		
		$trailer_value = 0;
		$counter = 0;
		while($row_trailer = mysqli_fetch_array($data_trailers)) {
			if(strtotime($row_trailer['linedate_returned']) > 0 && strtotime($row_trailer['linedate_returned']) < $date_end) {
				$show_trailer_value = '';
			} else {											
     			$e_value=$row_trailer['equipment_value'];
     			
     			//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
     			$vres=mrr_display_equipment_depreciation_final($date_start,0,$row_trailer['id']);		//date, truck ID, trailer ID
     			if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
     			//if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
     			
     			$trailer_value += $e_value;				
				$show_trailer_value=$e_value;
				
				//$show_trailer_value = $row_trailer['equipment_value'];
				//$trailer_value += $row_trailer['equipment_value'];
			}
			$counter++;
			$report_tab.= "
				<tr>
					<td>$counter</td>
					<td>$row_trailer[trailer_year]</td>
					<td>$row_trailer[trailer_make]</td>
					<td>$row_trailer[trailer_model]</td>
					<td>$row_trailer[vin]</td>
					<td>$row_trailer[trailer_name]</td>
					<td>$row_trailer[license_plate_no]</td>
					<td align='right'>".($e_value > 0 ? "$".money_format('', $e_value) : "")."</td>
				</tr>
			";
			
			$export_file .= "$counter".chr(9).
				"$row_trailer[trailer_year]".chr(9).
				"$row_trailer[trailer_make]".chr(9).
				"$row_trailer[trailer_model]".chr(9).
				"$row_trailer[vin]".chr(9).
				"$row_trailer[trailer_name]".chr(9).
				"$row_trailer[license_plate_no]".chr(9).
				"".($e_value > 0 ? "$".money_format('', $e_value) : "")."".chr(9);
			$export_file .= chr(13);	
		}
		$export_file .= "Trailer".chr(9).
			"Total".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"$".money_format('',$trailer_value)."".chr(9);
		$export_file .= chr(13);	
		
		
		$report_tab.= "</tbody>
			</table>
			<div".( $mrr_use_styles > 0 ? "".$mrr_total_head."" : " class='section_heading mrr_total_head'").">Total Trailer Value: $".money_format('',$trailer_value)."</div>
			";
		/*
		$report_tab.="
						
			<div>&nbsp;</div>
			
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">Trailer Interchange count: ".mysqli_num_rows($data_interchange)."</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b></b></th>
				<th><b>Year</b></th>
				<th><b>Make</b></th>
				<th><b>Model</b></th>
				<th><b>VIN</b></th>
				<th><b>Trailer #</b></th>
				<th><b>License Plate #</b></th>
				<th><b>Date Out</b></th>
				<th><b>Date In</b></th>
				<th align='right'><b>Value</b></th>
			</tr>
			</thead>
			<tbody>
		";
		$trailer_value = 0;
		$counter = 0;
		while($row_trailer = mysqli_fetch_array($data_interchange)) {
			if(strtotime($row_trailer['linedate_returned']) > 0 && strtotime($row_trailer['linedate_returned']) < $date_end) {
				$show_trailer_value = '';
			} else {
								
				$e_value=$row_trailer['equipment_value'];
     			
     			//pull value from equipment tracking spreadsheet.... if value is "-1", no record, so use the equipment history value like planned.
     			$vres=mrr_display_equipment_depreciation_final($date_start,0,$row_trailer['id']);		//date, truck ID, trailer ID
     			if($vres['cur_equip_value'] >=0)			$e_value=	$vres['cur_equip_value'];
     			//if($vres['cur_unit_value'] >=0)			$e_value=	$vres['cur_unit_value'];
     			
     			$trailer_value += $e_value;				
				$show_trailer_value=$e_value;
								
				//$show_trailer_value = $row_trailer['equipment_value'];
				//$trailer_value += $row_trailer['equipment_value'];
			}
			$counter++;
			$report_tab.= "
				<tr>
					<td>$counter</td>
					<td>$row_trailer[trailer_year]</td>
					<td>$row_trailer[trailer_make]</td>
					<td>$row_trailer[trailer_model]</td>
					<td>$row_trailer[vin]</td>
					<td>$row_trailer[trailer_name]</td>
					<td>$row_trailer[license_plate_no]</td>
					<td>".($row_trailer['linedate_aquired'] > 0 ? date("m/d/Y", strtotime($row_trailer['linedate_aquired'])) : "")."</td>
					<td>".($row_trailer['linedate_returned'] > 0 ? date("m/d/Y", strtotime($row_trailer['linedate_returned'])) : "")."</td>
					<td align='right'>".($e_value > 0 ? "$".money_format('', $e_value) : "")."</td>
				</tr>
			";
		}
		
		
		$report_tab.= "
			</tbody>
			</table>
			<div".( $mrr_use_styles > 0 ? "".$mrr_total_head."" : " class='section_heading mrr_total_head'").">Total Trailer Interchange Value: $".money_format('',$trailer_value)."</div>
			
			<div>&nbsp;</div>
			
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">Driver count: ".mysqli_num_rows($data_drivers)."</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th><b></b></th>
				<th><b>First</b></th>
				<th><b>Last</b></th>
				<th><b>DOB</b></th>
				<th><b>State</b></th>
				<th><b>DL #</b></th>
				<th><b>First Hire Date</b></th>
				<th><b>First Terminated</b></th>
				<th><b>Last Hire Date</b></th>
				<th><b>Last Terminated</b></th>
			</tr>
			</thead>
			<tbody>
		";
				
		$counter = 0;
		while($row_driver = mysqli_fetch_array($data_drivers)) 
		{
			if($row_driver['drive_usage'] > 0 || 1==1)
			{
     			$counter++;
     			
          		$hire="".($row_driver['linedate_started'] > 0 ? date("m/d/Y", strtotime($row_driver['linedate_started'])) : "")."";
          		$fire="".($row_driver['linedate_terminated'] > 0 ? date("m/d/Y", strtotime($row_driver['linedate_terminated'])) : "")."";
          		
          		//if($row_driver['linedate_rehire'] > 0)
          		//{
          			$hire2="".($row_driver['linedate_rehire'] > 0 ? date("m/d/Y", strtotime($row_driver['linedate_rehire'])) : "")."";
          			$fire2="".($row_driver['linedate_refire'] > 0 ? date("m/d/Y", strtotime($row_driver['linedate_refire'])) : "")."";
          		//}
     			
     			$report_tab.= "
     				<tr>
     					<td>$counter</td>
     					<td>$row_driver[name_driver_first]</td>
     					<td>$row_driver[name_driver_last]</td>
     					<td>".($row_driver['linedate_birthday'] > 0 ? date("m/d/Y", strtotime($row_driver['linedate_birthday'])) : "")."</td>
     					<td>$row_driver[dl_state]</td>
     					<td>$row_driver[dl_number]</td>
     					<td>".$hire."</td>
     					<td>".$fire."</td>
     					<td>".$hire2."</td>
     					<td>".$fire2."</td>
     				</tr>
     			";
			}
		}
		$report_tab.= "</tbody>
			</table>
		";	//<br>Query:<br>".$mrr_sql2."<br>
		*/
		echo $report_tab;
		
		$pdf = ob_get_contents();
		ob_end_clean();
	
		$prefix="";
		if($use_excel > 0) 
		{
			$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
			fwrite($fp, $export_file); 
			fclose($fp);
			
			$prefix="<br><br><a href=\"http://trucking.conardlogistics.com/temp/".$excel_filename."\" target='_blank'>Click for Excel Version</a><br><br>";
			echo $prefix;
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
     			
     		$sentit=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject, $prefix.$pdf, $prefix.$pdf);
     		
     		$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
     		echo "<br><br><b>This report has ".$sent_msg." to '".$_POST['mrr_email_addr_name']."' at E-Mail address '".$_POST['mrr_email_addr']."'.</b><br><br>";
     		
     		$sentit=mrr_trucking_sendMail('dconard@conardlogistics.com',"Dale Conard",$From,$user_name,'','',$Subject,$pdf,$pdf);
     		$sentit=mrr_trucking_sendMail('jgriffith@conardlogistics.com',"James Griffith",$From,$user_name,'','',$Subject,$pdf,$pdf);
     		$sentit=mrr_trucking_sendMail('amassar@conardlogistics.com',"Anthony Massar",$From,$user_name,'','',$Subject,$pdf,$pdf);     		
     	}
	}
?>
	</td>
</tr>
</table>
<input type='hidden' name='excel_output_file' id='excel_output_file' value='<?= ($use_excel > 0 ? "/temp/".$excel_filename."" : "") ?>'><br><br><?= ($use_excel > 0 ? "/temp/".$excel_filename."" : "") ?>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	<? if($pn_track_tab==0) { ?>
		$('.pn_tracker_tab').hide();
	<? } ?>
</script>
<? include('footer.php') ?>