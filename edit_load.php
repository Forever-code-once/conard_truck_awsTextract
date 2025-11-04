<? include('application.php') ?>
<? $no_header = 1 ?>
<? include('header.php') ?>
<?
	if(isset($_GET['id'])) {
		$_POST['load_id'] = $_GET['id'];
	} else {
		$_POST['load_id'] = 0;
	}
?>
<table width='100%'>
<tr>
	<td colspan='2' align='center'>
		<? 
		if($_POST['load_id'] == 0) {
			echo "New";
		} else {
			echo "Edit";
		}
		?>
		Load
	</td>
</tr>
<tr>
	<td>Load ID:</td>
	<td><?=$_POST['load_id']?></td>
</tr>
</table>