<?
$usetitle="GeoTab Diagnostics"; 

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
		if(date("m/d/Y",strtotime($_POST['date_from'])) <= "05/01/2018")		{	$_POST['date_from']="05/01/2018";		$early_notice="<b>Report not available before 05/01/2018.</b>";	}
		if(date("m/d/Y",strtotime($_POST['date_to'])) <= "05/01/2018")		{	$_POST['date_to']=date("m/d/Y");		$early_notice="<b>Report not available before 05/01/2018.</b>"; 	}
		     	    	
     	$rfilter = new report_filter();
     	//$rfilter->show_driver 			= true;
		$rfilter->show_truck 			= true;
		$rfilter->mrr_geotab_diagnostic	= true;
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
	$moder[0]="";												$labeler[0]="Value";
	$moder[1]="DiagnosticOdometerAdjustmentId";						$labeler[1]="Odometer Reading";						//	474192100
	$moder[2]="DiagnosticOdometerId";								$labeler[2]="Odometer";								//	474192100
	$moder[3]="DiagnosticRawOdometerId";							$labeler[3]="Raw Odometer";							//	474192100	
	$moder[4]="DiagnosticAccelerationForwardBrakingId";				$labeler[4]="Acceleration Forward Braking";				//	0
	$moder[5]="DiagnosticAccelerationSideToSideId";					$labeler[5]="Acceleration Side To Side";				//	0
	$moder[6]="DiagnosticAccelerationUpDownId";						$labeler[6]="Acceleration Up Down";					//	10.189109802246
	$moder[7]="DiagnosticCrankingVoltageId";						$labeler[7]="Cranking Voltage";						//	12.823
	$moder[8]="DiagnosticCruiseControlActiveId";						$labeler[8]="Cruise Control Active";					//	1
	$moder[9]="DiagnosticDieselExhaustFluidId";						$labeler[9]="Diesel Exhaust Fluid";					//	66.8	
	$moder[10]="DiagnosticDeviceTotalIdleFuelId";					$labeler[10]="Device Total Idle Fuel";					//	1264.91
	$moder[11]="DiagnosticDeviceTotalFuelId";						$labeler[11]="Device Total Fuel";						//	20727.85	
	$moder[12]="DiagnosticCoolantLevelId";							$labeler[12]="Coolant Level";							//	98.025
	$moder[13]="DiagnosticEngineCoolantTemperatureId";				$labeler[13]="Engine Coolant Temperature";				//	29
	$moder[14]="DiagnosticEngineOilTemperatureID";					$labeler[14]="Engine Oil Temperature";					//	31
	$moder[15]="DiagnosticEngineHoursId";							$labeler[15]="Engine Hours";							//	36674280	
	$moder[16]="DiagnosticEngineSpeedId";							$labeler[16]="Engine Speed";							//	293	
	$moder[17]="DiagnosticEngineRoadSpeedId";						$labeler[17]="Engine Road Speed";						//	0
	$moder[18]="DiagnosticFuelLevelId";							$labeler[18]="Fuel Level";							//	57.6387	
	$moder[19]="DiagnosticGearPositionId";							$labeler[19]="Gear Position";							//	-1
	$moder[20]="DiagnosticGoDeviceVoltageId";						$labeler[20]="GoDevice Voltage";						//	14.0744438
	$moder[21]="DiagnosticHarnessDetected9PinId";					$labeler[21]="Harness Detected 9 Pin";					//	1
	$moder[22]="DiagnosticIgnitionId";								$labeler[22]="Ignition Timing";						//	1
	$moder[23]="DiagnosticJ1708EngineProtocolDetectedId";				$labeler[23]="J1708 Engine Protocol Detected";			//	1
	$moder[24]="DiagnosticJ1939CanEngineProtocolDetectedId";			$labeler[24]="J1939 Can Engine Protocol Detected";		//	1	
	$moder[25]="DiagnosticOutsideTemperatureId";						$labeler[25]="Outside Temperature";					//	21
	$moder[26]="DiagnosticParkingBrakeId";							$labeler[26]="Parking Brake";							//	0
	$moder[27]="DiagnosticPositionValidId";							$labeler[27]="Position Valid";						//	0
	$moder[28]="DiagnosticTotalFuelUsedId";							$labeler[28]="Total Fuel Used";						//	185144
	$moder[29]="DiagnosticTotalPTOHoursId";							$labeler[29]="Total PTO Hours";						//	1051920
	$moder[30]="DiagnosticTotalIdleHoursId";						$labeler[30]="Total Idle Hours";						//	13516740
	$moder[31]="DiagnosticTotalIdleFuelUsedId";						$labeler[31]="Total Idle Fuel Used";					//	14312.5
	$moder[32]="DiagnosticTotalTripIdleFuelUsedId";					$labeler[32]="Total Trip Idle Fuel Used";				//	1.39
	$moder[33]="DiagnosticTotalTripFuelUsedId";						$labeler[33]="Total Trip Fuel Used";					//	8.98	
	$moder[34]="DiagnosticVehicleActiveId";							$labeler[34]="Vehicle Active";						//	1
	$moder[35]="DiagnosticVehicleProgrammedCruiseHighSpeedLimitId";		$labeler[35]="Vehicle Programmed Cruise High Speed Limit";	//	165
	$moder[36]="DiagnosticVehicleProgrammedMaximumRoadspeedLimitId";		$labeler[36]="Vehicle Programmed Maximum Road Speed Limit";	//	191
	
	$def_id=(int) $_POST['mrr_report_geotab_diagnostic_mode'];
		
	$rep_mode="";
	echo "<h4><b>Report Mode: ".($def_id > 0 ? $labeler[ $def_id ] : "All Diagnostics Available")."</b></h4>";
	?>
