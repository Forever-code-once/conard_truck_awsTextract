<?
ini_set("max_input_vars","10000");
	$use_title = "Report - Find Load Info";
	$usetitle = "Report - Find Load Info";
?>
<? include('header.php') ?>
<?

	if(!isset($_POST['load_handler_id'])) 			$_POST['load_handler_id'] = 0;
	if(!isset($_POST['customer_id'])) 				$_POST['customer_id'] = 0;
	//if(!isset($_POST['mrr_cust_addr1'])) 			$_POST['mrr_cust_addr1'] = '';
	//if(!isset($_POST['mrr_cust_addr2'])) 			$_POST['mrr_cust_addr2'] = '';
	if(!isset($_POST['mrr_cust_city'])) 			$_POST['mrr_cust_city'] = '';
	if(!isset($_POST['mrr_cust_state'])) 			$_POST['mrr_cust_state'] = '';
	if(!isset($_POST['mrr_cust_zip'])) 			$_POST['mrr_cust_zip'] = '';
	
	$search_date_range = "";
	if($_POST['load_handler_id'] != 0) 
	{	// we don't want to search by date range if the user is filtering by the load handler ID
		$search_date_range = " and load_handler.id='".sql_friendly($_POST['load_handler_id'])."'";	
	} 
	else 
	{		
		$search_date_range = "
			and load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
			and load_handler.linedate_pickup_eta <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
		";
	}	
	
	$sql_filter="";
	if( $_POST['customer_id'] > 0)				$sql_filter.=" and load_handler.customer_id='".sql_friendly($_POST['customer_id'])."'";
	//if( trim($_POST['mrr_cust_addr1']) !="")		$sql_filter.=" and customers.address1 like '%".sql_friendly($_POST['mrr_cust_addr1'])."%'";
	//if( trim($_POST['mrr_cust_addr2']) !="")		$sql_filter.=" and customers.address2 like '%".sql_friendly($_POST['mrr_cust_addr2'])."%'";
	if( trim($_POST['mrr_cust_city']) !="")			$sql_filter.=" and (load_handler.origin_city like '%".sql_friendly($_POST['mrr_cust_city'])."%' or load_handler.dest_city like '%".sql_friendly($_POST['mrr_cust_city'])."%')";
	if( trim($_POST['mrr_cust_state']) !="")		$sql_filter.=" and (load_handler.origin_state like '%".sql_friendly($_POST['mrr_cust_state'])."%' or load_handler.dest_state like '%".sql_friendly($_POST['mrr_cust_state'])."%')";
	//if( trim($_POST['mrr_cust_zip']) !="")		$sql_filter.=" and (load_handler.origin_zip like '%".sql_friendly($_POST['mrr_cust_zip'])."%' or load_handler.dest_zip like '%".sql_friendly($_POST['mrr_cust_zip'])."%')";
			
	$sql="
		select load_handler.*,
			customers.name_company,
			(select load_handler_stops.shipper_zip from load_handler_stops where load_handler_stops.deleted=0 and load_handler_stops.load_handler_id=load_handler.id order by load_handler_stops.linedate_pickup_eta asc limit 1) as origin_zip2,
			(select load_handler_stops.shipper_zip from load_handler_stops where load_handler_stops.deleted=0 and load_handler_stops.load_handler_id=load_handler.id order by load_handler_stops.linedate_pickup_eta desc limit 1) as dest_zip2		
		
		from load_handler
			left join customers on customers.id=load_handler.customer_id
	
		where load_handler.deleted=0
			".$search_date_range."
			".$sql_filter."
		order by load_handler.id asc		
	";
	$data = simple_query($sql);		
?>	
<?	
	$rfilter = new report_filter();
	$rfilter->show_load_id 		= true;
	$rfilter->show_customer 		= true;
	//$rfilter->show_cust_addr1	= true;
	//$rfilter->show_cust_addr2	= true;
	$rfilter->show_cust_city		= true;
	$rfilter->show_cust_state	= true;
	$rfilter->show_cust_zip 		= true;
	$rfilter->show_font_size		= true;	
	$rfilter->show_filter();
?>
	<table border='0'>
	<tr>
		<td valign='top'>
          	<table border='0' class='admin_menu1' width='1400'>
          	<tr>
          		<td valign='top' colspan='8'><center><b>Customer Load Info</b></center></td>
          	</tr>
          	<tr>
          		<td valign='top' align='left'><b>Load</b></td>
          		<td valign='top' align='left'><b>Customer</b></td>
          		<td valign='top' align='left'><b>Origin City</b></td>
          		<td valign='top' align='left'><b>Origin State</b></td>
          		<td valign='top' align='left'><b>Origin Zip</b></td>
          		<td valign='top' align='left'><b>Dest City</b></td>
          		<td valign='top' align='left'><b>Dest State</b></td>
          		<td valign='top' align='left'><b>Dest Zip</b></td>
          	</tr>
          	<?
          	$cntr=0;
          	while($row=mysqli_fetch_array($data))
          	{
          		$valid=1;
          		
          		if(trim($_POST['mrr_cust_zip']) !="")
          		{	//validate zip codes from stops...
          			if(trim($_POST['mrr_cust_zip'])!=trim($row['origin_zip2']) && trim($_POST['mrr_cust_zip'])!=trim($row['dest_zip2']))		$valid=0;
          		}
          		
          		if($valid==1)
          		{
               		$link1="<a href='manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a>";
               		$link2="<a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".trim($row['name_company'])."</a>";
               		      		
               		echo "
                    		<tr class='".($cntr %2==0 ? "even" : "odd")."'>
                    			<td valign='top' align='left'>".$link1."</td>
                    			<td valign='top' align='left'>".$link2."</td>
                    			<td valign='top' align='left'>".trim($row['origin_city'])."</td>
                    			<td valign='top' align='left'>".trim($row['origin_state'])."</td>
                    			<td valign='top' align='left'>".trim($row['origin_zip2'])."</td>
                    			<td valign='top' align='left'>".trim($row['dest_city'])."</td>
                    			<td valign='top' align='left'>".trim($row['dest_state'])."</td>
                    			<td valign='top' align='left'>".trim($row['dest_zip2'])."</td>
                    		</tr>
               		";
               		$cntr++;
          		}
          	}
          	?>          	
          	<tr>
     			<td valign='top' align='left' colspan='2'><b><?=$cntr ?> Loads Found.</b></td>
     			<td valign='top' align='left' colspan='6'>&nbsp;</td>
               </tr>
          	</table>
          	
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
			
	});	
	*/
</script>
<? include('footer.php') ?>