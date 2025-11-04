<? include('application.php') ?>
<? $admin_page = 1 ?>
<? $usetitle = "Alert Call Settings" ?>
<?	
	if(isset($_POST['update'])) 
	{
		if(!isset($_POST['tot_users']))		$_POST['tot_users']=0;
		
		for($i=0; $i < $_POST['tot_users']; $i++)
		{		
			if(isset($_POST["user_id_".$i.""]))
			{
     			
     			$user_id= $_POST["user_id_".$i.""];
     			$alert_email=$_POST["alert_email_".$i.""];
     			$alert_phone=$_POST["alert_phone_".$i.""];
     			$alert_priority=$_POST["alert_priority_".$i.""];
     						
     			$sql = "
     				update users
     				
     				set alert_call_email='".sql_friendly($alert_email)."',
     					alert_call_priority='".sql_friendly($alert_priority)."',
     					alert_call_phone='".sql_friendly($alert_phone)."'
     
     				where id = '".sql_friendly($user_id)."'
     			";
     			simple_query($sql);
     			
     			
			}
			
			$mrr_activity_log_notes.="Updated user Alert Call settings info. ";	
		}
	}

	$sql = "
		select *		
		from users
		where deleted = 0
			and active>0
		order by username
	";
	$data = simple_query($sql);
	
	$mrr_activity_log_notes.="Viewed list of users for Alert Call. ";	
?>
<? include('header.php') ?>

<table style='text-align:left'>
<tr>
	<td valign='top'>
		<div class='section_header'><?= $usetitle ?></div><br>
		<b>Email/Call/Text message sent out from <?= $defaultsarray['alert_call_priority_time_from'] ?> to <?= $defaultsarray['alert_call_priority_time_to'] ?> each day or night within the time range.</b><br><br>
		<form action="<?=$SCRIPT_NAME?>" method="post">
		<table class='admin_menu1' width='1200'>
		<tr>
			<td valign='top'><b>ID</b></td>
			<td valign='top'><b>Username</b></td>
			<td valign='top'><b>First Name</b></td>
			<td valign='top'><b>Last Name</b></td>
			<td valign='top'><b>Email</b></td>
			<td valign='top'><b>Alert Email</b></td>
			<td valign='top'><b>Alert Phone</b></td>
			<td valign='top'><b>Alert Priority</b></td>
		</tr>
		<?
		$cntr=0;
		while($row = mysqli_fetch_array($data)) 
		{
			echo "
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'>".$row['id']."<input type='hidden' name='user_id_".$cntr."' id='user_id_".$cntr."' value='".$row['id']."'></td>
				<td valign='top'>".$row['username']."</td>
				<td valign='top'>".$row['name_first']."</td>
				<td valign='top'>".$row['name_last']."</td>
				<td valign='top'>".$row['email']."</td>
				<td valign='top'><input type='text' name='alert_email_".$cntr."' id='alert_email_".$cntr."' value='".$row['alert_call_email']."' class='input_normal'></td>
				<td valign='top'><input type='text' name='alert_phone_".$cntr."' id='alert_phone_".$cntr."' value='".$row['alert_call_phone']."'></td>
				<td valign='top'>".mrr_alert_call_priority_select_box("alert_priority_".$cntr."",$row['alert_call_priority'])."</td>
			</tr>			
			";
			$cntr++;
		}
		?>
		<tr>
			<td colspan='8' align='center'>
				<input type="submit" name='update' id='update' value="Update">
				<input type='hidden' name='tot_users' id='tot_users' value='<?= $cntr ?>'>
			</td>
		</tr>		
		</table>
		</form>
	</td>
</tr>
</table>

<? include('footer.php') ?>

<script type='text/javascript'>
	
</script>
