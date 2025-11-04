<? include('application.php') ?>
<? $admin_page = 1 ?>
<? $usetitle = "Admin Home Page Links" ?>
<?	
	if(isset($_GET['delid'])) 
	{
		$sql = "
			update home_pg_links set
				deleted = 1			
			where id = '".$_GET['delid']."'
		";
		simple_query($sql);
		
		header("Location: admin_homepage_links.php?eid=0");
		die();
	}

	if(isset($_GET['new'])) 
	{
		$sql="update home_pg_links set deleted=1 where username='New Link'";
		mysqli_query($datasource, $sql);
		
		$sql = "
			insert into home_pg_links			
				(link_url,
				link_label,
				link_title,
				active,
				linedate_added)
			values 
				('New Link',
				'',
				'',
				1,
				NOW())
		";
		$data = simple_query($sql);
		$new_id=mysqli_insert_id($datasource);
		
		header("Location: admin_homepage_links.php?eid=".$new_id."");
		die();
	}
	
	if(isset($_POST['save_link'])) 
	{
		$useactive = 0;
		if(isset($_POST['active']))		$useactive = 1;
				
		$sqlu = "
			update home_pg_links set
				link_url = '".sql_friendly(trim($_POST['link_url']))."',
				link_label = '".sql_friendly(trim($_POST['link_label']))."',
				link_title='".sql_friendly($_POST['link_title'])."',
				active ='".sql_friendly($useactive)."',
				zorder ='".sql_friendly((int) trim($_POST['zorder']))."'
			where id = '".$_GET['eid']."'
		";
		simple_query($sqlu);
		
		header("Location: admin_homepage_links.php?eid=".$_GET['eid']."");
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
			and (link_label like '%$_POST[sbox]%'
				or link_title like '%$_POST[sbox]%'
				or link_url like '%$_POST[sbox]')
		";
	}

	$sql = "
		select *		
		from home_pg_links
		where deleted = 0
			$sql_extra
		order by zorder asc, link_label asc,link_title asc, id asc
	";
	$data = simple_query($sql);
	
	$mrr_activity_log_notes.="Viewed list of home page links. ";	

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
						<td><a href='admin_homepage_links.php?new=1'><button type='button' class='btn btn-success'><span class="glyphicon glyphicon-plus"></span> Add New Link</button></a></td>
					</tr>
					</table>
					</form>
				<br><br>
				<table class='table table-striped'>	
				<thead>	
          		<tr>
          			<th><b>Edit</b></th>          			
          			<th><b>Link Text, URL, and Title/Tooltip</b></th>
          			<th><b>Order</b></th>
          			<th><b>Active</b></th>
          			<th>&nbsp;</th>
          		</tr>
          		</thead>
          		<tbody>
          		<? while($row = mysqli_fetch_array($data)) { ?>
          			<tr>
          				<td><a href="<?=$SCRIPT_NAME?>?eid=<?=$row['id']?>"><?=$row['id']?></a></td>
          				<td>
                            Text: <? if($row['active']==0) echo "<strike>"?><?=$row['link_label']?><? if($row['active']==0) echo "</strike>"?>
                            <br></br>URL: <a href="<?=$row['link_url']?>" target="_blank"><?=$row['link_url']?></a>
                            <br></br>Tooltip: <i><?=$row['link_title']?></i>
                        </td>
          				<td><?=$row['zorder']?></td>
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
                    from home_pg_links
                    where id = '".$_GET['eid']."'
                ";
                $data_link = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
                $row_link = mysqli_fetch_array($data_link);                
			?>
     		<div class="panel panel-primary">
     			<div class="panel-heading">Edit Link: <?=$row_link['link_label']?></div>
     			<div class="panel-body">
     				<?
          			$mrr_activity_log_notes.="View Home Page Link ".$_GET['eid']." info. ";    			
          			?>			
          			<form action="<?=$SCRIPT_NAME?>?eid=<?=$_GET['eid']?>" method="post">
               			<table class='table table-bordered well'>          				
               				<tr>
               					<td>Link Text:</td>
               					<td>
                                    <input name="link_label" value="<?=$row_link['link_label']?>" class='form-control'>
                                    <br>This is the text the user clicks on...
                                </td>
               				</tr>
               				<tr>
               					<td>Link URL:</td>
               					<td>
                                    <input name="link_url" value="<?=$row_link['link_url']?>" class='form-control'>
                                    <br>This is where the link goes when the user clicks the text above (as the full URL).
                                    <br>Should start with http:// or https://
                                </td>
               				</tr>
               				<tr>
               					<td>Link Title/Tooltip:</td>
               					<td>
                                    <input name="link_title" value="<?=$row_link['link_title']?>" class='form-control'>
                                    <br><i>{Optional.  Seen only if user hovers over the link... A.K.A. "Tooltip" of "Title Text".}</i>
                                </td>
               				</tr>
               				<tr>
               					<td>Link Order:</td>
               					<td><input name="zorder" value="<?=(int) trim($row_link['zorder'])?>" class='form-control' style="width:100px; text-align:right;"> <i>Lowest sorts first.</i></td>
               				</tr> 
               				<tr>
               					<td><label for='active'>Active:</label></td>
               					<td><input type="checkbox" name="active" id='active' <? if($row_link['active']) echo "checked"?>></td>
               				</tr>               							
               			</table>               			
               			<p>
               				<button type='submit' class='btn btn-primary' name='save_link'><span class="glyphicon glyphicon-floppy-disk"></span> Update</button>
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
<? include('footer.php') ?>
<script type='text/javascript'>
	
	function confirm_del_addr(id) 
	{
		if(confirm("Are you sure you want to delete this home page link?")) {
			window.location = "<?=$SCRIPT_NAME?>?deladdr=" + id;
		}
	}
	
</script>