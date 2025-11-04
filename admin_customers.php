<? include('application.php') ?>
<? $admin_page = 1 ?>
<?
	$use_new_uploader=1;
	if(!isset($_GET['sid'])) $_GET['sid'] = 0;
	
	if(isset($_GET['id']) && !isset($_GET['eid']))		$_GET['eid']=$_GET['id'];
	
	if(isset($_GET['duplicate'])) 
	{	//Added Nov 2015...MRR
		$dup_id = duplicate_row('customers', $_GET['duplicate']);
				
		$sql = "
			update customers set
				name_company= CONCAT('DUP-',name_company),
				linedate_customer_since = NOW(),
				linedate_added = NOW(),
				customer_login_name='',
				customer_login_pass='',				
				payment_notes='',
				stoplight_warn_notes='',
				slow_pays='0',
				credit_hold='0',	
				mc_num='0',
				dirt_bags_flag='0',			
				override_slow_pays='0',
				document_75k_received='0',
				document_75k_exempt='0',
				linedate_document_75k= '0000-00-00 00:00:00',
				linedate_expires_75k= '0000-00-00 00:00:00',
				linedate_renewal_75k='0000-00-00 00:00:00',
				cust_credit_check='0',
				linedate_credit_check='0000-00-00 00:00:00',
				linedate_credit_check_exp='0000-00-00 00:00:00',
				deleted='0'
				
			where id = '".sql_friendly($dup_id)."'
		";
		simple_query($sql);
		
		
		//copy contacts' info
		$sql = "
			show columns from customer_contacts
		";
		$data_fields = simple_query($sql);
		$row_fields = mysqli_fetch_array($data_fields);
		$field_array = array();
		while($row_fields = mysqli_fetch_array($data_fields)) {
			//echo $row_fields['Field']."<br>";
			if($row_fields['Field'] != 'id' && $row_fields['Field'] != 'customer_id' && $row_fields['Field'] != 'linedate_added') $field_array[] = $row_fields['Field'];
		}
		$field_list = implode(",", $field_array);
		$sql = "
			insert into customer_contacts ($field_list, linedate_added, customer_id)
			select $field_list, NOW(), $dup_id as customer_id from customer_contacts i where i.customer_id = '".sql_friendly($_GET['duplicate'])."'
				
		";
		simple_query($sql);
		
		
		header("Location: admin_customers.php?eid=".$dup_id);
		die;
	}
	
	if(isset($_GET['delsurcharge'])) {
		$sql = "
			delete from fuel_surcharge
			where id = '".sql_friendly($_GET['delsurcharge'])."'
		";
		$data_delete = simple_query($sql);
	}

	if(isset($_GET['newsurcharge'])) {
		$sql = "
			insert into fuel_surcharge
				(customer_id,
				range_lower,
				fuel_surcharge)
			
			values (".sql_friendly($_GET['eid']).",
				0,
				0)
		";
		$data_insert = simple_query($sql);
		javascript_redirect($SCRIPT_NAME."?eid=".$_GET['eid']."&sid=".mysqli_insert_id($datasource));
	}

	if(isset($_POST['range_lower'])) {
		$sql = "
			update fuel_surcharge
			
			set range_lower = '".sql_friendly($_POST['range_lower'])."',
				fuel_surcharge = '".sql_friendly($_POST['fuel_surcharge'])."'

			where id = '".sql_friendly($_GET['sid'])."'			
		";
		$data_update = simple_query($sql);
		$mrr_activity_log_notes.="Updated fuel surcharge info ".$_POST['range_lower']." range lower at ".$_POST['fuel_surcharge'].". ";	
	}
	
	if(isset($_GET['delcontact'])) {
		$sql = "
			update customer_contacts
			set deleted = 1
			
			where id = '".sql_friendly($_GET['delcontact'])."'
		";
		$data = simple_query($sql);	
		$mrr_activity_log_notes.="Deleted contact info ".$_GET['delcontact'].". ";	
	}
	
	if(isset($_GET['delid'])) {
		$sql = "
			update customers
			set deleted = 1
			
			where id = '".sql_friendly($_GET['delid'])."'
		";
		$data = simple_query($sql);		
		$mrr_activity_log_notes.="Deleted customer ".$_GET['did'].". ";
	}

	/*
	if(isset($_GET['new'])) {
				
		//give until the end of the current day to change he name...so as not to block others adding customers today...  Added Jan 2014.
		$sql="update customers set deleted=1 where name_company='New Customer' and linedate_added<'".date("Y-m-d")." 00:00:00'";  
		simple_query($sql);
		
		$sql = "
			insert into customers			
				(name_company,
				payment_notes,
				hot_load_radius_arriving,
				hot_load_radius_arrived,
				hot_load_radius_departed)							
			values 
				('New Customer',
				'',
				'".sql_friendly($defaultsarray['peoplenet_geofencing_arriving'])."',
				'".sql_friendly($defaultsarray['peoplenet_geofencing_arrived'])."',
				'".sql_friendly($defaultsarray['peoplenet_geofencing_departed'])."')
		";
		$data = simple_query($sql);
		header("Location: $SCRIPT_NAME?eid=".mysql_insert_id());
		die();
	}
	*/
	
	if(isset($_GET['new'])) {
		$_GET['eid']=0;
	}
	
	if(isset($_POST['name_company'])) {
		if(isset($_POST['separate_truck_section'])) {
			$separate_truck_section = 1;
		} else {
			$separate_truck_section = 0;
		}
		
		$_POST['fuel_surcharge']=str_replace("$","",$_POST['fuel_surcharge']);
		
		$_POST['flat_fuel_surchage_mon']=str_replace("$","",$_POST['flat_fuel_surchage_mon']);
		$_POST['flat_fuel_surchage_tue']=str_replace("$","",$_POST['flat_fuel_surchage_tue']);
		$_POST['flat_fuel_surchage_wed']=str_replace("$","",$_POST['flat_fuel_surchage_wed']);
		$_POST['flat_fuel_surchage_thu']=str_replace("$","",$_POST['flat_fuel_surchage_thu']);
		$_POST['flat_fuel_surchage_fri']=str_replace("$","",$_POST['flat_fuel_surchage_fri']);
		$_POST['flat_fuel_surchage_sat']=str_replace("$","",$_POST['flat_fuel_surchage_sat']);
		$_POST['flat_fuel_surchage_sun']=str_replace("$","",$_POST['flat_fuel_surchage_sun']);
				
		if(trim($_POST['linedate_document_75k'])=="")	    $_POST['linedate_document_75k']="00/00/0000";
		if(trim($_POST['linedate_expires_75k'])=="")		$_POST['linedate_expires_75k']="00/00/0000";
         
        if(trim($_POST['linedate_renewal_75k'])=="")		$_POST['linedate_renewal_75k']="00/00/0000";
        if(trim($_POST['linedate_credit_check'])=="")		$_POST['linedate_credit_check']="00/00/0000";
        if(trim($_POST['linedate_credit_check_exp'])=="")	$_POST['linedate_credit_check_exp']="00/00/0000";
         
         if($_GET['eid'] == 0)
		{	//add new truck if EID=0
			
			//give until the end of the current day to change he name...so as not to block others adding customers today...  Added Jan 2014.
			$sql="update customers set deleted=1 where name_company='New Customer' and linedate_added<'".date("Y-m-d")." 00:00:00'";  
			simple_query($sql);
		
     		$sql = "
     			insert into customers			
     				(name_company,
     				payment_notes,
     				geofencing_radius_active,
     				geofencing_hot_msg_all_loads,
     				hot_load_radius_arriving,
     				hot_load_radius_arrived,
     				hot_load_radius_departed,
     				customer_login_name,
     				customer_login_pass)							
     			values 
     				('New Customer',
     				'',
     				'1',
     				'1',
     				'".sql_friendly($defaultsarray['peoplenet_geofencing_arriving'])."',
     				'".sql_friendly($defaultsarray['peoplenet_geofencing_arrived'])."',
     				'".sql_friendly($defaultsarray['peoplenet_geofencing_departed'])."',
     				'',
     				'')
     		";
     		$data = simple_query($sql);		
			
			$_GET['eid']=mysqli_insert_id($datasource);	
		}
		
		$old_credit_holder=0;
		$old_dirt_bags_flag=0;
		$sql = "
			select override_credit_hold,dirt_bags_flag,id		
			from customers
			where id = '".sql_friendly($_GET['eid'])."'
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$old_credit_holder=(int) $row['id'];	
			$old_dirt_bags_flag=(int) $row['dirt_bags_flag'];
		}
		
		/*
		geofencing_radius_active=1,
		geofencing_hot_msg_all_loads=1,
		hot_load_radius_arriving=15840,
		hot_load_radius_arrived=12960,
		hot_load_radius_departed=26400
		*/
		if(!is_numeric($_POST['hot_load_timer']))		$_POST['hot_load_timer']="0.00";
		$sql = "
			update customers set
			
				name_company = '".sql_friendly($_POST['name_company'])."',
				fuel_surcharge = '".sql_friendly($_POST['fuel_surcharge'])."',
				contact_primary = '".sql_friendly($_POST['contact_primary'])."',
				separate_truck_section = '$separate_truck_section',
				contact_email = '".sql_friendly($_POST['contact_email'])."',
				address1 = '".sql_friendly($_POST['address1'])."',
				address2 = '".sql_friendly($_POST['address2'])."',
				city = '".sql_friendly($_POST['city'])."',
				state = '".sql_friendly($_POST['state'])."',
				zip = '".sql_friendly($_POST['zip'])."',
				billing_address1 = '".sql_friendly($_POST['billing_address1'])."',
				billing_address2 = '".sql_friendly($_POST['billing_address2'])."',
				billing_city = '".sql_friendly($_POST['billing_city'])."',
				billing_state = '".sql_friendly($_POST['billing_state'])."',
				billing_zip = '".sql_friendly($_POST['billing_zip'])."',
				phone_work = '".sql_friendly($_POST['phone_work'])."',
				fax = '".sql_friendly($_POST['fax'])."',
				active = '".(isset($_POST['active']) ? '1' : '0')."',
				slow_pays = '".(isset($_POST['slow_pays']) ? '1' : '0')."',
				dirt_bags_flag = '".(isset($_POST['dirt_bags_flag']) ? '1' : '0')."',
				email_invoice = '".(isset($_POST['email_invoice']) ? '1' : '0')."',
				credit_hold = '".(isset($_POST['credit_hold']) ? '1' : '0')."',
				
				use_fuel_surcharge = '".(isset($_POST['use_fuel_surcharge']) ? '1' : '0')."',
				override_credit_hold = '".(isset($_POST['override_credit_hold']) ? '1' : '0')."',
				override_slow_pays = '".(isset($_POST['override_slow_pays']) ? '1' : '0')."',
				hot_load_switch='".(isset($_POST['hot_load_switch']) ? '1' : '0')."',
				
				mc_num = '".sql_friendly((int) trim($_POST['mc_num']))."',
				
				send_manual_depart_notice='".(isset($_POST['send_manual_depart_notice']) ? '1' : '0')."',
				
				edi_use_214_files='".(isset($_POST['edi_use_214_files']) ? '1' : '0')."',
				
				use_fuel_surcharge_auto = '".(isset($_POST['use_fuel_surcharge_auto']) ? '1' : '0')."',
				
				hot_load_timer='".sql_friendly( $_POST['hot_load_timer'])."',
				hot_load_email_arriving='".sql_friendly($_POST['hot_load_email_arriving'])."',
				hot_load_email_arrived='".sql_friendly($_POST['hot_load_email_arrived'])."',
				hot_load_email_departed='".sql_friendly($_POST['hot_load_email_departed'])."',
				hot_load_radius_arriving='".sql_friendly( (int) $_POST['hot_load_radius_arriving'])."',
				hot_load_radius_arrived='".sql_friendly( (int) $_POST['hot_load_radius_arrived'])."',
				hot_load_radius_departed='".sql_friendly( (int) $_POST['hot_load_radius_departed'])."',
				hot_load_email_msg_arriving='".sql_friendly( $_POST['hot_load_email_msg_arriving'])."',
				hot_load_email_msg_arrived='".sql_friendly( $_POST['hot_load_email_msg_arrived'])."',
				hot_load_email_msg_departed='".sql_friendly( $_POST['hot_load_email_msg_departed'])."',
				hot_load_email_msg_arriving_shipper='".sql_friendly( $_POST['hot_load_email_msg_arriving_shipper'])."',
				hot_load_email_msg_arrived_shipper='".sql_friendly( $_POST['hot_load_email_msg_arrived_shipper'])."',
				hot_load_email_msg_departed_shipper='".sql_friendly( $_POST['hot_load_email_msg_departed_shipper'])."',
				geofencing_radius_active='".(isset($_POST['geofencing_radius_active']) ? '1' : '0')."',
				geofencing_hot_msg_all_loads='".(isset($_POST['geofencing_hot_msg_all_loads']) ? '1' : '0')."',
				flat_fuel_surchage_override='".(isset($_POST['flat_fuel_surchage_override']) ? '1' : '0')."',	
							
				document_75k_received='".(isset($_POST['document_75k_received']) ? '1' : '0')."',
				document_75k_exempt='".(isset($_POST['document_75k_exempt']) ? '1' : '0')."',
				linedate_document_75k='".date("Y-m-d", strtotime($_POST['linedate_document_75k']))."',	
				linedate_expires_75k='".date("Y-m-d", strtotime($_POST['linedate_expires_75k']))."',	
				
				linedate_renewal_75k='".date("Y-m-d", strtotime($_POST['linedate_renewal_75k']))."',
				cust_credit_check='".(isset($_POST['cust_credit_check']) ? '1' : '0')."',
				linedate_credit_check='".date("Y-m-d", strtotime($_POST['linedate_credit_check']))."',
				linedate_credit_check_exp='".date("Y-m-d", strtotime($_POST['linedate_credit_check_exp']))."',
									
				flat_fuel_surchage_mon='".sql_friendly( $_POST['flat_fuel_surchage_mon'])."',
				flat_fuel_surchage_tue='".sql_friendly( $_POST['flat_fuel_surchage_tue'])."',
				flat_fuel_surchage_wed='".sql_friendly( $_POST['flat_fuel_surchage_wed'])."',
				flat_fuel_surchage_thu='".sql_friendly( $_POST['flat_fuel_surchage_thu'])."',
				flat_fuel_surchage_fri='".sql_friendly( $_POST['flat_fuel_surchage_fri'])."',
				flat_fuel_surchage_sat='".sql_friendly( $_POST['flat_fuel_surchage_sat'])."',
				flat_fuel_surchage_sun='".sql_friendly( $_POST['flat_fuel_surchage_sun'])."',
				customer_login_name='".sql_friendly(str_replace(" ","_",$_POST['customer_login_name']))."',
     			customer_login_pass='".sql_friendly(str_replace(" ","_",$_POST['customer_login_pass']))."',
				invoice_discount_percent='".sql_friendly( $_POST['invoice_discount_percent'])."'

			where id = '".sql_friendly((int) $_GET['eid'])."'
		";
		$data = simple_query($sql);
		
		//payment_notes_history
        if(trim($_POST['payment_notes'])!="") 
        {   //only update the notes if they are not blank...
             $sqli = "
                insert into payment_notes_history
                    (id,customer_id,linedate_added,user_id,payment_note,deleted)
                values 
                    (NULL,'".sql_friendly($_GET['eid'])."',NOW(),'".sql_friendly($_SESSION['user_id'])."','".sql_friendly($_POST['payment_notes'])."',0)
             ";
             simple_query($sqli);
     
             $sqlu="update customers set payment_notes = '".sql_friendly($_POST['payment_notes'])."' where id = '".sql_friendly((int) $_GET['eid'])."'";     
             simple_query($sqlu);
        }
		
		$mrr_activity_log_notes.="Updated customers ".$_GET['eid']." info. ";	
		
		mrr_add_user_change_log($_SESSION['user_id'],$_GET['eid'],0,0,0,0,0,0,"Updated customer ".$_GET['eid']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		
		if(isset($_POST['geofencing_hot_msg_all_loads']))
		{
			mrr_update_active_loads_for_this_customer($_GET['eid'],1);
		}
		
		
		if($defaultsarray['sicap_integration'] == 1) sicap_update_customers($_GET['eid']);
		
		
		if((isset($_POST['override_credit_hold']) && $old_credit_holder==0) || (!isset($_POST['override_credit_hold']) && $old_credit_holder==1))
		{
			$email_from="customer.alerts@conardtransportation.com";			
	
			$email_from_name="Customer Alerts";	
			
			$subject="Credit Hold Override ".((!isset($_POST['override_credit_hold']) && $old_credit_holder==1) ? "removed" : " SET").".";
			$msg="
				NOTICE: Credit Hold Override for <a href='https://trucking.conardtransportation.com/admin_customers.php?eid=".(int) $_GET['eid']."'>".trim($_POST['name_company'])."</a> 
				has been ".((!isset($_POST['override_credit_hold']) && $old_credit_holder==1) ? "removed" : "<b>SET</b>")." 
				by User ID <a href='https://trucking.conardtransportation.com/admin_users.php?eid=".$_SESSION['user_id']."'>".$_SESSION['user_id']."</a>.
			";
			
			mrr_trucking_sendMail("jgriffith@conardtransportation.com","James Griffith",$email_from,$email_from_name,"","",$subject,$msg,$msg);
			
			mrr_trucking_sendMail("phamm@conardtransportation.com","Patti Hamm",$email_from,$email_from_name,"","",$subject,$msg,$msg);
			
			mrr_trucking_sendMail($defaultsarray['special_email_monitor'],"Lord Vader",$email_from,$email_from_name,"","",$subject,$msg,$msg);
		}
		if((isset($_POST['dirt_bags_flag']) && $old_dirt_bags_flag==0) || (!isset($_POST['dirt_bags_flag']) && $old_dirt_bags_flag==1))
		{
			$email_from="customer.alerts@conardtransportation.com";			
	
			$email_from_name="Customer Alerts";	
			
			$subject="Dirt Bags Flag ".((!isset($_POST['dirt_bags_flag']) && $old_dirt_bags_flag==1) ? "REMOVED" : " set").".";
			$msg="
				NOTICE: The DIRT BAGS flag for <a href='https://trucking.conardtransportation.com/admin_customers.php?eid=".(int) $_GET['eid']."'>".trim($_POST['name_company'])."</a> 
				has been ".((!isset($_POST['dirt_bags_flag']) && $old_dirt_bags_flag==1) ? "<b>REMOVED</b>" : "set")." 
				by User ID <a href='https://trucking.conardtransportation.com/admin_users.php?eid=".$_SESSION['user_id']."'>".$_SESSION['user_id']."</a>.
			";
			
			mrr_trucking_sendMail("jgriffith@conardtransportation.com","James Griffith",$email_from,$email_from_name,"","",$subject,$msg,$msg);
			
			mrr_trucking_sendMail("phamm@conardtransportation.com","Patti Hamm",$email_from,$email_from_name,"","",$subject,$msg,$msg);
			
			mrr_trucking_sendMail($defaultsarray['special_email_monitor'],"Lord Vader",$email_from,$email_from_name,"","",$subject,$msg,$msg);
		}
		
		header("Location: $SCRIPT_NAME?id=".$_GET['eid']);
		die();
	}
	
	
	if(!isset($_POST['filter_active']))     $_POST['filter_active']=1;	
	
	$sql_extra = "";
	if(!isset($_POST['sbox'])) 
	{
		$_POST['sbox'] = "";
		//$sql_extra = "and customers.active > 0";
	} 
	else 
	{		
		if(is_numeric(trim($_POST['sbox'])))
		{
			$sql_extra = "
				and (mc_num = '".sql_friendly((int) trim($_POST['sbox']))."')
			";
		}
		else
		{
			$sql_extra = "
				and (name_company like '%".sql_friendly($_POST['sbox'])."%')
			";
		}
	}
	
	
	$activator="";
    if($_POST['filter_active'] ==0)  $activator="and customers.active <= 0";
    if($_POST['filter_active'] ==1)  $activator="and customers.active > 0";
	
	$sql = "
		select customers.*,
		    (
		        select load_handler.linedate_added 
		        from load_handler 
		        where customer_id=customers.id 
		            and load_handler.deleted=0
		        order by load_handler.linedate_added desc 
		        limit 1
		    ) as last_load_date
		from customers
		where customers.deleted = 0			
			".$sql_extra."
			".$activator."
		order by customers.active desc, customers.name_company asc
	";
	$data = simple_query($sql);
	
	if(mysqli_num_rows($data) == 1) 
	{
		$row = mysqli_fetch_array($data);
		header("Location: ?eid=".$row['id']);
		die;
	}
		
	$export_file = "CTS Customer ID".chr(9).
				"Company Name".chr(9).
				"MC No.".chr(9).
				"Contact".chr(9).
				"EMail".chr(9).
				"Work Phone".chr(9).
				"Phone 2".chr(9).
				"Fax".chr(9).
				"Credit Limit".chr(9).
				"Address 1".chr(9).
				"Address 2".chr(9).
				"City".chr(9).
				"State".chr(9).
				"Zip".chr(9).
				"Billing Address 1".chr(9).
				"Billing Address 2".chr(9).
				"Billing City".chr(9).
				"Billing State".chr(9).
				"Billing Zip".chr(9).
				"Website".chr(9);
	$export_file .= chr(13);
				
	while($row = mysqli_fetch_array($data)) 
	{
		$export_file .= $row['id'].chr(9);
		$export_file .= $row['name_company'].chr(9);
		$export_file .= $row['mc_num'].chr(9);
		$export_file .= $row['contact_primary'].chr(9);
		$export_file .= $row['contact_email'].chr(9);
		$export_file .= $row['phone_work'].chr(9);
		$export_file .= $row['phone2'].chr(9);
		$export_file .= $row['fax'].chr(9);
		$export_file .= $row['credit_limit'].chr(9);
		$export_file .= $row['address1'].chr(9);
		$export_file .= $row['address2'].chr(9);
		$export_file .= $row['city'].chr(9);
		$export_file .= $row['state'].chr(9);
		$export_file .= $row['zip'].chr(9);
		$export_file .= $row['billing_address1'].chr(9);
		$export_file .= $row['billing_address2'].chr(9);
		$export_file .= $row['billing_city'].chr(9);
		$export_file .= $row['billing_state'].chr(9);
		$export_file .= $row['billing_zip'].chr(9);
		$export_file .= $row['website'].chr(9);
		$export_file .= chr(13);
	}
	@mysqli_data_seek($data, 0);
	
	$uuid = createuuid();
	$excel_filename = "excel_$uuid.xls";
	
	$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
	fwrite($fp, $export_file); 
	fclose($fp);
	
	$mrr_activity_log_notes.="Viewed search of customers. ";
	$mrr_show_fuel_surcharge=0;
