<? include_once('application.php') ?>
<?

function getCSVValues($string,$separator=",") 
{
    $string = str_replace('""', "'", $string);
    // split the string at double quotes "
    $bits = explode('"',$string);
    $elements = array();
    for ( $i=0; $i < count($bits) ; $i++ ) 
    {
        //odd numbered elements would have been  enclosed by double quotes even numbered elements would not have been
        
        if (($i%2) == 1) 
        {
            // if the element number is odd add the  whole string  to the output array
            $elements[] = $bits[$i];
        } 
        else 
        {
            //otherwise split the unquoted stuff at commas and add the elements to the array
            $rest = $bits[$i];
            $rest = preg_replace("/^".$separator."/","",$rest);
            $rest = preg_replace("/".$separator."$/","",$rest);
            $elements = array_merge($elements,explode($separator,$rest));
        }
    }
    return $elements;
}

	$fleetone_ftp_server = $defaultsarray['quikq_address'];
	$fleetone_ftp_username = $defaultsarray['quikq_username'];
	$fleetone_ftp_password = $defaultsarray['quikq_password'];
	$use_path = $defaultsarray['base_path'].'/quikq/';
	$use_path_backup = $use_path . "/backup/";
	
	if(!file_exists($use_path)) mkdir($use_path);
	if(!file_exists($use_path_backup)) mkdir($use_path_backup);

	$msg = "";
	
	
