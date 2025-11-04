<? $usetitle="PB ShapeFile Importer"; ?>
<? include_once('header.php') ?>
<?
echo "<h2>THIS IS A TEST PAGE TO IMPORT PB SHAPEFILE ZONES.</h2><br>";

echo "Use this page to match PB Shapefiles with the existing GeoTab Zone Library.  
		-- -- -- <a href='mrr_geotab_deactivate_zones.php' target='_blank'>Disable Unused Zones</a>
		-- -- -- <a href='mrr_address_zoning.php' target='_blank'>Zone Library</a>  
		-- -- -- <a href='mrr_address_purging.php' target='_blank'>Stop Addresses</a>  	
		-- -- -- <a href='mrr_pb_shapefile_zone_importer.php'>Reload Page</a>
		";

$show_form=1;
if(isset($_GET['add_zone']))
{	
	$_GET['new_zone']=trim($_GET['new_zone']);
	$_GET['zip']=trim($_GET['zip']);				$_GET['zip']=str_replace("+"," ",$_GET['zip']);		$_GET['zip']=str_replace("%20"," ",$_GET['zip']);
	$_GET['city']=trim($_GET['city']);				$_GET['city']=str_replace("+"," ",$_GET['city']);		$_GET['city']=str_replace("%20"," ",$_GET['city']);
	$_GET['addr']=trim($_GET['addr']);				$_GET['addr']=str_replace("+"," ",$_GET['addr']);		$_GET['addr']=str_replace("%20"," ",$_GET['addr']);
	$_GET['name']=trim($_GET['name']);				$_GET['name']=str_replace("+"," ",$_GET['name']);		$_GET['name']=str_replace("%20"," ",$_GET['name']);
		
	$_GET['addr']=str_replace("_POUND_","#",$_GET['addr']);
    	$_GET['name']=str_replace("_POUND_","#",$_GET['name']);
    	
	if($_GET['new_zone']!="" && $_GET['zip']!="" && $_GET['city']!="" && $_GET['addr']!="" && $_GET['name']!="")
	{
		$sqlu = "
			insert into geotab_stop_zones
				(id,
				linedate_added,
				geotab_id_name,
				address_1,
				city,
				state,
				zip,
				conard_name,
				shapefile_imported,
				bad_zone_id)
			values
				(NULL,
				NOW(),
				'".sql_friendly($_GET['new_zone'])."',
				'".sql_friendly($_GET['addr'])."',
				'".sql_friendly($_GET['city'])."',
				'',
				'".sql_friendly($_GET['zip'])."',
				'".sql_friendly($_GET['name'])."',
				1,
				'')	
		";
		simple_query($sqlu);
		
		echo "<br>Added Zone ".$_GET['new_zone']." to Library.  Name ".$_GET['name']." and Address: ".$_GET['addr']."; ".$_GET['city'].", ".$_GET['zip'].".";	
	}
	$show_form=0;
}

if(isset($_GET['edit_zone']))
{	
	$_GET['bad_zone']=trim($_GET['bad_zone']);
	$_GET['new_zone']=trim($_GET['new_zone']);
	
	if($_GET['new_zone']!="" && $_GET['bad_zone']!="" && $_GET['new_zone']!=$_GET['bad_zone'])
	{
		$sqlu="update geotab_stop_zones set bad_zone_id=geotab_id_name where geotab_id_name='".sql_friendly($_GET['bad_zone'])."' and deleted=0 and shapefile_imported=0";	
		simple_query($sqlu);
		
		echo "<br>Updated Zone ".$_GET['bad_zone']." to use ".$_GET['new_zone']." Zone instead.";	
		
		$sqlu="update geotab_stop_zones set geotab_id_name='".sql_friendly($_GET['new_zone'])."',shapefile_imported=1 where geotab_id_name='".sql_friendly($_GET['bad_zone'])."' and deleted=0 and shapefile_imported=0";	
		simple_query($sqlu);
		
		echo " --- Updated as PB Import.";	
	}
	$show_form=0;
}


if(!isset($_POST['mrr_entries']))		$_POST['mrr_entries']="";