?>
<?
$usetitle = "Customers";
$use_title = "Customers";
?>
<? include('header.php') ?>

<table style='text-align:left'>
<tr>
	<td valign='top' colspan='3'>
		<table class='admin_menu1' style='width:100%'>
		<tr>
			<td colspan='2'>
				<form action="<?=$SCRIPT_NAME?>" method="post">
				<input name="sbox" value="<?=$_POST['sbox']?>" style='width:250px'>
                    
                <b>FILTER BY</b>
                 <?
                 $dcntr=0;
                 $active_bx="";
                 $active_bx.="<select name='filter_active' id='filter_active' onChange='submit();'>";
                 
                 $pre="";		if($_POST['filter_active']==0)		$pre=" selected";
                 $active_bx.="<option value='0'".$pre.">Inactive</option>";
                 
                 $pre="";		if($_POST['filter_active']==1)		$pre=" selected";
                 $active_bx.="<option value='1'".$pre.">Active</option>";

                 $pre="";		if($_POST['filter_active']==2)		$pre=" selected";
                 $active_bx.="<option value='2'".$pre.">ALL Customers</option>";
                 
                 $active_bx.="</select>";
                 echo $active_bx;
                 ?>
				<input type="submit" name='search_customers' value="Search">
				<br>
				<div class='toolbar_button' onclick="window.location='<?=$SCRIPT_NAME?>?new=1'">
					<div><img src='images/new.png'></div>
					<div>New</div>
				</div>
				<div class='toolbar_button' onclick="window.open('temp/<?=$excel_filename?>')">
					<div><img src='images/excel.png'></div>
					<div>Export</div>
				</div>
				<? if(isset($_GET['eid']) && $_GET['eid'] > 0 && 1==2) { ?>
					<div class='toolbar_button' onclick='duplicate_cust(<?=$_GET['eid'] ?>);'>
						<div><img src='images/copy.png'></div>
						<div>Duplicate</div>
					</div>
				<? } ?>
				</form>
			</td>
		</tr>
		<? if((isset($_POST['search_customers']) && trim($_POST['search_customers'])!="") || (!isset($_GET['eid']))) { ?>
            <tr>
                <td colspan='2'>
                    <table class='tablesorter' style='width:100%'>
                    <thead>
                    <tr>
                        <th><b>ID</b></th>
                        <th><b>Company</b></th>
                        <th><b>MC No.</b></th>
                        <th><b>Last Load</b></th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <? while($row = mysqli_fetch_array($data)) { ?>
                        <tr>
                            <td><?=$row['id']?></td>
                            <td><a href="<?=$SCRIPT_NAME?>?eid=<?=$row['id']?>" class='<?=($row['active'] ? '' : 'inactive')?>'><?=$row['name_company']?></a></td>
                            <td><a href="<?=$SCRIPT_NAME?>?eid=<?=$row['id']?>" class='<?=($row['active'] ? '' : 'inactive')?>'><?=$row['mc_num']?></a></td>
                            <td><?=(isset($row['last_load_date']) ? date("m/d/Y H:i:s",strtotime($row['last_load_date'])) : "N/A")?></td>
                            <td>
                                <a href="javascript:confirm_del(<?=$row['id']?>)"><img src='images/delete_sm.gif' border='0'></a>
                            </td>
                        </tr>
                    <? } ?>
                    </tbody>    
                    </table>    
                </td>
            </tr>            
		<? } ?>
		</table>
	</td>
