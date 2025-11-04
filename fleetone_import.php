<? 
//include('quick_q_import.php');
//die();
?>
<? include_once('application.php') ?>
<?
/*  */
function getCSVValues($string,$separator=",") {
    $string = str_replace('""', "'", $string);
    // split the string at double quotes "
    $bits = explode('"',$string);
    $elements = array();
    for ( $i=0; $i < count($bits) ; $i++ ) {
        //odd numbered elements would have been enclosed by double quotes even numbered elements would not have been
        
        if (($i%2) == 1) {
           // if the element number is odd add the            whole string  to the output array 
            $elements[] = $bits[$i];
        } else 
        {
            // otherwise split the unquoted stuff at commas        and add the elements to the array 
            $rest = $bits[$i];
            $rest = preg_replace("/^".$separator."/","",$rest);
            $rest = preg_replace("/".$separator."$/","",$rest);
            $elements = array_merge($elements,explode($separator,$rest));
        }
    }
    return $elements;
}

	$fleetone_ftp_server = $defaultsarray['fleetone_address'];
	$fleetone_ftp_username = $defaultsarray['fleetone_username'];
	$fleetone_ftp_password = $defaultsarray['fleetone_password'];
	$use_path = $defaultsarray['base_path'].'/fleetone/';
	$use_path_backup = $use_path . "/backup/";
	
	if(!file_exists($use_path)) mkdir($use_path);
	if(!file_exists($use_path_backup)) mkdir($use_path_backup);

	$msg = "";
	
	$conn_id = ftp_connect($fleetone_ftp_server);      // set up basic connection
	
	if(!$conn_id) {
		$rslt = 0;
		$msg = "Error: couldn't connect to ".$fleetone_ftp_server;
	} else {
		echo "Connected to: $fleetone_ftp_server<br>";
		ob_flush();
	
		
	
		$login_result = ftp_login($conn_id, $fleetone_ftp_username, $fleetone_ftp_password);   // login with username and password, or give invalid user message
		if(!$login_result) {
			$rslt = 0;
			$msg = "Error: You do not have access to this FTP server";
		} else {
		
			ftp_pasv($conn_id,true);
		
			echo "Retrieving directory contents<br>";
			ob_flush();
		
			//$rslt = ftp_put($conn_id, $out_file, $in_path."/out/".$out_file, FTP_ASCII);
			$file_array = ftp_nlist($conn_id, ".");
			
			var_dump($file_array);
			
			
			echo count($file_array)." files need to be downloaded<br>";
			ob_flush();
			
			foreach($file_array as $file) {
				echo $file."<br>";
				$local_file = $use_path."/$file";
				$rslt = ftp_get($conn_id, $local_file, $file, FTP_ASCII);
				if($rslt) {
					// got the file, now delete the one off the server
					$rslt = ftp_delete($conn_id, $file);
				} else {
					$last_error = error_get_last();
					echo "Error: ". $last_error . "<br>"; 
				}
				ob_flush();
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
	
	$vendor_id = $api->getVendorIDByName("Fleet One");
	
	$d = dir($use_path);
	echo "<table>";
	while (false !== ($entry = $d->read())) {
		
		$api->clearParams();
		
		if(stripos($entry, '.csv') !== false) {
	   		
	   		
	   		$file_part = explode("_", $entry);
	   		$file_invoice_date = $file_part[3];
	   		
	   		echo $entry." | ".$file_invoice_date."<br>";
	   		
	   		$contents = file_get_contents($use_path."/$entry");
	   		$contents_array = explode(chr(10), $contents);
	   		$header_array = explode(",", $contents_array[0]);
	   		echo "<tr>";
	   			for($m=0;$m < count($fields_array);$m++) {
	   				$header_entry = $fields_array[$m];
	   				echo "<td>$header_entry</td>";
	   			}
	   		echo "</tr>";
	   		
	   		$counter = 0;
	   		for($i=1;$i < count($contents_array);$i++) {
	   			//$line_array = explode(",", $contents_array[$i]);
	   			$line_array = getCSVValues($contents_array[$i]); // new funtion handles quoted (") entries properly
	   			
	   			$line_total = $line_array[array_search('Transaction_Total', $header_array)] + $line_array[array_search('Fleet_Fees', $header_array)];

				if($line_total > 0) {
					echo "<tr>";
					
		   			for($m=0;$m < count($fields_array);$m++) {
		   				
		   				$header_entry = $fields_array[$m];
		   				if(strpos($fields_array[$m], "[") === false) {
		   					echo "<td>".$line_array[array_search($fields_array[$m], $header_array)]."</td>";
		   				} else if($fields_array[$m] == '[Total]') {
		   					echo "<td>$line_total</td>";
		   				}
		   				
		   			}
		   			
		   			$counter++;
		   			
					//$chart_id = $api_item_id->getChartIDByName($line_array[array_search('Unit', $header_array)]);
					$line_amount = $line_array[array_search('Transaction_Total', $header_array)] + $line_array[array_search('Fleet_Fees', $header_array)];
					
					$api->addParam("Item_".$counter."_ChartNumber", "58800-".$line_array[array_search('Unit', $header_array)]);
					$api->addParam("Item_".$counter."_Amount", $line_amount);
					$api->addParam("Item_".$counter."_Desc", $line_array[array_search('Unit', $header_array)] . " - " .  $line_array[array_search('Driver_Name', $header_array)] . " - $" .  money_format('', $line_amount));
	
		   			/*
		   			for($p=0;$p < count($line_array);$p++) {
		   				echo "<td nowrap>".$line_array[$p]."</td>";
		   			}
		   			*/
		   			echo "</tr>";
		   		}
	   			
	   		}
	   		
	   		if($counter) {
		   		$api->addParam("VendorID", $vendor_id);
		   		$api->addParam("ReferenceNumber", date("m/d/Y", strtotime($file_invoice_date)));
		   		$api->addParam("BillDate", date("m/d/Y", strtotime($file_invoice_date)));
		   		$api->addParam("BillDateDue", date("m/d/Y", strtotime("+14 day", strtotime($file_invoice_date))));
				$api->addParam("ItemCount", $counter);
				$api->command = "create_bill";
				//$api->show_output = true;
				//$api->debug_post = true;
				
				$rslt = $api->execute();
		   	}
		   	
	   		if($counter == 0 || $rslt->rslt == 1) {
	   			// success, now move this file to the backup folder
	   			rename($use_path."/$entry", $use_path_backup."/$entry");
	   		}

	   	}
	}
	echo "</table>";
?>
<br><br>done.