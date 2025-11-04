<? include('header.php') ?>
<? if(!isset($_GET['print']) && !isset($_POST['print'])) { ?>
	<?
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}
	?>
<table border='0' style='text-align:left;width:800px;'>
<tr>
	<td valign='top'>
          <div style='text-align:left;margin:10px;'>
          	<div class='section_heading'>Truck Odometer Report</div>
          	Enter any date for the month you would like to build the truck odometer report for. You may also filter by Aquired From column using the Leased From field...or leave blank for all trucks.<br>
          	For example, if you wanted <?=date("M, Y")?>, select any date during that month and the system will automatically generate the full month report.
          	<br><br>Calculated Odometer reading and date are either from tracking system or last odometer reading history. 
          	Values in <span class='alert'><b>red</b></span> are from manual history and should be investigated... 
          </div>
	</td>
	<td valign='top' width='200' align='right'>
		<?
          	$rfilter = new report_filter();
          	$rfilter->show_date_range 		= false;
          	$rfilter->show_single_date 		= true;
          	$rfilter->show_leased_from		= true;
          	$rfilter->show_font_size			= true;
          	$rfilter->mrr_send_email_here		= true;
          	$rfilter->show_filter();
          ?>
	</td>
</tr>
</table>	
<? } ?>
<?
ob_start();		
$stylex=" style='font-weight:bold;'";
$mrr_total_head = " style='font-weight:bold; width:1000px; text-align:right;'";
$tablex=" border='1' cellpadding='1' cellspacing='1' width='1200'";
$headerx=" style='background-color:#CCCCFF;'";
?>
<br>
<table<?=( $mrr_use_styles > 0 ? "".$tablex."" : " class='font_display_section'") ?> style='text-align:left;width:1000px;'>
<tr>
	<td>
