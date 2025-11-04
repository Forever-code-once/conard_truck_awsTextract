<? include('header.php') ?>
<? if(!isset($_GET['print']) && !isset($_POST['print'])) { ?>
	<?
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}
	?>

    	<div class='section_heading'>PeopleNet Unit Truck Report</div>
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
				and (trucks.apu_serial!='' or trucks.dvr_serial!='')
			order by trucks.active desc,trucks.name_truck asc
		";
		$data = simple_query($sql);
		
		echo "
			<table class='admin_menu2 tablesorter' style='width:1000px;'>
			<thead>
			<tr>
				<th>Truck</th>
				<th>Year</th>
				<th>Make</th>
				<th>Model</th>
				<th>VIN</th>
				<th>Plate</th>
				<th>DSN</th>
				<th>DVR</th>
				<th></th>
			</tr>
			</thead>
			<tbody>
		";
		$counter = 0;
		
		$scnt=0;
		$vcnt=0;
		$dns[0]="";
		$dvr[0]="";
		
		while($row= mysqli_fetch_array($data)) 
		{
			$counter++;
						
			$tag1="<span class='alert'><b>";
			$tag2="</b></span>";
			
			$tag1="";
			$tag2="";			
			
			$founds=0;
			$foundv=0;
			$dup="";
			
			for($i=0;$i < $scnt; $i++)
			{
				if($dns[$i]==trim($row['apu_serial']) && trim($row['apu_serial'])!="")		{	$founds=1;	$dup.="-DSN Duplicate-";		}
			}
			for($i=0;$i < $vcnt; $i++)
			{
				if($dvr[$i]==trim($row['dvr_serial']) && trim($row['dvr_serial'])!="")		{	$foundv=1;	$dup.="-DVR Duplicate-";		}
			}
			
			if($founds==0 && trim($row['apu_serial'])!="")	{	$dns[$scnt]=trim($row['apu_serial']);		$scnt++;	}
			if($foundv==0 && trim($row['dvr_serial'])!="")	{	$dvr[$vcnt]=trim($row['dvr_serial']);		$vcnt++;	}
			
			echo "
				<tr>
					<td valign='top'><a href='admin_trucks.php?id=".$row['id']."' target='_blank'".($row['active']==0 ? " style='color:#dddddd;'" : "").">".$row['name_truck']."</a></td>
					<td valign='top'>$row[truck_year]</td>
					<td valign='top'>$row[truck_make]</td>
					<td valign='top'>$row[truck_model]</td>
					<td valign='top'>$row[vin]</td>
					<td valign='top'>$row[license_plate_no]</td>
					<td valign='top'>".$row['apu_serial']."</td>
					<td valign='top'>$row[dvr_serial]</td>		
					<td valign='top'><span style='color:#cc0000;'><b>".$dup."</b></span></td>			
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