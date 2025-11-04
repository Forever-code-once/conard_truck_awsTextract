<? 
	//ini_set("max_input_vars","20000");  Must change in INI file...
	error_reporting(0);
	
	// generate a starting timestamp for performance testing purposes
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$page_start = $time;
	$page_timer_array = array();

	date_default_timezone_set("America/Chicago");

	session_start();	
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');	
	$query_count = 0;
	$datasource=0;
	
	//set our query_string and http_referer to local variables in case they are blank we can still use them.
	if(isset($_SERVER['HTTP_REFERER'])) $http_referer = $_SERVER['HTTP_REFERER']; else $http_referer = "";
	if(isset($_SERVER['QUERY_STRING'])) $query_string = $_SERVER['QUERY_STRING']; else $query_string = "";
	// because some pages modify the query_string, we'll set a second one that will never be modified
	$query_string_original = $query_string;	
	
	if(!isset($SCRIPT_NAME)) $SCRIPT_NAME = $_SERVER['PHP_SELF'];
	
	
	$valid_access=0;
	if($_SERVER['REMOTE_ADDR']=="50.76.161.186")
	{	/* admin check */	//  50.76.161.186
		$valid_access=1;
		
	}
	
	
$global_header="
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\"   \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
<head>
<title>".$usetitle."</title>
</head>
<script type='text/javascript'>
	".$usescript."
</script>
<body>
<!-- start of page -->
";


