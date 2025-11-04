<? include('application.php') ?>
<? $admin_page = 1 ?>
<?
	$dateyear=date("Y");
	$dateyear-=1;
	
	if(!isset($_POST['cur_date']))	
	{
		$_POST['cur_date']=date("12/01/".$dateyear."");
		if(date("Ymd",time()) >='20180630')		$_POST['cur_date']=date("07/01/".$dateyear."");
	}
	if(!isset($_POST['cur_date_end']))		$_POST['cur_date_end']=date("06/30/Y");
	
	
		
	function mrr_update_equip_value_acquired_setting($dater,$type,$id,$val)
	{			
		return;		//disabled for now...
		
		$sql = "
			select *
			from equipment_history			
			where deleted = 0				
				and equipment_id = '".sql_friendly($id) ."'
				and equipment_type_id = '".sql_friendly($type) ."'				 
			order by linedate_aquired desc,id desc
		";	
			//and linedate_aquired <'".date("Y-m-d",strtotime($dater))." 00:00:00'
			//and (linedate_returned <='".date("Y-m-d",strtotime($date_end))." 23:59:59' or linedate_returned='0000-00-00 00:00:00')
				
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			//NOTE: *********should not make any changes if the last value = new value for this peice of equipment...*********
			
			if($row['linedate_returned'] == "0000-00-00 00:00:00" && $row['equipment_value']!=$val )
			{	//not returned at all, so return it at the old value in recreate it at the new value.
				$sqlu = "
				update equipment_history set
					linedate_returned='".date("Y-m-d",strtotime($dater))." 00:00:00'
				where id = '".sql_friendly($row['id'])."' 				
				";
				simple_query($sqlu);					
				
				//add it back at the new value...
				$sqlu = "
				insert into equipment_history
					(id,
					equipment_type_id,
					equipment_id,
					equipment_value,
					linedate_added,
					linedate_aquired,
					linedate_returned,
					deleted,
					miles_pickup,
					miles_dropoff,
					replacement,
					xref_id,
					replacement_xref_id)
				values
					(NULL,
					'".sql_friendly($type) ."',
					'".sql_friendly($id)."',
					'".sql_friendly($val)."',
					NOW(),
					'".date("Y-m-d",strtotime("+1 day",strtotime($dater)))." 00:00:00',
					'0000-00-00 00:00:00',
					0,
					'0',
					'0',
					'".sql_friendly($row['replacement'])."',
					'".sql_friendly($row['xref_id'])."',
					'".sql_friendly($row['replacement_xref_id'])."')
				";
				simple_query($sqlu);
			}
			/*
			elseif(date("Ymd",strtotime($row['linedate_returned']))  <= date("Ymd",strtotime($dater)) && $row['equipment_value']!=$val )
			{	//already returned BEFORE the date, so just change the value of what it was.
				$sqlu = "
				update equipment_history set
					equipment_value='".sql_friendly($val)."'
				where id = '".sql_friendly($row['id'])."' 				
				";
				simple_query($sqlu);		
			}
			elseif(date("Ymd",strtotime($row['linedate_returned']))  >  date("Ymd",strtotime($dater))  && $row['equipment_value']!=$val)
			{	//already returned AFTER the date, so bi-sect or split it so the last value ends on this date and the new value begins on this date too but still returned  using prior setting.
				
				//end exiting one "today" first ...with the old value up to this point.
				$sqlu = "
				update equipment_history set
					linedate_returned='".date("Y-m-d",strtotime($dater))." 00:00:00'
				where id = '".sql_friendly($row['id'])."' 				
				";
				simple_query($sqlu);	
				
				//Now, add it back at the new value...from this point on and return it like it was before.
				$sqlu = "
				insert into equipment_history
					(id,
					equipment_type_id,
					equipment_id,
					equipment_value,
					linedate_added,
					linedate_aquired,
					linedate_returned,
					deleted,
					miles_pickup,
					miles_dropoff,
					replacement,
					xref_id,
					replacement_xref_id)
				values
					(NULL,
					'".sql_friendly($type) ."',
					'".sql_friendly($id)."',
					'".sql_friendly($val)."',
					NOW(),
					'".date("Y-m-d",strtotime($dater))." 00:00:00',
					'".sql_friendly($row['linedate_returned'])."',
					0,
					'".sql_friendly($row['miles_pickup'])."',
					'".sql_friendly($row['miles_dropoff'])."',
					'".sql_friendly($row['replacement'])."',
					'".sql_friendly($row['xref_id'])."',
					'".sql_friendly($row['replacement_xref_id'])."')
				";
				simple_query($sqlu);	
			}
			//DONE
			*/
		}
	}	
		
	$update_msg=" ";				//TESTING MODE:
	
	if(isset($_POST['update_all_values']))
	{
		//$update_msg.="Disabled...no updates.";
		
		//trucks...and PN units....		
		foreach($_POST['truck_id_list'] as $id) 
		{
			if($id==455 || 1==1)
			{	//				
     			//$rvals=mrr_fetch_equip_value_tracked($_POST['cur_date'],$id,0);
     			//$tvalue=$rvals['equip_value'];
         			//$uvalue=$rvals['unit_value'];	
         			
     			//if($tvalue==0 && $tvalue==0)
     			//{
     				$sql = "
     				update equipment_value_tracking set
     					deleted='1'
     				where truck_id = '".sql_friendly($id)."' 
     					and linedate='".date("Y-m-d",strtotime($_POST['cur_date']))." 00:00:00'
     					
     				";
     				simple_query($sql);	
     				//and linedate_end='".date("Y-m-d",strtotime($_POST['cur_date_end']))." 23:59:59'
     			//}
     			
     			$tvalue=trim($_POST['truck_ovalue_'.$id.'']);			
     			$uvalue=trim($_POST['unit_ovalue_'.$id.'']);
     			
     			$tvalue_end=trim($_POST['truck_value_'.$id.'']);			
     			$uvalue_end=trim($_POST['unit_value_'.$id.'']);
     						
     			$tvalue=str_replace("$","",$tvalue);		$tvalue=str_replace(",","",$tvalue);		$tvalue=str_replace(" ","",$tvalue);
     			$uvalue=str_replace("$","",$uvalue);		$uvalue=str_replace(",","",$uvalue);		$uvalue=str_replace(" ","",$uvalue);
     			
     			$tvalue_end=str_replace("$","",$tvalue_end);	$tvalue_end=str_replace(",","",$tvalue_end);	$tvalue_end=str_replace(" ","",$tvalue_end);
     			$uvalue_end=str_replace("$","",$uvalue_end);	$uvalue_end=str_replace(",","",$uvalue_end);	$uvalue_end=str_replace(" ","",$uvalue_end);
     			
     			if($tvalue=="")		$tvalue="0.00";
     			if($uvalue=="")		$uvalue="0.00";
     			
     			if($tvalue_end=="")		$tvalue_end="0.00";
     			if($uvalue_end=="")		$uvalue_end="0.00";
     			
     			$sql = "
     				insert into equipment_value_tracking
     					(id,
     					user_id,
     					linedate_added,
     					linedate,
     					linedate_end,
     					truck_id,
     					trailer_id,					
     					equip_value,
     					unit_value,
     					equip_value_end,
     					unit_value_end,
     					deleted)
     				values
     					(NULL,
     					'".sql_friendly($_SESSION['user_id'])."',
     					NOW(),
     					'".date("Y-m-d",strtotime($_POST['cur_date']))." 00:00:00',
     					'".date("Y-m-d",strtotime($_POST['cur_date_end']))." 23:59:59',
     					'".sql_friendly($id)."',
     					0,
     					'".sql_friendly($tvalue)."',
     					'".sql_friendly($uvalue)."',	
     					'".sql_friendly($tvalue_end)."',
     					'".sql_friendly($uvalue_end)."',				
     					0)
     			";
     			simple_query($sql);
     			
     			//update truck unit...       DISABLED
     			$sql = "
     				update trucks set
     					apu_value = '".sql_friendly($uvalue_end)."'
     				where id = '".sql_friendly($id)."' 
     			";
     			//simple_query($sql);
     			
     			//now add new equipment history entry if the system needs it for this truck.
     			//mrr_update_equip_value_acquired_setting($_POST['cur_date_end'],1,$id,$tvalue_end);		
     			
     		}
		}				
		$update_msg.="...Truck (and PN Unit) Values Updated.";
		
		
		//trailers...
		foreach($_POST['trailer_id_list'] as $id) 
		{
     		if($id==297 || 1==1)
     		{
     			//$rvals=mrr_fetch_equip_value_tracked($_POST['cur_date'],0,$id);
     			//$tvalue=$rvals['equip_value'];
         			//$uvalue='0.00';
     			//if($tvalue==0)
     			//{
     				$sql = "
     				update equipment_value_tracking set
     					deleted='1'
     				where trailer_id = '".sql_friendly($id)."' 
     					and linedate='".date("Y-m-d",strtotime($_POST['cur_date']))." 00:00:00'
     				";
     				simple_query($sql);	
     				//and linedate_end='".date("Y-m-d",strtotime($_POST['cur_date_end']))." 23:59:59'
     			//}
     			
     			$uvalue='0.00';
     			$uvalue_end='0.00';
     			
     			$tvalue=trim($_POST['trailer_ovalue_'.$id.'']);			
     			//$uvalue=trim($_POST['unit_ovalue_'.$id.'']);
     			
     			$tvalue_end=trim($_POST['trailer_value_'.$id.'']);			
     			//$uvalue_end=trim($_POST['unit_value_'.$id.'']);
     			
     			$tvalue=str_replace("$","",$tvalue);			$tvalue=str_replace(",","",$tvalue);			$tvalue=str_replace(" ","",$tvalue);
     			//$uvalue=str_replace("$","",$uvalue);			$uvalue=str_replace(",","",$uvalue);			$uvalue=str_replace(" ","",$uvalue);
     			
     			$tvalue_end=str_replace("$","",$tvalue_end);		$tvalue_end=str_replace(",","",$tvalue_end);		$tvalue_end=str_replace(" ","",$tvalue_end);
     			//$uvalue_end=str_replace("$","",$uvalue_end);	$uvalue_end=str_replace(",","",$uvalue_end);		$uvalue_end=str_replace(" ","",$uvalue_end);
     			
     			if($tvalue=="")			$tvalue="0.00";
     			//if($uvalue=="")			$uvalue="0.00";
     			
     			if($tvalue_end=="")			$tvalue_end="0.00";
     			//if($uvalue_end=="")		$uvalue_end="0.00";
     			
     			$sql = "
     				insert into equipment_value_tracking
     					(id,
     					user_id,
     					linedate_added,
     					linedate,
     					linedate_end,
     					truck_id,
     					trailer_id,					
     					equip_value,
     					unit_value,
     					equip_value_end,
     					unit_value_end,
     					deleted)
     				values
     					(NULL,
     					'".sql_friendly($_SESSION['user_id'])."',
     					NOW(),
     					'".date("Y-m-d",strtotime($_POST['cur_date']))." 00:00:00',
     					'".date("Y-m-d",strtotime($_POST['cur_date_end']))." 23:59:59',
     					0,
     					'".sql_friendly($id)."',					
     					'".sql_friendly($tvalue)."',
     					'".sql_friendly($uvalue)."',		
     					'".sql_friendly($tvalue_end)."',
     					'".sql_friendly($uvalue_end)."',			
     					0)
     			";
     			simple_query($sql);
     			
     			
     			//now add new equipment history entry if the system needs it for this trailer.
     			//mrr_update_equip_value_acquired_setting($_POST['cur_date_end'],2,$id,$tvalue_end);				
     			
     		}     		
		}
		$update_msg.=" ...Trailer Values Updated.";
	}
				
	
	//trucks and PN tracking units
	$sql = "
		select *,
			(select replacement_xref_id from equipment_history eh where eh.deleted = 0 and eh.equipment_type_id = 1 and eh.equipment_id = trucks.id and eh.linedate_returned = 0 limit 1) as replaces_truck_id,
			(select equipment_id from equipment_history eh where eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_id,
			(SELECT GROUP_CONCAT(CONCAT(name_driver_first, ' ', name_driver_last)) FROM drivers WHERE drivers.active = 1 AND drivers.attached_truck_id = trucks.id AND drivers.deleted = 0) as attached_driver_list		
		from trucks
		where deleted = 0
			and trucks.active>0
		order by trucks.active desc,
				name_truck asc	
	";
	$data = simple_query($sql);

	$active_count = 0;
	while($row = mysqli_fetch_array($data)) 
	{
		if($row['active']) $active_count++;
	}
	if(mysqli_num_rows($data)) mysqli_data_seek($data,0);
	
	
	//trailers...
	$sqlt = "
		select *,
			(select count(*) from equipment_history where equipment_type_id = 2 and equipment_id = trailers.id and deleted = 0) as equipment_history_entry
		
		from trailers
		where deleted = 0
			and trailers.active > 0
			and interchange_flag=0
		order by trailers.active desc,
			trailer_name asc
	";
	$datat = simple_query($sqlt);
	
	$activet_count = 0;
	while($rowt = mysqli_fetch_array($datat)) 
	{
		if($rowt['active']) $activet_count++;
	}
	if(mysqli_num_rows($datat)) mysqli_data_seek($datat,0);
	
	
	$mrr_activity_log_notes.="Viewed list of trucks. ";
	
	
	function mrr_fetch_equip_value_tracked($dater,$truck_id=0,$trailer_id=0)
	{	//,$date_end
		$res['equip_value']=0;
		$res['unit_value']=0;
		
		$res['equip_start']=0;
		$res['unit_start']=0;
				
		$res['equip_diff']=0;
		$res['unit_diff']=0;
		
		$cntr=0;
		
		$last_val=-1;
		$last_val2=-1;
		
		$sql = "
			select *
			from equipment_value_tracking			
			where deleted = 0
				and linedate <='".date("Y-m-d",strtotime($dater))." 00:00:00'
				
				".($truck_id > 0 ? " and truck_id = '".sql_friendly($truck_id) ."'"  : "" )."
				".($trailer_id > 0 ? " and trailer_id = '".sql_friendly($trailer_id) ."'"  : "" )."
			order by linedate asc,id asc
		";	//and linedate_end >='".date("Y-m-d",strtotime($date_end))." 23:59:59'
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			if($cntr==0)
			{
				$res['equip_start']=$row['equip_value'];
				$res['unit_start']=$row['unit_value'];	
				
				if($row['equip_value'] == 0)		$res['equip_start']=$row['equip_value_end'];
				if($row['unit_value'] == 0)		$res['unit_start']=$row['unit_value_end'];					
			}
			
			if($row['equip_value'] > 0)		$res['equip_start']=$row['equip_value'];
			if($row['unit_value'] > 0)		$res['unit_start']=$row['unit_value'];	
			
			
			$res['equip_value']=$row['equip_value_end'];
			$res['unit_value']=$row['unit_value_end'];	
			
			
			if($last_val >= 0)	$res['equip_start']=$last_val;	
			if($last_val2 >= 0)	$res['unit_start']=$last_val2;		
			
			$last_val=$row['equip_value_end'];
			$last_val2=$row['unit_value_end'];
			
			$cntr++;
		}
		
		$res['equip_diff']= $res['equip_start']  - $res['equip_value'];
		$res['unit_diff']= $res['unit_start']  - $res['unit_value'];
				
		return $res;
	}
	
	
	function mrr_fetch_equip_value_acquired($dater,$date_end,$type,$id)
	{	
		$res['equip_start']=0;		
		$res['equip_value']=0;		
		$res['equip_diff']=0;
		
		$cntr=0;
				
		$sql = "
			select *
			from equipment_history			
			where deleted = 0				
				and equipment_id = '".sql_friendly($id) ."'
				and equipment_type_id = '".sql_friendly($type) ."'				 
			order by linedate_aquired asc,linedate_returned asc,id asc
		";	
			//and linedate_aquired <'".date("Y-m-d",strtotime($dater))." 00:00:00'
			//and (linedate_returned <='".date("Y-m-d",strtotime($date_end))." 23:59:59' or linedate_returned='0000-00-00 00:00:00')
				
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{
			if($cntr==0)		$res['equip_start']=$row['equipment_value'];		//set first value		
			
			//see if there is a closer value than the first time acquired...or if no value found for the truck/trailer.
			if($res['equip_start']==0 || date("Ymd",strtotime($row['linedate_aquired'])) <= date("Ymd",strtotime($dater)))	
			{
				$res['equip_start']=$row['equipment_value'];	//set first value		
			}
			
			$res['equip_value']=$row['equipment_value'];		//update last value
			
			$cntr++;
		}
		
		$res['equip_diff']= $res['equip_start']  - $res['equip_value'];
				
		return $res;
	}
	
	
	function show_my_truck($truck_id,$cntr=0,$dater="",$date_end="",$last_pass="") 
	{
		if($dater=="")		$dater=date("m/01/Y",time());
		if($date_end=="")	$date_end=date("m/01/Y",time());
		
		$res['excel']="";
		$res['tab']="";
		$res['truck_otot']=0;
		$res['truck_ctot']=0;
		$res['truck_dtot']=0;
		$res['unit_otot']=0;
		$res['unit_ctot']=0;
		$res['unit_dtot']=0;
		
		$export_file="";
		
		$sql = "
			select *,
				(select count(*) from equipment_history where equipment_type_id = 1 and equipment_id = trucks.id and deleted = 0) as equipment_history_entry,
				(select equipment_id from equipment_history eh where eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_id
			
			from trucks
			where deleted = 0
				and trucks.id = '".sql_friendly($truck_id) ."'
		";
		/*
		,
				(	SELECT GROUP_CONCAT(CONCAT(name_driver_first, ' ', name_driver_last)) 
					FROM drivers 
					WHERE drivers.active = 1 
						AND drivers.attached_truck_id = trucks.id 
						AND drivers.deleted = 0 
					) as attached_driver_list
		*/
		$data = simple_query($sql);
		//@mysqli_data_seek($data_tmp , 0);
		$row = mysqli_fetch_array($data);
		
		$use_class = "";
		if($row['replacement_truck_id']) {
			$use_class = "show_inactive_row show_inactive mrr_inactive_grey_back mrr_show_inactive_red";
		} elseif($row['replacement']) {
			$use_class = "show_inactive_row mrr_inactive_grey_back mrr_show_inactive_replace";
		}
		
		$warning=trucks_last_movement($truck_id);
		$mrr_insur="";
		if($row['no_insurance'] > 0)	$mrr_insur="&nbsp;&nbsp; <span class='alert'><b>NoInsur</b></span>";
		$in_shop="";
		if($row['in_the_shop'] > 0)	$in_shop="&nbsp;&nbsp; <span class='alert'><b>{In Shop}</b></span>";	
		
		
		$tab="";
		if(trim($row['license_plate_no'])!="") 
		{ 
			$rvals=mrr_fetch_equip_value_tracked($dater,$row['id'],0);
			
			//UNIT VALUE...use actual value if present
			if($rvals['unit_start']=="0.00")	$rvals['unit_start']=$row['apu_value'];
			if($rvals['unit_value']=="0.00")	$rvals['unit_value']=$row['apu_value'];	     					
			$duvalue=$rvals['unit_start'] - $rvals['unit_value'];	
			$uvalue=$rvals['unit_value'];
			$ouvalue=$rvals['unit_start'];	
			
			//Truck VALUE...use actual value if present
			$tres=mrr_fetch_equip_value_acquired($dater,$date_end,1,$row['id']);
			if($rvals['equip_start']=="0.00")		$rvals['equip_start']=$tres['equip_start'];
			if($rvals['equip_value']=="0.00")		$rvals['equip_value']=$tres['equip_value'];					
			$dtvalue=$rvals['equip_start'] - $rvals['equip_value'];
			$tvalue=$rvals['equip_value'];		    					    					
			$otvalue=$rvals['equip_start'];   	
			
			
			//subtotals for print/email displayer...javascript runs after the print/email has been sent.
			$res['truck_otot']+=$otvalue;
			$res['truck_ctot']+=$tvalue ;
			$res['truck_dtot']+=$dtvalue;
			$res['unit_otot']+=$ouvalue;
			$res['unit_ctot']+=$uvalue;
			$res['unit_dtot']+=$duvalue;	
			
			//<td>".$row['license_plate_no']."</td>	
			$tab.="
			<tr class='".$use_class."'>
     			<td>
     				".($row['replacement'] ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : "")."
     				<a href='admin_trucks.php?id=".$row['id']."' target='_blank' class='".($row['equipment_history_entry'] == 0 ? 'alert' : ($row['active'] ? '' : 'inactive'))."'>
     					".($row['name_truck'] == '' ? "(id: $row[id])" : $row['name_truck'])."
     				</a>
     				&nbsp;&nbsp; ". $warning ."".$mrr_insur ."".$in_shop ."
     			</td>	
     			<td>".$row['vin']."</td>		
     			<td>".$row['truck_make']."</td>
     			<td>".$row['truck_model']."</td>
     			<td>".$row['truck_year']."</td>			
     			<td>".trim($row['leased_from'])."".($row['rental'] ? " (Rental) " : "")."</td>
     			<!--<td>".$row['attached_driver_list']."</td>	-->					
     			<td nowrap>".($row['replacement']  ? "Replacement " : "")."".($row['replacement_truck_id'] ? "<span class='alert'>Replaced</span> " : "")."</td>
     			<td align='right'>$".number_format($otvalue,2)."</td>  
     			<td align='right'>
     				<input name='truck_value_".$row['id']."' value='$".$tvalue."' style='width:75px;text-align:right' class='truck_value_amnt'>
     				<input name='truck_id_list[]' value='".$row['id']."' type='hidden'>
     							
     				<input name='truck_ovalue_".$row['id']."' id='truck_ovalue_".$row['id']."' value='".$otvalue ."' type='hidden' class='truck_value_start'>
     				<input name='truck_dvalue_".$row['id']."' id='truck_dvalue_".$row['id']."' value='".$dtvalue ."' type='hidden' class='truck_value_diff'>
     				
     				<input name='unit_ovalue_".$row['id']."' id='unit_ovalue_".$row['id']."' value='".$ouvalue ."' type='hidden' class='unit_value_start'>
     				<input name='unit_dvalue_".$row['id']."' id='unit_dvalue_".$row['id']."' value='".$duvalue ."' type='hidden' class='unit_value_diff'>
     			</td>
     			<td align='right'>$".number_format($dtvalue,2) ."</td>  
     			<td align='right'>".($row['peoplenet_tracking'] > 0 ? 'Geo' : '')."</td>			
     			<td align='right'>". $row['apu_number']."</td>
     			<td align='right'>$".number_format($ouvalue,2)."</td>  
     			<td align='right'>
     				<input name='unit_value_".$row['id']."' value='$".$uvalue."' style='width:75px;text-align:right' class='unit_value_amnt'>
     			</td>
     			<td align='right'>$".number_format($duvalue,2)."</td>  
     		</tr>			
			";
			
			$excel_version="".($row['name_truck'] == '' ? "(id: $row[id])" : $row['name_truck'])." ". $warning ."".$mrr_insur ."".$in_shop ."";
			$excel_version=str_replace("&nbsp;"," ",$excel_version);
			
			$export_file .= "".trim(strip_tags($excel_version))."".chr(9);
          	$export_file .= "".trim($row['vin'])."".chr(9);
          	$export_file .= "".trim($row['license_plate_no'])."".chr(9);
          	$export_file .= "".trim($row['truck_make'])."".chr(9);
          	$export_file .= "".trim($row['truck_model'])."".chr(9);
          	$export_file .= "".trim($row['truck_year'])."".chr(9);
          	$export_file .= "".trim($row['leased_from'])."".($row['rental'] ? " (Rental) " : "")."".chr(9);
          	$export_file .= "$".number_format($otvalue,2)."".chr(9);
          	$export_file .= "$".number_format($tvalue,2)."".chr(9);
          	$export_file .= "$".number_format($dtvalue,2)."".chr(9);
          	$export_file .= "".($row['peoplenet_tracking'] > 0 ? 'PN' : '')."".chr(9);
          	$export_file .= "".trim($row['apu_number'])."".chr(9);
          	$export_file .= "$".number_format($ouvalue,2)."".chr(9);
          	$export_file .= "$".number_format($uvalue,2)."".chr(9);
          	$export_file .= "$".number_format($duvalue,2)."".chr(9);			
          	$export_file .= chr(13);	
		}
			
		$mrr_replaced=$row['replacement_truck_id'];
		$mrr_replacer=$row['replacement'];
		
		while($mrr_replacer > 0 &&  $mrr_replaced > 0 && $mrr_replaced!=$truck_id)
		{
			$sql2 = "
     			select *,
     				(select count(*) from equipment_history where equipment_type_id = 1 and equipment_id = trucks.id and deleted = 0) as equipment_history_entry,
     				(select equipment_id from equipment_history eh where eh.deleted = 0 and eh.equipment_type_id = 1 and eh.replacement_xref_id = trucks.id and eh.linedate_returned = 0 limit 1) as replacement_truck_id
     			
     			from trucks
     			where deleted = 0
     				and trucks.id = '".sql_friendly($mrr_replaced) ."'
     		";
     		/*
     		,
     				(	SELECT GROUP_CONCAT(CONCAT(name_driver_first, ' ', name_driver_last)) 
     					FROM drivers 
     					WHERE drivers.active = 1 
     						AND drivers.attached_truck_id = trucks.id 
     						AND drivers.deleted = 0 
     					) as attached_driver_list
     		*/
     		$data2 = simple_query($sql2);
     		$row2 = mysqli_fetch_array($data2);
     		
     		$use_class = "";
     		if($row2['replacement_truck_id']) {
     			$use_class = "show_inactive_row show_inactive mrr_inactive_grey_back mrr_show_inactive_red";
     		} elseif($row2['replacement']) {
     			$use_class = "show_inactive_row mrr_inactive_grey_back mrr_show_inactive_replace";
     		}
     		
     		$warning=trucks_last_movement($mrr_replaced);
     		$mrr_insur="";
     		if($row2['no_insurance'] > 0)	$mrr_insur="&nbsp;&nbsp; NoInsur";
     		$in_shop="";
			if($row2['in_the_shop'] > 0)	$in_shop="&nbsp;&nbsp; {In Shop}";
     		
     		if(trim($row2['license_plate_no'])!="") 
     		{ 
     			$rvals=mrr_fetch_equip_value_tracked($dater,$row2['id'],0);
				
				//UNIT VALUE...use actual value if present
				if($rvals['unit_start']=="0.00")	$rvals['unit_start']=$row['apu_value'];
				if($rvals['unit_value']=="0.00")	$rvals['unit_value']=$row['apu_value'];	     					
				$duvalue=$rvals['unit_start'] - $rvals['unit_value'];	
				$uvalue=$rvals['unit_value'];
				$ouvalue=$rvals['unit_start'];	
				
				//Truck VALUE...use actual value if present
				$tres=mrr_fetch_equip_value_acquired($dater,$date_end,1,$row2['id']);
				if($rvals['equip_start']=="0.00")		$rvals['equip_start']=$tres['equip_start'];
				if($rvals['equip_value']=="0.00")		$rvals['equip_value']=$tres['equip_value'];					
				$dtvalue=$rvals['equip_start'] - $rvals['equip_value'];
				$tvalue=$rvals['equip_value'];		    					    					
				$otvalue=$rvals['equip_start']; 	
				
				
				//subtotals for print/email displayer...javascript runs after the print/email has been sent.
				$res['truck_otot']+=$otvalue;
				$res['truck_ctot']+=$tvalue ;
				$res['truck_dtot']+=$dtvalue;
				$res['unit_otot']+=$ouvalue;
				$res['unit_ctot']+=$uvalue;
				$res['unit_dtot']+=$duvalue;	
     			
     			//<td>".$row2['license_plate_no']."</td>	
     			$tab.="
          		<tr class='".$use_class."'>
          			<td>
          				".($row2['replacement'] ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : "")."
          				<a href='admin_trucks.php?id=".$row2['id']."' target='_blank' class='".($row2['equipment_history_entry'] == 0 ? 'alert' : ($row2['active'] ? '' : 'inactive'))."'>
          					".($row2['name_truck'] == '' ? "(id: $row2[id])" : $row2['name_truck'])."
          				</a>
          				&nbsp;&nbsp; ". $warning ."".$mrr_insur ."".$in_shop ."
          			</td>
          			<td>".$row2['vin']."</td>	
          			<td>".$row2['truck_make']."</td>
          			<td>".$row2['truck_model']."</td>
          			<td>".$row2['truck_year']."</td>     			
          			<td>".trim($row2['leased_from'])."".($row2['rental'] ? " (Rental) " : "")."</td>
          			<!--<td>".$row['attached_driver_list']."</td>-->
          			<td nowrap>".($row2['replacement'] ? "Replacement " : "")."".($row2['replacement_truck_id'] ? "<span class='alert'>Replaced</span> " : "")."</td>
          			<td align='right'>$".number_format($otvalue,2)."</td>  
          			<td align='right'>
          				<input name='truck_value_".$row2['id']."' value='$".$tvalue ."' style='width:75px;text-align:right' class='truck_value_amnt'>
          				<input name='truck_id_list[]' value='".$row2['id']."' type='hidden'>
          				
          				<input name='truck_ovalue_".$row2['id']."' id='truck_ovalue_".$row2['id']."' value='".$otvalue."' type='hidden' class='truck_value_start'>
          				<input name='truck_dvalue_".$row2['id']."' id='truck_dvalue_".$row2['id']."' value='".$dtvalue."' type='hidden' class='truck_value_diff'>
          				
          				<input name='unit_ovalue_".$row2['id']."' id='unit_ovalue_".$row2['id']."' value='".$ouvalue."' type='hidden' class='unit_value_start'>
          				<input name='unit_dvalue_".$row2['id']."' id='unit_dvalue_".$row2['id']."' value='".$duvalue."' type='hidden' class='unit_value_diff'>
          			</td>
          			<td align='right'>$".number_format($dtvalue,2) ."</td>   
          			<td align='right'>".($row2['peoplenet_tracking'] > 0 ? 'Geo' : '')."</td>
          			<td align='right'>".$row2['apu_number']."</td>   
          			<td align='right'>$".number_format($ouvalue,2)."</td>  
          			<td align='right'>
          				<input name='unit_value_".$row2['id']."' value='$".$uvalue ."' style='width:75px;text-align:right' class='unit_value_amnt'>
          			</td> 
          			<td align='right'>$".number_format($duvalue,2) ."</td>     			
          		</tr>";    
          		
          		$excel_version="--".($row2['name_truck'] == '' ? "(id: $row2[id])" : $row2['name_truck'])." ". $warning ."".$mrr_insur ."".$in_shop ."";
				$excel_version=str_replace("&nbsp;"," ",$excel_version);
				
				$export_file .= "".trim(strip_tags($excel_version))."".chr(9);
          		$export_file .= "".trim($row2['vin'])."".chr(9);
          		$export_file .= "".trim($row2['license_plate_no'])."".chr(9);
          		$export_file .= "".trim($row2['truck_make'])."".chr(9);
          		$export_file .= "".trim($row2['truck_model'])."".chr(9);
          		$export_file .= "".trim($row2['truck_year'])."".chr(9);
          		$export_file .= "".trim($row2['leased_from'])."".($row2['rental'] ? " (Rental) " : "")."".chr(9);
          		$export_file .= "$".number_format($otvalue,2)."".chr(9);
          		$export_file .= "$".number_format($tvalue,2)."".chr(9);
          		$export_file .= "$".number_format($dtvalue,2)."".chr(9);
          		$export_file .= "".($row2['peoplenet_tracking'] > 0 ? 'Geo' : '')."".chr(9);
          		$export_file .= "".trim($row2['apu_number'])."".chr(9);
          		$export_file .= "$".number_format($ouvalue,2)."".chr(9);
          		$export_file .= "$".number_format($uvalue,2)."".chr(9);
          		$export_file .= "$".number_format($duvalue,2)."".chr(9);			
          		$export_file .= chr(13);	      		
          	 } 
     					
			$mrr_replaced=$row2['replacement_truck_id'];
			$mrr_replacer=$row2['replacement'];	
		}		
		
		$res['excel'] =$export_file;
		$res['tab']=$tab;
		return $res;
	}
?>
<?
$usetitle = "Equipment Value Spreadsheet";
$use_title = "Equipment Value Spreadsheet";
?>
<? include('header.php') ?>
<form action='' method='post' id='main_form'>
<?
$rfilter = new report_filter();
$rfilter->show_date_range 		= false;
$rfilter->show_single_date 		= false;
$rfilter->show_font_size			= true;	
$rfilter->mrr_excel_print_flag	= true;
$rfilter->mrr_send_email_here		= true;
$rfilter->mrr_no_form_enclosed	= true;
$rfilter->mrr_hide_submit_button	= true;
$rfilter->show_filter();

//new email sending line to submit and send the email directly from this report. 
$mrr_use_styles=0;	
if(!isset($_POST['mrr_email_addr']))		$_POST['mrr_email_addr']="";
if(!isset($_POST['mrr_email_addr_name']))	$_POST['mrr_email_addr_name']="";	
if(isset($_POST['mrr_email_report']))	{	$_POST['build_report'] = 1;	$mrr_use_styles=1;	}

$use_excel=0;		if(isset($_POST['mrr_excel_print_file']))		$use_excel=1;	

$uuid = createuuid();
$excel_filename = "equipment_$uuid.xls";
$export_file = "";

$export_file .= "Conard Transportation Trucks".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
$export_file .= chr(13);	
$export_file .= chr(13);
$export_file .= "Truck".chr(9).
			"VIN".chr(9).
			"Tag Number".chr(9).
			"Make".chr(9).
			"Model".chr(9).
			"Year".chr(9).
			"Rent/Lease".chr(9).
			"Original Value".chr(9).
			"Current Value".chr(9).
			"Depreciation".chr(9).
			"PN".chr(9).
			"ELD/Cam Unit".chr(9).
			"Original".chr(9).
			"ELD/Cam Value".chr(9).
			"Depreciation".chr(9);
$export_file .= chr(13);	

/*
<div class='toolbar_button' onclick=\"window.open('temp/".$excel_filename."')\">
					<div><img src='images/excel.png'></div>
					<div>Pricing</div>
				</div>
*/

$stylex=" style='font-weight:bold;'";
$mrr_total_head = " style='font-weight:bold; width:1000px; text-align:right;'";
$tablex=" border='1' cellpadding='1' cellspacing='1' width='1200'";
$headerx=" style='background-color:#CCCCFF;'";

ob_start();
echo "<table".( $mrr_use_styles > 0 ? "".$tablex."" : " class='table_section font_display_section' style='text-align:left;'").">";
?>
<tr>
	<td valign='top'>
		
		<table class='admin_menu1'>
		<tr>
			<td colspan='15'>
				<font class='standard18'><b><?=$use_title ?></b></font>
				<center>
					
					From <input type='text' value='<?=$_POST['cur_date']?>' class='datepicker' name='cur_date' id='cur_date' style='width:80px;'>
					To <input type='text' value='<?=$_POST['cur_date_end']?>' class='datepicker' name='cur_date_end' id='cur_date_end' style='width:80px;'>					
					<? if($mrr_use_styles==0) { ?>
						<input type='submit' value='Load' class='mrr_button_access' name='find_all_values'>
						<br>
					 	(mm/dd/yyyy) &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (mm/dd/yyyy)  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;						
					<? } ?>					
					<br>			
				</center>
			</td>
		</tr>	
		<? if($mrr_use_styles==0) { ?>
		<tr>
			<td colspan='7'>
				<font class='standard18 format_alert'><b>Notes:</b></font><br> 
				<b>Vehicle must have License Plate to show on this page.</b>
				<br>ORIGINAL VALUE is the value of the equipment before the FROM date (or the value entered when it was acquired if it is not set). 
				<br>The CURRENT VALUE is the value between the dates, and will be used to calculate the depreciated value for the equipment (for each year and/or the life of the equipment).
				<br>The GeoTab columns are for the Geotab/PeopleNet Tracking Unit original and current values for trucks to track the unit depreciation separate from the trucks. {N/A for trailers at this time.}
				<br>Positive depreciation is value lost; negative depreciation is value gained.
				
			</td>
			<td colspan='8'>				
				<font class='standard18 format_alert'><b>Example:</b></font><br>
				Using dates From <?=$_POST['cur_date']?> To <?=$_POST['cur_date_end']?>, the ORIGINAL VALUE is from before <?=$_POST['cur_date']?> (or when acquired if later).  
				<br>The CURRENT VALUE applies From <?=$_POST['cur_date']?> To <?=$_POST['cur_date_end']?>.  
				<br>This value will also be used as the ORIGINAL VALUE of the equipment when running the date range From <?=date("m/d/Y",strtotime("+1 year",strtotime($_POST['cur_date']))) ?> To <?=date("m/d/Y",strtotime("+1 year",strtotime($_POST['cur_date_end'])))?>.
			</td>
		</tr>
		<tr>
			<td colspan='15'><font class='standard18 format_alert'><b><?=$update_msg ?></b></font></td>
		</tr>
		<tr>
			<td colspan='15'><center><input type='submit' value='Update Values' class='mrr_button_access' name='update_all_values'></center></td>
		</tr>
		<? } ?>
		<tr>
			<td><b>Truck</b></td>
			<td><b>VIN</b></td>	
			<td><b>Make</b></td>
			<td><b>Model</b></td>
			<td><b>Year</b></td>
			<td><b>Rent/Lease</b></td>
			<!--<td><b>Attached Driver</b></td>-->
			<td><b>&nbsp;</b></td>
			<td align='right'><b>Original Value</b></td>
			<td align='right'><b>Current Value</b></td>
			<td align='right'><b>Depreciation</b></td>
			<td align='right'><span title='GeoTab PeopleNet Tracking enabled if PN displays for each Truck...'><b>GeoTab</b></span></td>
			<td align='right'><b>ELD/Cam Unit</b></td>
			<td align='right'><b>Original</b></td>
			<td align='right'><b>ELD/Cam Value</b></td>
			<td align='right'><b>Depreciation</b></td>
		</tr>
		<? 
		
		$truck_cntr=0;
		
		$print_tot_to=0;
		$print_tot_tc=0;
		$print_tot_td=0;
		
		$print_tot_uo=0;
		$print_tot_uc=0;
		$print_tot_ud=0;
		
		while($row = mysqli_fetch_array($data)) 
		{
			if($row['replaces_truck_id'] > 0) 
			{
			
			} 
			else 
			{
				$pres=show_my_truck($row['id'],$truck_cntr,$_POST['cur_date'],$_POST['cur_date_end']);
				echo $pres['tab'];
				
				$export_file .= $pres['excel'];
          				
          		$print_tot_to+=$pres['truck_otot'];
          		$print_tot_tc+=$pres['truck_ctot'];
          		$print_tot_td+=$pres['truck_dtot'];
          		
          		$print_tot_uo+=$pres['unit_otot'];
          		$print_tot_uc+=$pres['unit_ctot'];
          		$print_tot_ud+=$pres['unit_dtot'];        				
          				
				$truck_cntr++;
				
				/*
				if($row['replacement_truck_id'])
				{
					show_my_truck($row['replacement_truck_id'],$truck_cntr,$_POST['cur_date'],$_POST['cur_date_end']);
					$truck_cntr++;	
				}
				*/
			}
		} 
		$export_file .= chr(13);		
		$export_file .= "Truck".chr(9).
			"VIN".chr(9).
			"Tag Number".chr(9).
			"Make".chr(9).
			"Model".chr(9).
			"Year".chr(9).
			"Rent/Lease".chr(9).
			"Original Value".chr(9).
			"Current Value".chr(9).
			"Depreciation".chr(9).
			"GeoTab".chr(9).
			"ELD/Cam Unit".chr(9).
			"Original".chr(9).
			"ELD/Cam Value".chr(9).
			"Depreciation".chr(9);
		$export_file .= chr(13);	
		
		$export_file .= "Truck (and PN Unit) Totals".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "$".number_format($print_tot_to,2)."".chr(9);
		$export_file .= "$".number_format($print_tot_tc,2)."".chr(9);
		$export_file .= "$".number_format($print_tot_td,2)."".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "$".number_format($print_tot_uo,2)."".chr(9);
		$export_file .= "$".number_format($print_tot_uc,2)."".chr(9);
		$export_file .= "$".number_format($print_tot_ud,2)."".chr(9);			
		$export_file .= chr(13);	
		
		
		$print_tot_tlo=0;
		$print_tot_tlc=0;
		$print_tot_tld=0;
		?>
		<tr>
			<td><b>Truck</b></td>
			<td><b>VIN</b></td>
			<td><b>Make</b></td>
			<td><b>Model</b></td>
			<td><b>Year</b></td>
			<td><b>Rent/Lease</b></td>
			<!--<td><b>Attached Driver</b></td>-->
			<td><b>&nbsp;</b></td>
			<td align='right'><b>Original</b></td>
			<td align='right'><b>Current Value</b></td>
			<td align='right'><b>Depreciation</b></td>
			<td align='right'><span title='GeoTab or PeopleNet Tracking enabled if PN displays for each Truck...'><b>GeoTab</b></span></td>
			<td align='right'><b>ELD/Cam Unit</b></td>
			<td align='right'><b>Original</b></td>
			<td align='right'><b>ELD/Cam Value</b></td>
			<td align='right'><b>Depreciation</b></td>
		</tr>
		<tr>
			<td colspan='7'><b>Truck (and GeoTab Unit) Totals</b></td>
			<td align='right'><b><span id='tot_truck_start'>$<?=number_format($print_tot_to,2) ?></span></b></td>
			<td align='right'><b><span id='tot_truck_val'>$<?=number_format($print_tot_tc,2) ?></span></b></td>
			<td align='right'><b><span id='tot_truck_diff'>$<?=number_format($print_tot_td,2) ?></span></b></td>
			<td align='right' colspan='2'><b>&nbsp;</b></td>
			<td align='right'><b><span id='tot_unit_start'>$<?=number_format($print_tot_uo,2) ?></span></b></td>
			<td align='right'><b><span id='tot_unit_val'>$<?=number_format($print_tot_uc,2) ?></span></b></td>
			<td align='right'><b><span id='tot_unit_diff'>$<?=number_format($print_tot_ud,2) ?></span></b></td>
		</tr>
		<? if($mrr_use_styles==0) { ?>
		<tr>
			<td colspan='15'><center><input type='submit' value='Update Values' class='mrr_button_access' name='update_all_values'></center></td>
		</tr>
		<? } ?>
		<tr>
			<td colspan='15'><b>&nbsp;</b></td>
		</tr>
		<tr>
			<td colspan='15'><center><b>TRAILERS</b></center></td>
		</tr>
		<tr>
			<td><b>Trailer Name</b></td>
			<td><b>VIN</b></td>
			<td><b>Nick Name</b></td>
			<td colspan='2'><b>Owner</b></td>
			<td><b>Make</b></td>
			<td><b>Model</b></td>
			<td><b>Year</b></td>
			<!--Interchange---->
			<td><b>Rental Flag</b></td>
			<td align='right'><b>Original</b></td>
			<td align='right'><b>Current Value</b></td>
			<td align='right'><b>Depreciation</b></td>
			<td align='right' colspan='5'><b>&nbsp;</b></td>
		</tr>
		<?
		$export_file .= chr(13);
		$export_file .= "Conard Transportation Trailers".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);	
				
		$export_file .= chr(13);
		$export_file .= "Trailer".chr(9).
			"VIN".chr(9).
			"Tag Number".chr(9).
			"Nick Name".chr(9).
			"Owner".chr(9).
			"Make".chr(9).
			"Model".chr(9).
			"Year".chr(9).
			"Rental Flag".chr(9).
			"Original Value".chr(9).
			"Current Value".chr(9).
			"Depreciation".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);
			//"Interchange".chr(9).
			
		
			//$rowt['license_plate_no']
			
			
			/*
			<?=($rowt['interchange_flag'] ? '<b>Interchange</b>' : '')?>
			*/
		?>
		<? while($rowt = mysqli_fetch_array($datat)) { ?>
			<? if(trim($rowt['license_plate_no'])!="" || 1==1) { ?>			
     			<tr>
     				<td><a href="admin_trailers.php?id=<?=$rowt['id']?>" target='_blank' class='<?=($rowt['equipment_history_entry'] == 0 ? 'alert' : ($rowt['active'] ? '' : 'inactive'))?>'><?=$rowt['trailer_name']?></a></td>
     				<td><?=$rowt['vin']?></td>	
     				<td><?=trim($rowt['nick_name'])?></td>
     				<td colspan='2'><?=$rowt['trailer_owner']?></td>
     				<td><?=$rowt['trailer_make']?></td>
     				<td><?=$rowt['trailer_model']?></td>
     				<td><?=$rowt['trailer_year']?></td>
     				<td><?=($rowt['rental_flag'] ? '<b>Rental</b>' : '')?></td>
     				<?
     					$rvals=mrr_fetch_equip_value_tracked($_POST['cur_date'],0,$rowt['id']);		//second int value is truck ID...always zero here... trailer ID is third arg.
     					
     					//Trailer Value....if not set in sheet.
         					$tres=mrr_fetch_equip_value_acquired($_POST['cur_date'],$_POST['cur_date_end'],2,$rowt['id']);
          				if($rvals['equip_start']=="0.00")		$rvals['equip_start']=$tres['equip_start'];
          				if($rvals['equip_value']=="0.00")		$rvals['equip_value']=$tres['equip_value'];					
          				$dtvalue=$rvals['equip_start'] - $rvals['equip_value'];
          				$tvalue=$rvals['equip_value'];		    					    					
              				$otvalue=$rvals['equip_start']; 	
              				              				
              				$print_tot_tlo+=$otvalue;
						$print_tot_tlc+=$tvalue;
						$print_tot_tld+=$dtvalue; 
						
						//$excel_version=str_replace("&nbsp;"," ",$excel_version);       	
												
						$export_file .= "".trim($rowt['trailer_name'])."".chr(9);
						$export_file .= "".trim($rowt['vin'])."".chr(9);
						$export_file .= "".trim($rowt['license_plate_no'])."".chr(9);
						$export_file .= "".trim($rowt['nick_name'])."".chr(9);
						$export_file .= "".trim($rowt['trailer_owner'])."".chr(9);
						$export_file .= "".trim($rowt['trailer_make'])."".chr(9);	
						$export_file .= "".trim($rowt['trailer_model'])."".chr(9);	
						$export_file .= "".trim($rowt['trailer_year'])."".chr(9);	
							//".($rowt['interchange_flag'] ? 'Interchange' : '')."
						$export_file .= "".($rowt['rental_flag'] ? 'Rental' : '')."".chr(9);
						$export_file .= "$".number_format($otvalue,2)."".chr(9);
						$export_file .= "$".number_format($tvalue,2)."".chr(9);
						$export_file .= "$".number_format($dtvalue,2)."".chr(9);
						$export_file .= "".chr(9);
						$export_file .= "".chr(9);
						$export_file .= "".chr(9);			
						$export_file .= chr(13);									
     				?>				
     				<td align='right'>$<?=number_format($otvalue,2) ?></td>  
     				<td align='right'>
     					<input name='trailer_value_<?=$rowt['id']?>' id='trailer_value_<?=$rowt['id']?>' value="$<?=$tvalue ?>" style='width:75px;text-align:right' class='trailer_value_amnt'>
     					<input name="trailer_id_list[]" value="<?=$rowt['id']?>" type='hidden'>
     					
     					<input name="trailer_ovalue_<?=$rowt['id']?>" id="trailer_ovalue_<?=$rowt['id']?>" value="<?=$otvalue ?>" type='hidden' class='trailer_value_start'>
     					<input name="trailer_dvalue_<?=$rowt['id']?>" id="trailer_dvalue_<?=$rowt['id']?>" value="<?=$dtvalue ?>" type='hidden' class='trailer_value_diff'>
     				</td>
     				<td align='right'>$<?=number_format($dtvalue,2) ?></td>  
     				<td align='right' colspan='5'><b>&nbsp;</b></td>
     			</tr>
			<? } ?>
		<? } ?>
		<?
		$export_file .= chr(13);
		$export_file .= "Trailer".chr(9).
			"VIN".chr(9).
			"Tag Number".chr(9).
			"Nick Name".chr(9).
			"Owner".chr(9).
			"Make".chr(9).
			"Model".chr(9).
			"Year".chr(9).
			"Rental Flag".chr(9).
			"Original Value".chr(9).
			"Current Value".chr(9).
			"Depreciation".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);
		
		//"Interchange".chr(9).
		
		$export_file .= "Trailer Totals".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "$".number_format($print_tot_tlo,2)."".chr(9);
		$export_file .= "$".number_format($print_tot_tlc,2)."".chr(9);
		$export_file .= "$".number_format($print_tot_tld,2)."".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);
		$export_file .= "".chr(9);			
		$export_file .= chr(13);	
		?>
		<tr>
			<td><b>Trailer Name</b></td>
			<td><b>VIN</b></td>
			<td><b>Nick Name</b></td>
			<td colspan='2'><b>Owner</b></td>
			<td><b>Make</b></td>
			<td><b>Model</b></td>
			<td><b>Year</b></td>
			<!--Interchange--->
			<td><b>Rental Flag</b></td>
			<td align='right'><b>Original</b></td>
			<td align='right'><b>Current Value</b></td>
			<td align='right'><b>Depreciation</b></td>
			<td align='right' colspan='5'><b>&nbsp;</b></td>
		</tr>
		<tr>
			<td colspan='9'><b>Trailer Totals</b></td>
			<td align='right'><b><span id='tot_trailer_start'>$<?=number_format($print_tot_tlo,2) ?></span></b></td>
			<td align='right'><b><span id='tot_trailer_val'>$<?=number_format($print_tot_tlc,2) ?></span></b></td>
			<td align='right'><b><span id='tot_trailer_diff'>$<?=number_format($print_tot_tld,2) ?></span></b></td>
			<td align='right' colspan='5'><b>&nbsp;</b></td>
		</tr>
		<? if($mrr_use_styles==0) { ?>
		<tr>
			<td colspan='17'><center><input type='submit' value='Update Values' class='mrr_button_access' name='update_all_values'></center></td>
		</tr>
		<? } ?>
		<?
		$print_tot_gto=($print_tot_to + $print_tot_uo + $print_tot_tlo);
		$print_tot_gtc=($print_tot_tc + $print_tot_uc + $print_tot_tlc);
		$print_tot_gtd=($print_tot_td + $print_tot_ud + $print_tot_tld);
		?>
		<tr>
			<td colspan='17'><b>&nbsp;</b></td>
		</tr>
		<tr>
			<td colspan='9' align='right'><b>SUMMARY</b></td>
			<td align='right'><b>Original</b></td>
			<td align='right'><b>Current Value</b></td>
			<td align='right'><b>Depreciation</b></td>
			<td align='right' colspan='5'><b>&nbsp;</b></td>
		</tr>
		<tr>
			<td colspan='9' align='right'><b>Trucks</b></td>
			<td align='right'><b><span id='disp_tot_truck_start'>$<?=number_format($print_tot_to,2)?></span></b></td>
			<td align='right'><b><span id='disp_tot_truck_val'>$<?=number_format($print_tot_tc,2)?></span></b></td>
			<td align='right'><b><span id='disp_tot_truck_diff'>$<?=number_format($print_tot_td,2)?></span></b></td>
			<td align='right' colspan='5'><b>&nbsp;</b></td>
		</tr>
		<tr>
			<td colspan='9' align='right'><b>PN Units</b></td>
			<td align='right'><b><span id='disp_tot_unit_start'>$<?=number_format($print_tot_uo,2)?></span></b></td>
			<td align='right'><b><span id='disp_tot_unit_val'>$<?=number_format($print_tot_uc,2)?></span></b></td>
			<td align='right'><b><span id='disp_tot_unit_diff'>$<?=number_format($print_tot_ud,2)?></span></b></td>
			<td align='right' colspan='5'><b>&nbsp;</b></td>
		</tr>
		<tr>
			<td colspan='9' align='right'><b>Trailers</b></td>
			<td align='right'><b><span id='disp_tot_trailer_start'>$<?=number_format($print_tot_tlo,2)?></span></b></td>
			<td align='right'><b><span id='disp_tot_trailer_val'>$<?=number_format($print_tot_tlc,2)?></span></b></td>
			<td align='right'><b><span id='disp_tot_trailer_diff'>$<?=number_format($print_tot_tld,2)?></span></b></td>
			<td align='right' colspan='5'><b>&nbsp;</b></td>
		</tr>
		<tr>
			<td colspan='9' align='right'><b>&nbsp;</b></td>
			<td align='right'><b><hr></b></td>
			<td align='right'><b><hr></b></td>
			<td align='right'><b><hr></b></td>
			<td align='right' colspan='5'><b>&nbsp;</b></td>
		</tr>
		<tr>
			<td colspan='9' align='right'><b>ALL Totals</b></td>
			<td align='right'><b><span id='tot_all_start'>$<?=number_format($print_tot_gto,2)?></span></b></td>
			<td align='right'><b><span id='tot_all_val'>$<?=number_format($print_tot_gtc,2)?></span></b></td>
			<td align='right'><b><span id='tot_all_diff'>$<?=number_format($print_tot_gtd,2)?></span></b></td>
			<td align='right' colspan='5'><b>&nbsp;</b></td>
		</tr>
		</table>		
		<?
		$export_file .= chr(13);
		$export_file .= "".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"Summary".chr(9).
			"Original Value".chr(9).
			"Current Value".chr(9).
			"Depreciation".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);
		$export_file .= "".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"Trucks".chr(9).
			"$".number_format($print_tot_to,2)."".chr(9).
			"$".number_format($print_tot_tc,2)."".chr(9).
			"$".number_format($print_tot_td,2)."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);
		$export_file .= "".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"PN Units".chr(9).
			"$".number_format($print_tot_uo,2)."".chr(9).
			"$".number_format($print_tot_uc,2)."".chr(9).
			"$".number_format($print_tot_ud,2)."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);
		$export_file .= "".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"Trailers".chr(9).
			"$".number_format($print_tot_tlo,2)."".chr(9).
			"$".number_format($print_tot_tlc,2)."".chr(9).
			"$".number_format($print_tot_tld,2)."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);
		$export_file .= "".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"ALL TOTALS".chr(9).
			"$".number_format($print_tot_gto,2)."".chr(9).
			"$".number_format($print_tot_gtc,2)."".chr(9).
			"$".number_format($print_tot_gtd,2)."".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
		$export_file .= chr(13);
		?>
	</td>
