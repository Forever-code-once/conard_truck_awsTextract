<? include('application.php') ?>
<? 
	$admin_page = 1;
	
	$result_message="Ready.";
	
	if(isset($_GET['driver_id']))	
	{
		$truck_id=mrr_find_pn_drivers_truck_from_elog($_GET['driver_id'],date("Y-m-d"));
		$_GET['truck_id']=$truck_id;
		$_POST['truck_id']=$truck_id;
	}	
	
	if(isset($_GET['truck_id']))			$_POST['truck_id']=$_GET['truck_id'];	
	if(isset($_GET['reply_id']))			$_POST['reply_id']=$_GET['reply_id'];
	
	if(!isset($_POST['truck_id']))		$_POST['truck_id']=0;
	if(!isset($_POST['truck_name']))		$_POST['truck_name']="";
	
	if(!isset($_POST['reply_id']))		$_POST['reply_id']=0;
	if(!isset($_POST['emessage']))		$_POST['emessage']="";
	
	if($_POST['truck_id'] > 0)
	{
		$sql = "
			select name_truck 
			from trucks 
			where id='".sql_friendly($_POST['truck_id'])."'
		";
		$data=simple_query($sql);
		if($row=mysqli_fetch_array($data))
		{
			$_POST['truck_name']=trim($row['name_truck']);	
			$_POST['truck_name']=str_replace(" (Team Rate)","",$_POST['truck_name']);
		}
	}
	
	mrr_trim_old_truck_tracking_plot_points(7);	//remove truck_tracking points older than 7 days
	mrr_peoplenet_duel_msg_archiver(7);		//number is days to consider messages new.  If 7, messages are flagged as archived after 7 days. 
		
	$truck_box=mrr_peoplenet_truck_selector("truck_id",$_POST['truck_id']);
	$mydate=date("Y-m-d");		//today...
	$truck_drivers="";
	$truck_drivers2="";
	if($_POST['truck_id'] > 0)
	{
		$truck_drivers=mrr_find_pn_truck_drivers($_POST['truck_id'],$mydate);
		$truck_drivers2=mrr_find_pn_truck_drivers_from_elog($_POST['truck_id'],$mydate);
	}
	
	$pick_list="";
	if($_POST['truck_id'] == -1)
	{		
		if(!isset($_POST['truck_group']))	
		{	//set none as preselected....
			$_POST['truck_group']=array();
			$_POST['truck_group'][0]=0;
			$_POST['truck_group_names']=array();
			$_POST['truck_group_names'][0]="";
		}	
		$pick_list=mrr_peoplenet_truck_grouper('truck_group',$_POST['truck_group']);	
	}
	
	$serve_output="";
	$send_msg="";
	$kill_switch_on=0;
	
	if(isset($_POST['send_it']))
	{
		$_POST['service_type']="imessage_send";
		
		$result_message="Sending Message...";
		
		//special dispatch confirmation message....................................................
		$special_msg_flag=0;
		if($_POST['canned_message_id']==26)
		{
			$msg_tmp=trim($_POST['emessage']);
			
			$msg_tmp=str_replace("Load [load_id]:","your current load",$msg_tmp);
			$msg_tmp=str_replace("Dispatch [dispatch_id]","",$msg_tmp);	
			
			$special_msg_flag=1;
			$_POST['emessage']=trim($msg_tmp);
		}
		//.........................................................................................
		
		$_POST['emessage']=str_replace("#","(pound sign)",$_POST['emessage']);
		$_POST['emessage']=str_replace("&","and",$_POST['emessage']);
		$_POST['emessage']=str_replace("%","percent",$_POST['emessage']);
		
		
		if($_POST['truck_id']==0 && $_POST['service_type']=="imessage_send")
		{	//send messages to all trucks
			$tres=mrr_peoplenet_truck_array();
			$truck_cnt=$tres['num'];
			$truck_arr=$tres['arr'];
			$truck_namers=$tres['names'];
			$sent_cnt=0;
			
			for($t=0;$t < $truck_cnt; $t++)
			{
				$tid=$truck_arr[$t];
				$tname=$truck_namers[$t];
				
				$_SESSION['peoplenet_new_msg_id']=0;
				$serve_output=mrr_peoplenet_find_data($_POST['service_type'],$tid,0,$_POST['emessage'],0,0,"",$special_msg_flag);					
				
     			//if the session variable has been set, the message was saved.  This variable is set in the mrr_peoplenet_find_data function only for the imessage_send service.
     			$new_msg_id=$_SESSION['peoplenet_new_msg_id'];
     			if($new_msg_id  > 0)		$sent_cnt++;
     			
     			if($_POST['reply_id'] > 0 && $new_msg_id > 0)
     			{
     				$sql = "
     					update ".mrr_find_log_database_name()."truck_tracking_messages set
     						reply_msg_id='".sql_friendly($_POST['reply_id'])."'
     					where id='".sql_friendly($new_msg_id)."'
     					";
     				simple_query($sql);	
     				$sql = "
						update ".mrr_find_log_database_name()."truck_tracking_msg_history set
							user_id_reply='".sql_friendly($_SESSION['user_id'])."',
							linedate_reply=NOW()
						where id='".sql_friendly($_POST['reply_id'])."'
						";
					simple_query($sql);					
     			}				
			}
			if($sent_cnt > 0)		$_POST['emessage']="";
			
			$result_message="<span style='color:#00CC00; font-weight:bold;'>Message sent to ".$sent_cnt." PeopleNet-tracked trucks.</span>";
			$send_msg="<div class='mrr_maint_recur'>Message sent to ".$sent_cnt." PeopleNet-tracked trucks.</div>";
		}
		elseif($_POST['truck_id']==-1 && $_POST['service_type']=="imessage_send" && isset($_POST['truck_group']))
		{	//send messages to all trucks IN GROUP
			
			$truck_cnt=count($_POST['truck_group']);
			$truck_arr=$_POST['truck_group'];
			//$truck_namers=$_POST['truck_group_names'];
			
			$sent_cnt=0;
			$sent_list="";
			
			for($t=0;$t < $truck_cnt; $t++)
			{
				$tid=$truck_arr[$t];
				//$tname=$truck_namers[$t];
				
				$sqlt = "
                    	select name_truck 
                    	from trucks 
                    	where id='".sql_friendly($tid)."'
                    ";
                    $datat=simple_query($sqlt);
               	if($rowt=mysqli_fetch_array($datat))
               	{
               		$tname="".trim($rowt['name_truck'])."";
               	}
				
				$_SESSION['peoplenet_new_msg_id']=0;
				$serve_output=mrr_peoplenet_find_data($_POST['service_type'],$tid,0,$_POST['emessage'],0,0,"",$special_msg_flag);	
				//$_SESSION['peoplenet_new_msg_id']=1;				
				
     			//if the session variable has been set, the message was saved.  This variable is set in the mrr_peoplenet_find_data function only for the imessage_send service.
     			$new_msg_id=$_SESSION['peoplenet_new_msg_id'];
     			if($new_msg_id  > 0)	{	$sent_cnt++;				$sent_list.=", ".$tname."";		}
     			
     			if($_POST['reply_id'] > 0 && $new_msg_id > 0)
     			{
     				$sql = "
     					update ".mrr_find_log_database_name()."truck_tracking_messages set
     						reply_msg_id='".sql_friendly($_POST['reply_id'])."'
     					where id='".sql_friendly($new_msg_id)."'
     					";
     				simple_query($sql);	
     				$sql = "
						update ".mrr_find_log_database_name()."truck_tracking_msg_history set
							user_id_reply='".sql_friendly($_SESSION['user_id'])."',
							linedate_reply=NOW()
						where id='".sql_friendly($_POST['reply_id'])."'
						";
					simple_query($sql);					
     			}				
			}
			if($sent_cnt > 0)		$_POST['emessage']="";
			
			$result_message="<span style='color:#00CC00; font-weight:bold;'>Message sent to ".$sent_cnt." PeopleNet-tracked trucks".$sent_list."</span>";
			$send_msg="<div class='mrr_maint_recur'>Message sent to ".$sent_cnt." PeopleNet-tracked trucks".$sent_list."</div>";
			
		}
		else
		{
			$_SESSION['peoplenet_new_msg_id']=0;
			$serve_output=mrr_peoplenet_find_data($_POST['service_type'],$_POST['truck_id'],0,$_POST['emessage'],0,0,"",$special_msg_flag);	
			
			//if the session variable has been set, the message was saved.  This variable is set in the mrr_peoplenet_find_data function only for the imessage_send service.
			$new_msg_id=$_SESSION['peoplenet_new_msg_id'];
			
			if($_POST['reply_id'] > 0 && $new_msg_id > 0)
			{
				$sql = "
					update ".mrr_find_log_database_name()."truck_tracking_messages set
						reply_msg_id='".sql_friendly($_POST['reply_id'])."'
					where id='".sql_friendly($new_msg_id)."'
					";
				simple_query($sql);	
				$sql = "
					update ".mrr_find_log_database_name()."truck_tracking_msg_history set
						user_id_reply='".sql_friendly($_SESSION['user_id'])."',
						linedate_reply=NOW()
					where id='".sql_friendly($_POST['reply_id'])."'
					";
				simple_query($sql);									
			}
			if($new_msg_id  > 0)
			{
				$_POST['emessage']="";
				$send_msg="<div class='mrr_maint_recur'>Message has been sent.</div>";
				$result_message="<span style='color:#00CC00; font-weight:bold;'>Message has been sent.</span>";
			}
			else
			{
				$send_msg="<div class='alert'>Error: unable to send message.</div>".$serve_output."<br>";	
				$result_message="<span style='color:#CC0000; font-weight:bold;'>Message has NOT been sent.</span>";
			}
		}
	}
	
	if(!isset($_POST['canned_message_id']))		$_POST['canned_message_id']=0;
	$canned_messages=mrr_peoplenet_select_canned_message('canned_message_id',$_POST['canned_message_id'],500);
