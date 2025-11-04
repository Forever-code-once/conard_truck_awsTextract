<?
$usetitle = "Report - Maintenance Requests";
$use_title = "Report - Maintenance Requests";

if(isset($_GET['current']) && $_GET['current']==1)
{
     $_POST['date_from']="11/01/2011";
     $_POST['date_to']=date("m/d/Y", time());
     
     $_POST['active_maint'] = 1;
     
     //$_POST['snooze_maint'] = 1;
     
     $_POST['build_report'] = 1;
}
$last_year=(int) date("Y",time());      $last_year--;

if(!isset($_GET['date_from']) && !isset($_POST['date_from']))	{	$_POST['date_from']=date("m/d/".$last_year."", time());			}   //$_GET['current']=1;
if(!isset($_GET['date_to']) && !isset($_POST['date_to']))			$_POST['date_to']=date("m/d/Y", time());


//$excel_filename = "maint_requests_test.xlsx";

//header("Content-Type:   application/vnd.ms-excel");
//header("Content-type:   application/x-msexcel; charset=utf-8");
//header("Content-Disposition: attachment; filename=".$excel_filename."");
//header("Expires: 0");
//header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//header("Cache-Control: private",false);	
?>
<? include('header.php') ?>
<table class='' style='text-align:left;'>
<tr>
	<td valign='top'>
		
	<table cellpadding='2' cellspacing='0' width='600' border='1' class='table_grid no_print' style='margin:4px'>
	<tr>
		<td width="150" valign='top'><b>Go To</b></td>
		<td width="150" valign='top'><a href="maint.php"><b>Maintenance Requests</b></a></td>
		<td width="150" valign='top'><a href="maint_group.php"><b>Group Requests</b></a></td>
		<td width="150" valign='top'><a href="units_need_repair.php"><b>Units Needing Repair</b></a></td>
	</tr>
	<tr>
		<td valign='top'><a href="maint_recur.php"><b>Recurring Requests</b></a></td>
		<td valign='top'><a href="maint_recur_notices.php"><b>Maintenance Alerts</b></a></td>
		<td valign='top'><b>Maintenance Reports</b></td>
		<td valign='top'>&nbsp;</td>
	</tr>
	</table>
