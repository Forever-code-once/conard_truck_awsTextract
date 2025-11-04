<?
//functions only for the GeoTab interface/API...core functions to interface directly with GeoTab

function mrr_find_geotab_database_name()
{	//this is the conard system's database to store the records such as "conard_trucking" (main 'dispatch'), or "sicap_conard" (accounting), or "conard_trucking_logs" (PN).
	global $defaultsarray;
	return trim($defaultsarray['geotab_database_name']);			//leave blank to store records in tables with main system (Dispatch)
}

//main settings fro GeoTab API     
function mrr_find_geotab_database()
{
	global $defaultsarray;
	return trim($defaultsarray['geotab_database']);		
}
function mrr_find_geotab_username()
{
	global $defaultsarray;
	return trim($defaultsarray['geotab_user']);	
}
function mrr_find_geotab_password()
{
	global $defaultsarray;
	return trim($defaultsarray['geotab_pass']);	
}

//get and capture GeoTab server anme since this is apparently NOT static...'the cloud' storage may cause this to move!!!
function mrr_find_geotab_server_name()
{
	//global $defaultsarray;
	//return trim($defaultsarray['geotab_server_name']);	
	$geotab_server_name="";
	
	$sql="select xvalue_string from defaults where xname='geotab_server_name'";
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$geotab_server_name=trim($row['xvalue_string']);	
	}
	
	return $geotab_server_name;	
}
function mrr_set_geotab_server_name($geotab_server_name)
{
	$sql="update defaults set xvalue_string='".sql_friendly($geotab_server_name)."' where xname='geotab_server_name'";
	simple_query($sql);
}

//get and capture GeoTab API session ID
function mrr_find_geotab_session_id()
{
	//global $defaultsarray;
	//return trim($defaultsarray['geotab_session_id']);	
	
	$geotab_session_id="";
	
	$sql="select xvalue_string from defaults where xname='geotab_session_id'";
	$data=simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$geotab_session_id=trim($row['xvalue_string']);	
	}
	
	return $geotab_session_id;	
}
function mrr_set_geotab_session_id($geotab_session_id)
{
	$sql="update defaults set xvalue_string='".sql_friendly(trim($geotab_session_id))."' where xname='geotab_session_id'";
	simple_query($sql);
}


//parts of the API so that      
function mrr_find_geotab_url()
{
	return "https://my.geotab.com/apiv1/";		//core version for authentication....
}
function mrr_find_geotab_url_1()
{
	return "https://".mrr_find_geotab_server_name()."/".mrr_find_geotab_database()."/";
}
function mrr_find_geotab_url_2()
{
	return "https://".mrr_find_geotab_server_name()."/apiv1/";
}

//general API CURL process to get all results from any query...    
function mrr_geotab_get_file_contents($cmd,$url_mode=0,$get_post=0,$encoded=0,$decoded=0,$postdata=array())
{	//$get_post is GET if 0, POST if 1.
	
	global $defaultsarray;
	$date_from=trim($defaultsarray['geotab_last_login']);
	if(date("Y-m-d",strtotime($date_from)) != date("Y-m-d",time()) && substr_count($cmd,"Authenticate?")==0)		mrr_get_authenticate();
	
	$report="";
	
	if($url_mode==1)
	{
		$prime_url=mrr_find_geotab_url_1()."".$cmd;									//This mode uses the current server and database for the GeoTab system					
		if($encoded > 0)			$prime_url=mrr_find_geotab_url_1().urlencode($cmd);     		
		$report.="<br>CURL URL (1): ".mrr_find_geotab_url_1()."".$cmd."<br><br>";
	}
	elseif($url_mode==2)
	{
		$prime_url=mrr_find_geotab_url_2()."".$cmd;						
		if($encoded > 0)			$prime_url=mrr_find_geotab_url_2().urlencode($cmd);	
		$report.="<br>CURL URL (2) Pre-encoding: <br>".mrr_find_geotab_url_2()."".$cmd."<br>";
		//$report.='<br>https://my241.geotab.com/apiv1/Get?typeName=Device&credentials={"database":"conard","userName":"michael@sherrodcomputers.com","sessionId":"8930957207201481481"}<br><br>';
	}
	else
	{
		$prime_url="".mrr_find_geotab_url()."".$cmd;						
		if($encoded > 0)			$prime_url=mrr_find_geotab_url().urlencode($cmd);	     	
		$report.="<br>CURL URL (0): ".mrr_find_geotab_url()."".$cmd."<br><br>";
	}
	
	//if($decoded > 0)	$prime_url=htmlentities($prime_url);
			
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_HEADER, false);		
	curl_setopt($curl_handle, CURLOPT_URL,$prime_url);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,20);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);	
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); // we don't want to stop the page from loading if there is ever an issue with the SSL
	
	curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			
	if($get_post > 0)
	{					
		curl_setopt($curl_handle, CURLOPT_POST,1);						
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $postdata);
	}
	
	$response = curl_exec($curl_handle);	
	$status_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
	$curl_errors = curl_error($curl_handle);
	
	$response_alt=$response;
	if($decoded > 0)	
	{
		$report.="<br>INNER RESPONSE: ".$response.".<br>Status Code: ".$status_code.".<br>Error: ".$curl_errors.".<br>";
	}
	$response=json_decode($response, true, 512, JSON_BIGINT_AS_STRING);	
	
	curl_close($curl_handle);
	
	if($status_code!=200 && $status_code!="200")
	{ 
		//$report.="<br><br>CMD=".$prime_url.":<br><pre>".var_dump($response)."</pre><br>Status: ".$status_code."<br>Error: ".$curl_errors."<br>";	
		
		if(substr_count($response_alt,"Incorrect login credentials") > 0)
		{	//Session dropped on GeoTab side (30 days according to their documentation).
			mrr_get_authenticate();
			
			$response="New Session Login";				
		}
		else
		{
			$response=$report;	
		}		
	}	
				
	return $response;
}

function mrr_get_geotab_version()
{
	$version="";
	$vres=mrr_geotab_get_file_contents("GetVersion",0,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	
	//echo "<br>GetVersion Result: ".var_dump($vres)."<br>";
	
	if(trim($vres['result'])!="")
	{
		$version="".trim($vres['result'])."";	
	}	
	return $version;
}

function mrr_get_authenticate()
{		
	$url='Authenticate?database='.mrr_find_geotab_database().'&userName='.mrr_find_geotab_username().'&password='.mrr_find_geotab_password().'';
			
	$session="";
	$sres=mrr_geotab_get_file_contents($url,0,0,0);			//URL,URL-Mode,GET/POST,encodeURL,$postdata
	
	$report="";
			
	//$report.="<br>1 Authenticate Result: ".var_dump($sres)."<br>";	// 
	if(isset($sres['result']['credentials']['sessionId']))
	{
		$report.="<br>Username=".strval($sres['result']['credentials']['userName'])."."; 
		$report.="<br>SessionID=".strval($sres['result']['credentials']['sessionId'])."."; 
		$report.="<br>Database=".strval($sres['result']['credentials']['database'])."."; 
		$report.="<br>Path=".strval($sres['result']['path'])."."; 
		
		mrr_set_geotab_session_id(strval($sres['result']['credentials']['sessionId']));
		if(strval($sres['result']['path'])!="ThisServer" && strval($sres['result']['path'])!="")
		{
			mrr_set_geotab_server_name(strval($sres['result']['path']));
		}
		
		$sqlu="update defaults set xvalue_string='".date("Y-m-d H:i:s",time())."' where xname='geotab_last_login'";
     	simple_query($sqlu);
	}
	else
	{
		$report.="<br>Error Message=".strval($sres['error']['message'])."."; 
		$report.="<br>Error Code=".strval($sres['error']['code'])."."; 
	}
	
	return $report;
}

function mrr_get_geotab_get_users()
{		
	$searching="";
	$searching='&search={"fromDate":"2018-03-01T00:00:00.000Z"}';	//,"toDate":"'.date("Y-m-d",time()).'T23:59:59.000Z"
	
	$url='Get?typeName=User'.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
	
	//$url.="&resultsLimit=10";	//&search={}
			
	$users="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata


	//echo "<br>Get (Users) Result: ".var_dump($dres)."<br>";
		
	if(!is_array($dres) && trim($dres)=="New Session Login")
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}



	$users="<br>Result Count=".count($dres)."<br>";	


	if(!empty($dres['result']))
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$users.="<br>User 1 Result. Total Users Found=".count($arr["result"]).".";
			$update_cntr=0;
			
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				$temp=$arr["result"][$j];
				
				$users.="<br>User ".$j.".  ".$temp['id']." <b>".$temp['firstName']." ".$temp['lastName']."</b> (".($temp['isDriver']==true || $temp['isDriver']=="true" ? "DRIVER" : "STAFF") .") 
							Employee# ".$temp['employeeNo']." Active from ".$temp['activeFrom']." to ".$temp['activeTo'].". LogIN: ".$temp['name'].".";	
				     				
				$test_val= (int) trim($temp['employeeNo']);		//$temp['name']
				
				if(($temp['isDriver']==true || $temp['isDriver']=="true") && is_numeric($test_val) && $test_val > 0)
				{
					$sql = "
						update drivers set
							geotab_use_id='".sql_friendly( trim($temp['id']))."'
						where id='".sql_friendly($test_val)."'
					";
					simple_query($sql);	
					
					$users.=" ---UPDATED!";
					$update_cntr++;
				}
			}
			
			$users.="<br><b>".$update_cntr."</b> Drivers Updated.  The rest were users that don't matter for the API.";
		}
		$users.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
	}
	elseif($_SERVER['REMOTE_ADDR'] == '70.90.229.29')
	{
		if(count($dres)==1)
		{
			echo "<br>Result for ".$url.": ".$dres."<br>";
		}
		else
		{
			echo "<br>Result for ".$url.": ".var_dump($dres)."<br>";
		}
	}
	return $users;     	
}

function mrr_get_geotab_get_drivers()
{		
	$url='Get?typeName=Driver&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
	
	//$url.="&resultsLimit=10";	//&search={}
			
	$drivers="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	
	//echo "<br>Get (Drivers) Result: ".var_dump($dres)."<br>";	
	
	if(!is_array($dres) && trim($dres)=="New Session Login" || count($dres)==3)
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/Get?typeName=Driver&credentials={"database":"conard","userName":"michael@sherrodcomputers.com","sessionId":"8930957207201481481"}';
	
	//echo "<br>Get (Driver) Result: ".var_dump($dres)."<br>";	
	
	$drivers="<br>Result Count=".count($dres)."<br>";	
		
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$drivers.="<br>Driver 1 Result. Total Drivers Found=".count($arr["result"]).".";	
			
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				$temp=$arr["result"][$j];
				
				$drivers.="<br>Driver ".$j.".  <b>ID=".$temp['id']."</b>. ".$temp['firstName']." ".$temp['lastName'].". 
							Login # <b>".$temp['name']."</b> | <b>".$temp['employeeNo']."</b> | ".$temp['designation']." 
							[Authority: ".$temp['authorityName']." --- ".$temp['authorityAddress']."]";	
				     				
				$test_val= (int) trim($temp['employeeNo']);		//$temp['name']
				
				//update the driver with the new number.				
				if(1==2 && ($temp['isDriver']==true || $temp['isDriver']=="true") && is_numeric($test_val) && $test_val > 0)
				{
					$sql = "
						update drivers set
							geotab_use_id='".sql_friendly( trim($temp['id']))."'
						where id='".sql_friendly($test_val)."'
					";
					simple_query($sql);	
					
					$drivers.=" ---UPDATED!";
				}
			}
		}
		$drivers.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
	}
	return $drivers;     	
}
function mrr_add_geotab_url_log($cur_code,$cur_url)
{    //CUR_URL is the URL being added tothe GeOTab log... either GeoTab or CoPilot
     $url='Add?typeName=Audit&entity={"name":"'.trim($cur_code).'","comment":"'.trim($cur_url).'"}&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
     
     //$url.="&resultsLimit=10";	//&search={}
     
     $logger="";
     
     
     //return "<br>Logger Funtion Disabled/Skipped for simplicity.  Restore only if needed for debugging.<br>";
     
     $dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
     
     //echo "<br>Get (Devices) LOG Result: <br>URL: ".$url."<br>".var_dump($dres)."<br>";
     
     echo "<br>Get (Devices) LOG Result: <br>URL: https://my241.geotab.com/apiv1/".$url."<br>";       //Log Result Dump:<br>".var_dump($dres)."<br>
     
     if(!is_array($dres) && trim($dres)=="New Session Login" || count($dres)==3)
     {	//Had to re-initiate the session ID by logging in again.  Repeat operation.
          mrr_get_authenticate();
          $dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
     }
          
     //echo '<br>https://my241.geotab.com/apiv1/Get?typeName=Device&credentials={"database":"conard","userName":"michael@sherrodcomputers.com","sessionId":"8930957207201481481"}';
     
     echo "<br>Add (Audit Log) Result: ".var_dump($dres)."<br>";	
     
     $logger="<br>Result Count=".count($dres)."<br>";
     /*
     if(count($dres)==2)
     {
          $arr=$dres;
          if(isset($arr["result"]))
          {
               $logger.="<br>Device 1 Result. Total Devices Found=".count($arr["result"]).".";
               
               for($j=0; $j < count($arr["result"]); $j++)
               {
                    $temp=$arr["result"][$j];
                    
                    $logger.="<br>Device ".$j.".  <b>ID=".$temp['id']."</b>. ".$temp['licensePlate']." (".$temp['licenseState'].") 
							Device # <b>".$temp['hardwareId']."</b> | ".$temp['devicePlans'][0]." | <b>".$temp['serialNumber']."</b> | ".$temp['deviceType']." 
							[VIN# ".$temp['vehicleIdentificationNumber']."] ".$temp['timeZoneId']."";
                    
               }
          }
          $logger.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";
     }
     */
     return $logger;
}
	
function mrr_get_geotab_get_devices()
{
     $searching="";
     $searching='&search={"fromDate":"'.date("Y-m-d",time()).'T00:00:00.000Z","groups":[{"id":"GroupVehicleId"}]}';
     
     $url='Get?typeName=Device'.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
     
     //$url.='&search={"groups":[{"id":"GroupVehicleId"}]';
     //$url.='&fromDate="'.date("Y-m-d",time()).'T00:00:00.000Z"';
	
	
	//$url.="&resultsLimit=10";	//&search={}
			
	$devices="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	
	//echo "<br>Get (Devices) Result: ".var_dump($dres)."<br>";


	if(!is_array($dres) && trim($dres)=="New Session Login" || count($dres)==3)
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/Get?typeName=Device&credentials={"database":"conard","userName":"michael@sherrodcomputers.com","sessionId":"8930957207201481481"}';
	
	//echo "<br>Get (Devices) Result: ".var_dump($dres)."<br>";	
	
	$devices="<br>Result Count=".count($dres)."<br>";	
		
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$devices.="<br>Device 1 Result. Total Devices Found=".count($arr["result"]).".";	
			
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				$temp=$arr["result"][$j];
				
				//
                    //
				
				$devices.="<br>Device ".$j.".  <b>ID=".$temp['id']."</b>. ".$temp['licensePlate']." (".$temp['licenseState'].") 
							Device # <b>".$temp['hardwareId']."</b> | ".$temp['devicePlans'][0]." | <b>".$temp['serialNumber']."</b> | ".$temp['deviceType']." 
							[VIN# ".$temp['vehicleIdentificationNumber']."] ".$temp['timeZoneId']."
							| Active From ".$temp['activeFrom']." To ".$temp['activeTo'].".";	
				
				$is_still_active=trim($temp['activeTo']);
				if(strlen($is_still_active) > 19)         $is_still_active=substr($is_still_active,0,19);
                    $is_still_active=str_replace("T", " ",$is_still_active);			
				
				//update the truck with the new number.		 && trim($temp['licensePlate'])!=""
				if(((int) trim($temp['hardwareId']) > 0 || trim($temp['id'])!="") && trim($temp['vehicleIdentificationNumber'])!=""
                         && $is_still_active > date("Y-m-d H:i:s",time()))
				{	
					$sql = "
						update trucks set
							geotab_device_id='".sql_friendly( trim($temp['id']))."',
							geotab_device_num='".sql_friendly((int) trim($temp['hardwareId']))."',
							geotab_device_serial='".sql_friendly(trim($temp['serialNumber']))."',
							geotab_device_type='".sql_friendly(trim($temp['deviceType']))."',
							geotab_device_plan='".sql_friendly( trim($temp['devicePlans'][0]))."'
						where vin='".sql_friendly(trim($temp['vehicleIdentificationNumber']))."' 
							
					";        //and license_plate_no='".sql_friendly(trim($temp['licensePlate']))."'
					simple_query($sql);
					
					$devices.=" ---<span style='color:00CC00;'>UPDATED!</span>";
				}
				else
                    {
                         $devices.=" ---<span style='color:CC0000;'><i>Not Active or Updated!</i></span>";
                    }
			}
		}
		$devices.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
	}
	return $devices;     	
}	

function mrr_get_geotab_get_trailers()
{
     $searching="";
     $searching='&search={"fromDate":"'.date("Y-m-d",time()).'T00:00:00.000Z","groups":[{"id":"GroupTrailerId"}]}';
     
     $url='Get?typeName=Device'.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
	
	//$url.="&resultsLimit=10";	//&search={}
			
	$trailers="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	
	//echo "<br>Get (Trailers) Result: ".var_dump($dres)."<br>";	
	
	if(!is_array($dres) && trim($dres)=="New Session Login" || count($dres)==3)
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/Get?typeName=Device&credentials={"database":"conard","userName":"michael@sherrodcomputers.com","sessionId":"8930957207201481481"}';
	
	//echo "<br>Get (Trailers) Result: ".var_dump($dres)."<br>";	
	
	$trailers="<br>Result Count=".count($dres)."<br>";	
		
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$trailers.="<br>Trailer 1 Result. Total Trailers Found=".count($arr["result"]).".";	
			
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				$temp=$arr["result"][$j];
				if (empty($temp['version'])) $temp['version'] = 'Unknown Version';

				$trailers.="<br>Trailer ".$j.".  <b>ID=".$temp['id']."</b>. ".$temp['name']." --- Version ".$temp['version']." | Comment ".$temp['comment'].".";	
				
				
				$found_this=0;
				$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_trailer_list where geotab_id='".sql_friendly(trim($temp["id"]))."'";
				$data=simple_query($sql);		
				if($row = mysqli_fetch_array($data)) 
				{
					$found_this=(int) $row['cnt'];
					//already in the system so don't save it there again...
				}
				if($found_this==0)
				{
					$trailer_id=0;
					$trailer_name=trim($temp['name']);
					$sqlt="select id from trailers where deleted<=0 and (trailer_name like '".sql_friendly($trailer_name)."' or nick_name='".sql_friendly($trailer_name)."')";
					$datat=simple_query($sqlt);		
					if($rowt = mysqli_fetch_array($datat)) 
					{
						$trailer_id=$rowt['id'];
					}
					
					$sql="
						insert into ".mrr_find_log_database_name()."geotab_trailer_list
							(id,
							linedate_added,
							deleted,
							geotab_id,
							device_id,
							device_name,
							trailer_id)
						values
							(NULL,
							NOW(),
							0,
							'".sql_friendly(trim($temp["id"]))."',
							'".sql_friendly(trim($temp["id"]))."',
							'".sql_friendly($trailer_name)."',
							'".sql_friendly($trailer_id)."')
						";
					simple_query($sql);	
				}
				
				     				
				//update the truck with the new number.		
				if(trim($temp['id'])!="" && trim($temp['name'])!="")
				{	
					$sql = "
						update trailers set
							geotab_trailer_id='".sql_friendly( trim($temp['id']))."'
						where (trailer_name='".sql_friendly(trim($temp['name']))."' or nick_name='".sql_friendly(trim($temp['name']))."')
					";
					simple_query($sql);
					
					$trailers.=" ---UPDATED!";
				}
			}
		}
		$trailers.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
	}
	return $trailers;  
}


