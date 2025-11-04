<? include('application.php') ?>
<?

function getCSVValues($string,$separator=",") {
    $string = str_replace('""', "'", $string);
    // split the string at double quotes "
    $bits = explode('"',$string);
    $elements = array();
    for ( $i=0; $i < count($bits) ; $i++ ) {
        /*
        odd numbered elements would have been
        enclosed by double quotes
        even numbered elements would not have been
        */
        if (($i%2) == 1) {
            /* if the element number is odd add the
            whole string  to the output array */
            $elements[] = $bits[$i];
        } else 
        {
            /* otherwise split the unquoted stuff at commas
            and add the elements to the array */
            $rest = $bits[$i];
            $rest = preg_replace("/^".$separator."/","",$rest);
            $rest = preg_replace("/".$separator."$/","",$rest);
            $elements = array_merge($elements,explode($separator,$rest));
        }
    }
    return $elements;
}

$ftp_server = $defaultsarray['comdata_address'];
$ftp_username = $defaultsarray['comdata_username'];
$ftp_password = $defaultsarray['comdata_password'];
$use_path = $defaultsarray['base_path'].'/comdata/';
$use_path_backup = $use_path . "/backup/";


	
	if(!file_exists($use_path)) mkdir($use_path);
	if(!file_exists($use_path_backup)) mkdir($use_path_backup);

	$msg = "";
	
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
		
			ftp_chdir($conn_id, 'outgoing');
		
			echo "Retrieving directory contents<br>";
			ob_flush();
		
			//$rslt = ftp_put($conn_id, $out_file, $in_path."/out/".$out_file, FTP_ASCII);
			$file_array = ftp_nlist($conn_id, ".");
			
			echo count($file_array)." files need to be downloaded<br>";
			ob_flush();
			
			foreach($file_array as $file) {
				
				echo $file;
				
				// only download the file if we haven't downloaded it before
				$sql = "
					select id
					
					from ".mrr_find_log_database_name()."log_api_import
					where api_vendor_name = 'comdata'
						and filename = '".sql_friendly($file)."'
				";
				$data_check = simple_query($sql);
				
				if(mysqli_num_rows($data_check)) {
					echo " (already downloaded)";
				} else {
					$local_file = $use_path."/$file";
					$rslt = ftp_get($conn_id, $local_file, $file, FTP_ASCII);
					if($rslt) {
						// got the file, now delete the one off the server
						$rslt = ftp_delete($conn_id, $file);
					}
					
					echo " (success) ";
					$sql = "
						insert into ".mrr_find_log_database_name()."log_api_import
							(linedate_added,
							filename,
							api_vendor_name)
							
						values (now(),
							'".sql_friendly($file)."',
							'comdata')
					";
					simple_query($sql);
					
					ob_flush();				
				}
				
				echo "<br>";
				

			}
			


		}
	}
	
	echo "<br>$msg<br>";


	// process the files
	
	$fields_array = array('Unit', 
				'Tran_Date', 
				'Tran_Time', 
				'Card_Number', 
				'Driver_Name', 
				'Driver_Number', 
				'Hubometer', 
				'Transaction_Total', 
				'Fleet_Fees', 
				'Discounts', 
				'State', 
				'City', 
				'Merchant_Name',
				'DSL_Gallons',
				'PPG',
				'[Total]');
	
	$api = new sicap_api_connector();
	$api_item_id = new sicap_api_connector();	
	
	$vendor_id = $api->getVendorIDByName("Comdata (Fuel)");
	
	$d = dir($use_path);
	echo "<table>";
	while (false !== ($entry = $d->read())) {
		
		$api->clearParams();
		
		
		
		if(is_file($use_path.$entry)) {
	   		
	   		
	   		
	   		$file_part = explode(".", $entry);
	   		$file_type = $file_part[2];
	   		$file_invoice_date = $file_part[3];
	   		$file_invoice_date = substr($file_invoice_date, 0,2) . "/" . substr($file_invoice_date, 2,2) . "/" . substr($file_invoice_date, 4,4);
	   		$file_invoice_date = date("m/d/Y", strtotime("1 day", strtotime($file_invoice_date)));
	   		
	   		//d($entry . " | $file_invoice_date");
	   		
	   		//echo $entry." | ".$file_invoice_date."<br>";
	   		//die;
	   		
	   		if($file_type == 'FM00001') {
	   			// FM00001 = fuel file
	   		
		   		$contents = file_get_contents($use_path."/$entry");
		   		$contents_array = explode(chr(10), $contents);
		   		
		   		$counter = 0;
		   		for($i=1;$i < count($contents_array) - 1;$i++) {
		   			$fuel_entry['truck_number'] = trim(substr($contents_array[$i], 25,6));
		   			$fuel_entry['driver_name'] = trim(substr($contents_array[$i], 139,12));
		   			$fuel_entry['trans_amount'] = (float) substr($contents_array[$i], 77,4) . "." . substr($contents_array[$i], 81,2);
		   			$fuel_entry['trans_number'] = trim(substr($contents_array[$i], 20,5));
		   			$fuel_entry['city'] = trim(substr($contents_array[$i], 51,12));
		   			$fuel_entry['state'] = trim(substr($contents_array[$i], 63,2));
		   			$fuel_entry['stop_name'] = trim(substr($contents_array[$i], 36,15));
		   			$fuel_entry['fuel_fee'] = trim(substr($contents_array[$i], 83,4));
		   			$fuel_entry['gallons'] = (float) substr($contents_array[$i], 89,3) . "." . substr($contents_array[$i], 92,2);
		   			
		   			
		   			$line_total = $fuel_entry['trans_amount'];
		   			
		   			//echo "Truck number: $truck_number | $driver_name | $trans_amount | ($trans_number)<br>";
		   			//var_dump($fuel_entry);
		   			//echo "<br>";
					
					if($line_total > 0 && $fuel_entry['truck_number'] > 0 && $fuel_entry['driver_name'] != '000000000000') {
						echo "<tr>";
	
			   			$counter++;
						
						$api->addParam("Item_".$counter."_ChartNumber", "58800-".$fuel_entry['truck_number']);
						$api->addParam("Item_".$counter."_Amount", $fuel_entry['trans_amount']);
						$api->addParam("Item_".$counter."_Desc", $fuel_entry['truck_number'] . " - " .  $fuel_entry['driver_name'] . " - $" .  money_format('', $fuel_entry['trans_amount']));
		
						
			   			echo "</tr>";
			   		}
			   		
		   			
		   		}
		   		
		   		
		   		
		   		$api->addParam("VendorID", $vendor_id);
		   		$api->addParam("ReferenceNumber", date("m/d/Y", strtotime($file_invoice_date)));
		   		$api->addParam("BillDate", date("m/d/Y", strtotime($file_invoice_date)));
		   		$api->addParam("BillDateDue", date("m/d/Y", strtotime("+7 day", strtotime($file_invoice_date))));
				$api->addParam("ItemCount", $counter);
				$api->command = "create_bill";
				//$api->show_output = true;
				//$api->debug_post = true;
				
				$rslt = $api->execute();
		   		
		   		if($rslt->rslt == 1) {
		   			// success, now move this file to the backup folder
		   			rename($use_path."/$entry", $use_path_backup."/$entry");
		   		}
		   	}

	   	}
	}
	echo "</table>";
?>
<br><br>done.