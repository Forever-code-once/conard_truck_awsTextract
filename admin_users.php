<? include('application.php') ?>
<? $admin_page = 1 ?>
<? $usetitle = "Admin Users" ?>
<?
	if(mrr_special_admin_users($_SESSION['user_id'])==0)
    {
         header("Location: /index.php");
         die();
    }
	if(isset($_GET['delid'])) 
	{
		$sql = "
			update users set
				deleted = 1			
			where id = '$_GET[delid]'
		";
		$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );	
		
		$mrr_activity_log_notes.="Deleted user $_GET[delid]. ";	
	}

	if(isset($_GET['new'])) 
	{
		$sql="update users set deleted=1 where username='New User'";
		mysqli_query($datasource, $sql);
		
		$sql = "
			insert into users			
				(username,
				password,
				active,
				linedate_added)
			values 
				('New User',
				'Pass',
				'0',
				NOW())
		";
		$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
		$new_id=mysqli_insert_id($datasource);
		header("Location: admin_users.php?eid=".$new_id."");
		die();
	}
	
	if(isset($_POST['username'])) {
		
		/* check to make sure the username has not been used before (if it is a new username that is) */
		$nogo = 0;
		if(strtolower($_POST['username_hold']) != strtolower($_POST['username'])) {
			$sql = "
				select id
				
				from users
				where username = '$_POST[username]'
			";
			$data_dup = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
			if(mysqli_num_rows($data_dup) == 1) {
				$nogo = 1;
			}
		}
		
		if($nogo == 0) {
			
			if(isset($_POST['active'])) {
				$useactive = 1;
			} else {
				$useactive = 0;
			}

			
			$sql = "
				update users set
					username = '$_POST[username]',
					password = '$_POST[password]',
					access = '$_POST[access]',
					active = '$useactive',
					name_first = '$_POST[name_first]',
					name_last = '$_POST[name_last]',
					email = '$_POST[email]',
					
					force_logout = '".(isset($_POST['force_logout']) ? 1 : 0)."',
					
					txt_msg_reply='".(isset($_POST['txt_msg_reply']) ? 1 : 0)."',
					txt_msg_reply_phone='".sql_friendly(mrr_clear_phone_number_extras($_POST['txt_msg_reply_phone']))."',
					
					alert_call_priority='".(int) $_POST['alert_call_priority']."',
					alert_call_phone='".sql_friendly(trim($_POST['alert_call_phone']))."',
					alert_call_email='".sql_friendly(trim($_POST['alert_call_email']))."',
					
					password_expires=NOW()

				where id = '$_GET[eid]'
			";
			$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
			
			$mrr_activity_log_notes.="Updated user $_GET[eid] info. ";	
		}
	}

	if(!isset($_POST['sbox'])) 
	{
		$_POST['sbox'] = "";
		$sql_extra = "";
	} 
	else 
	{
		$sql_extra = "
			and (username like '%$_POST[sbox]%'
				or name_first like '%$_POST[sbox]%'
				or name_last like '%$_POST[sbox]')
		";
	}

	$sql = "
		select *
		
		from users
		where deleted = 0
			$sql_extra
		order by username
	";
	$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
	
	$mrr_activity_log_notes.="Viewed list of users. ";	

$use_bootstrap = false;
$passwords_expire=45;
?>
<? include('header.php') ?>