function mrr_get_geotab_get_device_info($device_id="",$cd=0,$diagnotics_save=0,$show_debug=0)
{		
	$searching="";
	$moder[0]="";												$labeler[0]="Value";
	$moder[1]="DiagnosticOdometerAdjustmentId";						$labeler[1]="Odometer Reading";						//	474192100
	$moder[2]="DiagnosticOdometerId";								$labeler[2]="Odometer";								//	474192100
	$moder[3]="DiagnosticRawOdometerId";							$labeler[3]="Raw Odometer";							//	474192100	
	$moder[4]="DiagnosticAccelerationForwardBrakingId";				$labeler[4]="Acceleration Forward Braking";				//	0
	$moder[5]="DiagnosticAccelerationSideToSideId";					$labeler[5]="Acceleration Side To Side";				//	0
	$moder[6]="DiagnosticAccelerationUpDownId";						$labeler[6]="Acceleration Up Down";					//	10.189109802246
	$moder[7]="DiagnosticCrankingVoltageId";						$labeler[7]="Cranking Voltage";						//	12.823
	$moder[8]="DiagnosticCruiseControlActiveId";						$labeler[8]="Cruise Control Active";					//	1
	$moder[9]="DiagnosticDieselExhaustFluidId";						$labeler[9]="Diesel Exhaust Fluid";					//	66.8	
	$moder[10]="DiagnosticDeviceTotalIdleFuelId";					$labeler[10]="Device Total Idle Fuel";					//	1264.91
	$moder[11]="DiagnosticDeviceTotalFuelId";						$labeler[11]="Device Total Fuel";						//	20727.85	
	$moder[12]="DiagnosticCoolantLevelId";							$labeler[12]="Coolant Level";							//	98.025
	$moder[13]="DiagnosticEngineCoolantTemperatureId";				$labeler[13]="Engine Coolant Temperature";				//	29
	$moder[14]="DiagnosticEngineOilTemperatureID";					$labeler[14]="Engine Oil Temperature";					//	31
	$moder[15]="DiagnosticEngineHoursId";							$labeler[15]="Engine Hours";							//	36674280	
	$moder[16]="DiagnosticEngineSpeedId";							$labeler[16]="Engine Speed";							//	293	
	$moder[17]="DiagnosticEngineRoadSpeedId";						$labeler[17]="Engine Road Speed";						//	0
	$moder[18]="DiagnosticFuelLevelId";							$labeler[18]="Fuel Level";							//	57.6387	
	$moder[19]="DiagnosticGearPositionId";							$labeler[19]="Gear Position";							//	-1
	$moder[20]="DiagnosticGoDeviceVoltageId";						$labeler[20]="GoDevice Voltage";						//	14.0744438
	$moder[21]="DiagnosticHarnessDetected9PinId";					$labeler[21]="Harness Detected 9 Pin";					//	1
	$moder[22]="DiagnosticIgnitionId";								$labeler[22]="Ignition Timing";						//	1
	$moder[23]="DiagnosticJ1708EngineProtocolDetectedId";				$labeler[23]="J1708 Engine Protocol Detected";			//	1
	$moder[24]="DiagnosticJ1939CanEngineProtocolDetectedId";			$labeler[24]="J1939 Can Engine Protocol Detected";		//	1	
	$moder[25]="DiagnosticOutsideTemperatureId";						$labeler[25]="Outside Temperature";					//	21
	$moder[26]="DiagnosticParkingBrakeId";							$labeler[26]="Parking Brake";							//	0
	$moder[27]="DiagnosticPositionValidId";							$labeler[27]="Position Valid";						//	0
	$moder[28]="DiagnosticTotalFuelUsedId";							$labeler[28]="Total Fuel Used";						//	185144
	$moder[29]="DiagnosticTotalPTOHoursId";							$labeler[29]="Total PTO Hours";						//	1051920
	$moder[30]="DiagnosticTotalIdleHoursId";						$labeler[30]="Total Idle Hours";						//	13516740
	$moder[31]="DiagnosticTotalIdleFuelUsedId";						$labeler[31]="Total Idle Fuel Used";					//	14312.5
	$moder[32]="DiagnosticTotalTripIdleFuelUsedId";					$labeler[32]="Total Trip Idle Fuel Used";				//	1.39
	$moder[33]="DiagnosticTotalTripFuelUsedId";						$labeler[33]="Total Trip Fuel Used";					//	8.98	
	$moder[34]="DiagnosticVehicleActiveId";							$labeler[34]="Vehicle Active";						//	1
	$moder[35]="DiagnosticVehicleProgrammedCruiseHighSpeedLimitId";		$labeler[35]="Vehicle Programmed Cruise High Speed Limit";	//	165
	$moder[36]="DiagnosticVehicleProgrammedMaximumRoadspeedLimitId";		$labeler[36]="Vehicle Programmed Maximum Road Speed Limit";	//	191
	
	$submenu="
		<table cellpadding='0' cellspacing='0' border='1' width='1600'>
		<tr>
	";
	for($i=1; $i <=36; $i++)
	{		
		$submenu.="<td valign='top'><a href='?diagnotics=".$i."'>".$labeler[$i]."</a><br>".$moder[$i]."</td>";	
		
		if($i%4==0)		$submenu.="</tr><tr>";
	}
	$submenu.="
		</tr>
		<tr>
			<td valign='top' colspan='4' align='center'><a href='?diagnotics=0'>Run All for Truck 001 (Device ID='b1')</a><br>".$moder[$i]."</td>
		</tr>
		</table>
	";
	
	global $defaultsarray;
	$date_from=trim($defaultsarray['geotab_last_odometer']);
	if($cd==1)	$date_from=trim($defaultsarray['geotab_last_odometer_special']);
		
	//if(trim($device_id)=="b1F")	$date_from="2018-07-02 10:19:49";

	$searching='&search={';	//"groups":[{"id":"GroupVehicleId"}],
	if(trim($device_id)!="")	
	{
		$searching.='"deviceSearch":{"id":"'.trim($device_id).'"},';	
		$date_from=date("Y-m-d",strtotime("-15 days",time()));			//if the device is selected specifically... get last seven days worth in case the truck is not moving.
	}
          
     if($cd > 0)	$searching.='"diagnosticSearch":{"id":"'.trim($moder[ $cd ]).'"},';		
	if($cd==1)
	{
		$date_from=date("Y-m-d",strtotime("-1 days",time()));			//if the device is selected specifically... get last seven days worth in case the truck is not moving.	
	}
	$searching.='"fromDate":"'.date("Y-m-d",strtotime($date_from)).'T00:00:00.000Z"}';				//,"toDate":"'.date("Y-m-d",time()).'T23:59:59.000Z"	
	
			
	$url='Get?typeName=StatusData'.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
	//GetFeed
	//$url.="&resultsLimit=10";	//&search={}
			
	$devices="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	
	if($show_debug > 0)      echo "<br>1. Get (Devices) Result [".trim($device_id)."]: ".var_dump($dres)."<br>";	
	
	if(!is_array($dres) && trim($dres)=="New Session Login" || count($dres)==3)
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
     
     if($show_debug > 0)      echo '<br>URL: https://my241.geotab.com/apiv1/'.$url.'<br><br>';
     
     if($show_debug > 0)      echo "<br>2. Get (Device Diagnostics) Result [".trim($device_id)."]: <pre>".print_r($dres)."<br>";	
	
	$devices=$submenu;
	
	$devices.="<br>".($cd > 0 ? "<b>".$labeler[$cd]."</b> " : "")."Result Count=".count($dres)."<br>";	
	
	$favorite_odometer=2;
		
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$devices.="<br>Device Diagnostic 1 Result. Total Device Diagnostics Found=".count($arr["result"]).".";	
			$mycntr=0;
			for($j=0; $j < count($arr["result"]); $j++)	
			{				
				
				$temp=$arr["result"][$j];
				
				if(trim($temp['id'])!="" || ($temp['dateTime']=="9999-12-31T23:59:59.999Z" && trim($device_id)!=""))
				{
     				$odometer=floatval(trim($temp['data']));
     				if($cd==1 || $cd==2 || $cd==3)	$odometer=$odometer / 1609.3;	//convert from meters to miles.		(1 KM=0.621371 Mi... 1 Mi=1.609344 KM)
     				
     				//375580656 m / 1609.3= 233,381.38
     				//375580656 m / 1600.0= 234,737.91
     				//375580656 m / 1593.22=235,736.84
     				
     				if(isset($temp['id']))		$temp['id']=0;
     				
     				$devices.="<br>Diagnostic ".$j.".  <b>ID=".$temp['id']."</b>. (Version ".(isset($temp['version']) ? $temp['version'] : "N/A").") 
     							Device <b>".$temp['device']['id']."</b> | Diagnostic ID (Name) <b>".$temp['diagnostic']['id']."</b> 
     							<i>DateTime ".$temp['dateTime']."</i> --- ".$labeler[$cd]." <b>".$odometer."</b>";	
     				
     				
     				if(($cd==1 || $cd==$favorite_odometer) && $odometer > 0 && trim($temp['device']['id'])!="")
     				{	//update the geotab truck odometer reading...
     					$sql = "
     						update trucks set
     							geotab_last_odometer_reading='".sql_friendly( $odometer )."',
     							geotab_last_odometer_date=NOW()
     						where geotab_device_id='".sql_friendly( trim($temp['device']['id']) )."'
     							and geotab_odometer_update_mode='".($cd==1 ? "1" : "0")."'
     					";
     					simple_query($sql);
     					
     					$devices.=" ---ODOMETER UPDATED to ".$odometer."!";	
     				}
     				     				
     				$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_odometer_diagnostics where diagnostic_mode='".sql_friendly($cd)."' and geotab_id='".sql_friendly(trim($temp["id"]))."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					if($found_this==0 && $diagnotics_save > 0)
					{
          				$sql = "
          					insert into ".mrr_find_log_database_name()."geotab_odometer_diagnostics
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     							diagnostic_mode,
     							date_time,
     							diagnostic_name,
     							non_odo_data,
     							odometer)
     						values
     							(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp['id']))."',
     							'".sql_friendly(trim($temp['device']['id']))."',
     							'".sql_friendly($cd)."',
     							'".sql_friendly(trim($temp['dateTime']))."',
     							'".sql_friendly(trim($temp['diagnostic']['id']))."',
     							'".sql_friendly(trim($temp['data']))."',
     							'".sql_friendly($odometer)."')
          				";
          				simple_query($sql);
     				}
     				
     				$mycntr++;
				}
				else
				{
					echo "<br>Skipped...<br>";	
					$devices.="<br>Diagnostic ".$j.".  <b>ID=".$temp['id']."</b>. (Version ".(isset($temp['version']) ? $temp['version'] : "N/A").") 
     							Device <b>".$temp['device']['id']."</b> | Diagnostic ID (Name) <b>".$temp['diagnostic']['id']."</b> 
     							<i>DateTime ".$temp['dateTime']."</i> --- ".$labeler[$cd]." <b>".$odometer."</b>";	
				}
			}			
						
			if($mycntr > 0 && $cd==$favorite_odometer)
			{
				$sqlu="update defaults set xvalue_string='".date("Y-m-d H:i:s",time())."' where xname='geotab_last_odometer'";
     			simple_query($sqlu);
     		}
     		if($mycntr > 0 && $cd==1)
			{
				$sqlu="update defaults set xvalue_string='".date("Y-m-d H:i:s",time())."' where xname='geotab_last_odometer_special'";
     			simple_query($sqlu);
     		}
		}
		else
		{
			echo "<br>Skipped MAIN...<br>";	
		}
		$devices.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
		
		if($mycntr ==0 || $cd==1)	echo "<br>Get (Device Diagnostics) Result: ".var_dump($dres)."<br><br>URL: https://my241.geotab.com/apiv1/".$url."<br><br>";	
	}
     //if($show_debug > 0)  echo "<b>DEBUG OUTPUT:</b> <br>".$devices."";
     return $devices;     	
}	


function mrr_send_geotab_message_all_trucks($message,$urgent=0)
{
	$sent_messages=0;
	
	$sql = "
		select id
		from trucks
		where deleted<=0
			and active > 0
			and geotab_device_id !=''				
		order by name_truck asc,id asc
	";		
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$sent=mrr_send_geotab_text_message($row['id'],$message,$urgent);
		if($sent > 0)	$sent_messages++;
	}
	return $sent_messages;
}



function mrr_send_geotab_text_message_dispatch($truck_id,$message,$urgent=0,$from=0,$sess_user_id=0,$zone="",$long=0,$lat=0,$driver_id=0,$stop_id=0)
{
	global $defaultsarray;
     
     $tmp=trim($defaultsarray['gmt_offset_peoplenet']);
     $tmp=(int) str_replace("-","",$tmp);
     
     
     $offset_tzone=$tmp;      //5 or 6 depending on CDT vs CST
     
     //$offset_tzone=5;      //5 or 6 depending on CDT vs CST
	
	$user_name="b11";						//MRR
	if($from==1)		$user_name="b7";		//Dale	(logistics)
	if($from==2)		$user_name="b5";		//Dale	(transportation)
	if($from==3)		$user_name="b8";		//James
	
	$device_id="";
	$sql = "
		select geotab_device_id
		from trucks
		where id='".sql_friendly($truck_id) ."'
	";		
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$device_id=trim($row['geotab_device_id']);
	}
	
	$driver_name="";
	if($driver_id > 0)
	{
		//switch from the truck to the driver...or at least prefix the name of the driver to the stop message			
		$sql = "
     		select name_driver_first,payroll_first_name
     		from drivers
     		where id='".sql_friendly($driver_id) ."'
     	";		
     	$data=simple_query($sql);
     	if($row=mysqli_fetch_array($data))
     	{
     		$driver_name=trim($row['name_driver_first']).", ";
     		if(trim($row['payroll_first_name'])!="")		$driver_name=trim($row['payroll_first_name']).", ";
     	}
	}
	
	
	$message=trim("".$driver_name."".$message);
     $message=str_replace('"',"'",$message);
     $message=str_replace(chr(9)," ",$message);
     $message=str_replace(chr(10)," ",$message);
     $message=str_replace(chr(13)," ",$message);
	$mesage_id="0";
	
	if(trim($device_id)!="" && trim($message)!="")
	{         	
     	$from_date=''.date("Y-m-d",time()).'T00:00:00.000Z';
     	$to_date=''.date("Y-m-d",strtotime("+1 year",time())).'T00:00:00.000Z';
     	
     	$fields='&entity={';
     	$fields.='"user":{"id":"'.$user_name.'"},';				//  "id":"b4" 
     	$fields.='"isDirectionToVehicle":true,';
     	$fields.='"activeFrom":"2018-01-01T00:00:00.000Z",';		//'.$from_date.'
     	$fields.='"activeTo":"2050-01-01T00:00:00.000Z",';		//'.$to_date.'
     	
     	//$fields.='"messageContent":{"contentType":"Normal","message":"'.$message.'","urgent":'.($urgent > 0 ? "true" : "false").'},';		//"TextMessage":{,}
     	     	 
     	$fields.='"messageContent":{"address":"'.trim($zone).'","contentType":"Location","latitude":'.$lat.',"longitude":'.$long.',"message":"'.$message.'"},';
     	     	          	
     	//$fields.='"messageContent":{"contentType":"CannedResponse","message":"'.$message.'","cannedResponseOptions":{0:{"text":"OK"}}},';		//"urgent":'.($urgent > 0 ? "true" : "false").'
     	//$fields.='"messageContent":{"contentType":"CannedResponse","message":"'.$message.'","cannedResponseOptions":{"text":"OK"}},';	
          
          
          $fields.='"sent":"'.date("Y-m-d",time()).'T'.date("H:i:s",strtotime("+".$offset_tzone." hours",time())).'.000Z",';
          
     	$fields.='"device":{"id":"'.$device_id.'"}';
     	
     	$fields.='}';
     	
     	$fields=str_replace(" ","+",$fields);
     	$fields=str_replace("++","+",$fields);
                    
          //$searching="";
          //$searching='&search={"fromDate":"'.date("Y-m-d",time()).'T00:00:00.000Z","groups":[{"id":"GroupVehicleId"}]}';
                    
          $url='Add?typeName=TextMessage'.$fields.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
		
		if($stop_id > 0)
          {
               //store the GeoTab URL for logging purposes... since apparently GeOTab doesn't have their own log.     
               $sqlu="
                    	update load_handler_stops set
                    		geotab_api_msg_url='".sql_friendly(trim("".mrr_find_geotab_url_2()."".$url.""))."'
                    	where id='".sql_friendly($stop_id)."'
                    ";
               simple_query($sqlu);
               
               //GeoTab     
               mrr_add_geotab_url_log("GpsTextMessageSend", "Date: ".date("Y-m-d H:i:s",time())." | URL: ".trim("".mrr_find_geotab_url_2()."".$url.""));
          }
				
		
		$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
		if(!is_array($dres) && trim($dres)=="New Session Login")
		{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
			mrr_get_authenticate();
			$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
		}
		
		$temp_id="0";
		
		//echo "<br><b>TextMessage SENT Result:</b><br>".trim($dres)."<br>";
          echo "<br><b>GeoTab Stop Message URL:</b><br>".mrr_find_geotab_url_2()."".$url."<br>&nbsp;<br>";     		
		
		if(count($dres) > 1)
		{
			$arr=$dres;
     		if(isset($arr["result"]))
     		{
     			if(!is_array($arr["result"]))
     			{
     				$temp_id=trim($arr["result"]);
     				     				
     				$sql = "
						insert into ".mrr_find_log_database_name()."geotab_messages_sent
							(id,
							linedate_added,
							truck_id,
							user_id,
							geotab_id,
							device_id,
							urgent_flag,							
							message_sent,
							archived,
							actual_user_id)
						values
							(NULL,
							NOW(),
							'".sql_friendly($truck_id)."',
							'".sql_friendly($from)."',
							'".sql_friendly($temp_id)."',
							'".sql_friendly($device_id)."',
							'".sql_friendly($urgent)."',
							'".sql_friendly($message)."',
							0,
							'".sql_friendly($sess_user_id)."')
					";
					simple_query($sql);
     			}
     		}
		}
		else
		{     			
			echo "<br><b>TextMessage Result:</b><br>".var_dump($dres)."<br>";
		}
		
		
		/*
		if(count($dres)==2)
     	{
     		$arr=$dres;
     		if(isset($arr["result"]))
     		{
     			if(!is_array($arr["result"]))
     			{
     				$temp_id=(int) $arr["result"];
     			}
     			else
     			{
     				for($j=0; $j < count($arr["result"]); $j++)	
     				{
     					$temp=$arr["result"][$j];
     					
     					$temp_id=(int) $temp["id"];
     				}
     			}
     		}
     	}
     	elseif(!is_array($dres))
     	{
     		$temp_id=(int) $dres;
     	}
     	else
     	{
     		echo "<br><b>TextMessage Result:</b><br>".var_dump($dres)."<br>";	
     	}
		*/
		$mesage_id="0";
		if(trim($temp_id)!="")		$mesage_id=trim($temp_id);
	}
	elseif(trim($message)!="")
	{
		$mesage_id="-1";			//blank message....skip sending a message.
	}
	else
	{
		$mesage_id="-2";			//no device set for this truck...skip this.
	}
	return $mesage_id;
}

