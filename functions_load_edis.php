<?php
//functions for the Visual Load Plus EDI...EDI 214 system.  Import the loads in it to the trucking dispatch system.

function mrr_process_visual_load_plus_edi($xml,$customer_id=0,$auto_save=0)
{
	$page="";
	
	$customer_name="";
	$sql="
		select name_company 
		from customers 
		where id='".sql_friendly($customer_id)."'
	";
	$data=simple_query($sql);
     if($rowc=mysqli_fetch_array($data))
     {
     	$customer_name=trim($rowc['name_company']);	
     }
		
	$floads=0;	
	$loads=0;	
	$load_ids[0]=0;
	
	foreach ($xml->LOAD as $load) 
	{
  		$floads++;
  		
  		$page_load="<br>Load ".$floads.": ".(string) $load->PICKDROP->NAME."";		
  		$page_load.="<br>....Notes: ".(string) $load->TRACKING_NOTES."";
  		
  		//load attributes...attributes within the LOAD tag.
  		$page_load.="<br>....ShipmentID: ".(string) $load['SHIPMENTID'].".";
  		$page_load.="<br>....Cust DUNS: ".(string) $load['ACTUAL_CUST_DUNS'].".";
  		$page_load.="<br>....Dispatcher: ".(string) $load['DISPATCHER'].".";
  		$page_load.="<br>....Actual Name: ".(string) $load['ACTUAL_NAME'].".";
  		$page_load.="<br>....Trans No: ".(string) $load['TRAN_NUMBER'].".";
  		$page_load.="<br>....Ref No: ".(string) $load['REF_NUMBER'].".";
  		$page_load.="<br>....Branch No: ".(string) $load['BRANCH_NO'].".";
  		
  		$page_load.="<br>....Booking Name: ".(string) $load->BOOKING->Name."";
  		$page_load.="<br>....Driver LName:".(string) $load->BOOKING->Driver_LastName."";
  		$page_load.="<br>....Driver FName: ".(string) $load->BOOKING->Driver_FirstName."";
  		$page_load.="<br>....Driver Cell: ".(string) $load->BOOKING->Driver_Cell."";
  		$page_load.="<br>....Driver Email: ".(string) $load->BOOKING->DRIVER_Email."";
  		$page_load.="<br>....Driver2_LName: ".(string) $load->BOOKING->Driver2_LastName."";
  		$page_load.="<br>....Driver2_FName: ".(string) $load->BOOKING->Driver2_FirstName."";
  		$page_load.="<br>....Driver2 Cell: ".(string) $load->BOOKING->Driver2_Cell."";
  		$page_load.="<br>....Driver2 Email: ".(string) $load->BOOKING->DRIVER2_Email."";
  		$page_load.="<br>....Truck: ".(string) $load->BOOKING->Tractor."";
  		$page_load.="<br>....Truck ID: ".(string) $load->BOOKING->Tractor_ID."";
  		$page_load.="<br>....Trailer: ".(string) $load->BOOKING->Trailer."";
  		$page_load.="<br>....Trailer ID: ".(string) $load->BOOKING->Trailer_ID."";
  		
  		
		$row=array();
		$row['origin_address1']="";		//
		$row['origin_address2']="";		//
		$row['origin_city']="";			//
		$row['origin_state']="";			//
		$row['origin_zip']="";			//
		$row['dest_address1']="";		//
		$row['dest_address2']="";		//
		$row['dest_city']="";			//
		$row['dest_state']="";			//
		$row['dest_zip']="";			//
		$row['special_instructions']="TEST VIRTUAL LOAD PLUS...";	//
		$row['estimated_miles']=0;
		$row['customer_id']=$customer_id;
		$row['deadhead_miles']=0;
		$row['linedate_pickup_eta']='0000-00-00 00:00:00';
		$row['deleted']=0;
		$row['linedate_dropoff_eta']='0000-00-00 00:00:00';
		$row['quote']="0.00";
		$row['fuel_charge_per_mile']='0.00';
		$row['shipper']="";	//
		$row['consignee']="";	//
		$row['load_available']=0;
		$row['rate_unloading']="0.00";	//
		$row['rate_stepoff']="0.00";	//
		$row['rate_misc']="0.00";	//
		$row['rate_fuel_surcharge_per_mile']="0.000";	//
		$row['rate_fuel_surcharge_total']="0.00";	//
		$row['rate_base']="0.00";	//
		$row['rate_lumper']="0.00";	//
		$row['preplan']=0;
		$row['preplan_driver_id']=0;
		$row['rate_fuel_surcharge']="0.00";	//
		$row['actual_rate_fuel_surcharge']="0.00";	//
		$row['actual_bill_customer']="0.00";	//
		$row['days_run_otr']="0.00";	//
		$row['days_run_hourly']="0.00";	//
		$row['loaded_miles_hourly']="0.00";	//
		$row['hours_worked']="0.00";	//
		$row['actual_fuel_charge_per_mile']="0.00";	//
		$row['load_number']=(string) $load['REF_NUMBER'];	//
		$row['actual_total_cost']="0.00";	//
		$row['actual_fuel_surcharge_per_mile']="0.00";	//
		$row['otr_daily_cost']="0.00";	//
		$row['sicap_invoice_number']="";	//
		$row['predispatch_odometer']="0";	//
		$row['predispatch_city']="";		//
		$row['predispatch_state']="";		//
		$row['predispatch_zip']="";		//		
		$row['pickup_number']="";		//
		$row['delivery_number']="";		//		
		$row['billing_notes']="";		//
		$row['driver_notes']="";			//
  		
  		/**/
  		$special_instructions="";
  		$dups=0;
  		
  		//stops
  		$stab="
  			<div style='border:1px solid blue; padding:5px; margin:5px;'>
  			<center>Stops for this load:</center>
  			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
  			<tr>
  				<td valign='top'><b>Type</b></td>
  				<td valign='top'><b>PickupETA</b></td>
  				<td valign='top'><b>Shipper</b></td>
  				<td valign='top'><b>Address</b></td>
  				<td valign='top'><b>Address2</b></td>
  				<td valign='top'><b>City</b></td>
  				<td valign='top'><b>State</b></td>
  				<td valign='top'><b>Zip</b></td>
  				<td valign='top'><b>Phone</b></td>
  				<td valign='top'><b>Longitude</b></td>
  				<td valign='top'><b>Latitude</b></td>  				
  				<td valign='top'><b>Directions</b></td>
  				<td valign='top' align='right'><b>PCM Miles</b></td>
  			</tr>
  		";	
  		$stop_cntr=0;
  		$stop_arr[0]= array();
  		$page_stops="";
  		$last_long="";
  		$last_lat="";
  		$est_miles=0;
  		foreach($load->PICKDROP as $stop) 
  		{
  			$page_stops.="<br>.... <b>Stop ".($stop_cntr + 1)."</b>:";
  			
  			
  			$stop_arr[$stop_cntr]['num']=($stop_cntr + 1);  	
  			$stop_type_id=1;
    			if((string) $stop->PU_DROP_TYPE == "C" || (string) $stop->PU_DROP_TYPE == "c")	$stop_type_id=2;
    			
    			$pickup_eta_time=(string) $stop->PU_DROP_TIME1;
    			$pickup_eta_time=trim(substr($pickup_eta_time,0,2).":".substr($pickup_eta_time,2,2).":00");
    			$pickup_eta=(string) $stop->PU_DROP_DATE1." ".$pickup_eta_time;
  			
  			$pcm_miles=0;
  			if($stop_cntr>0)
  			{
  				$cur_long=trim((string) $stop->LONGITUDE);
  				$cur_lat=trim((string) $stop->LATTITUDE);
  				  				
  				$pcm_miles=mrr_distance_between_gps_points($cur_lat,$cur_long,$last_lat,$last_long);
				$pcm_miles=abs($pcm_miles);	
				
				$est_miles+=$pcm_miles;
				
				$row['estimated_miles']=$est_miles;
				//$row['deadhead_miles']=0;
				
				$row['dest_city']=trim((string) $stop->CITY);			//
				$row['dest_state']=trim((string) $stop->STATE);			//	
				$row['linedate_dropoff_eta']=$pickup_eta;
				
  			}
  			else
  			{
  				$row['origin_city']=trim((string) $stop->CITY);			//
				$row['origin_state']=trim((string) $stop->STATE);			//	
				$row['linedate_pickup_eta']=$pickup_eta;
				
  			}
  			
  			$last_long=trim((string) $stop->LONGITUDE);
  			$last_lat=trim((string) $stop->LATTITUDE);  					
  			
  			$stop_arr[$stop_cntr]['shipper_name']=trim((string) $stop->NAME);		//shipper_name,
    			$stop_arr[$stop_cntr]['shipper_address1']=trim((string) $stop->ADDRESS);	//shipper_address1,
    			$stop_arr[$stop_cntr]['shipper_address2']=trim((string) $stop->ADDRESS2);	//shipper_address2,
    			$stop_arr[$stop_cntr]['shipper_city']=trim((string) $stop->CITY);		//shipper_city,
    			$stop_arr[$stop_cntr]['shipper_state']=trim((string) $stop->STATE);		//shipper_state,
    			$stop_arr[$stop_cntr]['shipper_zip']=trim((string) $stop->ZIP);			//shipper_zip,
    			$stop_arr[$stop_cntr]['dest_name']='';								//dest_name,
    			$stop_arr[$stop_cntr]['dest_address1']='';							//dest_address1,
    			$stop_arr[$stop_cntr]['dest_address2']='';							//dest_address2,
    			$stop_arr[$stop_cntr]['dest_city']='';								//dest_city,
    			$stop_arr[$stop_cntr]['dest_state']='';								//dest_state,
    			$stop_arr[$stop_cntr]['dest_zip']='';								//dest_zip,
    			
    			$stop_arr[$stop_cntr]['linedate_pickup_eta']=$pickup_eta;				//linedate_pickup_eta,
    			$stop_arr[$stop_cntr]['linedate_dropoff_eta']='0000-00-00 00:00:00';		//linedate_dropoff_eta,    			
    			
    			$stop_arr[$stop_cntr]['latitude']=trim((string) $stop->LATTITUDE);		//latitude,
    			$stop_arr[$stop_cntr]['longitude']=trim((string) $stop->LONGITUDE);		//longitude,					
    			$stop_arr[$stop_cntr]['stop_type_id']=$stop_type_id;					//stop_type_id,
    			
    			$stop_arr[$stop_cntr]['directions']='';								//directions,
    			$stop_arr[$stop_cntr]['stop_phone']=trim((string) $stop->MAIN_PHONE);		//stop_phone,
    			$stop_arr[$stop_cntr]['ignore_address']=0;							//ignore_address,
    			$stop_arr[$stop_cntr]['pcm_miles']=$pcm_miles;						//pcm_miles,
    			$stop_arr[$stop_cntr]['odometer_reading']=0;							//odometer_reading,
  			
  			$stab.="
  				<tr>
  					<td valign='top'>".($stop_arr[$stop_cntr]['stop_type_id']==1 ? "Shipper" : "Consignee")."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['linedate_pickup_eta']."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['shipper_name']."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['shipper_address1']."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['shipper_address2']."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['shipper_city']."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['shipper_state']."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['shipper_zip']."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['stop_phone']."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['longitude']."</td>
  					<td valign='top'>".$stop_arr[$stop_cntr]['latitude']."</td>  					
  					<td valign='top'>".$stop_arr[$stop_cntr]['directions']."</td> 					
  					<td valign='top' align='right'>".$stop_arr[$stop_cntr]['pcm_miles']."</td>  					
  				</tr>
  			";  			
  			/**/
  			
  			$page_stops.="<br>.... ....BRANCH_NO: ".(string) $stop->BRANCH_NO."";
  			$page_stops.="<br>.... ....REF_NUMBER: ".(string) $stop->REF_NUMBER."";
  			$page_stops.="<br>.... ....TRAN_NUMBER: ".(string) $stop->TRAN_NUMBER."";
  			$page_stops.="<br>.... ....CUSTOMER_NO: ".(string) $stop->CUSTOMER_NO."";
  			$page_stops.="<br>.... ....CUSTOMER_SITE: ".(string) $stop->CUSTOMER_SITE."";
  			$page_stops.="<br>.... ....PICKUP_DROP_SEQ: ".(string) $stop->PICKUP_DROP_SEQ."";
  			$page_stops.="<br>.... ....ENTRY_SEQUENCE: ".(string) $stop->ENTRY_SEQUENCE."";
  			$page_stops.="<br>.... ....PU_DROP_TYPE: ".(string) $stop->PU_DROP_TYPE."";  			
  			$page_stops.="<br>.... ....BILL_OF_LADING: ".(string) $stop->BILL_OF_LADING."";
  			$page_stops.="<br>.... ....BL_SIGNATURE: ".(string) $stop->BL_SIGNATURE."";
  			
  			$page_stops.="<br>.... ....NAME: ".(string) $stop->NAME."";
  			$page_stops.="<br>.... ....ADDRESS: ".(string) $stop->ADDRESS."";
  			$page_stops.="<br>.... ....ADDRESS2: ".(string) $stop->ADDRESS2."";
  			$page_stops.="<br>.... ....CITY: ".(string) $stop->CITY."";
  			$page_stops.="<br>.... ....STATE: ".(string) $stop->STATE."";
  			$page_stops.="<br>.... ....COUNTRY_CODE: ".(string) $stop->COUNTRY_CODE."";
  			$page_stops.="<br>.... ....ZIP: ".(string) $stop->ZIP."";
  			$page_stops.="<br>.... ....MAIN_PHONE: ".(string) $stop->MAIN_PHONE."";
  			$page_stops.="<br>.... ....EXTENSION1: ".(string) $stop->EXTENSION1."";
  			$page_stops.="<br>.... ....TELEPHONE2: ".(string) $stop->TELEPHONE2."";
  			$page_stops.="<br>.... ....EXTENSION2: ".(string) $stop->EXTENSION2."";
  			$page_stops.="<br>.... ....CONTACT1: ".(string) $stop->CONTACT1."";
  			$page_stops.="<br>.... ....CONTACT2: ".(string) $stop->CONTACT2."";
  			
  			$page_stops.="<br>.... ....PU_DROP_DATE1: ".(string) $stop->PU_DROP_DATE1."";
  			$page_stops.="<br>.... ....PU_DROP_DATE2: ".(string) $stop->PU_DROP_DATE2."";
  			$page_stops.="<br>.... ....PU_DROP_TIME1: ".(string) $stop->PU_DROP_TIME1."";
  			$page_stops.="<br>.... ....PU_DROP_TIME2: ".(string) $stop->PU_DROP_TIME2."";
  			$page_stops.="<br>.... ....APPOINT_REQUIED: ".(string) $stop->APPOINT_REQUIED."";
  			$page_stops.="<br>.... ....APPOINT_MADE: ".(string) $stop->APPOINT_MADE."";
  			$page_stops.="<br>.... ....COMMODITY: ".(string) $stop->COMMODITY."";
  			$page_stops.="<br>.... ....CDTY_VALUE: ".(string) $stop->CDTY_VALUE."";               
               $page_stops.="<br>.... ....PU_DROP_PO: ".(string) $stop->PU_DROP_PO."";
  			$page_stops.="<br>.... ....LOAD_UNLOAD: ".(string) $stop->LOAD_UNLOAD."";
  			$page_stops.="<br>.... ....PALLETS: ".(string) $stop->PALLETS."";
  			$page_stops.="<br>.... ....NOTIFY_DELIVER: ".(string) $stop->NOTIFY_DELIVER."";
  			$page_stops.="<br>.... ....NOTIFY_DEL_DATE: ".(string) $stop->NOTIFY_DEL_DATE."";
  			$page_stops.="<br>.... ....NOTIFY_DEL_TIME: ".(string) $stop->NOTIFY_DEL_TIME."";
  			$page_stops.="<br>.... ....DRIVER_DISPATCH: ".(string) $stop->DRIVER_DISPATCH."";
  			$page_stops.="<br>.... ....DISPATCH_DATE: ".(string) $stop->DISPATCH_DATE."";
  			$page_stops.="<br>.... ....DISPATCH_TIME: ".(string) $stop->DISPATCH_TIME."";
  			$page_stops.="<br>.... ....DISPATCH_FROM: ".(string) $stop->DISPATCH_FROM."";
  			$page_stops.="<br>.... ....DRIVER_IN: ".(string) $stop->DRIVER_IN."";
  			$page_stops.="<br>.... ....IN_DATE: ".(string) $stop->IN_DATE."";
  			$page_stops.="<br>.... ....IN_TIME: ".(string) $stop->IN_TIME."";
  			$page_stops.="<br>.... ....DEPARTED: ".(string) $stop->DEPARTED."";
  			$page_stops.="<br>.... ....DEPART_DATE: ".(string) $stop->DEPART_DATE."";
  			$page_stops.="<br>.... ....DEPART_TIME: ".(string) $stop->DEPART_TIME."";
  			$page_stops.="<br>.... ....LONGITUDE: ".(string) $stop->LONGITUDE."";
  			$page_stops.="<br>.... ....LATTITUDE: ".(string) $stop->LATTITUDE."";
  			
  			$row['estimated_miles']=$stop->UNIT_VALUE1;
  			
  			$page_stops.="<br>.... ....DAMAGE_SHORT: ".(string) $stop->DAMAGE_SHORT."";               
  			$page_stops.="<br>.... ....UNIT1: ".(string) $stop->UNIT1."";
  			$page_stops.="<br>.... ....UNIT_VALUE1: ".(string) $stop->UNIT_VALUE1."";
  			$page_stops.="<br>.... ....UNIT2: ".(string) $stop->UNIT2."";
  			$page_stops.="<br>.... ....UNIT_VALUE2: ".(string) $stop->UNIT_VALUE2."";
  			$page_stops.="<br>.... ....UNIT3: ".(string) $stop->UNIT3."";
  			$page_stops.="<br>.... ....UNIT_VALUE3: ".(string) $stop->UNIT_VALUE3."";
  			$page_stops.="<br>.... ....UNIT4: ".(string) $stop->UNIT4."";
  			$page_stops.="<br>.... ....UNIT_VALUE4: ".(string) $stop->UNIT_VALUE4."";
  			$page_stops.="<br>.... ....UNIT5: ".(string) $stop->UNIT5."";
  			$page_stops.="<br>.... ....UNIT_VALUE5: ".(string) $stop->UNIT_VALUE5."";
  			$page_stops.="<br>.... ....UNIT6: ".(string) $stop->UNIT6."";
  			$page_stops.="<br>.... ....UNIT_VALUE6: ".(string) $stop->UNIT_VALUE6."";
  			$page_stops.="<br>.... ....UNIT7: ".(string) $stop->UNIT7."";
  			$page_stops.="<br>.... ....UNIT_VALUE7: ".(string) $stop->UNIT_VALUE7."";
  			$page_stops.="<br>.... ....UNIT8: ".(string) $stop->UNIT8."";
  			$page_stops.="<br>.... ....UNIT_VALUE8: ".(string) $stop->UNIT_VALUE8."";
  			$page_stops.="<br>.... ....UNIT9: ".(string) $stop->UNIT9."";
  			$page_stops.="<br>.... ....UNIT_VALUE9: ".(string) $stop->UNIT_VALUE9."";
  			$page_stops.="<br>.... ....UNIT10: ".(string) $stop->UNIT10."";
  			$page_stops.="<br>.... ....UNIT_VALUE10: ".(string) $stop->UNIT_VALUE10."";
  			$page_stops.="<br>.... ....STAMP_DATE: ".(string) $stop->STAMP_DATE."";
  			$page_stops.="<br>.... ....STAMP_TIME: ".(string) $stop->STAMP_TIME."";
  			$page_stops.="<br>.... ....USER_ID: ".(string) $stop->USER_ID."";
  			$page_stops.="<br>.... ....E_MAIL: ".(string) $stop->E_MAIL."";
  			$page_stops.="<br>.... ....NEWCUST_NO: ".(string) $stop->NEWCUST_NO."";
  			$page_stops.="<br>.... ....NEWCUST_SITE: ".(string) $stop->NEWCUST_SITE."";
  			$page_stops.="<br>.... ....APPOINT_NO: ".(string) $stop->APPOINT_NO."";
  			$page_stops.="<br>.... ....APPOINT_SETBY: ".(string) $stop->APPOINT_SETBY."";
  			$page_stops.="<br>.... ....LENGTH: ".(string) $stop->LENGTH."";
  			$page_stops.="<br>.... ....WIDTH: ".(string) $stop->WIDTH."";
  			$page_stops.="<br>.... ....HEIGHT: ".(string) $stop->HEIGHT."";
  			
  			$page_stops.="<br>.... ....OVERSIZE: ".(string) $stop->OVERSIZE."";
  			$page_stops.="<br>.... ....TEMPERATURE: ".(string) $stop->TEMPERATURE."";
  			$page_stops.="<br>.... ....TEMPERATURE2: ".(string) $stop->TEMPERATURE2."";               
  			$page_stops.="<br>.... ....HAZMAT: ".(string) $stop->HAZMAT."";
  			$page_stops.="<br>.... ....NOT_DEL_REASON: ".(string) $stop->NOT_DEL_REASON."";
  			$page_stops.="<br>.... ....DRIVERIN_REASON: ".(string) $stop->DRIVERIN_REASON."";
  			$page_stops.="<br>.... ....DEPARTED_REASON: ".(string) $stop->DEPARTED_REASON."";
  			$page_stops.="<br>.... ....NOT_DEL_ID: ".(string) $stop->NOT_DEL_ID."";
  			$page_stops.="<br>.... ....DISPATCH_ID: ".(string) $stop->DISPATCH_ID."";
  			$page_stops.="<br>.... ....DRIVERIN_ID: ".(string) $stop->DRIVERIN_ID."";
  			$page_stops.="<br>.... ....DEPARTED_ID: ".(string) $stop->DEPARTED_ID."";
  			$page_stops.="<br>.... ....CDTY_UNITS: ".(string) $stop->CDTY_UNITS."";
  			$page_stops.="<br>.... ....CDTY_KINOFPKG: ".(string) $stop->CDTY_KINOFPKG."";
  			$page_stops.="<br>.... ....CDTY_NMFC_NUM: ".(string) $stop->CDTY_NMFC_NUM."";
  			$page_stops.="<br>.... ....CDTY_CLASS: ".(string) $stop->CDTY_CLASS."";
  			$page_stops.="<br>.... ....NOT_DEL_214: ".(string) $stop->NOT_DEL_214."";
  			$page_stops.="<br>.... ....DISPATCH_214: ".(string) $stop->DISPATCH_214."";
  			$page_stops.="<br>.... ....DRIVERIN_214: ".(string) $stop->DRIVERIN_214."";
  			$page_stops.="<br>.... ....DEPARTED_214: ".(string) $stop->DEPARTED_214."";
  			$page_stops.="<br>.... ....NOTIFY_CON1: ".(string) $stop->NOTIFY_CON1."";
  			$page_stops.="<br>.... ....NOTIFY_CON2: ".(string) $stop->NOTIFY_CON2."";
  			$page_stops.="<br>.... ....EMAIL_CON1: ".(string) $stop->EMAIL_CON1."";
  			$page_stops.="<br>.... ....EMAIL_CON2: ".(string) $stop->EMAIL_CON2."";
  			$page_stops.="<br>.... ....NOTIFY_EMAIL: ".(string) $stop->NOTIFY_EMAIL."";
  			$page_stops.="<br>.... ....PAYMENT: ".(string) $stop->PAYMENT."";
  			
  			foreach ($stop->PickdropNumbers->ReferenceNumber as $sinfo) 
  			{
    				switch((string) $sinfo['Type']) 
    				{ // Get attributes as element indices
    					case 'PONumber':
        					$page_stops.="<br>.... .... ....PONumber: ".(string) $sinfo."";
        					$special_instructions.="PONumber: ".trim((string) $sinfo)." ";
        					//$row['load_number']=;
        					break;
    					case 'BOLNumber':
        					$page_stops.="<br>.... .... ....BOLNumber: ".(string) $sinfo."";
        					$special_instructions.="BOLNumber: ".trim((string) $sinfo)." ";
        					//$row['pickup_number']=;        					
        					break;
        				case 'ShipNumber':
        					$page_stops.="<br>.... .... ....ShipNumber: ".(string) $sinfo."";
        					$special_instructions.="ShipNumber: ".trim((string) $sinfo)." ";
        					//$row['delivery_number']=;
        					break;
    				}
			}
			
			$stop_cntr++;
  		}
  		$carrier_pay=$load->BOOKING->Carrier_Pay;  		
  		$page_load.="<br>....Booking Carrier Pay: $".$carrier_pay ."";
  		$row['actual_bill_customer']=$carrier_pay;
  		
  		$stab.="
  			</table>
  			</div>
  		";
  		
  		
  		
  		$row['special_instructions']=$special_instructions . "... Carrier Pay=$".$carrier_pay.".";
  		$ltab="";		
  		
  		
  		$ltab="
  		 	<div style='border:1px solid green; padding:5px; margin:5px;'>
  			<center>New Load settings:</center>
  			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
  			<tr>
  				<td valign='top'><b>Customer</b></td>
  				<td valign='top'><b>Load#</b></td>
  				<td valign='top'><b>Origin City</b></td>
  				<td valign='top'><b>Origin State</b></td>
  				<td valign='top'><b>Dest City</b></td>
  				<td valign='top'><b>Dest State</b></td>
  				<td valign='top'><b>Pickup</b></td>
  				<td valign='top'><b>Dropoff</b></td>
  				<td valign='top'><b>EstMiles</b></td>
  				<td valign='top'><b>DeadHead</b></td>
  				<td valign='top'><b>SpecialInstruction</b></td>
  			</tr>
  			<tr>
  				<td valign='top'>".$customer_name."</td>
  				<td valign='top'>".$row['load_number']."</td>
  				<td valign='top'>".$row['origin_city']."</td>
  				<td valign='top'>".$row['origin_state']."</td>
  				<td valign='top'>".$row['dest_city']."</td>
  				<td valign='top'>".$row['dest_state']."</td>
  				<td valign='top'>".$row['linedate_pickup_eta']."</td>
  				<td valign='top'>".$row['linedate_dropoff_eta']."</td>
  				<td valign='top'>".$row['estimated_miles']."</td>
  				<td valign='top'>".$row['deadhead_miles']."</td>
  				<td valign='top'>".$row['special_instructions']."</td>
  			</tr>
  			</table>
  			</div>
  		 ";	
  		
  		if($auto_save > 0)
  		{  			
       		
       		$old_load_id=mrr_find_load_reference_number_for_vlp($row['load_number'],$customer_id);
       		if($old_load_id==0)
       		{
       			$new_load_id=mrr_create_edi_load($row);
       			if($new_load_id>0)
       			{
       				for($z=0; $z < $stop_cntr; $z++)
       				{  					
       					$stopid=mrr_create_edi_load_stop($new_load_id,$stop_arr[$z]);
       				}
       				$load_ids[$loads]=$new_load_id;
       				$loads++;	  			
       			}
       		}
       		else
       		{
       			$dups=$old_load_id;	
       		}
  		}
  		/**/
  		$page.=$ltab;
  		$page.=$stab;
  		$page.=$page_load;
  		$page.=$page_stops;
  		
  		//.....
	}	
	
	
	$page=trim($page);
	
	$res['page']=$page;
	$res['loads']=$loads;
	$res['duplicates']=$dups;
	$res['load_arr']=$load_ids;
	
	return $res;	
}
function mrr_process_visual_load_plus_edi_view($xml)
{
	$page="";
	
	ob_clean();
	ob_start();	
	
	print_r($xml);
	$page=ob_get_contents();
	
	ob_clean();
	
	$page=trim($page);
	
	$res['page']=$page;
	$res['void']=0;
	
	return $res;	
}
function mrr_create_edi_load($row)
{
	global $datasource;

	$newid=0;
		
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
	
	$mrr_trailer_mile_exp_per_mile=mrr_get_default_variable_setting('trailer_mile_exp_per_mile');
	
	$mrr_cargo_insurance=mrr_get_option_variable_settings('Cargo Insurance');
     $mrr_general_liability=mrr_get_option_variable_settings('General Liability');
     $mrr_liability_phy_damage=mrr_get_option_variable_settings('Liability/Phy Damage');
     $mrr_payroll___admin=mrr_get_option_variable_settings('Payroll & Admin');
     $mrr_rent=mrr_get_option_variable_settings('Rent');
     $mrr_tractor_lease=mrr_get_option_variable_settings('Tractor Lease');
     $mrr_trailer_expense=mrr_get_option_variable_settings('Trailer Expense');
     $mrr_trailer_lease=mrr_get_option_variable_settings('Trailer Lease');
     $mrr_misc_expenses=mrr_get_option_variable_settings('Misc Expenses');	
          
	$columns="(id,
		origin_address1,
		origin_address2,
		origin_city,
		origin_state,
		origin_zip,
		dest_address1,
		dest_address2,
		dest_city,
		dest_state,
		dest_zip,
		linedate_added,
		special_instructions,
		estimated_miles,
		created_by_id,
		customer_id,
		deadhead_miles,
		linedate_pickup_eta,
		linedate_pickup_pta,
		deleted,
		linedate_dropoff_eta,
		linedate_dropoff_pta,
		quote,
		fuel_charge_per_mile,
		invoice_number,
		shipper,
		consignee,
		load_available,
		rate_unloading,
		rate_stepoff,
		rate_misc,
		rate_fuel_surcharge_per_mile,
		rate_fuel_surcharge_total,
		rate_base,
		rate_lumper,
		preplan,
		preplan_driver_id,
		rate_fuel_surcharge,
		actual_rate_fuel_surcharge,
		actual_bill_customer,
		days_run_otr,
		days_run_hourly,
		loaded_miles_hourly,
		hours_worked,
		actual_fuel_charge_per_mile,
		load_number,
		actual_total_cost,
		actual_fuel_surcharge_per_mile,
		otr_daily_cost,
		linedate_invoiced,
		sicap_invoice_number,
		linedate_auto_created_reviewed,
		auto_created,
		linedate_edi_response_sent,
		linedate_edi_invoice_sent,
		predispatch_odometer,
		predispatch_city,
		predispatch_state,
		predispatch_zip,
		update_fuel_surcharge,
		master_load,
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
		dedicated_load,
		pickup_number,
		delivery_number,
		billing_notes,
		driver_notes,
		vpl_imported)";
	
	$values="(NULL,
		'".sql_friendly($row['origin_address1'])."',
		'".sql_friendly($row['origin_address2'])."',
		'".sql_friendly($row['origin_city'])."',
		'".sql_friendly($row['origin_state'])."',
		'".sql_friendly($row['origin_zip'])."',
		'".sql_friendly($row['dest_address1'])."',
		'".sql_friendly($row['dest_address2'])."',
		'".sql_friendly($row['dest_city'])."',
		'".sql_friendly($row['dest_state'])."',
		'".sql_friendly($row['dest_zip'])."',
		NOW(),
		'".sql_friendly($row['special_instructions'])."',
		'".sql_friendly($row['estimated_miles'])."',
		'".sql_friendly($_SESSION['user_id'])."',
		'".sql_friendly($row['customer_id'])."',
		'".sql_friendly($row['deadhead_miles'])."',
		'".date("Y-m-d H:i",strtotime($row['linedate_pickup_eta']))."',
		'0000-00-00 00:00:00',
		'".sql_friendly($row['deleted'])."',
		'0000-00-00 00:00:00',
		'0000-00-00 00:00:00',
		'".sql_friendly($row['quote'])."',
		'".sql_friendly($row['fuel_charge_per_mile'])."',
		'',
		'".sql_friendly($row['shipper'])."',
		'".sql_friendly($row['consignee'])."',
		'".sql_friendly($row['load_available'])."',
		'".sql_friendly($row['rate_unloading'])."',
		'".sql_friendly($row['rate_stepoff'])."',
		'".sql_friendly($row['rate_misc'])."',
		'".sql_friendly($row['rate_fuel_surcharge_per_mile'])."',
		'".sql_friendly($row['rate_fuel_surcharge_total'])."',
		'".sql_friendly($row['rate_base'])."',
		'".sql_friendly($row['rate_lumper'])."',
		'".sql_friendly($row['preplan'])."',
		'".sql_friendly($row['preplan_driver_id'])."',
		'".sql_friendly($row['rate_fuel_surcharge'])."',
		'".sql_friendly($row['actual_rate_fuel_surcharge'])."',
		'".sql_friendly($row['actual_bill_customer'])."',
		'".sql_friendly($row['days_run_otr'])."',
		'".sql_friendly($row['days_run_hourly'])."',
		'".sql_friendly($row['loaded_miles_hourly'])."',
		'".sql_friendly($row['hours_worked'])."',
		'".sql_friendly($row['actual_fuel_charge_per_mile'])."',
		'".sql_friendly($row['load_number'])."',
		'".sql_friendly($row['actual_total_cost'])."',
		'".sql_friendly($row['actual_fuel_surcharge_per_mile'])."',
		'".sql_friendly($row['otr_daily_cost'])."',
		'0000-00-00 00:00:00',
		'".sql_friendly($row['sicap_invoice_number'])."',
		'0000-00-00 00:00:00',
		'1',
		'0000-00-00 00:00:00',
		'0000-00-00 00:00:00',
		'".sql_friendly($row['predispatch_odometer'])."',
		'".sql_friendly($row['predispatch_city'])."',
		'".sql_friendly($row['predispatch_state'])."',
		'".sql_friendly($row['predispatch_zip'])."',
		'0000-00-00',
		'0',
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
		'".sql_friendly($row['dedicated_load'])."',
		'".sql_friendly($row['pickup_number'])."',
		'".sql_friendly($row['delivery_number'])."',
		'".sql_friendly($row['billing_notes'])."',
		'".sql_friendly($row['driver_notes'])."',
		1)";
	
     $sql = "
		insert into load_handler ".$columns." values ".$values."
	";
     simple_query($sql);	
     $newid=mysqli_insert_id($datasource);
     return $newid;	
}
function mrr_create_edi_load_stop($load_id,$row2)
{
	global $datasource;

	$columns3="(id,
    			load_handler_id,
    			trucks_log_id,
    			shipper_name,
    			shipper_address1,
    			shipper_address2,
    			shipper_city,
    			shipper_state,
    			shipper_zip,
    			shipper_eta,
    			shipper_pta,
    			dest_name,
    			dest_address1,
    			dest_address2,
    			dest_city,
    			dest_state,
    			dest_zip,
    			dest_eta,
    			dest_pta,
    			deleted,
    			linedate_added,
    			created_by_user_id,
    			linedate_pickup_eta,
    			linedate_pickup_pta,
    			linedate_dropoff_eta,
    			linedate_dropoff_pta,
    			stop_type_id,
    			linedate_completed,
    			directions,
    			stop_phone,
    			ignore_address,
    			pcm_miles,
    			odometer_reading,
    			latitude,
    			longitude,
    			appointment_window,
    			linedate_appt_window_start,
    			linedate_appt_window_end)
    	";
    			
	$values3="(NULL,
    			'".sql_friendly($load_id)."',
    			'0',
    			'".sql_friendly($row2['shipper_name'])."',
    			'".sql_friendly($row2['shipper_address1'])."',
    			'".sql_friendly($row2['shipper_address2'])."',
    			'".sql_friendly($row2['shipper_city'])."',
    			'".sql_friendly($row2['shipper_state'])."',
    			'".sql_friendly($row2['shipper_zip'])."',
    			'0000-00-00 00:00:00',
    			'0000-00-00 00:00:00',
    			'".sql_friendly($row2['dest_name'])."',
    			'".sql_friendly($row2['dest_address1'])."',
    			'".sql_friendly($row2['dest_address2'])."',
    			'".sql_friendly($row2['dest_city'])."',
    			'".sql_friendly($row2['dest_state'])."',
    			'".sql_friendly($row2['dest_zip'])."',
    			'0000-00-00 00:00:00',
    			'0000-00-00 00:00:00',
    			'0',
    			NOW(),
    			'".sql_friendly($_SESSION['user_id'])."',
    			'".date("Y-m-d H:i:s",strtotime($row2['linedate_pickup_eta']))."',
    			'0000-00-00 00:00:00',
    			'".date("Y-m-d H:i:s",strtotime($row2['linedate_dropoff_eta']))."',
    			'0000-00-00 00:00:00',
    			'".sql_friendly($row2['stop_type_id'])."',
    			'0000-00-00 00:00:00',
    			'".sql_friendly($row2['directions'])."',
    			'".sql_friendly($row2['stop_phone'])."',
    			'".sql_friendly($row2['ignore_address'])."',
    			'".sql_friendly($row2['pcm_miles'])."',
    			'".sql_friendly($row2['odometer_reading'])."',
    			'".sql_friendly($row2['latitude'])."',
    			'".sql_friendly($row2['longitude'])."',
    			0,
    			'0000-00-00 00:00:00',
    			'0000-00-00 00:00:00')
    	";   
		       		
	$sql3 = "
		insert into load_handler_stops ".$columns3." values ".$values3."
	";     					
	simple_query($sql3); 	
	$newid=mysqli_insert_id($datasource);
	return $newid;
}

