<? include('header.php') ?>
<? 

	if(!isset($_POST['import_box'])) $_POST['hide_matching'] = 1; // set our checkbox to default checked

	if(!isset($_POST['import_box'])) $_POST['import_box'] = "";
	if(!isset($_POST['date_from'])) $_POST['date_from'] = "";
	if(!isset($_POST['date_to'])) $_POST['date_to'] = "";
	
	

	if(isset($_POST['pull_from_sicap']) && $_POST['date_from'] != '' && $_POST['date_to'] != '') {
		$rslt = sicap_get_invoices_by_date($_POST['date_from'], $_POST['date_to']);
		//var_dump($rslt);
		$invoice_feed = "";
		foreach($rslt->InvoiceEntry as $invoice_entry) {
			$invoice_feed .= $invoice_entry->InvoiceID.chr(9).$invoice_entry->Amount.chr(9).$invoice_entry->InvoiceDate.chr(13);
		}
		
		$_POST['import_box'] = $invoice_feed;
	}
?>
<br><br><br>
<div style='float:left'>
<form action='' method='post'>
<table style='text-align:left;margin-left:15px'>
<tr>
	<td colspan='2'><h3>Accounting Invoice Comparison report</h3></td>
</tr>
<tr>
	<td>Date From:</td>
	<td><input name='date_from' value='<?=$_POST['date_from']?>' class='datepicker'></td>
</tr>
<tr>
	<td>Date To:</td>
	<td><input name='date_to' value='<?=$_POST['date_to']?>' class='datepicker'></td>
</tr>
<?
if($defaultsarray['sicap_integration'] == 1) {
	echo "
		<tr>
			<td colspan='2'><label><input type='checkbox' name='pull_from_sicap' ".(isset($_POST['pull_from_sicap']) ? "checked" : "")."> Pull from Accounting System</label></td>
		</tr>
	";
}
?>
<tr>
	<td colspan='2'><label><input type='checkbox' name='hide_matching' <?=(isset($_POST['hide_matching']) ? "checked" : "")?>> Check checkbox to only show discrepancies</label></td>
</tr>
<tr>
	<td colspan='2'
		<div style='border:1px black solid;background-color:#eeeeee;padding:15px;margin:10px 0;width:300px'>
			Please copy data from Excel
			<li>The only two columns copied should be the <b>invoice number</b> and <b>invoice amount</b>. </li>
			<li>The invoice number should be the first column. </li>
			<li>The column headers should <b>not</b> be included.</li>
		</div>
	</td>
</tr>
<tr>
	<td colspan='2'>
		Excel Data<br>
		<textarea name='import_box' style='width:400px;height:200px'><?=$_POST['import_box']?></textarea>
	</td>
</tr>
<tr>
	<td></td>
	<td><input type='submit' value='Submit'></td>
</tr>
</table>
</form>
</float>

<br><br><br>


<table style='width:600px'>
<tr>
	<td align='left'><b>Invoice Number</b></td>
	<td align='right'><b>Accounting Amount</b></td>
	<td align='right'><b>Trucking Amount</b></td>
	<td></td>
</tr>
<?
	if($_POST['import_box'] != '') {
		$line_array = explode(chr(13), $_POST['import_box']);
		
		
		$trucking_total = 0;
		$peachtree_total = 0;
		$invoice_array = array();
		
		foreach($line_array as $line) {
			$line_part_array = explode(chr(9), $line);
			
			if(count($line_part_array) == 2 || count($line_part_array) == 3) {
				
				$invoice_number = trim($line_part_array[0]);

				//if(in_array("'$invoice_number'", $invoice_array)) die("duplicate found");
				$invoice_array[] = "'$invoice_number'";
				
				$sql = "
					select ifnull(sum(actual_bill_customer),0) as total_bill_customer,
						linedate_invoiced
					
					from load_handler
					where invoice_number = '".sql_friendly($invoice_number)."'
						and deleted = 0
				";
				$data = simple_query($sql);				
				$row = mysqli_fetch_array($data);
				
				$peachtree_amount = money_strip(trim($line_part_array[1]));
				$trucking_amount = money_strip($row['total_bill_customer']);

				$peachtree_total += $peachtree_amount;
				$trucking_total += $trucking_amount;

				// get the 1% and 2% discounts
				$trucking_discount_one = number_format($trucking_amount * .99, 2, '.', '');
				$trucking_discount_two = number_format($trucking_amount * .98, 2, '.', '');
				//$account

				if((($trucking_amount == $peachtree_amount) || ($trucking_discount_one == $peachtree_amount) || ($trucking_discount_two == $peachtree_amount)) && isset($_POST['hide_matching'])) {
				} else {
					echo "
						<tr>
							<td align='left'>
								<a href='report_invoiced.php?invoice_number=$line_part_array[0]' target='view_invoice_$line_part_array[0]'>$line_part_array[0]</a>
								".($defaultsarray['sicap_integration'] == 1 ? "<a href='accounting/invoice.php?invoice_id=$line_part_array[0]' target='view_invoice_".$line_part_array[0]."'><img src='images/edit_small.png' style='border:0'></a>" : "" )."
							</td>
							<td align='right'>".money_format('',$peachtree_amount)." </td>
							<td align='right'>".money_format('',$trucking_amount)."</td>
							<td align='right'>
								".($trucking_amount != $peachtree_amount ? "<span class='alert'>&nbsp;&nbsp;&nbsp; Price Alert</span>" : "")."
							</td>
						</tr>
					";
				}
				//echo $line_part_array[0]."<br>";
			}
		}
		
		// see if there are any invoices in our system that do not match up in peachtree
		$sql = "
			select *
				
			
			from load_handler
			where invoice_number not in (".implode(",",$invoice_array).")
				and deleted = 0
				and linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and linedate_pickup_eta < '".date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])))."'
		";
		//echo $sql;
		$data_missing = simple_query($sql);
		
		if(mysqli_num_rows($data_missing)) {
			while($row_missing = mysqli_fetch_array($data_missing)) {
				$trucking_total += $row_missing['actual_bill_customer'];
				echo "
					<tr>
						<td align='left'>
							<a href='report_invoiced.php?invoice_number=$row_missing[invoice_number]' target='view_invoice_$row_missing[invoice_number]'><span class='alert'>$row_missing[invoice_number]</span></a>
							".($defaultsarray['sicap_integration'] == 1 ? "<a href='accounting/invoice.php?invoice_id=$row_missing[invoice_number]' target='view_invoice_".$row_missing['invoice_number']."'><img src='images/edit_small.png' style='border:0'></a>" : "" )."
						</td>
						<td align='right'>0</td>
						<td align='right'>".money_format('', $row_missing['actual_bill_customer'])."</td>
						<td align='right'><span class='alert'>missing in accounting system</span></td>
					</tr>
				";
			}
		}
		
		echo "
			<tr>
				<td colspan='5'><hr></td>
			</tr>
			<tr>
				<td></td>
				<td align='right'>Accounting System:<br>$".money_format('', $peachtree_total)."</td>
				<td align='right'>Trucking:<br>$".money_format('', $trucking_total)."</td>
				<td align='right'>Difference: <br>$".money_format('', $peachtree_total - $trucking_total)."</td>
			</tr>
		";
		
	}
	

?>

</table>


<script type='text/javascript'>
	$('.datepicker').datepicker();
</script>


<? include('footer.php') ?>