<?
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<? include_once('application.php')?>
<?

check_mail();

echo "<p>done...";

function check_mail() {
	
	global $defaultsarray;
	
	/* connect to gmail */
	$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
	$username = 'kruger@conardwd.com';
	$password = 'con!!ard';
	
	/* try to connect */
	//$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

	$inbox = @imap_open($hostname, $username, $password);
	if (!$inbox) {
		echo "<strong>IMAP Error:</strong><br>";
		print_r(imap_errors());
		exit;
	}
	
	/* grab emails */
	$emails = imap_search($inbox,'ALL');
	
	/* if emails are returned, cycle through each... */
	if($emails) {
		
		/* begin output var */
		$output = '';
		
		/* put the newest emails on top */
		//rsort($emails);
		
		/* for every email... */
		foreach($emails as $email_number) {
			
			/* get information specific to this email */
			$overview = imap_fetch_overview($inbox,$email_number,0);
			//$message = imap_fetchbody($inbox,$email_number,2);
			$msg_struct = imap_fetchstructure($inbox, $email_number);

			$part_count = count($msg_struct->parts);
			
			if($part_count == 2) {
				
				for($p=1;$p<999999;$p++) {
					$tFile = time()."$p.pdf";
					$tFileFull = "kruger/$tFile";
					if(!file_exists($tFileFull)) break;
				}

				$attachment_contents = imap_fetchbody($inbox, $email_number, "2");
				$fh = fopen($tFileFull, "w");
				fwrite($fh, imap_base64($attachment_contents));
				fclose($fh);
				
				imap_mail_move($inbox, $email_number, "Processed");
			}
		}
	} 
	
	/* close the connection */
	imap_close($inbox);
}
?>