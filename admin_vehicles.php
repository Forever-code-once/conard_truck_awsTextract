<? include('application.php') ?>
<? $admin_page = 1 ?>
<?
	$sql = "update trucks set active=1 where active>1";
	simple_query($sql);
	
	//Company Vehicle section...uses same tables as the trucks do but very limited section.
	
	if(isset($_GET['pmi_fed']))
	{
		$res=mrr_pmi_fed_trucks_due_soon(7,0,1);
		//echo "<h2>Trailer PMI/FED Due Report: (".$res['num']." Found)</h2><br>".$res['report']."<br>";	
	}
	
	$_POST['no_insurance']=0;
	
	//duplication effort moved to separate page.  Retaining in case a random link has this encoded.
	if(isset($_GET['duplicate'])) 
	{	//Added Nov 2015...MRR
		$dup_id = duplicate_row('trucks', $_GET['duplicate']);
				
		$sql = "
			update trucks set
				name_truck= CONCAT('00',name_truck),
				linedate_aquired = NOW(),
				linedate_returned = '0000-00-00 00:00:00',
				sicap_coa_created='0',
				pn_odometer_offset='0',
				license_plate_no='',
				own_op_ins_flag=0,				
				own_op_ins_number='',
				linedate_own_op_ins = '0000-00-00 00:00:00',
				own_op_acc_ins_flag=0,
				linedate_own_op_acc_ins = '0000-00-00 00:00:00',
				repairs_pending_list='',
				repairs_pending=0,
				repairs_pending_date_opened = '0000-00-00 00:00:00',
				repairs_pending_date_inspect = '0000-00-00 00:00:00',
				repairs_pending_date_repair = '0000-00-00 00:00:00',
				repairs_pending_date_closed = '0000-00-00 00:00:00',
				repairs_pending_made=0,
				repairs_pending_internal=0,
				vin='',
				use_pm_oil_report=0,
				made_by_user_id='".(int) $_SESSION['user_id']."',
				active='0',
				deleted='0'
				
			where id = '".sql_friendly($dup_id)."'
		";
		simple_query($sql);
		
		header("Location: admin_vehicles.php?id=".$dup_id);
		die;
	}
	
	
	if(isset($_GET['did'])) {
		$sql = "
			update trucks
			
			set	deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_delete = simple_query($sql);
		
		$mrr_activity_log_notes.="Deleted truck ".$_GET['did'].". ";
		
		mrr_send_insurance_information($_GET['did'],0,0,$_SESSION['user_id']);		//truck,trailer,driver,user
	}

	if(isset($_POST['truck_id_list'])) {
		foreach($_POST['truck_id_list'] as $id) {
			//echo $id." | ".$_POST['monthly_cost_'.$id]."<br>";
			$sql = "
				update trucks set
					monthly_cost = '".sql_friendly($_POST['monthly_cost_'.$id])."',
					prepass = '".sql_friendly($_POST['prepass_'.$id])."'
				where id = '".sql_friendly($id)."'
			";
			simple_query($sql);
		}
	}
	
	if(isset($_GET['new'])) {
		$_GET['id']=0;
	}

	if(isset($_POST['name_truck'])) 
	{		
		$_POST['name_truck'] = trim($_POST['name_truck']);		
		
		
		$invalid_id=0;		//mrr_ensure_truck_name_unique($_GET['id'],$_POST['name_truck']);
		if($invalid_id > 0)
		{	//redirect to existing truck if it already exists
			header("Location: $_SERVER[SCRIPT_NAME]?id=$invalid_id&duplicate=1");
			die;	
		}
		
		if($_GET['id'] == 0) 
		{
			$sql = "
				insert into trucks
					(name_truck,prepass,tire_size,made_by_user_id,repairs_pending,repairs_pending_list)
					
				values ('New Truck','','LP 22.5','".(int) $_SESSION['user_id']."',0,'');
			";
			simple_query($sql);
			$_GET['id'] = mysqli_insert_id($datasource);
		}		
		
		$sql = "
			select name_truck
			from trucks
			where id = '".sql_friendly($_GET['id'])."'
		";
		$data_old = simple_query($sql);
		$row_old = mysqli_fetch_array($data_old);
		
		
		/* update in progress */
		if(!isset($_POST['pn_odometer_offset']))		$_POST['pn_odometer_offset']="0.00";
		if($_POST['pn_odometer_offset']=="")			$_POST['pn_odometer_offset']="0.00";
		
		$use_op_ins_due='';
		if($_POST['linedate_own_op_ins'] != '') 	$use_op_ins_due = date("Y-m-d", strtotime($_POST['linedate_own_op_ins']));
		$use_op_acc_ins_due='';
		if($_POST['linedate_own_op_acc_ins'] != '') 	$use_op_acc_ins_due = date("Y-m-d", strtotime($_POST['linedate_own_op_acc_ins']));
		
		if($_POST['pm_miles_interval'] =="")			$_POST['pm_miles_interval']=0;
		if($_POST['pm_miles_last_oil'] =="")			$_POST['pm_miles_last_oil']=0;		
		if($_POST['pm_miles_last_date']=="")			$_POST['pm_miles_last_date']="";	
				
		$sql = "
			update trucks set
				name_truck = '".sql_friendly($_POST['name_truck'])."',
				monthly_cost = '".sql_friendly(money_strip($_POST['monthly_cost']))."',
				truck_year = '".sql_friendly($_POST['truck_year'])."',
				truck_make = '".sql_friendly($_POST['truck_make'])."',
				truck_model = '".sql_friendly($_POST['truck_model'])."',
				cab_type = '".sql_friendly($_POST['cab_type'])."',
				vin = '".sql_friendly(strtoupper($_POST['vin']))."',
				license_plate_no = '".sql_friendly($_POST['license_plate_no'])."',
				leased_from = '".sql_friendly($_POST['leased_from'])."',
				rental = '".(isset($_POST['rental']) ? '1' : '0')."',
				
				company_owned = '".(isset($_POST['company_owned']) ? '1' : '0') ."',
				company_admin_vehicle = '".(isset($_POST['company_admin_vehicle']) ? '1' : '0') ."',
				
				in_the_shop = '".(isset($_POST['in_the_shop']) ? '1' : '0')."',
				in_body_shop = '".(isset($_POST['in_body_shop']) ? '1' : '0')."',
				hold_for_driver = '".(isset($_POST['hold_for_driver']) ? '1' : '0')."',
				
				own_op_ins_flag='".(isset($_POST['own_op_ins_flag']) ? 1 : 0)."',
				own_op_ins_number='".sql_friendly($_POST['own_op_ins_number'])."',
				linedate_own_op_ins = '".($use_op_ins_due == '' ? '0000-00-00' : sql_friendly($use_op_ins_due))."',
				
				own_op_acc_ins_flag='".(isset($_POST['own_op_acc_ins_flag']) ? 1 : 0)."',
				linedate_own_op_acc_ins = '".($use_op_acc_ins_due == '' ? '0000-00-00' : sql_friendly($use_op_acc_ins_due))."',
				
				in_shop_note	= '".sql_friendly(trim($_POST['in_shop_note']))."',
				in_body_note	= '".sql_friendly(trim($_POST['in_body_note']))."',
				on_hold_note	= '".sql_friendly(trim($_POST['on_hold_note']))."',
				
				owner_operated	=  '".(isset($_POST['owner_operated']) ? '1' : '0')."',
				
				tire_size		= '".sql_friendly(trim($_POST['tire_size']))."',
				gallon_size	= '".sql_friendly((int) trim($_POST['gallon_size']))."',
				
				pm_inspection_note='".sql_friendly(trim($_POST['pm_inspection_note']))."',
				".(trim($_POST['pm_inspection_date'])!=""  ? "pm_inspection_date='".date("Y-m-d",strtotime($_POST['pm_inspection_date']))." 00:00:00'," :"")."
				".(trim($_POST['fd_inspection_date'])!=""  ? "fd_inspection_date='".date("Y-m-d",strtotime($_POST['fd_inspection_date']))." 00:00:00'," :"")."
				
				pm_miles_interval='".sql_friendly(money_strip($_POST['pm_miles_interval']))."',
				pm_miles_last_oil='".sql_friendly(money_strip($_POST['pm_miles_last_oil']))."',
				pm_miles_last_date='".(trim($_POST['pm_miles_last_date'])!=""  ? "".date("Y-m-d",strtotime($_POST['pm_miles_last_date']))." 00:00:00" :"0000-00-00 00:00:00")."',
				
				use_pm_oil_report='".(isset($_POST['use_pm_oil_report']) ? 1 : 0)."',
				
				repairs_pending='".(isset($_POST['repairs_pending']) ? '1' : '0') ."',
				repairs_pending_list='".sql_friendly(trim($_POST['repairs_pending_list']))."',
				
				repairs_pending_date_opened = '".(trim($_POST['repairs_pending_date_opened'])!=""  ? "".date("Y-m-d",strtotime($_POST['repairs_pending_date_opened']))." 00:00:00" :"0000-00-00 00:00:00")."',
				repairs_pending_date_inspect = '".(trim($_POST['repairs_pending_date_inspect'])!=""  ? "".date("Y-m-d",strtotime($_POST['repairs_pending_date_inspect']))." 00:00:00" :"0000-00-00 00:00:00")."',
				repairs_pending_date_repair = '".(trim($_POST['repairs_pending_date_repair'])!=""  ? "".date("Y-m-d",strtotime($_POST['repairs_pending_date_repair']))." 00:00:00" :"0000-00-00 00:00:00")."',
				repairs_pending_date_closed = '".(trim($_POST['repairs_pending_date_closed'])!=""  ? "".date("Y-m-d",strtotime($_POST['repairs_pending_date_closed']))." 00:00:00" :"0000-00-00 00:00:00")."',
				repairs_pending_made='".(isset($_POST['repairs_pending_made']) ? '1' : '0') ."',
				repairs_pending_internal='".(isset($_POST['repairs_pending_internal']) ? '1' : '0') ."',
				
				camera_installed = '".(isset($_POST['camera_installed']) ? '1' : '0')."',
				fubar_truck = '".(isset($_POST['fubar_truck']) ? '1' : '0')."',
				automatic_transmission = '".(isset($_POST['automatic_transmission']) ? '1' : '0')."',
				no_insurance = '0',
				prepass = '".sql_friendly(trim($_POST['prepass']))."',
				insurance_exclude = '".(isset($_POST['insurance_exclude']) ? '1' : '0')."',
				active_cnt_exclude = '".(isset($_POST['active_cnt_exclude']) ? '1' : '0')."',
				apu_number = '".sql_friendly($_POST['apu_number'])."',
				apu_serial = '".sql_friendly($_POST['apu_serial'])."',
				dvr_serial = '".sql_friendly($_POST['dvr_serial'])."',
				apu_value = '".sql_friendly(money_strip($_POST['apu_value']))."',
				peoplenet_tracking = '".(isset($_POST['peoplenet_tracking']) ? '1' : '0') ."',
				pn_odometer_offset = '".sql_friendly(money_strip($_POST['pn_odometer_offset']))."'			
				
			where id = '".sql_friendly($_GET['id'])."'
		";	//".(isset($_POST['no_insurance']) ? '1' : '0') ."
		
		$data = simple_query($sql);
		
		
		if($_POST['attached_driver_id'] > 0 && $_GET['id'] > 0)
		{
			//first clear out all the other drivers linked to this truck...
			$sqlu="update drivers set attached_truck_id='0' where attached_truck_id='".sql_friendly($_GET['id'])."' and attached_truck_id>0";
			simple_query($sqlu);
			
			//next,link the selected driver to this truck...NO DISPATCHES WILL BE CHANGED, ONLY THE TRUCK LINK.			
			$sqlu="update drivers set attached_truck_id='".sql_friendly($_GET['id'])."' where id='".sql_friendly($_POST['attached_driver_id'])."'";
			simple_query($sqlu);
		}
		elseif($_POST['attached_driver_id'] == 0 && $_GET['id'] > 0)
		{
			//clear out all the other drivers linked to this truck...
			$sqlu="update drivers set attached_truck_id='0' where attached_truck_id='".sql_friendly($_GET['id'])."'";
			simple_query($sqlu);
		}
		
		//second driver
		if($_POST['attached2_driver_id'] > 0 && $_GET['id'] > 0)
		{
			//first clear out all the other drivers linked to this truck...
			$sqlu="update drivers set attached2_truck_id='0' where attached2_truck_id='".sql_friendly($_GET['id'])."' and attached2_truck_id>0";
			simple_query($sqlu);
			
			//next,link the selected driver to this truck...NO DISPATCHES WILL BE CHANGED, ONLY THE TRUCK LINK.			
			$sqlu="update drivers set attached2_truck_id='".sql_friendly($_GET['id'])."' where id='".sql_friendly($_POST['attached2_driver_id'])."'";
			simple_query($sqlu);
		}
		elseif($_POST['attached2_driver_id'] == 0 && $_GET['id'] > 0)
		{
			//clear out all the other drivers linked to this truck...
			$sqlu="update drivers set attached2_truck_id='0' where attached2_truck_id='".sql_friendly($_GET['id'])."'";
			simple_query($sqlu);
		}
		
		if($_POST['attached_driver_id'] > 0 && $_POST['attached2_driver_id'] == $_POST['attached_driver_id'] && $_GET['id'] > 0)
		{
			//driver slot 1 and 2 are the same, so keep slot 1 and clear slot 2.		
			$sqlu="update drivers set attached2_truck_id='0' where attached_truck_id='".sql_friendly($_GET['id'])."' and id='".sql_friendly($_POST['attached2_driver_id'])."'";
			simple_query($sqlu);
		}
		
		
		$mrr_activity_log_notes.="Updated truck ".$_GET['id']." info. ";	
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,$_GET['id'],0,0,0,0,"Updated truck ".$_GET['id']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
			
		
		$mrr_rental_flagger=0;				if(isset($_POST['rental']))		$mrr_rental_flagger=1;
		
		if($defaultsarray['sicap_integration'] == 1 && ($row_old['name_truck'] != $_POST['name_truck'] || $row_old['rental'] != $mrr_rental_flagger)) 
			sicap_update_trucks($_GET['id'], $row_old['name_truck'],false,$mrr_rental_flagger);
			
		if(isset($_POST['coa_updates']))
		{	//update COAs anyway.
			sicap_update_trucks($_GET['id'], $row_old['name_truck'],false,$mrr_rental_flagger);
		}
		
		$eval_inactive=0;
		if(isset($_POST['equipment_history_array'])) 
		{
			foreach($_POST['equipment_history_array'] as $value) 
			{
				if(strtolower($_POST['linedate_aquired_'.$value]) == 'deleted' || $_POST['linedate_aquired_'.$value] == '') 
				{
					$sql = "
						update equipment_history
						set deleted = 1
						where id = '".sql_friendly($value)."'
					";
					simple_query($sql);
				} 
				else 
				{					
					$equipment_val = money_strip($_POST['equipment_value_'.$value]);
					$miles_pickup = money_strip($_POST['miles_pickup_'.$value]);
					$miles_dropoff = money_strip($_POST['miles_dropoff_'.$value]);
					
					$sql = "
						update equipment_history
						set linedate_aquired = '".date("Y-m-d", strtotime($_POST['linedate_aquired_'.$value]))."',
							linedate_returned = '".($_POST['linedate_returned_'.$value] != '' ? date("Y-m-d", strtotime($_POST['linedate_returned_'.$value])) : "0000-00-00")."',
							equipment_value = '".($equipment_val != '' && is_numeric($equipment_val) ? $equipment_val : "0")."',
							miles_pickup = '".($miles_pickup != '' && is_numeric($miles_pickup) ? $miles_pickup : "0")."',
							miles_dropoff = '".($miles_dropoff != '' && is_numeric($miles_dropoff) ? $miles_dropoff : "0")."',
							replacement = '".(isset($_POST['replacement_'.$value]) ? '1' : '0')."',
							replacement_xref_id = '".(isset($_POST['replacement_'.$value]) ? sql_friendly($_POST['replacement_xref_id_'.$value]) : '0')."'
						
						where id = '".sql_friendly($value)."'
					";
					simple_query($sql);
					
					if($_POST['linedate_returned_'.$value] != '')	$eval_inactive=1;
					elseif($_POST['linedate_returned_'.$value] == '')	$eval_inactive=0;
					if($eval_inactive > 0)
					{
						update_equipment_active_flag($_GET['id'], 1);
						mrr_update_truck_active_flag($_GET['id'],0);	
					}
					else
					{
						mrr_update_truck_active_flag($_GET['id'],1);	
					}	
					
				}
			}
			
		}
		if($eval_inactive == 0)
		{
			update_equipment_active_flag($_GET['id'], 1);
		}
		
		if($_POST['new_linedate_aquired'] != '' && $_POST['add_equipment_history_flag'] == 1) {
			
			$sql = "
				insert into equipment_history
					(equipment_type_id,
					equipment_id,
					equipment_value,
					linedate_added,
					linedate_aquired,
					linedate_returned,
					miles_pickup,
					miles_dropoff,
					replacement,
					replacement_xref_id)
					
				values (1,
					'".sql_friendly($_GET['id'])."',
					'".(money_strip($_POST['new_equipment_value']) != '' ? sql_friendly(money_strip($_POST['new_equipment_value'])) : "0")."',
					now(),
					'".($_POST['new_linedate_aquired'] != '' ? date("Y-m-d", strtotime($_POST['new_linedate_aquired'])) : "0000-00-00")."',
					'".($_POST['new_linedate_returned'] != '' ? date("Y-m-d", strtotime($_POST['new_linedate_returned'])) : "0000-00-00")."',
					'".(money_strip($_POST['new_miles_pickup']) != '' ? sql_friendly(money_strip($_POST['new_miles_pickup'])) : "0")."',
					'".(money_strip($_POST['new_miles_dropoff']) != '' ? sql_friendly(money_strip($_POST['new_miles_dropoff'])) : "0")."',
					'".(isset($_POST['new_replacement']) ? '1' : '0')."',
					'".(isset($_POST['new_replacement']) ? $_POST['new_xref_id'] : '0')."')
			";
			simple_query($sql);
		}
		update_replacement_flag($_GET['id'], 1);
		update_equipment_active_flag($_GET['id'], 1);
		
		if(isset($_POST['send_insurance_report']))
		{
			$reporter=mrr_send_insurance_information($_GET['id'],0,0,$_SESSION['user_id']);		//truck,trailer,driver,user
			//die($reporter);		
		}
		header("Location: $_SERVER[SCRIPT_NAME]?id=$_GET[id]");
		die;
		
	}
		
	
	$mrr_dup_msg="";
	if(isset($_GET['duplicate']))
	{
		$mrr_dup_msg="<span class='alert'><b>Attempting to save a Duplicate Truck... it is already in the system.<br>Redirected to this existing truck.</b></span>";
	}
	/*
	if(isset($_GET['new'])) {
		$sql = "
			insert into trucks
				(name_truck)
				
			values ('New Truck')
		";
		$data = simple_query($sql);
		header("Location: $SCRIPT_NAME?id=".mysql_insert_id());
		die();
	}
	*/
	
	if(!isset($_POST['filter_active']))		$_POST['filter_active']=1;
	
	if(!isset($_POST['sort_items_by']))		$_POST['sort_items_by']="name_truck";
	if(!isset($_POST['sort_items_direction']))	$_POST['sort_items_direction']="asc";
	
	$sort_order="order by active desc, replacement desc, name_truck";
	if(isset($_POST['sort_items_direction']) && isset($_POST['sort_items_by']))
	{
		$sort_order="order by active desc, ".$_POST['sort_items_by']." ".$_POST['sort_items_direction'].", name_truck";	
	}
	                    
	$sql = "
		select *,
			(select replacement_xref_id from equipment_history eh where eh.deleted = 0 and eh.equipment_type_id = 1 and eh.equipment_id = trucks.id and eh.linedate_returned = 0 limit 1) as replaces_truck_id,
			(select equipment_id from equipment_history eh where eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_id,
			(SELECT GROUP_CONCAT(CONCAT(name_driver_first, ' ', name_driver_last)) FROM drivers WHERE drivers.active = 1 AND drivers.attached_truck_id = trucks.id AND drivers.deleted = 0) as attached_driver_list,
			(SELECT GROUP_CONCAT(CONCAT(name_driver_first, ' ', name_driver_last)) FROM drivers WHERE drivers.active = 1 AND drivers.attached2_truck_id = trucks.id AND drivers.deleted = 0) as attached2_driver_list
		from trucks
		where deleted = 0
			and company_admin_vehicle > 0
			".($_POST['filter_active'] > 0 ? "and active>0" : "and active=0")."
		".$sort_order."	
	";
	$data = simple_query($sql);

	$active_count = 0;
	while($row = mysqli_fetch_array($data)) {
		if($row['active']) $active_count++;
	}
	if(mysqli_num_rows($data)) mysqli_data_seek($data,0);
	
	$mrr_activity_log_notes.="Viewed list of trucks. ";
?>
<?
$usetitle = "Company Vehicles";
$use_title = "Company Vehicles";
?>
<? include('header.php') ?>
<?
	global $mrr_last_pass;
	$mrr_last_pass="";
		
	function show_truck($truck_id,$last_pass="",$replacer=0) 
	{
		global $mrr_last_pass;
		global $use_admin_level;
		
		$sql = "
			select trucks.*,
				(select count(*) from equipment_history where equipment_type_id = 1 and equipment_id = trucks.id and equipment_history.deleted = 0) as equipment_history_entry,
								
				(select equipment_id from equipment_history eh where eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_id,
				(	SELECT GROUP_CONCAT(drivers.id)
					FROM drivers 
					WHERE drivers.active = 1 
						AND drivers.attached_truck_id = trucks.id 
						AND drivers.deleted = 0 
					) as attached_driver_list_id,
				(	SELECT GROUP_CONCAT(drivers.id)
					FROM drivers 
					WHERE drivers.active = 1 
						AND drivers.attached2_truck_id = trucks.id 
						AND drivers.deleted = 0 
					) as attached2_driver_list_id,
				(	SELECT GROUP_CONCAT(CONCAT(name_driver_first, ' ', name_driver_last)) 
					FROM drivers 
					WHERE drivers.active = 1 
						AND drivers.attached_truck_id = trucks.id 
						AND drivers.deleted = 0 
					) as attached_driver_list,
				(	SELECT GROUP_CONCAT(CONCAT(name_driver_first, ' ', name_driver_last)) 
					FROM drivers 
					WHERE drivers.active = 1 
						AND drivers.attached2_truck_id = trucks.id 
						AND drivers.deleted = 0 
					) as attached2_driver_list
			
			from trucks
			where deleted = 0
				and trucks.id = '".sql_friendly($truck_id) ."'
		";
		$data = simple_query($sql);
		//@mysqli_data_seek($data_tmp , 0);
		$row = mysqli_fetch_array($data);
		
		$is_del1=mrr_fetch_truck_deleted($truck_id);
		$is_del2=mrr_fetch_truck_deleted($row['replacement_truck_id']);
		if($is_del2==1)	
		{
			$row['replacement_truck_id']=0;
			$row['replacement']=0;	
		}	
		$use_class = "";
		if($row['replacement_truck_id']) 
		{
			$use_class = "show_inactive_row show_inactive mrr_inactive_grey_back mrr_show_inactive_red";
		} 
		elseif($row['replacement'] || $replacer > 0) 
		{
			$use_class = "show_inactive_row mrr_inactive_grey_back mrr_show_inactive_replace";
		}
		
		$warning=trucks_last_movement($truck_id);
		$mrr_insur="";
		if($row['no_insurance'] > 0)				$mrr_insur="&nbsp;&nbsp; <span class='alert'><b>NoInsur</b></span>";
		$in_shop="";
		if($row['in_the_shop'] > 0)				$in_shop="&nbsp;&nbsp; <span class='alert'><b>In Shop</b></span>";	
		if($row['in_body_shop'] > 0)				$in_shop="&nbsp;&nbsp; <span class='alert'><b>Body Shop</b></span>";	
		
		$on_hold="";
		if($row['hold_for_driver'] > 0)			$on_hold="&nbsp;&nbsp; <span class='alert'><b>On Hold</b></span>";
		$no_plate="";
     	if(trim($row['license_plate_no'])=="")		$no_plate="&nbsp;&nbsp; <span class='alert'><b>No Plate</b></span>";
     			
		if($is_del1==0)
		{
     		?>
     		<tr class="<?=$use_class?>">
     			<td>
     				<?=($row['replacement'] ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : "")?>
     				<a href="?id=<?=$row['id']?>" class='<?=($row['equipment_history_entry'] == 0 ? 'alert' : ($row['active'] ? '' : 'inactive'))?>' title='<?=($row['equipment_history_entry'] == 0 ? 'No Equipment History Found!' : 'View Truck Info')?>'>
     					<?=($row['name_truck'] == '' ? "(id: $row[id])" : $row['name_truck'])?>
     				</a>
     				&nbsp;&nbsp; <?= $warning ?><?=$mrr_insur ?><?=$on_hold ?><?=$in_shop ?><?=$no_plate ?>
     			</td>
     			<td><?=($row['fubar_truck'] > 0  ? "<span class='alert'><b>FUBAR</b></span>" : "&nbsp;") ?></td>
     			<td>
     				<?
     				if($row['replacement'])
     				{
     					?>
     					<input type='hidden' name='prepass_<?=$row['id']?>' value="<?=$row['prepass']?>">
     					<?
     					if(trim($row['prepass'])!="")
     					{
     						echo $row['prepass'];
     					}
     					else
     					{
     						echo trim($mrr_last_pass);	
     					}	
     				}
     				else
     				{
     					?>
     					<input name='prepass_<?=$row['id']?>' value="<?=$row['prepass']?>" style='width:75px;text-align:right'>
     					<?	
     				}
     				?>				
     			</td>
     			<td><?=($row['automatic_transmission'] > 0 ? "<span title='Truck has Automatic Transmission' style='color:#00CC00;'><b>Auto</b></span>" : "<span title='Truck has Manual Transmission'>Manu</span>") ?></td>
     			<td><?=$row['truck_make']?></td>
     			<td><?=$row['truck_model']?></td>
     			<td><span style='color:#E0B0FF; font-weight:bold;'><?=($row['cab_type'] > 0 ? option_value_text($row['cab_type'],2) : "&nbsp;")?></span></td><!-- #E0B0FF #CC99FF-->
     			<td><?=$row['truck_year']?></td>			
     			<td>
     				<?
     					$lease_rental="".trim($row['leased_from'])."";
     					
     					if($row['rental']) 
     					{
     						$lease_rental.=" (Rental) ";
     					}
     					echo $lease_rental;
     				?>
     			</td>
     			<td><?=($row['camera_installed'] > 0 ? "<span style='color:orange;'><b>Cam</b></span>" : "&nbsp;") ?></td>	     			
     			<td>
     				<?
     					$dlist1=trim($row['attached_driver_list']);
     					$dlist2=trim($row['attached2_driver_list']);
     					if($dlist2!="" && $dlist1!="")
     					{
     						$dlist2=str_replace($dlist1,"",$dlist2);
     						$dlist1=str_replace($dlist2,"",$dlist1);	
     					}
     					$driver_str=$dlist1;
     					if($dlist2!="")		$driver_str.=", ".$dlist2;
     					
     					$driver_str=str_replace(", ,",",",$driver_str);
     					$driver_str=str_replace(",,",",",$driver_str);
     				?>
     				<?=$driver_str ?>
     				<?
     				$date_from=date("m/d/Y");
					$date_to=date("m/d/Y");
					$unavail_absent_vacation="";
					$unavail_absent_vacation=mrr_pull_unavailable_driver_days_and_codes_symbols($date_from,$date_to,$row['attached_driver_list_id']);
     				?>
     				<?=( trim($unavail_absent_vacation)!="" ? "<br>".$unavail_absent_vacation."" : "")  ?> 
     				<?
     				$unavail_absent_vacation="";
					$unavail_absent_vacation=mrr_pull_unavailable_driver_days_and_codes_symbols($date_from,$date_to,$row['attached2_driver_list_id']);
     				?>
     				<?=( trim($unavail_absent_vacation)!="" ? "<br>2nd (".$unavail_absent_vacation.")" : "")  ?>     				
     			</td>     				
     			<td align='right'>
     				<?
     				//PM Inspection...ties into oil changes and new PM Mileage interval
     				$pm_inspect="N/A";
     				if($row['pm_inspection_date']!="0000-00-00 00:00:00")		$pm_inspect="".date("m/d/Y",strtotime($row['pm_inspection_date']))."";
     				
     				$pm_odom=mrr_fetch_last_PN_odometer_reading($row['id']);
     				
     				$pm_title_adder="";
     				$pm_color="000000";
     				if($row['company_owned'] == 0)
     				{
     					$pm_color="999999";
     				}
     				else
     				{
     					//pm_miles_interval='".sql_friendly(money_strip($_POST['pm_miles_interval']))."',
						//pm_miles_last_oil='".sql_friendly(money_strip($_POST['pm_miles_last_oil']))."',
						//pm_miles_last_date='".(trim($_POST['pm_miles_last_date'])!=""  ? "".date("Y-m-d",strtotime($_POST['pm_miles_last_date']))." 00:00:00" :"0000-00-00 00:00:00")."',	
						
						if(date("Y-m-d",strtotime("+30 days",strtotime($row['pm_inspection_date']))) <	date("Y-m-d",time()) )	
     					{
     						$pm_color="CC0000";
     					}
     					elseif(date("Y-m-d",strtotime("+10 days",strtotime($row['pm_inspection_date']))) <	date("Y-m-d",time()) )	
     					{
     						$pm_color="FFCC00";
     					}
     					else
     					{
     						$pm_color="00CC00";
     					}
     					
     					if($row['pm_miles_interval'] > 0)
     					{
     						if(($row['pm_miles_last_oil'] + $row['pm_miles_interval']) <  $pm_odom)
     						{
     							$pm_color="CC0000";
     							$pm_title_adder=" Was Due at ".($row['pm_miles_last_oil'] + $row['pm_miles_interval'])." Miles and is ".($pm_odom - ($row['pm_miles_last_oil'] + $row['pm_miles_interval']))." overdue.";
     						}
     						elseif(($row['pm_miles_last_oil'] + $row['pm_miles_interval']- $pm_odom) <=2500 )
     						{
     							$pm_color="FFCC00";
     							$pm_title_adder=" Is coming Due at ".($row['pm_miles_last_oil'] + $row['pm_miles_interval'])." Miles -- ".(($row['pm_miles_last_oil'] + $row['pm_miles_interval'])- $pm_odom)." left.";
     						}
     						else
     						{
     							$pm_color="00CC00";
     							$pm_title_adder=" Will be Due at ".($row['pm_miles_last_oil'] + $row['pm_miles_interval'])." Miles.";	
     						}
     					}
     				}    
     				
     				echo "<span title='Last PM Inspection was done ".$pm_inspect.".".$pm_title_adder."' style='color:#".$pm_color.";'>".$pm_odom."</span>";
     				?>
     			</td>	
     			<td align='right'>
     				<?
     				//Fed Inspection
     				$fed_inspect="N/A";
     				if($row['fd_inspection_date']!="0000-00-00 00:00:00")		$fed_inspect="".date("m/d/Y",strtotime($row['fd_inspection_date']))."";
     				
     				$fed_color="000000";
     				if($row['company_owned'] == 0)	
     				{
     					$fed_color="999999";
     				}
     				else
     				{
     					if(date("Y-m-d",strtotime("+1 year",strtotime($row['fd_inspection_date']))) <	date("Y-m-d",time()) )	
     					{
     						$fed_color="CC0000";
     					}
     					elseif(date("Y-m-d",strtotime("+335 days",strtotime($row['fd_inspection_date']))) <	date("Y-m-d",time()) )	
     					{
     						$fed_color="FFCC00";
     					}
     					else
     					{
     						$fed_color="00CC00";
     					}
     				}  				
     				
     				echo "<span title='Last FED Inspection was done ".$fed_inspect.".' style='color:#".$fed_color.";'>".$fed_inspect."</span>";
     				?>
     			</td>	
     			<td>
     				<input name='monthly_cost_<?=$row['id']?>' value="<?=$row['monthly_cost']?>" style='width:75px;text-align:right'>
     				<input name="truck_id_list[]" value="<?=$row['id']?>" type='hidden'>
     			</td>
     			<td nowrap>
     				<?					
     					if($row['replacement']) 				echo 'Replacement ';
     					if($row['replacement_truck_id']) 		echo "<span class='alert'>Replaced</span> ";					
     				?>
     			</td>
     			<td><?=($row['peoplenet_tracking'] > 0 ? 'PN' : '')?></td>
     			<?				
     				$mrr_daily_coster=get_daily_cost($row['id'], 0);	
     				
     				
     				/*
     				<!--<?= ($_SERVER['REMOTE_ADDR']=="70.90.229.29" ? "$".number_format($mrr_daily_coster,2)."" : "") ?>-->
     				*/
     			?>	
     			<td><?= $row['apu_number'];?></td>
     			<td>	<a href="javascript:confirm_delete(<?=$row['id']?>)" class='mrr_delete_access'><img src="images/delete_sm.gif" border="0"></a></td>
     		</tr>
     		<?
     		
     		$mrr_last_pass=$row['prepass'];
     		$mrr_replaced=$row['replacement_truck_id'];
     		$mrr_replacer=$row['replacement'];
     		
     		//$is_del=mrr_fetch_truck_deleted($mrr_replaced);
     		
     		while($mrr_replacer > 0 &&  $mrr_replaced > 0 && $mrr_replaced!=$truck_id)
     		{	
     			
     			$sql2 = "
          			select *,
          				(select count(*) from equipment_history where equipment_type_id = 1 and equipment_id = trucks.id and deleted = 0) as equipment_history_entry,
          				
          				(select equipment_id from equipment_history eh where eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_id,
          				(	SELECT GROUP_CONCAT(CONCAT(name_driver_first, ' ', name_driver_last)) 
          					FROM drivers 
          					WHERE drivers.active = 1 
          						AND drivers.attached_truck_id = trucks.id 
          						AND drivers.deleted = 0 
          					) as attached_driver_list,
         					(	SELECT GROUP_CONCAT(CONCAT(name_driver_first, ' ', name_driver_last)) 
          					FROM drivers 
          					WHERE drivers.active = 1 
          						AND drivers.attached2_truck_id = trucks.id 
          						AND drivers.deleted = 0 
          					) as attached2_driver_list
          			
          			from trucks
          			where deleted = 0
          				and trucks.id = '".sql_friendly($mrr_replaced) ."'
          		";
          		$data2 = simple_query($sql2);
          		//@mysqli_data_seek($data_tmp , 0);
          		$row2 = mysqli_fetch_array($data2);
          		
          		$use_class = "";
          		if($row2['replacement_truck_id']) {
          			$use_class = "show_inactive_row show_inactive mrr_inactive_grey_back mrr_show_inactive_red";
          		} elseif($row2['replacement'] || $mrr_replaced) {
          			$use_class = "show_inactive_row mrr_inactive_grey_back mrr_show_inactive_replace";
          		}
          		
          		$warning=trucks_last_movement($mrr_replaced);
          		$mrr_insur="";
          		if($row2['no_insurance'] > 0)			$mrr_insur="&nbsp;&nbsp; NoInsur";
          		$in_shop="";
     			if($row2['in_the_shop'] > 0)			$in_shop="&nbsp;&nbsp; In Shop";
     			if($row2['in_body_shop'] > 0)			$in_shop="&nbsp;&nbsp; Body Shop";	
     			
     			$on_hold="";	
     			if($row2['hold_for_driver'] > 0)		$on_hold="&nbsp;&nbsp; On Hold";
     			
     			$no_plate="";
     			if(trim($row2['license_plate_no'])=="")	$no_plate="&nbsp;&nbsp; No Plate";
     			//$is_del=mrr_fetch_truck_deleted($row2['id']);
     			//if($is_del==0) 
     			//{
               		?>
               		<tr class="<?=$use_class?>">
               			<td>
               				<?=($row2['replacement'] ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : "")?>
               				<a href="?id=<?=$row2['id']?>" class='<?=($row2['equipment_history_entry'] == 0 ? 'alert' : ($row2['active'] ? '' : 'inactive'))?>'><?=($row2['name_truck'] == '' ? "(id: $row2[id])" : $row2['name_truck'])?></a>
               				&nbsp;&nbsp; <?= $warning ?><?=$mrr_insur ?><?=$on_hold ?><?=$in_shop ?><?=$no_plate ?>
               			</td>
               			<td><?=($row2['fubar_truck'] > 0  ? "<span class='alert'><b>FUBAR</b></span>" : "&nbsp;") ?></td>
               			<td>
          					<?
               				if($row2['replacement'])
               				{
               					?>
               					<input type='hidden' name='prepass_<?=$row2['id']?>' value="<?=$row2['prepass']?>">
               					<?
               					if(trim($row2['prepass'])!="")
               					{
               						echo $row2['prepass'];
               					}
               					else
               					{
               						echo trim($last_pass);	
               					}	
               				}
               				else
               				{
               					?>
               					<input name='prepass_<?=$row2['id']?>' value="<?=$row2['prepass']?>" style='width:75px;text-align:right'>
               					<?	
               				}
               				?>	
          				</td>
               			<td><?=$row2['truck_make']?></td>
               			<td><?=$row2['truck_model']?></td>
               			<td><span style='color:#E0B0FF; font-weight:bold;'><?=($row2['cab_type'] > 0 ? option_value_text($row2['cab_type'],2) : "&nbsp;")?></span></td><!-- #E0B0FF #CC99FF -->
               			<td><?=$row2['truck_year']?></td>     			
               			<td>
               				<?
               					$lease_rental="".trim($row2['leased_from'])."";
               					
               					if($row2['rental'] > 0) 				
               					{
               						$lease_rental.=" (Rental) ";
               					}
               					echo $lease_rental;
               				?>
               			</td>
               			<td><?=($row2['camera_installed'] > 0 ? "<span style='color:orange;'><b>Cam</b></span>" : "&nbsp;") ?></td>	
               			<td>
          					<?=$row2['attached_driver_list']?>
          					<?=(trim($row2['attached2_driver_list'])!="" ? ", ".trim($row2['attached2_driver_list'])."" : "")?>
          				</td>          				
          				<td align='right'>
               				<?
               				//PM Inspection...ties into oil changes and new PM Mileage interval               				
               				$pm_inspect="N/A";
               				if($row2['pm_inspection_date']!="0000-00-00 00:00:00")		$pm_inspect="".date("m/d/Y",strtotime($row2['pm_inspection_date']))."";
               				
               				$pm_odom=mrr_fetch_last_PN_odometer_reading($row2['id']);
               				
               				$pm_title_adder="";
               				$pm_color="000000";
               				if($row2['company_owned'] == 0)
               				{
               					$pm_color="999999";
               				}
               				else
               				{
               					//pm_miles_interval='".sql_friendly(money_strip($_POST['pm_miles_interval']))."',
          						//pm_miles_last_oil='".sql_friendly(money_strip($_POST['pm_miles_last_oil']))."',
          						//pm_miles_last_date='".(trim($_POST['pm_miles_last_date'])!=""  ? "".date("Y-m-d",strtotime($_POST['pm_miles_last_date']))." 00:00:00" :"0000-00-00 00:00:00")."',	
          						
          						if(date("Y-m-d",strtotime("+30 days",strtotime($row2['pm_inspection_date']))) <	date("Y-m-d",time()) )	
               					{
               						$pm_color="CC0000";
               					}
               					elseif(date("Y-m-d",strtotime("+10 days",strtotime($row2['pm_inspection_date']))) <	date("Y-m-d",time()) )	
               					{
               						$pm_color="FFCC00";
               					}
               					else
               					{
               						$pm_color="00CC00";
               					}
               					
               					
               					if($row2['pm_miles_interval'] > 0)
               					{
               						if(($row2['pm_miles_last_oil'] + $row2['pm_miles_interval']) <  $pm_odom)
               						{
               							$pm_color="CC0000";
               							$pm_title_adder=" Was Due at ".($row2['pm_miles_last_oil'] + $row2['pm_miles_interval'])." Miles and is ".($pm_odom - ($row2['pm_miles_last_oil'] + $row2['pm_miles_interval']))." overdue.";
               						}
               						elseif(($row2['pm_miles_last_oil'] + $row2['pm_miles_interval']- $pm_odom) <=2500 )
               						{
               							$pm_color="FFCC00";
               							$pm_title_adder=" Is coming Due at ".($row2['pm_miles_last_oil'] + $row2['pm_miles_interval'])." Miles -- ".(($row2['pm_miles_last_oil'] + $row2['pm_miles_interval'])- $pm_odom)." left.";
               						}
               						else
               						{
               							$pm_color="00CC00";
               							$pm_title_adder=" Will be Due at ".($row2['pm_miles_last_oil'] + $row2['pm_miles_interval'])." Miles.";	
               						}
               					}
               				}
               				               				
               				echo "<span title='Last PM Inspection was done ".$pm_inspect.".".$pm_title_adder."' style='color:#".$pm_color.";'>".$pm_odom."</span>";
               				?>
               			</td>	
               			<td align='right'>
               				<?
               				//Fed Inspection
               				$fed_inspect="N/A";
               				if($row2['fd_inspection_date']!="0000-00-00 00:00:00")		$fed_inspect="".date("m/d/Y",strtotime($row2['fd_inspection_date']))."";
               				
               				$fed_color="000000";
               				if($row2['company_owned'] == 0)	
               				{
               					$fed_color="FFFFFF";
               				}
               				else
               				{
               					if(date("Y-m-d",strtotime("+1 year",strtotime($row2['fd_inspection_date']))) <	date("Y-m-d",time()) )	
               					{
               						$fed_color="CC0000";
               					}
               					elseif(date("Y-m-d",strtotime("+335 days",strtotime($row2['fd_inspection_date']))) <	date("Y-m-d",time()) )	
               					{
               						$fed_color="FFCC00";
               					}
               					else
               					{
               						$fed_color="00CC00";
               					}
               				}
               				
               				echo "<span title='Last FED Inspection was done ".$fed_inspect.".' style='color:#".$fed_color.";'>".$fed_inspect."</span>";
               				?>
               			</td>
               			<td>
               				<input name='monthly_cost_<?=$row2['id']?>' value="<?=$row2['monthly_cost']?>" style='width:75px;text-align:right'>
               				<input name="truck_id_list[]" value="<?=$row2['id']?>" type='hidden'>
               			</td>
               			<td nowrap>
               				<?					
               					if($row2['replacement']) 			echo 'Replacement ';
               					if($row2['replacement_truck_id']) 		echo "<span class='alert'>Replaced</span> ";					
               				?>
               			</td>
               			<td><?=($row2['peoplenet_tracking'] > 0 ? 'PN' : '')?></td>
               			<td><?=$row2['apu_number']?></td>    
               			<td>	<a href="javascript:confirm_delete(<?=$row2['id']?>)" class='mrr_delete_access'><img src="images/delete_sm.gif" border="0"></a></td>
               		</tr>
               		<?			
     			//}
     			$mrr_replaced=$row2['replacement_truck_id'];
     			$mrr_replacer=$row2['replacement'];	
     		}
		}
	}
	
	$truck_maint_requests="";
?>

<table class='' style='text-align:left'>
<tr>
	<td valign='top'>
		<form action='' method='post'>
		<table class='admin_menu1'>
		<tr>
			<td colspan='18'><font class='standard18'><b>Company Vehicles</b></font></td>
		</tr>
		<tr>
			<td colspan='18'>
				<a href="<?=$SCRIPT_NAME?>?id=0">Add New Vehicle</a>
			</td>
		</tr>
		<tr>
			<td colspan='18'>&nbsp;</td>
		</tr>
		<tr>
			<td colspan='13'>
				<b>FILTER BY</b> 
				<?
				
                    $active_bx="";
                    $active_bx.="<select name='filter_active' id='filter_active' onChange='submit();'>";
                    
                    $pre="";		if($_POST['filter_active']==0)		$pre=" selected";	
                    $active_bx.="<option value='0'".$pre.">Inactive</option>";
                    
                    $pre="";		if($_POST['filter_active']==1)		$pre=" selected";	
                    $active_bx.="<option value='1'".$pre.">Active</option>";
                    
                    $active_bx.="</select>";
                    echo $active_bx;				
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<b>SORT BY</b>
				<select name='sort_items_by' onChange='submit();'>
					<option value='name_truck' <?= ($_POST['sort_items_by']=="name_truck" ? " selected" : "")?>>Vehicle Name</option>
					<option value='prepass' <?= ($_POST['sort_items_by']=="prepass" ? " selected" : "")?>>Prepass</option>
					<option value='truck_make' <?= ($_POST['sort_items_by']=="truck_make" ? " selected" : "")?>>Make</option>
					<option value='truck_model' <?= ($_POST['sort_items_by']=="truck_model" ? " selected" : "")?>>Model</option>
					<option value='truck_year' <?= ($_POST['sort_items_by']=="truck_year" ? " selected" : "")?>>Year</option>
					<option value='leased_from' <?= ($_POST['sort_items_by']=="leased_from" ? " selected" : "")?>>Rent/Lease</option>
					<option value='monthly_cost' <?= ($_POST['sort_items_by']=="monthly_cost" ? " selected" : "")?>>Monthly Cost</option>
				</select>	
				<select name='sort_items_direction' onChange='submit();'>
					<option value='asc' <?= ($_POST['sort_items_direction']=="asc" ? " selected" : "")?>>Asc</option>
					<option value='desc' <?= ($_POST['sort_items_direction']=="desc" ? " selected" : "")?>>Desc</option>			
				</select>	
			</td>
			<td colspan='5'>
				<input type='submit' value='Update Truck Costs' class='mrr_button_access'>
			</td>
		</tr>
		<tr>
			<td><b>Vehicle</b></td>
			<td><b>&nbsp;</b></td>
			<td><b>Prepass</b></td>
			<td><b>Trans</b></td>
			<td><b>Make</b></td>
			<td><b>Model</b></td>
			<td><b>Cab</b></td>
			<td><b>Year</b></td>
			<td><b>Rent/Lease</b></td>
			<td><b>Cam</b></td>			
			<td><b>Attached Driver</b></td>
			<td><b>PM/Odom</b></td>
			<td><b>FED</b></td>
			<td><b>Monthly Cost</b></td>
			<td><b>&nbsp;</b></td>
			<td><span title='PeopleNet Tracking enabled if PN displays for each Truck...'><b>PN</b></span></td>
			<td><b>Unit</b></td>
			<td><b></b></td>
		</tr>
		<? 
		while($row = mysqli_fetch_array($data)) 
		{
			if($row['replaces_truck_id'] > 0) 
			{
			
			} 
			else 
			{
				echo show_truck($row['id']);
				//echo "<tr><td vlign='top' colspan='8'>".$row['id'].":: ".($row['replacement_truck_id'] ? $row['replacement_truck_id'] : 'none')."</td><tr>";
				if($row['replacement_truck_id']) show_truck($row['replacement_truck_id'],"",1);
			}
		} 
		?>
		<tr>
			<td colspan='18'><center><input type='submit' value='Update Vehicle Costs' class='mrr_button_access'></center></td>
		</tr>
		</table>
		<?
		echo "<br><center><a href=\"admin_vehicles.php?pmi_fed=1\" target='_blank'>Run Vehicle Oil/PM/FED Report</a><center><br>";
		?>
		</form>
	</td>
	<td valign='top' style='width:350px'>
		<? if(isset($_GET['id'])) { 
			
			//get the first driver attached to this truck...
			$cur_driver_attched=0;
			if($_GET['id'] > 0)
			{
				$sqld=" select id from drivers where active>0 and deleted=0 and attached_truck_id='".sql_friendly($_GET['id'])."' order by name_driver_last asc,name_driver_first asc";
				$datad = simple_query($sqld);
				if($rowd = mysqli_fetch_array($datad))
				{	
					$cur_driver_attched=$rowd['id'];		//only need the first one...
				}
			}
			$cur_driver_attched2=0;
			if($_GET['id'] > 0)
			{
				$sqld=" select id from drivers where active>0 and deleted=0 and attached2_truck_id='".sql_friendly($_GET['id'])."' order by name_driver_last asc,name_driver_first asc";
				$datad = simple_query($sqld);
				if($rowd = mysqli_fetch_array($datad))
				{	
					$cur_driver_attched2=$rowd['id'];		//only need the first one...
				}
			}
			
			//now get all drivers...
			$dselector="<select name='attached_driver_id'>";	
			$sel="";
			if($cur_driver_attched==0)	$sel=" selected";
				
			$dselector.="<option value='0'".$sel.">No Driver</option>";	
			
					
			$sqld=" select * from drivers where active>0 and deleted=0 order by name_driver_last asc,name_driver_first asc";
			$datad = simple_query($sqld);
			while($rowd = mysqli_fetch_array($datad))
			{
				$sel="";
				if($rowd['id']==$cur_driver_attched)	$sel=" selected";
				
				$dselector.="<option value='".$rowd['id']."'".$sel.">".$rowd['name_driver_first']." ".$rowd['name_driver_last']."</option>";		
			}
			$dselector.="</select>";	
			
			//second driver
			$dselector2="<select name='attached2_driver_id'>";	
			$sel="";
			if($cur_driver_attched2==0)	$sel=" selected";
				
			$dselector2.="<option value='0'".$sel.">None</option>";	
			
					
			$sqld=" select * from drivers where active>0 and deleted=0 order by name_driver_last asc,name_driver_first asc";
			$datad = simple_query($sqld);
			while($rowd = mysqli_fetch_array($datad))
			{
				$sel="";
				if($rowd['id']==$cur_driver_attched2)	$sel=" selected";
				
				$dselector2.="<option value='".$rowd['id']."'".$sel.">".$rowd['name_driver_first']." ".$rowd['name_driver_last']."</option>";		
			}
			$dselector2.="</select>";	
			
			
			
			$mrr_activity_log_notes.="View truck ".$_GET['id']." info. ";
			$mrr_activity_log_truck=$_GET['id'];
			
			$e_type=option_exists_mrr_alt('equipment_type', 'truck');
			
			$truck_maint_requests="";
			if($_GET['id'] > 0)		$truck_maint_requests=mrr_display_maint_request_section($e_type,$_GET['id'],500);
			?>
			<table>
			<tr>
				<td>
					<form action="<?=$SCRIPT_NAME?>?id=<?=$_GET['id']?>" method="post" name='my_form'>
					<table class='admin_menu2'>
					<?
						$sql = "
							select *
							
							from trucks
							where id = '".sql_friendly($_GET['id'])."'
						";
						$data_edit = simple_query($sql);
						$row_edit = mysqli_fetch_array($data_edit);
						
						if($row_edit['tire_size']=="")		$row_edit['tire_size']="LP 22.5";
						
						$_POST['pm_inspection_note']=trim($row_edit['pm_inspection_note']);
						
						$_POST['pm_inspection_date']=date("m/d/Y",strtotime($row_edit['pm_inspection_date']));
						if($row_edit['pm_inspection_date']=="0000-00-00 00:00:00")		$_POST['pm_inspection_date']="";
						
						$_POST['fd_inspection_date']=date("m/d/Y",strtotime($row_edit['fd_inspection_date']));
						if($row_edit['fd_inspection_date']=="0000-00-00 00:00:00")		$_POST['fd_inspection_date']="";
						
						
						$_POST['linedate_own_op_ins']=date("m/d/Y",strtotime($row_edit['linedate_own_op_ins']));
						if($row_edit['linedate_own_op_ins']=="0000-00-00 00:00:00")		$_POST['linedate_own_op_ins']="";
						
						$_POST['linedate_own_op_acc_ins']=date("m/d/Y",strtotime($row_edit['linedate_own_op_acc_ins']));
						if($row_edit['linedate_own_op_acc_ins']=="0000-00-00 00:00:00")		$_POST['linedate_own_op_acc_ins']="";
												
						$_POST['pm_miles_interval']=(int) $row_edit['pm_miles_interval'];						
						$_POST['pm_miles_last_oil']=(int) $row_edit['pm_miles_last_oil'];
						$_POST['pm_miles_last_date']="";
						if($row_edit['pm_miles_last_date']!="0000-00-00 00:00:00")		$_POST['pm_miles_last_date']=date("m/d/Y",strtotime($row_edit['pm_miles_last_date']));
						
						$sql = "
							select equipment_history.*,
								trucks.name_truck
							
							from equipment_history
								left join trucks on trucks.id = equipment_history.replacement_xref_id
							where equipment_history.equipment_type_id = 1
								and equipment_history.equipment_id = '".sql_friendly($_GET['id'])."'
								and equipment_history.equipment_id > 0
								and equipment_history.deleted = 0
							order by equipment_history.linedate_aquired
						";
						$data_history = simple_query($sql);				
						
						$sql = "
							select equipment_history.*,
								t.name_truck as replacement_truck_name
							
							from equipment_history
								inner join trucks on trucks.id = equipment_history.replacement_xref_id and trucks.id = '".sql_friendly($_GET['id'])."' and trucks.id>0
								left join trucks t on t.id = equipment_history.equipment_id
						";
						$data_replacement = simple_query($sql);
						
						$sql = "
							select equipment_history.*,
								t.name_truck as truck_name
							
							from equipment_history
								left join trucks t on t.id = equipment_history.equipment_id
							where equipment_history.deleted=0
								 and t.id = '".sql_friendly($_GET['id'])."'
								 and t.id > 0
								 and equipment_type_id=1
							order by equipment_history.linedate_aquired desc, equipment_history.linedate_returned desc, equipment_history.id desc
						";
						$data_value = simple_query($sql);
						
						if(isset($_POST['name_truck'])) {
							echo "<tr><td colspan='2'><font color='red'><b>Update Successful</b></font></td></tr>";
						}
					?>
					<tr>
						<td colspan='3'><?=$mrr_dup_msg ?></td>
					</tr>				
					<tr>
						<td><b>Vehicle</b> <?= show_help('admin_trucks.php','Truck Name') ?> &nbsp; &nbsp; &nbsp; &nbsp; (Should be Numeric for COAs)</td>
						<td colspan='2'>
							<input id="name_truck" name="name_truck" value="<?=$row_edit['name_truck']?>" onMouseOut='mrr_verify_user_unique(<?=$_GET['id']?>);' onBlur='mrr_verify_user_unique(<?=$_GET['id']?>);'>
							 <span id='mrr_naming_message'></span>
						</td>
					</tr>
					<tr>
						<td><label for='company_owned'><b>Company Owned</b></label> <?= show_help('admin_trucks.php','Company Owned') ?></td>
						<td><input type='checkbox' name='company_owned' id='company_owned' <? if($row_edit['company_owned']) echo 'checked'?> onClick='mrr_eval_rental();'></td>
						<td rowspan='5' align='right'>
          					<?
          					if($_GET['id'] > 0 && 1==2)
          					{
          						echo "
          							<div class='toolbar_button_mrr' onclick='duplicate_truck(".$_GET['id'].");'>
          								<div><img src='images/copy.png'></div>
          								<div>Duplicate</div>
          							</div>
          						";	
          					}
          					else
          					{
          						echo "&nbsp;";	
          					}
          					?>
          				</td>
					</tr>
					<tr>
						<td><label for='company_admin_vehicle'><b>Company Vehicle</b></label> <?= show_help('admin_trucks.php','company_admin_vehicle') ?></td>
						<td><input type='checkbox' name='company_admin_vehicle' id='company_admin_vehicle' <? if($row_edit['company_admin_vehicle']) echo 'checked'?>></td>						
					</tr>					
					<tr>
						<td><b>Active</b> <?= show_help('admin_trucks.php','Active') ?></td>
						<td><?=($row_edit['active'] ? 'active' : 'inactive')?>	</td>
					</tr>
					<tr class='mrr_shader'>
						<td><label for='rental'><b>Rental</b></label> <?= show_help('admin_trucks.php','Rental') ?></td>
						<td><input type='checkbox' name='rental' id='rental' <? if($row_edit['rental']) echo 'checked'?>></td>
					</tr>
					<tr>
						<td><label for='insurance_exclude'><b>Exclude Insurance</b></label> <?= show_help('admin_trucks.php','Exclude Insurance') ?></td>
						<td><input type='checkbox' name='insurance_exclude' id='insurance_exclude' <? if($row_edit['insurance_exclude']) echo 'checked'?>></td>
					</tr>
					<tr>
						<td><label for='active_cnt_exclude'><b>Exclude from Daily Cost</b></label> <?= show_help('admin_trucks.php','Exclude from Daily Cost') ?></td>
						<td><input type='checkbox' name='active_cnt_exclude' id='active_cnt_exclude' <? if($row_edit['active_cnt_exclude']) echo 'checked'?>></td>
					</tr>
					<tr>
						<td><label for='owner_operated'><b>Owner Operated/I.C.</b></label> <?= show_help('admin_trucks.php','Owner Operated/I.C') ?></td>
						<td>
							<input type='checkbox' name='owner_operated' id='owner_operated' <? if($row_edit['owner_operated']) echo 'checked'?>>
							
							<i>If checked, please fill Insurance info</i>
						</td>
					</tr>
					<tr>
          				<td><label for='own_op_ins_flag'><b>Has O.O. Truck Insurance</b></label> <?= show_help('admin_trucks.php','Owner Operator Insurance') ?></td>
          				<td colspan='2'>
          					<input type='checkbox' name='own_op_ins_flag' id='own_op_ins_flag' <? if($row_edit['own_op_ins_flag']) echo 'checked'?>>
          					
          					Starts 
          					<input name="linedate_own_op_ins" id='linedate_own_op_ins' value="<? if(strtotime($row_edit['linedate_own_op_ins']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_own_op_ins'])) ?>" style='width:80px;'> (mm/dd/yyyy)
          					
          					<input type='hidden' name="own_op_ins_number" id="own_op_ins_number" value="<?=$row_edit['own_op_ins_number']?>">	<!--  style='width:200px;' -->
          				</td>
          			</tr>
          			<tr>
          				<td><label for='own_op_acc_ins_flag'><b>Has O.O.C./A.C.C Insurance</b></label> <?= show_help('admin_trucks.php','Owner Operator A.C.C. Insurance') ?></td>
          				<td colspan='2'>
          					<input type='checkbox' name='own_op_acc_ins_flag' id='own_op_acc_ins_flag' <? if($row_edit['own_op_acc_ins_flag']) echo 'checked'?>>
          					
          					Starts 
          					<input name="linedate_own_op_acc_ins" id='linedate_own_op_acc_ins' value="<? if(strtotime($row_edit['linedate_own_op_acc_ins']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_own_op_acc_ins'])) ?>" style='width:80px;'> (mm/dd/yyyy)
          					
          				</td>
          			</tr>
					<!--
					<tr>
						<td><label for='no_insurance'><b>No Insurance</b></label> <?= show_help('admin_trucks.php','No Insurance') ?></td>
						<td colspan='2'><input type='checkbox' name='no_insurance' id='no_insurance' <? if($row_edit['no_insurance']) echo 'checked'?>></td>
					</tr>
					-->					
					<tr>
						<td><b>Monthly Cost</b> <?= show_help('admin_trucks.php','Monthly Cost') ?></td>
						<td colspan='2'><input name="monthly_cost" value="<?=$row_edit['monthly_cost']?>" size='10' style='text-align:right;'>
					</tr>
					<tr>
						<td><label for='peoplenet_tracking'><b>PeopleNet Tracking</b></label> <?= show_help('admin_trucks.php','PeopleNet Tracking') ?></td>
						<td colspan='2'><input type='checkbox' name='peoplenet_tracking' id='peoplenet_tracking' <? if($row_edit['peoplenet_tracking']) echo 'checked'?>></td>
					</tr>
					<tr>
						<td><label for='camera_installed'><b>Camera Installed</b></label> <?= show_help('admin_trucks.php','camera_installed') ?></td>
						<td colspan='2'><input type='checkbox' name='camera_installed' id='camera_installed' <? if($row_edit['camera_installed']) echo 'checked'?>></td>
					</tr>
					<tr>
						<td><label for='fubar_truck'><b>FUBAR</b></label> <?= show_help('admin_trucks.php','fubar_truck') ?></td>
						<td colspan='2'><input type='checkbox' name='fubar_truck' id='fubar_truck' <? if($row_edit['fubar_truck']) echo 'checked'?>></td>
					</tr>
					<tr>
						<td><b>PeopleNet Odometer Offset</b> <?= show_help('admin_trucks.php','pn_odometer_offset') ?></td>
						<td colspan='2'><input type='text' name='pn_odometer_offset' id='pn_odometer_offset' value="<?=$row_edit['pn_odometer_offset']?>" size='10' style='text-align:right;'></td>
					</tr>
					<tr>
						<td><label for='in_the_shop'><b>Truck In Shop</b></label> <?= show_help('admin_trucks.php','In The Shop') ?></td>
						<td colspan='2'>
							<input type='checkbox' name='in_the_shop' id='in_the_shop' <? if($row_edit['in_the_shop']) echo 'checked'?>>
							<input id="in_shop_note" name="in_shop_note" value="<?=trim($row_edit['in_shop_note'])?>">
						</td>
					</tr>
					<tr>
						<td><label for='in_body_shop'><b>Truck In Body Shop</b></label> <?= show_help('admin_trucks.php','In Body Shop') ?></td>
						<td colspan='2'>
							<input type='checkbox' name='in_body_shop' id='in_body_shop' <? if($row_edit['in_body_shop']) echo 'checked'?>>
							<input id="in_body_note" name="in_body_note" value="<?=trim($row_edit['in_body_note'])?>">
						</td>
					</tr>
					<tr class='mrr_shader'>
						<td valign='top'><label for='repairs_pending'><b>Truck Needs Repairs</b></label> <?= show_help('admin_trucks.php','repairs_pending') ?></td>
						<td valign='top' colspan='2'>
							<input type='checkbox' name='repairs_pending' id='repairs_pending' <? if($row_edit['repairs_pending']) echo 'checked'?>>
							<i>Enter Repairs Needed below</i><br>
							<textarea name="repairs_pending_list" id="repairs_pending_list" wrap='virtual' rows='5' cols='40'><?=$row_edit['repairs_pending_list'] ?></textarea>
							<div style='text-align:right; width:100%;'>	
     							
     							Opened:  <?= show_help('admin_trucks.php','repairs_pending opened') ?>
     							<input name="repairs_pending_date_opened" id='repairs_pending_date_opened' value="<? if(strtotime($row_edit['repairs_pending_date_opened']) != 0) echo date("m/d/Y", strtotime($row_edit['repairs_pending_date_opened'])) ?>" style='width:80px;' class='mrr_date_input'>
     							 (mm/dd/yyyy)
     							
     							<br>
     							Inspected:  <?= show_help('admin_trucks.php','repairs_pending inspected') ?>
     							<input name="repairs_pending_date_inspect" id='repairs_pending_date_inspect' value="<? if(strtotime($row_edit['repairs_pending_date_inspect']) != 0) echo date("m/d/Y", strtotime($row_edit['repairs_pending_date_inspect'])) ?>" style='width:80px;' class='mrr_date_input'>
     							 (mm/dd/yyyy)
     													
     							<br>
     							Repaired:  <?= show_help('admin_trucks.php','repairs_pending repaired') ?>
     							<input name="repairs_pending_date_repair" id='repairs_pending_date_repair' value="<? if(strtotime($row_edit['repairs_pending_date_repair']) != 0) echo date("m/d/Y", strtotime($row_edit['repairs_pending_date_repair'])) ?>" style='width:80px;' class='mrr_date_input'>
     							 (mm/dd/yyyy)
     														
     							<br>
     							Closed:  <?= show_help('admin_trucks.php','repairs_pending closed') ?>
     							<input name="repairs_pending_date_closed" id='repairs_pending_date_closed' value="<? if(strtotime($row_edit['repairs_pending_date_closed']) != 0) echo date("m/d/Y", strtotime($row_edit['repairs_pending_date_closed'])) ?>" style='width:80px;' class='mrr_date_input'>
     							 (mm/dd/yyyy)
     							
     							<br>
     							Repairs Made:
     							
     							<label for='repairs_pending_internal'><b>Internally</b></label> <?= show_help('admin_trucks.php','repairs_pending internal') ?>
     							<input type='checkbox' name='repairs_pending_internal' id='repairs_pending_internal' <? if($row_edit['repairs_pending_internal']) echo 'checked'?>>
     							
     							<label for='repairs_pending_made'><b>Completed</b></label> <?= show_help('admin_trucks.php','repairs_pending made') ?>
     							<input type='checkbox' name='repairs_pending_made' id='repairs_pending_made' <? if($row_edit['repairs_pending_made']) echo 'checked'?>>
							</div>
						</td>
					</tr>
					<tr>
						<td><label for='hold_for_driver'><b>Truck On Hold (for Driver)</b></label> <?= show_help('admin_trucks.php','hold_for_driver') ?></td>
						<td colspan='2'>
							<input type='checkbox' name='hold_for_driver' id='hold_for_driver' <? if($row_edit['hold_for_driver']) echo 'checked'?>>
							<input id="on_hold_note" name="on_hold_note" value="<?=trim($row_edit['on_hold_note'])?>">
						</td>
					</tr>					
					<tr>
						<td><label for='automatic_transmission'><b>Automatic Transmission</b></label> <?= show_help('admin_trucks.php','automatic_transmission') ?></td>
						<td colspan='2'><input type='checkbox' name='automatic_transmission' id='automatic_transmission' <? if($row_edit['automatic_transmission']) echo 'checked'?>></td>
					</tr>	
					<tr>
						<td colspan='3'>&nbsp;</td>
					</tr>								
					<tr>
						<td><b>Attached Driver</b> <?= show_help('admin_trucks.php','Attached Driver') ?></td>
						<td colspan='2'><?=$dselector ?></td>
					</tr>
					<tr>
						<td><b>Attached 2nd Driver</b> <?= show_help('admin_trucks.php','Attached 2nd Driver') ?></td>
						<td colspan='2'><?=$dselector2 ?></td>
					</tr>					
					<tr>
						<td colspan='3'>&nbsp;</td>
					</tr>
					<? if($row_edit['maint_req_lock']) { ?>
     					<tr>
     						<td colspan='3' align='center'>
     							<span class='mrr_alert'>WARNING: THIS TRUCK IS ON MAINTENANCE LOCKDOWN.</span><?= show_help('admin_trucks.php','maint_req_lock') ?>
     							<br>See supervisor to review other options. It has <span class='mrr_alert'>URGENT</span> maintence needs.
     							<br><span class='mrr_alert'>Please see James or Dale before moving this unit.</span>
     						</td>
     					</tr>					
     					<tr>
     						<td colspan='3'>&nbsp;</td>
     					</tr>
					<? } ?>
					
					<tr>
						<td colspan='3'>
						<? if($use_admin_level > 90) { ?>
							<table style='border:1px #aaaaaa solid;width:100%'>
							<tr>
								<td colspan='5'>
									<b>History</b> - 
									<input type='button' value='Add new history' onclick='add_equipment_history(true)' class='mrr_button_access'>  <?= show_help('admin_trucks.php','Add History') ?>
								</td>
							</tr>
							<tr class='add_equipment'>
								<td colspan='5'>
									<b>New equipment aquired</b>
									- <a href='javascript:void(0)' onclick='add_equipment_history(false)' style='float:right'>Cancel</a>
									<input type='hidden' name='add_equipment_history_flag' id='add_equipment_history_flag' value='0'>
								</td>
							</tr>
							<tr class='add_equipment'>
								<td colspan='2'><label><b>Replacement?</b> <input type='checkbox' name='new_replacement' id='new_replacement'></label></td>
								<td colspan='3'>
									<b>Replaces Truck</b>
									<select name='new_xref_id' id='new_xref_id'>
										<option value='0'>Not a replacement</option>
										<?
											mysqli_data_seek($data,0);
											while($row = mysqli_fetch_array($data)) {
												echo "<option value='$row[id]' ".(!$row['active'] ? "class='show_inactive'" : "").">$row[name_truck]</option>";
											}
										?>
									</select>
								</td>
							</tr>
							<tr class='add_equipment'>
								<td><input name='new_linedate_aquired' id='new_linedate_aquired' style='width:80px' class='datepicker' value='<?=date("m/d/Y")?>'></td>
								<td><input name='new_linedate_returned' id='new_linedate_returned' style='width:80px' class='datepicker' value=''></td>
								<td align='right'><input name='new_equipment_value' id='new_equipment_value' value="" style='width:80px;text-align:right'></td>
								<td align='right'><input name='new_miles_pickup' id='new_miles_pickup' value="" style='width:80px;text-align:right'></td>
								<td align='right'><input name='new_miles_dropoff' id='new_miles_dropoff' value="" style='width:80px;text-align:right'></td>
							</tr>
							<tr>
								<td><b>Acquired</b><br>(mm/dd/yyyy)</td>
								<td><b>Returned</b><br>(mm/dd/yyyy)</td>
								<td align='right'><b>Value</b></td>
								<td align='right'><b>Beginning Odometer</b></td>
								<td align='right'><b>Ending Odometer</b></td>
								<td></td>
							</tr>
							<? while($row_history = mysqli_fetch_array($data_history)) {
								echo "
									<tr>
										<td>
											<input name='linedate_aquired_$row_history[id]' id='linedate_aquired_$row_history[id]' style='width:80px' class='datepicker equip_history_linedate' value='".date("m/d/Y", strtotime($row_history['linedate_aquired']))."'>
											<input type='hidden' name='equipment_history_array[]' value='$row_history[id]'>
										</td>
										<td><input name='linedate_returned_$row_history[id]' style='width:80px' class='datepicker linedate_returned' value='".($row_history['linedate_returned'] > 0 ? date("m/d/Y", strtotime($row_history['linedate_returned'])) : "")."'></td>
										<td align='right'><input name='equipment_value_$row_history[id]' value=\"$".money_format('',$row_history['equipment_value'])."\" style='width:80px;text-align:right'></td>
										<td align='right'><input name='miles_pickup_$row_history[id]' value=\"".number_format($row_history['miles_pickup'])."\" style='width:80px;text-align:right' class='equip_history_pickup'></td>
										<td align='right'><input name='miles_dropoff_$row_history[id]' value=\"".number_format($row_history['miles_dropoff'])."\" style='width:80px;text-align:right' class='equip_history_dropoff'></td>
										<td><a href='javascript:void(0)' onclick='delete_equipment_history($row_history[id])' class='mrr_delete_access'><img src='images/delete_sm.gif' alt='Delete History' title='Delete History' style='border:0'></a></td>
									</tr>

									<tr ".(!$row_history['replacement'] ? "class='show_inactive'" : "").">
										<td><label for='replacement_$row_history[id]'>Replacement</label></td>
										<td>
											<input type='checkbox' name='replacement_$row_history[id]' id='replacement_$row_history[id]' ".($row_history['replacement'] ? 'checked' : '').">
										</td>
										<td colspan='3'>
											Replaces Truck: 
											<select name='replacement_xref_id_$row_history[id]'>
												<option value='0'></option>
								";
											mysqli_data_seek($data,0);
											while($row = mysqli_fetch_array($data)) {
												echo "<option value='$row[id]' ".(!$row['active'] ? "class='show_inactive'" : "")." ".($row_history['replacement_xref_id'] == $row['id'] ? 'selected' : '').">$row[name_truck]</option>";
											}
								echo "
											</select>
										</td>
									</tr>
								";
							} ?>
							<tr>
								<td colspan='5'>
									<? 
									if(!mysqli_num_rows($data_history)) {
										echo "
											<span class='alert'>There aren't any history entries</span>
										";
									}
									?>
									<center>
									<input type='submit' value='Update History' onClick="return mrr_mile_checker();" class='mrr_button_access'>
									</center>
								</td>
							</tr>							
							<tr>
								<td colspan='5'>
									<br>&nbsp;<br>
									<b>Depreciation History</b><br>
									<? 
									echo mrr_display_equipment_depreciation($_GET['id'],0);	//truck_id,trialer_id
									?>
								</td>
							</tr>							
							</table>
						<? } ?>
						</td>
					</tr>
					<tr>
						<td colspan='3'>&nbsp;</td>
					</tr>
					<tr class='mrr_shader'>
						<td><b>Acquired or Leased From</b> <?= show_help('admin_trucks.php','Leased From') ?></td>
						<td colspan='2'><input name="leased_from" id='leased_from' value="<?=$row_edit['leased_from']?>"></td>
					</tr>
					<tr>
						<td><b>Gallon Size</b> <?= show_help('admin_trucks.php','gallon_size') ?></td>
						<td colspan='2'><input name="gallon_size" value="<?=$row_edit['gallon_size']?>"></td>
					</tr>
					<tr>
						<td><b>Tire Size</b> <?= show_help('admin_trucks.php','tire_size') ?></td>
						<td colspan='2'><input name="tire_size" value="<?=$row_edit['tire_size']?>"></td>
					</tr>					
					<tr>
						<td><b>Year</b> <?= show_help('admin_trucks.php','Year') ?></td>
						<td colspan='2'><input name="truck_year" value="<?=$row_edit['truck_year']?>"></td>
					</tr>
					<tr>
						<td><b>Make</b> <?= show_help('admin_trucks.php','Make') ?></td>
						<td colspan='2'><input name="truck_make" value="<?=$row_edit['truck_make']?>"></td>
					</tr>
					<tr>
						<td><b>Model</b> <?= show_help('admin_trucks.php','Model') ?></td>
						<td colspan='2'><input name="truck_model" value="<?=$row_edit['truck_model']?>"></td>
					</tr>
					<tr>
						<td><b>VIN</b> <?= show_help('admin_trucks.php','VIN') ?></td>
						<td colspan='2'><input name="vin" value="<?=$row_edit['vin']?>"></td>
					</tr>
					<tr>
						<td><b>Cab Type</b> <?= show_help('admin_trucks.php','cab_type') ?></td>
						<td colspan='2'>
							<?
							$sqls="
								select fname as use_disp,
									id as use_val
								from option_values 
								where cat_id='20'
								order by id asc 
							";
							echo select_box_disp($sqls,'cab_type',$row_edit['cab_type'],'Cab Type?','');
							?>	
						</td>
					</tr>
					<tr>
						<td><b>Equipment Unit</b> <?= show_help('admin_trucks.php','PN Unit Type') ?></td>
						<td colspan='2'><input name="apu_number" value="<?=$row_edit['apu_number']?>"></td>
					</tr>
					<tr>
						<td><b>DSN Serial</b> <?= show_help('admin_trucks.php','PN Unit Serial') ?></td>
						<td colspan='2'><input name="apu_serial" value="<?=$row_edit['apu_serial']?>"></td>
					</tr>
					<tr>
						<td><b>DVR Serial</b> <?= show_help('admin_trucks.php','DVR Serial') ?></td>
						<td colspan='2'><input name="dvr_serial" value="<?=$row_edit['dvr_serial']?>"></td>
					</tr>
					<tr>
						<td><b>Equipment Value</b> <?= show_help('admin_trucks.php','PN Unit Type') ?></td>
						<td colspan='2'><input name="apu_value" value="<?=$row_edit['apu_value']?>" size='10' style='text-align:right;'></td>
					</tr>
					<tr>
						<td nowrap><b>License Plate #</b> <?= show_help('admin_trucks.php','License Plate') ?></td>
						<td colspan='2'><input name="license_plate_no" value="<?=$row_edit['license_plate_no']?>"></td>
					</tr>
					<tr>
						<td nowrap><b>Prepass</b> <?= show_help('admin_trucks.php','Prepass') ?></td>
						<td colspan='2'><input name="prepass" value="<?=$row_edit['prepass']?>"></td>
					</tr>
					<tr>
						<td colspan='3'><div class='mrr_button_access_notice'>&nbsp;</div></td>
					</tr>
					<tr>
						<td><input type='submit' name='send_insurance_report' value="Update and Send to Insurance" class='mrr_button_access'></td>
						<td colspan='2' align='right'>
							<?
							if($use_admin_level >= 100)
							{
								echo "<input type='submit' name='coa_updates' value='Update (with COAs)' class='mrr_button_access'> &nbsp;&nbsp;&nbsp;";
							}
							?>							
							<input type='submit' value="Update" class='mrr_button_access'>
						</td>
					</tr>
					<tr>
						<td colspan='3'><?= ($_GET['id'] > 0 ? mrr_get_insurance_email_log(0,0,$_GET['id'],0,"") : "") ?></td>
					</tr>
					</table>					
					<?
					$pm_odom=mrr_fetch_last_PN_odometer_reading($row_edit['id']);
					?>
					<div class='clear'></div>
					<br>
					<div id='pm_inspection_section' style='margin-bottom:10px;'>
						<table class='admin_menu1' style='width:100%;margin-bottom:10px'>
     					<tr>
     						<td colspan='4' class='border_bottom'><div class='section_heading'>PM Inspection</div></td>
     					</tr>
     					<tr>
     						<td><b>Last FED Inspection:</b> <?= show_help('admin_trucks.php','FED Inspection Date') ?></td>
     						<td><input name='fd_inspection_date' id='fd_inspection_date' style='width:80px' class='datepicker' value='<?=$_POST['fd_inspection_date'] ?>' placeholder='mm/dd/YYYY'></td>
     						<td align='right'>&nbsp;</td>
     					</tr>
     					<tr>
     						<td><b>Last PM Inspection Note</b> <?= show_help('admin_trucks.php','PM Inspection Note') ?></td>
     						<td><b>Last Date</b> <?= show_help('admin_trucks.php','PM Inspection Date') ?></td>
     						<td align='right'>&nbsp;</td>
     					</tr>
     					<tr>
     						<td><input name='pm_inspection_note' id='pm_inspection_note' style='width:300px' value="<?=$_POST['pm_inspection_note']?>"></td>
     						<td><input name='pm_inspection_date' id='pm_inspection_date' style='width:80px' class='datepicker' value='<?=$_POST['pm_inspection_date'] ?>' placeholder='mm/dd/YYYY'></td>
     						<td align='right'><input type='submit' value="Update" class='mrr_button_access'></td>
     					</tr>
     					<tr>
     						<td colspan='4' class='border_bottom'><div class='section_heading'>PM Mileage/Odometer (Oil Changes)</div></td>
     					</tr>
     					<tr>
     						<td><label for='use_pm_oil_report'><b>Use on Oil/PM/FED Report:</b></label> <?= show_help('admin_trucks.php','use_pm_oil_report') ?></td>
     						<td><input type='checkbox' name='use_pm_oil_report' id='use_pm_oil_report' <? if($row_edit['use_pm_oil_report']) echo 'checked'?>></td>
     						<td align='right'>&nbsp;</td>
     					</tr>
     					<tr>
     						<td><b>PM Mileage Interval:</b> <?= show_help('admin_trucks.php','PM Oil Change Interval') ?></td>
     						<td><input name='pm_miles_interval' id='pm_miles_interval' style='width:80px; text-align:right;' value='<?=$_POST['pm_miles_interval'] ?>'></td>
     						<td align='right'>&nbsp;</td>
     					</tr>
     					<tr>
     						<td><b>Odometer when Oil Last Changed:</b> <?= show_help('admin_trucks.php','PM Oil Change Last Odometer') ?></td>
     						<td><input name='pm_miles_last_oil' id='pm_miles_last_oil' style='width:80px; text-align:right;' value='<?=$_POST['pm_miles_last_oil'] ?>'></td>
     						<td align='right'>&nbsp;</td>
     					</tr>
     					<tr>
     						<td><b>Last Date Changed:</b> <?= show_help('admin_trucks.php','PM Oil Change Last Date') ?></td>
     						<td><input name='pm_miles_last_date' id='pm_miles_last_date' style='width:80px' value='<?=$_POST['pm_miles_last_date'] ?>' class='datepicker' placeholder='mm/dd/YYYY'></td>
     						<td align='right'>&nbsp;</td>
     					</tr>
     					<tr>
     						<td><b>Current Odometer Reading:</b> <?= show_help('admin_trucks.php','PM Odometer Reading') ?></td>
     						<td><?=$pm_odom ?></td>
     						<td align='right'>&nbsp;</td>
     					</tr>
     					<?
     					$pm_odom_due=0;
     					$pm_title_adder="";
     					$pm_color="000000";     					
     					$pm_inspect="N/A";
     					
     					if($_POST['pm_inspection_date']!="0000-00-00 00:00:00")		$pm_inspect="".date("m/d/Y",strtotime($_POST['pm_inspection_date']))."";
     					
     					
          					$pm_odom_due=((int) $_POST['pm_miles_last_oil'] + (int) $_POST['pm_miles_interval']);
          					
          					if($pm_odom_due <= $pm_odom)
          					{
          						$pm_color="CC0000";
          						$pm_title_adder=" Was Due at ".$pm_odom_due." Miles and is ".($pm_odom - $pm_odom_due)." overdue.";
          					}
          					elseif(($pm_odom_due - $pm_odom) <=2500)
          					{
          						$pm_color="FFCC00";
          						$pm_title_adder=" Is coming Due at ".$pm_odom_due." Miles -- ".($pm_odom_due - $pm_odom)." left.";
          					}
          					else
          					{
          						$pm_color="00CC00";
          						$pm_title_adder=" Will be Due at ".$pm_odom_due." Miles.";	
          					}
     					
     					?>
     					<tr>
     						<td><b>Next Odometer Reading Due:</b> <?= show_help('admin_trucks.php','PM Oil Change Due') ?></td>
     						<td><span title='Last PM Inspection was done <?=$pm_inspect ?>. <?=$pm_title_adder ?>' style='color:#<?=$pm_color ?>;'><?=$pm_odom_due ?></span></td>
     						<td align='right'>&nbsp;</td>
     					</tr>     					
     					<tr>
     						<td colspan='2' align='right'><b>Use UPDATE above to set or use this one for current PN Odometer - > </b></td>
     						<td align='right'><input type='button' value="Oil Changed" class='mrr_button_access' onClick='mrr_oil_changed(<?=(int) $pm_odom ?>);'></td>
     					</tr>
     					</table>	
					</div>										
					</form>					
					
					<div class='clear'></div>
					<br>
					<div id='maint_request_section' style='margin-bottom:10px;'><?= $truck_maint_requests ?></div>
					<br>
					<table class='admin_menu2' style='width:100%;margin-bottom:10px'>
					<tr>
						<td colspan='4' class='border_bottom'><div class='section_heading'>Replacement History</div></td>
					</tr>
					<tr>
						<td><b>Replacement Truck</b></td>
						<td><b>Acquired</b></td>
						<td><b>Returned</b></td>
						<td align='right'><b>Value</b></td>
					</tr>
					<? 
					while($row_replacement = mysqli_fetch_array($data_replacement)) 
					{
						echo "
							<tr>
								<td><a href='admin_trucks.php?id=$row_replacement[equipment_id]'>$row_replacement[replacement_truck_name]</a></td>
								<td>".date("m/d/Y", strtotime($row_replacement['linedate_added']))."</td>
								<td>".($row_replacement['linedate_returned'] > 0 ? date("m/d/Y", strtotime($row_replacement['linedate_returned'])) : "")."</td>
								<td align='right'>$".number_format($row_replacement['equipment_value'],2)."</td>
							</tr>
						";
					}
					?>
					</table>	
					
					<?
					if($use_admin_level > 95)
					{
						echo "
						<table class='admin_menu2' style='width:100%;margin-bottom:10px'>
						<tr>
							<td colspan='5' class='border_bottom'><div class='section_heading'>$row_value[truck_name] Value History</div></td>
						</tr>
						<tr>
							<td><b>Recorded</b></td>
							<td><b>Acquired</b></td>
							<td><b>Returned</b></td>
							<td align='right'><b>Value</b></td>
							<td align='right'><b>New Value</b></td>
						</tr>
						";
						while($row_value = mysqli_fetch_array($data_value)) 
						{
							$dlinker="";	//$row_value[truck_name]
							
							echo "
								<tr>
									<td>".date("m/d/Y", strtotime($row_value['linedate_added']))."</td>
									<td>".date("m/d/Y", strtotime($row_value['linedate_aquired']))."</td>
									<td>".($row_value['linedate_returned'] > 0 ? date("m/d/Y", strtotime($row_value['linedate_returned'])) : "")."</td>
									<td align='right'>$<span id='equip_id_".$row_value['id']."_old_value'>".number_format($row_value['equipment_value'],2)."</span></td>
									<td align='right'>$
										<input style='width:80px; text-align:right;' type='text' name='equip_id_".$row_value['id']."_value' id='equip_id_".$row_value['id']."_value' onChange='mrr_kill_value_history_item_id(".$row_value['id'].");' value='".number_format($row_value['equipment_value'],2)."'>
									</td>
								</tr>
							";
							
						}						
						echo "
						</table>	
						";	
					}
					?>									
					<div id='odometer_section'></div>
				
					<? if($use_new_uploader > 0) { ?>
               		
               			<br>&nbsp;<br>
                    		<iframe src="mrr_uploader_hack.php?section_id=3&id=<?=$_GET['id']?>" width='550' height='80' border='0' style='border:#000000 solid 0px; background-color:#ffffff;'>
                    		</iframe> 
                    		<div id='attachment_holder'></div>
               		
               		<? } else { ?>
               		
               			<div id='upload_section'></div>
               		
               		<? } ?>
					
					<div class='clear'></div>
										
					<div id='note_section'></div>				
				</td>
			</tr>
			</table>
			<div class='change_log'>
				<?= ($_GET['id'] > 0 ? mrr_get_user_change_log(" and user_change_log.truck_id='".sql_friendly($_GET['id'])."'"," order by user_change_log.linedate_added asc","",1): "") ?>
			</div>
			<div class='PN_disp_log_button'>
				<?
				if($_GET['id'] > 0) 
				{
					echo "<span class='mrr_link_like_on' onClick='ajax_mrr_find_truck_tracking_dispatch_record_all(".$_GET['id'].");'><b>Click for Dispatches for this Truck section</b></span>";
				}
				?>
			</div>
			<div class='PN_disp_log'></div>
		<? } ?>
	</td>
</tr>
</table>

<script type='text/javascript'>
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete this truck?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}
	
	function mrr_oil_changed(cur_miles)
	{
		var cmiles=cur_miles;
		$('#pm_miles_last_oil').val(''+cmiles+'');
		document.my_form.submit();
	}
	
	function mrr_toggle_xml_string(id)
	{
		$('.xml_string_'+id+'').toggle();	
	}
	function ajax_mrr_find_truck_tracking_dispatch_record_all(id)
	{
		$('.PN_disp_log').html('Loading...may take a few seconds...');
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_find_truck_tracking_dispatch_record_all",
		   data: {
		   		"id": id
		   	},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		mytxt=$(xml).find('Disp').text();
		   		$('.PN_disp_log').html(''+mytxt+'');		
		   		$('.xml_details').hide(); 		
		   }
		 });
	}
		
	function mrr_kill_value_history_item_id(equip_id)
	{		
		e_text=$('#equip_id_'+equip_id+'_value').val();
		e_val=get_amount($('#equip_id_'+equip_id+'_value').val());
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_value_update_equip_value_id",
		   data: {
		   		"equip_id": equip_id,
		   		"new_value": e_val
		   	},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		
		   		$('#equip_id_'+equip_id+'_old_value').html(''+e_text+'');
		   		
		   }
		 });
	}
	
