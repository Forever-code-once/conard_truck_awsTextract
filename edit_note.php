<? include('application.php') ?>
<? $no_header = 1 ?>
<? include('header.php') ?>
<?
	if(!isset($_GET['id'])) $_GET['id'] = 0;
	
	if(isset($_POST['desc_long'])) {
		if($_GET['id'] == 0) {
			$sql = "
				insert into notes
					(linedate_added,
					deleted,
					section_id)
					
				values (now(),
					0,
					'$_GET[sid]')
			";
			$data_insert = simple_query($sql);
			$_GET['id'] = mysqli_insert_id($datasource);
		}
		
		$sql = "
			update notes
			set desc_long = '".str_replace("'","''",$_POST['desc_long'])."',
				user_id = '$_SESSION[user_id]'
			
			where id = '$_GET[id]'
		";
		$data_update = simple_query($sql);
		
		parent_window_refresh();
	}
	
	if($_GET['id'] > 0) { 
		$sql = "
			select *
			
			from notes
			where id = '$_GET[id]'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$_POST['desc_long'] = $row['desc_long'];
	}
	
	if(!isset($_POST['desc_long'])) $_POST['desc_long'] = "";
?>

<form action="?id=<?=$_GET['id']?>&sid=<?=$_GET['sid']?>" method="post">
<table class='standard12 section1' style='text-align:left'>
<tr>
	<td>Note:</td>
</tr>
<tr>
	<td><textarea name="desc_long" rows="5" cols="55"><?=$_POST['desc_long']?></textarea></td>
</tr>
<tr>
	<td>
		<input type="submit" name="submit" value="Submit">
		<input type="button" value="Close" onclick='javascript:window.close()'>
	</td>
</tr>
</table>
</form>

<? include('footer.php') ?>
