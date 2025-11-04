<? 
$usetitle = "Switch/Shuttle Time Sheets";
$use_title = 'Switch/Shuttle Time Sheets';
$use_bootstrap = true;
?>
<? include('header.php') ?>
<?
	if(!isset($_POST['customer_id']))			    $_POST['customer_id']=0;
	if(!isset($_POST['display_mode']))			    $_POST['display_mode']=1;
	if(!isset($_POST['location_id']))			    $_POST['location_id']=0;
	
	if(!isset($_POST['timesheet_start_date']))	    $_POST['timesheet_start_date']=date("m/d/Y",time());
	if(!isset($_POST['timesheet_end_date']	))	    $_POST['timesheet_end_date']=date("m/d/Y",time());
	if(!isset($_POST['timesheet_load_id']))		    $_POST['timesheet_load_id']=0;
	if(!isset($_POST['timesheet_disp_id']))		    $_POST['timesheet_disp_id']=0;
	
	if(!isset($_POST['shuttle_runs']))			    $_POST['shuttle_runs']=0;
    if(!isset($_POST['shuttle_mileage_fuel_rate']))	$_POST['shuttle_mileage_fuel_rate']="0.0000";
	
	/* get the customer list */
	$sql = "
		select customers.*		
		from customers
		where customers.deleted = 0
			and customers.active>0
			and customers.use_timesheets > 0
		order by customers.name_company
	";
	$data_customers = simple_query($sql);
?>
<div class='container col-md-12'>
	<div class='col-md-6'>
		<div class="panel panel-info">
			<div class="panel-heading">New Time Sheet</div>
			  <div class="panel-body">
			  	
			  	Customer: 
			  	<select name="customer_id" id="customer_id" onChange='list_cust_timesheets();'>
					<option value="0"<?=($_POST['customer_id']==0 ? " selected" : "") ?>>Select Customer</option>
					<? 
					while($row_customers = mysqli_fetch_array($data_customers)) 
					{
						$sel="";		if($_POST['customer_id'] == $row_customers['id']) $sel=" selected";
						if($row_customers['id']==1601)
						{
							echo "<option value='".$row_customers['id']."'".$sel.">Carlex Glass C/O CT Logistics</option>";
						}
						else
						{
							echo "<option value='".$row_customers['id']."'".$sel.">".$row_customers['name_company']."</option>";
						}
					} 
					?>
				</select>
				
				Display:
				<select name="display_mode" id="display_mode" onChange='list_cust_timesheets();'>
					<option value="0"<?=($_POST['display_mode']==0 ? " selected" : "") ?>>All Time Sheets</option>
					<option value="1"<?=($_POST['display_mode']==1 ? " selected" : "") ?>>Not Invoiced</option>
					<option value="2"<?=($_POST['display_mode']==2 ? " selected" : "") ?>>Invoiced</option>
				</select>
				
				<input type='hidden' name='timesheet_id' id='timesheet_id' value='0'>
			  	
			  	<br><br>
				<table class='table well table-bordered' width='100%'>
				<thead>
				<tr>
					<th>Date From</th>
					<th>Date To</th>
					<th>Location</th>
					<th>Shuttle Runs</th>
					<th>&nbsp;</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td valign='top'>
						<input name='timesheet_start_date' id='timesheet_start_date' class='date_picker_rates' value='<?= $_POST['timesheet_start_date'] ?>' style='width:80px;'>
					</td>	
					<td valign='top'>
						<input name='timesheet_end_date' id='timesheet_end_date' class='date_picker_rates' value='<?= $_POST['timesheet_end_date'] ?>' style='width:80px;'>
					</td>
					<td>
               				<select name='location_id' id='location_id'>
               					<option value="0"<?=($_POST['location_id']==0 ? " selected" : "") ?>>Select Location</option>
								<option value="1"<?=($_POST['location_id']==1 ? " selected" : "") ?>>Nashville</option>
								<option value="2"<?=($_POST['location_id']==2 ? " selected" : "") ?>>Lebanon</option>
							</select>
               		</td>
					<td valign='top'>
						<input name='shuttle_runs' id='shuttle_runs' value='<?= $_POST['shuttle_runs'] ?>' style='width:80px;'>
					</td>
					<td valign='top'>
						<button onclick='add_cust_timesheets();' class='btn btn-success'><span class="glyphicon glyphicon-plus"></span> Add Time Sheet</button>
					</td>
				</tr>	
				</tbody>
				</table>				
			  </div>

		</div>
	</div>
		
	<div class='col-md-6'>
		<div class="panel panel-info">
			<div class="panel-heading">Time Sheets</div>
			  <div class="panel-body">
			  	
				<span id='timesheet_holder'></span>
				
			  </div>

		</div>		
	</div>
	
