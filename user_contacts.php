<? $use_bootstrap = true; ?>
<? $usetitle = "Your Contacts"; ?>
<? include('header.php') ?>
<?
	$user_id=(int) $_SESSION['user_id'];
	
	$rep_val="";
	if(isset($_GET['contact_id']))		$_POST['contact_id']=$_GET['contact_id'];
	
	if(isset($_GET['delid'])) 
	{
		$sql = "
			update user_contacts set
				deleted = 1			
			where id = '".$_GET['delid']."'
		";
		simple_query($sql);	
		header("Location: user_contacts.php");
		die;		
		//$_POST['contact_id']=0;
	}
	
	if(!isset($_POST['contact_id']))		$_POST['contact_id']=0;
	
	if($_POST['contact_id']==0 && isset($_POST['contact_save']) && ($_POST['company_name']!="" || $_POST['first_name']!=""))
	{	
		//die('Got here...1');
				
		$phone_no=trim(strip_tags($_POST['phone_no']));		$phone_no=mrr_clear_phone_number_extras($phone_no);	
		$fax_no=trim(strip_tags($_POST['fax_no']));			$fax_no=mrr_clear_phone_number_extras($fax_no);	
		$cell_no=trim(strip_tags($_POST['cell_no']));		$cell_no=mrr_clear_phone_number_extras($cell_no);	
		
		$sql = "
			insert into user_contacts
				(id,
				user_id,
				linedate_added,
						
				company_name,		
				first_name,
				last_name,
				address_1,
				address_2,
				city,
				state,
				zip,
				
				phone_no,
				fax_no,
				cell_no,
				
				email_address,
				contact_type,
				contact_notes,		
						
				deleted)
			values
				(NULL,
				'".sql_friendly($user_id)."',
				NOW(),
				
				'".sql_friendly(trim(strip_tags($_POST['company_name'])))."',
				'".sql_friendly(trim(strip_tags($_POST['first_name'])))."',
				'".sql_friendly(trim(strip_tags($_POST['last_name'])))."',
				'".sql_friendly(trim(strip_tags($_POST['address_1'])))."',
				'".sql_friendly(trim(strip_tags($_POST['address_2'])))."',
				'".sql_friendly(trim(strip_tags($_POST['city'])))."',
				'".sql_friendly(trim(strip_tags($_POST['state'])))."',
				'".sql_friendly(trim(strip_tags($_POST['zip'])))."',				
				
				'".sql_friendly($phone_no)."',
				'".sql_friendly($fax_no)."',
				'".sql_friendly($cell_no)."',
				
				'".sql_friendly(trim(strip_tags($_POST['email_address'])))."',
				'".sql_friendly($_POST['contact_type'])."',	
				'".sql_friendly(trim(strip_tags($_POST['contact_notes'])))."',
				
				0)
		";
		simple_query($sql);	
		$_POST['contact_id']= mysqli_insert_id($datasource);
		
		//header("Location: user_contacts.php?contact_id=".$_POST['contact_id']."");
		//die;			
	}	
	elseif($_POST['contact_id'] > 0 && isset($_POST['contact_save']))
	{
		//die('Got here...2');
		
		$phone_no=trim(strip_tags($_POST['phone_no']));		$phone_no=mrr_clear_phone_number_extras($phone_no);	
		$fax_no=trim(strip_tags($_POST['fax_no']));			$fax_no=mrr_clear_phone_number_extras($fax_no);	
		$cell_no=trim(strip_tags($_POST['cell_no']));		$cell_no=mrr_clear_phone_number_extras($cell_no);	
		
		$sql = "
			update user_contacts set
						
				company_name='".sql_friendly(trim(strip_tags($_POST['company_name'])))."',		
				first_name='".sql_friendly(trim(strip_tags($_POST['first_name'])))."',
				last_name='".sql_friendly(trim(strip_tags($_POST['last_name'])))."',
				address_1='".sql_friendly(trim(strip_tags($_POST['address_1'])))."',
				address_2='".sql_friendly(trim(strip_tags($_POST['address_2'])))."',
				city='".sql_friendly(trim(strip_tags($_POST['city'])))."',
				state='".sql_friendly(trim(strip_tags($_POST['state'])))."',
				zip='".sql_friendly(trim(strip_tags($_POST['zip'])))."',
				phone_no='".sql_friendly($phone_no)."',
				fax_no='".sql_friendly($fax_no)."',
				cell_no='".sql_friendly($cell_no)."',
				email_address='".sql_friendly(trim(strip_tags($_POST['email_address'])))."',
				contact_type='".sql_friendly($_POST['contact_type'])."',
				contact_notes='".sql_friendly(trim(strip_tags($_POST['contact_notes'])))."'
				
			where id='".sql_friendly($_POST['contact_id'])."'
		";
		simple_query($sql);
		
		//die('Got here...2a');
		
		//header("Location: user_contacts.php?contact_id=".$_POST['contact_id']."&updated=1");
		//die;	
	}
	
	if($_POST['contact_id'] > 0)
	{     	
     	$sql="
     		select * 
     		from user_contacts
     		where id='".sql_friendly($_POST['contact_id'])."'
     	";
     	$data=simple_query($sql);
     	if($row = mysqli_fetch_array($data))
     	{
     		$_POST['company_name']=trim($row['company_name']);
     		$_POST['first_name']=trim($row['first_name']);
     		$_POST['last_name']=trim($row['last_name']);
     		$_POST['address_1']=trim($row['address_1']);
     		$_POST['address_2']=trim($row['address_2']);
     		$_POST['city']=trim($row['city']);
     		$_POST['state']=trim($row['state']);
     		$_POST['zip']=trim($row['zip']);
     		$_POST['phone_no']=trim($row['phone_no']);
     		$_POST['fax_no']=trim($row['fax_no']);
     		$_POST['cell_no']=trim($row['cell_no']);
     		$_POST['email_address']=trim($row['email_address']);
     		$_POST['contact_type']=$row['contact_type'];
     		$_POST['contact_notes']=trim($row['contact_notes']);
     	}
	}
	else
	{
		$_POST['company_name']="";
     	$_POST['first_name']="";
     	$_POST['last_name']="";
     	$_POST['address_1']="";
     	$_POST['address_2']="";
     	$_POST['city']="";
     	$_POST['state']="";
     	$_POST['zip']="";
     	$_POST['phone_no']="";
     	$_POST['fax_no']="";
     	$_POST['cell_no']="";
     	$_POST['email_address']="";
     	$_POST['contact_type']=0;
     	$_POST['contact_notes']="";
	}	
