<? include('application.php') ?>
<? $admin_page = 1; ?>
<?
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');

	if(!isset($_GET['id']))		$_GET['id']=0;
	if(!isset($_POST['id']))		$_POST['id']=0;
	
	if(isset($_GET['new']))	{	$_GET['id']=0;				$_POST['id']=0;	}	
	
	if(isset($_POST['id']))		$_GET['id']=$_POST['id'];
	if(isset($_GET['id']))		$_POST['id']=$_GET['id'];
	/*
	if(isset($_POST['budget_saver']))
	{	//save new budget and update new and old
		if($_POST['id'] == 0)
		{
			$sql="
			insert into budget
				(id,
				linedate_added,
				deleted)
			values
				(NULL,
				NOW(),
				0)
               ";	
          	simple_query($sql);
			$newid=mysql_insert_id();
			$_POST['id']=$newid;	
		}
		//update new and old budget
		if($_POST['id'] > 0)
		{
			$sql="
			update budget set
          		budget.linedate_start='".date("Y-m-d", strtotime($_POST['linedate_start']))." 00:00:00',
          		budget.linedate_ended='".date("Y-m-d", strtotime($_POST['linedate_ended']))." 23:59:59',
          		budget.budget_name='".sql_friendly( $_POST['budget_name'] )."',
          		budget.active='".( isset($_POST['active']) ? "1" : "0" )."'
			where budget.id='".sql_friendly( $_POST['id'] )."'
               ";	
          	simple_query($sql);	
		}
		
	}	
	*/
	
	//work area....temporary...records from 8-12-2010 to present...
	$update_result="";	
		
	$start_year=2012;								$end_year=2012;	//date("Y");	//change these to select year(s)
	$start_mon=1;									$end_mon=1;		//date("m");	//change these to select month(s)
	$start_day=1;									$end_day=31;		//date("d");	//change these to pick the day(s)
	$from_date='';									$to_date='';
	
	$turn_on=0;									$turn_on_single=0;				//update all fields with current(ranged if available) settings 		---if $turn_on>0
	
	$single_field="budget_payroll_admin";				$single_value="50000.00";		//exp: "budget_payroll_admin='50000.00'" 						---if $turn_on_single>0
	
	$multi_cntr=20;								$turn_on_multi=0;				//exp: "budget_payroll_admin='50000.00',budget_rent='5000.00'"		---if $turn_on_multi>0
	//field name									//value						//used if =1
	$multi_fields[0]="budget_average_mpg";				$multi_values[0]="5.5";			$multi_use[0]=1;
	$multi_fields[1]="budget_days_in_month";			$multi_values[1]="21";			$multi_use[1]=1;
	$multi_fields[2]="budget_labor_per_hour";			$multi_values[2]="20";			$multi_use[2]=1;
	$multi_fields[3]="budget_labor_per_mile";			$multi_values[3]="0.47";			$multi_use[3]=1;
	$multi_fields[4]="budget_labor_per_mile_team";		$multi_values[4]="0.60";			$multi_use[4]=1;
	$multi_fields[5]="budget_driver_week_hours";			$multi_values[5]="10";			$multi_use[5]=1;
	$multi_fields[6]="budget_tractor_maint_per_mile";		$multi_values[6]="0.01";			$multi_use[6]=1;
	$multi_fields[7]="budget_trailer_maint_per_mile";		$multi_values[7]="0.05";			$multi_use[7]=1;
	$multi_fields[8]="budget_truck_accidents_per_mile";	$multi_values[8]="0.01";			$multi_use[8]=1;
	$multi_fields[9]="budget_tires_per_mile";			$multi_values[9]="0.01";			$multi_use[9]=1;
	$multi_fields[10]="budget_mileage_exp_per_mile";		$multi_values[10]="0.07";		$multi_use[10]=1;
	$multi_fields[11]="budget_misc_exp_per_mile";		$multi_values[11]="0.01";		$multi_use[11]=1;
	$multi_fields[12]="budget_cargo_insurance";			$multi_values[12]="66.67";		$multi_use[12]=1;
	$multi_fields[13]="budget_general_liability";		$multi_values[13]="45.58";		$multi_use[13]=1;
	$multi_fields[14]="budget_liability_damage";			$multi_values[14]="558.02";		$multi_use[14]=1;
	$multi_fields[15]="budget_payroll_admin";			$multi_values[15]="55000.00";		$multi_use[15]=1;
	$multi_fields[16]="budget_rent";					$multi_values[16]="5000.00";		$multi_use[16]=1;
	$multi_fields[17]="budget_tractor_lease";			$multi_values[17]="2100.00";		$multi_use[17]=1;
	$multi_fields[18]="budget_trailer_exp";				$multi_values[18]="653.08";		$multi_use[18]=1;
	$multi_fields[19]="budget_trailer_lease";			$multi_values[19]="0.00";		$multi_use[19]=1;
	$multi_fields[20]="budget_misc_exp";				$multi_values[20]="0.00";		$multi_use[20]=1;
	
	$multi_lister="";
	for($i=0;$i <= $multi_cntr; $i++)
	{
		if($multi_use[ $i ] == 1)
		{
			$multi_lister.=" ".$multi_fields[ $i ]."='".sql_friendly( $multi_values[ $i ] )."',";	
		}
	}
	//$multi_lister.=")";			//marker for end
	//now remove extra comma and marker
	//$multi_lister=str_replace(",)","",$multi_lister);	
	//$multi_lister=str_replace(")","",$multi_lister);	
		
	//defaults
	$average_mpg=0;
     $billable_days_in_month=0;
     $labor_per_hour=0;
     $labor_per_mile=0;
     $labor_per_mile_team=0;
     $local_driver_workweek_hours=0;
     $sicap_integration=0;
     $tractor_maint_per_mile=0;
     $trailer_maint_per_mile=0;
     
    	$truck_accidents_per_mile=0;
	$tires_per_mile=0;
	$mileage_expense_per_mile=0;
	$misc_expense_per_mile=0;
     
     $average_mpg=mrr_get_default_variable_setting('average_mpg');
     $billable_days_in_month=mrr_get_default_variable_setting('billable_days_in_month');
     $labor_per_hour=mrr_get_default_variable_setting('labor_per_hour');
     $labor_per_mile=mrr_get_default_variable_setting('labor_per_mile');
     $labor_per_mile_team=mrr_get_default_variable_setting('labor_per_mile_team');
     $local_driver_workweek_hours=mrr_get_default_variable_setting('local_driver_workweek_hours');
     $sicap_integration=mrr_get_default_variable_setting('sicap_integration');
     $tractor_maint_per_mile=mrr_get_default_variable_setting('tractor_maint_per_mile');
     $trailer_maint_per_mile=mrr_get_default_variable_setting('trailer_maint_per_mile');
     
     $truck_accidents_per_mile=mrr_get_default_variable_setting('truck_accidents_per_mile');
	$tires_per_mile=mrr_get_default_variable_setting('tires_per_mile');
	$mileage_expense_per_mile=mrr_get_default_variable_setting('mileage_expense_per_mile');
	$misc_expense_per_mile=mrr_get_default_variable_setting('misc_expense_per_mile');
	
     //option settings 
    	$cargo_insurance=0;
     $general_liability=0;
     $liability_phy_damage=0;
     $payroll___admin=0;
     $rent=0;
     $tractor_lease=0;
     $trailer_expense=0;
     $trailer_lease=0;
	
	$cargo_insurance=mrr_get_option_variable_settings('Cargo Insurance');
     $general_liability=mrr_get_option_variable_settings('General Liability');
     $liability_phy_damage=mrr_get_option_variable_settings('Liability/Phy Damage');
     $payroll___admin=mrr_get_option_variable_settings('Payroll & Admin');
     $rent=mrr_get_option_variable_settings('Rent');
     $tractor_lease=mrr_get_option_variable_settings('Tractor Lease');
     $trailer_expense=mrr_get_option_variable_settings('Trailer Expense');
     $trailer_lease=mrr_get_option_variable_settings('Trailer Lease');
     $misc_expenses=mrr_get_option_variable_settings('Misc Expenses');  
     
	for($x=$start_year;$x <= (int)$end_year;$x++)
	{
		for($y=$start_mon;$y <= (int)$end_mon;$y++)
		{
			$ymask=$y;
			if($y < 10)		$ymask="0".$y;
			
			for($z=$start_day; $z <= (int)$end_day;$z++)
			{
				$zmask=$z;
				if($z < 10)		$zmask="0".$z;
				
				//now do update...
				$from_date=''.$x.'-'.$ymask.'-'.$zmask.' 00:00:00';				$to_date=''.$x.'-'.$ymask.'-'.$zmask.' 23:59:59';
				
				if($y==2 && $z==28)		$z=32;
				if($y==4 && $z==30)		$z=32;
				if($y==6 && $z==30)		$z=32;
				if($y==9 && $z==30)		$z=32;
				if($y==11 && $z==30)	$z=32;
				
				$truck_count=get_active_truck_count_ranged($from_date,$to_date);				
     			$trailer_count=get_active_trailer_count_ranged($from_date,$to_date);	//get_active_trailer_count();
    				$daily_cost=get_daily_cost(0,0);
    								
               	$sql="
                    	update load_handler set
                    		budget_average_mpg='".sql_friendly($average_mpg)."',
               			budget_days_in_month='".sql_friendly($billable_days_in_month)."',
               			budget_labor_per_hour='".sql_friendly($labor_per_hour)."',
               			budget_labor_per_mile='".sql_friendly($labor_per_mile)."',
               			budget_labor_per_mile_team='".sql_friendly($labor_per_mile_team)."',
               			budget_driver_week_hours='".sql_friendly($local_driver_workweek_hours)."',
               			budget_tractor_maint_per_mile='".sql_friendly($tractor_maint_per_mile)."',
               			budget_trailer_maint_per_mile='".sql_friendly($trailer_maint_per_mile)."',
               			budget_truck_accidents_per_mile='".sql_friendly($truck_accidents_per_mile)."',
               			budget_tires_per_mile='".sql_friendly($tires_per_mile)."',
               			budget_mileage_exp_per_mile='".sql_friendly($mileage_expense_per_mile)."',
               			budget_misc_exp_per_mile='".sql_friendly($misc_expense_per_mile)."',
               			budget_cargo_insurance='".sql_friendly($cargo_insurance)."',
               			budget_general_liability='".sql_friendly($general_liability)."',
               			budget_liability_damage='".sql_friendly($liability_phy_damage)."',
               			budget_payroll_admin='".sql_friendly($payroll___admin)."',
               			budget_rent='".sql_friendly($rent)."',
               			budget_tractor_lease='".sql_friendly($tractor_lease)."',
               			budget_trailer_exp='".sql_friendly($trailer_expense)."',
               			budget_trailer_lease='".sql_friendly($trailer_lease)."',
               			budget_misc_exp='".sql_friendly($misc_expenses)."',
               			budget_active_trucks='".sql_friendly( (int)$truck_count['billable'] )."',
               			budget_active_trailers='".sql_friendly( (int)$trailer_count['billable'] )."',
               			budget_day_variance='".sql_friendly( $daily_cost )."'			
                    	where linedate_added>='".sql_friendly($from_date)."'
                    		and linedate_added<='".sql_friendly($to_date)."'	
                    ";
                   	if($turn_on > 0)
                   	{
                   	 	simple_query($sql);
                   	 	$update_result.="Updated all loads where linedate_added>='".sql_friendly($from_date)."' and linedate_added<='".sql_friendly($to_date)."'<br>";
               	}
               	else
               	{
               		$update_result.="Update is turned OFF where linedate_added>='".sql_friendly($from_date)."' and linedate_added<='".sql_friendly($to_date)."'<br>";
               	}
               	
               	if($turn_on_single > 0 && trim($single_field)!="")
               	{
               		$sql="
                    		update load_handler set
                    			".$single_field."='".sql_friendly( $single_value )."'	
                    					
                    		where linedate_added>='".sql_friendly($from_date)."'
                    			and linedate_added<='".sql_friendly($to_date)."'	
                   	 	";	
                   	 	simple_query($sql);
                   	 	$update_result.="Updated single field ".$single_field."='".$single_value."' for all loads where linedate_added>='".sql_friendly($from_date)."' and linedate_added<='".sql_friendly($to_date)."'<br>";
               	}  
               	
               	if($turn_on_multi > 0)
               	{	//multi-lister willl always have "," comma at the end field              		
               		$sql="
                    		update load_handler set
                    			".$multi_lister."
                    			budget_active_trucks='".sql_friendly( (int)$truck_count['billable'] )."',
          	     			budget_active_trailers='".sql_friendly( (int)$trailer_count['billable'] )."',
 		              			budget_day_variance='".sql_friendly( $daily_cost )."'	
 		              				
                    		where linedate_added>='".sql_friendly($from_date)."'
                    			and linedate_added<='".sql_friendly($to_date)."'	
                   	 	";	
                   	 	simple_query($sql);
                   	 	$update_result.="Updated multi fields for all loads where linedate_added>='".sql_friendly($from_date)."' and linedate_added<='".sql_friendly($to_date)."'<br>";	
               	}               	         	
			}//end day loop
		}//end month loop
	}//end year loop
     
