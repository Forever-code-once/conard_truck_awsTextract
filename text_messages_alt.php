<? include('application.php') ?>
<? $admin_page = 1; ?>
<? $use_bootstrap = true; ?>
<? $usetitle = "Driver Text Messages"; ?>
<? include('header.php') ?>
<?
	$rep_val="Ready:  Please enter any (new) phone numbers and a message to send.";
	if(!isset($_POST['txt_msg_id']))		$_POST['txt_msg_id']=0;
	if(!isset($_POST['txt_msg_numbers']))	$_POST['txt_msg_numbers']="";
	if(!isset($_POST['txt_msg_body']))		$_POST['txt_msg_body']="";
	
	if($_POST['txt_msg_id']==0 && trim($_POST['txt_msg_numbers'])!="" && trim($_POST['txt_msg_body'])!="" && isset($_POST['txt_msg_send']) && $_POST['txt_msg_send'] > 0)
	{		
		$sent_flag=0;
		
		$numbers=trim(strip_tags($_POST['txt_msg_numbers']));
		$body=trim(strip_tags($_POST['txt_msg_body']));
		
		$numbers=mrr_clear_phone_number_extras($numbers);	
		
		//Bracken knox, Ricky Memphis w pontotoc, Holmes chatt, Mcathern chatt, Monick Memphis w tupelo, Swafford memphis w tupelo, Maurice memphis w collierville 16456, Milo memphis w collierville 87031, Barlow memphis w collierville 781754. 
		
		$res['result']=0;
     	$res['report']="";
     	$res['length']=0;
     			
		//if($numbers!="6159699115")	
			$res=send_twilio_messages($numbers, $body);	
		
		$sent_flag=($res['result'] > 0 ? 1 : 0);
		
		$sql = "
			insert into ".mrr_find_log_database_name()."txt_msg_log
				(id,
				linedate_added,				
				user_id,
				archive,
				sent_flag,
				txt_msg_numbers,
				txt_msg_body,
				deleted,
				txt_mode)
			values
				(NULL,
				NOW(),
				'".sql_friendly($_SESSION['user_id'])."',
				0,
				'".sql_friendly($sent_flag)."',
				'".sql_friendly($numbers)."',
				'".sql_friendly($body)."',
				0,
				2)
		";
		$data = simple_query($sql);	
		$_POST['txt_msg_id']= mysqli_insert_id($datasource);
				
		if($sent_flag > 0)	
		{
			$rep_val="<span style='color:#00CC00;'><b>Message has been sent.</b></span>"; 
		}
		else	
		{
			$rep_val="<span style='color:#CC0000;'><b>ERROR: Could not send message.</b></span><br>Result = ".$res['result'].", Message Length=".$res['length'].".<br><b>Report:</b><br>".$res['report']."";
		}
		//header("Location: text_messages.php?txt_msg_id=".$_POST['txt_msg_id']."");
		//die;			
	}	
	elseif(trim($_POST['txt_msg_numbers'])!="" && isset($_POST['txt_msg_send']) && $_POST['txt_msg_send'] < 0)
	{
		$sent_flag=-1;
		
		$numbers=trim(strip_tags($_POST['txt_msg_numbers']));
		$body="Updated Phone Numbers";
		
		$numbers=mrr_clear_phone_number_extras($numbers);
		
		$sql = "
			insert into ".mrr_find_log_database_name()."txt_msg_log
				(id,
				linedate_added,				
				user_id,
				archive,
				sent_flag,
				txt_msg_numbers,
				txt_msg_body,
				deleted,
				txt_mode)
			values
				(NULL,
				NOW(),
				'".sql_friendly($_SESSION['user_id'])."',
				0,
				'".sql_friendly($sent_flag)."',
				'".sql_friendly($numbers)."',
				'".sql_friendly($body)."',
				0,
				2)
		";
		$data = simple_query($sql);	
		$_POST['txt_msg_id']= mysqli_insert_id($datasource);
		
		if($_POST['txt_msg_id'] > 0)		$rep_val="<span style='color:#00CC00;'><b>Phone Numbers have been Updated. Nothing sent out</b></span>"; 
	}
	
	//$_POST['txt_msg_id']=0;
	//$_POST['txt_msg_numbers']="";
	//$_POST['txt_msg_body']="";
		
	if(isset($_GET['txt_msg_id']))		$_POST['txt_msg_id']=$_GET['txt_msg_id'];
	
	if($_POST['txt_msg_id'] >= 0)
	{
		$sql = "
			select txt_msg_log.*,
				(select username from users where txt_msg_log.user_id=users.id) as user_name
						
			from ".mrr_find_log_database_name()."txt_msg_log
			where txt_msg_log.deleted = 0
				and txt_msg_log.txt_mode=2
				".($_POST['txt_msg_id'] > 0 ? " and txt_msg_log.id='".sql_friendly($_POST['txt_msg_id'])."'" : "")."
			order by txt_msg_log.linedate_added desc, 
				txt_msg_log.id desc
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data)) 
		{
			$_POST['txt_msg_id']=$row['id'];
			$_POST['txt_msg_numbers']=trim($row['txt_msg_numbers']);
			$_POST['txt_msg_body']="";	//trim(strip_tags($row['txt_msg_body']));
		}		
	}
	
	$driver_listing="".mrr_clear_phone_number_extras($_POST['txt_msg_numbers'])."";
	
	$sql="
		select id, name_driver_first,name_driver_last,phone_cell
		from drivers
		where active>0 
			and deleted=0
			
			and id!=405 and id!=345
		order by name_driver_last asc, name_driver_first asc
	";	//and LTRIM(RTRIM(phone_cell))!=''
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{	
		$namer=" ".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."";		//<a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."</a>
		$phone_number=mrr_clear_phone_number_extras($row['phone_cell']);
		
		$driver_listing=str_replace($phone_number,$namer,$driver_listing);	       
	}
	
	$sql="
		select id, name_first,name_last,txt_msg_reply_phone
		from users
		where active>0 
			and deleted=0
			and txt_msg_reply > 0
			and txt_msg_reply_phone!=''
		order by name_last asc, name_first asc
	";	//and LTRIM(RTRIM(phone_cell))!=''
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{	
		$namer=" <span style='color:#00CC00;'><b>".trim($row['name_first'])." ".trim($row['name_last'])."</b></span>";		//<a href='admin_users.php?eid=".$row['id']."' target='_blank'>".trim($row['name_first'])." ".trim($row['name_last'])."</a>
		$phone_number=mrr_clear_phone_number_extras($row['txt_msg_reply_phone']);
		
		$driver_listing=str_replace($phone_number,$namer,$driver_listing);	       
	}
