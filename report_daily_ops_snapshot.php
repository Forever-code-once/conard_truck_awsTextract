<?
$usetitle =  "Report - Daily Operations Snapshot";
$use_title = "Report - Daily Operations Snapshot";
?>
<? include('header.php') ?>
<?
$sent_daily_ops_snapshot=0;
//$sent_daily_ops_snapshot=1;

$pdf="";

$weekday="".date("l",time())."";
//$weekday="Friday";

$weekday_adder="";
$mrr_date_to="".date("Y-m-d", time())."";
$weekend1="".date("Y-m-d", time())."";
$weekend2="".date("Y-m-d", time())."";
if($weekday=="Friday")	
{
	$mrr_date_to="".date("Y-m-d", strtotime("+2 day",time()))."";
	$weekday_adder=" - Sunday";
	$weekend1="".date("Y-m-d", strtotime("+1 day",time()))."";
	$weekend2="".date("Y-m-d", strtotime("+2 day",time()))."";
}

$counter=0;
$total_miles =0;
$total_deadhead =0;					
$total_miles_hr =0;
$total_deadhead_hr =0;
$fuel_charge =0;

$mrr_inv_diff_tot =0;
$mrr_base_rate_tot =0;
$mrr_running_tot =0;

$total_profit =0;
$total_cost =0;
$total_sales =0;

$loads_pushed_off=mrr_conard_get_loads_pushed_off();
$loads_pushed_off=0;
$loads_pushed="";