?>
<? include('header.php') ?>

<?
	
     $mrr_checked_budget=" checked";
     $mrr_rows=0;
	/*	
	if(isset($_POST['id'])
	{
		$sql="
			select budget.*
			from budget
			where budget.id='".sql_friendly( $_POST['id'] )."'
               ";	
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {
          	$_POST['id']=$row['id']; 
          	$_POST['linedate_added']=date("m/d/Y", strtotime($row['linedate_added'])); // H:i:s
          	$_POST['linedate_start']=date("m/d/Y", strtotime($row['linedate_start'])); 
          	$_POST['linedate_ended']=date("m/d/Y", strtotime($row['linedate_ended'])); 
          	$_POST['budget_name']=$row['budget_name']; 
          	$_POST['active']=$row['active']; 
          	//$del=$row['deleted']; 
          	if($row['active'] == 0)	$mrr_checked_budget="";
          	          	
          }
		
		$mrr_rows++;	
	}
	else
	{
		$_POST['linedate_added']=date("m/d/Y", time()); // H:i:s
    	 	$_POST['linedate_start']=''; 
     	$_POST['linedate_ended']=''; 
     	$_POST['budget_name']=''; 
     	$_POST['active']=0; 	
	}
	*/
?>
<div><?= $update_result ?></div>
<table style='text-align:left'>
<tr>
	<td valign='top'>
		<table class='admin_menu1'>
		<tr>
		<td valign='top'>
          	<font class='standard18'><b>Admin Budget</b></font>
          	<br>
          	<div class='mrr_link_like_on' onClick='mrr_get_this_budget(0);'><b>Add New Budget</b></div>
          	
          	<table class='admin_menu2 tablesorter'>
          	<thead>
          	<tr>
          		<th valign='top'>Budget</th>
          		<th valign='top'>Created</th>
          		<th valign='top'>Starting</th>
          		<th valign='top'>Ending</th>
          		<th valign='top'>Active</th>
          		<th valign='top'></th>
          	</tr>
          	</thead>
          	<tbody id='mrr_body'>
          		
          	</tbody>
          	</table>
		</td>
		</tr>
		</table>
	</td>
	<td valign='top'>
		<form action="<?=$SCRIPT_NAME?>?id=<?=$_GET['id']?>" method="post">
		<table class='admin_menu1'>
		<tr>
		<td valign='top'>
			<input type='hidden' id='id' name='id' value='<?= $_POST['id'] ?>'>
          	<table class='admin_menu2'>
			<tr>
				<td valign='top'>Budget Label</td>
				<td valign='top' colspan='3'><input name='budget_name' id='budget_name' class='input_normal' value='<?= $_POST['budget_name'] ?>'></td>
				<td valign='top' width='75'>Starts</td>
				<td valign='top'><input name='linedate_start' id='linedate_start' class='input_date' value='<?= $_POST['linedate_start'] ?>'></td>
				<td valign='top' width='75'>Expires</td>
				<td valign='top'><input name='linedate_ended' id='linedate_ended' class='input_date' value='<?= $_POST['linedate_ended'] ?>'></td>
				<td valign='top'>Created <span id='linedate_added'><?= $_POST['linedate_added'] ?></span></td>
				<td valign='top'><label for='active'>Active</label> <input type='checkbox' name='active' id='active' value='1'<?= $mrr_checked_budget ?>></td>				
				<td valign='top' align='right'><input type='button' name='budget_saver' id='budget_saver' value='Save Budget' onClick='mrr_save_this_budget()'></td>
			</tr>		
			</table>	
			<!-- Drop ajax list below for budget items -->
			<br>
			<table class='admin_menu2  tablesorter'>
			<thead>
          	<tr>
          		<th valign='top' nowrap>Item</th>
          		<th valign='top' nowrap>Category</th>
          		<th valign='top' nowrap>Per Mile</th>
          		<th valign='top' nowrap>Per Truck</th>
          		<th valign='top' nowrap>Per Trailer</th>
          		<th valign='top' nowrap>Per Driver</th>
          		<th valign='top' nowrap>Per Dispatch</th>
          		<th valign='top' nowrap>Per Load</th>
          		<th valign='top' nowrap>Flat Amount</th>
          		<th valign='top' nowrap>Total</th>
          	</tr>
          	</thead>
          	<tbody id='budget_item_list'>
          		
          	</tbody>
			</table>
			<center>
			<b>Budget Total is $<span id='total_budget'></span></b><br>	
			<br><input type='hidden' name='budget_items_cntr' id='budget_items_cntr' value='0'>
			<input type='button' name='budget_items_saver' id='budget_items_saver' value='Save Items' onClick='mrr_save_these_budget_items()'>
			</center>
		</td>
		</tr>
		</table>
		
		</form>
	</td>
