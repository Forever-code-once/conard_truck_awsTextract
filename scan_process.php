<? include('application.php') ?>
<?

	/*
	1 - Driver		V	
	2 - Trailer		R
	3 - Truck			T
	5 - Customer		C
	6 - Dispatches		D
	7 - User			U
	8 - Loads			L	
	*/

	$ftype_array = array("V"=>"1",
					"R"=>"2",
					"T"=>"3",
					"C"=>"5",
					"D"=>"6",
					"U"=>"7",
					"L"=>"8",
                         "Z"=>"9",
                         "B"=>"10");

	$ftype_array2 = array("I"=>"Invoice",
					"P"=>"POD",
					"D"=>"Driver File",
					"V"=>"Violation",
					"E"=>"Expense",
					"O"=>"Other",
					"M"=>"Document",
                         "F"=>"File",
                         "X"=>"Extracts",
					"R"=>"Rate Confirmation");

	$dir = getcwd()."/scanner_upload/";
	$dir_upload = getcwd()."/".$defaultsarray['document_upload_dir']."/";
	$d = dir($dir);
	while (false !== ($file = $d->read())) 
	{
		if(is_file($dir.$file)) 
		{
			preg_match('/(\d+)/',$file, $matches);
	   		//echo $file." | $matches[0]<br>";
	   		$id = $matches[0];
	   		
	   		$mrr_skipit=0;
	   		
	   		$pos1=strrpos($file,".");	
	   		$file_ext=substr($file,$pos1);  		$file_ext=str_replace(".","",$file_ext);	
	   		
	   		$ftype = substr($file,0,$pos1);		// strlen($id), ($pos1 - strlen($id))
	   		$ftype = strtoupper(trim(str_replace("_","-", $ftype)));
	   		
	   		//added section to compoensate for the Conard staff not naming the files correctly...
	   		$use_file_typer="";
	   		if(substr_count($ftype,"-")==0)
	   		{
	   			//add "-" since it is not present and hope there is no other in the file in front of the code.
	   			$chrx=1;
	   			$chrv=substr($file,0,$chrx);
	   			while(is_numeric($chrv) && $chrx<=100 && substr_count($chrv,".")==0)
	   			{
	   				$chrx++;
	   				$chrv=substr($file,0,$chrx);
	   			}	
	   			
	   			$chrv=substr($file,0,($chrx-1));
	   			
	   			$mrr_tester=trim(strtoupper($file));
	   			$mrr_tester=str_replace(".".strtoupper($file_ext) , "",$mrr_tester);
	   			
	   			if(is_numeric($mrr_tester))		$mrr_skipit=1;
	   			
	   			$mrr_tester=str_replace($chrv , "",$mrr_tester);
                 
                    $mrr_tester=str_replace("BM","-BM",$mrr_tester);                 $mrr_tester=str_replace("BF","-BF",$mrr_tester);                 $mrr_tester=str_replace("BX","-BX",$mrr_tester);
                    $mrr_tester=str_replace("ZM","-ZM",$mrr_tester);                 $mrr_tester=str_replace("ZF","-ZF",$mrr_tester);                 $mrr_tester=str_replace("ZX","-ZX",$mrr_tester);
                 
                    $mrr_tester=str_replace("VI","-VI",$mrr_tester);	   			$mrr_tester=str_replace("RI","-RI",$mrr_tester);	   			$mrr_tester=str_replace("TI","-TI",$mrr_tester);
	   			$mrr_tester=str_replace("CI","-CI",$mrr_tester);	   			$mrr_tester=str_replace("DI","-DI",$mrr_tester);	   			$mrr_tester=str_replace("LI","-LI",$mrr_tester);
	   			$mrr_tester=str_replace("UI","-UI",$mrr_tester);	
	   			
	   			$mrr_tester=str_replace("VP","-VP",$mrr_tester);	   			$mrr_tester=str_replace("RP","-RP",$mrr_tester);	   			$mrr_tester=str_replace("TP","-TP",$mrr_tester);
	   			$mrr_tester=str_replace("CP","-CP",$mrr_tester);	   			$mrr_tester=str_replace("DP","-DP",$mrr_tester);	   			$mrr_tester=str_replace("LP","-LP",$mrr_tester);
	   			$mrr_tester=str_replace("UP","-UP",$mrr_tester);	
	   			
	   			$mrr_tester=str_replace("VD","-VD",$mrr_tester);	   			$mrr_tester=str_replace("RD","-RD",$mrr_tester);	   			$mrr_tester=str_replace("TD","-TD",$mrr_tester);
	   			$mrr_tester=str_replace("CD","-CD",$mrr_tester);	   			$mrr_tester=str_replace("DD","-DD",$mrr_tester);	   			$mrr_tester=str_replace("LD","-LD",$mrr_tester);
	   			$mrr_tester=str_replace("UD","-UD",$mrr_tester);	
	   			
	   			$mrr_tester=str_replace("VV","-VV",$mrr_tester);	   			$mrr_tester=str_replace("RV","-RV",$mrr_tester);	   			$mrr_tester=str_replace("TV","-TV",$mrr_tester);
	   			$mrr_tester=str_replace("CV","-CV",$mrr_tester);	   			$mrr_tester=str_replace("DV","-DV",$mrr_tester);	   			$mrr_tester=str_replace("LV","-LV",$mrr_tester);
	   			$mrr_tester=str_replace("UV","-UV",$mrr_tester);	
	   			
	   			$mrr_tester=str_replace("VE","-VE",$mrr_tester);	   			$mrr_tester=str_replace("RE","-RE",$mrr_tester);	   			$mrr_tester=str_replace("TE","-TE",$mrr_tester);
	   			$mrr_tester=str_replace("CE","-CE",$mrr_tester);	   			$mrr_tester=str_replace("DE","-DE",$mrr_tester);	   			$mrr_tester=str_replace("LE","-LE",$mrr_tester);
	   			$mrr_tester=str_replace("UE","-UE",$mrr_tester);	
	   			
	   			$mrr_tester=str_replace("VO","-VO",$mrr_tester);	   			$mrr_tester=str_replace("RO","-RO",$mrr_tester);	   			$mrr_tester=str_replace("TO","-TO",$mrr_tester);
	   			$mrr_tester=str_replace("CO","-CO",$mrr_tester);	   			$mrr_tester=str_replace("DO","-DO",$mrr_tester);	   			$mrr_tester=str_replace("LO","-LO",$mrr_tester);
	   			$mrr_tester=str_replace("UO","-UO",$mrr_tester);	
	   			
	   			$mrr_tester=str_replace("VM","-VM",$mrr_tester);	   			$mrr_tester=str_replace("RM","-RM",$mrr_tester);	   			$mrr_tester=str_replace("TM","-TM",$mrr_tester);
	   			$mrr_tester=str_replace("CM","-CM",$mrr_tester);	   			$mrr_tester=str_replace("DM","-DM",$mrr_tester);	   			$mrr_tester=str_replace("LM","-LM",$mrr_tester);
	   			$mrr_tester=str_replace("UM","-UM",$mrr_tester);	
	   			
	   			$mrr_tester=str_replace("VR","-VR",$mrr_tester);	   			$mrr_tester=str_replace("RR","-RR",$mrr_tester);	   			$mrr_tester=str_replace("TR","-TR",$mrr_tester);
	   			$mrr_tester=str_replace("CR","-CR",$mrr_tester);	   			$mrr_tester=str_replace("DR","-DR",$mrr_tester);	   			$mrr_tester=str_replace("LR","-LR",$mrr_tester);
	   			$mrr_tester=str_replace("UR","-UR",$mrr_tester);	
	   			
	   			$use_file_typer="".str_replace(".".strtoupper($file_ext) , "",$mrr_tester)."";
	   			
	   			//echo "<br>".($chrx-1)." chars. \"".$chrv."\". {".$use_file_typer."} -- [".$mrr_tester."]";		//
	   		}  		
	   		//..................................................................................	   		
	   		$ftype_part_array = explode("-",$ftype);
	   		   		
	   		//for($mrr=0; $mrr < count($ftype_part_array); $mrr++)
	   		//{
	   			//echo "<br>$mrr. ".$ftype_part_array[$mrr]."...";	
	   		//}
	   		
	   		$ftype = $ftype_part_array[0];
	   		if($use_file_typer !="")
	   		{
	   			$ftype = trim($use_file_typer);
	   			$ftype = str_replace("-","",$ftype);
	   		}
	   		//foreach($matches[0] as $digit) $id .= $digit;
	   		
	   		$section_id = 0;
	   		
	   		$ftype_sub = '';
	   		if(strlen($ftype) == 2 || strlen($ftype) == 7) 
	   		{
	   			$ftype_sub = substr($ftype,1,1); 	// signifies Invoice, Expense, POD, etc...
	   			$ftype = substr($ftype,0,1); 		// signifies Load, Driver, Truck, etc...
	   		}
	   		
	   		echo "<p>File: $file (".$ftype.")| ".$ftype_sub." | [".strtoupper($file_ext)."]<br></p>";
	   		
	   		if(isset($ftype_array[$ftype])) 
	   		{
	   			$section_id = $ftype_array[$ftype];
	   			$public_name="";
	   			
	   			
	   			if($section_id==1)
	   			{	//drivers
	   				$sql = "select id from drivers where id='".sql_friendly((int)trim($id))."' order by deleted asc, active desc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    		$public_name="Wrong Driver ID";
                    	}
	   			}
	   			elseif($section_id==2)
	   			{	//trailers
	   				$sql = "
                    		select id                    			
                    		from	trailers                    		
                    		where trailer_name='".sql_friendly(strtoupper(trim($id)))."'
                    		order by deleted asc, active desc, id asc
                    	";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    		$public_name="Wrong Trailer ID";
                    	}
	   			}
	   			elseif($section_id==3)
	   			{	//trucks
	   				$sql = "
                    		select id                    			
                    		from	trucks                    		
                    		where name_truck='".sql_friendly(strtoupper(trim($id)))."'
                    		order by deleted asc, active desc, id asc
                    	";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    		$public_name="Wrong Truck ID";
                    	}
	   			}
	   			elseif($section_id==5)
	   			{	//customers
	   				$sql = "select id from customers where id='".sql_friendly((int)trim($id))."' order by deleted asc, active desc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    		$public_name="Wrong Customer ID";
                    	}
	   			}
	   			elseif($section_id==6)
	   			{	//trucks_log
	   				$sql = "select id from trucks_log where id='".sql_friendly((int)trim($id))."' order by deleted asc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    		$public_name="Wrong Dispatch ID";
                    	}
	   			}
	   			elseif($section_id==7)
	   			{	//users
	   				$sql = "select id from users where id='".sql_friendly((int)trim($id))."' order by deleted asc, active desc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    		$public_name="Wrong User ID";
                    	}
	   			}
	   			elseif($section_id==8)
	   			{	//load_handler
	   				$sql = "select id from load_handler where id='".sql_friendly((int)trim($id))."' order by deleted asc, id asc";
                    	$data=simple_query($sql);	
                    	if($row=mysqli_fetch_array($data))
                    	{
                    		$id=$row['id'];
                    	}
                    	else
                    	{	
                    		$id=0;
                    		$public_name="Wrong Load ID";
                    	}
	   			}
                    elseif($section_id==9)
                    {	//vendor/business   ---This section won't really be used, but was a good test for the Vendor Bills below...
                         $sql = "select id from sicap_conard.vendors where id='".sql_friendly((int)trim($id))."' order by deleted asc, id asc";
                         $data=simple_query($sql);
                         if($row=mysqli_fetch_array($data))
                         {
                              $id=$row['id'];
                         }
                         else
                         {
                              $id=0;
                              $public_name="Wrong Vendor ID";
                         }
                    }
                    elseif($section_id==10)
                    {	//vendor/business bills
                         $sql = "select id from sicap_conard.bills where id='".sql_friendly((int)trim($id))."' order by deleted asc, id asc";
                         $data=simple_query($sql);
                         if($row=mysqli_fetch_array($data))
                         {
                              $id=$row['id'];
                         }
                         else
                         {
                              $id=0;
                              $public_name="Wrong Vendor Bill ID";
                         }
                    }
	   			
	   			if($ftype_sub != '' && isset($ftype_array2[$ftype_sub])) 
	   			{
					$file_ext = get_file_ext($file);
					$file_base = str_replace(".$file_ext","",$file);
	   				
	   				$new_fname_tmp = $file_base."-".$ftype_array2[$ftype_sub].".".$file_ext;
	   				$new_fname_tmp = get_unique_filename($dir, $new_fname_tmp);
                      
                         if($section_id==9 || $section_id==10) 
                         {
                              $new_fname_tmp = $file_base."-".$ftype_array2[$ftype_sub]."-".date("mdYHis",time()).".".$file_ext;
                         }
	   				
	   				echo "(".$dir.$file.",".$dir.$new_fname_tmp.")";
	   				
	   				rename($dir.$file,$dir.$new_fname_tmp);
                      
                         if($section_id==9 || $section_id==10)
                         {    //this is a vendor document or Bill, which does not exist on Dispatch Side.  Copy to the Accounting Side
                              $dest_file_name="c:\\web\\sicap_conard\\files\\".$new_fname_tmp."";
                              if(!copy($dir.$new_fname_tmp, $dest_file_name)) 
                              {
                                   echo "<br>Could not copy file ".$dest_file_name." from Dispatch side to Acct side.<br>";
                              }
                         }
	   				
	   				$file = $new_fname_tmp;
	   			}	
	   			
	   			$new_filename = get_unique_filename($dir_upload, $file);
                 
                    if($section_id==9 || $section_id==10)
                    {
                         $swap_section=10;                                 //this one is for the vendor bills, and will be used alot.
                         if($section_id==9)      $swap_section=6;          //this is for the vendor documents, and will rarely be used at all.
                         $sql = "
                              insert into sicap_conard.attached_files
                                   (section_id,
                                   xref_id,
                                   filename,
                                   filesize,
                                   linedate_added,
                                   access_level,
                                   deleted)                                   
                              values ('".sql_friendly($swap_section)."',
                                   '".sql_friendly($id)."',
                                   '".sql_friendly($new_filename)."',
                                   '".filesize($dir.$file)."',
                                   now(),
                                   50,
                                   0)
                         ";
                         simple_query($sql);
                    }
                    else
                    {
                         $sql = "
                              insert into attachments
                                   (fname,
                                   public_name,
                                   linedate_added,
                                   section_id,
                                   xref_id,
                                   file_ext,
                                   filesize,
                                   result,
                                   deleted,
                                   descriptor)
                                   
                              values ('".sql_friendly($new_filename)."',
                                   '".sql_friendly($public_name)."',
                                   now(),
                                   '".sql_friendly($section_id)."',
                                   '".sql_friendly($id)."',
                                   '".sql_friendly(get_file_ext($new_filename))."',
                                   '".filesize($dir.$file)."',
                                   1,
                                   0,
                                   '".($ftype_sub != '' ? $ftype_sub : "")."')
                         ";
                         simple_query($sql);
                    }
	   			
	   			
	   			//echo "$sql<br><br>";
	   			//die("($file | $new_filename | $section_id | $ftype_sub | $dir$file)");
	   			
	   			rename($dir.$file,$dir_upload.$new_filename);
	   		} 
	   		elseif($section_id > 0 || $mrr_skipit > 0)
	   		{	//move to the problem directory since we can't match it to where it should go
	   			$new_filename = get_unique_filename($dir."problem/",$file);

	   			$sql = "
	   				insert into attachments
	   					(fname,
	   					linedate_added,
	   					section_id,
	   					xref_id,
	   					file_ext,
	   					filesize,
	   					result,
	   					deleted)
	   					
	   				values ('".sql_friendly($new_filename)."',
	   					now(),
	   					'0',
	   					'0',
	   					'".sql_friendly(get_file_ext($new_filename))."',
	   					'".filesize($dir.$file)."',
	   					0,
	   					0)	   				
	   			";
	   			simple_query($sql);
	   			
	   			rename($dir.$file, $dir."problem/".$new_filename);
	   		}	   		
	   		
	   		echo "<p>(".($section_id > 0  ? "$section_id" : "ERROR").") $file | $id | $ftype<br></p>";
	   	}
	   	else
	   	{
	   		//echo "<p>ERROR: ".$dir.$file.".<br></p>";
	   	}
	}
	echo "<p>Done.</p>";
	$d->close();
?>