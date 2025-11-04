<? include('application.php') ?>
<? $admin_page = 1 ?>
<? include('header.php') ?>
<?
	if(!isset($_POST['zip_to_zip_trip_id']))	$_POST['zip_to_zip_trip_id']=0;
	
	if(!isset($_POST['edti_zip_1']))			$_POST['edti_zip_1']="0";
	if(!isset($_POST['edti_zip_2']))			$_POST['edti_zip_2']="0";
	if(!isset($_POST['edti_miles']))			$_POST['edti_miles']="0";
	if(!isset($_POST['edti_hours']))			$_POST['edti_hours']="00:00";
	
	if(isset($_GET['delid']))
	{
		$sql="
     		update zip_to_zip_trips set          			
     			deleted='1'          			
     		where id='".sql_friendly($_GET['delid'])."'
          ";
          simple_query($sql);	
          
          $mrr_message="Zip to Zip Trip ".$_GET['delid']." has been removed.";
          
		$_POST['zip_to_zip_trip_id']=0;	
	}
	
	$mrr_message="";
	if(isset($_POST['update_zip_to_zip']))
	{
		//$_POST['edti_zip_1']
		//$_POST['edti_zip_2']
		//$_POST['edti_miles']
		//$_POST['edti_hours']
		
		$err=0;
		if(trim($_POST['edti_zip_1'])=="" || (int)$_POST['edti_zip_1']==0)	{	$err=1;	$mrr_message.="ERROR: Zip Code 1 cannot be blank or Zero.";		}
		if(trim($_POST['edti_zip_2'])=="" || (int)$_POST['edti_zip_2']==0)	{	$err=1;	$mrr_message.="ERROR: Zip Code 2 cannot be blank or Zero.";		}
		if(!is_numeric($_POST['edti_miles']) || $_POST['edti_miles']==0)		{	$err=1;	$mrr_message.="ERROR: Miles should be numeric but not zero.";		}
		if(mrr_calc_time_into_hours($_POST['edti_hours'])==0)				{	$err=1;	$mrr_message.="ERROR: Time should be greater than zero in clock format such as '0:30' (1/2 hour), '3:30' (3.5 hours), or '14:15' (14.25 hours).";	}
				
		if($err==0)
		{
     		$_POST['edti_miles']=abs($_POST['edti_miles']);
     		
     		if($_POST['zip_to_zip_trip_id'] > 0)
     		{
     			$sql="
               		update zip_to_zip_trips set          			
               			
               			zip_code_1='".sql_friendly((int)$_POST['edti_zip_1'])."',
               			zip_code_2='".sql_friendly((int)$_POST['edti_zip_2'])."',
               			miles='".sql_friendly($_POST['edti_miles'])."',
               			timer='".sql_friendly($_POST['edti_hours'])."'
               			
               		where id='".sql_friendly($_POST['zip_to_zip_trip_id'])."'
               	";
               	simple_query($sql);	
               	
               	$mrr_message="Zip to Zip Trip ".$_POST['edti_zip_1']." to ".$_POST['edti_zip_2']." has been updated.";
     		}
     		else
     		{
     			$sql="
               		insert into zip_to_zip_trips
               			(id,
               			linedate_added,
               			zip_code_1,
               			zip_code_2,
               			miles,
               			timer,
               			deleted)
               		values 
               			(NULL,
               			NOW(),
               			'".sql_friendly((int)$_POST['edti_zip_1'])."',
               			'".sql_friendly((int)$_POST['edti_zip_2'])."',
               			'".sql_friendly($_POST['edti_miles'])."',
               			'".sql_friendly($_POST['edti_hours'])."',
               			0)	
               	";
               	simple_query($sql);	
               	
               	$mrr_message="Zip to Zip Trip ".$_POST['edti_zip_1']." to ".$_POST['edti_zip_2']." has been added.";
     		}     		
		}
	}
	
	if(isset($_GET['id']))
	{
		$_POST['zip_to_zip_trip_id']=$_GET['id'];
		$sql = "
			select zip_to_zip_trips.*		
			from zip_to_zip_trips
			where id='".sql_friendly($_POST['zip_to_zip_trip_id'])."'			
		";
		$data= simple_query($sql);	
		if($row=mysqli_fetch_array($data))
		{
			$_POST['edti_zip_1']=$row['zip_code_1'];
			$_POST['edti_zip_2']=$row['zip_code_2'];
			$_POST['edti_miles']=$row['miles'];
			$_POST['edti_hours']=$row['timer'];	
			
			$mrr_message="Viewing Zip to Zip Trip ".$_POST['edti_zip_1']." to ".$_POST['edti_zip_2'].":";
			if($_POST['edti_zip_1'] > $_POST['edti_zip_2'])
			{
				$_POST['zip_from']=$_POST['edti_zip_2'];
				$_POST['zip_to']=$_POST['edti_zip_1'];
			}
			else
			{
				$_POST['zip_from']=$_POST['edti_zip_1'];
				$_POST['zip_to']=$_POST['edti_zip_2'];	
			}
		}		
	}
	
	if(isset($_GET['zip1']))		$_POST['edti_zip_1']=trim($_GET['zip1']);	
	if(isset($_GET['zip2']))		$_POST['edti_zip_2']=trim($_GET['zip2']);	
	
	if(isset($_GET['zip1x']))	$_POST['zip_from']=trim($_GET['zip1x']);	
	if(isset($_GET['zip2x']))	$_POST['zip_to']=trim($_GET['zip2x']);	
	
	
	if(!isset($_POST['zip_from']))		$_POST['zip_from']="37000";
	if(!isset($_POST['zip_to']))			$_POST['zip_to']="38000";
	$limiter="";
	$str_filter="";
	
	if(!is_numeric($_POST['zip_from']) || trim($_POST['zip_from'])=="")		$_POST['zip_from']="00000";
	if(!is_numeric($_POST['zip_to']) || trim($_POST['zip_to'])=="")			$_POST['zip_to']="99999";
	
	if($_POST['zip_from']=="00000" || $_POST['zip_to']=="99999")			
	{
		$limiter="limit 50";
	}
	else
	{
		$str_filter="";
		//if(is_numeric($_POST['zip_from']) && $_POST['zip_from'] > 0)	$str_filter.=" and (zip_code_1>='".sql_friendly($_POST['zip_from'])."' or zip_code_2>='".sql_friendly($_POST['zip_from'])."')";
		//if(is_numeric($_POST['zip_to']) && $_POST['zip_to'] > 0)		$str_filter.=" and (zip_code_1<='".sql_friendly($_POST['zip_to'])."' or zip_code_2<='".sql_friendly($_POST['zip_to'])."')";
		
		if(is_numeric($_POST['zip_from']) && $_POST['zip_from'] > 0 && is_numeric($_POST['zip_to']) && $_POST['zip_to'] > 0)
		{
			$str_filter.=" 
				and (
					(zip_code_1>='".sql_friendly($_POST['zip_from'])."' and zip_code_1<='".sql_friendly($_POST['zip_to'])."')
					or
					(zip_code_2>='".sql_friendly($_POST['zip_from'])."' and zip_code_2<='".sql_friendly($_POST['zip_to'])."')
				)
			";
		}
		elseif(is_numeric($_POST['zip_from']) && $_POST['zip_from'] > 0)	
		{
			$str_filter.=" and (zip_code_1>='".sql_friendly($_POST['zip_from'])."' or zip_code_2>='".sql_friendly($_POST['zip_from'])."')";
		}
		elseif(is_numeric($_POST['zip_to']) && $_POST['zip_to'] > 0)		
		{
			$str_filter.=" and (zip_code_1<='".sql_friendly($_POST['zip_to'])."' or zip_code_2<='".sql_friendly($_POST['zip_to'])."')";
		}
	}
	
	$preview_url="<b>Search Utility:</b> <a href='http://www.truckmiles.com/prodrivf.exe?source=PRM&OriginCity=&OriginState=&OriginZip=".$_POST['zip_from']."&DestCity=&DestState=&DestZip=".$_POST['zip_to']."&Resultsin=Miles&RouteMethod=Truck_Practical".
     				"&PerMileRate=0.50&AvoidToll=OFF&Resultsas=RouteOnly&MPG=5.5&load_worth=300&my_cut=100&opcost=.55&B1.x=15&B1.y=10' target='_blank'>Find Trips by Changing Zip Codes.</a>";
     				
	if((int)$_POST['edti_zip_1'] > 0 && (int)$_POST['edti_zip_2'] > 0)
	{		
		$preview_url="<b>Preview:</b> <a href='http://www.truckmiles.com/prodrivf.exe?source=PRM&OriginCity=&OriginState=&OriginZip=".$_POST['edti_zip_1']."&DestCity=&DestState=&DestZip=".$_POST['edti_zip_2']."&Resultsin=Miles&RouteMethod=Truck_Practical".
     				"&PerMileRate=0.50&AvoidToll=OFF&Resultsas=RouteOnly&MPG=5.5&load_worth=300&my_cut=100&opcost=.55&B1.x=15&B1.y=10' target='_blank'>Please find ".$_POST['edti_zip_1']." to ".$_POST['edti_zip_2']." on TruckMiles.com and enter it here.</a>";
	}
		
	$sql = "
		select *		
		from zip_to_zip_trips
		where deleted = 0
			".$str_filter."
		order by zip_code_1 asc,zip_code_2 asc,id asc
		".$limiter."
	";
	$data= simple_query($sql);	
