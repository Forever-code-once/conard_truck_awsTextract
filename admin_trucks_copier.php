<? include('application.php') ?>
<? $admin_page = 1 ?>
<?
	$sql = "update trucks set active=1 where active>1";
	simple_query($sql);
	
	$usetitle = "Truck Copy Maker";
	$use_title = "Truck Copy Maker";
	
	$kill_it=0;		//565 is last truck ID...
	//if($_SESSION['user_id']==23 || $_SESSION['user_id']==15 || $_SESSION['user_id']==18)			$kill_it=0;
	
	if(!isset($_POST['truck_id']))	$_POST['truck_id']=0;
	if(!isset($_POST['copies']))		$_POST['copies']=0;
	
	$msg="Standing By.  Select the Truck and the Number of Copies you want to make.";
	$new_trucks="";
	if(isset($_POST['copy_unit']))
	{		
		if($_POST['truck_id'] > 0 && $_POST['copies'] > 0)
		{			
			$made=0;	
			$notmade=0;			
			
			$name="";
     		$vin="";
     		$plate="";
     		$mrr_rental_flagger=0;
     		     		
     		$sql = "
               	select *               	
               	from trucks
               	where id='".(int) $_POST['truck_id']."'
               ";
               $data=simple_query($sql);
               if($row=mysqli_fetch_array($data))
               {
               	$name=trim($row['name_truck']);
     			$vin=trim($row['vin']);
     			$plate=trim($row['license_plate_no']);
     			$mrr_rental_flagger=$row['rental'];
               }
			
			$msg="<b>Making ".$_POST['copies']." of Truck # ".$name."</b>";
			
			for($i=1; $i <= $_POST['copies']; $i++)
			{
				$new_name=trim($_POST["new_truck_".$i."_name"]);
				$new_vin=trim($_POST["new_truck_".$i."_vin"]);
				$new_plate=trim($_POST["new_truck_".$i."_plate"]);
				
				if($kill_it==0 && trim($new_name)!=trim($name))
				{
					$dup_id = duplicate_row('trucks', $_POST['truck_id']);
				
               		$sql = "
               			update trucks set
               				name_truck= '".sql_friendly($new_name)."',
               				linedate_aquired = NOW(),
               				linedate_returned = '0000-00-00 00:00:00',
               				sicap_coa_created='1',
               				pn_odometer_offset='0',
               				pm_inspection_note='',
               				pm_inspection_date='0000-00-00 00:00:00',
               				fd_inspection_date='0000-00-00 00:00:00',
               				license_plate_no='".sql_friendly($new_vin)."',
               				vin='".sql_friendly($new_plate)."',
               				made_by_user_id='".(int) $_SESSION['user_id']."',
               				active='1',
               				deleted='0'
               				
               			where id = '".sql_friendly($dup_id)."'
               		";
               		simple_query($sql);	
               		
               		if($defaultsarray['sicap_integration'] == 1)
               		{
               			sicap_update_trucks($dup_id, "",false,$mrr_rental_flagger);	
               		}
               		
               		$new_trucks.=" Copy ".$i.": <a href='admin_trucks.php?id=".$dup_id."' target='_blank'>".$new_name."</a> ";
               		
               		$made++;
				}
				elseif($kill_it==0)
				{
					$notmade++;
				}	
				
				
			}
			$msg.="  <span style='color:#00CC00;'><b>...SUCCESS: ".$made." Copies Made</b></span>";	
			if($notmade > 0)	$msg.="  <span style='color:#CC0000;'><b>...BUT: ".$notmade." Invalid Trucks Attempted</b></span>";	
			
			if(trim($new_trucks)!="")		$msg.="<br>".trim($new_trucks)."";			
		}
		else
		{
			$msg="<span style='color:#CC0000;'><b>ERROR: Please select number of copies and the truck to copy.</b></span>";	
		}
		
	}
?>
<? include('header.php') ?>
<?
	$sql = "
     	select *
     	
     	from trucks
     	where deleted = 0
     		and active>=0
     	order by active desc, name_truck
     ";
     $data_trucks = simple_query($sql);
?>
<form action='' method='post'>
     <table class='admin_menu1' style='text-align:left'>
     <tr>
		<td colspan='4'><font class='standard18'><b>Truck Copier</b></font></td>
	</tr>
	<tr>
		<td colspan='4'>Notes: Use this form to mass-copy trucks.  Please change Truck Name for all of them to prevent confusion and exact duplicates.</td>
	</tr>
     <tr>
     	<td valign='top' colspan='2' nowrap>
     		Select Truck to Copy
     		<select name='truck_id' id='truck_id'>
          		<option value='0'>None</option>
          		<?
          		while($row_truck = mysqli_fetch_array($data_trucks)) 
          		{
          			echo "<option value='".$row_truck['id']."'".($_POST['truck_id']==$row_truck['id'] ? " selected" : "").">".(!$row_truck['active'] ? '(inactive) ' : '')."".$row_truck['name_truck']."</option>";
          		}
          		?>
          	</select>
     	</td>
     	<td valign='top' nowrap>
     		Number of Copies
     		<select name='copies' id='copies'>
          		<option value='0'>None</option>
          		<?
          		for($i=1; $i <= 50; $i++)
          		{
          			echo "<option value='".$i."'".($_POST['copies']==$i ? " selected" : "").">".$i."</option>";
          		}
          		?>
          	</select>
     	</td>
     	<td valign='top' nowrap>
     		<input type='submit' value='Refresh Form' class='mrr_button_access'>
     	</td>
     </tr>
     <tr>
     	<td valign='top' colspan='4'><?=$msg ?></td>
     </tr> 
     <tr>
     	<td valign='top'><b>#</b></td>
     	<td valign='top'><b>Truck Name</b></td>
     	<td valign='top'><b>VIN</b></td>
     	<td valign='top'><b>License Plate</b></td>
     </tr> 
     <?
     	if($_POST['truck_id'] > 0 && $_POST['copies'] > 0)
     	{
     		$name="";
     		$vin="";
     		$plate="";
     		
     		
     		$sql = "
               	select *               	
               	from trucks
               	where id='".(int) $_POST['truck_id']."'
               ";
               $data=simple_query($sql);
               if($row=mysqli_fetch_array($data))
               {
               	$name=trim($row['name_truck']);
     			$vin=trim($row['vin']);
     			$plate=trim($row['license_plate_no']);
               }
               
     		for($i=1; $i <= $_POST['copies']; $i++)
     		{
     			echo "
     				<tr style='background-color:#".($i%2==0 ? "eeeeee" : "dddddd")."'>
     					<td valign='top'>".$i."</td>
     					<td valign='top'><input type='text' style='width:300px;' name='new_truck_".$i."_name' id='new_truck_".$i."_name' value=\"".$name."\"></td>
     					<td valign='top'><input type='text' style='width:300px;' name='new_truck_".$i."_vin' id='new_truck_".$i."_vin' value=\"".$vin."\"></td>
     					<td valign='top'><input type='text' style='width:300px;' name='new_truck_".$i."_plate' id='new_truck_".$i."_plate' value=\"".$plate."\"></td>
     				</tr>
     			";	
     		}	
     	}
     ?>  
     <tr>
     	<td valign='top' colspan='4' align='center'>
     		<input type='submit' name='copy_unit' id='copy_unit' value='Copy Truck' class='mrr_button_access'>
     	</td>
     </tr>  
     </table>
</form>
<script type='text/javascript'>
	
</script>
<? include('footer.php') ?>