<?
	$use_title="Maintenance Report";
	
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	    $mrr_use_styles=1;	}
	
	if(isset($_GET['maintenance_id'])) {
		$_POST['maintenance_id'] = $_GET['maintenance_id'];
		$_POST['build_report'] = 1;
	}
	
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

	//if(!isset($_POST['build_report'])) $_POST['active_maint'] = 1;
	
	/*
	if(isset($_GET['current']))
	{
		$_POST['date_from']="11/01/2011";
		$_POST['date_to']=date("m/d/Y", time());
		
		$_POST['build_report'] = 1;	
	}
	*/
	

	$rfilter = new report_filter();
	$rfilter->show_truck 			= true;
	$rfilter->show_trailer 			= true;	
	$rfilter->show_skips 			= true;	
	$rfilter->active_maint 			= true;
	$rfilter->closed_maint 			= true;
	$rfilter->recurring_maint 		= true;
	$rfilter->down_time_hours 		= true;
	$rfilter->down_time_hours_to 	= true;
	$rfilter->maint_request_cost	= true;
	$rfilter->maint_request_cost_to	= true;
	$rfilter->maintenance_id		= true;
	$rfilter->maintenance_desc		= true;
	$rfilter->maint_category		= true;
	$rfilter->maint_detail_items	= true;
	$rfilter->maint_from_recur		= true;
	$rfilter->maint_urgent			= true;
	$rfilter->mrr_send_email_here	= true;
	$rfilter->snooze_maint          = true;
	$rfilter->show_font_size		= true;
		
	$rfilter->show_filter();
	
	$show_line_items=0;			if(isset($_POST['maint_detail_items']))		$show_line_items=1;
	
	
	
	$sql="";
    $uuid = createuuid();
    $excel_filename = "maint_requests_$uuid.csv";   //xls
	$export_file = "";
	$use_excel=1;
		
 	if(isset($_POST['build_report']))
 	{ 
		$search_date_range="";
        
		$reactor=0;							if( isset($_POST['active_maint'] ))		$reactor=1;
		$reactor2=0;						if( isset($_POST['closed_maint'] ))		$reactor2=1;
		$recurrer=0;						if( isset($_POST['recurring_maint'] ))	$recurrer=1;
        $reactor3=0;						if( isset($_POST['snooze_maint'] ))		$reactor3=1;
		
		$ranger_date="maint_requests.linedate_added";	//maint_requests.linedate_scheduled
		if($reactor2 > 0)			$ranger_date="maint_requests.linedate_completed";
		
		
		if(isset($_GET['date_from']) && $_GET['date_from']=="1969-12-31")	
		{
			//$search_date_range = " and (linedate_scheduled > '0000-00-00 00:00:00' or linedate_scheduled='0000-00-00 00:00:00') ";
		}
		else
		{
			$search_date_range = " and ".$ranger_date." >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' ";		//( or ".$ranger_date."='0000-00-00 00:00:00')
		}
		
		$search_date_range .= " and ".$ranger_date."<= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59' ";		//( or linedate_scheduled='0000-00-00 00:00:00')
		
		if(isset($_GET['current']))
		{
			$search_date_range = "
				and (
					(".$ranger_date." >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' and ".$ranger_date."<= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59')					
				)					
				";		//or (linedate_scheduled='0000-00-00 00:00:00')
		}
		
		$conditions="";
		
		
		if($_POST['maintenance_id'] != "")			$conditions.=" and maint_requests.id='".sql_friendly((int) $_POST['maintenance_id'])."'";	
		
		if($_POST['maint_request_cost'] > 0 )		$conditions.=" and maint_requests.cost>='".sql_friendly($_POST['maint_request_cost'])."'";	
		if($_POST['maint_request_cost_to'] > 0 )	$conditions.=" and maint_requests.cost<='".sql_friendly($_POST['maint_request_cost_to'])."'";	
		if($_POST['down_time_hours'] > 0 )			$conditions.=" and maint_requests.down_time_hours>='".sql_friendly($_POST['down_time_hours'])."'";		
		if($_POST['down_time_hours_to'] > 0 )		$conditions.=" and maint_requests.down_time_hours<='".sql_friendly($_POST['down_time_hours_to'])."'";	
		
		if(isset($_POST['maint_from_recur']))		$conditions.=" and maint_requests.recur_ref>0";
		if(isset($_POST['maint_urgent']))			$conditions.=" and maint_requests.urgent='1'";
		
		/*	
		$conditions.=" and (maint_requests.active='".$reactor."'";	
		if($reactor==0)
		{
			$conditions.=" or maint_requests.linedate_completed>'0000-00-00 00:00:00')";	
		}
		else
		{
			$conditions.=" and maint_requests.linedate_completed='0000-00-00 00:00:00')";		
		}
		*/		
		
		if($reactor2 > 0 && $reactor > 0)
		{ 	//closed and active...probably none...
			$conditions.=" and maint_requests.linedate_completed>='2010-01-01 00:00:00' and maint_requests.active > 0";		
		}
		elseif($reactor2 > 0)
		{	//closed (perhaps active)
			$conditions.=" and (maint_requests.linedate_completed>='2010-01-01 00:00:00')";			// and maint_requests.active = 0
		}
		elseif($reactor > 0)
		{	//opened/active (not necessarily completed)
			$conditions.=" and (maint_requests.linedate_completed<'2010-01-01 00:00:00')";			// or maint_requests.active > 0
		}
		else
		{	//not active and not completed
			//$conditions.=" and maint_requests.active=0 and maint_requests.linedate_completed<'2010-01-01 00:00:00'";	
		}	
		
		if(isset($_GET['current']))
		{
			$conditions.=" and maint_requests.recur_flag='0' and maint_requests.active='1'";	
		}
		else
		{
			$conditions.=" and maint_requests.recur_flag='".$recurrer."'";	
		}
		
		if($_POST['maintenance_desc']!="")	
		{
			$conditions.=" and (maint_requests.maint_desc LIKE '%".sql_friendly($_POST['maintenance_desc'])."%'";	
			$conditions.=" or maint_line_items.lineitem_desc LIKE '%".sql_friendly($_POST['maintenance_desc'])."%')";
		}
		if($_POST['maint_category'] > 0)
		{
			$conditions.=" and maint_line_items.cat_id='".sql_friendly($_POST['maint_category'])."'";
		}	
		
		if($_POST['skip_type_id'] > 0)
		{
			$conditions.=" and maint_requests.equip_type!='".sql_friendly($_POST['skip_type_id'])."'";	
		}

/*
  if($reactor3 > 0)
{   //filter out any of them that the last item in the notes was a snooze.
    $conditions.=" and (
            select IF(LOCATE('SNOOZE: ',mr_unit_locations.mr_location) > 0, 1,0) as snoozing 
            from mr_unit_locations 
            where mr_unit_locations.maint_id = maint_requests.id and mr_unit_locations.deleted = 0
            order by mr_unit_locations.id desc
            limit 1
       ) > 0";
}
  else
{
    /* 
    $conditions.=" and (
            select IF(LOCATE('SNOOZE: ',mr_unit_locations.mlocation) > 0, 1,0) as snoozing 
            from mr_unit_locations 
            where mr_unit_locations.maint_id = maint_requests.id and mr_unit_locations.deleted = 0
            order by mr_unit_locations.id desc
            limit 1
       ) = 0";
    
}
  */
						
		$msql = "
			select distinct maint_requests.id,
				(SELECT trucks.name_truck FROM trucks WHERE trucks.id=maint_requests.ref_id AND (maint_requests.equip_type=1 OR maint_requests.equip_type=58)) AS truck_namer,
				(SELECT trailers.trailer_name FROM trailers WHERE trailers.id=maint_requests.ref_id AND (maint_requests.equip_type=2 OR maint_requests.equip_type=59)) AS trailer_namer
			from maint_requests 
				left join maint_line_items on maint_line_items.ref_id=maint_requests.id and maint_line_items.deleted='0'			
			where maint_requests.deleted='0' 
				".$conditions."
				".$search_date_range."				
			order by maint_requests.equip_type asc,truck_namer asc,trailer_namer asc,maint_requests.linedate_added asc,maint_requests.maint_desc asc,maint_requests.id asc
		";	// limit 1
			//and (maint_requests.equip_type=1 OR maint_requests.equip_type=58  or  maint_requests.equip_type=2 OR maint_requests.equip_type=59)
			//and maint_requests.ref_id > 0
		
		$id_list[0]=0;			$cntr=0;
		$mdata = simple_query($msql);
		while($mrow = mysqli_fetch_array($mdata))
		{			
			$id_list[$cntr]=$mrow['id'];
			$cntr++;
		}
		mysqli_free_result($mdata);	
		
	$stylex=" style='font-weight:bold;'";
	$mrr_total_head = " style='font-weight:bold; margin:0 10px; width:1400px; text-align:left;'";
	$tablex=" border='1' cellpadding='1' cellspacing='1' width='1200'";
	$headerx=" style='background-color:#CCCCFF;'";	
	
	
	?>
</td>
<td valign='top'>
	<?
	//echo "<br>Query:".$msql.".<br>";
	//die('Testing Mode.');
	
	$tot_hours=0;
	$tot_cost=0;
	$tot_reqs=0;
	
	ob_start();
	?>
	<table <?=( $mrr_use_styles > 0 ? "".$mrr_total_head."" : " class='admin_menu2 font_display_section' style='margin:0 10px;width:1200px;text-align:left;'")?>>
	<tr>
		<td colspan='12'>
			<center>
			<span <?=( $mrr_use_styles > 0 ? "".$stylex."" : "class='section_heading'")?>>Maintenance Request Report</span>
			</center>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td></td>
		<td nowrap>Maintenance Request</td>
		<td>Type</td>
		<td>Equipment</td>
		<td>Scheduled</td>
		<td>Completed</td>
		<td>Recurring</td>
		<td nowrap>Created By</td>
		<td nowrap>Created On</td>
		<td>Location</td>
		<td align='right' nowrap>Hours Down</td>
		<td align='right'>Cost</td>
	</tr>
	<?
		$export_file .= "Maintenance Request Report".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
		
		$export_file .= "".chr(9).
			"Maintenance Request".chr(9).
			"Type".chr(9).
			"Equipment".chr(9).
			"Scheduled".chr(9).
			"Completed".chr(9).
			"Recurring".chr(9).
			"Created By".chr(9).
			"Created On".chr(9).
			"Location".chr(9).
			"Hours Down".chr(9).
			"Cost".chr(9);
		$export_file .= chr(13);	
		
		
		//take array of requests...show all items....
		for($i=0;$i < $cntr;$i++)
		{
			$req_id=$id_list[$i];
			
			$sql = "
				select maint_requests.*,
				    (
                        select IF(LOCATE('SNOOZE: ',mr_unit_locations.mr_location) > 0, 1,0)
                        from mr_unit_locations 
                        where mr_unit_locations.maint_id = maint_requests.id and mr_unit_locations.deleted = 0
                        order by mr_unit_locations.linedate_added desc, mr_unit_locations.id desc
                        limit 1
                    ) as snoozing,
					(select users.username from users where users.id=maint_requests.user_id) as user_namer
						
				from maint_requests				
				where maint_requests.id='".sql_friendly($req_id)."'
				limit 1
			";
			
			//echo "<br>".$i.". Query:".$sql.".<br>";
			//if($i>0)		die('Testing Mode.');
			$data = simple_query($sql);
			$row = mysqli_fetch_array($data);
					
			$display_flagger=1;
						
			$e_type=$row['equip_type'];
			$e_select=$row['ref_id'];					
			$main_desc=$row['maint_desc'];
			$req_active=$row['active'];
			$schedule_date=$row['linedate_scheduled'];
			$completed_date=$row['linedate_completed'];
			$down_time=$row['down_time_hours'];
			$cost_est=$row['cost'];
			$odometer=$row['odometer_reading'];
			
			$recur_flag=$row['recur_flag'];
			$recur_days=$row['recur_days'];
			$recur_mileage=$row['recur_mileage'];
			
			$recur_ref=$row['recur_ref'];
			$urgent=$row['urgent'];
			
			$urgenter="";
			$recur_file="";
			if($recur_ref>0)		$recur_file="<a href='maint_recur.php?id=".$recur_ref."' target='_blank'>Edit</a>";
			if($recur_flag>0)		$recur_file="<span class='mrr_recur_styler'>Yes</span>";
			if($urgent>0)			$urgenter="<span style='color:#CC0000;'><b>!!!</b></span>";
									
			$equip_type=get_option_name_by_id($e_type);	
			$name=identify_truck_trailer($e_type , $e_select, 1);
			
			//use conditions to determine if this does meet additional criteria
			if(strtolower($equip_type)=="truck" && $e_select!=$_POST['truck_id'] && $_POST['truck_id'] > 0)
			{
				$display_flagger=0;	
			}
			elseif(strtolower($equip_type)=="trailer" && $_POST['truck_id'] > 0)
			{
				$display_flagger=0;		
			}
			if(strtolower($equip_type)=="trailer" && $e_select!=$_POST['trailer_id'] && $_POST['trailer_id'] > 0)
			{
				$display_flagger=0;	
			}
			elseif(strtolower($equip_type)=="truck" && $_POST['trailer_id'] > 0)
			{
				$display_flagger=0;		
			}
			
			if($display_flagger==1)
			{
				$tot_hours+=$down_time;
				$tot_cost+=$cost_est;
				$tot_reqs++;	
				
				$linker="";
				$link_namer="".$name."";
				
				if($recur_flag==1)
				{
					$linker="<a href='maint_recur.php?id=".$row['id']."' target='_blank'>".$main_desc."</a>";
				}
				else
				{
					$linker="<a href='maint.php?id=".$row['id']."' target='_blank'>".$main_desc."</a>";	
					
				}
				if( strtolower($equip_type)=="truck" && $e_select > 0 && substr_count($name,"[Deleted]")==0)
				{
					$link_namer="<a href='admin_trucks.php?id=".$e_select."' target='_blank'>".$name."</a>";
				}
				if( strtolower($equip_type)=="trailer" && $e_select > 0 && substr_count($name,"[Deleted]")==0)
				{
					$link_namer="<a href='admin_trailers.php?id=".$e_select."' target='_blank'>".$name."</a>";
				}
				
				$equip_local="";
				$equip_local=mrr_find_equip_current_location($e_type,$e_select);
                 
                $snooze="";
				if($row['snoozing'] > 0)       $snooze="<br><span style='color:#CC0000;'><b>SNOOZE is ON</b></span>";
				
				echo "
				<tr ".($mrr_use_styles > 0 ? "style='background-color:#".($tot_reqs % 2 == 1 ? 'dddddd' : 'eeeeee').";'" : "class='".($tot_reqs % 2 == 1 ? 'odd' : 'even')."'").">
					<td>".$urgenter."</td>
					<td width='400'>".$linker."".$snooze."</td>
					<td>$equip_type</td>
					<td nowrap>$link_namer</td>
					<td nowrap>".($schedule_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($schedule_date)): "")."</td>
					<td nowrap>".($completed_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($completed_date)): "") ."</td>
					<td>".$recur_file."</td>
					<td nowrap>".$row['user_namer']."</td>
					<td nowrap>".date("m/d/Y H:i", strtotime($row['linedate_added'])) ."</td>
					<td>".$equip_local."</td>
					<td align='right'>".number_format($down_time,2)."</td>
					<td align='right'>$".money_format('',$cost_est)."</td>
				</tr>
				";												 //"M j, Y"
				
				$linkerx=trim($linker);
				$linkerx=str_replace(chr(9)," ",$linkerx);
				$linkerx=str_replace(chr(10)," ",$linkerx);
				$linkerx=str_replace(chr(13)," ",$linkerx);
				$linkerx=str_replace("\n"," ",$linkerx);
				$linkerx=str_replace("\r"," ",$linkerx);
				
				$equip_localx=trim($equip_local);
				$equip_localx=str_replace(chr(9)," ",$equip_localx);
				$equip_localx=str_replace(chr(10)," ",$equip_localx);
				$equip_localx=str_replace(chr(13)," ",$equip_localx);
				$equip_localx=str_replace("\n"," ",$equip_localx);
				$equip_localx=str_replace("\r"," ",$equip_localx);
				
				$export_file .= "".strip_tags($urgenter)."".chr(9).
          			"\"".strip_tags($linkerx)."\"".chr(9).
          			"\"".strip_tags($equip_type)."\"".chr(9).
          			"\"".strip_tags($link_namer)."\"".chr(9).
          			"\"".($schedule_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($schedule_date)): "")."\"".chr(9).
          			"\"".($completed_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($completed_date)): "") ."\"".chr(9).
          			"\"".strip_tags($recur_file)."\"".chr(9).
          			"\"".strip_tags($row['user_namer'])."\"".chr(9).
          			"\"".date("m/d/Y H:i", strtotime($row['linedate_added'])) ."\"".chr(9).
          			"\"".strip_tags($equip_localx)."\"".chr(9).
          			"\"".number_format($down_time,2)."\"".chr(9).
          			"\"$".money_format('',$cost_est)."\"".chr(9);
				$export_file .= chr(13);	
				
				
				//now show line items....
				if($show_line_items)
				{
					$my_data=mrr_no_ajax_make_line_item_list($req_id);		//,$_POST['maintenance_desc']
					echo "
						<tr bgcolor='#ffffff'>
							<td colspan='12'>".$my_data."</td>
						</tr>
						<tr bgcolor='#ffffff'>
							<td colspan='12'><hr></td>
						</tr>";
				}
				
			}
			
			mysqli_free_result($data);	
			//if($i>=14)		die('Testing Mode.');		
		}
	
	
	//die('Testing Mode Pre-Done.');	
	
	
	if($show_line_items==0)
	{
		echo "
			<tr>
				<td colspan='12'><hr></td>
			</tr>";	
	}
	?>
	<tr>
		<td>&nbsp;</td>
		<td colspan='9'><?=number_format($tot_reqs)?> Request(s)</td>
		<td align='right'><?=number_format($tot_hours,2)?></td>
		<td align='right'>$<?=money_format('',$tot_cost)?></td>
	</tr>
	</table>
	<?
	$pdf = ob_get_contents();
	ob_end_clean();
	
	echo $pdf;
		
	
	$export_file .= "".chr(9).
		"".number_format($tot_reqs)." Request(s)".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"\"".number_format($tot_hours,2)."\"".chr(9).
		"\"$".money_format('',$tot_cost)."\"".chr(9);
	$export_file .= chr(13);	
