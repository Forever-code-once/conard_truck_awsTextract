<?php
$show_errors = true;

	include('application.php');
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	phpinfo();
exit;

	send_twilio_messages('6159714294','testing new sms send pt 2');
	
	
	
	die('nono');
	require 'vendor/autoload.php';
use Twilio\Rest\Client;

// Your Account SID and Auth Token from twilio.com/console
// To set up environmental variables, see http://twil.io/secure
$account_sid = "ACad25c862951f15bd8dd11ca7e14aad5a";
$auth_token = "dd83d49fd9e81b72b80f7d8bec1767dc";
// In production, these should be environment variables. E.g.:
// $auth_token = $_ENV["TWILIO_AUTH_TOKEN"]

// A Twilio number you own with SMS capabilities
$twilio_number = "+16292190125";

$pre = 'Conard Trucking: ';
$body = $pre.'this is a test..';
$post = "\n\nReply STOP to unsubscribe.";
$body .= $post;

$client = new Client($account_sid, $auth_token);
$client->messages->create(
    // Where to send a text message (your cell phone?)
    '6159714294',
    array(
        'from' => $twilio_number,
        'body' => $body
    )
);


	die('xyhere');
	
	
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script type="text/javascript">
	// Get the datetime of last sync
	// Connect to google mailbox
	// Get most recent messages
	// Fetch the first message
	var files = [];
	var CLIENT_ID = '485237769168-0tlpjtpbu1kvlssqim12qpqsc3brq3dl.apps.googleusercontent.com';
	var SCOPES = ['https://www.googleapis.com/auth/gmail.modify'];
	var messages = {};
	var file_pointer = 0;
	
	function checkAuth() {
		gapi.auth.authorize(
	      {
	        'client_id': CLIENT_ID,
	        'scope': SCOPES.join(' '),
	        'immediate': true
	      }, handleAuthResult);
	}
	
	/**
	* Handle response from authorization server.
	*
	* @param {Object} authResult Authorization result.
	*/
	function handleAuthResult(authResult) {
		var authorizeDiv = document.getElementById('authorize-div');
		if (authResult && !authResult.error) {
	  		// Hide auth UI, then load client library.
	  		authorizeDiv.style.display = 'none';
	  		loadGmailApi();
	  		//setTimeout(process_files, 4000);
	  		setTimeout(func, 4000);

		} else {
	  		// Show auth UI, allowing the user to initiate authorization by
	  		// clicking authorize button.
	  		authorizeDiv.style.display = 'inline';
		}
	}

	/**
	* Initiate auth flow in response to user clicking authorize button.
	*
	* @param {Event} event Button click event.
	*/
	function handleAuthClick(event) {
		gapi.auth.authorize({
			client_id: CLIENT_ID, 
			scope: SCOPES, 
			immediate: false
		},handleAuthResult);
		return false;
	}
	
	function pause() {
		var nothing = '';
		// do nothing
	}
	
	function func() {
		// do nothing
		//console.debug(files);
		
		// Send file data via ajax
		for (var m = 0; m < files.length; m++) {
			file_pointer = m;
			process_file(files[m]);
		}
	}
	
	function add_file(up, file_data) {
		//console.log('add file');
		//console.debug(up);
		//console.debug(file_data);
	}
	
	function message_details(msg) {
		//console.log('message details');
		
		var subject = '';
						
		// Get the subject
		for (var k = 0; k < msg.payload.headers.length; k++) {
			if (msg.payload.headers[k].name == 'Subject')
				subject = msg.payload.headers[k].value;
		}
		
		// Check for file(s)
		for (var j = 0; j < msg.payload.parts.length; j++) {
			if (j > 0) {
				if (msg.payload.parts[j].filename != '') {
					var file_name = msg.payload.parts[j].filename;
					var file_id = msg.payload.parts[j].body.attachmentId;
					var file_data = '';
					var message_id = msg.id;
					file_obj = {
						'file_name': file_name,
						'file_id': file_id,
						'message_id': message_id,
						'subject': subject,
						'file_data': ''
					}
					files.push(file_obj);
					//console.debug(files);
				} else {
					// Delete the message
					var trashRequest = gapi.client.gmail.users.messages.trash({
						'id': msg.id,
						'userId': 'me'
					});
				 	trashRequest.execute();
				}
			}
		}
	}
	
	function process_file(file) {
		//console.log('do process');
		//console.debug(file);
		
		var fileRequest = gapi.client.gmail.users.messages.attachments.get({
			'id':file['file_id'],
			'messageId': file['message_id'],
			'userId': 'me'
		});
		fileRequest.execute(function(result){
			//console.log('got file data');
			//console.debug(result);
			//console.debug(file);
			file['file_data'] = result.data;
			//console.debug(file);
			
			setTimeout(pause,4000);
			//console.log('process files done');
			
			
			$.ajax({
				url: "gmail_api_ajax.php",
				dataType: "json",
				type: "post",
				async: false,
				data: {
					'cmd': 'process_file',
					'filename': file['file_name'],
					'file_data': file['file_data'],
					'subject': file['subject']
				},
				error: function(data) {
					console.log('er proc file');
					//console.debug(data);
	
				},
				success: function(data) {
					//console.log('good');
					//console.debug(data);
					
					// Delete the message
					var trashRequest = gapi.client.gmail.users.messages.trash({
						'id': file['message_id'],
						'userId': 'me'
					});
				 	trashRequest.execute();
				 	//console.log('trashed');
				}
			});
			setTimeout(pause, 4000);

		});
	}
	
	function set_file(file_name, file_data) {
		//console.debug(file_name);
		//console.debug(file_data);
	}
	
	function displayInbox() {
		var upload_files = {};
		var request = gapi.client.gmail.users.messages.list({
			'userId': 'me',
			'labelIds': 'INBOX',
			'maxResults': 100
		});
	
		request.execute(function(response) {
			if (response.messages.length) {
				for (var i = 0; i < response.messages.length; i++) {
					var message_id = response.messages[i]['id'];
	
					var messageRequest = gapi.client.gmail.users.messages.get({
						'userId': 'me',
						'id': response.messages[i]['id']
					});
					messageRequest.execute(message_details);
				}
			}
			//setTimeout(function(){ window.close();}, 40000);
			
		});
	}
	
	/**
	* Load Gmail API client library. List labels once client library
	* is loaded.
	*/
	function loadGmailApi() {
		gapi.client.load('gmail', 'v1', displayInbox);
		return false;
	}
		
	/**
	* Append a pre element to the body containing the given message
	* as its text node.
	*
	* @param {string} message Text to be placed in pre element.
	*/
	function appendPre(message) {
		var pre = document.getElementById('output');
		var textContent = document.createTextNode(message + '\n');
		pre.appendChild(textContent);
	}
	
</script>
<script src="https://apis.google.com/js/client.js?onload=checkAuth"></script>
 <div id="authorize-div" style="display: none">
  <span>Authorize access to Gmail API</span>
  <!--Button for the user to click to initiate auth sequence -->
  
  <button id="authorize-button" onclick="handleAuthClick(event)">
    Authorize
  </button>
</div>
<pre id="output"></pre>
  