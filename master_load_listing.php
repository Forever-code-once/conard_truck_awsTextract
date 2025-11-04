<? include('application.php') ?>
<? $admin_page = 1 ?>
<?	
	$sql = "
		select load_handler.*,
			customers.name_company
			
		from load_handler
			left join customers on customers.id=load_handler.customer_id  
		where load_handler.deleted = 0
			and load_handler.master_load>0
		order by load_handler.linedate_pickup_eta desc, load_handler.id desc
	";
	$data = simple_query($sql);
	$tab="";
	
	$tab_hdr.="
		<tr>
			<th><b>&nbsp;</b></th>
			<th><b>Load</b></th>
			<th><b>PickupETA</b></th>	
			<th><b>Customer</b></th>	
			<th><b>Master Load Label</b></th>	
			<th><b>Origin</b></th>	
			<th><b>State</b></th>	
			<th><b>Dest</b></th>	
			<th><b>State</b></th>	
			<th><b>Dedicated</b></th>	
			<th><b>Load#</b></th>	
			<th><b>Pickup#</b></th>	
			<th><b>Delivery#</b></th>	
			<th><b>Invoice#</b></th>	
			<th><b>Instructions</b></th>
			<th><span class='mrr_link_like_on' onClick='mrr_show_load_info(0);'><b>Show All</b></span> / <span class='mrr_link_like_on' onClick='mrr_hide_load_info(0);'><b>Hide All</b></span></th>
			<th>&nbsp;</th>
		</tr>
	";
	while($row=mysqli_fetch_array($data))
	{
		$tab.="
		<tr class='row_".$row['id']."'>
			<td valign='top' nowrap><span class='mrr_link_like_on' onClick='mrr_run_copy_from_master(".$row['id'].");' title='Create a new load from this Master Load... most of details are copied. You can modify the new load as needed.'>Create Load</span></td>
			<td valign='top'><a href='manage_load.php?load_id=".$row['id']."&master_load_edit=1' target='_blank'>".$row['id']."</a></td>
			<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
			<td valign='top'><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['name_company']."</a></td>
			<td valign='top'>".$row['master_load_label']."</td>
			<td valign='top'>".$row['origin_city']."</td>
			<td valign='top'>".$row['origin_state']."</td>
			<td valign='top'>".$row['dest_city']."</td>
			<td valign='top'>".$row['dest_state']."</td>
			<td valign='top'>".($row['dedicated_load'] > 0 ? "Dedicated" : "")."</td>
			<td valign='top'>".$row['load_number']."</td>
			<td valign='top'>".$row['pickup_number']."</td>
			<td valign='top'>".$row['deliver_number']."</td>
			<td valign='top'>".$row['sicap_invoice_number']."</td>
			<td valign='top'>".$row['special_instructions']."</td>
			<td valign='top' nowrap><span class='mrr_link_like_on' onClick='mrr_show_load_info(".$row['id'].");'>Show</span> / <span class='mrr_link_like_on' onClick='mrr_hide_load_info(".$row['id'].");'>Hide</span></td>
			<td valign='top' nowrap><span class='mrr_link_like_on' onClick='turn_off_master_load(".$row['id'].");'>Deactivate</span></td>
		</tr>
		";
		
		$disp_tab=""; 
		
		$sqls = "
			select trucks_log.*,
				(select name_driver_first from drivers where drivers.id=trucks_log.driver_id) as driver_fname1,
				(select name_driver_last from drivers where drivers.id=trucks_log.driver_id) as driver_lname1,
				(select name_driver_first from drivers where drivers.id=trucks_log.driver2_id) as driver_fname2,
				(select name_driver_last from drivers where drivers.id=trucks_log.driver2_id) as driver_lname2,
				(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_namer,
				(select trailer_name from trailers where trailers.id=trucks_log.trailer_id) as trailer_namer
				
			from trucks_log 
			where trucks_log.deleted = 0
				and trucks_log.load_handler_id='".sql_friendly($row['id'])."'
			order by trucks_log.linedate_pickup_eta desc, trucks_log.id desc
		";
		$datas = simple_query($sqls);
		if(mysqli_num_rows($datas) > 0)
		{
			$disp_tab.="
				<div class='load_".$row['id']." all_master_loads'>
				<table border='0' cellpadding='0' cellspacing='0' width='100%'>
				<tr>
					<td valign='top'><b>Dispatch</b></td>
					<td valign='top'><b>Pickup</b></td>
					<td valign='top'><b>Truck</b></td>
					<td valign='top'><b>Trailer</b></td>
					<td valign='top'><b>Driver</b></td>
					<td valign='top'><b>Driver2</b></td>
					<td valign='top'><b>Miles</b></td>
					<td valign='top'><b>Deadhead</b></td>
					<td valign='top'><b>Total</b></td>
					<td valign='top'><b>Origin</b></td>
					<td valign='top'><b>State</b></td>
					<td valign='top'><b>Dest</b></td>
					<td valign='top'><b>State</b></td>
					<td valign='top'><b>Location</b></td>
					<td valign='top'><b>Notes</b></td>
					<td valign='top' nowrap>
						<span class='mrr_link_like_on' onClick='mrr_show_load_disp_info(".$row['id'].",0);'><b>Show All</b></span> / 
						<span class='mrr_link_like_on' onClick='mrr_hide_load_disp_info(".$row['id'].",0);'><b>Hide All</b></span>
					</td>
				</tr>
			";	
		}
		while($rows=mysqli_fetch_array($datas))
		{
			$disp_tab.="
				<tr>
					<td valign='top'><a href='add_entry_truck.php?load_id=".$row['id']."&id=".$rows['id']."' target='_blank'>".$rows['id']."</a></td>
					<td valign='top'>".date("m/d/Y H:i",strtotime($rows['linedate_pickup_eta']))."</td>
					<td valign='top'><a href='admin_trucks.php?id=".$rows['truck_id']."' target='_blank'>".$rows['truck_namer']."</a></td>
					<td valign='top'><a href='admin_trailers.php?id=".$rows['trailer_id']."' target='_blank'>".$rows['trailer_namer']."</a></td>
					<td valign='top'><a href='admin_drivers.php?id=".$rows['driver_id']."' target='_blank'>".$rows['dirver_fname']." ".$rows['dirver_lname']."</a></td>
					<td valign='top'><a href='admin_drivers.php?id=".$rows['driver2_id']."' target='_blank'>".$rows['dirver_fname2']." ".$rows['dirver_lname2']."</a></td>
					<td valign='top'>".$rows['miles']."</td>
					<td valign='top'>".$rows['miles_deadhead']."</td>
					<td valign='top'>".($rows['miles'] + $rows['miles_deadhead'])."</td>
					<td valign='top'>".$rows['origin']."</td>
					<td valign='top'>".$rows['origin_state']."</td>
					<td valign='top'>".$rows['destination']."</td>
					<td valign='top'>".$rows['destination_state']."</td>
					<td valign='top'>".$rows['location']."</td>
					<td valign='top'>".$rows['notes']."</td>
					<td valign='top' nowrap>
						<span class='mrr_link_like_on' onClick='mrr_show_load_disp_info(".$row['id'].",".$rows['id'].");'>Show</span> / 
						<span class='mrr_link_like_on' onClick='mrr_hide_load_disp_info(".$row['id'].",".$rows['id'].");'>Hide</span>
					</td>
				</tr>
			";
			
			$disp_stop_tab="";
			
			$sql2 = "
     			select load_handler_stops.*,
     				(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as trailer_namer1,
     				(select trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as trailer_namer2
     				
     			from load_handler_stops 
     			where load_handler_stops.deleted = 0
     				and load_handler_stops.trucks_log_id='".sql_friendly($rows['id'])."'
     			order by load_handler_stops.linedate_pickup_eta desc, load_handler_stops.id desc
     		";
     		$data2 = simple_query($sql2);
     		if(mysqli_num_rows($data2) > 0)
     		{
     			$disp_stop_tab.="
     				<div class='all_master_load_disps load_disp_".$rows['id']." master_load_disps_".$row['id']."'>
          				<table border='0' cellpadding='0' cellspacing='0' width='100%'>
          				<tr>
          					<td valign='top'><b>Stop</b></td>
          					<td valign='top'><b>Appointment</b></td>
          					<td valign='top'><b>Shipper</b></td>
          					<td valign='top'><b>Address1</b></td>
          					<td valign='top'><b>Address2</b></td>
          					<td valign='top'><b>City</b></td>
          					<td valign='top'><b>State</b></td>
          					<td valign='top'><b>Zip</b></td>
          					<td valign='top'><b>Phone</b></td>
          					<td valign='top'><b>PCM Miles</b></td>
          					<td valign='top'><b>Start Trailer</b></td>
          					<td valign='top'><b>End Trailer</b></td>
          					<td valign='top'><b>Directions</b></td>
          				</tr>
     			";	
			}
			$scntr=0;
			while($row2=mysqli_fetch_array($data2))
			{
				$show_appt_window="";
				if($row2['appointment_window'] > 0)
				{
					$show_appt_window="<br><span class='mrr_appt_windower'>".date("m/d/Y H:i",strtotime($row2['linedate_appt_window_start']))." to ".date("m/d/Y H:i",strtotime($row2['linedate_appt_window_end']))."</span>";	
				}
				$disp_stop_tab.="
						<tr class='".($scntr%2==0 ? "even" : "odd")."'>
          					<td valign='top'>".$row2['id']."</td>
          					<td valign='top'>".date("m/d/Y H:i",strtotime($row2['linedate_pickup_eta']))."".$show_appt_window."</td>
          					<td valign='top'>".$row2['shipper_name']."</td>
          					<td valign='top'>".$row2['shipper_address1']."</td>
          					<td valign='top'>".$row2['shipper_address2']."</td>
          					<td valign='top'>".$row2['shipper_city']."</td>
          					<td valign='top'>".$row2['shipper_state']."</td>
          					<td valign='top'>".$row2['shipper_zip']."</td>
          					<td valign='top'>".$row2['stop_phone']."</td>
          					<td valign='top'>".$row2['pcm_miles']."</td>
          					<td valign='top'><a href='admin_trailers.php?id=".$row2['start_trailer_id']."' target='_blank'>".$row2['trailer_namer1']."</a></td>
          					<td valign='top'><a href='admin_trailers.php?id=".$row2['end_trailer_id']."' target='_blank'>".$row2['trailer_namer2']."</a></td>
          					<td valign='top'>".$row2['directions']."</td>      
          				</tr>
				";	
				$scntr++;
			}
			if(mysqli_num_rows($data2) > 0)
     		{
     			$disp_stop_tab.="
     					</table>
					</div>
     			";	
			}			
			
			$disp_tab.="
				<tr>
					<td valign='top'>&nbsp;</td>
					<td valign='top' colspan='14'>".$disp_stop_tab."</td>
					<td valign='top'>&nbsp;</td>
				</tr>
			";	
		}
		if(mysqli_num_rows($datas) > 0)
		{
			$disp_tab.="
				</table>
				</div>
			";	
		}
		
		$tab.="
		<tr>
			<td valign='top'></td>
			<td valign='top'></td>
			<td valign='top' colspan='13'>".$disp_tab."</td>
			<td valign='top'></td>
		</tr>
		";
		
	}
?>
<?
$usetitle = "Master Loads";
$use_title = "Master Loads";
?>
<? include('header.php') ?>
<div style='margin:10px;'>
	
<div class='standard18'><b>Master Loads</b></div>
<div style=color:black;margin:10px;'>Create a load from one of these Master Loads.  Use the Show and Hide links to view more info on a given load/dispatch, or Show All for full details.</div>
<div style=color:purple;margin:10px;'>To find loads already in system to use as Master Loads, use the far right column on the <a href='report_dispatch.php'>Dispatch Report</a>.</div><br>
<table class='admin_menu1' style='text-align:left; width:1600px;'>
<thead>
	<?=$tab_hdr ?>
</thead>
<tbody>
	<?=$tab ?>
</tbody>
</table>
</div>

<script type='text/javascript'>
	/*
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete this driver?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}
	
	function confirm_delete_employer_change(myid,driverid,empid) {
		if(confirm("Are you sure you want to delete this driver's employer change record?")) 
		{			
			mrr_change_driver_employer(myid,driverid,empid,1);	
					
			$('.row_'+myid+'').hide();		//hide this row...removed.
		}
	}
		*/		
	//$('.mrr_date_picker').datepicker();	
     //$(".tablesorter").tablesorter();
     
     function mrr_show_load_disp_info(id,disp)
     {
     	if(disp > 0)  
     	{
     		$('.load_disp_'+disp+'').show();
     	}	
     	else if(id >0)
     	{
     		$('.master_load_disps_'+id+'').show();
     	}
     	else
     	{
     		$('.all_master_load_disps').show();
     	}
     	
     	//all_master_load_disps load_disp_".$rows['id']." master_load_disps_".$row['id']."
     }
     function mrr_hide_load_disp_info(id,disp)
     {
     	if(disp > 0)  
     	{
     		$('.load_disp_'+disp+'').hide();
     	}	
     	else if (id > 0)
     	{
     		$('.master_load_disps_'+id+'').hide();
     	}
     	else
     	{
     		$('.all_master_load_disps').hide();
     	}	
     }
     
     function turn_off_master_load(id)
     {
     	$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_deflag_master_load",
			   data: {
			   		"load_id":id
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		
			   		$.noticeAdd({text: "Load "+id+" is no longer a Master Load."});
			   		$('.row_'+id+'').hide();
			   }	
		 });
     }
     
     function mrr_show_load_info(id)
     {
     	if(id > 0)   	$('.load_'+id+'').show();
     	if(id==0)		$('.all_master_loads').show();
     }
     function mrr_hide_load_info(id)
     {
     	if(id > 0)   	$('.load_'+id+'').hide();
     	if(id==0)		$('.all_master_loads').hide();	
     }
     
     $().ready(function() {
		mrr_hide_load_info(0);
	});	
	
	function mrr_run_copy_from_master(masterid)
	{		
		if(masterid==0)
		{
			$.prompt("No Master Load selected to make copy.");	
		}
		else
		{
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_copy_load_handler_from_master_load",
			   data: {
			   		"load_id":masterid
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		newloadid = parseInt($(xml).find('NewLoadID').text());	
			   		if(newloadid>0)
			   		{     					
     					$.noticeAdd({text: "New load "+newloadid+" created from Master Load "+masterid+" successfully."});
     					windowname = window.open("manage_load.php?load_id="+newloadid,'customer_id','height=650,width=980,menubar=no,location=no,resizable=yes,status=no,scrollbars=yes')
						windowname.focus();
     				}
     				else
     				{
     					$.prompt("Failed to make copy of Master Load "+masterid+".");		
     				}
			   }	
			 });
		}
	}
		
</script>

<? include('footer.php') ?>