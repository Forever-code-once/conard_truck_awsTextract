<? include('application.php') ?>
<? $admin_page = 1 ?>
<?		
	if(isset($_GET['did'])) {
		$sql = "
			update driver_elog_events set			
				deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_delete = simple_query($sql);
	}
	
	//if(isset($_GET['id']))		$_POST['id']=$_GET['id'];	
	//if(!isset($_POST['id']))	$_POST['id']=0;	
	
	//if(!isset($_POST['driver_id']))		$_POST['driver_id']=0;
	//if(!isset($_POST['code_id']))			$_POST['code_id']=0;
	
	if(isset($_POST['updater'])) 
	{				
		//new item
		if(!isset($_POST['event_type_new']))		$_POST['event_type_new']="";
		if(!isset($_POST['event_act_new']))		$_POST['event_act_new']=0;
		
		if(trim($_POST['event_type_new'])!="")
		{
			
			$e_label1=$_POST['event_data_1_new'];
			$e_label2=$_POST['event_data_2_new'];
			$e_label3=$_POST['event_data_3_new'];
			$e_label4=$_POST['event_data_4_new'];
			
			$s_label1=$_POST['setting_1_new'];
			$s_label2=$_POST['setting_2_new'];
			$s_label3=$_POST['setting_3_new'];
			$s_label4=$_POST['setting_4_new'];
			
			$sqlu = "
				insert into driver_elog_events
					(id,
					elog_event,
					active,
					deleted,
					linedate_added,
					event_data1,
                    	event_data2,
                    	event_data3,
                    	event_data4,
                    	setting1,
                    	setting2,
                    	setting3,
                    	setting4)
				values
					(NULL,
					'".sql_friendly($_POST['event_type_new'])."',
					'".sql_friendly((int)$_POST['event_act_new'])."',
					0,			
					NOW(),
					'".sql_friendly($e_label1)."',
                    	'".sql_friendly($e_label2)."',
                    	'".sql_friendly($e_label3)."',
                    	'".sql_friendly($e_label4)."',
                    	'".sql_friendly($s_label1)."',
                    	'".sql_friendly($s_label2)."',
                    	'".sql_friendly($s_label3)."',
                    	'".sql_friendly($s_label4)."')
				";
			simple_query($sqlu);
		}	
				
		//updating older items...
		if(!isset($_POST['elog_events_tot']))		$_POST['elog_events_tot']=0;
		
		for($x=0;$x < $_POST['elog_events_tot']; $x++)
		{
			$myid=$_POST['event_id_'.$x.''];
			$event=$_POST['event_type_'.$x.''];
			
			$e_label1=$_POST['event_data_1_'.$x.''];
			$e_label2=$_POST['event_data_2_'.$x.''];
			$e_label3=$_POST['event_data_3_'.$x.''];
			$e_label4=$_POST['event_data_4_'.$x.''];
			
			$s_label1=$_POST['setting_1_'.$x.''];
			$s_label2=$_POST['setting_2_'.$x.''];
			$s_label3=$_POST['setting_3_'.$x.''];
			$s_label4=$_POST['setting_4_'.$x.''];
			
			$act=0;
			if(isset($_POST['event_act_'.$x.'']))		$act=1;
			
			$sql = "
     			update driver_elog_events set
     				elog_event='".sql_friendly($event)."',
     				active='".sql_friendly($act)."',
     				event_data1='".sql_friendly($e_label1)."',
                    	event_data2='".sql_friendly($e_label2)."',
                    	event_data3='".sql_friendly($e_label3)."',
                    	event_data4='".sql_friendly($e_label4)."',
                    	setting1='".sql_friendly($s_label1)."',
                    	setting2='".sql_friendly($s_label2)."',
                    	setting3='".sql_friendly($s_label3)."',
                    	setting4='".sql_friendly($s_label4)."'
     			where id='".sql_friendly($myid)."'
     		";
     		simple_query($sql);
		}
		//header("Location: $SCRIPT_NAME?id=".$new_id);
		//die();
	}

	$sql = "
		select *
		
		from driver_elog_events
		where deleted = 0
		order by active desc,id asc
	";
	$data = simple_query($sql);	
	
	$usetitle="Driver Safety Elog Controls";
	/*
	?id=<?=$_GET['id']?>
	*/
?>
<? include('header.php') ?>
<div style='margin-left:25px;'>
<form action="<?=$SCRIPT_NAME ?>" method="post">

<div class='standard18'><b><?=$usetitle?></b></div><br>

<table class='admin_menu1' style='text-align:left; width:1500px;'>
<tr>
	<td valign='top'><b>ID</b></td>
	<td valign='top'><b>Elog Event</b></td>
	<td valign='top'><b>Added</b></td>
	<td valign='top'><b>Active</b></td>
	
	<td valign='top'><b>Event Data 1</b></td>
	<td valign='top'><b>Event Data 2</b></td>
	<td valign='top'><b>Event Data 3</b></td>
	<td valign='top'><b>Event Data 4</b></td>
	
	<td valign='top'><b>Setting 1</b></td>
	<td valign='top'><b>Setting 2</b></td>
	<td valign='top'><b>Setting 3</b></td>
	<td valign='top'><b>Setting 4</b></td>
	
	<td valign='top'><b>&nbsp;</b></td>
