<? include('header.php') ?>
<div style='text-align:left;margin:10px'>
	<div class='section_heading'>Search for Files/Notes </div>
	Enter any date for the month you would like to narrow your search to. <br>
	For example, if you wanted <?=date("M, Y")?>, select any date during that month <br>
	and the system will automatically generate the full month search
</div>

<?
	$rfilter = new report_filter();
	$rfilter->show_date_range 		= false;
	$rfilter->show_single_date 		= true;
	$rfilter->maintenance_desc		= true;
	$rfilter->search_notes_files		= true;
	$rfilter->search_sort_by			= true;
	$rfilter->show_filter();
?>

<form action='' method='post'>
<input type='hidden' name='build_report' value='1'>
<input type='hidden' name='report_date' value='<?=$_POST['report_date']?>'>
<table class='admin_menu2' style='width:800px'>
<tr>
	<td valign='top'>
	
<?
	if(isset($_POST['build_report'])) 
	{
		$search_term=$_POST['maintenance_desc'];
		$search_mode=$_POST['search_notes_files'];	
		$search_sort_by=$_POST['search_sort_by'];
		
		$date_start = strtotime(date("m/1/Y", strtotime($_POST['report_date'])));
		$days_in_month = date("t", $date_start);
		$date_end = strtotime(date("m/".$days_in_month."/Y", $date_start));
		
		$use_start=date("Y-m-d", $date_start)." 00:00:00";
		$use_end=date("Y-m-d", $date_end)." 23:59:59";		
		
		if($search_mode==2 || $search_mode==0)
		{
		?>	
     		<center>	<div class='header'>Files</div> </center><br>
     		<table class='admin_menu2' style='width:750px'>
          	<tr>
          		<td valign='top' nowrap><b>File ID</b></td>
          		<td valign='top'><b>File Name</b></td>
          		<td valign='top' nowrap><b>File Size</b></td>
          		<td valign='top' nowrap><b>Ext.</b></td>
          		<td valign='top' nowrap><b>Descriptor</b></td>
          		<td valign='top' nowrap><b>Date</b></td>
          		<td valign='top'><b></b> <?= show_help('search_form.php','Search Form Files') ?></td>	
          	</tr>
		<?
			$mrr_adder="";
			if(trim($search_term)!="")			$mrr_adder.=" and (fname LIKE '%".sql_friendly($search_term)."%' or descriptor LIKE '%".sql_friendly($search_term)."%')";
			if($use_start!="1969-12-01 00:00:00")	$mrr_adder.=" and linedate_added>='".sql_friendly($use_start)."'";
			if($use_end!="1969-12-31 23:59:59")	$mrr_adder.=" and linedate_added<='".sql_friendly($use_end)."'";		
				
			$mrr_order="order by id asc,linedate_added asc,fname asc";									//ID
			if($search_sort_by==1)	$mrr_order="order by fname asc,linedate_added asc,id asc";				//Name
			if($search_sort_by==2)	$mrr_order="order by linedate_added asc,fname asc,id asc";				//Date
			if($search_sort_by==3)	$mrr_order="order by file_ext asc,fname asc,linedate_added asc,id asc";	//Type
				
     		$sql = "
     				select *				
     				from attachments
     				where deleted='0'
     					".$mrr_adder."
     				".$mrr_order."				
     			";
     		$data = simple_query($sql);
     		while($row = mysqli_fetch_array($data)) 
     		{
     			$file_id=$row['id'];
     			$filename=$row['fname'];
     			$filesize=$row['filesize'];
     			$ext=$row['file_ext'];
     			$desc=$row['descriptor'];
     			$dater=date("m/d/Y", strtotime($row['linedate_added']));     			
     			
     			//output and formatting     			
     			$f_link="<a href='/documents/".$filename."' title='Click to open this file.' target='_blank'><b>".$filename."</b></a>";
     			$trash='<a href="javascript:confirm_del_file('.$file_id.')"><img src="images/delete_sm.gif" border="0"></a>';
     						
     			echo "<tr class='file_search_$file_id'>
     					<td valign='top' nowrap>$file_id</td>
     					<td valign='top'>$f_link</td>
     					<td valign='top' nowrap>$filesize</td>
     					<td valign='top' nowrap>$ext</td>
     					<td valign='top' nowrap>$desc</td>
     					<td valign='top' nowrap>$dater</td>
     					<td valign='top' nowrap>$trash</td>	
     				</tr>";		
     		}
		?>
			</table>
		<?
		}
		if($search_mode==1 || $search_mode==0)
		{
		?>
     		<br>
     		<center>	<div class='header'>Notes</div> </center>
     		<br>
     	
     		<table class='admin_menu2' style='width:750px'>
          	<tr>
          		<td valign='top' nowrap><b>Note ID</b></td>
          		<td valign='top' nowrap><b>Type</b></td>
          		<td valign='top'><b>Note</b></td>
          		<td valign='top' nowrap><b>Date</b> <?= show_help('search_form.php','Search Form Notes') ?></td>		
          	</tr>
		<?
			
			$mrr_adder="";
			if(trim($search_term)!="")			$mrr_adder.=" and note LIKE '%".sql_friendly($search_term)."%'";
			if($use_start!="1969-12-01 00:00:00")	$mrr_adder.=" and linedate_added>='".sql_friendly($use_start)."'";
			if($use_end!="1969-12-31 23:59:59")	$mrr_adder.=" and linedate_added<='".sql_friendly($use_end)."'";	
			
			$mrr_order="order by id asc,linedate_added asc,note asc";									//ID
			if($search_sort_by==1)	$mrr_order="order by note asc,linedate_added asc,id asc";				//Name
			if($search_sort_by==2)	$mrr_order="order by linedate_added asc,note asc,id asc";				//Date
			if($search_sort_by==3)	$mrr_order="order by note_type_id asc,note asc,linedate_added asc,id asc";	//Type
			
			$sql = "
     				select *				
     				from notes_main
     				where deleted='0'     					 
     					".$mrr_adder."
     				".$mrr_order."			
     			";
     		$data = simple_query($sql);
     		while($row = mysqli_fetch_array($data)) 
     		{
     			$note_id=$row['id'];
     			$note=$row['note'];
     			$type=$row['note_type_id'];
     			$dater=date("m/d/Y", strtotime($row['linedate_added']));
     			     			
     			//output and formatting
     			$typer=mrr_type_decoder($type);
     			$trash='<a href="javascript:confirm_del_note('.$note_id.')"><img src="images/delete_sm.gif" border="0"></a>';
     					
     			echo "<tr class='note_search_$note_id'>
     					<td valign='top' nowrap>$note_id</td>
     					<td valign='top' nowrap>$typer</td>
     					<td valign='top'>$note</td>
     					<td valign='top' nowrap>$dater</td>
     					<td valign='top' nowrap>$trash</td>		
     				</tr>
     				<tr class='note_search_$note_id'>
     					<td valign='top' colspan='5'><hr></td>	
     				</tr>";	
     		}
			?>
			</table>
			<?
		}//end if for Mode 2
		
	}//end report builder if
