<?
$usetitle="GeoTab DataFeed"; 

//include_once("functions_geotab.php");
//include_once("functions_geotab_usage.php"); 
?>
<? include('header.php') ?>
<?
	if(isset($_GET['date_from']))	
	{
		$_GET['date_from']=str_replace("_","/",$_GET['date_from']);	
		$_POST['date_from']=$_GET['date_from'];
		
		$_POST['build_report']=1;
	}
	if(isset($_GET['date_to']))		
	{
		$_GET['date_to']=str_replace("_","/",$_GET['date_to']);
		$_POST['date_to']=$_GET['date_to'];
				
		$_POST['build_report']=1;
	}
	if(isset($_GET['driver_id']))		
	{
		$_POST['driver_id']=$_GET['driver_id'];
				
		$_POST['build_report']=1;
	}
	if(isset($_GET['truck_id']))		
	{
		$_POST['truck_id']=$_GET['truck_id'];
				
		$_POST['build_report']=1;
	}
	
?>
<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<?
		if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("m/d/Y");
		if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("m/d/Y");
		
		if($_POST['date_from']=="")		$_POST['date_from']="04/12/2018";
		if($_POST['date_to']=="")		$_POST['date_to']=date("m/d/Y");
		
		$early_notice="";
		if(date("m/d/Y",strtotime($_POST['date_from'])) <= "04/11/2018")		{	$_POST['date_from']="04/12/2018";		$early_notice="<b>Report not available before 04/12/2018.</b>";	}
		if(date("m/d/Y",strtotime($_POST['date_to'])) <= "04/11/2018")		{	$_POST['date_to']=date("m/d/Y");		$early_notice="<b>Report not available before 04/12/2018.</b>"; 	}
		     	    	
     	$rfilter = new report_filter();
     	$rfilter->show_driver 			= true;
		$rfilter->show_truck 			= true;
		$rfilter->mrr_geotab_log_mode		= true;
     	//$rfilter->summary_only	 		= true;
     	//$rfilter->team_choice	 		= true;
     	//$rfilter->show_font_size			= true;
     	//$rfilter->mrr_special_print_button	= true;
     	$rfilter->show_filter();
     	
     ?>
	<h3><?= $usetitle ?>
	<!----
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Show:
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(0);'>All</span>  				
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(1);'>Good Driving Only</span>    
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class='mrr_link_like_on' onClick='mrr_show_by_code(2);'>Violations Only</span>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	----->
	<?= $_GET['date_from'] ?>   
	<?= $_GET['date_to'] ?>   
	<?= $early_notice ?>
	</h3>
	<br>
	<?
	$mode_arr[0]="";
	$mode_arr[1]="LogRecord";
	$mode_arr[2]="StatusData";
	$mode_arr[3]="FaultData";
	$mode_arr[4]="Trip";
	$mode_arr[5]="ExceptionEvent";
	$mode_arr[6]="DutyStatusLog";
	$mode_arr[7]="AnnotationLog";
	$mode_arr[8]="DVIRLog";
	$mode_arr[9]="ShipmentLog";
	$mode_arr[10]="TrailerAttachment";
	$mode_arr[11]="IoxAddOn";
	$mode_arr[12]="CustomData";
	
	$def_id=$_POST['mrr_report_geotab_log_mode'];
		
	$rep_mode="";
	echo "<h4><b>Report Mode: ".$mode_arr[ $def_id ]."</b></h4>";
	
	
	?>
