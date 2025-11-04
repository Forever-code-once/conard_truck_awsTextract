<? include('header.php') ?>
<?
	if(!isset($_GET['request_id']))			$_GET['request_id']=0;
	
	
	//ready form for next group	
	$main_desc="";
	$req_active=1;
	$urgent=0;
	$request_truck_breakdown=0;
	$schedule_date="0000-00-00 00:00:00";
	$completed_date="0000-00-00 00:00:00";
	
	
	//create he new ones...
	$results="";
	if(isset($_POST['mass_producer']))
	{
		if(!isset($_POST['request_id']))		$_POST['request_id']=0;
		if(!isset($_POST['truck_cntr']))		$_POST['truck_cntr']=0;
		if(!isset($_POST['trailers_cntr']))	$_POST['trailers_cntr']=0;
		
		$main_desc=trim($_POST['request_desc']);
		$req_active=(isset($_POST['request_active']) ? 1 : 0);
		$urgent=(isset($_POST['request_urgent']) ? 1 : 0);
		$request_truck_breakdown=(isset($_POST['request_truck_breakdown']) ? 1 : 0);
		$schedule_date="0000-00-00 00:00:00";
		$completed_date="0000-00-00 00:00:00";
		
		if(trim($_POST['req_schedule_date'])!="")		$schedule_date="".date("Y-m-d",strtotime(trim($_POST['req_schedule_date'])))." 00:00:00";
		if(trim($_POST['req_complete_date'])!="")		$completed_date="".date("Y-m-d",strtotime(trim($_POST['req_complete_date'])))." 00:00:00";
		
		//$results.="<br>Making Request ".$_POST['request_id']." Duplicates.";
		//$results.="<br>Request Desc: ".$main_desc.".";
		//$results.="<br>Request Active: ".$req_active.".";
		//$results.="<br>Request Urgent: ".$urgent.".";
		//$results.="<br>Request BreakDown: ".$request_truck_breakdown.".";
		//$results.="<br>Request Scheduled: ".$schedule_date.".";
		//$results.="<br>Request Completed: ".$completed_date.".";
		
		//$results.="<br>TRUCKS: (".$_POST['truck_cntr'].").";
		for($i=0; $i < $_POST['truck_cntr']; $i++)
		{
			$type_id=58;
			$unit_id=0;
			$unit_arr="truck_id_".$i."";
			if(isset($_POST[''.$unit_arr.'']))		$unit_id=$_POST[''.$unit_arr.''];	
			
			if($unit_id > 0)
			{
				//$results.="<br>--Truck ID ".$unit_id." selected.";	
				
				if($_POST['request_id'] > 0)
				{
					$new_id=duplicate_row("maint_requests", $_POST['request_id']);	
					if($new_id > 0)
					{					
						$results.="<br>Request <a href='maint.php?id=".$new_id."' target='_blank'>".$new_id."</a>";
						$sql = "
               				update maint_requests set
               					
               					linedate_completed='".$completed_date."',
               					linedate_scheduled='".$schedule_date."',
               					linedate_added=NOW(),
               					
               					equip_type='".sql_friendly($type_id)."',
               					ref_id='".sql_friendly($unit_id)."',				
               					
               					user_id='".sql_friendly($_SESSION['user_id'])."',
               					sicap_invoice_user_id=0,
               					sicap_invoice_id=0,
               					linedate_invoiced='0000-00-00 00:00:00',
               					sicap_invoice_markup_rate='0.00',
               					sicap_invoice_labor_rate='0.00',
               					customer_id=0,
               					cur_load_id=0,
               					cur_location='',
               					next_load_id=0,
               					next_location='',
               					maint_desc='".sql_friendly($main_desc)."',
               					parent_request_id='".sql_friendly($_POST['request_id'])."',
               					active='".sql_friendly($req_active)."',
               					urgent='".sql_friendly($urgent)."',
               					unit_breakdown='".sql_friendly($request_truck_breakdown)."',
               					deleted=0
               					
               				where id = '".sql_friendly($new_id)."'
               			";
               			simple_query($sql);						
					}
				}
				else
				{	//straight entry...no need for duplication from original row.
					$sql="
						insert into maint_requests
							(id, linedate_added, maint_desc)
							values
							(NULL, NOW(), '".sql_friendly($main_desc)."')
					";
					simple_query($sql);					
					$new_id=mysqli_insert_id($datasource);
					
					if($new_id > 0)
					{					
						$results.="<br>Request <a href='maint.php?id=".$new_id."' target='_blank'>".$new_id."</a>";
						$sql = "
               				update maint_requests set
               					
               					linedate_completed='".$completed_date."',
               					linedate_scheduled='".$schedule_date."',
               					
               					equip_type='".sql_friendly($type_id)."',
               					ref_id='".sql_friendly($unit_id)."',				
               					
               					user_id='".sql_friendly($_SESSION['user_id'])."',
               					sicap_invoice_user_id=0,
               					sicap_invoice_id=0,
               					linedate_invoiced='0000-00-00 00:00:00',
               					sicap_invoice_markup_rate='0.00',
               					sicap_invoice_labor_rate='0.00',
               					customer_id=0,
               					cur_load_id=0,
               					cur_location='',
               					next_load_id=0,
               					next_location='',
               					parent_request_id='".sql_friendly($_POST['request_id'])."',
               					active='".sql_friendly($req_active)."',
               					urgent='".sql_friendly($urgent)."',
               					unit_breakdown='".sql_friendly($request_truck_breakdown)."',
               					deleted=0
               					
               				where id = '".sql_friendly($new_id)."'
               			";
               			simple_query($sql);						
					}	
				}					
			}			
		}
		
		//$results.="<br>TRAILERS: (".$_POST['trailer_cntr'].").";
		for($i=0; $i < $_POST['trailer_cntr']; $i++)
		{
			$type_id=59;
			$unit_id=0;
			$unit_arr="trailer_id_".$i."";
			if(isset($_POST[''.$unit_arr.'']))		$unit_id=$_POST[''.$unit_arr.''];	
			
			if($unit_id > 0)
			{
				//$results.="<br>--Trailer ID ".$unit_id." selected.";	
				
				if($_POST['request_id'] > 0)
				{
					$new_id=duplicate_row("maint_requests", $_POST['request_id']);	
					if($new_id > 0)
					{					
						$results.="<br>Request <a href='maint.php?id=".$new_id."' target='_blank'>".$new_id."</a>";
						$sql = "
               				update maint_requests set
               					
               					linedate_completed='".$completed_date."',
               					linedate_scheduled='".$schedule_date."',
               					linedate_added=NOW(),
               					
               					equip_type='".sql_friendly($type_id)."',
               					ref_id='".sql_friendly($unit_id)."',				
               					
               					user_id='".sql_friendly($_SESSION['user_id'])."',
               					sicap_invoice_user_id=0,
               					sicap_invoice_id=0,
               					linedate_invoiced='0000-00-00 00:00:00',
               					sicap_invoice_markup_rate='0.00',
               					sicap_invoice_labor_rate='0.00',
               					customer_id=0,
               					cur_load_id=0,
               					cur_location='',
               					next_load_id=0,
               					next_location='',
               					maint_desc='".sql_friendly($main_desc)."',
               					parent_request_id='".sql_friendly($_POST['request_id'])."',
               					active='".sql_friendly($req_active)."',
               					urgent='".sql_friendly($urgent)."',
               					unit_breakdown='".sql_friendly($request_truck_breakdown)."',
               					deleted=0
               					
               				where id = '".sql_friendly($new_id)."'
               			";
               			simple_query($sql);						
					}
				}
				else
				{	//straight entry...no need for duplication from original row.
					$sql="
						insert into maint_requests
							(id, linedate_added, maint_desc)
							values
							(NULL, NOW(), '".sql_friendly($main_desc)."')
					";
					simple_query($sql);					
					$new_id=mysqli_insert_id($datasource);
					
					if($new_id > 0)
					{					
						$results.="<br>Request <a href='maint.php?id=".$new_id."' target='_blank'>".$new_id."</a>";
						$sql = "
               				update maint_requests set
               					
               					linedate_completed='".$completed_date."',
               					linedate_scheduled='".$schedule_date."',
               					
               					equip_type='".sql_friendly($type_id)."',
               					ref_id='".sql_friendly($unit_id)."',				
               					
               					user_id='".sql_friendly($_SESSION['user_id'])."',
               					sicap_invoice_user_id=0,
               					sicap_invoice_id=0,
               					linedate_invoiced='0000-00-00 00:00:00',
               					sicap_invoice_markup_rate='0.00',
               					sicap_invoice_labor_rate='0.00',
               					customer_id=0,
               					cur_load_id=0,
               					cur_location='',
               					next_load_id=0,
               					next_location='',
               					parent_request_id='".sql_friendly($_POST['request_id'])."',
               					active='".sql_friendly($req_active)."',
               					urgent='".sql_friendly($urgent)."',
               					unit_breakdown='".sql_friendly($request_truck_breakdown)."',
               					deleted=0
               					
               				where id = '".sql_friendly($new_id)."'
               			";
               			simple_query($sql);						
					}	
				}
			}			
		}
		
		$_GET['request_id']=$_POST['request_id'];
		
		//$results.="<br>Done.";
	}
	
	//ready form for next group	
	//$main_desc="";
	//$req_active=1;
	//$urgent=0;
	//$request_truck_breakdown=0;
	//$schedule_date="0000-00-00 00:00:00";
	//$completed_date="0000-00-00 00:00:00";
	
	if($_GET['request_id'] > 0)
	{
     	$sql="
     		select *	
     		from maint_requests
     		where id='".sql_friendly($_GET['request_id']) ."'
     	";
     	$data= simple_query($sql);
     	if($row = mysqli_fetch_array($data))
     	{
     		$main_desc=trim($row['maint_desc']);
			$req_active=$row['active'];
			$urgent=$row['urgent'];
			$request_truck_breakdown=$row['unit_breakdown'];
			$schedule_date=$row['linedate_scheduled'];
			$completed_date=$row['linedate_completed'];
     	}
	}
