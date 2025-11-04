<?
ini_set("max_input_vars","20000");	//must change in INI file... 
//date_default_timezone_set("America/Chicago");

$usetitle = "Report - Comparison";
$use_title = "Report - Comparison";
$mrr_tab="";
?>
<? include('header.php') ?>
<?	
	//mrr_add_print_ability_conard('printable_area1', $use_title);
		
	//if(isset($_GET['driver_id'])) {	$_POST['driver_id'] = $_GET['driver_id'];	$_POST['build_report'] = 1;	}
	
	if(isset($_GET['date_from']))	
	{
		$_GET['date_from']=str_replace("_","/",$_GET['date_from']);	
		$_POST['date_from']=$_GET['date_from'];
		
		$_POST['build_report']=1;
	}
	if(isset($_GET['date_to']))		
	{
		$_GET['date_to']=str_replace("_","/",$_GET['date_to']);
		$_POST['date_to']=$_GET['date_to'];
				
		$_POST['build_report']=1;
	}
	if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("n/j/Y", time());
	//if(!isset($_POST['driver_id'])) $_POST['driver_id'] = 0;
	//if(!isset($_POST['employer_id'])) $_POST['employer_id'] = 0;
		
	$quick_links_reporting=mrr_get_month_parts(6);
			
	$truck_id=0;	
	$trailer_id=0;	
	
	
	//get chart of account listing......................................................................................
	if(!isset($_POST['mrr_chart_id']))		$_POST['mrr_chart_id']=0;
	$sel_chart="";						if($_POST['mrr_chart_id']==0)	$sel_chart=" selected";
		
	$results=mrr_get_coa_list('','');		//first arg is $chart_id, second arg is $chart_number	
		
	$budget_val_fuel=0;
	$budget_val_insur=0;
	$budget_val_labor=0;
	$budget_val_truck_maint=0;
	$budget_val_tires=0;
	$budget_val_trailer_maint=0;
	$budget_val_truck_rental=0;
	$budget_val_truck_lease=0;
	$budget_val_miles_exp=0;	
	$budget_val_admin_exp=0;
	$budget_val_misc_exp=0;
	$budget_val_misc_exp_mile=0;
	$budget_val_trailer_rental=0;
	$budget_val_accidents=0;	
	
	$budget_val_trailer_miles_exp=0;
		
	$coa_cntr=0;
	$coa_name[0]="";
	$coa_numb[0]="";	
	$oa_group[0]="";	
	
	$chart_id=0;			$chart_acct="";		$chart_name="";   		$group="";	
	foreach($results as $key2 => $value2 )
	{
		if($key2=="ChartEntry")
		{
     		foreach($value2 as $key => $value )
			{         		
          		$prt=trim($key);		$tmp=trim($value);
          		if($prt=="ID")			$chart_id=$tmp;
          		if($prt=="Name")		$chart_name=$tmp;
          		if($prt=="Number")		$chart_acct=$tmp;
          		
          		if($chart_id > 0 && $chart_acct!="" && $chart_name!="")
          		{
          			$group=$chart_acct;
          			if(strlen($chart_acct) > 5)	$group=substr($chart_acct,0,5);
          			
          			$chart_name=str_replace("&"," and ",$chart_name);
          			
          			$coa_name[$coa_cntr]="".$chart_name."";
					$coa_numb[$coa_cntr]="".$chart_acct."";	
					$coa_group[$coa_cntr]="".$group."";
					$coa_cntr++;       
					
					$chart_id=0;			$chart_acct="";		$chart_name="";   		$group="";			
          		}
     		}//end for loop for each chart entry
		}//end if
	}//end for loop for each result returned
	//...................................................................................................................
	
	$javascript_array_values2="";
	$mrr_output="<br><table cellpadding='0' cellspacing='0' border='0'>";
	for($xx=0;$xx < $coa_cntr; $xx++)
	{
		$mrr_output.="
			<tr class='".$coa_group[$xx]."'>
				<td valign='top'>".$coa_group[$xx]."</td>
				<td valign='top'>".$coa_name[$xx]."</td>
				<td valign='top'>".$coa_numb[$xx]."</td>
			</tr>
		";
		
		$javascript_array_values2.="
     		mrr_coa_names[".$xx ."]=\"". $coa_name[$xx]  ."\"; 
     		mrr_coa_numbs[".$xx ."]='". $coa_numb[$xx]  ."'; 
     		mrr_coa_group[".$xx ."]='". $coa_group[$xx] ."'; 
     		";
		
	}
	$mrr_output.="</table></br>";
	//echo $mrr_output;
		
     $cres=mrr_pull_timesheet_labor_cost($_POST['date_from'],$_POST['date_to'],1601);     //$cres['tot']		$cres['sql']		$cres['rep']
     $vres=mrr_pull_timesheet_labor_cost($_POST['date_from'],$_POST['date_to'],1687);	//$vres['tot']		$vres['sql']		$vres['rep']
     
     $timesheets_carlex=$cres['tot'];
     $timesheets_vietti=$vres['tot'];
     
     $sales_carlex=mrr_pull_acct_sales_invoice_by_cust_timesheet_v2($_POST['date_from'],$_POST['date_to'],1601);
     //$sales_html=mrr_pull_acct_sales_invoice_by_cust_timesheet($_POST['date_from'],$_POST['date_to'],1601,1);
     $sales_vietti=mrr_pull_acct_sales_invoice_by_cust_timesheet_v2($_POST['date_from'],$_POST['date_to'],1687);
          
	
	$mile_capture=mrr_truck_odometer_display($_POST['date_from'],$_POST['date_to']);
	$mrr_miles_moved=$mile_capture['tot'];
	$mrr_miles_adjust=$mile_capture['alt'];
	$mrr_miles_html=$mile_capture['html'];
	
	$res=get_active_truck_count_ranged($_POST['date_from'],$_POST['date_to']);
	$report_title="Comparison Report: ".$_POST['date_from']." thru ".$_POST['date_to'].".";	
	ob_start();
	
	?>
	<?= $quick_links_reporting ?>
	<table border=0>
	<tr>
		<td valign='top' align='left'>		
     	<?     	    	
     	$rfilter = new report_filter();
     	//$rfilter->show_driver 			= true;
     	//$rfilter->show_employers 		= true;
     	//$rfilter->summary_only	 		= true;
     	//$rfilter->team_choice	 		= true;
     	//$rfilter->show_font_size		= true;
     	$rfilter->mrr_special_print_button	= true;
     	$rfilter->show_filter();     	
     	?>
     	<center>
     	<div id='mrr_display_results' onClick='mrr_display_results_toggler();' class='mrr_link_like_on'><b>Show Table Summaries</b></div>
     	What is this <?= show_help('report_comparison.php','Show Details') ?>
		</center>
     	<div id='mrr_graph_maker_2'></div>
     	<div id='mrr_graph_maker_2a' style='display:none;'></div>
     	<div style='border:#000000 solid 1px; background-color:#FFFFFF;'>
     		<div id='mrr_quick_summary' style='margin-left:5px; margin-right:25px; margin-top:5px; margin-bottom:0;'>
     	<?     		
     		echo "
     		<center><b>".$_POST['date_from']." - ".$_POST['date_to']."</b></center>
     		<table width='100%'>
     		<tr>	
     			<td align='left'><b>Current</b></td>
     			<td align='right'><b>Trucks</b></td>
     			<td align='right'>".get_active_truck_count()."</td>
     			<td align='right'><b>Trailers</b></td>
     			<td align='right'>".get_active_trailer_count()."</td>
     			<td align='right'><b>Daily Cost</b></td>
     			<td align='right'>$".number_format(get_daily_cost(),2)."</td>
     		</tr>
     		</table>
     		";   		
     		
     		//echo "<br>All Trucks=".$res['trucks'].".";
     		//echo "<br>Billable Trucks=".$res['billable'].".";
     		//echo "<br>Replacements=".$res['replaced'].".";
     		//echo "<br>Value $".number_format($res['total_value'],2).".";
     		//echo "<br>M Value $".number_format($res['monthly_value'],2).".";
     	?>
     		</div>
     		<div id='mrr_calculations' style='margin-left:5px; margin-right:5px; margin-top:0; margin-bottom:5px;'>
     		</div>
     	</div>
     	
     	<center>
     	<div><b>&nbsp;</b></div>
		</center>
		</td>
		<td valign='top'>
			<!--<div id='mrr_graph_maker'></div>-->
	<?
	//echo $res['sql'];
	//=======================================================================================================================================================================================================
		$mrr_from_date=date("Y-m-d", strtotime($_POST['date_from']));
		$mrr_to_date=date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])));
		
		$mrr_from_date2=date("Y-m-d", strtotime("-15 day", strtotime($_POST['date_from'])));
		$mrr_to_date2=date("Y-m-d", strtotime("15 day", strtotime($_POST['date_to'])));
		
		$search_date_range = "
				and load_handler.linedate_pickup_eta >= '".$mrr_from_date."'
				and load_handler.linedate_pickup_eta < '".$mrr_to_date."'
			";
		$search_date_range2 = "
				and trucks_log.linedate_pickup_eta >= '".$mrr_from_date."'
				and trucks_log.linedate_pickup_eta < '".$mrr_to_date."'
			";
		$driver_search = "";
			//and trucks_log.linedate_pickup_eta >= '2012-01-01 00:00:00' 
			//and trucks_log.linedate_pickup_eta <= '2012-01-31 23:59:59' 
		
		$active_truck_cntr_for_avg=0;
		$active_truck_cntr_for_loads=0;
		$mrr_rep_load_id=0;
		
			//DISTINCT(load_handler.id) AS mrr_unique_id,
		$sql = "
			select 
				load_handler.*,
				customers.name_company,
				(load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount) as actual_bill_customer,
				load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount - load_handler.actual_total_cost as load_profit,
				(select ifnull(sum(trucks_log.driver2_id),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as driver_cnt,
				
				(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
				(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead,			
				(select ifnull(sum(trucks_log.loaded_miles_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as loaded_miles_hourly,
				(select ifnull(sum(trucks_log.miles_deadhead_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead_hourly
				
			from load_handler
				left join customers on customers.id = load_handler.customer_id
			where load_handler.deleted = 0
				
				and customers.deleted = 0	
				
					
				".$search_date_range."
			order by load_handler.id asc
		";		//load_handler.linedate_pickup_eta asc,
				//,trucks_log
				//and trucks_log.deleted = 0
				//and customers.id = load_handler.customer_id
				//and trucks_log.load_handler_id = load_handler.id	
		//d($sql);
		$mrr_capture_sql=$sql;
		$data = simple_query($sql);
		
		$mrr_sql=$sql;
		
		$mrr_loads=0;	
		$mrr_dispatches=0;	
		$counter = 0;
		$total_miles = 0;
		$total_deadhead = 0;
		
		$total_miles_hr = 0;
		$total_deadhead_hr = 0;
				
		$total_profit = 0;
		$total_cost = 0;
		$total_extra = 0;
		$total_sales = 0;
		$last_load_id = 0;
		$last_truck_id = 0;
		$not_invoiced = 0;
		$invoiced = 0;
		$invoiced_amount = 0;
		$not_invoiced_amount = 0;
		$fuel_charge = 0;
		
		$not_invoiced_display="";
		
		$mrr_miles_disp=0;
		$mrr_bill_tot=0;
		$mrr_bill_actual=0;
		$mrr_bill_diff=0;
		$mrr_bill_subtot2=0;
		$mrr_bill_subtot=0;
		
		$mrr_day_cost_total=0;
		$mrr_disp_cost_total=0;
		$mrr_disp_sql="";
		$mrr_tot_days_run=0;
		
		$mrr_total_profitless8t=0;
		
		$mrr_color_code1=" style='color:blue;'";					//fixed expenses that are not mileage based...was my "special" columns
		$mrr_color_code2=" style='color:red;'";						//Daily Cost and Dispatch exspenses found
		$mrr_color_code3=" style='color:purple;'";	// display:none;	//highlight billing verses cost and differnece of them
		$mrr_color_code4=" style='color:green;'";					//stored cost from table
		
		$mrr_display_headers="
					<tr bgcolor='#FFFFFF'>
						<td valign='top'><b>Load</b></td>
						<td valign='top'><b>Days</b></td>
						<td valign='top' align='left'><b>Customer</b></td>
						<td valign='top'><b>Pickup ETA Date</b></td>
						<td valign='top' align='right'><b>Miles</b></td>
						<td valign='top' align='right'><b>Fuel</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Insurance</b></td>
						<td valign='top' align='right'><b>Labor</b></td>
						<td valign='top' align='right'><b>Truck Maint</b></td>
						<td valign='top' align='right'><b>Tires</b></td>
						<td valign='top' align='right'><b>Trailer Maint</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Truck Lease</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Truck Rental</b></td>						
						<td valign='top' align='right'><b>Truck MilesExp</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Admin Exp</b></td>		
						<td valign='top' align='right'><b>Misc. Exp</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Trailer Rental</b></td>
						<td valign='top' align='right'><b>Accidents</b></td>
						<td valign='top' align='right'><b>Trailer MilesExp</b></td>
						<td valign='top' align='right'".$mrr_color_code2."><b>Daily Cost</b></td>
						<td valign='top' align='right'".$mrr_color_code2."><b>Dispatch</b></td>
						<td valign='top' align='right'><b>Calc Total</b></td>
						
						<td valign='top' align='right'".$mrr_color_code3."><b>Act Cost</b></td>
						<td valign='top' align='right'".$mrr_color_code3."><b>Billed</b></td>
						<td valign='top' align='right'".$mrr_color_code3."><b>Bill Diff</b></td>
						
						<td valign='top' align='right'".$mrr_color_code4."><b>Total Cost</b></td>
						
						<td valign='top' align='right'><b>Calc Diff</b></td>
					</tr>
		";
		
		$mrr_load_displayer="<br>
					<div style='border:1px solid #0000CC;margin:5px;' id='mrr_load_border' bgcolor='#FFFFFF'>
					<table border='0' width='100%' id='mrr_load_displayer'>
					".$mrr_display_headers."
		";		
		
		while($row = mysqli_fetch_array($data)) 
		{
			$mrr_fuel=0;
			$mrr_res=mrr_quick_and_easy_budget_maker($row,$mrr_from_date,$mrr_to_date);
			$mrr_disp_sql=$mrr_res['sql'];		
						
			if($last_load_id != $row['id'])
			{							
				$active_truck_cntr_for_avg+=$row['budget_active_trucks'];
				$active_truck_cntr_for_loads++;
				
				
				if($mrr_res['dispatch_count'] > 0)				
				{
					$last_load_id = $row['id'];
					
					$counter++;		
					$mrr_rep_load_id=$row['id'];
										
					$mrr_dispatches+=$mrr_res['dispatch_count'];	
				
     				//$last_load_id = $row['id'];
     				$load_miles = $row['miles'];					// + $row['loaded_miles_hourly'];
     				$hours_worked = $row['hours_worked'];
     				$load_miles_deadhead = $row['miles_deadhead'];	// + $row['miles_deadhead_hourly'];
     				
     				//$mrr_dcntr=$row['driver_cnt'];
     				
     				$total_miles += $load_miles;
     				$total_deadhead += $load_miles_deadhead;
     				
     				//$mrr_days_in_month=$row['budget_days_in_month'];
     				//if($mrr_days_in_month==0)		$mrr_days_in_month=1;
     				
     				$mrr_fuel=$mrr_res['fuel'];
     				$mrr_miles=$mrr_res['miles'];  
     				$mrr_loads++;
     				
     				$fuel_charge+=$mrr_fuel;
     				   				
     				$budget_val_fuel+=  $mrr_fuel;
     				$budget_val_insur+= $mrr_res['insur'];
     				
     				$budget_val_labor+= $mrr_res['labor'];
     				
     				$budget_val_truck_maint+= $mrr_res['truck_maint'];
     				
     				$budget_val_trailer_maint+= $mrr_res['trailer_maint'];
     				$budget_val_truck_rental+= $mrr_res['truck_rental'];
     				$budget_val_truck_lease+= $mrr_res['truck_lease'];
     				
     				$budget_val_admin_exp+= $mrr_res['admin_exp'];	
     				
     				$budget_val_trailer_rental+= $mrr_res['trailer_rental'] ;
     				
     				$budget_val_tires+=$mrr_res['tires'];	
     				$budget_val_accidents+= $mrr_res['accidents'];	
     				$budget_val_miles_exp+= $mrr_res['mileage_exp'];	
     				$budget_val_misc_exp_mile+= $mrr_res['misc_exp'];	     				
     				
     				$budget_val_trailer_miles_exp+=$mrr_res['trailer_mileage_exp'];
     				     				
     				$mrr_load_sub_tot=0;	//$mrr_res['daily_cost'] + $mrr_res['expenses'];
     				$mrr_load_sub_tot+= $mrr_fuel;
     				$mrr_load_sub_tot+= $mrr_res['insur'];	
     				$mrr_load_sub_tot+= $mrr_res['labor'];
     				$mrr_load_sub_tot+= $mrr_res['truck_maint'];				
     				$mrr_load_sub_tot+= $mrr_res['tires'];
     				$mrr_load_sub_tot+= $mrr_res['trailer_maint'];
     				$mrr_load_sub_tot+= $mrr_res['truck_rental'];
     				$mrr_load_sub_tot+= $mrr_res['truck_lease'];
     				$mrr_load_sub_tot+= $mrr_res['mileage_exp'];	
     				$mrr_load_sub_tot+= $mrr_res['admin_exp'];
     				$mrr_load_sub_tot+= $mrr_res['misc_exp'];				
     				$mrr_load_sub_tot+= $mrr_res['trailer_rental'];     					
     				$mrr_load_sub_tot+= $mrr_res['accidents'];	
     				$mrr_load_sub_tot+= $mrr_res['trailer_mileage_exp'];	
     				
     				$mrr_otr_cntr=$mrr_res['days_run'];
     				$mrr_tot_days_run+=$mrr_otr_cntr;
     				    				
     				$mrr_miles_disp+=$mrr_miles;
     								
     				$mrr_tot_cost_sales=$row['actual_total_cost'];
     				$mrr_billed_sales=$row['actual_bill_customer'];
     				$mrr_actual_bill=$row['actual_bill_customer'] - $row['actual_total_cost'];
     				
     				
     				$mrr_load_sub_tot2=$mrr_load_sub_tot;
     				//$mrr_load_sub_tot-=$mrr_actual_bill;
     												
     				$mrr_bill_tot+=$mrr_tot_cost_sales;
     				$mrr_bill_actual+=$mrr_billed_sales;
     				$mrr_bill_diff+=$mrr_actual_bill;
     				
     				//$mrr_load_sub_tot-=$mrr_actual_bill;
     				
     				$mrr_total_profitless=$mrr_load_sub_tot2 - $mrr_tot_cost_sales;				
     				
     				$mrr_bill_subtot+=$mrr_total_profitless;
     				$mrr_bill_subtot2+=$mrr_load_sub_tot2;
     								
     				$mrr_day_cost_total+=$mrr_res['daily_cost'];
     				$mrr_disp_cost_total+=$mrr_res['expenses'];
     				
     				$cnamer=$row['name_company'];
     				if(strlen($cnamer) > 25)		$cnamer="<span title='".$row['name_company']."'>".substr($row['name_company'],0,23)."...</span>";
     				
     				$color_1="#EEEEEE";
     				$color_2="#DDDDDD";
     				
     				$col_err="";
     				if(number_format($mrr_res['fun_disp_cost'], 2)!=number_format($mrr_load_sub_tot2,2))	$col_err=" style='color:red;'";
     				
     				$mrr_total_profitless8=($mrr_res['fun_disp_cost']- $mrr_load_sub_tot2);
     				$mrr_total_profitless8t+=$mrr_total_profitless8;
     				
     				$show_it=1; 
     				//$show_it=0;     				
     				//if( abs($mrr_total_profitless8) > 0.01)			$show_it=1;		//show only the bad ones..
     				
     				if($show_it==1)
     				{
          				$mrr_lab_miles=$mrr_res['labor_miles'];
          				$mrr_lab_hrs=$mrr_res['labor_hours'];
          				
          				//<br>$".number_format($mrr_lab_miles,2)."[$".number_format($mrr_lab_hrs,2)."]
          				          				
          				
          				if($counter%10==0)		$mrr_load_displayer.=$mrr_display_headers;
          				          				
          				
          				$mrr_load_displayer.="
          					<tr class='full_detail_report all_loads otr_".number_format($mrr_otr_cntr,0)."' bgcolor='".($counter%2==1 ? "$color_1" : "$color_2")."'>
          						<td valign='top'><a href='/manage_load.php?load_id=".$row['id']."' target='_blank'>".$row['id']."</a></td>
          						<td valign='top' align='left'". ($mrr_res['insur']==0 ? "$mrr_color_code2" : "$mrr_color_code1") .">".number_format($mrr_otr_cntr,0)."</td>     						
          						<td valign='top' align='left'>".$cnamer."</td>	
          						<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_pickup_eta']))."</td>						
          						<td valign='top' align='right'>".$mrr_miles."</td>
          						<td valign='top' align='right'>$".number_format( $mrr_fuel,2)."</td>
          						<td valign='top' align='right'".$mrr_color_code1.">$".number_format($mrr_res['insur'],2)."</td>
          						<td valign='top' align='right'>$".number_format($mrr_res['labor'],2)."</td>
          						<td valign='top' align='right'>$".number_format($mrr_res['truck_maint'],2)."</td>
          						<td valign='top' align='right'>$".number_format($mrr_res['tires'],2)."</td>
          						<td valign='top' align='right'>$".number_format($mrr_res['trailer_maint'],2)."</td>
          						<td valign='top' align='right'".$mrr_color_code1.">$".number_format($mrr_res['truck_rental'],2)."</td>
          						<td valign='top' align='right'".$mrr_color_code1.">$".number_format($mrr_res['truck_lease'],2)."</td>
          						<td valign='top' align='right'>$".number_format($mrr_res['mileage_exp'],2)."</td>
          						<td valign='top' align='right'".$mrr_color_code1.">$".number_format($mrr_res['admin_exp'],2)."</td>						
          						<td valign='top' align='right'>".number_format($mrr_res['misc_exp'],2)."</td>
          						<td valign='top' align='right'".$mrr_color_code1.">$".number_format($mrr_res['trailer_rental'],2)."</td>
          						<td valign='top' align='right'>$".number_format($mrr_res['accidents'],2)."</td>
          						<td valign='top' align='right'>$".number_format($mrr_res['trailer_mileage_exp'],2)."</td>
          						<td valign='top' align='right'".$mrr_color_code2.">$".number_format($mrr_res['daily_cost'],2)."</td>
          						<td valign='top' align='right'".$mrr_color_code2.">$".number_format($mrr_res['expenses'],2)."</td>
          						<td valign='top' align='right'>$<span".$col_err.">".number_format(($mrr_load_sub_tot2),2)."</span><br><span style='color:orange;'>".$mrr_res['fun_disp_cost']." - - ></span></td>	
          						
          						<td valign='top' align='right'".$mrr_color_code3.">$".number_format(($mrr_tot_cost_sales),2)."</td>	
          						<td valign='top' align='right'".$mrr_color_code3.">$".number_format(($mrr_billed_sales),2)."</td>
          						<td valign='top' align='right'".$mrr_color_code3.">$".number_format(($mrr_actual_bill),2)."</td>
          											
          						<td valign='top' align='right'".$mrr_color_code4.">$".number_format(($mrr_tot_cost_sales),2)."</td>
          						
          						<td valign='top' align='right'><span style='color:orange;'>$".number_format(($mrr_total_profitless8),2)."</span></td>
          					</tr>
          				";
     				}
				}								
			}			
			
			if((trim($row['sicap_invoice_number']) !="" && trim($row['sicap_invoice_number']) !="0") || (trim($row['invoice_number']) != "" && trim($row['invoice_number']) != "0"))
			{
				$invoiced++;
				$invoiced_amount += ($row['actual_bill_customer']);
			}
			else
			{
				$not_invoiced++;
				$not_invoiced_amount += ($row['actual_bill_customer']);	
				
				$not_invoiced_display.="<br>LOAD ".$row['id']." [Inv <u>".trim($row['invoice_number'])."</u> or SICAP Inv <u>".trim($row['sicap_invoice_number'])."</u>] $".$row['actual_bill_customer']."";
			}
			
			$total_profit += $row['actual_bill_customer'] - $row['actual_total_cost'];
			$total_cost += $row['actual_total_cost'];
			$total_sales += $row['actual_bill_customer'];			
		}	
		
		$total_sales+=($sales_carlex + $sales_vietti);		//add timesheet sales...
		

		$days_run = get_days_available(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		$days_actual = get_days_run(strtotime($_POST['date_from']), strtotime($_POST['date_to']));
		
		$days_variance = $days_actual - $days_run['days_available_so_far'];
		
		$daily_cost = get_daily_cost();
		$usage_difference = $daily_cost * $days_variance;
		
		$gross_profit = $total_profit;	// + $usage_difference;
		
		$gallons_used = ($total_miles + $total_deadhead) / $defaultsarray['average_mpg'];
		$cost_per_gallon = $fuel_charge / $gallons_used;
						
		$budget_val_misc_exp+=$budget_val_misc_exp_mile;
				
		//budget variance section...added April 2013
		$bud_variance_days=$days_run['days_available_so_far'];
		$bud_variance_run=$days_actual;
		$bud_variance_bill=$days_variance;
		$bud_variance_tot=$usage_difference;
		
		$bud_act_truck_cntr_avg=0;
		if($active_truck_cntr_for_loads > 0)		$bud_act_truck_cntr_avg= $active_truck_cntr_for_avg / $active_truck_cntr_for_loads;	//get active truck count average for all loads...used for variance fields below...
		
		$sub_profit=$gross_profit;
		
		$bud_act_truck_cntr_avg=ceil($bud_act_truck_cntr_avg);
		if($bud_act_truck_cntr_avg>0)
		{
     		$bud_variance_days=$defaultsarray['billable_days_in_month'] * $bud_act_truck_cntr_avg;
			$bud_variance_run=$days_actual;
			$bud_variance_bill= $days_actual - $bud_variance_days;
			$bud_variance_tot=$daily_cost * $bud_variance_bill;
			
			$gross_profit += $bud_variance_tot;
		}
		
		
		$mrr_all_display_tot=0;
		$mrr_all_display_tot+=$budget_val_fuel;
		$mrr_all_display_tot+=$budget_val_insur;
		$mrr_all_display_tot+=$budget_val_labor;
		$mrr_all_display_tot+=$budget_val_truck_maint;
		$mrr_all_display_tot+=$budget_val_tires;
		$mrr_all_display_tot+=$budget_val_trailer_maint;
		$mrr_all_display_tot+=$budget_val_truck_rental;
		$mrr_all_display_tot+=$budget_val_truck_lease;
		$mrr_all_display_tot+=$budget_val_miles_exp;
		$mrr_all_display_tot+=$budget_val_admin_exp;
		$mrr_all_display_tot+=$budget_val_misc_exp;
		$mrr_all_display_tot+=$budget_val_trailer_rental;
		$mrr_all_display_tot+=$budget_val_accidents;
		$mrr_all_display_tot+=$budget_val_trailer_miles_exp;
		
		
		$mrr_bill_subtot8=$mrr_total_profitless8t;
		
		$mrr_load_displayer.="
					<tr bgcolor='#FFFFFF'>
						<td valign='top'>Total</td>
						<td valign='top'>".number_format(($mrr_tot_days_run),0)."</td>
						<td valign='top'>Loads=".$mrr_loads."</td>						
						<td valign='top'>Dispatches=".$mrr_dispatches."</td>
						<td valign='top' align='right'>".$mrr_miles_disp."</td>
						<td valign='top' align='right'>$".number_format(($budget_val_fuel),2)."</td>
						<td valign='top' align='right'".$mrr_color_code1.">$".number_format(($budget_val_insur),2)."</td>
						<td valign='top' align='right'>$".number_format(($budget_val_labor),2)."</td>
						<td valign='top' align='right'>$".number_format(($budget_val_truck_maint),2)."</td>
						<td valign='top' align='right'>$".number_format(($budget_val_tires),2)."</td>
						<td valign='top' align='right'>$".number_format(($budget_val_trailer_maint),2)."</td>
						<td valign='top' align='right'".$mrr_color_code1.">$".number_format(($budget_val_truck_rental),2)."</td>
						<td valign='top' align='right'".$mrr_color_code1.">$".number_format(($budget_val_truck_lease),2)."</td>
						<td valign='top' align='right'>$".number_format(($budget_val_miles_exp),2)."</td>
						<td valign='top' align='right'".$mrr_color_code1.">$".number_format(($budget_val_admin_exp),2)."</td>		
						<td valign='top' align='right'>$".number_format(($budget_val_misc_exp),2)."</td>						
						<td valign='top' align='right'".$mrr_color_code1.">$".number_format(($budget_val_trailer_rental),2)."</td>
						<td valign='top' align='right'>$".number_format(($budget_val_accidents),2)."</td>
						<td valign='top' align='right'>$".number_format(($budget_val_trailer_miles_exp),2)."</td>
						<td valign='top' align='right'".$mrr_color_code2.">$".number_format(($mrr_day_cost_total),2)."</td>
						<td valign='top' align='right'".$mrr_color_code2.">$".number_format(($mrr_disp_cost_total),2)."</td>						
						<td valign='top' align='right'>$".number_format(($mrr_bill_subtot2),2)."</td>
						
						<td valign='top' align='right'".$mrr_color_code3.">$".number_format(($mrr_bill_tot),2)."</td>
						<td valign='top' align='right'".$mrr_color_code3.">$".number_format(($mrr_bill_actual),2)."</td>
						<td valign='top' align='right'".$mrr_color_code3.">$".number_format(($mrr_bill_diff),2)."</td>
						
						<td valign='top' align='right'".$mrr_color_code4.">$".number_format(($mrr_bill_tot),2)."</td>
						
						<td valign='top' align='right'>$".number_format(($mrr_bill_subtot8),2)."</td>
					</tr>
					<tr class='full_detail_report' bgcolor='#FFFFFF'>
						<td valign='top'><b>Load</b></td>
						<td valign='top'><b>Days</b></td>
						<td valign='top' align='left'><b>Customer</b></td>
						<td valign='top'><b>Pickup ETA Date</b></td>
						<td valign='top' align='right'><b>Miles</b></td>
						<td valign='top' align='right'><b>Fuel</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Insurance</b></td>
						<td valign='top' align='right'><b>Labor</b></td>
						<td valign='top' align='right'><b>Truck Maint</b></td>
						<td valign='top' align='right'><b>Tires</b></td>
						<td valign='top' align='right'><b>Trailer Maint</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Truck Lease</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Truck Rental</b></td>
						<td valign='top' align='right'><b>Truck MilesExp</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Admin Exp</b></td>		
						<td valign='top' align='right'><b>Misc. Exp</b></td>
						<td valign='top' align='right'".$mrr_color_code1."><b>Trailer Rental</b></td>
						<td valign='top' align='right'><b>Accidents</b></td>
						<td valign='top' align='right'><b>Trailer MilesExp</b></td>
						<td valign='top' align='right'".$mrr_color_code2."><b>Daily Cost</b></td>
						<td valign='top' align='right'".$mrr_color_code2."><b>Dispatch</b></td>
						<td valign='top' align='right'><b>Calc Total</b></td>
						
						<td valign='top' align='right'".$mrr_color_code3."><b>Act Cost</b></td>
						<td valign='top' align='right'".$mrr_color_code3."><b>Bill Cust</b></td>
						<td valign='top' align='right'".$mrr_color_code3."><b>Bill Diff</b></td>
						
						<td valign='top' align='right'".$mrr_color_code4."><b>Total Cost</b></td>
						
						<td valign='top' align='right'><b>Calc Diff</b></td>
					</tr>
					<tr class='full_detail_report' bgcolor='#FFFFFF'>
						<td valign='top'>&nbsp;</td>
						<td valign='top'>						
							<span class='mrr_link_like_on' onClick='mrr_display_otr_days_all();'>ALL</span><br>
							<span class='mrr_link_like_on' onClick='mrr_display_otr_days(0);'>0 Days</span><br>
							<span class='mrr_link_like_on' onClick='mrr_display_otr_days(1);'>1 Day</span><br>
							<span class='mrr_link_like_on' onClick='mrr_display_otr_days(2);'>2 Days</span><br>
							<span class='mrr_link_like_on' onClick='mrr_display_otr_days(3);'>3 Days</span><br>
						</td>
						<td valign='top' align='left'>&nbsp;</td>
						<td valign='top'>&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'".$mrr_color_code1.">&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'".$mrr_color_code1.">&nbsp;</td>
						<td valign='top' align='right'".$mrr_color_code1.">&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'".$mrr_color_code1.">&nbsp;</td>		
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'".$mrr_color_code1.">&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>
						<td valign='top' align='right'".$mrr_color_code2.">&nbsp;</td>
						<td valign='top' align='right'".$mrr_color_code2.">&nbsp;</td>
						<td valign='top' align='right'>&nbsp;</td>						
						
						<td valign='top' align='right'".$mrr_color_code3.">&nbsp;</td>
						<td valign='top' align='right'".$mrr_color_code3.">&nbsp;</td>
						<td valign='top' align='right'".$mrr_color_code3.">&nbsp;</td>
						
						<td valign='top' align='right'".$mrr_color_code4.">&nbsp;</td>
						
						<td valign='top' align='right'></td>
					</tr>
					</table>
					</div><br>
				";	//".$mrr_disp_sql."<br>
		
	$output="";
	
	$mrr_tot_miles=$total_miles + $total_deadhead;
	
	//OUTDATED SUMMARY, look below for Output6 version...**************************************************************************************
	$output5="
	<table border='0'>
		<tr>
			<td colspan='6'><center><b>Capture from Sales Report</b></center></td>
		</tr>
		<tr>	
			<td align='left'><b>Total Loaded Miles</b></td>
			<td align='right'>".number_format($total_miles)."</td>
			<td align='left'><b>Dispatches</b></td>
			<td align='right'>".number_format($counter)."</td>
		</tr>
		<tr>
			<td align='left'><b>Total Deadhead</b></td>
			<td align='right'>".number_format($total_deadhead)."</td>
			<td align='left'><b>Invoiced (".$invoiced.")</b></td>
			<td align='right'>$".money_format('',$invoiced_amount)."</td>
		</tr>
		<tr>
			<td align='left'><b>Total Extra</b></td>
			<td align='right'></td>
			<td align='left'><b>Not Invoiced ".$not_invoiced."</b></td>
			<td align='right'>$".money_format('',$not_invoiced_amount)."</td>
		</tr>
		<tr>
			<td align='left'><b>Total Miles</b></td>
			<td align='right'>".number_format($total_miles + $total_deadhead)."</td>
			<td align='left'></td>
			<td align='right'></td>
		</tr>
		<tr>
			<td align='left'>-</td>
			<td align='right'></td>
			<td align='left'></td>
			<td align='right'>-</td>
		</tr>
		<tr>
			<td align='left'><b>Total Sales</b></td>
			<td align='right'>$".money_format('',$total_sales)."</td>
			<td align='left'><b>Days Available</b></td>
			<td align='right'>".$days_run['days_available_so_far']."</td>
		</tr>
		<tr>
			<td align='left'><b>Total Cost</b></td>
			<td align='right'>$".money_format('',$total_cost)."</td>
			<td align='left'><b>Days Run</b></td>
			<td align='right'>".number_format($days_actual,2)."</td>
		</tr>
		<tr>
			<td align='left'><b>Total Profit</b></td>
			<td align='right'>$".money_format('',$total_profit)."</td>
			<td align='left'><b>Days Variance</b></td>
			<td align='right'>".($days_variance < 0 ? "<span class='alert'>".number_format($days_variance,2)."</span>" : "".number_format($days_variance,2)."")."</td>
		</tr>
		<tr>
			<td align='left'><b>Estimated Gross Profit</b></td>
			<td align='right'>".($gross_profit < 0 ? "<span class='alert'>$".money_format('', $gross_profit)."</span>" : "$".money_format('', $gross_profit)."")."</td>
			<td align='left'><b>Variance @ $".money_format('',$daily_cost)."</b></td>
			<td align='right'>".($usage_difference < 0 ? "<span class='alert'>$".money_format('', $usage_difference)."</span>" : "$".money_format('', $usage_difference)."")."</td>
		</tr>
		<tr>
			<td align='left'>-</td>
			<td align='right'></td>
			<td align='left'></td>
			<td align='right'>-</td>
		</tr>
		<tr>
			<td align='left'><b>Est Fuel (Gallons) used @ ".$defaultsarray['average_mpg']." mpg:</b></td>
			<td align='right'>".number_format($gallons_used)."</td>
			<td align='left'></td>
			<td align='right'></td>
		</tr>
		<tr>
			<td align='left'><b>Est Cost of Fuel used:</b></td>
			<td align='right'>$".money_format('', $fuel_charge)."</td>
			<td align='left'><b>Est Avg Cost per Gal:</b></td>
			<td align='right'>$".money_format('', $cost_per_gallon)."</td>
		</tr>
	</table>		
	";
	//echo "<div style='display:none' id='output5'>$output</div>";
	
	$output6="
	<table border='0' width='1550' bgcolor='#FFFFFF'>
		<tr>
			<td valign='top' align='left'><b>Sales Report Summary</b></td>
			<td valign='top' align='right'><b>Total Loaded Miles</b></td>
			<td valign='top' align='right'>".number_format($total_miles)."</td>
			<td valign='top' align='right'><b>Est Fuel (Gallons) used @ ".$defaultsarray['average_mpg']." mpg:</b></td>
			<td valign='top' align='right'>".number_format($gallons_used)."</td>
			<td valign='top' align='right'><b>Dispatches</b></td>
			<td valign='top' align='right'>".number_format($counter)."</td>
			<td valign='top' align='right'><b>Days Available</b></td>
			<td valign='top' align='right'>".$days_run['days_available_so_far']."</td>
			<td valign='top' align='right'><b>Billable Days</b></td>
			<td valign='top' align='right'>".$bud_variance_days."</td>
			<td valign='top' align='right'><b>Total Sales:</b></td>
			<td valign='top' align='right'>$<span id='mrr_btot_disp0'>".money_format('',$total_sales)."</span></td>
		</tr>
		<tr bgcolor='#EEEEEE'>
			<td valign='top'>&nbsp;</td>
			<td valign='top' align='right'><b>Total Deadhead</b></td>
			<td valign='top' align='right'>".number_format($total_deadhead)."</td>
			<td valign='top' align='right'><b>Est Cost of Fuel used:</b></td>
			<td valign='top' align='right'>$".money_format('', $fuel_charge)."</td>
			<td valign='top' align='right'><b>Invoiced (".$invoiced.")</b></td>
			<td valign='top' align='right'>$".money_format('',$invoiced_amount)."</td>
			<td valign='top' align='right'><b>Days Run</b></td>
			<td valign='top' align='right'>".number_format($days_actual,2)."</td>
			<td valign='top' align='right'><b>Billable Run</b></td>
			<td valign='top' align='right'>".number_format($bud_variance_run,2)."</td>
			<td valign='top' align='right'><b>Total Cost</b></td>
			<td valign='top' align='right'><span id='mrr_btot_disp1'>".money_format('',$total_cost)."</span></td>
		</tr>
		<tr>
			<td valign='top'>&nbsp;</td>
			<td valign='top' align='right'><b>Total Extra</b></td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'><b>Est Avg Cost per Gal:</b></td>
			<td valign='top' align='right'>$".money_format('', $cost_per_gallon)."</td>
			<td valign='top' align='right'><b>Not Invoiced (<span id='mrr_not_invoiced_cnt'>".$not_invoiced."</span>)</b></td>
			<td valign='top' align='right'><span id='mrr_not_invoiced_amount'>$".money_format('',$not_invoiced_amount)."</span></td>
			<td valign='top' align='right'><b>Days Variance</b></td>
			<td valign='top' align='right'>".($days_variance < 0 ? "<span class='alert'>".number_format($days_variance,2)."</span>" : "".number_format($days_variance,2)."")."</td>
			<td valign='top' align='right'><b>Budget Days</b></td>
			<td valign='top' align='right'>".($bud_variance_bill < 0 ? "<span class='alert'>".number_format($bud_variance_bill,2)."</span>" : "".number_format($bud_variance_bill,2)."")."</td>
			<td valign='top' align='right'><b>Total Profit</b></td>
			<td valign='top' align='right'><span id='mrr_btot_disp2'>".money_format('',$sub_profit)."</span></td>
		</tr>
		<tr bgcolor='#EEEEEE'>
			<td valign='top'>&nbsp;</td>
			<td valign='top' align='right'><b>Total Miles</b></td>
			<td valign='top' align='right'>".number_format($total_miles + $total_deadhead)."</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'><b>Variance @ $".money_format('',$daily_cost)."</b></td>
			<td valign='top' align='right'>".($usage_difference < 0 ? "<span class='alert'>$".money_format('', $usage_difference)."</span>" : "$".money_format('', $usage_difference)."")."<input type='hidden' name='mrr_reg_vari' id='mrr_reg_vari' value='".$usage_difference."'></td>
			<td valign='top' align='right'><b>Budget Variance</b></td>
			<td valign='top' align='right'>".($bud_variance_tot < 0 ? "<span class='alert'>$".number_format($bud_variance_tot,2)."</span>" : "".number_format($bud_variance_tot,2)."")."<input type='hidden' name='mrr_bud_vari' id='mrr_bud_vari' value='".$bud_variance_tot."'></td>
			<td valign='top' align='right'><b>Estimated Gross Profit</b></td>
			<td valign='top' align='right'><span id='mrr_btot_disp3'>".($gross_profit < 0 ? "<span class='alert'>".money_format('', $gross_profit)."</span>" : "".money_format('', $gross_profit)."")."</span></td>
		</tr>
		<tr>
			<td valign='top'>&nbsp;</td>
			<td valign='top' align='right'><b>&nbsp;</b></td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'><b>&nbsp;</b></td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'><b>&nbsp;</b></td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'><b><span id='mrr_btot_disp4a'>Profit with Budget Variance</span></b></td>
			<td valign='top' align='right'><span id='mrr_btot_disp4'>$0.00</span></td>
		</tr>
		<tr>
			<td valign='top'><b>Miles Moved ".number_format($mrr_miles_moved,0)."</b><br><br><b>".( $mrr_miles_adjust!=$mrr_miles_moved  ? "(<span style='color:red;'>Adjusted Miles ".number_format($mrr_miles_adjust,0)."</span>)"  : "") ."</b></td>
			<td valign='top' align='right' colspan='6'>".$mrr_miles_html."</td>
			<td valign='top' align='left'>&nbsp;</td>
			<td valign='top' align='left' colspan='3'><b>Loads Not Invoiced</b>".$not_invoiced_display."</td>
		</tr>
	</table>	<br>
	";	
	$output="";
	//========================================================================================================================================================================================================	
?>	
			<? echo $output6; ?>			
		</td>
	</tr>
	<tr>
		<td valign='top' colspan='2'>
			<div id='mrr_graph_maker_table'></div>
		</td>
	</tr>
	</table>
<? 
if(isset($_POST['build_report'])) 
{	
	$truck_id=0;	
	$trailer_id=0;	
	$all_totaled=0;
	
	$chart_accounts=17;
	//Chart Display Name					////Chart of Accounts #1			//Chart of Accounts #2		//Chart of Accounts #3		//Chart of Accounts #4		//Chart of Accounts #5		//Chart of Accounts #6
	$chart_title[0]="Fuel";					//$chart_acct1[0]="58800";		$chart_acct2[0]="58900";		$chart_acct3[0]="";			$chart_acct4[0]="";			$chart_acct5[0]="";			$chart_acct6[0]="";
	$chart_title[1]="Insurance";				//$chart_acct1[1]="62300";		$chart_acct2[1]="";			$chart_acct3[1]="";			$chart_acct4[1]="";			$chart_acct5[1]="";			$chart_acct6[1]="";
	$chart_title[2]="Labor(Drivers)";			//$chart_acct1[2]="67000-";		$chart_acct2[2]="65000-";	$chart_acct3[2]="75500-";	$chart_acct4[2]="78800";		$chart_acct5[2]="67100";		$chart_acct6[2]="";
	$chart_title[3]="Truck Maintenance";		//$chart_acct1[3]="74500-";		$chart_acct2[3]="74900-";	$chart_acct3[3]="";			$chart_acct4[3]="";			$chart_acct5[3]="";			$chart_acct6[3]="";
	$chart_title[4]="Truck Repairs";			//$chart_acct1[4]="";			$chart_acct2[4]="";			$chart_acct3[4]="";			$chart_acct4[4]="";			$chart_acct5[4]="";			$chart_acct6[4]="";
	$chart_title[5]="Tires";					//$chart_acct1[5]="77600";		$chart_acct2[5]="";			$chart_acct3[5]="";			$chart_acct4[5]="";			$chart_acct5[5]="";			$chart_acct6[5]="";
	$chart_title[6]="Truck Rental";			//$chart_acct1[6]="78000-";		$chart_acct2[6]="";			$chart_acct3[6]="";			$chart_acct4[6]="";			$chart_acct5[6]="";			$chart_acct6[6]="";
	$chart_title[7]="Trailer Maintenance";		//$chart_acct1[7]="77500";		$chart_acct2[7]="77800";		$chart_acct3[7]="";			$chart_acct4[7]="";			$chart_acct5[7]="";			$chart_acct6[7]="";
	$chart_title[8]="Truck Lease Fixed";		//$chart_acct1[8]="77950-";		$chart_acct2[8]="";			$chart_acct3[8]="";			$chart_acct4[8]="";			$chart_acct5[8]="";			$chart_acct6[8]="";
	$chart_title[9]="Mileage Expense";			//$chart_acct1[9]="78100-";		$chart_acct2[9]="78050-";	$chart_acct3[9]="";			$chart_acct4[9]="";			$chart_acct5[9]="";			$chart_acct6[9]="";
	$chart_title[10]="Admin Expense";			//$chart_acct1[10]="85000-OH";	$chart_acct2[10]="97000-OH";	$chart_acct3[10]="";		$chart_acct4[10]="";		$chart_acct5[10]="";		$chart_acct6[10]="";
	$chart_title[11]="Tolls";				//$chart_acct1[11]="";			$chart_acct2[11]="";		$chart_acct3[11]="";		$chart_acct4[11]="";		$chart_acct5[11]="";		$chart_acct6[11]="";
	$chart_title[12]="Miscellaneous Expense";	//$chart_acct1[12]="68270-";		$chart_acct2[12]="68800";	$chart_acct3[12]="77250";	$chart_acct4[12]="79000-";	$chart_acct5[12]="74400";	$chart_acct6[12]="57500";
	$chart_title[13]="Weigh Ticket Expense";	//$chart_acct1[13]="";			$chart_acct2[13]="";		$chart_acct3[13]="";		$chart_acct4[13]="";		$chart_acct5[13]="";		$chart_acct6[13]="";
	$chart_title[14]="Trailer Rental Expense";	//$chart_acct1[14]="77470-";		$chart_acct2[14]="77470";	$chart_acct3[14]="";		$chart_acct4[14]="";		$chart_acct5[14]="";		$chart_acct6[14]="";
	$chart_title[15]="Accidents";				//$chart_acct1[15]="74000-";		$chart_acct2[15]="77485-";	$chart_acct3[15]="";		$chart_acct4[15]="";		$chart_acct5[15]="";		$chart_acct6[15]="";
	$chart_title[16]="Trailer Mileage Expense";	//$chart_acct1[16]="77475-";		$chart_acct2[16]="";		$chart_acct3[16]="";		$chart_acct4[16]="";		$chart_acct5[16]="";		$chart_acct6[16]="";
	
	//new COA display.........................................................................................  	/*
	$new_coa[0][0]="";
	$new_coa_num[0]=0;
	$new_coa_lab[0]="";
	$new_coa_code[0]=0;
	
	//added to allow up to 20 codes (X) per section (Y).
	$chart_acct1[0]="";		for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct1[$i]="";		}
	$chart_acct2[0]="";		for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct2[$i]="";		}
	$chart_acct3[0]="";		for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct3[$i]="";		}
	$chart_acct4[0]="";		for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct4[$i]="";		}
	$chart_acct5[0]="";		for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct5[$i]="";		}
	$chart_acct6[0]="";		for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct6[$i]="";		}
	$chart_acct7[0]="";		for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct7[$i]="";		}
	$chart_acct8[0]="";		for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct8[$i]="";		}
	$chart_acct9[0]="";		for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct9[$i]="";		}
	$chart_acct10[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct10[$i]="";		}
	$chart_acct11[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct11[$i]="";		}
	$chart_acct12[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct12[$i]="";		}
	$chart_acct13[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct13[$i]="";		}
	$chart_acct14[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct14[$i]="";		}
	$chart_acct15[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct15[$i]="";		}
	$chart_acct16[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct16[$i]="";		}
	$chart_acct17[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct17[$i]="";		}
	$chart_acct18[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct18[$i]="";		}
	$chart_acct19[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct19[$i]="";		}
	$chart_acct20[0]="";	for($i=0; $i< $chart_accounts;$i++)	{	$chart_acct20[$i]="";		}
		
	
	//echo "<br><br>Scanning for Account Codes:";		
	$sql="
		select * 
		from comparison_sections
		order by id asc
	";		
	$data=simple_query($sql);	
	$mrr_cntr=0;
	$mrr_sub_cntr=0;
	
	while($row=mysqli_fetch_array($data))
	{
		$section_id=$row['id'];	
		$sector=$row['comparison_code'];
		$sector_name=$row['budget_name'];
		$sector_note=trim($row['notes']);		//find "Used as a range"
		$del=$row['deleted'];
		$mrr_sub_cntr=0;
		
		//echo "<br><br>Section ".$sector_name." ...Scanning for Account Codes:";
		
		$new_coa_lab[ $sector ]=trim($sector_name);
		$new_coa_num[ $sector ]=0;
		$new_coa[ $sector ][0]="";
		$new_coa_code[ $sector ]=0;
		if($sector_note=="Used as a range")
		{
			$new_coa_code[ $sector ]=1;	//mark to use as ranged values
		}		
		
		if($del==0)
		{
			$chart_title[ $sector ]=$sector_name;
			
			$mrr_temp=1;
			
			$res=mrr_get_budget_all_comparison_section_items($section_id);
			$arr=$res['arr'];
			$list=$res['list'];
			
			for($i=0;$i < $res['num'];$i++)
			{
				$myid=$arr[ $i ]; 
				$accts=mrr_get_budget_comparison_section_item($myid);
				$act=$accts['active'];
				$acct_code=$accts['account_code'];
				
				//echo "<br>".$mrr_temp." Account Found '".$acct_code."'.";
				
				if($act>0)
				{
					
					$varnamed="chart_acct".($mrr_temp)."";
					${$varnamed}[ $sector ]=trim($acct_code);	
					
					//$tmp=$new_coa_num[ $sector ];
					$new_coa[ $sector ][ $mrr_temp ]=$acct_code;
					
					//echo "Used... (sector: $sector | mrr_temp: $mrr_temp) ".$varnamed."= '".$$varnamed[ $sector ]."'. ($chart_acct1[0])";
					
					$mrr_temp++;
				}				
			}
		}
		$mrr_cntr++;
	}
	
	//$chart_accounts=$mrr_cntr;
	//end of new version    ............................................................................  */	
																      
	$sales[0]=0;		$invoiced[0]=0;		$actual[0]=0;		@ $budget[0]=$budget_val_fuel;	
	$sales[1]=0;		$invoiced[1]=0;		$actual[1]=0;		@ $budget[1]=$budget_val_insur;	
	$sales[2]=0;		$invoiced[2]=0;		$actual[2]=0;		@ $budget[2]=$budget_val_labor;
	$sales[3]=0;		$invoiced[3]=0;		$actual[3]=0;		@ $budget[3]=$budget_val_truck_maint;		
	$sales[4]=0;		$invoiced[4]=0;		$actual[4]=0;		@ $budget[4]=0;
	$sales[5]=0;		$invoiced[5]=0;		$actual[5]=0;		@ $budget[5]=$budget_val_tires;	
	$sales[6]=0;		$invoiced[6]=0;		$actual[6]=0;		@ $budget[6]=$budget_val_truck_rental;
	$sales[7]=0;		$invoiced[7]=0;		$actual[7]=0;		@ $budget[7]=$budget_val_trailer_maint;		
	$sales[8]=0;		$invoiced[8]=0;		$actual[8]=0;		@ $budget[8]=$budget_val_truck_lease;	
	$sales[9]=0;		$invoiced[9]=0;		$actual[9]=0;		@ $budget[9]=$budget_val_miles_exp;	
	$sales[10]=0;		$invoiced[10]=0;		$actual[10]=0;		@ $budget[10]=$budget_val_admin_exp;		
	$sales[11]=0;		$invoiced[11]=0;		$actual[11]=0;		@ $budget[11]=0;
	$sales[12]=0;		$invoiced[12]=0;		$actual[12]=0;		@ $budget[12]=$budget_val_misc_exp;		
	$sales[13]=0;		$invoiced[13]=0;		$actual[13]=0;		@ $budget[13]=0;
	$sales[14]=0;		$invoiced[14]=0;		$actual[14]=0;		@ $budget[14]=$budget_val_trailer_rental;	
	$sales[15]=0;		$invoiced[15]=0;		$actual[15]=0;		@ $budget[15]=$budget_val_accidents;	
	$sales[16]=0;		$invoiced[16]=0;		$actual[16]=0;		@ $budget[16]=$budget_val_trailer_miles_exp;
	$sales_tot=0;		$invoiced_tot=0;		$actual_tot=0;		$budget_tot=0;	
	
	$mrr_report_list="<br><br><div align='left' style='text-align:left;'>";
	
	$mrr_report_list.="<br>Dates ".$_POST['date_from']." thru ".$_POST['date_to']."";	
	
	$mrr_color_style=" style='color:#0000CC;'";
	$mrr_report_list2="<br>
					<div style='border:1px solid #0000CC; margin:5px; width:850px;' id='mrr_accounting_border'>
					<table border='0' id='mrr_accounting_displayer' bgcolor='#FFFFFF'>";
	$mrr_report_list2.="<td align='center' colspan='6'><b>Accounting Search Results:<b><br>Dates ".$_POST['date_from']." thru ".$_POST['date_to'].".</td>";	
	$mrr_report_list2.="<tr>";
     $mrr_report_list2.="<td align='left' width='200'><b>Section</b></td>";	
     $mrr_report_list2.="<td align='left' width='100'><b>Account</b></td>";	
     $mrr_report_list2.="<td align='left' width='100'><b>(Acct To)</b></td>";	
     $mrr_report_list2.="<td align='right' width='150'><b>Value</b></td>";
     $mrr_report_list2.="<td align='right' width='150'><b>Section Total</b></td>";
     $mrr_report_list2.="<td align='right' width='150'".$mrr_color_style."><b>All Total</b></td>";
     $mrr_report_list2.="</tr>";	
		
	for($i=0; $i < $chart_accounts; $i++)
	{
		@ $sales[ $i ]=0;
		@ $invoiced[ $i ]=0;
		//@ $budget[ $i ]=0;
		@ $actual[ $i ]=0;
				
		$mrr_report_list.="<br><br>".$chart_title[ $i ].": ";	
		$mrr_used_grouper=0;
		
		if($i>=0 && $i!=4 && $i!=11 && $i!=13)
		{
			$subitem=0;
			//------------------------------ - - ->API
			$tmp_val=(float)0;		$cur_val=(float) 0;
			if($i==10)
			{
				$mrr_used_grouper=1;
				$results=mrr_fetch_comparison_data_alt($i,$_POST['date_from'],$_POST['date_to'], $chart_acct1[ $i ] , $chart_acct2[ $i ]);
				foreach($results as $key => $value )
     			{
     				$prt=trim($key);			$tmp=$value;
     				if($prt=="Comparison")		$tmp_val+=(float)$tmp;
     				if($prt=="Comparison")		$cur_val=(float)$tmp;
     			}
     			$all_totaled+=$cur_val;
     			
     			$mrr_report_list2.="<tr class='full_detail_report' bgcolor='#EEEEEE'>";
     			$mrr_report_list2.="<td align='left'>".$chart_title[ $i ]."</td>";	
     			$mrr_report_list2.="<td align='left'>".$chart_acct1[ $i ]."</td>";	
     			$mrr_report_list2.="<td align='left'>".$chart_acct2[ $i ]."</td>";	
     			$mrr_report_list2.="<td align='right'>$".number_format($cur_val,2)."</td>";
     			$mrr_report_list2.="<td align='right'>$".number_format($tmp_val,2)."</td>";
     			$mrr_report_list2.="<td align='right'".$mrr_color_style.">$".number_format($all_totaled,2)."</td>";
     			$mrr_report_list2.="</tr>";	
     			
     			$mrr_report_list.="<br>Chart Range ".$chart_acct1[ $i ].":".$chart_acct2[ $i ]." $".number_format($cur_val,2)." + total=$".number_format($tmp_val,2).". ";
     			
			}
			else
			{
				$mrr_used_grouper=1;
				$tmp_val=0;	
								
				for($x=1; $x<=20;	$x++)
               	{
               		$varnamex="chart_acct".$x;
          			$mrr_use_it_now= ${$varnamex}[ $i ];
          			
          			if($mrr_use_it_now!="")
     				{
     					$results=mrr_fetch_comparison_data_alt($i,$_POST['date_from'],$_POST['date_to'], $mrr_use_it_now ,'');	
     					foreach($results as $key => $value )
          				{
          					$prt=trim($key);		$tmp=trim($value);
          					if($prt=="Comparison")	$tmp_val+=(float)$tmp;
          					if($prt=="Comparison")	$cur_val=(float)$tmp;
          				}
          				$mrr_report_list.="<br>Chart Account Group ".$mrr_use_it_now." $".number_format($cur_val,2)." + total=$".number_format($tmp_val,2).". ";
          				$all_totaled+=$cur_val;
          				
          				$mrr_report_list2.="<tr class='full_detail_report' bgcolor='#EEEEEE'>";
          				$mrr_report_list2.="<td align='left'>".$chart_title[ $i ]."</td>";	
          				$mrr_report_list2.="<td align='left'>".$mrr_use_it_now."</td>";	
          				$mrr_report_list2.="<td align='left'>".$varnamex."[".$i."]</td>";	
          				$mrr_report_list2.="<td align='right'>$".number_format($cur_val,2)."</td>";
          				$mrr_report_list2.="<td align='right'>$".number_format($tmp_val,2)."</td>";
          				$mrr_report_list2.="<td align='right'".$mrr_color_style.">$".number_format($all_totaled,2)."</td>";
          				$mrr_report_list2.="</tr>";
     				}               			
               	}  	
			}
				
			$actual[ $i ]=$tmp_val;
			
		}	
		//$sales_tot+=$sales[ $i ];
		//$invoiced_tot+=$invoiced[ $i ];
		$budget_tot+=$budget[ $i ];
		$actual_tot+=$actual[ $i ];	
		
		if($mrr_used_grouper==1)
		{
			$mrr_report_list2.="<tr class='full_detail_report'>";
			$mrr_report_list2.="<td colspan='6'>&nbsp;</td>";
			$mrr_report_list2.="</tr>";
		}

	}//end for loop
	
	$mrr_report_list.="</div>";
	
	$mrr_report_list2.="<tr>";
     $mrr_report_list2.="<td align='left' colspan='5'><b>Grand Total</b></td>";	
	$mrr_report_list2.="<td align='right'".$mrr_color_style."><b>$".number_format($all_totaled,2)."</b></td>";
     $mrr_report_list2.="</tr>";	
	$mrr_report_list2.="</table></div>";
	

	
	//special Income/Discount Accounts..............................$misc_income & $discounts
	$misc_income=0;	
	$results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48915' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48800' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48925' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '48900' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '45000' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '45100' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '66510' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$misc_income+=(float)$tmp;
     }
     
     $discounts=0;
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '46000' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$discounts+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '49500' ,'');	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$discounts+=(float)$tmp;
     }
     
     $net_profit=0;
     $net_profit_inc=0;
     $net_profit_cog=0;
     $net_profit_adm=0;
     $net_profit_cos=0;
     $results=mrr_fetch_comparison_data_alt(99,$_POST['date_from'],$_POST['date_to'], '0' ,'99999');	//income	
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$net_profit_inc+=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(98,$_POST['date_from'],$_POST['date_to'], '0' ,'99999');	//COGS
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$net_profit_cog-=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(10,$_POST['date_from'],$_POST['date_to'], '0' ,'99999');	//Admin Exp
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$net_profit_adm-=(float)$tmp;
     }
     $results=mrr_fetch_comparison_data_alt(1,$_POST['date_from'],$_POST['date_to'], '0' ,'99999');		//COS
	foreach($results as $key => $value )
     {
     	$prt=trim($key);		$tmp=trim($value);
     	if($prt=="Comparison")	$net_profit_cos-=(float)$tmp;
     }
     //..............................................
     
     //$cres=mrr_pull_timesheet_labor_cost($_POST['date_from'],$_POST['date_to'],1601);     //$cres['tot']		$cres['sql']		$cres['rep']
     //$vres=mrr_pull_timesheet_labor_cost($_POST['date_from'],$_POST['date_to'],1687);	//$vres['tot']		$vres['sql']		$vres['rep']
     
     //$timesheets_carlex=$cres['tot'];
     //$timesheets_vietti=$vres['tot'];
     
     $mrr_usage_variance=$usage_difference;
     $net_profit=$net_profit_inc+$net_profit_cog+$net_profit_adm+$net_profit_cos;	//+$days_variance;
     
     $timesheet_tot=$timesheets_carlex + $timesheets_vietti;

     $ts_sales_tot=$sales_carlex + $sales_vietti;
     
     $mrr_show_net_profit="<div id='mrr_net_profit_lister' style='display:none;'>";
     $mrr_show_net_profit.= "<br>Misc Income=".$misc_income.".";
     $mrr_show_net_profit.= "<br>Discounts=".$discounts.".";
     $mrr_show_net_profit.="<br>
     					<table cellpadding='0' cellspacing='0' border='0' width='1200'>
     					<tr>
     						<td valign='top' nowrap><b>Timesheets/Shuttle Routes</b></td>
     						<td valign='top' align='right'><b>Carlex</b></td>
     						<td valign='top' align='right'><b>Vietti</b></td>
     						<td valign='top' align='right'><b>Total</b></td>
     					</tr>
     					<!--
     					<tr>
     						<td valign='top'><b>SQL</b></td>
     						<td valign='top' align='right'>".$cres['sql']."</td>
     						<td valign='top' align='right'>".$vres['sql']."</td>
     						<td valign='top'><b>SQL</b></td>
     					</tr>
     					<tr>
     						<td valign='top'><b>REPORT</b></td>
     						<td valign='top' align='right'>".$cres['rep']."</td>
     						<td valign='top' align='right'>".$vres['rep']."</td>
     						<td valign='top'><b>REPORT</b></td>
     					</tr>
     					-->
     					<tr>
     						<td valign='top'><b>TIMESHEET LABOR</b></td>
     						<td valign='top' align='right'>$".number_format($timesheets_carlex,2)."</td>
     						<td valign='top' align='right'>$".number_format($timesheets_vietti,2)."</td>
     						<td valign='top' align='right'><b>$".number_format($timesheet_tot,2)."</b></td>
     					</tr>
     					<tr>
     						<td valign='top'><b>SALES</b></td>
     						<td valign='top' align='right'>$".number_format($sales_carlex,2)."</td>
     						<td valign='top' align='right'>$".number_format($sales_vietti,2)."</td>
     						<td valign='top' align='right'><b>$".number_format($ts_sales_tot,2)."</b></td>
     					</tr>
     					</table>
     ";																			//<br>".$sales_html."
     $mrr_show_net_profit.= "<br><hr><br>";
     $mrr_show_net_profit.= "<br>Net Profit Income=".$net_profit_inc.".";
     $mrr_show_net_profit.= "<br>Net Profit COG=".$net_profit_cog.".";
     $mrr_show_net_profit.= "<br>Net Profit Admin=".$net_profit_adm.".";
     $mrr_show_net_profit.= "<br>Net Profit COS=".$net_profit_cos.".<br>---------------------------<br>";
	$mrr_show_net_profit.= "<br>Total Net Profit=".$net_profit.". <br>True Loads=".$mrr_loads."<br>";	//Main SQL=".$mrr_sql.".<br>
	$mrr_show_net_profit.="</div>";
		
	$output="<table class='admin_menu2 font_display_section' style='margin:0 10px;text-align:left' width='650'>
          	<tr>
          		<td colspan='5'>
          			<center>
          			<span class='section_heading'>Comparison Report</span>
          			</center>
          		</td>
          	</tr>
          	<tr>
				<td valign='top' align='left'><b>Miles</b></td>
				<td valign='top' align='right'>".($total_miles + $total_deadhead)."</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
			</tr>
          	<tr>
				<td valign='top' align='left'><b>Chart</b></td>
				<td valign='top' align='right' width='120'><b>Sales</b></td>
				<td valign='top' align='right' width='120'><b>Invoiced</b></td>
				<td valign='top' align='right' width='120'><b>Budgetary</b></td>
				<td valign='top' align='right' width='120'><b>Actual</b></td>
			</tr>
          	<tr>
				<td valign='top' align='left'>Sales</td>
				<td valign='top' align='right'>".$total_sales."</td>
				<td valign='top' align='right'>".$invoiced_amount."</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>&nbsp;</td>
			</tr>			
		";
	$javascript_array_values="";
	
	for($i=0; $i < $chart_accounts; $i++)
	{
		if($i>=0 && $i!=4 && $i!=11 && $i!=13)
		{
     		
     		$sales_per=0;
     		if($total_sales > 0)
     		{
     			$sales_per= ($actual[ $i ] * 100)/$total_sales;		
     		}
     		$sales_tot+=$sales_per;
     		$sales[ $i ] = $sales_per;
     		
     		$output.="
     			<tr>
     				<td valign='top' align='left'>". $i ."<span title='".$chart_acct1[ $i ].", ".$chart_acct2[ $i ].", ".$chart_acct3[ $i ]."'>".$chart_title[ $i ]."</span></td>
     				<td valign='top' align='right'>".number_format($sales[ $i ], 2)."%</td>
     				<td valign='top' align='right'>&nbsp;</td>
     				<td valign='top' align='right'>$".number_format($budget[ $i ], 2)."</td> 
     				<td valign='top' align='right'>$".number_format($actual[ $i ], 2)."</td>
     			</tr>
     			";	//".number_format($invoiced[ $i ], 2)."	
     				//, ".$chart_acct4[ $i ]."
     				
     		$javascript_array_values.="
     		part_titles[".$i ."]='". (trim($chart_title[ $i ])=="" ? "" : $chart_title[ $i ]) ." ".$actual[ $i ]."'; 
     		part_values1[".$i ."]='". (trim($sales[ $i ])=="" ? "0.00" : $sales[ $i ]) ."'; 
     		part_values2[".$i ."]='". (trim($invoiced[ $i ])=="" ? "0.00" : $invoiced[ $i ]) ."'; 
     		part_values3[".$i ."]='". (trim($budget[ $i ])=="" ? "0.00" : $budget[ $i ]) ."'; 
     		part_values4[".$i ."]='". (trim($actual[ $i ])=="" ? "0.00" : $actual[ $i ]) ."'; 
     		";
		}
		else
		{
			$javascript_array_values.="
     		part_titles[".$i ."]=''; 
     		part_values1[".$i ."]='0.00'; 
     		part_values2[".$i ."]='0.00'; 
     		part_values3[".$i ."]='0.00'; 
     		part_values4[".$i ."]='0.00'; 
     		";	
		}
	}
	
	$output.="
			<tr>
				<td valign='top' colspan='5'><hr></td>
			</tr>
			<tr>
				<td valign='top' align='left'>Totals</td>
				<td valign='top' align='right'>".number_format($sales_tot, 2)."%</td>
				<td valign='top' align='right'>&nbsp;</td>
				<td valign='top' align='right'>$".number_format($budget_tot,2)."</td>
				<td valign='top' align='right'>$".number_format($actual_tot, 2)."</td>
			</tr>
			";	//".number_format($invoiced_tot, 2)."
	
	$output.="</table>";	
	
	$pdf = ob_get_contents();
	ob_end_clean();
	
	echo "<div id='printable_area1'>";
	echo $pdf;
	//echo $output6;
	
	//echo $output;
	echo "</div>";
	
	//echo $mrr_report_list;		//list of sections pulled from accounting
	echo $mrr_show_net_profit;
	echo $mrr_report_list2;		//list of sections pulled from accounting
	echo $mrr_load_displayer;	//table of loads used
	//echo "<br>".$mrr_capture_sql."<br>";
}
	
