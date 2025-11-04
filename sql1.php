<?
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('max_execution_time',6000);
?>
<? include('application.php')?>
<?
phpinfo();
die;
$use_load_id = 8847;		//7693  or 7726...dispatches will be pulled dynamically
if(isset($_GET['load_id']))	$use_load_id = $_GET['load_id'];	

$disp_date_from="2012-01-01 00:00:00";
$disp_date_to  ="2012-03-31 23:59:59";
$disp_cntr=0;
$disp_ids[0]=0;

$header="
<tr>
	<td valign='top'>Disp</td>
	<td valign='top'>Date Added</td>
	<td valign='top' align='right'>Truck</td>
	<td valign='top' align='right'>Trailer</td>
	<td valign='top' align='right'>Driver</td>
	<td valign='top' align='right'>Driver2</td>
	<td valign='top' align='right'>Days</td>
	<td valign='top' align='right'>Miles</td>
	<td valign='top' align='right'>Fuel</td>
	<td valign='top' align='right'>Insurance</td>
	<td valign='top' align='right'>Labor</td>
	<td valign='top' align='right'>Truck Maint</td>
	<td valign='top' align='right'>Tires</td>
	<td valign='top' align='right'>Trailer Maint </td>
	<td valign='top' align='right'>Truck Lease </td>
	<td valign='top' align='right'>Mileage Exp</td>
	<td valign='top' align='right'>Admin Exp </td>
	<td valign='top' align='right'>Misc. Exp</td>
	<td valign='top' align='right'>Trailer Rental</td>
	<td valign='top' align='right'>Accidents</td>
	<td valign='top' align='right'>Daily Cost</td>
	<td valign='top' align='right'>DayCstMRR</td>
	<td valign='top' align='right'>Dispatch</td>
	<td valign='top' align='right'>DayCst Diff</td>
	<td valign='top' align='right'>Calc Total </td>
	<td valign='top' align='right'>Difference</td>	
</tr>
";

//get dispatch list...
$disp_lister="";
$sql = "
	select trucks_log.id	
	from trucks_log, load_handler
	where trucks_log.load_handler_id = '".sql_friendly($use_load_id)."'
		and load_handler.id = trucks_log.load_handler_id
		and trucks_log.deleted=0
		and trucks_log.dispatch_completed = 1
";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data))
{
	$disp_ids[  $disp_cntr  ]=$row['id'];	
	$disp_lister.=" - <b><a href='add_entry_truck.php?load_id=".$use_load_id."&id=".$row['id']."' target='_blank'>".$row['id']."</a></b> ";
	$disp_cntr++;
}

//get load details
echo "<span style='color:#aaa'>Load ID: <a href='manage_load.php?load_id=".$use_load_id."' target='_blank'>".$use_load_id."</a></span><br>";
echo "<span style='color:#aaa'>Dispatches: ".$disp_lister."</span><br>";