?>
<form action="<?=$SCRIPT_NAME ?>?request_id=<?= $_GET['request_id'] ?>" method="post">
<table class='' style='text-align:left;'>
<tr>
	<td valign='top'>
		<table cellpadding='2' cellspacing='0' width='600' border='1' class='table_grid' style='margin:4px'>
          <tr>
          	<td width="150" valign='top'><b>Go To</b></td>
          	<td width="150" valign='top'><a href="maint.php"><b>Maintenance Requests</b></a></td>
          	<td width="150" valign='top'><b>Group Requests</b></td>
          	<td width="150" valign='top'><a href="units_need_repair.php"><b>Units Needing Repair</b></a></td>
          </tr>
          <tr>
          	<td valign='top'><a href="maint_recur.php"><b>Recurring Requests</b></a></td>
          	<td valign='top'><a href="maint_recur_notices.php"><b>Maintenance Alerts</b></a></td>
          	<td valign='top'><a href="report_maint_requests.php"><b>Maintenance Reports</b></a></td>
          	<td valign='top'>&nbsp;</td>
          </tr>
          </table>	
          <br>
          <font class='standard18'><b>&nbsp;Requests Made:</b></font><br>
     	<?=$results ?>
     	<br>          
	</td>	
	<td valign='top'>
		<font class='standard18'><b>&nbsp;Maintenance Request - Group</b></font><br>
		<p>
			Mass-Produce the same request for multiple equipment units.  Use the base settigns to create them all, then process and update them as individual requests.
		</p>
		<input type='hidden' name='request_id' id='request_id' value='<?= $_GET['request_id'] ?>'>
		<div style='border: solid #cccccc 1px; background-color: #e4eaff;'>
     		<br>
     		<table cellpadding="0" cellspacing="0">	     		
     		<tr>
     			<td valign='top'>&nbsp;<b>Request Description</b></td>
     			<td valign='top' colspan="5">
     				<textarea name="request_desc" id="request_desc" rows="3" cols="100" wrap="virtual" style='text-align:left;'><?=$main_desc ?></textarea>&nbsp;
     			</td>
     		</tr>
     		<tr>
     			<td colspan='6'>&nbsp;</td>
     		</tr>
     		<tr>
     			<td>&nbsp;&nbsp;<label for='request_active'><b>Active</b></label>
     				<input type='checkbox' name='request_active' id='request_active' <? if($req_active > 0) echo 'checked'?> value='1'>
     				</td>
     			<td><label for='request_urgent'><b>Deadline/Urgent</b></label>
     				<input type='checkbox' name='request_urgent' id='request_urgent' <? if($urgent > 0) echo 'checked'?> value='1'>
     				</td>
     			<td><b>Scheduled Date</b></td>
     			<td><input name="req_schedule_date" id="req_schedule_date" style="width: 80px;" class="datepicker" value="<?= ($schedule_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($schedule_date)): "" ) ?>"></td>
     			<td><b>Completed Date</b></td>
     			<td><input name="req_complete_date" id="req_complete_date" style="width: 80px;" class="datepicker" value="<?= ($completed_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($completed_date)) : "")?>"></td>			
     		</tr>
     		<tr>
     			<td>&nbsp;&nbsp;</td>
     			<td>&nbsp;
     				<label for='request_truck_breakdown'><b>Broken Down!</b></label> 
					<input type='checkbox' name='request_truck_breakdown' id='request_truck_breakdown' <? if($request_truck_breakdown > 0) echo 'checked'?> value='1'>	
     			</td>
     			<td><b>&nbsp;</b></td>
     			<td>&nbsp;</td>
     			<td><b>&nbsp;</b></td>
     			<td>&nbsp;</td>			
     		</tr>	     		
     		<tr>
     			<td colspan='6'>&nbsp;</td>
     		</tr>
     		<tr>
     			<td valign='top'>				
     				<b>&nbsp;&nbsp;Trucks</b>
     			</td>
     			<td valign='top' colspan='2'>	
     				<div style='max-height:350px; overflow:auto; padding:0 10px; 0 10px; border:1px solid #000000;'>
     				<?  
     				$trucks=0;                       				
                    	$sql = "
                    		select trucks.*			
                    		from trucks			
                    		where trucks.deleted = 0
                    		order by trucks.active desc, trucks.name_truck asc
                    	";
                    	$data_trucks = simple_query($sql);
                    	while($row = mysqli_fetch_array($data_trucks))
                    	{
                    		echo "<label><input type='checkbox' name='truck_id_".$trucks."' id='truck_id_".$trucks."' value='".$row['id']."'> ".trim($row['name_truck'])." ".($row['active'] == 0 ? " - Inactive" : "")."</label><br>";
                    		$trucks++;
                    	}
     				?>
     				</div>
     				<input type='hidden' name='truck_cntr' id='truck_cntr' value='<?= $trucks ?>'>
     			</td>
     			<td valign='top'>				
     				<b>&nbsp;&nbsp;Trailers</b>
     			</td>
     			<td valign='top' colspan='2'>	
     				<div style='max-height:350px; overflow:auto; padding:0 10px; 0 10px; border:1px solid #000000;'>
     				<?    
     				$trailers=0;
                    	$sql = "
                    		select trailers.*
                    		from trailers
                    		where trailers.deleted = 0	
                    		order by trailers.active desc, trailers.trailer_name asc
                    	";
                    	$data_trailers = simple_query($sql);
                    	while($row = mysqli_fetch_array($data_trailers))
                    	{
                    		echo "<label><input type='checkbox' name='trailer_id_".$trailers."' id='trailer_id_".$trailers."' value='".$row['id']."'> ".trim($row['trailer_name'])." ".($row['active'] == 0 ? " - Inactive" : "")."</label><br>";
                    		$trailers++;
                    	}
     				?>
     				</div>
     				<input type='hidden' name='trailer_cntr' id='trailer_cntr' value='<?= $trailers ?>'>
     			</td>
     		</tr>
     		<tr>
     			<td colspan='6'><center><input type="submit" name="mass_producer" id="mass_producer" value="Mass-Produce Requests"></center></td>
     		</tr>
     		<tr>
     			<td colspan='6'>&nbsp;</td>
     		</tr>     		
     		</table>
     	</div>
	</td>
</tr>	
</table>
</form>
<script type='text/javascript'>
	$().ready(function() {
		
	});
</script>
<? include('footer.php') ?>