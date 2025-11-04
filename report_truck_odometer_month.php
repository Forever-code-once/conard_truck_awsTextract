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
          	<div class='section_heading'>Truck Odometer Month Report</div>
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
		
		$date_start = date("Y-m-01", strtotime($_POST['report_date']));
		$days_in_month = date("t", strtotime($date_start));
		$date_end = date("Y-m-".$days_in_month."", strtotime($date_start));
		
		
		$date_odom=date("Y-m-d", strtotime($_POST['report_date']));
		
		$mrr_filter_leased="";
		if(trim($_POST['report_leased_from'])!="")
		{
			
			$mrr_filter_leased=" and trucks.leased_from like '%".sql_friendly($_POST['report_leased_from'])."%' ";
		}
		
		
		$sql="
			select trucks.*
			from trucks
			where deleted=0 
				and active=1
				".$mrr_filter_leased."
			order by name_truck asc,
				id asc
		";
				
		$data_trucks = simple_query($sql);
		echo "
			<form action='' method='post'>
			<input type='hidden' name='report_date' value='$_POST[report_date]'>
			<input type='hidden' name='build_report' value='1'>
			<table class='admin_menu2' style='width:800px'>
			<tr>
				<td nowrap><b>Truck Name</b></td>
				<td nowrap><b>Year</b></td>
				<td nowrap><b>Make</b></td>
				<td nowrap><b>Model</b></td>
				<td nowrap><b>Aquired From</b></td>				
				<td nowrap><b>Mode</b></td>	
				<td nowrap><b>Current Date</b></td>	
				<td nowrap align='right'><b>Odometer</b></td>	
				<td nowrap><b> Mode</b></td>
				<td nowrap><b>Start Date</b></td>	
				<td nowrap align='right'><b>Odometer</b></td>	
				<td nowrap align='right'><b>Month Miles</b></td>	
			</tr>
		";
		$counter = 0;
		
		//$show_cnt=0;
		//$shown[0]=0;
		$tag1="<span style='color:#999999;'>";			$tag2="</span>";
		$tag3="<span style='color:#000000;'><b>";		$tag4="</b></span>";
		while($row_truck = mysqli_fetch_array($data_trucks)) 
		{
				$counter++;
				
				$offset_from_pn=$row_truck['pn_odometer_offset'];
								
				$mrr_res['first_odometer']=0;
				$mrr_res['first_date']="";
				$mrr_res['last_odometer']=0;
				$mrr_res['last_date']="";
				$mrr_res['difference']=0;
				
				$mrr_res['mode1']="";
				$mrr_res['mode2']="";
				
				$mrr_res['sql']="";
				
				$mrr_res=mrr_get_first_and_last_reading_difference_for_odometer_month($row_truck['id'],$date_start, $date_end);
				
				if($offset_from_pn!=0)
     			{	//$offset_from_pn may be negative or positive...depends on truck and PN unit settings...
     				$mrr_res['first_odometer']=$mrr_res['first_odometer'] + $offset_from_pn;	
     				$mrr_res['last_odometer']=$mrr_res['last_odometer'] + $offset_from_pn;
     				
     				$mrr_res['difference']=$mrr_res['last_odometer'] - $mrr_res['first_odometer'];
     				
     				//$mrr_res['difference']=$mrr_res['difference'] + $offset_from_pn;
     				$mrr_value=$mrr_value + $offset_from_pn;
     			}
							
				echo "
     				<tr>
     					<td><a href='admin_trucks.php?id=".$row_truck['id']."' target='_blank'><span id='truck_".$row_truck['id']."_name' name=''>".$row_truck['name_truck']."</span></a></td>
     					<td>".$row_truck['truck_year']."</td>
     					<td>".$row_truck['truck_make']."</td>
     					<td>".$row_truck['truck_model']."</td>
     					<td>".$row_truck['leased_from']."</td>
     					<td>".$mrr_res['mode1']."</td>
     					<td>".$tag1."".$mrr_res['first_date']."".$tag2."</td>
     					<td align='right'>".$tag3."".number_format($mrr_res['first_odometer'],2)."".$tag4."</td>     					
     					<td> ".$mrr_res['mode2']."</td>
     					<td>".$tag1."".$mrr_res['last_date']."".$tag2."</td>
     					<td align='right'>".$tag3."".number_format($mrr_res['last_odometer'],2)."".$tag4."</td>     					
     					<td align='right'>".$tag3."".number_format($mrr_res['difference'],2)."".$tag4."</td>
     				</tr>
     				
     			";	//<tr><td colspan='12'>".$mrr_res['sql']."</td></tr>
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
<? } 
	
	
		function mrr_get_first_and_last_reading_difference_for_odometer_month($truck_id,$date_start,$date_end)
		{
			
			$rng_mon1=date("Y-m",strtotime($date_start));
			$rng_mon0=date("Y-m",strtotime("-1 MONTH",strtotime($date_start)));
			$rng_mon2=date("Y-m",strtotime("+1 MONTH",strtotime($date_start)));	
				
			$rng_start1="".$rng_mon0."-25";
			$rng_end1="".$rng_mon1."-05";
			
			$rng_start2="".$rng_mon1."-25";
			$rng_end2="".$rng_mon2."-05";
			
			$res['first_odometer']=0;
			$res['first_date']="";
			$res['last_odometer']=0;
			$res['last_date']="";
			$res['difference']=0;
			
			$res['mode1']="";
			$res['mode2']="";
			
			$res['sql']="";
			
			//first is most recent...get from most recent tracking...if available.
			$sql="
				select performx_odometer,
					linedate 
				from ".mrr_find_log_database_name()."truck_tracking 
				where truck_id='".sql_friendly($truck_id)."' 
					and linedate>='".$date_start." 00:00:00'
					and linedate<='".$date_end." 23:59:59' 
				order by linedate desc,
					id desc 		
			";
			
			
			$data = simple_query($sql);
			if($row = mysqli_fetch_array($data)) 
			{
				$res['first_odometer']=$row['performx_odometer'];
				$res['first_date']=date("m/d/Y",strtotime($row['linedate']));
				
				$res['mode1']="PeopleNet";
			}
			$res['sql'].="1 ".$sql;
			
			if($res['first_odometer']==0)
			{	//not found, use the tracking odometer archive
				$sql="
					select odometer,
						linedate 
					from ".mrr_find_log_database_name()."truck_tracking_odometer 
					where truck_id='".sql_friendly($truck_id)."' 
						and linedate>='".$date_start." 00:00:00'
						and linedate<='".$date_end." 23:59:59' 
					order by linedate desc,
						id desc 		
				";
			
			
				$data = simple_query($sql);
				if($row = mysqli_fetch_array($data)) 
				{
					$res['first_odometer']=$row['odometer'];
					$res['first_date']=date("m/d/Y",strtotime($row['linedate']));
					
					$res['mode1']="PN(archive)";
				}	
				$res['sql'].="<br>2 ".$sql;
			}
			
			if($res['first_odometer']==0)
			{	//still not found, use the manual truck odometer archive
								
				$sql="
					select odometer,
						linedate 
					from trucks_odometer
					where truck_id='".sql_friendly($truck_id)."' 
						and linedate>='".$rng_start1." 00:00:00'
						and linedate<='".$rng_end1." 23:59:59' 
					order by linedate desc,
						id desc 		
				";			
			
				$data = simple_query($sql);
				if($row = mysqli_fetch_array($data)) 
				{
					$res['first_odometer']=$row['odometer'];
					$res['first_date']=date("m/d/Y",strtotime($row['linedate']));
					$res['mode1']="Manual";
				}	
				$res['sql'].="<br>3 ".$sql;
			}
			
			
			
			
			//last is oldest ranged in archive within the month range
			$sql="
				select odometer,
					linedate 
				from ".mrr_find_log_database_name()."truck_tracking_odometer 
				where truck_id='".sql_friendly($truck_id)."' 
					and linedate>='".$date_start." 00:00:00'
					and linedate<='".$date_end." 23:59:59' 
				order by linedate asc,
					id asc 
			";
			$res['sql'].=$sql;
			$data = simple_query($sql);
			if($row = mysqli_fetch_array($data)) 
			{
				$res['mode2']="PN";
				
				$res['last_odometer']=$row['odometer'];
				$res['last_date']=date("m/d/Y",strtotime($row['linedate']));
			}
			$res['sql'].="<br>4 ".$sql;
			
			if($res['last_odometer']==0)
			{	//still not found, use the manual odometer archive for the 
				
				$sql="
					select odometer,
						linedate 
					from trucks_odometer
					where truck_id='".sql_friendly($truck_id)."' 
						and linedate>='".$rng_start2." 00:00:00'
						and linedate<='".$rng_end2." 23:59:59' 
					order by linedate asc,
						id asc 		
				";			
			
				$data = simple_query($sql);
				if($row = mysqli_fetch_array($data)) 
				{
					$res['last_odometer']=$row['odometer'];
					$res['last_date']=date("m/d/Y",strtotime($row['linedate']));
					$res['mode2']="Manual";
				}	
				$res['sql'].="<br>5 ".$sql;
			}
			
			
			$res['difference']= $res['first_odometer'] - $res['last_odometer'] ;
			
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