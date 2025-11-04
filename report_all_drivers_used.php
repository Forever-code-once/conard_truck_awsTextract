<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['truck_id'])) {
		$_POST['truck_id'] = $_GET['truck_id'];
		$_POST['show_dispatches'] = 1;
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
	//$rfilter->show_customer 				= true;
	//$rfilter->show_driver 				= true;
	//$rfilter->show_truck 				= true;
	//$rfilter->show_trailer 				= true;
	//$rfilter->show_dispatches			= true;
	////$rfilter->group_by_truck			= true;
	//$rfilter->first_dispatch_all_credit 	= true;
	//$rfilter->hide_non_first_dispatch 		= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

 	if(isset($_POST['build_report'])) { 
	
		/*
		$sql = "
			select trucks_log.*,
				(select min(linedate_pickup_eta) from load_handler_stops where load_handler_stops.trucks_log_id = trucks_log.id and deleted = 0) as linedate_pickup_eta_dispatch,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company,
				load_handler.actual_bill_customer,
				load_handler.actual_total_cost,
				load_handler.origin_city,
				load_handler.origin_state,
				load_handler.dest_city,
				load_handler.dest_state,
				load_handler.invoice_number,
				load_handler.linedate_pickup_eta,
				load_handler.actual_bill_customer - load_handler.actual_total_cost as load_profit,
				load_handler.actual_rate_fuel_surcharge,
				trucks_log.daily_run_otr,
				trucks_log.daily_run_hourly
			
			from trucks_log
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				left join load_handler on load_handler.id = trucks_log.load_handler_id
			where trucks_log.deleted = 0
			
				and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and trucks_log.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
				
				".($_POST['driver_id'] ? " and trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			
			order by drivers.name_driver_last, drivers.name_driver_first, trucks.name_truck
				
		";
		*/
		
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1100px;text-align:left'>
	<tr>
		<td colspan='15'>
			<center>
			<span class='section_heading'>Driver Report - Dispatch Count</span>
			</center>
		</td>
	</tr>
	<? 
		function mrr_get_first_and_last_load($driver,$date_from,$date_to)
		{
			$alinker="";
			$blinker="";
			$cntr=0;
			$status="";
			
			$sql="
				select id,
					linedate_pickup_eta,
					load_handler_id,
					(TO_DAYS('".date("Y-m-d", strtotime($date_to))." 23:59:59') - TO_DAYS(linedate_pickup_eta)) as days_from_now
				from trucks_log
				where trucks_log.deleted = 0			
					and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", strtotime($date_from))." 00:00:00'
					and trucks_log.linedate_pickup_eta <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
					and (trucks_log.driver_id = '".sql_friendly($driver)."'
						or
						trucks_log.driver2_id = '".sql_friendly($driver)."')
				order by linedate_pickup_eta asc
			";
			$data = simple_query($sql);	
			$mn=mysqli_num_rows($data);		
			while($row = mysqli_fetch_array($data))
			{
				if($cntr==0)
				{	//capture first link (dispatch)
					$alinker1="<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['load_handler_id']."</a>";
					$alinker2="<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['id']."</a>";
					$alinker3="<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".date("m/d/Y",strtotime($row['linedate_pickup_eta']))."</a>";	
					$status="<span class='alert'>Check Status</span>";
					if($row['days_from_now']  <= 10)
					{
						$status="";	
					}	
				}
				elseif($cntr+1 == $mn)
				{	//capture last link (dispatch)
					$blinker1="<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['load_handler_id']."</a>";	
					$blinker2="<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['id']."</a>";
					$blinker3="<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".date("m/d/Y",strtotime($row['linedate_pickup_eta']))."</a>";
					
					if($row['days_from_now']  <= 10)
					{
						$status="";	
					}
				}
				$cntr++;
			}	
			
			$res['num'] = $cntr;
			$res['first1']=$alinker1;
			$res['first2']=$alinker2;
			$res['first3']=$alinker3;
			$res['last1']=$blinker1;
			$res['last2']=$blinker2;
			$res['last3']=$blinker3;
			$res['status']=$status;
			return $res;
		}
		
		$counter=0;
		echo "
			<tr style='font-weight:bold'>
				<td valign='top'>Driver First Name</td>
				<td valign='top'>Driver Last Name</td>				
				<td valign='top'>DOB</td>
				<td valign='top'>CDL</td>
				<td valign='top'>CDL State</td>
				<td valign='top'>Hired</td>
				<td valign='top'>Terminated</td>				
				<td valign='top' width='50' align='right'>First Load</td>
				<td valign='top' width='50' align='right'>First Dispatch</td>
				<td valign='top' width='150' align='right'>First Date</td>
				<td valign='top' align='right'>Dispatches</td>
				<td valign='top' width='50' align='right'>Last Load</td>
				<td valign='top' width='50' align='right'>Last Dispatch</td>
				<td valign='top' width='150' align='right'>Last Date</td>
				<td valign='top'></td>
			</tr>
		";
		
		
		$sql = "
			select drivers.*		
			from drivers			
			
			order by drivers.name_driver_last, drivers.name_driver_first				
		";	//where deleted=0 
		//echo $sql;		
		$data = simple_query($sql);		
		while($row = mysqli_fetch_array($data)) 
		{
			$driver_id=$row['id'];
			$mres=mrr_get_first_and_last_load($driver_id,$_POST['date_from'],$_POST['date_to']);
			
			$load_cntr=$mres['num'];
			
			if($load_cntr > 0)
			{
				$load_mask=$load_cntr;	//"<a href='report_sales_by_driver.php?driver_id=".$driver_id."date_from=".$_POST['date_from']."&date_to=".$_POST['date_to']."' target='_blank'>".$load_cntr."</a>";
				$hire_mask=date("m/d/Y", strtotime($row['linedate_started']));				if($hire_mask=="12/31/1969")	$hire_mask="N/A";
				$fire_mask=date("m/d/Y", strtotime($row['linedate_terminated']));			if($fire_mask=="12/31/1969")	$fire_mask="N/A";
				$dob_mask=date("m/d/Y", strtotime($row['linedate_birthday']));				if($dob_mask=="12/31/1969")	$dob_mask="N/A";
				
				echo "
						<tr class='".($counter%2==0 ? "even" : "odd")."'>
							<td valign='top' nowrap><a href='admin_drivers.php?id=".$driver_id."' target='view_driver_".$driver_id."'>$row[name_driver_first]</a></td>
							<td valign='top' nowrap><a href='admin_drivers.php?id=".$driver_id."' target='view_driver_".$driver_id."'>$row[name_driver_last]</a></td>
							<td valign='top' align='right' nowrap> ".$dob_mask."</td>
							<td valign='top' align='right' nowrap> ".$row['dl_number']."</td>
							<td valign='top' align='right' nowrap> ".$row['dl_state']."</td>
							<td valign='top' align='right' nowrap> ".$hire_mask."</td>
							<td valign='top' align='right' nowrap> ".$fire_mask."</td>							
							<td valign='top' align='right' nowrap> ".$mres['first1']."</td>
							<td valign='top' align='right' nowrap> ".$mres['first2']."</td>
							<td valign='top' align='right' nowrap> ".$mres['first3']."</td>
							<td valign='top' align='right' nowrap> ".$load_mask."</td>
							<td valign='top' align='right' nowrap> ".$mres['last1']."</td>
							<td valign='top' align='right' nowrap> ".$mres['last2']."</td>
							<td valign='top' align='right' nowrap> ".$mres['last3']."</td>
							<td valign='top' align='right' nowrap> ".$mres['status']."</td>
						</tr>
				";
				$counter++;
			}
		}
		echo "
					<tr >						
						<td colspan='5'>".$counter." Drivers</td>
						<td align='right'>&nbsp;</td>
						<td align='right'>&nbsp;</td>	
						<td align='right'>&nbsp;</td>	
						<td align='right'>&nbsp;</td>					
						<td align='right'>&nbsp;</td>
						<td align='right'>&nbsp;</td>		
						<td align='right'>&nbsp;</td>				
						<td align='right'>&nbsp;</td>				
						<td align='right'>&nbsp;</td>
						<td align='right'>&nbsp;</td>
					</tr>
				";
	?>
	</table>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
</script>
<? include('footer.php') ?>