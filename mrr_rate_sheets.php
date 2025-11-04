<?
//new file to process the PDF files sent for rate sheets.  Added by MRR on 7/24/2019, but main class originally developed by Chris Sherrod.
include_once('application.php');
include_once('functions_fedex.php');


include_once('vendor/autoload.php');
include_once('class_conard_rate_sheets.php');

show_errors(true, false);

//die($defaultsarray['pdftotext_path']);

$rs = new ConardRateSheets();
//$rs->renameToUniqueFilenames = false;
//$rs->archiveFiles = false;
$rs->returnFirstRsltObjOnly = true;

//find the file type...CH Robinson,Essex Geodis, quad, or Sonoco
if(!isset($_GET['pdf_comp']))      $_GET['pdf_comp']=0;
$pdf_comp=(int) $_GET['pdf_comp'];

if($pdf_comp == 0)       $rs->filenameFilter = "CHR";
if($pdf_comp == 1)       $rs->filenameFilter = "Essex";
if($pdf_comp == 2)       $rs->filenameFilter = "quad";
if($pdf_comp == 3)       $rs->filenameFilter = "Sonoco";
if($pdf_comp == 4)       $rs->filenameFilter = "Koch";
if($pdf_comp == 5)       $rs->filenameFilter = "Schneider";
if($pdf_comp == 6)       $rs->filenameFilter = "Agreement";           //Conard Logistics (really only has the rate of a load, not the rest of the details...)
if($pdf_comp == 7)       $rs->filenameFilter = "DeliverySheet";       //Conard Agreement (most of the rate sheet)
if($pdf_comp == 8)       $rs->filenameFilter = "QL";                  //Quality Logistics (QL)
if($pdf_comp == 9)       $rs->filenameFilter = "GS";                  //Goldstar
if($pdf_comp == 10)      $rs->filenameFilter = "PFG";                 //Performance Foodservices (PFG)
if($pdf_comp == 11)      $rs->filenameFilter = "JB";                  //JB Hunt
if($pdf_comp == 12)      $rs->filenameFilter = "PC";                  //Plasticycle
if($pdf_comp == 13)      $rs->filenameFilter = "Echo";                //Echo Global Logistics Inc

$valid_to_save=0;
if($pdf_comp==0)    $valid_to_save=1;        //CH Robinson
if($pdf_comp==1)    $valid_to_save=1;        //Essex
if($pdf_comp==2)    $valid_to_save=1;        //Quad
if($pdf_comp==3)    $valid_to_save=1;        //Sonoco
if($pdf_comp==4)    $valid_to_save=1;        //Koch
if($pdf_comp==5)    $valid_to_save=1;        //Schneider
if($pdf_comp==6)    $valid_to_save=1;        //Conard Logistics Agreement (really only has the rate of a load, not the rest of the details...)
if($pdf_comp==7)    $valid_to_save=1;        //Conard DeliverySheet (most of the rate sheet)
if($pdf_comp==8)    $valid_to_save=1;        //Quality Logistics (QL)
if($pdf_comp==9)    $valid_to_save=1;        //Goldstar (GS)
if($pdf_comp==10)   $valid_to_save=1;        //Performance Foodservices (PFG)
if($pdf_comp==11)   $valid_to_save=0;        //JB Hunt (JB)
if($pdf_comp==12)   $valid_to_save=1;        //Plasticycle (PC)
if($pdf_comp==13)   $valid_to_save=1;        //Echo Global Logistics Inc

//$valid_to_save=0;                          //KILL SWITCH


//$rs->outputRawContent=true;
$rslt = $rs->processRateSheets();
if(!isset($rslt))
{
     echo "<br><br>No Files matching '".$rs->filenameFilter."' Company in filename are in the directory.";
     die("<br>Done.");
}

echo "<br> This is my test for ".$rs->filenameFilter.". FileName=".$rslt->pdfFileName."";

//RSLT has all the rate sheet info in it, so once we have it all, we can add it to the load table with stops.

$load_array['cust_id']=$rslt->customerID;
$load_array['cust_name']=$rslt->nameOfCustomer;

