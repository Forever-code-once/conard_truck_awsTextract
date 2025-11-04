<? include('header.php') ?>
<? if(!isset($_GET['print']) && !isset($_POST['print'])) { ?>
	<?
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}
	?>

    	<div class='section_heading'>Owner Operator Truck Report</div>
		<?
          	$rfilter = new report_filter();
          	$rfilter->show_date_range 		= false;
          	//$rfilter->show_single_date 		= true;
          	//$rfilter->show_leased_from		= true;
          	$rfilter->show_font_size			= true;
          	$rfilter->mrr_send_email_here		= true;
          	$rfilter->show_filter();
          	
          	$_POST['build_report']=1;
          ?>
	
<? } ?>
<?
ob_start();		
$stylex=" style='font-weight:bold;'";
$mrr_total_head = " style='font-weight:bold; width:1000px; text-align:right;'";
$tablex=" border='1' cellpadding='1' cellspacing='1' width='1200'";
$headerx=" style='background-color:#CCCCFF;'";
?>
<br>
<table<?=( $mrr_use_styles > 0 ? "".$tablex."" : " class='font_display_section'") ?> style='text-align:left;width:1800px;'>
<tr>
	<td>
<?	
	if(isset($_POST['build_report'])) 
	{		
		// get the number of trucks that were active for that date range
		$sql = "
			select trucks.*
			from trucks
			
			where trucks.deleted = 0 
				and trucks.active>0
				and trucks.owner_operated>0
			order by trucks.name_truck asc
		";
		$data = simple_query($sql);
		
		echo "
			<table class='admin_menu2 tablesorter' style='width:1800px;'>
			<thead>
			<tr>
				<th>Truck</th>
				<th>Year</th>
				<th>Make</th>
				<th>Model</th>
				<th>VIN</th>
				<th>Plate</th>
				<th>Cab</th>
				<th>Tires</th>
				<th>Gallons</th>
				<th>LeasedFrom</th>
				<th>Prepass</th>
				<th>Camera</th>
				<th>Unit</th>
				<th>DSN</th>
				<th>DVR</th>
				<th>Driver</th>
				<th>O.O.</th>
				<th>Starts</th>
				<th>Expires</th>
				<th>A.C.C.</th>
				<th>Starts</th>
				<th>Expires</th>
			</tr>
			</thead>
			<tbody>
		";
		$counter = 0;
		
		$show_cnt=0;
		$shown[0]=0;
		
		while($row= mysqli_fetch_array($data)) 
		{
			$counter++;
						
			$tag1="<span class='alert'><b>";
			$tag2="</b></span>";
			
			$tag1="";
			$tag2="";
			
			/*
				monthly_cost = '".sql_friendly(money_strip($_POST['monthly_cost']))."',				
				cab_type = '".sql_friendly($_POST['cab_type'])."',				
				rental = '".(isset($_POST['rental']) ? '1' : '0')."',
				
				company_owned = '".(isset($_POST['company_owned']) ? '1' : '0') ."',
				company_admin_vehicle = '".(isset($_POST['company_admin_vehicle']) ? '1' : '0') ."',
				
				in_the_shop = '".(isset($_POST['in_the_shop']) ? '1' : '0')."',
				in_body_shop = '".(isset($_POST['in_body_shop']) ? '1' : '0')."',
				hold_for_driver = '".(isset($_POST['hold_for_driver']) ? '1' : '0')."',				
				
				own_op_ins_number='".sql_friendly($_POST['own_op_ins_number'])."',				
				
				in_shop_note	= '".sql_friendly(trim($_POST['in_shop_note']))."',
				in_body_note	= '".sql_friendly(trim($_POST['in_body_note']))."',
				on_hold_note	= '".sql_friendly(trim($_POST['on_hold_note']))."',
				
				pm_inspection_note='".sql_friendly(trim($_POST['pm_inspection_note']))."',
				".(trim($_POST['pm_inspection_date'])!=""  ? "pm_inspection_date='".date("Y-m-d",strtotime($_POST['pm_inspection_date']))." 00:00:00'," :"")."
				".(trim($_POST['fd_inspection_date'])!=""  ? "fd_inspection_date='".date("Y-m-d",strtotime($_POST['fd_inspection_date']))." 00:00:00'," :"")."
				
				pm_miles_interval='".sql_friendly(money_strip($_POST['pm_miles_interval']))."',
				pm_miles_last_oil='".sql_friendly(money_strip($_POST['pm_miles_last_oil']))."',
				pm_miles_last_date='".(trim($_POST['pm_miles_last_date'])!=""  ? "".date("Y-m-d",strtotime($_POST['pm_miles_last_date']))." 00:00:00" :"0000-00-00 00:00:00")."',
				
				use_pm_oil_report='".(isset($_POST['use_pm_oil_report']) ? 1 : 0)."',
				
				repairs_pending='".(isset($_POST['repairs_pending']) ? '1' : '0') ."',
				repairs_pending_list='".sql_friendly(trim($_POST['repairs_pending_list']))."',
				
				repairs_pending_date_opened = '".(trim($_POST['repairs_pending_date_opened'])!=""  ? "".date("Y-m-d",strtotime($_POST['repairs_pending_date_opened']))." 00:00:00" :"0000-00-00 00:00:00")."',
				repairs_pending_date_inspect = '".(trim($_POST['repairs_pending_date_inspect'])!=""  ? "".date("Y-m-d",strtotime($_POST['repairs_pending_date_inspect']))." 00:00:00" :"0000-00-00 00:00:00")."',
				repairs_pending_date_repair = '".(trim($_POST['repairs_pending_date_repair'])!=""  ? "".date("Y-m-d",strtotime($_POST['repairs_pending_date_repair']))." 00:00:00" :"0000-00-00 00:00:00")."',
				repairs_pending_date_closed = '".(trim($_POST['repairs_pending_date_closed'])!=""  ? "".date("Y-m-d",strtotime($_POST['repairs_pending_date_closed']))." 00:00:00" :"0000-00-00 00:00:00")."',
				repairs_pending_made='".(isset($_POST['repairs_pending_made']) ? '1' : '0') ."',
				repairs_pending_internal='".(isset($_POST['repairs_pending_internal']) ? '1' : '0') ."',
				
				camera_installed = '".(isset($_POST['camera_installed']) ? '1' : '0')."',
				fubar_truck = '".(isset($_POST['fubar_truck']) ? '1' : '0')."',
				automatic_transmission = '".(isset($_POST['automatic_transmission']) ? '1' : '0')."',
				no_insurance = '0',
				insurance_exclude = '".(isset($_POST['insurance_exclude']) ? '1' : '0')."',
				active_cnt_exclude = '".(isset($_POST['active_cnt_exclude']) ? '1' : '0')."',
				
				apu_value = '".sql_friendly(money_strip($_POST['apu_value']))."',
				pn_odometer_offset = '".sql_friendly(money_strip($_POST['pn_odometer_offset']))."'	
			*/
			
			$driver_id=0;
			$driver_name="";
			
			$sqld="select id,name_driver_last,name_driver_first from drivers where active>0 and deleted=0 and attached_truck_id='".sql_friendly($row['id'])."'";
			$datad = simple_query($sqld);
			if($rowd = mysqli_fetch_array($datad))
			{	
				$driver_id=$rowd['id'];
				$driver_name=trim($rowd['name_driver_first']." ".$rowd['name_driver_last']);
			}
			
			echo "
				<tr>
					<td valign='top'><a href='admin_trucks.php?id=".$row['id']."' target='_blank'>".$row['name_truck']."</a></td>
					<td valign='top'>$row[truck_year]</td>
					<td valign='top'>$row[truck_make]</td>
					<td valign='top'>$row[truck_model]</td>
					<td valign='top'>$row[vin]</td>
					<td valign='top'>$row[license_plate_no]</td>
					<td valign='top'>".option_value_text($row['cab_type'],1)."</td>
					<td valign='top'>$row[tire_size]</td>
					<td valign='top'>$row[gallon_size]</td>
					<td valign='top'>$row[leased_from]</td>
					<td valign='top'>$row[prepass]</td>
					<td valign='top'>".($row['camera_installed'] > 0 ? "Yes" : "No")."</td>
					<td valign='top'>".($row['peoplenet_tracking'] > 0 ? "".$row['apu_number']."" : "N/A")."</td>
					<td valign='top'>".($row['peoplenet_tracking'] > 0 ? "".$row['apu_serial']."" : "N/A")."</td>
					<td valign='top'>$row[dvr_serial]</td>	
					<td valign='top'>".($driver_id > 0 ? "<a href='admin_drivers.php?id=".$driver_id."' target='_blank'>".$driver_name."</a>" : "N/A")."</td>				
					<td valign='top'>".($row['own_op_ins_flag'] > 0 ? "Yes" : "No")."</td>
					<td valign='top'>".($row['linedate_own_op_ins'] !="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($row['linedate_own_op_ins']))."" : "N/A")."</td>
					<td valign='top'>".($row['linedate_own_op_ins_exp'] !="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($row['linedate_own_op_ins_exp']))."" : "N/A")."</td>
					<td valign='top'>".($row['own_op_acc_ins_flag'] > 0 ? "Yes" : "No")."</td>
					<td valign='top'>".($row['linedate_own_op_acc_ins'] !="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($row['linedate_own_op_acc_ins']))."" : "N/A")."</td>
					<td valign='top'>".($row['linedate_own_op_acc_ins_exp'] !="0000-00-00 00:00:00" ? "".date("m/d/Y",strtotime($row['linedate_own_op_acc_ins_exp']))."" : "N/A")."</td>					
				</tr>
			";	//<td>".$tag1."".$tag2."</td>
			
		}
		echo "
			</tbody>
			</table>
		";
		?>
	</td>
</tr>
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
     		elseif(isset($usetitle))			$Subject=$use_title;
     		
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
<? } ?>
	
<script type='text/javascript'>
	$('.input_date').datepicker();
	
	$('.tablesorter').tablesorter();
</script>
<? include('footer.php') ?>