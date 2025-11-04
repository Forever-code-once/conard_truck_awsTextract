<? include('application.php') ?>
<? $admin_page = 1 ?>
<?
include_once('logistics_trucking_functions.php');

if(!isset($_GET['id']))				$_GET['id']=0;
if(!isset($_GET['email_id']))			$_GET['email_id']=0;
if(!isset($_GET['line_id']))			$_GET['line_id']=0;
if(!isset($_GET['msg_id']))			$_GET['msg_id']=0;	
	
$msg="";

//if(!isset($_POST['filter_date_from']))	$_POST['filter_date_from']=date("m/d/Y",time());
//if(!isset($_POST['filter_date_to']))		$_POST['filter_date_to']=date("m/d/Y",time());

//if(trim($_POST['filter_date_from'])=="")	$_POST['filter_date_from']=date("m/d/Y",time());
//if(trim($_POST['filter_date_to'])=="")	$_POST['filter_date_to']=date("m/d/Y",time());

if(isset($_POST['save_msg']))
{
	$msg_id=(int) $_POST['msg_id'];
	$msg_email=str_replace("'","ft",strip_tags(trim($_POST['msg_email'])));
	
	$msg_sub=str_replace("53'","53ft",strip_tags(trim($_POST['msg_sub'])));
	$msg_sub=str_replace("48'","48ft",strip_tags(trim($_POST['msg_sub'])));
	$msg_sub=str_replace("'","",strip_tags(trim($_POST['msg_sub'])));
	
	$msg_body=str_replace("'","ft",strip_tags(trim($_POST['msg_body'])));
	
	if($msg_id>0)
	{
		$sql = "
			update logistics_truck_emails set
				email_address='".$msg_email."',
				subject='".$msg_sub."',
				email_msg='".$msg_body."',
				comp_id=0
			where id='".(int) $msg_id."' 
		";
		mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");
		
		$msg="<b>E-Mail Message Saved Successful.</b>";
	}
	else
	{
		$sql = "
			insert into logistics_truck_emails 
				(id,
				linedate_added,
				processed,
				comp_id,
				subject,
				email_msg,
				dispatch_warning,     					
				email_address,
				deleted)
			values
				(NULL,
				NOW(),
				0,
				0,     					
				'".$msg_sub."',
				'".$msg_body."',
				0,
				'".$msg_email."',
				0)
		";
		mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
		$_POST['msg_id']=mysqli_insert_id($datasource);
		$_GET['msg_id']=$_POST['msg_id'];
		$msg_id=$_POST['msg_id'];
		
		$msg="<b>Added E-Mail Message Successfully.</b>";
	}
}


if(isset($_POST['update_company']))
{
	$act=0;		if(isset($_POST['company_active']) > 0)		$act=1;
	$name=str_replace("'","",trim($_POST['update_name']));
	$email=str_replace("'","",trim($_POST['update_email']));
	$phone=str_replace("'","",trim($_POST['update_phone']));
	$notes=str_replace("'","",trim($_POST['update_notes']));
	$user=str_replace("'","",trim($_POST['update_user']));
	$pass=str_replace("'","",trim($_POST['update_pass']));
	
	if($_POST['id'] > 0)
	{
		$sql = "
			update logistics_truck_companies set
				company_name='".$name."',
				company_email='".$email."',
				company_phone='".$phone."',
				company_notes='".$notes."',
				company_username='".$user."',
				company_password='".$pass."',
				active='".(int) $act."',
				deleted=0				
			
			where id='".(int) $_POST['id']."' 
		";
		mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");
		
		$msg="<b>Update Successful.</b>";
	}		
	else
	{			
		$sql = "
			insert into logistics_truck_companies 
				(id,
				company_name,
				company_email,
				company_phone,
				company_notes,
				company_username,
				company_password,
				active,
				deleted)
			values
				(NULL,
				'".$name."',
				'".$email."',
				'".$phone."',
				'".$notes."',
				'".$user."',
				'".$pass."',
				'".(int) $act."',
				0)
		";
		mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
		$_POST['id']=mysqli_insert_id($datasource);
		$_GET['id']=$_POST['id'];
		
		
		$msg="<b>Added Successfully.</b>";
	}
}
		
$_POST['id']=$_GET['id'];
		
$usetitle="Logistics E-Mail Admin";
$use_bootstrap = true;
?>
<? include('header.php'); ?>
<form action='' method='post' name='myform'>
	<div class='container col-md-12'>
		<!---<div class='col-md-7'>---->
			<div class="panel panel-info">
				<div class="panel-heading"><?=$usetitle ?></div>
			  	<div class="panel-body">
			  		<?
               		if($msg!="")		echo "<h2>".$msg."</h2>";
               		
               		//set_logistics_truck_emails();
               				
               		echo get_logistics_truck_companies(0,$_POST['id']);	
               		
               		if($_POST['id'] > 0)	
               		{
               			//echo get_logistics_truck_emails($_POST['id'],-1);		//-1=either, 0=not processed, >0 = processed.
               		}
               		
               		if($_POST['id'] > 0 && $_GET['email_id'] > 0)
               		{
               			//echo get_logistics_truck_listing($_POST['id'],$_GET['email_id']);
               		}
               		//$_GET['line_id']
               		
               		if($_POST['msg_id'] >= 0 && $_POST['id']==0 && 1==2)	
               		{
               			$msg_id=(int) $_POST['msg_id'];
               			$msg_email="";
               			$msg_sub="";
               			$msg_body="";
               			if($msg_id > 0)
               			{
               				$sql = "
                              		select *
                              		from logistics_truck_emails 
                              		where id='".$msg_id."'
                              	";		
                              	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
                              	if($row = mysqli_fetch_array($data)) 
                              	{
                              		$msg_email=trim($row['email_address']);
               					$msg_sub=trim($row['subject']);
               					$msg_body=trim($row['email_msg']);
                              	}
               			}              			
               		}              		
               		
               		//process any waiting emails.
               		//$rep=process_waiting_logistics_truck_emails($_POST['id']);
               		//echo "<h2>Processing Waiting E-Mail(s):</h2><br>".$rep."<br>";
			  		?>
			  		
			  	</div>
			</div>
		<!---</div>------>
		<?
		/*
		<!---<div class='col-md-5'>------>
			<div class="panel panel-primary">
				<div class="panel-heading">Add E-Mail Message:</div>
				<div class="panel-body">
					<?
					echo $email_form=input_form_logistics_truck_emails($msg_id,$msg_email,$msg_sub,$msg_body);
					?>
					<? if($_SERVER['REMOTE_ADDR'] == '50.76.161.186' || 1==1) { ?>
			  			<br><a href='logistics_trucking_controller.php?connect_key=bas82bad98fqhbnwga8shq34908asdhbn' target='_blank'>G-Mail Download</a>
			  		<? } ?>
				</div>
			</div>
		<!---</div>------>
		*/
		?>
	</div>
	<script type='text/javascript'>
		//$('#filter_date_from').datepicker();
		//$('#filter_date_to').datepicker();
	</script>
</form>
<? include('footer.php');  ?>