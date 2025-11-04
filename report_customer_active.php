<?
ini_set("max_input_vars","10000");
	$use_title = "Report - Customer Active";
	$usetitle = "Report - Customer Active";
?>
<? include('header.php') ?>
<?
	if(!isset($_POST['customer_id'])) 				$_POST['customer_id'] = 0;
	if(!isset($_POST['mrr_cust_addr1'])) 			$_POST['mrr_cust_addr1'] = '';
	if(!isset($_POST['mrr_cust_addr2'])) 			$_POST['mrr_cust_addr2'] = '';
	if(!isset($_POST['mrr_cust_city'])) 			$_POST['mrr_cust_city'] = '';
	if(!isset($_POST['mrr_cust_state'])) 			$_POST['mrr_cust_state'] = '';
	if(!isset($_POST['mrr_cust_zip'])) 			$_POST['mrr_cust_zip'] = '';
	
	$sql_filter="";
	$sql_filter2="";
	
	if($_POST['date_from']!="")					$sql_filter2.=" and load_handler.linedate_pickup_eta>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'";
	if($_POST['date_to']!="")					$sql_filter2.=" and load_handler.linedate_pickup_eta<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'";
	
	if( $_POST['customer_id'] > 0)				$sql_filter.=" and customers.id='".sql_friendly($_POST['customer_id'])."'";
	if( trim($_POST['mrr_cust_addr1']) !="")		$sql_filter.=" and customers.address1 like '%".sql_friendly($_POST['mrr_cust_addr1'])."%'";
	if( trim($_POST['mrr_cust_addr2']) !="")		$sql_filter.=" and customers.address2 like '%".sql_friendly($_POST['mrr_cust_addr2'])."%'";
	if( trim($_POST['mrr_cust_city']) !="")			$sql_filter.=" and customers.city like '%".sql_friendly($_POST['mrr_cust_city'])."%'";
	if( trim($_POST['mrr_cust_state']) !="")		$sql_filter.=" and customers.state like '%".sql_friendly($_POST['mrr_cust_state'])."%'";
	if( trim($_POST['mrr_cust_zip']) !="")			$sql_filter.=" and customers.zip like '%".sql_friendly($_POST['mrr_cust_zip'])."%'";
		
	$sql="
		select customers.*,
			(
				select count(*) 
				from load_handler 
				where load_handler.customer_id=customers.id 
					and load_handler.deleted=0 
					and (
						(load_handler.invoice_number <> ''	and load_handler.invoice_number is not null)
						or
						(load_handler.sicap_invoice_number <> '' and load_handler.sicap_invoice_number is not null)
					)	
					".$sql_filter2."
			) as loads_invoiced,
			(
				select count(*) 
				from attachments 
				where attachments.section_id='".SECTION_CUSTOMER."' 
					and attachments.deleted='0' 
					and attachments.xref_id=customers.id 
					and attachments.descriptor='M'
			) as doc_cntr
		from customers
		where customers.deleted=0
			and (
				select count(*) 
				from load_handler 
				where load_handler.customer_id=customers.id 
					and load_handler.deleted=0
					and (
						(load_handler.invoice_number <> ''	and load_handler.invoice_number is not null)
						or
						(load_handler.sicap_invoice_number <> '' and load_handler.sicap_invoice_number is not null)
					)
					".$sql_filter2."
				) > 0
			".$sql_filter."
		order by customers.active desc,customers.name_company asc, customers.id asc
	";	//and customers.active>0
	$data = simple_query($sql);	
	
	//new email sending line to submit and send the email directly from this report. 
	$mrr_use_styles=0;	
	if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
	if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
	if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}
	
	$stylex=" style='font-weight:bold;'";
	$mrr_total_head = " style='font-weight:bold; width:1000px; text-align:right;'";
	$tablex=" border='1' cellpadding='1' cellspacing='1' width='1200'";
	$headerx=" style='background-color:#CCCCFF;'";
?>	
<?	
	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_cust_addr1	= true;
	$rfilter->show_cust_addr2	= true;
	$rfilter->show_cust_city		= true;
	$rfilter->show_cust_state	= true;
	$rfilter->show_cust_zip 		= true;
	$rfilter->show_font_size		= true;
	$rfilter->mrr_excel_print_flag= true;
	$rfilter->mrr_send_email_here	= true;
	//$rfilter->show_date_range	=false;
	$rfilter->show_filter();
	
	
	$use_excel=0;		if(isset($_POST['mrr_excel_print_file']))		$use_excel=1;	

	$uuid = createuuid();
	$excel_filename = "ActiveCustomers_$uuid.xls";
	$export_file = "";
	
	
	ob_start();
