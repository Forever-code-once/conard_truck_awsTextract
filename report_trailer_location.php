<? include('application.php') ?>
<? $usetitle="Current Trailer Location Report";	?>
<? include('header.php') ?>
<table class='font_display_section' style='text-align:left;width:1600px'>
<tr>
	<td>
		<div class='section_heading'>Current Trailer Location Report</div>
		<div style='color:#00CC00; background-color:#eeeeee; border:1px solid #00cc00;width:850px'>
			This report shows ALL the Trailers that meet the filter (regardless of the location), but then shows the last known location if possible.
			<br>If Details column shows "Dropped:", the drop date is in the filter.
			<br>If details are otherwise present, trailer is on a dispatch or cannot be located at this time.
		</div>
		<span class='alert'>(showing alerts for trailers not moved in the past 7 days)</span><br><br>
	<?
		$rfilter = new report_filter();
		$rfilter->show_date_range 		= true;
		$rfilter->show_trailer 			= true;
		$rfilter->show_trailer_owner 		= true;
		$rfilter->show_trailer_interchange	= true;
		$rfilter->show_active			= true;		
		//$rfilter->show_single_date 		= true;
		$rfilter->show_font_size			= true;	
		$rfilter->show_filter();
      ?>
      </td>
</tr>    		
<tr>
	<td>         		
     <?   
     if(isset($_POST['build_report'])) 
     {
     	$mrr_adder="";
     	$mrr_adder2=" and trucks_log.linedate < '".date("Y-m-d", strtotime("+1 day", time()))."'";
     	
     	if(trim($_POST['date_from'])!="" && trim($_POST['date_to'])!="") 	
     	{
     		$mrr_adder2=" and linedate >='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00' and linedate <='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'";
     	}
     	/*
     	else
     	{
     		if(trim($_POST['date_from'])!="")			$mrr_adder2.=" and linedate >='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'";
     		if(trim($_POST['date_to'])!="")			$mrr_adder2.=" and linedate <='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'";
     	}	
     	*/
     	
     	if($_POST['report_trailer_interchange'] ==1)  	$mrr_adder.=" and interchange_flag = 1";
     	elseif($_POST['report_trailer_interchange'] ==2) 	$mrr_adder.=" and interchange_flag = 0";
     	     	
     	if($_POST['report_active'] > 0)  				$mrr_adder.=" and active = 1";
     	if($_POST['trailer_id'] > 0)  				$mrr_adder.=" and id='".sql_friendly($_POST['trailer_id'])."'";
     	if(trim($_POST['report_trailer_owner'])!="")  	$mrr_adder.=" and trailer_owner='".sql_friendly(trim($_POST['report_trailer_owner']))."'";
     	
     	
     	$sql = "
     		select *
     		
     		from trailers
     		where deleted = 0
     			".$mrr_adder."     			
     		order by trailers.trailer_name
     	";
     	$data = simple_query($sql);
     	
     	echo "
     		<div style='clear:both'></div>
     		<table class='tablesorter font_display_section' style='margin:10px 10px;width:1400px;text-align:left'>
     		<thead>
     		<tr>
     			<th>Trailer</th>
     			<th>Owner</th>
     			<th>Location</th>
     			<th>Address</th>
     			<th>Address2</th>
     			<th>City</th>
     			<th>State</th>
     			<th>Zip</th>
     			<th>Date</th>
     			<th>Completed</th>
     			<th>Details</th>
     			<th>Alert</th>
     		</tr>
     		</thead>
     		<tbody>
     	";
     	$counter = 0;
     	while($row = mysqli_fetch_array($data)) {
     		$counter++;
     		// check to see if this trailer is currently dropped
     		
     		$linedate = 0;
     		$details = '';
        
             $location = "";
             $addr1="";
             $addr2="";
             $city = "";
             $state = "";
             $zip = "";
     		
     		$sql = "
     			select trailers_dropped.*,
     				customers.name_company
     				
     			from trailers_dropped
     				left join customers on customers.id = trailers_dropped.customer_id
     			where trailer_id = '$row[id]'
     				and trailers_dropped.drop_completed = 0
     				and trailers_dropped.deleted = 0
     				".$mrr_adder2."
     		";	//
     		$data_dropped = simple_query($sql);
     		
     		if(mysqli_num_rows($data_dropped)) {
     			$row_dropped = mysqli_fetch_array($data_dropped);
     			$location = "$row_dropped[name_company]";
     			
     			$addr1='';
     			$addr2='';
     			$city = $row_dropped['location_city'];
     			$state = $row_dropped['location_state'];
     			$zip = $row_dropped['location_zip'];
     			     			
     			$linedate = strtotime($row_dropped['linedate']);
     			$linedate_completed = strtotime($row_dropped['linedate_completed']);
     			
     			$details = "<a href='trailer_drop.php?id=$row_dropped[id]' target='view_drop_$row_dropped[id]'>Dropped:</a> $row_dropped[name_company]";
     		} else {
     			// okay, trailer isn't dropped, so find out which dispatch it's on
     			$sql = "
     				select trucks_log.*,
     					customers.name_company
     				
     				from trucks_log
     					left join load_handler on load_handler.id = trucks_log.load_handler_id
     					left join customers on customers.id = load_handler.customer_id
     				where trailer_id = '$row[id]'
     					and trucks_log.deleted = 0
     					".$mrr_adder2."
     				order by trucks_log.linedate desc
     				limit 1
     			";
     			$data_dispatch = simple_query($sql);
     			
     			if(mysqli_num_rows($data_dispatch)) {
     				$row_dispatch = mysqli_fetch_array($data_dispatch);
     				
     				// get the location from the stops for this dispatch
     				$sql = "
     					select load_handler_stops.*
     					
     					from load_handler_stops
     					where trucks_log_id = '$row_dispatch[id]'
     					order by linedate_completed desc, linedate_pickup_eta desc
     					limit 1
     				";
     				$data_location = simple_query($sql);
     				
     				if(mysqli_num_fields($data_location)) {
     					$row_location = mysqli_fetch_array($data_location);
     					$location = $row_location['shipper_name'];
     					$addr1=$row_location['shipper_address1'];
     					$addr2=$row_location['shipper_address2'];
     					$city = $row_location['shipper_city'];
     					$state = $row_location['shipper_state'];
     					$zip = $row_location['shipper_zip'];
     				} else {
     					$location = "Unable to determine last location";
     				}
     				
     				
     				$linedate = strtotime($row_dispatch['linedate']);
     				$linedate_completed = 0;
     				
     				$details = "
     					Dispatch: <a href='add_entry_truck.php?id=$row_dispatch[id]' target='view_dispatch_$row_dispatch[id]'>$row_dispatch[id]</a>
     					Load: <a href='manage_load.php?load_id=$row_dispatch[load_handler_id]' target='view_load_$row_dispatch[load_handler_id]'>$row_dispatch[load_handler_id]</a>
     					- Customer: $row_dispatch[name_company]
     				";
     			}
     		}
     		
     		if(time() - $linedate > 7 * 86400 && $linedate > 0) {
     			$show_alert = true;
     		} else {
     			$show_alert = false;
     		}
     		
     		echo "
     			<tr class='".($counter % 2 == 1 ? "odd" : "even")."'>
     				<td><a href='admin_trailers.php?id=$row[id]' target='_blank'><b>$row[trailer_name]</b></a></td>
     				<td>$row[trailer_owner]</td>
     				<td>$location</td>
     				<td>$addr1</td>
     				<td>$addr2</td>
     				<td>$city</td>
     				<td>$state</td>
     				<td>$zip</td>
     				<td>".($linedate > 0 ? date("m-d-Y", $linedate) : "")."</td>
     				<td>".($linedate_completed > 0 ? date("m-d-Y H:i", $linedate_completed) : "")."</td>
     				<td>$details</td>
     				<td>".($show_alert ? "<span class='alert'>(alert)</span>" : "")."</td>
     			</tr>
     		";
     	}
     	echo "
     		</tbody>
     		</table>
     	";
     }
     ?>
	</td>
</tr>
</table>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
</script>
<? include('footer.php') ?>