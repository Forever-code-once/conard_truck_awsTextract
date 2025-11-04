<? error_reporting(E_ALL); ?>
<? include('header.php') ?>
<div style='float:left;text-align:left'>
<br><br><br>
<?

function test_chart() {
	$api = new sicap_api_connector();
	$api->command = "get_chart_of_accounts";
	$api->addParam("searchName", "Bad Debt");
	//$api->addParam("searchChartID", "113");
	//$api->addParam("searchChartNumber", "8000100002");
	$xmlObject = $api->execute();
	
	echo "RecordCount: ".$xmlObject->recordcount."<br>";
	foreach($xmlObject->ChartEntry as $chart) {
		echo "Chart Entry: (".$chart->Name.") - ".$chart->Number." <br>";
	}
}
function test_customer() {
	$api = new sicap_api_connector();
	$api->command = "get_customers";
	$api->addParam("searchCompanyName", "ABC");
	$xmlObject = $api->execute();
	
	echo "RecordCount: ".$xmlObject->recordcount."<br>";
	foreach($xmlObject->CustomerEntry as $line) {
		echo "Chart Entry: (".$line->CompanyName.") - ".$line->ID." <br>";
	}
}

function test_vendor() {
	$api = new sicap_api_connector();
	$api->command = "get_vendors";
	//$api->addParam("searchCompanyName", "ABC");
	$xmlObject = $api->execute();
	
	echo "RecordCount: ".$xmlObject->recordcount."<br>";
	foreach($xmlObject->VendorEntry as $line) {
		echo "Chart Entry: (".$line->CompanyName.") - ".$line->ID." <br>";
	}
}

function test_invoice() {
	$api = new sicap_api_connector();
	$api->command = "get_invoices";
	$api->addParam("maxResults", "50");
	$xmlObject = $api->execute();
	
	echo "RecordCount: ".$xmlObject->recordcount."<br>";
	foreach($xmlObject->InvoiceEntry as $line) {
		echo "Chart Entry: (".$line->Number.") - ".$line->ID." <br>";
	}
}

function test_address() {
	$api = new sicap_api_connector();
	$api->command = "get_addresses";
	$api->addParam("searchCustomerID", "6594");
	$api->addParam("maxResults", "50");
	$xmlObject = $api->execute();
	
	echo "RecordCount: ".$xmlObject->recordcount."<br>";
	foreach($xmlObject->AddressEntry as $line) {
		echo "Chart Entry: (".$line->Address1.") - ".$line->ID." <br>";
	}
}

function test_chart_types() {
	$api = new sicap_api_connector();
	$api->command = "get_chart_types";
	//$api->addParam("searchCustomerID", "6594");
	$api->addParam("maxResults", "50");
	$xmlObject = $api->execute();
	
	echo "RecordCount: ".$xmlObject->recordcount."<br>";
	foreach($xmlObject->ChartTypeEntry as $line) {
		echo "".$line->ChartType." | ".$line->ID." <br>";
	}
}


function test_inventory() {
	$api = new sicap_api_connector();
	$api->command = "get_inventory";
	//$api->addParam("searchID", "44036 ");
	$api->addParam("maxResults", "50");
	$xmlObject = $api->execute();
	
	echo "RecordCount: ".$xmlObject->recordcount."<br>";
	foreach($xmlObject->InventoryEntry as $line) {
		echo "Chart Entry: (".$line->Name.") - ".$line->ID." | qty on hand: " . $line->QtyOnHand . "<br>";
	}
}

$api = new sicap_api_connector();
$cost_of_sales_id 	= $api->getChartTypeIDByName("Cost of Sales");
$income_id		= $api->getChartTypeIDByName("Income");




sicap_update_customers();

$sql = "
	select *
	
	from customers
	where sicap_id = 0
		and deleted = 0
";
$data_customers = simple_query($sql);

echo "<h3 style='text-align:center'>Customers</h3>";

while($row_cust = mysqli_fetch_array($data_customers)) {
	echo "$row_cust[name_company]";
	
	$api->clearParams();
	$api->command = "create_customer";
	$api->addParam("CompanyName", $row_cust['name_company']);
	$rslt = $api->execute();
	
	if(isset($rslt->NewID) && intval($rslt->NewID) > 0) {
		$new_id = intval($rslt->NewID);
	} else {
		$new_id = 0;
	}
	
	if($new_id > 0) {
		$sql = "
			update customers
			set sicap_id = '".sql_friendly($new_id)."'
			where id = '".sql_friendly($row_cust['id'])."'
		";
		simple_query($sql);
	}
	
	echo "(".$rslt->NewID.") <br>";
}

