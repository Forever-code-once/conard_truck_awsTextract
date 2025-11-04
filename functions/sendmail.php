<?

	function sendMail($From,$FromName,$To,$ToName,$Subject,$Text,$Html,$AttmFiles = '') {
		/* mail using PHPMailer */
		global $defaultsarray;
		
		
		if(isset($defaultsarray['disable_email']) && $defaultsarray['disable_email'] == 'TRUE') {
			// e-mails turned off by global settings
			return;
		}
		
		
		$mail = new PHPMailer();
		$mail->IsSMTP(); // telling the class to use SMTP
		if(isset($defaultsarray['smtp_host']) && $defaultsarray['smtp_host'] != '') {
			$mail->Host = $defaultsarray['smtp_host']; // SMTP server
			if($defaultsarray['smtp_username'] != '') {
				$mail->SMTPAuth = true;
				$mail->Username = $defaultsarray['smtp_username'];
				$mail->Password = $defaultsarray['smtp_password'];
			}
		}
		$mail->From = $From;
		$mail->FromName = $FromName;
		
		// add support to process multiple e-mail addresses CSV
		$to_array = explode(",",$To);
		
		foreach($to_array as $value) {
			$mail->AddAddress($value);
		}
		
		$mail->AltBody = $Text;
		
		$mail->Subject = $Subject;
		$mail->Body = $Html;
		$mail->WordWrap = 50;
		
		if($AttmFiles != '') {
			$mail->AddAttachment($AttmFiles);
		}
		
		if(!$mail->Send())
		{
		   echo 'Message was not sent.';
		   echo 'Mailer error: ' . $mail->ErrorInfo;
		   
		}
		else
		{
		   //echo 'Message has been sent.';
		}
		
	}

	function sendMail_old($From,$FromName,$To,$ToName,$Subject,$Text,$Html,$AttmFiles) {
		/* old - non reliable mail code */
		global $rootpath;
		$OB="----=_OuterBoundary_000";
		$IB="----=_InnerBoundery_001";
		$Html=$Html?$Html:preg_replace("/\n/","{br}",$Text) 
			or die("neither text nor html part present.");
		$Text=$Text?$Text:"Sorry, but you need an html mailer to read this mail.";
		$From or die("sender address missing");
		$To or die("recipient address missing");
		
		$headers ="MIME-Version: 1.0\r\n"; 
		$headers.="From: ".$FromName." <".$From.">\n"; 
		$headers.="To: ".$ToName." <".$To.">\n"; 
		$headers.="Reply-To: ".$FromName." <".$From.">\n"; 
		$headers.="X-Priority: 1\n"; 
		$headers.="X-MSMail-Priority: High\n"; 
		$headers.="X-Mailer: My PHP Mailer\n"; 
		$headers.="Content-Type: multipart/mixed;\n\tboundary=\"".$OB."\"\n";
		//Messages start with text/html alternatives in OB
		$Msg ="This is a multi-part message in MIME format.\n";
		$Msg.="\n--".$OB."\n";
		$Msg.="Content-Type: multipart/alternative;\n\tboundary=\"".$IB."\"\n\n";
		//plaintext section 
		$Msg.="\n--".$IB."\n";
		$Msg.="Content-Type: text/plain;\n\tcharset=\"iso-8859-1\"\n";
		$Msg.="Content-Transfer-Encoding: quoted-printable\n\n";
		// plaintext goes here
		$Msg.=$Text."\n\n";
		// html section 
		$Msg.="\n--".$IB."\n";
		$Msg.="Content-Type: text/html;\n\tcharset=\"iso-8859-1\"\n";
		$Msg.="Content-Transfer-Encoding: base64\n\n";
		// html goes here 
		$Msg.=chunk_split(base64_encode($Html))."\n\n";
		// end of IB
		$Msg.="\n--".$IB."--\n";
		// attachments
		if($AttmFiles)
			{
			$patharray = explode ("/", $AttmFiles); 
			$FileName=$patharray[count($patharray)-1];
			
			$Msg.= "\n--".$OB."\n";
			$Msg.="Content-Type: application/octetstream;\n\tname=\"".$FileName."\"\n";
			$Msg.="Content-Transfer-Encoding: base64\n";
			$Msg.="Content-Disposition: attachment;\n\tfilename=\"".$FileName."\"\n\n";
	
			//file goes here
			$fd=fopen($AttmFiles, "r");
			$FileContent=fread($fd,filesize($AttmFiles));
			fclose($fd);
			$FileContent=chunk_split(base64_encode($FileContent));
			$Msg.=$FileContent;
			$Msg.="\n\n";
			}

		//message ends
		$Msg.="\n--".$OB."--\n";
		mail($To,$Subject,$Msg,$headers); 
		//syslog(LOG_INFO,"Mail: Message sent to $ToName <$To>");
		}
?>
