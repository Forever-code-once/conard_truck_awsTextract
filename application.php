<? 
	//ini_set("max_input_vars","20000");  Must change in INI file...
	//error_reporting(E_ALL ^ E_DEPRECATED);
	error_reporting(0);
	
	// generate a starting timestamp for performance testing purposes
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$page_start = $time;
	$page_timer_array = array();

	include('config.php');

	include('functions_error_handling.php');

     include 'vendor/autoload.php';
     use PHPMailer\PHPMailer\PHPMailer;
     use PHPMailer\PHPMailer\SMTP;
     use PHPMailer\PHPMailer\Exception;

	
	if($_SERVER['REMOTE_ADDR'] == '70.90.229.29') show_errors(true);
//show_errors(true);
	include('functions.php');
	
	$page_timer_array[] = "Start of page (after functions.php include): " . show_page_time();

	date_default_timezone_set("America/Chicago");


	session_start();	
	//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29') session_write_close();
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');	
	
	
	//turn on/off screen errors
	
     $show_dev_errors = 0;
     if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' && $show_dev_errors > 0)
     {
         ini_set('display_errors', 1);
         ini_set('display_startup_errors', 1);
         error_reporting(E_ALL);
     }
     
     $current_errorhandler = set_error_handler("main_error_handler");
     
     function main_error_handler($errno, $errstr, $errfile, $errline)
     {    // custom function to log all errors / warnings to the database
     	global $show_errors;
     	global $log_errors;
     	
     	if(!isset($log_errors)) $log_errors = true;
     	
          if($errno == 8192)                 			return;                	// ignore deprecated warnings (E_DEPRECATED)
     	if($_SERVER['SCRIPT_NAME']=="/peoplenet.php")	return;				//automated process...skip
     	
          $use_user_id = 0;
          if(isset($_SESSION['user_id']))            $use_user_id = $_SESSION['user_id'];
     
          if(!isset($_SERVER['QUERY_STRING']))       $_SERVER['QUERY_STRING'] = '';
     
         // write the error to the database
             $sql = "
                  insert into error_log
                        (error_number,
                         error_string,
                         error_file,
                         error_line,
                         remote_address,
                         script_name,
                         query_string,
                         user_id,
                         linedate_added)
                  values
                    ('".sql_friendly($errno)."',
                      '".sql_friendly($errstr)."',
                      '".sql_friendly($errfile)."',
                      '".sql_friendly($errline)."',
                      '".sql_friendly($_SERVER['REMOTE_ADDR'])."',
                      '".sql_friendly($_SERVER['SCRIPT_NAME'])."',
                      '".sql_friendly($_SERVER['QUERY_STRING'])."',
                      '".sql_friendly($use_user_id)."',
                      now())
             ";
             
             if($log_errors) simple_query($sql);
             
             if(!empty($show_errors) && $show_errors) echo "$errstr in <strong>$errfile</strong> on line <strong>$errline</strong><br>";
     }
	
	
	$query_count = 0;
	
	$page_timer_array[] = "After Session Start: " . show_page_time();

	include('defaults.php');	
	
	// MySQL system100 connection
	$datasource = mysqli_connect($db_server, $db_username, $db_password, $db_name) or die("Could not connect to database server");
	//mysql_select_db($db_name);
	
	$page_timer_array[] = "After connecting to MySQL: " . show_page_time();
	
	//set our query_string and http_referer to local variables in case they are blank we can still use them.
	if(isset($_SERVER['HTTP_REFERER'])) $http_referer = $_SERVER['HTTP_REFERER']; else $http_referer = "";
	if(isset($_SERVER['QUERY_STRING'])) $query_string = $_SERVER['QUERY_STRING']; else $query_string = "";
	// because some pages modify the query_string, we'll set a second one that will never be modified
	$query_string_original = $query_string;
	
	if(!isset($SCRIPT_NAME)) $SCRIPT_NAME = $_SERVER['PHP_SELF'];
	
	/* load any default vars specified in the database */
	$sql = "
		select xname,
			xvalue_string
		
		from defaults
		where load_default = 1
	";
	$data_defaults = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql );
	
	while($row_defaults = mysqli_fetch_array($data_defaults)) {
		$defaultsarray[$row_defaults['xname']] = $row_defaults['xvalue_string'];
	}	
	
	if(isset($_GET['results_per_page'])) $_SESSION['results_per_page'] = $_GET['results_per_page'];
	
	
	if(isset($_COOKIE['uuid']))
	{
		//$tmp_id=0;
		//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')   
		$tmp_id=mrr_update_session_cookie(0,"",trim($_COOKIE['uuid']));
		//if($tmp_id > 0)	$_SESSION['user_id']=$tmp_id;	
	}
	
	/* admin check */
	if(isset($admin_page) && substr_count($_SERVER['PHP_SELF'],"customer_loads.php")==0) 
	{
		if(!isset($_SESSION['admin'])) 	header("Location: login.php");
		
		if(isset($_SESSION['user_id']))
		{	//force logout for a user that is no longer active.  Added 1/19/2017 for James since he thinks some former user has never been logged out and is still able to use system.
			$sqlu = "
          		select active,deleted          		
          		from users
          		where id = '".(int) $_SESSION['user_id']."'
          	";
          	$datau = mysqli_query($datasource, $sqlu) or die("database query failed! <br>". mysqli_error() . "<pre>". $sqlu );          	
          	if($rowu = mysqli_fetch_array($datau)) 
          	{
          		if($rowu['deleted'] > 0)		header("Location: login.php?out=1");		//deleted...drop them
          		if($rowu['active'] == 0)		header("Location: login.php?out=1");		//no longer active... drop them,
          	}	
		}
	}
	
	// diabled by CS for now - 10/13/2014
	// would like the browser to take advantage of caching. If a page needs to force reload, then
	// we need to add a random url param to the include to foce the reload (i.e. ?test.php?random=time())
	//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	
	
	//include_once('class/class.phpmailer.php');
     //include_once('class/class.smtp.php');
	include_once('functions/sendmail.php');
	include('functions_sicap.php');
	include('functions_gen_tracking.php');
	include('functions_peoplenet.php');
	include('functions_peoplenet2vol.php');
	include('functions_peoplenet3vol.php');
	include('functions_comparison.php');	
	include('functions_lane_analyzer.php');
	include('functions_driver_planning.php');
	include('includes/sicap_class.php');
	include('load_board_include.php');
	include('functions_phone.php');
	
	include('functions_payroll.php');
	
	$page_timer_array[] = "After loading Include files: " . show_page_time();
	
	//New admin user access levels code here........................................................
	if(isset($_SESSION['user_id']) && $SCRIPT_NAME!="/ajax.php")
	{
		//if(mrr_get_user_menu_access_level($SCRIPT_NAME,$_SESSION['user_id']) == 0)	{	header("Location: user_access_restricted.php");	die();		}
		
		//if(mrr_get_user_menu_access_level($SCRIPT_NAME,$_SESSION['user_id']) == 0)	{	if($_SERVER['REMOTE_ADDR']=="70.90.229.29")	{	header("Location: user_access_restricted.php");	die();	}	}
	}
	//..............................................................................................
	
	
	//initialized here for multi-page 'user_action_log' tracking
	$mrr_activity_log_driver=0;
     $mrr_activity_log_truck=0;
     $mrr_activity_log_trailer=0;
     $mrr_activity_log_load=0;
     $mrr_activity_log_dispatch=0;
     $mrr_activity_log_stop=0;   
     $mrr_activity_log_refer="";  
     $mrr_activity_log_notes="";
     
     $mrr_save_preloader=0;
     $mrr_run_preloader=0;
     
     $use_new_uploader=1;
	//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' || $_SERVER['HTTP_CF_CONNECTING_IP']=="70.90.229.29")		$use_new_uploader=1;
	
	$_SESSION['use_geotab_vs_pn']=0;
	if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		$_SESSION['use_geotab_vs_pn']=1;
	
     if(!isset($upload_counter))		$upload_counter=1;
     
     if(!isset($upload_section_id))	$upload_section_id=0;
     if(!isset($upload_xref_id))		$upload_xref_id=0;
     if(!isset($upload_user_id))		$upload_user_id=0;    
     
     if(!isset($_SESSION['force_logout']))				$_SESSION['force_logout']=0;
     if(!isset($_SESSION['geotab_route_url']))			$_SESSION['geotab_route_url']="";
?>