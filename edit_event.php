<? include('application.php') ?>
<? $no_header = 1 ?>
<? include('header.php') ?>
<?
	if(!isset($_GET['id'])) $_GET['id'] = 0;
	
	if(isset($_POST['desc_long'])) {
		if($_GET['id'] == 0) {
			$sql = "
				insert into calendar
					(linedate_added,
					deleted,
					created_by_user_id)
					
				values (now(),
					0,
					'".sql_friendly($_SESSION['user_id'])."')
			";
			$data_insert = simple_query($sql);
			$_GET['id'] = mysqli_insert_id($datasource);
		}
		
		$sql = "
			update calendar
			set desc_long = '".sql_friendly($_POST['desc_long'])."',
				desc_short = '".sql_friendly($_POST['desc_short'])."',
				linedate = '".date("Y-m-d", strtotime($_POST['linedate']))."'
			
			where id = '".sql_friendly($_GET['id'])."'
		";
		$data_update = simple_query($sql);
		
		parent_window_refresh();
	}
	
	if($_GET['id'] > 0) { 
		$sql = "
			select *
			
			from calendar
			where id = '".sql_friendly($_GET['id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$_POST['desc_short'] = $row['desc_short'];
		$_POST['desc_long'] = $row['desc_long'];
		$_POST['linedate'] = date("m/d/Y", strtotime($row['linedate']));
	}
	
	if(!isset($_POST['desc_short'])) $_POST['desc_short'] = "";
	if(!isset($_POST['desc_long'])) $_POST['desc_long'] = "";
	if(!isset($_POST['linedate'])) $_POST['linedate'] = "";
?>

<form action="?id=<?=$_GET['id']?>" method="post">
<table class='standard12 section1' style='text-align:left'>
<tr>
	<td colspan='4' class='section_heading'>Calendar Event:</td>
</tr>
<tr>
	<td>Date:</td>
	<td colspan='3'><input name='linedate' id='linedate' value="<?=$_POST['linedate']?>"></td>
</tr>
<tr>
	<td>Event Name:</td>
	<td colspan='3'><input name='desc_short' id='desc_short' value="<?=$_POST['desc_short']?>" style='width:400px'></td>
</tr>
<tr>
	<td>Event Details:</td>
	<td colspan='3'><textarea name="desc_long" style='width:400px;height:200px'><?=$_POST['desc_long']?></textarea></td>
</tr>
<tr>
	<td></td>
	<td colspan='3'>
		<input type="submit" name="submit" value="Submit">
		<input type="button" value="Close" onclick='javascript:window.close()'>
	</td>
</tr>
</table>
</form>

<script type='text/javascript'>
	$('#linedate').datepicker();
</script>

<? include('footer.php') ?>
