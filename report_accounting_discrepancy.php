<? include('header.php') ?>
<? 
	
	if(!isset($_POST['hide_price_alerts']))			$_POST['hide_price_alerts'] = 0;
	if(!isset($_POST['hide_no_trucking_found']))		$_POST['hide_no_trucking_found'] = 0;
	if(!isset($_POST['pull_from_sicap']))			$_POST['pull_from_sicap']=0;
	if(!isset($_POST['hide_matching']))			$_POST['hide_matching']=0;
	
	if(!isset($_POST['import_box'])) {
		$_POST['hide_matching'] = 1; // set our checkbox to default checked
		$_POST['pull_from_sicap'] = 1;
		$_POST['hide_price_alerts'] = 0;
	}
	
	if(isset($_GET['date_from']) && isset($_GET['date_to']))	
	{
		$_POST['date_from'] = str_replace("_","/",$_GET['date_from']);
		$_POST['date_to'] = str_replace("_","/",$_GET['date_to']);	
	}	
	
	
	if(!isset($_POST['import_box'])) $_POST['import_box'] = "";		//invoices
	if(!isset($_POST['import_box2'])) $_POST['import_box2'] = "";	//bills...for credits
	if(!isset($_POST['import_box3'])) $_POST['import_box3'] = "";	//checks for deposit
	
	if(!isset($_POST['date_from'])) $_POST['date_from'] = "";
	if(!isset($_POST['date_to'])) $_POST['date_to'] = "";	

	if($_POST['pull_from_sicap'] > 0 && $_POST['date_from'] != '' && $_POST['date_to'] != '') {
		$rslt = sicap_get_invoices_by_date($_POST['date_from'], $_POST['date_to']);
		//var_dump($rslt);
		
		$invoice_feed = "";
		$bill_feed="";
		$check_feed="";
		
		foreach($rslt->InvoiceEntry as $invoice_entry) {
			$invoice_feed .= $invoice_entry->InvoiceID.chr(9).$invoice_entry->Amount.chr(9).$invoice_entry->InvoiceDate.chr(9).$invoice_entry->DiscAmount.chr(13);
		}
		
		foreach($rslt->BillEntry as $bill_entry) {
			$bill_feed .= $bill_entry->BillID.chr(9).$bill_entry->Amount.chr(9).$bill_entry->BillDate.chr(9).$bill_entry->Notes.chr(13);
		}
		
		foreach($rslt->CheckEntry as $check_entry) {
						
			$myamnt=$check_entry->Amount;
			if((int) $check_entry->JournalID > 0)
			{
				$myamnt=$check_entry->JournalAmnt;
			} 
			
			$check_feed .= $check_entry->CheckID.chr(9).$myamnt.chr(9).$check_entry->CheckDate.chr(9).$check_entry->CheckNumber.chr(9).$check_entry->Notes.chr(13);
		}
				
		$_POST['import_box'] = $invoice_feed;
		$_POST['import_box2'] = $bill_feed;
		$_POST['import_box3'] = $check_feed;
	}
?>

<div style='float:left'>
<form action='' method='post'>
<table style='text-align:left;margin-left:15px'>
<tr>
	<td colspan='6'><h3>Accounting Discrepancy Report</h3></td>
</tr>
<tr>
	<td>Date From:</td>
	<td><input name='date_from' value='<?=$_POST['date_from']?>' class='datepicker'></td>
	<td>Date To:</td>
	<td><input name='date_to' value='<?=$_POST['date_to']?>' class='datepicker'></td>
	<td colspan='2'>&nbsp;</td>
</tr>
<?
if($defaultsarray['sicap_integration'] == 1) {
	echo "
		<tr>
			<td colspan='6'><label><input type='checkbox' value='1' name='pull_from_sicap' ".($_POST['pull_from_sicap'] > 0 ? "checked" : "")."> Pull from Accounting System</label></td>
		</tr>
	";
}
?>
<tr>
	<td colspan='6'><label><input type='checkbox' value='1' name='hide_price_alerts' <?=($_POST['hide_price_alerts'] > 0 ? "checked" : "")?>> Hide Price Alerts</label></td>
</tr>
<tr>
	<td colspan='6'><label><input type='checkbox' value='1' name='hide_no_trucking_found' <?=($_POST['hide_no_trucking_found'] > 0 ? "checked" : "")?>> Hide "no trucking entry found"</label></td>
</tr>
<tr>
	<td colspan='6'><label><input type='checkbox' value='1' name='hide_matching' <?=($_POST['hide_matching'] > 0 ? "checked" : "")?>> Check checkbox to only show discrepancies</label></td>
</tr>
<tr>
	<td colspan='2'>
		<div style='border:1px black solid;background-color:#eeeeee;padding:15px;margin:10px 0;width:300px'>
			<h3 style='margin-top:0;padding-top:0'>Please copy data from Excel</h3>
			<ul>
				<li>The only three columns copied should be the <b>invoice number</b>, <b>invoice amount</b> and <b>invoice date</b>. </li>
				<li>The invoice number should be the first column. </li>
				<li>The column headers should <b>not</b> be included.</li>
			</ul>
		</div>
	</td>
	<td colspan='2'>
		<div style='border:1px black solid;background-color:#eeeeee;padding:15px;margin:10px 0;width:300px'>
			<h3 style='margin-top:0;padding-top:0'>Please copy data from Excel</h3>
			<ul>
				<li>The only four columns copied should be the <b>bill id</b>, <b>bill amount</b>, <b>bill date</b>, and <b>bill notes</b>. </li>
				<li>The bill id should be the first column. </li>
				<li>The column headers should <b>not</b> be included.</li>
			</ul>
		</div>
	</td>
	<td colspan='2'>
		<div style='border:1px black solid;background-color:#eeeeee;padding:15px;margin:10px 0;width:300px'>
			<h3 style='margin-top:0;padding-top:0'>Please copy data from Excel</h3>
			<ul>
				<li>The only five columns copied should be the <b>check id</b>, <b>check amount</b>, <b>check date</b>, <b>check number</b>, and <b>check memo</b>. </li>
				<li>The check id should be the first column. </li>
				<li>The column headers should <b>not</b> be included.</li>
			</ul>
		</div>
	</td>
