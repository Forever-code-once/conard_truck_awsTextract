<? include('application.php') ?>
<?php

require_once "twilio/Services/Twilio.php";

		// Set our AccountSid and AuthToken
		$AccountSid = "AC684a891a4dc7476487da40cb6ae56c3f";
		$AuthToken = "7dda51ae17f6a60c9a24130cf1aa2dd7";
		// Instantiate a new Twilio Rest Client


    /* connect to gmail */
    $hostname = '{imap.gmail.com:993/ssl}INBOX';
    $username = 'alerts@conardlogistics.com';
    $password = 'Con!ard5000';

    /* try to connect */
    $inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

    /* grab emails */
    $emails = imap_search($inbox,'ALL');

    /* if emails are returned, cycle through each... */
    if($emails) {

      /* begin output var */
      $output = '';

      /* put the newest emails on top */
      rsort($emails);

      /* for every email... */
      
      $i = 0;
      foreach($emails as $email_number) {
		$i++;
        /* get information specific to this email */
        $overview = imap_fetch_overview($inbox,$email_number,0);
        $message = imap_fetchbody($inbox,$email_number,2);

        
        /*
        echo "<pre>";
        var_dump($overview);
        echo "Message ID: (".$overview[0]->message_id.")";
        echo "</pre>";
        */

        /* output the email header information */
        $output.= '<tr><td>'.($overview[0]->seen ? 'read' : 'unread').'</td>';
        $output.= '<td>'.$overview[0]->subject.'</td> ';
        $output.= '<td>'.$overview[0]->from.'</td>';
        $output.= '<td>on '.$overview[0]->date.'</td></tr>';

        /* output the email body */
        //$output.= '<div class="body">'.$message.'</div>';
        
        //imap_mail_move($inbox, $i, "processed");
        
        $sql = "
        	insert into log_email_alerts
        		(imap_message_id,
        		linedate_added,
        		subject,
        		message_from,
        		deleted)
        		
        	values ('".sql_friendly($overview[0]->message_id)."',
        		now(),
        		'".sql_friendly($overview[0]->subject)."',
        		'".sql_friendly($overview[0]->from)."',
        		0)
        ";
        simple_query($sql);
        $mid = mysqli_insert_id($datasource);
        
		$client = new Services_Twilio($AccountSid, $AuthToken);

		$use_caller_id = '1-615-209-9029';
		$to='1-615-513-1209';
		
		
		// send the phone call alert
		$call = $client->account->calls->create($use_caller_id, $to, 'http://trucking.conardlogistics.com/alert_call_handler.php?phone_to='.$to.'&mid='.$mid,array(
			
		));
		
		
		// send the txt message alert
		/* Your Twilio Number or Outgoing Caller ID */
		// make an associative array of server admins
		$body = "VendEngine Deposit Receipt: Inmate: ".get_inmate_name_by_id($_POST['inmate_id']).". ";
		$body .= "Charged: ".money_format($total_charge)." | after fees: ".money_format($inmate_amount)."";
		$body .= " | ID: $trans_id";
		$client->account->sms_messages->create($use_caller_id, $to, $body);					
		
        
        
      }
	echo "<table>
		<tr>
			<td>Seen</td>
			<td>Subject</td>
			<td>From</td>
			<td>Date</td>
		</tr>
	";
	echo $output;
	echo "</table>";
    } 
    

    /* close the connection */
    imap_close($inbox);
    ?>