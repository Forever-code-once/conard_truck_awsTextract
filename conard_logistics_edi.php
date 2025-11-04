<? include('application.php') ?>
<? include_once('functions_load_edis.php') ?>
<? $admin_page = 1 ?>
<?	
$display_moder=1;
if($display_moder==0)
{
	if(!isset($_POST['file_name_holder']))	$_POST['file_name_holder']="";
	$mrr_msg="";
	$fnamer="";
	$page="";
	$load_listing="";
	$auto_save=1;
	
	if(isset($_POST['mrr_importer']))
	{     	
		$mrr_msg.="<br>File Name: ".$_FILES['mrr_import']['name'].".";
		$mrr_msg.="<br>File Temp: ".$_FILES['mrr_import']['tmp_name'].".";
		$mrr_msg.="<br>File Type: ".$_FILES['mrr_import']['type'].".";
		$mrr_msg.="<br>File Size: ".$_FILES['mrr_import']['size'].".";
		$mrr_msg.="<br>File Error: ".$_FILES['mrr_import']['error'].".";
		
		$fnamer=$_FILES['mrr_import']['name'];	
		$xml_file=$_FILES['mrr_import']['tmp_name'];
		
		if(substr_count(strtolower($_FILES['mrr_import']['type']),"/xml") > 0)
		{
			$mrr_msg.="<br>Processing:...<br>";				
			$xml = simplexml_load_file($xml_file);
			
			$res=mrr_process_visual_load_plus_edi($xml,7,$auto_save);		//7=Conard Logistics, 0 or 1 for auto-save
			$page=trim($res['page']);			
			$loads=$res['loads'];
			$load_ids=$res['load_arr'];
			
			$load_listing="".$loads." Load(s) Created: ";
			
			for($z=0; $z < $loads; $z++)
			{
				if($z > 0)	$load_listing.=", ";
				$load_listing.="<a href='manage_load.php?load_id=".$load_ids[$z]."' target='_blank'>Load ".$load_ids[$z]."</a>";				
			}
		}
		else
		{
			$mrr_msg.="<br>Error: This utility is only set up for properly formed XML files.<br>";	
		}
	}
?>
<?
$usetitle = "Conard Logistics Load EDI";
$use_title = "Conard Logistics Load EDI";
?>
<? include('header.php') ?>
<div style='margin:10px;'>

	<div class='standard18'><b>Conard Logistics Load EDI</b>...coming soon to a dispatch near you...  :)</div><br>
	<div style=color:purple;margin:10px;'>Import Loads via Visual Load Plus XML.</div><br>
	
	<form name='import_customer_loads' action='conard_logistics_edi.php' enctype="multipart/form-data" method='post'>
	
	<input type='hidden' name='file_name_holder' id='file_name_holder' value='<?= $_POST['file_name_holder'] ?>'>
	
	<table class='admin_menu1' style='text-align:left; width:1600px;'>
	<tr>
		<td valign='top' colspan='3'><?= $mrr_msg ?></td>
	</tr>
	<tr>
		<td valign='top'>Import File</td>
		<td valign='top'><input type="file" name='mrr_import' id='mrr_import' style='width:700px;'></td>
		<td valign='top'><input type="submit" name='mrr_importer' value='Import Load(s) from file'></td>
	</tr>
	<tr>
		<td valign='top' colspan='3'><hr></td>
	</tr>
	<tr>
		<td valign='top' colspan='3'><?= $load_listing ?></td>
	</tr>
	<tr>
		<td valign='top' colspan='3'><hr></td>
	</tr>
	<tr>
		<td valign='top' colspan='3'><pre><?= $page ?></pre></td>
	</tr>
	<tr>
		<td valign='top' colspan='3'><hr></td>
	</tr>
	</table>
	
	</form>
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
</script>
<?
}
else
{
?>
<? include('header.php') ?>
<div style='margin:10px;'>

	<div class='standard18'><b>Conard Logistics Load EDI</b></div><br>
	<div style=color:purple;margin:10px;'>Imported Loads via Visual Load Plus XML.</div><br>
	
	<form name='import_customer_loads' action='conard_logistics_edi.php' enctype="multipart/form-data" method='post'>
<?
	//report mode... display visula load plus loads that were imported
	$report="<table class='admin_menu1' style='text-align:left; width:1600px;'>";
	
	$report.="
		<tr>
			<td valign='top' colspan='9'><b>Unprocessed Visual Load Plus Loads</b></td>
		</tr>		
		<tr>
			<td valign='top'><b>LoadID</b></td>
			<td valign='top'><b>Pickup</b></td>
			<td valign='top'><b>Added</b></td>
			<td valign='top'><b>Origin</b></td>
			<td valign='top'><b>State</b></td>
			<td valign='top'><b>Destination</b></td>
			<td valign='top'><b>State</b></td>
			<td valign='top'><b>Load#</b></td>
			<td valign='top'><b>Special Instructions</b></td>
		</tr>
	";
	
	$cntr=0;
	$sql="
		select load_handler.* 
		from load_handler
		where deleted=0
			and vpl_imported>0
			and vpl_import_processed=0
		order by id desc
		limit 250
	";
	$data = simple_query($sql);	
	while($row=mysqli_fetch_array($data))
	{
		$flag=mrr_flag_processing_for_this_vlp_load($row['id']);
		if($flag==0)
		{
			$report.="
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'><a href='manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
				<td valign='top'>".$row['origin_city']."</td>
				<td valign='top'>".$row['origin_state']."</td>
				<td valign='top'>".$row['dest_city']."</td>
				<td valign='top'>".$row['dest_state']."</td>
				<td valign='top'>".$row['load_number']."</td>
				<td valign='top'>".$row['special_instructions']."</td>
			</tr>
			";
			$cntr++;
		}
	}	
	
	
	
	
	
	$report.="
		<tr>
			<td valign='top' colspan='9'><br><br><hr><br><br></td>
		</tr>
		<tr>
			<td valign='top' colspan='9'><b>Processed Visual Load Plus Loads</b></td>
		</tr>		
		<tr>
			<td valign='top'><b>LoadID</b></td>
			<td valign='top'><b>Pickup</b></td>
			<td valign='top'><b>Added</b></td>
			<td valign='top'><b>Origin</b></td>
			<td valign='top'><b>State</b></td>
			<td valign='top'><b>Destination</b></td>
			<td valign='top'><b>State</b></td>
			<td valign='top'><b>Load#</b></td>
			<td valign='top'><b>Special Instructions</b></td>
		</tr>
	";
	
	$cntr=0;
	$sql="
		select load_handler.* 
		from load_handler
		where deleted=0
			and vpl_imported>0
			and vpl_import_processed>0
		order by id desc
		limit 250
	";
	$data = simple_query($sql);	
	while($row=mysqli_fetch_array($data))
	{
		//$flag=mrr_flag_processing_for_this_vlp_load($row['id']);
		//if($flag==0)
		//{
			$report.="
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'><a href='manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
				<td valign='top'>".$row['origin_city']."</td>
				<td valign='top'>".$row['origin_state']."</td>
				<td valign='top'>".$row['dest_city']."</td>
				<td valign='top'>".$row['dest_state']."</td>
				<td valign='top'>".$row['load_number']."</td>
				<td valign='top'>".$row['special_instructions']."</td>
			</tr>
			";
			$cntr++;
		//}
	}		
	
	$report.="
		<tr>
			<td valign='top' colspan='9'><br><br></td>
		</tr>
		</table>
	";
	
	echo $report;
?>
	</form>
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
</script>
<?	
}
?>
<? include('footer.php') ?>