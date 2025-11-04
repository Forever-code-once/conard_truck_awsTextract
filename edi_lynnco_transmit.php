<? include('application.php') ?>
<? include_once('functions_lynnco.php'); ?>
<?
	//d(get_include_path() . PATH_SEPARATOR . 'phpseclib');
	
	set_include_path(getcwd().'/includes/phpsec1_0_10/');
	include('includes/phpsec1_0_10/Net/SFTP.php');

	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	$lynnco_ftp_port = $defaultsarray['lynnco_edi_port'];
     $lynnco_ftp_server = $defaultsarray['lynnco_edi_url'];
	$lynnco_ftp_username = $defaultsarray['lynnco_edi_user'];
	$lynnco_ftp_password = $defaultsarray['lynnco_edi_pass'];
	//$use_path = $defaultsarray['base_path'].'/lynnco/';
	$use_path = $defaultsarray['edi_lynnco_path'];
	$use_path_backup = $use_path . "/backup/";
	
	if(!file_exists($use_path)) mkdir($use_path);
	if(!file_exists($use_path_backup)) mkdir($use_path_backup);
	
	$in_path = $defaultsarray['edi_lynnco_path'];
	
	//if(!isset($_SERVER['HTTP_CF_CONNECTING_IP']))		$_SERVER['HTTP_CF_CONNECTING_IP']=$_SERVER['REMOTE_ADDR'];
	echo "<br>Server: [".$_SERVER['SERVER_NAME']."] --  Me: ".$_SERVER['REMOTE_ADDR'].".<br><hr><br>";		// ".$_SERVER['SERVER_ADDR']." CF: ".$_SERVER['HTTP_CF_CONNECTING_IP']."
	
	$msg = "";
	$sftp_mode=1;
	
	if($sftp_mode > 0)
	{
     	$sftp = new Net_SFTP($lynnco_ftp_server, $lynnco_ftp_port);		//,21     second arg is a port number. 22 is SFTP
     	
     	if (!$sftp->login($lynnco_ftp_username, $lynnco_ftp_password)) 
     	{
     	    exit('SFTP Login Failed');
     	} 
     	else 
     	{    
     		download_files("Outbound");	
     		echo "SFTP Connected<br>";
     		
     		// now test for and check that every lynnco_edi file has been sent for tendering (990 file sent)
     		echo "<br><br>SCANNING FOR FILES NOT SENT<br><br>";
     		$sql = "
				select id,lynnco_edi_input_file,lynnco_edi_output_file,load_number,linedate_edi_response_sent
			
				from load_handler
				where deleted=0
					and customer_id = '2031'
					and lynnco_edi > 0
					and linedate_edi_response_sent = '0000-00-00 00:00:00'
				order by id desc
			";	// 
			$data = simple_query($sql);
			while($row = mysqli_fetch_array($data))
			{
				echo "<br><br>...Resending Load ".$row['id']." file ".$row['lynnco_edi_input_file']."".$row['lynnco_edi_output_file']." for Load Number ".$row['load_number'].". Was sent ".$row['linedate_edi_response_sent'].".";
				
				if(send_lynnco_edi('/Inbound/', $in_path."/out/", $row['lynnco_edi_output_file'], $row['id'])) 
          		{
          			// upload was successful, remove that file from the server
          			//echo "<font color='green'>Successful Upload of 990 file.</font>";
          			$rslt = 1;
          			$msg = "LynnCo 990 EDI Sent Successfully";
          			
          			// log the upload
          			$sql = "
          				update load_handler set
          					linedate_edi_response_sent = now()
          				where id = '".sql_friendly($load_handler_id)."'
          			";
          			simple_query($sql);						
          		}
			}
			echo "<br><br>DONE.<br><br>";
     	}	
	}
			
	function download_files($ftp_dir) 
	{
		global $sftp;
		global $use_path;
		global $defaultsarray;
		
		//die($ftp_dir);
		
		echo "<hr>";
		//d($defaultsarray['edi_scac_code']);
		
		//$remote_path = "/".$defaultsarray['edi_scac_code']."/$ftp_dir";
		$remote_path = "/$ftp_dir";
		
		//d($remote_path);

		echo "<br>Checking for files in: $remote_path<br>";

		$file_array = $sftp->rawlist($remote_path);
		
		//var_dump($file_array);
		
		ob_flush();
		
		foreach($file_array as $file) {
			
			//print_r($file);
			
			if($file['type'] != NET_SFTP_TYPE_REGULAR) 
			{
				echo "<br>skipping for some reason (".$file['filename']." | ".$file['type']." | $ftp_dir)<br>";
			} 
			else 
			{
				echo "<br>Attempting to download <span style='color:green'>$file[filename]</span>";
				$local_file = $use_path."/".createuuid()."-$file[filename]";
				//$rslt = ftp_get($conn_id, $local_file, $file, FTP_BINARY);
				echo "
					<br><br>Local file: $local_file 
					<br>remote: $remote_path/$file[filename]
					<br>
				";
				//die('aborting for now');
				$sftp->get($remote_path."/".$file['filename'], $local_file);
				
				//print_r($rslt);
				
				if($rslt) 
				{					
					echo " | Download Successful ";
					// got the file, now delete the one off the server
					// fedex automatically deletes the file after a successful download
					/*
					if(!ftp_delete($conn_id, $file)) {
						echo " | <span style='color:red'>Could not delete</span>";
					}
					*/					
				} 
				else 
				{
					echo " | <span style='color:red'>Could not download</span>";
								
					if(substr_count($file['filename'],"997-MGLYNNCO") > 0)
					{
						//unlink($local_file);	
						//echo "... Removed 997 file.";
					}
					
					$sftp->delete($remote_path."/".$file['filename']);
				}
				echo "<br>";
				ob_flush();
			}
		}
	}	
?>