?>
<? include('header.php') ?>
<form name='peoplenet_messenger' action='<?= $SCRIPT_NAME ?>' method='post' onSubmit='return mrr_confirm_message_to_all_drivers();'>
	<input type='hidden' name='date_from' id='date_from' value='01/01/2012'>
	<input type='hidden' name='date_to' id='date_to' value='<?= date("m/d/Y") ?>'>
	<input type='hidden' name='truck_name' id='truck_name' value='<?= $_POST['truck_name'] ?>'>
	<input type='hidden' name='reply_id' id='reply_id' value='<?= $_POST['reply_id'] ?>'>
		
<table class='admin_menu2' style='width:1600px;'>
<tr>
	<td valign='top' colspan='4'><?= $send_msg ?></td>
</tr>
<tr>
	<td valign='top' colspan='2' width='810'><div class='section_heading'>PeopleNet Messenger</div></td>
	<td valign='top' colspan='2' width='790'><div class='section_heading'>Reply Message Viewer</div></td>
</tr>
<tr>
	<td valign='top'>Truck  <?= show_help('peoplenet_messager.php','Truck Select') ?></td>
	<td valign='top'>
		<?= $truck_box ?> 
		<div style='display:inline-block; border:0px solid #CC0000; padding-left:5px;'>
			Driver(s): <?= $truck_drivers ?>
			<br>PN E-log Driver(s): <b><?= $truck_drivers2 ?></b>
		</div>
		<br><?= $pick_list ?>
	</td>	
	<td valign='top'><div id='msg_reader_block_from'></div></td>
	<td valign='top'><div id='msg_reader_block_date'></div></td>
