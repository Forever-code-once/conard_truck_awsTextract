<? include('header.php') ?>
<?

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}


	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_driver 		= true;
	$rfilter->show_shipper		= true;
	$rfilter->show_consignee		= true;
	$rfilter->show_truck 		= true;
	$rfilter->show_trailer 		= true;
	$rfilter->show_load_id 		= true;
	$rfilter->show_only_invoiced	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

 	if(isset($_POST['build_report'])) { 
 	}
?>
 		
<? include('header.php') ?>