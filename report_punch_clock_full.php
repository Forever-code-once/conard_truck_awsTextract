<? include('header.php') ?>
<?
	$use_title="Reports - Punch Clock Full";
	$my_user_id=0;
	if(isset($_GET['user_id'])) {
		$_POST['build_report'] = 1;
		$my_user_id=$_GET['user_id'];
	}	
	
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}

	if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("n/j/Y", time());
	if(!isset($_POST['driver_id'])) 	$_POST['driver_id'] = 0;
	if(!isset($_POST['employer_id'])) 	$_POST['employer_id'] = 0;
	
	$rfilter = new report_filter();
	//$rfilter->show_driver 		= true;
	//$rfilter->show_employers 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
	
	//echo "<br>ID=".$my_user_id.".<br>";
	
?>


<? if(isset($_POST['build_report']) || $my_user_id > 0) { ?>
	<?
	$mrr_adder="";
	if($my_user_id > 0)	
	{
		$mrr_adder=" and id='".sql_friendly($my_user_id)."' ";
	}
	
	$tot_hours=0;
	$reporter="";
	$sql="
		select *
		from users
		where active=1 and deleted=0 and punch_clock > 0
			".$mrr_adder."
		order by name_last asc,name_first asc
	";
	$data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {
     	$user_id=$row['id'];
     	$username=$row['username'];
     	$name_first=$row['name_first'];
     	$name_last=$row['name_last'];
     	$user_hours=0;
     	
     	$ulinker="<a href='admin_punch_clock.php' target='_blank' title='View Full Report for ".$username.".'>".$username."</a>";
     	
     	$res=mrr_punch_clock_login_status($user_id,0);
     	$cur_status="Logged Out";
     	$hrs_found="---";
     	$auto_mode="";
     	if($res['clock_mode']==1)	$cur_status="Logged In";
     	if($res['clock_hrs']!=0)		$hrs_found=$res['clock_hrs'];
     	if($res['clock_auto'] > 0)	$auto_mode="Auto";
     	
     	$sql2="
     		select punch_clock.id
				from punch_clock,users
				where punch_clock.linedate_added>='".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' 
					and punch_clock.linedate_added<='".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
					and punch_clock.user_id='".sql_friendly($user_id)."' 
					and punch_clock.user_id=users.id
					and users.punch_clock>0
				order by punch_clock.linedate_added asc
     	";
     	$data2=simple_query($sql2);
     	$mncnt=mysqli_num_rows($data2);		//make sure actual time was entered for today...even if hours were auto-clocked out.
          	
     	if($res['clock_hrs'] > 0 && $mncnt>0)
     	{
          	$reporter.="
          		<table class='admin_menu1 font_display_section' style='margin:0 10px;width:1000px;text-align:left'>
          		<tr>
               		<td colspan='8'>
               			<center>
               			<span class='section_heading'>Punch Clock Activity Report for ".$name_first." ".$name_last."</span>
               			</center>
               		</td>
               	</tr>
               	<tr>
               		<td valign='top'><b>Username:</b></td>          		
               		<td valign='top'>".$ulinker."</td>          		
               		<td valign='top'><b>Current Status:</b></td>  
               		<td valign='top'>".$cur_status."</td>
               		<td valign='top'>".$res['linedate_date']." ".$res['linedate_time']."</td>
               		<td valign='top'></td> 
               		<td valign='top'><b>Hours:</b></td> 
               		<td valign='top'>".$hrs_found." </td>
               	</tr>	
               	<tr>		
               		<td valign='top'>&nbsp;</td>
               		<td valign='top'><b>Flag:</b></td> 
               		<td valign='top'>".$auto_mode."</td>
               		<td valign='top'><b>Notes:</b></td> 
               		<td valign='top' colspan='4'>".$res['notes']."</td>
               	</tr>
               	<tr>               		
                    		<td valign='top' colspan='8'><hr></td>
                    </tr>
               	<tr>               		
               		<td valign='top' width='120'><b>IP Address</b></td>
               		<td valign='top' width='100'><b>Status</b></td>
               		<td valign='top' width='100'><b>Day</b></td>
               		<td valign='top' width='100'><b>Date</b></td>
               		<td valign='top' width='80'><b>Time</b></td>
               		<td valign='top' width='100' align='right'><b>Hours</b> </td>
               		<td valign='top' width='100'><b>Flag</b></td>
               		<td valign='top'><b>Notes</b></td>
               	</tr>
               ";
          	
               while($row2=mysqli_fetch_array($data2))
               {
               	$id=$row2['id'];
          		$res=mrr_punch_clock_vals($id);
               	$cur_status="Logged Out";
          		$hrs_found="---";
          		$auto_mode="";
          		if($res['clock_mode']==1)	$cur_status="Logged In";
          		if($res['clock_hrs']!=0)		$hrs_found=$res['clock_hrs'];
          		if($res['clock_auto'] > 0)	$auto_mode="Auto";
          		//$username=$res['username'];
          		//$name_first=$res['name_first'];
          		//$name_last=$res['name_last'];
          		//$user_id=$res['id'];
          		//$ulinker="<a href='report_punch_clock_full.php' target='_blank' title='View Full Report for ".$username.".'>".$username."</a>";
          		
                    if($hrs_found >= 0)
     			{
          			$user_hours+=$res['clock_hrs'];
                    	$reporter.="
                         	<tr>               		
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
               $reporter.="
             			<tr>               		
                    		<td valign='top' colspan='8'><hr></td>
                    	</tr>
             			<tr>               		
                    		<td valign='top'>Total Hours</td>
                    		<td valign='top'>&nbsp;</td>
                    		<td valign='top'>&nbsp;</td>
                    		<td valign='top'>&nbsp;</td>
                    		<td valign='top'>&nbsp;</td>
                    		<td valign='top' align='right'>".number_format($user_hours,2)." </td>
                    		<td valign='top'>&nbsp;</td>
                    		<td valign='top'>&nbsp;</td>
                    	</tr>
               	</table>
               	<br>
               	<br>
               ";
               $tot_hours+=$user_hours;
     	}
	}
		
	ob_start();
	?>
	
	<? echo $reporter ?>
		
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