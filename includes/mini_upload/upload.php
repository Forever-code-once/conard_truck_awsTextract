<?php
include_once('../../application.php');

function return_result($rslt) 
{
	echo json_encode($rslt);
	exit;
}
function mrr_pull_image_created_date($imagePath)
{
	$camDate="";
	if(trim($imagePath)!="" && file_exists($imagePath))
	{
		$exif_ifd0 = read_exif_data($imagePath ,'IFD0' ,0);
		$camDate = $exif_ifd0['DateTime'];	
	}
	return $camDate;
}

$upcounter = $_GET['uuid'];

$new_folder = $defaultsarray['base_path']."files/";
$def_access = 50;

// A list of permitted file extensions
$allowed = array('png','jpg','jpeg','gif','zip','xls','xlsx','pdf','doc','docx','xml','mp3','mp4','wmv','wmp','avi','mov','wav','m4a');

$debug_info="";

if(isset($_FILES['upl_'.$upcounter]) && $_FILES['upl_'.$upcounter]['error'] == 0)
{
	$extension = pathinfo($_FILES['upl_'.$upcounter]['name'], PATHINFO_EXTENSION);
	//$file_ext = get_file_ext($_FILES['Filedata']['name']);
	$file_ext=$extension;
		
	if(!in_array(strtolower($extension), $allowed))
	{
		$rslt['status_code'] = 0;
		$rslt['msg'] = 'Invalid File Extension';
		return_result($rslt);
		die();
	}
	
	$debug_info="User ID=".$_POST['user_id'].", Section ID=".$_POST['section_id'].", ID=".$_POST['xref_id'].".";
	
	$finfo = pathinfo($_FILES['upl_'.$upcounter]['name']);
	$new_filename = str_replace("'","_",$finfo['filename']."-".date("YmdHis").".".$finfo['extension']);	
	
	//$move_destination="uploads/".$new_filename;
	$move_destination=getcwd()."/../../documents/".$new_filename;		//documents for private access...or masked temp filename
		
	$filesize = filesize($_FILES['upl_'.$upcounter]['tmp_name']);
	
	$file_contents = file_get_contents($_FILES['upl_'.$upcounter]['tmp_name']);
	if(move_uploaded_file($_FILES['upl_'.$upcounter]['tmp_name'], $move_destination))
	{	
		$sql = "
     		insert into attachments
     			(user_id,
     			linedate_added,
     			fname,
     			filesize,
     			file_ext,
     			section_id,
     			xref_id,
     			deleted,
     			public_name,
     			result,
     			debug_info,
     			cat_id)
     			
     		values ('".sql_friendly($_POST['user_id'])."',
     			now(),
     			'".sql_friendly($new_filename)."',
     			'".sql_friendly($filesize)."',
     			'".sql_friendly($file_ext)."',
     			'".sql_friendly($_POST['section_id'])."',
     			'".sql_friendly($_POST['xref_id'])."',
     			0,
     			'".sql_friendly($new_filename)."',
     			1,
     			'".sql_friendly($debug_info)."',
     			0)
     	";
     	simple_query($sql);		
		$iid=mysqli_insert_id($datasource);
				
		$rslt['status_code'] = 1;
		$rslt['msg'] = 'success!';
		$rslt['filename_new'] = $new_filename;
		$rslt['filename_original'] = $_FILES['upl_'.$upcounter]['name'];
		$rslt['extra_params'] = $_SESSION['upload_params'][$upcounter]['extra_params'];
		return_result($rslt);
	} 
	else 
	{
		$sql = "
     		insert into attachments
     			(user_id,
     			linedate_added,
     			fname,
     			filesize,
     			file_ext,
     			section_id,
     			xref_id,
     			deleted,
     			public_name,
     			result,
     			debug_info,
     			cat_id)
     			
     		values ('".sql_friendly($_POST['user_id'])."',
     			now(),
     			'".sql_friendly($new_filename)."',
     			'".sql_friendly($filesize)."',
     			'".sql_friendly($file_ext)."',
     			'".sql_friendly($_POST['section_id'])."',
     			'".sql_friendly($_POST['xref_id'])."',
     			0,
     			'".sql_friendly($new_filename)."',
     			0,
     			'".sql_friendly($debug_info)."',
     			0)
     	";
     	simple_query($sql);
		$iid=mysqli_insert_id($datasource);
		
		$rslt['status_code'] = 0;
		$rslt['msg'] = 'Upload failed';
		return_result($rslt);
	}	
}

$rslt['status_code'] = 0;
$rslt['msg'] = 'No files found.';
return_result($rslt);