$load_array['load_no']=$rslt->loadNumber;
$load_array['pickup_no']=$rslt->pickupNumber;

$load_array['pickup_eta']=$rslt->pickupDateTime;
$load_array['dropoff_eta']=$rslt->deliveryDateTime;
$load_array['bill_customer']=$rslt->rate;

$load_array['origin_city']="";
$load_array['origin_state']="";
$load_array['dest_city']="";
$load_array['dest_state']="";

$load_array['commodity']=$rslt->commodity;
$load_array['special_notes']="... ".$rslt->comments;
$load_array['load_notes']="";
$load_array['pdf_filename']=$rslt->pdfFileName;

$load_array['pickup_eta_appt_start'] =$rslt->pickupApptDateTime1;       //Appointment Window PU start
$load_array['pickup_eta_appt_end']   =$rslt->pickupApptDateTime2;       //Appointment Window PU end
$load_array['dropoff_eta_appt_start']=$rslt->deliveryApptDateTime1;     //Appointment Window DO start
$load_array['dropoff_eta_appt_end']  =$rslt->deliveryApptDateTime2;     //Appointment Window DO end



echo "<br><b>Customer ID</b>: ".$rslt->customerID.".";
echo "<br><b>Name of Customer</b>: ".$rslt->nameOfCustomer.".";

echo "<br><b>Loading Number</b>: ".$rslt->loadNumber.".";
echo "<br><b>Pickup Number</b>: ".$rslt->pickupNumber.".";
echo "<br><b>Bill Customer Rate</b>: $".number_format($rslt->rate,2).".";
echo "<br><b>Pickup ETA</b>: ".$rslt->pickupDateTime.".";
echo "<br><b>DropOff ETA</b>: ".$rslt->deliveryDateTime.".";
echo "<br><b>Comments/Notes</b>: ".$rslt->comments.".";

echo "<br><b>Commodity</b>: ".$rslt->commodity.".";

$stops=0;
$stop = array();

$pickups=0;
foreach($rslt->pickupObj as $pickup) {
     
     if($pdf_comp==0 || $pdf_comp==7)
     {
          if(substr_count($pickup['time'],"Appt") > 0 || substr_count($pickup['time'],"Contact") > 0 || substr_count($pickup['time'],"CHR") > 0)
          {
               $load_array['special_notes'].="  PickUp: ".$pickup['time']."";
          }
     
          if($pdf_comp==0)    $pickup['date'].=" ".mrr_trim_out_bad_times($pickup['time']);
          if($pdf_comp==0)    $pickup['date2']=$pickup['date'];
          echo "<br><b>Shipper Time</b>: ".mrr_trim_out_bad_times($pickup['time']).".";
     }
     echo "<br><b>Shipper Date</b>: ".$pickup['date'].".";
     echo "<br><b>Shipper Date2</b>: ".$pickup['date2'].".";
     echo "<br><b>Shipper No.</b>: ".$pickup['number'].".";
     echo "<br><b>Shipper Phone</b>: ".$pickup['phone'].".";
     
     //if($pdf_comp == 1)       echo "<br><b>Shipper Address Block</b>: ".$pickup['address_block'].".";
     
     
     if(trim($load_array['pickup_no'])=="")       $load_array['pickup_no']=$pickup['number'];
     
     $addr_parts=0;
     foreach($pickup['address'] as $addr) {
          echo "<br><b>Shipper Address [".$addr_parts."]</b>: ".$addr."";
          $addr_parts++;
     }
     
     if($pickups==0) {
          $load_array['origin_city']=$pickup['address'][( $addr_parts - 3 )];
          $load_array['origin_state']=$pickup['address'][( $addr_parts - 2 )];
     }
     
     $pickups++;
     //debug($pickup);
     
     if($addr_parts >= 6) {
          $stop[$stops]['name']=$pickup['address'][0];
          $stop[$stops]['address']=$pickup['address'][1];
          $stop[$stops]['address2']=$pickup['address'][2];
     }
     else {
          $stop[$stops]['name']=$pickup['address'][0];
          $stop[$stops]['address']=$pickup['address'][1];
          $stop[$stops]['address2']="";
     }
     $stop[$stops]['city']=$pickup['address'][( $addr_parts - 3 )];
     $stop[$stops]['state']=$pickup['address'][( $addr_parts - 2 )];
     $stop[$stops]['zip']=$pickup['address'][( $addr_parts - 1 )];
     $stop[$stops]['contact_phone']=$pickup['phone'];
     $stop[$stops]['ship_datetime']=$pickup['date'];
     $stop[$stops]['drop_datetime']=$pickup['date2'];
     $stop[$stops]['stop_type_id']=1;
     $stops++;
}
echo "<br><b>Origin City</b>: ".$load_array['origin_city'].".";
echo "<br><b>Origin State</b>: ".$load_array['origin_state'].".";



