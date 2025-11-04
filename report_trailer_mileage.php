<?
ini_set("max_input_vars","40000");	//must change in INI file... 
ini_set('max_execution_time', 600);
$days_to_look=3;
?>
<? include('application.php') ?>
<? $usetitle="Trailer Mileage Report";	?>
<? include('header.php') ?>
<table class='font_display_section' style='text-align:left;width:1600px'>
<tr>
	<td>
		<div class='section_heading'>Trailer Mileage Report</div>
		<span class='alert'>(showing alerts for trailers not moved in the past <?= $days_to_look ?> days)</span><br><br>
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
     	$days=0;
     	
     	if(trim($_POST['date_from'])!="" && trim($_POST['date_to'])!="") 	
     	{
     		$mrr_adder2=" and linedate >='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00' and linedate <='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'";
     		
     		$dt1=strtotime("".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00") / 86400;
     		$dt2=strtotime("".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59") / 86400;
     		$days=round(($dt2 - $dt1),0);
     		
     		if(trim($_POST['date_from'])==trim($_POST['date_to']))		$days=1;
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
     	
     	
     	$tot_pc_miler=0;
     	$tot_miles=0;     	$tot_miles_hourly=0;
     	$tot_dh_miles=0;    $tot_dh_miles_hourly=0;		//DeadHead Miles
     	
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
     		<h2>".$days." Days Included in Range... from ".date("Y-m-d",strtotime($_POST['date_from']))." to ".date("Y-m-d",strtotime($_POST['date_to']))." (inclusively).</h2>
     		<table class='tablesorter font_display_section' style='margin:10px 10px;width:1400px;text-align:left'>
     		<thead>
     		<tr>
     			<th>Trailer</th>
     			<th>Owner</th>
     			<th>OTR Use</th>
     			<th>Curent Use</th>     			
     			<th>PC* Miles</th>
     			<th>Average</th>
     			<th>Alert</th>
     			<!---
     			<th>Miles</th>
     			<th>Hourly Miles</th>
     			<th>DeadHead</th>
     			<th>Hourly DH</th>
     			<th>Total</th>
     			----->
     		</tr>
     		</thead>
     		<tbody>
     	";
     	$counter = 0;
     	while($row = mysqli_fetch_array($data)) 
     	{
     		$counter++;
     		// check to see if this trailer is currently dropped
     		
     		$location = '';
     		$linedate = 0;
     		$details = "";
     		
     		$details .= "<table cellpadding='0' cellspacing='0' width='100%' border='0'>";
     		$details .= "
     					<tr>
     						<td valign='top'><b>Load</b></td>
     						<td valign='top'><b>Dispatch</b></td>     						
     						<td valign='top'><b>Customer</b></td>
     						<td valign='top' align='right'><b>Run (OTR+Hrly)</b></td>
     						<td valign='top' align='right'><b>PC*Miler</b></td>
     						<!----
     						<td valign='top' align='right'><b>Miles</b></td>
     						<td valign='top' align='right'><b>Hourly</b></td>
     						<td valign='top' align='right'><b>DeadHead</b></td>
     						<td valign='top' align='right'><b>DH Hrly</b></td>
     						----->
     						<td valign='top'><b>PickUp</b></td>
     						<td valign='top'><b>DropOff</b></td>     						
     					</tr>     					
     			";
     		
     		$days_used=0;
     		$days_cur=0;
     		
     		$item_cntr=0;
     		
     		$pc_miles=0;	
     		$miles=0;
     		$miles_hourly=0;
     		$dh_miles=0;			//DeadHead Miles
     		$dh_miles_hourly=0;
     					
			$sql = "
				select trucks_log.*,
					customers.name_company,
					(
						select count(*) 
						from trucks_log t1
						where t1.deleted=0
							and t1.linedate_pickup_eta>='".date("Y-m-d",strtotime("-".$days_to_look." days",time()))." 00:00:00'
							and t1.linedate_pickup_eta<='".date("Y-m-d",time())." 23:59:59'
							and t1.trailer_id='".$row['id']."' 
					
					) as cur_move_days
				
				from trucks_log
					left join load_handler on load_handler.id = trucks_log.load_handler_id
					left join customers on customers.id = load_handler.customer_id
				where trailer_id = '".$row['id']."'
					and trucks_log.deleted = 0
					".$mrr_adder2."
				order by trucks_log.linedate asc
			";
			$data_dispatch = simple_query($sql);
			
			while($row_dispatch = mysqli_fetch_array($data_dispatch)) 
			{
				$pc_miles+=$row_dispatch['pcm_miles'];	
				$miles+=$row_dispatch['miles'];
     			$miles_hourly+=$row_dispatch['loaded_miles_hourly'];
     			$dh_miles+=$row_dispatch['miles_deadhead'];				//DeadHead Miles
     			$dh_miles_hourly+=$row_dispatch['miles_deadhead_hourly'];
				
				if(!isset($row_dispatch['cur_move_days']))		$row_dispatch['cur_move_days']=0;
				
				$days_cur=(int) $row_dispatch['cur_move_days'];
				
				$days_used+=$row_dispatch['daily_run_otr'];
				$days_used+=$row_dispatch['daily_run_hourly'];
								
				/*
				// get the location from the stops for this dispatch
				$sql = "
					select load_handler_stops.*
					
					from load_handler_stops
					where trucks_log_id = '$row_dispatch[id]'
					order by linedate_completed desc, linedate_pickup_eta desc
					limit 1
				";
				$data_location = simple_query($sql);
				
				if(mysql_num_fields($data_location)) 
				{
					$row_location = mysqli_fetch_array($data_location);
					$location = $row_location['shipper_name'];
					$addr1=$row_location['shipper_address1'];
					$addr2=$row_location['shipper_address2'];
					$city = $row_location['shipper_city'];
					$state = $row_location['shipper_state'];
					$zip = $row_location['shipper_zip'];
				} 
				else 
				{
					$location = "Unable to determine last location";
				}
				*/
				
				//$linedate = time();		//strtotime($row_dispatch['linedate']);
				//$linedate_completed = 0;
				
				$details .= "
						<tr>
     						<td valign='top'><a href='manage_load.php?load_id=$row_dispatch[load_handler_id]' target='view_load_$row_dispatch[load_handler_id]'>$row_dispatch[load_handler_id]</a></td>
     						<td valign='top'><a href='add_entry_truck.php?id=$row_dispatch[id]' target='view_dispatch_$row_dispatch[id]'>$row_dispatch[id]</a></td>     						  						
     						<td valign='top'>$row_dispatch[name_company]</td>   					
     						<td valign='top' align='right'>".number_format(($row_dispatch['daily_run_otr'] + $row_dispatch['daily_run_hourly']),2)."</td>	
     						<td valign='top' align='right'><b>".number_format($row_dispatch['pcm_miles'],2)."</b></td>
     						
     						<!----
     						<td valign='top' align='right'>".number_format($row_dispatch['miles'],2)."</td>
     						<td valign='top' align='right'>".number_format($row_dispatch['loaded_miles_hourly'],2)."</td>
     						<td valign='top' align='right'>".number_format($row_dispatch['miles_deadhead'],2)."</td>
     						<td valign='top' align='right'>".number_format($row_dispatch['miles_deadhead_hourly'],2)."</td>   
     						----->   						
     						
     						<td valign='top'>".date("m/d/Y", strtotime($row_dispatch['linedate_pickup_eta']))."</td>
     						<td valign='top'>".date("m/d/Y", strtotime($row_dispatch['linedate_dropoff_eta']))."</td>   						
     					</tr>
				";	
				$item_cntr++;			
			}
			
			/*
			if(time() - $linedate > 3 * 86400 && $linedate > 0) 
     		{
     			$show_alert = true;
     		} 
     		else 
     		{
     			$show_alert = false;
     		}
     		*/
     		     		
     		$show_alert = false;
     		//if( $days_used < ($days - $days_to_look))		$show_alert = true;
     		if( $days_cur <= 0)		$show_alert = true;
     		
     		    		
     		$total_mi=($miles + $miles_hourly + $dh_miles + $dh_miles_hourly);
     		//$avg_mile=$total_mi;
     		//if($days > 0)		$avg_mile=$total_mi / $days;
     		
     		$avg_mile=$pc_miles;
     		if($days > 0)		$avg_mile=$pc_miles / $days;
     		     		
     		$otr_useage="".$days_used." of ".$days."";
     		$cur_useage="".$days_cur." dispatch(es) within last ".$days_to_look." days.";
     		     		     		
     		$details .= "
						<tr>
     						<td valign='top'><b>".$item_cntr."</b></td>
     						<td valign='top'><b>SUBTOTAL</b></td>     						
     						<td valign='top'><b>(".$cur_useage.")</b></td>  						
     						<td valign='top' align='right'><b>".$otr_useage."</b></td>
     						<td valign='top' align='right'><b>".number_format($pc_miles,2)."</b></td>
     						
     						<!-----
     						<td valign='top' align='right'><b>".number_format($miles,2)."</b></td>
     						<td valign='top' align='right'><b>".number_format($miles_hourly,2)."</b></td>
     						<td valign='top' align='right'><b>".number_format($dh_miles,2)."</b></td>
     						<td valign='top' align='right'><b>".number_format($dh_miles_hourly,2)."</b></td>   
     						<td valign='top' align='right'><b>Mi + DH + Hrly + DH Hrly = ".number_format($total_mi,2)." miles</b></td>  
     						---->
     						
     						<td valign='top' colspan='2' align='right'><b>Average (".$days." days): ".number_format($avg_mile,4)."</b></td>       						 						
     					</tr>
			";	
     		$details .= "</table>";
     		  
     		     		
     		echo "
     			<tr class='".($counter % 2 == 1 ? "odd" : "even")."'>
     				<td><a href='admin_trailers.php?id=$row[id]' target='_blank'><b>$row[trailer_name]</b></a></td>
     				<td>$row[trailer_owner]</td>
     				<td align='right'>".$otr_useage."</td>
     				<td align='right'>".$cur_useage."</td>
     				<td align='right'>".number_format($pc_miles,2)."</td>
     				
     				<!-----
     				<td align='right'>".number_format($miles,2)."</td>
     				<td align='right'>".number_format($miles_hourly,2)."</td>
     				<td align='right'>".number_format($dh_miles,2)."</td>
     				<td align='right'>".number_format($dh_miles_hourly,2)."</td>
     				<td align='right'>".number_format($total_mi,2)."</td>
     				------>
     				
     				<td align='right'>".number_format($avg_mile,4)."</td>
     				<td align='right'>".($show_alert ? "<span class='alert'>(alert)</span>" : "")." <span style='color:green; cursor:pointer;' onClick='mrr_toggle_details(".$row['id'].");'>Details</span></td>
     			</tr>
     			<tr class='".($counter % 2 == 1 ? "odd" : "even")." details_".$row['id']." all_details'>
     				<td>&nbsp;</td>
     				<td colspan='6'>".$details."</td>
     			</tr>     			
     		";
     		
     		$tot_pc_miler+=$pc_miles;
     		$tot_miles+=$miles;     	
     		$tot_miles_hourly+=$miles_hourly;
     		$tot_dh_miles+=$dh_miles;    				//DeadHead Miles
     		$tot_dh_miles_hourly+=$dh_miles_hourly;		
		}
		
		//$full_tot=($tot_miles + $tot_miles_hourly + $tot_dh_miles + $tot_dh_miles_hourly);
		//$full_avg=$full_tot;
		$full_avg=$tot_pc_miler;
				
		$avg_pcm=$tot_pc_miler;
		$avg_mi=$tot_miles;
		$avg_dh=$tot_dh_miles;
		$avg_hr=$tot_miles_hourly;
		$avg_dhhr=$tot_dh_miles_hourly;
		
		if($days > 0)	
		{
			//$full_avg=$full_tot / $days;
			
			$full_avg=$tot_pc_miler / $days;
			
			$avg_pcm=$tot_pc_miler / $days;
			$avg_mi=$tot_miles / $days;
			$avg_dh=$tot_dh_miles / $days;
			$avg_hr=$tot_miles_hourly / $days;
			$avg_dhhr=$tot_dh_miles_hourly / $days;			
		}
		
		echo "
			<tr>	
				<td colspan='7'><hr></td>
			</tr> 			
			<tr>
				<td colspan='2'>TOTAL MILES</td>
				<td align='right'>".number_format($tot_pc_miler,2)."</td>
				<td align='right'>&nbsp;</td>
     			<td colspan='2'>AVERAGE MILES over ".$days." Days</td>
     			<td align='right'>".number_format($full_avg,4)."</td>
			</tr> 
			<!------
			<tr>
				<td colspan='2'>TOTAL MILES</td>
				<td align='right'>".number_format($tot_pc_miler,2)."</td>
     			<td align='right'>".number_format($tot_miles,2)."</td>
     			<td align='right'>".number_format($tot_miles_hourly,2)."</td>
     			<td align='right'>".number_format($tot_dh_miles,2)."</td>
     			<td align='right'>".number_format($tot_dh_miles_hourly,2)."</td>
     			<td align='right'>".number_format($full_tot,2)."</td>
     			<td align='right'>&nbsp;</td>
				<td align='right'>&nbsp;</td>
			</tr>   
			<tr>
				
				<td align='right'>".number_format($avg_pcm,4)."</td>
     			<td align='right'>".number_format($avg_mi,4)."</td>
     			<td align='right'>".number_format($avg_hr,4)."</td>
     			<td align='right'>".number_format($avg_dh,4)."</td>
     			<td align='right'>".number_format($avg_dhhr,4)."</td>
     			<td align='right'>&nbsp;</td>
     			<td align='right'>".number_format($full_avg,4)."</td>
				<td align='right'>&nbsp;</td>
			</tr>
			------>
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
	
	function mrr_toggle_details(trailer_id)
	{
		$('.details_'+trailer_id+'').toggle();
	}
	
	$('.all_details').hide();
</script>
<? include('footer.php') ?>