<?
function verify_coa($chart_name, $chart_number, $chart_type_id) {
	// verify the chart of account (exists) 
	$api = new sicap_api_connector();
	
	$chart_name=str_replace(" (Team Rate)","",$chart_name);
	$chart_number=str_replace(" (Team Rate)","",$chart_number);
	
	$api->clearParams();
	$api->command = "create_chart_of_account";
	$api->addParam("ChartNumber", $chart_number);
	$api->addParam("ChartName", $chart_name);
	$api->addParam("ChartTypeID", $chart_type_id);
	//$api->show_output = true;
	$api->execute();
}

function update_coa_mrr_link($truckname="",$trailername="")
{	//upda truck/trailer link on OCAs from dispatch side to make sorting/grouping by truck/trailer possible.
	$api = new sicap_api_connector();
	
	$api->clearParams();
	$api->command = "api_update_chart_of_account_mrr";
	$api->addParam("TruckName", $truckname);
	$api->addParam("TrailerName", $trailername);
	//$api->show_output = true;
	$api->execute();	
}

function update_coa($new_chart_name, $new_chart_number, $new_chart_type_id, $old_chart_name = '',$active=1,$truckname="",$trailername="") {
	$api = new sicap_api_connector();
	
	$new_chart_name=str_replace(" (Team Rate)","",$new_chart_name);
	$new_chart_number=str_replace(" (Team Rate)","",$new_chart_number);
	
	$old_chart_name=str_replace(" (Team Rate)","",$old_chart_name);
	
	// see if the old chart existed
	$chart_id = 0;
	if($old_chart_name != '') $chart_id = $api->getChartIDByName($old_chart_name);
	
	if($chart_id > 0) {
		// old chart does **exist**
		//echo "chart exists: $chart_id ($chart_id)<br>";
		$api->clearParams();
		$api->command = "update_chart_of_account";
		$api->addParam("ChartName", $new_chart_name);
		$api->addParam("ChartID", $chart_id);
		$api->addParam("ChartTypeID", $new_chart_type_id);
		$api->addParam("ChartNumber", $new_chart_number);
		
		$api->addParam("TruckName", trim($truckname));
		$api->addParam("TrailerName", trim($trailername));
		
		$api->addParam("Active", $active);
		//$api->show_output = true;
		$rslt = $api->execute();
	} else {
		// old chart does NOT exist - so create it
		//echo "old chart does NOT exist ($old_chart_name) - creating new one - $new_chart_name<br>";
		verify_coa($new_chart_name, $new_chart_number, $new_chart_type_id);
	}
	
	//die($new_chart_name);
	
	//die("new truck name: ($new_chart_name) - old ($old_chart_name)");
}

function sicap_update_customers($customer_id = 0) {
	$api = new sicap_api_connector();
	
	$sql = "
		select *
		
		from customers
		where deleted = 0
			".($customer_id > 0 ? " and id = '".sql_friendly($customer_id)."' " : "")."
	";
	$data_customers = simple_query($sql);

	while($row_cust = mysqli_fetch_array($data_customers)) {
		echo "$row_cust[name_company]";
		
		$sicap_id = $row_cust['sicap_id'];
		if($sicap_id == 0) {
			echo "creating";
			
			// create the customer in the accounting system
			$api->clearParams();
			$api->command = "create_customer";
			$api->addParam("CompanyName", $row_cust['name_company']);
			//$api->show_output = true;
			$rslt = $api->execute();
			
			if(isset($rslt->NewID) && intval($rslt->NewID) > 0) {
				$sicap_id = intval($rslt->NewID);
			} else {
				$sicap_id = 0;
			}
			
			if($sicap_id > 0) {
				$sql = "
					update customers
					set sicap_id = '".sql_friendly($sicap_id)."'
					where id = '".sql_friendly($row_cust['id'])."'
				";
				simple_query($sql);
			}
		}
		
		// update the customer's information
		if($sicap_id > 0) {
			
			$api->clearParams();
			$api->command = "update_customer";
			$api->addParam("CompanyName", $row_cust['name_company']);
			$api->addParam("CustomerID", $sicap_id);
			$api->addParam("Contact", $row_cust['contact_primary']);
			$api->addParam("Email", $row_cust['contact_email']);
			$api->addParam("CompanyPhone", $row_cust['phone_work']);
			$api->addParam("Fax", $row_cust['fax']);
			$api->addParam("Active", $row_cust['active']);
			$api->addParam("Website", $row_cust['website']);
			if(trim($row_cust['billing_address1'])=="")
			{
				$api->addParam("Address1", $row_cust['address1']);
				$api->addParam("Address2", $row_cust['address2']);
				$api->addParam("City", $row_cust['city']);
				$api->addParam("State", $row_cust['state']);
				$api->addParam("Zip", $row_cust['zip']);
			}
			else
			{
				$api->addParam("Address1", $row_cust['billing_address1']);
				$api->addParam("Address2", $row_cust['billing_address2']);
				$api->addParam("City", $row_cust['billing_city']);
				$api->addParam("State", $row_cust['billing_state']);
				$api->addParam("Zip", $row_cust['billing_zip']);
			}
			//$api->show_output = true;
			$rslt = $api->execute();
			echo "(updated)";
		}
		
		echo "($sicap_id) <br>";
	}
	
}

