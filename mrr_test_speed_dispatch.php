<? include('application.php') ?>
<? $no_header = 1 ?>
<?
$usetitle="TEST Dispatch PAGE";
?>
<? include('header.php') ?>
<?
	$mrr_debug_time_start=date("His");		//for page load speed checks...used at bottom of the page.
	$mrr_debug_time1=time();

	
	$mrr_debug_timer=time() - $mrr_debug_time1;
	die('<br>Loaded in '.$mrr_debug_timer.' Seconds.<br>');
	

	$pn_auto_dispatch_link="";

	if(isset($_GET['load_id'])) $_POST['load_id'] = $_GET['load_id'];
	if(!isset($_POST['load_id'])) $_POST['load_id'] = 0;	

	if(!isset($_GET['id'])) $_GET['id'] = 0;

	if(isset($_GET['trailer_id'])) $_POST['trailer_id'] = $_GET['trailer_id'];
	if(isset($_GET['truck_id'])) $_POST['truck_id'] = $_GET['truck_id'];

	if($_POST['load_id'] == 0) {
		// look up the load ID if one exists for this dispatch
		$_POST['load_id'] = get_load_id_from_dispatch_id($_GET['id']);
	}

	$dispatch_count = 0;
	if($_POST['load_id'] > 0) {
		// figure out how many dispatches there are for this load
		$sql = "
			select count(*) as dispatch_count
			
			from trucks_log
			where load_handler_id = '".sql_friendly($_POST['load_id'])."'
				and deleted = 0
				and id <> '".sql_friendly($_GET['id'])."'
		";
		$data_count = simple_query($sql);
		$row_count = mysqli_fetch_array($data_count);
		$dispatch_count = $row_count['dispatch_count'];
		
		$mrr_activity_log_notes.="Other Dispatches for Load ".$_POST['load_id']." count. ";
	}

	if(isset($_GET['did'])) {
		$sql = "
			update trucks_log
			
			set deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_delete = simple_query($sql);
		
		// if there were any stops associated with this dispatch, unassociate them
		$sql = "
			update load_handler_stops
			set trucks_log_id = 0,
				start_trailer_id='0',
				end_trailer_id='0'
			where trucks_log_id = '".sql_friendly($_GET['did'])."'
		";
		simple_query($sql);
		
		// get the load ID for this dispatch, then do our post processing for load/dispatches to update orig/dest, costs, etc...
		$sql = "
			select load_handler_id
			from trucks_log
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_lh = simple_query($sql);
		$row_lh = mysqli_fetch_array($data_lh);
		
		if($row_lh['load_handler_id'] > 0) update_origin_dest($row_lh['load_handler_id']);
		
		$mrr_activity_log_notes.="Delete Dispatch ".$_GET['did'].". ";
		parent_window_refresh();
	}

	/* insert our new record */
	if($_GET['id'] == 0 && isset($_POST['driver_id'])) {
		
		$emp_id=mrr_fetch_set_employer_id($_POST['driver_id']);	
		
		$sql = "
			insert into trucks_log
				(linedate_added,
				tires_per_mile,
				accidents_per_mile,
				mile_exp_per_mile,
				misc_per_mile,
				employer_id,
				trailer_exp_per_mile)				
			values (now(),
				'".sql_friendly($defaultsarray['tires_per_mile'])."',
				'".sql_friendly($defaultsarray['truck_accidents_per_mile'])."',
				'".sql_friendly($defaultsarray['mileage_expense_per_mile'])."',
				'".sql_friendly($defaultsarray['misc_expense_per_mile'])."',
				'".sql_friendly($emp_id)."',
				'".sql_friendly($defaultsarray['trailer_mile_exp_per_mile'])."')
		";

		simple_query($sql);
		
		$_GET['id'] = mysqli_insert_id($datasource);
		$mrr_activity_log_notes.="Added New Dispatch ".$_GET['id'].". ";
	}
	
	if($_GET['id'] > 0 && isset($_POST['driver_id'])) {
		
		if(isset($_POST['truck_name']) && $_POST['truck_name'] != '') {
			$sql = "
				insert into trucks
					(name_truck,
					deleted)
					
				values ('".sql_friendly($_POST['truck_name'])."',
					0)
			";
			simple_query($sql);
			$_POST['truck_id'] = mysqli_insert_id($datasource);
			$mrr_activity_log_notes.="Added New Truck ".$_POST['truck_id']." for Dispatch ".$_GET['id'].". ";
		}
		
		if(isset($_POST['trailer_name']) && $_POST['trailer_name'] != '') {
			$sql = "
				insert into trailers
					(trailer_name,
					deleted)
					
				values ('".sql_friendly($_POST['trailer_name'])."',
					0)
			";
			simple_query($sql);
			$_POST['trailer_id'] = mysqli_insert_id($datasource);
			$mrr_activity_log_notes.="Added New Trailer ".$_POST['trailer_id']." for Dispatch ".$_GET['id'].". ";
		}
		
		if(isset($_POST['driver_name_first']) && $_POST['driver_name_first'] != '') {
			$sql = "
				insert into drivers
					(name_driver_first,
					name_driver_last,
					active)
					
				values ('".sql_friendly($_POST['driver_name_first'])."',
					'".sql_friendly($_POST['driver_name_last'])."',
					1)
			";
			simple_query($sql);
			
			$_POST['driver_id'] = mysqli_insert_id($datasource);
			$mrr_activity_log_notes.="Added New Driver ".$_POST['driver_id']." for Dispatch ".$_GET['id'].". ";
		}
		
		if(isset($_POST['customer_name']) && $_POST['customer_name'] != '') {
			$sql = "
				insert into customers
					(name_company,
					deleted)
					
				values ('".sql_friendly($_POST['customer_name'])."',
					0)
			";
			simple_query($sql);
			
			$_POST['customer_id'] = mysqli_insert_id($datasource);
			$mrr_activity_log_notes.="Added New Customer ".$_POST['customer_id']." for Dispatch ".$_GET['id'].". ";
		}
		
		//if already saved, get prior truck and cost, as well as trailer and cost to compare...
		$old_truck_id=0;		$old_truck_cost=0;
		$old_trailer_id=0;		$old_trailer_cost=0;
		$old_driver2_labor_mi=0;	$old_driver2_labor_hr=0;
		if(isset($_GET['id']) && $_GET['id'] > 0) 
		{
			$sql = "
				select truck_id,
					trailer_id,
					truck_cost,
					trailer_cost,
					driver_2_labor_per_mile,
					driver_2_labor_per_hour 
				from trucks_log 
				where id='".sql_friendly($_GET['id'])."'
			";
			$data=simple_query($sql);
			$row = mysqli_fetch_array($data);			
			
			$old_truck_id=$row['truck_id'];			$old_truck_cost=$row['truck_cost'];
			$old_trailer_id=$row['trailer_id'];		$old_trailer_cost=$row['trailer_cost'];
			
			$old_driver2_labor_mi=$row['driver_2_labor_per_mile'];	
			$old_driver2_labor_hr=$row['driver_2_labor_per_hour'];
		}
		
		//now compare and get costs for truck and trailer if they are different				
		$mrr_truck_cost=$old_truck_cost;				//
		$mrr_trailer_cost=$old_trailer_cost;			//
		$trent_flag=0;
				
		if(isset($_POST['truck_id']) && $_POST['truck_id'] > 0 && $_POST['truck_id']!=$old_truck_id)
		{
			$mrr_truck_cost=mrr_get_truck_cost($_POST['truck_id']);
			$trent_flag=mrr_get_truck_rental_status($_POST['truck_id']);
			
			if($mrr_truck_cost==0)		$mrr_truck_cost=mrr_pull_default_truck_cost_if_none();				
		}		
		if(isset($_POST['trailer_id']) && $_POST['trailer_id'] > 0 && $_POST['trailer_id']!=$old_trailer_id)
		{
			$mrr_trailer_cost=mrr_get_trailer_cost($_POST['trailer_id']);
		}
		
		if(isset($_POST['truck_cost']) && $_POST['truck_cost']!=$old_truck_cost && $_POST['truck_id']==$old_truck_id)
		{
			$mrr_truck_cost=money_strip($_POST['truck_cost']);	
		}
		if(isset($_POST['trailer_cost']) && $_POST['trailer_cost']!=$old_trailer_cost && $_POST['trailer_id']==$old_trailer_id)
		{
			$mrr_trailer_cost=money_strip($_POST['trailer_cost']);	
		}
		
		$emp_id=mrr_fetch_set_employer_id($_POST['driver_id']);
		
		//driver_2_labor_per_mile  $_POST['driver2_id']
		
		$mrr_labor_adder="";
		if($_POST['driver2_id'] > 0)
		{
			if(money_strip($_POST['driver_2_labor_per_mile']) > 0)
			{
				$mrr_labor_adder.="driver_2_labor_per_mile='".sql_friendly(money_strip($_POST['driver_2_labor_per_mile']))."',";	
			}
			elseif($old_driver2_labor_mi==0)
			{
				$cur_driver_labor_miles=mrr_get_driver_pay_rate($_POST['driver2_id'],6);
				$mrr_labor_adder.="driver_2_labor_per_mile='".sql_friendly($cur_driver_labor_miles)."',";	
			}
			
			if(money_strip($_POST['driver_2_labor_per_hour']) > 0)
			{
				$mrr_labor_adder.="driver_2_labor_per_hour='".sql_friendly(money_strip($_POST['driver_2_labor_per_hour']))."',";	
			}
			elseif($old_driver2_labor_hr==0)
			{
				$cur_driver_labor_hours=mrr_get_driver_pay_rate($_POST['driver2_id'],7);
				$mrr_labor_adder.="driver_2_labor_per_hour='".sql_friendly($cur_driver_labor_hours)."',";	
			}				
		}
		elseif($_POST['driver2_id']==0)
		{
			$mrr_labor_adder.="driver_2_labor_per_mile='0.000',";		
			$mrr_labor_adder.="driver_2_labor_per_hour='0.000',";	
		}
		
		
		$sql = "
			update trucks_log
			
			set truck_id = '".($_POST['truck_id'] == '' ? '0' : sql_friendly($_POST['truck_id']))."',
				driver_id = '".($_POST['driver_id'] == '' ? '0' : sql_friendly($_POST['driver_id']))."',
				driver2_id = '".($_POST['driver2_id'] == '' ? '0' : sql_friendly($_POST['driver2_id']))."',
				load_handler_id = '".($_POST['load_id'] == '' ? '0' : sql_friendly($_POST['load_id']))."',
				daily_run_otr = '".($_POST['daily_run_otr'] == '' ? '0' : sql_friendly($_POST['daily_run_otr']))."',
				trailer_maint_per_mile = '".($_POST['trailer_maint_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['trailer_maint_per_mile'])))."',
				tractor_maint_per_mile = '".($_POST['tractor_maint_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['tractor_maint_per_mile'])))."',
				manual_miles_flag = '".(isset($_POST['manual_miles_flag']) ? '1' : '0')."',
				labor_per_mile = '".($_POST['labor_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['labor_per_mile'])))."',
				labor_per_hour = '".($_POST['labor_per_hour'] == '' ? '0' : sql_friendly(money_strip($_POST['labor_per_hour'])))."',
				daily_cost = '".($_POST['daily_cost'] == '' ? '0' : sql_friendly(money_strip($_POST['daily_cost'])))."',
				avg_mpg = '".($_POST['avg_mpg'] == '' ? '0' : sql_friendly(money_strip($_POST['avg_mpg'])))."',
				daily_run_hourly = '".($_POST['daily_run_hourly'] == '' ? '0' : sql_friendly($_POST['daily_run_hourly']))."',
				hours_worked = '".($_POST['hours_worked'] == '' ? '0' : sql_friendly($_POST['hours_worked']))."',
				loaded_miles_hourly = '".($_POST['loaded_miles_hourly'] == '' ? '0' : sql_friendly($_POST['loaded_miles_hourly']))."',
				loaded_miles_hourly = '".($_POST['loaded_miles_hourly'] == '' ? '0' : sql_friendly($_POST['loaded_miles_hourly']))."',
				color = '".sql_friendly($_POST['color'])."',
				trailer_id = '".str_replace("'","''",$_POST['trailer_id'])."',
				location = '".str_replace("'","''",$_POST['location'])."',
				miles = '".sql_friendly(money_strip($_POST['miles']))."',
				miles_deadhead = '".sql_friendly($_POST['miles_deadhead'])."',
				dropped_trailer = '".(isset($_POST['dropped_trailer']) ? '1' : '0')."',
				has_load_flag = '".(isset($_POST['has_load_flag']) && !isset($_POST['dispatch_completed']) ? '1' : '0')."',
				dispatch_completed = '".(isset($_POST['dispatch_completed']) ? '1' : '0')."',
				valid_trip_pack = '".(isset($_POST['valid_trip_pack']) ? '1' : '0')."',
				tires_per_mile = '".($_POST['tires_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['tires_per_mile'])))."',
				accidents_per_mile = '".($_POST['accidents_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['accidents_per_mile'])))."',
				mile_exp_per_mile = '".($_POST['mile_exp_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['mile_exp_per_mile'])))."',
				trailer_exp_per_mile = '".($_POST['trailer_exp_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['trailer_exp_per_mile'])))."',
				misc_per_mile = '".($_POST['misc_per_mile'] == '' ? '0' : sql_friendly(money_strip($_POST['misc_per_mile'])))."',
				truck_cost='".sql_friendly($mrr_truck_cost)."',
				trailer_cost='".sql_friendly($mrr_trailer_cost)."',
				employer_id = '".sql_friendly($emp_id)."',
				truck_rental='".sql_friendly($trent_flag)."',
				".$mrr_labor_adder."
				linedate_updated = now()	
				
			where id = '".sql_friendly($_GET['id'])."'
		";
		//die($sql);
		$data_update = simple_query($sql);
		
		/*
		//drop for current trailer is now completed...when attached to a dispatch.................added Jun 2013.........
		if($_POST['trailer_id'] > 0 && $old_trailer_id!=$_POST['trailer_id'])
		{
			//drop for current trailer is now completed...when attached to a dispatch.	
			$sqlx="
				update trailers_dropped set
					drop_completed='1'
				where deleted='0'
					and trailer_id='".sql_friendly($_POST['trailer_id'])."'
					and drop_completed='0'
			";
			simple_query($sqlx);
		}
		//...............................................................................................................
		*/
		
				
		if($_POST['truck_id']!='' && $_POST['truck_id'] > 0)
		{
			$truck_valid_pn=mrr_validate_peoplenet_truck($_POST['truck_id']);
			if($truck_valid_pn > 0)
			{
				$pn_auto_dispatch_link="peoplenet_interface.php?find_load_id=".$_POST['load_id']."&find_truck_id=".$_POST['truck_id']."&auto_run=1";
			}
		}
		
		$sql2 = "
			update load_handler_stops set
				start_trailer_id='".(int)$_POST['trailer_id']."',
				end_trailer_id='".(int)$_POST['trailer_id']."'
			where trucks_log_id = '".sql_friendly($_GET['id'])."'
		";
		//die($sql);
		simple_query($sql2);
		
		
		$mrr_activity_log_notes.="Updated Dispatch ".$_GET['id'].". ";
		
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,((int) $_POST['load_id']),$_GET['id'],0,"Updated Dispatch ".$_GET['id']." Load ".$_POST['load_id']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
				
		if(!isset($_POST['dispatch_completed']) && $_POST['driver_id'] > 0) {
			$sql = "
				update drivers
				set attached_truck_id = '".($_POST['truck_id'] == '' ? '0' : sql_friendly($_POST['truck_id']))."',
					attached_trailer_id = '".str_replace("'","''",$_POST['trailer_id'])."'
				
				where id = '".sql_friendly($_POST['driver_id'])."'
			";
			simple_query($sql);
		}
		/*
		if($_POST['driver_id'] > 0) {
			$sql = "
				update drivers
				set phone_cell = '".sql_friendly($_POST['phone_cell'])."',
					phone_home = '".sql_friendly($_POST['phone_home'])."',
					phone_other = '".sql_friendly($_POST['phone_other'])."'
				where id = '".sql_friendly($_POST['driver_id'])."'
				limit 1
			";
			simple_query($sql);
			
			mrr_add_user_change_log($_SESSION['user_id'],0,$_POST['driver_id'],0,0,0,0,0,"Dispatch phone numbers updated for driver ".$_POST['driver_id']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		}
		if($_POST['driver2_id'] > 0) {
			$sql = "
				update drivers
				set phone_cell = '".sql_friendly($_POST['phone2_cell'])."',
					phone_home = '".sql_friendly($_POST['phone2_home'])."',
					phone_other = '".sql_friendly($_POST['phone2_other'])."'
				where id = '".sql_friendly($_POST['driver2_id'])."'
				limit 1
			";
			simple_query($sql);
			mrr_add_user_change_log($_SESSION['user_id'],0,$_POST['driver2_id'],0,0,0,0,0,"Dispatch phone numbers updated for driver ".$_POST['driver2_id']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		}
		*/
		if($_POST['note'] != '') {
			$sql = "
				insert into trucks_log_notes
					(truck_log_id,
					linedate_added,
					note,
					user_id,
					deleted)
					
				values ('".sql_friendly($_GET['id'])."',
					'".date("Y-m-d", strtotime(str_replace("-","/",$_POST['note_date'])))." ".sql_friendly($_POST['note_time'].":00")."',
					'".sql_friendly($_POST['note'])."',
					'".sql_friendly($_SESSION['user_id'])."',
					0)
			";
			simple_query($sql);
		}
		
		// clear out the stops associated with this dispatch, then add the ones that are checked back in
		$sql = "
			update load_handler_stops
			set trucks_log_id = 0
			where trucks_log_id = '".sql_friendly($_GET['id'])."'
		";
		simple_query($sql);
		if(isset($_POST['stop_id_array'])) {
			foreach($_POST['stop_id_array'] as $value) {
				if($_POST['linedate_completed_'.$value] != '' || (isset($_POST['checkbox_stop']) && in_array($value,$_POST['checkbox_stop']))) {
					$sql = "
						update load_handler_stops
						set trucks_log_id = '".sql_friendly($_GET['id'])."'
						where id = '".sql_friendly($value)."'
					";
					simple_query($sql);
				}
			}
		}
		
		//update the cost for this dispatch when settigns have been saved.		
		$mrr_cd=0;
          $mrr_day_cost=mrr_quick_and_easy_daily_cost($_GET['id'],$mrr_cd);		//if MRR_CD==1, array is returned with each part of the daily cost including a total with the Days Run included....
		$sqlu = "
				update trucks_log
				set daily_cost = '".sql_friendly($mrr_day_cost)."'
				where id = '".sql_friendly($_GET['id'])."'
				limit 1
			";
		simple_query($sqlu);
		
		update_origin_dest($_POST['load_id']);
		
		// only refresh the parent window if not a load handler
		// if this dispatch has a load handler, then we opened the same window
		if(!isset($_POST['add_note']) && $_POST['load_id'] == 0) {
			//parent_window_refresh(false);
		}
		//parent_window_submit(false);
	}

	$mrr_read_only_field="";

	if($_GET['id'] > 0) {
		
		$mrr_activity_log_notes.="View Dispatch ".$_GET['id'].". ";
		
		$sql = "
			select *
			
			from trucks_log
			where id = '".sql_friendly($_GET['id'])."'
		";
		$data = simple_query($sql);
		$row = mysqli_fetch_array($data);
		
		if(mrr_get_user_access_level($_SESSION['user_id']) < 90)
		{
     		$sqlxx = "
     			select id			
     			from last_payroll_report
     			where employer_id = '$row[employer_id]'
     				and linedate > '$row[linedate_pickup_eta]'
     			order by linedate desc
     		";
     		$dataxx = simple_query($sqlxx);
     		if($rowxx = mysqli_fetch_array($dataxx))
     		{	//if found at all, this employer has already had payroll sent... so lock the input fields from editing them...
     			$mrr_read_only_field=" readonly style='color:white; background-color:grey;' title='This field is locked. Please consult your supervisor.'";	
     		}
		}		
		
		$sql = "
			select *
			
			from drivers
			where id = '$row[driver_id]'
		";
		$data_driver_info = simple_query($sql);
		$row_driver_info = mysqli_fetch_array($data_driver_info);
		
		$sql = "
			select *
			
			from drivers
			where id = '$row[driver2_id]'
		";
		$data_driver2_info = simple_query($sql);
		$row_driver2_info = mysqli_fetch_array($data_driver2_info);
		
		//logging stats capture...
		$mrr_activity_log_driver=$row['driver_id'];
     	$mrr_activity_log_truck=$row['truck_id'];
     	$mrr_activity_log_trailer=$row['trailer_id'];
     	$mrr_activity_log_load=$row['load_handler_id'];
     	$mrr_activity_log_dispatch=$_GET['id'];
		
		if($_POST['load_id'] == 0) $_POST['load_id'] = $row['load_handler_id'];
		$_POST['truck_id'] = $row['truck_id'];
		$_POST['driver_id'] = $row['driver_id'];
		$_POST['driver2_id'] = $row['driver2_id'];
		$_POST['customer_id'] = $row['customer_id'];
		$_GET['linedate'] = $row['linedate'];
		$_POST['trailer_id'] = $row['trailer_id'];
		$_POST['location'] = $row['location'];
		$_POST['notes'] = $row['notes'];
		$_POST['color'] = $row['color'];
		$_POST['origin'] = $row['origin'];
		$_POST['origin_state'] = $row['origin_state'];
		$_POST['destination'] = $row['destination'];
		$_POST['destination_state'] = $row['destination_state'];
		$_POST['miles'] = $row['miles'];
		$_POST['manual_miles_flag'] = $row['manual_miles_flag'];
		$_POST['pcm_miles'] = number_format($row['pcm_miles']);
		$_POST['miles_deadhead'] = $row['miles_deadhead'];
		$_POST['dropped_trailer'] = $row['dropped_trailer'];
		$_POST['has_load_flag'] = $row['has_load_flag'];
		$_POST['dispatch_completed'] = $row['dispatch_completed'];
		$_POST['valid_trip_pack'] = $row['valid_trip_pack'];		
		$_POST['daily_run_hourly'] = $row['daily_run_hourly'];
		$_POST['daily_run_otr'] = $row['daily_run_otr'];
		$_POST['loaded_miles_hourly'] = $row['loaded_miles_hourly'];
		$_POST['hours_worked'] = $row['hours_worked'];
		$_POST['otr_daily_cost'] = $row['otr_daily_cost'];
		$_POST['daily_cost'] = $row['daily_cost'];
		$_POST['labor_per_mile'] = $row['labor_per_mile'];
		$_POST['labor_per_hour'] = $row['labor_per_hour'];
		$_POST['tractor_maint_per_mile'] = $row['tractor_maint_per_mile'];
		$_POST['trailer_maint_per_mile'] = $row['trailer_maint_per_mile'];
		
		$_POST['tires_per_mile'] = number_format($row['tires_per_mile'],4);
		$_POST['accidents_per_mile'] = number_format($row['accidents_per_mile'],4);
		$_POST['mile_exp_per_mile'] = number_format($row['mile_exp_per_mile'],4);
		$_POST['trailer_exp_per_mile'] = number_format($row['trailer_exp_per_mile'],4);
		$_POST['misc_per_mile'] = number_format($row['misc_per_mile'],4);
		
		$_POST['truck_cost'] = number_format($row['truck_cost'],2);
		$_POST['trailer_cost'] = number_format($row['trailer_cost'],2);
		
		$_POST['avg_mpg'] = $row['avg_mpg'];	
		
		$_POST['driver_2_labor_per_mile']=$row['driver_2_labor_per_mile'];
		$_POST['driver_2_labor_per_hour']=$row['driver_2_labor_per_hour'];	

	} else {
		if(!isset($_POST['truck_id'])) $_POST['truck_id'] = "";
		$_POST['driver_id'] = "";
		$_POST['driver2_id'] = "";
		$_POST['customer_id'] = "";
		if(!isset($_POST['trailer_id'])) $_POST['trailer_id'] = "";
		$_POST['location'] = "";
		$_POST['notes'] = "";
		$_POST['color'] = "";
		$_POST['origin'] = "";
		$_POST['origin_state'] = "";
		$_POST['destination'] = "";
		$_POST['destination_state'] = "";
		$_POST['miles'] = "";
		$_POST['manual_miles_flag'] = 0;
		$_POST['pcm_miles'] = 0;
		$_POST['miles_deadhead'] = "";
		$_POST['dropped_trailer'] = 0;
		$_POST['has_load_flag'] = 0;
		$_POST['dispatch_completed'] = 0;
		$_POST['valid_trip_pack'] = 0;
		$_POST['daily_run_hourly'] = 0;
		$_POST['daily_run_otr'] = 0;
		$_POST['loaded_miles_hourly'] = 0;
		$_POST['hours_worked'] = 0;
		$_POST['otr_daily_cost'] = 0;
		$_POST['daily_cost'] = "$0.00";
		$_POST['labor_per_mile'] = "$0.00";;
		$_POST['labor_per_hour'] = "$0.00";
		$_POST['tractor_maint_per_mile'] = "$".number_format($defaultsarray['tractor_maint_per_mile'],4);
		$_POST['trailer_maint_per_mile'] = "$".number_format($defaultsarray['trailer_maint_per_mile'],4);
		
		$_POST['tires_per_mile'] =  "$".number_format($defaultsarray['tires_per_mile'],4);
		$_POST['accidents_per_mile'] = "$".number_format($defaultsarray['truck_accidents_per_mile'],4);
		$_POST['mile_exp_per_mile'] = "$".number_format($defaultsarray['mileage_expense_per_mile'],4);
		$_POST['misc_per_mile'] = "$".number_format($defaultsarray['misc_expense_per_mile'],4);
		$_POST['trailer_exp_per_mile'] = "$".number_format($defaultsarray['trailer_mile_exp_per_mile'],4);
		
		$mrr_tractor_lease=mrr_get_option_variable_settings('Tractor Lease');
     	$mrr_trailer_expense=mrr_get_option_variable_settings('Trailer Expense');
		
		$_POST['truck_cost'] = number_format($mrr_tractor_lease,2);
		$_POST['trailer_cost'] = number_format($mrr_trailer_expense,2);
		
		
		$_POST['avg_mpg'] = $defaultsarray['average_mpg'];
		
		$_POST['driver_2_labor_per_mile']="0.00";
		$_POST['driver_2_labor_per_hour']="0.00";	
	}


	
	if(isset($_GET['load_id']) && $_GET['id'] == 0) {
		// this is a new dispatch from a load handler, so pull in the information we have from the load handler into this
		// dispatch (to make it as easy as possible for the user
		
		$sql = "
			select *
			
			from load_handler
			where id = '".sql_friendly($_GET['load_id'])."'
		";
		$data_load_handler = simple_query($sql);
		$row_load_handler = mysqli_fetch_array($data_load_handler);
		
		$_POST['origin'] = $row_load_handler['origin_city'];
		$_POST['origin_state'] = $row_load_handler['origin_state'];
		$_POST['destination'] = $row_load_handler['dest_city'];
		$_POST['destination_state'] = $row_load_handler['dest_state'];
		$_POST['miles'] = $row_load_handler['estimated_miles'];
		$_POST['miles_deadhead'] = $row_load_handler['deadhead_miles'];
		$_POST['customer_id'] = $row_load_handler['customer_id'];


	}
	
	
	if(!isset($_GET['linedate'])) $_GET['linedate'] = date("m/d/Y", time());

	/* get the driver list */
	$sql = "
		select *
		
		from drivers
		where deleted = 0
			or drivers.id = '".sql_friendly($_POST['driver_id'])."'
		order by active desc, name_driver_last, name_driver_first
	";
	$data_drivers = simple_query($sql);
	
	/* get the truck list */
	$sql = "
		select *,
			(select t.name_truck from equipment_history eh, trucks t where eh.equipment_id = t.id and eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_name
		
		from trucks
		where (deleted = 0
				and active = 1)
			or trucks.id = '".sql_friendly($_POST['truck_id'])."'
		order by name_truck
	";
	$data_trucks = simple_query($sql);
	
	/* grab our list of trucks that are currently being used */
	$sql = "
		select distinct truck_id
		
		from trucks, trucks_log
		where trucks.id = trucks_log.truck_id
			and trucks_log.deleted = 0
			and trucks_log.dispatch_completed = 0
	";
	$data_trucks_used = simple_query($sql);	
	
	/* build an array of truck IDs that are currently being used so we can quickly search them later */
	$trucks_array = array();
	while($row_trucks_used = mysqli_fetch_array($data_trucks_used)) {
		$trucks_array[] = $row_trucks_used['truck_id'];
	}	
	

	/* grab our list of trailers that are currently being used */
	$sql = "
		select distinct trailer_id
		
		from trailers, trucks_log
		where trailers.id = trucks_log.trailer_id
			and trucks_log.deleted = 0
			and trucks_log.dispatch_completed = 0
			and trailers.allow_multiple = 0
	";
	$data_trailers_used = simple_query($sql);
	
	/* build an array of trailer IDs that are currently being used so we can quickly search them later */
	$trailer_array = array();
	$trailer_used_array = array();
	while($row_trailers_used = mysqli_fetch_array($data_trailers_used)) {
		if($row_trailers_used['trailer_id'] != $_POST['trailer_id']) $trailer_array[] = $row_trailers_used['trailer_id'];
		$trailer_used_array[] = $row_trailers_used['trailer_id'];
	}
	
	$trailer_available_array = array();
	$data_trailers_available = get_available_trailers();
	while($row_trailer_available = mysqli_fetch_array($data_trailers_available)) {
		$trailer_available_array[] = $row_trailer_available['trailer_id'];
	}
	
	if(count($trailer_available_array) > 0) {
		$trailer_list = implode(",",$trailer_available_array);
		$extra_trailer_sql = "
			and (id in ($trailer_list) or id = '".sql_friendly($_POST['trailer_id'])."')
		";
	} else {
		$extra_trailer_sql = "";
	}
		
	/* get the trailer list */		//left join trailers_dropped td on td.trailer_id=trailers.id
	$sql = "
		select trailers.*
		
		from trailers
			
		where ((trailers.deleted = 0 and trailers.active = 1) or trailers.id = '".$_POST['trailer_id']."')
					
		order by trailers.trailer_name
	";
	$data_trailers = simple_query($sql);
	/*
		and id not in 
				(
					select distinct(trailers_dropped.trailer_id) from trailers_dropped where trailers_dropped.deleted=0 and trailers_dropped.drop_completed=0 order by trailers_dropped.trailer_id asc
				)	
	*/
	
	// get the notes for this truck
	$sql = "
		select *
		
		from trucks_log_notes
		where truck_log_id = '".sql_friendly($_GET['id'])."'
			and linedate_added >= '".date("Y-m-d", strtotime("-6 month", time()))."'
			and deleted = 0
		order by linedate_added desc
		limit 30
	";
	$data_notes = simple_query($sql);
	
	mrr_check_drivers_for_loads();
?>

<form name="mainform" action="<?=$SCRIPT_NAME?>?id=<?=$_GET['id']?>&linedate=<?=$_GET['linedate']?>" method="post">
	
	
<table>
<tr>
	<td valign='top' colspan='2'>
		<div class='nav_bar' style=';position:fixed;top:0px;left;0px;margin-top:0px'>
			<div style='float:left;margin-left:40px'>&nbsp;</div>
			
			<?
			if($_POST['load_id']) {
				echo "
					<div class='toolbar_button' onclick=\"parent_window_submit(true)\" style='width:90px'>
						<div><img src='images/return.png'></div>
						<div>Return to LH</div>
					</div>
				";
			} else {
				echo "
					<div class='toolbar_button' onclick='parent_window_refresh(true)'>
						<div><img src='images/return.png'></div>
						<div>Close</div>
					</div>
				";
			}
			?>
			<!--
			<div class='toolbar_button' onclick='parent_window_refresh(true)'>
				<div><img src='images/return.png'></div>
				<div>Close</div>
			</div>
			-->
			<div class='toolbar_button' onclick="window.location='manage_load.php'" style='width:80px'>
				<div><img src='images/new.png'></div>
				<div>New Load</div>
			</div>
			<!---
			<div class='toolbar_button' onclick="window.location='manage_load.php'" style='width:90px'>
				<div><img src='images/new.png'></div>
				<div>New Dispatch</div>
			</div>
			--->
			<div class='toolbar_button' onclick='CheckSubmit()'>
				<div><img src='images/file.png'></div>
				<div>Save</div>
			</div>
			<? if($_GET['id'] > 0) { ?>
				<div class='toolbar_button' onclick='delete_entry(<?=$_GET['id']?>)'>
					<div><img src='images/delete.png'></div>
					<div>Delete</div>
				</div>
				<!---
				<div class='toolbar_button' style='width:70px' onclick='create_lh_from_dispatch(<?=$_GET['id']?>)'>
					<div><img src='images/copy.png' alt='Create Load Handler from Dispatch' title='Create Load Handler from Dispatch'></div>
					<div>Create LH</div>
				</div>
				--->
				
			<? } ?>
			<!---
			<div class='toolbar_button' onclick="search_full();">
				<div><img src='images/formupdate.png' alt='History' title='History'></div>
				<div>History</div>
			</div>
			--->
		</div>	
		
		<div style='clear:both;height:80px'>&nbsp;</div>		
	</td>
</tr>
<tr>
	<td valign='top'>
		
		
		<input type='hidden' name='load_id' value="<?=$_POST['load_id']?>">
		<table class='standard12 add_entry_truck section0' style='text-align:left'>
		<tr>
			<td nowrap style='width:100px'><b>Load Handler ID</b></td>
			<td align='right' style='width:20px'>
				<?=$_POST['load_id']?>
			</td>
			<td>&nbsp;&nbsp;&nbsp;</td>
			<td>
				<? 
				if($_POST['load_id'] > 0) {
					echo "<a href='manage_load.php?load_id=$_POST[load_id]' target='load_handler_$_POST[load_id]'>View Load Handler</a>";
				}
				?> <?= show_help('add_entry_truck.php','Load Handler') ?>
			</td>
			<td>
				<label><input type='checkbox' name='has_load_flag' id='has_load_flag' <?=($_POST['has_load_flag'] ? 'checked' : '')?>> Has Load</label>
				 <?= show_help('add_entry_truck.php','Has Load checkbox') ?>
			</td>
		</tr>
		<tr>
			<td><b>Entry ID</b></td>
			<td align='right' nowrap>
				<? 
					if($_GET['id'] == 0) {
						echo "New Entry";
					} else {
						echo $_GET['id'];
					}
				?>
			</td>
			<td></td>
			<td>
				<? if($_GET['id'] != 0) echo "<a href='javascript:view_attachments(6,$_GET[id])'>View Attachments</a> "; ?>
				 <?= show_help('add_entry_truck.php','View Attachments') ?>
			</td>
			<td>
				<label><input type='checkbox' name='dropped_trailer' id='dropped_trailer' <?=($_POST['dropped_trailer'] ? 'checked' : '')?>> Dropped Trailer</label>
				 <?= show_help('add_entry_truck.php','Dropped Trailer checkbox') ?>
			</td>
		</tr>
		<tr>
			<td colspan='4'>
				<b>
				<? if($_POST['dispatch_completed']) echo "<img src='images/good.png'> Dispatch has been completed"; ?>
				</b>
			</td>
			

			<td>
				<label><input type='checkbox' name='dispatch_completed' id='dispatch_completed' <?=($_POST['dispatch_completed'] ? 'checked' : '')?> onChange='mrr_warn_drop_trailer()'> Dispatch Completed</label>
				 <?= show_help('add_entry_truck.php','Dispatch Completed checkbox') ?>
			</td>
		</tr>
		<tr>
			<td colspan='4'>&nbsp;</td>
			<td>
				<label><input type='checkbox' name='valid_trip_pack' id='valid_trip_pack' <?=($_POST['valid_trip_pack'] ? 'checked' : '')?> onChange='mrr_trip_pack_log()'> Has Completed Trip Pack</label>
				 <?= show_help('add_entry_truck.php','Trip Pack checkbox') ?>
			</td>
		</tr>		
		</table>
		<table class='standard12 add_entry_truck section2' style='text-align:left'>
		<tr>
			<td><b>Truck</b></td>
			<td nowrap>
				<div id='truck_id_holder'>
					<select name="truck_id" id="id_truck_id" class='standard12 payroll_lock_down'>
					<option value="">Choose Truck
					<? while($row_truck = mysqli_fetch_array($data_trucks)) { ?>
						<option <?=(in_array($row_truck['id'], $trucks_array) ? "class='not_available' " : "class='available'")?> value="<?=$row_truck['id']?>" <? if($_POST['truck_id'] == $row_truck['id']) echo "selected"?>>
							<?=$row_truck['name_truck']?> <?=(in_array($row_truck['id'], $trucks_array) ? "(in use) " : "")?> <?=($row_truck['replacement_truck_name'] != '' ? "(use replacement $row_truck[replacement_truck_name]) " : "")?>
						</option>
					<? } ?>
					</select>
					
					<a href="javascript:view_attachments(3,$('#id_truck_id').val())">View Attachments</a>
					&nbsp;&nbsp;&nbsp;
					<a href="javascript:add_truck_switch(true)">Add Truck</a> <?= show_help('add_entry_truck.php','Trucks') ?>
				</div>
				<div id='truck_id_holder_new'>
					<input name='truck_name' id='truck_name' class='input_normal'>
					<a href="javascript:add_truck_switch(false)">Cancel</a>
				</div>
			</td>
		</tr>
		<tr>
			<td><b>Trailer</b></td>
			<td nowrap>
				<div id='trailer_id_holder'>
					<input type='hidden' name='mrr_trailer_drop_completer' id='mrr_trailer_drop_completer' value='0'>
					<select name="trailer_id" id="id_trailer_id" class='standard12 payroll_lock_down'>
					<option value="">--Unspecified--</option>
					<? while($row_trailers = mysqli_fetch_array($data_trailers)) { ?>
						<option <?=(in_array($row_trailers['id'], $trailer_used_array) ? "class='not_available'" : "class='available'")?> value="<?=$row_trailers['id']?>" <? if($_POST['trailer_id'] == $row_trailers['id']) echo "selected"?> mrr_use='<?=(in_array($row_trailers['id'], $trailer_used_array) ? "used" : "free")?>'>
							<?=$row_trailers['trailer_name']?><?=(in_array($row_trailers['id'], $trailer_used_array) ? " (in use)" : "")?>
						</option>
					<? } ?>
					</select>
		
					<a href="javascript:view_attachments(2,$('#id_trailer_id').val())">View Attachments</a>
					&nbsp;&nbsp;&nbsp;
					<a href="javascript:add_trailer_switch(true)">Add Trailer</a> <?= show_help('add_entry_truck.php','Trailers') ?>
				</div>
				<div id='trailer_id_holder_new'>
					<input name='trailer_name' id='trailer_name' class='input_normal'>
					<a href="javascript:add_trailer_switch(false)">Cancel</a>
				</div>
			</td>
		</tr>
		<tr>
			<td valign='top'><b>Driver</b></td>
			<td>
				<div id='driver_holder'>
					<select name="driver_id" id="id_driver_id" onchange="change_driver()" class='standard12 payroll_lock_down'>
					<option value="">Choose Driver</option>
					<? while($row_driver = mysqli_fetch_array($data_drivers)) { ?>
						<?	
						$opt_styler="";
						if(trim($row_driver['name_driver_last'].", ".$row_driver['name_driver_first'])=="Reminder, Load")		$opt_styler=" class='alert'";
						
						if($row_driver['active']==0)	$opt_styler=" style='color:#aaaaaa;";	
						
						?>					
						<option value="<?=$row_driver['id']?>" <? if($_POST['driver_id'] == $row_driver['id']) echo "selected"?><?=$opt_styler ?>>
							<?=(!$row_driver['active'] ? '(inactive) ' : '')?><?=$row_driver['name_driver_last']?>, <?=$row_driver['name_driver_first']?>
						</option>
					<? } ?>
					</select>
					
					<a href="javascript:view_attachments(1,$('#id_driver_id').val())">View Attachments</a>
					&nbsp;&nbsp;&nbsp;
					<a href='javascript:add_driver()'>Add Driver</a> <?= show_help('add_entry_truck.php','Drivers') ?>
				</div>
				<div id='add_driver' style='display:none'>
					<b>First</b>
					<input name='driver_name_first' id='driver_name_first'>
		
					<b>Last</b>
					<input name='driver_name_last' id='driver_name_last'>
					<a href='javascript:add_driver_cancel()'>Cancel</a>
				</div>
					<table class='standard12'>
					<tr>
						<td><b>Cell Phone:</b></td>
						<td><input name='phone_cell' id='phone_cell' value="<? if(isset($row_driver_info['phone_cell'])) echo $row_driver_info['phone_cell']?>"></td>
					</tr>
					<tr>
						<td><b>Home Phone:</b></td>
						<td><input name='phone_home' id='phone_home' value="<? if(isset($row_driver_info['phone_home'])) echo $row_driver_info['phone_home']?>"></td>
					</tr>
					<tr>
						<td><b>Other Phone:</b></td>
						<td><input name='phone_other' id='phone_other' value="<? if(isset($row_driver_info['phone_other'])) echo $row_driver_info['phone_other']?>"></td>
					</tr>
					</table>
				
			</td>
		</tr>
		<tr>
			<td valign='top'><a href='javascript:add_driver2()'><b>Driver 2</b></a></td>
			<td>
				<div id='driver2_info_holder' style='<? if($_POST['driver2_id'] == '' || $_POST['driver2_id'] == 0) echo "display:none"?>'>
					<div id='driver_holder'>
						<select name="driver2_id" id="id_driver2_id" class='standard12 payroll_lock_down'>
						<option value="">Choose Driver</option>
						<? @mysqli_data_seek($data_drivers,0) ?>
						<? while($row_driver = mysqli_fetch_array($data_drivers)) { ?>
							<option value="<?=$row_driver['id']?>" <? if($_POST['driver2_id'] == $row_driver['id']) echo "selected"?>><?=$row_driver['name_driver_last']?>, <?=$row_driver['name_driver_first']?></option>
						<? } ?>
						</select>
						
						<a href="javascript:view_attachments(1,$('#id_driver2_id').val())">View Attachments</a>
					</div>
					
					<table class='standard12'>
					<tr>
						<td><b>Cell Phone:</b></td>
						<td><input name='phone2_cell' id='phone2_cell' value="<? if(isset($row_driver2_info['phone_cell'])) echo $row_driver2_info['phone_cell']?>"></td>
					</tr>
					<tr>
						<td><b>Home Phone:</b></td>
						<td><input name='phone2_home' id='phone2_home' value="<? if(isset($row_driver2_info['phone_home'])) echo $row_driver2_info['phone_home']?>"></td>
					</tr>
					<tr>
						<td><b>Other Phone:</b></td>
						<td><input name='phone2_other' id='phone2_other' value="<? if(isset($row_driver2_info['phone_other'])) echo $row_driver2_info['phone_other']?>"></td>
					</tr>
					</table>
				</div>
				
			</td>
		</tr>
		</table>

		
	</td>
	<td valign='top'>
		
		
		
		<table class='section1' style='text-align:left'>
		<tr>
			<td><span class='section_heading'>Default Values</span></td>
			<td><span class='alert' id='mrr_team_flag'></span></td>
			<td>&nbsp;</td>
			<td align='right'>
				<span class='mrr_link_like_on' onClick='mrr_show_default_display();'><img src='images/add.gif' style='border:0'></span> 
				<span class='mrr_link_like_on' onClick='mrr_hide_default_display();'><img src='images/delete.gif' style='border:0;'></span>
			</td>
		</tr>
		<tr class='mrr_display_defaults'>
			<td><b>Labor per mile</b> <span style='float:right' id='labor_per_mile_holder'></span> <?= show_help('add_entry_truck.php','Labor per Mile') ?></td>
			<td>
				<input style='text-align:right' name="labor_per_mile" value="<?=($_POST['labor_per_mile'] == '' ? 0 : $_POST['labor_per_mile'])?>" id='labor_per_mile' size='6' class='payroll_lock_down'>
			</td>
			<td><b>Labor per hour</b> <span style='float:right' id='labor_per_hour_holder'></span> <?= show_help('add_entry_truck.php','Labor per Hour') ?></td>
			<td>
				<input style='text-align:right' name="labor_per_hour" value="<?=($_POST['labor_per_hour'] == '' ? 0 : $_POST['labor_per_hour'])?>" id='labor_per_hour' size='6' class='payroll_lock_down'>
			</td>
		</tr>
		<tr class='mrr_display_defaults'>
			<td><b>Truck Maint per mile</b> <?= show_help('add_entry_truck.php','Truck Maint per Mile') ?></td>
			<td>
				<input style='text-align:right' name="tractor_maint_per_mile" value="<?=($_POST['tractor_maint_per_mile'] == '' ? 0 : $_POST['tractor_maint_per_mile'])?>" id='tractor_maint_per_mile' size='6' class='payroll_lock_down'>
			</td>
			<td><b>Trailer Maint per mile</b> <?= show_help('add_entry_truck.php','Trailer Maint per Mile') ?></td>
			<td>
				<input style='text-align:right' name="trailer_maint_per_mile" value="<?=($_POST['trailer_maint_per_mile'] == '' ? 0 : $_POST['trailer_maint_per_mile'])?>" id='trailer_maint_per_mile' size='6' class='payroll_lock_down'>
			</td>
		</tr>
		
		<tr class='mrr_display_defaults'>
			<td><b>Tires per mile</b> <?= show_help('add_entry_truck.php','Tires per Mile') ?></td>
			<td>
				<input style='text-align:right' name="tires_per_mile" value="<?=($_POST['tires_per_mile'] == '' ? 0 : $_POST['tires_per_mile'])?>" id='tires_per_mile' size='6' class='payroll_lock_down'>
			</td>
			<td><b>Accidents per mile</b> <?= show_help('add_entry_truck.php','Accidents per Mile') ?></td>
			<td>
				<input style='text-align:right' name="accidents_per_mile" value="<?=($_POST['accidents_per_mile'] == '' ? 0 : $_POST['accidents_per_mile'])?>" id='accidents_per_mile' size='6' class='payroll_lock_down'>
			</td>
		</tr>
		<tr class='mrr_display_defaults'>
			<td><b>Mileage Exp per mile</b> <?= show_help('add_entry_truck.php','Mileage Exp per Mile') ?></td>
			<td>
				<input style='text-align:right' name="mile_exp_per_mile" value="<?=($_POST['mile_exp_per_mile'] == '' ? 0 : $_POST['mile_exp_per_mile'])?>" id='mile_exp_per_mile' size='6' class='payroll_lock_down'>
			</td>
			<td><b>Misc. Exp per mile</b> <?= show_help('add_entry_truck.php','Misc. Exp per Mile') ?></td>
			<td>
				<input style='text-align:right' name="misc_per_mile" value="<?=($_POST['misc_per_mile'] == '' ? 0 : $_POST['misc_per_mile'])?>" id='misc_per_mile' size='6' class='payroll_lock_down'>
			</td>
		</tr>
		<tr class='mrr_display_defaults'>
			<td><b>Trailer MileExp per mile</b> <?= show_help('add_entry_truck.php','Trailer Mileage Exp per Mile') ?></td>
			<td>
				<input style='text-align:right' name="trailer_exp_per_mile" value="<?=($_POST['trailer_exp_per_mile'] == '' ? 0 : $_POST['trailer_exp_per_mile'])?>" id='trailer_exp_per_mile' size='6' class='payroll_lock_down'>
			</td>
			<td><b>&nbsp;</b></td>
			<td>
				&nbsp;
			</td>
		</tr>
		
		<tr class='mrr_display_defaults'>
			<td><b>Daily Cost</b> <span style='float:right' id='daily_cost_holder'></span> <?= show_help('add_entry_truck.php','Daily Cost') ?></td>
			<td>
				<input style='text-align:right' name="daily_cost" value="<?=($_POST['daily_cost'] == '' ? 0 : $_POST['daily_cost'])?>" id='daily_cost' size='6' class='payroll_lock_down'>
			</td>
			<td><b>Avg MPG <?= show_help('add_entry_truck.php','Avg MPG') ?></td>
			<td>
				<input style='text-align:right' name="avg_mpg" value="<?=($_POST['avg_mpg'] == '' ? 0 : $_POST['avg_mpg'])?>" id='avg_mpg' size='6' class='payroll_lock_down'>
			</td>
		</tr>
		<tr class='mrr_display_defaults'>
			<td><b>Truck Cost</b> <span style='float:right' id='truck_cost_holder'>($<?= $_POST['truck_cost'] ?>)</span> <?= show_help('add_entry_truck.php','Truck Cost') ?></td>
			<td>
				<input style='text-align:right' name="truck_cost" value="<?=$_POST['truck_cost']?>" id='truck_cost' size='6' class='payroll_lock_down'>
			</td>
			<td><b>Trailer Cost</b> <span style='float:right' id='trailer_cost_holder'>($<?= $_POST['trailer_cost'] ?>)</span> <?= show_help('add_entry_truck.php','Trailer Cost') ?></td>
			<td>
				<input style='text-align:right' name="trailer_cost" value="<?=$_POST['trailer_cost']?>" id='trailer_cost' size='6' class='payroll_lock_down'>
			</td>
		</tr>
		<?
			if($_GET['id'] > 0 && $_POST['driver2_id'] > 0)
			{				
				echo "
					<tr class='mrr_display_defaults'>
						<td><b>Driver 1 Labor/Mile</b> ".show_help('add_entry_truck.php','Team Driver 1 Labor/Mile')."</td>
						<td>$".number_format(($_POST['labor_per_mile'] - $_POST['driver_2_labor_per_mile']),3)."</td>
						<td><b>Driver 2 Labor/Mile</b> ".show_help('add_entry_truck.php','Team Driver 2 Labor/Mile')."</td>
						<td>$<input style='text-align:right' name='driver_2_labor_per_mile' value='".number_format($_POST['driver_2_labor_per_mile'],3)."' id='driver_2_labor_per_mile' size='6' class='payroll_lock_down'></td>
					</tr>
					<tr class='mrr_display_defaults'>
						<td><b>Driver 1 Labor/Hour</b> ".show_help('add_entry_truck.php','Team Driver 1 Labor/Hour')."</td>
						<td>$".number_format(($_POST['labor_per_hour'] - $_POST['driver_2_labor_per_hour']),3)."</td>
						<td><b>Driver 2 Labor/Hour</b> ".show_help('add_entry_truck.php','Team Driver 2 Labor/Hour')."</td>
						<td>$<input style='text-align:right' name='driver_2_labor_per_hour' value='".number_format($_POST['driver_2_labor_per_hour'],3)."' id='driver_2_labor_per_hour' size='6' class='payroll_lock_down'></td>
					</tr>
				";
			}
		?>	
		<tr>
			<td><span class='section_heading'>Dispatch Numbers</span></td>
		</tr>
		<tr>
			<td><b>PC*M Miles</b> <?= show_help('add_entry_truck.php','PC*M Miles') ?></td>
			<td>
				<input style='text-align:right' name="pcm_miles" value="<?=($_POST['pcm_miles'] == '' ? 0 : $_POST['pcm_miles'])?>" id='pcm_miles' size='6' disabled readonly>
			</td>
			<td><b>&nbsp;</b></td>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr>
			<td><b>Miles</b> <label><input type='checkbox' name='manual_miles_flag' id='manual_miles_flag' <?=($_POST['manual_miles_flag'] ? 'checked' : '')?> class='payroll_lock_down'<?= (trim($mrr_read_only_field) != "" ? $mrr_read_only_field : " onFocus='mrr_change_miles_or_hours(0);'") ?>> Manual Miles</label>
				 <?= show_help('add_entry_truck.php','Manual Miles checkbox') ?>
				 <input type='hidden' name='mrr_toggle_storage_flag' id='mrr_toggle_storage_flag' value='0'></td>
			<td>
				<input style='text-align:right' name="miles" value="<?=($_POST['miles'] == '' ? 0 : $_POST['miles'])?>" id='miles' size='6' class='payroll_lock_down'<?= (trim($mrr_read_only_field) != "" ? $mrr_read_only_field : " onFocus='mrr_change_miles_or_hours(0);'") ?>>
				<input type='hidden' name='mrr_toggle_storage_miles' id='mrr_toggle_storage_miles' value='0'>
			</td>
			<td><b>Hours Worked</b> <?= show_help('add_entry_truck.php','Hours Worked') ?></td>
			<td>
				<input style='text-align:right' name="hours_worked" value="<?=($_POST['hours_worked'] == '' ? 0 : $_POST['hours_worked'])?>" id='hours_worked' size='6' class='payroll_lock_down'<?= (trim($mrr_read_only_field) != "" ? $mrr_read_only_field : " onFocus='mrr_change_miles_or_hours(1);'") ?>>
				<input type='hidden' name='mrr_toggle_storage_hr_wrk' id='mrr_toggle_storage_hr_wrk' value='0.00'>
			</td>
		</tr>
		<tr>
			<td><b>Deadhead Miles</b> <?= show_help('add_entry_truck.php','Deadhead Miles') ?></td>
			<td>
				<input style='text-align:right' name="miles_deadhead" value="<?=($_POST['miles_deadhead'] == '' ? 0 : $_POST['miles_deadhead'])?>" id='miles_deadhead' size='6' class='payroll_lock_down'<?= (trim($mrr_read_only_field) != "" ? $mrr_read_only_field : " onFocus='mrr_change_miles_or_hours(0);'") ?>>
				<input type='hidden' name='mrr_toggle_storage_mil_dh' id='mrr_toggle_storage_mil_dh' value='0'>
			</td>
			<td><b>Miles Hourly</b> <?= show_help('add_entry_truck.php','Miles Hourly') ?></td>
			<td>
				<input style='text-align:right' name="loaded_miles_hourly" value="<?=($_POST['loaded_miles_hourly'] == '' ? 0 : $_POST['loaded_miles_hourly'])?>" id='loaded_miles_hourly' size='6' class='payroll_lock_down'<?= (trim($mrr_read_only_field) != "" ? $mrr_read_only_field : " onFocus='mrr_change_miles_or_hours(1);'") ?>>
				<input type='hidden' name='mrr_toggle_storage_hr_mil' id='mrr_toggle_storage_hr_mil' value='0'>
			</td>
		</tr>

		<tr>
			<td><b>Days Run OTR</b> <?= show_help('add_entry_truck.php','Days Run OTR') ?></td>
			<td>
				<input style='text-align:right' name="daily_run_otr" value="<?=($_POST['daily_run_otr'] == '' ? 0 : $_POST['daily_run_otr'])?>" id='daily_run_otr' size='6' class='payroll_lock_down'<?= (trim($mrr_read_only_field) != "" ? $mrr_read_only_field : " onFocus='mrr_change_miles_or_hours(0);'") ?>>
				<input type='hidden' name='mrr_toggle_storage_dy_run' id='mrr_toggle_storage_dy_run' value='0.00'>
			</td>

			<td><b>Days Run Hourly</b> <?= show_help('add_entry_truck.php','Days Run Hourly') ?></td>
			<td>
				<input style='text-align:right' name="daily_run_hourly" value="<?=($_POST['daily_run_hourly'] == '' ? 0 : $_POST['daily_run_hourly'])?>" id='daily_run_hourly' size='6' class='payroll_lock_down'<?= (trim($mrr_read_only_field) != "" ? $mrr_read_only_field : " onFocus='mrr_change_miles_or_hours(1);'") ?>>
				<input type='hidden' name='mrr_toggle_storage_hr_run' id='mrr_toggle_storage_hr_run' value='0.00'>
			</td>

		</tr>

		<tr style='display:none'>
			<td valign='top'> <b>Color</b> &nbsp;</td>
			<td valign='top'>
				<input id="item_color" name="color" value="<?=$_POST['color']?>">
				<script type='text/javascript'>
					$(document).ready(function(){
				   		$('#item_color').colorPicker();
				   	})
				</script>
			</td>
		</tr>
		<!--
		<tr>
			<td>&nbsp;</td>
			<td colspan='3'>
				<input type="button" value="Submit / Update" class='standard12' onclick='CheckSubmit()'>
				<input type="button" value="Close" onclick="window.close()" class='standard12'>
				<? if($_GET['id'] > 0) { ?>
					<input type="button" value="Delete Load" onclick="delete_entry(<?=$_GET['id']?>)" class='standard12'>
				<? } ?>
			</td>
		</tr>
		-->
		</table>
		
		<table cellspacing='0' cellpadding='0' class='section3' style='text-align:left'>
		<tr>
			<td colspan='5'>
				<span class='section_heading'>Expenses</span> <?= show_help('add_entry_truck.php','Expenses') ?>
			</td>
		</tr>
		<tr>
			<td>
				Type:<br>
				<? build_option_box('expense_type','','expense_type') ?>
			</td>
			<td>
				Amount:<br>
				<input name='expense_amount' id='expense_amount' style='width:80px'>
			</td>
			<td>
				Desc:<br>
				<input name='expense_desc' id='expense_desc'>
			</td>
		
			<td>
				<!-- <img src='images/add.gif' alt='Add Expense' title='Add Expense'> -->
				<input type='button' value='Add Expense' onclick='add_expense()'>
			</td>			
		
		</tr>
		<tr>
			<td colspan='10'>
				<span id='expense_holder'></span>
			</td>
		</tr>
		</table>
		
		<table cellspacing='0' cellpadding='0' class='section3' style='text-align:left'>
		<tr>
			<td style='width:50px'><b>Day</b></td>
			<td style='width:75px' align='right'><b>Date</b></td>
			<td style='width:75px' align='right'><b>Time</b></td>
			<td style='width:20px'></td>
			<td style='margin-left:20px'><b>Note</b></td>
			<td></td>
		</tr>
		<tr>
			<td> <?= show_help('add_entry_truck.php','Days') ?></td>
			<td align='right'><input name='note_date' style='width:50px' value='<?=date("n-j-y", time())?>'></td>
			<td align='right'><input name='note_time' style='width:50px' id='note_time' value='<?=date("H:i", time())?>' class='input_time'></td>
			<td></td>
			<td colspan='2'>
				<input name='note' style='width:250px' onblur="initialCap(this)">
				<input type='submit' value='Add Note' name="add_note">
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td colspan='4'>
				<input type="button" value="Submit / Update" class='standard12' onclick='CheckSubmit()'>
				<input type="button" value="Close" onclick="window.close()" class='standard12'>
				<? if($_GET['id'] > 0) { ?>
					<input type="button" value="Delete Load" onclick="delete_entry(<?=$_GET['id']?>)" class='standard12'>
				<? } ?>
			</td>
		</tr>
		<? 
		while($row_notes = mysqli_fetch_array($data_notes)) { 
			echo "
				<tr id='note_$row_notes[id]'>
					<td>".date("D", strtotime($row_notes['linedate_added']))."</td>
					<td align='right'>".date("n-j-y", strtotime($row_notes['linedate_added']))."</td>
					<td align='right'>".date("H:i", strtotime($row_notes['linedate_added']))."</td>
					<td></td>
					<td>$row_notes[note]</td>
					<td><a href='javascript:delete_note($row_notes[id])'><img src='images/delete_sm.gif' border='0'></a></td>
				</tr>
			";
		}
		?>		
		</table>		
				
		
	</td>
</tr>
<tr>
	<td valign='top' colspan='2'>
		<div class='section1' style='width:1600px;'>
			<span id='stop_holder'></span>
		</div>
		
	</td>
</tr>
<tr>
	<td valign='top'>
		<div style=';border:1px black solid;margin:5px 0 0 3px;text-align:left'>
			<p style='text-align:center'>Truck History</p>
			<div id='truck_history' style=';text-align:left;float:left;margin:5px'></div>
			<div style='clear:both'></div>
		</div>	
		<div id='attachment_modal'></div>	
	</td>
	<td valign='top'>
		
		<table class='section1' style='text-align:left'>
		<tr>
			<td><b>Location</b> <?= show_help('add_entry_truck.php','Location') ?></td>
			<td colspan='3'><input name="location" value="<?=$_POST['location']?>" size="68" class='standard12' id="id_location" onblur="initialCap(this)"></td>
		</tr>
		</table>
		
		<div class='change_log'>
					<?
					if($_GET['id'] > 0)  echo mrr_get_user_change_log(" and user_change_log.dispatch_id='".sql_friendly($_GET['id'])."'"," order by user_change_log.linedate_added asc","",1); 
					?>
		</div>
		
		<div style='border:1px black solid;margin:5px 0 0 3px;text-align:left'>
			<p style='text-align:center'>Driver History</p>
			
			<div id='driver_history' style=';text-align:left;float:left;margin:5px'></div>
			<div style='clear:both'></div>
		</div>
	</td>
</tr>
</table>


</form>


<?
	//add user action to log...
     $mrr_activity_log_user=(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0');
     $mrr_activity_log_self=(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
     $mrr_activity_log_query=(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');
     mrr_set_user_action_log($mrr_activity_log_user,$mrr_activity_log_self,$mrr_activity_log_query,$mrr_activity_log_refer,$mrr_activity_log_driver,$mrr_activity_log_truck,$mrr_activity_log_trailer,
     						$mrr_activity_log_load,$mrr_activity_log_dispatch,$mrr_activity_log_stop,$mrr_activity_log_notes);		//values initialized in application.php
?>



<script type='text/javascript'>
	var phone_cell = new Array();
	var phone_home = new Array();
	var phone_other = new Array();
	var load_id = <?=$_POST['load_id']?>;
	var dispatch_id = <?=$_GET['id']?>;
	<? 
	@mysqli_data_seek($data_drivers,0);
	while($row_driver = mysqli_fetch_array($data_drivers)) {
		echo "
			phone_cell[$row_driver[id]] = '$row_driver[phone_cell]';
			phone_home[$row_driver[id]] = '$row_driver[phone_home]';
			phone_other[$row_driver[id]] = '$row_driver[phone_other]';
		";
	}
	?>
		
	$('#id_trailer_id').change(function() {
		update_daily_cost();
		
		$('#mrr_trailer_drop_completer').val(0);
		
		var opt_val=$(this).val();
          var class_value = $('option:selected', this).attr('mrr_use');
          
		if(class_value=='free')
		{
			$('#mrr_trailer_drop_completer').val(opt_val);
		}		
	});
	
	$('#id_truck_id').change(function() {
		load_truck_history($(this).val());
	});
	
	$('#id_driver_id').change(function() {
		// load the driver history
		load_driver_history($(this).val());
		set_phone($(this).val());
		
		mrr_look_up_charge_rate();
	});
	
	$('#id_driver2_id').change(function() {
		// load the driver history
		set_phone2($(this).val());
		
		mrr_look_up_charge_rate();
	});	
	
	var team_toggle_flag=0;
	
	$().ready(function() {
		$('#ajax_time_keeper').html('');
		var startTime = new Date();	
		
		var startTime1 = new Date();	
		team_toggle_flag=($('#id_driver2_id').val() ? $('#id_driver2_id').val() : 0);	//get value of flag to start	
		
		mrr_hide_default_display();	
		var endTime1 = new Date();
		
		
		var startTime2 = new Date();	
		<?
		if(trim($pn_auto_dispatch_link)!="")
		{
			echo "
				mrr_update_pn_dispatch_by_link();
			";
		}
		?>
		var endTime2 = new Date();
		
		var startTime3 = new Date();	
		if($('#id_driver_id').val() > 0) load_driver_history($('#id_driver_id').val());
		if($('#id_truck_id').val() > 0) load_truck_history($('#id_truck_id').val());
		//$('.input_time').timeEntry({show24Hours:true});
		$('.input_time').blur(simple_time_check);
		var endTime3 = new Date();
		
		
		var startTime4 = new Date();	
		load_stops();
		load_dispatch_expenses();
		manual_miles_check();
		var endTime4 = new Date();
		
		
		var endTime = new Date();		//getTime function returns milliseconds.  1 milli = 0.001 seconds
		mrr_cur_timer_diff=endTime.getTime()/1000 - startTime.getTime()/1000;
		
		mrr_cur_timer_diff1=endTime1.getTime()/1000 - startTime1.getTime()/1000;		
		mrr_cur_timer_diff2=endTime2.getTime()/1000 - startTime2.getTime()/1000;		
		mrr_cur_timer_diff3=endTime3.getTime()/1000 - startTime3.getTime()/1000;		
		mrr_cur_timer_diff4=endTime4.getTime()/1000 - startTime4.getTime()/1000;
			
		
		
		time_report="";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>Toggle/Display Section Start Time = "+startTime1.getTime()/1000+".";		
		time_report= time_report + "<br>Toggle/Display Section End Time = "+endTime1.getTime()/1000+".";
		time_report= time_report + "<br>Toggle/Display Section Total Time = <b>"+mrr_cur_timer_diff1+"</b> Seconds.";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>PN Dispatch Section Start Time = "+startTime2.getTime()/1000+".";		
		time_report= time_report + "<br>PN Dispatch Section End Time = "+endTime2.getTime()/1000+".";
		time_report= time_report + "<br>PN Dispatch Section Total Time = <b>"+mrr_cur_timer_diff2+"</b> Seconds.";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>History Section Start Time = "+startTime3.getTime()/1000+".";		
		time_report= time_report + "<br>History Section End Time = "+endTime3.getTime()/1000+".";
		time_report= time_report + "<br>History Section Total Time = <b>"+mrr_cur_timer_diff3+"</b> Seconds.";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>Stop/Expense Section Start Time = "+startTime4.getTime()/1000+".";		
		time_report= time_report + "<br>Stop/Expense Section End Time = "+endTime4.getTime()/1000+".";
		time_report= time_report + "<br>Stop/Expense Section Total Time = <b>"+mrr_cur_timer_diff4+"</b> Seconds.";
		time_report= time_report + "<br><hr>";	
		time_report= time_report + "<br>Ajax Start Time = "+startTime.getTime()/1000+".";		
		time_report= time_report + "<br>Ajax End Time = "+endTime.getTime()/1000+".";
		time_report= time_report + "<br>Ajax Total Time = <b>"+mrr_cur_timer_diff+"</b> Seconds.";
		
		$('#ajax_time_keeper').html('<b>Ajax Ready Time Report:</b> '+time_report+'');
	});	
	
	function mrr_show_default_display()
	{
		$('.mrr_display_defaults').show();		
	}
	function mrr_hide_default_display()
	{
		$('.mrr_display_defaults').hide();		
	}
	function mrr_warn_drop_trailer()
	{
		disp_complete=0;
		if($('#dispatch_completed').attr('checked')==true)		disp_complete=1;
		
		if(disp_complete==1)
		{
			$.prompt("Now that the dispatch is complete, please check the Dropped Trailer box if this has been done.");		
		}
	}
	
	function mrr_look_up_charge_rate()
	{
		driver1=($('#id_driver_id').val() ? $('#id_driver_id').val() : 0);
		driver2=($('#id_driver2_id').val() ? $('#id_driver2_id').val() : 0);
		
		$('#mrr_team_flag').html('');
		if(team_toggle_flag==1)		$('#mrr_team_flag').html('Team Drivers');
		
		if(team_toggle_flag==0)		driver2=0;
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_pull_driver_charge_rate",
			   data: {
			   		"driver1_id":driver1,
			   		"driver2_id":driver2
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
     				rate1=$(xml).find('PayRateMile').text();
     				rate2=$(xml).find('PayRateHour').text();
     				
     				$('#labor_per_mile').val(rate1);
     				$('#labor_per_hour').val(rate2);    						
			   }
			 });	
	}
	
	
	
	<? if(trim($mrr_read_only_field) != "") { ?>
		
		$(".payroll_lock_down").attr('disabled','disabled');
		$(".payroll_lock_down").attr('title','This field has been disabled.  Please consult your supervisor if you need to make changes to this setting.');
		
	<? } else { ?>
		
		$(".payroll_lock_down").attr('disabled','');
		$(".payroll_lock_down").attr('title','');
		
	<? } ?>	
	
	function mrr_change_miles_or_hours(cd)
	{
		mrr_milecheck=0;
		
		if($('#manual_miles_flag').is(':checked'))
		{
			mrr_milecheck=1;							//checkbox for manual miles
		} 
		mrr_miles_man=$('#miles').val();					//miles	
		mrr_miles_ded=$('#miles_deadhead').val();			//deadhead miles
		mrr_daily_run=$('#daily_run_otr').val();			//OTR run
		
		mrr_hours_wrk=$('#hours_worked').val();				//hours
		mrr_hours_mil=$('#loaded_miles_hourly').val();		//miles for hourly
		mrr_hours_run=$('#daily_run_hourly').val();			//hourly run
		
		storage_flag=$('#mrr_toggle_storage_flag').val();
		storage_miles=$('#mrr_toggle_storage_miles').val();
		storage_hr_wrk=$('#mrr_toggle_storage_hr_wrk').val();
		storage_mil_dh=$('#mrr_toggle_storage_mil_dh').val();
		storage_hr_mil=$('#mrr_toggle_storage_hr_mil').val();
		storage_dy_run=$('#mrr_toggle_storage_dy_run').val();
		storage_hr_run=$('#mrr_toggle_storage_hr_run').val();
						
		if(cd==1)
		{	//this is changing hours to cancel miles.
			//if(mrr_hours_wrk > 0 || mrr_hours_mil > 0 || mrr_hours_run > 0)
			if(mrr_miles_man > 0 || mrr_miles_ded > 0 || mrr_daily_run > 0)
			{			
     			$.prompt("Please use <span class='alert'>Miles or Hourly</span> values for this dispatch.<br>Are you sure you want to clear these <span class='alert'>Miles</span>?", {
          				buttons: {Yes: true, No:false},
          				submit: function(v, m, f) {
          					if(v) {
          						$('#mrr_toggle_storage_flag').val(mrr_milecheck);
                              		$('#mrr_toggle_storage_miles').val(mrr_miles_man);
                              		$('#mrr_toggle_storage_mil_dh').val(mrr_miles_ded);
                              		$('#mrr_toggle_storage_dy_run').val(mrr_daily_run);
                              		
                              		//$('#manual_miles_flag').attr('checked','');		//checkbox for manual miles
                              		$('#miles').val('0');						//miles	
								$('#miles_deadhead').val('0');				//deadhead miles
								$('#daily_run_otr').val('0.00');				//OTR run
								
								$('#hours_worked').val(storage_hr_wrk);			//hours
								$('#loaded_miles_hourly').val(storage_hr_mil);	//miles for hourly
								$('#daily_run_hourly').val(storage_hr_run);		//hourly run
          					}
          				}
          			}
          		);
     		}		
		}
		else
		{	//this is changing miles to cancel hours.
			//if(mrr_miles_man > 0 || mrr_miles_ded > 0 || mrr_daily_run > 0)
			if(mrr_hours_wrk > 0 || mrr_hours_mil > 0 || mrr_hours_run > 0)
			{
     			$.prompt("Please use <span class='alert'>Miles or Hourly</span> values for this dispatch.<br>Are you sure you want to clear these <span class='alert'>Hours</span>?", {
          				buttons: {Yes: true, No:false},
          				submit: function(v, m, f) {
          					if(v) {
          						$('#mrr_toggle_storage_hr_wrk').val(mrr_hours_wrk);
                              		$('#mrr_toggle_storage_hr_mil').val(mrr_hours_mil);
                              		$('#mrr_toggle_storage_hr_run').val(mrr_hours_run);
                              		
                              		//$('#manual_miles_flag').attr('checked',(storage_flag == 1 ? 'checked' : ''));
                              		$('#miles').val(storage_miles);				//miles	
								$('#miles_deadhead').val(storage_mil_dh);		//deadhead miles
								$('#daily_run_otr').val(storage_dy_run);		//OTR run
								
								$('#hours_worked').val('0.00');				//hours
								$('#loaded_miles_hourly').val('0');			//miles for hourly
								$('#daily_run_hourly').val('0.00');			//hourly run
          					}
          				}
          			}
          		);	
     		}
		}	
	}
	function mrr_update_pn_dispatch_by_link()
	{		
		url=	"<?= $pn_auto_dispatch_link ?>";
		//window.open(url,"_blank" );
		//window.opener.location.href = url;
		
		$.ajax({
			   type: "POST",
			   url: url,
			   data: {
			   		
			   		},		   
			   dataType: "html",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		$.noticeAdd({text: "PeopleNet Dispatch updated successfully."});
     					
			   }	
			 });
		
	}
	function mrr_trip_pack_log()
	{
		var on_off=($('#valid_trip_pack').is(':checked') ? 1 : 0);
		loader=load_id;
		disper=dispatch_id;
		trucker=$('#id_truck_id').val();
		driver=$('#id_driver_id').val();		
		//alert('Check Box Value is='+on_off+'.');
		if(on_off==1)
		{
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=add_trip_packs",
			   data: {
			   		"load_id":loader,
			   		"dispatch_id":disper,
			   		"truck_id":trucker,
			   		"driver_id":driver
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
     				newid=$(xml).find('TripPackID').text();
     				if(newid ==0)
     				{
     					$.prompt("Trip Pack Log could not be saved.");	
     				}
     				else
     				{
     					$.noticeAdd({text: "Trip Pack Log has been made for the report."});			
     				}     						
			   }
			 });
		}
		else
		{		
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=kill_trip_packs_alt",
			   data: {
			   		"load_id":loader,
			   		"dispatch_id":disper
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
			   		newid=$(xml).find('TripPackLoad').text();
     				if(newid > 0)
     				{
     					$.noticeAdd({text: "Trip Pack Log has been removed from report."});			
     				} 
			   }
			 });				
		}			
	}
	
	
	function load_driver_history(driver_id) {
		$('#driver_history').html("<img src='images/loader.gif'>");
		
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=load_driver_history",
			   data: {"driver_id":driver_id},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
				$('#driver_history').html($(xml).find('DispHTML').text());
				
				pay_per_mile = $(xml).find('PayPerMile').text();
				pay_per_hour = $(xml).find('PayPerHour').text();
				
				if(get_amount($('#labor_per_mile').val()) == 0) $('#labor_per_mile').val(pay_per_mile);
				if(get_amount($('#labor_per_hour').val()) == 0) $('#labor_per_hour').val(formatCurrency(pay_per_hour));
				
				if(get_amount(pay_per_mile) != get_amount($('#labor_per_mile').val())) {
					$('#labor_per_mile_holder').addClass('alert');
				} else {
					$('#labor_per_mile_holder').removeClass('alert');
				}
				
				if(get_amount(pay_per_hour) != get_amount($('#labor_per_hour').val())) {
					$('#labor_per_hour_holder').addClass('alert');
				} else {
					$('#labor_per_hour_holder').removeClass('alert');
				}				
				
				$('#labor_per_mile_holder').html("("+$(xml).find('PayPerMile').text()+")");
				$('#labor_per_hour_holder').html("("+formatCurrency($(xml).find('PayPerHour').text())+")");
			   }
			 });
	}
	
	function load_truck_history(truck_id) {
		$('#truck_history').html("<img src='images/loader.gif'>");
		
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=load_truck_history",
			   data: {"truck_id":truck_id},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
			   	
				$('#truck_history').html($(xml).find('DispHTML').text());
				update_daily_cost();
			   }
			 });
	}
		
	function add_driver2() {
		$('#driver2_info_holder').toggle();
		if(team_toggle_flag==1)
		{
			team_toggle_flag=0;		
		}		
		else
		{	
			team_toggle_flag=1;		
		}
		mrr_look_up_charge_rate();		
	}
	
	function update_daily_cost() {
		if($('#id_truck_id').val() > 0 && $('#id_trailer_id').val() > 0) {
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=get_daily_cost_ajax",
			   data: {"truck_id":$('#id_truck_id').val(),
			   		"trailer_id":$('#id_trailer_id').val(),
			   		"load_id": load_id,
			   		"dispatch_id": dispatch_id
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
				//$('#truck_history').html($(xml).find('DispHTML').text());
				daily_cost = get_amount($(xml).find("DailyCost").text());
				
				
				mrr_cost_truck   = get_amount($(xml).find("TruckCost").text());
				mrr_cost_trailer = get_amount($(xml).find("TrailerCost").text());
				mrr_cost_daily   = get_amount($(xml).find("DailyShow").text());
				
				if(mrr_cost_truck > 0)			$('#truck_cost').val(formatCurrency(mrr_cost_truck));
				if(mrr_cost_trailer > 0)			$('#trailer_cost').val(formatCurrency(mrr_cost_trailer));
				if(mrr_cost_daily > 0)			$('#daily_cost').val(formatCurrency(mrr_cost_daily));
				
								
				if(get_amount($('#daily_cost').val()) == 0) $('#daily_cost').val(formatCurrency(daily_cost));
				
				current_daily_cost = get_amount($('#daily_cost').val());
				
				$('#daily_cost_holder').html("("+formatCurrency(daily_cost) + ")");
				if(current_daily_cost.toFixed(2) != daily_cost.toFixed(2)) {
					$('#daily_cost_holder').addClass('alert');
				} else {
					$('#daily_cost_holder').removeClass('alert');
				}
			   }
			 });
		}
	}
	
	function set_phone(id) {
		if(id == '') {
			$('#phone_cell').val("");
			$('#phone_home').val("");
			$('#phone_other').val("");
		} else {
			$('#phone_cell').val(phone_cell[id]);
			$('#phone_home').val(phone_home[id]);
			$('#phone_other').val(phone_other[id]);
		}
	}
	
	function set_phone2(id) {
		if(id == '') {
			$('#phone2_cell').val("");
			$('#phone2_home').val("");
			$('#phone2_other').val("");
		} else {
			$('#phone2_cell').val(phone_cell[id]);
			$('#phone2_home').val(phone_home[id]);
			$('#phone2_other').val(phone_other[id]);
		}
	}	
	
	function delete_note(id) {
		if(confirm("Are you sure you want to delete this note?")) {
			$('#note_'+id).remove();
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=delete_note",
			   data: {"note_id":id},
			   success: function(data) {

			   }
			 });
		}
	}
	
	function CheckSubmit() {
		/* validate our form fields before submitting the request */
		var required_array = new Array(2);
		var required_array_msg = new Array(required_array.length);
		
		required_array[0] = "";
		required_array_msg[0] = "";

		submit_form = 1;
		
		if(!$('#dropped_trailer').attr('checked')) {
			if($('#id_driver_id').val() == 0 && $('#driver_name_first').val() == '') {
				alert("Driver is a required field");
				return;
			}
			if($('#id_truck_id').val() == 0 && $('#truck_name').val() == '') {
				alert("Truck is a required field");
				return;
			}
		}
		if($('#id_trailer_id').val() == 0 && $('#trailer_name').val() == '') {
			alert("Trailer is a required field");
			return;
		}
		
		//confirm if trailer should be drop completed or not...
		if($('#mrr_trailer_drop_completer').val() > 0)
		{
			mrr_ret=confirm("Do you want toe complete the drop for this trailer and move it from the dropped section? Press OK to Complete the Drop, and Cancel to leave it in the dropped section.");
			
			if(mrr_ret)
			{
				$.ajax({
			   		type: "POST",
			   		url: "ajax.php?cmd=mrr_auto_trailer_drop_complete",
			   		data: {
			   			"trailer_id":$('#mrr_trailer_drop_completer').val()
			   			},
			   		success: function(data) {
	
			  		 }
			 	});	
			}
		}
		


		


		
		
		for(i=0;i<required_array.length-1;i++) {
			if(required_array[i] != '' && document.getElementById(required_array[i]).value == '') {
				/* display our error to the user and focus on the problem field */
				alert("'"+required_array_msg[i]+ "' is a required field");
				document.getElementById(required_array[i]).focus();
				/* set our flag to not submit the form due to errors */
				submit_form = 0;
				/* break out of our loop so we don't show the user potentially numerous errors */
				break;
			}
		}
		
		if(submit_form == 1) {
			document.mainform.submit();
		}
	}
	
	function delete_entry(id) {
		if(confirm("Are you sure you want to delete this entry?" )) {
			window.location = "<?=$SCRIPT_NAME?>?did=" + id;
		}
	}
	
	function add_driver() {
		$('#phone_cell').val("");
		$('#phone_home').val("");
		$('#phone_other').val("");
		
		$('#driver_holder').hide();
		$('#add_driver').show();
		$('#driver_name_first').focus();
		
	}
	
	function add_driver_cancel() {
		set_phone($('#id_driver_id').val());
		$('#add_driver').hide();
		$('#driver_holder').show();
		$('#driver_name_first').val("");
		$('#driver_name_last').val("");
	}
	
	function add_truck_switch(new_flag) {
		if(new_flag) {
			$('#truck_id_holder').hide();
			$('#truck_id_holder_new').show();
			$('#truck_name').focus();
		} else {
			$('#truck_id_holder_new').hide();
			$('#truck_id_holder').show();
			$('#truck_name').val("");
		}
	}
	function add_trailer_switch(new_flag) {
		if(new_flag) {
			$('#trailer_id_holder').hide();
			$('#trailer_id_holder_new').show();
			$('#trailer_name').focus();
		} else {
			$('#trailer_id_holder_new').hide();
			$('#trailer_id_holder').show();
			$('#trailer_name').val("");
		}
	}
	function add_customer_switch(new_flag) {
		if(new_flag) {
			$('#customer_id_holder').hide();
			$('#customer_id_holder_new').show();
			$('#customer_name').focus();
		} else {
			$('#customer_id_holder_new').hide();
			$('#customer_id_holder').show();
			$('#customer_name').val("");
		}
	}
	
	function view_attachments(section_id, xref_id) {
		if(xref_id == 0 || xref_id == '') {
			alert("No documents found");
			return;
		}
		
		$('#attachment_modal').html("<div class='close'></div><div id='upload_section'></div>");
		create_upload_section('#upload_section', section_id, xref_id);
		
		$('#attachment_modal .close').click(function() {
			$('#attachment_modal').overlay().close();
		});
		
		$("#attachment_modal").overlay({
		    expose: { 
		        color: '#333', 
		        loadSpeed: 200, 
		        opacity: 0.9 
		    }, 
		    api:true,
		    top:75,
		    onClose: function() {
				$('#attachment_modal').html("");
			}
		}).load();
		
	}
	
	function create_lh_from_dispatch(dispatch_id) {
		$.prompt("Are you sure you want to <span class='alert'>create</span> a new Load Handler from this dispatch?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						window.location = 'manage_load.php?create_from_dispatch_id='+dispatch_id;
					}
				}
			}
		);	
		
	}
	
	$('#linedate').datepicker({
		numberOfMonths:1
	});
	
	
	
	function manual_miles_check() {
		if($('#manual_miles_flag').attr('checked')) {
			<? if(trim($mrr_read_only_field)=="") { ?>
				$('#miles').attr('readonly','');
				$('#miles').removeClass('disabled');
				//$('#miles').attr('disabled','');
			<? } ?>
		} else {
			<? if(trim($mrr_read_only_field)=="") { ?>
				$('#miles').attr('readonly','readonly');
				$('#miles').addClass('disabled');
				//$('#miles').attr('disabled','disabled');
				$('#miles').val($('#pcm_miles').val());
			<? } ?>
		}
	}

	$('#manual_miles_flag').click(manual_miles_check);

	$('.toolbar_button').hover(
		function() {
			$(this).addClass('toolbar_button_hover');
		},
		function() {
			$(this).removeClass('toolbar_button_hover');
		}
	);

	function swap_origin_dest() {
		origin_city = $('#origin').val();
		origin_state = $('#origin_state').val();
		
		$('#origin').val($('#destination').val());
		$('#origin_state').val($('#destination_state').val());
		
		$('#destination').val(origin_city);
		$('#destination_state').val(origin_state);
	}

	function load_stops() {
		
		if(load_id == 0) return false;
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_stops",
		   data: {load_id:load_id,
		   		dispatch_id:dispatch_id},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
			$('#stop_holder').html($(xml).find('DispHTML').text());
			
			$('.date_picker_completed').datepicker();
			//$('.time_picker_completed').timeEntry({show24Hours:true});
			$('.time_picker_completed').blur(simple_time_check);
			
			<? 
			if(isset($dispatch_count) && $dispatch_count == 0) {
				echo "
					$(\"input[name='checkbox_stop[]']\").attr('checked','checked');
				";
			}
			?>
		   }
		 });	
	}

	function add_expense() {
		
		if(dispatch_id == 0) {
			$.prompt("You must save the dispatch before you can add expenses to it");
			return false;
		}
		
		if($('#expense_type').val() == 0) {
			$.prompt("You must specify the 'Expense Type'");
			return false;
		} 

		//alert($('#expense_desc').val().length + ' | (' + $('#expense_type :selected').text().toLowerCase().trim() + ')');



		if($('#expense_type :selected').text().toLowerCase().trim() == 'misc' && $('#expense_desc').val().length < 5) {
			$.prompt("You must enter the expense description if you select the 'Misc' expense type");
			return false;
		}
		
		if(isNaN($('#expense_amount').val()) || $('#expense_amount').val() == '') {
			$.prompt("You must specify an expense amount");
			return false;
		}
		
		
		
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=add_dispatch_expense",
		   data: {"dispatch_id":dispatch_id,
		   	expense_type:$('#expense_type').val(),
		   	expense_amount:$('#expense_amount').val(),
		   	expense_desc:$('#expense_desc').val()},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   	
			$('#expense_type').val(0);
			$('#expense_amount').val('');
			$('#expense_desc').val('');
			
			load_dispatch_expenses();

		   }
		 });
	}
	
	function load_dispatch_expenses() {
		
		if(dispatch_id == 0) return false;
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_dispatch_expenses",
		   data: {"dispatch_id":dispatch_id},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
				$('#expense_holder').html($(xml).find('HTML').text());
		   }
		 });
	}	

	function load_dispatchs() {
		// a place holder function - don't remove
	}

	function change_driver() {
		 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=load_attached_equipment",
			   data: {driver_id:$('#id_driver_id').val()},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
			   		if($(xml).find("AttachedTruckID").text() != 0) $('#id_truck_id').val($(xml).find("AttachedTruckID").text());
			   		//if($(xml).find("AttachedTrailerID").text() != 0) $('#id_trailer_id').val($(xml).find("AttachedTrailerID").text());
					//$('#expense_holder').html($(xml).find('HTML').text());
					
					load_truck_history($(xml).find("AttachedTruckID").text());
			   }
		 });
	}
</script>
<?
/* Page Load speed checking code. */
$mrr_debug_time_end=date("His");
echo "
	<br><b>PHP Page Load:</b>
	<br>Start Time: ".$mrr_debug_time_start."
	<br>End Time: ".$mrr_debug_time_end."
	<br>Load Time: ".number_format(($mrr_debug_time_end - $mrr_debug_time_start),4)." Seconds.
	<br><div id='ajax_time_keeper'></div>
";
?>