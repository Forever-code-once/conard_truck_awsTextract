<? include('header.php') ?>
<table cellpadding='0' cellspacing='0' border='0' width='1000'>
<tr>
	<td valign='top' width='350'>
<?
	$rfilter = new report_filter();
	//$rfilter->generic_search_text 	= true;
	$rfilter->show_error_scans	 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
?>
	</td>	
	<td valign='top' align='right'>		
		<table cellpadding='0' cellspacing='0' border='0' class='admin_menu1' width='300'>	
			<tr><td valign='top' colspan='2' align='center'><b>Scan File Code First Letter</b></td></tr>
			<tr><td valign='top'><b>Letter</b></td><td valign='top'><b>Section</b></td></tr>
			<tr><td valign='top'>V</td><td valign='top'>Driver</td></tr>
			<tr><td valign='top'>R</td><td valign='top'>Trailer</td></tr>
			<tr><td valign='top'>T</td><td valign='top'>Truck</td></tr>
			<tr><td valign='top'>C</td><td valign='top'>Customer</td></tr>
			<tr><td valign='top'>D</td><td valign='top'>Dispatches</td></tr>
			<tr><td valign='top'>L</td><td valign='top'>Loads</td></tr>
			<tr><td valign='top'>U</td><td valign='top'>User</td></tr>
		</table>
		<br>
		Ex: VD = Driver, Driver File... but DV = Dispatch Violation
		<br>
		<a href='scan_process.php' target='_blank'>Run Scanning Processor</a>
	</td>
	<td valign='top' align='right'>		
		<table cellpadding='0' cellspacing='0' border='0' class='admin_menu2' width='300'>	
			<tr><td valign='top' colspan='2' align='center'><b>Scan File Code Second Letter</b></td></tr>
			<tr><td valign='top'><b>Letter</b></td><td valign='top'><b>Type/Category</b></td></tr>
			<tr><td valign='top'>I</td><td valign='top'>Invoice</td></tr>
			<tr><td valign='top'>P</td><td valign='top'>POD</td></tr>
			<tr><td valign='top'>D</td><td valign='top'>Driver File</td></tr>
			<tr><td valign='top'>V</td><td valign='top'>Violation</td></tr>
			<tr><td valign='top'>E</td><td valign='top'>Expense</td></tr>
			<tr><td valign='top'>O</td><td valign='top'>Other</td></tr>
			<tr><td valign='top'>M</td><td valign='top'>Document</td></tr>
			<tr><td valign='top'>R</td><td valign='top'>Rate Confirmation</td></tr>
		</table>
		<br>
		<a href='mrr_ftp_viewer.php' target='_blank'>View Scanner Upload Directory</a>
	</td>
