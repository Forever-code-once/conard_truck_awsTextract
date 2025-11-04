<? include('header.php') ?>
<?

error_reporting(E_ALL);
ini_set('display_errors', '1');

$e_type=0;
$e_select=0;
$set_id=0;

if(	isset($_POST['equipment_type']) && $_POST['equipment_type']> 0)			$e_type=$_POST['equipment_type'];
if(	isset($_POST['equipment_xref_id']) && $_POST['equipment_xref_id']> 0)		$e_select=$_POST['equipment_xref_id'];

$equip_type_box=mrr_select_box_for_options("equipment_type",'equipment_type',$e_type,'Select Equipment');

$show_scheduled=0;
if(	isset($_POST['show_scheduled']) && $_POST['show_scheduled']> 0)			$show_scheduled=$_POST['show_scheduled'];


?>

<table cellpadding='2' cellspacing='0' width='600' border='1' class='table_grid' style='margin:4px'>
<tr>
	<td width="150" valign='top'><b>Go To</b></td>
	<td width="150" valign='top'><a href="maint.php"><b>Maintenance Requests</b></a></td>
	<td width="150" valign='top'><a href="maint_group.php"><b>Group Requests</b></a></td>
	<td width="150" valign='top'><a href="units_need_repair.php"><b>Units Needing Repair</b></a></td>
</tr>
<tr>
	<td valign='top'><a href="maint_recur.php"><b>Recurring Requests</b></a></td>
	<td valign='top'><b>Maintenance Alerts</b></td>
	<td valign='top'><a href="report_maint_requests.php"><b>Maintenance Reports</b></a></td>
	<td valign='top'>&nbsp;</td>
</tr>
<tr>	
</table>
<form action="<?=$SCRIPT_NAME ?><?= $set_id ?>" method="post">
<table class='' style='text-align:left;'>
<tr>
	<td valign='top'>
		<div style='border: solid #e4eaff 1px; background-color: #e4eaff;'>
		<table class='admin_menu1'>
		<tr>
			<td colspan="7"><font class='standard18'><b>Recurring Maintenance Alerts</b></font></td>
		</tr>
		<tr>
			<td colspan="7"><div id='auto_recurring_notices_count' class='section_heading'></div></td>
		</tr>
		<tr bgcolor="#e4eaff" height="30">
			<td valign="middle">&nbsp;&nbsp;<b>Equipment Type</b></td>
			<td valign="middle" colspan="2">&nbsp;&nbsp;<?=$equip_type_box ?></td>
			<td valign="middle">&nbsp;&nbsp;<b>Equipment Item</b></td>
			<td valign="middle" colspan="2">&nbsp;&nbsp;<!--  onChange='mrr_update_list();' -->
				<select name='equipment_xref_id' id='equipment_xref_id'>
					<option>Please select equipment type</option>
				</select>
			</td>
			<td valign="middle">&nbsp;<label for='show_scheduled'><b>Display Scheduled</b></label>
				<input type='checkbox' name='show_scheduled' id='show_scheduled' <? if($show_scheduled) echo 'checked'?> value="1"></td>
		</tr>
		<tr>
			<td colspan="7"><div id='auto_recurring_notices'></div>	</td>
		</tr>				
		</table>
	</div>
	</td>
</tr>
</table>
</form>

