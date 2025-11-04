<?
$usetitle = "Report - Trucks With No Preplan";
$use_title = "Report - Trucks With No Preplan";
?>
<? include('header.php') ?>
<?
    $remove_mes="";
    

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
	
	if(!isset($_POST['date_from']))		$_POST['date_from']=date("m/d/Y",time());
	if(!isset($_POST['date_to']))			$_POST['date_to']=date("m/d/Y",time());

	$rfilter = new report_filter();
	$rfilter->show_customer 			= true;
	$rfilter->show_driver 			= true;
	$rfilter->show_truck 			= true;
	//$rfilter->show_trailer 			= true;
	$rfilter->show_load_id 			= true;
	//$rfilter->show_dispatch_id 		= true;
	//$rfilter->show_shipper_name		= true;
	//$rfilter->show_origin	 		= true;
	$rfilter->show_destination 		= true;
	//$rfilter->show_stops	 		= true;
	$rfilter->show_font_size			= true;
	//$rfilter->search_sort_by_report	= true;
	$rfilter->show_filter();
		
 	if(isset($_POST['build_report'])) 
 	{ 				
		$search_date_range = '';
		if($_POST['load_handler_id'] != '') 
		{
			//$_POST['dispatch_id'] != '' || 
		} 
		else 
		{
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and load_handler.linedate_dropoff_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
				and load_handler.linedate_dropoff_eta<= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
			";
		}
				
		$mrr_shipper_find="";
		/*
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
		*/
		
		/*
		left join trailers on trailers.id = trucks_log.trailer_id
		
		".($_POST['dispatch_id'] ? " and trucks_log.id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
		".($_POST['report_origin'] ? " and load_handler.origin_city like '%".sql_friendly($_POST['report_origin'])."%'" : '') ."
		".($_POST['report_origin_state'] ? " and load_handler.origin_state like '%".sql_friendly($_POST['report_origin_state'])."%'" : '') ."
		".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
		*/
				
		$sql = "
			select load_handler.*,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trucks.name_truck,
				trucks_log.id as dispatch_id,
				trucks_log.truck_id,
				trucks_log.driver_id,
				customers.name_company,					
				load_handler.id as load_handler_id
			
			from load_handler
				left join trucks_log on load_handler.id = trucks_log.load_handler_id and trucks_log.deleted = 0
				left join drivers on drivers.id = trucks_log.driver_id				
				left join trucks on trucks.id = trucks_log.truck_id
				left join customers on customers.id = trucks_log.customer_id
				
			where load_handler.deleted = 0
				".$search_date_range."				
				".$mrr_shipper_find."
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."')" : 'and load_handler.preplan_driver_id !=405') ."
				".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				
				".($_POST['customer_id'] ? " and trucks_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."				
				".($_POST['report_destination'] ? " and load_handler.dest_city = '".sql_friendly($_POST['report_destination'])."'" : '') ."
				".($_POST['report_destination_state'] ? " and load_handler.dest_state = '".sql_friendly($_POST['report_destination_state'])."'" : '') ."
			
			order by load_handler.linedate_dropoff_eta asc,load_handler.linedate_pickup_eta asc
		";
		$data = simple_query($sql);
		
		$sql2 = "
			select load_handler.*,
				drivers.name_driver_first,
				drivers.name_driver_last,
				drivers.attached_truck_id as truck_id,
				trucks.name_truck,				
				load_handler.preplan_driver_id as driver_id,
				customers.name_company,					
				load_handler.id as load_handler_id
			
			from load_handler
				left join drivers on drivers.id = load_handler.preplan_driver_id
				left join trucks on trucks.id = drivers.attached_truck_id
				left join customers on customers.id = load_handler.customer_id				
				
			where load_handler.deleted = 0
				and load_handler.preplan > 0
				and load_handler.preplan_driver_id > 0
				".$search_date_range."				
				".$mrr_shipper_find."
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['driver_id'] ? " and (load_handler.preplan_driver_id = '".sql_friendly($_POST['driver_id'])."' or load_handler.preplan_driver2_id = '".sql_friendly($_POST['driver_id'])."')" : 'and load_handler.preplan_driver_id !=405') ."
				".($_POST['truck_id'] ? " and drivers.attached_truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				
				".($_POST['customer_id'] ? " and load_handler.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."				
				".($_POST['report_destination'] ? " and load_handler.dest_city = '".sql_friendly($_POST['report_destination'])."'" : '') ."
				".($_POST['report_destination_state'] ? " and load_handler.dest_state = '".sql_friendly($_POST['report_destination_state'])."'" : '') ."
			
			order by load_handler.linedate_dropoff_eta asc,load_handler.linedate_pickup_eta asc
		";
		$data2 = simple_query($sql2);
		/*
		 <tr>
            <td colspan='10' align='center'>Query 1: <?=$sql ?></td>
        </tr>
        <tr>
            <td colspan='10' align='center'>Query 2: <?=$sql2 ?></td>
        </tr>
		 */
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1200px;text-align:left;'>
 
	<tr>
		<td colspan='10' align='center'><span class='section_heading'><?=$use_title ?></span></td>
	</tr>
	<tr>
        <td colspan='10' align='left'><b>Loads Dispatched with No Preplanned Load to follow:</b></td>
	</tr>
	<tr style='font-weight:bold'>
		<td nowrap>Load ID</td>
		<td nowrap>Dispatch ID</td>
		<td>Customer</td>
		<td>Origin</td>
		<td align='right'>Pickup</td>
		<td>Destination</td>
		<td align='right'>Dropoff</td>
		<td>Truck</td>
		<td>Driver</td>		
		<td align='right'>Planned&nbsp;</td>
	</tr>
	<?
		$load_counter=0;
		$load_arr[0]=0;	
		$truck_counter=0;
		$truck_arr[0]=0;	
		
		$counter = 0;
		
		$remove_mes="";
							
		while($row = mysqli_fetch_array($data)) 
		{
			$valid=1;
			$loads=0;
			
			$curdate=$row['linedate_dropoff_eta'];
			$curdate=$row['linedate_pickup_eta'];
			if($curdate!="0000-00-00 00:00:00")
			{
				$loads=mrr_find_truck_has_more_loads($row['load_handler_id'],$row['truck_id'],$row['driver_id'],$curdate);
				if($loads > 0)		$valid=0;
			}			
			
			if($row['driver_id'] ==405)     	$valid=0;   //skip the "Load Reminder APPT" driver.
			
			if($valid > 0)
			{
     			$load_found=0;
     			for($z=0;$z < $load_counter; $z++)
     			{
     				if(	isset($load_arr[ $z ]) && $load_arr[ $z ] == $row['load_handler_id'])
                    {
                        $load_found=1;                 //already in the loop, so this is a duplicate driver.
                        $remove_mes.="
                            $('.driver_".$row['driver_id']."').hide();
                        ";
                    }
     			}
     			if($load_found==0)
     			{
     				$load_arr[ $load_counter ]=$row['load_handler_id'];	
     				$load_counter++;	
     			}
     			
     			$truck_found=0;
     			for($z=0;$z < $truck_counter; $z++)
     			{
     				if(	isset($truck_arr[ $z ]) && $truck_arr[ $z ] == $row['truck_id'])
                    {
                        $truck_found=1;                 //already in the loop, so this is a duplicate truck.
                        $remove_mes.="
                            $('.truck_".$row['truck_id']."').hide();
                        ";
                    }
     			}
     			if($truck_found==0)
     			{
     				$truck_arr[ $load_counter ]=$row['truck_id'];	
     				$truck_counter++;	
     			}
     			
     			$disp="No Disp";
     			if($row['dispatch_id'] > 0)	  $disp="<a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['dispatch_id']."' target='view_dispatch_".$row['dispatch_id']."'>".$row['dispatch_id']."</a>";
     			   			
     			echo "
     				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')." driver_".$row['driver_id']." truck_".$row['truck_id']." all_rows'>
     					<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=".$row['load_handler_id']."' target='view_load_".$row['load_handler_id']."'>".$row['load_handler_id']."</a>" : "")."</td>
     					<td nowrap>".$disp."</td>
     					<td nowrap><a href='admin_customers.php?eid=".$row['customer_id']."' target='view_cust_".$row['customer_id']."'>".$row['name_company']."</td>
     					<td nowrap>".$row['origin_city'].", ".$row['origin_state']."</td>
     					<td nowrap align='right'>".date("M j, Y H:i", strtotime($row['linedate_pickup_eta']))."</td>
     					<td nowrap>".$row['dest_city'].", ".$row['dest_state']."</td>
     					<td nowrap align='right'>".date("M j, Y H:i", strtotime($row['linedate_dropoff_eta']))."</td>
     					<td nowrap><a href='admin_trucks.php?id=".$row['truck_id']."' target='view_truck_".$row['truck_id']."'>".$row['name_truck']."</td>
     					<td nowrap><a href='admin_drivers.php?id=".$row['driver_id']."' target='view_driver_".$row['driver_id']."'>".$row['name_driver_first']." ".$row['name_driver_last']."</td>
     					<td nowrap align='right'>".$loads."&nbsp;</td>
     				</tr>
     			";	//
     			$counter++;
			}		
		}				
	?>	
	<tr>
		<td colspan='10' align='left'><br><hr><br></td>
	</tr>
	<tr>
		<td colspan='10' align='left'><b>Loads Not Dispatched with No Loads Preplanned to follow:</b></td>
	</tr>
	<tr style='font-weight:bold'>
		<td nowrap>Load ID</td>
		<td nowrap>Dispatch ID</td>
		<td>Customer</td>
		<td>Origin</td>
		<td align='right'>Pickup</td>
		<td>Destination</td>
		<td align='right'>Dropoff</td>
		<td>Truck</td>
		<td>Driver</td>		
		<td align='right'>Planned&nbsp;</td>
	</tr>
	<?
		$counter = 0;	
							
		while($row = mysqli_fetch_array($data2)) 
		{
			$valid=1;
			$loads=0;
			
			$truck_found=0;
     		for($z=0;$z < $truck_counter; $z++)
     		{
     			if(	isset($truck_arr[ $z ]) && $truck_arr[ $z ] == $row['truck_id'])
                {
                    $truck_found=1;
                    $remove_mes.="
                            $('.truck_".$row['truck_id']."').hide();
                        ";
                }
     		}
			if($truck_found==0)
     		{
     			//$truck_arr[ $truck_counter ]=$row['truck_id'];	
     			//$truck_counter++;	
     		}
     		else
     		{
     			$valid=0;
     		}
			
			
			$curdate=$row['linedate_dropoff_eta'];
			if($curdate!="0000-00-00 00:00:00" && $valid==1)
			{
				$loads=mrr_find_truck_has_more_loads($row['load_handler_id'],$row['truck_id'],$row['driver_id'],$curdate);
				if($loads > 0)		$valid=0;
			}			
						
			if($valid > 0)
			{    			     			
     			echo "
     				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')." driver_".$row['driver_id']." truck_".$row['truck_id']." all_rows'>
     					<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=".$row['load_handler_id']."' target='view_load_".$row['load_handler_id']."'>".$row['load_handler_id']."</a>" : "")."</td>
     					<td>Preplan</td>
     					<td nowrap><a href='admin_customers.php?eid=".$row['customer_id']."' target='view_cust_".$row['customer_id']."'>".$row['name_company']."</td>
     					<td nowrap>".$row['origin_city'].", ".$row['origin_state']."</td>
     					<td nowrap align='right'>".date("M j, Y H:i", strtotime($row['linedate_pickup_eta']))."</td>
     					<td nowrap>".$row['dest_city'].", ".$row['dest_state']."</td>
     					<td nowrap align='right'>".date("M j, Y H:i", strtotime($row['linedate_dropoff_eta']))."</td>
     					<td nowrap><a href='admin_trucks.php?id=".$row['truck_id']."' target='view_truck_".$row['truck_id']."'>".$row['name_truck']."</td>
     					<td nowrap><a href='admin_drivers.php?id=".$row['driver_id']."' target='view_driver_".$row['driver_id']."'>".$row['name_driver_first']." ".$row['name_driver_last']."</td>
     					<td nowrap align='right'>".$loads."&nbsp;</td>
     				</tr>
     			";	//
     			$counter++;
			}		
		}				
	?>	
	</table> <br><br><span onClick="$('.all_rows').show();">(-----)</span>
<? } ?>
<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
    <?
        echo $remove_mes;
    ?>
</script>
<? include('footer.php') ?>