</div>
<div style='clear:both'></div>
<div style='clear:both'></div>
<?
	if(isset($_POST['build_report']))
	{
		$mph=1.6;		//MPH = KPH / 1.6  (1.6 Kilometers per Mile)
		
		$sql="
			select geotab_odometer_diagnostics.*,
				trucks.name_truck				
				
			from ".mrr_find_log_database_name()."geotab_odometer_diagnostics
				left join trucks on trucks.geotab_device_id=geotab_odometer_diagnostics.device_id
				
			where geotab_odometer_diagnostics.linedate_added>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'				
				and geotab_odometer_diagnostics.linedate_added<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
				".($def_id > 0 ? "and geotab_odometer_diagnostics.diagnostic_mode='".sql_friendly($def_id)."'" : "")."
				".($_POST['truck_id'] > 0 ? "and trucks.id='".sql_friendly($_POST['truck_id'])."'" : "")."
			order by geotab_odometer_diagnostics.linedate_added asc
		";		//
		
		//echo "<br>Query: ".$sql."<br>";
		$data=simple_query($sql);	
		
		$rep_mode.="<table class='font_display_section tablesorter' style='margin:0 10px;width:1600px;text-align:left'>";
		$rep_mode.="
			<thead>
			<tr>
				<th>ID</th>
				<th>Added</th>
				<th>GeoTabID</th>
				<th>Device</th>
				<th>Truck</th>
				
				<th>Type</th>
				<th>Date</th>
				<th>Value</th>
				<th>Odometer</th>
			</tr>
			</thead>	
			<tbody>
		";
		
		$cntr=0;	
		while($row = mysqli_fetch_array($data)) 
		{
			//(int) $row['cnt'];
			
			$disp_truck=trim($row['name_truck']);
			if(trim($disp_truck)=="")		$disp_truck=mrr_find_geotab_truck_by_id(trim($row['device_id']),1);
			
			$dfrom=mrr_geotab_datestring_display_alt($row['date_time']);
				
			//	<td valign='top' align='left'>".$labeler[ (int) $row['diagnostic_mode'] ]."</td>
			
			$val=trim($row['non_odo_data']);
			
				
			$rep_mode.="
				<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
					<td valign='top' align='left'>".trim($row['id'])."</td>
					<td valign='top' align='left'>".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."</td>
					<td valign='top' align='left'>".trim($row['geotab_id'])."</td>
					<td valign='top' align='left'>".trim($row['device_id'])."</td>
					<td valign='top' align='left'>".$disp_truck."</td>
					
					<td valign='top' align='left'>".trim($row['diagnostic_name'])."</td>
					<td valign='top' align='right'>".$dfrom."</td>					
					<td valign='top' align='right'>".$val."</td>
					<td valign='top' align='right'>".trim($row['odometer'])."</td>					
				</tr>				
			";	
			$cntr++;	
		}		
		
		$rep_mode.="</tbody></table><br><b>".$cntr."</b> records found for ".$labeler[ $def_id ].".";
		
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