/*	*/
	
	
	
	
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
	$used_excel_filename="";	
	$prefix="";
	
	//$use_excel=0;
	
	if($use_excel > 0) 
	{
        //for CSV file...replace commas
        //$export_file=str_replace(",",";",$export_file);
        $export_file=str_replace("&#039;","'",$export_file);
	    $export_file=str_replace(chr(9),",",$export_file);
	    
	    $fp = fopen(getcwd() . "/temp/$excel_filename", "w");
		fwrite($fp, $export_file); 
		fclose($fp);
		
		$prefix="<br><br><a href=\"http://trucking.conardlogistics.com/temp/".$excel_filename."\" target='_blank'>Click for Excel Version</a><br><br>";
		
		$used_excel_filename="http://trucking.conardlogistics.com/temp/".$excel_filename."";	
		
		echo $prefix;
	}
	
		
	
	//create record of this report...and use as the last one for special emails out for this report.
	$pdf=str_replace(" href="," name=",$pdf);
	$pdf.="<br><br><center>As of ".date("m/d/Y H:i:s",time())."</center>";
	$sql="
     	select *
     	from last_report_capture
     	where report_url='".sql_friendly($_SERVER['SCRIPT_NAME'])."' 
     		and report_query='".sql_friendly($_SERVER['QUERY_STRING'])."'
     ";
     $data=simple_query($sql);
     if($row=mysqli_fetch_array($data))
     {	//update the existing HTML...
     	$id=$row['id'];
     	$sqlu="
     		update last_report_capture set
     			report_html='".sql_friendly($pdf)."',
     			excel_filename='".$used_excel_filename."',
     			linedate=NOW()
     		where id='".sql_friendly($id)."'
     	";
    	 	simple_query($sqlu);    	 	
     }
     else
     {	//Report not present....add HTML
     	$sqlu="
     		insert into last_report_capture
     			(id,
     			linedate_added,
     			linedate,
     			report_url,
     			report_title,
     			report_html,
     			report_query,
     			excel_filename)
     		values
     			(NULL,
     			NOW(),
     			NOW(),
     			'".sql_friendly($_SERVER['SCRIPT_NAME'])."',
     			'Maintenance Request Report',
     			'".sql_friendly($pdf)."',
     			'".sql_friendly($_SERVER['QUERY_STRING'])."',
     			'".$used_excel_filename."')
     	";
    	 	simple_query($sqlu);
     }	
     mysqli_free_result($data);
     //die('Testing Mode Done.');
 ?>
<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
</script>
<? include('footer.php') ?>