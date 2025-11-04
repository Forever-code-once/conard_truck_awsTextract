<? include_once('header.php') ?>
<?
$min_load_id=(int) trim($defaultsarray['auto_min_load_id']);    
$tab=mrr_auto_process_load_saves(3,$min_load_id);		//3 loads at a time.
echo $tab;

$mode="";
if(isset($_GET['mode']) && $_GET['mode']==1)		$mode="peoplenet.php";
if(isset($_GET['mode']) && $_GET['mode']==2)		$mode="mrr_cron_job.php";

//add user action to log...
mrr_set_user_action_log(9999,$mode,'mrr_load_auto_saver.php','mrr_load_auto_saver.php',0,0,0,0,0,0,$mode);	
?>
<script type='text/javascript'>
	
	//$().ready(function() {
     	$('.auto_load_runner').each(function() {
     		
     		loadid= get_amount($(this).val());	 
     		//mrr_reload_load_for_load(loadid);
     		mrr_reload_load_for_page(loadid);
     	});
	//});
	function mrr_reload_load_for_load(loadid)
	{	
		$.ajax({
		   type: "GET",
		   url: "manage_load.php?load_id="+loadid+"&auto_save_trigger=1",		  
		   dataType: "html",
		   cache:false,
		   success: function(data) {
		   	//
		   }
		 });
	}
	function mrr_reload_load_for_page(loadid)
	{
		var winny=window.open("manage_load.php?load_id="+loadid+"&auto_save_trigger=1","_blank");
		
		winny.onload = function() {
		  	winny.close();
		};		
	}
</script>
<? include_once('footer.php') ?>