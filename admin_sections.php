<? include('application.php') ?>
<? $admin_page = 1 ?>
<?

	if(isset($_POST['xdesc'])) {
		$sql = "
			update site_sections
			
			set xdesc = '$_POST[xdesc]',
				notes_general = '$_POST[notes_general]'
			
			where id = '$_GET[id]'
		";
		$data_update = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
	}

	$sql = "
		select *
		
		from site_sections
		order by xdesc
	";
	$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
?>
<? include('header.php') ?>




<table class='standard12' style='text-align:left'>
<tr>
	<td valign='top'>
		<table class='admin_menu1'>
		<tr>
			<td><font class='standard18'><b>Admin Site Sections</b></font></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><b>Section</b></td>
		</tr>
		<? while($row = mysqli_fetch_array($data)) { ?>
			<tr>
				<td><a href="<?=$SCRIPT_NAME?>?id=<?=$row['id']?>"><?=$row['xdesc']?></a></td>
			</tr>
		<? } ?>
		</table>
	</td>
	<td valign='top'>
		<? if(isset($_GET['id'])) { ?>
			<form action="<?=$SCRIPT_NAME?>?id=<?=$_GET['id']?>" method="post">
			<table class='admin_menu2'>
			<?
				$sql = "
					select *
					
					from site_sections
					where id = '$_GET[id]'
				";
				$data_edit = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
				$row_edit = mysqli_fetch_array($data_edit);
				
				if(isset($_POST['xdesc'])) {
					echo "<tr><td colspan='2'><font color='red'><b>Update Successful</b></font></td></tr>";
				}
			?>
			<tr>
				<td><b>Site Section Name</b></td>
				<td><input name="xdesc" value="<?=$row_edit['xdesc']?>">
			</tr>
			<tr>
				<td><b>General Notes</b></td>
				<td><textarea name="notes_general" rows="5" cols="45"><?=$row_edit['notes_general']?></textarea>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type='submit' value="Update">
			</tr>
			</table>
			</form>
		<? } ?>
	</td>
</tr>
</table>








<? include('footer.php') ?>
