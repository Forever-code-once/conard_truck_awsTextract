<? include('application.php') ?>
<?
$admin_page = 1;
$usetitle = "Driver Load Planning";
$use_title = "Driver Load Planning";

?>
<? include('header.php') ?>
<?
	if(!isset($_POST['date_from'])) 	$_POST['date_from']=date("m/d/Y");
	//if(!isset($_POST['date_to'])) 	$_POST['date_to']=$_POST['date_from'];
	
	$offset_days=-7;	//last week
	$miles_per_hour=60;
	$max_hours_allowed=40;
	
	$dres1=get_week_dates_for_date($_POST['date_from']);
	$date_from=$dres1['date_from'];
	$date_to=$dres1['date_to'];		//$_POST['date_to']=$date_to;
	
	$driver_pool="";
	$dres=get_planning_driver_pool();
	$cntr=$dres['num'];
	$arr=$dres['arr'];
	$label=$dres['lab'];
	$phone=$dres['phone'];
	$truck=$dres['trucks'];
	$tname=$dres['truck_names'];
	//$trail=$dres['trailers'];
	//$tname2=$dres['trailer_names'];
	
	$driver_pool.="
		<table cellpadding='1' cellspacing='1' border='0'>
		<tr>
			<td valign='top'><b>Driver</b></td>
			<td valign='top'><b>Cell</b></td>
			<td valign='top'><b>Truck</b></td>			
			<td valign='top' align='right'><b>Hours<br>Last<br>Week</b></td>
			<td valign='top' align='right'><b>Dispatch<br>Hours</b></td>
			<td valign='top' align='right'><b>Preplan<br>Hours</b></td>
		</tr>
	";//<td valign='top'><b>Trailer</b></td>
	$driver_pool.="";
	$dcntr=0;
	for($i=0;$i < $cntr;$i++)
	{
		$driver_id=$arr[$i];
		$approx_hours=0;
		$approx_hours2=0;
		$preplan_hours=0;
		$approx_hours=get_planning_driver_approximate_hours_by_dispatch($driver_id,$date_from,$date_to,$offset_days,$miles_per_hour);
		$approx_hours2=get_planning_driver_approximate_hours_by_dispatch($driver_id,$date_from,$date_to,0,$miles_per_hour);
		$preplan_hours=get_planning_driver_approximate_hours_by_preplan($driver_id,$date_from,$date_to,0,$miles_per_hour);
		
		$tag1="";
		$tag2="";
		if(($approx_hours2 + $preplan_hours) > $max_hours_allowed)
		{
			$tag1="<span class='alert' title='Driver has more than ".$max_hours_allowed." hours scheduled (Dispatch Hours + Preplan Hours)...this may be an error.'><b>";
			$tag2="</b></span>";	
		}
		
		$driver_link="
			<span class='mrr_link_like_on' title='driver ".$driver_id."' onClick='mrr_driver_click(".$driver_id.",\"".$label[$i]."\",\"".$phone[$i]."\",".round($approx_hours2,2).",".round($preplan_hours,2).",".round(($approx_hours2+$preplan_hours),2).");'>
				".$label[$i]."
			</span>
		";
		
		//Check DOT violations...
		$dres=mrr_find_driver_dot_hrs_for_planning_mrr($driver_id,$date_from,$date_to);		
		if($dres['violation_70_hr'] > 0)
		{
			$driver_link="
				<span class='mrr_link_like_on' style='color:purple;' title='driver ".$driver_id." over 70 hours for week...must rest 34 hours.'>
					".$label[$i]."
				</span>
			";
		}
		elseif($dres['violation_l4_hr'] > 0)
		{
			$driver_link="
				<span class='mrr_link_like_on' style='color:orange;' title='driver ".$driver_id." worked over 14 hours without 10-hour break.' onClick='mrr_driver_click(".$driver_id.",\"".$label[$i]."\",\"".$phone[$i]."\",".round($approx_hours2,2).",".round($preplan_hours,2).",".round(($approx_hours2+$preplan_hours),2).");'>
					".$label[$i]."
				</span>
			";
		}
		elseif($dres['violation_ll_hr'] > 0)
		{
			$driver_link="
				<span class='mrr_link_like_on' style='color:red;' title='driver ".$driver_id." drove over 11 hours without 10-hour break.' onClick='mrr_driver_click(".$driver_id.",\"".$label[$i]."\",\"".$phone[$i]."\",".round($approx_hours2,2).",".round($preplan_hours,2).",".round(($approx_hours2+$preplan_hours),2).");'>
					".$label[$i]."
				</span>
			";
		}
		else
		{
			$driver_link="
				<span class='mrr_link_like_on' title='driver ".$driver_id."' onClick='mrr_driver_click(".$driver_id.",\"".$label[$i]."\",\"".$phone[$i]."\",".round($approx_hours2,2).",".round($preplan_hours,2).",".round(($approx_hours2+$preplan_hours),2).");'>
					".$label[$i]."
				</span>
			";
		}
		
		
		$driver_pool.="
			<tr class='".($dcntr%2==0 ? "even" : "odd")."'>
				<td valign='top'>".$driver_link."</td>
				<td valign='top'>".$phone[$i]."</td>
				<td valign='top'>".$tname[$i]."</td>				
				<td valign='top' align='right'>".number_format($approx_hours,2)."</td>
				<td valign='top' align='right'>".number_format($approx_hours2,2)."</td>
				<td valign='top' align='right'>".$tag1."".number_format($preplan_hours,2)."".$tag2."</td>
			</tr>
		";//<td valign='top'>".$tname2[$i]."</td>
		
		$dcntr++;
	}
	$driver_pool.="</table>";