</div>
<div class='container col-md-12'>
	<div class='col-md-12'>
		<div class="panel panel-primary">
			<div class="panel-heading">Switching/Shuttle Time Sheet Entries  <?= show_help('add_entry_truck.php','Switching/Shuttle Time Sheets') ?></div>
			  <div class="panel-body" id='mrr_line_items'>
					
				<table class='table table-bordered well'>
				<thead>
				<tr>
					<th>Date</th>
					<th>Conard Time In</th>
					<th>Company Time In</th>
					<th>Company Time Out</th>
					<th>Conard Time Out</th>		
					<th>Lunch Break</th>
					<th>Days Run</th>
					
					<th>Driver</th>	
					<th>Truck</th>
					<th>Trailer</th>												
					<th>Miles</th>
					<th>Deadhead</th>					
					<th>Shuttle Route</th>					
					<th>Shuttle Rate</th>											
				</tr>
				</thead>
				<tbody id='timesheet_entries_form'>
				</tbody>
				</table>	
				<input type='hidden' name='time_sheet_entries_cnt' id='time_sheet_entries_cnt' value='0' style='width:50px;'>	
				
				<div id='switch_shuttle_rates_holder'></div>	
                  
                  
                <?php
                    //shuttle_mileage_fuel_rate
                    echo "
                        <br><b>Shuttle Mileage:</b>
                        Total Miles from runs = <span id='shuttle_mileage_fuel_rate_miles'>0.00</span> Miles
                        at $<input type='text' name='shuttle_mileage_fuel_rate' id='shuttle_mileage_fuel_rate' value='".$_POST['shuttle_mileage_fuel_rate']."' onBlur='mrr_update_shuttle_mileage_rate();'> per mile.
                        Total Rate is <b><span id='shuttle_mileage_fuel_rate_total'>$0</span></b> for Invoice.
                    ";   
                ?>
				
			</div>
		</div>
	</div>
</div>