?>
<form action="<?=$SCRIPT_NAME?>" method="post">
<table class='admin_menu1' style='text-align:left;margin:5px; width:1700px;'>
<tr>
	<td valign='top' align='left' colspan='3'><h3><b>Zip to Zip Trip Mileage and Time Settings</b></h3></td>
</tr>
<tr>
	<td valign='top' align='left'>	
		
		Find Entries using Zip Code Range 
		
		From		<input type='text' name='zip_from' id='zip_from' value='<?= $_POST['zip_from'] ?>' style='width:100px; text-align:right;'>
		
		To 		<input type='text' name='zip_to' id='zip_to' value='<?= $_POST['zip_to'] ?>' style='width:100px; text-align:right;'>
		
		
		<input type="submit" name='refresh_zip_to_zip' value="Search"> - - - >
		
		<br><br>
		<a href='zip_to_zip_trip_storage.php?zip1x=<?=$_POST['zip_from'] ?>&zip2x=<?=$_POST['zip_to'] ?>'>Add New Zip to Zip Trip</a>
		<br><br>
		
		<input type='hidden' name='zip_to_zip_trip_id' id='zip_to_zip_trip_id' value='<?= $_POST['zip_to_zip_trip_id'] ?>'>	
		<table width='600' border='0' class='admin_menu2'>
		<tr>
			<td colspan='5' align='center'><b>Modify Zip to Zip Trip Info</b></td>
		</tr>
		<tr>
			<td colspan='5'>Since the road distance should be the same from Zip Code 1 to Zip Code 2 as it is from Zip Code 2 back to Zip Code 1, you only need to enter one set from either direction.  Both is redundant if they are the same.</td>
		</tr>
		<tr>
			<td colspan='5'><span class='alert'><b><?= $mrr_message ?></b></span></td>
		</tr>
		<tr>
			<td valign='top'>Zip Code 1 <?= show_help('zip_to_zip_trip_storage.php','Zip Code 1') ?> <input type='text' name='edti_zip_1' id='edti_zip_1' value='<?= $_POST['edti_zip_1'] ?>' style='width:100px; text-align:right;'></td>
			<td valign='top'>Zip Code 2 <?= show_help('zip_to_zip_trip_storage.php','Zip Code 2') ?> <input type='text' name='edti_zip_2' id='edti_zip_2' value='<?= $_POST['edti_zip_2'] ?>' style='width:100px; text-align:right;'></td>
			<td valign='top'>Trip Mileage <?= show_help('zip_to_zip_trip_storage.php','Trip Mileage') ?> <input type='text' name='edti_miles' id='edti_miles' value='<?= $_POST['edti_miles'] ?>' style='width:100px; text-align:right;'></td>
			<td valign='top'>Trip Time <?= show_help('zip_to_zip_trip_storage.php','Trip Time') ?> <input type='text' name='edti_hours' id='edti_hours' value='<?= $_POST['edti_hours'] ?>' style='width:100px; text-align:right;'></td>
			<td valign='top'><input type="submit" name='update_zip_to_zip' value="Update"></td>
		</tr>	
		</table>	
		<br><br>
		<?= $preview_url ?>
	</td>
	<td valign='top'>
				
		<table width='600' border='0' class='admin_menu2 tablesorter'>
		<thead>
		<tr>
			<th><b>EDIT</b></th>
			<th><b>ZipCode1</b></th>
			<th><b>ZipCode2</b></th>
			<th><b>Mileage</b></th>
			<th><b>Time</b></th>
			<th><b>DELETE</b></th>
		</tr>
		</thead>
		<tbody>
		<?
			$cntr=0;
			while($row=mysqli_fetch_array($data))
			{
				echo "
					<tr>
						<td valign='top'><a href='zip_to_zip_trip_storage.php?id=".$row['id']."' title='Edit Zip to Zip Trip Info for this entry...'>".$row['id']."</a></td>
						<td valign='top'>".$row['zip_code_1']."</td>
						<td valign='top'>".$row['zip_code_2']."</td>
						<td valign='top'>".$row['miles']."</td>
						<td valign='top'>".substr($row['timer'],0,5)."</td>
						<td valign='top'><a href='javascript:delete_zip_trip(".$row['id'].");'><img src='images/delete_small.png' alt='Delete' title='Delete this Zip To Zip Trip Info...forces reload next time searched.' style='border:0'></a></td>
					</tr>
				";
				$cntr++;
			}
		?>
		</tbody>
		</table>
		<br>
		<?= ($limiter!="" ? "".$limiter."" : "".$cntr."") ?> SHOWN
	</td>
	<td valign='top' align='left' width='500'>
		<div style='padding:10px;'>
     		<b>NOTES: (Zip Code List should build over time as new trips are processed.)</b>
     		<br><br>
     		These are trips already run through TruckMiles.com.  The site gives the mileage and driving time from zip code to zip code.  However, to prevent having to call their API so much,
     		all trips are stored and reused.  In general practice, the distance from Point A to Point B is the same as Point B to Point A.  So, only one version of the trip is in the table. 
     		If the trip (leg) can be found in the list (Use the range to search for specific trips), there is no API call made to save processing time and energy.  
     		<br><br>
     		Removing them will force the API call to pull a fresh set of values for mileage and driving time.  This can be good if you forgot what changes you made and they were wrong. 
     		<br><br>
     		If they are not as accurate as you'd like,  click on the EDIT ID link to change the mileage and/or driving time.  Once modified, mileage and time will use your new values each time the trip is pulled from either direction 
     		and not call the API again.  This would be ideal if TruckMiles.com claims a trip is 18.5 miles when you know it is 20.  Or, if the time is misleading and must be increased or decreased based on route-specific details.
     		<br><br>
     		If you do not see the trip (zip code pair in either order), AND you searched for it using the Zip Code Range and SEARCH button and could not find a pairing, you can <a href='zip_to_zip_trip_storage.php'>add a new one here</a>.
     		All four fields are essential: both zip codes, mileage (in decimal number of miles) and driving time (in clock format "00:00" or "00:00:00" for HH:MM:SS).  Seconds are not necessary and will be dropped in most displays.
     		Press UPDATE to add it manually (without going through the TruckMiles.com API), and it should then be used instead.  Each leg of a trip should be entered separately, but return trips usingthe same leg do not.
     		<br><br>
     		For Example: 
     		<br><br>
     		From LaVergne(37086), to Nashville (37221), to Franklin (37064) and back would need 37086 to 37221 and 37221 to 37064.  If you a returning back through the reversed route -- Franklin, to Nashville, to LaVergen -- 
     		nothing more is required.  
     		<br><br>
     		However, to get the return trip straight back from Franklin to LaVergne without Nashville , you will want a different line (37064 to 37086).  Please note that if any of the legs of the trip are already in the system from a
     		previous trip, there is no need to enter them again (eighter way).  37086 to 37064 should be the same as 37064 to 37086, so no need to duplicate efforts.  
     		<br><br>It is possible that ALL legs of the trip are already in the system, thus no new entries are required. Zip Code List should build over time as new trips are processed.
		</div>
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>
	
	function delete_zip_trip(id) 
	{
		$.prompt("Are you sure you want to <span class='alert'>delete</span> this Zip to Zip Trip Info?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						window.location = "<?=$_SERVER['SCRIPT_NAME']?>?delid="+id;
					}
				}
			}
		);	
	}
</script>
<? include('footer.php') ?>