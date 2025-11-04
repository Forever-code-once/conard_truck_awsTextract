<?
$usetitle = "Accident Trucks";
$use_title = "Accident Trucks";
?>
<? include('header.php') ?>
<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

$mrr_acc_id=0;
$accident_id=0;
$driver_id=0;
$truck_id=0;
$trailer_id=0;
$dispatch_id=0;
$load_id=0;
$accident_date="0000-00-00 00:00:00";
$record_date="0000-00-00 00:00:00";
$claim_date="0000-00-00 00:00:00";
$completed_date="0000-00-00";
$insurance_claim=0;
$insurance_covered=0;
$reviewed=0;
$insurance_company="";
$accident_desc="";
$accident_cost="0.00";
$accident_deductable=$defaultsarray['current_ins_accident_deductible'];
$accident_downtime="0.00";
$injury_desc="";
$injury_cost="0.00";
$injury_deductable="0.00";
$injury_downtime="0.00";
$driver_desc="";
$driver_cost="0.00";
$driver_deductable="0.00";
$driver_downtime="0.00";
$maint_id=0;
$active=1;
$notes_and_updates="";
$conard_number="";

if(	isset($_POST['accident_id']) && $_POST['accident_id']> 0)				$accident_id=$_POST['accident_id'];

if(isset($_GET['did'])) {
	$sql = "
		update accident_reports		
		set deleted = 1
		where id = '".sql_friendly($_GET['did'])."'
	";
	$data_delete = simple_query($sql);
	$accident_id=0;
	$_POST['accident_id']=0;
}

if(isset($_GET['id']))
{
	if($_GET['id']> 0)
	{
		$mrr_req_id=$_GET['id'];
		$accident_id=$_GET['id'];
	}
	elseif($accident_id>0)
	{
		$mrr_req_id=$accident_id;	
	}	
}
if($accident_id>0 && $mrr_req_id)
{
	$mrr_req_id=$accident_id;	
}


if(	isset($_POST['driver_id']) && $_POST['driver_id']> 0)					$driver_id=$_POST['driver_id'];
if(	isset($_POST['truck_id']) && $_POST['truck_id']> 0)					    $truck_id=$_POST['truck_id'];
if(	isset($_POST['trailer_id']) && $_POST['trailer_id']> 0)				    $trailer_id=$_POST['trailer_id'];
if(	isset($_POST['dispatch_id']) && $_POST['dispatch_id']> 0)				$dispatch_id=$_POST['dispatch_id'];
if(	isset($_POST['load_id']) && $_POST['load_id']> 0)						$load_id=$_POST['load_id'];
if(	isset($_POST['accident_date']) && $_POST['accident_date']!="")			$accident_date=$_POST['accident_date'];
if(	isset($_POST['claim_date']) && $_POST['claim_date']!="")				$claim_date=$_POST['claim_date'];
if(	isset($_POST['insurance_claim']) && $_POST['insurance_claim']> 0)		$insurance_claim=$_POST['insurance_claim'];
if(	isset($_POST['insurance_covered']) && $_POST['insurance_covered']> 0)	$insurance_covered=$_POST['insurance_covered'];
if(	isset($_POST['reviewed']) && $_POST['reviewed']> 0)					    $reviewed=$_POST['reviewed'];
if(	isset($_POST['insurance_company']) && $_POST['insurance_company']> 0)	$insurance_company=$_POST['insurance_company'];
if(	isset($_POST['accident_desc']) && $_POST['accident_desc'] !="")			$accident_desc=$_POST['accident_desc'];
if(	isset($_POST['accident_cost']) && $_POST['accident_cost'] > 0)			$accident_cost=$_POST['accident_cost'];
if(	isset($_POST['accident_deductable']) && $_POST['accident_deductable'] > 0)	$accident_deductable=$_POST['accident_deductable'];
if(	isset($_POST['accident_downtime']) && $_POST['accident_downtime'] > 0)	$accident_downtime=$_POST['accident_downtime'];
if(	isset($_POST['injury_desc']) && $_POST['injury_desc'] !="")				$injury_desc=$_POST['injury_desc'];
if(	isset($_POST['injury_cost']) && $_POST['injury_cost'] > 0)				$injury_cost=$_POST['injury_cost'];
if(	isset($_POST['injury_deductable']) && $_POST['injury_deductable'] > 0)	$injury_deductable=$_POST['injury_deductable'];
if(	isset($_POST['injury_downtime']) && $_POST['injury_downtime'] > 0)		$injury_downtime=$_POST['injury_downtime'];
if(	isset($_POST['driver_desc']) && $_POST['driver_desc'] !="")				$driver_desc=$_POST['driver_desc'];
if(	isset($_POST['driver_cost']) && $_POST['driver_cost'] > 0)				$driver_cost=$_POST['driver_cost'];
if(	isset($_POST['driver_deductable']) && $_POST['driver_deductable'] > 0)	$driver_deductable=$_POST['driver_deductable'];
if(	isset($_POST['driver_downtime']) && $_POST['driver_downtime'] > 0)		$driver_downtime=$_POST['driver_downtime'];
if(	isset($_POST['maint_id']) && $_POST['maint_id']> 0)					    $maint_id=$_POST['maint_id'];
if(	isset($_POST['active']) && $_POST['active']> 0)						    $active=$_POST['active'];
if(	isset($_POST['completed_date']) && $_POST['completed_date']!="")		$claim_date=$_POST['completed_date'];
if(	isset($_POST['notes_and_updates']) && $_POST['notes_and_updates']!="")	$notes_and_updates=$_POST['notes_and_updates'];
if(	isset($_POST['accident_number']) && $_POST['accident_number']!="")		$conard_number=$_POST['accident_number'];

