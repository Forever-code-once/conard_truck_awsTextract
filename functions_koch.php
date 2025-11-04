<?php
	function send_koch_edi($remote_path, $local_path, $file, $load_id=0,$reporter=0) 
	{
		global $defaultsarray;
		
		$edi_sftp_server = $defaultsarray['koch_edi_url'];
		$edi_sftp_username = $defaultsarray['koch_edi_user'];
		$edi_sftp_password = $defaultsarray['koch_edi_pass'];
			
			
		$ch = curl_init();
		$localfile = $local_path.''.$file;
		$full_server_path = 'sftp://'.$edi_sftp_username.':'.$edi_sftp_password.'@'.$edi_sftp_server.$remote_path.$file;
		
		if($reporter > 0)		echo "<br>Input File: ".$localfile." | Output File: ".$full_server_path .".";
		
		if(trim($file)=="")
		{
			$rslt = 0;
			if($reporter > 0)	echo "<br>Error: File is blank. Skipping Curl Process.";
			return $rslt;
		}
		
		//d($full_server_path);
		$fp = fopen($localfile, 'r');
		curl_setopt($ch, CURLOPT_URL, $full_server_path);
		curl_setopt($ch, CURLOPT_UPLOAD, 1);
		curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localfile));
		$result = curl_exec ($ch);
		$error_no = curl_errno($ch);
		
		if(is_array($result))		$result=json_encode($result);
		
		$error = "";
		if ($error_no == 0) 
		{
			$rslt = 1;
			
			if(is_string($result) && substr_count($result,"997") > 0)		$rslt = 997;
						
			if($reporter > 0)		echo "<br>Success!";
			
			$load_id=(int) $load_id;
			
			mrr_add_koch_edi_invoicing_log(0,$load_id,0,'Response Received...updating load. Result: '.trim($result).'.',1,$rslt,0,0);
			
			if($load_id > 0)
			{	//good, so clear the log.
				$sql = "
					update load_handler set					
						koch_edi_error_log = '',
						linedate_edi_response_sent = now()
					where id = '".sql_friendly($load_id)."'
				";
				simple_query($sql);			
			}			
		} 
		else 
		{
			$error = "File $file upload error ($error_no): " . curl_error($ch);
			if($reporter > 0)		echo "<br>Error: " . $error;
			$rslt = 0;
			
			$load_id=(int) $load_id;
			mrr_add_koch_edi_invoicing_log(0,$load_id,0,'Bad Response: '.addslashes(str_replace("'","",trim($error_no))).'. Result: '.trim($result).'.',1,$rslt,0,0);	
			
			if($load_id > 0)
			{	//failed, so record why if load is given.
				$sql = "
					update load_handler set					
						koch_edi_error_log = '".addslashes(str_replace("'","",trim($error_no)))."'
					where id = '".sql_friendly($load_id)."'
				";
				simple_query($sql);						
			}				
		}
		curl_close ($ch);
		
		if($reporter > 0)		echo "<br>Result=".$rslt.".<br>";
		return $rslt;
	}
	
	
	//store the file names so that it can be resent if file is still there.	 Added on 7/25/2018 by MRR to recover the files attempted.		
	function mrr_resend_missing_koch_edi_responses($dater="",$cd=0)
	{
     	global $defaultsarray;
     	
     	$dater=trim($dater);
     	if($dater=="")		$dater=date("m/d/Y",strtotime("-4 days",time()));
     	
     	$cntr=0;
     	$tab="<table cellpadding='1' cellspacing='1' border='1' width='1700'>";
     	$tab.="
     		<tr style='font-weight:bold;'>
     			<td valign='top'>#</td>
     			<td valign='top'>LoadID</td>
     			<td valign='top'>Added</td>
     			<td valign='top'>Origin</td>
     			<td valign='top'>State</td>
     			<td valign='top'>Destination</td>
     			<td valign='top'>State</td>
     			<td valign='top'>PickupETA</td>
     			<td valign='top'>EDI-Load</td>
     			<td valign='top'>Response</td>
     			<td valign='top'>Special Instructions</td>
     			<td valign='top'>Invoiced</td>
     			<td valign='top'>InputFile</td>
     			<td valign='top'>OutputFile</td>
     			<td valign='top'>ErrorLog</td>
     			<td valign='top'>NoticeSent</td>
     			<td valign='top'>Status</td>
     		</tr>
     	";
     	$sql="
     		select *
     		from load_handler
     		where deleted=0 
     			and auto_created > 0
     			and lynnco_edi <= 0 
     			and koch_edi > 0
     			and load_number!=''
     			and created_by_rate_sheet=0
     			and linedate_edi_response_sent='0000-00-00 00:00:00'	
     			and linedate_added>='".date("Y-m-d",strtotime($dater))." 00:00:00'		
     		order by id desc
     	";	
     	$data=simple_query($sql);
     	while($row=mysqli_fetch_array($data))
     	{     		
     		$status="N/A";
     		
     		//send the message to warn us about the load.
               
     		if($row['koch_edi_conard_warning']=="0000-00-00 00:00:00")
     		{     			
     			$email_from="system@conardtransportation.com";	     $from_name="Conard FedEx EDI";     			
     			$email_to="dconard@conardtransportation.com";	     $email_name="Dale";
     			$cc=$defaultsarray['special_email_monitor'];			$cc_name="Master Jedi";
     			$cc2="trucking@conardtransportation.com";			$cc_name2="Dispatch Team";
     			
     			$subject="WARNING: Koch EDI load created, but no response sent for Load ".$row['id']." (Koch ".trim($row['load_number']).")";
     			
     			$body="<br>
     				Dispatch Team,
     				<br><br>
     				<b>Koch EDI Issue:</b> Conard Load <a href='https://trucking.conardtransportation.com/manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a> was created from Koch Load ".trim($row['load_number']).", 
     				but response was not received or sent.  You may need to double-check this load.
     				<br>
     				Load Details: Pickup ETA ".date("m/d/Y H:i:s",strtotime($row['linedate_pickup_eta']))." - ".trim($row['origin_city']).", ".trim($row['origin_state'])." to ".trim($row['dest_city']).", ".trim($row['dest_state'])."
     				<br>
     				Notes: Created at ".date("m/d/Y H:i:s",strtotime($row['linedate_added'])).". ".trim($row['special_instructions'])."
     				<br>
     				<br>
     				<b>Additional Info:</b> (for debugging or reference)
     				<br>
     				Load Invoiced: ".($row['linedate_edi_invoice_sent']!="0000-00-00 00:00:00" ? "".date("m/d/Y H:i:s",strtotime($row['linedate_edi_invoice_sent']))."" : "N/A")." 
     					(Invoice Number: ".trim($row['invoice_number'])."  |  SICAP Invoice Number: ".trim($row['sicap_invoice_number']).")
     				<br>
     				Response Sent: ".($row['linedate_edi_response_sent']!="0000-00-00 00:00:00" ? "".date("m/d/Y H:i:s",strtotime($row['linedate_edi_response_sent']))."" : "N/A")."
     				<br>
     				<br>
     				<b>Input File:</b>  ...".trim($row['koch_edi_input_file'])."".trim($row['koch_edi_output_file'])."</a>.
     				<br>
     				Error Log: ".trim($row['koch_edi_error_log'])."
     				<br>
     				Warning Sent: ".($row['koch_edi_conard_warning']!="0000-00-00 00:00:00" ? "".date("m/d/Y H:i:s",strtotime($row['koch_edi_conard_warning']))."" : "".date("m/d/Y H:i:s",time())."")."
     				
     				<br><br><br>Auto-generated Message, so please do not reply... unless you do not want anyone to read your reply.
     			";		//C:\\web\\"."trucking.conardlogistics.com
     			
     			$did_send=mrr_trucking_sendMail_PN($email_to,$email_name,$email_from,$from_name,$subject,$body);	
     			if($cc!=$email_to)		$did_send=mrr_trucking_sendMail_PN($cc,$cc_name,$email_from,$from_name,$subject,$body);	
     			if($cc2!=$email_to)		$did_send=mrr_trucking_sendMail_PN($cc2,$cc_name2,$email_from,$from_name,$subject,$body);	
     			
     			$sql = "
     				update load_handler set					
     					koch_edi_conard_warning = now()
     				where id = '".sql_friendly($row['id'])."'
     			";
     			simple_query($sql);
     		}
     		
     		
     		//try to resend the file
     		if(trim($row['koch_edi_input_file'])!="" && trim($row['koch_edi_output_file'])!="")
     		{          		
          		$complete_path="C:\\web\\"."trucking.conardlogistics.com".trim($row['koch_edi_input_file'])."";
          		
          		if(send_koch_edi('/Inbox/', $complete_path, trim($row['koch_edi_output_file']),$row['id'],$cd))
          		{         //KLOG/990
          			// log the upload
     				$sql = "
     					update load_handler set					
     						linedate_edi_invoice_sent = now()
     					where id = '".sql_friendly($row['id'])."'
     				";
     				simple_query($sql);
     				$status="<span style='color:#00cc00;'><b>Resent</b></span>";
          		}
          		else
          		{
          			$status="<span style='color:#cc0000;'><b>Failed</b></span>";	
          		}
     		}
     		
     		$tab.="
     			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>
     				<td valign='top'>".($cntr + 1)."</td>
     				<td valign='top'><a href='https://trucking.conardtransportation.com/manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
     				<td valign='top'>".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."</td>
     				<td valign='top'>".trim($row['origin_city'])."</td>
     				<td valign='top'>".trim($row['origin_state'])."</td>
     				<td valign='top'>".trim($row['dest_city'])."</td>
     				<td valign='top'>".trim($row['dest_state'])."</td>				
     				<td valign='top'>".date("m/d/Y H:i:s",strtotime($row['linedate_pickup_eta']))."</td>				
     				<td valign='top'>".trim($row['load_number'])."</td>
     				<td valign='top'>".($row['linedate_edi_response_sent']!="0000-00-00 00:00:00" ? "".date("m/d/Y H:i:s",strtotime($row['linedate_edi_response_sent']))."" : "")."</td>
     				<td valign='top'>".trim($row['special_instructions'])."</td>
     				<td valign='top'>".($row['linedate_edi_invoice_sent']!="0000-00-00 00:00:00" ? "".date("m/d/Y H:i:s",strtotime($row['linedate_edi_invoice_sent']))."" : "")."</td>
     				<td valign='top'>".$complete_path."</td>
     				<td valign='top'>".trim($row['koch_edi_output_file'])."</td>
     				<td valign='top'>".trim($row['koch_edi_error_log'])."</td>  
     				<td valign='top'>".($row['koch_edi_conard_warning']!="0000-00-00 00:00:00" ? "".date("m/d/Y H:i:s",strtotime($row['koch_edi_conard_warning']))."" : "")."</td>   				
     				<td valign='top'>".$status."</td>
     			</tr>
     		";		//".($zone_name=="Add Zone" ? "<a href='sql_mrr.php?make_zone=".$row['id']."' target='_blank'>".$zone_name."</a>" : "<a href='sql_mrr.php?make_zone=".$row['id']."' target='_blank'>".$zone_name."</a>")."
     				
     		$cntr++;
     	}
     	$tab.="</table>";
     	if($cd>0)		$tab.="<br>Query was: ".$sql." <br>";
		return $tab;
	}
	
	//Don't forget to turn on the Customer table setting to use the EDI 214 updates...
     function mrr_send_koch_214_update($stop_id,$status_code="",$disp_report=0)
     {
          $stop_id=(int) $stop_id;
          if($stop_id<=0)	return 0;
          
          $status_code=trim($status_code);
          if($status_code=="")		$status_code="AF";
          
          global $defaultsarray;
          $in_path = $defaultsarray['koch_edi_path'];
          
          $cust_id=273;
          
          //AF= Actual Pickup (mandatory to send)
          //D1= Completed Unloading at Delivery Location (mandatory to send)
          //AA= Pickup Appointment Date/Time
          //AB= Delivery Appointment Date/Time
          //X3= Arrived at Pick-up Location
          //X1= Arrived at Delivery Location
          //X6= En Route to Delivery Location
     
     
     //		$sql = "
     //			select id			
     //			from customers
     //			where name_company = 'Carlex Glass C/O LynnCo'
     //				and deleted = 0
     //		";
     //		$data_cust = simple_query($sql);
     //		$row_cust = mysqli_fetch_array($data_cust);
     //		$cust_id=$row_cust['id'];
          
          
          $comp_code="KLOG           ";
          $comp_name="Koch Logistics ";
          $comp_address="PO Box 4239";
          $comp_city="St Paul";
          $comp_state="MN";
          $comp_zip="551014239";
          
          $load_handler_id=0;
          $use_control_number="";
          
          $mrr_sh_name="";
          $mrr_sh_addr="";
          $mrr_sh_city="";
          $mrr_sh_state="";
          $mrr_sh_zip="";
          
          $mrr_sh_date="";
          $mrr_sh_time="";
          
          $lading_number="";
          $lading_rate="";
          
          $sql="
                    select load_handler_stops.*,load_handler.load_number
                    from load_handler_stops
                        left join load_handler on load_handler.id=load_handler_stops.load_handler_id
                    where load_handler_stops.id='".$stop_id."'
               ";
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {
               $load_handler_id=$row['load_handler_id'];
     
               $lading_number="".$row['load_number']."";
               
               $lading_rate="".$row['actual_bill_customer']."";
               $lading_rate=str_replace("$","",$lading_rate);
               $lading_rate=str_replace(",","",$lading_rate);
               $lading_rate=str_replace(".","",$lading_rate);
               
               $use_control_number = str_pad($load_handler_id, 9, "0", STR_PAD_LEFT);
               
               $mrr_sh_name="".trim($row['shipper_name'])."";
               $mrr_sh_addr="".trim($row['shipper_address1'])."";
               $mrr_sh_city="".trim($row['shipper_city'])."";
               $mrr_sh_state="".trim($row['shipper_state'])."";
               $mrr_sh_zip="".trim($row['shipper_zip'])."";
               
               $mrr_sh_date="".date("Ymd", strtotime($row['linedate_pickup_eta']))."";
               $mrr_sh_time="".date("Hi", strtotime($row['linedate_pickup_eta']))."";
               
               $shipper_type=$row['stop_type_id'];
               
               if($shipper_type==1)		$status_code="AF";		//$status_code=="" && 
               if($shipper_type==2)		$status_code="D1";		//$status_code=="" && 
          }
          
          if($mrr_sh_name=="Conard Terminal")		return 0;		//done send any of the Conard Teminal files.     
          
     
          //$lading_number
          //$lading_rate
          if($lading_rate=="")     $lading_rate=0;
          
     
          // create the 214 response			
          $rval_214 = "ISA*00*          *00*          *02*".$defaultsarray['edi_scac_code']."           *ZZ*".$comp_code."*".date("ymd",time())."*".date("Hi",time())."*U*00604*".$use_control_number."*1*P*:\n";
          $rval_214 .= "GS*QM*".trim($defaultsarray['edi_scac_code'])."*".trim($comp_code)."*".date("Ymd")."*".date("Hi",time())."*".$use_control_number."*X*004010\n";
          $rval_214 .= "ST*214*".$use_control_number."\n";
          $rval_214 .= "B10*".$load_handler_id."*".$lading_number."*".$defaultsarray['edi_scac_code']."*1\n";
          //The B10*02 item is the second item after the B10...".$defaultsarray['edi_scac_code']."".$use_control_number."
              
          
          $rval_214 .= "N1*".($shipper_type==1 ? "SH" : "CN")."*".$mrr_sh_name."\n";
          $rval_214 .= "N3*".$mrr_sh_addr."\n";
          $rval_214 .= "N4*".$mrr_sh_city."*".$mrr_sh_state."*".$mrr_sh_zip."\n";
          //$rval_214 .= "G62*38*".$mrr_sh_date."*K*".$mrr_sh_time."\n";."\n";
          
          //$rval_214 .= "N1*BT*".$comp_name."\n";
          //$rval_214 .= "N3*".$comp_address."\n";
          //$rval_214 .= "N4*".$comp_city."*".$comp_state."*".$comp_zip."\n";	
          
          //$rval_214 .= "LX*1\n";
          $rval_214 .= "AT7*".$status_code."*NS***".date("Ymd",time())."*".date("Hi",time())."\n";
          $rval_214 .= "MS1*".$mrr_sh_city."*".$mrr_sh_state."*USA\n";
          $rval_214 .= "SE*8*".$use_control_number."\n";
          $rval_214 .= "GE*1*".$use_control_number."\n";
          $rval_214 .= "IEA*1*".$use_control_number."\n";
          
          //$rval_214=str_replace("\n",chr(10),$rval_214);
          $rval_214=str_replace("\n","~",$rval_214);
     
     //		EDI 214 for the 1st Pickup would look like this:
     //          
     //        ISA*00*          *00*          *02*CDPN           *ZZ*KLOG           *221021*1405*U*00604*000128001*1*P*:
     //        GS*QM*CDPN*KLOG*20221021*1405*000128001*X*004010
     //        ST*214*000128001
     //        B10*128001*T2604874*CDPN*1
     //        N1*SH*TRACTOR SUPPLY #1176
     //        N3*5500 MCCLELLAN BLVD
     //        N4*ANNISTON*AL*36206
     //        AT7*AF*NS***20221021*1405
     //        MS1*ANNISTON*AL*USA
     //        SE*8*000128001
     //        GE*1*000128001
     //        IEA*1*000128001               
     //          
     //        EDI 214 for the first Drop would look like this
     //                    
     //        ISA*00*          *00*          *02*CDPN           *ZZ*KLOG           *221021*1408*U*00604*000128001*1*P*:
     //        GS*QM*CDPN*KLOG*20221021*1408*000128001*X*004010
     //        ST*214*000128001
     //        B10*128001*T2604874*CDPN*1
     //        N1*CN*TRACTOR SUPPLY #1176
     //        N3*5500 MCCLELLAN BLVD
     //        N4*ANNISTON*AL*36206
     //        AT7*D1*NS***20221021*1408
     //        MS1*ANNISTON*AL*USA
     //        SE*8*000128001
     //        GE*1*000128001
     //        IEA*1*000128001
     //          
          
          $out_file_214 = "214_".$defaultsarray['edi_scac_code']."_".$stop_id."_".time()."_".$status_code.".txt";
          file_put_contents($in_path."/out/".$out_file_214, $rval_214);
          
          $rslt=0;
          $skip_send=0;
          
          if($skip_send==0)
          {
               if(send_koch_edi('/Inbox/', $in_path."/out/", $out_file_214, $load_handler_id,$disp_report,$rval_214))
               {
                    // upload was successful, remove that file from the server
                    if($disp_report > 0)		echo "<br><font color='green'>Successful Upload of 214 file.</font> <br><br>File Name: ".$in_path."/out/".$out_file_214." <br><br>File Content:<br><br>".$rval_214."<br>";
                    $rslt = 1;
                    $msg .= " and Koch Logistics 214 EDI Sent Successfully";
                    
                    
                    $sql = "
                              update load_handler_stops set
                                   koch_edi_status='".sql_friendly($rval_214)."',
                                   koch_edi_status_file='".sql_friendly($out_file_214)."',		
                                   koch_edi_status_date=NOW()		
                              where id = '".sql_friendly($_POST['stop_id'])."'
                         ";
                    simple_query($sql);
               }
               
               if($rslt==0)
               {
                    if($disp_report > 0)
                    {
                         echo "<br><pre>".send_koch_edi('/Inbox/', $in_path."/out/", $out_file_214, $load_handler_id,$rval_214)."</pre><br>";
                         $sql = "
                                   update load_handler_stops set
                                        koch_edi_status='".sql_friendly($rval_214)."',
                                        koch_edi_status_file='".sql_friendly($out_file_214)."',		
                                        koch_edi_status_date=NOW()					
                                   where id = '".sql_friendly($_POST['stop_id'])."'
                              ";
                         simple_query($sql);
                    }
               }
          }
          if($rslt==0)
          {
               if($disp_report > 0)			echo "<br><font color='red'>".($skip_send > 0 ? "TESTING: " : "")."Failed to Upload of 214 file.</font>  <br><br>File Name: ".$in_path."/out/".$out_file_214." <br><br>File Content:<br><br>".$rval_214."<br>";
          }
          
          return $rslt;
     }
     function mrr_check_if_edi_214_file_needed_koch($load_id,$stop_id,$status_code="")
     {
          $edi_214=0;
          $edi_load=0;
          $cust_id=0;
          
          $sql="
                    select customer_id,
                         koch_edi,
                         (select edi_use_214_files from customers where customers.id = load_handler.customer_id) as cust_edi_flag
                    from	load_handler 
                    where id = '".(int) $load_id."'
               ";
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {
               $edi_214=$row['cust_edi_flag'];
               $edi_load=$row['koch_edi'];
               $cust_id=$row['customer_id'];
          }
          
          //only send EDI 214 file if this is for the right customer, and the right load, and the customer is selected.  If any are zero, skip this.
          if($cust_id > 0 && $edi_load > 0 && $edi_214 > 0)
          {
               mrr_send_koch_214_update($stop_id,$status_code,0);
          }
     }
     
     
     function mrr_repair_koch_edi_bill_rate($load_id)
     {    //repair utility to fix odd cases where the bill rate is not captured, but the load is created from the EDI
          global $defaultsarray;
          $in_path = $defaultsarray['koch_edi_path'];
          
          $edi_file="";
          $edi_rate=0.00;
          $old_rate=0.00;
          $edi_load_no="";
     
          $load_id=(int) $load_id;
     
          $sql="
                    select load_number, edi_source_file, actual_bill_customer
                    from	load_handler 
                    where id = '".(int) $load_id."' and customer_id=273 and auto_created>0
               ";
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {
               $edi_file=trim($row['edi_source_file']);
               $edi_rate=$row['actual_bill_customer'];
               $old_rate=$edi_rate;
               $edi_load_no=trim($row['load_number']);
          }
          else
          {
               return $edi_rate;        // no rate to return if the load is not found.
          }     
     
          $fcontents = file_get_contents($in_path."backup\\".$edi_file);
     
          $fcontents = str_replace(">","",$fcontents);
          $fcontents = str_replace("~",chr(10),$fcontents);
          $fcontents = str_replace(chr(13),"",$fcontents);
          
          $marker_a=strpos("*".$edi_load_no."*",$fcontents);
          if($marker_a > 0)
          {
               $marker_b=strpos("L3*",$fcontents,($marker_a + 8));
               if($marker_b > 0)
               {
                    $marker_c=strpos("SE*",$fcontents,($marker_b + 3));
                    
                    $temp=trim(substr($fcontents,$marker_b,($marker_c - $marker_b)));
     
                    $line_parts = explode("*", $temp);
     
                    $edi_rate = $line_parts[3];
     
                    if(substr_count(trim($line_parts[3]),".")==0)
                    {
                         $temp_x=(int) trim($line_parts[3]);
                         $temp_x= $temp_x / 100;
                         $edi_rate = "".$temp_x."";
                    }
                                        
                    if($old_rate!=$edi_rate && $edi_rate > 0)
                    {
                         $sql = "
                              update load_handler set
                                   actual_bill_customer = '".sql_friendly($edi_rate)."'
                              where id = '$load_handler_id' and actual_bill_customer='0.00'
                              limit 1
                         ";
                         simple_query($sql);                         
                    }                  
               }
          }
          return $edi_rate; 
          
          /*  Sample File... only need to pull rate, but in cae multiple loads are in the same file, use markers to pull rate for only one.
          B2**CDPN**T2706200*L*CC~
          B2A*00~
          L11*6336491-6523*PO~
          L11*OTH*ZZ*LOAD 06/19 0700 TO 1400 FCFSDELIVER BY APPT 06/20 ; 0700 TIME SENSITIVE~
          PLD*0**L*25000~
          N1*SH*S.O.S. DOCK B~
          N3*800 MIDDLE TENNESSEE BLVD*DOCK B~
          N4*MURFREESBORO*TN*37129*US~
          G61*SH*SHIPPER CONTACT*TE*615-867-0858~
          N1*CN*TRACTOR SUPPLY #2821~
          N3*2135 EAST HIGHWAY 54~
          N4*LINTON*IN*47441*US~
          S5*1*LD~
          G62*69*20230619*I*070000*LT~
          G62*38*20230619*K*140000*LT~
          N1*SF*S.O.S. DOCK B~
          N3*800 MIDDLE TENNESSEE BLVD*DOCK B~
          N4*MURFREESBORO*TN*37129*US~
          G61*SF*CONTACT NUMBER*TE*615-867-0858~S5*2*UL~
          G62*69*20230620*I*070000*LT~
          G62*38*20230620*K*070000*LT~
          N1*ST*TRACTOR SUPPLY #2821~
          N3*2135 EAST HIGHWAY 54~
          N4*LINTON*IN*47441*US~
          L3*25000*G*750.00********0~
          SE*28*0298~
           */          
     }     
?>