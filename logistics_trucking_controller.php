<?php
if(!isset($_GET['connect_key']) || $_GET['connect_key'] != 'bas82bad98fqhbnwga8shq34908asdhbn') 
{
	die("You have reached this page incorrectly.");
}

require 'vendor/autoload.php';

include_once('logistics_trucking_functions.php');

session_start();

//$service_account = 'availabletrucks@trucksavailable-160822.iam.gserviceaccount.com';
$app_name = 'availabletrucks';


$userid = 'truckavailable@conardlogistics.com';
echo "<br>User ID: <b>".$userid."</b>.<br>";
$http = new GuzzleHttp\Client(['verify' => false]);

$client = new Google_Client();
$client->setHttpClient($http);

$client->setApplicationName($app_name);
//$client->setAuthConfig('service_account.json');
//$client->setAuthConfig('client_secret_101001145148148952019.json');
$client->setAuthConfig('trucksavailable-24f74fbfbb5d.json');

$client->setScopes(array('https://mail.google.com/',));
$client->setSubject($userid);

$gmail = new Google_Service_Gmail($client);


// Get a list of the messages
$messages = array();
$opt_param = array();
/*
$labels = array();

$labelsResponse = $gmail->users_labels->listUsersLabels($userid);
if ($labelsResponse->getLabels()) 
{
      $labels = array_merge($labels, $labelsResponse->getLabels());
}
$lab_cntr=0;
foreach($labels as $label)
{
	echo "<br>Label ".$lab_cntr.": ".$label->getId()." ".$label->getName()."";	//
	$lab_cntr++;
} 
*/

$archive_label="N/A";
$inbox_label="N/A";
$unread_label="N/A";

//labels for messages
$mrr_lab_id[0]="INBOX";		$mrr_lab_name[0]="INBOX";
$mrr_lab_id[1]="TRASH";		$mrr_lab_name[1]="TRASH";
$mrr_lab_id[2]="UNREAD";		$mrr_lab_name[2]="UNREAD";
$mrr_lab_id[3]="IMPORTANT";	$mrr_lab_name[3]="IMPORTANT";
$mrr_lab_id[4]="Label_1";	$mrr_lab_name[4]="Keep Box";
$mrr_lab_id[5]="Label_2";	$mrr_lab_name[5]="Processed";
$mrr_lab_id[6]="Label_3";	$mrr_lab_name[6]="Problems";
$mrr_lab_id[7]="Label_4";	$mrr_lab_name[7]="test";

$archive_label = $mrr_lab_id[5];
$inbox_label = $mrr_lab_id[0];
$unread_label = $mrr_lab_id[2];


echo "<br>".$mrr_lab_name[5]." Label: ".$archive_label." | ".$mrr_lab_name[2]." Label ".$unread_label.". | ".$mrr_lab_name[0]." Label ".$inbox_label.".<br>";


$opt_param["includeSpamTrash"]=false;
$opt_param["labelIds"]=$inbox_label;


$msg_cntr=0;
$msgs = $gmail->users_messages->listUsersMessages($userid, $opt_param);	
//echo "<br>MSG DUMP:<pre>".var_dump($msgs)."</pre>";

$cur_msg_id="";