<?
	if(!isset($_POST['report_leased_from']))	$_POST['report_leased_from']="";
	
	if(isset($_POST['build_report'])) {
		$date_start = strtotime(date("m/1/Y", strtotime($_POST['report_date'])));
		$days_in_month = date("t", $date_start);
		$date_end = strtotime(date("m/".$days_in_month."/Y", $date_start));
		$date_odom=date("Y-m-d", strtotime($_POST['report_date']));
		
		$mrr_filter_leased="";
		if(trim($_POST['report_leased_from'])!="")
		{
			
			$mrr_filter_leased=" and trucks.leased_from like '%".sql_friendly($_POST['report_leased_from'])."%' ";
		}
		
		
		if(isset($_POST['id_array'])) {
			foreach($_POST['id_array'] as $id) {
				$sql = "
					delete from trucks_odometer
					where truck_id = '".sql_friendly($id)."'
						and linedate between '".date("Y-m-d", $date_start)."' and '".date("Y-m-d", $date_end)."'
				";
				simple_query($sql);
				
				$miles = money_strip($_POST['odometer_miles_'.$id]);
				if(is_numeric($miles) && $miles > 0) {
					$sql = "
						insert into trucks_odometer
							(linedate_added,
							linedate,
							odometer,
							truck_id)
							
						values (now(),
							'".date("Y-m-d", strtotime($_POST['odometer_date_'.$id]))."',
							'$miles',
							'".sql_friendly($id)."')
					";
					simple_query($sql);
				}
			}
			
		}		
		
		// get the number of trucks that were active for that date range
		$sql = "
			select equipment_id,
				trucks.*,
				equipment_history.linedate_aquired,
				equipment_history.linedate_returned,
				equipment_history.equipment_value,
				trucks_odometer.odometer,
				trucks_odometer.linedate as linedate_odometer
			
			from trucks
				left join equipment_history on trucks.id = equipment_history.equipment_id and '".date("m")."' <> '".date("m", $date_start)."'

				left join trucks_odometer on trucks_odometer.truck_id = trucks.id and linedate between '".date("Y-m-d", $date_start)."' and '".date("Y-m-d", $date_end)."'
			where 
					(
						equipment_history.deleted = 0
						and equipment_type_id = 1
						and equipment_history.linedate_aquired <= '".date("Y-m-d", $date_end)."'
						and (
							equipment_history.linedate_returned = 0
							or equipment_history.linedate_returned >= '".date("Y-m-d", $date_end)."'
							)
						and equipment_history.deleted = 0
						and trucks.active = 1 
						and trucks.deleted = 0
						".$mrr_filter_leased."
					)
				or
					(
						trucks.active = 1 
						and '".date("m")."' = '".date("m", $date_start)."' 
						and trucks.deleted = 0
						".$mrr_filter_leased."
					)
			order by trucks.truck_year, trucks.truck_make, trucks.name_truck
		";
		$data_trucks = simple_query($sql);
		
		echo "
			<form action='' method='post'>
			<input type='hidden' name='report_date' value='$_POST[report_date]'>
			<input type='hidden' name='build_report' value='1'>
			<table class='admin_menu2' style='width:800px'>
			<tr>
				<td><b>Truck Name</b></td>
				<td><b>Year</b></td>
				<td><b>Make</b></td>
				<td><b>Model</b></td>
				<td><b>Aquired From</b></td>				
				<td><b>Cal. Date</b></td>
				<td align='right'><b>Cal. Odom</b></td>
				<td><b>Odometer Date</b></td>
				<td align='right'><b>Miles</b> ". show_help('report_truck_odometer','Truck Odometer Mileage')."</td>
			</tr>
		";
		$counter = 0;
		
		$show_cnt=0;
		$shown[0]=0;
		
		while($row_truck = mysqli_fetch_array($data_trucks)) {
			$counter++;
			
			$offset_from_pn=$row_truck['pn_odometer_offset'];
			
			$sql = "
				select *
				
				from trucks_odometer
				where truck_id = '".sql_friendly($row_truck['id'])."'
					and deleted = 0
				order by linedate desc
				limit 1
			";
			$data_odom = simple_query($sql);
			$row_odom = mysqli_fetch_array($data_odom);
			$odom_reading=$row_odom['odometer'];	
			
			$tag1="<span class='alert'><b>";
			$tag2="</b></span>";
			$mrr_manual_miles_flag=1;
			$mrr_odom_reading=$row_odom['odometer'];
			$mrr_odom_date=date("m/d/Y",strtotime($row_odom['linedate']));
			
			if($row_truck['peoplenet_tracking'] > 0)
			{
				$mrr_odom=mrr_peoplenet_odometer_reading($row_truck['id'],$date_odom);	
				$tag1="";
				$tag2="";
				$mrr_manual_miles_flag=0;
				$mrr_odom_reading=trim($mrr_odom['odometer']);
				$mrr_odom_date=$mrr_odom['odom_date'];
				
				$mrr_odom_reading=(int)$mrr_odom_reading;
				if($mrr_odom_reading == 0)
				{	//check archive from removed truck_tracking data
					$sql2 = "
						select * 
						from ".mrr_find_log_database_name()."truck_tracking_odometer
						
						where truck_id = '".sql_friendly($row_truck['id'])."'
							and linedate='".sql_friendly($date_odom)."'
						order by id desc
						limit 1
					";
					$data2 = simple_query($sql2);	
					if($row2 = mysqli_fetch_array($data2))
					{
						$mrr_odom_reading=$row2['odometer'];
						$mrr_odom_date=date("m/d/Y",strtotime($row2['linedate']));	
					}
					
						
				}
			}
			
			$mrr_value=$row_truck['odometer'];
			if(($row_truck['odometer']==0 || trim($row_truck['odometer'])=="") && $mrr_manual_miles_flag==0)
			{
				$mrr_value=$mrr_odom_reading;	
			}
			
			
			$found=0;			
			for($z=0;$z < $show_cnt;$z++)
			{
				if($row_truck['id'] == $shown[ $z ] )		$found=1;
			}
			if($found==0)
			{	//only show the truck once...not once for each equipment line.
     			$shown[ $show_cnt ]=$row_truck['id'];
     			$show_cnt++;
     			     			
     			if($offset_from_pn!=0)
     			{	//$offset_from_pn may be negative or positive...depends on truck and PN unit settings...
     				$mrr_odom_reading=$mrr_odom_reading + $offset_from_pn;	
     				$mrr_value=$mrr_value + $offset_from_pn;
     			}	     			
    			
     			echo "
     				<tr>
     					<td>
     						<a href='admin_trucks.php?id=".$row_truck['id']."' target='_blank'><span id='truck_".$row_truck['id']."_name' name=''>".$row_truck['name_truck']."</span></a>
     						<input type='hidden' id='odometer_old_".$row_truck['id']."' name='odometer_old_".$row_truck['id']."' value='".$row_odom['odometer']."'>
     					</td>
     					<td>$row_truck[truck_year]</td>
     					<td>$row_truck[truck_make]</td>
     					<td>$row_truck[truck_model]</td>
     					<td>$row_truck[leased_from]</td>
     					<td>".$tag1."".$mrr_odom_date."".$tag2."</td>
     					<td align='right'>".$tag1."".$mrr_odom_reading."".$tag2."</td>					
     					<td><input name='odometer_date_$row_truck[id]' id='odometer_date_$row_truck[id]' value='".($row_truck['linedate_odometer'] > 0 ? date("m/d/Y", strtotime($row_truck['linedate_odometer'])) : date("m/d/Y"))."' class='input_date'></td>
     					<td align='right'>
     						<input name='odometer_miles_$row_truck[id]' id='odometer_miles_$row_truck[id]' value='".$mrr_value."' class='input_medium' style='text-align:right' onBlur='mrr_check_odometer_readings(".$row_truck['id'].")'>
     					</td>
     					<td>
     						".($counter % 5 == 1 ? "<input type='submit' value='Update'>" : "")."
     						<input type='hidden' name='id_array[]' value='$row_truck[id]'>
     					</td>
     				</tr>
     			";
			}
		}
		echo "
			</table>
			</form>
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
	
	function mrr_check_odometer_readings(id)
	{
		var myid=id;
		var truck_name= $('#truck_'+myid+'_name').html();
		var old_odom= $('#odometer_old_'+myid).val();
		var new_odom= $('#odometer_miles_'+myid).val();
		if(new_odom < old_odom && new_odom !=0 && new_odom!='' )
		{
			prompt_txt = "<table>";	
			prompt_txt += "<tr><td colspan='2'>Please enter a valid odometer reading for truck '"+truck_name+"'.</td></tr>";
			prompt_txt += "<tr><td colspan='2'>The last odometer reading for this truck  was '"+old_odom+"'.</td></tr></table>";
			
			$.prompt(prompt_txt);
			$('#odometer_miles_'+myid).val('');
			$('#odometer_miles_'+myid).focus();
		}
	}
</script>
<? include('footer.php') ?>