</tr>
</table>
<?	
	//create simple assignment settings
	$selbox1="<select name='cust_id' id='cust_id'>";
	$selbox1.="<option value='0' selected></option>";
	$sql = "
		select id, name_company		
		from customers
		where deleted = 0				
		order by name_company asc, id asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$selbox1.="<option value='".$row['id']."'>".trim($row['name_company'])."</option>";
	}
	$selbox1.="</select>";
	
		
	$selbox2="<input type='text' name='load_id' id='load_id' value='0' style='text-align:right;'>";
	
	
		
	$selbox3="<input type='text' name='disp_id' id='disp_id' value='0' style='text-align:right;'>";
	
	
		
	$selbox4="<select name='driver_id' id='driver_id'>";
	$selbox4.="<option value='0' selected></option>";
	$sql = "
		select id, name_driver_first,name_driver_last	
		from drivers
		where deleted = 0				
		order by name_driver_last asc, name_driver_first asc, id asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$selbox4.="<option value='".$row['id']."'>".trim($row['name_driver_first']." ".$row['name_driver_last'])."</option>";
	}
	$selbox4.="</select>";
		
		
		
	$selbox5="<select name='truck_id' id='truck_id'>";
	$selbox5.="<option value='0' selected></option>";
	$sql = "
		select id, name_truck		
		from trucks
		where deleted = 0				
		order by name_truck asc, id asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$selbox5.="<option value='".$row['id']."'>".trim($row['name_truck'])."</option>";
	}
	$selbox5.="</select>";
	
	
	
	$selbox6="<select name='trailer_id' id='trailer_id'>";
	$selbox6.="<option value='0' selected></option>";
	$sql = "
		select id, trailer_name		
		from trailers
		where deleted = 0				
		order by trailer_name asc, id asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$selbox6.="<option value='".$row['id']."'>".trim($row['trailer_name'])."</option>";
	}
	$selbox6.="</select>";
	
	
	$selbox7='';
	$selbox10='';
	$selbox11='';
	
	$selbox10="<select name='maint_id' id='maint_id'>";
	$selbox10.="<option value='0' selected></option>";
	$sql = "
		select id, maint_desc	
		from maint_requests
		where deleted = 0	
			and active > 0			
		order by id desc
		limit 100
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$alt_desc=trim($row['maint_desc']);
		$alt_desc=str_replace("'","",$alt_desc);
		if(strlen($alt_desc) > 30)		$alt_desc=trim(substr($alt_desc,0,30))."...";
		
		$selbox10.="<option value='".$row['id']."'>".$row['id']."</option>";		//-".$alt_desc."
	}
	$selbox10.="</select>";
	
	
	$selbox11="<select name='acc_id' id='acc_id'>";
	$selbox11.="<option value='0' selected></option>";
	$sql = "
		select id, accident_number, accident_desc
		from accident_reports
		where deleted = 0				
		order by id desc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$alt_desc=trim($row['accident_desc']);
		$alt_desc=str_replace("'","",$alt_desc);
		if(strlen($alt_desc) > 20)		$alt_desc=trim(substr($alt_desc,0,20))."...";
		
		$selbox11.="<option value='".$row['id']."'>".$row['id']."</option>";		//".(trim($row['accident_number'])!="" ? trim($row['accident_number']) : $alt_desc)."
	}
	$selbox11.="</select>";
	
	$selbox7="<select name='user_id' id='user_id'>";
	$selbox7.="<option value='0' selected></option>";
	$sql = "
		select id, name_first,name_last
		from users
		where deleted = 0				
		order by id desc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$desc=trim($row['name_first']." ".$row['name_last']);
		$desc=str_replace("'","",$desc);
		if(strlen($desc) > 20)		$desc=trim(substr($alt_desc,0,20))."...";
		
		$selbox7.="<option value='".$row['id']."'>".$desc."</option>";		//".(trim($row['accident_number'])!="" ? trim($row['accident_number']) : $alt_desc)."
	}
	$selbox7.="</select>";
	
	
	function mrr_decode_simple_sector($id=0)
	{
		$sector[0]="";
		$sector[1]="Driver";
		$sector[2]="Trailer";
		$sector[3]="Truck";
		$sector[5]="Customer";
		$sector[6]="Dispatch";
		$sector[7]="User";
		$sector[8]="Load";
		$sector[10]="Maint Req";
		$sector[11]="Accident";
		
		return $sector[$id];
	}	
	
	if(isset($_POST['build_report'])) 
	{ 				
		$search_date_range = "
				and linedate_added >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and linedate_added < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
			";		
		
		$sql = "
			select *
			
			from attachments
			where deleted = 0				
				and (section_id = 0 or xref_id = 0)
				$search_date_range
				".($_POST['report_show_error_scans'] == 1 ? " and result = '0' " : '') ."
				".($_POST['report_show_error_scans'] == 2 ? " and result = '1' " : '') ."
		";
		$data = simple_query($sql);
		
		//echo "<br><b>Query:</b><br>".$sql."<br>";
		
		echo "&nbsp;&nbsp;&nbsp;
		     <b>Scan Errors Report:</b><br><br>
			<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1400px;text-align:left'>
			<tr>
				<th>ID</th>
				<th>Date</th>
				<th>Filename</th>
				<th>Status</th>
				<th>Section</th>
				<th>&nbsp;</th>
				<th align='right' title='Size in Bytes'>Size</th>
				
			</tr>
		";
		
		while($row = mysqli_fetch_array($data)) 
		{			
			$file_namer="".str_replace(" ","%20",$row['fname'])."";
			echo "
				<tr id='line_holder_$row[id]' class='line_entry'>
					<td><span class='mrr_link_like_on' onclick=\"process_file($row[id], 0,'".$file_namer."', '".$row['file_ext']."')\">$row[id]</span></td>					
					<td>".date("n/j/Y", strtotime($row['linedate_added']))."</td>
					<td><span id='filer_$row[id]'>".$row['fname']."</span></td>
					<td><span id='load_number_holder_$row[id]'><span class='alert'>Could Not Process</span> <a href='".($row['filesize'] > 0 ? "/documents/".$file_namer."" : "/scanner_upload/problem/".$file_namer."")."' target='_blank'>Examine File</a></span></td>
					<td>".mrr_decode_simple_sector($row['section_id'])."</td>
					<td>".trim($row['public_name'])."</td>
					<td align='right'>".number_format($row['filesize'])."</td>
				</tr>
			";		
		}
		echo "</table>";
	}
	