echo "<br>MSG LIST:<br>";
foreach($msgs as $msg) 
{
     $msg_id=trim((string) $msg["id"]);
     $thread_id=trim((string) $msg["threadId"]);
     
     $cur_msg_id="";
     //if($msg_cntr==0)		
     $cur_msg_id=trim($msg_id);
          
     if($cur_msg_id!="")
     {
     	try 
     	{	//(userID,msgID,format,metadataHeader)
         		$curmsg=$gmail->users_messages->get($userid, $cur_msg_id);	//listUsersMessages
         		print "<br>Message with ID: ".$cur_msg_id." retrieved.";
         		
         		$from="";
         		$date="";
         		$to="";
         		$company="";
         		$subject="";
         		$body="";
         		
         		$snippet=trim((string) $curmsg->snippet);
         		$pos1=strpos($snippet,"From:");
         		$pos2=strpos($snippet,"Sent:");
         		$pos3=strpos($snippet,"To:");
         		$pos4=strpos($snippet,"Subject:");
         		
         		$from=substr($snippet,($pos1+5), ($pos2 - ($pos1 + 5)));
         		$date=substr($snippet,($pos2+5), ($pos3 - ($pos2 + 5)));
         		$to=substr($snippet,($pos3+3), ($pos4 - ($pos3 + 3)));
         		$sub=substr($snippet,($pos4+8));
         		
         		if(substr_count($from,"[mailto:") > 0)
         		{
         			$pos5=strpos($from,"[mailto:");
         			$company=substr($from,0,$pos5);
         			$from=str_replace($company,"",$from);
         			$company=str_replace("From:","",$company);
         			
         			$from=str_replace("[mailto:","",$from);
         			$from=str_replace("]","",$from);
         		}
         		echo "<br>Company: ".trim($company).".";
         		echo "<br>From: ".trim($from).".";
         		echo "<br>Sent: ".trim($date).".";
         		echo "<br>To: ".trim($to).".";    		    		
         		
         		//echo "<br>Msg ID: ".$curmsg->id.".";
         		//echo "<br>Thread ID: ".$curmsg->threadId.".";
         		//echo "<br>Labels: <pre>".var_dump($curmsg->labelIds)."</pre>.";
         		//echo "<br>Snippet: ".$snippet.".";
         		//echo "<br>Headers: <pre>".var_dump($curmsg->payload->headers)."</pre>.";
         		
         		$sub_found=0;
         		foreach($curmsg->payload->headers as $key => $parts) 
         		{
         			//echo "<br><pre>".var_dump($parts)."</pre>.";	//".$key." | 
         			
         			foreach($parts as $key2 => $value2) 
         			{
         				if($key2=="name" || $key2=="value")
         				{
         					if($key2=="name" && $value2=="Subject")
         					{
         						$sub_found=1;
         						//echo "<br>---".$key2." | ".$value2.".";
         					}
         					elseif($sub_found > 0)
         					{
         						$sub=trim($value2);
         						$sub_found=0;
         						//echo "<br>---".$key2." | ".$value2.".";
         					}
         					//echo "<br>---".$key2." | ".$value2.".";
         				}
         			}
         		}
         		
         		echo "<br>Subject: ".trim($sub).".<br>";
         		
         		$body_cntr=0;
         		$size=0;
         		$body="";
         		//echo "<br>Body: <pre>".var_dump($curmsg->payload->parts)."</pre>.<br>";  
         		foreach($curmsg->payload->parts as $key => $parts) 
         		{
         			if($key==0)
         			{
              			//echo "<br>***".$key."***<br><pre>".var_dump($parts->body)."</pre><br>";
              			foreach($parts->body as $key2 => $value2) 
              			{
                   			//echo "<br>---".$key2." | ".$value2."<br>";
                   			if($key2=="size")
                   			{
                   				$size+=(int)trim((string) $value2);
                   			}
                   			if($key2=="data")
                   			{
         						$temp=trim((string) $value2);
         						//$temp=str_replace(" ","+",$temp);
         						$temp=str_replace("-","+",$temp);
         						$temp=str_replace("_","/",$temp);
         						
         						
         						$temp=base64_decode($temp);
         						
         						$temp=str_replace(chr(9),"<br>",$temp);
         						$temp=str_replace(chr(10),"<br>",$temp);
         						$temp=str_replace(chr(13),"<br>",$temp);
         						
         						//$temp=nl2br($temp);
         						
         						$body.="".$temp."";	
         						$body_cntr++;
                   			}
              			}
         			}
         		}
         		
         		echo "<br>Body [".$size."]: ".trim($body).".<br><hr><br>";  //<div style='width:1400px; height:500px; padding:10px; border:1px solid #00CC00; margin:10px; overflow:auto;'></div>
         		
         		
         		$poser=strpos($body,"Subject:");
         		if($poser > 0)
         		{
         			$body=substr($body,$poser);	
         		}
         		else
         		{       		
              		//$body=str_replace("[mailto:".$from."]","",$body);
              		//$body=str_replace("From: ".$company."","",$body);
         		}
         		if(strpos($body,"Subject:") > 0)
         		{
         			$body=str_replace("".$sub."","",$body);	
         			$body=str_replace("Subject:","",$body);
         		}
         		
         		$body=str_replace("Subject: ".$sub."","",$body);
         		$sub=str_replace("FW:","",$sub);
         		$body=str_replace("Subject: ".$sub."","",$body);
         		
         		if(trim($body)!="" && trim($from)!="" && 1==1)
         		{
         			$from=str_replace("'","ft",strip_tags(trim($from)));
	
				$sub=str_replace("53'","53ft",trim($sub));
				$sub=str_replace("48'","48ft",trim($sub));
				$sub=str_replace("'","",strip_tags(trim($sub)));
	
				$body=str_replace("'","ft",strip_tags(trim($body),"<br>"));
         			
         			$sql = "
          			insert into logistics_truck_emails 
          				(id,
          				linedate_added,
          				processed,
          				comp_id,
          				subject,
          				email_msg,
          				dispatch_warning,     					
          				email_address,
          				deleted)
          			values
          				(NULL,
          				NOW(),
          				0,
          				0,     					
          				'".$sub."',
          				'".$body."',
          				0,
          				'".$from."',
          				0)
          		";
          		mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
          		
          		if(1==1)
          		{
          			$mods = new Google_Service_Gmail_ModifyMessageRequest();
                    	    
                    	$mods->setAddLabelIds(array($archive_label));
                    	$mods->setRemoveLabelIds(array($inbox_label,$unread_label));
                    	$archived = $gmail->users_messages->modify($userid, trim($cur_msg_id), $mods);
          		}
          		$from="";	
				$sub="";	
				$body="";
         		}
         		
       	} 
       	catch (Exception $e) 
       	{
       	 	print "<br>An error occurred: ".$e->getMessage().".";
       	}
     
     }
             
     
     //echo "<br>".$msg_cntr.". ".$msg_id." | ".$thread_id.".";
     $msg_cntr++;
}
echo "<br>MSG LIST Complete.<br><br>";



