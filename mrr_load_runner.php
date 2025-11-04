<? $usetitle="Load Runner Test Page"; ?>
<? include_once('header.php') ?>
<?
echo "<h2>THIS IS A TEST PAGE TO FIND THE NEXT LOAD OF EVERY DRIVER:</h2> <br>It will soon become automated... and actually send out to GeoTab.  At the moment, it just picks the load.<br>";

$mrr_use_geotab=0;

$starting_date=date("m/d/Y",strtotime("-7 days",time()));
$ending_date=date("m/d/Y",strtotime("+7 days",time()));;

$load_id=0;
$disp_id=0;
$driver_id=0;	//476;
$truck_id=0;	//547;

if(isset($_GET['load_id']))		$load_id=(int) trim($_GET['load_id']);
if(isset($_GET['disp_id']))		$disp_id=(int) trim($_GET['disp_id']);
if(isset($_GET['driver_id']))		$driver_id=(int) trim($_GET['driver_id']);
if(isset($_GET['truck_id']))		$truck_id=(int) trim($_GET['truck_id']);

$show_completed=3;
$show_in_progress=3;

$cur_truck_id=0;
$cur_truck_last_disp=0;
$cur_truck_next_disp=0;
$cur_truck_completed=0;
$cur_truck_in_progress=0;

echo "
	<div style='border:1px solid purple; width:1400px; color:purple; margin:10px; padding:10px;'>
		The purpose of this page is to figure out which Dispatches are the next one(s) to send via GeoTab for each active truck/driver.  This can be used to automate the sending of dispatches to the drivers/trucks.
		Shows up to the last ".$show_completed." Completed dispatches and up to ".$show_in_progress." dispatches in progress in date range (".$starting_date." thru ".$ending_date.").
		<span style='color:#999999;'><i>Completed loads are in grey text in the list...</i></span>  Page will mark the first incomplete dispatch that has NOT already been sent to GeoTab...<span style='color:#cc0000;'>which shows in red text under the load</span>.
		
		<br><br>This page is also coded to work for one truck/driver/load/dispatch at a time --to be used for links or buttons, etc. by the truck/driver/load/dispatch if needed.  Examples:
		<br>- A single truck by truck ID (not the device ID) -- <a href='mrr_load_runner.php?truck_id=547'>mrr_load_runner.php?truck_id=547</a> (for Truck 002)
		<br>- A single driver by driver ID (not the user ID) -- <a href='mrr_load_runner.php?driver_id=476'>mrr_load_runner.php?driver_id=476</a> (for Driver Darrell Murphy)
		<br>- A single load by load ID (must have dispatches) -- <a href='mrr_load_runner.php?load_id=88868'>mrr_load_runner.php?load_id=88868</a> (the exact load to send dispatches from)
		<br>- A single dispatch by dispatch ID (if not sent) -- <a href='mrr_load_runner.php?disp_id=107557'>mrr_load_runner.php?disp_id=107557</a> (the exact dispatch ID)
	</div>
";

echo "<br><b>Current Settings: Truck=".$truck_id.", Driver=".$driver_id.", Load=".$load_id.", and Dispatch=".$disp_id.".</b><br><br>";

echo "<table cellpading='0' cellspacing='0' border='0' width='1400'>";
echo "
		<tr style='font-weight:bold;'>
			<td valign='top'>No.</td>
			<td valign='top'>Truck</td>
			<td valign='top'>Driver</td>
			<td valign='top'>Driver2</td>
			<td valign='top'>Load</td>
			<td valign='top'>Dispatch</td>
			<td valign='top'>PickupETA</td>			
			<td valign='top'>Origin</td>
			<td valign='top'>State</td>
			<td valign='top'>Destination</td>
			<td valign='top'>State</td>
			<td valign='top'>Complete</td>
			<td valign='top'>Stops</td>
			<td valign='top'>Done</td>	
			<td valign='top'>GeoTabDispID</td>
			<td valign='top'>GeoTabMsgID</td>
			
			<td valign='top'>Last Disp</td>
			<td valign='top'>Next Disp</td>
			<td valign='top'>Completed</td>
			<td valign='top'>InProgress</td>		
		</tr>	
	";

