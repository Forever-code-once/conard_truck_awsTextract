<? include('application.php') ?>
<? $usetitle = "Mini Menu Setup" ?>
<? include('header.php') ?>
<?	
	$use_admin_level=0;
	$cur_user=0;
	if(isset($_SESSION['user_id']))			$cur_user=$_SESSION['user_id'];
	
	$msg1="";
	if($cur_user > 0)
	{
		$use_admin_level=mrr_get_user_access_level($_SESSION['user_id']);	
		
		//mrr_get_user_menu_page_selector($field,$pre=0)
		//mrr_get_user_menu_page($id,$cd=0)	
		
		if(isset($_POST['update_menu_groups']))
		{
     		//add new one...
     		if((int) $_POST['item_page_0'] < 0)
     		{
     			$label=trim($_POST['label_0']);	
     			$tip=trim($_POST['tip_0']);	
     			$order=(int) $_POST['order_0'];
     			
     			if($label=="")		$label="---";			
     			
     			$sql="
     				insert into user_menu_custom
     					(id,
     					linedate_added,
     					user_id,
     					page_id,
     					label,
     					tool_tip,
     					zorder,
     					deleted)
     				values
     					(NULL,
     					NOW(),
     					'".sql_friendly($cur_user)."',
     					'-1',
     					'".sql_friendly($label)."',
     					'".sql_friendly($tip)."',
     					'".sql_friendly($order)."',
     					0)
     			";
     			simple_query($sql);	
     			
     			$msg1.="Added Spacer...";
     		}
     		elseif((int) $_POST['item_page_0'] > 0)
     		{
     			$label=trim($_POST['label_0']);	
     			$tip=trim($_POST['tip_0']);	
     			$order=(int) $_POST['order_0'];
     			
     			if($label=="")		$label=mrr_get_user_menu_page($_POST['item_page_0'],1);			
     			
     			$sql="
     				insert into user_menu_custom
     					(id,
     					linedate_added,
     					user_id,
     					page_id,
     					label,
     					tool_tip,
     					zorder,
     					deleted)
     				values
     					(NULL,
     					NOW(),
     					'".sql_friendly($cur_user)."',
     					'".sql_friendly($_POST['item_page_0'])."',
     					'".sql_friendly($label)."',
     					'".sql_friendly($tip)."',
     					'".sql_friendly($order)."',
     					0)
     			";
     			simple_query($sql);	
     			
     			$msg1.="Added <b>".$label."</b> Mini-Menu Item...";	
			}
		}	//end button click.
		
		
		///update the others
		for($i=1; $i <= $_POST['item_tot']; $i++)
		{
			$id= $_POST['item_id_'.$i.''];
			$label=trim($_POST['label_'.$i.'']);	
			$tip=trim($_POST['tip_'.$i.'']);	
			$order=(int) $_POST['order_'.$i.''];
			
			if($label=="")		$label=mrr_get_user_menu_page($_POST['item_page_'.$i.''],1);	
						
			$sql="
				update user_menu_custom set
					page_id='".sql_friendly($_POST['item_page_'.$i.''])."',
					label='".sql_friendly($label)."',
					tool_tip='".sql_friendly($tip)."',
					zorder='".sql_friendly($order)."'
				where id='".sql_friendly($id)."'
			";
			simple_query($sql);	
		}
		$msg1.="Updated <b>".$_POST['item_tot']."</b> Mini-Menu Items...";	
		
	}
	
	
	$sql="
		select *
		from user_menu_custom
		where user_id='".sql_friendly($cur_user)."'
			and deleted=0
		order by zorder asc,id asc
	";
	$data = simple_query($sql);
	$cntr=0;
?>
<form action="<?=$SCRIPT_NAME?>" name='mini_menu_form' method="post">
<table style='text-align:left' width='1500'>
<tr>
	<td valign='top'>
		<table class='admin_menu1' width='100%'>
		<tr>
			<td colspan='7'><b><?= $msg1 ?></b></td>
		</tr>
		<tr>
			<td colspan='7' align='center'><b><?=$usetitle ?></b></td>
		</tr>
		<tr>
			<td colspan='7'>This is your Custom Mini Menu. (You must be logged in to make your Mini-Menu.)</td>
		</tr>
		<tr>
			<td><b>ID</b></td>
			<td><b>Added</b></td>
			<td><b>Menu Page</b></td>
			<td><b>Custom Label</b></td>
			<td><b>Custom ToolTip</b></td>
			<td><b>Order</b></td>
			<td><b>Remove</b></td>
		</tr>
		<?
		while($row = mysqli_fetch_array($data)) 
		{			
			$cntr++;
			$bx1=mrr_get_user_menu_page_selector("item_page_".$cntr."",$row['page_id']);
						
			echo "
			<tr>
				<td><input type='hidden' name='item_id_".$cntr."' value=\"".$row['id']."\">".$row['id']."</td>
				<td>".date("Y-m-d",strtotime($row['linedate_added']))."</td>
				<td>".$bx1."</td>
				<td><input type='text' name='label_".$cntr."' value=\"".$row['label']."\" style='width:300px;'></td>
				<td><input type='text' name='tip_".$cntr."' value=\"".$row['tool_tip']."\" style='width:300px;'></td>				
				<td><input type='text' name='order_".$cntr."' value=\"".$row['zorder']."\" style='width:50px; text-align:right;'></td>
				<td><span class='alert' onClick='mrr_mini_menu_item_kill2(".$row['id'].");' style='cursor:pointer;'><b>X</b></span></td>
			</tr>
			";			
		} 
		
		//new entry form here....
		$bx1=mrr_get_user_menu_page_selector("item_page_0",0);
						
		echo "
			<tr>
				<td>New</td>
				<td>&nbsp;</td>
				<td>".$bx1."</td>
				<td><input type='text' name='label_0' value=\"\" style='width:300px;'></td>
				<td><input type='text' name='tip_0' value=\"\" style='width:300px;'></td>				
				<td><input type='text' name='order_0' value=\"".($cntr + 1)."\" style='width:50px; text-align:right;'></td>
				<td>&nbsp;</td>
			</tr>
		";
		
		?>
		<tr>
			<td colspan='7' align='center'>
				<input type="submit" name="update_menu_groups" value="Update Menu Items">
				<input type="hidden" name="item_tot" value="<?=$cntr ?>">
			</td>
		</tr>
		</table>		
	</td>
	<td valign='top'>
		<b>Preview Mini-Menu</b><br><div style='padding:20px; border:1px solid #000000;'><?= mrr_get_mini_menu_display() ?></div>
	</td>
</tr>
</table>
</form>
<? include('footer.php') ?>