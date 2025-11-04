<?
ini_set("max_input_vars","10000");
	$use_title = "Report - Customers by Location";
	$usetitle = "Report - Customers by Location";
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
	if( $_POST['customer_id'] > 0)				$sql_filter.=" and id='".sql_friendly($_POST['customer_id'])."'";
	if( trim($_POST['mrr_cust_addr1']) !="")		$sql_filter.=" and address1 like '%".sql_friendly($_POST['mrr_cust_addr1'])."%'";
	if( trim($_POST['mrr_cust_addr2']) !="")		$sql_filter.=" and address2 like '%".sql_friendly($_POST['mrr_cust_addr2'])."%'";
	if( trim($_POST['mrr_cust_city']) !="")			$sql_filter.=" and city like '%".sql_friendly($_POST['mrr_cust_city'])."%'";
	if( trim($_POST['mrr_cust_state']) !="")		$sql_filter.=" and state like '%".sql_friendly($_POST['mrr_cust_state'])."%'";
	if( trim($_POST['mrr_cust_zip']) !="")			$sql_filter.=" and zip like '%".sql_friendly($_POST['mrr_cust_zip'])."%'";
		
	$sql="
		select * 
		from customers
		where deleted=0
			".$sql_filter."
		order by active desc,name_company asc, id asc
	";
	$data = simple_query($sql);	
	
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
	$rfilter->show_date_range	=false;
	$rfilter->show_filter();
?>
	<table border='0'>
	<tr>
		<td valign='top'>
          	<table border='0' class='admin_menu1' width='1300'>
          	<tr>
          		<td valign='top' colspan='7'><center><b>Customers By Location</b></center></td>
          	</tr>
          	<tr>
          		<td valign='top' align='left'><b>Customer</b></td>
          		<td valign='top' align='left'><b>Address</b></td>
          		<td valign='top' align='left'><b>Address 2</b></td>
          		<td valign='top' align='left'><b>City</b></td>
          		<td valign='top' align='left'><b>State</b></td>
          		<td valign='top' align='left'><b>Zip</b></td>
          		<td valign='top' align='left'><b>&nbsp;</b></td>
          	</tr>
          	<?
          	while($row=mysqli_fetch_array($data))
          	{
          		$act_col="";
          		if($row['active']==0)	$act_col=" style='color:#cccccc;'";
          		
          		$link1="<a href='admin_customers.php?eid=".$row['id']."' target='_blank'".$act_col.">".$row['name_company']."</a>";
          		$link2="";	//"<a href='admin_customers.php?eid=".$row['id']."' target='_blank'".$act_col.">".$row['name_company']."</a>";
          		          		
          		echo "
          		<tr".$act_col.">
          			<td valign='top' align='left'>".$link1."</td>
          			<td valign='top' align='left'>".$row['address1']."</td>
          			<td valign='top' align='left'>".$row['address2']."</td>
          			<td valign='top' align='left'>".$row['city']."</td>
          			<td valign='top' align='left'>".$row['state']."</td>
          			<td valign='top' align='left'>".$row['zip']."</td>
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