<script type='text/javascript'>
	
		
	//update maintenance request listing for first load.
	$().ready(function() {
		
		display_maint_recur_notes(0,0,0);
		
	});
	
	
	//$('.datepicker').datepicker();
	
	$('#equipment_type').change(function() {
		
		var state_is=($('#show_scheduled').is(':checked') ? 1 : 0)	;
		display_equipment_select_box( $('#equipment_type').val(), 0);
		display_maint_recur_notes($('#equipment_type').val(),0,state_is);
	});
	
	$('#equipment_xref_id').change(function() {
		
		var state_is=($('#show_scheduled').is(':checked') ? 1 : 0)	;
		display_maint_recur_notes($('#equipment_type').val(), $(this).val(), state_is);
	});
	
	$('#show_scheduled').change(function() {
		
		var state_is=($('#show_scheduled').is(':checked') ? 1 : 0)	;
		display_maint_recur_notes($('#equipment_type').val(), $('#equipment_xref_id').val(), state_is);
	});
	
	function schedule_item_maint_recurring(xx,yy,zz)
	{
		var maint_id=xx;
		var item=yy;
		var etype_id=zz;	
		var my_new_id=0;
		
		$.ajax({
     			url: "ajax.php?cmd=ajax_copy_maint_request_or_recurring",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output
     				//$('#equipment_type').val()
     				//$('#equipment_xref_id').val()
     				request_id: maint_id,
     				req_recur_flag: 0,
     				equipment_type: etype_id,
     				equipment_xref_id: item
     			},
     			error: function() {
     				alert('general error scheduling item using recurring schedule.');
     			},
     			success: function(xml) {
     				$(xml).find('CopyRequest').each(function() {
     					
     					my_new_id=$(this).find('RequestDestinationID').text();
     					
     					$.noticeAdd({text: "Maintenance Request "+my_new_id+" has been created based on this Recurring Maintenance Alert."});
       				});
     				
     				
     			}
     		});	
     	//var state_is=($('#show_scheduled').is(':checked') ? 1 : 0)	;
     	////alert("State is "+state_is+".");
		//display_maint_recur_notes($('#equipment_type').val(), $('#equipment_xref_id').val(), state_is);
	}
		
	function schedule_item_maint(xx,yy,zz)
	{
		var maint_id=xx;
		var item=yy;
		var etype_id=zz;	
		var my_new_id=0;
		var box_namer="";
		
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
     					box_namer="container_"+maint_id+"_"+etype_id+"_"+item+"";
     					
     					$('#'+box_namer+'').html('');
     					$('#'+box_namer+'').append("<a href='maint.php?id="+my_new_id+"' class='mrr_scheduled_link' target='_blank'><b>Scheduled</b></a>");
       				});    			
     			}
     		});	
     	     	
     	//var state_is=($('#show_scheduled').is(':checked') ? 1 : 0)	;
     	////alert("State is "+state_is+".");
		//display_maint_recur_notes($('#equipment_type').val(), $('#equipment_xref_id').val(), state_is);
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
     function display_maint_recur_notes(type_id, xref_id,show_scheduled)
      {
        		$.ajax({
     			url: "ajax.php?cmd=ajax_generate_recurring_schedule_notices",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output 
     				//$('#equipment_type').val()
     				//$('#equipment_xref_id').val()
     				equipment_type: type_id,
     				equipment_xref_id: xref_id,
     				show_sched: show_scheduled
     			},
     			error: function() {
     				alert('Error listing Recurring Notices.');
     			},
     			success: function(xml) {
     				
     				var box_namer='auto_recurring_notices';
     				var box_namer2='auto_recurring_notices_count';
     				var box_namer3='';
     				var my_req_id=0;
     				var sub_counter=0;
     				var marker_item=0;
     				temp_holding="";
     				
     				$('#'+box_namer+'').html('');

     				temp_holding+="<table cellpadding='2' cellspacing='0' width='800' border='1' class='table_grid' style='margin:4px'>";
     				
     				temp_holding+="<tr>";
     				temp_holding+="<td width='20'><b></b></td>";
     				temp_holding+="<td><b>Description</b></td>";
     				temp_holding+="<td width='45'><b>Type</b></td>";
     				temp_holding+="<td width='80'><b>Name</b></td>";
     				temp_holding+="<td width='70' align='right'><b>Scheduled</b></td>";
     				temp_holding+="<td width='70' align='right'><b>Days</b></td>";
     				temp_holding+="<td width='90' align='right'><b>Miles</b></td>";
     				temp_holding+="<td width='80' align='right'><b>Each Cost</b></td>";
     				temp_holding+="<td width='65' align='right'><b>Count</b></td>";
     				temp_holding+="</tr>";
     				   				
     				
     				$(xml).find('MaintRequest').each(function()	{
     					
     					marker_item++;
     					sub_counter=0;  					
     					temp_holding+="<tr>";
     					
     					tempy=$(this).find('RequestUrgent').text();
     					if(tempy==1)	temp_holding+="<td><span style='color:#CC0000;'><b>!!!</b></span></td>";
     					else			temp_holding+="<td>&nbsp;</td>";    
     					
     					my_req_id=$(this).find('RequestID').text();
     					box_namer3="fun_count_"+my_req_id+"_"+marker_item;	//
     					
     					
     					
     					temp_holding+="<td>"+$(this).find('RequestLink').text()+"</td>";
     					temp_holding+="<td>"+$(this).find('RequestType').text()+"</td>";
     					temp_holding+="<td>"+$(this).find('RequestName').text()+"</td>";
     					temp_holding+="<td align='right'>"+$(this).find('RequestScheduled').text()+"</td>";
     					temp_holding+="<td align='right'>"+$(this).find('RequestRDays').text()+"</td>";
     					temp_holding+="<td align='right'>"+$(this).find('RequestRMiles').text()+"</td>";
     					temp_holding+="<td align='right'>$"+$(this).find('RequestCost').text()+"</td>";   
     					temp_holding+="<td align='right'><div id='"+box_namer3+"'></div></td>";
     					temp_holding+="</tr>";
     					
     					temp_holding+="<tr>";
     					temp_holding+="<td></td>";
     					temp_holding+="<td></td>";
     					temp_holding+="<td><b>Type</b></td>";
     					temp_holding+="<td><b>Item</b></td>";
     					temp_holding+="<td align='right'><b>Cur Odom</b></td>";
     					temp_holding+="<td align='right'><b>Cur Date</b></td>";
     					temp_holding+="<td align='right'><b>Last Req Odom</b></td>";
     					temp_holding+="<td align='right'><b>Last Req Date</b></td>";
     					temp_holding+="<td align='right'><b>Action</b></td>";
     					temp_holding+="</tr>";
     					
     					$(this).find('ItemRecurring').each(function() {
     					    	     					    	
     						temp_holding+="<tr>";
     						temp_holding+="<td></td>";
     						temp_holding+="<td></td>";
     						//temp_holding+="<td>"+$(this).find('ItemMaintDesc').text()+"</td>";
     						//temp_holding+="<td>"+$(this).find('ItemTypeID').text()+"</td>";
     						temp_holding+="<td>"+$(this).find('ItemTypeName').text()+"</td>";     						
     						//temp_holding+="<td>"+$(this).find('ItemID').text()+"</td>";
     						temp_holding+="<td>"+$(this).find('ItemName').text()+"</td>";     						
     						temp_holding+="<td align='right'>"+$(this).find('ItemCurOdom').text()+"</td>";
     						temp_holding+="<td align='right'>"+$(this).find('ItemCurDate').text()+"</td>";
     						temp_holding+="<td align='right'>"+$(this).find('ItemReqOdom').text()+"</td>";
     						temp_holding+="<td align='right'>"+$(this).find('ItemReqDate').text()+"</td>";
     						temp_holding+="<td align='right'>"+$(this).find('ItemAddReq').text()+"</td>";
     						temp_holding+="</tr>";
     						
     						sub_counter++;     							
     					});	    
     					
     					if(sub_counter==0)
     					{
     						temp_holding+="<tr>";
     						temp_holding+="<td colspan='9'><center>There are no Alerts for this Recurring Schedule.</center></td>";
     						temp_holding+="</tr>";	
     					}
     					 					
     					temp_holding+="<tr>";
     					temp_holding+="<td colspan='9'><hr></td>";
     					temp_holding+="</tr>";
     					
     					//alert('Field will be called="'+box_namer3+'" has a subcount of '+sub_counter+'.');
     					$('#'+box_namer3+'').html(sub_counter);
     					     					     					
     					$('#'+box_namer2+'').html('');
     					$('#'+box_namer2+'').append(""+$(this).find('RequestCount').text()+" General Recurring Notices");		
     					
     				});	
     				temp_holding+="</table>";
     				$('#'+box_namer+'').append(temp_holding);
     				
     			}
     		});
     }
</script>
<? include('footer.php') ?>
