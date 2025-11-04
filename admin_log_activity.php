<? include('header.php') ?>
<? $admin_page = 1 ?>
<?
	$use_title = "Log Activity";
	$usetitle = "Log Activity";

	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	if(isset($_GET['user_id'])) {
		$_POST['user_id'] = $_GET['user_id'];
		$_POST['build_report'] = 1;
	}
	if(!isset($_POST['report_activity_log']))		$_POST['report_activity_log']=0;
	if(!isset($_POST['date_from'])) 				$_POST['date_from'] = date("n/j/Y", time());
	if(!isset($_POST['date_to'])) 				$_POST['date_to'] = date("n/j/Y", time());

	$rfilter = new report_filter();
	$rfilter->show_driver 			= true;
	$rfilter->show_users			= true;
	$rfilter->show_truck 			= true;
	$rfilter->show_trailer 			= true;
	$rfilter->show_load_id 			= true;
	$rfilter->show_font_size			= true;
	$rfilter->log_activity_report_mode = true;
	$rfilter->show_filter();
	
 	if(isset($_POST['build_report'])) 
 	{ 
			
		$username="";
		$drivername="";
		$output="";
		
		$date_from=date("Y-m-d");
		$date_to=date("Y-m-d");
		$user_id=0;
		$driver_id=0;
		$truck_id=0;
		$trailer_id=0;
		$load_id=0;
		$dispatch_id=0;
		$stop_id=0;
		$flag=0;
		$deleted=0;
		
		if(isset($_POST['truck_id']))			$truck_id=$_POST['truck_id'];
		if(isset($_POST['trailer_id']))		$trailer_id=$_POST['trailer_id'];
		if(isset($_POST['load_handler_id']))	$load_id=(int) $_POST['load_handler_id'];
		if(isset($_POST['trailer_id']))		$dispatch_id=(int) $_POST['dispatch_id'];
		
		if(isset($_POST['driver_id']))
		{
			$sql = "
				select *				
				from drivers
				where deleted = 0 and id='".sql_friendly($_POST['driver_id'])."'
			";
			$data = simple_query($sql);
			if($row=mysqli_fetch_array($data))
			{
				$drivername="".$row['name_driver_first']." ".$row['name_driver_last']."";	
			}
			$driver_id=$_POST['driver_id'];
		}
		if(isset($_POST['report_user_id']))
		{
			/* get the user list */
			$sql = "
				select *				
				from users
				where deleted = 0 and id='".sql_friendly($_POST['report_user_id'])."'
			";
			$data = simple_query($sql);
			if($row=mysqli_fetch_array($data))
			{
				$username="".$row['name_first']." ".$row['name_last']."";	
			}
			$user_id=$_POST['report_user_id'];
		}
		$date_from=date("Y-m-d", strtotime($_POST['date_from']));
		$date_to=date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])));		
					
		$display_date_range = "".date("m/d/Y", strtotime($_POST['date_from']))." thru ".date("m/d/Y", strtotime($_POST['date_to']))."";
		
		//run all report modes...find in functions.php
		
		$output=mrr_log_search_user_loads($date_from,$date_to,$user_id,$driver_id,$_POST['report_activity_log'],$truck_id,$trailer_id,$load_id,$dispatch_id,$stop_id,$flag,$deleted);
		
		$mrr_activity_log_notes.="".$use_title.": ".mrr_decode_activity_report_modes($_POST['report_activity_log'])."";
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1500px;text-align:left'>
	<tr>
		<td colspan='4'>
			<center>
			<span class='section_heading'>Log Activity</span>
			</center>
		</td>
	</tr>
	<tr>
		<td valign='top'><b>Report Mode</b></td>
		<td valign='top'><?= mrr_decode_activity_report_modes($_POST['report_activity_log']) ?></td>
		<td valign='top'><b>Date Range</b></td>
		<td valign='top'><?= $display_date_range ?></td>
	</tr>
	<tr>
		<td valign='top'><b>User Name</b></td>
		<td valign='top'><?= $username ?></td>
		<td valign='top'><b>Driver Name</b></td>
		<td valign='top'><?= $drivername ?></td>
	</tr>
	<tr>
		<td colspan='4'><hr></td>
	</tr>
	<tr>
		<td colspan='4'><?= $output ?></td>
	</tr>
	</table>
     <? 
     }          
     ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
</script>
<? include('footer.php') ?>