</tr>
</table>
<input type='hidden' name='excel_output_file' id='excel_output_file' value='<?= ($use_excel > 0 ? "/temp/".$excel_filename."" : "") ?>'><br><br><?= ($use_excel > 0 ? "/temp/".$excel_filename."" : "") ?>
<?
if($use_excel > 0)
{
	$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
	fwrite($fp, $export_file); 
	fclose($fp);
}
?>
</form>
<script type='text/javascript'>
	$('.datepicker').datepicker();
	
	$().ready(function() {
		mrr_calc_trailers();
		mrr_calc_trucks();
		mrr_calc_all();		
	});
	
	$('#main_form').keypress(function(e){
		if (e.which == 13)         e.preventDefault(); // prevent form submission
         	// do stuff with the keypress
         	//$('#main_form').submit(); // now, submit the form
     });
		
	
	function mrrCheckNumeric(mrr)
  	{
    		var mrrstr = mrr;
    		var mrrres = mrrstr.replace(/\,/g,'');
    		return mrrres;
  	}
  	  
	function mrr_calc_all()
	{
		all_val_tot=0;
		all_start_tot=0;
		all_diff_tot=0;
		
		
		//trucks...
		truck_val_tot=parseFloat(get_amount( 		mrrCheckNumeric(   $('#tot_truck_val').html() 	) 	 ));
		truck_start_tot=parseFloat(get_amount( 		mrrCheckNumeric(   $('#tot_truck_start').html() 	) 	 ));
		truck_diff_tot=parseFloat(get_amount( 		mrrCheckNumeric(   $('#tot_truck_diff').html() 	) 	 ));
		
		$('#disp_tot_truck_start').html(formatCurrency(truck_start_tot));
		$('#disp_tot_truck_val').html(formatCurrency(truck_val_tot));
		$('#disp_tot_truck_diff').html(formatCurrency(truck_diff_tot));
		
		all_val_tot+=truck_val_tot;
		all_start_tot+=truck_start_tot;
		all_diff_tot+=truck_diff_tot;
		
		
		//truck PN units
		unit_val_tot=parseFloat(get_amount( 		mrrCheckNumeric(   $('#tot_unit_val').html() 	) 	 ));
		unit_start_tot=parseFloat(get_amount( 		mrrCheckNumeric(   $('#tot_unit_start').html() 	) 	 ));
		unit_diff_tot=parseFloat(get_amount( 		mrrCheckNumeric(   $('#tot_unit_diff').html() 	) 	 ));
		
		$('#disp_tot_unit_start').html(formatCurrency(unit_start_tot));
		$('#disp_tot_unit_val').html(formatCurrency(unit_val_tot));
		$('#disp_tot_unit_diff').html(formatCurrency(unit_diff_tot));
		
		all_val_tot+=unit_val_tot;
		all_start_tot+=unit_start_tot;
		all_diff_tot+=unit_diff_tot;
				
		
		//trailers
		trailer_val_tot=parseFloat(get_amount( 		mrrCheckNumeric(  $('#tot_trailer_val').html() 	) 	));
		trailer_start_tot=parseFloat(get_amount( 	mrrCheckNumeric(  $('#tot_trailer_start').html() 	) 	));
		trailer_diff_tot=parseFloat(get_amount( 	mrrCheckNumeric(  $('#tot_trailer_diff').html() 	) 	));
		
		//alert('Start='+trailer_start_tot+', Val='+trailer_val_tot+', DIFF='+trailer_diff_tot+'.');
				
		$('#disp_tot_trailer_start').html(formatCurrency(trailer_start_tot));
		$('#disp_tot_trailer_val').html(formatCurrency(trailer_val_tot));
		$('#disp_tot_trailer_diff').html(formatCurrency(trailer_diff_tot));
				
		all_val_tot+=trailer_val_tot;
		all_start_tot+=trailer_start_tot;
		all_diff_tot+=trailer_diff_tot;	
		
		
		//display section
		$('#tot_all_val').html(formatCurrency(all_val_tot));
		$('#tot_all_start').html(formatCurrency(all_start_tot));
		$('#tot_all_diff').html(formatCurrency(all_diff_tot));
		
	}
	
	function mrr_calc_trucks()
	{
		/**/
		truck_val_tot=0;
		truck_start_tot=0;
		truck_diff_tot=0;
		
		$(".truck_value_start").each(function() {
 	 			//alert('Value is '+$(this).val()+'.');
 	 			
 	 			truck_start_tot += parseFloat(get_amount($(this).val()));
		});	
		$('#tot_truck_start').html(formatCurrency(truck_start_tot));
		
		$(".truck_value_amnt").each(function() {
 	 			truck_val_tot += parseFloat(get_amount($(this).val()));
		});	
		$('#tot_truck_val').html(formatCurrency(truck_val_tot));
		
		
		$(".truck_value_diff").each(function() {
 	 			truck_diff_tot += parseFloat(get_amount($(this).val()));
		});	
		$('#tot_truck_diff').html(formatCurrency(truck_diff_tot));
		
		
		unit_val_tot=0;
		unit_start_tot=0;
		unit_diff_tot=0;
		
		$(".unit_value_start").each(function() {
 	 			//alert('Value is '+$(this).val()+'.');
 	 			
 	 			unit_start_tot += parseFloat(get_amount($(this).val()));
		});	
		$('#tot_unit_start').html(formatCurrency(unit_start_tot));
		
		$(".unit_value_amnt").each(function() {
 	 			unit_val_tot += parseFloat(get_amount($(this).val()));
		});	
		$('#tot_unit_val').html(formatCurrency(unit_val_tot));
		
		
		$(".unit_value_diff").each(function() {
 	 			unit_diff_tot += parseFloat(get_amount($(this).val()));
		});	
		$('#tot_unit_diff').html(formatCurrency(unit_diff_tot));
		
	}
	
	function mrr_calc_trailers()
	{
		/**/
		trailer_val_tot=0;
		trailer_start_tot=0;
		trailer_diff_tot=0;
		
		$(".trailer_value_start").each(function() {
 	 			//alert('Value is '+$(this).val()+'.');
 	 			
 	 			trailer_start_tot += parseFloat(get_amount($(this).val()));
		});	
		$('#tot_trailer_start').html(formatCurrency(trailer_start_tot));
		
		$(".trailer_value_amnt").each(function() {
 	 			trailer_val_tot += parseFloat(get_amount($(this).val()));
		});	
		$('#tot_trailer_val').html(formatCurrency(trailer_val_tot));
		
		
		$(".trailer_value_diff").each(function() {
 	 			trailer_diff_tot += parseFloat(get_amount($(this).val()));
		});	
		$('#tot_trailer_diff').html(formatCurrency(trailer_diff_tot));
		
	}
	
