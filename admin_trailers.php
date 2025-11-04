<? include('application.php') ?>
<? $admin_page = 1 ?>
<?	
	$sql = "update trailers set deleted = 1	where trailer_name='unkown'";
	simple_query($sql);
	$sql = "update trailers set active=1 where active>1";
	simple_query($sql);
	
	if(isset($_GET['pmi_fed']))
	{
		$res=mrr_pmi_fed_trailers_due_soon(7);
		//echo "<h2>Trailer PMI/FED Due Report: (".$res['num']." Found)</h2><br>".$res['report']."<br>";	
	}

    $mrr_dir="".$defaultsarray['base_path']."/www/trailers/";
    $mrr_trailer_image="";
	
	// duplication effrot moved to separate page.  Retaining in case a random link has this encoded.
	if(isset($_GET['duplicate'])) 
	{	//Added Nov 2015...MRR
		$dup_id = duplicate_row('trailers', $_GET['duplicate']);
				
		$sql = "
			update trailers set
				trailer_name= CONCAT('00',trailer_name),
				nick_name= CONCAT('00',nick_name),
				location_updated = NOW(),
				linedate_aquired = NOW(),
				linedate_returned = '0000-00-00 00:00:00',
				linedate_last_pmi= '0000-00-00 00:00:00',
				linedate_last_fed= '0000-00-00 00:00:00',
				sicap_coa_created='0',
				trailer_regist_file='',
				license_plate_no='',
				pending_repairs_list='',
				pending_repairs=0,
				pending_repairs_date_opened = '0000-00-00 00:00:00',
				pending_repairs_date_inspect = '0000-00-00 00:00:00',
				pending_repairs_date_repair = '0000-00-00 00:00:00',
				pending_repairs_date_closed = '0000-00-00 00:00:00',
				pending_repairs_made=0,
				pending_repairs_internal=0,
				vin='',
				pn_tracking_has=0,
    				pn_tracking_num='',
    				pn_tracking_val='0.00',
    				geotab_trailer_id='',
				made_by_user='".(int) $_SESSION['user_id']."',
				current_location='',
				active='0',
				deleted='0'
				
			where id = '".sql_friendly($dup_id)."'
		";
		simple_query($sql);
		
		header("Location: admin_trailers.php?id=".$dup_id);
		die;
	}
	
	if(isset($_GET['did'])) {
		$sql = "
			update trailers
			
			set	deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_delete = simple_query($sql);
		$mrr_activity_log_notes.="Deleted trailer ".$_GET['did'].". ";
		
		mrr_send_insurance_information(0,$_GET['did'],0,$_SESSION['user_id']);		//truck,trailer,driver,user
	}

	if(isset($_POST['truck_id_list'])) {
		foreach($_POST['truck_id_list'] as $id) {
			$sql = "
				update trailers
				set monthly_cost_actual = '".sql_friendly($_POST['monthly_cost_actual_'.$id])."'
				where id = '".sql_friendly($id)."'
			";					//, monthly_cost = '".sql_friendly($_POST['monthly_cost_'.$id])."'
			simple_query($sql);
		}
	}
	
	if(isset($_GET['new'])) {
		$_GET['id']=0;
	}
	
	if(isset($_POST['trailer_name'])) {
		if(isset($_POST['allow_multiple'])) {
			$use_allow_multiple = 1;
		} else {
			$use_allow_multiple = 0;
		}
		
		$invalid_id=mrr_ensure_trailer_name_unique($_GET['id'],$_POST['trailer_name']);
		if($invalid_id > 0)
		{	//redirect to existing trailer if it already exists
			header("Location: $_SERVER[SCRIPT_NAME]?id=$invalid_id&duplicate=1");
			die;	
		}
         
         if(!isset($_POST['trailer_photo']))		$_POST['trailer_photo']="";
         
         $mrr_msg="";
         $mrr_msg.="<br>File Name: ".$_FILES['mrr_import']['name'].".";
         $mrr_msg.="<br>File Temp: ".$_FILES['mrr_import']['tmp_name'].".";
         $mrr_msg.="<br>File Type: ".$_FILES['mrr_import']['type'].".";
         $mrr_msg.="<br>File Size: ".$_FILES['mrr_import']['size'].".";
         $mrr_msg.="<br>File Error: ".$_FILES['mrr_import']['error'].".";
         
         if(isset($_FILES['mrr_import']['tmp_name']) && $_FILES['mrr_import']['tmp_name'] !="")
         {
              $typer="";
              if(substr_count(strtolower($_FILES['mrr_import']['name']),".jpg") > 0 || substr_count(strtolower($_FILES['mrr_import']['name']),".jpeg") > 0)		$typer=".jpg";
              if(substr_count(strtolower($_FILES['mrr_import']['name']),".png") > 0 || substr_count(strtolower($_FILES['mrr_import']['name']),".png") > 0)		$typer=".png";
              if(substr_count(strtolower($_FILES['mrr_import']['name']),".gif") > 0 || substr_count(strtolower($_FILES['mrr_import']['name']),".giff") > 0)		$typer=".gif";
              
              if($typer!="")
              {
                   $mrr_msg.="<br><br>File Source: ".$_FILES['mrr_import']['tmp_name'].".";
                   $mrr_msg.="<br><br>File Dest: ".$mrr_dir."trailer_photo_".$_GET['id']."".$typer.".";
                   
                   if(move_uploaded_file ( $_FILES['mrr_import']['tmp_name'] , "".$mrr_dir."trailer_photo_".$_GET['id']."".$typer.""))
                   {
                        $_POST['trailer_photo']="trailer_photo_".$_GET['id']."".$typer."";
                        
                        $mrr_msg.="<br><br>File Saved.";
                   }
              }
         }
		
		if($_GET['id'] == 0)
		{
     		$sql = "
     			insert into trailers
     				(trailer_name,trailer_tire_size,made_by_user,pending_repairs,pending_repairs_list)     				
     			values
     			 	('New Trailer','LP 22.5','".(int) $_SESSION['user_id']."',0,'')
     		";
     		$data = simple_query($sql);
     		$_GET['id']=mysqli_insert_id($datasource);
		}
		
		
		
		$sql = "
			select trailer_name
			
			from trailers
			where id = '".sql_friendly($_GET['id'])."'
		";
		$data_old = simple_query($sql);
		$row_old = mysqli_fetch_array($data_old);
		
		// update in progress 
		$sql = "
			update trailers set
				trailer_name = '".sql_friendly($_POST['trailer_name'])."',
				nick_name = '".sql_friendly($_POST['nick_name'])."',
				trailer_owner = '".sql_friendly($_POST['trailer_owner'])."',
				trailer_year = '".sql_friendly($_POST['trailer_year'])."',
				trailer_make = '".sql_friendly($_POST['trailer_make'])."',
				trailer_model = '".sql_friendly($_POST['trailer_model'])."',
				trailer_tire_size= '".sql_friendly(trim($_POST['trailer_tire_size']))."',
				vin = '".sql_friendly(strtoupper($_POST['vin']))."',
				license_plate_no = '".sql_friendly($_POST['license_plate_no'])."',				
				monthly_cost_actual = '".sql_friendly($_POST['monthly_cost_actual'])."',
				allow_multiple = $use_allow_multiple,
				rental_flag = ".(isset($_POST['rental_flag']) ? '1' : '0').",
				in_the_shop = '".(isset($_POST['in_the_shop']) ? '1' : '0')."',
				no_insurance = ".(isset($_POST['no_insurance']) ? '1' : '0').",
				fubar_trailer = ".(isset($_POST['fubar_trailer']) ? '1' : '0').",
				active = '".(isset($_POST['active']) ? '1' : '0')."',
				special_project = '".(isset($_POST['special_project']) ? '1' : '0')."',
				interchange_flag = '".(isset($_POST['interchange_flag']) ? '1' : '0')."',
				linedate_last_pmi='".($_POST['linedate_last_pmi'] != "" ? date("Y-m-d",strtotime($_POST['linedate_last_pmi'])) : "0000-00-00 00:00:00")."',	
				pmi_test_ignore = '".(isset($_POST['pmi_test_ignore']) ? '1' : '0')."',		
				linedate_last_fed='".($_POST['linedate_last_fed'] != "" ? date("Y-m-d",strtotime($_POST['linedate_last_fed'])) : "0000-00-00 00:00:00")."',	
				fed_test_ignore = '".(isset($_POST['fed_test_ignore']) ? '1' : '0')."',		
				
				trailer_regist_file='".sql_friendly(trim($_POST['trailer_regist_file']))."',
				
				pending_repairs='".(isset($_POST['pending_repairs']) ? '1' : '0')."',
				pending_repairs_list='".sql_friendly(trim($_POST['pending_repairs_list']))."',
				
				pending_repairs_date_opened = '".(trim($_POST['pending_repairs_date_opened'])!=""  ? "".date("Y-m-d",strtotime($_POST['pending_repairs_date_opened']))." 00:00:00" :"0000-00-00 00:00:00")."',
				pending_repairs_date_inspect = '".(trim($_POST['pending_repairs_date_inspect'])!=""  ? "".date("Y-m-d",strtotime($_POST['pending_repairs_date_inspect']))." 00:00:00" :"0000-00-00 00:00:00")."',
				pending_repairs_date_repair = '".(trim($_POST['pending_repairs_date_repair'])!=""  ? "".date("Y-m-d",strtotime($_POST['pending_repairs_date_repair']))." 00:00:00" :"0000-00-00 00:00:00")."',
				pending_repairs_date_closed = '".(trim($_POST['pending_repairs_date_closed'])!=""  ? "".date("Y-m-d",strtotime($_POST['pending_repairs_date_closed']))." 00:00:00" :"0000-00-00 00:00:00")."',
				pending_repairs_made='".(isset($_POST['pending_repairs_made']) ? '1' : '0') ."',
				pending_repairs_internal='".(isset($_POST['pending_repairs_internal']) ? '1' : '0') ."',
				
				pn_tracking_has=".(isset($_POST['pn_tracking_has']) ? '1' : '0').",
    				pn_tracking_num='".sql_friendly($_POST['pn_tracking_num'])."',
    				pn_tracking_val='".sql_friendly(money_strip($_POST['pn_tracking_val']))."',
    				
    				geotab_trailer_id='".sql_friendly(trim($_POST['geotab_trailer_id']))."',
				
				company_owned = '".(isset($_POST['company_owned']) ? '1' : '0') ."',
				
				image_rotation='".sql_friendly($_POST['image_rotation'])."',
				trailer_photo='".sql_friendly($_POST['trailer_photo'])."'			
				
			where id = '".sql_friendly($_GET['id'])."'
		";		//dedicated_trailer = '".(isset($_POST['dedicated_trailer']) ? '1' : '0') ."'
				//monthly_cost = '".sql_friendly($_POST['monthly_cost'])."',
		$data = simple_query($sql);
		
		$mrr_activity_log_notes.="Updated trailer ".$_GET['id']." info. ";	
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,$_GET['id'],0,0,0,"Updated trailer ".$_GET['id']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		
		if($defaultsarray['sicap_integration'] == 1 && $row_old['trailer_name'] != $_POST['trailer_name']) 
			sicap_update_trailers($_GET['id'], $row_old['trailer_name']);
		
		if(isset($_POST['coa_updates']))
		{	//update COAs anyway.
			sicap_update_trailers($_GET['id'], $row_old['trailer_name']);
		}

		$eval_inactive=0;
		if(isset($_POST['equipment_history_array'])) {
			foreach($_POST['equipment_history_array'] as $value) {
				if(strtolower($_POST['linedate_aquired_'.$value]) == 'deleted' || $_POST['linedate_aquired_'.$value] == '') {
					$sql = "
						update equipment_history
						set deleted = 1
						where id = '".sql_friendly($value)."'
					";
					simple_query($sql);
					
                     $sql = "
						update trailers
						set active=0
						where id = '".sql_friendly($_GET['id'])."'
					";
                     simple_query($sql);
				} else {
					
					$equipment_val = money_strip($_POST['equipment_value_'.$value]);
					$miles_pickup = money_strip($_POST['miles_pickup_'.$value]);
					$miles_dropoff = money_strip($_POST['miles_dropoff_'.$value]);
					
					$sql = "
						update equipment_history
						set linedate_aquired = '".date("Y-m-d", strtotime($_POST['linedate_aquired_'.$value]))."',
							linedate_returned = '".($_POST['linedate_returned_'.$value] != '' ? date("Y-m-d", strtotime($_POST['linedate_returned_'.$value])) : "0000-00-00")."',
							equipment_value = '".($equipment_val != '' && is_numeric($equipment_val) ? $equipment_val : "0")."',
							miles_pickup = '".($miles_pickup != '' && is_numeric($miles_pickup) ? $miles_pickup : "0")."',
							miles_dropoff = '".($miles_dropoff != '' && is_numeric($miles_dropoff) ? $miles_dropoff : "0")."'
						
						where id = '".sql_friendly($value)."'
					";
					simple_query($sql);
					
					if($_POST['linedate_returned_'.$value] != '')	$eval_inactive=1;
					elseif($_POST['linedate_returned_'.$value] == '')	$eval_inactive=0;
					
					if($eval_inactive > 0)
					{
						//update_equipment_active_flag($_GET['id'], 2);			//Disabled for James in favor of active checkbox for temporary time period.  11/20/2017...MRR
						//mrr_update_trailer_active_flag($_GET['id'],0);			//Disabled for James in favor of active checkbox for temporary time period.  11/20/2017...MRR
                         $sql = "
                            update trailers
                            set active=0
                            where id = '".sql_friendly($_GET['id'])."'
                        ";
                         simple_query($sql);
					}
					else
					{
						//mrr_update_trailer_active_flag($_GET['id'],1);			//Disabled for James in favor of active checkbox for temporary time period.  11/20/2017...MRR
                         $sql = "
                            update trailers
                            set active=1
                            where id = '".sql_friendly($_GET['id'])."'
                        ";
                         simple_query($sql);
					}
				}
			}
		}
		if($eval_inactive == 0)
		{
			//update_equipment_active_flag($_GET['id'], 2);		//Disabled for James in favor of active checkbox for temporary time period.  11/20/2017...MRR
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
					miles_dropoff)
					
				values (2,
					'".sql_friendly($_GET['id'])."',
					'".(money_strip($_POST['new_equipment_value']) != '' ? sql_friendly(money_strip($_POST['new_equipment_value'])) : "0")."',
					now(),
					'".($_POST['new_linedate_aquired'] != '' ? date("Y-m-d", strtotime($_POST['new_linedate_aquired'])) : "0000-00-00")."',
					'".($_POST['new_linedate_returned'] != '' ? date("Y-m-d", strtotime($_POST['new_linedate_returned'])) : "0000-00-00")."',
					'".(money_strip($_POST['new_miles_pickup']) != '' ? sql_friendly(money_strip($_POST['new_miles_pickup'])) : "0")."',
					'".(money_strip($_POST['new_miles_dropoff']) != '' ? sql_friendly(money_strip($_POST['new_miles_dropoff'])) : "0")."')
			";
			simple_query($sql);
             
             $sql = "
						update trailers
						set active=1
						where id = '".sql_friendly($_GET['id'])."'
					";
             simple_query($sql);
		}
		if(isset($_POST['send_insurance_report']))
		{
			mrr_send_insurance_information(0,$_GET['id'],0,$_SESSION['user_id']);		//truck,trailer,driver,user
		}
		header("Location: $_SERVER[SCRIPT_NAME]?id=$_GET[id]");
		die;
	}
	
	$mrr_dup_msg="";
	if(isset($_GET['duplicate']))
	{
		$mrr_dup_msg="<span class='alert'><b>Attempting to save a Duplicate Trailer... it is already in the system.<br>Redirected to this existing trailer.</b></span>";
	}
	/*
	if(isset($_GET['new'])) {
		$sql = "
			insert into trailers
				(trailer_name)
				
			values ('New Trailer')
		";
		$data = simple_query($sql);
		header("Location: $SCRIPT_NAME?id=".mysql_insert_id());
		die();
	}
	*/
	
	
	if(!isset($_POST['filter_active']))		$_POST['filter_active']=1;
	
	if(!isset($_POST['sort_items_by']))		$_POST['sort_items_by']="trailer_name";
	if(!isset($_POST['sort_items_direction']))	$_POST['sort_items_direction']="asc";
	
	$sort_order="order by active desc, trailer_name";
	if(isset($_POST['sort_items_direction']) && isset($_POST['sort_items_by']))
	{
		$sort_order="order by active desc, ".$_POST['sort_items_by']." ".$_POST['sort_items_direction'].", trailer_name";	
	}	
	
	$sql = "
		select *,
			(select count(*) from equipment_history where equipment_type_id = 2 and equipment_id = trailers.id and deleted = 0) as equipment_history_entry
		
		from trailers
		where deleted = 0
			".($_POST['filter_active'] > 0 ? "and active>0" : "and active=0")."
		".$sort_order."
	";
	$data = simple_query($sql);
	
	$trailer_maint_requests="";
	
	$mrr_activity_log_notes.="Viewed list of trailers. ";
	
	$uuid = "_".date("Ymd",time());	//.createuuid();
	$excel_filename = "".($_POST['filter_active']==0 ? "in" : "")."active_trailer_email".$uuid.".csv";
	$export_file = "";
	$use_excel=1;
	/*
	$export_file .= "".($_POST['filter_active']==0 ? "Ina" : "A")."ctive Trailer E-mail List".chr(9).
     	"".chr(9).
     	"".chr(9).
     	"".chr(9).
     	"".chr(9).
        "".chr(9).
        "".chr(9).
        "".chr(9).
        "".chr(9).
     	"".chr(9);
	$export_file .= chr(13);	
	
	$export_file .= "ID".chr(9).
		"Trailer Name".chr(9).
		"Owner".chr(9).
        "Year".chr(9).
        "Make".chr(9).
        "Model".chr(9).
        "Vin".chr(9).
		"PMI".chr(9).
		"FED".chr(9).
		"Rental".chr(9);
	$export_file .= chr(13);	
	*/
    $export_file .= "".($_POST['filter_active']==0 ? "Ina" : "A")."ctive Trailer E-mail List";
    $export_file .= chr(13);
    
    $export_file .= "ID,".
         "Trailer Name,".
         "Owner,".
         "Year,".
         "Make,".
         "Model,".
         "Vin,".
         "PMI,".
         "FED,".
         "Rental";
    $export_file .= chr(13);
