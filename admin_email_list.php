<? include('application.php') ?>
<? $admin_page = 1 ?>
<? $usetitle = "Admin Email List" ?>
<?	
	if(isset($_GET['delid'])) 
	{
		$sql = "
			update email_address_list set
				deleted = 1			
			where id = '".$_GET['delid']."'
		";
		simple_query($sql);
		
		header("Location: admin_email_list.php?eid=0");
		die();
	}

	if(isset($_GET['new'])) 
	{
		$sql="update email_address_list set deleted=1 where username='New Email'";
		mysqli_query($datasource, $sql);
		
		$sql = "
			insert into email_address_list			
				(email_name,
				email,
				active,
				linedate_added)
			values 
				('New Email',
				'',
				1,
				NOW())
		";
		$data = simple_query($sql);
		$new_id=mysqli_insert_id($datasource);
		
		header("Location: admin_email_list.php?eid=".$new_id."");
		die();
	}
	
	if(isset($_POST['save_email'])) 
	{
		$useactive = 0;
		if(isset($_POST['active']))		$useactive = 1;
				
		$sql = "
			update email_address_list set
				email_name = '".sql_friendly(trim($_POST['email_name']))."',
				email = '".sql_friendly(trim($_POST['email']))."',
				phone_number='".sql_friendly(mrr_clear_phone_number_extras($_POST['phone_number']))."',
				active ='".sql_friendly($useactive)."',
				cat_id ='".sql_friendly($_POST['cat_id'])."'

			where id = '".$_GET['eid']."'
		";
		simple_query($sql);
		
		header("Location: admin_email_list.php?eid=".$_GET['eid']."");
		die();		
	}

	if(!isset($_POST['sbox'])) 
	{
		$_POST['sbox'] = "";
		$sql_extra = "";
	} 
	else 
	{
		$sql_extra = "
			and (email like '%$_POST[sbox]%'
				or email_name like '%$_POST[sbox]%'
				or phone_number like '%$_POST[sbox]')
		";
	}

	$sql = "
		select *		
		from email_address_list
		where deleted = 0
			$sql_extra
		order by email asc, email_name asc, id asc
	";
	$data = simple_query($sql);
	
	$mrr_activity_log_notes.="Viewed list of email list addresses. ";	

$use_bootstrap = true;
?>
<? include('header.php') ?>
<div class='container col-md-12'>
	<div class='col-md-6'>
		<div class="panel panel-info">
			<div class="panel-heading"><?=$usetitle ?></div>
			<div class="panel-body">
					<form action="<?=$SCRIPT_NAME?>" method="post">			
					<table class='table table-bordered well'>
					<tr>				
						<td><input name="sbox" class='form-control' value="<?=$_POST['sbox']?>"></td>
						<td><button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-search"></span> Search</button></td>
						<td><a href='admin_email_list.php?new=1'><button type='button' class='btn btn-success'><span class="glyphicon glyphicon-plus"></span> Add New Address</button></a></td>
					</tr>
					</table>
					</form>
				<br><br>
				<table class='table table-striped'>	
				<thead>	
          		<tr>
          			<th><b>ID</b></th>          			
          			<th><b>Email Address</b></th>
          			<th><b>Email Name</b></th>
          			<th><b>Phone Number</b></th>
          			<th><b>List</b></th>
          			<th><b>Active</b></th>
          			<th>&nbsp;</th>
          		</tr>
          		</thead>
          		<tbody>
          		<? while($row = mysqli_fetch_array($data)) { ?>
          			<tr>
          				<td><?=$row['id']?></td>
          				<td><a href="<?=$SCRIPT_NAME?>?eid=<?=$row['id']?>"><? if($row['active']==0) echo "<strike>"?><?=$row['email']?><? if($row['active']==0) echo "</strike>"?></a></td>
          				<td><?=$row['email_name']?></td>
          				<td><?=$row['phone_number']?></td>
          				<td><?=mrr_admin_email_list_decoder($row['cat_id'])?></td>
          				<td><?= ($row['active']==0 ? "Inactive" : "Active")?></a></td>
          				<td>
          					<button onclick="confirm_del_addr(<?=$row['id']?>)" class='mrr_delete_access btn btn-danger btn-sm'><span class='glyphicon glyphicon-trash'></span></button>
          				</td>
          			</tr>
          		<? } ?>
          		</tbody>
          		</table>
			</div>
		</div>
	</div>
	<div class='col-md-6'>
		
		<? if(isset($_GET['eid'])) { ?>
			<?
					$sql = "
          				select *          				
          				from email_address_list
          				where id = '".$_GET['eid']."'
          			";
          			$data_user = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
          			$row_user = mysqli_fetch_array($data_user);
			?>
     		<div class="panel panel-primary">
     			<div class="panel-heading">Edit user: <?=$row_user['username']?></div>
     			<div class="panel-body">
     				<?
          			$mrr_activity_log_notes.="View email address ".$_GET['eid']." info. ";     			
          			
          			$selbx=mrr_admin_email_list_box('cat_id',$row_user['cat_id'],"");    			
          			?>			
          			<form action="<?=$SCRIPT_NAME?>?eid=<?=$_GET['eid']?>" method="post">
               			<table class='table table-bordered well'>          				
               				<tr>
               					<td>Email Name:</td>
               					<td><input name="email_name" value="<?=$row_user['email_name']?>" class='form-control'></td>
               				</tr>
               				<tr>
               					<td>E-Mail:</td>
               					<td><input name="email" value="<?=$row_user['email']?>" class='form-control'></td>
               				</tr>
               				<tr>
               					<td>Phone Number:</td>
               					<td><input name="phone_number" value="<?=$row_user['phone_number']?>" class='form-control'></td>
               				</tr>
               				<tr>
               					<td>Email List:</td>
               					<td><?=$selbx  ?> <i>Limit to one application/use if All is not selected.</i></td>
               				</tr> 
               				<tr>
               					<td><label for='active'>Active:</label></td>
               					<td><input type="checkbox" name="active" id='active' <? if($row_user['active']) echo "checked"?>></td>
               				</tr>
               							
               			</table>               			
               			<p>
               				<button type='submit' class='btn btn-primary' name='save_email'><span class="glyphicon glyphicon-floppy-disk"></span> Update</button>
               				<div class='mrr_button_access_notice'>&nbsp;</div>
               			</p>
               			
          			</form>			
          			
     			</div>
     		</div>
		
	<? } else { ?>
		&nbsp;
	<? } ?>		
	</div>