function mrr_find_load_reference_number_for_vlp($load_ref,$customer_id=0)
{
	$id=0;
	$sql = "
		 select id 
		 from load_handler 
		 where load_number='".sql_friendly(trim($load_ref))."'
		 	and deleted=0
		 	and customer_id='".sql_friendly($customer_id)."'
		 order by id asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{
		$id=$row['id'];
	}
	return $id;
}
function mrr_flag_processing_for_this_vlp_load($load_id)
{
	$id=0;
	$sql = "
		 select id 
		 from trucks_log 
		 where load_handler_id='".sql_friendly($load_id)."'
		 	and deleted=0
		 order by id asc
	";     					
	$data=simple_query($sql); 	
	while($row=mysqli_fetch_array($data))
	{
		$id=$row['id'];
	}
	if($id>0)
	{
		$sql = "
		 	update load_handler set
		 		vpl_import_processed='1'
		 	where id='".sql_friendly($load_id)."'
		";     					
		simple_query($sql); 	
	}
	else
	{
		$sql = "
		 	update load_handler set
		 		vpl_import_processed='0'
		 	where id='".sql_friendly($load_id)."'
		";     					
		simple_query($sql); 	
	}
	return $id;	
}
function mrr_send_vlp_notice()
	{	//sends email to insurance addresses for new truck/trailer/driver and logs it (driver not yet needed).
		global $defaultsarray;
		
		$addr1 = trim($defaultsarray['visual_load_plus_email']);
		$addr2 = trim($defaultsarray['visual_load_plus_email2']);
		
		$send_mail=0;	
		
		$From=trim($defaultsarray['company_email_address']);		
		$FromName=trim($defaultsarray['company_name']);
		
		$ToName="";
		$Subject="".trim($defaultsarray['company_name'])." Imported Load Notice";
		$Html="";
				
		
		$report="<table class='admin_menu1' style='text-align:left; width:1600px;'>";
     	
     	$report.="
     		<tr>
     			<td valign='top' colspan='9'><b>Unprocessed Visual Load Plus Loads</b></td>
     		</tr>		
     		<tr>
     			<td valign='top'><b>LoadID</b></td>
     			<td valign='top'><b>Pickup</b></td>
     			<td valign='top'><b>Added</b></td>
     			<td valign='top'><b>Origin</b></td>
     			<td valign='top'><b>State</b></td>
     			<td valign='top'><b>Destination</b></td>
     			<td valign='top'><b>State</b></td>
     			<td valign='top'><b>Load#</b></td>
     			<td valign='top'><b>Special Instructions</b></td>
     		</tr>
     	";
     	
		$cntr=0;
     	$sql="
     		select load_handler.* 
     		from load_handler
     		where deleted=0
     			and vpl_imported>0
     			and vpl_import_processed=0
     		order by id desc
     		limit 250
     	";
     	$data = simple_query($sql);	
     	while($row=mysqli_fetch_array($data))
     	{
     		$flag=mrr_flag_processing_for_this_vlp_load($row['id']);
     		if($flag==0)
     		{
     			$report.="
     			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
     				<td valign='top'><a href='trucking.conardtransportation.com/manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
     				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
     				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
     				<td valign='top'>".$row['origin_city']."</td>
     				<td valign='top'>".$row['origin_state']."</td>
     				<td valign='top'>".$row['dest_city']."</td>
     				<td valign='top'>".$row['dest_state']."</td>
     				<td valign='top'>".$row['load_number']."</td>
     				<td valign='top'>".$row['special_instructions']."</td>
     			</tr>
     			";
     			$cntr++;
     		}
     	}
     	$report.="
			</table>
		";
		
		$Html=$report;
		$ToName="Dispatcher";
				
		if(trim($Html)!="")		$send_mail=1;
		
		if($send_mail==1 && trim($addr1)!="")	
		{	//primary address			
			$did_send=mrr_trucking_sendMail_PN($addr1,$ToName,$From,$FromName,$Subject,$Html);				
		}
		if($send_mail==1 && trim($addr2)!="")	
		{	//secondary address
			$did_send=mrr_trucking_sendMail_PN($addr2,$ToName,$From,$FromName,$Subject,$Html);				
		}
		return $reporter;		
	}
