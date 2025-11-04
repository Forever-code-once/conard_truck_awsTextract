<? include('application.php') ?>
<? $admin_page = 1 ?>
<?	
	
	if(isset($_GET['did'])) {
		$sql = "
			update safety_driver_codes set			
				deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_delete = simple_query($sql);
	}
	
	if(isset($_GET['delid'])) {
		$sql = "
			update safety_driver_points set		
				deleted = 1
			where id = '".sql_friendly($_GET['delid'])."'
		";
		$data_delete = simple_query($sql);
		if(isset($_GET['driver_id']))		$_POST['driver_id']=$_GET['driver_id'];
	}
	
	
	//if(isset($_GET['id']))		$_POST['id']=$_GET['id'];	
	//if(!isset($_POST['id']))	$_POST['id']=0;	
	
	if(!isset($_POST['driver_id']))		    $_POST['driver_id']=0;
	if(!isset($_POST['code_id']))			$_POST['code_id']=0;
	if(!isset($_POST['points']))			$_POST['points']=0;
	
	if(!isset($_POST['run_year']))          $_POST['run_year']=(int) date("Y",time());
    if(!isset($_POST['copy_year']))         $_POST['copy_year']=(int) date("Y",time());
    if(!isset($_POST['dot_year']))          $_POST['dot_year']=(int) date("Y",time());

    if(!isset($_POST['points_note']))		$_POST['points_note']="";

    if(!isset($_POST['occurrence']))		$_POST['occurrence']="".date("m/d/Y",time())."";

    if(isset($_POST['copier']))
    {
        if($_POST['copy_year'] > 0 && $_POST['copy_year']!=$_POST['run_year'])
        {
             //copy all the active values to the next selected year... so the DOT point values can be updated, but preserve what it was for the previous range.     
             $sql = "
                select *                
                from safety_driver_codes
                where deleted = 0 and active>0
                    and start_year<='".(int) $_POST['run_year']."' 
                    and end_year>='".(int) $_POST['run_year']."'
                order by id asc, safety_code asc, safety_description asc
            ";
             $data = simple_query($sql);
             while($row=mysqli_fetch_array($data))
             {
                  $sqlu = "
                        insert into safety_driver_codes
                            (id,
                            safety_code,
                            safety_description,
                            deleted,
                            active,
                            start_year,
                            end_year,
                            points,
                            linedate_added)
                        values
                            (NULL,
                            '".sql_friendly($row['safety_code'])."',
                            '".sql_friendly($row['safety_description'])."',
                            0,
                            '".sql_friendly((int)$row['active'])."',
                            '".sql_friendly((int)$_POST['copy_year'])."',
                            '".sql_friendly((int)$_POST['copy_year'])."',
                            '".sql_friendly((int)$row['points'])."',					
                            NOW())
                   ";
                  simple_query($sqlu);
             }
                 
             $_POST['run_year']=$_POST['copy_year'];
             $_POST['dot_year']=$_POST['copy_year'];
        }        
    }
	
	if(isset($_POST['add_code']))
	{
		$add_points=0;
		$mrr_adder="";
		if($_POST['code_id'] > 0 && $_POST['points']==0)
		{
			$sql = "
				select points			
				from safety_driver_codes
				where id = '".sql_friendly($_POST['code_id'])."'
			";
			$data = simple_query($sql);
			if($row=mysqli_fetch_array($data))
			{
				$_POST['points']=$row['points'];	
			}	
		}		
		if($_POST['driver_id'] > 0 && $_POST['code_id'] > 0)	
		{
			$daterx="0000-00-00 00:00:00";
			if(trim($_POST['occurrence'])!="")      $daterx="".date("Y-m-d",strtotime(trim($_POST['occurrence'])))." 00:00:00";
		    
		    $sqlu = "
				insert into safety_driver_points
					(id,
					driver_id,
					points,
					linedate_added,
					linedate,
					user_id,
					deleted,
					safety_code,
					points_note,
					dot_year,
					active)
				values
					(NULL,
					'".sql_friendly($_POST['driver_id'])."',
					'".sql_friendly((int) $_POST['points'])."',
					NOW(),
					'".$daterx."',
					'".sql_friendly($_SESSION['user_id'])."',
					0,
					'".sql_friendly($_POST['code_id'])."',
					'".sql_friendly(trim( $_POST['points_note'] ))."',
					'".sql_friendly((int) $_POST['dot_year'])."',
					1)
				";
			simple_query($sqlu);
			$_POST['points']=0;	
		}
		elseif($_POST['code_id'] > 0 && $_POST['points'] > 0)
		{
             $daterx="0000-00-00 00:00:00";
             if(trim($_POST['occurrence'])!="")      $daterx="".date("Y-m-d",strtotime(trim($_POST['occurrence'])))." 00:00:00";
             
		     $sql="
               		select * 
               		from drivers 
               		where deleted=0 
               			and active=1 
               		order by name_driver_last, name_driver_first
               	";
               $data = simple_query($sql);	
               while($row=mysqli_fetch_array($data))
               {
               	$driver=$row['id'];
               	$sqlu = "
     				insert into safety_driver_points
     					(id,
     					driver_id,
     					points,
     					linedate_added,
     					linedate,
     					user_id,
     					deleted,
     					safety_code,
     					points_note,
     					dot_year,
     					active)
     				values
     					(NULL,
     					'".sql_friendly($driver)."',
     					'".sql_friendly((int) $_POST['points'])."',
     					NOW(),
     					'".$daterx."',
     					'".sql_friendly($_SESSION['user_id'])."',
     					0,
     					'".sql_friendly($_POST['code_id'])."',
     					'".sql_friendly(trim( $_POST['points_note'] ))."',
     					'".sql_friendly((int) $_POST['dot_year'])."',
     					1)
				";
				simple_query($sqlu);
			
               }
               $_POST['points']=0;	
		}
	}
	
	
	if(isset($_POST['updater'])) {
		
		//$use_started = '';				
		//if($_POST['linedate_started'] != '') $use_started = date("Y-m-d", strtotime($_POST['linedate_started']));
		
		//new item
		if(!isset($_POST['safety_code_new']))		$_POST['safety_code_new']="";
		if(!isset($_POST['safety_desc_new']))		$_POST['safety_desc_new']="";
		if(!isset($_POST['safety_points_new']))		$_POST['safety_points_new']=0;
		if(!isset($_POST['safety_act_new']))		$_POST['safety_act_new']=0;
		
		if(trim($_POST['safety_points_new'])=="")	$_POST['safety_points_new']=0;
		if(trim($_POST['safety_act_new'])=="")		$_POST['safety_act_new']=0;
         
		if(!isset($_POST['safety_start_new']))		$_POST['safety_start_new']=date("Y",time());
        if(!isset($_POST['safety_end_new']))		$_POST['safety_end_new']=date("Y",time());
		
		if(trim($_POST['safety_code_new'])!="" || trim($_POST['safety_desc_new'])!="")
		{
			$sqlu = "
				insert into safety_driver_codes
					(id,
					safety_code,
					safety_description,
					deleted,
					active,
					start_year,
					end_year,
					points,
					linedate_added)
				values
					(NULL,
					'".sql_friendly($_POST['safety_code_new'])."',
					'".sql_friendly($_POST['safety_desc_new'])."',
					0,
					'".sql_friendly((int)$_POST['safety_act_new'])."',
					'".sql_friendly((int)$_POST['safety_start_new'])."',
					'".sql_friendly((int)$_POST['safety_end_new'])."',
					'".sql_friendly((int)$_POST['safety_points_new'])."',					
					NOW())
				";
			simple_query($sqlu);
		}	
		
		
		
		//updating older items...
		if(!isset($_POST['safety_points_tot']))		$_POST['safety_points_tot']=0;
		
		for($x=0;$x < $_POST['safety_points_tot']; $x++)
		{
			$myid=$_POST['safety_id_'.$x.''];
			$code=$_POST['safety_code_'.$x.''];
			$desc=$_POST['safety_desc_'.$x.''];
			$points=$_POST['safety_points_'.$x.''];
			
			$act=0;
			if(isset($_POST['safety_act_'.$x.'']))		$act=1;
             
            $start=(int) $_POST['safety_start_'.$x.''];
            $end=(int) $_POST['safety_end_'.$x.''];
			
			if(trim($points)=="")	$points=0;
			
			$sql = "
     			update safety_driver_codes set
     				safety_code='".sql_friendly($code)."',
     				safety_description='".sql_friendly($desc)."',
     				active='".sql_friendly($act)."',
     				start_year='".sql_friendly($start)."',
     				end_year='".sql_friendly($end)."',
     				points='".sql_friendly( (int) $points )."'
     			where id='".sql_friendly($myid)."'
     		";
     		simple_query($sql);
		}
		//header("Location: $SCRIPT_NAME?id=".$new_id);
		//die();
	}

	$sql = "
		select *
		
		from safety_driver_codes
		where deleted = 0
		    and start_year<='".(int) $_POST['run_year']."' 
		    and end_year>='".(int) $_POST['run_year']."'
		order by id asc, safety_code asc, safety_description asc
	";
	$data = simple_query($sql);	
	
	$usetitle="Driver Safety Control Panel";
	/*
	?id=<?=$_GET['id']?>
	*/
		
	
    $selbx3="<select name='run_year' id='run_year' onChange='submit();'>";
    for($i=2013; $i <= ((int) date("Y",time()) + 5); $i++)
    {
        //build select box in same swoop
		$sel="";			if($_POST['run_year']==$i)		$sel=" selected";	
		$selbx3.="<option value='".$i."'".$sel.">".$i."</option>";
	}
	$selbx3.="</select>";
    
    $selbx3b="<select name='copy_year' id='copy_year'>";
    for($i=2021; $i <= ((int) date("Y",time()) + 5); $i++)
    {
         //build select box in same swoop
         $sel="";			if($_POST['copy_year']==$i)		$sel=" selected";
         $selbx3b.="<option value='".$i."'".$sel.">".$i."</option>";
    }
    $selbx3b.="</select>";

    $selbx4="<select name='dot_year' id='dot_year'>";
    for($i=2013; $i <= (int) date("Y",time()); $i++)
    {
         //build select box in same swoop
         $sel="";			if($_POST['dot_year']==$i)		$sel=" selected";
         $selbx4.="<option value='".$i."'".$sel.">".$i."</option>";
    }
    $selbx4.="</select>";           //$_POST['run_year']