?>
<form action="<?=$SCRIPT_NAME?>" name='my_form' method="post">
	<input type='hidden' name="txt_msg_id" id='txt_msg_id' value="0">
	<input type='hidden' name="txt_msg_send" id='txt_msg_send' value="1">
<div class='container col-md-12'>
	<div class='col-md-6'>
		<div class="panel panel-primary">
			<div class="panel-heading"><?=$usetitle ?></div>
			<div class="panel-body">
				<table class='table table-bordered well'>         
				<tr>
               		<td valign='top' colspan='2' align='center'><?=$rep_val ?></td>
               	</tr>      	
               	<tr>
               		<td valign='top'>
               			<b>Phone Number(s):</b><br>Comma Separated
               		</td>
               		<td valign='top'>
               			<textarea name="txt_msg_numbers" id="txt_msg_numbers" wrap='virtual' rows='5' cols='100'><?=$_POST['txt_msg_numbers'] ?></textarea>
               		</td>
               	</tr>
               	<tr>
               		<td valign='top'>
               			<b>Driver Summary:</b>
               		</td>
               		<td valign='top'>
               			<?= $driver_listing ?>
               		</td>
               	</tr>
               	<tr>
               		<td valign='top'>
               			<b>Message Text:</b>
               		</td>
               		<td valign='top'>
               			<textarea name="txt_msg_body" id="txt_msg_body" wrap='virtual' rows='5' cols='100'><?=$_POST['txt_msg_body']?></textarea>
               			<br><b>Note:</b> If the message is longer than 140 characters, message will be sent in multiple pieces to each driver.
               		</td>
               	</tr>	
               	<tr>
               		<td valign='top' colspan='2' align='center'>
               			<button type='button' class='btn btn-primary' onclick='mrr_test_submit();'><span class="glyphicon glyphicon-floppy-disk"></span> Update Numbers Only</button>
               			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
               			<button type='button' class='btn btn-primary' onclick='mrr_submit();'><span class="glyphicon glyphicon-envelope"></span> Send Text Message(s)</button>
               		</td>
               	</tr>
               	</table>
               	<p>
               		
               	</p>
			</div>
		</div>
		
		<div class="panel panel-info">
			<div class="panel-heading">Active Driver Phone Numbers</div>
			<div class="panel-body">
				<table class='table table-bordered well'>  
               	<tr>
               		<td valign='top'><b>Cell Phone</b></td>
               		<td valign='top'><b>Driver</b></td>
               		<td valign='top'><b>Cell Phone</b></td>
               		<td valign='top'><b>Driver</b></td>
               	</tr>
               	<?
               	$all_drivers="";
               	$dcntr=0;
               	$sql="
					select id, name_driver_first,name_driver_last,phone_cell,driver_no_text_msg
					from drivers
					where active>0 
						and deleted=0
						
						and id!=405 and id!=345
					order by name_driver_last asc, name_driver_first asc
				";	//and LTRIM(RTRIM(phone_cell))!=''
				$data = simple_query($sql);
          		while($row = mysqli_fetch_array($data)) 
          		{
          			if($dcntr%2==0 && $dcntr > 0)
          			{
          				echo "
          				</tr>
          				<tr>
          				";	
          			}
          			
          			if($row['driver_no_text_msg'] > 0)
          			{
          				echo "
          					<td valign='top'><span style='color:red;'><b>".trim($row['phone_cell'])."</b> (No Txt Msg)</span></td>
          					<td valign='top'><a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."</a></td>          				
          				";
          			}
          			else
          			{           			
          				echo "
          					<td valign='top'><span onClick='mrr_add_number(\"".trim($row['phone_cell'])."\");' style='cursor:pointer; color:orange;'><b>".trim($row['phone_cell'])."</b></span></td>
          					<td valign='top'><a href='admin_drivers.php?id=".$row['id']."' target='_blank'>".trim($row['name_driver_first'])." ".trim($row['name_driver_last'])."</a></td>          				
          				";
          				
          				if(trim($row['phone_cell'])!="")		$all_drivers.="".($dcntr > 0 ? "," : "" )." ".trim($row['phone_cell'])."";
          			}          			
          			$dcntr++;	
          		}
               	?>
               	<tr>
               		<td valign='top'>
               			<span onClick='mrr_add_all_drivers();' style='cursor:pointer; color:orange;'><b>All Drivers</b></span>
               			<br>
               			<span onClick='mrr_clear_form_numbers();' style='cursor:pointer; color:purple;'><b>Clear All</b></span>
               		</td>
               		<td valign='top' colspan='3'><div id='all_driver_pool'><?=trim($all_drivers) ?></div></td>
               	</tr>
               	</table>
               	<p>
               		
               	</p>
			</div>
		</div>
		
		
		<div class="panel panel-info">
			<div class="panel-heading">Active Dispatch Reply Phone Numbers</div>
			<div class="panel-body">
				<table class='table table-bordered well'>  
               	<tr>
               		<td valign='top'><b>Cell Phone</b></td>
               		<td valign='top'><b>User</b></td>
               		<td valign='top'><b>Cell Phone</b></td>
               		<td valign='top'><b>User</b></td>
               	</tr>
               	<?
               	
               	$ucntr=0;
               	$sql="
					select id, name_first,name_last,txt_msg_reply_phone
					from users
					where active>0 
						and deleted=0
						and txt_msg_reply > 0
						and txt_msg_reply_phone!=''
					order by name_last asc, name_first asc
				";	//and LTRIM(RTRIM(txt_msg_reply_phone))!=''
				$data = simple_query($sql);
          		while($row = mysqli_fetch_array($data)) 
          		{
          			if($ucntr%2==0 && $ucntr > 0)
          			{
          				echo "
          				</tr>
          				<tr>
          				";	
          			}      
          			   			
          			echo "
          				<td valign='top'><span onClick='mrr_add_number(\"".trim($row['txt_msg_reply_phone'])."\");' style='cursor:pointer; color:orange;'><b>".trim($row['txt_msg_reply_phone'])."</b></span></td>
          				<td valign='top'><a href='admin_users.php?eid=".$row['id']."' target='_blank'>".trim($row['name_first'])." ".trim($row['name_last'])."</a></td>          				
          			";          			
          			$ucntr++;	
          		}
               	?>
               	
               	</table>
               	<p>
               		
               	</p>
			</div>
		</div>
	</div>
	<div class='col-md-6'>
		<div class="panel panel-info">
			<div class="panel-heading">Message History</div>
			<div class="panel-body" style='max-height:560px; height:560px; overflow:auto;'>
				<table class='table table-bordered well'> 
				<tr>
               		<td valign='top'><b>ID</b></td>
               		<td valign='top'><b>Created</b></td>
               		<td valign='top'><b>Sent by</b></td>
               		<td valign='top'><b>Cell Phone Number(s)</b></td>
               		<td valign='top'><b>Message</b></td>
               		<td valign='top'><b>Sent</b></td>
               	</tr>
				<?
				$limit_msg=(int) $defaultsarray['phone_text_msg_history_cnt'];
				if($limit_msg==0)		$limit_msg=25;
				
				
				$cntr=0;
				$sql = "
          			select txt_msg_log.*,
          				(select username from users where txt_msg_log.user_id=users.id) as user_name          						
          			from ".mrr_find_log_database_name()."txt_msg_log
          			where txt_msg_log.deleted = 0
          				and txt_msg_log.txt_mode=2          				
          			order by txt_msg_log.linedate_added desc, 
          				txt_msg_log.id desc
          			limit ".$limit_msg."
          		";
          		$data = simple_query($sql);
          		while($row = mysqli_fetch_array($data)) 
          		{
          			$sent_mode="<span style='color:#CC0000;'>No</span>";
          			if($row['sent_flag'] > 0)	$sent_mode="<span style='color:#00CC00;'>Yes</span>";	
          			if($row['sent_flag'] < 0)	$sent_mode="<span style='color:orange;'>Test</span>";	
          			
          			echo "
          				<tr style='background-color:#".($cntr % 2 == 0 ? "eeeeee" : "dddddd").";'>
          					<td valign='top'><a href='text_messages.php?txt_msg_id=".$row['id']."'>".$row['id']."</a></td>
          					<td valign='top'>".date("m/d/Y",strtotime($row['linedate_added']))."</td>
          					<td valign='top'>".$row['user_name']."</td>
          					<td valign='top'>".trim($row['txt_msg_numbers'])."</td>
          					<td valign='top'>".trim(strip_tags($row['txt_msg_body']))."</td>
          					<td valign='top'>".$sent_mode."</td>
          				</tr>
          			";
          			$cntr++;
          		}
				?>
				<tr>
          			<td valign='top' colspan='6'>Last <b><?=$cntr ?></b> Messages</td>
          		</tr>
				</table>
			</div>
		</div>	
		
		
		
		<div class="panel panel-warning">
			<div class="panel-heading">Reply Message History <a name='reply_messages'>&nbsp;</a></div>
			<div class="panel-body" style='max-height:1020px; height:1020px; overflow:auto;'>
				<table class='table table-bordered well'> 
				<tr>
               		<td valign='top'><b>ID</b></td>
               		<td valign='top'><b>Created</b></td>
               		<td valign='top'><b>Cell Phone</b></td>
               		<td valign='top'><b>Sent by</b></td>               		
               		<td valign='top'><b>Sent to</b></td>               		
               		<td valign='top'><b>Message</b></td>
               	</tr>
				<?
				$cntr=0;
				$sql = "
          			select txt_msg_reply_log.*,
          				(select CONCAT(name_driver_first,' ',name_driver_last) from drivers where txt_msg_reply_log.driver_id=drivers.id) as driver_name,
          				(select username from users where txt_msg_reply_log.user_id=users.id) as user_name         						
          			from ".mrr_find_log_database_name()."txt_msg_reply_log
          			where txt_msg_reply_log.deleted = 0  
          				and txt_msg_reply_log.txt_mode>=1  				
          			order by txt_msg_reply_log.linedate_added desc, 
          				txt_msg_reply_log.id desc
          			limit ".$limit_msg."
          		";
          		$data = simple_query($sql);
          		while($row = mysqli_fetch_array($data)) 
          		{
          			//$sent_mode="<span style='color:#CC0000;'>No</span>";
          			//if($row['sent_flag'] > 0)	$sent_mode="<span style='color:#00CC00;'>Yes</span>";	
          			//if($row['sent_flag'] < 0)	$sent_mode="<span style='color:orange;'>Test</span>";	
          			          			
          			$sent_by="";          			
          			if($row['user_id'] > 0)		$sent_by=trim($row['user_name']);
          			if($row['driver_id'] > 0)	$sent_by=trim($row['driver_name']);
          			          			
          			echo "
          				<tr style='background-color:#".($cntr % 2 == 0 ? "eeeeee" : "dddddd").";'>
          					<td valign='top'>".$row['id']."</td>
          					<td valign='top' nowrap>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>   
          					<td valign='top'>".trim($row['from_phone'])."</td>      					
          					<td valign='top'>".$sent_by."</td>          					
          					<td valign='top'>".trim($row['to_phone'])."</td>
          					<td valign='top'>".trim(strip_tags($row['message_body']))."</td>
          				</tr>
          			";	//<a href='text_messages.php?txt_msg_id=".$row['id']."'>".$row['id']."</a>
          				//<td valign='top'>".$sent_mode."</td>
          			$cntr++;
          		}
				?>
				<tr>
          			<td valign='top' colspan='6'>Last <b><?=$cntr ?></b> Reply Messages</td>
          		</tr>
				</table>
			</div>
		</div>
	</div>
</div>
</form>
<script type='text/javascript'>
	function mrr_submit()
	{		
		document.my_form.submit();
	}	
	
	function mrr_test_submit()
	{	//only saves the numbers in this mode...
		$('#txt_msg_send').val('-1');		
		document.my_form.submit();
	}
	function mrr_add_number(numb)
	{
		var pnumb = "" + numb + "";
		
		var pval=$('#txt_msg_numbers').val();	
		
		//alert('Adding "'+pnumb+'" to "'+pval+'".');
		 
		if(pval=="")	
		{
			pval = pval + "" + pnumb;
		}
		else
		{
			pval = pval + ", " + pnumb;	
		}
		$('#txt_msg_numbers').val(''+pval+'');
	}
	
	function mrr_add_all_drivers()
	{
		pval=$('#all_driver_pool').html();
		$('#txt_msg_numbers').val(''+pval+'');	
	}
	function mrr_clear_form_numbers()
	{
		$('#txt_msg_numbers').val('');	
	}
</script>
<? include('footer.php') ?>