$dropoffs=0;
foreach($rslt->dropObj as $dropoff) {
     
     if($pdf_comp==0 || $pdf_comp==7)
     {
          if(substr_count($dropoff['time'],"Appt") > 0 || substr_count($dropoff['time'],"Contact") > 0 || substr_count($dropoff['time'],"CHR") > 0)
          {
               $load_array['special_notes'].="  DropOff: ".$dropoff['time']."";
          }
     
          if($pdf_comp==0)    $dropoff['date'].=" ".mrr_trim_out_bad_times($dropoff['time']);
          if($pdf_comp==0)    $dropoff['date2']=$dropoff['date'];
          echo "<br><b>DropOff Time</b>: ".mrr_trim_out_bad_times($dropoff['time']).".";
     }
     
     echo "<br><b>DropOff Date</b>: ".$dropoff['date'].".";
     echo "<br><b>DropOff Date2</b>: ".$dropoff['date2'].".";
     //echo "<br><b>DropOff No.</b>: ".$dropoff['number'].".";
     echo "<br><b>DropOff Phone</b>: ".$dropoff['phone'].".";
     
     
     //if($pdf_comp == 1)       echo "<br><b>Dropoff Address Block</b>: ".$dropoff['address_block'].".";
     
     $addr_parts=0;
     foreach($dropoff['address'] as $addr) {
          echo "<br><b>DropOff Address [".$addr_parts."]</b>: ".$addr."";
          $addr_parts++;
     }
     
     $load_array['dest_city']=$dropoff['address'][( $addr_parts - 3 )];
     $load_array['dest_state']=$dropoff['address'][( $addr_parts - 2 )];
     
     $dropoffs++;
     //debug($dropoff);
     
     if($addr_parts >= 6) {
          $stop[$stops]['name']=$dropoff['address'][0];
          $stop[$stops]['address']=$dropoff['address'][1];
          $stop[$stops]['address2']=$dropoff['address'][2];
     }
     else {
          $stop[$stops]['name']=$dropoff['address'][0];
          $stop[$stops]['address']=$dropoff['address'][1];
          $stop[$stops]['address2']="";
     }
     $stop[$stops]['city']=$dropoff['address'][( $addr_parts - 3 )];
     $stop[$stops]['state']=$dropoff['address'][( $addr_parts - 2 )];
     $stop[$stops]['zip']=$dropoff['address'][( $addr_parts - 1 )];
     $stop[$stops]['contact_phone']=$dropoff['phone'];
     $stop[$stops]['ship_datetime']=$dropoff['date'];
     $stop[$stops]['drop_datetime']=$dropoff['date2'];
     $stop[$stops]['stop_type_id']=2;
     $stops++;
}
echo "<br><b>Dest City</b>: ".$load_array['dest_city'].".";
echo "<br><b>Dest State</b>: ".$load_array['dest_state'].".";