$skip_uploader=1;
$save_info=1;
	
	
if($skip_uploader==0)
{	
	$conn_id = ftp_connect($fleetone_ftp_server);      // set up basic connection
	if(!$conn_id) 
	{
		$rslt = 0;
		$msg = "Error: couldn't connect to ".$fleetone_ftp_server;
	} 
	else 
	{
		echo "Connected to: $fleetone_ftp_server<br>";
		ob_flush();
	
		$login_result = ftp_login($conn_id, $fleetone_ftp_username, $fleetone_ftp_password);   // login with username and password, or give invalid user message
		if(!$login_result) 
		{
			$rslt = 0;
			$msg = "Error: You do not have access to this FTP server";
		} 
		else 
		{
		
			echo "Retrieving directory contents<br>";
			ob_flush();
		
			//$rslt = ftp_put($conn_id, $out_file, $in_path."/out/".$out_file, FTP_ASCII);
			$file_array = ftp_nlist($conn_id, ".");
			
			echo count($file_array)." files need to be downloaded<br>";
			ob_flush();
			
			foreach($file_array as $file) 
			{
				echo $file."<br>";
				$local_file = $use_path."/$file";
				$rslt = ftp_get($conn_id, $local_file, $file, FTP_ASCII);
				if($rslt) 
				{
					// got the file, now delete the one off the server
					$rslt = ftp_delete($conn_id, $file);
				}
				ob_flush();
			}
			


		}
	}
}
	
	echo "<br>MODE: <b>".($save_info > 0 ? "SAVE" : "VIEW ONLY")."</b>...<br>$msg<br>";
	
	
	// process the files
	/*
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
	*/			
	$fields_array = array('Truck Stop', 
				'City', 
				'State', 
				'Store', 
				'Truck Stop Code', 
				'POS Ticket#', 
				'Date/Time', 
				'Driver', 
				'Unit', 
				'Trailer', 
				'Transaction', 
				'Sequence', 
				'Product Code',
				'Product Name',
				'Qty', 
				'Price',
				'Total',
				'Odometer',
				'Order No',
				'Credit');
				
	$fields_array2 = array("Type",
				"Truck Stop",
				"Store Date",
				"Store Time",
				"City",
				"State",
				"Transaction",
				"Sub Trans",
				"POS Ticket#",
				"Product",
				"Qty",
				"Price",
				"Tax",
				"Discount",
				"SubTotal",
				"Adjusted Price",
				"Adjusted SubTotal",
				"Driver",
				"Unit",
				"Trailer",
				"Confirmation Code",
				"Credit",
				"DC Driver Code",
				"DC Unit Number",
				"Driver Division",
				"Unit Division",
				"Trailer Division",
                "Carrier Fee");
	            //added carrier fee later... may add it here at some point.  "Carrier Fee"
	
	//TruckStop","City","State","Store Date and Time","Transaction","","POS Ticket#","Product","Qty","Price","Tax","Discount","SubTotal","Adj Price","Adj SubTotal","Driver","Unit","Trailer
	
    /*
    Type	
    Truck Stop	
    Store Date	
    Store Time	
    City	
    State	
    Transaction	
    Sub Trans	
    POS Ticket#	
    Product	
    Qty	
    Price	
    Tax	
    Discount	
    SubTotal	
    Adjusted Price	
    Adjusted SubTotal	
    Driver	
    Unit	
    Trailer	
    Confirmation Code	
    Credit	
    DC Driver Code	
    DC Unit Number	
    Driver Division	
    Unit Division	
    Trailer Division	
    Carrier Fee
    */
	$fields_array3 = array("TruckStop",
				"City",
				"State",
				"Store Date and Time",
				"Transaction",
				"[Type]",
				"POS Ticket#",
				"Product",
				"Qty",
				"Price",
				"Tax",
				"Discount",
				"SubTotal",
				"Adj Price",
				"Adj SubTotal",
				"Driver",
				"Unit",
				"Trailer");
		
	$api = new sicap_api_connector();
	$api_item_id = new sicap_api_connector();	
	
	$vendor_id = $api->getVendorIDByName("QuikQ");
	
	$d = dir($use_path);
	echo "<table>";
	while (false !== ($entry = $d->read())) 
	{		
		$api->clearParams();
		
		if(stripos($entry, '.csv') !== false) 
		{   		
	   		$file_part = explode("_", $entry);
	   		$file_invoice_date = trim($file_part[0]);
	   		$file_invoice_date=str_replace("VGENCSV","",$file_invoice_date);
	   		$file_invoice_date=str_replace("TRANSRPT","",$file_invoice_date);
	   		if(substr_count($file_invoice_date,"TransactionReport_") > 0)
	   		{
	   			//$file_invoice_date=str_replace("TransactionReport_","",$file_invoice_date);
	   			//$file_invoice_date=date("Y-m-d",time());
	   			
	   			$file_invoice_date=date("m/d/Y",strtotime("-1 day",time()));
	   		}
	   		
	   		echo $entry." | ".$file_invoice_date."<br>";
	   		
	   		$contents = file_get_contents($use_path."/$entry");
	   		
	   		$contents=str_replace("'","",$contents);
	   		
	   		echo "<br>CONTENTS:<br><pre>".$contents."</pre><br>";
	   		
	   		
	   		//$contents_array = explode(chr(10), $contents);
	   		$contents_array = explode(chr(13), $contents);
	   		$header_array = explode(",", $contents_array[0]);
	   		
	   		$file_mode=0;
	   			   		
	   		if(substr_count($contents,"Adj SubTotal") > 0)			$file_mode=3;		//18 columns (one header has no label)
	   		elseif(substr_count($contents,"Adjusted SubTotal") > 0)	$file_mode=2;		//27 columns...now 28 columns counting the newer carrier fee.
	   		elseif(substr_count($contents,"Total") > 0)				$file_mode=1;		//20 columns
                        
	   		
	   		echo "<tr>";
	   		if($file_mode==1)
	   		{
	   			for($m=0;$m < count($fields_array);$m++) 
	   			{
	   				$header_entry = $fields_array[$m];
	   				echo "<td>$header_entry</td>";
	   			}
	   		}
	   		if($file_mode==2 || $file_mode==0)
	   		{   //ADDED as of 12/23/2019.  Newest version has no column headers at all, but so far matches this mode.
	   			for($m=0;$m < count($fields_array2);$m++) 
	   			{
	   				$header_entry = $fields_array2[$m];
	   				echo "<td>$header_entry</td>";
	   			}
	   		}
	   		if($file_mode==3)
	   		{
	   			for($m=0;$m < count($fields_array3);$m++) 
	   			{
	   				$header_entry = $fields_array3[$m];
	   				echo "<td>$header_entry</td>";
	   			}
	   			$file_invoice_date=date("m/d/Y",strtotime("-1 day",time()));
	   		}
	   		echo "</tr>";
	   		
	   		
	   		
	   		$counter = 0;
             $mrr_start_index=1;                     if($file_mode==0)     $mrr_start_index=0;
	   		
             for($i=$mrr_start_index;$i < count($contents_array);$i++) 
	   		{
	   			$line_string=trim($contents_array[$i]);
	   			
	   			$line_string=str_replace(chr(10),"",$line_string);
	   			$line_string=str_replace('"',"",$line_string);
	   			
	   			//$line_array = explode(",", $contents_array[$i]);
	   			   			
	   			//$line_array = getCSVValues($contents_array[$i]); // new funtion handles quoted (") entries properly
	   				   			
	   			//$line_total = $line_array[array_search('Total', $header_array)];			// + $line_array[array_search('Fleet_Fees', $header_array)];	//Transaction_Total
				// && $line_total > 0 && substr_count($line_string,"Total") > 0
				
				if(trim($line_string)!="") 
				{
					$line_array = explode(",", $line_string);
					
					echo "<tr>";
					
					$line_total="0.00";
					$line_amount="0.00";
					
					$unit_no="";
					$truck_name="";
					$driver_name="";
					$driver_id="";
					
					if($file_mode==1)
					{
     					for($j=0;$j < count($line_array);$j++) 
     					{
     						$temp="";
     						if($j==0)		$temp=trim($line_array[$j]);		//'Truck Stop', 
               				if($j==1)		$temp=trim($line_array[$j]);		//'City', 
               				if($j==2)		$temp=trim($line_array[$j]);		//'State', 
               				if($j==3)		$temp=trim($line_array[$j]);		//'Store', 
               				if($j==4)		$temp=trim($line_array[$j]);		//'Truck Stop Code', 
               				if($j==5)		$temp=trim($line_array[$j]);		//'POS Ticket#', 
               				if($j==6)		$file_invoice_date=date("Y-m-d",strtotime(trim($line_array[$j])));		//'Date/Time', 
               				if($j==7)		$driver_id=trim($line_array[$j]);	//'Driver', 
               				if($j==8)		$unit_no  =trim($line_array[$j]);	//'Unit', 
               				if($j==9)		$temp=trim($line_array[$j]);		//'Trailer', 
               				if($j==10)		$temp=trim($line_array[$j]);		//'Transaction', 
               				if($j==11)		$temp=trim($line_array[$j]);		//'Sequence', 
               				if($j==12)		$temp=trim($line_array[$j]);		//'Product Code',
               				if($j==13)		$temp=trim($line_array[$j]);		//'Product Name',
               				if($j==14)		$temp=trim($line_array[$j]);		//'Qty', 
               				if($j==15)		$temp=trim($line_array[$j]);		//'Price',
               				if($j==16)	    $line_total=trim($line_array[$j]);	//'Total',
               				if($j==17)		$temp=trim($line_array[$j]);		//'Odometer',
               				if($j==18)		$temp=trim($line_array[$j]);		//'Order No',
               				if($j==19)		$temp=trim($line_array[$j]);		//'Credit'			
               				
               				echo "<td>".trim($line_array[$j])."</td>";		
               			}		
					}
					elseif($file_mode==2 || $file_mode==0)
					{   //ADDED as of 12/23/2019.  Newest version has no column headers at all, but so far matches this mode.
						for($j=0;$j < count($line_array);$j++) 
     					{
     						$temp="";
     						if($j==0)		$temp=trim($line_array[$j]);		//"Type",
               				if($j==1)		$temp=trim($line_array[$j]);		//"Truck Stop", 
               				if($j==2)		$file_invoice_date=date("Y-m-d",strtotime(trim($line_array[$j])));		//"Store Date", 
               				if($j==3)		$temp=trim($line_array[$j]);		//"Store Time", 
               				if($j==4)		$temp=trim($line_array[$j]);		//"City", 
               				if($j==5)		$temp=trim($line_array[$j]);		//"State", 
               				if($j==6)		$temp=trim($line_array[$j]);		//"Transaction", 
               				if($j==7)		$temp=trim($line_array[$j]);		//"Sub Trans",
               				if($j==8)		$temp=trim($line_array[$j]);		//"POS Ticket#", 
               				if($j==9)		$temp=trim($line_array[$j]);		//"Product", 
               				if($j==10)		$temp=trim($line_array[$j]);		//"Qty", 
               				if($j==11)		$temp=trim($line_array[$j]);		//"Price", 
               				if($j==12)		$temp=trim($line_array[$j]);		//"Tax",
               				if($j==13)		$temp=trim($line_array[$j]);		//"Discount",
               				if($j==14)		$temp=trim($line_array[$j]);		//"SubTotal", 
               				if($j==15)		$temp=trim($line_array[$j]);		//"Adjusted Price",
               				if($j==16)	    $line_total=trim($line_array[$j]);	//"Adjusted SubTotal",
               				if($j==17)	    $driver_name=trim($line_array[$j]);	//"Driver",
               				if($j==18)	    $unit_no=trim($line_array[$j]);		//"Unit",
               				if($j==19)		$temp=trim($line_array[$j]);		//"Trailer",
               				if($j==20)		$temp=trim($line_array[$j]);		//"Confirmation Code",
               				if($j==21)		$temp=trim($line_array[$j]);		//"Credit",
               				if($j==22)	    $driver_id=trim($line_array[$j]);	//"DC Driver Code",
               				if($j==23)		$temp=trim($line_array[$j]);		//"DC Unit Number",
               				if($j==24)		$temp=trim($line_array[$j]);		//"Driver Division",
               				if($j==25)		$temp=trim($line_array[$j]);		//"Unit Division",
               				if($j==26)		$temp=trim($line_array[$j]);		//"Trailer Division"
                            if($j==27)		$temp=trim($line_array[$j]);		//"Carrier Fee"
               				
               				echo "<td>".trim($line_array[$j])."</td>";		
               			}
					}
					elseif($file_mode==3)
					{
     					for($j=0;$j < count($line_array);$j++) 
     					{
     						$temp="";
     						if($j==0)		$temp=trim($line_array[$j]);		//'TruckStop', 
               				if($j==1)		$temp=trim($line_array[$j]);		//'City', 
               				if($j==2)		$temp=trim($line_array[$j]);		//'State',
               				if($j==3)	
               				{	//'Store Date and Time', 
               					$tmp_date=trim($line_array[$j]);
               					$tmp_date=str_replace("-","/",$tmp_date);
               					$tmp_date=str_replace(" CT","",$tmp_date);
               					//$file_invoice_date=date("Y-m-d",strtotime($tmp_date));		//'Store Date and Time', 
               					$line_array[$j]=$tmp_date;
               				}
               				if($j==4)		$temp=trim($line_array[$j]);		//'Transaction', 
               				if($j==5)		$temp=trim($line_array[$j]);		//'[Type]', 
               				if($j==6)		$temp=trim($line_array[$j]);		//'POS Ticket#', 
               				if($j==7)		$temp=trim($line_array[$j]);		//'Product',
               				if($j==8)		$temp=trim($line_array[$j]);		//'Qty', 
               				if($j==9)		$temp=trim($line_array[$j]);		//'Price',
               				if($j==10)		$temp=trim($line_array[$j]);		//"Tax",
               				if($j==11)		$temp=trim($line_array[$j]);		//"Discount",
               				if($j==12)		$temp=trim($line_array[$j]);		//"SubTotal", 
               				if($j==13)		$temp=trim($line_array[$j]);		//"Adj Price",
               				if($j==14)	    $line_total=trim($line_array[$j]);	//"Adj SubTotal",
               				if($j==15)	    $driver_id=trim($line_array[$j]);	//'Driver', 
               				if($j==16)	    $unit_no  =trim($line_array[$j]);	//'Unit', 
               				if($j==17)		$temp=trim($line_array[$j]);		//'Trailer', 
               				
               				echo "<td>".trim($line_array[$j])."</td>";		
               			}		
					}
					$truck_name=$unit_no;
					$line_amount=$line_total;
					
					/*					
		   			for($m=0;$m < count($fields_array);$m++) 
		   			{		   				
		   				$header_entry = $fields_array[$m];
		   				if(strpos($fields_array[$m], "Total") === false) 
		   				{
		   					echo "<td>".$line_array[array_search($fields_array[$m], $header_array)]."</td>";
		   					echo "<td>".$line_array[array_search($fields_array[$m], $header_array)]."</td>";
		   				} 
		   				elseif($fields_array[$m] == 'Total') 
		   				{
		   					$line_total=trim($line_array[$y]);
		   					$line_amount=$line_total;
		   					echo "<td>$line_total</td>";
		   				}		   				
		   			}
		   			
		   			
					$unit_no=$line_array[array_search('Unit', $header_array)];
					$truck_name=$unit_no;
					$driver_name="";
					$driver_id=$line_array[array_search('Driver', $header_array)];
		   			*/
		   			$counter++;
		   			
					//$chart_id = $api_item_id->getChartIDByName($line_array[array_search('Unit', $header_array)]);
					//$line_amount = $line_array[array_search('Total', $header_array)];	// + $line_array[array_search('Fleet_Fees', $header_array)];	//Transaction_Total
					
					
					//get the name of the driver
					$sql = "
               			select payroll_first_name,payroll_last_name,name_driver_first,name_driver_last               			
               			from drivers
               			where id = '".sql_friendly($driver_id)."'
               		";
               		$data = simple_query($sql);
               		if($row = mysqli_fetch_array($data))
               		{
               			$driver_name=trim("".$row['payroll_first_name']." ".$row['payroll_last_name']."");	
               			if($driver_name=="")		$driver_name=trim("".$row['name_driver_first']." ".$row['name_driver_last']."");	
               		}
               		
					//get the name of the truck
					$sql = "
               			select id,name_truck               			
               			from trucks
               			where name_truck like '".sql_friendly($unit_no)."'
               				and deleted=0 and active=1
               			order by deleted asc, active desc, id asc
               		";
               		$data = simple_query($sql);
               		if($row = mysqli_fetch_array($data))
               		{
               			$truck_name=trim("".$row['name_truck']."");
               		}
               		else
               		{
               			$sql = "
                    			select id,name_truck               			
                    			from trucks
                    			where name_truck like '%".sql_friendly($unit_no)."'
                    			order by deleted asc, active desc, id asc
                    		";
                    		$data = simple_query($sql);
                    		if($row = mysqli_fetch_array($data))
                    		{
                    			$truck_name=trim("".$row['name_truck']."");
                    		}	
               		}
					
					
					$api->addParam("Item_".$counter."_ChartNumber", "58800-".$truck_name);
					$api->addParam("Item_".$counter."_Amount", $line_amount);
					$api->addParam("Item_".$counter."_Desc", $truck_name . " - " .$driver_name. " - $" .  money_format('', $line_amount));
	
		   			
		   			//for($p=0;$p < count($line_array);$p++) 		{	echo "<td nowrap>".$line_array[$p]."</td>";	}
		   			
		   			
		   			echo "</tr>";
		   		}
		   		else
		   		{
		   			//echo "<tr><td colspan='20'><pre>".$line_total."</pre></td></tr>";
		   			echo "<tr><td colspan='20'>".$line_string."</td></tr>";	//<pre></pre>
		   		}	   			
	   		}
	   		
	   		if($counter > 0 && $save_info > 0) 
	   		{
		   		if($file_invoice_date=="" || $file_invoice_date=="12/31/1969" || $file_invoice_date=="00/00/0000" || $file_invoice_date=="0000-00-00")		$file_invoice_date=date("m/d/Y",strtotime("-1 day",time()));
		   		
		   		
		   		$api->addParam("VendorID", $vendor_id);
		   		$api->addParam("ReferenceNumber", date("m/d/Y", strtotime($file_invoice_date)));
		   		$api->addParam("BillDate", date("m/d/Y", strtotime($file_invoice_date)));
		   		$api->addParam("BillDateDue", date("m/d/Y", strtotime("+7 day", strtotime($file_invoice_date))));
				$api->addParam("ItemCount", $counter);
				$api->command = "create_bill";
				//$api->show_output = true;
				//$api->debug_post = true;
				
				$rslt = $api->execute();
		   	}
		   	
	   		if($save_info > 0 && ($counter > 0 || $rslt->rslt == 1)) 
	   		{
	   			// success, now move this file to the backup folder
	   			rename($use_path."/$entry", $use_path_backup."/$entry");
	   		}

	   	}
	}
	echo "</table>";
?>
<br><br>done. QuikQ V 1.0.8