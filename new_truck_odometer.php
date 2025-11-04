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
          	<div class='section_heading'>NEW Truck Odometer Report</div>
          	Enter any date range for the time you would like to build the truck odometer report for. You may also filter by Aquired From column using the Leased From field...or leave blank for all trucks.<br>
          	<br><br>Calculated Odometer reading and date are either from tracking system or last odometer reading history. 
          	Values in <span class='alert'><b>red</b></span> are from manual history and should be investigated... 
          </div>
	</td>
	<td valign='top' width='400' align='right'>
		<?
          	if(!isset($_POST['date_from']))		$_POST['date_from']=date("m/01/Y");
          	$days_in_month = date("t", $_POST['date_from']);
          	if(!isset($_POST['date_to']))		$_POST['date_to']=date("m/".$days_in_month."/Y");
          	
          	$rfilter = new report_filter();
          	$rfilter->show_date_range 		= true;
          	//$rfilter->show_single_date 		= true;
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
<table<?=( $mrr_use_styles > 0 ? "".$tablex."" : " class='font_display_section'") ?> style='text-align:left;width:1200px;'>
<tr>
	<td>
<?
	if(!isset($_POST['report_leased_from']))	$_POST['report_leased_from']="";
	
	if(isset($_POST['build_report'])) {
		
		/*		
		$date_start = strtotime(date("m/1/Y", strtotime($_POST['report_date'])));
		$days_in_month = date("t", $date_start);
		$date_end = strtotime(date("m/".$days_in_month."/Y", $date_start));
		$date_odom=date("Y-m-d", strtotime($_POST['report_date']));
		*/
				
		$date_start = strtotime($_POST['date_from']);
		$days_in_month = date("t", $date_start);
		$date_end = strtotime($_POST['date_to']);
		$date_odom=date("Y-m-d", $date_start);
		
		
		
		$date_odom2=$date_odom;		//used for Truck Tracking section below...
		
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
		
		//(select name_truck from trucks t1 where t1.id=equipment_history.replacement_xref_id) as replace_truck_name,
		$sql = "
			select equipment_id,
				trucks.*,
				equipment_history.replacement_xref_id as replace_truck_id,
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
						and trucks.peoplenet_tracking = 0
						".$mrr_filter_leased."
					)
				or
					(
						trucks.active = 1 
						and '".date("m")."' = '".date("m", $date_start)."' 
						and trucks.deleted = 0
						and trucks.peoplenet_tracking = 0
						".$mrr_filter_leased."
					)
			order by trucks.name_truck, trucks.truck_make, trucks.truck_year, equipment_history.linedate_aquired desc
		";
		$data_trucks = simple_query($sql);
				
		echo "
			<form action='' method='post'>
			<input type='hidden' name='report_date' value='$_POST[report_date]'>
			<input type='hidden' name='build_report' value='1'>
			<table class='admin_menu2' style='width:1200px;'>
			<tr>
				<td colspan='13'><b>Trucks without GPS Tracking:  (Manual Odometer Readings Required)</b></td>
			</tr>
			<tr>
				<td width='100'><b>Truck Name</b></td>
				<td><b>Year</b></td>
				<td><b>Make</b></td>
				<td><b>Model</b></td>
				<td><b>Aquired From</b></td>	
								
				<td width='100' align='right'><b>Replaced Date</b></td>
				<td width='100' align='right'><b>Replaced Truck</b></td>
				<td width='100' align='right'><b>Start Date</b></td>	
				<td width='100' align='right'><b>Start Odom</b></td>
				<td width='100' align='right'><b>Odometer Date</b></td>
				<td width='100' align='right'><b>New Odometer</b> ". show_help('report_truck_odometer','Truck Odometer Mileage')."</td>
				<td width='100' align='right'><b>Miles</b></td>
				<td width='75'><b>&nbsp;</b></td>
			</tr>
		";
		$counter = 0;
		
		$show_cnt=0;
		$shown[0]=0;
		
		$miles_tot=0;
		$miles_gtot=0;
		
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
			
			$mrr_odom_reading=0;
			$mrr_odom_date="";
			
			$odom_reading=$row_odom['odometer'];	
			$mrr_manual_miles_flag=1;
			if(!isset($row_odom['odometer']))
			{
				$o2res=mrr_get_previous_equip_history_odometer(1,$row_truck['id'],date("Y-m-d", $date_start),date("Y-m-d", $date_end));	
				//$o2res['stamp'];
				$mrr_odom_date=$o2res['stamp_in'];
				//$o2res['stamp_out'];
				$mrr_odom_reading=$o2res['odom_in'];
				//$o2res['odom_out'];
			}
			else
			{
				$mrr_odom_reading=$row_odom['odometer'];
				$mrr_odom_date=date("m/d/Y",strtotime($row_odom['linedate']));
			}
			
			$replace_truck="";
			$replace_date="";
			if($row_truck['replace_truck_id'] > 0)
			{
				$sqlt = "
					select name_truck					
					from trucks
					where id = '".sql_friendly($row_truck['replace_truck_id'])."'
				";
				$datat = simple_query($sqlt);
				$rowt = mysqli_fetch_array($datat);	
				
				$replace_truck="<a href='admin_trucks.php?id=".$row_truck['replace_truck_id']."' target='_blank'>".trim($rowt['name_truck'])."</a>";	//
				$replace_date=date("m/d/Y",strtotime($row_truck['linedate_aquired']));
			}
			
			
			$mrr_value=$mrr_odom_reading;	
			
			/*
			$ores=mrr_get_previous_odometer_reading($row_truck['id'],$row_odom['linedate']);
			$prev_odom=$ores['odom'];
			$prev_date=$ores['stamp'];
			if($prev_date=="")
			{
				$o2res=mrr_get_previous_equip_history_odometer(1,$row_truck['id'],date("Y-m-d", $date_start),date("Y-m-d", $date_end));	
				//$o2res['stamp'];
				$prev_date=$o2res['stamp_in'];
				//$o2res['stamp_out'];
				$prev_odom=$o2res['odom_in'];
				//$o2res['odom_out'];
			}
			*/
			
			
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
     			
     			if($mrr_odom_reading=="" || $mrr_odom_reading==0)			$mrr_odom_date="";
     			     			
    				$miles_diff=$mrr_value - $mrr_odom_reading;
    				$tag3="";
    				$tag4="";
    				if($miles_diff < 0)	
    				{
    					$tag3="<span class='alert'><b>";
    					$tag4="</b></span>";	
    				}
    				
    				
    				$miles_tot+=$miles_diff;
    				
     			echo "
     				<tr>
     					<td>
     						<a href='admin_trucks.php?id=".$row_truck['id']."' target='_blank'><span id='truck_".$row_truck['id']."_name' name=''>".$row_truck['name_truck']."</span></a>
     						<input type='hidden' id='odometer_old_".$row_truck['id']."' name='odometer_old_".$row_truck['id']."' value='".$mrr_odom_reading."'>
     					</td>
     					<td>$row_truck[truck_year]</td>
     					<td>$row_truck[truck_make]</td>
     					<td>$row_truck[truck_model]</td>
     					<td>$row_truck[leased_from]</td>
     					<td align='right'>".$replace_date."</td>
     					<td align='right'>".$replace_truck."</td>
     					<td align='right'>".$mrr_odom_date."</td>
     					<td align='right'>".$mrr_odom_reading."</td>					
     					<td align='right'><input name='odometer_date_$row_truck[id]' id='odometer_date_$row_truck[id]' value='".($row_truck['linedate_odometer'] > 0 ? date("m/d/Y", strtotime($row_truck['linedate_odometer'])) : date("m/d/Y"))."' class='input_date'></td>
     					<td align='right'>
     						<input name='odometer_miles_$row_truck[id]' id='odometer_miles_$row_truck[id]' value='".$mrr_value."' class='input_medium' style='text-align:right' onBlur='mrr_check_odometer_readings(".$row_truck['id'].")'>
     					</td>
     					<td align='right'>".$tag3."".number_format($miles_diff,0)."".$tag4."</td>
     					<td>
     						".($counter % 5 == 1 ? "<input type='submit' value='Update'>" : "")."
     						<input type='hidden' name='id_array[]' value='$row_truck[id]'>
     					</td>
     				</tr>
     			";
			}
		}
		$miles_gtot+=$miles_tot;
		echo "
				<tr>
     					<td colspan='11'><b>".$counter." Trucks</b></td>
     					<td align='right'><b>".number_format($miles_tot,0)."</b></td>
     					<td><b>Miles</b></td>
     			</tr>
			</table>
			</form>
		";
		
		
		//Trucks with tracking are for display only and need no form input from users....
		
		echo "
			<div></div>
			<table class='admin_menu2' style='width:1200px; margin-top:25px;'>
			<tr>
				<td colspan='11'><b>Trucks with GPS Tracking:</b></td>
			</tr>
			<tr>
				<td width='100'><b>Truck Name</b></td>
				<td><b>Year</b></td>
				<td><b>Make</b></td>
				<td><b>Model</b></td>
				<td><b>Aquired From</b></td>	
				<td width='100' align='right'><b>Start Date</b></td>	
				<td width='100' align='right'><b>Start Odom</b></td>	
				<td width='100' align='right'><b>End Date</b></td>	
				<td width='100' align='right'><b>End Odom</b></td>	
				<td width='100' align='right'><b>Miles</b></td>
				<td width='75'><b>&nbsp;</b></td>	
			</tr>
		";
		
		$counter = 0;
		$miles_tot=0;
		
		$sql = "
			select trucks.*
			
			from trucks
			
			where trucks.deleted = 0
				and trucks.active = 1 	
				and trucks.peoplenet_tracking > 0
				".$mrr_filter_leased."		
				
			order by trucks.name_truck, trucks.truck_year, trucks.truck_make
		";
		$data_trucks = simple_query($sql);
		while($row_truck = mysqli_fetch_array($data_trucks)) 
		{
			$counter++;
					
			$found=0;			
			for($z=0;$z < $show_cnt;$z++)
			{
				if($row_truck['id'] == $shown[ $z ] )		$found=1;
			}
			if($found==0)
			{	//only show the truck once...not once for each equipment line.
     			$shown[ $show_cnt ]=$row_truck['id'];
     			$show_cnt++;
     			
     			//find odometer readings for the month...     			
     			$tres=mrr_new_truck_odometer_finder($row_truck['id'],$date_odom2);
				$prev_date=$tres['date_start'];
				$prev_odom=$tres['odom_start'];
	
				$mrr_odom_date=$tres['date_end'];
				$mrr_odom_reading=$tres['odom_end'];
     			
     			//if there is an offset, use this on both readings so the difference is preserved.
     			$offset_from_pn=$row_truck['pn_odometer_offset'];     			
     			if($offset_from_pn!=0)
     			{	//$offset_from_pn may be negative or positive...depends on truck and PN unit settings...
     				$prev_odom=$prev_odom + $offset_from_pn;
     				$mrr_odom_reading=$mrr_odom_reading + $offset_from_pn;	
     			}	
     			
     			if($mrr_odom_reading=="" || $mrr_odom_reading==0)			$mrr_odom_date="";
     			if($prev_odom=="" || $prev_odom==0)					$prev_odom="";
     			     			
    				$miles_diff=$mrr_odom_reading - $prev_odom;
    				$tag1="";
    				$tag2="";
    				if($miles_diff < 0)	
    				{
    					$tag1="<span class='alert'><b>";
    					$tag2="</b></span>";	
    				}
    				    				
    				$miles_tot+=$miles_diff;
    				
     			echo "
     				<tr>
     					<td><a href='admin_trucks.php?id=".$row_truck['id']."' target='_blank'>".$row_truck['name_truck']."</a></td>
     					<td>$row_truck[truck_year]</td>
     					<td>$row_truck[truck_make]</td>
     					<td>$row_truck[truck_model]</td>
     					<td>$row_truck[leased_from]</td>
     					<td align='right'>".$prev_date."</td>
     					<td align='right'>".$prev_odom."</td>
     					<td align='right'>".$mrr_odom_date."</td>
     					<td align='right'>".$mrr_odom_reading."</td>	
     					<td align='right'>".$tag1."".number_format($miles_diff,0)."".$tag2."</td>
     					<td><b>&nbsp;</b></td>	
     				</tr>
     			";
			}
		}
		
		echo "
				<tr>
     					<td colspan='9'><b>".$counter." Trucks</b></td>
     					<td align='right'><b>".number_format($miles_tot,0)."</b></td>
     					<td><b>Miles</b></td>	
     			</tr>
     			
     			<tr>
     					<td colspan='11'><hr></td>	
     			</tr>     			
     			<tr>
     					<td colspan='9'><b>All Trucks</b></td>
     					<td align='right'><b>".number_format($miles_gtot,0)."</b></td>
     					<td><b>Miles</b></td>	
     			</tr>
     			
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
     		$Subject="Truck Odemeter Report";
     		//if(isset($use_title))			$Subject=$use_title;
     		//elseif(isset($usetitle))		$Subject=$usetitle;
     		
     		$pdf=str_replace(" href="," name=",$pdf);
     		//$pdf=str_replace("</a>","",$pdf);
     			
     		$sentit=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject,$pdf,$pdf);
     		
     		$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
     		echo "<br><br><b>This report has ".$sent_msg." to '".$_POST['mrr_email_addr_name']."' at E-Mail address '".$_POST['mrr_email_addr']."'.</b><br><br>";
     		
     		$sentit=mrr_trucking_sendMail('dconard@cconardtransportation.com',"Dale Conard",$From,$user_name,'','',$Subject,$pdf,$pdf);
     		//$sentit=mrr_trucking_sendMail('jgriffith@conardtransportation.com',"James Griffith",$From,$user_name,'','',$Subject,$pdf,$pdf);
     		//$sentit=mrr_trucking_sendMail('amassar@conardtransportation.com',"Anthony Massar",$From,$user_name,'','',$Subject,$pdf,$pdf);     		
     	}
