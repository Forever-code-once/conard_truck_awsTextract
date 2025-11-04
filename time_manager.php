<?
$usetitle="Time Manager";
$usescript="";
include('time_manager_functions.php');

echo $global_header;

$content="<form name='main_form' action='".$SCRIPT_NAME."' method='POST'>";

if($valid_access==1)
{
	//page contents goes here
	$msg="";
	
	//presets
	if(!isset($_POST['user_id']))			$_POST['user_id']=0;
	if(!isset($_POST['last_hours_id']))	$_POST['last_hours_id']=0;
	
	if(!isset($_POST['cat_id']))			$_POST['cat_id']=0;
	if(!isset($_POST['cat_name']))		$_POST['cat_name']="";
	if(!isset($_POST['cat_active']))		$_POST['cat_active']=0;
	if(!isset($_POST['cat_delete']))		$_POST['cat_delete']=0;
	
	if(isset($_POST['save_cat']))
	{
		if($_POST['cat_id'] == 0)
		{
			$sql="
				insert into time_manage_cats 
					(id,
					linedate_added,
					deleted)
				values
					(NULL,
					NOW(),
					0)
			";
			simple_query($sql);
			$_POST['cat_id']=mysqli_insert_id($datasource);
		}
		
		if($_POST['cat_id'] > 0)
		{
			$sql="
				update time_manage_cats set
					cat_name='".addslashes($_POST['cat_name'])."',
					active='".(int)$_POST['cat_active']."',
					deleted='".(int)$_POST['cat_delete']."'
				where id='".(int)$_POST['cat_id']."'
			";
			simple_query($sql);	
			$msg.="<span style='color:green; font-weight:bold;'>Category <b>".$_POST['cat_name']."</b> has been saved.</span>";
		}
		else
		{
			$msg.="<span style='color:red; font-weight:bold;'><b>ERROR:</b> Category <b>".$_POST['cat_name']."</b> cannot be saved.</span>";
		}
	}
	
	if(!isset($_POST['customer_id']))			$_POST['customer_id']=0;
	if(!isset($_POST['customer_name']))		$_POST['customer_name']="";
	if(!isset($_POST['customer_contact']))		$_POST['customer_contact']="";
	if(!isset($_POST['customer_active']))		$_POST['customer_active']=0;
	if(!isset($_POST['customer_delete']))		$_POST['customer_delete']=0;
	
	if(isset($_POST['save_customer']))
	{
		if($_POST['customer_id'] == 0)
		{
			$sql="
				insert into time_manage_customers 
					(id,
					linedate_added,
					deleted)
				values
					(NULL,
					NOW(),
					0)
			";
			simple_query($sql);
			$_POST['customer_id']=mysqli_insert_id($datasource);
		}
		
		if($_POST['customer_id'] > 0)
		{
			$sql="
				update time_manage_customers set
					customer_name='".addslashes($_POST['customer_name'])."',
					contact_name='".addslashes($_POST['customer_contact'])."',
					active='".(int)$_POST['customer_active']."',
					deleted='".(int)$_POST['customer_delete']."'
				where id='".(int)$_POST['customer_id']."'
			";
			simple_query($sql);	
			$msg.="<span style='color:green; font-weight:bold;'>Customer <b>".$_POST['customer_name']."</b> has been saved.</span>";
		}
		else
		{
			$msg.="<span style='color:red; font-weight:bold;'><b>ERROR:</b> Customer <b>".$_POST['customer_name']."</b> cannot be saved.</span>";
		}
	}
	
	if(!isset($_POST['project_id']))			$_POST['project_id']=0;
	if(!isset($_POST['project_cust_id']))		$_POST['project_cust_id']=0;
	if(!isset($_POST['project_name']))			$_POST['project_name']="";
	if(!isset($_POST['project_contact']))		$_POST['project_contact']="";
	if(!isset($_POST['project_active']))		$_POST['project_active']=0;
	if(!isset($_POST['project_delete']))		$_POST['project_delete']=0;
	
	if(isset($_POST['save_project']))
	{
		if($_POST['project_id'] == 0)
		{
			$sql="
				insert into time_manage_projects 
					(id,
					linedate_added,
					deleted)
				values
					(NULL,
					NOW(),
					0)
			";
			simple_query($sql);
			$_POST['project_id']=mysqli_insert_id($datasource);
		}
		
		if($_POST['project_id'] > 0)
		{
			$sql="
				update time_manage_projects set
					customer_id='".(int)($_POST['project_cust_id'])."',
					project_name='".addslashes($_POST['project_name'])."',
					project_lead='".addslashes($_POST['project_contact'])."',
					active='".(int)$_POST['project_active']."',
					deleted='".(int)$_POST['project_delete']."'
				where id='".(int)$_POST['project_id']."'
			";
			simple_query($sql);	
			$msg.="<span style='color:green; font-weight:bold;'>Project <b>".$_POST['project_name']."</b> has been saved.</span>";
		}
		else
		{
			$msg.="<span style='color:red; font-weight:bold;'><b>ERROR:</b> Project <b>".$_POST['project_name']."</b> cannot be saved.</span>";
		}
	}
	
	
	
	
	//build page components for form
	$content.="<table width='1000' cellpadding='0' cellspacing='0' border='0'>";
	$content.="<tr>";
	$content.=	"<td valign='top' align='left'>User:</td>";
	$content.=	"<td valign='top' align='left'>".select_from_users('user_id',$_POST['user_id'],"",200,1,1)."</td>";
	$content.=	"<td valign='top' align='right'>&nbsp;</td>";
	$content.="</tr>";
	$content.="<tr>";
	$content.=	"<td valign='top' align='center' colspan='3'><br><hr><br></td>";
	$content.="</tr>";
	$content.="<tr>";
	$content.=	"<td valign='top' align='center' colspan='3'>".$msg."</td>";
	$content.="</tr>";
	
	
	if($_POST['user_id'] > 0)
	{
		$ures=get_from_users($_POST['user_id']);
		
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>Welcome:</td>";
		$content.=	"<td valign='top' align='left'>".$ures['first_name']." ".$ures['last_name']."</td>";
		$content.=	"<td valign='top' align='right'>".$ures['date']."</td>";
		$content.="</tr>";
		
		
		
		
		
		
		
		$content.="<tr>";
		$content.=	"<td valign='top' align='center' colspan='3'><br><hr><br></td>";
		$content.="</tr>";
		
		//categories
		$cares=get_from_cats($_POST['cat_id']);
		$_POST['cat_name']=$cares['name'];
		$_POST['cat_active']=$cares['active'];
		$_POST['cat_delete']=$cares['deleted'];
		
		$content.="<tr bgcolor='#eeeeee'>";
		$content.=	"<td valign='top' align='left'>Cats:</td>";
		$content.=	"<td valign='top' align='left'>".select_from_cats('cat_id',$_POST['cat_id'],"New Cat",200,0,1)."</td>";
		$content.=	"<td valign='top' align='right'><input type='submit' name='save_cat' id='save_cat' value='Save Cat'></td>";
		$content.="</tr>";		
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Cat Name:</td>";
		$content.=	"<td valign='top' align='left'><input type='text' name='cat_name' id='cat_name' value='".$_POST['cat_name']."'></td>";
		$content.=	"<td valign='top' align='right'>".$cares['date']."</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Cat Active:</td>";
		$content.=	"<td valign='top' align='left'>".select_mrr_yes_no('cat_active',$_POST['cat_active'])."</td>";
		$content.=	"<td valign='top' align='right'>&nbsp;</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Cat Delete:</td>";
		$content.=	"<td valign='top' align='left'>".select_mrr_yes_no('cat_delete',$_POST['cat_delete'])."</td>";
		$content.=	"<td valign='top' align='right'>&nbsp;</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='center' colspan='3'>&nbsp;</td>";
		$content.="</tr>";		
		
		
		//customers
		$cures=get_from_customers($_POST['customer_id']);
		$_POST['customer_name']=$cures['name'];
		$_POST['customer_contact']=$cures['contact_name'];
		$_POST['customer_active']=$cures['active'];
		$_POST['customer_delete']=$cures['deleted'];
		
		$content.="<tr bgcolor='#eeeeee'>";
		$content.=	"<td valign='top' align='left'>Customers:</td>";
		$content.=	"<td valign='top' align='left'>".select_from_customers('customer_id',$_POST['customer_id'],"New Customer",200,0,1)."</td>";
		$content.=	"<td valign='top' align='right'><input type='submit' name='save_customer' id='save_customer' value='Save Customer'></td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Customer Name:</td>";
		$content.=	"<td valign='top' align='left'><input type='text' name='customer_name' id='customer_name' value='".$_POST['customer_name']."'></td>";
		$content.=	"<td valign='top' align='right'>".$cures['date']."</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Customer Contact:</td>";
		$content.=	"<td valign='top' align='left'><input type='text' name='customer_contact' id='customer_contact' value='".$_POST['customer_contact']."'></td>";
		$content.=	"<td valign='top' align='right'>&nbsp;</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Customer Active:</td>";
		$content.=	"<td valign='top' align='left'>".select_mrr_yes_no('customer_active',$_POST['customer_active'])."</td>";
		$content.=	"<td valign='top' align='right'>&nbsp;</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Customer Delete:</td>";
		$content.=	"<td valign='top' align='left'>".select_mrr_yes_no('customer_delete',$_POST['customer_delete'])."</td>";
		$content.=	"<td valign='top' align='right'>&nbsp;</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='center' colspan='3'>&nbsp;</td>";
		$content.="</tr>";
		
		
		//projects
		$pjres=get_from_projects($_POST['project_id']);
		$_POST['project_cust_id']=$pjres['cust_id'];
		$_POST['project_name']=$pjres['name'];
		$_POST['project_contact']=$pjres['contact_name'];
		$_POST['project_active']=$pjres['active'];
		$_POST['project_delete']=$pjres['deleted'];	
		
		$content.="<tr bgcolor='#eeeeee'>";
		$content.=	"<td valign='top' align='left'>Projects:</td>";
		$content.=	"<td valign='top' align='left'>".select_from_projects('project_id',$_POST['project_id'],"New Project",200,0,1,0)."</td>";		//$_POST['customer_id']
		$content.=	"<td valign='top' align='right'><input type='submit' name='save_project' id='save_project' value='Save Project'></td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Project Customer:</td>";
		$content.=	"<td valign='top' align='left'>".select_from_customers('project_cust_id',$_POST['project_cust_id'],"None",200,1,0)."</td>";
		$content.=	"<td valign='top' align='right'>".$pjres['date']."</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Project Name:</td>";
		$content.=	"<td valign='top' align='left'><input type='text' name='project_name' id='project_name' value='".$_POST['project_name']."'></td>";
		$content.=	"<td valign='top' align='right'>&nbsp;</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Project Contact/Lead:</td>";
		$content.=	"<td valign='top' align='left'><input type='text' name='project_contact' id='project_contact' value='".$_POST['project_contact']."'></td>";
		$content.=	"<td valign='top' align='right'>&nbsp;</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Project Active:</td>";
		$content.=	"<td valign='top' align='left'>".select_mrr_yes_no('project_active',$_POST['project_active'])."</td>";
		$content.=	"<td valign='top' align='right'>&nbsp;</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Project Delete:</td>";
		$content.=	"<td valign='top' align='left'>".select_mrr_yes_no('project_delete',$_POST['project_delete'])."</td>";
		$content.=	"<td valign='top' align='right'>&nbsp;</td>";
		$content.="</tr>";
		$content.="<tr>";
		$content.=	"<td valign='top' align='center' colspan='3'>&nbsp;</td>";
		$content.="</tr>";
	}
	
	
	$content.="<tr>";
	$content.=	"<td valign='top' align='center' colspan='3'><br><hr><br></td>";
	$content.="</tr>";
	$content.="</table>";
	
	//hidden variables
	$content.="
		<input type='hidden' name='last_hours_id' id='last_hours_id' value='".$_POST['last_hours_id']."'>
	";	
	//end page contents
}

$content.="</form>";

echo $content;
echo $global_footer;
?>