function mrr_send_geotab_text_message_dispatch_copilot($truck_id,$message,$copilot,$from=0,$sess_user_id=0,$long=0,$lat=0,$driver_id=0,$addr="")
{    //Same as the mrr_send_geotab_text_message_dispatch function, but used for NEW CoPilot "Navigate To ..." links.  Added Jan 2021 by MRR.
     global $defaultsarray;
     
     $tmp=trim($defaultsarray['gmt_offset_peoplenet']);
     $tmp=(int) str_replace("-","",$tmp);
     
     
     $offset_tzone=$tmp;      //5 or 6 depending on CDT vs CST
     
     $user_name="b11";						//MRR
     if($from==1)		$user_name="b7";		//Dale	(logistics)
     if($from==2)		$user_name="b5";		//Dale	(transportation)
     if($from==3)		$user_name="b8";		//James
     
     $device_id="";
     $sql = "
		select geotab_device_id
		from trucks
		where id='".sql_friendly($truck_id) ."'
	";
     $data=simple_query($sql);
     if($row=mysqli_fetch_array($data))
     {
          $device_id=trim($row['geotab_device_id']);
     }
     
     //eliminate zip codes without "-" for the zip plus four... if the zip code is > 5 digits... which seem to be Carlex loads.  Messes up CoPilot zip codes...(?)
     $sqlu = "
		update load_handler_stops set
	           shipper_zip=CONCAT(SUBSTR(shipper_zip,1,5) ,'-',SUBSTR(shipper_zip,-4,4) )
          where LOCATE('-',shipper_zip)=0 and LOCATE(' ',shipper_zip)=0
                and LENGTH(shipper_zip)>5 
          order by id DESC 
	";
     simple_query($sqlu);
     
     
     $driver_name="";
     if($driver_id > 0)
     {
          //switch from the truck to the driver...or at least prefix the name of the driver to the stop message			
          $sql = "
     		select name_driver_first,payroll_first_name
     		from drivers
     		where id='".sql_friendly($driver_id) ."'
     	";
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {
               $driver_name=trim($row['name_driver_first']).", ";
               if(trim($row['payroll_first_name'])!="")		$driver_name=trim($row['payroll_first_name']).", ";
          }
     }
     
     
     $message=trim("MRR Test CoPilot: ".$message);      //Navigate to       //".$driver_name."
     $message=str_replace('"',"'",$message);
     $message=str_replace(chr(9)," ",$message);
     $message=str_replace(chr(10)," ",$message);
     $message=str_replace(chr(13)," ",$message);
     $mesage_id="0";
     
     //Full address
     //$copilot="copilot://options?type=STOPS&Stop=City Hall|1400 John F Kennedy Blvd|Philadelphia|19107|PA|PA|39.9527572|-75.1638264&stop=Yankee Stadium|1 E 161 St|The Bronx|10451|NY|NY|40.829611|73.926211";
     //Partial address information can be passed by leaving empty the fields you are not including.
     //$copilot="copilot://options?type=STOPS&Stop=Old Office|||WC1A 2RP||GB|||";
     //Coordinates can be sent in a decimal format (ex. 51.518220, -0.122805,) or as long integers (+51518220, -122805). When entering latitude and longitude, “+” is optional however “-“ is mandatory where required.
     //$copilot="copilot://options?type=STOPS&Stop=Old Office||||||51.51880|-0.122805&stop=New Office||||||51.51883|-0.122890";
     
     
     //$copilot="";
     $copilot=str_replace('"',"'",$copilot);
     $copilot=str_replace(chr(9)," ",$copilot);
     $copilot=str_replace(chr(10)," ",$copilot);
     $copilot=str_replace(chr(13)," ",$copilot);
     
     if(trim($device_id)!="" && trim($message)!="")
     {
          $from_date=''.date("Y-m-d",time()).'T00:00:00.000Z';
          $to_date=''.date("Y-m-d",strtotime("+1 year",time())).'T00:00:00.000Z';
          
          $fields='&entity={';
          $fields.='"user":{"id":"'.$user_name.'"},';				//  "id":"b4" 
          $fields.='"isDirectionToVehicle":true,';
          $fields.='"activeFrom":"2021-01-01T00:00:00.000Z",';		//'.$from_date.'
          $fields.='"activeTo":"2050-01-01T00:00:00.000Z",';		//'.$to_date.'
          
          //$fields.='"messageContent":{"message":"['.$message.']('.$copilot.')","contentType":"Normal"},';          // -TEST-MRR
     
          //$addr=str_replace(",","%2C",$addr);
          //$message=str_replace(",","%2C",$message);
     
          $fields.='"messageContent":{';          // -TEST-MRR
          $fields.=      '"message":"'.$message.'",';
          $fields.=      '"address":"'.$addr.'",';
          $fields.=      '"longitude":"'.$long.'",';
          $fields.=      '"latitude":"'.$lat.'",';
          $fields.=      '"contentType":"Location"';
          $fields.='},';
     
          $fields.='"sent":"'.date("Y-m-d",time()).'T'.date("H:i:s",strtotime("+".$offset_tzone." hours",time())).'.000Z",';
          
          //$searching="";
          //$searching='&search={"fromDate":"'.date("Y-m-d",time()).'T00:00:00.000Z","groups":[{"id":"GroupVehicleId"}]}';     
     
          $fields.='"device":{"id":"'.$device_id.'"}';
          
          $fields.='}';
          
          /*
           * Add?typeName=TextMessage&
           *   entity={
           *        "user":{"id":"b11"},
           *        "isDirectionToVehicle":true,
           *        "activeFrom":"2021-01-01T00:00:00.000Z",
           *        "activeTo":"2050-01-01T00:00:00.000Z",
           *        "messageContent":{
           *             "message":"MRR+Test+CoPilot:+3050+Barry+Drive,+Portland,+TN,+37148",
           *             "address":"3050+Barry+Drive,+Portland,+TN,+37148",
           *             "longitude":"-86.59668731689453",
           *             "latitude":"36.59251785",
           *             "contentType":"Location"
           *        },
           *        "sent":"2021-05-07T22:34:33.000Z",
           *        "device":{"id":"b93"}
           *   }
           *   &credentials={"database":"conard","userName":"michael@sherrodcomputers.com","sessionId":"XY-NAfjKjoSUwOihQwi_Dg"}
           * 
           * 
              "messageContent":{
                 "message":"Address Test Dispatch",
                 "contentType":"Location",
                 "address":"US-84, Abilene, TX, USA",
                 "longitude":-99.80889261746778,
                 "latitude":32.45384984286474
              }
           */
          
          $fields=str_replace(" ","+",$fields);
     
          //$fields=str_replace(",","%2C",$fields);
          
          $fields=str_replace("++","+",$fields);
          
          $url='Add?typeName=TextMessage'.$fields.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
     
          echo "<br><b>COPILOT TextMessage SENT Result:</b><br>URL: ".$url."<br>".trim($dres)."<br>";
          return 0;
                              
          $dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
          if(!is_array($dres) && trim($dres)=="New Session Login")
          {	//Had to re-initiate the session ID by logging in again.  Repeat operation.
               mrr_get_authenticate();
               $dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
          }
          
          $temp_id="0";
          
          //echo "<br><b>COPILOT TextMessage SENT Result:</b><br>URL: ".$url."<br>".trim($dres)."<br>";     		
          
          if(count($dres) > 1)
          {
               $arr=$dres;
               if(isset($arr["result"]))
               {
                    if(!is_array($arr["result"]))
                    {
                         $temp_id=trim($arr["result"]);
                         
                         $sql = "
						insert into ".mrr_find_log_database_name()."geotab_messages_sent
							(id,
							linedate_added,
							truck_id,
							user_id,
							geotab_id,
							device_id,
							urgent_flag,							
							message_sent,
							archived,
							actual_user_id)
						values
							(NULL,
							NOW(),
							'".sql_friendly($truck_id)."',
							'".sql_friendly($from)."',
							'".sql_friendly($temp_id)."',
							'".sql_friendly($device_id)."',
							'".sql_friendly($urgent)."',
							'".sql_friendly($message)."',
							0,
							'".sql_friendly($sess_user_id)."')
					";
                         simple_query($sql);
                    }
               }
          }
          else
          {
               echo "<br><b>TextMessage Result:</b><br>".var_dump($dres)."<br>";
          }
          
          
          /*
          if(count($dres)==2)
          {
               $arr=$dres;
               if(isset($arr["result"]))
               {
                    if(!is_array($arr["result"]))
                    {
                         $temp_id=(int) $arr["result"];
                    }
                    else
                    {
                         for($j=0; $j < count($arr["result"]); $j++)	
                         {
                              $temp=$arr["result"][$j];
                              
                              $temp_id=(int) $temp["id"];
                         }
                    }
               }
          }
          elseif(!is_array($dres))
          {
               $temp_id=(int) $dres;
          }
          else
          {
               echo "<br><b>TextMessage Result:</b><br>".var_dump($dres)."<br>";	
          }
          */
          $mesage_id="0";
          if(trim($temp_id)!="")		$mesage_id=trim($temp_id);
     }
     elseif(trim($message)!="")
     {
          $mesage_id="-1";			//blank message....skip sending a message.
     }
     else
     {
          $mesage_id="-2";			//no device set for this truck...skip this.
     }
     return $mesage_id;
}

function mrr_send_geotab_text_message($truck_id,$message,$urgent=0,$from=0,$sess_user_id=0)
{
	global $defaultsarray;
	
	$user_name="b11";						//MRR
	if($from==1)		$user_name="b7";		//Dale	(logistics)
	if($from==2)		$user_name="b5";		//Dale	(transportation)
	if($from==3)		$user_name="b8";		//James
	
	$device_id="";
	$sql = "
		select geotab_device_id
		from trucks
		where id='".sql_friendly($truck_id) ."'
	";		
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$device_id=trim($row['geotab_device_id']);
	}
	
	$message=trim($message);
     $message=str_replace('"',"'",$message);
	$mesage_id="0";
	
	if(trim($device_id)!="" && trim($message)!="")
	{         	
     	$from_date=''.date("Y-m-d",time()).'T00:00:00.000Z';
     	$to_date=''.date("Y-m-d",strtotime("+1 year",time())).'T00:00:00.000Z';
     	
     	$fields='&entity={';
     	$fields.='"user":{"id":"'.$user_name.'"},';				//  "id":"b4" 
     	$fields.='"isDirectionToVehicle":true,';
     	$fields.='"activeFrom":"2018-01-01T00:00:00.000Z",';		//'.$from_date.'
     	$fields.='"activeTo":"2050-01-01T00:00:00.000Z",';		//'.$to_date.'
     	
     	$fields.='"messageContent":{"contentType":"Normal","message":"'.$message.'","urgent":'.($urgent > 0 ? "true" : "false").'},';		//"TextMessage":{,}
     	          	
     	//$fields.='"messageContent":{"contentType":"CannedResponse","message":"'.$message.'","cannedResponseOptions":{0:{"text":"OK"}}},';		//"urgent":'.($urgent > 0 ? "true" : "false").'
     	//$fields.='"messageContent":{"contentType":"CannedResponse","message":"'.$message.'","cannedResponseOptions":{"text":"OK"}},';	
                    
          //$searching="";
          //$searching='&search={"fromDate":"'.date("Y-m-d",time()).'T00:00:00.000Z","groups":[{"id":"GroupVehicleId"}]}';
          
          $fields.='"device":{"id":"'.$device_id.'"}';
     	
     	$fields.='}';
     	
     	$fields=str_replace(" ","+",$fields);
     			
		$url='Add?typeName=TextMessage'.$fields.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
		
		$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
		if(!is_array($dres) && trim($dres)=="New Session Login")
		{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
			mrr_get_authenticate();
			$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
		}
		
		$temp_id="0";
		
		//echo "<br><b>TextMessage SENT Result:</b><br>".trim($dres)."<br>";     		
		
		if(count($dres) > 1)
		{
			$arr=$dres;
     		if(isset($arr["result"]))
     		{
     			if(!is_array($arr["result"]))
     			{
     				$temp_id=trim($arr["result"]);
     				     				
     				$sql = "
						insert into ".mrr_find_log_database_name()."geotab_messages_sent
							(id,
							linedate_added,
							truck_id,
							user_id,
							geotab_id,
							device_id,
							urgent_flag,							
							message_sent,
							archived,
							actual_user_id)
						values
							(NULL,
							NOW(),
							'".sql_friendly($truck_id)."',
							'".sql_friendly($from)."',
							'".sql_friendly($temp_id)."',
							'".sql_friendly($device_id)."',
							'".sql_friendly($urgent)."',
							'".sql_friendly($message)."',
							0,
							'".sql_friendly($sess_user_id)."')
					";
					simple_query($sql);
     			}
     		}
		}
		else
		{     			
			echo "<br><b>TextMessage Result:</b><br>".var_dump($dres)."<br>";
		}
		
		
		/*
		if(count($dres)==2)
     	{
     		$arr=$dres;
     		if(isset($arr["result"]))
     		{
     			if(!is_array($arr["result"]))
     			{
     				$temp_id=(int) $arr["result"];
     			}
     			else
     			{
     				for($j=0; $j < count($arr["result"]); $j++)	
     				{
     					$temp=$arr["result"][$j];
     					
     					$temp_id=(int) $temp["id"];
     				}
     			}
     		}
     	}
     	elseif(!is_array($dres))
     	{
     		$temp_id=(int) $dres;
     	}
     	else
     	{
     		echo "<br><b>TextMessage Result:</b><br>".var_dump($dres)."<br>";	
     	}
		*/
		$mesage_id="0";
		if(trim($temp_id)!="")		$mesage_id=trim($temp_id);
	}
	elseif(trim($message)!="")
	{
		$mesage_id="-1";			//blank message....skip sending a message.
	}
	else
	{
		$mesage_id="-2";			//no device set for this truck...skip this.
	}
	return $mesage_id;
}