/* 
{
 "id": "15ab4f9346f183c1",
 "threadId": "15ab4f9346f183c1",
 "labelIds": [
  "IMPORTANT",
  "CATEGORY_PERSONAL",
  "INBOX"
 ],
 "snippet": "From: Roadrunner Truckload [mailto:mike091771@gmail.com] Sent: Thursday, March 09, 2017 12:33 PM To: Terri Jenkins Subject: Available trucks! See a truck you can use? Can&#39;t see this Message?",
 "historyId": "5193",
 "internalDate": "1489095160000",
 "payload": {
  "mimeType": "multipart/mixed",
  "filename": "",
  "headers": [
   {
    "name": "Delivered-To",
    "value": "truckavailable@conardlogistics.com"
   },
   {
    "name": "Received",
    "value": "by 10.79.167.22 with SMTP id q22csp543024ive;        Thu, 9 Mar 2017 13:28:35 -0800 (PST)"
   },
   {
    "name": "X-Received",
    "value": "by 10.129.49.140 with SMTP id x134mr5746100ywx.227.1489094915213;        Thu, 09 Mar 2017 13:28:35 -0800 (PST)"
   },
   {
    "name": "Return-Path",
    "value": "\u003ctjenkins@conardlogistics.com\u003e"
   },
   {
    "name": "Received",
    "value": "from intranet.conardlogistics.com (96-91-31-105-static.hfc.comcastbusiness.net. [96.91.31.105])        by mx.google.com with ESMTPS id m35si1158393ywh.414.2017.03.09.13.28.34        for \u003ctruckavailable@conardlogistics.com\u003e        (version=TLS1 cipher=ECDHE-RSA-AES128-SHA bits=128/128);        Thu, 09 Mar 2017 13:28:35 -0800 (PST)"
   },
   {
    "name": "Received-SPF",
    "value": "neutral (google.com: 96.91.31.105 is neither permitted nor denied by best guess record for domain of tjenkins@conardlogistics.com) client-ip=96.91.31.105;"
   },
   {
    "name": "Authentication-Results",
    "value": "mx.google.com;       spf=neutral (google.com: 96.91.31.105 is neither permitted nor denied by best guess record for domain of tjenkins@conardlogistics.com) smtp.mailfrom=tjenkins@conardlogistics.com"
   },
   {
    "name": "Received",
    "value": "from CONARDPDC3.conardlogistics.local ([192.168.0.2]) by conardpdc3 ([192.168.0.2]) with mapi id 14.01.0438.000; Thu, 9 Mar 2017 15:32:40 -0600"
   },
   {
    "name": "Content-Type",
    "value": "multipart/mixed; boundary=\"_000_70BA03BE20B9E243AA6BC51F562831631B79B245conardpdc3_\""
   },
   {
    "name": "From",
    "value": "Terri Jenkins \u003ctjenkins@conardlogistics.com\u003e"
   },
   {
    "name": "To",
    "value": "\"truckavailable@conardlogistics.com\" \u003ctruckavailable@conardlogistics.com\u003e"
   },
   {
    "name": "Subject",
    "value": "FW: Available trucks!"
   },
   {
    "name": "Thread-Topic",
    "value": "Available trucks!"
   },
   {
    "name": "Thread-Index",
    "value": "AQHSmQQl3ShAvGJH7kGjqr7C7PS1nqGNBziw"
   },
   {
    "name": "Date",
    "value": "Thu, 9 Mar 2017 21:32:40 +0000"
   },
   {
    "name": "Message-ID",
    "value": "\u003c70BA03BE20B9E243AA6BC51F562831631B79B245@conardpdc3\u003e"
   },
   {
    "name": "References",
    "value": "\u003c1127402348831.1111342349780.1581460298.0.661332JL.2002@scheduler.constantcontact.com\u003e"
   },
   {
    "name": "In-Reply-To",
    "value": "\u003c1127402348831.1111342349780.1581460298.0.661332JL.2002@scheduler.constantcontact.com\u003e"
   },
   {
    "name": "Accept-Language",
    "value": "en-US"
   },
   {
    "name": "Content-Language",
    "value": "en-US"
   },
   {
    "name": "X-MS-Has-Attach",
    "value": "yes"
   },
   {
    "name": "X-MS-TNEF-Correlator",
    "value": "\u003c70BA03BE20B9E243AA6BC51F562831631B79B245@conardpdc3\u003e"
   },
   {
    "name": "x-originating-ip",
    "value": "[192.168.0.54]"
   },
   {
    "name": "MIME-Version",
    "value": "1.0"
   }
  ],
  "body": {
   "size": 0
  },
  "parts": [
   {
    "partId": "0",
    "mimeType": "text/plain",
    "filename": "",
    "headers": [
     {
      "name": "Content-Type",
      "value": "text/plain; charset=\"utf-8\""
     },
     {
      "name": "Content-Transfer-Encoding",
      "value": "base64"
     }
    ],
    "body": {
     "size": 5242,
     "data": "DQoNCkZyb206IFJvYWRydW5uZXIgVHJ1Y2tsb2FkIFttYWlsdG86bWlrZTA5MTc3MUBnbWFpbC5jb21dDQpTZW50OiBUaHVyc2RheSwgTWFyY2ggMDksIDIwMTcgMTI6MzMgUE0NClRvOiBUZXJyaSBKZW5raW5zDQpTdWJqZWN0OiBBdmFpbGFibGUgdHJ1Y2tzIQ0KDQpTZWUgYSB0cnVjayB5b3UgY2FuIHVzZT8NCg0KDQpDYW4ndCBzZWUgdGhpcyBNZXNzYWdlPyBMZXQncyBGaXggVGhhdCEgPGh0dHA6Ly9jYW1wYWlnbi5yMjAuY29uc3RhbnRjb250YWN0LmNvbS9yZW5kZXI_bT0xMTExMzQyMzQ5NzgwJmNhPWQzZDVmNjc5LTQ4NzMtNGQwMy1iZmQwLTViMzUzM2FiNDAxYz4NCg0KDQoNCg0KDQoNCg0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuXQ0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuXTxodHRwOi8vcy5yczYubmV0L3Q_ZT1fU1RCRG9aUF9vZyZjPTEmcj0xPg0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuXTxodHRwOi8vcy5yczYubmV0L3Q_ZT1fU1RCRG9aUF9vZyZjPTMmcj0xPg0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuXTxodHRwOi8vcy5yczYubmV0L3Q_ZT1fU1RCRG9aUF9vZyZjPTQmcj0xPg0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuXQ0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuXTxodHRwOi8vcy5yczYubmV0L3Q_ZT1fU1RCRG9aUF9vZyZjPTUmcj0xPg0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuXQ0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuXTxodHRwOi8vbXllbWFpbC5jb25zdGFudGNvbnRhY3QuY29tL0F2YWlsYWJsZS10cnVja3MtLmh0bWw_c29pZD0xMTExMzQyMzQ5NzgwJmFpZD1fU1RCRG9aUF9vZyNmYmxpa2U-DQoNCg0KDQoNCg0KDQoNCg0KDQoNCltJbWFnZSByZW1vdmVkIGJ5IHNlbmRlci5dDQoNCg0KDQoNCiBDYWxsDQo3NzItOTE4LTQ4NDgNCg0KIENvbm5lY3Qgd2l0aCBvdXIgRGlzcGF0Y2hlcnMgdG8gdXNlIG91ciBBdmFpbGFibGUgVHJ1Y2tzIGZvciB5b3VyIEV2ZXJ5IE5lZWQhDQoNCg0KVG8gU2FmZVVuc3Vic2NyaWJl4oSiIHBsZWFzZSBjbGljayB0aGUgbGluayBhdCB0aGUgYm90dG9tIQ0KDQoNCg0KRGF0ZSBBdmFpbGFibGUNCg0KRXF1aXBtZW50DQoNCkRlc2lyZWQgT3JpZ2luDQoNCkRlc2lyZWQgRGVzdGluYXRpb24NCg0KQ29udGFjdGVkIEJ5DQoNCjMvOS8yMDE3DQoNClYNCg0KT3JsYW5kbywgRkwNCg0KRmxvcmlkYQ0KDQpDaGFybG90dGUgeDMyNA0KDQozLzkvMjAxNw0KDQpWDQoNClRyaWFkZWxwaGlhLCBXVg0KDQpNaWFtaSwgRkwNCg0KTGFyaXNzYSB4MzE3DQoNCjMvOS8yMDE3DQoNClYNCg0KQ29ucm9lLCBUWA0KDQpLYXJlbg0KDQozLzEwLzIwMTcNCg0KVg0KDQpKb2xpZXQgSUwNCg0Kb3Blbg0KDQpOaWMgeDMwMw0KDQozLzEwLzIwMTcNCg0KVg0KDQpCZW50b252aWxsZSwgQVINCg0KT3Blbg0KDQpMYXJpc3NhIHgzMTcNCg0KMy8xMC8yMDE3DQoNClYNCg0KU3lyYWN1c2UsIE5ldyBZb3JrDQoNCk9wZW4NCg0KVG9tIHggMzA3DQoNCjMvMTAvMjAxNw0KDQpWDQoNCkFybGluZ3RvbiwgVFgNCg0KT3Blbg0KDQpMYXJpc3NhIHgzMTcNCg0KMy8xMC8yMDE3DQoNClYNCg0KTW91bnRhaW4gR3JvdmUsIE1PDQoNCkZMDQoNCkxhcmlzc2EgeDMxNw0KDQozLzEwLzIwMTcNCg0KVg0KDQpLaW5nc3BvcnQsIFRODQoNCk5hc2h2aWxsZSwgVE4NCg0KTGFyaXNzYSB4MzE3DQoNCjMvMTAvMjAxNw0KDQpWWlQNCg0KTWlhbWkgZmwNCg0Kb3Blbg0KDQpUb20geCAzMDcNCg0KMy8xMC8yMDE3DQoNClYNCg0KR3JlZW5zYm9ybywgR0ENCg0KQ2hhcmxvdHRlDQp4MzI0DQoNCjMvMTAvMjAxNw0KDQpWDQoNCkF0bGFudGEsIEdBDQoNCldhbnRzIHRvIHN0YXkgRWFzdCBvZiBJLTM1IG9yIE5vcnRoIEVhc3Qgb3IgUEEgTkogREUgTUQNCg0KQ2hhcmxvdHRlIHgzMjQNCg0KMy8xMC8yMDE3DQoNClYNCg0KRGVudmVyIENPDQoNCk9yZWdvbg0KDQpOYXRlIFN0cmF1c3Nlcg0KDQozLzEwLzIwMTcNCg0KVg0KDQpGbGV0Y2hlciBOQw0KDQpOaWMgeDMwMw0KDQozLzEwLzIwMTcNCg0KVg0KDQpTYWx0IExha2UgQ2l0eSwgVVQNCg0KTWlkd2VzdCBBcmVhDQoNCk5hdGUgeCAzMjYNCg0KMy8xMC8yMDE3DQoNClYNCg0KQ29sdW1iaWEgU0MNCg0KT3Blbg0KDQpKYWlkZSB4MzE2DQoNCjMvMTAvMjAxNw0KDQpWDQoNCkFsYnVxdWVycXVlLCBOZXcgTWV4aWNvIHdpbGwgREggMTUwIG1pbGVzDQoNCkNoYXJsb3R0ZSBIdWRuYWxsDQoNCjMvMTAvMjAxNw0KDQpWDQoNCk1hbnNmaWVsZCwgTEENCg0KQ2hhcmxvdHRlIEh1ZG5hbGwNCg0KMy8xMC8yMDE3DQoNClYNCg0KQnJvb2toYXZlbiBNUw0KDQpLYXJhbiB4IDMyMw0KDQozLzEwLzIwMTcNCg0KVg0KDQpXYXNoaW5ndG9uLCBXVg0KDQpvcGVuDQoNCkNoYXJsb3R0ZSB4MzI0DQoNCjMvMTAvMjAxNw0KDQpWDQoNCkNoaWNhZ29sYW5kLCBJTA0KDQpNaWR3ZXN0DQoNCk5hdGUgeCAzMjYNCg0KMy8xMy8yMDE3DQoNClYNCg0KQ29ubmVsbHN2aWxsZSBQQQ0KDQpvcGVuDQoNCk5pYyB4MzAzDQoNCjMvMTMvMjAxNw0KDQpWDQoNCkdhc3Nhd2F5LCBXVg0KDQpvcGVuDQoNCk1hdHQgeDMxMw0KDQozLzEzLzIwMTcNCg0KVg0KDQpJbmRpYW5vbGEsIE1TDQoNCm9wZW4NCg0KTWF0dCB4MzEzDQoNCjMvMTQvMjAxNw0KDQpWWg0KDQpTYW4gQW50b25pbyBUWA0KDQpDaGFybG90dGUgTkMNCg0KTmljIHgzMDMNCg0KDQpWDQoNCk1hdHQgeDMxMw0KDQoNClYNCg0KSW5kaWFuYXBvbGlzLCBJTg0KDQpKYWlkZSB4MzE2DQoNCg0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuXQ0KDQoNCg0KDQpTVEFZIENPTk5FQ1RFRDoNCg0KW0ltYWdlIHJlbW92ZWQgYnkgc2VuZGVyLiBGYWNlYm9va108aHR0cDovL3IyMC5yczYubmV0L3RuLmpzcD9mPTAwMW9BcDB5WWw2YmpCWEJ5ZU5vODZzc0V0VjkwMjRTRFJxa3pjcElfYjhocVFPbnlzaENUNGtBTldVMUJfdkhQdUNmcVUzRjN5czgwWUVVbnl6OFBIbnNKT1Jndm5sX212ZFRxMVMyeUlpNjJ0U2NNdUtDUmhkVXA1em9jcUJoM3hRM0tIVWZvQ1F1OWpnc3dIYVRkZEZvZ1d4YUd3OHctSGgmYz1ZSnc5NHVWVG1aMEVadl9GUFlXRWszVDZTaVRhMkcxV0Q1eXNNckV0dU5qaGtDU3EzWWJRbkE9PSZjaD10NEhTS0RERDA0UUZuejNEeDBoUG5RRXZXSWxGeFdYRHlKM3pQbElfVUtMUS1HVGFKdzY5R0E9PT4NCg0KW0ltYWdlIHJlbW92ZWQgYnkgc2VuZGVyLiBUd2l0dGVyXTxodHRwOi8vcjIwLnJzNi5uZXQvdG4uanNwP2Y9MDAxb0FwMHlZbDZiakJYQnllTm84NnNzRXRWOTAyNFNEUnFremNwSV9iOGhxUU9ueXNoQ1Q0a0FOV1UxQl92SFB1Q2ZxVTNGM3lzODBZRVVueXo4UEhuc0pPUmd2bmxfbXZkVHExUzJ5SWk2MnRTY011S0NSaGRVcDV6b2NxQmgzeFEzS0hVZm9DUXU5amdzd0hhVGRkRm9nV3hhR3c4dy1IaCZjPVlKdzk0dVZUbVowRVp2X0ZQWVdFazNUNlNpVGEyRzFXRDV5c01yRXR1Tmpoa0NTcTNZYlFuQT09JmNoPXQ0SFNLREREMDRRRm56M0R4MGhQblFFdldJbEZ4V1hEeUozelBsSV9VS0xRLUdUYUp3NjlHQT09Pg0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuIExpbmtlZEluXTxodHRwOi8vcjIwLnJzNi5uZXQvdG4uanNwP2Y9MDAxb0FwMHlZbDZiakJYQnllTm84NnNzRXRWOTAyNFNEUnFremNwSV9iOGhxUU9ueXNoQ1Q0a0FOV1UxQl92SFB1Q2ZxVTNGM3lzODBZRVVueXo4UEhuc0pPUmd2bmxfbXZkVHExUzJ5SWk2MnRTY011S0NSaGRVcDV6b2NxQmgzeFEzS0hVZm9DUXU5amdzd0hhVGRkRm9nV3hhR3c4dy1IaCZjPVlKdzk0dVZUbVowRVp2X0ZQWVdFazNUNlNpVGEyRzFXRDV5c01yRXR1Tmpoa0NTcTNZYlFuQT09JmNoPXQ0SFNLREREMDRRRm56M0R4MGhQblFFdldJbEZ4V1hEeUozelBsSV9VS0xRLUdUYUp3NjlHQT09Pg0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuIFBpbnRlcmVzdF08aHR0cDovL3IyMC5yczYubmV0L3RuLmpzcD9mPTAwMW9BcDB5WWw2YmpCWEJ5ZU5vODZzc0V0VjkwMjRTRFJxa3pjcElfYjhocVFPbnlzaENUNGtBTldVMUJfdkhQdUNmcVUzRjN5czgwWUVVbnl6OFBIbnNKT1Jndm5sX212ZFRxMVMyeUlpNjJ0U2NNdUtDUmhkVXA1em9jcUJoM3hRM0tIVWZvQ1F1OWpnc3dIYVRkZEZvZ1d4YUd3OHctSGgmYz1ZSnc5NHVWVG1aMEVadl9GUFlXRWszVDZTaVRhMkcxV0Q1eXNNckV0dU5qaGtDU3EzWWJRbkE9PSZjaD10NEhTS0RERDA0UUZuejNEeDBoUG5RRXZXSWxGeFdYRHlKM3pQbElfVUtMUS1HVGFKdzY5R0E9PT4NCg0KDQoNCg0KDQoNCg0KDQoNCg0KDQpSb2FkcnVubmVyIFRydWNrbG9hZCBQbHVzICYgQ1JULCA3MjUgQ29tbWVyY2UgQ2VudGVyIERyLCBTdWl0ZSBFLCBTZWJhc3RpYW4sIEZMIDMyOTU4DQoNClNhZmVVbnN1YnNjcmliZeKEoiB0amVua2luc0Bjb25hcmRsb2dpc3RpY3MuY29tPGh0dHBzOi8vdmlzaXRvci5jb25zdGFudGNvbnRhY3QuY29tL2RvP3A9dW4mbT0wMDFCOHE3eEVWbkEtN2lJcWtJbGJwdTR3JTNEJTNEJmNoPWFlZWQ0ZjUwLWU3ZGYtMTFlNi05ZmIwLWQ0YWU1MjhlYWJhOSZjYT1kM2Q1ZjY3OS00ODczLTRkMDMtYmZkMC01YjM1MzNhYjQwMWM-DQoNCg0KRm9yd2FyZCB0aGlzIGVtYWlsPGh0dHA6Ly91aS5jb25zdGFudGNvbnRhY3QuY29tL3NhL2Z3dGYuanNwP2xscj11bzR1c2FsYWImbT0xMTExMzQyMzQ5NzgwJmVhPXRqZW5raW5zJTQwY29uYXJkbG9naXN0aWNzLmNvbSZhPTExMjc0MDIzNDg4MzE-IHwgVXBkYXRlIFByb2ZpbGU8aHR0cHM6Ly92aXNpdG9yLmNvbnN0YW50Y29udGFjdC5jb20vZG8_cD1vbyZtPTAwMUI4cTd4RVZuQS03aUlxa0lsYnB1NHclM0QlM0QmY2g9YWVlZDRmNTAtZTdkZi0xMWU2LTlmYjAtZDRhZTUyOGVhYmE5JmNhPWQzZDVmNjc5LTQ4NzMtNGQwMy1iZmQwLTViMzUzM2FiNDAxYz4gfCBBYm91dCBvdXIgc2VydmljZSBwcm92aWRlcjxodHRwOi8vd3d3LmNvbnN0YW50Y29udGFjdC5jb20vbGVnYWwvc2VydmljZS1wcm92aWRlcj9jYz1hYm91dC1zZXJ2aWNlLXByb3ZpZGVyPg0KDQoNClNlbnQgYnkgbWlrZTA5MTc3MUBnbWFpbC5jb208bWFpbHRvOm1pa2UwOTE3NzFAZ21haWwuY29tPiBpbiBjb2xsYWJvcmF0aW9uIHdpdGgNCg0KDQpbSW1hZ2UgcmVtb3ZlZCBieSBzZW5kZXIuIENvbnN0YW50IENvbnRhY3RdPGh0dHA6Ly93d3cuY29uc3RhbnRjb250YWN0LmNvbS9pbmRleC5qc3A_Y2M9UFQxMTI5Pg0KDQpUcnkgaXQgZnJlZSB0b2RheTxodHRwOi8vd3d3LmNvbnN0YW50Y29udGFjdC5jb20vaW5kZXguanNwP2NjPVBUMTEyOT4NCg0KDQoNCg0KDQoNCg0KDQoNCg=="
    }
   },
   {
    "mimeType": "multipart/mixed",
    "filename": "",
    "headers": [
     {
      "name": "Content-Disposition",
      "value": "attachment; filename=\"winmail.dat\""
     },
     {
      "name": "Content-Transfer-Encoding",
      "value": "base64"
     },
     {
      "name": "Content-Type",
      "value": "application/ms-tnef; name=\"winmail.dat\""
     }
    ],
    "body": {
     "attachmentId": "ANGjdJ-j5DPV39EKX1gcTg1q9zsr1uydOnTa4mmNuyco8GkgtldzOcx2oAqD3nqQttHy2A8vFr5xILf1X-CLtbYTn11oxvrBTASVY0xaAD6McPVgNcJ_5yXuhvo_sj3YlAiGNMWF5pRW3-4X5esxwmbiCK-N6oUqdH_vTTNuNGDu_HkGYPuSGDGAAf3kB46kVWeI5mx_bh0lbiAvC5ro-wnn2v-n388fUlLA-ptjpkxgFVzxw7MPpTjglac9pTcLqy7A_mWe1Bu--xTEFb9UaBesX1VzrA1A-r-Tx0guvzfOh1epDUgI7NbHBp1Ggec",
     "size": 86546
    },
    "parts": [
     {
      "partId": "1.0",
      "mimeType": "application/octet-stream",
      "filename": "~WRD000.jpg",
      "body": {
       "attachmentId": "ANGjdJ-6i9gP_NYlMlY9la1DQuFMWT8xiIt4A5R479e3T8Lk9NrLKyd0LRYtrJ0knTmRt76ro7jwOYZxWAOzU3VuveYyKeKGH_We4KyvIpQEEuIsugrEusSqzBTi8wAzenH6V_gHWFAb3IKVmr1BYokXCaZGC2QcYMGSI10ocyQIcWo0PHoXTR-xj2S78LEBNbQFxO52SSasEZm25FdBvPWsAqxuenw8offCflK4kp2w2dlioR6hN9sJUZSTzri32JGZ63N0_IaJ-acY7t0RcANbt2PQFezobNZ6xTnn3w",
       "size": 823
      }
     },
     {
      "partId": "1.1",
      "mimeType": "application/octet-stream",
      "filename": "image001.jpg",
      "body": {
       "attachmentId": "ANGjdJ9_COlL-25FG3LA_n23TgbrS9Na2lQxAw6tRjbfnaC5HrB2awqTFL7jeY6rZOfVM95nW_qXwHlUpTJkAcw4Xq7vKB4s6P14eYifzeMXK8l6aBuLvdNz-6z8ZqDqlpT9usrkNqZhj9RTZX76gOAQIvyD-3qqW1l520l-UYmSm9J03KOGxUHYkdm-j9hRgxYi2PKMA7uV_Jij8rwXyXZzAuVmrWlWVFiqHKaqPowNtlBGYP5RnAs9YRaa-Pf6HMLhDhicfuNcAR5QC9GYyCDdD3H0RSqORGwqyxq3iw",
       "size": 332
      }
     },
     {
      "partId": "1.2",
      "mimeType": "application/octet-stream",
      "filename": "image002.jpg",
      "body": {
       "attachmentId": "ANGjdJ88lIMgHFwX_EsJf4X2po0CyXJNYAZrw4iuxVqvhk7vnAmNmBjSoDONOes0O5O5ivdTNk4ZB4ie79mPMJvrISjgRws5w-fcRlm7u_MN64-m5Z5YHp6AIhGBvCObn8aHBL8pp7R8Kxf0YZtIv_3wfcufzG_y8x96XlyDyL0yks5h4EJ7wzZa1Y0WHPg61FU60GJFZOlicikiAx9NeUgwNQHS-NvD6bKIc4Ckbv7VziqZa5XL90fngBl5yvNyCJZEmJQU6dSQNFHiciOB11dG5A2bFr0w7VeZSw4r6w",
       "size": 332
      }
     },
     {
      "partId": "1.3",
      "mimeType": "application/octet-stream",
      "filename": "image003.jpg",
      "body": {
       "attachmentId": "ANGjdJ-MGOtAO4TxUmSuDzwTOt4S8YdS9hwz6D13SAFuujx7YvWWQUMq-1VFdtvNB1nI4zui5NKrtpL42pa8SISgnesR0lEXnl70AeqZLKB6wE6wgQ6HS2-Qzs08lRdp0zESKOge6fMXOE6ejCwzBZvc5LmE6uvCwinGo2JYBg9inb-NoFy_wSL8vGC3Npc-z_lxhRu2Q7xWJb-iQej7hzTT0vPBUNneGLDlY2teOdfsprc5aJubRuwfEMk4h6Sgh9AlpFn7ZSQfXCQ2-vZj02RSR_aQKU8Glq2i-T5T-A",
       "size": 993
      }
     },
     {
      "partId": "1.4",
      "mimeType": "application/octet-stream",
      "filename": "image004.jpg",
      "body": {
       "attachmentId": "ANGjdJ8J75yPqQ3ZrdkAjnKd4X1ki7a6RFa3jZBoXAJ_HBAmFhXSbpI4nnOFvdYu3oZNA3Cb372dMlGbOeC81jXviLtV-fHPOE39Z6IrmUfMWCRdXsFiY6g-FSTluSNqHf_r_GdY_yiccFd5krSI_v0c19Z0pKl4-GU3RWmnUecXA1MCjm12SmC2fjJTu_vYiD-80easl-FMnl_bxi3CDbsq4xTBmxXmYvJxzIlm-b3A3032skYMA0-aTp4LobBWiJpAfJ-MPBsdKU6RXipJ0W-PlXckzB37vJsKoUpPBw",
       "size": 1157
      }
     },
     {
      "partId": "1.5",
      "mimeType": "application/octet-stream",
      "filename": "image005.jpg",
      "body": {
       "attachmentId": "ANGjdJ9nc18JLFIWFEkxd05m066jTRpeF1Q1OMphDmhyp8awFWOxs8qCm1GMHk6wjhglL9rpItSxWF5W9f_Ppx9mAJOBe-_ZNov7rFP8EERTLgvnF_X1QC-Lf2BUVI4wjP7U6Q5ed6BB7J78OSqqIUbjH9rELKtLjM4QCT1BFvif8PnwgMvIic9rEppvntevHxqxXBXFhiS7tB1slxjJllx6tyTWI6URRioJFr8SQ-Uxl7RyI0PHLhF0-iIHU5_py77ER7557hcvDOmjgQ1S3yS4f7yKogP_remFOrftgQ",
       "size": 338
      }
     },
     {
      "partId": "1.6",
      "mimeType": "application/octet-stream",
      "filename": "image006.jpg",
      "body": {
       "attachmentId": "ANGjdJ_h11o8KL-Ha7sy_B_KzwkVB25GTOinigp7X9aqWj2wHPdBsu395Y95u_6_Q1bMRXWzMk_ZTKbRfxVZnzvcsREydKJj-KB_elixgMm6BK-S32U3EAUh2EbHYhiyvsq8cpuHLqjkwPx21nFdom2CJ59qwVPAvA1tDR9RRgJY_3qMZiEHsxMpPmbHsb2fbpmQt-n2GYm3S3MZnmgQR7WGuQjQYfJ6ap2CVo_4WZleO55KSjww8zwtXg-HAb38Tm4_ENqj1dFMWc4LGw71peqyzWXiEjB2DY4qb4ibKA",
       "size": 728
      }
     }
    ]
   }
  ]
 },
 "sizeEstimate": 128112
}

*/


