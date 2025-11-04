<? include('application.php') ?>
<?
	$report="";
		
	$destx="License Key";
	$dest="C:\\ALK Technologies\\PMW230\\App\\x32api.dll";				//Program Files (x86)\\
	$dest2="C:\\ALK Technologies\\PMW230\\App\\x32api.dll_bk_".date("YmdHis")."";	//Program Files (x86)\\
	$source="C:\\web\\trucking.conardlogistics.com\\mrr\\x32api.dll";	
	$source="C:\\web\\trucking.conardlogistics.com\\mrr_test\\x32api.dll";	
	
	if(!file_exists($dest)) 
	{	//not found, so make it.
		$report.="Repair needed. the '<b>".$destx."</b>' does not exist.<br>";
		if (!copy($source, $dest)) 
		{
			$report.="Failed to copy '<b>".$destx."</b>'<br>";
		}
		else
		{
			$report.="Repair complete. File copied.<br>";	
		}
	}
	else
	{	//found so it is corrupted...?  Rename this one and copy new one.
		$report.="The '<b>".$destx."</b>' exists.  Renaming and replacing it...<br>";
		if(!rename($dest, $dest2))
		{
			$report.="Failed to replace '<b>".$destx."</b>'.<br>";
			
			unlink($dest);
			if (!copy($source, $dest)) 
			{
				$report.="Failed to copy '<b>".$destx."</b>'<br>";
			}
			else
			{
				$report.="Repair complete. File restored.<br>";	
			}
		}
		else
		{
			if (!copy($source, $dest)) 
			{
				$report.="Failed to copy '<b>".$destx."</b>'<br>";
			}
			else
			{
				$report.="Repair complete. File restored.<br>";	
			}
		}				
	}
	echo "
		<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01//EN'   'http://www.w3.org/TR/html4/strict.dtd'>
		<html>
		<head>
			<title>PC Miler Repair Utility</title>		
		</head>	
		<body>	
			<h1>PC Miler Repair Utility</h1>	
			<br>
			".$report."
		</body>
		</html>
	";	
?>