function mrr_get_geotab_get_txtmsg($truck_id=0,$msg="",$subject="")
{		
	//$date_from="2018-03-01";
	//$date_to="2018-04-01";
	$last_msg_id=0;
	
	global $defaultsarray;
	$date_from=trim($defaultsarray['geotab_last_msg_date']);	
	$date_to=trim($defaultsarray['geotab_last_msg_date']);
	$last_msg_id=(int) trim($defaultsarray['geotab_last_msg_id']);	
							
	$searching='&search={"fromDate":"'.$date_from.'T00:00:00.000Z"}';		//,"toDate":"'.$date_to.'T23:59:59.999Z"
	
	$url='Get?typeName=TextMessage'.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
	
	//$url.="&resultsLimit=10";	//&search={}
			
	$messages="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	if(!is_array($dres) && trim($dres)=="New Session Login")
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/Get?typeName=Device&credentials={"database":"conard","userName":"michael@sherrodcomputers.com","sessionId":"8930957207201481481"}';
	
	echo "<br>Get (Messages) URL https://my241.geotab.com/apiv1/".$url." Result: ".var_dump($dres)."<br>";	
			
	$messages="<br>Result Count=".count($dres)."<br>";	
	
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$messages.="<br><b>Message 1 Result. Total Messages Found=".count($arr["result"]).".</b>";	
			
			$msg_found=0;
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				$temp=$arr["result"][$j];
				
				$show_message=1;
				$messages_holder="";
				
				$reply_req=0;
				$reply_opts="";
				
				//Added by MRR on 6/8/2021 to deal with bad GeoTab change where Device ID (truck) link is not set...............
				if($temp["device"]["id"]=="N" || $temp["device"]["id"]=="NoDeviceId")
                    {    //Truck Device ID is not set on this messsage, so try to find the messsage truck from the current driver.
                         //$temp["device"]["id"]
                         //$temp["user"]["id"]
                         
                         //filter out the non-driver users since these are never made by the drivers.... 
                         if($temp["user"]["id"]=="b11")             $show_message=0;
                         if($temp["user"]["id"]=="b25")             $show_message=0;
                         if($temp["user"]["id"]=="b26")             $show_message=0;
                         if($temp["user"]["id"]=="b27")             $show_message=0;
                         if($temp["user"]["id"]=="bE5")             $show_message=0;
                         if($temp["user"]["id"]=="b127")            $show_message=0;
                         //if($temp["user"]["id"]=="")             $show_message=0;
                         
                         if($show_message>0)
                         {
                              //$temp["device"]["id"]=mrr_find_truck_device_form_geotab_user_id($temp["user"]["id"]);
                              
                              //if($temp["device"]["id"]=="N")          $show_message=0;    //kill message, not truck device found.
                         }                           
                    }	
				//..............................................................................................................
                    $is_to_vehicle=0;
				if($temp["isDirectionToVehicle"]==true || $temp["isDirectionToVehicle"]=="true" || $temp["isDirectionToVehicle"]==1)    $is_to_vehicle=1;
                    
                    
				    				    				
				$messages_holder.="<br>Message ".$j.". --- Sent: ".$temp["sent"].".";        				
				if(isset($temp["delivered"]))		$messages_holder.="<br>Delivered ".$temp["delivered"].".";  //Active From ".$temp["activeFrom"]." To ".$temp["activeTo"]." --- 
				$messages_holder.="<br>Device:".$temp["device"]["id"].", ID=".$temp["id"].".";  
				$messages_holder.="<br>To Truck: ".($is_to_vehicle > 0 ? "YES" : "no").".";  	    				
				if(isset($temp["user"]))			$messages_holder.="<br>---User:  [".$temp["user"]["id"]."] -- Driver: ".(isset($temp["user"]["isDriver"]) ? "YES" : "no").".";
				
				if(isset($temp["read"]))			$messages_holder.="<br>Read: ".$temp["read"].".";  
				if(isset($temp["markedReadBy"]))	$messages_holder.="<br>---Read by:  [".$temp["markedReadBy"]["id"]."] -- Driver: ".(isset($temp["markedReadBy"]["isDriver"]) ? "YES" : "no").".";
				
				$messages_holder.="<br>Message Type: ".$temp["messageContent"]["contentType"]."";
				
				if($temp["messageContent"]["contentType"]=="Normal")
				{
					$messages_holder.="<div style='border:1px solid red; margin:10px 0px 10px 0px; padding:10px;'><b>Normal:</b><br>".$temp["messageContent"]["message"]."</div>";		
				}
				elseif($temp["messageContent"]["contentType"]=="CannedResponse")
				{
					$messages_holder.="<div style='border:1px solid red; margin:10px 0px 5px 0px; padding:10px;'><b>CannedResponse:</b><br>".$temp["messageContent"]["message"]."</div>";
					
					$messages_holder.="<div style='border:1px solid green; margin:5px 0px 10px 0px; padding:10px;'>";
					for($z=0; $z < count($temp["messageContent"]["cannedResponseOptions"]); $z++)
					{
						$messages_holder.="*** Option ".$z.". [ID ".$temp["messageContent"]["cannedResponseOptions"][$z]["id"]."]  - ".$temp["messageContent"]["cannedResponseOptions"][$z]["text"].".";
												
						$reply_opts.="<br>*** Option ".$z." [ID ".$temp["messageContent"]["cannedResponseOptions"][$z]["id"]."]: ".$temp["messageContent"]["cannedResponseOptions"][$z]["text"]."";
					}	
					$messages_holder.="</div>";
				}
				elseif($temp["messageContent"]["contentType"]=="StatusDataRequest")
				{
					$show_message=0;		//Skip these messages...
					if(!isset($temp["messageContent"]["message"])) $temp["messageContent"]["message"] = "";
					$messages_holder.="<div style='border:1px solid purple; background-color:#eeeeee; margin:10px 0px 10px 0px; padding:10px;'><b>StatusDataRequest:</b><br>".$temp["messageContent"]["message"]."</div>";	
				}
				else
				{
					$messages_holder.="<div style='border:1px solid orange; background-color:#eeeeee; margin:10px 0px 10px 0px; padding:10px;'><b>Other Message Type:</b><br>".$temp["messageContent"]["message"]."</div>";	
					
					if(isset($temp["messageContent"]["isAcknowledgeRequired"]) && isset($temp["messageContent"]["ids"]))	
					{
						$messages_holder.=" -- Reply Required: ".($temp["messageContent"]["isAcknowledgeRequired"]==true ? "YES" : "no").".";
					
						$reply_req="".($temp["messageContent"]["isAcknowledgeRequired"]==true ? "1" : "0")."";
					
						for($z=0; $z < count($temp["messageContent"]["ids"]); $z++)
						{
							$messages_holder.="<br> --- [".$z."] -- ".$temp["messageContent"]["ids"][$z]."";
								
							$reply_opts.="<br>Content ID ".$z.": ".$temp["messageContent"]["ids"][$z]."";
						}
					}	
				}
				$messages_holder.="<br><br><hr><br>";
				
				
				if($show_message > 0)	
				{
					$messages.=$messages_holder;
					$msg_found++;
					
					$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_messages_received where geotab_id='".sql_friendly(trim($temp["id"]))."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_messages_received
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     														
     							msg_to_truck,
     							date_sent,
     							date_delivered,
     							
     							geotab_user,
     							is_driver,
     							date_read,
     							read_by,
     							read_by_driver,
     							
     							msg_type,
     							reply_required,
     							msg_body,
     							msg_options,
     							
     							archived,
     							no_response_needed,
     							linedate_read,
     							read_user_id,
     							read_reply_id,
     							read_reply_user_id)
     						values
     							(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
     							'".sql_friendly(trim($temp["device"]["id"]))."',
     							
     							'".sql_friendly($is_to_vehicle)."',
     							'".sql_friendly(trim($temp["sent"]))."',
     							'".sql_friendly((isset($temp["delivered"]) ? trim($temp["delivered"]) : ""))."',
     							
     							".(isset($temp["user"])  		? "'".sql_friendly(trim($temp["user"]["id"]))."'" : "''").",
     							".(isset($temp["user"])  		? "'".sql_friendly((isset($temp["user"]["isDriver"]) ? "1" : "0"))."'" : "'0'").",
     							".(isset($temp["read"])  		? "'".sql_friendly(trim($temp["read"]))."'" : "''").",
     							".(isset($temp["markedReadBy"])  	? "'".sql_friendly(trim($temp["markedReadBy"]["id"]))."'" : "''").",
     							".(isset($temp["markedReadBy"])  	? "'".sql_friendly((isset($temp["markedReadBy"]["isDriver"]) ? "1" : "'0'"))."'" : "0").",
     							
     							'".sql_friendly(trim($temp["messageContent"]["contentType"]))."',
     							'".sql_friendly($reply_req)."',
     							'".sql_friendly(trim($temp["messageContent"]["message"]))."',
     							'".sql_friendly(trim($reply_opts))."',
     							
     							0,
     							0,
     							'0000-00-00 00:00:00',
     							0,
     							0,
     							0)
     					";
     					simple_query($sql);
					}
					
					$sqlu="update defaults set xvalue_string='".date("Y-m-d",time())."' where xname='geotab_last_msg_date'";
     				simple_query($sqlu);
				}
				/*
                    MessageContentType
                    
                    StatusDataRequest	string	Request for data from the truck.  (Added from testing, but not on the documentation.) 
                    
                    cannedResponse		string	Text message that also includes response options. See CannedResponseContent.
                    driverWhiteList	string	Text message with information to add/remove a driver from a GoDevice's white list. See DriverWhiteListContent.
                    goTalk			string	Text message that is converted to speech. Must have GOTalk.
                    ioxOutput			string	Text message that inclides instructions to open or close an IOX-OUTPUT relay. See IoxOutputContent.
                    location			string	Text message that includes a location. See LocationContent.
                    normal			string	Basic text message. See TextContent.
                    */
			}
			
			if($msg_found==0)		$messages.=" <span style='color:purple;'><b>NO USABLE MESSAGES FOUND.</b></span>";	
		}
		$messages.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
	}     	
	return $messages;	
}	

