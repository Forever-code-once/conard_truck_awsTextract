<?
				require "Services/Twilio.php";
			
				// Set our AccountSid and AuthToken
				$AccountSid = "AC684a891a4dc7476487da40cb6ae56c3f";
				$AuthToken = "7dda51ae17f6a60c9a24130cf1aa2dd7";
				// Instantiate a new Twilio Rest Client
				$client = new Services_Twilio($AccountSid, $AuthToken);
				/* Your Twilio Number or Outgoing Caller ID */
				$from= '1-615-513-1209';
				$to="1-615-513-1209";
				// make an associative array of server admins
				$call = $client->account->calls->create($from, $to, 'http://www.tbejail.com/twilio/test_callback.php?number=1-615-459-2921');
?>