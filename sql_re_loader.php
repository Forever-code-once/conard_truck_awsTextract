<? include_once('application.php')?>
<?
$sql = "delete from error_log";
simple_query($sql);
$sql = "delete from sicap_conard.error_log";
simple_query($sql);
             
include_once('mrr_load_auto_saver.php');

echo "<br><br><a href='sql_re_loader.php'>Reload</a><br><br>Your IP Address is ".$_SERVER['REMOTE_ADDR'].".";

die("<br><br>Finished.");
?>