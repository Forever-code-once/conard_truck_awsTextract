<? include('header.php') ?>
<?

	if(isset($_POST['driver_id'])) {
		$_POST['build_report'] = 1;
	}

	$rfilter = new report_filter();
	$rfilter->show_driver 		= true;
	$rfilter->show_employers 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
?>
	<?
		if(isset($_POST['build_report'])) {
			echo "
				<div style='margin:0 0 10px 30px' class='section_heading'>Driver Absence Report</div>
				<br>
								
				<table class='tablesorter font_display_section' style='margin:0 10px;width:1200px;text-align:left'>
				<thead>
				<tr>
					<th>Driver</th>
					<th>Date</th>
					<th>Code</th>
					<th>Note</th>
				</tr>
				</thead>
				<tbody>
			";
			$sql = "
				select driver_absenses.*,
					drivers.name_driver_last,
					drivers.name_driver_first,
     				option_values.fname,
     				option_values.fvalue
     				
     			from driver_absenses
     				left join option_values on option_values.id=driver_absenses.driver_code
     				left join drivers on drivers.id=driver_absenses.driver_id
     			where driver_absenses.deleted=0
     				".($_POST['driver_id'] > 0 ? " and driver_absenses.driver_id = '".sql_friendly($_POST['driver_id'])."' " : "")."
					".($_POST['employer_id'] > 0 ? " and drivers.employer_id = '".sql_friendly($_POST['employer_id'])."' " : "")."
					".($_POST['date_from'] != '' ? " and driver_absenses.linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))."' " : "")."
					".($_POST['date_to'] != '' ? " and driver_absenses.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))."' " : "")."
     			
     			order by driver_absenses.linedate desc
			";
			$data = simple_query($sql);

			$cntr=0;
			while($row = mysqli_fetch_array($data)) 
			{								
				echo "
					<tr class='".($cntr%2==0 ? "even" : "odd")."'>
						<td valign='top'><a href='admin_drivers.php?id=$row[driver_id]' target='view_driver_$row[driver_id]'>$row[name_driver_last], $row[name_driver_first]</a></td>
     					<td valign='top'>".date("m/d/Y",strtotime($row['linedate']))."</td>
     					<td valign='top'><b>".$row['fvalue']."</b></td>
     					<td valign='top'><i>".$row['driver_reason']."</i></td>
     				</tr>					
				";
			}
		echo "
			</tbody>
			</table>	
			<br><center><a name='to_bottom' href='#to_top'>Back to Top</a></center>
		";
	}	
?>	
<script type='text/javascript'>
	$('.tablesorter').tablesorter();          
</script>
<? include('footer.php') ?>