</tr>
<tr>
	<td valign='top'>
		<? if(isset($_GET['eid'])) { ?>
			<table>
			<tr>
				<td valign='top'>
					<form action="<?=$SCRIPT_NAME?>?eid=<?=$_GET['eid']?>&sid=<?=$_GET['sid']?>" method="post">
					<table class='admin_menu2' style='text-align:left;width:500px;'>
					<?
					$sql = "
						select *						
						from customers
						where id = '".sql_friendly($_GET['eid'])."'
					";
					$data_user = simple_query($sql);
					$row_user = mysqli_fetch_array($data_user);
					
					$mrr_activity_log_notes.="View customer ".$_GET['eid']." info. ";
					
					$mrr_show_fuel_surcharge=$row_user['use_fuel_surcharge'];
					
					$sql = "
						select *						
						from fuel_surcharge
						where customer_id = '".sql_friendly($_GET['eid'])."' and customer_id > 0
						order by range_lower
					";
					$data_surcharge = simple_query($sql);
					
					if(isset($_POST['name_company'])) {
						echo "<tr><td colspan='2' align='center'><font color='red'><b>Update Successful</b></font></td></tr>";
					}
					
					if($_GET['eid']==0)
					{
						$row_user['geofencing_radius_active']=1;
						$row_user['geofencing_hot_msg_all_loads']=1;
						$row_user['hot_load_radius_arriving']=$defaultsarray['peoplenet_geofencing_arriving'];
						$row_user['hot_load_radius_arrived']=$defaultsarray['peoplenet_geofencing_arrived'];
						$row_user['hot_load_radius_departed']=$defaultsarray['peoplenet_geofencing_departed'];
					}	
					
					?>
					<tr>
						<td colspan='2'>
							<center>
							<font class='standard16'>
							<b>
							Edit Company: <?=$row_user['name_company']?>
							</b>
							</font>
							</center>
						</td>
					</tr>
					<tr>
						<td>Company: <?= show_help('admin_customers.php','name_company') ?></td>
						<td>
							<input name="name_company" id="name_company" value="<?=$row_user['name_company']?>" size='40' onMouseOut='mrr_verify_user_unique(<?=$_GET['eid']?>);'>
							 <span id='mrr_naming_message'></span>
						</td>
					</tr>
					<tr>
						<td>Customer ID: <?= show_help('admin_customers.php','customer_id') ?></td>
						<td>
							<b><?= (isset($_GET['eid']) ? $_GET['eid'] : "") ?></b>
						</td>
					</tr>
					<tr>
						<td><label for='active'>Active:</label> <?= show_help('admin_customers.php','active') ?></td>
						<td><input type="checkbox" name="active" id="active" value="1" <? if($row_user['active']) echo "checked"?>></td>
					</tr>
					<tr>
						<td>MC No.: <?= show_help('admin_customers.php','mc_num') ?></td>
						<td><input name="mc_num" value="<?=(int) trim($row_user['mc_num']) ?>"></td>
					</tr>
					<tr>
						<td colspan='2'><b>Physical Address</b></td>
					</tr>
					<tr>
						<td>Address 1:</td>
						<td><input name="address1" value="<?=$row_user['address1']?>" size='40'></td>
					</tr>
					<tr>
						<td>Address 2:</td>
						<td><input name="address2" value="<?=$row_user['address2']?>" size='40'></td>
					</tr>
					<tr>
						<td>City:</td>
						<td><input name="city" value="<?=$row_user['city']?>"></td>
					</tr>
					<tr>
						<td>State:</td>
						<td><input name="state" value="<?=$row_user['state']?>"></td>
					</tr>
					<tr>
						<td>Zip:</td>
						<td><input name="zip" value="<?=$row_user['zip']?>"></td>
					</tr>
					<tr>
						<td colspan='2'><b>Billing Address</b></td>
					</tr>
					<tr>
						<td>Address 1:</td>
						<td><input name="billing_address1" value="<?=$row_user['billing_address1']?>" size='40'></td>
					</tr>
					<tr>
						<td>Address 2:</td>
						<td><input name="billing_address2" value="<?=$row_user['billing_address2']?>" size='40'></td>
					</tr>
					<tr>
						<td>City:</td>
						<td><input name="billing_city" value="<?=$row_user['billing_city']?>"></td>
					</tr>
					<tr>
						<td>State:</td>
						<td><input name="billing_state" value="<?=$row_user['billing_state']?>"></td>
					</tr>
					<tr>
						<td>Zip:</td>
						<td><input name="billing_zip" value="<?=$row_user['billing_zip']?>"></td>
					</tr>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<td>Cust Login Username:</td>
						<td><input id="customer_login_name" name="customer_login_name" value="<?=$row_user['customer_login_name']?>" size='20'> (no special characters)</td>
					</tr>
					<tr>
						<td>Cust Login Password:</td>
						<td><input id="customer_login_pass" name="customer_login_pass" value="<?=$row_user['customer_login_pass']?>" size='20'> (no special characters)</td>
					</tr>
					<tr>
						<td>Customer Login Link:</td>
						<td>
							<? if(trim($row_user['customer_login_name'])!="" && trim($row_user['customer_login_pass'])!="") { ?>
								<a href='http://trucking.conardtransportation.com/customer_loads.php?u=<?=$row_user['customer_login_name']?>&p=<?=$row_user['customer_login_pass']?>' target='_blank'>
									http://trucking.conardtransportation.com/customer_loads.php?u=<?=$row_user['customer_login_name']?>&p=<?=$row_user['customer_login_pass']?>
								</a>
							<? } else { ?>
								<b>Must have username and password to use the link.</b>
							<? } ?>
						</td>
					</tr>
					
					<tr>
						<td>&nbsp;</td>
						<td><center><input type="submit" value="Update" class='standard12'></center></td>
					</tr>
					<tr>
						<td>Primary Contact: <?= show_help('admin_customers.php','contact_primary') ?></td>
						<td>
							<input type="text" name="contact_primary" value="<?=$row_user['contact_primary']?>" size='40'>
						</td>
					</tr>
					<tr>
						<td>E-Mail: <?= show_help('admin_customers.php','contact_email') ?></td>
						<td><input name="contact_email" value="<?=$row_user['contact_email']?>" size='40'></td>
					</tr>
					<tr>
						<td>Website: <?= show_help('admin_customers.php','website') ?></td>
						<td><input name="website" value="<?=$row_user['website']?>" size='40'></td>
					</tr>					
					<tr>
						<td>Phone: <?= show_help('admin_customers.php','phone_work') ?></td>
						<td><input name="phone_work" value="<?=$row_user['phone_work']?>"></td>
					</tr>
					<tr>
						<td>Fax: <?= show_help('admin_customers.php','fax') ?></td>
						<td><input name="fax" value="<?=$row_user['fax']?>"></td>
					</tr>
					<tr>
						<td>Invoice Discount: <?= show_help('admin_customers.php','invoice_discount_percent') ?></td>
						<td><input name="invoice_discount_percent" value="<?=$row_user['invoice_discount_percent']?>" size='10'> ( 0.01 = 1%)</td>
					</tr>					
					<tr>
						<td><label for='use_fuel_surcharge'>Use Fuel Surcharge:</label> <?= show_help('admin_customers.php','use_fuel_surcharge') ?></td>
						<td>
							<input type="checkbox" name="use_fuel_surcharge" id="use_fuel_surcharge" value="1" <? if($row_user['use_fuel_surcharge']) echo "checked"?>>
							
							<label for='use_fuel_surcharge_auto'> ... Auto-Fill Load FSC</label>   <?= show_help('admin_customers.php','use_fuel_surcharge_auto') ?>
							
							<input type="checkbox" name="use_fuel_surcharge_auto" id="use_fuel_surcharge_auto" value="1" <? if($row_user['use_fuel_surcharge_auto']) echo "checked"?>>
						</td>
					</tr>
					<tr>
						<td>Fuel Surcharge: <?= show_help('admin_customers.php','fuel_surcharge') ?></td>
						<td><input name="fuel_surcharge" value="<?=$row_user['fuel_surcharge']?>" size='10'></td>
					</tr>
					<tr>
						<td><label for='flat_fuel_surchage_override'>Use Flat Fuel Surchage:</label> <?= show_help('admin_customers.php','flat_fuel_surchage_override') ?></td>
						<td><input type="checkbox" name="flat_fuel_surchage_override" id="flat_fuel_surchage_override" value="1" <? if($row_user['flat_fuel_surchage_override']) echo "checked"?>></td>
					</tr>
					<tr>
						<td>Flat Fuel Surcharge: <?= show_help('admin_customers.php','flat_fuel_surchage_mon') ?></td>
						<td>
							Mon &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
							Tue &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
							Wed &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
							Thu &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
							Fri &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							Sat &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							Sun
							<br>
							<input name="flat_fuel_surchage_mon" value="<?=$row_user['flat_fuel_surchage_mon']?>" size='3'>
							<input name="flat_fuel_surchage_tue" value="<?=$row_user['flat_fuel_surchage_tue']?>" size='3'>
							<input name="flat_fuel_surchage_wed" value="<?=$row_user['flat_fuel_surchage_wed']?>" size='3'>
							<input name="flat_fuel_surchage_thu" value="<?=$row_user['flat_fuel_surchage_thu']?>" size='3'>
							<input name="flat_fuel_surchage_fri" value="<?=$row_user['flat_fuel_surchage_fri']?>" size='3'>
							<input name="flat_fuel_surchage_sat" value="<?=$row_user['flat_fuel_surchage_sat']?>" size='3'>
							<input name="flat_fuel_surchage_sun" value="<?=$row_user['flat_fuel_surchage_sun']?>" size='3'>
						</td>
					</tr>
					
					<tr>
						<td>75K Document</td>
						<td>
							<?= show_help('admin_customers.php','document_75k_received') ?>
							<label for='document_75k_received'>Received:</label> <input type="checkbox" name="document_75k_received" id="document_75k_received" value="1" <? if($row_user['document_75k_received']) echo "checked"?>>
							
							<?= show_help('admin_customers.php','document_75k_exempt') ?>								
							<label for='document_75k_exempt'>Exempt</label> <input type="checkbox" name="document_75k_exempt" id="document_75k_exempt" value="1" <? if($row_user['document_75k_exempt']) echo "checked"?>>
													 
						</td>
					</tr>
					<tr>
						<td>75K Doc Inception</td>
						<td>
							<?
								$dater_75k="";
								if($row_user['linedate_document_75k']!="0000-00-00 00:00:00")	$dater_75k=date("m/d/Y", strtotime($row_user['linedate_document_75k']));
								if($dater_75k=="12/31/1969")		$dater_75k="";
							?>
							<input name="linedate_document_75k" id='linedate_document_75k' value="<?=$dater_75k ?>">
							(mm/dd/yyyy) <?= show_help('admin_customers.php','linedate_document_75k') ?> 														 
						</td>
					</tr>	
                    <tr>
                        <td>75K Document</td>
                        <td>
                             <?
                             $dater_75kr="";
                             if($row_user['linedate_renewal_75k']!="0000-00-00 00:00:00")	$dater_75kr=date("m/d/Y", strtotime($row_user['linedate_renewal_75k']));
                             if($dater_75kr=="12/31/1969")		$dater_75kr="";

                             if($row_user['document_75k_received'] > 0 && $dater_75kr=="" && $row_user['linedate_document_75k']>="2010-01-01 00:00:00")
                             {
                                  $dater_75kr=date("m/d/Y", strtotime("+6 month",strtotime($row_user['linedate_document_75k'])));
                             }
                             
                             //$row_user['linedate_document_75k']
                             
                             $dater_75kx="";
                             if($row_user['linedate_expires_75k']!="0000-00-00 00:00:00")	$dater_75kx=date("m/d/Y", strtotime($row_user['linedate_expires_75k']));
                             if($dater_75kx=="12/31/1969")		$dater_75kx="";
                             
                             if($row_user['document_75k_received'] > 0 && $dater_75kx=="" && $row_user['linedate_document_75k']>="2010-01-01 00:00:00")
                             {
                                  $dater_75kx=date("m/d/Y", strtotime("+6 month",strtotime($row_user['linedate_document_75k'])));
                             }                             
                             ?>
                            <table width="100%" border="0">
                                <tr>
                                    <td valign="top"><b>Renewed</b> <?= show_help('admin_customers.php','linedate_renewal_75k') ?></td>
                                    <td>&nbsp;</td>
                                    <td valign="top"><b>Expires</b> <?= show_help('admin_customers.php','linedate_expires_75k') ?></td>
                                </tr>
                                <tr>
                                    <td valign="top">
                                        <input name="linedate_renewal_75k" id='linedate_renewal_75k' value="<?=$dater_75kr ?>" readonly style="width:80px;">                                         
                                    </td>
                                    <td><input type="button" value="Renew (+6 Months)" class='standard12' onClick="mrr_update_renew_dates(1);"></td>
                                    <td valign="top">
                                        <input name="linedate_expires_75k" id='linedate_expires_75k' value="<?=$dater_75kx ?>" readonly style="width:80px;">                                         
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td>Credit Check</td>
                        <td>
                             <?= show_help('admin_customers.php','cust_credit_check') ?>
                            <label for='cust_credit_check'>Run:</label> <input type="checkbox" name="cust_credit_check" id="cust_credit_check" value="1" <? if($row_user['cust_credit_check']) echo "checked"?>>

                        </td>
                    </tr>
                    <tr>
                        <td>Last Credit Check</td>
                        <td>
                             <?
                             $dater_chck="";
                             if($row_user['linedate_credit_check']!="0000-00-00 00:00:00")	$dater_chck=date("m/d/Y", strtotime($row_user['linedate_credit_check']));
                             if($dater_chck=="12/31/1969")		$dater_chck="";
                             
                             if($row_user['cust_credit_check'] > 0 && $dater_chck=="" && $row_user['linedate_credit_check']>="2010-01-01 00:00:00")
                             {
                                  $dater_chck=date("m/d/Y", strtotime("+1 year",strtotime($row_user['linedate_credit_check'])));
                             }
                             
                             //linedate_credit_check_exp
                             
                             $dater_chck_exp="";
                             if($row_user['linedate_credit_check_exp']!="0000-00-00 00:00:00")	$dater_chck_exp=date("m/d/Y", strtotime($row_user['linedate_credit_check_exp']));
                             if($dater_chck_exp=="12/31/1969")		$dater_chck_exp="";

                             if($row_user['cust_credit_check_exp'] > 0 && $dater_chck_exp=="" && $row_user['linedate_credit_check_exp']>="2010-01-01 00:00:00")
                             {
                                  $dater_chck_exp=date("m/d/Y", strtotime("+1 year",strtotime($row_user['linedate_credit_check_exp'])));
                             }
                             ?>
                            <table width="100%" border="0">
                                <tr>
                                    <td valign="top"><b>Renewed</b> <?= show_help('admin_customers.php','linedate_credit_check') ?></td>
                                    <td>&nbsp;</td>
                                    <td valign="top"><b>Expires</b> <?= show_help('admin_customers.php','linedate_credit_check_exp') ?></td>
                                </tr>
                                <tr>
                                    <td valign="top">
                                        <input name="linedate_credit_check" id='linedate_credit_check' value="<?=$dater_chck ?>" readonly style="width:80px;">                                             
                                    </td>
                                    <td><input type="button" value="Checked (+1 Year)" class='standard12' onClick="mrr_update_renew_dates(2);"></td>
                                    <td valign="top">
                                        <input name="linedate_credit_check_exp" id='linedate_credit_check_exp' value="<?=$dater_chck_exp ?>" readonly style="width:80px;">                                         
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>    
                        
					<tr>
						<td><label for='email_invoice'>E-Mail Invoices:</label> <?= show_help('admin_customers.php','email-invoice') ?></td>
						<td><input type="checkbox" name="email_invoice" id="email_invoice" value="1" <? if($row_user['email_invoice']) echo "checked"?>></td>
					</tr>
					<tr>
						<td nowrap><label for='separate_truck_section'>Display Separate<br>Truck Section:</label> <?= show_help('admin_customers.php','separate_truck_section') ?></td>
						<td valign='top'><input type="checkbox" name="separate_truck_section" id="separate_truck_section" value="1" <? if($row_user['separate_truck_section']) echo "checked"?>></td>
					</tr>
					<tr>
						<td><label for='slow_pays'>Slow Paying:</label> <?= show_help('admin_customers.php','slow_pays') ?></td>
						<td>
							<input type="checkbox" name="slow_pays" id="slow_pays" value="1" <? if($row_user['slow_pays']) echo "checked"?>>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
							<label for='override_slow_pays'>Override Slow Pays:</label> 
							<input type="checkbox" name="override_slow_pays" id="override_slow_pays" value="1" <? if($row_user['override_slow_pays']) echo "checked"?> onClick='mrr_sg_warning();'>
						</td>
					</tr>
					
					<tr>
						<td><label for='dirt_bags_flag'><b>Dirt Bags!</b></label> <?= show_help('admin_customers.php','dirt_bags_flag') ?></td>
						<td>
							<input type="checkbox" name="dirt_bags_flag" id="dirt_bags_flag" value="1" <? if($row_user['dirt_bags_flag']) echo "checked"?>>
							Customer may pay, but has attitude problem, etc.
						</td>
					</tr>
					
					<tr>
						<td><label for='credit_hold'>Credit Hold:</label> <?= show_help('admin_customers.php','credit_hold') ?></td>
						<td>
							<input type="checkbox" name="credit_hold" id="credit_hold" value="1" <? if($row_user['credit_hold']) echo "checked"?>>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
							<label for='override_credit_hold'>Override Credit Hold:</label> 
							<input type="checkbox" name="override_credit_hold" id="override_credit_hold" value="1" <? if($row_user['override_credit_hold']) echo "checked"?> onClick='mrr_sg_warning();'>
						</td>
					</tr>
					<tr>
						<td valign='top'>Payment Notes: <?= show_help('admin_customers.php','payment_notes') ?></td>
						<td valign='top'><textarea name="payment_notes" id="payment_notes" rows='3' cols='35'></textarea></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><center><input type="submit" value="Update" class='standard12'></center></td>
					</tr>
                    <tr>
                        <td colspan="2">
                            <center><h4><b>Payment Notes History</b></h4></center>
                            <?php
                            // $row_user['payment_notes'] 
                            echo "<table cellpadding='1' cellspacing='1' border='0' width='100%'>";
                            echo "
                                <tr>
                                    <th valign='top'>Username</th>
                                    <th valign='top'>Added</th>
                                    <th valign='top'>Payment Note</th>
                                </tr>
                            ";
                            $cntru=0;
                            
                            $sqlu = "
                                select payment_notes_history.*,
                                    (select username from users where users.id=payment_notes_history.user_id) as user_name                                
                                from payment_notes_history
                                where payment_notes_history.customer_id = '".sql_friendly($_GET['eid'])."'
                                     and payment_notes_history.deleted<=0
                                order by payment_notes_history.linedate_added desc
                            ";
                            $datau = simple_query($sqlu);
                            while($rowu = mysqli_fetch_array($datau))
                            {
                                echo "
                                    <tr style='background-color:#".($cntru%2==0 ? "eeeeee" : "dddddd").";'>
                                        <td valign='top'>".$rowu['user_name']."</td>
                                        <td valign='top'>".date("m/d/Y H:i:s",strtotime($rowu['linedate_added']))."</td>
                                        <td valign='top'>".$rowu['payment_note']."</td>
                                    </tr>
                                ";
                                $cntru++;
                            }
                            echo "</table>";
                            ?>
                        </td>
                    </tr>    
                    <tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><label for='edi_use_214_files'>EDI 214 file on stops:</label> <?= show_help('admin_customers.php','edi_use_214_files') ?></td>
						<td><input type="checkbox" name="edi_use_214_files" id="edi_use_214_files" value="1" <? if($row_user['edi_use_214_files']) echo "checked"?>></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>					
					<tr>
						<td><hr></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='2'><b>Geofencing and Hot Load Messages</b></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>	
					<tr>
						<td><label for='geofencing_radius_active'>Use Geofencing:</label> <?= show_help('admin_customers.php','geofencing_radius_active') ?></td>
						<td><input type="checkbox" name="geofencing_radius_active" id="geofencing_radius_active" value="1" <? if($row_user['geofencing_radius_active']) echo "checked"?>></td>
					</tr>
					<tr>
						<td><label for='geofencing_hot_msg_all_loads'>Geofencing all Loads:</label> <?= show_help('admin_customers.php','geofencing_hot_msg_all_loads') ?></td>
						<td><input type="checkbox" name="geofencing_hot_msg_all_loads" id="geofencing_hot_msg_all_loads" value="1" <? if($row_user['geofencing_hot_msg_all_loads']) echo "checked"?>></td>
					</tr>			
					<tr>
						<td><label for='hot_load_switch'>Use Hot Load Messages:</label> <?= show_help('admin_customers.php','hot_load_switch') ?></td>
						<td><input type="checkbox" name="hot_load_switch" id="hot_load_switch" value="1" <? if($row_user['hot_load_switch']) echo "checked"?>></td>
					</tr>					
					<tr>
						<td colspan='2'>GeoFencing Radius Tolerance is set to <b><?= $defaultsarray['peoplenet_geofencing_tolerance'] ?></b> feet.<br>Tolerance is added to radius setting for each part.</td>
					</tr>			
					<tr>
						<td>&nbsp;</td>
					</tr>		
					<tr>
						<td colspan='2'><b>Arriving</b></td>
					</tr>
					<tr>
						<td>Hot Load Timer (hrs): <?= show_help('admin_customers.php','hot_load_timer') ?></td>
						<td><input name="hot_load_timer" value="<?=number_format($row_user['hot_load_timer'],2)?>" size='10'></td>
					</tr>
					<tr>
						<td>Radius (in feet): <?= show_help('admin_customers.php','hot_load_radius_arriving') ?></td>
						<td><input name="hot_load_radius_arriving" value="<?=$row_user['hot_load_radius_arriving']?>" size='10'></td>
					</tr>
					<tr>
						<td valign='top'>
							Email Address(es): <?= show_help('admin_customers.php','hot_load_email_arriving') ?>
							<center><input type="button" value="Fill Below Addresses" class='standard12' onClick='mrr_fill_hot_load_addresses();'></center>
						</td>
						<td valign='top'><textarea name="hot_load_email_arriving" id="hot_load_email_arriving" rows='3' cols='35'><?= $row_user['hot_load_email_arriving'] ?></textarea></td>
					</tr>
					<tr>
						<td valign='top'>Arriving Message (S): <?= show_help('admin_customers.php','hot_load_email_msg_arriving_shipper') ?></td>
						<td valign='top'><textarea name="hot_load_email_msg_arriving_shipper" id="hot_load_email_msg_arriving_shipper" rows='3' cols='35'><?= $row_user['hot_load_email_msg_arriving_shipper'] ?></textarea></td>
					</tr>
					<tr>
						<td valign='top'>Arriving Message (C): <?= show_help('admin_customers.php','hot_load_email_msg_arriving') ?></td>
						<td valign='top'><textarea name="hot_load_email_msg_arriving" id="hot_load_email_msg_arriving" rows='3' cols='35'><?= $row_user['hot_load_email_msg_arriving'] ?></textarea></td>
					</tr>
					
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='2'><b>Arrived</b></td>
					</tr>
					<tr>
						<td>Hot Load Timer (hrs):</td>
						<td>Automatic... when truck has arrived.</td>
					</tr>
					<tr>
						<td>Radius (in feet): <?= show_help('admin_customers.php','hot_load_radius_arrived') ?></td>
						<td><input name="hot_load_radius_arrived" value="<?=$row_user['hot_load_radius_arrived']?>" size='10'></td>
					</tr>
					<tr>
						<td valign='top'>Email Address(es): <?= show_help('admin_customers.php','hot_load_email_arrived') ?></td>
						<td valign='top'><textarea name="hot_load_email_arrived" id="hot_load_email_arrived" rows='3' cols='35'><?= $row_user['hot_load_email_arrived'] ?></textarea></td>
					</tr>
					<tr>
						<td valign='top'>Arrived Message (S): <?= show_help('admin_customers.php','hot_load_email_msg_arrived_shipper') ?></td>
						<td valign='top'><textarea name="hot_load_email_msg_arrived_shipper" id="hot_load_email_msg_arrived_shipper" rows='3' cols='35'><?= $row_user['hot_load_email_msg_arrived_shipper'] ?></textarea></td>
					</tr>
					<tr>
						<td valign='top'>Arrived Message (C): <?= show_help('admin_customers.php','hot_load_email_msg_arrived') ?></td>
						<td valign='top'><textarea name="hot_load_email_msg_arrived" id="hot_load_email_msg_arrived" rows='3' cols='35'><?= $row_user['hot_load_email_msg_arrived'] ?></textarea></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan='2'><b>Departed</b></td>
					</tr>		
					<tr>
						<td>Radius (in feet): <?= show_help('admin_customers.php','hot_load_radius_departed') ?></td>
						<td><input name="hot_load_radius_departed" value="<?=$row_user['hot_load_radius_departed']?>" size='10'></td>
					</tr>		
					<tr>
						<td valign='top'>Email Address(es): <?= show_help('admin_customers.php','hot_load_email_departed') ?></td>
						<td valign='top'><textarea name="hot_load_email_departed" id="hot_load_email_departed" rows='3' cols='35'><?= $row_user['hot_load_email_departed'] ?></textarea></td>
					</tr>		
					<tr>
						<td valign='top'>Departed Message (S): <?= show_help('admin_customers.php','hot_load_email_msg_departed_shipper') ?></td>
						<td valign='top'><textarea name="hot_load_email_msg_departed_shipper" id="hot_load_email_msg_departed_shipper" rows='3' cols='35'><?= $row_user['hot_load_email_msg_departed_shipper'] ?></textarea></td>
					</tr>
					<tr>
						<td valign='top'>Departed Message (C): <?= show_help('admin_customers.php','hot_load_email_msg_departed') ?></td>
						<td valign='top'><textarea name="hot_load_email_msg_departed" id="hot_load_email_msg_departed" rows='3' cols='35'><?= $row_user['hot_load_email_msg_departed'] ?></textarea></td>
					</tr>	
					<tr>
						<td><label for='send_manual_depart_notice'>Send Manual Depart Msg:</label> <?= show_help('admin_customers.php','send_manual_depart_notice') ?></td>
						<td><input type="checkbox" name="send_manual_depart_notice" id="send_manual_depart_notice" value="1" <? if($row_user['send_manual_depart_notice']) echo "checked"?>></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><center><input type="submit" value="Update" class='standard12'></center></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					</table>
					<div class='change_log'>
						<?
							if($_GET['eid'] > 0 )	echo mrr_get_user_change_log(" and user_change_log.customer_id='".sql_friendly($_GET['eid'])."'"," order by user_change_log.linedate_added asc","",1);
						 ?>
					</div>
					</form>
				</td>
				<td valign='top'>
					<? if($_GET['eid'] > 0 ) { ?>

                        <div class='clear'></div>
                        <br>
                        <div id='internal_tasks' style='margin-bottom:10px;'>
                            <table class='admin_menu1' style='width:100%;margin-bottom:10px'>
                                <tr>
                                    <td colspan='4' class='border_bottom'><div class='section_heading'>Internal Tasks</div></td>
                                </tr>
                                <tr>
                                    <td valign='top'><b>Task</b></td>
                                    
                                    <td valign='top'><b>Due Date</b></td>
                                    <td valign='top'><b>Checked by</b></td>
                                    <td valign='top'><b>Completed</b></td>
                                </tr>
                                 <?php
                                 //<td valign='top'><b>Unit</b></td>
                                 $task_cntr=0;
                                 $sql_tasks = "
                                select internal_tasks_checked.*,
                                    internal_tasks.task_name,
                                    internal_tasks.task_type,
                                    (select name_truck from trucks where trucks.id=internal_tasks_checked.entity_id) as truck_name,
                                    (select trailer_name from trailers where trailers.id=internal_tasks_checked.entity_id) as trailer_name,
                                    (select CONCAT(name_driver_first,' ',name_driver_last) from drivers where drivers.id=internal_tasks_checked.entity_id) as driver_name,
                                    (select customers.name_company from customers where customers.id=internal_tasks_checked.entity_id) as cust_name,
		                            (select CONCAT(us.name_first,' ',us.name_last) from users us where us.id=internal_tasks_checked.entity_id) as user_name,
                                    users.username	
                                from internal_tasks_checked
                                    left join internal_tasks on internal_tasks.id=internal_tasks_checked.task_id
                                    left join users on users.id=internal_tasks_checked.user_id
                                where internal_tasks.deleted <= 0 and internal_tasks_checked.deleted<=0
                                    and (internal_tasks.active > 0 || internal_tasks_checked.user_id > 0)
                                    and internal_tasks.task_type = 4
                                    and internal_tasks_checked.entity_id='".(int) $_GET['eid']."'
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
                                      if($row_tasks['task_type']==1)    $entity_name="<i>".trim($row_tasks['truck_name'])."</i>";
                                      //if($row_tasks['task_type']==2)  $entity_name="<a href='admin_trailers.php?id=".$row_tasks['entity_id']."' target='blank'>".trim($row_tasks['trailer_name'])."</a>";
                                      //if($row_tasks['task_type']==3)  $entity_name="<a href='admin_drivers.php?id=".$row_tasks['entity_id']."' target='blank'>".trim($row_tasks['driver_name'])."</a>";
                                      if($row_tasks['task_type']==4)    $entity_name="<i>".trim($row_tasks['cust_name'])."</i>";
                                      
                                      echo "
                                        <tr style='background-color:".($task_cntr%2==0 ? "eeeeee" : "dddddd").";'>
                                            <td valign='top'>".$row_tasks['task_name']."</td>
                                            
                                            <td valign='top'>".$cur_dater."</td>                                        
                                            <td valign='top'><i>".$done_by."</i></td>
                                            <td valign='top'><i>".$done_on."</i></td>                                     
                                        </tr>
                                 ";     //              mrr_set_internal_task       <td valign='top'>".$entity_name."</td>
                                      $task_cntr++;
                                 }
                                 ?>
                            </table>
                        </div>
                         
                         
                         
     					<form action="<?=$SCRIPT_NAME?>?eid=<?=$_GET['eid']?>&sid=<?=$_GET['sid']?>" name='second_form' method="post">
     					<table class='admin_menu1' style='width:400px'>
     					<tr>
     						<td colspan='2'>
     							<div id='additional_contact_section'></div>
     							<div class='clear'></div>
     						</td>
     					</tr>					
     					<tr>
     						<td colspan='2'>
     							<div id='note_section'></div>
     							<div class='clear'></div>
     						</td>
     					</tr>
     					<tr>
     						<td colspan='2'>
     							<? if($use_new_uploader > 0) { ?>
                              		
                              			<br>&nbsp;<br>
                                   		<iframe src="mrr_uploader_hack.php?section_id=5&id=<?=$_GET['eid']?>" width='550' height='80' border='0' style='border:#000000 solid 0px; background-color:#ffffff;'>
                                   		</iframe> 
                                   		<div id='attachment_holder'></div>
                              		
                              		<? } else { ?>
                              		
                              			<div id='upload_section'></div>
                              		
                              		<? } ?>
     							
     							
     							<div class='clear'></div>
     						</td>
     					</tr>
     					</table>
     					
     					</form>
					<? } ?>
				</td>
				<td valign='top'>					
					<table class='admin_menu3'>
					<? if($_GET['eid'] > 0 ) { ?>
					<tr>
						<td colspan='2' align='center'><font class='standard14'><b>Fuel Surcharge</b></font></td>
					</tr>
					<tr>
						<td colspan='2' align='center'><a href="<?=$SCRIPT_NAME?>?eid=<?=$_GET['eid']?>&newsurcharge=1">Add new surcharge range</a></td>
					</tr>
					<?
						if(isset($_GET['sid'])) {
							$sql = "
								select *
								
								from fuel_surcharge
								where id = '".sql_friendly($_GET['sid'])."'
							";
							$data_surcharge_detail = simple_query($sql);
							$row_surcharge_detail = mysqli_fetch_array($data_surcharge_detail);
					?>
							
     							<form action="<?=$SCRIPT_NAME?>?eid=<?=$_GET['eid']?>&sid=<?=$_GET['sid']?>" method="post">		
     							<tr>
     								<td align='right'>Lower Range: <?= show_help('admin_customers.php','Lower Range') ?></td>
     								<td><input name='range_lower' value='<?=$row_surcharge_detail['range_lower']?>' size='5'></td>
     							</tr>
     							<tr>
     								<td align='right'>Surcharge: <?= show_help('admin_customers.php','Surcharge') ?></td>
     								<td><input name='fuel_surcharge' value='<?=$row_surcharge_detail['fuel_surcharge']?>' size='5'></td>
     							</tr>
     							<tr>
     								<td colspan='2' align='center'>
     									<input type='submit' value='Update Fuel Surcharge'>
     								</td>
     							</tr>
     							</form>							
					<? 
						} 
					?>
					<? } ?>
					<tr class='mrr_hide_fuel_surcharge'>
						<td colspan='2' align='center'>
							<table class='standard12'>
							<tr>
								<td><b>Lower</b></td>
								<td><b>Surcharge</b></td>
								<td>&nbsp;</td>
							</tr>
							<? while($row_surcharge = mysqli_fetch_array($data_surcharge)) { ?>
								<tr>
									<td><?=$row_surcharge['range_lower']?></td>
									<td><?=$row_surcharge['fuel_surcharge']?></td>
									<td>
										<a href="<?=$SCRIPT_NAME?>?eid=<?=$_GET['eid']?>&sid=<?=$row_surcharge['id']?>"><img src="images/edit_small.gif" border="0"></a>
										&nbsp;
										<a href="javascript:confirm_del_surcharge(<?=$row_surcharge['id']?>,<?=$_GET['eid']?>)"><img src="images/delete_sm.gif" border="0"></a>
									</td>
								</tr>
							<? } ?>
							</table>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
			
		<? } ?>
	</td>
