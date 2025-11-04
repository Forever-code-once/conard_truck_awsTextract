<? include('application.php') ?>
<?
$admin_page = 1;

$ftp_view_mode=0;		$dir=getcwd()."/scanner_upload/";	

$usetitle = "View Scanner Upload Directory";
$use_title = "View Scanner Upload Directory";
?>
<? include('header.php') ?>
<div style='margin:10px;'>
	
<div class='standard18'><b><?=$usetitle ?></b></div>
<div style=color:black;margin:10px;'>
	Use this page to view all the files that were uploaded by the scanner, and are in the queue to process. Limited operations may be possible with these files from here.
	<br>You might also check that the filename isn't named with a bad set of code letters with hte lower charts or extra digits on the Truck, Load, or Trailer numbers.  <b>For Drivers and Users, only use their ID, not their name.</b>
	<br>If there are no files listed below, the queue is empty... and no files are waiting to be processed.
	<!--- <br>Path: <?=$dir ?>.  --->	
</div>
<div style=color:purple;margin:10px;'>
	<b>WARNINGS: 
		<ul>
		<li>- You can NOT have have two or more identical file names at the same time in the same directory.  Ex:  87ud.pdf and 87ud.pdf are identical, so the scanner adds a "-1" to the first duplicate, "-2" to the next one, etc.</li>
		<li>- This number (such as "-1", "-2", "-3") is automatically appended to any otherwise duplicated filenames before the file extension.  Ex: 87ud.pdf, 87ud-1.pdf, 87ud-2.pdf, ...  Unfortunately, none of these will process correctly.</li>
		<li>- Remove any files named in error, or that were duplicates.  (View file to confirm if identical document or not.)  Ex: If 87ud-1.pdf is the same exact file as 87ud.pdf, remove the one with the "-1".</li> 
		<li>- If the documents are different,but have the same name, let the first one (87ud.pdf) process first by <a href='scan_process.php' target='_blank'>running the scanning processor</a>.  
			Rename any files one at a time, removing the suffix number ("-1", "-2", etc.) from the name, and then <a href='scan_process.php' target='_blank'>run the scanning processor</a> again.  Rinse and Repeat until all the files have been processed</li>
		</ul>
	</b>
</div>
<br>Quick Links:  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href='mrr_ftp_viewer.php'>Refresh View</a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; | &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href='scan_process.php' target='_blank'>Run Scan Processor</a>
<br>
<form action='' method='post'>
<table class='tablesorter' style='width:1000px;'>
<thead>
	<tr>
		<th>File</th>
		<th>Filename</th>
		<th>Type</th>
		<th>Filesize</th>
		<th>Uploaded</th>		
		<th>Rename</th>
		<th>Remove</th>
	</tr>	
</thead>
<tbody>
	<?
	$lister=scandir($dir);
	$files=0;
	$last_file="";
	
	
	for($i=0; $i < count($lister); $i++)
	{
		$filename=trim($lister[$i]);
		
		$typer="N/A";
		$sizer="";
		$dater="";
		if($filename == ".")
		{
			$typer="Root";
		}
		elseif($filename == "..")
		{
			$typer="Parent";
		}
		elseif(is_dir($dir.$filename))
		{
			$typer="Directory";
		}
		else
		{
			$typer=filetype($dir.$filename);	
			$sizer=filesize($dir.$filename);
			$dater=date("m/d/Y H:i:s.", filemtime($dir.$filename));
		}
		
		
		$filename2=$filename;	
     	$filename2=str_replace("-1.",".",$filename2);     	$filename2=str_replace("-2.",".",$filename2);    			$filename2=str_replace("-3.",".",$filename2);     		$filename2=str_replace("-4.",".",$filename2);
     	$filename2=str_replace("-5.",".",$filename2);     	$filename2=str_replace("-6.",".",$filename2);    			$filename2=str_replace("-7.",".",$filename2);     		$filename2=str_replace("-8.",".",$filename2);
     	$filename2=str_replace("-9.",".",$filename2);     	$filename2=str_replace("-10.",".",$filename2);   			$filename2=str_replace("-11.",".",$filename2);    		$filename2=str_replace("-12.",".",$filename2);
     	$filename2=str_replace("-13.",".",$filename2);		$filename2=str_replace("-14.",".",$filename2);   			$filename2=str_replace("-15.",".",$filename2);			$filename2=str_replace("-16.",".",$filename2);
     	$filename2=str_replace("-17.",".",$filename2);		$filename2=str_replace("-18.",".",$filename2);   			$filename2=str_replace("-19.",".",$filename2);			$filename2=str_replace("-20.",".",$filename2);
		
				
		if($typer=="N/A" || $typer=="file")
		{			
     		if(isset($_GET['did']) && (int) $_GET['did']==$files)
     		{
     			//remove the file...	
     			echo "
          			<tr>
          				<td valign='top'>".($files+1)."</td>
          				<td valign='top'>".$filename."</td>
          				<td valign='top' colspan='5'>Removing this file...</td>
          			</tr>
          		";
          		
          		unlink($dir.$filename);
     		}
     		elseif(isset($_GET['rid']) && (int) $_GET['rid']==$files)
     		{
     			//rename the file...
     			echo "
          			<tr>
          				<td valign='top'>".($files+1)."</td>
          				<td valign='top'>".$filename."</td>
          				<td valign='top' colspan='5'>Renaming this file \"".$filename2."\"...</td>
          			</tr>
          		";
          		
          		rename($dir.$filename, $dir.$filename2);
     		}
     		else
     		{
          		$renamer_function="<a href=\"javascript:confirm_rename(".$files.",'".$filename."','".$filename2."');\"><i>".$filename2."</i>";
          		if($last_file==$filename2)		$renamer_function="Duplicate?";
          		if($filename==$filename2)		$renamer_function="N/A";
          		
          		
          		echo "
          			<tr>
          				<td valign='top'>".($files+1)."</td>
          				<td valign='top'>".$filename."</td>
          				<td valign='top'>".$typer."</td>
          				<td valign='top'>".$sizer."</td>
          				<td valign='top'>".$dater."</td>
          				
          				<td valign='top'>".$renamer_function."</td>
          				<td valign='top'>
          					<input type='hidden' name='file_namer_".$files."' id='file_namer_".$files."' value=\"".$filename."\">
          					<a href=\"javascript:confirm_delete(".$files.",'".$filename."');\" class='mrr_delete_access'><img src=\"images/delete_sm.gif\" border=\"0\"></a>
          				</td>
          			</tr>
          		";
     		}	
     		$files++;
     		$last_file=$filename2;
		}
	}
	?>
