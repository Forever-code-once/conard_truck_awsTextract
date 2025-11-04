<? include('application.php') ?>
<?

if(!isset($_GET['connect_key']) || $_GET['connect_key'] != 'bas82bad98fqhbnwga8shq34908asdhbn') {
	die("You have reached this page incorrectly.");
}

	//die("Temporarily Off.");

	/*******************************************************************************************\ 
	NOTICE:
	Page to do standard routines.  
	This page is run by cron-job or scheduled tasks.   
	
	TEST PAGE ONLY...add good code to daily_maint.php, which is an active routine once a day.
	\*******************************************************************************************/
	
	$found_raises=mrr_process_scheduled_pay_raises(1);

	$found_requests=mrr_send_email_list_of_maint_requests(1);		//send Maint Request list to Dispatch...testing here.
	
	$found_reviews=mrr_send_email_list_of_driver_reviews(15);		//0=days before now to find reviews due.
		
			
	echo '
		<br><b>Maint Requests Found for Email Notice:</b> '.$found_requests.'.<br>
		
		<br><b>Driver Reviews for Email Notice:</b> '.$found_reviews.'.<br>
		
		<br><b>Driver Raises Processed:</b> '.$found_raises.'.<br>
	';
	
	//include('mrr_load_auto_saver.php');
	
	//add user action to log...
     //mrr_set_user_action_log(9999,'mrr_cron_job.php','mrr_load_auto_saver.php','mrr_load_auto_saver.php',0,0,0,0,0,0,'mrr_cron_job.php');		//values initialized in application.php
     die('<br>stopped.');
?>
<script type='text/javascript'>
	//window.location.assign("mrr_load_auto_saver.php?mode=2");
</script>