?>
	<? } ?>
<?
function mrr_get_previous_equip_history_odometer($equip_type_id,$equip_id,$date_start,$date_end)
{	//date should come in in MySQL standard format "Y-m-d H:i:s"...preferably from next odometer reading in same table.
	$linedate="";
	$linedate_in="";
	$linedate_out="";
	$odometer_in=0;
	$odometer_out=0;
	
	$sql = "
		select *
		
		from equipment_history
		where equipment_type_id = '".sql_friendly($equip_type_id)."'
			and equipment_id = '".sql_friendly($equip_id)."'
			and deleted = 0
			and linedate_aquired <= '".sql_friendly($date_end)."'
			and (
					linedate_returned = 0
					or linedate_returned >= '".date("Y-m-d", $date_end)."'
				)			
		order by linedate_added desc
		limit 1
	";
	$data_odom = simple_query($sql);
	if($row_odom = mysqli_fetch_array($data_odom))
	{
		$linedate=date("m/d/Y",strtotime($row_odom['linedate_added']));
		$linedate_in="";		if($row_odom['linedate_aquired'])		$linedate_in=date("m/d/Y",strtotime($row_odom['linedate_aquired']));
		$linedate_out="";		if($row_odom['linedate_returned'])		$linedate_in=date("m/d/Y",strtotime($row_odom['linedate_returned']));
		$odometer_in=$row_odom['miles_pickup'];
		$odometer_out=$row_odom['miles_dropoff'];
	}
	
	$res['stamp']=$linedate;
	$res['stamp_in']=$linedate_in;
	$res['stamp_out']=$linedate_out;
	$res['odom_in']=$odometer_in;
	$res['odom_out']=$odometer_out;
	return $res;
}
function mrr_get_previous_odometer_reading($truck_id,$dater)
{	//date should come in in MySQL standard format "Y-m-d H:i:s"...preferably from next odometer reading in same table.
	$linedate="";
	$odometer=0;
	
	$sql = "
		select *
		
		from trucks_odometer
		where truck_id = '".sql_friendly($truck_id)."'
			and deleted = 0
			and linedate < '".sql_friendly($dater)."'
		order by linedate desc
		limit 1
	";
	$data_odom = simple_query($sql);
	if($row_odom = mysqli_fetch_array($data_odom))
	{
		$linedate=date("m/d/Y",strtotime($row_odom['linedate']));
		$odometer=$row_odom['odometer'];	
	}
	
	$res['stamp']=$linedate;
	$res['odom']=$odometer;
	return $res;
}	
?>	
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