if($pdf_comp==6 && $valid_to_save > 0)
{
     if($load_array['bill_customer'] > 0 && trim($load_array['load_no'])!="")
     {    //update the specific rate amount for the lading number given.
          $sqlu="update load_handler set
                    actual_bill_customer='".sql_friendly($load_array['bill_customer'])."'
                where load_number='".sql_friendly(trim($load_array['load_no']))."'
                    and created_by_rate_sheet=1
          ";
          simple_query($sqlu);
     
          echo "<br>Updated Conard Logistics Load ".trim($load_array['load_no'])." with the rate $".number_format($load_array['bill_customer'],2)."</a>";
     }
     else
     {
          echo "<br>Error: Cannot update the Conard Load using the Conard Logistics Rate Sheet (Agreement).  <i>Note: DeliverySheet document must be processed first.</i>";
     }
     $valid_to_save=0;        //turn off making a new load for this one...the load should aready be created by a corresponding Delivery Sheet.
}
elseif($pdf_comp==6)
{
     echo "<br>Error: Not Ready to update the Conard Load using the Conard Logistics Rate Sheet (Agreement).  <i>Note: DeliverySheet document must be processed first.</i>";
}

if((int) $load_array['cust_id']==0)     $valid_to_save=0;        //invalid... no customer defined...using the ID so that name spellings don't matter.
if($stops < 2)                          $valid_to_save=0;        //invalid... not enough stops.  Should have at least 2 stops.

if($valid_to_save > 0) {
     //saved a load so spit it out.
     $new_load_id=mrr_create_load_from_pdf($load_array,$stop);
     echo "<br>New Load ID is <a href='https://trucking.conardtransportation.com/manage_load.php?load_id=".$new_load_id."' target='_blank'>".$new_load_id."</a>";
}
elseif($pdf_comp!=6)
{
      echo "<br>Error: this PDF is not valid to save as a Conard Load.";          //<br><hr><br>".debug($rslt)."<br>
}


echo "<br><hr><br>";
debug($rslt);


function mrr_trim_out_bad_times($time_val)
{
     $time_val = str_replace("Appt.","",$time_val);
     $time_val = str_replace("Appt","",$time_val);
     $time_val = str_replace("Contact CHR for scheduling","00:00",$time_val);
     return trim($time_val);
}

function get_text_from_pdf($filename_with_path)
{
     global $defaultsarray;
     
     $tmp_filename = "temp\\".createuuid()."txt";
     
     $cmd = $defaultsarray['pdftotext_path'].'\pdftotext.exe -simple "'.$filename_with_path.'" "'.__DIR__.'\\'.$tmp_filename.'"';
     
     exec($cmd, $output, $rslt);
     
     $output = file_get_contents($tmp_filename);
     unlink($tmp_filename);
     
     $return_array = array('cmd_result' => $rslt,
          'content' => $output);
     
     return $return_array;
}

