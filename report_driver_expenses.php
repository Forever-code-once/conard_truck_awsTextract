<? include('header.php') ?>
<?

	if(isset($_POST['driver_id'])) {
		$_POST['build_report'] = 1;
	}

	$rfilter = new report_filter();
	$rfilter->show_driver 		= true;
	$rfilter->show_employers 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();
?>


	<?
		if(isset($_POST['build_report'])) {
			echo "
				<div style='float:left;margin:0 0 10px 30px' class='section_heading'>Driver Expense Report</div>
				<div style='clear:both'></div>
				
				<div style='float:left;margin:0 0 10px 30px'>
					<a name='to_top' href='#to_bottom'>Summary</a> &nbsp; &nbsp; &nbsp;
					<b>Display Types: </b> &nbsp; &nbsp; &nbsp;					
					<span class='mrr_link_like_on' onClick='mrr_show_group(\"comcheck\");'>Comcheck</span> &nbsp; &nbsp; &nbsp;
					<span class='mrr_link_like_on' onClick='mrr_show_group(\"layover\");'>Layover</span> &nbsp; &nbsp; &nbsp;
					<span class='mrr_link_like_on' onClick='mrr_show_group(\"misc\");'>Misc</span> &nbsp; &nbsp; &nbsp;
					<span class='mrr_link_like_on' onClick='mrr_show_group(\"stop_off\");'>Stop Off</span> &nbsp; &nbsp; &nbsp;
					<span class='mrr_link_like_on' onClick='mrr_show_group(\"\");'>All</span>
					
				</div>
				<div style='clear:both'></div>
				
				<table class='tablesorter font_display_section' style='margin:0 10px;width:1200px;text-align:left'>
				<thead>
				<tr>
					<th>Driver</th>
					<th>Type</th>
					<th>Account</th>
					<th>Date</th>
					<th align='right'>Cost</th>
					<th align='right'>Billable</th>
					<th>Description</th>
				</tr>
				</thead>
				<tbody>
			";
			$sql = "
					select drivers_expenses.amount,
						drivers_expenses.billable,
						drivers_expenses.chart_id,
						drivers_expenses.desc_long,
						drivers_expenses.linedate,
						option_values.fvalue as expense_type,
						drivers.name_driver_first,
						drivers.name_driver_last
					
					from drivers_expenses
						left join option_values on option_values.id = drivers_expenses.expense_type_id
						left join drivers on drivers.id = drivers_expenses.driver_id
					where drivers_expenses.deleted = 0
						".($_POST['driver_id'] > 0 ? " and drivers_expenses.driver_id = '".sql_friendly($_POST['driver_id'])."' " : "")."
						".($_POST['employer_id'] > 0 ? " and drivers.employer_id = '".sql_friendly($_POST['employer_id'])."' " : "")."
						".($_POST['date_from'] != '' ? " and drivers_expenses.linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))."' " : "")."
						".($_POST['date_to'] != '' ? " and drivers_expenses.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))."' " : "")."
					
				union all
				
					select expense_amount,
						0,
						expense_desc,
						dispatch_expenses.linedate_added,
						dispatch_expenses.chart_id as dispatch_chart_id,
						option_values.fvalue as expense_type,
						drivers.name_driver_first,
						drivers.name_driver_last
						
					from dispatch_expenses
						left join option_values on option_values.id = dispatch_expenses.expense_type_id
						left join trucks_log on trucks_log.id = dispatch_expenses.dispatch_id
						left join drivers on drivers.id = trucks_log.driver_id
						
					where trucks_log.deleted = 0
						and dispatch_expenses.deleted = 0
						".($_POST['driver_id'] > 0 ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."') " : "")."
						".($_POST['employer_id'] > 0 ? " and drivers.employer_id = '".sql_friendly($_POST['employer_id'])."' " : "")."
						".($_POST['date_from'] != '' ? " and dispatch_expenses.linedate_added >= '".date("Y-m-d", strtotime($_POST['date_from']))."' " : "")."
						".($_POST['date_to'] != '' ? " and dispatch_expenses.linedate_added <= '".date("Y-m-d", strtotime($_POST['date_to']))."' " : "")."
					
				order by linedate desc
			";
			$data = simple_query($sql);

			$cost = 0;
			$billable = 0;
			while($row = mysqli_fetch_array($data)) {
				
				$cost += $row['amount'];
				$billable += $row['billable'];
				
				$account="";
				if($row['chart_id'] > 0)
				{
					$results=mrr_get_coa_list($row['chart_id'],'');		//67000	//first arg is $chart_id, second arg is $chart_number	
		
                    	foreach($results as $key2 => $value2 )
                    	{
                    		if($key2=="ChartEntry")
                    		{
                         		foreach($value2 as $key => $value )
                    			{         		
                              		$prt=trim($key);		$tmp=trim($value);
                              		//if($prt=="ID")		$chart_id=$tmp;
                              		if($prt=="Name")		$account=$tmp;
                              		//if($prt=="Number")	$chart_acct=$tmp;                              		
                         		}//end for loop for each chart entry
                    		}//end if
                    	}//end for loop for each result returned
				}//end if ID check		$row['chart_id']
				
				echo "
					<tr id='driver_expense_entry_$row[id]' class='".str_replace(" ","_",strtolower(trim($row['expense_type'])))." all_types'>
						<td><a href='admin_drivers.php?id=$row[driver_id]' target='view_driver_$row[driver_id]'>$row[name_driver_last], $row[name_driver_first]</a></td>
						<td>$row[expense_type]</td>
						<td>".$account."</td>
						<td>".date("m-d-Y", strtotime($row['linedate']))." (".date("M j, Y", strtotime($row['linedate'])).")</td>
						<td align='right' nowrap>
							<span class='".str_replace(" ","_",strtolower(trim($row['expense_type'])))."_amount' style='display:none;'>".$row['amount']."</span>
							&nbsp;&nbsp;&nbsp; $".money_format('',$row['amount'])."
						</td>
						<td align='right' nowrap>&nbsp;&nbsp;&nbsp; $".money_format('',$row['amount_billable'])."</td>
						<td>$row[desc_long]</td>
					</tr>
				";
			}
		echo "
			</tbody>
			<tr>
				<td colspan='4'>".mysqli_num_rows($data)." result(s)</td>
				<td align='right'>Total Cost: $".money_format('', $cost)."</td>
				<td align='right'>Total Billable: $".money_format('', $billable)."</td>
				<td colspan='5'>&nbsp;</td>
			</tr>
			<tr>
				<td colspan='4'>Displayed Total <input type='hidden' id='selected_tot_all' name='selected_tot_all' value='".money_format('', $cost)."'></td>
				<td align='right'>$<span id='selected_tot'>".money_format('', $cost)."</span></td>
				<td align='right'>&nbsp;</td>
				<td colspan='5'>&nbsp;</td>
			</tr>
			</table>	
			<br><center><a name='to_bottom' href='#to_top'>Back to Top</a></center>
		";
	}	
?>

	
	
<script type='text/javascript'>
	$('.tablesorter').tablesorter({
		headers: { 
			3: {sorter:'currency'},
			4: {sorter:'currency'}
		}
        				
     });
     
     function mrr_show_group(grpname)
     {
     	myval=$('#selected_tot_all').val();
     	
     	if(grpname!="")
     	{
     		$('.all_types').hide();
     		$('.'+grpname+'').show();
     		
     		mytot=0;
     		
     		$('.'+grpname+'_amount').each(function() {     		
     			line_applied = get_amount($(this).html());
     			mytot+=parseFloat(line_applied);
     		});
     		
     		$('#selected_tot').html(mytot);	
     	}
     	else
     	{
     		$('.all_types').show();	
     		
     		
     		$('#selected_tot').html(myval);	
     	}     		
     }
     
</script>
<? include('footer.php') ?>