$sql = "
	select load_handler.*,
		customers.name_company,
		load_handler.actual_bill_customer - load_handler.actual_total_cost as load_profit,
		(select ifnull(sum(trucks_log.loaded_miles_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as loaded_miles_hourly,
		(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
		(select ifnull(sum(trucks_log.driver2_id),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as driver_cnt,
		(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead			
	from load_handler
		left join customers on customers.id = load_handler.customer_id
	where load_handler.deleted = 0
		and customers.deleted = 0			
		and load_handler.id = '".sql_friendly($use_load_id)."'
	order by load_handler.id
";
$data = simple_query($sql);
$row = mysqli_fetch_array($data);

echo "<span style='color:#aaa'>Actual Load Cost (Stored): ".$row['actual_total_cost']."</span><br>";
echo "<span style='color:#aaa'>Load Pickup Date: ".date("m/d/Y H:i", strtotime($row['linedate_pickup_eta']))."</span><br>";


$mrr_miles=$row['miles'] + $row['miles_deadhead'];
if($mrr_miles==0)	$mrr_miles=$row['loaded_miles_hourly'];		

if($row['budget_average_mpg'] > 0)
{
	$mrr_fuel=($mrr_miles) * number_format($row['actual_rate_fuel_surcharge'] / $row['budget_average_mpg'],2,'.','');
}
else
{
	$mrr_fuel=($mrr_miles) * number_format($row['actual_rate_fuel_surcharge'] / $defaultsarray['average_mpg'],2,'.','');
}

$drun_tot=0;
$miles_tot=0;
$fuel_tot=0;
$ins_tot=0;
$labor_tot=0;
$truckm_tot=0;
$tires_tot=0;
$trailm_tot=0;
$truckl_tot=0;
$mileage_tot=0;
$admin_tot=0;
$misc_tot=0;
$trailr_tot=0;
$accident_tot=0;
$dc_tot=0;
$dc_tot2=0;
$exp_tot=0;
$dc_diff_tot=0;
$gtotal=0;
$diff_tot=0;

$hours_tot=0;

$tab_row_list="";

for($i=0;$i < $disp_cntr; $i++)
{
	$fuel_charge = 0;
	$total = 0;
	$use_dispatch_id=$disp_ids[ $i ];
	
	$disp_header="DISPATCH:".$use_dispatch_id." -- ";
	
	echo "<hr>";
	echo "<hr>";
		
	echo "<span style='color:#aaa'>Dispatch ID: ".$use_dispatch_id."</span><br>";
	if($use_dispatch_id > 0)
     {
     	$sql = "
     		select trucks_log.*,
     			load_handler.actual_fuel_charge_per_mile
     		
     		from trucks_log, load_handler
     		where trucks_log.id = '".sql_friendly($use_dispatch_id)."'
     			and load_handler.id = trucks_log.load_handler_id
     			and trucks_log.load_handler_id='".sql_friendly($use_load_id)."'
     			and trucks_log.deleted=0
     			and trucks_log.dispatch_completed = 1
     	";
     	$data_extra = simple_query($sql);
     	$row_extra = mysqli_fetch_array($data_extra);     
     
          echo "Daily Cost: $row_extra[daily_cost]<br>";
          echo "".$disp_header."{current daily cost: ".get_daily_cost($row_extra['truck_id'], $row_extra['trailer_id'])."}<br>";
                    
          $dispatch_cost = get_dispatch_cost($use_dispatch_id);
          
          $rslt = mrr_quick_and_easy_budget_maker($row,$disp_date_from,$disp_date_to,$use_dispatch_id);
          
          //$rslt['fuel'] = $mrr_fuel;                    
          
          //$mrr_res['daily_cost']
          
          $added_val=$rslt['insur'] + $rslt['truck_lease'] + $rslt['admin_exp'] + $rslt['trailer_rental'];
          $dc_diff=$rslt['daily_cost'] - $added_val;
          
          $new_daily_cost = $rslt['insur'] + $rslt['truck_lease'] + $rslt['admin_exp'] + $rslt['trailer_rental'];	//$rslt['daily_cost']
          $new_maint = $rslt['truck_maint'] + $rslt['trailer_maint'];
          $tires_misc_other = $rslt['tires'] + $rslt['misc_exp'] + $rslt['accidents'] + $rslt['mileage_exp'];
          $tmp_total = $new_daily_cost + $new_maint + $rslt['fuel'] + $rslt['labor'] + $tires_misc_other;
          
          $difference = $dispatch_cost - $tmp_total;
          
          $tab_row="
          <tr>
          	<td valign='top'><a href='add_entry_truck.php?load_id=$use_load_id&id=$use_dispatch_id' target='_blank'>".$use_dispatch_id."</a></td>
          	<td valign='top'><span style='color:#aaa'>".date("m/d/Y H:i", strtotime($row_extra['linedate_added']))."</span></td>
          	<td valign='top'><a href='admin_trucks.php?id=".$row_extra['truck_id']."' target='_blank'>".$row_extra['truck_id']."</a></td>
          	<td valign='top'><a href='admin_trailers.php?id=".$row_extra['trailer_id']."' target='_blank'>".$row_extra['trailer_id']."</a></td>
          	<td valign='top'><a href='admin_drivers.php?id=".$row_extra['driver_id']."' target='_blank'>".$row_extra['driver_id']."</a></td>
          	<td valign='top'><a href='admin_drivers.php?id=".$row_extra['driver2_id']."' target='_blank'>".$row_extra['driver2_id']."</a></td>
          	<td valign='top' align='right'>".$rslt['days_run']."</td>
          	<td valign='top' align='right'>".$rslt['miles']."</td>
          ";
          
          
          foreach($rslt as $key => $value) {
          	if($key != 'daily_cost' && $key != 'dispatch_count' && $key != 'dispatch_list' && $key != 'sql' && $key != 'days_run' && $key != 'miles' && $key != 'expenses'
          						 && $key != 'pay_rate' && $key != 'pay_rate2' && $key != 'hours' && $key != 'fun_disp_cost') {
          		$total += $value;
          		echo "".$disp_header."($key): $value<br>";
          		$tab_row.="<td valign='top' align='right'>".number_format($value,3)."</td>";	//($key):
          	}
          }
          //$total+=$dc_diff;	//$rslt['expenses']
          
          $tab_row.="
          	<td valign='top' align='right'><span style='color:green;'>".number_format($rslt['daily_cost'],3)."</span></td>
          	<td valign='top' align='right'><span style='color:orange;'>".number_format($added_val,3)."</span></td>
     		<td valign='top' align='right'>".number_format($rslt['expenses'],3)."</td>
     		<td valign='top' align='right'><span style='color:orange;'>".number_format($dc_diff,3)."</span></td>
     		<td valign='top' align='right'>".number_format($total,3)."</td>
     		<td valign='top' align='right'><span style='color:red;'>".number_format($difference,2)."</span></td>
     	</tr>
          ";
          
          $tab_row_list.=$tab_row;
          
          echo "<table border='1'>";
          echo $header;
          echo $tab_row;          
     	echo "</table>";
     	echo "".$disp_header."Maint: $new_maint<br>";
     	echo "".$disp_header."Labor Rate Miles: $".number_format($rslt['pay_rate'],2)."<br>";
     	echo "".$disp_header."Labor Rate Hourly: $".number_format($rslt['pay_rate2'],2)."<br>";
     	echo "".$disp_header."HrsWrkd: ".$rslt['hours']."<br>";
     	echo "".$disp_header."DayCst Val: ".$added_val."<br>";
          echo "".$disp_header."Tires, misc, accidents, mileage: $tires_misc_other<br>";
          echo "<span style='color:#aaa'>".$disp_header."Dispatch Cost (MRR calculated): ".$tmp_total."</span><br>";
          echo "<span style='color:#aaa'>".$disp_header."Get Dispatch Cost (Chris function): ".$dispatch_cost."</span><br>";
          echo "".$disp_header."Difference: $difference<br>";
          echo "<br>";
                    
          $drun_tot+=$rslt['days_run'];
          $miles_tot+=$rslt['miles'];
          $fuel_tot+=$rslt['fuel'];
          $ins_tot+=$rslt['insur'];
          $labor_tot+=$rslt['labor'];
          $truckm_tot+=$rslt['truck_maint'];
          $tires_tot+=$rslt['tires'];
          $trailm_tot+=$rslt['trailer_maint'];
          $truckl_tot+=$rslt['truck_lease'];
          $mileage_tot+=$rslt['mileage_exp'];
          $admin_tot+=$rslt['admin_exp'];
          $misc_tot+=$rslt['misc_exp'];
          $trailr_tot+=$rslt['trailer_rental'];
          $accident_tot+=$rslt['accidents'];
          $dc_tot+=$rslt['daily_cost'];
          $dc_tot2+=$added_val;
          $exp_tot+=$rslt['expenses'];
          $dc_diff_tot+=$dc_diff;
          $gtotal+=$total;
          $diff_tot+=$difference;
     }
	
}
echo "<hr>";
echo "<hr>";
echo "<br><span style='color:#aaa'>Summary of Load ID: <a href='manage_load.php?load_id=".$use_load_id."' target='_blank'>".$use_load_id."</a></span><br>";
echo "<table border='1'>";
echo $header;

echo $tab_row_list;

echo "<tr>";
echo "<td valign='top'>Total</td>";
echo "<td valign='top' colspan='5'>".number_format($disp_cntr,3)." Load Dispatches</td>";
echo "<td valign='top' align='right'>".number_format($drun_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($miles_tot,3)."</td>";

echo "<td valign='top' align='right'>".number_format($fuel_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($ins_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($labor_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($truckm_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($tires_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($trailm_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($truckl_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($mileage_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($admin_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($misc_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($trailr_tot,3)."</td>";
echo "<td valign='top' align='right'>".number_format($accident_tot,3)."</td>";

echo "<td valign='top' align='right'><span style='color:green;'>".number_format($dc_tot,3)."</span></td>";
echo "<td valign='top' align='right'><span style='color:orange;'>".number_format($dc_tot2,3)."</span></td>";
echo "<td valign='top' align='right'>".number_format($exp_tot,3)."</td>";
echo "<td valign='top' align='right'><span style='color:orange;'>".number_format($dc_diff_tot,3)."</span></td>";
echo "<td valign='top' align='right'>".number_format($gtotal,3)."</td>";
echo "<td valign='top' align='right'><span style='color:red;'>".number_format($diff_tot,3)."</span></td>";
echo "</tr>";
echo "</table>";

$all_diff=number_format(($row['actual_total_cost'] - $gtotal),3);
//echo "<br>Grand Diff...".$all_diff.".";
die;
//..................................................................................................................................................

echo str_pad("7760", "9", "0", STR_PAD_LEFT);

//19838 -- 7760
//19840 -- 7782

die;

$rslt = sicap_update_customers('126');

echo $rslt->rslt;
echo "<br>".$rslt->rsltmsg;
die;

var_dump($rslt);

die('aborted...');

//truck update section
$sql = "
	select *
	
	from
	trucks
	where active = 0
		and deleted = 0
";
$data = simple_query($sql);

$api = new sicap_api_connector();
$income_id		= $api->getChartTypeIDByName("Cost of Sales");

while($row = mysqli_fetch_array($data)) {
	if(is_numeric($row['name_truck'])) {
		echo "$row[name_truck]<br>";
		
		$old_name="";
		$truck_name = $row['name_truck'];
		$truck_id = $row['id'];
		$rental = $row['rental'];
		//sicap_update_trucks($truck_id, $truck_name, false, $rental);
		update_coa("Truck Cleaning - #$truck_name", "74900-$truck_name", $income_id, ($old_name != '' ? "Truck Cleaning - #$old_name" : ""));
	}
}


//truck update section
$sql = "
	select *
	
	from
	trucks
	where active = 1
		and deleted = 0
";
$data = simple_query($sql);

$api = new sicap_api_connector();
//$income_id		= $api->getChartTypeIDByName("Income");		
$income_id		= $api->getChartTypeIDByName("Cost of Sales");	
$old_name = '';

while($row = mysqli_fetch_array($data)) {
	if(is_numeric($row['name_truck'])) {
		echo "$row[name_truck]<br>";
		
		$truck_name = $row['name_truck'];
		$truck_id = $row['id'];
		//sicap_update_trucks($row['id'], $truck_name, $quick_update = false, $rental = 0)
		
		
		
		//update_coa("Discount-Truck #$truck_name", "46000-$truck_name", $income_id, ($old_name != '' ? "Discount-Truck #$old_name" : ""));
		
		//update_coa("Truck Accidents - #$truck_name", "74000-$truck_name", $income_id, ($old_name != '' ? "Truck Accidents - #$old_name" : ""));
		update_coa("Truck Repairs - #$truck_name", "74700-$truck_name", $income_id, ($old_name != '' ? "Truck Repairs - #$old_name" : ""));
		$inventory_item_id = 0;
		
		$chart_id_sales = $api->getChartIDByName("Truck Repairs - #$truck_name");
		$chart_id_inv = $chart_id_sales;
		$chart_id_cost = $chart_id_sales;
		
		$api->clearParams();
		$api->command = "update_inventory_item";
		$api->addParam("ItemName", $truck_name."-Truck Repairs");
		$api->addParam("ItemID",$inventory_item_id);
		$api->addParam("ChartIDSales", $chart_id_sales);
		$api->addParam("Active", ($row['active'] ? 1 : 0));
		$api->addParam("ChartIDInventory", $chart_id_inv);
		$api->addParam("ChartIDCOGS", $chart_id_cost);
		//$api->show_output = true;
		$api->execute();
		
	}
}


die;
//var_dump($rslt);

//Trailer update section
$sql = "
	select *
	
	from
	trailers
	where active = 1
		and deleted = 0
";
$data = simple_query($sql);

$api = new sicap_api_connector();
//$income_id		= $api->getChartTypeIDByName("Income");		
$income_id		= $api->getChartTypeIDByName("Cost of Sales");	
$old_name = '';

while($row = mysqli_fetch_array($data)) {
	if(is_numeric($row['trailer_name'])) {
		echo "$row[trailer_name]<br>";
		
		$trailer_name = $row['trailer_name'];
		
		//update_coa("Trailer Accidents - #$trailer_name", "77485-$trailer_name", $income_id, ($old_name != '' ? "Trailer Accidents - #$old_name" : ""));
		update_coa("Trailer Repairs - #$trailer_name", "77550-$trailer_name", $income_id, ($old_name != '' ? "Trailer Repairs - #$old_name" : ""));
		$inventory_item_id = 0;
		
		$chart_id_sales = $api->getChartIDByName("Trailer Repairs - #$trailer_name");
		$chart_id_inv = $chart_id_sales;
		$chart_id_cost = $chart_id_sales;
		
		$api->clearParams();
		$api->command = "update_inventory_item";
		$api->addParam("ItemName", $trailer_name."-Trailer Repairs");
		$api->addParam("ItemID",$inventory_item_id);
		$api->addParam("ChartIDSales", $chart_id_sales);
		$api->addParam("Active", ($row['active'] ? 1 : 0));
		$api->addParam("ChartIDInventory", $chart_id_inv);
		$api->addParam("ChartIDCOGS", $chart_id_cost);
		//$api->show_output = true;
		$api->execute();
		
	}
}

die;

//........................

d("(".$rslt.")");

$xml = simplexml_load_string($rslt);
$json = json_encode($xml);
$array = json_decode($json,TRUE);

d($array);

die;

sicap_create_invoice(6867);
die;

sicap_create_invoice(6406);
die;

// actual_fuel_surcharge_per_mile
// 

$lid = 4561;

$use_control_number = str_pad($lid, 9, "0", STR_PAD_LEFT);

echo $use_control_number;
echo "<br>".strlen($use_control_number)."<br>";

die('aborted...');

$sql = "
	select *
	
	from customers
	where deleted = 0
	order by id
";
$data = simple_query($sql);

while($row = mysqli_fetch_array($data)) {
	sicap_update_customers($row['id']);
}

die('aborted...');

//////////////////////////////////////////
// duplicate customer check 
//////////////////////////////////////////
$sql = "
	select name_company
	
	from customers
	where deleted = 0
	group by name_company
	having count(name_company) > 1
";
$data = simple_query($sql);

while($row = mysqli_fetch_array($data)) {
	$sql = "
		select *
		
		from customers
		where deleted = 0
			and name_company = '".sql_friendly($row['name_company'])."'
	";
	$data_sub = simple_query($sql);
	
	while($row_sub = mysqli_fetch_array($data_sub)) {
		$sql = "
			select (select count(*) from load_handler where customer_id = '$row_sub[id]' and deleted = 0) as lh_count,
				(select count(*) from trucks_log where customer_id = '$row_sub[id]' and deleted = 0) as tl_count
			
		";
		$data_check = simple_query($sql);
		$row_check = mysqli_fetch_array($data_check);
		
		echo "$row_sub[id] - $row[name_company]";
		
		if($row_check['lh_count'] || $row_check['tl_count']) {
			echo " - ($row_check[lh_count] | $row_check[tl_count])";
		} else {
			echo " - Not used";
			$sql = "
				update customers
				set deleted = 1
				where id = '".$row_sub['id']."'
			";
			simple_query($sql);
		}
		echo "<br>";
	}
	//echo $row['name_company']."<br>";
}

//////////////////////////////////////////
// end of duplicate customer check
//////////////////////////////////////////

die;

$date = "20110706 2200";

echo date("m/d/Y h:i:00 a", strtotime($date));

die;


$_POST['ziplist'] = '37130,37205';
$_POST['hub_run'] = 0;

		$ziparray = explode(",",$_POST['ziplist']);
		
		$traveldist = 0;
		$travel_time = 0;
		$stoparray_dist = array();
		$stoparray_time = array();
		$stop_minutes = 0;
		try {
			$trip = $pcm->NewTrip("NA");
			
			$last_zip = "";
			$stop_counter = 0;
			$counter = 0;
			$first_stop = "";
			//$stop_minutes = 0;
			foreach($ziparray as $zip) {
				if($first_stop == '' && $zip != '') $first_stop = $zip;
				if($zip != '') {
					if($stop_counter) {
						if($_POST['hub_run'] == '1') {
							$stoparray_dist[$counter] = $pcm->CalcDistance2($first_stop, $zip, 0) / 10;
						} else {
							$stoparray_dist[$counter] = $pcm->CalcDistance2($last_zip, $zip, 0) / 10;
						}
						$stoparray_time[$counter] = $stop_minutes;
					}
					$trip->AddStop($zip);
					$last_zip = $zip;
					$stop_counter++;
				}
				$counter++;
			}
			
			$options = $trip->GetOptions();
			$options->RouteType = 0;
			$options->Hub = ($_POST['hub_run'] == '1' ? true : false);
			
			$traveldist = $trip->TravelDistance() / 10;
			$travel_time = $trip->TravelTime() / 60.0;
			//$html_report = $trip->GetReport(2);
			
			$rslt = 1;
		} catch (Exception $e) {
			$rslt = 0;
			echo "error";
			echo print_r($e);
		}
		
echo "<br><br>";
print_r($stoparray_dist);
echo "<br><br>";
print_r($stoparray_time);
echo "done";

die();



//sicap_update_trucks(0, '', true);
//sicap_update_trucks();
sicap_update_trailers();

//echo $defaultsarray['sicap_integration'];
die;

sicap_create_invoice(4225);


die;

echo "<br><br><br><br>";

//update_coa('Truck Rental - Fixed - #369004','Truck Rental - Fixed - #369004','77950-369004','18');

	$api = new sicap_api_connector();
	
	$truck_name = "4332";
	$chart_id_sales = $api->getChartIDByName("Income-Truck #$truck_name");
	$chart_id_inv = $api->getChartIDByName("Income-Truck #$truck_name");
	$chart_id_cost = $api->getChartIDByName("Income-Truck #$truck_name");
	
	$api->clearParams();
	$api->command = "update_inventory_item";
	$api->addParam("ItemName", $truck_name);
	$api->addParam("ChartIDSales", $chart_id_sales);
	$api->addParam("ChartIDInventory", $chart_id_inv);
	$api->addParam("ChartIDCOGS", $chart_id_cost);
	$api->show_output = true;
	$api->debug_post = true;
	$api->execute();




//phpinfo();

die('aborted...');

$sql = "
	select *
	
	from trucks_log
	where id > 4500
		and deleted = 0
		and truck_id > 0
		and trailer_id > 0
		and driver_id > 0
	order by id
";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) {
	$sql = "
		update drivers set attached_truck_id = 0
		where attached_truck_id = '$row[truck_id]'
	";
	simple_query($sql);
	
	$sql = "
		update drivers set attached_trailer_id = 0
		where attached_trailer_id = '$row[trailer_id]'
	";
	simple_query($sql);
	
	$sql = "
		update drivers
		set attached_truck_id = '$row[truck_id]',
			attached_trailer_id = '$row[trailer_id]'
		
		where id = '$row[driver_id]'
	";
	simple_query($sql);
}

die("done");

		$last_week_start = strtotime("-".(date("w") + 7)." day", time());
		$last_week_end = strtotime("-".(date("w"))." day", time());
		$last_month = strtotime("-1 month", time());
		
		echo "<br><br><br>";
		echo date("m/d/Y", $last_month)."<br>";
		echo "linedate_pickup_eta >= '".date("Y-m-01")."' and linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", time()))."'";
		//echo date("m/d/Y", $last_month)."<br>";
		//echo date("m/d/Y", $last_month)."<br>";
		
		die;
		

update_origin_dest(2087);

die('aborted');
$sql = "
	select load_handler.*,
		log_fuel_updates.fuel_surcharge
		
	
	from load_handler
		left join log_fuel_updates on date_format(log_fuel_updates.linedate_added, '%Y-%m-%d') = date_format(load_handler.linedate_added, '%Y-%m-%d')
	where load_handler.linedate_added >= '2010-11-01'
		and load_handler.linedate_added < '2010-12-30'
		and load_handler.deleted = 0
		and fuel_surcharge <> actual_rate_fuel_surcharge
		and fuel_surcharge > 0
";
$data = simple_query($sql);

while($row = mysqli_fetch_array($data)) {
	echo "$row[id] - $row[actual_rate_fuel_surcharge] - $row[fuel_surcharge]<br>";
	$sql = "
		update load_handler
		set actual_rate_fuel_surcharge = '$row[fuel_surcharge]'
		where id = '$row[id]'
		limit 1
	";
	simple_query($sql);
	update_origin_dest($row['id']);
}

die;

$sql = "
	select *,
		(select sum(miles + miles_deadhead) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id having sum(trucks_log.daily_run_otr + daily_run_hourly) = 0) as miles
	
	from load_handler
	where deleted = 0
		and id > 900
	order by id desc
	
";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) {
	if($row['miles']) {
		echo $row['id']." | $row[miles]<br>";
	}
	
}


die('aborted...');

$sql = "
	select *
	
	from load_handler
	where deleted = 0
		and id > 900
";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) {
	echo $row['id']."<br>";
	update_origin_dest($row['id']);
}

die('aborted');
$sql = "
	select load_handler.*,
		load_handler_actual_var_exp.id as lha_id
	
	from load_handler
		left join load_handler_actual_var_exp on load_handler_actual_var_exp.load_handler_id = load_handler.id and load_handler_actual_var_exp.expense_type_id = 25
";
$data = simple_query($sql);

while($row = mysqli_fetch_array($data)) {
	if($row['lha_id'] == '') {
		echo $row['id']."<br>";
		$sql = "
			insert into load_handler_actual_var_exp
				(load_handler_id,
				expense_type_id,
				expense_amount)
				
			values ('$row[id]',
				25,
				'$row[actual_bill_customer]')
		";
		simple_query($sql);
		/*
		$sql = "
			update load_handler_actual_var_exp
			set expense_amount = '$row[actual_bill_customer]'
			where expense_type_id = 25
				and load_handler_id = '$row[id]'
		";
		//simple_query($sql);
		*/
	}
}
die;
?>
<div class='admin_menu1' id='truck_odometer_alert' style='height:150px;overflow-y:scroll;width:300px'>
</div>

<script type='text/javascript'>
	function enter_odo(truck_id, truck_name) {
		prompt_txt = "<table>";
		prompt_txt += "<tr><td colspan='2'>Please enter the odometer reading for truck '"+truck_name+"'</td></tr>";
		prompt_txt += "<tr><td>Miles:</td><td><input name='odometer' id='odometer'></td></tr>";
		prompt_txt += "<tr><td>Date of reading:</td><td><input name='odometer_linedate' id='odometer_linedate' value='<?=date('m/d/Y')?>'></td></tr></table>";
		$.prompt(prompt_txt,{
				buttons: {Okay:1, Cancel:0},
				callback: function(v,m,f) {
					if(v != undefined && v) {
						if(f.odometer == '') {
							$.prompt("Odometer miles is a required field");
							return false;
						}
						if(isNaN(f.odometer)) {
							$.prompt("Invalid odometer. Please enter a number only");
							return false;
						}
						
						 $.ajax({
						   type: "POST",
						   url: "ajax.php?cmd=save_odometer_reading",
						   data: {"truck_id":truck_id,
						   		odometer:f.odometer,
						   		linedate:f.odometer_linedate},
						   dataType: "xml",
						   cache:false,
						   success: function(xml) {
								load_odometer_alert();
						   }
						 });
					}
				},
				loaded: function() {
					$('#odometer').focus();
					$('#odometer_linedate').datepicker();
				}
			});
	}
	
	function load_odometer_alert() {
		// load the odometer list
		 $.ajax({
				   type: "POST",
				   url: "ajax.php?cmd=truck_odometer_alert",
				   data: {},
				   dataType: "xml",
				   cache:false,
				   success: function(xml) {
						$('#truck_odometer_alert').html($(xml).find('TruckList').text());
				   }
			});
	}
	
	<? 
	if(date("t") - date("j") <= 4) {
		echo "load_odometer_alert();";
	} 
	?>
</script>
<?
die;



$time = 0;
$distance = $pcm->CalcDistance3("37130", "37205", 0, $time);
echo "$distance - ($time)";
die;

if($pcm->ID() <= 0) die("Failed to load PC*Miler module");


$plist = $pcm->GetFmtPickList("37130", "NA", 0, 20, 100, 100);
echo $plist->Entry(0);
die;

$trip = $pcm->NewTrip("NA");
if($trip->ID() <= 0) die("failed to create trip");
//$loc1 = "3611 sanford dr, Murfreesboro, TN";
//$loc2 = "216 Parthenon Blvd, 37086";
$loc1 = "Murfreesboro, TN";
$loc2 = "La Vergne, TN";
$loc3 = "Nashville, TN";

/*
try {
	$plist = $pcm->GetPickList($loc1, "NA", 0);
	if(count($plist) == 0) {
		die("invalid location");
	} else {
		$loc1 = $plist->Entry(1);
	}
} catch (Exception $e) {
	die("error with location");
}
*/

$trip->AddStop($loc1);
$trip->AddStop($loc2);
$trip->AddStop($loc3);

$options = $trip->GetOptions();
$options->RouteType = 0;
//$distance = $pcm->CalcDistance2($loc1, $loc2, 0) / 10;
$traveldist = $trip->TravelDistance() / 10;
$travel_time = $trip->TravelTime() / 60.0;
$reportobj = $trip->GetHTMLReport(0);

echo "
	Distance: $traveldist miles<br>
	Travel Time: $travel_time hours<p>
	Report:<p>
	".$reportobj->Text()."
";

die;

$trip->AddStop("");



die("aborted");

$sql = "
	select *
	
	from trailers
	where deleted = 0
		and linedate_aquired > 0
		and linedate_returned > 0
";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) {
	$sql = "
		insert into equipment_history
			(equipment_type_id,
			equipment_id,
			equipment_value,
			linedate_added,
			linedate_aquired,
			linedate_returned)
			
		values (2,
			'$row[id]',
			'0',
			now(),
			'".date("Y-m-d", strtotime($row['linedate_aquired']))."',
			'".date("Y-m-d", strtotime($row['linedate_returned']))."')
	";
	simple_query($sql);
}

die;

$sql = "
	select *
	
	from load_handler
	where deleted = 0
";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) {
	update_origin_dest($row['id']);
}

echo "<p>done...";
//phpinfo();
//echo getcwd();
die;
$sql = "
	select *
	
	from trucks_log
	where deleted = 0
	
";

$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) {
	$note_array = explode(chr(10), $row['notes']);
	foreach($note_array as $value) {
		if($value != '') {
			$hold_date = substr($value,0,strpos($value, " "));
			$hold_date_array = explode("-", $hold_date);
			if(count($hold_date_array) == 3) {
				$note = str_replace(chr(13), "", substr($value,strpos($value," ")));
				$hold_date = strtotime("$hold_date_array[0]/$hold_date_array[1]/$hold_date_array[2]");
				echo $value;
				echo " | ".date("m/d/Y", $hold_date)."<br>";
				$sql = "
					insert into trucks_log_notes
						(truck_log_id,
						linedate_added,
						note,
						user_id,
						deleted)
						
					values ('$row[id]',
						'".date('Y-m-d', $hold_date)."',
						'".sql_friendly($note)."',
						0,
						0)
				";
				simple_query($sql);
			}
		}
	}
	//echo $row['notes'];
}
die();
phpinfo();

/* connect to gmail */
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'mkd@conardwd.com';
$password = 'con!ard';

/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox,'ALL');

/* if emails are returned, cycle through each... */
if($emails) {
	
	/* begin output var */
	$output = '';
	
	/* put the newest emails on top */
	//rsort($emails);
	
	/* for every email... */
	foreach($emails as $email_number) {
		
		/* get information specific to this email */
		$overview = imap_fetch_overview($inbox,$email_number,0);
		//$message = imap_fetchbody($inbox,$email_number,2);
		$msg_struct = imap_fetchstructure($inbox, $email_number);
		
		/* output the email header information */
		$output.= '<div class="toggler '.($overview[0]->seen ? 'read' : 'unread').'">';
		$output.= '<span class="subject">'.$overview[0]->subject.'</span> ';
		$output.= '<span class="from">'.$overview[0]->from.'</span>';
		$output.= '<span class="date">on '.$overview[0]->date.'</span>';
		$output.= '</div>';
		$part_count = count($msg_struct->parts);
		
		if($part_count == 2) {
			
			for($p=1;$p<999999;$p++) {
				$tFile = "$p.pdf";
				$tFileFull = "orders/temp/$tFile";
				if(!file_exists($tFileFull)) break;
			}
			echo "($tFile)";
			
			$attachment_contents = imap_fetchbody($inbox, $email_number, "2");
			$fh = fopen($tFileFull, "w");
			fwrite($fh, imap_base64($attachment_contents));
			fclose($fh);

		}
		
		
		/*
		if($order_id != '') {
			// see if this order has been processed
			$sql = "
				select id
				
				from inventory_shipping
				where po_number = '".."'
					and deleted = 0
			";
			$data_order = simple_query($sql);
			if(mysqli_num_rows($data_order)) {
				// order already exists, archive this e-mail
			}
		}
		*/
		break;
		//imap_mail_move($inbox, $email_number, "Processed");
		
		
		/* output the email body */
		//$output.= '<div class="body">'.$message.'</div>';
	}
	
	echo $output;
} 

/* close the connection */
imap_close($inbox);

/*
$from = "chris@sherrodcomputers.com";
$fromname = $from;
$to = "chris@sherrodcomputers.com";
$toname = $to;
$subject = "test email";
$text = "test body";
$html = "test html";
sendMail($from, $fromname, $to, $toname, $subject, $text, $html);
*/

die();
$sql = "SELECT isi.*, ie.net_weight FROM conard.inventory_shipping_items isi, conard.inventory_entries ie where ie.id = isi.inventory_entries_id and isi.weight = 0 and ie.net_weight <> 0";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) {
	//echo $row['id']." weight: $row[net_weight]<br>";
	
	$sql = "
		update inventory_shipping_items
		
		set weight = '$row[net_weight]'
		
		where id = '$row[id]'
			and weight = 0
			and qty = 1
	";
	simple_query($sql);
	echo $sql."<br>";
}

die();
	$ftp_dir = "C:\\Users Shared Folders\\CustRates\\EDI";

	$listDir = Array();
	if($checkDir = opendir($ftp_dir)) {
		// check all files in $dir, add to array listDir or listFile
		while($file = readdir($checkDir)){
			
			if($file != "." && $file != ".."){
				if(is_file($ftp_dir . "/" . $file)){
					$listDir[] = $file;
				}
			}
		}
	} else {
		echo "Could not open directory";
	}
	
	
	if(!count($listDir)) {
	   	echo "No files";
	} else {
		foreach($listDir as $file) {
			echo "$file<br>";
		}
	}

/*
	$fname = "";
	$fhandle = fopen($fname,"rb");
	$fcontents = fread($fhandle, filesize($fname));
	fclose($fhandle);
*/



	die();
	/* grab our list of trailers that are currently being used */
	$sql = "
		select distinct trailer_id
		
		from trailers, trucks_log
		where trailers.id = trucks_log.trailer_id
		and trucks_log.deleted = 0
	";
	$data_trailers_used = simple_query($sql);
	
	$trailer_array = array();
	while($row_trailers_used = mysqli_fetch_array($data_trailers_used)) {
		$trailer_array[] = $row_trailers_used['trailer_id'];
	}
	
	
	/* grab our list of available trailers */
	$sql = "
		select id,
			trailer_name
			
		from trailers
		where deleted = 0
	";
	$data = simple_query($sql);
	
	while($row = mysqli_fetch_array($data)) {
		if(in_array($row['id'],$trailer_array)) echo "<font color='#cccccc'>";
		echo $row['trailer_name']."</font><br>";
	}
	
	
	
	
die;

$sql = "
	select distinct load_handler_id 
	
	from trucks_log
	where trailer_maint_per_mile = 4
	order by load_handler_id
";
$data = simple_query($sql);

while($row = mysqli_fetch_array($data)) {
	echo $row['load_handler_id']."<br>";
	
	$sql = "
		update trucks_log
		set trailer_maint_per_mile = '$defaultsarray[trailer_maint_per_mile]'
		where trailer_maint_per_mile = 4
			and load_handler_id = '$row[load_handler_id]'
	";
	simple_query($sql);
	
	update_origin_dest($row['load_handler_id']);
}
die;

$_POST['date_from'] = "2/13/2012";
$_POST['date_to'] = "2/19/2012";
$date_start = strtotime($_POST['date_from']);
$date_end = strtotime($_POST['date_to']);
$sql = "
	select driver_id, 
		drivers.name_driver_first, 
		drivers.name_driver_last 

	from `conard_trucking_restore`.trucks_log 
		inner join `conard_trucking_restore`.drivers on drivers.id = trucks_log.driver_id 
		
	where trucks_log.deleted = 0 and linedate_pickup_eta >= '2012-02-13' and linedate_pickup_eta < '2012-02-20' 
	
	union 
	
	select driver2_id, 
		drivers.name_driver_first, 
		drivers.name_driver_last 
	from `conard_trucking_restore`.trucks_log 
		inner join `conard_trucking_restore`.drivers on drivers.id = trucks_log.driver2_id 
	where trucks_log.deleted = 0 and driver2_id > 0 and linedate_pickup_eta >= '2012-02-13' and linedate_pickup_eta < '2012-02-20' order by name_driver_last, name_driver_first 
";
$data = simple_query($sql);
			echo "
				<tr>
					<td colspan='2'>
						<table class='admin_menu3' style='width:100%'>
						<tr>
							<td>Date Start:</td>
							<td> ".date("m/d/Y", $date_start)."</td>
						</tr>
						<tr>
							<td>Date End:</td>
							<td> ".date("m/d/Y", $date_end)."</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<form action='' method='post'>
						<input type='hidden' name='date_from' value='$_POST[date_from]'>
						<input type='hidden' name='date_to' value='$_POST[date_to]'>
						<input type='hidden' name='build_report' value='1'>
						<table class='admin_menu2' style='width:100%'>
						<tr style='background-color:#bdc3ff'>
							<td><b>Driver</b></td>
							<td align='right'><b>Dispatches</b></td>
							<td align='right'><b>Days</b></td>
							<td align='right'nowrap><b>Hours Logged</b></td>
							<td align='right' nowrap><b>Hours Charged</b></td>
							<td align='right' nowrap><b>Days</b></td>
							<td></td>
						</tr>
			";
			
			$driver_array = array();
			$counter = 0;
			while($row = mysqli_fetch_array($data)) {
				$counter++;
				if(!in_array($row['driver_id'], $driver_array)) {
					$driver_array[] = $row['driver_id'];
					
					
					// get the total hours this driver worked (according to our system)
					$sql = "
						select ifnull(sum(hours_worked),0) as hours_worked,
							count(*) as dispatch_count,
							ifnull(sum(daily_run_hourly),0) as days_run
						
						from `conard_trucking_restore`.trucks_log
						where deleted = 0
							and linedate_pickup_eta >= '".date("Y-m-d", $date_start)."'
							and linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", $date_end))."'
							and (
								driver_id = '$row[driver_id]'
								or driver2_id = '$row[driver_id]'
							)
					";
					
					$data_hours = simple_query($sql);
					$row_hours = mysqli_fetch_array($data_hours);
					
					
					echo "
						<tr class='row_hover_highlight'>
							<td nowrap>$row[name_driver_first] $row[name_driver_last]</td>
							<td align='right'>$row_hours[dispatch_count]</td>
							<td nowrap align='right'>".($row_hours['days_run'] == 0 ? "<span class='show_inactive'>" : "")."$row_hours[days_run]</span></td>
							<td nowrap align='right'>".($row_hours['hours_worked'] == 0 ? "<span class='show_inactive'>" : "")."$row_hours[hours_worked]</span></td>
							<td align='right'>
								<input name='hours_charged_$row[driver_id]' style='width:50px' id='hours_charged_$row[driver_id]'>
								<input type='hidden' name='id_list[]' value='$row[driver_id]'>
								<input type='hidden' name='hours_logged_$row[driver_id]' value='$row_hours[hours_worked]'>
							</td>
							<td align='right'>
								<input name='days_charged_$row[driver_id]' style='width:50px' id='days_charged_$row[driver_id]'>
								<input type='hidden' name='days_logged_$row[driver_id]' value='$row_hours[days_run]'>
							</td>
							<td>".($counter % 5 == 1 ? "<input type='submit' value='Update'>" : "")." ($row[driver_id])</td>
						</tr>
					";
				}
			}
			
			echo "
					</table>
					</form>
				</td>
			</tr>
			</table>
			";



die;
	
?>
