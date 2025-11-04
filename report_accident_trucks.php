<?
$usetitle = "Report - Accident Trucks";
$use_title = "Report - Accident Trucks";
?>
<? include('header.php') ?>
<table class='' style='text-align:left;'>
<tr>
	<td valign='top'>		
<?

	if(isset($_GET['maintenance_id'])) 
	{
		$_POST['maintenance_id'] = $_GET['maintenance_id'];
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['current']))
	{
		$_POST['date_from']="11/01/2011";
		$_POST['date_to']=date("m/d/Y", time());
		
		$_POST['active_maint'] = 1;
		
		$_POST['build_report'] = 1;	
	}
	if(isset($_GET['completed']))
	{
		$_POST['date_from']="11/01/2011";
		$_POST['date_to']=date("m/d/Y", time());
		
		$_POST['closed_maint'] = 1;
		
		$_POST['build_report'] = 1;	
	}
	
	if(!isset($_POST['build_report'])) $_POST['active_maint'] = 1;
	
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}

	$rfilter = new report_filter();
	$rfilter->show_driver			= true;
	$rfilter->show_truck2 			= true;
	$rfilter->show_trailer 			= true;	
	$rfilter->active_maint 			= true;
	$rfilter->closed_maint			= true;
	//$rfilter->recurring_maint 		= true;
	$rfilter->down_time_hours 		= true;
	$rfilter->down_time_hours_to 		= true;
	$rfilter->maint_request_cost		= true;
	$rfilter->maint_request_cost_to	= true;
	$rfilter->accident_id			= true;
	$rfilter->maintenance_desc		= true;
	$rfilter->show_dispatch_id		= true;
	$rfilter->show_load_id			= true;	
	$rfilter->mrr_send_email_here		= true;
	$rfilter->show_font_size		= true;	
	//$rfilter->maint_category			= true;
	//$rfilter->maint_detail_items		= true;
	//$rfilter->maint_from_recur		= true;
	//$rfilter->maint_urgent			= true;
	
	$rfilter->show_filter();
	
	$show_line_items=0;			if(isset($_POST['maint_detail_items']))		$show_line_items=1;
	
	$sql="";
	
	$uuid = createuuid();
	$excel_filename = "accident_trucks_$uuid.xls";
	$export_file = "";
	$use_excel=1;
	
	$stylex=" style='font-weight:bold;'";
	$mrr_total_head = " style='font-weight:bold; margin:0 10px; width:1400px; text-align:left;'";
	$tablex=" border='1' cellpadding='1' cellspacing='1' width='1200'";
	$headerx=" style='background-color:#CCCCFF;'";	
		
 	if(isset($_POST['build_report']))
 	{ 
		$search_date_range = "
				and (
					(accident_date>= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' and accident_date<= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59')
					or
					(accident_date='0000-00-00 00:00:00')
				)
				";
		
		$columns="distinct accident_reports.id";
		$tables="accident_reports";	// left join maint_line_items on maint_line_items.ref_id=maint_requests.id and maint_line_items.deleted='0'";
		$conditions=" accident_reports.deleted='0'";
		$orders=" accident_reports.accident_date asc,accident_reports.accident_desc asc,accident_reports.id asc";
		
		$reactor=0;							if( isset($_POST['active_maint'] ))		$reactor=1;
		$reactor2=0;							if( isset($_POST['closed_maint'] ))		$reactor2=1;
		//$recurrer=0;							if( isset($_POST['recurring_maint'] ))		$recurrer=1;
		
		if($_POST['accident_id'] > 0 )			$conditions.=" and accident_reports.id='".sql_friendly($_POST['accident_id'])."'";	
		
		if($_POST['maint_request_cost'] > 0 )		$conditions.=" and accident_reports.accident_cost>='".sql_friendly($_POST['maint_request_cost'])."'";	
		if($_POST['maint_request_cost_to'] > 0 )	$conditions.=" and accident_reports.accident_cost<='".sql_friendly($_POST['maint_request_cost_to'])."'";	
		if($_POST['down_time_hours'] > 0 )			$conditions.=" and accident_reports.accident_downtime>='".sql_friendly($_POST['down_time_hours'])."'";		
		if($_POST['down_time_hours_to'] > 0 )		$conditions.=" and accident_reports.accident_downtime<='".sql_friendly($_POST['down_time_hours_to'])."'";	
		
		$multi_sel_trucks=$_POST['truck_id'];
		$multi_sel_count=count($multi_sel_trucks);
		
		if($multi_sel_count>1)
		{
			$conditions.=" and (";	
			for($x=0;$x < $multi_sel_count; $x++)
			{
				if($x==0)	$conditions.=" accident_reports.truck_id='".sql_friendly( $multi_sel_trucks[$x] )."'";
				else		$conditions.=" or accident_reports.truck_id='".sql_friendly( $multi_sel_trucks[$x] )."'";	
			}
			$conditions.=") ";
		}
		elseif($multi_sel_count==1)
		{
			if(	$multi_sel_trucks[0] > 0)	$conditions.=" and accident_reports.truck_id='".sql_friendly( $multi_sel_trucks[0] )."'";	
		}
		
		//if($_POST['truck_id'] > 0 )				$conditions.=" and accident_reports.truck_id='".sql_friendly($_POST['truck_id'])."'";		
		if($_POST['driver_id'] > 0 )				$conditions.=" and accident_reports.driver_id='".sql_friendly($_POST['driver_id'])."'";		
		if($_POST['trailer_id'] > 0 )				$conditions.=" and accident_reports.trailer_id='".sql_friendly($_POST['trailer_id'])."'";
		if($_POST['dispatch_id'] > 0 )			$conditions.=" and accident_reports.dispatch_id='".sql_friendly($_POST['dispatch_id'])."'";
		if($_POST['load_handler_id'] > 0 )			$conditions.=" and accident_reports.load_id='".sql_friendly($_POST['load_handler_id'])."'";
		
		if($reactor > 0)	
		{
			$conditions.=" and accident_reports.completed_date='0000-00-00'";
		}
		if($reactor2 > 0)	
		{
			$conditions.=" and accident_reports.completed_date!='0000-00-00'";
		}
			
		if($_POST['maintenance_desc']!="")	
		{
			$conditions.=" and (accident_reports.accident_desc LIKE '%".sql_friendly($_POST['maintenance_desc'])."%'";	
			//$conditions.=" or maint_line_items.lineitem_desc LIKE '%".sql_friendly($_POST['maintenance_desc'])."%')";
		}
		/*
		if($_POST['maint_category'] > 0)
		{
			$conditions.=" and maint_line_items.cat_id='".sql_friendly($_POST['maint_category'])."'";
		}		
		*/				
		$msql = "
			select ".$columns."			
			from ".$tables."				
			where ".$conditions."
				".$search_date_range."
			order by ".$orders."
		";	// limit 1
		
		$id_list[0]=0;			$cntr=0;
		$mdata = simple_query($msql);
		while($mrow = mysqli_fetch_array($mdata))
		{
			$id_list[$cntr]=$mrow['id'];
			$cntr++;
		}
		//mysql_free_result($mdata);
			
	?>
</td>
<td valign='top' width='1400'>
	<?
	ob_start();
	?>	
	<table <?=( $mrr_use_styles > 0 ? "".$mrr_total_head."" : " class='admin_menu2 font_display_section' style='margin:0 10px;width:1400px;text-align:left;'")?>>	
	<thead>
	<tr style='font-weight:bold'>
		<th>Accident Truck Report</th>
		<th nowrap>Date</th>
		<th nowrap>Filed</th>
		<th nowrap>Driver</th>
		<th nowrap>Truck</th>
		<th nowrap>Trailer</th>
		<th nowrap>Load</th>
		<th nowrap>Dispatch</th>
		<th align='right' nowrap>Hours Down</th>
		<th align='right' nowrap>Cost</th>
        <th align='right' title='How much Conard paid'><b>Conard</b></th>
        <th align='right' title='How much Insurance paid (after the deductible)'><b>Insurance</b></th>
	</tr>
	</thead>
	<tbody>
	<?
		$export_file .= "Accident Truck Report".chr(9).
			"".chr(9).
			"".chr(9).
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
		
		$export_file .= "Accident Truck Report".chr(9).
			"Date".chr(9).
			"Filed".chr(9).
			"Driver".chr(9).
			"Truck".chr(9).
			"Trailer".chr(9).
			"Load".chr(9).
			"Dispatch".chr(9).
			"Hours Down".chr(9).
			"Cost".chr(9).
            "Conard Paid".chr(9).
            "Insurance Paid".chr(9);
		$export_file .= chr(13);	
		
		$tot_conard=0.00;
		$tot_insurance=0.00;
		
		$tot_hours=0;
		$tot_cost=0;
		$tot_reqs=0;
		//take array of accident reports...show all items....
		for($i=0;$i<$cntr;$i++)
		{
			$req_id=$id_list[$i];
			
			$sql = "
				select *		
				from accident_reports				
				where id='".sql_friendly($req_id)."'
				limit 1
			";
			$data = simple_query($sql);
			$row = mysqli_fetch_array($data);
					
			$display_flagger=1;						
			$truck_id=$row['truck_id'];
			$trailer_id=$row['trailer_id'];
			$driver_id=$row['driver_id'];
			$load_id=$row['load_id'];
			$dispatch_id=$row['dispatch_id'];
								
			$main_desc=$row['accident_desc'];
			$req_active=$row['active'];
			$schedule_date=$row['accident_date'];
			$completed_date=$row['claim_date'];
			$down_time=$row['accident_downtime'];
			$cost_est=$row['accident_cost'];
            $ins_deduct=$row['accident_deductable'];
            
            $a_cost=0.00;
            $a_deduct=0.00;
            
            if($cost_est > $ins_deduct && $row['id']>=181)
            {
                 $a_cost=$ins_deduct;
                 $a_deduct=$cost_est - $ins_deduct;
            }
            elseif($row['id']>=181)
            {
                 $a_cost=$cost_est;
                 $a_deduct=0.00;
            }
            
            $tot_conard+=$a_cost;
            $tot_insurance+=$a_deduct;
            
			$display_flagger=1;
            
            /*
            $equip_type=get_option_name_by_id($e_type);
            $name=identify_truck_trailer($e_type , $e_select);
            
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
            */
			
			if($display_flagger==1)
			{
				$tot_hours+=$down_time;
				$tot_cost+=$cost_est;
				$tot_reqs++;	
				
				//$linker="";
				//$link_namer="".$name."";
				
				$linker="<a href='/accident_trucks.php?id=".$row['id']."' target='_blank'>".$main_desc."</a>";	
				
				//get truck
     			$sql2 = "
     				select name_truck				
     				from trucks
     				where deleted = 0
     					and id='".sql_friendly($truck_id)."' 
     				limit 1
     			";
     			$tdata = simple_query($sql2);
     			$trow = mysqli_fetch_array($tdata);
     			$tnamer="<a href='/admin_trucks.php?id=".$truck_id."' target='_blank'>".$trow['name_truck']."</a>";
     			
     			//get trailer
     			$sql2 = "
     				select trailer_name				
     				from trailers
     				where deleted = 0
     					and id='".sql_friendly($trailer_id)."' 
     				limit 1
     			";
     			$tdata = simple_query($sql2);
     			$trow = mysqli_fetch_array($tdata);
     			$rnamer="<a href='/admin_trailers.php?id=".$trailer_id."' target='_blank'>".$trow['trailer_name']."</a>";
     			
     			//get driver
     			$sql3 = "
     				select name_driver_first,name_driver_last				
     				from drivers
     				where deleted = 0
     					and id='".sql_friendly($driver_id)."' 
     				limit 1
     			";
     			$ddata = simple_query($sql3);
     			$drow = mysqli_fetch_array($ddata);
     			$dnamer="<a href='/admin_drivers.php?id=".$driver_id."' target='_blank'>".$drow['name_driver_first']." ".$drow['name_driver_last']."</a>";
				
				$loader=$load_id;
				$disper=$dispatch_id;
				if($load_id>0)
				{
					$loader="<a href='/manage_load.php?load_id=".$load_id."' target='_blank'>".$load_id."</a>";
					if($dispatch_id>0)
					{
						$disper="<a href='/add_entry_truck.php?load_id=".$load_id."&id=".$dispatch_id."' target='_blank'>".$dispatch_id."</a>";
					}
				}
				
				echo "
				<tr ".($mrr_use_styles > 0 ? "style='background-color:#".($tot_reqs % 2 == 1 ? 'dddddd' : 'eeeeee').";'" : "class='".($tot_reqs % 2 == 1 ? 'odd' : 'even')."'").">
					<td >$linker</td>
					<td nowrap>".($schedule_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($schedule_date)): "<span class='mrr_alert'><b>N/A</b></span>")."</td>
					<td nowrap>".($completed_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($completed_date)): "") ."</td>
					<td nowrap>$dnamer</td>
					<td nowrap>$tnamer</td>
					<td nowrap>$rnamer</td>
					<td nowrap>$loader</td>
					<td nowrap>$disper</td>
					<td nowrap align='right'>".number_format($down_time,2)."</td>
					<td nowrap align='right'>$".money_format('',$cost_est)."</td>
					<td nowrap align='right'>$".number_format($a_cost,2)."</td>
					<td nowrap align='right' title='Deductiable is/was $".$ins_deduct.".'>$".money_format('',$a_deduct)."</td>
				</tr>				
				";	
				
				
				$export_file .= "".$main_desc."".chr(9).
          			"".($schedule_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($schedule_date)): "")."N/A".chr(9).
          			"".($completed_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($completed_date)): "") ."".chr(9).
          			"".strip_tags($dnamer)."".chr(9).
          			"".strip_tags($tnamer)."".chr(9).
          			"".strip_tags($rnamer)."".chr(9).
          			"".strip_tags($loader)."".chr(9).
          			"".strip_tags($disper)."".chr(9).
          			"".number_format($down_time,2)."".chr(9).
          			"$".money_format('',$cost_est)."".chr(9).
                    "$".number_format($a_cost,2)."".chr(9).
                    "$".money_format('',$a_deduct)."".chr(9);
				$export_file .= chr(13);												 //"M j, Y"
				/*				
				//now show line items....
				if($show_line_items)
				{
					$my_data=mrr_no_ajax_make_line_item_list($req_id);		//,$_POST['maintenance_desc']
					echo "
						<tr bgcolor='#ffffff'>
							<td colspan='10'>".$my_data."</td>
						</tr>
						<tr bgcolor='#ffffff'>
							<td colspan='10'><hr></td>
						</tr>";
				}
				*/				
			}			
		}	
	
	?>
	</tbody>
	</table>
	<br><br>
	
	<div class='section_heading'
		<b><?=number_format($tot_reqs)?></b> Accident(s).  <br>
		<b><?=number_format($tot_hours,2)?></b> Hours Down.<br>
		<b>$<?=money_format('',$tot_cost)?></b> Total Cost.<br>
        <b>$<?=money_format('',$tot_conard)?></b> Total Paid by Conard.<br>
        <b>$<?=money_format('',$tot_insurance)?></b> Total Paid by Insurance (After Deductible).<br>
	</div>
	
	<br><br>
	<?
	
	$export_file .= "".number_format($tot_reqs)." Accident(s)".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".chr(9).
		"".number_format($tot_hours,2)."".chr(9).
		"$".money_format('',$tot_cost)."".chr(9).
        "$".money_format('',$tot_conard)."".chr(9).
        "$".money_format('',$tot_insurance)."".chr(9);
	$export_file .= chr(13);	
	
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
	
	$().ready(function() {		
		$('.tablesorter').tablesorter();
		
		mrr_set_labels_for_report_form();		
	});
	function mrr_set_labels_for_report_form()
	{
		$('#report_active_maint').html('Show Active Accidents');	
		$('#report_closed_maint').html('Show Completed Accidents');	
	}
	$('#date_from').datepicker();
	$('#date_to').datepicker();
</script>
<? include('footer.php') ?>