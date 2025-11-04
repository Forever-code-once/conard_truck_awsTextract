<?
$usetitle="Dispatcher Task Work";  
?>
<? include('header.php') ?>
<?
	$myuser_id=$_SESSION['user_id'];
	$mrrsel=mrr_dispatcher_task_work_code_select_box('task_work_code',0);
	$mrrday=date("m/d/Y");
	$mrrtime=date("H:i");
?>
<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3><?= $usetitle ?></h3>
	<div style='color:purple;'>
		
	</div>
	<div id='task_listing'></div>
</div>
<div style='clear:both'></div>


</form>
<script type='text/javascript'>
	//$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
		//$('.datepicker').datepicker();
		send_email_hot_tracking(<?= $myuser_id ?>);
		
		
		setTimeout("location.reload();", (60 * 1000));		//ten minutes...600 seconds...1000=1 second
	});
	
	function send_email_hot_tracking(userid)
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_dispatcher_tasks_display",
		   data: {
		   		"user_id":userid
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		txt="Loading...";		   		
		   		if($(xml).find('mrrTab').text()!="")
		   		{			   				
		   			txt=$(xml).find('mrrTab').text();
		   		}
		   		$('#task_listing').html(txt);
		   }	
		});
	}
	
	function mrr_update_task_completion(id)
	{
		alert("Adding work for task "+id+".");	
		
		var sel_bx="";
		/*
		//Notes:   op_id=invoice_id, mode1=invoice,  mode2=email,  email_to=field_name,  ''=preselected one...default to company
		$.ajax({
     		url: "ajax.php?cmd=mrr_change_email_contact_box",
     		data: { 
     				"op_id":id,
     				"moder1":1,
     				"moder2":1,
     				"field_name":"email_to",
     				"pre_selected":""
     			},
     		type: "POST",
			cache:false,
			async: false,
			dataType: "xml",
     		error: function() {
				$.prompt("General Error finding email/fax info for this form");
			},
			success:function(xml) { 
				if($(xml).find("html").text() != '')		sel_bx=$(xml).find("html").text();
			}
     	});
		*/
		var txt = "";
		txt = txt + "<br><br>Task Date and Time<br>";
		txt = txt + "<input name='task_work_date' id='task_work_date' value='<?= $mrrday ?>' style='width:100px;' class='mrr_date_picker'> ";
		txt = txt + "<input name='task_work_time' id='task_work_time' value='<?= $mrrtime ?>' style='width:100px;'>";
		txt = txt + "<br><br>Task Completion Code<br>";
		txt = txt + "<?= $mrrsel ?>";
		txt = txt + "<br><br>Task Work Notes<br>";
		txt = txt + "<textarea name='task_work_notes' id='task_work_notes' style='width:400px;height:75px'></textarea>";

		function mycallbackform(v,m,f)
		{
			if(v) 
			{				
				// save task work info
				$.ajax({
					url: "ajax.php?cmd=mrr_dispatcher_tasks_work_update",
					type: "post",
					dataType: "xml",
					data: {
						task_code: f.task_work_code,
						task_date: f.task_work_date,
						task_time: f.task_work_time,
						task_note: f.task_work_notes,
						task_id: id,
						user_id: <?= $myuser_id ?>						
					},
					error: function() {
						$.prompt("General Error, could not update dispatcher task.");
					},
					success: function(xml) {
						//$.noticeAdd({text: "Success - Task updated."});
						//$('#to_be_emailed').attr('checked','checked');
						
						send_email_hot_tracking(<?= $myuser_id ?>);
					}
				});
				
			} // end of if for type of send (now or later)
		}
		
		function loadedfunction() 
		{
			$('.mrr_date_picker').datepicker();
			$('#task_work_date').focus();
		}
		
		$.prompt(txt,{
		      callback: mycallbackform,
		      overlayspeed: 'fast',
		      loaded: loadedfunction,
		      buttons: { Ok: true, Cancel: false }
		});
	}
	
</script>
<? include('footer.php') ?>