<?
	$usetitle="Graded Loads Report";	
	
	if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("n/j/Y", time());
	$show_summary_only_flag=0;
	if(isset($_POST['summary_only']))	$show_summary_only_flag=1;
?>
<? include('header.php') ?>

<form action='' method='post'>
	<?     	    	
     	$rfilter = new report_filter();
     	//$rfilter->show_driver 			= true;
     	//$rfilter->show_employers 		= true;
     	$rfilter->summary_only	 		= true;
     	//$rfilter->team_choice	 		= true;
     	//$rfilter->show_font_size		= true;
     	$rfilter->mrr_special_print_button	= true;
     	$rfilter->show_filter();     	
     ?>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>Load Delivery Grades</h3>
	<div id='geo_message'></div>
</div>
<div style='clear:both'></div>

<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1600px;margin:10px'>
<thead>
<tr>
	<th nowrap><b>Load ID</b></th>
	<th nowrap><b>Dispatch</b></th>
	<th nowrap><b>Stop ID</b></th>
	<th><b>Customer</b></th>
	<th><b>Truck</b></th>
	<th><b>Trailer</b></th>
	<th><b>Driver</b></th>
	<th><b>Due</b></th>
	<th><b>Arrived</b></th>
	<th><b>Grade</b></th>
	<th><b>Grade Note</b></th>
	<th><b>Score</b></th>
</tr>
</thead>
<tbody>
<? 
	$loads=0;
	$load_arr[0]=0;
	$load_stops[0]=0;
	$load_grade[0]=0;
	
	$dispatches=0;
	$disp_arr[0]=0;
	$disp_stops[0]=0;
	$disp_grade[0]=0;
	
	$stops=0;
	$ungraded_stops=0;
	
	$grade_tot=0;
	$grade_percent=0;
	
	$scale=7;
	
	$html_tmp=mrr_self_grading_completed_stops(date("Y-m-d",strtotime($_POST['date_from'])),date("Y-m-d",strtotime($_POST['date_to'])));
		
	$sql="
		select load_handler_stops.*,
			trucks_log.truck_id,
			trucks_log.trailer_id,
			trucks_log.driver_id,
			trucks_log.customer_id,
			(select name_truck from trucks where trucks.id=trucks_log.truck_id) as truckname,
			(select trailer_name from trailers where trailers.id=trucks_log.trailer_id) as trailername,
			(select name_driver_first from drivers where drivers.id=trucks_log.driver_id) as driverfname,
			(select name_driver_last from drivers where drivers.id=trucks_log.driver_id) as driverlname,
			(select name_company from customers where customers.id=trucks_log.customer_id) as compname 
		from load_handler_stops,
			trucks_log
		where load_handler_stops.deleted=0
			and trucks_log.deleted=0
			and load_handler_stops.trucks_log_id=trucks_log.id
			and load_handler_stops.linedate_completed>'2000-01-01 00:00:00'
			and load_handler_stops.linedate_pickup_eta>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'
			and load_handler_stops.linedate_pickup_eta<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
		order by load_handler_stops.linedate_pickup_eta asc	
	";	
	$data=simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$score=$row['stop_grade_id'];
		$grader=mrr_load_stop_grade_decoder($row['stop_grade_id']);
		
		if($score==0)	$ungraded_stops++;
		
		$grade_tot+=$score;
		
		$stops++;
		
		$found_load=0;
		for($x=0;$x < $loads; $x++)
		{
			if($load_arr[ $x ]== $row['load_handler_id'])
			{
				$found_load=1;
				$load_stops[ $x ]++;
				$load_grade[ $x ]+=$score;
			}	
		}
		if($found_load==0)
		{
			$load_arr[ $loads ]= $row['load_handler_id'];
			
			$load_stops[ $loads ]=1;
			$load_grade[ $loads ]=$score;
			
			$loads++;
		}
		
		$found_disp=0;
		for($x=0;$x < $dispatches; $x++)
		{
			if($disp_arr[ $x ]== $row['trucks_log_id'])	
			{
				$found_disp=1;
				$disp_stops[ $x ]++;
				$disp_grade[ $x ]+=$score;
			}	
		}
		if($found_disp==0)
		{
			$disp_arr[ $dispatches ]= $row['trucks_log_id'];
			
			$disp_stops[ $dispatches ]=1;
			$disp_grade[ $dispatches ]=$score;
			
			$dispatches++;
		}
		
		if($show_summary_only_flag==0)
		{
			echo "
			<tr>
				<td><a href='manage_load.php?load_id=".$row['load_handler_id']."' target='_blank' name='".$row['id']."'>".$row['load_handler_id']."</a></td>
				<td><a href='add_entry_truck.php?load_id=".$row['load_handler_id']."&id=".$row['trucks_log_id']."' target='_blank'>".$row['trucks_log_id']."</a></td>
				<td>".$row['id']."</td>
				<td><a href='admin_customers.php?eid=".$row['customer_id']."' target='_blank'>".$row['compname']."</a></td>
				<td><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['driverfname']." ".$row['driverlname']."</a></td>
				<td><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['truckname']."</a></td>
					<td><a href='admin_trailers.php?id=".$row['trailer_id']."' target='_blank'>".$row['trailername']."</a></td>
				<td>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>
				<td>".date("m/d/Y H:i",strtotime($row['linedate_completed']))."</td>
				<td>".$grader."</td>
				<td>".$row['stop_grade_note']."</td>
				<td>".$score."</td>
			</tr>
			";
		}	
	}
	//stop grades
	if(($stops-$ungraded_stops) > 0)		$grade_percent=(($grade_tot/$scale) / ($stops-$ungraded_stops) )*100;
	$grade_tot_max= ($stops-$ungraded_stops) * $scale;
	
	//dispatch grades
	$dcntr=0;
	$disp_grade_tot=0;
	$disp_grade_percent=0;
	for($x=0;$x < $dispatches; $x++)
	{
		$dstops=$disp_stops[ $x ];
		$dscore=$disp_grade[ $x ];
		if($dstops > 0 && $dscore>0)
		{
			$dcntr++;
			$disp_grade_tot+= ($dscore/$dstops);
		}
	}
	if($dcntr > 0)						$disp_grade_percent=(($disp_grade_tot/$scale)/$dcntr)*100;
	$disp_grade_tot_max=$dcntr * $scale;
	
	//load grades
	$lcntr=0;
	$load_grade_tot=0;
	$load_grade_percent=0;
	for($x=0;$x < $loads; $x++)
	{
		$lstops=$load_stops[ $x ];
		$lscore=$load_grade[ $x ];
		if($lstops > 0 && $lscore>0)
		{
			$lcntr++;
			$load_grade_tot+= ($lscore/$lstops);
		}
	}
	if($lcntr > 0)						$load_grade_percent=(($load_grade_tot/$scale)/$lcntr)*100;	
	$load_grade_tot_max=$lcntr * $scale;