?>
<? include('header.php') ?>
<div style='margin-left:25px;'>
<form action="<?=$SCRIPT_NAME ?>" method="post">

<div class='standard18'><b><?= $usetitle ?></b></div><br>

<table class='admin_menu1' style='text-align:left; width:1000px;'>
<tr>
    <td valign='top' colspan="3">
        <b>Choose Year</b>
        <?= $selbx3 ?>
    </td>
    <td valign='top' colspan="5" align="right">
        <b>Copy Active Codes to</b> 
        <?= $selbx3b ?> for new points
        <input type='submit' name='copier' value='Copy'>
    </td>
</tr>
<tr>
	<td valign='top'><b>ID</b></td>
	<td valign='top'><b>Safety Code</b></td>
	<td valign='top'><b>Description</b></td>
	<td valign='top'><b>Points</b></td>
	<td valign='top'><b>Active</b></td>
    <td valign='top'><b>From</b></td>
    <td valign='top'><b>To</b></td>
	<td valign='top'><b>&nbsp;</b></td>
</tr>
<?
	$cntr=0;
	$selbx2="";
	
	
	$selbx2.="<select name='code_id' id='code_id'>";
	$sel="";			if($_POST['code_id']==0)	$sel=" selected";	
	$selbx2.="<option value='0'".$sel.">Select Safety Code</option>";
	
	while($row=mysqli_fetch_array($data))
	{		
		//<a href='admin_safety_violations.php?id=".$row['id']."'>".$row['id']."</a>
		echo "
			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
				<td valign='top'>".$row['id']." <input type='hidden' id='safety_id_".$cntr."' name='safety_id_".$cntr."' value=\"".$row['id']."\"></td>
				<td valign='top'><input type='text' id='safety_code_".$cntr."' name='safety_code_".$cntr."' value=\"".$row['safety_code']."\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='safety_desc_".$cntr."' name='safety_desc_".$cntr."' value=\"".$row['safety_description']."\" style='width:500px;'></td>
				<td valign='top'><input type='text' id='safety_points_".$cntr."' name='safety_points_".$cntr."' value=\"".$row['points']."\" style='width:50px; text-align:right;'></td>
				<td valign='top'><input type='checkbox' id='safety_act_".$cntr."' name='safety_act_".$cntr."' value=\"1\"".($row['active'] > 0 ? " checked" : "")."></td>
				<td valign='top'><input type='text' id='safety_start_".$cntr."' name='safety_start_".$cntr."' value=\"".$row['start_year']."\" size='4'></td>
				<td valign='top'><input type='text' id='safety_end_".$cntr."' name='safety_end_".$cntr."' value=\"".$row['end_year']."\" size='4'></td>
				<td valign='top'><a href='javascript:confirm_delete(".$row['id'].")'><img src='images/delete_sm.gif' border='0'></a></td>				
			</tr>
		";	
		
		//build select box in same swoop
		$sel="";			if($_POST['code_id']==$row['id'])		$sel=" selected";	
		$selbx2.="<option value='".$row['id']."'".$sel.">".$row['safety_code']."</option>";
			
		$cntr++;
	}
	$selbx2.="</select>";
	
	echo "
			<tr>
				<td valign='top'>New</td>
				<td valign='top'><input type='text' id='safety_code_new' name='safety_code_new' value=\"\" style='width:100px;'></td>
				<td valign='top'><input type='text' id='safety_desc_new' name='safety_desc_new' value=\"\" style='width:500px;'></td>
				<td valign='top'><input type='text' id='safety_points_new' name='safety_points_new' value=\"\" style='width:50px; text-align:right;'></td>
				<td valign='top'><input type='checkbox' id='safety_act_new' name='safety_act_new' value=\"1\" checked></td>
				<td valign='top'><input type='text' id='safety_start_new' name='safety_start_new' value=\"".date("Y",time())."\" size='4'></td>
				<td valign='top'><input type='text' id='safety_end_new' name='safety_end_new' value=\"".date("Y",time())."\" size='4'></td>
				<td valign='top'>&nbsp;</td>				
			</tr>
		";
	
