<?
include('app.php');

$tab1="";
$tab2="";
$tab3="";

if(isset($_POST['save_settings']))
{
	$sqlu = "update cm_settings set setting_value='".sql_friendly(trim($_POST['tax']))."' where setting_id='1'";
	//$tab1.="<br>Q1: ".$sqlu.".<br>";
	simple_query($sqlu);
	
	$sqlu = "update cm_settings set setting_value='".sql_friendly(trim($_POST['admin']))."' where setting_id='2'";
	//$tab1.="<br>Q2: ".$sqlu.".<br>";
	simple_query($sqlu);
		
	$sqlu = "update cm_settings set setting_value='".sql_friendly(trim($_POST['clerk1']))."' where setting_id='3'";
	//$tab1.="<br>Q3: ".$sqlu.".<br>";
	simple_query($sqlu);	
	
	$sqlu = "update cm_settings set setting_value='".sql_friendly(trim($_POST['clerk2']))."' where setting_id='4'";
	//$tab1.="<br>Q4: ".$sqlu.".<br>";
	simple_query($sqlu);	
}
if(isset($_POST['save_depts']))
{
	$dept_max=(int) $_POST['max_depts'];	
	for($i=0; $i < $dept_max; $i++)
	{
		$tmp_id=(int) $_POST["id_".$i.""];	
		$tmp_name=trim($_POST["dept_name_".$i.""]);	
		$tmp_code=trim($_POST["dept_code_".$i.""]);	
		$tmp_reg=trim($_POST["reg_code_".$i.""]);	
		$tmp_note=trim($_POST["item_note_".$i.""]);	
		
		$sqlu = "
			update cm_depts set 
				dept_name='".sql_friendly($tmp_name)."', 
				dept_code='".sql_friendly($tmp_code)."',
				reg_code='".sql_friendly($tmp_reg)."',
				item_notes='".sql_friendly($tmp_note)."'
				 
			where dept_id='".sql_friendly($tmp_id)."'";
		//$tab1.="<br>Q".$i.": ".$sqlu.".<br>";
		simple_query($sqlu);
	}
}
if(isset($_POST['add_item']))
{
	$sqlu = "
		insert into cm_items
			(item_id,item_name,active)
		values 
			(NULL,'',1) 
	";
	//$tab1.="<br>Q".$i.": ".$sqlu.".<br>";
	simple_query($sqlu);
}
if(isset($_POST['save_items']))
{
	$item_max=(int) $_POST['max_items'];	
	for($i=0; $i < $item_max; $i++)
	{
		$tmp_id=(int) $_POST["item_".$i.""];	
		$tmp_name=trim($_POST["item_name_".$i.""]);	
		$tmp_code=trim($_POST["item_code_".$i.""]);	
		$tmp_note=trim($_POST["item_details_".$i.""]);	
		$tmp_dept=trim($_POST["dept_id_".$i.""]);	
		$tmp_active=trim($_POST["active_".$i.""]);
		$tmp_cost=trim($_POST["cost_".$i.""]);
		$tmp_price=trim($_POST["price_".$i.""]);
		$tmp_sprice=trim($_POST["sprice_".$i.""]);
		
		$sqlu = "
			update cm_items set 
				item_name='".sql_friendly($tmp_name)."', 
				item_code='".sql_friendly($tmp_code)."',
				dept_id='".sql_friendly($tmp_dept)."',
				cost='".sql_friendly($tmp_cost)."',
				price='".sql_friendly($tmp_price)."',
				sprice='".sql_friendly($tmp_sprice)."',
				active='".sql_friendly($tmp_active)."',
				item_details='".sql_friendly($tmp_note)."'
				 
			where item_id='".sql_friendly($tmp_id)."'";
		//$tab1.="<br>Q".$i.": ".$sqlu.".<br>";
		simple_query($sqlu);
	}	
}

