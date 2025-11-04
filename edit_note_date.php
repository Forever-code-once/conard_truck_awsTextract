<? include('application.php') ?>
<? $no_header = 1 ?>
<? include('header.php') ?>
<?
	if(isset($_GET['delid'])) {
		$sql = "
			update notes
			set deleted = 1
			where id = '".sql_friendly($_GET['delid'])."'
		";
		simple_query($sql);
		
		parent_window_refresh();
		die;
	}
	
	if(!isset($_GET['id'])) $_GET['id'] = 0;
	
	if(isset($_POST['desc_long'])) {
		if($_GET['id'] == 0) {
			$sql = "
				insert into notes
					(linedate_added,
					deleted,
					customer_id,
					linedate)
					
				values (now(),
					0,
					'".sql_friendly($_POST['customer_id'])."',
					'".date("Y-m-d", strtotime($_POST['linedate']))."')
			";
			$data_insert = simple_query($sql);
			$_GET['id'] = mysqli_insert_id($datasource);
		}
		
		$sql = "
			update notes
			set desc_long = '".str_replace("'","''",$_POST['desc_long'])."',
				user_id = '$_SESSION[user_id]',
				customer_id = '".sql_friendly($_POST['customer_id'])."',
				linedate = '".date("Y-m-d", strtotime($_POST['linedate']))."'
			
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
		$_POST['customer_id'] = $row['customer_id'];
		$_POST['linedate'] = date("n/j/Y", strtotime($row['linedate']));
	} else {
		$_POST['linedate'] = date("n/j/Y", strtotime($_GET['linedate']));
		$_POST['customer_id'] = $_GET['customer_id'];
	}
	
	if(!isset($_POST['desc_long'])) $_POST['desc_long'] = "";
?>

<form action="?id=<?=$_GET['id']?>" method="post">
<input type='hidden' name='customer_id' value='<?=$_POST['customer_id']?>'>
<table class='standard12 section1' style='text-align:left'>
<tr>
	<td>Note Date:</td>
	<td><input name='linedate' value='<?=$_POST['linedate']?>'></td>
</tr>
<tr>
	<td>Note:</td>
	<td><textarea name="desc_long" rows="5" cols="55"><?=$_POST['desc_long']?></textarea></td>
</tr>
<tr>
	<td></td>
	<td>
		<input type="submit" name="submit" value="Submit">
		<input type="button" value="Close" onclick='javascript:window.close()'>
		<? if($_GET['id'] > 0) { ?>
			<input type="button" value="Delete Note" onclick='delete_note()'>
		<? } ?>
	</td>
</tr>
</table>
</form>

<script type='text/javascript'>
	function delete_note() {
		if(confirm("Are you sure you want to delete this note?")) {
			window.location = "edit_note_date.php?delid=<?=$_GET['id']?>";
		}
	}
</script>

<? include('footer.php') ?>