$chart_width='1400';
$chart_height='800';
$landscape=1;
$form_mode=1;
if(!isset($timesheet_tot) || $timesheet_tot == '') $timesheet_tot = 0;
?>
<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
		
	function mrr_hide_invoice_summary(id)
	{
		$('#invoice_'+id+'_sample').hide();
	}
	function mrr_pop_invoice_summary(id)
	{
		$('#invoice_'+id+'_sample').show();
	}
	$().ready(function() 
	{	
		$('.invoice_samples').hide();		
	});
	
	
	var timesheet_total=<?=$timesheet_tot ?>;
	<?
	if(isset($_POST['build_report']))
	{
	?>	     	
     	var graph_width=<?= $chart_width ?>;
     	var graph_height=<?= $chart_height ?>;	
     	
     	var miles=<?= ($total_miles + $total_deadhead) ?>;
     	var sales_tot=<?= $total_sales ?>;
     	var invoiced=<?= $invoiced_amount ?>;
     	
     	var parts=<?= $chart_accounts ?>;
     	var part_titles=new Array();
     	var part_values1=new Array();
     	var part_values2=new Array();
     	var part_values3=new Array();
     	var part_values4=new Array();     	
		<?
		echo $javascript_array_values; 
		?>
		
		var mrr_coa_cntr=<?= $coa_cntr ?>;
		var mrr_coa_names=new Array();
     	var mrr_coa_numbs=new Array();
     	var mrr_coa_group=new Array();
		<?
		echo $javascript_array_values2; 
		?>
		
		var mrr_misc_income=<?= $misc_income ?>;
		var mrr_disc_income=<?= $discounts ?>;
		var mrr_net_profit=<?= $net_profit ?>;
		var mrr_net_variance=<?= $mrr_usage_variance ?>;
		var mrr_net_profit_income=<?= $net_profit_inc ?>;
		var mrr_net_profit_sales=<?= $total_sales ?>;
		
		var mrr_rep_load=<?= $mrr_rep_load_id ?>;
		var mrr_loaded_miler=<?= $mrr_miles_disp ?>;
		
	
		$().ready(function() 
		{	
			//functions load graphs(id,reload,mode,disp,months,width,height)
			//reload clears data and forces new allocation of data if >0
			//mode shows previous=1 or next=2 graph type in rotation...
			//disp=current display mode for rraphic rotation...
     		
     		//start as bar 3D
     		mrr_dynamic_charts_comparison(1,0,0,1,graph_width,graph_height,parts,part_titles,part_values1,part_values2,part_values3,part_values4,miles,sales_tot,invoiced,mrr_coa_cntr,mrr_coa_names,mrr_coa_numbs,mrr_coa_group);
     		
     		//start as pie	
     		mrr_dynamic_charts_comparison(2,0,0,0,250,200,parts,part_titles,part_values1,part_values2,part_values3,part_values4,miles,sales_tot,invoiced,0,mrr_coa_names,mrr_coa_numbs,mrr_coa_group);	
     		
     		//start as pie
     		mrr_dynamic_charts_comparison_enlarger(2,0,0,0,550,400,parts,part_titles,part_values1,part_values2,part_values3,part_values4,miles,sales_tot,invoiced,0,mrr_coa_names,mrr_coa_numbs,mrr_coa_group);	
     		        		
        		mrr_coa_displayer_off();
        		$('.full_detail_report').hide();
        		$('#mrr_load_displayer').hide();
        		$('#mrr_load_border').hide(); 
        		$('#mrr_accounting_displayer').hide();        		
        		$('#mrr_accounting_border').hide();    
        		
        		
        		//printing like the accounting side....
        		print_block='printable_area1';
        			
        		if(print_block!='')
        		{	
        			obj_holder = $('#'+print_block+'');
				obj_wrapper_holder = "";
				
				$(obj_holder).wrap("<div id='"+print_block+"_print_wrapper' />");
				
				obj_wrapper_holder = $('#'+print_block+'_print_wrapper');
        		}		
		});	
		
		function mrr_print_report() 
		{
				//print_icon_holder = "print_icon";
				//$('#'+print_icon_holder).attr('src','images/loader.gif');
				//alert("entering special print, coming soon...");
				$.ajax({
					url: "print_report.php",
					dataType: "xml",
					type: "post",
					data: {
						script_name: "<?=$_SERVER['SCRIPT_NAME']?>",
						report_title: "<?=$report_title?>",
						'display_mode':"<?=$landscape?>",
						'form_mode':"<?=$form_mode?>",
						report_contents: encodeURIComponent(html_entity_decode($(obj_wrapper_holder).html()))
					},
					error: function() {
						$.prompt("General error printing report");
						//$('#'+print_icon_holder).attr('src','images/printer.png');
					},
					success: function(xml) {
						//$('#'+print_icon_holder).attr('src','images/printer.png');
						if($(xml).find('PDFName').text() == '') {
							$.prompt("Error reading filename");
						} else {
							window.open($(xml).find('PDFName').text());
						}
					}
				});
		}
	
	<?
	}	//end build report if....
	?>
	function mrr_display_otr_days_all()
	{
		$('.all_loads').show();	
	}
	function mrr_display_otr_days(days)
	{		
		$('.all_loads').hide();
		$('.otr_'+days+'').show();
	}
	function mrr_display_results_toggler()
	{			
		if($('#mrr_display_results').html()=="<b>Show Table Summaries</b>")
		{
			$('#mrr_load_displayer').show();
			$('#mrr_load_border').show(); 
			$('#mrr_accounting_displayer').show(); 
			$('#mrr_accounting_border').show(); 
			$('.full_detail_report').hide();
			$('#mrr_net_profit_lister').hide();
			
			$('#mrr_display_results').html('<b>Show Table Details</b>');		
		}
		else
		{
			if($('#mrr_display_results').html()=="<b>Show Table Details</b>")
			{
				$('#mrr_load_displayer').show();
				$('#mrr_load_border').show(); 
				$('#mrr_accounting_displayer').show(); 
				$('#mrr_accounting_border').show(); 
				$('.full_detail_report').show();
				$('#mrr_net_profit_lister').show();
				$('#mrr_display_results').html('<b>Hide Tables</b>');		
			}
			else
			{
				$('.full_detail_report').hide();
				$('#mrr_accounting_displayer').hide(); 
				$('#mrr_accounting_border').hide(); 
				$('#mrr_load_displayer').hide();
				$('#mrr_load_border').hide(); 
				$('#mrr_net_profit_lister').show();
				$('#mrr_display_results').html('<b>Show Table Summaries</b>');	
			}			
		}		
	}
	function mrr_calc_comparison()
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_ajax_calc_budget_table",
			type: "post",
			dataType: "xml",
			data: {
				"date_from": $('#date_from').val(),
				"date_to": $('#date_to').val(),
				"calc_sales_tot": $('#calc_sales_tot').html(), 
				"calc_sales_percent": $('#calc_sales_percent').html(), 
				"calc_budget_tot": $('#calc_budget_tot').html(), 
				"calc_actual_tot": $('#calc_actual_tot').html(), 
				"calc_diff_tot": $('#calc_diff_tot').html(), 
				"calc_tot_percent": $('#calc_tot_percent').html(),
				"calc_misc_income": mrr_misc_income,
				"calc_disc_income": mrr_disc_income,
				"calc_net_profit": mrr_net_profit,
				"calc_variance": mrr_net_variance,
				"calc_use_tprofit": get_amount($('#mrr_btot_disp2').html()),
				"mrr_net_profit_income":mrr_net_profit_income,
				"mrr_net_profit_sales":mrr_net_profit_sales,
				"mrr_not_invoiced_cnt": get_amount($('#mrr_not_invoiced_cnt').html()),
				"mrr_not_invoiced_amount": get_amount($('#mrr_not_invoiced_amount').html())
			},
			error: function() {
				$.prompt("Error: No Calculations available.");
			},
			success: function(xml) {
				if($(xml).find('mrrTab').text())
				{
					$('#mrr_calculations').html( $(xml).find('mrrTab').text() );
				}
			}
		});
	}
			
	function mrr_make_calculations_show()
	{		
		$('#mrr_calculations').html(""+ <?= $mrr_tab ?> +"");
	}
		
	function mrr_make_calculations_show_filler()
	{	//this table function may be depricated..................***	
		//get field current values...
		var calc_egp= $('#mrr_calc_egp').html();
		var calc_oub= $('#mrr_calc_oub').html();
		var calc_misc= $('#mrr_calc_misc').html();
		var calc_disc= $('#mrr_calc_disc').html();
		var calc_enp= $('#mrr_calc_enp').html();
		var calc_tap= $('#mrr_calc_tap').html();
		var calc_dif= $('#mrr_calc_dif').html();
		//input only from main comparison chart...
		var calc_sales_tot = $('#calc_sales_tot').html();
		var calc_sales_percent =  $('#calc_sales_percent').html();
		var calc_budget_tot = $('#calc_budget_tot').html();
		var calc_actual_tot =  $('#calc_actual_tot').html();
		var calc_diff_tot = $('#calc_diff_tot').html();
		var calc_tot_percent =  $('#calc_tot_percent').html();
		
		calc_oub=parseFloat(calc_diff_tot);
		$('#mrr_calc_oub').html(calc_oub);
		
		calc_enp=parseFloat(calc_enp)-parseFloat(calc_oub);
		$('#mrr_calc_enp').html(calc_enp);
		
		calc_dif=parseFloat(calc_enp) - parseFloat(calc_tap);
		$('#mrr_calc_dif').html(calc_dif);
	}
	
	function mrr_make_sales_report_pop_up()
	{		
		$.prompt($('#output5').html());
	}
	
	function mrr_adjust_sales_report_numbers(ctot)
	{
		stot=get_amount($('#mrr_btot_disp0').html());
		
		$('#mrr_btot_disp1').html(formatCurrency(ctot));
		
		differ=parseFloat(stot) - parseFloat(ctot);
				
		$('#mrr_btot_disp2').html(formatCurrency(differ));
		//$('#mrr_btot_disp3').html(formatCurrency(differ));
		
		mrr_adjust_the_adjustment();
	}
	function mrr_adjust_the_adjustment()
	{
		stot=get_amount($('#mrr_btot_disp2').html());
		
		//vtot=get_amount($('#mrr_reg_vari').val());
		vtot=get_amount($('#mrr_bud_vari').val());
		
		differ=parseFloat(stot) + parseFloat(vtot);	
		
		$('#mrr_btot_disp4').html(formatCurrency(differ));
		if(differ < 0)
		{
			$('#mrr_btot_disp4').attr("style","font-weight:bold; color:#CC0000;");	
		}
		else
		{
			$('#mrr_btot_disp4').attr("style","font-weight:bold; color:#00CC00;");	
		}
		
		$('#mrr_btot_disp3').html(formatCurrency(differ));
		if(differ < 0)
		{
			$('#mrr_btot_disp3').attr("style","font-weight:bold; color:#CC0000;");	
		}
		else
		{
			$('#mrr_btot_disp3').attr("style","font-weight:bold; color:#00CC00;");	
		}
		$('#mrr_btot_disp4').hide();
		$('#mrr_btot_disp4a').hide();
	}
	function mrr_dynamic_charts_comparison(id,loader,moder,disp,cwidth,cheight,parts,part_titles,part_values1,part_values2,part_values3,part_values4,miles,sales_tot,invoiced,mrr_coa_cntr,mrr_coa_names,mrr_coa_numbs,mrr_coa_group)
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_reload_graph_comparison",
			type: "post",
			dataType: "xml",
			data: {
				"graph_id": id,
				"reload": loader,
				"moder": moder,
				"displayer": disp,				
				"chart_width": cwidth,
				"chart_height": cheight,
				"timesheet_total":timesheet_total,
				"miles": miles,
				"sales_tot": sales_tot,
				"invoiced": invoiced,
				"parts": parts,
				"date_from": $('#date_from').val(),
				"date_to": $('#date_to').val(),
				"part_titles[]": part_titles,
				"part_values1[]": part_values1,
				"part_values2[]": part_values2,
				"part_values3[]": part_values3,
				"part_values4[]": part_values4,
				"mrr_coa_cntr":mrr_coa_cntr,
				"mrr_coa_names[]":mrr_coa_names,
				"mrr_coa_numbs[]":mrr_coa_numbs,
				"mrr_coa_group[]":mrr_coa_group,
				"mrr_loaded_miles":mrr_loaded_miler,
				"mrr_rep_load_id":mrr_rep_load
			},
			error: function() {
				$.prompt("Error: No Graph could be found (A)");
			},
			success: function(xml) {
				if(id==1)
				{
     				if($(xml).find('GraphHTML').text() == '') {
     					//$('#mrr_graph_maker').html('Graph Not Found.');
     				} else {
     					mygraph=$(xml).find('GraphHTML').text();
     					mygraph2=$(xml).find('GraphHTML2').text();
     					
     					//$('#mrr_graph_maker').html(mygraph);
     					$('#mrr_graph_maker_table').html(mygraph2);	    						
     					
     					if($(xml).find('GraphBudget').text())
						{
							mrr_btot=parseFloat(get_amount($(xml).find('GraphBudget').text()));
							mrr_adjust_sales_report_numbers(mrr_btot);
						}
						
						mrr_calc_comparison();				
     				}
				}
				if(id==2)
				{
					if($(xml).find('GraphHTML').text() == '') {
     					//$('#mrr_graph_maker_2').html('Graph Not Found.');
     				} else {
     					mygraph=$(xml).find('GraphHTML').text();
     					     					
     					//$('#mrr_graph_maker_2').html(mygraph);	
     					if($(xml).find('GraphBudget').text())
						{
							mrr_btot=parseFloat(get_amount($(xml).find('GraphBudget').text()));
							mrr_adjust_sales_report_numbers(mrr_btot);
						}    									
     				}
				}				
			}
		});
	}
	
	function mrr_dynamic_charts_comparison_enlarger(id,loader,moder,disp,cwidth,cheight,parts,part_titles,part_values1,part_values2,part_values3,part_values4,miles,sales_tot,invoiced,mrr_coa_cntr,mrr_coa_names,mrr_coa_numbs,mrr_coa_group)
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_reload_graph_comparison",
			type: "post",
			dataType: "xml",
			data: {
				"graph_id": id,
				"reload": loader,
				"moder": moder,
				"displayer": disp,				
				"chart_width": cwidth,
				"chart_height": cheight,
				"miles": miles,
				"sales_tot": sales_tot,
				"invoiced": invoiced,
				"parts": parts,
				"date_from": $('#date_from').val(),
				"date_to": $('#date_to').val(),
				"part_titles[]": part_titles,
				"part_values1[]": part_values1,
				"part_values2[]": part_values2,
				"part_values3[]": part_values3,
				"part_values4[]": part_values4,
				"mrr_coa_cntr":mrr_coa_cntr,
				"mrr_coa_names[]":mrr_coa_names,
				"mrr_coa_numbs[]":mrr_coa_numbs,
				"mrr_coa_group[]":mrr_coa_group
			},
			error: function() {
				$.prompt("Error: No Graph could be found (B)");
			},
			success: function(xml) {
				if(id==1)
				{
     				/*
     				if($(xml).find('GraphHTML').text() == '') {
     					$('#mrr_graph_maker').html('Graph Not Found.');
     				} else {
     					mygraph=$(xml).find('GraphHTML').text();
     					mygraph2=$(xml).find('GraphHTML2').text();
     					
     					$('#mrr_graph_maker').html(mygraph);
     					$('#mrr_graph_maker_table').html(mygraph2);						
     				}
     				*/
				}
				if(id==2)
				{
					if($(xml).find('GraphHTML').text() == '') {
     					$('#mrr_graph_maker_2a').html('Graph Not Found.');
     				} else {
     					mygraph=$(xml).find('GraphHTML').text();
     					     					
     					$('#mrr_graph_maker_2a').html(mygraph);	    									
     				}
				}
				
			}
		});
	}
	
	function mrr_enlarge_pie_chart()
	{
		$('#mrr_clicker550').empty(); 
		txt=$('#mrr_graph_maker_2a').html();
		$.prompt(txt);
	}
	
	function mrr_coa_displayer_off()
	{
		$('#coa_lister_0').css("display","none");
		$('#coa_lister_1').css("display","none");	
		$('#coa_lister_2').css("display","none");	
		$('#coa_lister_3').css("display","none");	
		$('#coa_lister_5').css("display","none");	
		$('#coa_lister_6').css("display","none");
		$('#coa_lister_7').css("display","none");	
		$('#coa_lister_8').css("display","none");	
		$('#coa_lister_9').css("display","none");	
		$('#coa_lister_10').css("display","none");	
		$('#coa_lister_12').css("display","none");	
		$('#coa_lister_14').css("display","none");	
		$('#coa_lister_15').css("display","none");		
	}
	
	mrr_coa_displayer_off();
	
	function mrr_coa_displayer(coa)
	{
		var txt="";
		
		txt+="<div style='max-height:500px; height:500px; overflow:auto;'>";
		
		$('#coa_lister_'+coa+'').css("display","block");
		txt+=$('#coa_lister_'+coa+'').html();
		$('#coa_lister_'+coa+'').css("display","none");	
		
		txt+="</div>";
		$.prompt(txt);			
	}	
	
</script>
<? include('footer.php') ?>