?>
</tbody>
</table><br>
<?   
echo "
	<table class='admin_menu1 font_display_section' id='table_2' style='text-align:left;width:800px;margin:10px'>
	<tr>
		<td valign='top'><b>Graded Summary (scale of 1-".$scale.")</b></td>
		<td valign='top' align='right'><b>Graded Items</b></td>
		<td valign='top' align='right'><b>Total Score</b></td>
		<td valign='top' align='right'><b>Max Score</b></td>
		<td valign='top' align='right'><b>Percent</b></td>
	</tr>
	<tr>
		<td valign='top'>Stops (every graded and completed stop)</td>
		<td valign='top' align='right'>".($stops-$ungraded_stops)."</td>
		<td valign='top' align='right'>".number_format($grade_tot)."</td>
		<td valign='top' align='right'>".number_format($grade_tot_max)."</td>
		<td valign='top' align='right'>".number_format($grade_percent,2)."%</td>
	</tr>
	<tr>
		<td valign='top'>Dispatches (only graded and completed stops in dispatch)</td>
		<td valign='top' align='right'>".$dcntr."</td>
		<td valign='top' align='right'>".number_format($disp_grade_tot)."</td>
		<td valign='top' align='right'>".number_format($disp_grade_tot_max)."</td>
		<td valign='top' align='right'>".number_format($disp_grade_percent,2)."%</td>
	</tr>
	<tr>
		<td valign='top'>Loads (only graded and completed stops in dispatch)</td>
		<td valign='top' align='right'>".$lcntr."</td>
		<td valign='top' align='right'>".number_format($load_grade_tot)."</td>
		<td valign='top' align='right'>".number_format($load_grade_tot_max)."</td>
		<td valign='top' align='right'>".number_format($load_grade_percent,2)."%</td>
	</tr>
	</table>
	"; 
?>
</form>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
		
	});
	
</script>
<? include('footer.php') ?>