</div>
<div class='container col-md-12' style='display:none;'>
	<div class='col-md-6'>
		<div class="panel panel-info">
			<div class="panel-heading">Email Addresses on PM/FED Report (only)</div>
			<div class="panel-body">
				
				<table class='table table-striped'>	
				<thead>	
          		<tr>
          			<th><b>#</b></th>
          			<th><b>ID</b></th>          			
          			<th><b>Email Address</b></th>
          			<th><b>Email Name</b></th>
          			<th><b>Phone Number</b></th>
          		</tr>
          		</thead>
          		<tbody>
				<?
				$res=mrr_find_all_admin_email_list(1,0);
				$cntr=$res['num'];				
				$arr=$res['ids'];
				$arr_email=$res['email'];
				$arr_names=$res['names'];
				$arr_phone=$res['phone'];
				
				for($i=0;$i < $cntr; $i++)
				{
					echo "
						<tr>
							<td valign='top'>".($i + 1)."</td>
							<td valign='top'>".$arr[$i]."</td>
							<td valign='top'>".$arr_email[$i]."</td>
							<td valign='top'>".$arr_names[$i]."</td>
							<td valign='top'>".$arr_phone[$i]."</td>
						</tr>
					";	
				}
				?>
				</tbody>
          		</table>
			</div>
		</div>
	</div>
	<div class='col-md-6'>
			
		<div class="panel panel-info">
			<div class="panel-heading">Email Addresses on PM/FED Report or ALL Lists</div>
			<div class="panel-body">
     				
				<table class='table table-striped'>	
				<thead>	
          		<tr>
          			<th><b>#</b></th>
          			<th><b>ID</b></th>          			
          			<th><b>Email Address</b></th>
          			<th><b>Email Name</b></th>
          			<th><b>Phone Number</b></th>
          		</tr>
          		</thead>
          		<tbody>
				<?
				$res=mrr_find_all_admin_email_list(1,1);
				$cntr=$res['num'];				
				$arr=$res['ids'];
				$arr_email=$res['email'];
				$arr_names=$res['names'];
				$arr_phone=$res['phone'];
				
				for($i=0;$i < $cntr; $i++)
				{
					echo "
						<tr>
							<td valign='top'>".($i + 1)."</td>
							<td valign='top'>".$arr[$i]."</td>
							<td valign='top'>".$arr_email[$i]."</td>
							<td valign='top'>".$arr_names[$i]."</td>
							<td valign='top'>".$arr_phone[$i]."</td>
						</tr>
					";	
				}
				?>
				</tbody>
          		</table>
          		
			</div>
		</div>
	
		
	</div>
</div>
<? include('footer.php') ?>
<script type='text/javascript'>
	
	function confirm_del_addr(id) 
	{
		if(confirm("Are you sure you want to delete this email address?")) {
			window.location = "<?=$SCRIPT_NAME?>?deladdr=" + id;
		}
	}
	
</script>
