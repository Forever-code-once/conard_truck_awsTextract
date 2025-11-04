<? include('header.php') ?>
<?

	$rfilter = new report_filter();
	$rfilter->generic_search_text 	= true;
	$rfilter->show_error_scans	 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
	
	if(isset($_POST['build_report'])) { 
		
		$search_date_range = '';
		if($_POST['report_generic_search_text'] != '') {
		} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and linedate_added >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and linedate_added < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
			";
		}
		
		$sql = "
			select *
			
			from ".mrr_find_log_database_name()."log_scan_loads
			where deleted = 0
				and section_id = 0
				$search_date_range
				".($_POST['report_generic_search_text'] ? " and load_id like '%".sql_friendly($_POST['report_generic_search_text'])."%'" : '') ."
				".($_POST['report_show_error_scans'] == 1 ? " and rslt = '0' " : '') ."
				".($_POST['report_show_error_scans'] == 2 ? " and rslt = '1' " : '') ."
				

		";
		$data = simple_query($sql);
		
		echo "
			<table class='admin_menu2 font_display_section' style='margin:0 10px;width:950px;text-align:left'>
			<tr>
				<th>ID</th>
				<th>Load</th>
				<th>Date</th>
				<th>Filename</th>
				<th align='right'>Size</th>
				
			</tr>
		";
		
		while($row = mysqli_fetch_array($data)) {
			echo "
				<tr id='line_holder_$row[id]' onclick=\"process_file($row[id], 0,'$row[filename]', '$row[load_id]')\" class='line_entry'>
					<td>$row[id]</td>
					<td><span id='load_number_holder_$row[id]'>".($row['load_id'] == '' ? "<span class='alert'>Could Not Process</span>" : $row['load_id'])."</span></td>
					<td>".date("n/j/Y", strtotime($row['linedate_added']))."</td>
					<td>$row[filename]</td>
					<td align='right'>".number_format($row['filesize'])."</td>
				</tr>
			";
		}
		echo "</table>";
	}
?>
<script type='text/javascript'>
	
	function process_file(id, rslt, filename, load_number) {
		if(!rslt) {
			$.prompt("Enter the Load Number: <input name='load_number' id='load_number' value='"+load_number+"'><br><br><a href='#' onclick=\"process_file("+id+", 1,'"+filename+"','')\">Click here</a> to view file.",{
				buttons: {Save: true, Cancel: false, Delete: "delete"},
				loaded: function() {
					$('#load_number').focus();
				},
				submit: function(v, m, f) {
					if(v == 'delete') {
						$.ajax({
							url: "ajax.php?cmd=delete_scanned_load",
							type: "post",
							dataType: "xml",
							data: {
								id: id,
								filename: filename
							},
							success: function(xml) {
								$('#line_holder_'+id).remove();
								$.noticeAdd({text: "Success - file deleted"});
							}
						});
					} else if(v) {
						if(f.load_number == '') {
							return false;
						}
						
						$.ajax({
							url: "ajax.php?cmd=rename_scanned_load",
							type: "post",
							dataType: "xml",
							data: {
								id: id,
								load_number: f.load_number
							},
							success: function(xml) {
								//$.prompt("done");
								$('#load_number_holder_'+id).html(f.load_number);
								$.noticeAdd({text: "Success - Load updated."});
							}
						});
					}
				}
			});
		} else {
			window.open('loads/files/'+filename);
		}
	}
	
	$('.line_entry').hover(
		function() {
			$(this).addClass('over');
		},
		function() {
			$(this).removeClass('over');
		}
	);
</script>
<? include('footer.php') ?>