<? 
$usetitle = "Driver Safety Violations Report";
$use_title = "Driver Safety Violations Report";
?>
<? include('header.php') ?>
<?
	
?>
<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3><?=$usetitle ?></h3>
	<div style='color:purple;'>
		Not Available yet...coming soon to a dispatch near you...  :)
	</div>
</div>
<div style='clear:both'></div>

<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1800px;margin:10px'>
<thead>
<tr>
	<th nowrap><b>Load ID</b></th>
	<th nowrap><b>Dispatch</b></th>
	<th nowrap><b>Stop ID</b></th>
</tr>
</thead>
<tbody>
<? 
	//$full_report=mrr_pull_all_active_geofencing_rows(0);	//moved to function to use in ajax as well.
	//echo $full_report;
?>
</tbody>
</table>
<br>     

</form>
<script type='text/javascript'>
	//$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
	});
	
</script>
<? include('footer.php') ?>