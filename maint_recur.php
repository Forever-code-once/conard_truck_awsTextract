<? include('header.php') ?>
<?

error_reporting(E_ALL);
ini_set('display_errors', '1');

if(isset($_GET['did'])) {
	$sql = "
		update maint_requests
		
		set	deleted = 1
		where id = '".sql_friendly($_GET['did'])."'
	";
	$data_delete = simple_query($sql);
}
if(isset($_GET['lid'])) {
	$sql = "
		update maint_line_items
		
		set	deleted = 1
		where id = '".sql_friendly($_GET['lid'])."'
	";
	$data_delete = simple_query($sql);
}

$mrr_equip_select=0;
$mrr_req_id=0;	
$set_id="";		
if(isset($_GET['id']))
{
	if($_GET['id']> 0)
	{
		$set_id="?id=".$_GET['id']."";
		$mrr_req_id=$_GET['id'];
	}
	elseif(isset($_POST['req_id']) && $_POST['req_id']>0)
	{
		$set_id="?id=".$_POST['req_id']."";
		$mrr_req_id=$_POST['req_id'];	
	}
}

$mrr_item_id=0;
if(isset($_GET['item']))
{
	if($_GET['item']> 0)
	{
		$set_item_id="?id=".$mrr_req_id."&item=".$_GET['item']."";
		$mrr_item_id=$_GET['item'];
	}
	elseif(isset($_POST['item']) && $_POST['item']>0)
	{
		$set_item_id="?id=".$mrr_req_id."&item=".$_POST['item']."";
		$mrr_item_id=$_POST['item'];
	}
}

	
?>


