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
          		<td valign='top' colspan='7'><center><b>Customer Tracking Links</b></center></td>
          	</tr>
          	<tr>
          		<td valign='top' align='left'><b>Customer</b></td>
          		<td valign='top' align='left'><b>Address</b></td>
          		<td valign='top' align='left'><b>Address 2</b></td>
          		<td valign='top' align='left'><b>City</b></td>
          		<td valign='top' align='left'><b>State</b></td>
          		<td valign='top' align='left'><b>Zip</b></td>
          		<td valign='top' align='left'><b>Tracking Link</b></td>
          	</tr>
          	<?
          	$cntr=0;
          	while($row=mysqli_fetch_array($data))
          	{          		
          		$link1="<a href='admin_customers.php?eid=".$row['id']."' target='_blank'".$act_col.">".$row['name_company']."</a>";
          		
          		$link2="";
				if(trim($row['customer_login_name'])!="" && trim($row['customer_login_pass'])!="")
				{
					$link2="<a href='http://trucking.conardlogistics.com/customer_loads.php?u=".$row['customer_login_name']."&p=".$row['customer_login_pass']."' target='_blank'>View Page</a>";		
				}  
          		          		          		
          		echo "
          		<tr class='".($cntr%2==0 ? "even" : "odd")."'>
          			<td valign='top' align='left'>".$link1."</td>
          			<td valign='top' align='left'>".$row['address1']."</td>
          			<td valign='top' align='left'>".$row['address2']."</td>
          			<td valign='top' align='left'>".$row['city']."</td>
          			<td valign='top' align='left'>".$row['state']."</td>
          			<td valign='top' align='left'>".$row['zip']."</td>
          			<td valign='top' align='left'>".$link2."</td>
          		</tr>
          		";
          		$cntr++;
          	}
          	?>
     	</td>
     </tr>
     </tr>
	</table>
<? include('footer.php') ?>