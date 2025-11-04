<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

	//include('application.php');
	
	
		/*
		$url = "https://service.cyntrxfleet.com:6443/external/Cyntrx.External.Web.Services.DeviceHistoryService.svc";
		
		$xml = "
			<Authenticate>
				<user>conardtrans_svc</user>
				<password>ct5vhB42x</password>
			</Authenticate>
		";
		
		
		//$postData['Authenticate'] = "conardtrans_svc";
		//$postData['user'] = "conardtrans_svc";
		//$postData['password'] = "ct5vhB42x";
		
		//print_r($postData);
		//die;
		
		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL,$url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl_handle, CURLOPT_POST,1);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); // we don't want to stop the page from loading if there is ever an issue with the SSL
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $postData);
		$buffer = @curl_exec($curl_handle);
		curl_close($curl_handle);
		
		echo "($buffer)";
		*/
		
	/*
	$client = new SoapClient('https://service.cyntrxfleet.com:6443/external/Cyntrx.External.Web.Services.DeviceHistoryService.svc');
	//$msg['AccountData']['any'] = "<somexml></somexml><otherxml></otherxml>";
	$sh_param = array(	'user'		=> 	'conardtrans_svc',
					'password'	=>	'ct5vhB42x');
	//$headers = new SoapHeader('https://service.cyntrxfleet.com:6443/external/Cyntrx.External.Web.Services.DeviceHistoryService.svc', 'Authenticate', $sh_param); 
        // Prepare Soap Client
        //$soapClient->__setSoapHeaders(array($headers));
   
        // Setup the RemoteFunction parameters
        $ap_param = array('amount'     =>    $irow['total_price']);
                   
        // Call RemoteFunction ()
        $error = 0;
        try {
            $info = $soapClient->__call("Authenticate", array($sh_param));
        } catch (SoapFault $fault) {
            $error = 1;
            echo "Sorry, blah returned the following ERROR: ".$fault->faultcode."-".$fault->faultstring.". We will now take you back to our home page.";
        } 
    */
    
	require_once('nusoap/nusoap.php');
	$proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
	$proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
	$proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
	$proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
	$useCURL = isset($_POST['usecurl']) ? $_POST['usecurl'] : '0';    
    
    if(1 == 2) {
		$client = new nusoap_client("http://trucking.conardlogistics.com/cyntrx_test.php");
		$client->soap_defencoding = 'UTF-8';
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}
		$client->setUseCurl($useCURL);
		// This is an archaic parameter list
		$params = array(
		    'name'	    => "Chris"
		);
		
		$result = $client->call('hello', $params);
	} else {

		$client = new nusoap_client("https://service.cyntrxfleet.com:6443/external/Cyntrx.External.Web.Services.DeviceHistoryService.svc", false);
		//$client = new nusoap_client("https://service.cyntrxfleet.com:6443/external/");
		$client->soap_defencoding = 'UTF-8';
		$err = $client->getError();
		if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}
		$client->setUseCurl($useCURL);
		// This is an archaic parameter list
		$params = array(
		    'User'	    => "conardtrans_svc",
		    'Password'     => 'ct5vhB42x'
		);
		
		$result = $client->call('Authenticate', $params, false, false);
		//$result = $client->call('Authenticate', $params, false, false);
		//$result = $client->call('IsTokenActive', $params_token, "DeviceHistoryServiceReference.DeviceHistoryServiceClient", "IsTokenActive", false, true);
	}
	
	if ($client->fault) {
		echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
	} else {
		$err = $client->getError();
		if ($err) {
			echo '<h2>Error</h2><pre>' . $err . '</pre>';
		} else {
			echo '<h2>Result</h2><pre>'; print_r($result); echo '</pre>';
		}
	}
	echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
	
		
?>

<br><br>
done...