</tr>
<tr>
	<td colspan='2'>
		Invoice Excel Data <?= show_help('report_accounting_discrepancy.php','Invoice Excel Data') ?><br>
		<textarea name='import_box' style='width:400px;height:200px;'><?=$_POST['import_box']?></textarea>
	</td>
	<td colspan='2'>
		Bill Excel Data <?= show_help('report_accounting_discrepancy.php','Bill Excel Data') ?><br>
		<textarea name='import_box2' style='width:400px;height:200px;'><?=$_POST['import_box2']?></textarea>
	</td>
	<td colspan='3'>
		Check Excel Data <?= show_help('report_accounting_discrepancy.php','Check Excel Data') ?><br>
		<textarea name='import_box3' style='width:400px;height:200px;'><?=$_POST['import_box3']?></textarea>
	</td>
</tr>
<tr>
	<td colspan='3'></td>
	<td><input type='submit' value='Submit'></td>
	<td colspan='2'></td>
</tr>
</table>
</form>
</float>

<br><br><br>


<table style='width:1000px'>
<tr>
	<td align='left'><b>Invoice Number</b></td>
	<td align='right'><b>Accounting Amount</b></td>
	<td align='right'><b>Trucking Amount</b></td>
	<td align='right'><b>Difference</b></td>
	<td></td>
	<td></td>