function mrr_get_geotab_get_datafeed($feed_type="",$id="",$last_dated="")
{		
	$id=trim(str_replace(" ","+",$id));
	
	if(trim($feed_type)=="")		$feed_type="Trip";
	$last_id=0;
	$last_date="2018-03-21";
	
	$def_id=0;	
	if(trim($feed_type)=="LogRecord")			$def_id=1;	//See LogRecord -- Record of log entries containing data for a device's position and speed at a specific date and time.
	if(trim($feed_type)=="StatusData")			$def_id=2;	//See StatusData -- A record that represents an engine status record from the engine system of the specific Device.
	if(trim($feed_type)=="FaultData")			$def_id=3;	//See FaultData -- A record that represents a fault code record from the engine system of the specific Device.
	if(trim($feed_type)=="Trip")				$def_id=4;	//See Trip -- Vehicles trip & summary. A complete "trip" defined as from vehicle start to and ends when vehicle restarts --which starts next trip.
	if(trim($feed_type)=="ExceptionEvent")		$def_id=5;	//See ExceptionEvent -- The event of an exception generated by Rule violation.
	if(trim($feed_type)=="DutyStatusLog")		$def_id=6;	//See DutyStatusLog -- HOS regulations record. Required to have driver,dateTime,status,and device. Location not required--calculated from device data.
	if(trim($feed_type)=="AnnotationLog")		$def_id=7;	//See AnnotationLog -- An AnnotationLog is a comment that can be associated with a DutyStatusLog. The Driver is the author of the AnnotationLog.
	if(trim($feed_type)=="DVIRLog")			$def_id=8;	//See DVIRLog -- Driver Vehicle Inspection Report: driver lists defects (Vehicle or Trailer). Once done (w/ remarks?),DVIR is acted upon, & marked (repaired or N/A (by another User?). Driver then marks log certified safe/unsafe (comments?)
	if(trim($feed_type)=="ShipmentLog")		$def_id=9;	//See ShipmentLog -- A ShipmentLog is a record of shipment transported by a specified vehicle for a duration of time.
	if(trim($feed_type)=="TrailerAttachment")	$def_id=10;	//See TrailerAttachment -- A TrailerAttachment is a record of the attachment of a Trailer to a Device over a period of time.
	if(trim($feed_type)=="IoxAddOn")			$def_id=11;	//See IoxAddOn -- Represents Iox Add-On (like modem or navigation device) attached to GO unit. Each is assigned channel - which is the serial port number that it typically communicates with.
	if(trim($feed_type)=="CustomData")			$def_id=12;	//See CustomData -- Generic Custom Data from a GO unit that was sent through from a third-party device that is attached to the serial port.
							
	global $defaultsarray;	
	if($def_id > 0)
	{
		//$last_id=(int) trim($defaultsarray['geotab_last_feed_id_'.$def_id.'']);	
		$last_date= trim($defaultsarray['geotab_last_feed_id_'.$def_id.'']);	
	}		
	 
	//for testing...
	$x="-86.5923843";	//"-86.59220123291016";		//East/West part
	$y="36.0172806";	//"36.02058029";			//North/South part
	
	$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
	$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;
		
	$gres=mrr_gps_point_box_creator($x,$y,$x_off,$y_off);
	
	$get_mode=0;
	if(trim($id)=="")	
	{
		//$last_date="2019-07-22T23:59:59.000Z";			//testing only......remove when done or comment out.
		$get_mode=1;
	}
	else
	{
		//$last_date="".date("Y-m-d",time())."T00:00:00.000Z";			//only grab the current day.	
		//if(trim($last_dated)!="")		$last_date="".date("Y-m-d",floatval(trim($last_date)))."T00:00:00.000Z";		//use last date for this truck unit.
		
		//if($last_dated==1563741091)		$last_date="".date("Y-m-d",time())."T00:00:00.000Z";			//only grab the current day.	...not set in trucks table.
		
		$last_date="".date("Y-m-d",time())."T00:00:00.000Z";			//testing only......remove when done or comment out.
	}
	
	$searching="";	
	if(strlen($last_date) > 10)	
	{
		$searching='&search={"fromDate":"'.$last_date.'","toDate":"'.date("Y-m-d",time()).'T23:59:59.000Z"}';		//
	}
	else
	{				
		$searching='&search={"fromDate":"'.$last_date.'T00:00:00.000Z","toDate":"'.date("Y-m-d",time()).'T23:59:59.000Z"}';
	}
     //$searching='&search={"fromDate":"'.date("Y-m-d",time()).'T00:00:00.000Z","groups":[{"id":"GroupVehicleId"}]}';
          
     if($last_id > 0)	$searching='&search={"id": "'.$last_id.'"}';		//"'.trim($feed_type).'":"{}"	
	if($id!="")		$searching='&search={"deviceSearch":{"id":"'.$id.'"},"fromDate":"'.$last_date.'","toDate":"'.date("Y-m-d",time()).'T23:59:59.000Z"}';	
	
	$url='Get?typeName='.trim($feed_type).''.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
     
     if($def_id==8)         $get_mode=0;
     
	if($get_mode==1)
	{	    //GetFeed?typeName=LogRecord&search=%7B%22fromDate%22:%222019-07-21T23:59:59.000Z%22,%22toDate%22:%222019-07-22T23:59:59.000Z%22%7D  ... new method given to me by Oscar as of 7/23/2019...MRR	
		$url='GetFeed?typeName='.trim($feed_type).''.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
	}
		
	//$url.="&resultsLimit=10";	//&search={}
	
	echo '<br>https://my241.geotab.com/apiv1/'.$url.'';
					
	$feed="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	if(!is_array($dres) && trim($dres)=="New Session Login")
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/Get?typeName=Device&credentials={"database":"conard","userName":"michael@sherrodcomputers.com","sessionId":"8930957207201481481"}';
	
	if($def_id==11 || $def_id==12 || $def_id==5)		echo "<br>Get (".trim($feed_type)." Feed) Result: ".var_dump($dres)."<br>";	
			
	$feed="<br>Result Count=".count($dres)." since ".$last_date.".<br>";	
	
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$my_results=$arr["result"];
			
			if($get_mode==1)
			{
				$my_results=$arr["result"]['data'];
			}
					
			$feed.="<br><b>".trim($feed_type)." Feed Result. Total ".trim($feed_type)."(s) Found=".count($my_results).".</b><br>";	
			     			
			for($j=0; $j < count($my_results); $j++)	
			{
				$temp=$my_results[$j];
				
				$show_feed=1;
				$feed_holder="";
				
				$block_db_feed=1;
				
				     				
				if($def_id==1)
				{						
					$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["dateTime"]."";   
               		
               		$feed_holder.="<br>-- --- GPS: [".$temp["latitude"].", ".$temp["longitude"]."], Speed ".$temp["speed"]."."; 
               		
               		//$long=$temp["latitude"];
					//$lat=$temp["longitude"];
					//$in_bounds=mrr_gps_point_inside_bounds($long,$lat,$gres['pt0_lat_n'],$gres['pt1_lat_s'],$gres['pt1_long_e'],$gres['pt0_long_w']);	
					
					//$feed_holder.=" --- GPS point [".$long." , ".$lat."] is ".($in_bounds > 0 ? "<span style='color:#00CC00;'><b>In Bounds</b></span>" : "<span style='color:#CC0000;'><b>Out-of_bounds</b></span>").".";
               		
               		$feed_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"].".";  
               		
               		               		 
					$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
					
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     							feed_type,
                                        date_from,
                                        latitude,
                                        longitude,
                                        speed_mph)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
                           			'".sql_friendly(trim($temp["device"]["id"]))."',
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["dateTime"]))."',
                           			'".sql_friendly(trim($temp["latitude"]))."',
                           			'".sql_friendly(trim($temp["longitude"]))."',
                           			'".sql_friendly(trim($temp["speed"]))."')
     					";
     					//echo "<br>Inserted Query... ".$sql."<br>";
     					simple_query($sql);
     					
     					$sqlu="update defaults set xvalue_string='".trim($temp["dateTime"])."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);
     					
     					$sqlu="update trucks set geotab_last_location_date=NOW() where geotab_device_id!='' and geotab_device_id='".sql_friendly(trim($temp["device"]["id"]))."'";
     					simple_query($sqlu);
					}
				}
				elseif($def_id==2)
				{
               		$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["dateTime"]."";   
               		if(isset($temp["diagnostic"]))	$feed_holder.="<br>--Diagnostic: ".$temp["diagnostic"]["id"].".";
               		
               		$feed_holder.="<br>--Data:".$temp["data"].", Version ".$temp["version"]."."; 
               		
               		$feed_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"].".";   
               		
               		$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
					
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     							feed_type,
                                        date_from,
                                        version,
                                        data_txt,
                                        diagnostic_id)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
                           			'".sql_friendly(trim($temp["device"]["id"]))."',
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["dateTime"]))."',
                           			'".sql_friendly(trim($temp["version"]))."',
                           			'".sql_friendly(trim($temp["data"]))."',
                           			".(isset($temp["diagnostic"]) ? "'".sql_friendly(trim($temp["diagnostic"]["id"]))."'" : "''").")
     					";
     					simple_query($sql);
     					
     					$sqlu="update defaults set xvalue_string='".trim($temp["dateTime"])."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);
					}            		
				}
				elseif($def_id==3)
				{
               		$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["dateTime"]."";   
               		$feed_holder.="<br>".$temp["count"]." --- ".(isset($temp["failureMode"]["id"]) ? $temp["failureMode"]["id"] : "0")." --- ".$temp["faultState"].".";  
               		
               		if(isset($temp["controller"]))	$feed_holder.="<br>--Controller: ".$temp["controller"]["id"].".";
               		if(isset($temp["diagnostic"]))	$feed_holder.="<br>--Diagnostic: ".$temp["diagnostic"]["id"].".";
               		
               		$feed_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"]."."; 
               		
               		$feed_holder.="<br>--Lamps: Amber ".($temp["amberWarningLamp"]==true ? "YES" : "no")." 
               						- Malfunction ".($temp["malfunctionLamp"]==true ? "YES" : "no")." 
               						- Warning ".($temp["protectWarningLamp"]==true ? "YES" : "no")." 
               						- Stop ".($temp["redStopLamp"]==true ? "YES" : "no").".";  	    				
				    	
				    	$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
					
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     							feed_type,
                                        date_from,
                                        diagnostic_id,
                                        controller_id,
                                        cnt,
                                        failuer_mode,
                                        fault_state,
                                        amber_light,
                                        mal_light,
                                        warning_light,
                                        stop_light)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
                           			'".sql_friendly(trim($temp["device"]["id"]))."',
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["dateTime"]))."',
                           			".(isset($temp["diagnostic"]) ? "'".sql_friendly(trim($temp["diagnostic"]["id"]))."'" : "''").",
                           			".(isset($temp["controller"]) ? "'".sql_friendly(trim($temp["controller"]["id"]))."'" : "''").",
                           			'".sql_friendly(trim($temp["count"]))."',
                           			'".sql_friendly(trim("".(isset($temp["failureMode"]["id"]) ? $temp["failureMode"]["id"] : "0").""))."',
                           			'".sql_friendly(trim($temp["faultState"]))."',
                           			'".($temp["amberWarningLamp"]==true ? "1" : "0")."',
                           			'".($temp["malfunctionLamp"]==true ? "1" : "0")."',
                           			'".($temp["protectWarningLamp"]==true ? "1" : "0")."',
                           			'".($temp["redStopLamp"]==true ? "1" : "0")."')
     					";
     					simple_query($sql);
     					
     					$sqlu="update defaults set xvalue_string='".trim($temp["dateTime"])."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);
					}
				}
				elseif($def_id==4)
				{			
					$feed_holder.="<br>".trim($feed_type)." ".$j.". --- GPS: [".$temp["stopPoint"]["x"].", ".$temp["stopPoint"]["y"]."]. 
									Stop Duration:".$temp["stopDuration"]."";    
					$feed_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"].".";
					
					if(isset($temp["driver"]))			$feed_holder.="<br>--Driver:  [".$temp["driver"]["id"]."] -- Driver: ".(!empty($temp["driver"]["isDriver"]) && $temp["driver"]["isDriver"]==true ? "YES" : "no").".";
					$feed_holder.="<br>--Started ".$temp["start"]." Stopped ".$temp["stop"]." | 
									SeatBeltOff: ".($temp["isSeatBeltOff"]==true ? "YES" : "no")."."; 
					
					if(!isset($temp["averageSpeed"]))		$temp["averageSpeed"]=0;
					     					
					$feed_holder.="<br>--Speed: MAX ".$temp["maximumSpeed"]." | AVG ".$temp["averageSpeed"]." | 
									Distance ".$temp["distance"]." | Driving ".$temp["drivingDuration"]." | 
									Idling ".$temp["idlingDuration"]." | Next Trip ".$temp["nextTripStart"].".";  
					$feed_holder.="<br>--Work: Dist ".$temp["workDistance"]." | 
									Drving Duration ".$temp["workDrivingDuration"]." | 
									Stop Duration ".$temp["workStopDuration"].".";
					     					
					$feed_holder.="<br>--Speed Ranges: ".$temp["speedRange1"]." | ".$temp["speedRange1Duration"]." | 
									".$temp["speedRange2"]." | ".$temp["speedRange2Duration"]." | 
									".$temp["speedRange3"]." | ".$temp["speedRange3Duration"].".";
					$feed_holder.="<br>--After Hours: Start ".($temp["afterHoursStart"]==true ? "YES" : "no")." - End ".($temp["afterHoursEnd"]==true ? "YES" : "no")." | 
									Distance ".$temp["afterHoursDistance"]." | 
									Driving Duration ".$temp["afterHoursDrivingDuration"]." | 
									Stop Duration".$temp["afterHoursStopDuration"].".";  	    				
				    	
				    	$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
					
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     							feed_type,
     							latitude,
                                        longitude,
     							stop_duration,
                                        geotab_user,
                                        is_driver,
     							belt_off,
                                        started,
                                        stopped,
     							max_speed,
                                        avg_speed,
                                        distance,
                                        drive_duration,
                                        idle_duration,
                                        next_trip_stop,
                                        work_distance,
                                        work_drive_duration,
                                        work_stop_duration,
                                        range1_duration,
                                        range2_duration,
                                        range3_duration,
                                        range1,
                                        range2,
                                        range3,
                                        after_hrs_start,
                                        after_hrs_end,
                                        after_hrs_distance,
                                        after_hrs_drive_duration,
                                        after_hrs_stop_duration)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
                           			'".sql_friendly(trim($temp["device"]["id"]))."',
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["stopPoint"]["y"]))."',
                           			'".sql_friendly(trim($temp["stopPoint"]["x"]))."',
                           			'".sql_friendly(trim($temp["stopDuration"]))."',
                           			".(isset($temp["driver"]) ? "'".sql_friendly(trim($temp["driver"]["id"]))."'" : "''").",
                           			".(isset($temp["driver"]) ? "'".(!empty($temp["driver"]["isDriver"]) && $temp["driver"]["isDriver"]==true ? "1" : "0")."'" : "'0'").",    
                           			'".($temp["isSeatBeltOff"]==true ? "1" : "0")."',
                           			'".sql_friendly(trim($temp["start"]))."',
                           			'".sql_friendly(trim($temp["stop"]))."',
                           			'".sql_friendly(trim($temp["maximumSpeed"]))."',
                           			'".sql_friendly(trim($temp["averageSpeed"]))."',
                           			'".sql_friendly(trim($temp["distance"]))."',
                           			'".sql_friendly(trim($temp["drivingDuration"]))."',
                           			'".sql_friendly(trim($temp["idlingDuration"]))."',
                           			'".sql_friendly(trim($temp["nextTripStart"]))."',                           			
                           			'".sql_friendly(trim($temp["workDistance"]))."',
                           			'".sql_friendly(trim($temp["workDrivingDuration"]))."',
                           			'".sql_friendly(trim($temp["workStopDuration"]))."',
                           			'".sql_friendly(trim($temp["speedRange1Duration"]))."',
                           			'".sql_friendly(trim($temp["speedRange2Duration"]))."',
                           			'".sql_friendly(trim($temp["speedRange3Duration"]))."',
                           			'".sql_friendly(trim($temp["speedRange1"]))."',
                           			'".sql_friendly(trim($temp["speedRange2"]))."',
                           			'".sql_friendly(trim($temp["speedRange3"]))."',
                           			'".($temp["afterHoursStart"]==true ? "1" : "0")."',
                           			'".($temp["afterHoursEnd"]==true ? "1" : "0")."',
                           			'".sql_friendly(trim($temp["afterHoursDistance"]))."',
                           			'".sql_friendly(trim($temp["afterHoursDrivingDuration"]))."',
                           			'".sql_friendly(trim($temp["afterHoursStopDuration"]))."')
     					";
     					simple_query($sql);
     					
     					$sqlu="update defaults set xvalue_string='".date("Y-m-d",time())."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);
					}					
				}
				elseif($def_id==5)
				{                         	
                    	$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["activeFrom"]." to ".$temp["activeTo"]."";   
               		$feed_holder.="<br>Distance ".$temp["distance"]." --- Duration ".$temp["duration"]." --- Version ".$temp["version"].".";  
               		
               		if(isset($temp["driver"]))		$feed_holder.="<br>--Driver:  [".$temp["driver"]["id"]."] -- Driver: ".(!empty($temp["driver"]["isDriver"]) && $temp["driver"]["isDriver"]==true ? "YES" : "no").".";
               		if(isset($temp["rule"]))			$feed_holder.="<br>--Rule: ".$temp["rule"]["id"].".";
               		if(isset($temp["diagnostic"]["id"]))	$feed_holder.="<br>--Diagnostic: ".$temp["diagnostic"]["id"].".";
               		
               		$feed_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"]."."; 
               		
               		$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
					
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     							feed_type,
                                        date_from,
                                        date_to,
                                        distance,
                                        drive_duration,                                        
                                        version,
                                        geotab_user,
                                        is_driver,
                                        rule_id,
                                        diagnostic_id)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
                           			'".sql_friendly(trim($temp["device"]["id"]))."',
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["activeFrom"]))."',
                           			'".sql_friendly(trim($temp["activeTo"]))."',
                           			'".sql_friendly(trim($temp["distance"]))."',
                           			'".sql_friendly(trim($temp["duration"]))."',
                           			'".sql_friendly(trim($temp["version"]))."',
                           			".(isset($temp["driver"]) ? "'".sql_friendly(trim($temp["driver"]["id"]))."'" : "''").",
                           			".(isset($temp["driver"]) ? "'".(!empty($temp["driver"]["isDriver"]) && $temp["driver"]["isDriver"]==true ? "1" : "0")."'" : "'0'").",      
                           			".(isset($temp["rule"]) ? "'".sql_friendly(trim($temp["rule"]["id"]))."'" : "''").",
                           			".(isset($temp["diagnostic"]["id"]) ? "'".sql_friendly(trim($temp["diagnostic"]["id"]))."'" : "''").")
     					";
     					simple_query($sql);
     					     					
     					$sqlu="update defaults set xvalue_string='".trim($temp["activeFrom"])."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);
					}
				}
				elseif($def_id==6)
				{
					$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["dateTime"]."";   
               		$feed_holder.="<br>Status ".$temp["status"]." --- Origin ".$temp["origin"]." --- Version ".$temp["version"].".";  
               		$feed_holder.="<br>State ".$temp["state"]." --- Sequence ".$temp["sequence"]." --- Malfunction ".$temp["malfunction"].".";  
               		if(isset($temp["driver"]))	
               		{
               			$feed_holder.="<br>--Driver:  [".$temp["driver"]["id"]."]";
               			if(isset($temp["driver"]["isDriver"]))		$feed_holder.=" -- IS Driver: ".(!empty($temp["driver"]["isDriver"]) && $temp["driver"]["isDriver"]==true ? "YES" : "no").".";
               			$feed_holder.=".";
               		}
               		                   		
               		if(isset($temp["location"]))		$feed_holder.="<br>--Location: [".$temp["location"]["location"]["x"]." , ".$temp["location"]["location"]["y"]."].";
               		
               		$feed_holder.="<br>Event: Status ".$temp["eventRecordStatus"]." --- Type ".$temp["eventType"].".";  
               		if(isset($temp["eventCode"]))							$feed_holder.=" --- Code ".$temp["eventCode"].".";  
               		if(isset($temp["distanceSinceLastValidCoordinate"]))		$feed_holder.=" --- Distance Since Last Location ".$temp["distanceSinceLastValidCoordinate"].".";  
               		                    		
               		$feed_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"]."."; 
               		
               		$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
					
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,    							
     							feed_type,
                                        date_from,
                                        latitude,
                                        longitude,
                                        geotab_user,
                                        is_driver,
                                        version,
                                        status,
                                        origin,
                                        state,
                                        sequence,
                                        malfunction,
                                        event_status,
                                        event_code,
                                        event_type,
                                        last_gps_distance)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
                           			'".sql_friendly(trim($temp["device"]["id"]))."',
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["dateTime"]))."',
                           			".(isset($temp["location"]) ? "'".sql_friendly(trim($temp["location"]["location"]["y"]))."'" : "''").",
                           			".(isset($temp["location"]) ? "'".sql_friendly(trim($temp["location"]["location"]["x"]))."'" : "''").",                           			
                           			".(isset($temp["driver"]) ? "'".sql_friendly(trim($temp["driver"]["id"]))."'" : "''").",
                           			".(isset($temp["driver"]["isDriver"]) ? "'1'" : "'0'").",                            			
                           			'".sql_friendly(trim($temp["version"]))."',
                           			'".sql_friendly(trim($temp["status"]))."',
                           			'".sql_friendly(trim($temp["origin"]))."',
                           			'".sql_friendly(trim($temp["state"]))."',
                           			'".sql_friendly(trim($temp["sequence"]))."',
                           			'".sql_friendly(trim($temp["malfunction"]))."',
                           			'".sql_friendly(trim($temp["eventRecordStatus"]))."',
                           			".(isset($temp["eventCode"]) ? "'".sql_friendly(trim($temp["eventCode"]))."'" : "''" ).",
                           			'".sql_friendly(trim($temp["eventType"]))."',
                           			".(isset($temp["distanceSinceLastValidCoordinate"]) ? "'".sql_friendly(trim($temp["distanceSinceLastValidCoordinate"]))."'" : "''" ).")
     					";
     					simple_query($sql);
     					
     					$sqlu="update defaults set xvalue_string='".trim($temp["dateTime"])."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);
					}
				}
				elseif($def_id==7)
				{                         	
                    	$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["dateTime"]."";   
               		$feed_holder.="<br>ID ".$temp["id"]." --- Version ".$temp["version"]." --- Comment: \"".$temp["comment"]."\".";  
               		if(isset($temp["driver"]))		$feed_holder.="<br>--Driver:  [".$temp["driver"]["id"]."].";
               		
               		if(isset($temp["dutyStatusLog"]))	
               		{
               			$feed_holder.="<br>Duty Status: ID ".$temp["dutyStatusLog"]["id"]."";  
               			if(isset($temp["dutyStatusLog"]["malfunction"]))						$feed_holder.=" --- Malfunction ".$temp["dutyStatusLog"]["malfunction"]."";  
               			if(isset($temp["dutyStatusLog"]["distanceSinceLastValidCoordinate"]))		$feed_holder.=" --- Distance Since Last Location ".$temp["dutyStatusLog"]["distanceSinceLastValidCoordinate"]."";  
               			$feed_holder.=".";  
               		}               		
               		$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
					
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							feed_type,
                                        date_from,
                                        version,
                                        data_txt,
                                        geotab_user,
                                        is_driver,
                                        status,
                                        malfunction,
                                        last_gps_distance)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["dateTime"]))."',
                           			'".sql_friendly(trim($temp["version"]))."',
                           			'".sql_friendly(trim($temp["comment"]))."',
                           			".(isset($temp["driver"]) ? "'".sql_friendly(trim($temp["driver"]["id"]))."'" : "''").",
                           			".(isset($temp["driver"]) ? "1" : "0").",                           			
                           			".(isset($temp["dutyStatusLog"]) ? "'".sql_friendly(trim($temp["dutyStatusLog"]["id"]))."'" : "''").",
                           			".(isset($temp["dutyStatusLog"]) ? "'".sql_friendly(trim($temp["dutyStatusLog"]["malfunction"]))."'" : "''").",
                           			".(isset($temp["dutyStatusLog"]) ? "'".sql_friendly(trim($temp["dutyStatusLog"]["distanceSinceLastValidCoordinate"]))."'" : "''").")
     					";
     					simple_query($sql);     					
     					
     					$sqlu="update defaults set xvalue_string='".trim($temp["dateTime"])."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);
					}
				}
				elseif($def_id==8)
				{
                    	$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["dateTime"]."";   
               		$feed_holder.="<br>Version ".$temp["version"]." --- LogType: \"".$temp["logType"]."\".";  
               		if(isset($temp["driver"]))		$feed_holder.="<br>--Driver:  [".$temp["driver"]["id"]."].";
               		
               		$feed_holder.="<br>Remark: Certify ".$temp["certifyRemark"]." --- Driver \"".$temp["driverRemark"]."\".";  
               		if(isset($temp["repairRemark"]))        $feed_holder.=" --- Repair \"".$temp["repairRemark"]."\".";
                         
               		$mrr_sub_remark="";
                         if(isset($temp["dVIRDefects"]))
                         {
                              if(isset($temp["dVIRDefects"][0]["defectRemarks"]))  
                              {
                                   if(isset($temp["dVIRDefects"][0]["defectRemarks"][0]["remark"]))
                                   {
                                        $mrr_sub_remark=trim($temp["dVIRDefects"][0]["defectRemarks"][0]["remark"]);
                                   }
                              }                              
                         }
               		                    		
               		if(isset($temp["device"]))	$feed_holder.="<br>--Device:".$temp["device"]["id"]."."; 
               		$feed_holder.="<br> ID=".$temp["id"]."."; 
               		
               		$trailer_device="";
               		$trailer_name="";
               		$trailer_id=0;
               		              		
               		if(isset($temp["trailer"]["id"]))	
               		{	              			
               			$trailer_device=trim($temp["trailer"]["id"]);
               			if(isset($temp["trailer"]["name"]))		$trailer_name=trim($temp["trailer"]["name"]); 
               			
               			if($trailer_name=="")
               			{
               				$sqlt="select trailer_id,device_name from ".mrr_find_log_database_name()."geotab_trailer_list where geotab_id='".sql_friendly(trim($trailer_device))."' order by linedate_added desc limit 1";
							$datat=simple_query($sqlt);		
							if($rowt = mysqli_fetch_array($datat)) 
							{
								$trailer_id=$rowt['trailer_id'];
								$trailer_name=trim($rowt['device_name']); 
							}
               			}
               			
               			$feed_holder.="<br>--Trailer Device:".$temp["trailer"]["id"].", Trailer Name=".$trailer_name.".";
               			              			
               			if($trailer_name!="")
               			{
               				$sqlt="select id from trailers where deleted<=0 and (trailer_name like '".sql_friendly($trailer_name)."' or nick_name='".sql_friendly($trailer_name)."')";
							$datat=simple_query($sqlt);		
							if($rowt = mysqli_fetch_array($datat)) 
							{
								$trailer_id=$rowt['id'];
							}	
               			}
               		}
               		             		      		
               		$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
					
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$skippy=0;
     					
     					if(substr_count(strtolower(trim($temp["driverRemark"])),"no defects") > 0)	$skippy=1;
     					if(substr_count(strtolower(trim($temp["driverRemark"])),"all good") > 0)		$skippy=1;
     					if(substr_count(strtolower(trim($temp["driverRemark"])),"all ok") > 0)		$skippy=1;
     					if(substr_count(strtolower(trim($temp["driverRemark"])),"n/a") > 0)			$skippy=1;
                              
                              $use_txt_val=trim($temp["driverRemark"]);
                              if(trim($use_txt_val)=="" && isset($temp["repairRemark"]))         $use_txt_val=trim($temp["repairRemark"]);
                              if(trim($use_txt_val)=="" && isset($temp["certifyRemark"]))        $use_txt_val=trim($temp["certifyRemark"]);
                              
                              $use_txt_val.=" ".trim($mrr_sub_remark);
                              
     					$maint_request_id=0;     					
     					if(trim($use_txt_val)!="" && $skippy==0)
     					{
     						//create maint request form... annotation by driver should have the relavent maint request info in it.
     						$dev_id="";				if(isset($temp["device"]))		$dev_id=trim($temp["device"]["id"]);
     						$maint_request_id=mrr_auto_create_maint_request_from_geotab($dev_id, trim($temp["driver"]["id"]), trim($use_txt_val),$trailer_id,$trailer_name);	
     					}
     					     					     					
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     							feed_type,
                                        date_from,
                                        version,
                                        data_txt,
                                        geotab_user,
                                        is_driver,
                                        data_typer,
                                        data_body,
                                        trailer_id,
                                        trailer_name,
                                        trailer_device,
                                        maint_request_id)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
     							".(isset($temp["device"]) ? "'".sql_friendly(trim($temp["device"]["id"]))."'" : "'0'").",
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["dateTime"]))."',
                           			'".sql_friendly(trim($temp["version"]))."',
                           			'".sql_friendly(trim($temp["certifyRemark"]))."',                           			
                           			".(isset($temp["driver"]) ? "'".sql_friendly(trim($temp["driver"]["id"]))."'" : "''").",
                           			".(isset($temp["driver"]) ? "1" : "0").",
                           			'".sql_friendly(trim($temp["logType"]))."',
                           			'".sql_friendly(trim($use_txt_val))."',                           			
                           			'".sql_friendly($trailer_id)."',
                           			'".sql_friendly(trim($trailer_name))."',
                           			'".sql_friendly(trim($trailer_device))."',
                           			'".sql_friendly($maint_request_id)."')
     					";
     					simple_query($sql);
     					
     					$feed_holder.=" <span style='color:#00cc00;'>...Added Record. {".$sql."}</span>";
     					
     					$sqlu="update defaults set xvalue_string='".trim($temp["dateTime"])."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);     					
					}
					else
					{
						$feed_holder.=" <span style='color:#cc0000;'>...SKIPPED. { Found ".$found_this." for ".trim($temp["id"]).".}</span>";	
					}
					if(trim($mrr_sub_remark)!="")      $feed_holder.=" <br>Driver Remark: <span style='color:purple;'>".$mrr_sub_remark."</span>";
				}
				elseif($def_id==9)
				{
					//ShipmentLog
					$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["dateTime"]." --- From ".$temp["activeFrom"]." to ".$temp["activeTo"].".";   
               		$feed_holder.="<br>Version ".$temp["version"]." --- DocNumber: \"".$temp["documentNumber"]."\".";  
               		if(isset($temp["driver"]))		$feed_holder.="<br>--Driver:  [".$temp["driver"]["id"]."].";
               		
               		$feed_holder.="<br>Shipper Name: ".$temp["shipperName"]." --- Commodity ".$temp["commodity"].".";  
               		                    		
               		$feed_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"]."."; 
               		
               		      		
               		$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
										
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     							feed_type,
                                        date_from,
                                        date_to,
                                        version,
                                        data_txt,
                                        geotab_user,
                                        is_driver,
                                        data_typer,
                                        data_body)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
                           			'".sql_friendly(trim($temp["device"]["id"]))."',
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["activeFrom"]))."',
                           			'".sql_friendly(trim($temp["activeTo"]))."',
                           			'".sql_friendly(trim($temp["version"]))."',
                           			'".sql_friendly(trim($temp["commodity"]))."',                           			
                           			".(isset($temp["driver"]) ? "'".sql_friendly(trim($temp["driver"]["id"]))."'" : "''").",
                           			".(isset($temp["driver"]) ? "1" : "0").",
                           			'".sql_friendly(trim($temp["documentNumber"]))."',
                           			'".sql_friendly(trim($temp["shipperName"]))."')
     					";		//'".sql_friendly(trim($temp["dateTime"]))."',
     					simple_query($sql);
     					
     					$sqlu="update defaults set xvalue_string='".trim($temp["dateTime"])."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);
					}					
				}
				elseif($def_id==10)
				{
					$trailer_geotab_id="";
					$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["activeFrom"]." to  ".$temp["activeTo"]."";   
               		if(isset($temp["trailer"]))	{	$feed_holder.="<br>--Trailer:  [".$temp["trailer"]["id"]."].";			$trailer_geotab_id="".trim($temp["trailer"]["id"])."";		}
               		                   		
               		$feed_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"].". Version ".$temp["version"].""; 
               		
               		      		
               		$found_this=0;
					$sql="select count(*) as cnt from ".mrr_find_log_database_name()."geotab_datafeed_log where geotab_id='".sql_friendly(trim($temp["id"]))."' and feed_type='".sql_friendly($def_id)."'";
					$data=simple_query($sql);		
					if($row = mysqli_fetch_array($data)) 
					{
						$found_this=(int) $row['cnt'];
						//already in the system so don't save it there again...
					}
					
					//if($block_db_feed > 0)		$found_this=1;		//kill switch to block off the database record save.  
					
					if($found_this==0 && trim($temp["id"])!="")
					{	//not found, so update the table.
     					$sql = "
     						insert into ".mrr_find_log_database_name()."geotab_datafeed_log
     							(id,
     							linedate_added,
     							geotab_id,
     							device_id,
     							feed_type,
                                        date_from,
                                        date_to,
                                        version,
                                        data_typer,
                                        data_body)
                           		values
                           			(NULL,
     							NOW(),
     							'".sql_friendly(trim($temp["id"]))."',
                           			'".sql_friendly(trim($temp["device"]["id"]))."',
                           			'".sql_friendly($def_id)."',
                           			'".sql_friendly(trim($temp["activeFrom"]))."',
                           			'".sql_friendly(trim($temp["activeTo"]))."', 
                           			'".sql_friendly(trim($temp["version"]))."',
                           			'Trailer',
                           			'".sql_friendly(trim($trailer_geotab_id))."')
     					";
     					simple_query($sql);
     					
     					$sqlu="update defaults set xvalue_string='".trim($temp["activeFrom"])."' where xname='geotab_last_feed_id_".$def_id."'";
     					simple_query($sqlu);
					}
				}
				elseif($def_id==11)
				{
					
				}
				elseif($def_id==12)
				{
					
				}
				
				
				$feed_holder.="<br><br><hr><br>";
				
				if($show_feed > 0)	
				{
					$feed.=$feed_holder;
					
				}
			}
			
		}
		$feed.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
	}     	
	return $feed;	
}

