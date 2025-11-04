<?
$usetitle = "Report - Lane Analyzer V1 - Loads Only";
$use_title ="Report - Lane Analyzer V1 - Loads Only";
?>
<? include('header.php') ?>
<?
if(!isset($_POST['date_from']))			    $_POST['date_from']=date("m/01/Y");
if(!isset($_POST['date_to']))				$_POST['date_to']=date("m/d/Y");

if(!isset($_POST['trip_mode']))             $_POST['trip_mode']=0;

//if(!isset($_POST['stop_1_name']))			$_POST['stop_1_name']="";
//if(!isset($_POST['stop_1_addr']))			$_POST['stop_1_addr']="";
if(!isset($_POST['stop_1_city']))			$_POST['stop_1_city']="";
if(!isset($_POST['stop_1_state']))			$_POST['stop_1_state']="";
//if(!isset($_POST['stop_1_zip']))			$_POST['stop_1_zip']="";

//if(!isset($_POST['stop_2_name']))			$_POST['stop_2_name']="";
//if(!isset($_POST['stop_2_addr']))			$_POST['stop_2_addr']="";
if(!isset($_POST['stop_2_city']))			$_POST['stop_2_city']="";
if(!isset($_POST['stop_2_state']))			$_POST['stop_2_state']="";
//if(!isset($_POST['stop_2_zip']))			$_POST['stop_2_zip']="";

if(!isset($_POST['customer_id']))			$_POST['customer_id']=0;
//if(!isset($_POST['truck_id']))			$_POST['truck_id']=0;
//if(!isset($_POST['trailer_id']))			$_POST['trailer_id']=0;
//if(!isset($_POST['driver_id']))			$_POST['driver_id']=0;

