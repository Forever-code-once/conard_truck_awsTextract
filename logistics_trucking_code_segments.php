<?php
//JSON to create autoload file.
/*
{
    "name": "bill/onsite",
    "description": "billbrewingtin.com",
    "require": {
        "wrseward/pdf-parser": "0.1.0",
        "google/apiclient": "^2.0",
        "authorizenet/authorizenet": "1.9.0",
        "jms/serializer": "dev-serializer-master as 1.0",
        "phpmailer/phpmailer": "~5.2"
    },
    "repositories": [{  
        "type": "vcs",
        "url": "https://github.com/goetas/serializer.git"
    }],
    "autoload": {
        "files": [
                "config.php",
                "functions.php"
        ]
    },
    "minimum-stability": "stable"
}
*/

require 'vendor/autoload.php';
session_start();
//////////////////////////////////////////////////////////////////////////////////
// download status update emails with PDF files, parse them, then
// update our database with some of the information
//////////////////////////////////////////////////////////////////////////////////
$service_account = 'emailscanner@advance-block-144020.iam.gserviceaccount.com';
$userid = 'status@billbrewington.com';
$client = new Google_Client();
$client->setApplicationName('billbrewington');
$client->setAuthConfig('service_account.json');
$client->setScopes(array(
    'https://www.googleapis.com/auth/gmail.modify',
));
$client->setSubject($app['email']['username']);
$gmail = new Google_Service_Gmail($client);
function parseMessages($service, $userid) {
    
    // Get the user's labels -- we're looking for the id for the archived label
//  echo '<h1>Labels</h1>';
//  $labels = array();
//  $labels_response = $service->users_labels->listUsersLabels($userid);
//  if ($labels_response->getLabels()) {
//      $labels = array_merge($labels, $labels_response->getLabels());
//      foreach ($labels as $labelObj) {
//          $label_id = $labelObj->getId();
//          echo $label_id.'<br>';
//          $label = $service->users_labels->get($userid, $label_id);
//          debug($label);
//      }
//  }
    // Get a list of the messages
    $messages = array();
    $opt_param = array(
        'q' => 'label:INBOX has:attachment'
    );
    
    $messages_response = $service->users_messages->listUsersMessages($userid, $opt_param);
    if ($messages_response->getMessages()) {
        $messages = array_merge($messages, $messages_response->getMessages());
        $files = array();
        
        // Loop through the messages
        foreach ($messages as $messageObj) {
            $message_id = $messageObj->getId();
            echo $message_id.'<br>';
            
            $message = $service->users_messages->get($userid, $message_id);
            $payload = $message->getPayload();
            $payload_parts = $payload->getParts();
            $payload_headers = $payload->getHeaders();
            $subject = '';
            foreach ($payload_headers as $header) {
                if ($header->getName() == 'Subject') {
                    $subject = $header->getValue();
                    break;
                }
            }
            echo $subject.'<br>';
            foreach ($payload_parts as $part) {
                $filename = $part->getFilename();
                if ($filename != '') {
                    $attachment_id = $part->getBody()->getAttachmentId();
                    $attachment_response = $service->users_messages_attachments->get($userid, $message_id, $attachment_id);
                    $file_data = $attachment_response->getData();
                    
                    // Write file to temp dir
                    $file_data = str_replace('-', '+', $file_data);
                    $file_data = str_replace('_', '/', $file_data);
                    if (file_put_contents('temp/'.$filename, base64_decode($file_data))) {
                        $case = pdfparser('temp/'.$filename);
                        process_case($case);
                        
                        // Get new labels for "archiving" this message
                        $archive_label = $service->users_labels->get($userid, 'Label_1');
                        $inbox_label = $service->users_labels->get($userid, 'INBOX');
                        
                        // Archive this message
                        
                        $mods = new Google_Service_Gmail_ModifyMessageRequest();
                        
                        $mods->setAddLabelIds(array('Label_1'));
                        $mods->setRemoveLabelIds(array('INBOX'));
                        $archived = $service->users_messages->modify($userid, $message_id, $mods);
                        //exit;
                    }
                }
            }
        }
    }
}
parseMessages($gmail, $app['email']['username']);
echo 'done';
exit;
// sample files
$file_appt = "test\\no_appt.pdf";
$file_no_appt = "test\\appt.pdf";
$case = pdfparser($file_appt);
//d($case);
// process the case and update our applicants
process_case($case);
function process_case($case) {
    //debug($case);
    // take a case element from a parsed PDF file
    // and update the applicant information on our side
    
    if(stripos($case['case'], 'web') === false) {
        // all case numbers should have the word "web" in front of the numbers
        // if that doesn't exist, then we don't have a valid case number - exit out
        return false;
    }
    
    
    $sql_message = "";
    for($i=1;$i<=20;$i++) {
        $sql_message .= "
            `message $i date` as message_".$i."_date,
            `message $i` as message_".$i.",
        ";
    }
    
    // make sure we can locate this case
    $sql = "
        select $sql_message
            `sequence no` as sequenceno 
        from applicants
        where ticket_number = '".sql_friendly($case['case'])."'
    ";
    $data = simple_query($sql);
    if(!mysqli_num_rows($data)) {
        // could not locate case
        echo "Error: Could not locate case!";
        return false;
    } else {
        $row = mysqli_fetch_array($data);
        echo "Found case!<br>";
        echo "Sequence #: $row[sequenceno]<br>";
        
        // find the first unused history entry we can use
        for($i=1;$i<=20;$i++) {
            if(strtotime($row['message_'.$i.'_date']) == 0) {
                // found an empty history entry we can use
                echo "History entry $i available<br>";
                break;
            }
        }
        
        // appointment was set
        if($case['appointment'] != '') {
            // break the appointment into date and time and update the row
            $date = date('Y-m-d', strtotime($case['appointment']));
            $time = date('Y-m-d H:i:s', strtotime($case['appointment']));
            $appointment = "
                `date scheduled` = '". sql_friendly($date) ."',
                `time scheduled` = '". sql_friendly($time) ."',
                `current status` = 'SCHEDULED',
            ";
            $case['message'] = 'SCHEDULED: '. $case['appointment'] .'. '. $case['message'];
        } else {
            $appointment = '';
        }
        
        // date cancelled
        if ($case['cancelled'] != '') {
            $cancelled = "
                `current status` = 'CANCELLED PER ORDERING FACILITY',
                `date scheduled` = null,
                `time scheduled` = null,
            ";
        } else {
            $cancelled = "";
        }
        
        // completed
        if (strpos($case['message'], "Our Examiner has completed") !== false) {
            $completed = "
                `date completed` = now(),
                `current status` = 'COMPLETED',
            ";
        } else {
            $completed = "";
        }
        
        // update note section here     
        $sql = "
            update applicants
            set 
                ". $completed ."
                ". $appointment ."
                ". $cancelled ."
                `message ".$i." date` = now(),
                `message $i` = '".sql_friendly($case['message'])."'
            where `sequence no` = '".sql_friendly($row['sequenceno'])."'
        ";
        //echo $sql.'<br>';
        simple_query($sql);
    }
}
function pdfparser($file) {
    
    // load the PDF extraction library, which uses the pdftotext.exe program
    // the -layout parameter just makes sure to keep the layout intact when reading the text out
    $parser = new \Wrseward\PdfParser\Pdf\PdfToTextParser(getcwd()."\\..\\pdftotext\\bin32\\pdftotext -layout");
    $parser->parse($file);
    $text = $parser->text();
    // parse out the pieces we need into easily accessible array elements
    $info = array();
    $info['agent'] = get_text_block($text, "Agent Name ", "Message ");
    $info['email'] = get_text_block($text, "Email: ", chr(13));
    $info['message'] = str_replace("  ", "", str_replace(chr(13), "", str_replace(chr(10), "", get_text_block($text, "Message ", "Case Number "))));
    $info['client'] = get_text_block($text, "Client ", chr(13));
    $info['insurance'] = get_text_block($text, "Insurance Co.", chr(13));
    $info['case'] = get_text_block($text, "Case Number ", chr(13));
    $info['labworkto'] = get_text_block($text, "Labwork to ", chr(13));
    $info['paperworkto'] = get_text_block($text, "Paperwork to ", chr(13));
    $info['requirements'] = get_text_block($text, "Requirements Paperwork: ", "Labwork to ");
    $info['appointment'] = get_text_block($text, "Appointment Date ", "Requirements Paperwork: ");
    $info['cancelled'] = get_text_block($text, "Date Cancelled ", "Requirements Paperwork: ");
    $info['original_text'] = $text;
    return $info;
}
function get_text_block($text, $start_text, $end_text) {
    // get a text area using a starting and ending text
    // $test = full text string we'll be extracting the search phrase from
    
    // find the starting position of our search text
    $startpos = strpos($text, $start_text);
    
    // if we couldn't locate our search term, return out
    if(!$startpos) return "";
    
    // end position (needed for our substring)
    $endpos = strpos($text, $end_text, $startpos);
    
    return trim(substr($text, $startpos + strlen($start_text), $endpos - $startpos - strlen($start_text)));
}

