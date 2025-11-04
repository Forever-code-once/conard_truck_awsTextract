<? include('header.php') ?>
<?
	
	$rfilter = new report_filter();
	$rfilter->show_users 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

	$sql = "
		select log_email_alerts.*		
		from log_email_alerts
		where log_email_alerts.deleted = 0
			and log_email_alerts.linedate_added >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
			and log_email_alerts.linedate_added < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
		order by log_email_alerts.linedate_added asc
	";
	$data = simple_query($sql);
	
	ob_start();
	
?>


<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h1>Alert Email Report</h1>
</div>
<div style='clear:both'></div>
<table class='admin_menu1 font_display_section' style='text-align:left; width:1000px; margin:10px'>
<tr>
	<td nowrap><b>#</b></td>
	<td><b>Added</b></td>
	<td colspan='2'><b>Subject</b></td>
	<td colspan='4'><b>Message</b></td>
	<td>&nbsp;</td>
</tr>
<? 
	
	$counter = 0;
	while($row = mysqli_fetch_array($data)) 
	{
		//$row['imap_message_id']=str_replace("<","&lt;",$row['imap_message_id']);
		//$row['imap_message_id']=str_replace(">","&gt;",$row['imap_message_id']);
		
		$row['message_from']=str_replace("<","&lt;",$row['message_from']);
		$row['message_from']=str_replace(">","&gt;",$row['message_from']);
		
		echo "
			<tr>
				<td valign='top'>".($counter + 1)."</td>
				<td valign='top'>".date("m/d/Y H:i", strtotime($row['linedate_added']))."</td>
				<td valign='top' colspan='2'>".trim($row['subject'])."</td>
				<td valign='top' colspan='4'>".trim($row['message_from'])."</td>
				<td valign='top'>&nbsp;</td>
			</tr>
		";
		
		// get the trucks and drivers for this load
		$sql2 = "
			select log_email_alerts_entries.*,
     			users.name_first,
     			users.name_last
     		
     		from log_email_alerts_entries
     			left join users on users.id = log_email_alerts_entries.user_id
     		where log_email_alerts_entries.deleted = 0
     			and log_email_alerts_entries.log_email_alert_id='".sql_friendly($row['id'])."'		
     			".($_POST['user_id'] ? " and users.id = '".sql_friendly($_POST['user_id'])."'" : '') ."
     		order by log_email_alerts_entries.linedate_added asc
		";
		$data2 = simple_query($sql2);
		$subcntr=0;
		if(mysqli_num_rows($data2) > 0)
		{
			echo "
				<tr>
					<td valign='top'>&nbsp;</td>
					<td valign='top'>&nbsp;</td>
					<td valign='top'><b>Attempted</b></td>
					<td valign='top'><b>Response</b></td>
					<td valign='top'><b>First Name</b></td>
					<td valign='top'><b>Last Name</b></td>
					<td valign='top'><b>Phone Number</b></td>
					<td valign='top'><b>Handled</b></td>
					<td valign='top'>&nbsp;</td>
				</tr>
			";	
		}				
		while($row2 = mysqli_fetch_array($data2)) 
		{			
			//$user_id = $row2['user_id'];
			//$method = $row2['method_id'];
			//$alert_id = $row2['id'];
			
			$handle_mask="No Answer";
			if( $row2['handle_flag'] ==1)		$handle_mask="Accepted";
			if( $row2['handle_flag'] ==2)		$handle_mask="Passed";
			
			$resp=date("m/d/Y H:i", strtotime($row2['linedate_response']));
			if(substr_count($resp,"12/31/1969") > 0)	$resp="";
						
			echo "
				<tr class='".($subcntr%2==0 ? "even1" : "odd1")."'>
					<td valign='top'>&nbsp;</td>
					<td valign='top'>&nbsp;</td>
					<td valign='top'>".date("m/d/Y H:i", strtotime($row2['linedate_added']))."</td>
					<td valign='top'>".$resp."</td>
					<td valign='top'>".trim($row2['name_first'])."</td>
					<td valign='top'>".trim($row2['name_last'])."</td>
					<td valign='top'>".trim($row2['phone'])."</td>
					<td valign='top'>".$handle_mask."</td>
					<td valign='top'>&nbsp;</td>
				</tr>
			";
			
			$subcntr++;
		}
		$counter++;
	}
?>
</table>
</form>

<?
	$pdf = ob_get_contents();
	ob_end_clean();
?>
<script type='text/javascript'>
	
	/*
	$().ready(function() {
		$('.tablesorter').tablesorter({
		   			headers: {         				
		   				5: {sorter:'currency'},
		   				6: {sorter:'currency'},
		   				7: {sorter:'currency'},
		   				11: {sorter: false}
		   			}
		   				
		});
		
		$('.datepicker').datepicker();
	});
	*/
</script>
<?
	$link = print_contents('alert_call_'.createuuid(), $pdf);

	echo "
		<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
			<a href='$link'>Printed Version</a>
		</div>
		<div style='clear:both'></div>
		
	";

	echo $pdf;
	
?>
<? include('footer.php') ?>