function mrr_create_load_from_pdf($load_array,$stop_array)
{
	global $datasource;

     //default settings used for budget items
     $mrr_average_mpg=mrr_get_default_variable_setting('average_mpg');
     $mrr_billable_days_in_month=mrr_get_default_variable_setting('billable_days_in_month');
     $mrr_labor_per_hour=mrr_get_default_variable_setting('labor_per_hour');
     $mrr_labor_per_mile=mrr_get_default_variable_setting('labor_per_mile');
     $mrr_labor_per_mile_team=mrr_get_default_variable_setting('labor_per_mile_team');
     $mrr_local_driver_workweek_hours=mrr_get_default_variable_setting('local_driver_workweek_hours');
     $mrr_tractor_maint_per_mile=mrr_get_default_variable_setting('tractor_maint_per_mile');
     $mrr_trailer_maint_per_mile=mrr_get_default_variable_setting('trailer_maint_per_mile');
     
     $mrr_truck_accidents_per_mile=mrr_get_default_variable_setting('truck_accidents_per_mile');
     $mrr_tires_per_mile=mrr_get_default_variable_setting('tires_per_mile');
     $mrr_mileage_expense_per_mile=mrr_get_default_variable_setting('mileage_expense_per_mile');
     $mrr_misc_expense_per_mile=mrr_get_default_variable_setting('misc_expense_per_mile');
     
     $mrr_cargo_insurance=mrr_get_option_variable_settings('Cargo Insurance');
     $mrr_general_liability=mrr_get_option_variable_settings('General Liability');
     $mrr_liability_phy_damage=mrr_get_option_variable_settings('Liability/Phy Damage');
     $mrr_payroll___admin=mrr_get_option_variable_settings('Payroll & Admin');
     $mrr_rent=mrr_get_option_variable_settings('Rent');
     $mrr_tractor_lease=mrr_get_option_variable_settings('Tractor Lease');
     $mrr_trailer_expense=mrr_get_option_variable_settings('Trailer Expense');
     $mrr_trailer_lease=mrr_get_option_variable_settings('Trailer Lease');
     $mrr_misc_expenses=mrr_get_option_variable_settings('Misc Expenses');
     
     $cust_id=$load_array['cust_id'];
     $cust_name=$load_array['cust_name'];
     
     $load_number=$load_array['load_no'];
     $pickup_no=$load_array['pickup_no'];
     
     $stop_city=$load_array['origin_city'];
     $stop_state=$load_array['origin_state'];
     $stop_city_x=$load_array['dest_city'];
     $stop_state_x=$load_array['dest_state'];
     
     $commodity=$load_array['commodity'];
     $spec_notes=$load_array['special_notes'];
     $mrr_load_notes=$load_array['load_notes'];
     $filenamer=$load_array['pdf_filename'];
     
     $bill_customer=$load_array['bill_customer'];
     
     $spec_notes=str_replace(chr(9), " ",$spec_notes);       $spec_notes=str_replace("\t", " ",$spec_notes);
     $spec_notes=str_replace(chr(10), " ",$spec_notes);      $spec_notes=str_replace("\r", " ",$spec_notes);
     $spec_notes=str_replace(chr(13), " ",$spec_notes);      $spec_notes=str_replace("\n", " ",$spec_notes);
     
     
     
     $sql = "
		insert into load_handler
			(origin_city,
			origin_state,
			dest_city,
			dest_state,
			linedate_added,
			special_instructions,
			customer_id,
			load_number,
			auto_created,
         		budget_average_mpg,
			budget_days_in_month,
			budget_labor_per_hour,
			budget_labor_per_mile,
			budget_labor_per_mile_team,
			budget_driver_week_hours,
			budget_tractor_maint_per_mile,
			budget_trailer_maint_per_mile,
			budget_truck_accidents_per_mile,
			budget_tires_per_mile,
			budget_mileage_exp_per_mile,
			budget_misc_exp_per_mile,
			budget_cargo_insurance,
			budget_general_liability,
			budget_liability_damage,
			budget_payroll_admin,
			budget_rent,
			budget_tractor_lease,
			budget_trailer_exp,
			budget_trailer_lease,
			budget_misc_exp,
			budget_active_trucks,
			budget_active_trailers,
			budget_day_variance,
			pickup_number,
			delivery_number,
			feded_edi_invoice_file,
			fedex_edi_invoice_file_text,
			fedex_edi_invoice_mileage,
			lynnco_edi_invoice_file,
			lynnco_edi_invoice_file_text,
			lynnco_edi_invoice_mileage,
			lynnco_edi,
			commodity,
			billing_notes,
			created_by_rate_sheet,
			rate_sheet_file_name,
			driver_notes)
		
		values ('".sql_friendly($stop_city)."',
			'".sql_friendly($stop_state)."',
			'".sql_friendly($stop_city_x)."',
			'".sql_friendly($stop_state_x)."',
			now(),
			'".sql_friendly("".sql_friendly(trim($spec_notes)))."',
			'".sql_friendly($cust_id)."',
			'".sql_friendly(trim($load_number))."',
			1,
			'".sql_friendly($mrr_average_mpg)."',
			'".sql_friendly($mrr_billable_days_in_month)."',
			'".sql_friendly($mrr_labor_per_hour)."',
			'".sql_friendly($mrr_labor_per_mile)."',
			'".sql_friendly($mrr_labor_per_mile_team)."',
			'".sql_friendly($mrr_local_driver_workweek_hours)."',
			'".sql_friendly($mrr_tractor_maint_per_mile)."',
			'".sql_friendly($mrr_trailer_maint_per_mile)."',
			'".sql_friendly($mrr_truck_accidents_per_mile)."',
			'".sql_friendly($mrr_tires_per_mile)."',
			'".sql_friendly($mrr_mileage_expense_per_mile)."',
			'".sql_friendly($mrr_misc_expense_per_mile)."',
			'".sql_friendly($mrr_cargo_insurance)."',
			'".sql_friendly($mrr_general_liability)."',
			'".sql_friendly($mrr_liability_phy_damage)."',
			'".sql_friendly($mrr_payroll___admin)."',
			'".sql_friendly($mrr_rent)."',
			'".sql_friendly($mrr_tractor_lease)."',
			'".sql_friendly($mrr_trailer_expense)."',
			'".sql_friendly($mrr_trailer_lease)."',
			'".sql_friendly($mrr_misc_expenses)."',
			'".sql_friendly( get_active_truck_count() )."',
			'".sql_friendly( get_active_trailer_count() )."',
			'".sql_friendly( get_daily_cost(0,0) )."',
			'".sql_friendly(trim($pickup_no))."',
			'',
			'',
			'',
			0,
			'',
			'',
			0,
          	0,
		     '".sql_friendly(trim($commodity))."',
		     '".sql_friendly(trim($mrr_load_notes))."',
		     1,
		     '".sql_friendly($filenamer)."',
		     '')
	";
     simple_query($sql);
     $load_handler_id = mysqli_insert_id($datasource);
     
     // get the base_rate expense type
     $sql = "
		select ov.id
		from option_values ov, option_cat oc
		where ov.cat_id = oc.id
			and ov.deleted = 0
			and oc.cat_name = 'expense_type_lh'
			and ov.fname = 'base_rate'
	";
     $data_expense_id = simple_query($sql);
     $row_expense_id = mysqli_fetch_array($data_expense_id);
     
     // create the base rate expense
     $sql = "
		insert into load_handler_actual_var_exp
			(load_handler_id,
			expense_type_id,
			expense_amount)
			
		values ('$load_handler_id',
			'".$row_expense_id['id']."',
			'".sql_friendly($bill_customer)."')
		";
     simple_query($sql);
     
     $sql = "
		update load_handler set
			actual_bill_customer='".sql_friendly($bill_customer)."'
		where id='".sql_friendly($load_handler_id)."'
	";
     simple_query($sql);
     
     // create the stops
     $stop_counter = 0;
     $pickup_date=0;
     $dropoff_date=0;
     
     foreach($stop_array as $stop)
     {
          $stop_counter++;
          if(!isset($stop['delivery_datetime'])) 	$stop['delivery_datetime'] = 0;
          if(!isset($stop['load_datetime'])) 	$stop['load_datetime'] = 0;
          //$stop_type_id=2;
     
          $pickup_eta=strtotime($stop['ship_datetime']);
          $dropoff_eta=strtotime($stop['drop_datetime']);
          
          $appt_flag=0;
          $appt_start=0;
          $appt_end=0;
          if($stop['drop_datetime']!=$stop['ship_datetime'] && trim($stop['drop_datetime'])!="") 
          {
               $appt_flag=1;
               $appt_start=strtotime($stop['ship_datetime']);
               $appt_end=strtotime($stop['drop_datetime']);
          }
          
          //$dropoff_date=strtotime($stop['ship_datetime']);
          
          if($stop_counter == 1 || $stop['stop_type_id'])
          {	// this is the first stop set up the pickup eta
               $pickup_date=strtotime($stop['ship_datetime']);
               
               $sqlu = "
					update load_handler set
					   linedate_pickup_eta = '".date("Y-m-d H:i:s", $pickup_date)."'
					where id = '$load_handler_id'
				";
               simple_query($sqlu);
               //$stop_type_id=1;
          }
          
          $sql = "
			insert into load_handler_stops
				(load_handler_id,
				shipper_name,
				shipper_address1,
				shipper_address2,
				shipper_city,
				shipper_state,
				shipper_zip,
				stop_type_id,
				linedate_added,
				stop_phone,
				linedate_pickup_eta,
				linedate_dropoff_eta,
				linedate_pickup_pta,
				linedate_dropoff_pta,
				
				appointment_window,
				linedate_appt_window_start,
				linedate_appt_window_end,
				
				deleted)
				
			values ('$load_handler_id',
				'".sql_friendly($stop['name'])."',
				'".sql_friendly($stop['address'])."',
				'".sql_friendly($stop['address2'])."',
				'".sql_friendly($stop['city'])."',
				'".sql_friendly($stop['state'])."',
				'".sql_friendly($stop['zip'])."',
				'".sql_friendly($stop['stop_type_id'])."',
				now(),
				'".sql_friendly($stop['contact_phone'])."',
				'".date("Y-m-d H:i:s", $pickup_eta)."',
				'".date("Y-m-d H:i:s", $dropoff_eta)."',
				'0000-00-00 00:00:00',
				'0000-00-00 00:00:00',
				
				'".sql_friendly($appt_flag)."',
				'".date("Y-m-d H:i:s", $appt_start)."',
				'".date("Y-m-d H:i:s", $appt_end)."',				
				
				0)
		";
          simple_query($sql);
          //$appt_flag=1;
          //$appt_start=strtotime($stop['ship_datetime']);
          //$appt_end=strtotime($stop['drop_datetime']);
          
          $sqlu = "
					update load_handler_stops set
					   appointment_window=0,
				        linedate_appt_window_start='0000-00-00 00:00:00',
				        linedate_appt_window_end='0000-00-00 00:00:00'
					where linedate_appt_window_end < '2010-01-01 00:00:00' 
					   and load_handler_id>='112900'
				";
          simple_query($sqlu);
     
          $sqlu = "
					update load_handler set
					   linedate_dropoff_eta = '".date("Y-m-d H:i:s", $pickup_eta)."'
					where id = '$load_handler_id'
				";
          simple_query($sqlu);
     }
     
     update_origin_dest($load_handler_id);
     
     //now attach the original file to the new load as well. This should save time scanning/attaching the document later...and we already know which one it is.
     getcwd();
     $target_dir = "../incoming_rate_sheets/";
     chdir($target_dir);
     
     $original_file = trim($load_array['pdf_filename']);
     if(substr_count(trim($load_array['pdf_filename']),"\\") == 0 && trim($original_file)!="")
     {
          //full path give for file...
          $original_file = $target_dir . trim($load_array['pdf_filename']);
     }
     
     $target_dir2 = "../www/documents/";
     $new_file="".$load_handler_id."lr-RateSheet".date("Ymdhis",time()).".pdf";
     $new_file_path = $target_dir2 .$new_file;
     
     /*
     $base_path=mrr_get_default_variable_setting('base_path');
     
     $new_file_path=$base_path."www/documents/".$new_file;
     $original_file=trim($load_array['pdf_filename']);
     if(substr_count(trim($load_array['pdf_filename']),"\\") == 0 && trim($original_file)!="")
     {
          //full path give for file...
          $original_file=$base_path."incoming_rate_sheets"."\\".trim($load_array['pdf_filename']);
     }
     */
     if(trim($original_file)!="")
     {    //attach the file based on the file path created or stored.
          //if(copy($original_file, $new_file_path))
          if(rename($original_file, $new_file_path))
          {    //save it to the attachments/log.
               $filesize = filesize($new_file_path);
               $file_ext = pathinfo($new_file_path, PATHINFO_EXTENSION);
               
               $sql = "
     		     insert into attachments
     			    (user_id,
     			    linedate_added,
     			    fname,
     			    filesize,
     			    file_ext,
     			    section_id,
     			    xref_id,
     			    deleted,
     			    public_name,
     			    result,
     			    debug_info,
     			    cat_id)
     			    
     		     values ('1',
     			     now(),
     			     '".sql_friendly($new_file)."',
     			     '".sql_friendly($filesize)."',
     			     '".sql_friendly($file_ext)."',
     			     '8',
     			     '".sql_friendly($load_handler_id)."',
     			     0,
     			     '".sql_friendly($new_file)."',
     			     1,
     			     '',
     			     0)
     	     ";
               simple_query($sql);
          }
     }
     
     mrr_repair_gps_points_on_load($load_handler_id);
     
     return $load_handler_id;
}
?>