<? include('application.php') ?>
<?

	// conard_loads
	// 387ns6njx674


	//if(!isset($_GET['manual'])) die('only manual runs for now');
	/*
	$command_to_run = "d:/php/AutoBatch Conard Scan Loads - OCR.bat";
	$rslt = shell_exec($command_to_run); //dash at the end to output content
	die($rslt);
	*/

	// base directory params
	$output_dir = getcwd()."/scanned_invoices/files/";
	$input_dir = getcwd()."/scanned_invoices/working/";
	$input_base_dir = getcwd()."/scanned_invoices/";
	
	$invalid_page_array = array();
	$invalid_page_text_array = array();

	$pdf_module_loaded = false;
	
	// grab a list of all the files in the 'scan' directory to process
	$d = dir($input_base_dir);
	while (false !== ($entry = $d->read())) {
		if(!is_dir($input_base_dir.$entry)) {
	   		
	   		//$rslt = @rename($input_base_dir.$entry, $input_dir.$entry);
	   		$rslt = @copy($input_base_dir.$entry, $input_dir.$entry);
	   		if($rslt) {
	   			echo $entry."<br>";
	   			if(!$pdf_module_loaded) {
	   				$pdf_module_loaded = true;
					// load our PDF handler module
					$pdf_split = new COM("PDFSplitMerge.PDFSplitMerge");
					$pdf_split->SetCode("9898885DCB97DE31B6D42C"); // sets the key so it knows it's been registered and is not in a trial mode
	   			}
	   			process_scan_file($entry);
	   			unlink($input_dir.$entry);
	   		} else {
	   			echo "<span style='color:red'>Error:</span> $entry - File is probably open somewhere else<br>";
	   		}
	   		echo "<hr>";
	   	}
	}
	$d->close();
	
	// if there were any problem pages, log them
	$counter = 0;
	foreach($invalid_page_array as $bad_file) {
		$sql = "
			insert into ".mrr_find_log_database_name()."log_scan_loads
				(linedate_added,
				filename,
				filesize,
				rslt,
				document_text,
				section_id)
				
			values (now(),
				'".sql_friendly($bad_file)."',
				'".filesize($output_dir.$bad_file)."',
				0,
				'".sql_friendly($invalid_page_text_array[$counter])."',
				1)
		";
		simple_query($sql);
		$counter++;
	}
	
	echo "<br><br>done...";
	
	function process_scan_file($filename) {
		global $pdf_split;
		global $time_uuid;
		global $invalid_page_array;
		global $invalid_page_text_array;
		global $input_base_dir;
		global $input_dir;
		global $output_dir;
	
		$filename = $input_dir.$filename;
		
		
		// split all the pages out first
		$page_count = $pdf_split->GetNumberOfPages($filename, "");
		$page_list = '';
		for($i=1;$i<=$page_count;$i++) $page_list .= "$i;";
		
		$time_uuid = time().uniqid(true);
		
		$pdf_split->split($filename,$page_list,$input_dir."$time_uuid-%d.pdf");
	
	
		$i=0;
		$page_breaks = 0;
		$current_invoice_number = '';
		$current_section_page_start = 0;
		$invoice_number = '';
		$page_list = "";
		
		$invoice_id = 0;
		
		for($p=0;$p < $page_count;$p++) {
			$filename_base = "$time_uuid-$i.pdf";
			$filename = $input_dir.$filename_base;
			//echo "filename ($filename)<br>";
			$command_to_run = 'C:/web/trucking.conardlogistics.com/AutoBatch/pdftotext.exe '.$filename.' -';
			
			//echo "<br><br>$command_to_run<br><br>";
			
			$page = shell_exec($command_to_run); //dash at the end to output content
			
			$i++;
			//echo "Page $i<br>";
			
			if(stripos($page, "Invoice Date") !== false && stripos($page, "Payment Terms") !== false && stripos($page, "Due Date") !== false) {
				
				if($current_section_page_start) {
					merge_section($invoice_number, $current_section_page_start, $i-1, $time_uuid);
				}
				
				// invoice - so it's a new page section
				$invoice_number = get_load_number($page,'Due Date:',100);
				$current_invoice_number = $invoice_number;
				$current_section_page_start = $i;
				echo "New Section ($current_invoice_number): $current_section_page_start<br>";
			}
			
			
			/*
			echo "Invoice #: ";
			if($invoice_number == '') {
				// keep track of this so we can show it in a report of the pages
				// not recognized
				echo "Could not locate";
				$invalid_page_array[] = $filename_base;
				$invalid_page_text_array[] = $page;
				rename($filename, $output_dir.$filename_base);
			} else {
				echo $invoice_number;
				split_file_handler($filename, $invoice_number, $filename_base, $page);
			}
			echo "<br><hr><br>";
			*/
			
			
			
		}
		
	
	}
	
	
	function merge_section($invoice_number, $start_page, $last_page, $file_partial) {
		
		global $pdf_split;
		global $input_base_dir;
		global $input_dir;
		global $output_dir;
		
		$page_count = $last_page - $start_page;
		
		$merge_list = "";
		for($i=0;$i < $page_count;$i++) {
			echo "File $i<br>";
			$merge_list .= $input_dir."/".$file_partial."-".($start_page + $i - 1).".pdf|";
		}
		
		$new_filename = $invoice_number."_".$file_partial.".pdf";
		
		$pdf_split->Merge($merge_list,$output_dir."/".$new_filename);

		/*		
		echo "<br><br>$merge_list<br><br>";
		echo "<br><br>$output_dir/$new_filename<br><br>";
		echo $pdf_split;
		die;
		*/
	}
	
	
	
	function get_load_number($content, $search_string, $search_length) {
		if(stripos($content, $search_string) === false) {
			return '';
		} else {
			$invoice_area = str_replace(chr(10)," ",trim(substr($content, stripos($content, $search_string)+strlen($search_string), $search_length)));
			//die("Invoice Area: (".$content.")");
			$invoice_number = explode(" ", $invoice_area);
			$invoice_number = $invoice_number[0];
			
			$invoice_number = str_replace(chr(183),"-",$invoice_number);
			$invoice_number = str_replace(",","",$invoice_number);
			$invoice_number = str_replace(".","-",$invoice_number);
			$invoice_number = str_replace("/","",$invoice_number);
			$invoice_number = str_replace("--","-",$invoice_number);
			
			// make sure what we've found is numeric (minus the dashes), if not, then we read the load number improperly
			if(!is_numeric(str_replace("-","", $invoice_number))) {
				$invoice_number = '';
			}
			
			return $invoice_number;		
		}
	}
	
	function split_file_handler($tmp_filename, $current_invoice_number, $old_filename_base, $document_text) {
		global $pdf_split;
		global $defaultsarray;
		global $output_dir;
		global $time_uuid;
		global $input_dir;
		global $invalid_page_array;
		global $invalid_page_text_array;


		$new_filename_base = $current_invoice_number."-".date("Ymd");
		
		if(file_exists($output_dir.$new_filename_base.".pdf")) {
			for($i=1;$i<1000;$i++) {
				$tmp_filename_base = $new_filename_base."-$i";
				if(!file_exists($output_dir.$tmp_filename_base.".pdf")) {
					$new_filename_base = $tmp_filename_base;
					break;
				}
			}
		}
		
		$new_filename = $new_filename_base.".pdf";
		
		//die("($current_invoice_number | $new_filename_base | $new_filename)");
		
		if(!@rename($tmp_filename, $output_dir.$new_filename)) {
			$invalid_page_array[] = $old_filename_base;
			$invalid_page_text_array[] = $document_text;
			rename($tmp_filename, $output_dir.$old_filename_base);
		} else {
		
			//unlink($tmp_filename);
			 
			 //$invoice_id = get_invoice_id($current_invoice_number);
			 
			 
			 // log this file
			 $sql = "
			 	insert into ".mrr_find_log_database_name()."log_scan_loads
			 		(linedate_added,
			 		filename,
			 		filesize,
			 		load_id,
			 		rslt,
			 		document_text,
			 		section_id)
			 		
			 	values (now(),
			 		'".sql_friendly($new_filename)."',
			 		'".filesize($output_dir.$new_filename)."',
			 		'".sql_friendly($current_invoice_number)."',
			 		1,
			 		'".sql_friendly($document_text)."',
			 		1)
			 ";
			 simple_query($sql);
		}
	}
	
?>