$cntr=0;
$sql = "
	select trucks_log.*,
		(select trucks.name_truck from trucks where trucks.id=trucks_log.truck_id) as truck_name,
		(select CONCAT(d1.name_driver_first, ' ',d1.name_driver_last) from drivers d1 where d1.id=trucks_log.driver_id) as d1_name,
		(select CONCAT(d2.name_driver_first, ' ',d2.name_driver_last) from drivers d2 where d2.id=trucks_log.driver2_id) as d2_name,
		
		(select geotab_stop_id from load_handler_stops where load_handler_stops.trucks_log_id=trucks_log.id and load_handler_stops.deleted=0 order by load_handler_stops.linedate_pickup_eta desc limit 1) as stop_geotab_id,
		(select geotab_stop_msg_id from load_handler_stops where load_handler_stops.trucks_log_id=trucks_log.id and load_handler_stops.deleted=0 order by load_handler_stops.linedate_pickup_eta desc limit 1) as stop_geotab_msg_id,
		
		(select count(*) from load_handler_stops where load_handler_stops.trucks_log_id=trucks_log.id and load_handler_stops.deleted=0) as stop_count,
		(select count(*) from load_handler_stops where load_handler_stops.trucks_log_id=trucks_log.id and load_handler_stops.deleted=0 and load_handler_stops.linedate_completed IS NOT NULL and load_handler_stops.linedate_completed > 0) as stops_done	
	from trucks_log
		left join load_handler on load_handler.id=trucks_log.load_handler_id
	where trucks_log.deleted = 0 
  		and trucks_log.linedate_pickup_eta >= '".date("Y-m-d",strtotime($starting_date))." 00:00:00' 
		and trucks_log.linedate_pickup_eta <= '".date("Y-m-d",strtotime($ending_date))." 23:59:59' 	
		".($driver_id > 0 ? " and (trucks_log.driver_id='".$driver_id."' or trucks_log.driver2_id='".$driver_id."')" : "")."
		".($truck_id > 0 ? " and trucks_log.truck_id='".$truck_id."'" : "")."
		".($load_id > 0 ? " and trucks_log.load_handler_id='".$load_id."'" : "")."
		".($disp_id > 0 ? " and trucks_log.id='".$disp_id."'" : "")."		
		and trucks_log.truck_id > 0
		and trucks_log.driver_id > 0
		
	order by trucks_log.truck_id asc, trucks_log.linedate_pickup_eta asc
";	//and trucks_log.dispatch_completed = 0