//Sales Report...by the load...
$sql = "
	select load_handler.*,				
		customers.name_company,
		load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount - load_handler.actual_total_cost as load_profit,
		
		(select trucks_log.linedate_pickup_eta from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0 order by trucks_log.id desc limit 1) as mrr_deferred,
		
		(select expense_amount from load_handler_actual_var_exp where load_handler_id = load_handler.id and expense_type_id='25') as mrr_base_rate,
		(select ifnull(sum(trucks_log.cost),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as mrr_cost,
		(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
		(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead,
		(select ifnull(sum(trucks_log.loaded_miles_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_hr,
		(select ifnull(sum(trucks_log.miles_deadhead_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead_hr
			
	from load_handler
		left join customers on customers.id = load_handler.customer_id
	where load_handler.deleted = 0
		and customers.deleted = 0
		and load_handler.linedate_pickup_eta >= '".date("Y-m-d", time())." 00:00:00'
		and load_handler.linedate_pickup_eta <= '".$mrr_date_to." 23:59:59'
	order by load_handler.id
";	//load_handler.linedate_pickup_eta asc,
$data = simple_query($sql);
while($row = mysqli_fetch_array($data)) 
{
	$counter++;	

	$flat_rate_fuel_charge=$row['flat_fuel_rate_amount'];

	if($last_load_id != $row['id'])
	{
		if(!isset($row['mrr_deferred']) || (isset($row['mrr_deferred']) && date("Y-m-d",strtotime($row['mrr_deferred'])) != date("Y-m-d",strtotime($row['linedate_pickup_eta'])) ) )
		{
			$loads_pushed_off++;
			$loads_pushed.="Load ".$row['id']." Pick ETA ".date("Y-m-d",strtotime($row['linedate_pickup_eta']))." Dispatched ".date("Y-m-d",strtotime($row['mrr_deferred']))."<br>";
		}		
		
		//$load_miles = $row['miles'];
		//$load_miles_deadhead = $row['miles_deadhead'];
				
		//$truck_miles_hr = $row['miles_hr'];
		//$truck_deadhead_hr = $row['miles_deadhead_hr'];

		//$total_miles += $load_miles;
		//$total_deadhead += $load_miles_deadhead;	
				
		//$total_miles_hr += $truck_miles_hr;
		//$total_deadhead_hr += $truck_deadhead_hr;

		$fuel_charge += ($row['miles'] + $row['miles_deadhead'] + $row['miles_hr'] + $row['miles_deadhead_hr']) * $row['actual_rate_fuel_surcharge'] / $defaultsarray['average_mpg'];


		$mrr_inv_amnt=$row['sicap_invoice_amount'];
		$sales_differ=($row['actual_bill_customer'] + $flat_rate_fuel_charge) - $mrr_inv_amnt;
				
		$mrr_inv_diff_tot+=$sales_differ;

		$mrr_base_rate_tot+=$row['mrr_base_rate'];
                 
                $mrr_running_tot+=($row['actual_bill_customer'] + $flat_rate_fuel_charge);

		$total_profit += $row['actual_bill_customer'] + $flat_rate_fuel_charge - $row['actual_total_cost'];
		$total_cost += $row['actual_total_cost'];
		$total_sales += $row['actual_bill_customer'] + $flat_rate_fuel_charge;
	}

	$last_load_id = $row['id'];
}


//Now get the Dispatch Histry Report to get the mileage.   =====================================================================
$sqld = "
	select trucks_log.miles,
		trucks_log.miles_deadhead,
		trucks_log.loaded_miles_hourly,
		trucks_log.miles_deadhead_hourly,
		load_handler.id as load_handler_id
	
	from load_handler
		left join trucks_log on load_handler.id = trucks_log.load_handler_id and trucks_log.deleted = 0
		
	where load_handler.deleted = 0
		and trucks_log.linedate_pickup_eta >= '".date("Y-m-d", time())." 00:00:00'
		and trucks_log.linedate_pickup_eta<= '".$mrr_date_to." 23:59:59'			
				
	order by trucks_log.linedate_pickup_eta
";
$datad = simple_query($sqld);
while($rowd = mysqli_fetch_array($datad)) 
{
	$load_miles = $rowd['miles'];
	$load_miles_deadhead = $rowd['miles_deadhead'];
				
	$truck_miles_hr = $rowd['loaded_miles_hourly'];
	$truck_deadhead_hr = $rowd['miles_deadhead_hourly'];


	$total_miles += $load_miles;
	$total_deadhead += $load_miles_deadhead;	
				
	$total_miles_hr += $truck_miles_hr;
	$total_deadhead_hr += $truck_deadhead_hr;
}


//Now get the date(s) surcharge.    ============================================================================================

$date1=date("m/d",strtotime("-2 day",time()));
$date2=date("m/d",strtotime("-1 day",time()));
$date3=date("m/d",time());
$date4=date("m/d",strtotime("+1 day",time()));

$dayer1=date("D",strtotime("-2 day",time()));
$dayer2=date("D",strtotime("-1 day",time()));
$dayer3=date("D",time());
$dayer4=date("D",strtotime("+1 day",time()));

$sur1=mrr_get_fuel_surcharge_by_today($date1);
$sur2=mrr_get_fuel_surcharge_by_today($date2);
$sur3=mrr_get_fuel_surcharge_by_today($date3);
$sur4=mrr_get_fuel_surcharge_by_today($date4);

$use_cur_charge=$sur3;

if($weekday=="Friday")
{
	$date1=date("m/d",strtotime("-1 day",time()));
	$date2=date("m/d",time());
	$date3=date("m/d",strtotime($weekend1));
	$date4=date("m/d",strtotime($weekend2));
	
	$dayer1=date("D",strtotime("-1 day",time()));
	$dayer2=date("D",time());
	$dayer3=date("D",strtotime($weekend1));
	$dayer4=date("D",strtotime($weekend2));
	
	$sur1=mrr_get_fuel_surcharge_by_today($date1);
	$sur2=mrr_get_fuel_surcharge_by_today($date2);
	$sur3=mrr_get_fuel_surcharge_by_today($date3);
	$sur4=mrr_get_fuel_surcharge_by_today($date4);

	$use_cur_charge=$sur1;
}

$offset_a=$sur2 - $sur1;
$offset_b=$sur3 - $sur2;
$offset_c=$sur4 - $sur3;

$fprices="";
$fprices.="".$dayer1." ".$date1." $".number_format($sur1,2)." &nbsp; &nbsp;";
$fprices.=" <b>(".($offset_a >= 0 ? "+" :"")."".$offset_a.")</b>  &nbsp; &nbsp;";
$fprices.="".$dayer2." ".$date2." $".number_format($sur2,2)." &nbsp; &nbsp;";
$fprices.=" <b>(".($offset_b >= 0 ? "+" :"")."".$offset_b.")</b>  &nbsp; &nbsp;";
$fprices.="".$dayer3." ".$date3." $".number_format($sur3,2)." &nbsp; &nbsp;";
$fprices.=" <b>(".($offset_c >= 0 ? "+" :"")."".$offset_c.")</b>  &nbsp; &nbsp;";
$fprices.="".$dayer4." ".$date4." $".number_format($sur4,2)."";

$fuel_charge_calc=$fuel_charge;
$grand_tot_miles=($total_miles+ $total_deadhead + $total_miles_hr + $total_deadhead_hr);
$mpg=round(floatval(trim($defaultsarray['average_mpg'])));
$fuel_charge_calc=($grand_tot_miles / $mpg * $use_cur_charge);


//$pdf.="<center><span class='section_heading'><b>".$use_title."</b></span></center>";
$pdf.="<table cellspacing='2' cellpadding='2' width='850' border='0' style='background-color:#FFFFFF;text-align:left;'>";	// margin:0 10px; class='admin_menu2 font_display_section tablesorter'
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'><span class='section_heading'><b>".$use_title."</b></span></td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Date:</b></td>";
$pdf.=	"<td valign='top'>".date("m/d/Y",time())." (".$weekday."".$weekday_adder.")</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Sales:</b></td>";
$pdf.=	"<td valign='top'>$".number_format($total_sales,2)."</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Fuel Price with Discount:</b></td>";
$pdf.=	"<td valign='top'>".$fprices."</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Estimated Fuel Cost:</b><br>(miles ran / 6 x discounted average)</td>";	//$".number_format($fuel_charge,2)."
$pdf.=	"<td valign='top'>(".$grand_tot_miles." / ".$mpg." * ".$use_cur_charge.") = $".number_format($fuel_charge_calc,2)."</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Trucks sitting:</b></td>";
$pdf.=	"<td valign='top'>".mrr_conard_get_no_truck_movement(0)."</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Drivers Off:</b></td>";
$pdf.=	"<td valign='top'>".mrr_conard_get_drivers_timeoff()."</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Dispatch miles:</b></td>";
$pdf.=	"<td valign='top'>
		<b>".number_format($total_miles,0)."</b> Miles + 
		<b>".number_format($total_deadhead,0)."</b> DH Miles + 
		<b>".number_format($total_miles_hr,0)."</b> Hourly Miles + 
		<b>".number_format($total_deadhead_hr,0)."</b> DH Hourly Miles = 
		<b>".number_format($grand_tot_miles,0)."</b> Total Miles.
		</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
/*
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Trucks left uncovered:</b></td>";
$pdf.=	"<td valign='top'>".mrr_conard_get_no_truck_movement(1)."</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Loads pushed off:</b></td>";
$pdf.=	"<td valign='top'>".$loads_pushed_off."<br>".$loads_pushed."</td>";		//
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top'><b>Notes:</b></td>";
$pdf.=	"<td valign='top'></td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
$pdf.="<tr>";
$pdf.=	"<td valign='top' colspan='2' align='center'>&nbsp; &nbsp;</td>";
$pdf.="</tr>";
*/
$pdf.="</table>";

//$pdf = ob_get_contents();
//ob_end_clean();
	
echo $pdf;

$last_run=trim($defaultsarray['last_daily_ops_snapshot']);	
$comp_date="".date('Y-m-d',time())." 16:30:00";

$last_valid=1;
if($last_run > $comp_date)		$last_valid=0;  	//the last run is greater than today at 4:30 PM
		
if(isset($_GET['auto_run']) || $last_valid==0)
{	//only send the email on the auto_run time... or if it hasn't been run yet.
	$email_to=$defaultsarray['email_daily_ops_snapshot'];   
	$email_to2=$defaultsarray['special_email_monitor'];    	

	$user_name=$defaultsarray['company_name'];
     	$From=$defaultsarray['company_email_address'];
     	$Subject="";
     	if(isset($use_title))			$Subject=$use_title;
     	elseif(isset($usetitle))		$Subject=$usetitle;
     		
     	//$pdf=str_replace(" href="," name=",$pdf);
     			
     	$sentit=mrr_trucking_sendMail($email_to,'Daily Ops Manager',$From,$user_name,'','',$Subject,$pdf,$pdf);
	$sentit=mrr_trucking_sendMail($email_to2,'Daily Ops Manager',$From,$user_name,'','',"COPY: ".$Subject,$pdf,$pdf);
     		
     	$sent_msg="not been sent";		if($sentit==1)	$sent_msg="been sent";	
     	echo "<br><br><b>This report has ".$sent_msg." to 'Daily Ops Manager' at E-Mail address '".$email_to."'.</b><br><br>";
     		
	//$sentit=mrr_trucking_sendMail('dconard@conardlogistics.com',"Dale Conard",$From,$user_name,'','',$Subject,$pdf,$pdf);


	$sqlu="
		update defaults set 
			xvalue_string='".date('Y-m-d',time())." 16:30:00' 
		where xname='last_daily_ops_snapshot'
	";
	simple_query($sqlu);
}


function mrr_conard_get_loads_pushed_off()
{
	$loads_pushed_off=0;

	return $loads_pushed_off;
}


function mrr_conard_get_no_truck_movement($mode=0)
{		
	global $mrr_date_to;
	$res="";
	$cntr=0;
	$sql="
		select * 
		from mrr_not_truck_movement
		where linedate>='".date("Y-m-d",time())."' and linedate<='".$mrr_date_to."' and truck_mode='".(int) $mode."'
		order by linedate asc, truck_name asc
	";
	$data = simple_query($sql);
        while($row = mysqli_fetch_array($data)) 
	{
		if($cntr > 0)   $res.=", &nbsp; &nbsp; &nbsp;";
		
		$res.="<b>".$row['truck_name']."</b> -- ".$row['maint_marks']."";
		$cntr++;
	}
	return $res;
}

function mrr_get_fuel_surcharge_by_today($date)
{         
	global $defaultsarray; 

	$pre_surcharge=0;
	$surcharge=0;

	//get the previous day just in case...
	$sql="
        	select avg_rate
                from mrr_avg_state_values
                where linedate='".date("Y-m-d", strtotime("-1 day",strtotime($date)))."' and store_state='AVG'
                order by id desc
                limit 1   		
        ";
        $data = simple_query($sql);
        if($row = mysqli_fetch_array($data))
        {
        	$pre_surcharge=$row['avg_rate'];
		$surcharge=$row['avg_rate'];
        } 
	else
	{
		$sql="
        		select avg_rate
       	         	from mrr_avg_state_values
                	where linedate='".date("Y-m-d", strtotime("-2 day",strtotime($date)))."' and store_state='AVG'
                	order by id desc
                	limit 1   		
        	";
        	$data = simple_query($sql);
        	if($row = mysqli_fetch_array($data))
        	{
        		$pre_surcharge=$row['avg_rate'];
			$surcharge=$row['avg_rate'];
	        } 
	}

	$sql="
        	select avg_rate
                from mrr_avg_state_values
                where linedate='".date("Y-m-d", strtotime($date))."' and store_state='AVG'
                order by id desc
                limit 1   		
        ";
        $data = simple_query($sql);
        if($row = mysqli_fetch_array($data))
        {
        	$surcharge=$row['avg_rate'];
        } 
	elseif($pre_surcharge==0)
	{	//use the older rates frorm the system if there is not anything ion the new table.
		$sql="
    			select fuel_surcharge
     			from log_fuel_updates
     			where linedate_added>='".date("Y-m-d", strtotime($date))." 00:00:00'
     				and linedate_added<='".date("Y-m-d", strtotime($date))." 23:59:59'
     			order by id desc
     			limit 1   		
     		";	
     		$data = simple_query($sql);
        	if($row = mysqli_fetch_array($data)) 
        	{
        	 	$surcharge=$row['fuel_surcharge'];
        	}
        	else
        	{	//still nothing, so use the default setting...
         	      	$surcharge=$defaultsarray['fuel_surcharge'];
         	      	
         	      	//check for last range just in case
         	      	$sql="
         	      		select fuel_surcharge
           	         	from log_fuel_updates
           	         	where linedate_added<='".date("Y-m-d", strtotime($date))." 23:59:59'
                    		order by id desc
                    		limit 1   		
               		";
               		$data = simple_query($sql);
               		if($row = mysqli_fetch_array($data))
               		{
                    		$surcharge=$row['fuel_surcharge'];
               		}    
        	} 	
	}
	return $surcharge;   		
}

function mrr_conard_get_drivers_timeoff()
{
	global $mrr_date_to;
	$res="";
		
	$sql2 = "
		select linedate_start as linedate,
			linedate_end as linedate_to,
			driver_id as driver,
			name_driver_first as name_driver_first,
			name_driver_last as name_driver_last,
			concat('Unavailable: <a href=\"admin_drivers.php?id=',drivers.id,'\" target=\"view_driver_',drivers.id,'\">', name_driver_first, ' ', name_driver_last,'</a> ',date_format(linedate_start, '%b %e'), ' - ', date_format(linedate_end, '%b %e'),': ',reason_unavailable) as c_reason,
			reason_unavailable as c_reason2,
			1 as from_calendar,
			drivers_unavailable.id as calendar_id,
			'none' as desc_long
				
		from drivers, drivers_unavailable
		where drivers.deleted <= 0
			and drivers.active > 0
			and drivers_unavailable.deleted <= 0
			and drivers.id = drivers_unavailable.driver_id
			and drivers_unavailable.linedate_start <= '".date("Y-m-d",time())."'
			and drivers_unavailable.linedate_end >= '".$mrr_date_to."'
			
		union 

            	select
              		linedate AS linedate,
              		linedate AS linedate_to,
              		driver_id AS user,
			name_driver_first as name_driver_first,
			name_driver_last as name_driver_last,
              		CONCAT('Driver: <a href=\"admin_drivers.php?id=',driver_id,'\" target=\"view_driver_', driver_id,'\">',name_driver_first,' ',name_driver_last,'</a>: ',driver_reason) AS c_reason,
              		driver_reason AS c_reason2,
              		0 AS from_calendar,
              		driver_absenses.id AS calendar_id,
              		'calendar' AS desc_long
            	from drivers, driver_absenses
            	where drivers.deleted <= 0
              		and driver_absenses.deleted <= 0
              		and drivers.id = driver_absenses.driver_id
              		and driver_absenses.linedate >= '".date("Y-m-d", time())." 00:00:00'
			and driver_absenses.linedate <= '".$mrr_date_to." 23:59:59'		
						
		union
						
		select linedate,
			'0000-00-00 00:00:00',
			0,
			desc_short,
			'',
			desc_short,
			'',
			0 as from_calendar,				
			id,
			desc_long
			
		from calendar
		where deleted <= 0
			and linedate >= '".date("Y-m-d", time())." 00:00:00'
			and linedate <= '".$mrr_date_to." 23:59:59'
				
		order by linedate asc
	";	
	$data2 = simple_query($sql2);
	
	$cntr=0;
		
	//add new vacation and cash advances to this section.....................Addd April 2014.......		
	$sqlv = "		
		select driver_vacation_advances.*,
     			drivers.name_driver_first,
			drivers.name_driver_last     			
     					
     		from driver_vacation_advances
     			left join drivers on drivers.id = driver_vacation_advances.driver_id
     		where driver_vacation_advances.deleted <= 0
     			and driver_vacation_advances.approved_by_id >0
     			and driver_vacation_advances.cancelled_by_id <=0
     			and driver_vacation_advances.cash_advance =0
     			and drivers.deleted <= 0
     			and (
				driver_vacation_advances.linedate_end >= '".date("Y-m-d",time())." 00:00:00'   
				".( $mrr_date_to != date("Y-m-d",time()) ? "or driver_vacation_advances.linedate_end >= '".$mrr_date_to." 23:59:59' " : "")."  		
     			)
     		order by driver_vacation_advances.linedate_start asc, driver_vacation_advances.linedate_end asc
	";	
	$datav = simple_query($sqlv);
	while($rowv = mysqli_fetch_array($datav)) 
	{				
		
		/*
		$driver="<a href='admin_drivers.php?id=".$rowv['driver_id']."' target='_blank'>".trim($rowv['name_driver_first']." ".$rowv['name_driver_last'])."</a>";
		
		$vaca_label="Vacation";	
		$ranger="".date("M, j", strtotime($rowv['linedate_start']))." - ".date("M, j", strtotime($rowv['linedate_end']))."";
		if($rowv['cash_advance'] > 0)
		{
			$vaca_label="Advance";	
			$ranger="".date("M, j Y", strtotime($rowv['linedate_start']))."";
		}	
												
		$res.=	"<li class='mrr_time_off_drivers'>";
		$res.=		"<h3><span>".$vaca_label."</span>";
		$res.=			"<span style='margin-left:120px;' onclick='window.open(\"drivers_vacation_advances.php?driver_id=".$rowv['driver_id']."&use_id=0\");'>
									<img src='".$new_style_path."blue_icon1.png' alt='add'>
					</span>";	
		$res.=			"<a href='drivers_vacation_advances.php?driver_id=".$rowv['driver_id']."&use_id=".$rowv['id']."' target='_blank'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";	
		$res.=		"</h3>";
		$res.=		"<p><b>".$driver." ".$ranger."</b><br>".trim($rowv['comments'])."</p> ";		
		$res.=	"</li>";
		*/
		
		$ranger="".date("M j", strtotime($rowv['linedate_start']))." - ".date("M j", strtotime($rowv['linedate_end']))."";

		if($cntr > 0)		$res.="<br>";
		$res.="".$ranger." <b>".trim($rowv['name_driver_first']." ".$rowv['name_driver_last'])."</b>";
		$cntr++;
	}
	//.............................................................................................		
		
		
	//build time off ...add editing to Unavailable Driver on load board only.		
	while($row2 = mysqli_fetch_array($data2)) 
	{			
		/*
		$tmp_reason=trim($row2['c_reason2']);
		$tmp_reason=str_replace("'","",$tmp_reason);	//&apos;
		$tmp_reason=str_replace('"',"",$tmp_reason);	//&quot;
				
		$tmp_from=date("m/d/Y",strtotime($row2['linedate']));
		$tmp_to=date("m/d/Y",strtotime($row2['linedate_to']));
				
		$row2['c_reason']=str_replace("Unavailable: ","",$row2['c_reason']);
				
		$use_java="delete_from_calendar($row2[calendar_id]);";
		if($row2['from_calendar']==1)
		{
			$use_java="delete_driver_unavailable($row2[calendar_id]);";	
		}
		elseif($row2['from_calendar']==2)
		{
			//$row2['c_reason']="<b>".$row2['c_reason'].":</b> ".trim($row2['desc_long']);
		}
		else
		{
			$row2['c_reason']="<b>".$row2['c_reason'].":</b> ".trim($row2['desc_long']);
		}
							
		if($row2['from_calendar']!=2)
		{
			$res.=	"<li class='mrr_time_off_".( trim($row2['desc_long'])=="staff" ? "users" : "drivers") ."'>";
			$res.=		"<h3><span>".date("M, j", strtotime($row2['linedate']))."</span>";
			$res.=			"<span onClick='mrr_edit_driver_unavailable(".$row2['calendar_id'].",".$row2['driver'].",\"".$tmp_from."\",\"".$tmp_to."\",\"".$tmp_reason."\");' style='margin-left:120px;'>
							<img src='".$new_style_path."blue_icon1.png' alt='add'>
						</span>";	
			$res.=			"<a href='javascript:".$use_java."'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";	
			$res.=		"</h3>";
			$res.=		"<p>".trim($row2['c_reason'])."</p> ";		
			$res.=	"</li>";
		}
		else
		{
			$res.=	"<li class='mrr_time_off_".( trim($row2['desc_long'])=="staff" ? "users" : "drivers") ."'>";
			$res.=		"<h3><span>".date("M, j", strtotime($row2['linedate']))."</span>";
			
			$res.=		"</h3>";
			$res.=		"<p>".trim($row2['c_reason'])."</p> ";		
			$res.=	"</li>";
		}
		*/

		if($cntr > 0)		$res.="<br>";
		$res.="".date("M j", strtotime($row2['linedate']))." <b>".trim($row2['name_driver_first']." ".$row2['name_driver_last'])."</b>";
		$cntr++;
	}
		
	return $res;
} 	
?>	
<script type='text/javascript'>
	//$('#date_from').datepicker();
	//$('#date_to').datepicker();
	//$('.tablesorter').tablesorter();
	
	function mrr_sent_daily_ops_snapshot()
	{
		$.ajax({
			url: "report_daily_ops_snapshot.php?auto_run=1",
			type: "post",
			dataType: "html",
			data: {
				'auto_run':1
			},
			success: function(xml) {
				
			}						
		});
	}
	$().ready(function() 
	{
		<? if($sent_daily_ops_snapshot > 0) { ?>
			mrr_sent_daily_ops_snapshot();
		<? } ?>
	});	
</script>
<? include('footer.php') ?>