$global_footer="
<!-- end of page -->
</body>
</html>
";
	
	//function libary for application...
	function simple_query($sql) 
	{		
		global $query_count;
		// MySQL connection
		$db_server = 'localhost';
		$db_username = 'conard';
		$db_password = '6379874nbz2198';
		$db_name = 'conard_trucking_logs';
	
		$datasource = mysqli_connect($db_server, $db_username, $db_password, $db_name) or die("Could not connect to database server");
		
		$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql );
		$query_count++;
		//mysql_close($datasource);
		return $data;
	}
	function select_mrr_yes_no($field_name,$preselect,$width=50)
	{
		$selbx="<select name='".$field_name."' id='".$field_name."' style='width:".$width."px;'>";
		
		$sel="";		if(!isset($preselect) || $preselect=="" || $preselect==0)		$sel=" selected";		
		$selbx.="<option value='0'".$sel.">No</option>";
		
		$sel="";		if($preselect==1)		$sel=" selected";		
		$selbx.="<option value='1'".$sel.">Yes</option>";
		
		$selbx.="</select>";		
		return $selbx;
	}
	
	
	//users
	function get_from_users($id)
	{
		$res['id']=0;
		$res['name']="";
		$res['first_name']="";
		$res['last_name']="";
		$res['active']=0;
		$res['deleted']=0;
		$res['date']="00/00/0000 00:00";
		$res['stamp']="0000-00-00 00:00:00";
		
		$sql="
			select * 
			from time_manage_users
			where id='".(int)$id."'
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$res['id']=$row['id'];
			$res['name']=$row['user_name'];
			$res['first_name']=$row['first_name'];
			$res['last_name']=$row['last_name'];
			$res['active']=$row['active'];
			$res['deleted']=$row['deleted'];
			$res['date']=date("m/d/Y G:i",strtotime($row['linedate_added']));
			$res['stamp']=$row['linedate_added'];	
		}
		return $res;
	}
	function select_from_users($field_name,$preselect,$prompt="",$width=300,$cd=0,$autosubmit=0)
	{
		$java="";
		if($autosubmit==1)
		{
			$java=" onChange='submit();'";	
		}
		
		$selbx="<select name='".$field_name."' id='".$field_name."' style='width:".$width."px;'".$java.">";
		
		$sel="";		if(!isset($preselect) || $preselect=="" || $preselect==0)		$sel=" selected";		
		$selbx.="<option value='0'".$sel.">".trim($prompt)."</option>";
		
		$where="";
		if($cd > 0)	$where.=" and active='".$cd."'";
		
		$sql="
			select * 
			from time_manage_users
			where deleted=0
				".$where."
			order by user_name asc,
				id asc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$sel="";		if($preselect==$row['id'])		$sel=" selected";		
			$selbx.="<option value='".$row['id']."'".$sel.">".trim($row['user_name'])."</option>";	
		}		
		
		$selbx.="</select>";
		
		return $selbx;
	}
	
	//cats
	function get_from_cats($id)
	{
		$res['id']=0;
		$res['name']="";
		$res['active']=0;
		$res['deleted']=0;
		$res['date']="00/00/0000 00:00";
		$res['stamp']="0000-00-00 00:00:00";
		
		$sql="
			select * 
			from time_manage_cats
			where id='".(int)$id."'
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$res['id']=$row['id'];
			$res['name']=$row['cat_name'];
			$res['active']=$row['active'];
			$res['deleted']=$row['deleted'];
			$res['date']=date("m/d/Y G:i",strtotime($row['linedate_added']));
			$res['stamp']=$row['linedate_added'];	
		}
		return $res;
	}
	function select_from_cats($field_name,$preselect,$prompt="",$width=300,$cd=0,$autosubmit=0)
	{
		$java="";
		if($autosubmit==1)
		{
			$java=" onChange='submit();'";	
		}
		
		$selbx="<select name='".$field_name."' id='".$field_name."' style='width:".$width."px;'".$java.">";
		
		$sel="";		if(!isset($preselect) || $preselect=="" || $preselect==0)		$sel=" selected";		
		$selbx.="<option value='0'".$sel.">".trim($prompt)."</option>";
		
		$where="";
		if($cd > 0)	$where.=" and active='".$cd."'";
		
		$sql="
			select * 
			from time_manage_cats
			where deleted=0
				".$where."
			order by cat_name asc,
				id asc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$sel="";		if($preselect==$row['id'])		$sel=" selected";		
			$selbx.="<option value='".$row['id']."'".$sel.">".trim($row['cat_name'])."</option>";	
		}		
		
		$selbx.="</select>";
		
		return $selbx;
	}
	
	
	//customers
	function get_from_customers($id)
	{
		$res['id']=0;
		$res['name']="";
		$res['contact_name']="";
		$res['active']=0;
		$res['deleted']=0;
		$res['date']="00/00/0000 00:00";
		$res['stamp']="0000-00-00 00:00:00";
		
		$sql="
			select * 
			from time_manage_customers
			where id='".(int)$id."'
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$res['id']=$row['id'];
			$res['name']=$row['customer_name'];
			$res['contact_name']=$row['contact_name'];
			$res['active']=$row['active'];
			$res['deleted']=$row['deleted'];
			$res['date']=date("m/d/Y G:i",strtotime($row['linedate_added']));
			$res['stamp']=$row['linedate_added'];	
		}
		return $res;
	}
	function select_from_customers($field_name,$preselect,$prompt="",$width=300,$cd=0,$autosubmit=0)
	{
		$java="";
		if($autosubmit==1)
		{
			$java=" onChange='submit();'";	
		}
		
		$selbx="<select name='".$field_name."' id='".$field_name."' style='width:".$width."px;'".$java.">";
		
		$sel="";		if(!isset($preselect) || $preselect=="" || $preselect==0)		$sel=" selected";		
		$selbx.="<option value='0'".$sel.">".trim($prompt)."</option>";
		
		$where="";
		if($cd > 0)	$where.=" and active='".$cd."'";
		
		$sql="
			select * 
			from time_manage_customers
			where deleted=0
				".$where."
			order by customer_name asc,
				id asc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$sel="";		if($preselect==$row['id'])		$sel=" selected";		
			$selbx.="<option value='".$row['id']."'".$sel.">".trim($row['customer_name'])."</option>";	
		}		
		
		$selbx.="</select>";
		
		return $selbx;
	}
		
	//projects
	function get_from_projects($id)
	{
		$res['id']=0;
		$res['cust_id']=0;
		$res['name']="";
		$res['contact_name']="";
		$res['active']=0;
		$res['deleted']=0;
		$res['date']="00/00/0000 00:00";
		$res['stamp']="0000-00-00 00:00:00";
		
		$sql="
			select * 
			from time_manage_projects
			where id='".(int)$id."'
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$res['id']=$row['id'];
			$res['cust_id']=$row['customer_id'];
			$res['name']=$row['project_name'];
			$res['contact_name']=$row['project_lead'];
			$res['active']=$row['active'];
			$res['deleted']=$row['deleted'];
			$res['date']=date("m/d/Y G:i",strtotime($row['linedate_added']));
			$res['stamp']=$row['linedate_added'];	
		}
		return $res;
	}
	function select_from_projects($field_name,$preselect,$prompt="",$width=300,$cd=0,$autosubmit=0,$mode=0)
	{
		$java="";
		if($autosubmit==1)
		{
			$java=" onChange='submit();'";	
		}
		
		$selbx="<select name='".$field_name."' id='".$field_name."' style='width:".$width."px;'".$java.">";
		
		$sel="";		if(!isset($preselect) || $preselect=="" || $preselect==0)		$sel=" selected";		
		$selbx.="<option value='0'".$sel.">".trim($prompt)."</option>";
		
		$where="";
		if($cd > 0)	$where.=" and active='".$cd."'";
		if($mode > 0)	$where.=" and customer_id='".$mode."'";
		
		$sql="
			select * 
			from time_manage_projects
			where deleted=0
				".$where."
			order by project_name asc,
				id asc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$sel="";		if($preselect==$row['id'])		$sel=" selected";		
			$selbx.="<option value='".$row['id']."'".$sel.">".trim($row['project_name'])."</option>";	
		}		
		
		$selbx.="</select>";
		
		return $selbx;
	}
	
	//hours
	function get_from_hours($id)
	{
		$res['id']=0;
		$res['user_id']=0;
		$res['project_id']=0;
		$res['cat_id']=0;		
		$res['notes']="";
		$res['start_date']="00/00/0000 00:00";
		$res['start_stamp']="0000-00-00 00:00:00";
		$res['end_date']="00/00/0000 00:00";
		$res['end_stamp']="0000-00-00 00:00:00";
		$res['active']=0;
		$res['deleted']=0;
		$res['hours']=0;
		$sql="
			select * 
			from time_manage_hours
			where id='".(int)$id."'
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$res['id']=$row['id'];
			$res['user_id']=$row['user_id'];
			$res['project_id']=$row['project_id'];
			$res['cat_id']=$row['cat_id'];
			$res['notes']=$row['notes'];			
			$res['start_date']=date("m/d/Y G:i",strtotime($row['linedate_started']));
			$res['start_stamp']=$row['linedate_started'];
			$res['end_date']=date("m/d/Y G:i",strtotime($row['linedate_ended']));
			$res['end_stamp']=$row['linedate_ended'];			
			$res['active']=$row['active'];
			$res['deleted']=$row['deleted'];
			$res['hours']=$row['hours'];
		}
		return $res;
	}
	
	
?>