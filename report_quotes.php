<? include('header.php') ?>
<?

	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_users 		= true;
	$rfilter->show_driver 		= true;
	$rfilter->show_truck 		= true;
	$rfilter->show_trailer 		= true;
	$rfilter->show_quote_id 		= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
	
	if(isset($_POST['build_report'])) { 
		
		$search_date_range = '';
		if($_POST['report_quote_id'] != '') {
		} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and quotes.linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))."'
				and quotes.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))."'
			";
		}
		
		$sql = "
			select quotes.*,
				concat(users.name_first, ' ', users.name_last) as created_by_name,
				customers.name_company
			
			from quotes
				left join users on users.id = quotes.created_by_user_id
				left join customers on customers.id = quotes.customer_id
			where quotes.deleted = 0
				$search_date_range
				".($_POST['driver_id'] ? " and quotes.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
				".($_POST['truck_id'] ? " and quotes.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."
				".($_POST['trailer_id'] ? " and quotes.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
				".($_POST['customer_id'] ? " and quotes.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
				".($_POST['report_user_id'] ? " and quotes.created_by_user_id = '".sql_friendly($_POST['report_user_id'])."'" : '') ."
				".($_POST['report_quote_id'] ? " and quotes.id = '".sql_friendly($_POST['report_quote_id'])."'" : '') ."
		";
		$data = simple_query($sql);
		
		echo "
			<table class='admin_menu2 font_display_section' style='margin:0 10px;width:950px;text-align:left'>
			<tr>
				<th>ID</th>
				<th>Date</th>
				<th>Created By</th>
				<th>Customer</th>
				<th>Quote Name</th>
				<th align='right'>Miles</th>
				<th align='right'>Cost</th>
				<th align='right'>Bill</th>
				<th align='right'>Profit</th>
			</tr>
		";
		
		while($row = mysqli_fetch_array($data)) {
			echo "
				<tr onclick=\"window.location='quote.php?id=$row[id]'\" class='line_entry'>
					<td>$row[id]</td>
					<td>".date("n/j/Y", strtotime($row['linedate']))."</td>
					<td>$row[created_by_name]</td>
					<td>$row[name_company]</td>
					<td>$row[quote_name]</td>
					<td align='right'>".money_format('',$row['miles_loaded'])."</td>
					<td align='right'>$".money_format('',$row['total_cost'])."</td>
					<td align='right'>$".money_format('',$row['bill_customer'])."</td>
					<td align='right'>$".money_format('',$row['profit'])."</td>
				</tr>
			";
		}
		echo "</table>";
	}
?>
<script type='text/javascript'>
	$('.line_entry').hover(
		function() {
			$(this).addClass('over');
		},
		function() {
			$(this).removeClass('over');
		}
	);
</script>
<? include('footer.php') ?>