</tr>
</table>
<?
	$mrr_res=get_cur_counts_for_period($_POST['linedate_start'],$_POST['linedate_ended']);
?>
<script type='text/javascript'>
	var cur_miles=<?= $mrr_res['mile'] ?>;
	var cur_trucks=<?= $mrr_res['truck'] ?>;
	var cur_trailers=<?= $mrr_res['trailer'] ?>;
	var cur_drivers=<?= $mrr_res['driver'] ?>;
	var cur_disps=<?= $mrr_res['dispatch'] ?>;
	var cur_loads=<?= $mrr_res['load'] ?>;
	
	//$('#linedate_added').datepicker();
	$('#linedate_start').datepicker();
	$('#linedate_ended').datepicker();
	//$(".tablesorter").tablesorter();		//{textExtraction: 'complex'}
	
	//update maintenance request listing for first load.
	$().ready(function() {
		mrr_load_all_budgets();	
		$('#total_budget').html(formatCurrencyMRR(0));
		if($('#id').val() >0)
		{
			mrr_load_all_budget_items( $('#id').val() );	
			mrr_update_total_budget();
		}	
	});
	
	//loader functions 
	function mrr_load_all_budget_items(id)
	{
		$.ajax({
     			url: "ajax.php?cmd=mrr_ajax_get_budget_item_list",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output
     				"id": $('#id').val() 
     			},
     			error: function() {
     				alert('general error loading budget items');
     			},
     			success: function(xml) {
     				$('#budget_item_list').html('');
     				
     				$('#budget_item_list').html($(xml).find('BudgetTable').text());
     				$('#budget_items_cntr').val($(xml).find('BudgetCntr').text());
     				mrr_update_total_budget();   				   				
     			}
     		});
	}
	function mrr_load_all_budgets()
	{
		$.ajax({
     			url: "ajax.php?cmd=mrr_ajax_get_budget_list",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output
     				"id": '0'  
     			},
     			error: function() {
     				alert('general error loading budgets');
     			},
     			success: function(xml) {
     				$('#mrr_body').html('');
     				
     				$('#mrr_body').html($(xml).find('BudgetTable').text());  				   				
     			}
     		});
	}
	
	//save/update
	function mrr_update_budget_item_row(rownum)
	{
		var myid=$('#id_"'+rownum+'').val();
		var mile=$('#mile_'+rownum+'').val();
		var truck=$('#truck_'+rownum+'').val();
		var trailer=$('#trailer_'+rownum+'').val();
		var driver=$('#driver_'+rownum+'').val();
		var dispatch=$('#dispatch_'+rownum+'').val();
		var loaded=$('#load_'+rownum+'').val();
		var flat=$('#flat_'+rownum+'').val();
		var amnt=0;	//$('#amnt_'+rownum+'').val();
		
		amnt+=(parseFloat(cur_miles) * parseFloat(mile));
		amnt+=(parseFloat(cur_trucks) * parseFloat(truck));
		amnt+=(parseFloat(cur_trailers) * parseFloat(trailer));
		amnt+=(parseFloat(cur_drivers) * parseFloat(driver));
		amnt+=(parseFloat(cur_disps) * parseFloat(dispatch));
		amnt+=(parseFloat(cur_loads) * parseFloat(loaded));
		amnt+=(parseFloat(flat));
		
		$('#amnt_'+rownum+'').val(amnt.toFixed(2));	//formatCurrencyMRR(amnt)
		
		mrr_update_total_budget();
	}
	function mrr_update_total_budget()
	{
		var cntr=$('#budget_items_cntr').val();
		var gtot=0;
		var i=0;
		for(i=0;i < cntr; i++)
		{
			tmp=$('#amnt_'+i+'').val();			
			gtot+=parseFloat(tmp);
			//$('#amnt_'+i+'').val(tmp.toFixed(2));
		}
		$('#total_budget').html(formatCurrencyMRR(gtot));
	}
	function formatCurrencyMRR(num) 
	{
     	num = num.toString().replace(/\$|\,/g,'');
     	if(isNaN(num))
     	num = "0";
     	sign = (num == (num = Math.abs(num)));
     	num = Math.floor(num*100+0.50000000001);
     	cents = num%100;
     	num = Math.floor(num/100).toString();
     	if(cents<10)
     	cents = "0" + cents;
     	for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
     	num = num.substring(0,num.length-(4*i+3))+','+
     	num.substring(num.length-(4*i+3));
     	return (((sign)?'':'-') + '' + num + '.' + cents);	//$
	}
	function mrr_save_this_budget()
	{
		//alert("POST ID='"+$('#id').val()+"'.");
		$.ajax({
     			url: "ajax.php?cmd=mrr_ajax_save_budget",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output
     				"id": $('#id').val(),
     				"budget_name": $('#budget_name').val(),
     				"linedate_start": $('#linedate_start').val(), 
     				"linedate_ended": $('#linedate_ended').val(), 
     				"active": ($('#active').is(':checked') ? 1 : 0)
     			},
     			error: function() {
     				alert('general error saving budget');
     			},
     			success: function(xml) {
     				$.noticeAdd({text: "Success - Budget Updated."});  
     				mrr_load_all_budgets();
     				  				
     				$('#id').val($(xml).find('BudgetID').text());
     				if($(xml).find('BudgetID').text() >0)
					{
						mrr_load_all_budget_items( $(xml).find('BudgetID').text() );
						mrr_update_total_budget();
					}   				
     			}
     		});
	}
	function mrr_save_these_budget_items()
	{
		var cntr=$('#budget_items_cntr').val();
		var i=0;
		for(i=0;i < cntr; i++)
		{
			//alert("Cat Number='"+i+"'.");
			$.ajax({
     			url: "ajax.php?cmd=mrr_ajax_save_budget_item",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output
     				"id": $('#id_'+i+'').val(),
     				"mile": $('#mile_'+i+'').val(),
     				"truck": $('#truck_'+i+'').val(),
     				"trailer": $('#trailer_'+i+'').val(),
     				"driver": $('#driver_'+i+'').val(),
     				"dispatch": $('#dispatch_'+i+'').val(),
     				"load": $('#load_'+i+'').val(),
     				"flat": $('#flat_'+i+'').val(),
     				"amnt": $('#amnt_'+i+'').val()
     			},
     			error: function() {
     				alert('general error saving budget item '+i+'. ID='+  $('#id_'+i+'').val()  +'.');
     			},
     			success: function(xml) {
     				//$.noticeAdd({text: "Success - Budget Item Updated."});  
     				// alert('Saving budget ID '+$('#id_'+i+'').val()+'. Amnt='+  $('#amnt_'+i+'').val()  +'.'); 		
     						
     			}
     		});	
		}		
	}	
	
	//finder
	function mrr_get_this_budget(id)
	{
		//alert("Get ID='"+id+"'.");
		$.ajax({
     			url: "ajax.php?cmd=mrr_ajax_get_budget",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output
     				"id": id     
     			},
     			error: function() {
     				alert('general error getting budget');
     			},
     			success: function(xml) {
     				
     				$(xml).find('Budget').each(function() {
     					$('#id').val(id);
     					$('#budget_name').val($(this).find('BudgetName').text());
     					$('#linedate_added').html($(this).find('BudgetAdded').text());
     					$('#linedate_start').val($(this).find('BudgetStart').text());
     					$('#linedate_ended').val($(this).find('BudgetEnded').text());
     					$('#active').attr('checked',($(this).find('BudgetActive').text()== 1 ? 'checked' : ''));
     					     					
     					//run budget items list here...
     					if($('#id').val() >0)
						{
							mrr_load_all_budget_items( $('#id').val() );	
						} 
						if($('#id').val() ==0)
						{
							$('#budget_item_list').html('');
							$('#budget_items_cntr').val('0');
							$('#total_budget').html(formatCurrencyMRR(0));
						} 
     				});	
     			}
     		});
	}
	
	//removal		
	function confirm_budget_delete(id) {
		$.prompt("Are you sure you want to delete this budget?", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {
					mrr_remove_this_budget(id);
				}
			}
		});

	}	
	function mrr_remove_this_budget(id)
	{
		$.ajax({
     			url: "ajax.php?cmd=mrr_ajax_delete_budget",
     			type: "post",
     			dataType: "xml",
     			data: {
     				//POST variables needed for "page" to load for XML output
     				"id": id     
     			},
     			error: function() {
     				alert('general error removing budget');
     			},
     			success: function(xml) {
     				$.noticeAdd({text: "Success - Budget Removed."});     				
     				mrr_load_all_budgets();
					$('#total_budget').html(formatCurrencyMRR(0));
					$('#id').val('0');
					$('#budget_item_list').html('');
					$('#budget_items_cntr').val('0');
					$('#total_budget').html(formatCurrencyMRR(0));
     			}
     		});
	}
	
</script>

<? include('footer.php') ?>