$tab= "<table cellspacing='0' cellpadding='0' border='0' width='1400'>";
$tab.= "<tr>
		<td valign='top'><b>Entry</b></td>		
				
		<td valign='top'><b>PB ID</b></td>
		<td valign='top'><b>PB Stop</b></td>
		<td valign='top'><b>PB City</b></td>
		<td valign='top'><b>PB State</b></td>
		<td valign='top'><b>PB ZIP</b></td>
		<td valign='top'><b>PBZone</b></td>		
		
		<td valign='top'><b>Zone</b></td>
		<td valign='top'><b>ZoneID</b></td>
		<td valign='top'><b>Comp/Stop</b></td>
		<td valign='top'><b>Address</b></td>
		<td valign='top'><b>City</b></td>
		<td valign='top'><b>State</b></td>
		<td valign='top'><b>ZIP</b></td>
		
		<td valign='top'><b>Imported</b></td>
	</tr>";
	/*
		<td valign='top'><b>PBComm</b></td>
		<td valign='top'><b>PB Grp</b></td>
	*/
$entries=0;

if(isset($_POST['preview_sheet']) || isset($_POST['update_sheet']))
{
	if(isset($_POST['update_sheet']))		$importer=1;
	
	if($_POST['mrr_entries']!="")
	{
		$all_values=trim($_POST['mrr_entries']);
		
		$all_values=str_replace(chr(9)," , ",$all_values);
		$all_values=str_replace(chr(10),"<br>",$all_values);
		$all_values=str_replace(chr(13),"",$all_values);
		
		
		$list="";
		$arr=explode("<br>",$all_values);
		foreach($arr as $lines)
		{
			$line = trim($lines);
			$parts=explode(" , ",$line);	
						
			$new_zone_name="";
			$new_zone_comm="";
			$new_zone_id="";
			$new_zone_stop="";
			$new_zone_city="";
			$new_zone_state="";
			$new_zone_zip="";
			$new_zone_group="";
			
					
			$list.="<br><br>".($entries + 1).".  ";
			foreach($parts as $key => $value)
			{
				$list.="<br>--[".$key."] = ".trim($value)."";
				
				if($key==0)		$new_zone_name="".trim($value)."";
				if($key==1)		$new_zone_comm="".trim($value)."";					
				if($key==2)		$new_zone_id=trim(str_replace("$","",trim($value)));	
				if($key==3)		$new_zone_stop=trim(str_replace("$","",trim($value)));	
				if($key==4)		$new_zone_city=trim(str_replace("$","",trim($value)));	
				if($key==5)		$new_zone_state=trim(str_replace("$","",trim($value)));	
				if($key==6)		$new_zone_group=trim(str_replace("$","",trim($value)));	
			}
			
			if(strlen($new_zone_state) > 5)		$new_zone_zip=trim(substr($new_zone_state,0,5));			else 		$new_zone_zip=trim($new_zone_state);	
			$new_zone_name=str_replace("PBShape ","",$new_zone_name);
			
			$res=mrr_find_geotab_zone_by_address_ish($new_zone_id, $new_zone_stop, $new_zone_name, $new_zone_city, $new_zone_state, $new_zone_zip);
			
			$found_level=$res['found'];						
			$my_zone_id=$res['id'];
			$my_zone_name=$res['zone'];
			$my_zone_comp=$res['comp'];
			$my_zone_addr=$res['addr'];		
			$my_zone_city=$res['city'];	
			$my_zone_state=$res['state'];	
			$my_zone_zip=$res['zip'];	
			
			$imported=$res['imported'];
			$replaces=$res['replaces'];
					
			
			//$imported=0;
    			//if($importer > 0)
    			//{	
    				
    				//$imported=1;
    			//}
    			$new_zone_name=str_replace("#","_POUND_",$new_zone_name);
    			$new_zone_stop=str_replace("#","_POUND_",$new_zone_stop);
    			
    			$linker="<a href='mrr_pb_shapefile_zone_importer.php?add_zone=1&new_zone=".$new_zone_id."&zip=".$new_zone_state."&city=".$new_zone_city."&addr=".$new_zone_name."&name=".$new_zone_stop."' target='_blank' onClick='mrr_hide_this_zone_with_id(".$entries.");'>Add</a>";
    			if($my_zone_id > 0)		$linker="<a href='mrr_pb_shapefile_zone_importer.php?edit_zone=1&bad_zone=".$my_zone_name."&new_zone=".$new_zone_id."' target='_blank' onClick='mrr_hide_this_zone_with_id(".$entries.");'>Update</a>";
    			if($imported > 0)	
    			{
    				if(trim($replaces)=="")		$replaces="GOOD/NEW";
    				$linker="<span style='color:#00cc00;'><b>".$replaces."</b></span>";
    			}
    			if(trim($new_zone_id)!="")
    			{
     			$new_zone_name=str_replace("_POUND_","#",$new_zone_name);
    				$new_zone_stop=str_replace("_POUND_","#",$new_zone_stop);
     			
     			$tab.= "
     				<tr style='background-color:#".($entries%2==0 ? "eeeeee" : "dddddd").";' class='mrr_row_".$entries."'>
     					<td valign='top'>".($entries + 1)."</td>	
     					<td valign='top'>".$new_zone_name."</td>					
     					<td valign='top'>".$new_zone_stop."</td>
     					<td valign='top'>".$new_zone_city."</td>
     					<td valign='top'>".$new_zone_state."</td>
     					<td valign='top'>".$new_zone_zip."</td>
     					<td valign='top'>".($my_zone_id > 0 ? "<b>".$new_zone_id."</b>" : "".$new_zone_id."")."</td>
     					
     					<td valign='top'>".($my_zone_id > 0 ? "=<b>".$my_zone_name."</b>" : "")."</td>
     					<td valign='top'>".($my_zone_id > 0 ? "<span style='color:purple;'>".$my_zone_id."</span>" : "")."</td>
     					
     					<td valign='top'>".$my_zone_comp."</td>
     					<td valign='top'>".$my_zone_addr."</td>
     					<td valign='top'>".$my_zone_city."</td>
     					<td valign='top'>".$my_zone_state."</td>
     					<td valign='top'>".$my_zone_zip."</td>
     					<td valign='top'>".$linker."</td>
     				</tr>
     			";
     				/*
     					<td valign='top'>".$new_zone_comm."</td>
     					<td valign='top'>".$new_zone_group."</td>
     					
     					".($imported > 0 ? "Yes" : "&nbsp;")."
     				*/
     			
     			$entries++;
			}
		}
		//echo "<tr><td valign='top' colspan='14'>LIST VIEW: ".$list."</td></tr>";	
	}
	
		
}
$tab.= "</table><br><b>".$entries."</b> Entries Found in this range.<br><br>";

