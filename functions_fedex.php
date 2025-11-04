<?php
	function send_fedex_edi($remote_path, $local_path, $file, $load_id=0,$reporter=0) 
	{
		global $defaultsarray;
		
		$edi_sftp_server = $defaultsarray['fedex_sftp_address'];
		$edi_sftp_username = $defaultsarray['fedex_sftp_username'];
		$edi_sftp_password = $defaultsarray['fedex_sftp_password'];
			
			
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
			
			mrr_add_fedex_edi_invoicing_log(0,$load_id,0,'Response Received...updating load. Result: '.trim($result).'.',1,$rslt,0,0);
			
			if($load_id > 0)
			{	//good, so clear the log.
				$sql = "
					update load_handler set					
						fedex_edi_error_log = '',
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
			mrr_add_fedex_edi_invoicing_log(0,$load_id,0,'Bad Response: '.addslashes(str_replace("'","",trim($error_no))).'. Result: '.trim($result).'.',1,$rslt,0,0);	
			
			if($load_id > 0)
			{	//failed, so record why if load is given.
				$sql = "
					update load_handler set					
						fedex_edi_error_log = '".addslashes(str_replace("'","",trim($error_no)))."'
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
	function mrr_resend_missing_fed_ex_edi_responses($dater="",$cd=0)
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
     			and koch_edi <= 0
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
               
     		if($row['fedex_edi_conard_warning']=="0000-00-00 00:00:00")
     		{     			
     			$email_from="system@conardtransportation.com";	$from_name="Conard FedEx EDI";     			
     			$email_to="dconard@conardtransportation.com";	$email_name="Dale";
     			$cc=$defaultsarray['special_email_monitor'];			$cc_name="Master Jedi";
     			$cc2="trucking@conardtransportation.com";			$cc_name2="Dispatch Team";
     			
     			$subject="WARNING: FedEx EDI load created, but no response sent for Load ".$row['id']." (FedEx ".trim($row['load_number']).")";
     			
     			$body="<br>
     				Dispatch Team,
     				<br><br>
     				<b>FedEx EDI Issue:</b> Conard Load <a href='https://trucking.conardtransportation.com/manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a> was created from FedEx Load ".trim($row['load_number']).", 
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
     				<b>Input File:</b>  ...".trim($row['fedex_edi_input_file'])."".trim($row['fedex_edi_output_file'])."</a>.
     				<br>
     				Error Log: ".trim($row['fedex_edi_error_log'])."
     				<br>
     				Warning Sent: ".($row['fedex_edi_conard_warning']!="0000-00-00 00:00:00" ? "".date("m/d/Y H:i:s",strtotime($row['fedex_edi_conard_warning']))."" : "".date("m/d/Y H:i:s",time())."")."
     				
     				<br><br><br>Auto-generated Message, so please do not reply... unless you do not want anyone to read your reply.
     			";		//C:\\web\\"."trucking.conardlogistics.com
     			
     			$did_send=mrr_trucking_sendMail_PN($email_to,$email_name,$email_from,$from_name,$subject,$body);	
     			if($cc!=$email_to)		$did_send=mrr_trucking_sendMail_PN($cc,$cc_name,$email_from,$from_name,$subject,$body);	
     			if($cc2!=$email_to)		$did_send=mrr_trucking_sendMail_PN($cc2,$cc_name2,$email_from,$from_name,$subject,$body);	
     			
     			$sql = "
     				update load_handler set					
     					fedex_edi_conard_warning = now()
     				where id = '".sql_friendly($row['id'])."'
     			";
     			simple_query($sql);
     		}
     		
     		
     		//try to resend the file
     		if(trim($row['fedex_edi_input_file'])!="" && trim($row['fedex_edi_output_file'])!="")
     		{          		
          		$complete_path="C:\\web\\"."trucking.conardlogistics.com".trim($row['fedex_edi_input_file'])."";
          		
          		if(send_fedex_edi('/FXFE/990/', $complete_path, trim($row['fedex_edi_output_file']),$row['id'],$cd))
          		{
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
     				<td valign='top'>".trim($row['fedex_edi_output_file'])."</td>
     				<td valign='top'>".trim($row['fedex_edi_error_log'])."</td>  
     				<td valign='top'>".($row['fedex_edi_conard_warning']!="0000-00-00 00:00:00" ? "".date("m/d/Y H:i:s",strtotime($row['fedex_edi_conard_warning']))."" : "")."</td>   				
     				<td valign='top'>".$status."</td>
     			</tr>
     		";		//".($zone_name=="Add Zone" ? "<a href='sql_mrr.php?make_zone=".$row['id']."' target='_blank'>".$zone_name."</a>" : "<a href='sql_mrr.php?make_zone=".$row['id']."' target='_blank'>".$zone_name."</a>")."
     				
     		$cntr++;
     	}
     	$tab.="</table>";
     	if($cd>0)		$tab.="<br>Query was: ".$sql." <br>";
		return $tab;
	}
?>