$data= simple_query($sql);	
while($row=mysqli_fetch_array($data))
{
	if($cur_truck_id!=$row['truck_id'])
	{	//new truck, so clear the subtotals...
		$cur_truck_last_disp=0;
		$cur_truck_next_disp=0;
		$cur_truck_completed=0;
		$cur_truck_in_progress=0;
	}
	
	$geotab_disp_id=trim($row['geotab_disp_id']);			if(trim($row['stop_geotab_id'])!="")		$geotab_disp_id=trim($row['stop_geotab_id']);
	$geotab_msg_id=trim($row['geotab_msg_id']);				if(trim($row['stop_geotab_msg_id'])!="")	$geotab_msg_id=trim($row['stop_geotab_msg_id']);
	
	$showme=1;
	$completed_color="000000";
	
	if($row['dispatch_completed'] > 0)
	{
		$cur_truck_completed++;
		$cur_truck_last_disp=$row['id'];
		
		if($cur_truck_completed > $show_completed) 		{	$showme=0;	$cur_truck_completed=$show_completed;		}
		
		$completed_color="999999";
	}
	else
	{
		$cur_truck_in_progress++;
		
		if($row['stop_count'] > $row['stops_done'])			$cur_truck_last_disp=$row['id'];		//current stop is not in this load
		
		if($cur_truck_in_progress > $show_in_progress)	{	$showme=0;	$cur_truck_in_progress=$show_in_progress;	}
		
		if($geotab_disp_id=="" && $geotab_msg_id=="" && $cur_truck_next_disp==0 && $row['stops_done']==0 && $row['stop_count'] > 0)			$cur_truck_next_disp=$row['id'];
	}
	
	//if($row['stop_count']==0)	$showme=0;		
	
	if($showme > 0)
	{	
		echo "
		<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd")."; color:#".$completed_color.";'>
			<td valign='top'>".($cntr+1)."</td>
			<td valign='top'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['truck_name']."</a></td>
			<td valign='top'><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['d1_name']."</a></td>
			<td valign='top'><a href='admin_drivers.php?id=".$row['driver2_id']."' target='_blank'>".$row['d2_name']."</a></td>
			<td valign='top'><a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank'>".$row['load_handler_id']."</a></td>
			<td valign='top'><a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
			<td valign='top'>".date("m/d/Y",strtotime($row['linedate_pickup_eta']))."</td>			
			<td valign='top'>".$row['origin']."</td>
			<td valign='top'>".$row['origin_state']."</td>
			<td valign='top'>".$row['destination']."</td>
			<td valign='top'>".$row['destination_state']."</td>
			<td valign='top'>".$row['dispatch_completed']."</td>
			<td valign='top'>".$row['stop_count']."</td>
			<td valign='top'>".$row['stops_done']."</td>	
			<td valign='top'>".$geotab_disp_id."</td>
			<td valign='top'>".$geotab_msg_id."</td>
			
			<td valign='top'>".$cur_truck_last_disp."</td>
			<td valign='top'>".($cur_truck_next_disp > 0 ? "<b>".$cur_truck_next_disp."</b>" : "")."</td>	
			<td valign='top'>".$cur_truck_completed."</td>
			<td valign='top'>".$cur_truck_in_progress."</td>		
		</tr>	
		";
		
		if($cur_truck_next_disp > 0)
		{
			$geotab_report="<center><span style='color:#cc0000;'><b>Load ".$row['load_handler_id']." (Dispatch ".$row['id'].") for Truck ".$row['truck_name']." and/or Driver ".$row['d1_name']." has NOT been sent to GeoTab, but should be.</b></span></center>";
						
			if($mrr_use_geotab > 0)
			{
				$loadid=$row['load_handler_id'];
				$dispid=$row['id'];
				$driverid=$row['driver_id'];	
				$truckid=$row['truck_id'];					
				
				$truck2id=0;
               	if($truckid > 0 || $driverid > 0)
               	{
               		if($truckid == 0)
               		{
               			$truckid=mrr_find_geotab_driver_by_id($driverid,1);
               			$truck2id=mrr_find_geotab_driver_by_id($driverid,2);	
               			
               			if($truckid == 0)		$truckid=$truck2id;
               		}
               		//should now have the truck ID...
               		if($truckid > 0)
               		{
               			$dres=mrr_send_geotab_complete_dispatch($loadid,$dispid,$truckid,1);
               			
                    		echo "
                    			<br><h3>GEOTAB section for Dispatch ".$dispid." (Load ".$loadid.") for Truck ID ".$truckid.":</h3>
                    			<br>Truck ID: ".$dres['truck_id']."
                    			<br>Truck Name: ".$dres['truck_name']."
                    			<br>Dispatch ID: ".$dres['dispatch_id']."
                    			<br>Counter: ".$dres['dispatch_cntr']."
                    			<br>Output: ".$dres['output']."
                    			<br>GeoTab Dispatch ID: ".$dres['geotab_id'].".
                    			<br>GeoTab URL:<br><div style='border:1px solid purple; padding:10px;'>".$_SESSION['geotab_route_url']."</div><br>
                    		";
                    		
               		}		
               	}				
			}
			
			echo "
				<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>	
					<td valign='top'>&nbsp;</td>
					<td valign='top' colspan='18'>".$geotab_report."</td>
					<td valign='top'>&nbsp;</td>
				</tr>	
			";			
		}
		$cntr++;
	}	
	
	$cur_truck_id=$row['truck_id'];		
}

echo "</table>";		//<br><b>Query was:</b> ".$sql."<br>


if($mrr_use_geotab > 0 && 1==2)
{
	$truck2_id=0;
	if($truck_id > 0 || $driver_id > 0)
	{
		if($truck_id == 0)
		{
			$truck_id=mrr_find_geotab_driver_by_id($driver_id,1);
			$truck2_id=mrr_find_geotab_driver_by_id($driver_id,2);	
			
			if($truck_id == 0)		$truck_id=$truck2_id;
		}
		//should now have the truck ID...
		if($truck_id > 0)
		{
			$dres=mrr_send_geotab_complete_dispatch($load_id,$disp_id,$truck_id,1);
			
     		echo "
     			<br><h3>GEOTAB section for Dispatch ".$disp_id." (Load ".$load_id.") for Truck ID ".$truck_id.":</h3>
     			<br>Truck ID: ".$dres['truck_id']."
     			<br>Truck Name: ".$dres['truck_name']."
     			<br>Dispatch ID: ".$dres['dispatch_id']."
     			<br>Counter: ".$dres['dispatch_cntr']."
     			<br>Output: ".$dres['output']."
     			<br>GeoTab Dispatch ID: ".$dres['geotab_id'].".
     			<br>GeoTab URL:<br><div style='border:1px solid purple; padding:10px;'>".$_SESSION['geotab_route_url']."</div><br>
     		";
     		/**/
		}		
	}
}

?>
<? include_once('footer.php') ?>