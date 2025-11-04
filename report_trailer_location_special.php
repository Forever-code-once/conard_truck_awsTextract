<? include('application.php') ?>
<? $usetitle="Lot Trailer (Location) Report";	?>
<? include('header.php') ?>
<table class='font_display_section' style='text-align:left;width:1600px'>
<tr>
	<td>
		<div class='section_heading'>Trailer Lot (Location) Report</div>
		<div style='color:#00CC00; background-color:#eeeeee; border:1px solid #00cc00;width:850px'>
			<span style='color:black;'><b>Updated:</b> Trailers Dropped as of 11/3/2014 (3PM CST) will have a dropped date and a completed drop date...which will be stamped when the dropped has been flagged completed.</span><br> 
			<span style='color:black;'>{<i>Previous trailer drops will use the same date for both date dropped and date drop was completed since this was not stored at the time.</i>}</span><br> 
			This report only shows the Trailers that have been dropped in the current date range and not completed, or trailer was dropped or completed within the date range.
		</div>
		<span class='alert'>(showing alerts for trailers not moved in the past 7 days)</span><br><br>
	<?
		$rfilter = new report_filter();
		$rfilter->show_date_range 		= true;
		$rfilter->show_trailer 			= true;
		$rfilter->show_trailer_owner 		= true;
		$rfilter->show_trailer_interchange	= true;
		$rfilter->show_active			= true;	
		$rfilter->show_customer			= true;	
		//$rfilter->show_single_date 		= true;
		$rfilter->show_font_size			= true;	
		$rfilter->show_filter();
      ?>
      </td>
