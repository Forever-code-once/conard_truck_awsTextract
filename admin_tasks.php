<? include('application.php') ?>
<? $admin_page = 1 ?>
<?		
	if(isset($_GET['did'])) {
		$sql = "
			update dispatcher_tasks set			
				deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_delete = simple_query($sql);
	}
	if(isset($_GET['delid'])) {
		$sql = "
			update dispatcher_tasks_work set			
				deleted = 1
			where id = '".sql_friendly($_GET['delid'])."'
		";
		$data_delete = simple_query($sql);
	}
	
	if(isset($_GET['id']))				$_POST['id']=$_GET['id'];
	if(!isset($_POST['id']))				$_POST['id']=0;	
	
	if(!isset($_POST['assigned_to_id']))	$_POST['assigned_to_id']=0;
	if(!isset($_POST['task_act']))		$_POST['task_act']=0;
	
	
	if(isset($_POST['updater'])) 
	{				
		//new item
		$time_starter=date("Y-m-d H:i:s",strtotime($_POST['task_start']." ".$_POST['task_start_time']));
		$time_ender=date("Y-m-d H:i:s",strtotime($_POST['task_complete']." ".$_POST['task_complete_time']));
		if(substr_count($time_starter,"12/31/1969") > 0)		$time_starter="0000-00-00 00:00:00";
		if(substr_count($time_ender,"12/31/1969") > 0)		$time_ender="0000-00-00 00:00:00";
		
		if($_POST['id']==0)
		{				
			$sqlu = "
				insert into dispatcher_tasks
					(id,
					linedate_added, 
					linedate_updated, 
					deleted,
                      	active,
                      	created_by_id,
					assigned_to_id,
					task,
					freq_days,
					linedate_start, 
					linedate_complete)
				values
					(NULL,
					NOW(),
					NOW(),
					0,
					'".sql_friendly((int)$_POST['task_act'])."',
					'".sql_friendly($_SESSION['user_id'])."',
					'".sql_friendly($_POST['task_worker'])."',
					'".sql_friendly($_POST['task_desc'])."',
					'".sql_friendly($_POST['task_freq'])."',
					'".$time_starter."',
					'".$time_ender."')
				";
			simple_query($sqlu);
			$_POST['id']=mysqli_insert_id($datasource);
			
		}	
		else
		{
			if(!isset($_POST['task_act']))		$_POST['task_act']=0;
			$sqlu = "
				update dispatcher_tasks set
					linedate_updated=NOW(), 
                      	active='".sql_friendly((int)$_POST['task_act'])."',
					assigned_to_id='".sql_friendly($_POST['task_worker'])."',
					task='".sql_friendly($_POST['task_desc'])."',
					freq_days='".sql_friendly($_POST['task_freq'])."',
					linedate_start='".$time_starter."', 
					linedate_complete='".$time_ender."'
				where id='".sql_friendly($_POST['id'])."'
				";
			simple_query($sqlu);	
		}
		header("Location: $SCRIPT_NAME?id=".$_POST['id']);
		die();
	}

	$sql = "
		select dispatcher_tasks.*,
			(select CONCAT(name_first,' ',name_last) from users where users.id=dispatcher_tasks.created_by_id) as creator,
			(select CONCAT(name_first,' ',name_last) from users where users.id=dispatcher_tasks.assigned_to_id) as worker		
		from dispatcher_tasks
		where dispatcher_tasks.deleted = 0
		order by dispatcher_tasks.active desc,dispatcher_tasks.linedate_start asc,dispatcher_tasks.linedate_complete asc,dispatcher_tasks.id asc
	";
	$data_all = simple_query($sql);	
	
	$cur_user="";
	$sqlu = "
		select *
		
		from users
		where id = '".sql_friendly($_SESSION['user_id'])."'
	";
	$datau = simple_query($sqlu);	
	if($rowu=mysqli_fetch_array($datau))
	{
		$cur_user=trim($rowu['name_first']." ".$rowu['name_last']);
	}
	
	$sqlu = "
		select *
		
		from users
		where deleted = 0 
			and active='1'
		order by name_last asc,name_first asc,id asc
	";
	$datau = simple_query($sqlu);	
	
	$usetitle="Dispatcher Tasks";
?>
<? include('header.php') ?>
<div style='margin-left:25px;'>
<form action="<?=$SCRIPT_NAME ?>" method="post">

<div class='standard18'><b><?=$usetitle?></b>  <a href='admin_tasks.php'>Add New Task</a></div><br>