//$full_admin=0;
if($full_admin > 0)
{
     $tab1.="<form name='settings' action='item.php' method='post'>";
     $tab1.="<div style='font-weight:bold; color:purple;'>Primary Settings:</div>";
     $tab1.="<table cellpadding='0' cellspacing='0' width='100%' border='0'>";
     $cntr=0;
     
     $sql = "
		select * 	
		from cm_settings		
		order by setting_id asc
	";	//where category_name = '".sql_friendly($category_name)."'
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{		
     	$tab1.="<tr>";
     	$tab1.=	"<td valign='top'>";
     	$tab1.=		"".$row['setting_id']." <b>".$row['setting_label']."</b>";
     	$tab1.=	"</td>";
     	$tab1.=	"<td valign='top' width='80%'>";
     	$tab1.=		"<input type='text' name='".$row['setting_name']."' id='".$row['setting_name']."' value=\"".$row['setting_value']."\">";
     	$tab1.=	"</td>";
     	$tab1.="</tr>";
	}
     $tab1.="<tr>";
     $tab1.=	"<td valign='top'>";
     $tab1.=		"<b>&nbsp;</b>";
     $tab1.=	"</td>";
     $tab1.=	"<td valign='top'>";
     $tab1.=		"<input type='submit' name='save_settings' value='Save Settings'>";
     $tab1.=	"</td>";
     $tab1.="</tr>";
     $tab1.="</table><input type='hidden' name='max_settings' id='max_settings' value='".$cntr."'>";
     $tab1.="</form>";   
     
     
     
     $tab2.="<form name='depts' action='item.php' method='post'>";
     $tab2.="<div style='font-weight:bold; color:purple;'>Departments:</div>";
     $tab2.="<table cellpadding='0' cellspacing='0' width='100%' border='0'>";
     
     	$tab2.="<tr>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<b>Dept</b>";
     	$tab2.=	"</td>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<b>Name</b>";
     	$tab2.=	"</td>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<b>Code</b>";
     	$tab2.=	"</td>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<b>Reg Code</b>";
     	$tab2.=	"</td>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<b>Notes</b>";
     	$tab2.=	"</td>";
     	$tab2.="</tr>";
     	
     $cntr=0;
     $sql = "
		select * 	
		from cm_depts		
		order by dept_id asc
	";	//where category_name = '".sql_friendly($category_name)."'
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{		
     	$tab2.="<tr>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<b>".$row['dept_num']." <input type='hidden' name='id_".$cntr."' id='id_".$cntr."' value='".$row['dept_id']."'></b>";
     	$tab2.=	"</td>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<input type='text' name='dept_name_".$cntr."' id='dept_name_".$cntr."' value=\"".trim($row['dept_name'])."\">";
     	$tab2.=	"</td>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<input type='text' name='dept_code_".$cntr."' id='dept_code_".$cntr."' value=\"".trim($row['dept_code'])."\">";
     	$tab2.=	"</td>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<input type='text' name='reg_code_".$cntr."' id='reg_code_".$cntr."' value=\"".trim($row['reg_code'])."\">";
     	$tab2.=	"</td>";
     	$tab2.=	"<td valign='top'>";
     	$tab2.=		"<input type='text' name='item_note_".$cntr."' id='item_note_".$cntr."' value=\"".trim($row['item_notes'])."\">";
     	$tab2.=	"</td>";
     	$tab2.="</tr>";
     	$cntr++;
	}
     $tab2.="<tr>";
     $tab2.=	"<td valign='top' colspan='4'>";
     $tab2.=		"<b>&nbsp;</b>";
     $tab2.=	"</td>";
     $tab2.=	"<td valign='top'>";
     $tab2.=		"<input type='submit' name='save_depts' value='Save Depts'>";
     $tab2.=	"</td>";
     $tab2.="</tr>";
     $tab2.="</table><input type='hidden' name='max_depts' id='max_depts' value='".$cntr."'>";
     $tab2.="</form>";     
}

	$tab3.="<form name='items' action='item.php' method='post'>";
     $tab3.="<div style='font-weight:bold; color:purple;'>Items:</div>";
     $tab3.="<table cellpadding='0' cellspacing='0' width='100%' border='0'>";
     
     	$tab3.="<tr>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>Item</b>";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>Name</b>";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>Details</b>";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>Code</b>";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>Dept</b>";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>Status</b>";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>Cost</b>";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>Price</b>";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>Staff</b>";
     	$tab3.=	"</td>";
     	$tab3.="</tr>";
     	
     $cntr=0;
     $sql = "
		select * 	
		from cm_items		
		order by dept_id asc,item_name asc,item_id asc
	";	//where category_name = '".sql_friendly($category_name)."'
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{		
     	//mrr_active($field,$pre=0,$cd=0)
     	//mrr_numbers($field,$pre=0,$min=0,$max=0,$cd=0)
     	//mrr_sizes($field,$pre="",$cd=0)
     	//mrr_depts($field,$pre=0,$cd=0)
     	
     	$tab3.="<tr>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<b>".$row['item_id']." <input type='hidden' name='item_".$cntr."' id='item_".$cntr."' value='".$row['item_id']."'></b>";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<input type='text' name='item_name_".$cntr."' id='item_name_".$cntr."' value=\"".trim($row['item_name'])."\">";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<input type='text' name='item_details_".$cntr."' id='item_details_".$cntr."' value=\"".trim($row['item_details'])."\">";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"<input type='text' name='item_code_".$cntr."' id='item_code_".$cntr."' value=\"".trim($row['item_code'])."\">";
     	$tab3.=	"</td>";
     	    	
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"".mrr_depts("dept_id_".$cntr."",$row['dept_id'],1)."";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top'>";
     	$tab3.=		"".mrr_active("active_".$cntr."",$row['active'],1)."";
     	$tab3.=	"</td>";
     	    	
     	$tab3.=	"<td valign='top' nowrap>";
     	$tab3.=		"$ <input type='text' name='cost_".$cntr."' id='cost_".$cntr."' value=\"".$row['cost']."\">";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top' nowrap>";
     	$tab3.=		"$ <input type='text' name='price_".$cntr."' id='price_".$cntr."' value=\"".$row['price']."\">";
     	$tab3.=	"</td>";
     	$tab3.=	"<td valign='top' nowrap>";
     	$tab3.=		"$ <input type='text' name='sprice_".$cntr."' id='sprice_".$cntr."' value=\"".$row['sprice']."\">";
     	$tab3.=	"</td>";
     	$tab3.="</tr>";
     	$cntr++;
	}
     $tab3.="<tr>";
     $tab3.=	"<td valign='top'>";
     $tab3.=		"<input type='submit' name='add_item' value='New Item'>";
     $tab3.=	"</td>";
     $tab3.=	"<td valign='top' colspan='7'>";
     $tab3.=		"<b>&nbsp;</b>";
     $tab3.=	"</td>";
     $tab3.=	"<td valign='top'>";
     $tab3.=		"<input type='submit' name='save_items' value='Save Items'>";
     $tab3.=	"</td>";
     $tab3.="</tr>";
     $tab3.="</table><input type='hidden' name='max_items' id='max_items' value='".$cntr."'>";
     $tab3.="</form>";  


echo mrr_header("Items and Settings");
echo $tab1;
echo $tab2;
echo $tab3;
echo mrr_footer();
?>