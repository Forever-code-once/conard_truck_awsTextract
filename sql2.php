<? include('application.php') ?>
<?

die('End of the Line.');


$load_id = 8339;
$dispatch_id = 11463;

			$sql = "
				select load_handler.*,
					customers.name_company,
					load_handler.actual_bill_customer - load_handler.actual_total_cost as load_profit,
					(select ifnull(sum(trucks_log.loaded_miles_hourly),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as loaded_miles_hourly,
					(select ifnull(sum(trucks_log.miles),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles,
					(select ifnull(sum(trucks_log.driver2_id),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as driver_cnt,
					(select ifnull(sum(trucks_log.miles_deadhead),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as miles_deadhead			
				from load_handler
					left join customers on customers.id = load_handler.customer_id
				where load_handler.deleted = 0
					and customers.deleted = 0			
					and load_handler.id = '".sql_friendly($load_id)."'
				order by load_handler.id
			";
			$data_new_daily_cost = simple_query($sql);
			$row_new_daily_cost = mysqli_fetch_array($data_new_daily_cost);
			
			
			$res_daily_cost = mrr_quick_and_easy_budget_maker_disp($row_new_daily_cost,$dispatch_id);
			$sql = "
				update trucks_log
				set daily_cost = '$res_daily_cost[daily_cost]'
				where id = '$dispatch_id'
				limit 1
			";
			d($sql);
			//simple_query($sql);

//12220 
/*
		$sql = "
			select trucks_log.* 
			
			from trucks_log, load_handler
			where trucks_log.deleted = 0
				and load_handler.deleted = 0
				and trucks_log.linedate_added > '2012-01-01'
				and (load_handler.invoice_number = '' or load_handler.invoice_number is null)
				and load_handler.id = trucks_log.load_handler_id
		";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data)) {
			$current_daily_cost = number_format(get_daily_cost($row['truck_id'], $row['trailer_id']),2);
			if($current_daily_cost != $row['daily_cost']) {
				echo "($row[load_handler_id] | $row[id] | $row[daily_cost] | $current_daily_cost<br>";
				$sql = "
					update trucks_log
					set daily_cost = '$current_daily_cost'
					where id = '$row[id]'
					limit 1
				";
				die;
				simple_query($sql);
				update_origin_dest($row['load_handler_id']);
				die;
			}
		}
*/
		die('aborted');
		$sql = "select * from trucks where deleted=0 order by id asc";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$truck_id=$row['id'];
			$monthly_cost=mrr_get_truck_cost($truck_id);	
			
			echo "<br>Truck ".$row['name_truck']." current cost is $".$monthly_cost.".";
			//$sql2="update trucks_log set truck_cost='".sql_friendly($monthly_cost)."' where truck_id='".sql_friendly($truck_id)."'";
			//simple_query($sql2);
			
			echo " ---Dispatches updated";
		}
		
		$sql = "select * from trailers where deleted=0 order by id asc";
		$data=simple_query($sql);
		while($row = mysqli_fetch_array($data)) 
		{
			$trailer_id=$row['id'];
			$monthly_cost=mrr_get_trailer_cost($trailer_id);	
			
			echo "<br>Trailer ".$row['trailer_name']." current cost is $".$monthly_cost.".";
		//	$sql2="update trucks_log set trailer_cost='".sql_friendly($monthly_cost)."' where trailer_id='".sql_friendly($trailer_id)."'";
		//	simple_query($sql2);
			
			echo " ---Dispatches updated";
		}
		//$newid=mysql_insert_id();
		d('Stopped');
?>