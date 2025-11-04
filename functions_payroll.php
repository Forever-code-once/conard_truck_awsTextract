<?
	//functions for Payroll Export to Peach Tree System...Added Jan 2016...MRR
	
	//primary control function	
	function mrr_payroll_check_api_connector($mode,$table="",$first_name="",$last_name="",$id=0)
	{
		global $defaultsarray;
		$url=trim($defaultsarray['payroll_check_api']);
		
		$res['rslt']=0;
		$res['url']=$url;
		$res['buffer']="";
		$res['html']="";
		$res['xml']="";
		
		$display="";
		$debug=0;		if($mode==6)		$debug=1;	
		
		if($url!="")
		{
			$full_url=$url;
			$res['url']=$full_url;
			
			
			$curl_handle=curl_init();		
			curl_setopt($curl_handle, CURLOPT_URL,$full_url);
			curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,10);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);	
			
			//parameter(s) needed for processing...
			$post['mode']=$mode;
			if($id > 0)				$post['id']=$id;
			if(trim($table)!="")		$post['tname']=trim($table);
			if(trim($first_name)!="")	$post['fname']=trim($first_name);
			if(trim($last_name)!="")		$post['lname']=trim($last_name);
					
			
			if($debug > 0)
			{
				$display.= "<br><b>Post Array contents:</b>";
				$icntr=0;
				foreach ($post as &$entry) 
				{
					
					if(is_array($entry))
					{
						$display.="<br>Level ".$icntr.":<br>";	
						foreach ($entry as &$value) 
						{
							$display.="<br>(".trim($value).")";	
						}						
					}
					else
					{
						$display.="<br>".$icntr.": (".trim($entry).")";	
					}
					$icntr++;
				}
			}							
     		curl_setopt($curl_handle, CURLOPT_POST,1);
     		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); // we don't want to stop the page from loading if there is ever an issue with the SSL
     		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post);
     		
     		$buffer = curl_exec($curl_handle);
     		curl_close($curl_handle);
     		
     		if($debug > 0)
     		{
     			$display.= "<br><b>Buffer contents:</b><br>";
     			$display.= "<pre>$buffer</pre>";
     		}
     		
     		$xmlObject = new SimpleXMLElement($buffer);
     		
     		$res['buffer']=$buffer;	
     		$res['xml']=$xmlObject;
     		$res['rslt']=1;
		}	
		
		$res['html']=$display;
		return $res;
	}
		
	
	//various modes controlled.
	function mrr_payroll_check_api_get_employee($first_name,$last_name)
	{	//pull driver info by first and last name separately...to avoid the middle initial
		$pres=mrr_payroll_check_api_connector(3,"",trim($first_name),trim($last_name));
		
		$out="<br>API: ".$pres['url'].": ";
		
		$res['id_name'][0]= "";
		$res['id'][0]= 0;
		$res['full'][0]= "";
		$res['last'][0]= "";
		$res['first'][0]= "";
		$res['middle'][0]= "";
		$res['addr1'][0]= "";
		$res['addr2'][0]= "";
		$res['city'][0]= "";
		$res['state'][0]= "";
		$res['zip'][0]= "";
		$res['inactive'][0]= 0;
		$res['active'][0]= 0;
		$res['hourly'][0]= 0;
		$res['direct_deposit'][0]= 0;
		$res['status'][0]= 0;
		$res['hired'][0]= "";
		$res['terminated'][0]= "";
		$res['birthdate'][0]= "";
		$res['rehire'][0]= "";
		$res['phone'][0]= "";		
		
		$cntr=0;
		
		if($pres['rslt'] > 0)
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#00cc00;'>Success! :) </span><br>";
			
			$xml=$pres['xml'];
			
			foreach ($xml->employee as $line) 
			{
  				$res['id_name'][$cntr]= (string) $line->ID;
  				$res['id'][$cntr]= (int) $line->Number;
  				$res['full'][$cntr]= (string) $line->FullName;
  				$res['last'][$cntr]= (string) $line->LastName;
  				$res['first'][$cntr]= (string) $line->FirstName;
  				$res['middle'][$cntr]= (string) $line->MiddleInitial;
  				$res['addr1'][$cntr]= (string) $line->Address1;
  				$res['addr2'][$cntr]= (string) $line->Address2;
  				$res['city'][$cntr]= (string) $line->City;
  				$res['state'][$cntr]= (string) $line->State;
  				$res['zip'][$cntr]= (string) $line->Zip;
  				$res['inactive'][$cntr]= (int) $line->Inactive;
  				$res['active'][$cntr]= (int) $line->ActEmployee;
  				$res['hourly'][$cntr]= (int) $line->Hourly;
  				$res['direct_deposit'][$cntr]= (int) $line->DirectDeposit;
  				$res['status'][$cntr]= (int) $line->Status;
  				$res['hired'][$cntr]= (string) $line->HireDate;
  				$res['terminated'][$cntr]= (string) $line->TerminateDate;
  				$res['birthdate'][$cntr]= (string) $line->BirthDate;
  				$res['rehire'][$cntr]= (string) $line->RehireDate;
  				$res['phone'][$cntr]= (string) $line->Phone;
  				
     			$cntr++;
			}			
		}
		else
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#cc0000;'>Failure. :( </span><br>";	
		}
		
		$out.="<br><hr><br>".$pres['html']."<br><hr><br>";
		
		$res['num']=$cntr;
		$res['output']=$out;
		return $res;
	}
	
	function mrr_payroll_check_api_get_employee_info($id)
	{	//pull driver info by first and last name separately...to avoid the middle initial
		$pres=mrr_payroll_check_api_connector(5,"","","",1);		//5th number is the employee record number...ID (but not the dispatch side ID)
		
		$out="<br>API: ".$pres['url'].": ";
		
		$res['id'][0]= 0;
  		$res['emp_id'][0]= 0;
  		$res['rate'][0]= 0;
  		$res['def'][0]= 0;
  		$res['acct_id'][0]= 0;
  		$res['pay_rate'][0]= "0.00";
  		$res['acct_name'][0]= "";
		
		$cntr=0;
		
		if($pres['rslt'] > 0)
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#00cc00;'>Success! :) </span><br>";
			
			$xml=$pres['xml'];
			
			foreach ($xml->PayInfo as $line) 
			{
  				$res['id'][$cntr]= (int) $line->ID;
  				$res['emp_id'][$cntr]= (int) $line->EmpID;
  				$res['rate'][$cntr]= (int) $line->Rate;
  				$res['def'][$cntr]= (int) $line->Default;
  				$res['acct_id'][$cntr]= (int) $line->AcctNum;
  				$res['pay_rate'][$cntr]= (string) $line->PayRate;
  				$res['acct_name'][$cntr]= (string) $line->AcctName;
  				
     			$cntr++;
			}			
		}
		else
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#cc0000;'>Failure. :( </span><br>";	
		}
		
		$out.="<br><hr><br>".$pres['html']."<br><hr><br>";
		
		$res['num']=$cntr;
		$res['output']=$out;
		return $res;
	}
	
	function mrr_payroll_check_api_get_employee_fields($id)
	{	//pull driver info by first and last name separately...to avoid the middle initial
		
		$pres=mrr_payroll_check_api_connector(6,"","","",1);		//5th number is the employee record number...ID (but not the dispatch side ID)
		
		$out="<br>API: ".$pres['url'].": ";
		
		$res['id'][0]= 0;
  		$res['emp_id'][0]= 0;
  		
  		$res['field_num'][0]= 0;
  		$res['custom'][0]= 0;
  		$res['acct_id'][0]= 0;
  		$res['calc'][0]= 0;
  		$res['calc_name'][0]= "";
  		$res['def_amnt'][0]= "0.00";
  		//$res['gross'][0]= 0;
  		$res['contra'][0]= "";
  		
  		$res['acct_name'][0]= "";
		
		$cntr=0;
		
		if($pres['rslt'] > 0)
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#00cc00;'>Success! :) </span><br>";
			
			$xml=$pres['xml'];
			
			foreach ($xml->PayInfo as $line) 
			{
  				$res['id'][$cntr]= (int) $line->ID;
  				$res['emp_id'][$cntr]= (int) $line->EmpID;
  				
  				$res['field_num'][$cntr]= (int) $line->FieldNum;
  				$res['custom'][$cntr]= (int) $line->Custom;
  				$res['acct_id'][$cntr]= (int) $line->AcctNum;
  				$res['calc'][$cntr]= (int) $line->Calc;
  				$res['calc_name'][$cntr]= (string) $line->CalcName;
  				$res['def_amnt'][$cntr]= (string) $line->DefaultAmount;
  				//$res['gross'][$cntr]= (int) $line->Gross;
  				$res['contra'][$cntr]= (string) $line->ContraAcct;
  				
  				$res['acct_name'][$cntr]= (string) $line->AcctName;
  				
     			$cntr++;
			}	
				
		}
		else
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#cc0000;'>Failure. :( </span><br>";	
		}
		
		$out.="<br><hr><br>".$pres['html']."<br><hr><br>";
		
		$res['num']=$cntr;
		
		$res['output']=$out;
		return $res;
		
	}	
	function mrr_payroll_check_api_get_employee_check($id)
	{	//pull driver info by first and last name separately...to avoid the middle initial
		
		$pres=mrr_payroll_check_api_connector(9,"","","",6904);		//5th number is the check number...
		
		$out="<br>API: ".$pres['url'].": ";
		
          $res['CustVendId'][0]="0"; 	
          $res['TransactionDate'][0]=""; 	
          $res['PostOrder'][0]="0"; 	
          $res['Description'][0]=""; 	
          $res['MainAmount'][0]="0"; 	
          $res['GLAcntNumber'][0]="0"; 	
          $res['TrxName'][0]=""; 	
          $res['TrxAddress1'][0]=""; 	
          $res['TrxAddress2'][0]=""; 	
          $res['TrxCity'][0]=""; 	
          $res['TrxState'][0]=""; 	
          $res['TrxZIP'][0]=""; 	
          $res['TrxCountry'][0]=""; 	
          $res['PaymentMethod'][0]=""; 	
          $res['ShipToName'][0]=""; 	
          $res['ShipToAddress1'][0]=""; 	
          $res['ShipToAddress2'][0]=""; 	
          $res['ShipToCity'][0]=""; 	
          $res['ShipToState'][0]=""; 	
          $res['ShipToZIP'][0]=""; 	
          $res['ShipToCountry'][0]=""; 	
          $res['EndOfPayPeriod'][0]=""; 	
          $res['WeeksWorked'][0]="0"; 	
          $res['PRHours1'][0]="0"; 	
          $res['PRHours2'][0]="0"; 	
          $res['PRHours3'][0]="0"; 	
          $res['PayMethod'][0]="0"; 	
          $res['LastPostedAt'][0]=""; 	
          
		$cntr=0;
		
		if($pres['rslt'] > 0)
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#00cc00;'>Success! :) </span><br>";
			
			$xml=$pres['xml'];
			
			foreach ($xml->CheckNum as $line) 
			{
  				foreach ($line as $key => $value) 
  				{
  					//if($cntr==0)		echo "<br>".$key."=".(string) trim($value).".";	
  					
  					$res[''.$key.''][$cntr]= (string) trim($value);
  				}
     			$cntr++;
			}	
				
		}
		else
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#cc0000;'>Failure. :( </span><br>";	
		}
		
		$out.="<br><hr><br>".$pres['html']."<br><hr><br>";
		
		$res['num']=$cntr;
		
		$res['output']=$out;
		return $res;		
	}
	function mrr_payroll_check_api_get_employee_check_info($id)
	{	//pull driver info by first and last name separately...to avoid the middle initial
		
		$pres=mrr_payroll_check_api_connector(10,"","","",6904);		//5th number is the check number...
		
		$out="<br>API: ".$pres['url'].": ";
		
          $res['MRRAcctDesc'][0]=""; 	
          $res['GLAcntNumber'][0]="0"; 
          $res['PostOrder'][0]="0";
          $res['RowNumber'][0]="0";
          $res['ItemRecordNumber'][0]="0";
          $res['JobRecordNumber'][0]="0";
          $res['PhaseRecordNumber'][0]="0";
          $res['CostRecordNumber'][0]="0";
          $res['LinkToAnotherTrx'][0]="0";
          $res['LinkToOtherTrxIndex'][0]="0";
          $res['Journal'][0]="0";
          $res['RowDate'][0]=""; 	
          $res['POSOIsClosed'][0]="0";
          $res['IncludeInGL'][0]="0";
          $res['IncludeInInvLedger'][0]="0";
          $res['RowType'][0]="0";
          $res['Amount'][0]="0.00"; 	
          $res['StockingQuantity'][0]="0.00"; 	
          $res['StockingUnitCost'][0]="0.00"; 	
          $res['AmountReceived'][0]="0.00"; 
          $res['StockingQtyReceived'][0]="0.00"; 
          $res['DistNumber'][0]="0";
          $res['RowDescription'][0]=""; 
          $res['CustomerRecordNumber'][0]="0";
          $res['VendorRecordNumber'][0]="0";
          $res['EmpRecordNumber'][0]="0";
          $res['UsedForReimbExp'][0]="0";
          $res['TaxAuthorityCode'][0]=""; 
          $res['SalesTaxType'][0]="0";
          $res['PayrollFieldNumber'][0]="0";
          $res['InvNumForThisTrx'][0]=""; 
          $res['DateCleared'][0]=""; 
          $res['Quantity'][0]="0.00"; 
          $res['QtyReceived'][0]="0.00"; 
          $res['UnitCost'][0]="0.00"; 
          $res['POCreated'][0]="0";
          $res['HasSerialNumbers'][0]="0";
          $res['RetainagePercent'][0]="0.00"; 
          $res['LaborBurdenPercent'][0]="0.00"; 
          $res['JournalRowEx'][0]="0";
          $res['LinkJournalRowEx'][0]="0";
          $res['LastUpdateCounter'][0]="0";
          
          
		$cntr=0;
		
		if($pres['rslt'] > 0)
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#00cc00;'>Success! :) </span><br>";
			
			$xml=$pres['xml'];
			
			foreach ($xml->CheckDetail as $line) 
			{
  				foreach ($line as $key => $value) 
  				{
  					//if($cntr==0)		echo "<br>".$key."=".(string) trim($value).".";	
  					
  					$res[''.$key.''][$cntr]= (string) trim($value);
  				}
     			$cntr++;
			}	
				
		}
		else
		{
			//$buffer=$pres['buffer'];
			$out.="<span style='color:#cc0000;'>Failure. :( </span><br>";	
		}
		
		$out.="<br><hr><br>".$pres['html']."<br><hr><br>";
		
		$res['num']=$cntr;
		
		$res['output']=$out;
		return $res;		
	}	
	
	
	//general fetch functions...usign the above API functions...
	function mrr_get_payroll_api_driver_settings($disp_driver_id)
	{
		$report="";
		if($disp_driver_id==0)		return $report;
		
		$first_name="";
		$last_name="";
		
		$sql="
			select drivers.payroll_first_name,
				drivers.payroll_last_name
			from drivers
			where drivers.id='".sql_friendly($disp_driver_id) ."'
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$first_name=trim($row['payroll_first_name']);
			$last_name=trim($row['payroll_last_name']);
		}	
		
		if($first_name=="" || $last_name=="")		return $report;
		
		$res=mrr_payroll_check_api_get_employee($first_name,$last_name);
		//echo "<br>".$res['output']."<br>";	
		
		$report.="<h1>Test Drive for ".$first_name." ".$last_name.":</h1>
			<h2>".$first_name." ".$last_name." Employee Info:</h2>
			<table cellpadding='0' cellspacing='0' border='0' width='100%'>
     		<tr style='font-weight:bold;'>
     			<td valign='top'>EmployeeID</td>
     			<td valign='top' align='right'>EmpRecordNumber </td>
     			<td valign='top'>EmployeeName</td>
     			<td valign='top'>LastName</td>
     			<td valign='top'>FirstName</td>
     			<td valign='top'>MiddleInit</td>     			
     			<td valign='top'>Address1</td>
     			<td valign='top'>Address2</td>
     			<td valign='top'>City</td>
     			<td valign='top'>State</td>
     			<td valign='top'>ZIP</td>
     			<td valign='top' align='right'>Inactive</td>
     			<td valign='top' align='right'>Employee</td>
     			<td valign='top' align='right'>Hourly</td>
     			<td valign='top' align='right'>DirectDeposit</td>     			
     			<td valign='top' align='right'>Status</td>
     			<td valign='top' align='right'>HireDate</td>
     			<td valign='top' align='right'>TerminationDate</td>
     			<td valign='top' align='right'>BirthDate</td>
     			<td valign='top' align='right'>RehireDate</td>
     			<td valign='top' align='right'>PhoneMobile</td>     			
     		</tr>
		";
		
		$cntr=$res['num'];
		for($i=0;$i < $cntr; $i++)
		{
			$report.="
				<tr style='background-color:#".($i%2==0 ? "eeeeee" : "dddddd").";'>
     				<td valign='top'>".$res['id_name'][$i]."</td>
     				<td valign='top' align='right'><b>".$res['id'][$i]."</b> </td>
     				<td valign='top'>".$res['full'][$i]."</td>
     				<td valign='top'>".$res['last'][$i]."</td>
     				<td valign='top'>".$res['first'][$i]."</td>
     				<td valign='top'>".$res['middle'][$i]."</td>
     				<td valign='top'>".$res['addr1'][$i]."</td>
     				<td valign='top'>".$res['addr2'][$i]."</td>
     				<td valign='top'>".$res['city'][$i]."</td>
     				<td valign='top'>".$res['state'][$i]."</td>
     				<td valign='top'>".$res['zip'][$i]."</td>
     				<td valign='top' align='right'>".$res['inactive'][$i]."</td>
     				<td valign='top' align='right'>".$res['active'][$i]."</td>
     				<td valign='top' align='right'>".$res['hourly'][$i]."</td>
     				<td valign='top' align='right'>".$res['direct_deposit'][$i]."</td>     				
     				<td valign='top' align='right'>".$res['status'][$i]."</td>
     				<td valign='top' align='right'>".$res['hired'][$i]."</td>
     				<td valign='top' align='right'>".$res['terminated'][$i]."</td>
     				<td valign='top' align='right'>".$res['birthdate'][$i]."</td>
     				<td valign='top' align='right'>".$res['rehire'][$i]."</td>
     				<td valign='top' align='right'>".$res['phone'][$i]."</td>
     			</tr>
			";
						
			$emp_id=$res['id'][$i];
			
			$epres=mrr_payroll_check_api_get_employee_info($emp_id);
						
			$report.="
				<tr style='background-color:#".($i%2==0 ? "eeeeee" : "dddddd").";'>
     				<td valign='top' colspan='21'>
     					<h2>Payroll Info</h2>
     					<table width='100%'>
                    		<tr style='font-weight:bold;'>
                    			<td valign='top'>AcctName</td>
                    			<td valign='top' align='right'>RecordNumber</td>
                    			<td valign='top' align='right'>EmployeeRecordNumber</td>
                    			<td valign='top' align='right'>RateNumber</td>
                    			<td valign='top' align='right'>UseDefault</td>
                    			<td valign='top' align='right'>AccountRecordNumber</td>
                    			<td valign='top' align='right'>PayRate</td>                    			
                    		</tr>
			";
			
			$cntr2=$epres['num'];
			for($j=0;$j < $cntr2; $j++)
			{				
				$report.="
					<tr style='background-color:#".($j%2==0 ? "cccccc" : "bbbbbb").";'>
               			<td valign='top'>".$epres['acct_name'][$j]."</td>
               			<td valign='top' align='right'>".$epres['id'][$j]."</td>
               			<td valign='top' align='right'>".$epres['emp_id'][$j]."</td>
               			<td valign='top' align='right'>".$epres['rate'][$j]."</td>
               			<td valign='top' align='right'>".$epres['def'][$j]."</td>
               			<td valign='top' align='right'>".$epres['acct_id'][$j]."</td>
               			<td valign='top' align='right'>$".number_format($epres['pay_rate'][$j], 2)."</td>               			
          			</tr>
				";
			}
			
			$report.="		</table>
     				</td>
     			</tr>
			";
			
			
			
			$epres=mrr_payroll_check_api_get_employee_fields($emp_id);
						
			$report.= "
				<tr style='background-color:#".($i%2==0 ? "eeeeee" : "dddddd").";'>
     				<td valign='top' colspan='21'>
     					<h2>Payroll Fields</h2>
     					<table width='100%'>
                    		<tr style='font-weight:bold;'>
                    			<td valign='top'>AcctName</td>
                    			<td valign='top' align='right'>RecordNumber</td>
                    			<td valign='top' align='right'>EmployeeRecordNumber</td>
                    			                    			
                    			<td valign='top' align='right'>FieldNumber</td>
                    			<td valign='top' align='right'>UseCustom</td>
                    			<td valign='top' align='right'>AccountRecordNumber</td>
                    			<td valign='top' align='right'>Calculateable</td>
                    			<td valign='top' align='right'>CalculationName</td>
                    			<td valign='top' align='right'>DefaultAmount</td>
                    			<td valign='top' align='right'>ContraAcctRecNumber</td>
                    		</tr>
			";
			
			$cntr2=$epres['num'];
			for($j=0;$j < $cntr2; $j++)
			{				
				$report.="
					<tr style='background-color:#".($j%2==0 ? "cccccc" : "bbbbbb").";'>
               			<td valign='top'>".$epres['acct_name'][$j]."</td>
               			<td valign='top' align='right'>".$epres['id'][$j]."</td>
               			<td valign='top' align='right'>".$epres['emp_id'][$j]."</td>
               			
               			<td valign='top' align='right'>".$epres['field_num'][$j]."</td>
               			<td valign='top' align='right'>".$epres['custom'][$j]."</td>
               			<td valign='top' align='right'>".$epres['acct_id'][$j]."</td>
               			<td valign='top' align='right'>".$epres['calc'][$j]."</td>
               			<td valign='top' align='right'>".$epres['calc_name'][$j]."</td>
               			<td valign='top' align='right'>$".number_format($epres['def_amnt'][$j], 2)."</td>
               			<td valign='top' align='right'>".$epres['contra'][$j]."</td>  
          			</tr>
				";				
			}
			
			$report.="		</table>
     				</td>
     			</tr>
			";
		}
		$report.="</table>";
		
		return $report;
	}
	function mrr_get_payroll_api_driver_check_display($check_id)
	{
		$report="";
		if($check_id==0)		return $report;
				
		$res=mrr_payroll_check_api_get_employee_check($check_id);
		
		$report.="				
			<h2>Check #".$check_id."</h2>
			<table width='100%'>
     		<tr style='font-weight:bold;'>
     			<td valign='top'>Description</td>
     			<td valign='top' align='right'>CustVendId</td>
     			<td valign='top' align='right'>TransactionDate</td>
     			<td valign='top' align='right'>PostOrder</td>     			
     			<td valign='top' align='right'>MainAmount</td>
     			<td valign='top' align='right'>GLAcntNumber </td>
     			<td valign='top'>TrxName</td>
     			<td valign='top'>TrxAddress1</td>
     			<td valign='top'>TrxAddress2</td>
     			<td valign='top'>TrxCity</td>
     			<td valign='top'>TrxState</td>
     			<td valign='top'>TrxZIP</td>
     			<td valign='top'>TrxCountry</td>
     			<td valign='top'>PaymentMethod</td>
     			<td valign='top'>ShipToName</td>
     			<td valign='top'>ShipToAddress1</td>
     			<td valign='top'>ShipToAddress2</td>
     			<td valign='top'>ShipToCity</td>
     			<td valign='top'>ShipToState</td>
     			<td valign='top'>ShipToZIP</td>
     			<td valign='top'>ShipToCountry</td>
     			<td valign='top' align='right'>EndOfPayPeriod</td>
     			<td valign='top' align='right'>WeeksWorked</td>
     			<td valign='top' align='right'>PRHours1</td>
     			<td valign='top' align='right'>PRHours2</td>
     			<td valign='top' align='right'>PRHours3</td>
     			<td valign='top' align='right'>PayMethod</td>
     			<td valign='top' align='right'>LastPostedAt</td>
     		</tr>
		";
		
		$cntr=$res['num'];
		for($j=0;$j < $cntr; $j++)
		{				
			$report.="
				<tr style='background-color:#".($j%2==0 ? "eeeeee" : "dddddd").";'>
          			<td valign='top'>".$res['Description'][$j]."</td>
          			<td valign='top' align='right'>".$res['CustVendId'][$j]."</td>
          			<td valign='top' align='right'>".$res['TransactionDate'][$j]."</td>
          			<td valign='top' align='right'>".$res['PostOrder'][$j]."</td>               			
          			<td valign='top' align='right'>$".number_format($res['MainAmount'][$j], 2)."</td>
          			<td valign='top' align='right'>".$res['GLAcntNumber'][$j]." </td>
          			<td valign='top'>".$res['TrxName'][$j]."</td>
          			<td valign='top'>".$res['TrxAddress1'][$j]."</td>
          			<td valign='top'>".$res['TrxAddress2'][$j]."</td>
          			<td valign='top'>".$res['TrxCity'][$j]."</td>
          			<td valign='top'>".$res['TrxState'][$j]."</td>
          			<td valign='top'>".$res['TrxZIP'][$j]."</td>
          			<td valign='top'>".$res['TrxCountry'][$j]."</td>
          			<td valign='top'>".$res['PaymentMethod'][$j]."</td>
          			<td valign='top'>".$res['ShipToName'][$j]."</td>
          			<td valign='top'>".$res['ShipToAddress1'][$j]."</td>
          			<td valign='top'>".$res['ShipToAddress2'][$j]."</td>
          			<td valign='top'>".$res['ShipToCity'][$j]."</td>
          			<td valign='top'>".$res['ShipToState'][$j]."</td>
          			<td valign='top'>".$res['ShipToZIP'][$j]."</td>
          			<td valign='top'>".$res['ShipToCountry'][$j]."</td>
          			<td valign='top' align='right'>".$res['EndOfPayPeriod'][$j]."</td>
          			<td valign='top' align='right'>".$res['WeeksWorked'][$j]."</td>
          			<td valign='top' align='right'>$".number_format($res['PRHours1'][$j], 2)."</td>
          			<td valign='top' align='right'>$".number_format($res['PRHours2'][$j], 2)."</td>
          			<td valign='top' align='right'>$".number_format($res['PRHours3'][$j], 2)."</td>
          			<td valign='top' align='right'>".$res['PayMethod'][$j]."</td>
          			<td valign='top' align='right'>".$res['LastPostedAt'][$j]."</td>
     			</tr>
			";	
		}		
		$report.="</table>";
		
		$res=mrr_payroll_check_api_get_employee_check_info($check_id);
		
		$report.="				
			<h2>Check #".$check_id." Line Item(s)</h2>
			<table width='100%'>
     		<tr style='font-weight:bold;'>
     			<td valign='top'>AcctDesc</td>
     			<td valign='top' align='right'>GLAcntNumber</td>
     			<td valign='top' align='right'>PostOrder</td>
     			<td valign='top' align='right'>RowNumber</td>
     			<td valign='top' align='right'>ItemRecordNumber</td>
     			<td valign='top' align='right'>JobRecordNumber</td>
     			<td valign='top' align='right'>PhaseRecordNumber</td>
     			<td valign='top' align='right'>CostRecordNumber</td>
     			<td valign='top' align='right'>LinkToAnotherTrx</td>
     			<td valign='top' align='right'>LinkToOtherTrxIndex</td>
     			<td valign='top' align='right'>Journal</td>
     			<td valign='top' align='right'>RowDate</td>
     			<td valign='top' align='right'>POSOIsClosed</td>
     			<td valign='top' align='right'>IncludeInGL</td>
     			<td valign='top' align='right'>IncludeInInvLedger</td>
     			<td valign='top' align='right'>RowType</td>
     			<td valign='top' align='right'>Amount</td>
     			<td valign='top' align='right'>StockingQuantity</td>
     			<td valign='top' align='right'>StockingUnitCost</td>
     			<td valign='top' align='right'>AmountReceived</td>
     			<td valign='top' align='right'>StockingQtyReceived</td>
     			<td valign='top' align='right'>DistNumber </td>
     			<td valign='top'>RowDescription</td>
     			<td valign='top' align='right'>CustomerRecordNumber</td>
     			<td valign='top' align='right'>VendorRecordNumber</td>
     			<td valign='top' align='right'>EmpRecordNumber</td>
     			<td valign='top' align='right'>UsedForReimbExp </td>
     			<td valign='top'>TaxAuthorityCode</td>
     			<td valign='top' align='right'>SalesTaxType</td>
     			<td valign='top' align='right'>PayrollFieldNumber </td>
     			<td valign='top'>InvNumForThisTrx</td>
     			<td valign='top' align='right'>DateCleared </td>
     			<td valign='top' align='right'>Quantity</td>
     			<td valign='top' align='right'>QtyReceived</td>
     			<td valign='top' align='right'>UnitCost</td>
     			<td valign='top' align='right'>POCreated</td>
     			<td valign='top' align='right'>HasSerialNumbers</td>
     			<td valign='top' align='right'>RetainagePercent</td>
     			<td valign='top' align='right'>LaborBurdenPercent</td>
     			<td valign='top' align='right'>JournalRowEx</td>
     			<td valign='top' align='right'>LinkJournalRowEx</td>
     			<td valign='top' align='right'>LastUpdateCounter</td>
     		</tr>
		";
		
		$cntr=$res['num'];
		for($j=0;$j < $cntr; $j++)
		{				
			$report.="
				<tr style='background-color:#".($j%2==0 ? "eeeeee" : "dddddd").";'>
          			<td valign='top'>".$res['MRRAcctDesc'][$j]."</td>
          			<td valign='top' align='right'>".$res['GLAcntNumber'][$j]."</td>
          			<td valign='top' align='right'>".$res['PostOrder'][$j]."</td>
          			<td valign='top' align='right'>".$res['RowNumber'][$j]."</td>
          			<td valign='top' align='right'>".$res['ItemRecordNumber'][$j]."</td>
          			<td valign='top' align='right'>".$res['JobRecordNumber'][$j]."</td>
          			<td valign='top' align='right'>".$res['PhaseRecordNumber'][$j]."</td>
          			<td valign='top' align='right'>".$res['CostRecordNumber'][$j]."</td>
          			<td valign='top' align='right'>".$res['LinkToAnotherTrx'][$j]."</td>
          			<td valign='top' align='right'>".$res['LinkToOtherTrxIndex'][$j]."</td>
          			<td valign='top' align='right'>".$res['Journal'][$j]."</td>
          			<td valign='top' align='right'>".$res['RowDate'][$j]."</td>
          			<td valign='top' align='right'>".$res['POSOIsClosed'][$j]."</td>
          			<td valign='top' align='right'>".$res['IncludeInGL'][$j]."</td>
          			<td valign='top' align='right'>".$res['IncludeInInvLedger'][$j]."</td>
          			<td valign='top' align='right'>".$res['RowType'][$j]."</td>
          			<td valign='top' align='right'>$".number_format($res['Amount'][$j], 2)."</td>
          			<td valign='top' align='right'>$".number_format($res['StockingQuantity'][$j], 2)."</td>
          			<td valign='top' align='right'>$".number_format($res['StockingUnitCost'][$j], 2)."</td>
          			<td valign='top' align='right'>$".number_format($res['AmountReceived'][$j], 2)."</td>
          			<td valign='top' align='right'>$".number_format($res['StockingQtyReceived'][$j], 2)."</td>
          			<td valign='top' align='right'>".$res['DistNumber'][$j]." </td>
          			<td valign='top'>".$res['RowDescription'][$j]."</td>
          			<td valign='top' align='right'>".$res['CustomerRecordNumber'][$j]."</td>
          			<td valign='top' align='right'>".$res['VendorRecordNumber'][$j]."</td>
          			<td valign='top' align='right'>".$res['EmpRecordNumber'][$j]."</td>
          			<td valign='top' align='right'>".$res['UsedForReimbExp'][$j]." </td>
          			<td valign='top'>".$res['TaxAuthorityCode'][$j]."</td>
          			<td valign='top' align='right'>".$res['SalesTaxType'][$j]."</td>
          			<td valign='top' align='right'>".$res['PayrollFieldNumber'][$j]." </td>
          			<td valign='top'>".$res['InvNumForThisTrx'][$j]."</td>
          			<td valign='top' align='right'>".$res['DateCleared'][$j]."</td>
          			<td valign='top' align='right'>$".number_format($res['Quantity'][$j], 2)."</td>
          			<td valign='top' align='right'>$".number_format($res['QtyReceived'][$j], 2)."</td>
          			<td valign='top' align='right'>$".number_format($res['UnitCost'][$j], 2)."</td>
          			<td valign='top' align='right'>".$res['POCreated'][$j]."</td>
          			<td valign='top' align='right'>".$res['HasSerialNumbers'][$j]."</td>
          			<td valign='top' align='right'>$".number_format($res['RetainagePercent'][$j], 2)."</td>
          			<td valign='top' align='right'>$".number_format($res['LaborBurdenPercent'][$j], 2)."</td>
          			<td valign='top' align='right'>".$res['JournalRowEx'][$j]."</td>
          			<td valign='top' align='right'>".$res['LinkJournalRowEx'][$j]."</td>
          			<td valign='top' align='right'>".$res['LastUpdateCounter'][$j]."</td>
     			</tr>
			";
		}		
		$report.="</table>";
		return $report;
	}
	function mrr_grab_driver_payroll_item($driver_id,$disp_date,$mode=0)
	{
		$amnt=0.00;
		$sql="
			select *
			from driver_payroll_changes
			where driver_id='".sql_friendly($driver_id) ."'
				and deleted=0 
				and auto_schedule=0
				and linedate<='".date("Y-m-d",strtotime($disp_date))." 00:00:00'
			order by linedate desc
			limit 1
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			if($mode==1)	$amnt=$row['single_hour_pay'];
			if($mode==2)	$amnt=$row['single_mile_pay'];
			if($mode==3)	$amnt=$row['single_hour_pay_charged'];
			if($mode==4)	$amnt=$row['single_mile_pay_charged'];
			
			if($mode==5)	$amnt=$row['team_hour_pay'];
			if($mode==6)	$amnt=$row['team_mile_pay'];
			if($mode==7)	$amnt=$row['team_hour_pay_charged'];
			if($mode==8)	$amnt=$row['team_mile_pay_charged'];
		}
		return $amnt;
	}
?>