</tr>
<?
	$mrr_rep="";
	$mrr_rep_cnt=0;
	/*
	//special Income/Discount Accounts..............................$misc_income & $discounts
	$misc_income=0;	
	$results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48915' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48800' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48925' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48900' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '45000' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '66510' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     
	$discounts=0;
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '46000' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$discounts+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '49500' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$discounts+=(float)$tmp;
     }
     */
	
	//get net profit values............................................................................................
	$net_profit=0;
     $net_profit_inc=0;
     $net_profit_cog=0;
     $net_profit_adm=0;
     $net_profit_cos=0;
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '0' ,'99999');	//income	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$net_profit_inc+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(98,$_POST['date_from'],$_POST['date_to'], '0' ,'99999');	//COGS
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$net_profit_cog-=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(10,$_POST['date_from'],$_POST['date_to'], '0' ,'99999');	//Admin Exp
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$net_profit_adm-=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(1,$_POST['date_from'],$_POST['date_to'], '0' ,'99999');		//COS
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$net_profit_cos-=(float)$tmp;
     }
     $net_profit=$net_profit_inc+$net_profit_cog+$net_profit_adm+$net_profit_cos + $misc_income + $discounts;
     //.................................................................................................................
	

	
	$acct_bill_total = 0;
	$acct_check_total = 0;
	$truck_bill_total = 0;
	$truck_check_total = 0;
	
	$trucking_total = 0;
	$peachtree_total = 0;
	$invoice_array = array();
	
	//get any credits, etc...
	$added_credits="";
	$added_truck_tot=0;
	$added_acct_tot=0;
	$date_alert_total = 0; // total of loads with invoice numbers that are outside our date range
	
	$bills_total = 0;
	$journal_total = 0;
	
	
	$load_id_array = array();
	//get credits from bills first
	if($_POST['import_box2'] != '') 
	{
		$line_array = explode(chr(13), $_POST['import_box2']);			
		
		foreach($line_array as $line) {
			$line_part_array = explode(chr(9), $line);
			
			if(count($line_part_array) == 2 || count($line_part_array) == 3 || count($line_part_array) == 4) {
								
				$invoice_number = trim($line_part_array[0]);
				$peachtree_amount = money_strip(trim($line_part_array[1]));
				$trucking_amount = money_strip(0);
				$notes=trim($line_part_array[3]);
				
				$bills_total+=$peachtree_amount;
				
				$added_credits.="
					<tr>
						<td align='left' nowrap><b>Bill ".$invoice_number."</b></td>
						<td align='right'>".money_format('',$peachtree_amount)." </td>
						<td align='right'>".money_format('',$trucking_amount)."</td>
						<td></td>
						<td align='right'><b>Credit</b> ".show_help('report_accounting_discrepancy.php','Credit')."</td>
						<td align='left'>".$notes."</td>
					</tr>
				";
				$acct_bill_total += $peachtree_amount;
				$truck_bill_total += $trucking_amount;				
				
				$added_truck_tot+=$trucking_amount;
				$added_acct_tot+=$peachtree_amount;
			}
		}		
	}
	
	
	
	//now get other check/deposit refunds
	if($_POST['import_box3'] != '') 
	{
		$line_array = explode(chr(13), $_POST['import_box3']);			
		
		foreach($line_array as $line) {
			$line_part_array = explode(chr(9), $line);
			
			if(count($line_part_array) == 2 || count($line_part_array) == 3 || count($line_part_array) == 4 || count($line_part_array) == 5) {
				
				$invoice_number = trim($line_part_array[0]);
				$peachtree_amount = money_strip(trim($line_part_array[1]));
				$trucking_amount = money_strip(0);
				$chknum=trim($line_part_array[3]);
				$notes=trim($line_part_array[4]);
				
				$peachtree_amount=($peachtree_amount * -1);
				$trucking_amount=($trucking_amount * -1);
				
				if($chknum=="JRNL")		$journal_total+=$peachtree_amount;
								
				$added_credits.="
					<tr>
						<td align='left' nowrap><b>Check ".$invoice_number." (".$chknum.")</b></td>
						<td align='right'>".money_format('',$peachtree_amount)." </td>
						<td align='right'>".money_format('',$trucking_amount)."</td>
						<td></td>
						<td align='right'><b>Deposit</b> ".show_help('report_accounting_discrepancy.php','Deposit')."</td>
						<td align='left'>".$notes."</td>
					</tr>
				";
				
				$acct_check_total += $peachtree_amount;
				$truck_check_total += $trucking_amount;
				
				$added_truck_tot+=$trucking_amount;
				$added_acct_tot+=$peachtree_amount;
			}
		}		
	}
	$peachtree_amount = 0;
	$trucking_amount = 0;
	$difference_total = 0;
	$discount_total = 0;
	$discount_total2 = 0;
	$discount_total3 = 0;
	
	
	$no_load_total = 0;
	
	$mrr_cntr=0;
	
	$mrr_rep.="<br><br><b>CALCULATED DISCOUNT BREAKDOWN:</b><br>";
	
	if($_POST['import_box'] != '') 
	{
		$line_array = explode(chr(13), $_POST['import_box']);
		
		$dbnamer=mrr_find_acct_database_name();		//load_handler.
		
		foreach($line_array as $line) {
			$line_part_array = explode(chr(9), $line);
			
			if(count($line_part_array) == 2 || count($line_part_array) == 3 || count($line_part_array) == 4) {
				
				$invoice_number = trim($line_part_array[0]);
				

				//if(in_array("'$invoice_number'", $invoice_array)) die("duplicate found");
				$invoice_array[] = "'$invoice_number'";
				
				$sql = "
					select ifnull(sum(load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount),0) as total_bill_customer,
						load_handler.linedate_invoiced,
						load_handler.id,
						
						(select ifnull(invoice.linedate_ship,0) from ".$dbnamer."invoice where invoice.id='".(int) $invoice_number."' and invoice.id>0) as mrr_inv_date,
						
						(select count(*) from load_handler lh where lh.id <> load_handler.id and lh.invoice_number = load_handler.invoice_number) as outside_date_range_count,
						(select sum(miles) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) as my_miles,
						(select sum(miles_deadhead) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) as my_miles_dh,
						(select sum(loaded_miles_hourly) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) as my_miles_hr,
						(select sum(miles_deadhead_hourly) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0) as my_miles_hr_dh,
						load_handler.invoice_number
					
					from load_handler
					where load_handler.invoice_number = '".sql_friendly($invoice_number)."'
						and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
						and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
						and load_handler.deleted = 0
				";
				$data = simple_query($sql);				
				$row = mysqli_fetch_array($data);
				
				$peachtree_amount = money_strip(trim($line_part_array[1]));
				$trucking_amount = money_strip($row['total_bill_customer']);
				
				$peachtree_disc_amnt = money_strip(trim($line_part_array[3]));
				
				$peachtree_disc_amnt=$peachtree_disc_amnt * -1;
				
				
				$peachtree_total += $peachtree_amount;
				$trucking_total += $trucking_amount;
				
				$percent_difference = $peachtree_amount / $trucking_amount;
				
				// get the 1% and 2% discounts
				$trucking_discount_one = number_format($trucking_amount * .99, 2, '.', '');
				$trucking_discount_two = number_format($trucking_amount * .98, 2, '.', '');
				//$account
				
				$discount_total2=0;

				if($row['id'] != '') { 
					// trucking load found to match invoice
					if(in_array($row['id'], $load_id_array)) {
						// we've already processed a load with this invoice ID, show an alert
						echo "
							Duplicate load: Load ID: $row[id]<br>
						";
					} else {
						$load_id_array[] = $row['id'];
					}
					
					
					if($trucking_amount != $peachtree_amount) {
						$discount_total += $trucking_amount - $peachtree_amount;
						$discount_total2=$trucking_amount - $peachtree_amount;
						
						$mrr_rep_cnt++;
						$mrr_rep.="<br>".$mrr_rep_cnt.". Discount Item (Load# <a href='manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a> 
								$".number_format($trucking_amount,2)." Trucking - 
								$".number_format($peachtree_amount,2)." Acct Invoice #
								<a href='https://trucking.conardlogistics.com/accounting/invoice.php?invoice_id=".$row['invoice_number']."' target='_blank'>".$row['invoice_number']."</a> 
								<span style='color:orange;'><b>[".(strtotime($row['mrr_inv_date']) > 0 ? "".date("m/d/Y",strtotime($row['mrr_inv_date']))."" : "NONE" )."]</b></span>
								) 
							= ".($discount_total2 < 0 ? "<span class='alert'>" : "")."<b>$".number_format(($discount_total2),2)."</b>".($discount_total2 < 0 ? "</span>" : "")." 
							Running Total <b>$".number_format($discount_total,2)."</b>.
							--- <span style='color:#00CC00;'>".$row['my_miles']." Mi + ".$row['my_miles_dh']." MiDH = <b>".($row['my_miles'] + $row['my_miles_dh'])." Miles</b>.</span>
							--- <span style='color:purple;'>".$row['my_miles_hr']." Hr + ".$row['my_miles_hr_dh']." HrDH = <b>".($row['my_miles_hr'] + $row['my_miles_hr_dh'])." Miles Hourly</b>.</span>
							--- <span style='color:brown;'><b>All Miles ".($row['my_miles'] + $row['my_miles_hr'] + $row['my_miles_dh'] + $row['my_miles_hr_dh'])."</b>.</span>
							".($row['my_miles'] > 0 && $row['my_miles_hr'] > 0 ? "<span class='alert' title='Mileage/Fuel Surcharge...Both Miles and Hourly Miles for this load.'><i> = = = = = = Possible Issue</i></span>": "")."
						";
									
						if($peachtree_disc_amnt > 0 && number_format($peachtree_disc_amnt,2)!=number_format($discount_total2,2))
						{
							$mrr_rep.="   <span class='alert'><i>DISC ACCT is <b>$".number_format($peachtree_disc_amnt,2)."</b></i></span>";	
						}						
					}
				}
				
				if((($trucking_amount == $peachtree_amount) || ($peachtree_disc_amnt > 0 && number_format($peachtree_disc_amnt,2)==number_format($discount_total2,2))) && $_POST['hide_matching'] > 0)
				{
					//skip for now	
					
					// || ($peachtree_disc_amnt < 0 && $peachtree_disc_amnt!=$discount_total2)
					
					// || (number_format($percent_difference, 3) <= .99 && number_format($percent_difference, 3)
					
					// || (number_format($percent_difference, 3) == .99) || (number_format($percent_difference, 3) == .98) 			
				} 
				else 
				{					
					if($_POST['hide_price_alerts'] > 0 && $row['id'] != '' && $trucking_amount != $peachtree_amount)
					{
						
					} 
					else if($_POST['hide_no_trucking_found'] > 0 && $row['id'] == '') 
					{
						
					}
					else
					{
						$mrr_cntr++;
     					    					
     					$difference = $peachtree_amount - $trucking_amount;
     					$difference_total += $difference;
     					$discount_total3 += $difference;
     					
     					/*
     					$mrr_rep.="<br><b>Discount2 Item</b> ($".number_format($peachtree_amount,2)." Acct - $".number_format($trucking_amount,2)." Trucking ) 
									= <b>$".number_format(( $peachtree_amount - $trucking_amount),2)."</b> 
									<span class='alert'>Running Total $".number_format($discount_total3,2)."</span>.";
     					*/
     					
     					
     					//$misc_inc_total = 0;
						if($row['id'] == '')	$no_load_total += $difference;
     					
     					     					
     					echo "
     						<tr>
     							<td valign='top' align='left' nowrap>".$mrr_cntr." 
     								<a href='report_invoiced.php?invoice_number=$line_part_array[0]' target='view_invoice_$line_part_array[0]'>inv: $line_part_array[0] (load: $row[id])</a>
     								".($defaultsarray['sicap_integration'] == 1 ? "<a href='accounting/invoice.php?invoice_id=$line_part_array[0]' target='view_invoice_".$line_part_array[0]."'><img src='images/edit_small.png' style='border:0'></a>" : "" )."
     							</td>
     							<td valign='top' align='right'>".money_format('',$peachtree_amount)." </td>
     							<td valign='top' align='right'>".money_format('',$trucking_amount)."</td>
     							<td valign='top' align='right'>".money_format('',$difference)."</td>
     							<td valign='top' align='right' nowrap>
     								".($row['id'] == '' ? "&nbsp;&nbsp;&nbsp; <a href='https://trucking.conardtransportation.com/accounting/invoice.php?invoice_id=".$line_part_array[0]."' target='_blank' title='view invoice'><span class='alert'>no trucking entry found</span></a>" : "")."
     								".($row['id'] != '' && $trucking_amount != $peachtree_amount ? "<span class='alert'>&nbsp;&nbsp;&nbsp; price alert</span>" : "")."
     					";
     						if($peachtree_disc_amnt > 0 && number_format($peachtree_disc_amnt,2)!=number_format($discount_total2,2))
							{
								echo "$".number_format((abs($peachtree_disc_amnt) - abs($discount_total2)),2)."<br><span class='alert'><i>DISC AMNT is <b>$".number_format($peachtree_disc_amnt,2)."</b></i></span>";	
							}
     						
     						
     						if($row['outside_date_range_count'] > 0) 
     						{
     							// find which loads are outside the date range
     							$sql = "
									select *,
										(actual_bill_customer + flat_fuel_rate_amount) as actual_bill_customer
									
									from load_handler
									where invoice_number = '".sql_friendly($invoice_number)."'
										and 
											(
											linedate_pickup_eta < '".date("Y-m-d", strtotime($_POST['date_from']))."'
											or
											linedate_pickup_eta >= '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
											)
										and deleted = 0
     							";
     							$data_outside_date = simple_query($sql);
     							echo " - Date Alert";
     							while($row_outside = mysqli_fetch_array($data_outside_date)) {
     								echo "<br><a href='manage_load.php?load_id=$row_outside[id]' target='_blank'>$row_outside[id]</a> | ".date("m/d/Y", strtotime($row_outside['linedate_pickup_eta']))." | $".money_format('',$row_outside['actual_bill_customer'])."";
     								$date_alert_total += $row_outside['actual_bill_customer'];
     							}
     						}
     					echo "
     							</td>
     							<td valign='top'></td>
     						</tr>
     					";
					}
				}
				//echo $line_part_array[0]."<br>";
			}
		}
		
		// see if there are any invoices in our system that do not match up in accounting.
		
		$sql = "
			select load_handler.*
			
			from load_handler
			where load_handler.invoice_number not in (".implode(",",$invoice_array).")
				and load_handler.deleted = 0
				and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
			order by load_handler.linedate_pickup_eta asc,load_handler.id asc
		";
		//echo $sql;
		$data_missing = simple_query($sql);
		
		if(mysqli_num_rows($data_missing)) {
			while($row_missing = mysqli_fetch_array($data_missing)) {
				$trucking_total += $row_missing['actual_bill_customer'];
				//report_invoiced.php?invoice_number=$row_missing[invoice_number]
				
				echo "
					<tr>
						<td align='left' nowrap>
							<a href='manage_load.php?load_id=$row_missing[id]' target='view_invoice_$row_missing[id]'><span class='alert'>inv: $row_missing[invoice_number] (load: $row_missing[id])</span></a>
							".($defaultsarray['sicap_integration'] == 1 ? "<a href='accounting/invoice.php?invoice_id=$row_missing[invoice_number]' target='view_invoice_".$row_missing['invoice_number']."'><img src='images/edit_small.png' style='border:0'></a>" : "" )."
						</td>
						<td align='right'>0</td>
						<td align='right'>".money_format('', $row_missing['actual_bill_customer'])."</td>
						<td align='right'><span class='alert'>missing in accounting system</span></td>
						<td>".date("m/d/Y",strtotime($row_missing['linedate_pickup_eta']))." -- Inv <u>".trim($row_missing['invoice_number'])."</u> SICAP Inv <u>".trim($row_missing['sicap_invoice_number'])."</u></td>
					</tr>
				";
			}
		}
		
		echo $added_credits;
		$trucking_total +=(-1 * $added_truck_tot);
		$peachtree_total+=(-1 * $added_acct_tot);		
		
		$acct_credits_total = $acct_bill_total + $acct_check_total;
		$truck_credits_total= $truck_bill_total + $truck_check_total;
		$credits_total= $acct_credits_total - $truck_credits_total;
		
		if($date_alert_total != 0) {
			echo "
				<tr>
					<td colspan='5'><hr></td>
				</tr>
				<tr>
					<td>Loads Outside Date Range<br>(with an assigned invoice number)</td>
					<td align='right'>$".money_format('',$date_alert_total)."</td>
					
					<td></td>
				</tr>
			";
		}
		
		echo "
			<tr>
				<td colspan='5'><hr></td>
			</tr>
			<tr>
				<td></td>
				<td align='right'>Accounting System:</td>
				<td align='right'>Trucking:</td>
				<td align='right'>True Difference:</td>
				<td></td>
			</tr>
			<tr>
				<td>Invoices</td>
				<td align='right'>$".money_format('', $peachtree_total)."</td>
				<td align='right'>$".money_format('', $trucking_total)."</td>
				<td align='right'>$".money_format('', $difference_total)."</td>
				<td>(total 'difference' shown above)</td>
			</tr>
			<tr>
				<td colspan='5'><hr></td>
			</tr>
			<tr>
				<td>Totals (No Credits) ".show_help('report_accounting_discrepancy.php','Total No Credits')."</td>
				<td align='right'>$".money_format('', $peachtree_total)."</td>
				<td align='right'> - $".money_format('', $trucking_total)."</td>
				<td align='right'> = $".money_format('', $peachtree_total - $trucking_total)."</td>				
				<td>(difference between accounting and trucking)</td>
			</tr>
			<tr>
				<td colspan='5'><hr></td>
			</tr>
			<tr>
				<td>Credits</td>
				<td align='right'>$".money_format('', $acct_credits_total)."</td>
				<td align='right'>$".money_format('', $truck_credits_total)."</td>
				<td align='right' nowrap>- $".money_format('', $credits_total)."</td>
				<td></td>
			</tr>
			<tr>
				<td>Totals (With Credits) ".show_help('report_accounting_discrepancy.php','Total With Credits')."</td>
				<td align='right'>$".money_format('', $peachtree_total + $acct_credits_total)."</td>
				<td align='right'> - $".money_format('', $trucking_total + $truck_credits_total)."</td>
				<td align='right'> = $".money_format('', ($peachtree_total + $acct_credits_total) - ($trucking_total + $truck_credits_total))."</td>				
				<td></td>
			</tr>
			<tr>
				<td colspan='5'><hr></td>
			</tr>			
			<tr>
				<td></td>
				<td align='right'>Income Statement<br>$".money_format('', $net_profit_inc)."</td>
				<td align='right'></td>
				<td align='right'></td>				
				<td></td>
			</tr>
			<tr>
				<td colspan='5'><hr></td>
			</tr>	
			<tr>
				<td>Discounts (credits/price alerts/etc...)</td>
				<td align='right'>$".money_format('', $discount_total)."</td>
			</tr>
			
		";
	}
	