</tbody>
</table>
	<input type='hidden' name='tot_files' id='tot_files' value='<?=$files ?>'>
</form>

<table cellpadding='0' cellspacing='0' border='0' width='1000'>
<tr>	
	<td valign='top' align='right' width='490'>		
		<table cellpadding='0' cellspacing='0' border='0' class='admin_menu1' width='300'>	
			<tr><td valign='top' colspan='2' align='center'><b>Scan File Code First Letter</b></td></tr>
			<tr><td valign='top'><b>Letter</b></td><td valign='top'><b>Section</b></td></tr>
			<tr><td valign='top'>V</td><td valign='top'>Driver</td></tr>
			<tr><td valign='top'>R</td><td valign='top'>Trailer</td></tr>
			<tr><td valign='top'>T</td><td valign='top'>Truck</td></tr>
			<tr><td valign='top'>C</td><td valign='top'>Customer</td></tr>
			<tr><td valign='top'>D</td><td valign='top'>Dispatches</td></tr>
			<tr><td valign='top'>L</td><td valign='top'>Loads</td></tr>
			<tr><td valign='top'>U</td><td valign='top'>User</td></tr>
		</table>
		<br>
		Ex: VD = Driver, Driver File... but DV = Dispatch Violation
	</td>
	<td valign='top' align='right' width='20'>	
		&nbsp;
	</td>	
	<td valign='top' align='left'>		
		<table cellpadding='0' cellspacing='0' border='0' class='admin_menu2' width='300'>	
			<tr><td valign='top' colspan='2' align='center'><b>Scan File Code Second Letter</b></td></tr>
			<tr><td valign='top'><b>Letter</b></td><td valign='top'><b>Type/Category</b></td></tr>
			<tr><td valign='top'>I</td><td valign='top'>Invoice</td></tr>
			<tr><td valign='top'>P</td><td valign='top'>POD</td></tr>
			<tr><td valign='top'>D</td><td valign='top'>Driver File</td></tr>
			<tr><td valign='top'>V</td><td valign='top'>Violation</td></tr>
			<tr><td valign='top'>E</td><td valign='top'>Expense</td></tr>
			<tr><td valign='top'>O</td><td valign='top'>Other</td></tr>
			<tr><td valign='top'>M</td><td valign='top'>Document</td></tr>
			<tr><td valign='top'>R</td><td valign='top'>Rate Confirmation</td></tr>
		</table>
	</td>
</tr>
</table>

</div>
<script type='text/javascript'>
	$(".tablesorter").tablesorter();
	
	function confirm_delete(id,filer) 
	{
		var cntr=parseInt(id);
		cntr++;
		if(confirm("Are you sure you want to DELETE file "+cntr+", \""+filer+"\"?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}
	
	function confirm_rename(id,filer,filer2) 
	{
		var cntr=parseInt(id);
		cntr++;
		if(confirm("Are you sure you want to RENAME file "+cntr+", from \""+filer+"\" to \""+filer2+"\"?")) {
			window.location = '<?=$SCRIPT_NAME?>?rid=' + id;
		}
	}
</script>
<? include('footer.php') ?>