</tr>
<tr>
	<td valign='top'>For Message  <?= show_help('peoplenet_messager.php','Message Text') ?> <br><br><br><a href='/geotab_messenger.php' target='_blank'>View GeoTab Messages</a></td>
	<td valign='top'>
		<?= $canned_messages ?> <input type='button' name='insert_canned_msg' id='insert_canned_msg' value='Add Text' onClick='insert_canned_message();'>  <?= show_help('peoplenet_messager.php','Canned Message') ?>	
		<br>
		<textarea name='emessage' id='emessage' rows='8' cols='70' wrap='virtual'><?= $_POST['emessage'] ?></textarea>	
		<div style='border:1px solid #000000;'><?=$result_message ?></div>	
	</td>
	<td valign='top' colspan='2'><div id='msg_reader_block_msg'></div></td>
</tr>
<tr>
	<td valign='top'><a href='/'>Return to Load Board</a></td>
	<td valign='top' align='right'>
		<?
		$mrr_now_test=date("m/d/Y H:i:s");
		$mrr_now_test_stamp1=date("m/d/Y H:i:s",strtotime($mrr_now_test));;
		$mrr_now_test_stamp2=gmdate("m/d/Y H:i:s",strtotime($mrr_now_test));;
		?>
		Local Time: <b><?= $mrr_now_test_stamp1 ?></b>&nbsp;&nbsp;&nbsp;
		GMT Time:   <b><?= $mrr_now_test_stamp2 ?></b>&nbsp;&nbsp;&nbsp;
		<input type='submit' name='send_it' id='send_it' value='Send Message'>
	</td>  
	<td valign='top'>
		<div id='msg_reader_block_flag1'></div>
		<div id='msg_reader_block_flag3'></div>
		<div id='msg_reader_block_flag5'></div>
	</td>
	<td valign='top'>
		<div id='msg_reader_block_flag2'></div>
		<div id='msg_reader_block_flag4'></div>
		<div id='msg_reader_block_flag6'></div>
	</td>
