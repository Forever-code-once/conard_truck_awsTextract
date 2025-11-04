<? 
$usetitle = "Report - Truck Activity";
$use_title = "Report - Truck Activity";
?>
<? include('header.php') ?>
<?
	if(isset($_GET['date_from'])) {
		$_POST['date_from'] = $_GET['date_from'];
		$_POST['build_report'] = 1;
	}
	
	if(isset($_GET['date_to'])) {
		$_POST['date_to'] = $_GET['date_to'];
		$_POST['build_report'] = 1;
	}
	
	if(!isset($_POST['date_from']))	$_POST['date_from']=date("m/01/Y");
	if(!isset($_POST['date_to']))		$_POST['date_to']=date("m/d/Y");
?>
<form method='post' action=''>
     <table class='admin_menu2 font_display_section' style='margin:0 10px;width:1000px;text-align:left'>
     	<tr>
     		<td colspan='6'>
     			<center>
     			<span class='section_heading'>Report - Truck Activity</span>
     			</center>
     		</td>
     	</tr>
     	<tr>
     		<td colspan='6'>
     			<center>
     			<b>Date Range:</b> 
     			From <input type='text' name='date_from' id='date_from' size='10' value='<?= $_POST['date_from'] ?>'>
     			To <input type='text' name='date_to' id='date_to' size='10' value='<?= $_POST['date_to'] ?>'>
     			
     			<input type='submit' name='build_report' value='Run Report'>
     			
     			</center>
     		</td>
     	</tr>
     	
     	<tr>
     		<td colspan='6'>
     			<hr>
     		</td>
     	</tr>
     	
     	<tr>
			<td valign='top'><span class='mrr_link_like_on' onClick='mrr_show_truck_activity_details(1000,1);'><b>All</b></span> <br> <span class='mrr_link_like_on' onClick='mrr_show_truck_activity_details(1000,0);'><b>None</b></span></td>
			<td valign='top'><b>Date From</b></td>
			<td valign='top'><b>Date To</b></td>
			<td valign='top' align='right'><b>Trucks</b></td>			
			<td valign='top' align='right'><b>Replaced</b></td>
			<td valign='top' align='right'><b>Billable</b></td>
		</tr>
     						
     	<?
     		if(isset($_POST['build_report']))
     		{
               	$days=strtotime($_POST['date_to']) - strtotime($_POST['date_from']);
               	$days=(int) abs($days/(3600*24));
               	
               	for($i=0;$i <= $days;$i++)
               	{
               		$to_day= strtotime("+".$i." day",strtotime($_POST['date_from']));
               		$mydate=date("m/d/Y",$to_day);
               		
               		$res=get_active_truck_count_ranged($mydate,$mydate);
               	
               		//echo "<br>Tot Val=".$res['total_value'].".";
               		//echo "<br>Monthly Val=".$res['monthly_value'].".";
               		//echo "<br>SQL=".$res['sql'].".";	
               		
               		$action="<span class='mrr_link_like_on' onClick='mrr_show_truck_activity_details(".$i.",1);'><b>+</b></span> / <span class='mrr_link_like_on' onClick='mrr_show_truck_activity_details(".$i.",0);'><b>-</b></span>";
               		
               		echo "
               			<tr class='day_".$i."'>
               				<td valign='top'>".$action."</td>
     						<td valign='top'>".$res['date_start']."</td>
     						<td valign='top'>".$res['date_end']."</td>
     						<td valign='top' align='right'>".$res['trucks']."</td>     						
     						<td valign='top' align='right'>- ".$res['replaced']."</td>
     						<td valign='top' align='right'>= ".$res['billable']."</td>
     					</tr>
     					<tr class='day_".$i."_details all_details'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='4'>".$res['html']."</td>
     						<td valign='top' align='right'>&nbsp;</td>
     					</tr>
     					
          				<tr class='day_".$i."_details all_details'>
     						<td valign='top'>&nbsp;</td>
          					<td valign='top' colspan='4'><hr></td>
          					<td valign='top' align='right'>&nbsp;</td>
          				</tr> 
          				<tr class='day_".$i."_details all_details'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='3' align='right'><b>Rented</b></td>
          					<td valign='top' align='right'>$".number_format($res['rent_value'],2)."</td>
          					<td valign='top' align='right'>&nbsp;</td>
          				</tr> 
          				<tr class='day_".$i."_details all_details'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='3' align='right'><b>Leased</b></td>
          					<td valign='top' align='right'>$".number_format($res['lease_value'],2)."</td>
          					<td valign='top' align='right'>&nbsp;</td>
          				</tr> 
          				<tr class='day_".$i."_details all_details'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='3' align='right'><b>Company Owned</b></td>
          					<td valign='top' align='right'>$".number_format($res['comp_value'],2)."</td>
          					<td valign='top' align='right'>&nbsp;</td>
          				</tr> 
          				<tr class='day_".$i."_details all_details'>
     						<td valign='top'>&nbsp;</td>
     						<td valign='top' colspan='3' align='right'><b>Total Monthly Cost</b></td>
          					<td valign='top' align='right'>$".number_format(($res['rent_value'] + $res['lease_value'] + $res['comp_value']),2)."</td>
          					<td valign='top' align='right'>&nbsp;</td>
          				</tr> 
               		";
               		
               		
               		
               	}
               		
     		}
     		
               	//
               	/*
               	echo "<br>Tot Val=".$res['total_value'].".";
               	echo "<br>Monthly Val=".$res['monthly_value'].".";
               	echo "<br>SQL=".$res['sql'].".";
               	*/
               	
     		$res=get_active_truck_count_ranged($_POST['date_from'],$_POST['date_to']);
     		
     		$action="<span class='mrr_link_like_on' onClick='mrr_show_truck_activity_details(999,1);'><b>+</b></span> / <span class='mrr_link_like_on' onClick='mrr_show_truck_activity_details(999,0);'><b>-</b></span>";
     		
     		echo "
          			<tr>
						<td colspan='6'><hr></td>
					</tr>
          			<tr class='ranged_days'>
          				<td valign='top'>".$action."</td>
						<td valign='top'>".$res['date_start']."</td>
						<td valign='top'>".$res['date_end']."</td>
						<td valign='top' align='right'>".$res['trucks']."</td>
						<td valign='top' align='right'>- ".$res['replaced']."</td>
						<td valign='top' align='right'>= ".$res['billable']."</td>						
					</tr>
					<tr class='ranged_days_details all_details'>
						<td valign='top'>&nbsp;</td>
     					<td valign='top' colspan='4'>".$res['html']."</td>
     					<td valign='top' align='right'>&nbsp;</td>
     				</tr> 
     				
     				<tr class='ranged_days_details all_details'>
						<td valign='top'>&nbsp;</td>
     					<td valign='top' colspan='4'><hr></td>
     					<td valign='top' align='right'>&nbsp;</td>
     				</tr> 
     				<tr class='ranged_days_details all_details'>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='3' align='right'><b>Rented</b></td>
     					<td valign='top' align='right'>$".number_format($res['rent_value'],2)."</td>
     					<td valign='top' align='right'>&nbsp;</td>
     				</tr> 
     				<tr class='ranged_days_details all_details'>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='3' align='right'><b>Leased</b></td>
     					<td valign='top' align='right'>$".number_format($res['lease_value'],2)."</td>
     					<td valign='top' align='right'>&nbsp;</td>
     				</tr> 
     				<tr class='ranged_days_details all_details'>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='3' align='right'><b>Company Owned</b></td>
     					<td valign='top' align='right'>$".number_format($res['comp_value'],2)."</td>
     					<td valign='top' align='right'>&nbsp;</td>
     				</tr> 
     				<tr class='ranged_days_details all_details'>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='3' align='right'><b>Total Monthly Cost</b></td>
     					<td valign='top' align='right'>$".number_format(($res['rent_value'] + $res['lease_value'] + $res['comp_value']),2)."</td>
     					<td valign='top' align='right'>&nbsp;</td>
     				</tr> 
     				    				
               		";
               		/*
               		<tr class='ranged_days_details all_details'>
						<td valign='top'>&nbsp;</td>
     					<td valign='top' colspan='4'>".$res['sql']."</td>
     					<td valign='top' align='right'>&nbsp;</td>
     				</tr>
               		*/
     	?>
     	
     	
     	
     </table>	
</form>

<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
	$().ready(function() {
		
		mrr_show_truck_activity_details(1000,0);
		
	});
	
	function mrr_show_truck_activity_details(day_num,moder)
	{
		//final ranged details
		if(day_num ==999)
		{
			if(moder ==0)
			{
				$('.ranged_days_details').hide();
			}
			if(moder ==1)
			{
				$('.ranged_days_details').show();
			}
		}
		//all details
		if(day_num ==1000)
		{
			if(moder ==0)
			{
				$('.all_details').hide();
			}
			if(moder ==1)
			{
				$('.all_details').show();
			}
		}
		//just this row details
		if(day_num >=0)
		{
			if(moder ==0)
			{
				$('.day_'+day_num+'_details').hide();
			}
			if(moder ==1)
			{
				$('.day_'+day_num+'_details').show();
			}
		}
	}
</script>
<? include('footer.php') ?>