/*

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require 'vendor/autoload.php';


//var_dump(openssl_get_cert_locations());

$service_account = 'trucksavailable@trucksavailable-160822.iam.gserviceaccount.com';
$userid = 'truckavailable@conardlogistics.com';

//$http = new GuzzleHttp\Client(['verify' => '../cert/trucking_conardtransportation_com.cer']);
$http = new GuzzleHttp\Client(['verify' => false]);

//var_dump($http);
//exit;

$client = new Google_Client();
$client->setHttpClient($http);
$client->setApplicationName('trucksavailable');
$client->setAuthConfig('conard-904440bd98d5.json');

$client->setScopes(array(
    'https://www.googleapis.com/auth/gmail.modify',
));

$client->setSubject($userid);

//$client->getHttpClient()->request();
//$client->getHttpClient()->setDefaultOption('verify',false);
//$client->getHttpClient()->setDefaultOption('GET', '/', array('verify' => false));
//$client->getHttpClient()->setDefaultOption(array('verify' => false) );

$gmail = new Google_Service_Gmail($client);
//echo '<pre>';
//var_dump($gmail);
//echo '</pre>';
//exit;


// Get a list of the messages
$messages = array();
$opt_param = array(
	'q' => 'label:INBOX'
);

$messages_response = $gmail->users_messages->listUsersMessages($userid, $opt_param);

echo '<pre>';
var_dump($messages_response);
echo '</pre>';
exit;

parseMessages($gmail, $userid);



function parseMessages($service, $userid) 
{    
	$working="INBOX";
	$done="Processed";
		
	//Get the user's labels -- we're looking for the id for the archived label
	
     echo '<h1>Labels</h1>';
     $labels = array();
     try 
     {
          $labels_response = $service->users_labels->listUsersLabels($userid);
          
          echo '<pre>';
          var_dump($labels_response);
          echo '</pre>';
          exit;
          
          if($labels_response->getLabels()) 
          {
          	$labels = array_merge($labels, $labels_response->getLabels());
             	foreach($labels as $labelObj) 
          	{
             		$label_id = $labelObj->getId();
                  	
                  	$label_name ="";
                  	$label_name = $service->users_labels->get($userid, $label_id);
                  	
                  	echo "".$label_id.". ".$label_name ."<br>";
          	}
          }
	}
	catch (Excetion $e)
	{
		echo "An error occurred: " . $e->getMessage()."<br>";
	}
     
    	//Get a list of the messages
    	$messages = array();
    	$opt_param = array(
        	'q' => 'label:INBOX has:attachment'
    	);
    
    	$messages_response = $service->users_messages->listUsersMessages($userid, $opt_param);
    	if($messages_response->getMessages()) 
    	{
        $messages = array_merge($messages, $messages_response->getMessages());
        $files = array();
        
        // Loop through the messages
        foreach ($messages as $messageObj) 
        {
            $message_id = $messageObj->getId();
            echo $message_id.'<br>';
            
            $message = $service->users_messages->get($userid, $message_id);
            $payload = $message->getPayload();
            $payload_parts = $payload->getParts();
            $payload_headers = $payload->getHeaders();
            $subject = '';
            foreach ($payload_headers as $header) 
            {
                if ($header->getName() == 'Subject') 
                {
                    $subject = $header->getValue();
                    break;
                }
            }
            echo $subject.'<br>';
            foreach ($payload_parts as $part) 
            {
                $filename = $part->getFilename();
                if ($filename != '') 
                {
                    $attachment_id = $part->getBody()->getAttachmentId();
                    $attachment_response = $service->users_messages_attachments->get($userid, $message_id, $attachment_id);
                    $file_data = $attachment_response->getData();
                    
                    // Write file to temp dir
                    $file_data = str_replace('-', '+', $file_data);
                    $file_data = str_replace('_', '/', $file_data);
                    
                    if (file_put_contents('temp/'.$filename, base64_decode($file_data))) 
                    {
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
*/
echo '<br><br>done';
?>