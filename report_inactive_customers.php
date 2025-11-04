<?
ini_set("max_input_vars","10000");
	$use_title = "Report - Inactive Customers";
	$usetitle = "Report - Inactive Customers";
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
	if( $_POST['customer_id'] > 0)				$sql_filter.=" and customers.id='".sql_friendly($_POST['customer_id'])."'";
	if( trim($_POST['mrr_cust_addr1']) !="")		$sql_filter.=" and customers.address1 like '%".sql_friendly($_POST['mrr_cust_addr1'])."%'";
	if( trim($_POST['mrr_cust_addr2']) !="")		$sql_filter.=" and customers.address2 like '%".sql_friendly($_POST['mrr_cust_addr2'])."%'";
	if( trim($_POST['mrr_cust_city']) !="")			$sql_filter.=" and customers.city like '%".sql_friendly($_POST['mrr_cust_city'])."%'";
	if( trim($_POST['mrr_cust_state']) !="")		$sql_filter.=" and customers.state like '%".sql_friendly($_POST['mrr_cust_state'])."%'";
	if( trim($_POST['mrr_cust_zip']) !="")			$sql_filter.=" and customers.zip like '%".sql_friendly($_POST['mrr_cust_zip'])."%'";
	if( isset($_POST['report_active']))			$sql_filter.=" and customers.active>0";
	
	$sql_range="
		and (
				(
				select count(*) 
				from load_handler 
				where load_handler.customer_id=customers.id 
					and load_handler.deleted=0
	";
	if($_POST['date_from']!="" && $_POST['date_to']!="")	
	{
		$sql_range.=" 
					and load_handler.linedate_pickup_eta>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'
					and load_handler.linedate_pickup_eta<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
		";
	}
	elseif($_POST['date_from']!="")	
	{
		$sql_range.=" 
					and load_handler.linedate_pickup_eta>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'
		";
	}
	elseif($_POST['date_to']!="")	
	{
		$sql_range.=" 
					and load_handler.linedate_pickup_eta<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
		";
	}
	$sql_range.="
				) =0
			)
	";
		
	$sql="
		select customers.*,
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
			and customers.name_company!='New Customer'
			".$sql_filter."
			".$sql_range."
		order by customers.active desc,customers.name_company asc, customers.id asc
	";
	$data = simple_query($sql);	
	
?>	
<?	
	$rfilter = new report_filter();
	$rfilter->show_active		= true;	
	$rfilter->show_customer 		= true;
	$rfilter->show_cust_addr1	= true;
	$rfilter->show_cust_addr2	= true;
	$rfilter->show_cust_city		= true;
	$rfilter->show_cust_state	= true;
	$rfilter->show_cust_zip 		= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_date_range	= true;
	$rfilter->show_filter();
?>
	<table border='0'>
	<tr>
		<td valign='top'>
          	<table border='0' class='admin_menu1' width='1300'>
          	<tr>
          		<td valign='top' colspan='7'><center><b>Inactive Customers By Location</b></center></td>
          	</tr>
          	<tr>
          		<td valign='top' align='left'><b>Customer</b></td>
          		<td valign='top' align='left'><b>Address</b></td>
          		<td valign='top' align='left'><b>Address 2</b></td>
          		<td valign='top' align='left'><b>City</b></td>
          		<td valign='top' align='left'><b>State</b></td>
          		<td valign='top' align='left'><b>Zip</b></td>
          		<td valign='top' align='right'><b>75K Bond Inception</b></td>
          		<td valign='top' align='left'><b>Expires/Renewal</b></td>
          		<td valign='top' align='right'><span title='Customer 75K Bond Documents that appear to be uploaded'><b>CM Docs</b></span></td>
          		<td valign='top' align='left'><b>&nbsp;</b></td>
          	</tr>
          	<?
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
          		<tr".$act_col.">
          			<td valign='top' align='left'>".$link1."</td>
          			<td valign='top' align='left'>".$row['address1']."</td>
          			<td valign='top' align='left'>".$row['address2']."</td>
          			<td valign='top' align='left'>".$row['city']."</td>
          			<td valign='top' align='left'>".$row['state']."</td>
          			<td valign='top' align='left'>".$row['zip']."</td>
          			<td valign='top' align='right'>".$doc_flag."</td>
          			<td valign='top' align='right'>".$doc_flag2."</td>
          			<td valign='top' align='right'>".$doc2_flag."</td>
          			<td valign='top' align='left'>".$link2."</td>
          		</tr>
          		";
          	}
          	?>          	
          	<!--
          	<tr>
          		<td valign='top' align='left' width='150'><b>Date</b></td>
          		<td valign='top' align='left' width='150'><input type='text' name='date_from' id='date_from' value='<?= $_POST['date_from'] ?>' class='input_medium' onChange='mrr_load_up_ar_details();'></td>
          		<td valign='top' align='left' width='150'><b>Aging (days)</b></td>
          		<td valign='top' align='left' width='150'><input type='text' name='mrr_aging_from' id='mrr_aging_from' value='<?= $_POST['mrr_aging_from'] ?>' class='input_medium' onChange='mrr_load_up_ar_details();'></td>
          		<td valign='top' align='left' width='150'>&nbsp; to &nbsp;</td>
          		<td valign='top' align='left' width='150'><input type='text' name='mrr_aging_to' id='mrr_aging_to' value='<?= $_POST['mrr_aging_to'] ?>' class='input_medium' onChange='mrr_load_up_ar_details();'></td>
          		<td valign='top' align='right'><span class='mrr_link_like_on' onClick='mrr_toggle_inv_display();'>Toggle Invoice List</span></td>
          	</tr>
          	
          	</table>
          	<br>          	
          	<table border='0' class='admin_menu2' width='1300'>
          	<tr>		
          		<td valign='top'><div id='mrr_report_view'></div></td>
          	</tr>
          	</table>
          	-->
     	</td>
     </tr>
     </tr>
	</table>
<script type='text/javascript'>
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