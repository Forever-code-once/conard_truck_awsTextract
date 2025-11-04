<? include('header.php') ?>
<?

	if(isset($_POST['resend_items']) && isset($_POST['resend_id_list'])) {
		$sql = "
			update load_handler set
			 	linedate_edi_invoice_sent = '0000-00-00 00:00:00',
			 	linedate_edi_response_sent = '0000-00-00 00:00:00'
			where id in (".sql_friendly(implode(",",$_POST['resend_id_list'])).")
		";
		simple_query($sql);
		
		echo "<div class='admin_menu3' style='width:300px;margin:20px 20px 0 10px;float:left'>";
		echo "Resending the following LynnCo Loads:<br>";
		foreach($_POST['resend_id_list'] as $value) 
		{
			echo "$value<br>";
			
			$load_id=(int) trim($value);
			mrr_add_lynnco_edi_invoicing_log($_SESSION['user_id'],$load_id,0,'',0,0,1,0);
		}
		echo "</div><div style='clear:both'></div>";
	}

	echo "<form action='' method='post'>";
	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_load_id 		= true;
	$rfilter->show_font_size		= true;
	$rfilter->mrr_no_form_enclosed= true;
	$rfilter->show_filter();
	
	
 	
	
		$search_date_range = '';
		if($_POST['load_handler_id'] != '') {
		} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				load_handler.linedate_edi_invoice_sent >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and load_handler.linedate_edi_invoice_sent <= '".date("Y-m-d 23:59:59", strtotime($_POST['date_to']))."'
			";
		}
	
		$sql = "
			select load_handler.*,
				customers.name_company,
				load_handler.id as load_handler_id
			
			from load_handler
				left join customers on customers.id = load_handler.customer_id
				
			where load_handler.deleted = 0
				and lynnco_edi > 0
			    and (
        				(
						load_handler.linedate_invoiced>0 
						and load_handler.auto_created > 0 
						and (load_handler.linedate_edi_invoice_response = 0 or load_handler.linedate_edi_invoice_sent=0) 
						and load_handler.lynnco_edi_input_file!=''
					)
        				or 
        				(
        					$search_date_range
        				)  
			     )				
				
				".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
				".($_POST['customer_id'] ? " and load_handler.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			
			order by load_handler.invoice_number
		";
		
		$data = simple_query($sql);
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:950px;text-align:left'>
	<tr>
		<td colspan='10'>
			<center>
			<span class='section_heading'>LynnCo EDI Invoiced Report</span>
			
			<br><br>Click to force the automatic <a href='edi_fedex_invoice.php?autorun=1' target='_blank'>LynnCo EDI Invoicing</a> process to run right now. Otherwise, it will run again at 2:35AM on its own.<br><br>
			</center>
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td nowrap>Load ID</td>
		<td>Invoice #</td>
		<td>Origin</td>
		<td>Destination</td>
		<td>Date Picked Up</td>
		<td>Date EDI Sent</td>
		<td>Received Response</td>
		<td align='center'>Resend</td>
		<td>Customer</td>
	</tr>
	<?
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		$total_profit = 0;
		while($row = mysqli_fetch_array($data)) 
		{
			$counter++;
			
			//$total_miles += $row['miles'];
			//$total_deadhead += $row['miles_deadhead'];
			//$total_profit += $row['profit'];
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')."' style='".($row['linedate_edi_invoice_response'] == 0 ? "background-color:#ffc7c7" : "")."'>
					<td>".($row['load_handler_id'] > 0 ? "<a href='manage_load.php?load_id=$row[load_handler_id]' target='view_load_$row[load_handler_id]'>$row[load_handler_id]</a>" : "")."</td>
					<td>".$row['invoice_number']."</td>
					<td nowrap>$row[origin_city]</td>
					<td nowrap>$row[dest_city]</td>
					<td nowrap>".date("M j, Y", strtotime($row['linedate_pickup_eta']))."</td>
					<td nowrap><label for='checkbox_$row[id]'>".date("M j, Y h:i a", strtotime($row['linedate_edi_invoice_sent']))."</label></td>
					<td>".($row['linedate_edi_invoice_response'] > 0 ? date("M j, Y h:i a", strtotime($row['linedate_edi_invoice_response'])) : "<span class='alert'>Not Received Yet</span>")."</td>
					<td align='center'><input type='checkbox' name='resend_id_list[]' id='checkbox_$row[id]' value='$row[id]'></td>
					<td nowrap>$row[name_company]</td>
				</tr>
			";
		}
	?>
	<tr>
		<td colspan='15'>
			<hr>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan='4'><?=number_format($counter)?> load(s)</td>
		<td colspan='10'><input type='submit' name='resend_items' value='Resend Checked Loads'></td>
	</tr>
	</table>
</form>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
</script>
<? include('footer.php') ?>