</tr>
<!--
<tr>
	<td valign='top' colspan='4'>
		Test Area Below...disregard this...<br>
		<div style='border:1px solid #CC0000;'>
		<?
		$offset_gmt=mrr_gmt_offset_val();
		$offset_gmt=$offset_gmt * -1;
		$mrr_date_test="1978-04-14 14:00:00";
		$stamp0=date("m/d/Y H:i",strtotime("+".($offset_gmt-1)." hours",strtotime($mrr_date_test)));		//trip start time
     	$stamp =date("m/d/Y H:i",strtotime("+".($offset_gmt  )." hours",strtotime($mrr_date_test)));		//arriving time     			
     	$stamp2=date("m/d/Y H:i",strtotime("+".($offset_gmt+1)." hours",strtotime($mrr_date_test)));		//arrived time
     	$stamp3=date("m/d/Y H:i",strtotime("+".($offset_gmt+2)." hours",strtotime($mrr_date_test)));		//departed time
		?>
		Trip Start Time: <?=$mrr_date_test ?> <?= "+".($offset_gmt-1)." hours = ".$stamp0 ?><br>
		Arriving Time: <?=$mrr_date_test ?> <?= "+".($offset_gmt  )." hours = ".$stamp ?><br>
		Arrived Time: <?=$mrr_date_test ?> <?= "+".($offset_gmt+1)." hours = ".$stamp2 ?><br>
		Departed Time: <?=$mrr_date_test ?> <?= "+".($offset_gmt+2)." hours = ".$stamp3 ?><br>
		</div>
	</td>  
</tr>
-->
<tr>
	<td valign='top' colspan='4'>		
		<div id='all_new_messages' style='border:1px solid #0000CC; padding:10px;'>
		<b>New Messages from all Trucks:</b><br><br>
		<table id='all_new_unread_messages'>
		</table>
		</div>
	</td>  
</tr>
<?
	/*
	$tabby="&nbsp;";	//mrr_display_current_dispatch_tracking($_POST['truck_id']);
	echo "
		<tr>
			<td valign='top' colspan='4'>	
				<div style='border:1px solid #CC0000; padding:10px;'>".$tabby."</div>
			</td>
		</tr>	
	";
	*/