<table class='admin_menu1' style='text-align:left; width:1500px;'>
<tr>
	<td valign='top'><b>ID</b></td>
	<td valign='top'><b>Added</b></td>
	<td valign='top'><b>Updated</b></td>
	<td valign='top'><b>Created by</b></td>
	<td valign='top'><b>Assigned to</b></td>	
	<td valign='top'><b>Task</b></td>	
	<td valign='top'><b>Freq Days</b></td>
	<td valign='top'><b>Start By</b></td>
	<td valign='top'><b>Start Time</b></td>
	<td valign='top'><b>Complete By</b></td>
	<td valign='top'><b>Complete Time</b></td>	
	<td valign='top'><b>Status</b></td>
	<td valign='top'><b>&nbsp;</b></td>
</tr>
<?	
	if($_POST['id'] > 0)
	{
		$id_val=$_POST['id'];
		
		$sql = "
			select *			
			from dispatcher_tasks
			where id='".sql_friendly($_POST['id'])."'
		";
		$data = simple_query($sql);
		$row=mysqli_fetch_array($data);
		
		$added_val=date("m/d/Y H:i",strtotime($row['linedate_added']));
		$update_val=date("m/d/Y H:i",strtotime($row['linedate_updated']));
		
		$_POST['task_act']=$row['active'];
		$_POST['task_worker']=$row['assigned_to_id'];
		$_POST['task_desc']=$row['task'];
		$_POST['task_freq']=$row['freq_days'];
		$_POST['task_start']=date("m/d/Y",strtotime($row['linedate_start']));
		$_POST['task_start_time']=date("H:i",strtotime($row['linedate_start']));
		$_POST['task_complete']=date("m/d/Y",strtotime($row['linedate_complete']));
		$_POST['task_complete_time']=date("H:i",strtotime($row['linedate_complete']));
		
		if(substr_count($_POST['task_start'],"12/31/1969") > 0)	{	$_POST['task_start']="";			$_POST['task_start_time']="";		}
		if(substr_count($_POST['task_complete'],"12/31/1969") > 0)	{	$_POST['task_complete']="";		$_POST['task_complete_time']="";	}
	}
	else
	{
		$id_val="New";
		$added_val="&nbsp;";
		$update_val="&nbsp;";
		$_POST['task_act']=1;
		$_POST['task_worker']=0;
		$_POST['task_desc']='';
		$_POST['task_freq']=0;
		$_POST['task_start']='';
		$_POST['task_start_time']='';
		$_POST['task_complete']='';	
		$_POST['task_complete_time']='';	
	}
	
	$selbox.="<select name='task_worker' id='task_worker'>";
	$sel="";
	if($_POST['task_worker']==0)	$sel=" selected";	
	$selbox.="<option value='0'".$sel.">All</option>";	
	
	while($rowu=mysqli_fetch_array($datau))
	{
		$sel="";
		if($_POST['task_worker']==$rowu['id'])	$sel=" selected";
		
		$selbox.="<option value='".$rowu['id']."'".$sel.">".trim($rowu['name_first']." ".$rowu['name_last'])."</option>";	
	}
	$selbox.="</select>";
	
	echo "
			<tr>
				<td valign='top'>".$id_val."<input type='hidden' name='id' id='id' value='".$_POST['id']."'></td>
				<td valign='top'>".$added_val."</td>
				<td valign='top'>".$update_val."</td>
				<td valign='top'>".$cur_user."</td>
				<td valign='top'>".$selbox."</td>
				<td valign='top'><input type='text' id='task_desc' name='task_desc' value=\"".$_POST['task_desc']."\" style='width:300px;'></td>
				<td valign='top'><input type='text' id='task_freq' name='task_freq' value=\"".$_POST['task_freq']."\" style='width:50px;'></td>				
				<td valign='top'><input type='text' id='task_start' name='task_start' value=\"".$_POST['task_start']."\" style='width:100px;' class='mrr_date_picker'></td>
				<td valign='top'><input type='text' id='task_start_time' name='task_start_time' value=\"".$_POST['task_start_time']."\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='task_complete' name='task_complete' value=\"".$_POST['task_complete']."\" style='width:100px;' class='mrr_date_picker'></td>
				<td valign='top'><input type='text' id='task_complete_time' name='task_complete_time' value=\"".$_POST['task_complete_time']."\" style='width:100px;'></td>				
				<td valign='top'><input type='checkbox' id='task_act' name='task_act' value=\"1\"".($_POST['task_act'] > 0 ? " checked" : "")."></td>
				<td valign='top'>&nbsp;</td>				
			</tr>
		";
	
	
	while($row=mysqli_fetch_array($data_all))
	{
		$start1=date("m/d/Y",strtotime($row['linedate_start']));
		$start2=date("H:i",strtotime($row['linedate_start']));
		$end1=date("m/d/Y",strtotime($row['linedate_complete']));
		$end2=date("H:i",strtotime($row['linedate_complete']));
		
		if(substr_count($start1,"12/31/1969") > 0)	$start1="";	
		if(substr_count($start2,"12/31/1969") > 0)	$start2="";
		if(substr_count($end1,"12/31/1969") > 0)	$end1="";	
		if(substr_count($end2,"12/31/1969") > 0)	$end2="";
		
		echo "
			<tr>
				<td valign='top'><a href='admin_tasks.php?id=".$row['id']."'>".$row['id']."</a></td>
				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_updated']))."</td>
				<td valign='top'>".$row['creator']."</td>
				<td valign='top'>".$row['worker']."</td>
				<td valign='top'>".$row['task']."</td>
				<td valign='top'>".$row['freq_days']."</td>				
				<td valign='top'>".$start1."</td>
				<td valign='top'>".$start2."</td>
				<td valign='top'>".$end1."</td>
				<td valign='top'>".$end2."</td>				
				<td valign='top'>".($row['active'] > 0 ? "Active" : "")."</td>
				<td valign='top'><a href='javascript:confirm_delete(".$row['id'].")'><img src='images/delete_sm.gif' border='0'></a></td>				
			</tr>
		";	
		
		$task_work_list=mrr_get_dispatcher_task_work_list($row['id'],1);	
		echo "
			<tr>
				<td valign='top' colspan='13'>".$task_work_list."</td>				
			</tr>
		";	
	}
