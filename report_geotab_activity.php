<?
$usetitle="GeoTab Activity Report";  
?>
<? include('header.php'); ?>
<?
	//include_once("functions_geotab.php");
	//include_once("functions_geotab_usage.php");
	
	
	/*
	if(isset($_GET['activate']))
	{
		$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			active='1'
		where id='".sql_friendly($_GET['activate'])."'	
		";	
		simple_query($sql);	
	}
	
	if(isset($_GET['deactivate']))
	{
		$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			active='0'
		where id='".sql_friendly($_GET['deactivate'])."'	
		";	
		simple_query($sql);	
	}
	
	if(isset($_GET['delid']))
	{
		$sql="
		update ".mrr_find_log_database_name()."geofence_hot_load_tracking set
			deleted='1'
		where id='".sql_friendly($_GET['delid'])."'	
		";	
		simple_query($sql);	 
	}
	*/	
	
	
	
	//**** Note -- to help with the loading speed of this page, this function call was moved to the functions_geotab_usage.php file
    //**** after processing the mrr_get_geotab_get_datafeed() funciton
    //update the current truck locations...
    //$current_locals=mrr_process_current_geotab_location_of_trucks(1);
	//echo $current_locals;
	
	
	
	//mrr_pull_geotab_active_geofencing_rows_no_display(0);			//SHOULD BE RUNNING IN geotab_cronjob.php?feed_type=1  MODE.
?>
<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>GeoTab Geofencing Activity</h3>
	<div style='color:purple;'>
		This page now only displays <b>All stops</b> (by appointment time) for each truck where the dispatch is not yet completed.  
		<br>The next dispatch will display after the current one has been completed. Last Loaded on <?=date("m/d/Y H:i") ?>
	</div>
	<div id='geo_message'></div>
</div>
<div style='clear:both'></div>

<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1800px;margin:10px'>
<thead>
<!--
<tr>
	<th nowrap><b>Load ID</b></th>
	<th nowrap><b>Dispatch</b></th>
	<th nowrap><b>Stop ID</b></th>
	<th><b>Customer</b></th>
	<th><b>Driver</b></th>
	<th><b>Truck</b></th>
	<th><b>Trailer</b></th>		
	<th><b>Note</b></th>
	<th><b>Due</b></th>
	<th><b>Hours</b></th>
	<th><b>Dest</b></th>
	<th><b>Miles</b></th>
	<th><b>Position</b></th>
	<th nowrap><b>ETA</b></th>
	<th><b>Grade</b></th>
	<th><b>Test</b></th>
	<th><b>Active</b></th>
	<th><b>&nbsp;</b></th>
</tr>
-->
<tr>
	<th nowrap><b>Load ID</b></th>
	<th nowrap><b>Dispatch</b></th>
	<th nowrap><b>Stop ID</b></th>
	<th><b>Customer</b></th>
	<th><b>Driver</b></th>
	<th><b>Truck</b></th>
	<th><b>Trailer</b></th>	
	<th><b>DueDate</b></th>
	<th><b>Hours</b></th>
	<th><b>Dest</b></th>
	<th><b>Miles</b></th>
	<th><b>Position</b></th>
	<th><b>GPSDate</b></th>
	<th><b>Away</b></th>
	<th><b>MPH</b></th>
	<th><b>Location</b></th>
	<th><b>Distance</b></th>
	<th><b>ETA</b></th>
	<th><b>Due</b></th>
	<th><b>Grade</b></th>
	<th><b>Notes</b></th>
</tr>
</thead>
<tbody>
<? 
	$full_report=mrr_pull_geotab_active_geofencing_rows(0);
	echo $full_report;
