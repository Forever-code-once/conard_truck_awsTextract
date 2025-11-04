<? include('header.php') ?>
<table cellpadding='0' cellspacing='0' border='0' width='1600'>
<tr>
	<td valign='top' width='350'>
<?
	$rfilter = new report_filter();
	//$rfilter->generic_search_text 	= true;
	$rfilter->show_error_scans	 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
	$max_drivers=0;
	$max_trucks=0;
	$max_trailers=0;
	$max_customers=0;
	$max_loads=0;
	$max_dispatches=0;
	$max_users=0;
	
	$sql = "select id from drivers order by id desc";			$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_drivers=$row['id'];		}
	$sql = "select id from trucks	order by id desc";			$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_trucks=$row['id'];		}
	$sql = "select id from trailers order by id desc";		$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_trailers=$row['id'];	}
	$sql = "select id from customers order by id desc";		$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_customers=$row['id'];	}
	$sql = "select id from load_handler order by id desc";		$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_loads=$row['id'];		}
	$sql = "select id from trucks_log order by id desc";		$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_dispatches=$row['id'];	}
	$sql = "select id from users order by id desc";			$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_users=$row['id'];		}
?>
	</td>	
	<td valign='top' align='right'>		
		<table cellpadding='0' cellspacing='0' border='0' class='admin_menu1' width='300'>	
			<tr><td valign='top' colspan='2' align='center'><b>Scan File Code First Letter</b></td></tr>
			<tr><td valign='top'><b>Letter</b></td><td valign='top'><b>Section</b></td><td valign='top'><b>MaxID</b></td></tr>
			<tr><td valign='top'>V</td><td valign='top'>Driver</td><td valign='top'><?=$max_drivers  ?></td></tr>
			<tr><td valign='top'>T</td><td valign='top'>Truck</td><td valign='top'><?=$max_trucks  ?></td></tr>
			<tr><td valign='top'>R</td><td valign='top'>Trailer</td><td valign='top'><?=$max_trailers  ?></td></tr>			
			<tr><td valign='top'>C</td><td valign='top'>Customer</td><td valign='top'><?=$max_customers  ?></td></tr>
			<tr><td valign='top'>D</td><td valign='top'>Dispatches</td><td valign='top'><?=$max_dispatches  ?></td></tr>
			<tr><td valign='top'>L</td><td valign='top'>Loads</td><td valign='top'><?=$max_loads  ?></td></tr>
			<tr><td valign='top'>U</td><td valign='top'>User</td><td valign='top'><?=$max_users  ?></td></tr>
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
	
	$name_trucks=0;
	$name_trailers=0;
	
	$max_drivers=0;
	$max_trucks=0;
	$max_trailers=0;
	$max_customers=0;
	$max_loads=0;
	$max_dispatches=0;
	$max_users=0;
	
	if(isset($_POST['build_report'])) 
	{ 	
		$sql = "select id from drivers order by id desc";			$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_drivers=$row['id'];		}
		$sql = "select id from trucks	order by id desc";			$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_trucks=$row['id'];		}
		$sql = "select id from trailers order by id desc";		$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_trailers=$row['id'];	}
		$sql = "select id from customers order by id desc";		$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_customers=$row['id'];	}
		$sql = "select id from load_handler order by id desc";		$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_loads=$row['id'];		}
		$sql = "select id from trucks_log order by id desc";		$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_dispatches=$row['id'];	}
		$sql = "select id from users order by id desc";			$data = simple_query($sql);			if($row = mysqli_fetch_array($data)) 	{	$max_users=$row['id'];		}
						
		$search_date_range = "
				and linedate_added >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and linedate_added < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
			";		
		
		
		echo "&nbsp;&nbsp;&nbsp;
		     <b>Scan FILING Errors Report: (File scanned properly, but may have been filed incorrectly)</b>
		     <br><br>
		     &nbsp;&nbsp;&nbsp;
		     *File Attachments show on this report if the Reference ID is higher than the number of items in the table (drivers, loads, etc.) by ID of item, not the Name of it. 
		     <br>
		     &nbsp;&nbsp;&nbsp;
		     *If the Reference ID matches exactly to the truck or trailer name, the RefID is set to the true ID for proper attachment automatically, and not shown below.
		     <br><br>
			<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1600px;text-align:left'>
			<tr>
				<th>ID</th>
				<th>Date</th>
				<th>Filename</th>
				<th>Link</th>
				<th>Section</th>
				<th>ReferenceID</th>				
				<th>Public Name</th>
				<th align='right' title='Size in Bytes'>Size</th>							
			</tr>
		";		//<th align='right'>&nbsp;</th>	
		
		$sql = "
			select *			
			from attachments
			where deleted = 0	
				$search_date_range
				".($_POST['report_show_error_scans'] == 1 ? " and result = '0' " : '') ."
				".($_POST['report_show_error_scans'] == 2 ? " and result = '1' " : '') ."			
				and (
					(section_id = '".SECTION_DRIVER."' and xref_id > '".$max_drivers."')
					or
					(section_id = '".SECTION_TRUCK."' and xref_id > '".$max_trucks."')
					or
					(section_id = '".SECTION_TRAILER."' and xref_id > '".$max_trailers."')
					or
					(section_id = '".SECTION_CUSTOMER."' and xref_id > '".$max_customers."')
					or
					(section_id = '".SECTION_DISPATCH."' and xref_id > '".$max_dispatches."')
					or
					(section_id = '".SECTION_LOAD."' and xref_id > '".$max_loads."')
					or
					(section_id = '".SECTION_USER."' and xref_id > '".$max_users."')
				)
			order by id desc	
		";				
		$data = simple_query($sql);		
		//echo "<br><b>Query:</b><br>".$sql."<br>";	
		while($row = mysqli_fetch_array($data)) 
		{			
			$file_namer="".str_replace(" ","%20",$row['fname'])."";
			$name_trucks="";
			$name_trailers="";
					
			$valid=1;			
			$sqlx="";
			
			if($row['section_id']==SECTION_TRUCK)
			{	//check if the name is the ref ID
				$sqlx = "select id,name_truck from trucks where name_truck='".$row['xref_id']."' order by deleted asc, active desc, id asc";		
				$datax = simple_query($sqlx);		
				if($rowx = mysqli_fetch_array($datax)) 	
				{
					$name_trucks=(int) trim($rowx['name_truck']);	
					if($name_trucks==$row['xref_id'])	
					{
						$valid=0;		
						$sqlu = "update attachments set xref_id='".$rowx['id']."' where id='".$row['id']."'";		
						simple_query($sqlu);	
					}
				}
			}
			if($row['section_id']==SECTION_TRAILER)
			{	//check if the name is the ref ID
				$sqlx = "select id,trailer_name from trailers where trailer_name='".$row['xref_id']."' order by deleted asc, active desc, id asc";	
				$datax = simple_query($sqlx);		
				if($rowx = mysqli_fetch_array($datax)) 	
				{	
					$name_trailers=(int) trim($rowx['trailer_name']);	
					if($name_trailers==$row['xref_id'])	
					{
						$valid=0;	
						$sqlu = "update attachments set xref_id='".$rowx['id']."' where id='".$row['id']."'";		
						simple_query($sqlu);	
					}
				}	
			}
			//$sqlx="";
			
			if($valid > 0)
			{
     			echo "
     				<tr id='line_holder_$row[id]' class='line_entry'>
     					<td><span class='mrr_link_like_on' onclick=\"process_file($row[id], 0,'".$file_namer."', '".$row['file_ext']."')\">$row[id]</span></td>					
     					<td>".date("n/j/Y", strtotime($row['linedate_added']))."</td>
     					<td><span id='filer_$row[id]'>".$row['fname']."</span></td>
     					<td><span id='load_number_holder_$row[id]'><a href='/documents/".$file_namer."' target='_blank'>View</a></span></td>
     					<td>".mrr_decode_file_section($row['section_id'])."</td>
     					<td>".$row['xref_id']."</td>
     					<td>".trim($row['public_name'])."".($_SESSION['user_id']==23 ? " | <b>".$name_trucks."</b> | <b>".$name_trailers."</b> | ".$sqlx : "")."</td>
     					<td align='right'>".number_format($row['filesize'])."</td>     					
     				</tr>
     			";		//<td align='right'><a href='javascript:confirm_delete(".$row['id'].");'><img src='images/delete_sm.gif' border='0'></a></td>
			}
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
			txt=txt + "File Name: <b>"+filename+"</b> <input type='hidden' name='file_name' id='file_name' value='"+filename+"'><br><br>";
			txt=txt + "<a href='#' onclick=\"process_file("+id+", 1,'"+filename+"','')\">Click here</a> to view file.<br><br>";
			txt=txt + "<b>Categorize file by using ONE of the options below:</b><br>";
			txt=txt + "Customer<br>"+sel1+"<br>";
			txt=txt + "Load<br>"+sel2+"<br>";
			txt=txt + "Dispatch<br>"+sel3+"<br>";
			txt=txt + "Driver<br>"+sel4+"<br>";
			txt=txt + "User<br>"+sel7+"<br>";
			txt=txt + "Truck<br>"+sel5+"<br>";
			txt=txt + "Trailer<br>"+sel6+"<br><br>";
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
								
								'bypass': 1,			
													
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
	function confirm_delete(id) 
	{
		$.prompt("Are you sure you want to delete this file attachment?", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {
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
				}
			}
		});
	}
</script>
<? include('footer.php') ?>