?>
<tr>
	<td valign='top' colspan='8' align='center'><input type='submit' name='updater' value='Update'>  <input type='button' name='printer' id='printer' value='Print' onClick='window.print();'></td>
</tr>
</table><input type='hidden' id='safety_points_tot' name='safety_points_tot' value='<?=$cntr ?>'>
<br><br>

<?
	$selbx="";
	$sql="
		select * 
		from drivers 
		where deleted=0 
			and active=1 
		order by name_driver_last, name_driver_first
	";
	$data = simple_query($sql);	
	$selbx.="<select name='driver_id' id='driver_id' onChange='submit();'>";
	
	$sel="";			if($_POST['driver_id']==0)	$sel=" selected";	
	$selbx.="<option value='0'".$sel.">All Drivers</option>";
	
	while($row=mysqli_fetch_array($data))
	{
		$sel="";			if($_POST['driver_id']==$row['id'])		$sel=" selected";	
		$selbx.="<option value='".$row['id']."'".$sel.">".$row['name_driver_first']." ".$row['name_driver_last']."</option>";
	}
	$selbx.="</select>";
	
	
	$mrr_adder="";
	if($_POST['driver_id'] > 0)		$mrr_adder=" and driver_id='".sql_friendly($_POST['driver_id'])."'";
	
	
	$tot_drivers=0;
	$sql = "select max(id) as max_id from drivers";
	$data = simple_query($sql);	
	if($row=mysqli_fetch_array($data))
	{
		$tot_drivers=$row['max_id'];	
	}
	$id_list[0]=0;
	$name_list[0]="";
	$award_list[0]=0;
	$deduct_list[0]=0;
	for($i=0;$i< $tot_drivers;$i++)
	{
		$id_list[$i]=0;
		$name_list[$i]="";
		$award_list[$i]=0;
		$deduct_list[$i]=0;	
	}
	$lowest_val=0;
	$highest_val=0;
	$average_val=0;
	$median_val=0;
	
	$sql = "
		select safety_driver_points.*,
			safety_driver_codes.safety_code as abbr,
			safety_driver_codes.safety_description,
			safety_driver_codes.points as normal_points,
			drivers.name_driver_first,
			drivers.name_driver_last,
			drivers.id as truck_driver_id,
			users.username
		
		from safety_driver_points
			left join drivers on drivers.id=safety_driver_points.driver_id
			left join safety_driver_codes on safety_driver_codes.id=safety_driver_points.safety_code
			left join users on users.id=safety_driver_points.user_id
		where safety_driver_points.deleted = 0
			and drivers.deleted=0
			and drivers.active > 0
			".$mrr_adder."
			and dot_year='".sql_friendly((int) $_POST['dot_year'])."'
		order by drivers.name_driver_last asc,drivers.name_driver_first,linedate asc,linedate_added asc
	";
	//echo "<br>Main Query: ".$sql."<br>";
	$data = simple_query($sql);	