<form action="<?=$SCRIPT_NAME ?><?= $set_id ?>" method="post">
<table class='' style='text-align:left;'>
<tr>
	<td valign='top'>
	
          <table cellpadding='2' cellspacing='0' width='600' border='1' class='table_grid' style='margin:4px'>
          <tr>
          	<td width="150" valign='top'><b>Go To</b></td>
          	<td width="150" valign='top'><a href="maint.php"><b>Maintenance Requests</b></a></td>
          	<td width="150" valign='top'><a href="maint_group.php"><b>Group Requests</b></a></td>
          	<td width="150" valign='top'><a href="units_need_repair.php"><b>Units Needing Repair</b></a></td>
          </tr>
          <tr>
          	<td valign='top'><b>Recurring Requests</b></td>
          	<td valign='top'><a href="maint_recur_notices.php"><b>Maintenance Alerts</b></a></td>
          	<td valign='top'><a href="report_maint_requests.php"><b>Maintenance Reports</b></a></td>
          	<td valign='top'>&nbsp;</td>
          </tr>
          <tr>	
          </table>
		
		
		<table class='admin_menu1'>
		<tr>
			<td><font class='standard18'><b>Recurring Maintenance</b></font></td>
		</tr>
		<tr>
			<td><a href="<?=$SCRIPT_NAME ?>?id=0">Add New Sheduled Maintenance</a></td>
		</tr>
		<tr>
			<td><div id='auto_recurring_listing_count' class='section_heading'></div></td>
		</tr>
		<tr>
			<td><div id='auto_recurring_listing'></div>	</td>
		</tr>				
		</table>

	</td>
	
	
	<td valign='top'>
	<?
	if(isset($_GET['id']))
	{
		$e_type=0;
		$e_select=0;
		$main_desc="";
		$req_active=0;
		$schedule_date="0000-00-00 00:00:00";
		$completed_date="0000-00-00 00:00:00";
		$down_time="0";
		$cost_est="0.00";
		$odometer="0";
		$recref=0;
		$urgent=0;
		
		if(	isset($_POST['equipment_type']) && $_POST['equipment_type']> 0)		$e_type=$_POST['equipment_type'];
		if(	isset($_POST['xref_id']) && $_POST['xref_id']> 0)					$e_select=$_POST['xref_id'];
		if(	isset($_POST['request_active']) && $_POST['request_active']> 0)		$req_active=$_POST['request_active'];
		if(	isset($_POST['request_desc']) && $_POST['request_desc']> 0)			$main_desc=$_POST['request_desc'];	
		if(	isset($_POST['down_time_hours']) && $_POST['down_time_hours']> 0)	$down_time=$_POST['down_time_hours'];
		if(	isset($_POST['cost_estimate']) && $_POST['cost_estimate']> 0)		$cost_est=$_POST['cost_estimate'];
		if(	isset($_POST['req_odometer']) && $_POST['req_odometer']> 0)			$odometer=$_POST['req_odometer'];
		
		if(	isset($_POST['request_urgent']) && $_POST['request_urgent']> 0)		$urgent=$_POST['request_urgent'];
				
		$recur_flag=1;
		$recur_days=0;
		$recur_mileage=0;
		
		if(	isset($_POST['recur_days']) && $_POST['recur_days']> 0)			$recur_days=$_POST['recur_days'];
		if(	isset($_POST['recur_miles']) && $_POST['recur_miles']> 0)			$recur_mileage=$_POST['recur_miles'];
				
		
		if($mrr_req_id>0)
		{
			$sql = "
				select *
					from maint_requests
					where id = '".sql_friendly($mrr_req_id)."'			
			";		// and active=1
				
			$data = simple_query($sql);
			$row = mysqli_fetch_array($data);
			
			$e_type=$row['equip_type'];
			$e_select=$row['ref_id'];					
			$main_desc=$row['maint_desc'];
			$req_active=$row['active'];
			$schedule_date=$row['linedate_scheduled'];
			$completed_date=$row['linedate_completed'];
			$down_time=$row['down_time_hours'];
			$cost_est=$row['cost'];
			$odometer=$row['odometer_reading'];
			
			$recur_flag=$row['recur_flag'];
			$recur_days=$row['recur_days'];
			$recur_mileage=$row['recur_mileage'];	
			
			$recref=$row['recur_ref'];
			$urgent=$row['urgent'];
			
			$mrr_equip_select=$e_select;	//used for java/ajax function
		}
		else
		{
			$req_active=1;	
			$schedule_date=date("Y-m-d")."00:00:00";
		}
		
		/*
		$option_cat_name="equipment_type";		
		$sql="
			select id as use_val,fvalue as use_disp 
				from option_values 
				where cat_id='".sql_friendly(get_option_cat_id($option_cat_name))."'
					 and deleted='0' 
				order by fvalue desc
			";
		$equip_type_box=select_box_disp($sql,'equipment_type',$e_type,'Select Equipment',"");
		*/
		$equip_type_box=mrr_select_box_for_options("equipment_type",'equipment_type',$e_type,'Select Equipment');	
		
		//fillers for new line item
		$label_1_item="";		
		$quant_1_item=1;
		$hours_1_item=0;
		$cost_1_item=0;
		$maker_1_item="";
		$model_1_item="";
		$active_1_item=1;
		$req_cat_1_item=0;
		$pos_x_1_item=0;
		$pos_y_1_item=0;
		$pos_z_1_item=0;
		$pos_t_1_item=0;
				
		if(isset($_POST['item_1_label']) && $_POST['item_1_label']> 0)		$label_1_item=$_POST['item_1_label'];		
		if(isset($_POST['item_1_quant']) && $_POST['item_1_quant']> 0)		$quant_1_item=$_POST['item_1_quant'];
		if(isset($_POST['item_1_hours']) && $_POST['item_1_hours']> 0)		$hours_1_item=$_POST['item_1_hours'];
		if(isset($_POST['item_1_cost']) && $_POST['item_1_cost']> 0)		$cost_1_item=$_POST['item_1_cost'];
		if(isset($_POST['item_1_maker']) && $_POST['item_1_maker']> 0)		$maker_1_item=$_POST['item_1_maker'];
		if(isset($_POST['item_1_model']) && $_POST['item_1_model']> 0)		$model_1_item=$_POST['item_1_model'];
		if(isset($_POST['item_1_active']) && $_POST['item_1_active']> 0)		$active_1_item=$_POST['item_1_active'];
		if(isset($_POST['item_1_req_cat']) && $_POST['item_1_req_cat']> 0)	$req_cat_1_item=$_POST['item_1_req_cat'];
		if(isset($_POST['item_1_pos_x']) && $_POST['item_1_pos_x']> 0)		$pos_x_1_item=$_POST['item_1_pos_x'];
		if(isset($_POST['item_1_pos_y']) && $_POST['item_1_pos_y']> 0)		$pos_y_1_item=$_POST['item_1_pos_y'];
		if(isset($_POST['item_1_pos_z']) && $_POST['item_1_pos_z']> 0)		$pos_z_1_item=$_POST['item_1_pos_z'];
		if(isset($_POST['item_1_pos_t']) && $_POST['item_1_pos_t']> 0)		$pos_t_1_item=$_POST['item_1_pos_t'];
				
		$request_cat_box=mrr_select_box_for_options("request_category",'item_1_req_cat',$req_cat_1_item,'Select Request Category');
		
		$pos_x_box=mrr_select_box_for_options("positions_x_axis",'item_1_pos_x',$pos_x_1_item,'Select Position X');
		$pos_y_box=mrr_select_box_for_options("positions_y_axis",'item_1_pos_y',$pos_y_1_item,'Select Position Y');
		$pos_z_box=mrr_select_box_for_options("positions_z_axis",'item_1_pos_z',$pos_z_1_item,'Select Position Z');
		$pos_t_box=mrr_select_box_for_options("positions_t_axis",'item_1_pos_t',$pos_t_1_item,'Select Position T');
		// style="border: solid #cccccc 1px; background-color: #FAF3F3; margin:10px;"
	 ?>
	
	<div id='table_section_for_new_form'>
	<table class='admin_menu1'>
	<tr>
	<td>
		<div id='request_new_mainter' style='border: solid #cccccc 1px; background-color: #ffaaaa;'>
		<br>
		<table cellpadding="0" cellspacing="0">	
		<tr height="30">
			<td valign="middle" colspan="6"><font class='standard18'>&nbsp;<b>Recurring Scheduled Maintenance</b></font></td>
		</tr>
		<tr>
			<td valign='top'>&nbsp;&nbsp;<b>Request Description</b></td>
			<td valign='top' colspan="5"><textarea name="request_desc" id="request_desc" rows="3" cols="100" wrap="virtual" style='text-align:left;'><?=$main_desc ?></textarea>&nbsp;
			</td>
		</tr>
		<tr>
			<td colspan='6'>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;&nbsp;<b>Equipment Type</b></td>
			<td><?=$equip_type_box ?></td>
			<td><b>Equipment Item</b></td>
			<td><select name='equipment_xref_id' id='equipment_xref_id'>
					<option>Please select equipment type</option>
				</select>
			</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;&nbsp;<label for='request_active'><b>Active</b></label>
				<input type='checkbox' name='request_active' id='request_active' <? if($req_active) echo 'checked'?>>
				</td>
			<td><label for='request_urgent'><b>Urgent</b></label>
				<input type='checkbox' name='request_urgent' id='request_urgent' <? if($urgent) echo 'checked'?>>
				</td>
			<td><b>Start Date</b></td>
			<td><input name="req_schedule_date" id="req_schedule_date" style="width: 80px;" class="datepicker" value="<?= ($schedule_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($schedule_date)): "" ) ?>"></td>
			<td><b>Down Time (Hours)</b></td>
			<td><input name="down_time_hours" id="down_time_hours" style="width: 80px; text-align: right;" value="<?= number_format($down_time,2) ?>"></td>
		</tr>
		<tr>			
			<td>&nbsp;&nbsp;<b>Days Until Next</b></td>
			<td><input name="recur_days" id="recur_days" style="width: 80px; text-align: right;" value="<?= $recur_days ?>"></td>
			<td><b>Mileage Until Next</b></td>
			<td><input name="recur_mileage" id="recur_miles" style="width: 80px; text-align: right;" value="<?= $recur_mileage ?>"></td>
			<td><b>Cost $</b></td>
			<td><input name="cost_estimate" id="cost_estimate" style="width: 80px; text-align: right;" value="<?= number_format($cost_est,2) ?>"></td>
		</tr>
		<tr height="30">
			<td colspan='2' valign='bottom'>&nbsp;<input type="button" name="update_request_button" id="update_request_button" value="Update Request"></td>
			<td colspan='2' valign='bottom'><div id='last_odometer_reading'></div></td>
			<td colspan='2' valign='bottom'>
			<? 
			if($_GET['id']>0)
			{
			?>	
				<input type="button" id="maint_norecur_maker" value="Copy to Non-Recurring" onClick="schedule_item_maint_v2('<?= $_GET['id'] ?>');">
			<?
			}		// onClick="schedule_item_maint(<?= $_GET['id'] ? >,<?= $e_select ? >,<?= $e_type ? >);"
					//
			?>
			</td>
		</tr>
		<tr>
			<td colspan='6'>&nbsp;</td>
		</tr>
	</table>
	</div>
	
	</td>
	</tr>
	<?
	$classy="none";		if($mrr_req_id>0)	$classy="block";
			
	?>	
	
	<tr>
	<td>
		<br>
		<div id='request_new_items' style='border: solid #cccccc 1px; background-color: #f4f4f4; display:<?= $classy ?>;'>
			<br>	
			<table cellpadding='0' cellspacing='0'>
				<tr height="30">
					<td valign='top' width='165'>
						<span id='link_like_0' class='mrr_link_like_on' onClick='load_line_item_form(0,0);'>&nbsp;&nbsp;<b>New Line Item</b></span>				
					</td>
					<td valign='top' colspan="2">
						<input name="item_1_label" id="item_1_label" style="width: 350px;" value="<?= $label_1_item ?>">
					</td>					
					<td valign='top' align='right'>
						&nbsp;&nbsp;<b>Quantity</b> <input name="item_1_quant" id="item_1_quant" style="width: 80px; text-align: right;" value="<?= $quant_1_item ?>">
					</td>	
					<td valign='top' align='right'>
						<b>Unit Cost $</b> <input name="item_1_cost" id="item_1_cost" style="width: 80px; text-align: right;" value="<?= number_format($cost_1_item,2) ?>">
					</td>			
				</tr>	
				<tr height="30">
					<td valign='top'>
						<b>&nbsp;</b>				
					</td>
					<td valign='top'>
						<b>Additional Details</b>				
					</td>
					<td valign='top'>
						<?= $request_cat_box  ?>					
					</td>
					<td valign='top'>&nbsp;&nbsp;<label for='item_1_active'><b>Active</b></label> 
						<input type='checkbox' name='item_1_active' id='item_1_active' <? if($active_1_item) echo 'checked'?>>						
					</td>
					<td valign='top' align='right'>
						<b>Time (Hours)</b> <input name="item_1_hours" id="item_1_hours" style="width: 80px; text-align: right;" value="<?= number_format($hours_1_item,2) ?>">
					</td>					
				</tr>				
				<tr height="30">
					<td valign='top'>
						<b>&nbsp;</b>				
					</td>
					<td valign='top'>
						<b>Vendor</b>				
					</td>
					<td valign='top'>
						<input name="item_1_maker" id="item_1_maker" style="width: 200px;" value="<?= $maker_1_item ?>">			
					</td>
					<td valign='top'>
						&nbsp;&nbsp;<?= $pos_x_box  ?>
					</td>
					<td valign='top' align='right'>
						<?= $pos_z_box  ?>
					</td>
				</tr>
				
				
				<tr height="30">
					<td valign='top'>
						&nbsp;&nbsp;<input type="button" name="update_line_item_button" id="update_line_item_button" value="Save Line Item">
						<input type="hidden" name="item_1_id" id="item_1_id" value="0">					
					</td>
					<td valign='top'>
						<b>Part</b>				
					</td>
					<td valign='top'>
						<input name="item_1_model" id="item_1_model" style="width: 200px;" value="<?= $model_1_item ?>">				
					</td>
					<td valign='top'>
						&nbsp;&nbsp;<?= $pos_y_box  ?>
					</td>
					<td valign='top' align='right'>
						<?= $pos_t_box  ?>	
					</td>
				
				</tr>
			</table>
				
			<div id='auto_maint_line_item_listing'></div>
		</div>
		
	</td>
	</tr>
	
	<tr>
	<td>
		<div id='upload_section'></div>
		<div id='request_notes'></div>
		<br>
		<input type='hidden' name='req_id' id='req_id' value="<?= $mrr_req_id ?>">		
	</td>
	</tr>
	</table>
		
</td>
</tr>
</table>
		
</div>
<?

}
?>	
</td>
</tr>
</table>
</form>
<script type='text/javascript'>
	var current_req_id = <?= $mrr_req_id ?> ;
	<? 
		if($mrr_req_id > 0) {
			echo " create_note_section('#request_notes', 10, $mrr_req_id); "; 
			echo " create_upload_section('#upload_section', 10, $mrr_req_id); "; 
		}
		
	?>
	$('.datepicker').datepicker();
	
	$('#equipment_type').change(function() {
				
		display_equipment_select_box( $('#equipment_type').val(), 0);
	});
	//check for last mileage Reading
	$('#equipment_xref_id').change(function() {
		
		display_get_last_odometer_reading($('#equipment_type').val(), $('#equipment_xref_id').val());		
	});
	
	function schedule_item_maint_v2(myid)
	{
		var maint_id=myid;
		var item=$('#equipment_xref_id').val();
		var etype_id=$('#equipment_type').val();	
		var my_new_id=0;
		
		$.prompt("Are you sure you want to create a new Maintenance Request for this Recurring Maintenance Alert?", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {
					$.ajax({
     					url: "ajax.php?cmd=ajax_copy_maint_request_or_recurring",
     					type: "post",
     					dataType: "xml",
     					data: {
     						//POST variables needed for "page" to load for XML output
     						//$('#equipment_type').val()
     						//$('#equipment_xref_id').val()
     						request_id: maint_id,
     						req_recur_flag: 1,
     						equipment_type: etype_id,
     						equipment_xref_id: item
     					},
     					error: function() {
     						alert('general error scheduling item using recurring schedule.');
     					},
     					success: function(xml) {
     						$(xml).find('CopyRequest').each(function() {
     							
     							//$('#none').append(""+$(this).find('none').text()+""+$(this).find('EquipmentName').text()+"");
     							my_new_id=$(this).find('RequestDestinationID').text();
     							
     							$.noticeAdd({text: "Maintenance Request "+my_new_id+" has been created based on this Recurring Maintenance Alert."});
       						});
     				
     				
     					}
     				});	
					display_maint(0, 0);	
					
					
				}
			}
		});				
	}
	
	//update maintenance request.
	$('#update_request_button').click(function() {
				
		$.ajax({
			url: "ajax.php?cmd=ajax_update_maint_req",
			type: "post",
			dataType: "xml",
			data: {
				//POST variables needed for "page" to load for XML output
				req_id: current_req_id,
				req_scheduled: $('#req_schedule_date').val(),
				req_completed: '',
				req_odometer: 0,
				req_equip_type: $('#equipment_type').val(),
				req_equip_id: $('#equipment_xref_id').val(),
				req_downtime: $('#down_time_hours').val(),
				req_cost: $('#cost_estimate').val(),
				req_desc: $('#request_desc').val(),
				
				req_recur_flag: 1,
				req_recur_days: $('#recur_days').val(),
				req_recur_miles: $('#recur_miles').val(),
								
				req_active:  ($('#request_active').is(':checked') ? 1 : 0),	
				req_urgent:  ($('#request_urgent').is(':checked') ? 1 : 0)
						
				
			},
			error: function() {
				alert('Error saving request. Please make sure Schedule Description has some text in the box.');
			},
			success: function(xml) {
				$('#last_odometer_reading').html('');
				
				$(xml).find('NewMaintRequest').each(function() {
					$('#last_odometer_reading').append("Request "+ $(this).find('RequestID').text()+" has been saved on "+ $(this).find('RequestDate').text()+".");
				});
				
				current_req_id = parseInt($(xml).find('RequestID').text());
				if(current_req_id) display_maint_line_item( current_req_id );
				$.noticeAdd({text: "Maintenance Request has been updated."});
				//$('#table_section_for_new_form').html('');
				
				//$('#request_new_items').css("display","block");
				if(current_req_id>0)	$('#request_new_items').show();
				
				$('#request_desc').focus();			
			}
		});
		display_maint(0, 0);			
	});
		
	//add line item request.
	$('#update_line_item_button').click(function() {
		
		var old_item_id=$('#item_1_id').val();
		var new_item_id=0;		
		
		$.ajax({
			url: "ajax.php?cmd=ajax_update_maint_req_item",
			type: "post",
			dataType: "xml",
			data: {
				//POST variables needed for "page" to load for XML output........... current_req_id
				item_id: 0 ,
				maint_id: current_req_id,
				cat_id: $('#item_1_req_cat').val(),
				item_desc: $('#item_1_label').val(),
				quantity: $('#item_1_quant').val(),
				make: $('#item_1_maker').val(),
				model: $('#item_1_model').val(),
				item_downtime: $('#item_1_hours').val(),
				item_cost: $('#item_1_cost').val(),
				location_front: $('#item_1_pos_x').val(),
				location_left: $('#item_1_pos_y').val(),
				location_top: $('#item_1_pos_z').val(),
				location_inside: $('#item_1_pos_t').val(),
				item_active:  ($('#item_1_active').is(':checked') ? 1 : 0)								
			},
			error: function() {
				alert('Error saving request item. Please make sure New Line Item has a label.');
			},
			success: function(xml) {
				//alert('REQ_ID is '+ $('#req_id').val() +'.');
				$('#last_odometer_reading').html('');
				
				$(xml).find('NewMaintRequestItem').each(function() {
					$('#last_odometer_reading').append("New Request "+ $(this).find('RequestItemID').text()+" has been saved on "+ $(this).find('RequestItemDate').text()+".");
					
					//remove copy
					new_item_id=$(this).find('RequestItemID').text();
					if(new_item_id >0 && old_item_id >0)
					{
						delete_single_line_item(current_req_id,old_item_id);						
					}
					$('#item_1_id').val(new_item_id);
				});
								
				$.noticeAdd({text: "Maintenance Request Item has been saved."});	
				
				if(current_req_id > 0) {
					display_maint_line_item( current_req_id );
				}
				
				$('#item_1_label').focus();
				
			}
		});
					
	});
	
	//update maintenance request listing for first load.
	$().ready(function() {
		
		display_maint(0, 0);
		display_equipment_select_box( $('#equipment_type').val(), <?= $mrr_equip_select ?>);
		display_get_last_odometer_reading($('#equipment_type').val(),  <?= $mrr_equip_select ?>);
		
		<?
		if($mrr_req_id > 0 )
		{
			//load line items on request.	
		?>
			display_maint_line_item( <?= $mrr_req_id ?> );
		<?	
		}
		?>	
		
		if(current_req_id>0)	$('#request_new_items').show();
			
	});
			
	function confirm_delete(id) {
		$.prompt("Are you sure you want to delete this recurring maintenance request?", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {
					window.location = '<?=$SCRIPT_NAME?>?did=' + id;
				}
			}
		});

	}
	function confirm_delete_item(id,linteitem) {
		$.prompt("Are you sure you want to delete this recurring maintenance request line item?", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {
					window.location = '<?=$SCRIPT_NAME?>?id='+id+'&lid=' + linteitem;
				}
			}
		});

	}
	
	function delete_single_line_item(xref_id,item)
	{
		$.ajax({
				url: "ajax.php?cmd=ajax_remove_one_maint_line_item",
				type: "post",
				dataType: "xml",
				data: {
					//POST variables needed for "page" to load for XML output 
					maint_id: xref_id,
					item_id: item				
				},
				error: function() {
					alert('Error updating Recurring Maint Request '+ xref_id +' Item '+ item +'.');
				},
				success: function(xml) {
							
					$(xml).find('MaintRequestItem').each(function() {
						
											
					});	
					
				}
		});	
		display_maint_line_item(xref_id);
	}
	
	function load_line_item_form(xref_id,item)
	{
		var request_id=xref_id;
		var lineitem=item;
		
		if(request_id==0 || lineitem==0)
		{
						$('#item_1_id').val( 0 );
						$('#item_1_label').val( ''  );
						$('#item_1_quant').val( '1'  );
						$('#item_1_hours').val( '0.00'  );
						$('#item_1_cost').val( '0.00'  );
						$('#item_1_maker').val( ''  );
						$('#item_1_model').val( ''  );
						$('#item_1_active').val( 1  );
						$('#item_1_req_cat').val( 0  );
						$('#item_1_pos_x').val( 0  );
						$('#item_1_pos_y').val( 0  );
						$('#item_1_pos_z').val( 0  );
						$('#item_1_pos_t').val( 0  );

						$('#item_1_label').focus();
		}
		else
		{	
			$.ajax({
				url: "ajax.php?cmd=ajax_get_single_line_item",
				type: "post",
				dataType: "xml",
				data: {
					//POST variables needed for "page" to load for XML output 
					maint_id: xref_id,
					item_id: item				
				},
				error: function() {
					alert('Error listing requests for Recurring Maint Request '+ xref_id +' Item '+ item +'.');
				},
				success: function(xml) {
							
					$(xml).find('MaintRequestItem').each(function() {
						
						//var is_checked=$(this).find('RequestItemCat').text();
						$('#item_1_id').val( $(this).find('RequestItemID').text() );
						$('#item_1_label').val( $(this).find('RequestItemName').text()  );
						$('#item_1_quant').val( $(this).find('RequestItemQuant').text()  );
						$('#item_1_hours').val( $(this).find('RequestItemHours').text()  );
						$('#item_1_cost').val( $(this).find('RequestItemUnit').text()  );
						$('#item_1_maker').val( $(this).find('RequestItemMaker').text()  );
						$('#item_1_model').val( $(this).find('RequestItemModel').text()  );						
						$('#item_1_active').attr('checked',($(this).find('RequestItemActive').text() == 1 ? 'checked' : ''));						
						$('#item_1_req_cat').val($(this).find('RequestItemCat').text());
						$('#item_1_pos_x').val($(this).find('RequestItemFront').text());
						$('#item_1_pos_y').val($(this).find('RequestItemLeft').text());
						$('#item_1_pos_z').val($(this).find('RequestItemTop').text());
						$('#item_1_pos_t').val($(this).find('RequestItemInside').text());
						
						//$('#req_id').val( $(this).find('RequestItemRefer').text()  );	
						
					});	
					
				}
			});
		}	
	}
	     
     function display_get_last_odometer_reading(type_id, xref_id)	{
     	$.ajax({
     			url: "ajax.php?cmd=ajax_get_last_odometer_reading",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output
     				//$('#equipment_type').val()
     				//$('#equipment_xref_id').val()
     				equipment_type: type_id,
     				equipment_xref_id: xref_id
     
     			},
     			error: function() {
     				alert('general error pulling odometer reading');
     			},
     			success: function(xml) {
     				$('#last_odometer_reading').html('');
     				$(xml).find('LastOdometerReading').each(function() {
     					$('#last_odometer_reading').append("Last Odometer Reading was "+$(this).find('Odometer').text()+" on "+$(this).find('ReadingDate').text()+".");
     				});
     				
     				//$.noticeAdd({text: "Success - Loaded last odometer reading"});
     			}
     		});
     }
     function display_equipment_select_box(type_id, xref_id)	{
     	
     	$.ajax({
     			url: "ajax.php?cmd=ajax_get_option_list",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output
     				//$('#equipment_type').val()
     				//$('#equipment_xref_id').val()
     				equipment_type: type_id,
     				equipment_xref_id: xref_id
     			},
     			error: function() {
     				alert('general error pulling equipment list');
     			},
     			success: function(xml) {
     				$('#equipment_xref_id').html('');
     				$('#equipment_xref_id').append("<option value='0'>Select Equipment</option>");
     				$(xml).find('EquipmentEntry').each(function() {
     					if($(this).find('EquipmentID').text() == xref_id) {
     						$('#equipment_xref_id').append("<option value='"+$(this).find('EquipmentID').text()+"' selected>"+$(this).find('EquipmentName').text()+"</option>");
     					}
     					else
     					{
     						$('#equipment_xref_id').append("<option value='"+$(this).find('EquipmentID').text()+"'>"+$(this).find('EquipmentName').text()+"</option>");
     					}
     					
     				});
     			}
     		});		
     }
     function display_maint(type_id, xref_id) {
     		$.ajax({
     			url: "ajax.php?cmd=ajax_maint_req_list",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output 
     				
     				req_equip_type: type_id,
     				req_equip_id: xref_id,
     				req_recur_flag: 1
     			},
     			error: function() {
     				alert('Error listing requests.');
     			},
     			success: function(xml) {
     				temp_holder="";     				
     				
     				$('#auto_recurring_listing').html('');
     				temp_holder+="<table cellpadding='2' cellspacing='0' width='98%' border='1' class='table_grid' style='margin:4px'>";
     				temp_holder+="<tr>";
     				temp_holder+="<td><b></b></td>";
     				temp_holder+="<td><b>Description</b></td>";
     				temp_holder+="<td><b>Type</b></td>";
     				temp_holder+="<td><b>Name</b></td>";
     				temp_holder+="<td><b>Scheduled</b></td>";
     				temp_holder+="<td><b>Days</b></td>";
     				temp_holder+="<td><b>Miles</b></td>";
     				temp_holder+="<td align='right'><b>Cost</b></td>";
     				temp_holder+="<td><b></b></td>";
     				temp_holder+="</tr>";
     				
     				$(xml).find('MaintRequest').each(function() {
     					temp_holder+="<tr>";
     					
     					tempy=$(this).find('RequestUrgent').text();
     					if(tempy==1)	temp_holder+="<td><span style='color:#CC0000;'><b>!!!</b></span></td>";
     					else			temp_holder+="<td>&nbsp;</td>";    
     					
     					temp_holder+="<td>"+$(this).find('RequestLink').text()+"</td>";
     					temp_holder+="<td>"+$(this).find('RequestType').text()+"</td>";
     					temp_holder+="<td>"+$(this).find('RequestName').text()+"</td>";
     					temp_holder+="<td>"+$(this).find('RequestScheduled').text()+"</td>";
     					//temp_holder+="<td>"+$(this).find('RequestCompleted').text()+"</td>";
     					//temp_holder+="<td>"+$(this).find('RequestRecur').text()+"</td>";
     					temp_holder+="<td>"+$(this).find('RequestRDays').text()+"</td>";
     					temp_holder+="<td>"+$(this).find('RequestRMiles').text()+"</td>";
     					temp_holder+="<td align='right'>"+$(this).find('RequestCost').text()+"</td>";     					
     					temp_holder+="<td>"+$(this).find('RequestTrash').text()+"</td>";
     					temp_holder+="</tr>";
     					 					
     					$('#auto_recurring_listing_count').html('');
     					$('#auto_recurring_listing_count').append(""+$(this).find('RequestCount').text()+" Active Recurring Maintenance Request(s)");
     					    					
     				});
     				temp_holder+="</table>";	
     				
     				$('#auto_recurring_listing').append(temp_holder);
     				
     			}
     		});
     }
     function display_maint_line_item(xref_id) {
     		$.ajax({
     			url: "ajax.php?cmd=ajax_make_line_item_list",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output 
     				maint_id: xref_id
     			},
     			error: function() {
     				alert('Error listing requests for Recurring Maint Request '+ xref_id +'.');
     			},
     			success: function(xml) {
     				temp_holder=""; 
     				
     				$('#auto_maint_line_item_listing').html('');
     				
     				temp_holder+="<table cellpadding='2' cellspacing='0' width='98%' border='1' class='table_grid' style='margin:4px'>";
     				temp_holder+="<tr>";
     				temp_holder+="<td valign='top' width='165'>&nbsp;<b>Line Items</b></td>";
     				temp_holder+="<td valign='top' width='80'><b>Category</b></td>";
     				//temp_holder+="<td valign='top'><b>Name</b></td>";
     				temp_holder+="<td valign='top' width='100'><b>Vendor</b></td>";
     				temp_holder+="<td valign='top' width='150'><b>Part</b></td>";
     				temp_holder+="<td valign='top' width='150'><b>Location Markers</b></td>";
     				temp_holder+="<td valign='top' width='50' align='right'><b>Hours</b></td>";
     				temp_holder+="<td valign='top' width='50' align='right'><b>Quant</b></td>";
     				temp_holder+="<td valign='top' width='50' align='right'><b>Unit</b></td>";
     				temp_holder+="<td valign='top' width='80' align='right'><b>Cost</b></td>";
     				temp_holder+="<td valign='top' width='25'><b></b></td>";
     				temp_holder+="</tr>";
     				     				
     				$(xml).find('MaintRequestItem').each(function() {
     					temp_holder+="<tr>";
     					temp_holder+="<td valign='top'>&nbsp;"+$(this).find('RequestItemLink').text()+"</td>";
     					temp_holder+="<td valign='top'>"+$(this).find('RequestItemCat').text()+"</td>";
     					//temp_holder+="<td valign='top'>"+$(this).find('RequestItemName').text()+"</td>";					
     					temp_holder+="<td valign='top'>"+$(this).find('RequestItemMaker').text()+"</td>";
     					temp_holder+="<td valign='top'>"+$(this).find('RequestItemModel').text()+"</td>";
     					temp_holder+="<td valign='top'>"+$(this).find('RequestItemFront').text()+" "+
     															$(this).find('RequestItemLeft').text()+" "+
     															$(this).find('RequestItemTop').text()+" "+
     															$(this).find('RequestItemInside').text()+"</td>";
     					temp_holder+="<td valign='top' align='right'>"+$(this).find('RequestItemHours').text()+"</td>";
     					temp_holder+="<td valign='top' align='right'>"+$(this).find('RequestItemQuant').text()+"</td>";
     					temp_holder+="<td valign='top' align='right'>"+$(this).find('RequestItemUnit').text()+"</td>";
     					temp_holder+="<td valign='top' align='right'>"+$(this).find('RequestItemCost').text()+"</td>";
     					temp_holder+="<td valign='top'>&nbsp;"+$(this).find('RequestItemTrash').text()+"</td>";
     					temp_holder+="</tr>";
     					
     				});	
     				temp_holder+="</table>";				
     				$('#auto_maint_line_item_listing').append(temp_holder);
     				
     			}
     		});
     }
</script>
<? include('footer.php') ?>