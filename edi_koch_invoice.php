<? include('application.php') ?>
<? include_once('functions_koch.php') ?>
<?
	$mrr_test=0;		//toggles output only of file to test its output...does not send the file or create the existing one on our side...only outputs.
	$mrr_output=0;
	if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' || $_SERVER['HTTP_CF_CONNECTING_IP']=='70.90.229.29')	{	$mrr_test=1;	$mrr_output=1;		}
	
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
		
	if(isset($_GET['autorun'])) 
	{
		//die('disabled for now');
		// this is for the daily scheduled task
		// koch customer id is 273
		// this EDI system was implemented after invoice number 118549, so only send
		// loads with the invoice number greater than that
		$sql = "
			SELECT * 			
			FROM load_handler 
			WHERE customer_id = 273 
				AND deleted = 0 				
				AND sicap_invoice_number >= 118549
				AND linedate_edi_invoice_sent = 0
				AND id>=128248
			ORDER BY id DESC
		";   //and koch_edi>0
		$data = simple_query($sql);		
		while($row = mysqli_fetch_array($data)) 
		{
			echo "Sending Load: $row[id] | Invoice #: $row[sicap_invoice_number]";
			edi_koch_create_invoice($row['id'],0,$mrr_output);
			echo " | done...<br>";
		}
	} 
	else 
	{
		if(!isset($_GET['lid'])) 
		{
			die("Missing Load ID");
		}		
		$load_id = $_GET['lid'];
		
		edi_koch_create_invoice($load_id,$mrr_test,$mrr_output);
	}

	function edi_koch_create_invoice($load_id,$test_file=0,$mrr_output=0) 
	{
		global $defaultsarray;
		
		$in_path = $defaultsarray['koch_edi_path'];
		
		$process_210 = true;
		
		$sicap_invoice_number=0;
		
		$sql = "
			select *,
				(select sum(miles) from trucks_log tl where tl.deleted = 0 and tl.load_handler_id = load_handler.id) as total_miles			
			from load_handler
			where id = '".sql_friendly($load_id)."'
		";
		$data = simple_query($sql);		
		if(!mysqli_num_rows($data)) 
		{
			echo "Could not locate load";
			$process_210 = false;
		} 
		else 
		{
			$row = mysqli_fetch_array($data);
			$sicap_invoice_number=(int) trim($row['invoice_number']);
			if($sicap_invoice_number==0 || trim($row['sicap_invoice_number'])!="")		$sicap_invoice_number=(int) trim($row['sicap_invoice_number']);
		}
		
		if(strtotime($row['linedate_edi_invoice_sent']) > 0 && $test_file==0) 
		{
			echo "EDI Invoice already sent on ".date("M j, Y h:i a", strtotime($row['linedate_edi_invoice_sent']));
			$process_210 = false;
		}
		
		if($process_210) 
		{		
			// get the delivery date
			$sql = "
				select ifnull(max(linedate_completed),0) as linedate_completed				
				from load_handler_stops
				where load_handler_id = '".sql_friendly($load_id)."'
					and stop_type_id = 2
					and deleted = 0
					and linedate_completed > 0
					and linedate_completed is not null
			";
			$data_stop = simple_query($sql);
			$row_stop = mysqli_fetch_array($data_stop);
			
			if($row_stop['linedate_completed'] == 0) 
			{
				echo "No completed stops found";
				$process_210 = false;
			} 
			else 
			{
				
			}
		}
				
		if($process_210) 
		{			
			$sql = "
				select *				
				from load_handler_stops
				where load_handler_id = '".sql_friendly($load_id)."'
					and deleted = 0
				order by linedate_pickup_eta, id
			";
			$data_stops = simple_query($sql);
			
			//$use_control_number = "000000001";
			$use_control_number = str_pad($load_id, "9", "0", STR_PAD_LEFT);
			$comp_code="KLOG";       
	
			// create the 210 response
			$rval = "ISA*00*          *00*          *02*".$defaultsarray['edi_scac_code']."           *02*".$comp_code."           *".date("ymd")."*".date("Hi")."*X*00401*$use_control_number*0*P*>\n";
			$rval .= "GS*IM*".$defaultsarray['edi_scac_code']."*".$comp_code."*".date("Ymd")."*".date("Hi")."*$use_control_number*X*004010\n";
			$rval .= "ST*210*$use_control_number\n";
			$rval .= "B3*B*$load_id*$row[load_number]*TP*L*".date("Ymd")."*".str_replace(".","",$row['actual_bill_customer'])."**".date("Ymd", strtotime($row_stop['linedate_completed']))."*035*".$defaultsarray['edi_scac_code']."*".date("Ymd")."\n";
			
			$se_counter=2;
			
			$mrr_rval="";
			$stop_count = 0;
			while($row_stops = mysqli_fetch_array($data_stops)) 
			{				
				if($row_stops['shipper_address1'] != '') 
				{					
					$stop_count++;
					$mrr_rval="";		//clear it for the next one.
					
					$mrr_rval .= "N1*".($row_stops['stop_type_id'] == 1 ? "SH" : "CN")."*".$row_stops['shipper_name'].($row_stops['shipper_address2'] != '' ?"*94*$row_stops[shipper_address2]" : "")."\n";
					$mrr_rval .= "N3*".$row_stops['shipper_address1']."\n";
					$mrr_rval .= "N4*$row_stops[shipper_city]*".trim($row_stops['shipper_state'])."*$row_stops[shipper_zip]*US\n";
					
					if($stop_count==1)	
                         {		//should get the first stop in the list.
                              $rval.=$mrr_rval;
                              $se_counter+=3;
                         }
				}
			}
			
			$rval.=$mrr_rval;		//should get the last stop in the list.
               $se_counter+=3;
			
			//SELECT * FROM load_handler WHERE id=82047 OR id=82151 OR id=81818 or id=81698 ORDER BY id DESC
			
			//Load 81698:
			//actual_bill_customer = 3333.12
			//total_miles = 461
			//actual_fuel_surcharge_per_mile = 0.32                 
			// so, 0.32 x 461 miles = 147.52...  and 3333.12 - 147.52 = 3185.60
						
			$use_miles=$row['total_miles'];
			if($row['koch_edi_invoice_mileage'] > 0)		$use_miles= $row['koch_edi_invoice_mileage'];
			
			$use_miles=$use_miles * 1.000;
			
			$fuel_surcharge = $row['actual_fuel_surcharge_per_mile'] * $use_miles;
			$charge_no_fuel_surcharge = $row['actual_bill_customer'] - $fuel_surcharge;

			$rval .= "LX*1\n";			
			$rval .= "L0**".$use_miles."*DM\n";
			$rval .= "L1*1*".number_format($charge_no_fuel_surcharge,0,'','')."*FR*".number_format($charge_no_fuel_surcharge,2,'','')."\n";
			$rval .= "LX*2\n";
			$rval .= "L1*2*".number_format($row['actual_fuel_surcharge_per_mile'],2)."*PM*".number_format($fuel_surcharge,2,'','')."****FUE\n";
			$rval .= "L3*****".str_replace(".","",$row['actual_bill_customer'])."\n";
               
               $se_counter+=6;
               $se_counter+=1;
			
			//$row_count = 9 + ($stop_count * 3);
               $row_count=$se_counter;
			
			$rval .= "SE*$row_count*$use_control_number\n";
			$rval .= "GE*1*$use_control_number\n";
			$rval .= "IEA*1*$use_control_number\n";
               
               $rval=str_replace("\n","~",$rval);
			
			//d($rval);
			
			$rslt = 0;
			$out_file = "210_$row[load_number]_".time().".txt";
			
			if($test_file==0)
			{
     			file_put_contents($in_path."/out/".$out_file, $rval);     	     			
     			
     			//$rslt = ftp_put($conn_id, "/KLOG/210/".$out_file, $in_path."/out/".$out_file, FTP_BINARY);
     			if(send_koch_edi('/Inbox/', $in_path."/out/", $out_file, $load_id,$mrr_output)) 
     			{    //KLOG/210
     				// upload was successful, remove that file from the server
     				echo "<br><font color='green'>Successful Upload</font>";
     				$rslt = 1;
     				$msg = "EDI Invoice Sent Successfully";
     				
     				// log the upload
     				$sql = "
     					update load_handler set		
     						koch_edi_invoice_file='".sql_friendly("/out/".$out_file)."',	
     						koch_edi_invoice_file_text='".sql_friendly($rval)."',		
     						linedate_edi_invoice_sent = now()
     					where id = '".sql_friendly($load_id)."'
     				";
     				simple_query($sql);	
     				
     				//mrr_add_koch_edi_invoicing_log(0,$load_id,$sicap_invoice_number,$rval,1,0,0,(int) $use_miles);
     			}
     			else
     			{
     				//store the file names so that it can be resent if file is still there.	 Added on 7/25/2018 by MRR to recover the files attempted.				
     				echo "<br><font color='red'>FAILED Upload</font>";
     				$sql = "
     					update load_handler set					
     						koch_edi_invoice_file='".sql_friendly("/out/".$out_file)."',	
     						koch_edi_invoice_file_text='".sql_friendly($rval)."',	
     					where id = '".sql_friendly($load_id)."'
     				";
     				simple_query($sql);
     				
     				//mrr_add_koch_edi_invoicing_log(0,$load_id,$sicap_invoice_number,$rval,1,0,0,(int) $use_miles);
     			}
			}
			else
			{
				//mrr_add_koch_edi_invoicing_log(0,$load_id,$sicap_invoice_number,$rval,1,0,0,(int) $use_miles);
			}
			
			mrr_add_koch_edi_invoicing_log(0,$load_id,$sicap_invoice_number,$rval,1,0,0,(int) $use_miles);
			
			echo "<br>FILE IS: $out_file<br><br>";
			echo "<pre>";
			print_r($rval);
			echo "</pre>";			
		}
	}
	
	echo "<br><br>done...";
?>