?>
<form action="<?=$SCRIPT_NAME ?>?contact_id=<?=$_POST['contact_id'] ?>" name='my_form' method="post">
	<input type='hidden' name="contact_id" id='contact_id' value="<?=$_POST['contact_id'] ?>">
	<input type='hidden' name="contact_save" id='contact_save' value="1">
<div class='container col-md-12'>
	<div class='col-md-8'>
		<div class="panel panel-info">
			<div class="panel-heading"><?=$usetitle ?></div>
			<div class="panel-body">
				<table class='table table-bordered well'>  
               	<tr>
               		<td valign='top'><b>Type</b></td>
               		<td valign='top'><b>First Name</b></td>
               		<td valign='top'><b>Last Name</b></td>
               		<td valign='top'><b>Company Name</b></td>
               		<td valign='top'><b>Email Address</b></td>
               		<td valign='top'><b>Phone No</b></td>
               		<td valign='top'><b>Cell No</b></td>              		
               		<td valign='top'><b>Fax/Other</b></td>
               		<td valign='top'><b><a href='user_contacts.php?contact_id=0'>NEW</a></b></td>
               	</tr>
               	<?
               	$cntr=0;
               	$sql="
					select user_contacts.*,
						(select option_values.fname from option_values where option_values.id=user_contacts.contact_type) as type_name
					from user_contacts
					where user_contacts.deleted=0						
						and user_contacts.user_id='".$user_id."'
					order by user_contacts.last_name asc, 
						user_contacts.first_name asc, 
						user_contacts.company_name asc, 
						user_contacts.id asc
				";	
				$data = simple_query($sql);
          		while($row = mysqli_fetch_array($data)) 
          		{          			         			
          			echo "
          				<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
          					<td valign='top'>".trim($row['type_name'])."</td> 
          					<td valign='top'><a href='user_contacts.php?contact_id=".$row['id']."'>".trim($row['first_name'])."</a></td> 
          					<td valign='top'><a href='user_contacts.php?contact_id=".$row['id']."'>".trim($row['last_name'])."</a></td> 
          					<td valign='top'>".trim($row['company_name'])."</td>  
          					<td valign='top'>".trim($row['email_address'])."</td>
          					<td valign='top'>".trim($row['phone_no'])."</td>
          					<td valign='top'>".trim($row['cell_no'])."</td>
          					<td valign='top'>".trim($row['fax_no'])."</td>
          					<td valign='top'><button onclick='confirm_del(".$row['id'].")' class='mrr_delete_access btn btn-danger btn-sm'><span class='glyphicon glyphicon-trash'></span></button></td>
          				</tr>       				
          			";          			
          			$cntr++;	
          		}
               	?>
               	</table>
               	
			</div>
		</div>
		
	</div>
	<div class='col-md-4'>
		
		<div class="panel panel-primary">
			<div class="panel-heading">Edit Contact</div>
			<div class="panel-body">
				<h3>All contact items are optional, but first name and last name would be recommended for proper label.</h3>
				<table class='table table-bordered well'>  
				<tr>
               		<td valign='top'><b>First name:</b></td>
               		<td valign='top'><input type='type' name="first_name" id="first_name" value="<?=$_POST['first_name']?>" style='width:200px; text-align:left;'></td>
               	</tr>	
				<tr>
               		<td valign='top'><b>Last Name:</b></td>
               		<td valign='top'><input type='type' name="last_name" id="last_name" value="<?=$_POST['last_name']?>" style='width:200px; text-align:left;'></td>
               	</tr>	
				<tr>
               		<td valign='top'><b>Company Name:</b></td>
               		<td valign='top'><input type='type' name="company_name" id="company_name" value="<?=$_POST['company_name']?>" style='width:250px; text-align:left;'></td>
               	</tr>
               	              		
				<tr>
               		<td valign='top'><b>Address 1:</b></td>
               		<td valign='top'><input type='type' name="address_1" id="address_1" value="<?=$_POST['address_1']?>" style='width:250px; text-align:left;'></td>
               	</tr>	
				<tr>
               		<td valign='top'><b>Address 2:</b></td>
               		<td valign='top'><input type='type' name="address_2" id="address_2" value="<?=$_POST['address_2']?>" style='width:250px; text-align:left;'></td>
               	</tr>
				<tr>
               		<td valign='top'><b>City:</b></td>
               		<td valign='top'><input type='type' name="city" id="city" value="<?=$_POST['city']?>" style='width:200px; text-align:left;'></td>
               	</tr>
				<tr>
               		<td valign='top'><b>State:</b></td>
               		<td valign='top'><input type='type' name="state" id="state" value="<?=$_POST['state']?>" style='width:100px; text-align:left;'></td>
               	</tr>
				<tr>
               		<td valign='top'><b>Zip:</b></td>
               		<td valign='top'><input type='type' name="zip" id="zip" value="<?=$_POST['zip']?>" style='width:100px; text-align:left;'></td>
               	</tr>	
               	
				<tr>
               		<td valign='top'><b>Email Address:</b></td>
               		<td valign='top'><input type='type' name="email_address" id="email_address" value="<?=$_POST['email_address']?>" style='width:350px; text-align:left;'></td>
               	</tr>
               	
               	<tr>
               		<td valign='top'><b>Phone No.:</b></td>
               		<td valign='top'><input type='type' name="phone_no" id="phone_no" value="<?=$_POST['phone_no']?>" style='width:100px; text-align:left;'></td>
               	</tr>   
               	<tr>
               		<td valign='top'><b>Cell No.:</b></td>
               		<td valign='top'><input type='type' name="cell_no" id="cell_no" value="<?=$_POST['cell_no']?>" style='width:100px; text-align:left;'></td>
               	</tr>
               	<tr>
               		<td valign='top'><b>Fax/Other:</b></td>
               		<td valign='top'><input type='type' name="fax_no" id="fax_no" value="<?=$_POST['fax_no']?>" style='width:100px; text-align:left;'></td>
               	</tr>  	
               	
               	<tr>
               		<td valign='top'><b>Note(s):</b></td>
               		<td valign='top'><textarea name="contact_notes" id="contact_notes" wrap='virtual' rows='6' cols='50'><?=$_POST['contact_notes'] ?></textarea></td>
               	</tr>   
               	   
               	<tr>
               		<td valign='top'><b>Contact Type:</b></td>
               		<td valign='top'><? echo mrr_select_box_for_options('contact_types','contact_type',$_POST['contact_type'],"Default","",2); ?></td>
               	</tr>   
               	      		
               	<tr>
               		<td valign='top' colspan='2' align='center'>
               			<button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-floppy-disk"></span> Update</button>
               		</td>
               	</tr>
               	</table>
			</div>
		</div>	
	</div>
</div>
</form>
<script type='text/javascript'>
	function mrr_submit()
	{		
		//document.my_form.submit();		// onclick='mrr_submit();'
	}	
	function confirm_del(id) {
		if(confirm("Are you sure you want to delete this user contact?")) 
		{
			window.location = "<?=$SCRIPT_NAME?>?delid=" + id;
		}
	}
</script>
<? include('footer.php') ?>