// get a list of the trucks
$sql = "
	select *,
		trim(name_truck) as name_truck
	
	from trucks
	where deleted = 0
		and sicap_coa_created = 0
";
$data_trucks = simple_query($sql);

echo "<h3 style='text-align:center'>Trucks</h3>";
while($row_truck = mysqli_fetch_array($data_trucks)) {
	if(is_numeric($row_truck['name_truck'])) {
		// look up to see if the chart of accounts exist

		$truck_name = $row_truck['name_truck'];
		
		verify_coa("Income-Truck #$truck_name", "41000-$truck_name", $income_id);
		verify_coa("Fuel - #$truck_name", "58800-$truck_name", $cost_of_sales_id);
		verify_coa("Layover Expense - #$truck_name", "65000-$truck_name", $cost_of_sales_id);
		verify_coa("Lease Drivers - #$truck_name", "67000-$truck_name", $cost_of_sales_id);
		verify_coa("Lumper #$truck_name", "68270-$truck_name", $cost_of_sales_id);
		verify_coa("Repairs & Maint - #$truck_name", "74500-$truck_name", $cost_of_sales_id);
		verify_coa("Stop off - #$truck_name", "75500-$truck_name", $cost_of_sales_id);
		verify_coa("Truck Rental - Fixed - #$truck_name", "77950-$truck_name", $cost_of_sales_id);
		verify_coa("Trk Lease Fixed - #$truck_name", "78000-$truck_name", $cost_of_sales_id);
		verify_coa("Truck Rental - Variable - #$truck_name", "78050-$truck_name", $cost_of_sales_id);
		verify_coa("Truck Lease Variable - #$truck_name", "78100-$truck_name", $cost_of_sales_id);
		verify_coa("Weigh Ticket Expense - #$truck_name", "79000-$truck_name", $cost_of_sales_id);

		$sql = "
			update trucks
			set sicap_coa_created = 1
			where id = '$row_truck[id]'
		";
		simple_query($sql);

		echo "Truck: ($row_truck[name_truck])<br>";
	} else {
		echo "not numeric ($row_truck[name_truck])<br>";
	}
}


// get a list of the trailers
$sql = "
	select *,
		trim(trailer_name) as trailer_name
	
	from trailers
	where deleted = 0
		and sicap_coa_created = 0
";
$data_trailers = simple_query($sql);

echo "<h3 style='text-align:center'>Trailers</h3>";
while($row_trailer = mysqli_fetch_array($data_trailers)) {
	if(is_numeric($row_trailer['trailer_name'])) {
		// look up to see if the chart of accounts exist

		$trailer_name = $row_trailer['trailer_name'];
		
		verify_coa("Trailer Repairs & Maint - #$trailer_name", "77500-$trailer_name", $cost_of_sales_id);
		verify_coa("Tires #$trailer_name", "77600-$trailer_name", $cost_of_sales_id);
		verify_coa("Trailer Wash - #$trailer_name", "77800-$trailer_name", $cost_of_sales_id);


		$sql = "
			update trailers
			set sicap_coa_created = 1
			where id = '$row_trailer[id]'
		";
		simple_query($sql);

		echo "Trailer: ($row_trailer[trailer_name])<br>";
	} else {
		echo "not numeric ($row_trailer[trailer_name])<br>";
	}
}

/*
echo "
	Income ID: $income_id<br>
	COS ID: $cost_of_sales_id
";
*/
?>

<!--
<br><br><br>

<br><br>

<input type='button' value='Test COA' onclick='testcoa()'>

<script type='text/javascript'>
	function testcoa() {
		$.ajax({
			url:"accounting/api.php",
			type: "post",
			dataType: "xml",
			data: {
				api_key: "<?=$api_connect_key?>",
				cmd: "get_chart_of_accounts"
			},
			error: function() {
				alert('error');
			},
			success: function(xml) {
				$.prompt($(xml).find('ChartEntry').text());
			}
		});
	}
</script>
-->
</div>
<? include('footer.php') ?>