<?  
$usetitle = "User Validation Log";
$use_title = "User Validation Log";
?>
<? include('header.php') ?>
<?
	if(isset($_GET['user_id'])) {
		$_POST['user_id'] = $_GET['user_id'];
		$_POST['build_report'] = 1;
	}


	$rfilter = new report_filter();
	$rfilter->show_users		= true;
	//$rfilter->show_customer 		= true;
	//$rfilter->show_driver 		= true;
	//$rfilter->show_truck 		= true;
	//$rfilter->show_trailer 		= true;
	//$rfilter->show_load_id 		= true;
	//$rfilter->show_dispatch_id 	= true;
	//$rfilter->show_origin	 	= true;
	//$rfilter->show_destination 	= true;
	//$rfilter->show_stops	 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
	$mrr_table="";
	
	if(isset($_POST['build_report'])) 
	{ 		
		$search_date_range = "
			and log_user_validation.linedate_added >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
			and log_user_validation.linedate_added <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
		";
		
		$where_clause="
			".$search_date_range."				
			".($_POST['report_user_id'] ? " and log_user_validation.user_id = '".sql_friendly($_POST['report_user_id'])."'" : '') ."	
		";
		
		
		$mrr_table=mrr_get_log_user_validation($where_clause," order by log_user_validation.linedate_added asc","",0);
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