<? include('header.php') ?>
<?
	if(!isset($_POST['customer_id'])) 		$_POST['customer_id'] = 0;
	if(!isset($_POST['trailer_id'])) 		$_POST['trailer_id'] = 0;
	if(!isset($_POST['skip_days']))		$_POST['skip_days']=0;
	if(!isset($_POST['date_from'])) 		$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) 		$_POST['date_to'] = date("n/j/Y", time());
	
	if(!isset($_POST['filter_city']))		$_POST['filter_city']="";
	if(!isset($_POST['filter_state']))		$_POST['filter_state']="";
	if(!isset($_POST['filter_zip']))		$_POST['filter_zip']="";

	$data_customers = get_customers();
	$data_trailers = get_trailers();
	
	$sql="update trailers_dropped set invoice_pending=0 where invoice_pending>0";
	$data= simple_query($sql);
?>
<form action='' method='post'>
<input type='hidden' name='build_report' value='1'>
<div style='width:1400px;'>
     <div style='margin:20px;padding:20px; float:right; width:600px; background-color:#FFFFFF; border:1px solid #00CC00;'>
     	This report finds all trailers (or the selected trailer) dropped within the date range (with the other filters as well).
     	<br><br>
     	<ul>
     		<li>* Trailer was dropped and completed within the date range.</li>
     		<li>* Trailer was dropped before the date range, but completed within the date range.</li>	
     		<li>* Trailer was dropped in date range, and completed later...or still opened.</li>	
     		<li>* Trailer was dropped before the date range, and not completed until after the date range...or still opened.</li>	
     	</ul>
     	<br>
     	<b>DAYS are calculated only from within the date range...</b>regardless of when it was dropped or when it was completed if either is outside of the filtered date range.
     	<br>
     	Ex: If the filtered range is Feb., days reflected is only the days during the month/period filtered.
     </div>
     <table class='admin_menu1 font_display_section' style='margin:10px;text-align:left; width:600px;'>
     <tr>
     	<td>Customer</td>
     	<td>
     		<select name='customer_id' id='customer_id'>
     			<option value='0'>All Customers</option>
     			<?
     			while($row_customer = mysqli_fetch_array($data_customers)) 
     			{ 
     				echo "<option value='$row_customer[id]' ".($row_customer['id'] == $_POST['customer_id'] ? 'selected' : '').">$row_customer[name_company]</option>";
     			}
     			?>
     		</select>
     	</td>
     </tr>
     <tr>
     	<td>Trailer</td>
     	<td>
     		<select name='trailer_id' id='trailer_id'>
     			<option value='0'>All Trailers</option>
     			<?
     			while($row_trailer = mysqli_fetch_array($data_trailers)) 
     			{ 
     				echo "<option value='$row_trailer[id]' ".($row_trailer['id'] == $_POST['trailer_id'] ? 'selected' : '').">".(!$row_trailer['active'] ? '(inactive) ' : '')."$row_trailer[trailer_name]</option>";
     			}
     			?>
     		</select>
     	</td>
     </tr>
     <tr>
     	<td>Date From</td>
     	<td><input class='date_picker' name='date_from' id='date_from' value='<?=$_POST['date_from']?>'></td>
     </tr>
     <tr>
     	<td>Date To</td>
     	<td><input class='date_picker' name='date_to' id='date_to' value='<?=$_POST['date_to']?>'></td>
     </tr>
     <tr>
     	<td>Free Days < = </td>
     	<td><input type='text' name='skip_days' id='skip_days' value='<?=$_POST['skip_days']?>' style='width:75px;'></td>
     </tr>
     <tr>
     	<td>City</td>
     	<td><input class='long' name='filter_city' id='filter_city' value='<?=$_POST['filter_city']?>'></td>
     </tr>
     <tr>
     	<td>State</td>
     	<td><input class='long' name='filter_state' id='filter_state' value='<?=$_POST['filter_state']?>'></td>
     </tr>
     <tr>
     	<td>Zip</td>
     	<td><input class='long' name='filter_zip' id='filter_zip' value='<?=$_POST['filter_zip']?>'></td>
     </tr>
     <tr>
     	<td></td>
     	<td><input type='submit' value='Submit' name='build_report'></td>
     </tr>
     </table>
     </form>