?>
<table class='admin_menu1' style='text-align:left; width:1000px;'>
<tr>
	<td valign='top'>Driver</td>
	<td valign='top'><?=$selbx ?></td>
	<td valign='top'>Safety Code</td>
	<td valign='top'><?=$selbx2 ?></td>
    <td valign='top' nowrap><b>Year</b> <?= $selbx4 ?></td>
	<td valign='top'>Points</td>
	<td valign='top'><input type='text' id='points' name='points' value="<?= $_POST['points'] ?>" style='width:50px; text-align:right;'></td>
	<td valign='top'><input type='submit' name='add_code' value='Update Points'></td>
</tr>
    <tr>
        <td valign='top'>Occurrence</td>        
        <td valign='top'>
            <input type='text' id='occurrence' name='occurrence' value="<?= $_POST['occurrence'] ?>" class="mrr_date_picker" style='width:100px; text-align:left;'>
        </td>
        <td valign='top'>Note</td>
        <td valign='top' colspan='4'>
            <input type='text' id='points_note' name='points_note' value="<?= $_POST['points_note'] ?>" style='width:500px; text-align:left;'>
        </td>
        <td valign='top'>&nbsp;</td>
    </tr>
<tr>
	<td valign='top' colspan='8'>
        <span style="color:purple;"><b>Only Drivers with Points (negative or positive) show below.  Add (training) points to a driver above if that driver is not showing below, such as a new driver.</b></span>
    </td>