?>
</tbody>
</table>
<br>     
<div>
</div>
<div style='margin-left:20px;' id='check_for_geo_id'></div>
<?
$validate_user_read=0;
if(isset($_GET['verify']))	
{
	$validate_user_read=1;
	echo "<audio id='im_sound_affect' src='/sounds/FireTruck.mp3'></audio>";
}
?>
</form>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
		//$('.datepicker').datepicker();
		
		//$('.all_pn_activity_notes').hide();
		
		//mrr_process_promiles_distances();
		<? if($validate_user_read > 0) { ?>
			
			txt="";
     		txt+="Please review the Geofencing Report<br><br><br>";
     		$.prompt(txt, {
     			buttons: {"Yes, I will": true},
     			loaded: function() {
     				//nothing right now...     , "No": false
     				$("#im_sound_affect").get(0).play();
     			},
     			submit: function(v, m, f) {
     				if(v) 
     				{     					
     					$.ajax({
     						url: "ajax.php?cmd=add_log_user_validation",
     						dataType: "xml",
     						type: "post",
     						data: {
     							"mode":1,
     							"page": "report_geotab_activity.php"
     						},
     						success: function(xml) {
     							$.noticeAdd({text: "Thank you."});
     							$("#im_sound_affect").get(0).play();
     						}
     					});     					
     				}
     			}
     		});
		<? } ?>		
		setTimeout("location.reload();", (60 * 5000));		//ten minutes...600 seconds...1000=1 second
	});
	
	function mrr_process_promiles_distances()
	{
		//calculate the stop distances
		cntr=0;
		$('.stop_pro_miles_distance').each(function() {
			stopid= get_amount($(this).attr('stop_id'));	
			dispid= get_amount($(this).attr('disp_id'));	
			latA= get_amount($(this).attr('lat1'));	
			longA= get_amount($(this).attr('long1'));	
			latB= get_amount($(this).attr('lat2'));	
			longB= get_amount($(this).attr('long2'));	
			mph= get_amount($(this).attr('mph'));
			due= get_amount($(this).attr('due'));
			
			mrr_pro_miles_dist_cals(cntr,stopid,dispid,latA,longA,latB,longB,mph,due);
			
			cntr++;
		});
		
		//alert(''+cntr+' Stops Found.');
	}
	
	
	function mrr_pro_miles_dist_cals(cntr,stopid,dispid,latA,longA,latB,longB,mph,due)
	{		
		//if(cntr==0)
		//{
			//alert('Stop '+stopid+': ('+latA+','+longA+') ('+latB+','+longB+')');	 		
			
     		$.ajax({
     		   type: "POST",
     		   url: "ajax.php?cmd=mrr_pro_miles_dist_calc",
     		   data: {
     		   		"mph":mph,
     		   		"due":due,
     		   		"lat1":latA,
     		   		"long1":longA,
     		   		"lat2":latB,
     		   		"long2":longB
     		   		},		   
     		   dataType: "xml",
     		   cache:false,
     		   async:false,
     		   success: function(xml) {
     		   		dist=0;
     		   		dist_eta=0;
     		   		dist_due=0;
     		   		
     		   		if(get_amount($(xml).find('mrrDist').text()) > 0)
     		   		{			   				
     		   			dist=get_amount($(xml).find('mrrDist').text());
     		   			dist_due=get_amount($(xml).find('mrrDue').text());
     		   			dist_eta=get_amount($(xml).find('mrrMPH').text());
     		   		} 
     		   		$('#find_dist_'+stopid+'_'+dispid+'').html(dist);
     		   		$('#find_eta_'+stopid+'_'+dispid+'').html(dist_eta);
     		   		$('#find_due_'+stopid+'_'+dispid+'').html(dist_due);
     		   }	
     		});				
		//}			
	}
	
	function add_note(dispatch_id) 
	{
		txt="";
		txt+="Enter your note for dispatch ID: "+dispatch_id+"<br>";
		txt+="<textarea style='width:400px;height:100px' name='new_note' id='new_note'></textarea><br><br>";
		$.prompt(txt, {
			buttons: {Okay: true, Cancel: false},
			loaded: function() {
				$('#new_note').focus();
				$('#added_note_deadline').datepicker();
			},
			submit: function(v, m, f) {
				if(v) {
					if(f.new_note == '') {
						$.prompt.close();
						return false;
					}
					
					$.ajax({
						url: "ajax.php?cmd=add_truck_note",
						dataType: "xml",
						type: "post",
						data: {
							dispatch_id: dispatch_id,
							note: f.new_note 
						},
						success: function(xml) {
							$.noticeAdd({text: "Success - note was saved to dispatch " + dispatch_id});
						}
					});
					
					
				}
			}
		});
	}
	function mrr_view_note(dispatch_id)
	{
		if(dispatch_id > 0)
		{
			$.ajax({
				url: "ajax.php?cmd=mrr_load_notes",
				dataType: "xml",
				type: "post",
				data: {
					log_id: dispatch_id
				},
				success: function(xml) {
					txt=$(xml).find('DispHTML').text();
					$.prompt(txt);
				}
			});	
		}
	}
	
	
	function mrr_toggle_pn_load_notes(id)
	{
		txt="";
		txt = txt + "";
		txt = txt + "<div id='note_section'>Loading...</div>";
		txt = txt + "";
		
		$.prompt(txt, {
			buttons: {"Close": true},
			loaded: function() {
				create_note_section('#note_section', 8, id);
			},
			submit: function(v, m, f) {
				if(v) {
					$.prompt.close();					
				}
			}
		});
	}
		
	function mrr_toggle_pn_activity_notes(id)
	{		
		txt=$('#pn_activity_notes_'+id+'').html();
		$.prompt(txt);
	}
	
	function send_email_hot_tracking(geoid,sectid)
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_send_email_for_hot_load_tracking",
		   data: {
		   		"geo_id":geoid,
		   		"geo_sector":sectid
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		txt="";
		   		
		   		if($(xml).find('mrrTab').text()=="Done")
		   		{			   				
		   			txt=txt + "<span class='good_alert'><b>DONE sending email.</b></span>";
		   		}
		   		else
		   		{
		   			txt=txt + "<span class='alert'><b>ERROR sending email.</b></span>";		
		   		}
		   		
		   		//txt=txt + " (Sector "+$(xml).find('sector').text() +" from "+sectid+".)";
		   		txt=txt + " "+$(xml).find('msg').text() +"";
		   		$('#geo_message').html(txt);
		   }	
		});
	}
	function mrr_check_geofence_location(id,tid,tname,dest_lat,dest_long,gps_lat,gps_long)
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_check_up_on_geotab_id",
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
			window.location = 'report_geotab_activity.php?delid=' + id;
		}
	}
	function confirm_deactivate(id) {
		if(confirm("Are you sure you want to deactivate the tracking for this stop?")) {
			window.location = 'report_geotab_activity.php?deactivate=' + id;
		}
	}
</script>
<? include('footer.php') ?>