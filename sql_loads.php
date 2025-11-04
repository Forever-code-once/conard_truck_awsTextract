<? include('application.php')?>
<?

	$_POST['date_from'] = "2/01/2012";
	$_POST['date_to'] 	= "2/29/2012";

	$mrr_from_date=date("Y-m-d", strtotime($_POST['date_from']));
	$mrr_to_date=date("Y-m-d", strtotime("1 day", strtotime($_POST['date_to'])));
	
	$mrr_from_date2=date("Y-m-d", strtotime("-15 day", strtotime($_POST['date_from'])));
	$mrr_to_date2=date("Y-m-d", strtotime("15 day", strtotime($_POST['date_to'])));
	
	echo "<br><br>".$_POST['date_from']."  --  ".$_POST['date_to']."<br>";
	
	echo "<br><b>Load Report (Comparison Report Version)</b><br>";
	echo "<table border='1'>
			<tr>
				<td valign='top'><b>LoadID</b></td>
				<td valign='top'><b>Pickup Date</b></td>
				<td valign='top'><b>Customer</b></td>
				<td valign='top'><b>Dispatches</b></td>
				<td valign='top'><b>Disp Cnt</b></td>
				<td valign='top'><b>Disp Diff</b></td>
				<td valign='top'><b>Disp Miles</b></td>
				<td valign='top'><b>Disp Cost</b></td>
				<td valign='top'><b>Dispatch List</b></td>
			</tr>
		";
	
	$search_date_range = "
			and load_handler.linedate_pickup_eta >= '".$mrr_from_date."'
			and load_handler.linedate_pickup_eta < '".$mrr_to_date."'
		";
	$search_date_range2 = " 
			and trucks_log.linedate_pickup_eta >= '".$mrr_from_date."'
			and trucks_log.linedate_pickup_eta < '".$mrr_to_date."' 
		";
		
	$sql = "
			select load_handler.*,
				customers.name_company,
				(
					select count(trucks_log.id) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0 order by trucks_log.linedate_pickup_eta asc
				) as qty_dispatches,
				(
					select count(trucks_log.id) from trucks_log where trucks_log.load_handler_id=load_handler.id and trucks_log.deleted=0".$search_date_range2."order by trucks_log.linedate_pickup_eta asc
				) as qty_ranged	
					
			from load_handler
				left join customers on customers.id = load_handler.customer_id
			where load_handler.deleted = 0
				and customers.deleted = 0			
				".$search_date_range."			
			order by load_handler.linedate_pickup_eta asc,load_handler.id asc
	";
	
	$loads=0;
	$dispatches=0;
	$disp_incl=0;
	$disp_dif_tot=0;
	$tot_cost=0;
	$tot_miles=0;
	
	$tot_cost_used=0;
	$tot_miles_used=0;
	
	//$mrr_capture_sql=$sql;
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$dcost=0;
		$dmiles=0;
		$disp_coster=0;
		$disp_miler=0;
		
		$disp_table="
     		<table border='0'>
     		<tr>
     			<td valign='top'><b>DispID</b></td>
     			<td valign='top'><b>Pickup</b></td>
     			<td valign='top'><b>Miles</b></td>
     			<td valign='top'><b>DH Miles</b></td>
     			<td valign='top'><b>Tot Miles</b></td>
     			<td valign='top'><b>Cost</b></td>
     		</tr>     		
     	";
		$sql2 = "
			select trucks_log.*,
				DATEDIFF('".$mrr_from_date."',trucks_log.linedate_pickup_eta) as mrr_before,
				DATEDIFF(trucks_log.linedate_pickup_eta,'".$mrr_to_date."') as mrr_after
			from trucks_log
			where load_handler_id ='".sql_friendly($row['id'])."'
				and deleted = 0				
			order by trucks_log.linedate_pickup_eta asc
		";
		$data2 = simple_query($sql2);
		while($row2 = mysqli_fetch_array($data2)) 
		{
			$disp_table.="
     			<tr>
     				<td valign='top'>".$row2['id']."</td>
     				<td valign='top'>".$row2['linedate_pickup_eta']."</td>
     				<td valign='top'>".$row2['miles']."</td>
     				<td valign='top'>".$row2['miles_deadhead']."</td>
     				<td valign='top'>".($row2['miles'] + $row2['miles_deadhead'])."</td>
     				<td valign='top'>".$row2['cost']."</td>
     			</tr>   
     		";	
     		if($row2['mrr_before']<=0 && $row2['mrr_after']>=0)
     		{
     			$dcost+=$row2['cost'];
				$dmiles+=($row2['miles'] + $row2['miles_deadhead']);
			}
			else
			{
				$disp_coster+=$row2['cost'];
				$disp_miler+=($row2['miles'] + $row2['miles_deadhead']);
				
				$tot_cost_used+=$row2['cost'];
				$tot_miles_used+=($row2['miles'] + $row2['miles_deadhead']);	
			}
		}
     	$disp_table.="</table>";		
		
		$loads++;
		$dispatches+=$row['qty_dispatches'];
		$disp_incl+=$row['qty_ranged'];
		$disp_dif_tot+=($row['qty_dispatches'] - $row['qty_ranged']);
		
		$tot_cost+=$dcost;
		$tot_miles+=$dmiles;
		
		echo "
			<tr>
				<td valign='top'>".$row['id']."</td>
				<td valign='top'>".$row['linedate_pickup_eta']."</td>
				<td valign='top'>".$row['name_company']."</td>
				<td valign='top'>".$row['qty_dispatches']."</td>
				<td valign='top'>".$row['qty_ranged']."</td>
				<td valign='top'>".($row['qty_dispatches'] - $row['qty_ranged'])."</td>
				<td valign='top'>".$disp_miler."</td>
				<td valign='top'>$".$disp_coster."</td>
				<td valign='top'>".$disp_table."</td>
			</tr>
		";
	}
	
	echo "
			<tr>
				<td valign='top'>".$loads."</td>
				<td valign='top' colspan='2'><b>Totals</b></td>
				<td valign='top'>".$dispatches."</td>
				<td valign='top'>".$disp_incl."</td>
				<td valign='top'>".$disp_dif_tot."</td>
				<td valign='top'>".$tot_miles_used."</td>
				<td valign='top'>$".$tot_cost_used."</td>
				<td valign='top'>Difference Total Miles=".$tot_miles.".  Total Cost = $".$tot_cost."</td>
			</tr>
		";
	
	
	echo "</table>";
	
	echo "<br><br><b>Dispatch Report (Sales Report Version)</b><br>";		//============================================================================================================================================================================
	
	echo "<table border='1'>
			<tr>
				<td valign='top'><b>DispID</b></td>
				<td valign='top'><b>Pickup Date</b></td>
				<td valign='top'><b>Customer</b></td>
				<td valign='top'><b>Miles</b></td>
				<td valign='top'><b>Cost</b></td>
				<td valign='top'><b>LoadID</b></td>
				<td valign='top'><b>LoadDate</b></td>
				<td valign='top'><b>Used</b></td>
				<td valign='top'><b>Miles</b></td>
				<td valign='top'><b>Cost</b></td>
			</tr>
		";
	
	$search_date_range = "
			and load_handler.linedate_pickup_eta >= '".$mrr_from_date."'
			and load_handler.linedate_pickup_eta < '".$mrr_to_date."'
		";
	$search_date_range2 = " 
			and trucks_log.linedate_pickup_eta >= '".$mrr_from_date."'
			and trucks_log.linedate_pickup_eta < '".$mrr_to_date."' 
		";
	$dispatches=0;
	$tot_cost=0;
	$tot_miles=0;	
	$skipper=0;
	
	$tot_cost_used=0;
	$tot_miles_used=0;
	
	$sql = "
			select trucks_log.*,
				load_handler.linedate_pickup_eta as load_date,
				load_handler.customer_id
			from trucks_log,load_handler
			where trucks_log.load_handler_id=load_handler.id
				and trucks_log.deleted = 0	
				and load_handler.deleted = 0		
				".$search_date_range2."			
			order by trucks_log.linedate_pickup_eta asc,trucks_log.load_handler_id asc
	";
	
	$data = simple_query($sql);
	while($row = mysqli_fetch_array($data)) 
	{
		$comp_name='';
		$sql3 = "
			select name_company from customers where id='".sql_friendly($row['customer_id'])."'
		";
		$data3 = simple_query($sql3);
		if($row3 = mysqli_fetch_array($data3)) 
		{
			$comp_name=$row3['name_company'];	
		}
		
		$used='Yes';
		$miler='';
		$coster='';
		
		$disp_coster=0;
		$disp_miler=0;
		
		$sql2 = "
			select DATEDIFF('".$mrr_from_date."',load_handler.linedate_pickup_eta) as mrr_before,
				DATEDIFF('".$mrr_to_date."',load_handler.linedate_pickup_eta) as mrr_after
			from load_handler
			where id='".sql_friendly($row['load_handler_id'])."'
		";
		$data2 = simple_query($sql2);
		while($row2 = mysqli_fetch_array($data2)) 
		{
			if($row2['mrr_before'] > 0 || $row2['mrr_after'] < 0)
			{
				$used='--'.$row2['mrr_before'].'--'.$row2['mrr_after'].'------NO';
				$tot_cost+=$row['cost'];
				$tot_miles+=($row['miles'] + $row['miles_deadhead']);
				
				$miler=($row['miles'] + $row['miles_deadhead']);
				$coster=$row['cost'];
				
				$skipper++;
			} 
			else
			{
				$disp_coster+=$row['cost'];
				$disp_miler+=($row['miles'] + $row['miles_deadhead']);
				
				$tot_cost_used+=$row['cost'];
				$tot_miles_used+=($row['miles'] + $row['miles_deadhead']);	
			}
		}	
				
		$dispatches++;
		echo "
			<tr>
				<td valign='top'>".$row['id']."</td>
				<td valign='top'>".$row['linedate_pickup_eta']."</td>
				<td valign='top'>".$comp_name."</td>
				<td valign='top'>".$disp_miler."</td>
				<td valign='top'>$".$disp_coster."</td>
				<td valign='top'>".$row['load_handler_id']."</td>
				<td valign='top'>".$row['load_date']."</td>
				<td valign='top'>".$used."</td>
				<td valign='top'>".$miler."</td>
				<td valign='top'>".$coster."</td>
			</tr>
		";
	}
	
	echo "
			<tr>
				<td valign='top'>".$dispatches."</td>
				<td valign='top' colspan='2'><b>Totals</b></td>
				<td valign='top'>".$tot_miles_used."</td>
				<td valign='top'>$".$tot_cost_used."</td>
				<td valign='top'>".$row['load_handler_id']."</td>
				<td valign='top'>".$row['load_date']."</td>
				<td valign='top'>Excluded: ".$skipper."</td>
				<td valign='top'>".$tot_miles."</td>
				<td valign='top'>$".$tot_cost."</td>
			</tr>
		";
	
	echo "</table>";
	
	
	
	echo "<br><br>";
	die('End of test');
?>
