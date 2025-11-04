<? $usetitle="GeoTab Zone Deactivator"; ?>
<? include_once('header.php') ?>
<?
echo "<h2>THIS IS A TEST PAGE TO PURGE INACTIVE ZONES.</h2><br>Zones are only turned off for the sake of the map... not removed.<br>";	

echo "Use this page to update the GeoTab system so that all of the inactive zones are hidden from the map.  
		-- -- -- <a href='mrr_geotab_deactivate_zones.php'>Reload Page</a>
		-- -- -- <a href='mrr_address_zoning.php' target='_blank'>Zone Library</a>  
		-- -- -- <a href='mrr_address_purging.php' target='_blank'>Stop Addresses</a>  	
		-- -- -- <a href='mrr_pb_shapefile_zone_importer.php' target='_blank'>PB Shapefile Importer</a>
	<br><br>";
	
echo "<form name='mrr_purgery' method='POST' action=''>";
echo "<table cellpadding='0' cellspacing='0' width='1400' border='0'>";
echo "
		<tr>
			<td valign='top'><b>#</b></td>
			<td valign='top'><b>Name</b></td>
			<td valign='top'><b>Address</b></td>
			<td valign='top'><b>City</b></td>
			<td valign='top'><b>State</b></td>
			<td valign='top'><b>Zip</b></td>
			<td valign='top'><b>GeoTab Zone</b></td>
			<td valign='top'><b>GeoTab Stop</b></td>
			<td valign='top'><b>Active Stop<b></td>
			<td valign='top'><b>Pickup ETA<b></td>
		</tr>
	";
	
$cntr=0;	
$this_month=date("Y-m",time());
$sql = "
	select *			
	from geotab_stop_zones
	where deleted = 0
  		and shapefile_imported=0
		and id>833404	
		and geotab_id_name!=''	
	order by id desc
";
/*
		(
			select ifnull(load_handler_stops.id,0)
			from load_handler_stops
			where load_handler_stops.deleted=0
				and load_handler_stops.linedate_completed is not NULL
				and load_handler_stops.linedate_completed!='0000-00-00 00:00:00'
				and load_handler_stops.linedate_pickup_eta >='".$this_month."-01 00:00:00'
					
				and load_handler_stops.geotab_zone_id=geotab_stop_zones.geotab_id_name			
			order by load_handler_stops.id desc
			limit 1 
		) as active_stop


				and load_handler_stops.shipper_address1=geotab_stop_zones.address_1
				and load_handler_stops.shipper_city=geotab_stop_zones.city
				and load_handler_stops.shipper_state=geotab_stop_zones.state
				and load_handler_stops.shipper_zip=geotab_stop_zones.zip
*/
$data= simple_query($sql);	
while($row=mysqli_fetch_array($data))
{
	$active_stop=0;	//$row['active_stop']
	$active_pickup="";
	
	//tried asa sub query in main query.  Took 13.5 minutes to run.  Changing to sub query.
	$sql2="
		select id,linedate_pickup_eta
		from load_handler_stops
		where deleted=0
			and (linedate_completed is NULL or linedate_completed='0000-00-00 00:00:00')
			and linedate_pickup_eta >='".$this_month."-01 00:00:00'				
			and geotab_stop_id='".sql_friendly($row['geotab_id_name'])."'			
		order by linedate_pickup_eta desc, id desc
	";
	$data2= simple_query($sql2);	
	if($row2=mysqli_fetch_array($data2))
	{
		$active_stop=$row2['id'];
		$active_pickup=date("m/d/Y H:i",strtotime($row2['linedate_pickup_eta']));	
	}
	else
	{
		//remove this stop... or provide the link so that the generic shape disappears if not recently used in a while.
		$active_pickup="<a href='geotab.php?diagnotics=101&displayed=0&geotab_stop_id=".$row['geotab_id_name']."' target='_blank' onClick='mrr_remove_this_zone_with_id(".$row['id'].");'>Hide GeoTab Zone</a>";	
	}
	
	echo "
		<tr style='background-color:#".($cntr%2==0 ? "dddddd" : "eeeeee").";' class='mrr_row_".$row['id']."'>
			<td valign='top'>".($cntr+1)."</td>
			<td valign='top'>'".$row['conard_name']."'</td>
			<td valign='top'>'".$row['address_1']."'</td>
			<td valign='top'>'".$row['city']."'</td>
			<td valign='top'>'".$row['state']."'</td>
			<td valign='top'>'".$row['zip']."'</td>
			<td valign='top'>'".$row['geotab_id_name']."'</td>
			<td valign='top'>".$row['id']."</td>
			<td valign='top'>".$active_stop."</td>
			<td valign='top'>".$active_pickup."</td>
		</tr>
	";
	$cntr++;
}
echo "</table><br><b>".$cntr."</b> UNIQUE Stops Found.";
echo "</form>";
?>
<script type='text/javascript'>
	/*
	//$().ready(function() {
		
	//});
	*/
	
	function mrr_remove_this_zone_with_id(id)
	{		
		$('.mrr_row_'+id+'').hide();	
	}
</script>
<? include_once('footer.php') ?>