?>
<?
$usetitle = "Trailer Master";
$use_title = "Trailer Master";
$days_counted=(int)$defaultsarray['trailer_pmi_date_days'];
$feds_counted=(int)$defaultsarray['trailer_fed_date_days'];
?>
<? include('header.php') ?>

<table class='standard12' style='text-align:left'>
<tr>
	<td valign='top'>
		<form action='' method='post'>
		<table class='admin_menu1'>
		<tr>
			<td valign='top' colspan='7'><font class='standard18'><b>Trailer Master (Admin Trailers)</b></font></td>
			<td valign='top' colspan='5'>
				<!--
				<a href='http://www.xtralease.com/Rent-Lease-Or-Buy/Pages/Trailer-Rental-Options.aspx' target='_blank'>XtraLease Site</a>
				<a href='https://www.myqualcomm.com/ttracsWeb/sso/dashboard.do' target='_blank'>XtraLease Acct</a>
				<br>
				-->
				<a href='https://secure.xtra.com/WebDocuments/WebDocuments.aspx?PageType=XTRA_Access' target='_blank'>XtraLease Site</a>
			</td>
		</tr>
		<tr>
			<td valign='top' colspan='7'>
                <a href="<?=$SCRIPT_NAME?>?new=1">Add New Trailer</a>
                 <? if($_SESSION['user_id']==23 || $_SESSION['user_id']==15 || $_SESSION['user_id']==18 || $_SESSION['user_id']==19 || $_SESSION['user_id']==81) { ?>
                     &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                     &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                     <a href="equipment_special_notices.php" target="_blank">Edit Special Equipment Notices</a>
                 <? } ?>
            </td>
			<td valign='top' colspan='5'><a href='https://www.starleasing.com/customer/' target='_blank'>Starleasing Site</a></td>			
		</tr>
		<!---
		https://insight.skybitz.com/login.jsp
		?strUserName=conard&strPassword=welcome1234
		?strUserName=CTI2181&strPassword=welcome123
		
		
		<tr>
			<td colspan='7'>&nbsp;</td>
			<td colspan='5'><a href='https://insight.skybitz.com/login.jsp' target='_blank'>Skybitz Site 1</a><br></td>
		</tr>
		---->
		<tr>
			<td valign='top' colspan='7'>&nbsp;</td>
			<td valign='top' colspan='5'><a href='https://insight.skybitz.com/LAABSearch?event=menuSearchAssets&requestorUrl=/LAABSearch?event=menustartsearch&dispatchTo=/LocateAssets/NewAdvAssetSearchResults.jsp&map=no&optMulTerminal=AllAssets' target='_blank'>Skybitz Site</a><br></td>
		</tr>
        <tr>
            <td valign='top' colspan='7'>&nbsp;</td>
            <td valign='top' colspan='5'><a href='https://www.lbtelematics.net/track1/Track' target='_blank'>5 Fleet Equipment Rentals</a><br></td>
        </tr>    
		<tr>
			<td colspan='12'>
				<div>NOTE: Entries in <span class='alert'>red</span> are missing the <span class='alert'>Date Acquired</span> information</div>
				<span class='section_heading'><?=get_active_trailer_count() ?> Active Trailer(s)</span>
			</td>
		</tr>
		<tr>
			<td colspan='7'>
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
					<option value='trailer_name' <?= ($_POST['sort_items_by']=="trailer_name" ? " selected" : "")?>>Trailer Name</option>
					<option value='nick_name' <?= ($_POST['sort_items_by']=="nick_name" ? " selected" : "")?>>Nick Name</option>
					<option value='trailer_owner' <?= ($_POST['sort_items_by']=="trailer_owner" ? " selected" : "")?>>Owner</option>
					<option value='vin' <?= ($_POST['sort_items_by']=="vin" ? " selected" : "")?>>VIN</option>
					<option value='linedate_last_pmi' <?= ($_POST['sort_items_by']=="linedate_last_pmi" ? " selected" : "")?>>PMI</option>
					<option value='linedate_last_fed' <?= ($_POST['sort_items_by']=="linedate_last_fed" ? " selected" : "")?>>FED</option>
					<option value='monthly_cost_actual' <?= ($_POST['sort_items_by']=="monthly_cost_actual" ? " selected" : "")?>>Monthly Cost</option>
					<option value='interchange_flag' <?= ($_POST['sort_items_by']=="interchange_flag" ? " selected" : "")?>>Interchange</option>	
					<option value='rental_flag' <?= ($_POST['sort_items_by']=="rental_flag" ? " selected" : "")?>>Rental Flag</option>			
				</select>	
				<select name='sort_items_direction' onChange='submit();'>
					<option value='asc' <?= ($_POST['sort_items_direction']=="asc" ? " selected" : "")?>>Asc</option>
					<option value='desc' <?= ($_POST['sort_items_direction']=="desc" ? " selected" : "")?>>Desc</option>			
				</select>	
			</td>
			<td colspan='5'><input type='submit' value='Update Trailer Cost' class='mrr_button_access'></td>
		</tr>
		<tr>
			<td><b>Trailer Name</b></td>
			<td><b>&nbsp;</b></td>
			<td><b>&nbsp;</b></td>
			<td><b>Nick Name</b></td>	
			<td><b>VIN</b></td>		
			<td><b>Owner</b></td>
			<td><b>PMI</b></td>
			<td><b>FED</b></td>
			<td><b>Monthly Cost<br>(Actual)</b></td>
			<td><b>Interchange</b></td>
			<td><b>Rental Flag</b></td>
			<td>&nbsp;</td>
		</tr>
		<? while($row = mysqli_fetch_array($data)) { ?>
			<?
				$last_maint_inspect_id=mrr_find_last_inspection($row['id']);
				$pmi_pending=0;
				$fed_pending=0;
				if($last_maint_inspect_id > 0)
				{
					$insres=mrr_form_trailer_inspection_list($last_maint_inspect_id);
					/*
					$insres['used']=0;
                    	$insres['passed']=0;
                    	$insres['created_by']="";
                    	$insres['updated_by']="";
                    	$insres['updated']="";
                    	$insres['created']="";
                    	$insres['inspection']=0;
                    	$insres['used_pmi']=0;		
                    	$insres['used_fed']=0;
					*/
					if($insres['used'] > 0 && $insres['used_pmi'] > 0)	$pmi_pending=1;	//PMI inspection is still pending					
					if($insres['used'] > 0 && $insres['used_fed'] > 0)	$fed_pending=1;	//FED inspection is still pending					
				}			
			?>
			<tr>
				<td><a href="<?=$SCRIPT_NAME?>?id=<?=$row['id']?>" class='<?=($row['equipment_history_entry'] == 0 ? 'alert' : ($row['active'] ? '' : 'inactive'))?>'><?=$row['trailer_name']?></a></td>
				<td><?=($row['fubar_trailer'] > 0  ? "<span class='alert'><b>FUBAR</b></span>" : "")  ?></td>
				<td><?=(trim($row['license_plate_no'])==""  ? "<span class='alert'><b>No Plate</b></span>" : "")  ?></td>
				<td><?=($row['special_project'] > 0  ? "<span class='special_project'>".trim($row['nick_name'])."</span>" : trim($row['nick_name']))  ?></td>
				<td><?=($row['special_project'] > 0  ? "<span class='special_project'>".trim($row['vin'])."</span>" : trim($row['vin']))  ?></td>
				<td><?=($row['special_project'] > 0  ? "<span class='special_project'>".trim($row['trailer_owner'])."</span>" : trim($row['trailer_owner'])) ?></td>
				<td>
					<?
					$pmi_displayer="";
					if($days_counted > 0 && $row['pmi_test_ignore'] ==0)
					{
						if($row['linedate_last_pmi']=="0000-00-00 00:00:00")
						{
							$pmi_displayer="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",time())."' target='_blank'><span class='alert'><b>OVERDUE!</b></span></a>";
						}
						else
						{
							$now_dater=date("ymd",time());
							$next_run=date("m/d/Y", strtotime("+".$days_counted." days", strtotime($row['linedate_last_pmi'])));	
							$due_compare=date("ymd",strtotime($next_run));
							if((int) $due_compare <= (int) $now_dater)
							{								
								if($pmi_pending > 0)
								{
									$pmi_displayer="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' title='Inspection Pending'><span style='color:yellow;background-color:#000000;'><b>".$next_run."</b></span></a>";	//  #ffc200;
								}
								else
								{
									$pmi_displayer="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=1&due=".date("Y-m-d",strtotime($next_run))."' target='_blank'><span class='alert'><b>".$next_run."</b></span></a>";
								}
							}
							else
							{
								$pmi_displayer="<span style='color:green;'><b>".$next_run."</b></span>";
							}
						}
					}
					if($row['pmi_test_ignore'] > 0)	$pmi_displayer="<span style='color:green;'><b>N/A</b></span>";
					
					echo $pmi_displayer;
					?>
				</td>
				<td>
					<?
					$fed_displayer="";
					if($feds_counted > 0 && $row['fed_test_ignore'] ==0)
					{
						if($row['linedate_last_fed']=="0000-00-00 00:00:00")
						{
							$fed_displayer="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",time())."' target='_blank'><span class='alert'><b>OVERDUE!</b></span></a>";
						}
						else
						{
							$now_dater=date("ymd",time());
							$next_run=date("m/d/Y", strtotime("+".$feds_counted." days", strtotime($row['linedate_last_fed'])));	
							$due_compare=date("ymd",strtotime($next_run));
							if((int) $due_compare <= (int) $now_dater)
							{								
								if($fed_pending > 0)
								{
									$fed_displayer="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",strtotime($next_run))."' target='_blank' title='Inspection Pending'><span style='color:yellow;background-color:#000000;'><b>".$next_run."</b></span></a>";	//background-color:#000000;
								}
								else
								{
									$fed_displayer="<a href='maint.php?id=0&e_type=2&e_id=".$row['id']."&inspect=2&due=".date("Y-m-d",strtotime($next_run))."' target='_blank'><span class='alert'><b>".$next_run."</b></span></a>";
								}
							}
							else
							{
								$fed_displayer="<span style='color:green;'><b>".$next_run."</b></span>";
							}
						}
					}
					if($row['fed_test_ignore'] > 0)	$fed_displayer="<span style='color:green;'><b>N/A</b></span>";
					
					echo $fed_displayer;
					?>
				</td>
				<td>
					<input name='monthly_cost_actual_<?=$row['id']?>' value="<?=$row['monthly_cost_actual']?>" style='width:75px;text-align:right'>
					<input name="truck_id_list[]" value="<?=$row['id']?>" type='hidden'>
				</td>
				<!--
				<td>
					<input name='monthly_cost_<?=$row['id']?>' value="<?=$row['monthly_cost']?>" style='width:75px;text-align:right'>
					
				</td>
				-->
				<td><?=($row['interchange_flag'] ? '<b>Interchange</b>' : '')?></td>
				<td><?=($row['rental_flag'] ? '<b>Rental</b>' : '')?></td>
				<td><a href="javascript:confirm_delete(<?=$row['id']?>)" class='mrr_delete_access'><img src="images/delete_sm.gif" border="0"></a></td>
				<?
                /*
                 $export_file .= "".$row['id']."".chr(9).
     				"".trim($row['trailer_name'])."".chr(9).
     				"".trim($row['trailer_owner'])."".chr(9).
                    "".trim($row['trailer_year'])."".chr(9).
                    "".trim($row['trailer_make'])."".chr(9).
                    "".trim($row['trailer_model'])."".chr(9).
                    "".trim($row['vin'])."".chr(9).
                    "".trim(strip_tags($pmi_displayer))."".chr(9).
     				"".trim(strip_tags($fed_displayer))."".chr(9).
     				"".trim(($row['rental_flag'] ? 'Rental' : ''))."".chr(9);
     			$export_file .= chr(13);	
                 */
				$export_file .= "".$row['id'].",".
     				"\"".trim($row['trailer_name'])."\",".
     				"\"".trim($row['trailer_owner'])."\",".
                    "".trim($row['trailer_year']).",".
                    "".trim($row['trailer_make']).",".
                    "".trim($row['trailer_model']).",".
                    "".trim($row['vin']).",".
                    "".trim(strip_tags($pmi_displayer)).",".
     				"".trim(strip_tags($fed_displayer)).",".
     				"".trim(($row['rental_flag'] ? 'Rental' : ''))."";
     			$export_file .= chr(13);	
				?>
			</tr>
		<? } ?>
		<tr>
			<td colspan='7'></td>
			<td colspan='5'><input type='submit' value='Update Trailer Cost' class='mrr_button_access'></td>
		</tr>
		</table>		
		<? 
		$prefix="<br><center><a href=\"admin_trailers.php\">Reset Trailer selection to get the Excel Trailer E-Mail List</a><center><br>";
		if($use_excel > 0 && !isset($_GET['id']) ) 
		{
			$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
			fwrite($fp, $export_file); 
			fclose($fp);
			
			$prefix="<br><center><a href=\"/temp/".$excel_filename."\" target='_blank'>Click for Excel Trailer E-Mail List</a><center><br>";			
		} 	
					
		echo $prefix;
		
		echo "<br><center><span style='color:#0000CC; cursor:pointer;' onClick='mrr_run_maint_request_report();'><b>Run Trailer PMI/FED Report</b></span><center><br>";
		//<a href=\"admin_trailers.php?pmi_fed=1\" target='_blank'>Run Trailer PMI/FED Report</a>
		?>
		</form>
		
	</td>
	<td valign='top' style='width:550px'>
		<? if(isset($_GET['id'])) { ?>
			<form action="<?=$SCRIPT_NAME?>?id=<?=$_GET['id']?>" enctype="multipart/form-data" method="post">
			<table class='admin_menu2'>
			<?
				$e_type=option_exists_mrr_alt('equipment_type', 'trailer');
				
				$trailer_maint_requests="";
				if($_GET['id'] > 0)			$trailer_maint_requests=mrr_display_maint_request_section($e_type,$_GET['id'],550);
								
				$sql = "
					select *
					
					from trailers
					where id = '".sql_friendly($_GET['id'])."'
				";
				$data_edit = simple_query($sql);
				$row_edit = mysqli_fetch_array($data_edit);
				
				if($row_edit['trailer_tire_size']=="")		$row_edit['trailer_tire_size']="LP 22.5";
				
				$mrr_activity_log_notes.="View trailer ".$_GET['id']." info. ";
				$mrr_activity_log_trailer=$_GET['id'];

				$sql = "
					select *
					
					from equipment_history
					where equipment_type_id = 2
						and equipment_id = '".sql_friendly($_GET['id'])."'
						and equipment_id > 0
						and deleted = 0
					order by linedate_aquired
				";
				$data_history = simple_query($sql);
				
				if(isset($_POST['trailer_name'])) {
					echo "<tr><td colspan='2'><font color='red'><b>Update Successful</b></font></td></tr>";
				}
				
				
			?>
			<tr>
				<td colspan='3'><?=$mrr_dup_msg ?></td>
			</tr>
			<tr>
				<td><b>Trailer Name</b> <?= show_help('admin_trailers.php','Trailer Name') ?> &nbsp; &nbsp; &nbsp; &nbsp; (Should be Numeric for COAs)</td>
				<td colspan='2'>
					<input id="trailer_name" name="trailer_name" value="<?=$row_edit['trailer_name']?>" onChange='mrr_verify_user_unique(<?=$_GET['id']?>);'>
					 <span id='mrr_naming_message'></span>
				</td>
			</tr>
			<tr>
				<td><b>Trailer Nick Name</b> <?= show_help('admin_trailers.php','Nick Name') ?></td>
				<td colspan='2'><input id="nick_name" name="nick_name" value="<?=$row_edit['nick_name']?>"></td>
			</tr>
			<tr>
				<td><label for='company_owned'><b>Company Owned</b></label> <?= show_help('admin_trailers.php','Company Owned') ?></td>
				<td><input type='checkbox' name='company_owned' id='company_owned' <? if($row_edit['company_owned']) echo 'checked'?> onClick='mrr_eval_rental();'></td>
				<td rowspan='4' align='right'>
					<?
					if($_GET['id'] > 0 && 1==2)
					{
						echo "
							<div class='toolbar_button_mrr' onclick='duplicate_trailer(".$_GET['id'].");'>
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
			<tr class='mrr_shader'>
				<td><label for='interchange_flag'><b>Trailer Interchange</b></label> <?= show_help('admin_trailers.php','Trailer Interchange') ?></td>
				<td><input type='checkbox' name='interchange_flag' id='interchange_flag' <? if($row_edit['interchange_flag']) echo 'checked'?> onClick='mrr_eval_rental();'></td>
			</tr>
			<!--
			<tr>
				<td><b>Monthly Cost</b> <?= show_help('admin_trailers.php','Monthly Cost Budget') ?></td>
				<td>$ <input name="monthly_cost" value="<?=$row_edit['monthly_cost']?>" size='10' style='text-align:right;'> Budget</td>
			</tr>
			-->
			<tr>
				<td><b>Monthly Cost</b> <?= show_help('admin_trailers.php','Monthly Cost Actual') ?></td>
				<td>$ <input name="monthly_cost_actual" value="<?=$row_edit['monthly_cost_actual']?>" size='10' style='text-align:right;'> Actual</td>
			</tr>
			<tr>
				<td><label for='active'><b>Active</b></label> <?= show_help('admin_trailers.php','Active') ?></td>
				<td><input type='checkbox' name='active' <? if($row_edit['active']) echo 'checked'?>></td>
			</tr>
			<tr>
				<td><label for='special_project'><b>Special Project</b></label> <?= show_help('admin_trailers.php','special_project') ?></td>
				<td colspan='2'><input type='checkbox' name='special_project' <? if($row_edit['special_project'] > 0) echo 'checked'?>></td>
			</tr>
			<tr>
				<td><label for='allow_multiple'><b>Allow Trailer to be used more than once at a time</b></label> <?= show_help('admin_trailers.php','Trailer Multi-Use') ?></td>
				<td colspan='2'><input type='checkbox' name='allow_multiple' <? if($row_edit['allow_multiple']) echo 'checked'?>></td>				
			</tr>
			<tr class='mrr_shader'>
				<td><label for='rental_flag'><b>Rental</b></label> <?= show_help('admin_trailers.php','Rental') ?></td>
				<td colspan='2'><input type='checkbox' name='rental_flag' id='rental_flag' <? if($row_edit['rental_flag']) echo "checked"?>></td>
			</tr>
			<tr>
				<td><label for='no_insurance'><b>No Insurance</b></label> <?= show_help('admin_trailers.php','No Insurance') ?></td>
				<td colspan='2'><input type='checkbox' name='no_insurance' id='no_insurance' <? if($row_edit['no_insurance']) echo "checked"?>></td>
			</tr>
			<tr>
				<td><label for='in_the_shop'><b>Trailer In Shop</b></label> <?= show_help('admin_trailers.php','In The Shop') ?></td>
				<td colspan='2'><input type='checkbox' name='in_the_shop' id='in_the_shop' <? if($row_edit['in_the_shop']) echo 'checked'?>></td>
			</tr>
			<tr>
				<td><label for='fubar_trailer'><b>FUBAR</b></label> <?= show_help('admin_trailers.php','fubar_trailer') ?></td>
				<td colspan='2'><input type='checkbox' name='fubar_trailer' id='fubar_trailer' <? if($row_edit['fubar_trailer']) echo 'checked'?>></td>
			</tr>	
			
			<tr class='mrr_shader'>
				<td valign='top'><label for='pending_repairs'><b>Trailer Needs Repairs</b></label> <?= show_help('admin_trailers.php','pending_repairs') ?></td>
				<td valign='top' colspan='2'>
					<input type='checkbox' name='pending_repairs' id='pending_repairs' <? if($row_edit['pending_repairs']) echo 'checked'?>>
					<i>Enter Repairs Needed below</i><br>
					<textarea name="pending_repairs_list" id="pending_repairs_list" wrap='virtual' rows='5' cols='40'><?=$row_edit['pending_repairs_list'] ?></textarea>
					<div style='text-align:right; width:100%;'>					
     					
     					Opened:  <?= show_help('admin_trailers.php','pending_repairs opened') ?>
     					<input name="pending_repairs_date_opened" id='pending_repairs_date_opened' value="<? if(strtotime($row_edit['pending_repairs_date_opened']) != 0) echo date("m/d/Y", strtotime($row_edit['pending_repairs_date_opened'])) ?>" style='width:80px;' class='mrr_date_input'>
     					 (mm/dd/yyyy)
     					
     					<br>
     					Inspected:  <?= show_help('admin_trailers.php','pending_repairs inspected') ?>
     					<input name="pending_repairs_date_inspect" id='pending_repairs_date_inspect' value="<? if(strtotime($row_edit['pending_repairs_date_inspect']) != 0) echo date("m/d/Y", strtotime($row_edit['pending_repairs_date_inspect'])) ?>" style='width:80px;' class='mrr_date_input'>
     					 (mm/dd/yyyy)
     											
     					<br>
     					Repaired:  <?= show_help('admin_trailers.php','pending_repairs repaired') ?>
     					<input name="pending_repairs_date_repair" id='pending_repairs_date_repair' value="<? if(strtotime($row_edit['pending_repairs_date_repair']) != 0) echo date("m/d/Y", strtotime($row_edit['pending_repairs_date_repair'])) ?>" style='width:80px;' class='mrr_date_input'>
     					 (mm/dd/yyyy)
     												
     					<br>
     					Closed:  <?= show_help('admin_trailers.php','pending_repairs closed') ?>
     					<input name="pending_repairs_date_closed" id='pending_repairs_date_closed' value="<? if(strtotime($row_edit['pending_repairs_date_closed']) != 0) echo date("m/d/Y", strtotime($row_edit['pending_repairs_date_closed'])) ?>" style='width:80px;' class='mrr_date_input'>
     					 (mm/dd/yyyy)
     					
     					<br>
     					Repairs Made:
     					
     					<label for='pending_repairs_internal'><b>Internally</b></label> <?= show_help('admin_trailers.php','pending_repairs internal') ?>
     					<input type='checkbox' name='pending_repairs_internal' id='pending_repairs_internal' <? if($row_edit['pending_repairs_internal']) echo 'checked'?>>
     					
     					<label for='pending_repairs_made'><b>Completed</b></label> <?= show_help('admin_trailers.php','pending_repairs made') ?>
     					<input type='checkbox' name='pending_repairs_made' id='pending_repairs_made' <? if($row_edit['pending_repairs_made']) echo 'checked'?>>
					</div>
					
				</td>
			</tr>
					
			<!--
			<tr>
				<td><label for='dedicated_trailer'><b>Dedicated Trailer</b></label> <?= show_help('admin_trailers.php','Dedicated Trailer') ?></td>
				<td colspan='2'><input type='checkbox' name='dedicated_trailer' <? if($row_edit['dedicated_trailer']) echo 'checked'?>></td>
			</tr>
			-->
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>
			<? if($row_edit['maint_req_lockdown']) { ?>
     			<tr>
     				<td colspan='3' align='center'>
     					<span class='mrr_alert'>WARNING: THIS TRAILER IS ON MAINTENANCE LOCKDOWN.</span><?= show_help('admin_trailers.php','maint_req_lockdown') ?>
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
					<table style='border:1px #aaaaaa solid;width:100%'>
					<tr>
						<td colspan='5'>
							<b>History</b> - 
							<input type='button' value='Add new history' onclick='add_equipment_history(true)' class='mrr_button_access'> <?= show_help('admin_trailers.php','Add New History') ?>
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
									<input name='linedate_aquired_$row_history[id]' id='linedate_aquired_$row_history[id]' style='width:80px' class='datepicker' value='".date("m/d/Y", strtotime($row_history['linedate_aquired']))."'>
									<input type='hidden' name='equipment_history_array[]' value='$row_history[id]'>
								</td>
								<td><input name='linedate_returned_$row_history[id]' style='width:80px' class='datepicker linedate_returned' value='".($row_history['linedate_returned'] > 0 ? date("m/d/Y", strtotime($row_history['linedate_returned'])) : "")."'></td>
								<td align='right'><input name='equipment_value_$row_history[id]' value=\"$".money_format('',$row_history['equipment_value'])."\" style='width:80px;text-align:right'></td>
								<td align='right'><input name='miles_pickup_$row_history[id]' value=\"".number_format($row_history['miles_pickup'])."\" style='width:80px;text-align:right'></td>
								<td align='right'><input name='miles_dropoff_$row_history[id]' value=\"".number_format($row_history['miles_dropoff'])."\" style='width:80px;text-align:right'></td>
								<td><a href='javascript:void(0)' onclick='delete_equipment_history($row_history[id])' class='mrr_delete_access'><img src='images/delete_sm.gif' alt='Delete History' title='Delete History' style='border:0'></a></td>
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
							<input type='submit' value='Update History' class='mrr_button_access'>
							</center>
						</td>
					</tr>
					<tr>
						<td colspan='5'>
							<br>&nbsp;<br>
							<b>Depreciation History</b><br>
							<? 
							echo mrr_display_equipment_depreciation(0,$_GET['id']);	//truck_id,trialer_id
							?>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>
			<tr class='mrr_shader'>
				<td><b>Trailer Owner</b> <?= show_help('admin_trailers.php','Trailer Owner') ?></td>
				<td colspan='2'><input name="trailer_owner" id='trailer_owner' value="<?=$row_edit['trailer_owner']?>"></td>
			</tr>
			<tr>
				<td><b>Year</b> <?= show_help('admin_trailers.php','Year') ?></td>
				<td colspan='2'><input name="trailer_year" value="<?=$row_edit['trailer_year']?>"></td>
			</tr>
			<tr>
				<td><b>Make</b> <?= show_help('admin_trailers.php','Make') ?></td>
				<td colspan='2'><input name="trailer_make" value="<?=$row_edit['trailer_make']?>"></td>
			</tr>
			<tr>
				<td><b>Model</b> <?= show_help('admin_trailers.php','Model') ?></td>
				<td colspan='2'><input name="trailer_model" value="<?=$row_edit['trailer_model']?>"></td>
			</tr>
			<tr>
				<td><b>Tire Size</b> <?= show_help('admin_trailers.php','trailer_tire_size') ?></td>
				<td colspan='2'><input name="trailer_tire_size" value="<?=$row_edit['trailer_tire_size']?>"></td>
			</tr>
			<tr>
				<td><b>VIN</b> <?= show_help('admin_trailers.php','VIN') ?></td>
				<td colspan='2'><input name="vin" value="<?=$row_edit['vin']?>"></td>
			</tr>
			<tr>
				<td><b>License Plate #</b> <?= show_help('admin_trailers.php','License Plate') ?></td>
				<td colspan='2'><input name="license_plate_no" value="<?=$row_edit['license_plate_no']?>"></td>
			</tr>
			<tr>
				<td>
					<b>Current Registration PDF Filename</b> <?= show_help('admin_trailers.php','trailer_regist_file') ?>
					<br>Document Location: https://trucking.conardtransportation.com/documents/<br>
				</td>
				<td colspan='2'>
					<input name="trailer_regist_file" value="<?=$row_edit['trailer_regist_file']?>">
					<br>
					<?
					if(trim($row_edit['trailer_regist_file'])!="")
					{
						$pdf_file="https://trucking.conardtransportation.com/documents/".$row_edit['trailer_regist_file'];	
						echo "<a href='".$pdf_file."' target='_blank'>".trim($row_edit['trailer_regist_file'])."</a>";
					}
					?>
					<br>
				</td>
			</tr>
			<?
				$last_maint_inspect_id=mrr_find_last_inspection($_GET['id']);
				$pmi_pending=0;
				$fed_pending=0;
				if($last_maint_inspect_id > 0)
				{
					$insres=mrr_form_trailer_inspection_list($last_maint_inspect_id);
					/*
					$insres['used']=0;
                    	$insres['passed']=0;
                    	$insres['created_by']="";
                    	$insres['updated_by']="";
                    	$insres['updated']="";
                    	$insres['created']="";
                    	$insres['inspection']=0;
                    	$insres['used_pmi']=0;		
                    	$insres['used_fed']=0;
					*/
					if($insres['used'] > 0 && $insres['used_pmi'] > 0)	$pmi_pending=1;	//PMI inspection is still pending					
					if($insres['used'] > 0 && $insres['used_fed'] > 0)	$fed_pending=1;	//FED inspection is still pending					
				}
			?>
			<tr>
				<td>
					<b>PMI's</b> <?= show_help('admin_trailers.php','linedate_last_pmi') ?>  
					<label>Ignore <input type='checkbox' name='pmi_test_ignore' id='pmi_test_ignore' value='1' <? if($row_edit['pmi_test_ignore']) echo 'checked'?>></label>
					<?					
					if($days_counted > 0 && $row_edit['pmi_test_ignore'] ==0)
					{
						if($row_edit['linedate_last_pmi']=="0000-00-00 00:00:00")
						{
							echo " <a href='maint.php?id=0&e_type=2&e_id=".$row_edit['id']."&inspect=1&due=".date("Y-m-d",time())."' target='_blank'><span class='alert'><b>OVERDUE!</b></span></a>";
						}
						else
						{
							$now_dater=date("ymd",time());
							$next_run=date("m/d/Y", strtotime("+".$days_counted." days", strtotime($row_edit['linedate_last_pmi'])));	
							$due_compare=date("ymd",strtotime($next_run));
							if((int) $due_compare <= (int) $now_dater)
							{
								echo " <a href='maint.php?id=0&e_type=2&e_id=".$row_edit['id']."&inspect=1&due=".date("Y-m-d",strtotime($next_run))."' target='_blank'>
										<span class='alert'><b>OVERDUE".($pmi_pending > 0 ? "-Pending" : "")." ".$next_run."</b></span>
									</a>";
							}
							else
							{
								echo " <span style='color:green;'><b>Next PMI Due: ".$next_run."</b></span>";
							}
						}
					}
					if($row_edit['pmi_test_ignore'] > 0)	echo " <span style='color:green;'><b>N/A</b></span>";
					?>					
				</td>
				<td colspan='2'><input name="linedate_last_pmi" id='linedate_last_pmi' value="<?=($row_edit['linedate_last_pmi']!="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($row_edit['linedate_last_pmi'])) : "")?>"></td>
			</tr>
			<tr>
				<td>
					<b>FED</b> <?= show_help('admin_trailers.php','linedate_last_fed') ?>  
					<label>Ignore <input type='checkbox' name='fed_test_ignore' id='fed_test_ignore' value='1' <? if($row_edit['fed_test_ignore']) echo 'checked'?>></label>
					<?					
					if($feds_counted > 0 && $row_edit['fed_test_ignore'] ==0)
					{
						if($row_edit['linedate_last_fed']=="0000-00-00 00:00:00")
						{
							echo " <a href='maint.php?id=0&e_type=2&e_id=".$row_edit['id']."&inspect=2&due=".date("Y-m-d",time())."' target='_blank'><span class='alert'><b>OVERDUE!</b></span></a>";
						}
						else
						{
							$now_dater=date("ymd",time());
							$next_run=date("m/d/Y", strtotime("+".$feds_counted." days", strtotime($row_edit['linedate_last_fed'])));	
							$due_compare=date("ymd",strtotime($next_run));
							if((int) $due_compare <= (int) $now_dater)
							{
								echo " <a href='maint.php?id=0&e_type=2&e_id=".$row_edit['id']."&inspect=2&due=".date("Y-m-d",strtotime($next_run))."' target='_blank'>
										<span class='alert'><b>OVERDUE".($fed_pending > 0 ? "-Pending" : "")." ".$next_run."</b></span>
									</a>";
							}
							else
							{
								echo " <span style='color:green;'><b>Next FED Due: ".$next_run."</b></span>";
							}
						}
					}
					if($row_edit['fed_test_ignore'] > 0)	echo " <span style='color:green;'><b>N/A</b></span>";
					?>					
				</td>
				<td colspan='2'><input name="linedate_last_fed" id='linedate_last_fed' value="<?=($row_edit['linedate_last_fed']!="0000-00-00 00:00:00" ? date("m/d/Y",strtotime($row_edit['linedate_last_fed'])) : "")?>"></td>
			</tr>
			
			<tr>
				<td><label for='pn_tracking_has'><b>Has Tracking</b></label> <?= show_help('admin_trailers.php','pn_tracking_has') ?></td>
				<td colspan='2'><input type='checkbox' name='pn_tracking_has' id='pn_tracking_has' <? if($row_edit['pn_tracking_has']) echo 'checked'?>></td>
			</tr>
			<tr>
				<td><b>Tracking Unit #</b> <?= show_help('admin_trailers.php','pn_tracking_num') ?></td>
				<td colspan='2'><input name="pn_tracking_num" value="<?=$row_edit['pn_tracking_num']?>"></td>
			</tr>
			<tr>
				<td><b>Tracking Unit Cost</b> <?= show_help('admin_trailers.php','pn_tracking_val') ?></td>
				<td>$ <input name="pn_tracking_val" value="<?=$row_edit['pn_tracking_val']?>" size='10' style='text-align:right;'></td>
			</tr>
			<tr>
				<td><b>GeoTab Trailer ID</b> <?= show_help('admin_trailers.php','geotab_trailer_id') ?></td>
				<td colspan='2'><input name="geotab_trailer_id" value="<?=trim($row_edit['geotab_trailer_id'])?>"></td>
			</tr>
            <tr>
                <td valign='top'>
                    <b>Trailer Photo</b> <?= show_help('admin_trailers.php','trailer_photo') ?>
                    <br> (.gif, .jpg, or .png)
                </td>
                <td colspan='2' valign='top'>
                    <input name="trailer_photo"  id="trailer_photo" value="<?=$row_edit['trailer_photo']?>">
                    <br>
                    <input type="file" name='mrr_import' id='mrr_import' style='width:200px;'>
                     <?
                     $mrr_trailer_image=trim($row_edit['trailer_photo']);
                     $image_rotation=$row_edit['image_rotation'];
                     ?>
                </td>
            </tr>
            <tr>
                <td valign='top'>
                    <b>Rotation</b> <?= show_help('admin_trailers.php','image_rotation') ?>
                </td>
                <td colspan='2' valign='top'>
                    <input name="image_rotation"  id="image_rotation" value="<?=$row_edit['image_rotation']?>" style='width:50px; text-align:right;'> Degrees (90,180,270,360)
                </td>
            </tr>
            <tr>
                <td colspan='3' valign='top'>
                     <?
                     if(trim($mrr_trailer_image)!="")
                     {
                          $sized = getimagesize("trailers/".trim($mrr_trailer_image)."");
                          
                          $iwide=(int) str_replace(",","",$sized[0]);
                          $ihigh=(int) str_replace(",","",$sized[1]);
                          $max_wide=500;
                          
                          if($iwide > $max_wide)
                          {
                               $ratio=$iwide/$max_wide;				// 1536 / 300 = 5.12
                               $iwide=$max_wide;
                               $ihigh= round($ihigh / $ratio);  		// 2048 / 5.12 = 400
                          }
                          echo "
                                <br>
                                <br>
                                <a href='/trailers/".trim($mrr_trailer_image)."' target='_blank'>
                                    <img src='/trailers/".trim($mrr_trailer_image)."' border='1' width='".$iwide."' alt='".trim($mrr_trailer_image)." not found...".$iwide." x ".$ihigh."'".
                               ($row_edit['image_rotation'] > 0 ? " style='-ms-transform: rotate(".$row_edit['image_rotation']."deg); -webkit-transform: rotate(".$row_edit['image_rotation']."deg); transform: rotate(".$row_edit['image_rotation']."deg);'" : "")
                               .">
                                </a>";	// height='".$ihigh."'
                     }
                     ?>
                </td>
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
				<td colspan='3'><?= ($_GET['id'] > 0 ? mrr_get_insurance_email_log(0,0,0,$_GET['id'],"") : "")?></td>
			</tr>
			</table>
			</form>
             
             <? if($_GET['id'] > 0) { ?>
                <div class='clear'></div>
                <br>
                <div id='internal_tasks' style='margin-bottom:10px;'>
                    <table class='admin_menu1' style='width:100%;margin-bottom:10px'>
                        <tr>
                            <td colspan='5' class='border_bottom'><div class='section_heading'>Internal Tasks</div></td>
                        </tr>
                        <tr>
                            <td valign='top'><b>Task</b></td>
                            <td valign='top'><b>Unit</b></td>
                            <td valign='top'><b>Due Date</b></td>
                            <td valign='top'><b>Checked by</b></td>
                            <td valign='top'><b>Completed</b></td>
                        </tr>
                         <?php
                         $task_cntr=0;
                         $sql_tasks = "
                                select internal_tasks_checked.*,
                                    internal_tasks.task_name,
                                    internal_tasks.task_type,
                                    (select name_truck from trucks where trucks.id=internal_tasks_checked.entity_id) as truck_name,
                                    (select trailer_name from trailers where trailers.id=internal_tasks_checked.entity_id) as trailer_name,
                                    (select CONCAT(name_driver_first,' ',name_driver_last) from drivers where drivers.id=internal_tasks_checked.entity_id) as driver_name,
                                    users.username	
                                from internal_tasks_checked
                                    left join internal_tasks on internal_tasks.id=internal_tasks_checked.task_id
                                    left join users on users.id=internal_tasks_checked.user_id
                                where internal_tasks.deleted <= 0 and internal_tasks_checked.deleted<=0
                                    and (internal_tasks.active > 0 || internal_tasks_checked.user_id > 0)
                                    and internal_tasks.task_type = 2
                                    and internal_tasks_checked.entity_id='".(int) $_GET['id']."'
                                    and internal_tasks.linedate_start <= NOW()
                                order by internal_tasks.task_name asc,internal_tasks_checked.id asc
                             ";
                         $data_tasks = mysqli_query($datasource, $sql_tasks);
                         while($row_tasks = mysqli_fetch_array($data_tasks))
                         {
                              $cur_dater="<input name='cur_date_task' id='cur_date_task' value='".date("m/d/Y",strtotime($row_tasks['cur_date']))."' onBlur='mrr_set_task_date(".$row_tasks['id'].");' size='15' class='datepicker' placeholder='mm/dd/YYYY'>";
                              $done_by="";
                              $done_on="";
                              if($row_tasks['user_id'] > 0)
                              {
                                   $done_by=trim($row_tasks['username']);
                                   $done_on="".date("m/d/Y",strtotime($row_tasks['done_date']))."";
                                   $cur_dater="".date("m/d/Y",strtotime($row_tasks['cur_date']))."";
                              }
                              $entity_name="<b>N/A</b>";
                              //if($row_tasks['task_type']==1)  $entity_name="<a href='admin_trucks.php?id=".$row_tasks['entity_id']."' target='blank'>".trim($row_tasks['truck_name'])."</a>";
                              if($row_tasks['task_type']==2)    $entity_name="<i>".trim($row_tasks['trailer_name'])."</i>";
                              //if($row_tasks['task_type']==3)  $entity_name="<a href='admin_drivers.php?id=".$row_tasks['entity_id']."' target='blank'>".trim($row_tasks['driver_name'])."</a>";
                              
                              echo "
                                        <tr style='background-color:".($task_cntr%2==0 ? "eeeeee" : "dddddd").";'>
                                            <td valign='top'>".$row_tasks['task_name']."</td>
                                            <td valign='top'>".$entity_name."</td>
                                            <td valign='top'>".$cur_dater."</td>                                        
                                            <td valign='top'><i>".$done_by."</i></td>
                                            <td valign='top'><i>".$done_on."</i></td>                                     
                                        </tr>
                                 ";     //              mrr_set_internal_task
                              $task_cntr++;
                         }
                         ?>
                    </table>
                </div>
             <? } ?>


            <div class='clear'></div>
			<br>
			<div id='maint_request_section' style='margin-bottom:10px;'><?= $trailer_maint_requests ?></div>
			<div class='clear'></div>
			<div id='note_section'></div>
			<div class='clear'></div>
			
			<? if($use_new_uploader > 0) { ?>
     		
     			<br>&nbsp;<br>
          		<iframe src="mrr_uploader_hack.php?section_id=2&id=<?=$_GET['id']?>" width='600' height='80' border='0' style='border:#000000 solid 0px; background-color:#ffffff;'>
          		</iframe> 
          		<div id='attachment_holder'></div>
     		
     		<? } else { ?>
     		
     			<div id='upload_section'></div>
     		
     		<? } ?>
			
			<div class='change_log'>
				<?= ($_GET['id'] > 0 ? mrr_get_user_change_log(" and user_change_log.trailer_id='".sql_friendly($_GET['id'])."'"," order by user_change_log.linedate_added asc","",1) : "") ?>
			</div>
		<? } ?>
	</td>
