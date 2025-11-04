<?
	$usetitle = "Units Needing Repair";
	$use_title = "Units Needing Repair";
	
	if(!isset($_GET['id']))			$_GET['id']=0;
	if(!isset($_GET['tid']))			$_GET['tid']=0;
	
	if(isset($_POST['truck_id']))		$_GET['id']=$_POST['truck_id'];
	if(isset($_POST['trailer_id']))	$_GET['tid']=$_POST['trailer_id'];
	
?>
<? include('header.php') ?>
<?
	if(isset($_POST['update_repairs_needed']))
	{
		$toggle=(isset($_POST['repairs_toggle']) ? 1 : 0);	//	$row['repairs_pending'];	
		$list=trim($_POST['repairs_list']);				//	trim($row['repairs_pending_list']);
		$opened=trim($_POST['repairs_opened']);				//	trim($row['repairs_pending_date_opened']);
		$inspect=trim($_POST['repairs_inspect']);			//	trim($row['repairs_pending_date_inspect']);
		$repair=trim($_POST['repairs_repair']);				//	trim($row['repairs_pending_date_repair']);
		$closed=trim($_POST['repairs_closed']);				//	trim($row['repairs_pending_date_closed']);
		$internal=(isset($_POST['repairs_internal']) ? 1 : 0);
		$done=(isset($_POST['repairs_made']) ? 1 : 0);	
		
		if($_POST['truck_id'] > 0 && $_POST['trailer_id']==0)
		{
			$sql = "
				update trucks set 
					repairs_pending='".sql_friendly($toggle)."',
					repairs_pending_list='".sql_friendly(trim($list))."',
				
					repairs_pending_date_opened = '".($opened!=""  ? "".date("Y-m-d",strtotime($opened))." 00:00:00" :"0000-00-00 00:00:00")."',
					repairs_pending_date_inspect = '".($inspect!=""  ? "".date("Y-m-d",strtotime($inspect))." 00:00:00" :"0000-00-00 00:00:00")."',
					repairs_pending_date_repair = '".($repair!=""  ? "".date("Y-m-d",strtotime($repair))." 00:00:00" :"0000-00-00 00:00:00")."',
					repairs_pending_date_closed = '".($closed!=""  ? "".date("Y-m-d",strtotime($closed))." 00:00:00" :"0000-00-00 00:00:00")."',
					repairs_pending_made='".sql_friendly($done)."',
					repairs_pending_internal='".sql_friendly($internal)."'
					
				where id='".sql_friendly($_POST['truck_id'])."'
			";		
			simple_query($sql);
		}
		elseif($_POST['truck_id']==0 && $_POST['trailer_id'] > 0)
		{
			$sql = "
				update trailers set 
					pending_repairs='".sql_friendly($toggle)."',
					pending_repairs_list='".sql_friendly(trim($list))."',
				
					pending_repairs_date_opened = '".($opened!=""  ? "".date("Y-m-d",strtotime($opened))." 00:00:00" :"0000-00-00 00:00:00")."',
					pending_repairs_date_inspect = '".($inspect!=""  ? "".date("Y-m-d",strtotime($inspect))." 00:00:00" :"0000-00-00 00:00:00")."',
					pending_repairs_date_repair = '".($repair!=""  ? "".date("Y-m-d",strtotime($repair))." 00:00:00" :"0000-00-00 00:00:00")."',
					pending_repairs_date_closed = '".($closed!=""  ? "".date("Y-m-d",strtotime($closed))." 00:00:00" :"0000-00-00 00:00:00")."',
					pending_repairs_made='".sql_friendly($done)."',
					pending_repairs_internal='".sql_friendly($internal)."'
				where id='".sql_friendly($_POST['trailer_id'])."'
			";		
			simple_query($sql);
		}
	}
