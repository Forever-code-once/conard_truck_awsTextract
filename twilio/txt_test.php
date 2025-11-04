<?

die('aborted...');

	// Include the PHP TwilioRest library
	require "Services/Twilio.php";

	// Set our AccountSid and AuthToken
	$AccountSid = "AC684a891a4dc7476487da40cb6ae56c3f";
	$AuthToken = "7dda51ae17f6a60c9a24130cf1aa2dd7";
	// Instantiate a new Twilio Rest Client
	$client = new Services_Twilio($AccountSid, $AuthToken);
	/* Your Twilio Number or Outgoing Caller ID */
	$from= '1-615-685-4201';
	// make an associative array of server admins
	$people = array(
		"16157671689"=>"Michael"
	);
	// Iterate over all our server admins
	foreach ($people as $to => $name) {
		// Send a new outgoinging SMS by POST'ing to the SMS resource */
		// YYY-YYY-YYYY must be a Twilio validated phone number
		$body = "Bad news $name, the server is down and it needs your help";
		$client->account->sms_messages->create($from, $to, $body);
		echo "Sent message to $name";
	}
?>