//Removal function for any entity.
function mrr_kill_geotab_route($remove_type,$geotab_id="")
{    	
	$done="0";
	
	if(trim($remove_type)!="" && trim($geotab_id)!="")
	{     		
		//$fields='&entity={"'.trim($remove_type).'":{"id":"'.trim($geotab_id).'"}}';		
		$fields='&entity={"id":"'.trim($geotab_id).'"}';	
		
     	$fields=str_replace(" ","+",$fields);
     			
		$url='Remove?typeName='.trim($remove_type).''.$fields.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
		
		$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
		if(!is_array($dres) && trim($dres)=="New Session Login")
		{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
			mrr_get_authenticate();
			$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
		}
          $temp_id="";
		if(count($dres) > 1)
		{
			$arr=$dres;
     		if(isset($arr["result"]))
     		{
     			if(!is_array($arr["result"]))
     			{
     				$temp_id=trim($arr["result"]);
     				$done="1";
     			}
     			else
     			{
     				//echo "<br><b>Zone Result 3:</b><br>".var_dump($dres)."<br>URL=".mrr_find_geotab_url_2()."".$url."<br>";
     				$done="0";
     			}
     		}
     		else
     		{
     			//echo "<br><b>Zone Result 2:</b><br>".var_dump($dres)."<br>URL=".mrr_find_geotab_url_2()."".$url."<br>";
     			$done="0";
     		}
		}
		else
		{     			
			$done="1";
		}
		
		
		if(trim($temp_id)!="")		$done=trim($temp_id);
	}
	return $done;
}

//Routes
function mrr_make_geotab_route($truck_id,$route_info,$name="",$notes="",$set_temp=0,$plan_basic=0)
{
	$from_date=''.date("Y-m-d",time()).'';
     $to_date='2050-12-31';
     
     $device_id="";
	$sql = "
		select geotab_device_id
		from trucks
		where id='".sql_friendly($truck_id) ."'
	";		
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$device_id=trim($row['geotab_device_id']);
	}
	
	//$name=trim(str_replace(" ","+",$name));
	//$notes=trim(str_replace(" ","+",$notes));
     
	$make_route=0;
	$stops="";
	for($i=0; $i < count($route_info); $i++)
	{
		if($i > 0)	$stops.=",";
		
		$stops.='{';
		$stops.=	'"activeFrom":"'.$route_info[$i]["from"].'",';
		$stops.=	'"activeTo":"'.$route_info[$i]["to"].'",';
		$stops.=	'"sequence":'.$i.',';
		$stops.=	'"zone":{"id":"'.$route_info[$i]["id"].'"},';
		$stops.=	'"dateTime":"'.$route_info[$i]["date"].'",';
		//$stops.=	'"id":"b14",';
		$stops.=	'"expectedTripDurationToArrival":"00:00:00",';
		$stops.=	'"expectedStopDuration":"00:00:00",';
		$stops.=	'"expectedDistanceToArrival":0';
		$stops.='}';	
		
		$make_route++;		
	}
	$route_id="0";
	     	
	if($make_route > 0)
	{     		
		$fields='&entity={';
		
		$fields.='"device":{"id":"'.$device_id.'"},'; 
		$fields.='"routePlanItemCollection":['.$stops.'],';
		$fields.='"name":"'.trim($name).'",';	
		$fields.='"comment":"'.trim($notes).'",';	
		if($plan_basic==0)	
		{
			$fields.='"routeType":"Plan"';     		//Plan or Basic      	
     	}
     	else
     	{
     		$fields.='"routeType":"Basic"';     		//Plan or Basic  
     	}
     	$fields.='}';		
		
     	$fields=str_replace(" ","+",$fields);
     			
		$url='Add?typeName=Route'.$fields.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
		
		if($set_temp>0)		$_SESSION['geotab_route_url']=mrr_find_geotab_url_2()."".trim($url);
		
		$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
		if(!is_array($dres) && trim($dres)=="New Session Login")
		{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
			mrr_get_authenticate();
			$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
		}
          /*
          
          {
		"method":"Add",
		"params":
		{
			"typeName":"Route",
			"entity":
			{
				"device":{"id":"bB"},
				"routePlanItemCollection":
				[
					{
						"activeFrom":"2018-03-17T13:00:00.000Z",
						"activeTo":"2018-03-17T13:00:00.000Z",
						"sequence":0,
						"zone":{"id":"b234"},
						"dateTime":"2018-01-01",
						"id":"b14",
						"expectedTripDurationToArrival":"00:00:00",
						"expectedStopDuration":"00:00:00",
						"expectedDistanceToArrival":0
					},
					{
						"activeFrom":"2018-03-17T13:04:00.000Z",
						"activeTo":"2018-03-17T13:04:00.000Z",
						"sequence":1,
						"zone":{"id":"b235"},
						"dateTime":"2018-01-01",
						"id":"b15",
						"expectedTripDurationToArrival":"00:04:00",
						"expectedStopDuration":"00:05:00",
						"expectedDistanceToArrival":2.2586
					},
					{
						"activeFrom":"2018-03-17T13:12:00.000Z",
						"activeTo":"2018-03-17T13:12:00.000Z",
						"sequence":2,
						"zone":{"id":"b236"},
						"dateTime":"2018-01-01",
						"id":"b16",
						"expectedTripDurationToArrival":"00:03:00",
						"expectedStopDuration":"00:05:00",
						"expectedDistanceToArrival":2.2817
					},
					{
						"activeFrom":"2018-03-17T13:21:00.000Z",
						"activeTo":"2018-03-17T13:21:00.000Z",
						"sequence":3,
						"zone":{"id":"b237"},
						"dateTime":"2018-01-01",
						"id":"b17",
						"expectedTripDurationToArrival":"00:04:00",
						"expectedStopDuration":"00:05:00",
						"expectedDistanceToArrival":3.2737
					}
				],
				"name":"test",
				"comment":"test",
				"id":null,
				"routeType":"Basic"
			},
			"credentials":{"database":"rafnaztest","sessionId":"XXXXX","userName":"rafaelnazareno@geotab.com"}}
		}               
          */
		     		
		$temp_id="0";
		
		//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br>GeoTab Route Creation Function Output below... Display is for debugging only.<br>URL: ".$url."<br>";
		
		if(count($dres) > 1)
		{
			$arr=$dres;
     		if(isset($arr["result"]))
     		{
     			if(!is_array($arr["result"]))
     			{
     				$temp_id=trim($arr["result"]);
     			}
     			else
     			{
     				//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br><b>Route Result 3:</b><br>".var_dump($dres)."<br>URL=".mrr_find_geotab_url_2()."".$url."<br>";
     			}
     		}
     		else
     		{
     			//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br><b>Route Result 2:</b><br>".var_dump($dres)."<br>URL=".mrr_find_geotab_url_2()."".$url."<br>";
     		}
		}
		else
		{     			
			//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br><b>Route Result 1:</b><br>".var_dump($dres)."<br>URL=".mrr_find_geotab_url_2()."".$url."<br>";
		}
				
		$route_id="0";
		if(trim($temp_id)!="")		$route_id=trim($temp_id);
		
		//if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br>New GeoTab Route ID is ".$temp_id.".<br>";
	}
	return $route_id;
}
function mrr_get_geotab_routes($id="",$name="")
{	//Get the established GeoFencing Routes in the system so far.
	$id=trim(str_replace(" ","+",$id));
	$name=trim(str_replace(" ","+",$name));
	
	//$last_date="2018-01-01";
	//$to_date="2018-12-31";
	$last_date=date("Y-m-d",strtotime("-72 hours",time()));
	$to_date=date("Y-m-d",strtotime("+72 hours",time()));
	
	$searching="";		//
	
	$searching.='&search={"fromDate":"'.$last_date.'T00:00:00.000Z","toDate":"'.$to_date.'T23:59:59.999Z"'.($id!="" ? ',"id":"'.$id.'"' : '').''.($name!="" ? ',"name":"%'.$name.'%"' : '').'}';
	
	$url='Get?typeName=Route'.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
					
	$routes="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	if(!is_array($dres) && trim($dres)=="New Session Login")
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/'.$url.'<br>';
	
	//echo "<br>Get (Routes) Result: ".var_dump($dres)."<br>";	
			
	$routes="<br>Result Count=".count($dres)."<br>";	
	
	/*
	array(2) 
	{ 
		["result"]=> array(1) 
		{ 
			[0]=> array(6) 
			{ 
				["comment"]=> string(39) "Using test stops as zones....making the" 
				["id"]=> string(2) "b1" 
				["name"]=> string(20) "This is a test route" 
				["routePlanItemCollection"]=> array(4) 
				{ 
					[0]=> array(9) 
					{ 
						["activeFrom"]=> string(24) "2018-03-19T13:00:00.000Z" 
						["activeTo"]=> string(24) "2018-03-19T14:00:00.000Z" 
						["expectedDistanceToArrival"]=> float(0) 
						["expectedStopDuration"]=> string(8) "00:00:00" 
						["expectedTripDurationToArrival"]=> string(8) "00:00:00" 
						["sequence"]=> int(0) 
						["zone"]=> array(1) { ["id"]=> string(5) "b1A86" } 
						["dateTime"]=> string(24) "2018-03-19T13:30:00.000Z" 
						["id"]=> string(2) "b1" 
					} 
					[1]=> array(9) 
					{ 
						["activeFrom"]=> string(24) "2018-03-19T15:30:00.000Z" 
						["activeTo"]=> string(24) "2018-03-19T16:30:00.000Z" 
						["expectedDistanceToArrival"]=> float(0) 
						["expectedStopDuration"]=> string(8) "00:00:00" 
						["expectedTripDurationToArrival"]=> string(8) "00:00:00" 
						["sequence"]=> int(0) 
						["zone"]=> array(1) { ["id"]=> string(5) "b1A87" } 
						["dateTime"]=> string(24) "2018-03-19T16:00:00.000Z" 
						["id"]=> string(2) "b2" 
					} 
					[2]=> array(9) 
					{ 
						["activeFrom"]=> string(24) "2018-03-20T10:00:00.000Z" 
						["activeTo"]=> string(24) "2018-03-20T12:00:00.000Z" 
						["expectedDistanceToArrival"]=> float(0) 
						["expectedStopDuration"]=> string(8) "00:00:00" 
						["expectedTripDurationToArrival"]=> string(8) "00:00:00" 
						["sequence"]=> int(0) 
						["zone"]=> array(1) { ["id"]=> string(5) "b1A88" } 
						["dateTime"]=> string(24) "2018-03-20T11:00:00.000Z" 
						["id"]=> string(2) "b3" 
					} 
					[3]=> array(9) 
					{ 
						["activeFrom"]=> string(24) "2018-03-21T08:30:00.000Z" 
						["activeTo"]=> string(24) "2018-03-21T09:30:00.000Z" 
						["expectedDistanceToArrival"]=> float(0) 
						["expectedStopDuration"]=> string(8) "00:00:00" 
						["expectedTripDurationToArrival"]=> string(8) "00:00:00" 
						["sequence"]=> int(0) 
						["zone"]=> array(1) { ["id"]=> string(5) "b1A89" } 
						["dateTime"]=> string(24) "2018-03-21T09:00:00.000Z" 
						["id"]=> string(2) "b4" 
					} 
				} 
				["routeType"]=> string(4) "Plan" 
				["device"]=> array(1) { ["id"]=> string(2) "b1" } 
			} 
		} 
		["jsonrpc"]=> string(3) "2.0" 
	} 
	*/

	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$routes.="<br><b>Route 1 Result. Total Routes Found=".count($arr["result"]).".</b>";	
			
			$routes_found=0;
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				$temp=$arr["result"][$j];
				
				$show_routes=1;
				$routes_holder="";
				    				    				
				$routes_holder.="<br>Route ".$j.". --- ID: ".$temp["id"]."";  // Active From ".$temp[""]." To ".$temp[""].".
				$routes_holder.="<br>Name: ".$temp["name"].".";
				$routes_holder.="<br>Comment: ".$temp["comment"].".";
				$routes_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"].".";  
				
				//$routes_holder.="<br>Displayed: ".($temp["displayed"]==true ? "YES" : "no").".";  	  
				
				$routes_holder.="<br>RouteType: ".$temp["routeType"].".";     				
				
				//$routes_holder.="<br>MustIdentifyStops: ".($temp["mustIdentifyStops"]==true ? "YES" : "no").".";   
								
				for($z=0; $z < count($temp["routePlanItemCollection"]); $z++)
				{
					$routes_holder.="<br>*** Point ".$z.". 
						ID ".$temp["routePlanItemCollection"][$z]["id"]." -- Date: ".$temp["routePlanItemCollection"][$z]["dateTime"]." 
						".$temp["routePlanItemCollection"][$z]["sequence"].".
						From ".$temp["routePlanItemCollection"][$z]["activeFrom"]." To ".$temp["routePlanItemCollection"][$z]["activeTo"]."
						Distance: ".$temp["routePlanItemCollection"][$z]["expectedDistanceToArrival"]."
						Duration: ".$temp["routePlanItemCollection"][$z]["expectedStopDuration"]."
						TripDuration: ".$temp["routePlanItemCollection"][$z]["expectedTripDurationToArrival"]."
						Route: ".$temp["routePlanItemCollection"][$z]["route"]."
						ZONE: ".$temp["routePlanItemCollection"][$z]["zone"]['id']."

					";
				}
				     				
				$routes_holder.="<br><br><hr><br>";
				     				
				if($show_routes > 0)	
				{
					$routes.=$routes_holder;
					$routes_found++;
				}
			}
			
			if($routes_found==0)		$routes.=" <span style='color:purple;'><b>NO ROUTE FOUND.</b></span>";	
		}
		$routes.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
	}     	
	return $routes;		
}


