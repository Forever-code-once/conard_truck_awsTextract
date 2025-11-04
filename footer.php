<?
	/*
	if(isset($admin_page)) {
		echo "<br><br><a href='admin.php'>[Admin Home]</a> | <a href='index.php'>[Intranet Home]</a> |";
	}
	if(isset($_SESSION['user_id'])) echo "<a href='login.php?out=1'>[LogOut]</a>" 
	*/
	
	/*	
	mrr_log_page_loads($mrr_micro_seconds_start," (Full File)");		//make tracking log entry	
	if($_SESSION['user_id']==23)
	{
		echo "<br>Memory Usage=".memory_get_usage().".<br>";	
	}
	*/
	
	//add user action to log...
     $mrr_activity_log_user=(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0');
     $mrr_activity_log_self=(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
     $mrr_activity_log_query=(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');
     mrr_set_user_action_log($mrr_activity_log_user,$mrr_activity_log_self,$mrr_activity_log_query,$mrr_activity_log_refer,$mrr_activity_log_driver,$mrr_activity_log_truck,$mrr_activity_log_trailer,
     						$mrr_activity_log_load,$mrr_activity_log_dispatch,$mrr_activity_log_stop,$mrr_activity_log_notes);		//values initialized in application.php
 

?>
<script type='text/javascript'>
	$().ready(function() {
		$.datepicker.setDefaults({
			changeMonth: true,
			changeYear: true,
			yearRange: "c-70:c+70"
		});
	});
</script>
<style>
	.ui-datepicker-month {
		color:#000000;
	}
	.ui-datepicker-year {
		color:#000000;
	}	
</style>
<? 
if(isset($mrr_micro_seconds_start) && !isset($_GET['notime']))
{
	$mrr_micro_seconds_footer=time();	//date("His");  
	echo "<br>&nbsp;<br>&nbsp;<br>&nbsp;<br><b>Page Loaded: ". ($mrr_micro_seconds_footer - $mrr_micro_seconds_start)." second(s) to load page on ".date("m/d/Y H:i:s").".</b><br>".$_SERVER['PHP_SELF']."<br>"; 	
}
?>