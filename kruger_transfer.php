<? include('application.php') ?>
<?

	$ftp_server = "intranet.conardlogistics.com";
	$ftp_username = "kruger";
	$ftp_password = "8s7h8s8dz";
	$use_path = getcwd().'/kruger/out/';
	
	
	$file_array = scandir($use_path);
	$trans_array = array();
	foreach($file_array as $file) {
		if(stripos($file, ".pdf") !== false) {
			echo $file."<br>";
			$trans_array[] = $file;
		}
	}
	if(count($trans_array)) {
		echo count($trans_array) . " file(s) need to be uploaded";
		
		
		$conn_id = ftp_connect($ftp_server);      // set up basic connection
		if(!$conn_id) {
			$rslt = 0;
			$msg = "Error: couldn't connect to ".$ftp_server;
		} else {
			echo "Connected to: $ftp_server<br>";
			ob_flush();
		
			$login_result = ftp_login($conn_id, $ftp_username, $ftp_password);   // login with username and password, or give invalid user message
			if(!$login_result) {
				$rslt = 0;
				$msg = "Error: You do not have access to this FTP server";
			} else {
				echo "<br>";
				foreach($trans_array as $upload_file) {
					echo "Uploading: $upload_file<br>";
					$rslt = ftp_put($conn_id, "kruger_".$upload_file, $use_path."/".$upload_file, FTP_BINARY);
					if($rslt) {
						// upload was a success, delete the original from our server
						unlink($use_path."/".$upload_file);
						ob_flush();
					} else {
						echo "Error uploading<br>";
					}
				}
			
	
			}
		}
		
	}
	
	die('<br>done...');
	

	
?>	