//ZONES
function mrr_make_geotab_zone($long,$lat,$name,$info,$notes,$no_library=0,$long_w="",$long_e="",$lat_n="",$lat_s="")
{
	if($no_library==0)
	{
		$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
		$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;
		
		$res=mrr_gps_point_box_creator($long,$lat,$x_off,$y_off);
		
		$lat_n="".$res['pt0_lat_n']."";			//North
		$lat_s="".$res['pt1_lat_s']."";			//South
		$long_e="".$res['pt1_long_e']."";			//East
		$long_w="".$res['pt0_long_w']."";			//West
	}
	
	$from_date=''.date("Y-m-d",time()).'';
     $to_date='2050-12-31';
			
	$zone_id="0";
	$make_zone=1;
	
	if($make_zone > 0)
	{       
     	$fields='&entity={';          	
     	
     	$fields.='"activeFrom":"'.$from_date.'T00:00:00.000Z",';	
     	$fields.='"activeTo":"'.$to_date.'T00:00:00.000Z",';		
     	$fields.='"comment":"'.trim($notes).'",';	
     	$fields.='"displayed":true,';
     	$fields.='"externalReference":"'.trim($info).'",';
     	$fields.='"mustIdentifyStops":true,';
     	$fields.='"name":"'.trim($name).'",';	
     	
     	$fields.='"groups":[{"id":"GroupCompanyId","children":[{}],"comments":"","reference":""}],';
     	
     	$fields.='"points":[{"x":"'.$long_w.'","y":"'.$lat_n.'"},{"x":"'.$long_e.'","y":"'.$lat_n.'"},{"x":"'.$long_e.'","y":"'.$lat_s.'"},{"x":"'.$long_w.'","y":"'.$lat_s.'"},{"x":"'.$long_w.'","y":"'.$lat_n.'"}]';		
     	          	
     	$fields.='}';
     	
     	$fields=str_replace("&","and",$fields);
     	$fields=str_replace("andentity={","&entity={",$fields);
     	$fields=str_replace(" ","+",$fields);
     			
		$url='Add?typeName=Zone'.$fields.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
		
		
		//echo "<br><b>URL:</b><br>".$url."<br>";
		
		
		$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
		if(!is_array($dres) && trim($dres)=="New Session Login")
		{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
			mrr_get_authenticate();
			$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
		}
		     		
		$temp_id="0";
		
		if(count($dres) > 1)
		{
			$arr=$dres;
     		if(isset($arr["result"]))
     		{
     			if(!is_array($arr["result"]))
     			{
     				$temp_id=trim($arr["result"]);
     				
     				$res['id']=0;
                    	$res['geotab_id_name']=$temp_id;
                    	$res['address_1']="";
                    	$res['city']="";
                    	$res['state']="";
                    	$res['zip']="";
                    	$res['long']=$long;
                    	$res['lat']=$lat;
                    	$res['long_zone_w']=$long_w;
                    	$res['long_zone_e']=$long_e;
                    	$res['lat_zone_n']=$lat_n;
                    	$res['lat_zone_s']=$lat_s;
                    	
                    	$res['conard_name']=$name;
     				
     				if($no_library==0)	mrr_create_geotab_stop_zones($res);
     			}
     			else
     			{
     				//echo "<br><b>Zone Result 1:</b><br>".var_dump($dres)."<br>";
     			}
     		}
     		else
     		{
     			//echo "<br><b>Zone Result 2:</b><br>".var_dump($dres)."<br>";
     		}
		}
		else
		{     			
			//echo "<br><b>Zone Result 3:</b><br>".var_dump($dres)."<br>";
		}
		
		$zone_id="0";
		if(trim($temp_id)!="")		$zone_id=trim($temp_id);
	}
	return $zone_id;
}
function mrr_get_geotab_zones($id="",$name="",$alt_return=0)
{	//Get the established GeoFencing Zones in the system so far.
	$id=trim(str_replace(" ","+",$id));
	$name=trim(str_replace(" ","+",$name));
	
	$searching="";
	if($id!="" || $name!="")
	{
		if($id!="" && $name!="")	{	$searching='&search={"id":"'.$id.'","name":"%'.$name.'%"}';		}
		elseif($id!="")		{	$searching='&search={"id":"'.$id.'"}';						}
		elseif($name!="")		{	$searching='&search={"name":"%'.$name.'%"}';					}
	}
	
	$url='Get?typeName=Zone'.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
					
	$zones="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	if(!is_array($dres) && trim($dres)=="New Session Login")
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/'.$url.'<br>';
	
	//echo "<br>Get (Zones) Result: ".var_dump($dres)."<br>";	
			
	$zones="<br>Result Count=".count($dres)."<br>";	
	
	$tmp_zone_id="";
	
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$zones.="<br><b>Zone 1 Result. Total Zones Found=".count($arr["result"]).".</b>";	
			
			$zones_found=0;
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				$temp=$arr["result"][$j];
				
				$show_zones=1;
				$zones_holder="";
				    				    				
				$zones_holder.="<br>Zone ".$j.".--- ID: ".$temp["id"].". Active From ".$temp["activeFrom"]." To ".$temp["activeTo"].".";  // 
				$zones_holder.="<br>Name: ".$temp["name"].".";
				$zones_holder.="<br>Ref: ".$temp["externalReference"].".";
				
				$zones_holder.="<br>Displayed: ".($temp["displayed"]==true ? "YES" : "no").".";  	  
				
				$zones_holder.="<br>Comment: ".$temp["comment"].".";     				
				
				$zones_holder.="<br>MustIdentifyStops: ".($temp["mustIdentifyStops"]==true ? "YES" : "no").".";   
				
				$long_e="";
				$long_w="";
				$lat_n="";
				$lat_s="";			
				 
				$x_start=0;		$x_offset=0;
				$y_start=0;		$y_offset=0;
				 				
				for($z=0; $z < count($temp["points"]); $z++)
				{
					if($z==0)
					{
						$x_start=$temp["points"][$z]["x"];
						$y_start=$temp["points"][$z]["y"];	
						
						//$long_w="".$x_start."";
						//$lat_n="".$y_start."";
					}
					else
					{
						$x_offset=( $x_start - $temp["points"][$z]["x"] );
						$y_offset=( $y_start - $temp["points"][$z]["y"] );
						
						$x_start=$temp["points"][$z]["x"];
						$y_start=$temp["points"][$z]["y"];	
					}
					
					if(count($temp["points"])==5 && $z==0)	{	$long_w="".$x_start."";	$lat_n="".$y_start."";	}
					if(count($temp["points"])==5 && $z==2)	{	$long_e="".$x_start."";	$lat_s="".$y_start."";	}
					
					
					$zones_holder.="<br>*** Point ".$z.". [ID ".$temp["points"][$z]["x"]." , ".$temp["points"][$z]["y"]."]  OFFSETS [ ".$x_offset." | ".$y_offset." ].";
				}
				     				
				$zones_holder.="<br><br><hr><br>";
				
				if(count($temp["points"])==5)
				{	//this is a box... points 1 and 5 (0 and 4 in hte array) are the same point...or should be... to close the box.
     				//$res['id']=0;
                         $res['geotab_id_name']=trim($temp["id"]);
                         //$res['conard_name']="";
                         //$res['address_1']="";
                         //$res['city']="";
                         //$res['state']="";
                         //$res['zip']="";
                         //$res['long']="";
                         //$res['lat']="";
                         $res['long_zone_w']=$long_w;
                         $res['long_zone_e']=$long_e;
                         $res['lat_zone_n']=$lat_n;
                         $res['lat_zone_s']=$lat_s;
                         mrr_update_geotab_stop_zones_points_name($res);
				}
				$tmp_zone_id=trim($temp["id"]);
				     				
				if($show_zones > 0)	
				{
					$zones.=$zones_holder;
					$zones_found++;
				}
			}
			
			if($zones_found==0)		$zones.=" <span style='color:purple;'><b>NO ZONES FOUND.</b></span>";	
		}
		$zones.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
	}     	
	
	if($alt_return > 0)		return $tmp_zone_id;
	return $zones;	
}	
function mrr_set_geotab_zones_displayed($geotab_id,$displayed=0)
{	//updates the shipping log entry... but only the date
	global $defaultsarray;
		
	$geotab_id=trim($geotab_id);
	
	if(trim($geotab_id)!="")
	{    
     	$fields='&entity={';
     	
     	$fields.='"id":"'.trim($geotab_id).'",';
     	     	
     	$fields.='"displayed":'.($displayed > 0 ? "true" : "false").'';	
     	
     	$fields.='}';
     	
     	$fields=str_replace(" ","+",$fields);
     	     			
		$url='Set?typeName=Zone'.$fields.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
		
		$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
		if(!is_array($dres) && trim($dres)=="New Session Login")
		{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
			mrr_get_authenticate();
			$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
		}
		
		$temp_id="0";
		
		//echo "<br><b>Shipment Log SENT Result:</b><br>URL: ".$url."<br><br>".trim($dres)."<br>";     		
		
		if(count($dres) > 1)
		{
			$arr=$dres;
			echo "<br><b>(1) Zone Display Result:</b><br>".var_dump($dres)."<br>";
     		//if(isset($arr["result"]))
     		//{
     			//if(!is_array($arr["result"]))
     			//{
     				//$temp_id=trim($arr["result"]);
     				
					
     			//}
     		//}
		}
		else
		{     			
			echo "<br><b>(2) Zone Display Result:</b><br>".var_dump($dres)."<br>";
		}
	}
}


function mrr_gps_point_box_creator($x,$y,$x_off=0,$y_off=0)
{	//Takes a single GPS point, and maps out a generic box (5 points where the first and the last are the same)
	//Center of box is $x,$y where X=Longitude (-) and Y=Latitude (+) forthe USA. 
	
	//Longitude offset of 0.002 = 0.22 miles East/West from West to East....0.11 to be even with center point or "half way".
	//Latitude offset of 0.002 = 0.28 miles North/South from North to South... 0.14 to be even with center point or "half way".
			
	$res['center']="[ ".$y." , ".$x." ]<br>Latitude , Longitude";
	
	$east=$x + $x_off;
	$west=$x - $x_off;
	
	$north=$y + $y_off;
	$south=$y - $y_off;
			
	$res['pt0']="[ ".$north." , ".$west." ]";		//NW
	$res['pt1']="[ ".$north." , ".$east." ]";		//NE
	$res['pt2']="[ ".$south." , ".$east." ]";		//SE
	$res['pt3']="[ ".$south." , ".$west." ]";		//SW
	$res['pt4']="[ ".$north." , ".$west." ]";		//NW
	
	$res['pt0_lat_n']="".$north."";		//N
	$res['pt1_lat_s']="".$south."";		//S
	
	$res['pt0_long_w']="".$west."";		//W
	$res['pt1_long_e']="".$east."";		//E
	
	$tab="
		<center>
		<div style='border:2px #CC0000 solid; width:800px;'>
		<table border='0' cellpadding='0' cellspacing='0' width='750'>
		<tr height='150'>	<td valign='top'>".$res['pt0']."</td>  	<td valign='top'>&nbsp;</td>  <td valign='top'>&nbsp;</td>  						<td valign='top'>&nbsp;</td>  <td valign='top' align='right'>".$res['pt1']."</td> 	</tr>
		<tr height='150'>	<td valign='top'>&nbsp;</td>  		<td valign='top'>&nbsp;</td>  <td valign='top'>&nbsp;</td>  						<td valign='top'>&nbsp;</td>  <td valign='top' align='right'>&nbsp;</td> 			</tr>
		<tr height='150'>	<td valign='top'>&nbsp;</td>  		<td valign='top'>&nbsp;</td>  <td valign='top' align='center'>".$res['center']."</td> 	<td valign='top'>&nbsp;</td>  <td valign='top' align='right'>&nbsp;</td> 			</tr>
		<tr height='150'>	<td valign='top'>&nbsp;</td>  		<td valign='top'>&nbsp;</td>  <td valign='top'>&nbsp;</td>  						<td valign='top'>&nbsp;</td>  <td valign='top' align='right'>&nbsp;</td> 			</tr>
		<tr height='150'>	<td valign='bottom'>".$res['pt3']."</td><td valign='top'>&nbsp;</td>  <td valign='top'>&nbsp;</td>  						<td valign='top'>&nbsp;</td>  <td valign='bottom' align='right'>".$res['pt2']."</td> </tr>
		</table>		
		</div>
		</center>
	";
	$res['graphic']=$tab;
	
	return $res;
}
function mrr_gps_point_inside_bounds($long,$lat,$n,$s,$e,$w)
{
	$in_bounds=0;
	
	if($long!=0 && $lat!=0)
	{	
		//echo "<br>HERE IS VALUE SET: Long=".$long.", Lat=".$lat.", N=".$n.", S=".$s.", E=".$e.", W=".$w.".<br>";
		
		if((floatval($long) >= floatval($s) && floatval($long) <= floatval($n)) && (floatval($lat) >= floatval($w) && floatval($lat) <= floatval($e)))	$in_bounds=1;
		
		//echo "<br>HERE IS VALUE SET: S= ".floatval($s)." --> Long= ".floatval($long)." --->  N= ".floatval($n)." |   W= ".floatval($w)." ---> Lat= ".floatval($lat)." ---> E= ".floatval($e).".  ===".$in_bounds.".<br>";		
	}
	return $in_bounds;
}


//Shipment Logs entries creator and editor
function mrr_send_geotab_shipment_log($truck_id,$driver_id,$body,$typer,$txt,$geotab_id="")
{
	global $defaultsarray;
	
	//$user_name="b11";						//MRR
	//if($from==1)		$user_name="b7";		//Dale	(logistics)
	//if($from==2)		$user_name="b5";		//Dale	(transportation)
	//if($from==3)		$user_name="b8";		//James
	
	$driver_name="";
	$driver_user="";
	$device_id="";
	
	$body=str_replace('"',"'",$body);     		$body=str_replace('"',"'",$body);
	$typer=str_replace('"',"'",$typer);     	$typer=str_replace('"',"'",$typer);
	$txt=str_replace('"',"'",$txt);     		$txt=str_replace('"',"'",$txt);
	
	$sql = "
		select geotab_device_id
		from trucks
		where id='".sql_friendly($truck_id) ."'
	";		
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$device_id=trim($row['geotab_device_id']);
	}
		
	if($driver_id > 0)
	{
		//switch from the truck to the driver...or at least prefix the name of the driver to the stop message			
		$sql = "
     		select name_driver_first,payroll_first_name,geotab_use_id
     		from drivers
     		where id='".sql_friendly($driver_id) ."'
     	";		
     	$data=simple_query($sql);
     	if($row=mysqli_fetch_array($data))
     	{
     		$driver_name=trim($row['name_driver_first']).", ";
     		if(trim($row['payroll_first_name'])!="")		$driver_name=trim($row['payroll_first_name']).", ";
     		
     		$driver_user=trim($row['geotab_use_id'])."";
     	}
	}
		
	$sl_id="0";	
	if(trim($geotab_id)!="")		$sl_id=trim($geotab_id);
	
	if(trim($device_id)!="")
	{         	
     	//$add_date=''.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z';
     	//$from_date=''.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z';
     	//$to_date=''.date("Y-m-d",strtotime("+1 year",time())).'T00:00:00.000Z';
     	
     	$fields='&entity={';
     	//$fields.='"user":{"id":"'.$user_name.'"},';				//  "id":"b4" 
     	$fields.='"driver":{"id":"'.$driver_user.'"},';				//  "id":"b4" 
     	
     	$fields.='"dateTime":"'.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z",';		//'.$from_date.'
     	$fields.='"activeFrom":"'.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z",';		//'.$from_date.'
     	$fields.='"activeTo":"2050-01-01T00:00:00.000Z",';		//'.$to_date.'
     	
     	$fields.='"shipperName":"'.$body.'",';	
     	$fields.='"documentNumber":"'.$typer.'",';	
     	$fields.='"commodity":"'.$txt.'",';	     	
     	
     	$fields.='"device":{"id":"'.$device_id.'"}';
     	
     	$fields.='}';
     	
     	$fields=str_replace(" ","+",$fields);
     			
		$url='Add?typeName=ShipmentLog'.$fields.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
		
		$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
		if(!is_array($dres) && trim($dres)=="New Session Login")
		{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
			mrr_get_authenticate();
			$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
		}
		
		$temp_id="0";
		
		//echo "<br><b>Shipment Log SENT Result:</b><br>URL: ".$url."<br><br>".trim($dres)."<br>";     		
		
		if(count($dres) > 1)
		{
			$arr=$dres;
     		if(isset($arr["result"]))
     		{
     			if(!is_array($arr["result"]))
     			{
     				$temp_id=trim($arr["result"]);
     				/*     				
     				$sql = "
						insert into ".mrr_find_log_database_name()."geotab_messages_sent
							(id,
							linedate_added,
							truck_id,
							user_id,
							geotab_id,
							device_id,
							urgent_flag,							
							message_sent,
							archived,
							actual_user_id)
						values
							(NULL,
							NOW(),
							'".sql_friendly($truck_id)."',
							'".sql_friendly($from)."',
							'".sql_friendly($temp_id)."',
							'".sql_friendly($device_id)."',
							'".sql_friendly($urgent)."',
							'".sql_friendly($message)."',
							0,
							'".sql_friendly($sess_user_id)."')
					";
					simple_query($sql);
					*/
     			}
     		}
		}
		else
		{     			
			//echo "<br><b>Shipment Log Result:</b><br>".var_dump($dres)."<br>";
		}
		
		$sl_id="0";
		if(trim($temp_id)!="")		$sl_id=trim($temp_id);
	}
	else
	{
		$sl_id="-1";			//no device set for this truck...skip this.
	}
	return $sl_id;
}
function mrr_set_geotab_shipment_log($geotab_id,$enddate="")
{	//updates the shipping log entry... but only the date
	global $defaultsarray;
		
	if(trim($enddate)=="")		$enddate=''.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z';
	
	$geotab_id=trim($geotab_id);
	
	if(trim($geotab_id)!="")
	{         	
     	//$add_date=''.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z';
     	//$from_date=''.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z';
     	//$to_date=''.date("Y-m-d",strtotime("+1 year",time())).'T00:00:00.000Z';
     	
     	$fields='&entity={';
     	
     	$fields.='"id":"'.trim($geotab_id).'",';
     	
     	//$fields.='"driver":{"id":"'.$driver_user.'"},';				//  "id":"b4" 
     	
     	//$fields.='"dateTime":"'.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z",';		//'.$from_date.'
     	//$fields.='"activeFrom":"'.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z",';		//'.$from_date.'
     	     	
     	//$fields.='"shipperName":"'.$body.'",';	
     	//$fields.='"documentNumber":"'.$typer.'",';	
     	//$fields.='"commodity":"'.$txt.'",';	     	
     	
     	//$fields.='"device":{"id":"'.$device_id.'"}';
     	     	
     	$fields.='"activeTo":"'.date("Y-m-d",time()).'T'.date("H:i:s",time()).'.000Z"';	
     	
     	$fields.='}';
     	
     	$fields=str_replace(" ","+",$fields);
     	
     				//ShipmentLog
					//$feed_holder.="<br>".trim($feed_type)." ".$j.". --- ".$temp["dateTime"]." --- From ".$temp["activeFrom"]." to ".$temp["activeTo"].".";   
               		//$feed_holder.="<br>Version ".$temp["version"]." --- DocNumber: \"".$temp["documentNumber"]."\".";  
               		//if(isset($temp["driver"]))		$feed_holder.="<br>--Driver:  [".$temp["driver"]["id"]."].";
               		
               		//$feed_holder.="<br>Shipper Name: ".$temp["shipperName"]." --- Commodity ".$temp["commodity"].".";  
               		                    		
               		//$feed_holder.="<br>--Device:".$temp["device"]["id"].", ID=".$temp["id"]."."; 
     			
		$url='Set?typeName=ShipmentLog'.$fields.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
		
		$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
		if(!is_array($dres) && trim($dres)=="New Session Login")
		{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
			mrr_get_authenticate();
			$dres=mrr_geotab_get_file_contents($url,2,0,0,1);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
		}
		
		$temp_id="0";
		
		//echo "<br><b>Shipment Log SENT Result:</b><br>URL: ".$url."<br><br>".trim($dres)."<br>";     		
		
		if(count($dres) > 1)
		{
			$arr=$dres;
     		if(isset($arr["result"]))
     		{
     			if(!is_array($arr["result"]))
     			{
     				$temp_id=trim($arr["result"]);
     				/*     				
     				$sql = "
						insert into ".mrr_find_log_database_name()."geotab_messages_sent
							(id,
							linedate_added,
							truck_id,
							user_id,
							geotab_id,
							device_id,
							urgent_flag,							
							message_sent,
							archived,
							actual_user_id)
						values
							(NULL,
							NOW(),
							'".sql_friendly($truck_id)."',
							'".sql_friendly($from)."',
							'".sql_friendly($temp_id)."',
							'".sql_friendly($device_id)."',
							'".sql_friendly($urgent)."',
							'".sql_friendly($message)."',
							0,
							'".sql_friendly($sess_user_id)."')
					";
					simple_query($sql);
					*/
     			}
     		}
		}
		else
		{     			
			//echo "<br><b>Shipment Log Result:</b><br>".var_dump($dres)."<br>";
		}
	}
}

