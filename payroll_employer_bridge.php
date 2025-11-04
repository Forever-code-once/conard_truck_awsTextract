<? include('application.php') ?>
<? 
	$admin_page = 1;
	
	$mrr_message="";
	
	if(isset($_GET['did'])) {
		$sql = "
			update payroll_employer_vendor
			
			set	deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_delete = simple_query($sql);
		
		$mrr_message="Deleted payroll employer to SICAP vendor bridge.";
	}
	
	if(!isset($_POST['employer_id']))		$_POST['employer_id']=0;
	if(!isset($_POST['old_vendor_id']))	$_POST['old_vendor_id']=0;
	if(!isset($_POST['new_vendor_id']))	$_POST['new_vendor_id']=0;
	if(!isset($_POST['linedate_started']))	$_POST['linedate_started']=date("m/d/Y");	
	if(!isset($_POST['linedate_ended']))	$_POST['linedate_ended']=date("m/d/Y");	
	
	
	if(isset($_POST['bridge_it']))
	{		
		$sql="
			insert into payroll_employer_vendor
				(id, 
				linedate_added,
				linedate_started,
				linedate_ended,
				employer_id,
				old_sicap_vendor_id,
				new_sicap_vendor_id,
				deleted)
			values
				(NULL,
				NOW(),
				'".date("Y-m-d",strtotime($_POST['linedate_started']))."',
				'".date("Y-m-d",strtotime($_POST['linedate_ended']))."',
				'".sql_friendly($_POST['employer_id'])."',
				'".sql_friendly($_POST['old_vendor_id'])."',
				'".sql_friendly($_POST['new_vendor_id'])."',
				0)				
		";
		simple_query($sql);
		$newid=mysqli_insert_id($datasource);
		if($newid==0)
		{
			$mrr_message="<span class='alert'><b>Error: Employer bridge could not be createdto Vendor.<b></span>";
		}
		else
		{
			$mrr_message="Employer has been linked to Sicap Vendor.";		
		}
	}
	
	$emp_box=mrr_select_employer('employer_id',$_POST['employer_id'],'Select Employer');
	$vend_box1=mrr_get_all_sicap_vendors('old_vendor_id',$_POST['old_vendor_id'],'Select Old Vendor');
	$vend_box2=mrr_get_all_sicap_vendors('new_vendor_id',$_POST['new_vendor_id'],'Select New Vendor');
?>
<? include('header.php') ?>
<form name='payroll_bridge' action='<?= $SCRIPT_NAME ?>' method='post'>
<table class='admin_menu2' style='width:1200px;'>
<tr>
	<td valign='top' colspan='2'><div class='section_heading'>Payroll Employer to Vendor Bridge</div></td>
	<td valign='top' colspan='4'>Use this form to have payroll bills link an empoloyer to a different SICAP vendor than normally matched.</td>
</tr>
<tr>
	<td valign='top' colspan='6'><?= $mrr_message ?></td>
</tr>
<tr>
	<td valign='top' align='left'><b>Dispatch Employer:</b></td>	
	<td valign='top' align='left'><?=$emp_box ?></td>
	<td valign='top' align='left'><b>Old SICAP Vendor:</b></td>
	<td valign='top' align='left'><?=$vend_box1['select'] ?></td>
	<td valign='top' align='left'><b>New SICAP Vendor:</b></td>
	<td valign='top' align='left'><?=$vend_box2['select'] ?></td>	
</tr>
<tr>
	<td valign='top' align='left'><b>Date From:</b></td>
	<td valign='top' align='left'><input id='linedate_started' name='linedate_started' value='<?= $_POST['linedate_started'] ?>' class='input_medium'> (mm/dd/YYYY)</td>
	<td valign='top' align='left'><b>Date To:</b></td>
	<td valign='top' align='left'><input id='linedate_ended' name='linedate_ended' value='<?= $_POST['linedate_ended'] ?>' class='input_medium'> (mm/dd/YYYY)</td>
	<td valign='top' align='right' colspan='2'>
		<input type='submit' name='bridge_it' id='bridge_it' value='Bridge Employer'>
	</td>
</tr>
</table>
<br><br>
<table class='admin_menu2' style='width:1200px;'>
<tr>
	<td valign='top' colspan='5'><div class='section_heading'>Payroll Employer to Vendor Bridge</div></td>
</tr>
<tr>
	<td valign='top' align='left'><b>Employer</b></td>
	<td valign='top' align='left'><b>From Sicap Vendor</b></td>
	<td valign='top' align='left'><b>To Sicap Vendor</b></td>
	<td valign='top' align='left'><b>As of</b></td>
	<td valign='top' align='left'><b>&nbsp;</b></td>
</tr>
<?
	$sql="
		select *
		from payroll_employer_vendor
		where deleted=0				
		order by linedate_started desc
	";
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$ename=mrr_fetch_set_employer($row['employer_id']);
		$dlink="<a href='javascript:confirm_delete(".$row['id'].")'><img src='images/delete_sm.gif' border='0'></a>";
		
		$vname1=mrr_get_sicap_vendor_name($row['old_sicap_vendor_id']);
		$vname2=mrr_get_sicap_vendor_name($row['new_sicap_vendor_id']);
		
		echo "
			<tr>
				<td valign='top' align='left'>".$ename."</td>
				<td valign='top' align='left'>".$vname1."</td>
				<td valign='top' align='left'>".$vname2."</td>
				<td valign='top' align='left'>".date("m/d/Y",strtotime($row['linedate_started']))."</td>
				<td valign='top' align='left'>".$dlink."</td>
			</tr>
		";
	}	
?>
</table>
</form>
<script type='text/javascript'>
	/*
	$().ready(function() 
	{			
			
	});	
	*/
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete this employer to SICAP vendor bridge?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}
	$('#linedate_started').datepicker();
	$('#linedate_ended').datepicker();
</script>
<? include('footer.php') ?>