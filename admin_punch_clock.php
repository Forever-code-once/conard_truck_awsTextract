<? include('application.php') ?>
<? $admin_page = 1 ?>
<?
	if(!isset($_POST['punch_clock_start']))		$_POST['punch_clock_start']=date("m/01/Y");
	if(!isset($_POST['punch_clock_ender']))		$_POST['punch_clock_ender']=date("m/d/Y");
?>
<? include('header.php') ?>
<?
	if(isset($_POST['user_admin']))
	{	//reset user punch clock usage
		$sql="
               update users
               set punch_clock='0'
		";
		simple_query($sql);		
		
		$mrrcntr=0;
		if(isset($_POST['mrr_user_cntr']))		$mrrcntr=$_POST['mrr_user_cntr'];
		for($i=0;$i < $mrrcntr;	$i++)
		{
			if(!isset($_POST["user_".$i.""]))	$_POST["user_".$i.""]=0;
			$myuser=$_POST["user_".$i.""];
			if($myuser > 0)
			{
				$sql="
          			update users
          			set punch_clock='1'
          			where id='".sql_friendly( $myuser )."'
				";
				simple_query($sql);
			}
		}
	}	
	
	
	$reporter="";
	$sql="
		select *
		from users
		where active=1 and deleted=0
		order by name_last asc,name_first asc
	";
	$data=simple_query($sql);
	$row_cntr=0;
     while($row=mysqli_fetch_array($data))
     {
     	$user_id=$row['id'];
     	$username=$row['username'];
     	$name_first=$row['name_first'];
     	$name_last=$row['name_last'];
     	$pc_on=$row['punch_clock'];
     	
     	$ulinker="<span class='graph_links' title='Click to expand Times' onClick='mrr_expand_punch_clock_data(".$user_id.")'>".$username."</span>";
     	
     	$res=mrr_punch_clock_login_status($user_id,0);
     	$cur_status="Out";
     	$hrs_found="---";
     	$auto_mode="";
     	if($res['clock_mode']==1)	$cur_status="In";
     	if($res['clock_hrs']!=0)		$hrs_found=$res['clock_hrs'];
     	if($res['clock_auto'] > 0)	$auto_mode="Auto";
     	
     	if($pc_on > 0)
     	{
               $reporter.="
               	<tr>
               		<td valign='top'><input type='checkbox' name='user_".$row_cntr."' id='user_".$row_cntr."' value='".$user_id."' checked></td>
               		<td valign='top'>".$ulinker."</td>
               		<td valign='top'>".$name_first."</td>
               		<td valign='top'>".$name_last."</td>
               		<td valign='top'>".$res['ip_address']."</td>
               		<td valign='top'>".$cur_status."</td>
               		<td valign='top'>".$res['linedate_date']."</td>
               		<td valign='top'>".$res['linedate_time']."</td>
               		<td valign='top' align='right'>".$hrs_found." </td>
               		<td valign='top'>".$auto_mode."</td>
               		<td valign='top'>".$res['notes']."</td>
               	</tr>
               ";
     	}
     	else
     	{
     		$reporter.="
               	<tr>
               		<td valign='top'><input type='checkbox' name='user_".$row_cntr."' id='user_".$row_cntr."' value='".$user_id."'></td>
               		<td valign='top'>".$username."</td>
               		<td valign='top'>".$name_first."</td>
               		<td valign='top'>".$name_last."</td>
               		<td valign='top'></td>
               		<td valign='top'>N/A</td>
               		<td valign='top'></td>
               		<td valign='top'></td>
               		<td valign='top' align='right'> </td>
               		<td valign='top'></td>
               		<td valign='top'></td>
               	</tr>
               ";	
     	}
     	$row_cntr++;
	}
