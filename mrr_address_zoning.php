<? $usetitle="Zone Library Purging List"; ?>
<? include_once('header.php') ?>
<?
//Zone Library Purging List...  This does not really remove any zones, but instead sets them to match.  The Utility will update one set of zone library GeoTab ID settings with the other
$starting_date="2018-01-01";
$ending_date="2019-12-31";

$sqlu = "delete from geotab_stop_zones where geotab_id_name='0' or geotab_id_name='' or geotab_id_name='Add Zone'";
simple_query($sqlu);   

$sqlu = "update geotab_stop_zones set deleted='1' where conard_name=''";
simple_query($sqlu);  

$cntr=0;
$last_entry="";
$last_zone_id="";

if(!isset($_GET['sort']))	$_GET['sort']="";
$_GET['sort']=trim($_GET['sort']);	

echo "<form name='mrr_purgery' method='POST' action=''>";
echo "<h2>\"Purging\" Address ZONES from ".$starting_date." to ".$ending_date.".</h2>";
echo "<p>Don't worry, the purging is more to set the zones the same so that there is no difference.  No zones are being removed... and no details should be lost.<br>
		<br>Be careful which way they are set so that the most complete version is the one being used and kept... removing the lesser duplicates or alternate versions. 
		-- -- -- <a href='mrr_geotab_deactivate_zones.php' target='_blank'>Disable Unused Zones</a> 
		-- -- -- <a href='mrr_pb_shapefile_zone_importer.php' target='_blank'>PB Shapefile Importer</a> 
		-- -- -- <a href='mrr_address_purging.php' target='_blank'>Stop Addresses</a>  		  
		-- -- -- <a href='mrr_address_zoning.php'>Reload Page</a> -- -- -- <a href='mrr_address_zoning.php?sort=addr'>By Addr</a> -- -- -- <a href='mrr_address_zoning.php?sort=zip'>By Zip</a>
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
			<td valign='top'><b>ID<b></td>
			<td valign='top'><b>Replace Address With Stop<b></td>
		</tr>
	";
	
$sorter="ORDER BY conard_name ASC,address_1 ASC,city ASC,state ASC,zip ASC ";
if($_GET['sort']=="zip")		$sorter="ORDER BY zip ASC,state ASC,city ASC,address_1 ASC,conard_name ASC ";
if($_GET['sort']=="addr")	$sorter="ORDER BY address_1 ASC,city ASC,state ASC,zip ASC,conard_name ASC ";
	
$sql = "
	select address_1,
       city,
       state,
       zip,
       conard_name,
       geotab_id_name,
       id
			
	from geotab_stop_zones
	where deleted = 0 
  		and linedate_added >= '".$starting_date." 00:00:00' 
		and linedate_added <= '".$ending_date." 23:59:59' 	
	".$sorter."	
";
$data= simple_query($sql);	
while($row=mysqli_fetch_array($data))
{
	if($last_entry != "".$row['conard_name']." ".$row['address_1']." ".$row['city']." ".$row['state']." ".$row['zip']."")
	{
		echo "
			<tr style='background-color:#".($cntr%2==0 ? "dddddd" : "eeeeee").";' class='mrr_row_".$row['id']."'>
				<td valign='top'>'".$row['conard_name']."'</td>
				<td valign='top'>'".$row['address_1']."'</td>
				<td valign='top'>'".$row['city']."'</td>
				<td valign='top'>'".$row['state']."'</td>
				<td valign='top'>'".$row['zip']."'</td>
				<td valign='top'".($last_zone_id==$row['geotab_id_name'] ? " style='background-color:#00FF00;'" : "").">'".$row['geotab_id_name']."'</td>
				<td valign='top'".($last_zone_id==$row['geotab_id_name'] ? " style='background-color:#00FF00;'" : "").">".$row['id']."</td>
				<td valign='top'><input type='text' name='mrr_zone_purge_".$row['id']."' id='mrr_zone_purge_".$row['id']."' value=''><input type='button' value='Copy' onclick='mrr_update_this_zone_with_id(".$row['id'].");'></td>
			</tr>
		";
		$cntr++;
	}	
	$last_entry = "".$row['conard_name']." ".$row['address_1']." ".$row['city']." ".$row['state']." ".$row['zip']."";		
	$last_zone_id=$row['geotab_id_name'];
}
echo "</table><br><b>".$cntr."</b> UNIQUE Zones Found.";
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
	
	function mrr_update_this_zone_with_id(id)
	{
		var use_id="0";
		use_id=$('#mrr_zone_purge_'+id+'').val();
		if(use_id=="")		use_id=0;
				
		use_id=parseInt(use_id);
		
		//alert('Copy Address from Zone '+use_id+' to this zone (ZONE '+id+') and those with same address.');
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_zone_purgery",
		   data: {
		   		"copy_id":use_id,
		   		"zone_id":id
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