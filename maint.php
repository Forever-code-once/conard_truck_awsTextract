<?php

include('application.php');

if(isset($_GET['dup_id']))
{
    echo "<br>Duplicating Request ".$_GET['dup_id']."...";
    if((int) $_GET['dup_id'] > 0)
    {
        $new_id=duplicate_row("maint_requests", $_GET['dup_id']);
        echo "<br>New Request ".$new_id." created...";
        if($new_id > 0)
        {
            $_GET['id']=$new_id;

            $sql = "
				update maint_requests set					
					linedate_completed='0000-00-00 00:00:00',
					linedate_scheduled='".date("Y-m-d",time())." 00:00:00',
					linedate_added=NOW(),
					user_id='".sql_friendly($_SESSION['user_id'])."',
					sicap_invoice_user_id=0,
					sicap_invoice_id=0,
					linedate_invoiced='0000-00-00 00:00:00',
					sicap_invoice_markup_rate='0.00',
					sicap_invoice_labor_rate='0.00',
					customer_id=0,
					cur_load_id=0,
					cur_location='',
					next_load_id=0,
					next_location='',
					parent_request_id='".sql_friendly($_GET['dup_id'])."',
					active=1,
					deleted=0					
				where id = '".sql_friendly($new_id)."'
			";
            simple_query($sql);

            echo "<br>New Request ".$new_id." updated...";

            header("Location: maint.php?id=".$new_id);
            die;
        }
        else
        {
            echo "<br>New Request ".$new_id." NOT created.  :(  ";
        }
    }
    else
    {
        echo "<br>Request Not Selected Properly. :(  ";
    }
}

if(isset($_GET['did']))
{
    $sql = "
		update maint_requests		
		set	deleted = 1
		where id = '".sql_friendly($_GET['did'])."'
	";
    $data_delete = simple_query($sql);

    header("Location: maint.php?id=0");
    die;
}

include('header.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');

if(isset($_GET['lid']))
{
    $sql = "
		update maint_line_items		
		set	deleted = 1
		where id = '".sql_friendly($_GET['lid'])."'
	";
    $data_delete = simple_query($sql);
}

$mrr_show_inspect=0;
$mrr_equip_select=0;
$mrr_req_id=0;
$set_id="";
if(isset($_GET['id']))
{
    if($_GET['id']> 0)
    {
        $set_id="?id=".$_GET['id']."";
        $mrr_req_id=$_GET['id'];
    }
    elseif(isset($_POST['req_id']) && $_POST['req_id']>0)
    {
        $set_id="?id=".$_POST['req_id']."";
        $mrr_req_id=$_POST['req_id'];
    }
}

$mrr_item_id=0;
if(isset($_GET['item']))
{
    if($_GET['item']> 0)
    {
        $set_item_id="?id=".$mrr_req_id."&item=".$_GET['item']."";
        $mrr_item_id=$_GET['item'];
    }
    elseif(isset($_POST['item']) && $_POST['item']>0)
    {
        $set_item_id="?id=".$mrr_req_id."&item=".$_POST['item']."";
        $mrr_item_id=$_POST['item'];
    }
}

$e_type=0;
$e_select=0;
$request_truck_breakdown=0;

if(isset($_GET['inspect']))
{
    $mrr_show_inspect=1;


    $e_type=((int)$_GET['e_type']==2 ? 59 : 58);		//trucks=58 (1), trailers=59 (2) as in equipment history.
    $e_select=(int)$_GET['e_id'];
    $main_desc="".($_GET['inspect']==1 ? "PMI" : "FED")." INSPECTION DUE ".date("m/d/Y",strtotime(trim($_GET['due']))).".";
    $req_active=1;
    $schedule_date=date("Y-m-d H:i:s",time());
    $urgent=1;
    //$request_truck_breakdown=0
}

mrr_repair_maint_request_schedule_dates("");		//dater="", repairs the scheduled date if any are set to a bad date.

