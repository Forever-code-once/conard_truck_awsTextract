<?php
//NOTE:This file is not used...go to /includes/uploadify/ for the current file.

include_once('application.php');

//move_uploaded_file($_FILES['Filedata']['tmp_name'], getcwd().'\\documents\\'.$_FILES['Filedata']['name']);


if (!empty($_FILES)) {
	
	$new_folder = getcwd()."/".$defaultsarray['document_upload_dir']."/";
	//$new_folder = getcwd()."\\documents\\";	

	if(!file_exists($new_folder)) mkdir($new_folder);
	
	
	
	$file_ext = get_file_ext($_FILES['Filedata']['name']);
	//$file_base = str_replace(".$file_ext","",$_FILES['Filedata']['name']);
	
	$new_filename = get_unique_filename($new_folder,$_FILES['Filedata']['name']);
	
	
	if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $new_folder.$new_filename)) {
		$rslt = 1;
	} else {
		$rslt = 0;
	}
	
	
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
			cat_id)
			
		values ('".sql_friendly($_POST['user_id'])."',
			now(),
			'".sql_friendly($new_filename)."',
			'".sql_friendly($_FILES['Filedata']['size'])."',
			'".sql_friendly($file_ext)."',
			'".sql_friendly($_POST['section_id'])."',
			'".sql_friendly($_POST['xref_id'])."',
			0,
			'".sql_friendly($new_filename)."',
			$rslt,
			0)
	";
	simple_query($sql);
	
}
?>
1
