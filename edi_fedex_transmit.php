<? include('application.php') ?>
<?

	//d(get_include_path() . PATH_SEPARATOR . 'phpseclib');

	set_include_path(getcwd().'/includes/phpsec1_0_10/');
	include('includes/phpsec1_0_10/Net/SFTP.php');

	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	$fedex_ftp_server = $defaultsarray['fedex_sftp_address'];
	$fedex_ftp_username = $defaultsarray['fedex_sftp_username'];
	$fedex_ftp_password = $defaultsarray['fedex_sftp_password'];
	//$use_path = $defaultsarray['base_path'].'/fedex/';
	$use_path = $defaultsarray['edi_fedex_path'];
	$use_path_backup = $use_path . "/backup/";
	
	if(!file_exists($use_path)) mkdir($use_path);
	if(!file_exists($use_path_backup)) mkdir($use_path_backup);

	$msg = "";
	$sftp = new Net_SFTP($fedex_ftp_server);
	
	if (!$sftp->login($fedex_ftp_username, $fedex_ftp_password)) {
	    exit('Login Failed');
	} else {
		
		//$rslt = ftp_put($conn_id, $out_file, $in_path."/out/".$out_file, FTP_ASCII);
		download_files("204");
		download_files("FA");		
		echo "Connected<br>";
	}
	

	
	function download_files($ftp_dir) {
		global $sftp;
		global $use_path;
		global $defaultsarray;
		
		//die($ftp_dir);
		
		
		
		echo "<hr>";
		//d($defaultsarray['edi_scac_code']);
		$remote_path = "/".$defaultsarray['edi_scac_code']."/$ftp_dir";
		//d($remote_path);

		echo "Checking for files in: $remote_path<br>";

		$file_array = $sftp->rawlist($remote_path);
		
		var_dump($file_array);
		
		ob_flush();
		
		foreach($file_array as $file) {
			
			print_r($file);
			
			if($file['type'] != NET_SFTP_TYPE_REGULAR) {
				echo "skipping for some reason ($file | $ftp_dir)<br>";
			} else {
				echo "Attempting to download <span style='color:green'>$file[filename]</span>";
				$local_file = $use_path."/".createuuid()."-$file[filename]";
				//$rslt = ftp_get($conn_id, $local_file, $file, FTP_BINARY);
				echo "
					<br><br>Local file: $local_file 
					<br>remote: $remote_path/$file[filename]
					<br>
				";
				//die('aborting for now');
				$sftp->get($remote_path."/".$file['filename'], $local_file);
				
				print_r($rslt);
				
				if($rslt) {
					
					echo " | Download Successful ";
					// got the file, now delete the one off the server
					// fedex automatically deletes the file after a successful download
					/*
					if(!ftp_delete($conn_id, $file)) {
						echo " | <span style='color:red'>Could not delete</span>";
					}
					*/
				} else {
					echo " | <span style='color:red'>Could not download</span>";
				}
				echo "<br>";
				ob_flush();
			}
		}
	}
	
?>