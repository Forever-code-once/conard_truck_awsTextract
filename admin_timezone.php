<? include('application.php') ?>
<? $admin_page = 1 ?>
<?

	if(isset($_GET['did'])) {
		$sql = "
			update timezones
			
			set	deleted = 1
			where id = '$_GET[did]'
		";
		$data_delete = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
	}

	if(isset($_POST['timezone'])) {
		/* update in progress */
		$sql = "
			update timezones
			
			set timezone = '$_POST[timezone]',
				gmt_difference = '$_POST[gmt_difference]'
			where id = '$_GET[id]'
		";
		$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
	}

	if(isset($_GET['new'])) {
		$sql = "
			insert into timezones
				(timezone)
				
			values ('New Time Zone')
		";
		$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
		header("Location: $SCRIPT_NAME?id=".mysqli_insert_id($datasource));
		die();
	}

	$sql = "
		select *
		
		from timezones
		where deleted = 0
		order by gmt_difference
	";
	$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
?>
<? include('header.php') ?>

<table class='standard12' style='text-align:left'>
<tr>
	<td valign='top'>
		<table class='admin_menu1'>
		<tr>
			<td><font class='standard18'><b>Admin Time Zones</b></font></td>
		</tr>
		<tr>
			<td>
				<a href="<?=$SCRIPT_NAME?>?new=1">Add New Time Zone</a>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><b>Time Zone</b></td>
			<td align='center'><b>GMT Difference</b></td>
		</tr>
		<? while($row = mysqli_fetch_array($data)) { ?>
			<tr>
				<td><a href="<?=$SCRIPT_NAME?>?id=<?=$row['id']?>"><?=$row['timezone']?></a></td>
				<td align='center'><?=$row['gmt_difference']?></td>
				<td><a href="javascript:confirm_delete(<?=$row['id']?>)"><img src="images/delete_sm.gif" border="0"></a></td>
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
					
					from timezones
					where id = '$_GET[id]'
				";
				$data_edit = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
				$row_edit = mysqli_fetch_array($data_edit);
				
				if(isset($_POST['timezone'])) {
					echo "<tr><td colspan='2'><font color='red'><b>Update Successful</b></font></td></tr>";
				}
			?>
			<tr>
				<td><b>Time Zone</b></td>
				<td><input name="timezone" value="<?=$row_edit['timezone']?>">
			</tr>
			<tr>
				<td><b>GMT Difference</b></td>
				<td><input name="gmt_difference" value="<?=$row_edit['gmt_difference']?>">
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

<script type='text/javascript'>
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete this time zone?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}
</script>

<? include('footer.php') ?>