?>
</table>
<br>
<table class='admin_menu2' style='width:1600px;'>
<tr>
	<td valign='top' width='790'><div class='section_heading'>Messages Sent by Truck</div></td>
	<td valign='top' width='20'>&nbsp;</td>
	<td valign='top' width='790'><div class='section_heading'>Messages Received by Truck</div></td>
</tr>
<tr>
	<td valign='top'><div style='height:300px; max-height:300px; overflow:auto;' id='sent_messages'></div></td>
	<td valign='top'>&nbsp;</td>
	<td valign='top'><div style='height:300px; max-height:300px; overflow:auto;' id='received_messages'></div></td>
</tr>
<tr>
	<td valign='top' colspan='3'><hr></td>
</tr>
<tr>
	<td valign='top' colspan='3'><div class='section_heading'>Dispatches Sent to Truck</div></td>
</tr>
<tr>
	<td valign='top' colspan='3'><div style='height:300px; max-height:300px; overflow:auto;' id='sent_dispatches'></div></td>
</tr>
<tr>
	<td valign='top' width='790'><hr></td>
	<td valign='top' width='20'>&nbsp;</td>
	<td valign='top' width='790'><hr></td>
</tr>
<tr>
	<td valign='top' width='790'><div class='section_heading'>Archived Messages Sent by Truck <span class='mrr_link_like_on' title=' Click to see the full archive of all Messages Sent by Truck.' onClick='mrr_load_archive_sent();'>Load Archive</span></div></td>
	<td valign='top' width='20'>&nbsp;</td>
	<td valign='top' width='790'><div class='section_heading'>Archived Messages Received by Truck <span class='mrr_link_like_on' title=' Click to see the full archive of all Messages Received by Truck.' onClick='mrr_load_archive_received();'>Load Archive</span></div></td>
</tr>
<tr>
	<td valign='top'><div style='height:300px; max-height:300px; overflow:auto;' id='archived_sent_messages'></div></td>
	<td valign='top'>&nbsp;</td>
	<td valign='top'><div style='height:300px; max-height:300px; overflow:auto;' id='archived_received_messages'></div></td>
</tr>
<tr>
	<td valign='top' colspan='3'><hr></td>
</tr>
<tr>
	<td valign='top' colspan='3'><div class='section_heading'>Archived Dispatches Sent to Truck <span class='mrr_link_like_on' title=' Click to see the full archive of all Dispatches Sent by Truck.' onClick='mrr_load_archive_dispatched();'>Load Archive</span></div></td>
</tr>
<tr>
	<td valign='top' colspan='3'><div style='height:300px; max-height:300px; overflow:auto;' id='archived_sent_dispatches'></div></td>