if($show_form > 0)
{
	echo "<form name='mrr' action='' method='POST'>";
	echo "<textarea name='mrr_entries' rows='25' cols='190' wrap='virtual'>".trim($_POST['mrr_entries'])."</textarea><br><br>";
	echo "<input type='submit' name='preview_sheet' value='View Import Data'> ...PB Shapefiles import file should have these columns in order: <b>Zone,Comment,New ID,StopName,City,State,Group</b>.  <i>State holds the ZIP plus 4 in this, not the true state.</i>";
	//echo "<input type='submit' name='update_sheet' value='SAVE Import'>";
	echo "</form><br><br>";
	
	echo "<br><br>".$tab."<br><br>";
	
	$good_imported_cntr=0;
	$good_imported="";
	$sql="
		select distinct(geotab_id_name) 
		from geotab_stop_zones 
		where shapefile_imported=1 
			and deleted=0 
		order by geotab_id_name asc
	";
	$data= simple_query($sql);	
	while($row=mysqli_fetch_array($data))
	{
		if($good_imported_cntr > 0)		$good_imported.=", ";
		
		$good_imported.="".trim($row['geotab_id_name'])."";
		
		$good_imported_cntr++;
	}
	
	$bad_imported_cntr=0;
	$bad_imported="";
	$sql="
		select distinct(bad_zone_id) 
		from geotab_stop_zones 
		where shapefile_imported=1 
			and deleted=0 
			and bad_zone_id!='' 
		order by bad_zone_id asc
	";
	$data= simple_query($sql);	
	while($row=mysqli_fetch_array($data))
	{
		if($bad_imported_cntr > 0)		$bad_imported.=", ";
		
		$bad_imported.="".trim($row['bad_zone_id'])."";
		
		$bad_imported_cntr++;
	}
	
	$bad2_imported_cntr=0;
	$bad2_imported="";
	$sql="
		select distinct(geotab_id_name) 
		from geotab_stop_zones 
		where deleted > 0
			and geotab_id_name!='' 
		order by geotab_id_name asc
	";
	$data= simple_query($sql);	
	while($row=mysqli_fetch_array($data))
	{
		if($bad2_imported_cntr > 0)		$bad2_imported.=", ";
		
		$bad2_imported.="".trim($row['geotab_id_name'])."";
		
		$bad2_imported_cntr++;
	}
	
	$not_imported_cntr=0;
	$not_imported="";
	$not_imported_details="";
	$sql="
		select * 
		from geotab_stop_zones 
		where shapefile_imported=0 
			and deleted=0 
			and bad_zone_id=''
			and geotab_id_name!='' 
		order by bad_zone_id asc
	";
	$data= simple_query($sql);	
	while($row=mysqli_fetch_array($data))
	{
		if($not_imported_cntr > 0)		$not_imported.=", ";
		
		$not_imported.="".trim($row['geotab_id_name'])."";
		$not_imported_details.="".trim($row['geotab_id_name'])."  -- ".trim($row['conard_name'])." -- ".trim($row['address_1'])."; ".trim($row['city']).", ".trim($row['state'])." ".trim($row['zip'])."\n";
		
		$not_imported_cntr++;
	}
	
	echo "<table cellspacing='0' cellpadding='0' border='0' width='1400'>";
	echo "<tr>";
	echo 	"<td valign='top'>";
	
	echo 		"<div style='width:300px; padding:10px; border:1px solid purple;'>
					<span style='color:purple;'><b>Zone IDs Replaced by PB Shapefiles (".$bad_imported_cntr."):</b></span><br><br>
					<textarea name='bad_entries' rows='50' cols='30' wrap='virtual'>".trim($bad_imported)."</textarea>
				</div>";
	
	echo 	"</td>";
	echo 	"<td valign='top'>";
	
	echo 		"<div style='width:300px; padding:10px; border:1px solid orange;'>
					<span style='color:orange;'><b>Not Replaced by PB Shapefiles (".$not_imported_cntr."):</b></span><br><br>
					<textarea name='not_entries' rows='50' cols='30' wrap='virtual'>".trim($not_imported)."</textarea>
				</div>";
	
	echo 	"</td>";
	echo 	"<td valign='top'>";
	
	echo 		"<div style='width:900px; padding:10px; border:1px solid orange;'>
					<span style='color:orange;'><b>Not Replaced Details (".$not_imported_cntr."):</b></span><br><br>
					<textarea name='detailed_entries' rows='50' cols='100' wrap='virtual'>".$not_imported_details."</textarea>
					<br>
					
				</div>";
	
	echo 	"</td>";
	echo 	"<td valign='top'>";
	
	echo 		"<div style='width:300px; padding:10px; border:1px solid #00CC00;'>
					<span style='color:#00CC00;'><b>PB Shapefile Zones (".$good_imported_cntr."):</b></span><br><br>
					<textarea name='good_entries' rows='50' cols='30' wrap='virtual'>".trim($good_imported)."</textarea>
				</div>";
	
	echo 	"</td>";
	echo "</tr>";
	echo "<tr>";
	echo 	"<td valign='top' colspan='4'>";
	echo 		"<div style='width:1400px; padding:10px; border:1px solid #00CC00;'>
					<span style='color:#00CC00;'><b>Deleted Zones (".$bad2_imported_cntr."):</b></span><br><br>
					<textarea name='bad2_entries' rows='3' cols='170' wrap='virtual'>".trim($bad2_imported)."</textarea>
				</div>";
	echo 	"</td>";
	echo "</tr>";
	echo "</table>";
}