?>
<?

	$message_array = array();
	
	$message_array[] = '
[Image removed by sender.]

MX Logistics is the NEW Mario\'s Express Service For Truck availability, please contact Frank 732-952-8019 or Fvagueiro@mxlogistics.com David 732-95


[Image removed by sender. Mxlogistics logo]<http://sable.madmimi.com/c/13378?id=1441147.7900.1.0f3ab85a32b24fde7401134dab553722>



[Image removed by sender. LineOfTrucks]



MX Logistics is the NEW Mario\'s Express Service

For Truck availability, please contact
Frank 732-952-8019 or Fvagueiro@mxlogistics.com
David 732-952-8034 or Dharing@mxlogistics.com

48\' & 53\' LIFTGATE TRAILERS AND 53\' AIR RIDE LOGISTIC VANS

02/07/17
Waco, TX 53\' Dry Van EMPTY
Cedar Falls, IA 53\' Liftgate Trailer EMPTY

02/09/17
Erlanger, KY 53\' Dry Van

02/13/17
Carson, CA 53\' Dry Van
Pheonix, AZ 53\' Dry Van






©2017 Mario\'s Express Service Inc | 45 Fernwood Ave Edison. NJ
	';
	
	$message_array[] = '
	TODAY 2/7
Indianapolis IN
Atlanta IN
Terre Haute IN (late truck)
Louisville KY

WEDNESDAY 2/8
Lebanon TN
Indianapolis IN
Terre Haute IN (late truck)
Louisville KY

';
	
	$message_array[] = '
4264

Memphis,TN/

2/7

TN/GA/VA

16242

Jacksonville,FL/

2/8

ATLANTA,GA

4272

Watertown,TN/

2/8

KNOXVILLE,TN

16241

Greensboro,NC/

2/8

TN/GA/AL/KY

268

Zachary,LA/

2/8

KNOXVILLE,TN

12101

Stone Mountain,GA/

2/8

NC/VA

4256

Millry,AL/

2/8

TN/GA/SC/NC/VA

16234

Zebulon,NC/

2/8

TN/GA/AL/KY

284

Mechanicsville,VA/Han

2/8

TN/GA/AL/KY

16245

Zebulon,NC/

2/8

TN/GA/AL/KY';


process_message($message_array[0]);

foreach($message_array as $message) {
	//process_message($message);
}

function process_message($str) {
	
	echo "<pre>$str</pre>";
	
	// scan through the lines until we find a date
	$line_array = explode(chr(10), $str);
	
	foreach($line_array as $line) {
		if(strtotime($line) > 0) {
			echo "$line<br>";
		}
	}
	
}




?>