</tr>
<?
	$cntr=0;	
	while($row=mysqli_fetch_array($data))
	{		
		//<a href='admin_safety_elog.php?id=".$row['id']."'>".$row['id']."</a>
		echo "
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'>".$row['id']." <input type='hidden' id='event_id_".$cntr."' name='event_id_".$cntr."' value=\"".$row['id']."\"></td>
				<td valign='top'><input type='text' id='event_type_".$cntr."' name='event_type_".$cntr."' value=\"".$row['elog_event']."\" style='width:300px;'></td>
				<td valign='top'>".date("M d, Y H:i",strtotime($row['linedate_added']))."</td>
				<td valign='top'><input type='checkbox' id='event_act_".$cntr."' name='event_act_".$cntr."' value=\"1\"".($row['active'] > 0 ? " checked" : "")."></td>
				
				<td valign='top'><input type='text' id='event_data_1_".$cntr."' name='event_data_1_".$cntr."' value=\"".$row['event_data1']."\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='event_data_2_".$cntr."' name='event_data_2_".$cntr."' value=\"".$row['event_data2']."\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='event_data_3_".$cntr."' name='event_data_3_".$cntr."' value=\"".$row['event_data3']."\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='event_data_4_".$cntr."' name='event_data_4_".$cntr."' value=\"".$row['event_data4']."\" style='width:100px;'></td>
				
				<td valign='top'><input type='text' id='setting_1_".$cntr."' name='setting_1_".$cntr."' value=\"".$row['setting1']."\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='setting_2_".$cntr."' name='setting_2_".$cntr."' value=\"".$row['setting2']."\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='setting_3_".$cntr."' name='setting_3_".$cntr."' value=\"".$row['setting3']."\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='setting_4_".$cntr."' name='setting_4_".$cntr."' value=\"".$row['setting4']."\" style='width:100px;'></td>
				
				<td valign='top'><a href='javascript:confirm_delete(".$row['id'].")'><img src='images/delete_sm.gif' border='0'></a></td>				
			</tr>
		";				
		$cntr++;
	}	
	echo "
			<tr>
				<td valign='top'>New</td>
				<td valign='top'><input type='text' id='event_type_new' name='event_type_new' value=\"\" style='width:300px;'></td>
				<td valign='top'>&nbsp;</td>
				<td valign='top'><input type='checkbox' id='event_act_new' name='event_act_new' value=\"1\" checked></td>
				
				<td valign='top'><input type='text' id='event_data_1_new' name='event_data_1_new' value=\"\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='event_data_2_new' name='event_data_2_new' value=\"\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='event_data_3_new' name='event_data_3_new' value=\"\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='event_data_4_new' name='event_data_4_new' value=\"\" style='width:100px;'></td>
				
				<td valign='top'><input type='text' id='setting_1_new' name='setting_1_new' value=\"\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='setting_2_new' name='setting_2_new' value=\"\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='setting_3_new' name='setting_3_new' value=\"\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='setting_4_new' name='setting_4_new' value=\"\" style='width:100px;'></td>
				
				<td valign='top'>&nbsp;</td>				
			</tr>
		";
	
?>
<tr>
	<td valign='top' colspan='6' align='center'><input type='submit' name='updater' value='Update'></td>
</tr>
</table><input type='hidden' id='elog_events_tot' name='elog_events_tot' value='<?=$cntr ?>'>
<br><br>

</div>
<script type='text/javascript'>
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete this e-log event?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}
	
	
	$().ready(function() 
	{			
		
	});	
					
	//$('.mrr_date_picker').datepicker();
	
     //$(".tablesorter").tablesorter({textExtraction: 'complex'});
	
	/*
	function mrr_verify_user_unique(myid)
	{		
		$('#mrr_naming_message').html('');	
		mrr_lab="Driver";
		mrr_lab2="name_driver_";
		mrr_code=1;
		
		new_name1=$('#'+mrr_lab2+'first').val();
		new_name2=$('#'+mrr_lab2+'last').val();
		new_name=new_name1+' '+new_name2;
		
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
				//$('#'+mrr_lab2+'first').val('');
				//$('#'+mrr_lab2+'last').val('');
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
					$('#'+mrr_lab2+'first').val(''+new_name1+'.');
					$('#'+mrr_lab2+'last').val(''+new_name2+'.');
					$('#mrr_naming_message').html(''+mrr_lab+' must be unique.');	
					$('#mrr_naming_message').css('color','red');									
				}				
			}
		});	
		
	}
	*/	
</script>
</form>
<? include('footer.php') ?>