?>
</table>
<br><br><br>
<?
$acct_misc_income=0;
$acct_misc_income1=0;
$acct_misc_income2=0;
$acct_misc_income3=0;
$acct_misc_income4=0;
$acct_misc_income5=0;
$results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '45000' ,'');	
foreach($results as $key => $value )
{
    	$prt=trim($key);		$tmp=trim($value);
    	if($prt=="Comparison")	$acct_misc_income1+=(float)$tmp;
}
$results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48800' ,'');	
foreach($results as $key => $value )
{
    	$prt=trim($key);		$tmp=trim($value);
    	if($prt=="Comparison")	$acct_misc_income2+=(float)$tmp;
}
$results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48900' ,'');	
foreach($results as $key => $value )
{
    	$prt=trim($key);		$tmp=trim($value);
    	if($prt=="Comparison")	$acct_misc_income3+=(float)$tmp;
}
$results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48915' ,'');	
foreach($results as $key => $value )
{
    	$prt=trim($key);		$tmp=trim($value);
    	if($prt=="Comparison")	$acct_misc_income4+=(float)$tmp;
}
$results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48925' ,'');	
foreach($results as $key => $value )
{
    	$prt=trim($key);		$tmp=trim($value);
    	if($prt=="Comparison")	$acct_misc_income5+=(float)$tmp;
}