//RULES
function mrr_get_geotab_rules($id="",$name="")
{	//Get the established GeoFencing Zones in the system so far.
	$id=trim(str_replace(" ","+",$id));
	$name=trim(str_replace(" ","+",$name));
	
	$searching="";
	if($id!="")		$searching='&search={"id":"'.$id.'"}';
	elseif($name!="")	$searching='&search={"name":"%'.$name.'%"}';
	else				$searching='&search={"baseType":"Custom"}';
			
	$url='Get?typeName=Rule'.$searching.'&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
					
	$rules="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	if(!is_array($dres) && trim($dres)=="New Session Login")
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/'.$url.'';
	
	//echo "<br>Get (Rules) Result: ".var_dump($dres)."<br>";	
			
	$rules="<br>Result Count=".count($dres)."<br>";	
	
	/*
	{ 
		["result"]=> array(1) 
		{ 
			[0]=> array(10) 
			{ 
				["activeFrom"]=> string(10) "1986-01-01" 
				["activeTo"]=> string(10) "2050-01-01" 
				["baseType"]=> string(6) "Custom" 
				["color"]=> array(4) 
				{ 
					["a"]=> int(255) 
					["b"]=> int(0) 
					["g"]=> int(0) 
					["r"]=> int(255) 
				} 
				["comment"]=> string(0) "" 
				["condition"]=> array(3) 
				{ 
					["conditionType"]=> string(5) "Fault" 
					["sequence"]=> string(16) "0000000000000000" 
					["id"]=> string(23) "arCX0YhdFYEargWLZajJ0XA" 
				} 
				["id"]=> string(23) "aZp8Xj-72B0KG6VciYaCGjg" 
				["name"]=> string(22) "Engine Fault Exception" 
				["groups"]=> array(1) 
				{ 
					[0]=> array(5) 
					{ 
						["color"]=> array(4) 
						{ 
								["a"]=> int(255) 
								["b"]=> int(0) 
								["g"]=> int(0) 
								["r"]=> int(0) 
						} 
						["id"]=> string(14) "GroupCompanyId" 
						["children"]=> array(0) { } 
						["comments"]=> string(0) "" 
						["reference"]=> string(0) "" 
					} 
				} 
				["version"]=> string(16) "000000000000000c" 
			} 
		} 
		["jsonrpc"]=> string(3) "2.0" 
	} 
	*/
					
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$rules.="<br><b>Rule 1 Result. Total Rules Found=".count($arr["result"]).".</b>";	
			
			$rules_found=0;
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				$temp=$arr["result"][$j];
				
				$show_rules=1;
				$rules_holder="";
				    				    				
				$rules_holder.="<br>Rule ".$j.". Active From ".$temp["activeFrom"]." To ".$temp["activeTo"].".";
				$rules_holder.="<br>Name: ".$temp["name"].".";
				$rules_holder.="<br>ID: ".$temp["id"].". BaseType: ".$temp["baseType"].".";
				$rules_holder.="<br>Comment: ".$temp["comment"].".";
				$rules_holder.="<br>Version: ".$temp["version"].".";     				
				
				$rules_holder.="<br>Condition: ID=".$temp["condition"]["id"].", Type=".$temp["condition"]["conditionType"].", Sequence=".$temp["condition"]["sequence"].".";   
				 
				for($z=0; $z < count($temp["groups"]); $z++)
				{
					$rules_holder.="
						<br>*** Group ".$z.". 
						ID ".$temp["groups"][$z]["id"].", 
						Comments ".$temp["groups"][$z]["comments"].", 
						Reference ".$temp["groups"][$z]["reference"].".
					";
				}
				
				
				$rules_holder.="<br><br><hr><br>";
				     				
				if($show_rules > 0)	
				{
					$rules.=$rules_holder;
					$rules_found++;
				}
			}
			
			if($rules_found==0)		$rules.=" <span style='color:purple;'><b>NO Rules FOUND.</b></span>";	
		}
		$rules.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
	}     	
	return $rules;	
}

function mrr_geotab_reverse_geocode_address_from_point($long,$lat)
{
	global $defaultsarray;
	//ReverseGeocodeAddress (GetAddresses) to get address from GPS coordinate
	$res['address_1']="";
	$res['address_2']="";
	$res['city']="";
	$res['state']="";
	$res['zip']="";
	
	if(trim($long)=="" || trim($lat)=="")		return $res;
			
	$url='GetAddresses?coordinates=[{"x":"'.$long.'","y":"'.$lat.'"}]&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
					
	$addr="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	if(!is_array($dres) && trim($dres)=="New Session Login")
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/'.$url.'';	
	//echo "<br>Get (GetAddresses) Result: ".var_dump($dres)."<br>";	
			
	$addr="<br>Result Count=".count($dres)."<br>";	
	
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$addr.="<br><b>GetAddresses Result. Total Addresses Found=".count($arr["result"]).".</b>";	
			
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				/*
                         $temp["country"]			"UnitedStates"
                         $temp["formattedAddress"]	"333-353 Waldron Rd, La Vergne, TN 37086, USA"
                         $temp["streetName"]			"Waldron Rd"
                         $temp["streetNumber"] 		"333-353"                              
				*/
				
				$res['address_1']="";
				$res['address_2']="";
				$res['city']="";
				$res['state']="";
				$res['zip']="";
				
				$temp=$arr["result"][$j];
				
				if(isset($temp["street"]))		$res['address_1']="".trim($temp["street"])."";
				//if(isset($temp["postalCode"]))	$res['address_2']="".trim($temp[""])."";
				if(isset($temp["city"]))			$res['city']="".trim($temp["city"])."";
				if(isset($temp["region"]))		$res['state']="".trim($temp["region"])."";				
				if(isset($temp["postalCode"]))	$res['zip']="".trim($temp["postalCode"])."";
				
				$xres=mrr_find_geotab_stop_zones_by_gps($long,$lat);
				if($xres['id']==0)
				{
					$res['long']=$long;
					$res['lat']=$lat;
					
					$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
					$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;			
					
					$gres=mrr_gps_point_box_creator($long,$lat,$x_off,$y_off);
										
					$res['long_zone_w']="".$gres['pt0_long_w']."";
					$res['long_zone_e']="".$gres['pt1_long_e']."";
					$res['lat_zone_n']="".$gres['pt0_lat_n']."";
					$res['lat_zone_s']="".$gres['pt1_lat_s']."";	
					
					$res['geotab_id_name']="";
					$res['conard_name']="";
					
					mrr_create_geotab_stop_zones($res);
				}
				
			}
			
			$addr.=" <span style='color:purple;'><b>NO Address FOUND.</b></span>";	
		}
		$addr.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
		
		//echo $addr;
	} 
			
	return $res;
}

function mrr_geotab_get_coordinate_from_addr($address,$city,$state,$zip)
{
	global $defaultsarray;
	
	//GetCoordinates to get long and lat of given address.
	$res['long']="";		//Ex: -86.59220123291016
	$res['lat']="";		//Ex:  36.02058029
	
	if(trim($address)=="" || trim($city)=="" || trim($state)=="")		return $res;
	
	$addr_string=''.$address.', '.$city.', '.$state.' '.$zip.', USA';
	$addr_string=str_replace(" ","+",$addr_string);
				
	$url='GetCoordinates?addresses=["'.$addr_string.'"]&credentials={"database":"'.mrr_find_geotab_database().'","userName":"'.mrr_find_geotab_username().'","sessionId":"'.mrr_find_geotab_session_id().'"}';
					
	$geocode="";
	$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata
	if(!is_array($dres) && trim($dres)=="New Session Login")
	{	//Had to re-initiate the session ID by logging in again.  Repeat operation.
		mrr_get_authenticate();
		$dres=mrr_geotab_get_file_contents($url,2,0,0);	//URL,URL-Mode,GET/POST,encodeURL,$postdata	
	}
	
	//echo '<br>https://my241.geotab.com/apiv1/'.$url.'';
	
	//echo "<br>Get (GeoCode) Result: ".var_dump($dres)."<br>";	
			
	$geocode="<br>Result Count=".count($dres)."<br>";	
	
	if(count($dres)==2)
	{
		$arr=$dres;
		if(isset($arr["result"]))
		{
			$geocode.="<br><b>GetCoordinates Result. Total GPS Points Found=".count($arr["result"]).".</b>";	
			
			for($j=0; $j < count($arr["result"]); $j++)	
			{
				$temp=$arr["result"][$j];
				
				$res['long']="".trim($temp["x"])."";
				$res['lat']="".trim($temp["y"])."";  
				
				$long=$res['long'];
				$lat=$res['lat'];
				
				$xres=mrr_find_geotab_stop_zones_by_gps($long,$lat);
				if($xres['id']==0)
				{
					$res['address_1']=$address;
					$res['city']=$city;
					$res['state']=$state;
					$res['zip']=$zip;
					
					$x_off=floatval(trim($defaultsarray['geotab_x_zone_offset']));	//0.002;
					$y_off=floatval(trim($defaultsarray['geotab_y_zone_offset']));	//0.002;			
					
					$gres=mrr_gps_point_box_creator($long,$lat,$x_off,$y_off);
										
					$res['long_zone_w']="".$gres['pt0_long_w']."";
					$res['long_zone_e']="".$gres['pt1_long_e']."";
					$res['lat_zone_n']="".$gres['pt0_lat_n']."";
					$res['lat_zone_s']="".$gres['pt1_lat_s']."";	
					
					$res['geotab_id_name']="";
					$res['conard_name']="";
					
					mrr_create_geotab_stop_zones($res);
				}   				
			}
			     			
			$geocode.=" <span style='color:purple;'><b>NO GPS Point FOUND.</b></span>";	
		}
		$geocode.="<br>JSON 2 Version ".strval($arr["jsonrpc"]).".";	
		
		//echo $geocode;
	} 
	
	return $res;
}
	
/*
[0]=> array(8) 
{ 
	["certifyRemark"]=> string(0) "" 
	["device"]=> array(1) { ["id"]=> string(2) "b1" } 
	["driverRemark"]=> string(0) "" 
	["dateTime"]=> string(24) "2018-02-28T13:50:46.476Z" 
	["driver"]=> array(1) { ["id"]=> string(2) "b9" } 
	["logType"]=> string(7) "PreTrip" 
	["version"]=> string(16) "00000000000000cc" 
	["id"]=> string(23) "aj6hEUOJh2EGaLBxxd69axw" 
}

*/


/*
	Zone 0.. Active From 1986-01-01 To 2050-01-01.
     Name: Conard drop yard.
     Ref: .
     Displayed: YES.
     Comment: .
     MustIdentifyStops: YES.
     *** Point 0. [ID -86.592079162598 , 36.017700195312]
     *** Point 1. [ID -86.59065246582  , 36.017597198486]
     *** Point 2. [ID -86.590690612793 , 36.017078399658]
     *** Point 3. [ID -86.592903137207 , 36.017204284668]
     *** Point 4. [ID -86.592864990234 , 36.017520904541]
     *** Point 5. [ID -86.592170715332 , 36.017448425293]
     *** Point 6. [ID -86.592079162598 , 36.017700195312]
     

HEXAGON ZONE STOPS:

Name: Conard drop yard.
*** Point 0. [ID -86.592079162598 , 36.017700195312] 		OFFSETS [  0 | 0 ].
*** Point 1. [ID -86.59065246582 , 36.017597198486] 		OFFSETS [ -0.0014266967773438  |  0.00010299682617188 ].
*** Point 2. [ID -86.590690612793 , 36.017078399658] 		OFFSETS [  3.814697265625E-5   |  0.000518798828125 ].
*** Point 3. [ID -86.592903137207 , 36.017204284668] 		OFFSETS [  0.0022125244140625  | -0.00012588500976562 ].
*** Point 4. [ID -86.592864990234 , 36.017520904541] 		OFFSETS [ -3.814697265625E-5   | -0.00031661987304688 ].
*** Point 5. [ID -86.592170715332 , 36.017448425293] 		OFFSETS [ -0.00069427490234375 |  7.2479248046875E-5 ].
*** Point 6. [ID -86.592079162598 , 36.017700195312] 		OFFSETS [ -9.1552734375E-5     | -0.00025177001953125 ].

Name: Bridgestone.
*** Point 0. [ID -86.600791931152 , 36.011001586914] 		OFFSETS [  0 | 0 ].
*** Point 1. [ID -86.60026550293 , 36.011295318604] 		OFFSETS [ -0.00052642822265625 | -0.00029373168945312 ].
*** Point 2. [ID -86.594833374023 , 36.010730743408] 		OFFSETS [ -0.00543212890625    |  0.0005645751953125 ].
*** Point 3. [ID -86.595848083496 , 36.004421234131] 		OFFSETS [  0.0010147094726562  |  0.0063095092773438 ].
*** Point 4. [ID -86.603164672852 , 36.005310058594] 		OFFSETS [  0.0073165893554688  | -0.00088882446289062 ].
*** Point 5. [ID -86.60179901123 , 36.011894226074] 		OFFSETS [ -0.0013656616210938  | -0.0065841674804688 ].
*** Point 6. [ID -86.600791931152 , 36.011001586914] 		OFFSETS [ -0.001007080078125   |  0.00089263916015625 ].

Name: Ingram Book.
*** Point 0. [ID -86.59009552002 , 35.998908996582] 		OFFSETS [  0 | 0 ].
*** Point 1. [ID -86.586837768555 , 35.998516082764] 		OFFSETS [ -0.0032577514648438  |  0.00039291381835938 ].
*** Point 2. [ID -86.587074279785 , 35.996669769287] 		OFFSETS [  0.00023651123046875 |  0.0018463134765625 ].
*** Point 3. [ID -86.588684082031 , 35.995754241943] 		OFFSETS [  0.0016098022460938  |  0.00091552734375 ].
*** Point 4. [ID -86.590766906738 , 35.996006011963] 		OFFSETS [  0.0020828247070312  | -0.00025177001953125 ].
*** Point 5. [ID -86.590270996094 , 35.998916625977] 		OFFSETS [ -0.00049591064453125 | -0.0029106140136719 ].
*** Point 6. [ID -86.59009552002 , 35.998908996582] 		OFFSETS [ -0.00017547607421875 |  7.62939453125E-6 ].


SQUARE ZONE STOPS

Name: Ingram Periodicals Inc.
*** Point 0. [ID -86.592170715332 , 36.001831054688] 		OFFSETS [  0 | 0 ].
*** Point 1. [ID -86.589416503906 , 36.001396179199] 		OFFSETS [ -0.0027542114257812  |  0.00043487548828125 ].
*** Point 2. [ID -86.589828491211 , 35.999057769775] 		OFFSETS [  0.0004119873046875  |  0.0023384094238281 ].
*** Point 3. [ID -86.592514038086 , 35.99983215332] 		OFFSETS [  0.002685546875      | -0.00077438354492188 ].
*** Point 4. [ID -86.592170715332 , 36.001831054688] 		OFFSETS [ -0.00034332275390625 | -0.0019989013671875 ].

Name: Cardinal.
*** Point 0. [ID -86.592948913574 , 35.998798370361] 		OFFSETS [  0 | 0 ].
*** Point 1. [ID -86.590835571289 , 35.998592376709] 		OFFSETS [ -0.0021133422851562  |  0.00020599365234375 ].
*** Point 2. [ID -86.591209411621 , 35.996143341064] 		OFFSETS [  0.00037384033203125 |  0.0024490356445312 ].
*** Point 3. [ID -86.593315124512 , 35.996356964111] 		OFFSETS [  0.002105712890625   | -0.000213623046875 ].
*** Point 4. [ID -86.592948913574 , 35.998798370361] 		OFFSETS [ -0.0003662109375     | -0.00244140625 ].

Name: Ryder.
*** Point 0. [ID -86.597961425781 , 36.002635955811] 		OFFSETS [  0 | 0 ].
*** Point 1. [ID -86.596031188965 , 36.002376556396] 		OFFSETS [ -0.0019302368164062  |  0.0002593994140625 ].
*** Point 2. [ID -86.596397399902 , 36.000629425049] 		OFFSETS [  0.0003662109375     |  0.0017471313476562 ].
*** Point 3. [ID -86.598434448242 , 36.000770568848] 		OFFSETS [  0.0020370483398438  | -0.00014114379882812 ].
*** Point 4. [ID -86.597961425781 , 36.002635955811] 		OFFSETS [ -0.0004730224609375  | -0.0018653869628906 ].
     
     



Leg = A single leg of Directions between origin and destination Waypoints.
		Step = A single step in a sequence of step-by-step instructions to complete Leg of Directions.
		
LocationContent = Message content that can send a GPS location to a device. Derived from TextContent.

ReverseGeocodeAddress = The address and Zone (if any found) returned by a reverse geocode operation.
GetAddresses = SAME AS ReverseGeocodeAddress apparently only it has parameters...

GetCoordinates = Geocodes or looks up the latitude and longitude from a list of addresses.


Route = A connected sequence of zones which create a path for the vehicle to follow.
	RoutePlanItem = The class representing an individual item in a planned Route.	
	RouteType = A type of Route. Route is either "Basic" or "Plan".

Waypoint = A set of coordinates that reference a location.
{	
	coordinate 	The Coordinate.
	description	The waypoint description
	sequence	 	The sequence number.
}

Coordinate	Specify x for the longitude. Specify y for the latitude.  Both are numbers.

Zone = Sometimes refereed to as a "Geofence", a zone is a virtual geographic boundary, defined by it's points representing a real-world geographic area.

/*
//USER LIST AS OF 10:43
User 7. b7 Dale Conard (STAFF) Employee# Active from 2017-12-12T16:39:00.623Z to 2050-01-01. LogIN: dconard@conardlogistics.com.
User 8. b5 Dale Conard (STAFF) Employee# Active from 2017-12-11T23:38:10.877Z to 2050-01-01. LogIN: dconard@conardtransportation.com.
User 1. b19 alexmontenegro (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: alexmontenegro@geotab.com.
User 2. b17 anniehart (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: anniehart@geotab.com.
User 3. b1C arjeemendez (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: arjeemendez@geotab.com.
User 4. bD benschwartz (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: benschwartz@geotab.com.
User 5. b20 brentmcinnis (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: brentmcinnis@geotab.com.
User 9. b16 eduardotassara (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: eduardotassara@geotab.com.
User 10. b1D gracielacamacho (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: gracielacamacho@geotab.com.
User 12. b4 Jamilla Kebede (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: jamillakebede@geotab.com.
User 13. b8 James Griffith (STAFF) Employee# Active from 2017-12-13T00:36:22.177Z to 2050-01-01. LogIN: jgriffith@conardtransportation.com.
User 14. b10 josephstamps (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: josephstamps@geotab.com.
User 15. bF justindigal (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: justindigal@geotab.com.
User 16. bC kevinterrell (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: kevinterrell@geotab.com.
User 17. b18 Login (STAFF) Employee# Active from 2018-01-29T20:33:27.880Z to 2050-01-01. LogIN: login@navistar.com.
User 18. b15 Louis-Phillippe Papillon (STAFF) Employee# Active from 2018-01-09T19:56:22.957Z to 2050-01-01. LogIN: lppapillon@d2go.io.
User 19. b1E mauriciomuniz (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: mauriciomuniz@geotab.com.
User 20. b11 Michael Richardson (STAFF) Employee# Active from 2018-01-03T17:15:24.100Z to 2050-01-01. LogIN: michael@sherrodcomputers.com.
User 21. bE michellesutton (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: michellesutton@geotab.com.
User 22. b1F omedsherzad (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: omedsherzad@geotab.com.
User 23. b14 rogergu (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: rogergu@geotab.com.
User 24. bA rudyr (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: rudyr@rushenterprises.com.
User 25. b21 samanthamartinez-luna (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: samanthamartinez-luna@geotab.com.
User 26. b13 tongyan (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: tongyan@geotab.com.
User 27. b1A trenawade (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: trenawade@geotab.com.
User 28. b1B vikramsridhar (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: vikramsridhar@geotab.com.
User 29. bB viksridhar (STAFF) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: viksridhar@geotab.com.

User 0. b9 Darren Isenberger (DRIVER) Employee# 334 Active from 2017-12-13T14:14:08.660Z to 2050-01-01. LogIN: 334.
User 6. b12 Test Test1 (DRIVER) Employee# Active from 2018-01-03T17:39:39.283Z to 2050-01-01. LogIN: conard.
User 11. b6 TEST ACCOUNT (DRIVER) Employee# Active from 1986-01-01 to 2050-01-01. LogIN: hunterc@rushenterprises.com.

*/
?>