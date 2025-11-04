<? include('header.php') ?>
<?
	$use_title="Reports - Punch Clock Users";
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}

	if(!isset($_POST['date_from'])) $_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) $_POST['date_to'] = date("n/j/Y", time());
	if(!isset($_POST['driver_id'])) $_POST['driver_id'] = 0;
	if(!isset($_POST['employer_id'])) $_POST['employer_id'] = 0;
	
	$rfilter = new report_filter();
	//$rfilter->show_driver 		= true;
	//$rfilter->show_employers 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
?>


<? if(isset($_POST['build_report'])) { ?>
	<?
	$reporter="";
	$sql="
		select *
		from users
		where active=1 and deleted = 0 and punch_clock > 0
		order by name_last asc,name_first asc
	";
	$data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {
     	$user_id=$row['id'];
     	$username=$row['username'];
     	$name_first=$row['name_first'];
     	$name_last=$row['name_last'];
     	
     	$ulinker="<a href='report_punch_clock_full.php?user_id=".$user_id."' target='_blank' title='View Full Report for ".$username.".'>".$username."</a>";
     	
     	$res=mrr_punch_clock_login_status($user_id,0);
     	$cur_status="Out";
     	$hrs_found="---";
     	$auto_mode="";
     	if($res['clock_mode']==1)	$cur_status="In";
     	if($res['clock_hrs']!=0)		$hrs_found=$res['clock_hrs'];
     	if($res['clock_auto'] > 0)	$auto_mode="Auto";
     	
          $reporter.="
          	<tr>
          		<td valign='top'>".$ulinker."</td>
          		<td valign='top'>".$name_first."</td>
          		<td valign='top'>".$name_last."</td>
          		<td valign='top'>".$res['ip_address']."</td>
          		<td valign='top'>".$cur_status."</td>
          		<td valign='top'>".$res['linedate_day']."</td>
          		<td valign='top'>".$res['linedate_date']."</td>
          		<td valign='top'>".$res['linedate_time']."</td>
          		<td valign='top' align='right'>".$hrs_found." </td>
          		<td valign='top'>".$auto_mode."</td>
          		<td valign='top'>".$res['notes']."</td>
          	</tr>
          ";
	}
	
	$reporter2="";
	$sql="
		select punch_clock.id
		from punch_clock,users
		where punch_clock.linedate_added>='".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' 
			and punch_clock.linedate_added<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
			and punch_clock.user_id=users.id
			and users.punch_clock>0
		order by punch_clock.linedate_added asc
	";
	$data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {
     	$id=$row['id'];
     	$res=mrr_punch_clock_vals($id);
     	$cur_status="Out";
     	$hrs_found="---";
     	$auto_mode="";
     	if($res['clock_mode']==1)	$cur_status="In";
     	if($res['clock_hrs']!=0)		$hrs_found=$res['clock_hrs'];
     	if($res['clock_auto'] > 0)	$auto_mode="Auto";
     	$username=$res['username'];
     	$name_first=$res['name_first'];
     	$name_last=$res['name_last'];
     	$user_id=$res['user_id'];
     	$ulinker="<a href='report_punch_clock_full.php?user_id=".$user_id."' target='_blank' title='View Full Report for ".$username.".'>".$username."</a>";
     	
          if($hrs_found >= 0)
     	{
          	$reporter2.="
          	<tr>
          		<td valign='top'>".$ulinker."</td>
          		<td valign='top'>".$name_first."</td>
          		<td valign='top'>".$name_last."</td>
          		<td valign='top'>".$res['ip_address']."</td>
          		<td valign='top'>".$cur_status."</td>
          		<td valign='top'>".$res['linedate_day']."</td>
          		<td valign='top'>".$res['linedate_date']."</td>
          		<td valign='top'>".$res['linedate_time']."</td>
          		<td valign='top' align='right'>".$hrs_found." </td>
          		<td valign='top'>".$auto_mode."</td>
          		<td valign='top'>".$res['notes']."</td>
          	</tr>
          	";
          }
	}
	
	
	ob_start();
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1000px;text-align:left'>
	<tr>
		<td colspan='10'>
			<center>
			<span class='section_heading'>Punch Clock Report (Current Status)</span>
			</center>
		</td>
	</tr>	
	<tr>
		<td valign='top'><b>Username</b></td>
		<td valign='top'><b>First Name</b></td>
		<td valign='top'><b>Last Name</b></td>
		<td valign='top'><b>IP Address</b></td>
		<td valign='top'><b>Status</b></td>
		<td valign='top'><b>Day</b></td>
		<td valign='top'><b>Date</b></td>
		<td valign='top'><b>Time</b></td>
		<td valign='top' align='right'><b>Hours</b> </td>
		<td valign='top'><b>Flag</b></td>
		<td valign='top'><b>Notes</b></td>
	</tr>
	
	<?= $reporter ?>
	</table>
	<br>	
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1000px;text-align:left'>
	<tr>
		<td colspan='10'>
			<center>
			<span class='section_heading'>Punch Clock Report (Activity)</span>
			</center>
		</td>
	</tr>
	<tr>
		<td valign='top'><b>Username</b></td>
		<td valign='top'><b>First Name</b></td>
		<td valign='top'><b>Last Name</b></td>
		<td valign='top'><b>IP Address</b></td>
		<td valign='top'><b>Status</b></td>
		<td valign='top'><b>Day</b></td>
		<td valign='top'><b>Date</b></td>
		<td valign='top'><b>Time</b></td>
		<td valign='top' align='right'><b>Hours</b> </td>
		<td valign='top'><b>Flag</b></td>
		<td valign='top'><b>Notes</b></td>
	</tr>
	
	<?= $reporter2 ?>
	</table>
	
	
	<?
	$pdf = ob_get_contents();
	ob_end_clean();
	
	echo $pdf;
	?>
<? } ?>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
</script>
<? include('footer.php') ?>