</script>
<?
$pdf = ob_get_contents();
ob_end_clean();

$prefix="";
if($use_excel > 0) 
{
	$prefix="<br><br><a href=\"http://trucking.conardlogistics.com/temp/".$excel_filename."\" target='_blank'>Click for Excel Version</a><br><br>";
	echo $prefix;
}
echo $pdf;
		
if(trim($_POST['mrr_email_addr'])!="" && isset($_POST['mrr_email_report']))
{
	$user_name=$defaultsarray['company_name'];
	$From=$defaultsarray['company_email_address'];
	$Subject="";
	if(isset($use_title))			$Subject=$use_title;
	elseif(isset($usetitle))			$Subject=$use_title;
	
	$pdf=str_replace(" href="," name=",$pdf);
	//$pdf=str_replace("</a>","",$pdf); 
		
	$sentit=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject, $prefix.$pdf , $prefix.$pdf);
	
	$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
	echo "<br><br><b>This report has ".$sent_msg." to '".$_POST['mrr_email_addr_name']."' at E-Mail address '".$_POST['mrr_email_addr']."'.</b><br><br>";
	
	if(trim($_POST['mrr_email_addr'])!="" && trim($_POST['mrr_email_addr'])!="michael@sherrodcomputers.com")
	{
		//$sentit=mrr_trucking_sendMail('dconard@conardtransportation.com',"Dale Conard",$From,$user_name,'','',$Subject,$pdf,$pdf);
		$sentit=mrr_trucking_sendMail('jgriffith@conardtransportation.com',"James Griffith",$From,$user_name,'','',$Subject,$pdf,$pdf);
		//$sentit=mrr_trucking_sendMail('amassar@conardtransportation.com',"Anthony Massar",$From,$user_name,'','',$Subject,$pdf,$pdf);     		
	}
}
?>
<? include('footer.php') ?>
