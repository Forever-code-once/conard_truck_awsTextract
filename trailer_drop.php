<? include('application.php') ?>
<? $no_header = 1 ?>
<?
	if(!isset($_GET['id']))         $_GET['id'] = 0;
    if(!isset($_GET['trailer_id'])) $_GET['trailer_id'] = 0;
	
	if(isset($_POST['trailer_id'])) {
		if($_POST['dropped_trailer_id'] == 0) {
			$sql = "
				insert into trailers_dropped
					(linedate_added,
					created_by_user_id,
					mrr_drop_mode)
					
				values (now(),
					'".sql_friendly($_SESSION['user_id'])."',
					1)
			";
			simple_query($sql);
			$_POST['dropped_trailer_id'] = mysqli_insert_id($datasource);
		}
		
		/*
		MRR Drop Mode Key
		1=Trailer Dropped from this form
		2=Trailer Dropped from Manage Load or Load Board form (Load Stops Ajax)
		3=Trailer Switched on Manage Load or Load Board form (Load Stops Ajax)
		*/
		
		//added Nov 2013...complete all prior drops for this trailer so that trailer does not get dropped in more than one location at the same time...
		$sql = "
			update trailers_dropped set 
				drop_completed = 1,
				linedate_completed=NOW()
			where id != '".sql_friendly($_POST['dropped_trailer_id'])."'
				and trailer_id = '".sql_friendly($_POST['trailer_id'])."'	
				and deleted = 0		
		";
		simple_query($sql);
		
		$sql = "
			update trailers_dropped
			
			set linedate = '".($_POST['linedate'] != '' ? date("Y-m-d", strtotime($_POST['linedate'])) : "0000-00-00")."',
				trailer_id = '".sql_friendly($_POST['trailer_id'])."',
				customer_id = '".sql_friendly($_POST['customer_id'])."',
				location_city = '".sql_friendly($_POST['location_city'])."',
				location_state = '".sql_friendly($_POST['location_state'])."',
				location_zip = '".sql_friendly($_POST['location_zip'])."',
				notes = '".sql_friendly($_POST['notes'])."',
				drop_completed = '".(isset($_POST['drop_completed']) ? '1' : '0')."',
				linedate_completed = ".(isset($_POST['drop_completed']) ? "NOW()" : "'0000-00-00 00:00:00'").",
				is_empty = '".(isset($_POST['is_empty']) ? '1' : '0') ."',
				dedicated_trailer = '".(isset($_POST['dedicated_trailer']) ? '1' : '0') ."'
				
			where id = '".sql_friendly($_POST['dropped_trailer_id'])."'
		";
		simple_query($sql);
		
		$mrr_activity_log_notes.="Trailer ID ".$_POST['trailer_id']." Dropped.";
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,$_POST['trailer_id'],0,0,0,"Trailer ID ".$_POST['trailer_id']." Dropped.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		
		header("Location: trailer_drop.php?id=".$_POST['dropped_trailer_id']);
		die;
	}
	
	$sql = "
		select trailers_dropped.*,
			trailers.trailer_name
		
		from trailers_dropped
			left join trailers on trailers.id = trailers_dropped.trailer_id
		where trailers_dropped.id = '".sql_friendly($_GET['id'])."'
	";
	$data = simple_query($sql);
	$row = mysqli_fetch_array($data);
	
	$mrr_activity_log_notes.="View Trailer Drop ID ".$_GET['id'].". ";
	if(mysqli_num_rows($data))
	{
		$mrr_activity_log_trailer=$row['trailer_id'];
	}
	
	// get a list of available trailers
	$data_trailers = get_available_trailers();
	
	$data_customers = get_customers();
	
	$preset_trailer_id=0;
    if((int) $_GET['id'] == 0 && (int) $_GET['trailer_id'] > 0)
    {
         $preset_trailer_id=(int) $_GET['trailer_id'];
         $sqlt = "
                select trailer_name		
                from trailers
                where id = '".sql_friendly($preset_trailer_id)."'
         ";
         $datat = simple_query($sqlt);
         $rowt = mysqli_fetch_array($datat);
     
         $row['trailer_id']=$preset_trailer_id;
         $row['trailer_name']=trim($rowt['trailer_name']);     
         $row['drop_completed']=0;
         $row['dedicated_trailer']=false;
         $row['customer_id']=0;
         $row['location_city']="";
         $row['location_state']="";
         $row['location_zip']="";
         $row['linedate']=0;
         $row['notes']="";    
         $row['is_empty']=0;
    }
    //parent_window_refresh(true)
?>
<? include('header.php') ?>
		<div class='nav_bar' style=';position:fixed;top:0px;left;0px;margin-top:0px'>
			<div style='float:left;margin-left:40px'>&nbsp;</div>
			<div class='toolbar_button' onclick='window.close();'>
				<div><img src='images/return.png'></div>
				<div>Close</div>
			</div>
			<div class='toolbar_button' onclick="window.location='<?=$_SERVER['SCRIPT_NAME']?>'" style='width:120px'>
				<div><img src='images/new.png'></div>
				<div>New Drop Trailer</div>
			</div>
			<div class='toolbar_button' onclick='CheckSubmit()'>
				<div><img src='images/file.png'></div>
				<div>Save</div>
			</div>
			<? if($_GET['id'] > 0) { ?>
				<div class='toolbar_button' onclick='delete_entry(<?=$_GET['id']?>)'>
					<div><img src='images/delete.png'></div>
					<div>Delete</div>
				</div>
			<? } ?>
		</div>