<? if($use_bootstrap) { ?>


     <div class='container col-md-12'>
     	<div class='col-md-4'>
     		<div class="panel panel-info">
     			<div class="panel-heading">Users</div>
     			<div class="panel-body">
     					<form action="<?=$SCRIPT_NAME?>" method="post">			
     					<table class='table table-bordered well'>
     					<tr>				
     						<td><input name="sbox" class='form-control' value="<?=$_POST['sbox']?>"></td>
     						<td><button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-search"></span> Search</button></td>
     						<td><a href='admin_users.php?new=1'><button type='button' class='btn btn-success'><span class="glyphicon glyphicon-plus"></span> Add New User</button></a></td>
     					</tr>
     					</table>
     					</form>
     				<br><br>
     				<table class='table table-striped'>	
     				<thead>	
               		<tr>
               			<th><b>ID</b></th>
               			<th><b>Username</b></th>
               			<th><b>Name</b></th>
               			<th><b>Access Level</b></th>
               			<th>&nbsp;</th>
               		</tr>
               		</thead>
               		<tbody>
               		<? while($row = mysqli_fetch_array($data)) { ?>
               			<tr>
               				<td><?=$row['id']?></td>
               				<td><a href="<?=$SCRIPT_NAME?>?eid=<?=$row['id']?>"><?if(!$row['active']) echo "<strike>"?><?=$row['username']?></a></td>
               				<td><?=$row['name_first']?> <?=$row['name_last']?></td>
               				<td><?=$row['access']?></td>
               				<td>
               					<button onclick="confirm_del(<?=$row['id']?>)" class='mrr_delete_access btn btn-danger btn-sm'><span class='glyphicon glyphicon-trash'></span></button>
               				</td>
               			</tr>
               		<? } ?>
               		</tbody>
               		</table>
     				
     			</div>
     		</div>
     	</div>
     	<div class='col-md-5'>
     		
     		<? if(isset($_GET['eid'])) { ?>
     			<?     					
     					$sql = "
               				select *,
               					DATE_ADD(password_expires, INTERVAL ".(int) $passwords_expire." DAY) as pass_expired	
               				
               				from users
               				where id = '$_GET[eid]'
               			";
               			$data_user = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
               			$row_user = mysqli_fetch_array($data_user);
     			?>
          		<div class="panel panel-primary">
          			<div class="panel-heading">Edit user: <?=$row_user['username']?></div>
          			<div class="panel-body">
          				<?
               			
               			
               			$mrr_activity_log_notes.="View user $_GET[eid] info. ";     			
               			
               			$selbx=mrr_alert_call_priority_select_box('alert_call_priority',$row_user['alert_call_priority']);     			
               			?>			
               			<form action="<?=$SCRIPT_NAME?>?eid=<?=$_GET['eid']?>" method="post">
                    			<table class='table table-bordered well'>          				
                    				<tr>
                    					<td>Username:</td>
                    					<td>
                    						<input name="username" id='username' value="<?=$row_user['username']?>" onMouseOut='mrr_verify_user_unique(<?=$_GET['eid']?>);' class='form-control'>
                    						<input type="hidden" id="username_hold" name="username_hold" value="<?=$row_user['username']?>"> <span id='mrr_naming_message'></span>
                    					</td>
                    				</tr>
                    				<tr>
                    					<td>Password:</td>
                    					<td>
                    						<input name="password" value="<?=$row_user['password']?>" class='form-control'>  Last Set: <?=date("m/d/Y H:i:s", strtotime($row_user['password_expires']))?>.
                    						<br>Expires <?=$passwords_expire ?> Days after it was last set: <?=date("m/d/Y H:i:s", strtotime($row_user['pass_expired']))?>.
                    						<br><label><b>Force Logout:</b> <input type="checkbox" name="force_logout" <? if($row_user['force_logout']) echo "checked"?> value='1'></label>
                    					</td>
                    				</tr>
                    				<tr>
                    					<td>Access:</td>
                    					<td>
                    						<input type="text" name="access" value="<?=$row_user['access']?>" class='form-control'>
                    					</td>
                    				</tr>
                    				<tr>
                    					<td><label for='active'>Active:</label></td>
                    					<td><input type="checkbox" name="active" id='active' <? if($row_user['active']) echo "checked"?>></td>
                    				</tr>
                    				<tr>
                    					<td><label for='inventory_access'>Inventory System:</label></td>
                    					<td><input type="checkbox" name="inventory_access" id='inventory_access' <?if($row_user['inventory_access']) echo "checked"?>></td>
                    				</tr>          
                    				<tr>
                    					<td>First Name:</td>
                    					<td><input name="name_first" value="<?=$row_user['name_first']?>" class='form-control'></td>
                    				</tr>
                    				<tr>
                    					<td>Last Name:</td>
                    					<td><input name="name_last" value="<?=$row_user['name_last']?>" class='form-control'></td>
                    				</tr>
                    				<tr>
                    					<td>E-Mail:</td>
                    					<td><input name="email" value="<?=$row_user['email']?>" class='form-control'></td>
                    				</tr>
                    				<tr>
                    					<td>Alert Call Phone:</td>
                    					<td><input name="alert_call_phone" value="<?=$row_user['alert_call_phone']?>" class='form-control'></td>
                    				</tr>
                    				<tr>
                    					<td>Alert Call E-Mail:</td>
                    					<td><input name="alert_call_email" value="<?=$row_user['alert_call_email']?>" class='form-control'></td>
                    				</tr>
                    				<tr>
                    					<td>Alert Call Priority:</td>
                    					<td><?=$selbx  ?></td>
                    				</tr>  
                    				<tr>
                    					<td colspan='2' align='center'><b>Text Messaging Driver Notifications/Dispatch Reply</b></td>
                    				</tr> 
                    				<tr>
                    					<td><label for='txt_msg_reply'>Text Message Receive Replies:</label></td>
                    					<td><input type="checkbox" name="txt_msg_reply" <? if($row_user['txt_msg_reply']) echo "checked"?> value='1'></td>
                    				</tr>
                    				<tr>
                    					<td>Text Message Reply Cell Phone:</td>
                    					<td><input name="txt_msg_reply_phone" value="<?=$row_user['txt_msg_reply_phone']?>" class='form-control' maxlength='12' placeholder='16152132270'></td>
                    				</tr>  				
                    			</table>               			
                    			<p>
                    				<button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-floppy-disk"></span> Update</button>
                    				<div class='mrr_button_access_notice'>&nbsp;</div>
                    			</p>
                    			
                    			
                    			<? if($use_new_uploader > 0) { ?>
                         		
                         			<br>&nbsp;<br>
                              		<iframe src="mrr_uploader_hack.php?section_id=7&id=<?=$_GET['eid']?>" width='500' height='80' border='0' style='border:#000000 solid 0px; background-color:#ffffff;'>
                              		</iframe> 
                              		<div id='attachment_holder'></div>
                         		
                         		<? } else { ?>
                         		
                         			<div id='upload_section'></div>
                         		
                         		<? } ?>
     		
               			</form>			
               			
          			</div>
          		</div>
          		
     	<? } else { ?>
     		&nbsp;
     	<? } ?>
     		
     	</div>
     	
     	<div class='col-md-3'>
     	
     	<? if(isset($_GET['eid'])) { ?>
     			<div class="panel panel-primary">
          			<div class="panel-heading">Staff Absence</div>
          			<div class="panel-body">
          				<div id='driver_absenses'>    			
          					<div id='driver_absenses_list'></div>
          					<div class='clear'></div>
          				</div>
          			</div>
          		</div>
     	<? } else { ?>
     		&nbsp;
     	<? } ?>	
     	
     	</div>
     </div>


<? } else { ?>


     <table style='text-align:left' width='100%'>
     <tr>
     	<td valign='top' width='30%'>
     		
     		<form action="<?=$SCRIPT_NAME?>" method="post">			
     		<table class='admin_menu1' width='100%' style='margin:10px;'>
     		<tr>				
     			<td colspan='3'><h2>Users</h2></td>
     		</tr>
     		<tr>				
     			<td><input name="sbox" class='form-control' value="<?=$_POST['sbox']?>"></td>
     			<td><button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-search"></span> Search</button></td>
     			<td><a href='admin_users.php?new=1'><button type='button' class='btn btn-success'><span class="glyphicon glyphicon-plus"></span> Add New User</button></a></td>
     		</tr>
     		</table>
     		</form>
     			
     		<br><br>
     		<table class='admin_menu1' width='100%' style='margin:10px;'>	
     		<thead>	
     		<tr>
     			<th><b>ID</b></th>
     			<th><b>Username</b></th>
     			<th><b>Name</b></th>
     			<th><b>Access Level</b></th>
     			<th>&nbsp;</th>
     		</tr>
     		</thead>
     		<tbody>
     		<? while($row = mysqli_fetch_array($data)) { ?>
     			<tr>
     				<td><?=$row['id']?></td>
     				<td><a href="<?=$SCRIPT_NAME?>?eid=<?=$row['id']?>"><?if(!$row['active']) echo "<strike>"?><?=$row['username']?></a></td>
     				<td><?=$row['name_first']?> <?=$row['name_last']?></td>
     				<td><?=$row['access']?></td>
     				<td>
     					<a href="javascript:confirm_delete(<?=$row['id']?>)" class='mrr_delete_access'><img src="images/delete_sm.gif" border="0"></a>
     				</td>
     			</tr>
     		<? } ?>
     		</tbody>
     		</table>
                    		
     	</td>
     	<td valign='top'>
     		<? if(isset($_GET['eid'])) { ?>	
     			
     			<?
          		$sql = "
                    	select *,
                    		DATE_ADD(password_expires, INTERVAL ".(int) $passwords_expire." DAY) as pass_expired	
                       	from users
                    	where id = '$_GET[eid]'
                    ";
                    $data_user = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
                    $row_user = mysqli_fetch_array($data_user);
          		
     			$mrr_activity_log_notes.="View user $_GET[eid] info. ";     			
     			
     			$selbx=mrr_alert_call_priority_select_box('alert_call_priority',$row_user['alert_call_priority']);     			
     			?>			
     			<form action="<?=$SCRIPT_NAME?>?eid=<?=$_GET['eid']?>" method="post">
          			<table class='admin_menu2' width='100%' style='margin:10px;'> 
          				<tr>				
     						<td colspan='2'><h2>Edit user: <?=$row_user['username']?></h2></td>
     					</tr>         				
          				<tr>
          					<td>Username:</td>
          					<td>
          						<input name="username" id='username' value="<?=$row_user['username']?>" onMouseOut='mrr_verify_user_unique(<?=$_GET['eid']?>);' class='form-control'>
          						<input type="hidden" id="username_hold" name="username_hold" value="<?=$row_user['username']?>"> <span id='mrr_naming_message'></span>
          					</td>
          				</tr>
          				<tr>
          					<td>Password:</td>
          					<td>
          						<input name="password" value="<?=$row_user['password']?>" class='form-control'>  Last Set: <?=date("m/d/Y H:i:s", strtotime($row_user['password_expires']))?>.
                    				<br>Expires <?=$passwords_expire ?> Days after it was last set: <?=date("m/d/Y H:i:s", strtotime($row_user['pass_expired']))?>.
                    				<br><label><b>Force Logout:</b> <input type="checkbox" name="force_logout" <? if($row_user['force_logout']) echo "checked"?> value='1'></label>
          					</td>
          				</tr>
          				<tr>
          					<td>Access:</td>
          					<td>
          						<input type="text" name="access" value="<?=$row_user['access']?>" class='form-control'>
          					</td>
          				</tr>
          				<tr>
          					<td><label for='active'>Active:</label></td>
          					<td><input type="checkbox" name="active" id='active' <? if($row_user['active']) echo "checked"?>></td>
          				</tr>
          				<tr>
          					<td><label for='inventory_access'>Inventory System:</label></td>
          					<td><input type="checkbox" name="inventory_access" id='inventory_access' <?if($row_user['inventory_access']) echo "checked"?>></td>
          				</tr>          
          				<tr>
          					<td>First Name:</td>
          					<td><input name="name_first" value="<?=$row_user['name_first']?>" class='form-control'></td>
          				</tr>
          				<tr>
          					<td>Last Name:</td>
          					<td><input name="name_last" value="<?=$row_user['name_last']?>" class='form-control'></td>
          				</tr>
          				<tr>
          					<td>E-Mail:</td>
          					<td><input name="email" value="<?=$row_user['email']?>" class='form-control' style='width:300px;'></td>
          				</tr>
          				<tr>
          					<td>Alert Call Phone:</td>
          					<td><input name="alert_call_phone" value="<?=$row_user['alert_call_phone']?>" class='form-control'></td>
          				</tr>
          				<tr>
          					<td>Alert Call E-Mail:</td>
          					<td><input name="alert_call_email" value="<?=$row_user['alert_call_email']?>" class='form-control' style='width:300px;'></td>
          				</tr>
          				<tr>
          					<td>Alert Call Priority:</td>
          					<td><?=$selbx  ?></td>
          				</tr>  
          				<tr>
          					<td colspan='2' align='center'><b>Text Messaging Driver Notifications/Dispatch Reply</b></td>
          				</tr> 
          				<tr>
          					<td><label for='txt_msg_reply'>Text Message Receive Replies:</label></td>
          					<td><input type="checkbox" name="txt_msg_reply" <? if($row_user['txt_msg_reply']) echo "checked"?> value='1'></td>
          				</tr>
          				<tr>
          					<td>Text Message Reply Cell Phone:</td>
          					<td><input name="txt_msg_reply_phone" value="<?=$row_user['txt_msg_reply_phone']?>" class='form-control' maxlength='12' placeholder='16152132270'></td>
          				</tr> 
          				
          				<tr>
          					<td align='center' colspan='2'>          						
          						<input type='submit' value='Update'>
          						<div class='mrr_button_access_notice'>&nbsp;</div>
          					</td>
          				</tr>  
          				<tr>
          					<td align='center' colspan='2'>
          						<? if($use_new_uploader > 0) { ?>
                         		
                         				<br>&nbsp;<br>
                              			<iframe src="mrr_uploader_hack.php?section_id=7&id=<?=$_GET['eid']?>" width='500' height='80' border='0' style='border:#000000 solid 0px; background-color:#ffffff;'>
                              			</iframe> 
                              			<div id='attachment_holder'></div>
                         		
                         			<? } else { ?>
                         		
                         				<div id='upload_section'></div>
                         		
                         			<? } ?>          						
          					</td>
          				</tr>   				
          			</table>  
                 
                    <br>
                    <div id='internal_tasks' style='margin-bottom:10px;'>
                        <table class='admin_menu1' style='width:100%;margin-bottom:10px'>
                            <tr>
                                <td colspan='4' class='border_bottom'><div class='section_heading'>Internal Tasks</div></td>
                            </tr>
                            <tr>
                                <td valign='top'><b>Task</b></td>
                                
                                <td valign='top'><b>Due Date</b></td>
                                <td valign='top'><b>Checked by</b></td>
                                <td valign='top'><b>Completed</b></td>
                            </tr>
                             <?php
                             //<td valign='top'><b>User</b></td>
                             $task_cntr=0;
                             $sql_tasks = "
                                    select internal_tasks_checked.*,
                                        internal_tasks.task_name,
                                        internal_tasks.task_type,
                                        (select name_truck from trucks where trucks.id=internal_tasks_checked.entity_id) as truck_name,
                                        (select trailer_name from trailers where trailers.id=internal_tasks_checked.entity_id) as trailer_name,
                                        (select CONCAT(name_driver_first,' ',name_driver_last) from drivers where drivers.id=internal_tasks_checked.entity_id) as driver_name,
                                        (select customers.name_company from customers where customers.id=internal_tasks_checked.entity_id) as cust_name,
                                        (select CONCAT(us.name_first,' ',us.name_last) from users us where us.id=internal_tasks_checked.entity_id) as user_name,
                                        users.username	
                                    from internal_tasks_checked
                                        left join internal_tasks on internal_tasks.id=internal_tasks_checked.task_id
                                        left join users on users.id=internal_tasks_checked.user_id
                                    where internal_tasks.deleted <= 0 and internal_tasks_checked.deleted<=0
                                        and internal_tasks.active > 0
                                        and internal_tasks.task_type = 5
                                        and internal_tasks_checked.entity_id='".(int) $_GET['eid']."'
                                        and internal_tasks.linedate_start <= NOW()
                                    order by internal_tasks.task_name asc,internal_tasks_checked.id asc
                                 ";
                             $data_tasks = mysqli_query($datasource, $sql_tasks);
                             while($row_tasks = mysqli_fetch_array($data_tasks))
                             {
                                  $cur_dater="<input name='cur_date_task' id='cur_date_task' value='".date("m/d/Y",strtotime($row_tasks['cur_date']))."' onBlur='mrr_set_task_date(".$row_tasks['id'].");' size='15' class='datepicker' placeholder='mm/dd/YYYY'>";
                                  $done_by="";
                                  $done_on="";
                                  if($row_tasks['user_id'] > 0)
                                  {
                                       $done_by=trim($row_tasks['username']);
                                       $done_on="".date("m/d/Y",strtotime($row_tasks['done_date']))."";
                                       $cur_dater="".date("m/d/Y",strtotime($row_tasks['cur_date']))."";
                                  }
                                  $entity_name="<b>N/A</b>";
                                  if($row_tasks['task_type']==1)    $entity_name="<i>".trim($row_tasks['truck_name'])."</i>";
                                  //if($row_tasks['task_type']==2)  $entity_name="<a href='admin_trailers.php?id=".$row_tasks['entity_id']."' target='blank'>".trim($row_tasks['trailer_name'])."</a>";
                                  //if($row_tasks['task_type']==3)  $entity_name="<a href='admin_drivers.php?id=".$row_tasks['entity_id']."' target='blank'>".trim($row_tasks['driver_name'])."</a>";
                                  if($row_tasks['task_type']==5)    $entity_name="<i>".trim($row_tasks['user_name'])."</i>";
                                  
                                  echo "
                                            <tr style='background-color:".($task_cntr%2==0 ? "eeeeee" : "dddddd").";'>
                                                <td valign='top'>".$row_tasks['task_name']."</td>
                                                
                                                <td valign='top'>".$cur_dater."</td>                                        
                                                <td valign='top'><i>".$done_by."</i></td>
                                                <td valign='top'><i>".$done_on."</i></td>                                     
                                            </tr>
                                     ";     //              mrr_set_internal_task   <td valign='top'>".$entity_name."</td>
                                  $task_cntr++;
                             }
                             ?>
                        </table>
                    </div>
                </form>
     			
     		<? } else { ?>
          		&nbsp;
          	<? } ?>
     	</td>
     	<td valign='top' width='30%'>
     		<? if(isset($_GET['eid'])) { ?>	
          		
     			<div id='driver_absenses' style='margin:10px;'>    			
               		<div id='driver_absenses_list'></div>
               		<div class='clear'></div>
               	</div>		
               	
          	<? } else { ?>
          		&nbsp;
          	<? } ?>	
     	</td>
     </tr>
     </table>

<? } ?>
<?php
$cal_mon=date("m",time());
$cal_day=date("d",time());
$cal_yr=date("Y",time());
if(isset($_GET['cal_mon']))         $cal_mon=(int) $_GET['cal_mon'];
if(isset($_GET['cal_day']))         $cal_day=(int) $_GET['cal_day'];
if(isset($_GET['cal_year']))        $cal_yr=(int) $_GET['cal_year'];
?>
<? include('footer.php') ?>
<script type='text/javascript'>
	<? if($use_admin_level < 95) { ?>
		$('.mrr_button_access').hide();
		$('.mrr_delete_access').hide();
		$('.mrr_button_access_notice').html('View Only.<br>Consult Supervisor.');
		$( ":input" ).attr('disabled','disabled');
	<? } else { ?>
		$( ":input" ).attr('disabled','');
		$('.mrr_button_access').show();
		$('.mrr_delete_access').show();
		$('.mrr_button_access_notice').html('&nbsp;');
	<? } ?>
	
	function confirm_del(id) {
		if(confirm("Are you sure you want to delete this user?")) {
			window.location = "<?=$SCRIPT_NAME?>?delid=" + id;
		}
	}
	function confirm_del_addr(id) {
		if(confirm("Are you sure you want to delete this address?")) {
			window.location = "<?=$SCRIPT_NAME?>?deladdr=" + id;
		}
	}

    function mrr_set_task_date(id)
    {
        var task_date=$('#cur_date_task').val();

        task_date=task_date+"";

        $.ajax({
            type: "POST",
            url: "ajax.php?cmd=mrr_set_internal_task",
            data: {"id":id , "date": task_date },
            dataType: "xml",
            cache:false,
            success: function(xml) {
                $.noticeAdd({text: "Internal Task date ("+task_date+") has been updated successfully."});
            }
        });
    }
	
	function confirm_delete_absense(user,id) 
	{
		if(confirm("Are you sure you want to delete this user absence record?")) {
			
			$.ajax({
     			url: "ajax.php?cmd=mrr_remove_user_absense_records",
     			type: "post",
     			dataType: "xml",
     			data: {				
     				"id":id
     			},
     			error: function() {
     				$.prompt("Error: Cannot update User Absence Record");
     			},
     			success: function(xml) {		
     				
     				mrr_list_user_absense(user);
     			}
     		});	
		}
	}
	function mrr_list_user_absense(id)
	{
		$('#driver_absenses_list').html('loading...');
		
		$.ajax({
			url: "ajax.php?cmd=mrr_list_user_absense_records",
			type: "post",
			dataType: "xml",
			data: {				
				"cal_mon": "<?=$cal_mon ?>" ,
                "cal_day": "<?=$cal_day ?>" ,
                "cal_year": "<?=$cal_yr ?>" ,			    
			    "user_id":id                
			},
			error: function() {
				$.prompt("Error: Cannot update User Absence Record");
			},
			success: function(xml) {		
				
				mrr_tab=$(xml).find('mrrTab').text();
				$('#driver_absenses_list').html(mrr_tab);	
			}
		});	
	}
	function mrr_add_to_user_absense(id)
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_add_user_absense_record",
			type: "post",
			dataType: "xml",
			data: {				
				"user_id":id,
				"date": $('#driver_absense_date').val(),
                "date_to": $('#driver_absense_date_to').val(),
				"code":$('#driver_absense_code').val(),
				"note": $('#driver_absense_note').val(),
                "duration": $('#driver_absense_duration').val()
			},
			error: function() {
				$.prompt("Error: Cannot update User Absence Record");
			},
			success: function(xml) {		
				mrr_list_user_absense(id);		
			}
		});		
	}
	
	function mrr_verify_user_unique(myid)
	{		
		$('#mrr_naming_message').html('');	
		mrr_lab="Username";
		mrr_lab2="username";
		mrr_code=0;
		
		new_name=$('#'+mrr_lab2+'').val();
		held=$('#'+mrr_lab2+'_hold').val();
		
		$.ajax({
			url: "ajax.php?cmd=mrr_verify_item_name",
			type: "post",
			dataType: "xml",
			data: {
				"name": new_name,
				"mode": mrr_code,
				"id": myid
			},
			error: function() {
				$.prompt("Error: Cannot check for duplication of "+mrr_lab+" "+new_name+".");
				$('#'+mrr_lab2+'').val(held);
				$('#mrr_naming_message').html(''+mrr_lab+' reset, must verify uniqueness.');	
				$('#mrr_naming_message').css('color','red');	
			},
			success: function(xml) {				
				mytxt=$(xml).find('mrrTab').text();
				if(mytxt=="")
				{					
					$('#mrr_naming_message').html(''+mrr_lab+' is valid.');	
					$('#mrr_naming_message').css('color','blue');		
				}
				else
				{
					$.prompt( ""+ mytxt +"" );
					$('#'+mrr_lab2+'').val(held);
					$('#mrr_naming_message').html(''+mrr_lab+' must be unique.');	
					$('#mrr_naming_message').css('color','red');									
				}				
			}
		});	
		
	}
	<? 
	if(isset($_GET['eid']) && $_GET['eid'] > 0)
	{
		//echo " create_upload_section('#upload_section', 4, $_GET[eid]); "; 
		 
		if($use_new_uploader > 0) 
		{ 
			echo " create_upload_section_alt('#upload_section', 7, $_GET[eid]); "; 
		}
		else
		{
			echo " create_upload_section('#upload_section', 7, $_GET[eid]); "; 
		}
			
		echo " mrr_list_user_absense( $_GET[eid] );";
	}
	?>
</script>