?>
<form action='' method='post'>
<table class='' style='text-align:left'>
<tr>
	<td valign='top' width='800'>	<br><br>	
		<table class='admin_menu1'>
		<tr>
			<td colspan='11'><font class='standard18'><b>Admin Punch Clock</b></font></td>
		</tr>
		<tr>
			<td valign='top'></td>
			<td valign='top' nowrap><b>Username</b></td>
			<td valign='top' nowrap><b>First Name</b></td>
			<td valign='top' nowrap><b>Last Name</b></td>
			<td valign='top' nowrap><b>IP Address</b></td>
			<td valign='top'><b>Status</b></td>
			<td valign='top'><b>Date</b></td>
			<td valign='top'><b>Time</b></td>
			<td valign='top' align='right'><b>Hrs</b> </td>
			<td valign='top'><b>Flag</b></td>
			<td valign='top' width='300'><b>Notes</b></td>
		</tr>
		<? echo $reporter; ?> 
		</table>
		<br><input type='hidden' name='mrr_user_cntr' value='<?= $row_cntr ?>'>
		<center><input type='submit' name='user_admin' value='Update Users'></center>
	</td>
	<td valign='top' width='800'>
		<div style='height:32px;' valign='middle'>
     		<input type='hidden' name='punch_clock_user' id='punch_clock_user' value='0'>	
     		<span class='standard18'>Select Date Range from</span>
     		<input name='punch_clock_start' id='punch_clock_start' style='width:80px' class='datepicker' value='<?=$_POST['punch_clock_start'] ?>' onChange='mrr_reload_punch_clock_data()'> 
     		<span class='standard18'>to</span>
     		<input name='punch_clock_ender' id='punch_clock_ender' style='width:80px' class='datepicker' value='<?=$_POST['punch_clock_ender'] ?>' onChange='mrr_reload_punch_clock_data()'> 
     		<span class='standard18'>for</span> <span id='punch_clock_selected' class='standard18'></span>
		</div>
		<div id='punch_clock_editor'></div>
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>
	
	$('.datepicker').datepicker();
	
	function mrr_reload_punch_clock_data()
	{
		user_id=$('#punch_clock_user').val();
		date_start=$('#punch_clock_start').val();
		date_ender=$('#punch_clock_ender').val();
		load_punch_clock_data(user_id,date_start,date_ender);
	}
	function mrr_expand_punch_clock_data(user_id)
	{
		$('#punch_clock_user').val(user_id);
		date_start=$('#punch_clock_start').val();
		date_ender=$('#punch_clock_ender').val();
		load_punch_clock_data(user_id,date_start,date_ender);
	}
	function load_punch_clock_data(user_id,date_start,date_ender) 
	{
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_expand_punch_clock_data",
		   data: {
		   		"user_id":user_id,
		   		"date_start":date_start,
		   		"date_ender":date_ender
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
				$('#punch_clock_editor').html('');
				$('#punch_clock_selected').html('');
					
					namer_txt="";
					temp_holder="";
     				temp_holder+="<table cellpadding='2' cellspacing='0' width='98%' border='1' class='admin_menu1 table_grid' style='margin:4px'>";
     				temp_holder+="<tr>";
     				//temp_holder+="<td><b>UserID</b></td>";
     				//temp_holder+="<td><b>Username</b></td>";				
     				//temp_holder+="<td><b>Stamp</b></td>";    					
     				temp_holder+="<td><b>IP Address</b></td>"; 
     				temp_holder+="<td><b>Flag</b></td>";		
     				temp_holder+="<td><b>Mode</b></td>";     					
     				temp_holder+="<td><b>Day</b></td>";
     				temp_holder+="<td><b>Date</b></td>";
     				temp_holder+="<td><b>Time</b></td>";
     				temp_holder+="<td align='right'><b>Hours</b></td>";
     				temp_holder+="<td align='right'><b>CumHrs</b></td>";  
     				temp_holder+="<td><b>Notes</b></td>";			     					
     				temp_holder+="</tr>";
     				
     				$(xml).find('PunchClock').each(function() {
     					clock_id=$(this).find('PunchClockID').text();
     					mytime=''+$(this).find('PunchClockTime').text()+'';
     					
     					hider1="<input type='hidden' id='prev_clock_"+clock_id+"' value='"+$(this).find('PunchClockPrevID').text()+"'>";
     					hider2="<input type='hidden' id='next_clock_"+clock_id+"' value='"+$(this).find('PunchClockNextID').text()+"'>";
     					
     					hider3="<input type='hidden' id='auto_clock_"+clock_id+"' value='"+$(this).find('PunchClockAuto').text()+"'>";
     					hider4="<input type='hidden' id='mode_clock_"+clock_id+"' value='"+$(this).find('PunchClockMode').text()+"'>";
     					
     					namer_txt=""+$(this).find('PunchClockFirstName').text()+" "+$(this).find('PunchClockLastName').text()+"";
     					
     					temp_holder+="<tr class='"+clock_id+"'>";
     					//temp_holder+="<td>"+$(this).find('PunchClockUserID').text()+"</td>";
     					//temp_holder+="<td>"+$(this).find('PunchClockUsername').text()+"</td>";				
     					//temp_holder+="<td>"+$(this).find('PunchClockStamp').text()+"</td>";    					
     					temp_holder+="<td>"+$(this).find('PunchClockIP').text()+""+hider1+""+hider2+""+hider3+""+hider4+"</td>"; 
     					temp_holder+="<td>"+$(this).find('PunchClockAutoDisp').text()+"</td>";		
     					temp_holder+="<td>"+$(this).find('PunchClockModeDisp').text()+"</td>";     
     					temp_holder+="<td>"+$(this).find('PunchClockDay').text()+"</td>";					
     					temp_holder+="<td><span id='dater_"+clock_id+"'>"+$(this).find('PunchClockDate').text()+"</span></td>";
     					temp_holder+="<td><input type='text' id='time_setter_"+clock_id+"' value='"+mytime+"' onChange='mrr_update_punch_clock_timer("+clock_id+","+user_id+");' style='width:50px; text-align:right;' maxlength='5'></td>";
     					temp_holder+="<td align='right'>"+$(this).find('PunchClockHours').text()+"</td>";
     					temp_holder+="<td align='right'>"+$(this).find('PunchClockTotHrs').text()+"</td>";  
     					temp_holder+="<td><textarea id='notes_clock_"+clock_id+"' style='width:300px; height:17px;' wrap='virtual' onChange='mrr_update_punch_clock_notes("+clock_id+","+user_id+");'>"+$(this).find('PunchClockNotes').text()+"</textarea></td>";			     					
     					temp_holder+="</tr>";
     					
     				});	
     				temp_holder+="</table>";
				
				$('#punch_clock_editor').html(temp_holder);
				$('#punch_clock_selected').html(namer_txt);
		   }
		 });
	}	
	function mrr_update_punch_clock_notes(clock_id,user_id)
	{		
		my_notes=$("#notes_clock_"+clock_id+"").val();
		
		txt="<br>Changing Clock ID="+clock_id+".";
		txt+="<br>User ID="+user_id+".";
		txt+="<br>Clock Notes='<b>"+my_notes+"</b>'.";
		txt+="<br><br>";
		//$.prompt(txt);
				
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_update_punch_clock_data_notes",
		   data: {
		   		"clock_id":clock_id,
		   		"new_notes":my_notes
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
				     				
     				$(xml).find('NewNotes').each(function() {     					
     					
     					//$.prompt("Success...Notes updated."); 
     					mrr_expand_punch_clock_data(user_id);    					
     				});	
		   }
		 });
	}
	function mrr_update_punch_clock_timer(clock_id,user_id)
	{
		
		my_prev=$("#prev_clock_"+clock_id+"").val();
		my_next=$("#next_clock_"+clock_id+"").val();
		my_auto=$("#auto_clock_"+clock_id+"").val();
		my_mode=$("#mode_clock_"+clock_id+"").val();
		my_hours=$("#time_setter_"+clock_id+"").val();
		my_dater=$("#dater_"+clock_id+"").html();
		
		txt="<br>Changing Clock ID="+clock_id+".";
		txt+="<br>User ID="+user_id+".";
		txt+="<br>Hours will be changed to "+my_hours+".";
		txt+="<br>Previous Clock ID="+my_prev+".";
		txt+="<br>Next Clock ID="+my_next+".";
		txt+="<br>Clock Mode="+my_auto+".";
		txt+="<br>Clock Auto="+my_mode+".";
		txt+="<br>Clock Date="+my_dater+".";
		txt+="<br><br>";		
		//$.prompt(txt);	
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_update_punch_clock_data_hrs",
		   data: {
		   		"clock_id": clock_id ,
		   		"user_id": user_id,
		   		"mode_id": my_mode,
		   		"prev_id": my_prev,
		   		"next_id": my_next,
		   		"new_time": my_hours,
		   		"dater": my_dater
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
				     				
     				$(xml).find('NewTime').each(function() {     					
     					
     					//$.prompt("Success...Time updated."); 
     					mrr_expand_punch_clock_data(user_id);			
     				});	
		   }
		 });
	}
	
	
</script>

<? include('footer.php') ?>