<? include('application.php') ?>
<? include_once('functions_koch.php') ?>
<?
    $sql = "
            update load_handler_stops set 
                latitude='40.422767',
                longitude='-94.5505656' 
            where shipper_address1='4200 Dahlberg Dr' 
                and latitude=''
                and longitude='' 
            order by id desc
        ";        //40.422767,-94.5505656
    simple_query($sql);
	
	// process 204's (load tender request) from fedex

	$in_path = $defaultsarray['koch_edi_path'];

	$descriptor = array();
	$descriptor['ISA'] = 'Interchange Control Header';
	$descriptor['GS'] = 'Functional Group Header';
	$descriptor['ST'] = 'Transaction set header';
	$descriptor['B2A'] = 'Set Purpose';
	$descriptor['B2'] = 'Beginning Segment for Shipment Information Transaction';
	$descriptor['L11'] = 'Business Instructions and Reference Number';
	$descriptor['G62'] = 'Date/Time';
	$descriptor['N1'] = 'Name';
	$descriptor['N3'] = 'Address';
	$descriptor['N4'] = 'Geographical Information';
	$descriptor['G61'] = 'Contact';
	$descriptor['S5'] = 'Stop off details';
	$descriptor['L3'] = 'Total weight and charges';
	$descriptor['SE'] = 'Transaction set trailer (end of transaction)';
	$descriptor['GE'] = 'Functional Group Trailer';
	$descriptor['IEA'] = 'Interchange Control Trailer';
	$descriptor['AK2'] = 'Part of the 997 return file for a 210';
	// AK2 sample values: 990 - confirmation from a load response
	//				  210 - confirmation from an invoice
	
	$d = dir($in_path);
	while (false !== ($entry = $d->read())) 
	{
		if(!is_dir($in_path.$entry)) 
		{
	   		echo $entry."<br>";
	   		edi_koch_process_file($entry);
	   	}
	}
		
	function edi_koch_process_file($file) 
	{
		global $descriptor;
		global $defaultsarray;
		global $in_path;

		echo "Processing file: $file";
		
		$fcontents = file_get_contents($in_path.$file);
         
        $fcontents = str_replace(">","",$fcontents);
        $fcontents = str_replace("~",chr(10),$fcontents);
		$fcontents = str_replace(chr(13),"",$fcontents);
		$line_array = explode(chr(10), $fcontents);
         
        $info['mrr_load_notes']="";
        $info['spec_notes'] = "";
        $info['pickup_no'] = "";
        $info['commodity'] = "";
         
        $info['shipper_datetime'] = "";
        $info['consignee_datetime'] = "";
         
        $info['mrr_billing_amount'] = "";
        $info['mrr_billing_line'] = "";

		//var_dump($line_array);

		echo "<table>";
		foreach($line_array as $line) {
			$line_part_array = explode("*",$line);
			
			// stop entry
			if($line_part_array[0] == 'SE') {
				$info['mrr_file_name']=trim($file);
				process_edi_load($info, $file, $stop_array);
				
				unset($info);
				unset($stop_array);
				$process_stop = false;
			} else if($line_part_array[0] == 'S5' || $process_stop) {
				
				if($line_part_array[0] == 'S5') {
					$stop_number = $line_part_array[1];
				
					$process_stop = true;
					$stop_array[$stop_number] = array();
					$stop_array[$stop_number]['stop_type'] = $line_part_array[2];
				}
				
				/*
				//Sample 204 file from KOCH... mainly to compare with the 204 from LynnCo...MRR
				ISA*00*          *00*          *02*KLOG           *02*SDPN           *220928*0933*U*00401*000000001*0*T*>~
				    GS*SM*KLOG*SDPN*20220928*0933*1*X*004010~
				    ST*204*0001~
				    B2**SDPN**T2604874*L*CC~
				    B2A*00~
				    L11*5882206*PO~
				    L11*OTH*ZZ*LOAD 0700-1400 FCFSDELIVER 9/28; 0900AMTIME SENSITIVE~
				    PLD*0**L*25000~
				    N1*SH*STORE OPENING SOLUTIONS~
				    N3*800 MIDDLE TENNESSEE BLVD*DOCK B~
				    N4*MURFREESBORO*TN*37129*US~
				    G61*SH*SHIPPER CONTACT*TE*615-867-0858~
				    N1*CN*TRACTOR SUPPLY #1176~
				    N3*5500 MCCLELLAN BLVD~
				    N4*ANNISTON*AL*36206*US~
				    G61*CN*CONSIGNEE CONTACT*TE*256-820-3385~S5*1*LD~
				    G62*69*20220927*I*070000*LT~
				    G62*38*20220927*K*140000*LT~
				    N1*SF*STORE OPENING SOLUTIONS~
				    N3*800 MIDDLE TENNESSEE BLVD*DOCK B~
				    N4*MURFREESBORO*TN*37129*US~
				    G61*SF*CONTACT NUMBER*TE*615-867-0858~
				    S5*2*UL~
				    G62*69*20220928*I*090000*LT~
				    G62*38*20220928*K*090000*LT~
				    N1*ST*TRACTOR SUPPLY #1176~
				    N3*5500 MCCLELLAN BLVD~
				    N4*ANNISTON*AL*36206*US~
				    G61*CN*CONSIGNEE CONTACT*TE*256-820-3385~
				    L3*25000*G*********0~
				    SE*30*0001~
				    GE*1*1~
				    IEA*1*000000001~				
				*/
				
				if($line_part_array[0] == 'N1') $stop_array[$stop_number]['name'] = $line_part_array[2];
				if($line_part_array[0] == 'N1') $stop_array[$stop_number]['address2'] = $line_part_array[4];
				if($line_part_array[0] == 'N3') $stop_array[$stop_number]['address'] = $line_part_array[1];
				if($line_part_array[0] == 'N4') $stop_array[$stop_number]['city'] = $line_part_array[1];
				if($line_part_array[0] == 'N4') $stop_array[$stop_number]['state'] = $line_part_array[2];
				if($line_part_array[0] == 'N4') $stop_array[$stop_number]['zip'] = $line_part_array[3];
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '64') $stop_array[$stop_number]['date'] = $line_part_array[2];
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '64') $stop_array[$stop_number]['time'] = $line_part_array[4];
				
				/*
				if($line_part_array[0] == 'G62' && $line_part_array[1] == 'CL') 
				{
					$stop_array[$stop_number]['load_datetime'] = strtotime($line_part_array[2].' '.$line_part_array[4]);
					//$stop_array[$stop_number]['load_date'] = $line_part_array[2];
					//$stop_array[$stop_number]['load_time'] = $line_part_array[4];
				}
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '67') 
				{
					$stop_array[$stop_number]['delivery_datetime'] = strtotime($line_part_array[2].' '.$line_part_array[4]);
					//$stop_array[$stop_number]['deliver_date'] = $line_part_array[2];
					//$stop_array[$stop_number]['deliver_time'] = $line_part_array[4];
				}
				*/
                if($line_part_array[0] == 'G62' && $line_part_array[1] == '69')
                {
                      $timer_x=trim($line_part_array[4]);
                      $hrs=substr($timer_x,0,2);
                      $mns=substr($timer_x,2,2);
                      $timer_x=$hrs.":".$mns."";
                      
                      $stop_array[$stop_number]['load_datetime'] = strtotime($line_part_array[2].' '.$timer_x);
                      $stop_array[$stop_number]['load_date'] = $line_part_array[2];
                      $stop_array[$stop_number]['load_time'] = $timer_x;
     
                      $stop_array[$stop_number]['date'] = $line_part_array[2];
                      $stop_array[$stop_number]['time'] = $line_part_array[4];
     
                      $stop_array[$stop_number]['appt_1_date'] = $line_part_array[2];
                      $stop_array[$stop_number]['appt_1_time'] = $timer_x;
     
                      if($info['shipper_datetime']=="") 
                      {
                          //$stop_array[$stop_number]['load_datetime'] = strtotime($line_part_array[2].' '.$timer_x);
                          //$stop_array[$stop_number]['load_date'] = $line_part_array[2];
                          //$stop_array[$stop_number]['load_time'] = $timer_x;
                          
                          $info['shipper_datetime'] = "".strtotime($line_part_array[2].' '.$timer_x)."";
                      }
                }
                if($line_part_array[0] == 'G62' && $line_part_array[1] == '38')
                {
                      $timer_x=trim($line_part_array[4]);
                      $hrs=substr($timer_x,0,2);
                      $mns=substr($timer_x,2,2);
                      $timer_x=$hrs.":".$mns."";
                      
                      $stop_array[$stop_number]['delivery_datetime'] = strtotime($line_part_array[2].' '.$timer_x);
                      $stop_array[$stop_number]['deliver_date'] = $line_part_array[2];
                      $stop_array[$stop_number]['deliver_time'] = $timer_x;
     
                      $stop_array[$stop_number]['date'] = $line_part_array[2];
                      $stop_array[$stop_number]['time'] = $line_part_array[4];
     
                      $stop_array[$stop_number]['appt_2_date'] = $line_part_array[2];
                      $stop_array[$stop_number]['appt_2_time'] = $timer_x;
     
                      $info['consignee_datetime'] = "".strtotime($line_part_array[2].' '.$timer_x)."";
                }
				
				if($line_part_array[0] == 'G61') {
					$stop_array[$stop_number]['contact_name'] = $line_part_array[2];
					$stop_array[$stop_number]['contact_phone'] = $line_part_array[4];
					$process_stop = false;
				}
                 
                 if($line_part_array[0] == 'L3')
                 {
                      $info['bill_customer'] = $line_part_array[3];
                      $info['mrr_billing_amount'] = "(3) ".$line_part_array[3].".";  // (4) ".$line_part_array[4]." (5)".$line_part_array[5]."
                      
                      $info['mrr_billing_line']=trim($line);
                 }
                 if($line_part_array[0] == 'L11' && trim($line_part_array[3])=="Pickup Number")     $info['pickup_no'] = trim($line_part_array[1]);	//." ".trim($line_part_array[2])." ".trim($line_part_array[3])."";
                 if($line_part_array[0] == 'L5') 									                $info['commodity'] = trim($line_part_array[2]);	//." ".trim($line_part_array[2])." ".trim($line_part_array[3])."";
                 
                 if($line_part_array[0] == 'L11' && trim($line_part_array[1])=="OTH" && trim($line_part_array[2])=="ZZ")
                 {
                      $info['spec_notes']=trim($line_part_array[3]);
                 }       
			} 
			else 
			{
				if($line_part_array[0] == 'N1') $info['name'] = $line_part_array[2];
				if($line_part_array[0] == 'N3') $info['address'] = $line_part_array[1];
				if($line_part_array[0] == 'N4') $info['city'] = $line_part_array[1];
				if($line_part_array[0] == 'N4') $info['state'] = $line_part_array[2];
				if($line_part_array[0] == 'N4') $info['zip'] = $line_part_array[3];
				if($line_part_array[0] == 'ST') $info['set_control_number'] = $line_part_array[2];
				if($line_part_array[0] == 'ST') $info['edi_file_type'] = $line_part_array[1];
				if($line_part_array[0] == 'B2') $info['load_number'] = $line_part_array[4];
				if($line_part_array[0] == 'B2A') $info['set_purpose'] = $line_part_array[1];
				if($line_part_array[0] == 'B2A') $info['set_purpose2'] = $line_part_array[2];
				if($line_part_array[0] == 'G61') $info['contact_name'] = $line_part_array[2];
				if($line_part_array[0] == 'G61') $info['contact_phone'] = $line_part_array[4];
				//if($line_part_array[0] == 'L3') $info['bill_customer'] = $line_part_array[5];
                if($line_part_array[0] == 'L3')
                {
                      $info['bill_customer'] = $line_part_array[3];
                      $info['mrr_billing_amount'] = "(3) ".$line_part_array[3].".";  // (4) ".$line_part_array[4]." (5)".$line_part_array[5]."
                      
                      $info['mrr_billing_line']=trim($line);
                }
				if($line_part_array[0] == 'AK2') $info['edi_return_type'] = $line_part_array[1];
				if($line_part_array[0] == 'AK2') $info['edi_return_load_id'] = $line_part_array[2];
                 
                if($line_part_array[0] == 'NTE') 		$info['spec_notes'] = " ...".trim($line_part_array[2]);
                 
                if($line_part_array[0] == 'L11' && trim($line_part_array[1])=="OTH" && trim($line_part_array[2])=="ZZ")
                {
                     $info['spec_notes']=trim($line_part_array[3]);
                }
			}
						
			echo "<tr>
					<td>".$descriptor[$line_part_array[0]]."</td>
					<td>$line</td>
				</tr>
			";			
		}
		echo "</table>";
		
		
		echo "<br>Info Type=".$info['edi_file_type']."<br>";
		print_r($info);
		
		//$info['mrr_file_name']=trim($file);		
		rename($in_path.$file,$in_path."backup/".$file);		
	}
		
	function process_edi_load($info, $file, $stop_array) 
	{
		global $in_path;
		global $defaultsarray;
		global $datasource;
		
		//d($stop_array);
		/*
		echo "<pre>";
		print_r($stop_array);
		echo "</pre>";
		die;
		*/
		
		if($info['edi_file_type'] == '997') 
		{
			// return confirmation file
			if($info['edi_return_type'] == '210' && $info['edi_return_load_id'] > 0) 
			{
				// return for an invoice
				$sql = "
					update load_handler
					set linedate_edi_invoice_response = now()
					where id = '".sql_friendly($info['edi_return_load_id'])."'
					limit 1
				";
				simple_query($sql);
			}			
			return false;			
		} 
		elseif($info['edi_file_type'] != '204') 
		{
			// not a 204, move to the 'other' directory
			//rename($in_path.$file,$in_path."other/".$file);
			/*
			echo "<pre>";
			print_r($info);
			echo "</pre>";
			die("got here2: ($info[edi_file_type])");
			*/
			return false;
		}
		
		//die('got here1');
		
		
		$sql = "
			select id			
			from customers
			where name_company = 'Koch Logistics '
				and deleted = 0
		";
		$data_cust = simple_query($sql);
		$row_cust = mysqli_fetch_array($data_cust);
		
		
		// check to make sure we haven't already processed this load
		$sql = "
			select id			
			from load_handler
			where load_number = '".sql_friendly($info['load_number'])."'
				and customer_id = '".sql_friendly($row_cust['id'])."'
				and lynnco_edi <=0
				and koch_edi > 0
				and deleted = 0
		";
		$data_check = simple_query($sql);
		
		if(mysqli_num_rows($data_check)) 
		{
			// already processed this load
			echo "Already processed Koch load: $info[load_number]<br>";
			return false;
		}
		
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
				koch_edi,
				commodity,
				billing_notes,
				edi_source_file,				
				driver_notes)
			
			values ('".sql_friendly($stop_array[1]['city'])."',
				'".sql_friendly($stop_array[1]['state'])."',
				'".sql_friendly($stop_array[count($stop_array)]['city'])."',
				'".sql_friendly($stop_array[count($stop_array)]['state'])."',
				now(),
				'".sql_friendly("Auto Created from Koch EDI file".trim($info['spec_notes']))."',
				'".sql_friendly($row_cust['id'])."',
				'".sql_friendly($info['load_number'])."',
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
				'".sql_friendly(trim($info['pickup_no']))."',
				'',
				'',
				'',
				0,
				'',
				'',
				0,			
				1,
				'".sql_friendly(trim($info['commodity']))."',
				'".sql_friendly(trim($info['mrr_load_notes']))."',
				'".sql_friendly(trim($info['mrr_file_name']))."',
				'')
		";
		simple_query($sql);
		$load_handler_id = mysqli_insert_id($datasource);
		
		//'Auto Created from Koch EDI file - respond by: ".date("M j, Y h:i a", strtotime($stop_array[1]['date']. ' '.$stop_array[1]['time']))."',
		
        echo "<br><br>Shipping Out: ".date("m/d/Y H:i",$info['shipper_datetime']).", Delivery time: ".date("m/d/Y H:i",$info['consignee_datetime']).". PU No. ".$info['pickup_no'].". Commodity=".trim($info['commodity']).".";
		
		if(isset($info['bill_customer'])) 
		{
            if(substr_count(trim($info['bill_customer']),".")==0)
            {
                  $temp_x=(int) trim($info['bill_customer']);
                  $temp_x= $temp_x / 100;
                  $info['bill_customer']="".$temp_x."";
            }
             
            echo " ----- Bill Customer: $".$info['bill_customer'].".";
             
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
					'$row_expense_id[id]',
					'".sql_friendly($info['bill_customer'])."')
			";
			simple_query($sql);
             
            $sql = "
				update load_handler set
					actual_bill_customer = '".sql_friendly($info['bill_customer'])."'
				where id = '$load_handler_id'
				limit 1
			";
            simple_query($sql);
		}
		
		// create the stops
		$stop_counter = 0;
        $stop_type_id=1;
		foreach($stop_array as $stop) 
		{
			$stop_counter++;
			
			if($stop_counter==count($stop_array))   $stop_type_id=2;        //make sure that the last stop is always the consignee stop.
			
			if(!isset($stop['delivery_datetime']))  $stop['delivery_datetime'] = date("Y-m-d H:i:00",strtotime("+6 hours",time()));
			if(!isset($stop['load_datetime']))      $stop['load_datetime'] = date("Y-m-d H:i:00",strtotime("+6 hours",time()));;
			
			//                    && ($stop['stop_type'] == 'LD' || $stop['stop_type'] == 'UL')
			if($stop_counter == 1 ) 
			{	// this is the first stop set up the pickup eta                     $stop['load_datetime']
				$sql = "
					update load_handler set
						linedate_pickup_eta = '".date("Y-m-d H:s", strtotime($info['shipper_datetime']))."'
					where id = '$load_handler_id'
					limit 1
				";
				simple_query($sql);
			}
            
			//added the appointment window processing to this for Koch Logistics EDI loads...12/13/2022...MRR
			$appt_win_on=0;
			$appt_win_start="".strtotime($stop['appt_1_date'].' '.$stop['appt_1_time'])."";
            $appt_win_end=  "".strtotime($stop['appt_2_date'].' '.$stop['appt_2_time'])."";            
            if(trim($appt_win_start)=="") 
            {
                 if($stop_type_id == 1)     $appt_win_start=$info['shipper_datetime'];
            }
            if(trim($appt_win_end)=="")
            {
                  if($stop_type_id == 1)    $appt_win_end = $info['consignee_datetime'];
            }
            if($appt_win_start != $appt_win_end)      $appt_win_on=1;
            			
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
					'".($stop_type_id==1 ? '1' : '2')."',
					now(),
					'".sql_friendly($stop['contact_phone'])."',
					'".($stop_type_id==1 ? date("Y-m-d H:i", $info['shipper_datetime']) : date("Y-m-d H:i", $info['consignee_datetime'])).":00',
					'".sql_friendly($appt_win_on)."',					
					'".date("Y-m-d H:i", $appt_win_start).":00',
					'".date("Y-m-d H:i", $appt_win_end).":00',
					0)
			";
			simple_query($sql);
             
            $stop_type_id=2;
            //if($stop_counter==count($stop_array))   $stop_type_id=2;        //make sure that the last stop is always the consignee stop.
		}
		
		update_origin_dest($load_handler_id);

		//$use_control_number = "000000001";
		$use_control_number = str_pad($load_handler_id, 9, "0", STR_PAD_LEFT);
		$comp_code="KLOG";     
		
		// create the 990 response
		$rval = "ISA*00*          *00*          *02*".$defaultsarray['edi_scac_code']."           *02*".$comp_code."           *".date("ymd")."*".date("Hi")."*X*00401*$use_control_number*0*P*>\n";
		$rval .= "GS*GF*".$defaultsarray['edi_scac_code']."*".$comp_code."*".date("Ymd")."*".date("Hi")."*$use_control_number*X*004010\n";
		$rval .= "ST*990*$use_control_number\n";
		$rval .= "B1*".$defaultsarray['edi_scac_code']."*$info[load_number]*".date("Ymd")."*A\n";
        //$rval .= "B1*".($info['set_purpose'] == '14' ? 'ADV' : 'REG')."*$info[load_number]*".date("Ymd")."*A\n";
		$rval .= "N9*CN*$load_handler_id\n";
		$rval .= "N9*BN**".$defaultsarray['edi_company_name']."\n";
		$rval .= "SE*5*$use_control_number\n";
		$rval .= "GE*1*$use_control_number\n";
		$rval .= "IEA*1*$use_control_number\n";
         
        $rval=str_replace("\n","~",$rval);

		$out_file = "990_$info[load_number]_".time().".txt";
		file_put_contents($in_path."/out/".$out_file, $rval);
				
		// send the file via FTP to our EDI provider		
		$sql = "
			update load_handler set					
				koch_edi_input_file='/edi/koch_in/out/',
				koch_edi_output_file='".trim($out_file)."'
			where id = '".sql_friendly($load_handler_id)."'
		";
		simple_query($sql);
		
		
		
		//Send an email.
		$subject="New EDI Load for Koch Logistics Created";
		$msg="This is to let you know that a new load ($info[load_number]) from Koch Logistics EDI system has been created as Conard Load <a href='https://trucking.conardtransportation.com/manage_load.php?load_id=".$load_handler_id."'>".$load_handler_id."</a>.";		
		//mrr_trucking_sendMail($defaultsarray['company_email_address'],"Dispatch",$defaultsarray['company_email_address'],"Koch Logistics EDI",$defaultsarray['peoplenet_hot_msg_cc'],"",$subject,$msg,$msg);
		
		// log the upload
		$sqlu = "
			update load_handler set
				linedate_edi_response_sent = now()
			where id = '".sql_friendly($load_handler_id)."'
		";
		simple_query($sqlu);
		
		if(send_koch_edi('/Inbox/', $in_path."/out/", $out_file, $load_handler_id)) 
		{   //KLOG/990
			// upload was successful, remove that file from the server
			//echo "<font color='green'>Successful Upload</font>";
			$rslt = 1;
			$msg = "Koch EDI Sent Successfully";
			
			// log the upload
			$sqlu = "
				update load_handler set
					linedate_edi_response_sent = now()
				where id = '".sql_friendly($load_handler_id)."'
			";
			simple_query($sqlu);	
		}
		
		echo "<pre>";
		print_r($info);
		print_r($stop_array);
		echo "</pre>";
		
		//echo "<pre>$fcontents</pre>";
	}
?>
<br><br>done...