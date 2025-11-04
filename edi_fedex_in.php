<? include('application.php') ?>
<? include_once('functions_fedex.php') ?>
<?
	$sql = "
		update load_handler_stops set 
			latitude='30.80245781',
			longitude='-83.28965759277344' 
		where shipper_address1='809 GIL HARBIN INDUSTRIAL BLVD' 
			and latitude=''
			and longitude='' 
		order by id desc
	";
	simple_query($sql);
	
	// process 204's (load tender request) from fedex

	$in_path = $defaultsarray['edi_fedex_path'];

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
	   		edi_fedex_process_file($entry);
	   	}
	}
		
	function edi_fedex_process_file($file) 
	{
		global $descriptor;
		global $defaultsarray;
		global $in_path;

		echo "Processing file: $file";
		
		$fcontents = file_get_contents($in_path.$file);
		
		$fcontents = str_replace(chr(13),"",$fcontents);
		$line_array = explode(chr(10), $fcontents);

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
				//Sample 204 file from FedEx... mainly to compare with the 204 from LynnCo...MRR
				
				ISA*00*          *00*          *02*FXFE           *02*CNDD           *130416*0510*U*00401*000000807*0*P*:
                    GS*SM*FXFE*CNDD*20130416*0510*7*X*004010
                    ST*204*70001
                    B2**CNDD**1422310**PP
                    B2A*00*LT
                    L11*0001*55*NUMBER OF TRAILERS NEEDED
                    L11* *77*SINGLE
                    G62*64*00010101*1*0000
                    N1*BT*FEDEX FREIGHT EAST
                    N3*2200 FORWARD DRIVE
                    N4*HARRISON*AR*72601*US
                    G61*IC*JUANITTA THOMASON*TE*800-874-4723 X 2735
                    S5*1*LD
                    G62*CL*20130418*S*0800
                    N1*SF*NASHVILLE*93*NAS
                    N3*3960 LOGISTICS WAY
                    N4*ANTIOCH*TN*37013-2427*US
                    G61*IC*BILL NIPP*TE*800-367-8407
                    S5*2*UL
                    G62*67*20130418*T*2359
                    N1*DA*ORLANDO*93*ORL
                    N3*1850 E LANDSTREET RD
                    N4*ORLANDO*FL*32824-7913*US
                    G61*IC*BERNARD BOLLING*TE*800-218-6292
                    L3*****1958.00
                    SE*24*70001
                    ST*204*70002
                    B2**CNDD**1422311**PP
                    B2A*00*LT
                    L11*0001*55*NUMBER OF TRAILERS NEEDED
                    L11* *77*SINGLE
                    G62*64*00010101*1*0000
                    N1*BT*FEDEX FREIGHT EAST
                    N3*2200 FORWARD DRIVE
                    N4*HARRISON*AR*72601*US
                    G61*IC*JUANITTA THOMASON*TE*800-874-4723 X 2735
                    S5*1*LD
                    G62*CL*20130418*S*0200
                    N1*SF*NASHVILLE*93*NAS
                    N3*3960 LOGISTICS WAY
                    N4*ANTIOCH*TN*37013-2427*US
                    G61*IC*BILL NIPP*TE*800-367-8407
                    S5*2*UL
                    G62*67*20130418*T*2300
                    N1*DA*HARTFORD*93*HFD
                    N3*130 OLD COUNTY CIRCLE
                    N4*WINDSOR LOCKS*CT*06096*US
                    G61*IC*JEFF BAUSCH*TE*888-267-5436
                    L3*****2900.00
                    SE*24*70002
                    ST*204*70003
                    B2**CNDD**1422312**PP
                    B2A*00*LT
                    L11*0001*55*NUMBER OF TRAILERS NEEDED
                    L11* *77*SINGLE
                    G62*64*00010101*1*0000
                    N1*BT*FEDEX FREIGHT EAST
                    N3*2200 FORWARD DRIVE
                    N4*HARRISON*AR*72601*US
                    G61*IC*JUANITTA THOMASON*TE*800-874-4723 X 2735
                    S5*1*LD
                    G62*CL*20130418*S*0100
                    N1*SF*MEMPHIS*93*MEM
                    N3*461 WINCHESTER RD
                    N4*MEMPHIS*TN*38109-3951*US
                    G61*IC*TOM HUGHES*TE*800-622-8178
                    S5*2*UL
                    G62*67*20130418*T*2300
                    N1*DA*HAGERSTOWN*93*HGR
                    N3*16114 TRANSPORTATION CIRCLE
                    N4*HAGERSTOWN*MD*21740*US
                    G61*IC*SCOTT DOLEMAN*TE*877-743-4440
                    L3*****1996.00
                    SE*24*70003
                    ST*204*70004
                    B2**CNDD**1422313**PP
                    B2A*00*LT
                    L11*0001*55*NUMBER OF TRAILERS NEEDED
                    L11* *77*SINGLE
                    G62*64*00010101*1*0000
                    N1*BT*FEDEX FREIGHT EAST
                    N3*2200 FORWARD DRIVE
                    N4*HARRISON*AR*72601*US
                    G61*IC*JUANITTA THOMASON*TE*800-874-4723 X 2735
                    S5*1*LD
                    G62*CL*20130418*S*0600
                    N1*SF*NASHVILLE*93*NAS
                    N3*3960 LOGISTICS WAY
                    N4*ANTIOCH*TN*37013-2427*US
                    G61*IC*BILL NIPP*TE*800-367-8407
                    S5*2*UL
                    G62*67*20130418*T*2200
                    N1*DA*ORLANDO*93*ORL
                    N3*1850 E LANDSTREET RD
                    N4*ORLANDO*FL*32824-7913*US
                    G61*IC*BERNARD BOLLING*TE*800-218-6292
                    L3*****1958.00
                    SE*24*70004
                    ST*204*70005
                    B2**CNDD**1422314**PP
                    B2A*00*LT
                    L11*0001*55*NUMBER OF TRAILERS NEEDED
                    L11* *77*SINGLE
                    G62*64*00010101*1*0000
                    N1*BT*FEDEX FREIGHT EAST
                    N3*2200 FORWARD DRIVE
                    N4*HARRISON*AR*72601*US
                    G61*IC*JUANITTA THOMASON*TE*800-874-4723 X 2735
                    S5*1*LD
                    G62*CL*20130418*S*0600
                    N1*SF*NASHVILLE*93*NAS
                    N3*3960 LOGISTICS WAY
                    N4*ANTIOCH*TN*37013-2427*US
                    G61*IC*BILL NIPP*TE*800-367-8407
                    S5*2*UL
                    G62*67*20130418*T*2200
                    N1*DA*ORLANDO*93*ORL
                    N3*1850 E LANDSTREET RD
                    N4*ORLANDO*FL*32824-7913*US
                    G61*IC*BERNARD BOLLING*TE*800-218-6292
                    L3*****1958.00
                    SE*24*70005
                    ST*204*70006
                    B2**CNDD**1422315**PP
                    B2A*00*LT
                    L11*0001*55*NUMBER OF TRAILERS NEEDED
                    L11* *77*SINGLE
                    G62*64*00010101*1*0000
                    N1*BT*FEDEX FREIGHT EAST
                    N3*2200 FORWARD DRIVE
                    N4*HARRISON*AR*72601*US
                    G61*IC*JUANITTA THOMASON*TE*800-874-4723 X 2735
                    S5*1*LD
                    G62*CL*20130418*S*1500
                    N1*SF*WEST MEMPHIS*93*WME
                    N3*3301 MID AMERICA BLVD
                    N4*WEST MEMPHIS*AR*72301*US
                    G61*IC*AARON LAGING*TE*800-999-3872
                    S5*2*UL
                    G62*67*20130419*T*1100
                    N1*DA*N HARRISBURG*93*NHS
                    N3*2030 NORTH UNION STREET
                    N4*MIDDLETOWN*PA*17057*US
                    G61*IC*LEN MYERS*TE*800-777-0668
                    L3*****2215.98
                    SE*24*70006
                    GE*6*7
                    IEA*1*000000807
                    				
				*/
				
				if($line_part_array[0] == 'N1') $stop_array[$stop_number]['name'] = $line_part_array[2];
				if($line_part_array[0] == 'N1') $stop_array[$stop_number]['address2'] = $line_part_array[4];
				if($line_part_array[0] == 'N3') $stop_array[$stop_number]['address'] = $line_part_array[1];
				if($line_part_array[0] == 'N4') $stop_array[$stop_number]['city'] = $line_part_array[1];
				if($line_part_array[0] == 'N4') $stop_array[$stop_number]['state'] = $line_part_array[2];
				if($line_part_array[0] == 'N4') $stop_array[$stop_number]['zip'] = $line_part_array[3];
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '64') $stop_array[$stop_number]['date'] = $line_part_array[2];
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '64') $stop_array[$stop_number]['time'] = $line_part_array[4];
				
				if($line_part_array[0] == 'G62' && $line_part_array[1] == 'CL') {
					$stop_array[$stop_number]['load_datetime'] = strtotime($line_part_array[2].' '.$line_part_array[4]);
					//$stop_array[$stop_number]['load_date'] = $line_part_array[2];
					//$stop_array[$stop_number]['load_time'] = $line_part_array[4];
				}
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '67') {
					$stop_array[$stop_number]['delivery_datetime'] = strtotime($line_part_array[2].' '.$line_part_array[4]);
					//$stop_array[$stop_number]['deliver_date'] = $line_part_array[2];
					//$stop_array[$stop_number]['deliver_time'] = $line_part_array[4];
				}
				
				if($line_part_array[0] == 'G61') {
					$stop_array[$stop_number]['contact_name'] = $line_part_array[2];
					$stop_array[$stop_number]['contact_phone'] = $line_part_array[4];
					$process_stop = false;
				}
			} else {
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
				if($line_part_array[0] == 'L3') $info['bill_customer'] = $line_part_array[5];
				if($line_part_array[0] == 'AK2') $info['edi_return_type'] = $line_part_array[1];
				if($line_part_array[0] == 'AK2') $info['edi_return_load_id'] = $line_part_array[2];
			}
						
			echo "<tr>
					<td>".$descriptor[$line_part_array[0]]."</td>
					<td>$line</td>
				</tr>
			";			
		}
		echo "</table>";
		
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
			where name_company = 'Fedex'
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
				and deleted = 0
		";
		$data_check = simple_query($sql);
		
		if(mysqli_num_rows($data_check)) 
		{
			// already processed this load
			echo "Already processed FedEx load: $info[load_number]<br>";
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
				billing_notes,
				edi_source_file,
				driver_notes)
			
			values ('".sql_friendly($stop_array[1]['city'])."',
				'".sql_friendly($stop_array[1]['state'])."',
				'".sql_friendly($stop_array[count($stop_array)]['city'])."',
				'".sql_friendly($stop_array[count($stop_array)]['state'])."',
				now(),
				'Auto Created from EDI file',
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
				'',
				'',
				'',
				'',
				0,
				'',
				'',
				0,
				'',
				'".sql_friendly(trim($info['mrr_file_name']))."',
				'')
		";
		simple_query($sql);
		$load_handler_id = mysqli_insert_id($datasource);
		
		//'Auto Created from Fedex EDI file - respond by: ".date("M j, Y h:i a", strtotime($stop_array[1]['date']. ' '.$stop_array[1]['time']))."',
		
		if(isset($info['bill_customer'])) 
		{			
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
		}
		
		// create the stops
		$stop_counter = 0;
		foreach($stop_array as $stop) 
		{
			$stop_counter++;
			if(!isset($stop['delivery_datetime'])) $stop['delivery_datetime'] = 0;
			if(!isset($stop['load_datetime'])) $stop['load_datetime'] = 0;
			
			if($stop_counter == 1 && $stop['stop_type'] == 'LD') 
			{	// this is the first stop set up the pickup eta
				$sql = "
					update load_handler set
						linedate_pickup_eta = '".date("Y-m-d H:s", $stop['load_datetime'])."'
					where id = '$load_handler_id'
					limit 1
				";
				simple_query($sql);
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
					deleted)
					
				values ('$load_handler_id',
					'".sql_friendly($stop['name'])."',
					'".sql_friendly($stop['address'])."',
					'".sql_friendly($stop['address2'])."',
					'".sql_friendly($stop['city'])."',
					'".sql_friendly($stop['state'])."',
					'".sql_friendly($stop['zip'])."',
					'".($stop['stop_type'] == 'LD' ? '1' : '2')."',
					now(),
					'".sql_friendly($stop['contact_phone'])."',
					'".($stop['stop_type'] == 'LD' ? date("Y-m-d H:s", $stop['load_datetime']) : date("Y-m-d H:s", $stop['delivery_datetime']))."',
					0)
			";
			simple_query($sql);
		}
		
		update_origin_dest($load_handler_id);

		//$use_control_number = "000000001";
		$use_control_number = str_pad($load_handler_id, 9, "0", STR_PAD_LEFT);
		$comp_code="FXFE";
		
		// create the 990 response
		$rval = "ISA*00*          *00*          *02*".$defaultsarray['edi_scac_code']."           *02*".$comp_code."           *".date("ymd")."*".date("Hi")."*X*00401*$use_control_number*0*P*>\n";
		$rval .= "GS*GF*".$defaultsarray['edi_scac_code']."*".$comp_code."*".date("Ymd")."*".date("Hi")."*$use_control_number*X*004010\n";
		$rval .= "ST*990*$use_control_number\n";
		$rval .= "B1*".($info['set_purpose'] == '14' ? 'ADV' : 'REG')."*$info[load_number]*".date("Ymd")."*A\n";
		$rval .= "N9*CN*$load_handler_id\n";
		$rval .= "N9*BN**".$defaultsarray['edi_company_name']."\n";
		$rval .= "SE*5*$use_control_number\n";
		$rval .= "GE*1*$use_control_number\n";
		$rval .= "IEA*1*$use_control_number\n";

		$out_file = "990_$info[load_number]_".time().".txt";
		file_put_contents($in_path."/out/".$out_file, $rval);
				
		// send our response file to klein schmidt to go to fedex
		// send the file via FTP to our EDI provider
		
		$sql = "
			update load_handler set					
				fedex_edi_input_file='/edi/fedex_in/out/',
				fedex_edi_output_file='".trim($out_file)."'
			where id = '".sql_friendly($load_handler_id)."'
		";
		simple_query($sql);
		
		
		
		//Send an email.
		$subject="New EDI Load for FedEx Created";
		$msg="This is to let you know that a new load ($info[load_number]) from FedEx EDI system has been created as Conard Load <a href='https://trucking.conardtransportation.com/manage_load.php?load_id=".$load_handler_id."'>".$load_handler_id."</a>.";		
		//mrr_trucking_sendMail($defaultsarray['company_email_address'],"Dispatch",$defaultsarray['company_email_address'],"FedEx EDI",$defaultsarray['peoplenet_hot_msg_cc'],"",$subject,$msg,$msg);
		
		
		
		if(send_fedex_edi('/FXFE/990/', $in_path."/out/", $out_file, $load_handler_id)) 
		{
			// upload was successful, remove that file from the server
			//echo "<font color='green'>Successful Upload</font>";
			$rslt = 1;
			$msg = "EDI Sent Successfully";
			
			// log the upload
			$sql = "
				update load_handler set
					linedate_edi_response_sent = now()
				where id = '".sql_friendly($load_handler_id)."'
			";
			simple_query($sql);	
		}
		
		echo "<pre>";
		print_r($info);
		print_r($stop_array);
		echo "</pre>";
		
		//echo "<pre>$fcontents</pre>";
	}
?>
<br><br>done...