$mrr_sql="";
if(isset($_POST['run_report']))
{
     //try dispatch stop match
     $mrr_adder="";
     if($_POST['date_from']!="")			$mrr_adder.=" and load_handler.linedate_pickup_eta>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'";
     if($_POST['date_to']!="")			    $mrr_adder.=" and load_handler.linedate_pickup_eta<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'";
     
     //if($_POST['truck_id'] > 0)			$mrr_adder.=" and trucks_log.truck_id='".sql_friendly($_POST['truck_id'])."'";
     //if($_POST['trailer_id'] > 0)			$mrr_adder.=" and trucks_log.trailer_id='".sql_friendly($_POST['trailer_id'])."'";
     
     if($_POST['customer_id'] > 0)			$mrr_adder.=" and load_handler.customer_id='".sql_friendly($_POST['customer_id'])."'";
     
     //if($_POST['driver_id'] > 0)			$mrr_adder.=" and (trucks_log.driver_id='".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id='".sql_friendly($_POST['driver_id'])."')";
     
     $stop1_adder="";
     if(trim($_POST['stop_1_city'])!="")	$stop1_adder.="and load_handler.origin_city like '".sql_friendly($_POST['stop_1_city'])."'";
     if(trim($_POST['stop_1_state'])!="")	$stop1_adder.="and load_handler.origin_state like '".sql_friendly($_POST['stop_1_state'])."'";
     
     $stop2_adder="";
     if(trim($_POST['stop_2_city'])!="")	$stop2_adder.="and load_handler.dest_city like '".sql_friendly($_POST['stop_2_city'])."'";
     if(trim($_POST['stop_2_state'])!="")	$stop2_adder.="and load_handler.dest_state like '".sql_friendly($_POST['stop_2_state'])."'";
     
     $sql = "
     		select load_handler.*,
     		    (select SUM(trucks_log.pcm_miles) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_pcm_miles,
     		    
     		    (select SUM(trucks_log.miles) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_miles,
     		    (select SUM(trucks_log.miles_deadhead) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_miles_dh,
     		    
     		    (select SUM(trucks_log.loaded_miles_hourly) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_miles_hourly,
     		    (select SUM(trucks_log.miles_deadhead_hourly) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_miles_hourly_dh,
     		    
     		    (select SUM(trucks_log.hours_worked) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_hours,
     			customers.name_company as customer_name
     		from load_handler
     			left join customers on customers.id=load_handler.customer_id
     		where load_handler.deleted = 0
     			and load_handler.linedate_invoiced is not NULL 
     			and load_handler.linedate_invoiced >= '2010-01-01 00:00:00'
     			".$mrr_adder."
     			".$stop1_adder."
     			".$stop2_adder."
     		order by load_handler.linedate_pickup_eta asc,load_handler.id asc
     	";
     $mrr_sql=$sql;
     $data = simple_query($sql);
}

ob_start();


//get the truck list
$sql = "
		select *		
		from trucks
		where deleted = 0
		order by active desc, name_truck
	";
//$data_trucks = simple_query($sql);

//get the traier list
$sql = "
		select *		
		from trailers
		where deleted = 0
		order by active desc, trailer_name
	";
//$data_trailers = simple_query($sql);

//get the driver list
$sql = "
		select *		
		from drivers
		where deleted = 0
		order by active desc, name_driver_last, name_driver_first
	";
//$data_drivers = simple_query($sql);

//get customers
$sql = "
		select *		
		from customers
		where deleted = 0
		order by active desc, name_company
	";
$data_cust = simple_query($sql);
?>


    <form action='' method='post'>

        <div style='margin-left:30px;margin-top:10px;display:inline;float:left; width:1800px;'>
            <div style='float:right; width:200px;'><a href='report_lane_analyzer2.php'>View Version 2</a></div>
            <h1>Lane Analysis Report - V1 - Loads Only</h1>
        </div>
        <br>
        <br>
        <br>
         <?php
         /*
          * <td colspan='2'><b>Shipper Name</b></td>
          * <td><b>Address</b></td>
          * <td><b>Zip</b></td>
          * 
          * <td colspan='2'><input type='text' name='stop_1_name' id='stop_1_name' value='<?= $_POST['stop_1_name'] ?>' class='input_normal'></td>
          * <td><input type='text' name='stop_1_addr' id='stop_1_addr' value='<?= $_POST['stop_1_addr'] ?>' class='input_normal'></td>
          * <td><input type='text' name='stop_1_zip' id='stop_1_zip' value='<?= $_POST['stop_1_zip'] ?>' class='input_medium'></td>
          * 
          * <td colspan='2'><input type='text' name='stop_2_name' id='stop_2_name' value='<?= $_POST['stop_2_name'] ?>' class='input_normal'></td>
          * <td><input type='text' name='stop_2_addr' id='stop_2_addr' value='<?= $_POST['stop_2_addr'] ?>' class='input_normal'></td>
          * <td><input type='text' name='stop_2_zip' id='stop_2_zip' value='<?= $_POST['stop_2_zip'] ?>' class='input_medium'></td>
          * 
          * 
          */
         ?>
        <table class='admin_menu1 font_display_section' style='text-align:left; width:1800px; margin:10px'>
            <tr>
                <td valign='top'><b>Stop</b></td>
                <td valign='top'><b>City</b></td>
                <td valign='top'><b>State</b></td>
                <td valign='top'><b>Date Range</b></td>
                <td valign='top'><b>Customer</b></td>
            </tr>
            <tr>
                <td valign='top'><b>Origin</b></td>
                <td valign='top'><input type='text' name='stop_1_city' id='stop_1_city' value='<?= $_POST['stop_1_city'] ?>' class='input_medium'></td>
                <td valign='top'><input type='text' name='stop_1_state' id='stop_1_state' value='<?= $_POST['stop_1_state'] ?>' class='input_short'></td>
                <td valign='top'>From <input type='text' class='datepicker' name='date_from' id='date_from' value='<?= $_POST['date_from'] ?>' style='width:75px;'></td>
                <td valign='top'>
                    <select name='customer_id' id='customer_id'>
                        <option value='0'>All Customers</option>
                         <?
                         while($row_cust = mysqli_fetch_array($data_cust))
                         {
                              echo "<option value='$row_cust[id]' ".($row_cust['id'] == $_POST['customer_id'] ? 'selected' : '').">".(!$row_cust['active'] ? '(inactive) ' : '')."$row_cust[name_company]</option>";
                         }
                         ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td valign='top'><b>Destination</b></td>               
                <td valign='top'><input type='text' name='stop_2_city' id='stop_2_city' value='<?= $_POST['stop_2_city'] ?>' class='input_medium'></td>
                <td valign='top'><input type='text' name='stop_2_state' id='stop_2_state' value='<?= $_POST['stop_2_state'] ?>' class='input_short'></td>
                <td valign='top'>To &nbsp; &nbsp; &nbsp;<input type='text' class='datepicker' name='date_to' id='date_to' value='<?= $_POST['date_to'] ?>' style='width:75px;'></td>
                <td valign='top'>
                    <select name='trip_mode' id='trip_mode'>
                        <option value='0'<?=($_POST['trip_mode']==0 ? " selected" : "") ?>>No Round Trip</option>
                        <option value='1'<?=($_POST['trip_mode']==1 ? " selected" : "") ?>>Show Round Trip</option>
                    </select>
                    <input type='submit' name='run_report' id='run_report' value='Run'>
                </td>
            </tr>
            <!----
            <tr>
                <td colspan='7'>&nbsp;NOTE: Shipper Name, Address, and Zip are not used at this time.  City, State, Date Range, and truck/trailer/driver optional select boxes are.</td>
            </tr>
            ----->
             <?php
             /*             
            <tr>
                
                <td>
                    <select name='driver_id' id='driver_id'>
                        <option value='0'>All Drivers</option>
                         <?
                         while($row_driver = mysqli_fetch_array($data_drivers))
                         {
                              echo "<option value='$row_driver[id]' ".($row_driver['id'] == $_POST['driver_id'] ? 'selected' : '').">".(!$row_driver['active'] ? '(inactive) ' : '')."$row_driver[name_driver_last], $row_driver[name_driver_first]</option>";
                         }
                         ?>
                    </select>
                </td>
                <td>
                    <select name='truck_id' id='truck_id'>
                        <option value='0'>All Trucks</option>
                         <?
                         while($row_truck = mysqli_fetch_array($data_trucks))
                         {
                              echo "<option value='$row_truck[id]' ".($row_truck['id'] == $_POST['truck_id'] ? 'selected' : '').">".(!$row_truck['active'] ? '(inactive) ' : '')."$row_truck[name_truck]</option>";
                         }
                         ?>
                    </select>
                </td>
                <td>
                    <select name='trailer_id' id='trailer_id'>
                        <option value='0'>All Trailers</option>
                         <?
                         while($row_trailer = mysqli_fetch_array($data_trailers))
                         {
                              echo "<option value='$row_trailer[id]' ".($row_trailer['id'] == $_POST['trailer_id'] ? 'selected' : '').">".(!$row_trailer['active'] ? '(inactive) ' : '')."$row_trailer[trailer_name]</option>";
                         }
                         ?>
                    </select>
                </td>
            </tr>
             */
             ?>
        </table>
         <?
         //echo "<br><b>Query Run:</b><br>".$mrr_sql."<br>"
         ?>
        <div style='clear:both'></div>
        <table class='admin_menu1 font_display_section' style='text-align:left; width:1800px; margin:10px'>
            <!----
                <td><b>Dispatch</b></td>
                <td><b>Driver</b></td>
                <td><b>Truck</b></td>
                <td><b>Trailer</b></td>            
            ---->
            <tr>
                <td><b>Load</b></td>                
                <td><b>Pickup ETA</b></td>
                <td><b>Dropoff ETA</b></td>
                <td><b>Customer</b></td>
                <td><b>Origin</b></td>
                <td><b>State</b></td>
                <td><b>Dest</b></td>
                <td><b>State</b></td>
                <td align='right'><b>Revenue</b></td>
                <td align='right'><b>PC*M Miles</b></td>
                <td align='right'><b>Miles</b></td>
                <td align='right'><b>Diff Miles</b></td>
                <td align='right'><b>Hours</b></td>
                <td align='right'><b>Gals</b></td>
                <td align='right'><b>Fuel</b></td>
                <td align='right'><b>DailyCost</b></td>
                <td align='right'><b>Labor(Miles)</b></td>
                <td align='right'><b>Labor(Hourly)</b></td>
                <td align='right'><b>Full Cost</b></td>
                <td align='right'><b>Profit</b></td>
            </tr>
             <?
             $hub_city="la vergne";			$hub_city2="lavergne";				$hub_state="tn";				$hub_zip="37086";			//back to hub info...
             
             $tot_pcm_miles=0;
             $tot_miles=0;
             $tot_diff_miles=0;
             $tot_hours=0;
             $tot_gals=0;
             $tot_fuel=0;
             $tot_dcost=0;
             $tot_labor=0;
             $tot_hourly=0;
             $tot_fcost=0;
             $tot_profit=0;
             $tot_revenue=0;
             
             $round_trip=$_POST['trip_mode'];
             
             $counter = 0;
             while($row = mysqli_fetch_array($data))
             {
                  //$pta="".date("m/d/Y H:i", strtotime($row['pickup_time']))."";			//pickup_time
                  $pta="".date("m/d/Y H:i", strtotime($row['linedate_pickup_eta']))."";	//dispatch date...
                  $dta="".date("m/d/Y H:i", strtotime($row['linedate_dropoff_eta']))."";
                  
                  $labor_miles=$row['sum_miles'] + $row['sum_miles_dh'];   //($row['miles'] + $row['miles_deadhead']);
                  
                  $pcm_miles=$row['sum_pcm_miles'];
                  $reg_miles=$row['sum_miles'] + $row['sum_miles_dh'] + $row['sum_miles_hourly'] + $row['sum_miles_hourly_dh'];
                                //$row['fedex_edi_invoice_mileage'] $row['lynnco_edi_invoice_mileage'];
                  $diff_miles=$reg_miles - $pcm_miles;
                  $reg_hours=$row['sum_hours'];
     
                  $load_gals=($reg_miles / $row['budget_average_mpg']);
                  $load_fuel=($reg_miles * $row['actual_fuel_surcharge_per_mile']);
                  $load_dcost=$row['budget_day_variance'];
                  $load_labor=($labor_miles * $row['budget_labor_per_mile']);
                  $load_hourly=($reg_hours * $row['budget_labor_per_hour']);
                  $load_fcost=$row['actual_total_cost'];
                  $load_profit=$row['actual_bill_customer'] - $row['actual_total_cost'];
                  $load_revenue=$row['actual_bill_customer'];
                  
                  echo "
                    <tr class='".($counter%2==0 ? "even" : "odd")."'>
                        <td valign='top'><a href='manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a></td>				
                        <td valign='top'>".$pta."</td>	
                        <td valign='top'>".$dta."</td>				
                        <td valign='top'>".$row['customer_name']."</td>				
                        <td valign='top'>".$row['origin_city']."</td>
                        <td valign='top'>".$row['origin_state']."</td>
                        <td valign='top'>".$row['dest_city']."</td>
                        <td valign='top'>".$row['dest_state']."</td>
                        <td valign='top' align='right'>$".number_format($load_revenue,2)."</td>
                        <td valign='top' align='right'>".number_format($pcm_miles,2)."</td>
                        <td valign='top' align='right'>".number_format($reg_miles,2)."</td>
                        <td valign='top' align='right'>".number_format($diff_miles,2)."</td>
                        <td valign='top' align='right'>".number_format($reg_hours,2)."</td>
                        <td valign='top' align='right'>".number_format($load_gals,2)."</td>
                        <td valign='top' align='right'>$".number_format($load_fuel,2)."</td>
                        <td valign='top' align='right'>$".number_format($load_dcost,2)."</td>
                        <td valign='top' align='right'>$".number_format($load_labor,2)."</td>
                        <td valign='top' align='right'>$".number_format($load_hourly,2)."</td>
                        <td valign='top' align='right'>$".number_format($load_fcost,2)."</td>
                        <td valign='top' align='right'>$".number_format($load_profit,2)."</td>
                    </tr>
                  ";
     
                  $sub_pcm_miles=$pcm_miles;
                  $sub_miles=$reg_miles;
                  $sub_hours=$reg_hours;
                  $sub_diff_miles=$diff_miles;
     
                  $sub_gals=$load_gals;
                  $sub_fuel=$load_fuel;
                  $sub_dcost=$load_dcost;
                  $sub_labor=$load_labor;
                  $sub_hourly=$load_hourly;
                  $sub_fcost=$load_fcost;
                  $sub_profit=$load_profit;
                  $sub_revenue=$load_revenue;
                  
                  /*
                   * <td valign='top'><a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
                   * <td valign='top'><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driver_name']."</a></td>
                   *        ".($row['driver2_id'] > 0  ? "<br><a href='admin_drivers.php?id=".$row['driver2_id']."' target='_blank'>".$row['driver2_name']."</a>" : "")."
                   * <td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['truck_name']."</a></td>
                   * <td valign='top'><a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailer_name']."</a></td>
                   * 
                   */
     
                    //this is a load that starts with the trip, so now track it back to the conard terminal....or at least 
                    $not_at_hub=1;
                    if(trim(strtolower($row['dest_city']))==$hub_city  && trim(strtolower($row['dest_state']))==$hub_state)		$not_at_hub=0;		//ALREADY home town...hub
                    if(trim(strtolower($row['dest_city']))==$hub_city2 && trim(strtolower($row['dest_state']))==$hub_state)		$not_at_hub=0;		//ALREADY home town...hub
     
                    if($round_trip > 0)
                    {
                        $stop1_adder="";
                        if(trim($_POST['stop_2_city'])!="")	    $stop1_adder.="and load_handler.origin_city like '".sql_friendly($_POST['stop_2_city'])."'";
                        if(trim($_POST['stop_2_state'])!="")	$stop1_adder.="and load_handler.origin_state like '".sql_friendly($_POST['stop_2_state'])."'";
     
                        $stop2_adder="";
                        if(trim($_POST['stop_1_city'])!="")	    $stop2_adder.="and load_handler.dest_city like '".sql_friendly($_POST['stop_1_city'])."'";
                        if(trim($_POST['stop_1_state'])!="")	$stop2_adder.="and load_handler.dest_state like '".sql_friendly($_POST['stop_1_state'])."'";
     
     
                        $sql2 = "
                            select load_handler.*,
                                (select SUM(trucks_log.pcm_miles) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_pcm_miles,
                                
                                (select SUM(trucks_log.miles) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_miles,
                                (select SUM(trucks_log.miles_deadhead) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_miles_dh,
                                
                                (select SUM(trucks_log.loaded_miles_hourly) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_miles_hourly,
                                (select SUM(trucks_log.miles_deadhead_hourly) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_miles_hourly_dh,
                                
                                (select SUM(trucks_log.hours_worked) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) sum_hours,
                                customers.name_company as customer_name
                            from load_handler
                                left join customers on customers.id=load_handler.customer_id
                            where load_handler.deleted = 0
                                and load_handler.linedate_pickup_eta>='".$row['linedate_dropoff_eta']."'
                                ".$stop1_adder."
                                ".$stop2_adder."
                            order by load_handler.linedate_pickup_eta asc,load_handler.id asc
                            limit 1
                        ";
                        $mrr_sql.="".$sql2;
                        $data2 = simple_query($sql2);
                        while($row2 = mysqli_fetch_array($data2))
                        {
                             //$pta="".date("m/d/Y H:i", strtotime($row2['pickup_time']))."";			//pickup_time
                             $pta="".date("m/d/Y H:i", strtotime($row2['linedate_pickup_eta']))."";	//dispatch date...
                             $dta="".date("m/d/Y H:i", strtotime($row2['linedate_dropoff_eta']))."";
     
                             $labor_miles=$row2['sum_miles'] + $row2['sum_miles_dh'];   //($row2['miles'] + $row2['miles_deadhead']);
     
                             $pcm_miles=$row2['sum_pcm_miles'];
                             $reg_miles=$row2['sum_miles'] + $row2['sum_miles_dh'] + $row2['sum_miles_hourly'] + $row2['sum_miles_hourly_dh'];
                                        //$row2['fedex_edi_invoice_mileage'] $row2['lynnco_edi_invoice_mileage'];
                             $diff_miles=$reg_miles - $pcm_miles;
                             $reg_hours=$row2['sum_hours'];
     
                             $load_gals=($reg_miles / $row2['budget_average_mpg']);
                             $load_fuel=($reg_miles * $row2['actual_fuel_surcharge_per_mile']);
                             $load_dcost=$row2['budget_day_variance'];
                             $load_labor=($labor_miles * $row2['budget_labor_per_mile']);
                             $load_hourly=($reg_hours * $row2['budget_labor_per_hour']);
                             $load_fcost=$row2['actual_total_cost'];
                             $load_profit=$row2['actual_bill_customer'] - $row2['actual_total_cost'];
                             $load_revenue=$row2['actual_bill_customer'];
     
                             echo "
                                <tr class='".($counter%2==0 ? "even" : "odd")."'>
                                    <td valign='top'>-----<a href='manage_load.php?load_id=".$row2['id']."' target='_blank'>".$row2['id']."</a></td>				
                                    <td valign='top'>".$pta."</td>	
                                    <td valign='top'>".$dta."</td>			
                                    <td valign='top'>".$row2['customer_name']."</td>				
                                    <td valign='top'>".$row2['origin_city']."</td>
                                    <td valign='top'>".$row2['origin_state']."</td>
                                    <td valign='top'>".$row2['dest_city']."</td>
                                    <td valign='top'>".$row2['dest_state']."</td>
                                    <td valign='top' align='right'>$".number_format($load_revenue,2)."</td>
                                    <td valign='top' align='right'>".number_format($pcm_miles,2)."</td>
                                    <td valign='top' align='right'>".number_format($reg_miles,2)."</td>
                                    <td valign='top' align='right'>".number_format($diff_miles,2)."</td>
                                    <td valign='top' align='right'>".number_format($reg_hours,2)."</td>
                                    <td valign='top' align='right'>".number_format($load_gals,2)."</td>
                                    <td valign='top' align='right'>$".number_format($load_fuel,2)."</td>
                                    <td valign='top' align='right'>$".number_format($load_dcost,2)."</td>
                                    <td valign='top' align='right'>$".number_format($load_labor,2)."</td>
                                    <td valign='top' align='right'>$".number_format($load_hourly,2)."</td>
                                    <td valign='top' align='right'>$".number_format($load_fcost,2)."</td>
                                    <td valign='top' align='right'>$".number_format($load_profit,2)."</td>
                                </tr>
                              ";
     
                             $sub_pcm_miles+=$pcm_miles;
                             $sub_miles+=$reg_miles;
                             $sub_hours+=$reg_hours;
                             $sub_diff_miles+=$diff_miles;
     
                             $sub_gals+=$load_gals;
                             $sub_fuel+=$load_fuel;
                             $sub_dcost+=$load_dcost;
                             $sub_labor+=$load_labor;
                             $sub_hourly+=$load_hourly;
                             $sub_fcost+=$load_fcost;
                             $sub_profit+=$load_profit;
                             $sub_revenue+=$load_revenue;     
                        }
                       
                        echo "
                            <tr class='".($counter%2==0 ? "even" : "odd")."' style='font-weight:bold; color:brown;'>
                                <td valign='top'>&nbsp;</td>				
                                <td valign='top' colspan='7'>Return Trip Summary</td>	
                                <td valign='top' align='right'>$".number_format($sub_revenue,2)."</td>
                                <td valign='top' align='right'>".number_format($sub_pcm_miles,2)."</td>
                                <td valign='top' align='right'>".number_format($sub_miles,2)."</td>
                                <td valign='top' align='right'>".number_format($sub_diff_miles,2)."</td>
                                <td valign='top' align='right'>".number_format($sub_hours,2)."</td>
                                <td valign='top' align='right'>".number_format($sub_gals,2)."</td>
                                <td valign='top' align='right'>$".number_format($sub_fuel,2)."</td>
                                <td valign='top' align='right'>$".number_format($sub_dcost,2)."</td>
                                <td valign='top' align='right'>$".number_format($sub_labor,2)."</td>
                                <td valign='top' align='right'>$".number_format($sub_hourly,2)."</td>
                                <td valign='top' align='right'>$".number_format($sub_fcost,2)."</td>
                                <td valign='top' align='right'>$".number_format($sub_profit,2)."</td>
                            </tr>
                        ";
                  }
                  
                  $tot_pcm_miles+=$sub_pcm_miles;
                  $tot_miles+=$sub_miles;
                  $tot_diff_miles+=$sub_diff_miles;
                  $tot_hours+=$sub_hours;
                  $tot_gals+=$sub_gals;
                  $tot_fuel+=$sub_fuel;
                  $tot_dcost+=$sub_dcost;
                  $tot_labor+=$sub_labor;
                  $tot_hourly+=$sub_hourly;
                  $tot_fcost+=$sub_fcost;
                  $tot_profit+=$sub_profit;
                  $tot_revenue+=$sub_revenue;
                  
                  $counter++;
             }
             /*
              * <td><b>Dispatch</b></td>
              * <td><b>Driver</b></td>
              * <td><b>Truck</b></td>
              * <td><b>Trailer</b></td> 
              */             
             ?>
            <tr>
                <td><b>Load</b></td>                
                <td><b>PickupETA</b></td>
                <td><b>Dropoff ETA</b></td>
                <td><b>Customer</b></td>
                <td><b>Origin</b></td>
                <td><b>State</b></td>
                <td><b>Dest</b></td>
                <td><b>State</b></td>
                <td align='right'><b>Revenue</b></td>
                <td align='right'><b>PC*M Miles</b></td>
                <td align='right'><b>Miles</b></td>
                <td align='right'><b>Diff Miles</b></td>
                <td align='right'><b>Hours</b></td>
                <td align='right'><b>Gals</b></td>
                <td align='right'><b>Fuel</b></td>
                <td align='right'><b>DailyCost</b></td>
                <td align='right'><b>Labor(Miles)</b></td>
                <td align='right'><b>Labor(Hourly)</b></td>
                <td align='right'><b>Full Cost</b></td>
                <td align='right'><b>Profit</b></td>
            </tr>
             <?
                //<td valign='top'>&nbsp;</td><td valign='top'>&nbsp;</td><td valign='top'>&nbsp;</td><td valign='top'>&nbsp;</td>
             echo "
                <tr>
                    <td valign='top'><b>Total</b></td>
                    <td valign='top'><b>".$counter."</b></td>                    
                    <td valign='top'>&nbsp;</td>
                    <td valign='top'>&nbsp;</td>
                    <td valign='top'>&nbsp;</td>
                    <td valign='top'>&nbsp;</td>
                    <td valign='top'>&nbsp;</td>
                    <td valign='top'>&nbsp;</td>
                    <td valign='top' align='right'>$".number_format($tot_revenue,2)."</td>
                    <td valign='top' align='right'>".number_format($tot_pcm_miles,2)."</td>
                    <td valign='top' align='right'>".number_format($tot_miles,2)."</td>
                    <td valign='top' align='right'>".number_format($tot_diff_miles,2)."</td>
                    <td valign='top' align='right'>".number_format($tot_hours,2)."</td>
                    <td valign='top' align='right'>".number_format($tot_gals,2)."</td>
                    <td valign='top' align='right'>$".number_format($tot_fuel,2)."</td>
                    <td valign='top' align='right'>$".number_format($tot_dcost,2)."</td>
                    <td valign='top' align='right'>$".number_format($tot_labor,2)."</td>
                    <td valign='top' align='right'>$".number_format($tot_hourly,2)."</td>
                    <td valign='top' align='right'>$".number_format($tot_fcost,2)."</td>
                    <td valign='top' align='right'>$".number_format($tot_profit,2)."</td>
                </tr>
            ";
             if($counter>0)
             {
                 //<td valign='top'>&nbsp;</td><td valign='top'>&nbsp;</td><td valign='top'>&nbsp;</td><td valign='top'>&nbsp;</td>
                  echo "
                    <tr>
                        <td valign='top'><b>Average</b></td>
                        <td valign='top'><b>&nbsp;</b></td>                        
                        <td valign='top'>&nbsp;</td>
                        <td valign='top'>&nbsp;</td>
                        <td valign='top'>&nbsp;</td>
                        <td valign='top'>&nbsp;</td>
                        <td valign='top'>&nbsp;</td>
                        <td valign='top'>&nbsp;</td>
                        <td valign='top' align='right'>$".number_format(($tot_revenue/$counter),2)."</td>
                        <td valign='top' align='right'>".number_format(($tot_pcm_miles/$counter),2)."</td>
                        <td valign='top' align='right'>".number_format(($tot_miles/$counter),2)."</td>
                        <td valign='top' align='right'>".number_format(($tot_diff_miles/$counter),2)."</td>
                        <td valign='top' align='right'>".number_format(($tot_hours/$counter),2)."</td>
                        <td valign='top' align='right'>".number_format(($tot_gals/$counter),2)."</td>
                        <td valign='top' align='right'>$".number_format(($tot_fuel/$counter),2)."</td>
                        <td valign='top' align='right'>$".number_format(($tot_dcost/$counter),2)."</td>
                        <td valign='top' align='right'>$".number_format(($tot_labor/$counter),2)."</td>
                        <td valign='top' align='right'>$".number_format(($tot_hourly/$counter),2)."</td>
                        <td valign='top' align='right'>$".number_format(($tot_fcost/$counter),2)."</td>
                        <td valign='top' align='right'>$".number_format(($tot_profit/$counter),2)."</td>
                    </tr>
                ";
             }
             ?>
        </table>
    </form>
<?
$pdf = ob_get_contents();
ob_end_clean();
?>
    <script type='text/javascript'>


        $().ready(function() {
            /*
              $('.tablesorter').tablesorter({
                                headers: {         				
                                     5: {sorter:'currency'},
                                     6: {sorter:'currency'},
                                     7: {sorter:'currency'},
                                     11: {sorter: false}
                                }
                                     
              });
              */
            $('.datepicker').datepicker();
        });

    </script>
<?
$link = print_contents('lane_analyzer1_'.createuuid(), $pdf);

echo "
		<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
			<a href='$link'>Printed Version</a>
		</div>
		<div style='clear:both'></div>
		
	";

echo $pdf;
?>
<? include('footer.php') ?>