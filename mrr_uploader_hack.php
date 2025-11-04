<? include("application.php") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"   "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Image Uploader</title>				
		

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		
		
     	<script src="includes/jquery-ui-1.8.21.custom.min.js" language="JavaScript" type="text/javascript"></script>
		<script src="includes/jquery.tools.min.js" language="JavaScript" type="text/javascript"></script>
		
		<script src="includes/jquery-impromptu.3.1.js" language='JavaScript' type='text/javascript'></script>
		<script src="includes/jquery.notice.js" type="text/javascript"></script>
		
		<script src="includes/jquery.qtip-1.0.0-rc3.min.js" type="text/javascript"></script>
		
		<!-- uploader code -->  
		<link href="includes/mini_upload/assets/css/style.css" rel="stylesheet" />	
		<script src="includes/mini_upload/assets/js/jquery.knob.js"></script>
     	<script src="includes/mini_upload/assets/js/jquery.ui.widget.js"></script>
     	<script src="includes/mini_upload/assets/js/jquery.iframe-transport.js"></script>
     	<script src="includes/mini_upload/assets/js/jquery.fileupload.js"></script>
     	<script src="includes/mini_upload/assets/js/script.js"></script>			
     	
     	<link rel="stylesheet" href="includes/jquery.notice.css" type="text/css" media="all">
     	<link rel="stylesheet" href="includes/jquery.tools.css" type="text/css" />
     	
		
     	<link rel="stylesheet" href="style.css" type="text/css" media="all">
		<link rel="stylesheet" href="images/2012/style.css" type="text/css">
    		<link rel="stylesheet" href="images/2012/css3.css" type="text/css">			
	</head>	
	<body style='background-color:#ffffff;'>
		<div id='upload_container' style='width:480px; padding-left:15px;'>
			<div class='inside_container'>          				
				<?
				echo "<div class='mrr_browse_box'>";
     			
     			$temp_id=(int) $_GET['id'];
     			
     			if(isset($_GET['mrr_id']))		$temp_id=(int) $_GET['mrr_id'];
     			if(isset($_GET['eid']))			$temp_id=(int) $_GET['eid'];
     			     			
     			$upsection = new upload_section();
     			$upsection->section_id = $_GET['section_id'];
     			$upsection->xref_id = $temp_id;
     			    			
     			if($temp_id==0 && $_GET['section_id']==11)
     			{
     				$upsection->param("callback_function", "parent.display_files(".$_GET['section_id'].", parent.acc_id);"); 		// optional -- this calls a javascript function when the upload is done
     			}
     			else
     			{     			
     				$upsection->param("callback_function", "parent.display_files(".$_GET['section_id'].", ".$temp_id.");"); 		// optional -- this calls a javascript function when the upload is done
     			}
     			$upsection->show();
				echo "</div>";				
				?>   			
			</div>
		</div>
		<script type='text/javascript'>
			
		</script>
	</body>
</html>	