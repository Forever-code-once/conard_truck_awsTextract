<? include('application.php') ?>
<? $admin_page = 1 ?>
<? 
	$usetitle = "PN Canned Messages"; 
	
	if(isset($_GET['message_id']))		$_POST['message_id']=$_GET['message_id'];	
	
	if(!isset($_POST['message_id']))		$_POST['message_id']=0;
	if(!isset($_POST['esubject']))		$_POST['esubject']="";
	if(!isset($_POST['emessage']))		$_POST['emessage']="";
	if(!isset($_POST['active']))			$_POST['active']=0;	
	
	if(isset($_GET['delid'])) 
	{
		$sql = "
			update truck_tracking_canned_message
			set deleted = '1'
			
			where id = '$_GET[delid]'
		";
		simple_query($sql);	
		$_POST['message_id']=0;
	}
	
	if(isset($_POST['update_message']))
	{
		$user_id=0;
		if(isset($_SESSION['user_id']))	$user_id=$_SESSION['user_id'];
		
		if($_POST['message_id'] == 0)
		{
			$sql="
				insert into truck_tracking_canned_message
					(id,
					linedate_added,
					user_id,
					active,
					deleted,
					canned_subject,
					canned_message)
				values
					(NULL,
					NOW(),
					'".sql_friendly($user_id)."',
					'".($_POST['active'] > 0 ? '1' : '0')."',
					0,
					'".sql_friendly($_POST['esubject'])."',
					'".sql_friendly($_POST['emessage'])."')					
			";
			simple_query($sql);	
			$_POST['message_id']=mysqli_insert_id($datasource);	
		}
		else
		{
			$sql="
				update truck_tracking_canned_message set
					active='".($_POST['active'] > 0 ? '1' : '0')."',
					canned_subject='".sql_friendly($_POST['esubject'])."',
					canned_message='".sql_friendly($_POST['emessage'])."'	
				where id='".sql_friendly($_POST['message_id'])."'
			";
			simple_query($sql);							
		}		
		
		header("Location: ".$SCRIPT_NAME."?message_id=".$_POST['message_id']."");
		die();		
	}	
	
	$sql="
		select * 
		from truck_tracking_canned_message 
		where id='".sql_friendly($_POST['message_id'])."'
	";
	$data=simple_query($sql);	
	if($row=mysqli_fetch_array($data))
	{
		$_POST['esubject']=$row['canned_subject'];		
		$_POST['emessage']=$row['canned_message'];	
		$_POST['active']=$row['active'];		
	}
	
	$sql2="
		select truck_tracking_canned_message.*,
			users.username
		from truck_tracking_canned_message
			left join users on users.id=truck_tracking_canned_message.user_id
		where truck_tracking_canned_message.deleted=0
		order by truck_tracking_canned_message.active desc,truck_tracking_canned_message.canned_subject asc
	";
	$data2=simple_query($sql2);	
?>
<? include('header.php') ?>
<form name='peoplenet_canned_msg' action='<?= $SCRIPT_NAME ?>' method='post'>
	<input type='hidden' name='message_id' id='message_id' value='<?= $_POST['message_id'] ?>'>

<table border='0'>
	<tr>
		<td valign='top'>
			<table class='admin_menu1'>
               	<tr>
               		<td valign='top' colspan='5'><a href="<?=$SCRIPT_NAME ?>?message_id=0">Add New Canned Message</a></td>
               	</tr>
               	<tr>
               		<td valign='top'><b>Active</b></td>
               		<td valign='top'><b>Subject</b></td>
               		<td valign='top'><b>Created On</b></td>
               		<td valign='top'><b>Created by</b></td>
               		<td valign='top'><b></b></td>
               	</tr>
               	<? 
               		while($row2 = mysqli_fetch_array($data2)) 
               		{
               			$id=$row2['id'];
               			$esubject=$row2['canned_subject'];		
               			$emessage=$row2['canned_message'];	
               			$active=$row2['active'];
               			$linedate_added=date("m/d/Y",strtotime($row2['linedate_added']));
               			$user_id=$row2['user_id'];
               			$user_name=$row2['username'];
               			
               			$linker="<a href='peoplenet_canned_messages.php?message_id=".$id."'>".$esubject."</a>";
               			$actor="";
               			if($active>0)	$actor="Active";			
               			
               			echo "
               				<tr>
               					<td valign='top'>".$actor."</td>
               					<td valign='top'>".$linker."</td>
               					<td valign='top'>".$linedate_added."</td>
               					<td valign='top'>".$user_name."</td>
               					<td valign='top'><a href='javascript:confirm_del_canned_msg(".$row2['id'].")'><img src='images/delete_sm.gif' border='0'></a></td>
               				</tr>
               			";
               		} 
               	?>
               </table>
		</td>
		<td valign='top'>
			&nbsp;
		</td>
		<td valign='top'>
			<table class='admin_menu2' style='width:800px;'>
                    <tr>
                    	<td valign='top'><div class='section_heading'>Canned Message</div></td>
                    	<td valign='top' align='right'><a href="<?= $SCRIPT_NAME ?>?message_id=0">Add New Canned Message</a></td>
                    </tr>
                    <tr>
                    	<td valign='top'>Subject <?= show_help('peoplenet_canned_messages.php','Message Subject') ?></td>
                    	<td valign='top'><input type='text' name='esubject' id='esubject' value="<?= $_POST['esubject'] ?>" class='mrr_text_input'></td>
                    </tr>
                    <tr>
                    	<td valign='top'>For Message  <?= show_help('peoplenet_canned_messages.php','Message Text') ?></td>
                    	<td valign='top'><textarea name='emessage' id='emessage' rows='10' cols='70' wrap='virtual'><?= $_POST['emessage'] ?></textarea></td>
                    </tr>
                    <tr>
                    	<td valign='top'><label for='active'><b>Active</b></label>  <?= show_help('peoplenet_canned_messages.php','Active') ?></td>
                    	<td valign='top'><input type='checkbox' name='active' id='active' value='1' <? if($_POST['active'] > 0) echo 'checked'?>> Check to allow dispatch to use this canned message.</td>
                    </tr>
                    <tr>
                    	<td>&nbsp;</td>
                    	<td><center><input type="submit" value="Update" name='update_message' id='update_message' class='standard12'></center></td>
                    </tr>
               </table>
		</td>
	</tr>
</table>
</form>
<script type='text/javascript'>	
	$().ready(function() 
	{			
				
	});	
	
	function confirm_del_canned_msg(id) {
		if(confirm("Are you sure you want to delete this PeopleNet canned message?")) {
			window.location = "<?=$SCRIPT_NAME?>?delid=" + id;
		}
	}	
</script>
<? include('footer.php') ?>