?>
<tr>
	<td valign='top' colspan='13' align='center'><input type='submit' name='updater' value='Update'></td>
</tr>
</table>

</div>
<script type='text/javascript'>
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete this task?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}
	function confirm_work_delete(id) {
		if(confirm("Are you sure you want to delete this task work?")) {
			window.location = '<?=$SCRIPT_NAME?>?delid=' + id;
		}
	}
	
	function mrr_dispatch_task_work_status(id,idstatus)
	{
		// save task work info
		$.ajax({
			url: "ajax.php?cmd=mrr_dispatcher_tasks_work_status",
			type: "post",
			dataType: "xml",
			data: {
				task_id: id,
				status_val: idstatus					
			},
			error: function() {
				$.prompt("General Error, could not update dispatcher task work.");
			},
			success: function(xml) {
				location.reload();				
			}
		});	
	}
	
	
	$().ready(function() 
	{			
		
	});	
					
	$('.mrr_date_picker').datepicker();
	
     //$(".tablesorter").tablesorter({textExtraction: 'complex'});
	
	/*
	function mrr_verify_user_unique(myid)
	{		
		$('#mrr_naming_message').html('');	
		mrr_lab="Driver";
		mrr_lab2="name_driver_";
		mrr_code=1;
		
		new_name1=$('#'+mrr_lab2+'first').val();
		new_name2=$('#'+mrr_lab2+'last').val();
		new_name=new_name1+' '+new_name2;
		
		$.ajax({
			url: "ajax.php?cmd=mrr_verify_item_name",
			type: "post",
			dataType: "xml",
			data: {
				"name": new_name,
				"mode": mrr_code,
				"id": myid
			},
			error: function() {
				$.prompt("Error: Cannot check for duplication of "+mrr_lab+" "+new_name+".");
				//$('#'+mrr_lab2+'first').val('');
				//$('#'+mrr_lab2+'last').val('');
			},
			success: function(xml) {				
				mytxt=$(xml).find('mrrTab').text();
				if(mytxt=="")
				{					
					$('#mrr_naming_message').html(''+mrr_lab+' is valid.');	
					$('#mrr_naming_message').css('color','blue');		
				}
				else
				{
					$.prompt( ""+ mytxt +"" );
					$('#'+mrr_lab2+'first').val(''+new_name1+'.');
					$('#'+mrr_lab2+'last').val(''+new_name2+'.');
					$('#mrr_naming_message').html(''+mrr_lab+' must be unique.');	
					$('#mrr_naming_message').css('color','red');									
				}				
			}
		});	
		
	}
	*/	
</script>
</form>
<? include('footer.php') ?>