?>
<table class='' style='text-align:left;'>
<tr>
	<td valign='top'>
     		
     	<table cellpadding='2' cellspacing='0' width='600' border='1' class='table_grid no_print' style='margin:4px'>
     	<tr>
     		<td width="150" valign='top'><b>Go To</b></td>
     		<td width="150" valign='top'><a href="maint.php"><b>Maintenance Requests</b></a></td>
     		<td width="150" valign='top'><a href="maint_group.php"><b>Group Requests</b></a></td>
     		<td width="150" valign='top'><b>Units Needing Repair</b></td>
     	</tr>
     	<tr>
     		<td valign='top'><a href="maint_recur.php"><b>Recurring Requests</b></a></td>
     		<td valign='top'><a href="maint_recur_notices.php"><b>Maintenance Alerts</b></a></td>
     		<td valign='top'><a href="report_maint_requests.php"><b>Maintenance Reports</b></a></td>
     		<td valign='top'>&nbsp;</td>
     	</tr>
     	</table>	
     		
		<?
		$sel_trucks="<select name='truck_id' id='truck_id' onChange='mrr_submit(1);'>";
		$sel_trucks.="<option value='0'>Select Truck</option>";
		$sql = "
			select *
			from trucks 
			where deleted=0
			order by active desc, 
				name_truck asc, 
				id asc
		";		
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$sel="";
			if($_GET['id']==$row['id'])
			{
				$toggle=$row['repairs_pending'];	
				$list=trim($row['repairs_pending_list']);
				$opened=trim($row['repairs_pending_date_opened']);
				$inspect=trim($row['repairs_pending_date_inspect']);
				$repair=trim($row['repairs_pending_date_repair']);
				$closed=trim($row['repairs_pending_date_closed']);
				$internal=$row['repairs_pending_internal'];
				$done=$row['repairs_pending_made'];
				
				$sel=" selected";
			}
			
			$sel_trucks.="<option value='".$row['id']."'".($row['active'] ==0 ? " style='color:#cccccc;'" : "")."".$sel.">".$row['name_truck']."".($row['active'] ==0 ? " - Inactive" : "")."</option>";
		}
		$sel_trucks.="</select>";
		
		
		$sel_trailers="<select name='trailer_id' id='trailer_id' onChange='mrr_submit(2);'>";
		$sel_trailers.="<option value='0'>Select Truck</option>";
		$sql = "
			select *
			from trailers 
			where deleted=0
			order by active desc, 
				trailer_name asc, 
				id asc
		";		
		$data = simple_query($sql);	
		while($row = mysqli_fetch_array($data))
		{
			$sel="";
			if($_GET['tid']==$row['id'])
			{
				$toggle=$row['pending_repairs'];	
				$list=trim($row['pending_repairs_list']);
				$opened=trim($row['pending_repairs_date_opened']);
				$inspect=trim($row['pending_repairs_date_inspect']);
				$repair=trim($row['pending_repairs_date_repair']);
				$closed=trim($row['pending_repairs_date_closed']);
				$internal=$row['pending_repairs_internal'];
				$done=$row['pending_repairs_made'];
				
				$sel=" selected";
			}
			$sel_trailers.="<option value='".$row['id']."'".($row['active'] ==0 ? " style='color:#cccccc;'" : "")."".$sel.">".$row['trailer_name']."".($row['active'] ==0 ? " - Inactive" : "")."</option>";	
		}	
		$sel_trailers.="</select>";
		?>
		<form name='my_form' action='<?=$SCRIPT_NAME?>?id=<?=$_GET['id']?>&tid=<?=$_GET['tid']?>' method='post'>
		<table cellpadding='0' cellspacing='0' width='1000' border='0' class='admin_menu no_print' style='margin:4px'>
		<tr>
			<td valign='top'><b><label for='repairs_toggle'>Unit Needs Repairs </label></b></td>	
			<td valign='top'><input type='checkbox' name='repairs_toggle' id='repairs_toggle' value='1'<?=($toggle > 0 ? " checked" : "") ?>></td>
			<td valign='top'><a href='units_need_repair.php?id=0&tid=0'>Clear/New Edit Form</a></td>	
			<td valign='top' align='right'><input type='submit' name='update_repairs_needed' id='update_repairs_needed' value='Update Unit'></td>
		</tr>
		<tr>
			<td valign='top'><b>Truck</b></td>	
			<td valign='top'><?=$sel_trucks ?></td>
			<td valign='top'><b>Or Trailer</b></td>	
			<td valign='top'><?=$sel_trailers ?></td>
		</tr>
		<tr>
			<td valign='top'><b>Repairs Needed</b></td>	
			<td valign='top' colspan='3'><textarea name="repairs_list" id="repairs_list" wrap='virtual' rows='2' cols='100'><?=trim($list)?></textarea></td>
		</tr>
		<tr>
			<td valign='top'><b>Opened:</b></td>	
			<td valign='top'><input name="repairs_opened" id='repairs_opened' value="<? if(strtotime($opened) != 0) echo date("m/d/Y", strtotime($opened)) ?>" style='width:80px;' class='mrr_date_input'></td>
			<td valign='top'><b>Inspected:</b></td>	
			<td valign='top'><input name="repairs_inspect" id='repairs_inspect' value="<? if(strtotime($inspect) != 0) echo date("m/d/Y", strtotime($inspect)) ?>" style='width:80px;' class='mrr_date_input'></td>
		</tr>
		<tr>
			<td valign='top'><b>Repaired:</b></td>	
			<td valign='top'><input name="repairs_repair" id='repairs_repair' value="<? if(strtotime($repair) != 0) echo date("m/d/Y", strtotime($repair)) ?>" style='width:80px;' class='mrr_date_input'></td>
			<td valign='top'><b>Closed:</b></td>	
			<td valign='top'><input name="repairs_closed" id='repairs_closed' value="<? if(strtotime($closed) != 0) echo date("m/d/Y", strtotime($closed)) ?>" style='width:80px;' class='mrr_date_input'></td>
		</tr>
		<tr>
			<td valign='top'><label for='repairs_internal'><b>Repaired Internally</b></label></td>	
			<td valign='top'><input type='checkbox' name='repairs_internal' id='repairs_internal' value='1'<?=($internal > 0 ? " checked" : "") ?>></td>
			<td valign='top'><label for='repairs_made'><b>Repairs Completed</b></label></td>	
			<td valign='top'><input type='checkbox' name='repairs_made' id='repairs_made' value='1'<?=($done > 0 ? " checked" : "") ?>></td>
		</tr>		
		</table>
		</form>
	</td>
	
	<td valign='top'>