</tr>
</table>

<? include('footer.php') ?>

<script type='text/javascript'>
	
	function confirm_del_surcharge(id,eid) { 
		if(confirm("Are you sure you want to delete this surcharge lineitem?")) {
			window.location = "<?=$SCRIPT_NAME?>?eid="+eid+"&delsurcharge=" + id;
		}
	}
	function confirm_del(id) {
		if(confirm("Are you sure you want to delete this customer?")) {
			window.location = "<?=$SCRIPT_NAME?>?delid=" + id;
		}
	}
	function confirm_del_addr(id) {
		if(confirm("Are you sure you want to delete this address?")) {
			window.location = "<?=$SCRIPT_NAME?>?deladdr=" + id;
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
	
	function mrr_fill_hot_load_addresses()
	{
		txt_val=$('#hot_load_email_arriving').val();
		
		//Subject -- name: load number: Check Call
		
		$('#hot_load_email_arrived').val(txt_val);
		$('#hot_load_email_departed').val(txt_val);
	}
	function mrr_sg_warning()
	{
		//override_credit_hold
		if($('#override_credit_hold').attr('checked'))
		{
     		txt="<div class='alert'>Surgeon Genereal's Warning:</div>";
     		txt= txt + "<div>";
     		txt= txt + " Overriding a customer <span class='alert'>Credit Hold</span> or <span class='alert'>Slow Pays</span> may be hazardous to your employment status.";
     		txt= txt + " Please verify with Dale or James that this is permitted.";
     		txt= txt + " Are you sure you are allowed to overrride this credit hold for this customer?";     		
     		txt= txt + "</div>";
     		
     		$.prompt(txt, {
     				buttons: {Yes: true, No:false},
     				submit: function(v, m, f) {
     					if(v) 
     					{
     						$('#override_credit_hold').attr('checked','checked'); 
     					}
     					else
     					{
     						$('#override_credit_hold').attr('checked','');	
     					}
     				}
     			}
     		);
		}
	}
	
	function confirm_del_contact(id,eid) {
		var myid_val=id;
		if(confirm("Are you sure you want to delete this additional contact?")) {
			
			$.ajax({
     		   type: "POST",
     		   url: "ajax.php?cmd=kill_additional_contacts",
     		   dataType: "xml",
     		   data: {
     		   		"contact_id": myid_val
     		   		},
     		   success: function(xml) {
     		   		kill_contact_id=$(xml).find('ContactID').text();
     				$.noticeAdd({text: "Contact "+kill_contact_id+" has been removed."});	
     		   		$('.contact_'+kill_contact_id+'').remove();
     		   }		   
     		 });	
			
		}
	}
	<? 
		if(isset($_GET['eid']) && $_GET['eid'] > 0) {
			//echo " create_upload_section('#upload_section', ".SECTION_CUSTOMER.", $_GET[eid]); "; 
			if($use_new_uploader > 0) 
			{ 
				echo " create_upload_section_alt('#upload_section', ".SECTION_CUSTOMER.", $_GET[eid]); "; 
			}
			else
			{
				echo " create_upload_section('#upload_section', ".SECTION_CUSTOMER.", $_GET[eid]); "; 
			}
			echo " create_note_section('#note_section', ".SECTION_CUSTOMER.", $_GET[eid]); "; 
			echo " create_additional_contacts('#additional_contact_section', 0, $_GET[eid]); "; 
			echo " var my_eid=".$_GET['eid'].";";
		}
		
		if($mrr_show_fuel_surcharge == 0)
		{
			echo "$('.mrr_hide_fuel_surcharge').hide();";
		}
	?>	
	
	function mrr_save_contact_info()
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=save_additional_contacts",
		   data: {
		   		ac_cust: my_eid,
		   		ac_id: $('#add_contact_id').val(),
		   		ac_name: $('#add_contact_name').val(),
		   		ac_addr1: $('#add_contact_address1').val(),
		   		ac_addr2: $('#add_contact_address2').val(),
		   		ac_city: $('#add_contact_city').val(),
		   		ac_state: $('#add_contact_state').val(),
		   		ac_zip: $('#add_contact_zip').val(),
		   		ac_email: $('#add_contact_email').val(),
		   		ac_work: $('#add_contact_work').val(),
		   		ac_fax: $('#add_contact_fax').val(),
		   		ac_cell: $('#add_contact_cell').val(),
		   		ac_home: $('#add_contact_home').val(),
		   		},
		   success: function(data) {
		   		new_contact_id=$(this).find('ContactID').text();
				new_contact_name=$(this).find('ContactName').text();
				$.noticeAdd({text: "Contact "+new_contact_id+" Info for "+new_contact_name+" has been saved."});	
						
		   		//create_additional_contacts('#additional_contact_section', 0, my_eid);
		   		display_additional_contacts($('#add_contact_id').val(), my_eid);	
		   }		   
		 });	
	}
	
	function mrr_load_contact_info(contactor)
	{
		<? if(isset($_GET['eid']) && $_GET['eid'] > 0) { ?>     			
     		$.ajax({
     		   type: "POST",
     		   url: "ajax.php?cmd=load_additional_contacts",
     		   data: {"contact_id":contactor},
     		   success: function(xml) {
     		   		$(xml).find('Contact').each(function() {
          		   		$('#add_contact_id').val( $(this).find('ContactID').text() );
          		   		$('#add_contact_name').val( $(this).find('ContactName').text() );
          		   		$('#add_contact_address1').val( $(this).find('ContactAddr1').text() );
          		   		$('#add_contact_address2').val( $(this).find('ContactAddr2').text() );
          		   		$('#add_contact_city').val( $(this).find('ContactCity').text() );
          		   		$('#add_contact_state').val( $(this).find('ContactState').text() );
          		   		$('#add_contact_zip').val( $(this).find('ContactZip').text() );
          		   		$('#add_contact_email').val( $(this).find('ContactEmail').text() );
          		   		$('#add_contact_work').val( $(this).find('ContactWork').text() );
          		   		$('#add_contact_fax').val( $(this).find('ContactFax').text() );
          		   		$('#add_contact_cell').val( $(this).find('ContactCell').text() );
          		   		$('#add_contact_home').val( $(this).find('ContactHome').text() );
          		   		
          		   		id_val=$(this).find('ContactID').text();
          		   		if(id_val > 0)
          		   		{
          		   			$('#update_contact_info').val( 'Update Contact Info' );
          		   		}
          		   		else
          		   		{
          		   			$('#update_contact_info').val( 'Add Contact Info' );
          		   		}
          		   	});
     		   }
     		 });
		<? } ?>
	}
	
	
	function create_additional_contacts(element_holder, section_id, xref_id) 
	{		
		<? if(isset($_GET['eid']) && $_GET['eid'] > 0) { ?> 
     		uc_tmp = "<div id='additional_contacts_container'>";
     			uc_tmp += "<div class='inside_container'>";
     				uc_tmp += "<div class='header'>Additional Contacts</div>";
     
     			uc_tmp += "</div>";
     			uc_tmp += "<div id='additional_contacts_holder' style='border: 1px solid grey;'>";
     			uc_tmp += "</div>";
     		uc_tmp += "</div>";
     			
     		$(element_holder).append(uc_tmp);
     		
     		display_additional_contacts(section_id, xref_id);	
		<? } ?>	
	}
	function display_additional_contacts(section_id, xref_id) 
	{
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=display_additional_contacts",
		   data: {"contact_id":section_id,
		   		"customer_id":xref_id},
		   success: function(data) {
		   		$('#additional_contacts_holder').html(data);
		   }
		 });
	}
	
	function mrr_verify_user_unique(myid)
	{		
		<? if(isset($_GET['eid']) && $_GET['eid'] > 0) { ?>  
     		$('#mrr_naming_message').html('');	
     		mrr_lab="Customer";
     		mrr_lab2="name_company";
     		mrr_code=2;
     		
     		new_name=$('#'+mrr_lab2+'').val();
     		
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
     				//$('#'+mrr_lab2+'').val(''+new_name+'.');
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
     					$('#'+mrr_lab2+'').val(''+new_name+'.');
     					$('#mrr_naming_message').html(''+mrr_lab+' must be unique.');	
     					$('#mrr_naming_message').css('color','red');									
     				}				
     			}
     		});	
		<? } ?>
	}
	
	//$('#linedate_document_75k').datepicker();
    //$('#linedate_renewal_75k').datepicker();
    //$('#linedate_credit_check').datepicker();
    //$('#linedate_credit_check_exp').datepicker();

    $('.tablesorter').tablesorter();
    
    var right_now="";
	var plus_months="";
    var plus_months2="";
	<?php
	    echo "
	        right_now='".date("m/d/Y",time())."';
	        plus_months='".date("m/d/Y",strtotime("+6 month",time()))."';
	        plus_months2='".date("m/d/Y",strtotime("+1 year",time()))."';
	       ";
	?>
    
    function  mrr_update_renew_dates(cd)
    {
        if(cd==1)
        {   // this is hte 75K renewal section...            
            $('#linedate_renewal_75k').val(right_now);
            $('#linedate_expires_75k').val(plus_months);
        }
        if(cd==2)
        {   // this is hte 75K renewal section...
            $('#linedate_credit_check').val(right_now);
            $('#linedate_credit_check_exp').val(plus_months2);
        }
    }
	
	function duplicate_cust(cust_id) 
	{
		$.prompt("Are you sure you want to <span class='alert'>duplicate</span> this Customer?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						window.location = "<?=$_SERVER['SCRIPT_NAME']?>?duplicate="+cust_id;
					}
				}
			}
		);	
	}
	
</script>