</tr>
</table>
</form>
<script type='text/javascript'>	
	var mrr_user_id=<?= (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0') ?>;
	var mrr_load_sent_archive=0;
	var mrr_load_received_archive=0;
	var mrr_load_dispatched_archive=0;
	
	
	$().ready(function() 
	{			
		$('#sent_messages').html('Loading Sent Messages...');
		mrr_fetch_sent_messages(0,0);
		
		$('#received_messages').html('Loading Received Messages...');	
		mrr_fetch_received_messages(0,0);
		
		$('#sent_dispatches').html('Loading Sent Dispatches...');
		mrr_fetch_sent_dispatches(0,0);
				
		$('.mrr_block_details').toggle();
		
		if($('#truck_id').val() ==0)
		{
			$('#all_new_messages').show();
		}
		else
		{
			$('#all_new_messages').hide();		
		}
		
		
		
		tmper=$('#reply_id').val();
		tmper=parseInt(tmper);
		if(tmper > 0)		mrr_fill_reader( tmper );	
				
		setInterval( function() 
          {
              	mrr_check_for_new_messages();  
              	
              	if($('#truck_id').val() ==0)
			{
				$('#all_new_messages').show();
			}
			else
			{
				$('#all_new_messages').hide();		
			}
              	        	
          },(1000 * 600));		//2 minutes...1000=1 second
	});	
	
	function mrr_confirm_message_to_all_drivers()
	{
		if($('#truck_id').val() ==0)
		{
			return confirm("Are you sure you want to send a message to ALL TRUCKS?");
			/*
			$.prompt("Are you sure you want to send a message to <span class='alert'>ALL TRUCKS</span>?", {
				buttons: {Yes: true, No:false},
					submit: function(v, m, f) {
						if(v) {
							//return true;
						}
					}
				}
			);	
			*/	
		}
		else
		{
			return true;
		}	
	}
	
	function insert_canned_message()
	{
		msgid=$('#canned_message_id').val();
		otxt=$('#emessage').val();
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_fetch_canned_message",
			   data: {
			   		"message_id":msgid
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrrtab=$(xml).find("mrrTab").text();
					if(otxt=="")	
					{
						otxt = mrrtab;
					}
					else
					{
						otxt = otxt + " " + mrrtab;	
					}					
					$('#emessage').val(otxt);
			   }
		});	
	}
	
	function mrr_ignore_new_messages(id,userid)
	{			
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_pn_update_incoming_message_ignore",
			   data: {
			   		"msg_id":id,
			   		"user_id":userid
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrr_fetch_sent_messages(0,0);
			   }
		});
	}
	
	function mrr_check_for_new_messages()
	{			
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_truck_tracking_messages_check_for_new",
			   data: {
			   		
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrr_fetch_sent_messages(0,0);
			   }
		});
	}
	
	function mrr_fill_reader(id)
	{
		tmp1=$('#msg_list_created_'+id+'').html();
		tmp2=$('#msg_list_received_'+id+'').html();
		tmp3=$('#msg_list_recipient_'+id+'').html();
		tmp4=$('#msg_list_read_user_'+id+'').html();	
		tmp5=$('#msg_list_read_date_'+id+'').html();
		tmp6=$('#msg_list_reply_user_'+id+'').html();
		tmp7=$('#msg_list_reply_date_'+id+'').html();
		tmp8=$('#msg_list_msg_'+id+'').html();
		
		tmp9=$('#msg_list_driver_'+id+'').html();
				
		$('#msg_reader_block_from').html('<b>From:</b> '+tmp3+' [<b>'+tmp9+'</b>]');
		$('#msg_reader_block_date').html('<b>Date:</b> '+tmp1);
		$('#msg_reader_block_msg').html('<b>Message:</b> '+tmp8);
		$('#msg_reader_block_flag1').html('<b>Received:</b> '+tmp2);
		$('#msg_reader_block_flag2').html('<b>Sent from Truck</b>');
		$('#msg_reader_block_flag3').html('<b>Read by:</b> '+tmp4);
		$('#msg_reader_block_flag4').html('<b>Read Date:</b> '+tmp5);
		$('#msg_reader_block_flag5').html('<b>Replied by:</b> '+tmp6);
		$('#msg_reader_block_flag6').html('<b>Replied Date:</b> '+tmp7);
		
		$('#reply_id').val(id);
		mrr_mark_read_message(id);		
	}
	function mrr_view_reader(id)
	{
		tmp1=$('#msg_view_send_date_'+id+'').html();
		tmp2=$('#msg_view_sender_'+id+'').html();
		tmp3=$('#msg_view_replied_'+id+'').html();
		tmp4=$('#msg_view_msg_'+id+'').html();
		
		$('#msg_reader_block_from').html('<b>From:</b> '+tmp2);
		$('#msg_reader_block_date').html('<b>Date:</b> '+tmp1);
		$('#msg_reader_block_msg').html('<b>Message:</b> '+tmp4);
		$('#msg_reader_block_flag1').html('<b>Replied:</b> '+tmp3);
		$('#msg_reader_block_flag2').html('<b>Sent from PeopleNet</b>');
		$('#msg_reader_block_flag3').html('');
		$('#msg_reader_block_flag4').html('');
		$('#msg_reader_block_flag5').html('');
		$('#msg_reader_block_flag6').html('');
	}
	
	function mrr_mark_read_message(msgid)
	{			
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_truck_tracking_messages_mark_read",
			   data: {"message_id": msgid,
			   		"user_id": mrr_user_id
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrr_fetch_sent_messages(0,0);
			   }
		});
	}
	function mrr_fetch_sent_dispatches(archived,limit)
	{		
		if(archived==0)
		{
			$('#sent_dispatches').html('Loading Sent Dispatches...');
		}
		else
		{
			$('#archived_sent_dispatches').html('Loading Sent Dispatches Archive...');
		}
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_truck_tracking_dispatches_sent",
			   data: {"truck_id": $('#truck_id').val(),
			   		"date_from": $('#date_from').val(),
			   		"date_to":  $('#date_to').val(),
			   		"truck_name": $('#truck_name').val(),
			   		"archived":archived,
			   		"limit": limit,
			   		"dsiplay_mode":0
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrrtab=$(xml).find("mrrTab").text();
					mrrUnread=$(xml).find("mrrUnread").text();
					
					if(archived==0)
					{
						$('#sent_dispatches').html(mrrtab);
					}
					else
					{
						$('#archived_sent_dispatches').html(mrrtab);
					}
					//$('#all_new_unread_messages').html(mrrUnread);
			   }
		});
	}
	function mrr_fetch_sent_messages(archived,limit)
	{		
		if(archived==0)
		{
			$('#sent_messages').html('Loading Sent Messages...');
		}
		else
		{
			$('#archived_sent_messages').html('Loading Sent Messages Archive...');
		}
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_truck_tracking_messages_sent",
			   data: {"truck_id": $('#truck_id').val(),
			   		"date_from": $('#date_from').val(),
			   		"date_to":  $('#date_to').val(),
			   		"truck_name": $('#truck_name').val(),
			   		"archived":archived,
			   		"limit": limit,
			   		"dsiplay_mode":0
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrrtab=$(xml).find("mrrTab").text();
					mrrUnread=$(xml).find("mrrUnread").text();
					
					if(archived==0)
					{
						$('#sent_messages').html(mrrtab);
					}
					else
					{
						$('#archived_sent_messages').html(mrrtab);
					}
					$('#all_new_unread_messages').html(mrrUnread);
			   }
		});
	}
	function mrr_fetch_received_messages(archived,limit)
	{		
		if(archived==0)
		{
			$('#received_messages').html('Loading Received Messages...');
		}
		else
		{
			$('#archived_received_messages').html('Loading Received Messages Archive...');
		}
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_truck_tracking_messages_received",
			   data: {"truck_id": $('#truck_id').val(),
			   		"date_from": $('#date_from').val(),
			   		"date_to":  $('#date_to').val(),
			   		"truck_name": $('#truck_name').val(),
			   		"archived":archived,
			   		"limit": limit
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrrtab=$(xml).find("mrrTab").text();
					
					if(archived==0)
					{
						$('#received_messages').html(mrrtab);
					}
					else
					{
						$('#archived_received_messages').html(mrrtab);
					}
			   }
		});
	}
	
	function mrr_load_archive_dispatched()
	{
		mrr_load_dispatched_archive=1;
		mrr_fetch_sent_dispatches(1,0);
	}
	
	function mrr_load_archive_sent()
	{
		mrr_load_sent_archive=1;
		mrr_fetch_sent_messages(1,0);
	}
	function mrr_load_archive_received()
	{
		mrr_load_received_archive=1;
		mrr_fetch_received_messages(1,0);
	}
	
	$('#truck_id').change( function()
	{
		document.peoplenet_messenger.submit();
	});
	
	function mrr_toggle_block(id)
	{
		$('#mrr_block_'+id+'').toggle();	
	}
	function mrr_toggle_show()
	{
		$('.mrr_block_details').show();
	}
	function mrr_toggle_hide()
	{
		$('.mrr_block_details').hide();
	}
	
</script>
<? include('footer.php') ?>