</div>
<div style='clear:both'></div>
<div style='clear:both'></div>
<?
	if(isset($_POST['build_report']))
	{
		$driver_included=0;
		if($def_id >=4 && $def_id <= 8)		$driver_included=1;
		
		$sql="
			select geotab_datafeed_log.*,
				".($driver_included > 0 ? "drivers.name_driver_first,drivers.name_driver_last," : "")."
				trucks.name_truck				
				
			from ".mrr_find_log_database_name()."geotab_datafeed_log
				left join trucks on trucks.geotab_device_id=geotab_datafeed_log.device_id
				".($driver_included > 0 ? "left join drivers on drivers.geotab_use_id=geotab_datafeed_log.geotab_user" : "")."
				
			where geotab_datafeed_log.feed_type='".sql_friendly($def_id)."'
				and geotab_datafeed_log.linedate_added>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'
				and geotab_datafeed_log.linedate_added<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
				".($_POST['truck_id'] > 0 ? "and trucks.id='".sql_friendly($_POST['truck_id'])."'" : "")."
			order by geotab_datafeed_log.linedate_added asc
		";		//
		$data=simple_query($sql);	
		
		$rep_mode.="<table class='font_display_section tablesorter' style='margin:0 10px;width:1600px;text-align:left'>";
		$rep_mode.="
			<thead>
			<tr>
				<th>ID</th>
				<th>Added</th>
				<th>GeoTabID</th>
		";
		if($def_id!=7)	
		{
			$rep_mode.="					
				<th>Device</th>
				<th>Truck</th>
			";	
		}
			
			
		
		if($def_id==1)
		{
			$rep_mode.="
				<th>Longitude</th>
				<th>Latitude</th>
				<th align='right' nowrap>Date From</th>
				<th align='right'>MPH</th>
			";
		}
		if($def_id==2)
		{
			$rep_mode.="
				<th>Data</th>
				<th>Diagnostic</th>
				<th align='right' nowrap>Date From</th>
				<th align='right'>Version</th>
			";
		}
		if($def_id==3)
		{			
			$rep_mode.="
				<th>Diagnostic</th>
				<th>Controller</th>
				<th>FailuerMode</th>
				<th>FaultState</th>
				<th>AmberLight</th>
				<th>Malfunction</th>
				<th>Warning</th>
				<th>StopLight</th>
				<th align='right' nowrap>Date From</th>
				<th align='right'>Count</th>
			";
		}
		if($def_id==4)
		{			
			$rep_mode.="
				<th>GPS (Long, Lat)</th>
				<th>User</th>
				<th>Type</th>
				<th>Seatbelt</th>
				<th align='right'>Stop Duration</th>
				<th align='right'>Max MPH</th>
				<th align='right'>Avg MPH</th>
				<th align='right'>Distance</th>
				<th align='right'>Drive Duration</th>
				<th align='right'>Idle Duration</th>
				<th align='right'>Next Trip Stop</th>
				<th align='right' nowrap>Date From</th>
				<th align='right' nowrap>Date To</th>
				<th align='right'>Work</th>
				<th align='right'>Speed Ranges</th>
				<th align='right'>After Hours</th>				
			";
		}
		if($def_id==5)
		{
			$rep_mode.="
				<th>User</th>
				<th>Type</th>
				<th>Diagnostic</th>
				<th>Rule</th>
				<th align='right' nowrap>Date From</th>
				<th align='right' nowrap>Date To</th>
				<th align='right'>Version</th>
				<th align='right'>Duration</th>
			";
		}
		if($def_id==6)
		{			
			$rep_mode.="
				<th>Longitude</th>
				<th>Latitude</th>
				<th>User</th>
				<th>Type</th>
				<th>Status</th>
				<th>Origin</th>
				<th>State</th>
				<th>Sequence</th>
				<th>Malfunction</th>
				<th>Event Status</th>
				<th>Event Code</th>
				<th>Event Type</th>
				<th align='right'>LastGPS</th>
				<th align='right' nowrap>Date From</th>
				<th align='right'>Version</th>
			";
		}
		if($def_id==7)
		{
			$rep_mode.="
				<th>User</th>
				<th>Type</th>
				<th>Status</th>
				<th>Malfunction</th>
				<th>Comment</th>
				<th align='right'>LastGPS</th>
				<th align='right' nowrap>Date From</th>
				<th align='right'>Version</th>
			";
		}
		if($def_id==8)
		{
			$rep_mode.="
				<th>User</th>
				<th>Type</th>
				<th>DriverRemark</th>
				<th>DVIR Type</th>
				<th>CertifyRemark</th>
				<th align='right' nowrap>Date From</th>
				<th align='right'>Version</th>
			";			
		}
		if($def_id==9)
		{
			$rep_mode.="
				<th>Document</th>
				<th>Shipper</th>
				<th>Commodity</th>
				<th align='right' nowrap>Date From</th>
				<th align='right' nowrap>Date To</th>
				
				<th align='right'>Version</th>
			";	
		}
		if($def_id==10)
		{
			$rep_mode.="
				<th>Type</th>
				<th>GeoTabID</th>
				<th>Trailer</th>
				<th align='right' nowrap>Date From</th>
				<th align='right' nowrap>Date To</th>				
				<th align='right'>Version</th>
			";	
		}
		if($def_id==11)
		{
			//
		}
		if($def_id==12)
		{
			//
		}
		
		$rep_mode.="
			</tr>
			</thead>
			<tbody>
		";
		
		$mph=1.6;		//MPH = KPH / 1.6  (1.6 Kilometers per Mile)
		
		
		$cntr=0;	
		while($row = mysqli_fetch_array($data)) 
		{
			//(int) $row['cnt'];
			
			
			$rep_mode.="<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>";			
			$rep_mode.="
					<td valign='top' align='left'>".trim($row['id'])."</td>
					<td valign='top' align='left'>".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."</td>
					<td valign='top' align='left'>".trim($row['geotab_id'])."</td>
			";	
			if($def_id!=7)	
			{
				$disp_truck=trim($row['name_truck']);
				if(trim($disp_truck)=="")		$disp_truck=mrr_find_geotab_truck_by_id(trim($row['device_id']),1);
				
				
				$rep_mode.="					
					<td valign='top' align='left'>".trim($row['device_id'])."</td>
					<td valign='top' align='left'>".$disp_truck."</td>
				";	
			}
			
			
						
			if($def_id==1)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['date_from']);
				
				$rep_mode.="
					<td valign='top' align='left'>".trim($row['longitude'])."</td>
					<td valign='top' align='left'>".trim($row['latitude'])."</td>
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".($row['speed_mph'] / $mph)."</td>
				";	
			}
			if($def_id==2)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['date_from']);
				
				$rep_mode.="
					<td valign='top' align='left'>".trim($row['data_txt'])."</td>
					<td valign='top' align='left'>".trim($row['diagnostic_id'])."</td>
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".trim($row['version'])."</td>
				";	
			}
			if($def_id==3)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['date_from']);
								
				$rep_mode.="					
					<td valign='top' align='left'>".trim($row['diagnostic_id'])."</td>
					<td valign='top' align='left'>".trim($row['controller_id'])."</td>
					<td valign='top' align='left'>".trim($row['failuer_mode'])."</td>
					<td valign='top' align='left'>".trim($row['fault_state'])."</td>
					<td valign='top' align='left'>".($row['amber_light'] > 0 ? "<b>ON</b>" : "")."</td>
					<td valign='top' align='left'>".($row['mal_light'] > 0 ? "<b>ON</b>" : "")."</td>
					<td valign='top' align='left'>".($row['warning_light'] > 0 ? "<b>ON</b>" : "")."</td>
					<td valign='top' align='left'>".($row['stop_light'] > 0 ? "<b>ON</b>" : "")."</td>
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".trim($row['cnt'])."</td>
				";	
			}
			if($def_id==4)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['started']);
				$dto=mrr_geotab_datestring_display_alt($row['stopped']);
				
				$driver=$row['geotab_user'];
				if($driver_included > 0 && trim($row['geotab_user'])!="U")		$driver=$row['name_driver_first']." ".$row['name_driver_last'];
								               
				$rep_mode.="
					<td valign='top' align='left'>".trim($row['longitude']).", ".trim($row['latitude'])."</td>
					<td valign='top' align='left'>".trim($driver)."</td>
					<td valign='top' align='left'>".($row['is_driver'] > 0 ? "Driver" : "User")."</td>
					<td valign='top' align='left'>".($row['belt_off'] > 0 ? "<b>OFF</b>" : "on")."</td>	
					<td valign='top' align='right'>".trim(substr($row['stop_duration'],0,8))."</td>
					<td valign='top' align='right'>".($row['max_speed'] / $mph)."</td>
					<td valign='top' align='right'>".($row['avg_speed'] / $mph)."</td>
					<td valign='top' align='right'>".trim($row['distance'])."</td>
					<td valign='top' align='right'>".trim(substr($row['drive_duration'],0,8))."</td>
					<td valign='top' align='right'>".trim(substr($row['idle_duration'],0,8))."</td>
					<td valign='top' align='right'>".mrr_geotab_datestring_display_alt($row['next_trip_stop'])."</td>
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".$dto."</td>					
					<td valign='top' align='right' nowrap>
						".trim($row['work_distance'])." Distance<br>
						".trim(substr($row['work_drive_duration'],0,8))." Driving<br>
						".trim(substr($row['work_stop_duration'],0,8))." Stopped
					</td>
					<td valign='top' align='right' nowrap>
						".($row['range1'] / $mph)."MPH ".trim(substr($row['range1_duration'],0,8))."<br>
						".($row['range2'] / $mph)."MPH ".trim(substr($row['range2_duration'],0,8))."<br>
						".($row['range3'] / $mph)."MPH  ".trim(substr($row['range3_duration'],0,8))."
					</td>
					<td valign='top' align='right' nowrap>
						Started: ".($row['after_hrs_start'] > 0 ? "<b>YES</b>" : "no")."<br>
						Ended: ".($row['after_hrs_end'] > 0 ? "<b>YES</b>" : "no")."<br>
						".trim($row['after_hrs_distance'])." Distance<br>
						".trim(substr($row['after_hrs_drive_duration'],0,8))." Driving<br>
						".trim(substr($row['after_hrs_stop_duration'],0,8))." Stopped
					</td>
				";	
			}
			if($def_id==5)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['date_from']);
				$dto=mrr_geotab_datestring_display_alt($row['date_to']);
				
				$driver=$row['geotab_user'];
				if($driver_included > 0 && trim($row['geotab_user'])!="U")		$driver=$row['name_driver_first']." ".$row['name_driver_last'];
								
				$rep_mode.="
					<td valign='top' align='left'>".trim($driver)."</td>
					<td valign='top' align='left'>".($row['is_driver'] > 0 ? "Driver" : "User")."</td>
					<td valign='top' align='left'>".trim($row['diagnostic_id'])."</td>
					<td valign='top' align='left'>".trim($row['rule_id'])."</td>
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".$dto."</td>	
					<td valign='top' align='right'>".trim($row['version'])."</td>
					<td valign='top' align='right'>".trim($row['drive_duration'])."</td>
				";	
			}
			if($def_id==6)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['date_from']);
				
				$driver=$row['geotab_user'];
				if($driver_included > 0 && trim($row['geotab_user'])!="U")		$driver=$row['name_driver_first']." ".$row['name_driver_last'];
				
				$rep_mode.="
					<td valign='top' align='left'>".trim($row['longitude'])."</td>
					<td valign='top' align='left'>".trim($row['latitude'])."</td>
					<td valign='top' align='left'>".trim($driver)."</td>
					<td valign='top' align='left'>".($row['is_driver'] > 0 ? "Driver" : "User")."</td>
					<td valign='top' align='left'>".trim($row['status'])."</td>
					<td valign='top' align='left'>".trim($row['origin'])."</td>
					<td valign='top' align='left'>".trim($row['state'])."</td>
					<td valign='top' align='left'>".trim($row['sequence'])."</td>
					<td valign='top' align='left'>".trim($row['malfunction'])."</td>
					<td valign='top' align='left'>".trim($row['event_status'])."</td>
					<td valign='top' align='left'>".trim($row['event_code'])."</td>
					<td valign='top' align='left'>".trim($row['event_type'])."</td>
					<td valign='top' align='right'>".trim($row['last_gps_distance'])."</td>
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".trim($row['version'])."</td>
				";	
			}
			if($def_id==7)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['date_from']);
				
				$driver=$row['geotab_user'];
				if($driver_included > 0 && trim($row['geotab_user'])!="U")		$driver=$row['name_driver_first']." ".$row['name_driver_last'];
				                                       
				$rep_mode.="
					<td valign='top' align='left'>".trim($driver)."</td>
					<td valign='top' align='left'>".($row['is_driver'] > 0 ? "Driver" : "User")."</td>
					<td valign='top' align='left'>".trim($row['status'])."</td>
					<td valign='top' align='left'>".trim($row['malfunction'])."</td>
					<td valign='top' align='left'>".trim($row['data_txt'])."</td>
					<td valign='top' align='right'>".trim($row['last_gps_distance'])."</td>
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".trim($row['version'])."</td>
				";	
			}
			if($def_id==8)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['date_from']);
				
				$driver=$row['geotab_user'];
				if($driver_included > 0 && trim($row['geotab_user'])!="U")		$driver=$row['name_driver_first']." ".$row['name_driver_last'];
				
				$rep_mode.="
					<td valign='top' align='left'>".trim($driver)."</td>
					<td valign='top' align='left'>".($row['is_driver'] > 0 ? "Driver" : "User")."</td>
					<td valign='top' align='left'>".trim($row['data_body'])."</td>
					<td valign='top' align='left'>".trim($row['data_typer'])."</td>	
					<td valign='top' align='left'>".trim($row['data_txt'])."</td>									
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".trim($row['version'])."</td>
				";	
			}
			if($def_id==9)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['date_from']);
				$dto=mrr_geotab_datestring_display_alt($row['date_to']);
				if($row['date_to']=="2050-01-01")		$dto="N/A";
				
				$rep_mode.="
					<td valign='top' align='left'>".trim($row['data_typer'])."</td>	
					<td valign='top' align='left'>".trim($row['data_body'])."</td>
					<td valign='top' align='left'>".trim($row['data_txt'])."</td>								
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".$dto."</td>
					<td valign='top' align='right'>".trim($row['version'])."</td>
				";	
			}
			if($def_id==10)
			{
				$dfrom=mrr_geotab_datestring_display_alt($row['date_from']);
				$dto=mrr_geotab_datestring_display_alt($row['date_to']);
				if($row['date_to']=="2050-01-01")		$dto="N/A";
				
				$trailer_name=mrr_find_geotab_trailer_by_id(trim($row['data_body']),1);
				
				$rep_mode.="
					<td valign='top' align='left'>".trim($row['data_typer'])."</td>	
					<td valign='top' align='left'>".trim($row['data_body'])."</td>
					<td valign='top' align='left'>".$trailer_name."</td>								
					<td valign='top' align='right'>".$dfrom."</td>
					<td valign='top' align='right'>".$dto."</td>
					<td valign='top' align='right'>".trim($row['version'])."</td>
				";	
			}
			if($def_id==11)
			{
				
			}
			if($def_id==12)
			{
				
			}
					
			
			
			
			$rep_mode.="</tr>";
			$cntr++;
		}
		
		
		$rep_mode.="</tbody></table><br><b>".$cntr."</b> records found for ".$mode_arr[ $def_id ].".";
		
		echo $rep_mode;
	}
?>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
		//$('.hidden_signs').hide();
		
		//$('.datepicker').datepicker();
		
		//setTimeout("location.reload();", (60 * 1000));		//ten minutes...600 seconds...1000=1 second
	});
	
</script>
<? include('footer.php') ?>