<?
if($defaultsarray['sicap_integration'] == 1)
{		
?>
	$('#name_truck').blur(function() {			
			mrr_test_type_name();					
	});
	
	function mrr_test_type_name()
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_test_name_type",
		   data: {"equipment_name": $('#name_truck').val()},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		$(xml).find('Namer').each(function() {
     				var test_result=$(this).find('NamerResult').text();
     				if(test_result==0)
					{				
						$.prompt("Warning: The name for this truck is valid here, but it will not display in accounting application. Use numeric digits only to use with accounting system.");
					}     				
		   		});
		   }
		 });
	}
<?
}
?>
		
	function mrr_eval_rental()
	{		
		if( $('#company_owned').attr('checked') )
		{			
			$('.mrr_shader').attr('style','background-color:#dddddd;');
			$('#rental').attr('style','background-color:#dddddd;');
			//$('#leased_from').val('');
			$('#leased_from').attr('style','background-color:#dddddd;');
		}
		else
		{			
			$('.mrr_shader').attr('style','');
			$('#rental').attr('style','');
			//$('#leased_from').val('');
			$('#leased_from').attr('style','');	
		}		
	}
	
	
	function mrr_mile_checker()
	{
		new_aquired_val=$('#new_linedate_aquired').val();	
		new_aquired_miles=$('#new_miles_pickup').val();			
		new_returned_val=$('#new_linedate_returned').val();		
		new_returned_miles=$('#new_miles_dropoff').val();	
		
		aquired_val=$('.equip_history_linedate:last').val();	
		aquired_miles=$('.linedate_returned:last').val();			
		returned_val=$('.equip_history_pickup:last').val();		
		returned_miles=$('.equip_history_dropoff:last').val();	
		
		if((aquired_val!='' && aquired_miles==0) && (new_aquired_val!='' && new_aquired_miles==0))
		{
			$.prompt("Miles In is required to Aquire this equipment.<br>Please enter the mileage in the Miles In box to add the equipment.");
			
			if(aquired_val!='')		$('.linedate_returned:last').focus();
			else					$('#new_miles_pickup').focus();				
			return false;
		}
		if((returned_val!='' && returned_miles==0) && (new_returned_val!='' && new_returned_miles==0))
		{
			$.prompt("Miles Out is required to Return this equipment.<br>Please enter the mileage in the Miles Out box to return the equipment.");
			
			if(returned_val!='')	$('#.equip_history_dropoff:last').focus();
			else					$('#new_miles_dropoff').focus();	
			return false;
		}
		return true;
	}
	
	function add_equipment_history(disp_flag) {
		
		if(!disp_flag) {
			$('.add_equipment').hide();
			$('#new_linedate_aquired').val('');
			$('#add_equipment_history_flag').val(0);
			return false;
		}
		
		// if any of the history entries are missing a 'returned' field, then we need to alert the user that they can't aquire this euipment
		// since it hasn't been returned yet
		
		new_okay = true;
		
		$('.linedate_returned').each(function() {
			if($(this).val() == '') {
				$.prompt("You must enter the date returned for all entries before you can add a new history<p>Since you cannot acquire it again if you never returned it");
				new_okay = false;
				return false;
			}
		});
		
		if(new_okay) {
			$('#add_equipment_history_flag').val(1);
			$('.add_equipment').show();
		}
	}
	
	function delete_equipment_history(id) {
		$.prompt("Are you sure you want to delete this Equipment History?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						// mark the entry for deletion
						$('#linedate_aquired_'+id).val('deleted');
					}
				}
			}
		);	
	}
	
	function load_odometer_history(element_id, truck_id, delete_blocker) {
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_odometer_history",
		   data: {"truck_id":truck_id , "delete_blocker":delete_blocker},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
				$(element_id).html($(xml).find("OdometerHistory").text());
		   }
		 });
	}
	
	$('#linedate_aquired').datepicker();
	$('#linedate_returned').datepicker();
	$('#linedate_own_op_ins').datepicker();
	$('#linedate_own_op_acc_ins').datepicker();
	$('.datepicker').datepicker();
	$('.mrr_date_input').datepicker();

	function confirm_delete_odometer_reading(odometer_entry_id) {
		$.prompt("Are you sure you want to delete this odometer entry?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						 $.ajax({
						   type: "POST",
						   url: "ajax.php?cmd=delete_odometer_entry",
						   data: {"odometer_entry_id":odometer_entry_id},
						   dataType: "xml",
						   cache:false,
						   success: function(xml) {
								load_odometer_alert();
						   }
						 });
					}
				}
			}
		);	
	}

	<? 
		if(isset($_GET['id']) && $_GET['id'] > 0) 
		{
			//echo " create_upload_section('#upload_section', 3, $_GET[id]); "; 
			
			if($use_new_uploader > 0) 
			{ 
				echo " create_upload_section_alt('#upload_section', 3, $_GET[id]); "; 
			}
			else
			{
				echo " create_upload_section('#upload_section', 3, $_GET[id]); "; 
			}
			
			echo " create_note_section('#note_section', 3, $_GET[id]); "; 
			
			$blocker_val=0;
			if($use_admin_level < 90)		$blocker_val=1;
			 
			echo "
				function load_odometer_alert() {
					load_odometer_history('#odometer_section', $_GET[id], $blocker_val);
				}
				
				load_odometer_alert();
			";	
			echo " mrr_eval_rental(); ";		
		}
	?>

	function mrr_verify_user_unique(myid)
	{		
		$('#mrr_naming_message').html('');	
		mrr_lab="Truck";
		mrr_lab2="name_truck";
		mrr_code=3;
		
		new_name=$('#'+mrr_lab2+'').val();
		held=new_name;
		
		$.ajax({
			url: "ajax.php?cmd=mrr_verify_item_name",
			type: "post",
			dataType: "xml",
			data: {
				"name": new_name,
				"mode": mrr_code,
				"id": myid
			},
			error: function() {
				$.prompt("Error: Cannot check for duplication of "+mrr_lab+" "+new_name+".");
				//$('#'+mrr_lab2+'').val(held);
				$('#mrr_naming_message').html(''+mrr_lab+' reset, must verify uniqueness.');	
				$('#mrr_naming_message').css('color','red');	
			},
			success: function(xml) {				
				mytxt=$(xml).find('mrrTab').text();
				if(mytxt=="")
				{					
					$('#mrr_naming_message').html(''+mrr_lab+' is valid.');	
					$('#mrr_naming_message').css('color','blue');		
				}
				else
				{
					$.prompt( ""+ mytxt +"" );
					//$('#'+mrr_lab2+'').val(held);
					$('#mrr_naming_message').html(''+mrr_lab+' must be unique.');	
					$('#mrr_naming_message').css('color','red');									
				}				
			}
		});	
		
	}
		
	<? if($use_admin_level < 50) { ?>
		$('.mrr_button_access').hide();
		$('.mrr_delete_access').hide();		
		$('.mrr_button_access_notice').html('View Only.<br>Consult Supervisor.');
		$( ":input" ).attr('disabled','disabled');
	<? } else { ?>
		$( ":input" ).attr('disabled','');
		$('.mrr_button_access').show();
		$('.mrr_delete_access').show();
		$('.mrr_button_access_notice').html('&nbsp;');
	<? } ?>
	
	
	function duplicate_truck(id) 
	{
		$.prompt("Are you sure you want to <span class='alert'>duplicate</span> this Truck?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						window.location = "<?=$_SERVER['SCRIPT_NAME']?>?duplicate="+id;
					}
				}
			}
		);	
	}
</script>

<? include('footer.php') ?>