/*
<EDI214>
     <LOAD SHIPMENTID=" " ACTUAL_CUST_DUNS=" " DISPATCHER="SYS" ACTUAL_NAME="_CLIENT SERVICES EDI " TRAN_NUMBER="0" REF_NUMBER="4881" BRANCH_NO="0">
          <PICKDROP Sequence="1" Type="Shipper">
               <BRANCH_NO>0</BRANCH_NO>
               <REF_NUMBER>4881</REF_NUMBER>
               <TRAN_NUMBER>0</TRAN_NUMBER>
               <CUSTOMER_NO>2959</CUSTOMER_NO>
               <CUSTOMER_SITE>1</CUSTOMER_SITE>
               <PICKUP_DROP_SEQ>1</PICKUP_DROP_SEQ>
               <ENTRY_SEQUENCE>1</ENTRY_SEQUENCE>
               <PU_DROP_TYPE>S</PU_DROP_TYPE>
               <BILL_OF_LADING/>
               <BL_SIGNATURE/>
               <NAME>_CLIENT SERVICES EDI</NAME>
               <ADDRESS>100 SW 124 ST</ADDRESS>
               <ADDRESS2/>
               <CITY>TAMPA</CITY>
               <STATE>FL</STATE>
               <COUNTRY_CODE>US</COUNTRY_CODE>
               <ZIP/>
               <MAIN_PHONE>9995551212</MAIN_PHONE>
               <EXTENSION1>0</EXTENSION1>
               <TELEPHONE2/>
               <EXTENSION2>0</EXTENSION2>
               <CONTACT1/>
               <CONTACT2/>
               <PU_DROP_DATE1>08/07/2013</PU_DROP_DATE1>
               <PU_DROP_DATE2/>
               <PU_DROP_TIME1>090000</PU_DROP_TIME1>
               <PU_DROP_TIME2/>
               <APPOINT_REQUIED>Y</APPOINT_REQUIED>
               <APPOINT_MADE>Y</APPOINT_MADE>
               <COMMODITY>LUMBER</COMMODITY>
               <CDTY_VALUE>0</CDTY_VALUE>
               <PU_DROP_PO/>
               <LOAD_UNLOAD/>
               <PALLETS/>
               <NOTIFY_DELIVER>Y</NOTIFY_DELIVER>
               <NOTIFY_DEL_DATE>08/07/2013</NOTIFY_DEL_DATE>
               <NOTIFY_DEL_TIME>132519</NOTIFY_DEL_TIME>
               <DRIVER_DISPATCH/>
               <DISPATCH_DATE/>
               <DISPATCH_TIME/>
               <DISPATCH_FROM/>
               <DRIVER_IN/>
               <IN_DATE/>
               <IN_TIME/>
               <DEPARTED/>
               <DEPART_DATE/>
               <DEPART_TIME/>
               <LONGITUDE>82.458611</LONGITUDE>
               <LATTITUDE>27.947222</LATTITUDE>
               <DAMAGE_SHORT/>
               <UNIT1>1040</UNIT1>
               <UNIT_VALUE1>10000</UNIT_VALUE1>
               <UNIT2>1030</UNIT2>
               <UNIT_VALUE2>100</UNIT_VALUE2>
               <UNIT3>0</UNIT3>
               <UNIT_VALUE3>0</UNIT_VALUE3>
               <UNIT4>0</UNIT4>
               <UNIT_VALUE4>0</UNIT_VALUE4>
               <UNIT5>0</UNIT5>
               <UNIT_VALUE5>0</UNIT_VALUE5>
               <UNIT6>0</UNIT6>
               <UNIT_VALUE6>0</UNIT_VALUE6>
               <UNIT7>0</UNIT7>
               <UNIT_VALUE7>0</UNIT_VALUE7>
               <UNIT8>0</UNIT8>
               <UNIT_VALUE8>0</UNIT_VALUE8>
               <UNIT9>0</UNIT9>
               <UNIT_VALUE9>0</UNIT_VALUE9>
               <UNIT10>0</UNIT10>
               <UNIT_VALUE10>0</UNIT_VALUE10>
               <STAMP_DATE>08/07/2013</STAMP_DATE>
               <STAMP_TIME>132524</STAMP_TIME>
               <USER_ID>SYS</USER_ID>
               <E_MAIL>don@freight-mngt.com</E_MAIL>
               <NEWCUST_NO>2959</NEWCUST_NO>
               <NEWCUST_SITE>1</NEWCUST_SITE>
               <APPOINT_NO>1005</APPOINT_NO>
               <APPOINT_SETBY/>
               <LENGTH>0</LENGTH>
               <WIDTH>0</WIDTH>
               <HEIGHT>0</HEIGHT>
               <OVERSIZE/>
               <TEMPERATURE>0</TEMPERATURE>
               <TEMPERATURE2>0</TEMPERATURE2>
               <HAZMAT/>
               <NOT_DEL_REASON/>
               <DISPATCH_REASON/>
               <DRIVERIN_REASON/>
               <DEPARTED_REASON/>
               <NOT_DEL_ID>SYS</NOT_DEL_ID>
               <DISPATCH_ID/>
               <DRIVERIN_ID/>
               <DEPARTED_ID/>
               <CDTY_UNITS>0</CDTY_UNITS>
               <CDTY_KINOFPKG/>
               <CDTY_NMFC_NUM/>
               <CDTY_CLASS/>
               <NOT_DEL_214/>
               <DISPATCH_214/>
               <DRIVERIN_214/>
               <DEPARTED_214/>
               <NOTIFY_CON1/>
               <NOTIFY_CON2/>
               <EMAIL_CON1/>
               <EMAIL_CON2/>
               <NOTIFY_EMAIL>Y</NOTIFY_EMAIL>
               <PAYMENT>COLLECT</PAYMENT>
               <PickdropNumbers>
                    <ReferenceNumber Type="PONumber">123456</ReferenceNumber>
                    <ReferenceNumber Type="BOLNumber">789</ReferenceNumber>
                    <ReferenceNumber Type="ShipNumber">ABCDE</ReferenceNumber>
               </PickdropNumbers>
          </PICKDROP>
          <PICKDROP Sequence="2" Type="Consignee">
               <BRANCH_NO>0</BRANCH_NO>
               <REF_NUMBER>4881</REF_NUMBER>
               <TRAN_NUMBER>0</TRAN_NUMBER>
               <CUSTOMER_NO>2959</CUSTOMER_NO>
               <CUSTOMER_SITE>1</CUSTOMER_SITE>
               <PICKUP_DROP_SEQ>2</PICKUP_DROP_SEQ>
               <ENTRY_SEQUENCE>2</ENTRY_SEQUENCE>
               <PU_DROP_TYPE>C</PU_DROP_TYPE>
               <BILL_OF_LADING/>
               <BL_SIGNATURE/>
               <NAME>_CLIENT SERVICES EDI</NAME>
               <ADDRESS>100 SW 124 ST</ADDRESS>
               <ADDRESS2/>
               <CITY>TAMPA</CITY>
               <STATE>FL</STATE>
               <COUNTRY_CODE>US</COUNTRY_CODE>
               <ZIP/>
               <MAIN_PHONE>9995551212</MAIN_PHONE>
               <EXTENSION1>0</EXTENSION1>
               <TELEPHONE2/>
               <EXTENSION2>0</EXTENSION2>
               <CONTACT1/>
               <CONTACT2/>
               <PU_DROP_DATE1>08/09/2013</PU_DROP_DATE1>
               <PU_DROP_DATE2/>
               <PU_DROP_TIME1>100000</PU_DROP_TIME1>
               <PU_DROP_TIME2/>
               <APPOINT_REQUIED>Y</APPOINT_REQUIED>
               <APPOINT_MADE/>
               <COMMODITY/>
               <CDTY_VALUE>0</CDTY_VALUE>
               <PU_DROP_PO/>
               <LOAD_UNLOAD/>
               <PALLETS/>
               <NOTIFY_DELIVER/>
               <NOTIFY_DEL_DATE/>
               <NOTIFY_DEL_TIME/>
               <DRIVER_DISPATCH/>
               <DISPATCH_DATE/>
               <DISPATCH_TIME/>
               <DISPATCH_FROM/>
               <DRIVER_IN/>
               <IN_DATE/>
               <IN_TIME/>
               <DEPARTED/>
               <DEPART_DATE/>
               <DEPART_TIME/>
               <LONGITUDE>82.458611</LONGITUDE>
               <LATTITUDE>27.947222</LATTITUDE>
               <DAMAGE_SHORT/>
               <UNIT1>1040</UNIT1>
               <UNIT_VALUE1>10000</UNIT_VALUE1>
               <UNIT2>1030</UNIT2>
               <UNIT_VALUE2>100</UNIT_VALUE2>
               <UNIT3>0</UNIT3>
               <UNIT_VALUE3>0</UNIT_VALUE3>
               <UNIT4>0</UNIT4>
               <UNIT_VALUE4>0</UNIT_VALUE4>
               <UNIT5>0</UNIT5>
               <UNIT_VALUE5>0</UNIT_VALUE5>
               <UNIT6>0</UNIT6>
               <UNIT_VALUE6>0</UNIT_VALUE6>
               <UNIT7>0</UNIT7>
               <UNIT_VALUE7>0</UNIT_VALUE7>
               <UNIT8>0</UNIT8>
               <UNIT_VALUE8>0</UNIT_VALUE8>
               <UNIT9>0</UNIT9>
               <UNIT_VALUE9>0</UNIT_VALUE9>
               <UNIT10>0</UNIT10>
               <UNIT_VALUE10>0</UNIT_VALUE10>
               <STAMP_DATE>08/07/2013</STAMP_DATE>
               <STAMP_TIME>132400</STAMP_TIME>
               <USER_ID>SYS</USER_ID>
               <E_MAIL>don@freight-mngt.com</E_MAIL>
               <NEWCUST_NO>2959</NEWCUST_NO>
               <NEWCUST_SITE>1</NEWCUST_SITE>
               <APPOINT_NO>5678</APPOINT_NO>
               <APPOINT_SETBY/>
               <LENGTH>0</LENGTH>
               <WIDTH>0</WIDTH>
               <HEIGHT>0</HEIGHT>
               <OVERSIZE/>
               <TEMPERATURE>0</TEMPERATURE>
               <TEMPERATURE2>0</TEMPERATURE2>
               <HAZMAT/>
               <NOT_DEL_REASON/>
               <DISPATCH_REASON/>
               <DRIVERIN_REASON/>
               <DEPARTED_REASON/>
               <NOT_DEL_ID/>
               <DISPATCH_ID/>
               <DRIVERIN_ID/>
               <DEPARTED_ID/>
               <CDTY_UNITS>0</CDTY_UNITS>
               <CDTY_KINOFPKG/>
               <CDTY_NMFC_NUM/>
               <CDTY_CLASS/>
               <NOT_DEL_214/>
               <DISPATCH_214/>
               <DRIVERIN_214/>
               <DEPARTED_214/>
               <NOTIFY_CON1/>
               <NOTIFY_CON2/>
               <EMAIL_CON1/>
               <EMAIL_CON2/>
               <NOTIFY_EMAIL>Y</NOTIFY_EMAIL>
               <PickdropNumbers>
                    <ReferenceNumber Type="PONumber">123456</ReferenceNumber>
               </PickdropNumbers>
          </PICKDROP>
          <TRACKING_NOTES/>
          <BOOKING Sequence="1">
               <Name>_TRUCKING SERVICES</Name>
               <Driver_LastName>JONES</Driver_LastName>
               <Driver_FirstName>ALBERT</Driver_FirstName>
               <Driver_Cell/>
               <Driver_Email>aj@trucking.com</Driver_Email>
               <Driver2_LastName/>
               <Driver2_FirstName/>
               <Driver2_Cell/>
               <Driver2_Email/>
               <Tractor>95</Tractor>
               <Tractor_ID>0</Tractor_ID>
               <Trailer>102</Trailer>
               <Trailer_ID>0</Trailer_ID>
          </BOOKING>
     </LOAD>
</EDI214>
*/
?>