?>
<form action="<?=$SCRIPT_NAME ?>?id=<?= $mrr_acc_id ?>" method="post">
<table class='' style='text-align:left;'>
<tr>
	<td valign='top'>
		<table class='admin_menu1'>
		<tr>
			<td><font class='standard18'><b>Accident Trucks</b></font></td>
            <td align='right'><b>Current Deductible</b></td>
		</tr>
		<tr>
			<td><span class='mrr_link_like_on' onClick='load_accident_truck(0);'><b>Add New Accident Truck</b></span></td>
            <td align='right'><b>$<?=$defaultsarray['current_ins_accident_deductible']?></b></td>
		</tr>
		<tr>
			<td colspan='2'><div id='accident_listing_count' class='section_heading'></div></td>
		</tr>		
		<tr>
			<td colspan='2'><div id='accident_listing'></div>	</td>
		</tr>
		<tr>
			<td colspan='2'><a href='report_accident_trucks.php?current=1' target='_blank'>Email Current List with Report</a></td>
		</tr>
		<tr>
			<td colspan='2'><br><hr><br></td>
		</tr>
		<tr>
			<td colspan='2'><div id='completed_accident_listing_count' class='section_heading'></div></td>
		</tr>
		<tr>
			<td colspan='2'><div id='completed_accident_listing'></div>	</td>
		</tr>	
		<tr>
			<td colspan='2'><a href='report_accident_trucks.php?completed=1' target='_blank'>Email Completed/Inactive List with Report</a></td>
		</tr>			
		</table>
	</td>	
	<td valign='top'>
	<?
	if($mrr_acc_id >= 0)
	{
		if($mrr_acc_id > 0)
		{
			$sql = "
				select *
					from accident_reports
					where id = '".sql_friendly($mrr_acc_id)."'			
			";		// and active=1
				
			$data = simple_query($sql);
			$row = mysqli_fetch_array($data);
			
			$conard_number=trim($row['accident_number']);
			
			$id=$row['id'];
               $driver_id=$row['driver_id'];
               $truck_id=$row['truck_id'];
               $trailer_id=$row['trailer_id'];
               $dispatch_id=$row['dispatch_id'];
               $load_id=$row['load_id'];
               $accident_date=$row['accident_date'];
               $record_date=$row['linedate_added'];
               $claim_date=$row['claim_date'];
               $insurance_claim=$row['insurance_claim'];
               $insurance_covered=$row['insurance_covered'];
               $reviewed=$row['reviewed'];
               $insurance_company=$row['insurance_company'];
               $accident_desc=$row['accident_desc'];
               $accident_cost=$row['accident_cost'];
               $accident_deductable=$row['accident_deductable'];
               $accident_downtime=$row['accident_downtime'];
               $injury_desc=$row['injury_desc'];
               $injury_cost=$row['injury_cost'];
               $injury_deductable=$row['injury_deductable'];
               $injury_downtime=$row['injury_downtime'];
               $driver_desc=$row['driver_desc'];
               $driver_cost=$row['driver_cost'];
               $driver_deductable=$row['driver_deductable'];
               $driver_downtime=$row['driver_downtime'];
               $maint_id=$row['maint_id'];
               $active=$row['active'];
               $completed_date=$row['completed_date'];
               $notes_and_updates=$row['notes_and_updates'];
		}
		
		//$adder_in_shop=" and in_the_shop=0";
		$adder_in_shop="";
		
		
          //get select boxes
          $sql="
          	select id as use_val,
          		name_truck as use_disp
          	from trucks
          	where deleted=0 and active=1
          		".$adder_in_shop."
          	order by name_truck asc,id asc
          ";
          $truck_box=mrr_select_box_disp($sql,'truck_id',$truck_id,'Truck',''); 		// style="width:300px;"
          $sql="
          	select id as use_val,
          		trailer_name as use_disp
          	from trailers
          	where deleted=0 and active=1
          		".$adder_in_shop."
          	order by trailer_name asc,id asc
          ";
          $trailer_box=mrr_select_box_disp($sql,'trailer_id',$trailer_id,'Trailer',''); 
          $sql="
          	select id as use_val,
          		CONCAT(name_driver_first,' ',name_driver_last) as use_disp,
          		active as is_active
          	from drivers
          	where deleted=0
          	order by active desc,name_driver_last asc,name_driver_first asc,id asc
          ";	// and active=1
          $driver_box=mrr_select_box_disp($sql,'driver_id',$driver_id,'Driver','',1); 
		//echo "<br>Query: ".$sql."<br>";
	?>
		<table class='admin_menu1'>
		<tr>
			<td>
				<div id='request_new_mainter' style='border: solid #cccccc 1px; background-color: #e4eaff;'>
				<br>
				<table cellpadding="0" cellspacing="0">	
				<tr height="30">
					<td colspan="6"><font class='standard18'><b>&nbsp;Accident Truck</b></font></td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;</td>
					<td valign='top'><b>Driver</b> <?= show_help('accident_trucks.php','Accident Driver') ?></td>
					<td valign='top'><b>Truck</b> <?= show_help('accident_trucks.php','Accident Truck') ?></td>
					<td valign='top'><b>Trailer</b> <?= show_help('accident_trucks.php','Accident Trailer') ?></td>
					<td valign='top'><b>Load</b> <?= show_help('accident_trucks.php','Accident Load') ?></td>
					<td valign='top'><b>Dispatch</b> <?= show_help('accident_trucks.php','Accident Dispatch') ?></td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;<b>Select as needed.</b></td>
					<td valign='top'><?=$driver_box ?></td>
					<td valign='top'><?=$truck_box ?></td>
					<td valign='top'><?=$trailer_box ?></td>
					<td valign='top'><input name='load_id' id='load_id' value='<?=$load_id ?>' class='mrr_number_input'>&nbsp;</td>
					<td valign='top'><input name='dispatch_id' id='dispatch_id' value='<?=$dispatch_id ?>' class='mrr_number_input'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='6'>&nbsp;</td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;<b>Conard Accident No.</b> <?= show_help('accident_trucks.php','Accident Number') ?></td>
					<td valign='top' colspan="3">
						<input name='accident_number' id='accident_number' value='<?=$conard_number ?>' class='mrr_number_input'>&nbsp;
					</td>
					<td valign='top' align='right' colspan='2'>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;<b>Accident Description</b> <?= show_help('accident_trucks.php','Accident Desc') ?><br><label>Email <input type='checkbox' name='accident_email_desc0' id='accident_email_desc0' value='1'> </label></td>
					<td valign='top' colspan="3">
						<textarea name="accident_desc" id="accident_desc" rows="3" cols="50" wrap="virtual" style='text-align:left;'><?=$accident_desc ?></textarea>&nbsp;
					</td>
					<td valign='top' align='right' colspan='2'>
								<table width='100%' border='0'>
									<tr><td valing='top'><b>Cost $&nbsp;</b></td>
										<td valing='top' align='right'><input name='accident_cost' id='accident_cost' value='<?=$accident_cost ?>' class='mrr_number_input'>&nbsp;</td></tr>
									<tr><td valing='top'><b>Deductible $&nbsp;</b></td>
										<td valing='top' align='right'><input name='accident_deductable' id='accident_deductable' value='<?=$accident_deductable ?>' class='mrr_number_input'>&nbsp;</td></tr>
									<tr><td valing='top'><b>Down Time (hrs)&nbsp;</b></td>
										<td valing='top' align='right'><input name='accident_downtime' id='accident_downtime' value='<?=$accident_downtime ?>' class='mrr_number_input'>&nbsp;</td></tr>
								</table>
							</td>
				</tr>
				<tr>
					<td colspan='6'><hr></td>
				</tr>
				<tr>
					<td valign='middle'>&nbsp;<b>Accident Date</b> <?= show_help('accident_trucks.php','Accident Date') ?></td>
					<td valign='middle'><input name='accident_date' id='accident_date' value='<?= ($accident_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($accident_date)): "" ) ?>' class='input_medium datepicker'></td>
					<td valign='middle'><b>Maint. Request Id</b> <?= show_help('accident_trucks.php','Maintenance Request ID') ?></td>
					<td valign='middle'><input name="maint_id" id="maint_id" value="<?=$maint_id ?>" class='mrr_number_input'></td>
					<td valign='middle' align='right'>
							&nbsp;
						</td>
					<td valign='middle' align='right'>
							<label for='reviewed'><b>Reviewed</b></label>  <?= show_help('accident_trucks.php','Accident Reviewed') ?>
							<input type='checkbox' name='reviewed' id='reviewed' <? if($reviewed) echo 'checked'?>>	&nbsp;
						</td>
				</tr>
				<!-- <?=  ($record_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($record_date)): "" ) ?> -->
				<tr>
					<td valign='middle'>&nbsp;<b>Claim Date</b> <?= show_help('accident_trucks.php','Claim Date') ?></td>
					<td valign='middle'><input name='claim_date' id='claim_date' value='<?= ($claim_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($claim_date)): "" ) ?>' class='input_medium datepicker'></td>
					<td valign='middle'><b>Insurance Company</b> <?= show_help('accident_trucks.php','Insurance Company') ?></td>
					<td valign='middle' colspan=2><input name="insurance_company" id="insurance_company" style="text-align:left; width:200px;" value="<?=$insurance_company ?>"></td>
					<td valign='middle' align='right'>
							<label for='insurance_claim'><b>Claim Filed</b></label>  <?= show_help('accident_trucks.php','Claim Filed') ?>
							<input type='checkbox' name='insurance_claim' id='insurance_claim' <? if($insurance_claim) echo 'checked'?>>	&nbsp;
						</td>
				</tr> 
				<tr>
					<td valign='middle'>&nbsp;<b>Completed Date</b> <?= show_help('accident_trucks.php','Completed Date') ?></td>
					<td valign='middle'><input name='completed_date' id='completed_date' value='<?= ($completed_date!="0000-00-00" ? date("m/d/Y", strtotime($completed_date)): "" ) ?>' class='input_medium datepicker'></td>
					<td valign='middle'>&nbsp;</td>
					<td valign='middle'>&nbsp;</td>
					<td valign='middle' align='right'>
							&nbsp;
						</td>
					<td valign='middle' align='right'>
							<label for='insurance_covered'><b>Covered</b></label>  <?= show_help('accident_trucks.php','Claim Covered') ?>
							<input type='checkbox' name='insurance_covered' id='insurance_covered' <? if($insurance_covered) echo 'checked'?>>	&nbsp;
						</td>
				</tr>
				<tr>
					<td colspan='6'><hr></td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;<b>Injury Description</b> <?= show_help('accident_trucks.php','Injury Desc') ?><br><label>Email <input type='checkbox' name='accident_email_desc1' id='accident_email_desc1' value='1'> </label></td>
					<td valign='top' colspan="3">
						<textarea name="injury_desc" id="injury_desc" rows="3" cols="50" wrap="virtual" style='text-align:left;'><?=$injury_desc ?></textarea>&nbsp;
					</td>
					<td valign='top' align='right' colspan='2'>
								<table width='100%' border='0'>
									<tr><td valing='top'><b>Cost $&nbsp;</b></td>
										<td valing='top' align='right'><input name='injury_cost' id='injury_cost' value='<?=$injury_cost ?>' class='mrr_number_input'>&nbsp;</td></tr>
									<tr><td valing='top'><b>Deductible $&nbsp;</b></td>
										<td valing='top' align='right'><input name='injury_deductable' id='injury_deductable' value='<?=$injury_deductable ?>' class='mrr_number_input'>&nbsp;</td></tr>
									<tr><td valing='top'><b>Down Time (hrs)&nbsp;</b></td>
										<td valing='top' align='right'><input name='injury_downtime' id='injury_downtime' value='<?=$injury_downtime ?>' class='mrr_number_input'>&nbsp;</td></tr>
								</table>
							</td>
				</tr>
				<tr>
					<td colspan='6'><hr></td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;<b>Driver's Description</b> <?= show_help('accident_trucks.php','Driver Desc') ?><br><label>Email <input type='checkbox' name='accident_email_desc2' id='accident_email_desc2' value='1'> </label></td>
					<td valign='top' colspan="3">
						<textarea name="driver_desc" id="driver_desc" rows="3" cols="50" wrap="virtual" style='text-align:left;'><?=$driver_desc ?></textarea>&nbsp;
					</td>
					<td valign='top' align='right' colspan='2'>
								<table width='100%' border='0'>
									<tr><td valing='top'><b>Cost $&nbsp;</b></td>
									<td valing='top' align='right'><input name='driver_cost' id='driver_cost' value='<?=$driver_cost ?>' class='mrr_number_input'>&nbsp;</td></tr>
									<tr><td valing='top'><b>Deductible $&nbsp;</b></td>
										<td valing='top' align='right'><input name='driver_deductable' id='driver_deductable' value='<?=$driver_deductable ?>' class='mrr_number_input'>&nbsp;</td></tr>
									<tr><td valing='top'><b>Down Time (hrs)&nbsp;</b></td>
										<td valing='top' align='right'><input name='driver_downtime' id='driver_downtime' value='<?=$driver_downtime ?>' class='mrr_number_input'>&nbsp;</td></tr>
								</table>
							</td>
				</tr>
				<tr>
					<td colspan='6'><hr></td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;<b>Itemized Accident</b> <?= show_help('accident_trucks.php','itemized_report') ?></td>
					<td valign='top' colspan="5">
						<?
						$sel_cats="<select id='ac_item_cat' name='ac_item_cat'>";
						$sel_cats.="<option value='0' selected>Uncategorized</option>";
						
						$data_cats=get_options('accident_itemize');
						while($row_cats = mysqli_fetch_array($data_cats))
						{
							$sel_cats.="<option value='".$row_cats['id']."'>".trim($row_cats['fname'])."</option>";
						}
						$sel_cats.="</select>";
						?>
						<table width='100%' border='0' cellpadding='0' cellspacing='0'>
						<tr>
							<td valing='top'><b>Date</b></td>
							<td valing='top'><b>Category</b></td>
							<td valing='top'><b>Note/Description</b></td>
							<td valing='top' align='right'><b>Cost</b></td>
							<td valing='top' align='right'><b>&nbsp;</b></td>
						</tr>	
						<tr>
							<td valing='top'>New</td>
							<td valing='top'><?=$sel_cats ?></td>
							<td valing='top'><input name='ac_item_desc' id='ac_item_desc' value="" style='width:600px;'></td>
							<td valing='top' align='right'><input name='ac_item_cost' id='ac_item_cost' value="0.00" class='mrr_number_input'></td>
							<td valing='top' align='right'>$<input type='button' value="Add Cost" onClick='mrr_add_to_itemized_cost();'></td>
						</tr>
						</table>
						<div id='accident_itemized_list'></div>
					</td>
				</tr>
				<tr>
					<td colspan='6'><hr></td>
				</tr>
				<tr>
					<td valign='top'>&nbsp;<b>Notes and Updates</b> <?= show_help('accident_trucks.php','notes_and_updates') ?><br><label>Email <input type='checkbox' name='accident_email_desc3' id='accident_email_desc3' value='1'></label></td>
					<td valign='top' colspan="3">
						<textarea name="notes_and_updates" id="notes_and_updates" rows="3" cols="50" wrap="virtual" style='text-align:left;'><?=$notes_and_updates ?></textarea>&nbsp;
						<br><br>
						<div class='clear'></div>
						<div id='note_section'></div>
						<div class='clear'></div>
						
						<? if($use_new_uploader > 0) { ?>
                    		                    			
                    			<center>
                         			<iframe id='iframe_doc_loader' src="mrr_uploader_hack.php?section_id=11&id=0" width='550' height='80' border='0' style='border:#000000 solid 0px; background-color:#ffffff;'>
                         			</iframe> 
                         		</center>
                         		<div id='attachment_holder'></div>
                    		
                    		<? } else { ?>
                    		
                    			<div id='upload_section'></div>
                    		
                    		<? } ?>
					</td>
					<td valign='top' align='right' colspan='2'>
						<table width='100%' border='0'>
							<tr><td valing='top'><b>&nbsp;</b></td>
								<td valing='top' align='right'>&nbsp;</td></tr>
							<tr><td valing='top'><b>&nbsp;</b></td>
								<td valing='top' align='right'>&nbsp;</td></tr>
							<tr><td valing='top'><b>&nbsp;</b></td>
								<td valing='top' align='right'>&nbsp;</td></tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan='6'>&nbsp;</td>
				</tr>
				<tr>
					<td colspan='6'><center><input type="button" name="update_accident_button" id="update_accident_button" value="Update Accident"></center></td>
				</tr>
				<tr>
					<td colspan='6'>
						<h3>Your Email Contact:</h3>
						<table width='100%' border='0'>
						<tr>
							<td valign='top'><b>Type</b></td>
							<td valign='top'><b>First Name</b></td>	
							<td valign='top'><b>Last Name</b></td>	
							<td valign='top'><b>Company</b></td>	
							<td valign='top'><b>Email</b></td>	
						</tr>
						<?
						//get all user contacts with email filled in
						$cntr=0;
                         	$sql="
          					select user_contacts.*,
          						(select option_values.fname from option_values where option_values.id=user_contacts.contact_type) as type_name
          					from user_contacts
          					where user_contacts.deleted=0						
          						and user_contacts.user_id='".(int) $_SESSION['user_id']."'
          						and user_contacts.email_address!=''
          					order by user_contacts.last_name asc, 
          						user_contacts.first_name asc, 
          						user_contacts.company_name asc, 
          						user_contacts.id asc
          				";	
          				$data = simple_query($sql);
                    		while($row = mysqli_fetch_array($data)) 
                    		{          			         			
                    			echo "
                    				<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd").";'>
                    					<td valign='top'>".trim($row['type_name'])."</td> 
                    					<td valign='top'><span onClick='user_email_addr(\"".trim($row['email_address'])."\",\"".trim($row['first_name']." ".$row['last_name'])."\");' style='color:#0000CC; cursor:pointer;'>".trim($row['first_name'])."</span></td> 
                    					<td valign='top'><span onClick='user_email_addr(\"".trim($row['email_address'])."\",\"".trim($row['first_name']." ".$row['last_name'])."\");' style='color:#0000CC; cursor:pointer;'>".trim($row['last_name'])."</span></td> 
                    					<td valign='top'>".trim($row['company_name'])."</td>  
                    					<td valign='top'>".trim($row['email_address'])."</td>
                    				</tr>       				
                    			";          			
                    			$cntr++;	
                    		}
						?>
						</table>
						<br>
						<div id='accident_email_app'></div>
						
						<div id='accident_email_log'></div>
						
					</td>
				</tr>				
				<?
				if($mrr_acc_id > 0)
				{
					//put damage items here					
					?>
     				<tr>
     					<td colspan='6'>
     						<br>
     						<div id='accident_uploads'></div>
     						<div id='accident_notes'></div>
     						<br>	
     						
     					</td>
     				</tr>     				
					<?
				}
				?>		
				</table>
				<input type='hidden' name='accident_id' id='accident_id' value="<?= $mrr_acc_id ?>">	  
				<!--
				ACC ID= <input type='text' name='accident_id2' id='accident_id2' value="<?= $mrr_acc_id ?> ">
				-->							
			</td>
		</tr>		
		</table>
	<?	
	}	
	?>
	</td>
