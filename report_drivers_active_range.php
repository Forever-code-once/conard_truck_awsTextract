<? include('header.php') ?>
<?
	if(isset($_POST['driver_id'])) 
	{
		$_POST['build_report'] = 1;
	}

	$rfilter = new report_filter();
	$rfilter->show_driver 		= true;
	$rfilter->show_employers 	= true;
	$rfilter->show_active		= true;
	//$rfilter->mrr_driver_mode	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

	
	$uuid = createuuid();
	$excel_filename = "driver_range_$uuid.xls";
	$export_file = "";
	$use_excel=1;
	
	$now="2014-01-01 00:00:00";
	
		if(isset($_POST['build_report'])) {
			
			$export_file .= "Driver Active Details".chr(9).
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
     			"".chr(9).
     			"".chr(9).
     			"".chr(9).
     			"".chr(9);
     		$export_file .= chr(13);	
     		
     		$export_file .= "ID".chr(9).
     			"First Name".chr(9).
     			"Last Name".chr(9).
     			"Cell Phone".chr(9).
     			"Currently Active".chr(9).
     			"Hired".chr(9).
     			"Terminated".chr(9).
     			"Rehired".chr(9).
     			"Terminated".chr(9).
     			"CPM".chr(9).
     			"CBM".chr(9).
     			"PPH".chr(9).
     			"CPH".chr(9).
     			"CPMT".chr(9).
     			"CBMT".chr(9).
     			"PPHT".chr(9).
     			"CPHT".chr(9).
     			"Last Raise".chr(9);
     		$export_file .= chr(13);	
			
			
			echo "
				<div style='float:left;margin:0 0 10px 30px' class='section_heading'>Driver Active Details</div>
				<div style='clear:both'></div>				
				<table class='tablesorter font_display_section' style='margin:0 10px;width:1600px;text-align:left'>
				<thead>
				<tr>
					<th>ID</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Cell Phone</th>
					<th align='right'>Currently Active</th>
					<th align='right'>Hired</th>
					<th align='right'>Terminated</th>
					<th align='right' title='Last date rehired.'>Rehired</th>
					<th align='right' title='Last date terminated after being rehired.'>Terminated</th>						
					<th align='right'>CPM</th>
					<th align='right'>CBM</th>
					<th align='right'>PPH</th>
					<th align='right'>CPH</th>	
					<th align='right'>CPMT</th>					
					<th align='right'>CBMT</th>					
					<th align='right'>PPHT</th>
					<th align='right'>CPHT</th>
					<th align='right' nowrap title='Last payroll change (pay raise) date.'>Last Raise</th>
					<!--
					<th align='right'>Per Mile</th>
					<th align='right'>Per Hour</th>						
					<th align='right'>Team Mile</th>
					<th align='right'>Team Hourly</th>
					-->
				</tr>
				</thead>
				<tbody>
			";
			
			$cntr=0;
			$acntr=0;
			$icntr=0;
			
			if(!isset($_POST['report_active']))		$_POST['report_active']=0;
						
			//".($_POST['date_from'] != '' ? " and drivers_expenses.linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))."' " : "")."
			//".($_POST['date_to'] != '' ? " and drivers_expenses.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))."' " : "")."
			$sql = "
				select drivers.*,
					(
						select driver_payroll_changes.linedate
						from driver_payroll_changes
						where driver_payroll_changes.driver_id=drivers.id
						order by driver_payroll_changes.linedate desc
						limit 1
					) as linedate_raise,
					(select option_values.fname from option_values where option_values.id=drivers.employer_id) as emp_name,
					(select option_values.fvalue from option_values where option_values.id=drivers.employer_id) as emp_value 
				
				from drivers
				where drivers.deleted = 0
					and drivers.id!=405
					and drivers.id!=345
					".($_POST['report_active'] > 0 ? " and drivers.active > 0" : "")."
					".($_POST['driver_id'] > 0 ? " and drivers.id = '".sql_friendly($_POST['driver_id'])."' " : "")."
					".($_POST['employer_id'] > 0 ? " and drivers.employer_id = '".sql_friendly($_POST['employer_id'])."' " : "")."		
					and (					
						(	
							drivers.linedate_started<='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00' 
							and drivers.linedate_started!='0000-00-00 00:00:00'
							and (																	
									drivers.linedate_terminated>='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59' 
									or drivers.linedate_terminated='0000-00-00 00:00:00'
								)								
						)
						or
						(
							drivers.linedate_rehire<='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00' 
							and drivers.linedate_rehire!='0000-00-00 00:00:00' 
							and (																	
									drivers.linedate_refire>='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59' 
									or drivers.linedate_refire='0000-00-00 00:00:00'
								)
						)					
					)							
				order by 
					drivers.name_driver_last asc,
					drivers.name_driver_first asc										
			";		//drivers.active > 0
			$data = simple_query($sql);			
			while($row = mysqli_fetch_array($data)) 
			{						
     			
     			$valid=1;
     			
     			if($row['active']==0 && ($row['linedate_terminated']=="0000-00-00 00:00:00" && $row['linedate_started'] < $now) && ($row['linedate_refire']=="0000-00-00 00:00:00" && $row['linedate_rehire'] < $now))	
     			{
     				$valid=0;
     			}
     			if($row['active']==0  && (!isset($row['linedate_raise']) || $row['linedate_raise']=="0000-00-00 00:00:00"))			$valid=0;
     			
     			/*     			
     			if($_POST['mrr_report_driver_mode']==1)
     			{	//hired only
     				if(
     					(strtotime($row['linedate_started'])>=strtotime(trim($_POST['date_from']." 00:00:00"))  &&  strtotime($row['linedate_started'])<=strtotime(trim($_POST['date_to']." 23:59:59"))  )
     					|| 
     					(strtotime($row['linedate_rehire'])>=strtotime(trim($_POST['date_from']." 00:00:00"))  &&  strtotime($row['linedate_rehire'])<=strtotime(trim($_POST['date_to']." 23:59:59")) && $row['linedate_rehire']!="0000-00-00 00:00:00")
     				)
     				{
     					//valid...hired or rehired in date range.  :)
     				}
     				else
     				{
     					$valid=0;		//not hired or rehired in date range...no valid.
     				}     				
     			}
     			if($_POST['mrr_report_driver_mode']==2)
     			{	//fired only
     				if(
     					(strtotime($row['linedate_terminated'])>=strtotime(trim($_POST['date_from']." 00:00:00"))  &&  strtotime($row['linedate_terminated'])<=strtotime(trim($_POST['date_to']." 23:59:59")) && $row['linedate_terminated']!="0000-00-00 00:00:00" )
     					|| 
     					(strtotime($row['linedate_refire'])>=strtotime(trim($_POST['date_from']." 00:00:00"))  &&  strtotime($row['linedate_refire'])<=strtotime(trim($_POST['date_to']." 23:59:59")) && $row['linedate_refire']!="0000-00-00 00:00:00" )
     				)
     				{
     					//valid...fired or refired in date range.  :)
     				}
     				else
     				{
     					$valid=0;		//not fired or refired in date range...no valid.
     				} 
     			}
     			if($_POST['mrr_report_driver_mode']==3)
     			{	//hired or fired
     				if(
     					(strtotime($row['linedate_started'])>=strtotime(trim($_POST['date_from']." 00:00:00"))  &&  strtotime($row['linedate_started'])<=strtotime(trim($_POST['date_to']." 23:59:59"))  )
     					|| 
     					(strtotime($row['linedate_rehire'])>=strtotime(trim($_POST['date_from']." 00:00:00"))  &&  strtotime($row['linedate_rehire'])<=strtotime(trim($_POST['date_to']." 23:59:59")) && $row['linedate_rehire']!="0000-00-00 00:00:00")
     					||
     					(strtotime($row['linedate_terminated'])>=strtotime(trim($_POST['date_from']." 00:00:00"))  &&  strtotime($row['linedate_terminated'])<=strtotime(trim($_POST['date_to']." 23:59:59")) && $row['linedate_terminated']!="0000-00-00 00:00:00" )
     					|| 
     					(strtotime($row['linedate_refire'])>=strtotime(trim($_POST['date_from']." 00:00:00"))  &&  strtotime($row['linedate_refire'])<=strtotime(trim($_POST['date_to']." 23:59:59")) && $row['linedate_refire']!="0000-00-00 00:00:00" )
     				)
     				{
     					//valid...hired/fired or rehired/refired in date range.  :)
     				}
     				else
     				{
     					$valid=0;		//not hired/fired or rehired/refired in date range...no valid.
     				} 
     			}
     			*/
     			
     			if($valid > 0)
     			{
     				if($row['active']==0)	
     				{
     					$color="AAAAAA";
     					$actor="No";
     					$icntr++;
     				}
     				else
     				{
     					$color="000000";	
     					$actor="Yes";
     					$acntr++;
     				}
     				
     				if(!isset($row['linedate_raise']) || $row['linedate_raise']=="0000-00-00 00:00:00")		$row['linedate_raise']="";
     				
     				echo "
     					<tr>
     						<td><a href='admin_drivers.php?id=".$row['id']."' target='view_driver_".$row['id']."'>".$row['id']."</a></td>
     						<td style='color:#".$color."'>".$row['name_driver_first']."</td>
     						<td style='color:#".$color."'>".$row['name_driver_last']."</td>
     						<td style='color:#".$color."'>".$row['phone_cell']."</td>
     						<td style='color:#".$color."' align='right'>".$actor."</td>
     						<td style='color:#".$color."' align='right'>".(($row['linedate_started']!="" && $row['linedate_started']!="0000-00-00 00:00:00") 		? date("m-d-Y", strtotime($row['linedate_started'])) : "&nbsp;")."</td>
     						<td style='color:#".$color."' align='right'>".(($row['linedate_terminated']!="" && $row['linedate_terminated']!="0000-00-00 00:00:00") ? date("m-d-Y", strtotime($row['linedate_terminated'])) : "&nbsp;")."</td>
     						<td style='color:#".$color."' align='right'>".(($row['linedate_rehire']!="" && $row['linedate_rehire']!="0000-00-00 00:00:00") 		? date("m-d-Y", strtotime($row['linedate_rehire'])) : "&nbsp;")."</td>
     						<td style='color:#".$color."' align='right'>".(($row['linedate_refire']!="" && $row['linedate_refire']!="0000-00-00 00:00:00") 		? date("m-d-Y", strtotime($row['linedate_refire'])) : "&nbsp;")."</td>						
     						<td style='color:#".$color."' align='right'>$".number_format($row['pay_per_mile'],3)."</td>
     						<td style='color:#".$color."' align='right'>$".number_format($row['charged_per_mile'],3)."</td>
     						<td style='color:#".$color."' align='right'>$".number_format($row['pay_per_hour'],3)."</td>
     						<td style='color:#".$color."' align='right'>$".number_format($row['charged_per_hour'],3)."</td>					
     						<td style='color:#".$color."' align='right'>$".number_format($row['pay_per_mile_team'],3)."</td>
     						<td style='color:#".$color."' align='right'>$".number_format($row['charged_per_mile_team'],3)."</td>
     						<td style='color:#".$color."' align='right'>$".number_format($row['pay_per_hour_team'],3)."</td>
     						<td style='color:#".$color."' align='right'>$".number_format($row['charged_per_hour_team'],3)."</td>
     						
     						<td style='color:#".$color."' align='right'>".(($row['linedate_raise']!="" && $row['linedate_raise']!="0000-00-00 00:00:00") 		? date("m-d-Y", strtotime($row['linedate_raise'])) : "&nbsp;")."</td>
     						<!--
     						<td style='color:#".$color."' align='right'>$".number_format($row['charged_per_mile'],3)."</td>
     						<td style='color:#".$color."' align='right'>$".number_format($row['charged_per_hour'],3)."</td>
     						<td style='color:#".$color."' align='right'>$".number_format($row['charged_per_mile_team'],3)."</td>
     						<td style='color:#".$color."' align='right'>$".number_format($row['charged_per_hour_team'],3)."</td>
     						-->
     					</tr>
     				";
     				
     				$export_file .= "".$row['id']."".chr(9).
               			"".$row['name_driver_first']."".chr(9).
               			"".$row['name_driver_last']."".chr(9).
               			"".$row['phone_cell']."".chr(9).
               			"".$actor."".chr(9).
               			"".(($row['linedate_started']!="" && $row['linedate_started']!="0000-00-00 00:00:00") 		? date("m-d-Y", strtotime($row['linedate_started'])) : "")."".chr(9).
               			"".(($row['linedate_terminated']!="" && $row['linedate_terminated']!="0000-00-00 00:00:00") 	? date("m-d-Y", strtotime($row['linedate_terminated'])) : "")."".chr(9).
               			"".(($row['linedate_rehire']!="" && $row['linedate_rehire']!="0000-00-00 00:00:00") 			? date("m-d-Y", strtotime($row['linedate_rehire'])) : "")."".chr(9).
               			"".(($row['linedate_refire']!="" && $row['linedate_refire']!="0000-00-00 00:00:00") 			? date("m-d-Y", strtotime($row['linedate_refire'])) : "")."".chr(9).
               			"$".number_format($row['pay_per_mile'],3)."".chr(9).
               			"$".number_format($row['charged_per_mile'],3)."".chr(9).
               			"$".number_format($row['pay_per_hour'],3)."".chr(9).
               			"$".number_format($row['charged_per_hour'],3)."".chr(9).
               			"$".number_format($row['pay_per_mile_team'],3)."".chr(9).
               			"$".number_format($row['charged_per_mile_team'],3)."".chr(9).
               			"$".number_format($row['pay_per_hour_team'],3)."".chr(9).
               			"$".number_format($row['charged_per_hour_team'],3)."".chr(9).
               			"".(($row['linedate_raise']!="" && $row['linedate_raise']!="0000-00-00 00:00:00") 			? date("m-d-Y", strtotime($row['linedate_raise'])) : "")."".chr(9);
          			$export_file .= chr(13);					
     				
     				$cntr++;				
				}
			}
		echo "
			</tbody>
			</table>
			<br>".$cntr." Active Drivers Found within Range {".date("m/d/Y",strtotime($_POST['date_from']))." - ".date("m/d/Y",strtotime($_POST['date_to']))."}
			
		";	//<br><br><b>Query:</b> <br>".$sql."<br><br>		// (<b>".$acntr." Active</b> and ".$icntr." Inactive).
		
		// (".$acntr." Active and ".$icntr." Inactive).
		
		$export_file .= chr(13);	
		$export_file .= chr(13);	
		$export_file .= "".$cntr." Active Drivers Found within Range {".date("m/d/Y",strtotime($_POST['date_from']))." - ".date("m/d/Y",strtotime($_POST['date_to']))."}".chr(9).
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
     			"".chr(9).
     			"".chr(9).
     			"".chr(9).
     			"".chr(9);
		$export_file .= chr(13);	
		
		$prefix="";
		if($use_excel > 0) 
		{
			$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
			fwrite($fp, $export_file); 
			fclose($fp);
			
			$prefix="<br><br><a href=\"http://trucking.conardlogistics.com/temp/".$excel_filename."\" target='_blank'>Click for Excel Version</a><br><br>";
			echo $prefix;
		}
	}			
?>
<!--
<input type='hidden' name='excel_output_file' id='excel_output_file' value='<?= ($use_excel > 0 ? "/temp/".$excel_filename."" : "") ?>'><br><br><?= ($use_excel > 0 ? "/temp/".$excel_filename."" : "") ?>
-->
<script type='text/javascript'>
	$('.tablesorter').tablesorter();     
</script>
<? include('footer.php') ?>