function mrr_find_geotab_zone_by_address_ish($pb_id, $pb_stop, $pb_address, $pb_city, $pb_state, $pb_zip)
{
	$res['found']=0;
	$res['id']=0;
	$res['zone']="";
	$res['comp']="";
	$res['addr']="";
	$res['city']="";
	$res['state']="";
	$res['zip']="";
	$res['imported']=0;
	$res['replaces']=0;
	
	$found=0;
	/*
	if($found==0)
	{	//first see if the exact zone name is already in the list.
		$sql = "
			select *
			from geotab_stop_zones
			where deleted = 0 
				and geotab_id_name = '".sql_friendly($pb_name)."' 	
			order by id asc	
		";
		$data= simple_query($sql);	
		if($row=mysqli_fetch_array($data))	
		{
			$res['id']=$row['id'];
			$res['zone']=trim($row['geotab_id_name']);
			$res['comp']=trim($row['conard_name']);
			$res['addr']=trim($row['address_1']);
			$res['city']=trim($row['city']);
			$res['state']=trim($row['state']);
			$res['zip']=trim($row['zip']);
			$res['imported']=$row['shapefile_imported'];
			$res['replaces']=$row['bad_zone_id'];
			$found=1;
		}
	}
	*/
	if($found==0)
	{	//now see if the zone is there by full address... minus the state  (State is the Zip + 4 in the PB file).
		$sql = "
			select *
			from geotab_stop_zones
			where deleted = 0 
  				and address_1 like '".sql_friendly($pb_address)."' 
				and city like '".sql_friendly($pb_city)."' 
				and zip='".sql_friendly($pb_state)."' 
				and conard_name like '".sql_friendly($pb_stop)."' 	
			order by id asc	
		";
		$data= simple_query($sql);	
		if($row=mysqli_fetch_array($data))	
		{
			$res['id']=$row['id'];
			$res['zone']=trim($row['geotab_id_name']);
			$res['comp']=trim($row['conard_name']);
			$res['addr']=trim($row['address_1']);
			$res['city']=trim($row['city']);
			$res['state']=trim($row['state']);
			$res['zip']=trim($row['zip']);
			$res['imported']=$row['shapefile_imported'];
			$res['replaces']=$row['bad_zone_id'];
			$found=2;
		}
	}
	if($found==0)
	{	//now see if the zone is there by full address... minus the state  (State is the Zip + 4 in the PB file).  This time, only look for the 5 digit Zip code.
		$sql = "
			select *
			from geotab_stop_zones
			where deleted = 0 
  				and address_1 like '".sql_friendly($pb_address)."' 
				and city like '".sql_friendly($pb_city)."' 
				and zip='".sql_friendly($pb_zip)."' 
				and conard_name like '".sql_friendly($pb_stop)."' 	
			order by id asc	
		";
		$data= simple_query($sql);	
		if($row=mysqli_fetch_array($data))	
		{
			$res['id']=$row['id'];
			$res['zone']=trim($row['geotab_id_name']);
			$res['comp']=trim($row['conard_name']);
			$res['addr']=trim($row['address_1']);
			$res['city']=trim($row['city']);
			$res['state']=trim($row['state']);
			$res['zip']=trim($row['zip']);
			$res['imported']=$row['shapefile_imported'];
			$res['replaces']=$row['bad_zone_id'];
			$found=3;
		}
	}
	if($found==0)
	{	//Still not found... try to match by address and zip 
		$sql = "
			select *
			from geotab_stop_zones
			where deleted = 0 
  				and address_1 like '".sql_friendly($pb_address)."' 
				and zip='".sql_friendly($pb_zip)."' 
			order by id asc	
		";
		$data= simple_query($sql);	
		if($row=mysqli_fetch_array($data))	
		{
			$res['id']=$row['id'];
			$res['zone']=trim($row['geotab_id_name']);
			$res['comp']=trim($row['conard_name']);
			$res['addr']=trim($row['address_1']);
			$res['city']=trim($row['city']);
			$res['state']=trim($row['state']);
			$res['zip']=trim($row['zip']);
			$res['imported']=$row['shapefile_imported'];
			$res['replaces']=$row['bad_zone_id'];
			$found=4;
		}
	}
	if($found==0)
	{	//Still not found... try to match by address and city
		$sql = "
			select *
			from geotab_stop_zones
			where deleted = 0 
  				and address_1 like '".sql_friendly($pb_address)."' 
				and city like '".sql_friendly($pb_city)."' 
			order by id asc	
		";
		$data= simple_query($sql);	
		if($row=mysqli_fetch_array($data))	
		{
			$res['id']=$row['id'];
			$res['zone']=trim($row['geotab_id_name']);
			$res['comp']=trim($row['conard_name']);
			$res['addr']=trim($row['address_1']);
			$res['city']=trim($row['city']);
			$res['state']=trim($row['state']);
			$res['zip']=trim($row['zip']);
			$res['imported']=$row['shapefile_imported'];
			$res['replaces']=$row['bad_zone_id'];
			$found=5;
		}
	}
	$res['found']=$found;
		
	return $res;
}
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
	*/
	
	function mrr_hide_this_zone_with_id(id)
	{		
		$('.mrr_row_'+id+'').hide();			
	}
</script>
<? include_once('footer.php') ?>