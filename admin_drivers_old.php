<? include('application.php') ?>
<? $admin_page = 1 ?>
<?		
	$use_admin_level=mrr_get_user_access_level($_SESSION['user_id']);
	if($use_admin_level < 10)
	{
		header("Location: report_geotab_activity.php?verify=1");
		die;	
	}
	/*
	if(isset($_GET['mrr'])) {
		
		$sqlu = "
			select id,employer_id 
			from drivers
			order by id asc
		";
		$datau = simple_query($sqlu);
		while($rowu = mysqli_fetch_array($datau)) 
		{
			$sqlu2 = "
				update trucks_log set
					employer_id='".sql_friendly($rowu['employer_id'])."'
				where driver_id='".sql_friendly($rowu['id'])."'
			";
			simple_query($sqlu2);
		}
		
	}	
	*/
		
	if(isset($_GET['duplicate'])) 
	{	//Added Nov 2015...MRR
		$dup_id = duplicate_row('drivers', $_GET['duplicate']);
				
		$sql = "
			update drivers set
				name_driver_first= CONCAT('DUP-',name_driver_first),
				linedate_birthday = '0000-00-00',
				linedate_drugtest = '0000-00-00',
				linedate_started = NOW(),
				linedate_driver_has_load = '0000-00-00 00:00:00',
				linedate_license_expires = '0000-00-00 00:00:00',
				attached_truck_id='0',
				attached2_truck_id='0',
				attached_trailer_id='0',
				driver_has_load='0',
				payroll_bonus_override='0',
				available_notes='',
				linedate_available_notes = '0000-00-00 00:00:00',
				linedate_cov_expires = '0000-00-00 00:00:00',
				linedate_terminated = '0000-00-00 00:00:00',
				linedate_rehire = '0000-00-00 00:00:00',
				linedate_refire = '0000-00-00 00:00:00',
				shirt_size='',
				linedate_rehire2 = '0000-00-00 00:00:00',
				linedate_refire2 = '0000-00-00 00:00:00',
				linedate_rehire3 = '0000-00-00 00:00:00',
				linedate_refire3 = '0000-00-00 00:00:00',
				linedate_rehire4 = '0000-00-00 00:00:00',
				linedate_refire4 = '0000-00-00 00:00:00',
				linedate_rehire5 = '0000-00-00 00:00:00',
				linedate_refire5 = '0000-00-00 00:00:00',
				
				linedate_misc_cert = '0000-00-00 00:00:00',
				misc_cert_name = '',
				geotab_use_id='',
				
				own_op_ins_flag=0,
				own_op_ins_number='',
				linedate_own_op_ins = '0000-00-00 00:00:00',
								
				linedate_review_due = '0000-00-00 00:00:00',
				linedate_spouse = '0000-00-00 00:00:00',
				linedate_anniversary = '0000-00-00 00:00:00',
				dl_number='',
				dl_state='',
				active='1',
				driver_status='0',
				driver_status_date='0000-00-00 00:00:00',
				driver_status_notes='',
				driver_no_text_msg='0',
				shuttle_runner=0,
				night_shifter=0,
				deleted='0'
				
			where id = '".sql_friendly($dup_id)."'
		";
		simple_query($sql);
				
		if($dup_id > 0)
		{
			$cur_employer_id=0;
			$sqlu = "
				select employer_id 
				from drivers
				where id = '".sql_friendly($dup_id)."'
			";
			$datau = simple_query($sqlu);
			if($rowu = mysqli_fetch_array($datau)) 
			{
				$cur_employer_id=$rowu['employer_id'];
			}
			
			//log employer change...initializing
     		$sql2="insert into drivers_employer_change 
     					(id,linedate_added,linedate,driver_id,old_employer_id,new_employer_id,deleted) 
     				values 
     					(NULL,NOW(),'0000-00-00 00:00:00','".sql_friendly($dup_id)."',0,0,0)";
     		simple_query($sql2);
     		
			//employer info...			
			$sqlu = "
				insert into drivers_employer_change 
					(id,
					linedate_added,
					linedate,
					driver_id,
					old_employer_id,
					new_employer_id,
					deleted)
				values
					(NULL,
					NOW(),
					NOW(),
					'".sql_friendly($dup_id)."',
					'0',
					'".sql_friendly($cur_employer_id)."',
					0)
				";
			simple_query($sqlu);
		
			$mrr_activity_log_notes.="Duplicated driver ".$_GET['duplicate']." to driver ".$dup_id.". ";	
			mrr_add_user_change_log($_SESSION['user_id'],0,$dup_id,0,0,0,0,0,"Updated driver ".$dup_id." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes			
		}
		
		header("Location: admin_drivers.php?id=".$dup_id);
		die;
	}
	
	if(isset($_GET['did'])) {
		$sql = "
			update drivers
			
			set	deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_delete = simple_query($sql);
		$mrr_activity_log_notes.="Deleted driver ".$_GET['did'].". ";			
		mrr_add_user_change_log($_SESSION['user_id'],0,$_GET['did'],0,0,0,0,0,"Deleted driver ".$_GET['did']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
	}
	
	$mrr_driver_id=0;	
	$mrr_emp_id=0;
	$mrr_emp_name='';
	if(!isset($_POST['employer_id']))		$_POST['employer_id']=0;
	if($_POST['employer_id'] > 0)
	{
		$mrr_emp_id=$_POST['employer_id'];
	}
	else
	{	//get default
		$mrr_emp_id=mrr_get_employer_id_by_def('conard');	
	}
	
	$old_employer_id=0;
	if(isset($_GET['id']))
	{
		$sql = "select employer_id from drivers where id='".sql_friendly($_GET['id'])."'";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			$old_employer_id=$row['employer_id'];
		}	
	}	
	
	if(isset($_GET['new'])) {
		$_GET['id']=0;
	}	
	
	$mrr_dir="".$defaultsarray['base_path']."/www/drivers/";
	
	
	if(!isset($_GET['checklist_id']))		$_GET['checklist_id']=0;
     		
	if(isset($_POST['save_checklist']))
	{	//SAVE CHECKLIST FOR DRIVER FORM
		if(!isset($_GET['id']))		$_GET['id']=0;
		
		if($_GET['id'] > 0)	
		{
			if(!isset($_POST['reviewed_user_id']))		$_POST['reviewed_user_id']=0;
			if(!isset($_POST['received_user_id']))		$_POST['received_user_id']=0;
			if(trim($_POST['linedate_reviewed'])=="")	$_POST['linedate_reviewed']="0000-00-00 00:00:00";
			if(trim($_POST['linedate_received'])=="")	$_POST['linedate_received']="0000-00-00 00:00:00";
			
			if(!isset($_POST['master_info_sheet']))		$_POST['master_info_sheet']=0;
			if(!isset($_POST['mandatory_psp']))		$_POST['mandatory_psp']=0;
			if(!isset($_POST['psp_report']))			$_POST['psp_report']=0;
			if(!isset($_POST['driving_record_auth']))	$_POST['driving_record_auth']=0;
			if(!isset($_POST['mvr_report']))			$_POST['mvr_report']=0;
			if(!isset($_POST['employee_application']))	$_POST['employee_application']=0;
			if(!isset($_POST['driver_data_sheet']))		$_POST['driver_data_sheet']=0;
			if(!isset($_POST['amvdcv_done']))			$_POST['amvdcv_done']=0;
			if(!isset($_POST['road_test']))			$_POST['road_test']=0;
			if(!isset($_POST['drug_test_consent']))		$_POST['drug_test_consent']=0;
			if(!isset($_POST['pre_employ_drug_screen']))	$_POST['pre_employ_drug_screen']=0;
			if(!isset($_POST['pre_employ_drug_result']))	$_POST['pre_employ_drug_result']=0;
			if(!isset($_POST['employee_loan']))		$_POST['employee_loan']=0;
			if(!isset($_POST['controlled_sub_abuse']))	$_POST['controlled_sub_abuse']=0;
			if(!isset($_POST['form_1_9']))			$_POST['form_1_9']=0;
			if(!isset($_POST['form_w_4']))			$_POST['form_w_4']=0;
			if(!isset($_POST['new_hire_pay_info']))		$_POST['new_hire_pay_info']=0;
			if(!isset($_POST['wage_deduct_policy']))	$_POST['wage_deduct_policy']=0;
			if(!isset($_POST['direct_deposit']))		$_POST['direct_deposit']=0;
			if(!isset($_POST['aflac_page']))			$_POST['aflac_page']=0;
						
			if(!isset($_POST['med_exam_cert']))		$_POST['med_exam_cert']=0;
			if(!isset($_POST['med_exam_form']))		$_POST['med_exam_form']=0;
			if(!isset($_POST['med_card']))			$_POST['med_card']=0;
			if(trim($_POST['med_card_info'])=="")		$_POST['med_card_info']="0000-00-00 00:00:00";
			if(!isset($_POST['driver_license']))		$_POST['driver_license']=0;
			//$_POST['driver_license_num']="";
			if(!isset($_POST['ssn_copy']))			$_POST['ssn_copy']=0;
			//$_POST['ssn_num']="";
			if(!isset($_POST['driver_point_system']))	$_POST['driver_point_system']=0;
			if(!isset($_POST['camera_acknowledged']))	$_POST['camera_acknowledged']=0;
			if(!isset($_POST['bio_page']))			$_POST['bio_page']=0;
			if(!isset($_POST['driver_safety_policy']))	$_POST['driver_safety_policy']=0;
			if(!isset($_POST['meet_and_greet']))		$_POST['meet_and_greet']=0;
			if(!isset($_POST['fuel_card']))			$_POST['fuel_card']=0;
			if(!isset($_POST['peoplenet_training']))	$_POST['peoplenet_training']=0;
			if(!isset($_POST['phone_list']))			$_POST['phone_list']=0;
			if(!isset($_POST['after_hours']))			$_POST['after_hours']=0;
			if(!isset($_POST['assign_truck']))			$_POST['assign_truck']=0;
			if(!isset($_POST['employee_handbook']))		$_POST['employee_handbook']=0;
			if(!isset($_POST['pre_post_trips']))		$_POST['pre_post_trips']=0;
			
			//$_POST['employer_1']="";
			//$_POST['employer_2']="";
			//$_POST['employer_3']="";
			//$_POST['employer_4']="";			
			if(trim($_POST['emp_1_sent_1'])=="")		$_POST['emp_1_sent_1']="0000-00-00 00:00:00";
			if(trim($_POST['emp_1_sent_2'])=="")		$_POST['emp_1_sent_2']="0000-00-00 00:00:00";
			if(trim($_POST['emp_1_sent_3'])=="")		$_POST['emp_1_sent_3']="0000-00-00 00:00:00";
			if(trim($_POST['emp_1_received'])=="")		$_POST['emp_1_received']="0000-00-00 00:00:00";
			if(trim($_POST['emp_2_sent_1'])=="")		$_POST['emp_2_sent_1']="0000-00-00 00:00:00";
			if(trim($_POST['emp_2_sent_2'])=="")		$_POST['emp_2_sent_2']="0000-00-00 00:00:00";
			if(trim($_POST['emp_2_sent_3'])=="")		$_POST['emp_2_sent_3']="0000-00-00 00:00:00";
			if(trim($_POST['emp_2_received'])=="")		$_POST['emp_2_received']="0000-00-00 00:00:00";
			if(trim($_POST['emp_3_sent_1'])=="")		$_POST['emp_3_sent_1']="0000-00-00 00:00:00";
			if(trim($_POST['emp_3_sent_2'])=="")		$_POST['emp_3_sent_2']="0000-00-00 00:00:00";
			if(trim($_POST['emp_3_sent_3'])=="")		$_POST['emp_3_sent_3']="0000-00-00 00:00:00";
			if(trim($_POST['emp_3_received'])=="")		$_POST['emp_3_received']="0000-00-00 00:00:00";
			if(trim($_POST['emp_4_sent_1'])=="")		$_POST['emp_4_sent_1']="0000-00-00 00:00:00";
			if(trim($_POST['emp_4_sent_2'])=="")		$_POST['emp_4_sent_2']="0000-00-00 00:00:00";
			if(trim($_POST['emp_4_sent_3'])=="")		$_POST['emp_4_sent_3']="0000-00-00 00:00:00";
			if(trim($_POST['emp_4_received'])=="")		$_POST['emp_4_received']="0000-00-00 00:00:00";	
			
			//added on 7/20/2017
			if(!isset($_POST['driver_photo']))			$_POST['driver_photo']=0;
			if(!isset($_POST['driver_door_code']))		$_POST['driver_door_code']=0;
			if(!isset($_POST['driver_box_setup']))		$_POST['driver_box_setup']=0;
			if(!isset($_POST['driver_should_haves']))	$_POST['driver_should_haves']=0;
			if(!isset($_POST['driver_fleet_one']))		$_POST['driver_fleet_one']=0;
			if(!isset($_POST['driver_add_pn']))		$_POST['driver_add_pn']=0;
			if(!isset($_POST['driver_key_tags']))		$_POST['driver_key_tags']=0;
			if(!isset($_POST['driver_speed_space']))	$_POST['driver_speed_space']=0;
						
			$rec_id=mrr_add_driver_dot_checklist($_GET['id'],$_POST);
			//$_GET['checklist_id']=$rec_id;
			
			header("Location: $SCRIPT_NAME?id=".$_GET['id']."");	//". ($rec_id > 0  ? "&checklist_id=".$rec_id."" : "") ."
			die();
		}
	}	
	elseif(isset($_POST['name_driver_last'])) 
	{	//SAVE DRIVER FORM
		
		$use_birthday = '';
		$use_drugtest = '';
		$use_started = '';
		$use_license_expires = '';
		$use_cov_expires = '';
		$use_terminated='';
		$use_review_due='';
		$use_spouse='';
		$use_anniversary='';		
		$use_driver_date='';
		$use_op_ins_due='';
		$misc_cert_expires='';
		
		$use_hired='';							$use_fired='';
		$use_hired2='';						$use_fired2='';
		$use_hired3='';						$use_fired3='';
		$use_hired4='';						$use_fired4='';
		$use_hired5='';						$use_fired5='';
		
		if($_POST['linedate_own_op_ins'] != '') 	$use_op_ins_due = date("Y-m-d", strtotime($_POST['linedate_own_op_ins']));
				
		if($_POST['linedate_started'] != '') 		$use_started = date("Y-m-d", strtotime($_POST['linedate_started']));
		if($_POST['linedate_birthday'] != '') 		$use_birthday = date("Y-m-d", strtotime($_POST['linedate_birthday']));
		if($_POST['linedate_drugtest'] != '') 		$use_drugtest = date("Y-m-d", strtotime($_POST['linedate_drugtest']));
		if($_POST['linedate_license_expires'] != '') $use_license_expires = date("Y-m-d", strtotime($_POST['linedate_license_expires']));
		if($_POST['linedate_cov_expires'] != '') 	$use_cov_expires = date("Y-m-d", strtotime($_POST['linedate_cov_expires']));
		if($_POST['linedate_terminated'] != '') 	$use_terminated = date("Y-m-d", strtotime($_POST['linedate_terminated']));
		if($_POST['linedate_rehire'] != '') 		$use_hired = date("Y-m-d", strtotime($_POST['linedate_rehire']));
		if($_POST['linedate_refire'] != '') 		$use_fired = date("Y-m-d", strtotime($_POST['linedate_refire']));
		
		if($_POST['linedate_rehire2'] != '') 		$use_hired2 = date("Y-m-d", strtotime($_POST['linedate_rehire2']));
		if($_POST['linedate_refire2'] != '') 		$use_fired2 = date("Y-m-d", strtotime($_POST['linedate_refire2']));
		if($_POST['linedate_rehire3'] != '') 		$use_hired3 = date("Y-m-d", strtotime($_POST['linedate_rehire3']));
		if($_POST['linedate_refire3'] != '') 		$use_fired3 = date("Y-m-d", strtotime($_POST['linedate_refire3']));
		if($_POST['linedate_rehire4'] != '') 		$use_hired4 = date("Y-m-d", strtotime($_POST['linedate_rehire4']));
		if($_POST['linedate_refire4'] != '') 		$use_fired4 = date("Y-m-d", strtotime($_POST['linedate_refire4']));
		if($_POST['linedate_rehire5'] != '') 		$use_hired5 = date("Y-m-d", strtotime($_POST['linedate_rehire5']));
		if($_POST['linedate_refire5'] != '') 		$use_fired5 = date("Y-m-d", strtotime($_POST['linedate_refire5']));
		
		if($_POST['linedate_review_due'] != '') 	$use_review_due = date("Y-m-d", strtotime($_POST['linedate_review_due']));
		
		if($_POST['linedate_misc_cert'] != '') 		$misc_cert_expires = date("Y-m-d", strtotime($_POST['linedate_misc_cert']));
		
		
		
				
		if($_POST['linedate_spouse'] != '') 		$use_spouse = date("Y-m-d", strtotime($_POST['linedate_spouse']));
		if($_POST['linedate_anniversary'] != '') 	$use_anniversary = date("Y-m-d", strtotime($_POST['linedate_anniversary']));
		if($_POST['driver_status_date'] != '') 		$use_driver_date = date("Y-m-d", strtotime($_POST['driver_status_date']));	
		
		if(!isset($_POST['head_shot_photo']))		$_POST['head_shot_photo']="";
		
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
				$mrr_msg.="<br><br>File Dest: ".$mrr_dir."driver_photo_".$_GET['id']."".$typer.".";
				
				if(move_uploaded_file ( $_FILES['mrr_import']['tmp_name'] , "".$mrr_dir."driver_photo_".$_GET['id']."".$typer.""))
				{
					$_POST['head_shot_photo']="driver_photo_".$_GET['id']."".$typer."";
					
					$mrr_msg.="<br><br>File Saved.";
				}					
			}		
		}
		if($_GET['id'] == 0)
		{
     		$sql = "
     			insert into drivers
     				(name_driver_last,
     				name_driver_first)
     				
     			values ('Driver',
     				'New')
     		";
     		$data = simple_query($sql);
     		
     		$_GET['id']=mysql_insert_id();
     		
     		//log employer change...initializing
     		$sql2="insert into drivers_employer_change 
     					(id,linedate_added,linedate,driver_id,old_employer_id,new_employer_id,deleted) 
     				values 
     					(NULL,NOW(),'0000-00-00 00:00:00','".sql_friendly($_GET['id'])."',0,0,0)";
     		simple_query($sql2);
     		
			$mrr_activity_log_notes.="Added new driver ".$_GET['id']." info. ";	
			mrr_add_user_change_log($_SESSION['user_id'],0,$_GET['id'],0,0,0,0,0,"Added new driver ".$_GET['id']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		}
		
		if($_POST['pay_per_mile'] == '' ) 			$_POST['pay_per_mile']="0.000";
		if($_POST['pay_per_hour'] == '' ) 			$_POST['pay_per_hour']="0.00";
		if($_POST['charged_per_mile'] == '' ) 		$_POST['charged_per_mile']="0.000";
		if($_POST['charged_per_hour'] == '' ) 		$_POST['charged_per_hour']="0.00";
		if($_POST['pay_per_mile_team'] == '' ) 		$_POST['pay_per_mile_team']="0.000";
		if($_POST['pay_per_hour_team'] == '' ) 		$_POST['pay_per_hour_team']="0.00";
		if($_POST['charged_per_mile_team'] == '' )	$_POST['charged_per_mile_team']="0.000";
		if($_POST['charged_per_hour_team'] == '' )	$_POST['charged_per_hour_team']="0.00";
				
		$add_raise_tracking=1;
		$vaca_days_avail=0;
		$sick_days_avail=0;
		$vaca_days_used=0;
		$sick_days_used=0;
		
		$sqly="
			select * 
			from driver_payroll_changes
			where driver_id = '".sql_friendly($_GET['id'])."'
				and auto_schedule=0
				and deleted=0
			order by linedate desc, linedate_added desc 
			limit 1
		";
		$datay=simple_query($sqly);
		if($rowy=mysqli_fetch_array($datay))
		{
			$add_raise_tracking=0;	
			//if anything is different, mark to save changes in log.
			if(number_format($rowy['single_hour_pay'],2) != number_format($_POST['pay_per_hour']  ,2))				$add_raise_tracking=1;
			if(number_format($rowy['single_mile_pay'],3) != number_format($_POST['pay_per_mile']  ,3))				$add_raise_tracking=1;
			if(number_format($rowy['single_hour_pay_charged'],2) != number_format($_POST['charged_per_hour']  ,2))		$add_raise_tracking=1;
			if(number_format($rowy['single_mile_pay_charged'],3) != number_format($_POST['charged_per_mile']  ,3))		$add_raise_tracking=1;
			if(number_format($rowy['team_hour_pay'],2) != number_format($_POST['pay_per_hour_team']  ,2))				$add_raise_tracking=1;
			if(number_format($rowy['team_mile_pay'],3) != number_format($_POST['pay_per_mile_team']  ,3))				$add_raise_tracking=1;
			if(number_format($rowy['team_hour_pay_charged'],2) != number_format($_POST['charged_per_hour_team']  ,2))	$add_raise_tracking=1;
			if(number_format($rowy['team_mile_pay_charged'],3) != number_format($_POST['charged_per_mile_team']  ,3))	$add_raise_tracking=1;
						
			$vaca_days_avail=$rowy['vaca_days_allowed'];
			$sick_days_avail=$rowy['sick_days_allowed'];
			$vaca_days_used=$rowy['vaca_days_used'];
			$sick_days_used=$rowy['sick_days_used'];			
		}
		if($add_raise_tracking==1)
		{	//something changed from last log (or there was no log), so add the record.
			$sqlu="
				insert into driver_payroll_changes
					(id,
					user_id,
					linedate_added,
					driver_id,				
					linedate,					
					single_hour_pay,
					single_mile_pay,
					single_hour_pay_charged,
					single_mile_pay_charged,
					team_hour_pay,
					team_mile_pay,
					team_hour_pay_charged,
					team_mile_pay_charged,					
					auto_schedule,		
					vaca_days_allowed,
					sick_days_allowed,
					vaca_days_used,
					sick_days_used,
					deleted,
					raise_notes)
				values 
					(NULL,
					'".sql_friendly($_SESSION['user_id'])."',
					NOW(),
					'".sql_friendly($_GET['id'])."',
					NOW(),
					'".sql_friendly($_POST['pay_per_hour'])."',
					'".sql_friendly($_POST['pay_per_mile'])."',
					'".sql_friendly($_POST['charged_per_hour'])."',
					'".sql_friendly($_POST['charged_per_mile'])."',
					'".sql_friendly($_POST['pay_per_hour_team'] )."',
					'".sql_friendly($_POST['pay_per_mile_team'] )."',
					'".sql_friendly($_POST['charged_per_hour_team'] )."',
					'".sql_friendly($_POST['charged_per_mile_team'] )."',
					0,
					'".sql_friendly($vaca_days_avail)."',
					'".sql_friendly($sick_days_avail)."',
					'".sql_friendly($vaca_days_used)."',
					'".sql_friendly($sick_days_used)."',
					0,
					'Payroll change detected on Admin Drivers page.')
			";			
			simple_query($sqlu);				
		}		
		
		$old_status=0;
		$sqlold="
			select driver_status 
			from drivers
			where id = '".sql_friendly($_GET['id'])."'
		";
		$dataold=simple_query($sqlold);
		if($rowold=mysqli_fetch_array($dataold))
		{
			$old_status=$rowold['driver_status'];	
		}
		
		// update in progress 
		$sql = "
			update drivers set
				name_driver_first = '".sql_friendly($_POST['name_driver_first'])."',
				name_driver_last = '".sql_friendly($_POST['name_driver_last'])."',
				
				payroll_first_name='".sql_friendly($_POST['payroll_first_name'])."',
				payroll_last_name='".sql_friendly($_POST['payroll_last_name'])."',
				
				shirt_size='".sql_friendly(trim($_POST['shirt_size']))."',
				
				payroll_bonus_override='".sql_friendly($_POST['payroll_bonus_override'])."',
				
				phone_cell = '".sql_friendly($_POST['phone_cell'])."',
				phone_home = '".sql_friendly($_POST['phone_home'])."',
				phone_other = '".sql_friendly($_POST['phone_other'])."',
				
				preferred_contact='".sql_friendly($_POST['preferred_contact'])."',
				
				emergency_contact_name = '".sql_friendly($_POST['emergency_contact_name'])."',
				emergency_contact_phone = '".sql_friendly($_POST['emergency_contact_phone'])."',
				dl_number = '".sql_friendly($_POST['dl_number'])."',
				dl_state = '".sql_friendly($_POST['dl_state'])."',
				pay_per_mile = '".sql_friendly($_POST['pay_per_mile'])."',
				pay_per_hour = '".sql_friendly($_POST['pay_per_hour'])."',
				charged_per_mile = '".sql_friendly($_POST['charged_per_mile'])."',
				charged_per_hour = '".sql_friendly($_POST['charged_per_hour'])."',
				pay_per_mile_team = '".sql_friendly($_POST['pay_per_mile_team'])."',
				pay_per_hour_team = '".sql_friendly($_POST['pay_per_hour_team'])."',
				charged_per_mile_team = '".sql_friendly($_POST['charged_per_mile_team'])."',
				charged_per_hour_team = '".sql_friendly($_POST['charged_per_hour_team'])."',
				
				overtime_hourly_charged = '".($_POST['overtime_hourly_charged'] == '' ? '0' : sql_friendly($_POST['overtime_hourly_charged']))."',
				overtime_hourly_paid = '".($_POST['overtime_hourly_paid'] == '' ? '0' : sql_friendly($_POST['overtime_hourly_paid']))."',
				
				driver_address_1='".sql_friendly($_POST['driver_address_1'])."',
          		driver_address_2='".sql_friendly($_POST['driver_address_2'])."',
          		driver_city='".sql_friendly($_POST['driver_city'])."',
          		driver_state='".sql_friendly($_POST['driver_state'])."',
          		driver_zip='".sql_friendly($_POST['driver_zip'])."',
          		driver_email='".sql_friendly($_POST['driver_email'])."',
          		
          		driver_no_text_msg='".(isset($_POST['driver_no_text_msg']) ? 1 : 0)."',
				
				own_op_ins_flag='".(isset($_POST['own_op_ins_flag']) ? 1 : 0)."',
				own_op_ins_number='".sql_friendly($_POST['own_op_ins_number'])."',
				linedate_own_op_ins = '".($use_op_ins_due == '' ? '0000-00-00' : sql_friendly($use_op_ins_due))."',
								
				linedate_review_due = '".($use_review_due == '' ? '0000-00-00' : sql_friendly($use_review_due))."',
				linedate_birthday = '".($use_birthday == '' ? '0000-00-00' : sql_friendly($use_birthday))."',
				linedate_drugtest = '".($use_drugtest == '' ? '0000-00-00' : sql_friendly($use_drugtest))."',
				linedate_started = '".($use_started == '' ? '0000-00-00' : sql_friendly($use_started))."',
				linedate_license_expires = '".($use_license_expires == '' ? '0000-00-00' : sql_friendly($use_license_expires))."',
				linedate_cov_expires ='".($use_cov_expires == '' ? '0000-00-00 00:00:00' : sql_friendly($use_cov_expires))."',
				active = '".(isset($_POST['active']) ? 1 : 0)."',
				
				linedate_misc_cert = '".($misc_cert_expires == '' ? '0000-00-00 00:00:00' : sql_friendly($misc_cert_expires))."',
				misc_cert_name = '".sql_friendly(trim($_POST['misc_cert_name']))."',	
				
				geotab_use_id = '".sql_friendly(trim($_POST['geotab_use_id']))."',	
				
				owner_operator	=  '".(isset($_POST['owner_operator']) ? '1' : '0')."',
				fuel_card_number = '".sql_friendly(trim($_POST['fuel_card_number']))."',
				
				hide_available = '".(isset($_POST['hide_available']) ? 1 : 0)."',
				team_driver = '".(isset($_POST['team_driver']) ? 1 : 0)."',
				hazmat = '".(isset($_POST['hazmat']) ? 1 : 0)."',
				tanker_endorsement = '".(isset($_POST['tanker_endorsement']) ? 1 : 0)."',
				jit_driver_flag='".sql_friendly($_POST['jit_driver_flag'])."',
				employer_id = '".sql_friendly($mrr_emp_id)."',
				linedate_terminated ='".($use_terminated == '' ? '0000-00-00 00:00:00' : sql_friendly($use_terminated))."',
				peoplenet_driver_id='".($_POST['peoplenet_driver_id'] == '' ? '0' : sql_friendly( (int)$_POST['peoplenet_driver_id'] ))."',
				
				linedate_rehire='".($use_hired == '' ? '0000-00-00 00:00:00' : sql_friendly($use_hired))."',
				linedate_refire='".($use_fired == '' ? '0000-00-00 00:00:00' : sql_friendly($use_fired))."',
								
				linedate_rehire2='".($use_hired2 == '' ? '0000-00-00 00:00:00' : sql_friendly($use_hired2))."',
				linedate_refire2='".($use_fired2 == '' ? '0000-00-00 00:00:00' : sql_friendly($use_fired2))."',
				linedate_rehire3='".($use_hired3 == '' ? '0000-00-00 00:00:00' : sql_friendly($use_hired3))."',
				linedate_refire3='".($use_fired3 == '' ? '0000-00-00 00:00:00' : sql_friendly($use_fired3))."',
				linedate_rehire4='".($use_hired4 == '' ? '0000-00-00 00:00:00' : sql_friendly($use_hired4))."',
				linedate_refire4='".($use_fired4 == '' ? '0000-00-00 00:00:00' : sql_friendly($use_fired4))."',
				linedate_rehire5='".($use_hired5 == '' ? '0000-00-00 00:00:00' : sql_friendly($use_hired5))."',
				linedate_refire5='".($use_fired5 == '' ? '0000-00-00 00:00:00' : sql_friendly($use_fired5))."',
				
				spouse_name='".sql_friendly($_POST['spouse_name'])."',
				linedate_spouse='".($use_spouse == '' ? '0000-00-00 00:00:00' : sql_friendly($use_spouse))."',
				linedate_anniversary='".($use_anniversary == '' ? '0000-00-00 00:00:00' : sql_friendly($use_anniversary))."',
				
				shuttle_runner='".(isset($_POST['shuttle_runner']) ? 1 : 0)."',
				night_shifter='".(isset($_POST['night_shifter']) ? 1 : 0)."',
				
				driver_smokes='".(isset($_POST['driver_smokes']) ? 1 : 0)."',
				
				driver_status_days='".sql_friendly($_POST['driver_status_days'])."',
				driver_status='".sql_friendly($_POST['driver_status'])."',
				driver_status_date='".($use_driver_date == '' ? '0000-00-00 00:00:00' : sql_friendly($use_driver_date))."',
				driver_status_notes='".sql_friendly($_POST['driver_status_notes'])."',
				
				image_rotation='".sql_friendly($_POST['image_rotation'])."',
				head_shot_photo='".sql_friendly($_POST['head_shot_photo'])."'
				
			where id = '".sql_friendly($_GET['id'])."'
		";
		$data = simple_query($sql);
		
		/*
		if($_POST['attached_truck_id'] > 0)
		{
			//first clear out all the other drivers linked to the selected truck...
			$sqlu="update drivers set attached_truck_id='0' where attached_truck_id='".sql_friendly($_POST['attached_truck_id'])."'";
			simple_query($sqlu);
			
			//next,link the selected truck to this driver...NO DISPATCHES WILL BE CHANGED, ONLY THE DRIVER LINK.			
			$sqlu="update drivers set attached_truck_id='".sql_friendly($_POST['attached_truck_id'])."' where id='".sql_friendly($_GET['id'])."'";
			simple_query($sqlu);
		}
		elseif($_POST['attached_truck_id'] == 0)
		{
			//clear the selected driver to this truck...NO DISPATCHES WILL BE CHANGED, ONLY THE TRUCK LINK.			
			$sqlu="update drivers set attached_truck_id='0' where id='".sql_friendly($_GET['id'])."'";
			simple_query($sqlu);
		}
		
		//second driver
		if($_POST['attached2_truck_id'] > 0)
		{
			//first clear out all the other drivers linked to the selected truck...
			$sqlu="update drivers set attached2_truck_id='0' where attached2_truck_id='".sql_friendly($_POST['attached2_truck_id'])."'";
			simple_query($sqlu);
			
			//next,link the selected truck to this driver...NO DISPATCHES WILL BE CHANGED, ONLY THE DRIVER LINK.			
			$sqlu="update drivers set attached2_truck_id='".sql_friendly($_POST['attached2_truck_id'])."' where id='".sql_friendly($_GET['id'])."'";
			simple_query($sqlu);
		}
		elseif($_POST['attached2_truck_id'] == 0)
		{
			//clear the selected driver to this truck...NO DISPATCHES WILL BE CHANGED, ONLY THE TRUCK LINK.			
			$sqlu="update drivers set attached2_truck_id='0' where id='".sql_friendly($_GET['id'])."'";
			simple_query($sqlu);
		}
		*/
		
		
		//now check if driver status has changed...if so, and appropriate, add to unavailable section....so it displays in Time Off section on Load Board...Added Feb 2016...MRR
		if(($old_status==0 && $_POST['driver_status'] > 0)  || ($old_status > 0 && $_POST['driver_status'] > 0 && $_POST['driver_status']!=$old_status))
		{
			$unavail_reason=option_value_text($_POST['driver_status'],2);
			
			if($use_driver_date == '')
			{
				$starter_date=date("Y-m-d");
				$ender_date=date("Y-m-d");
			}
			else
			{
				$status_days=ceil($_POST['driver_status_days']);
				$starter_date=date("Y-m-d",strtotime($use_driver_date));
				$ender_date=date("Y-m-d",strtotime("+".$status_days." days",strtotime($use_driver_date)));
			}
			
			$sql = "
     			insert into drivers_unavailable
     				(driver_id,
     				deleted,
     				linedate_added,
     				linedate_start,
     				linedate_end,
     				reason_unavailable,
     				recurring_wk_day,
     				added_by)
     				
     			values ('".sql_friendly($_GET['id'])."',
     				0,
     				now(),
     				'".$starter_date." 00:00:00',
     				'".$ender_date." 23:59:59',
     				'".sql_friendly( trim($unavail_reason)).": ".sql_friendly(trim($_POST['driver_status_notes']))."',
     				'0',
     				'".sql_friendly($_SESSION['user_id'])."')
			";
			//simple_query($sql);
		}
		//.......................................................................................................................................................................
		
			
		if($old_employer_id != $mrr_emp_id) 
		{
			$sqlu = "
				insert into drivers_employer_change 
					(id,
					linedate_added,
					linedate,
					driver_id,
					old_employer_id,
					new_employer_id,
					deleted)
				values
					(NULL,
					NOW(),
					NOW(),
					'".sql_friendly($_GET['id'])."',
					'".sql_friendly($old_employer_id)."',
					'".sql_friendly($mrr_emp_id)."',
					0)
				";
			simple_query($sqlu);
		}		
		
		$mrr_activity_log_notes.="Updated driver ".$_GET['id']." info. ";	
		mrr_add_user_change_log($_SESSION['user_id'],0,$_GET['id'],0,0,0,0,0,"Updated driver ".$_GET['id']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		
		header("Location: $SCRIPT_NAME?id=".$_GET['id']."".(isset($_GET['filter_active'])  ? "&filter_active=1" : "") ."");	
		die();
	}
	/*
	if(isset($_GET['new'])) {
		$sql = "
			insert into drivers
				(name_driver_last,
				name_driver_first)
				
			values ('Driver',
				'New')
		";
		$data = simple_query($sql);
		
		$new_id=mysql_insert_id();
		
		//log employer change...initializing
		$sql2="insert into drivers_employer_change 
					(id,linedate_added,linedate,driver_id,old_employer_id,new_employer_id,deleted) 
				values 
					(NULL,NOW(),'0000-00-00 00:00:00','".sql_friendly($new_id)."',0,0,0)";
		simple_query($sql2);
		
		$mrr_activity_log_notes.="Added new driver ".$new_id." info. ";	
		mrr_add_user_change_log($_SESSION['user_id'],0,$new_id,0,0,0,0,0,"Added new driver ".$new_id." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
		
		header("Location: $SCRIPT_NAME?id=".$new_id);
		die();
	}
	*/
	
	if(isset($_GET['filter_active']) && !isset($_POST['filter_active']))		$_POST['filter_active']=0;
	if(!isset($_POST['filter_active']))								$_POST['filter_active']=1;
	
	$sql = "
		select drivers.*,
			(	
			select trucks.name_truck 
			from trucks
			where trucks.id=drivers.attached_truck_id
				and trucks.deleted = 0 
			) as attached_driver_truck,
			(
			select trucks.name_truck 
			from trucks
			where trucks.id=drivers.attached2_truck_id
				and trucks.deleted = 0 
			) as attached2_driver_truck,
			(
			select driver_payroll_changes.linedate 
     		from driver_payroll_changes
     		where driver_payroll_changes.driver_id=drivers.id
     		    and driver_payroll_changes.driver_id>0
     		    and driver_payroll_changes.deleted=0
     		    and driver_payroll_changes.auto_schedule=0
     		order by driver_payroll_changes.linedate desc
     		limit 1) as last_raise_date,			
			
			(select option_values.fname from option_values where option_values.id=drivers.driver_status) as driver_status_option
		from drivers
		where drivers.deleted = 0
			".($_POST['filter_active'] > 0 ? "and drivers.active>0" : "and drivers.active=0")."
		order by drivers.active desc, drivers.name_driver_last, drivers.name_driver_first
	";
	$data = simple_query($sql);
	$mrr_activity_log_notes.="Viewed list of drivers. ";
	
	$show_driver_email_section=$defaultsarray['show_driver_email_section'];

	$usetitle = "Driver Master";
	$use_title = "Driver Master";
	
	$driver_admin_min=95;
	
	$mrr_driver_image="";
	$image_rotation=0;
?>
<? include('header.php') ?>

<table class='admin_menu1' style='text-align:left'>
<tr>
	<td valign='top'>
		<font class='standard18'><b>Driver Master (Admin Drivers)</b></font>
		<br>		
		<br>
		<form action='' method='post'>
		<b>FILTER BY</b> 
		<?
		$dcntr=0;
          $active_bx="";
          $active_bx.="<select name='filter_active' id='filter_active' onChange='submit();'>";
          
          $pre="";		if($_POST['filter_active']==0)		$pre=" selected";	
          $active_bx.="<option value='0'".$pre.">Inactive</option>";
          
          $pre="";		if($_POST['filter_active']==1)		$pre=" selected";	
          $active_bx.="<option value='1'".$pre.">Active</option>";
          
          $active_bx.="</select>";
          echo $active_bx;				
		?>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <a href="<?=$SCRIPT_NAME?>?new=1">Add New Driver</a>
		<br>
		<br>
		</form>
		<table class='standard12 tablesorter'>
		<thead>
		<tr>
			<th>ID &nbsp;&nbsp;&nbsp;</th>
			<th>Driver</th>
			<th>Cell Phone &nbsp;&nbsp;&nbsp;</th>
			<th>Contact</th>
			<th>Employer </th>
			<th>Certs </th>
			<th>Truck</th>
			<th>Last Raise</th>
			<th>Hired </th>
			<th>Left </th>
			<th>Rehired </th>	
			<th>Left </th>			
			<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<? 
		
		$uuid = "_".date("Ymd",time());	//.createuuid();
		$excel_filename = "".($_POST['filter_active']==0 ? "in" : "")."active_driver_email".$uuid.".xls";
		$export_file = "";
		$use_excel=1;
		
		$export_file .= "".($_POST['filter_active']==0 ? "Ina" : "A")."ctive Driver E-mail List".chr(9).
          	"".chr(9).
          	"".chr(9).
          	"".chr(9);
     	$export_file .= chr(13);	
     	
     	$export_file .= "ID".chr(9).
     		"First Name".chr(9).
     		"Last Name".chr(9).
     		"E-Mail Address".chr(9);
     	$export_file .= chr(13);	
		
		while($row = mysqli_fetch_array($data)) 
		{
			$mrr_emp_name=mrr_get_employer_by_id($row['employer_id']);
			
			$date_from=date("m/d/Y");
			$date_to=date("m/d/Y");
			$unavail_absent_vacation="";
			$unavail_absent_vacation=mrr_pull_unavailable_driver_days_and_codes_symbols($date_from,$date_to,$row['id'],0);
			
			$export_file .= "".$row['id']."".chr(9).
     			"".trim($row['name_driver_first'])."".chr(9).
     			"".trim($row['name_driver_last'])."".chr(9).
     			"".trim($row['driver_email'])."".chr(9);
     		$export_file .= chr(13);	
     		
     		if($row['active'] > 0) 	$dcntr++;
		?>
			<tr>
				<td><?=$row['id']?></td>
				<td>
					<a class='<?=($row['active'] ? 'driver_active' : 'driver_inactive')?>' href="<?=$SCRIPT_NAME?>?id=<?=$row['id']?><?=( (int) $row['active'] ==0 ? '&filter_active=1' : '')?>">
						<?=$row['name_driver_last']?>, <?=$row['name_driver_first']?>
					</a>
					<?=( trim($unavail_absent_vacation)!="" ? "<br>".$unavail_absent_vacation."" : "")  ?>
					<?
					if($row['driver_status'] > 0)
					{
                        $mrr_status_driver= "<span class='mrr_unavailable'>";
						
						$dfrom_date=date("m/d/Y",strtotime($row['driver_status_date']));
						$dto_date="";
                        $mrr_to_date="";
						if($row['driver_status_days'] > 1)
						{
							$dto_date=" - ".date("m/d/Y",strtotime("+".$row['driver_status_days']." days",strtotime($row['driver_status_date'])));
                            $mrr_to_date="".date("Y-m-d",strtotime("+".$row['driver_status_days']." days",strtotime($row['driver_status_date'])))."";
						}
                         
                        $mrr_status_driver.= "<br>STATUS: ".trim($row['driver_status_option']).": ".$dfrom_date."".$dto_date."";
						//if(trim($row['driver_status_notes'])!="")		echo "<br>".trim($row['driver_status_notes'])."";
                         
                        $mrr_status_driver.= "</span>";
                        
                        
                        if($mrr_to_date < date("Y-m-d",time()) && $row['driver_status_days'] > 1)
                        {   //this status has expired....do not show it, and clear the values for this driver.  Added 01/05/2023
                            $mrr_status_driver="";
                            
                            //log the status in the history table...
                             mrr_driver_status_history_add($row['id'],$row['driver_status_date'],$row['driver_status'],$row['driver_status_notes'],$row['driver_status_days']); 
                             
                            //now kill thestatus settigns for the next one.     
                            $sqluu="
                                    update drivers set
                                        driver_status=0,
                                        driver_status_notes='',
                                        driver_status_date='0000-00-00 00:00:00',
                                        driver_status_days=0
                                    where id='".$row['id']."'
	                        ";
                            simple_query($sqluu);
                        }
						
						echo $mrr_status_driver;
					}
					?>
				</td>
				<td><?=$row['phone_cell']?></td>
				<td>
					<?
					if($row['preferred_contact']==0)		echo "&nbsp;";
					elseif($row['preferred_contact']==1)	echo "Txt Msg";
					elseif($row['preferred_contact']==2)	echo "E-Mail";					
					?>	
				</td>				
				<td><?=$mrr_emp_name ?></td>
				<td>
					<?=($row['hazmat'] ? 'HAZ' : '')?> &nbsp;
					<?=($row['tanker_endorsement'] ? 'TANK' : '')?>
				</td>
				<td>
					<?=($row['attached_truck_id'] > 0 ? "<a href='admin_trucks.php?id=".$row['attached_truck_id']."' target='_blank'>".trim($row['attached_driver_truck'])."</a> " : '')?>
					<?=($row['attached2_truck_id'] > 0 ? "<a href='admin_trucks.php?id=".$row['attached2_truck_id']."' target='_blank'>".trim($row['attached2_driver_truck'])."</a>" : '')?>
				</td>
				<td>
                     <?                     
                     //echo "".($row['team_driver'] ? 'Team' : '')."";
                     //echo "".($row['attached2_truck_id'] > 0 && $row['team_driver']==0 ? "(Team)" : '')."";
                     
                     $last_raise_date=$row['last_raise_date'];
                     if(isset($last_raise_date) && $last_raise_date!="0000-00-00 00:00:00" && $last_raise_date > 0) 
                     {
                          echo "".date("Y-m-d", strtotime($last_raise_date))."";
                     }
                     else
                     {
                         echo "N/A";
                     }
                     ?>
				</td>
				<td><?=(strtotime($row['linedate_started']) != 0  ? "".date("Y-m-d", strtotime($row['linedate_started'])).""  : "N/A") ?></td>
				<td><?=(strtotime($row['linedate_terminated']) != 0  ? "".date("Y-m-d", strtotime($row['linedate_terminated'])).""  : "N/A") ?></td>
				<td><?=(strtotime($row['linedate_rehire']) != 0  ? "".date("Y-m-d", strtotime($row['linedate_rehire'])).""  : "N/A") ?></td>
				<td><?=(strtotime($row['linedate_refire']) != 0  ? "".date("Y-m-d", strtotime($row['linedate_refire'])).""  : "N/A") ?></td>
				<?
				//date("m/d/Y", strtotime($row_edit['linedate_terminated']))
				//date("m/d/Y", strtotime($row_edit['linedate_refire']))
				?>
				<td><a href="javascript:confirm_delete(<?=$row['id']?>)" class='mrr_delete_access'><img src="images/delete_sm.gif" border="0"></a></td>
			</tr>
		<? 
		}
		$prefix="<br><center><a href=\"admin_drivers.php\">Reset Driver selection to get the Excel Driver E-Mail List</a><center><br>";
		if($use_excel > 0 && !isset($_GET['id']) ) 
		{
			$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
			fwrite($fp, $export_file); 
			fclose($fp);
			
			$prefix="<br><center><a href=\"/temp/".$excel_filename."\" target='_blank'>Click for Excel Driver E-Mail List</a><center><br>";			
		} 
		?>
		</tbody>
		</table>
		<? 
			echo $prefix;
		?>
		<? if(!isset($_GET['id'])) { ?>
			<div class='clear'></div>
			<h3><b>Add a note to all <?=$dcntr ?> Active Drivers:</b></h3>
			<div id='note_section'></div>
			<div class='clear'></div>			
		<? } ?>		
		<? 
		//MASS EMAIL SENDER...OR SEND TO CURRENT DRIVER SELECTED............ADDED 3/30/2017...MRR
		$dnum=0;
		$dids[0]=0;
		$dfnm[0]="";
		$dlnm[0]="";
		$deml[0]="";
		$get_id=0;
		if(isset($_GET['id']) && $_GET['id'] > 0) 
		{ 
			$sqld = "
				select id, name_driver_first, name_driver_last, driver_email				
				from drivers
				where id = '".sql_friendly($_GET['id'])."'
			";
			$datad = simple_query($sqld);
			if($rowd = mysqli_fetch_array($datad))
			{
				$dids[$dnum]=$rowd['id'];
				$dfnm[$dnum]=trim($rowd['name_driver_first']);
				$dlnm[$dnum]=trim($rowd['name_driver_last']);
				$deml[$dnum]=trim($rowd['driver_email']);
				$dnum++;
			}
			$get_id=$_GET['id'];
		}
		else
		{
			$sqld = "
				select id, name_driver_first, name_driver_last, driver_email				
				from drivers
				where deleted=0 
					and active>0 
					and driver_email!=''
				order by name_driver_last asc, name_driver_first asc, driver_email asc
			";
			$datad = simple_query($sqld);
			while($rowd = mysqli_fetch_array($datad))
			{
				$dids[$dnum]=$rowd['id'];
				$dfnm[$dnum]=trim($rowd['name_driver_first']);
				$dlnm[$dnum]=trim($rowd['name_driver_last']);
				$deml[$dnum]=trim($rowd['driver_email']);
				$dnum++;
			}
		}	
		
		$dlister="";
		$dlister.="<div style='width:650px; height:100px; max-height:100px; padding:10px; border:1px solid #000000; overflow:auto;'>";
		for($d=0;$d < $dnum; $d++)
		{
			$dlister.="".($d+1).". ".trim( $dfnm[$d]." ".$dlnm[$d] )." [<b>".$deml[$d]."</b>]<br>";
		}
		$dlister.="</div>";
		
		$form="<br><h3>Send Email Message to Driver".($get_id==0 ? "s'" : "'s")." Email</h3><br>";
		
		$form.="<div style='width:100%; padding:10px;'>";
		$form.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
		$form.="<tr>";
		$form.=	"<td valign='top'>Sending To:</td>";
		$form.=	"<td valign='top'><input type='hidden' name='gen_driver_id' id='gen_driver_id' value='".$get_id."'>".$dlister."</td>";
		$form.="</tr>";
		$form.="<tr>";
		$form.=	"<td valign='top'>&nbsp;</td>";
		$form.=	"<td valign='top'><b>Select a driver from the table above to send only to that driver.</b></td>";
		$form.="</tr>";
		$form.="<tr>";
		$form.=	"<td valign='top'>Subject:</td>";
		$form.=	"<td valign='top'><input type='text' name='gen_driver_sub' id='gen_driver_sub' value=\"\" style='width:650px;'></td>";
		$form.="</tr>";		
		$form.="<tr>";
		$form.=	"<td valign='top'>Message:</td>";
		$form.=	"<td valign='top'><textarea tabindex='9' name='gen_driver_email' id='gen_driver_email' wrap='virtual' rows='10' cols='80'></textarea></td>";
		$form.="</tr>";
		$form.="<tr>";
		$form.=	"<td valign='top'><input type='button' value='Send Email' onClick='mrr_send_mass_email_drivers();'></td>";
		$form.=	"<td valign='top'><span id='gen_driver_msg'></span></td>";
		$form.="</tr>";
		$form.="</table>";
		$form.="</div>";
		
		$limit=10;
		$form.="<br><h3>Most Recent ".$limit." Email Message Log</h3><br>";
		
		$form.="<div style='width:100%; padding:10px;'>";
		$form.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
		$form.="<tr>";
		$form.=	"<td valign='top'><b>Sent</b></td>";
		$form.=	"<td valign='top'><b>By</b></td>";
		$form.=	"<td valign='top'><b>To</b></td>";
		$form.=	"<td valign='top'><b>Email</b></td>";
		$form.="</tr>";
		
		$sqld="
			select driver_email_log.*,
				(select username from users where users.id=driver_email_log.user_id) as user_name,
				(select CONCAT(name_driver_first, ' ', name_driver_last) from drivers where drivers.id=driver_email_log.driver_id) as driver_name
			from driver_email_log
			where driver_email_log.deleted=0
				and driver_email_log.driver_id='".sql_friendly($get_id)."'
			order by driver_email_log.linedate_added desc
			limit ".(int) $limit."
			";
		$datad=simple_query($sqld);
		while($rowd = mysqli_fetch_array($datad))
		{
			$eml_disp=trim($rowd['sent_list']);
			$eml_disp=str_replace($rowd['driver_name'],"",$eml_disp);
			$eml_disp=str_replace("[","",$eml_disp);
			$eml_disp=str_replace("]","",$eml_disp);
			
			$form.="<tr>";
			$form.=	"<td valign='top' nowrap>".date("m/d/Y H:i",strtotime($rowd['linedate_added']))."</td>";
			$form.=	"<td valign='top'>".$rowd['user_name']."</td>";
			$form.=	"<td valign='top'>".$rowd['driver_name']."</td>";
			$form.=	"<td valign='top'>".trim($eml_disp)."</td>";
			$form.="</tr>";
			//$form.="<tr>";
			//$form.=	"<td valign='top'>&nbsp;</td>";
			//$form.=	"<td valign='top' colspan='3'>".$rowd['subject']."</td>";
			//$form.="</tr>";
			$form.="<tr>";
			$form.=	"<td valign='top'>&nbsp;</td>";
			$form.=	"<td valign='top' colspan='3'>".$rowd['body']."</td>";
			$form.="</tr>";
			$form.="<tr>";
			$form.=	"<td valign='top'>&nbsp;</td>";
			$form.=	"<td valign='top' colspan='2'><hr></td>";
			$form.=	"<td valign='top'>&nbsp;</td>";
			$form.="</tr>";
		}
		$form.="</table>";
		$form.="</div>";
		
		if($show_driver_email_section > 0)		echo $form;		
		//..........................................................................................
		?>		
	</td>
	<td valign='top' style='width:350px'>
		<? 
		if(isset($_GET['id'])) 
		{ 
			?>
			<form action="<?=$SCRIPT_NAME?>?id=<?=$_GET['id']?><?=(isset($_GET['filter_active'])  ? "&filter_active=1" : "") ?>" enctype="multipart/form-data" method="post" onsubmit='if(!check_driver_form()) return false;'>
				<input type='hidden' name='filter_active' id='filter_active' value='<?=$_POST['filter_active'] ?>'>
			<table class='admin_menu2'>
			<?
				$mrr_driver_id=$_GET['id'];
				$sql = "
					select *
					
					from drivers
					where id = '".sql_friendly($_GET['id'])."'
				";
				$data_edit = simple_query($sql);
				$row_edit = mysqli_fetch_array($data_edit);
				$cur_driver_attched=$row_edit['attached_truck_id'];
				$cur_driver_attched2=$row_edit['attached2_truck_id'];
				
				// grab our list of trucks that are currently being used
               	$sqlt = "
               		select distinct truck_id
               		
               		from trucks, trucks_log
               		where trucks.id = trucks_log.truck_id
               			and trucks_log.deleted = 0
               			and trucks_log.dispatch_completed = 0
               	";
               	$data_trucks_used = simple_query($sqlt);	
               	
               	// build an array of truck IDs that are currently being used so we can quickly search them later
				$trucks_array = array();
				while($row_trucks_used = mysqli_fetch_array($data_trucks_used)) {
					$trucks_array[] = $row_trucks_used['truck_id'];
				}	
				
				//get all trucks...
     			$dselector="<select name='attached_truck_id'>";	
     			$sel="";
     			if($cur_driver_attched==0)	$sel=" selected";
     				
     			$dselector.="<option value='0'".$sel.">No Truck</option>";	     			
     					
     			$sql_trucks="
     				select *,
     					(select t.name_truck from equipment_history eh, trucks t where eh.equipment_id = t.id and eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_name
     				from trucks 
     				where active>0 
     					and deleted=0 
     					and in_the_shop=0
     				order by name_truck asc";
     			$data_trucks = simple_query($sql_trucks);
     			while($row_trucks = mysqli_fetch_array($data_trucks))
     			{
     				if(!in_array($row_trucks['id'], $trucks_array) || $cur_driver_attched==$row_trucks['id'])
     				{
     					$sel="";
          				if($row_trucks['id']==$cur_driver_attched)	$sel=" selected";
          				
          				$dselector.="<option value='".$row_trucks['id']."'".$sel.">".$row_trucks['name_truck']."</option>";	
     				}
     			}
     			$dselector.="</select>";	 			
     			
     			
     			//second driver
     			$dselector2="<select name='attached2_truck_id'>";	
     			$sel="";
     			if($cur_driver_attched2==0)	$sel=" selected";
     				
     			$dselector2.="<option value='0'".$sel.">None</option>";	     			
     					
     			$sql_trucks="
     				select *,
     					(select t.name_truck from equipment_history eh, trucks t where eh.equipment_id = t.id and eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_name
     				from trucks 
     				where active>0 
     					and deleted=0 
     					and in_the_shop=0
     				order by name_truck asc";
     			$data_trucks = simple_query($sql_trucks);
     			while($row_trucks = mysqli_fetch_array($data_trucks))
     			{
     				//if(!in_array($row_trucks['id'], $trucks_array) || $cur_driver_attched2==$row_trucks['id'])
     				//{
     					$sel="";
          				if($row_trucks['id']==$cur_driver_attched2)	$sel=" selected";
          				
          				$dselector2.="<option value='".$row_trucks['id']."'".$sel.">".$row_trucks['name_truck']."</option>";	
     				//}
     			}
     			$dselector2.="</select>";	 
     			
     			
     			
				
				$mrr_activity_log_notes.="View driver ".$_GET['id']." info. ";
				$mrr_activity_log_driver=$_GET['id'];
				
				if(isset($_POST['name_driver_last'])) {
					echo "<tr><td colspan='2'><font color='red'><b>Update Successful</b></font></td></tr>";
				}
				
				//get select box
                    $sql="
                    	select option_values.id as use_val,
                    		option_values.fvalue as use_disp
                    	from option_values,option_cat
                    	where option_values.deleted=0
                    		and option_cat.id=option_values.cat_id
                    		and option_cat.cat_name='employer_list'
                    	order by option_values.fvalue asc
                    ";
                    $mrr_emp_id=$row_edit['employer_id'];	//get selected on to show in box
                    $emp_box=mrr_select_box_disp($sql,'employer_id',$mrr_emp_id,'Choose Employer',''); 		// style="width:300px;"
                    
                    //get select box
                    $sqld="
                    	select option_values.id as use_val,
                    		option_values.fvalue as use_disp
                    	from option_values,option_cat
                    	where option_values.deleted=0
                    		and option_cat.id=option_values.cat_id
                    		and option_cat.cat_name='driver_status'
                    	order by option_values.fvalue asc
                    ";
                    $mrr_status_id=$row_edit['driver_status'];	//get selected on to show in box
                    $status_box=mrr_select_box_disp($sqld,'driver_status',$mrr_status_id,'Optional Status',''); 		// style="width:300px;"
			?>
			<tr>
				<td><b>Driver ID</b> <?= show_help('admin_drivers.php','id') ?></td>
				<td><?=$_GET['id']?></td>
				<td rowspan='3' align='right'>
					<?
					if($_GET['id'] > 0 && 1==2)
					{
						echo "
							<div class='toolbar_button_mrr' onclick='duplicate_driver(".$_GET['id'].");'>
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
				<td><label for="active"><b>Active</b></label> <?= show_help('admin_drivers.php','active') ?></td>
				<td><input type='checkbox' id="active" name="active" <? if($row_edit['active']) echo "checked"?>></td>
			</tr>
			<tr>
				<td><label for="hide_available"><b>Hide Available</b></label> <?= show_help('admin_drivers.php','hide_available') ?></td>
				<td><input type='checkbox' id="hide_available" name="hide_available" <? if($row_edit['hide_available']) echo "checked"?>></td>
			</tr>
			<tr>
				<td><b>Driver's First Name</b> <?= show_help('admin_drivers.php','name_driver_first') ?></td>
				<td colspan='2'><input name="name_driver_first" id="name_driver_first" value="<?=$row_edit['name_driver_first']?>" onMouseOut='mrr_verify_user_unique(<?=$_GET['id']?>);'> <span id='mrr_naming_message'></span></td>
			</tr>
			<tr>
				<td><b>Driver's Last Name</b> <?= show_help('admin_drivers.php','name_driver_last') ?></td>
				<td colspan='2'><input name="name_driver_last" id="name_driver_last" value="<?=$row_edit['name_driver_last']?>" onMouseOut='mrr_verify_user_unique(<?=$_GET['id']?>);'></td>
			</tr>
			
			<tr>
				<td><b>Driver's Address 1</b> <?= show_help('admin_drivers.php','driver_address_1') ?></td>
				<td colspan='2'><input name="driver_address_1" id="driver_address_1" value="<?=$row_edit['driver_address_1']?>" style='width:200px;'></td>
			</tr>
			<tr>
				<td><b>Driver's Address 2</b> <?= show_help('admin_drivers.php','driver_address_2') ?></td>
				<td colspan='2'><input name="driver_address_2" id="driver_address_2" value="<?=$row_edit['driver_address_2']?>" style='width:200px;'></td>
			</tr>
			<tr>
				<td><b>Driver's City</b> <?= show_help('admin_drivers.php','driver_city') ?></td>
				<td colspan='2'><input name="driver_city" id="driver_city" value="<?=$row_edit['driver_city']?>" style='width:200px;'></td>
			</tr>
			<tr>
				<td><b>Driver's State</b> <?= show_help('admin_drivers.php','driver_state') ?></td>
				<td colspan='2'><input name="driver_state" id="driver_state" value="<?=$row_edit['driver_state']?>" style='width:50px;'></td>
			</tr>
			<tr>
				<td><b>Driver's Zip</b> <?= show_help('admin_drivers.php','driver_zip') ?></td>
				<td colspan='2'><input name="driver_zip" id="driver_zip" value="<?=$row_edit['driver_zip']?>" style='width:100px;'></td>
			</tr>
			<tr>
				<td><b>Driver's E-Mail</b> <?= show_help('admin_drivers.php','driver_email') ?></td>
				<td colspan='2'><input name="driver_email" id="" value="<?=$row_edit['driver_email']?>" style='width:200px;'></td>
			</tr>
			
			<tr>
				<td><label for='owner_operator'><b>Owner Operator/I.C.</b></label> <?= show_help('admin_drivers.php','Owner Operator/I.C') ?></td>
				<td>
					<input type='checkbox' name='owner_operator' id='owner_operator' <? if($row_edit['owner_operator']) echo 'checked'?>>
					<!--<i>If checked, please fill Insurance information.</i>--->
				</td>
			</tr>
			<tr>
				<td><!---<label for='own_op_ins_flag'><b>Has O.O. Insurance</b></label> <?= show_help('admin_drivers.php','Owner Operator Insurance') ?> ---></td>
				<td colspan='2'>
					<!--
					<input type='checkbox' name='own_op_ins_flag' id='own_op_ins_flag' <? if($row_edit['own_op_ins_flag']) echo 'checked'?>>
					
					Starts 
					<input name="linedate_own_op_ins" id='linedate_own_op_ins' value="<? if(strtotime($row_edit['linedate_own_op_ins']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_own_op_ins'])) ?>"> (mm/dd/yyyy)
					--->
					<input type='hidden' name="own_op_ins_number" id="own_op_ins_number" value="<?=$row_edit['own_op_ins_number']?>">	<!--  style='width:200px;' -->
					
					<input type='hidden' name="own_op_ins_flag" id="own_op_ins_flag" value="<?=$row_edit['own_op_ins_flag']?>">	
					<input type='hidden' name="linedate_own_op_ins" id="linedate_own_op_ins" value="<?=$row_edit['linedate_own_op_ins']?>">	
					
				</td>
			</tr>
			
			<tr>
				<td><label for="team_driver"><b>Team Driver</b></label> <?= show_help('admin_drivers.php','team_drivers') ?></td>
				<td colspan='2'><input type='checkbox' id="team_driver" name="team_driver" <? if($row_edit['team_driver']) echo "checked"?>></td>
			</tr>
			<tr>
				<td colspan='3'>
					&nbsp;
					<input type='hidden' name='attached_truck_id' id='attached_truck_id' value='<?=$cur_driver_attched ?>'>
					<input type='hidden' name='attached2_truck_id' id='attached2_truck_id' value='<?=$cur_driver_attched2 ?>'>
				</td>
			</tr>
			<tr>
				<td><label for="driver_smokes"><b>Driver Smokes</b></label> <?= show_help('admin_drivers.php','driver_smokes') ?></td>
				<td><input type='checkbox' id="driver_smokes" name="driver_smokes" value='1' <? if($row_edit['driver_smokes'] > 0) echo "checked"?>> </td>
			</tr>
			
			<tr>
				<td><label for="shuttle_runner"><b>Shuttle Runs Only</b></label> <?= show_help('admin_drivers.php','shuttle_runner') ?></td>
				<td><input type='checkbox' id="shuttle_runner" name="shuttle_runner" value='1' <? if($row_edit['shuttle_runner'] > 0) echo "checked"?>></td>
			</tr>
			<tr>
				<td><label for="night_shifter"><b>Night Shift Only</b></label> <?= show_help('admin_drivers.php','night_shifter') ?></td>
				<td><input type='checkbox' id="night_shifter" name="night_shifter" value='1' <? if($row_edit['night_shifter'] > 0) echo "checked"?>></td>
			</tr>
			<!--
			<tr>
				<td><b>Attached Truck</b> <?= show_help('admin_drivers.php','Attached Truck') ?></td>
				<td colspan='2'><?=$dselector ?></td>
			</tr>
			<tr>
				<td><b>Attached Truck (as 2nd Driver)</b> <?= show_help('admin_drivers.php','2nd Driver Attached Truck') ?></td>
				<td colspan='2'><?=$dselector2 ?></td>
			</tr>
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>
			-->
			<tr>
				<td><b>Employer</b> <?= show_help('admin_drivers.php','employer_id') ?></td>
				<td colspan='2'><?= $emp_box ?></td>
			</tr>
			<tr>
				<td><b>Cell Phone</b> <?= show_help('admin_drivers.php','phone_cell') ?></td>
				<td colspan='2'>
					<input name="phone_cell" value="<?=$row_edit['phone_cell']?>">
										
					<label>
					No Txt Msg <input type='checkbox' name='driver_no_text_msg' id='driver_no_text_msg' <? if($row_edit['driver_no_text_msg']) echo 'checked'?>>	
					</label>
				</td>
			</tr>
			<tr>
				<td><b>Home Phone</b> <?= show_help('admin_drivers.php','phone_home') ?></td>
				<td colspan='2'><input name="phone_home" value="<?=$row_edit['phone_home']?>"></td>
			</tr>
			<tr>
				<td><b>Phone (other)</b> <?= show_help('admin_drivers.php','phone_other') ?></td>
				<td colspan='2'><input name="phone_other" value="<?=$row_edit['phone_other']?>"></td>
			</tr>
			<tr>
				<td><b>Preferred Contact</b> <?= show_help('admin_drivers.php','preferred_contact') ?></td>
				<td colspan='2'>					
					<select name='preferred_contact' id='preferred_contact'>
						<option value='0' <?=($row_edit['preferred_contact'] == 0 ? "selected" : "")?>>None</option>
						<option value='1' <?=($row_edit['preferred_contact'] == 1 ? "selected" : "")?>>Text Message</option>
						<option value='2' <?=($row_edit['preferred_contact'] == 2 ? "selected" : "")?>>E-Mail Message</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><b>Emergency Contact (Name)</b> <?= show_help('admin_drivers.php','emergency_contact_name') ?></td>
				<td colspan='2'><input name="emergency_contact_name" value="<?=$row_edit['emergency_contact_name']?>"></td>
			</tr>
			<tr>
				<td><b>Emergency Contact (Phone)</b> <?= show_help('admin_drivers.php','emergency_contact_phone') ?></td>
				<td colspan='2'><input name="emergency_contact_phone" value="<?=$row_edit['emergency_contact_phone']?>"></td>
			</tr>
            <tr>
                <td colspan='3'>&nbsp;</td>
            </tr>
            <tr>
                <td><b>Shirt Size</b> <?= show_help('admin_drivers.php','Shirt Size') ?></td>
                <td colspan='2'>
                    <select name='shirt_size' id='shirt_size'>
                        <option value='' <?=($row_edit['shirt_size'] == "" ? "selected" : "")?>> - </option>
                        <option value='xs' <?=($row_edit['shirt_size'] == "xs" ? "selected" : "")?>>X-Small</option>
                        <option value='s' <?=($row_edit['shirt_size'] == "s" ? "selected" : "")?>>Small</option>
                        <option value='m' <?=($row_edit['shirt_size'] == "m" ? "selected" : "")?>>Medium</option>
                        <option value='l' <?=($row_edit['shirt_size'] == "l" ? "selected" : "")?>>Large</option>
                        <option value='xl' <?=($row_edit['shirt_size'] == "xl" ? "selected" : "")?>>X-Large</option>
                        <option value='xxl' <?=($row_edit['shirt_size'] == "xxl" ? "selected" : "")?>>2X-Large</option>
                        <option value='xxxl' <?=($row_edit['shirt_size'] == "xxxl" ? "selected" : "")?>>3X-Large</option>
                        <option value='xxxxl' <?=($row_edit['shirt_size'] == "xxxxl" ? "selected" : "")?>>4X-Large</option>
                        <option value='xxxxxl' <?=($row_edit['shirt_size'] == "xxxxxl" ? "selected" : "")?>>5X-Large</option>
                        <option value='xxxxxxl' <?=($row_edit['shirt_size'] == "xxxxxxl" ? "selected" : "")?>>6X-Large</option>
                    </select>
                </td>
            </tr>    
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>
			<tr>
				<td><b>Spouse's Name</b> <?= show_help('admin_drivers.php','Spouse Name') ?></td>
				<td colspan='2'><input name="spouse_name" value="<?=$row_edit['spouse_name']?>"></td>
			</tr>
			<tr>
				<td><b>Spouse's Birthday</b> <?= show_help('admin_drivers.php','Spouse Birthday') ?></td>
				<td colspan='2'><input name="linedate_spouse" id='linedate_spouse' value="<? if(strtotime($row_edit['linedate_spouse']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_spouse'])) ?>"> (mm/dd/yyyy)</td>
			</tr>
			<tr>
				<td><b>Wedding Anniversary</b> <?= show_help('admin_drivers.php','Wedding Anniversary') ?></td>
				<td colspan='2'><input name="linedate_anniversary" id='linedate_anniversary' value="<? if(strtotime($row_edit['linedate_anniversary']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_anniversary'])) ?>"> (mm/dd/yyyy)</td>
			</tr>
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>		
			<tr>
				<td></td>
				<td colspan='2'>
					<label style='width:60px;border:0px black solid;float:left;display:block'>Single</label>
					<label style='width:60px;border:0px black solid;float:left;display:block;margin-left:10px'>Team</label>
					<label style='width:60px;border:0px black solid;float:left;display:block;margin-left:10px'>Overtime</label>
				</td>
			</tr>
			<tr>
				<td><b>Driver Pay per hour</b> <?= show_help('admin_drivers.php','pay_per_hour') ?></td>
				<td colspan='2'>
					<input name="pay_per_hour" value="<?=$row_edit['pay_per_hour']?>" size='5'>
					<input name="pay_per_hour_team" value="<?=$row_edit['pay_per_hour_team']?>" size='5'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input name="overtime_hourly_paid" value="<?=$row_edit['overtime_hourly_paid']?>" size='5'>
				</td>
			</tr>
			<tr>
				<td><b>Driver Pay per mile</b> <?= show_help('admin_drivers.php','pay_per_mile') ?></td>
				<td colspan='2'>
					<input name="pay_per_mile" value="<?=$row_edit['pay_per_mile']?>" size='5'>
					<input name="pay_per_mile_team" value="<?=$row_edit['pay_per_mile_team']?>" size='5'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
			</tr>

			<tr>
				<td><b>Charged per hour</b> <?= show_help('admin_drivers.php','charged_per_hour') ?></td>
				<td colspan='2'>
					<input name="charged_per_hour" value="<?=$row_edit['charged_per_hour']?>" size='5'>
					<input name="charged_per_hour_team" value="<?=$row_edit['charged_per_hour_team']?>" size='5'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input name="overtime_hourly_charged" value="<?=$row_edit['overtime_hourly_charged']?>" size='5'>
				</td>
			</tr>
			<tr>
				<td><b>Charged per mile</b> <?= show_help('admin_drivers.php','charged_per_mile') ?></td>
				<td colspan='2'>
					<input name="charged_per_mile" value="<?=$row_edit['charged_per_mile']?>" size='5'>
					<input name="charged_per_mile_team" value="<?=$row_edit['charged_per_mile_team']?>" size='5'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>
			<tr>
				<td><b>Date of Birth</b> <?= show_help('admin_drivers.php','linedate_birthday') ?></td>
				<td colspan='2'>
					<input name="linedate_birthday" id='linedate_birthday' value="<? if(strtotime($row_edit['linedate_birthday']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_birthday'])) ?>"> (mm/dd/yyyy)
				</td>
			</tr>
			<tr>
				<td nowrap><b>Expiration Date of Medical Card</b> <?= show_help('admin_drivers.php','linedate_drugtest') ?></td>
				<td colspan='2'><input name="linedate_drugtest" id='linedate_drugtest' value="<? if(strtotime($row_edit['linedate_drugtest']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_drugtest'])) ?>"> (mm/dd/yyyy)</td>
			</tr>
			<tr>
				<td nowrap><b>Expiration Date of MVR</b> <?= show_help('admin_drivers.php','linedate_cov_expires') ?></td>
				<td colspan='2'><input name="linedate_cov_expires" id='linedate_cov_expires' value="<? if(strtotime($row_edit['linedate_cov_expires']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_cov_expires'])) ?>"> (mm/dd/yyyy)</td>
			</tr>
			
			<tr>
				<td><b>Review Due Date</b> <?= show_help('admin_drivers.php','linedate_review_due') ?></td>
				<td colspan='2'><input name="linedate_review_due" id='linedate_review_due' value="<? if(strtotime($row_edit['linedate_review_due']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_review_due'])) ?>"> (mm/dd/yyyy)</td>
			</tr>
			
			
			<tr>
				<td nowrap><b>Misc. Item Name</b> <?= show_help('admin_drivers.php','misc_cert_name') ?></td>
				<td colspan='2'>
					<input name="misc_cert_name" id='misc_cert_name' value="<?= trim($row_edit['misc_cert_name']) ?>" style='width:200px;'>
				</td>
			</tr>
			<tr>
				<td nowrap><b>Misc. Item Expires</b> <?= show_help('admin_drivers.php','linedate_misc_cert') ?></td>
				<td colspan='2'>
					<input name="linedate_misc_cert" id='linedate_misc_cert' value="<? if(strtotime($row_edit['linedate_misc_cert']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_misc_cert'])) ?>"> (mm/dd/yyyy)
				</td>
			</tr>
			
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>
			<tr>
				<td><b>Driver Status</b> <?= show_help('admin_drivers.php','driver_status') ?></td>
				<td colspan='2'><?=$status_box ?></td>
			</tr>
			<tr>
				<td><b>Driver Status Note</b> <?= show_help('admin_drivers.php','driver_status_notes') ?></td>
				<td colspan='2'><input name="driver_status_notes" id='driver_status_notes' value="<?= trim($row_edit['driver_status_notes']) ?>" style='width:200px;'></td>
			</tr>
			<tr>
				<td><b>Driver Status Begin</b> <?= show_help('admin_drivers.php','driver_status_date') ?></td>
				<td colspan='2'><input name="driver_status_date" id='driver_status_date' value="<? if(strtotime($row_edit['driver_status_date']) != 0) echo date("m/d/Y", strtotime($row_edit['driver_status_date'])) ?>"> (mm/dd/yyyy)</td>
			</tr>	
			<tr>
				<td><b>Driver Status Days</b> <?= show_help('admin_drivers.php','driver_status_days') ?></td>
				<td colspan='2'><input name="driver_status_days" id='driver_status_days' value="<?= (int) $row_edit['driver_status_days'] ?>" style='width:60px; text-align:right;'>
					 <?
					 if($row_edit['driver_status'] > 0)
					 {
					 	$calc_days=(int) $row_edit['driver_status_days'];
						$calc_end_date=date("m/d/Y",strtotime("+".$calc_days." days",strtotime($row_edit['driver_status_date']))); 
						
						echo " Ends <b>".$calc_end_date."</b>";
					 }
					 ?>
				</td>
			</tr>
            <tr>
                <td colspan='3'>
                    <b>Driver Status History:</b>
                     <?
                     if((int) $_GET['id'] > 0) 
                     {
                          echo mrr_driver_status_history_list((int)$_GET['id'], 0);
                     }
                     ?>   
                </td>
            </tr>                     
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>
			<tr>
				<td><b>Date of Hire</b> <?= show_help('admin_drivers.php','linedate_started') ?></td>
				<td colspan='2'><input name="linedate_started" id='linedate_started' value="<? if(strtotime($row_edit['linedate_started']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_started'])) ?>" onChange='mrr_act_driver(0);'> (mm/dd/yyyy)</td>
			</tr>
			<tr>
				<td><b>Termination Date</b> <?= show_help('admin_drivers.php','linedate_terminated') ?></td>
				<td colspan='2'><input name="linedate_terminated" id='linedate_terminated' value="<? if(strtotime($row_edit['linedate_terminated']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_terminated'])) ?>" onChange='mrr_deact_driver(0);'> (mm/dd/yyyy)</td>
			</tr>
			
			<tr>
				<td><b>Re-Hire Date</b> <?= show_help('admin_drivers.php','linedate_rehire') ?></td>
				<td colspan='2'><input name="linedate_rehire" id='linedate_rehire' value="<? if(strtotime($row_edit['linedate_rehire']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_rehire'])) ?>" onChange='mrr_act_driver(1);'> (mm/dd/yyyy)</td>
			</tr>
			<tr>
				<td><b>Re-Leave Date</b> <?= show_help('admin_drivers.php','linedate_refire') ?></td>
				<td colspan='2'><input name="linedate_refire" id='linedate_refire' value="<? if(strtotime($row_edit['linedate_refire']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_refire'])) ?>" onChange='mrr_deact_driver(1);'> (mm/dd/yyyy)</td>
			</tr>
			
			<tr>
				<td colspan='3' align='center'><span class='mrr_link_like_on' onClick='mrr_toggle_hire_fire();'><b>More...</b></span></td>
			</tr>
			
			<tr class='toggle_hire_fire'>
				<td><b>Re-Hire Date 2</b> <?= show_help('admin_drivers.php','linedate_rehire2') ?></td>
				<td colspan='2'><input name="linedate_rehire2" id='linedate_rehire2' value="<? if(strtotime($row_edit['linedate_rehire2']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_rehire2'])) ?>" onChange='mrr_act_driver(2);'> (mm/dd/yyyy)</td>
			</tr>
			<tr class='toggle_hire_fire'>
				<td><b>Re-Leave Date 2</b> <?= show_help('admin_drivers.php','linedate_refire2') ?></td>
				<td colspan='2'><input name="linedate_refire2" id='linedate_refire2' value="<? if(strtotime($row_edit['linedate_refire2']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_refire2'])) ?>" onChange='mrr_deact_driver(2);'> (mm/dd/yyyy)</td>
			</tr>
			<tr class='toggle_hire_fire'>
				<td><b>Re-Hire Date 3</b> <?= show_help('admin_drivers.php','linedate_rehire3') ?></td>
				<td colspan='2'><input name="linedate_rehire3" id='linedate_rehire3' value="<? if(strtotime($row_edit['linedate_rehire3']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_rehire3'])) ?>" onChange='mrr_act_driver(3);'> (mm/dd/yyyy)</td>
			</tr>
			<tr class='toggle_hire_fire'>
				<td><b>Re-Leave Date 3</b> <?= show_help('admin_drivers.php','linedate_refire3') ?></td>
				<td colspan='2'><input name="linedate_refire3" id='linedate_refire3' value="<? if(strtotime($row_edit['linedate_refire3']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_refire3'])) ?>" onChange='mrr_deact_driver(3);'> (mm/dd/yyyy)</td>
			</tr>
			<tr class='toggle_hire_fire'>
				<td><b>Re-Hire Date 4</b> <?= show_help('admin_drivers.php','linedate_rehire4') ?></td>
				<td colspan='2'><input name="linedate_rehire4" id='linedate_rehire4' value="<? if(strtotime($row_edit['linedate_rehire4']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_rehire4'])) ?>" onChange='mrr_act_driver(4);'> (mm/dd/yyyy)</td>
			</tr>
			<tr class='toggle_hire_fire'>
				<td><b>Re-Leave Date 4</b> <?= show_help('admin_drivers.php','linedate_refire4') ?></td>
				<td colspan='2'><input name="linedate_refire4" id='linedate_refire4' value="<? if(strtotime($row_edit['linedate_refire4']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_refire4'])) ?>" onChange='mrr_deact_driver(4);'> (mm/dd/yyyy)</td>
			</tr>
			<tr class='toggle_hire_fire'>
				<td><b>Re-Hire Date 5</b> <?= show_help('admin_drivers.php','linedate_rehire5') ?></td>
				<td colspan='2'><input name="linedate_rehire5" id='linedate_rehire5' value="<? if(strtotime($row_edit['linedate_rehire5']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_rehire5'])) ?>" onChange='mrr_act_driver(5);'> (mm/dd/yyyy)</td>
			</tr>
			<tr class='toggle_hire_fire'>
				<td><b>Re-Leave Date 5</b> <?= show_help('admin_drivers.php','linedate_refire5') ?></td>
				<td colspan='2'><input name="linedate_refire5" id='linedate_refire5' value="<? if(strtotime($row_edit['linedate_refire5']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_refire5'])) ?>" onChange='mrr_deact_driver(5);'> (mm/dd/yyyy)</td>
			</tr>
            <tr>
                <td><b>Payroll Bonus Override</b> <?= show_help('admin_drivers.php','payroll_bonus_override') ?></td>
                <td colspan='2'>
                    <select name='payroll_bonus_override' id='payroll_bonus_override'>
                        <option value='0' <?=($row_edit['payroll_bonus_override'] == 0 ? "selected" : "")?>>Default - None</option>
                        <option value='1' <?=($row_edit['payroll_bonus_override'] == 1 ? "selected" : "")?>>Half $<?= $defaultsarray['payroll_bonus_half_rate'] ?></option>
                        <option value='2' <?=($row_edit['payroll_bonus_override'] == 2 ? "selected" : "")?>>Full $<?= $defaultsarray['payroll_bonus_full_rate'] ?></option>
                    </select>
                </td>
            </tr>
			<tr>
				<td><b>Payroll First Name</b> <?= show_help('admin_drivers.php','payroll_first_name') ?></td>
				<td colspan='2'><input name="payroll_first_name" value="<?=$row_edit['payroll_first_name']?>"></td>
			</tr>
			<tr>
				<td><b>Payroll Last Name</b> <?= show_help('admin_drivers.php','payroll_last_name') ?></td>
				<td colspan='2'><input name="payroll_last_name" value="<?=$row_edit['payroll_last_name']?>"></td>
			</tr>
			<tr>
				<td><b>JIT driver</b> <?= show_help('admin_drivers.php','jit_driver_flag') ?></td>
				<td colspan='2'>
					<?  if(!isset($row_edit['jit_driver_flag']))		$row_edit['jit_driver_flag']=0;	?>
					
					<input type='radio' id="jit_driver_flag" name="jit_driver_flag" value='0' <? if($row_edit['jit_driver_flag'] == 0) echo " checked"?>> <span class='non_jit'>Non-JIT</span> 
					or 
					<input type='radio' id="jit_driver_flag" name="jit_driver_flag" value='1' <? if($row_edit['jit_driver_flag'] > 0) echo " checked"?>> <span class='jit'>JIT</span>					
				</td>
			</tr>			
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>
			
			<tr>
				<td><b>GeoTab User ID</b> <?= show_help('admin_drivers.php','geotab_use_id') ?></td>
				<td colspan='2'><input type='text' id="geotab_use_id" name="geotab_use_id" value="<?= trim($row_edit['geotab_use_id']) ?>" placeholder='Ex: "b9"' title='Not necesarily a number.'></td>
			</tr>
			<tr>
				<td><label for="hazmat"><b>Hazardous Material Certified</b></label> <?= show_help('admin_drivers.php','hazmat') ?></td>
				<td colspan='2'><input type='checkbox' id="hazmat" name="hazmat" <? if($row_edit['hazmat']) echo "checked"?>></td>
			</tr>
			<tr>
				<td><label for="tanker_endorsement"><b>Tanker Endorsement</b></label> <?= show_help('admin_drivers.php','tanker_endorsement') ?></td>
				<td colspan='2'><input type='checkbox' id="tanker_endorsement" name="tanker_endorsement" <? if($row_edit['tanker_endorsement']) echo "checked"?>></td>
			</tr>
			<tr>
				<td><b>Driver's License Number</b> <?= show_help('admin_drivers.php','dl_number') ?></td>
				<td colspan='2'><input name="dl_number" value="<?=$row_edit['dl_number']?>"></td>
			</tr>
			<tr>
				<td><b>Driver's License State</b> <?= show_help('admin_drivers.php','dl_state') ?></td>
				<td colspan='2'><input name="dl_state" value="<?=$row_edit['dl_state']?>"></td>
			</tr>
			<tr>
				<td><b>Date License Expires</b> <?= show_help('admin_drivers.php','linedate_license_expires') ?></td>
				<td colspan='2'><input name="linedate_license_expires" id='linedate_license_expires' value="<? if(strtotime($row_edit['linedate_license_expires']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_license_expires'])) ?>"> (mm/dd/yyyy)</td>
			</tr>
			<tr>
				<td><b>Fuel Card Number</b> <?= show_help('admin_drivers.php','fuel_card_number') ?></td>
				<td colspan='2'><input name="fuel_card_number" value="<?=$row_edit['fuel_card_number']?>"></td>
			</tr>
			<tr>
				<td colspan='3'>&nbsp;</td>
			</tr>
			<tr>
				<td><b>Peoplenet Tracking ID</b> <?= show_help('admin_drivers.php','peoplenet_driver_id') ?></td>
				<td colspan='2'><input name="peoplenet_driver_id" value="<?=$row_edit['peoplenet_driver_id']?>"></td>
			</tr>
			<tr>
				<td valign='top'>
					<b>Head Shot</b> <?= show_help('admin_drivers.php','head_shot_photo') ?> 					
					<br> (.gif, .jpg, or .png)
				</td>
				<td colspan='2' valign='top'>
					<input name="head_shot_photo"  id="head_shot_photo" value="<?=$row_edit['head_shot_photo']?>">
					<br>
					<input type="file" name='mrr_import' id='mrr_import' style='width:200px;'>
					<?
					$mrr_driver_image=trim($row_edit['head_shot_photo']);
					$image_rotation=$row_edit['image_rotation'];
					?>
				</td>
			</tr>
			<tr>
				<td valign='top'>
					<b>Rotation</b> <?= show_help('admin_drivers.php','image_rotation') ?> 
				</td>
				<td colspan='2' valign='top'>
					<input name="image_rotation"  id="image_rotation" value="<?=$row_edit['image_rotation']?>" style='width:50px; text-align:right;'> Degrees (90,180,270,360) 
				</td>
			</tr>
			<tr>
				<td><div class='mrr_button_access_notice'>&nbsp;</div></td>
				<td colspan='2'><input type='submit' value="Update" class='mrr_button_access'></td>
			</tr>
			</table>
			</form>
			<div class='clear'></div>
			<div id='note_section'></div>
			<div class='clear'></div>
			
			<div id='employer_section'>
					
			<? if($use_admin_level >= $driver_admin_min) { ?>
     				
     				<h1>Driver Employer Changes</h1>
     				<table width='500' border='0' cellspacing='0' cellpadding='0'>
     				<tr>
     					<td valign='top'><b>Switch Date<br>(mm/dd/yyyy)</b></td>
     					<td valign='top'><b>Old Employer</b></td>
     					<td valign='top'><b>New Employer</b></td>
     					<td valign='top'><b>&nbsp;</b></td>
     				</tr>
     				<?					
     					$sqlu="
     						select * 
     						from drivers_employer_change
     						where driver_id='".sql_friendly($_GET['id'])."'
     							and driver_id>0
     							and deleted=0
     						order by linedate asc
     					";
     					$datau=simple_query($sqlu);
     					while($rowu=mysqli_fetch_array($datau))
     					{
     						$old_emp="N/A";
     						$new_emp="N/A";
     						
     						//$rowu['id']	
     						//$rowu['linedate_added']
     						$stamper=date("m/d/Y",strtotime($rowu['linedate']));
     						if($rowu['old_employer_id'] > 0)	$old_emp=mrr_fetch_set_employer($rowu['old_employer_id']);
     						if($rowu['new_employer_id'] > 0)	$new_emp=mrr_fetch_set_employer($rowu['new_employer_id']);
     						//$rowu['deleted']	
     						
     						if($stamper=="12/31/1969")		$stamper="Starting";
     						//if(strtotime($row_edit['linedate_birthday']) != 0) echo date("m/d/Y", strtotime($row_edit['linedate_birthday']))
     						
     						$stamper_box="<input class='mrr_date_picker' name='linedate_rec_".$rowu['id']."' id='linedate_rec_".$rowu['id']."' value='".$stamper."' size='11' onChange='mrr_change_driver_employer(".$rowu['id'].",".$_GET['id'].",".$rowu['new_employer_id'].",0);'>";		
     						$removal_box="<span onClick='confirm_delete_employer_change(".$rowu['id'].",".$_GET['id'].",".$rowu['new_employer_id'].");'><img src='images/delete_sm.gif' border='0'></span>";
     						
     						if($stamper=="Starting")		{	$stamper_box="".$stamper."";			$removal_box=""; 	}	
     						
     						if($use_admin_level < 90) $removal_box="";
     							
     						echo "
     							<tr class='row_".$rowu['id']."'>
     								<td valign='top'>".$stamper_box."</td>
     								<td valign='top'><span title='Old Employer ID was ".$rowu['old_employer_id']."'>".$old_emp."</span></td>
     								<td valign='top'><span title='New Employer ID was ".$rowu['new_employer_id']."'>".$new_emp."</span></td>
     								<td valign='top'>".$removal_box."</td>
     							</tr>
     						";
     					}			
     				?>
     				</table>
     				<br>				
     				<h1>Driver Payroll Changed Log</h1>
     				<table width='500' border='0' cellspacing='0' cellpadding='0'>
     				<tr>
     					<td valign='top'><b>Date</b></td>
     					<td valign='top'><b>Type</b></td>
     					<td valign='top' align='right' nowrap><b>Miles Pd</b></td>
     					<td valign='top' align='right' nowrap><b>Miles Ch</b></td>
     					<td valign='top' align='right' nowrap><b>Hours Pd</b></td>
     					<td valign='top' align='right' nowrap><b>Hours Ch</b></td>
     				</tr>
     				<?	     									
     					$my_driver_id=$_GET['id'];
     					$my_last_raise="";
     					
     					$cntr=0;
     					$sqlu="
     						select driver_payroll_changes.*,
     						(
     							select d2.linedate 
     							from driver_payroll_changes d2 
     							where d2.linedate >= driver_payroll_changes.linedate 
     								and d2.id != driver_payroll_changes.id 
     								and d2.deleted=0
     								and d2.auto_schedule=0
     								and d2.driver_id='".sql_friendly($_GET['id'])."'
     							order by id asc limit 1
     							
     						) as next_dater
     						from driver_payroll_changes
     						where driver_payroll_changes.driver_id='".sql_friendly($_GET['id'])."'
     							and driver_payroll_changes.driver_id>0
     							and driver_payroll_changes.deleted=0
     							and driver_payroll_changes.auto_schedule=0
     						order by driver_payroll_changes.linedate asc,driver_payroll_changes.linedate_added asc
     					";
     					$datau=simple_query($sqlu);
     					while($rowu=mysqli_fetch_array($datau))
     					{
     						$stamper=date("m/d/Y H:i",strtotime($rowu['linedate']));
     						$stamper2=date("m/d/Y H:i",strtotime($rowu['next_dater']));
     						
     						$my_last_raise=date("m/d/Y",strtotime($rowu['linedate']));
     						
     						$dres=mrr_pull_unavailable_driver_days_and_codes_symbols($stamper,$stamper2,$_GET['id'],1);
     						
     						$vaca_taken=$dres['vaca'];				
     						$sick_taken=$dres['unavail'] + $dres['absent'];				
     						
     						$vaca_left = $rowu['vaca_days_allowed'] - $rowu['vaca_days_used'] - $vaca_taken;
     						$sick_left = $rowu['sick_days_allowed'] - $rowu['sick_days_used'] - $sick_taken;
     						
     						echo "
     							<tr class='".($cntr%2==0 ? "even" : "odd")."'>
     								<td valign='top'>".$stamper."</td>
     								<td valign='top'>Single<br>Team</td>
     								<td valign='top' align='right'>$".number_format($rowu['single_mile_pay'],3)."<br>$".number_format($rowu['team_mile_pay'],3)."</td>
     								<td valign='top' align='right'>$".number_format($rowu['single_mile_pay_charged'],3)."<br>$".number_format($rowu['team_mile_pay_charged'],3)."</td>
     								<td valign='top' align='right'>$".number_format($rowu['single_hour_pay'],2)."<br>$".number_format($rowu['team_hour_pay'],2)."</td>
     								<td valign='top' align='right'>$".number_format($rowu['single_hour_pay_charged'],2)."<br>$".number_format($rowu['team_hour_pay_charged'],2)."</td>
     							</tr>
     							<tr class='".($cntr%2==0 ? "even" : "odd")."'>
     								<td valign='top'>&nbsp;</td>
     								<td valign='top'>Vacation Days</td>
     								<td valign='top' align='right'>".number_format($rowu['vaca_days_allowed'],2)."</td>
     								<td valign='top' align='right'> - ".number_format($rowu['vaca_days_used'],2)." Used</td>
     								<td valign='top' align='right'> - <span style='color:purple;'><b>".number_format($vaca_taken,2)." Taken</span></td>
     								<td valign='top' align='right'> = ".number_format($vaca_left,2)." Left</td>
     							</tr>
     							<tr class='".($cntr%2==0 ? "even" : "odd")."'>
     								<td valign='top'>&nbsp;</td>
     								<td valign='top'>Personal Days</td>
     								<td valign='top' align='right'>".number_format($rowu['sick_days_allowed'],2)."</td>
     								<td valign='top' align='right'> - ".number_format($rowu['sick_days_used'],2)." Used</td>
     								<td valign='top' align='right'> - <span style='color:purple;'><b>".number_format($sick_taken,2)." Taken</span></td>
     								<td valign='top' align='right'> = ".number_format($sick_left,2)." Left</td>
     							</tr>
     							<tr class='".($cntr%2==0 ? "even" : "odd")."'>
     								<td valign='top'>&nbsp;</td>
     								<td valign='top' colspan='5'>".trim($rowu['raise_notes'])."</td>
     							</tr>
     						";						
     						$cntr++;
     					}			
     				?>
     				</table>	
     				<?
     					if($my_driver_id > 0 && trim($my_last_raise)!="" && $cntr>0 && $use_admin_level >=95)
     					{
     						//$rep_raise=mrr_backprocess_payroll_raise_dispatches($my_driver_id,$my_last_raise,1);
     						echo "<br>
     							<center>
     								<span class='mrr_link_like_on' onClick='mrr_update_last_pay_raises(".$my_driver_id.",\"".trim($my_last_raise)."\");'><b>Update Last Pay Raise Dispatches</b></span> 
     								".show_help('admin_drivers.php','payroll update from last raise')."
     							</center>
     							<br>
     						";     						
     					}
     				?>     				
     				<br>			
     				<span style='color:purple;'><b>*** Personal and Vacation Days "Taken" are days calculated by the system from Driver Absences and the Driver Unavailable History, or Driver Vacation and Advances.</b></span>
     				<br>				
     				
     				<h1>Driver Payroll Changes Scheduled</h1>
     				<table width='500' border='0' cellspacing='0' cellpadding='0'>
     				<tr>
     					<td valign='top'><b>Scheduled</b></td>
     					<td valign='top'><b>Type</b></td>
     					<td valign='top' align='right' nowrap><b>Miles Pd</b></td>
     					<td valign='top' align='right' nowrap><b>Miles Ch</b></td>
     					<td valign='top' align='right' nowrap><b>Hours Pd</b></td>
     					<td valign='top' align='right' nowrap><b>Hours Ch</b></td>
     				</tr>
     				<?					
     					$cntr=0;
     					$sqlu="
     						select driver_payroll_changes.*,
     						(
     							select d2.linedate 
     							from driver_payroll_changes d2 
     							where d2.linedate >= driver_payroll_changes.linedate 
     								and d2.id != driver_payroll_changes.id 
     								and d2.deleted=0
     								and d2.auto_schedule = 0
     								and d2.driver_id='".sql_friendly($_GET['id'])."'
     							order by id asc limit 1
     							
     						) as next_dater
     						from driver_payroll_changes
     						where driver_payroll_changes.driver_id='".sql_friendly($_GET['id'])."'
     							and driver_payroll_changes.driver_id>0
     							and driver_payroll_changes.deleted=0
     							and driver_payroll_changes.auto_schedule > 0
     						order by driver_payroll_changes.linedate asc,driver_payroll_changes.linedate_added asc
     					";
     					$datau=simple_query($sqlu);
     					while($rowu=mysqli_fetch_array($datau))
     					{
     						$stamper=date("m/d/Y H:i",strtotime($rowu['linedate']));
     						$stamper2=date("m/d/Y H:i",strtotime($rowu['next_dater']));
     						
     						$dres=mrr_pull_unavailable_driver_days_and_codes_symbols($stamper,$stamper2,$_GET['id'],1);
     						
     						$vaca_taken=$dres['vaca'];				
     						$sick_taken=$dres['unavail'] + $dres['absent'];				
     						
     						$vaca_left = $rowu['vaca_days_allowed'] - $rowu['vaca_days_used'] - $vaca_taken;
     						$sick_left = $rowu['sick_days_allowed'] - $rowu['sick_days_used'] - $sick_taken;
     						
     						$removal_box="<span onClick='confirm_delete_payroll_change(".$rowu['id'].",".$_GET['id'].");'><img src='images/delete_sm.gif' border='0'></span>";
     							
     						echo "
     							<tr class='".($cntr%2==0 ? "even" : "odd")." change_row_".$rowu['id']."'>
     								<td valign='top'>".$removal_box." ".$stamper."</td>
     								<td valign='top'>Single<br>Team</td>
     								<td valign='top' align='right'>$".number_format($rowu['single_mile_pay'],3)."<br>$".number_format($rowu['team_mile_pay'],3)."</td>
     								<td valign='top' align='right'>$".number_format($rowu['single_mile_pay_charged'],3)."<br>$".number_format($rowu['team_mile_pay_charged'],3)."</td>
     								<td valign='top' align='right'>$".number_format($rowu['single_hour_pay'],2)."<br>$".number_format($rowu['team_hour_pay'],2)."</td>
     								<td valign='top' align='right'>$".number_format($rowu['single_hour_pay_charged'],2)."<br>$".number_format($rowu['team_hour_pay_charged'],2)."</td>
     							</tr>
     							<tr class='".($cntr%2==0 ? "even" : "odd")."'>
     								<td valign='top'>&nbsp;</td>
     								<td valign='top'>Vacation Days</td>
     								<td valign='top' align='right'>".number_format($rowu['vaca_days_allowed'],2)."</td>
     								<td valign='top' align='right'> - ".number_format($rowu['vaca_days_used'],2)." Used</td>
     								<td valign='top' align='right'> - <span style='color:purple;'><b>".number_format($vaca_taken,2)." Taken</span></td>
     								<td valign='top' align='right'> = ".number_format($vaca_left,2)." Left</td>
     							</tr>
     							<tr class='".($cntr%2==0 ? "even" : "odd")."'>
     								<td valign='top'>&nbsp;</td>
     								<td valign='top'>Personal Days</td>
     								<td valign='top' align='right'>".number_format($rowu['sick_days_allowed'],2)."</td>
     								<td valign='top' align='right'> - ".number_format($rowu['sick_days_used'],2)." Used</td>
     								<td valign='top' align='right'> - <span style='color:purple;'><b>".number_format($sick_taken,2)." Taken</span></td>
     								<td valign='top' align='right'> = ".number_format($sick_left,2)." Left</td>
     							</tr>
     							<tr class='".($cntr%2==0 ? "even" : "odd")." change_row_".$rowu['id']."'>
     								<td valign='top'>&nbsp;</td>
     								<td valign='top' colspan='5'>".trim($rowu['raise_notes'])."</td>
     							</tr>
     						";						
     						$cntr++;
     					}			
     				?>
     				</table>	
     			<? } ?>
				<? if($_GET['id'] > 0 && $use_admin_level >= $driver_admin_min) { ?>			
     				<br>				
     				<h1>Schedule Driver Payroll Change</h1>
     				<table width='500' border='0' cellspacing='0' cellpadding='0'>
     				<tr>
     					<td valign='top'><b>Scheduled</b> <?= show_help('admin_drivers.php','payroll scheduled') ?></td>
     					<td valign='top'><b>Type</b></td>
     					<td valign='top' align='right' nowrap><b>Miles Pd</b></td>
     					<td valign='top' align='right' nowrap><b>Miles Ch</b></td>
     					<td valign='top' align='right' nowrap><b>Hours Pd</b></td>
     					<td valign='top' align='right' nowrap><b>Hours Ch</b></td>
     				</tr>     				
     				<tr>
     					<td valign='top'><input type='text' name='payroll_change_date' id='payroll_change_date' value="" class='mrr_date_picker'></td>
     					<td valign='top'>Single <?= show_help('admin_drivers.php','payroll scheduled single') ?><br>Team <?= show_help('admin_drivers.php','payroll scheduled team') ?></td>
     					<td valign='top' align='right' nowrap>
     						$<input type='text' name='payroll_change_miles' id='payroll_change_miles' value="0.000" style='width:50px; text-align:right;'>
     						<br>
     						$<input type='text' name='payroll_change_miles_team' id='payroll_change_miles_team' value="0.000" style='width:50px; text-align:right;'>	
     					</td>
     					<td valign='top' align='right' nowrap>
     						$<input type='text' name='payroll_change_miles_charged' id='payroll_change_miles_charged' value="0.000" style='width:50px; text-align:right;'>
     						<br>
     						$<input type='text' name='payroll_change_miles_charged_team' id='payroll_change_miles_charged_team' value="0.000" style='width:50px; text-align:right;'>	
     					</td>
     					<td valign='top' align='right' nowrap>
     						$<input type='text' name='payroll_change_hours' id='payroll_change_hours' value="0.00" style='width:50px; text-align:right;'>
     						<br>
     						$<input type='text' name='payroll_change_hours_team' id='payroll_change_hours_team' value="0.00" style='width:50px; text-align:right;'>	
     					</td>
     					<td valign='top' align='right' nowrap>
     						$<input type='text' name='payroll_change_hours_charged' id='payroll_change_hours_charged' value="0.00" style='width:50px; text-align:right;'>
     						<br>
     						$<input type='text' name='payroll_change_hours_charged_team' id='payroll_change_hours_charged_team' value="0.00" style='width:50px; text-align:right;'>	
     					</td>
     				</tr>
     				
     				<tr>
     					<td valign='top'>&nbsp;</td>
     					<td valign='top'>Vacation Days <?= show_help('admin_drivers.php','payroll scheduled vacation') ?></td>
     					<td valign='top' align='right' colspan='2'>
     						<b>Allowed:</b> <input type='text' name='payroll_vaca_allowed' id='payroll_vaca_allowed' value="0.00" style='width:50px; text-align:right;'>
     					</td>
     					<td valign='top' align='right' colspan='2'>
     						<b>Used:</b> <input type='text' name='payroll_vaca_used' id='payroll_vaca_used' value="0.00" style='width:50px; text-align:right;'>
     					</td>
     				</tr>
     				<tr>
     					<td valign='top'>&nbsp;</td>
     					<td valign='top'>Sick Days <?= show_help('admin_drivers.php','payroll scheduled sick') ?></td>
     					<td valign='top' align='right' colspan='2'>
     						<b>Allowed:</b> <input type='text' name='payroll_sick_allowed' id='payroll_sick_allowed' value="0.00" style='width:50px; text-align:right;'>
     					</td>
     					<td valign='top' align='right' colspan='2'>
     						<b>Used:</b> <input type='text' name='payroll_sick_used' id='payroll_sick_used' value="0.00" style='width:50px; text-align:right;'>
     					</td>
     				</tr>
     				
     				<tr>
     					<td valign='top'> Reason / Note</td>
     					<td valign='top' colspan='4'><input type='text' name='payroll_change_reason' id='payroll_change_reason' value="" style='width:280px;'></td>
     					<td valign='top' align='right'><span class='mrr_link_like_on' onClick='add_driver_payroll_change(<?=$_GET['id'] ?>);'>Schedule</span></td>					
     				</tr>
     				</table>
     				<br>&nbsp;
				<? } ?>					
			</div>
			<div class='change_log'>
				<?
				if(!isset($_GET['id']))		$_GET['id']=0;
				?>
				<?= ($_GET['id'] > 0 && $use_admin_level >= $driver_admin_min ?  mrr_get_user_change_log(" and user_change_log.driver_id='".sql_friendly($_GET['id'])."'"," order by user_change_log.linedate_added asc","",1) : "") ?>
			</div>			
		<? } ?>
	</td>
	<?
	if(!isset($_GET['id']))		$_GET['id']=0;
	?>
	<td valign='top'>
		<? if($_GET['id'] > 0 && $use_admin_level >= $driver_admin_min) { ?>	
     		<div id='driver_unavailable_history' style='margin-bottom:10px'></div>
     		
     		<br>
     		<h1>Driver Dispatch and Preplanned Hours</h1>
     		<table width='500' border='0' cellspacing='0' cellpadding='0'>
     		<tr>
     			<td valign='top'><b>Current Week</b></td>
     			<td valign='top' align='right'><b>Hours Last Week</b></td>
     			<td valign='top' align='right'><b>Dispatch Hours</b></td>
     			<td valign='top' align='right'><b>Preplan Hours</b></td>
     		</tr>
     		<?
     			$max_hours_allowed=40;
     			$dres=mrr_driver_hours_calc($_GET['id'],date("m/d/Y"));
     			$approx_hours=$dres['hours'];
     			$approx_hours2=$dres['hours2'];
     			$preplan_hours=$dres['planned'];
     			
     			$tag1="";
          		$tag2="";
          		if(($approx_hours2 + $preplan_hours) > $max_hours_allowed)
          		{
          			$tag1="<span class='alert' title='Driver may have more than ".$max_hours_allowed." hours scheduled (Dispatch Hours + Preplan Hours)...this may be an error. Possible DOT violation.'><b>";
          			$tag2="</b></span>";	
          		}
          		
     			echo "
     				<tr>
     					<td valign='top'>".$dres['from']." - ".$dres['to']."</td>
     					<td valign='top' align='right'>".number_format($approx_hours,2)."</td>
     					<td valign='top' align='right'>".number_format($approx_hours2,2)."</td>
     					<td valign='top' align='right'>".$tag1."".number_format($preplan_hours,2)."".$tag2."</td>
     				</tr>
     			";
     		?>
     		</table>
     		<br>	
			
     		<div id='driver_expense_history'></div>
     		
     		<br><br>
     		<div id='driver_photo_pic'>
     		<?
     		if(trim($mrr_driver_image)!="")
     		{
     			$sized = getimagesize("drivers/".trim($mrr_driver_image)."");
     			
     			$iwide=(int) str_replace(",","",$sized[0]);
     			$ihigh=(int) str_replace(",","",$sized[1]);
     			$max_wide=400;
     			
     			if($iwide > $max_wide)
     			{
     				$ratio=$iwide/$max_wide;				// 1536 / 300 = 5.12
     				$iwide=$max_wide;	
     				$ihigh= round($ihigh / $ratio);  		// 2048 / 5.12 = 400
     			}
     			echo "
     				<br>
     				<br>
     				<a href='/drivers/".trim($mrr_driver_image)."' target='_blank'>
     				    <img src='/drivers/".trim($mrr_driver_image)."' border='1' width='".$iwide."' alt='".trim($mrr_driver_image)." not found...".$iwide." x ".$ihigh."'".
     					    ($row_edit['image_rotation'] > 0 ? " style='-ms-transform: rotate(".$row_edit['image_rotation']."deg); -webkit-transform: rotate(".$row_edit['image_rotation']."deg); transform: rotate(".$row_edit['image_rotation']."deg);'" : "")
     				    .">
     				</a>";	// height='".$ihigh."'
     		}
     		?>
     		</div>
     		<br><br>
			
			
     		<div id='driver_avail_mrr'>
     		<?
     			//$res=mrr_validate_driver_truck_trailer_attachment($mrr_driver_id);
     			//echo $res['table'];
     			//echo "<br>Driver ".$mrr_driver_id." Validity is ".$res['valid']."<br>";
     		?>
     		</div>
     				
		
     		<div id='driver_absenses'>    			
     			<div id='driver_absenses_list'></div>
     			<div class='clear'></div>
     		</div>
             
             <? if($_GET['id'] > 0) { ?>
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
                         <?php      //<td valign='top'><b>Driver</b></td>
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
                                    and internal_tasks.task_type = 3
                                    and internal_tasks_checked.entity_id='".(int) $_GET['id']."'
                                    and internal_tasks.linedate_start <= NOW()
                                order by internal_tasks.task_name asc,internal_tasks_checked.id asc
                             ";
                         $data_tasks = mysql_query($sql_tasks,$datasource);
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
                              //if($row_tasks['task_type']==2)  $entity_name="<a href='admin_trailers.php?id=".$row_tasks['entity_id']."' target='blank'>".trim($row_tasks['trailer_name'])."</a>";
                              if($row_tasks['task_type']==3)    $entity_name="<i>".trim($row_tasks['driver_name'])."</i>";
                              
                              echo "
                                        <tr style='background-color:".($task_cntr%2==0 ? "eeeeee" : "dddddd").";'>
                                            <td valign='top'>".$row_tasks['task_name']."</td>
                                            
                                            <td valign='top'>".$cur_dater."</td>                                        
                                            <td valign='top'><i>".$done_by."</i></td>
                                            <td valign='top'><i>".$done_on."</i></td>                                     
                                        </tr>
                                 ";     //     <td valign='top'>".$entity_name."</td>         
                              $task_cntr++;
                         }
                         ?>
                    </table>
                </div>
             <? } ?>
             
             
             <? if($use_new_uploader > 0) { ?>
     		
     			<br>&nbsp;<br>
          		<iframe src="mrr_uploader_hack.php?section_id=1&id=<?=$_GET['id']?>" width='500' height='80' border='0' style='border:#000000 solid 0px; background-color:#ffffff;'>
          		</iframe> 
          		<div id='attachment_holder'></div>
     		
     		<? } else { ?>
     		
     			<div id='upload_section'></div>
     		
     		<? } ?>
     		
     		
			<div class='clear'></div>
		<? } ?>	
	</td>
</tr>
<tr>
	<td valign='top'>&nbsp;</td>
	<td valign='top' colspan='2'>
		<form action="<?=$SCRIPT_NAME?>?id=<?=$_GET['id']?>" method="post" name='mrr_checklist'>			
     		<? 	// enctype="multipart/form-data"     		
     		if($_GET['id'] > 0 && $use_admin_level >= $driver_admin_min) 
     		{ 
     			echo "<div style='width:800px; border:1px solid #000000; background-color:#FFFFFF; padding:10px;'>";
     			
     			$checkform=mrr_load_driver_dot_checklist_form($_GET['id'],$_GET['checklist_id']);
     			echo $checkform;
     			
     			$checklists=mrr_list_driver_dot_checklist_forms($_GET['id']);
     			echo $checklists;
     			echo "</div>";
     		} 
     		?>	     		
		</form>	
	</td>
	
</tr>
</table>

<script type='text/javascript'>
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete this driver?")) {
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
	
	function mrr_send_mass_email_drivers()
	{
		my_driver_id=$('#gen_driver_id').val();
		subj=$('#gen_driver_sub').val();
		msg=$('#gen_driver_email').val();
		
		$('#gen_driver_msg').html("");
		
		$.ajax({
     		url: "ajax.php?cmd=mrr_send_driver_message_out",
     		type: "post",
     		dataType: "xml",
     		data: {				
     			"driver_id":my_driver_id,
     			"subject": subj,
     			"message":msg
     		},
     		error: function() {
     			$.prompt("Error: Cannot send Driver email at this time.");
     		},
     		success: function(xml) {	     				
     			$.noticeAdd({text: "Driver Email sent successfully."});
     			     			
     			$('#gen_driver_msg').html("Message Sent");
     		}
     	});	
	}
	
	function  mrr_act_driver(cd)
	{
		tester="";
		if(cd==0)			tester=$('#linedate_started').val();
		if(cd==1)			tester=$('#linedate_rehire').val();
		if(cd==2)			tester=$('#linedate_rehire2').val();
		if(cd==3)			tester=$('#linedate_rehire3').val();
		if(cd==4)			tester=$('#linedate_rehire4').val();
		if(cd==5)			tester=$('#linedate_rehire5').val();
		
		if(tester!="")		$('#active').attr( "checked", true );
	}
	function  mrr_deact_driver(cd)
	{
		tester="";
		if(cd==0)			tester=$('#linedate_terminated').val();
		if(cd==1)			tester=$('#linedate_refire').val();
		if(cd==2)			tester=$('#linedate_refire2').val();
		if(cd==3)			tester=$('#linedate_refire3').val();
		if(cd==4)			tester=$('#linedate_refire4').val();
		if(cd==5)			tester=$('#linedate_refire5').val();
		
		if(tester!="")		$('#active').attr( "checked", false );
	}
	
	function mrr_toggle_hire_fire()
	{
		$('.toggle_hire_fire').toggle();
	}
	
	function check_driver_form()
	{
		if($('#linedate_review_due').val()=="")
		{
			$.prompt("<span class='mrr_alert'>Error:</span> Driver must has Review Due Date set to update his/her record.<br><br>Please enter Review Due Date and try again.");
			$('#linedate_review_due').focus();
			return false;
		}		
		
		return true;	
	}
	
	function confirm_delete_employer_change(myid,driverid,empid) {
		if(confirm("Are you sure you want to delete this driver's employer change record?")) 
		{			
			mrr_change_driver_employer(myid,driverid,empid,1);	
					
			$('.row_'+myid+'').hide();		//hide this row...removed.
		}
	}
	function confirm_delete_payroll_change(myid,driverid)
	{
		if(confirm("Are you sure you want to delete this driver's scheduled payroll change?")) 
		{			
			mrr_change_driver_payroll(myid,driverid);	
		}
	}
	
	function mrr_update_last_pay_raises(my_driver_id,my_last_raise)
	{
		if(confirm("Are you sure you want to modify all of the dispatches from "+my_last_raise+" to now for this driver?")) {
			
			$.ajax({
     			url: "ajax.php?cmd=mrr_set_driver_raise_dispatches",
     			type: "post",
     			dataType: "xml",
     			data: {				
     				"driver_id":my_driver_id,
     				"date_start":my_last_raise
     			},
     			error: function() {
     				$.prompt("Error: Cannot update Driver's dispatches for last pay raise.");
     			},
     			success: function(xml) {	     				
     				$.noticeAdd({text: "Driver's dispatches updated for last pay raise successfully."});
     			}
     		});	
		}	
	}
	
	function confirm_delete_absense(driver,id) 
	{
		if(confirm("Are you sure you want to delete this driver absence record?")) {
			
			$.ajax({
     			url: "ajax.php?cmd=mrr_remove_driver_absense_records",
     			type: "post",
     			dataType: "xml",
     			data: {				
     				"id":id
     			},
     			error: function() {
     				$.prompt("Error: Cannot update Driver Absence Record");
     			},
     			success: function(xml) {		
     				
     				mrr_list_driver_absense(driver);
     			}
     		});	
		}
	}
	<?php    
    $cal_mon = date("m", time());
    $cal_day = date("d", time());
    $cal_yr = date("Y", time());
    if(isset($_GET['cal_mon'])) $cal_mon = (int)$_GET['cal_mon'];
    if(isset($_GET['cal_day'])) $cal_day = (int)$_GET['cal_day'];
    if(isset($_GET['cal_year'])) $cal_yr = (int)$_GET['cal_year'];    
    ?>
	function mrr_list_driver_absense(id)
	{
		$('#driver_absenses_list').html('loading...');
		
		$.ajax({
			url: "ajax.php?cmd=mrr_list_driver_absense_records",
			type: "post",
			dataType: "xml",
			data: {
                "cal_mon": "<?=$cal_mon ?>" ,
                "cal_day": "<?=$cal_day ?>" ,
                "cal_year": "<?=$cal_yr ?>" ,
                "driver_id":id
			},
			error: function() {
				$.prompt("Error: Cannot update Driver Absence Record");
			},
			success: function(xml) {		
				
				mrr_tab=$(xml).find('mrrTab').text();
				$('#driver_absenses_list').html(mrr_tab);	
			}
		});	
	}
	
	function add_driver_payroll_change(id)
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_add_driver_payroll_change",
			type: "post",
			dataType: "xml",
			data: {				
				"driver_id":id,
				"date": $('#payroll_change_date').val(),
				"miles":$('#payroll_change_miles').val(),
				"miles_tm":$('#payroll_change_miles_team').val(),
				"miles_ch":$('#payroll_change_miles_charged').val(),
				"miles_ch_tm":$('#payroll_change_miles_charged_team').val(),
				"hours":$('#payroll_change_hours').val(),
				"hours_tm":$('#payroll_change_hours_team').val(),
				"hours_ch":$('#payroll_change_hours_charged').val(),
				"hours_ch_tm":$('#payroll_change_hours_charged_team').val(),
				
				"vaca_days": $('#payroll_vaca_allowed').val(),
				"vaca_used": $('#payroll_vaca_used').val(),
				"sick_days": $('#payroll_sick_allowed').val(),
				"sick_used": $('#payroll_sick_used').val(),
				
				"reason": $('#payroll_change_reason').val()
			},
			error: function() {
				$.prompt("Error: Cannot update Driver Payroll Change Schedule.");
			},
			success: function(xml) {		
				location.reload();
			}
		});	
	}
	
	function mrr_add_to_driver_absense(id)
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_add_driver_absense_record",
			type: "post",
			dataType: "xml",
			data: {				
				"driver_id":id,
				"date": $('#driver_absense_date').val(),
                "date_to": $('#driver_absense_date_to').val(),
				"code":$('#driver_absense_code').val(),
				"note": $('#driver_absense_note').val(),
                "duration": $('#driver_absense_duration').val()
			},
			error: function() {
				$.prompt("Error: Cannot update Driver Absence Record");
			},
			success: function(xml) {		
				mrr_list_driver_absense(id);		
			}
		});		
	}
	
	function mrr_change_driver_payroll(myid,driverid)
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_update_driver_payroll_change",
			type: "post",
			dataType: "xml",
			data: {
				"id": myid,
				"driver_id":driverid
			},
			error: function() {
				$.prompt("Error: Cannot update driver "+driverid+" payroll change record. ID="+myid+".");
			},
			success: function(xml) {				
				$('.change_row_'+myid+'').hide();		//hide this row...removed.		
			}
		});	
	}
	
	function mrr_change_driver_employer(myid,driverid,empid,delflag)
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_update_driver_employer",
			type: "post",
			dataType: "xml",
			data: {
				"id": myid,
				"driver_id":driverid,
				"employer_id":empid,
				"linedate": $('#linedate_rec_'+myid+'').val(),
				"delete_flag":delflag
			},
			error: function() {
				$.prompt("Error: Cannot update driver "+driverid+" employed by "+empid+" on "+$('#linedate_rec_'+myid+'').val()+". ID="+myid+".");
			},
			success: function(xml) {				
				mytxt=$(xml).find('mrrTab').text();	
				$.prompt( ""+ mytxt +"" );			
			}
		});	
	}
	
	$('.mrr_datepicker').datepicker();
				
	$('.mrr_date_picker').datepicker();
	$('#linedate_birthday').datepicker();	
	$('#linedate_drugtest').datepicker();
	$('#linedate_started').datepicker();
	$('#linedate_license_expires').datepicker();
	$('#linedate_cov_expires').datepicker();
	$('#linedate_terminated').datepicker();
	
	$('#linedate_misc_cert').datepicker();	
	
	$('#linedate_review_due').datepicker();
	
	$('#linedate_rehire').datepicker();
	$('#linedate_refire').datepicker();
	
	$('#linedate_rehire2').datepicker();
	$('#linedate_refire2').datepicker();
	$('#linedate_rehire3').datepicker();
	$('#linedate_refire3').datepicker();
	$('#linedate_rehire4').datepicker();
	$('#linedate_refire4').datepicker();
	$('#linedate_rehire5').datepicker();
	$('#linedate_refire5').datepicker();
	
	$('#linedate_spouse').datepicker();
	$('#linedate_anniversary').datepicker();
	$('#linedate_own_op_ins').datepicker();
	
	$('#driver_status_date').datepicker();
	
     $(".tablesorter").tablesorter({textExtraction: 'complex'});
	
	<? 
		if(isset($_GET['id']) && $_GET['id'] > 0 && $use_admin_level >= $driver_admin_min) 
		{
			if($use_new_uploader > 0) 
			{ 
				echo " create_upload_section_alt('#upload_section', 1, $_GET[id]); "; 
			}
			else
			{
				echo " create_upload_section('#upload_section', 1, $_GET[id]); "; 
			}				
			echo " create_note_section('#note_section', 1, $_GET[id]); "; 
			echo " $('#driver_expense_history').html(load_driver_expenses($_GET[id])); ";
			echo " $('#driver_unavailable_history').html(load_driver_unavailable($_GET[id])); ";
			echo " mrr_list_driver_absense( $_GET[id] );";
		}
		else
		{			
			if($use_new_uploader > 0) 
			{ 
				echo " create_note_section('#note_section', 1, $_GET[id]); "; 
			}
			else
			{
				echo " create_note_section('#note_section', 1, $_GET[id]); "; 
			}
		}
	?>
	function mrr_verify_user_unique(myid)
	{		
		$('#mrr_naming_message').html('');	
		mrr_lab="Driver";
		mrr_lab2="name_driver_";
		mrr_code=1;
		
		new_name1=$('#'+mrr_lab2+'first').val();
		new_name2=$('#'+mrr_lab2+'last').val();
		new_name=new_name1+' '+new_name2;
		
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
				//$('#'+mrr_lab2+'first').val('');
				//$('#'+mrr_lab2+'last').val('');
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
					$('#'+mrr_lab2+'first').val(''+new_name1+'.');
					$('#'+mrr_lab2+'last').val(''+new_name2+'.');
					$('#mrr_naming_message').html(''+mrr_lab+' must be unique.');	
					$('#mrr_naming_message').css('color','red');									
				}				
			}
		});	
		
	}	
	
	<? if($use_admin_level < 90) { ?>
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
	
	$('.toggle_hire_fire').hide();
		
	function duplicate_driver(id) 
	{
		$.prompt("Are you sure you want to <span class='alert'>duplicate</span> this Driver?", {
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
