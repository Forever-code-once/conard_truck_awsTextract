<? include_once('application.php')?>
<?
	$use_bootstrap = true;			
	
	if(!isset($_SESSION['user_id']) || (isset($_SESSION['user_id']) && $_SESSION['user_id']==0))
	{	//session not active, send to login page.
		header("Location: login.php?out=1");
		exit;	
	}
	
	$passwords_expire=45;
	
	
	$username="";
	$name="";
	$old_pass="";
	if($_SESSION['user_id'] > 0)
	{
		$sql = "
			select *					
			from users				
			where id = '" . sql_friendly($_SESSION['user_id']) . "'
		";							
		$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
		$row = mysqli_fetch_array($data);	
		
		$old_pass=trim($row['password']);
		$username=trim($row['username']);
		$name=trim( $row['name_first']." ".$row['name_last'] );
	}
	

	$error = '';
	//if(!isset($defaultsarray))
	//{
		//global $defaultsarray;						
	//}
	
	$tried=0;	
	$new_pass="";				if(isset($_POST['pword']) && $_POST['pword'] != '')		{	$new_pass=trim($_POST['pword']);		$tried=1;	}
	$new_pass2="";				if(isset($_POST['pword2']) && $_POST['pword2'] != '')		{	$new_pass2=trim($_POST['pword2']);		$tried=1;	}	
	
	if(substr_count(trim($new_pass),"'") > 0 || substr_count(trim($new_pass2),"'") > 0)		$error='<span style="color:#cc0000;"><b>ERROR: Please enter and confirm your new password. Please try again.</b></span>';
	if(substr_count(trim($new_pass),'"') > 0 || substr_count(trim($new_pass2),'"') > 0)		$error='<span style="color:#cc0000;"><b>ERROR: Please enter and confirm your new password. Please try again.</b></span>';
	
	if(trim($new_pass)=="" || trim($new_pass2)=="")		$error='<span style="color:#cc0000;"><b>ERROR: Please enter and confirm your new password. Please try again.</b></span>';
	if(trim($new_pass)=="" || trim($new_pass2)=="")		$error='<span style="color:#cc0000;"><b>ERROR: Please enter and confirm your new password. Please try again.</b></span>';
	if(trim($new_pass)!=trim($new_pass2))				$error='<span style="color:#cc0000;"><b>ERROR: Please enter and confirm your new password by typing the same thing. Please try again.</b></span>';
	if(trim($old_pass)==trim($new_pass))				$error='<span style="color:#cc0000;"><b>ERROR: Please enter a NEW password. You have already used this one.  Please try again.</b></span>';	
	if(strlen(trim($new_pass)) < 3)					$error='<span style="color:#cc0000;"><b>ERROR: Password must be longer than 2 characters. Please try again.</b></span>';	
			
	if($tried > 0 && $error=='')
	{	
		$sql = "
			update users set
				password='" . sql_friendly($_POST['pword']) . "',
				password_expires=NOW()				
			where id = '" . sql_friendly($_SESSION['user_id']) . "'
		";				
		simple_query($sql);
				
		header("Location: report_geotab_activity.php?verify=1");	
		exit;		
	}	
	
	if($tried==0)		$error='';
?>
<? $body_tag = '<body MARGINWIDTH="0" MARGINHEIGHT="0" TOPMARGIN="0" LEFTMARGIN="0" rightmargin="0" bgcolor="F8F3E4" onload="document.getElementById(\'pword\').focus()">';?>
<? include('header.php')?>
<div class='container col-md-12'>
	<div class='col-md-4'>
		&nbsp;
	</div>
	<div class='col-md-4'>
		<div class="panel panel-info">
			<div class="panel-heading">Trucking Intranet Password Reset</div>
			<div class="panel-body">
					<p>
						<?=$error ?>
					</p>										
					<form action="<?=$_SERVER['SCRIPT_NAME']?>?<?=$query_string?>" method="post">
						<b><?=$name ?>, please change your password.</b> Passwords now expire every <?=$passwords_expire ?> days.
						<table class='table table-bordered well'>
						<tr>
							<td>Username:</td>
							<td colspan='2'><?=$username ?></td>
						</tr>
						<tr>
							<td>New Password:</td>
							<td><input name='pword' class='form-control' type='password'></td>
							<td>&nbsp;</td>
						</tr>	
						<tr>
							<td>Confirm Password:</td>
							<td><input name='pword2' class='form-control' type='password'></td>
							<td><button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-ok"></span> Update Password</button></td>
						</tr>					
						</table>
					</form>					
					<br>	
									
			</div>
		</div>
	</div>
	<div class='col-md-4'>
		&nbsp;
	</div>
</div>					
<script type='text/javascript'>
	
</script>
<? include('footer.php') ?>