function sicap_update_trucks($truck_id = 0, $old_name = '', $quick_update = false, $rental = 0) {
	// get a list of the trucks
	$sql = "
		select *,
			trim(name_truck) as name_truck
		
		from trucks
		where deleted = 0
			".($truck_id > 0 ? " and id = '".sql_friendly($truck_id)."' " : "")."
	";
	$data_trucks = simple_query($sql);
	
	$api = new sicap_api_connector();
	$cost_of_sales_id 	= $api->getChartTypeIDByName("Cost of Sales");
	$income_id		= $api->getChartTypeIDByName("Income");
	
	$ignore_int_name=1;
	
	//echo "<h3 style='text-align:center'>Trucks</h3>";
	while($row_truck = mysqli_fetch_array($data_trucks)) {
		
		if(is_numeric($row_truck['name_truck']) || $ignore_int_name==1) 
		{			
			$truck_name = $row_truck['name_truck'];
			
			$truck_name=str_replace(" (Team Rate)","",$truck_name);
			
			$truck_name=mrr_make_numeric2($truck_name);
			
			$rent_lab="";	
			
			$active=1;//$row_truck['active'];		
			
			if(trim($row_truck['leased_from'])!="" && $row_truck['rental'] > 0)		$rent_lab=" (R) ".$row_truck['leased_from']."";
			if(trim($row_truck['leased_from'])!="" && $row_truck['rental']==0)		$rent_lab=" (L) ".$row_truck['leased_from']."";
			
			if(!$quick_update) 
			{
				//first pass...assume name does not have the renatal label...
				$rent_lab2="";				
				update_coa("Income-Truck #$truck_name".$rent_lab."", "41000-$truck_name", $income_id, ($old_name != '' ? "Income-Truck #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				update_coa("Discount-Truck #$truck_name".$rent_lab."", "46000-$truck_name", $income_id, ($old_name != '' ? "Discount-Truck #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				update_coa("Fuel - #$truck_name".$rent_lab."", "58800-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Fuel - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				update_coa("Layover Expense - #$truck_name".$rent_lab."", "65000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Layover Expense - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				update_coa("Lease Drivers - #$truck_name".$rent_lab."", "67000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lease Drivers - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				update_coa("Lumper #$truck_name".$rent_lab."", "68270-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lumper #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				update_coa("Truck Repairs & Maint - #$truck_name".$rent_lab."", "74500-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Repairs & Maint - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
                    
                    update_coa("Truck Tires - #$truck_name".$rent_lab."", "74510-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Tires - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
                                        
                    update_coa("Stop off - #$truck_name".$rent_lab."", "75500-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Stop off - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");				
				update_coa("Lease Drivers Panther Bonus - #$truck_name".$rent_lab."", "67100-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lease Drivers Panther Bonus - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");				
				//if($rental > 0)
				//{			
					update_coa("Truck Rental - Fixed - #$truck_name".$rent_lab."", "77950-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Rental - Fixed - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				//}
				//else
				//{
					update_coa("Truck Lease Fixed - #$truck_name".$rent_lab."", "78000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Lease Fixed - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				//}
				update_coa("Truck Rental Mileage Exp - #$truck_name".$rent_lab."", "78050-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Rental Mileage Exp - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				update_coa("Truck Lease Mileage Exp - #$truck_name".$rent_lab."", "78100-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Lease Mileage Exp - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				update_coa("Weigh Ticket Expense - #$truck_name".$rent_lab."", "79000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Weigh Ticket Expense - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
				update_coa("Truck Accidents - #$truck_name".$rent_lab."", "74000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Accidents - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");				
				update_coa("Truck Cleaning - #$truck_name".$rent_lab."", "74900-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Cleaning - #$old_name".$rent_lab2."" : ""),$active,$truck_name,"");
							
				
				//second pass...try it without.
				update_coa("Income-Truck #$truck_name".$rent_lab."", "41000-$truck_name", $income_id, ($old_name != '' ? "Income-Truck #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Discount-Truck #$truck_name".$rent_lab."", "46000-$truck_name", $income_id, ($old_name != '' ? "Discount-Truck #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Fuel - #$truck_name".$rent_lab."", "58800-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Fuel - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Layover Expense - #$truck_name".$rent_lab."", "65000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Layover Expense - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Lease Drivers - #$truck_name".$rent_lab."", "67000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lease Drivers - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Lumper #$truck_name".$rent_lab."", "68270-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lumper #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Truck Repairs & Maint - #$truck_name".$rent_lab."", "74500-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Repairs & Maint - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Stop off - #$truck_name".$rent_lab."", "75500-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Stop off - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");				
				update_coa("Lease Drivers Panther Bonus - #$truck_name".$rent_lab."", "67100-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Lease Drivers Panther Bonus - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");				
				//if($rental > 0)
				//{			
					update_coa("Truck Rental - Fixed - #$truck_name".$rent_lab."", "77950-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Rental - Fixed - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				//}
				//else
				//{
					update_coa("Truck Lease Fixed - #$truck_name".$rent_lab."", "78000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Lease Fixed - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				//}
				update_coa("Truck Rental Mileage Exp - #$truck_name".$rent_lab."", "78050-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Rental Mileage Exp - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Truck Lease Mileage Exp - #$truck_name".$rent_lab."", "78100-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Lease Mileage Exp - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Weigh Ticket Expense - #$truck_name".$rent_lab."", "79000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Weigh Ticket Expense - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
				update_coa("Truck Accidents - #$truck_name".$rent_lab."", "74000-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Accidents - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");				
				update_coa("Truck Cleaning - #$truck_name".$rent_lab."", "74900-$truck_name", $cost_of_sales_id, ($old_name != '' ? "Truck Cleaning - #$old_name".$rent_lab."" : ""),$active,$truck_name,"");
			}
	
			$sql = "
				update trucks
				set sicap_coa_created = 1
				where id = '$row_truck[id]'
			";
			simple_query($sql);
			
			$inventory_item_id = 0;
			if($old_name != '') $inventory_item_id = $api->getInventoryIDByName($old_name);
			
			$chart_id_sales = $api->getChartIDByName("Income-Truck #$truck_name");
			$chart_id_inv = $chart_id_sales;
			$chart_id_cost = $chart_id_sales;
			
			$api->clearParams();
			$api->command = "update_inventory_item";
			$api->addParam("ItemName", $truck_name);
			$api->addParam("ItemID",$inventory_item_id);
			$api->addParam("ChartIDSales", $chart_id_sales);
			$api->addParam("Active",$active);
			$api->addParam("ChartIDInventory", $chart_id_inv);
			$api->addParam("ChartIDCOGS", $chart_id_cost);
			//$api->show_output = true;
			$api->execute();
	
			$inventory_item_id = 0;
			if($old_name != '') $inventory_item_id = $api->getInventoryIDByName($old_name."-Discount");
	
			$chart_id_sales = $api->getChartIDByName("Discount-Truck #$truck_name");
			$chart_id_inv = $chart_id_sales;
			$chart_id_cost = $chart_id_sales;
	
			$api->clearParams();
			$api->command = "update_inventory_item";
			$api->addParam("ItemName", $truck_name."-Discount");
			$api->addParam("ItemID",$inventory_item_id);
			$api->addParam("ChartIDSales", $chart_id_sales);
			$api->addParam("Active",$active);
			$api->addParam("ChartIDInventory", $chart_id_inv);
			$api->addParam("ChartIDCOGS", $chart_id_cost);
			//$api->show_output = true;
			$api->execute();
			
			$inventory_item_id = 0;
		
     		$chart_id_sales = $api->getChartIDByName("Truck Repairs - #$truck_name");
     		$chart_id_inv = $chart_id_sales;
     		$chart_id_cost = $chart_id_sales;
     		
     		$api->clearParams();
     		$api->command = "update_inventory_item";
     		$api->addParam("ItemName", $truck_name."-Truck Repairs");
     		$api->addParam("ItemID",$inventory_item_id);
     		$api->addParam("ChartIDSales", $chart_id_sales);
     		$api->addParam("Active", $active);
     		$api->addParam("ChartIDInventory", $chart_id_inv);
     		$api->addParam("ChartIDCOGS", $chart_id_cost);
     		
     		$api->execute();	
	
			//echo "Truck: ($row_truck[name_truck])<br>";
		} else {
			//echo "not numeric ($row_truck[name_truck])<br>";
		}
	}
}

function sicap_update_trailers($trailer_id = 0, $old_name = '') {
	// get a list of the trailers
	$sql = "
		select *,
			trim(trailer_name) as trailer_name
		
		from trailers
		where deleted = 0
			".($trailer_id > 0 ? " and id = '".sql_friendly($trailer_id)."' " : "")."
	";
	$data = simple_query($sql);
	
	$ignore_int_name=1;
	
	$api = new sicap_api_connector();
	$cost_of_sales_id 	= $api->getChartTypeIDByName("Cost of Sales");
	
	//echo "<h3 style='text-align:center'>Trailers</h3>";
	while($row = mysqli_fetch_array($data)) {
		
		if(is_numeric($row['trailer_name']) || $ignore_int_name==1) 
		{			
			$trailer_name = $row['trailer_name'];
			
			$trailer_name=str_replace(" (Team Rate)","",$trailer_name);
			
			$trailer_name=mrr_make_numeric2($trailer_name);
			
			$active=1;	//$row['active'];	
			
			$rent_lab="";			
			
			if(trim($row['trailer_owner'])!="" && $row['rental_flag'] > 0)		$rent_lab=" (R) ".$row['trailer_owner']."";
			if(trim($row['trailer_owner'])!="" && $row['rental_flag']==0)		$rent_lab=" (L) ".$row['trailer_owner']."";
			
			//first pass...without rental label info
			$rent_lab2="";	
			update_coa("Trailer Repairs & Maint - #$trailer_name".$rent_lab."", "77500-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Repairs & Maint - #$old_name".$rent_lab2."" : ""),$active,"",$trailer_name);			
			update_coa("Tires #$trailer_name".$rent_lab."", "77600-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Tires #$old_name".$rent_lab2."" : ""),$active,"",$trailer_name);
			update_coa("Trailer Wash - #$trailer_name".$rent_lab."", "77800-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Wash - #$old_name".$rent_lab2."" : ""),$active,"",$trailer_name);
			update_coa("Trailer Accidents - #$trailer_name".$rent_lab."", "77485-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Accidents - #$old_name".$rent_lab2."" : ""),$active,"",$trailer_name);
			
			//second pass...with the rental label info
			update_coa("Trailer Repairs & Maint - #$trailer_name".$rent_lab."", "77500-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Repairs & Maint - #$old_name".$rent_lab."" : ""),$active,"",$trailer_name);			
			update_coa("Tires #$trailer_name".$rent_lab."", "77600-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Tires #$old_name".$rent_lab."" : ""),$active,"",$trailer_name);
			update_coa("Trailer Wash - #$trailer_name".$rent_lab."", "77800-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Wash - #$old_name".$rent_lab."" : ""),$active,"",$trailer_name);
			update_coa("Trailer Accidents - #$trailer_name".$rent_lab."", "77485-$trailer_name", $cost_of_sales_id, ($old_name != '' ? "Trailer Accidents - #$old_name".$rent_lab."" : ""),$active,"",$trailer_name);
			
			$sql = "
				update trailers
				set sicap_coa_created = 1
				where id = '$row[id]'
			";
			simple_query($sql);
			
			$inventory_item_id = 0;
		
     		$chart_id_sales = $api->getChartIDByName("Trailer Repairs - #$trailer_name");
     		$chart_id_inv = $chart_id_sales;
     		$chart_id_cost = $chart_id_sales;
     		
     		$api->clearParams();
     		$api->command = "update_inventory_item";
     		$api->addParam("ItemName", $trailer_name."-Trailer Repairs");
     		$api->addParam("ItemID",$inventory_item_id);
     		$api->addParam("ChartIDSales", $chart_id_sales);
     		$api->addParam("Active", $active);
     		$api->addParam("ChartIDInventory", $chart_id_inv);
     		$api->addParam("ChartIDCOGS", $chart_id_cost);
     		
     		$api->execute();
	
			//echo "Trailer: ($row[trailer_name])<br>";
		} else {
			//echo "not numeric ($row[trailer_name])<br>";
		}
	}
}

function sicap_create_invoice($load_id, $sicap_invoice_id = 0) 
{	
	// if sicap_invoice_id is specified, then we're adding this load to an existing invoice
	
	$api = new sicap_api_connector();
	$api_item_id = new sicap_api_connector();
     
     $sicap_invoice_id=(int) $sicap_invoice_id;
	
	$sql = "
		select load_handler.*,
			(select sum(loaded_miles_hourly) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id) as tot_hourly_miles,
			(select actual_fuel_surcharge_per_mile * (select sum(loaded_miles_hourly) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id)) as fuel_expense_hourly,
			customers.name_company,
			customers.email_invoice,
			customers.invoice_discount_percent,
			customers.flat_fuel_surchage_override
		
		from load_handler, customers
		where load_handler.id = '".sql_friendly($load_id)."'
			and load_handler.customer_id = customers.id
			
	";
	$data = simple_query($sql);
	$row = mysqli_fetch_array($data);
	
	$edi_miles_override=0;
	if((int) $row['fedex_edi_invoice_mileage'] > 0)        $edi_miles_override=(int) $row['fedex_edi_invoice_mileage'];
     if((int) $row['lynnco_edi_invoice_mileage'] > 0)       $edi_miles_override=(int) $row['lynnco_edi_invoice_mileage'];
	
	$test_cust=0;
		
	if($row['sicap_invoice_number'] != '' && $row['sicap_invoice_number'] != '0') 
	{
		echo "SICAP Invoice already created ($row[sicap_invoice_number])";
		return false;
	}
     
     $misc_detention=$row['misc_detention'];
	
	$def_load_route=trim("(From) ".$row['origin_city'].", ".$row['origin_state']." (To) ".$row['dest_city'].", ".$row['dest_state']."");
	//$def_load_route="";
	
	$alt_fuel=0;
	if($row['flat_fuel_surchage_override'] > 0)		$alt_fuel=$row['flat_fuel_rate_amount'];
	//$row['actual_bill_customer']+=$alt_fuel;
	
	
	// get any variable expenses
	$sql = "
		select lhave.*,
			option_values.fvalue as expense_type_value,
			option_values.fname as expense_type
		
		from load_handler_actual_var_exp lhave
			left join option_values on option_values.id = lhave.expense_type_id
		where load_handler_id = '".sql_friendly($load_id)."'
			and expense_amount > 0
		order by expense_amount desc
	";
	$data_expenses = simple_query($sql);
	
	$test_cust=$row['customer_id'];	
	if($test_cust==1601 || $test_cust==2233)	
	{
		$customer_id=1816;			//Carlex Glass C/O nVision shuttle runs and timesheets now go through CT Logistics as well.  No word yet on Nashville vs Lebanon loads. 3/14/2019...MRR
	}
	elseif($test_cust==2031)	
	{
		$customer_id=1788;			//LynnCo now has its own billing again... so as of 4/12, goes back to LynnCo for billing/invoices.  4/11/2022...MRR
          //$customer_id=1816;            //LynnCo linked to Carlex, but invoices through CT Logistics.   3/04/2019...MRR
	}
	else
	{
		$customer_id = $api->getCustomerIDByName($row['name_company']);
	}
	
	// get all the dispatches for this load - each dispatch will be a separate invoice line item
	$sql = "
		select trucks_log.*,
			trucks.name_truck
		
		from trucks_log, trucks
		where trucks_log.load_handler_id = '".sql_friendly($load_id)."'
			and trucks_log.deleted = 0
			and trucks.id = trucks_log.truck_id
		order by trucks_log.linedate_pickup_eta asc
	";
	//and cost + profit > 0
	$data_dispatch = simple_query($sql);
	
	
	$api->clearParams();
	
	// get the last stop of the load - as that will be our ship-to address
	$sql = "
		select *
		
		from load_handler_stops
		where load_handler_id = '".sql_friendly($load_id)."'
			and deleted=0
		order by linedate_pickup_eta desc, id desc
		limit 1
	";
	$data_last_stop = simple_query($sql);
	$row_last_stop = mysqli_fetch_array($data_last_stop);
	
	
	// get the first stop of the load - as that will be our invoice ship date
	$sql = "
		select *
		
		from load_handler_stops
		where load_handler_id = '".sql_friendly($load_id)."'
			and deleted=0
		order by linedate_pickup_eta, id
		limit 1
	";
	$data_first_stop = simple_query($sql);
	$row_first_stop = mysqli_fetch_array($data_first_stop);
	
	if($sicap_invoice_id <= 0) 
	{
		$api->addParam("ShippingName",$row_last_stop['shipper_name']);
		$api->addParam("ShippingAddress1",$row_last_stop['shipper_address1']);
		$api->addParam("ShippingAddress2",$row_last_stop['shipper_address2']);
		$api->addParam("ShippingCity",$row_last_stop['shipper_city']);
		$api->addParam("ShippingState",$row_last_stop['shipper_state']);
		$api->addParam("ShippingZip",$row_last_stop['shipper_zip']);		
	}
	
	
	$counter = 0;
	$loaded_miles = 0;
	$loaded_miles_hr = 0;
	$base_rate_desc = "Base Rate - Load ID: $load_id - ".$def_load_route."";
	$inv_id = 0;
	
	$make_total=0;
	
	$starting_local="";
	$ending_local="";
	
	while($row_dispatch = mysqli_fetch_array($data_dispatch)) 
	{
		$loaded_miles += $row_dispatch['miles'];
		$loaded_miles_hr += $row_dispatch['loaded_miles_hourly'];
		//d(time());
		//$api_item_id->show_output = true;
		if(!$inv_id) $inv_id = $api_item_id->getInventoryIDByName($row_dispatch['name_truck']);
		
		//if($starting_local=="")		$starting_local=trim("(From:) $row_dispatch[origin], $row_dispatch[origin_state]");
		//$ending_local="(To:) $row_dispatch[destination], $row_dispatch[destination_state]";
		/*
		$counter++;
		$api->addParam("Item_".$counter."_ID", $inv_id);
		//$api->addParam("Item_".$counter."_Price", $row_dispatch['cost'] + $row_dispatch['profit']);
		$api->addParam("Item_".$counter."_Price", 0);
		$api->addParam("Item_".$counter."_Qty", 1);
		$api->addParam("Item_".$counter."_QtyShipped", 1);
		$api->addParam("Item_".$counter."_Desc", "");
		*/
		//$base_rate_desc .= "Dispatch: $row_dispatch[id] (From:) $row_dispatch[origin], $row_dispatch[origin_state] (To:) $row_dispatch[destination], $row_dispatch[destination_state]. ";
	}
	//$base_rate_desc.=" ".$starting_local."".$ending_local."";
	
	while($row_expense = mysqli_fetch_array($data_expenses)) 
	{
		$counter++;
		$api->addParam("Item_".$counter."_ID", $inv_id);
		$api->addParam("Item_".$counter."_Price", $row_expense['expense_amount']);
		$api->addParam("Item_".$counter."_Qty", 1);
		$api->addParam("Item_".$counter."_QtyShipped", 1);
		if($row_expense['expense_type'] == 'base_rate') {
			$api->addParam("Item_".$counter."_Desc", $base_rate_desc);
		} else {
			$api->addParam("Item_".$counter."_Desc", "Load: $load_id - $row_expense[expense_type_value] - $".money_format('', $row_expense['expense_amount'])."");
		}
		
		
		$make_total+=$row_expense['expense_amount'];
	}
	
	if($misc_detention > 0)
     {
          $counter++;
          $api->addParam("Item_".$counter."_ID", 2617);
          $api->addParam("Item_".$counter."_Price", $misc_detention);
          $api->addParam("Item_".$counter."_Qty", 1);
          $api->addParam("Item_".$counter."_QtyShipped", 1);
          
          $api->addParam("Item_".$counter."_Desc", "Load: $load_id - Detention  $".money_format('', $misc_detention)."");
               
          $make_total+=$misc_detention;          
     }
	
	if($row['flat_fuel_surchage_override'] > 0) 
	{
		// there's a flat rate fuel surcharge, so put an entry for it
		$counter++;
		$total_fuel_surcharge = $alt_fuel;
		$api->addParam("Item_".$counter."_ID", $inv_id);
		$api->addParam("Item_".$counter."_Price", $total_fuel_surcharge);
		$api->addParam("Item_".$counter."_Qty", 1);
		$api->addParam("Item_".$counter."_QtyShipped", 1);
		$api->addParam("Item_".$counter."_Desc", "Load: $load_id - Flat Rate Fuel Surcharge $".number_format($alt_fuel,0).".");
		
		$make_total+=$total_fuel_surcharge;
	}
	
	if($row['actual_fuel_surcharge_per_mile'] > 0 && ($loaded_miles + $loaded_miles_hr) > 0) 
	{	// || ($row['fuel_expense_hourly'] > 0 && $row['tot_hourly_miles'] > 0)
				
		// there's a fuel surcharge, so put an entry for it
		$counter++;
		$total_fuel_surcharge = ($loaded_miles + $loaded_miles_hr) * $row['actual_fuel_surcharge_per_mile'];
		/*
		if($row['fuel_expense_hourly'] > 0 && $row['tot_hourly_miles'] > 0)
		{
			if($loaded_miles > 0)
			{	//add to loaded miles
				$total_fuel_surcharge+=$row['fuel_expense_hourly'];
				$loaded_miles+=$row['tot_hourly_miles'];
			}
			else
			{	//this is only hourly miles...
				$total_fuel_surcharge=$row['fuel_expense_hourly'];
				$loaded_miles=$row['tot_hourly_miles'];
			}
		}
		*/
		if($edi_miles_override > 0)
          {
               $total_fuel_surcharge = $edi_miles_override * $row['actual_fuel_surcharge_per_mile'];
     
               $api->addParam("Item_".$counter."_ID", $inv_id);
               $api->addParam("Item_".$counter."_Price", $total_fuel_surcharge);
               $api->addParam("Item_".$counter."_Qty", 1);
               $api->addParam("Item_".$counter."_QtyShipped", 1);
               $api->addParam("Item_".$counter."_Desc", "Load: $load_id - Fuel Surcharge ".$edi_miles_override." miles @ $".money_format('', $row['actual_fuel_surcharge_per_mile'])." per mile");     
          }
		else
          {
               $api->addParam("Item_".$counter."_ID", $inv_id);
               $api->addParam("Item_".$counter."_Price", $total_fuel_surcharge);
               $api->addParam("Item_".$counter."_Qty", 1);
               $api->addParam("Item_".$counter."_QtyShipped", 1);
               $api->addParam("Item_".$counter."_Desc", "Load: $load_id - Fuel Surcharge ".number_format(($loaded_miles + $loaded_miles_hr),0)." miles @ $".money_format('', $row['actual_fuel_surcharge_per_mile'])." per mile");     
          }
		
		//$api->addParam("Item_".$counter."_ID", $inv_id);
		//$api->addParam("Item_".$counter."_Price", $total_fuel_surcharge);
		//$api->addParam("Item_".$counter."_Qty", 1);
		//$api->addParam("Item_".$counter."_QtyShipped", 1);
		//$api->addParam("Item_".$counter."_Desc", "Load: $load_id - Fuel Surcharge ".number_format(($loaded_miles + $loaded_miles_hr),0)." miles @ $".money_format('', $row['actual_fuel_surcharge_per_mile'])." per mile");
		
		$make_total+=$total_fuel_surcharge;
	}
	
	if($row['invoice_discount_percent']!=0)
	{
		$discount_rate_amnt=($make_total * $row['invoice_discount_percent'] * -1);	
		// there's a fuel surcharge, so put an entry for it
		$counter++;
		$api->addParam("Item_".$counter."_ID", 78);		//Generic Discounts
		$api->addParam("Item_".$counter."_Price", $discount_rate_amnt);
		$api->addParam("Item_".$counter."_Qty", 1);
		$api->addParam("Item_".$counter."_QtyShipped", 1);
		$api->addParam("Item_".$counter."_Desc", "Customer Discount ".number_format(($row['invoice_discount_percent']*100),2)."% = $".number_format($discount_rate_amnt,2).".");		
	}
	
	$api->addParam("PickUp", $row['pickup_number']);
	$api->addParam("ItemCount", $counter);	
	
	if(date("m") != date("m", strtotime($row_first_stop['linedate_pickup_eta']))) {
		$use_invoice_date = date("m/t/Y", strtotime($row_first_stop['linedate_pickup_eta']));
	} else {
		$use_invoice_date = date("m/d/Y");
	}
     
     //$use_invoice_date = date("m/d/Y",time());         //updated this on Jan 20, 2021 for Patti since the dates were getting mixed up when the date entered is less than the actual creation date.  MRR
     	
	if($sicap_invoice_id > 0) 
	{
		$api->addParam("InvoiceID", $sicap_invoice_id);
	} 
	else 
	{
		//$api->addParam("InvoiceDate", $use_invoice_date);
          $api->addParam("InvoiceDate", date("m/d/Y", strtotime($row_first_stop['linedate_pickup_eta'])));
		$api->addParam("InvoiceDateCustomer", date("m/d/Y"));
		$api->addParam("ShipDate", date("m/d/Y", strtotime($row_first_stop['linedate_pickup_eta'])));
		$api->addParam('CustomerID',$customer_id);
		if($row['email_invoice']) 
		{
			$api->addParam('ToBeEmailed',1);
			$api->addParam('ToBePrinted',0);
		} 
		else 
		{
			$api->addParam('ToBePrinted',1);
			$api->addParam('ToBeEmailed',0);
		}
		$api->addParam('CustomerPO',$row['load_number']);
	}
	
	$api->command = "update_invoice";
	
	if($_SERVER['REMOTE_ADDR'] == '70.88.3.201') 
	{
		//$api->debug_post = true;
		//$api->show_output = true;
	}
	
	$rslt = $api->execute();
	
	if($sicap_invoice_id > 0)
	{
		$sql = "
     		update load_handler set
     		 	sicap_invoice_amount = '".sql_friendly($make_total)."',
     		 	sicap_invoice_number = '".sql_friendly($sicap_invoice_id)."',
     			invoice_number = '".sql_friendly($sicap_invoice_id)."',
     			linedate_invoiced = '".date("Y-m-d", strtotime($use_invoice_date))."'
     		where id = '".sql_friendly($load_id)."'
     	";
     	simple_query($sql);
	}
	else
	{	
     	$sql = "
     		update load_handler set
     		 	sicap_invoice_amount = '".sql_friendly($make_total)."',
     		 	sicap_invoice_number = '".sql_friendly($rslt->InvoiceNumber)."',
     			invoice_number = '".sql_friendly($rslt->InvoiceNumber)."',
     			linedate_invoiced = '".date("Y-m-d", strtotime($use_invoice_date))."'
     		where id = '".sql_friendly($load_id)."'
     	";
     	simple_query($sql);
	}
	return $rslt;
}

function sicap_get_invoices_by_date($date_from, $date_to) {
	
	$api = new sicap_api_connector();
	$api->clearParams();
	$api->command = "get_invoices_by_date";
	$api->addParam("DateFrom", $date_from);
	$api->addParam("DateTo",$date_to);
	//$api->show_output = true;
	$rslt = $api->execute();
	
	return $rslt;
}

function mrr_unpack_bill_details($bill_header,$bill_items)
{
	
     $api = new sicap_api_connector();
	$api->clearParams();
	
	$api->command = "create_bill_from_payroll";
	
	$api->addParam("billHeaderVendor", $bill_header['vendor']);
	$api->addParam("billHeaderMemo", $bill_header['memo']);
	$api->addParam("billHeaderRefer", $bill_header['reference_number']);
	$api->addParam("billHeaderItems", $bill_header['items']);
	$api->addParam("billHeaderDate", $bill_header['bill_date']);
	
	if($bill_header['bridge_vendor_id'] > 0)
	{
		$api->addParam("bridgeEmployerVendor", $bill_header['bridge_vendor_id']);	
	}
	
	for($i=0; $i< $bill_header['items']; $i++)
	{
		$api->addParam("billItem". $i ."Acct", $bill_items[ $i ]['account_name']);
		$api->addParam("billItem". $i ."Amount", $bill_items[ $i ]['amount']);
		$api->addParam("billItem". $i ."Memo", $bill_items[ $i ]['memo']);
	}
	//$api->show_output = true;
	$rslt = $api->execute();	
	return $rslt;	
}

function mrr_fetch_comparison_data($sql)
{	//function 
	$api = new sicap_api_connector();
	$api->clearParams();
	
	$api->command = "mrr_comparison_query";
	
	$api->addParam("comparisonSQL", $sql);
	
	//$api->show_output = true;
	$rslt = $api->execute();			// result is in this format....  "<rslt>1</rslt><Comparison>".$rval."</Comparison>"
	return $rslt;
}

function mrr_fetch_comparison_data_alt($mode,$date_from,$date_to,$coa_from,$coa_to)
{	//function 
	$api = new sicap_api_connector();
	$api->clearParams();
	
	$api->command = "mrr_comparison_query_alt";
	
	$api->addParam("mode", $mode);
	$api->addParam("date_from", $date_from);
	$api->addParam("date_to", $date_to);
	$api->addParam("coa_from", $coa_from);
	$api->addParam("coa_to", $coa_to);
	
	//if($mode==10)	$api->show_output = true;
	$rslt = $api->execute();			// result is in this format....  "<rslt>1</rslt><Comparison>".$rval."</Comparison>"
	return $rslt;	
}

function mrr_get_coa_list($chart_id,$chart_number)
{
	$chart_number=trim($chart_number);
	// get chart list...using chart _id to select only one or chart_number to select a group of accounts by number
	$api = new sicap_api_connector();
	
	$api->clearParams();
	$api->command = "get_chart_of_accounts";
	if($chart_id > 0) 		$api->addParam("searchChartID", $chart_id);
	if($chart_number!="")	$api->addParam("searchChartNumber", $chart_number);
	//if($chart_id==0 && $chart_number=="")
	//	$api->show_output = true;
	
	$rslt = $api->execute();			// result is in this format....  "<rslt>1</rslt><Comparison>".$rval."</Comparison>"
	
	return $rslt;	
}

function mrr_get_vendor_bill_list($vendor_name,$date_from,$date_to,$bridge_vendor_id=0)
{
	$api = new sicap_api_connector();
	
	$api->clearParams();
	
	$api->command = "mrr_bill_hunter_query";
	
	$api->addParam("vendor_name", $vendor_name);
	$api->addParam("date_from", $date_from);
	$api->addParam("date_to", $date_to);
	
	if($bridge_vendor_id > 0)
	{
		$api->addParam("bridgeEmployerVendor", $bridge_vendor_id);	
	}
	
	//$api->show_output = true;
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;	
}

function mrr_get_ar_summary_info($customer_id,$customer_name,$date_from,$date_to)
{
	$api = new sicap_api_connector();
	
	$api->clearParams();
	
	$api->command = "mrr_get_ar_summary_info";
	
	$api->addParam("cust_id", $customer_id);
	$api->addParam("cust_name", $customer_name);
	//$api->addParam("date_from", $date_from);
	$api->addParam("date_to", $date_to);
	//$api->show_output = true;
	if($_SERVER['REMOTE_ADDR'] == '70.90.229.29') {
		//$api->debug_post = true;
	}
	
	

	$rslt = $api->execute();			// result is in this format.... 
	

	
	return $rslt;	
}

function mrr_get_load_invoice_info($invoice_id,$load_id)
{
	$api = new sicap_api_connector();
	
	$api->clearParams();
	
	$api->command = "mrr_get_api_invoice_info";
	
	$api->addParam("searchID", $invoice_id);
	$api->addParam("loadID", $load_id);
	//$api->show_output = true;
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;	
}

function mrr_get_ar_summary_detail_info($customer_id,$customer_name,$date_from,$date_to,$aging_from,$aging_to)
{
	$api = new sicap_api_connector();
	
	$api->clearParams();
	
	$api->command = "mrr_get_ar_summary_detail_info";
	
	$api->addParam("cust_id", $customer_id);
	$api->addParam("cust_name", $customer_name);
	//$api->addParam("date_from", $date_from);
	$api->addParam("date_to", $date_to);
	
	$api->addParam("aging_from", $aging_from);
	$api->addParam("aging_to", $aging_to);
	
	//$api->show_output = true;
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;	
}
function mrr_get_ar_summary_detail_info_v2($customer_id,$customer_name,$date_from,$date_to,$aging_from,$aging_to)
{
	$api = new sicap_api_connector();
	
	$api->clearParams();
	
	$api->command = "mrr_get_all_ar_summary_detail_info";
	
	$api->addParam("cust_id", $customer_id);
	$api->addParam("cust_name", $customer_name);
	//$api->addParam("date_from", $date_from);
	$api->addParam("date_to", $date_to);
	
	$api->addParam("aging_from", $aging_from);
	$api->addParam("aging_to", $aging_to);
	
	//$api->show_output = true;
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;	
}

function mrr_get_cust_average_payment($invoice_id)
{
	$api = new sicap_api_connector();
	
	$api->clearParams();
	
	$api->command = "mrr_get_customer_invoice_average_payment";
	
	$api->addParam("invoice_id", $invoice_id);
	
	//$api->show_output = true;
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;	
}

function mrr_get_cust_has_paid($invoice_id)
{
	$api = new sicap_api_connector();
	
	$api->clearParams();
	
	$api->command = "mrr_get_customer_invoice_paid";
	
	$api->addParam("invoice_id", $invoice_id);
	
	//$api->show_output = true;
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;	
}
function mrr_get_all_cust_has_paid($customer_id,$alt_mode=0)
{
	$api = new sicap_api_connector();
	
	$api->clearParams();
	
	$api->command = "mrr_get_all_customer_invoice_paid";
	
	$api->addParam("customer_id", $customer_id);
	$api->addParam("alt_mode", $alt_mode);
	
	//$api->show_output = true;
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;	
}

function mrr_sicap_remove_invoice_line_item($load_id=0, $invoice_id=0) 
{
	
	$api = new sicap_api_connector();
	$api->clearParams();
	$api->command = "mrr_remove_invoice_line_item";
	$api->addParam("load_id", $load_id);
	$api->addParam("invoice_id",$invoice_id);
	//$api->show_output = true;
	$rslt = $api->execute();
	
	return $rslt;
}

function mrr_sicap_get_accounts_for_truck_name($truck_name="", $max_coa=0) 
{
	
	$api = new sicap_api_connector();
	$api->clearParams();
	$api->command = "mrr_get_truck_chart_of_accounts";
	$api->addParam("truck_name", $truck_name);
	$api->addParam("maxResults",$max_coa);
	//$api->show_output = true;
	$rslt = $api->execute();	
	return $rslt;
}
function mrr_sicap_get_accounts_for_trailer_name($trailer_name="", $max_coa=0) 
{
	
	$api = new sicap_api_connector();
	$api->clearParams();
	$api->command = "mrr_get_trailer_chart_of_accounts";
	$api->addParam("trailer_name", $trailer_name);
	$api->addParam("maxResults",$max_coa);
	//$api->show_output = true;
	$rslt = $api->execute();	
	return $rslt;
}
function mrr_update_truck_chart_names($id=0, $truck_name="") 
{
	$api = new sicap_api_connector();
	$api->clearParams();
	$api->command = "mrr_update_truck_chart_names";
	$api->addParam("coa_name", $truck_name);
	$api->addParam("coa_id",$id);
	//$api->show_output = true;
	$rslt = $api->execute();
	
	return $rslt;
}

function mrr_get_all_vendors() {
	// fetch vendors in use
	$api = new sicap_api_connector();
	
	$api->clearParams();
	$api->command = "get_vendors";
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;
}
function mrr_get_a_vendor($vendor_id) {
	// fetch one vendor by ID
	$api = new sicap_api_connector();
	
	$api->clearParams();
	$api->command = "get_vendors";
	$api->addParam("searchID",$vendor_id);
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;
}

function mrr_get_peachtree_import_name($first,$last) 
{
	// fetch one user's import_name by first and last name
	$api = new sicap_api_connector();
	
	$api->clearParams();
	$api->command = "api_get_peachtree_user";
	$api->addParam("firstName",$first);
	$api->addParam("lastName",$last);
	$rslt = $api->execute();			// result is in this format.... 
	return $rslt;
}

function mrr_get_sicap_maint_invoice($id)
{
	$res['invoice']=0;
	$res['date']="";
	$res['user']="";
	$res['user_id']=0;
	$res['cust']="";
	$res['cust_id']=0;	
	$res['labor']="0.00";
	$res['markup']="0.00";
	
	$sql="
		select sicap_invoice_id,
			sicap_invoice_markup_rate,
			sicap_invoice_labor_rate,
			customer_id,
			linedate_invoiced,
			sicap_invoice_user_id,
			(select name_company from customers where customers.id=maint_requests.customer_id) as inv_cust,
			(select username from users where users.id=maint_requests.sicap_invoice_user_id) as inv_user
		from maint_requests
		where id='".sql_friendly($id)."'
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$res['invoice']=$row['sicap_invoice_id'];
		$res['date']=$row['linedate_invoiced'];
		$res['user']=$row['inv_user'];
		$res['user_id']=$row['sicap_invoice_user_id'];
		$res['cust']=$row['inv_cust'];
		$res['cust_id']=$row['customer_id'];
		$res['labor']=number_format($row['sicap_invoice_labor_rate'],2);
		$res['markup']=number_format($row['sicap_invoice_markup_rate'],2);
	}
	return $res; 	
}
function mrr_set_sicap_maint_invoice($id,$inv_id,$cust_id)
{
	global $defaultsarray;
	$labor_rate=floatval($defaultsarray['maint_labor_rate']);
	$markup_val=floatval($defaultsarray['maint_invoice_markup']);
	
	$sql="
		update maint_requests set
			sicap_invoice_id='".sql_friendly($inv_id)."',
			customer_id='".sql_friendly($cust_id)."',
			sicap_invoice_markup_rate='".sql_friendly($markup_val)."',
			sicap_invoice_labor_rate='".sql_friendly($labor_rate)."',
			linedate_invoiced=NOW(),
			sicap_invoice_user_id='".sql_friendly($_SESSION['user_id'])."'
		where id='".sql_friendly($id)."'
	";
	simple_query($sql);
}
function mrr_kill_sicap_maint_invoice($id)
{
	$sql="
		update maint_requests set
			sicap_invoice_id='0',
			customer_id='0',
			linedate_invoiced=NOW(),
			sicap_invoice_user_id='".sql_friendly($_SESSION['user_id'])."'
		where id='".sql_friendly($id)."'
	";
	simple_query($sql);
}


function mrr_get_sicap_timesheet_invoice($id)
{
	$res['invoice']=0;
	$res['date']="";
		
	$sql="
		select invoice_id,
			linedate_invoiced
		from timesheets
		where id='".sql_friendly($id)."'
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$res['invoice']=$row['invoice_id'];
		$res['date']=$row['linedate_invoiced'];
	}
	return $res; 	
}
function mrr_set_sicap_timesheet_invoice($id,$inv_id)
{
	$sql="
		update timesheets set
			invoice_id='".sql_friendly($inv_id)."',
			linedate_invoiced=NOW()
		where id='".sql_friendly($id)."'
	";
	simple_query($sql);
}
function mrr_kill_sicap_timesheet_invoice($id)
{
	$sql="
		update timesheets set
			invoice_id='0',
			linedate_invoiced=NOW()
		where id='".sql_friendly($id)."'
	";
	simple_query($sql);
}


function mrr_get_sicap_trailer_invoice($id)
{
	$res['invoice']=0;
	$res['date']="";
		
	$sql="
		select invoice_id,
			linedate_invoiced
		from trailers_dropped
		where id='".sql_friendly($id)."'
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$res['invoice']=$row['invoice_id'];
		$res['date']=$row['linedate_invoiced'];
	}
	return $res; 	
}
function mrr_set_sicap_trailer_invoice($id,$inv_id)
{
	$sql="
		update trailers_dropped set
			invoice_pending=0,
			invoice_id='".sql_friendly($inv_id)."',
			linedate_invoiced=NOW()
		where id='".sql_friendly($id)."'
	";
	simple_query($sql);
}
function mrr_set_sicap_trailer_invoice_all($inv_id)
{
	$sql="
		update trailers_dropped set
			invoice_pending=0,
			invoice_id='".sql_friendly($inv_id)."',
			linedate_invoiced=NOW()
		where invoice_pending>0 
			and deleted=0
	";
	simple_query($sql);
}
function mrr_kill_sicap_trailer_invoice($id)
{
	$sql="
		update trailers_dropped set
			invoice_id='0',
			linedate_invoiced=NOW()
		where id='".sql_friendly($id)."'
	";
	simple_query($sql);
}
function mrr_kill_sicap_trailer_invoice_all($inv_id)
{
	$sql="
		update trailers_dropped set
			invoice_id='0',
			linedate_invoiced=NOW()
		where invoice_id='".sql_friendly($inv_id)."'
	";
	simple_query($sql);
}
?>