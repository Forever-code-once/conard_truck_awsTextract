<?
// class to connect to the SICAP accounting system
// version 1 
// created on 6/7/2011
// Author: Chris Sherrod

class sicap_api_connector {
	
	// enter the information 
	//var $api_connect_key 	= "13074777974dee872559ffd8.39489359";
	//var $api_root_url		= "http://demo.conardlogistics.com/accounting/"; //(i.e. http://192.168.0.1/sicap/";
	
	//var $api_connect_key 	= $defaultsarray['sicap_integration_key'];
	//var $api_root_url		= $defaultsarray['sicap_integration_url']; //(i.e. http://192.168.0.1/sicap/";
	
	// set some default val
	var $postData 			= "";
	var $debug_post 		= false;
	var $show_output 		= false;
	var $version			= "1";
	
	function execute() {
		
		//global $api_connect_key;
		global $defaultsarray;
		
		$api_connect_key = $defaultsarray['sicap_integration_key'];
		$api_root_url = $defaultsarray['sicap_integration_url'];
		
		$this->postData['api_key'] = $api_connect_key;
		$this->postData['cmd'] = $this->command;
				
		if($this->debug_post) {
			echo "<pre>";
			print_r($this->postData);
			echo "</pre>";
			die;
		}
		
		$url = $api_root_url."/api.php";
		
		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL,$url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl_handle, CURLOPT_POST,1);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); // we don't want to stop the page from loading if there is ever an issue with the SSL
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $this->postData);
		$buffer = @curl_exec($curl_handle);
		curl_close($curl_handle);

		if($this->show_output) echo "<pre>$buffer</pre>";

		$xmlObject = new SimpleXMLElement($buffer);
		
		return $xmlObject;
	}
	
	function addParam($param_name, $param_value) {
		$this->postData[$param_name] = $param_value;
	}
	
	function clearParams() {
		unset($this->postData);
	}
	
	function getChartTypeIDByName($chart_type_name) {
		$this->clearParams();
		
		$this->postData['searchChartType'] = $chart_type_name;
		$this->command = "get_chart_types";
		$rslt = $this->execute();
		
		$chart_id = $rslt->ChartTypeEntry->ID;
		if($chart_id == '') $chart_id = 0;
		return $chart_id;
	}
	
	function getChartIDByName($chart_name) {
		$this->clearParams();
		
		$this->addParam('searchName',$chart_name);
		$this->command = "get_chart_of_accounts";
		$rslt = $this->execute();
		
		$chart_id = $rslt->ChartEntry->ID;
		if($chart_id == '') $chart_id = 0;
		return intval($chart_id);
	}
	
	function getInventoryIDByName($item_name) {
		$this->clearParams();
		
		$this->addParam('searchName',$item_name);
		$this->command = "get_inventory";
		$rslt = $this->execute();
		
		$item_id = $rslt->InventoryEntry->ID;
		if($item_id == '') $item_id = 0;
		return intval($item_id);
	}
	
	function getCustomerIDByName($cust_name) {
		$this->clearParams();
		
		$this->addParam('searchCompanyName',$cust_name);
		$this->command = "get_customers";
		$rslt = $this->execute();
		
		$item_id = $rslt->CustomerEntry->ID;
		if($item_id == '') $item_id = 0;
		return intval($item_id);
	}
	
	function getVendorIDByName($cust_name) {
		$this->clearParams();
		
		$this->addParam('searchCompanyName',$cust_name);
		$this->command = "get_vendors";
		$rslt = $this->execute();
		
		$item_id = $rslt->VendorEntry->ID;
		if($item_id == '') $item_id = 0;
		return intval($item_id);
	}
}
?>