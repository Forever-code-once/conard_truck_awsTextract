<? include('application.php') ?>
<? error_reporting(E_ALL ^ E_DEPRECATED); ?>
<? include_once('functions_lynnco.php'); ?>
<?
	$sql = "
		update load_handler_stops set 
			latitude='40.741895',
			longitude='-73.989308' 
		where shipper_address1='2448 E 1st St.' 
			and latitude=''
			and longitude='' 
		order by id desc
	";
	//simple_query($sql);
	
	// process 204's (load tender request) from fedex

	$in_path = $defaultsarray['edi_lynnco_path'];
	
	$block_file_rename=0;       //block if the approval process is awaiting files...

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
	
	$cntr=0;
	//echo "<br>Got Here...";
	
	$d = dir($in_path);
	while (false !== ($entry = $d->read())) 
	{
		$cntr++;
		
		if(!is_dir($in_path.$entry)) 
		{
	   		echo $entry."<br>";
	   		edi_lynnco_process_file($entry);
	   	}
	   	//echo "<br>Got Here...".$cntr.".";
	}
	
	//echo "<br>Got Here...(T = ".$cntr.")";
	
	function edi_lynnco_process_file($file) 
	{
		global $descriptor;
		global $defaultsarray;
		global $in_path;
		global $block_file_rename;
		
		$cancellation=0;
		if(substr_count(strtolower($file),"-cancel-") > 0 && substr_count($file,"-204-") > 0)  
        {
            $cancellation=1;
            //$file=str_replace("-cancel","",$file);
            //$file=str_replace("-CANCEL","",$file);
            //$file=str_replace("-Cancel","",$file);
        }
         
        echo "Processing file: $file ".($cancellation > 0 ? "CANCELLED" : "")."";
		
		$fcontents = file_get_contents($in_path.$file);
		
		$fcontents = str_replace("~",chr(10),$fcontents);
		$fcontents = str_replace(chr(13),"",$fcontents);
		$line_array = explode(chr(10), $fcontents);

		//var_dump($line_array);

		echo "<table>";
        $process_stop = false;
        $stop_array=array();
		
		
		$info['mrr_load_notes']="";
		$info['spec_notes'] = "";
		$info['pickup_no'] = "";
		$info['commodity'] = "";
		
		$info['shipper_datetime'] = "";
		$info['consignee_datetime'] = "";
		
		$info['mrr_billing_amount'] = "";
		$info['mrr_billing_line'] = "";
				
		foreach($line_array as $line) 
		{
			$line_part_array = explode("*",$line);
			
			//echo "<br> Part: ".$line_part_array[0].".";
			
			// stop entry
			if($line_part_array[0] == 'SE') 
			{
				$info['mrr_file_name']=trim($file);
				process_edi_load_lynnco($info, $file, $stop_array,$cancellation);
				
				unset($info);
				unset($stop_array);
				$process_stop = false;
			}
			elseif($line_part_array[0] == 'S5' || $process_stop) 
			{				
				if($line_part_array[0] == 'S5') 
				{
					$stop_number = $line_part_array[1];
				
					$process_stop = true;
					$stop_array[$stop_number] = array();
					$stop_array[$stop_number]['stop_type'] = $line_part_array[2];
				}
								
				if($line_part_array[0] == 'N1') $stop_array[$stop_number]['name'] = $line_part_array[2];
				if($line_part_array[0] == 'N1') $stop_array[$stop_number]['address2'] = $line_part_array[4];
				if($line_part_array[0] == 'N3') $stop_array[$stop_number]['address'] = $line_part_array[1];
				if($line_part_array[0] == 'N4') $stop_array[$stop_number]['city'] = $line_part_array[1];
				if($line_part_array[0] == 'N4') $stop_array[$stop_number]['state'] = $line_part_array[2];
				if($line_part_array[0] == 'N4') $stop_array[$stop_number]['zip'] = $line_part_array[3];
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '64') $stop_array[$stop_number]['date'] = $line_part_array[2];
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '64') $stop_array[$stop_number]['time'] = $line_part_array[4];
				
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '37') 
				{
					$timer_x=trim($line_part_array[4]);
					$hrs=substr($timer_x,0,2);
					$mns=substr($timer_x,2);
					$timer_x=$hrs.":".$mns."";
					
					$stop_array[$stop_number]['load_datetime'] = strtotime($line_part_array[2].' '.$timer_x);
					$stop_array[$stop_number]['load_date'] = $line_part_array[2];
					$stop_array[$stop_number]['load_time'] = $timer_x;
					
					$info['shipper_datetime'] = "".strtotime($line_part_array[2].' '.$timer_x)."";
				}
				if($line_part_array[0] == 'G62' && $line_part_array[1] == '38') 
				{
					$timer_x=trim($line_part_array[4]);
					$hrs=substr($timer_x,0,2);
					$mns=substr($timer_x,2);
					$timer_x=$hrs.":".$mns."";
					
					$stop_array[$stop_number]['delivery_datetime'] = strtotime($line_part_array[2].' '.$timer_x);
					$stop_array[$stop_number]['deliver_date'] = $line_part_array[2];
					$stop_array[$stop_number]['deliver_time'] = $timer_x;
					
					$info['consignee_datetime'] = "".strtotime($line_part_array[2].' '.$timer_x)."";
				}				
				if($line_part_array[0] == 'G61') 
				{
					$stop_array[$stop_number]['contact_name'] = $line_part_array[2];
					$stop_array[$stop_number]['contact_phone'] = $line_part_array[4];
					//$process_stop = false;
				}
				
				
				if($line_part_array[0] == 'L3') 
				{
					$info['bill_customer'] = $line_part_array[3];
					$info['mrr_billing_amount'] = "(3) ".$line_part_array[3]." (4) ".$line_part_array[4]." (5)".$line_part_array[5].".";
					
					$info['mrr_billing_line']=trim($line);
				}
				if($line_part_array[0] == 'L11' && trim($line_part_array[3])=="Pickup Number") 		$info['pickup_no'] = trim($line_part_array[1]);	//." ".trim($line_part_array[2])." ".trim($line_part_array[3])."";
				if($line_part_array[0] == 'L5') 									$info['commodity'] = trim($line_part_array[2]);	//." ".trim($line_part_array[2])." ".trim($line_part_array[3])."";
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
				if($line_part_array[0] == 'B2') $info['load_number'] = trim($line_part_array[4]);
				if($line_part_array[0] == 'B2')
				{
					//$info['mrr_load_notes'].="B2 - ";
					//$info['mrr_load_notes'].=" [1] ".trim($line_part_array[1]).",";		
					//$info['mrr_load_notes'].=" [2] ".trim($line_part_array[2]).",";
					//$info['mrr_load_notes'].=" [3] ".trim($line_part_array[3]).",";
					//$info['mrr_load_notes'].=" [4] ".trim($line_part_array[4]).",";
					//$info['mrr_load_notes'].=" [5] ".trim($line_part_array[5]).",";
					//$info['mrr_load_notes'].=" [6] ".trim($line_part_array[6])."";
					
				}
				if($line_part_array[0] == 'B2A') $info['set_purpose'] = $line_part_array[1];
				if($line_part_array[0] == 'B2A') $info['set_purpose2'] = $line_part_array[2];
				if($line_part_array[0] == 'G61') $info['contact_name'] = $line_part_array[2];
				if($line_part_array[0] == 'G61') $info['contact_phone'] = $line_part_array[4];
				if($line_part_array[0] == 'L3') 
				{
					$info['bill_customer'] = $line_part_array[3];
					$info['mrr_billing_amount'] = "(3) ".$line_part_array[3]." (4) ".$line_part_array[4]." (5)".$line_part_array[5].".";
					
					$info['mrr_billing_line']=trim($line);
				}
				if($line_part_array[0] == 'AK2') $info['edi_return_type'] = $line_part_array[1];
				if($line_part_array[0] == 'AK2') $info['edi_return_load_id'] = $line_part_array[2];
				
				//new ones added for LynnCo
				if($line_part_array[0] == 'NTE') 		$info['spec_notes'] = " ...".trim($line_part_array[2]);
				//if($line_part_array[0] == 'L11') 	$info['pickup_no'] .= trim($line_part_array[1])."".trim($line_part_array[2])."".trim($line_part_array[3])."";
				//if($line_part_array[0] == 'L5') 		$info['commodity'] .= trim($line_part_array[1])."".trim($line_part_array[2])."".trim($line_part_array[3])."";
				
			}
			
			/*
				//Real 204 examples....Load 89483
				
				ISA*00*          *00*          *ZZ*MGLYNNCO       *02*CDPN           *190328*1601*U*00401*000000233*0*P*:~
				GS*SM*MGLYNNCO*CDPN*20190328*160101*54*X*004010~
				ST*204*000540001~
				B2**CDPN**003623719MG**TP~
				B2A*01*LT~
				NTE**Must Schedule Delivery Appointment Emailed in by Jessica~
				N1*BT*CT LOGISTICS - TEAM 7*93*L312540~
				N3*PO BOX 30382~
				N4*CLEVELAND*OH*441300382*USA~
				G61*IC*RACHELLE EBERHART*TE*216-267-2000 EXT 2097~
				S5*1*CL~
				L11*003623719MG*BM*BOL~
				G62*37*20190329*I*0700~
				G62*38*20190329*K*1700~
				AT8*G*L*40000**20~
				N1*SH*ROSEMILL~
				N3*681 HEIL QUAKER AVE~
				N4*LEWISBURG*TN*370912160*USA~
				L5**GLASS***CRT*003623719MG*SI~
				AT8*G*L*40000**20~
				S5*2*CU~
				L11*003623719MG*BM*BOL~
				G62*53*20190329*G*0800~
				G62*54*20190329*L*1500~
				AT8*G*L*40000**20~
				N1*CN*CARLEX - AFTERMARKET DISTRIBUTION (ARG)*93*L310850~
				N3*340 BRIDGESTONE PKWY~
				N4*LEBANON*TN*370907015*USA~
				G61*IC*TI LAVENDER*TE*615-257-5711~
				L5**GLASS***CRT*003623719MG*SI~
				AT8*G*L*40000**20~
				L3*40000*G*326.68*FR*32668******20~
				SE*31*000540001~
				GE*1*54~
				IEA*1*000000233~

			
				
				
				
				
				//Real 204 examples....Load 88653
			
				ISA*00*          *00*          *ZZ*MGLYNNCO       *02*CDPN           *190305*1657*U*00401*000000008*0*P*:~
                    GS*SM*MGLYNNCO*CDPN*20190305*165745*2*X*004010~
                    ST*204*000020001~
                    B2**CDPN**003600255MG**TP~
                    B2A*00*LT~
                    NTE**MUST MAKE PICKUP APPOINTMENT~
                    N1*BT*CT LOGISTICS - TEAM 7*93*L312540~
                    N3*PO BOX 30382~
                    N4*CLEVELAND*OH*441300382*USA~
                    G61*IC*RACHELLE EBERHART*TE*216-267-2000 EXT 2097~
                    S5*1*CL~
                    L11*003600255MG*BM*BOL~
                    L11*BGA030619*CR*Pickup Number~
                    G62*37*20190306*I*0900~
                    G62*38*20190306*K*1500~
                    AT8*G*L*10000**1~
                    N1*SH*Carlex - ARG*93*L310850~
                    N3*340 BRIDGESTONE PKWY~
                    N4*LEBANON*TN*370907015*USA~
                    L5**GLASS***TRU*003600255MG*SI~
                    AT8*G*L*10000**1~
                    S5*2*CU~
                    L11*003600255MG*BM*BOL~
                    L11*BGA030619*CR*Pickup Number~
                    G62*53*20190306*G*0900~
                    G62*54*20190306*L*1500~
                    AT8*G*L*10000**1~
                    N1*CN*SAFELITTE AUTO GLASS*93*L220261~
                    N3*1350 BRASELTON PKWY~
                    N4*BRASELTON*GA*305175324*USA~
                    L5**GLASS***TRU*003600255MG*SI~
                    AT8*G*L*10000**1~
                    L3*10000*G*758.19*FR*75819******1~
                    SE*32*000020001~
                    GE*1*2~
                    IEA*1*000000008~
                    
                    
                    ISA*00*          *00*          *ZZ*MGLYNNCO       *02*CDPN           *190306*0954*U*00401*000000009*0*P*:~
                    GS*SM*MGLYNNCO*CDPN*20190306*095453*3*X*004010~
                    ST*204*000030001~
                    B2**CDPN**003601398MG**TP~
                    B2A*00*LT~
                    NTE**Must Schedule Delivery Appointment Emailed in by Tracy Finch~
                    N1*BT*CT LOGISTICS - TEAM 7*93*L312540~
                    N3*PO BOX 30382~
                    N4*CLEVELAND*OH*441300382*USA~
                    G61*IC*RACHELLE EBERHART*TE*216-267-2000 EXT 2097~
                    S5*1*CL~
                    L11*003601398MG*BM*BOL~
                    G62*37*20190306*I*0900~
                    G62*38*20190306*K*1200~
                    AT8*G*L*44000**20~
                    N1*SH*ROSEMILL~
                    N3*681 HEIL QUAKER AVE~
                    N4*LEWISBURG*TN*370912160*USA~
                    L5**GLASS***CRT*003601398MG*SI~
                    AT8*G*L*44000**20~
                    S5*2*CU~
                    L11*003601398MG*BM*BOL~
                    G62*53*20190306*G*0800~
                    G62*54*20190306*L*1500~
                    AT8*G*L*44000**20~
                    N1*CN*CARLEX - AFTERMARKET DISTRIBUTION (ARG)*93*L310850~
                    N3*340 BRIDGESTONE PKWY~
                    N4*LEBANON*TN*370907015*USA~
                    G61*IC*TI LAVENDER*TE*615-257-5711~
                    L5**GLASS***CRT*003601398MG*SI~
                    AT8*G*L*44000**20~
                    L3*44000*G*326.68*FR*32668******20~
                    SE*31*000030001~
                    GE*1*3~
                    IEA*1*000000009~
                    
                    ISA*00*          *00*          *ZZ*MGLYNNCO       *02*CDPN           *190306*0955*U*00401*000000010*0*P*:~
                    GS*SM*MGLYNNCO*CDPN*20190306*095518*4*X*004010~
                    ST*204*000040001~
                    B2**CDPN**003601399MG**TP~
                    B2A*00*LT~
                    NTE**Must Schedule Delivery Appointment Emailed in by Tracy Finch~
                    N1*BT*CT LOGISTICS - TEAM 7*93*L312540~
                    N3*PO BOX 30382~
                    N4*CLEVELAND*OH*441300382*USA~
                    G61*IC*RACHELLE EBERHART*TE*216-267-2000 EXT 2097~
                    S5*1*CL~
                    L11*003601399MG*BM*BOL~
                    G62*37*20190306*I*1400~
                    G62*38*20190306*K*1600~
                    AT8*G*L*44000**20~
                    N1*SH*ROSEMILL~
                    N3*681 HEIL QUAKER AVE~
                    N4*LEWISBURG*TN*370912160*USA~
                    L5**GLASS***CRT*003601399MG*SI~
                    AT8*G*L*44000**20~
                    S5*2*CU~
                    L11*003601399MG*BM*BOL~
                    G62*53*20190306*G*0800~
                    G62*54*20190306*L*1500~
                    AT8*G*L*44000**20~
                    N1*CN*CARLEX - AFTERMARKET DISTRIBUTION (ARG)*93*L310850~
                    N3*340 BRIDGESTONE PKWY~
                    N4*LEBANON*TN*370907015*USA~
                    G61*IC*TI LAVENDER*TE*615-257-5711~
                    L5**GLASS***CRT*003601399MG*SI~
                    AT8*G*L*44000**20~
                    L3*44000*G*326.68*FR*32668******20~
                    SE*31*000040001~
                    GE*1*4~
                    IEA*1*000000010~
			*/
			
			//if(isset($line_part_array[0])) 
			//{
                 echo "<tr>
				    	<td>" . $descriptor[$line_part_array[0]] . "</td>
					    <td>$line</td>
				    </tr>
			    ";
            //}
		}
		echo "</table>";
		
		//$info['mrr_file_name']=trim($file);
         
        if($block_file_rename ==0)		rename($in_path.$file,$in_path."backup/".$file);		
	}
	
	
	function process_edi_load_lynnco($info, $file, $stop_array, $cancellation=0) 
	{
		global $in_path;
		global $defaultsarray;
		global $block_file_rename;
		global $datasource;
		
		//echo "<br>EDI Load ID = ".$info['edi_return_load_id'].". EDI File: ".$info['edi_file_type'].".<br>";
		
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
			where name_company = 'Carlex Glass C/O LynnCo'
				and deleted = 0
		";
		$data_cust = simple_query($sql);
		$row_cust = mysqli_fetch_array($data_cust);
		
		if($cancellation > 0)       
        {
             $info['load_number']=str_replace("-CANCEL","",$info['load_number']);
             $info['load_number']=str_replace("-Cancel","",$info['load_number']);
             $info['load_number']=str_replace("-cancel","",$info['load_number']);
        }
         
         //First,check to make sure that the load was accepted... or if it was declined, send the 990 response for it if not already sent.   Added by MRR on 10/3/2019
         $sql = "
			     select *			
			     from edi_accepted_loads
			     where load_no = '".sql_friendly(trim($info['load_number']))."'
				    and deleted = 0
		      ";
         $data = simple_query($sql);
         if($row = mysqli_fetch_array($data))
         {
              // found it, so it is already in the system...  see if already accepted, or declined, and change the links accordingly to show it is marked...
              
              if($cancellation > 0)
              {
                   $email_to="trucking@conardtransportation.com";
                   $email_to2="";   //"dconard@conardtransportation.com";
                   $email_to3="";   //$defaultsarray['special_email_monitor'];
                   
                   $can_load=0;
     
                   $sqlt = "
                     select id 
                     from load_handler 
                     where load_number = '".sql_friendly(trim($row['load_no']))."'
                        and deleted = 0
                    order by id asc
                  ";
                   $datat = simple_query($sqlt);
                   if($rowt = mysqli_fetch_array($datat)) 
                   {
                        $can_load=$rowt['id'];
                   }
     
                   //send the email out...
                   $subj="Lynnco/MercuryGate EDI Load CANCELLED";
                   $msg="
                        Looks like Lynnco/MercuryGate EDI Load ".trim($info['load_number'])." (".trim($row['load_no']).") has been cancelled.  Please verify or update the system as necessary. 
                        <br>Click the link (assuming you are logged in) to view the load.
                        <br><br>
                        <a href='https://trucking.conardtransportation.com/manage_load.php?load_id=".$can_load."'>https://trucking.conardtransportation.com/manage_load.php?load_id=".$can_load."</a>
                        <br>
                        <br>Thank you. <br>
                    ";                 
                 
                   //mrr_trucking_sendMail($email_to,'EDI Cancelled',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subj,$msg,$msg);
                   if(trim($email_to2)!="")		mrr_trucking_sendMail(trim($email_to2),'EDI Cancelled',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subj,$msg,$msg);
                   if(trim($email_to3)!="")		mrr_trucking_sendMail(trim($email_to3),'EDI Cancelled',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subj,$msg,$msg);
                   return false;
              }             
              
              if($row['accepted_by'] > 0)
              {
                   //approved, so carry on...  no need to do anything... continue on this function to make and send EDI response back. 
              }
              elseif($row['declined_by'] > 0)
              {
                   if(trim($row['response_990'])!="")
                   {    //990 Response already sent...so this load has already been processed, but refused/rejected/declined.
                        echo "<br><span style='color:#cc0000;'><b>Already DECLINED this LynnCo/Carlex load: $info[load_number]</b></span><br>";
                        return false;
                   }
                   else
                   {    //Declined, but the 990 has not beed sent yet to refuse them...     
                        $use_control_number = str_replace("MG","",$info['load_number']);     //use there number as our controol number for a load not being created at all on the Conard side.
                        //$use_control_number = str_pad($load_handler_id, 9, "0", STR_PAD_LEFT);
                        $comp_code="MGLYNNCO       ";					//can be 15 chanracters.
                        
                        //$info['load_number']=str_replace(" ","",$info['load_number']);
                        //$info['load_number']=str_replace("&nbsp;","",$info['load_number']);
                        
                        $tender_resp_code="D";       //Declined
                        //$tender_resp_code="R";       //Remove or Delete
                        //$tender_resp_code="A";       //Accepted
                        
                        // create the 990 response
                        $rval = "ISA*00*          *00*          *02*".$defaultsarray['edi_scac_code']."           *ZZ*".$comp_code."*".date("ymd")."*".date("Hi")."*X*00401*$use_control_number*0*P*>\n";
                        $rval .= "GS*GF*".$defaultsarray['edi_scac_code']."*".$comp_code."*".date("Ymd")."*".date("Hi")."*$use_control_number*X*004010\n";
                        $rval .= "ST*990*$use_control_number\n";
                        $rval .= "B1*".$defaultsarray['edi_scac_code']."*".trim($info['load_number'])."*".date("Ymd")."*".$tender_resp_code."\n";		//".$defaultsarray['edi_scac_code']."*
                        $rval .= "N9*CN*$load_handler_id\n";
                        //$rval .= "N9*BN**".$defaultsarray['edi_company_name']."\n";
                        $rval .= "SE*4*$use_control_number\n";
                        $rval .= "GE*1*$use_control_number\n";
                        $rval .= "IEA*1*$use_control_number\n";
                        
                        $sqlu = "		
                            update edi_accepted_loads set 
                                 response_990='".sql_friendly(trim($rval))."'                            
                            where load_no = '".sql_friendly(trim($info['load_number']))."'
                                 and deleted = 0
                         ";
                        simple_query($sqlu);
                        
                        $out_file = "990_$info[load_number]_".time().".txt";
                        file_put_contents($in_path."/out/".$out_file, $rval);
                        
                        if(send_lynnco_edi('/Inbound/', $in_path."/out/", $out_file, 0))
                        {
                             echo "<br><span style='color:#cc0000;'><b>Just DECLINED this LynnCo/Carlex load: $info[load_number].  Send 990 Response as well.</b></span><br>";
                        }
                        else
                        {
                             echo "<br><span style='color:#cc0000;'><b>Just DECLINED this LynnCo/Carlex load: $info[load_number].  Prepared 990 Response as well for next transmission interval.</b></span><br>";
                             $block_file_rename=1;
                        }
                        return false;
                   }
              }
              else
              {
                   echo "<br><span style='color:#cc0000;'><b>Awaiting Approval or Rejection of LynnCo/Carlex load: $info[load_number]</b></span><br>";
                   $block_file_rename=1;
                   return false;  //Found, but not processed. Skip this load, not approved or declined yet... Awaiting approval or rejection from Justin/etc.
              }
         }
         else
         {
              echo "<br><span style='color:#cc0000;'><b>Awaiting Approval or Rejection of LynnCo/Carlex NEW load: $info[load_number]</b></span><br>";
              $block_file_rename=1;
              return false;  //NO RECORD FOUND... skip this load, not approved or declined yet... Awaiting approval or rejection from Justin/etc.
         }
         
         //Then, check to make sure we haven't already processed this load
		$sql = "
			select id
			
			from load_handler
			where load_number = '".sql_friendly($info['load_number'])."'
				and customer_id = '".sql_friendly($row_cust['id'])."'
				and lynnco_edi > 0
				and deleted = 0
		";
		$data_check = simple_query($sql);		
		if(mysqli_num_rows($data_check)) 
		{
			// already processed this load
			echo "<br><span style='color:#cc0000;'><b>Already processed LynnCo/Carlex load: $info[load_number]</b></span><br>";
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
				lynnco_edi,
				commodity,
				billing_notes,
				edi_source_file,
				driver_notes)
			
			values ('".sql_friendly($stop_array[1]['city'])."',
				'".sql_friendly($stop_array[1]['state'])."',
				'".sql_friendly($stop_array[count($stop_array)]['city'])."',
				'".sql_friendly($stop_array[count($stop_array)]['state'])."',
				now(),
				'".sql_friendly("Auto Created from LynnCo EDI file".trim($info['spec_notes']))."',
				'".sql_friendly($row_cust['id'])."',
				'".sql_friendly(trim($info['load_number']))."',
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
		
		//echo "<br>SQL (new ID is".$load_handler_id.")<br>".$sql."<br><hr>";
		
		echo "<br><br>Shipping Out: ".date("m/d/Y H:i",$info['shipper_datetime']).", Delivery time: ".date("m/d/Y H:i",$info['consignee_datetime']).". PU No. ".$info['pickup_no'].". Commodity=".trim($info['commodity']).".";
			
		
		//'Auto Created from LynnCo EDI file - respond by: ".date("M j, Y h:i a", strtotime($stop_array[1]['date']. ' '.$stop_array[1]['time']))."',
		
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
					actual_bill_customer='".sql_friendly($info['bill_customer'])."'
				where id='".sql_friendly($load_handler_id)."'
			";
			simple_query($sql);
		}
			
			
		//echo "<br><br>---=== MRR BILLING CAPTURE: ".$info['mrr_billing_amount'] ." ===---<br><br>Billing Line: ".$info['mrr_billing_line']."...<br><br>";
			
		// create the stops
		$stop_counter = 0;
		foreach($stop_array as $stop) 
		{
			$stop_counter++;
			if(!isset($stop['delivery_datetime'])) 	$stop['delivery_datetime'] = 0;
			if(!isset($stop['load_datetime'])) 	$stop['load_datetime'] = 0;
			$stop_type_id=2;
			
			if($stop_counter == 1) 
			{	// && $stop['stop_type'] == 'LD'
				// this is the first stop set up the pickup eta
				$sql = "
					update load_handler
					set linedate_pickup_eta = '".date("Y-m-d H:s", $info['shipper_datetime'])."'
					where id = '$load_handler_id'
					limit 1
				";
				simple_query($sql);
				$stop_type_id=1;
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
					'".($stop_type_id==1 ? '1' : '2')."',
					now(),
					'".sql_friendly($stop['contact_phone'])."',
					'".($stop_type_id==1 ? date("Y-m-d H:s", $info['shipper_datetime']) : date("Y-m-d H:s", $info['consignee_datetime']))."',
					0)
			";
			simple_query($sql);
		}
		
		update_origin_dest($load_handler_id);

		//$use_control_number = "000000001";
		$use_control_number = str_pad($load_handler_id, 9, "0", STR_PAD_LEFT);
		$comp_code="MGLYNNCO       ";					//can be 15 chanracters.
		$comp_name="LYNNCO SUPPLY CHAIN SOLUTIONS";
		$comp_address="PO BOX 2170";
		$comp_city="BROKEN ARROW";
		$comp_state="OK";
		$comp_zip="740132170";
		
		$info['load_number']=str_replace(" ","",$info['load_number']);
		$info['load_number']=str_replace("&nbsp;","",$info['load_number']);
         
        $tender_resp_code="D";       //Declined
        $tender_resp_code="R";       //Remove or Delete
		$tender_resp_code="A";       //Accepted
		
		// create the 990 response
		$rval = "ISA*00*          *00*          *02*".$defaultsarray['edi_scac_code']."           *ZZ*".$comp_code."*".date("ymd")."*".date("Hi")."*X*00401*$use_control_number*0*P*>\n";
		$rval .= "GS*GF*".$defaultsarray['edi_scac_code']."*".$comp_code."*".date("Ymd")."*".date("Hi")."*$use_control_number*X*004010\n";
		$rval .= "ST*990*$use_control_number\n";
		$rval .= "B1*".$defaultsarray['edi_scac_code']."*".trim($info['load_number'])."*".date("Ymd")."*".$tender_resp_code."\n";		//".$defaultsarray['edi_scac_code']."*
		$rval .= "N9*CN*$load_handler_id\n";
		//$rval .= "N9*BN**".$defaultsarray['edi_company_name']."\n";
		$rval .= "SE*4*$use_control_number\n";
		$rval .= "GE*1*$use_control_number\n";
		$rval .= "IEA*1*$use_control_number\n";
		/*
		ISA*00*          *00*          *02*CDPN           *ZZ*MGLYNNCO       *120210*1124*U*00401*000001000*0*P*>
		GS*GF*CDPN*MGLYNNCO*20180210*1124*1000*X*004010
		ST*990*1000
		B1*CDPN*CDPN79365T*20190215*A
		SE*5*1000
		GE*1*1000
		IEA*1*000001000
		
		ISA*00*          *00*          *02*CDPN           *ZZ*MGLYNNCO       *190328*1346*X*00401*000089483*0*P*>
		GS*GF*CDPN*MGLYNNCO       *20190328*1346*000089483*X*004010
		ST*990*000089483
		B1*CDPN*003623719MG*20190328*A
		N9*CN*89483
		SE*4*000089483
		GE*1*000089483
		IEA*1*000089483		
		*/			
		
		$out_file = "990_$info[load_number]_".time().".txt";
		file_put_contents($in_path."/out/".$out_file, $rval);
		
				
		// send our response file to lynnco
		// send the file via FTP to our EDI provider
		
		$sql = "
			update load_handler set					
				lynnco_edi_input_file='/edi/lynnco_in/out/',
				lynnco_edi_output_file='".trim($out_file)."'
			where id = '".sql_friendly($load_handler_id)."'
		";
		simple_query($sql);
		
		
		//Send an email.
		$subject="New EDI Load for LynnCo Created";
		$msg="This is to let you know that a new load ($info[load_number]) from LynnCo EDI system has been created as Conard Load <a href='https://trucking.conardtransportation.com/manage_load.php?load_id=".$load_handler_id."'>".$load_handler_id."</a>.";		
		mrr_trucking_sendMail($defaultsarray['company_email_address'],"Dispatch","system@conardtransportation.com","LynnCo EDI","","",$subject,$msg,$msg);
		//mrr_trucking_sendMail($defaultsarray['special_email_monitor'],"Lord Vader","system@conardtransportation.com","LynnCo EDI","","",$subject,$msg,$msg);
		
		
		if(substr_count($out_file,"990_999") ==0)
		{
     		if(send_lynnco_edi('/Inbound/', $in_path."/out/", $out_file, $load_handler_id)) 
     		{
     			// upload was successful, remove that file from the server
     			//echo "<font color='green'>Successful Upload of 990 file.</font>";
     			$rslt = 1;
     			$msg = "LynnCo 990 EDI Sent Successfully";
     			
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
		}
		else
		{
			echo "<br><span style='color:#cc0000;'><b>'".$out_file." is only a TEST FILE and was not sent to LynnCo.</b></span><br><br>";
		}	
		
		
		$info['pickup_no'] = "";
		$info['commodity'] = "";
		
		//echo "<pre>$fcontents</pre>";
	}
?>
<br><br>done...