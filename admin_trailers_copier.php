<? include('application.php') ?>
<? $admin_page = 1 ?>
<?
	$sql = "update trailers set deleted = 1	where trailer_name='unkown'";
	simple_query($sql);
	$sql = "update trailers set active=1 where active>1";
	simple_query($sql);
	
	$usetitle = "Trailer Copy Maker";
	$use_title = "Trailer Copy Maker";
	
	$kill_it=0;		//669 is last truck ID...
	//if($_SESSION['user_id']==23 || $_SESSION['user_id']==15 || $_SESSION['user_id']==18)			$kill_it=0;
	
	if(!isset($_POST['trailer_id']))	$_POST['trailer_id']=0;
	if(!isset($_POST['copies']))		$_POST['copies']=0;
	
	$msg="Standing By.  Select the Trailer and the Number of Copies you want to make.";
	$new_trailers="";
	if(isset($_POST['copy_unit']))
	{		
		if($_POST['trailer_id'] > 0 && $_POST['copies'] > 0)
		{			
			$made=0;	
			$notmade=0;			
			
			$name="";
     		$vin="";
     		$plate="";
     		$mrr_rental_flagger=0;
     		     		
     		$sql = "
               	select *               	
               	from trailers
               	where id='".(int) $_POST['trailer_id']."'
               ";
               $data=simple_query($sql);
               if($row=mysqli_fetch_array($data))
               {
               	$name=trim($row['trailer_name']);
     			$vin=trim($row['vin']);
     			$plate=trim($row['license_plate_no']);
     			$mrr_rental_flagger=$row['rental_flag'];
               }
			
			$msg="<b>Making ".$_POST['copies']." of Trailer # ".$name."</b>";
			
			for($i=1; $i <= $_POST['copies']; $i++)
			{
				$new_name=trim($_POST["new_trailer_".$i."_name"]);
				$new_vin=trim($_POST["new_trailer_".$i."_vin"]);
				$new_plate=trim($_POST["new_trailer_".$i."_plate"]);
				
				if($kill_it==0 && trim($new_name)!=trim($name))
				{
					$dup_id = duplicate_row('trailers', $_POST['trailer_id']);
				
               		$sql = "
               			update trailers set
               				trailer_name= '".sql_friendly($new_name)."',
               				nick_name= '".sql_friendly($new_name)."',
               				location_updated = NOW(),
               				linedate_aquired = NOW(),
               				linedate_returned = '0000-00-00 00:00:00',
               				linedate_last_pmi= '0000-00-00 00:00:00',
               				linedate_last_fed= '0000-00-00 00:00:00',
               				mrr_stamp_added= '0000-00-00 00:00:00',
               				trailer_regist_file='',
               				sicap_coa_created='1',
               				pn_tracking_has=0,
               				pn_tracking_num='',
               				pn_tracking_val='0.00',
               				current_location='',
               				license_plate_no='".sql_friendly($new_vin)."',
               				made_by_user='".(int) $_SESSION['user_id']."',
               				vin='".sql_friendly($new_plate)."',
               				active='1',
               				deleted='0'
               				
               			where id = '".sql_friendly($dup_id)."'
               		";
               		simple_query($sql);	
               		
               		if($defaultsarray['sicap_integration'] == 1)
               		{
               			sicap_update_trailers($dup_id, "");
               		}
               		
               		$new_trailers.=" Copy ".$i.": <a href='admin_trailers.php?id=".$dup_id."' target='_blank'>".$new_name."</a> ";
               		
               		$made++;
				}
				elseif($kill_it==0)
				{
					$notmade++;
				}	
				
				
			}
			$msg.="  <span style='color:#00CC00;'><b>...SUCCESS: ".$made." Copies Made</b></span>";	
			if($notmade > 0)	$msg.="  <span style='color:#CC0000;'><b>...BUT: ".$notmade." Invalid Trailers Attempted</b></span>";	
			
			if(trim($new_trailers)!="")		$msg.="<br>".trim($new_trailers)."";			
		}
		else
		{
			$msg="<span style='color:#CC0000;'><b>ERROR: Please select number of copies and the trailer to copy.</b></span>";	
		}
		
	}
?>
<? include('header.php') ?>
<?
	$sql = "
     	select *
     	
     	from trailers
     	where deleted = 0
     		and active>=0
     	order by active desc, trailer_name
     ";
     $data_trailers = simple_query($sql);
?>
<form action='' method='post'>
     <table class='admin_menu1' style='text-align:left'>
     <tr>
		<td colspan='4'><font class='standard18'><b>Trailer Copier</b></font></td>
	</tr>
	<tr>
		<td colspan='4'>Notes: Use this form to mass-copy trailers.  Please change Trailer Name for all of them to prevent confusion and exact duplicates.</td>
	</tr>
     <tr>
     	<td valign='top' colspan='2' nowrap>
     		Select Trailer to Copy
     		<select name='trailer_id' id='trailer_id'>
          		<option value='0'>None</option>
          		<?
          		while($row_trailer = mysqli_fetch_array($data_trailers)) 
          		{
          			echo "<option value='".$row_trailer['id']."'".($_POST['trailer_id']==$row_trailer['id'] ? " selected" : "").">".(!$row_trailer['active'] ? '(inactive) ' : '')."".$row_trailer['trailer_name']."</option>";
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
     	<td valign='top'><b>Trailer Name</b></td>
     	<td valign='top'><b>VIN</b></td>
     	<td valign='top'><b>License Plate</b></td>
     </tr> 
     <?
     	if($_POST['trailer_id'] > 0 && $_POST['copies'] > 0)
     	{
     		$name="";
     		$vin="";
     		$plate="";
     		
     		
     		$sql = "
               	select *               	
               	from trailers
               	where id='".(int) $_POST['trailer_id']."'
               ";
               $data=simple_query($sql);
               if($row=mysqli_fetch_array($data))
               {
               	$name=trim($row['trailer_name']);
     			$vin=trim($row['vin']);
     			$plate=trim($row['license_plate_no']);
               }
               
     		for($i=1; $i <= $_POST['copies']; $i++)
     		{
     			echo "
     				<tr style='background-color:#".($i%2==0 ? "eeeeee" : "dddddd")."'>
     					<td valign='top'>".$i."</td>
     					<td valign='top'><input type='text' style='width:300px;' name='new_trailer_".$i."_name' id='new_trailer_".$i."_name' value=\"".$name."\"></td>
     					<td valign='top'><input type='text' style='width:300px;' name='new_trailer_".$i."_vin' id='new_trailer_".$i."_vin' value=\"".$vin."\"></td>
     					<td valign='top'><input type='text' style='width:300px;' name='new_trailer_".$i."_plate' id='new_trailer_".$i."_plate' value=\"".$plate."\"></td>
     				</tr>
     			";	
     		}	
     	}
     ?>  
     <tr>
     	<td valign='top' colspan='4' align='center'>
     		<input type='submit' name='copy_unit' id='copy_unit' value='Copy Trailer' class='mrr_button_access'>
     	</td>
     </tr>  
     </table>
</form>
<script type='text/javascript'>
	
</script>
<? include('footer.php') ?>