$acct_misc_income=$acct_misc_income1;	//($acct_misc_income1 + $acct_misc_income2 + $acct_misc_income3 + $acct_misc_income4 + $acct_misc_income5);
$acct_misc_incomeX=($acct_misc_income1 + $acct_misc_income2 + $acct_misc_income3 + $acct_misc_income4 + $acct_misc_income5);

$acct_discounts1=0;
$acct_discounts2=0;
$results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '46000' ,'');	
foreach($results as $key => $value )
{
	$prt=trim($key);		$tmp=trim($value);
	if($prt=="Comparison")	$acct_discounts1+=(float)$tmp;
}
$results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '49500' ,'');	
foreach($results as $key => $value )
{
	$prt=trim($key);		$tmp=trim($value);
	if($prt=="Comparison")	$acct_discounts2+=(float)$tmp;
}


//$mrr_rep.="<br><hr><br>$".money_format('', $discount_total)." - $".money_format('', $discount_total3)." = $".money_format('', ($discount_total-$discount_total3))."";


if($acct_misc_income1 > 0)
{
	$acct_misc_incomeZ=$acct_misc_incomeX + ($acct_misc_income - $no_load_total + $journal_total + $bills_total - $acct_misc_income1);
}
else
{
	$acct_misc_incomeZ=$acct_misc_incomeX + ($acct_misc_income - $no_load_total + $journal_total + $bills_total);	
}