?>
<form action="<?=$SCRIPT_NAME?>" method="post">
<div class='planning_driver_pallet'>
	<table width='100%'>
	<tr>
		<td valign='top' align='center' colspan='6'><b>Preplan Pad</b></td>			
	</tr>
	<tr>
		<td valign='top'>Selected</td>
		<td valign='top' align='right'>Load</td>
		<td valign='top' align='right'><b><span id='show_load_name'></span></b></td>
		<td valign='top' align='right' colspan='3'><span class='mrr_link_like_on' onClick='mrr_clear_pallet();'><b>Clear Pad</b></span></td>
	</tr>
	<tr>
		<td valign='top'>Selected</td>
		<td valign='top' align='right'>Driver</td>
		<td valign='top' align='right'>Driver2</td>
		<td valign='top' align='right'>Leg 2</td>
		<td valign='top' align='right'>Driver1</td>
		<td valign='top' align='right'>Driver2</td>
	</tr>	
	<tr>
		<td valign='top'>&nbsp;</td>
		<td valign='top' align='right'><b><span id='show_driver_name'></span></b></td>
		<td valign='top' align='right'><b><span id='show_driver2_name'></span></b></td>
		<td valign='top'>&nbsp;</td>
		<td valign='top' align='right'><b><span id='show_driver3_name'></span></b></td>
		<td valign='top' align='right'><b><span id='show_driver4_name'></span></b></td>
	</tr>
	<tr>
		<td valign='top'>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' colspan='6' align='center'><b>Approximate Hours</b></td>
	</tr>
	<tr>
		<td valign='top'><b>Dispatched</b></td>
		<td valign='top' align='right'><b><span id='show_disp_hrs'>0.00</span></b></td>
		<td valign='top' align='right'><b><span id='show_disp_hrs2'>0.00</span></b></td>
		<td valign='top'>&nbsp;</td>
		<td valign='top' align='right'><b><span id='show_disp_hrs3'>0.00</span></b></td>
		<td valign='top' align='right'><b><span id='show_disp_hrs4'>0.00</span></b></td>
	</tr>
	<tr>
		<td valign='top'>Preplanned</td>
		<td valign='top' align='right'><b><span id='show_preplan_hrs'>0.00</span></b></td>
		<td valign='top' align='right'><b><span id='show_preplan_hrs2'>0.00</span></b></td>
		<td valign='top'>&nbsp;</td>
		<td valign='top' align='right'><b><span id='show_preplan_hrs3'>0.00</span></b></td>
		<td valign='top' align='right'><b><span id='show_preplan_hrs4'>0.00</span></b></td>
	</tr>
	<tr>
		<td valign='top' colspan='6' align='right'><hr></td>
	</tr>
	<tr>
		<td valign='top'>Planned</td>
		<td valign='top' align='right'><b><span id='show_tot_hrs'>0.00</span></b></td>
		<td valign='top' align='right'><b><span id='show_tot_hrs2'>0.00</span></b></td>
		<td valign='top'>&nbsp;</td>
		<td valign='top' align='right'><b><span id='show_tot_hrs3'>0.00</span></b></td>
		<td valign='top' align='right'><b><span id='show_tot_hrs4'>0.00</span></b></td>
	</tr>	
	<tr>
		<td valign='top'>Warnings</td>
		<td valign='top' align='right'><b><span id='driver_warning'></span></b></td>
		<td valign='top' align='right'><b><span id='driver_warning2'></span></b></td>
		<td valign='top'>&nbsp;</td>
		<td valign='top' align='right'><b><span id='driver_warning3'></span></b></td>
		<td valign='top' align='right'><b><span id='driver_warning4'></span></b></td>
	</tr>
	</table>
	
	
