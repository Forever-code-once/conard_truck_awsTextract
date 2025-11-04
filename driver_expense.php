<?
$use_title="Add Driver Expense";		$usetitle=$use_title;
?>
<? include('header.php') ?>
<?

	if(!isset($_POST['driver_expense_id'])) $_POST['driver_expense_id'] = 0;

	
	//get expense account listing......................................................................................
	if(!isset($_POST['mrr_chart_id']))		$_POST['mrr_chart_id']=0;
	if(!isset($_POST['mrr_chart_name']))	$_POST['mrr_chart_name']="";
	
	//$results=mrr_get_coa_list('','67000');	//first arg is $chart_id, second arg is $chart_number	
	/*
	$results=mrr_get_coa_list('','');	//first arg is $chart_id, second arg is $chart_number	
		
	$sel_chart="";			if($_POST['mrr_chart_id']==0)	$sel_chart=" selected";		
	$mrr_billing="
		<select name='mrr_chart_id' id='mrr_chart_id'>
			<option value='0'".$sel_chart."></option>
	";
	
	$cntr=0;
	$chart_id=0;					$chart_acct="";		$chart_name="";
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
          			$sel_chart="";			if($_POST['mrr_chart_id']==$chart_id)	$sel_chart=" selected";	
          			$mrr_billing.="
          				<option value='".$chart_id."'".$sel_chart.">".$chart_name."</option>
          			";	// ".$chart_acct."
          			$cntr++;
          			$chart_id=0;			$chart_acct="";		$chart_name="";
          		}
     		}//end for loop for each chart entry
		}//end if
	}//end for loop for each result returned
	$mrr_billing.="
		</select>
	";
	*/
	
	//...................................................................................................................
	$output="";
	if(isset($_POST['driver_id'])) {
		//NOT USED>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>AJAX FUNCTION ON BUTTON
		/*
		if($_POST['driver_expense_id'] == 0) {
			$sql = "
				insert into drivers_expenses
					(linedate_added,
					created_by_user_id)
					
				values (now(),
					'".sql_friendly($_SESSION['user_id'])."')
			";
			simple_query($sql);
			
			$_POST['driver_expense_id'] = mysql_insert_id();
		}
		
		//$use_chart_id=(int) $_POST['mrr_chart_id'];		
		
		$use_chart_id=mrr_get_coa_chart_id_by_name($_POST['mrr_chart_name']);
		$exptype=$_POST['expense_type_id'];
		
		if($use_chart_id==0)
		{	//attempt to assign the ID based on the general type.
			
			if($exptype==27)		$use_chart_id=1707;		//generic lease drivers
			if($exptype==34)		$use_chart_id=1707;		//generic lease drivers
			if($exptype==142)		$use_chart_id=1707;		//generic lease drivers
			
			if($exptype==26)		$use_chart_id=1716;		//Misc Exp - All trucks
			if($exptype==103)		$use_chart_id=1726;		//Tolls
		}
		
		//$output="<br>Type=".$_POST['expense_type_id'].", Chart ID=".$use_chart_id.", Chart name=".$_POST['mrr_chart_name']."<br>";
				
		$sql = "
			update drivers_expenses set
				driver_id = '".sql_friendly($_POST['driver_id'])."',
				linedate = '".date("Y-m-d", strtotime($_POST['linedate']))."',
				desc_long = '".sql_friendly($_POST['description'])."',
				billable = '".(isset($_POST['billable']) ? '1' : '0')."',
				expense_type_id = '".sql_friendly($_POST['expense_type_id'])."',
				amount = '".sql_friendly($_POST['amount'])."',
				amount_billable = '".sql_friendly($_POST['amount_billable'])."',
				chart_id = '".sql_friendly($use_chart_id)."',
				payroll = '".(isset($_POST['payroll']) ? '1' : '0')."'
			
			where id = '".sql_friendly($_POST['driver_expense_id'])."'
		";
		simple_query($sql);		
		*/
	}

	/* get the driver list */
	$sql = "
		select *
		
		from drivers
		where deleted = 0
		order by active desc, name_driver_last, name_driver_first
	";
	$data_drivers = simple_query($sql);
	
	$sql = "
		select *
		
		from drivers_expenses
		where id = '".sql_friendly($_POST['driver_expense_id'])."'
	";
	$data = simple_query($sql);
	$row = mysqli_fetch_array($data);	
	
?>
<table>
<tr>
	<td valign='top'>
		<h1><?=$use_title ?></h1>
		<table class='admin_menu1' style='text-align:left;margin-top:10px'>
		<tr>
			<td colspan='2'><?=$output ?></td>
		</tr>
		<tr>
			<td>Driver</td>
			<td>
				<select name='driver_id' id='driver_id'>
					<option value='0'>All Drivers</option>
					<?
					while($row_driver = mysqli_fetch_array($data_drivers)) 
					{ 
						echo "<option value='$row_driver[id]' ".($row_driver['id'] == $row['driver_id'] ? 'selected' : '').">".(!$row_driver['active'] ? '(inactive) ' : '')."$row_driver[name_driver_last], $row_driver[name_driver_first]</option>";
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Date</td>
			<td><input name='linedate' id='linedate' value='<?=($row['linedate'] > 0 ? date("m-d-Y", strtotime($row['linedate'])) : '')?>'></td>
		</tr>
		<tr>
			<td>Account</td>
			<td>
				<input type='text' name='mrr_chart_name' id='mrr_chart_name' value='<?=$_POST['mrr_chart_name'] ?>'>
				<input type='hidden' name='mrr_chart_id' id='mrr_chart_id' value='<?=$use_chart_id ?>'>
			</td>
			<!--
			<td><?=$mrr_billing ?></td>
			-->
		</tr>
		<tr>
			<td>Expense Type</td>
			<td>
				<? build_option_box('driver_expense_type','','expense_type_id') ?>
			</td>
		</tr>
		<tr>
			<td>Description</td>
			<td><textarea name='description' id='description' style='width:350px;height:80px'></textarea></td>
		</tr>
		<tr>
			<td><label for='payroll'>Add to Payroll</label></td>
			<td><input type='checkbox' name='payroll' id='payroll'></td>
		</tr>
		<tr>
			<td>Driver Pay Amount</td>
			<td><input name='amount' id='amount' size='10'></td>
		</tr>
		<tr>
			<td>Billed Amount</td>
			<td><input name='amount_billable' id='amount_billable' size='10'></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type='button' value='Submit' onclick="add_driver_expense()"></td>
		</tr>
		</table>
	</td>
	<td valign='top' style='text-align:left'>
		<h1>Driver Expense History</h1>
		<div id='driver_expense_history' style='margin-top:10px'></div>
	</td>
</tr>
</table>
<script type='text/javascript'>
	$('#linedate').datepicker();
	
	$('#driver_id').change(function() {
		$('#driver_expense_history').html(load_driver_expenses($(this).val()));
	});

	if($('#driver_id').val() != '0') {
		$('#driver_expense_history').html(load_driver_expenses($('#driver_id').val()));
	}
	$('#mrr_chart_name').autocomplete('ajax.php?cmd=search_coa_chart',{formatItem:formatItem});
</script>
<? include('footer.php') ?>