<?
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}
	
	$_POST['build_report']=1;
	
	/*
	if(isset($_GET['truck_id'])) {
		$_POST['truck_id'] = $_GET['truck_id'];
		
		if(isset($_GET['date_from']) && !isset($_POST['date_from']))		$_POST['date_from']=date("m/d/Y", strtotime($_GET['date_from']));
		
		$_POST['active_maint']=1;
		$_POST['build_report'] = 1;
	}
	if(isset($_GET['trailer_id'])) {
		$_POST['trailer_id'] = $_GET['trailer_id'];
		
		if(isset($_GET['date_from']) && !isset($_POST['date_from']))		$_POST['date_from']=date("m/d/Y", strtotime($_GET['date_from']));
		
		$_POST['active_maint']=1;
		$_POST['build_report'] = 1;
	}

	if(!isset($_POST['build_report'])) $_POST['active_maint'] = 1;
	
	if(isset($_GET['current']))
	{
		$_POST['date_from']="11/01/2011";
		$_POST['date_to']=date("m/d/Y", time());
		
		$_POST['build_report'] = 1;	
	}
	*/
	

	$rfilter = new report_filter();
	//$rfilter->show_truck 			= true;
	//$rfilter->show_trailer 		= true;	
	$rfilter->show_date_range		= false;
	$rfilter->mrr_send_email_here		= true;
	$rfilter->show_font_size			= true;
		
	$rfilter->show_filter();
	
	
	
	$sql="";
	
	$uuid = createuuid();
	$excel_filename = "units_need_repair_$uuid.xls";
	$export_file = "";
	$use_excel=1;
		
 	if(isset($_POST['build_report']))
 	{ 
		/*
		$search_date_range = "
				and linedate_scheduled >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
				and linedate_scheduled<= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
				";
		
		if(isset($_GET['current']))
		{
			$search_date_range = "
				and (
					(linedate_scheduled >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' and linedate_scheduled<= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59')
					or
					(linedate_scheduled='0000-00-00 00:00:00')
				)					
				";
		}
		*/
		$conditions="";
		$cntr=0;		//for trucks
		$cntr2=0;		//for trailers
		
			
		$sql = "
			select trucks.id as unit_id,
				trucks.active as unit_active,
				trucks.name_truck as unit_name,
				trucks.repairs_pending_list as unit_list,
				trucks.repairs_pending_date_opened as date_opened,
				trucks.repairs_pending_date_inspect as date_inspect,
				trucks.repairs_pending_date_repair as date_repair,
				trucks.repairs_pending_date_closed as date_closed,
				trucks.repairs_pending_made as repairs_made,
				trucks.repairs_pending_internal as repairs_internal,
				'Truck' as etype
			from trucks 
			where trucks.deleted=0
				and trucks.repairs_pending > 0
				
			union all
			
			select trailers.id as unit_id,
				trailers.active as unit_active,
				trailers.trailer_name as unit_name,
				trailers.pending_repairs_list as unit_list,
				trailers.pending_repairs_date_opened as date_opened,
				trailers.pending_repairs_date_inspect as date_inspect,
				trailers.pending_repairs_date_repair as date_repair,
				trailers.pending_repairs_date_closed as date_closed,
				trailers.pending_repairs_made as repairs_made,
				trailers.pending_repairs_internal as repairs_internal,
				'Trailer' as etype
			from trailers 
			where trailers.deleted=0
				and trailers.pending_repairs > 0
				
			order by unit_active desc, 
				unit_name asc, 
				unit_id asc
		";		
		$data = simple_query($sql);
			
		
		$stylex=" style='font-weight:bold;'";
		$mrr_total_head = " style='font-weight:bold; margin:0 10px; width:1400px; text-align:left;'";
		$tablex=" border='1' cellpadding='1' cellspacing='1' width='1200'";
		$headerx=" style='background-color:#CCCCFF;'";	
	?>
	</td>
<tr>
</tr>
	<td valign='top' colspan='2'>
	<?
	//echo "<br>Query:".$msql.".<br>";
	ob_start();
	?>
	<table <?=( $mrr_use_styles > 0 ? "".$mrr_total_head."" : " class='admin_menu2 font_display_section' style='margin:0 10px;width:1800px;text-align:left;'")?>>
	<tr>
		<td colspan='10'>
			<center>
			<span <?=( $mrr_use_styles > 0 ? "".$stylex."" : "class='section_heading'")?>><?=$use_title ?></span>
			</center>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td></td>
		<td>Type</td>
		<td>Unit</td>
		<td>Repairs Needed</td>
		<td>Opened</td>
		<td>Inspected</td>
		<td>Repaired</td>
		<td>Closed</td>		
		<td>Internal</td>
		<td>Done</td>
	</tr>
	<?
		$export_file .= "".$use_title."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
		
		$export_file .= "".chr(9).
			"Type".chr(9).
			"Unit".chr(9).
			"Repairs Needed".chr(9).
			"Opened".chr(9).
			"Inspected".chr(9).
			"Repaired".chr(9).
			"Closed".chr(9).
			"Internal".chr(9).
			"Done".chr(9);
		$export_file .= chr(13);	
		
		while($row = mysqli_fetch_array($data))
		{
			$link="";
			
			$list=trim($row['unit_list']);
			
			$list=str_replace(chr(13),"<br>",$list);		$list=str_replace("\r","",$list);		$list=str_replace("\n","<br>",$list);
			
			if($row['etype']=="Truck")
			{
				$link="<a href='admin_trucks.php?id=".$row['unit_id']."' target='_blank'>".trim($row['unit_name'])."</a>";
				
				echo "
					<tr style='background-color:#".($cntr % 2 == 1 ? 'ffffff' : 'eeeeee').";'>
						<td valign='top'>".($row['unit_active']==0 ? "Inactive" : "&nbsp;")."</td>
						<td valign='top' nowrap><a href='units_need_repair.php?id=".$row['unit_id']."&tid=0'>Edit</a> ".$row['etype']."</td>
						<td valign='top'>".$link."</td>
						<td valign='top'>".$list."</td>
						<td valign='top'>".($row['date_opened']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_opened'])) : "")."</td>
						<td valign='top'>".($row['date_inspect']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_inspect'])) : "")."</td>
						<td valign='top'>".($row['date_repair']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_repair'])) : "")."</td>
						<td valign='top'>".($row['date_closed']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_closed'])) : "")."</td>
						<td valign='top'>".($row['repairs_internal'] > 0 ? "Yes" : "No")."</td>
						<td valign='top'>".($row['repairs_made'] > 0 ? "Yes" : "No")."</td>						
					</tr>
				";					
				
				$cntr++;	
			}
			elseif($row['etype']=="Trailer")
			{
				$link="<a href='admin_trailers.php?id=".$row['unit_id']."' target='_blank'>".trim($row['unit_name'])."</a>";
				
				echo "
					<tr style='background-color:#".($cntr2 % 2 == 1 ? 'cccccc' : 'dddddd').";'>
						<td valign='top'>".($row['unit_active']==0 ? "Inactive" : "&nbsp;")."</td>
						<td valign='top' nowrap><a href='units_need_repair.php?id=0&tid=".$row['unit_id']."'>Edit</a> ".$row['etype']."</td>
						<td valign='top'>".$link."</td>
						<td valign='top'>".$list."</td>
						<td valign='top'>".($row['date_opened']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_opened'])) : "")."</td>
						<td valign='top'>".($row['date_inspect']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_inspect'])) : "")."</td>
						<td valign='top'>".($row['date_repair']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_repair'])) : "")."</td>
						<td valign='top'>".($row['date_closed']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_closed'])) : "")."</td>
						<td valign='top'>".($row['repairs_internal'] > 0 ? "Yes" : "No")."</td>
						<td valign='top'>".($row['repairs_made'] > 0 ? "Yes" : "No")."</td>	
					</tr>
				";	
				
				$cntr2++;	
			}
							
			$export_file .= "".($row['unit_active']==0 ? "Inactive" : "")."".chr(9).
          		"".$row['etype']."".chr(9).
          		"".trim($row['unit_name'])."".chr(9).
          		"".trim($row['unit_list'])."".chr(9).
          		"".($row['date_opened']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_opened'])) : "")."".chr(9).
          		"".($row['date_inspect']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_inspect'])) : "")."".chr(9).
          		"".($row['date_repair']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_repair'])) : "")."".chr(9).
          		"".($row['date_closed']!='0000-00-00 00:00:00' ? date("m/d/Y",strtotime($row['date_closed'])) : "")."".chr(9).
          		"".($row['repairs_internal'] > 0 ? "Yes" : "No")."".chr(9).          		
          		"".($row['repairs_made'] > 0 ? "Yes" : "No")."".chr(9);
          		
			$export_file .= chr(13);			
		}
	
		echo "
			<tr>
				<td colspan='10'><hr></td>
			</tr>
			<tr>
				<td colspan='10'><b>".$cntr." Truck(s)</b></td>
			</tr>
			<tr>
				<td colspan='10'><b>".$cntr2." Trailer(s)</b></td>
			</tr>
		";	
	
	
	$export_file .= "".$cntr." Truck(s)".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9);
	$export_file .= chr(13);	
	
	$export_file .= "".$cntr2." Trailer(s)".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9);
	$export_file .= chr(13);	
	
	?>
	</table>
	<?
	$pdf = ob_get_contents();
	ob_end_clean();
	
	echo $pdf;
	
	if(trim($_POST['mrr_email_addr'])!="" && isset($_POST['mrr_email_report']))
	{
		$user_name=$defaultsarray['company_name'];
		$From=$defaultsarray['company_email_address'];
		$Subject="";
		if(isset($use_title))			$Subject=$use_title;
		elseif(isset($usetitle))			$Subject=$usetitle;
		
		$pdf=str_replace(" href="," name=",$pdf);
		//$pdf=str_replace("</a>","",$pdf);
			
		$sentit=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject,$pdf,$pdf);
		
		$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
		echo "<br><br><b>This report has ".$sent_msg." to '".$_POST['mrr_email_addr_name']."' at E-Mail address '".$_POST['mrr_email_addr']."'.</b><br><br>";
		
		//$sentit=mrr_trucking_sendMail('dconard@conardlogistics.com',"Dale Conard",$From,$user_name,'','',$Subject,$pdf,$pdf);
		//$sentit=mrr_trucking_sendMail('jgriffith@conardlogistics.com',"James Griffith",$From,$user_name,'','',$Subject,$pdf,$pdf);
		//$sentit=mrr_trucking_sendMail('amassar@conardlogistics.com',"Anthony Massar",$From,$user_name,'','',$Subject,$pdf,$pdf);     			
	}
	?>
	</td>
</tr>
</table>		
<? }
	//echo "".$msql."";
		
	$prefix="";
	if($use_excel > 0) 
	{
		$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
		fwrite($fp, $export_file); 
		fclose($fp);
		
		$prefix="<br><br><a href=\"http://trucking.conardlogistics.com/temp/".$excel_filename."\" target='_blank'>Click for Excel Version</a><br><br>";
		echo $prefix;
	}
 ?>
<script type='text/javascript'>
	//$('#date_from').datepicker();
	//$('#date_to').datepicker();
	$('.mrr_date_input').datepicker();
	
	function mrr_submit(id)
	{
		if(id==1)	$('#trailer_id').val('0');
		if(id==2)	$('#truck_id').val('0');
		
		document.my_form.submit();
	}
</script>
<? include('footer.php') ?>