//$mrr_rep="";

//Timesheet entries...usign the invoice date first, then figuring out whihc ones are probasbly not included in the regular comparison/sales report sections...
echo "	
		<table celppading='0' cellspacing='0' width='1200' border='0'>
		<tr>
			<td valign='top'>TimeSheetID</td>
			<td valign='top'>Customer</td>
			<td valign='top' align='right'>Added</td>
			<td valign='top' align='right'>Invoiced</td>
			<td valign='top' align='right'>InvoiceDate</td>
			<td valign='top' align='right'>Starting</td>
			<td valign='top' align='right'>Ending</td>			
			<td valign='top' align='right'>Location</td>
			<td valign='top' align='right'>ShuttleRuns</td>
		</tr>
	";	

$cntr=0;
$invoiced=0;
$not_invoiced=0;
$in_gtot=0;
$out_gtot=0;
$run_gtot=0;
$runs=0;
	//and (
	//	(timesheets.linedate_invoiced >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'and timesheets.linedate_invoiced <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59')
	//	or
	//	(timesheets.linedate_start <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59' and timesheets.linedate_end >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00')
	//)
$sql2 = "
	select timesheets.*,
		(select name_company from customers where customers.id=timesheets.customer_id) as cust_name
	from timesheets
	where timesheets.deleted=0
		and timesheets.linedate_start <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59' 
		and timesheets.linedate_end >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
		
     	and timesheets.linedate_start!='00000-00-00 00:00:00'
     order by timesheets.linedate_start asc,
     	timesheets.invoice_id asc,
     	timesheets.id asc
