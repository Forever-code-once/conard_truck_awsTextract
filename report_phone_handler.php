<? include('application.php') ?>
<? 
$usetitle = "Report - Driver Phone Actions";
$use_title = "Report - Driver Phone Actions";
?>
<? include('header.php') ?>
<?
	/*
	if(isset($_GET['date_from'])) {
		$_POST['date_from'] = $_GET['date_from'];
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['date_to'])) {
		$_POST['date_to'] = $_GET['date_to'];
		$_POST['build_report'] = 1;
	}
	
	if(!isset($_POST['date_from']))	$_POST['date_from']=date("m/01/Y");
	if(!isset($_POST['date_to']))		$_POST['date_to']=date("m/d/Y");
	*/
?>
<form method='post' action=''>
     <table class='admin_menu2 font_display_section' style='margin:0 10px;width:1800px;text-align:left'>
     	<tr>
     		<td colspan='18'>
     			<center>
     			<span class='section_heading'><?= $use_title ?></span>
     			</center>
     		</td>
     	</tr>
     	<tr>
     		<td colspan='18'>		
     			<?
               		$rfilter = new report_filter();
               		$rfilter->show_date_range 		= true;
               		$rfilter->show_driver 			= true;
               		$rfilter->show_truck			= true;
               		$rfilter->show_trailer 			= true;
               		$rfilter->show_customer			= true;	
               		$rfilter->show_load_id 			= true;	
               		$rfilter->show_dispatch_id 		= true;
               		$rfilter->show_font_size			= true;	
               		$rfilter->show_filter();
                     ?>     			
     		</td>
     	</tr>     	
     	<tr>
     		<td colspan='18'>
     			<hr>
     		</td>
     	</tr>
		<tr>
     		<td colspan='18'>
     			<b>Show: </b>
     			<span class='mrr_link_like_on' onClick='mrr_show_details("",0);'><b>All</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("verify_number",0);'><b>Validating Location</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("verify_number",1);'><b>Invalid Number</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("verify_action",0);'><b>Arrived...</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("verify_action",1);'><b>Getting Minutes Late</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("verify_action",2);'><b>Request Callback</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("verify_trailer",0);'><b>Getting Trailer to Drop</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("verify_trailer",1);'><b>Getting Trailer to Switch</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("verify_trailer",2);'><b>Stop Completed</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("trailer_drop",0);'><b>Dropped Trailer</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("trailer_switch_drop",0);'><b>Drop and Switch</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("trailer_switch",0);'><b>Switched Trailers</b></span> ... 
     			<span class='mrr_link_like_on' onClick='mrr_show_details("late_minutes",0);'><b>Running Late</b></span>
     		</td>
     	</tr>
		<tr>
			<td valign='top'>ID</td>
			<td valign='top'>Date</td>
			<td valign='top'>Driver</td>
			<td valign='top'>Phone</td>
			<td valign='top'>CallForm</td>
			<td valign='top'>Response</td>               				
			<td valign='top'>Truck</td>
			<td valign='top'>Trailer</td>
			<td valign='top'>Customer</td>
			<td valign='top'>LoadID</td>
			<td valign='top'>DispID</td>
			<td valign='top'>StopID</td>
			<td valign='top'>City</td>
			<td valign='top'>State</td>
			<td valign='top'>Last Location</td>
			<td valign='top'>GPS</td>
			<td valign='top'>MPH</td>
			<td valign='top'>Heading</td>
			<!--<td valign='top'>Subject</td>-->
			<!--<td valign='top'>Message</td>-->
		</tr>
     						
     	<?
     		if(isset($_POST['build_report']))
     		{
               	$adder="";
               	if(trim($_POST['date_from'])!="")	$adder.="and twilio_call_log.linedate_added>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'";
               	if(trim($_POST['date_to'])!="") 	$adder.="and twilio_call_log.linedate_added<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'";
               	
               	if($_POST['driver_id'] > 0) 		$adder.="and twilio_call_log.driver_id='".sql_friendly($_POST['driver_id'])."'";
               	if($_POST['truck_id'] > 0) 		$adder.="and twilio_call_log.truck_id='".sql_friendly($_POST['truck_id'])."'";
               	if($_POST['trailer_id'] > 0) 		$adder.="and twilio_call_log.trailer_id='".sql_friendly($_POST['trailer_id'])."'";
               	
               	if($_POST['customer_id'] > 0) 	$adder.="and twilio_call_log.customer_id='".sql_friendly($_POST['customer_id'])."'";
               	if($_POST['load_handler_id'] > 0) 	$adder.="and twilio_call_log.load_id='".sql_friendly($_POST['load_handler_id'])."'";
               	if($_POST['dispatch_id'] > 0) 	$adder.="and twilio_call_log.disp_id='".sql_friendly($_POST['dispatch_id'])."'";
               	//if($_POST['stop_id'] > 0) 		$adder.="and twilio_call_log.stop_id='".sql_friendly($_POST['stop_id'])."'";
               	
               	
               	$cntr=0;
               	$sql = "
               		select twilio_call_log.*,
               			customers.name_company,
               			drivers.name_driver_first,
               			drivers.name_driver_last,   
               			trailers.trailer_name,
               			trucks.name_truck            		
               		from ".mrr_find_log_database_name()."twilio_call_log
               			left join customers on customers.id=twilio_call_log.customer_id
               			left join trucks on trucks.id=twilio_call_log.truck_id
               			left join trailers on trailers.id=twilio_call_log.trailer_id
               			left join drivers on drivers.id=twilio_call_log.driver_id
               		where twilio_call_log.deleted = 0
               			 ".$adder."
               		order by twilio_call_log.linedate_added desc
               	";
               	
               	$data = simple_query($sql);
               	while($row = mysqli_fetch_array($data))
               	{               		
               		$cd=0;
               		if($row['response']=="Invalid Number" || $row['response']=="Getting Minutes Late" || $row['response']=="Getting Trailer to Switch")	
               			$cd=1;
               		if($row['response']=="Request Callback" || $row['response']=="Stop Completed")						
               			$cd=2;
               		
               		echo "
               			<tr class='".( $cntr%2==0 ? 'even' : 'odd')." ".$row['cmd']." code_".$cd." all_rows'>
               				<td valign='top'>".$row['id']."</td>
               				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
               				<td valign='top'><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['name_driver_first']." ".$row['name_driver_last']."</a></td>
               				<td valign='top'>".$row['phone_from']."</td>
               				<td valign='top'>".$row['text_code']."</td>
               				<td valign='top'>".$row['response']."</td>               				
               				<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a></td>
               				<td valign='top'><a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailer_name']."</a></td>
               				<td valign='top'><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['name_company']."</a></td>
               				<td valign='top'><a href='manage_load.php?load_id=".$row['load_id']."' target='_blank'>".$row['load_id']."</a></td>
               				<td valign='top'><a href='add_entry_truck.php?id=".$row['disp_id']."' target='_blank'>".$row['disp_id']."</a></td>
               				<td valign='top'>".$row['stop_id']."</td>
               				<td valign='top'>".$row['city']."</td>
               				<td valign='top'>".$row['state']."</td>
               				<td valign='top'>".$row['location']."</td>
               				<td valign='top'>".$row['latitude'].",".$row['longitude']."</td>
               				<td valign='top'>".$row['truck_speed']."</td>
               				<td valign='top'>".$row['truck_heading']."</td>
               				<!--<td valign='top'>".$row['subject']."</td>-->
               				<!--<td valign='top'>".$row['message']."</td>-->
               			</tr>
               		";              		
               		$cntr++;	
               	}               		
     		}
     	?>     	
     </table>	
</form>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
	$().ready(function() {
		mrr_show_details("",0);
	});	
	
	function mrr_show_details(sect,id)
	{
		if(sect=="")
		{
			$('.all_rows').show();
		}
		else
		{
			$('.all_rows').hide();
			$('.'+sect+'').show();
			if(id==0)
			{
				$('.code_1').hide();
				$('.code_2').hide();
			}
			if(id==1)
			{
				$('.code_0').hide();
				$('.code_2').hide();
			}
			if(id==2)
			{
				$('.code_0').hide();
				$('.code_1').hide();
			}
		}
	}
</script>
<? include('footer.php') ?>