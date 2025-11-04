<?  
$usetitle = "User Change Log";
$use_title = "User Change Log";
?>
<? include('header.php') ?>
<?
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	
	if(!isset($_POST['report_show_loads_only']))     $_POST['report_show_loads_only']=0;

	$rfilter = new report_filter();
	$rfilter->show_users		= true;
	$rfilter->show_customer 	= true;
	$rfilter->show_driver 		= true;
	$rfilter->show_truck 		= true;
	$rfilter->show_trailer 		= true;
	$rfilter->show_load_id 		= true;
    $rfilter->show_load_only 	= true;
	$rfilter->show_dispatch_id 	= true;
	//$rfilter->show_origin	 	= true;
	//$rfilter->show_destination= true;
	//$rfilter->show_stops	 	= true;
	$rfilter->show_font_size	= true;
	$rfilter->show_filter();
	
	$mrr_table="";
	
	if(isset($_POST['build_report'])) 
	{ 		
		$search_date_range = '';
		if($_POST['dispatch_id'] != '' || $_POST['load_handler_id'] != '') {
		} else {
			// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
			$search_date_range = "
				and user_change_log.linedate_added >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
				and user_change_log.linedate_added <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
			";
		}
		
		$loads_created=$_POST['report_show_loads_only'];
		
		$where_clause="
			".$search_date_range."				
			".($_POST['load_handler_id'] ? " and user_change_log.load_id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
			".($_POST['dispatch_id'] ? " and user_change_log.dispatch_id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."				
			".($_POST['driver_id'] ? " and user_change_log.driver_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
			".($_POST['truck_id'] ? " and user_change_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."				
			".($_POST['trailer_id'] ? " and user_change_log.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."				
			".($_POST['customer_id'] ? " and user_change_log.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."	
			".($_POST['report_user_id'] ? " and user_change_log.user_id = '".sql_friendly($_POST['report_user_id'])."'" : '') ."	
		";
		
		
		$mrr_table=mrr_get_user_change_log($where_clause," order by user_change_log.linedate_added asc","",0,$loads_created);
	}	
?>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<!--
	<div style='color:purple;'>
		&nbsp;
	</div>
	-->
</div>
<div style='clear:both'></div>
<div style='padding:10px; width:1200px; border:1px solid #000000;'>
<? 
	echo $mrr_table;
?>
</div>
<br>     
<script type='text/javascript'>
	//$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
	});
	
</script>
<? include('footer.php') ?>