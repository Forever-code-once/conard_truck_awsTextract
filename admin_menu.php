<? include('application.php') ?>
<? $admin_page = 1 ?>
<? $usetitle = "Admin Menu" ?>
<? include('header.php') ?>
<?	
	$msg1="";
	$msg2="";
	if(isset($_POST['update_menu_groups']))
	{
		if(!isset($_POST['access_tot']))	$_POST['access_tot']=0;
		for($i=0; $i < $_POST['access_tot']; $i++)
		{
			$id= $_POST["access_id_".$i.""];
			$lev= $_POST["access_".$i.""];
			
			$sql="
				update user_menu_group set
					access_level='".(int)$lev ."'
				where id='".(int) $id."'
			";
			simple_query($sql);
		}
		
		$msg1="Updated Menu Groups Successfully!";
	}
	
	if(isset($_POST['update_menu_items']))
	{
		if(!isset($_POST['access_sub_tot']))	$_POST['access_sub_tot']=0;
		for($i=0; $i < $_POST['access_sub_tot']; $i++)
		{
			$id= $_POST["access_sub_id_".$i.""];
			$lev= $_POST["access_sub_".$i.""];
			$grp= $_POST["access_sub_".$i."_grp"];
			
			$sql="
				update user_menu_access set
					access_level='".(int)$lev ."',
					group_id='".(int)$grp ."'
				where id='".(int) $id."'
			";
			simple_query($sql);
		}
		
		$msg2="Updated Menu Items Successfully!";
	}
	
	$sql="
		select *
		from user_menu_group 
		where deleted=0
	";
	$data = simple_query($sql);
	$cntr=0;
	
	//echo "<br><b>User ID:</b> ".$_SESSION['user_id']."";
	//echo "<br><b>Access Level:</b> ".$_SESSION['user_access_level']."";
	//echo "<br><b>Menu Item:</b> ".$SCRIPT_NAME."";
	//echo "<br><b>Access Granted:</b> ".mrr_get_user_menu_access_level($SCRIPT_NAME,$_SESSION['user_id'])."";
	
?>
<form action="<?=$SCRIPT_NAME?>" method="post">
<table style='text-align:left'>
<tr>
	<td valign='top'>
		<table class='admin_menu1' width='500'>
		<tr>
			<td colspan='3'><b><?= $msg1 ?></b></td>
		</tr>
		<tr>
			<td colspan='3' align='center'><b>Primary Menu Access</b></td>
		</tr>
		<tr>
			<td colspan='3'>Control what main menu sections users can see based on Access Level.</td>
		</tr>
		<tr>
			<td width='100'><b>ID</b></td>
			<td><b>Menu Group</b></td>
			<td width='100'><b>Access Level</b></td>
		</tr>
		<?
		while($row = mysqli_fetch_array($data)) 
		{
			echo "
			<tr>
				<td><input type='hidden' name='access_id_".$cntr."' value='".$row['id']."'>".$row['id']."</td>
				<td>".$row['menu_name']."</td>
				<td><input type='text' name='access_".$cntr."' value='".$row['access_level']."' style='width:50px; text-align:right;'></td>
			</tr>";
			$cntr++;
		} 
		?>
		<tr>
			<td colspan='3' align='center'>
				<input type="submit" name="update_menu_groups" value="Update Menu Groups">
				<input type="hidden" name="access_tot" value="<?=$cntr ?>">
			</td>
		</tr>
		</table>		
	</td>
	<td valign='top'>
	<?
	$cntr2=0;
	$sql2="
		select *
		from user_menu_access
		where deleted=0
		order by group_id asc,admin_url asc
	";
	$data2 = simple_query($sql2);
	?>	
		<table class='admin_menu1' width='600'>
		<tr>
			<td colspan='4'><b><?= $msg2 ?></b></td>
		</tr>
		<tr>
			<td colspan='4' align='center'><b>Menu Item Access</b></td>
		</tr>
		<tr>
			<td colspan='4'>Control what main menu items users can see based on Access Level.</td>
		</tr>
		<tr>
			<td width='100'><b>ID</b></td>
			<td><b>Menu Item URL</b></td>
			<td width='100'><b>Access Level</b></td>
			<td width='100'><b>Menu Group ID</b></td>
		</tr>
		<?
		while($row2 = mysqli_fetch_array($data2)) 
		{
			echo "
			<tr>
				<td><input type='hidden' name='access_sub_id_".$cntr2."' value='".$row2['id']."'>".$row2['id']."</td>
				<td>".$row2['admin_url']."</td>
				<td><input type='text' name='access_sub_".$cntr2."' value='".$row2['access_level']."' style='width:50px; text-align:right;'></td>
				<td><input type='text' name='access_sub_".$cntr2."_grp' value='".$row2['group_id']."' style='width:50px; text-align:right;'></td>
			</tr>";
			$cntr2++;
		} 
		?>
		<tr>
			<td colspan='4' align='center'>
				<input type="submit" name="update_menu_items" value="Update Menu Items">
				<input type="hidden" name="access_sub_tot" value="<?=$cntr2 ?>">
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

</form>
<? include('footer.php') ?>