</tr>
<tr>
    <td valign='top' colspan='8' align="right">
        <span style="color:#0000CC; cursor:pointer;" onClick="mrr_toggle_details();"><b>Details +/-</b></span>
    </td>
</tr>
<tr>
	<td valign='top'><b>Occurrence</b></td>
	<td valign='top'><b>Driver</b></td>
	<td valign='top'><b>Safety Code</b></td>
	<td valign='top'><b>Description</b></td>
    <td valign='top' align='right'><b>DOT<br>Year</b></td>
	<td valign='top' align='right'><b>Points<br>Normal</b></td>
	<td valign='top' align='right'><b>Points<br>Given</b></td>
	<td valign='top'>&nbsp;</td>
</tr>
	<?
	$cntr=0;
	$point_tot=0;
	
	$dcntr=0;
	$d_arr=array();
	$cur_id=0;
	$cur_name="";
	$cur_awards=0;
	$cur_deducts=0;	
	
	while($row=mysqli_fetch_array($data))
	{
		$myid=$row['truck_driver_id'];
		/*	
		$id_list[$myid]=0;
		$name_list[$myid]="".$row['name_driver_first']." ".$row['name_driver_last']."";
		
		if($row['points'] > 0)		$award_list[$myid]+=$row['points'];
		if($row['points'] < 0)		$deduct_list[$myid]+=$row['points'];	
		*/
		
		if($cur_id!=$myid && $cur_id>0)
		{
			echo "
				<tr class='".($dcntr%2==0 ? "even" : "odd")."'>
					<td valign='top'>&nbsp;</td>
					<td valign='top'><b>".$cur_name."</b></td>				
					<td valign='top'>&nbsp;</td>
					<td valign='top' align='right'><b>Driver Total</b></td>
					<td valign='top' align='right'>&nbsp;</td>
					<td valign='top' align='right'>".$cur_awards."</td>
					<td valign='top' align='right'>".$cur_deducts."</td>				
					<td valign='top' align='right'>".($cur_awards + $cur_deducts)."</td>				
				</tr>
				<tr class='mrr_details ".($dcntr%2==0 ? "even" : "odd")."'>
					<td valign='top' colspan='8'>&nbsp;</td>		
				</tr>
			";			
			
			if(($cur_awards + $cur_deducts) > $highest_val)       $highest_val=($cur_awards + $cur_deducts);
            if(($cur_awards + $cur_deducts) < $lowest_val)        $lowest_val=($cur_awards + $cur_deducts);
             
            $d_arr[ $dcntr ]=($cur_awards + $cur_deducts);
			
			//clear totals for next one...			
			$cur_awards=0;
			$cur_deducts=0;
			$dcntr++;
		}
		
		$cur_id=$myid;
		$cur_name="".$row['name_driver_first']." ".$row['name_driver_last']."";
		if($row['points'] > 0)		$cur_awards+=$row['points'];
		if($row['points'] < 0)		$cur_deducts+=$row['points'];	
		
		//if($_POST['driver_id'] > 0)
		//{
			echo "<tr class='mrr_details ".($dcntr%2==0 ? "even" : "odd")."'>
					<td valign='top'>".date("m/d/Y",strtotime($row['linedate']))."</td>
					<td valign='top'>".$row['name_driver_first']." ".$row['name_driver_last']."</td>				
					<td valign='top'>".$row['abbr']."</td>
					<td valign='top'>".$row['safety_description']."</td>
					<td valign='top' align='right'>".$row['dot_year']."</td>
					<td valign='top' align='right'>".$row['normal_points']."</td>
					<td valign='top' align='right'>".$row['points']."</td>				
					<td valign='top'><a href='javascript:confirm_delete_violation(".$row['id'].",".$_POST['driver_id'].");'><img src='images/delete_sm.gif' border='0'></a></td>				
				</tr>";
			if(trim($row['points_note'])!="")
            {
                 echo "<tr class='mrr_details ".($dcntr%2==0 ? "even" : "odd")."'>
					<td valign='top'>&nbsp;</td>					
					<td valign='top' colspan='6'><i>Note:</i> ".trim($row['points_note'])."</td>
					<td valign='top'>&nbsp;</td>			
				</tr>";
            }
			if($row['user_id'] > 0) 
			{
                 echo "<tr class='mrr_details " . ($dcntr % 2 == 0 ? "even" : "odd") . "'>
					<td valign='top'>&nbsp;</td>	
					<td valign='top' colspan='2'><i>Added:</i> " . date("m/d/Y H:i:s", strtotime($row['linedate_added'])) . "</td>				
					<td valign='top' colspan='4'><i>By:</i> " . trim($row['username']) . "</td>					
					<td valign='top'>&nbsp;</td>			
				</tr>";
            }
			$cntr++;
		//}
		$point_tot+=$row['points'];
					
	}
	if($cur_id>0)
	{
			echo "
				<tr class='".($dcntr%2==0 ? "even" : "odd")."'>
					<td valign='top'>&nbsp;</td>
					<td valign='top'><b>".$cur_name."</b></td>				
					<td valign='top'>&nbsp;</td>
					<td valign='top' align='right'><b>Driver Total</b></td>
					<td valign='top' align='right'>&nbsp;</td>
					<td valign='top' align='right'>".$cur_awards."</td>
					<td valign='top' align='right'>".$cur_deducts."</td>				
					<td valign='top' align='right'>".($cur_awards + $cur_deducts)."</td>				
				</tr>
				<tr class='mrr_details ".($dcntr%2==0 ? "even" : "odd")."'>
					<td valign='top' colspan='87'>&nbsp;</td>		
				</tr>
			";
         
         if(($cur_awards + $cur_deducts) > $highest_val)       $highest_val=($cur_awards + $cur_deducts);
         if(($cur_awards + $cur_deducts) < $lowest_val)        $lowest_val=($cur_awards + $cur_deducts);
         
         $d_arr[ $dcntr ]=($cur_awards + $cur_deducts);
         
         $dcntr++;
	}
	
	
	
	/*
	if($_POST['driver_id'] ==0)
	{
		for($i=0;$i< $tot_drivers;$i++)
		{
			$id_list[$i]=0;
			$name_list[$i]="";
			$award_list[$i]=0;
			$deduct_list[$i]=0;	
		}	
	}
	*/
	
	echo "<tr>
				<td valign='top'>Total</td>
				<td valign='top'>".$dcntr." Drivers</td>				
				<td valign='top'>&nbsp;</td>
				<td valign='top'>&nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>".$point_tot."</td>				
				<td valign='top'>&nbsp;</td>				
			</tr>";
	
	?>
