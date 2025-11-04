<? include('application.php') ?>
<? 
	$admin_page = 1;
	
	$serve_output="";
	$serve_box="";
	/* 
	if(isset($_GET['service_type']))		$_POST['service_type']=$_GET['service_type'];
	if(isset($_GET['truck_id']))			$_POST['truck_id']=$_GET['truck_id'];
		
	if(isset($_GET['service_type']) && isset($_GET['truck_id']))		$_POST['track_it']=1;	//submit the form any way
	
	if(!isset($_POST['service_type']))		$_POST['service_type']="";
	$serve_box=mrr_peoplenet_service_selector2("service_type",$_POST['service_type']);
	*/
	
	if(isset($_GET['delid']))
	{
		if($_GET['delid'] > 0)
		{
			$serve_output.=mrr_peoplenet_find_data2("pnet_landmark_remove",$_POST,1,$_GET['delid'],0);		
		}	
	}
		
	if(!isset($_POST['landmark_id']))		$_POST['landmark_id']=0;
	if(!isset($_POST['name']))			$_POST['name']="";
	if(!isset($_POST['description']))		$_POST['description']="";
	if(!isset($_POST['addr1']))			$_POST['addr1']="";
	if(!isset($_POST['addr2']))			$_POST['addr2']="";
	if(!isset($_POST['city']))			$_POST['city']="";
	if(!isset($_POST['state']))			$_POST['state']="";
	if(!isset($_POST['zip']))			$_POST['zip']="";
	if(!isset($_POST['custom1']))			$_POST['custom1']="";
	if(!isset($_POST['custom2']))			$_POST['custom2']="";
	if(!isset($_POST['custom3']))			$_POST['custom3']="";
	if(!isset($_POST['custom4']))			$_POST['custom4']="";
	
	$_POST['state']=trim($_POST['state']);
	$_POST['zip']=trim($_POST['zip']);
	
	if(strlen($_POST['state']) > 2)		$_POST['state']=substr($_POST['state'],0,2);
	if(strlen($_POST['zip']) > 5)			$_POST['zip']=substr($_POST['zip'],0,5);
	
	$rowheight=30;
     $entry_form="
     	<table class='admin_menu3' border='0' cellpadding='0' cellspacing='0' width='600'>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Landmark Name/Label ".show_help('peoplenet_landmarks.php','Landmark Name')."</td>
     		<td valign='top'><input type='text' id='name' name='name' class='input_normal' value=\"".$_POST['name']."\"></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Description ".show_help('peoplenet_landmarks.php','Landmark Description')."</td>
     		<td valign='top'><input type='text' id='description' name='description' class='input_normal' value=\"".$_POST['description']."\"></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Address 1 ".show_help('peoplenet_landmarks.php','Landmark Address 1')."</td>
     		<td valign='top'><input type='text' id='addr1' name='addr1' class='input_normal' value=\"".$_POST['addr1']."\"></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Address 2 ".show_help('peoplenet_landmarks.php','Landmark Address 2')."</td>
     		<td valign='top'><input type='text' id='addr2' name='addr2' class='input_normal' value=\"".$_POST['addr2']."\"></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>City ".show_help('peoplenet_landmarks.php','Landmark City')."</td>
     		<td valign='top'><input type='text' id='city' name='city' class='input_normal' value=\"".$_POST['city']."\"></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>State ".show_help('peoplenet_landmarks.php','Landmark State')."</td>
     		<td valign='top'><input type='text' id='state' name='state' class='input_short' value=\"".$_POST['state']."\" maxlength='2'></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Zip ".show_help('peoplenet_landmarks.php','Landmark Zip Code')."</td>
     		<td valign='top'><input type='text' id='zip' name='zip' class='input_medium' value=\"".$_POST['zip']."\" maxlength='5'></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Custom Info 1 ".show_help('peoplenet_landmarks.php','Custom Info Line 1')."</td>
     		<td valign='top'><input type='text' id='custom1' name='custom1' class='input_normal' value=\"".$_POST['custom1']."\"></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Custom Info 2 ".show_help('peoplenet_landmarks.php','Custom Info Line 2')."</td>
     		<td valign='top'><input type='text' id='custom2' name='custom2' class='input_normal' value=\"".$_POST['custom2']."\"></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Custom Info 3 ".show_help('peoplenet_landmarks.php','Custom Info Line 3')."</td>
     		<td valign='top'><input type='text' id='custom3' name='custom3' class='input_normal' value=\"".$_POST['custom3']."\"></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Custom Info 4 ".show_help('peoplenet_landmarks.php','Custom Info Line 4')."</td>
     		<td valign='top'><input type='text' id='custom4' name='custom4' class='input_normal' value=\"".$_POST['custom4']."\"></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Latitude ".show_help('peoplenet_landmarks.php','Latitude')."</td>
     		<td valign='top'><span id='latitude'></span></td>
     	</tr>
     	<tr height='".$rowheight."'>
     		<td valign='top'>Longitude ".show_help('peoplenet_landmarks.php','Longitude')."</td>
     		<td valign='top'><span id='longitude'></span></td>
     	</tr>
     	<tr>
     		<td valign='top'>
     			<span class='mrr_link_like_on' onClick='mrr_load_landmark(0);'>New Landmark</span>
     		</td>
     		<td valign='top'>
     			<input type='hidden' name='landmark_id' id='landmark_id' value='".$_POST['landmark_id']."'>
     			<input type='submit' name='mark_it' id='mark_it' value='Save Landmark'>
     		</td>
     	</tr>
     	
     	</table>
     ";
	
	//  */
	
	if(isset($_POST['mark_it']))
	{
		$serve_output.=mrr_peoplenet_find_data2("pnet_landmark_add",$_POST,1,$_POST['landmark_id'],0);	
	}
		
	$serve_output.=mrr_peoplenet_find_data2("pnet_landmark_view","",0,$_POST['landmark_id'],0);
