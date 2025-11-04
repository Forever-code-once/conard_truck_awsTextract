<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) 
	{
		$_POST['driver_id'] = $_GET['driver_id'];	
	}
	
	$_POST['build_report'] = 1;

	$rfilter = new report_filter();
	//$rfilter->show_customer 		= true;
	//$rfilter->show_driver 		= true;
	//$rfilter->show_truck 		= true;
	//$rfilter->show_trailer 		= true;
	$rfilter->show_stops	 	= true;
	$rfilter->show_date_range	= false;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
	$days=15;	
	
 	if(isset($_POST['build_report'])) 
 	{	
		$sql = "
			select trucks_log.*,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trucks.name_truck,
				customers.name_company
			
			from trucks_log
				left join drivers on drivers.id = trucks_log.driver_id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				left join load_handler on load_handler.id = trucks_log.load_handler_id
			where trucks_log.deleted = 0
				and trucks_log.dispatch_completed = 0
				and load_handler.deleted=0
				and trucks_log.linedate_pickup_eta <= '".date("Y-m-d",strtotime("-".$days." days",time()))." 23:59:59' 
  				and trucks_log.linedate_dropoff_eta <= '".date("Y-m-d",strtotime("-".$days." days",time()))." 23:59:59' 
			
			order by trucks_log.linedate_pickup_eta
		";
		/*
				".($_POST['driver_id'] ? " and trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
		*/
		$data = simple_query($sql);
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:950px;text-align:left'>
	<tr>
		<td colspan='11'>
			
			<span class='alert section_heading'><?=$days ?> Days Old Dispatches Opened: (<?= mrr_show_old_dispatches_opened_count($days) ?> Found)</span>
			
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
		<th><span title='Dispatch has been marked completed.'>Done</span></th>
		<td>Truck</td>
		<td>Trailer</td>
		<td>Customer</td>
		<td align='right'>Profit</td>
		<td align='right'>Complete</td>
	</tr>
	<?
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_profit = 0;
		while($row = mysqli_fetch_array($data)) 
		{
			$counter++;
			
			$total_miles += $row['miles'];
			$total_deadhead += $row['miles_deadhead'];
			
			//$mrr_use_profit=$row['profit'];
			$mrr_use_profit=mrr_figure_profit_for_load_dispatch($row['load_handler_id'],$row['id']);		//see functions.php file for this profit.
			
			$total_profit += $mrr_use_profit;
			
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."'>
					<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
					<td><a href='add_entry_truck.php?id=$row[id]' target='view_dispatch_$row[id]'>$row[id]</a></td>
					<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
					<td nowrap>$row[origin]</td>
					<td nowrap>$row[destination]</td>
					<td align='right'>".number_format($row['miles'])."</td>
					<td align='right'>".number_format($row['miles_deadhead'])."</td>
					<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
					<td nowrap>".($row['dispatch_completed'] > 0 ? "Yes" : "<span style='color:#cc0000;'><b>No</b></span>")."</td>
					<td nowrap>$row[name_truck]</td>
					<td nowrap>$row[trailer_name]</td>
					<td nowrap>$row[name_company]</td>
					<td align='right'>$".money_format('',$mrr_use_profit)."</td>
					<td align='right'>
						<input type='checkbox' name='disp_$row[id]' id='disp_$row[id]' value='1' onClick='mrr_auto_complete_dispatch_from_report(".$row['id'].");'>
					</td>
				</tr>
			";
			
			// if the user selected to show the detailed stops for this dispatch, load them and show them
			if(isset($_POST['show_stops'])) {
				$sql = "
					select *
					
					from load_handler_stops
					where deleted = 0
						and trucks_log_id = '$row[id]'
					order by linedate_pickup_eta
				";
				$data_stops = simple_query($sql);
				
				while($row_stop = mysqli_fetch_array($data_stops)) {
					echo "
						<tr style='background-color:#e2ffe4'>
							<td></td>
							<td nowrap align='right'>Stop:</td>
							<td colspan='2' nowrap>Shipper: $row_stop[shipper_name]</td>
							<td colspan='4' nowrap>$row_stop[shipper_address1] $row_stop[shipper_city], $row_stop[shipper_state] $row_stop[shipper_zip]</td>
							<td colspan='2'>Pickup: ".date("M j, Y H:i", strtotime($row_stop['linedate_pickup_eta']))."</td>
							<td colspan='2'>
								".($row_stop['linedate_completed'] > 0 ? "Completed: ".date("M j, Y H:i", strtotime($row_stop['linedate_completed'])) : "")."
							</td>
						</tr>
					";
				}
			}
		}
	?>
	<tr>
		<td colspan='15'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='4'><?=number_format($counter)?> dispatch(es)</td>
		
		<td align='right'><?=number_format($total_miles)?></td>
		<td align='right'><?=number_format($total_deadhead)?></td>
		<td colspan='4'>&nbsp;</td>
		<td align='right'>$<?=money_format('',$total_profit)?></td>
	</tr>
	</table>
<? } ?>

<script type='text/javascript'>
	//$('#date_from').datepicker();
	//$('#date_to').datepicker();
	
	function mrr_auto_complete_dispatch_from_report(id)
	{
		var myval=0;
		var lab="Opened";
		if($('#disp_'+id+'').is(':checked'))
		{
			myval=1;
			lab="Completed";
		}
		//alert("Dispatch "+id+" value is "+myval+".  Label="+lab+".");
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_auto_complete_dispatch_from_report",
			   data: {"id": id,
			   		"done":myval
			   		},
			   dataType: "xml",
			   cache:false,			   
			   success: function(xml) {
					$.noticeAdd({text: "Success - Dispatch "+id+" has been flagged as "+lab+"."});	
			   }
		});
	}
</script>
<? include('footer.php') ?>