?>	
	</td>
</tr>
</table>

</form>
<script type='text/javascript'>
	$('.input_date').datepicker();
	
	function confirm_del_note(myid) {
		$.prompt("Are you sure you want to delete this note entry?", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {
					$.ajax({
          			   type: "POST",
          			   url: "ajax.php?cmd=mrr_kill_search_note",
          			   data: {
          			   		"note_id":myid,
          			   		},
          			   dataType: "xml",
          			   cache:false,
          			   success: function(xml) {
          			   		newid=$(xml).find('NoteID').text();
               				if(newid > 0)
               				{
               					$.noticeAdd({text: "Note entry has been removed."});
               					
               					$('.note_search_'+myid+'').html('');			
               				} 
          			   }
          			});	
				}
			}
		});
	}
	function confirm_del_file(myid) {
		$.prompt("Are you sure you want to delete this file from your uploads?", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {
					$.ajax({
          			   type: "POST",
          			   url: "ajax.php?cmd=mrr_kill_search_file",
          			   data: {
          			   		"file_id":myid,
          			   		},
          			   dataType: "xml",
          			   cache:false,
          			   success: function(xml) {
          			   		newid=$(xml).find('FileID').text();
               				if(newid > 0)
               				{
               					$.noticeAdd({text: "File has been removed."});
               					
               					$('.file_search_'+myid+'').html('');			
               				} 
          			   }
          			});	
				}
			}
		});

	}
	
</script>
<? include('footer.php') ?>