?>
<script type='text/javascript'>
	
	var sel1="<?=$selbox1 ?>";
	var sel2="<?=$selbox2 ?>";
	var sel3="<?=$selbox3 ?>";
	var sel4="<?=$selbox4 ?>";
	var sel5="<?=$selbox5 ?>";
	var sel6="<?=$selbox6 ?>";
	var sel7="<?=$selbox7 ?>";	
	var sel10="<?=$selbox10 ?>";
	var sel11="<?=$selbox11 ?>";
		
	function process_file(id, rslt, filename, not_used) 
	{
		if(!rslt) 
		{			
			txt="";			
			txt=txt + "Enter the new File Name: <input name='file_name' id='file_name' value='"+filename+"'><br><br>";
			txt=txt + "<a href='#' onclick=\"process_file("+id+", 1,'"+filename+"','')\">Click here</a> to view file.<br><br>";
			txt=txt + "<b>Categorize file by using ONE of the options below:</b><br>";
			txt=txt + "Customer<br>"+sel1+"<br>";
			txt=txt + "Load<br>"+sel2+"<br>";
			txt=txt + "Dispatch<br>"+sel3+"<br>";
			txt=txt + "Driver<br>"+sel4+"<br>";
			txt=txt + "User<br>"+sel7+"<br>";
			txt=txt + "Truck<br>"+sel5+"<br>";
			txt=txt + "Trailer<br>"+sel6+"<br>";	
			txt=txt + "Maint Request<br>"+sel10+"<br>";
			txt=txt + "Accident Report<br>"+sel11+"<br><br>";	
			txt=txt + "<br>*** First one set in order is used.";				
			
			$.prompt(txt,{
				buttons: {Save: true, Cancel: false, Delete: "delete"},
				loaded: function() {
					$('#file_name').focus();
				},
				submit: function(v, m, f) {
					if(v == 'delete') {
						$.ajax({
							url: "ajax.php?cmd=mrr_delete_scanned_file",
							type: "post",
							dataType: "xml",
							data: {
								'id': id
							},
							success: function(xml) {
								$('#line_holder_'+id).remove();
								$.noticeAdd({text: "Success - file deleted"});
							}
						});
					} else if(v) {
						if(f.file_name == '') {
							return false;
						}
						
						$.ajax({
							url: "ajax.php?cmd=mrr_rename_scanned_file",
							type: "post",
							dataType: "xml",
							data: {
								'id': id,
								
								'cust_id': $('#cust_id').val(),
								'load_id': $('#load_id').val(),
								'disp_id': $('#disp_id').val(),
								'driver_id': $('#driver_id').val(),
								'user_id': $('#user_id').val(),
								'truck_id': $('#truck_id').val(),
								'trailer_id': $('#trailer_id').val(),
								
								'maint_id': $('#maint_id').val(),
								'acc_id': $('#acc_id').val(),
																
								'file_old': filename,
								'file_name': f.file_name
							},
							success: function(xml) {
								
								if($(xml).find('rslt').text() == "0") {
                    					$.prompt("Error: File did not get renamed...");					
                    				}
                    				else
                    				{
                    					//$.prompt("done");
									$('#load_number_holder_'+id).html('<span style="color:green;"><b>Renamed...will try again.</b></span>');
									$('#filer_'+id).html(f.file_name);
									
									$.noticeAdd({text: "Success - File '"+filename+"' Renamed to '"+f.file_name+"'."});	
									if(parseInt($(xml).find('sector').text()) > 0 && 	parseInt($(xml).find('xref').text()) > 0)
									{
										$('#line_holder_'+id).remove();		//processed	
									}
                    				}									
							}
						});
					}
				}
			});
		} 
		else 
		{
			window.open('scanner_upload/problem/'+filename);
		}
	}
	/*
	$('.line_entry').hover(
		function() {
			$(this).addClass('over');
		},
		function() {
			$(this).removeClass('over');
		}
	);
	*/
</script>
<? include('footer.php') ?>