</table>
     <?php
     
     if($dcntr > 0)     $average_val=$point_tot / $dcntr;
     
     $mid_range=$lowest_val + (($highest_val - $lowest_val) / 2);       // figure out what the mid value should be between these min and max number... these can be ZERO so allow for that.
     
     //Default the nearest low and hig to the fartherst numbers... min and mac.  Will move them in below by checking each driver total.
     $nearest_low=$lowest_val;
     $nearest_high=$highest_val;
     
     //echo "<br>Nearest Low starts at ".$nearest_low.", Nearest High starts at ".$nearest_high.". Mid Range shold be ".$mid_range.".";
     
     $found=0;
     for($i=0; $i < $dcntr; $i++)
     {
         //if(!isset($d_arr[$i]) || $d_arr[$i]="")        $d_arr[$i]=0;
         $dval=(int) $d_arr[$i];
         
         if($dval == $mid_range)   
         {     //found the value that IS the Median...so skip the other calculations.
             $median_val=$mid_range;  
             $found=1;
              //echo "<br>FINAL== ".$i.".  Cur Value = ".$dval."...FOUND is ".$found.".  |  Nearest Low starts at ".$nearest_low.", Nearest High starts at ".$nearest_high.". Mid Range shold be ".$mid_range.".";
         } 
          
          if($found==0)
          {     //adjust nearest low and high values to get closer to the mid-range value.
              if($dval > $nearest_low && $dval < $mid_range)    $nearest_low=$dval;
     
              if($dval < $nearest_high && $dval > $mid_range)   $nearest_high=$dval;
          }
     
          //echo "<br>".$i.".  Cur Value = ".$dval."...Found is ".$found.".  |  Nearest Low starts at ".$nearest_low.", Nearest High starts at ".$nearest_high.". Mid Range shold be ".$mid_range.".";
     }

     if($found==0)
     {
          $diff1=abs($median_val - $nearest_low);
          $diff2=abs($median_val - $nearest_high);
          if($diff2==$diff1)
          {    //use average between middle values closest.
               $median_val = ($nearest_high + $nearest_low) / 2;
          }  
          elseif($diff2 > $diff1)
          {    //closer to the lowet value
               $median_val = $nearest_low;
          }
          elseif($diff2 < $diff1)
          {    //closest to higher value
               $median_val = $nearest_high;
          }
          
          //echo "<br>FINAL-- ".$i.".  *** Found is ".$found.".  |  Nearest Low starts at ".$nearest_low.", Nearest High starts at ".$nearest_high.". Mid Range shold be ".$mid_range.".";
     }
        
     echo "<br><br>
        <table width='400'>
        <tr>
            <td valign='top'><b>Overall Summary</b></td>
            <td valign='top' align='right'><b>Value</b></td>
        </tr>
        <tr>
            <td valign='top'>Lowest Points Total</td>
            <td valign='top' align='right'>".$lowest_val."</td>
        </tr>
        <tr>
            <td valign='top'>Highest Points Total</td>
            <td valign='top' align='right'>".$highest_val."</td>
        </tr>
        <tr>
            <td valign='top'>Average Points/Driver (on report)</td>
            <td valign='top' align='right'>".round($average_val,2)."</td>
        </tr>
        <tr>
            <td valign='top'>Median Points</td>
            <td valign='top' align='right'>".floor($median_val)."</td>
        </tr>
        </table>
     ";    
     ?>
</div>
<script type='text/javascript'>
	function confirm_delete(id) {
		if(confirm("Are you sure you want to delete this safety violation?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}
	
	function confirm_delete_violation(id,driver) {
		if(confirm("Are you sure you want to delete this driver safety violation?")) {
			window.location = '<?=$SCRIPT_NAME?>?delid=' + id + '&driver_id='+driver+'';
		}
	}
	
	$().ready(function() 
	{			
		
	});	
	
	function mrr_toggle_details()
    {
        $('.mrr_details').toggle();
    }
					
	$('.mrr_date_picker').datepicker();
	
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
