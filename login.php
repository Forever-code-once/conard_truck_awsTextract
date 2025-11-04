<? include_once('application.php')?>
<?
	$use_bootstrap = true;	
	/*
	if($app['users'][$_SESSION['id']]['status'] == 1)
	{
		header("Location: home.php");
		exit;
	}
	*/		

	$error = '';
	if(!isset($defaultsarray))
	{
		global $defaultsarray;						
	}
		
	if(isset($_POST['uname']) && $_POST['uname'] != '')
	{			
		$mrr_cookie="novalue";			//createuuid();
		if(isset($_COOKIE['uuid']))		$mrr_cookie=trim($_COOKIE['uuid']);
		
		
		$passwords_expire=45;
		
		$sql = "
			select *,
				DATE_ADD(password_expires, INTERVAL ".(int) $passwords_expire." DAY) as pass_expired				
			from users				
			where deleted = 0
				and active = 1
				and (
					(username = '" . sql_friendly($_POST['uname']) . "' and password = '" . sql_friendly($_POST['pword']) . "')
					or 
					(uuid ='".sql_friendly($mrr_cookie)."' and uuid !='')
				) 
		";							
		$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
		$row = mysqli_fetch_array($data);			
		if(is_array($row))
		{
			$sql = "
				insert into ".mrr_find_log_database_name()."log_login
					(user_id,
					linedate_added,
					ip_address)
					
				values ('$row[id]',
					now(),
					'".sql_friendly($_SERVER['REMOTE_ADDR'])."')
			";
			simple_query($sql);
			
			mrr_update_session_cookie($row['id'], $row['username'],"");
			
			$_SESSION['force_logout']=(int) $row['force_logout'];
			//$pass_exp=$row['password_expires'];
			$has_exp=strtotime($row['pass_expired']);
			$right_now=time();
			/*				
			$_SESSION['id'] = $row['id'];
			$_SESSION['user_id'] = $row['id'];
			$_SESSION['username'] = $row['username'];
			$_SESSION['conard_trucking_logged_in'] = 1;
			
			$mrr_cookie=createuuid();
			$mrr_cookie_bake=(time()+3600)*48;				  // expire in 48 hour 
			setcookie("uuid", $mrr_cookie, $mrr_cookie_bake);
			
			$sql = "
				update users set
					uuid='".sql_friendly($mrr_cookie)."'
				where id='".sql_friendly($row['id'])."'
			";
			simple_query($sql);
			*/
						
			if(trim($defaultsarray['time_clock_ip_restriction']) != '')
			{
				$test_ip=$_SERVER['REMOTE_ADDR'];
				$test_ip2=trim($defaultsarray['time_clock_ip_restriction']);
				if(substr_count($test_ip,$test_ip2) > 0)
				{
					mrr_punch_clock_login($_SESSION['user_id'],$_SERVER['REMOTE_ADDR']);	
				}
			}
			else
			{
				mrr_punch_clock_login($_SESSION['user_id'],$_SERVER['REMOTE_ADDR']);	
			}	
		}
		else
		{					
			$sql = "
				insert into ".mrr_find_log_database_name()."log_login
					(user_id,
					linedate_added,
					ip_address,
					invalid_username)
					
				values (0,
					now(),
					'".sql_friendly($_SERVER['REMOTE_ADDR'])."',
					'".sql_friendly($_POST['uname'])."')
			";
			simple_query($sql);
			
			$error = 'Invalid username or password';
		}
			
		if($error == '')
		{
			if(isset($_GET['redirect']))
			{			
				if(isset($_GET['querystring']))
				{								
					$uQueryString = str_replace('!','&',$_GET['querystring']);
																				
					header("Location: ". $_GET['redirect'] . "?" . $uQueryString);
					exit;											
				}
				else
				{
					header("Location: ". $_GET['redirect']);
					exit;								
				}
			}
			else
			{
				unset($_SESSION['admin']);
				if($row['access'] >= 90) 
				{
					$_SESSION['admin'] = $row['access'];
					if(!$row['inventory_access']) header("Location: admin.php?mCheck=1");
				}
				
				if($row['inventory_access']) 
				{
					$_SESSION['inventory_access'] = 1;
					header("Location: inventory.php?mCheck=1");
				} 
				else 
				{					
					if($has_exp < $right_now)
					{
						header("Location: login_change.php");
					}
					else
					{
						//header("Location: index.php?mCheck=1");
					
						//header("Location: report_peoplenet_activity.php?verify=1");
						header("Location: report_geotab_activity.php?verify=1");
					}				
				}
				exit;
			}					
		}	
	}		
		
	if($error == '' and isset($_GET['error']))
	{
		if($_GET['error'] == 1) $error = "You must be logged in in order to access the page your requested.  Please login in above.";
		if($_GET['error'] == 2) $error = "Thank you for logging out. Your account is now protected from unauthorized access.";
		if($_GET['error'] == 3) $error = "Your session has expired and you have been logged out. Your account is now protected from unauthorized access.";
	}	
?>
<? $body_tag = '<body MARGINWIDTH="0" MARGINHEIGHT="0" TOPMARGIN="0" LEFTMARGIN="0" rightmargin="0" bgcolor="F8F3E4" onload="document.getElementById(\'uname\').focus()">';?>
<? include('header.php')?>
<div class='container col-md-12'>
	<div class='col-md-4'>
		&nbsp;
	</div>
	<div class='col-md-4'>
		<div class="panel panel-info">
			<div class="panel-heading">Trucking Intranet Login</div>
			<div class="panel-body">
			  	<?
					if(isset($_GET['out'])) {
						mrr_punch_clock_logout($_SESSION['user_id'],$_SERVER['REMOTE_ADDR']);
						//unset($_SESSION);
						//session_destroy();
						if(isset($_SESSION['user_id']))
						{
							$sql="
								update users set
									uuid=''
								where id='".sql_friendly($_SESSION['user_id'])."'
							";
							simple_query($sql);
						}
						if(isset($_SESSION['user_id']) && isset($_COOKIE['uuid']))
						{
							$sql="
								delete from user_cookies 
								where user_id='".sql_friendly($_SESSION['user_id'])."'
									and uuid='".sql_friendly($_COOKIE['uuid'])."'
							";
							simple_query($sql);
						}
						unset($_SESSION['conard_trucking_logged_in']);						
						unset($_COOKIE['uuid']);
						setcookie("uuid", 'novalue', 3600);	//reset the cookie with 60 seconds
						/*
						unset($_SESSION['inventory_access']);
						unset($_SESSION['id']);
						unset($_SESSION['admin']);
						unset($_SESSION['user_id']);
						*/
					}
					?>
					
					<p>
						<button onclick="window.location='index.php'" class='btn btn-success'><span class="glyphicon glyphicon-home"></span> Home</button>
					</p>	
					<p>
						<?=$error ?>
					</p>
										
					<form action="<?=$_SERVER['SCRIPT_NAME']?>?<?=$query_string?>" method="post">
						<table class='table table-bordered well'>
						<tr>
							<td>Username:</td>
							<td colspan='2'><input name='uname' class='form-control' id='uname'></td>
						</tr>
						<tr>
							<td>Password:</td>
							<td><input name='pword' class='form-control' type='password'></td>
							<td><button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-ok"></span> Login</button></td>
						</tr>						
						</table>
						<!--<input type="submit" value="Login" class='standard12'> -->	
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
	function upload_file() {
		window.open("upload.asp?logout=1&thirdparty=1", "file_upload", 'width=700,height=500,location=no,resizable=yes,menubar=no,status=no,toolbar=no,scrollbars=yes')
	}
</script>
<? include('footer.php') ?>