?>
    <form action="<?=$SCRIPT_NAME ?><?= $set_id ?>" method="post">
        <table class='' style='text-align:left;'>
            <tr>
                <td valign='top' class='no_req_print'>
                    <table cellpadding='2' cellspacing='0' width='600' border='1' class='table_grid' style='margin:4px'>
                        <tr>
                            <td width="150" valign='top'><b>Go To</b></td>
                            <td width="150" valign='top'><b>Maintenance Requests</b></td>
                            <td width="150" valign='top'><a href="maint_group.php"><b>Group Requests</b></a></td>
                            <td width="150" valign='top'><a href="units_need_repair.php"><b>Units Needing Repair</b></a></td>
                        </tr>
                        <tr>
                            <td valign='top'><a href="maint_recur.php"><b>Recurring Requests</b></a></td>
                            <td valign='top'><a href="maint_recur_notices.php"><b>Maintenance Alerts</b></a></td>
                            <td valign='top'><a href="report_maint_requests.php"><b>Maintenance Reports</b></a></td>
                            <td valign='top'>&nbsp;</td>
                        </tr>
                    </table>

                    <br>

                    <table class='admin_menu1'>
                        <tr>
                            <td colspan='2'><font class='standard18'><b>Maintenance Requests</b></font></td>
                        </tr>
                        <tr>
                            <td><a href="<?=$SCRIPT_NAME ?>?id=0">Add New Maintenance Request</a></td>
                            <td><a href="<?=$SCRIPT_NAME ?>">Maintenance Request List</a></td>
                        </tr>
                        <tr>
                            <td><div id='auto_request_listing_count' class='section_heading'></div></td>
                            <td>
                                <div class='section_heading'>
                                    <span onClick='mrr_snooze_toggle();' style='color:#0000CC; cursor:pointer;'><b>Show/Hide Snooze MR</b></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'><div id='auto_request_listing'></div>	</td>
                        </tr>
                        <tr>
                            <td colspan='2'><a href='report_maint_requests.php?current=1' target='_blank'>Email This List with Report</a></td>
                        </tr>
                    </table>

                </td>

                <td valign='top'>
                    <?php
                    $list_moder=0;
                    if(!isset($_GET['id']))		$list_moder=1;

                    if(isset($_GET['id']))
                    {
                    if(!isset($_GET['inspect']))
                    {
                        $e_type=0;
                        $e_select=0;
                        $main_desc="";
                        $req_active=0;
                        $schedule_date="0000-00-00 00:00:00";
                        $urgent=0;
                    }
                    $completed_date="0000-00-00 00:00:00";
                    $down_time="0";
                    $cost_est="0.00";
                    $odometer="0";
                    $recref=0;

                    //$request_truck_breakdown=0;

                    $created_by="";
                    $created_on="";

                    $next_load=0;
                    $cur_load=0;
                    $cur_local="";
                    $next_local="";

                    $unit_location="";

                    //&truck_id=".$truck_id."&trailer_id=".$trailer_id."&msg_id=".$msg_id."
                    if(isset($_GET['truck_id']) && isset($_GET['trailer_id']) && isset($_GET['msg_id']))
                    {
                        $e_type=58;
                        $e_select=(int) $_GET['truck_id'];

                        if($_GET['truck_id'] > 0 && $_GET['trailer_id'] > 0)
                        {
                            $e_type=59;
                            $e_select=(int) $_GET['trailer_id'];
                        }

                        $main_desc="";

                        $sqlm = "
          		select *				
          		from ".mrr_find_log_database_name()."truck_tracking_msg_history
          		where id='".sql_friendly($_GET['msg_id']) ."'
          	";
                        $datam= simple_query($sqlm);
                        while($rowm = mysqli_fetch_array($datam))
                        {
                            $main_desc=trim($rowm['msg_text']);
                            $req_active=1;
                        }
                    }

                    if(	isset($_POST['equipment_type']) && $_POST['equipment_type']> 0)		$e_type=$_POST['equipment_type'];
                    if(	isset($_POST['xref_id']) && $_POST['xref_id']> 0)					$e_select=$_POST['xref_id'];
                    if(	isset($_POST['request_active']) && $_POST['request_active']> 0)		$req_active=$_POST['request_active'];
                    if(	isset($_POST['request_desc']) && $_POST['request_desc']> 0)			$main_desc=$_POST['request_desc'];
                    if(	isset($_POST['down_time_hours']) && $_POST['down_time_hours']> 0)	$down_time=$_POST['down_time_hours'];
                    if(	isset($_POST['cost_estimate']) && $_POST['cost_estimate']> 0)		$cost_est=$_POST['cost_estimate'];
                    if(	isset($_POST['req_odometer']) && $_POST['req_odometer']> 0)			$odometer=$_POST['req_odometer'];

                    if(	isset($_POST['request_urgent']) && $_POST['request_urgent']> 0)		$urgent=$_POST['request_urgent'];

                    $recur_flag=0;
                    $recur_days=0;
                    $recur_mileage=0;

                    if(	isset($_POST['recur_days']) && $_POST['recur_days']> 0)			$recur_days=$_POST['recur_days'];
                    if(	isset($_POST['recur_miles']) && $_POST['recur_miles']> 0)			$recur_mileage=$_POST['recur_miles'];


                    $geotab_msg_id=0;
                    if($mrr_req_id>0)
                    {
                        $sql = "
				select id 
				from ".mrr_find_log_database_name()."geotab_datafeed_log
				where feed_type=8 
					and maint_request_id = '".sql_friendly($mrr_req_id)."'								
			";
                        $data = simple_query($sql);
                        if($row = mysqli_fetch_array($data))
                        {
                            $geotab_msg_id=$row['id'];
                        }
                    }
                    mrr_geotab_repair_maint_request($geotab_msg_id,0);		//0=not showing debug, 1=to show debug


                    if($mrr_req_id>0)
                    {
                        $sql = "
				select maint_requests.*,
					(select users.username from users where users.id=maint_requests.user_id) as userx
				from maint_requests
				where maint_requests.id = '".sql_friendly($mrr_req_id)."'			
			";		// and maint_requests.active=1

                        $data = simple_query($sql);
                        $row = mysqli_fetch_array($data);

                        $e_type=$row['equip_type'];
                        $e_select=$row['ref_id'];
                        $main_desc=$row['maint_desc'];
                        $req_active=$row['active'];
                        $request_truck_breakdown=$row['unit_breakdown'];
                        $schedule_date=$row['linedate_scheduled'];
                        $completed_date=$row['linedate_completed'];
                        $down_time=$row['down_time_hours'];
                        $cost_est=$row['cost'];
                        $odometer=$row['odometer_reading'];

                        if($schedule_date=="" || $schedule_date=="0000-00-00 00:00:00" || $schedule_date=="1969-31-12 00:00:00")		$schedule_date=date("Y-m-d")."00:00:00";

                        $recur_flag=$row['recur_flag'];
                        $recur_days=$row['recur_days'];
                        $recur_mileage=$row['recur_mileage'];
                        $recref=$row['recur_ref'];
                        $urgent=$row['urgent'];
                        $safety_shutdown=$row['safety_shutdown'];

                        $next_load=$row['next_load_id'];
                        $cur_load=$row['cur_load_id'];
                        $cur_local=trim($row['cur_location']);
                        $next_local=trim($row['next_location']);

                        $mrr_equip_select=$e_select;	//used for java/ajax function

                        $created_by=$row['userx'] ." at ";
                        $created_on=date("m/d/Y H:i",strtotime($row['linedate_added']));
                        $tmp_date=$row['linedate_added'];

                        $use_t_name="truck";
                        if($e_type==2 || $e_type==59)		$use_t_name="trailer";

                        if($e_type==1 || $e_type==58)
                        {
                            $gps_current_date=date("m/d/Y H:i");
                            /*
                            $tres=mrr_find_only_location_of_this_truck($row['ref_id']);			//$cur_location=$tres['location'];
                            //$truck_lat=$tres['latitude'];								//$temp_page=$tres['temp_page'];
                            //$truck_long=$tres['longitude'];

                            $gps_location=$tres['gps_location'];
                            //$truck_speed=$tres['truck_speed'];
                            //$truck_head=$tres['truck_head'];
                            */
                            $sql = "
          			select geotab_current_location
          			from trucks
          			where deleted = 0
          				and trucks.id = '".sql_friendly($row['ref_id']) ."'
          		";
                            $data = simple_query($sql);
                            if($row = mysqli_fetch_array($data))
                            {
                                $gps_location=trim($row['geotab_current_location']);
                            }

                            $unit_location="<br><b>Current PN Location:</b> ".$gps_location." as of ".$gps_current_date.".";		//[".$row['ref_id']."]
                        }
                        if($e_type==2 || $e_type==59)
                        {
                            $unit_location="<br><b>Trailer's Last Location:</b> ".$cur_local."?";

                            $sql2="
               		select trailers_dropped.*         		
               		from trailers_dropped
               		where trailers_dropped.deleted = 0               			
               			and trailers_dropped.trailer_id = '".sql_friendly($row['ref_id'])."'               			
               		order by trailers_dropped.linedate desc,trailers_dropped.id desc 
               	";		// and trailers_dropped.drop_completed = 0
                            // and trailers_dropped.linedate_completed!='0000-00-00 00:00:00'
                            $data2 = simple_query($sql2);
                            if($row2 = mysqli_fetch_array($data2))
                            {
                                $unit_location="<br><b>Trailer Last Dropped:</b> ".trim($row2['location_city']).", ".trim($row2['location_state'])." <b>".trim($row2['location_zip'])."</b> as of ".date("m/d/Y H:i",strtotime($row2['linedate'])).".";
                            }


                        }

                        if($cur_load==0 && $e_select > 0 && $e_type > 0)
                        {
                            $sql2 = "
					select load_handler_id,linedate_pickup_eta,destination_state,destination,origin_state,origin,dispatch_completed		
					from trucks_log
					where deleted=0
						and ".$use_t_name."_id = '".sql_friendly($e_select)."'	
						and linedate_pickup_eta<='".$tmp_date."'
					order by linedate_pickup_eta desc		
				";
                            $data2 = simple_query($sql2);
                            if($row2 = mysqli_fetch_array($data2))
                            {
                                $cur_load=$row2['load_handler_id'];
                                $cur_local=trim("".$row2['origin'].", ".$row2['origin_state']." to ".$row2['destination'].", ".$row2['destination_state']."");

                                $sql3 = "
						update maint_requests set
							cur_location='".sql_friendly($cur_local)."',
							cur_load_id='".sql_friendly($cur_load)."'						
						where id = '".sql_friendly($mrr_req_id)."'	
					";
                                simple_query($sql3);
                            }
                        }
                        if($next_load==0 && $e_select > 0 && $e_type > 0)
                        {
                            $sql2 = "
					select load_handler_id,linedate_pickup_eta,destination_state,destination,origin_state,origin,dispatch_completed		
					from trucks_log
					where deleted=0
						and ".$use_t_name."_id = '".sql_friendly($e_select)."'	
						and linedate_pickup_eta>='".$tmp_date."'						
					order by linedate_pickup_eta asc		
				";
                            $data2 = simple_query($sql2);
                            if($row2 = mysqli_fetch_array($data2))
                            {
                                $next_load=$row2['load_handler_id'];
                                $next_local=trim("".$row2['origin'].", ".$row2['origin_state']." to ".$row2['destination'].", ".$row2['destination_state']."");

                                $sql3 = "
						update maint_requests set
							next_location='".sql_friendly($next_local)."',
							next_load_id='".sql_friendly($next_load)."'						
						where id = '".sql_friendly($mrr_req_id)."'	
					";
                                simple_query($sql3);
                            }
                        }
                        /*
                        if($next_load==0 && $e_select > 0 && $e_type > 0)
                        {	//try the preplanned load if still blank...
                            $sql2 = "
                                select id,linedate_pickup_eta,dest_state,dest_city,origin_state,origin_city
                                from load_handler
                                where deleted=0
                                    and ".$use_t_name."_id = '".sql_friendly($e_select)."'
                                    and linedate_pickup_eta>='".$tmp_date."'
                                order by linedate_pickup_eta asc
                            ";
                            $data2 = simple_query($sql2);
                            if($row2 = mysqli_fetch_array($data2))
                            {
                                $next_load=$row2['id'];
                                $next_local=trim("".$row2['origin_city'].", ".$row2['origin_state']." to ".$row2['dest_city'].", ".$row2['dest_state']."");

                                $sql3 = "
                                    update maint_requests set
                                        next_location='".sql_friendly($next_local)."',
                                        next_load_id='".sql_friendly($next_load)."'
                                    where id = '".sql_friendly($mrr_req_id)."'
                                ";
                                simple_query($sql3);
                            }
                        }
                        */
                    }
                    else
                    {
                        $req_active=1;
                        $schedule_date=date("Y-m-d",time())."00:00:00";
                    }

                    $equip_type_box=mrr_select_box_for_options("equipment_type",'equipment_type',$e_type,'Select Equipment Type');

                    //fillers for new line item
                    $label_1_item="";
                    $quant_1_item=1;
                    $hours_1_item=0;
                    $cost_1_item=0;
                    $maker_1_item="";
                    $model_1_item="";
                    $active_1_item=1;
                    $req_cat_1_item=0;
                    $pos_x_1_item=0;
                    $pos_y_1_item=0;
                    $pos_z_1_item=0;
                    $pos_t_1_item=0;

                    if(isset($_POST['item_1_label']) && $_POST['item_1_label']> 0)		$label_1_item=$_POST['item_1_label'];
                    if(isset($_POST['item_1_quant']) && $_POST['item_1_quant']> 0)		$quant_1_item=$_POST['item_1_quant'];
                    if(isset($_POST['item_1_hours']) && $_POST['item_1_hours']> 0)		$hours_1_item=$_POST['item_1_hours'];
                    if(isset($_POST['item_1_cost']) && $_POST['item_1_cost']> 0)		$cost_1_item=$_POST['item_1_cost'];
                    if(isset($_POST['item_1_maker']) && $_POST['item_1_maker']> 0)		$maker_1_item=$_POST['item_1_maker'];
                    if(isset($_POST['item_1_model']) && $_POST['item_1_model']> 0)		$model_1_item=$_POST['item_1_model'];
                    if(isset($_POST['item_1_active']) && $_POST['item_1_active']> 0)		$active_1_item=$_POST['item_1_active'];
                    if(isset($_POST['item_1_req_cat']) && $_POST['item_1_req_cat']> 0)	$req_cat_1_item=$_POST['item_1_req_cat'];
                    if(isset($_POST['item_1_pos_x']) && $_POST['item_1_pos_x']> 0)		$pos_x_1_item=$_POST['item_1_pos_x'];
                    if(isset($_POST['item_1_pos_y']) && $_POST['item_1_pos_y']> 0)		$pos_y_1_item=$_POST['item_1_pos_y'];
                    if(isset($_POST['item_1_pos_z']) && $_POST['item_1_pos_z']> 0)		$pos_z_1_item=$_POST['item_1_pos_z'];
                    if(isset($_POST['item_1_pos_t']) && $_POST['item_1_pos_t']> 0)		$pos_t_1_item=$_POST['item_1_pos_t'];

                    $request_cat_box=mrr_select_box_for_options("request_category",'item_1_req_cat',$req_cat_1_item,'Select Request Category');

                    $pos_x_box=mrr_select_box_for_options("positions_x_axis",'item_1_pos_x',$pos_x_1_item,'Select Position X');
                    $pos_y_box=mrr_select_box_for_options("positions_y_axis",'item_1_pos_y',$pos_y_1_item,'Select Position Y');
                    $pos_z_box=mrr_select_box_for_options("positions_z_axis",'item_1_pos_z',$pos_z_1_item,'Select Position Z');
                    $pos_t_box=mrr_select_box_for_options("positions_t_axis",'item_1_pos_t',$pos_t_1_item,'Select Position T');


                    $schedule_date=$created_on;
                    ?>
                    <div id='table_section_for_new_form'>
                        <table class='admin_menu1 hide_from_printer_all'>
                            <tr>
                                <td>
                                    <div id='request_new_mainter' style='border: solid #cccccc 1px; background-color: #e4eaff;' class='print_req'>
                                        <br>
                                        <table cellpadding="0" cellspacing="0">
                                            <tr height="30">
                                                <td colspan="2">
                                                    <font class='standard16'><b>&nbsp;Maintenance Request <span id="req_num"><?=$mrr_req_id ?></span></b></font>
                                                </td>
                                                <td colspan="4" align='right'>
                                                    &nbsp;
                                                    <?php if($mrr_req_id > 0) { ?>
                                                        <span  class='no_req_print'><b>E-Mail To:</b></span>
                                                        <input name="email_to" id="email_to" style="width: 300px;" value="" class='no_req_print'>
                                                        <input type='button' name="email_to_send" id="email_to_send" value="Send" onClick='mrr_simple_email();' class='no_req_print'>
                                                        <input type='button' name="email_to_print" id="email_to_print" value="Print" onClick='mrr_simple_print();' class='no_req_print'>
                                                        <input type='button' name="email_to_back" id="email_to_back" value="Back" onClick='mrr_simple_show();' class='no_req_print'>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign='top'>&nbsp;<b>Request Description</b></td>
                                                <td valign='top' colspan="5"><textarea name="request_desc" id="request_desc" rows="3" cols="100" wrap="virtual" style='text-align:left;'><?=$main_desc ?></textarea>&nbsp;
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign='top' colspan="6"><div id="accident_list" style='text-align:left;'></div></td>
                                            </tr>
                                            <tr>
                                                <td colspan='6'>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;<b>Equipment Type</b></td>
                                                <td><?=$equip_type_box ?></td>
                                                <td><b>Equipment Item</b></td>
                                                <td>
                                                    <select name='equipment_xref_id' id='equipment_xref_id'>
                                                        <option value='0'>Please select equipment type</option>
                                                    </select>
                                                </td>
                                                <td><b>Odometer Reading</b></td>
                                                <td><input name="req_odometer" id="req_odometer" style="width: 80px; text-align: right;" value="<?= $odometer ?>"></td>
                                            </tr>
                                            <tr>
                                                <td colspan='6' align='center'>
                                                    &nbsp;<br>
                                                    <b>Snooze Options:</b>
                                                    <span onClick='mrr_mr_local_snooze(<?=$mrr_req_id ?>,7);' style="color:#0000CC; cursor:pointer;">7 Days</span> -
                                                    <span onClick='mrr_mr_local_snooze(<?=$mrr_req_id ?>,14);' style="color:#0000CC; cursor:pointer;">14 Days</span> -
                                                    <span onClick='mrr_mr_local_snooze(<?=$mrr_req_id ?>,21);' style="color:#0000CC; cursor:pointer;">21 Days</span> -
                                                    <span onClick='mrr_mr_local_snooze(<?=$mrr_req_id ?>,28);' style="color:#0000CC; cursor:pointer;">28 Days</span> -
                                                    <span onClick='mrr_mr_local_snooze(<?=$mrr_req_id ?>,0);' style="color:#0000CC; cursor:pointer;">Clear</span>
                                                    (Delay/Ignore feature)
                                                    <br>&nbsp;
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan='6' align='center'><div id='mrr_truck_in_shop'></div></td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;&nbsp;<label for='request_active'><b>Active</b></label>
                                                    <input type='checkbox' name='request_active' id='request_active' <?php if($req_active) echo 'checked'?>>
                                                </td>
                                                <td><label for='request_urgent'><b>Deadline/Urgent</b></label>
                                                    <input type='checkbox' name='request_urgent' id='request_urgent' <?php if($urgent) echo 'checked'?>>
                                                </td>
                                                <td><b>Scheduled Date</b></td>
                                                <td><input name="req_schedule_date" id="req_schedule_date" style="width: 80px;" value="<?= ($schedule_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($schedule_date)): "" ) ?>" readonly><!--- class="datepicker" ----></td>
                                                <td><b>Completed Date</b></td>
                                                <td><input name="req_complete_date" id="req_complete_date" style="width: 80px;" class="datepicker" value="<?= ($completed_date!="0000-00-00 00:00:00" ? date("m/d/Y", strtotime($completed_date)) : "")?>"></td>
                                            </tr>
                                            <?php
                                            //Added on Nov 10, 2020. ... MRR
                                            if($safety_shutdown && $completed_date!="0000-00-00 00:00:00" && $e_select > 0 && $mrr_req_id > 0)
                                            {
                                                $safety_shutdown=false;

                                                //trucks
                                                if($e_type==1 || $e_type==58)
                                                {
                                                    $sqlu="
                            update trucks set
	    						in_shop_note=''					
		    				where id = '".sql_friendly($e_select)."' and in_shop_note like 'Safety Shut Down!%'
                           ";
                                                    simple_query($sqlu);
                                                }

                                                //trailers
                                                if($e_type==2 || $e_type==59)
                                                {
                                                    $sqlu="
                            update trailers set
	    						in_shop_notes=''					
		    				where id = '".sql_friendly($e_select)."' and in_shop_notes like 'Safety Shut Down!%'
                           ";
                                                    simple_query($sqlu);
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td>&nbsp;&nbsp;</td>
                                                <td>&nbsp;
                                                    <label for='request_truck_breakdown'><b>Broken Down!</b></label>
                                                    <input type='checkbox' name='request_truck_breakdown' id='request_truck_breakdown' <?php if($request_truck_breakdown) echo 'checked'?>>
                                                    <br>
                                                    <label for='safety_shutdown'><b>Safety Shut Down!</b></label>
                                                    <input type='checkbox' name='safety_shutdown' id='safety_shutdown' <?php if($safety_shutdown) echo 'checked'?>>
                                                </td>
                                                <td><b>&nbsp;</b></td>
                                                <td>&nbsp;</td>
                                                <td><b>&nbsp;</b></td>
                                                <td>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;&nbsp;<b>Created by</b></td>
                                                <td><?=$created_by ?><?=$created_on ?></td>
                                                <td><b>Down Time (Hours)</b></td>
                                                <td><input name="down_time_hours" id="down_time_hours" style="width: 80px; text-align: right; background-color:#cccccc;" value="<?= number_format($down_time,2) ?>" readonly></td>
                                                <td><b>Cost $</b></td>
                                                <td><input name="cost_estimate" id="cost_estimate" style="width: 80px; text-align: right; background-color:#cccccc;" value="<?= number_format($cost_est,2) ?>" readonly></td>
                                            </tr>
                                            <tr>
                                                <td colspan='2'>&nbsp;<input type="button" name="update_request_button" id="update_request_button" value="Update Request" class='no_req_print'></td>
                                                <td colspan='2'><div id='last_odometer_reading'></div></td>
                                                <td>
                                                    <?php
                                                    if($_GET['id'] > 0 && 1==2)
                                                    {
                                                        ?>
                                                        <input type="button" id="maint_recur_maker" value="Copy to Regular Schedule" onClick="schedule_item_maint_v3(<?= $_GET['id'] ?>);">
                                                        <?php
                                                    }		// onClick="schedule_item_maint(<?= $_GET['id'] ? >,<?= $e_select ? >,<?= $e_type ? >);"
                                                    ?>
                                                    <?php
                                                    if($_GET['id'] > 0)
                                                    {
                                                        ?>
                                                        <input type="button" id="maint_copy_maker" value="Duplicate Request" onClick="mrr_copy_maint_request_selected(<?= $_GET['id'] ?>);">
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if($_GET['id'] > 0)
                                                    {
                                                        ?>
                                                        <a href='maint_group.php?request_id=<?= $_GET['id'] ?>' target='_blank'>Make Copies</a>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan='6'>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td valign='top' colspan='2'>
                                                    <?php
                                                    if($cur_load > 0)
                                                    {
                                                        echo "Current Load (when created) <a href='manage_load.php?load_id=".$cur_load."' target='_blank'><b>".$cur_load."</b></a>. ";
                                                    }
                                                    ?>
                                                </td>
                                                <td valign='top' colspan='4' rowspan='4'>
                                                    <?php
                                                    $truck_maint_requests="";
                                                    if($_GET['id'] > 0 && $e_type > 0 && $e_select > 0)		$truck_maint_requests=mrr_display_maint_request_section($e_type,$e_select,620,150);
                                                    ?>
                                                    <?= $truck_maint_requests ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign='top' colspan='2'>
                                                    <?php
                                                    if(trim($cur_local)!="")
                                                    {
                                                        echo "<b>Trip (when created):</b><br>".trim($cur_local).". ";
                                                    }
                                                    ?>

                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign='top' colspan='2'>
                                                    <?php
                                                    if($next_load > 0)
                                                    {
                                                        echo "Next Load (after created) <a href='manage_load.php?load_id=".$next_load."' target='_blank'><b>".$next_load."</b></a>. ";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign='top' colspan='2'>
                                                    <?php
                                                    if(trim($next_local)!="")
                                                    {
                                                        echo "<b>Trip (after created):</b><br>".trim($next_local).". ";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan='6'><?= $unit_location ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan='6'>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td colspan='6'>
                                                    <b>Update this unit's current location for MR:</b>
                                                    <input name="mr_location" id="mr_location" style="width:500px;" value="" class='no_req_print'>
                                                    <input type='button' name="update_mr_location" id="update_mr_location" value="Update MR Location Log" onClick='mrr_update_mr_location(<?=$_GET['id'] ?>);' class='no_req_print'>
                                                    <br>
                                                    <?php
                                                    //$report=mrr_get_mr_unit_locations($_GET['id']);
                                                    //echo "<br><b>MR Unit Location Log:</b><br>".$report."<br>";
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan='6'>&nbsp;</td>
                                            </tr>

                                        </table>
                                    </div>
                                    <?php
                                    //Inspection section.
                                    $inspect_id=0;	// && $_SERVER['REMOTE_ADDR'] == '70.90.229.29'
                                    if($_GET['id'] > 0 && ($e_type==1 || $e_type==58) && $e_select > 0)
                                    {
                                        //Trucks Inspection section...added Oct 2017
                                        $sql = "
     		select id
     		from maint_inspect_trucks
     		where maint_id='".sql_friendly($_GET['id'])."'
     	";
                                        $data=simple_query($sql);
                                        if($row=mysqli_fetch_array($data))
                                        {
                                            $inspect_id=$row['id'];
                                        }
                                        if($inspect_id==0)
                                        {
                                            //create one...even if it never gets used...

                                            $driver_id=0;
                                            $rep_num='';
                                            $id_type=0;
                                            $id_num='';
                                            $inspect_name='';
                                            $inspect_local='';
                                            $pass=0;
                                            $meets_396_19_items=0;

                                            $inspect_id=mrr_update_truck_inspection(0,$e_select,$_GET['id'],58,$driver_id,$rep_num,$id_type,$id_num,$inspect_name,$inspect_local,$pass,$meets_396_19_items);
                                        }
                                        if($inspect_id > 0)
                                        {
                                            $inspect_form=mrr_form_truck_inspection($inspect_id);
                                            $pdf_form=$inspect_form;

                                            echo "<div id='print_me' class='no_req_print'>".$inspect_form."</div>";		//hide_from_printer
                                        }
                                    }
                                    elseif($_GET['id'] > 0 && ($e_type==2 || $e_type==59) && $e_select > 0)
                                    {
                                        //Trailer inspection section...added Nov 2015
                                        $sql = "
     		select id
     		from maint_inspect_trailers
     		where maint_id='".sql_friendly($_GET['id'])."'
     	";
                                        $data=simple_query($sql);
                                        if($row=mysqli_fetch_array($data))
                                        {
                                            $inspect_id=$row['id'];
                                        }
                                        if($inspect_id==0)
                                        {
                                            //create one...even if it never gets used...
                                            $gen['qualify_section_396_19']=0;            $gen['qualify_section_396_25']=0;

                                            $ckbxs['inspect_ck_reg']=0;               	$cds['inspect_cd_reg']=0;
                                            $ckbxs['inspect_ck_body']=0;               	$cds['inspect_cd_body']=0;
                                            $ckbxs['inspect_ck_frame']=0;               	$cds['inspect_cd_frame']=0;
                                            $ckbxs['inspect_ck_rear']=0;               	$cds['inspect_cd_rear']=0;
                                            $ckbxs['inspect_ck_susp']=0;               	$cds['inspect_cd_susp']=0;
                                            $ckbxs['inspect_ck_brake']=0;               	$cds['inspect_cd_brake']=0;
                                            $ckbxs['inspect_ck_wheel']=0;               	$cds['inspect_cd_wheel']=0;
                                            $ckbxs['inspect_ck_tires']=0;               	$cds['inspect_cd_tires']=0;
                                            $ckbxs['inspect_ck_light']=0;               	$cds['inspect_cd_light']=0;
                                            $ckbxs['inspect_ck_decal']=0;               	$cds['inspect_cd_decal']=0;

                                            $ckbxs['inspect_ck_bpm_items']=0;            $cds['inspect_cd_bpm_items']=0;
                                            $ckbxs['inspect_ck_cpm_items']=0;            $cds['inspect_cd_cpm_items']=0;
                                            $ckbxs['inspect_ck_annual']=0;               $cds['inspect_cd_annual']=0;
                                            $ckbxs['inspect_ck_attach']=0;              	$cds['inspect_cd_attach']=0;

                                            $brake['brake_left_front']=0;      		$brake['brake_right_front']=0;
                                            $brake['brake_left_rear']=0;       		$brake['brake_right_rear']=0;

                                            $tread['tread_lfo']=0;	$tread['tread_lfi']=0;               $tread['tread_rfi']=0; 	$tread['tread_rfo']=0;
                                            $tread['tread_lro']=0;   $tread['tread_lri']=0;               $tread['tread_rri']=0;		$tread['tread_rro']=0;

                                            $inspect_id=mrr_update_trailer_inspection(0,$e_select,$_GET['id'],$ckbxs,$cds,$gen,$tread,$brake);
                                        }
                                        if($inspect_id > 0)
                                        {
                                            $inspect_form=mrr_form_trailer_inspection($inspect_id);
                                            $pdf_form=$inspect_form;

                                            echo "<div id='print_me' class='no_req_print'>".$inspect_form."</div>";		//hide_from_printer
                                        }
                                    }
                                    else
                                    {
                                        echo "<h4><b>Notice:</b> Save Request to enter line items or create inspection form for this request.</h4>";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            $classy="none";		if($mrr_req_id>0)	$classy="block";
                            ?>
                            <tr>
                                <td>
                                    <br>
                                    <div id='request_new_items' style='border: solid #cccccc 1px; background-color: #f4f4f4; display:<?= $classy ?>;' class='print_req_items'>
                                        <br>
                                        <table cellpadding='0' cellspacing='0'>
                                            <tr height="30">
                                                <td valign='top' width='165'>
                                                    <span id='link_like_0' class='mrr_link_like_on' onClick='load_line_item_form(0,0);'>&nbsp;&nbsp;<b>New Line Item</b></span>
                                                </td>
                                                <td valign='top' colspan="2">
                                                    <input name="item_1_label" id="item_1_label" style="width: 350px;" value="<?= $label_1_item ?>">
                                                </td>
                                                <td valign='top' align='right'>
                                                    &nbsp;&nbsp;<b>Quantity</b> <input name="item_1_quant" id="item_1_quant" style="width: 80px; text-align: right;" value="<?= $quant_1_item ?>">
                                                </td>
                                                <td valign='top' align='right'>
                                                    <b>Unit Cost $</b> <input name="item_1_cost" id="item_1_cost" style="width: 80px; text-align: right;" value="<?= number_format($cost_1_item,2) ?>">
                                                </td>
                                            </tr>
                                            <tr height="30">
                                                <td valign='top'>
                                                    <b>&nbsp;</b>
                                                </td>
                                                <td valign='top'>
                                                    <b>Additional Details</b>
                                                </td>
                                                <td valign='top'>
                                                    <?= $request_cat_box  ?>
                                                </td>
                                                <td valign='top'>&nbsp;&nbsp;<label for='item_1_active'><b>Active</b></label>
                                                    <input type='checkbox' name='item_1_active' id='item_1_active' <?php if($active_1_item) echo 'checked'?>>
                                                </td>
                                                <td valign='top' align='right'>
                                                    <b>Time (Hours)</b> <input name="item_1_hours" id="item_1_hours" style="width: 80px; text-align: right;" value="<?= number_format($hours_1_item,2) ?>">
                                                </td>
                                            </tr>
                                            <tr height="30">
                                                <td valign='top'>
                                                    <b>&nbsp;</b>
                                                </td>
                                                <td valign='top'>
                                                    <b>Vendor</b>
                                                </td>
                                                <td valign='top'>
                                                    <input name="item_1_maker" id="item_1_maker" style="width: 200px;" value="<?= $maker_1_item ?>">
                                                </td>
                                                <td valign='top'>
                                                    &nbsp;&nbsp;<?= $pos_x_box  ?>
                                                </td>
                                                <td valign='top' align='right'>
                                                    <?= $pos_z_box  ?>
                                                </td>
                                            </tr>


                                            <tr height="30">
                                                <td valign='top'>
                                                    &nbsp;&nbsp;<input type="button" name="update_line_item_button" id="update_line_item_button" value="Save Line Item" class='no_req_print'>
                                                    <input type="hidden" name="item_1_id" id="item_1_id" value="0">
                                                </td>
                                                <td valign='top'>
                                                    <b>Part</b>
                                                </td>
                                                <td valign='top'>
                                                    <input name="item_1_model" id="item_1_model" style="width: 200px;" value="<?= $model_1_item ?>">
                                                </td>
                                                <td valign='top'>
                                                    &nbsp;&nbsp;<?= $pos_y_box  ?>
                                                </td>
                                                <td valign='top' align='right'>
                                                    <?= $pos_t_box  ?>
                                                </td>

                                            </tr>
                                        </table>
                                        <div id='auto_maint_line_item_listing'></div>

                                        <?php
                                        if(!isset($use_admin_level))		$use_admin_level=0;

                                        if(isset($_GET['id']) &&  $use_admin_level >= (int) $defaultsarray['maint_invoicing_access'])
                                        {																		// && 1==2
                                            echo "<div style='margin:10px; border:1px solid orange; padding:10px;' class='no_req_print'>";

                                            //$labor_rate_val=floatval($defaultsarray['maint_labor_rate']);
                                            //$markup_val=floatval($defaultsarray['maint_invoice_markup']);

                                            /* get the customer list */
                                            $sql = "
               		select customers.*,
               			(
               				select count(*) 
               				from attachments 
               				where attachments.section_id='".SECTION_CUSTOMER."' 
               					and attachments.deleted='0' 
               					and attachments.xref_id=customers.id 
               					and attachments.descriptor='M'
               			) as doc_cntr
               		
               		from customers
               		where customers.deleted = 0
               			and customers.active = 1
               		order by customers.name_company
               	";
                                            $data_customers = simple_query($sql);

                                            $marr=mrr_get_sicap_maint_invoice($_GET['id']);
                                            if($marr['invoice'] > 0 || $marr['user_id'] > 0)
                                            {
                                                //="0.00";
                                                //$marr['markup']="0.00";

                                                if($marr['invoice'] > 0)
                                                {
                                                    echo "
							<b>INVOICED:</b> 
							<a href='https://trucking.conardtransportation.com/accounting/invoice.php?invoice_id=".$marr['invoice']."' target='_blank'><b>Invoice ".$marr['invoice']."</b></a>							
							created by <a href='admin_users.php?eid=".$marr['user_id']."' target='_blank'>".$marr['user']."</a> on ".date("m/d/Y H:i", strtotime($marr['date']))."
							for <a href='admin_customers.php?eid=".$marr['cust_id']."' target='_blank'>".$marr['cust']."</a> 
							| Markup ".$marr['markup']." (0.10=10%) | Labor $".$marr['labor']."/Hr | 
							<span class='alert' style='cursor:pointer;' onClick='mrr_kill_sicap_invoice(".$_GET['id'].");'><b>X</b></span>
						";
                                                }
                                                else
                                                {
                                                    echo "
							<b>INVOICED:</b> 
							<b> - REMOVED</b>						
							by <a href='admin_users.php?eid=".$marr['user_id']."' target='_blank'>".$marr['user']."</a> on ".date("m/d/Y H:i", strtotime($marr['date'])).".
							
							Create a new one for customer: 
							
							<select name='customer_id' class='standard12' id='customer_id'>
							<option value='0'>--Unspecified--</option>
							";
                                                    while($row_customers = mysqli_fetch_array($data_customers))
                                                    {
                                                        $sel="";		if($marr['cust_id'] == $row_customers['id']) $sel=" selected";
                                                        echo "<option value='".$row_customers['id']."'".$sel.">".$row_customers['name_company']."</option>";
                                                    }
                                                    echo "</select>";
                                                    echo " <input type='button' name='create_invoice_id' id='create_invoice_id' value='Recreate' onClick='mrr_make_sicap_invoice(".$_GET['id'].");'>";
                                                }
                                            }
                                            else
                                            {
                                                echo "
						<b>INVOICED:</b> 
						N/A, so create one for customer:  
						
						<select name='customer_id' class='standard12' id='customer_id'>
						<option value='0'>--Unspecified--</option>
					";
                                                while($row_customers = mysqli_fetch_array($data_customers))
                                                {
                                                    $sel="";		if($marr['cust_id'] == $row_customers['id']) $sel=" selected";
                                                    echo "<option value='".$row_customers['id']."'".$sel.">".$row_customers['name_company']."</option>";
                                                }
                                                echo "</select>";
                                                echo " <input type='button' name='create_invoice_id' id='create_invoice_id' value='Create the Invoice' onClick='mrr_make_sicap_invoice(".$_GET['id'].");'>";
                                            }
                                            echo "</div>";
                                        }
                                        ?>

                                    </div>

                                </td>
                            </tr>

                            <tr>
                                <td class='no_req_print'>
                                    <h4><b>Notice: </b>Attachments and Notes section each require this request to be saved (it has a Request ID or shows on left side).</h4>

                                    <?php if($use_new_uploader > 0) { ?>

                                        <br>&nbsp;<br>
                                        <center>
                                            <iframe src="mrr_uploader_hack.php?section_id=10&id=<?=$mrr_req_id ?>&mrr_id=<?=$mrr_req_id ?>" width='500' height='80' border='0' style='border:#000000 solid 0px; background-color:#ffffff;'>
                                            </iframe>
                                        </center>
                                        <div id='attachment_holder'></div>

                                    <?php } else { ?>

                                        <div id='upload_section'></div>

                                    <?php } ?>


                                    <div id='request_notes'></div>
                                    <br>
                                    <input type='hidden' name='req_id' id='req_id' value="<?= $mrr_req_id ?>">
                                </td>
                            </tr>
                        </table>

                </td>
            </tr>
        </table>

        </div>
        <?php } ?>
        </td>
        </tr>
        </table>
    </form>
    <script type='text/javascript'>
        var current_req_id = <?= $mrr_req_id ?> ;
        var mrr_display_snooze = 0;
        <?php
        if($mrr_req_id > 0)
        {
            echo " create_note_section('#request_notes', 10, $mrr_req_id); ";


            //echo " create_upload_section('#upload_section', 10, $mrr_req_id); ";
            if($use_new_uploader > 0)
            {
                echo " create_upload_section_alt('#upload_section', 10, $mrr_req_id); ";
            }
            else
            {
                echo " create_upload_section('#upload_section', 10, $mrr_req_id); ";
            }
        }

        ?>
        var equip_pre_sel=<?= $e_select ?>;

        $('.datepicker').datepicker();

        $('#equipment_type').change(function() {

            display_equipment_select_box( $('#equipment_type').val(), 0);
            mrr_load_truck_in_shop();

            mrr_inspection_display(0);
        });
        //check for last mileage Reading
        $('#equipment_xref_id').change(function() {

            mrr_test_for_prior_maint_request(current_req_id,$('#equipment_type').val(), $('#equipment_xref_id').val())

            display_get_last_odometer_reading($('#equipment_type').val(), $('#equipment_xref_id').val());
            mrr_load_truck_in_shop();
        });

        function mrr_pull_accident_list()
        {
            $('#accident_list').html('Loading Accident Trucks linked to this MR...');

            $.ajax({
                url: "ajax.php?cmd=mrr_pull_accident_list",
                type: "post",
                dataType: "xml",
                data: {
                    "maint_id": parseInt(current_req_id)
                },
                error: function() {
                    alert('General error updating the Maint Request Unit Location Log.');
                },
                success: function(xml) {

                    mrr_list=$(xml).find('Listing').text();

                    $('#accident_list').html(''+ mrr_list+'');
                }
            });
        }

        function mrr_mr_local_snooze(id,days)
        {
            $.ajax({
                url: "ajax.php?cmd=mrr_update_mr_unit_local_snooze",
                type: "post",
                dataType: "xml",
                data: {
                    "maint_id": parseInt(id),
                    "delay": parseInt(days)
                },
                error: function() {
                    alert('General error updating the Maint Request Unit Location Log.');
                },
                success: function(xml) {

                    //$.noticeAdd({text: "Maint Request Inspection Item has been updated."});
                    //$("#update_request_button").click();
                    location.reload();
                }
            });
        }
        function mrr_update_mr_location(myid)
        {
            var mr_local=	$('#mr_location').val();
            var maint_id=myid;
            var item=$('#equipment_xref_id').val();
            var etype_id=$('#equipment_type').val();

            $.ajax({
                url: "ajax.php?cmd=mrr_update_maint_request_unit_location",
                type: "post",
                dataType: "xml",
                data: {
                    "maint_id": parseInt(maint_id),
                    "etype_id": etype_id,
                    "item": item,
                    "mr_location": mr_local
                },
                error: function() {
                    alert('General error updating the Maint Request Unit Location Log.');
                },
                success: function(xml) {

                    //$.noticeAdd({text: "Maint Request Inspection Item has been updated."});
                    //$("#update_request_button").click();
                    location.reload();
                }
            });
        }
        function mrr_update_mr_location_alt(myid)
        {

            var maint_id=myid;
            var mr_local=	$('#cur_local_'+maint_id+'').val();
            var item=$('#cur_local_'+maint_id+'unit').html();
            var etype_id=$('#cur_local_'+maint_id+'_type').html();

            $.ajax({
                url: "ajax.php?cmd=mrr_update_maint_request_unit_location_alt",
                type: "post",
                dataType: "xml",
                data: {
                    "maint_id": parseInt(maint_id),
                    "etype_id": etype_id,
                    "item": item,
                    "mr_location": mr_local
                },
                error: function() {
                    alert('General error updating the Maint Request Unit Location Log List.');
                },
                success: function(xml) {

                    //$.noticeAdd({text: "Maint Request Inspection Item has been updated."});
                    //$("#update_request_button").click();
                    $('#cur_local_'+maint_id+'').val("");
                    location.reload();
                }
            });
        }
        function mrr_send_mr_msg_note(msg_id,msg_time,msg_user,msg_type,msg_unit,msg_note)
        {
            if(parseInt(msg_id)==0)
            {
                alert('General error: Please select a Maint Request ID to send the location note.');
                return;
            }

            $.ajax({
                url: "ajax.php?cmd=mrr_send_mr_msg_note",
                type: "post",
                dataType: "xml",
                data: {
                    "id": parseInt(msg_id),
                    "type": msg_type,
                    "unit": msg_unit,
                    "time": msg_time,
                    "user": msg_user,
                    "note": msg_note
                },
                error: function() {
                    alert('General error sending the Maint Request note.');
                },
                success: function(xml) {

                    $.noticeAdd({text: "Maint Request note has been sent."});
                }
            });
        }
        function mrr_toggle_main_request_auto_notes()
        {
            $('.auto_notes').toggle();
        }

        function mrr_auto_update_inspect_setting(sect,subid)
        {
            var inspectid=parseInt($('#inspect_id').val());

            var ok_ans=parseInt($('#ok_'+sect+'_'+subid+'').val());
            var nr_ans=parseInt($('#repairs_'+sect+'_'+subid+'').val());
            var rd_ans=""+ $('#rdate_'+sect+'_'+subid+'').val() + "";
            var nt_ans="";
            if(parseInt(sect)==15)
            {
                var nt_ans=""+ $('#custom_'+sect+'_'+subid+'').val() +"";
            }

            if(parseInt(sect)==0 || parseInt(subid)==0)
            {
                //$.prompt("<span class='mrr_alert'>WARNING: Bad Sector "+parseInt(sect)+" and/or Bad SubID "+parseInt(subid)+".<br><br>");
            }
            else
            {
                //$.prompt("<span class='mrr_alert'>NOTICE: Sector "+parseInt(sect)+" | SubID "+parseInt(subid)+".  Date is "+rd_ans+".<br><br>");

                $.ajax({
                    url: "ajax.php?cmd=mrr_update_maint_truck_inspect_item",
                    type: "post",
                    dataType: "xml",
                    data: {
                        "sect_id": parseInt(sect),
                        "sub_id": parseInt(subid),
                        "ans_ok": ok_ans,
                        "ans_repairs": nr_ans,
                        "ans_date": rd_ans,
                        "ans_note": nt_ans,
                        "inspect_id":inspectid
                    },
                    error: function() {
                        alert('general error updating the Maint Request Inspection Item.');
                    },
                    success: function(xml) {

                        $.noticeAdd({text: "Maint Request Inspection Item has been updated."});
                    }
                });
            }
        }

        function mrr_test_for_prior_maint_request(req_id,e_type,e_xref)
        {
            $.ajax({
                url: "ajax.php?cmd=mrr_prior_maint_request",
                type: "post",
                dataType: "xml",
                data: {
                    //POST variables needed for "page" to load for XML output
                    "req_id": req_id,
                    "e_type": e_type,
                    "e_xref": e_xref
                },
                error: function() {
                    alert('general error finding prior requests for this equipment.');
                },
                success: function(xml) {

                    mrr_test=$(xml).find('OtherRequests').text();
                    if(mrr_test!="")
                    {
                        $.prompt("<span class='mrr_alert'>WARNING: This unit is already on the following request(s):</span><br><br>"+mrr_test+"<br>Click Ok to continue with a new request, or click on the request above.<br>");
                    }

                }
            });
        }

        function mrr_kill_sicap_invoice(maint_id)
        {
            if(parseInt(maint_id)==0)		return;

            $.prompt("Are you sure you want to remove the invoice link from this Maintenance Request? <br><br><span class='alert'><b><i>--Don't forget to remove the invoice on the accounting side</i></b></span>", {
                buttons: {'Yes': true, 'No': false},
                submit: function(v, m, f) {
                    if(v) {

                        $.ajax({
                            url: "ajax.php?cmd=mrr_kill_sicap_invoice",
                            type: "post",
                            dataType: "xml",
                            data: {
                                //POST variables needed for "page" to load for XML output
                                req_id: parseInt(maint_id)
                            },
                            error: function() {
                                alert('general error removing invoice linkage.');
                            },
                            success: function(xml) {
                                $.noticeAdd({text: "Maintenance Request Invoice has been unlinked.  Please remove it from Accounting side."});
                                window.location = "<?=$_SERVER['SCRIPT_NAME']?>?id="+maint_id;
                            }
                        });

                    }
                }
            });
        }
        function mrr_make_sicap_invoice(maint_id)
        {
            if(parseInt(maint_id)==0)		return;

            $.ajax({
                url: "ajax.php?cmd=mrr_make_sicap_invoice",
                type: "post",
                dataType: "xml",
                data: {
                    //POST variables needed for "page" to load for XML output
                    req_id: parseInt(maint_id),
                    cust_id: parseInt($('#customer_id').val())
                },
                error: function() {
                    alert('Error creating invoice. Please try again later.');
                },
                success: function(xml) {

                    inv_id = parseInt($(xml).find('InvoiceID').text());
                    item_id = parseInt($(xml).find('InventoryItem').text());
                    custid = parseInt($(xml).find('CustID').text());
                    if(inv_id)
                    {
                        $.noticeAdd({text: "Maintenance Request has been Invoiced."});
                        window.location = "<?=$_SERVER['SCRIPT_NAME']?>?id="+maint_id;
                    }
                    else
                    {
                        if(custid==0)		$.prompt("Invoice Failed.  Please make sure you have selected a Customer for the Invoice.  You might double-check the Completion Date while you are at it.");
                        if(item_id==0)		$.prompt("Invoice Failed.  Could not locate the proper Inventory item for Accounting.  You might double-check the Completion Date while you are at it.");
                    }
                }
            });
        }


        function mrr_inspection_display(cd)
        {
            if(cd==0)	$('.trailer_inspection_form').hide();
            if(cd==1)	$('.trailer_inspection_form').show();
        }

        function mrr_copy_maint_request_selected(id)
        {
            window.location = "<?=$_SERVER['SCRIPT_NAME']?>?dup_id="+id;
        }

        function schedule_item_maint_v3(myid)
        {
            var maint_id=myid;
            var item=$('#equipment_xref_id').val();
            var etype_id=$('#equipment_type').val();
            var my_new_id=0;

            $.prompt("Are you sure you want to create a Recurring Maintenance Alert for this Maintenance Request?", {
                buttons: {'Yes': true, 'No': false},
                submit: function(v, m, f) {
                    if(v) {


                        $.ajax({
                            url: "ajax.php?cmd=ajax_copy_maint_request_or_recurring",
                            type: "post",
                            dataType: "xml",
                            data: {
                                //POST variables needed for "page" to load for XML output
                                //$('#equipment_type').val()
                                //$('#equipment_xref_id').val()
                                request_id: maint_id,
                                req_recur_flag: 0,
                                equipment_type: etype_id,
                                equipment_xref_id: item
                            },
                            error: function() {
                                alert('general error scheduling item using recurring schedule.');
                            },
                            success: function(xml) {
                                $(xml).find('CopyRequest').each(function() {

                                    my_new_id=$(this).find('RequestDestinationID').text();
                                    if(my_new_id==0)
                                    {
                                        //.noticeAdd({text: "Copy Failed.  This Recurring Maintenance Alert is already in the system."});
                                        $.prompt("Copy Failed.  This Recurring Maintenance Alert is already in the system.");
                                    }
                                    else
                                    {
                                        $.noticeAdd({text: "Recurring Maintenance Alert "+my_new_id+" has been created based on this Maintenance Request."});
                                    }
                                    display_maint(0, 0);
                                });
                            }
                        });

                    }
                }
            });
        }

        function mrr_max_tread_value(fielder)
        {
            if(fielder=='')	return;

            tval=parseInt($('#'+fielder+'').val());

            minval=1;
            maxval=32;

            if(tval < minval || tval > maxval)
            {
                $.prompt("<b>Error: ("+fielder+")</b> You can only enter tread values from "+minval+" to "+maxval+".  Please try again.");
                $('#'+fielder+'').val(minval);
                $('#'+fielder+'').focus();
            }
        }

        //update maintenance request.
        $('#update_request_button').click(function() {

            test_type=parseInt($('#equipment_type').val());
            test_val=parseInt($('#equipment_xref_id').val());

            if(test_type==0 || test_val==0)
            {
                $.prompt("<b>Error:</b> You must select the Equipment Type and the Equipment Item.  Please try again.");
                return;
            }

            $.ajax({
                url: "ajax.php?cmd=ajax_update_maint_req",
                type: "post",
                dataType: "xml",
                data: {
                    //POST variables needed for "page" to load for XML output
                    req_id: current_req_id,
                    req_scheduled: $('#req_schedule_date').val(),
                    req_completed: $('#req_complete_date').val(),
                    req_odometer: $('#req_odometer').val(),
                    req_equip_type: $('#equipment_type').val(),
                    req_equip_id: $('#equipment_xref_id').val(),
                    req_downtime: $('#down_time_hours').val(),
                    req_cost: $('#cost_estimate').val(),
                    req_desc: $('#request_desc').val(),

                    req_recur_flag: 0,
                    req_recur_days: 0,
                    req_recur_miles: 0,

                    req_breakdown: ($('#request_truck_breakdown').is(':checked') ? 1 : 0),
                    req_active:  ($('#request_active').is(':checked') ? 1 : 0),
                    req_urgent:  ($('#request_urgent').is(':checked') ? 1 : 0),
                    req_safety:  ($('#safety_shutdown').is(':checked') ? 1 : 0)

                },
                error: function() {
                    alert('Error saving request. Please make sure Request Description has some text in the box.');
                },
                success: function(xml) {
                    $('#last_odometer_reading').html('');

                    $(xml).find('NewMaintRequest').each(function() {
                        $('#last_odometer_reading').append("Request "+ $(this).find('RequestID').text()+" has been saved on "+ $(this).find('RequestDate').text()+".");
                    });

                    current_req_id = parseInt($(xml).find('RequestID').text());
                    if(current_req_id) display_maint_line_item( current_req_id );
                    $.noticeAdd({text: "Maintenance Request has been updated."});
                    //$('#table_section_for_new_form').html('');

                    //$('#request_new_items').css("display","block");
                    if(current_req_id>0) {
                        $('#request_new_items').show();
                        $('#req_num').html(''+current_req_id+'');
                    }

                    display_maint(0, 0);
                    $('#request_desc').focus();
                }
            });

        });

        //add line item request.
        $('#update_line_item_button').click(function() {
            var old_item_id=$('#item_1_id').val();
            var new_item_id=0;

            $.ajax({
                url: "ajax.php?cmd=ajax_update_maint_req_item",
                type: "post",
                dataType: "xml",
                data: {
                    //POST variables needed for "page" to load for XML output........... current_req_id
                    item_id: 0 ,
                    maint_id: current_req_id,
                    cat_id: $('#item_1_req_cat').val(),
                    item_desc: $('#item_1_label').val(),
                    quantity: $('#item_1_quant').val(),
                    make: $('#item_1_maker').val(),
                    model: $('#item_1_model').val(),
                    item_downtime: $('#item_1_hours').val(),
                    item_cost: $('#item_1_cost').val(),
                    location_front: $('#item_1_pos_x').val(),
                    location_left: $('#item_1_pos_y').val(),
                    location_top: $('#item_1_pos_z').val(),
                    location_inside: $('#item_1_pos_t').val(),
                    item_active:  ($('#item_1_active').is(':checked') ? 1 : 0)
                },
                error: function() {
                    alert('Error saving request item. Please make sure New Line Item has a label, or save the request before adding the line items.');
                },
                success: function(xml) {
                    //alert('REQ_ID is '+ $('#req_id').val() +'.');
                    $('#last_odometer_reading').html('');

                    $(xml).find('NewMaintRequestItem').each(function() {
                        $('#last_odometer_reading').append("New Request "+ $(this).find('RequestItemID').text()+" has been saved on "+ $(this).find('RequestItemDate').text()+".");

                        //remove copy
                        new_item_id=$(this).find('RequestItemID').text();
                        if(new_item_id >0 && old_item_id >0)
                        {
                            delete_single_line_item(current_req_id,old_item_id);
                        }
                        $('#item_1_id').val(new_item_id);
                    });

                    $.noticeAdd({text: "Maintenance Request Item has been saved."});

                    if(current_req_id > 0) {
                        display_maint_line_item( current_req_id );
                        $('#req_num').html(''+current_req_id+'');
                    }

                    $('#item_1_label').focus();

                }
            });

        });

        function mrr_print_this_inspection()
        {
            //$('.hide_from_printer_all').hide();
            //$('#print_me').show();
            $('.hide_from_printer').hide();
            //alert('testing');
            mrrPrintElem("#print_me");
        }


        function mrrPrintElem(elem)
        {
            mrrPopup($(elem).html());
        }

        function mrrPopup(data)
        {
            var mywindow = window.open('', 'inspection', 'height=1600,width=1200');
            mywindow.document.write('<html><head><title>Inspection</title>');
            mywindow.document.write('<link rel="stylesheet" href="style.css" type="text/css" />');		//optional stylesheet
            mywindow.document.write('<style>* { font-size:10px; }</style>');
            mywindow.document.write('</head><body>');
            //alert(data);
            mywindow.document.write(data);

            mywindow.document.write('</body></html>');

            //mywindow.document.close(); 	// necessary for IE >= 10
            mywindow.focus(); 			// necessary for IE >= 10

            setTimeout(function() { mywindow.print(); }, 500);
            //mywindow.close();

            $('.hide_from_printer').show();

            return true;
        }

        function mrr_update_truck_inspection()
        {
            if(parseInt($('#inspect_id').val()) > 0)
            {
                var mrr_use_id_type=parseInt($('#unit_type_id').val());
                var mrr_use_id_numb="";

                mrr_use_id_numb=""+$('#unit_type_number').val()+"";
                if(mrr_use_id_type==1)	mrr_use_id_numb=""+$('#unit_type_plate').val()+"";
                if(mrr_use_id_type==2)	mrr_use_id_numb=""+$('#unit_type_vin').val()+"";
                //if(mrr_use_id_type==3)	mrr_use_id_numb=""+$('#unit_type_plate').val()+"";

                $('#unit_type_number').val(""+mrr_use_id_numb+"");

                $.ajax({
                    url: "ajax.php?cmd=mrr_update_maint_truck_inspection",
                    type: "post",
                    dataType: "xml",
                    data: {
                        "inspect_id": $('#inspect_id').val(),

                        "meets_396_19_items":  $('#meets_396_19_items').val(),

                        "inspector_name": $('#inspector_name').val(),
                        "inspector_local": $('#inspector_local').val(),

                        "unit_type_id": mrr_use_id_type,
                        "unit_type_number": mrr_use_id_numb,

                        "brake_left_steering": $('#brake_left_steering').val(),
                        "brake_right_steering": $('#brake_right_steering').val(),
                        "brake_left_front": $('#brake_left_front').val(),
                        "brake_right_front": $('#brake_right_front').val(),
                        "brake_left_rear": $('#brake_left_rear').val(),
                        "brake_right_rear": $('#brake_right_rear').val(),

                        "tread_lso": $('#tread_lso').val(),
                        "tread_rso": $('#tread_rso').val(),
                        "tread_lfo": $('#tread_lfo').val(),
                        "tread_lfi": $('#tread_lfi').val(),
                        "tread_rfi": $('#tread_rfi').val(),
                        "tread_rfo": $('#tread_rfo').val(),
                        "tread_lro": $('#tread_lro').val(),
                        "tread_lri": $('#tread_lri').val(),
                        "tread_rri": $('#tread_rri').val(),
                        "tread_rro": $('#tread_rro').val(),

                        "passed":  $('#passed').val()
                    },
                    error: function() {
                        alert('Error saving Truck Inspection...');
                    },
                    success: function(xml) {

                        myres=parseInt($(xml).find('rslt').text());
                        if(myres==0 && parseInt($('#passed').val()) > 0)
                        {
                            $.prompt("<b>Error:</b> <br>Inspection has been saved, but cannot be passed if any items are not passed, are skipped, or are in need of repairs that are not yet completed.<br>NA values are fine as long as ALL items are not NA.");
                            $('#passed').val('0');
                        }
                        else
                        {
                            $.noticeAdd({text: "Maintenance Request Truck Inspection has been saved."});
                        }
                    }
                });
            }
        }


        function mrr_update_this_inspection()
        {
            if(parseInt($('#inspect_id').val()) > 0)
            {
                $.ajax({
                    url: "ajax.php?cmd=mrr_update_maint_trailer_inspection",
                    type: "post",
                    dataType: "xml",
                    data: {
                        "inspect_id": $('#inspect_id').val(),

                        "qualify_section_396_19":  ($('#qualify_section_396_19').is(':checked') ? 1 : 0),
                        "qualify_section_396_25":  ($('#qualify_section_396_25').is(':checked') ? 1 : 0),

                        "inspect_ck_reg":  ($('#inspect_ck_reg').is(':checked') ? 1 : 0),
                        "inspect_ck_body":  ($('#inspect_ck_body').is(':checked') ? 1 : 0),
                        "inspect_ck_frame":  ($('#inspect_ck_frame').is(':checked') ? 1 : 0),
                        "inspect_ck_rear":  ($('#inspect_ck_rear').is(':checked') ? 1 : 0),
                        "inspect_ck_susp":  ($('#inspect_ck_susp').is(':checked') ? 1 : 0),
                        "inspect_ck_brake":  ($('#inspect_ck_brake').is(':checked') ? 1 : 0),
                        "inspect_ck_wheel":  ($('#inspect_ck_wheel').is(':checked') ? 1 : 0),
                        "inspect_ck_tires":  ($('#inspect_ck_tires').is(':checked') ? 1 : 0),
                        "inspect_ck_light":  ($('#inspect_ck_light').is(':checked') ? 1 : 0),
                        "inspect_ck_decal":  ($('#inspect_ck_decal').is(':checked') ? 1 : 0),

                        "inspect_ck_bpm_items":  ($('#inspect_ck_bpm_items').is(':checked') ? 1 : 0),
                        "inspect_ck_cpm_items":  ($('#inspect_ck_cpm_items').is(':checked') ? 1 : 0),
                        "inspect_ck_annual":  ($('#inspect_ck_annual').is(':checked') ? 1 : 0),
                        "inspect_ck_attach":  ($('#inspect_ck_attach').is(':checked') ? 1 : 0),

                        "inspect_cd_reg": $('#inspect_cd_reg').val(),
                        "inspect_cd_body": $('#inspect_cd_body').val(),
                        "inspect_cd_frame": $('#inspect_cd_frame').val(),
                        "inspect_cd_rear": $('#inspect_cd_rear').val(),
                        "inspect_cd_susp": $('#inspect_cd_susp').val(),
                        "inspect_cd_brake": $('#inspect_cd_brake').val(),
                        "inspect_cd_wheel": $('#inspect_cd_wheel').val(),
                        "inspect_cd_tires": $('#inspect_cd_tires').val(),
                        "inspect_cd_light": $('#inspect_cd_light').val(),
                        "inspect_cd_decal": $('#inspect_cd_decal').val(),

                        "inspect_cd_bpm_items": $('#inspect_cd_bpm_items').val(),
                        "inspect_cd_cpm_items": $('#inspect_cd_cpm_items').val(),
                        "inspect_cd_annual": $('#inspect_cd_annual').val(),
                        "inspect_cd_attach": $('#inspect_cd_attach').val(),

                        "brake_left_front": $('#brake_left_front').val(),
                        "brake_right_front": $('#brake_right_front').val(),
                        "brake_left_rear": $('#brake_left_rear').val(),
                        "brake_right_rear": $('#brake_right_rear').val(),

                        "tread_lfo": $('#tread_lfo').val(),
                        "tread_lfi": $('#tread_lfi').val(),
                        "tread_rfi": $('#tread_rfi').val(),
                        "tread_rfo": $('#tread_rfo').val(),
                        "tread_lro": $('#tread_lro').val(),
                        "tread_lri": $('#tread_lri').val(),
                        "tread_rri": $('#tread_rri').val(),
                        "tread_rro": $('#tread_rro').val(),

                        "passed":  $('#passed').val()
                    },
                    error: function() {
                        alert('Error saving Trailer Inspection...');
                    },
                    success: function(xml) {

                        myres=parseInt($(xml).find('rslt').text());
                        if(myres==0 && parseInt($('#passed').val()) > 0)
                        {
                            $.prompt("<b>Error:</b> You must have all the B-PM (PMI) or C-PM (FED) items checked with Repair Code(s) selected to Pass the inspection(s).");
                            $('#passed').attr('checked','');
                        }
                        else
                        {
                            $.noticeAdd({text: "Maintenance Request Trailer Inspection has been saved."});
                        }
                    }
                });
            }
        }

        //update maintenance request listing for first load.
        $().ready(function() {

            display_maint(0, 0);
            //$('.list_mode').show();

            display_equipment_select_box( $('#equipment_type').val(), <?= $mrr_equip_select ?>);
            display_get_last_odometer_reading($('#equipment_type').val(),  <?= $mrr_equip_select ?>);

            <?php
            if($mrr_req_id > 0 )
            {
            //load line items on request.
            ?>
            display_maint_line_item( <?= $mrr_req_id ?> );
            //$('.list_mode').hide();
            <?
            }
            ?>
            if(current_req_id>0)
            {
                $('#request_new_items').show();
                $('#req_num').html(''+current_req_id+'');

                mrr_pull_accident_list();
            }

            mrr_load_truck_in_shop();

            <?php if($mrr_show_inspect == 0 ) { ?>
            mrr_inspection_display(0);
            <?php } ?>

            $('.tablesorter').tablesorter();
        });

        function mrr_load_truck_in_shop()
        {
            etype_id=parseInt($('#equipment_type').val());
            item=parseInt($('#equipment_xref_id').val());

            //alert("Type="+etype_id+", ID="+item+".");

            $('#mrr_truck_in_shop').html('');

            etyper=0;
            if(etype_id==58)		etyper=1;
            if(etype_id==59)		etyper=2;

            if(etyper>0 && item > 0)
            {
                $.ajax({
                    url: "ajax.php?cmd=mrr_truck_in_shop_switch",
                    type: "post",
                    dataType: "xml",
                    data: {
                        equipment_type: etyper,
                        equipment_xref_id: item
                    },
                    error: function() {
                        alert('general error getting Truck In Shop value.');
                    },
                    success: function(xml) {

                        myhtml=$(xml).find('mrrHTML').text();
                        $('#mrr_truck_in_shop').html(myhtml);
                    }
                });
            }
        }
        function mrr_toggle_in_the_shop(etyper,item,cur_status)
        {
            $.ajax({
                url: "ajax.php?cmd=mrr_truck_in_shop_switch_toggle",
                type: "post",
                dataType: "xml",
                data: {
                    equipment_type: etyper,
                    equipment_xref_id: item,
                    status_is:cur_status
                },
                error: function() {
                    alert('general error setting Truck In Shop value.');
                },
                success: function(xml) {
                    mrr_load_truck_in_shop();
                }
            });
        }

        function confirm_delete(id)
        {
            $.prompt("Are you sure you want to delete this maintenance request?", {
                buttons: {'Yes': true, 'No': false},
                submit: function(v, m, f) {
                    if(v) {
                        window.location = '<?=$SCRIPT_NAME?>?did=' + id;
                    }
                }
            });
        }
        function confirm_delete_item(id,linteitem)
        {
            $.prompt("Are you sure you want to delete this maintenance request line item?", {
                buttons: {'Yes': true, 'No': false},
                submit: function(v, m, f) {
                    if(v) {
                        window.location = '<?=$SCRIPT_NAME?>?id='+id+'&lid=' + linteitem;
                    }
                }
            });
        }

        function delete_single_line_item(xref_id,item)
        {
            $.ajax({
                url: "ajax.php?cmd=ajax_remove_one_maint_line_item",
                type: "post",
                dataType: "xml",
                data: {
                    //POST variables needed for "page" to load for XML output
                    maint_id: xref_id,
                    item_id: item
                },
                error: function() {
                    alert('Error updating Maint Request '+ xref_id +' Item '+ item +'.');
                },
                success: function(xml) {

                    $(xml).find('MaintRequestItem').each(function() {


                    });

                }
            });
            display_maint_line_item(xref_id);
        }

        function load_line_item_form(xref_id,item)
        {
            var request_id=xref_id;
            var lineitem=item;

            if(request_id==0 || lineitem==0)
            {
                $('#item_1_id').val( 0 );
                $('#item_1_label').val( ''  );
                $('#item_1_quant').val( '1'  );
                $('#item_1_hours').val( '0.00'  );
                $('#item_1_cost').val( '0.00'  );
                $('#item_1_maker').val( ''  );
                $('#item_1_model').val( ''  );
                $('#item_1_active').val( 1  );
                $('#item_1_req_cat').val( 0  );
                $('#item_1_pos_x').val( 0  );
                $('#item_1_pos_y').val( 0  );
                $('#item_1_pos_z').val( 0  );
                $('#item_1_pos_t').val( 0  );

                $('#item_1_label').focus();
            }
            else
            {
                $.ajax({
                    url: "ajax.php?cmd=ajax_get_single_line_item",
                    type: "post",
                    dataType: "xml",
                    data: {
                        //POST variables needed for "page" to load for XML output
                        maint_id: xref_id,
                        item_id: item
                    },
                    error: function() {
                        alert('Error listing requests for Maint Request '+ xref_id +' Item '+ item +'.');
                    },
                    success: function(xml) {

                        $(xml).find('MaintRequestItem').each(function() {

                            //var is_checked=$(this).find('RequestItemCat').text();
                            $('#item_1_id').val( $(this).find('RequestItemID').text() );
                            $('#item_1_label').val( $(this).find('RequestItemName').text()  );
                            $('#item_1_quant').val( $(this).find('RequestItemQuant').text()  );
                            $('#item_1_hours').val( $(this).find('RequestItemHours').text()  );
                            $('#item_1_cost').val( $(this).find('RequestItemUnit').text()  );
                            $('#item_1_maker').val( $(this).find('RequestItemMaker').text()  );
                            $('#item_1_model').val( $(this).find('RequestItemModel').text()  );
                            $('#item_1_active').attr('checked',($(this).find('RequestItemActive').text() == 1 ? 'checked' : ''));
                            $('#item_1_req_cat').val($(this).find('RequestItemCat').text());
                            $('#item_1_pos_x').val($(this).find('RequestItemFront').text());
                            $('#item_1_pos_y').val($(this).find('RequestItemLeft').text());
                            $('#item_1_pos_z').val($(this).find('RequestItemTop').text());
                            $('#item_1_pos_t').val($(this).find('RequestItemInside').text());
                            //$('#req_id').val( $(this).find('RequestItemRefer').text()  );
                        });
                    }
                });
            }
        }

        function display_get_last_odometer_reading(type_id, xref_id)
        {
            $.ajax({
                url: "ajax.php?cmd=ajax_get_last_odometer_reading",
                type: "post",
                dataType: "xml",
                data: {
                    //POST variables needed for "page" to load for XML output
                    //$('#equipment_type').val()
                    //$('#equipment_xref_id').val()
                    equipment_type: type_id,
                    equipment_xref_id: xref_id
                },
                error: function() {
                    alert('general error pulling odometer reading');
                },
                success: function(xml) {
                    $('#last_odometer_reading').html('');
                    var moder="N/A";

                    $(xml).find('LastOdometerReading').each(function() {

                        if(parseInt($(this).find('Mode').text())==1)		moder="Manual";
                        if(parseInt($(this).find('Mode').text())==2)		moder="GeoTab";

                        $('#last_odometer_reading').append("Last Odometer Reading was "+$(this).find('Odometer').text()+" on "+$(this).find('ReadingDate').text()+" ["+moder+"].");
                    });
                    //$.noticeAdd({text: "Success - Loaded last odometer reading"});
                }
            });
        }
        function display_equipment_select_box(type_id, xref_id)
        {
            $.ajax({
                url: "ajax.php?cmd=ajax_get_option_list",
                type: "post",
                dataType: "xml",
                async: false,
                data: {
                    //POST variables needed for "page" to load for XML output
                    //$('#equipment_type').val()
                    //$('#equipment_xref_id').val()
                    "show_deleted":0,
                    equipment_type: type_id,
                    equipment_xref_id: xref_id,
                    cur_mr_id: current_req_id
                },
                error: function() {
                    alert('general error pulling equipment list');
                },
                success: function(xml) {
                    $('#equipment_xref_id').html('');
                    $('#equipment_xref_id').append("<option value='0'>Select Equipment</option>");
                    $(xml).find('EquipmentEntry').each(function() {
                        if($(this).find('EquipmentID').text() == xref_id)
                        {
                            if($(this).find('EquipmentAct').text() == "1")
                            {
                                $('#equipment_xref_id').append("<option value='" + $(this).find('EquipmentID').text() + "' selected>" + $(this).find('EquipmentName').text() + "</option>");
                            }
                            else
                            {
                                $('#equipment_xref_id').append("<option value='" + $(this).find('EquipmentID').text() + "' selected style='color:#999999;'>" + $(this).find('EquipmentName').text() + " -- inactive</option>");
                            }
                            //$('#equipment_xref_id').append("<option value='"+$(this).find('EquipmentID').text()+"'>"+$(this).find('EquipmentName').text()+"</option>");
                        }
                        else
                        {
                            if($(this).find('EquipmentAct').text() == "1")
                            {
                                $('#equipment_xref_id').append("<option value='" + $(this).find('EquipmentID').text() + "'>" + $(this).find('EquipmentName').text() + "</option>");
                            }
                            else
                            {
                                $('#equipment_xref_id').append("<option value='" + $(this).find('EquipmentID').text() + "' style='color:#999999;'>" + $(this).find('EquipmentName').text() + " -- inactive</option>");
                            }
                        }
                    });
                    if(equip_pre_sel > 0)	$('#equipment_xref_id').val(equip_pre_sel);
                }
            });
        }

        function display_maint(type_id, xref_id)
        {
            $('#req_num').html(''+current_req_id+'');

            $.ajax({
                url: "ajax.php?cmd=ajax_maint_req_list",
                type: "post",
                dataType: "xml",
                data: {
                    req_equip_type: type_id,
                    req_equip_id: xref_id,
                    req_recur_flag: 0
                },
                error: function() {
                    alert('Error listing requests.');
                },
                success: function(xml) {
                    $('#auto_request_listing').html('');

                    var tempy=0;
                    var allRows = [];
                    var requestCount = 0;

                    // Collect all data first
                    $(xml).find('MaintRequest').each(function() {
                        requestCount++;

                        var rowData = {
                            urgent: "",
                            urgentSort: 0,
                            requestLink: $(this).find('RequestLink').text(),
                            requestID: $(this).find('RequestID').text(),
                            requestType: $(this).find('RequestType').text(),
                            requestName: $(this).find('RequestName').text(),
                            requestScheduled: $(this).find('RequestScheduled').text(),
                            requestCost: $(this).find('RequestCost').text(),
                            requestPM: $(this).find('RequestPM').text(),
                            requestFED: $(this).find('RequestFED').text(),
                            requestCreatedBY: $(this).find('RequestCreatedBY').text(),
                            requestCreatedON: $(this).find('RequestCreatedON').text(),
                            requestTrash: $(this).find('RequestTrash').text(),
                            requestUnitLog: $(this).find('RequestUnitLog').text(),
                            requestSnooze: parseInt($(this).find('RequestSnooze').text()),
                            showclass: "",
                            snoozer: ""
                        };

                        // Handle urgent/breakdown/safety indicators
                        tempy = $(this).find('RequestUrgent').text();
                        if(tempy == 1) {
                            rowData.urgent = "<span class='urgent_indicator' style='color:#CC0000;' title='Urgent Maintenance Request'>!!!</span>";
                            rowData.urgentSort = 2;
                        }

                        tempz = $(this).find('RequestBreakdown').text();
                        if(tempz == 1) {
                            rowData.urgent = "<span class='urgent_indicator' style='color:#CC0000;' title='Broken Down Unit'>BD!</span>";
                            rowData.urgentSort = 3;
                        }

                        tempx = $(this).find('SafetyShutDown').text();
                        if(tempx == 1) {
                            rowData.urgent = "<span class='urgent_indicator' style='color:#CC0000;' title='Safety Shut Down Unit'>SAFETY</span>";
                            rowData.showclass = " style='background-color:#FFEEEE !important;'";
                            rowData.urgentSort = 4;
                        }

                        if(rowData.urgent === "") {
                            rowData.urgent = "&nbsp;";
                            rowData.urgentSort = 1;
                        }

                        if(rowData.requestSnooze > 0) {
                            rowData.snoozer = " mr_snoozing";
                        }

                        allRows.push(rowData);
                    });

                    // Update count
                    $('#auto_request_listing_count').html(requestCount + " Active Maintenance Request(s)");

                    // Build the table
                    temp_holder = "";
                    temp_holder += "<table cellpadding='2' cellspacing='0' width='98%' border='1' class='maint_request_table table_grid' style='margin:4px'>";
                    temp_holder += "<thead>";
                    temp_holder += "<tr>";
                    temp_holder += "<th width='30' class='no-sort'></th>";
                    temp_holder += "<th onclick='sortMaintTable(1)' class='header'>Description</th>";
                    temp_holder += "<th onclick='sortMaintTable(2)' class='header'>Type</th>";
                    temp_holder += "<th onclick='sortMaintTable(3)' class='header'>Name</th>";
                    temp_holder += "<th onclick='sortMaintTable(4)' class='header'>Scheduled</th>";
                    temp_holder += "<th onclick='sortMaintTable(5)' class='list_mode header cost_column'>Cost</th>";
                    temp_holder += "<th class='no-sort'>Quick Note Or Location</th>";
                    temp_holder += "<th onclick='sortMaintTable(7)' class='list_mode header'>PM Date</th>";
                    temp_holder += "<th onclick='sortMaintTable(8)' class='list_mode header'>FED Date</th>";
                    temp_holder += "<th onclick='sortMaintTable(9)' class='header'>Created</th>";
                    temp_holder += "<th onclick='sortMaintTable(10)' class='header'>Added</th>";
                    temp_holder += "<th width='30' class='no-sort'></th>";
                    temp_holder += "</tr>";
                    temp_holder += "</thead>";
                    temp_holder += "<tbody id='maint_tbody'>";

                    // Add all rows
                    for(var i = 0; i < allRows.length; i++) {
                        var row = allRows[i];
                        var rowBgClass = (i % 2 === 0) ? ' row-even' : ' row-odd';

                        // Main data row
                        temp_holder += "<tr class='data_row" + row.snoozer + rowBgClass + "'" + row.showclass + ">";
                        temp_holder += "<td>" + row.urgent + "</td>";
                        temp_holder += "<td>" + row.requestLink + "</td>";
                        temp_holder += "<td><span id='cur_local_" + row.requestID + "_type'>" + row.requestType + "</span></td>";
                        temp_holder += "<td nowrap><span id='cur_local_" + row.requestID + "_unit'>" + row.requestName + "</span></td>";
                        temp_holder += "<td nowrap>" + row.requestScheduled + "</td>";
                        temp_holder += "<td class='list_mode cost_column'>" + row.requestCost + "</td>";
                        temp_holder += "<td><input type='text' id='cur_local_" + row.requestID + "' value=\"\" onBlur='mrr_update_mr_location_alt(" + row.requestID + ");' title='enter the current location of this unit.'></td>";
                        temp_holder += "<td class='list_mode' nowrap>" + row.requestPM + "</td>";
                        temp_holder += "<td class='list_mode' nowrap>" + row.requestFED + "</td>";
                        temp_holder += "<td nowrap>" + row.requestCreatedBY + "</td>";
                        temp_holder += "<td nowrap>" + row.requestCreatedON + "</td>";
                        temp_holder += "<td class='delete_column'>" + row.requestTrash + "</td>";
                        temp_holder += "</tr>";

                        // Unit log row - only add if there's content
                        var unitLogContent = row.requestUnitLog.trim();
                        if(unitLogContent && unitLogContent !== '') {
                            temp_holder += "<tr class='unit_log_row" + row.snoozer + rowBgClass + "'" + row.showclass + ">";
                            temp_holder += "<td colspan='12'><div class='unit_log_content'>" + unitLogContent + "</div></td>";
                            temp_holder += "</tr>";
                        }
                    }

                    temp_holder += "</tbody>";
                    temp_holder += "</table>";

                    $('#auto_request_listing').append(temp_holder);

                    // Store the original data for sorting
                    window.maintTableData = allRows;

                    if(current_req_id > 0) {
                        $('.list_mode').hide();
                    }

                    mrr_pull_accident_list();
                    mrr_filter_mr_snoozing();
                }
            });
        }

        // Custom sort function that keeps unit log rows with their parent
        window.maintTableSortColumn = 4; // Default sort by scheduled date
        window.maintTableSortDirection = 'desc';

        function sortMaintTable(columnIndex) {
            if (!window.maintTableData || window.maintTableData.length === 0) return;

            // Toggle sort direction if same column
            if (window.maintTableSortColumn === columnIndex) {
                window.maintTableSortDirection = (window.maintTableSortDirection === 'asc') ? 'desc' : 'asc';
            } else {
                window.maintTableSortColumn = columnIndex;
                window.maintTableSortDirection = 'asc';
            }

            // Update header classes
            $('.maint_request_table th').removeClass('headerSortUp headerSortDown');
            var $th = $('.maint_request_table th').eq(columnIndex);
            if (window.maintTableSortDirection === 'asc') {
                $th.addClass('headerSortUp');
            } else {
                $th.addClass('headerSortDown');
            }

            // Sort the data
            var sortedData = window.maintTableData.slice().sort(function(a, b) {
                var aVal, bVal;

                switch(columnIndex) {
                    case 1: // Description (extract text from link)
                        aVal = $(a.requestLink).text() || a.requestLink;
                        bVal = $(b.requestLink).text() || b.requestLink;
                        break;
                    case 2: // Type
                        aVal = a.requestType;
                        bVal = b.requestType;
                        break;
                    case 3: // Name
                        aVal = a.requestName;
                        bVal = b.requestName;
                        break;
                    case 4: // Scheduled date
                        aVal = new Date(a.requestScheduled || '01/01/1900');
                        bVal = new Date(b.requestScheduled || '01/01/1900');
                        break;
                    case 5: // Cost
                        aVal = parseFloat(a.requestCost.replace(/[$,]/g, '') || '0');
                        bVal = parseFloat(b.requestCost.replace(/[$,]/g, '') || '0');
                        break;
                    case 7: // PM Date
                        aVal = new Date(a.requestPM || '01/01/1900');
                        bVal = new Date(b.requestPM || '01/01/1900');
                        break;
                    case 8: // FED Date
                        aVal = new Date(a.requestFED || '01/01/1900');
                        bVal = new Date(b.requestFED || '01/01/1900');
                        break;
                    case 9: // Created
                        aVal = a.requestCreatedBY;
                        bVal = b.requestCreatedBY;
                        break;
                    case 10: // Added date
                        aVal = new Date(a.requestCreatedON || '01/01/1900');
                        bVal = new Date(b.requestCreatedON || '01/01/1900');
                        break;
                    default:
                        return 0;
                }

                if (aVal < bVal) return window.maintTableSortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return window.maintTableSortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            // Rebuild tbody with sorted data
            var tbody = '';
            for(var i = 0; i < sortedData.length; i++) {
                var row = sortedData[i];
                var rowBgClass = (i % 2 === 0) ? ' row-even' : ' row-odd';

                // Main data row
                tbody += "<tr class='data_row" + row.snoozer + rowBgClass + "'" + row.showclass + ">";
                tbody += "<td>" + row.urgent + "</td>";
                tbody += "<td>" + row.requestLink + "</td>";
                tbody += "<td><span id='cur_local_" + row.requestID + "_type'>" + row.requestType + "</span></td>";
                tbody += "<td nowrap><span id='cur_local_" + row.requestID + "_unit'>" + row.requestName + "</span></td>";
                tbody += "<td nowrap>" + row.requestScheduled + "</td>";
                tbody += "<td class='list_mode cost_column'>" + row.requestCost + "</td>";
                tbody += "<td><input type='text' id='cur_local_" + row.requestID + "' value=\"\" onBlur='mrr_update_mr_location_alt(" + row.requestID + ");' title='enter the current location of this unit.'></td>";
                tbody += "<td class='list_mode' nowrap>" + row.requestPM + "</td>";
                tbody += "<td class='list_mode' nowrap>" + row.requestFED + "</td>";
                tbody += "<td nowrap>" + row.requestCreatedBY + "</td>";
                tbody += "<td nowrap>" + row.requestCreatedON + "</td>";
                tbody += "<td class='delete_column'>" + row.requestTrash + "</td>";
                tbody += "</tr>";

                // Unit log row - only add if there's content
                var unitLogContent = row.requestUnitLog.trim();
                if(unitLogContent && unitLogContent !== '') {
                    tbody += "<tr class='unit_log_row" + row.snoozer + rowBgClass + "'" + row.showclass + ">";
                    tbody += "<td colspan='12'><div class='unit_log_content'>" + unitLogContent + "</div></td>";
                    tbody += "</tr>";
                }
            }

            $('#maint_tbody').html(tbody);

            // Reapply any visibility filters
            if(current_req_id > 0) {
                $('.list_mode').hide();
            }
            mrr_filter_mr_snoozing();
        }

        function mrr_snooze_toggle()
        {
            if(mrr_display_snooze > 0)
            {
                mrr_display_snooze = 0;
                $.noticeAdd({text: "Maint Request ...Hiding MR's on Snooze."});
            }
            else
            {
                mrr_display_snooze = 1;
                $.noticeAdd({text: "Maint Request ...Showing MR's on Snooze."});
            }
            mrr_filter_mr_snoozing();
        }

        function mrr_filter_mr_snoozing()
        {
            if(mrr_display_snooze > 0)
            {
                $('.mr_snoozing').show();
            }
            else
            {
                $('.mr_snoozing').hide();
            }
        }


        function delete_unit_log_history(id) {
            $.prompt("Are you sure you want to delete this MR Quick note and loc entry?", {
                    buttons: {Yes: true, No:false},
                    submit: function(v, m, f) {
                        if(v) {

                            $.ajax({
                                url: "ajax.php?cmd=ajax_remove_mr_quick_note_entry",
                                type: "post",
                                dataType: "xml",
                                data: {
                                    //POST variables needed for "page" to load for XML output
                                    id: id
                                },
                                error: function() {
                                    alert('Error removing Quick note and loc.');
                                },
                                success: function(xml) {
                                    $('.mrr_unit_log_row_'+id).hide();
                                }
                            });
                        }
                    }
                }
            );
        }
        function display_maint_line_item(xref_id)
        {
            $.ajax({
                url: "ajax.php?cmd=ajax_make_line_item_list",
                type: "post",
                dataType: "xml",
                data: {
                    //POST variables needed for "page" to load for XML output
                    maint_id: xref_id
                },
                error: function() {
                    alert('Error listing requests for Maint Request '+ xref_id +'.');
                },
                success: function(xml) {
                    $('#auto_maint_line_item_listing').html('');
                    txt = "<table cellpadding='2' cellspacing='0' width='98%' border='1' class='table_grid' style='margin:4px'>";
                    txt += "<tr>";
                    txt += "<td valign='top' width='165'>&nbsp;<b>Line Items</b></td>";	//
                    txt += "<td valign='top' width='80'><b>Category</b></td>";
                    //txt += "<td valign='top'><b>Name</b></td>";
                    txt += "<td valign='top' width='100'><b>Vendor</b></td>";
                    txt += "<td valign='top' width='150'><b>Part</b></td>";
                    txt += "<td valign='top' width='150'><b>Location Markers</b></td>";
                    txt += "<td valign='top' width='50' align='right'><b>Hours</b></td>";
                    txt += "<td valign='top' width='50' align='right'><b>Quant</b></td>";
                    txt += "<td valign='top' width='50' align='right'><b>Unit</b></td>";
                    txt += "<td valign='top' width='80' align='right'><b>Cost</b></td>";
                    txt += "<td valign='top' width='25'><b></b></td>";
                    txt += "</tr>";

                    $('#down_time_hours').val($(xml).find('TotItemHours').text());
                    $('#cost_estimate').val($(xml).find('TotItemCost').text());

                    $(xml).find('MaintRequestItem').each(function() {
                        txt += "<tr>";
                        txt += "<td valign='top'>&nbsp;"+$(this).find('RequestItemLink').text()+"</td>";
                        txt += "<td valign='top'>"+$(this).find('RequestItemCat').text()+"</td>";
                        //txt += "td valign='top'>"+$(this).find('RequestItemName').text()+"</td>";
                        txt += "<td valign='top'>"+$(this).find('RequestItemMaker').text()+"</td>";
                        txt += "<td valign='top'>"+$(this).find('RequestItemModel').text()+"</td>";
                        txt += "<td valign='top'>"+$(this).find('RequestItemFront').text()+" "+
                            $(this).find('RequestItemLeft').text()+" "+
                            $(this).find('RequestItemInside').text()+" "+
                            $(this).find('RequestItemTop').text()+"</td>";
                        txt += "<td valign='top' align='right'>"+$(this).find('RequestItemHours').text()+"</td>";
                        txt += "<td valign='top' align='right'>"+$(this).find('RequestItemQuant').text()+"</td>";
                        txt += "<td valign='top' align='right'>"+$(this).find('RequestItemUnit').text()+"</td>";
                        txt += "<td valign='top' align='right'>"+$(this).find('RequestItemCost').text()+"</td>";
                        txt += "<td valign='top'>&nbsp;"+$(this).find('RequestItemTrash').text()+"</td>";
                        txt += "</tr>";

                    });
                    txt += "</table>";
                    $('#auto_maint_line_item_listing').append(txt);
                }
            });
        }

        function mrr_simple_show()
        {
            $('.no_req_print').show();
            //window.print();
        }
        function mrr_simple_print()
        {
            $('.no_req_print').hide();
            window.print();
        }
        function mrr_simple_email()
        {
            email_to=$('#email_to').val();
            if(email_to!="")
            {
                $('.no_req_print').hide();
                //window.print();

                html_body1=$('.print_req').html();
                html_body2=$('.print_req_items').html();

                html_body=""+html_body1+"<br><br>"+html_body2+"";

                $.ajax({
                    url: "ajax.php?cmd=mrr_email_this_thing",
                    type: "post",
                    dataType: "xml",
                    data: {
                        //POST variables needed for "page" to load for XML output
                        "xref_id": current_req_id,
                        "email_address": email_to,
                        "html_body": html_body,
                        "subject": "Conard Transportation Maintenance Request "+current_req_id+""
                    },
                    error: function() {
                        alert('Error sending email for Maint Request '+ xref_id +'.');
                    },
                    success: function(xml) {
                        sentto=$(xml).find('MRRSendAddress').text()
                        if(sentto!="")
                        {
                            $.prompt("Request E-Mail sent to Address "+sentto+".");
                        }
                        else
                        {
                            $.prompt("Request E-Mail was not sent to Address "+sentto+". :(");
                        }
                        mrr_simple_show();
                    }
                });
            }
            else
            {
                $.prompt("Please enter an E-Mail Address to send this Request.");
                mrr_simple_show();
                $('#email_to').focus();
            }
        }
    </script>

    <style>
        /* Maintenance Request Table Styling */
        .maint_request_table {
            border-collapse: collapse;
            width: 98%;
            margin: 4px;
            font-family: Arial, sans-serif;
        }

        .maint_request_table thead tr {
            background-color: #e4eaff;
            border-bottom: 2px solid #cccccc;
        }

        .maint_request_table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            cursor: pointer;
            position: relative;
            white-space: nowrap;
            border: 1px solid #cccccc;
        }

        .maint_request_table th.header {
            background-image: url('data:image/gif;base64,R0lGODlhFQAJAIAAACMtMP///yH5BAEAAAEALAAAAAAVAAkAAAIXjI+AywnaYnhUMoqt3gZXPmVg94yJVQAAOw==');
            background-repeat: no-repeat;
            background-position: center right;
            padding-right: 20px;
        }

        .maint_request_table th.headerSortUp {
            background-image: url('data:image/gif;base64,R0lGODlhFQAEAIAAACMtMP///yH5BAEAAAEALAAAAAAVAAQAAAINjI8Bya2wnINUMopZAQA7');
            background-repeat: no-repeat;
            background-position: center right;
            padding-right: 20px;
        }

        .maint_request_table th.headerSortDown {
            background-image: url('data:image/gif;base64,R0lGODlhFQAEAIAAACMtMP///yH5BAEAAAEALAAAAAAVAAQAAAINjB+gC+jP2ptn0WskLQA7');
            background-repeat: no-repeat;
            background-position: center right;
            padding-right: 20px;
        }

        .maint_request_table td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            font-size: 12px;
        }

        /* Main data rows - striped */
        /* Removed - handled differently now */

        /* Hover effect only on data rows */
        .maint_request_table tbody tr.data_row:hover {
            background-color: #f0f4ff !important;
        }

        /* Unit log rows should match their parent row color */
        .maint_request_table tbody tr.unit_log_row {
            border-top: none;
        }

        .maint_request_table tbody tr.unit_log_row td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        /* Special row styling */
        .maint_request_table .mr_snoozing {
            opacity: 0.7;
        }

        /* Urgent/Breakdown/Safety indicators */
        .maint_request_table .urgent_indicator {
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 3px;
        }

        /* Cost column right alignment */
        .maint_request_table .cost_column {
            text-align: right;
        }

        /* Quick note input styling */
        .maint_request_table input[type="text"] {
            width: 90%;
            padding: 4px 6px;
            border: 1px solid #ccc;
            font-size: 11px;
        }

        /* No sort columns */
        .maint_request_table th.no-sort {
            cursor: default;
        }

        /* Nested table in unit log rows */
        .maint_request_table .unit_log_content {
            padding: 8px;
            padding-left: 40px;
            display: block;
        }

        .maint_request_table .unit_log_content table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
            margin: 0;
        }

        .maint_request_table .unit_log_content table td,
        .maint_request_table .unit_log_content table th {
            padding: 10px 8px !important;
            border: 1px solid #ddd !important;
            text-align: left;
            font-size: 11px;
        }

        .maint_request_table .unit_log_content table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        /* Also style tables with class table_grid inside unit logs */
        .maint_request_table .unit_log_content .table_grid {
            width: 100%;
            border: 1px solid #cccccc;
            border-collapse: collapse;
        }

        .maint_request_table .unit_log_content .table_grid td,
        .maint_request_table .unit_log_content .table_grid th {
            padding: 10px 8px !important;
            border: 1px solid #ddd !important;
        }

        /* Center delete icons */
        .maint_request_table td.delete_column {
            text-align: center;
        }

        /* Hide empty unit log rows */
        .maint_request_table tr.unit_log_row.empty_log {
            display: none;
        }
    </style>

<?php include('footer.php');