</div>

<h2>&nbsp;&nbsp;Dropped Trailer Billing Report</h2>&nbsp;<br>
<? 
if(isset($_POST['build_report'])) 
{ 
	$date_filter="";
	if($_POST['date_from'] != '' && $_POST['date_to'] != '')
	{
		mrr_update_trailer_drops_for_trailer($_POST['date_from'],$_POST['date_to'],(int) $_POST['trailer_id'],(int) $_POST['customer_id']);
		
		/*
		//Show all of the follow: (assuming month is Feb)
		1-dropped and completed in Feb.
		2-dropped before Feb., but returned in Feb.
		3-dropped in Feb., but returned later...or not at all
		4-dropped before Feb., but completed after Feb...or not at all
		*/
		$date_filter="
			and (
				(
					trailers_dropped.linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' 
					and trailers_dropped.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
					
					and trailers_dropped.linedate_completed >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' 
					and trailers_dropped.linedate_completed <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
				)
				or
				(
					trailers_dropped.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
					
					and trailers_dropped.linedate_completed >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' 
					and trailers_dropped.linedate_completed <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
				)
				or
				(
					trailers_dropped.linedate >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' 
					and trailers_dropped.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
					
					and (trailers_dropped.linedate_completed >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' or trailers_dropped.linedate_completed='0000-00-00 00:00:00')
				)
				or
				(
					trailers_dropped.linedate <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
					
					and (trailers_dropped.linedate_completed >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00' or trailers_dropped.linedate_completed='0000-00-00 00:00:00')
				)
			)
		";
		
		
		//UPDATE: as of 4/7/2017, Megan stated the Dale wants to only bill them after it has been completed.  So the only ones that need to show up are the ones completed... 
		
	}
	//$date_filter="";
	
	$sql = "
		select trailers_dropped.*,
			customers.name_company,
			trailers.trailer_name
		
		from trailers_dropped
			left join customers on customers.id = trailers_dropped.customer_id
			left join trailers on trailers.id = trailers_dropped.trailer_id
		where trailers_dropped.deleted = 0
			".$date_filter."
			".($_POST['trailer_id'] ? " and trailers_dropped.trailer_id = '".sql_friendly($_POST['trailer_id'])."'" : '') ."
			".($_POST['customer_id'] ? " and trailers_dropped.customer_id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			".($_POST['filter_city']!="" ? " and trailers_dropped.location_city='".sql_friendly($_POST['filter_city'])."'" : "")."
			".($_POST['filter_state']!="" ? " and trailers_dropped.location_state='".sql_friendly($_POST['filter_state'])."'" : "")."
			".($_POST['filter_zip']!="" ? " and trailers_dropped.location_zip='".sql_friendly($_POST['filter_zip'])."'" : "")."
			
		order by trailers_dropped.trailer_id, trailers_dropped.linedate
	";
	$data = simple_query($sql);
	
	$secs_to_days=(60 * 60 * 24);
	
	$force_start_date=strtotime($_POST['date_from']." 00:00:00");
	$force_end_date=strtotime($_POST['date_to']." 23:59:59");
	
	$max_days=(int) ( ($force_end_date/$secs_to_days) - ($force_start_date/$secs_to_days));
	$max_days+=1;		//we count the day we started for the 1st day so that Feb is 28 days, etc.
	
	$last_trailer_id=0;	
	
	$days_cntr=0;
	$days_tot=0;
	$totdays_tot=0;
		
		// [".$max_days." days max]
	echo "
		<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1400px;text-align:left'>
		<tr>
			<td valign='top' colspan='9'>
				<center><i>Total is Days dropped within ".date("M j, Y", strtotime($_POST['date_from']))." - ".date("M j, Y", strtotime($_POST['date_to']))." Date Range.</i></center>
			</td>
		</tr>
		<tr>
			<td nowrap><b>Trailer Name</b></td>
			<td><b>Location</b></td>
			<td><b>Customer</b></td>
			<td nowrap><b>Date Dropped</b></td>
			<td><b>Notes</b></td>
			<td><b>&nbsp;</b></td>
			<td><b>Status</b></td>
			<td><b>Completed</b></td>
			<td align='right' nowrap><b>Bill Days</b></td>
			<td align='right' nowrap><b>Total-Free Days</b></td>
		</tr>
	";
		/*
			<td align='right' nowrap><b>Days-Prior</b></td>
			<td align='right' nowrap><b>Skipped</b></td>
		*/
	while($row = mysqli_fetch_array($data)) 
	{
		$days=0;
		$totdays=0;
		
		$mrr_notes="";
		
		/*
		if($row['drop_completed']  > 0)
		{
			$linedate = strtotime(date("m/d/Y",strtotime($row['linedate'])));
     		$linedate_completed = strtotime(date("m/d/Y",strtotime($row['linedate_completed'])));
     		if($row['linedate_completed']=="0000-00-00 00:00:00")		$linedate_completed=time();	
     		
     		$days=0;	
     		
		}
		else
		{
			$linedate = strtotime(date("m/d/Y",strtotime($row['linedate'])));
     		$linedate_completed=time();	
		}
		*/
		$skip_days=(int) $_POST['skip_days'];
    		$days_before=0;
				  		
    		//get start date...
          $is_started=1;
		$linedate=strtotime(date("m/d/Y",strtotime($row['linedate']))." 23:59:59");
		if($row['linedate']=="0000-00-00 00:00:00")	
		{	//not set, so default to beginning of period	
			$linedate=$force_start_date;		
			$is_started=0;	
		}
		if($linedate < $force_start_date)			
		{	//set but out of range...use start date.
			$linedate=$force_start_date;		
			$is_started=0;	
			$days_before=(int) ( ($force_start_date/$secs_to_days) - (strtotime(date("m/d/Y",strtotime($row['linedate']))." 23:59:59")/$secs_to_days) );
			
			//adjust skip days if the start date was before the beginning of the month.
			if($days_before >= $skip_days)
			{
				$skip_days=0;
			}
			else
			{
				$skip_days-=$days_before;
			}			
		}	
		
		//get end date...	
		$is_completed=0;
		$linedate_completed=0;
		if($row['linedate_completed']=="0000-00-00 00:00:00")		$linedate_completed=$force_end_date;								//not set or completed, so use max range.
		if($row['linedate_completed']!="0000-00-00 00:00:00")	{	$linedate_completed=strtotime(date("m/d/Y",strtotime($row['linedate_completed']))." 23:59:59");	$is_completed=1;	}
		if($linedate_completed > $force_end_date)			{	$linedate_completed=$force_end_date;		$is_completed=0;	}		//set but out of range...use end date (max range).
		
							
		
		
		//now calculate days.
		//$arg1=$linedate_completed;
          //$arg2=$linedate;  
          
          //$days=(int) ( ($arg1/$secs_to_days) - ($arg2/$secs_to_days));	
          //if($linedate==$force_start_date)	$days+=1;					//include the start date...started out of range.
          ////$days+=1;	
		////if($days > $max_days)			$days=$max_days;			//only use the max from the range.  Ex: 28 days in Feb 2017
          ////if($days_before > 0)			$days+=1;					//has trouble changing from month to month
          ////if($days_before > $skip_days && $skip_days==0)	$days+=1;		//has trouble changing from month to month
          
          //calculate the total days just to show them.
          //$arg3=strtotime(date("m/d/Y",strtotime($row['linedate']))." 23:59:59"); 
          //$arg4=time(); 
          //if($row['linedate_completed']!="0000-00-00 00:00:00")		$arg4=strtotime(date("m/d/Y",strtotime($row['linedate_completed']))." 23:59:59");  
                    
          //$totdays=(int) ( ($arg4/$secs_to_days) - ($arg3/$secs_to_days));	
          ////$totdays+=1;	
          //if($linedate==$force_start_date)	$totdays+=1;					//include the start date...started out of range.
          
          $use_complete=strtotime($row['linedate_completed']);
          if($use_complete==0 || $row['linedate_completed']=="0000-00-00 00:00:00")		$use_complete=$force_end_date;
          
		$cal_days1=$linedate_completed - $linedate;	
		$cal_days2=strtotime("".date("m/d/Y",$use_complete)." 23:59:59") - strtotime("".date("m/d/Y",strtotime($row['linedate']))." 23:59:59");
		
		$days=ceil($cal_days1/$secs_to_days);
		$totdays=ceil($cal_days2/$secs_to_days);
		//hold on to the details for debugging.
		$mrr_notes="
			Bill: ".date("m/d/Y H:i:s",$linedate_completed)." - ".date("m/d/Y H:i:s",$linedate)." = ".$days." days.<br>
			Total: ".date("m/d/Y",strtotime($row['linedate_completed']))." - ".date("m/d/Y",strtotime($row['linedate']))." = ".$totdays." days.
		";
		
		$mrr_notes="";
				
          if($skip_days > 0)
          {
          	$days = ($days - $skip_days);
          	$totdays = ($totdays - $skip_days);
          }
				
		if($days > 0)
		{				
     		$days_tot+=$days;
     		$totdays_tot+=$totdays;
     		
     		$complete_date="";
     		if($row['linedate_completed']!="0000-00-00 00:00:00" && $is_completed > 0)
     		{
     			$complete_date="".date("M j, Y", strtotime($row['linedate_completed']))."";
     		}
     		elseif($row['linedate_completed']!="0000-00-00 00:00:00")
     		{
     			$complete_date="<span style='color:#CC0000;'>".date("M j, Y", strtotime($row['linedate_completed']))."</span>";	
     		}
     		
     		echo "
     			<tr>
     				<td><a href='trailer_drop.php?id=$row[id]' target='view_drop_$row[id]'>$row[trailer_name]</a></td>
     				<td>$row[location_city], $row[location_state] $row[location_zip]</td>
     				<td>".trim($row['name_company'])."</td>
     				<td>".($is_started > 0 ? "".date("M j, Y", strtotime($row['linedate']))."" : "<span style='color:#CC0000;'>".date("M j, Y", strtotime($row['linedate']))."</span>")."</td>
     				<td>$row[notes]</td>
     				<td nowrap>".$mrr_notes."</td>
     				<td>".($is_completed > 0 ? 'Completed' : '')."</td>
     				<td>".$complete_date."</td>
     				<td align='right'>".$days."</td>
     				<td align='right'>".$totdays."</td>
     			</tr>
     		";		//<span style='color:#".($days > 7 ? "CC0000" : "000000").";'>".$days."</span>
     			/*
     				<td align='right'>".$days_before."</td>
     				<td align='right'>".$skip_days."</td>
     			*/
     		$days_cntr++;     	
     		
     		$sqlu="update trailers_dropped set invoice_pending='".$days."' where id='".$row['id']."'";
			$datau= simple_query($sqlu);	
		}
		$last_trailer_id=$row['trailer_id'];
	}
	echo "
			<tr>
				<td><b>".$days_cntr."</b></td>
				<td><b>Total</b></td>
				<td colspan='6'>&nbsp;</td>
				<td align='right'><b>".$days_tot."</b></td>
				<td align='right'><b>".$totdays_tot."</b></td>
			</tr>
		";	// Max days filtered ".$max_days.".
	echo "</table>";
?>
<? } ?>
<script type='text/javascript'>
	$('.date_picker').datepicker();
	
	function mrr_make_sicap_invoice_trailer()
	{
		if(parseInt($('#customer_id').val())==0)
		{
			$.prompt("Invoice Failed.  Please make sure you have selected a Customer for the Trailer Dropped Invoice.");	
			return;
		}	
		$.ajax({
			url: "ajax.php?cmd=mrr_make_sicap_invoice_trailer",
			type: "post",
			dataType: "xml",
			data: {
				//POST variables needed for "page" to load for XML output
				"cust_id": parseInt($('#customer_id').val()),
				"date_from":$('#date_from').val(),
				"date_to":$('#date_to').val()	
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
					$.noticeAdd({text: "Dropped Trailer Report has been Invoiced."});
				}
				else
				{
					if(custid==0)		$.prompt("Invoice Failed.  Please make sure you have selected a Customer for the Trailer Dropped Invoice.");	
					if(item_id==0)		$.prompt("Invoice Failed.  Could not locate the proper Inventory item for Accounting.");		
				}		
			}
		});
	}
</script>
<? include('footer.php') ?>