</tr>    		
<tr>
	<td>         		
     <?   
     if(isset($_POST['build_report'])) 
     {
     	$mrr_adder="";
     	
     	//and linedate >='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'   	     	
     	$mrr_adder2="
     	 	and (
     				(linedate_completed='0000-00-00 00:00:00' 
     				 
     				and linedate <='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59')
     			or
     				(
     					(linedate <='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
     					or
     					linedate_completed >='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00')
     				)
     			)
     	";
     	
     	if($_POST['customer_id'] > 0)
        {
            if($_POST['customer_id'] == 7 || $_POST['customer_id'] ==1901 || $_POST['customer_id'] == 1682)
            {
                 $mrr_adder2.=" and (trailers_dropped.customer_id='7' or trailers_dropped.customer_id='1901' or trailers_dropped.customer_id='1682')";
            }
            else
            {
                 $mrr_adder2.=" and trailers_dropped.customer_id='".sql_friendly($_POST['customer_id'])."'";
            }
        }        
     
     
     	
     	//linedate >='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00' and 
     	// and linedate_completed <='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
     	     	
     	if($_POST['report_trailer_interchange'] ==1)  	$mrr_adder.=" and interchange_flag = 1";
     	elseif($_POST['report_trailer_interchange'] ==2) 	$mrr_adder.=" and interchange_flag = 0";
     	     	
     	if($_POST['report_active'] > 0)  				$mrr_adder.=" and active = 1";
     	if($_POST['trailer_id'] > 0)  				$mrr_adder.=" and id='".sql_friendly($_POST['trailer_id'])."'";
     	if(trim($_POST['report_trailer_owner'])!="")  	$mrr_adder.=" and trailer_owner='".sql_friendly(trim($_POST['report_trailer_owner']))."'";
     	     	
     	$sql = "
     		select *     		
     		from trailers
     		where deleted = 0
     			".$mrr_adder."     			
     		order by trailers.trailer_name
     	";
     	//echo "<br>QUERY: ".$sql."<br>";
     	$data = simple_query($sql);
     	     	
     	$secs_to_days=(60 * 60 * 24);
     	
     	$right_now=strtotime(date("m/d/Y",time())." 23:59:59");
     	$this_from=strtotime($_POST['date_from']." 00:00:00");
     	$this_to=strtotime($_POST['date_to']." 23:59:59");
     	$max_allowed=$this_to - $this_from;
     	$max_allowed= (int) ($max_allowed / $secs_to_days);
     	     	
     	echo "
     		<div style='clear:both'></div>
     		<table class='tablesorter font_display_section' style='margin:10px 10px;width:1500px;text-align:left'>
     		<thead>
     		<tr>
     			<th>Trailer</th>
     			<th>Owner</th>
     			<th>Customer/Location</th>
     			<th>Address</th>
     			<th>Address2</th>
     			<th>City</th>
     			<th>State</th>
     			<th>Zip</th>
     			<th>Date</th>
     			<th>Completed</th>
     			<th>Days</th>
     			<th>Details</th>
     			<th>Maint</th>
     			<th>Alert</th>
     		</tr>
     		</thead>
     		<tbody>
     	";
     	$counter = 0;
     	while($row = mysqli_fetch_array($data)) 
     	{
     		$show_me=0;
     		     		
     		$location = '';
     		$linedate = 0;
     		$details = '';
     		$addr1='';
     		$addr2='';
     		$city = "";
     		$state = "";
     		$zip = "";
     		
     		$linedate_completed=0;
     		
     		$my_mode=0;
     		
     		// check to see if this trailer is currently dropped
     		$sql = "
     			select trailers_dropped.*,
     				customers.name_company     				
     			from trailers_dropped
     				left join customers on customers.id = trailers_dropped.customer_id
     			where trailer_id = '$row[id]'
     				and trailers_dropped.drop_completed = 0
     				and trailers_dropped.deleted = 0
     				".$mrr_adder2."
     		";
     		//echo "<br>QUERY: ".$sql."<br>";
     		$data_dropped = simple_query($sql);     		
     		if(mysqli_num_rows($data_dropped)) 
     		{
     			$row_dropped = mysqli_fetch_array($data_dropped);
     			$location = "$row_dropped[name_company]";
     			
     			$addr1='';
     			$addr2='';
     			$city = $row_dropped['location_city'];
     			$state = $row_dropped['location_state'];
     			$zip = $row_dropped['location_zip'];
     			
     			$linedate = strtotime($row_dropped['linedate']);
     			$linedate_completed = strtotime(date("m/d/Y",strtotime($row_dropped['linedate_completed'])));
     			if($row_dropped['linedate_completed']=="0000-00-00 00:00:00")	$linedate_completed=0;
     			
     			//$show_date=$row_dropped['linedate_completed'];
     			
     			$details = "<a href='trailer_drop.php?id=$row_dropped[id]' target='view_drop_$row_dropped[id]'>Dropped:</a> $row_dropped[name_company]";
     			     			
     			if(time() - $linedate > 7 * 86400 && $linedate > 0) {
          			$show_alert = true;
          		} else {
          			$show_alert = false;
          		}
          		
          		$days=0;
          		if($linedate_completed==0)
          		{
          			$show_date=date("m/d/Y",time());
          			
          			$arg1=$right_now/$secs_to_days;
          			$arg2=$linedate/$secs_to_days;
          			$days=(int) ( $arg1 - $arg2);	// + 1;
          			$my_mode=4;
          		}
          		else
          		{
          			$show_date=$row_dropped['linedate_completed'];
          			
          			$arg1=$linedate_completed/$secs_to_days;
          			$arg2=$linedate/$secs_to_days;
          			$days=(int) ( $arg1 - $arg2);	// + 1;	
          			
          			$my_mode=1;
          		}
          		
          		/*
          		if($linedate_completed==0)
          		{
          			//$days= $right_now - $linedate;
          			
          			$arg1=$right_now;
          			$arg2=$linedate;  
          			$show_date=date("m/d/Y",time());
          			
          			if($right_now > $this_to)			$arg1=$this_to; 
          			if($linedate < $this_from)			$arg2=$this_from;            			
          		}
          		else
          		{
          			//$days= $linedate_completed - $linedate;	
          			$arg1=$linedate_completed ;
          			$arg2=$linedate;          			
          			
          			if($linedate_completed > $this_to)		$arg1=$this_to;
          			if($linedate < $this_from)			$arg2=$this_from;          			
          		}
          		
          		$days=(int) ( ($arg1/$secs_to_days) - ($arg2/$secs_to_days)) + 1;
          		
          		$max_allowed=$this_to - $this_from;
     			$max_allowed= (int) ($max_allowed / $secs_to_days)+ 1;
          		//if($days > $max_allowed)		$days=$max_allowed;   
          		*/
          		
          		$show_me=1; 
          		//$show_me=0;       		
     		}
     		
     		//test for carlex section
     		if($show_me == 0)
     		{
     			$my_mode=5;
     			$sqlx = "
          			select trucks_log_shuttle_routes.*,
          				(select trucks.name_truck from trucks where trucks.id=trucks_log_shuttle_routes.truck_id) as mytruck,
          				(select trailers.trailer_name from trailers where trailers.id=trucks_log_shuttle_routes.trailer_id) as mytrailer,
          				(select CONCAT(drivers.name_driver_first, ' ' ,drivers.name_driver_last) from drivers where drivers.id=trucks_log_shuttle_routes.driver_id) as mydriver,
          				customers.name_company,
          				(select users.username from users where users.id=trucks_log_shuttle_routes.user_id) as myuser,
          				(select option_values.fname from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myname,
          				(select option_values.fvalue from option_values where option_values.id=trucks_log_shuttle_routes.option_id) as myval
          			from trucks_log_shuttle_routes
          				left join timesheets on timesheets.id=trucks_log_shuttle_routes.timesheet_id
          				left join customers on customers.id = trucks_log_shuttle_routes.customer_id
          			where trucks_log_shuttle_routes.deleted=0 
          				and timesheets.deleted=0
          				and linedate_from >='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'
          				and linedate_to <='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
          				".($_POST['customer_id'] > 0 ? " and trucks_log_shuttle_routes.customer_id='".sql_friendly($_POST['customer_id'])."'" : "" )."
          				and trucks_log_shuttle_routes.trailer_id='".sql_friendly($row['id'])."'
          			order by trucks_log_shuttle_routes.linedate_from desc,
          					trucks_log_shuttle_routes.linedate_to desc,
          					trucks_log_shuttle_routes.id desc
          		";
          		$datax=simple_query($sqlx);
          		//if($row['id'] == 16) d($sqlx);
          		if($rowx = mysqli_fetch_array($datax)) 
          		{
          			$show_alert = false;
          			$days=0;
          			          			
          			$location = $rowx['name_company'];     			
     				$addr1="";
     				$addr2="";
     				$city = "";
     				$state = "";
     				$zip = "";
     				//$linedate=$rowx['linedate_from'];
     				//$linedate_completed=$rowx['linedate_to'];
     				
     				$linedate = strtotime(date("m/d/Y",strtotime($rowx['linedate_from'])));
     				$linedate_completed = strtotime(date("m/d/Y",strtotime($rowx['linedate_to'])));
     				
     				$my_mode=2;
     				
     				$arg1=$linedate_completed;
          			$arg2=$linedate;   
          			
          			$days=(int) ( ($arg1/$secs_to_days) - ($arg2/$secs_to_days));	// + 1;
          			if($days > 7)		$show_alert = true;
     				
     				//echo "($rowx[id] $days)";
     				
     				$details=$rowx['myname'];
     				
     				$show_me=1;
          		}
     		}
     		
     		$sqlx="";
     		if($linedate_completed==0)
     		{
     			$my_mode=6;
     			$sqlx = "
          			select equipment_history.*
          			from equipment_history
          			where equipment_history.deleted=0 
          				and equipment_history.equipment_type_id=2
          				and equipment_history.equipment_id='".sql_friendly($row['id'])."'          				
          				and equipment_history.linedate_aquired <= '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
          				and (
          					equipment_history.linedate_returned >='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'
          					and (
          						 equipment_history.linedate_returned <='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59' or equipment_history.linedate_returned='0000-00-00 00:00:00'
          					)          				
          				)
          			order by equipment_history.linedate_aquired desc
          		";		//and equipment_history.linedate_returned <='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
          		$datax=simple_query($sqlx);
          		if($rowx = mysqli_fetch_array($datax)) 
          		{
          			$my_mode=7;
          			
          			if($linedate==0)		$linedate = strtotime(date("m/d/Y",strtotime($rowx['linedate_aquired'])));
     				$linedate_completed = strtotime(date("m/d/Y",strtotime($rowx['linedate_returned'])));
     				if($rowx['linedate_returned']=="0000-00-00 00:00:00")		$linedate_completed=0;	
     				if($linedate_completed > 0)
     				{
     					$days=0;
     					$arg1=$linedate_completed;
          				$arg2=$linedate;  
          				
          				$days=(int) ( ($arg1/$secs_to_days) - ($arg2/$secs_to_days));	// + 1;
          				if($days > 7)		$show_alert = true;
          				
          				$details.=" <span class='good_alert'><b>- Returned.</b></span>";
          				if($_POST['customer_id'] == 0 || 1==1)
          				{
          					$show_me=1;
          					$my_mode=3;
          				}
     				}
          		}
     		}
     		
     		if($linedate_completed==0 && $my_mode==6)
     		{	//may have been returned before the date range...but never completed:
     			$sqlx = "
          			select equipment_history.*
          			from equipment_history
          			where equipment_history.deleted=0 
          				and equipment_history.equipment_type_id=2
          				and equipment_history.equipment_id='".sql_friendly($row['id'])."'          				
          				and equipment_history.linedate_aquired <= '".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
          				and equipment_history.linedate_returned <'".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'       				
          				
          			order by equipment_history.linedate_aquired desc
          		";	
          		$datax=simple_query($sqlx);
          		if($rowx = mysqli_fetch_array($datax)) 
          		{
          			$my_mode=8;
          			if($linedate==0)		$linedate = strtotime(date("m/d/Y",strtotime($rowx['linedate_aquired'])));
          			$linedate_completed = strtotime(date("m/d/Y",strtotime($rowx['linedate_returned'])));
     				if($rowx['linedate_returned']=="0000-00-00 00:00:00")		$linedate_completed=0;	
     				if($linedate_completed > 0)
     				{
     					$days=0;
     					$arg1=$linedate_completed;
          				$arg2=$linedate;  
          				
          				$days=(int) ( ($arg1/$secs_to_days) - ($arg2/$secs_to_days));	// + 1;
          				if($days > 7)		$show_alert = true;
          				
          				$details.=" <span class='good_alert'><b>- Returned.</b></span>";
          				$show_me=0;
          				
          				$my_mode=9;
     				}
          		}
     		}
     		
     		
     		if($show_me > 0)
     		{
                 $maint="";
     		     $warning_flag2=mrr_get_equipment_maint_notice_warning('trailer',$row['id']);
                 $breakdown_trailer=mrr_find_unit_breakdown_maint_request(59,$row['id']);
     		     
                 if($warning_flag2!="")         $maint.="".$warning_flag2." ";
                 if($breakdown_trailer!="")     $maint.=" <b>Unit Breakdown</b>";
                 //if($breakdown_trailer!="")     $maint.=" ".$breakdown_trailer."";
            
                 echo "
          			<tr class='".($counter % 2 == 1 ? "odd" : "even")."'>
          				<td><a href='admin_trailers.php?id=$row[id]' target='_blank'><b>$row[trailer_name]</b></a></td>
          				<td>$row[trailer_owner]</td>
          				<td>$location</td>
          				<td>$addr1</td>
          				<td>$addr2</td>
          				<td>$city</td>
          				<td>$state</td>
          				<td>$zip</td>
          				<td>".($linedate > 0 ? date("m-d-Y", $linedate) : "")."</td>
          				<td>".($linedate_completed > 0 ? date("m-d-Y", $linedate_completed) : "")."</td>
          				<td>".$days."</td>
          				<td>$details</td>
          				<td nowrap>$maint</td>
          				<td>".($show_alert ? "<span class='alert'>(alert)</span>" : "")."</td>
          			</tr>
          		";	//|".$my_mode."
          		
          		if($my_mode==6 && 1==2)
          		{
          			echo "
               			<tr class='".($counter % 2 == 1 ? "odd" : "even")."'>
               				<td>&nbsp;</td>
               				<td colspan='11'>QUERY: ".$sqlx."</td>
               				<td>&nbsp;</td>
               			</tr>
               		";
          		}
          		$counter++;
     		}     		
     	}
     	echo "
     		</tbody>
     		</table>
     	";
     }
     ?>
	</td>
</tr>
</table>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
</script>
<? include('footer.php') ?>