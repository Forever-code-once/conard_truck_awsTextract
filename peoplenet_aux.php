<? include('application.php') ?>
<?
if(!isset($_GET['connect_key']) || $_GET['connect_key'] != 'bas82bad98fqhbnwga8shq34908asdhbn') {
	die("You have reached this page incorrectly.");
}	
	/*******************************************************************************************\ 
	NOTICE:
	Secondary processing file for PeopleNet (and related) auto-processing.  
	This page is run by cron-job or scheduled tasks.
	\*******************************************************************************************/
			

	die('<br>Done.<br>');	//Turned this off, and just in case it runs somewhere else, disabling the page.  As of 6/28/2018...MRR.  Replaced by GeoTab

	
	//purge large tables...
	$purge_date="".date("Y-m-d",strtotime("-30 days",time()))."";						$del_row_limit=10000;
	
	$sqld = "delete from ".mrr_find_log_database_name()."truck_tracking where linedate_added<'".$purge_date." 00:00:00' LIMIT ".$del_row_limit."";
	simple_query($sqld);
	$sqld = "delete from ".mrr_find_log_database_name()."truck_tracking_drivers where linedate_added<'".$purge_date." 00:00:00' LIMIT ".$del_row_limit."";
	simple_query($sqld);
	$sqld = "delete from ".mrr_find_log_database_name()."truck_tracking_packets where linedate_added<'".$purge_date." 00:00:00' LIMIT ".$del_row_limit."";
	simple_query($sqld);	
	$sqld = "delete from ".mrr_find_log_database_name()."truck_tracking_packet_xml where linedate_added<'".$purge_date." 00:00:00' LIMIT ".$del_row_limit."";
	simple_query($sqld);
	
	$sqld = "delete from ".mrr_find_log_database_name()."truck_tracking_dispatch_xml where linedate_added<'".$purge_date." 00:00:00' LIMIT ".$del_row_limit."";
	simple_query($sqld);
	
	$purge_start_date="".date("Y-m-d",strtotime("-45 days",time()))."";
	$purge_end_date = "".date("Y-m-d",strtotime("-15 days",time()))."";
	//$rep=mrr_purge_all_truck_odometer_readings($purge_start_date,$purge_end_date);
	//echo $rep;
	
	
	
		
	mrr_check_drivers_for_loads();
		
	mrr_trim_old_truck_tracking_plot_points(7);	//remove truck_tracking points older than 7 days 
			
	// simple query to make sure any 'lost' dispatches are moved to the current day
	$sql = "
		update trucks_log
		set linedate = '".date("Y-m-d")."'
		where linedate = 0
			and deleted = 0
	";
	simple_query($sql);	
			
	mrr_pn_data_lag_checker(1);	//location packet data log check
	mrr_pn_data_lag_checker(2);	//msg packet data log check
	mrr_pn_data_lag_checker(3);	//dispatch events packet data log check
	mrr_pn_data_lag_checker(5);	//elog events packet data log check
		
	//update safety report
	$mrr_start_time=time();
	$mrr_rep=mrr_peoplenet_driver_violations_update(0,0);	//driver, employer
	$mrr_end_time=time();
	$mrr_diff_time=$mrr_end_time - $mrr_start_time;	
		
	
	//now auto-update any stop to be completed that has arrived more the HR_MAX ago...  
	//This is to catch the load/dispatches/stops where the end of one is the same as the start of the next... so the PN system can depart the next one without being on the last stop.
	//Ex: Disp A ends in LaVergne,TN...Disp B begins in LaVergne,TN.  PN does not mark stop/Disp A completed until outside radius, but when it happens it will be for both.  
	$hr_max=10;
	$mrr_updated_stops=0;
	$mrr_updated_disps=0;
	$sql = "
     	select id,
     		trucks_log_id,
     		load_handler_id
     	from load_handler_stops
     	where deleted=0
     		and stop_type_id='2'
     		and linedate_arrival > '2014-12-01 00:00:00'
     		and linedate_arrival < '".date("Y-m-d",strtotime("-".$hr_max." hour",time()))." 00:00:00'
     		and (linedate_completed is NULL or linedate_completed < '2014-12-01 00:00:00')
     	order by linedate_pickup_eta desc
     ";
     $data = simple_query($sql);
     while($row=mysqli_fetch_array($data))
	{
		//update this stop
		$sql2 = "
          	update load_handler_stops set
          		geofencing_arriving_sent='1',
				linedate_geofencing_arriving=NOW(),
				geofencing_arrived_sent='1',              						
				linedate_geofencing_arrived=NOW(),
				linedate_completed=NOW(),
				geofencing_departed_sent='1',              						
				linedate_geofencing_departed=NOW()
          	where id='".sql_friendly($row['id'])."'
          ";
          simple_query($sql2);
          $mrr_updated_stops++;
          
         	//see if more stops are still needed.
          $sqlx = "
          	select *		
          	from load_handler_stops
          	where deleted=0
          		and trucks_log_id='".sql_friendly($row['trucks_log_id'])."'
          		and (linedate_completed is NULL or linedate_completed < '2014-12-01 00:00:00')
          	order by linedate_pickup_eta desc
          ";
          $datax = simple_query($sqlx);
          $mn=mysqli_num_rows($datax);
          if($mn == 0)
          {	//no more stops, so flag as completed....
          	$sql2 = "
          		update trucks_log set
          			dispatch_completed='1'
          		where id='".sql_friendly($row['trucks_log_id'])."'
         	 	";
          	simple_query($sql2);	
          	
          	$mrr_updated_disps++;
		}
	}
	//....................................................................................................................................................................................................  
	
			
	echo '<br>
		<b>Driver Safety Report: (generated in '.$mrr_diff_time.' seconds)</b>
		<br>
		==========================
		<br>
		<br>'.$mrr_rep.'
		<br>	
		<br>---------------------------------------		
		<br>
		<br>
		<b>Auto-Process PN Stops Lags...done so that if Disp A ends where Disp B starts, Disp A is completed and Disp B processing stop 1 arrival/departure.</b>		
		<br>
		<br>'.$mrr_updated_stops.' Stops and '.$mrr_updated_disps.' Dispatches have been completed that have arrived more than '.$hr_max.' hours ago.
		<br>	
		<br>---------------------------------------';
			
	echo mrr_pn_driver_dot_list();
	echo mrr_pn_driver_dot_list_v2(0);		//Driver ID	
	
	//log page load...
	$load_end=date("U");
	$load_time=$load_end - $load_start;
	$sql="
		insert into ".mrr_find_log_database_name()."log_page_loads
			(id,
			time_stamp,
			ip_address,
			page_url,
			user_id,
			start_load,
			end_load,
			load_time)
		values 
			(NULL,
			NOW(),			
			'".$_SERVER['REMOTE_ADDR']."',
			'".$SCRIPT_NAME."',
			'".$_SESSION['user_id']."',
			'".$load_start."',
			'".$load_end."',
			'".$load_time."')
	";
	$id=simple_query($sql);
?>