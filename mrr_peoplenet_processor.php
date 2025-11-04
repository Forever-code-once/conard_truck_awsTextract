<?
$usetitle="PeopleNet Email Processor";  
?>
<? include('header.php') ?>
<?
	
?>
<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3><?= $usetitle ?></h3>
	<div style='color:purple;'></div>
	<div id='geo_message'></div>
</div>
<div style='clear:both'></div>
<br>    

<div id='email_monitor' style='margin-left:20px;'></div>


</form>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
		//$('.datepicker').datepicker();
		monitor_pn_email_report();
		
		//setTimeout("location.reload();", (60 * 1000));		//ten minutes...600 seconds...1000=1 second  
	});
	
	function monitor_pn_email_report()
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_pn_email_processor",
		   data: {
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		txt=$(xml).find('mrrtab').text();
		   		$('#email_monitor').html(txt);
		   }	
		});
	}
	/*
	function mrr_check_geofence_location(id,tid,tname,dest_lat,dest_long,gps_lat,gps_long)
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_check_up_on_geo_id",
		   data: {
		   		"geo_id":id,
		   		"truck_id":tid,
		   		"truck_name":tname,
		   		"dest_lat":dest_lat,
		   		"dest_long":dest_long,
		   		"gps_lat":gps_lat,
		   		"gps_long":gps_long
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		txt="";
		   		
		   		if($(xml).find('mrrTab').text()!="")
		   		{			   				
		   			txt=$(xml).find('mrrTab').text();
		   		}
		   		
		   		$('#check_for_geo_id').html(txt);
		   }	
		});
	}
	
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete the tracking for this stop?")) {
			window.location = 'report_peoplenet_activity.php?delid=' + id;
		}
	}
	function confirm_deactivate(id) {
		if(confirm("Are you sure you want to deactivate the tracking for this stop?")) {
			window.location = 'report_peoplenet_activity.php?deactivate=' + id;
		}
	}
	*/
</script>
<? include('footer.php') ?>