</div>	
<table class='admin_menu1' style='text-align:left;'>
<tr>
	<td valign='top'>
		<font class='standard18'><b><?= $usetitle ?></b></font>		
	</td>
	<td valign='top' align='left'>
		Week of <input name="date_from" id='date_from' value="<?= $_POST['date_from'] ?>"> 
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (mm/dd/yyyy) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
		<input type='submit' name="daily_plan" id='daily_plan' value="View/Refresh Dates and Hours">	
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		
		...Date Range is <?= $date_from ?> thru <?= $date_to ?>.				
	</td>	
	<!--
	<td valign='top'>
		Date To <input name="date_to" id='date_to' value="<?= $_POST['date_to'] ?>">	(mm/dd/yyyy)				
	</td>	
	-->
	<td valign='top' align='right'>
		<span class='mrr_link_like_on' onClick='mrr_toggle_info(1);'><b>Show</b></span> 
		or 
		<span class='mrr_link_like_on' onClick='mrr_toggle_info(0);'><b>Hide</b></span> 
		Detail
	</td>
</tr>
<tr>
	<td valign='top'>
		<?= $driver_pool ?>
	</td>
	<td valign='top' colspan='2'>
		<input type='hidden' name="load_id" id='load_id' value="0">
		
		<input type='hidden' name="driver_id" id='driver_id' value="0">
		<input type='hidden' name="driver_name" id='driver_name' value="">
		<input type='hidden' name="driver_phone" id='driver_phone' value="">	
		
		<input type='hidden' name="driver2_id" id='driver2_id' value="0">
		<input type='hidden' name="driver2_name" id='driver2_name' value="">
		<input type='hidden' name="driver2_phone" id='driver2_phone' value="">	
		
		<input type='hidden' name="driver3_id" id='driver3_id' value="0">
		<input type='hidden' name="driver3_name" id='driver3_name' value="">
		<input type='hidden' name="driver3_phone" id='driver3_phone' value="">	
		
		<input type='hidden' name="driver4_id" id='driver4_id' value="0">
		<input type='hidden' name="driver4_name" id='driver4_name' value="">
		<input type='hidden' name="driver4_phone" id='driver4_phone' value="">		
			
		<table cellpadding='0' cellspacing='0' border='1'>
		<tr style='background-color:#0000CC; color:#FFFFFF; font-weight:bold;'>
			<td valign='top' class='mrr_day_0' align='center' width='200'>Sun</td>
			<td valign='top' class='mrr_day_1' align='center' width='200'>Mon</td>
			<td valign='top' class='mrr_day_2' align='center' width='200'>Tue</td>
			<td valign='top' class='mrr_day_3' align='center' width='200'>Wed</td>
			<td valign='top' class='mrr_day_4' align='center' width='200'>Thu</td>
			<td valign='top' class='mrr_day_5' align='center' width='200'>Fri</td>
			<td valign='top' class='mrr_day_6' align='center' width='200'>Sat</td>		
		</tr>
		<tr style='background-color:#0000CC; color:#FFFFFF; font-weight:bold;'>
			<td valign='top' class='mrr_day_0' align='center'><?= date("m/d/Y",strtotime($date_from)) ?></td>
			<td valign='top' class='mrr_day_1' align='center'><?= date("m/d/Y",strtotime("+1 day",strtotime($date_from))) ?></td>
			<td valign='top' class='mrr_day_2' align='center'><?= date("m/d/Y",strtotime("+2 day",strtotime($date_from))) ?></td>
			<td valign='top' class='mrr_day_3' align='center'><?= date("m/d/Y",strtotime("+3 day",strtotime($date_from))) ?></td>
			<td valign='top' class='mrr_day_4' align='center'><?= date("m/d/Y",strtotime("+4 day",strtotime($date_from))) ?></td>
			<td valign='top' class='mrr_day_5' align='center'><?= date("m/d/Y",strtotime("+5 day",strtotime($date_from))) ?></td>
			<td valign='top' class='mrr_day_6' align='center'><?= date("m/d/Y",strtotime($date_to)) ?></td>
		</tr>
		<tr>
			<td valign='top' class='mrr_day_0'>
				<?
					$lres=get_planning_loads_for_this_date(0, date("m/d/Y",strtotime($date_from)) ,$miles_per_hour);
					$sunday_count=$lres['num'];
					$sunday_list=$lres['loads'];
					
					echo $sunday_list;
				?>
			</td>
			<td valign='top' class='mrr_day_1'>
				<?
					$lres=get_planning_loads_for_this_date(1, date("m/d/Y",strtotime("+1 day",strtotime($date_from))) ,$miles_per_hour);
					$monday_count=$lres['num'];
					$monday_list=$lres['loads'];
					
					echo $monday_list;
				?>
			</td>
			<td valign='top' class='mrr_day_2'>
				<?
					$lres=get_planning_loads_for_this_date(2, date("m/d/Y",strtotime("+2 day",strtotime($date_from))) ,$miles_per_hour);
					$tuesday_count=$lres['num'];
					$tuesday_list=$lres['loads'];
					
					echo $tuesday_list;
				?>
			</td>
			<td valign='top' class='mrr_day_3'>
				<?
					$lres=get_planning_loads_for_this_date(3, date("m/d/Y",strtotime("+3 day",strtotime($date_from))) ,$miles_per_hour);
					$wednesday_count=$lres['num'];
					$wednesday_list=$lres['loads'];
					
					echo $wednesday_list;
				?>
			</td>
			<td valign='top' class='mrr_day_4'>
				<?
					$lres=get_planning_loads_for_this_date(4, date("m/d/Y",strtotime("+4 day",strtotime($date_from))) ,$miles_per_hour);
					$thursday_count=$lres['num'];
					$thursday_list=$lres['loads'];
					
					echo $thursday_list;
				?>
			</td>
			<td valign='top' class='mrr_day_5'>
				<?
					$lres=get_planning_loads_for_this_date(5, date("m/d/Y",strtotime("+5 day",strtotime($date_from))) ,$miles_per_hour);
					$friday_count=$lres['num'];
					$friday_list=$lres['loads'];
					
					echo $friday_list;
				?>
			</td>
			<td valign='top' class='mrr_day_6'>
				<?
					$lres=get_planning_loads_for_this_date(6, date("m/d/Y",strtotime($date_to)) ,$miles_per_hour);
					$saturday_count=$lres['num'];
					$saturday_list=$lres['loads'];
					
					echo $saturday_list;
				?>
			</td>			
		</tr>		
		</table>		
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>
	$('#date_from').datepicker();	
	//$('#date_to').datepicker();	
	
	var mrr_date_from="<?= $date_from ?>";
	var mrr_date_to="<?= $date_to ?>";
	
	$().ready(function() 
	{
		mrr_toggle_info(0);
		
		<? if($sunday_count==0) { ?>
			$('.mrr_day_0').hide();	
		<? } ?>
		<? if($monday_count==0) { ?>
			$('.mrr_day_1').hide();	
		<? } ?>
		<? if($tuesday_count==0) { ?>
			$('.mrr_day_2').hide();	
		<? } ?>
		<? if($wednesday_count==0) { ?>
			$('.mrr_day_3').hide();	
		<? } ?>
		<? if($thursday_count==0) { ?>
			$('.mrr_day_4').hide();	
		<? } ?>
		<? if($friday_count==0) { ?>
			$('.mrr_day_5').hide();	
		<? } ?>
		<? if($saturday_count==0) { ?>
			$('.mrr_day_6').hide();	
		<? } ?>
	});
	
	function mrr_driver_click(id,drivername,driverphone,disp_hrs,pre_hrs,tot_hrs)
	{
		set_driver=0;
		
		if($('#driver_id').val() > 0 && $('#driver2_id').val()> 0 && $('#driver3_id').val()> 0 && $('#driver4_id').val()> 0)
		{
			$('#driver_id').val('0');
			$('#driver2_id').val('0');
			$('#driver3_id').val('0');
			$('#driver4_id').val('0');
		}
		
		
		if($('#driver_id').val()==0)
		{		
			$('#driver_id').val(id);
			$('#driver_name').val(drivername);
			$('#driver_phone').val(driverphone);
			
			$('#show_driver_name').html(drivername);
		
			$('#show_disp_hrs').html(mrrformatNumber(disp_hrs));
			$('#show_preplan_hrs').html(mrrformatNumber(pre_hrs));
			$('#show_tot_hrs').html(mrrformatNumber(tot_hrs));
			set_driver=1;
		}
		if($('#driver2_id').val()==0 && set_driver==0)
		{		
			$('#driver2_id').val(id);
			$('#driver2_name').val(drivername);
			$('#driver2_phone').val(driverphone);
						
			$('#show_driver2_name').html(drivername);
		
			$('#show_disp_hrs2').html(mrrformatNumber(disp_hrs));
			$('#show_preplan_hrs2').html(mrrformatNumber(pre_hrs));
			$('#show_tot_hrs2').html(mrrformatNumber(tot_hrs));
			set_driver=2;
		}
		if($('#driver3_id').val()==0 && set_driver==0)
		{		
			$('#driver3_id').val(id);
			$('#driver3_name').val(drivername);
			$('#driver3_phone').val(driverphone);
			
			$('#show_driver3_name').html(drivername);
		
			$('#show_disp_hrs3').html(mrrformatNumber(disp_hrs));
			$('#show_preplan_hrs3').html(mrrformatNumber(pre_hrs));
			$('#show_tot_hrs3').html(mrrformatNumber(tot_hrs));
			set_driver=3;
		}
		if($('#driver4_id').val()==0 && set_driver==0)
		{		
			$('#driver4_id').val(id);
			$('#driver4_name').val(drivername);
			$('#driver4_phone').val(driverphone);
			
			$('#show_driver4_name').html(drivername);
		
			$('#show_disp_hrs4').html(mrrformatNumber(disp_hrs));
			$('#show_preplan_hrs4').html(mrrformatNumber(pre_hrs));
			$('#show_tot_hrs4').html(mrrformatNumber(tot_hrs));
			set_driver=4;
		}
		
		if($('#driver3_id').val() > 0 && $('#driver3_id').val()==$('#driver2_id').val())
		{
			$('#driver2_id').val(0);
			$('#driver2_name').val('');
			$('#driver2_phone').val('');
						
			$('#show_driver2_name').html('');
		
			$('#show_disp_hrs2').html('0.00');
			$('#show_preplan_hrs2').html('0.00');
			$('#show_tot_hrs2').html('0.00');
			
		}
		
		loadid=$('#load_id').val();		
		$('#show_load_name').html(loadid);
				
		mrr_preplan_set(loadid,set_driver,id,drivername,driverphone);	
	}
	function mrr_load_click(id)
	{
		$('#load_id').val(id);
		$('#show_load_name').html(id);
				
		driverid=$('#driver_id').val();
		drivername=$('#driver_name').val();
		driverphone=$('#driver_phone').val();		
		
		$('#show_driver_name').html(drivername);
		
		driverid2=$('#driver2_id').val();
		drivername2=$('#driver2_name').val();
		driverphone2=$('#driver2_phone').val();		
		
		$('#show_driver2_name').html(drivername2);
		
		driverid3=$('#driver3_id').val();
		drivername3=$('#driver3_name').val();
		driverphone3=$('#driver3_phone').val();		
		
		$('#show_driver3_name').html(drivername3);
		
		driverid4=$('#driver4_id').val();
		drivername4=$('#driver4_name').val();
		driverphone4=$('#driver4_phone').val();		
		
		$('#show_driver4_name').html(drivername4);
		
		mrr_preplan_set(id,1,driverid,drivername,driverphone);	
		mrr_preplan_set(id,2,driverid2,drivername2,driverphone2);	
		mrr_preplan_set(id,3,driverid3,drivername3,driverphone3);	
		mrr_preplan_set(id,4,driverid4,drivername4,driverphone4);	
		
		//mrr_preplan_set(id,driverid,drivername,driverphone);	
		
		mrr_clear_pallet();		
	}
	
	function mrr_preplan_set(loadid,dnum,driverid,drivername,driverphone)
	{
		if(loadid > 0 && driverid > 0)
		{
     		//alert("Load "+loadid+" Clicked: Driver ["+driverid+"] "+drivername+" ("+driverphone+").");
     			
     		$.ajax({
     			url: "ajax.php?cmd=mrr_preplan_auto_set_driver_for_load",
     			data: {
     					"load_id" : loadid,
     					"driver_id": driverid,
     					"driver_num":dnum 					
     				},
     			type: "POST",
     			cache:false,
     			async:false,
     			dataType: "xml",
     			success: function(xml) {
     				if($(xml).find('rslt').text() == "1") 
     				{
     					$.noticeAdd({text: "Success - Preplanned Driver has been set to "+drivername+" for Load "+loadid+"."});	
     					
     					if(dnum==1)
     					{
     						$('#preplan_driver_'+loadid+'').html(drivername);
							$('#preplan_phone_'+loadid+'').html(driverphone);		
     					}
     					if(dnum==2)
     					{
     						$('#preplan_driver2_'+loadid+'').html(drivername);
							$('#preplan_phone2_'+loadid+'').html(driverphone);		
     					}
     					if(dnum==3)
     					{
     						$('#preplan_driver3_'+loadid+'').html(drivername);
							$('#preplan_phone3_'+loadid+'').html(driverphone);		
     					}
     					if(dnum==4)
     					{
     						$('#preplan_driver4_'+loadid+'').html(drivername);
							$('#preplan_phone4_'+loadid+'').html(driverphone);		
     					}
     					if(dnum==0)
     					{
     						$('#preplan_driver_'+loadid+'').html('');
							$('#preplan_phone_'+loadid+'').html('');	
							$('#preplan_driver2_'+loadid+'').html('');
							$('#preplan_phone2_'+loadid+'').html('');
							$('#preplan_driver3_'+loadid+'').html('');
							$('#preplan_phone3_'+loadid+'').html('');
							$('#preplan_driver4_'+loadid+'').html('');
							$('#preplan_phone4_'+loadid+'').html('');								
     					}    								
     				}
     				else
     				{					
     					$.prompt("Error: Driver Planning could not be updated.");		
     				}
     				//mrr_clear_pallet();					
     			}
          	});
     	}
     	
     	if(loadid == 0 && driverid > 0)
     	{
     		if(dnum==1)
     		{
     			$('#driver_warning').html('Loading...');		
     			disp_hrs=get_amount($('#show_disp_hrs').html() );
				plan_hrs=get_amount($('#show_preplan_hrs').html() );
     		}
     		else
     		{
     			$('#driver_warning'+dnum+'').html('Loading...');					
				disp_hrs=get_amount( $('#show_disp_hrs'+dnum+'').html() );
				plan_hrs=get_amount( $('#show_preplan_hrs'+dnum+'').html() );
     		}
     		
     		$.ajax({
     			url: "ajax.php?cmd=mrr_get_driver_dot_info_for_load_planning",
     			data: {
     					"date_from" : mrr_date_from,
     					"date_to" : mrr_date_to,
     					"hrs_planned" : plan_hrs,
     					"hrs_dispatched" : disp_hrs,
     					"driver_id": driverid				
     				},
     			type: "POST",
     			cache:false,
     			async:false,
     			dataType: "xml",
     			success: function(xml) {
     				if($(xml).find('rslt').text() == "1") 
     				{
     					warn=$(xml).find('warnings').text();
     					if(dnum==1)
     					{
     						$('#driver_warning').html(warn);
     					}
     					else
     					{
     						$('#driver_warning'+dnum+'').html(warn);
     					}
     				}
     				else
     				{					
     					//$.prompt("Error: Driver Planning could not find DOT info.");		
     				}			
     			}
          	});
     	}
     	if(loadid == 0 && driverid == 0)
     	{
     		if(dnum==1)
			{
				$('#driver_warning').html('');
			}
			else
			{
				$('#driver_warning'+dnum+'').html('');
			}
     	}
	}
	function mrr_preplan_cancel(loadid,dnum)
	{
		drivername='';
		driverphone='';
		
		$.ajax({
			url: "ajax.php?cmd=mrr_preplan_auto_set_driver_for_load",
			data: {
					"load_id" : loadid,
					"driver_id": 0,
					"driver_num":dnum 						
				},
			type: "POST",
			cache:false,
			async:false,
			dataType: "xml",
			success: function(xml) {
				if($(xml).find('rslt').text() == "1") 
				{
					$.noticeAdd({text: "Success - Preplanned Driver has been removed from Load "+loadid+"."});		
					
					if(dnum==1)
					{
						$('#preplan_driver_'+loadid+'').html(drivername);
						$('#preplan_phone_'+loadid+'').html(driverphone);		
					}
					if(dnum==2)
					{
						$('#preplan_driver2_'+loadid+'').html(drivername);
						$('#preplan_phone2_'+loadid+'').html(driverphone);		
					}
					if(dnum==3)
					{
						$('#preplan_driver3_'+loadid+'').html(drivername);
						$('#preplan_phone3_'+loadid+'').html(driverphone);		
					}
					if(dnum==4)
					{
						$('#preplan_driver4_'+loadid+'').html(drivername);
						$('#preplan_phone4_'+loadid+'').html(driverphone);		
					}
					if(dnum==0)
					{
						$('#preplan_driver_'+loadid+'').html(drivername);
						$('#preplan_phone_'+loadid+'').html(driverphone);	
						$('#preplan_driver2_'+loadid+'').html(drivername);
						$('#preplan_phone2_'+loadid+'').html(driverphone);
						$('#preplan_driver3_'+loadid+'').html(drivername);
						$('#preplan_phone3_'+loadid+'').html(driverphone);
						$('#preplan_driver4_'+loadid+'').html(drivername);
						$('#preplan_phone4_'+loadid+'').html(driverphone);							
					}			
				}
				else
				{					
					$.prompt("Error: Driver Planning could not be updated.");		
				}
				mrr_clear_pallet();					
			}
     	});
	}
	
	function mrr_clear_pallet()
	{
		$('#load_id').val('0');
		$('#show_load_name').html('0');
		
		$('#driver_id').val('0');
		$('#driver_name').val('');
		$('#driver_phone').val('');
		$('#show_driver_name').html('');
		$('#show_disp_hrs').html('0.00');
		$('#show_preplan_hrs').html('0.00');
		$('#show_tot_hrs').html('0.00');
		$('#driver_warning').html('');
		
		
		$('#driver2_id').val('0');
		$('#driver2_name').val('');
		$('#driver2_phone').val('');
		$('#show_driver2_name').html('');
		$('#show_disp_hrs2').html('0.00');
		$('#show_preplan_hrs2').html('0.00');
		$('#show_tot_hrs2').html('0.00');
		$('#driver_warning2').html('');
		
		$('#driver3_id').val('0');
		$('#driver3_name').val('');
		$('#driver3_phone').val('');
		$('#show_driver3_name').html('');
		$('#show_disp_hrs3').html('0.00');
		$('#show_preplan_hrs3').html('0.00');
		$('#show_tot_hrs3').html('0.00');
		$('#driver_warning3').html('');
		
		$('#driver4_id').val('0');
		$('#driver4_name').val('');
		$('#driver4_phone').val('');
		$('#show_driver4_name').html('');
		$('#show_disp_hrs4').html('0.00');
		$('#show_preplan_hrs4').html('0.00');
		$('#show_tot_hrs4').html('0.00');		
		$('#driver_warning4').html('');
	}
	
	function mrr_toggle_info(id)
	{
		$('.mrr_extra_info').hide();	
		if(id > 0)	$('.mrr_extra_info').show();	
	}
	
</script>
<? include('footer.php') ?>