<form name='mainform' action='' method='post' onsubmit="">
<input type='hidden' name='dropped_trailer_id' value='<?=$_GET['id']?>'>
<table class='standard12 add_entry_truck section0' style='text-align:left;margin-top:100px'>
<tr>
	<td>Trailer Drop ID</td>
	<td><?=$_GET['id']?></td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td><label for='drop_completed'>Drop Completed:</label></td>
	<td>
		<input type='checkbox' name='drop_completed' id='drop_completed' <?=($row['drop_completed'] ? 'checked' : '')?>>
		<?
		if($row['drop_completed'] > 0)	echo date("m/d/Y H:i",strtotime($row['linedate_completed']));
		?>
	</td>
</tr>  
<tr>
	<td><label for='drop_completed'>Dedicated Trailer:</label></td>
	<td><input type='checkbox' name='dedicated_trailer' id='dedicated_trailer' <?=($row['dedicated_trailer'] ? 'checked' : '')?>></td>
</tr>
<tr>
    <td><label for='is_empty'>Trailer Is Empty:</label></td>
    <td><input type='checkbox' name='is_empty' id='is_empty' <?=($row['is_empty'] > 0 ? 'checked' : '')?>></td>
</tr>
    <tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Trailer</td>
	<td>
		<select name='trailer_id' id='trailer_id' onChange='mrr_show_trailer_drops();'>
			<option value='0'>Select Trailer</option>
			<? 
			if($row['trailer_id'])
            {
                echo "<option value='$row[trailer_id]' selected>$row[trailer_name]</option>";
                $preset_trailer_id=$row['trailer_id'];
            }
			while($row_trailer = mysqli_fetch_array($data_trailers)) 
            {
			    if($row_trailer['trailer_id']!=$row['trailer_id']) 
			    {
                     echo "<option value='$row_trailer[trailer_id]' " . ($row_trailer['trailer_id'] == $preset_trailer_id ? 'selected' : '') . ">$row_trailer[trailer_name]</option>";
                }
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td>Customer</td>
	<td>
		<select name='customer_id' id='customer_id'>
			<option value='0'>Select Customer</option>
			<? 
			while($row_customer = mysqli_fetch_array($data_customers)) {
				echo "<option value='$row_customer[id]' ".($row_customer['id'] == $row['customer_id'] ? 'selected' : '').">$row_customer[name_company]</option>";
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td>Location City:</td>
	<td><input name='location_city' id='location_city' value="<?=$row['location_city']?>"></td>
</tr>
<tr>
	<td>Location State:</td>
	<td><input name='location_state' id='location_state' value="<?=$row['location_state']?>"></td>
</tr>
<tr>
	<td>Location Zip:</td>
	<td><input name='location_zip' id='location_zip' value="<?=$row['location_zip']?>"></td>
</tr>
<tr>
	<td>Date:</td>
	<td><input name='linedate' id='linedate' value="<?= ($row['linedate'] > 0 ? date("m/d/Y", strtotime($row['linedate'])) : date("m/d/Y"))?>"></td>
</tr>
<tr>
	<td>Notes:</td>
	<td><textarea name='notes' id='notes' style='width:500px;height:100px'><?=$row['notes']?></textarea></td>
</tr>
<tr>
	<td colspan='2'><div id='dropped_trailer_listing'></div><input type='hidden' name='already_dropped' id='already_dropped' value='0'></td>
</tr>
</table>
</form>
<script type='text/javascript'>

	$('.toolbar_button').hover(
		function() {
			$(this).addClass('toolbar_button_hover');
		},
		function() {
			$(this).removeClass('toolbar_button_hover');
		}
	);
	
	$('#linedate').datepicker();
	
	function mrr_show_trailer_drops()
	{
		cur_id=$('#trailer_id').val();
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_display_previous_trailer_drops",
		   data: {
		   		"trailer_id":cur_id
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) 
		   {
		   		drops=$(xml).find('drops').text();
		   		tdrops=$(xml).find('mrrTab').text();
		   		$('#dropped_trailer_listing').html(tdrops);
		   		$('#already_dropped').val(drops);		   		
		   }	
		 });
	}
	function mrr_complete_this_trailer_drop(id)
	{		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_display_complete_trailer_drop",
		   data: {
		   		"drop_id":id
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) 
		   {
		   		mrr_show_trailer_drops()	   		
		   }	
		 });
	}
	
	function CheckSubmit() {
		
		if($('#trailer_id').val() == 0) {
			$.prompt("You must select the trailer");
			return false;
		}
		
		if($('#customer_id').val() == 0) {
			$.prompt("You must select the customer where this trailer will be dropped with");
			return false;
		}
		
		if($('#already_dropped').val() > 0) {
			$.prompt("This trailer has already been dropped... Please correct the trailer or complete the previous drop record.");
			return false;	
		}
			
		document.mainform.submit();
	}

</script>
<? include('footer.php') ?>