</tr>
</table>

</form>
<script type='text/javascript'>
	var acc_id = $('#accident_id').val() ;
	$('#accident_id2').val(acc_id);
	
	<? 
		if($mrr_acc_id > 0) 
		{
			echo " create_note_section('#accident_notes', 11, $mrr_acc_id); "; 
			
			
			//echo " create_upload_section('#accident_uploads', 11, $mrr_acc_id); "; 			
			if($use_new_uploader > 0) 
			{ 
				echo " create_upload_section_alt('#upload_section', 11, $mrr_acc_id); "; 
			}
			else
			{
				echo " create_upload_section('#upload_section', 11, $mrr_acc_id); "; 
			}
		}
		
	?>
	
	function mrr_send_out_accident_email_msg()
	{
		id=parseInt($('#accident_id').val());
		if(id == 0)		return;				//no point to make a list yet.
		
		var email_to = $('#accident_email_addr').val();
		var email_name = $('#accident_email_name').val();
		
		var email_sub = $('#accident_email_sub').val();
		
		var email_msg = $('#accident_email_msg').val();	
		
		var email_sec0 = ($('#accident_email_desc0').is(':checked') ? 1 : 0);	
		var email_sec1 = ($('#accident_email_desc1').is(':checked') ? 1 : 0);		
		var email_sec2 = ($('#accident_email_desc2').is(':checked') ? 1 : 0);		
		var email_sec3 = ($('#accident_email_desc3').is(':checked') ? 1 : 0);	
		
		var email_prt0 = $('#accident_desc').val();	
		var email_prt1 = $('#injury_desc').val();	
		var email_prt2 = $('#driver_desc').val();	
		var email_prt3 = $('#notes_and_updates').val();
				
		if(email_sec0==0)	email_prt0="";
		if(email_sec1==0)	email_prt1="";
		if(email_sec2==0)	email_prt2="";
		if(email_sec3==0)	email_prt3="";
		
		var file_list="";
		var tot_files = parseInt($('#mrr_attatched_email_files').val());
		for(i=0; i<tot_files; i++)
        {
            if($('#mrr_file_'+i+'').is(':checked')) 
            {
                file_list=file_list + "; "+$('#mrr_file_'+i+'').val();
            }
        }
				
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_accident_email_app_send",
		   data: {
		   		"id":id,
		   		"msg_to":email_to,	
		   		"msg_name":email_name,
		   		"msg_sub":email_sub,		   		
		   		"msg_sec0":email_prt0,
		   		"msg_sec1":email_prt1,
		   		"msg_sec2":email_prt2,
		   		"msg_sec3":email_prt3,
                "msg_zip": file_list,
		   		"msg_body":email_msg
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		
		   		$.noticeAdd({text: "Accident Truck Report information sent to "+email_to+"."});
		   		
		   		mrr_list_email_log();
		   }
		});	
	}
	
	function user_email_addr(email_addr,email_namer)
	{
		var tmp_email=email_addr;
		var tmp_namer=email_namer;
		$('#accident_email_addr').val(tmp_email);
		$('#accident_email_name').val(tmp_namer);
	}
	
	function mrr_list_email_app()
	{		
		$('#accident_email_app').html('');
		id=parseInt($('#accident_id').val());
		if(id == 0)		return;				//no point to make a list yet.
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_accident_email_app",
		   data: {
		   		"id":id
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		
		   		lister=$(xml).find('Disp').text();
		   		$('#accident_email_app').html(lister);
		   }
		});	
	}
	function mrr_toggle_email_log(id)
	{
		$('.email_log_id_'+id+'').toggle();	
	}
	
	function mrr_list_email_log()
	{		
		mrr_list_email_app();
		
		$('#accident_email_log').html('');
		id=parseInt($('#accident_id').val());
		if(id == 0)		return;				//no point to make a list yet.
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_accident_email_log",
		   data: {
		   		"id":id
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		
		   		lister=$(xml).find('Disp').text();
		   		$('#accident_email_log').html(lister);
		   		$('.all_email_logs').hide();
		   }
		});	
	}
	
	function mrr_add_to_itemized_cost()
	{
		id=parseInt($('#accident_id').val());
		if(id == 0)
		{
			$.prompt('Sorry, you must save the Accident Report to add itemized costs.  Please save and try again.');
			return;
		} 
		
		catid=$('#ac_item_cat').val();
		desc=$('#ac_item_desc').val();
		cost=$('#ac_item_cost').val();
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_accident_reports_item_adder",
		   data: {
		   		"id":id,
		   		"desc":desc,
		   		"cost":cost,
		   		"catid":catid
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		
		   		$.noticeAdd({text: "Accident Cost Item has been added."});
		   		mrr_list_ac_items();
		   		
		   		$('#ac_item_cat').val(0);
		   		$('#ac_item_desc').val("");
		   		$('#ac_item_cost').val("0.00");
		   }
		});			
	}
	function mrr_list_ac_items()
	{		
		$('#accident_itemized_list').html('');
		id=parseInt($('#accident_id').val());
		if(id == 0)		return;				//no point to make a list yet.
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_accident_reports_item_lister",
		   data: {
		   		"id":id
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		
		   		lister=$(xml).find('Disp').text();
		   		$('#accident_itemized_list').html(lister);
		   }
		});	
	}
	function mrr_remove_ac_item(itemid)
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_accident_reports_item_remover",
		   data: {
		   		"id":itemid
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		
		   		$.noticeAdd({text: "Accident Cost Item has been removed."});
		   		mrr_list_ac_items();
		   }
		});	
	}
	
	$('#driver_id').change(function() {
		mrr_change_display_listing();
	});
	$('#truck_id').change(function() {
		mrr_change_display_listing();
	});
	$('#trailer_id').change(function() {
		mrr_change_display_listing();
	});
	/*
	$('#load_id').blur(function() {
		mrr_change_display_listing();
	});
	$('#dispatch_id').blur(function() {
		mrr_change_display_listing();
	});
	*/
	function mrr_change_display_listing()
	{
		var driver_x = 0;	//$('#driver_id').val() ;
		var truck_x = 0;	//$('#truck_id').val();
		var trailer_x = 0;	//$('#trailer_id').val();
		var load_x=0;		// $('#load_id').val() ;
		var dispatch_x=0;	//$('#dispatch_id').val();
		
		display_accidents(truck_x, trailer_x , driver_x, load_x, dispatch_x);	
	}
	
	//update maintenance request.
	$('#update_accident_button').click(function() {
				
		$.ajax({
			url: "ajax.php?cmd=mrr_add_accident_reports",
			type: "post",
			dataType: "xml",
			data: {
				//POST variables needed for "page" to load for XML output
				"accident_id": $('#accident_id').val(),
				"driver_id": $('#driver_id').val(),
				"truck_id": $('#truck_id').val(),
				"trailer_id": $('#trailer_id').val(),
				"dispatch_id": $('#dispatch_id').val(),
				"load_id": $('#load_id').val(),				
				"accident_date": $('#accident_date').val(),
				"claim_date": $('#claim_date').val(),
				"insurance_claim":  ($('#insurance_claim').is(':checked') ? 1 : 0),
				"insurance_covered":  ($('#insurance_covered').is(':checked') ? 1 : 0),
				"reviewed":  ($('#reviewed').is(':checked') ? 1 : 0),	
				"insurance_company": $('#insurance_company').val(),
				"accident_desc": $('#accident_desc').val(),
				"accident_cost": $('#accident_cost').val(),
				"accident_deductable": $('#accident_deductable').val(),
				"accident_downtime": $('#accident_downtime').val(),
				"injury_desc": $('#injury_desc').val(),
				"injury_cost": $('#injury_cost').val(),
				"injury_deductable": $('#injury_deductable').val(),
				"injury_downtime": $('#injury_downtime').val(),
				"driver_desc": $('#driver_desc').val(),
				"driver_cost": $('#driver_cost').val(),
				"driver_deductable": $('#driver_deductable').val(),
				"driver_downtime": $('#driver_downtime').val(),
				"maint_id": $('#maint_id').val(),
				"completed_date": $('#completed_date').val(),
				"accident_number": $('#accident_number').val(),
				"notes_and_updates": $('#notes_and_updates').val(),
				"active": 1	// ($('#active').is(':checked') ? 1 : 0)	
			},
			error: function() {
				alert('Error saving Accident Report.');
			},
			success: function(xml) {
				current_id = parseInt($(xml).find('AccidentID').text());
				//if(current_id) display_maint_line_item( current_id );
				$.noticeAdd({text: "Accident Report has been updated."});
				//$('#table_section_for_new_form').html('');
				
				//$('#request_new_items').css("display","block");
				//if(current_id>0)	$('#request_new_items').show();
				acc_id=current_id;
				$('#accident_id').val(acc_id);
				$('#accident_id2').val(acc_id);
				$('#accident_desc').focus();	
				
				mrr_destroy_upload_section('#note_section'); 
				create_note_section('#note_section', 11, acc_id);
				
				mrr_destroy_upload_section('#upload_section'); 
				create_upload_section('#upload_section', 11, acc_id);	
				
				//display_files(11, acc_id);
				mrr_list_email_log();
			}
		});
		mrr_change_display_listing();		
	});
	
	$('.datepicker').datepicker();
	$().ready(function() {
		mrr_change_display_listing();					
	});
	
	function confirm_del_accident(id)
	{
		$.prompt("Are you sure you want to <span class='alert'>delete</span> this accident truck?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						window.location = 'accident_trucks.php?did='+id;
					}
				}
			}
		);	
	}
	
	function display_accidents(truck, trailer, driver, loadid, dispatch) {
     		$.ajax({
     			url: "ajax.php?cmd=mrr_list_accident_reports",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output 
     				"truck_id": truck,
     				"trailer_id": trailer,
     				"driver_id": driver,
     				"load_id": loadid,
     				"dispatch_id": dispatch
     			},
     			error: function() {
     				alert('Error listing accidents.');
     			},
     			success: function(xml) {
     				$('#accident_listing').html('');
     				$('#completed_accident_listing').html('');
     				
     				temp_holder="";
     				temp_holder+="<table width='98%' class='standard12 tablesorter'>";
     				temp_holder+="<thead><tr>";
     				temp_holder+="<th><b>Accident</b></th>";
     				temp_holder+="<th><b>Description</b></th>";
     				temp_holder+="<th><b>Driver</b></th>"; 
     				temp_holder+="<th><b>Truck</b></th>"; 	
     				temp_holder+="<th><b>Trailer</b></th>"; 	
     				temp_holder+="<th><b>Load</b></th>"; 	
     				temp_holder+="<th><b>Dispatch</b></th>"; 	
     				temp_holder+="<th><b>Date</b></th>";
                    temp_holder+="<th align='right' title='How much Conard paid'><b>Conard</b></th>";
                    temp_holder+="<th align='right' title='How much Insurance paid (after the deductible)'><b>Insurance</b></th>";
     				temp_holder+="<th><b></b></th>"; 
     				temp_holder+="</tr></thead><tbody>";
     				    				
					temp_holder2="";
     				temp_holder2+="<table width='98%' class='standard12 tablesorter'>";
     				temp_holder2+="<thead><tr>";
     				temp_holder2+="<th><b>Accident</b></th>";
     				temp_holder2+="<th><b>Description</b></th>";
     				temp_holder2+="<th><b>Driver</b></th>"; 
     				temp_holder2+="<th><b>Truck</b></th>"; 	
     				temp_holder2+="<th><b>Trailer</b></th>"; 	
     				temp_holder2+="<th><b>Load</b></th>"; 	
     				temp_holder2+="<th><b>Dispatch</b></th>"; 	
     				temp_holder2+="<th><b>Date</b></th>";
                    temp_holder2+="<th align='right' title='How much Conard paid'><b>Conard</b></th>";
                    temp_holder2+="<th align='right' title='How much Insurance paid (after the deductible)'><b>Insurance</b></th>";
                    temp_holder2+="<th><b></b></th>";
     				temp_holder2+="</tr></thead><tbody>";

                    var active_cnt=0;
                    var complete_cnt=0;
                    
     				var a_cost=0.00;
     				var a_deduct=0.00;
     				
     				var open_cost=0.00;
                    var open_deduct=0.00;
                    var open_tot_cost=0.00;
                    var open_tot_deduct=0.00;

                    var closed_cost=0.00;
                    var closed_deduct=0.00;
                    var closed_tot_cost=0.00;
                    var closed_tot_deduct=0.00;
     				
     				$(xml).find('Accident').each(function() {
     					
     					use_num=""+$(this).find('AccidentNumber').text()+"";    					
     					
     					if($(this).find('AccidentNumber').text()=='')	use_num=$(this).find('AccidentID').text();	
     					
     					if($(this).find('AccidentCompleted').text()=='')
     					{
                            a_cost=parseFloat($(this).find('AccidentACost').text());
                            a_deduct=parseFloat($(this).find('AccidentADeduct').text());
                            
                            if(parseInt($(this).find('AccidentID').text())< 181)
                            {
                                a_cost=0.00;
                                a_deduct=0.00;
                            }
                            

                            if(a_cost > a_deduct)
                            {
                                open_cost=a_deduct;                 //paid by Conard
                                open_deduct=a_cost - a_deduct;      //extra amount covered by insurance
                            }
                            else
                            {
                                open_cost=a_cost;                   //FULL amount paid by Conard
                                open_deduct=0.00;
                            }

                            open_tot_cost+=open_cost;
                            open_tot_deduct+=open_deduct;

                            active_cnt++;
                            
                            temp_holder+="<tr class='accident_"+$(this).find('AccidentID').text()+"'>";
     						temp_holder+="<td><span title='Accident ID="+$(this).find('AccidentID').text()+"'>"+use_num+"</span></td>";
     						temp_holder+="<td><span title='Accident ID="+$(this).find('AccidentID').text()+"'>"+$(this).find('AccidentLink').text()+"</span></td>";
     						temp_holder+="<td>"+$(this).find('AccidentDriverName').text()+"</td>";
     						temp_holder+="<td>"+$(this).find('AccidentTruckName').text()+"</td>";
     						temp_holder+="<td>"+$(this).find('AccidentTrailerName').text()+"</td>";
     						temp_holder+="<td>"+$(this).find('AccidentLoad').text()+"</td>";
     						temp_holder+="<td>"+$(this).find('AccidentDispatch').text()+"</td>";
     						temp_holder+="<td>"+$(this).find('AccidentDate').text()+"</td>";
                            temp_holder+="<td align='right'>"+formatCurrency(open_cost)+"</td>";
                            temp_holder+="<td align='right'>"+formatCurrency(open_deduct)+"</td>";
     						temp_holder+="<td>"+$(this).find('AccidentTrash').text()+"</td></tr>";
     						
     						//$('#accident_listing_count').html('');
     						//$('#accident_listing_count').append(""+$(this).find('AccidentCount').text()+" Opened/Active Accident Truck(s)");
     						     					     					
     						temp_holder+="</tr>";
     					}
     					else
     					{
                            a_cost=parseFloat($(this).find('AccidentACost').text());
                            a_deduct=parseFloat($(this).find('AccidentADeduct').text());

                            if(parseInt($(this).find('AccidentID').text())< 181)
                            {
                                a_cost=0.00;
                                a_deduct=0.00;
                            }

                            if(a_cost > a_deduct)
                            {
                                closed_cost=a_deduct;                 //paid by Conard
                                closed_deduct=a_cost - a_deduct;      //extra amount covered by insurance
                            }
                            else
                            {
                                closed_cost=a_cost;                   //FULL amount paid by Conard
                                closed_deduct=0.00;
                            }

                            closed_tot_cost+=closed_cost;
                            closed_tot_deduct+=closed_deduct;

                            complete_cnt++;

                            temp_holder2+="<tr class='accident_"+$(this).find('AccidentID').text()+"'>";
     						temp_holder2+="<td><span title='Accident ID="+$(this).find('AccidentID').text()+"'>"+use_num+"</span></td>";
     						temp_holder2+="<td><span title='Accident ID="+$(this).find('AccidentID').text()+"'>"+$(this).find('AccidentLink').text()+"</span></td>";
     						temp_holder2+="<td>"+$(this).find('AccidentDriverName').text()+"</td>";
     						temp_holder2+="<td>"+$(this).find('AccidentTruckName').text()+"</td>";
     						temp_holder2+="<td>"+$(this).find('AccidentTrailerName').text()+"</td>";
     						temp_holder2+="<td>"+$(this).find('AccidentLoad').text()+"</td>";
     						temp_holder2+="<td>"+$(this).find('AccidentDispatch').text()+"</td>";
     						temp_holder2+="<td>"+$(this).find('AccidentDate').text()+"</td>";
                            temp_holder2+="<td align='right'>"+formatCurrency(closed_cost)+"</td>";
                            temp_holder2+="<td align='right'>"+formatCurrency(closed_deduct)+"</td>";
     						temp_holder2+="<td>"+$(this).find('AccidentTrash').text()+"</td></tr>";
     						
     						//$('#completed_accident_listing_count').html('');
     						//$('#completed_accident_listing_count').append(""+$(this).find('AccidentCount').text()+" Closed/Completed Accident Truck(s)");
     						     					     					
     						temp_holder2+="</tr>";	
     					}
     				});
     				
                    $('#accident_listing_count').html('');
                    $('#accident_listing_count').append(""+active_cnt+" Opened/Active Accident Truck(s)");

                    $('#completed_accident_listing_count').html('');
                    $('#completed_accident_listing_count').append(""+complete_cnt+" Closed/Completed Accident Truck(s)");
     				
                    temp_holder+="</tbody><tfoot><tr class='accident_"+$(this).find('AccidentID').text()+"'>";
                    temp_holder+="<td>Total</td>";
                    temp_holder+="<td>&nbsp;</td>";
                    temp_holder+="<td>&nbsp;</td>";
                    temp_holder+="<td>&nbsp;</td>";
                    temp_holder+="<td>&nbsp;</td>";
                    temp_holder+="<td>&nbsp;</td>";
                    temp_holder+="<td>&nbsp;</td>";
                    temp_holder+="<td>&nbsp;</td>";
                    temp_holder+="<td align='right'>"+formatCurrency(open_tot_cost)+"</td>";
                    temp_holder+="<td align='right'>"+formatCurrency(open_tot_deduct)+"</td>";
                    temp_holder+="<td>&nbsp;</td>";
     				temp_holder+="</tr></tfoot></table>";

                    temp_holder2+="</tbody><tfoot><tr class='accident_"+$(this).find('AccidentID').text()+"'>";
                    temp_holder2+="<td>Total</td>";
                    temp_holder2+="<td>&nbsp;</td>";
                    temp_holder2+="<td>&nbsp;</td>";
                    temp_holder2+="<td>&nbsp;</td>";
                    temp_holder2+="<td>&nbsp;</td>";
                    temp_holder2+="<td>&nbsp;</td>";
                    temp_holder2+="<td>&nbsp;</td>";
                    temp_holder2+="<td>&nbsp;</td>";
                    temp_holder2+="<td align='right'>"+formatCurrency(closed_tot_cost)+"</td>";
                    temp_holder2+="<td align='right'>"+formatCurrency(closed_tot_deduct)+"</td>";
                    temp_holder2+="<td>&nbsp;</td>";
     				temp_holder2+="</tr></tfoot></table>";
     				
     				$('#accident_listing').append(temp_holder);
     				$('#completed_accident_listing').append(temp_holder2);

                    $(".tablesorter").tablesorter({textExtraction: 'complex'});
     			}
     		});
     		
     }
     function load_accident_truck(myid)
	{
		var accident_id=myid;
		
		acc_id=accident_id;
		
		$('#accident_id').val(acc_id);	
		$('#accident_id2').val(acc_id);	
		
		if(accident_id==0)
		{
				$('#accident_id').val(0);	
				$('#accident_id2').val(0);		
				$('#driver_id').val(0);
				$('#truck_id').val(0);
				$('#trailer_id').val(0);
				$('#dispatch_id').val(0);
				$('#load_id').val(0);				
				$('#accident_date').val('');
				$('#claim_date').val('');				
				$('#insurance_claim').attr('checked','');	
				$('#insurance_covered').attr('checked','');	
				$('#reviewed').attr('checked','');	
				$('#insurance_company').val('');
				$('#accident_desc').val('');
				$('#accident_cost').val('0.00');
				$('#accident_deductable').val('0.00');
				$('#accident_downtime').val('0.00');
				$('#injury_desc').val('');
				$('#injury_cost').val('0.00');
				$('#injury_deductable').val('0.00');
				$('#injury_downtime').val('0.00');
				$('#driver_desc').val('');
				$('#driver_cost').val('0.00');
				$('#driver_deductable').val('0.00');
				$('#driver_downtime').val('0.00');
				$('#maint_id').val(0);
				//$('#active').attr('checked','checked');	
				//$('#active').attr('checked',($(this).find('RequestItemActive').text() == 1 ? 'checked' : ''));	
				$('#completed_date').val('');
				$('#notes_and_updates').val('');
				$('#accident_number').val('');
				
				mrr_destroy_upload_section('#note_section'); 
				create_note_section('#note_section', 11, 0);
				
				mrr_destroy_upload_section('#upload_section'); 				
				create_upload_section('#upload_section', 11, 0);	
				
				document.getElementById('iframe_doc_loader').src="mrr_uploader_hack.php?section_id=11&id="+accident_id+"";
				display_files(11, accident_id);
				
				mrr_list_ac_items();
				mrr_list_email_log();
				
				$('#accident_desc').focus();					
		}
		else
		{	
			$.ajax({
				url: "ajax.php?cmd=mrr_get_accident_reports",
				type: "post",
				dataType: "xml",
				data: {
					//POST variables needed for "page" to load for XML output 
					"accident_id": accident_id				
				},
				error: function() {
					alert('Error listing Accident Report '+accident_id+'');
				},
				success: function(xml) {
							
					$(xml).find('Accident').each(function() {
											
						mrr_acc_id=$(this).find('AccidentID').text();
						$('#accident_id').val( $(this).find('AccidentID').text()  );	
						$('#accident_id2').val( $(this).find('AccidentID').text()  );		
          				$('#driver_id').val( $(this).find('AccidentDriver').text() );
          				$('#truck_id').val( $(this).find('AccidentTruck').text() );
          				$('#trailer_id').val( $(this).find('AccidentTrailer').text() );
          				$('#dispatch_id').val( $(this).find('AccidentDispatch').text() );
          				$('#load_id').val( $(this).find('AccidentLoad').text() );				
          				$('#accident_date').val( $(this).find('AccidentDate').text() );
          				$('#claim_date').val( $(this).find('AccidentClaimDate').text() );				
          				$('#insurance_claim').attr('checked',($(this).find('AccidentInsClaim').text() == 1 ? 'checked' : ''));	
          				$('#insurance_covered').attr('checked',($(this).find('AccidentInsCover').text() == 1 ? 'checked' : ''));	
          				$('#reviewed').attr('checked',($(this).find('AccidentReviewed').text() == 1 ? 'checked' : ''));
          				$('#insurance_company').val( $(this).find('AccidentInsComp').text() );
          				$('#accident_desc').val( $(this).find('AccidentADesc').text() );
          				$('#accident_cost').val( $(this).find('AccidentACost').text() );
          				$('#accident_deductable').val( $(this).find('AccidentADeduct').text() );
          				$('#accident_downtime').val( $(this).find('AccidentADowntime').text() );
          				$('#injury_desc').val( $(this).find('AccidentIDesc').text() );
          				$('#injury_cost').val( $(this).find('AccidentICost').text() );
          				$('#injury_deductable').val( $(this).find('AccidentIDeduct').text() );
          				$('#injury_downtime').val( $(this).find('AccidentIDowntime').text() );
          				$('#driver_desc').val( $(this).find('AccidentDDesc').text() );
          				$('#driver_cost').val( $(this).find('AccidentDCost').text() );
          				$('#driver_deductable').val( $(this).find('AccidentDDeduct').text() );
          				$('#driver_downtime').val( $(this).find('AccidentDDowntime').text() );
          				$('#maint_id').val( $(this).find('AccidentMaintID').text() );
          				//$('#active').attr('checked',($(this).find('AccidentActive').text() == 1 ? 'checked' : ''));
          				$('#completed_date').val( $(this).find('AccidentCompleted').text() );	
          				$('#notes_and_updates').val( $(this).find('NotesUpdates').text() );
          				$('#accident_number').val($(this).find('AccidentNumber').text() );
          				
          				mrr_list_ac_items();
          				
						mrr_destroy_upload_section('#note_section'); 
						create_note_section('#note_section', 11, mrr_acc_id);
						
          				//mrr_destroy_upload_section('#upload_section');      				
						//create_upload_section('#upload_section', 11, mrr_acc_id);	
						
						
						document.getElementById('iframe_doc_loader').src="mrr_uploader_hack.php?section_id=11&id="+accident_id+"";
						display_files(11, accident_id);
						
						mrr_list_email_log();					
					});	
					
				}
			});
		}
		mrr_change_display_listing();		
	}
     function confirm_del_confirm_del_accident(myid) {
		$.prompt("Are you sure you want to delete this accident truck?", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {
					$.ajax({
          			   type: "POST",
          			   url: "ajax.php?cmd=mrr_kill_accident_reports",
          			   data: {
          			   		"acc_id":myid
          			   		},
          			   dataType: "xml",
          			   cache:false,
          			   success: function(xml) {
          			   		newid=$(xml).find('AccidentID').text();
               				if(newid > 0)
               				{
               					$.noticeAdd({text: "Accident truck has been removed."});
               					
               					$('.accident_'+myid+'').html('');		
               					$('#accident_id').val(0);	
								$('#accident_id2').val(0);	
																
								mrr_destroy_upload_section('#note_section'); 
								create_note_section('#note_section', 11, 0);				
								
								//mrr_destroy_upload_section('#upload_section'); 	
								//create_upload_section('#upload_section', 11, 0);
								
								display_files(11, 0);
								mrr_list_email_log();
               				} 
          			   }
          			});	
				}
			}
		});
	}
</script>
<? include('footer.php') ?>