</tr>
</table>

<script type='text/javascript'>
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete this trailer?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}

    function mrr_set_task_date(id)
    {
        var task_date=$('#cur_date_task').val();

        task_date=task_date+"";

        $.ajax({
            type: "POST",
            url: "ajax.php?cmd=mrr_set_internal_task",
            data: {"id":id , "date": task_date },
            dataType: "xml",
            cache:false,
            success: function(xml) {
                $.noticeAdd({text: "Internal Task date ("+task_date+") has been updated successfully."});
            }
        });
    }
	
<?
if($defaultsarray['sicap_integration'] == 1)
{		
?>		
	$('#trailer_name').change(function() {			
		mrr_test_type_name();					
	});
	
	function mrr_test_type_name()
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_test_name_type",
		   data: {"equipment_name": $('#trailer_name').val()},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		$(xml).find('Namer').each(function() {
     				var test_result=$(this).find('NamerResult').text();
     				if(test_result==0)
					{				
						$.prompt("Warning: The name for this trailer is valid here, but it will not display in accounting application. Use numeric digits only to use with accounting system.");
					}     				
		   		});
		   }
		 });
	}
<?
}
?>
	function mrr_run_maint_request_report()
	{
		$.ajax({
		   type: "POST",
		   url: "report_maint_requests.php?current=1",
		   data: {"current": 1},
		   dataType: "HTML",
		   
		   cache:false,
		   success: function (data) {
		   		//WindowObjectReference = window.open("admin_trailers.php?pmi_fed=1", "PMI_FED_report", 'width=850,height=550,location=no,resizable=yes,menubar=no,status=yes,toolbar=no,scrollbars=yes');
				//WindowObjectReference.focus();
				window.location = "admin_trailers.php?pmi_fed=1";
		   }
		 });	
	}
	
	
	function mrr_eval_rental()
	{		
		if( $('#company_owned').attr('checked') )
		{			
			$('.mrr_shader').attr('style','background-color:#dddddd;');
			$('#rental_flag').attr('style','background-color:#dddddd;');
			//$('#trailer_owner').val('');
			$('#trailer_owner').attr('style','background-color:#dddddd;');
			$('#interchange_flag').attr('style','background-color:#dddddd;');
		}
		else
		{			
			$('.mrr_shader').attr('style','');
			$('#rental_flag').attr('style','');
			//$('#trailer_owner').val('');
			$('#trailer_owner').attr('style','');
			$('#interchange_flag').attr('style','');	
		}		
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
	
	$('#linedate_aquired').datepicker();
	$('#linedate_returned').datepicker();
	$('.datepicker').datepicker();
	$('#linedate_last_pmi').datepicker();	
	$('#linedate_last_fed').datepicker();
	$('.mrr_date_input').datepicker();
	
	<? 
		if(isset($_GET['id']) && $_GET['id'] > 0) 
		{
			//echo " create_upload_section('#upload_section', 2, $_GET[id]); "; 
			if($use_new_uploader > 0) 
			{ 
				echo " create_upload_section_alt('#upload_section', 2, $_GET[id]); "; 
			}
			else
			{
				echo " create_upload_section('#upload_section', 2, $_GET[id]); "; 
			}
			
			
			echo " create_note_section('#note_section', 2, $_GET[id]); "; 
			echo " mrr_eval_rental(); ";
		}
	?>
	
	function mrr_verify_user_unique(myid)
	{		
		$('#mrr_naming_message').html('');	
		mrr_lab="Trailer";
		mrr_lab2="trailer_name";
		mrr_code=4;
		
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
	
	function duplicate_trailer(id) 
	{
		$.prompt("Are you sure you want to <span class='alert'>duplicate</span> this Trailer?", {
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
