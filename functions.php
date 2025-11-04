<?
	function d($ustring) 
	{
		echo "<pre>$ustring</pre>";
		die;
	}	
	function debug_check($kill_page = false, $ip_restriction = '') 
	{
		global $query_count;
		global $page_timer_array;		
		
		if($ip_restriction != '' && $ip_restriction != $_SERVER['REMOTE_ADDR']) return;
		
		echo "
			query count: ($query_count)
			page load time: ".show_page_time()."
		"; 
		
		if($kill_page) {
			echo "<pre>";
			var_dump($page_timer_array);
			echo "</pre>";
			die();
		}
	}
	
	function debug($variable) {
		echo '<pre>';
		var_dump($variable);
		echo '</pre>';
	}
	

	function get_amount($amount) {
		// function to strip "$" and "," from currency fields
		
		$amount = str_replace("%","",str_replace(",","",str_replace("$", "", $amount)));
		if($amount == '') $amount = 0;
		return $amount;
	}
	
	function get_file_ext($file) 
	{
		$file_ext = '';
		
		if(strpos($file, ".") !== false) {
			$file_parts = explode(".", $file);
			$file_ext = $file_parts[count($file_parts)-1];
			
		}
		
		return $file_ext;
	}	
	function invoice_type($type_id) 
	{
		// return the text type of the invoice_type
		if($type_id == 1) {
			return 'Monthly';
		} elseif($type_id == 2) {
			return 'Date Range';
		}
		return $type_id;
	}
		
	function trim_string($use_string, $chars) {
		if(strlen($use_string) > $chars) {
			// note it too long, shorten it and add a "..."
			$use_note = substr($use_string, 0, $chars)."...";
		} else {
			$use_note = $use_string;
		}
		
		return $use_note;
	}

	function get_edi_files() {
		// function to return a list of the EDI files that have been uploaded to the FTP site
		// used for importing incoming loads
		global $defaultsarray;
		
		$ftp_dir = $defaultsarray['edi_ftp_upload'];
		
		$listDir = Array();
		if($checkDir = opendir($ftp_dir)) {
			// check all files in $dir, add to array listDir or listFile
			while($file = readdir($checkDir)){
				
				if($file != "." && $file != ".."){
					if(is_file($ftp_dir . "/" . $file)){
						$listDir[] = $file;
					}
				}
			}
		} else {
			echo "Could not open directory";
		}
		
		return $listDir;
	}

	function process_date($use_date, $format_type = 0) {
		// will return a zero if the date is blank, or an MySQL formatted date if not
		// format_type: 0 - mysql
		// format_type: 1 - normal display (m/d/y)
		
		if($use_date == '' || $use_date == 0) {
			if($format_type == 0) {
				return 0;
			} else {
				return '';
			}
		} else {
			if($format_type == 0) {
				return date("Y-m-d", strtotime($use_date));
			} else {
				return date("n/j/Y", strtotime($use_date));
			}
		}
	}

	function get_location_id($location_name) {
		// see if this location is in the database, if not, add it //
		$sql = "
			select id
			
			from locations_inventory
			where deleted = 0
				and location_inventory_name = '".sql_friendly($location_name)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		return $row['id'];
		
	}
	
	function createuuid() {
		return md5(uniqid(rand(), true));
	}
	
	function checkbox_validate($field_name) {
		// function to return 1 if checkbox exists and 0 if it doesn't
		
		if(isset($_POST[$field_name])) {
			return 1;
		} else {
			return 0;
		}
	}
	
	function get_inventory_id_by_item_id($item_id) {
		// look up the item_id from the item_id_disp
		$sql = "
			select id
			
			from inventory
			where item_id = '".sql_friendly($item_id)."'
				and deleted = 0
		";
		$data_item_id = simple_query($sql);
		if(mysqli_num_rows($data_item_id)) {
			$row_item_id = mysqli_fetch_array($data_item_id);
			return $row_item_id['id'];
		} else {
			return '';
		}
	}
	
	function inventory_qty_on_hand($item_id) {
	
		// get the qty available
		$sql = "
			select sum(qty_on_hand) as total_qty_on_hand
			
			from inventory_entries
			where item_id = '$item_id'
				and linedate_received > 0
				and deleted = 0
		";
		$data_qty = simple_query($sql);
		
		if(!mysqli_num_rows($data_qty)) {
			$use_qty = 0;
		} else {
			$row_qty = mysqli_fetch_array($data_qty);
			$use_qty = $row_qty['total_qty_on_hand'];
		}
		if($use_qty == '') $use_qty = 0;

		return number_format($use_qty,0);
	}

	function select_box_disp($sql,$form_name,$selected_val = 0,$default_text = '',$extra_params = '') {
		// function to display a select box based on a query and other vars
		// SQL must return two vars, one named use_val and one named use_disp
		$data = simple_query($sql);
		$return_var = "<select id='$form_name' name='$form_name' $extra_params>";
		if($default_text != '') 
		{
			$def_val="";
			if(substr_count($sql,"from option_values") > 0 || $default_text=='__')		$def_val="0";
			
			$use_selected = "";
			if($selected_val == 0) 	$use_selected = "selected";
			$return_var .= "<option $use_selected value='".$def_val."'>$default_text</option>";
		}
		while($row = mysqli_fetch_array($data)) 
		{
			$use_selected = "";
			if($row['use_val'] == $selected_val) 	$use_selected = "selected";
			$return_var .= "<option $use_selected value='$row[use_val]'>$row[use_disp]</option>";
		}
		
		$return_var .= "</select>";
		
		return $return_var;
	}

	/* option_value_text will take an ID number of the option and return the text result of it
	 * (i.e. in the case of a country, it might take '660' and return 'USA' (results pulled from the tblorder_idnfo_options table */
	function option_value_text($oid,$cd=0) 
	{	
		if($cd==0)
		{
			$sql = "
				select list_value
			
				from list_values
				where id = '$oid'
			";
			$data_option = simple_query($sql);
			$row_option = mysqli_fetch_array($data_option);
			
			return $row_option['list_value'];
		}
		elseif($cd==1)
		{
			$sql = "
				select fname as list_value
			
				from option_values
				where id = '$oid'
			";
			$data_option = simple_query($sql);
			$row_option = mysqli_fetch_array($data_option);
			
			return $row_option['list_value'];
		}
		elseif($cd==2)
		{
			$sql = "
				select fvalue as list_value
			
				from option_values
				where id = '$oid'
			";
			$data_option = simple_query($sql);
			$row_option = mysqli_fetch_array($data_option);
			
			return $row_option['list_value'];
		}
	}
	
	function get_list_category_id($category_name) {
		$sql = "
			select id
			
			from listcategories
			where category_name = '".sql_friendly($category_name)."'
			and deleted = 0
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		return $row['id'];
	}
	
	function display_optionbox($display_type, $oiocat_id, $form_name, $use_value = 0) {
		global $datasource;
		/* function to display a selectbox based on info in the tblorder_info_options table */
		if($use_value != 0) $_POST[$form_name] = $use_value;
			
		
		if(!isset($_POST[$form_name])) $_POST[$form_name] = "";
		
		$sql = "
			select list_value as oiovalue,
				list_values.id,
				xdefault,
				list_categories.blank_option_name
				
			from list_values, list_categories
			where list_category_id = '$oiocat_id'
				and list_values.list_category_id = list_categories.id
				and list_values.deleted = 0
			order by list_value
		";
		$data_selectbox = simple_query($sql);
		
		echo "<font class='standard12'>";
		if($display_type == 1) {
			/* select box */
			echo "<select id='id_$form_name' name='$form_name'>";
			/* if a blank option was specified, then display it */
			$row_selectbox = mysqli_fetch_array($data_selectbox);
			if($row_selectbox['blank_option_name'] != "") {
				echo "<option value=''>$row_selectbox[blank_option_name]</option>";
			}
			mysqli_data_seek($data_selectbox,0);
			while($row_selectbox = mysqli_fetch_array($data_selectbox)) {
				if($row_selectbox['xdefault'] == 1 && $_POST[$form_name] == "") {
					$uselected = " selected ";
				} else if($_POST[$form_name] == $row_selectbox['id']) {
					$uselected = " selected ";
				} else {
					$uselected = "";
				}
				/* see if this option was selected */
				echo "<option value='$row_selectbox[id]' $uselected>$row_selectbox[oiovalue]</option>";
			}
			echo "</select>";
		} else if($display_type == 2) {
			/* radio buttons */
			echo "<table class='standard12'>";
			while($row_selectbox = mysqli_fetch_array($data_selectbox)) {
				if($row_selectbox['xdefault'] == 1 && $_POST[$form_name] == "") {
					$uselected = " checked ";
				} else if($_POST[$form_name] == $row_selectbox['id']) {
					$uselected = " checked ";
				} else {
					$uselected = "";
				}
				/* see if this option was selected */
				echo "<tr><td><input type='radio' name='$form_name' value='$row_selectbox[id]' $uselected>$row_selectbox[oiovalue]</td></tr>";
			}
			echo "</table>";
		}
		echo "</font>";
	}

	function javascript_redirect($use_url) {
		?>
		<script language="javascript">
			window.location = "<?=$use_url?>";
		</script>
		<?
		die();
	}	
	
	function sql_friendly($sql_string) {
		global $datasource;
		
		$hold = mysqli_real_escape_string($datasource, $sql_string);
	
		/*
		$hold = str_replace("'","''",$sql_string);
		$hold = str_replace("//","////",$hold);
		*/		
	
		return $hold;
	
	}
	
	function simple_query($sql) {
		global $datasource;
		global $query_count;
		
		//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29') echo "$sql<br>";
		
		$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql );
		$query_count++;
		
		//log drivers table updates....this is temporary until reason can be found for why info is disappearing...
		$sql=strtolower($sql);
		if(substr_count($sql,"update drivers") > 0 && substr_count($sql,"update drivers_") == 0)
		{		
			if(!isset($_SESSION['user_id']))	$_SESSION['user_id']=0;
			
			$update_id=0;
			$update_id_text="";
			$pos1=0;
			$page_name=$_SERVER['PHP_SELF'];
			
			$sql=str_replace("'","",trim($sql));
			
			if(substr_count($sql," id=") > 0)
			{
				$pos1=strpos($sql," id=");
				$pos2=strpos($sql," ",$pos1);	
				$update_id_text=trim(substr($sql,$pos1));
				if(substr_count($update_id_text,"id=") > 0)
				{
					$update_id_temp=str_replace("id=","",$update_id_text);
					$update_id_temp=trim($update_id_temp);	
					if(is_numeric($update_id_temp))	$update_id=(int) $update_id_temp;
					$update_id_text=trim($update_id_temp);
				}
				elseif($pos2>$pos1)
				{
					$update_id_temp=trim(substr($sql,$pos1,$pose2-$pos1));	
					$update_id_temp=trim(str_replace("id=","",$update_id_temp));
					if(is_numeric($update_id_temp))	$update_id=(int) $update_id_temp;
					$update_id_text=trim($update_id_temp);
				}				
			}
			if(substr_count($sql," id =") > 0 && $pos1==0)
			{
				$pos1=strpos($sql," id =");
				$pos2=strpos($sql," ",$pos1);	
				$update_id_text=trim(substr($sql,$pos1));
				if(substr_count($update_id_text,"id =") > 0)
				{
					$update_id_temp=str_replace("id =","",$update_id_text);
					$update_id_temp=trim($update_id_temp);	
					if(is_numeric($update_id_temp))	$update_id=(int) $update_id_temp;
					$update_id_text=trim($update_id_temp);
				}
				elseif($pos2>$pos1)
				{
					$update_id_temp=trim(substr($sql,$pos1,$pose2-$pos1));	
					$update_id_temp=trim(str_replace("id=","",$update_id_temp));
					if(is_numeric($update_id_temp))	$update_id=(int) $update_id_temp;
					$update_id_text=trim($update_id_temp);
				}
			}
			
			
			mrr_add_user_change_log($_SESSION['user_id'],0,$update_id,0,0,0,0,0,"Driver Update Query: ".$update_id_text.", [".$page_name."]");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		}	
		/**/
		return $data;
	}
	

	function parent_window_refresh($close_after_refresh = true) {
		echo "
			<script type='text/javascript'>
		   		window.opener.focus();
		   		window.opener.location.reload();
		   		".($close_after_refresh ? "window.close()" : "").";
	   		</script>
   		";
		//die();
	}
	
	function parent_window_submit($close_after_refresh = true) {
		echo "
			<script type='text/javascript'>
		   		window.opener.focus();
		   		window.opener.document.save_load_handler();
		   		".($close_after_refresh ? "window.close()" : "").";
	   		</script>
   		";
		//die();
	}	
	
	function buildling_location_list($use_input_name = "location_id", $use_default_id = 0) {
		global $datasource;
		
		$sql = "
			select *
			
			from locations
			where deleted = 0
			order by location_name
		";
		$data = simple_query($sql);
		
		/* if the default_id is set to 0, then grab the ID of the default building location */
		if($use_default_id == 0) {
			$sql = "
				select id
				
				from locations
				where default_location = 1
					and deleted = 0
			";
			$data_default = simple_query($sql);
			$row_default = mysqli_fetch_array($data_default);
			
			$use_default_id = $row_default;			
		}
		

		echo "<select name='$use_input_name'>";
		
		while($row = mysqli_fetch_array($data)) {
			if($use_default_id == $row['id']) {
				$check_entry = "selected";
			} else {
				$check_entry = "";
			}
			echo "<option value='$row[id]' $check_entry>$row[location_name]</option>";
		}
		
		echo "</select>";
	}
	
	function inventory_location_name($location_id) {
		// function to return the textual name of the location by location_id
		$sql = "
			select location_inventory_name
			
			
			from locations_inventory
			where id = '".sql_friendly($location_id)."'
		";
		$data = simple_query($sql);
		if(mysqli_num_rows($data)) {
			$row = mysqli_fetch_array($data);
			return $row['location_inventory_name'];
		} else {
			return 'Not Found';
		}
	}
	
	function inventory_building_name_by_location_id($location_id) {
		// function to return the textual name of the building by the inventory_id (NOT THE BUILDING ID)
		$sql = "
			select l.location_name
			
			
			from locations_inventory li, locations l
			where li.id = '".sql_friendly($location_id)."'
				and li.location_id = l.id
		";
		$data = simple_query($sql);
		if(mysqli_num_rows($data)) {
			$row = mysqli_fetch_array($data);
			return $row['location_name'];
		} else {
			return 'Not Found';
		}
	}
	
	function get_internal_address() {
		// function to return the site internal address
		// this is needed for items like the PDF creator that references the internal address
		
		global $defaultsarray;

		if(isset($defaultsarray['internal_address']) && $defaultsarray['internal_address'] != '') {
			$use_address = $defaultsarray['internal_address'];
		} else {
			$use_address = "http://". $_SERVER['HTTP_HOST'];
		}
		
		if(substr($use_address,-1) != '/') {
			return $use_address.'/';
		} else {
			return $use_address;
		}
	}

	function print_contents($use_filename = '', $html = '', $display_mode = 0, $header = "", $footer = "", $show_page_numbers = true) {
		// takes a couple POST parameters, writes them to a PDF file, then displays the PDF
		// display_mode: 0 = portrait, 1 = landscape
		//echo $_POST['use_html'];
		
		
	     //generate pdf content and write to html file to conver to pdf
          
          if($html == '') {
          	$html = $_POST['use_html'];
          }
          
          if($use_filename == '') {
          	$use_filename = $_POST['use_filename'];
          }
         
		return print_contents_include1($use_filename, $html, $display_mode, $header, $footer, $show_page_numbers);
		//echo $pdfContent;
	}
	
	function print_contents_include1($use_filename, $html, $display_mode, $header = "", $footer = "", $show_page_numbers = true) {
		// display_mode 0 = portrait, 1 = landscape
		
		ob_start();
		echo "<html><head>";
		echo '<link rel="stylesheet" href="style.css" type="text/css"><link rel="stylesheet" href="includes/tablesort_theme/style.css" type="text/css"></head><body>';
		echo "<center>";
		echo "<br>";
		
		echo stripslashes($html);
		
		echo "</body></html>";
		
		
		
		$pdfContent = ob_get_contents();
		ob_end_clean();
		//ob_end_flush();
		
		
		
		$savedHtml = $use_filename . ".html";
		$savedFile = getcwd() . "\\temp\\" . $use_filename . ".pdf";		
		
		$fp = fopen(getcwd() . "/$savedHtml", "w");
                fwrite($fp, $pdfContent); 
                fclose($fp);


		if($header != '') {
			$savedHtml_header = $use_filename . "_header.html";
			
			$fp = fopen(getcwd() . "/$savedHtml_header", "w");
	                fwrite($fp, $header); 
	                fclose($fp);

		}
		
		if($footer != '') {
			$savedHtml_footer = $use_filename . "_footer.html";
			
			$fp = fopen(getcwd() . "/$savedHtml_footer", "w");
	                fwrite($fp, $footer); 
	                fclose($fp);

		}

		$theDoc = new COM("ABCpdf9.Doc") or die ("connection create fail");
		//$theDoc->AddImageHTML($pdfContent,0,0,0);


		$w = $theDoc->MediaBox->Width;
		$h = $theDoc->MediaBox->Height;
		$l = $theDoc->MediaBox->Left;
		$b = $theDoc->MediaBox->Bottom;

		
		if($display_mode == 1) {
			/* rotation code (to landscape) */
			
			$theDoc->Transform->Rotate(90,$l,$b);
			$theDoc->Transform->Translate($w,0);
			
			$theDoc->Rect->Width = $h;
			$theDoc->Rect->Height = $w;
		} else {
			// portrait
			$theDoc->Rect->Width = $w;
			$theDoc->Rect->Height = $h;
		}
		

		

		
		/* end of landscape rotation code */
		
		$theDoc->Rect->inset(15,15);
		/* create a variable that has the path (i.e. /demo/schedule_form_top.php would be '/demo/') */
		$sub_path = substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-strpos(strrev($_SERVER['PHP_SELF']),"/"));
		if($sub_path == '/') $sub_path = '';

		
		if(isset($_GET['html_only'])) {
			// output the link to the generated HTML, otherwise, print it as a PDF
			return "http://". $_SERVER['HTTP_HOST'] .$sub_path. $savedHtml;
		}
		
		if($header != '') $theDoc->Rect->String = "15 140 " . ($w - 15) ." 555";
		/* add the actual document to the PDF file */
		//die("<a href='".get_internal_address().$sub_path. $savedHtml."' target='new_window'>link</a>");
		$theID = $theDoc->AddImageURL(get_internal_address() .$sub_path. $savedHtml);

		$m=0;
		while (true) {
			//$theDoc->FrameRect;
			if(!$theDoc->Chainable($theID) || $m >= 1000) break;
			$m++;
			$theDoc->Page = $theDoc->AddPage();
			$theID = $theDoc->AddImageToChain($theID);
		}
		
		
		
		for($m=1;$m<=$theDoc->PageCount;$m++) {
			$theDoc->PageNumber = $m;
			if($header != '') {
				if($header != '') $theDoc->Rect->String = "15 555 " . ($w - 15) ." 775";
				$theDoc->AddImageURL("http://". get_internal_address() .$sub_path. $savedHtml_header);
				//$theDoc->FrameRect();
			}
			if($footer != '') {
				if($header != '') $theDoc->Rect->String = "15 25 " . ($w - 15) ." 140";
				$theDoc->AddImageURL("http://". get_internal_address() .$sub_path. $savedHtml_footer);
			}
			
			
			if($show_page_numbers) {
				$theDoc->Rect->String = "15 15 " . ($w - 15) ." 25";
				$theDoc->AddHtml("<p align='right'><font size='2' face='arial'>Page $m of ".$theDoc->PageCount."</font></p>");
			}
			
			$theDoc->Flatten();
		}

	
		/* Now, rotate the actual page */
		$theID = $theDoc->GetInfo($theDoc->Root,"Pages");
		
		if($display_mode == 1) {
			// rotate the page to landscape
			$theDoc->SetInfo($theID, "/Rotate","90");
		}
		
		/* save the doc */
		$theDoc->Save($savedFile);
		//echo $savedFile;
					
	   	
	   	unlink(getcwd(). "\\$savedHtml");
	   	if($header != '')	@unlink(getcwd(). "\\$savedHtml_header");
	   	if($footer != '')	@unlink(getcwd(). "\\$savedHtml_footer");
	   	
	   	return "./temp/".$use_filename . ".pdf";
	}

	function print_contents_include($use_filename, $html) {
		ob_start();
		echo "<html><head>";
		echo '<link rel="stylesheet" href="style.css" type="text/css"><link rel="stylesheet" href="includes/tablesort_theme/style.css" type="text/css"></head><body>';
		echo "<center>";
		echo "<br>";
		
		echo stripslashes($html);
		
		echo "</body></html>";
		
		
		
		$pdfContent = ob_get_contents();
		ob_end_clean();
		//ob_end_flush();
		
		
		$savedHtml = $use_filename . ".html";
		$savedFile = getcwd() . "\\temp\\" . $use_filename . ".pdf";
		
		
		$fp = fopen(getcwd() . "/$savedHtml", "w");
                fwrite($fp, $pdfContent); 
                fclose($fp);
								

		$theDoc = new COM("ABCpdf9.doc") or die ("connection create fail");
		//$theDoc->AddImageHTML($pdfContent,0,0,0);
		
		/* rotation code (to landscape) */
		

		
		$w = $theDoc->MediaBox->Width;
		$h = $theDoc->MediaBox->Height;
		$l = $theDoc->MediaBox->Left;
		$b = $theDoc->MediaBox->Bottom;
		
		//$theDoc->Transform->Rotate(90,$l,$b);
		//$theDoc->Transform->Translate($w,0);
		
		//$theDoc->Rect->Width = $h;
		//$theDoc->Rect->Height = $w;
		
		$theDoc->Rect->Width = $w;
		$theDoc->Rect->Height = $h;
		
		/* end of landscape rotation code */
		
		$theDoc->Rect->inset(15,15);
		/* create a variable that has the path (i.e. /demo/schedule_form_top.php would be '/demo/') */
		$sub_path = substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-strpos(strrev($_SERVER['PHP_SELF']),"/"));
		
		if(isset($_GET['html_only'])) {
			// output the link to the generated HTML, otherwise, print it as a PDF
			return "http://". $_SERVER['HTTP_HOST'] .$sub_path. $savedHtml;
		}
		
		
		/* add the actual document to the PDF file */
		$theID = $theDoc->AddImageURL("http://". $_SERVER['HTTP_HOST'] .$sub_path. $savedHtml);

		$m=0;
		while (true) {
			$theDoc->FrameRect;
			if(!$theDoc->Chainable($theID) || $m >= 1000) break;
			$m++;
			$theDoc->Page = $theDoc->AddPage();
			$theID = $theDoc->AddImageToChain($theID);
		}
		
		for($m=1;$m<$theDoc->PageCount;$m++) {
			$theDoc->PageNumber = $m;
			$theDoc->Flatten();
		}

		
		/* Now, rotate the actual page */
		$theID = $theDoc->GetInfo($theDoc->Root,"Pages");
		//$theDoc->SetInfo($theID, "/Rotate","90");
		
		/* save the doc */
		$theDoc->Save($savedFile);
		//echo $savedFile;
					
	   	
	   	unlink(getcwd(). "\\$savedHtml");
	   	
	   	return "./temp/".$use_filename . ".pdf";
	}

function query_string_remove($query_string,$removestring)

{



	

	$uQueryString = explode("&",$query_string);

	$query_string = '';

	

	

	foreach($uQueryString as $uVar)

	{

		

		if(preg_match("[^". $removestring ."]",$uVar) == 0)

			{

				

				$query_string .= "&".$uVar;	

			}

	}

	return substr($query_string,1);

}

function sort_fields($default_sort_field = "id", $force_update = false) {

	

	/* this function will create the session variable for the page that holds how we are sorting, and

	 * which direction (i.e. ascending, or descending) */

	 

	global $SCRIPT_NAME;

	global $page_name;



	





	$page_name = substr($SCRIPT_NAME,strpos($SCRIPT_NAME,"/") + 1);

	$page_name = substr($page_name,0,strpos($page_name,"."));

	





	if(!isset($_SESSION['sort_field_'.$page_name]) || $force_update) {

		$_SESSION['sort_field_'.$page_name] = $default_sort_field;

		$_SESSION['sort_field_direction_'.$page_name] = "desc";

	}



	if(isset($_GET['sort_field'])) {

		if($_SESSION['sort_field_'.$page_name] == $_GET['sort_field']) {

			

			/* switch sort direction */

			if($_SESSION['sort_field_direction_'.$page_name] == "asc") {

				$_SESSION['sort_field_direction_'.$page_name] = "desc";

			} else {

				$_SESSION['sort_field_direction_'.$page_name] = "asc";

			}

		} else {

			//echo "hit1";

			$_SESSION['sort_field_direction_'.$page_name] = "asc";

			$_SESSION['sort_field_'.$page_name] = $_GET['sort_field'];

		}

	}

	

}

function data_nav_bar($data_list, $record_start = 0,$records_per_page = 20) {

		/* 
		 * function to display the [previous] [next], page range, and search box on our data view pages
		*/
		global $query_string;
		
		if(!isset($_SESSION['results_per_page'])) {
			$_SESSION['results_per_page'] = $records_per_page;
		}
		
		$use_query_string = query_string_remove($query_string, "eid");
		$use_query_string = query_string_remove($use_query_string, "id");
		$use_query_string = query_string_remove($use_query_string, "search");
		$use_query_string = query_string_remove($use_query_string, "results_per_page");
		
		
		if(isset($_POST['sbox']) && $_POST['sbox'] != "") {
			$extra_search = "&search=$_POST[sbox]";
		} else {
			$extra_search = "";
		}
		
		/* calculate our top record for this set */
		$top_record = $record_start + $_SESSION['results_per_page'];
		if($top_record > mysqli_num_rows($data_list)) $top_record = mysqli_num_rows($data_list);
	?>
	<table class='standard12' width='100%'>
		<tr>
			<form action='?<?=$use_query_string?>' method='post'>
			<td width='1%'>
				&nbsp;&nbsp;&nbsp;
			</td>
			<td>
				<b>&nbsp;&nbsp;&nbsp;<?=mysqli_num_rows($data_list)?> record(s) |  viewing records <?=$record_start + 1?> - <?=$top_record?></b><br>
				<?
				if($record_start > 0) {
					$prev_no = $record_start - $_SESSION['results_per_page'];
					if($prev_no < 0) $prev_no = 0;
					echo "<a href='?$use_query_string&results_per_page=$_SESSION[results_per_page]&start_row=".$prev_no."$extra_search'>[Previous]</a>";
				} else {
					echo "[Previous]";
				}
				echo "&nbsp;&nbsp;&nbsp;";
				if(($record_start + $_SESSION['results_per_page']) < mysqli_num_rows($data_list)) {
					echo "<a href='?$use_query_string&results_per_page=$_SESSION[results_per_page]&start_row=".($record_start + $_SESSION['results_per_page'])."$extra_search'>[Next]</a>";
				} else {
					echo "[Next]";
				}
				?>				
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<b>Page:</b> 
				<?
					// only show a max of 15 page links, first 5, mid 5, last 5
					$max_links = 15;
					$links_per_section = 5;
					$page_count = mysqli_num_rows($data_list) / $_SESSION['results_per_page'];
					$page_middle = $page_count / 2;
					$max_dots = 5;
					$page_start = $record_start / $_SESSION['results_per_page'];
					
					for($i=0;$i < $page_count;$i++) {
						if($i < $links_per_section 
							|| ($i >= $page_middle && $i < ($page_middle + $links_per_section)) 
							|| $i > ($page_count - $links_per_section)
							|| ($i >= ($page_start - $links_per_section) && $i < ($page_start + $links_per_section))
							) {
							echo "<a href='?$use_query_string&results_per_page=$_SESSION[results_per_page]&start_row=".($i*$_SESSION['results_per_page'])."$extra_search'>[".($i + 1)."]</a> ";
							$dot_counter = 0;
						} else {
							if($dot_counter < $max_dots) {
								$dot_counter ++;
								echo " . ";
							}
						}

					}
				?>
				<br>
				<b>Search:</b>
				<input name="sbox" class='standard12'>
				<input type='submit' value='Go'>
				<?
					if(isset($_POST['sbox']) && $_POST['sbox'] != "") {
						echo "<br><b>Current Search Term: '$_POST[sbox]'";
					}
				?>
			</td>
			</form>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan='5'>
				<b>Records per page: </b>
				<a href='?results_per_page=20&<?=$use_query_string?><?=$extra_search?>'>[20]</a>
				<a href='?results_per_page=50&<?=$use_query_string?><?=$extra_search?>'>[50]</a>
				<a href='?results_per_page=100&<?=$use_query_string?><?=$extra_search?>'>[100]</a>
				<a href='?results_per_page=200&<?=$use_query_string?><?=$extra_search?>'>[200]</a>
				<a href='?results_per_page=9999&<?=$use_query_string?><?=$extra_search?>'>[all]</a>
			</td>
		</tr>
		</table>

<? }

function get_contact_name_by_id($contact_id) {
	// function to return contact's name by their ID
	
	$sql = "
		select *
		
		from inventory_customers
		where id = '$contact_id'
	";
	$data = simple_query($sql);
	
	$row = mysqli_fetch_array($data);
	
	return $row['company_name'];
}
function get_userid_by_name($username) {
	// function to return the user ID by passing the username
	
	$sql = "
		select id
		
		from users
		where username = '".sql_friendly($username)."'
			and deleted = 0
	";
	$data = simple_query($sql);
	
	if(!mysqli_num_rows($data)) {
		return 0;
	} else {
		$row = mysqli_fetch_array($data);
		return $row['id'];
	}
	
}
function login_check($username, $password, $inventory_login = 0) {
	// function to check the login and assign session vars
	
	if($username == '' || $password == '') return false;
	
	$sql = "
		select id,
			username
		
		from users
		where deleted = 0
			and active = 1
			and inventory_access = '".sql_friendly($inventory_login)."'
			and username = '".sql_friendly($username)."'
			and password = '".sql_friendly($password)."'
	";
	$data = simple_query($sql);
	
	if(mysqli_num_rows($data)) {
		// valid login, set the session vars
		
		$row = mysqli_fetch_array($data);
		
		$_SESSION['id'] = $row['id'];
		$_SESSION['user_id'] = $row['id'];
		$_SESSION['username'] = $row['username'];
		
		return true;
	}
	
	return false;
}


	function show_edi_dir() {
		global $defaultsarray;

		$ftp_dir = $defaultsarray['edi_ftp_upload'];
	
		$listDir = get_edi_files();
	

		
		
		if(!count($listDir)) {
		   	echo "No files";
		} else {
			echo "
				<table class='standard12' width='100%'>
				<tr>
					<td><b>Filename</b></td>
					<td align='right'><b>Size</b></td>
					<td align='right'><b>Date Received</b></td>
				</tr>
			";
			foreach($listDir as $file) {
				$fullfile = $ftp_dir."\\".$file;
				
				//$finfo = file($fullfile);
				$filesize = filesize($fullfile);
				
				$uploaded_datetime = filectime($fullfile);
				//echo "$file<br>";
				echo "
					<tr>
						<td><a href='javascript:void(0)' class='edi_link' filedata='$file'>$file</a></td>
						<td align='right'>".number_format($filesize)."</td>
						<td align='right'>".date("m/j/Y h:i a", $uploaded_datetime)."</td>
					</tr>
				";
			}
			echo "</table>";
		}
	}
	
	function show_edi_preview($filename) {
		// function to show a preview of the uploaded file
		global $defaultsarray;
		$ftp_dir = $defaultsarray['edi_ftp_upload'];
		$full_file = $ftp_dir."\\".$filename;
		if(!file_exists($full_file)) {
			echo "Cannot locate specified file";
			return;
		}
		
		$fcontents = file($full_file);
		
		$load_counter = 0;
		$load_entry_counter = 0;
		foreach($fcontents as $line_raw) {
			$line = substr($line_raw,0,-3);
			$linearray = explode("*",$line);
			/*
			foreach($linearray as $linepart) {
				echo $linepart." | ";
			}
			*/
			
			if($linearray[0] == 'TD3') {
				// rail car
				$load['railcar'] = $linearray[2].$linearray[3];
			}
			
			if($linearray[0] == 'BSN') {
				// Load Number
				$load['load_number'] = $linearray[2];
				$load['load_date'] = $linearray[3];
				$load['load_time'] = $linearray[4];
				//echo "<font color='red'>$line</font>";
				$load_counter++;
			}
			if(count($linearray) >= 3 && $linearray[0] == 'LIN' && $linearray[2] == 'IN') {
				$load_id = count($load) - 1;
				// line entry
				
				$load['entries'][$load_entry_counter]['roll_number'] = $linearray[3];
				$load_entry_counter++;
				//echo "<font color='red'>$line</font>";
			}
			if($linearray[0] == 'MEA' && $linearray[1] == 'WT' && $linearray[2] == 'GW') {
				if($load_entry_counter == 0) {
					// total weight
					$load['total_weight'] = $linearray[3] * 2.20462262; // convert kilograms to pounds;
				} else {
					$load['entries'][$load_entry_counter - 1]['weight'] = $linearray[3] * 2.20462262; // convert kilograms to pounds;
				}
				
				
			}
			//echo "<br>";
		}
		return $load;
		/*
		echo "<pre>";
		print_r($load);
		echo "</pre>";
		*/
	}
	
	function archive_edi($filename) {
		// function to move an EDI file to the archive location
		
		global $defaultsarray;
		
		rename($defaultsarray['edi_ftp_upload']."\\".$filename, $defaultsarray['edi_ftp_archive']."\\".$filename);
	}

	function display_xml_response($use_response, $error_number = 0, $error_msg = '') {
		
		$return_var = "<?xml version='1.0' encoding='utf-8' ?>
			<response>
				$use_response
			</response>
		";
		header('Content-Type: text/xml');
		
		echo $return_var;
	}

	function print_barcode_include($use_filename, $html, $height, $width, $browser_width) {
		ob_start();
		echo "<html><head>";
		echo '<link rel="stylesheet" href="style.css" type="text/css"></head><body>';
		
		echo stripslashes($html);
		
		echo "</body></html>";
		
		
		
		$pdfContent = ob_get_contents();
		ob_end_clean();
		//ob_end_flush();
		
		
		$savedHtml = $use_filename . ".html";
		$savedFile = getcwd() . "\\barcode_temp\\" . $use_filename . ".pdf";
		
		$fp = fopen(getcwd() . "/$savedHtml", "w");
	           fwrite($fp, $pdfContent); 
	           fclose($fp);
								
	
		$theDoc = new COM("ABCpdf9.doc") or die ("connection create fail");
		//$theDoc->AddImageHTML($pdfContent,0,0,0);
		
		/* rotation code (to landscape) */
		
	
		$theDoc->MediaBox->Width = $width;
		$theDoc->MediaBox->Height = $height;
		
		$w = $theDoc->MediaBox->Width;
		$h = $theDoc->MediaBox->Height;
		$l = $theDoc->MediaBox->Left;
		$b = $theDoc->MediaBox->Bottom;
		
		//echo "Width: $w | Height: $h";
		
		//$theDoc->Transform->Rotate(90,$l,$b);
		//$theDoc->Transform->Translate($w,0);
		
		//$theDoc->Rect->Width = $h;
		//$theDoc->Rect->Height = $w;
		
		$theDoc->Rect->Width = $w;
		$theDoc->Rect->Height = $h;
		$theDoc->FrameRect();
		
		/* end of landscape rotation code */

		$theDoc->Rect->inset(5,5);
		
		/* create a variable that has the path (i.e. /demo/schedule_form_top.php would be '/demo/') */
		$sub_path = substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-strpos(strrev($_SERVER['PHP_SELF']),"/"));
		
		if(isset($_GET['html_only'])) {
			// output the link to the generated HTML, otherwise, print it as a PDF
			return "http://". $_SERVER['HTTP_HOST'] .$sub_path. $savedHtml;
		}
		
		$theDoc->HtmlOptions->BrowserWidth = $browser_width;
		
		/* add the actual document to the PDF file */
		$theID = $theDoc->AddImageURL("http://". $_SERVER['HTTP_HOST'] .$sub_path. $savedHtml."?dummvar=".createuuid());
	
		$m=0;
		while (true) {
			$theDoc->FrameRect;
			if(!$theDoc->Chainable($theID) || $m >= 1000) break;
			$m++;
			$theDoc->Page = $theDoc->AddPage();
			$theID = $theDoc->AddImageToChain($theID);
		}
		
		for($m=1;$m<$theDoc->PageCount;$m++) {
			$theDoc->PageNumber = $m;
			$theDoc->Flatten();
		}
	
		//$theDoc->Rendering->SaveAlpha = true;
		
		/* Now, rotate the actual page */
		$theID = $theDoc->GetInfo($theDoc->Root,"Pages");
		$theDoc->SetInfo($theID, "/Rotate","90");
		
		/* save the doc */
		$theDoc->Save($savedFile);
		//echo $savedFile;
					
	   	
	   	unlink(getcwd(). "\\$savedHtml");
	   	
	   	return "./temp/".$use_filename . ".pdf";
	}

	if(!function_exists('money_format')) {
		function money_format($use_format = true, $money) {
			$money = money_strip($money);
			if($money == '' || !is_numeric($money)) $money = 0;
			
			if($money < 0) {
				if($use_format) {
					return "<span class='alert'>".number_format($money, 2)."</span>";
				} else {
					return number_format($money, 2);
				}
			} else {
				return number_format($money, 2);
			}
		}
	}

	function inventory_invoice_item_id($item_name, $supplier_id = 0) {
		// return the id of the inventory item
		
		$sql = "
			select *
			
			from inventory_invoice_items
			where internal_name = '".sql_friendly($item_name)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		$row['customer_price_each'] = 0;
		
		if($supplier_id > 0) {
			
			// see if we can locate a price for this supplier for this id
			$sql = "
				select iiic.price_each
				
				from inventory_invoice_items_customers iiic, inventory_customers ic
				where iiic.inventory_invoice_items_id = '$row[id]'
					and iiic.contract_id = ic.contract_id
					and ic.id = '".sql_friendly($supplier_id)."'
			";
			$data_price = simple_query($sql);
			if(mysqli_num_rows($data_price)) {
				$row_price = mysqli_fetch_array($data_price);
				if(is_numeric($row_price['price_each'])) $row['customer_price_each'] = $row_price['price_each'];
			}
		}
		
		return $row;
		
	}

	function send_xml($xml) {
		header('Content-Type: text/xml');
		echo "<?xml version='1.0' encoding='utf-8' ?>
			<response>";
		echo $xml;
			
		echo "
			</response>
		";
	}
	
	
	
	function calculate_pallets($date_from, $date_to, $supplier_id) {
		// take a date range, supplier and return the number of pallets in storage
		
		$sql_filter = "";
		global $defaultsarray;
		
		$sql = "
			select ic.id as supplier_id
			
			from inventory_entries ie, inventory_customers ic, inventory i
			where ie.linedate_added >= '".date("Y-m-d", strtotime($date_from))."'
				and ie.linedate_added < '".date("Y-m-d", strtotime("1 day", strtotime($date_to)))."'
				and i.supplier_id = ic.id
				and ie.deleted = 0
				and i.id = ie.item_id
				and ie.linedate_received > 0
				and i.supplier_id = '".sql_friendly($supplier_id)."'
		
			group by ic.id
			order by ic.company_name
		";
		$data = simple_query($sql);
		
		
		
		
		
	
	
		// get a list of the selected suppliers
		$sql = "
			select *
			
			from inventory_customers
			where supplier_flag = 1
				and deleted = 0
				and id = '".sql_friendly($supplier_id)."'
		";
		$data_suppliers = simple_query($sql);
		
		$total_units = 0;
		$total_pallets = 0;
		$incoming_units = 0;
		$outgoing_units = 0;
		$incoming_pallets = 0;
		$outgoing_pallets = 0;
		
		$return_var = "
			<table width='1000' class='standard12'>
			<tr>
				<td><b>Item</b></td>
				<td><b>Units Per Pack</b></td>
				<td><b>Units</b></td>
				<td><b>Running Total</b></td>
				<td><b>Incoming Units</b></td>
				<td><b>Incoming Pallets</b></td>
				<td><b>Outoing Units</b></td>
				<td><b>Outgoing Pallets</b></td>
			</tr>
		";
		while($row_suppliers = mysqli_fetch_array($data_suppliers)) { 
			$sql = "
				select i.*,
					ic.company_name
				
				from inventory i, inventory_customers ic
				where (i.supplier_id = '$row_suppliers[id]' or ic.supplier_id = '$row_suppliers[id]')
					and i.deleted = 0
					and i.supplier_id = ic.id
				
				order by i.item_id
			";
			$data_items = simple_query($sql);
			while($row_items = mysqli_fetch_array($data_items)) {
				$sql = "
					select ie.units,
						ie.linedate_added,
						'Incoming' as type_of_activity,
						0 as ship_to_id,
						ie.lot_serial,
						ie.reference,
						'' as po_number
					
					from inventory_entries ie, inventory i
					where ie.item_id = i.id
						and i.item_id = '".sql_friendly($row_items['item_id'])."'
						and ie.linedate_added < '".date('Y-m-d', strtotime("1 day", strtotime($_POST['date_to'])))."'
						and ie.deleted = 0
						and ie.linedate_received > 0
					
					union all 
					
					select -isi.qty as qty,
						inventory_shipping.linedate_added,
						'Outgoing' as type_of_activity,
						inventory_shipping.ship_to_id,
						ie.lot_serial,
						ie.reference,
						inventory_shipping.po_number
					
					from inventory_shipping, inventory_shipping_items isi, inventory_entries ie, inventory i
					where isi.inventory_shipping_id = inventory_shipping.id
						and isi.inventory_entries_id = ie.id
						and ie.item_id = i.id
						and ie.deleted = 0
						and inventory_shipping.deleted = 0
						and i.item_id = '".sql_friendly($row_items['item_id'])."'
						and inventory_shipping.linedate_added < '".date('Y-m-d', strtotime("1 day", strtotime($_POST['date_to'])))."'
						and ie.linedate_received > 0
					
					union all
					
					select ia.qty_adjust,
						ia.linedate_adjusted as linedate_added,
						concat('Inv. Adjustment: ',' ',ia.desc_short) as type_of_activity,
						0 as ship_to_id,
						ie.lot_serial,
						ie.reference,
						'' as po_number
					
					from inventory_adjustments ia, inventory_entries ie, inventory i
					where i.item_id = '".sql_friendly($row_items['item_id'])."'
						and ie.item_id = i.id
						and ia.deleted = 0
						and ie.deleted = 0
						and ia.inventory_entry_id = ie.id
						and ia.qty_adjust <> 0
						and ia.linedate_adjusted < '".date('Y-m-d', strtotime("1 day", strtotime($_POST['date_to'])))."'
						and ie.linedate_received > 0
						
					order by linedate_added
				";
				//die("<pre>$sql</pre>");
				$data_lineitems = simple_query($sql);
				$i = 0;
				$units = 0;
				
				$use_units_per_pack = calculate_units_per_pack($row_items['units_per_pack']);
				
				
				while($row_lineitems = mysqli_fetch_array($data_lineitems)) {
					if(strtotime($row_lineitems['linedate_added']) < strtotime($date_from)) {
						$units += $row_lineitems['units'];
	
					} else {
						
						if($i == 0) {
							// this is our first display for this item, so show the starting qty
							// opening qty $units
						}
						if($row_lineitems['type_of_activity'] == 'Incoming') {
							$incoming_units += $row_lineitems['units'];
							$incoming_pallets += $row_lineitems['units'] / $use_units_per_pack;
						} elseif($row_lineitems['type_of_activity'] == 'Outgoing') {
							$outgoing_units += $row_lineitems['units'];
							$outgoing_pallets += $row_lineitems['units'] / $use_units_per_pack;
						}
						$i++;
						$units += $row_lineitems['units'];
	
					}
				} // end while
				
				if($i == 0) {
					// no entries exist for this item for the time period specified, go ahead and show the opening inventory
					// opening inventory $units
				}
				$total_units += $units;
				$total_pallets += $units / $use_units_per_pack;

				$return_var .= "
					<tr>
						<td><font color='blue'>$row_items[item_id]</font></td>
						<td><font color='red'>$row_items[units_per_pack]</font></td>
						<td><font color='green'>$units</font></td>
						<td>".ceil($total_pallets)."</td>
						<td>$incoming_units</td>
						<td>".ceil($incoming_pallets)."</td>
						<td>$outgoing_units</td>
						<td>".ceil($outgoing_pallets)."</td>
					</tr>
					";
			} // end while
			
		} // end while
		
		$return_var .= "
			</table>
		";
		
		return ceil($total_pallets);
	} // end calculate_pallets functions
	
	
	
	function get_suppliers($supplier_id = 0) {
		
		// return the list of suppliers
		// if $supplier_id is specified, return the information for that specific supplier
		
		$sql_extra = '';
		
		if($supplier_id != 0) {
			$sql_extra = " and id = '".sql_friendly($supplier_id)."' ";
		}
		
		$sql = "
			select *
			
			from inventory_customers
			where deleted = 0
				and supplier_flag = 1
				$sql_extra
			order by company_name
		";
		$data = simple_query($sql);
		return $data;
	}
	
	function calculate_units_per_pack($units_per_pack) {
		if($units_per_pack > 0) {
			$use_units_per_pack = $units_per_pack;
		} else {
			$use_units_per_pack = 1;
		}
		
		return $use_units_per_pack;
	}
	
	function get_qty_by_date($inventory_entry_id, $date_to) {
		$sql = "
			select ie.units,
				ie.linedate_added,
				'Receiving                          ' as type_of_activity,
				0 as ship_to_id,
				ie.lot_serial,
				ie.reference,
				'' as po_number,
				i.units_per_pack
			
			from inventory_entries ie, inventory i
			where ie.item_id = i.id
				and ie.id = '".sql_friendly($inventory_entry_id)."'
				and ie.linedate_added < '".date('Y-m-d', strtotime("1 day", $date_to))."'
				and ie.deleted = 0
				and ie.linedate_received > 0
			
			union all 
			
			select -isi.qty as qty,
				inventory_shipping.linedate_added,
				'Whse ship order' as type_of_activity,
				inventory_shipping.ship_to_id,
				ie.lot_serial,
				ie.reference,
				inventory_shipping.po_number,
				i.units_per_pack
			
			from inventory_shipping, inventory_shipping_items isi, inventory_entries ie, inventory i
			where isi.inventory_shipping_id = inventory_shipping.id
				and isi.inventory_entries_id = ie.id
				and ie.item_id = i.id
				and ie.deleted = 0
				and inventory_shipping.deleted = 0
				and ie.id = '".sql_friendly($inventory_entry_id)."'
				and inventory_shipping.linedate_added < '".date('Y-m-d', strtotime("1 day", $date_to))."'
				and ie.linedate_received > 0
			
			union all
			
			select ia.qty_adjust,
				ia.linedate_adjusted,
				concat('Inv. Adjustment: ',' ',ia.desc_short) as type_of_activity,
				0 as ship_to_id,
				ie.lot_serial,
				ie.reference,
				'' as po_number,
				i.units_per_pack
			
			from inventory_adjustments ia, inventory_entries ie, inventory i
			where ie.id = '".sql_friendly($inventory_entry_id)."'
				and ie.item_id = i.id
				and ia.deleted = 0
				and ie.deleted = 0
				and ia.inventory_entry_id = ie.id
				and ia.qty_adjust <> 0
				and ia.linedate_adjusted < '".date('Y-m-d', strtotime("1 day", $date_to))."'
				and ie.linedate_received > 0
				
			order by linedate_added
		";
		$data = simple_query($sql);
		
		if(!mysqli_num_rows($data)) {
			return 0;
		} else {
			$units = 0;
			while($row = mysqli_fetch_array($data)) {
				$units += $row['units'];
			}
			
			return $units;
		}
	}
	
	function note_section($note_type_id) {
		// function to create and display notes for the specified type id (i.e. customers, trailer, drivers, etc...)
		
		$sql = "
			select *
			
			from notes_main
			where note_type_id = '".sql_friendly($note_type_id)."'
				and deleted = 0
				and access_level=0
			order by linedate_added desc
		";
		$data = simple_query($sql);
		
		$return_var = "
			<table class='note_area'>
		";
		
		if(!mysqli_num_rows($data)) {
			$return_var .= "
				<tr>
					<td>No notes</td>
				</tr>
			";
		} else {
			while($row = mysqli_fetch_array($data)) {
				$return_var .= "
					
				";
			}
		}
		$return_var .= "</table>";
		
		return $return_var;
	}
	
	function get_upcoming_events() {
		$result_limit = 10;
		$future_time = "30 day";
	
		/*
			select linedate_started,
				concat('Date of Hire: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers 
			where month(linedate_started) in (month(now()),".date("m", strtotime($future_time, time())).")
				and deleted = 0
				and active = 1
			
			union 
		*/
		
		/*
		$sql = "
			select linedate_birthday as linedate,				
				concat('Birthday: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
				0 as calendar_id
				
			from drivers 
			where month(linedate_birthday) in (month(now()),".date("m", strtotime($future_time, time())).")
				and deleted = 0
				and active = 1
			
			union
			
			select linedate_start,
				concat('Unavailable: <a href=\"admin_drivers.php?id=',drivers.id,'\" target=\"view_driver_',drivers.id,'\">', name_driver_first, ' ', name_driver_last,'</a> ',date_format(linedate_start, '%b %e'), ' - ', date_format(linedate_end, '%b %e')),
				0
				
			from drivers, drivers_unavailable
			where drivers.deleted = 0
				and drivers.active = 1
				and drivers_unavailable.deleted = 0
				and drivers.id = drivers_unavailable.driver_id
				and drivers_unavailable.linedate_end >= '".date("Y-m-d")."'
			
			union 
			
			select linedate_drugtest,
				concat('Physical due for: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_drugtest <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and linedate_drugtest > 0
				and deleted = 0
				and active = 1
			
			union 
			
			select linedate_license_expires,
				concat('License Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_license_expires > 0
				and linedate_license_expires <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				and active = 1
				
			union 
			
			select linedate,
				desc_short,
				id
			
			from calendar
			where linedate > 0
				and linedate <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				
			order by month(linedate), day(linedate)
			
			limit $result_limit
		";
		$data = simple_query($sql);
		
		$return_var = "<div style='font-size:12px;font-family:arial;width:420px'>";
		while($row = mysqli_fetch_array($data)) {
			$return_var .= "
				<div style='width:80px;float:left;text-align:left'>
					".date("M, j", strtotime($row['linedate'])).
					($row['calendar_id'] ? " <a href='javascript:delete_event($row[calendar_id])'><img src='images/delete.gif' title='Delete Event' alt='Delete Event' style='border:0;float:right'></a>" : '').
					($row['calendar_id'] ? " <a href='javascript:edit_event($row[calendar_id])'><img src='images/edit_small.png' title='Edit Event' alt='Edit Event' style='border:0;float:right'></a>" : '')."
				</div>
				<div style='float:left;width:330px'>".$row['c_reason']."&nbsp;</div> 
			";
		}
		$return_var .= "</div>";
		*/
		
		
		$sql = "
			select linedate_birthday as linedate,				
				concat('Birthday: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
				0 as calendar_id
				
			from drivers 
			where DATE_FORMAT(linedate_birthday,'%m-%d') <= '".date("m-d", strtotime("+5 day", time()))."' 
				and DATE_FORMAT(linedate_birthday,'%m-%d')  >= '".date("m-d", strtotime("-3 day", time()))."' 				
				and deleted = 0
				and active = 1
			
			union
						
			select linedate_drugtest,
				concat('Physical due for: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_drugtest <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and linedate_drugtest > 0
				and deleted = 0
				and active = 1
			
			union 
			
			select linedate_license_expires,
				concat('License Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_license_expires > 0
				and linedate_license_expires <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				and active = 1
							
			order by month(linedate), day(linedate)
			
			limit $result_limit
		";
		$data = simple_query($sql);
		
		$sql2 = "
			select linedate_start as linedate,
				linedate_end as linedate_to,
				driver_id as driver,
				concat('Unavailable: <a href=\"admin_drivers.php?id=',drivers.id,'\" target=\"view_driver_',drivers.id,'\">', name_driver_first, ' ', name_driver_last,'</a> ',date_format(linedate_start, '%b %e'), ' - ', date_format(linedate_end, '%b %e'),': ',reason_unavailable) as c_reason,
				reason_unavailable as c_reason2,
				1 as from_calendar,
				drivers_unavailable.id as calendar_id
				
			from drivers, drivers_unavailable
			where drivers.deleted = 0
				and drivers.active = 1
				and drivers_unavailable.deleted = 0
				and drivers.id = drivers_unavailable.driver_id
				and drivers_unavailable.linedate_end >= '".date("Y-m-d")."'
			
			union 
						
			select linedate,
				'0000-00-00 00:00:00',
				0,
				desc_short,
				'',
				0 as from_calendar,				
				id
			
			from calendar
			where linedate > 0
				and linedate <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				
			order by month(linedate), day(linedate)
			
			limit $result_limit
		";
		$data2 = simple_query($sql2);
		
		//build events and time off calendar lists separately...
		$return_var1="";
		$return_var2="";
		
		$return_var1 = "<table border='0' width='420'>";
		while($row = mysqli_fetch_array($data)) {
			
			$return_var1 .= "					
					<tr>
						<td valign='top' width='80'>
							".date("M, j", strtotime($row['linedate'])).
							($row['calendar_id'] ? " <a href='javascript:delete_event($row[calendar_id])'><img src='images/delete.gif' title='Delete Event' alt='Delete Event' style='border:0;float:right'></a>" : '').
							($row['calendar_id'] ? " <a href='javascript:edit_event($row[calendar_id])'><img src='images/edit_small.png' title='Edit Event' alt='Edit Event' style='border:0;float:right'></a>" : '')."
						</td>
						<td valign='top' width='330'>".$row['c_reason']."&nbsp;</td>
					</tr>
			";
		}
		$return_var1 .= "</table>";
		
		$return_var2 = "<table border='0' width='420'>";
		while($row2 = mysqli_fetch_array($data2)) {
			
			if(isset($row2['from_calendar']) && (int) $row2['from_calendar']==0)
			{
				$return_var2 .= "					
					<tr>
						<td valign='top' width='80'>".date("M, j", strtotime($row2['linedate'])).
							($row2['calendar_id'] ? " <a href='javascript:delete_event($row2[calendar_id])'><img src='images/delete.gif' title='Delete Event' alt='Delete Event' style='border:0;float:right'></a>" : '').
							($row2['calendar_id'] ? " <a href='javascript:edit_event($row2[calendar_id])'><img src='images/edit_small.png' title='Edit Event' alt='Edit Event' style='border:0;float:right'></a>" : '')."</td>
						<td valign='top' width='330'>Scheduled Event: <b>".$row2['c_reason']."</b>&nbsp;</td>
					</tr>
				";
			}
			else
			{
				//add editing to Unavailable Driver on load board only.
				$tmp_reason=trim($row2['c_reason2']);
				$tmp_reason=str_replace("'","",$tmp_reason);	//&apos;
				$tmp_reason=str_replace('"',"",$tmp_reason);	//&quot;
				
				$tmp_from=date("m/d/Y",strtotime($row2['linedate']));
				$tmp_to=date("m/d/Y",strtotime($row2['linedate_to']));
				
				$link_edit="<span class='mrr_link_like_on' onClick='mrr_edit_driver_unavailable(".$row2['calendar_id'].",".$row2['driver'].",\"".$tmp_from."\",\"".$tmp_to."\",\"".$tmp_reason."\");'><b>Unavailable</b></span>";
				
				$row2['c_reason']=str_replace("Unavailable",$link_edit,$row2['c_reason']);
				
				$return_var2 .= "					
					<tr>
						<td valign='top' width='80'>".date("M, j", strtotime($row2['linedate'])).
							($row2['calendar_id'] ? " <a href='javascript:delete_driver_unavailable($row2[calendar_id])'><img src='images/delete.gif' title='Delete Notice' alt='Delete Notice' style='border:0;float:right'></a>" : '')."</td>
						<td valign='top' width='330'>".$row2['c_reason']."&nbsp;</td>
					</tr>
				";	
			}
			
				
		}
		$return_var2 .= "</table>";
		
		//now put lists together
		$return_var = "<table border='0' width='840'>";
		$return_var .= "					
			<tr>
				<td valign='top' width='50%'><div class='section_heading'>Events <a href='javascript:edit_event(0)'><img src='images/add.gif' style='border:0'> Add new event</a></div></td>
				<td valign='top' width='50%'><div class='section_heading'>Time Off</div></td>
			</tr>
			<tr>
				<td valign='top'>".$return_var1."</td>
				<td valign='top'>".$return_var2."</td>
			</tr>
			";
		$return_var .= "</table>";
		
		return $return_var;
	}
	
	function money_strip($amount) {
		// function to strip "$" and "," from currency fields
		$money_fixed = str_replace("%","",str_replace(",","",str_replace("$", "", $amount)));
		if($money_fixed == '') $money_fixed = 0;
		return $money_fixed;
	}	
	
	function field_exists($use_table, $use_field,$db="") {
		global $datasource;	
		
		$fieldname = $use_field;
		$table = $use_table;
		$fieldexists = false;
		$result = mysqli_query("SHOW FIELDS FROM `".$db."$table`",$datasource) or die("Error in Query");
		while ($record = mysqli_fetch_array($result)) {
			if (strtolower($record['Field']) == $fieldname) {
				$fieldexists = true;
				break;
			}
		}
	
		return $fieldexists;
	}
	
	function table_exists($tablename,$db="") {
		global $datasource;
		
	     $exists = mysqli_query("SELECT 1 FROM ".$db."$tablename LIMIT 0", $datasource);
	     if ($exists) return true;
	     return false;
	}
	
	function update_version($version_no) {
		// writes the new version number to the database
		global $datasource;
		
		$sql = "
			update defaults
			set xvalue_string = '$version_no'
			where xname = 'version'
		";
		$data_version_update = simple_query($sql);
	}
	
	function time_prep($use_datetime) {
		// function to get and format the time from a datetime (mysql) field
		
		$use_time = "";
		if(strtotime($use_datetime) && date("H", strtotime($use_datetime)) >= 0) $use_time = date("H:i", strtotime($use_datetime));	//
		
		return $use_time;
	}
	
	function build_option_box($option_cat_name, $selected_value = "", $field_name, $show_name = false,$mrr_combo=false) {
		$data = get_options($option_cat_name);
		
		echo "<select name='$field_name' id='$field_name'>";
		
		if(mysqli_num_rows($data)) {
			$row = mysqli_fetch_array($data);
			if($row['blank_text'] != '') {
				echo "<option value='0'>$row[blank_text]</option>";
			}
			mysqli_data_seek($data, 0);
			while($row = mysqli_fetch_array($data)) {
				$use_selected = "";
				if($row['id'] == $selected_value) 		$use_selected = "selected";
				
				if(!$mrr_combo)
				{
					$disp_name = "";
					if($show_name) $disp_name = "($row[fname]) ";
					
					echo "<option $use_selected value='$row[id]'>$disp_name $row[fvalue]</option>";
				}
				else
				{
					$disp_name = "$row[fvalue]";
					if($show_name) $disp_name = "$row[fname] ";
					
					echo "<option $use_selected value='$row[id]'>$disp_name</option>";	
				}
				
			}
		}
		echo "</select>";
	} 
	function mrr_build_option_box($option_cat_name, $selected_value = "", $field_name, $show_name = false,$mrr_combo=false,$java="") {
		$data = get_options($option_cat_name);
		
		$tab="";
		
		$tab.="<select name='$field_name' id='$field_name'".$java.">";
		
		if(mysqli_num_rows($data)) {
			$row = mysqli_fetch_array($data);
			if($row['blank_text'] != '') {
				$tab.="<option value='0'>$row[blank_text]</option>";
			}
			mysqli_data_seek($data, 0);
			while($row = mysqli_fetch_array($data)) {
				$use_selected = "";
				if($row['id'] == $selected_value) 		$use_selected = "selected";
				
				if(!$mrr_combo)
				{
					$disp_name = "";
					if($show_name) $disp_name = "($row[fname]) ";
					
					$tab.="<option $use_selected value='$row[id]'>$disp_name $row[fvalue]</option>";
				}
				else
				{
					$disp_name = "$row[fvalue]";
					if($show_name) $disp_name = "$row[fname] ";
					
					$tab.="<option $use_selected value='$row[id]'>$disp_name</option>";	
				}
				
			}
		}
		$tab.="</select>";
		return $tab;
	} 


	function option_exists($option_cat_name, $option_name) {
		$sql = "
			select id
			
			from option_values
			where fname = '".sql_friendly($option_name)."'
				and cat_id = '".sql_friendly(get_option_cat_id($option_cat_name))."'
		";
		$data = simple_query($sql);
		
		return mysqli_num_rows($data);
	}
	function option_exists_mrr_alt($option_cat_name, $option_name) {
		$sql = "
			select id
			
			from option_values
			where fname = '".sql_friendly($option_name)."'
				and cat_id = '".sql_friendly(get_option_cat_id($option_cat_name))."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		return $row['id'];
	}
	
	function get_option_name_by_id($option_id) {
		$sql = "
			select
				fname
			
			from option_values
			where id = '".sql_friendly($option_id)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		return $row['fname'];
	}
	function get_option_value_by_id($option_id) {
		$sql = "
			select
				fvalue
			
			from option_values
			where id = '".sql_friendly($option_id)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		return $row['fvalue'];
	}

	function get_option_value($option_cat_name, $option_name, $sub_option = false, $return_id = false) {
		// sub_option returns the dummy_val, instead of the normal val
		
		$sql = "
			select *
			
			from option_values
			where cat_id = '".get_option_cat_id($option_cat_name)."'
				and fname = '".sql_friendly($option_name)."'				
		";
		$data = simple_query($sql);
				
		if(!mysqli_num_rows($data)) {
			return false;
		} else {
			$row = mysqli_fetch_array($data);
		
			if($return_id) {
				return $row['id'];
			} else if($sub_option) {
				return $row['dummy_val'];
			} else {
				return $row['fvalue'];
			}
		}
	}

	function get_options($option_cat_name) {
		$sql = "
			select option_values.*,
				option_cat.blank_text
			
			from option_values, option_cat
			where option_values.cat_id = '".sql_friendly(get_option_cat_id($option_cat_name))."'
				and option_values.deleted = 0
				and option_values.cat_id = option_cat.id

			order by option_values.zorder, option_values.fvalue
		";
		$data = simple_query($sql);
		
		return $data;
	}	
	
	function get_option_cat_id($cat_name) {
		$sql = "
			select id
			
			from option_cat
			where cat_name = '".sql_friendly($cat_name)."'
				and deleted = 0
		";
		$data = simple_query($sql);
		if(!mysqli_num_rows($data)) {
			return 0;
		} else {
			$row = mysqli_fetch_array($data);
			return $row['id'];
		}
	}
	function mrr_select_box_for_options($option_cat_name,$field_name,$sel_id,$prompter,$extra="",$cd=0)
	{
		$sql="
			select id as use_val,fvalue as use_disp,fname as use_disp2
				from option_values 
				where cat_id='".sql_friendly(get_option_cat_id($option_cat_name))."'
					 and deleted='0' 
				order by id asc
			";
		$mrr_box=mrr_select_box_disp($sql,$field_name,$sel_id,$prompter,$extra,$cd);
		return $mrr_box;	
	}
	function mrr_select_box_disp($sql,$form_name,$selected_val = 0,$default_text = '',$extra_params = '',$cd=0) 
	{
		// function to display a select box based on a query and other vars
		// SQL must return two vars, one named use_val and one named use_disp.  is_active used if CD==1.
		$data = simple_query($sql);
		$return_var = "<select id='$form_name' name='$form_name' $extra_params>";
		if($default_text != '') 
		{
			$return_var .= "<option value='0'>$default_text</option>";
		}
		while($row = mysqli_fetch_array($data))
		{
			$use_selected = "";
			if($row['use_val'] == $selected_val)	$use_selected = " selected";
			
			$mrr_styler="";
			$mrr_adder="";
			if($cd > 0 && $cd!=2)
			{
				if((int) $row['is_active'] == 0)	
				{
					$mrr_styler=" style='color:#999999;'";
					$mrr_adder=" - (Inactive)";
				}
			}
			
			$use_display=trim($row['use_disp']);
			if($cd==2)	$use_display=trim($row['use_disp2']);
			
			$return_var .= "<option value='".$row['use_val']."'".$use_selected."".$mrr_styler.">".$use_display."".$mrr_adder."</option>";
		}		
		$return_var .= "</select>";
		
		return $return_var;
	}
	function mrr_get_option_fvalue_by_id($option_id,$cd=0) {
		$sql = "
			select
				fvalue,dummy_val
			
			from option_values
			where id = '".sql_friendly($option_id)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		if($cd==1)		return trim($row['dummy_val']);
		
		return $row['fvalue'];
	}
	function mrr_get_option_fname_by_id($option_id) {
		$sql = "
			select fname			
			from option_values
			where id = '".sql_friendly($option_id)."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		return $row['fname'];
	}
	
	function mrr_dispatch_completion_updates($load_id=0,$run_updater=0)
     {
          $tab="";
          $sql="
          	select load_handler_stops.*,
          		(select trucks_log.dispatch_completed from trucks_log where trucks_log.id=load_handler_stops.trucks_log_id) as disp_complete  
          	from load_handler_stops 
          	where load_handler_stops.deleted=0
          		and load_handler_stops.trucks_log_id>0 
          		and load_handler_stops.load_handler_id>0
          		".($load_id > 0 ? " and load_handler_id='".sql_friendly($load_id)."'" : "")."
          		and load_handler_stops.linedate_pickup_eta>='".date("Y-m-d",strtotime("-7 days",time()))." 00:00:00'
          		and (load_handler_stops.linedate_completed='0000-00-00 00:00:00' or load_handler_stops.linedate_completed is NULL or load_handler_stops.linedate_completed=0)
          	order by load_handler_stops.load_handler_id,load_handler_stops.trucks_log_id,load_handler_stops.id
          ";
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
          {
          	$comp_date="NULL";
          	if($row['linedate_completed']=='0000-00-00 00:00:00')		$comp_date="0000-00-00 00:00:00";
          	
          	$tab.="<br>".$row['load_handler_id'].": ".$row['trucks_log_id']." Stop ".$row['id']." shows Completion Date is <b>".$comp_date."</b>. <span style='color:green;'>".($row['disp_complete'] > 0 ? "Complete" : "Active")."</span>";
          	if($row['disp_complete'] > 0)
          	{
          		$sql="
          			update trucks_log set
               			dispatch_completed = '0'
               		where id='".sql_friendly($row['trucks_log_id'])."'
          		";
          		simple_query($sql);
          		$tab.=" <span style='color:red;'>...UPDATED</span>";
          	}
          	
          	if($run_updater > 0)		update_origin_dest($row['load_handler_id']);          	
          }         
                    
          $tab.="<br><hr><br><b>Dispatches to flag completed.</b><br>";
          
          //now set all dispatches that have no un-completed stops left to completed.
          $sql="
          	select trucks_log.*,
          		(
               		select count(*) 
               		from load_handler_stops 
               		where load_handler_stops.deleted =0
               			and load_handler_stops.trucks_log_id=trucks_log.id
               			and (load_handler_stops.linedate_completed='0000-00-00 00:00:00' or load_handler_stops.linedate_completed is NULL or load_handler_stops.linedate_completed=0)
          		) as stop_count 
          	from trucks_log 
          	where trucks_log.deleted=0
          		and trucks_log.dispatch_completed=0
          		and trucks_log.linedate_pickup_eta>='".date("Y-m-d",strtotime("-7 days",time()))." 00:00:00'
          		".($load_id > 0 ? " and trucks_log.load_handler_id='".sql_friendly($load_id)."'" : "")."          		
          ";
          $data=simple_query($sql);
          while($row=mysqli_fetch_array($data))
          {
          	$tab.="<br>".$row['load_handler_id'].": ".$row['id']." Stops left ".$row['stop_count'].". <span style='color:green;'>".($row['stop_count'] > 0 ? "Incomplete" : "Finished")."</span>";
          	if($row['stop_count'] == 0)
          	{
          		$sql="
          			update trucks_log set
               			dispatch_completed = '1'
               		where id='".sql_friendly($row['id'])."'
          		";
          		simple_query($sql);
          		$tab.=" <span style='color:red;'>...UPDATED</span>";
          	}          	      	
          }  
                    
          return $tab;
     }
	
	function update_origin_dest($load_id,$skip_pcmiler=0) 
	{		
		// make sure we have a valid load ID
		if(!is_numeric($load_id) || $load_id == '' || $load_id == 0) 	return false;
		
		$total_dispatch_cost = 0;
		
		// get all the dispatches for this load
		$sql = "
			select *			
			from trucks_log
			where load_handler_id = '".sql_friendly($load_id)."'
				and deleted = 0
		";
		$data_dispatchs = simple_query($sql);
		
		// if there are any dispatches, then make sure the 'preplan' flag is unchecked for this load
		if(mysqli_num_rows($data_dispatchs)) 
		{
			$sql = "
				update load_handler set
				    preplan = 0,
					load_available = 0
				where id = '".sql_friendly($load_id)."'
			";
			simple_query($sql);
		} 
		else 
		{
			// no dispatches, so this is an 'available' load
			$sql = "
				update load_handler set
				    load_available = 1
				where id = '".sql_friendly($load_id)."'
			";
			simple_query($sql);
		}
		
		$geotab_shipment_log_id="";
		
		while($row_dispatch = mysqli_fetch_array($data_dispatchs)) 
		{
			$dispatch_id = $row_dispatch['id'];
			// update the origin city/state
			
			$sql = "
				select *				
				from load_handler_stops
				where trucks_log_id = '".sql_friendly($dispatch_id)."'
					and deleted = 0
				order by linedate_pickup_eta
				limit 1
			";
			$data = simple_query($sql);
			
			if(mysqli_num_rows($data)) 
			{
				$row = mysqli_fetch_array($data);
	
				$use_city = $row['shipper_city'];
				$use_state = $row['shipper_state'];
				$use_linedate_pickup_eta = date("Y-m-d", strtotime($row['linedate_pickup_eta']));
				
				if(trim($row['geotab_shipment_log_id'])!="")		$geotab_shipment_log_id=trim($row['geotab_shipment_log_id']);
			} 
			else 
			{
				$use_city = '';
				$use_state = '';
				$use_linedate_pickup_eta = '0000-00-00';
			}
			
			$sql = "
				update trucks_log set
					origin = '".sql_friendly($use_city)."',
					origin_state = '".sql_friendly($use_state)."',
					linedate_pickup_eta = '$use_linedate_pickup_eta'						
				where id = '".sql_friendly($dispatch_id)."'
			";
			simple_query($sql);
						
			// update the dest city/state
			$sql = "
				select *				
				from load_handler_stops
				where trucks_log_id = '".sql_friendly($dispatch_id)."'
					and deleted = 0
				order by linedate_pickup_eta desc
				limit 1
			";
			$data = simple_query($sql);
			
			if(mysqli_num_rows($data)) 
			{
				$row = mysqli_fetch_array($data);
	
				$use_city = $row['shipper_city'];
				$use_state = $row['shipper_state'];
				$use_linedate_dropoff_eta = date("Y-m-d", strtotime($row['linedate_pickup_eta'])); // we use the linedate_pickup_eta for both pickup/dropoff - confusing, I know....
				
				if(trim($row['geotab_shipment_log_id'])!="")		$geotab_shipment_log_id=trim($row['geotab_shipment_log_id']);
			} 
			else 
			{
				$use_city = '';
				$use_state = '';
				$use_linedate_dropoff_eta = '0000-00-00';
			}
			
			/*
			$sql = "
				update trucks_log
				set daily_cost = '$res_daily_cost[daily_cost]'
				where id = '$dispatch_id'
				limit 1
			";
			simple_query($sql); 
			*/
			
			$dispatch_cost = get_dispatch_cost($dispatch_id);
			$total_dispatch_cost += $dispatch_cost;
			
			$sql = "
				update trucks_log set
					destination = '".sql_friendly($use_city)."',
					destination_state = '".sql_friendly($use_state)."',
					cost = '".$dispatch_cost."',
					linedate_dropoff_eta = '$use_linedate_dropoff_eta'						
				where id = '".sql_friendly($dispatch_id)."'
			";
			simple_query($sql);
						
			// update the dispatch 'linedate' with whichever the next stop that isn't completed is
			$sql = "
				select *				
				from load_handler_stops
				where trucks_log_id = '".sql_friendly($dispatch_id)."'
					and deleted = 0
					and (linedate_completed = 0 or linedate_completed is null)
				order by linedate_pickup_eta
				limit 1
			";
			$data_next_stop = simple_query($sql);
			
			if(!mysqli_num_rows($data_next_stop) && mysqli_num_rows($data)) 
			{
				// there are no more stops for the dispatch, mark it completed
				
				// make sure there were at least some stops for this dispatch, if there weren't any stops, then don't mark
				// it as completed, as it's possible the user simply hasn't input any yet
				$sql = "
					select *					
					from load_handler_stops
					where trucks_log_id = '".sql_friendly($dispatch_id)."'
						and deleted = 0
					order by linedate_completed desc
					limit 1
				";
				$data_count = simple_query($sql);
				
				if(mysqli_num_fields($data_count)) 
				{
					$dispatch_completed = 1;
					$row_count = mysqli_fetch_array($data_count);
					$use_linedate = $row_count['linedate_completed'];
					
					mrr_set_geotab_shipment_log($geotab_shipment_log_id,"");		//complete the GeoTab ShipmentLog... if there is one.
				} 
				else 
				{
					$dispatch_completed = 0;
					$use_linedate = 0;
				}

				$sql = "
					update trucks_log set
						dispatch_completed = '".sql_friendly($dispatch_completed)."',
						".($dispatch_completed == 1 ? " has_load_flag = '0', " : "")."
						linedate = '".($use_linedate > 0 ? date("Y-m-d", strtotime($use_linedate)) : '0000-00-00')."'
					where id = '".sql_friendly($dispatch_id)."'
				";
				simple_query($sql);
				
			} else {
				$row_next_stop = mysqli_fetch_array($data_next_stop);
				$sql = "
					update trucks_log set
						linedate = '".date("Y-m-d", strtotime($row_next_stop['linedate_pickup_eta']))."',
						dispatch_completed = 0
					where id = '".sql_friendly($dispatch_id)."'
				";
				simple_query($sql);
			}			
		}

		// get the first stop of the load and set the load origin to be that
		$sql = "
			select *			
			from load_handler_stops
			where load_handler_id = '".sql_friendly($load_id)."'
				and deleted = 0
			order by linedate_pickup_eta, id
			limit 1
		";
		$data_start = simple_query($sql);
		if(mysqli_num_rows($data_start)) 
		{
			$row_start = mysqli_fetch_array($data_start);
			$use_city = $row_start['shipper_city'];
			$use_state = $row_start['shipper_state'];
			$linedate_dropoff_eta = date("y-m-d H:i:s", strtotime($row_start['linedate_pickup_eta']));
		} 
		else 
		{
			$use_city = '';
			$use_state = '';
			$linedate_dropoff_eta = '0000-00-00';
		}
		
		$sql = "
			update load_handler set
				origin_city = '".sql_friendly($use_city)."',
				origin_state = '".sql_friendly($use_state)."',
				linedate_pickup_eta = '$linedate_dropoff_eta'
			where id = '".sql_friendly($load_id)."'
		";
		simple_query($sql);
		
		// get the last of the load and set the load destination to be that
		$sql = "
			select *			
			from load_handler_stops
			where load_handler_id = '".sql_friendly($load_id)."'
				and deleted = 0
			order by linedate_pickup_eta desc, id desc
			limit 1
		";
		$data_start = simple_query($sql);
		if(mysqli_num_rows($data_start)) 
		{
			$row_start = mysqli_fetch_array($data_start);
			$use_city = $row_start['shipper_city'];
			$use_state = $row_start['shipper_state'];
			$linedate_dropoff_eta = date("y-m-d H:i:s", strtotime($row_start['linedate_pickup_eta']));
		} 
		else 
		{
			$use_city = '';
			$use_state = '';
			$linedate_dropoff_eta = '0000-00-00';
		}
		
		$sql = "
			select customer_id,
				actual_bill_customer,
				actual_fuel_surcharge_per_mile				
			from load_handler
			where id = '".sql_friendly($load_id)."'
		";
		$data_cust = simple_query($sql);
		$row_cust = mysqli_fetch_array($data_cust);
				
		// make sure the customer_id on the turcks_log page matches the driver_id of the load_handler
		$sql = "
			update trucks_log set
				customer_id = '".sql_friendly($row_cust['customer_id'])."'
			where load_handler_id = '".sql_friendly($load_id)."'
		";
		simple_query($sql);
		
		
		// get the variable expenses for this load
		$sql = "
			select sum(expense_amount) as total_var_expenses			
			from load_handler_actual_var_exp
			where load_handler_id = '".sql_friendly($load_id)."'
		";
		$data_var_exp = simple_query($sql);
		$row_var_exp = mysqli_fetch_array($data_var_exp);
		
		//$total_load_cost = $total_dispatch_cost + $row_var_exp['total_var_expenses'];
		$total_load_cost = $total_dispatch_cost;
		
		$sql = "
			update load_handler set
				actual_total_cost = '".$total_load_cost."',
				dest_city = '".sql_friendly($use_city)."',
				dest_state = '".sql_friendly($use_state)."',
				linedate_dropoff_eta = '$linedate_dropoff_eta'				
			where id = '".sql_friendly($load_id)."'
		";
		simple_query($sql);		

		
		// loop through all our stops, generate the miles per leg, and the total dispatch miles
		$sql = "
			select *			
			from load_handler_stops
			where load_handler_id = '".sql_friendly($load_id)."'
				and deleted = 0
			order by linedate_pickup_eta asc, id desc
		";
		$data_stops = simple_query($sql);
		
		//Conard in LeVergne, TN
		$prev_lat = "36.001156";
		$prev_long = "-86.597328";
		$prev_zip = "37086";		
		$prev_city_state = "";
		$last_dispatch_id = 0;
		$pcm_miles_total = 0;
		$calc_errors="";
		$mcntr=0;
		
		if($skip_pcmiler==0)
		{				
     		$pcm = new COM("PCMServer.PCMServer") or die ("connection create fail");
     		$pcm_miles = 0;
     		$city_state="";	
     		while($row_stop = mysqli_fetch_array($data_stops)) 
     		{		
     			if($mcntr > 0)
     			{
          			$city_state = "$row_stop[shipper_city]".($row_stop['shipper_state'] != '' ? ', '.$row_stop['shipper_state'] : '');
          			
          			if($last_dispatch_id != $row_stop['trucks_log_id']) 
          			{				
          				if($last_dispatch_id > 0) 
          				{
          					$sql = "
          						update trucks_log set
          							pcm_miles = '$pcm_miles_total'
          						where id = '$last_dispatch_id'
          					";
          					simple_query($sql);
          				}
          				
          				$last_dispatch_id = $row_stop['trucks_log_id'];
          				$pcm_miles_total = 0;
          			}
          			$stop_minutes = 0;
          			$pcm_miles = 0;
          			if($prev_city_state != $city_state || $prev_zip!=trim($row_stop['shipper_zip'])) 
          			{
          				try 
          				{				
          					if($prev_zip!=trim($row_stop['shipper_zip']))
          					{
          						$pcm_miles = $pcm->CalcDistance3($prev_zip, trim($row_stop['shipper_zip']), 0, $stop_minutes) / 10;
          					}
          					elseif($prev_city_state != $city_state) 
          					{
          						$pcm_miles = $pcm->CalcDistance3($prev_city_state, $city_state, 0, $stop_minutes) / 10;
          					}
          				} 
          				catch (Exception $e) 
          				{
          					$pcm_miles = 0;
          				}
          				
          			}
          			
          			$sql = "
          				update load_handler_stops set
          					pcm_miles = '$pcm_miles',linedate_updater=NOW()				
          				where id = '$row_stop[id]'
          			";
          			simple_query($sql);
     			}
     			$pcm_miles_total += $pcm_miles;
     			$prev_city_state = $city_state;
     			$prev_zip=trim($row_stop['shipper_zip']);
     			$mcntr++;
     		}
		}
		
		if($last_dispatch_id > 0 && $skip_pcmiler==0) 
		{
			$sql = "
				update trucks_log set
					pcm_miles = '$pcm_miles_total'
				where id = '$last_dispatch_id'
			";
			simple_query($sql);
		}
		if($skip_pcmiler==0)
		{
     		// update all the miles to be equal to the pcm_miles for all dispatches where the manual_miles_flag is 0
     		$sql = "
     			update trucks_log set
     				miles = pcm_miles
     			where load_handler_id = '".sql_friendly($load_id)."'
     				and manual_miles_flag = 0
     				and deleted = 0
     		";
     		simple_query($sql);
		}
		// now that we have our miles updated -- get the total, then multiply by our fuel surcharge, add it to our base expenses to get the customer rate
		$sql = "
			select ifnull(sum(miles + loaded_miles_hourly),0) as load_miles
			
			from trucks_log
			where deleted = 0
				and load_handler_id = '".sql_friendly($load_id)."'
		";
		$data_miles = simple_query($sql);
		$row_miles = mysqli_fetch_array($data_miles);
		
		// calculate the new bill customer
		$total_bill_cust = $row_var_exp['total_var_expenses'] + ($row_miles['load_miles'] * $row_cust['actual_fuel_surcharge_per_mile']);
		
		// update our new bill customer value
		$sql = "
			update load_handler set
				actual_bill_customer = '$total_bill_cust'
			where id = '".sql_friendly($load_id)."'
		";
		simple_query($sql);
		
		// update the dispatch profit
		if(mysqli_num_rows($data_dispatchs)) 
		{
			$load_profit = $total_bill_cust  - $total_load_cost;
			
			mysqli_data_seek($data_dispatchs, 0);
			while($row_dispatch = mysqli_fetch_array($data_dispatchs)) 
			{
				$dispatch_cost = get_dispatch_cost($row_dispatch['id']);
				if(!$dispatch_cost) 
				{
					$dispatch_cost_percent = 0;
					$dispatch_profit = $load_profit;	//0;
				} 
				else 
				{
					if($dispatch_cost == 0 || $total_load_cost == 0) 
					{
						$dispatch_cost_percent = 1;
					} 
					else 
					{
						$dispatch_cost_percent = $dispatch_cost / $total_load_cost;
					}
					$dispatch_profit = $load_profit * $dispatch_cost_percent;
				}
				
				$sql = "
					update trucks_log set
						profit = '$dispatch_profit'
					where id = '$row_dispatch[id]'
				";
				simple_query($sql);				
			}
		}
				
		mrr_dispatch_completion_updates($load_id,0);		//second arguments calls the function we are already in if > 0...infinite loop.
	}
	
	function default_exists($xname) {
		// checks to see if the specified default entry exists in the tbldefaults table
		
		$sql = "
			select id
			
			from defaults
			where xname = '".sql_friendly($xname)."'
		";
		$data = simple_query($sql);
		
		if(mysqli_num_rows($data)) {
			return true;
		} else {
			return false;
		}
	}
	
	function mrr_get_truck_cost($truck_id=0)
	{
		$truck_cost = 0;
		if($truck_id==0)
		{
			$truck_cost = mrr_get_option_variable_settings('Tractor Lease');
			$truck_cost += mrr_get_option_variable_settings('Truck Rental');
		}
		elseif($truck_id > 0) 
		{
			$sql = "
				select monthly_cost
				
				from trucks
				where id = '".sql_friendly($truck_id)."'
			";
			$data_truck_cost = simple_query($sql);
			$row_truck_cost = mysqli_fetch_array($data_truck_cost);
			
			if($row_truck_cost['monthly_cost'] > 0) $truck_cost = $row_truck_cost['monthly_cost'];			
		}
		return $truck_cost;	
	}
	function mrr_get_trailer_cost($trailer_id)
	{
		$trailer_cost=0;
		if($trailer_id==0)	
		{
			$trailer_cost=mrr_get_option_variable_settings('Trailer Expense');
			$trailer_cost+=mrr_get_option_variable_settings('Trailer Rental');
		}
		if($trailer_id > 0) 
		{
			$sql = "
				select monthly_cost_actual
				
				from trailers
				where id = '".sql_friendly($trailer_id)."'
			";
			$data_trailer_cost = simple_query($sql);
			$row_trailer_cost = mysqli_fetch_array($data_trailer_cost);
			
			if($row_trailer_cost['monthly_cost_actual'] > 0) $trailer_cost = $row_trailer_cost['monthly_cost_actual'];	
		}	
		return $trailer_cost;
	}
	
	function mrr_get_truck_rental_flag($truck_id=0)
	{
		$truck_rental_flag = 0;
		if($truck_id > 0) 
		{
			$sql = "
				select rental				
				from trucks
				where id = '".sql_friendly($truck_id)."'
			";
			$data_truck = simple_query($sql);
			if($row_truck = mysqli_fetch_array($data_truck))
			{
				$truck_rental_flag= $row_truck['rental'];	
			}			
		}
		return $truck_rental_flag;	
	}
	function mrr_get_rental_truck_counts()
	{
		$res['all_trucks']=0;
		$res['rentals']=0;
		$res['comp_owned']=0;
		$res['lease']=0;
		
		$sql = "
				select rental,leased_from,company_owned
				from trucks
				where deleted=0 and active=1
			";
		$data_truck = simple_query($sql);
		while($row_truck = mysqli_fetch_array($data_truck))
		{
			$res['all_trucks']++;
			
			if($row_truck['company_owned'] > 0)		$res['comp_owned']++;
			elseif($row_truck['rental'] > 0)			$res['rentals']++;
			elseif($row_truck['leased_from'] !="")		$res['lease']++;			
		}
		return $res;	
	}
	
	function get_daily_cost($truck_id = 0, $trailer_id = 0) {
		// if truck_id is specified, use the cost for that specific truck instead of the average
		// if trailer_id is specified, use the cost for that specific trailer instead of the average
		
		global $defaultsarray;
		
		// calculate our fixed expenses
		$sql = "
			select option_values.fvalue,
				option_values.fname,
				option_values.dummy_val
			
			from option_values, option_cat
				where option_cat.id = option_values.cat_id
				and option_cat.cat_name = 'fixed_expenses'
			order by option_values.fname
		";
		$data_expenses = simple_query($sql);
		
		$truck_cost = mrr_get_truck_cost($truck_id);
		
		$trailer_id=0;		//force default setting...
		$trailer_cost = mrr_get_trailer_cost($trailer_id);
		
		/*
		if($truck_id) {
			$sql = "
				select monthly_cost
				
				from trucks
				where id = '".sql_friendly($truck_id)."'
			";
			$data_truck_cost = simple_query($sql);
			$row_truck_cost = mysqli_fetch_array($data_truck_cost);
			
			if($row_truck_cost['monthly_cost'] > 0) $truck_cost = $row_truck_cost['monthly_cost'];			
		}
		*/
		if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' && isset($_GET['debug'])) { 
			//echo "<br>TRUCK COST FOUND FOR truck ".$truck_id."=".$truck_cost."<br>";	
		}
		/*
		if($trailer_id) {
			$sql = "
				select monthly_cost
				
				from trailers
				where id = '".sql_friendly($trailer_id)."'
			";
			$data_trailer_cost = simple_query($sql);
			$row_trailer_cost = mysqli_fetch_array($data_trailer_cost);
			
			if($row_trailer_cost['monthly_cost'] > 0) $trailer_cost = $row_trailer_cost['monthly_cost'];			
		}
		*/
		if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' && isset($_GET['debug'])) { 
			//echo "<br>TRAILER COST FOUND FOR trailer ".$trailer_id."=".$trailer_cost."<br>";	
		}
		$daily_expense = 0;
		
		$my_truck_lease_and_rental=0;
		
		while($row_expenses = mysqli_fetch_array($data_expenses)) {
			if($row_expenses['fvalue'] != 0) {
				$skip_expense = false;
				
				if(strtolower($row_expenses['fname']) == 'trailer lease' || strtolower($row_expenses['fname']) == 'trailer expense') {
					if($trailer_cost > 0) {
						$daily_expense += $trailer_cost / $defaultsarray['billable_days_in_month'];
					} else {
						$daily_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
					}
					$skip_expense = true;
				}
				
				if(strtolower($row_expenses['fname']) == 'tractor lease' || strtolower($row_expenses['fname']) == 'truck rental') {
					/**/
					if($truck_cost > 0) {
						$daily_expense += $truck_cost / $defaultsarray['billable_days_in_month'];
					} else {
						$daily_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
					}
					
					//$my_truck_lease_and_rental+=$row_expenses['fvalue'];		//now that there are two values...sum taken first then divided by billable days after while loop below.
					
					$skip_expense = true;
				}
				
				if(!$skip_expense) {
					// we already processed the expense ealier, skip it now so we don't double count it
					if($row_expenses['dummy_val'] == 1) {
						$daily_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'] / get_active_truck_count();
					} else {
						$daily_expense += $row_expenses['fvalue'] / $defaultsarray['billable_days_in_month'];
					}
				}
			}
		}
		
		$billdays=(int) $defaultsarray['billable_days_in_month'];
		if($billdays > 0)		
		{ 
			
		}
		else
		{
			$billdays=21;
		}	
		
		if($billdays > 0)
		{
			$daily_expense +=$my_truck_lease_and_rental / $billdays;
		}
		
		
		if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' && isset($_GET['debug'])) 
		{ 
			//	echo "<br>Daily Cost...=".$daily_expense."<br>";	
		}
		
		return $daily_expense;
	}
	
	function get_dispatch_cost($disp_id) 
	{		
		global $defaultsarray;
		
		$sql = "
			select trucks_log.*,
				load_handler.actual_fuel_charge_per_mile
			
			from trucks_log, load_handler
			where trucks_log.id = '".sql_friendly($disp_id)."'
				and load_handler.id = trucks_log.load_handler_id
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		// get any variable expenses
		$sql = "
			select *
			
			from dispatch_expenses
			where dispatch_id = '".sql_friendly($disp_id)."'
				and deleted = 0
		";
		$data_expenses = simple_query($sql);
		
		$variable_expenses_total = 0;
		while($row_expense = mysqli_fetch_array($data_expenses)) {
			$variable_expenses_total += $row_expense['expense_amount'];
		}
		
		$daily_cost = ($row['daily_cost'] > 0 ? $row['daily_cost'] : get_daily_cost($row['truck_id'], $row['trailer_id']));
		
		$avg_mpg = ($row['avg_mpg'] > 0 ? $row['avg_mpg'] : $defaultsarray['average_mpg']);
		$fuel_per_mile = $row['actual_fuel_charge_per_mile'];
		$tractor_maint_per_mile = ($row['tractor_maint_per_mile'] > 0 ? $row['tractor_maint_per_mile'] : $defaultsarray['tractor_maint_per_mile']);
		$trailer_maint_per_mile = ($row['trailer_maint_per_mile'] > 0 ? $row['trailer_maint_per_mile'] : $defaultsarray['trailer_maint_per_mile']);
		
		$mrr_other_per_mile=0;		
		
		$tires_per_mile = ($row['tires_per_mile'] > 0 ? $row['tires_per_mile'] : $defaultsarray['tires_per_mile']);
		$accidents_per_mile = ($row['accidents_per_mile'] > 0 ? $row['accidents_per_mile'] : $defaultsarray['truck_accidents_per_mile']);
		$mile_exp_per_mile = ($row['mile_exp_per_mile'] > 0 ? $row['mile_exp_per_mile'] : $defaultsarray['mileage_expense_per_mile']);
		
		$trailer_mile_exp_per_mile=0;
		//$trailer_mile_exp_per_mile = ($row['trailer_exp_per_mile'] > 0 ? $row['trailer_exp_per_mile'] : $defaultsarray['trailer_mile_exp_per_mile']);
		//$trailer_mile_exp_per_mile = $row['trailer_exp_per_mile'];		//$defaultsarray['trailer_mile_exp_per_mile']
		
		$misc_per_mile = ($row['misc_per_mile'] > 0 ? $row['misc_per_mile'] : $defaultsarray['misc_expense_per_mile']);
		$mrr_other_per_mile=$tires_per_mile + $accidents_per_mile + $mile_exp_per_mile + $misc_per_mile + $trailer_mile_exp_per_mile;
		
		$compute_dater=trim(substr($row['linedate_added'],0,10));			$compute_timer=trim(substr($row['linedate_added'],10));
		$compute_dater=str_replace("-","",$compute_dater);				$compute_timer=str_replace("-","",$compute_timer);
		$compute_dater=str_replace(":","",$compute_dater);				$compute_timer=str_replace(":","",$compute_timer);
		$compute_dater=str_replace(" ","",$compute_dater);				$compute_timer=str_replace(" ","",$compute_timer);
		
		$mrr_other_per_mile2=$mrr_other_per_mile;		
		if($compute_dater < 20120210 || ($compute_dater == 20120210 && $compute_timer <= 124620))
		{
			$mrr_other_per_mile=0;
		}
			
		$total_maint_per_mile = $tractor_maint_per_mile + $trailer_maint_per_mile + $mrr_other_per_mile;
				
		$stored_labor_per_mile = $row['labor_per_mile'];
		if($stored_labor_per_mile>0)
		{
			$total_per_mile = $stored_labor_per_mile + $total_maint_per_mile + $fuel_per_mile;
		}
		else
		{
			$total_per_mile = $defaultsarray['labor_per_mile'] + $total_maint_per_mile + $fuel_per_mile;
		}
		
		$deadhead_cost = $total_per_mile * $row['miles_deadhead'];
		$breakeven_otr = $total_per_mile * $row['miles'] + $deadhead_cost + ($daily_cost * $row['daily_run_otr']);
		$labor_per_hour = ($row['labor_per_hour'] > 0 ? $row['labor_per_hour'] : $defaultsarray['labor_per_hour']);
		$breakeven_hourly = ($row['loaded_miles_hourly'] + $row['miles_deadhead_hourly']) * ($fuel_per_mile + $total_maint_per_mile) + ($row['hours_worked'] * $labor_per_hour) + ($daily_cost * $row['daily_run_hourly']);

		$total_cost = $breakeven_otr + $breakeven_hourly + $variable_expenses_total;
		
		$err_on=0;
		
		$total_miles =  $row['miles_deadhead'] + $row['miles'];
		
		if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' && isset($_GET['debug'])) {
			echo "
				<span style='color:#aaa'>Labor (stored): ".($stored_labor_per_mile * $total_miles + ($row['hours_worked'] * $labor_per_hour))."</span><br>
			";
		}
		
		if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' && isset($_GET['debug']) && $err_on==1) {
			
			echo "<br>OUTPUT FROM functions.php ...function 'get_dispatch_cost()'
				<br><b>==============================================================================================</b><br>
				Labor per Mile: ".$stored_labor_per_mile."<br><br>
				
				Daily Cost from functions.php file is ".(get_daily_cost($row['truck_id'], $row['trailer_id']))."<br>
				Daily Cost from table is ".$row['daily_cost']."<br>
				DC Diff = ".(get_daily_cost($row['truck_id'], $row['trailer_id']) - $row['daily_cost'])."<br>
				<b>DC Diff * OTR = ".($row['daily_run_otr'] * (get_daily_cost($row['truck_id'], $row['trailer_id']) - $row['daily_cost']))."</b><br><br>
				
				Truck per Mile: ".$tractor_maint_per_mile."<br>	
				Trailer per Mile: ".$trailer_maint_per_mile."<br>	
				Maint per Mile Tot = ".$total_maint_per_mile."<br><br>
				
				Fuel per Mile: ".$fuel_per_mile."<br>
				Maint per Mile Tot = ".$total_maint_per_mile."<br>	
				Labor per Mile: ".$stored_labor_per_mile."<br>				
				Other per Mile: ".$mrr_other_per_mile."<br>					
				<b>Total per Mile = ".$total_per_mile."</b><br>
				<br>				
				Days Run Hourly: ".$row['daily_run_otr']."<br>
				B.E.OTR Hourly Fuel:  ".($row['loaded_miles_hourly'] + $row['miles_deadhead_hourly'])." x (".$fuel_per_mile." + ".$total_maint_per_mile.") = ".($row['loaded_miles_hourly'] + $row['miles_deadhead_hourly']) * ($fuel_per_mile + $total_maint_per_mile)."<br>
				B.E. OTR Hourly Labor: ".$row['hours_worked']." x ".$labor_per_hour." = ".($row['hours_worked'] * $labor_per_hour)."<br>
				B.E. OTR Hourly DayCost: ".$daily_cost ." x ".$row['daily_run_hourly']." = ".($daily_cost * $row['daily_run_hourly'])."<br>
				Break Even Hourly OTR =  ".$breakeven_hourly."<br>
				<br>				
				Days Run OTR: ".$row['daily_run_otr']."<br>
				B.E.OTR DH Cost:  ".$total_per_mile." x ".$row['miles_deadhead']." = ".$total_per_mile * $row['miles_deadhead']."<br>
				B.E. OTR Reg Cost: ".$total_per_mile." x ".$row['miles']." = ".$total_per_mile * $row['miles']."<br>
				B.E. OTR DayCost: ".$daily_cost ." x ".$row['daily_run_otr']." = ".$daily_cost * $row['daily_run_otr']."<br>
				Break Even OTR =  ".$breakeven_otr."<br>
				<br>
				
				<b>".$variable_expenses_total."  +  ".$breakeven_hourly."  +  ".$breakeven_otr."  =  ".$total_cost."</b><br>
				<br><br>
				fuel: ".($fuel_per_mile * $total_miles)."<br>
				Daily Cost: $daily_cost<br>
				Current Labor: ".($defaultsarray['labor_per_mile'] * $total_miles)."<br>
				<span style='color:#aaa'>Labor (stored): ".($stored_labor_per_mile * $total_miles)."</span><br>
				Maint: ".($total_maint_per_mile * $total_miles)."<br>
				Total for Dispatch: <b>".$total_cost."</b><br>
				<br><b>==============================================================================================</b><br>
			";
			/*
				
				
				Days Run Hourly: ".$row['daily_run_hourly']."<br>
				Labor per Hour: ".$labor_per_hour."<br>
				Hours Worked: ".$row['hours_worked']."<br>
				Miles Hourly: ".$row['loaded_miles_hourly']."<br>
				Fuel + Maint: ".($fuel_per_mile + $total_maint_per_mile)."<br>
				B.E. Hourly F+M Cost: ".$row['loaded_miles_hourly'] *($fuel_per_mile + $total_maint_per_mile)."<br>
				B.E. Hourly Labor: ".($row['hours_worked'] * $labor_per_hour) ."<br>
				B.E. Hourly DayCost: ".($daily_cost * $row['daily_run_hourly'])."<br>
				Break Even Hourly = ".$breakeven_hourly ."<br>
				<br>
				: ".$variable_expenses_total."<br>
				<br><br>
				
				
			*/
		}		
		return $total_cost;		
	}
	
	function get_available_trailers() {
		$sql = "
			select distinct id as trailer_id,
				trailer_name				
			
			from trailers
			where 
				(trailers.id not in (select trailer_id from trucks_log where trucks_log.deleted = 0 and trucks_log.dispatch_completed = 0)
					or trailers.allow_multiple = 1)
				and (trailers.id not in (select trailer_id from trailers_dropped where trailers_dropped.deleted = 0 and trailers_dropped.drop_completed = 0)
					or trailers.allow_multiple = 1)
				and trailers.active = 1
				and trailers.deleted = 0

			order by trailers.trailer_name
		";
		return simple_query($sql);

	}
	function mrr_get_available_dropped_trailers($city="",$state="",$zip="") 
	{
		/*
		$sql = "
			select distinct id as trailer_id,
				trailer_name				
			
			from trailers
			where 
				(trailers.id not in (select trailer_id from trucks_log where trucks_log.deleted = 0 and trucks_log.dispatch_completed = 0)
					or trailers.allow_multiple = 1)
				and (trailers.id not in (select trailer_id from trailers_dropped where trailers_dropped.deleted = 0 and trailers_dropped.drop_completed = 0)
					or trailers.allow_multiple = 1)
				and (
					trailers.id in (
									select trailer_id 
									from trailers_dropped 
									where trailers_dropped.deleted = 0 
										and trailers_dropped.drop_completed = 1
										and (
											location_city like '%".sql_friendly($city)."%'
											or location_state like '%".sql_friendly($state)."%'
											)
								)
					)
				and trailers.active = 1
				and trailers.deleted = 0

			order by trailers.trailer_name
		";
		*/
		$arr[0]=0;
		$cntr=0;
		
		$selbx="";
		$sql="
			select trailers_dropped.*,
				customers.name_company,
				trailers.trailer_name		
			from trailers_dropped
				left join customers on customers.id = trailers_dropped.customer_id
				left join trailers on trailers.id = trailers_dropped.trailer_id and trailers.deleted=0 and trailers.active=1
			where trailers_dropped.deleted = 0
			order by trailers_dropped.linedate desc, trailers.trailer_name asc
		";
		$data=simple_query($sql);
		
		$city=trim($city);			//strtolower()
		$state=trim($state);		//strtolower()
		
		while($row = mysqli_fetch_array($data))
		{
			$tid=$row['trailer_id'];
			$found=0;
			for($x=0;$x< $cntr;$x++)
			{
				if($tid==$arr[ $x ])	$found=1;
			}
			if($found==0)
			{				
				$tname=$row['trailer_name'];
     			$tcust=$row['name_company'];
     			$tcity=$row['location_city'];
     			$tstate=$row['location_state'];
     			$tzip=$row['location_zip'];
     			
     			$con_catter=trim("".$tcity." ".$tstate." ".$tzip." ".$tcust."");	//strtolower()
     			
     			$con_catter=strtolower($con_catter);
     			
     			$matcher=0;
     			     			
     			if(substr_count($con_catter,strtolower($city)) > 0 && $city!="")		$matcher=1;
     			//if(substr_count($con_catter,$state) > 0 && $state!="")		$matcher=1;
     			
     			if(trim($tname)=="")	$matcher=0;
     			
     			if($matcher==1)
     			{
     				$selbx.="<option value='".$tid."'>".$tname." (".$tcust.": ".$tcity.", ".$tstate." ".$tzip.")</option>";	
     			     			
     				$arr[ $cntr ]=$tid;
					$cntr++;
				}	
			}
		}
		return $selbx;
	}
	
	function get_customers() {
		$sql = "
			select *
			
			from customers
			where deleted = 0
				and active = 1
			order by name_company
		";
		
		return simple_query($sql);
	}
	
	function get_trailers() {
		$sql = "
			select *
			
			from trailers
			where deleted = 0
				and active = 1
			order by trailer_name
		";
		
		return simple_query($sql);
	}
	
	class report_filter {
		
		var $show_date_range 		= true;
		var $show_single_date		= false;
		var $show_payroll_date		= false;
		var $show_employers			= false;
		var $show_users			= false;
		var $show_users2			= false;
		var $show_customer			= false;
		var $show_customer_grp		= false;
		var $show_driver			= false;
		var $show_trailer			= false;
		var $show_trailer_owner		= false;
		var $show_trailer_interchange	= false;
		var $show_active			= false;
		var $show_consignee			= false;
		var $show_shipper			= false;
		var $show_truck			= false;
		var $show_skips			= false;
		var $show_load_id			= false;
		var $show_late_loads_only	= false;
         var $show_load_only			= false;
         var $show_report_log_mode			= false;
		var $generic_search_text		= false;
		var $show_quote_id			= false;
		var $show_dispatch_id		= false;
		var $show_shipper_name		= false;
		var $show_shipper_type		= false;
		var $show_stops			= false;
		var $show_error_scans		= false;
		var $show_dispatches		= false;
		var $show_invoice_number		= false;
		var $group_by_truck			= false;
		var $first_dispatch_all_credit= false;
		var $hide_non_first_dispatch  = false;
		var $show_deleted_loads		= false;
		var $summary_only			= false;
		var $payroll_mode			= false;
		var $team_choice			= false;
		var $show_only_invoiced		= false;
		var $show_origin			= false;
		var $show_destination		= false;
		var $maintenance_id			= false;
		var $accident_id			= false;
		var $maintenance_desc		= false;
		var $active_maint			= false;
        var $snooze_maint			= false;
		var $closed_maint			= false;
		var $maint_detail_items		= false;		
		var $recurring_maint		= false;
		var $down_time_hours		= false;
		var $maint_request_cost		= false;
		var $down_time_hours_to		= false;
		var $maint_request_cost_to	= false;
		var $maint_from_recur		= false;
		var $maint_urgent			= false;
		var $maint_category			= false;
		var $search_notes_files		= false;
		var $search_sort_by			= false;
		var $search_sort_by_report	= false;
		var $show_truck2			= false;
		var $mrr_no_form_enclosed	= false;		
		var $show_font_size			= false;
		var $show_edi_invoiced		= false;
		var $mrr_hide_submit_button	= false;
		var $mrr_excel_print_flag	= false;
		var $mrr_send_email_here		= false;
		var $mrr_special_print_button = false;
		var $mrr_special_run_api 	= false;
		var $log_activity_report_mode	= false;
		var $show_leased_from		= false;
		var $mrr_show_misc_value		= false;
		
		var $mrr_show_num_range		= false;
		
		var $mrr_show_num_range1		= false;
		var $mrr_show_num_range2		= false;
		var $mrr_show_num_range3		= false;
		var $mrr_show_num_range4		= false;
		var $mrr_show_num_range5		= false;
		
		var $mrr_show_num_haul1		= false;
		var $mrr_show_num_haul2		= false;
		
		var $mrr_show_zero_value		= false;
		
		var $mrr_driver_mode		= false;
		
		var $mrr_geotab_log_mode		= false;
		var $mrr_geotab_diagnostic	= false;
		
		var $show_cust_addr1		= false;
		var $show_cust_addr2		= false;
		var $show_cust_city			= false;
		var $show_cust_state		= false;
		var $show_cust_zip			= false;
		var $show_radius_val		= false;
		
		var $vacation_advance		= false;
		
		function handle_quick_dates() {
			if(isset($_POST['report_quick_date']) && $_POST['report_quick_date'] > 0) {
				if($_POST['report_quick_date'] == '1') {
					// this week
					$start_date = strtotime("-".date("w")." day", time());
					$_POST['date_from'] = date("m/d/Y", $start_date);
					$end_date = strtotime("6 day", $start_date);
					$_POST['date_to'] = date("m/d/Y", $end_date);
				} elseif($_POST['report_quick_date'] == '6') {
					// this week (to date)
					$start_date = strtotime("-".date("w")." day", time());
					$_POST['date_from'] = date("m/d/Y", $start_date);
					$_POST['date_to'] = date("m/d/Y");
				} elseif($_POST['report_quick_date'] == '2') {
					// last week
					$start_date = strtotime("-".(date("w")+7)." day", time());
					$_POST['date_from'] = date("m/d/Y", $start_date);
					$end_date = strtotime("6 day", $start_date);
					$_POST['date_to'] = date("m/d/Y", $end_date);
				} elseif($_POST['report_quick_date'] == '3') {
					// this month
					$_POST['date_from'] = date("m/1/Y");
					$days_in_month = date("t", strtotime($_POST['date_from']));
					$_POST['date_to'] = date("m/$days_in_month/Y");
				} elseif($_POST['report_quick_date'] == '5') {
					// this month (to date)
					$_POST['date_from'] = date("m/1/Y");
					$_POST['date_to'] = date("m/d/Y");
				} elseif($_POST['report_quick_date'] == '4') {
					// last month
					// month start
					$tmp_date = strtotime(date("m/15/Y"));
					$_POST['date_from'] = date("m/1/Y", strtotime("-1 month", $tmp_date));
					$days_in_month = date("t", strtotime($_POST['date_from']));
					$_POST['date_to'] = date("m/$days_in_month/Y", strtotime("-1 month", $tmp_date));
				} elseif($_POST['report_quick_date'] == '7') {
					// this year
					$_POST['date_from'] = date("1/1/Y");
					$_POST['date_to'] = date("12/31/Y");
				} elseif($_POST['report_quick_date'] == '8') {
					// last year
					$use_date = strtotime("-1 year", time());
					$_POST['date_from'] = date("1/1/Y", $use_date);
					$_POST['date_to'] = date("12/31/Y", $use_date);
				} else {
					// selected month
					$_POST['date_from'] = $_POST['report_quick_date'];
					$days_in_month = date("t", strtotime($_POST['date_from']));
					$_POST['date_to'] = date("m/$days_in_month/Y", strtotime($_POST['date_from']));
				}
			}			
		}
		
		function show_filter() {
			
			global $use_title;
			global $quick_date_range_array;
			global $use_bootstrap;
			
			
			if(isset($_POST['print']) || isset($_GET['print'])) {
				// we're in print mode, don't show the filter set
				?>
				<form action='' id='report_form' method='post'>
					<!-- Only add the filters that are most commonly used here... --->
					<? if(isset($_POST['date_from'])) { ?> <input type='hidden' name='date_from' value='<?=$_POST['date_from'] ?>'> <? } ?>
					<? if(isset($_POST['date_to'])) { ?> <input type='hidden' name='date_to' value='<?=$_POST['date_to'] ?>'> <? } ?>
					
					<? if(isset($_POST['load_handler_id'])) { ?> <input type='hidden' name='load_handler_id' value='<?=$_POST['load_handler_id'] ?>'> <? } ?>
					<? if(isset($_POST['dispatch_id'])) { ?> <input type='hidden' name='dispatch_id' value='<?=$_POST['dispatch_id'] ?>'> <? } ?>
					
					<? if(isset($_POST['driver_id'])) { ?> <input type='hidden' name='driver_id' value='<?=$_POST['driver_id'] ?>'> <? } ?>
					<? if(isset($_POST['truck_id'])) { ?> <input type='hidden' name='truck_id' value='<?=$_POST['truck_id'] ?>'> <? } ?>
					<? if(isset($_POST['trailer_id'])) { ?> <input type='hidden' name='trailer_id' value='<?=$_POST['trailer_id'] ?>'> <? } ?>
					<? if(isset($_POST['customer_id'])) { ?> <input type='hidden' name='customer_id' value='<?=$_POST['customer_id'] ?>'> <? } ?>
					<? if(isset($_POST['employer_id'])) { ?> <input type='hidden' name='employer_id' value='<?=$_POST['employer_id'] ?>'> <? } ?>
					
				 	<center><input type='submit' value='Back' name='print_back' id='print_back'></center>
				 	<br>
				</form>
				<?				
				return false;
			}
			
			if(isset($_POST['print_back']))	$_POST['build_report']=1;			
				
			
			if(!isset($_POST['date_from'])) $_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
			if(!isset($_POST['date_to'])) $_POST['date_to'] = date("n/j/Y", time());
			if(!isset($_POST['report_date'])) $_POST['report_date'] = date("n/j/Y", time());
			if(!isset($_POST['report_payroll_date'])) 	$_POST['report_payroll_date'] = "";
			if(!isset($_POST['driver_id'])) $_POST['driver_id'] = 0;
			if(!isset($_POST['trailer_id'])) $_POST['trailer_id'] = 0;
			
			if(!isset($_POST['report_trailer_owner'])) 		$_POST['report_trailer_owner'] = "";
			if(!isset($_POST['report_trailer_interchange'])) 	$_POST['report_trailer_interchange'] = 0;
			if(!isset($_POST['report_active']))			$_POST['report_active']=0;
			
			if(!isset($_POST['truck_id'])) $_POST['truck_id'] = 0;
			if(!isset($_POST['report_quote_id'])) $_POST['report_quote_id'] = '';
			if(!isset($_POST['report_user_id'])) $_POST['report_user_id'] = 0;
			if(!isset($_POST['report_user_id2'])) $_POST['report_user_id2'] = 0;
			if(!isset($_POST['dispatch_id'])) $_POST['dispatch_id'] = '';
			if(!isset($_POST['load_handler_id'])) $_POST['load_handler_id'] = '';
			if(!isset($_POST['shipper_name']))		$_POST['shipper_name']="";
			if(!isset($_POST['shipper_type']))		$_POST['shipper_type']=0;
			if(!isset($_POST['report_consignee'])) $_POST['report_consignee'] = '';
			if(!isset($_POST['report_shipper'])) $_POST['report_shipper'] = '';
			if(!isset($_POST['customer_id'])) $_POST['customer_id'] = 0;
			if(!isset($_POST['team_choice'])) $_POST['team_choice'] = 0;
			if(!isset($_POST['employer_id'])) $_POST['employer_id'] = 0;
			if(!isset($_POST['report_quick_date'])) $_POST['report_quick_date'] = '0';
			if(!isset($_POST['report_invoice_number'])) $_POST['report_invoice_number'] = "";
			if(!isset($_POST['report_origin'])) $_POST['report_origin'] = "";
			if(!isset($_POST['report_origin_state'])) $_POST['report_origin_state'] = "";
			if(!isset($_POST['report_destination'])) $_POST['report_destination'] = "";
			if(!isset($_POST['report_destination_state'])) $_POST['report_destination_state'] = "";
			if(!isset($_POST['report_generic_search_text'])) $_POST['report_generic_search_text'] = "";
			if(!isset($_POST['report_show_error_scans'])) $_POST['report_show_error_scans'] = 0;
			
			if(!isset($_POST['down_time_hours'])) 		$_POST['down_time_hours'] = "";
			if(!isset($_POST['maint_request_cost'])) 	$_POST['maint_request_cost'] = "";
			if(!isset($_POST['down_time_hours_to'])) 	$_POST['down_time_hours_to'] = "";
			if(!isset($_POST['maint_request_cost_to'])) 	$_POST['maint_request_cost_to'] = "";
			if(!isset($_POST['maintenance_id'])) 		$_POST['maintenance_id'] = "";			
			if(!isset($_POST['maintenance_desc'])) 		$_POST['maintenance_desc'] = "";
			if(!isset($_POST['maint_category']))		$_POST['maint_category']= 0;
			if(!isset($_POST['search_notes_files']))	$_POST['search_notes_files']=0;
			if(!isset($_POST['search_sort_by']))		$_POST['search_sort_by']=0;
			if(!isset($_POST['search_sort_by_report']))	$_POST['search_sort_by_report']="";
						
			if(!isset($_POST['accident_id']))			$_POST['accident_id']=0;
			
			if(!isset($_POST['truck_id2'])) 			$_POST['truck_id2'] = array();
			
			if(!isset($_POST['report_font_display']))	$_POST['report_font_display']=14;
			
			if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
			if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";
			
			if(!isset($_POST['mrr_run_api']))			$_POST['mrr_run_api']=0;
			if(!isset($_POST['report_activity_log']))	$_POST['report_activity_log']=0;
			
			if(!isset($_POST['report_leased_from']))	$_POST['report_leased_from']="";
			
			if(!isset($_POST['mrr_cust_addr1']))		$_POST['mrr_cust_addr1']='';
			if(!isset($_POST['mrr_cust_addr2']))		$_POST['mrr_cust_addr2']='';
			if(!isset($_POST['mrr_cust_city']))		$_POST['mrr_cust_city']='';
			if(!isset($_POST['mrr_cust_state']))		$_POST['mrr_cust_state']='';
			if(!isset($_POST['mrr_cust_zip']))			$_POST['mrr_cust_zip']='';
			if(!isset($_POST['mrr_radius']))			$_POST['mrr_radius']=0;
			
			if(!isset($_POST['vacation_advance']))		$_POST['vacation_advance']=0;
			if(!isset($_POST['payroll_mode']))			$_POST['payroll_mode']=0;
			
			if(!isset($_POST['skip_type_id']))			$_POST['skip_type_id']=0;
			
			if(!isset($_POST['mrr_misc_value_label']))	$_POST['mrr_misc_value_label']="Misc";
			if(!isset($_POST['mrr_show_misc_values']))	$_POST['mrr_show_misc_values']=0;
			
			if(!isset($_POST['mrr_show_zero_values']))	$_POST['mrr_show_zero_values']=0;			
			if(!isset($_POST['mrr_report_driver_mode']))	$_POST['mrr_report_driver_mode']=0;
			
			if(!isset($_POST['mrr_report_geotab_log_mode']))			$_POST['mrr_report_geotab_log_mode']=0;
			if(!isset($_POST['mrr_report_geotab_diagnostic_mode']))	$_POST['mrr_report_geotab_diagnostic_mode']=0;
			
			if(!isset($_POST['mrr_show_num_range_min']))		$_POST['mrr_show_num_range_min']=0;
			if(!isset($_POST['mrr_show_num_range_max']))		$_POST['mrr_show_num_range_max']=0;
			
			
			if(!isset($_POST['mrr_show_num_range1_min']))	$_POST['mrr_show_num_range1_min']=0;
			if(!isset($_POST['mrr_show_num_range1_max']))	$_POST['mrr_show_num_range1_max']=0;
			
			if(!isset($_POST['mrr_show_num_range2_min']))	$_POST['mrr_show_num_range2_min']=0;
			if(!isset($_POST['mrr_show_num_range2_max']))	$_POST['mrr_show_num_range2_max']=0;
			
			if(!isset($_POST['mrr_show_num_range3_min']))	$_POST['mrr_show_num_range3_min']=0;
			if(!isset($_POST['mrr_show_num_range3_max']))	$_POST['mrr_show_num_range3_max']=0;
			
			if(!isset($_POST['mrr_show_num_range4_min']))	$_POST['mrr_show_num_range4_min']=0;
			if(!isset($_POST['mrr_show_num_range4_max']))	$_POST['mrr_show_num_range4_max']=0;
			
			if(!isset($_POST['mrr_show_num_range5_min']))	$_POST['mrr_show_num_range5_min']=0;
			if(!isset($_POST['mrr_show_num_range5_max']))	$_POST['mrr_show_num_range5_max']=0;
			
			
			if(!isset($_POST['mrr_show_num_haul1_min']))		$_POST['mrr_show_num_haul1_min']=0;
			if(!isset($_POST['mrr_show_num_haul1_max']))		$_POST['mrr_show_num_haul1_max']=0;
			
			if(!isset($_POST['mrr_show_num_haul2_min']))		$_POST['mrr_show_num_haul2_min']=0;
			if(!isset($_POST['mrr_show_num_haul2_max']))		$_POST['mrr_show_num_haul2_max']=0;
			
					
			$this->handle_quick_dates();
		
			/* get the driver list */
			$sql = "
				select *
				
				from drivers
				where deleted = 0
				order by active desc, name_driver_last, name_driver_first
			";
			$data_drivers = simple_query($sql);
			
			/* get the customer list */
			$sql = "
				select *
				
				from customers
				where deleted = 0
				order by name_company
			";
			$data_customers = simple_query($sql);
			
			/* get the traier list */
			$sql = "
				select *
				
				from trailers
				where deleted = 0
				order by active desc, trailer_name
			";
			$data_trailers = simple_query($sql);
			
			/* get the truck list */
			$sql = "
				select *
				
				from trucks
				where deleted = 0
				order by active desc, name_truck
			";
			$data_trucks = simple_query($sql);
			
			/* get the user list */
			$sql = "
				select *
				
				from users
				where deleted = 0
				order by active desc, name_last, name_first
			";
			$data_users = simple_query($sql);
			$data_users2 = simple_query($sql);
			
			/* get the leased from list */
			$sql = "
				select distinct(leased_from)
				
				from trucks
				where deleted = 0 and leased_from is not NULL and leased_from!=''
				order by leased_from
			";
			$data_leased = simple_query($sql);
			
			/* get the owner from list */
			$sql = "
				select distinct(trailer_owner)
				
				from trailers
				where deleted = 0 and trailer_owner is not NULL and trailer_owner!=''
				order by trailer_owner
			";
			$data_owner = simple_query($sql);
			
			?>
			
			<? if($this->mrr_no_form_enclosed) { ?>
				&nbsp;<br>
			<? }
				else
				{			
			 ?>
			 	<form action='' id='report_form' method='post'>
			<? } ?>	
			
			<input type='hidden' name='build_report' value='1'>
			<? if($use_bootstrap==true) { ?>
				<table class='table well table-bordered' width='100%'>
			<? } else { ?>
				<table class='admin_menu1' style='margin:10px;text-align:left'>
			<? } ?>
			
			<? if($this->show_employers) { ?>
				<?
					// get the list of employers
                         $sql="
                         	select option_values.id as use_val,
                         		option_values.fvalue as use_disp
                         	from option_values,option_cat
                         	where option_values.deleted=0
                         		and option_cat.id=option_values.cat_id
                         		and option_cat.cat_name='employer_list'
                         	order by option_values.fvalue asc
                         ";
					$mrr_emp_id=0;
					if(isset($_POST['employer_id']))		$mrr_emp_id=$_POST['employer_id'];
					$emp_box=mrr_select_box_disp($sql,'employer_id',$mrr_emp_id,'All Employers',''); 		// style="width:300px;"
				?>
				<tr>
					<td><span id='report_show_employers'>Employer</span></td>
					<td><?= $emp_box ?></td>
				</tr>
			<? } ?>
			<? if($this->show_customer) { ?>
				<tr>
					<td><span id='report_show_customer'>Customer</span></td>
					<td>
						<select name='customer_id' id='customer_id'>
							<option value='0'>All Customers</option>
							<? if($this->show_customer_grp) { ?>
								<option value='-1'>Bridgestone Customers</option>
							<? } ?>
							<?
							while($row_customer = mysqli_fetch_array($data_customers)) { 
								echo "<option value='$row_customer[id]' ".($row_customer['id'] == $_POST['customer_id'] ? 'selected' : '').">$row_customer[name_company]</option>";
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_users) { ?>
				<tr>
					<td><span id='report_show_users'>User</span></td>
					<td>
						<select name='report_user_id' id='report_user_id'>
							<option value='0'>All Users</option>
							<?
							while($row_user = mysqli_fetch_array($data_users)) { 
								echo "<option value='$row_user[id]' ".($row_user['id'] == $_POST['report_user_id'] ? 'selected' : '').">$row_user[name_last], $row_user[name_first]</option>";
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_users2) { ?>
				<tr>
					<td><span id='report_show_users2'>And User</span></td>
					<td>
						<select name='report_user_id2' id='report_user_id2'>
							<option value='0'>All Users</option>
							<?
							while($row_user2 = mysqli_fetch_array($data_users2)) { 
								echo "<option value='$row_user2[id]' ".($row_user2['id'] == $_POST['report_user_id2'] ? 'selected' : '').">$row_user2[name_last], $row_user2[name_first]</option>";
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_driver) { ?>
				<tr>
					<td><span id='report_show_driver'>Driver</span></td>
					<td>
						<select name='driver_id' id='driver_id'>
							<option value='0'>All Drivers</option>
							<?
							while($row_driver = mysqli_fetch_array($data_drivers)) { 
								echo "<option value='$row_driver[id]' ".($row_driver['id'] == $_POST['driver_id'] ? 'selected' : '').">".(!$row_driver['active'] ? '(inactive) ' : '')."$row_driver[name_driver_last], $row_driver[name_driver_first]</option>";
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_trailer) { ?>
				<tr>
					<td><span id='report_show_trailer'>Trailer</span></td>
					<td>
						<select name='trailer_id' id='trailer_id'>
							<option value='0'>All Trailers</option>
							<?
							while($row_trailer = mysqli_fetch_array($data_trailers)) { 
								echo "<option value='$row_trailer[id]' ".($row_trailer['id'] == $_POST['trailer_id'] ? 'selected' : '').">".(!$row_trailer['active'] ? '(inactive) ' : '')."$row_trailer[trailer_name]</option>";
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			
			<? if($this->show_trailer_owner) { ?>
				<tr>
					<td><span id='report_show_trailer_owner'>Trailer Owner</span></td>
					<td>
						
						<select name='report_trailer_owner' id='report_trailer_owner'>
							<option value=''>All</option>
							<?							
							while($row_owner = mysqli_fetch_array($data_owner)) 
							{
								$sel="";
								if(trim($_POST['report_trailer_owner']) == trim($row_owner['trailer_owner']))		$sel=" selected";
								echo "<option value=\"".$row_owner['trailer_owner']."\" ".$sel.">".$row_owner['trailer_owner']."</option>";
								
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_trailer_interchange) { ?>
				<tr>
					<td><span id='report_show_trailer_interchange'>Trailer Interchange</span></td>
					<td>					
						<select name='report_trailer_interchange' id='report_trailer_interchange'>
							<option value='0' <?=($_POST['report_trailer_interchange'] == 0 ? "selected" : "")?>>Show All</option>
							<option value='1' <?=($_POST['report_trailer_interchange'] == 1 ? "selected" : "")?>>Show Interchange</option>
							<option value='2' <?=($_POST['report_trailer_interchange'] == 2 ? "selected" : "")?>>Show No Interchange</option>
						</select>
					</td>
				</tr>
			<? } ?>
			
			<? if($this->show_truck) { ?>
				<tr>
					<td><span id='report_show_truck'>Truck</span></td>
					<td>
						<select name='truck_id' id='truck_id'>
							<option value='0'>All Trucks</option>
							<?
							while($row_truck = mysqli_fetch_array($data_trucks)) { 
								echo "<option value='$row_truck[id]' ".($row_truck['id'] == $_POST['truck_id'] ? 'selected' : '').">".(!$row_truck['active'] ? '(inactive) ' : '')."$row_truck[name_truck]</option>";
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_truck2) { ?>
				<tr>
					<td><span id='report_show_truck2'>Truck(s)</span></td>
					<td>
						<select name='truck_id[]' id='truck_id[]' multiple="yes">
							<option value='0'>All Trucks</option>
							<?
														
							$multi_sel_trucks=$_POST['truck_id'];
							$multi_sel_count=count($multi_sel_trucks);
							
							while($row_truck = mysqli_fetch_array($data_trucks)) 
							{ 
								$mrr_found=0;
								for($x=0;$x < $multi_sel_count; $x++)
								{
									if($multi_sel_trucks[$x] == $row_truck['id'] )	$mrr_found=1;
								}
																
								echo "<option value='$row_truck[id]' ".($mrr_found == 1 ? 'selected' : '').">".(!$row_truck['active'] ? '(inactive) ' : '')."$row_truck[name_truck]</option>";
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_skips) { ?>
				<tr>
					<td><span id='report_show_trailer'>Skip Type</span></td>
					<td>
						<select name='skip_type_id' id='skip_type_id'>
							<?							
							echo "<option value='0' ".($_POST['skip_type_id']==0 ? 'selected' : '').">Show All</option>";
							echo "<option value='58' ".($_POST['skip_type_id']==58 ? 'selected' : '').">Skip Trucks</option>";
							echo "<option value='59' ".($_POST['skip_type_id']==59 ? 'selected' : '').">Skip trailers</option>";
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_leased_from) { ?>
				<tr>
					<td><span id='report_show_leased_from'>Leased From</span></td>
					<td>
						<select name='report_leased_from' id='report_leased_from'>
							<option value=''>All</option>
							<?							
							while($row_leased = mysqli_fetch_array($data_leased)) 
							{
								$sel="";
								if(trim($_POST['report_leased_from']) == trim($row_leased['leased_from']))		$sel=" selected";
								echo "<option value=\"".$row_leased['leased_from']."\" ".$sel.">".$row_leased['leased_from']."</option>";
								
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>			
			<? if($this->show_consignee) { ?>
				<tr>
					<td><span id='report_show_consignee'>Consignee</span></td>
					<td><input name='report_consignee' id='report_consignee' value='<?=$_POST['report_consignee']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_shipper) { ?>
				<tr>
					<td><span id='report_show_shipper'>Shipper</span></td>
					<td><input name='report_shipper' id='report_shipper' value='<?=$_POST['report_shipper']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_error_scans) { ?>
				<tr>
					<td><span id='report_show_error_scans'>Show</span></td>
					<td>
						<select name='report_show_error_scans' id='report_show_error_scans'>
							<option value='0' <?=($_POST['report_show_error_scans'] == 0 ? "selected" : "")?>>Show All</option>
							<option value='1' <?=($_POST['report_show_error_scans'] == 1 ? "selected" : "")?>>Show Only Error Scans</option>
							<option value='2' <?=($_POST['report_show_error_scans'] == 2 ? "selected" : "")?>>Show Only Valid Scans</option>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_load_id) { ?>
				<tr>
					<td><span id='report_show_load_id'>Load Handler ID</span></td>
					<td><input name='load_handler_id' id='load_handler_id' value='<?=$_POST['load_handler_id']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_load_only) { ?>
				<tr>
					<td><span id='report_show_load_only'>Loads Only (Created)</span></td>
					<td>
					    <select name='report_show_loads_only' id='report_show_loads_only'>
							<option value='0' <?=($_POST['report_show_loads_only'] == 0 ? "selected" : "")?>>No</option>
							<option value='1' <?=($_POST['report_show_loads_only'] == 1 ? "selected" : "")?>>Yes</option>
						</select>
                    </td>
				</tr>
			<? } ?>
			<? if($this->show_report_log_mode) { ?>
			    <tr>
					<td><span id='report_log_mode_oil'>AVI/Oil Change Mode</span></td>
					<td>
					    <select name='report_log_mode_oil' id='report_log_mode_oil'>
							<option value='0' <?=($_POST['report_log_mode_oil'] == 0 ? "selected" : "")?>>AVI and Oil Change</option>
							<option value='1' <?=($_POST['report_log_mode_oil'] == 1 ? "selected" : "")?>>Oil Change</option>
							<option value='2' <?=($_POST['report_log_mode_oil'] == 2 ? "selected" : "")?>>AVI</option>
						</select>
                    </td>
				</tr>
			<? } ?>
			<? if($this->show_dispatch_id) { ?>
				<tr>
					<td><span id='report_show_dispatch_id'>Dispatch ID</span></td>
					<td><input name='dispatch_id' id='dispatch_id' value='<?=$_POST['dispatch_id']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_shipper_name) { ?>
				<tr>
					<td><span>Shipper/Consignee</span></td>
					<td><input name='shipper_name' id='shipper_name' value='<?=$_POST['shipper_name']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_shipper_type) { ?>
				<tr>
					<td><span>Stop Type</span></td>
					<td>
						<select name='shipper_type' id='shipper_type'>
							<option value='0' <?=($_POST['shipper_type'] == 0 ? "selected" : "")?>>Show All</option>
							<option value='1' <?=($_POST['shipper_type'] == 1 ? "selected" : "")?>>Show Only Shipper</option>
							<option value='2' <?=($_POST['shipper_type'] == 2 ? "selected" : "")?>>Show Only Consignee</option>
						</select>	
					</td>
				</tr>
			<? } ?>
			<? if($this->show_quote_id) { ?>
				<tr>
					<td><span id='report_show_quote_id'>Quote ID</span></td>
					<td><input name='report_quote_id' id='report_quote_id' value='<?=$_POST['report_quote_id']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_invoice_number) { ?>
				<tr>
					<td><span id='report_show_invoice_number'>Invoice Number</span></td>
					<td><input name='report_invoice_number' id='report_invoice_number' value='<?=$_POST['report_invoice_number']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_origin) { ?>
				<tr>
					<td><span id='report_report_origin'>Origin City</span></td>
					<td><input name='report_origin' id='report_origin' value='<?=$_POST['report_origin']?>'></td>
				</tr>
				<tr>
					<td><span id='report_report_origin_state'>Origin State</span></td>
					<td><input name='report_origin_state' id='report_origin_state' value='<?=$_POST['report_origin_state']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_destination) { ?>
				<tr>
					<td><span id='report_report_destination'>Destination City</span></td>
					<td><input name='report_destination' id='report_destination' value='<?=$_POST['report_destination']?>'></td>
				</tr>
				<tr>
					<td><span id='report_report_destination_state'>Destination State</span></td>
					<td><input name='report_destination_state' id='report_destination_state' value='<?=$_POST['report_destination_state']?>'></td>
				</tr>
			<? } ?>
			<? if($this->generic_search_text) { ?>
				<tr>
					<td><span id='report_report_generic_search_text'>Search</span></td>
					<td><input name='report_generic_search_text' id='report_generic_search_text' value='<?=$_POST['report_generic_search_text']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_date_range) { ?>
				<tr>
					<td><span id='report_date_from'>Date From</span></td>
					<td><input name='date_from' id='date_from' class='<?= ($use_bootstrap==true  ? "date_picker_rates" : "report_datepicker" ) ?>' value='<?=$_POST['date_from']?>'> (mm/dd/yyyy)</td>
				</tr>
				<tr>
					<td><span id='report_date_to'>Date To</span></td>
					<td><input name='date_to' id='date_to' class='<?= ($use_bootstrap==true  ? "date_picker_rates" : "report_datepicker" ) ?>' value='<?=$_POST['date_to']?>'> (mm/dd/yyyy)</td>
				</tr>
				<tr>
					<td><span id='report_report_quick_date'>Quick Date</span></td>
					<td>
						<select name='report_quick_date'>
							<option value='0'>Select Quick Date</option>
							<option value='1'>This Week</option>
							<option value='6'>This Week (to Date)</option>
							<option value='2'>Last Week</option>
							<option value='3'>This Month</option>
							<option value='5'>This Month (to Date)</option>
							<option value='4'>Last Month</option>
							<option value='7'>This Year</option>
							<option value='8'>Last Year</option>
							<?
							for($i=1;$i<13;$i++) {
								$use_month = strtotime("-$i month", time());
								echo "<option value='".date("m/1/Y", $use_month)."'>".date("F, Y", $use_month)."</option>";
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_single_date) { ?>
				<tr>
					<td><span id='report_show_single_date'>Date</span></td>
					<td><input name='report_date' id='report_date' class='report_datepicker' value='<?=$_POST['report_date']?>'></td>
				</tr>
			<? } ?>
			<? if($this->show_payroll_date) { ?>
				<tr>
					<td><span id='report_show_payroll_date'>Payroll Start Date</span> <?= show_help('report_payroll.php','Payroll Start Date') ?></td>
					<td><input name='report_payroll_date' id='report_payroll_date' class='report_datepicker' value='<?=$_POST['report_payroll_date']?>'>  (mm/dd/yyyy)</td>
				</tr>
			<? } ?>
			<? if($this->show_stops) { ?>
				<tr>
					<td><span id='report_show_stops'></span></td>
					<td><label><input type='checkbox' name='show_stops' id='show_stops' <?=(isset($_POST['show_stops']) ? 'checked' : '')?>> Show all the stops of a dispatch</label></td>
				</tr>
			<? } ?>
			<? if($this->show_dispatches) { ?>
				<tr>
					<td><span id='report_show_dispatches'></span></td>
					<td><label><input type='checkbox' name='show_dispatches' id='show_dispatches' <?=(isset($_POST['show_dispatches']) ? 'checked' : '')?>> Show all the dispatches for each load</label></td>
				</tr>
			<? } ?>
			<? if($this->show_active) { ?>
				<tr>
					<td></td>
					<td><label><input type='checkbox' name='report_active' id='report_active' <?=($_POST['report_active']> 0 ? 'checked' : '')?> value='1'> 
						<span id='report_active'>Show Active</span></label></td>
				</tr>
			<? } ?>	
			<? if($this->maintenance_id) { ?>
				<tr>
					<td><span id='report_maintenance_id'>Maintenance Request ID</span></td>
					<td><input name='maintenance_id' id='maintenance_id' value='<?=(isset($_POST['maintenance_id'])? $_POST['maintenance_id'] : "" ) ?>' placeholder='0'></td>
				</tr>
			<? } ?>
			<? if($this->maintenance_desc) { ?>
				<tr>
					<td><span id='report_maintenance_desc'>Search Description</span></td>
					<td><input name='maintenance_desc' id='maintenance_desc' value='<?=(isset($_POST['maintenance_desc'])? $_POST['maintenance_desc'] : "" ) ?>'></td>
				</tr>
			<? } ?>
			<? if($this->accident_id) { ?>
				<tr>
					<td><span id='report_accident_id'>Accident ID</span></td>
					<td><input name='accident_id' id='accident_id' value='<?=(isset($_POST['accident_id'])? $_POST['accident_id'] : "" ) ?>'></td>
				</tr>
			<? } ?>
			<? if($this->maint_category) { ?>
			<?
					// get the list of request categories
					$mrr_cat_id=0;
					$sql = "
						select id 
							from option_cat 
							where cat_name='request_category' and deleted=0
							limit 1
					";
					$mdata = simple_query($sql);
					
					if($mrow = mysqli_fetch_array($mdata))			$mrr_cat_id= $mrow['id'];
																			
					$sql = "
						select id,fname,fvalue						
							from option_values
							where cat_id='".sql_friendly($mrr_cat_id)."' and deleted=0
							order by fvalue asc,fname asc,id asc
					";
					$data_categories = simple_query($sql);
				?>
				<tr>
					<td><span id='report_maint_category'>Search Category</span></td>
					<td>
						<select name='maint_category' id='maint_category'>
							<option value='0'>All Categories</option>
							<?
							$emp_array = array();
							while($row_categories = mysqli_fetch_array($data_categories)) 
							{								
								$emp_array[] = $row_categories['id'];
								echo "<option value='".$row_categories['id']."' ".($row_categories['id'] == $_POST['maint_category'] ? 'selected' : '').">".$row_categories['fvalue']."</option>";								
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->recurring_maint) { ?>
				<tr>
					<td><span id='report_recurring_maint'></span></td>
					<td><label><input type='checkbox' name='recurring_maint' id='recurring_maint' <?=(isset($_POST['recurring_maint']) ? 'checked' : '')?>> Show Recurring Maintenance</label></td>
				</tr>
			<? } ?>
			<? if($this->active_maint) { ?>
				<tr>
					<td></td>
					<td><label><input type='checkbox' name='active_maint' id='active_maint' <?=(isset($_POST['active_maint']) ? 'checked' : '')?>> 
						<span id='report_active_maint'>Show Opened Requests</span></label></td>
				</tr>
			<? } ?>	
			<? if($this->snooze_maint) { ?>
				<tr>
					<td></td>
					<td><label><input type='checkbox' name='snooze_maint' id='snooze_maint' <?=(isset($_POST['snooze_maint']) ? 'checked' : '')?>> 
						<span id='report_snooze_maint'>Show Snoozed Requests</span></label></td>
				</tr>
	        <? }  ?>	
			<? if($this->closed_maint) { ?>
				<tr>
					<td></td>
					<td><label><input type='checkbox' name='closed_maint' id='closed_maint' <?=(isset($_POST['closed_maint']) ? 'checked' : '')?>> 
						<span id='report_closed_maint'>Show Completed Requests</span></label></td>
				</tr>
			<? } ?>		
			<? if($this->maint_detail_items) { ?>
				<tr>
					<td><span id='report_maint_detail_items'></span></td>
					<td><label><input type='checkbox' name='maint_detail_items' id='maint_detail_items' <?=(isset($_POST['maint_detail_items']) ? 'checked' : '')?>> Show Line Items</label></td>
				</tr>
			<? } ?>			
			
			<? if($this->down_time_hours && $this->down_time_hours_to) { ?>
				<tr>
					<td><span id='report_down_time_hours'>Down Time Hours</span></td>
					<td>From <input name='down_time_hours' id='down_time_hours' value='<?=(isset($_POST['down_time_hours'])? $_POST['down_time_hours'] : "" ) ?>' style='width:70px;'>
					    To <input name='down_time_hours_to' id='down_time_hours_to' value='<?=(isset($_POST['down_time_hours_to'])? $_POST['down_time_hours_to'] : "" ) ?>' style='width:70px;'>
						</td>
				</tr>
			<? } ?>	
			<? if($this->maint_request_cost && $this->maint_request_cost_to) { ?>
				<tr>
					<td><span id='report_maint_request_cost'>Cost</span></td>
					<td>From <input name='maint_request_cost' id='maint_request_cost' value='<?=(isset($_POST['maint_request_cost'])? $_POST['maint_request_cost'] : "" ) ?>' style='width:70px;'>
					    To <input name='maint_request_cost_to' id='maint_request_cost_to' value='<?=(isset($_POST['maint_request_cost_to'])? $_POST['maint_request_cost_to'] : "" ) ?>' style='width:70px;'>
						</td>
				</tr>
			<? } ?>	
			<? if($this->maint_urgent) { ?>
				<tr>
					<td><span id='report_maint_urgent'></span></td>
					<td><label><input type='checkbox' name='maint_urgent' id='maint_urgent' <?=(isset($_POST['maint_urgent']) ? 'checked' : '')?>> Show Urgent Only</label></td>
				</tr>
			<? } ?>
			<? if($this->maint_from_recur) { ?>
				<tr>
					<td><span id='report_maint_from_recur'></span></td>
					<td><label><input type='checkbox' name='maint_from_recur' id='maint_from_recur' <?=(isset($_POST['maint_from_recur']) ? 'checked' : '')?>> Show From Recurring</label></td>
				</tr>
			<? } ?>
			<? if($this->search_notes_files) { ?>
			<?
					$mrr_search_moder=$_POST['search_notes_files'];
				?>
				<tr>
					<td><span id='report_search_notes_files'>Search Category</span></td>
					<td>
						<select name='search_notes_files' id='search_notes_files'>
							<option value='0'<?= ($mrr_search_moder==0 ? " selected" : "") ?>>Notes and Files</option>
							<option value='1'<?= ($mrr_search_moder==1 ? " selected" : "") ?>>Notes Only</option>
							<option value='2'<?= ($mrr_search_moder==2 ? " selected" : "") ?>>Files Only</option>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->search_sort_by) { ?>
			<?
					$mrr_search_sort_by=$_POST['search_sort_by'];
				?>
				<tr>
					<td><span id='report_search_sort_by'>Sort By</span></td>
					<td>
						<select name='search_sort_by' id='search_sort_by'>
							<option value='0'<?= ($mrr_search_sort_by==0 ? " selected" : "") ?>>ID</option>
							<option value='1'<?= ($mrr_search_sort_by==1 ? " selected" : "") ?>>Name</option>
							<option value='2'<?= ($mrr_search_sort_by==2 ? " selected" : "") ?>>Date</option>
							<option value='3'<?= ($mrr_search_sort_by==3 ? " selected" : "") ?>>Type</option>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->search_sort_by_report) { ?>
			<?
					$mrr_search_sort_by_report=$_POST['search_sort_by_report'];
				?>
				<tr>
					<td><span id='report_search_sort_by_report'>Sort By</span></td>
					<td>
						<select name='search_sort_by_report' id='search_sort_by'>
							<option value=''<?= ($mrr_search_sort_by_report=="" ? " selected" : "") ?>>Truck</option>
							<option value='date'<?= ($mrr_search_sort_by_report=="date" ? " selected" : "") ?>>Pickup ETA</option>
							<option value='load'<?= ($mrr_search_sort_by_report=="load" ? " selected" : "") ?>>Load</option>
							<option value='dispatch'<?= ($mrr_search_sort_by_report=="dispatch" ? " selected" : "") ?>>Dispatch</option>
							<option value='driver'<?= ($mrr_search_sort_by_report=="driver" ? " selected" : "") ?>>Driver</option>
							<option value='customer'<?= ($mrr_search_sort_by_report=="customer" ? " selected" : "") ?>>Customer</option>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_only_invoiced) { ?>
				<tr>
					<td><span id='report_show_only_invoiced'></span></td>
					<td><label><input type='checkbox' name='show_only_invoiced' id='show_only_invoiced' <?=(isset($_POST['show_only_invoiced']) ? 'checked' : '')?>> Show only invoiced entries</label></td>
				</tr>
			<? } ?>
							
			<? if($this->mrr_special_run_api) { ?>
				<tr>
					<td></td>
					<td><label><input type='checkbox' name='mrr_run_api' id='mrr_run_api' <?=( $_POST['mrr_run_api'] > 0 ? 'checked' : '')?> value='1'> Use API to get InvAmnt (Slower)</label></td>
				</tr>
			<? } ?>
			<? if($this->group_by_truck) { ?>
				<tr>
					<td><span id='report_group_by_truck'></span></td>
					<td><label><input type='checkbox' name='group_by_truck' id='group_by_truck' <?=(isset($_POST['group_by_truck']) ? 'checked' : '')?>> Group by Truck</label></td>
				</tr>
			<? } ?>
			<? if($this->first_dispatch_all_credit) { ?>
				<tr>
					<td><span id='report_first_dispatch_all_credit'></span></td>
					<td><label><input type='checkbox' name='first_dispatch_all_credit' id='first_dispatch_all_credit' <?=(isset($_POST['first_dispatch_all_credit']) ? 'checked' : '')?>> Give first dispatch all credit</label></td>
				</tr>
			<? } ?>
			<? if($this->hide_non_first_dispatch) { ?>
				<tr>
					<td><span id='report_hide_non_first_dispatch'></span></td>
					<td><label><input type='checkbox' name='hide_non_first_dispatch' id='hide_non_first_dispatch' <?=(isset($_POST['hide_non_first_dispatch']) ? 'checked' : '')?>> Hide all dispatches after first</label></td>
				</tr>
			<? } ?>
			<? if($this->show_deleted_loads) { ?>
				<tr>
					<td><span id='report_show_deleted_loads'></span></td>
					<td><label><input type='checkbox' name='show_deleted_loads' id='show_deleted_loads' <?=(isset($_POST['show_deleted_loads']) ? 'checked' : '')?>> Search deleted loads as well</label></td>
				</tr>
			<? } ?>		
			<? if($this->payroll_mode) { ?>
				<tr>
					<td><span id='report_payroll_mode'>Payroll Mode</span></td>
					<td>
						<select name='payroll_mode' id='payroll_mode'>
							<option value='0'<?= ($_POST['payroll_mode']==0 ? " selected" : "") ?>>Show Charged Values</option>
							<option value='1'<?= ($_POST['payroll_mode']==1 ? " selected" : "") ?>>Show Driver Pay Values</option>
						</select>	
					</td>
				</tr>
			<? } ?>	
			<? if($this->summary_only) { ?>
				<tr>
					<td><span id='report_summary_only'></span></td>
					<td><label><input type='checkbox' name='summary_only' id='summary_only' <?=(isset($_POST['summary_only']) ? 'checked' : '')?> value='1'> Show Summary Only</label></td>
				</tr>
			<? } ?>
			<? if($this->show_late_loads_only) { ?>
				<tr>
					<td><span id='report_late_loads_only'></span></td>
					<td><label><input type='checkbox' name='late_loads_only' id='late_loads_only' <?=(isset($_POST['late_loads_only']) ? 'checked' : '')?> value='1'> Show Late Loads Only (Stops)</label></td>
				</tr>
			<? } ?>			
			<? if($this->show_edi_invoiced) { ?>
				<tr>
					<td><span id='report_edi_invoiced'></span></td>
					<td><label><input type='checkbox' name='report_edi_invoiced' id='report_edi_invoiced' <?=(isset($_POST['report_edi_invoiced']) ? 'checked' : '')?>> Show EDI Invoiced</label></td>
				</tr>
			<? } ?>
			<? if($this->team_choice) { ?>
				<tr>
					<td><span id='report_team_choice'></span></td>
					<td>
						<label><input type='radio' name='team_choice' id='team_choice' value='0' <?=($_POST['team_choice'] == 0 ? 'checked' : '')?>> Show All</label> /
						<label><input type='radio' name='team_choice' id='team_choice' value='1' <?=($_POST['team_choice'] == 1 ? 'checked' : '')?>> Show Teams only</label> /
						<label><input type='radio' name='team_choice' id='team_choice' value='2' <?=($_POST['team_choice'] == 2 ? 'checked' : '')?>> Show Single</label>
					</td>
				</tr>
			<? } ?>	
			<? if($this->log_activity_report_mode) { ?>
				<tr>
					<td><span id='mrr_report_activity_log'>Report Mode</span></td>
					<td>
						<?
							echo mrr_log_activity_report_mode_selector('report_activity_log',$_POST['report_activity_log'],'Select Report Mode');
						?>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_cust_addr1) { ?>
				<tr>
					<td><span id='mrr_show_cust_addr1_display'>Address 1</span></td>
					<td><input type='text' name='mrr_cust_addr1' id='mrr_cust_addr1' value='<?= $_POST['mrr_cust_addr1'] ?>' class='input_normal'></td>
				</tr>
			<? } ?>
			<? if($this->show_cust_addr2) { ?>
				<tr>
					<td><span id='mrr_show_cust_addr2_display'>Address 2</span></td>
					<td><input type='text' name='mrr_cust_addr2' id='mrr_cust_addr2' value='<?= $_POST['mrr_cust_addr2'] ?>' class='input_normal'></td>
				</tr>
			<? } ?>				
			<? if($this->show_cust_city) { ?>
				<tr>
					<td><span id='mrr_show_cust_city_display'>City</span></td>
					<td><input type='text' name='mrr_cust_city' id='mrr_cust_city' value='<?= $_POST['mrr_cust_city'] ?>' class='input_normal'></td>
				</tr>
			<? } ?>
			<? if($this->show_cust_state) { ?>
				<tr>
					<td><span id='mrr_show_cust_state_display'>State</span></td>
					<td><input type='text' name='mrr_cust_state' id='mrr_cust_state' value='<?= $_POST['mrr_cust_state'] ?>' class='input_short'></td>
				</tr>
			<? } ?>
			<? if($this->show_cust_zip) { ?>
				<tr>
					<td><span id='mrr_show_cust_zip_display'>Zip</span></td>
					<td><input type='text' name='mrr_cust_zip' id='mrr_cust_zip' value='<?= $_POST['mrr_cust_zip'] ?>' class='input_short'></td>
				</tr>
			<? } ?>		
			<? if($this->vacation_advance) { ?>
				<tr>
					<td><span id='mrr_report_vacation_advance'>Vacation/Advance</span></td>
					<td>
						<select name='vacation_advance' id='vacation_advance'>
							<option value='0'<?= ($_POST['vacation_advance']==0 ? " selected" : "") ?>>All</option>
							<option value='1'<?= ($_POST['vacation_advance']==1 ? " selected" : "") ?>>Vacation Time</option>
							<option value='2'<?= ($_POST['vacation_advance']==2 ? " selected" : "") ?>>Cash Advances</option>
						</select>
					</td>
				</tr>
			<? } ?>	
			<? if($this->show_font_size) { ?>
				<tr>
					<td><span id='mrr_report_font_display'>Font Size</span></td>
					<td>
						<select name='report_font_display' id='report_font_display'>
							<?
							for($i=8;$i<=24;$i++)
							{
								?>
								<option value='<?= $i ?>'<?= ($_POST['report_font_display']==$i ? " selected" : "") ?>><?= $i ?></option>
								<?
							}
							?>
						</select>
					</td>
				</tr>
			<? } ?>
			<? if($this->show_radius_val) { ?>
				<tr>
					<td><span id='mrr_show_radius_display'>Radius</span></td>
					<td><input type='text' name='mrr_radius' id='mrr_radius' value='<?= $_POST['mrr_radius'] ?>' class='input_short'></td>
				</tr>
			<? } ?>	
			<? if($this->mrr_show_zero_value) { ?>
				<tr>
					<td><span id='mrr_show_zero_values_label'>Show Zero Values</span></td>
					<td><label><input type='checkbox' name='mrr_show_zero_values' id='mrr_show_zero_values' <?=($_POST['mrr_show_zero_values'] > 0 ? 'checked' : '')?> value='1'></label></td>
				</tr>
			<? } ?>	
			<? if($this->mrr_geotab_log_mode) { ?>
				<tr>
					<td><span id='mrr_geotab_log_mode_label'>GeoTab DataFeed Mode</span></td>
					<td>
						<select name='mrr_report_geotab_log_mode' id='mrr_report_geotab_log_mode'>
							<option value='0'<?= ($_POST['mrr_report_geotab_log_mode']==0 ? " selected" : "") ?>>None</option>
							<option value='1'<?= ($_POST['mrr_report_geotab_log_mode']==1 ? " selected" : "") ?>>LogRecord</option>
							<option value='2'<?= ($_POST['mrr_report_geotab_log_mode']==2 ? " selected" : "") ?>>StatusData</option>
							<option value='3'<?= ($_POST['mrr_report_geotab_log_mode']==3 ? " selected" : "") ?>>FaultData</option>
							<option value='4'<?= ($_POST['mrr_report_geotab_log_mode']==4 ? " selected" : "") ?>>Trip</option>
							<option value='5'<?= ($_POST['mrr_report_geotab_log_mode']==5 ? " selected" : "") ?>>ExceptionEvent</option>
							<option value='6'<?= ($_POST['mrr_report_geotab_log_mode']==6 ? " selected" : "") ?>>DutyStatusLog</option>
							<option value='7'<?= ($_POST['mrr_report_geotab_log_mode']==7 ? " selected" : "") ?>>AnnotationLog</option>
							<option value='8'<?= ($_POST['mrr_report_geotab_log_mode']==8 ? " selected" : "") ?>>DVIRLog</option>
							<option value='9'<?= ($_POST['mrr_report_geotab_log_mode']==9 ? " selected" : "") ?>>ShipmentLog</option>
							<option value='10'<?= ($_POST['mrr_report_geotab_log_mode']==10 ? " selected" : "") ?>>TrailerAttachment</option>
							<option value='11'<?= ($_POST['mrr_report_geotab_log_mode']==11 ? " selected" : "") ?>>IoxAddOn</option>
							<option value='12'<?= ($_POST['mrr_report_geotab_log_mode']==12 ? " selected" : "") ?>>CustomData</option>
						</select>
					</td>					
				</tr>
			<? } ?>	
			<? if($this->mrr_geotab_diagnostic) { ?>
				<tr>
					<td><span id='mrr_geotab_diagnostic_label'>GeoTab Diagnostic Mode</span></td>
					<td>
						<select name='mrr_report_geotab_diagnostic_mode' id='mrr_report_geotab_diagnostic_mode'>
							<option value='0'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==0 ? " selected" : "") ?>>All Diagnostics Available</option>
							<option value='1'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==1 ? " selected" : "") ?>>Odometer Reading</option>
							<option value='2'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==2 ? " selected" : "") ?>>Odometer</option>
							<option value='3'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==3 ? " selected" : "") ?>>Raw Odometer</option>
							<option value='4'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==4 ? " selected" : "") ?>>Acceleration Forward Braking</option>
							<option value='5'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==5 ? " selected" : "") ?>>Acceleration Side To Side</option>
							<option value='6'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==6 ? " selected" : "") ?>>Acceleration Up Down</option>
							<option value='7'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==7 ? " selected" : "") ?>>Cranking Voltage</option>
							<option value='8'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==8 ? " selected" : "") ?>>Cruise Control Active</option>
							<option value='9'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==9 ? " selected" : "") ?>>Diesel Exhaust Fluid</option>
							
							<option value='10'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==10 ? " selected" : "") ?>>Device Total Idle Fuel</option>
							<option value='11'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==11 ? " selected" : "") ?>>Device Total Fuel</option>
							<option value='12'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==12 ? " selected" : "") ?>>Coolant Level</option>
							<option value='13'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==13 ? " selected" : "") ?>>Engine Coolant Temperature</option>
							<option value='14'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==14 ? " selected" : "") ?>>Engine Oil Temperature</option>
							<option value='15'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==15 ? " selected" : "") ?>>Engine Hours</option>
							<option value='16'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==16 ? " selected" : "") ?>>Engine Speed</option>
							<option value='17'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==17 ? " selected" : "") ?>>Engine Road Speed</option>
							<option value='18'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==18 ? " selected" : "") ?>>Fuel Level</option>
							<option value='19'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==19 ? " selected" : "") ?>>Gear Position</option>
							
							<option value='20'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==20 ? " selected" : "") ?>>GoDevice Voltage</option>
							<option value='21'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==21 ? " selected" : "") ?>>Harness Detected 9 Pin</option>
							<option value='22'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==22 ? " selected" : "") ?>>Ignition Timing</option>
							<option value='23'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==23 ? " selected" : "") ?>>J1708 Engine Protocol Detected</option>
							<option value='24'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==24 ? " selected" : "") ?>>J1939 Can Engine Protocol Detected</option>
							<option value='25'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==25 ? " selected" : "") ?>>Outside Temperature</option>
							<option value='26'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==26 ? " selected" : "") ?>>Parking Brake</option>
							<option value='27'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==27 ? " selected" : "") ?>>Position Valid</option>
							<option value='28'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==28 ? " selected" : "") ?>>Total Fuel Used</option>
							<option value='29'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==29 ? " selected" : "") ?>>Total PTO Hours</option>
							
							<option value='30'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==30 ? " selected" : "") ?>>Total Idle Hours</option>
							<option value='31'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==31 ? " selected" : "") ?>>Total Idle Fuel Used</option>
							<option value='32'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==32 ? " selected" : "") ?>>Total Trip Idle Fuel Used</option>
							<option value='33'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==33 ? " selected" : "") ?>>Total Trip Fuel Used</option>
							<option value='34'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==34 ? " selected" : "") ?>>Vehicle Active</option>
							<option value='35'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==35 ? " selected" : "") ?>>Vehicle Programmed Cruise High Speed Limit</option>
							<option value='36'<?= ($_POST['mrr_report_geotab_diagnostic_mode']==36 ? " selected" : "") ?>>Vehicle Programmed Maximum Road Speed Limit</option>
						</select>
					</td>					
				</tr>
			<? } ?>	
			<? if($this->mrr_driver_mode) { ?>
				<tr>
					<td><span id='mrr_driver_mode_label'>Driver Report Mode</span></td>
					<td>
						<select name='mrr_report_driver_mode' id='mrr_report_driver_mode'>
							<option value='0'<?= ($_POST['mrr_report_driver_mode']==0 ? " selected" : "") ?>>All Drivers</option>
							<option value='1'<?= ($_POST['mrr_report_driver_mode']==1 ? " selected" : "") ?>>Hired Only</option>
							<option value='2'<?= ($_POST['mrr_report_driver_mode']==2 ? " selected" : "") ?>>Fired Only</option>
							<option value='3'<?= ($_POST['mrr_report_driver_mode']==3 ? " selected" : "") ?>>Hired or Fired</option>
						</select>
					</td>
				</tr>
			<? } ?>	
			<? if($this->mrr_show_num_range) { ?>
				<tr>
					<td><span id='mrr_show_num_range_label'>Range</span></td>
					<td>
						From <input type='text' name='mrr_show_num_range_min' id='mrr_show_num_range_min' value='<?=$_POST['mrr_show_num_range_min'] ?>' size='5'>
						To <input type='text' name='mrr_show_num_range_max' id='mrr_show_num_range_max' value='<?=$_POST['mrr_show_num_range_max'] ?>' size='5'>
					</td>
				</tr>
			<? } ?>
			
			<? if($this->mrr_show_num_range1) { ?>
				<tr>
					<td><span id='mrr_show_num_range1_label'>Group Range 1</span></td>
					<td>
						Min <input type='text' name='mrr_show_num_range1_min' id='mrr_show_num_range1_min' value='<?=$_POST['mrr_show_num_range1_min'] ?>' size='5'>
						Max <input type='text' name='mrr_show_num_range1_max' id='mrr_show_num_range1_max' value='<?=$_POST['mrr_show_num_range1_max'] ?>' size='5'>
					</td>
				</tr>
			<? } ?>
			<? if($this->mrr_show_num_range2) { ?>
				<tr>
					<td><span id='mrr_show_num_range2_label'>Group Range 2</span></td>
					<td>
						Min <input type='text' name='mrr_show_num_range2_min' id='mrr_show_num_range2_min' value='<?=$_POST['mrr_show_num_range2_min'] ?>' size='5'>
						Max <input type='text' name='mrr_show_num_range2_max' id='mrr_show_num_range2_max' value='<?=$_POST['mrr_show_num_range2_max'] ?>' size='5'>
					</td>
				</tr>
			<? } ?>
			<? if($this->mrr_show_num_range3) { ?>
				<tr>
					<td><span id='mrr_show_num_range3_label'>Group Range 3</span></td>
					<td>
						Min <input type='text' name='mrr_show_num_range3_min' id='mrr_show_num_range3_min' value='<?=$_POST['mrr_show_num_range3_min'] ?>' size='5'>
						Max <input type='text' name='mrr_show_num_range3_max' id='mrr_show_num_range3_max' value='<?=$_POST['mrr_show_num_range3_max'] ?>' size='5'>
					</td>
				</tr>
			<? } ?>
			<? if($this->mrr_show_num_range4) { ?>
				<tr>
					<td><span id='mrr_show_num_range4_label'>Group Range 4</span></td>
					<td>
						Min <input type='text' name='mrr_show_num_range4_min' id='mrr_show_num_range4_min' value='<?=$_POST['mrr_show_num_range4_min'] ?>' size='5'>
						Max <input type='text' name='mrr_show_num_range4_max' id='mrr_show_num_range4_max' value='<?=$_POST['mrr_show_num_range4_max'] ?>' size='5'>
					</td>
				</tr>
			<? } ?>
			<? if($this->mrr_show_num_range5) { ?>
				<tr>
					<td><span id='mrr_show_num_range5_label'>Group Range 5</span></td>
					<td>
						Min <input type='text' name='mrr_show_num_range5_min' id='mrr_show_num_range5_min' value='<?=$_POST['mrr_show_num_range5_min'] ?>' size='5'>
						Max <input type='text' name='mrr_show_num_range5_max' id='mrr_show_num_range5_max' value='<?=$_POST['mrr_show_num_range5_max'] ?>' size='5'>
					</td>
				</tr>
			<? } ?>
			
			<? if($this->mrr_show_num_haul1) { ?>
				<tr>
					<td><span id='mrr_show_num_haul1_label'>Short Haul</span></td>
					<td>
						<!---
						Min <input type='text' name='mrr_show_num_haul1_min' id='mrr_show_num_haul1_min' value='<?=$_POST['mrr_show_num_haul1_min'] ?>' size='5'>
						---->
						Max <input type='text' name='mrr_show_num_haul1_max' id='mrr_show_num_haul1_max' value='<?=$_POST['mrr_show_num_haul1_max'] ?>' size='5'>
					</td>
				</tr>
			<? } ?>
			<? if($this->mrr_show_num_haul2) { ?>
				<tr>
					<td><span id='mrr_show_num_haul2_label'>Long Haul</span></td>
					<td>
						Min <input type='text' name='mrr_show_num_haul2_min' id='mrr_show_num_haul2_min' value='<?=$_POST['mrr_show_num_haul2_min'] ?>' size='5'>
						Max <input type='text' name='mrr_show_num_haul2_max' id='mrr_show_num_haul2_max' value='<?=$_POST['mrr_show_num_haul2_max'] ?>' size='5'>
					</td>
				</tr>
			<? } ?>
			
			
			<? if($this->mrr_show_misc_value) { ?>
				<tr>
					<td><span id='mrr_show_misc_values_label'><?=$_POST['mrr_misc_value_label'] ?></span></td>
					<td><label><input type='checkbox' name='mrr_show_misc_values' id='mrr_show_misc_values' <?=($_POST['mrr_show_misc_values'] > 0 ? 'checked' : '')?> value='1'></label></td>
				</tr>
			<? } ?>	
			<? if($this->mrr_excel_print_flag) { ?>
				<tr>
					<td><label for='mrr_excel_print_file'>Use Excel File</label></td>
					<td>						
						<input type='checkbox' name='mrr_excel_print_file' id='mrr_excel_print_file' <?=(isset($_POST['mrr_excel_print_file']) ? 'checked' : '')?> value='1'>
					</td>
				</tr>			
			<? } ?>
			<? if($this->mrr_send_email_here) { ?>
				<tr>
					<td><span id='mrr_report_send_email'>Email Address</span></td>
					<td>
						<input type='text' name='mrr_email_addr' id='mrr_email_addr' value='<?=$_POST['mrr_email_addr']?>' class='input_normal'>
						<? if($use_bootstrap==true) { ?>
							<button type='submit' name='mrr_email_report' id='mrr_email_report' class='btn btn-primary'><span class='glyphicon glyphicon-envelope'></span> Send</button>
						<? }	else	{ ?>
							<input type='submit' name='mrr_email_report' id='mrr_email_report' value='Send'>
						<? } ?>
						
					</td>
				</tr>
				<tr>
					<td><span id='mrr_report_send_email_name'>Contact Name</span></td>
					<td>
						<input type='text' name='mrr_email_addr_name' id='mrr_email_addr_name' value='<?=$_POST['mrr_email_addr_name']?>' class='input_normal'>
						(Optional)
					</td>
				</tr>
			<? } ?>
			<tr>
				<td></td>
				<td>
					<? if($this->mrr_hide_submit_button) {	?> 
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
					<? }	else	{ ?> 
						<? if($use_bootstrap==true) { ?>
							<button type='submit' class='btn btn-success'><span class='glyphicon glyphicon-print'></span> Submit</button>
						<? }	else	{ ?> 
							<input type='submit' value='Submit'> 
						<? } ?>
					<? } ?>
					<? if($this->mrr_special_print_button) {	?>
						<!--use special Print function only.-->						
						<? if($use_bootstrap==true) { ?>
							<button name='print' class='btn btn-info' onClick='mrr_print_report();'><span class='glyphicon glyphicon-print'></span> Print</button>
						<? }	else	{ ?>
							<input type='button' value='Print' name='print' onClick='mrr_print_report();'>
						<? } ?>
					<? }	else	{ ?>
						<? if($use_bootstrap==true) { ?>
							<button type='submit' name='print' class='btn btn-info'><span class='glyphicon glyphicon-print'></span> Print</button>
						<? }	else	{ ?>
							<input type='submit' value='Print' name='print'>
						<? } ?>
					<? }	?>					
				</td>
			</tr>
			</table>
			
			<? if($this->mrr_no_form_enclosed) { ?>
				&nbsp;<br>
			<? }	else	{ ?>
			 	</form>
			<? } ?>		
			<? if($use_bootstrap==true) { ?>		
     			<style type="text/css">
                    .ui-datepicker select {                        
                        color: #333333;
                    }               
                    </style>
			<? } ?>
			<script type='text/javascript'>
				$('.report_datepicker').datepicker();
				$('.date_picker_rates').datepicker();
				
				$('#report_consignee').autocomplete('ajax.php?mrr=2&cmd=search_stop_address',{formatItem:formatItem});
				$('#report_shipper').autocomplete('ajax.php?mrr=1&cmd=search_stop_address',{formatItem:formatItem});
				$('#shipper_name').autocomplete('ajax.php?mrr=0&cmd=search_stop_address',{formatItem:formatItem});
				
				
               	$().ready(function() {
               		<?
               		if(isset($_POST['report_font_display'])) {
               			echo " $('.font_display_section, .font_display_section a, .section_heading').css('font-size','".$_POST['report_font_display']."px'); ";
               		}
               		?>
               	});
			</script>
			<?
		}
	}
		
	function get_unique_filename($dir, $file) {
		// check to see if the file exists, if so, loop through until we get a unique filename
		
		$file_ext = get_file_ext($file);
		$file_base = str_replace(".$file_ext","",$file);
		
		if(!file_exists($dir.$file)) {
			$new_filename = $file;
		} else {
			for($i=1;$i<99999;$i++) {
				$new_filename = $file_base."_".$i.".$file_ext";
				if(!file_exists($dir.$new_filename)) break;
			}
		}
		
		return $new_filename;
	}
	
	function update_replacement_flag($equipment_id, $equipment_type_id) {
		$sql = "
			select replacement,
				replacement_xref_id
			
			from equipment_history
			where equipment_id = '".sql_friendly($_GET['id'])."'
				and equipment_type_id = '".sql_friendly($equipment_type_id)."'
				and deleted = 0
				and linedate_returned = 0
				and linedate_aquired > 0
				and replacement > 0
		";
		$data = simple_query($sql);
		if(mysqli_num_rows($data)) {
			$replacement_flag = 1;
		} else {
			$replacement_flag = 0;
		}
		
		if($equipment_type_id == 1) {
			$sql = "
				update trucks
				set replacement = '".sql_friendly($replacement_flag)."'
				where id = '".sql_friendly($equipment_id)."'
			";
			simple_query($sql);
		} else if($equipment_type_id == 2) {
			$sql = "
				update trailers
				set replacement = '".sql_friendly($replacement_flag)."'
				where id = '".sql_friendly($equipment_id)."'
			";
			simple_query($sql);
		}
	}
	
	function update_equipment_active_flag($equipment_id, $equipment_type_id) {
		// look through our equipment history log to figure out if this equipment is active or not
		
		$sql = "
			select count(*) as active_flag
			
			from equipment_history
			where equipment_id = '".sql_friendly($_GET['id'])."'
				and equipment_type_id = '".sql_friendly($equipment_type_id)."'
				and deleted = 0
				and linedate_returned = 0
				and linedate_aquired > 0
		";
		$data_active_check = simple_query($sql);
		$row_active_check = mysqli_fetch_array($data_active_check);
		$active_flag = $row_active_check['active_flag'];
		
		if($equipment_type_id == 1) {
			$sql = "
				update trucks
				set active = '".sql_friendly($active_flag)."'
				where id = '".sql_friendly($equipment_id)."'
			";
			simple_query($sql);
		} else if($equipment_type_id == 2) {
			$sql = "
				update trailers
				set active = '".sql_friendly($active_flag)."'
				where id = '".sql_friendly($equipment_id)."'
			";
			simple_query($sql);
		}
	}
	function mrr_update_truck_active_flag($id,$flag)
	{
		$sql = "
				update trucks set
					active = '".sql_friendly($flag)."'
				where id = '".sql_friendly($id)."'
			";
		simple_query($sql);	
	}
	function mrr_update_trailer_active_flag($id,$flag)
	{
		$sql = "
				update trailers set
					active = '".sql_friendly($flag)."'
				where id = '".sql_friendly($id)."'
			";
		simple_query($sql);	
		
		if($flag==0)
		{	//trailer is inactive...returned.  So complete all dropped trailers...	
			$sql = "
				update trailers_dropped set
					drop_completed=1,
					linedate_completed=NOW()
				where trailer_id = '".sql_friendly($id)."'
					and drop_completed=0
			";
			simple_query($sql);	
		}
	}
	function get_active_truck_count() {
		$counter=0;
		
		$sql = "
			select count(*) as active_truck_count
			
			from trucks
			where active = 1
				and replacement = 0
				and no_insurance = 0
				and active_cnt_exclude=0
				and deleted = 0
		";
		$data = simple_query($sql);
		
		$row = mysqli_fetch_array($data);
		$counter=$row['active_truck_count'];
		
		$replaced=0;
		/*
		$sql = "
			select trucks.id			
			from trucks,equipment_history
			where trucks.active = 1
				and trucks.id=equipment_history.replacement_xref_id
				and trucks.replacement = 0
				and trucks.deleted = 0
				and equipment_history.replacement = 1
				and equipment_history.deleted = 0
				and equipment_history.equipment_type_id = 1
				and equipment_history.linedate_returned='0000-00-00 00:00:00'
				
		";
		$data = simple_query($sql);		
		while($row = mysqli_fetch_array($data))
		{
			$replaced++;
		}
		*/		
		$counter=$counter-$replaced;		
		return $counter;
	}
	
	function get_active_truck_count_exclude() {
		$counter=0;
		
		$sql = "
			select count(*) as active_truck_count
			
			from trucks
			where active = 1
				and replacement = 0
				and no_insurance = 0
				and active_cnt_exclude > 0
				and deleted = 0
		";
		$data = simple_query($sql);
		
		$row = mysqli_fetch_array($data);
		$counter=$row['active_truck_count'];
		
		return $counter;
	}
	function get_active_trailer_count() {
		$counter=0;
		
		$sql = "
			select count(*) as active_count
			
			from trailers
			where active = 1
								
				and deleted = 0
		";	//and rental_flag=0
			//and no_insurance = 0
		$data = simple_query($sql);
		
		$row = mysqli_fetch_array($data);
		$counter=$row['active_count'];
		
		return $counter;
	}
	function get_active_truck_count_ranged($date_start,$date_end)
	{
		
		//$date_start = strtotime(date("m/1/Y", strtotime($date_start)));
		//$date_end = strtotime(date("m/1/Y", strtotime($date_end)));
		
		$html="";
		
		$html.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
		$html.="
			<tr>
					<td valign='top'><b>#</b></td>					
					<td valign='top'><b>Aquired</b></td>
					<td valign='top'><b>Returned</b></td>
					<td valign='top'><b>Active</b></td>	
					<td valign='top'><b>Truck</b></td>
					<td valign='top' align='right'><b>Comp Owned</b></td>
					<td valign='top' align='right'><b>Lease From</b></td>
					<td valign='top' align='right'><b>Rent</b></td>							
					<td valign='top' align='right'><b>Excl Insur</b></td>
					<td valign='top' align='right'><b>EquipID</b></td>
					<td valign='top' align='right'><b>Replaced</b></td>
					<td valign='top' align='right'><b>EquipVal</b></td>
					<td valign='top' align='right'><b>Cost</b></td>
			</tr>
		";
		
		$cur_active=0;
		$comp_cntr=0;
		$rental_cntr=0;
		$lease_cntr=0;
		$replace_cntr=0;
		$exclue_ins_cntr=0;
		$cost_tot=0;
		
		$sql = "
			select equipment_id,
				trucks.*,
				equipment_history.linedate_aquired,
				equipment_history.replacement,
				(select name_truck from trucks tk where tk.id=equipment_history.replacement_xref_id) as truck_namer,
				equipment_history.linedate_returned,
				equipment_history.equipment_value,
					(
						select equipment_id
						
						from equipment_history eh
						where eh.deleted = 0
							and eh.replacement_xref_id = trucks.id
							and eh.linedate_aquired <= '".date("Y-m-d", strtotime($date_end))." 23:59:59'
							and (
								eh.linedate_returned = 0
								or eh.linedate_returned >= '".date("Y-m-d", strtotime($date_start))." 00:00:00'
								)
						limit 1
					) as replacement_truck_id
			
			from equipment_history, trucks
			where equipment_type_id = 1
				and equipment_history.linedate_aquired <= '".date("Y-m-d", strtotime($date_end))." 23:59:59'
				and (
					equipment_history.linedate_returned = 0
					or equipment_history.linedate_returned >= '".date("Y-m-d", strtotime($date_start))." 00:00:00'
					)
				and equipment_history.deleted = 0
				and trucks.deleted = 0	
				and trucks.no_insurance = 0			
				and trucks.id = equipment_history.equipment_id

				
			order by trucks.truck_year, trucks.truck_make, trucks.name_truck
		";	//replacement_xref_id
		$data_trucks = simple_query($sql);
		
		$counter=0;
		$billable_trucks = 0;
		$total_value = 0;
		$monthly_value = 0;
		$replaced_trucks = 0;
		
		$rent_value=0;
		$lease_value=0;
		$comp_value=0;
		
		while($row_truck = mysqli_fetch_array($data_trucks)) {
			$replaced="";
			
			if($row_truck['replacement'] > 0) {	//replacement_truck_id
				$replaced_trucks++;
				$replaced="<a href='admin_trucks.php?id=".$row_truck['replacement_truck_id']."' target='_blank'>".$row_truck['truck_namer']."</a>";
			} else {
				$billable_trucks++;
				$total_value += $row_truck['equipment_value'];
				$monthly_value +=  $row_truck['monthly_cost'];				
			}
			$counter++;
			
			$aq_mask=date("m/d/Y",strtotime($row_truck['linedate_aquired']));				if($aq_mask=="12/31/1969") 	$aq_mask="";
			$re_mask=date("m/d/Y",strtotime($row_truck['linedate_returned']));				if($re_mask=="12/31/1969")	$re_mask="";
			
			$cur_active+=$row_truck['active'];
			$comp_cntr+=$row_truck['company_owned'];			
			$rental_cntr+=$row_truck['rental'];
			
			if($row_truck['rental']==0 && $row_truck['company_owned']==0 && $row_truck['replacement'] == 0)	
			{
				$lease_cntr++;
				$lease_value+=$row_truck['monthly_cost'];
			}
			elseif($row_truck['rental'] > 0 && $row_truck['replacement'] == 0)
			{
				$rent_value+=$row_truck['monthly_cost'];
			}
			elseif($row_truck['company_owned'] > 0 && $row_truck['replacement'] == 0)
			{
				$comp_value+=$row_truck['monthly_cost'];	
			}
			
			$replace_cntr+=$row_truck['replacement'];
			$exclue_ins_cntr+=$row_truck['insurance_exclude'];
			//$cost_tot+=$row_truck['monthly_cost'];
			
			$tag1="";					$tag2="";
			if(trim($replaced)!="")	
			{	
				$tag1="<span style='color:orange;'>";		
				$tag2="</span>"; 
			}
			else
			{
				$cost_tot+=$row_truck['monthly_cost'];	
			}
									
			$html.="
				<tr class='".($counter%2==0 ? "even" : "odd")."'>
					<td valign='top'>".$counter."</td>					
					<td valign='top'>".$aq_mask."</td>
					<td valign='top'>".$re_mask."</td>
					<td valign='top'>".$row_truck['active']."</td>
					<td valign='top'><a href='admin_trucks.php?id=".$row_truck['id']."' target='_blank'>".$row_truck['name_truck']."</a></td>
					<td valign='top' align='right'>".$row_truck['company_owned']."</td>
					<td valign='top' align='right'>".$row_truck['leased_from']."</td>
					<td valign='top' align='right'>".$row_truck['rental']."</td>						
					<td valign='top' align='right'>".$row_truck['insurance_exclude']."</td>
					<td valign='top' align='right'>".$row_truck['equipment_id']."</td>					
					<td valign='top' align='right'>".$replaced."</td>
					<td valign='top' align='right'>$".number_format($row_truck['equipment_value'],0)."</td>
					<td valign='top' align='right'>".$tag1."$".number_format($row_truck['monthly_cost'],0)."".$tag2."</td>
				</tr>
			";	//$row_truck['replacement_truck_id']
			
		}
		
		$html.="
				<tr bgcolor='#FFFFFF'>
					<td valign='top'>&nbsp;</td>					
					<td valign='top'>Totals</td>
					<td valign='top'>&nbsp;</td>
					<td valign='top'>".$cur_active."</td>
					<td valign='top'>&nbsp;</td>
					<td valign='top' align='right'>".$comp_cntr."</td>
					<td valign='top' align='right'>".$lease_cntr."</td>
					<td valign='top' align='right'>".$rental_cntr."</td>									
					<td valign='top' align='right'>".$exclue_ins_cntr."</td>
					<td valign='top'>&nbsp;</td>				
					<td valign='top' align='right'>".$replaced_trucks."</td>
					<td valign='top'>&nbsp;</td>
					<td valign='top' align='right'>$".number_format($cost_tot,0)."</td>
				</tr>
			";
		
		$html.="</table>";
		
		$res['sql']=$sql;
		$res['date_start']=$date_start;
		$res['date_end']=$date_end;
		$res['trucks']=$counter;
		$res['html']=$html;
		$res['billable']=$billable_trucks;
		$res['replaced']=$replaced_trucks;
		$res['total_value']=$total_value;	
		$res['monthly_value']=$monthly_value;
		$res['rent_value']=$rent_value;
		$res['lease_value']=$lease_value;
		$res['comp_value']=$comp_value;
		return $res;
	}
	function get_active_trailer_count_ranged($date_start,$date_end)
	{	//copy of truck function converted for trailers....
		
		//$date_start = strtotime(date("m/1/Y", strtotime($date_start)));
		//$date_end = strtotime(date("m/1/Y", strtotime($date_end)));
		
		// get the number of trailers that were active for that date range
		$sql = "
			select equipment_id,
				trailers.*,
				equipment_history.linedate_aquired,
				equipment_history.linedate_returned,
				equipment_history.equipment_value,
					(
						select equipment_id
						
						from equipment_history eh
						where eh.deleted = 0
							and eh.replacement_xref_id = trailers.id
							and eh.linedate_aquired <= '".date("Y-m-d", strtotime($date_end))." 23:59:59'
							and (
								eh.linedate_returned = 0
								or eh.linedate_returned >= '".date("Y-m-d", strtotime($date_start))." 00:00:00'
								)
						limit 1
					) as replacement_trailer_id
			
			from equipment_history, trailers
			where equipment_type_id = 2
				and equipment_history.linedate_aquired <= '".date("Y-m-d", strtotime($date_end))." 23:59:59'
				and (
					equipment_history.linedate_returned = 0
					or equipment_history.linedate_returned >= '".date("Y-m-d", strtotime($date_start))." 00:00:00'
					)
				and equipment_history.deleted = 0
				and trailers.deleted = 0							
				and trailers.id = equipment_history.equipment_id
				
			order by trailers.trailer_year, trailers.trailer_make, trailers.trailer_name
		";
		/*
		and trucks.no_insurance = 0	
		*/
		$data_trucks = simple_query($sql);
		
		$counter=0;
		$billable_trucks = 0;
		$total_value = 0;
		$monthly_value = 0;
		$replaced_trucks = 0;
				
		$rent_value=0;
		$lease_value=0;
		$comp_value=0;
		
		while($row_truck = mysqli_fetch_array($data_trucks)) {
			if($row_truck['replacement_trailer_id'] > 0) {
				$replaced_trucks++;
				$counter++;
			} else {
				$billable_trucks++;
				$total_value += $row_truck['equipment_value'];
				$monthly_value +=  $row_truck['monthly_cost_actual'];
				$counter++;
			}
			
			if($row_truck['rental_flag']==0 && $row_truck['company_owned']==0)	
			{
				$lease_value+=$row_truck['monthly_cost_actual'];
			}
			elseif($row_truck['rental_flag'] > 0)
			{
				$rent_value+=$row_truck['monthly_cost_actual'];
			}
			elseif($row_truck['company_owned'] > 0)
			{
				$comp_value+=$row_truck['monthly_cost_actual'];	
			}
			
		}
		$res['sql']=$sql;
		$res['date_start']=$date_start;
		$res['date_end']=$date_end;
		$res['trailers']=$counter;
		$res['billable']=$billable_trucks;
		$res['replaced']=$replaced_trucks;
		$res['total_value']=$total_value;	
		$res['monthly_value']=$monthly_value;
		$res['rent_value']=$rent_value;
		$res['lease_value']=$lease_value;
		$res['comp_value']=$comp_value;
		return $res;
	}
	
	function verify_index($table_name, $index_name) {
		// function to make sure an index exists on a table 
		// returns true if it does, false if it doesn't
		
		$sql = "show index from $table_name";
		$data = simple_query($sql);
		
		while($row = mysqli_fetch_array($data)) {
			if(strtolower($row['Key_name']) == strtolower($index_name)) return true;
		}
		return false;
	}
	
	function get_load_id_from_dispatch_id($dispatch_id) {
		// get the load id
		$sql = "
			select load_handler_id
			
			from trucks_log
			where id = '".sql_friendly($dispatch_id)."'
		";
		$data_load_id = simple_query($sql);
		$row_load_id = mysqli_fetch_array($data_load_id);
		
		return $row_load_id['load_handler_id'];
	}
	
	function mrr_get_holiday_option_list()
	{
		$holidays= array();
		$sql = "
			select fvalue 					
			from option_values
			left join option_cat on option_cat.id= option_values.cat_id
			where option_values.deleted = 0
				and option_cat.cat_name='company_holidays'
				and option_cat.deleted = 0
			";
		$data_days = simple_query($sql);
		while($row_days=mysqli_fetch_array($data_days))
		{
			$holidays[]="".trim($row_days['fvalue'])."";
		}
		return $holidays;	
	}
	
	function mrr_get_truck_from_load_id($load_id)
	{		
		$truck_ids[0]=0;
		$cntr=0;
		if($load_id > 0)
		{		
     		$sql = "
     			select truck_id					
     			from trucks_log
     			where load_handler_id='".sql_friendly($load_id)."'
     				and deleted = 0
     			";
     		$data = simple_query($sql);
     		while($row=mysqli_fetch_array($data))
     		{
     			$truck_ids[$cntr]=$row['truck_id'];
     			$cntr++;
     		}
		}
		return $truck_ids;	
	}
	
	function get_days_available($date_start, $date_end, $truck_id = 0,$load_id=0) 
	{
		$truck_list="";
		if($load_id > 0)
		{
			$tarr=mrr_get_truck_from_load_id($load_id);
			if(count($tarr)==1 && $truck_id==0)
			{
				$truck_id=$tarr[0];
			}
			elseif(count($tarr) > 0)
			{
				$truck_list=" and (";	
				for($i=0;$i < count($tarr); $i++)
				{
					if($i==0)
					{
						$truck_list.="trucks.id = '".sql_friendly($tarr[0])."' ";	
					}
					else
					{
						$truck_list.=" or trucks.id = '".sql_friendly($tarr[ $i ])."' ";	
					}
				}				
				$truck_list.=") ";	
				
				$truck_id=0;		//override the single truck since the list will take over...
			}
		}
		
		global $defaultsarray;
		
		if($date_end > time()) $date_end = strtotime(date("m/d/Y"));
		
		$days_in_month = date("t", $date_start);
		$billable_days = 0;
		$billable_days_so_far = 0;
		$days_available = 0;
		$days_available_so_far = 0;
		$days_detailed_html = '';
		
		$mrr_holidays= mrr_get_holiday_option_list();
		
		$date_start_original = $date_start;
		
		$mrr_sql="";
		
		for($i=0;$i< 1100;$i++) 
		{
			if(strtotime("$i day", $date_start) > $date_end) 		break;
			//echo date("m/d/Y", $date_start)."<br>";
			$day_of_week = date("w", strtotime("$i day", $date_start));			
			
			$is_holiday=in_array("".date("Y-m-d", strtotime("$i day", $date_start))."" , $mrr_holidays);			
			
			if($day_of_week != 0 && $day_of_week != 6 && $is_holiday==false) 
			{
				// this is a billable day, see how many trucks we had on this exact day
				$sql = "
					select count(*) as trucks_available
					
					from equipment_history, trucks 
					where equipment_type_id = 1 
						and trucks.deleted = 0
						and trucks.id = equipment_history.equipment_id
						and equipment_history.deleted = 0
						and equipment_history.replacement = 0
						and equipment_history.linedate_aquired <= '".date("Y-m-d", strtotime("$i day", $date_start))."'
						and (
							equipment_history.linedate_returned = '0000-00-00'
							or equipment_history.linedate_returned > '".date("Y-m-d", strtotime("$i day", $date_start))."'
						)
						".($truck_id > 0 ? " and trucks.id = '".sql_friendly($truck_id)."' " : "")."
						".$truck_list."
				";
				$data_count = simple_query($sql);
				$row_count = mysqli_fetch_array($data_count);
				
				$mrr_sql.="<br>".$sql;

				if(strtotime("$i day", $date_start) <= $date_end) 
				{
					$billable_days_so_far++;
					$days_available_so_far += $row_count['trucks_available'];
					$days_detailed_html .= "
						<tr>
							<td>".date("m/d/Y", strtotime("$i day", $date_start))."</td>
							<td>". $row_count['trucks_available']."</td>
						</tr>
					";
				}
				$billable_days++;
				
				
				$days_available += $row_count['trucks_available'];
			}
			
		}
		
		//$billable_days = (int) $defaultsarray['billable_days_in_month'];
		
		//$billable_days_so_far = (int) $defaultsarray['billable_days_in_month'];
		
		$rarray['days_detailed_html'] = $days_detailed_html;
		$rarray['billable_days'] = $billable_days;
		$rarray['billable_days_so_far'] = $billable_days_so_far;
		$rarray['days_available'] = $days_available;
		$rarray['days_available_so_far'] = $days_available_so_far;
		$rarray['sql'] = "Load ".$load_id."<br>".$mrr_sql;
		
		return $rarray;
	}
	
	function get_days_run($date_start, $date_end, $truck_id = 0,$load_id = 0) 
	{
		$truck_list="";
		if($load_id > 0)
		{
			$tarr=mrr_get_truck_from_load_id($load_id);
			if(count($tarr)==1 && $truck_id==0)
			{
				$truck_id=$tarr[0];
			}
			elseif(count($tarr) > 0)
			{
				$truck_list=" and (";	
				for($i=0;$i < count($tarr); $i++)
				{
					if($i==0)
					{
						$truck_list.="trucks_log.truck_id = '".sql_friendly($tarr[0])."' ";	
					}
					else
					{
						$truck_list.=" or trucks_log.truck_id = '".sql_friendly($tarr[ $i ])."' ";	
					}
				}				
				$truck_list.=") ";	
				
				$truck_id=0;		//override the single truck since the list will take over...
			}
		}
		
		global $defaultsarray;
		//(trucks_log.hours_worked / $defaultsarray[local_driver_workweek_hours])
		$sql = "
			select sum((trucks_log.daily_run_otr + trucks_log.daily_run_hourly)) as days_run
			
			from trucks_log, load_handler
			where trucks_log.deleted = 0
				and load_handler.deleted = 0
				and trucks_log.load_handler_id = load_handler.id
				and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", $date_start)." 00:00:00'
				and trucks_log.linedate_pickup_eta <= '".date("Y-m-d", $date_end)." 23:59:59'
				".($truck_id > 0 ? " and trucks_log.truck_id = '".sql_friendly($truck_id)."' " : "")."
				".$truck_list."
		";
		//and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') >= '".date("Y-m-d", $date_start)."'
		//and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') < '".date("Y-m-d", $date_end)."'		
		
		//echo $sql;
		$data_actual = simple_query($sql);
		$row_actual = mysqli_fetch_array($data_actual);
		
		$days_actual = $row_actual['days_run'];
		//$days_actual = ceil($days_actual);
		return $days_actual;
	}
	function get_days_run_v2($date_start, $date_end, $truck_id = 0,$load_id = 0) 
	{
		$truck_list="";
		if($load_id > 0)
		{
			$tarr=mrr_get_truck_from_load_id($load_id);
			if(count($tarr)==1 && $truck_id==0)
			{
				$truck_id=$tarr[0];
			}
			elseif(count($tarr) > 0)
			{
				$truck_list=" and (";	
				for($i=0;$i < count($tarr); $i++)
				{
					if($i==0)
					{
						$truck_list.="trucks_log.truck_id = '".sql_friendly($tarr[0])."' ";	
					}
					else
					{
						$truck_list.=" or trucks_log.truck_id = '".sql_friendly($tarr[ $i ])."' ";	
					}
				}				
				$truck_list.=") ";	
				
				$truck_id=0;		//override the single truck since the list will take over...
			}
		}
		
		global $defaultsarray;
		//(trucks_log.hours_worked / $defaultsarray[local_driver_workweek_hours])
		$sql = "
			select sum(trucks_log.daily_run_otr) as days_run
			
			from trucks_log, load_handler
			where trucks_log.deleted = 0
				and load_handler.deleted = 0
				and trucks_log.load_handler_id = load_handler.id
				and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", $date_start)." 00:00:00'
				and trucks_log.linedate_pickup_eta <= '".date("Y-m-d", $date_end)." 23:59:59'
				".($truck_id > 0 ? " and trucks_log.truck_id = '".sql_friendly($truck_id)."' " : "")."
				".$truck_list."
		";
		//and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') >= '".date("Y-m-d", $date_start)."'
		//and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') < '".date("Y-m-d", $date_end)."'
		
		
		
		
		//echo $sql;
		$data_actual = simple_query($sql);
		$row_actual = mysqli_fetch_array($data_actual);
		
		$days_actual = $row_actual['days_run'];
		//$days_actual = ceil($days_actual);
		return $days_actual;
	}
	function get_days_run_v3($date_start, $date_end, $truck_id = 0,$load_id = 0) 
	{
		$truck_list="";
		if($load_id > 0)
		{
			$tarr=mrr_get_truck_from_load_id($load_id);
			if(count($tarr)==1 && $truck_id==0)
			{
				$truck_id=$tarr[0];
			}
			elseif(count($tarr) > 0)
			{
				$truck_list=" and (";	
				for($i=0;$i < count($tarr); $i++)
				{
					if($i==0)
					{
						$truck_list.="trucks_log.truck_id = '".sql_friendly($tarr[0])."' ";	
					}
					else
					{
						$truck_list.=" or trucks_log.truck_id = '".sql_friendly($tarr[ $i ])."' ";	
					}
				}				
				$truck_list.=") ";	
				
				$truck_id=0;		//override the single truck since the list will take over...
			}
		}
		
		global $defaultsarray;
		//(trucks_log.hours_worked / $defaultsarray[local_driver_workweek_hours])
		$sql = "
			select sum(trucks_log.daily_run_hourly) as days_run
			
			from trucks_log, load_handler
			where trucks_log.deleted = 0
				and load_handler.deleted = 0
				and trucks_log.load_handler_id = load_handler.id
				and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", $date_start)." 00:00:00'
				and trucks_log.linedate_pickup_eta <= '".date("Y-m-d", $date_end)." 23:59:59'
				".($truck_id > 0 ? " and trucks_log.truck_id = '".sql_friendly($truck_id)."' " : "")."
				".$truck_list."
		";
		//and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') >= '".date("Y-m-d", $date_start)."'
		//and date_format(trucks_log.linedate_pickup_eta, '%Y-%m-%d') < '".date("Y-m-d", $date_end)."'
		
		
		
		
		//echo $sql;
		$data_actual = simple_query($sql);
		$row_actual = mysqli_fetch_array($data_actual);
		
		$days_actual = $row_actual['days_run'];
		//$days_actual = ceil($days_actual);
		return $days_actual;
	}
	
	
	
	function mrr_get_days_run($date_start, $date_end, $truck_id = 0) {
		global $defaultsarray;
		//(trucks_log.hours_worked / $defaultsarray[local_driver_workweek_hours])
		$sql = "
			select sum((trucks_log.daily_run_otr + trucks_log.daily_run_hourly)) as days_run,
				count(trucks_log.id) as row_counter
			
			from trucks_log, load_handler
			where trucks_log.deleted = 0
				and load_handler.deleted = 0
				and trucks_log.load_handler_id = load_handler.id
				and trucks_log.linedate_pickup_eta  >= '".date("Y-m-d", $date_start)."'
				and trucks_log.linedate_pickup_eta  <= '".date("Y-m-d", $date_end)." 23:59:59'
				".($truck_id > 0 ? " and trucks_log.truck_id = '".sql_friendly($truck_id)."' " : "")."
		";
		//echo $sql;
		$data_actual = simple_query($sql);
		$row_actual = mysqli_fetch_array($data_actual);
		
		$days_actual = $row_actual['days_run'];
		$days_actual = ceil($days_actual);
		
		$days_actual = "Found ".$row_actual['row_counter'] ." Rows for Date Range ".date("Y-m-d", $date_start)." thru ".date("Y-m-d", $date_end)."";
		
		
		return $days_actual;
	}
	
	function force_line_wrap($use_string, $chars_per_row) {
		$note_holder = "";
		$last_note_pos = 0;
		$chars_per_line = 50;
		for($p=$chars_per_line;$p< strlen($use_string);$p+=$chars_per_line) {
			for($m=0;$m<40;$m++) {
				if(substr($use_string, $p+$m, 1) == ' ') {
					
					$note_holder .= substr($use_string, $last_note_pos, $chars_per_line+$m);
					
					$p+=$m;
					$last_note_pos = $p;
					$note_holder .= "<br>";
					break;
				}
			}
		}
		$note_holder .= substr($use_string,$last_note_pos,strlen($use_string));
		
		return $note_holder;
	}
	
	//support function for 	ajax_maint_req_list()
	function identify_truck_trailer($equip_type , $equip_id,$show_deleted=0)
	{
		$my_label="";
		
		$del_adder=" and deleted='0'";		if($show_deleted > 0)	$del_adder="";
		
		if(get_option_name_by_id($equip_type) == 'truck' && $equip_id> 0)
		{
			$sql = "
					select id as use_val,name_truck as use_disp,deleted
					from trucks 
					where id>0".$del_adder." and id='".sql_friendly( $equip_id )."'							
				";
			$data = simple_query($sql);
			$row = mysqli_fetch_array($data);
			$my_label=$row['use_disp']."".($row['deleted'] > 0 ? " [Deleted]" :"")."";
		}
		elseif(get_option_name_by_id($equip_type) == 'trailer' && $equip_id> 0)
		{
			$sql = "
					select id as use_val,trailer_name as use_disp,deleted 
					
					from trailers 
					where id>0".$del_adder." and id='".sql_friendly( $equip_id )."' 				
				";
			$data = simple_query($sql);
			$row = mysqli_fetch_array($data);
			$my_label=$row['use_disp']."".($row['deleted'] > 0 ? " [Deleted]" :"")."";
		}	
		return $my_label;
	}
	
	function ajax_request_duplication_check($desc,$sel_type,$sel_item,$copy_mode)
	{
		//check if there is a request for this name in this mode and return a code.
		//COPY_MODE 0= MaintRequest to Recurring....  1=Recurring to MaintRequest.
		$result="Invalid";
		
		$mrr_adder="";
		//if($sel_type> 0 )	$mrr_adder.=" and equip_type='". sql_friendly( $sel_type )."'";
		//if($sel_item> 0 )	$mrr_adder.=" and ref_id='". sql_friendly( $sel_item )."'";
					
		//$rval = "";
		$sql = "
				select *
				from maint_requests
				where deleted ='0' ".$mrr_adder."
					and maint_desc='". sql_friendly( $desc )."' 	
					and recur_flag='".($copy_mode == '1' ? "0" : "1")."'
					and linedate_completed='0000-00-00 00:00:00'
				limit 1
			
		";		
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		if($row['id'] > 0 && $copy_mode==0)
		{
			$result="Invalid";
		}
		else
		{
			$result="Valid";	
		}	
		return $result;			
	}	
	
	function mrr_count_days_between_date($date1,$date2)
	{	//take two MySQL dates in YYYY-MM-DD for mand count days
		$date1 =trim($date1);
		$date2 =trim($date2);
		$days =0;
		$mydate1 =0;
		$mydate2 =0;
		
		$str1 = substr($date1,0,10);
		$str2 = substr($date2,0,10);
		
		$str1 = str_replace("-","",$str1);
		$str2 = str_replace("-","",$str2);
		
		$mydate1 = (int) $str1;
		$mydate2 = (int) $str2;		
		
		$days = $mydate2 - $mydate1;
		
		return $days;
	}
	
	function mrr_last_request_reading($type,$item,$desc,$etype_id,$dater,$days)
	{
		//get last odometer reading
		$found_id=0;
		$odometer=0;
		$madedate="0000-00-00 00:00:00";			//DATE_FORMAT(linedate,'%m/%d/%Y')
		$compdate="0000-00-00 00:00:00";
		
		$mrr_adder="";
		if($etype_id>0)	$mrr_adder.=" and equip_type = '".sql_friendly( $etype_id )."'";	
		if($item>0)		$mrr_adder.=" and ref_id = '".sql_friendly( $item )."'";
				
		$msg="<br>Type=".$type.", Item=".$item.", Desc=".$desc.", Type_Id=".$etype_id.", DATER=".$dater.", Days=".$days.".<br>";
			
		$sql = "
			select id,odometer_reading,linedate_scheduled,linedate_completed
			
			from maint_requests
			where deleted ='0' and recur_flag='0' and active='1' 
				and maint_desc = '".sql_friendly( $desc )."'
				".$mrr_adder."					
				and linedate_scheduled>='".sql_friendly( $dater )."'
				and linedate_scheduled>=DATE_SUB(NOW(),INTERVAL ".sql_friendly( $days )." DAY)
			order by linedate_scheduled desc
			limit 1
		";
		$data = simple_query($sql);			
		while($row = mysqli_fetch_array($data))
		{
			$found_id=$row['id'];
			$odometer=$row['odometer_reading'];
			$madedate=$row['linedate_scheduled'];
			$compdate=$row['linedate_completed'];
		}
		
		$res['found_id']=$found_id;
		$res['odometer']=$odometer;
		$res['scheduled']=$madedate;
		$res['completed']=$compdate;
		$res['msg']=$msg;
		return $res;
	}
	function mrr_last_odometer_reading($type,$item,$dater='')
	{
		//get last odometer reading
		$odometer = 0;
		$linedate="0000-00-00 00:00:00";			//DATE_FORMAT(linedate,'%m/%d/%Y')
		
		$mrr_adder="";	
		if(trim($dater)!="")	$mrr_adder=" and linedate < '".$dater."'";	//date should come from an earlier run of this function...
		
		if($type == 'truck' && $item> 0 ) {
			$sql = "
				select odometer,linedate
				
				from trucks_odometer
				where truck_id = '".sql_friendly( $item )."'
					and deleted = 0
					".$mrr_adder."
				order by linedate desc
				limit 1
			";
			$data = simple_query($sql);			
			while($row = mysqli_fetch_array($data))
			{
				$odometer=$row['odometer'];
				$linedate=$row['linedate'];
			}
		}
		$res['odometer']=$odometer;
		$res['linedate']=$linedate;
		return $res;
	}
	function mrr_no_ajax_make_line_item_list($req_id,$maint_desc="")
	{
		$rval = "";
		$active_count = 0;
		
		$mrr_adder=" and ref_id='".sql_friendly($req_id)."'";
		
		if($maint_desc!="")				$mrr_adder.=" or lineitem_desc LIKE '%".sql_friendly($maint_desc)."%')";
			
		$sql = "
				select *
				from maint_line_items
				where deleted = 0 ".$mrr_adder."
				order by linedate_added asc, id asc
			
		";		// and active=1
		$data = simple_query($sql);
		
		$all_items=0;
		$tot_items=0;
		$tot_quant=0;
		$tot_hours=0;
		$tot_cost=0;
		
		$rval.= "<table cellpadding='2' cellspacing='0' width='98%' border='1' class='table_grid' style='margin:4px'>
				<tr bgcolor='#ffffff'>
					<td valign='top' width='175'><b>Line Item</b></td>		
					<td valign='top' width='100'><b>Category</b></td>
					<td valign='top' width='100'><b>Vendor</b></td>
					<td valign='top'><b>Part</b></td>
					<td valign='top' width='90'><b>Location</b></td>
					<td valign='top' width='60' align='right'><b>Downtime</b></td>
					<td valign='top' width='50' align='right'><b>Quantity</b></td>
					<td valign='top' width='75' align='right'><b>Unit Cost</b></td>					
					<td valign='top' width='75' align='right'><b>Cost</b></td>	
				</tr>
			";
		
		while($row = mysqli_fetch_array($data))
		{
			$classy="mrr_link_like_off";
			$checked="";
			
			$quant=$row['quantity'];
			$unit_cost=$row['item_cost'];
			$sub_cost=$quant * $unit_cost;
			$coster=money_format('',$sub_cost);
			$hours=number_format($row['down_time_hours'],2);
			
			
			if($row['active']==1) 	
			{
				$active_count++;
				$classy="mrr_link_like_on";
				$checked=" checked";
				$tot_cost+=$sub_cost;				//projected cost Q*C=subtot  Changing quantity will change sub
				$tot_items++;						//added items
				$tot_quant+=$row['quantity'];			//add number of items
				$tot_hours+=$row['down_time_hours'];	//hours to accumulate
			}
						
			$main_desc=trim($row['lineitem_desc']);
			$full_desc=$main_desc;
			if(strlen($main_desc)>28)			$main_desc=substr($main_desc,0,25)."...";
			//remove any special characters
			$full_desc=strip_tags($full_desc);
			$full_desc=str_replace("'","",$full_desc);
			$full_desc=str_replace('"','',$full_desc);		
			$full_desc=addslashes($full_desc);	
			
			$maker=trim($row['make']);
			$full_maker=str_replace("'","",$maker);
			if(strlen($maker) > 15)				$maker="<span title='".$full_maker."'>".substr($maker,0,15)."...</span>";
			
			$model=trim($row['model']);
			$full_model=str_replace("'","",$model);
			if(strlen($model) > 15)				$model="<span title='".$full_model."'>".substr($model,0,15)."...</span>";
									
			$cat_type=mrr_get_option_fvalue_by_id($row['cat_id']);	
			$front_type=mrr_get_option_fvalue_by_id($row['location_front']);	
			$left_type=mrr_get_option_fvalue_by_id($row['location_left']);	
			$top_type=mrr_get_option_fvalue_by_id($row['location_top']);	
			$inside_type=mrr_get_option_fvalue_by_id($row['location_inside']);
			
			$cat_type=trim($cat_type);
			$full_cat=str_replace("'","",$cat_type);
			if(strlen($cat_type) > 15)			$cat_type="<span title='".$full_cat."'>".substr($cat_type,0,15)."...</span>";
						
			
			//$trash='<a href="javascript:confirm_delete_item('.$req_id.','.$row['id'].')"><img src="images/delete_sm.gif" border="0"></a>';
			if($main_desc!=$full_desc)
				$linker="<a href='maint.php?id=".$req_id."&item=".$row['id']."' class='".$classy."' title='".$full_desc."'>".$main_desc."</a>";
			else
				$linker="<a href='maint.php?id=".$req_id."&item=".$row['id']."' class='".$classy."'>".$main_desc."</a>";
			
			//$linker="<span id='link_like_".$all_items."' class='".$classy."' onClick='load_line_item_form(".$req_id.",".$row['id'].");'><b>".$main_desc."</b></span>";
			$all_items++;		
			
			$unit_label="<span class='".$classy."'>$".$unit_cost."</span>";
			$cost_label="<span class='".$classy."'>$".$coster."</span>";
			$hour_label="<span class='".$classy."'>".$hours."</span>";
			$quant_label="<span class='".$classy."'>".$quant."</span>";
			
			$loc_tags="";
			if(trim($front_type)!="")			$loc_tags.=substr($front_type,0,1)." ";
			if(trim($left_type)!="")				$loc_tags.=substr($left_type,0,4)." ";
			if(trim($top_type)!="")				$loc_tags.=substr($top_type,0,4)." ";
			if(trim($inside_type)!="")			$loc_tags.=substr($inside_type,0,3)." ";
					
			//xml output
			$rval.= "
				<tr bgcolor='#ffffff'>
					<td valign='top' nowrap>$linker</td>		
					<td valign='top' nowrap>$cat_type</td>
					<td valign='top' nowrap>$maker</td>
					<td valign='top' nowrap>$model</td>
					<td valign='top' nowrap>$loc_tags</td>
					<td valign='top' align='right'>$hour_label</td>
					<td valign='top' align='right'>$quant_label</td>
					<td valign='top' align='right'>$unit_label</td>
					<td valign='top' align='right'>$cost_label</td>		
				</tr>
			";
			
		}
		if($all_items>0)
		{
			$tot_cost=money_format('',$tot_cost);
			$tot_hours=number_format($tot_hours,2);
			$rval.= "
				<tr bgcolor='#ffffff'>
					<td valign='top'>Total Items</td>	
					<td valign='top'>$active_count</td>
					<td valign='top'></td>
					<td valign='top'></td>
					<td valign='top'></td>
					<td valign='top' align='right'>$tot_hours</td>
					<td valign='top' align='right'>$tot_quant</td>
					<td valign='top' align='right'></td>
					<td valign='top' align='right'>$tot_cost</td>	
				</tr>
			";			
		}	
		$rval.="</table>";
		return $rval;
	}
	
	function mrr_display_maint_request_section($e_type,$e_select,$width=0,$height=0)
	{
		$color="#cc0000";
		
		if($width==0)		$width=400;
		if($height==0)		$height=400;
		
		$col_num=4;
		$col_adder="";
		if($e_type==2 || $e_type==59)
		{
			$col_num=7;
			$col_adder="
				<td valign='top'><b>Inspection</b></td>	
				<td valign='top' width='45'><b>Passed</b></td>	
				<td valign='top'><b>Updated</b></td>	
			";
		}		
		$rval="<div style='height:".$height."px; max-height:".$height."px; overflow:auto;'>";
		$rval.= "<table class='admin_menu1' width='".$width."' style='border:1px ".$color." solid;'>
				<tr>
					<td valign='top' colspan='".$col_num."' class='border_bottom'><div class='section_heading'>Maintenance Requests</div></td>
				</tr>
				<tr>
					<td valign='top' width='15'>&nbsp;</td>	
					<td valign='top'><b>Request</b></td>		
					<td valign='top' width='75'><b>Scheduled</b></td>
					<td valign='top' width='45'><b>Done</b></td>
					".$col_adder."			
				</tr>
			";
		
			/*
					<td valign='top' width='60' align='right'><b>Downtime</b></td>				
					<td valign='top' width='75' align='right'><b>Cost</b></td>	
			*/
		
		$mrr_adder="";
		
		if($e_type > 0 )	$mrr_adder.=" and equip_type='".sql_friendly( $e_type )."'";
		if($e_select > 0 )	$mrr_adder.=" and ref_id='".sql_friendly( $e_select )."'";
		$sql = "
				select maint_requests.*
				from maint_requests
				where maint_requests.deleted=0 
					and maint_requests.active=1
					and maint_requests.recur_flag=0
					 ".$mrr_adder."
				order by maint_requests.urgent desc,maint_requests.linedate_added desc, maint_requests.id asc
				
		";		
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$tres=mrr_form_trailer_inspection_list($row['id']);
			/*
			$tres['used']=0;			//is there an inspection being used...
          	$tres['passed']=0;
          	$tres['created_by']="";
          	$tres['updated_by']="";
          	$tres['updated']="";
          	$tres['created']="";
          	$tres['inspection']=0;
          	$tres['used_pmi']=0;		
          	$tres['used_fed']=0;
			*/
			$classy="mrr_link_like_off";
			$checked="";
			//capture result row
			$scheduled=$row['linedate_scheduled'];
			$scheduled=$row['linedate_added'];
			
			$completed=$row['linedate_completed'];
			$desc=$row['maint_desc'];
			
			$urgent=$row['urgent'];
			
			//$cost=$row['cost'];
			//$downtime=$row['down_time_hours'];
			//$odometer=$row['odometer_reading'];	
			
			//$coster=money_format('',$cost);
			//$hours=number_format($downtime,2);
			$urg="";
			if($urgent > 0)		$urg="<span style='color:".$color.";'><b>!!!</b></span>";
			
			$maint_desc=$desc;
			if(strlen($desc) > 42) 	$maint_desc="".substr($desc,0,45)."...";	//<span title='".$desc."'></span>
			$linker="<a href='maint.php?id=".$row['id']."' target='_blank'><b>".$maint_desc."</b></a>";	//$row['id']
							
			if($scheduled=="0000-00-00 00:00:00")	$scheduled="";		else		$scheduled=date("m/d/Y", strtotime($scheduled));
			if($completed=="0000-00-00 00:00:00")	$completed="No";	else		$completed="Yes";
			
			//xml output
					
			if($e_type==2 || $e_type==59)
			{	//trailers			
										// $tres['used'] > 0
				if($tres['passed'] > 0)
     			{
     				//$inspect_type="";		//$tres['updated_by']
     				//if($tres['used_pmi'] > 0 && $tres['used_fed'] > 0)	$inspect_type="PMI / FED";
     				//elseif($tres['used_pmi'] > 0)						$inspect_type="PMI";
     				//elseif($tres['used_fed'] > 0)						$inspect_type="FED";
     				
     				$inspect_type="";
     				if($tres['passed']==1)		$inspect_type="PMI";
     				if($tres['passed']==2)		$inspect_type="FED";
     				if($tres['passed']==3)		$inspect_type="PMI / FED";
     				
     				$rval.= "
          				<tr>
          					<td valign='top'>$urg</td>
          					<td valign='top'>$linker</td>		
          					<td valign='top'>$scheduled</td>	
          					<td valign='top'>$completed</td>	    					
          					<td valign='top'>".$inspect_type."</td>
          					<td valign='top'>".($tres['passed'] > 0 ? "Yes" : "No")."</td>	
          					<td valign='top'>".$tres['updated']."</td>		
          				</tr>
          			";
     			}
     			else
     			{
     				$rval.= "
          				<tr>
          					<td valign='top'>$urg</td>
          					<td valign='top'>$linker</td>		
          					<td valign='top'>$scheduled</td>	
          					<td valign='top'>$completed</td>		
          					<td valign='top'>N/A</td>
          					<td valign='top'>&nbsp;</td>
          					<td valign='top'>&nbsp;</td>			
          				</tr>
          			";
     			}
			}	
			else
			{	//trucks
				$rval.= "
     				<tr>
     					<td valign='top'>$urg</td>
     					<td valign='top'>$linker</td>		
     					<td valign='top'>$scheduled</td>	
     					<td valign='top'>$completed</td>			
     				</tr>
     			";
			}
						
			/*
					<td valign='top' align='right'>$hours</td>
					<td valign='top' align='right'>$coster</td>	
			*/
		}
		$rval.="</table></div>";
		return $rval;	
	}
	
	function mrr_get_customer_id_email($cust_id)
	{
		$email="";
		$sql = "
				select contact_email 
					from customers 
					where id='".sql_friendly( $cust_id )."'		
		";
		$data2 = simple_query($sql);
		while($row2 = mysqli_fetch_array($data2))
		{		
			$email=$row2['contact_email'];
			$email=trim($email);
		}		
		return $email;	
	}
	function mrr_get_default_xvalue_by_xname($xname)
	{
		$xvalue="";
		$sql = "
				select xvalue_string
					from defaults
					where xname='".sql_friendly( $xname )."'
			";
		$data2 = simple_query($sql);
		while($row2 = mysqli_fetch_array($data2))
		{		
			$xvalue=trim($row2['xvalue_string']);
		}		
		return $xvalue;	
	}	
	
	function show_help($file_name,$field_name,$new_style_path="",$new_image="")
	{
		global $datasource;

		$sql = "
				select *										
				from help_desk
				where page_name='".sql_friendly( $file_name )."' 
					and field_name='".sql_friendly( $field_name )."'
					and deleted=0
				order by linedate_added asc,id asc 
				limit 1
			";
		$data = simple_query($sql);
		$mn=mysqli_num_rows($data);
		//if not present, add it
		if($mn==0)
		{
			$sql = "
				insert into help_desk (id,
					page_name,
					field_name,
					linedate_added,
					quick_text,
					help_text,
					active,
					deleted) 
				values (NULL,
					'".sql_friendly( $file_name )."',
					'".sql_friendly( $field_name )."',
					NOW(),
					'',
					'',
					1,
					0)	
			";
			$data = simple_query($sql);
			$new_id=mysqli_insert_id($datasource);
			
			$sql = "
				select *										
				from help_desk
				where page_name='".sql_friendly( $file_name )."' 
					and field_name='".sql_friendly( $field_name )."'
					and deleted=0
				order by linedate_added asc,id asc 
				limit 1
			";
			$data = simple_query($sql);		
		}		
		
		//now return help info
		$id=0;
		//$active=0;
		//$page_name="";
		//$field_name="";
		$quick_text="";
		//$help_text="";
		$stamp="00/00/0000";
		while($row = mysqli_fetch_array($data))
		{		
			$id=$row['id'];
			//$active=$row['active'];
			//$page_name=trim($row['page_name']);
			//$field_name=trim($row['field_name']);
			$quick_text=trim($row['quick_text']);
			$help_text=trim($row['help_text']);
			$stamp= date("n/j/Y", strtotime($row['linedate_added']));
		}
		
		//if(strlen($help_text) > 55 )	$help_text=substr($help_text,0,50)."...";
		if(strlen($help_text) > 0 )		$quick_text.=" Click here for more info.";
		
		if(strlen($quick_text) ==0)		$quick_text="Help information is not yet available.  Click to Add.";
		
		$old_design="/images/help_small.png";
		$linker="<a class='mrr_help' id='help_topic_".$id."' name='help_topic_".$id."' title='".$quick_text."' href='admin_help.php?id=".$id."' target='_blank'>
					<img src='".$old_design."' alt='?' border='0'>
				</a>
				<script type='text/javascript'>$('#help_topic_".$id."').qtip({ style: { name: 'cream', tip: true,'font-family':'arial','font-size':'12px' } });</script>
		";		
		$new_design_image=$new_style_path."".$new_image;
		if(trim($new_design_image)!="")
		{
			$linker="<a class='mrr_help' id='help_topic_".$id."' name='help_topic_".$id."' title='".$quick_text."' href='admin_help.php?id=".$id."' target='_blank'><img src='".trim($new_design_image)."' alt='?' border='0'></a>".
					"<script type='text/javascript'>$('#help_topic_".$id."').qtip({ style: { name: 'cream', tip: true,'font-family':'arial','font-size':'12px' } });</script>";	
		}		
		return $linker;
	}
	
	function mrr_help_selector_box($form_name,$selected_val)
	{
		$default_text="";
		$sql="";
		$table_field="";
		if($form_name=="page_name")
		{
			$default_text="All Pages";	
			$sql="
				select distinct(page_name) 
					from help_desk
					where deleted=0
					order by page_name asc					
			";
			$table_field="page_name";
		}
		elseif($form_name=="field_name")
		{
			$default_text="All Fields";
			$sql="
				select distinct(field_name) 
					from help_desk
					where deleted=0
					order by field_name asc					
			";
			$table_field="field_name";
		}
		$form_name.="_selector";		
		
		$return_var = "<select id='".$form_name."' name='".$form_name."'>";
		if($default_text != '')
		{
			$return_var .= "<option value=''>".$default_text."</option>";
		}
		
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$valuer=$row[''.$table_field.''];
			$use_selected = "";
						
			if($valuer == $selected_val) 		$use_selected = "selected";
			
			$return_var .= "<option $use_selected value='$valuer'>$valuer</option>";
		}		
		$return_var .= "</select>";
		
		return $return_var;
	}
	
	function mrr_odometer_check_for_mistype_alt($truck_id,$odom_one,$dater_one)
	{
		$warning="";
		//$odom_one=0;			$dater_one="00/00/0000";
		$odom_two=0;			$dater_two="00/00/0000";
		
		//now get highest odometer reading
		$sql="
				select * 
					from trucks_odometer
					where deleted=0
						and truck_id='".sql_friendly( $truck_id )."'
						and odometer>0
					order by odometer desc
					limit 1					
			";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$odom_two=$row['odometer'];
			$dater_two= date("n/j/Y", strtotime($row['linedate_added']));
		}
		
		if($dater_one!=$dater_two || $odom_one < $odom_two)
		{
			$warning="Warning Last odometer reading is not the highest odometer reading.";
		}
		return $warning;
	}
	
	function mrr_odometer_check_for_mistype($truck_id)
	{
		$warning="";
		$odom_one=0;			$dater_one="00/00/0000";
		$odom_two=0;			$dater_two="00/00/0000";
		
		//get last odometer reading
		$sql="
				select * 
					from trucks_odometer
					where deleted=0
						and truck_id='".sql_friendly( $truck_id )."'
						and odometer>0
					order by linedate_added desc,id desc
					limit 1					
			";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$odom_one=$row['odometer'];
			$dater_one= date("n/j/Y", strtotime($row['linedate_added']));
		}
		
		//now get highest odometer reading
		$sql="
				select * 
					from trucks_odometer
					where deleted=0
						and truck_id='".sql_friendly( $truck_id )."'
						and odometer>0
					order by odometer desc
					limit 1					
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$odom_two=$row['odometer'];
			$dater_two= date("n/j/Y", strtotime($row['linedate_added']));
		}
		
		if($dater_one!=$dater_two || $odom_one < $odom_two)
		{
			$warning="Warning Last odometer reading is not the highest odometer reading.";
		}
		return $warning;
	}
	function mrr_fetch_truck_deleted($truck_id)
	{
		$del=0;
		//get last odometer reading
		$sql="
			select deleted
				from trucks
				where id='".sql_friendly( $truck_id )."'
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			$del=$row['deleted'];
		}
		return $del;	
	}
	function trucks_last_movement($truck_id, $cd = 0)
	{	//CD determines warning message type.  0="!!!", 1="text warning...."
		$today=0;
		$weekdays=0;
		$dater="00/00/0000";
		$wkdy=date("w");
		
		$sql="
				select linedate_added
				
				from trucks_log
				where deleted=0
					and truck_id='".sql_friendly( $truck_id )."'
				order by linedate_added desc
				limit 1
			";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$today=date("m/d/Y", strtotime($row['linedate_added']));
			$weekdays=date("m/d/Y");
			$dater= date("n/j/Y", strtotime($row['linedate_added']));
		}
		
		$diff=$today-$weekdays;	//number of days between now and stamp
		if($wkdy==0)	$diff-=2;	//if sunday, deduct two days from difference so that friday is still good.
		if($wkdy==6)	$diff-=1;	//if saturday, deduct one day from difference so that friday is still good.
		
		//display message		
		$warning="";
		if($diff>1 && $cd==0)
		{
			//$warning=" &nbsp;&nbsp; <span title='This truck has not moved since ".$dater."' style='color:#cc0000;'><b>!!!</b></span>";	
		}	
		if($diff>1 && $cd==1)
		{
			$warning=" &nbsp;&nbsp; <span style='color:#cc0000;'><b>This truck has not moved since ".$dater."</b></span>";	
		}		
		return $warning;
	}
	function show_warnings_for_all_truck_movement()
	{
		$output="";
		$sql="
				select id,
					name_truck
				from trucks
				where deleted=0
					and active=1
				order by name_truck asc,id asc			
			";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$truck_id=$row['id'];
			$truck_name=$row['name_truck'];
			$warning=trucks_last_movement($truck_id,1);	//show text warning not just "!!!"
			if(trim($warning)!="")
			{
				$linker="<a href='/admin_trucks.php?id=".$truck_id."' title='Click here to see what is up with this truck...' target='_blank'><b>".$truck_name."</b></a>";
				
				$output.="<tr><td valign='top'>".$linker."</td><td valign='top'>".$warning."</td></tr>";	
			}
		}
		
		return $output;	
	}
	
	function mrr_type_decoder($type)
	{
		$typer[0]="";
		$typer[1]="Driver";
		$typer[2]="Trailer";
		$typer[3]="Truck";
		$typer[4]="";
		$typer[5]="Customer";
		$typer[6]="Dispatch";
		$typer[7]="";
		$typer[8]="Load";
		$typer[9]="";
		$typer[10]="Maint";
		$typer[11]="Accident";
		
		return $typer[ $type ];	
	}
	function mrr_log_page_loads($start_time_seconds = 0,$label= '')
	{	//take starting point in seconds and subtract from now to find unix seconds since Jan 1 1970.  Enter in log, with label appended if present, these times.
		
     	$mrr_micro_seconds_partB=time();	
         	$partB_time=$mrr_micro_seconds_partB;
     		
     	$elapse_AB=$partB_time-$start_time_seconds;
     	$pather=$_SERVER['SCRIPT_FILENAME'];
     	$pather=str_replace("www","www/",$pather);
     	$pather.=$label;
     	$user_id=0;
     	if(isset($_SESSION['user_id']))	$user_id=$_SESSION['user_id'];
     	
     	$sql="insert into log_page_loads 
     					(id,
     					time_stamp,
     					ip_address,
     					page_url,
     					user_id,
     					start_load,
     					end_load,
     					load_time)
     				values (NULL,
     					NOW(),
     					'".$_SERVER['REMOTE_ADDR']."',
     					'".$pather."',
     					'".$user_id."',
     					'".$start_time_seconds."',
     					'".$partB_time."',
     					'".$elapse_AB."')
     		";
     	//simple_query($sql);	
     	// stop logging for now (12/13/2011 CS)
	}
	function mrr_get_employer_by_id($emp_id)
	{
		//$use_id=0;
		$use_val='';
		$sql="
               	select option_values.id as use_val,
               		option_values.fvalue as use_disp
               	from option_values,option_cat
               	where option_values.deleted=0
               		and option_cat.id=option_values.cat_id
               		and option_cat.cat_name='employer_list'
               		and option_values.id='".sql_friendly( $emp_id )."'
               	order by option_values.fvalue asc
               ";	
          $data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			//$use_id=$row['use_val'];
			$use_val=$row['use_disp'];
		} 
		return $use_val;		
	}
	function mrr_get_employer_id_by_def($emp_val)
	{
		$use_id=0;
		//$use_val='';
		$sql="
               	select option_values.id as use_val,
               		option_values.fvalue as use_disp
               	from option_values,option_cat
               	where option_values.deleted=0
               		and option_cat.id=option_values.cat_id
               		and option_cat.cat_name='employer_list'
               		and option_values.fname='".sql_friendly( $emp_val )."'
               	order by option_values.fvalue asc
               ";	
          $data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$use_id=$row['use_val'];
			//$use_val=$row['use_disp'];
		} 
		return $use_id;		
	}
	
	//Punch Card functions.....................
	function mrr_punch_clock_login($user_id,$ip)
	{
		global $defaultsarray;	
		
		$today=date("m/d/Y");
		//see if still logged in...
		$res=mrr_punch_clock_login_status($user_id);
		if($res['clock_mode']==1 && $res['linedate_date']!=$today)
		{
			$end_stamp=trim($defaultsarray['time_clock_auto_time']);
			if(strlen($end_stamp) > 8)	$end_stamp=substr($end_stamp,0,8);
			
			$dater="".$res['linedate_date']." ".$end_stamp."";			
          	$new_id=mrr_punch_clock_logger($user_id,$ip,1,0,0,$dater);	//auto logout with default stamp for that day at 4:35PM
          	
          	//now caluculate hours
          	$res2=mrr_punch_clock_vals($new_id);
          	$hrs1=date("H", strtotime($res['linedate_time']));		$hrs1=(int)$hrs1;
          	$min1=date("i", strtotime($res['linedate_time']));		$min1=(int)$min1;
          	$sec1=date("s", strtotime($res['linedate_time']));		$sec1=(int)$sec1;
          	
          	$hrs2=date("H", strtotime($res2['linedate_time']));		$hrs2=(int)$hrs2;
          	$min2=date("i", strtotime($res2['linedate_time']));		$min2=(int)$min2;
          	$sec2=date("s", strtotime($res2['linedate_time']));		$sec2=(int)$sec2;
          	 
     		if($sec1 >=30)	$min1++;
     		if($sec2 >=30)	$min2++;
     	
     		$totalmins1=($hrs1*60) + $min1;
     		$totalmins2=($hrs2*60) + $min2;
     		$totalhrs=($totalmins2 - $totalmins1)/60;
     		$totalhrs=number_format($totalhrs,2);
     		$msg="Auto Clock Out: ".$res2['linedate_time']." - ".$res['linedate_time']." = ".$totalhrs.".";
          	mrr_punch_clock_update_hours($new_id,$totalhrs,"");          	
          	
          	$new_id=mrr_punch_clock_logger($user_id,$ip,0,1,0,$msg);		//clock in
		}
		elseif($res['clock_mode']==0)
		{
			$new_id=mrr_punch_clock_logger($user_id,$ip,0,1,0,"");		//clock in
		}          
          
	}
	function mrr_punch_clock_logger($user_id,$ip,$auto,$moder,$hrs,$timed="")
	{	
		global $datasource;

		//only function to make new rows in table...logs in and out for manual and auto
		$ntimer="NOW()";
		if($timed!="")		$ntimer="'".date("Y-m-d", strtotime($timed))."'";
		$sql="
			insert into punch_clock
				(id,
				linedate_added,
				user_id,
				ip_address,
				clock_auto,
				clock_mode,
				clock_hrs,
				notes)
			values
				(NULL,
				".$ntimer.",				
				'".sql_friendly( $user_id )."',
				'".sql_friendly( $ip )."',
				'".sql_friendly( $auto )."',
				'".sql_friendly( $moder )."',
				'".sql_friendly( $hrs )."',
				'')
               ";	
          simple_query($sql);	
          $newid=mysqli_insert_id($datasource);
          return $newid;	
	}	
	function mrr_punch_clock_logout($user_id,$ip)
	{
		$res=mrr_punch_clock_login_status($user_id);		
		$new_id=mrr_punch_clock_logger($user_id,$ip,0,0,0,"");
		
          //now caluculate hours since last clock in today
          $res2=mrr_punch_clock_vals($new_id);
     	$hrs1=date("H", strtotime($res['linedate_time'] ));		$hrs1=(int)$hrs1;
     	$min1=date("i", strtotime($res['linedate_time']));		$min1=(int)$min1;
     	$sec1=date("s", strtotime($res['linedate_time']));		$sec1=(int)$sec1;
     	
     	$hrs2=date("H", strtotime($res2['linedate_time']));		$hrs2=(int)$hrs2;
     	$min2=date("i", strtotime($res2['linedate_time']));		$min2=(int)$min2;
     	$sec2=date("s", strtotime($res2['linedate_time']));		$sec2=(int)$sec2;
     	
     	if($sec1 >=30)	$min1++;
     	if($sec2 >=30)	$min2++;
     	
     	$totalmins1=($hrs1*60) + $min1;
     	$totalmins2=($hrs2*60) + $min2;
     	$totalhrs=($totalmins2 - $totalmins1)/60;
     	$totalhrs=number_format($totalhrs,2);
     	$msg="Clock Out: ".$res2['linedate_time']." - ".$res['linedate_time']." = ".$totalhrs.".";
     	mrr_punch_clock_update_hours($new_id,$totalhrs,$msg);  
          
	}
	function mrr_punch_clock_update_hours($id,$hrs,$notes)
	{
		$sql="
			update punch_clock 
				set clock_hrs='".sql_friendly( $hrs )."',notes='".sql_friendly( $notes )."' 
			where id='".sql_friendly( $id )."'
               ";	
          simple_query($sql);
	}
	function mrr_punch_clock_update_hours_only($id,$hrs)
	{
		$sql="
			update punch_clock 
				set clock_hrs='".sql_friendly( $hrs )."' 
			where id='".sql_friendly( $id )."'
               ";	
          simple_query($sql);
	}
	function mrr_punch_clock_update_notes_only($id,$notes)
	{
		$sql="
			update punch_clock 
				set notes='".sql_friendly( $notes )."' 
			where id='".sql_friendly( $id )."'
               ";	
          simple_query($sql);
	}
	function mrr_punch_clock_update_time_only($id,$dater,$timer)
	{
		$masker=$dater." ".$timer.":00";
		$sql="
			update punch_clock 
				set linedate_added='".sql_friendly( $masker )."' 
			where id='".sql_friendly( $id )."'
               ";	
          simple_query($sql);
	}
	function mrr_punch_clock_vals($id)
	{
		$res['id']=0;
          $res['linedate_added']='';
          $res['linedate_date']='';
          $res['linedate_time']='';
          $res['linedate_day']='';
          $res['user_id']=0;
          $res['ip_address']='';
          $res['clock_auto']=0;
          $res['clock_mode']=0;
          $res['clock_hrs']=0;
          $res['name_first']='';
          $res['name_last']='';
          $res['username']='';
          $res['notes']='';
		
		$sql="
			select punch_clock.*,
				users.name_first,
				users.name_last,
				users.username 
			from punch_clock,users
			where punch_clock.user_id=users.id
				and punch_clock.id='".sql_friendly( $id )."'
               ";	
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {
          	$res['id']=$row['id'];
          	$res['linedate_added']=date("m/d/Y H:i:s", strtotime($row['linedate_added']));
          	$res['linedate_date']=date("m/d/Y", strtotime($row['linedate_added']));
          	$res['linedate_time']=date("H:i", strtotime($row['linedate_added']));
          	$res['linedate_day']=date("l", strtotime($row['linedate_added']));
          	$res['user_id']=$row['user_id'];
          	$res['ip_address']=$row['ip_address'];
          	$res['clock_auto']=$row['clock_auto'];
          	$res['clock_mode']=$row['clock_mode'];
          	$res['clock_hrs']=number_format($row['clock_hrs'],2);
          	$res['name_first']=$row['name_first'];
          	$res['name_last']=$row['name_last'];
          	$res['username']=$row['username'];    
          	$res['notes']=$row['notes'];      	
          }
          return $res;
	}
		
	function mrr_punch_clock_login_status($user_id,$moder=0)
	{
		$res['id']=0;
          $res['linedate_added']='';
          $res['linedate_date']='';
          $res['linedate_time']='';
          $res['linedate_day']='';
          $res['user_id']=0;
          $res['ip_address']='';
          $res['clock_auto']=0;
          $res['clock_mode']=0;
          $res['clock_hrs']=0;
          $res['name_first']='';
          $res['name_last']='';
          $res['username']='';
          $res['notes']=''; 
		
		$sql="
			select punch_clock.*,
				users.name_first,
				users.name_last,
				users.username 
			from punch_clock,users
			where punch_clock.user_id=users.id
				and user_id='".sql_friendly( $user_id )."'				
			order by punch_clock.linedate_added desc
			limit 1
               ";	//and clock_mode='".sql_friendly( $moder )."'
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {
          	$res['id']=$row['id'];
          	$res['linedate_added']=date("m/d/Y H:i:s", strtotime($row['linedate_added']));
          	$res['linedate_date']=date("m/d/Y", strtotime($row['linedate_added']));
          	$res['linedate_time']=date("H:i", strtotime($row['linedate_added']));
          	$res['linedate_day']=date("l", strtotime($row['linedate_added']));
          	$res['user_id']=$row['user_id'];
          	$res['ip_address']=$row['ip_address'];
          	$res['clock_auto']=$row['clock_auto'];
          	$res['clock_mode']=$row['clock_mode'];
          	$res['clock_hrs']=number_format($row['clock_hrs'],2);
          	$res['name_first']=$row['name_first'];
          	$res['name_last']=$row['name_last'];
          	$res['username']=$row['username'];  
          	$res['notes']=$row['notes'];         	
          }
          return $res;
	}
	function mrr_punch_clock_next($id,$user_id)
	{
		$newid=0;
		$sql="
			select punch_clock.id
			from punch_clock
			where punch_clock.id>'".sql_friendly( $id )."'
				and punch_clock.user_id='".sql_friendly( $user_id )."'
			order by punch_clock.id asc
			limit 1
               ";	
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {
          	$newid=$row['id'];     	
          }
          return $newid;
	}
	//.........................................
		
	function mrr_get_driver_med_card_exp($id)
	{
		$dl_number="0000-00-00 00:00:00";
		$sql="
     		select linedate_drugtest
     		from drivers 
     		where id='".sql_friendly($id)."'
     	";
     	$data = simple_query($sql);
     	if($row = mysqli_fetch_array($data)) {
     		$dl_number=trim($row['linedate_drugtest']);	
     	}	
		return $dl_number;		
	}
	function mrr_get_driver_license_number($id)
	{
		$dl_number="";
		$sql="
     		select dl_number
     		from drivers 
     		where id='".sql_friendly($id)."'
     	";
     	$data = simple_query($sql);
     	if($row = mysqli_fetch_array($data)) {
     		$dl_number=trim($row['dl_number']);	
     	}	
		return $dl_number;		
	}
	function mrr_get_driver_name($id)
	{
		$driver="";
		$sql="
     		select name_driver_first,name_driver_last
     		from drivers 
     		where id='".sql_friendly($id)."'
     	";
     	$data = simple_query($sql);
     	if($row = mysqli_fetch_array($data)) {
     		$driver=$row['name_driver_first']." ".$row['name_driver_last'];	
     	}	
		return $driver;		
	}	
	
	function mrr_trucking_sendMail($To,$ToName,$From,$FromName,$cc,$bcc,$Subject,$Text,$Html,$error_show=0,$add_header="")
	{	
		//,$AttmFiles,$faxcode=0,$replyName='',$replyAddr='') {
		/* mail using PHPMailer ...alternate function to show attachments and message in body of email.*/
		$to      = $To;		
		$subject = $Subject;		
		$message = trim($Html);
		
		// To send HTML mail, the Content-type header must be set
          $headers  = 'MIME-Version: 1.0' . "\r\n";
          $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";          
          // Additional headers
          $headers .= 'To: '.$ToName.' <'.$To.'>' . "\r\n";	//, Kelly <kelly@example.com>
          $headers .= 'From: '.$FromName.' <'.$From.'>' . "\r\n";
          //$headers .= 'Cc: '.$cc.'' . "\r\n";
          //$headers .= 'Bcc: '.$bcc.'' . "\r\n";
         
          $header.=$add_header;
          
		$sent=mail($to, $subject, $message, $headers);
		//$sent=imap_mail($to, $subject, $message, $headers);
		if($error_show > 0)
		{
			$res['sent']=$sent;
			$res['msg']="";
			if($sent==false)
			{
				$err=error_get_last();
				$res['msg']="<span class='alert'>".$err['message']."</span>";
			}
			return $res;
		}		
		return $sent;
	}
	/*
	function mrr_mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message) {
         
         $file = $path.$filename;
         
         $file_size = filesize($file);
         
         $handle = fopen($file, "r");
         
         $content = fread($handle, $file_size);
         
         fclose($handle);
         
         $content = chunk_split(base64_encode($content));
         
         $uid = md5(uniqid(time()));
         
         $name = basename($file);
         
         $header = "From: ".$from_name." <".$from_mail.">\r\n";
         $header .= "Reply-To: ".$replyto."\r\n";
         $header .= "MIME-Version: 1.0\r\n";
         $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
         $header .= "This is a multi-part message in MIME format.\r\n";
         $header .= "--".$uid."\r\n";
         $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
         $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
         $header .= $message."\r\n\r\n";
         $header .= "--".$uid."\r\n";
         $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
         $header .= "Content-Transfer-Encoding: base64\r\n";
         $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
         $header .= $content."\r\n\r\n";
         $header .= "--".$uid."--";
         $sent=mail($mailto, $subject, "", $header);
         return $sent;
     }
     $my_file = "somefile.zip";
     $my_path = $_SERVER['DOCUMENT_ROOT']."/your_path_here/";
     $my_name = "Olaf Lederer";
     $my_mail = "my@mail.com";
     $my_replyto = "my_reply_to@mail.net";
     $my_subject = "This is a mail with attachment.";
     $my_message = "Hallo,\r\ndo you like this script? I hope it will help.\r\n\r\ngr. Olaf";
     mail_attachment($my_file, $my_path, "recipient@mail.org", $my_mail, $my_name, $my_replyto, $my_subject, $my_message);
     */
	function mrr_print_contents($use_filename = '', $html = '', $display_mode = 0, $header = "", $footer = "", $show_page_numbers = true, $email_id = 0) {
		// takes a couple POST parameters, writes them to a PDF file, then displays the PDF....email_id gets other attachments to the message inside the PDF...passed to internal function.
		// display_mode: 0 = portrait, 1 = landscape
		//echo $_POST['use_html'];
		
		
	     //generate pdf content and write to html file to conver to pdf
          
          if($html == '' && isset($_POST['use_html'])) {
          	$html = $_POST['use_html'];
          }
          
          if($use_filename == '') {
          	$use_filename = $_POST['use_filename'];
          }
         
		return mrr_print_contents_include($use_filename, $html, $display_mode, $header, $footer, $show_page_numbers,$email_id);
		//echo $pdfContent;
	}
	
	function mrr_print_contents_include($use_filename, $html, $display_mode, $header = "", $footer = "", $show_page_numbers = true, $email_id = 0) {
		// display_mode 0 = portrait, 1 = landscape...................Added email attachments displayed inside the PDF file
		global $defaultsarray;
		ob_start();
		echo '<!DOCTYPE HTML>';
		echo "<html>";
		echo "<head>";
		echo '<link rel="stylesheet" href="style.css" type="text/css">';
		echo '<link rel="stylesheet" href="includes/tablesort_theme/style.css" type="text/css">';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		echo "</head>";
		echo "<body>";
		echo "<br>";
				
		echo stripslashes($html);
		
		echo "</body></html>";		
		
		$pdfContent = ob_get_contents();
		ob_end_clean();
		//ob_end_flush();		
		
		$savedHtml = $use_filename . ".html";
		$savedFile = getcwd() . "\\temp\\" . $use_filename . ".pdf";		
		
		$fp = fopen(getcwd() . "/$savedHtml", "w");
                fwrite($fp, $pdfContent); 
                fclose($fp);

		if($header != '') {
			$savedHtml_header = $use_filename . "_header.html";
			
			$fp = fopen(getcwd() . "/$savedHtml_header", "w");
	                fwrite($fp, $header); 
	                fclose($fp);
		}
		
		if($footer != '') {
			$savedHtml_footer = $use_filename . "_footer.html";
			
			$fp = fopen(getcwd() . "/$savedHtml_footer", "w");
	                fwrite($fp, $footer); 
	                fclose($fp);
		}
		
		$theDoc = new COM("ABCpdf9.Doc") or die ("connection create fail");
		
		//$theDoc->AddImageHTML($pdfContent,0,0,0);

		$w = $theDoc->MediaBox->Width;
		$h = $theDoc->MediaBox->Height;
		$l = $theDoc->MediaBox->Left;
		$b = $theDoc->MediaBox->Bottom;

		
		if($display_mode == 1) {
			/* rotation code (to landscape) */
			
			$theDoc->Transform->Rotate(90,$l,$b);
			$theDoc->Transform->Translate($w,0);
			
			$theDoc->Rect->Width = $h;
			$theDoc->Rect->Height = $w;
		} else {
			// portrait
			$theDoc->Rect->Width = $w;
			$theDoc->Rect->Height = $h;
		}
				
		/* end of landscape rotation code */
		
		$theDoc->Rect->inset(15,15);
		/* create a variable that has the path (i.e. /demo/schedule_form_top.php would be '/demo/') */
		$sub_path = substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-strpos(strrev($_SERVER['PHP_SELF']),"/"));
		
		if(isset($_GET['html_only'])) {
			// output the link to the generated HTML, otherwise, print it as a PDF
			return "http://". $_SERVER['HTTP_HOST'] .$sub_path. $savedHtml;
		}
		
		if(get_option_text_by_id($defaultsarray['invoice_print_style'])) {
			if($header != '') $theDoc->Rect->String = "15 140 " . ($w - 15) ." 505";
		} else {
			if($header != '') $theDoc->Rect->String = "15 140 " . ($w - 15) ." 555";
		}
		/* add the actual document to the PDF file */
		$theID = $theDoc->AddImageURL("http://". get_internal_address() .$sub_path. $savedHtml);

		$m=0;
		while (true) {
			//$theDoc->FrameRect;
			if(!$theDoc->Chainable($theID) || $m >= 1000) break;
			$m++;
			$theDoc->Page = $theDoc->AddPage();
			$theID = $theDoc->AddImageToChain($theID);
		}
				
		for($m=1;$m<=$theDoc->PageCount;$m++) {
			$theDoc->PageNumber = $m;
			if($header != '') {
				if(get_option_text_by_id($defaultsarray['invoice_print_style']) == '1') {
					if($header != '') $theDoc->Rect->String = "15 555 " . ($w - 15) ." 775";
				} else {
					if($header != '') $theDoc->Rect->String = "15 505 " . ($w - 15) ." 775";
				}
				$theDoc->AddImageURL("http://". get_internal_address() .$sub_path. $savedHtml_header);
				//$theDoc->FrameRect();
			}
			if($footer != '') {
				if($header != '') $theDoc->Rect->String = "15 25 " . ($w - 15) ." 140";
				$theDoc->AddImageURL("http://". get_internal_address() .$sub_path. $savedHtml_footer);
			}
			
			
			if($show_page_numbers) {
				$theDoc->Rect->String = "15 15 " . ($w - 15) ." 25";
				$theDoc->AddHtml("<p align='right'><font size='2' face='arial'>Page $m of ".$theDoc->PageCount."</font></p>");
			}
			
			$theDoc->Flatten();
		}
	
		/* Now, rotate the actual page */
		$theID = $theDoc->GetInfo($theDoc->Root,"Pages");
		
		if($display_mode == 1) {
			// rotate the page to landscape
			$theDoc->SetInfo($theID, "/Rotate","90");
		}
				
		/* save the doc */
		$theDoc->Save($savedFile);
		//echo $savedFile;					
	   	
	   	unlink(getcwd(). "\\$savedHtml");
	   	@unlink(getcwd(). "\\$savedHtml_header");
	   	@unlink(getcwd(). "\\$savedHtml_footer");
	   	
	   	return "./temp/".$use_filename . ".pdf";
	}
	function mrr_get_last_driver_load_dedicated($id)
	{
		$dedicated=0;
		$sql = "
			select dedicated_load 
			from load_handler,trucks_log 
			where load_handler.id=trucks_log.load_handler_id 
				and load_handler.deleted=0 
				and trucks_log.dispatch_completed=1
				and driver_id='".sql_friendly($id)."' 
			order by trucks_log.linedate_pickup_eta desc,load_handler.id desc 
			limit 1			
          	";	
          $data = simple_query($sql);
          while($row = mysqli_fetch_array($data))
          {
          	$dedicated=$row['dedicated_load'];	
          }
          return $dedicated;
	}
	
	
	
	function mrr_get_driver_pay_rate($id,$cd)
	{
		$rate=0;
		$sql = "
			select pay_per_hour,
				pay_per_mile,
				pay_per_hour_team,
				pay_per_mile_team,
				charged_per_hour,
				charged_per_mile,
				charged_per_hour_team,
				charged_per_mile_team,
				overtime_hourly_charged,
				overtime_hourly_paid
			from drivers
			where drivers.deleted = 0
				and id='".sql_friendly($id)."'		
			order by id asc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{		
			$rate=$row['pay_per_mile'];
			if($cd==1)	$rate=$row['pay_per_hour'];
			if($cd==2)	$rate=$row['pay_per_mile_team'];
			if($cd==3)	$rate=$row['pay_per_hour_team'];
			if($cd==4)	$rate=$row['charged_per_mile'];			
			if($cd==5)	$rate=$row['charged_per_hour'];
			if($cd==6)	$rate=$row['charged_per_mile_team'];
			if($cd==7)	$rate=$row['charged_per_hour_team'];
			if($cd==8)	$rate=$row['overtime_hourly_charged'];
			if($cd==9)	$rate=$row['overtime_hourly_paid'];	
		}	
		return $rate;
	}
	
	function mrr_truck_odometer_display($date_from,$date_to)
	{
		$miles_tot=0;
		$miles_alt=0;
		$date_start=date("Y-m-d", strtotime("-5 day", strtotime($date_from)));
		$date_end=date("Y-m-d", strtotime("+5 day", strtotime($date_to)));
		
		$html="<div style='max-height:400px; height:400px; min-height:400px; overflow:auto;'>";
		$html.="<table border='0' width='100%'>";
		$html.="
				<tr>
					<td valign='top'><b>Truck</b></td>						
					<td valign='top' align='right'><b>Previous Date</b></td>
					<td valign='top' align='right'><b>Last Date</b></td>
					<td valign='top' align='right'><b>Started</b></td>
					<td valign='top' align='right'><b>Ended</b></td>
					<td valign='top' align='right'><b>Miles</b></td>
				</tr>
		";
		$cntr=0;
		
		$sql="
			select * 
			from trucks 
			where deleted=0 
				and active=1
		";	
		$data=simple_query($sql);
		while( $row=mysqli_fetch_array($data))
		{
			$truck_id=$row['id'];
			$truck_name=$row['name_truck'];
											
			$last=mrr_last_odometer_reading('truck',$truck_id,$date_end);
			//$last['odometer']=$odometer;
			//$last['linedate']=$linedate;
						
			$prev=mrr_last_odometer_reading('truck',$truck_id,$last['linedate']);
			//$prev['odometer']=$odometer;
			//$prev['linedate']=$linedate;		
			
			$valid=1;
			$flag="";
			$flag2="";
			$titler="";
			$titler2="";
			$started=$prev['odometer'];
			$ended=$last['odometer'];
			$diff=0;	
			
			$as_of=date("m/d/Y", strtotime($last['linedate']));
			if($last['linedate']=="0000-00-00 00:00:00")		$as_of="";
			
			$as_of2=date("m/d/Y", strtotime($prev['linedate']));
			if($prev['linedate']=="0000-00-00 00:00:00")		$as_of2="";
						
			if($started==0)
			{
				$started=mrr_get_last_pickup_odometer($truck_id, 1); 	
			}			
						
			$diff=$ended - $started;
			$reversed=$started - $ended;
			
			if($diff==0 || $as_of=="")											$valid=0;
			if($last['linedate'] < $date_start || $last['linedate'] > $date_end)			$valid=0;
			
						
						
			if($valid==1)
			{
				$mrr_classy="color:black;";
				$mrr_classy2="color:black;";
				$mrr_special="";
				if($diff < 0)
				{
					$mrr_classy="color:red; font-weight:bold;";
					$mrr_special="(".number_format($reversed,0).") &nbsp; &nbsp; &nbsp; &nbsp;";
					$miles_alt+=$reversed;	
					$miles_tot+=$diff;	
					$titler=" title='Mileage for ending period is less than when it started.  Use adjusted Miles if this was an error.'";			
				}				
				else
				{
					$miles_alt+=$diff;	
					$miles_tot+=$diff;
				}				
				
				if($prev['linedate'] < $date_start || $prev['linedate'] > $date_end)			
				{
					$mrr_classy2="color:brown;";
					$flag="<b>Missing Reading</b>";
					$flag2="<b>Old</b>";
					$titler2=" title='Previous Date reading is outside of date range.  Previous month reading must be missing.'";
				}
				
				
				$cntr++;
				$html.="
					<tr bgcolor='#".( $cntr%2==0 ? 'DDDDDD'  : 'EEEEEE')."'>
						<td valign='top'><a href='admin_trucks.php?id=".$truck_id."' target='_blank' title='Click to view the truck'>".$truck_name."</a></td>						
						<td valign='top' align='right'><span style='".$mrr_classy2."'".$titler2.">".$flag2."</span> &nbsp; &nbsp; <span style='".$mrr_classy."'>".$as_of2."</span></td>
						<td valign='top' align='right'><span style='".$mrr_classy."'>".$as_of."</span></td>
						<td valign='top' align='right'><span style='".$mrr_classy."'>".number_format($started,0)."</span></td>
						<td valign='top' align='right'><span style='".$mrr_classy."'>".number_format($ended,0)."</span></td>
						<td valign='top' align='right'><span style='".$mrr_classy2."'".$titler2.">".$flag."</span> &nbsp; &nbsp; <span style='".$mrr_classy."'>".$mrr_special."".number_format($diff,0)."</span></td>
					</tr>
				";
			}
		}
		$html.="
				<tr>
					<td valign='top' align='left' colspan='5'><b>Total Miles</b></td>
					<td valign='top' align='right'><b>".number_format($miles_tot,0)."</b></td>
				</tr>
				";
		if($miles_alt!=$miles_tot)
		{
			$html.="
				<tr>
					<td valign='top' align='left' colspan='5'><span style='color:red;'><b>Adjusted Total Miles</b></span>  Check odometer reading for mileage drop above in <span style='color:red;'><b>red</b></span>. Use Adjusted Miles if this was an error.</td>
					<td valign='top' align='right'><span style='color:red;'><b>".number_format($miles_alt,0)."</b></span></td>
				</tr>
				";	
		}					
		$html.="</table>";		
		$html.="</div>";
		
		$res['html']=$html;
		$res['tot']=$miles_tot;
		$res['alt']=$miles_alt;
		return $res;
	}
	
	function mrr_get_last_pickup_odometer($equipment_id, $equipment_type_id) 
	{
		$mileage=0;
		$sql = "
			select miles_pickup			
			from equipment_history
			where equipment_id = '".sql_friendly($equipment_id)."'
				and equipment_type_id = '".sql_friendly($equipment_type_id)."'
				and deleted = 0
			order by linedate_aquired desc 
			limit 1
		";
		$data = simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$mileage=$row['miles_pickup'];	
		}
		return $mileage;
	}
	
	function mrr_get_equipment_maint_notice_warning($equip_type='',$equip_id=0)
	{
		$warn="";
		
		$sql = "
			select maint_requests.id,
				maint_requests.maint_desc,
				maint_requests.linedate_scheduled,
				maint_requests.recur_flag,
				maint_requests.recur_days,
				maint_requests.recur_mileage,
				DATE_ADD(maint_requests.linedate_scheduled,Interval maint_requests.recur_days Day) as next_run						
			from maint_requests,option_values
			where linedate_completed = '0000-00-00 00:00:00'
				and maint_requests.deleted = 0
				and option_values.id=maint_requests.equip_type
				and option_values.fname='".sql_friendly($equip_type)."'
				and maint_requests.ref_id='".sql_friendly($equip_id)."'
			order by linedate_scheduled asc
		";
		$data = simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$id=$row['id'];	
			$desc=$row['maint_desc'];
			$scheduled=date("m/d/Y", strtotime($row['linedate_scheduled']));
			$recur_flag=$row['recur_flag'];
			$recur_days=$row['recur_days'];
			$recur_miles=$row['recur_mileage'];
			$next_run=date("m/d/Y", strtotime($row['next_run']));
		}
		if(mysqli_num_rows($data) > 0)
		{
			$warn=" <a href='report_maint_requests.php?".$equip_type."_id=".$equip_id."&date_from=".date("Y-m-d",strtotime($row['linedate_scheduled']))."' target='_blank' title='This ".
					$equip_type." has Maintenance Needs...Please check the report.' style=' color:red; text-decoration:blink;'><b>M!</b></a> ";	
		}
		
		return $warn;
	}
	function mrr_get_equipment_maint_notice_warning_array($equip_type='',$color_change="")
	{
		$cntr=0;
		$arr[0]=0;
		$link[0]="";
		
		if(trim($color_change)=="")		$color_change="red";
		
		$sql = "
			select distinct(maint_requests.ref_id),linedate_scheduled,maint_requests.maint_desc					
			from maint_requests,option_values
			where linedate_completed = '0000-00-00 00:00:00'
				and maint_requests.deleted = 0
				and option_values.id=maint_requests.equip_type
				and option_values.fname='".sql_friendly($equip_type)."'
			order by linedate_scheduled asc
		";
		$data = simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{			
			$equip_id=$row['ref_id'];	
			$desc=trim($row['maint_desc']);
			
			$arr[ $cntr ]=$equip_id;
			$link[ $cntr ]="<a href='report_maint_requests.php?".$equip_type."_id=".$equip_id."&date_from=".date("Y-m-d",strtotime($row['linedate_scheduled']))."' target='_blank' title='".
                 str_replace("'","", $desc)."' style=' color:".$color_change."; text-decoration:blink;'><b>Mt</b></a>";
			        //This ".$equip_type." has Maintenance Needs...Please check the report.
			/*
			$tester="";
			if($equip_type=="truck")
			{
				$tester=mrr_find_fed_inspection_last_completed(1,$equip_id);
			}
			else
			{
				$tester=mrr_find_fed_inspection_last_completed(2,$equip_id);	
			}
			if(trim($tester)!="")	$link[ $cntr ]=$tester;
			*/
			
			$cntr++;		
		}
				
		$res['num']=$cntr;
		$res['arr']=$arr;
		$res['links']=$link;
		
		return $res;
	}
	function mrr_find_fed_inspection_last_completed($equip_type,$xref_id)
	{
		$rep="";		
		if($xref_id==0)		return $rep;		//bypass...no point if not truck/trailer ID
		
		$due_date=date("Y-m-d",time());
		$due_15=date("Y-m-d",strtotime("-15 days",time()));
		$due_30=date("Y-m-d",strtotime("-30 days",time()));
		
		if($equip_type==2)
		{	//trailers
			return $rep;		//skip on all trailers...
			
			$sql="
				select fed_test_ignore,
					linedate_last_fed
				from trailers 
				where id='".sql_friendly($xref_id)."'
			";
			$data = simple_query($sql);
			if($row=mysqli_fetch_array($data))
     		{
     			$last=date("Y-m-d",strtotime("+1 year",strtotime($row['linedate_last_fed'])));
     			$last2=date("m/d/Y",strtotime("+1 year",strtotime($row['linedate_last_fed'])));
     			
     			if($row['fed_test_ignore'] > 0)	return $rep;		//bypass...ignoreed trailer...not needed.
     			
     			if($row['linedate_last_fed']=="0000-00-00 00:00:00")	
     			{	//not set...past due.
     				$rep="<a href='admin_trailers.php?id=".$xref_id."' target='_blank' title='Federal Inspection has not been completed at all. Please correct this ASAP.' style='color:red; text-decoration:blink;'><b>Fd</b></a>";
     				return $rep;	
     			}
     			
     			if($last < $due_date)	
     			{	//past due.
     				$rep="<a href='admin_trailers.php?id=".$xref_id."' target='_blank' title='Federal Inspection is Past Due (".$last2."). Please conduct the Federal Insepction on this Trailer.' style='color:red; text-decoration:blink;'><b>Fd</b></a>";
     				return $rep;	
     			}
     			if($last < $due_15)	
     			{	//within 15 days of due date.
     				$rep="<a href='admin_trailers.php?id=".$xref_id."' target='_blank' title='Federal Inspection is coming due ".$last2.". Please update the Federal Insepction on this Trailer.' style='color:orange; text-decoration:blink;'><b>Fd</b></a>";
     				return $rep;	
     			}
     			if($last < $due_30)	
     			{	//within 30 days of due date.
     				$rep="<a href='admin_trailers.php?id=".$xref_id."' target='_blank' title='Federal Inspection will be due on ".$last2.". Please update the Federal Insepction on this Trailer.' style='color:green; text-decoration:blink;'><b>Fd</b></a>";
     				return $rep;	
     			}
     		}
		}
		else
		{	//trucks
			$sql="
				select fd_inspection_date,company_owned
				from trucks 
				where id='".sql_friendly($xref_id)."'
			";
			$data = simple_query($sql);
     		if($row=mysqli_fetch_array($data))
     		{
     			if($row['company_owned'] == 0)			return $rep;
     			
     			$last=date("Y-m-d",strtotime("+1 year",strtotime($row['fd_inspection_date'])));
     			$last2=date("m/d/Y",strtotime("+1 year",strtotime($row['fd_inspection_date'])));
     			
     			if($row['fd_inspection_date']=="0000-00-00 00:00:00")	
     			{	//not set...past due.
     				$rep="<a href='admin_trucks.php?id=".$xref_id."' target='_blank' title='Federal Inspection has not been completed at all. Please correct this ASAP.' style='color:red; text-decoration:blink;'><b>Fd</b></a>";
     				return $rep;	
     			}
     			
     			if($last < $due_date)	
     			{	//past due.
     				$rep="<a href='admin_trucks.php?id=".$xref_id."' target='_blank' title='Federal Inspection is Past Due (".$last2."). Please conduct the Federal Insepction on this Truck.' style='color:red; text-decoration:blink;'><b>Fd</b></a>";
     				return $rep;	
     			}
     			if($last < $due_15)	
     			{	//within 15 days of due date.
     				$rep="<a href='admin_trucks.php?id=".$xref_id."' target='_blank' title='Federal Inspection is coming due ".$last2.". Please update the Federal Insepction on this Truck.' style='color:orange; text-decoration:blink;'><b>Fd</b></a>";
     				return $rep;	
     			}
     			if($last < $due_30)	
     			{	//within 30 days of due date.
     				$rep="<a href='admin_trucks.php?id=".$xref_id."' target='_blank' title='Federal Inspection will be due on ".$last.". Please update the Federal Insepction on this Truck.' style='color:green; text-decoration:blink;'><b>Fd</b></a>";
     				return $rep;	
     			}
     		}
		}
		
		
		return $rep;
	}
	
	
	function mrr_log_search_user_loads($date_from,$date_to,$user_id,$driver_id,$mode,$truck_id,$trailer_id,$load_id,$dispatch_id,$stop_id,$flag,$deleted)
	{
		$html="<table border='0' width='100%'>";
		$cntr=0;
		
		if($mode==1)
		{
			$html.="
					<tr>
						<td valign='top'><b>User Name</b></td>	
						<td valign='top'><b>Date</b></td>
						<td valign='top'><b>Time</b></td>
						<td valign='top'><b>IP Address</b></td>	
						<td valign='top'><b>Invalid User Name</b></td>	
					</tr>
				";			
			$search_date_range = "";
			
			$sql = "
				select log_login.*,
					users.name_first,
					users.name_last			
				from log_login,users			
				where log_login.user_id=users.id
					and log_login.linedate_added >= '".$date_from."' and log_login.linedate_added < '".$date_to."' 
									
					".($user_id > 0 ? " and log_login.user_id = '".sql_friendly($user_id)."'" : '') ."
					
				order by log_login.linedate_added asc
			";
			$data = simple_query($sql);
			while($row=mysqli_fetch_array($data))
			{
				$html.="
					<tr".( ($cntr%2)==0 ? " bgcolor='#EEEEEE'" : "").">
						<td valign='top'>".$row['name_first']." ".$row['name_last']."</td>	
						<td valign='top'>".date("m/d/Y",strtotime($row['linedate_added']))."</td>
						<td valign='top'>".date("H:i",strtotime($row['linedate_added']))."</td>
						<td valign='top'>".$row['ip_address']."</td>	
						<td valign='top'>".(isset($row['invalid_username']) ? trim($row['invalid_username']) : '')."</td>	
					</tr>
				";
				$cntr++;
			}
		}
		elseif($mode==2)
		{
			$html.="
					<tr>
						<td valign='top'><b>LogID</b></td>
						<td valign='top'><b>User Name</b></td>	
						<td valign='top'><b>Date</b></td>
						<td valign='top'><b>Time</b></td>
						<td valign='top'><b>Page</b></td>	
						<td valign='top'><b>Variables</b></td>
						<td valign='top' width='200'><b>Notes</b></td>
						<td valign='top'><b>Driver</b></td>
						<td valign='top'><b>Truck</b></td>
						<td valign='top'><b>Trailer</b></td>
						<td valign='top'><b>Load</b></td>
						<td valign='top'><b>Dispatch</b></td>
						<td valign='top'><b>Stop</b></td>
						<td valign='top'><b>Flag</b></td>
						<td valign='top'><b>Action</b></td>
					</tr>
				";			
			$search_date_range = "";
						
			$sql = "
				select user_action_log.*,
					users.name_first,
					users.name_last,
					(select name_truck from trucks where trucks.id=user_action_log.truck_id limit 1) as name_truck,
					(select trailer_name from trailers where trailers.id=user_action_log.trailer_id limit 1) as name_trailer,
					(select CONCAT(name_driver_first,' ',name_driver_last) from drivers where drivers.id=user_action_log.driver_id limit 1) as name_driver	
				from ".mrr_find_log_database_name()."user_action_log,users		
				where user_action_log.user_id=users.id
					and user_action_log.linedate_added >= '".$date_from."' and user_action_log.linedate_added < '".$date_to."' 
									
					".($user_id > 0 ? " and user_action_log.user_id = '".sql_friendly($user_id)."'" : '') ."
					".($driver_id > 0 ? " and user_action_log.driver_id = '".sql_friendly($driver_id)."'" : '') ."
					
					".($truck_id > 0 ? " and user_action_log.truck_id = '".sql_friendly($truck_id)."'" : '') ."
					".($trailer_id > 0 ? " and user_action_log.trailer_id = '".sql_friendly($trailer_id)."'" : '') ."
					".($load_id > 0 ? " and user_action_log.load_handler_id = '".sql_friendly($load_id)."'" : '') ."
					".($dispatch_id > 0 ? " and user_action_log.dispatch_id = '".sql_friendly($dispatch_id)."'" : '') ."
					".($stop_id > 0 ? " and user_action_log.stop_id = '".sql_friendly($stop_id)."'" : '') ."
					
					".($flag > 0 ? " and user_action_log.flag = '".sql_friendly($flag)."'" : '') ."
					".($deleted >= 0 ? " and user_action_log.deleted = '".sql_friendly($deleted)."'" : '') ."
					
				order by user_action_log.linedate_added asc
			";
			$data = simple_query($sql);
			while($row=mysqli_fetch_array($data))
			{
				$linker="<a href='".$row['page_url']."?".$row['page_get']."' title='Click to view this page yourself.' target='_blank'>".$row['page_url']."</a>";
				
				if(strtolower($row['page_url'])=="/ajax.php")	$linker="<span style='color:green;'>".$row['page_url']."</span>";
				
				$truck_link='';
				if($row['truck_id'] > 0)			$truck_link="<a href='admin_trucks.php?id=".$row['truck_id']."' title='Click to view this truck' target='_blank'>".$row['name_truck']."</a>";
				
				$trailer_link='';
				if($row['trailer_id'] > 0)		$trailer_link="<a href='admin_trailers.php?id=".$row['trailer_id']."' title='Click to view this trailer' target='_blank'>".$row['name_trailer']."</a>";
				
				$driver_link='';
				if($row['driver_id'] > 0)		$driver_link="<a href='admin_drivers.php?id=".$row['driver_id']."' title='Click to view this driver' target='_blank'>".$row['name_driver']."</a>";
				
				$load_link='';
				if($row['load_handler_id'] > 0)	$load_link="<a href='manage_load.php?load_id=".$row['load_handler_id']."' title='Click to view this load' target='_blank'>".$row['load_handler_id']."</a>";
				
				$disp_link='';
				if($row['dispatch_id'] > 0)		$disp_link="<a href='add_entry_truck.php?id=".$row['dispatch_id']."' title='Click to view this dispatch' target='_blank'>".$row['dispatch_id']."</a>";
				
				$stop_link='';
				if($row['stop_id'] > 0)			$stop_link=$row['stop_id'];
				
				$flag_link='';
				if($row['flag'] > 0)			$flag_link=$row['flag'];
				
				$html.="
					<tr".( ($cntr%2)==0 ? " bgcolor='#EEEEEE'" : "").">
						<td valign='top'>".$row['id']."</td>
						<td valign='top'>".$row['name_first']." ".$row['name_last']."</td>	
						<td valign='top'>".date("m/d/Y",strtotime($row['linedate_added']))."</td>
						<td valign='top'>".date("H:i",strtotime($row['linedate_added']))."</td>
						<td valign='top'>".$linker."</td>
						<td valign='top'>".$row['page_get']."</td>						
						<td valign='top'>".$row['page_notes']."</td>	
						<td valign='top'>".$driver_link."</td>
						<td valign='top'>".$truck_link."</td>	
						<td valign='top'>".$trailer_link."</td>	
						<td valign='top'>".$load_link."</td>	
						<td valign='top'>".$disp_link."</td>	
						<td valign='top'>".$stop_link."</td>
						<td valign='top'>".$flag_link."</td>
						<td valign='top'>".$row['page_action']."</td>
					</tr>
				";
				$cntr++;
			}
		}
		else
		{
			$html.="
					<tr>
						<td valign='top'><b>No Mode Selected.</b>  Select Activity Log Report Mode</td>	
					</tr>
				";						
		}
		$html.="</table><br><center><b>".$cntr." Results Found.</b></center>";
		return $html;
	}
	function mrr_log_activity_report_mode_selector($field,$pre,$prompt)
	{
		$maxy=2;
		$html="<select name='".$field."' id='".$field."'>";
				
		$selector="";			if($pre==0 || $pre=="")	$selector=" selected";
		$html.="<option value='0'".$selector.">".$prompt."</option>";	
		
		for($i=1;$i<=$maxy;$i++)
		{
			$selector="";		if($pre==$i)			$selector=" selected";
			$html.="<option value='".$i."'".$selector.">".mrr_decode_activity_report_modes($i)."</option>";		
		}
		
		$html.="</select>";		
		return $html;
	}
	function mrr_decode_activity_report_modes($mode)
	{
		$moder[0]="";
		$moder[1]="Login Log";
		$moder[2]="User Action Log";
		$moder[3]="";
		$moder[4]="";
		$moder[5]="";
		
		return trim( $moder[ $mode ] );	
	}
	function mrr_set_user_action_log($user,$url,$args,$refer,$driver,$truck,$trailer,$load,$dispatch,$stop,$notes)
	{
		global $datasource;

		$sql="
			insert into ".mrr_find_log_database_name()."user_action_log
					(id,
					user_id, 
					linedate_added,
					page_url,
					page_get,
					page_action,
					driver_id,
					truck_id,
					trailer_id,
					load_handler_id,
					dispatch_id,
					stop_id,
					flag,
					deleted,
					page_notes)
			values (NULL,
				'".sql_friendly($user)."',
				NOW(),
				'".sql_friendly($url)."',
				'".sql_friendly($args)."',
				'".sql_friendly($refer)."',
				'".sql_friendly($driver)."',
				'".sql_friendly($truck)."',
				'".sql_friendly($trailer)."',
				'".sql_friendly($load)."',
				'".sql_friendly($dispatch)."',
				'".sql_friendly($stop)."',
				0,
				0,
				'".sql_friendly($notes)."')
		";		
		simple_query($sql);
		$myid=mysqli_insert_id($datasource);
		return $myid;
	}
	function mrr_set_user_action_log_flag($id,$flag)
	{
		$sql="
			update ".mrr_find_log_database_name()."user_action_log set
					flag='".sql_friendly($flag)."'
			where id='".sql_friendly($id)."'
		";		
		simple_query($sql);
	}
	function mrr_set_user_action_log_delete($id,$del)
	{
		$sql="
			update ".mrr_find_log_database_name()."user_action_log set
					deleted='".sql_friendly($del)."'
			where id='".sql_friendly($id)."'
		";		
		simple_query($sql);
	}
	
	//sections for Comparison Report changing...labels and account inclusion per item.  Added May 2012.
	function mrr_get_budget_comparison_sections($section,$cd=0)
	{
		$budget_name="";
		
		$sql="
			select budget_name,notes 
			from comparison_sections
			where id='".sql_friendly($section)."'
		";		
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$budget_name=trim($row['budget_name']);	
			if($cd==1)
			{
				$budget_name=trim($row['notes']);		
			}
		}
		return $budget_name;
	}
	function mrr_get_budget_all_comparison_section_items($section,$active_only=0)
	{		
		$cntr=0;
		$arr[0]=0;
		$sector[0]=0;
		$list[0]="";
		$dater[0]="";
		$all_listed="";
		
		$order=" order by active desc,section_id asc,account_code asc";
		if($active_only>0)		$order="order by section_id asc,account_code asc";
		
		$where=" and section_id='".sql_friendly($section)."'";
		if($section==0)		$where="";
		if($active_only>0)		$where.=" and active='1'";
					
		$sql="
			select id,account_code,section_id,linedate_added 
			from comparison_section_items
			where deleted=0
			".$where."	
			".$order."
		";		
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$arr[ $cntr ]=$row['id'];
			$sector[ $cntr ]=$row['section_id'];
			$list[ $cntr ]=trim($row['account_code']);	
			$dater[ $cntr ]=$row['linedate_added'];	
			
			$all_listed.=trim($row['account_code']).",";
			
			$cntr++;		
		}
		$res['num']=$cntr;
		$res['arr']=$arr;
		$res['sector']=$sector;
		$res['list']=$list;
		$res['dates']=$dater;
		$res['all']=$all_listed;
		$res['sql']=$sql;
		return $res;
	}
	function mrr_get_budget_comparison_section_item($section_item)
	{			
		$sql="
			select *
			from comparison_section_items
			where id='".sql_friendly($section_item)."'			
		";		
		$data=simple_query($sql);
		$row=mysqli_fetch_array($data);
		return $row;
	}
	
	function mrr_validate_driver_truck_trailer_attachment($driver=0)
	{
		$valid="false";
		$sql = "
     		select *
     		
     		from drivers
     		where active = 1
     			and deleted = 0
     			and hide_available = 0
     			and id not in (
     							select driver_id
     							
     							from trucks_log 
     							where deleted = 0 
     								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
     								and dispatch_completed = 0
     						)
     
     			and id not in (
     							select driver2_id
     							
     							from trucks_log 
     							where deleted = 0 
     								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
     								and dispatch_completed = 0
     						)
     
     			and id not in (
     							select driver_id
     							
     							from drivers_unavailable
     							where drivers_unavailable.deleted = 0
     								and drivers_unavailable.driver_id = drivers.id
     								and linedate_start <= '".date("Y-m-d")."'
     								and linedate_end >= '".date("Y-m-d")."'
     			)
     
     		order by name_driver_last, name_driver_first, id
     	";
     	$data = simple_query($sql);	
     	
     	$dlist="
     		<table border='0'>
     		<tr>
     			<td valign='top' colspan='3'><b>Available Drivers</b></td>
     		</tr>
     		<tr>
     			<td valign='top'><b>ID</b></td>
     			<td valign='top'><b>First Name</b></td>
     			<td valign='top'><b>Last Name</b></td>
     			<td valign='top'><b>Truck</b></td>
     			<td valign='top'><b>Trailer</b></td>
     		</tr>
     	";
     	while($row=mysqli_fetch_array($data))
     	{
          	if($driver>0)
          	{
          		if($row['id']==$driver) 		$valid="true";	
          	}
          	
          	// get any attached trucks
          	$trucker="";         	
     		$sql2 = "
     			select attached_truck_id,
     				trucks.name_truck
     				
     			from drivers
     				left join trucks on trucks.id = drivers.attached_truck_id
     			where drivers.id = '".sql_friendly($row['id'])."'
     		";
     		
     		$data2 = simple_query($sql2);
          	while($row2=mysqli_fetch_array($data2))
          	{
          		$trucker="<a href='admin_trucks.php?id=".$row2['attached_truck_id']."'>".$row2['name_truck']."</a>";
          	 	$valid="false";
           	}
          	
          	// get any attached trailers
          	$trailer="";
     		$sql2 = "
     			select attached_trailer_id,
     				trailers.trailer_name
     				
     			from drivers
     				left join trailers on trailers.id = drivers.attached_trailer_id
     			where drivers.id = '".sql_friendly($row['id'])."'
     		";
     		
     		$data2 = simple_query($sql2);
          	while($row2=mysqli_fetch_array($data2))
          	{
          		$trailer="<a href='admin_trailers.php?id=".$row2['attached_trailer_id']."'>".$row2['trailer_name']."</a>";
          		$valid="false";
          	}
          	
          	$dlist.="
          		<tr>
          			<td valign='top'>".$row['id']."</td>
          			<td valign='top'>".$row['name_driver_first']."</td>
          			<td valign='top'>".$row['name_driver_last']."</td>
          			<td valign='top'>".$trucker."</td>
          			<td valign='top'>".$trailer."</td>
          		</tr>
          	";
     	}
     	$dlist.="
     		</table>
     	";
     	$res['valid']=$valid;
     	$res['table']=$dlist;
     	return $res;
	}	
	
	function mrr_check_drivers_for_loads()
	{
		
     	$sql3 = "
			update drivers set
				driver_has_load='0'
			where active = 1
				and driver_has_load = 1
				and deleted = 0
				AND (
						drivers.id not IN (
								SELECT driver_id
								FROM trucks_log
								WHERE (driver_id=drivers.id)
								 	AND trucks_log.deleted = 0
								 	AND trucks_log.dispatch_completed = 0
								)
						and drivers.id not IN (
								SELECT driver2_id
								FROM trucks_log
								WHERE (driver2_id=drivers.id)
								 	AND trucks_log.deleted = 0
								 	AND trucks_log.dispatch_completed = 0
						)
					)
		";	
		simple_query($sql3);
		
		$sql = "
			update drivers
			set driver_has_load = 1
			WHERE drivers.active = 1
				AND drivers.deleted = 0
				and driver_has_load = 0
				AND (
						drivers.id IN (
								SELECT driver_id
								FROM trucks_log
								WHERE (driver_id=drivers.id)
								 	AND trucks_log.deleted = 0
								 	AND trucks_log.dispatch_completed = 0
								)
						OR drivers.id IN (
								SELECT driver2_id
								FROM trucks_log
								WHERE (driver2_id=drivers.id)
								 	AND trucks_log.deleted = 0
								 	AND trucks_log.dispatch_completed = 0
						)
					)
		";
		simple_query($sql);
		

	}
	
	function mrr_update_session_cookie($id,$username,$cookie_value="")
	{			
		global $defaultsarray;
		
		$minutes=(int) $defaultsarray['login_cookie_expiration'];		
		$mrr_cookie_bake= time() +  (60 * $minutes);				  /* expires in MINUTES */
		
		if( trim($username)!="" && $id > 0 )
		{	//user is already logged in...
			$_SESSION['id'] = $id;
			$_SESSION['user_id'] = $id;
			$_SESSION['username'] = $username;
			$_SESSION['conard_trucking_logged_in'] = 1;
				
			$mrr_cookie=createuuid();
			
			setcookie("uuid", $mrr_cookie, $mrr_cookie_bake);
			
			$sql = "
				update users set
					uuid='".sql_friendly($mrr_cookie)."'
				where id='".sql_friendly($id)."'
			";
			simple_query($sql);
			
			$sql = "
				insert into user_cookies
					(id,
					linedate_added,
					user_id,
					time_secs,
					uuid)
				values
					(NULL,
					NOW(),
					'".sql_friendly($id)."',
					'".sql_friendly( ($minutes * 60) )."',
					'".sql_friendly($mrr_cookie)."')
			";
			simple_query($sql);
		}	
		elseif(trim($cookie_value)!="")
		{	//check session			
			$sql = "
				select user_id 
				from user_cookies
				where uuid='".sql_friendly(trim($cookie_value))."'
				order by id desc
				limit 1
			";
			$data=simple_query($sql);
			if($row=mysqli_fetch_array($data))
			{
				$id=$row['user_id'];
				$sql2 = "
					select * 
					from users
					where id='".sql_friendly($id)."'
				";
				$data2=simple_query($sql2);
				if($row2=mysqli_fetch_array($data2))
				{
					$username=trim($row2['username']);	
					setcookie("uuid", $cookie_value, $mrr_cookie_bake);	//reset the cookie with new expiration date
					
					$_SESSION['id'] = $id;
					$_SESSION['user_id'] = $id;
					$_SESSION['username'] = $username;
					$_SESSION['conard_trucking_logged_in'] = 1;
					
					$sql3 = "
						update users set
							uuid='".sql_friendly($cookie_value)."'
						where id='".sql_friendly($id)."'
					";
					simple_query($sql3);
				}	//end reset by cookie	
			}	//found cookie for this user....		
		}	//end elseif
		return $id;
	}
	
	function mrr_find_truck_driver_by_date($truck_id,$date)
	{
		$res['load_id']=0;
		$res['dispatch_id']=0;
		$res['driver_id']=0;
		$res['driver2_id']=0;
		$res['trailer_id']=0;
		$res['trailer']="";
		$res['origin']="";
		$res['origin_state']="";
		$res['destination']="";
		$res['destination_state']="";
		
		$res['driver_name']="";
		$res['driver2_name']="";
		$res['trailer_name']="";
		
		if($truck_id>0)
		{
			$sql = "
     			select trucks_log.*,
     				(select trailer_name from trailers where trailers.id=trucks_log.trailer_id) as trailer_name,
     				(select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last)  from drivers where drivers.id=trucks_log.driver_id ) as driver_name,
     				(select CONCAT(drivers.name_driver_first,' ',drivers.name_driver_last)  from drivers where drivers.id=trucks_log.driver2_id) as driver2_name     				 
     			from trucks_log
     			where trucks_log.truck_id='".sql_friendly($truck_id) ."'
     				and trucks_log.deleted<=0     				     				
     			order by trucks_log.linedate_dropoff_eta desc 
     			
     		";	
     		$data=simple_query($sql);
			if($row=mysqli_fetch_array($data))
			{
     			$res['load_id']=$row['load_handler_id'];
     			$res['dispatch_id']=$row['id'];
     			$res['driver_id']=$row['driver_id'];
     			$res['driver2_id']=$row['driver2_id'];
     			$res['trailer_id']=$row['trailer_id'];
     			$res['trailer']=$row['trailer'];
     			$res['origin']=$row['origin'];
     			$res['origin_state']=$row['origin_state'];
     			$res['destination']=$row['destination'];
     			$res['destination_state']=$row['destination_state'];	
     			
     			$res['driver_name']=$row['driver_name'];
				$res['driver2_name']=$row['driver2_name'];
				
				$res['trailer_name']=$row['trailer_name'];
			}	//end if			
		}	//end truck selected
		
		return $res;
	}
	
	function show_page_time() {
		global $page_start;
		global $query_count;
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $page_start), 4);
		return $total_time . " (queries: ".$query_count.")";
		
	}
	
	function mrr_get_daily_cost_from_truck_logs($start_date,$end_date)
	{
		$cost_cnt=0;
		$cost_total=0;
		$cost_avg=0;
		
		$sql = "
     			select trucks_log.daily_cost
     			from trucks_log
     				left join load_handler on load_handler.id=trucks_log.load_handler_id
     			where trucks_log.deleted<=0
     				and load_handler.deleted<=0
     				and trucks_log.linedate_pickup_eta>='".date("Y-m-d",strtotime($start_date))." 00:00:00' 
     				and trucks_log.linedate_pickup_eta<='".date("Y-m-d",strtotime($end_date))." 23:59:59' 
     				and (trucks_log.daily_run_otr > 0 or trucks_log.daily_run_hourly > 0)  				
     			order by trucks_log.linedate_pickup_eta asc
     		";	
     	$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$cost_total+=$row['daily_cost'];
			$cost_cnt++;
		}
		if($cost_cnt > 0)	$cost_avg= ($cost_total / $cost_cnt);
		
		$res['total']=$cost_total;
		$res['num']=$cost_cnt;
		$res['avg']=$cost_avg;
		return $res;
	}
	
	function mrr_trailer_drop_and_switch($arr) 
	{		
		$dispatch_id=$arr['dispatch_id'];
		$stop_id=$arr['stop_id'];
		$driver_id=$arr['driver_id'];
		$customer_id=$arr['customer_id'];		
		
		$trailer1_id=$arr['trailer1_id'];
		$trailer2_id=$arr['trailer2_id'];		
		
		$local_city=$arr['location_city'];
		$local_state=$arr['location_state'];
		$local_zip=$arr['location_zip'];
		
		$notes=$arr['notes'];
		$notes.="  Switch trailer from ID ".$trailer1_id." to ID ".$trailer2_id.".";
		
		$linedate=date("Y-m-d", strtotime($arr['linedate']));
		
		$dedicated=$arr['dedicated_trailer'];
		
		/*
		
		//if already dropped trailer with another, remove them to add new trailer...
		$sql3 = "
			update trailers_dropped set					
				deleted=1
			where trailer_id = '".$trailer1_id."'
				and linedate>='".$linedate." 00:00:00'
				and linedate<='".$linedate." 23:59:59'
				and LOCATE('Quick Trailer Drop.',notes)>0
			";
		simple_query($sql3);
		
		//now make new trailer drop with this trailer...		
		$sql = "
				insert into trailers_dropped
					(linedate_added,
					created_by_user_id)
					
				values (now(),
					'".sql_friendly($_SESSION['user_id'])."')
			";
		simple_query($sql);
		$trailer_drop_id = mysql_insert_id();	
				
		$sql = "
			update trailers_dropped
			
			set linedate = '".$linedate."',
				trailer_id = '".sql_friendly($trailer1_id)."',
				customer_id = '".sql_friendly($customer_id)."',
				location_city = '".sql_friendly($local_city)."',
				location_state = '".sql_friendly($local_state)."',
				location_zip = '".sql_friendly($local_zip)."',
				notes = '".sql_friendly($notes)."',
				drop_completed = '1',
				dedicated_trailer = '".$dedicated."'
				
			where id = '".sql_friendly($trailer_drop_id)."'
		";
		simple_query($sql);
		
		
		//create switch record...
		$trailer1_cost=mrr_get_trailer_cost($trailer1_id);
		if($trailer1_cost > 0)		$trailer1_cost=mrr_get_option_variable_settings('Trailer Expense');
		
		$trailer2_cost=0;
		if($trailer2_id > 0)	
		{
			$trailer2_cost=mrr_get_trailer_cost($trailer2_id);
			if($trailer2_cost > 0)	$trailer2_cost=mrr_get_option_variable_settings('Trailer Expense');
		}		
		
		$sql = "
			insert into trailer_switched
				(id,
				linedate_added,
				linedate,
				dispatch_id,
				stop_id,
				deleted,
				old_trailer_id,
				new_trailer_id,
				old_trailer_cost,
				new_trailer_cost)
			values
				(NULL,
				NOW(),
				NOW(),
				'".sql_friendly($dispatch_id)."',
				'".sql_friendly($stop_id)."',
				0,
				'".sql_friendly($trailer1_id)."',
				'".sql_friendly($trailer2_id)."',
				'".sql_friendly($trailer1_cost)."',
				'".sql_friendly($trailer2_cost)."')
		";
		simple_query($sql);
		
		//now make sure there is only one switch for this dispatch and stop... but other stops on dispatch may switch again...
		$sql2="
			select * 
			from trailer_switched 
			where deleted<=0 
				and dispatch_id='".sql_friendly($dispatch_id)."' 
				and stop_id='".sql_friendly($stop_id)."'
		";
		$data2=simple_query($sql2);
		$mn2=mysqli_num_rows($data2);
		if($mn2 > 1)
		{
			$lim=$mn2-1;
			
			$sql2="
			update trailer_switched set
				deleted=1 
			where dispatch_id='".sql_friendly($dispatch_id)."' 
				and stop_id='".sql_friendly($stop_id)."'
			order by id asc 
			limit ".$lim."
			";
			simple_query($sql2);	
			
			
		}
		
		
		$switched="";
		if($trailer2_id > 0)
		{
			
			$sql = "
				update drivers set					
					attached_trailer_id = '".sql_friendly($trailer2_id)."'
				where id = '".$driver_id."'
			";
			simple_query($sql);
			
			//,dropped_trailer='1'
			$sql = "
				update trucks_log set					
					trailer_id = '".sql_friendly($trailer2_id)."',
					linedate_updated=NOW()
					
				where id = '".$dispatch_id."'
			";
			simple_query($sql);
			
		}
		*/
		$sql2="";
		
		return $sql2;
	}
	
	
	
	function mrr_fetch_driver_name($driverid)
	{
		$namer="";
		
		$sql="
			select name_driver_first,name_driver_last 
			from drivers
			where id='".sql_friendly($driverid)."'
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$namer=$row['name_driver_first']." ".$row['name_driver_last'];
		}
		return $namer;	
	}
	function mrr_fetch_set_employer_id($driverid)
	{
		$emp=0;
		
		$sql="
			select employer_id 
			from drivers
			where id='".sql_friendly($driverid)."'
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$emp=$row['employer_id'];
		}
		return $emp;	
	}
	function mrr_fetch_set_employer($empid)
	{
		$emp="";
		
		$sql="
			select fvalue 
			from option_values
			where id='".sql_friendly($empid)."'
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$emp=$row['fvalue'];
		}
		return $emp;	
	}
	
	function mrr_select_employer($field,$pre,$prompt)
	{
		$html="<select name='".$field."' id='".$field."'>";
		$selector="";			if($pre==0 || $pre=="")	$selector=" selected";
		$html.="<option value='0'".$selector.">".$prompt."</option>";	
		
		$sql="
			select option_values.id,
				option_values.fvalue 
			from option_values,
				option_cat
			where option_values.cat_id=option_cat.id
				and option_cat.cat_name='employer_list'
				and option_cat.deleted<=0
				and option_values.deleted<=0
			order by option_values.fvalue asc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$id=$row['id'];
			$emp=$row['fvalue'];
			
			$selector="";		if($pre==$id)			$selector=" selected";
			$html.="<option value='".$id."'".$selector.">".$emp."</option>";	
		}	
		
		$html.="</select>";		
		return $html;	
	}
	function mrr_get_last_employer_vendor_bridge($employer_id,$date_from,$date_to)
	{
		$bridge_id=0;
		
		$sql="
			select new_sicap_vendor_id
			from payroll_employer_vendor
			where employer_id='".sql_friendly($employer_id)."'
				and deleted<=0
				and linedate_started<='".date("Y-m-d",strtotime($date_from))."'				
				
			order by linedate_started desc
		";		//and linedate_started<='".date("Y-m-d",strtotime($date_to))."'
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$bridge_id=$row['new_sicap_vendor_id'];
		}	
		return $bridge_id;
	}
	
	function mrr_get_all_sicap_vendors($field,$pre,$prompt)
	{
		$res['num']=0;
		$res['arr'][0]=0;
		$res['comp'][0]="";
		$res['vend'][0]="";
		$res['select']="";
		
		
		$html="<select name='".$field."' id='".$field."'>";
		$selector="";			if($pre==0 || $pre=="")	$selector=" selected";
		$html.="<option value='0'".$selector.">".$prompt."</option>";	
		
		$cntr=0;
		$results=mrr_get_all_vendors();
		foreach($results as $key => $value )
		{
			$prt=trim($key);	
			if($prt=="VendorEntry")		
			{
				$res['arr'][$cntr]=0;	
          		$res['comp'][$cntr]="space";	
          		$res['vend'][$cntr]="holder";	
				
				foreach($value as $key2 => $value2 )
          		{
          			$prt2=trim($key2);			$tmp2=trim($value2);
          			if($prt2=="ID")			$res['arr'][$cntr]=$tmp2;	
          			if($prt2=="CompanyName")		$res['comp'][$cntr]=$tmp2;	
          			//if($prt2=="VendorName")		$res['vend'][$cntr]=$tmp2;	
          		}
          						
				$cntr++;
			}
		}
		
		$res['num']=$cntr;
		for($i=0; $i< $cntr; $i++)
		{
			$selector="";		if($pre==$res['arr'][$i])			$selector=" selected";
			$html.="<option value='".$res['arr'][$i]."'".$selector.">".$res['comp'][$i]."</option>";	// | ".$res['vend'][$i]."
		}		
		$html.="</select>";		
		$res['select']=$html;
		
		return $res;	
	}
	
	function mrr_get_sicap_vendor_name($vendor_id)
	{
		$vendor_name="";
		
		$results=mrr_get_a_vendor($vendor_id);
		foreach($results as $key => $value )
		{
			$prt=trim($key);	
			if($prt=="VendorEntry")		
			{
				foreach($value as $key2 => $value2 )
          		{
          			$prt2=trim($key2);			$tmp2=trim($value2);
          			//if($prt2=="ID")			$vendor_name=$tmp2;	
          			if($prt2=="CompanyName")		$vendor_name=$tmp2;	
          			//if($prt2=="VendorName")	$vendor_name=$tmp2;	
          		}
          						
				$cntr++;
			}
		}		
		return $vendor_name;	
	}
	
	
	function mrr_load_stop_fault_decoder($id=0)
	{
		$fault="";
		$sql="
			select *
			from option_values
			where id='".sql_friendly($id)."'
		";		
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$fault=trim($row['fvalue']);					//trim($row['fname']);	
		}				
		return $fault;
	}
	function mrr_select_load_stop_faults($field,$pre=0,$prompt="",$java="")
	{
		$html="<select name='".$field."' id='".$field."'".$java.">";
		$selector="";			if($pre<=0 || $pre=="")	$selector=" selected";
		$html.="<option value='0'".$selector.">".$prompt."</option>";	
			
		$cat_id=19;		//cat is 'load_grading_faults'
		
		$sql="
			select *
			from option_values
			where cat_id='".sql_friendly($cat_id)."'
				and deleted<=0				
			order by fvalue asc
		";		//fname asc,
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$namer=trim($row['fvalue']);					//trim($row['fname']);			
			
			$selector="";			if($pre==$row['id'])	$selector=" selected";
			$html.="<option value='".$row['id']."'".$selector.">".$namer."</option>";
		}		
		
		$html.="</select>";			
		return $html;	
	}
	
	
	
	function mrr_select_load_stop_grades($field,$pre=0,$prompt="",$java="",$simplify=0)
	{
		$html="<select name='".$field."' id='".$field."'".$java.">";
		$selector="";			if($pre<=0 || $pre=="")	$selector=" selected";
		$html.="<option value='0'".$selector.">".$prompt."</option>";	
				
		$cntr=50;
		if($pre > $cntr)		$pre=$cntr;	
		
		if($simplify==0)        $simplify=1;
		
		if($simplify==0)
		{		
     		//basic order
     		/*		
     		for($i=1; $i<= $cntr; $i++)
     		{
     			$grader=mrr_load_stop_grade_decoder($i);
     			if(trim($grader)!="")
     			{
     				$selector="";		if($pre==$i)			$selector=" selected";
     				$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     			}
     		}	
     		*/	
     		$i=8;
     		$grader=mrr_load_stop_grade_decoder($i);
     		if(trim($grader)!="")
     		{
     			$selector="";		if($pre==$i)			$selector=" selected";
     			$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     		}
     		$i=7;
     		$grader=mrr_load_stop_grade_decoder($i);
     		if(trim($grader)!="")
     		{
     			$selector="";		if($pre==$i)			$selector=" selected";
     			$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     		}
     		$i=6;
     		$grader=mrr_load_stop_grade_decoder($i);
     		if(trim($grader)!="")
     		{
     			$selector="";		if($pre==$i)			$selector=" selected";
     			$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     		}
     		$i=5;
     		$grader=mrr_load_stop_grade_decoder($i);
     		if(trim($grader)!="")
     		{
     			$selector="";		if($pre==$i)			$selector=" selected";
     			$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     		}
     		
     		$i=4;
     		$grader=mrr_load_stop_grade_decoder($i);
     		if(trim($grader)!="")
     		{
     			$selector="";		if($pre==$i)			$selector=" selected";
     			$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     		}
     		for($i=40; $i<= 45; $i++)
     		{
     			$grader=mrr_load_stop_grade_decoder($i);
     			if(trim($grader)!="")
     			{
     				$selector="";		if($pre==$i)			$selector=" selected";
     				//$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     			}
     		}
     				
     		$i=3;
     		$grader=mrr_load_stop_grade_decoder($i);
     		if(trim($grader)!="")
     		{
     			$selector="";		if($pre==$i)			$selector=" selected";
     			$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     		}
     		for($i=30; $i<= 35; $i++)
     		{
     			$grader=mrr_load_stop_grade_decoder($i);
     			if(trim($grader)!="")
     			{
     				$selector="";		if($pre==$i)			$selector=" selected";
     				//$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     			}
     		}
     		
     		
     		$i=2;
     		$grader=mrr_load_stop_grade_decoder($i);
     		if(trim($grader)!="")
     		{
     			$selector="";		if($pre==$i)			$selector=" selected";
     			$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     		}
     		for($i=20; $i<= 25; $i++)
     		{
     			$grader=mrr_load_stop_grade_decoder($i);
     			if(trim($grader)!="")
     			{
     				$selector="";		if($pre==$i)			$selector=" selected";
     				//$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     			}
     		}
     		
     		
     		$i=1;
     		$grader=mrr_load_stop_grade_decoder($i);
     		if(trim($grader)!="")
     		{
     			$selector="";		if($pre==$i)			$selector=" selected";
     			$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     		}
     		for($i=10; $i<= 15; $i++)
     		{
     			$grader=mrr_load_stop_grade_decoder($i);
     			if(trim($grader)!="")
     			{
     				$selector="";		if($pre==$i)			$selector=" selected";
     				//$html.="<option value='".$i."'".$selector.">".$grader."</option>";
     			}
     		}
		}
		else
		{
			$selector="";		if($pre==1 || $pre==2 || $pre==3 || $pre==4)			$selector=" selected";
    			$html.="<option value='4'".$selector.">Late</option>";
     			
     		$selector="";		if($pre==5 || $pre==6 || $pre==7 || $pre==8)			$selector=" selected";
    			$html.="<option value='5'".$selector.">On Time</option>";
		}
		
		$html.="</select>";	
		
		return $html;	
	}
	function mrr_load_stop_grade_decoder($id=0)
	{
		$cntr=50;
		if($id < 0)		$id=0;
		if($id > $cntr)	$id=$cntr;	
		
		$grade[0]="";
		for($i=0; $i<= $cntr; $i++)	$grade[$i]="";
		
		$grade[0]="Ungraded";
		$grade[1]="Epic Fail";		
		$grade[2]="Past Due";
		$grade[3]="Very Late";
		$grade[4]="Late";
		$grade[5]="On Time";		
		$grade[6]="Within Window";
		$grade[7]="Early";
		$grade[8]="Very Early";
		$grade[9]="Cancelled";
						
		$grade[10]="Epic Fail: Truck Breakdown";
		$grade[11]="Epic Fail: Trailer Breakdown";
		$grade[12]="Epic Fail: Driver Issue";
		$grade[13]="Epic Fail: Traffic";
		$grade[14]="Epic Fail: Pickup Delayed";
		$grade[15]="Epic Fail: Other (Specify)";
		
		$grade[20]="Past Due: Truck Breakdown";
		$grade[21]="Past Due: Trailer Breakdown";
		$grade[22]="Past Due: Driver Issue";
		$grade[23]="Past Due: Traffic";
		$grade[24]="Past Due: Pickup Delayed";
		$grade[25]="Past Due: Other (Specify)";
		
		$grade[30]="Very Late: Truck Breakdown";
		$grade[31]="Very Late: Trailer Breakdown";
		$grade[32]="Very Late: Driver Issue";
		$grade[33]="Very Late: Traffic";
		$grade[34]="Very Late: Pickup Delayed";
		$grade[35]="Very Late: Other (Specify)";
		
		$grade[40]="Late: Truck Breakdown";
		$grade[41]="Late: Trailer Breakdown";
		$grade[42]="Late: Driver Issue";
		$grade[43]="Late: Traffic";
		$grade[44]="Late: Pickup Delayed";
		$grade[45]="Late: Other (Specify)";
				
		return $grade[ $id ];
	}
	
	function mrr_self_grading_completed_stops($date_from="",$date_to="")
	{
		if(trim($date_from)=="")		$date_from=date("Y-m-d");
		if(trim($date_to)=="")		$date_to=date("Y-m-d");		
		
		$date_from=date("Y-m-d",strtotime($date_from));
		$date_to=date("Y-m-d",strtotime($date_to));
     		
     	$html="";	
     	$stops_found=0;
     	$stops_update=0;
     	
     	$html.="<table border='0' cellpadding='0' cellspacing='0' width='800'>
     		<tr>
     			<td valign='top'><b>ID</b></td>
     			<td valign='top'><b>Pickup ETA</b></td>
     			<td valign='top'><b>Completed</b></td>
     			<td valign='top'><b>Diff Hrs</b></td>
     			<td valign='top'><b>Score</b></td>				
     			<td valign='top'><b>Grade</b></td>
     			<td valign='top'><b>Grade Note</b></td>
     			<td valign='top'><b>New Score</b></td>
     			<td valign='top'><b>New Grade</b></td>	
     		</tr>
     	";
     	     	
     	$sql="
     		select id,
     			linedate_pickup_eta,
     			linedate_completed,
     			stop_grade_id,
     			stop_grade_note
     		from load_handler_stops
     		where deleted<=0
     			and stop_grade_id<=0
     			and linedate_pickup_eta >= '".$date_from." 00:00:00'
     			and linedate_pickup_eta <= '".$date_to." 23:59:59'
     			and linedate_completed >= linedate_added
     			and linedate_completed > '2000-01-01 00:00:00'
     		order by linedate_pickup_eta asc
     		"; 
     	$data = simple_query($sql); 
     	while($row = mysqli_fetch_array($data)) 
     	{	
     		$id=$row['id'];
     		$eta_hrs=strtotime($row['linedate_pickup_eta']);
     		$comp_hrs=strtotime($row['linedate_completed']);
     		$score=$row['stop_grade_id'];
     		$grader=mrr_load_stop_grade_decoder($row['stop_grade_id']);
     		
     		$eta_hrs=$eta_hrs/(60 * 60);
     		$comp_hrs=$comp_hrs/(60 * 60);
     		
     		$diff=$eta_hrs - $comp_hrs;
     		
     		$new_score=0;
     		$new_note="Auto Graded...";
     		if($diff==0)					{	$new_score=5;	$new_note.="on schedule";				}	//On Time
     		elseif($diff < 0 && $diff >=-1)	{	$new_score=4;	$new_note.="1 hr or so late";				}	//Late
     		elseif($diff < -1 && $diff >=-3)	{	$new_score=3;	$new_note.="1 to 3 hrs late";				}	//Very Late
     		elseif($diff < -3 && $diff >=-10)	{	$new_score=2;	$new_note.="3 to 10 hrs late";			}	//Past Due
     		elseif($diff < -10)				{	$new_score=1;	$new_note.="more than 10 hrs late";		}	//Epic Fail
     		elseif($diff > 0 && $diff <=2)	{	$new_score=7;	$new_note.="under two hours early";		}	//Early
     		elseif($diff > 2)				{	$new_score=8;	$new_note.="more than two hours early";		}	//Very Early		
     		     		
     		$new_grader=mrr_load_stop_grade_decoder($new_score);
     		
     		$html.="
     			<tr>
     				<td valign='top'>".$id."</td>
     				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
     				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_completed']))."</td>
     				<td valign='top'>".$diff."</td>			
     				<td valign='top'>".$score."</td>
     				<td valign='top'>".$grader."</td>
     				<td valign='top'>".$row['stop_grade_note']."</td>
     				<td valign='top'>".$new_score."</td>
     				<td valign='top'>".$new_grader."</td>
     			</tr>
     		";	
     		if($score==0 && $new_score>0)
     		{
     			$sql2="
     				update load_handler_stops set
     					stop_grade_id='".sql_friendly($new_score)."',
     					stop_grade_note='".sql_friendly($new_note)."'
     				where id='".sql_friendly($id)."'
     			"; 
     			//simple_query($sql2);		//turned off the auto_grading for James...June 2015...using the newer Admin Load Grading page...
     			$stops_update++;
     		}
     		
     		$stops_found++;		
     	}
     	$html.="</table>";
     	
     	$html.="<br><b>Stops found: ".$stops_found.".</b>";
     	$html.="<br><b>Stops updated: ".$stops_update.".</b>";
     	$html.="<br><br>";
     	return $html;
	} 
	
	
	function mrr_find_replacement_truck_name($truck_id,$date_from,$date_to)
	{
		$replaced_by="";
		
		$sql="
			select equipment_id
						
			from equipment_history
			where deleted <= 0
				and replacement_xref_id = '".sql_friendly($truck_id)."'
				and linedate_aquired <= '".date("Y-m-d", strtotime($date_from))." 00:00:00'
				and (
					linedate_returned <= 0
					or linedate_returned >= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
					)
			order by equipment_id desc
			limit 1
		";
		$data = simple_query($sql); 
     	if($row = mysqli_fetch_array($data)) 
     	{
     		if($row['equipment_id'] > 0)
     		{
     			$sql = "
					select name_truck					
					from trucks
					where id = '".sql_friendly($row['equipment_id'])."'
				";
				$data_truck_name = simple_query($sql);
				$row_truck_name = mysqli_fetch_array($data_truck_name);	
				$replaced_by=" ".trim($row_truck_name['name_truck']);
     		}     		
     	}
     	return $replaced_by;		
	}
	
	function mrr_add_user_change_log($user,$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes)
	{
		global $datasource;

		$sql = "
			insert into ".mrr_find_log_database_name()."user_change_log
				(id,
				linedate_added,
				active,
                    deleted,
				user_id,
				customer_id,
				driver_id,
                   	truck_id,
                   	trailer_id,
                   	load_id,
                   	dispatch_id,
                   	stop_id,
                   	notes)
			values
				(NULL,
				NOW(),
				'1',
				'0',
				'".sql_friendly($user)."',
				'".sql_friendly($cust)."',
				'".sql_friendly($driver)."',
				'".sql_friendly($truck)."',
				'".sql_friendly($trailer)."',
				'".sql_friendly($load)."',
				'".sql_friendly($disp)."',
				'".sql_friendly($stop)."',
				'".sql_friendly($notes)."')
		";
		simple_query($sql);
		$newid=mysqli_insert_id($datasource);
		return $newid;			
	}
	
	function mrr_get_user_change_log($where,$order,$limit,$cd=0,$loads_created=0)
	{
		$tab="";
		
		if($loads_created > 0)
        {   //report_show_loads_only
             $where.="
                and user_change_log.load_id > 0 
                and user_change_log.dispatch_id=0 
                and user_change_log.stop_id=0
             ";
        }
		
		$sql = "
			select user_change_log.*,
				users.name_first,
				users.name_last,
				load_handler.origin_city,
				load_handler.origin_state,
				load_handler.dest_city,
				load_handler.dest_state,
				load_handler.linedate_pickup_eta,
				load_handler.linedate_dropoff_eta,
				drivers.name_driver_first,
				drivers.name_driver_last,
				trailers.trailer_name,
				trucks.name_truck,
				(select c.name_company from customers c where c.id=load_handler.customer_id) as cust_name,
				(select c.id from customers c where c.id=load_handler.customer_id) as cust_id,
				customers.name_company
			from ".mrr_find_log_database_name()."user_change_log
				left join users on users.id=user_change_log.user_id
				left join customers on customers.id=user_change_log.customer_id
				left join drivers on drivers.id=user_change_log.driver_id
				left join trucks on trucks.id=user_change_log.truck_id
				left join trailers on trailers.id=user_change_log.trailer_id
				left join load_handler on load_handler.id=user_change_log.load_id
				left join trucks_log on trucks_log.id=user_change_log.dispatch_id
				left join load_handler_stops on load_handler_stops.id=user_change_log.stop_id
			where user_change_log.deleted<=0
				".$where."
			".$order."
			".$limit."
		";
		$data=simple_query($sql);
		
		$cntr=0;
		if($loads_created==0)
        {
             $tab.="<span class='mrr_link_like_on' onClick=\"$('#mrr_user_change_log_div').show();\"><b>Show User Change Log</b></span> | <span class='mrr_link_like_on' onClick=\"$('#mrr_user_change_log_div').hide();\"><b>Hide User Change Log</b></span>";     
        }
		$tab.="<div id='mrr_user_change_log_div'>";
		$tab.="<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
		if($cd==0)
		{			
			$tab.="
			<tr>					
				<td valign='top' colspan='10'><h1>User Change Log</h1></td>
			<tr>
			<tr>					
				<td valign='top'><b>Date</b></td>	
				<td valign='top'><b>User</b></td>
				<td valign='top'><b>Customer</b></td>
				";
			if($loads_created>0)
            {
                $tab.="
                    <td valign='top'><b>Origin</b></td>
                    <td valign='top'><b>Pickup</b></td>
                    <td valign='top'><b>Destination</b></td>
                    <td valign='top'><b>Dropoff</b></td>
                ";
            }
			$tab.="
				<td valign='top'><b>Driver</b></td>					
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>Trailer</b></td>
				<td valign='top' align='right'><b>Load</b></td>
				<td valign='top' align='right'><b>Dispatch</b></td>
				<td valign='top' align='right'><b>Stop</b></td>
				<td valign='top'>&nbsp;&nbsp;<b>Notes</b></td>
			<tr>
			";
		}
		elseif($cd==1)
		{
			$tab.="
			<tr>					
				<td valign='top' colspan='3'><h1>User Change Log</h1></td>
			<tr>
			<tr>					
				<td valign='top'><b>Date</b></td>	
				<td valign='top'><b>User</b></td>
				<td valign='top'><b>Notes</b></td>
			<tr>
			";
		}		
				
		while($row=mysqli_fetch_array($data))
		{
			if($cd==0)
			{
                 $is_valid=0;
                 if(substr_count($row['notes'],"Duplicate") > 0)        $is_valid=1;
                 if(substr_count($row['notes'],"Create") > 0)           $is_valid=1;
                 
			    if($loads_created==0 || ($loads_created > 0 && $is_valid > 0))
                 {
                     $tab.="
                    <tr class='".($cntr%2==0 ? "even" : "odd")."'>					
                        <td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>	
                        <td valign='top'><a href='admin_users.php?eid=".$row['user_id']."' title='log ".$row['id'].".'>".$row['name_first']." ".$row['name_last']."</span></td>
                        ";
                          if($loads_created>0)
                          {
                               $tab.="
                                    <td valign='top'><a href='admin_customers.php?eid=".$row['cust_id']."' target='_blank'>".$row['cust_name']."</a></td>
                                    <td valign='top'>".$row['origin_city'].", ".$row['origin_state']."</td>
                                    <td valign='top'><b>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</b></td>
                                    <td valign='top'>".$row['dest_city'].", ".$row['dest_state']."</td>
                                    <td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_dropoff_eta']))."</td>
                                ";
                          }
                          else
                          {
                               $tab.="<td valign='top'><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['name_company']."</a></td>";
                          }
                          
                    $tab.="
                        <td valign='top'><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['name_driver_first']." ".$row['name_driver_last']."</a></td>					
                        <td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a></td>
                        <td valign='top'><a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailer_name']."</a></td>
                        <td valign='top' align='right'><a href='manage_load.php?load_id=".$row['load_id']."' target='_blank'>".$row['load_id']."</a></td>
                        <td valign='top' align='right'><a href='add_entry_truck.php?load_id=".$row['load_id']."&id=".$row['dispatch_id']."' target='_blank'>".$row['dispatch_id']."</a></td>
                        <td valign='top' align='right'>".$row['stop_id']."</td>
                        <td valign='top'>&nbsp;&nbsp;".$row['notes']."</td>
                    <tr>
                    ";
                 }		    
			}
			elseif($cd==1)
			{
				$tab.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>					
					<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>	
					<td valign='top'><a href='admin_users.php?eid=".$row['user_id']."' title='log ".$row['id'].".'>".$row['name_first']." ".$row['name_last']."</span></td>
					<td valign='top'>".$row['notes']."</td>
				<tr>
				";
			}
			$cntr++;
		}
		$tab.="
			</table>
			</div>			
			";
		if($loads_created==0)
        {
            $tab.="
                <script type='text/javascript'>
                $().ready(function() {
                    $('#mrr_user_change_log_div').hide();
                });
                </script>
            ";
        }
		return $tab;
	}
	
	function mrr_get_log_user_validation($where,$order,$limit,$cd=0)
	{
		$tab="";
		
		$sql = "
			select log_user_validation.*,
				users.name_first,
				users.name_last
			from log_user_validation
				left join users on users.id=log_user_validation.user_id
			where log_user_validation.deleted<=0
				".$where."
			".$order."
			".$limit."
		";
		$data=simple_query($sql);
		
		$cntr=0;
		//$tab.="<span class='mrr_link_like_on' onClick=\"$('#mrr_user_change_log_div').show();\"><b>Show User Change Log</b></span> | <span class='mrr_link_like_on' onClick=\"$('#mrr_user_change_log_div').hide();\"><b>Hide User Change Log</b></span>";
		//$tab.="<div id='mrr_user_change_log_div'>";
		$tab.="<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
		$tab.="
			<tr>					
				<td valign='top' colspan='4'><h1>User Validation Log</h1></td>
			<tr>
			<tr>					
				<td valign='top'><b>Date</b></td>	
				<td valign='top'><b>User</b></td>
				<td valign='top'><b>Page</b></td>
				<td valign='top'><b>Clicked</b></td>
			<tr>
		";
			
		
		while($row=mysqli_fetch_array($data))
		{
			$tab.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>					
					<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>	
					<td valign='top'><a href='admin_users.php?eid=".$row['user_id']."' title='log ".$row['id'].".'>".$row['name_first']." ".$row['name_last']."</span></td>					
					<td valign='top'>".$row['page_url']."</td>
					<td valign='top'>".($row['clicked_yes'] > 0 ? "Yes" : "No")."</td>
				<tr>
			";
			
			$cntr++;
		}
		$tab.="</table>";
		/*
		$tab.="
			</div>
			<script type='text/javascript'>
			$().ready(function() {
				$('#mrr_user_change_log_div').hide();
			});
			</script>
			";
		*/
		return $tab;
	}
	
	function mrr_send_insurance_information($truck_id=0,$trailer_id=0,$driver_id=0,$user_id=0)
	{	//sends email to insurance addresses for new truck/trailer/driver and logs it (driver not yet needed).
		global $defaultsarray;
		
		$trucks_on = (int) $defaultsarray['insurance_report_email_trucks'];
		$trailers_on = (int) $defaultsarray['insurance_report_email_trailers'];
		$addr1 = trim($defaultsarray['insurance_report_email_address1']);
		$addr2 = trim($defaultsarray['insurance_report_email_address2']);
		
		$send_mail=0;	
		
		$From=trim($defaultsarray['company_email_address']);		
		$FromName=trim($defaultsarray['company_name']);
		
		$ToName="";
		$Subject="".trim($defaultsarray['company_name'])." Insurance Update";
		$Html="";
		$reporter="";
		
		if($trucks_on > 0 && $truck_id > 0)
		{
			$sql="
          		select trucks.*,
          			
          			(select linedate_aquired from equipment_history eh where eh.deleted <= 0 and eh.equipment_type_id = 1 and eh.equipment_id = trucks.id and eh.linedate_returned <= 0 limit 1) as replaces_aquired,
          			(select equipment_value from equipment_history eh where eh.deleted <= 0 and eh.equipment_type_id = 1 and eh.equipment_id = trucks.id and eh.linedate_returned <= 0 limit 1) as replaces_value,
          			(select linedate_returned from equipment_history eh where eh.deleted <= 0 and eh.equipment_type_id = 1 and eh.equipment_id = trucks.id order by eh.id desc limit 1) as replaces_returned,
          			
          			(select replacement_xref_id from equipment_history eh where eh.deleted <= 0 and eh.equipment_type_id = 1 and eh.equipment_id = trucks.id and eh.linedate_returned <= 0 limit 1) as replaces_truck_id,
					(select equipment_id from equipment_history eh where eh.deleted <= 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned <= 0 limit 1) as replacement_truck_id
          		from trucks
          		where trucks.id='".sql_friendly($truck_id)."'
          	";	
          	$data=simple_query($sql);
          	if($row=mysqli_fetch_array($data))
          	{
          		$namer=$row['name_truck'];
          		
          		$pflag="Leased";
          		if($row['company_owned'] > 0)		$pflag="Owned by ".trim($defaultsarray['company_name'])."";
          		if($row['rental'] > 0)			$pflag="Rented";	
          		
          		$provider_line="Provider: ".$row['leased_from'].". Truck is ".$pflag.".<br>";
          		$replacer_line="";
          		
          		if($row['deleted'])	   $Html.="<b>Truck ".$namer." has been removed from our system.</b><br><br>";
          		
          		if($row['replacement'] > 0)
          		{
          			if($row['replaces_truck_id'] > 0)
          			{
          				$sql_r="select name_truck from trucks where id='".sql_friendly($row['replaces_truck_id'])."'";
          				$data_r=simple_query($sql_r);
          				if($row_r=mysqli_fetch_array($data_r))	
          				{
          					$replacer_line="Note: Substitutes for Truck ".$row_r['name_truck'].".<br>";		
          				}
          			}         			
          		}
          		$aquired=date("m/d/Y",strtotime($row['replaces_aquired']));
          		if($aquired=="12/31/1969")	$aquired="N/A";
          		$returned=date("m/d/Y",strtotime($row['replaces_returned']));
          		if($returned=="12/31/1969")	$returned="N/A";
          		  		
          		$Html.="
          			Truck ".$namer." Information has been updated.<br>
          			".$provider_line." 
          			".$replacer_line."<br>         			
          			Year: ".$row['truck_year']."<br>
          			Make: ".$row['truck_make']."<br>
          			Model: ".$row['truck_model']."<br>
          			VIN: ".$row['vin']."<br>
          			Plate: ".$row['license_plate']."<br>
          			
          			Aquired: ".$aquired."<br>
          			Returned: ".$returned."<br>
          			
          			Monthly Cost: $".number_format($row['monthly_cost'],2)."<br>
          			Equipment Value: $".number_format($row['replaces_value'],2)."<br>
          		"; 
          	}			
		}
		
		if($trailers_on > 0 && $trailer_id > 0)
		{
			$sql="
          		select trailers.*,          			
          			(select linedate_aquired from equipment_history eh where eh.deleted <= 0 and eh.equipment_type_id = 2 and eh.equipment_id = trailers.id and eh.linedate_returned <= 0 limit 1) as replaces_aquired,
          			(select equipment_value from equipment_history eh where eh.deleted <= 0 and eh.equipment_type_id = 2 and eh.equipment_id = trailers.id and eh.linedate_returned <= 0 limit 1) as replaces_value,
          			(select linedate_returned from equipment_history eh where eh.deleted <= 0 and eh.equipment_type_id = 2 and eh.equipment_id = trailers.id order by eh.id desc limit 1) as replaces_returned
          		from trailers
          		where trailers.id='".sql_friendly($trailer_id)."'
          	";	
          	$data=simple_query($sql);
          	if($row=mysqli_fetch_array($data))
          	{
          		$namer=$row['trailer_name'];
          		
          		$pflag="Leased";
          		if($row['company_owned'] > 0)		$pflag="Owned";
          		if($row['rental_flag'] > 0)		$pflag="Rented";	
          		
          		$provider_line="Owner: ".$row['trailer_owner'].". Trailer is ".$pflag.".<br>";
          		
          		if($row['deleted'])	   $Html.="<b>Trailer ".$namer." has been removed from our system.</b><br><br>";
          		
          		$aquired=date("m/d/Y",strtotime($row['replaces_aquired']));
          		if($aquired=="12/31/1969")	$aquired="N/A";
          		$returned=date("m/d/Y",strtotime($row['replaces_returned']));
          		if($returned=="12/31/1969")	$returned="N/A";
          		  		
          		$Html.="
          			Trailer ".$namer." Information has been updated.<br>
          			".$provider_line." <br>     			
          			Year: ".$row['trailer_year']."<br>
          			Make: ".$row['trailer_make']."<br>
          			Model: ".$row['trailer_model']."<br>
          			VIN: ".$row['vin']."<br>
          			Plate: ".$row['license_plate']."<br>
          			
          			Aquired: ".$aquired."<br>
          			Returned: ".$returned."<br>
          			
          			Monthly Cost: $".number_format($row['monthly_cost_actual'],2)."<br>
          			Equipment Value: $".number_format($row['replaces_value'],2)."<br>
          		"; 
          	} 
		}		
		
		if(trim($Html)!="")		$send_mail=1;
		
		if($send_mail==1 && trim($addr1)!="")	
		{	//primary address
			$reporter.="Running first email address '".$addr1."'.";
			
			$ToName="Insurance Co.";
			
			$did_send=mrr_trucking_sendMail_PN($addr1,$ToName,$From,$FromName,$Subject,$Html);	
			mrr_make_insurance_email_log($user_id,$driver_id,$truck_id,$trailer_id,$addr1,"<b>To: ".$addr1." (".$ToName.") ".($did_send > 0 ? 'Sent!' : 'Failed')."</b><br><b>From: ".$From." (".$FromName.")</b><br><br><b>".$Subject."</b><br><br>".$Html."<br>");
		}
		if($send_mail==1 && trim($addr2)!="")	
		{	//secondary address
			$reporter.="Running second email address '".$addr2."'.";
			
			$ToName="Accounting";
			
			$did_send=mrr_trucking_sendMail_PN($addr2,$ToName,$From,$FromName,$Subject,$Html);	
			mrr_make_insurance_email_log($user_id,$driver_id,$truck_id,$trailer_id,$addr2,"<b>To: ".$addr2." (".$ToName.") ".($did_send > 0 ? 'Sent!' : 'Failed')."</b><br><b>From: ".$From." (".$FromName.")</b><br><br><b>".$Subject."</b><br><br>".$Html."<br>");
		}
		return $reporter;	
	}
	
	function mrr_make_insurance_email_log($user_id,$driver_id,$truck_id,$trailer_id,$addr,$msg)
	{
		$sql="
          	insert into insurance_email_log
          		(id,
				linedate_added,
				active,
                 	deleted,
				user_id,
				driver_id,
                 	truck_id,
                 	trailer_id,
                 	email_addr,
                 	email_msg)
          	values
          		(NULL,
          		NOW(),
          		1,
          		0,
          		'".sql_friendly($user_id)."',
          		'".sql_friendly($driver_id)."',
          		'".sql_friendly($truck_id)."',
          		'".sql_friendly($trailer_id)."',
          		'".sql_friendly($addr)."',
          		'".sql_friendly($msg)."') 		
          ";	
          simple_query($sql);				
	}
	
	function mrr_get_insurance_email_log($user_id=0,$driver_id=0,$truck_id=0,$trailer_id=0,$addr="")
	{
		$mrr_adder="";
		
		if($user_id>0)		$mrr_adder.=" and insurance_email_log.user_id='".sql_friendly($user_id)."'";
		if($driver_id>0)	$mrr_adder.=" and insurance_email_log.driver_id='".sql_friendly($driver_id)."'";
		if($truck_id>0)	$mrr_adder.=" and insurance_email_log.truck_id='".sql_friendly($truck_id)."'";
		if($trailer_id>0)	$mrr_adder.=" and insurance_email_log.trailer_id='".sql_friendly($trailer_id)."'";
		if(trim($addr)!="")	$mrr_adder.=" and insurance_email_log.email_addr='".sql_friendly(trim($addr))."'";
				
		$tab="<div class='insurance_email_log'>			
			<h1>Last Insurance Update Emails Sent</h1>
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			<tr>
				<td valign='top' nowrap> <b>Date</b></td>
				<td valign='top' nowrap> <b>To</b></td>
				<td valign='top' nowrap> <b>By</b></td>
			</tr>
		";
		$cntr=0;
		
		$sql="
			select insurance_email_log.*,
				users.username
			from insurance_email_log
				left join users on users.id=insurance_email_log.user_id
			where insurance_email_log.deleted<=0
				".$mrr_adder."
			order by insurance_email_log.linedate_added desc
			limit 20
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$tab.="
				<tr class='".( $cntr %2 == 0 ? "even" : "odd" )."'>
					<td valign='top'> ".date("m/d/Y H:i", strtotime($row['linedate_added']))."</td>
					<td valign='top'> ".$row['email_addr']."</td>
					<td valign='top'> ".$row['username']."</td>
				</tr>
			";
			$cntr++;
		}
		$tab.="</table></div>";
		return $tab;
	}
	
	
	//added July 2013
	function mrr_find_current_dispatcher_tasks($user_id)
	{
		$cur_user="";
		$sqlu = "
			select *			
			from users
			where id = '".sql_friendly($user_id)."'
		";
		$datau = simple_query($sqlu);	
		if($rowu=mysqli_fetch_array($datau))
		{
			$cur_user=trim($rowu['name_first']." ".$rowu['name_last']);
		}	
		
		$tab="";
		
		if($user_id>0)
		{
			$tab.="
				<table width='300' cellpadding='0' cellspacing='0' border='0'>
				<tr>
					<td valign='top' colspan='3'><span class='mrr_task_list_title'>Task List for ".$cur_user."</span></td>
				</tr>
				<tr>
     				<td valign='top' colspan='3'><hr><br></td>
     			</tr>
			";
			
			$sql = "
          		select dispatcher_tasks.*,
          			(select CONCAT(name_first,' ',name_last) from users where users.id=dispatcher_tasks.created_by_id) as creator	
          		from dispatcher_tasks
          		where dispatcher_tasks.deleted <= 0
          			and dispatcher_tasks.active > 0
          			and (dispatcher_tasks.assigned_to_id ='".sql_friendly($user_id)."' or dispatcher_tasks.assigned_to_id ='0')
          		order by dispatcher_tasks.active desc,dispatcher_tasks.linedate_start asc,dispatcher_tasks.linedate_complete asc,dispatcher_tasks.id asc
          	";
          	$data = simple_query($sql);	
          	while($row=mysqli_fetch_array($data))
          	{
               	$start1=date("m/d/Y",strtotime($row['linedate_start']));
				$start2=date("H:i",strtotime($row['linedate_start']));
				$end1=date("m/d/Y",strtotime($row['linedate_complete']));
				$end2=date("H:i",strtotime($row['linedate_complete']));
				
				if(substr_count($start1,"12/31/1969") > 0)	$start1="";	
				if(substr_count($start2,"12/31/1969") > 0)	$start2="";
				if(substr_count($end1,"12/31/1969") > 0)	$end1="";	
				if(substr_count($end2,"12/31/1969") > 0)	$end2="";
				
				$next_run=mrr_find_next_task_run($start1,$start2,$end1,$end2,$row['freq_days']);
				$task_work_list=mrr_get_dispatcher_task_work_list($row['id'],0);
				               	
               	$tab.="     				
     				<tr>
     					<td valign='top' colspan='3'>
     						<span class='mrr_task_list_edit' onClick='mrr_update_task_completion(".$row['id'].")'><b>Work</b></span> 
     						<span class='mrr_task_list_task' title='".$row['id']."'>Task: <b>".$row['task']."</b></span>      						
     					</td>
     				</tr>
     				<tr>
     					<td valign='top'>Created by</td>
     					<td valign='top'>".$row['creator']."</td>
     					<td valign='top'>".($row['freq_days'] > 0 ? "Every ".$row['freq_days']." Days" : "" )."</td>
     				</tr>
     				<tr>
     					<td valign='top' width='90'>Created</td>
     					<td valign='top'>".date("m/d/Y",strtotime($row['linedate_added']))."</td>
     					<td valign='top' width='90'>".date("H:i",strtotime($row['linedate_added']))."</td>
     				</tr>
     				<tr>
     					<td valign='top'>Updated</td>
     					<td valign='top'>".date("m/d/Y",strtotime($row['linedate_updated']))."</td>
     					<td valign='top'>".date("H:i",strtotime($row['linedate_updated']))."</td>
     				</tr>
     				<tr>
     					<td valign='top'><span class='mrr_task_list_task_run'>Start by</span></td>
     					<td valign='top'><span class='mrr_task_list_task_run'>".$start1."</span></td>
     					<td valign='top'><span class='mrr_task_list_task_run'>".$start2."</span></td>
     				</tr>
     				<tr>
     					<td valign='top'><span class='mrr_task_list_task_run'>Complete by</span></td>
     					<td valign='top'><span class='mrr_task_list_task_run'>".$end1."</span></td>
     					<td valign='top'><span class='mrr_task_list_task_run'>".$end2."</span></td>
     				</tr>     				
     				".$next_run."     				
     				<tr>
     					<td valign='top' colspan='3'>".$task_work_list."</td>
     				</tr>
     				<tr>
     					<td valign='top' colspan='3'><hr><br></td>
     				</tr>
     			";
          	}          	
          	$tab.="</table>";          		
		}		
		return $tab;	
	}
	function mrr_find_next_task_run($start_date,$start_time,$end_date,$end_time,$days_freq=0)
	{
		$now=date("m/d/Y");
		$rval="";
		$days_freq=(int)$days_freq;
		if($days_freq>0)
		{     		
     		$starter=date("m/d/Y", strtotime($start_date));
     		$ender=date("m/d/Y", strtotime($end_date));
     		$show_last=0;
     		while($starter < $now)
     		{
     			$starter=date("m/d/Y", strtotime("+".$days_freq." days",strtotime($starter)));
     			$ender=date("m/d/Y", strtotime("+".$days_freq." days",strtotime($ender)));	
     			$show_last++;
     		}
     		
     		$next_starter=date("m/d/Y", strtotime("+".$days_freq." days",strtotime($starter)));
     		$next_ender=date("m/d/Y", strtotime("+".$days_freq." days",strtotime($ender)));	
     		
     		if($show_last>0)
     		{
     			$rval.="
     				<tr>
          				<td valign='top'><span class='mrr_task_list_last_run'>Last Start</span></td>
          				<td valign='top'><span class='mrr_task_list_last_run'>".$starter."</span></td>
          				<td valign='top'><span class='mrr_task_list_last_run'>".$start_time."</span></td>
          			</tr>
          			<tr>
          				<td valign='top'><span class='mrr_task_list_last_run'>Last Complete</span></td>
          				<td valign='top'><span class='mrr_task_list_last_run'>".$ender."</span></td>
          				<td valign='top'><span class='mrr_task_list_last_run'>".$end_time."</span></td>
          			</tr>
     			";
     		}
     		$rval.="
     			<tr>
          			<td valign='top'><span class='mrr_task_list_next_run'>Next Start</span></td>
          			<td valign='top'><span class='mrr_task_list_next_run'>".$next_starter."</span></td>
          			<td valign='top'><span class='mrr_task_list_next_run'>".$start_time."</span></td>
          		</tr>
          		<tr>
          			<td valign='top'><span class='mrr_task_list_next_run'>Next Complete</span></td>
          			<td valign='top'><span class='mrr_task_list_next_run'>".$next_ender."</span></td>
          			<td valign='top'><span class='mrr_task_list_next_run'>".$end_time."</span></td>
          		</tr>
     		";
		}
		return $rval;	
	}
	function mrr_get_dispatcher_task_work_list($task_id=0,$cd=0)
	{
		$tab="";
		$cntr=0;
		if($task_id>0)
		{
			$sql = "
               		select dispatcher_tasks_work.*,
               			(select CONCAT(name_first,' ',name_last) from users where users.id=dispatcher_tasks_work.user_id) as worker	
               		from dispatcher_tasks_work
               		where dispatcher_tasks_work.deleted <= 0
               			and dispatcher_tasks_work.task_id ='".sql_friendly($task_id)."'
               		order by dispatcher_tasks_work.linedate asc
               	";
               $data = simple_query($sql);	
			
			if($cd==0)
			{	//display only version for all users...no options
     			$tab.="
     				<table width='300' cellpadding='0' cellspacing='0' border='0'>
     				<tr>
     					<td valign='top' colspan='3'><b>Task Work List</b></td>
     				</tr>
     			";
     			
     			
     			
               	while($row=mysqli_fetch_array($data))
               	{
               		$classer="mrr_task_list_task_work_active";
               		if($row['active']==0)	$classer="mrr_task_list_task_work_inactive";
               		
               		$tab.="
               			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
     						<td valign='top'><span class='".$classer."'>".date("m/d/Y H:i",strtotime($row['linedate']))."</span></td>
     						<td valign='top'><span class='".$classer."'>".$row['worker']."</span></td>
     						<td valign='top'><span class='".$classer."'>".mrr_dispatcher_task_work_codes($row['work_code'])."</span></td>
     					</tr>
     					<tr class='".($cntr%2==0 ? "even" : "odd")."'>						
     						<td valign='top' colspan='3'><span class='".$classer."'>".$row['work']."</span></td>
     					</tr>
     					<tr class='".($cntr%2==0 ? "even" : "odd")."'>						
     						<td valign='top' colspan='3'>&nbsp;</td>
     					</tr>
     				";
     				$cntr++;	
               	}
               	$tab.="</table>";  
          	}
          	else
          	{	//admin version with privledges
          		$tab.="
          			<center>
     				<table width='90%' cellpadding='0' cellspacing='0' border='0'>
     				<tr>
     					<td valign='top' colspan='7'><b>Task Work List</b></td>
     				</tr>
     				<tr>
     					<td valign='top'><b>Added</b></td>
     					<td valign='top'><b>Date</b></td>
     					<td valign='top'><b>User</b></td>
     					<td valign='top'><b>Completion Code</b></td>
     					<td valign='top'><b>Task Work Notes</b></td>
     					<td valign='top'><b>Active</b></td>
     					<td valign='top'><b>&nbsp;</b></td>
     				</tr>
     			";
     			
               	while($row=mysqli_fetch_array($data))
               	{
               		$tab.="
               			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
     						<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
     						<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate']))."</td>
     						<td valign='top'>".$row['worker']."</td>
     						<td valign='top'>".mrr_dispatcher_task_work_codes($row['work_code'])."</td>
     						<td valign='top'>".$row['work']."</td>
     						<td valign='top'>".
     							($row['active'] > 0 ? "<span class='mrr_task_list_edit' onClick='mrr_dispatch_task_work_status(".$row['id'].",0);'>Yes</span>" : "<span class='mrr_task_list_edit' onClick='mrr_dispatch_task_work_status(".$row['id'].",1);'>No</span>")
     						."</td>
     						<td valign='top'><a href='javascript:confirm_work_delete(".$row['id'].");'><img src='images/delete_sm.gif' border='0'></a></td>
     					</tr>
     					<tr class='".($cntr%2==0 ? "even" : "odd")."'>						
     						<td valign='top' colspan='7'>&nbsp;</td>
     					</tr>
     				";	
     				$cntr++;
               	}
               	$tab.="
               		</table>
               		</center>
               	";  
          	}
          }
          if($cntr==0)	$tab="";
          return $tab;	
	}
	function mrr_dispatcher_task_work_codes($id=0)
	{
		$coder="";
		if($id==1)	$coder="Request Denied";
		if($id==2)	$coder="Hold/Delay";
		if($id==3)	$coder="In Progress";
		if($id==4)	$coder="Almost Done";
		if($id==5)	$coder="Completed";	
		return $coder;
	}
	function mrr_dispatcher_task_work_code_select_box($field,$pre=0)
	{
		$selbox="<select name='".$field."' id='".$field."'>";
		
		$sel="";		if($pre==0 || $pre=="")	$sel=" selected";	
		$selbox.="<option value='0'".$sel.">".mrr_dispatcher_task_work_codes(0)."</option>";	
		
		$sel="";		if($pre==1)	$sel=" selected";	
		$selbox.="<option value='1'".$sel.">".mrr_dispatcher_task_work_codes(1)."</option>";	
		$sel="";		if($pre==2)	$sel=" selected";	
		$selbox.="<option value='2'".$sel.">".mrr_dispatcher_task_work_codes(2)."</option>";	
		$sel="";		if($pre==3)	$sel=" selected";	
		$selbox.="<option value='3'".$sel.">".mrr_dispatcher_task_work_codes(3)."</option>";	
		$sel="";		if($pre==4)	$sel=" selected";	
		$selbox.="<option value='4'".$sel.">".mrr_dispatcher_task_work_codes(4)."</option>";	
		$sel="";		if($pre==5)	$sel=" selected";	
		$selbox.="<option value='5'".$sel.">".mrr_dispatcher_task_work_codes(5)."</option>";	
	
		$selbox.="</select>";	
		return $selbox;
	}
	
	function mrr_super_repair_pc_miler_app()
	{
		$report="";
		
		$destx="License Key";
		//$dest="C:\\web\\trucking.conardlogistics.com\\mrr_test\\x32api.dll";
		//$dest2="C:\\web\\trucking.conardlogistics.com\\mrr_test\\x32api.dll_bk_".date("YmdHis")."";
		$dest="C:\\Program Files (x86)\\ALK Technologies\\PMW230\\App\\x32api.dll";				//Program Files (x86)\\
		$dest2="C:\\Program Files (x86)\\ALK Technologies\\PMW230\\App\\x32api.dll_bk_".date("YmdHis")."";	//Program Files (x86)\\
		$source="C:\\web\\trucking.conardlogistics.com\\mrr\\x32api.dll";	
		$source="C:\\web\\trucking.conardlogistics.com\\mrr_test\\x32api.dll";	
		
		if(!file_exists($dest)) 
		{	//not found, so make it.
			
    			//$report.="Repair needed. the file '<b>".$dest."</b>' does not exist.<br>";
    			$report.="Repair needed. the '<b>".$destx."</b>' does not exist.<br>";
    			if (!copy($source, $dest)) 
    			{
    				//$report.="Failed to copy '<b>".$dest."</b>'<br>";
    				$report.="Failed to copy '<b>".$destx."</b>'<br>";
			}
			else
			{
				$report.="Repair complete. File copied.<br>";	
			}
		}
		else
		{	//found so it is corrupted...?  Rename this one and copy new one.
			
			//$report.="The file '<b>".$dest."</b>' exists.  Renaming and replacing file.";
			$report.="The '<b>".$destx."</b>' exists.  Renaming and replacing it...<br>";
			if(!rename($dest, $dest2))
			{
				//$report.="Failed to rename '<b>".$dest."</b>' to '<b>".$dest2."</b>'.<br>";
				$report.="Failed to replace '<b>".$destx."</b>'.<br>";
				
				unlink($dest);
				if (!copy($source, $dest)) 
    				{
    					//$report.="Failed to copy '<b>".$dest."</b>'<br>"; 
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
    					//$report.="Failed to copy '<b>".$dest."</b>'<br>"; 
    					$report.="Failed to copy '<b>".$destx."</b>'<br>";
				}
				else
				{
					$report.="Repair complete. File restored.<br>";	
				}
			}				
		}
		$report.="<br><br><b><i>If this does not solve it, please ask your administrator to remote into the web server and run the <u>fixlic.bat - Shortcut</u> on the Desktop.</i></b>";		
		return $report;
	}
	function mrr_super_repair_scan_process()
	{
		//this function has been moved to the page itself, so this simply spits back the message...admin_defaults.php	
		
		$report="Scanner Processing has been Executed.";
		
		return $report;	
	}
	
	function mrr_ensure_truck_name_unique($truck_id,$truck_name)
	{
		$invalid_id=0;
		
		$sql = "
			select id,
				name_truck		
			from trucks
			where deleted<=0
				and name_truck='".sql_friendly($truck_name)."'
				and name_truck!='New Truck'
				and id>0
				and (id<'".sql_friendly($truck_id)."' or id>'".sql_friendly($truck_id)."')
			order by id desc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$invalid_id=$row['id'];
		}		
		return $invalid_id;
	}
	function mrr_ensure_trailer_name_unique($trailer_id,$trailer_name)
	{
		$invalid_id=0;
		
		$sql = "
			select id,
				trailer_name		
			from trailers
			where deleted<=0
				and trailer_name='".sql_friendly($trailer_name)."'
				and trailer_name!='New Trailer'
				and id!='".sql_friendly($trailer_id)."'
			order by id desc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$invalid_id=$row['id'];
		}		
		return $invalid_id;
	}
	
	function mrr_alert_call_priority_codes($id=0)
	{
		$coder="Never Call";
		if($id==1)	$coder="1st Priority";
		if($id==2)	$coder="2nd Priority";
		if($id==3)	$coder="3rd Priority";
		if($id==4)	$coder="4th Priority";
		if($id==5)	$coder="5th Priority";
		if($id==6)	$coder="6th Priority";
		if($id==7)	$coder="7th Priority";
		if($id==8)	$coder="8th Priority";
		if($id==9)	$coder="9th Priority";
		if($id==10)	$coder="10th Priority";
		return $coder;
	}
	function mrr_alert_call_priority_select_box($field,$pre=0)
	{
		$selbox="<select name='".$field."' id='".$field."'>";
		
		$sel="";		if($pre==0 || $pre=="")	$sel=" selected";	
		$selbox.="<option value='0'".$sel.">".mrr_alert_call_priority_codes(0)."</option>";	
		
		$sel="";		if($pre==1)	$sel=" selected";	
		$selbox.="<option value='1'".$sel.">".mrr_alert_call_priority_codes(1)."</option>";	
		$sel="";		if($pre==2)	$sel=" selected";	
		$selbox.="<option value='2'".$sel.">".mrr_alert_call_priority_codes(2)."</option>";	
		$sel="";		if($pre==3)	$sel=" selected";	
		$selbox.="<option value='3'".$sel.">".mrr_alert_call_priority_codes(3)."</option>";	
		$sel="";		if($pre==4)	$sel=" selected";	
		$selbox.="<option value='4'".$sel.">".mrr_alert_call_priority_codes(4)."</option>";	
		$sel="";		if($pre==5)	$sel=" selected";	
		$selbox.="<option value='5'".$sel.">".mrr_alert_call_priority_codes(5)."</option>";	
		$sel="";		if($pre==6)	$sel=" selected";	
		$selbox.="<option value='6'".$sel.">".mrr_alert_call_priority_codes(6)."</option>";	
		$sel="";		if($pre==7)	$sel=" selected";	
		$selbox.="<option value='7'".$sel.">".mrr_alert_call_priority_codes(7)."</option>";	
		$sel="";		if($pre==8)	$sel=" selected";	
		$selbox.="<option value='8'".$sel.">".mrr_alert_call_priority_codes(8)."</option>";	
		$sel="";		if($pre==9)	$sel=" selected";	
		$selbox.="<option value='9'".$sel.">".mrr_alert_call_priority_codes(9)."</option>";	
		$sel="";		if($pre==10)	$sel=" selected";	
		$selbox.="<option value='10'".$sel.">".mrr_alert_call_priority_codes(10)."</option>";	
	
		$selbox.="</select>";	
		return $selbox;
	}
	
	function mrr_get_terminal_hub_address()
	{
		global $defaultsarray;
		
		$res['phone']=trim($defaultsarray['terminal_hub_phone']);	
		$res['name']=trim($defaultsarray['terminal_hub_name']);	
		$res['address']=trim($defaultsarray['terminal_hub_address']);	
		$res['city']=trim($defaultsarray['company_city']);	
		$res['state']=trim($defaultsarray['company_state']);	
		$res['zip']=trim($defaultsarray['company_zip']);	
		return $res;
	}
	
	function mrr_get_coa_chart_name_by_id($id)
	{
		global $defaultsarray;
		$coa_db=trim($defaultsarray['accounting_database_name']);	
		$name="";
		
		if($coa_db=="" || $id==0)		return $name;
		
		// use a union, the first part will scan for all matches starting with the letter, so it shows up in the search list first
		// the second part of the query will search for any part of the search term anywhere in the chart name, which will be shown below
		// the first set, as the first letter search should take priority
		$sql = "
			select chart_name,chart_number
			
			from ".$coa_db.".chart
			where id ='".sql_friendly($id)."'
		";
		$data = simple_query($sql);	
		if($row = mysqli_fetch_array($data)) 
		{
			$name=$row['chart_name'];		//chart_number
		}
		return $name;
	}
	function mrr_get_coa_chart_id_by_name($name)
	{
		global $defaultsarray;
		$coa_db=trim($defaultsarray['accounting_database_name']);	
		$id=0;
		
		if($coa_db=="" || trim($name)=="")		return $id;
		
		// use a union, the first part will scan for all matches starting with the letter, so it shows up in the search list first
		// the second part of the query will search for any part of the search term anywhere in the chart name, which will be shown below
		// the first set, as the first letter search should take priority
		$sql = "
			select id
			
			from ".$coa_db.".chart
			where chart_name ='".sql_friendly($name)."'
				and deleted <= 0
			order by id asc
		";
		$data = simple_query($sql);	
		if($row = mysqli_fetch_array($data)) 
		{
			$id=$row['id'];
		}
		else
		{
			$sql = "
			select id
			
			from ".$coa_db.".chart
			where chart_name like '".sql_friendly(trim($name))."'
				and deleted <= 0
			order by id asc
			";
			$data = simple_query($sql);	
			if($row = mysqli_fetch_array($data)) 
			{
				$id=$row['id'];
			}	
		}
		
		return $id;
	}
	
	function mrr_get_user_access_level($user_id)
	{
		$access=0;
		$sql = "
			select access 
			from users 
			where id='".sql_friendly($user_id)."'
				and deleted<=0
				and active>0
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$access=(int) $row['access'];
		}	
		return $access;
	}
	function mrr_get_user_menu_access_level($base_url,$user_id=0)
	{	
		$access_valid=0;
		$cntr=0;
		$sql = "
			select access_level
			from user_menu_access 
			where admin_url='".sql_friendly(trim($base_url))."'
				and deleted<=0
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			if($user_id>0)
			{	//use as an on or off check if this is validating a user can see a page...
				$user_level=mrr_get_user_access_level($user_id);
				if($user_level >= $row['access_level'])		$access_valid=1;
			}
			else
			{	//get actual access level of the page...(no reference to user access level, just the page.
				$access_valid=$row['access_level'];
			}
			$cntr++;		//page has been located...access should be evaluated.
		}	
		
		if($cntr==0 && $user_id>0)		$access_valid=1;		//allow all pages that are not even in the list as generally accessable.
		
		return $access_valid;
	}
	function mrr_get_user_menu_group_level($menu)
	{
		$access=0;
		$sql = "
			select access_level
			from user_menu_group 
			where menu_name='".sql_friendly(trim($menu))."'
				and deleted<=0
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$access=(int) $row['access_level'];
		}	
		return $access;
	}
	
	function mrr_get_master_load_stops_selector($field,$pre=0,$customer_id=0)
	{
		$selbox="<select name='".$field."' id='".$field."'>";
		
		$sel="";		if($pre==0 || $pre=="")	$sel=" selected";	
		$selbox.="<option value='0'".$sel.">Select Master Load Label</option>";	
		
		$mrr_adder="";
		if($customer_id > 0)	$mrr_adder=" and customer_id='".sql_friendly($customer_id)."'";
		
		$sql = "
			select id,master_load_label
			
			from load_handler
			where deleted<=0
				and master_load>0
				".$mrr_adder."
			order by id desc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			if(trim($row['master_load_label'])!="")
			{
				$sel="";		if($pre==$row['id'])	$sel=" selected";	
				$selbox.="<option value='".$row['id']."'".$sel.">".trim($row['master_load_label'])."</option>";		
			}
		}
		
		$selbox.="</select>";	
		return $selbox;
	}
	
	function mrr_display_trailers_dropped_multi_times()
	{
		$tab="";
		$tab2="";		
		
		$tab.="
			<table cellspacing='0' cellpadding='0' border='0'>
			<tr>
				<td valign='top'>Trailer</td>
				<td valign='top'>Dropped</td>
				<td valign='top'>City</td>
				<td valign='top'>State</td>
				<td valign='top'>Zip</td>
				<td valign='top'>Notes</td>
			</td>
		";
				
		$cntr=0;
		$cntr2=0;
		
		$arr[0]=0;
		$lab[0]="";
		$drops[0]="";
		$fnd[0]=0;
		$num=0;
		
		$sql = "
			select trailers_dropped.*,
				trailers.trailer_name
			
			from trailers_dropped
				left join trailers on trailers.id=trailers_dropped.trailer_id
			where trailers_dropped.deleted<=0
				and trailers_dropped.drop_completed<=0
				and trailers_dropped.trailer_id>0
			order by trailers.trailer_name asc, trailers_dropped.id desc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{				
			$tab.="
				<tr class='".($cntr%2==0 ? "even" : "odd")."'>
					<td valign='top'><a href='trailer_drop.php?id=".$row['id']."' target='_blank'>".$row['trailer_name']."</a></td>
					<td valign='top'>".date("m/d/Y", strtotime($row['linedate']))."</td>
					<td valign='top'>".$row['location_city']."</td>
					<td valign='top'>".$row['location_state']."</td>
					<td valign='top'>".$row['location_zip']."</td>
					<td valign='top'>".$row['notes']."</td>
				</tr>
			";	
			$cntr++;
			
			$found=0;
			for($x=0;$x < $num; $x++)
			{
				if($arr[$x]== $row['trailer_id'])	
				{
					$found=1;
					$fnd[$x]+=1;
					$drops[$x].="<a href='trailer_drop.php?id=".$row['id']."' target='_blank'>".date("m/d/Y", strtotime($row['linedate']))."</a>";
				}
			}
			
			if($found==0)			
			{
				$arr[$num]=$row['trailer_id'];
				$lab[$num]=$row['trailer_name'];	
				$drops[$num]="<a href='trailer_drop.php?id=".$row['id']."' target='_blank'>".date("m/d/Y", strtotime($row['linedate']))."</a>";
				$fnd[$num]=1;
				$num++;
			}			
		}
		$tab.="<table>";
		
		//for load board notes page...		
		$tab2.="<li><h3><span style='color:red;'>Trailer(s) in Multiple Locations...</span></h3></li>";
		
		for($x=0;$x < $num; $x++)
		{
			if(	$fnd[$x] > 1)	
			{
				$tab2.="<li><h3><span style='color:red;'>".$lab[$x]."</span>: ".$drops[$x]."</h3></li>";				
				$cntr2++;
			}
		}	
		
		if($cntr  == 0)	$tab="";
		if($cntr2 == 0)	$tab2="";
		
		$res['html']=$tab;
		$res['html2']=$tab2;
		return $res;	
	}
	
	function mrr_find_days_between_common_load_stops($sel_load,$cur_load)
	{		
		$days_apart=0;
		
		//first get current stops to fill around...     			
		$start_date="";     			
		$end_date="";
		$start_local="";     			
		$end_local="";
		$stops=0;	
		
		$sqlx="
			select load_handler_stops.*
			from load_handler_stops
			where load_handler_stops.load_handler_id='".sql_friendly($cur_load)."'
				and load_handler_stops.deleted<=0
			order by load_handler_stops.linedate_pickup_eta asc
		";
		$datax=simple_query($sqlx);
		while($rowx=mysqli_fetch_array($datax))
		{   //only need first stop       			
			if($stops==0)	
			{          				
				$start_date=$rowx['linedate_pickup_eta'];
				$start_local=trim($rowx['shipper_name']); 
			}
			$end_date=$rowx['linedate_pickup_eta'];
			$end_local=trim($rowx['shipper_name']);       			        			
			$stops++;
		}     			
     	//.........................................
		
		//now find FIRST stop that matches the location...
		$sqlx="
			select load_handler_stops.*
			from load_handler_stops
			where load_handler_stops.load_handler_id='".sql_friendly($sel_load)."'
				and load_handler_stops.deleted<=0
			order by load_handler_stops.linedate_pickup_eta asc
		";
		$datax=simple_query($sqlx);
		while($rowx=mysqli_fetch_array($datax))
		{
			if($start_local==trim($rowx['shipper_name']))
			{
          		$start_date_only=date("Y-m-d",strtotime($start_date)). "00:00:00";					//date only for starting point...current load
          		$this_date_only=date("Y-m-d",strtotime($rowx['linedate_pickup_eta'])). "00:00:00";		//date only for starting point...selected load which should be in the past
          		
          		$days_apart=strtotime($start_date_only) - strtotime($this_date_only);					//should be number of days different between new date and original first stop date (IN SECONDS)
          		$days_apart=(int) ceil($days_apart / (60 * 60 * 24));
          		
          		return $days_apart;	//exit.
			}
		}
		return $days_apart;
	}
	
	function mrr_send_email_list_of_maint_requests($active=0,$choose_recur=0)
	{
		global $defaultsarray;
		
		$adder="";
		if($active>0)		$adder=" and maint_requests.active='".sql_friendly($active)."'";
		
		$found=0;		
		$line1="";
		$line2="";
		
		
		$sqlx="
			select maint_requests.*
			from maint_requests
			where maint_requests.deleted<=0
				".$adder."
				and maint_requests.recur_flag='". sql_friendly( $choose_recur )."'
				and maint_requests.linedate_completed='0000-00-00 00:00:00'
			order by maint_requests.linedate_added desc
		";
		$datax=simple_query($sqlx);
		while($rowx=mysqli_fetch_array($datax))
		{
			$req_id=$rowx['id'];
			$request_description=trim($rowx['maint_desc']);
			
			$equip_type=get_option_name_by_id($rowx['equip_type']);	
			
			$name=identify_truck_trailer($rowx['equip_type'] , $rowx['ref_id']);
			
			$req_date="Request on ".date("m/d/Y H:i", strtotime($rowx['linedate_added']))." and scheduled for ".date("m/d/Y", strtotime($rowx['linedate_scheduled'])).".";
			
			
			$line1.="\r\n ".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id." for ".$request_description.".\r\n".$req_date."\r\n";
			$line2.="<br><a href='".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id."'>".$_SERVER['SERVER_NAME']."/maint.php?id=".$req_id."</a> <b>".$equip_type." #".$name."</b>  for <i>".$request_description."</i>.<br>".$req_date."<br>";
						
			$found++;
		}
		
		if($found>0)
		{			
			$send_to=$defaultsarray['company_email_address'];
			//$send_to=$defaultsarray['peoplenet_hot_msg_cc'];			//testing address...MRR
			$subject="Reminder: ".$found." Maintenance Requests Found.";
			
			$msg1="This is a reminder to check on the status of the following maintenance requests: \r\n ".$line1." \r\n";
			$msg2="<b>This is a reminder to check on the status of the following maintenance requests:</b> <br><br>".$line2."<br><br>";
			
			mrr_trucking_sendMail($send_to,'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
			//mrr_trucking_sendMail("disenberger22@gmail.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
            mrr_trucking_sendMail("Shamm@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
			mrr_trucking_sendMail("dconard@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
			mrr_trucking_sendMail("jgriffith@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
		}
		return $found;
	}
	
	function mrr_figure_profit_for_load_dispatch($load_id,$disp_id=0)
	{	
		//this function gives the profit for a given load dispatches.  If dispatch is given it will make the determination if the profit belongs on this dispatch or not.
		$disp_profit=0;
		$extra_cost=0;
		
		$sql = "
			select trucks_log.*,
				(select load_handler.actual_bill_customer from load_handler where load_handler.id=trucks_log.load_handler_id) as billed_customer,
				trucks.name_truck,
				trailers.trailer_name,
				concat(drivers.name_driver_first, ' ', drivers.name_driver_last) as driver_name
			
			from trucks_log
				left join trucks on trucks_log.truck_id = trucks.id
				left join trailers on trailers.id = trucks_log.trailer_id
				left join drivers on drivers.id = trucks_log.driver_id
			where trucks_log.load_handler_id = '".sql_friendly($load_id)."'
				and trucks_log.deleted <= 0
			
			order by trucks_log.linedate_pickup_eta, trucks_log.linedate, trucks_log.id
		";	
		$data_dispatch = simple_query($sql);
		
		if(!isset($data_dispatch) || !mysqli_num_rows($data_dispatch) ) 
		{ 
			return 0;
		} 
		else 
		{
			//determine "primary" dispatch for cost/profit display.
			$prime_dispatch_id=0;
			$prime_dispatch_miles=0;
			$prime_dispatch_hours=0;
			//$total_cost=0;
			$total_profit=0;
			
			while($row_dispatch = mysqli_fetch_array($data_dispatch)) 
			{					
				$prime_billed=$row_dispatch['billed_customer'];
				$prime_cost=$row_dispatch['cost'];				
									
				if($row_dispatch['daily_run_hourly'] > 0 || $row_dispatch['daily_run_otr'] > 0)	
				{
					$total_profit=($prime_billed - $prime_cost);
					$prime_dispatch_id=$row_dispatch['id'];
				}	
				elseif($prime_cost > 0)
				{
					$extra_cost=(0 - $prime_cost);		//add the cost to affect the profit.
				}								
			}
			//........................totals found for profit and cost
			
			if($disp_id==0)
			{
				$disp_profit=$total_profit;		//no dispatch, so just return load profit.
			}
			else
			{
				if($disp_id==$prime_dispatch_id)
				{
					$disp_profit=$total_profit;	//only return the profit if this is the primary dispatch. (Otherwise, it will be zero for the selected dispatch.) 
				}
				else
				{
					$disp_profit=$extra_cost;
				}			
			}
			return $disp_profit;
		}		
	}
	
	
	function mrr_get_log_database_name()
	{
		global $defaultsarray;
		
		$log_database_name="";
		//if(trim($defaultsarray['log_storage_database_name'])!="")		$log_database_name=$defaultsarray['log_storage_database_name'].".";
		
		return $log_database_name;
	}
	
	function mrr_find_logistics_company_login($user,$pass)
     {
     	$res['customer_name']="";
     	$res['customer_email']="";
     	$res['customer_phone']="";
     	$res['customer_id']=0;
     	
     	$cntr=0;
     	$sql="
     		select id,company_name,company_email,company_phone
     		from logistics_truck_companies 
     		where deleted<=0
     			and company_username='".sql_friendly($user)."'
     			and company_password='".sql_friendly($pass)."'
     			and active > 0
     	";
     	$data=simple_query($sql);
          while($rowc=mysqli_fetch_array($data))
          {
          	$res['customer_name']=trim($rowc['company_name']);
          	$res['customer_email']=trim($rowc['company_email']);
          	$res['customer_phone']=trim($rowc['company_phone']);
          	$res['customer_id']=$rowc['id'];
          	$cntr++;
          }
          if($cntr != 1 || trim($user)=="" || trim($pass)=="" )
          {
          	$res['customer_name']="Error: No Company Available.";
          	$res['customer_email']="";
          	$res['customer_phone']="";
          }
          return $res;
     }
	
	function mrr_find_customer_user_login($user,$pass)
     {
     	$res['customer_name']="";
     	$res['customer_id']=0;
     	$cntr=0;
     	$sql="
     		select id,name_company 
     		from customers 
     		where deleted<=0
     			and customer_login_name='".sql_friendly($user)."'
     			and customer_login_pass='".sql_friendly($pass)."'
     	";
     	$data=simple_query($sql);
          while($rowc=mysqli_fetch_array($data))
          {
          	$res['customer_name']=trim($rowc['name_company']);	
          	$res['customer_id']=$rowc['id'];
          	$cntr++;
          }
          if($cntr != 1 || trim($user)=="" || trim($pass)=="" )
          {
          	$res['customer_name']="Error: No Loads Available.";
          }
          return $res;
     }
     function mrr_confirm_driver_available($driver_id)
     {	
     	$cntr=0;
     	$today=date("Y-m-d")." 00:00:00";
     	$sql="
     		select id 
     		from trucks_log 
     		where deleted=0 
     			and linedate_pickup_eta>='".$today."'
     			and dispatch_completed<=0
     			and (driver_id='".sql_friendly($driver_id)."' or driver2_id='".sql_friendly($driver_id)."')
     	";
     	$data=simple_query($sql);
          if($row=mysqli_fetch_array($data))	
          {
          	$cntr++;	
          }
          
          $sql="
     		select id 
     		from load_handler 
     		where deleted<=0 
     			and load_handler.preplan > 0
     			and linedate_pickup_eta>='".$today."'
     			and (
     				preplan_driver_id='".sql_friendly($driver_id)."' 
     				or preplan_driver2_id='".sql_friendly($driver_id)."' 
     				or preplan_leg2_driver_id='".sql_friendly($driver_id)."' 
     				or preplan_leg2_driver2_id='".sql_friendly($driver_id)."'
     				)
     	";
     	$data=simple_query($sql);
          if($row=mysqli_fetch_array($data))	
          {
          	$cntr++;	
          }
          
          return $cntr;
     }
     
     
     function mrr_pull_customer_fuel_range($cust_id,$new_fuel_rate)
     {
     	$surcharge=0;
     	$sql="
     		select * 
     		from fuel_surcharge 
     		where customer_id='".sql_friendly($cust_id)."'
     			and range_lower<='".sql_friendly($new_fuel_rate)."'
     			and range_upper>='".sql_friendly($new_fuel_rate)."'
     		order by range_lower asc,range_upper asc
     	";
     	$data=simple_query($sql);
          if($row=mysqli_fetch_array($data))	
          {
          	$surcharge=$row['fuel_surcharge'];	
          }
          return $surcharge;
     }
     
     function mrr_update_load_fuel_surcharges($new_fuel_rate,$today)
     {	//update all future loads with the average fuel rate from TODAY on...but only if the customer has the fuel rate range active.
     	
     	$cntr=0;
     	
     	$tab="<table cellpadding='0' cellspacing='0' border='0' width='1400'>";
     	$tab.="
     		<tr>
     			<td valign='top'><b>Load</b></td>
     			<td valign='top'><b>PickupETA</b></td>     			
     			<td valign='top'><b>Customer</b></td>     			
     			<td valign='top' align='right'><b>NewAvg</b></td>
     			<td valign='top' align='right'><b>NewRate</b></td>
     			<td valign='top' align='right'><b>Miles</b></td>
     			
     			<td valign='top' align='right'><b>ActualRateSur</b></td>
     			<td valign='top' align='right'><b>ActualFuelCharge/mi</b></td>
     			
     			<td valign='top' align='right'><b>BillCust</b></td>
     			<td valign='top' align='right'><b>TotCost</b></td>
     			
     		</tr>
     	";		//<td valign='top' align='right'><b>UpdatedSur</b></td> 
     	
     	$sql="
     		select load_handler.*,
     			customers.use_fuel_surcharge,
     			customers.name_company as cust_name,
     			(select sum(miles) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) as mrr_miles
     			
     		from load_handler 
     			left join customers on customers.id=load_handler.customer_id
     		where load_handler.deleted<=0 
     			and customers.use_fuel_surcharge > 0
     			and load_handler.linedate_pickup_eta>='".$today."'
     			and load_handler.sicap_invoice_number=''
     		order by load_handler.linedate_pickup_eta asc,load_handler.id asc
     	";
     	$data=simple_query($sql);
          while($row=mysqli_fetch_array($data))	
     	{        			
     		$updated="";
     		if($row['update_fuel_surcharge']!="0000-00-00")		$updated=date("m/d/Y",strtotime($row['update_fuel_surcharge']));
     		
     		$my_rate=mrr_pull_customer_fuel_range($row['customer_id'],$new_fuel_rate);
     		$my_miles=$row['mrr_miles'];
     		
     		$tab.="
     			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>
     				<td valign='top'><a href='manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
     				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
     				<td valign='top'><a href='/admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['cust_name']."</a></td>     				
     				<td valign='top' align='right'> ".$new_fuel_rate."</td>
     				<td valign='top' align='right'> ".$my_rate."</td>
     				<td valign='top' align='right'> ".$my_miles."</td>
     				
     				<td valign='top' align='right'>$".number_format($row['actual_rate_fuel_surcharge'],3)."</td>
     				<td valign='top' align='right'>$".number_format($row['actual_fuel_charge_per_mile'],2)."</td>
     				   
     				<td valign='top' align='right'>$".number_format($row['actual_bill_customer'],2)."</td>
     				<td valign='top' align='right'>$".number_format($row['actual_total_cost'],2)."</td>       				
     				
     			</tr>
     		";		//<td valign='top' align='right'>".$updated."</td>
     		
     		//now update the actual settings to make it stick...so that this load now uses the current average fuel rate.
     		$sql="
				update load_handler set
					actual_rate_fuel_surcharge='".sql_friendly($new_fuel_rate)."',
					actual_fuel_charge_per_mile='".sql_friendly($my_rate)."'					     				
				where id='".sql_friendly($row['id'])."'
			";
			//simple_query($sql);        		
     		
     		$cntr++;
     	}    	
     	
     	$tab.="</table>";
     	
     	return $tab;
     }
     
     function mrr_find_acct_database_name()
     {    //add prefix of database to any table...  "sicap_conard.invoice"
     	return "sicap_conard.";
     }
     function mrr_pull_ooic_exp($truck_id,$driver_id,$date_from,$date_to)
	{
		$expenses=0;
		$dbnamer= mrr_find_acct_database_name();
		
		
		return $expenses;
	}
	function mrr_pull_ooic_insurance_exp($truck_id,$driver_id,$date_from,$date_to)
	{
		$expenses=0;
		$dbnamer= mrr_find_acct_database_name();
		
		
		return $expenses;
	}
     function mrr_pull_income_fuel_exp($truck_id,$driver_id,$date_from,$date_to)
	{
		$expenses=0;
		$dbnamer= mrr_find_acct_database_name();
		
		if($truck_id==0 && $driver_id>0)
		{
			$sql = "
				select attached_truck_id,attached2_truck_id 
				from drivers 
				where id='".sql_friendly($driver_id)."'
			";
     		$data = simple_query($sql);	
     		if($row = mysqli_fetch_array($data))
     		{
     			$truck_id= $row['attached_truck_id'];
     			//$truck_id= $row['attached2_truck_id'];
     		}
		}		
		
		$coa_name="";
		if($truck_id>0)
		{
			$sql = "select name_truck from trucks where id='".sql_friendly($truck_id)."'";
     		$data = simple_query($sql);	
     		if($row = mysqli_fetch_array($data))
     		{
     			$coa_name="58800-".trim($row['name_truck'])."";		//(int) 
     		}
     		if($coa_name=="58800-")		$coa_name="";
		}
		
		$sql = "
			select IFNULL(bills_details.amount,0) as bill_amount,
				chart_entries_details.*			
			from ".$dbnamer."chart_entries_details
				left join ".$dbnamer."chart_entries on chart_entries.id = chart_entries_details.chart_entries_id
				left join ".$dbnamer."chart on chart.id=chart_entries_details.chart_id
				left join ".$dbnamer."bills_details on bills_details.bill_id=chart_entries_details.bill_id and bills_details.deleted=0 and bills_details.chart_id=chart_entries_details.chart_id
				left join ".$dbnamer."bills on bills.id=bills_details.bill_id and bills.deleted=0
			where chart_entries_details.deleted <= 0
				and chart_entries.deleted <= 0
				and chart_entries.voided <= 0
				and (
					(bills.linedate>='".date("Y-m-d", strtotime($date_from))." 00:00:00' and bills.linedate<='".date("Y-m-d", strtotime($date_to))." 23:59:59')
					or
					(chart_entries.linedate>='".date("Y-m-d", strtotime($date_from))." 00:00:00' and chart_entries.linedate<='".date("Y-m-d", strtotime($date_to))." 23:59:59')
					)
				and chart.chart_number='".sql_friendly($coa_name)."'
			order by chart_entries.linedate asc
     	";		//and chart_entries.linedate>='".date("Y-m-d", strtotime($date_from))." 00:00:00' 
     			//and chart_entries.linedate<='".date("Y-m-d", strtotime($date_to))." 23:59:59'
     	
     	
     	
     	$sql = "     	
     		select if(chart_entries.deposit_id > 0, chart_entries_details.amount, chart_entries_details.amount * -1) as amount,
     			chart_entries.linedate,
     			ifnull(chart_name, '(uncategorized)') as chart_name,
     			if(chart_entries.deposit_id > 0, chart_entries.deposit_id, chart.id) as id,			
     			chart_entries_details.memo,
     			chart_entries_details.bill_id,
     			concat('manage_checks.php?check_id=',chart_entries.id) as use_link,
     			if(chart_entries.deposit_id > 0, 'Deposit', 'Check') as line_type,
     			check_number as line_num,
     			case 
     				when chart_entries.vendor_id > 0 then (select name_company from ".$dbnamer."vendors where vendors.id = chart_entries.vendor_id)
     				when chart_entries.customer_id > 0 then (select name_company from ".$dbnamer."customers where customers.id = chart_entries.customer_id)
     				when chart_entries.user_id > 0 then (select concat(name_first,' ',name_last) from ".$dbnamer."users where users.id = chart_entries.user_id)
     				else ''
     			end as line_to
     		
     		from ".$dbnamer."chart_entries
     			left join ".$dbnamer."chart_entries_details on chart_entries.id = chart_entries_details.chart_entries_id and chart_entries_details.deleted = 0 and chart_entries_details.bill_id = 0
     			left join ".$dbnamer."chart on chart.id = chart_entries.chart_id
     			left join ".$dbnamer."chart_types on chart_types.id = chart.chart_type_id
     			left join ".$dbnamer."vendors on vendors.id = chart_entries.vendor_id
     			left join ".$dbnamer."customers on customers.id = chart_entries.customer_id
     			
     		where chart_entries.deleted = 0
     			and chart_entries.journal_id = 0
     			and chart_entries.linedate >= '".date("Y-m-d", strtotime($date_from))." 00:00:00'
     			and chart_entries.linedate <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
     			and chart.chart_number='".sql_friendly($coa_name)."'
     			and voided = 0	
     
     		union all
     		
     		select (chart_entries.payment_amount - chart_entries.deposit_amount) as amount,
     			chart_entries.linedate,
     			ifnull(chart_name, '(uncategorized)') as chart_name,
     			chart.id,
     			chart_entries.memo,
     			chart_entries.id,
     			concat('manage_checks.php?check_id=',chart_entries.id) as use_link,
     			'Journal' as line_type,
     			check_number as line_num,
     			if(chart_entries.vendor_id > 0, (select name_company from ".$dbnamer."vendors where vendors.id = chart_entries.vendor_id), (select name_company from ".$dbnamer."customers where customers.id = chart_entries.customer_id)) as line_to
     		
     		from ".$dbnamer."chart_entries
     			left join ".$dbnamer."chart on chart.id = chart_entries.chart_id
     			left join ".$dbnamer."chart_types on chart_types.id = chart.chart_type_id
     			left join ".$dbnamer."vendors on vendors.id = chart_entries.vendor_id
     			left join ".$dbnamer."customers on customers.id = chart_entries.customer_id
     			
     		where chart_entries.deleted = 0
     			and chart_entries.journal_id > 0
     			and chart_entries.linedate >= '".date("Y-m-d", strtotime($date_from))." 00:00:00'
     			and chart_entries.linedate <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
     			and chart.chart_number='".sql_friendly($coa_name)."'			
     			and voided = 0	
     
     		union all     		
     		
     		select (bills_details.amount * -1) as amount,
     			bills.linedate,
     			ifnull(chart_name, '(uncategorized)') as chart_name,
     			chart.id,
     			bills_details.notes,
     			bills_details.bill_id,
     			concat('manage_bills.php?bill_id=',bills.id) as use_link,
     			'Bill' as line_type,
     			bills_details.bill_id as line_num,
     			vendors.name_company
     			
     		from ".$dbnamer."bills_details
     			inner join ".$dbnamer."bills on bills.id = bills_details.bill_id and bills_details.deleted = 0 and bills.deleted = 0
     			left join ".$dbnamer."chart on chart.id = bills_details.chart_id
     			left join ".$dbnamer."chart_types on chart_types.id = chart.chart_type_id
     			left join ".$dbnamer."vendors on vendors.id = bills.vendor_id
     		where bills_details.deleted = 0     			
     			and bills.linedate >= '".date("Y-m-d", strtotime($date_from))." 00:00:00'
     			and bills.linedate <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
     			and chart.chart_number='".sql_friendly($coa_name)."'	
     		
     			
     		order by linedate
     	";
     	/*
     	
     		union all 
     		
     		select (invoice_entries.unit_price * invoice_entries.qty_shipped) as amount,
     			invoice.linedate,
     			'chart_name',
     			invoice.id,
     			invoice_entries.item_desc,
     			'xref_id',
     			concat('invoice.php?invoice_id=', invoice.id),
     			'Invoice',
     			invoice.id,
     			customers.name_company
     		
     		from ".$dbnamer."invoice
     			inner join ".$dbnamer."invoice_entries on invoice_entries.invoice_id = invoice.id and invoice.deleted = 0 and invoice_entries.qty_shipped!=0 and invoice_entries.unit_price!=0
     			left join ".$dbnamer."inventory on inventory.id = invoice_entries.item_id
     			left join ".$dbnamer."customers on customers.id = invoice.customer_id
     			left join ".$dbnamer."vendors on inventory.default_vendor_id = vendors.id
     			
     		where invoice.linedate >= '".date("Y-m-d", strtotime($date_from))." 00:00:00'
     			and invoice.linedate <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
     			and invoice.deleted = 0
     			and chart.chart_number='".sql_friendly($coa_name)."'	
     	*/
     	
     	$data = simple_query($sql);
     	while($row = mysqli_fetch_array($data))
     	{
     		$expenses+=abs($row['amount']);
     		//if($row['amount'] > 0)		$expenses+=$row['amount'];			else			$expenses+=$row['amount'];
     	}
     	//echo "<br>Expense Function Query: Truck ID=".$truck_id." | Driver ID=".$driver_id." [".$coa_name."] $".number_format($expenses,2)." -- ".$sql."<br>";
     	return $expenses;
	}
     
     
     function mrr_find_log_database_name()
     {    //add prefix of database to any table...  "db_log.mytable"....  function returns "db_log." to use as prefix where this function is installed.
     	//Only use for tables in the log database.  Added Dec 2014.	
     	
     	$log_db_namer=mrr_get_default_variable_setting("log_storage_database_name","General Settings");	
     	
     	if(trim($log_db_namer)!="")
     	{
     		return $log_db_namer.".";
     	}
     	return "";
     }
     
     function mrr_money_stripper($val)
     {
     	$val=trim($val);
     	$val=str_replace("$","",$val);
     	$val=str_replace(",","",$val);
     	$val=str_replace("(","",$val);
     	$val=str_replace(")","",$val);
     	$val=str_replace("+","",$val);
     	$val=str_replace("{","",$val);
     	$val=str_replace("}","",$val);
     	$val=str_replace(" ","",$val);
     	
     	if(!is_numeric($val))			$val="0.000";
     	     	
     	return $val;
     }
     
     function mrr_backprocess_payroll_raise_dispatches($driver_id,$date_start,$run_update=0)
     {
          if(trim($date_start)=="")		return "";
          
          $cntr=0;
          $rep="";
          $rep.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
          
          	
          $sqld="
          	select * 
          	from drivers
          	where id='".$driver_id."'
          ";
          $datad=simple_query($sqld);
          if($rowd=mysqli_fetch_array($datad))
          {
          	$single_mile_pay=$rowd['pay_per_mile'];
          	$single_hour_pay=$rowd['pay_per_hour'];
          	$single_mile_pay_charged=$rowd['charged_per_mile'];
          	$single_hour_pay_charged=$rowd['charged_per_hour'];
          	$team_mile_pay=$rowd['pay_per_mile_team'];
          	$team_hour_pay=$rowd['pay_per_hour_team'];
          	$team_mile_pay_charged=$rowd['charged_per_mile_team'];
          	$team_hour_pay_charged=$rowd['charged_per_hour_team'];
          	
          	$rep.="
          		<tr>
          			<td valign='top' colspan='4' align='left'><b>".trim($rowd['name_driver_first']." ".$rowd['name_driver_last'])."</b></td>
          			<td valign='top' colspan='2' align='right'><b>Effective: ".$date_start."</b></td>
          			<td valign='top' align='right'>$".number_format($single_mile_pay_charged,4)."</td>
               		<td valign='top' align='right'>$".number_format($single_hour_pay_charged,4)."</td>
               		<td valign='top' align='right'>$".number_format($single_mile_pay,4)."</td>
               		<td valign='top' align='right'>$".number_format($single_hour_pay,4)."</td>     			
               		<td valign='top' align='right'>$".number_format($team_mile_pay_charged,4)."</td>
               		<td valign='top' align='right'>$".number_format($team_hour_pay_charged,4)."</td>
               		<td valign='top' align='right'>$".number_format($team_mile_pay,4)."</td>
               		<td valign='top' align='right'>$".number_format($team_hour_pay,4)."</td>   
          		</tr>
          	";     
          	
          	$rep.="
          		<tr>
          			<td valign='top' align='left'><b>Load</b></td>
          			<td valign='top' align='left'><b>Dispatch</b></td>
          			<td valign='top' align='left'><b>Pickup</b></td>
          			<td valign='top' align='left'><b>Origin</b></td>
          			<td valign='top' align='left'><b>Destination</b></td>
          			<td valign='top' align='left'><b>Position</b></td>
          			<td valign='top' align='right'><b>LaborPerMile</b></td>
          			<td valign='top' align='right'><b>LaborPerHour</b></td>
          			<td valign='top' align='right'><b>PayPerMile</b></td>
          			<td valign='top' align='right'><b>PayPerHour</b></td>
          			<td valign='top' align='right'><b>LaborPerMile</b></td>
          			<td valign='top' align='right'><b>LaborPerHour</b></td>
          			<td valign='top' align='right'><b>PayPerMile</b></td>
          			<td valign='top' align='right'><b>PayPerHour</b></td>
          		</tr>
          	";          
               $sql="
               	select trucks_log.* 
               	from trucks_log
               	where trucks_log.deleted<=0
               		and (trucks_log.driver_id='".$driver_id."' or trucks_log.driver2_id='".$driver_id."')
               		and trucks_log.linedate_pickup_eta>='".date("Y-m-d",strtotime($date_start))." 00:00:00'
               	order by trucks_log.linedate_pickup_eta asc,trucks_log.id asc
               ";
               $data=simple_query($sql);
               while($row=mysqli_fetch_array($data))
               {
               	$d1_style="color:#000000; font-weight:bold;";
               	$d2_style="color:#999999; font-weight:normal;";
               	if($row['driver2_id']==$driver_id)
               	{	//reverse the style...highlight driver 2 settings.
               		$d2_style="color:#000000; font-weight:bold;";
               		$d1_style="color:#999999; font-weight:normal;";	
               	}
               	
               	$rep.="
               		<tr class='".($cntr%2==0 ? "even" : "odd")."'>
               			<td valign='top' align='left'>".$row['load_handler_id']."</td>
               			<td valign='top' align='left'>".$row['id']."</td>
               			<td valign='top' align='left'>".date("m/d/Y H;i",strtotime($row['linedate_pickup_eta']))."</td>
               			<td valign='top' align='left'>".$row['origin'].",".$row['origin_state']."</td>
               			<td valign='top' align='left'>".$row['destination'].",".$row['destination_state']."</td>
               			<td valign='top' align='left'>".($row['driver_id']==$driver_id ? "Driver 1" : "Driver 2")."</td>
               			<td valign='top' align='right'><span style='".$d1_style."'>$".number_format($row['labor_per_mile'],4)."</span></td>
               			<td valign='top' align='right'><span style='".$d1_style."'>$".number_format($row['labor_per_hour'],4)."</span></td>
               			<td valign='top' align='right'><span style='".$d1_style."'>$".number_format($row['driver1_pay_per_mile'],4)."</span></td>
               			<td valign='top' align='right'><span style='".$d1_style."'>$".number_format($row['driver1_pay_per_hour'],4)."</span></td>     			
               			<td valign='top' align='right'><span style='".$d2_style."'>$".number_format($row['driver_2_labor_per_mile'],4)."</span></td>
               			<td valign='top' align='right'><span style='".$d2_style."'>$".number_format($row['driver_2_labor_per_hour'],4)."</span></td>
               			<td valign='top' align='right'><span style='".$d2_style."'>$".number_format($row['driver2_pay_per_mile'],4)."</span></td>
               			<td valign='top' align='right'><span style='".$d2_style."'>$".number_format($row['driver2_pay_per_hour'],4)."</span></td>   
               		</tr>
               	";	
               		//<td valign='top' align='right'><span style='".$d1_style."'>$".number_format($row['driver1_overtime_hourly_charged'],4)."</span></td>
               		//<td valign='top' align='right'><span style='".$d1_style."'>$".number_format($row['driver1_overtime_hourly_paid'],4)."</span></td>
               		//<td valign='top' align='right'><span style='".$d2_style."'>$".number_format($row['driver2_overtime_hourly_charged'],4)."</span></td>
               		//<td valign='top' align='right'><span style='".$d2_style."'>$".number_format($row['driver2_overtime_hourly_paid'],4)."</span></td>
               	
               	if($row['driver2_id']==$driver_id && $run_update > 0)
               	{	//update driver 2 settings.
               		
               		//driver2_overtime_hourly_charged  	driver2_overtime_hourly_paid
               		$sqlu="
               			update trucks_log set
               				driver_2_labor_per_mile='".sql_friendly($team_mile_pay_charged)."',
               				driver_2_labor_per_hour='".sql_friendly($team_hour_pay_charged)."',
               				driver2_pay_per_mile='".sql_friendly($team_mile_pay)."',
               				driver2_pay_per_hour='".sql_friendly($team_hour_pay)."'
               				
               			where id='".sql_friendly($row['id'])."'
               		";          		
               		simple_query($sqlu);
               	}
               	elseif($run_update > 0)
               	{	//update driver 1 settings.
               		
               		//driver1_overtime_hourly_charged		driver1_overtime_hourly_paid
               		$sqlu="
               			update trucks_log set
               				labor_per_mile='".sql_friendly($single_mile_pay_charged)."',
               				labor_per_hour='".sql_friendly($single_hour_pay_charged)."',
               				driver1_pay_per_mile='".sql_friendly($single_mile_pay)."',
               				driver1_pay_per_hour='".sql_friendly($single_hour_pay)."'
               				
               			where id='".sql_friendly($row['id'])."'
               		";           		
               		simple_query($sqlu);
               	}          	
               	
               	$cntr++;
               }
          }	
     
          $rep.="</table><br><b>".$cntr."</b> Dispatches Found to update.";
          return $rep;
     }

     function mrr_process_scheduled_pay_raises($update_dispatches=0)
     {
     	$cntr=0;
     	
     	$today=date("Y-m-d",time())." 23:59:59";		//get end of today's date/time
     	
		$sql="
			select * 
			from driver_payroll_changes
			where deleted<=0
				and driver_id>0
				and auto_schedule > 0
				and linedate <= '".$today."'
			order by driver_id asc
		";
		$data=simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			//update driver settings for future loads
			$sqlu="
				update drivers set
					pay_per_mile = '".sql_friendly($row['single_mile_pay'])."',
					pay_per_hour = '".sql_friendly($row['single_hour_pay'])."',
					charged_per_mile = '".sql_friendly($row['single_mile_pay_charged'])."',
					charged_per_hour = '".sql_friendly($row['single_hour_pay_charged'])."',
					pay_per_mile_team = '".sql_friendly($row['team_mile_pay'])."',
					pay_per_hour_team = '".sql_friendly($row['team_hour_pay'])."',
					charged_per_mile_team = '".sql_friendly($row['team_mile_pay_charged'])."',
					charged_per_hour_team = '".sql_friendly($row['team_hour_pay_charged'])."'
				where id='".sql_friendly($row['driver_id'])."'
			";
			simple_query($sqlu);			
			
			//now marked as processed...turn off which makes it a changed log entry
			$sqlu="
				update driver_payroll_changes set
					auto_schedule=0
				where id='".sql_friendly($row['id'])."'
			";
			simple_query($sqlu);
			
			if($update_dispatches > 0)
			{
				$rep=mrr_backprocess_payroll_raise_dispatches($row['driver_id'],$row['linedate'],1);	
			}
								
			$cntr++;
		}	
		
		return $cntr;		
     }
     
     function mrr_send_email_list_of_driver_reviews($days=0,$testing=0)
	{
		global $defaultsarray;
		
		$today=date("Y-m-d",time())." 23:59:59";		//get end of today's date/time
		
		$adder=" and (linedate_review_due='0000-00-00 00:00:00' or linedate_review_due<='".$today."')";
		if($days>0)	$adder=" and (linedate_review_due='0000-00-00 00:00:00' or linedate_review_due<=DATE_ADD('".$today."' ,INTERVAL ".(int) $days." DAY) )";
		
		$found=0;		
		$line1="";
		$line2="";
		$msg1="";
		$msg2="";
		
		$sqlx="
			select *
			from drivers
			where deleted<=0
				and active > 0
				and id!=405
				".$adder."
			order by name_driver_last asc,name_driver_first asc,id asc
		";
		$datax=simple_query($sqlx);
		while($rowx=mysqli_fetch_array($datax))
		{
			$id=$rowx['id'];			
			$name=trim($rowx['name_driver_first']." ".$rowx['name_driver_last']);
			
			$rev_date="Review Due Date: ".date("m/d/Y", strtotime($rowx['linedate_review_due'])).".";
			if($rowx['linedate_review_due']=="" || $rowx['linedate_review_due']=="0000-00-00 00:00:00")	$rev_date="Review Due Date: N/A.";	
			
			$line1.="\r\n ".$_SERVER['SERVER_NAME']."/admin_drivers.php?id=".$id." for ".$name.". ".$rev_date.".\r\n";
			$line2.="<br><a href='".$_SERVER['SERVER_NAME']."/admin_drivers.php?id=".$id."'>".$_SERVER['SERVER_NAME']."/maint.php?id=".$id."</a> <b>".$name."</b>. ".$rev_date."<br>";
						
			$found++;
		}
		
		if($found>0)
		{			
			$send_to=$defaultsarray['company_email_address'];
			$send_to="trucking@conardtransportation.com";		//amassar@conardlogistics.com
			
			$cc_1="dconard@conardtransportation.com";
			$cc_2="jgriffith@conardtransportation.com";
			//$cc_3="kmullins@conardtransportation.com";
			$cc_4="";   //$defaultsarray['special_email_monitor'];
			
			$subject="Reminder: ".$found." Drivers need Reviews.";
			
			$msg1="This is a reminder to check on the following drivers for review due dates to be updated: \r\n ".$line1." \r\n";
			$msg2="<b>This is a reminder to check on the following drivers for review due dates to be updated:</b> <br><br>".$line2."<br><br><i>Printed from mrr_send_email_list_of_driver_reviews(".$days.",".$testing.") function.</i>";
			
			if($testing==0)
			{
				mrr_trucking_sendMail($send_to,'Driver Reviews',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
				if(trim($cc_1)!="")		mrr_trucking_sendMail(trim($cc_1),'Driver Reviews',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
				if(trim($cc_2)!="")		mrr_trucking_sendMail(trim($cc_2),'Driver Reviews',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
				if(trim($cc_3)!="")		mrr_trucking_sendMail(trim($cc_3),'Driver Reviews',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
				if(trim($cc_4)!="")		mrr_trucking_sendMail(trim($cc_4),'Driver Reviews',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
			}
			else
			{
				if(trim($cc_4)!="")		mrr_trucking_sendMail(trim($cc_4),'Driver Reviews',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
			}
		}
		if($testing > 0)
		{
			$res['found']=$found;
			$res['list1']=$msg1;
			$res['list2']=$msg2;
			
			return $res;	
		}
		
		return $found;
	}
	
	
	function mrr_auto_calculate_surcharge($customer_id,$save_surcharge=0)
	{	
		global $defaultsarray;
		
		$national_avg=$defaultsarray['fuel_surcharge'];
		if(!is_numeric($national_avg))		$national_avg=0;
		
		if($save_surcharge > 0 && $save_surcharge!=$national_avg)		$national_avg=$save_surcharge;		//use the one that was saved at the time....
		
		$avg_mph=$defaultsarray['average_mpg'];
		if(!is_numeric($avg_mph))		$avg_mph=0;
		
		$sur=0;
		$formula_code=0;
		
		//Bridgestone group....
		if($customer_id==1584)		$formula_code=1;		//Bridgestone Akron 	
		if($customer_id==1426)		$formula_code=1;		//Bridgestone Americas Graniteville 	
		if($customer_id==1461)		$formula_code=1;		//Bridgestone Americas Tire Operations 	
		if($customer_id==1421)		$formula_code=1;		//Bridgestone Americas Tire Ops Inc- IA 	
		if($customer_id==1450)		$formula_code=1;		//Bridgestone Americas Tire Ops-IL 	
		if($customer_id==1374)		$formula_code=1;		//Bridgestone Americas Woodridge DC 	
		if($customer_id==1460)		$formula_code=1;		//Bridgestone C/O CTSI 	
		if($customer_id==1423)		$formula_code=1;		//Bridgestone c/o Williams and Associates 	
		if($customer_id==1539)		$formula_code=1;		//Bridgestone Lebanon 	
		if($customer_id==1537)		$formula_code=1;		//Bridgestone Wilson
		if($customer_id==1606)		$formula_code=1;		//Bridgestone Morrison
		if($customer_id==1622)		$formula_code=1;		//Bridgestone Roanoke
				
		if($formula_code==1)
		{
			$factor=1.16;
			//if($avg_mph > 0)		$sur=(  ($national_avg - $factor) / $avg_mph);
			$sur=(  ($national_avg - $factor) / 6.0);
		}
		return "$".number_format($sur,2);	
	}
	
	function mrr_display_equipment_depreciation($truck_id=0,$trailer_id=0)
	{
		$tab="";
		
		$cntr=0;
		
		$starting_equip_value=0;
		$starting_unit_value=0;
		
		$type=1;
		$id=$truck_id;
		if($trailer_id > 0)
		{
			$type=2;
			$id=$trailer_id;	
		}
		elseif($truck_id > 0)
		{
			$sql = "
     			select apu_value
     			from trucks		
     			where id = '".sql_friendly($id) ."'
     		";		
     		$data = simple_query($sql);
     		if($row = mysqli_fetch_array($data))
     		{
     			$starting_unit_value=$row['apu_value'];
     		}	
		}
		
		if($id==0)	return $tab;		//exit 
		
		$sql = "
			select *
			from equipment_history			
			where deleted <= 0				
				and equipment_id = '".sql_friendly($id) ."'
				and equipment_type_id = '".sql_friendly($type) ."'				 
			order by linedate_aquired asc,linedate_returned asc,id asc
		";		
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$starting_equip_value=$row['equipment_value'];
		}
		
		
		$tab.="
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			<tr>
				<td valign='top'><b>From</b></td>
				<td valign='top'><b>To</b></td>
				<td valign='top' align='right'><b>PN Unit</b></td>
				<td valign='top' align='right' title='Tracking Unit Depreciation'><b>Depr.</b></td>
				<td valign='top' align='right'><b>Equipment</b></td>
				<td valign='top' align='right' title='Equipment Depreciation'><b>Depr.</b></td>				
			</tr>
		";
		
		$equip_val=0;
		$equip_cur=0;
		$equip_dep=0;
		$unit_val=0;
		$unit_cur=0;
		$unit_dep=0;
		
		$started=0;
		$started2=0;
		
		$sql = "
			select *
			from equipment_value_tracking			
			where deleted <= 0
				".($truck_id > 0 ? " and truck_id = '".sql_friendly($truck_id) ."'"  : "" )."
				".($trailer_id > 0 ? " and trailer_id = '".sql_friendly($trailer_id) ."'"  : "" )."
			order by linedate asc,id asc
		";	
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			if($row['equip_value']==0 && $row['equip_value_end'] > 0 && $started==0)	
			{
				$row['equip_value']=$starting_equip_value;	
				$started=1;	
			}
			
			if($row['unit_value']==0 && $row['unit_value_end'] > 0 && $started2==0)	
			{
				$row['unit_value']=$starting_unit_value;
				$started2=1;	
			}
									
			$equip_dep+=($row['equip_value'] - $row['equip_value_end']);
			$unit_dep+=($row['unit_value'] - $row['unit_value_end']);
			
			$tab.="
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'>".date("m/d/Y",strtotime($row['linedate']))."</td>
				<td valign='top'>".date("m/d/Y",strtotime($row['linedate_end']))."</td>
				<td valign='top' align='right'><span title='Original/Previous Value'>O</span> $".number_format($row['unit_value'],2)."<br>-<span title='Current Value'>C</span> $".number_format($row['unit_value_end'],2)."</td>
				<td valign='top' align='right'>$".number_format(($row['unit_value'] - $row['unit_value_end']),2)."</td>
				<td valign='top' align='right'><span title='Original/Previous Value'>O</span> $".number_format($row['equip_value'],2)."<br>-<span title='Current Value'>C</span> $".number_format($row['equip_value_end'],2)."</td>
				<td valign='top' align='right'>$".number_format(($row['equip_value'] - $row['equip_value_end']),2)."</td>				
			</tr>
			";
			$cntr++;
		}
		$tab.="
			<tr>
				<td valign='top'><b>Subtotals</b></td>
				<td valign='top'>&nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>$".number_format($unit_dep,2)."</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>$".number_format($equip_dep,2)."</td>				
			</tr>
			<tr>
				<td valign='top' colspan='4'><b>Total Depreciation</b></td>
				<td valign='top' align='right'>$".number_format(($equip_dep+$unit_dep),2)."</td>	
				<td valign='top'>&nbsp;</td>			
			</tr>
			</table>
		";
		
		return $tab;
	}
	
	function mrr_display_equipment_depreciation_final($dater,$truck_id=0,$trailer_id=0)
	{
		$value=-1;
		$value2=-1;
		
		$res['cur_equip_value']=$value;
		$res['cur_unit_value']=$value2;
		$res['sql']="";
		
		$type=1;
		$id=$truck_id;
		if($trailer_id > 0)
		{			
			$id=$trailer_id;	
			$type=2;
		}
		elseif($truck_id > 0)
		{
			$sql = "
     			select apu_value
     			from trucks		
     			where id = '".sql_friendly($id) ."'
     		";		
     		$data = simple_query($sql);
     		if($row = mysqli_fetch_array($data))
     		{
     			$value2=$row['apu_value'];
     		}	
     		$res['sql']=$sql;
		}
				
		if($id==0)	return $res;		//exit 
		
		//dater is already a time...
		
		$sql = "
			select *
			from equipment_value_tracking			
			where deleted <= 0
				".($truck_id > 0 ? " and truck_id = '".sql_friendly($truck_id) ."'"  : "" )."
				".($trailer_id > 0 ? " and trailer_id = '".sql_friendly($trailer_id) ."'"  : "" )."
				and linedate<='".date("Y-m-d",$dater)."'				
			order by linedate desc,linedate_added desc,id desc
			".($truck_id > 0 ? "limit 1"  : "" )."
		";		//and linedate>='".date("Y-m-d",$dater)."'
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$value=$row['equip_value_end'];
			$value2=$row['unit_value_end'];
		}	
		if(mysqli_num_rows($data) > 1 && $truck_id > 0  && $row = mysqli_fetch_array($data))
		{
			$value=$row['equip_value_end'];
			$value2=$row['unit_value_end'];	
		}
        
		$res['cnt']=mysqli_num_rows($data);
		$res['cur_equip_value']=$value;
		$res['cur_unit_value']=$value2;
		$res['sql']=$sql;
		return $res;
	}
	
	
	function mrr_get_user_menu_page_selector($field,$pre=0)
	{
		$selbox="<select name='".$field."' id='".$field."'>";
		
		$sel="";		if($pre==0 || $pre=="")	$sel=" selected";	
		$selbox.="<option value='0'".$sel.">Select Menu Item</option>";	
		
		$sel="";		if($pre==-1)	$sel=" selected";	
		$selbox.="<option value='-1'".$sel.">Add Spacer Here</option>";	
		
		$cur_access=0;
		if(isset($_SESSION['user_id']))		$cur_access=mrr_get_user_access_level($_SESSION['user_id']);
		
		$sql = "
			select *
			
			from user_menu_pages
			where deleted<=0
				and access_level<='".sql_friendly($cur_access)."'
			order by page_label asc, id asc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$sel="";		if($pre==$row['id'])	$sel=" selected";	
			$selbox.="<option value='".$row['id']."'".$sel.">".trim($row['page_label'])."</option>";		
		}
		
		$selbox.="</select>";	
		return $selbox;
	}
	function mrr_get_user_menu_page($id,$cd=0)
	{
		$value="";
		$sql = "
			select *			
			from user_menu_pages
			where id='".sql_friendly($id)."'
		";	
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			$value=trim($row['page_name']);
			if($cd==1)	$value=trim($row['page_label']);
		}	
		
		if($id==-1)
		{
			$value="";	
			if($cd==1)	$value="---";	
		}
		
		return $value;
	}
	
	function mrr_get_mini_menu_display($cd=0)
	{
		$cur_user=0;
		$cur_access=0;
		if(isset($_SESSION['user_id']))	
		{
			$cur_user=$_SESSION['user_id'];
			$cur_access=mrr_get_user_access_level($cur_user);
		}		
		
		$tab="<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
		
		
		if($cd>0)		$tab="";
		
			
		$sql="
		select *
			from user_menu_custom
			where user_id='".sql_friendly($cur_user)."'
				and deleted<=0
			order by zorder asc,id asc
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$linker="<hr>";
			if($row['page_id'] > 0)
			{
				$label=trim($row['label']);
				$tip=trim($row['tool_tip']);
				
				$url=mrr_get_user_menu_page($row['page_id'],0);				
				
				//$url=str_replace("'","",$url);
				$tip=str_replace("'","",$tip);
				
				if($label=="")		$label=mrr_get_user_menu_page($row['page_id'],1);					
				
				$new_win="";		if(substr_count($url,"http://") > 0 || substr_count($url,"https://") > 0)		$new_win=" target='_blank'";
				$linker="<a href='".$url."'".$new_win." title='".$tip."'>".$label."</a>";
			}
			
			if($cd>0)
			{	//adding to main header menu, so format accordingly...
				if($linker=="<hr>")
				{
					$tab.="<li class='mrr_li'><hr style='clear:both'></li>";
				}
				else
				{
					$tab.="<li class='mrr_li'><a class='nav_popup_link mrr_li' href='".$url."'".$new_win." title='".$tip."'>".$label."</a></li>";	
				}				
			}
			else
			{	//simple preview mode...
				$tab.="
					<tr>
						<td valign='top'>".$linker."</td>
					</tr>
				";
			}	
		}
		if($cd > 0)	
		{	//allow editing menu....
			$tab.="
				<li class='mrr_li'><hr style='clear:both'></li>
				<li class='mrr_li'><a class='nav_popup_link mrr_li' href='admin_mini_menu.php' target='_blank' title='Click here to edit your Mini-Menu'>{Edit Your Mini-Menu}</a></li>
			";
		}
		else
		{
			$tab.="
				<tr>
					<td valign='top' align='right'>{<a href='admin_mini_menu.php' target='_blank' title='Click here to edit your Mini-Menu'>Edit Menu</a>}</td>
				</tr>
				</table>
			";	
		}
		
		return $tab;
	}	
	
	function duplicate_row($table, $id) 
	{	
		global $datasource;

		// function to duplicate a row from a table
		$sql = "
			show columns from $table
		";
		$data_fields = simple_query($sql);
		$row_fields = mysqli_fetch_array($data_fields);
		$field_array = array();
		while($row_fields = mysqli_fetch_array($data_fields)) 
		{
			//echo $row_fields['Field']."<br>";
			if($row_fields['Field'] != 'id') $field_array[] = $row_fields['Field'];
		}
		$field_list = implode(",", $field_array);
		$sql = "
			insert into $table ($field_list)
			select $field_list from $table i where i.id = '".sql_friendly($id)."'
		";		
		simple_query($sql);		
		
		return mysqli_insert_id($datasource);
	}
	
	function mrr_make_numeric2($txt="")
	{	//For COAs
		$txt=strtolower(trim($txt));
		
		$txt=str_replace('"',"",$txt);
		$txt=str_replace("'","",$txt);
		$txt=str_replace("a","",$txt);
		$txt=str_replace("b","",$txt);
		$txt=str_replace("c","",$txt);
		$txt=str_replace("d","",$txt);
		$txt=str_replace("e","",$txt);
		$txt=str_replace("f","",$txt);
		$txt=str_replace("g","",$txt);
		$txt=str_replace("h","",$txt);
		$txt=str_replace("i","",$txt);
		$txt=str_replace("j","",$txt);
		$txt=str_replace("k","",$txt);
		$txt=str_replace("l","",$txt);
		$txt=str_replace("m","",$txt);
		$txt=str_replace("n","",$txt);
		$txt=str_replace("o","",$txt);
		$txt=str_replace("p","",$txt);
		$txt=str_replace("q","",$txt);
		$txt=str_replace("r","",$txt);
		$txt=str_replace("s","",$txt);
		$txt=str_replace("t","",$txt);
		$txt=str_replace("u","",$txt);
		$txt=str_replace("v","",$txt);
		$txt=str_replace("w","",$txt);
		$txt=str_replace("x","",$txt);
		$txt=str_replace("y","",$txt);
		$txt=str_replace("z","",$txt);
		$txt=str_replace("_","",$txt);
		$txt=str_replace("=","",$txt);
		//$txt=str_replace("-","",$txt);
		$txt=str_replace("+","",$txt);
		$txt=str_replace("(","",$txt);
		$txt=str_replace(")","",$txt);
		$txt=str_replace(".","",$txt);
		$txt=str_replace(",","",$txt);
		$txt=str_replace("$","",$txt);
		$txt=str_replace("#","",$txt);
		$txt=str_replace("&","",$txt);
		$txt=str_replace("%","",$txt);
		$txt=str_replace("@","",$txt);
		$txt=str_replace("!","",$txt);
		$txt=str_replace("/","",$txt);
			
		return $txt;
	}
	function mrr_select_preplan_driver_by_id($load_id,$driver_id=0)
    {
        global $data_preplan_drivers;
        if(!isset($data_preplan_drivers))
        {
             $sql = "
                select *
                
                from drivers
                where active >0
                    and deleted <= 0                    
        
                order by name_driver_last, name_driver_first
            ";
             $data_preplan_drivers = simple_query($sql);
        }
         mysqli_data_seek($data_preplan_drivers,0);
        
         $tab="";
         $tab.="<select name='preplan_driver_".$load_id."_val' id='preplan_driver_".$load_id."_val' onChange='mrr_auto_update_preplan_driver(".$load_id.");' style='width:50px;'>";
         $tab.="<option value='0'".($driver_id=="" ? " selected" : "")."></option>";
         
         while($row__preplan_drivers = mysqli_fetch_array($data_preplan_drivers))
         {
              $tab.="
                <option value='".$row__preplan_drivers["id"]."'".($driver_id==$row__preplan_drivers["id"] ? " selected" : "").">
                    ".$row__preplan_drivers["name_driver_first"]." ".$row__preplan_drivers["name_driver_last"]."
                </option>
                ";
         }     
     
         $tab.="</select>";
     
         return $tab;
    }
	
	function mrr_select_preplan_marker($load_id,$marker="")
	{
		$tab="";
		
		$tab.="<select name='preplan_marker_".$load_id."_val' id='preplan_marker_".$load_id."_val' onChange='mrr_auto_update_preplan_marker(".$load_id.");'>";
		$tab.="<option value=''".($marker=="" ? " selected" : "")."></option>";
         
         $tab.="<option value='D'".($marker=="D" ? " selected" : "").">D</option>";
         $tab.="<option value='P'".($marker=="P" ? " selected" : "").">P</option>";
         $tab.="<option value='R'".($marker=="R" ? " selected" : "").">R</option>";
		/*
		$tab.="<option value='A'".($marker=="A" ? " selected" : "").">A</option>";
		$tab.="<option value='B'".($marker=="B" ? " selected" : "").">B</option>";
		$tab.="<option value='C'".($marker=="C" ? " selected" : "").">C</option>";
		$tab.="<option value='D'".($marker=="D" ? " selected" : "").">D</option>";
		$tab.="<option value='E'".($marker=="E" ? " selected" : "").">E</option>";
		$tab.="<option value='F'".($marker=="F" ? " selected" : "").">F</option>";
		$tab.="<option value='G'".($marker=="G" ? " selected" : "").">G</option>";
		$tab.="<option value='H'".($marker=="H" ? " selected" : "").">H</option>";
		$tab.="<option value='I'".($marker=="I" ? " selected" : "").">I</option>";
		$tab.="<option value='J'".($marker=="J" ? " selected" : "").">J</option>";
		$tab.="<option value='K'".($marker=="K" ? " selected" : "").">K</option>";
		$tab.="<option value='L'".($marker=="L" ? " selected" : "").">L</option>";
		$tab.="<option value='M'".($marker=="M" ? " selected" : "").">M</option>";
		$tab.="<option value='N'".($marker=="N" ? " selected" : "").">N</option>";
		$tab.="<option value='O'".($marker=="O" ? " selected" : "").">O</option>";
		$tab.="<option value='P'".($marker=="P" ? " selected" : "").">P</option>";
		$tab.="<option value='Q'".($marker=="Q" ? " selected" : "").">Q</option>";
		$tab.="<option value='R'".($marker=="R" ? " selected" : "").">R</option>";
		$tab.="<option value='S'".($marker=="S" ? " selected" : "").">S</option>";
		$tab.="<option value='T'".($marker=="T" ? " selected" : "").">T</option>";
		$tab.="<option value='U'".($marker=="U" ? " selected" : "").">U</option>";
		$tab.="<option value='V'".($marker=="V" ? " selected" : "").">V</option>";
		$tab.="<option value='W'".($marker=="W" ? " selected" : "").">W</option>";
		$tab.="<option value='X'".($marker=="X" ? " selected" : "").">X</option>";
		$tab.="<option value='Y'".($marker=="Y" ? " selected" : "").">Y</option>";
		$tab.="<option value='Z'".($marker=="Z" ? " selected" : "").">Z</option>";
		*/
		
		$tab.="</select>";
		
		return $tab;	
	}
	
	
	function mrr_get_coordinates($address)
     {
          $address = urlencode(trim($address));
          $url = "maps.google.com/maps/api/geocode/json?sensor=false&address=".$address."";
          $response = file_get_contents($url);
          $json = json_decode($response,true);
         
          $res['lat'] = $json['results'][0]['geometry']['location']['lat'];
          $res['long']= $json['results'][0]['geometry']['location']['lng'];
      
          return $res;
     }
     function mrr_get_coord_addr($lat,$long)
     {
          $url = "//maps.googleapis.com/maps/api/geocode/json?sensor=false&latlng=".trim($lat).",".trim($long)."";
          $response = file_get_contents($url);
          $json = json_decode($response,true);
      	
      	$res['numb'] = "";
          $res['addr'] = "";
          $res['city'] = "";
          $res['state']= "";
          $res['zip']  = "";
          $res['cnty'] = "";
          $res['usa']  = "";
          
          //echo "<br>ARRAY:<br>==============<br>";
          
         	for($i=0;$i < count($json['results']); $i++)
         	{
         		if($i==0)
         		{
         			for($j=0;$j < count($json['results'][$i]['address_components']); $j++)
         			{
         				$type=trim($json['results'][$i]['address_components'][$j]['types'][0]);
         				$val=trim($json['results'][$i]['address_components'][$j]['short_name']);
         				
         				//echo "<br>".$i."-".$j.". <b>".$type."</b> = ".$val.".";     
         				
         				if($type=="street_number")				$res['numb']=$val;
         				if($type=="route")						$res['addr']=$val; 
         				if($type=="locality")					$res['city']=$val; 
         				if($type=="administrative_area_level_1")	$res['state']=$val; 
         				if($type=="postal_code")					$res['zip']=$val; 
         				if($type=="administrative_area_level_2")	$res['cnty']=$val;
         				if($type=="country")					$res['usa']=$val;  			
         				
         				//var_dump($json['results'][$i]['address_components'][$j]);
         				//echo "<br>........................";
         			}
         			     			
         			
         			//echo "<br>---------------------------<br>";
         		}
     	}
                   
          //echo "<br>==============<br>".count($json['results'])." Results found.<br>";
          
          return $res;
     }
     
     function mrr_micro_miler_leg($zip1,$zip2,$hub_run=0)
     {
     	$miles=0;
     	if(trim($zip1)==trim($zip2))		return $miles;	
     	     	
     	$pcm = new COM("PCMServer.PCMServer") or die ("connection create fail");
     	
     	$stop_minutes=0;
     	     	
     	try {
			//$trip = $pcm->NewTrip("NA");			
			
			$miles = $pcm->CalcDistance3(trim($zip1), trim($zip2), 0, $stop_minutes) / 10;			
		} 
		catch (Exception $e) 
		{
			$miles = 0;
		}
     	
     	return $miles;	
     }
     
     
     function mrr_update_trailer_drops_for_trailer($date_from,$date_to,$id=0,$cust_id=0)
{
	$rep="";
	
	$sql="
		select trailers_dropped.*,
			customers.name_company,
			trailers.trailer_name
		
		from trailers_dropped
			left join customers on customers.id = trailers_dropped.customer_id
			left join trailers on trailers.id = trailers_dropped.trailer_id
		where trailers_dropped.deleted <= 0
			".($id > 0 ? "and trailers_dropped.trailer_id = '".sql_friendly($id)."'" : "")."
			".($cust_id > 0 ? "and trailers_dropped.customer_id = '".sql_friendly($cust_id)."'" : "")."			
			and (
				(
					trailers_dropped.linedate >= '".date("Y-m-d", strtotime($date_from))." 00:00:00' 
					and trailers_dropped.linedate <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
					
					and trailers_dropped.linedate_completed >= '".date("Y-m-d", strtotime($date_from))." 00:00:00' 
					and trailers_dropped.linedate_completed <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
				)
				or
				(
					trailers_dropped.linedate <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
					
					and trailers_dropped.linedate_completed >= '".date("Y-m-d", strtotime($date_from))." 00:00:00' 
					and trailers_dropped.linedate_completed <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
				)
				or
				(
					trailers_dropped.linedate >= '".date("Y-m-d", strtotime($date_from))." 00:00:00' 
					and trailers_dropped.linedate <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
					
					and (trailers_dropped.linedate_completed >= '".date("Y-m-d", strtotime($date_from))." 00:00:00' or trailers_dropped.linedate_completed='0000-00-00 00:00:00')
				)
				or
				(
					trailers_dropped.linedate <= '".date("Y-m-d", strtotime($date_to))." 23:59:59'
					
					and (trailers_dropped.linedate_completed >= '".date("Y-m-d", strtotime($date_from))." 00:00:00' or trailers_dropped.linedate_completed='0000-00-00 00:00:00')
				)
			)
		order by trailers_dropped.trailer_id, trailers_dropped.linedate
	";
	$data = simple_query($sql);
	
	$skip_days=0;	
	$secs_to_days=(60 * 60 * 24);
	
	$force_start_date=strtotime($date_from." 00:00:00");
	$force_end_date=strtotime($date_to." 23:59:59");
	
	$max_days=(int) ( ($force_end_date/$secs_to_days) - ($force_start_date/$secs_to_days));
	$max_days+=1;		//we count the day we started for the 1st day so that Feb is 28 days, etc.
	
	$rep.="
		<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1600px;text-align:left'>
		<tr>
			<td nowrap><b>Trailer Name</b></td>
			<td><b>Location</b></td>
			<td><b>Customer</b></td>
			<td nowrap><b>Date Dropped</b></td>
			<td><b>Notes</b></td>
			<td><b>Status</b></td>
			<td><b>Completed</b></td>
			<td><b>Updated</b></td>
			<td align='right' nowrap><b>Bill Days</b></td>
			<td align='right' nowrap><b>Total Days</b></td>
		</tr>
	";
	while($row = mysqli_fetch_array($data)) 
	{
		$days=0;
		$totdays=0;
		   		
    		//get start date...
    		$is_started=1;
		$linedate=strtotime(date("m/d/Y",strtotime($row['linedate']))." 00:00:00");
		if($row['linedate']=="0000-00-00 00:00:00")	{	$linedate=$force_start_date;		$is_started=0;	}							//not set, so default to beginning of period	
		if($linedate < $force_start_date)			{	$linedate=$force_start_date;		$is_started=0;	}							//set but out of range...use start date.
		
		//get end date...	
		$is_completed=0;
		$linedate_completed=0;
		if($row['linedate_completed']=="0000-00-00 00:00:00")		$linedate_completed=$force_end_date;								//not set or completed, so use max range.
		if($row['linedate_completed']!="0000-00-00 00:00:00")	{	$linedate_completed=strtotime(date("m/d/Y",strtotime($row['linedate_completed']))." 23:59:59");	$is_completed=1;	}
		if($linedate_completed > $force_end_date)			{	$linedate_completed=$force_end_date;		$is_completed=0;	}		//set but out of range...use end date (max range).
		
		//now calculate days.
		$arg1=$linedate_completed;
          $arg2=$linedate;  
          
           $days=(int) ( ($arg1/$secs_to_days) - ($arg2/$secs_to_days));	
          if($linedate==$force_start_date)	$days+=1;					//include the start date...started out of range.
		if($days > $max_days)			$days=$max_days;			//only use the max fro mthe range.  Ex: 28 days in Feb 2017
          
          //calculate the total days just to show them.
          $arg3=strtotime(date("m/d/Y",strtotime($row['linedate']))." 00:00:00"); 
          $arg4=time(); 
          if($row['linedate_completed']!="0000-00-00 00:00:00")		$arg4=strtotime(date("m/d/Y",strtotime($row['linedate_completed']))." 00:00:00");  
                    
          $totdays=(int) ( ($arg4/$secs_to_days) - ($arg3/$secs_to_days));	
          
          $updated_complete_date="";
          if($row['linedate_completed']!="0000-00-00 00:00:00" && ($days > 3 || $totdays > 3))
          {
          	//look for another drop after this one.
          	$sql2="
          		select trailers_dropped.*         		
          		from trailers_dropped
          		where trailers_dropped.deleted <= 0
          			and trailers_dropped.trailer_id = '".sql_friendly($row['trailer_id'])."'
          			and trailers_dropped.id!='".$row['id']."'
          			and trailers_dropped.linedate >= '".$row['linedate']."' 
          			and trailers_dropped.linedate < NOW() 
          			and trailers_dropped.linedate_completed!='0000-00-00 00:00:00'
          		order by trailers_dropped.linedate asc,trailers_dropped.id asc 
          	";
          	$data2 = simple_query($sql2);
          	if($row2 = mysqli_fetch_array($data2)) 
          	{
          		$updated_complete_date=$row2['linedate'];	
          	}
          	//check for if this trailer was used by dispatch sooner (or instead of another trailer drop).
          	$sql2="
          		select trucks_log.*         		
          		from trucks_log
          		where trucks_log.deleted <= 0
          			and trucks_log.trailer_id = '".sql_friendly($row['trailer_id'])."'
          			and trucks_log.linedate_pickup_eta >= '".$row['linedate']."' 
          			".($updated_complete_date!="" ? "and trucks_log.linedate_pickup_eta < '".$updated_complete_date."' " : "")."
          			and trucks_log.linedate_pickup_eta < NOW() 
          			and trucks_log.dispatch_completed>0
          		order by trucks_log.linedate_pickup_eta asc,trucks_log.id asc 
          	";
          	$data2 = simple_query($sql2);
          	if($row2 = mysqli_fetch_array($data2)) 
          	{
          		$updated_complete_date=$row2['linedate_pickup_eta'];	
          	}
          	if($updated_complete_date!="")
          	{
          		$sqlu="
          		update trailers_dropped set
          			linedate_completed='".$updated_complete_date."' 
          		where id='".$row['id']."'
          		";
          		simple_query($sqlu);	
          	}
          }
          	
		
		if($days > $skip_days)
		{				
     		$days_tot+=$days;
     		$totdays_tot+=$totdays;
     		
     		$complete_date="";
     		if($row['linedate_completed']!="0000-00-00 00:00:00" && $is_completed > 0)
     		{
     			$complete_date="".date("m/d/Y", strtotime($row['linedate_completed']))."";
     		}
     		elseif($row['linedate_completed']!="0000-00-00 00:00:00")
     		{
     			$complete_date="<span style='color:#CC0000;'>".date("m/d/Y", strtotime($row['linedate_completed']))."</span>";	
     		}
     		
     		$rep.="
     			<tr>
     				<td><a href='trailer_drop.php?id=$row[id]' target='view_drop_$row[id]'>$row[trailer_name]</a></td>
     				<td>$row[location_city], $row[location_state] $row[location_zip]</td>
     				<td>".trim($row['name_company'])."</td>
     				<td>".($is_started > 0 ? "".date("m/d/Y", strtotime($row['linedate']))."" : "<span style='color:#CC0000;'>".date("m/d/Y", strtotime($row['linedate']))."</span>")."</td>
     				<td>$row[notes]</td>
     				<td>".($is_completed > 0 ? 'Completed' : '')."</td>
     				<td>".$complete_date."</td>
     				<td>".($updated_complete_date!="" ? "".date("m/d/Y",strtotime($updated_complete_date))."" : "")."</td>
     				<td align='right'>".$days."</td>
     				<td align='right'>".$totdays."</td>
     			</tr>
     		";		//<span style='color:#".($days > 7 ? "CC0000" : "000000").";'>".$days."</span>
     		$days_cntr++;     		
		}
		$last_trailer_id=$row['trailer_id'];
		
	}
	$rep.="
			<tr>
				<td><b>".$days_cntr."</b></td>
				<td><b>Total</b></td>
				<td colspan='5'><i>Total is Days dropped within ".date("M j, Y", strtotime($date_from))." - ".date("M j, Y", strtotime($date_to))." Date Range.</i></td>
				<td align='right'><b>".$days_tot."</b></td>
				<td align='right'><b>".$totdays_tot."</b></td>
			</tr>
		";	// Max days filtered ".$max_days.".
	$rep.="</table>";
	return $rep;	
}

function mrr_repair_maint_request_schedule_dates($dater="")
{
	$rep="";
	$cntr=0;
	
	if(trim($dater)=="")		$dater="01/01/2010";
	
	$rep.="<table width='100%' cellpadding='1' cellspacing='1' border='0'>";
	$rep.="
		<tr>
			<td valign='top'><b>Urgent</b></td>
			<td valign='top'><b>MaintID</b></td>
			<td valign='top'><b>Added</b></td>
			<td valign='top'><b>User</b></td>
			<td valign='top'><b>Type</b></td>
			<td valign='top'><b>EquipName</b></td>				
			<td valign='top'><b>Scheduled</b></td>
			<td valign='top'><b>Odometer</b></td>
			<td valign='top'><b>Completed</b></td>
			<td valign='top'><b>Description</b></td>
			<td valign='top'><b>Status</b></td>
		</tr>
	";
	$sql="
		select maint_requests.*
		from maint_requests
		where maint_requests.deleted<=0 
			and maint_requests.linedate_scheduled <='".date("Y-m-d", strtotime($dater))." 00:00:00'
			and maint_requests.linedate_scheduled != '0000-00-00 00:00:00' 
		order by maint_requests.linedate_added asc,
			maint_requests.id desc		
	";
	$data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {		
		$status="";
		if(date("m/d/Y",strtotime($row['linedate_scheduled'])) == "12/31/1969")
		{
			$sqlu="update maint_requests set linedate_scheduled='".date("m/d/Y",strtotime($row['linedate_added']))." 00:00:00' where id='".$row['id']."'";
			simple_query($sqlu);	
			
			$status="Updated";	
		}
		
		$rep.="
			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
				<td valign='top'>".($row['urgent'] > 0 ? "<span style='color:#CC0000;'><b>!!!</b></span>" : "" )."</td>
				<td valign='top'>".$row['id']."</td>
				<td valign='top' nowrap>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
				<td valign='top'>".$row['user_id']."</td>
				<td valign='top'>".($row['equip_type']==58 ? "Truck" : "Trailer")."</td>
				<td valign='top'>".identify_truck_trailer(($row['equip_type']==58 ? "truck" : "trailer") , $row['ref_id'],1)."</td>				
				<td valign='top'>".date("m/d/Y",strtotime($row['linedate_scheduled']))."</td>
				<td valign='top'>".$row['odometer_reading']."</td>
				<td valign='top'>".($row['linedate_completed']!="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($row['linedate_completed'])) : "")."</td>
				<td valign='top'>".$row['maint_desc']."</td>
				<td valign='top'><span style='color:#CC0000;'><b>".$status."</b></span></td>
			</tr>	
		";
		$cntr++;    	
		
     }
     $rep.="</table><br><b>".$cntr." Maint Request Found and corrected.</b>";
     
     return $rep;
}

function mrr_add_mr_unit_locations($maint_id,$truck_id,$trailer_id,$local="")
{
	$local=trim($local);
	$sql="
		insert into mr_unit_locations
			(id,linedate_added,maint_id,truck_id,trailer_id,mr_location,user_id)
		values 
			(NULL, NOW(), '".sql_friendly($maint_id)."', '".sql_friendly($truck_id)."', '".sql_friendly($trailer_id)."', '".sql_friendly($local)."', '".sql_friendly($_SESSION['user_id'])."')
	";
	simple_query($sql);
}

function mrr_get_mr_unit_locations($maint_id=0,$mode=0)
{
	$cntr=0;
	
	$mark_one=date("Y-m-d",time())." 08:00:00";
	$mark_two=date("Y-m-d",time())." 06:00:00";
	
	$tab="<table cellpadding='0' cellspacing='0' border='0' width='100%' class='tablesorter'>";
	$tab.="
		<thead>
		<tr>
			".($mode == 0 ? "<th><b>#</b></th>" : "")."
			<th><b>Added</b></th>
			<th><b>User</b></th>	
			".($mode == 0 ? "<th><b>Unit</b></th>" : "")."
			<th><b>Quick Note Or Location</b></th>	
			<th><b>MSG</b></th>	
			<th><b>Delete</b></th>		
		</tr>";
	/*
	$tab.="
		<tr>
			<th colspan='".($mode == 0 ? "7" : "5")."' align='center'>
			    <b>Snooze Options:</b>
			    <span onClick='mrr_mr_local_snooze(".$maint_id.",7);' style='color:#0000CC; cursor:pointer;'>7 Days</span> - 
			    <span onClick='mrr_mr_local_snooze(".$maint_id.",14);' style='color:#0000CC; cursor:pointer;'>14 Days</span> - 
			    <span onClick='mrr_mr_local_snooze(".$maint_id.",21);' style='color:#0000CC; cursor:pointer;'>21 Days</span> -
			    <span onClick='mrr_mr_local_snooze(".$maint_id.",28);' style='color:#0000CC; cursor:pointer;'>28 Days</span> - 
			    <span onClick='mrr_mr_local_snooze(".$maint_id.",0);' style='color:#0000CC; cursor:pointer;'>Clear</span>
			     (Delay/Ignore feature)
			 </th>
		</tr>";
	*/
	$tab.="</thead>
		<tbody>
	";	
	$sql = "
		select mr_unit_locations.id,
		    mr_unit_locations.linedate_added,
		    mr_unit_locations.truck_id,
		    mr_unit_locations.trailer_id,
		    mr_unit_locations.mr_location,
			(select maint_requests.equip_type from maint_requests where maint_requests.id=mr_unit_locations.maint_id) as etyper,
			(select maint_requests.ref_id from maint_requests where maint_requests.id=mr_unit_locations.maint_id) as refid,
			(select trucks.name_truck from trucks where trucks.id=mr_unit_locations.truck_id) as truck_namer,
			(select trailers.trailer_name from trailers where trailers.id=mr_unit_locations.trailer_id) as trailer_namer,
			(select users.username from users where users.id=mr_unit_locations.user_id) as user_namer
		from mr_unit_locations
		where mr_unit_locations.deleted=0 
		    and mr_unit_locations.maint_id='".sql_friendly($maint_id)."'
		    
		 union
		 
		 select notes_main.id,
		    notes_main.linedate_added,
		    99,
		    0,
		    notes_main.note,
			0,
			0,
			'',
			'',
			(select users.username from users where users.id=notes_main.created_by_user_id) as user_namer	 
		 from notes_main
		 where notes_main.deleted=0 
		    and notes_main.note_type_id=10
		    and notes_main.xref_id='".sql_friendly($maint_id)."'	
		    and notes_main.note not like '%Maint Request Prompt Noticed by%'	    
		    
		order by linedate_added desc, id desc
	";
	$data = simple_query($sql);			
	while($row = mysqli_fetch_array($data))
	{
		$sqlu="";
		if($row['truck_id']==0 && $row['trailer_id']==0)
		{	//truck and trailer not set, so let us fix that here.  {Probably made before the unit was selected...}
			if($row['etyper']==1 || $row['etyper']==58)		$row['truck_id']=$row['refid'];
			if($row['etyper']==2 || $row['etyper']==59)		$row['trailer_id']=$row['refid'];
			
			$sqlu="update mr_unit_locations set truck_id='".sql_friendly($row['truck_id'])."',trailer_id='".sql_friendly($row['trailer_id'])."' where id='".sql_friendly($row['id'])."' and maint_id='".sql_friendly($maint_id)."'";
			simple_query($sqlu);
		}
		
		//
        //$mark_two
		$tested=0;
		if($row['linedate_added'] < $mark_one)
        {
             $tested=1;
             if($row['linedate_added'] > $mark_two)
             {
                  $tested=2;
             }
        }
		$span1="";
		$span2="";
		if($cntr>0)     $tested=0;
		
		if($tested==1)
        {
             $span1="<span style='color:#cc0000;'><b>";
             $span2="</b></span>";         
        }
		elseif($tested==2)
        {
             $span1="<span style='color:#00cc00;'><b>";
             $span2="</b></span>";
        }
		
		$msg_time="".date("m/d/Y H:i",strtotime($row['linedate_added']))."";
		$msg_user="".$row['user_namer']."";
        $msg_type="";
        $msg_unit="";
        if($row['truck_id'] > 0)   {   $msg_type="Truck";       $msg_unit="".trim($row['truck_namer'])."";  }
        if($row['trailer_id'] > 0) {   $msg_type="Trailer";     $msg_unit="".trim($row['trailer_namer'])."";  }
        
        $msg_note="".trim($row['mr_location'])."";
        $msg_id=$maint_id;
						
		$tab.="
			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";' class='mrr_unit_log_row_$row[id]'>
				".($mode == 0 ? "<td valign='top'>".$span1."".($cntr+1)."".$span2."</td>" : "")."
				<td valign='top'>".$span1."".date("m/d/Y H:i",strtotime($row['linedate_added']))."".$span2."</td>
				<td valign='top'>".$span1."".$row['user_namer']."".$span2."</td>
				".($mode == 0 ? "<td valign='top'>".$span1."".($row['truck_id'] > 0   ? "<a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".trim($row['truck_namer'])."</a>" : "")."".($row['trailer_id'] > 0 ? "<a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".trim($row['trailer_namer'])."</a>" : "")."".$span2."</td>" : "")."
				<td valign='top'>".$span1."".trim($row['mr_location'])."".$span2."</td>
				
				<td>".$span1."<span onclick='mrr_send_mr_msg_note(".$msg_id.",\"".$msg_time."\",\"".$msg_user."\",\"".$msg_type."\",\"".$msg_unit."\",\"".$msg_note."\");' style='color:#0000cc; cursor:pointer;'>Send</span>".$span2."</td>
				
				<td>".$span1."".($row['truck_id']==99 ? "Main<br>Note" : "<a href='javascript:void(0)' onclick='delete_unit_log_history($row[id]);' class='mrr_delete_access'><img src='images/delete_sm.gif' alt='Delete Quick note and loc' title='Delete this Quick note and loc' style='border:0;'></a>")."".$span2."</td>
			</tr>
			
     	";	//<tr><td valign='top' colspan='5'>".$sqlu."</td></tr>
     	$cntr++;
	}
	$tab.="</tbody></table>";
	
	if($mode > 0 && $cntr==0)	$tab="";	
	
	return $tab;
}


function mrr_decode_file_section($id=0)
{
	$section="";
	if($id==1)		$section="Driver";
	if($id==3)		$section="Truck";
	if($id==2)		$section="Trailer";
	if($id==5)		$section="Customer";
	if($id==6)		$section="Dispatch";
	if($id==8)		$section="Load";
	if($id==10)		$section="Maint";
	if($id==11)		$section="Accident";
	return $section;	
}

function mrr_find_unit_breakdown_maint_request($equip_type=0,$equip_id=0)
{
	$rep="";
	$req_id=0;
	if($equip_type > 0 && $equip_id > 0)
	{		
		$sql = "
			select maint_requests.*,
				(select users.username from users where users.id=maint_requests.user_id) as user_namer
			from maint_requests
			where maint_requests.deleted <=0
				and maint_requests.unit_breakdown > 0
				and maint_requests.linedate_completed='0000-00-00 00:00:00'
				and maint_requests.equip_type='".sql_friendly($equip_type)."'
				and maint_requests.ref_id='".sql_friendly($equip_id)."'
			order by maint_requests.urgent desc,maint_requests.linedate_scheduled desc, maint_requests.id desc
		";
		$data = simple_query($sql);			
		if($row = mysqli_fetch_array($data))
		{
			$req_id=$row['id'];
			$linedate=date("m/d/Y H:i",strtotime($row['linedate_added']));
			$note=trim($row['maint_desc']);
			$user=trim($row['user_namer']);
			
			//check for a later note added to the request.
			$sql2 = "
     			select notes_main.*,
     				(select users.username from users where users.id=notes_main.created_by_user_id) as user_namer
     			from notes_main
     			where notes_main.deleted ='0'
     				and notes_main.note_type_id=10
     				and notes_main.xref_id='".sql_friendly($req_id)."'
     			order by notes_main.linedate_added desc, notes_main.id desc
     		";
     		$data2 = simple_query($sql2);			
     		if($row2 = mysqli_fetch_array($data2))
			{
				$linedate=date("m/d/Y H:i",strtotime($row2['linedate_added']));
				$note=trim($row2['note']);
				$user=trim($row2['user_namer']);
			}
						
			$rep="
				<div style='background-color:#ffffff; color:#CC0000; padding:5px; border:1px solid #cc0000; width:97%; margin-left:5px;'>
					<div style='float:right; margin-right:10px;'><b>".$user." ".$linedate."</b></div>
					<a href='maint.php?id=".$req_id."' target='_blank'><b>Unit Breakdown</b></a>:
					<br>
					<textarea rows='2' wrap='virtual' readonly style='background-color:#ffffff; color:#000000; width:98%;'>".$note."</textarea>					
				</div>
				<br>
			";
		}	
	}	
	if($req_id==0)		$rep="";
	
	return $rep;	
}

function mrr_pull_owner_operator_setup($driver_id=0)
{
	/*
	
							if($test_driver_id==562)		$use_cat_id=26;		//Abell
							if($test_driver_id==536)		$use_cat_id=27;		//Swafford
							if($test_driver_id==580)		$use_cat_id=28;		//Monick
							if($test_driver_id==575)		$use_cat_id=29;		//Bracken
							if($test_driver_id==598)		$use_cat_id=30;		//Milo Williams
							if($test_driver_id==599)		$use_cat_id=31;		//Ricky Hayes
							if($test_driver_id==520)		$use_cat_id=32;		//Leonard Giles
							if($test_driver_id==600)		$use_cat_id=33;		//Othman Al-Hajiri 	
	*/
		
	$owner_operator_cat=0;	
	
	if($driver_id > 0)
	{
		$sql="
			select cat_id				
			from owner_operator_setup
			where deleted<=0 and cat_id > 0 and driver_id='".(int) $driver_id."'
			order by id asc
		";	
		$data = simple_query($sql);	
		if($row = mysqli_fetch_array($data)) 
		{
			$owner_operator_cat=$row['cat_id'];
		}
	}	
	
	return $owner_operator_cat;
}
function mrr_clear_phone_number_extras($numbers)
{
	$numbers=trim($numbers);
	$numbers=str_replace('"',"",$numbers);		$numbers=str_replace("'","",$numbers);
	$numbers=str_replace("(","",$numbers);		$numbers=str_replace(")","",$numbers);
	$numbers=str_replace("-","",$numbers);		$numbers=str_replace(".","",$numbers);
	$numbers=str_replace("{","",$numbers);		$numbers=str_replace("}","",$numbers);
	$numbers=str_replace("[","",$numbers);		$numbers=str_replace("]","",$numbers);
	$numbers=str_replace("$","",$numbers);		$numbers=str_replace("%","",$numbers);
	$numbers=str_replace("+1","",$numbers);		
	$numbers=str_replace("_","",$numbers);
	$numbers=str_replace(";",",",$numbers);
	
	return $numbers;	
}

function mrr_fetch_last_PN_odometer_reading($truck_id)
{
	$reading=0;
	$truck_id=(int) $truck_id;
	
	if($truck_id > 0)
	{
		//$sql="select performx_odometer from ".mrr_find_log_database_name()."truck_tracking where truck_id='".$truck_id."' order by linedate_added desc limit 1";
		//$data = simple_query($sql);
		//if($row = mysqli_fetch_array($data))
		//{
		//	$reading=$row['performx_odometer'];
		//}
				
		$sql="select geotab_last_odometer_reading from trucks where id='".$truck_id."'";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$reading=$row['geotab_last_odometer_reading'];
		}				
	}		
	return $reading;
}
	
function mrr_pmi_fed_trucks_due_soon($days=0,$testing=0,$comp_vehicles=0,$display_only=0)
{
	$res['num']=0;
	$res['report']="";		
		
	global $defaultsarray;
	$days_counted=(int)$defaultsarray['truck_pm_date_days'];
	$feds_counted=(int)$defaultsarray['truck_fed_date_days'];
	
	$now_dater=date("ymd",time());
	$later_dater=date("ymd", strtotime("+".$days." days", time()));
	
	$cntr=0;
	$tab="";
	$tab2="";
	
	$tab.="<table cellpadding='0' cellspacing='0' border='0' width='800'>";
	$tab.="
		<tr>
			<td valign='top'><b>#</b></td>
			<td valign='top'><b>Truck</b></td>
			<td valign='top' align='right'><b>Oil Soon</b></td>
			<td valign='top' align='right'><b>Oil Overdue</b></td>
			<td valign='top' align='right'><b>Valve Soon</b></td>
			<td valign='top' align='right'><b>Valve Overdue</b></td>
			<td valign='top' align='right'><b>Drain Soon</b></td>
			<td valign='top' align='right'><b>Drain Overdue</b></td>
			<td valign='top' align='right'><b>PM Soon<br>(".(int) ($days_counted / 30)." months)</b></td>
			<td valign='top' align='right'><b>PM Overdue</b></td>
			<td valign='top' align='right'><b>FED Soon<br>(".(int) ($feds_counted / 30)." months)</b></td>
			<td valign='top' align='right'><b>FED Overdue</b></td>			
		</tr>
	";
	//return $res;	
	
	$sql="
     	select *
     	from trucks
     	where deleted<=0 and active>0 
     		and (use_pm_oil_report > 0 or use_pm_oil_report_valve > 0)
     		".($comp_vehicles > 0 ? " and company_admin_vehicle='".$comp_vehicles."'" : "")."
     	order by name_truck asc
     ";
     $data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {
     	$pm_oil_chg1="";
		$pm_oil_chg2="";
		$pm_inspect1="";
		$pm_inspect2="";
		$fed_inspect1="";
		$fed_inspect2="";		
		$valve_chg1="";
		$valve_chg2="";
		$drain_chg1="";
		$drain_chg2="";
     
          //Fed Inspection		
		$fed_inspect="N/A";
		if($row['fd_inspection_date']!="0000-00-00 00:00:00")		$fed_inspect="".date("m/d/Y",strtotime($row['fd_inspection_date']))."";
		
		$fed_color="000000";
		
		if($row['use_pm_oil_report'] > 0)
		{
     		//if($row['company_owned'] == 0)	
     		//{
     		//	$fed_color="999999";
     		//}
     		//else
     		//{
     			if(date("Y-m-d",strtotime("+".$feds_counted." days",strtotime($row['fd_inspection_date']))) <	date("Y-m-d",time()) )	
     			{
     				$fed_color="CC0000";
     				$fed_inspect2="<span title='Last FED Inspection was done ".$fed_inspect.".' style='color:#".$fed_color.";'>".$fed_inspect."</span>";
     			}
     			elseif(date("Y-m-d",strtotime("+".($feds_counted - $days)." days",strtotime($row['fd_inspection_date']))) <	date("Y-m-d",time()) )	
     			{
     				$fed_color="FFCC00";
     				$fed_color="8b4513";
     				$fed_inspect1="<span title='Last FED Inspection was done ".$fed_inspect.".' style='color:#".$fed_color.";'>".$fed_inspect."</span>";
     			}
     			else
     			{
     				//$fed_color="00CC00";
     				//$fed_inspect1="<span title='Last FED Inspection was done ".$fed_inspect.".' style='color:#".$fed_color.";'>".$fed_inspect."</span>";
     			}
     		//}  	
     		//echo "<span title='Last FED Inspection was done ".$fed_inspect.".' style='color:#".$fed_color.";'>".$fed_inspect."</span>";
     	}     	
     	
     	//PM Inspection...ties into oil changes and new PM Mileage interval		
		$pm_inspect="N/A";
		if($row['pm_inspection_date']!="0000-00-00 00:00:00")		$pm_inspect="".date("m/d/Y",strtotime($row['pm_inspection_date']))."";
		
		$pm_odom=0;
		$pm_odom=mrr_fetch_last_PN_odometer_reading($row['id']);
		
		$pm_title_adder="";
		$pm_color="000000";
		
		if($row['use_pm_oil_report'] > 0)
		{
     		$pm_oil_chg1="";
			$pm_oil_chg2="";
     		
     		if($row['company_owned'] > 0 && 1==2)
     		{
     			$pm_color="999999";
     		}
     		else
     		{
     			if(date("Y-m-d",strtotime("+".$days_counted." days",strtotime($row['pm_inspection_date']))) <	date("Y-m-d",time()) )	
     			{
     				$pm_color="CC0000";
     				$pm_inspect2="<span title='Last PM Inspection was done ".$pm_inspect.".' style='color:#".$pm_color.";'>".$pm_inspect."</span>";
     			}
     			elseif(date("Y-m-d",strtotime("+".($days_counted - $days)." days",strtotime($row['pm_inspection_date']))) <	date("Y-m-d",time()) )	
     			{
     				$pm_color="FFCC00";
     				$pm_color="8b4513";
     				$pm_inspect1="<span title='Last PM Inspection was done ".$pm_inspect.".' style='color:#".$pm_color.";'>".$pm_inspect."</span>";
     			}
     			else
     			{
     				//$pm_color="00CC00";
     			}
     			
     			if( $row['pm_miles_interval'] > 0)
     			{
     				$pm_inspect1="";
     				$pm_inspect2="";
     				
     				$oil_diff=floatval($pm_odom) - (floatval($row['pm_miles_last_oil']) + floatval($row['pm_miles_interval']));	
     				$oil_diff=(int) $oil_diff * -1;
     				
     				$pm_inspect="N/A";
     				if($row['pm_miles_last_date']!="0000-00-00 00:00:00")		$pm_inspect="".date("m/d/Y",strtotime($row['pm_miles_last_date']))."";
     				
     				if($oil_diff < 0)
     				{
     					$pm_color="CC0000";
     					$pm_title_adder=" Was Due at ".($row['pm_miles_last_oil'] + $row['pm_miles_interval'])." Miles and is ".abs($oil_diff)." overdue.";
     					$pm_oil_chg2="<span title='Last PM Oil Change was done ".$pm_inspect.".".$pm_title_adder."' style='color:#".$pm_color.";'>".$pm_odom."</span>";
     				}
     				elseif($oil_diff >= 0 && $oil_diff <=2500 )
     				{
     					$pm_color="FFCC00";
     					$pm_color="8b4513";
     					$pm_title_adder=" Is coming Due at ".($row['pm_miles_last_oil'] + $row['pm_miles_interval'])." Miles -- ".abs($oil_diff)." left.";
     					$pm_oil_chg1="<span title='Last PM Oil Change was done ".$pm_inspect.".".$pm_title_adder."' style='color:#".$pm_color.";'>".$pm_odom."</span>";
     				}
     				else
     				{
     					//$pm_color="00CC00";
     					//$pm_title_adder=" Will be Due at ".($row['pm_miles_last_oil'] + $row['pm_miles_interval'])." Miles.";	
     				}
     			}
     			if($display_only > 0)
     			{
     				echo "<br>
     					".$row['name_truck']." -- <span title='Last PM Inspection was done ".$pm_inspect.".".$pm_title_adder."' style='color:#".$pm_color.";'>".$pm_odom."</span> 
     					Oil 1:".$pm_oil_chg1." Oil 2:".$pm_oil_chg2.".
     					<span style='color:#00cc00;'><i>Last Odom ".$row['pm_miles_last_oil']." + ".$row['pm_miles_interval']." Interval (".($row['pm_miles_last_oil'] + $row['pm_miles_interval']).") compared to ".$pm_odom." is result: ".$oil_diff.".</i></span>
     				";
     			}
     			$oil_diff=0;
     		}    		//
     			
     	}
     	
     	//new Valve Adjustment/Overhead section...
     	if($row['use_pm_oil_report_valve'] > 0)
     	{     		
     		$over_due=(int) trim(str_replace(",","",$defaultsarray['truck_valve_overhead_due']));
			$almost_due=(int) trim(str_replace(",","",$defaultsarray['truck_valve_overhead_soon']));
			$due_sooner_miles=$over_due - $almost_due;
     		
     		$valve_interval=$row['valve_miles_interval'];
     		if($valve_interval <= 0)		$valve_interval=$over_due;
     		
     		$warn_min=($valve_interval - $almost_due);
     		if($warn_min <= 0)			$warn_min=$valve_interval - ($over_due - $almost_due);
     		
     		if($valve_interval > 0)
			{
				$valve_chg1="";
				$valve_chg2="";
				
				if(($row['valve_miles_last'] + $valve_interval) <  $pm_odom)
				{
					$pm_color="CC0000";
					$pm_title_adder=" Was Due at ".($row['valve_miles_last'] + $valve_interval)." Miles and is ".($pm_odom - ($row['valve_miles_last'] + $valve_interval))." overdue.";
					$valve_chg2="<span title='Last Valve Adjustment was done ".$pm_inspect.".".$pm_title_adder."' style='color:#".$pm_color.";'>".$pm_odom."</span>";
				}
				elseif(($row['valve_miles_last'] + $valve_interval - $almost_due) <=$pm_odom)
				{	//($row['valve_miles_last'] + $valve_interval- $pm_odom) <=($over_due - $almost_due)												//($valve_interval - $warn_min)
					$pm_color="FFCC00";
					$pm_color="8b4513";
					$pm_title_adder=" Is coming Due at ".($row['valve_miles_last'] + $valve_interval)." Miles -- ".(($row['valve_miles_last'] + $valve_interval)- $pm_odom)." left.";
					$valve_chg1="<span title='Last Valve Adjustment was done ".$pm_inspect.".".$pm_title_adder."' style='color:#".$pm_color.";'>".$pm_odom."</span>";
				}
				else
				{
					//$pm_color="00CC00";
					//$pm_title_adder=" Will be Due at ".($row['valve_miles_last'] + $valve_interval)." Miles.";	
				}
			}
     	}
     		    	
     	if($pm_oil_chg1!="" || $pm_oil_chg2!="" || $valve_chg1!="" || $valve_chg2!="" || $drain_chg1!="" || $drain_chg2!="" || $pm_inspect1!="" || $pm_inspect2!="" || $fed_inspect1!="" || $fed_inspect2!="")
     	{
     		$tab.="
     			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
     				<td valign='top'>".($cntr+1)."</td>
     				<td valign='top'><a href='admin_trucks.php?id=".$row['id']."' target='_blank'>".trim($row['name_truck'])."</a></td>
     				<td valign='top' align='right'>".$pm_oil_chg1."</td>
     				<td valign='top' align='right'>".$pm_oil_chg2."</td>
     				<td valign='top' align='right'>".$valve_chg1."</td>
     				<td valign='top' align='right'>".$valve_chg2."</td>
     				<td valign='top' align='right'>".$drain_chg1."</td>
     				<td valign='top' align='right'>".$drain_chg2."</td>
     				<td valign='top' align='right'>".$pm_inspect1."</td>
     				<td valign='top' align='right'>".$pm_inspect2."</td>
     				<td valign='top' align='right'>".$fed_inspect1."</td>
     				<td valign='top' align='right'>".$fed_inspect2."</td>
     			</tr>
     		";
     		
     		$tab2.="<br>
     			".($cntr+1)." ".trim($row['name_truck'])." 
     			- Oil Change Soon: ".$pm_oil_chg1." Oil Change OVERDUE ".$pm_oil_chg2.", 
     			- Valve Adjustment Soon: ".$valve_chg1." Valve Adjustment OVERDUE ".$valve_chg2.", 
     			- Drain Separator Soon: ".$drain_chg1." Drain Separator OVERDUE ".$drain_chg2.", 
     			PMI Soon: ".$pm_inspect1." PMI OVERDUE - ".$pm_inspect2.", 
     			FED Soon: ".$fed_inspect1." FED OVERDUE - ".$fed_inspect2."
     		";     	
     		//if($row['id']==640 && $testing > 0 && $display_only > 0)
     		//{
     			$pm=0;	    				$fed=0;    			$oil=0;     			$valve=0;           $drain=0;
     			$pmtxt="";    				$fedtxt="";   		$oiltxt="";   			$valvetxt="";       $draintxt="";
     			
     			if($pm_inspect1!="")	{	$pm=1; 		$pmtxt=$pm_inspect1;	}		if($pm_inspect2!="")	{	$pm=2; 		$pmtxt=$pm_inspect2;	}
     			if($fed_inspect1!="")	{	$fed=1; 	$fedtxt=$fed_inspect1;	}		if($fed_inspect2!="")	{	$fed=2; 	$fedtxt=$fed_inspect2;	}
     			if($pm_oil_chg1!="")	{	$oil=1; 	$oiltxt=$pm_oil_chg1;	}		if($pm_oil_chg2!="")	{	$oil=2; 	$oiltxt=$pm_oil_chg2;	}
     			if($valve_chg1!="")		{	$valve=1; 	$valvetxt=$valve_chg1;	}		if($valve_chg2!="")		{	$valve=2; 	$valvetxt=$valve_chg2;	}
                if($drain_chg1!="")		{	$drain=1; 	$draintxt=$drain_chg1;	}		if($drain_chg2!="")		{	$drain=2; 	$draintxt=$drain_chg2;	}
                
     			$reported=mrr_auto_create_maint_request_for_pm_fed_oil_valve($row['id'],0,$pm,$fed,$oil,$valve,$pmtxt,$fedtxt,$oiltxt,$valvetxt,$drain,$draintxt);	
     			//echo $reported;
     		//}
     		$cntr++;
     	}
     	
     }
     $tab.="</table>";
          
     $sql="
     	select *
     	from last_report_capture
     	where report_url='/report_maint_requests.php' 
     		and report_query='current=1'
     ";
     $data=simple_query($sql);
     if($row=mysqli_fetch_array($data))
     {	
     	$report_snapshot="";
     	$excel_version="";
     	
     	$report_snapshot=trim($row['report_html']);
     	$excel_version=trim($row['excel_filename']);
     	
     	if($report_snapshot!="")		$tab.="<br><br><h3>".trim($row['report_title'])."</h3><br>".$report_snapshot."";	
     	
     	if($excel_version!="")		$tab.="<br><br><a href='".$excel_version."'>Click for Excel Version</a>";	     	
     }
     
     if($cntr > 0)
     {
		$from="system@conardtransportation.com";
		$from_name=trim($defaultsarray['company_name']);
		
		$subject="Truck Oil/Valve/Drain/PM/FED: ".$cntr." Coming Due or OVERDUE.";
		$msg1="This is a reminder to check on the following trucks for Oil/Valve/Drain/PM/FED inspections due: \r\n ".$tab2." \r\n";
		$msg2="<b>This is a reminder to check on the following trucks for Oil/Valve/Drain/PM/FED inspections due:</b> <br><br>".$tab."<br>";
				
		$res=mrr_find_all_admin_email_list(1,1);
		$cntrx=$res['num'];				
		//$arr=$res['ids'];
		$arr_email=$res['email'];
		$arr_names=$res['names'];
		//$arr_phone=$res['phone'];		
		for($i=0;$i < $cntrx; $i++)
		{
			$send_name=trim($arr_names[$i]).", ";
			$send_to=trim($arr_email[$i]);
						
			if($testing==0 && $send_to!="")
			{
				mrr_trucking_sendMail($send_to,'Truck Oil/Valve/PM/FED',$from,$from_name,'','',$subject, $send_name."".$msg1, $send_name."".$msg2);				
			}
		}
     	     	
     	if($testing > 0 && $display_only==0)
     	{
     		$send_to=$defaultsarray['special_email_monitor'];
     		$send_name="Lord Vader, ";
     		mrr_trucking_sendMail($send_to,'Truck Oil/Valve/PM/FED',$from,$from_name,'','',$subject, $send_name."".$msg1, $send_name."".$msg2);
     	}     	
     }     
     
     if($display_only > 0)
     {
     	echo "<br>".$cntr." Items found... <br>Report Contents:<br>".$tab."<br>".$tab2."";	//
     }
     
     $res['num']=$cntr;
     $res['report']=$tab;
	
	return $res;	
}

function mrr_pmi_fed_trailers_due_soon($days=0,$testing=0,$display_only=0)
{
	$res['num']=0;
	$res['report']="";	
	
	global $defaultsarray;
	$days_counted=(int)$defaultsarray['trailer_pmi_date_days'];
	$feds_counted=(int)$defaultsarray['trailer_fed_date_days'];
	
	$now_dater=date("ymd",time());
	$later_dater=date("ymd", strtotime("+".$days." days", time()));
	
	$cntr=0;
	$tab="";
	$tab2="";
	
	$tab.="<table cellpadding='0' cellspacing='0' border='0' width='600'>";
	$tab.="
		<tr>
			<td valign='top'><b>#</b></td>
			<td valign='top'><b>Trailer</b></td>
			<td valign='top' align='right'><b>PMI Soon</b></td>
			<td valign='top' align='right'><b>PMI Overdue</b></td>
			<td valign='top' align='right'><b>FED Soon</b></td>
			<td valign='top' align='right'><b>FED Overdue</b></td>			
		</tr>
	";
	
	$sql="
     	select *
     	from trailers
     	where deleted<=0 and active>0
     	order by trailer_name asc
     ";
     $data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {
     	$pmi_pending=0;
		$fed_pending=0;
		if($last_maint_inspect_id > 0)
		{
			$insres=mrr_form_trailer_inspection_list($last_maint_inspect_id);
			/*
			$insres['used']=0;
              	$insres['passed']=0;
              	$insres['created_by']="";
               $insres['updated_by']="";
               $insres['updated']="";
               $insres['created']="";
               $insres['inspection']=0;
               $insres['used_pmi']=0;		
               $insres['used_fed']=0;
			*/
			if($insres['used'] > 0 && $insres['used_pmi'] > 0)	$pmi_pending=1;	//PMI inspection is still pending					
			if($insres['used'] > 0 && $insres['used_fed'] > 0)	$fed_pending=1;	//FED inspection is still pending					
		}	
     	
     	
     	$pmi_displayer1="";
     	$pmi_displayer2="";
     	
		if($days_counted > 0 && $row['pmi_test_ignore'] ==0)
		{
			if($row['linedate_last_pmi']=="0000-00-00 00:00:00")
			{
				$pmi_displayer2="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",time())."' target='_blank'><span class='alert'><b>OVERDUE!</b></span></a>";
			}
			else
			{
				$next_run=date("m/d/Y", strtotime("+".$days_counted." days", strtotime($row['linedate_last_pmi'])));	
				$due_compare=date("ymd",strtotime($next_run));
				if((int) $due_compare <= (int) $now_dater)
				{								
					if($pmi_pending > 0)
					{
						$pmi_displayer2="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' title='Inspection Pending'><span style='color:yellow;background-color:#000000;'><b>".$next_run."</b></span></a>";	//  #ffc200;
					}
					else
					{
						$pmi_displayer2="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",strtotime($next_run))."' target='_blank'><span class='alert'><b>".$next_run."</b></span></a>";
					}
				}
				elseif((int) $due_compare > (int) $now_dater && (int) $due_compare <= (int) $later_dater)
				{								
					if($pmi_pending > 0)
					{
						$pmi_displayer1="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' title='Inspection Pending'><span style='color:yellow;background-color:#000000;'><b>".$next_run."</b></span></a>";	//  #ffc200;
					}
					else
					{
						$pmi_displayer1="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",strtotime($next_run))."' target='_blank'><span class='alert'><b>".$next_run."</b></span></a>";
					}
				}
				else
				{
					//$pmi_displayer="<span style='color:green;'><b>".$next_run."</b></span>";
				}
			}
		}
		//if($row['pmi_test_ignore'] > 0)	$pmi_displayer="<span style='color:green;'><b>N/A</b></span>";
		
		
     	$fed_displayer1="";
     	$fed_displayer2="";
     	
		if($feds_counted > 0 && $row['fed_test_ignore'] ==0)
		{
			if($row['linedate_last_fed']=="0000-00-00 00:00:00")
			{
				$fed_displayer2="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",time())."' target='_blank'><span class='alert'><b>OVERDUE!</b></span></a>";
			}
			else
			{
				$next_run=date("m/d/Y", strtotime("+".$feds_counted." days", strtotime($row['linedate_last_fed'])));	
				$due_compare=date("ymd",strtotime($next_run));
				if((int) $due_compare <= (int) $now_dater)
				{								
					if($fed_pending > 0)
					{
						$fed_displayer2="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' title='Inspection Pending'><span style='color:yellow;background-color:#000000;'><b>".$next_run."</b></span></a>";	//background-color:#000000;
					}
					else
					{
						$fed_displayer2="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",strtotime($next_run))."' target='_blank'><span class='alert'><b>".$next_run."</b></span></a>";
					}
				}
				elseif((int) $due_compare > (int) $now_dater && (int) $due_compare <= (int) $later_dater)
				{								
					if($fed_pending > 0)
					{
						$fed_displayer1="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' title='Inspection Pending'><span style='color:yellow;background-color:#000000;'><b>".$next_run."</b></span></a>";	//background-color:#000000;
					}
					else
					{
						$fed_displayer1="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",strtotime($next_run))."' target='_blank'><span class='alert'><b>".$next_run."</b></span></a>";
					}
				}
				else
				{
					//$fed_displayer="<span style='color:green;'><b>".$next_run."</b></span>";
				}
			}
		}
		//if($row['fed_test_ignore'] > 0)	$fed_displayer="<span style='color:green;'><b>N/A</b></span>";
					
		    	
     	if($pmi_displayer1!="" || $pmi_displayer2!="" || $fed_displayer1!="" || $fed_displayer2!="")
     	{
     		$tab.="
     			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
     				<td valign='top'>".($cntr+1)."</td>
     				<td valign='top'><a href='admin_trailers.php?id=".$row['id']."' target='_blank'>".trim($row['trailer_name'])."</a></td>
     				<td valign='top' align='right'>".$pmi_displayer1."</td>
     				<td valign='top' align='right'>".$pmi_displayer2."</td>
     				<td valign='top' align='right'>".$fed_displayer1."</td>
     				<td valign='top' align='right'>".$fed_displayer2."</td>
     			</tr>
     		";
     		
     		$tab2.="
     			".($cntr+1)." ".trim($row['trailer_name'])." - PMI Soon: ".$pmi_displayer1." PMI OVERDUE - ".$pmi_displayer2.", FED Soon: ".$fed_displayer1." FED OVERDUE - ".$fed_displayer2."
     		";     		
     		
     		//if($row['id']==325 && $testing > 0 && $display_only > 0)
     		//{
     			$pm=0;	    				$fed=0;    			$oil=0;     			$valve=0;
     			$pmtxt="";    				$fedtxt="";   			$oiltxt="";   			$valvetxt="";
     			
     			if($pmi_displayer1!="")	{	$pm=1; 		$pmtxt=$pmi_displayer1;	}		if($pmi_displayer2!="")	{	$pm=2; 		$pmtxt=$pmi_displayer2;	}
     			if($fed_displayer1!="")	{	$fed=1; 		$fedtxt=$fed_displayer1;	}		if($fed_displayer2!="")	{	$fed=2; 		$fedtxt=$fed_displayer2;	}
     			//if($pm_oil_chg1!="")	{	$oil=1; 		$oiltxt=$pm_oil_chg1;	}		if($pm_oil_chg2!="")	{	$oil=2; 		$oiltxt=$pm_oil_chg2;	}
     			//if($valve_chg1!="")	{	$valve=1; 	$valvetxt=$valve_chg1;	}		if($valve_chg2!="")		{	$valve=2; 	$valvetxt=$valve_chg2;	}
     			     			
     			$reported=mrr_auto_create_maint_request_for_pm_fed_oil_valve(0,$row['id'],$pm,$fed,$oil,$valve,$pmtxt,$fedtxt,$oiltxt,$valvetxt);	
     			//echo $reported;
     		//}
     		
     		
     		$cntr++;
     	}
     }
     $tab.="</table>";
     
          
     $sql="
     	select *
     	from last_report_capture
     	where report_url='/report_maint_requests.php' 
     		and report_query='current=1'
     ";
     $data=simple_query($sql);
     if($row=mysqli_fetch_array($data))
     {	
     	$report_snapshot="";
     	$excel_version="";
     	
     	$report_snapshot=trim($row['report_html']);
     	$excel_version=trim($row['excel_filename']);
     	
     	if($report_snapshot!="")		$tab.="<br><br><h3>".trim($row['report_title'])."</h3><br>".$report_snapshot."";	
     	
     	if($excel_version!="")		$tab.="<br><br><a href='".$excel_version."'>Click for Excel Version</a>";	     	
     }
     
     if($cntr > 0)
     {		
		//$cc_2="kmullins@conardtransportation.com";
		
		$from="system@conardtransportation.com";
		$from_name=trim($defaultsarray['company_name']);
		
		$subject="Trailer PMI/FED: ".$cntr." Coming Due or OVERDUE.";
		$msg1="This is a reminder to check on the following trailers for PMI/FED inspections due: \r\n ".$tab2." \r\n";
		$msg2="<b>This is a reminder to check on the following trailers for PMI/FED inspections due:</b> <br><br>".$tab."<br>";
		
		$res=mrr_find_all_admin_email_list(1,1);
		$cntrx=$res['num'];				
		//$arr=$res['ids'];
		$arr_email=$res['email'];
		$arr_names=$res['names'];
		//$arr_phone=$res['phone'];	
		for($i=0;$i < $cntrx; $i++)
		{
			$send_name=trim($arr_names[$i]).", ";
			$send_to=trim($arr_email[$i]);
						
			if($testing==0 && $send_to!="")
			{
				mrr_trucking_sendMail($send_to,'Trailer PMI/FED',$from,$from_name,'','',$subject, $send_name."".$msg1, $send_name."".$msg2);				
			}
		}
     	     	
     	if($testing > 0 && $display_only==0)
     	{
     		$send_to=$defaultsarray['special_email_monitor'];
     		$send_name="Lord Vader, ";
     		mrr_trucking_sendMail($send_to,'Trailer PMI/FED',$from,$from_name,'','',$subject, $send_name."".$msg1, $send_name."".$msg2);
     	} 
     }     
     
     if($display_only > 0)
     {
     	echo "<br>".$cntr." Items found... <br>Report Contents:<br>".$tab."";			//<br>".$tab2."
     }
     
     $res['num']=$cntr;
     $res['report']=$tab;
	
	return $res;
}


function mrr_send_mr_msg_quick_note($mr_id,$quick_note="")
{
     $id=(int) $mr_id;
     $note=trim($quick_note);
     
     if($id<=0 || $note=="")    return 0;
     
     global $defaultsarray;
     
     $desc="";
     $type="";
     $unit="";
     
     if($id>0 && $note!="")
     {
          $sql2="
				select maint_requests.equip_type,
				    maint_requests.ref_id,
				    maint_requests.maint_desc			
				from maint_requests
				where maint_requests.id='".sql_friendly($id)."'
			";
          $data2=simple_query($sql2);
          if($row2=mysqli_fetch_array($data2))
          {
               $desc=trim($row2['maint_desc']);
               
               if($row2['equip_type']==1 || $row2['equip_type']==58)
               {
                    $type="Truck";
                    $sql1="
                              select name_truck
                               from trucks 
                               where id='".sql_friendly($row2['ref_id'])."'
                         ";
                    $data1=simple_query($sql1);
                    if($row1=mysqli_fetch_array($data1))
                    {
                         $unit=trim($row1['name_truck']);
                    }
               }
               elseif($row2['equip_type']==2 || $row2['equip_type']==59)
               {
                    $type="Trailer";
                    $sql1="
                              select trailer_name
                               from trailers 
                               where id='".sql_friendly($row2['ref_id'])."'
                         ";
                    $data1=simple_query($sql1);
                    if($row1=mysqli_fetch_array($data1))
                    {
                         $unit=trim($row1['trailer_name']);
                    }
               }
          }
          
     }
     
     $send_to=$defaultsarray['company_email_address'];
     $subject="New Maintenance Request ".$id." Quick Note Added";
     
     $msg1="New Quick Note added for MR:  ".$type." ".$unit." \r\n New Quick Note: ".$note." \r\n MR ".$id.": ".$desc." \r\n";
     $msg2="<b>New note added for MR:  ".$type." ".$unit."</b> <br><br>New Quick Note: ".$note."<br><br><a href='https://trucking.conardtransportation.com/maint.php?id=".$id."' target='_blank'>MR ".$id."</a>: ".$desc." <br><br>";
     
     //mrr_trucking_sendMail($defaultsarray['special_email_monitor'],'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
     
     mrr_trucking_sendMail($send_to,'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
     mrr_trucking_sendMail("conardmaintenance@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
          
     ////mrr_trucking_sendMail("disenberger22@gmail.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
     //mrr_trucking_sendMail("Shamm@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
     //mrr_trucking_sendMail("dconard@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
     //mrr_trucking_sendMail("jgriffith@conardtransportation.com",'Dispatch',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subject,$msg1,$msg2);
     
     return 1;
}

//Email list functions...
function mrr_admin_email_list_box($field,$pre=0,$java="") 
{
	$tab="<select name='".$field."' id='".$field."'".$java.">";
	
	$sel="";	if($pre==0) 	$sel=" selected";
	$tab.="<option value='0'".$sel.">All</option>";
	
	$sel="";	if($pre==1) 	$sel=" selected";
	$tab.="<option value='1'".$sel.">PM/FED Report</option>";
     
    $sel="";	if($pre==2) 	$sel=" selected";
    $tab.="<option value='2'".$sel.">Inventory-NEW</option>";
     
    $sel="";	if($pre==3) 	$sel=" selected";
    $tab.="<option value='3'".$sel.">Inventory-LOW</option>";
	
	$tab.="</select>";
	return $tab;
} 
function mrr_admin_email_list_decoder($id)
{
	$arr[0]="All";
	$arr[1]="PM/FED Report";
    $arr[1]="Inventory-NEW";
    $arr[1]="Inventory-LOW";
	
	return $arr[ $id ];
}
function mrr_find_all_admin_email_list($cat_id,$cd=0)
{
	$cntr=0;
	$arr[0]=0;
	$arr_email[0]="";
	$arr_names[0]="";
	$arr_phone[0]="";
		
	$sql = "
		select *		
		from email_address_list
		where deleted<=0 and active>0
			".($cd > 0 ? "and (cat_id='".sql_friendly($cat_id)."' or cat_id<=0)" : "and cat_id='".sql_friendly($cat_id)."'")."
			
		order by email asc, email_name asc, id asc
	";
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data))
	{
		$arr[$cntr]=$row['id'];
		$arr_email[$cntr]=trim($row['email']);
		$arr_names[$cntr]=trim($row['email_name']);
		$arr_phone[$cntr]=trim($row['phone_number']);
		$cntr++;
	}
	
	$res['num']=$cntr;
	$res['ids']=$arr;
	$res['email']=$arr_email;
	$res['names']=$arr_names;
	$res['phone']=$arr_phone;
	
	return $res;
}


function mrr_convert_hour_decimal_to_time($timer=0)
{
	$show_neg=0;
	$new_timer=trim(round($timer,2));
	$new_timer=str_replace(",","",$new_timer);
	
	$time_displayer="(".$new_timer.")";
	
	//capture if negative amount...
	if(substr_count($new_timer,"-") > 0)
	{
		$show_neg=1;	
		$new_timer=str_replace("-","",$new_timer);
	}
			
	$poser=strpos($new_timer,".");
	if($poser == 0 || substr_count($new_timer,".00") > 0)
	{	//no decimal, so just display as full hours... ":00"
		$time_displayer="".($show_neg > 0 ? "-" : "")."".$new_timer.":00";
	}
	elseif($poser > 0)
	{
		$temp="0".substr($new_timer,$poser);
		
		$fraction=round(floatval($temp),4);
		
		$temp_hrs=abs(round(floatval($timer),2)) - abs($fraction);
					
		$fractal=(int) ($fraction * 60);
		
		if($fractal==9 || $fractal==19 || $fractal==29 || $fractal==39 || $fractal==49 || $fractal==59)	$fractal++;
		if($fractal==4 || $fractal==14 || $fractal==24 || $fractal==34 || $fractal==44 || $fractal==54)	$fractal++;
		if($fractal==60)	$fractal=0;
		
		//$time_displayer="".($show_neg > 0 ? "-" : "")."[".$temp_hrs.":".$fractal."  ".$fraction."]";	//  ".$timer." - 
		$time_displayer="".($show_neg > 0 ? "-" : "")."".$temp_hrs.":".($fractal < 10 ? "0".$fractal."" : "".$fractal."")."";	
	}
	return $time_displayer;
}


function mrr_show_old_dispatches_opened_count($days=0)
{
	$alerts=0;
	$sql = "
		select count(*) as alert_count
		
		from trucks_log
			left join load_handler on load_handler.id = trucks_log.load_handler_id
		where trucks_log.deleted <= 0
			and trucks_log.dispatch_completed <= 0
			and load_handler.deleted <= 0
			and trucks_log.linedate_pickup_eta <= '".date("Y-m-d",strtotime("-".$days." days",time()))." 23:59:59' 
			and trucks_log.linedate_dropoff_eta <= '".date("Y-m-d",strtotime("-".$days." days",time()))." 23:59:59' 
	
		order by trucks_log.linedate_pickup_eta
	";
	$data = simple_query($sql);	
	if($row = mysqli_fetch_array($data))
	{
		$alerts=$row['alert_count'];
	}
	return $alerts;
}

//FedEx EDI Logs
function mrr_add_fedex_edi_invoicing_log($user_id,$load_id,$invoice_id,$text,$sent,$received,$cleared,$mileage)
{
	$user_id=(int) $user_id;			//$_SESSION['user_id']
	$load_id=(int) $load_id;
	$invoice_id=(int) $invoice_id;
	$text=trim($text);
	$sent=(int) $sent;
	$received=(int) $received;
	$cleared=(int) $cleared;
	$mileage=(int) $mileage;
	
	$sql = "
		insert into log_edi_invoicing
			(id,
			linedate_added,
			user_id,
			load_id,
			invoice_id,
			invoice_text,
			sent_out,
			response_received,
			clear_to_resend,
			lynnco_edi_flag,
			koch_edi_flag,
			mileage)
		values
			(NULL,
			NOW(),
			'".sql_friendly($user_id)."',
			'".sql_friendly($load_id)."',
			'".sql_friendly($invoice_id)."',
			'".sql_friendly($text)."',
			'".sql_friendly($sent)."',
			'".sql_friendly($received)."',
			'".sql_friendly($cleared)."',
			0,
			0,
			'".sql_friendly($mileage)."')
	";
	simple_query($sql);	
}
function mrr_get_fedex_edi_invoicing_logs($load_id,$show_details=0,$width=0,$height=0)
{
	$rval="";
	if($load_id==0)		return $rval;
	
	if($width==0)			$width=500;
	if($height==0)			$height=700;
	
	$col_num=9;
	$color="00cc00";
	
	$rval="<div style='height:".$height."px; max-height:".$height."px; width:".$width."px; overflow:auto;'>";
	
	$rval.= "<table class='admin_menu1' width='".($width-10)."' style='border:1px ".$color." solid;'>
				<tr>
					<td valign='top' colspan='".$col_num."' class='border_bottom'><div class='section_heading'>FedEx EDI Invoicing Log</div></td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;</td>	
					<td valign='top'>Log Date</td>		
					<td valign='top'>User</td>
					<td valign='top' align='right'>Load</td>
					<td valign='top' align='right'>Invoice</td>
					<td valign='top' align='right'>Sent</td>
					<td valign='top' align='right'>Received</td>
					<td valign='top' align='right'>Reset</td>
					<td valign='top' align='right'>Miles</td>
				</tr>
			";
	
	$cntr=0;
	$sql = "
		select log_edi_invoicing.*,
			users.username
		
		from log_edi_invoicing
			left join users on users.id = log_edi_invoicing.user_id
		where log_edi_invoicing.load_id='".sql_friendly($load_id)."'
			and lynnco_edi_flag<=0
			and koch_edi_flag<=0
	
		order by log_edi_invoicing.linedate_added desc,log_edi_invoicing.response_received asc
	";
	$data = simple_query($sql);	
	while($row = mysqli_fetch_array($data))
	{
		$username=trim($row['username']);		if($row['user_id']==0)				$username="System";
		$sent="<b>No</b>";					if($row['sent_out'] > 0)				$sent="Yes";	
		$received="<b>No</b>";				if($row['response_received'] > 0)		$received="Yes";
		$cleared="";						if($row['clear_to_resend']  > 0)		$cleared="<b>Cleared</b>";	
		
		
		$rval.= "<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
					<td valign='top'><span title='ID is ".$row['id']."'>".($cntr + 1)."</span></td>	
					<td valign='top'>".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."</td>		
					<td valign='top'>".$username."</td>
					<td valign='top' align='right'>".$row['load_id']."</td>
					<td valign='top' align='right'>".$row['invoice_id']."</td>
					<td valign='top' align='right'>".$sent."</td>
					<td valign='top' align='right'>".$received."</td>
					<td valign='top' align='right'>".$cleared."</td>
					<td valign='top' align='right'>".$row['mileage']."</td>
				</tr>
			";
		if($show_details > 0 && trim($row['invoice_text'])!="")
		{
			$rval.= "
				<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
					<td valign='top'>&nbsp;</td>	
					<td valign='top' colspan='".($col_num - 1)."'>
						".((int) $row['response_received'] > 1 ? "Response Result: ".$row['response_received']."<br>" : "")."
						".($row['clear_to_resend'] > 0 ? "<span style='color:#cc0000;'><i>".trim($row['invoice_text'])."</i></span>" : "".trim($row['invoice_text'])."" )."
					</td>
				</tr>
			";
		}
		
		$cntr++;
	}
	
	$rval.="</table></div>";		//<br>".$sql."
	
	return $rval;	
}
function mrr_fedex_990_display($load_id)
{
	$load_id=(int) $load_id;
	$tab="";
	if($load_id > 0)
	{		
		$sql="
			select load_number, fedex_edi_input_file,fedex_edi_output_file, edi_source_file
			from load_handler 
			where id='".$load_id."'
				and deleted=0 				
		";	
		//$tab=$sql."<br>";
		$data = simple_query($sql);	
		if($row = mysqli_fetch_array($data))
		{
			//first, find the original document we created as a response
			$file_path="C:\web\\trucking.conardlogistics.com".trim($row['fedex_edi_input_file']);
			$file_name=trim($row['fedex_edi_output_file']);
			$str="";
			
			$str=file_get_contents("".$file_path."".$file_name."", FILE_USE_INCLUDE_PATH);		//, FILE_USE_INCLUDE_PATH    ...or...  , true
			
			$tab.="<h4><b>".trim($row['load_number'])." 990 Sent</b></h4>";
			$tab.="<br><b>".$file_path."".$file_name."</b>";	
			$tab.="<br>";
			$tab.="<pre>".$str."</pre>";
			
			//now find the one they sent us.
			$backup_path="C:\web\\trucking.conardlogistics.com"."/edi/fedex/backup/";
			$backup_file=trim($row['edi_source_file']);
			if($backup_file!="")
			{
				$str=file_get_contents("".$backup_path."".$backup_file."", FILE_USE_INCLUDE_PATH);		//, FILE_USE_INCLUDE_PATH    ...or...  , true
				
				$tab="<h4><b>".trim($row['load_number'])." Source File Received</b></h4>";
				$tab.="<br>EDI Input File: <b>".$backup_path."".$backup_file."</b>";
                $tab.="<br>";
                $tab.="<br>EDI Response File: <b><span style='color:#00cc00;'>".trim($row['fedex_edi_input_file'])."</span><span style='color:purple;'>".$file_name."</span></b>";
                $tab.="<br>";
				$tab.="<pre>".$str."</pre>";
			}
			
			//getcwd();			//chdir(".");		chdir("edi");		chdir("lynnco_in");		chdir("backup");
			//chdir($backup_path);
			//foreach (glob("*.txt") as $filename) 
			//{
    				//echo "$filename size " . filesize($filename) . "\n";
			//}			
		}
	}	
	return $tab;	
}


//LynnCo EDI Logs
function mrr_add_lynnco_edi_invoicing_log($user_id,$load_id,$invoice_id,$text,$sent,$received,$cleared,$mileage)
{
	$user_id=(int) $user_id;			//$_SESSION['user_id']
	$load_id=(int) $load_id;
	$invoice_id=(int) $invoice_id;
	$text=trim($text);
	$sent=(int) $sent;
	$received=(int) $received;
	$cleared=(int) $cleared;
	$mileage=(int) $mileage;
	
	$sql = "
		insert into log_edi_invoicing
			(id,
			linedate_added,
			user_id,
			load_id,
			invoice_id,
			invoice_text,
			sent_out,
			response_received,
			clear_to_resend,
			lynnco_edi_flag,
			koch_edi_flag,
			mileage)
		values
			(NULL,
			NOW(),
			'".sql_friendly($user_id)."',
			'".sql_friendly($load_id)."',
			'".sql_friendly($invoice_id)."',
			'".sql_friendly($text)."',
			'".sql_friendly($sent)."',
			'".sql_friendly($received)."',
			'".sql_friendly($cleared)."',
			1,
			0,
			'".sql_friendly($mileage)."')
	";
	simple_query($sql);	
}
function mrr_get_lynnco_edi_invoicing_logs($load_id,$show_details=0,$width=0,$height=0)
{
	$rval="";
	if($load_id==0)		return $rval;
	
	if($width==0)			$width=500;
	if($height==0)			$height=700;
	
	$col_num=9;
	$color="00cc00";
	
	$rval="<div style='height:".$height."px; max-height:".$height."px; width:".$width."px; overflow:auto;'>";
	
	$rval.= "<table class='admin_menu1' width='".($width-10)."' style='border:1px ".$color." solid;'>
				<tr>
					<td valign='top' colspan='".$col_num."' class='border_bottom'><div class='section_heading'>LynnCo EDI Invoicing Log</div></td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;</td>	
					<td valign='top'>Log Date</td>		
					<td valign='top'>User</td>
					<td valign='top' align='right'>Load</td>
					<td valign='top' align='right'>Invoice</td>
					<td valign='top' align='right'>Sent</td>
					<td valign='top' align='right'>Received</td>
					<td valign='top' align='right'>Reset</td>
					<td valign='top' align='right'>Miles</td>
				</tr>
			";
	
	$cntr=0;
	$sql = "
		select log_edi_invoicing.*,
			users.username
		
		from log_edi_invoicing
			left join users on users.id = log_edi_invoicing.user_id
		where log_edi_invoicing.load_id='".sql_friendly($load_id)."'
			and lynnco_edi_flag > 0
			and koch_edi_flag <=0
	
		order by log_edi_invoicing.linedate_added desc,log_edi_invoicing.response_received asc
	";
	$data = simple_query($sql);	
	while($row = mysqli_fetch_array($data))
	{
		$username=trim($row['username']);		if($row['user_id']==0)				$username="System";
		$sent="<b>No</b>";					if($row['sent_out'] > 0)				$sent="Yes";	
		$received="<b>No</b>";				if($row['response_received'] > 0)		$received="Yes";
		$cleared="";						if($row['clear_to_resend']  > 0)		$cleared="<b>Cleared</b>";	
		
		
		$log_txt=trim($row['invoice_text']);
		$log_txt=str_replace("214_CDPN_","214_CDPN_<b>",$log_txt);
		$log_txt=str_replace("_D1.","</b>_D1.",$log_txt);
		$log_txt=str_replace("_AF.","</b>_AF.",$log_txt);
		
		$rval.= "<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
					<td valign='top'><span title='ID is ".$row['id']."'>".($cntr + 1)."</span></td>	
					<td valign='top'>".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."</td>		
					<td valign='top'>".$username."</td>
					<td valign='top' align='right'>".$row['load_id']."</td>
					<td valign='top' align='right'>".$row['invoice_id']."</td>
					<td valign='top' align='right'>".$sent."</td>
					<td valign='top' align='right'>".$received."</td>
					<td valign='top' align='right'>".$cleared."</td>
					<td valign='top' align='right'>".$row['mileage']."</td>
				</tr>
			";
		if($show_details > 0 && $log_txt!="")
		{
			$rval.= "
				<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
					<td valign='top'>&nbsp;</td>	
					<td valign='top' colspan='".($col_num - 1)."'>
						".((int) $row['response_received'] > 1 ? "Response Result: ".$row['response_received']."<br>" : "")."
						".($row['clear_to_resend'] > 0 ? "<span style='color:#cc0000;'><i>".trim($log_txt)."</i></span>" : "".trim($log_txt)."" )."
					</td>
				</tr>
			";
		}
		
		$cntr++;
	}
	
	$rval.="</table></div>";		//<br>".$sql."
	
	return $rval;	
}
function mrr_lynnco_990_display($load_id)
{
	$load_id=(int) $load_id;
	$tab="";
	if($load_id > 0)
	{		//fedex_edi_input_file,fedex_edi_output_file
		$sql="
			select load_number,lynnco_edi_input_file,lynnco_edi_output_file, edi_source_file
			from load_handler 
			where id='".$load_id."'
				and deleted=0 
				and lynnco_edi > 0 				
		";	
		//$tab=$sql."<br>";
		$data = simple_query($sql);	
		if($row = mysqli_fetch_array($data))
		{
			//first, find the original document we created as a response
			$file_path="C:\web\\trucking.conardlogistics.com".trim($row['lynnco_edi_input_file']);
			$file_name=trim($row['lynnco_edi_output_file']);
			$str="";
			
			$str=file_get_contents("".$file_path."".$file_name."", FILE_USE_INCLUDE_PATH);		//, FILE_USE_INCLUDE_PATH    ...or...  , true
			
			$tab.="<h4><b>".trim($row['load_number'])." 990 Sent</b></h4>";
			$tab.="<br><b>".$file_path."".$file_name."</b>";	
			$tab.="<br>";
			$tab.="<pre>".$str."</pre>";
			
			//now find the one they sent us.
			$backup_path="C:\web\\trucking.conardlogistics.com"."/edi/lynnco_in/backup/";
			$backup_file=trim($row['edi_source_file']);
			if($backup_file!="")
			{
				$str=file_get_contents("".$backup_path."".$backup_file."", FILE_USE_INCLUDE_PATH);		//, FILE_USE_INCLUDE_PATH    ...or...  , true
				
				$tab="<h4><b>".trim($row['load_number'])." Source File Received</b></h4>";
				$tab.="<br><b>".$backup_path."".$backup_file."</b>";	
				$tab.="<br>";
				$tab.="<pre>".$str."</pre>";
			}
			
			//getcwd();			//chdir(".");		chdir("edi");		chdir("lynnco_in");		chdir("backup");
			//chdir($backup_path);
			//foreach (glob("*.txt") as $filename) 
			//{
    				//echo "$filename size " . filesize($filename) . "\n";
			//}			
		}
	}	
	return $tab;	
}

//Koch EDI Logs
function mrr_add_koch_edi_invoicing_log($user_id,$load_id,$invoice_id,$text,$sent,$received,$cleared,$mileage)
{
     $user_id=(int) $user_id;			//$_SESSION['user_id']
     $load_id=(int) $load_id;
     $invoice_id=(int) $invoice_id;
     $text=trim($text);
     $sent=(int) $sent;
     $received=(int) $received;
     $cleared=(int) $cleared;
     $mileage=(int) $mileage;
     
     $sql = "
		insert into log_edi_invoicing
			(id,
			linedate_added,
			user_id,
			load_id,
			invoice_id,
			invoice_text,
			sent_out,
			response_received,
			clear_to_resend,
			lynnco_edi_flag,
			koch_edi_flag,
			mileage)
		values
			(NULL,
			NOW(),
			'".sql_friendly($user_id)."',
			'".sql_friendly($load_id)."',
			'".sql_friendly($invoice_id)."',
			'".sql_friendly($text)."',
			'".sql_friendly($sent)."',
			'".sql_friendly($received)."',
			'".sql_friendly($cleared)."',
			0,
			1,
			'".sql_friendly($mileage)."')
	";
     simple_query($sql);
}
function mrr_get_koch_edi_invoicing_logs($load_id,$show_details=0,$width=0,$height=0)
{
     $rval="";
     if($load_id==0)		return $rval;
     
     if($width==0)			$width=500;
     if($height==0)			$height=700;
     
     $col_num=9;
     $color="00cc00";
     
     $rval="<div style='height:".$height."px; max-height:".$height."px; width:".$width."px; overflow:auto;'>";
     
     $rval.= "<table class='admin_menu1' width='".($width-10)."' style='border:1px ".$color." solid;'>
				<tr>
					<td valign='top' colspan='".$col_num."' class='border_bottom'><div class='section_heading'>Koch EDI Invoicing Log</div></td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;</td>	
					<td valign='top'>Log Date</td>		
					<td valign='top'>User</td>
					<td valign='top' align='right'>Load</td>
					<td valign='top' align='right'>Invoice</td>
					<td valign='top' align='right'>Sent</td>
					<td valign='top' align='right'>Received</td>
					<td valign='top' align='right'>Reset</td>
					<td valign='top' align='right'>Miles</td>
				</tr>
			";
     
     $cntr=0;
     $sql = "
		select log_edi_invoicing.*,
			users.username
		
		from log_edi_invoicing
			left join users on users.id = log_edi_invoicing.user_id
		where log_edi_invoicing.load_id='".sql_friendly($load_id)."'
			and lynnco_edi_flag<=0
			and koch_edi_flag > 0
	
		order by log_edi_invoicing.linedate_added desc,log_edi_invoicing.response_received asc
	";
     $data = simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {
          $username=trim($row['username']);		if($row['user_id']==0)				    $username="System";
          $sent="<b>No</b>";					if($row['sent_out'] > 0)				$sent="Yes";
          $received="<b>No</b>";				if($row['response_received'] > 0)		$received="Yes";
          $cleared="";						    if($row['clear_to_resend']  > 0)		$cleared="<b>Cleared</b>";
          
          
          $rval.= "<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
					<td valign='top'><span title='ID is ".$row['id']."'>".($cntr + 1)."</span></td>	
					<td valign='top'>".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."</td>		
					<td valign='top'>".$username."</td>
					<td valign='top' align='right'>".$row['load_id']."</td>
					<td valign='top' align='right'>".$row['invoice_id']."</td>
					<td valign='top' align='right'>".$sent."</td>
					<td valign='top' align='right'>".$received."</td>
					<td valign='top' align='right'>".$cleared."</td>
					<td valign='top' align='right'>".$row['mileage']."</td>
				</tr>
			";
          if($show_details > 0 && trim($row['invoice_text'])!="")
          {
               $rval.= "
				<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
					<td valign='top'>&nbsp;</td>	
					<td valign='top' colspan='".($col_num - 1)."'>
						".((int) $row['response_received'] > 1 ? "Response Result: ".$row['response_received']."<br>" : "")."
						".($row['clear_to_resend'] > 0 ? "<span style='color:#cc0000;'><i>".trim($row['invoice_text'])."</i></span>" : "".trim($row['invoice_text'])."" )."
					</td>
				</tr>
			";
          }
          
          $cntr++;
     }
     
     $rval.="</table></div>";		//<br>".$sql."
     
     return $rval;
}
function mrr_koch_990_display($load_id)
{
     $load_id=(int) $load_id;
     $tab="";
     if($load_id > 0)
     {
          $sql="
			select load_number, koch_edi_input_file,koch_edi_output_file, edi_source_file, linedate_edi_response_sent
			from load_handler 
			where id='".$load_id."'
				and deleted=0 				
		";
          //$tab=$sql."<br>";
          $data = simple_query($sql);
          if($row = mysqli_fetch_array($data))
          {
               //first, find the original document we created as a response
               $file_path="C:\web\\trucking.conardlogistics.com".trim($row['koch_edi_input_file']);
               $file_name=trim($row['koch_edi_output_file']);
               $str="";
               
               $str=file_get_contents("".$file_path."".$file_name."", FILE_USE_INCLUDE_PATH);		//, FILE_USE_INCLUDE_PATH    ...or...  , true
               
               $tab.="<h4><b>".trim($row['load_number'])." 990 Sent</b></h4>";
               $tab.="<br><b>".$file_path."".$file_name."</b>";
               $tab.="<br>";
               $tab.="<pre>".$str."</pre>";
               
               //now find the one they sent us.
               $backup_path="C:\web\\trucking.conardlogistics.com"."/edi/koch/backup/";
               $backup_file=trim($row['edi_source_file']);
               if($backup_file!="")
               {
                    $str=file_get_contents("".$backup_path."".$backup_file."", FILE_USE_INCLUDE_PATH);		//, FILE_USE_INCLUDE_PATH    ...or...  , true
                    
                    $tab="<h4><b>".trim($row['load_number'])." Source File Received</b></h4>";
                    $tab.="<br>EDI Input File: <b>".$backup_path."".$backup_file."</b>";
                    $tab.="<br>";
                    $tab.="<br>EDI Response File: <b><span style='color:#00cc00;'>".trim($row['koch_edi_input_file'])."</span><span style='color:purple;'>".$file_name."</span></b>";
                    $tab.=" | SENT: ".$row['linedate_edi_response_sent']." &nbsp;&nbsp;&nbsp; - - >";
		    $tab.="<a href='manage_load.php?load_id=".$load_id."&resend_edi_file=990'>Resend EDI Response File to Koch</a>";
		    $tab.="<br>";
                    $tab.="<pre>".$str."</pre>";
               }
               
               //getcwd();			//chdir(".");		chdir("edi");		chdir("lynnco_in");		chdir("backup");
               //chdir($backup_path);
               //foreach (glob("*.txt") as $filename) 
               //{
               //echo "$filename size " . filesize($filename) . "\n";
               //}			
          }
     }
     return $tab;
}


//New Driver Status history...added 01/06/2023
function mrr_driver_status_history_add($driver_id,$status_date,$status_id=0,$status_note="",$days=0)
{   
    if((int)$driver_id==0)      return 0;       //no driver, no need for history to be saved.
    
    $sqlu="
        insert into driver_status_history
            (id,
            linedate_added,
            driver_id,
            linedate_status,            
            status_id,
            status_note,
            expire_days,
            deleted)
        values 
            (NULL,
            NOW(),
            '".sql_friendly($driver_id)."',
            '".date("Y-m-d",strtotime($status_date))."',
            '".sql_friendly($status_id)."',
            '".sql_friendly(trim($status_note))."',
            '".sql_friendly($days)."',            
            0)    
    ";
    simple_query($sqlu);
    return 1;
}
function mrr_driver_status_history_list($driver_id,$limit=0)
{
     //if((int)$driver_id==0)      return "";       //no driver, no need for history to be saved.
          
     $cntr=0;
     $tab="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
     $tab.="<tr>";
     if($driver_id == 0) 
     {
        $tab .= "              
                <td valign='top'><b>Driver First</b></td>
                <td valign='top'><b>Last Name</b></td>
                <td valign='top'><b>Logged</b></td>
        ";
     }
     $tab.="
                <td valign='top'><b>Started</b></td>
                <td valign='top'><b>Status</b></td>
                <td valign='top'><b>Days</b></td>
                <td valign='top'><b>Expired</b></td>
                <td valign='top'><b>Note</b></td>                
            </tr>
     ";
     $sql="
            select driver_status_history.*,
                 drivers.name_driver_first,
                 drivers.name_driver_last,
                 option_values.fname,
                 option_values.fvalue
            from driver_status_history
                left join drivers on drivers.id=driver_status_history.driver_id
                left join option_values on option_values.id=driver_status_history.status_id
            where driver_status_history.deleted=0
                ".($driver_id > 0 ? "and driver_status_history.driver_id='".sql_friendly($driver_id)."'" : "")."
            order by driver_status_history.linedate_status desc, 
                driver_status_history.id desc
            ".($limit > 0 ? "limit ".$limit."" : "")."			
     ";
     //$tab=$sql."<br>";
     $data = simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {
         $cntr++;
         $tab.="<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'> ";
         if($driver_id == 0) 
         {
              $tab.="               
                    <td valign='top'>".$row['name_driver_first']."</td>
                    <td valign='top'>".$row['name_driver_last']."</td>
                    <td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
             ";                 
         }
         $tab.="              
                               
                <td valign='top'>".date("m/d/Y",strtotime($row['linedate_status']))."</td>
                <td valign='top'>".$row['fvalue']."</td>
                <td valign='top'>".$row['expire_days']."</td>
                <td valign='top'>".date("m/d/Y",strtotime("+".$row['expire_days']." days",strtotime($row['linedate_status'])))."</td>
                <td valign='top'>".trim($row['status_note'])."</td>                
            </tr>
         ";
         //driver_id,status_id, <td valign='top'>".$row['id']."</td><td valign='top'>".$row['fname']."</td>
     }
     $tab.="</table>";
     return $tab;
}


//functions for bonus pay.
function mrr_calculate_bonus_pay_from_hours($hours_worked,$driver_id)
{
     global $defaultsarray;
     $bonus_pay=0;
     
     //Settings added to control hourly bonus........Added 8/31/2021...MRR for Dale.
     $conard_only_employer=83;                   //ID for Conard Transportation Employers.
     $bonus_reg_hours=mrr_get_driver_bonus_pay_min_hrs();
     $bonus_limit_employer=(int) trim($defaultsarray['payroll_bonus_limit_employer']);
     $bonus_full_months=(int) trim($defaultsarray['payroll_bonus_full_months']);
     $bonus_half_months=(int) trim($defaultsarray['payroll_bonus_half_months']);
     $bonus_full_rate=trim($defaultsarray['payroll_bonus_full_rate']);
     $bonus_half_rate=trim($defaultsarray['payroll_bonus_half_rate']);
     
     $bonus_full_rate=str_replace("$","",$bonus_full_rate);
     $bonus_half_rate=str_replace("$","",$bonus_half_rate);
     
     $bonus_override = mrr_get_driver_last_hire_date($driver_id,2);  
         
     
     $employer_id = mrr_get_driver_last_hire_date($driver_id,1);
     
     $hire_date   = mrr_get_driver_last_hire_date($driver_id,0);
     if(trim($hire_date)=="")       $hire_date=date("Y-m-d",time());        //default hire date to today...just in case it is not set.
     
     
     //Reasons to stop any bonus play added to this driver's hours worked.
     if(!is_numeric($bonus_full_rate) || !is_numeric($bonus_half_rate))         return $bonus_pay;      //Bad form... don't bother calculating the bonus at all.
     
     if($bonus_limit_employer > 0 && $employer_id!=$conard_only_employer)       return $bonus_pay;      //wrong employer.
     
     if($hours_worked < $bonus_reg_hours)       return $bonus_pay;      //Didn't work enough hours.  No need for bonus.
     
     $bonus_hours=$hours_worked - $bonus_reg_hours;                     //these will be the hours the driver can get bonus pay on...
     
     
     //added on 1/21/2022 for Megan to ignore the date hired for drivers who have come back and are still allowed to have the bonus..............................
     if($bonus_override==1)
     {  //override of driver set to use HALF rate no matter what date they were hired or rehired (if they came back).
          $bonus_pay = $bonus_hours * $bonus_half_rate;
          return round($bonus_pay,2);
     }
     if($bonus_override==2)
     {  //override of driver set to use FULL rate no matter what date they were hired or rehired (if they came back).
          $bonus_pay = $bonus_hours * $bonus_full_rate;
          return round($bonus_pay,2);
     }   
     //..........................................................................................................................................................
     
     
          
     if(date("Y-m-d", strtotime($_POST['date_to'])) < date("Y-m-d",strtotime("+".$bonus_half_months." month",strtotime($hire_date))) )
     {
          //no need for bonus here either.  Hire date is less than 6 months, so there is no need for a bonus since they will not qualify for either of them.
          return $bonus_pay;
     }
     elseif(date("Y-m-d", strtotime($_POST['date_to'])) < date("Y-m-d",strtotime("+".$bonus_full_months." month",strtotime($hire_date))) &&
          date("Y-m-d", strtotime($_POST['date_to'])) >= date("Y-m-d",strtotime("+".$bonus_half_months." month",strtotime($hire_date)))  )
     {
          //driver qualifies for the half bonus amount on extra hours...  :)  Ex: $2.50 per hour extra over 55 hours.
          $bonus_pay = $bonus_hours * $bonus_half_rate;
          
          return round($bonus_pay,2);
     }
     elseif(date("Y-m-d", strtotime($_POST['date_to'])) >= date("Y-m-d",strtotime("+".$bonus_full_months." month",strtotime($hire_date))) )
     {
          //driver qualifies for the FULL bonus amount on extra hours...  :)  Ex: $5.00 per hour extra over 55 hours.
          $bonus_pay = $bonus_hours * $bonus_full_rate;
          
          return round($bonus_pay,2);
     }
     
     return $bonus_pay;         //if it gets here, the bonus is probably $0.00... A.K.A. No Bonus.        
}
function mrr_get_driver_bonus_pay_min_hrs()
{
     global $defaultsarray;
     
     $bonus_reg_hours=trim(strtolower($defaultsarray['payroll_bonus_min_reg_hours']));
     
     $bonus_reg_hours=str_replace("$","",$bonus_reg_hours);
     
     $bonus_reg_hours=str_replace("hours","",$bonus_reg_hours);
     $bonus_reg_hours=str_replace("hour","",$bonus_reg_hours);
     $bonus_reg_hours=str_replace("hr","",$bonus_reg_hours);
     $bonus_reg_hours=trim($bonus_reg_hours);
     
     if(substr_count($bonus_reg_hours,".50") > 0)
     {
          $bonus_reg_hours=(int) $bonus_reg_hours + 0.50;
     }
     elseif(substr_count($bonus_reg_hours,".25") > 0)
     {
          $bonus_reg_hours=(int) $bonus_reg_hours + 0.25;
     }
     elseif(substr_count($bonus_reg_hours,".75") > 0)
     {
          $bonus_reg_hours=(int) $bonus_reg_hours + 0.75;
     }
     else
     {
          $bonus_reg_hours=(int) $bonus_reg_hours + 0.00;
     }
     
     return $bonus_reg_hours;
}
function mrr_get_driver_last_hire_date($driver_id,$mode=0)
{   //Mode 2 just spits back the employer ID... will save time passing it through older payroll functions
     $use_hire_date="";
     $sql="
            select employer_id,
                payroll_bonus_override,
                linedate_started,
                linedate_terminated,
                linedate_rehire,
                linedate_refire,
                linedate_rehire2,
                linedate_refire2,
                linedate_rehire3,
                linedate_refire3,
                linedate_rehire4,
                linedate_refire4,
                linedate_rehire5,
                linedate_refire5
            from drivers 
            where id='".$driver_id."'
        ";
     $data = simple_query($sql);
     if($row = mysqli_fetch_array($data))
     {
          if($mode==1)    return $row['employer_id'];
          if($mode==2)    return $row['payroll_bonus_override'];
          
          $use_hire_date=$row['linedate_started'];
          if($row['linedate_terminated']>="2010-01-01 00:00:00" && $row['linedate_rehire']>="2010-01-01 00:00:00" && $row['linedate_refire']=="0000-00-00 00:00:00")
          {
               $use_hire_date=$row['linedate_rehire'];
          }
          if($row['linedate_refire']>="2010-01-01 00:00:00" && $row['linedate_rehire2']>="2010-01-01 00:00:00" && $row['linedate_refire2']=="0000-00-00 00:00:00")
          {
               $use_hire_date=$row['linedate_rehire2'];
          }
          if($row['linedate_refire2']>="2010-01-01 00:00:00" && $row['linedate_rehire3']>="2010-01-01 00:00:00" && $row['linedate_refire3']=="0000-00-00 00:00:00")
          {
               $use_hire_date=$row['linedate_rehire3'];
          }
          if($row['linedate_refire3']>="2010-01-01 00:00:00" && $row['linedate_rehire4']>="2010-01-01 00:00:00" && $row['linedate_refire4']=="0000-00-00 00:00:00")
          {
               $use_hire_date=$row['linedate_rehire4'];
          }
          if($row['linedate_refire4']>="2010-01-01 00:00:00" && $row['linedate_rehire5']>="2010-01-01 00:00:00" && $row['linedate_refire5']=="0000-00-00 00:00:00")
          {
               $use_hire_date=$row['linedate_rehire5'];
          }
     }
     return $use_hire_date;
}

function mrr_special_admin_users($user_id)
{   //Sherrod, Dale, Justin, and Megan are the special users here... added 1/31/2022 by MRR
    if($user_id==23 || $user_id==15 || $user_id==19 || $user_id==52) return 1;      //good.
    return false;
}

function mrr_sendMail_attachment($From,$FromName,$To,$ToName,$Subject,$Text,$Html,$replyName='',$replyAddr='',$my_files = array(),$my_file_cntr=0)
{
     /* mail using PHPMailer */
     //global $defaultsarray;     
     
     $mail_uuid = createuuid();
     
     //$mail = new PHPMailer();
     $mail = new PHPMailer\PHPMailer\PHPMailer();
     
     /*
     //$mail->SMTPDebug = true;
     if($defaultsarray['smtp_host'] != '') 
     {
          $mail->IsSMTP(); // telling the class to use SMTP
          $mail->Port = (int) $defaultsarray['smtp_port'];
          $mail->Host = $defaultsarray['smtp_host']; // SMTP server
          if($defaultsarray['smtp_username'] != '') 
          {
               $mail->SMTPAuth = true;
               //$mail->SMTPSecure = 'ssl';
               $mail->Username = $defaultsarray['smtp_username'];
               $mail->Password = $defaultsarray['smtp_password'];
          }
     }
     */
     $mail->AddAddress($To,$ToName);
     /*
     // add support to process multiple e-mail addresses CSV
     $to_array = explode(",",$To);
     
     foreach($to_array as $value) {
          $mail->AddAddress($value);
     }
     */
     $mail->From = $From;
     $mail->FromName = $FromName;
     
     $mrr_email=$replyAddr;
     $mrr_names=$replyName;
     if(trim($mrr_names)=="")		$mrr_names=$FromName;
     if(trim($mrr_email)=="")		$mrr_email=$From;
     $mail->AddReplyTo($mrr_email, $mrr_names);
     
     if($my_file_cntr > 0)
     {
          for($i=0; $i < $my_file_cntr; $i++)
          {
               $file_namer_added="".getcwd()."".trim($my_files[$i])."";
               $file_namer_added=str_replace("./temp/","/temp/",$file_namer_added);
               $mail->AddAttachment($file_namer_added);
          }
     }
     
     $mail->AltBody = $Text;
     $mail->Subject = $Subject;
     $mail->Body = $Html;
     $mail->WordWrap = 80;
     
     if(!$mail->Send())
     {
          $mail_error='Message was not sent.';
          $mail_error.='<br>Mailer error: ' . $mail->ErrorInfo;
          $mail_error.="<br>".$mail->ErrorInfo."<br>";
     }
     else
     {
          $mail_error = "";
          $mail_error='Message has been sent.';
     }
     return $mail_error;
}

class upload_section 
{			
	var $section_id = 0; 			// what type of document this is (i.e. user avatar, main document, company attachment, etc...)
	var $xref_id=0; 				// stores the ID of the user this document is for, which could be different than who is uploading it (example, admin uploading for a user)
	var $display_style = 0;			// if specified, we'll use this as our main display (visual / styles, etc...
	var $display_text = "Upload A New Document"; // name to show in the text field
	private $extra_params= array(); 	// stores all our extra params
	private $user_id; 				// stores the ID of the user uploading the document
	private $uuid;
	
	//Style file in /includes/mini_upload...
		
	function __construct() 
	{
		$this->param('show_success_notice', true); // default the "growl" notice on successful upload
		$this->user_id = $_SESSION['user_id']; 
		$this->section_id = $_GET['section_id'];
		$this->xref_id = $_GET['id'];
		
		global $upload_user_id;
		global $upload_section_id;
     	global $upload_xref_id;
     	
     	$upload_user_id=$_SESSION['user_id'];
		$upload_section_id=$_GET['section_id'];
		$upload_xref_id=$_GET['id'];
		
		// create a uuid for this upload section to keep track of our sessions (in case the user has multiple tabs open, we need to have
		// some unique IDs so one tab session doesn't interfere with the other.
		$this->uuid = uniqid();
	}

	// public function to add to our 'extra_params' variable
	function param($param_name, $param_value) 
	{
		$this->extra_params[$param_name] = $param_value;
	}
	
	// let's show it!
	function show() 
	{		
		// keep track of how many upload sections we've created
		global $upload_counter;
		global $upload_user_id;
		global $upload_section_id;
     	global $upload_xref_id;
     	
     	$upload_user_id=$this->user_id;
		$upload_section_id=$this->section_id;
		$upload_xref_id=$this->xref_id;
		
		$upload_counter++;
		
		// create a session variable to keep track of all the parameters set for this upload section
		$_SESSION['upload_params'][$this->uuid]['extra_params'] = $this->extra_params;
		$_SESSION['upload_params'][$this->uuid]['user_id'] = $this->user_id;
		$_SESSION['upload_params'][$this->uuid]['section_id'] = $this->section_id;
		$_SESSION['upload_params'][$this->uuid]['xref_id'] = $this->xref_id;
		
		//if($this->section_id == SECTION_SUBDIVISION || $this->section_id == SECTION_LOT)
		//{
		//	$this->display_text="Upload";	
		//}
							
		//echo htmlspecialchars(serialize($_SESSION['upload_params'][$this->uuid]));		
		?>	
			<form class="upload <?=($this->display_style == 0 ? "upload_style" : "")?>" name='form_upload_<?=$this->uuid ?>' method="post" action="includes/mini_upload/upload.php?upcounter=<?=$upload_counter ?>&uuid=<?=$this->uuid ?>" enctype="multipart/form-data">
				<div class="drop <?=($this->display_style == 0 ? "drop_style" : "")?>">
					<? if($this->display_style == 0) { ?>
						<span class='upload_document_label'><?= $this->display_text ?></span>
						<a>Browse</a>
					<? } else { ?>
						<button type='button' class='btn btn-default navbar-btn upload_btn'><?= $this->display_text ?></button>
					<? } ?>						
					<input type="file" name="upl_<?=$this->uuid ?>" id="upl_<?=$this->uuid ?>" multiple />						
				</div>	
				<input type='hidden' name='user_id' id='user_id' value='<?=$upload_user_id ?>'>	
				<input type='hidden' name='section_id' id='section_id' value='<?=$upload_section_id ?>'>
				<input type='hidden' name='xref_id' id='xref_id' value='<?=$upload_xref_id ?>'>
				<ul>
					<!-- The file uploads will be shown here -->
				</ul>		
			</form>
		<?
	}
}
?>