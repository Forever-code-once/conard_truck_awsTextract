<? $usetitle="Address Purging List"; ?>
<? include_once('header.php') ?>
<?
//Address Purging List...  This does not really remove any addresses, but instead sets them to match.  The Utility will update one set of stop settings with the other
$starting_date="2018-01-01";
$ending_date="2019-12-31";

$cntr=0;
$last_entry="";

if(!isset($_GET['sort']))	$_GET['sort']="";
$_GET['sort']=trim($_GET['sort']);	

echo "<form name='mrr_purgery' method='POST' action=''>";
echo "<h2>\"Purging\" Address Stops from ".$starting_date." to ".$ending_date.".</h2>";
echo "<p>Don't worry, the purging is more to set the addresses the same so that there is no difference.  No stops are beign removed... and no details should be lost.<br>
		<br>Be careful which way they are set so that the most complete version is the one being used and kept... removing the lesser duplicates or alternate versions. 
		-- -- -- <a href='mrr_geotab_deactivate_zones.php' target='_blank'>Disable Unused Zones</a>
		-- -- -- <a href='mrr_pb_shapefile_zone_importer.php' target='_blank'>PB Shapefile Importer</a> 
		-- -- -- <a href='mrr_address_zoning.php' target='_blank'>Zone Library</a>  
		-- -- -- <a href='mrr_address_purging.php'>Reload Page</a> -- -- -- <a href='mrr_address_purging.php?sort=addr'>By Addr</a> -- -- -- <a href='mrr_address_purging.php?sort=zip'>By Zip</a>
		</p>";
echo "<table cellpadding='0' cellspacing='0' width='1400' border='0'>";
echo "
		<tr>
			<td valign='top'><b>Name</b></td>
			<td valign='top'><b>Address</b></td>
			<td valign='top'><b>City</b></td>
			<td valign='top'><b>State</b></td>
			<td valign='top'><b>Zip</b></td>
			<td valign='top'><b>GeoTab Zone</b></td>
			<td valign='top'><b>GeoTab Stop</b></td>
			<td valign='top'><b>Stop ID<b></td>
			<td valign='top'><b>Replace Address With Stop<b></td>
		</tr>
	";
	
$sorter="ORDER BY shipper_name ASC,shipper_address1 ASC, shipper_city ASC,shipper_state ASC,shipper_zip ASC";
if($_GET['sort']=="zip")		$sorter="ORDER BY shipper_zip ASC,shipper_state ASC,shipper_city ASC,shipper_address1 ASC,shipper_name ASC ";
if($_GET['sort']=="addr")	$sorter="ORDER BY shipper_address1 ASC,shipper_city ASC,shipper_state ASC,shipper_zip ASC,shipper_name ASC ";

$sql = "
	select shipper_address1,
       shipper_city,
       shipper_state,
       shipper_zip,
       shipper_name,
       geotab_zone_id,
       geotab_stop_id,
       id
			
	from load_handler_stops
	where deleted = 0 
  		and linedate_pickup_eta >= '".$starting_date." 00:00:00' 
		and linedate_pickup_eta <= '".$ending_date." 23:59:59' 			
	".$sorter."
";
$data= simple_query($sql);	
while($row=mysqli_fetch_array($data))
{
	if($last_entry != "".$row['shipper_name']." ".$row['shipper_address1']." ".$row['shipper_city']." ".$row['shipper_state']." ".$row['shipper_zip']."")
	{
		echo "
			<tr style='background-color:#".($cntr%2==0 ? "dddddd" : "eeeeee").";' class='mrr_row_".$row['id']."'>
				<td valign='top'>'".$row['shipper_name']."'</td>
				<td valign='top'>'".$row['shipper_address1']."'</td>
				<td valign='top'>'".$row['shipper_city']."'</td>
				<td valign='top'>'".$row['shipper_state']."'</td>
				<td valign='top'>'".$row['shipper_zip']."'</td>
				<td valign='top'>'".$row['geotab_zone_id']."'</td>
				<td valign='top'>'".$row['geotab_stop_id']."'</td>
				<td valign='top'>".$row['id']."</td>
				<td valign='top'><input type='text' name='mrr_stop_purge_".$row['id']."' id='mrr_stop_purge_".$row['id']."' value=''><input type='button' value='Copy' onclick='mrr_update_this_stop_with_id(".$row['id'].");'></td>
			</tr>
		";
		$cntr++;
	}	
	$last_entry = "".$row['shipper_name']." ".$row['shipper_address1']." ".$row['shipper_city']." ".$row['shipper_state']." ".$row['shipper_zip']."";		
}
echo "</table><br><b>".$cntr."</b> UNIQUE Stops Found.";
echo "</form>";
?>
<script type='text/javascript'>
	/*
	//$().ready(function() {
     	$('.auto_load_runner').each(function() {
     		
     		loadid= get_amount($(this).val());	 
     		//mrr_reload_load_for_load(loadid);
     		mrr_reload_load_for_page(loadid);
     	});
	//});
	function mrr_reload_load_for_load(loadid)
	{	
		$.ajax({
		   type: "GET",
		   url: "manage_load.php?load_id="+loadid+"&auto_save_trigger=1",		  
		   dataType: "html",
		   cache:false,
		   success: function(data) {
		   	//
		   }
		 });
	}
	function mrr_reload_load_for_page(loadid)
	{
		var winny=window.open("manage_load.php?load_id="+loadid+"&auto_save_trigger=1","_blank");
		
		winny.onload = function() {
		  	winny.close();
		};		
	}
	*/
	
	function mrr_update_this_stop_with_id(id)
	{
		var use_id="0";
		use_id=$('#mrr_stop_purge_'+id+'').val();
		if(use_id=="")		use_id=0;
				
		use_id=parseInt(use_id);
		
		//alert('Copy Address from Stop '+use_id+' to this stop (Stop '+id+') and those with same address.');
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_address_purgery",
		   data: {
		   		"copy_id":use_id,
		   		"stop_id":id
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		$('.mrr_row_'+id+'').hide();	
		   }	
		});	
	}
</script>
<? include_once('footer.php') ?>