";	
//echo "<pre>$sql2</pre>";
$data2 = simple_query($sql2);
while($row2 = mysqli_fetch_array($data2)) 
{
	$runs+=$row2['shuttle_runs'];
	if($row2['invoice_id'] > 0)		$invoiced++;		else		$not_invoiced++;
	
	$local="";
	if($row2['location_id']==1)  $local="Nashville";
	if($row2['location_id']==2)  $local="Lebanon";
	
	echo "
		<tr class='".($cntr%2==0 ? "even" : "odd")."'>
			<td valign='top'>".$row2['id']."</td>
			<td valign='top'><a href='admin_customers.php?eid=".$row2['customer_id']."' target='_blank'>".$row2['cust_name']."</a></td>
			<td valign='top' align='right'>".date("Y-m-d", strtotime($row2['linedate_added']))."</td>
			<td valign='top' align='right'><a href='https://trucking.conardtransportation.com/accounting/invoice.php?invoice_id=".$row2['invoice_id']."' target='_blank'>".$row2['invoice_id']."</a></td>
			<td valign='top' align='right'>".date("Y-m-d", strtotime($row2['linedate_invoiced']))."</td>
			<td valign='top' align='right'>".date("Y-m-d", strtotime($row2['linedate_start']))."</td>
			<td valign='top' align='right'>".date("Y-m-d", strtotime($row2['linedate_end']))."</td>			
			<td valign='top' align='right'>".$local."</td>
			<td valign='top' align='right'>".$row2['shuttle_runs']."</td>
		</tr>
	";	
	
	$tab="";
	$cntr2=0;
	$in_tot=0;
	$out_tot=0;
	$run_tot=0;
	$is_split=0;
	$include_shuttle_runs=1;
	
	$tab.="
		<table celppading='0' cellspacing='0' width='100%' border='0'>		
		<tr>
			<td>Name</td>
			<td nowrap>From</td>
			<td nowrap>To</td>
			<td>Driver</td>
			<td>Truck</td>
			<td nowrap>Start</td>
			<td nowrap>End</td>			
			<td align='right'>Cost</td>
			<td align='right'>Sales</td>
			<td align='right'>Profit</td>												
		</tr>
	";
	
	$sql3 = "
		select trucks_log_shuttle_routes.*,
          	(select trucks.name_truck from trucks where trucks.id=trucks_log_shuttle_routes.truck_id) as mytruck,
          	(select trailers.trailer_name from trailers where trailers.id=trucks_log_shuttle_routes.trailer_id) as mytrailer,
          	(select CONCAT(drivers.name_driver_first, ' ' ,drivers.name_driver_last) from drivers where drivers.id=trucks_log_shuttle_routes.driver_id) as mydriver,
          	
          	(select users.username from users where users.id=trucks_log_shuttle_routes.user_id) as myuser,
          	(select option_values.fname from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myname,
          	(select option_values.fvalue from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myval
          from trucks_log_shuttle_routes
          where trucks_log_shuttle_routes.deleted=0
          	and trucks_log_shuttle_routes.timesheet_id='".sql_friendly($row2['id'])."'
          					
          	
          order by trucks_log_shuttle_routes.linedate_from,
          	trucks_log_shuttle_routes.linedate_to,
          	trucks_log_shuttle_routes.id
	";	
			//and trucks_log_shuttle_routes.linedate_from >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
			//and trucks_log_shuttle_routes.linedate_from <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'	
	$data3 = simple_query($sql3);
	while($row3 = mysqli_fetch_array($data3)) 
	{
		$value=0;
		$cost=0;
		
		$use_namer1="";
		$use_namer2="";
		
		$shuttle_route=0;
		$use_pay_rate=option_value_text($row3['option_id'],2);						//pay rate (for invoice amount) by shuttle run or by base settings (Carlex=145, Vietti=159).
					
		if($row3['option_id']==159)	
		{
			$shuttle_route=0;		
			$value=$use_pay_rate * ($row3['conard_hours'] - $row3['lunch-break']);	//Vietti Foods...all of them use this...no shuttle runs.	
			$cost=$row3['pay_rate_hours'] * ($row3['conard_hours'] - $row3['lunch-break']);
		}
		elseif($row3['option_id'] > 0 && $row3['option_id'] !=145)
		{
			$shuttle_route=1;												//this is a shuttle run location for Carlex (flat rate)...skip it?
			$value=$use_pay_rate;											// * $row2['conard_hours'];
			
			if(($row3['conard_hours'] - $row3['lunch-break']) > 0)
			{
				$labor=option_value_text(145,2);		//grab from the NONE - Switching ONLY rate.	
				
				$value+=($labor * ($row3['conard_hours'] - $row3['lunch-break']));	
			}
			$cost=$row3['pay_rate_hours'] * ($row3['hours'] - $row3['lunch-break']);
			
			$use_namer1="Shuttle Run:";
			$use_namer2="".option_value_text($row3['option_id'],1);
		}
		else
		{
			$value=$use_pay_rate * ($row3['hours'] - $row3['lunch-break']);
			$cost=$row3['pay_rate_hours'] * ($row3['conard_hours'] - $row3['lunch-break']);
		}
		
		$cost+=($row3['cost'] * $row3['days_run']);
		
		$profit=$value - $cost;
		
		
		
		$out_a_range=0;
		if(strtotime($row3['linedate_from']) > strtotime($_POST['date_to']." 23:59:59"))		$out_a_range=1;
		if(strtotime($row3['linedate_to']) < strtotime($_POST['date_from']." 00:00:00"))		$out_a_range=1;
		
		if($shuttle_route==0 || $include_shuttle_runs > 0)
		{
			$run_tot+=$value;
			
			if($out_a_range > 0)
			{
				$out_tot+=$value;
				$is_split++;
			}
			else
			{
				$in_tot+=$value;
			}
			
			//<td nowrap>".$use_namer1."</td>
			//<td nowrap>".$use_namer2."</td>
			$tab.="
				<tr style='background-color:#eeeeee;".($out_a_range > 0 ? " color:#cc0000;" : "")."' class='timesheet_details_row timesheet_".$row2['id']."_details'>
					<td>".trim($row3['myname'])."</td>
					<td nowrap>".date("m/d/Y H:i:s", strtotime($row3['linedate_from']))."</td>
					<td nowrap>".date("m/d/Y H:i:s", strtotime($row3['linedate_to']))."</td>
					<td>".trim($row3['mydriver'])."</td>
					<td>".trim($row3['mytruck'])."</td>				
					<td nowrap>".date("m/d/Y H:i:s", strtotime($row3['linedate_start']))."</td>
					<td nowrap>".date("m/d/Y H:i:s", strtotime($row3['linedate_end']))."</td>					
					<td align='right'>$".money_format('',$cost)."</td>
					<td align='right'>$".money_format('',$value)."</td>
					<td align='right'>".($profit <= 0 ? "<span style='color:red'>" : "<span>")."$".money_format('',$profit)."</span></td>												
				</tr>
			";	
			$cntr2++;
		}		
	}
	//{$". money_format('',$in_tot)." + <span style='color:#cc0000;' title='Timesheet has parts of invoice out of date range.'><b>$". money_format('',$out_tot) ."</b></span>}
	//{$ + <span style='color:#cc0000;' title='Timesheet has parts of invoice out of date range.'><b>$</b></span>}
	$tab.="
			<tr style='background-color:#eeeeee;'>
				<td align='right'>".$cntr2."</td>
				<td nowrap colspan='6'>Timesheet Total $".number_format($in_tot,2)." + <span style='color:#cc0000;' title='Timesheet has parts of invoice out of date range.'><b>$".number_format($out_tot,2)."</b></span></td>
				<td align='right'>&nbsp;</td>
				<td align='right'>$".money_format('',$run_tot)."</td>
				<td align='right'>&nbsp;</td>												
			</tr>
		";	
	$tab.="</table>";
	if($cntr2==0)		$tab="No Timesheet Entries.";
	
	$in_gtot+=$in_tot;
	$out_gtot+=$out_tot;
	$run_gtot+=$run_tot;
	
	
	echo "
		<tr class='".($cntr%2==0 ? "even" : "odd")."'>
			<td valign='top'>".($is_split > 0 ? "<span style='color:#cc0000;' title='Timesheet info splits months.'><b>Split</b></span>" : "&nbsp;")."</td>
			<td valign='top' colspan='7'>".$tab."</td>
			<td valign='top' align='right' nowrap>
				<span style='color:blue; cursor:pointer;' onClick='mrr_show_timesheet_details(".$row2['id'].");' title='Show details for this timesheet.'><b>Show</b></span> 
				/ 
				<span style='color:blue; cursor:pointer;' onClick='mrr_hide_timesheet_details(".$row2['id'].");' title='Show details for this timesheet.'><b>Hide</b></span> 
			</td>
		</tr>
	";	
	
	$cntr++;
}
echo "
		<tr style='font-weight:bold;'>
			<td valign='top'>".$cntr."</td>
			<td valign='top'>Timesheet In $".money_format('',$in_gtot)." + <span style='color:#cc0000;' title='Timesheet has parts of invoice out of date range.'><b>Out $".money_format('',$out_gtot)."</b></span> = $".money_format('',$run_gtot)."</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>".$invoiced." Invoiced</td>
			<td valign='top' align='right'>".$not_invoiced." Not</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>			
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>".$runs." Runs</td>
		</tr>
		</table>
";	


$mrr_rep.="
<br><br>
<h2>Discrepancy Report Summary:</h2><br>
<table cellpadding='0' cellspacing='0' border='0' width='800'>
<tr>
	<td valign='top' align='left'>[46000]</td>
	<td valign='top' align='left'> Discount (Trucks)</td>
	<td valign='top' align='right'>$".number_format($acct_discounts1,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'>[49500]</td>
	<td valign='top' align='left'>Discount (General)</td>
	<td valign='top' align='right'>+ $".number_format($acct_discounts2,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='right'><hr></td>
</tr>
<tr>
	<td valign='top' align='left'>([46000] + [49500])</td>
	<td valign='top' align='left'>Acct Discounts</td>
	<td valign='top' align='right'>$".number_format(($acct_discounts1+$acct_discounts2),2)."</td>
</tr>
<tr>
	<td valign='top' align='left'>(Credits/price alerts/etc...from Excel Data above)</td>
	<td valign='top' align='left'>Calc Discounts</td>
	<td valign='top' align='right'>+ $".number_format($discount_total,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='right'><hr></td>
</tr>
<tr>
	<td valign='top' align='left'>(Calculated - Accounting) Discount Credits</td>
	<td valign='top' align='left'>'Credit Difference'</td>
	<td valign='top' align='right'><b>$".number_format(($discount_total + $acct_discounts1 + $acct_discounts2),2)."</b></td>
</tr>
<tr>
	<td valign='top' colspan='3'>&nbsp;</td>
</tr>
<tr>
	<td valign='top' colspan='3'><i>Display Only</i></td>
</tr>
<tr>
	<td valign='top' align='left'><b>[45000]</b></td>
	<td valign='top' align='left'>Misc Income -Trailer Rental</td>
	<td valign='top' align='right'><b>$".number_format($acct_misc_income1,2)."</b></td>
</tr>
<tr>
	<td valign='top' align='left'>[48800]</td>
	<td valign='top' align='left'>Misc Income -Fuel Rebates</td>
	<td valign='top' align='right'>$".number_format($acct_misc_income2,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'>[48900]</td>
	<td valign='top' align='left'>Misc Income -Refunds</td>
	<td valign='top' align='right'>$".number_format($acct_misc_income3,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'>[48915]</td>
	<td valign='top' align='left'>Misc Income -Conard Acct</td>
	<td valign='top' align='right'>$".number_format($acct_misc_income4,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'>[48925]</td>
	<td valign='top' align='left'>Misc Income -Software</td>
	<td valign='top' align='right'>$".number_format($acct_misc_income5,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='right'><hr></td>
</tr>
<tr>
	<td valign='top' align='left'>([45000] + [48800] + [48900] + [48915] + [48925])</td>
	<td valign='top' align='left'>Misc Income All</td>
	<td valign='top' align='right'>$".number_format($acct_misc_incomeX,2)."</td>
</tr>
<tr>
	<td valign='top' colspan='3'>&nbsp;</td>
</tr>
<tr>
	<td valign='top' align='left'><b>([45000] Only)</b></td>
	<td valign='top' align='left'>Misc Income </td>
	<td valign='top' align='right'><b>$".number_format($acct_misc_income,2)."</b></td>
</tr>
<tr>
	<td valign='top' align='left'>(Total 'no trucking entry found')</td>
	<td valign='top' align='left'>NoInvoiceLoad Total</td>
	<td valign='top' align='right'>- $".number_format($no_load_total,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'></td>
	<td valign='top' align='left'>Bills</td>
	<td valign='top' align='right'>- $".number_format($bills_total,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'>(JRNL)</td>
	<td valign='top' align='left'>Journal Entries</td>
	<td valign='top' align='right'>- $".number_format($journal_total,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='right'><hr></td>
</tr>
<tr>
	<td valign='top' align='left'></td>
	<td valign='top' align='left'>Acct Misc Diff</td>
	<td valign='top' align='right'>$".number_format(($acct_misc_income - $no_load_total + $journal_total + $bills_total),2)."</td>
</tr>
<tr>
	<td valign='top' colspan='3'>&nbsp;</td>
</tr>
<tr>
	<td valign='top' align='left'>(Misc Income All + Acct Misc Diff)</td>
	<td valign='top' align='left'><b>Balanced Misc Income</b></td>
	<td valign='top' align='right'>$".number_format($acct_misc_incomeZ ,2)."</td>
</tr>
<tr>
	<td valign='top' align='left'>('Credit Difference')</td>
	<td valign='top' align='left'><b>Discount Diff</b></td>
	<td valign='top' align='right'>+ $".number_format(($discount_total + $acct_discounts1 + $acct_discounts2),2)."</td>
</tr>
<tr>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='left'><hr></td>
	<td valign='top' align='right'><hr></td>
</tr>
<tr>
	<td valign='top' align='left'></td>
	<td valign='top' align='left'><b>Discrepancy</b></td>
	<td valign='top' align='right'><b>$".number_format(($acct_misc_incomeZ + ($discount_total + $acct_discounts1 + $acct_discounts2)),2)."</b></td>
</tr>
</table>
<br><br><br>
";
echo $mrr_rep;
?>
<script type='text/javascript'>
	$('.datepicker').datepicker();
	
	function mrr_show_timesheet_details(id)
	{
		$('.timesheet_'+id+'_details').show();
	}
	function mrr_hide_timesheet_details(id)
	{
		$('.timesheet_'+id+'_details').hide();
	}
		
	$('.timesheet_details_row').hide();	
</script>
<? include('footer.php') ?>