<? include('header.php') ?>
<div style='text-align:left;margin:10px'>
	<div class='section_heading'>Trip Pack Completed Report</div>
	Enter any date for the month you would like to build the "trip pack completed" report for. <br>
	For example, if you wanted <?=date("M, Y")?>, select any date during that month <br>
	and the system will automatically generate the full month report
</div>

<?
	$rfilter = new report_filter();
	$rfilter->show_date_range 		= false;
	$rfilter->show_single_date 		= true;
	$rfilter->show_font_size			= true;
	$rfilter->show_filter();
?>

<form action='' method='post'>
<input type='hidden' name='build_report' value='1'>
<input type='hidden' name='report_date' value='<?=$_POST['report_date']?>'>
<?
	if(isset($_POST['build_report'])) 
	{
?>	
	
	
	<table class='admin_menu2 font_display_section' style='width:600px'>
	<tr>
		<td valign='top'><b>Date Checked</b></td>
		<td valign='top'><b>Load Id</b></td>
		<td valign='top'><b>Dispatch ID</b></td>
		<td valign='top'><b>Truck Name</b></td>
		<td valign='top'><b>Driver Name</b></td>
		<td valign='top'><?= show_help('report_trip_packs.php','Trip Packs Completed Report') ?></td>		
	</tr>
<?
		$date_start = strtotime(date("m/1/Y", strtotime($_POST['report_date'])));
		$days_in_month = date("t", $date_start);
		$date_end = strtotime(date("m/".$days_in_month."/Y", $date_start));
		
		$use_start=date("Y-m-d", $date_start)." 00:00:00";
		$use_end=date("Y-m-d", $date_end)." 23:59:59";
		
		$sql = "
				select *
				from trip_packs
				where deleted='0'
					and linedate_added>='".$use_start."' and linedate_added<='".$use_end."' 
				order by linedate_added asc,id asc				
			";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$tpid=$row['id'];
			$load_id=$row['load_id'];
			$dispatch_id=$row['dispatch_id'];
			$truck_id=$row['truck_id'];
			$driver_id=$row['driver_id'];
			$dater=date("m/d/Y", strtotime($row['linedate_added']));
			
			//get truck
			$sql2 = "
				select name_truck				
				from trucks
				where deleted = 0
					and id='".sql_friendly($truck_id)."' 
				limit 1
			";
			$tdata = simple_query($sql2);
			$trow = mysqli_fetch_array($tdata);
			$tnamer=$trow['name_truck'];
			
			//get driver
			$sql3 = "
				select name_driver_first,name_driver_last				
				from drivers
				where deleted = 0
					and id='".sql_friendly($truck_id)."' 
				limit 1
			";
			$ddata = simple_query($sql3);
			$drow = mysqli_fetch_array($ddata);
			$dnamer=$drow['name_driver_first']." ".$drow['name_driver_last'];
			
			//output and formatting
			$trash='<a href="javascript:confirm_del_tpid('.$tpid.')"><img src="images/delete_sm.gif" border="0"></a>';
			$l_link="<a href='/manage_load.php?load_id=".$load_id."' title='Load details here' target='_blank'><b>".$load_id."</b></a>";
			$p_link="<a href='/add_entry_truck.php?load_id=".$load_id."&id=".$dispatch_id."' title='Dispatch details here' target='_blank'><b>".$dispatch_id."</b></a>";
			$t_link="<a href='/admin_trucks.php?id=".$truck_id."' title='Truck details here' target='_blank'><b>".$tnamer."</b></a>";
			$d_link="<a href='/admin_drivers.php?id=".$driver_id."' title='Driver details here' target='_blank'><b>".$dnamer."</b></a>";
						
			echo "<tr class='trip_pack_$tpid'>
					<td valign='top'>$dater</td>
					<td valign='top'>$l_link</td>
					<td valign='top'>$p_link</td>
					<td valign='top'>$t_link</td>
					<td valign='top'>$d_link</td>
					<td valign='top'>$trash</td>		
				</tr>";		
		}
		?>
	</table>
<?
	}
?>	

</form>
<script type='text/javascript'>
	$('.input_date').datepicker();
	
	function confirm_del_tpid(myid) {
		$.prompt("Are you sure you want to delete this trip pack log?", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {
					$.ajax({
          			   type: "POST",
          			   url: "ajax.php?cmd=kill_trip_packs",
          			   data: {
          			   		"tpid":myid,
          			   		},
          			   dataType: "xml",
          			   cache:false,
          			   success: function(xml) {
          			   		newid=$(xml).find('TripPackID').text();
               				if(newid > 0)
               				{
               					$.noticeAdd({text: "Trip pack log has been removed."});
               					
               					$('.trip_pack_'+myid+'').html('');			
               				} 
          			   }
          			});	
				}
			}
		});
	}
</script>
<? include('footer.php') ?>