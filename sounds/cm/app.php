<?
	//error_reporting(E_ALL ^ E_DEPRECATED);
	error_reporting(0);
	date_default_timezone_set("America/Chicago");

	session_start();	
	
	$full_admin=0;	
	if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')
     {
         //ini_set('display_errors', 1);
         //ini_set('display_startup_errors', 1);
         //error_reporting(E_ALL);
         error_reporting(E_ALL ^ E_DEPRECATED);
         $full_admin=1;
     }
	
	
	// MySQL system100 connection
	$db_server = 'conard-cluster-cluster.cluster-ctljjcc4qcdj.us-east-1.rds.amazonaws.com';
	$db_username = 'webuser';
	$db_password = 'football2119';
	$db_name = 'websites';
	
	$use_mysql_i=1;
	global $datasource;
	if($use_mysql_i > 0)
	{
		$datasource = mysqli_connect($db_server, $db_username, $db_password,$db_name) or die("Could not connect to database server");
		//mysql_select_db($db_name);
	}
	else
	{
		$datasource = mysql_connect($db_server, $db_username, $db_password) or die("Could not connect to database server");
		mysql_select_db($db_name,$datasource);
	}
	
	
	//$_SERVER['HTTP_HOST']
	//$_SERVER['SCRIPT_NAME']
	
	function sql_friendly($sql_string) 
	{		
		global $datasource;
		$hold = mysqli_real_escape_string($datasource, $sql_string);	
		
		//$hold = str_replace("'","''",$sql_string);
		//$hold = str_replace("//","////",$hold);
					
		return $hold;	
	}
	function simple_query($sql) 
	{
		global $datasource;
		
		//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29') echo "$sql<br>";
		
		$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql );
		
		return $data;
	}
	
	function mrr_header($title)
	{
		$tab="
		<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\"   \"http://www.w3.org/TR/html4/strict.dtd\">
		<html>
		<head>
			<title>".$title."</title>	
			
			<script type='text/javascript'>
				
			</script>
		</head>
		<body style='background-color:#F8F3E4'>
			<div width='100%' align='center' style='background-color:#0000CC;'>
				<div width='1000' align='left' style='border:1px solid #0000CC; background-color:#FFFFFF; padding:10px;'>
					
					<table cellpadding='0' cellspacing='0' width='100%' border='0'>
					<tr>
						<td valign='top' width='25%'><a href='reg.php'><b>Home</b></a></td>
						<td valign='top' width='25%'><a href='item.php'><b>Settings</b></a></td>
						<td valign='top' width='25%'><a href='rep.php'><b>Reports</b></a></td>
						<td valign='top' width='25%'><a href='reg.php'><b>Home</b></a></td>						
					</tr>					
					</table>
					<h2>".$title."</h2>
		";
			
		return $tab;	
	}
	
	function mrr_footer()
	{
		$tab="
				</div>
			</div>
		</body>
		</html>
		";				
		return $tab;	
	}
	
	function mrr_depts($field,$pre=0,$cd=0)
	{
		$tab="";
		
		$tab.="<select name='".$field."' id='".$field."'>";
		if($cd==1)
		{
			$sel="";				if($pre==0)	$sel=" selected";	
     			
          	$tab.="<option value='0'".$sel.">Select Dept</option>";	
		}
		
		$sql = "
			select * 	
			from cm_depts		
			where dept_name!=''
			order by dept_id asc
		";	
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
     	{	
     		$sel="";				if($pre==$row['dept_id'])	$sel=" selected";	
     			
          	$tab.="<option value='".$row['dept_id']."'".$sel.">".$row['dept_id']." - ".trim($row['dept_name'])."</option>";
     	}	
     	
     	$tab.="</select>";
     	return $tab;
	}
	
	function mrr_sizes($field,$pre="",$cd=0)
	{
		$tab="";
		
		$tab.="<select name='".$field."' id='".$field."'>";
		if($cd==1)
		{
			$sel="";				if($pre=="0")	$sel=" selected";	     			
          	$tab.="<option value='0'".$sel.">Select Size</option>";	
		}
		
     	$max=13;
     	$size[0]="sunit";		$label[0]="Number";
     	$size[1]="cxs";		$label[1]="C-XS";
     	$size[2]="csm";		$label[2]="C-S";
     	$size[3]="cm";			$label[3]="C-M";
     	$size[4]="cl";			$label[4]="C-L";
     	$size[5]="cxl";		$label[5]="C-XL";
     	$size[6]="axs";		$label[6]="XS";
     	$size[7]="asm";		$label[7]="S";
     	$size[8]="am";			$label[8]="M";
     	$size[9]="al";			$label[9]="L";
     	$size[10]="axl";		$label[10]="XL";
     	$size[11]="axxl";		$label[11]="XXL";
     	$size[12]="axxxl";		$label[12]="XXXL";
     	$size[13]="axxxxl";		$label[13]="XXXXL";
     	
     	for($i=0;$i <= $max; $i++)
     	{     	
     		$sel="";				if($pre==$size[$i])	$sel=" selected";	     			
          	$tab.="<option value='".$size[$i]."'".$sel.">".trim($label[$i])."</option>";
     	}
     	$tab.="</select>";
     	return $tab;
	}
	
	function mrr_numbers($field,$pre=0,$min=0,$max=0,$cd=0)
	{
		$tab="";
		
		$tab.="<select name='".$field."' id='".$field."'>";
		if($cd==1)
		{
			$sel="";				if($pre==-1)	$sel=" selected";	
     		$tab.="<option value='-1'".$sel.">Select Size</option>";	
		}
     	
     	for($i=$min;$i <= $max; $i++)
     	{     	
     		$sel="";				if($pre==$i)	$sel=" selected";	     			
          	$tab.="<option value='".$i."'".$sel.">".$i."</option>";
     	}
     	$tab.="</select>";
     	return $tab;
	}
	
	function mrr_active($field,$pre=0,$cd=0)
	{
		$tab="";
		
		$tab.="<select name='".$field."' id='".$field."'>";
		if($cd==1)
		{
			$sel="";			if($pre==-1)	$sel=" selected";	
     		$tab.="<option value='-1'".$sel.">Toggle</option>";	
		}
     	
     	$sel="";				if($pre==0)	$sel=" selected";	     			
          $tab.="<option value='0'".$sel.">Hide</option>";
          $sel="";				if($pre==1)	$sel=" selected";	     			
          $tab.="<option value='1'".$sel.">Show</option>";
          
     	$tab.="</select>";
     	return $tab;
	}
?>