?>
	<table border='0'>
	<tr>
		<td valign='top'>			
			<?
			$export_file .= "Active Customers (by Invoiced Loads) for ".date("M j, Y", strtotime($_POST['date_from']))." to " . date("M j, Y", strtotime($_POST['date_to']))."".chr(9).
               			"".chr(9).
               			"".chr(9).
               			"".chr(9).
               			"".chr(9).
               			"".chr(9).
               			"".chr(9).
               			"".chr(9).
               			"".chr(9).
               			"".chr(9).
               			"".chr(9).
               			"".chr(9);
               $export_file .= chr(13);	
               $export_file .= chr(13);
               $export_file .= "Customer".chr(9).
               			"Address".chr(9).
               			"Address 2".chr(9).
               			"City".chr(9).
               			"State".chr(9).
               			"Zip".chr(9).
               			"E-Mail".chr(9).
               			"Fax".chr(9).
               			"75K Bond Inception".chr(9).
               			"Expires/Renewal".chr(9).
               			"CM Docs".chr(9).
               			"Invoiced Loads".chr(9);
               $export_file .= chr(13);	
               
               
			echo "
			<div".( $mrr_use_styles > 0 ? "".$stylex."" : " class='section_heading'").">Active Customers (by Invoiced Loads) for ".date("M j, Y", strtotime($_POST['date_from']))." to " . date("M j, Y", strtotime($_POST['date_to']))."</div>
			<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section tablesorter'").">
			<thead>
			<tr". ( $mrr_use_styles > 0 ? "".$headerx."" : "").">
				<th align='left'><b>Customer</b></th>
          		<th align='left'><b>Address</b></th>
          		<th align='left'><b>Address 2</b></th>
          		<th align='left'><b>City</b></th>
          		<th align='left'><b>State</b></th>
          		<th align='left'><b>Zip</b></th>
          		<th align='left'><b>E-Mail</b></th>
          		<th align='left'><b>Fax</b></th>
          		<th align='right'><b>75K Bond Inception</b></th>
          		<th align='left'><b>Expires/Renewal</b></th>
          		<th align='right'><span title='Customer 75K Bond Documents that appear to be uploaded'><b>CM Docs</b></span></th>
          		<th align='right' nowrap><b>Invoiced Loads</b></th>
			</tr>
			</thead>
			<tbody>
			";		
          	$cntr=0;
          	while($row=mysqli_fetch_array($data))
          	{
          		$act_col="";
          		if($row['active']==0)	$act_col=" style='color:#cccccc;'";
          		
          		$link1="<a href='admin_customers.php?eid=".$row['id']."' target='_blank'".$act_col.">".$row['name_company']."</a>";
          		$link2="";	//"<a href='admin_customers.php?eid=".$row['id']."' target='_blank'".$act_col.">".$row['name_company']."</a>";
          		
          		$doc_flag2="";
          		$doc_flag="<span class='alert'><b>MISSING</b></span>";
          		if($row['document_75k_exempt'] > 0)	$doc_flag="<b>Exempt</b>";
          		if($row['document_75k_received'] > 0)	
          		{
          			$doc_flag="".date("m/d/Y", strtotime($row['linedate_document_75k']))."";
          			if($row['linedate_expires_75k']!="0000-00-00 00:00:00" && $row['linedate_expires_75k']>="2010-01-01 00:00:00")
          			{
          				$doc_flag2="".date("m/d/Y", strtotime($row['linedate_expires_75k']))."";
          			}
          			else
          			{
          				$doc_flag2="".date("m/d/Y", strtotime("+1 year",strtotime($row['linedate_document_75k'])))."";
          			}
          		}
          		if($doc_flag=="12/31/1969")			$doc_flag="Yes(No Date)";
          		
          		$doc2_flag="<span class='alert'><b>NONE</b></span>";
          		if($row['doc_cntr'] > 0)		$doc2_flag="".$row['doc_cntr']."";	
          		          		
          		          		
          		echo "
               		<tr class='".($cntr %2==0 ? "even" : "odd")."'>
               			<td valign='top' align='left'>".$link1."</td>
               			<td valign='top' align='left'>".$row['address1']."</td>
               			<td valign='top' align='left'>".$row['address2']."</td>
               			<td valign='top' align='left'>".$row['city']."</td>
               			<td valign='top' align='left'>".$row['state']."</td>
               			<td valign='top' align='left'>".$row['zip']."</td>
               			<td valign='top' align='left'>".trim($row['contact_email'])."</td>
               			<td valign='top' align='left'>".trim($row['fax'])."</td>
               			<td valign='top' align='right'>".$doc_flag."</td>
               			<td valign='top' align='right'>".$doc_flag2."</td>
               			<td valign='top' align='right'>".$doc2_flag."</td>
               			<td valign='top' align='right'><a href='report_invoiced.php?customer_id=".$row['id']."&date_from=".date("Y-m-d",strtotime($_POST['date_from']))."&date_to=".date("Y-m-d",strtotime($_POST['date_to']))."' target='_blank'>".$row['loads_invoiced']."</a></td>
               		</tr>
          		";
          		
          		$export_file .= "".$row['name_company']."".chr(9).
               			"".$row['address1']."".chr(9).
               			"".$row['address2']."".chr(9).
               			"".$row['city']."".chr(9).
               			"".$row['state']."".chr(9).
               			"".$row['zip']."".chr(9).
               			"".trim($row['contact_email'])."".chr(9).
               			"".trim($row['fax'])."".chr(9).
               			"".strip_tags($doc_flag)."".chr(9).
               			"".strip_tags($doc_flag2)."".chr(9).
               			"".strip_tags($doc2_flag)."".chr(9).
               			"".$row['loads_invoiced']."".chr(9);
               	$export_file .= chr(13);	
          		
          		
          		$cntr++;
          	}
          	?>          	
          	<!--
          	<tr>
          		<td valign='top' align='left' width='150'><b>Date</b></td>
          		<td valign='top' align='left' width='150'><input type='text' name='date_from' id='date_from' value='<?= $_POST['date_from'] ?>' class='input_medium' onChange='mrr_load_up_ar_details();'></td>
          		<td valign='top' align='left' width='150'><b>Aging (days)</b></td>
          		<td valign='top' align='left' width='150'><input type='text' name='mrr_aging_from' id='mrr_aging_from' value='<?= $_POST['mrr_aging_from'] ?>' class='input_medium' onChange='mrr_load_up_ar_details();'></td>
          		<td valign='top' align='left' width='150'>&nbsp; to &nbsp;</td>
          		<td valign='top' align='left' width='150'>&nbsp; to &nbsp;</td>
          		<td valign='top' align='left' width='150'><input type='text' name='mrr_aging_to' id='mrr_aging_to' value='<?= $_POST['mrr_aging_to'] ?>' class='input_medium' onChange='mrr_load_up_ar_details();'></td>
          		<td valign='top' align='right'><span class='mrr_link_like_on' onClick='mrr_toggle_inv_display();'>Toggle Invoice List</span></td>
          	</tr>        	
          	
          	<br>          	
          	<table border='0' class='admin_menu2' width='1300'>
          	<tr>		
          		<td valign='top'><div id='mrr_report_view'></div></td>
          	</tr>
          	</table>
          	-->
          	
          	</tbody>
          	</table>
     	</td>
     </tr>
     </tr>
	</table>
	<?
	
	$pdf = ob_get_contents();
	ob_end_clean();
	
	echo $pdf;
	
	if($use_excel > 0)
	{
		$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
		fwrite($fp, $export_file); 
		fclose($fp);
	}
	
	$prefix="";
	if($use_excel > 0) 
	{
		$prefix="<br><br><a href=\"http://trucking.conardlogistics.com/temp/".$excel_filename."\" target='_blank'>Click for Excel Version</a><br><br>";
		echo $prefix;
	}
	
	if(trim($_POST['mrr_email_addr'])!="" && isset($_POST['mrr_email_report']))
     {
     	$user_name=$defaultsarray['company_name'];
     	$From=$defaultsarray['company_email_address'];
     	$Subject="";
     	if(isset($use_title))			$Subject=$use_title;
     	elseif(isset($usetitle))			$Subject=$use_title;
     	
     	$pdf=str_replace(" href="," name=",$pdf);
     	//$pdf=str_replace("</a>","",$pdf);
     		
     	$sentit=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject,$pdf,$pdf);
     	
     	$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
     	echo "<br><br><b>This report has ".$sent_msg." to '".$_POST['mrr_email_addr_name']."' at E-Mail address '".$_POST['mrr_email_addr']."'.</b><br><br>";
     	
     	//$sentit=mrr_trucking_sendMail('dconard@conardlogistics.com',"Dale Conard",$From,$user_name,'','',$Subject,$pdf,$pdf);
     	//$sentit=mrr_trucking_sendMail('jgriffith@conardlogistics.com',"James Griffith",$From,$user_name,'','',$Subject,$pdf,$pdf);
     	
     	//$sentit=mrr_trucking_sendMail('amassar@conardlogistics.com',"Anthony Massar",$From,$user_name,'','',$Subject,$pdf,$pdf);     	
     }
	?>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	//$('#date_from').datepicker();
	//$('#date_to').datepicker();
	/*
	$().ready(function() 
	{	
		mrr_load_up_cust_avg_payments();		
	});
	
	function mrr_load_up_cust_avg_payments()
	{
		$('#mrr_report_view').html('Loading...Takes about 8 Seconds...');				//mrr_get_ar_detail_info_find
		
		$.ajax({
			url: "ajax.php?cmd=mrr_cust_average_pay_report",
			type: "post",
			dataType: "xml",
			data: {
				
			},
			error: function() {
				$.prompt("Error: Average Payment info could be found.");
				$('#mrr_report_view').html('Done. No information found.');
			},
			success: function(xml) {
				if($(xml).find('mrrTab').text() == '')
				{
					$('#mrr_report_view').html('Done. No information found.');
				}
				else
				{
					$('#mrr_report_view').html($(xml).find('mrrTab').text());	
					$('.tablesorter').tablesorter();		
				}
			}
		});
		
	}
	*/
</script>
<? include('footer.php') ?>