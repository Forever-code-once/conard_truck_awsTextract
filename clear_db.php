<?
include('application.php');

// simple program to clear the database out - getting it ready for a new installation

// MAKE SURE THE "die()" statement is put back in after the initial clearing out - as we do NOT want this page run
// once the new site is live

die('aborted');

$table_array = array();
$table_array[] = "trucks_log";
$table_array[] = "load_handler";
$table_array[] = "trucks";
$table_array[] = "drivers";
$table_array[] = "customers";
$table_array[] = "attachments";
$table_array[] = "accident_reports";
$table_array[] = "drivers_expenses";
$table_array[] = "drivers_payroll";
$table_array[] = "drivers_unavailable";
$table_array[] = "equipment_history";
$table_array[] = "load_handler_stops";
$table_array[] = "loads";
$table_array[] = "log_edi";
$table_array[] = "log_login";
$table_array[] = "log_user_action";
$table_array[] = "log_user_changes";
$table_array[] = "maint_requests";
$table_array[] = "notes";
$table_array[] = "notes_main";
$table_array[] = "quotes";
$table_array[] = "quotes_expenses";
$table_array[] = "quotes_stops";
$table_array[] = "trailers";
$table_array[] = "trailers_dropped";
$table_array[] = "trip_packs";
$table_array[] = "trucks_log_notes";
$table_array[] = "trucks_odometer";
$table_array[] = "user_action_log";

foreach($table_array as $table) {
	echo "$table<br>";
	$sql = "
		delete from `$table`
	";
	simple_query($sql);
}

die('<br><br>done...');
?>