<script type='text/javascript'>
	
	var customer_id=<?= $_POST['customer_id'] ?>;
	
	$().ready(function() {
			
		$('.date_picker_rates').datepicker();
		list_cust_timesheets();
		select_cust_timesheets(0);
	});


    function mrr_get_shuttle_mileage_fuel_rate(id)
    {
        $('#shuttle_mileage_fuel_rate').val(0.0000);
        var rate_val=0.0000;
        
        $.ajax({
            url: "ajax.php?cmd=mrr_get_miles_timesheet_invoice",
            type: "post",
            dataType: "xml",
            async: false,
            data: {
                //POST variables needed for "page" to load for XML output
                "id": id
            },
            error: function() {
                alert('general error getting timesheet mileage fuel rate per mile.');
            },
            success: function(xml) {

                if($(xml).find('rslt').text() == "1")
                {
                    rate_val=parseFloat($(xml).find('rate').text());
                    $('#shuttle_mileage_fuel_rate').val(rate_val);
                }
                else
                {
                    $.prompt("<span class='alert'>Error:</span> Cannot find the mileage rate for this timesheet.");
                }
            }
        });
    }
	
	function mrr_calc_shuttle_mileage_fuel_rate_amnt(id)
    {
        $('#shuttle_mileage_fuel_rate_total').html('Calculating...');
        var rate_val_mileage=parseFloat($('#shuttle_mileage_fuel_rate').val());
        var rate_val_miles=parseFloat($('#shuttle_mileage_fuel_rate_miles').html());
        var gt_rate = rate_val_mileage * rate_val_miles;

        $.ajax({
            url: "ajax.php?cmd=mrr_calc_miles_timesheet_invoice",
            type: "post",
            dataType: "xml",
            data: {
                //POST variables needed for "page" to load for XML output
                "id": id
            },
            error: function() {
                alert('general error setting timesheet mileage fuel rate per mile.');
            },
            success: function(xml) {

                if($(xml).find('rslt').text() == "1")
                {
                    rate_val_miles=parseFloat($(xml).find('miles').text());
                    $('#shuttle_mileage_fuel_rate_miles').html(rate_val_miles);

                    gt_rate = parseFloat(rate_val_mileage) * parseFloat(rate_val_miles);
                    $('#shuttle_mileage_fuel_rate_total').html(formatCurrency(gt_rate));

                    $.noticeAdd({text: "Time Sheet Shuttle Mileage rate is "+rate_val_miles+" miles and $"+rate_val_mileage+", or "+formatCurrency(gt_rate)+"."});
                }
                else
                {
                    $.prompt("<span class='alert'>Error:</span> Cannot calculate the miles or the mileage rate amount.");
                    $('#shuttle_mileage_fuel_rate_total').html('0');
                }
            }
        });        
    }
	function mrr_update_shuttle_mileage_rate()
    {
        var ts_id=parseInt($('#timesheet_id').val());
        var rate_val_mileage=parseFloat($('#shuttle_mileage_fuel_rate').val());
        
        if(ts_id > 0)
        {
            $.ajax({
                url: "ajax.php?cmd=mrr_set_miles_timesheet_invoice",
                type: "post",
                dataType: "xml",
                data: {
                    //POST variables needed for "page" to load for XML output
                    "id": ts_id,
                    "rate":rate_val_mileage
                },
                error: function() {
                    alert('general error setting timesheet mileage fuel rate per mile.');
                },
                success: function(xml) {

                    if($(xml).find('rslt').text() == "1")
                    {
                        $.noticeAdd({text: "Time Sheet Shuttle Mileage rate has been update for this Time Sheet."});
                        mrr_calc_shuttle_mileage_fuel_rate_amnt(ts_id);
                    }
                    else
                    {
                        $.prompt("<span class='alert'>Error:</span> Cannot update the timesheet mileage fuel rate.");
                    }
                }
            });           
        }
        else
        {
            $.prompt("<span class='alert'>Error:</span> No Time Sheet selected to update.");
        }
    }
	
	function add_timesheet_invoice(id)
	{
		customer_id=parseInt($('#customer_id').val());
		
		if(id==0 && customer_id==0)
		{
			$.prompt("<span class='alert'>Error:</span> No Time Sheet or Customer.");	
			return;
		}
		
		$.ajax({
     		url: "ajax.php?cmd=mrr_make_sicap_invoice_timesheet",
     		type: "post",
     		dataType: "xml",
     		data: {
     			//POST variables needed for "page" to load for XML output
     			"id": id,
     			"cust_id":customer_id
     		},
     		error: function() {
     			alert('general error removing invoice linkage.');
     		},
     		success: function(xml) {
     			
     			if($(xml).find('rslt').text() == "1")
     			{
     				invid=parseInt($(xml).find('InvoiceID').text());
          			if(invid > 0)
          			{
          				$.noticeAdd({text: "Time Sheet Invoice has been created."});
          				list_switch_shuttle_rates();
          			}
          			else
          			{
          				$.prompt("<span class='alert'>Error:</span> No Time Sheet Invoice created.");	
          				list_switch_shuttle_rates();	
          			}
     			}
     			else
     			{
     				$.prompt("<span class='alert'>Error:</span> Cannot add the Time Sheet Invoice.");	
     				list_switch_shuttle_rates();	
     			}    			
     		}
     	});			
	}
	
	function timesheet_invoice_del(id) 
	{
		if(id==0)		return;	
		
		$.prompt("Are you sure you want to remove the invoice link from this Time Sheet? <br><br><span class='alert'><b><i>--Don't forget to remove the invoice on the accounting side</i></b></span>", {
			buttons: {'Yes': true, 'No': false},
			submit: function(v, m, f) {
				if(v) {					
					
					$.ajax({
     					url: "ajax.php?cmd=mrr_kill_sicap_invoice_timesheet",
     					type: "post",
     					dataType: "xml",
     					data: {
     						//POST variables needed for "page" to load for XML output
     						"id": id
     					},
     					error: function() {
     						alert('general error removing invoice linkage.');
     					},
     					success: function(xml) {
     						$.noticeAdd({text: "Time Sheet Invoice has been unlinked.  Please remove it from Accounting side."});
							list_switch_shuttle_rates();
     		    			}
     				});	
										
				}
			}
		});	
	}
	
	function mrr_load_timesheet_entry_section(id)
	{	//load timesheet_entries_form section...
		$('#timesheet_entries_form').html('');
		$('#time_sheet_entries_cnt').val('0');
				
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=form_timesheets_entries",
			   data: {
			   		"timesheet":id		   		
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {				     				
			   		if($(xml).find('rslt').text() == "1")
     				{					
     					mrrtab=$(xml).find('Disp').text();
     					$('#timesheet_entries_form').html(mrrtab);
     					
     					ts_cnt=parseInt($(xml).find('entries').text());
     					$('#time_sheet_entries_cnt').val(ts_cnt);
     					     					  					
     					$('.date_picker_rates').datepicker();
     				}		
			   }
		});	
		
	}		
	
	function select_cust_timesheets(id)
	{
		timesheet_id=id;	
		$('#timesheet_id').val(id);
		list_switch_shuttle_rates();
		
		if(id==0)
		{
			$('#mrr_line_items').hide();
		}
		else
		{
			$('#mrr_line_items').show();
			
			mrr_load_timesheet_entry_section(id);            

            mrr_get_shuttle_mileage_fuel_rate(id);
            mrr_calc_shuttle_mileage_fuel_rate_amnt(id);
		}		
	}
	
	function list_cust_timesheets()
	{
		customer_id=parseInt($('#customer_id').val());
		dmode=parseInt($('#display_mode').val());
		
		if(parseInt(customer_id)==0)	
		{
			$('#driver_id').val(0);	
			$('#truck_id').val(0);	
			$('#trailer_id').val(0);	
			
			//$('#location_id').val(0);
			
			$('#timesheet_holder').html('');	
			return;	
		}		
		//timesheet_id
		
		$('#timesheet_holder').html('Loading...');	
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=list_cust_timesheets",
			   data: {
			   		"cust_id":customer_id,
			   		"mode":dmode			   		
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {				     				
			   		if($(xml).find('rslt').text() == "1")
     				{					
     					mrrtab=$(xml).find('Disp').text();
     					$('#timesheet_holder').html(mrrtab);
     				}		
			   }
		});	
	}
	function add_cust_timesheets()
	{		
		customer_id=parseInt($('#customer_id').val());
		if(customer_id==0)		$.prompt("<span class='alert'>Error:</span> Please select a customer to enter time sheet(s).");	
		
		/*
		"load_id":$('#timesheet_load_id').val(),
		"disp_id":$('#timesheet_disp_id').val(),
		*/
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=add_cust_timesheets",
			   data: {
			   		"id":0,
			   		"customer_id":customer_id,
			   		"start_date":$('#timesheet_start_date').val(),
			   		"end_date":$('#timesheet_end_date').val(),
			   		"load_id":0,
			   		"disp_id":0,
			   		"runs": parseInt($('#shuttle_runs').val()),		
			   		"location_id":$('#location_id').val()	   					   		
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {				     				
			   		if($(xml).find('rslt').text() == "1")
     				{					
     					list_cust_timesheets();
     					
     					$('#timesheet_start_date').val('');
			   			$('#timesheet_end_date').val('');
			   			$('#shuttle_runs').val('0');
			   			$('#location_id').val('0');
     				}
					else
					{
						$.prompt("<span class='alert'>Error:</span> Cannot add the Time Sheet holder.");	
					}	
			   }
		});
		
	}
	function kill_cust_timesheets(id)
	{
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=kill_cust_timesheets",
			   data: {
			   		"id":id			   		
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {				     				
			   		if($(xml).find('rslt').text() == "1")
     				{					
     					list_cust_timesheets();
     					select_cust_timesheets(0);
     				}		
			   }
		});
	}
	
	function  mrr_change_driver_truck(id)
	{
		var driverid=parseInt($('#driver_'+id+'_id').val());
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=find_truck_for_driver",
			   data: {
			   		"id":driverid,
			   		"mrr_mode":id		   		
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {				     				
			   		if($(xml).find('rslt').text() == "1")
     				{					
     					fval=$(xml).find('TruckID').text();
     					$('#truck_'+id+'_id').val(fval);
     				}		
			   }
		});	
	}
	function mrr_change_route_rate(id)
	{
		$('#switch_'+id+'_shuttle_rate').val('0.00');
		var optid=parseInt($('#switch_'+id+'_shuttle_route_id').val());
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=find_switch_shuttle_rates",
			   data: {
			   		"id":optid,
			   		"mrr_mode":id				   		
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {				     				
			   		if($(xml).find('rslt').text() == "1")
     				{					
     					fval=$(xml).find('Disp').text();
     					$('#switch_'+id+'_shuttle_rate').val(fval);
     				}		
			   }
		});		
	}
	
	
	function kill_switch_shuttle_rates(id)
	{
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=kill_switch_shuttle_rates",
			   data: {
			   		"id":id			   		
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {				     				
			   		if($(xml).find('rslt').text() == "1")
     				{					
     					list_switch_shuttle_rates();
     				}		
			   }
		});
	}
	
	function add_switch_shuttle_rates()
	{			
		customer_id=parseInt($('#customer_id').val());
		if(customer_id==0)
		{
			$.prompt("<span class='alert'>Error:</span> Please select a custoemr to add time sheet entries.");	
			return;
		}
		timesheet_id=parseInt($('#timesheet_id').val());
		if(timesheet_id==0)
		{
			$.prompt("<span class='alert'>Error:</span> Please select a time sheet to add an entry.");	
			return;
		}
		
		
		entries=parseInt($('#time_sheet_entries_cnt').val());
		var i=0;
		for(i=0; i < entries; i++)
		{
     		dater=$('#switch_'+ i +'_shuttle_date').val();	
     		conard1=$('#conard_'+ i +'_shuttle_time1').val();	
     		timer1=$('#switch_'+ i +'_shuttle_time1').val();	
     		conard2=$('#conard_'+ i +'_shuttle_time2').val();
     		timer2=$('#switch_'+ i +'_shuttle_time2').val();	
     		router=$('#switch_'+ i +'_shuttle_route_id').val();	
     		rater=$('#switch_'+ i +'_shuttle_rate').val();	
     		otr=$('#conard_'+ i +'_days_run').val();
     		miles=parseInt($('#conard_'+ i +'_shuttle_miles').val());
     		dhmiles=parseInt($('#conard_'+ i +'_shuttle_deadhead').val());
     		lunch=$('#conard_'+ i +'_lunch_break').val();
     		driver=$('#driver_'+ i +'_id').val();	
     		truck=$('#truck_'+ i +'_id').val();	
     		trailer=$('#trailer_'+ i +'_id').val();
     		sundays=$('#switch_'+ i +'_wkday').val();
     		local=0;	//$('#location_'+ i +'_id').val();	
     		
     		if(parseInt(router)==0)
     		{
     			//$.prompt("<span class='alert'>Error:</span> Please select the route or switch only option.");	
     			//return;
     		}
     		
     		if(conard1!="00:00" || conard2!="00:00" || (parseInt(router) > 0 && parseInt(router)!=145) )
     		{
          		$.ajax({
          			   type: "POST",
          			   url: "ajax.php?cmd=add_switch_shuttle_rates",
          			   data: {
          			   		"id":timesheet_id,
          			   		"date":dater,	
          					"conard1":conard1,
          					"time1":timer1,
          					"conard2":conard2,
          					"time2":timer2,
          					"route_id":router,
          					"rate":rater,	
          					"lunch":lunch,
          					"otr":otr,
          					"miles":miles,
          			   		"dhmiles":dhmiles,
          			   		"driver_id":driver,
          			   		"truck_id":truck,
          			   		"trailer_id":trailer,
          			   		"location_id":local,
                            "sundays": sundays,
          			   		"cust_id":customer_id		   		
          			   		},
          			   dataType: "xml",
          			   cache:false,
          			   async:false,
          			   success: function(xml) {				
               				if($(xml).find('rslt').text() == "1")
               				{				
               					$.noticeAdd({text: "Added Switch/Shuttle info to Time Sheet."});
               					
               					$('#switch_'+ i +'_shuttle_date').val('<?= date("m/d/Y") ?>');	
               					$('#switch_'+ i +'_shuttle_time1').val('00:00');	
               					$('#switch_'+ i +'_shuttle_time2').val('00:00');	
               					$('#switch_'+ i +'_shuttle_route_id').val('145');		//145 is the None - Switch only option....
               					$('#switch_'+ i +'_shuttle_rate').val('0.00');	
               					
               					$('#conard_'+ i +'_shuttle_time1').val('00:00');
               					$('#conard_'+ i +'_shuttle_time2').val('00:00');
               					$('#conard_'+ i +'_days_run').val('0.00');
               					$('#conard_'+ i +'_lunch_break').val('0.00');
               					$('#conard_'+ i +'_shuttle_miles').val('0');
               					$('#conard_'+ i +'_shuttle_deadhead').val('0');
               					
               					$('#driver_'+ i +'_id').val('0');	
               					$('#truck_'+ i +'_id').val('0');	
               					$('#trailer_'+ i +'_id').val('0');	
               					//$('#location_'+ i +'_id').val('0');
               					
               					if(i+1 == entries)		list_switch_shuttle_rates();
               				}
               				else
               				{
               					$.prompt("<span class='alert'>Error:</span> Please make sure you have entered all the information");	
               				}				
          			   }
          		});
     		}
     		if(i+1 == entries)		list_switch_shuttle_rates();
		}
	}
	function list_switch_shuttle_rates()
	{
		//if(parseInt(dispatch_id)==0)			return;
		//timesheet_id
		
		timesheet_id=parseInt($('#timesheet_id').val());
		
		if(timesheet_id==0)
		{
			$('#switch_shuttle_rates_holder').html('Please select a time sheet from the left to enter times.');	
			return;
		}
		
		
		$('#switch_shuttle_rates_holder').html('Loading...');	
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=list_switch_shuttle_rates",
			   data: {
			   		"id":timesheet_id			   		
			   		},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {				     				
			   		if($(xml).find('rslt').text() == "1")
     				{					
     					mrrtab=$(xml).find('Disp').text();
     					$('#switch_shuttle_rates_holder').html(mrrtab);
     				}
		
			   }
		});
	}

</script>
