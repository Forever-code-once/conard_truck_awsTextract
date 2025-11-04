<?
$usetitle="Geofencing Event Activity Report";  
?>
<? include('header.php') ?>
<?
	if(isset($_GET['load_id']))		$_GET['load_id']=$_POST['load_id'];
	if(isset($_GET['disp_id']))		$_GET['load_id']=$_POST['disp_id'];
	if(isset($_GET['stop_id']))		$_GET['stop_id']=$_POST['stop_id'];
	
	if(!isset($_POST['load_id']))		$_POST['load_id']=0;
	if(!isset($_POST['disp_id']))		$_POST['disp_id']=0;
	if(!isset($_POST['stop_id']))		$_POST['stop_id']=0;
?>
<form action='' name='my_report' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>Geofencing Event Activity Messages</h3>
	<div style='color:purple;'>Last Loaded on <?=date("m/d/Y H:i") ?></div>
</div>
<div style='clear:both'></div>

<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1100px;margin:10px'>
	<tr>
		<td valign='top' colspan='7'><center>Optional Filter: Use as many as you want.</center></td>
	</tr>
	<tr>
		<td valign='top'>Load Number <span class='mrr_link_like_on' onClick='mrr_reset_load_disp_stop(0);'>Reset for Today</span></td>
		<td valign='top'><input type='text' name='load_id' id='load_id' value="<?= $_POST['load_id']?>" size='10'></td>
		
		<td valign='top'>Dispatch</td>
		<td valign='top'><input type='text' name='disp_id' id='disp_id' value="<?= $_POST['disp_id']?>" size='10'></td>
		
		<td valign='top'>Stop Number</td>
		<td valign='top'><input type='text' name='stop_id' id='stop_id' value="<?= $_POST['stop_id']?>" size='10'></td>
		
		<td valign='top' align='right'><input type='submit' name='build_report' id='build_report' value="Run Report"></td>
	</tr>
</table>
<? 
	$full_report=mrr_pull_all_active_geofencing_event_rows($_POST['load_id'],$_POST['disp_id'],$_POST['stop_id']);	//moved to function to use in ajax as well.
	echo $full_report; 
?>
<br>    

<table class='admin_menu1' style='text-align:left;width:1100px;margin:10px'>
	<tr><td valign='top' colspan='2'><b> GEOFENCE KEY </b></td></tr>
	<tr><td valign='top'><span style='color:#cc0000;'><b> dispatch info in red </b></span></td><td valign='top'> Indicates Dispatch was not found connected to the load (by error, as preplanned load, or load updated after sent to PN dispatch).</td></tr>
	<tr><td valign='top' colspan='2'><br><br><b> Event List </b></td></tr>
	<tr><td valign='top'><b> trip-start </b></td><td valign='top'> Indicates a trip start event  <span class='alert'><b>{Email Not Sent for this event}</b></span></td></tr>
	<tr><td valign='top'><b> trip-end </b></td><td valign='top'> Indicates a trip end event   <span class='alert'><b>{Email Not Sent for this event}</b></span</td></tr>
	<tr><td valign='top'><b> approaching-occurred </b></td><td valign='top'> Indicates a trip stop approaching action has occurred   <span class='alert'><b>{Email Not Sent for this event}</b></span></td></tr>
	<tr><td valign='top'><b> arrived-occurred </b></td><td valign='top'> <span style='color:#cc00cc;'>Indicates a trip stop arrived action has occurred </td></tr>
	<tr><td valign='top'><b> departed-occurred </b></td><td valign='top'> <span style='color:#00cccc;'>Indicates a trip stop departed action has occurred</span> </td></tr>
	<tr><td valign='top'><b> approaching-late </b></td><td valign='top'> Indicates a trip stop approaching action is late   <span class='alert'><b>{Email Not Sent for this event}</b></span></td></tr>
	<tr><td valign='top'><b> arrived-late </b></td><td valign='top'> <span style='color:#cc00cc;'>Indicates a trip stop arrived action is late <span class='alert'><b>{Email No Longer Sent for this event}</b></span></td></tr>
	<tr><td valign='top'><b> departed-late </b></td><td valign='top'> <span style='color:#00cccc;'>Indicates a trip stop departed action is late</span> <span class='alert'><b>{Email No Longer Sent for this event}</b></span></td></tr>
	<tr><td valign='top' colspan='2'><br><br><b> Reason Triggered </b></td></tr>
	<tr><td valign='top'><b> timed </b></td><td valign='top'>  Event occurred based on a preset time </td></tr>
	<tr><td valign='top'><b> manual </b></td><td valign='top'>  Event was triggered manually by driver </td></tr>
	<tr><td valign='top'><b> required-guf-confirmed </b></td><td valign='top'> Event occurred when required GUF was confirmed by driver  </td></tr>
	<tr><td valign='top'><b> no-guf </b></td><td valign='top'>  Event occurred with no driver GUF </td></tr>
	<tr><td valign='top'><b> neg-guf-timeout </b></td><td valign='top'> Event occurred when negative response GUF timed out  </td></tr>
	<tr><td valign='top'><b> neg-guf-confirmed </b></td><td valign='top'> Event occurred when negative response GUF was confirmed by driver  </td></tr>
	<tr><td valign='top'><b> inferred </b></td><td valign='top'> Event occurrence is inferred by the occurence of some other event (approaching inferred when arrived occurs, depart inferred when arrival at another stop, etc.)  </td></tr>
</table>
</form>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
		//$('.datepicker').datepicker();
		
		//setTimeout("location.reload();", (60 * 1000));		//ten minutes...600 seconds...1000=1 second
	});
	
	function mrr_reset_load_disp_stop(use_load)
	{
		$('#load_id').val(''+use_load+'');
		$('#disp_id').val('0');
		$('#stop_id').val('0');
		document.my_report.submit();
	}
</script>
<? include('footer.php') ?>