?>
<? include('header.php') ?>
<form action='<?= $SCRIPT_NAME ?>' method='post'>
<table class='admin_menu2' style='width:1600px'>
<tr>
	<td valign='top' colspan='3'>
		<div class='section_heading'>PeopleNet Landmarks</div>
	</td>
</tr>
<!--
<tr>
	<td valign='top'>	
		Service Type  <?= show_help('peoplenet_interface.php','Service Type') ?>
	</td>
	<td valign='top'>	
		<?= $serve_box ?>
	</td>
	<td valign='top' align='right'>			
		 <input type='submit' name='track_it' id='track_it' value='Run'>
	</td>
</tr>
-->
<tr>
	<td valign='top' colspan='3'>
		<?= $entry_form ?>
	</td>
</tr>
<tr>
	<td valign='top' colspan='3'>&nbsp;</td>
</tr>
<tr>
	<td valign='top' colspan='3'>
		<?= $serve_output ?>
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>
	function mrr_load_landmark(id)
	{		
		var id_num=id;
		
		namer="";
		typer="";
		descr="";
		dress1="";
		dress2="";
		cityr="";
		stater="";
		zipper="";
		cust1="";
		cust2="";
		cust3="";
		cust4="";
		lati="";
		longi="";	
		
		if(id_num > 0)
		{		
     		namer=$('#row_'+id+'_field_2').html();
     		typer=$('#row_'+id+'_field_3').html();
     		descr=$('#row_'+id+'_field_4').html();
     		dress1=$('#row_'+id+'_field_5').html();
     		dress2=$('#row_'+id+'_field_6').html();
     		cityr=$('#row_'+id+'_field_7').html();
     		stater=$('#row_'+id+'_field_8').html();
     		zipper=$('#row_'+id+'_field_9').html();
     		cust1=$('#row_'+id+'_field_10').html();
     		cust2=$('#row_'+id+'_field_11').html();
     		cust3=$('#row_'+id+'_field_12').html();
     		cust4=$('#row_'+id+'_field_13').html();
     		lati=$('#row_'+id+'_field_14').html();
     		longi=$('#row_'+id+'_field_15').html();		
		}
		$('#landmark_id').val(id);
		$('#name').val(namer);
		$('#description').val(descr);
		$('#addr1').val(dress1);
		$('#addr2').val(dress2);
		$('#city').val(cityr);
		$('#state').val(stater);
		$('#zip').val(zipper);
		$('#custom1').val(cust1);
		$('#custom2').val(cust2);
		$('#custom3').val(cust3);
		$('#custom4').val(cust4);
		$('#latitude').html(lati);	
		$('#longitude').html(longi);		
	}
	function mrr_landmark_remover(id)
	{
		$.prompt("Are you sure you want to <span class='alert'>delete</span> this landmark?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						window.location = "<?=$_SERVER['SCRIPT_NAME']?>?delid="+id;
					}
				}
			}
		);	
	}
</script>
<? include('footer.php') ?>