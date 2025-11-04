<? include('application.php') ?>
<?
	$mrr_debug_time_start=date("His");		//for page load speed checks...used at bottom of the page.
	$mrr_debug_time1=time();
	
	if(isset($_SESSION['inventory_access'])) {
		javascript_redirect("login.php?out=1");
	}
	
	mrr_check_drivers_for_loads();
	mrr_trim_old_truck_tracking_plot_points(7);	//remove truck_tracking points older than 7 days  
		
	
	if(!isset($_SESSION['map_display'])) 	$_SESSION['map_display'] = 0;
	if(isset($_GET['map'])) 				$_SESSION['map_display'] = $_GET['map'];
		

	
	$mrr_calendar_mode=0;
	$mrr_alt_mode=0;
	
	if(isset($_GET['mrr_calendar_mode']))
	{
		$mrr_calendar_mode=$_GET['mrr_calendar_mode'];
		$_SESSION['mrr_calendar_mode']=$_GET['mrr_calendar_mode'];	
	}
	elseif(isset($_SESSION['mrr_calendar_mode']))
	{
		$mrr_calendar_mode=$_SESSION['mrr_calendar_mode'];
		$_GET['mrr_calendar_mode']=$_SESSION['mrr_calendar_mode'];	
	}
		
	if(isset($_SESSION['full_calendar_view_flag']))
	{
		if($_SESSION['full_calendar_view_flag']==true)
		{
			$_SESSION['mrr_calendar_mode']=1;
			$_GET['mrr_calendar_mode']=1;
		}
		else
		{
			$_SESSION['mrr_calendar_mode']=0;
			$_GET['mrr_calendar_mode']=0;
		}		
	}
	
	if(isset($_GET['mrr_calendar_mode']))
	{
		if($_GET['mrr_calendar_mode']==2)
		{
			$mrr_alt_mode=1;	
			$mrr_calendar_mode=2;	
			//$_GET['alt_mode']=1;
			$_SESSION['full_calendar_view_flag']=false;
		}
		elseif($_GET['mrr_calendar_mode']==1)
		{
			$show_full_calendar = true;
			$mrr_calendar_mode=1;
			$_SESSION['full_calendar_view_flag']=true;
		}
		elseif($_GET['mrr_calendar_mode']==0)
		{
			$show_full_calendar = false;
			$mrr_calendar_mode=0;
			$_SESSION['full_calendar_view_flag']=false;
		}
		$_SESSION['mrr_calendar_mode']=$mrr_calendar_mode;	
	}
	else
	{
		if(!isset($_SESSION['full_calendar_view_flag']) && !isset($_GET['alt_mode']))	
     	{	//none set, should use the small calendar
     		$show_full_calendar = false;
     		$mrr_calendar_mode=0;
     		$_SESSION['full_calendar_view_flag']=false;
     	}
     	if(isset($_SESSION['full_calendar_view_flag']) && $_SESSION['full_calendar_view_flag']==true) 
     	{	//FULL Calendar set, should use the full calendar
     		$show_full_calendar = true;
     		$mrr_calendar_mode=1;
     		$_SESSION['full_calendar_view_flag']=true;
     	}
     	elseif(isset($_SESSION['full_calendar_view_flag']))
     	{	//also show small calendar...needed for refresh
     		$show_full_calendar = false;
     		$mrr_calendar_mode=0;
     		$_SESSION['full_calendar_view_flag']=false;	
     	}
     	//if(!isset($_GET['alt_mode']))			$_GET['alt_mode']=0;
     		
     	if(isset($_GET['alt_mode']))	
     	{	//ALT MODE set, should use the new board entirely
     		if($_GET['alt_mode'] > 0)
     		{
     			$mrr_alt_mode=1;	
     			$mrr_calendar_mode=2;	
     			$_SESSION['full_calendar_view_flag']=false;
     		}
     	}
     	$_SESSION['mrr_calendar_mode']=$mrr_calendar_mode;	
	}
	
		
	/* find the closest sunday to today */
	$startdate = date("w", time());
	$startdate = strtotime("-$startdate days");	
	
	
	if(isset($_GET['deleventid'])) 
	{
		$sql = "
			update calendar
			set deleted = 1
			where id = '".sql_friendly($_GET['deleventid'])."'
		";
		simple_query($sql);
	}
	
	if(isset($_GET['delcalendarid']))
	{
		$sql = "
			update calendar
			set deleted = 1
			where id = '".sql_friendly($_GET['delcalendarid'])."'
		";							
		simple_query($sql);		
	}
	if(isset($_GET['deldriverunavailableid']))
	{
		$sql = "
			update drivers_unavailable
			set deleted = 1
			where id = '".sql_friendly($_GET['deldriverunavailableid'])."'
		";							
		simple_query($sql);	
	}
	
	if(isset($_GET['delnoteid'])) {
		$sql = "
			update notes
			set deleted = 1
			where id = '".sql_friendly($_GET['delnoteid'])."'
		";
		$data_delete_note = simple_query($sql);
	}
	
	// simple query to make sure any 'lost' dispatches are moved to the current day
	$sql = "
		update trucks_log
		set linedate = '".date("Y-m-d")."'
		where linedate = 0
			and deleted = 0
	";
	simple_query($sql);
	
	/*
	// get our timezones
	$sql = "
		select *
		
		from timezones
		where deleted = 0
		order by gmt_difference
	";
	$data_time = simple_query($sql);
	*/
	
	$gmt = date("O") / 100;
	$current_date = getdate();
	

	
	
	// get the available loads
	$sql = "
		select load_handler.*,
			customers.name_company,
			drivers.name_driver_first,
			drivers.name_driver_last
		
		from load_handler
			left join customers on load_handler.customer_id = customers.id
			left join drivers on drivers.id = load_handler.preplan_driver_id
		where load_handler.deleted = 0
			and load_available = 1
		order by linedate_pickup_eta, origin_state, origin_city, dest_state, dest_city
	";
	// or (select count(*) from trucks_log where load_handler_id = load_handler.id and trucks_log.deleted = 0) = 0
	$data_avail_loads = simple_query($sql);
	
	
	$sql = "
		select *
		
		from drivers
		where active = 1
			and deleted = 0
			and hide_available = 0
			and id not in (
							select driver_id
							
							from trucks_log 
							where deleted = 0 
								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
								and dispatch_completed = 0
						)

			and id not in (
							select driver2_id
							
							from trucks_log 
							where deleted = 0 
								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
								and dispatch_completed = 0
						)

			and id not in (
							select driver_id
							
							from drivers_unavailable
							where drivers_unavailable.deleted = 0
								and drivers_unavailable.driver_id = drivers.id
								and linedate_start <= '".date("Y-m-d")."'
								and linedate_end >= '".date("Y-m-d")."'
			)

		order by name_driver_last, name_driver_first
	";
	$data_avail_drivers = simple_query($sql);
	
	if(isset($_GET['debug'])) {
		echo "<br><br><br><br><br>";
		while($rd = mysqli_fetch_array($data_avail_drivers)) {
			echo $rd['name_driver_first']."<br>";
		}		
	}
		
	/* get a list of all our appointments */
	$sql = "
		select *
		
		from appointments
		where deleted = 0
		order by linedate_start, time_start, time_end
	";
	$data_appointments = simple_query($sql);
	
	$sql = "
		select notes_general,
			id
		
		from site_sections
		where site_default = 1;
	";
	$data_notes = simple_query($sql);
	$row_notes = mysqli_fetch_array($data_notes);
	
	
	$sql = "
		select *
		
		from notes
		where section_id = '$row_notes[id]'
			and deleted = 0
			and customer_id = 0
			and linedate = 0
		order by linedate_added
	";
	$data_notes_more = simple_query($sql);
	
	/* grab our list of trucks that are currently being used */
	$sql = "
		select distinct truck_id
		
		from trucks, trucks_log
		where trucks.id = trucks_log.truck_id
			and trucks_log.deleted = 0
			and trucks_log.dispatch_completed = 0
	";
	$data_trucks_used = simple_query($sql);
	
	/* build an array of truck IDs that are currently being used so we can quickly search them later */
	$trucks_array = array();
	while($row_trucks_used = mysqli_fetch_array($data_trucks_used)) {
		$trucks_array[] = $row_trucks_used['truck_id'];
	}
	
	/* grab our list of all 'non-deleted' trucks in our database */
	// *** subquery is a potential slow point ***
	$sql = "
		select trucks.id,
			trucks.name_truck,
			drivers.attached_truck_id,
			drivers.name_driver_first,
			drivers.name_driver_last,
			drivers.id as driver_id,
			ifnull((
				select max(trucks_log.linedate)
				
				from trucks_log
				where truck_id = trucks.id
					and trucks_log.deleted = 0
					and trucks_log.linedate > '".date("Y-m-d", strtotime("-1 month", time()))."'
					and trucks_log.linedate < '".date("Y-m-d", strtotime("+2 day", time()))."'
			),0) as linedate_last_moved
			
		from trucks
			left join drivers on drivers.attached_truck_id = trucks.id and drivers.deleted = 0 and drivers.active = 1
		where trucks.deleted = 0
			and trucks.active = 1
			and trucks.id not in 	
							(
								select replacement_xref_id
								
								from equipment_history eh
								where eh.deleted = 0
									and eh.linedate_returned = 0
									and eh.equipment_type_id = 1
							)
		order by trucks.name_truck
	";
	$data_trucks = simple_query($sql);
	
	/* grab our list of trailers that are currently being used */
	$sql = "
		select distinct trailer_id,
			trucks_log.location,
			trucks_log.linedate,
			trucks.name_truck
		
		from trailers
			inner join trucks_log on trailers.id = trucks_log.trailer_id
			left join trucks on trucks.id = trucks_log.truck_id
		where trucks_log.deleted = 0
			and trailers.allow_multiple = 0
			and trucks_log.dispatch_completed = 0
	";
	$data_trailers_used = simple_query($sql);
	
	/* build an array of trailer IDs that are currently being used so we can quickly search them later */
	$trailer_array = array();
	while($row_trailers_used = mysqli_fetch_array($data_trailers_used)) {
		$trailer_array[] = $row_trailers_used['trailer_id'];
	}
	
	$trailer_available_array = array();
	$data_trailers_available = get_available_trailers();
	while($row_trailer_available = mysqli_fetch_array($data_trailers_available)) {
		$trailer_available_array[] = $row_trailer_available['trailer_id'];
	}
	
	/* grab our list of all 'non-deleted' trailers in our database */
	$sql = "
		select trailers.*,
			drivers.attached_trailer_id,
			drivers.name_driver_first,
			drivers.name_driver_last,
			drivers.id as driver_id
			
		from trailers
			left join drivers on drivers.attached_trailer_id = trailers.id  and drivers.deleted = 0 and drivers.active = 1
		where trailers.deleted = 0
			and trailers.active = 1		
			and trailers.id not in 
				(
					select distinct(trailers_dropped.trailer_id) from trailers_dropped where trailers_dropped.deleted=0 and trailers_dropped.drop_completed=0 order by trailers_dropped.trailer_id asc
				)	
		order by trailers.trailer_name
	";
	$data_trailers = simple_query($sql);

	/*
	
	*/
	                    		
                    		

	/*
	$sql = "
		select load_handler.*
		
		from load_handler
			left join customers on customers.id = load_handler.customer_id
		where load_handler.deleted = 0
			and auto_created = 1
			and linedate_auto_created_reviewed = 0
	";
	$data_edi = simple_query($sql);
	*/
	
	$page_timer_array[] = show_page_time();
	
	if(count($driver_avail_array)==0)
	{
			$driver_avail_array = array();
			$driver_avail_id = array();
			
			$tval = "";
			while($row_avail = mysqli_fetch_array($data_avail_drivers)) 
			{
				$sql = "
					select concat(shipper_city, ', ', shipper_state) as last_city,
						load_handler_stops.linedate_completed,
						name_driver_first,
						name_driver_last,
						load_handler.customer_id,
						drivers.id as driver_id,
						trucks_log.load_handler_id,
						drivers.attached_truck_id,
						drivers.attached_trailer_id,
						drivers.driver_has_load,
						drivers.available_notes,
						drivers.linedate_driver_has_load
					
					from load_handler_stops, trucks_log, drivers, load_handler
					where trucks_log.deleted = 0 
						and load_handler_stops.deleted = 0
						and trucks_log.id = load_handler_stops.trucks_log_id
						and drivers.hide_available = 0
						and drivers.id = '$row_avail[id]'
						and load_handler.id = trucks_log.load_handler_id
						and (trucks_log.driver_id = '$row_avail[id]' or trucks_log.driver2_id = '$row_avail[id]')
						and load_handler_stops.linedate_completed > '".date("Y-m-d", strtotime("-1 month", time()))."'
						and load_handler_stops.linedate_completed < '".date("Y-m-d", strtotime("1 day", time()))."'
					order by load_handler_stops.linedate_completed desc
					limit 1
				";
				
				$data_last_stop = simple_query($sql);
				if(!mysqli_num_rows($data_last_stop)) 
				{
					$last_complete = 0;
					$last_city = "";
					$driver_avail_array[$row_avail['id']] = 0;
				}
				else 
				{
					$row_last_stop = mysqli_fetch_array($data_last_stop);
					$last_city = $row_last_stop['last_city'];
					$last_complete = strtotime($row_last_stop['linedate_completed']);
					$driver_avail_array[$row_avail['id']] = $row_last_stop;
					$driver_avail_id[] = $row_avail['id'];

					// get a list of pre-planed loads available for this driver
					$sql = "
						select load_handler.*,
							customers.name_company
						
						from load_handler, customers
						where load_handler.preplan = 1
							and customers.id = load_handler.customer_id
							and load_handler.deleted = 0
							and load_handler.preplan_driver_id = '".sql_friendly($row_avail['id'])."'
					";
					$data_preplan = simple_query($sql);
				}
				
				//<a href='javascript:new_load()'>$row_avail[name_driver_last], $row_avail[name_driver_first] ($last_city)</a>
				if(mysqli_num_rows($data_last_stop)) 
				{
					while($row_preplan = mysqli_fetch_array($data_preplan)) 
					{						
						//$row_preplan[id]
						//$row_preplan[name_company]
						//<a href='javascript:edit_entry_truck(0,0,)'>$row_preplan[id] - $row_preplan[name_company]</a>
					}
				}	
			}
	}//end if	
	$new_design=0;
	if(isset($_SESSION['user_id']))
	{
		//if($_SESSION['user_id']==23)	$new_design=1;				
	}
	$new_design=1;
	
	$page_timer_array[] = show_page_time();

$usetitle="TEST LOAD BOARD";

?>
<? include('header.php') ?>
<?
	
	if(!isset($new_style_path))
	{
		$new_style_path="images/2012/";	
	}
	
	//added April 2012 so that Maint flags are all found in one group/set in one query instead of every point.
	$mrr_maint_resx=mrr_get_equipment_maint_notice_warning_array('truck');
	$mrr_maint_truck_cntr=$mrr_maint_resx['num'];
	$mrr_maint_truck_arr=$mrr_maint_resx['arr'];
	$mrr_maint_truck_links=$mrr_maint_resx['links'];
	
	$mrr_maint_resy=mrr_get_equipment_maint_notice_warning_array('trailer');
	$mrr_maint_trailer_cntr=$mrr_maint_resy['num'];
	$mrr_maint_trailer_arr=$mrr_maint_resy['arr'];
	$mrr_maint_trailer_links=$mrr_maint_resy['links'];
	
	$page_timer_array[] = show_page_time();
	
	//functions for new components...this is so they can be used in other places later...or called by AJAX functions...Added June 2012.
	function mrr_conard_component_time()
	{
		//global $new_style_path;
		//global $defaultsarray;
		$res="";
				
		//$mon=date("m");
		$smon=date("M");
		$day=date("d");
		$year=date("Y");
		$time=date("g:i A");
		$wday=date("D");		
		
		$res.="<div class='left_box top'>";
		$res.=	"<span>".$day."</span>";
		$res.=	"<span>".$time."</span>";
		$res.=	"<small>".$wday."<br>".$smon." ".$year."</small>";
		$res.="</div>";
		
		return $res;
	}	
	function mrr_conard_component_fuel()
	{
		global $new_style_path;
		global $defaultsarray;
		
		/* get a list of companies that have separate fuel surcharge prices */
		$sql = "
			select customers.fuel_surcharge,
				customers.name_company,
				customers.id,
				(select fuel_surcharge.fuel_surcharge from fuel_surcharge where customer_id = customers.id and fuel_surcharge.range_lower <= $defaultsarray[fuel_surcharge] order by fuel_surcharge desc limit 1 ) as surcharge_list
			
			from customers 
			where customers.deleted = 0
				and customers.active = 1
				and use_fuel_surcharge > 0
			 	
			 having surcharge_list > 0 or customers.fuel_surcharge > 0
			order by name_company
		";
		$data_surcharge = simple_query($sql);
		
		$rval="<br><br><br><br>";		
		/*
		$rval = "<span style='font-size:14px; color:white;'><b>Fuel Surcharges:</b></span><br>";
		$rval .= "
					<span style='font-size:12px; color:white;'>NATIONAL AVERAGE</span>	
					<span style='font-size:12px; color:#c56100;'>".$defaultsarray['fuel_surcharge']." ".show_help('index.php','Fuel Surcharge Ntl Avg',$new_style_path,"question_mark.png")."</span><br><br>						
			";	
		*/	
		$i = 0;
		while($row_surcharge = mysqli_fetch_array($data_surcharge)) 
		{
			$i++;
			$rval .= "
					<span style='font-size:12px; color:#c56100;'>".($row_surcharge['fuel_surcharge'] > 0 ? $row_surcharge['fuel_surcharge'] : $row_surcharge['surcharge_list'])."</span>	
					<span style='font-size:12px; color:white;'><b>$row_surcharge[name_company]</b></span><br>							
			";			
		}		
		$res="";				
		$res.="<div class='first-bg'>";
		
		$res.=	"<div class='full-surcharge fuel_surcharge'><p>FUEL SURCHARGE NATIONAL AVERAGE</p></div>";
		$res.=	"<div class='surcharge-rate fuel_surcharge'><span style='color:#c56100;'>".$defaultsarray['fuel_surcharge']."</span> ".show_help('index.php','Fuel Surcharge Ntl Avg',$new_style_path,"question_mark.png")."</div>";	
		$res.=	"<div id='fuel_surcharge_holder' onMouseOver='mrr_fuel_surcharge_show(\"".$defaultsarray['fuel_surcharge']."\");' onMouseOut='mrr_fuel_surcharge_hide();'>".$rval."</div>";
		
		$res.="</div>";
					
		return $res;
	}
	function mrr_conard_component_tool()
	{
		//global $new_style_path;
		//global $defaultsarray;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h4>Tools</h4>";
		$res.=	"<div class='tool-list'>";
		$res.=		"<ul>";
		$res.=			"<li><a href='javascript:new_load()'>New Load</a></li>";
		$res.=			"<li><a href='quote.php'>New Quote</a></li>";
		$res.=			"<li><a href='javascript:edit_dropped_trailer(0)'>New Drop Trailer</a></li>";
		$res.=			"<li class='actual'><div id='map_toggle'> Show Map</div></li>";
		$res.=		"</ul>";
		$res.=		"<div id='map_holder' style='display:none; z-index:1000;'>";
		$res.=			"<img src='images/timezone_map.gif'>";
		$res.=		"</div>";			
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
	}
	
	
	function mrr_conard_component_truck_movement()
	{
		global $new_style_path;
		//global $defaultsarray;
		
		global $data_trucks;
		
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span>These trucks have not moved in over 2 business day</span> ".show_help('index.php','Trucks Not Moved Box',$new_style_path,"question_mark.png")."</h3>";
		$res.=	"<ul class='left_sec'>";
		$res.=		"";
		
		if(mysqli_num_rows($data_trucks)) mysqli_data_seek($data_trucks,0);
		$cntr=0;
		$arr_used[0]=0;
		
		while($row_truck = mysqli_fetch_array($data_trucks)) {
			$linedate = 0;
			
			$found_it=0;
			for($x=0;$x< $cntr; $x++)
			{
				if( $row_truck['attached_truck_id'] == $arr_used[ $x ] )  $found_it++;
			}
			
			if($found_it==0)
			{
     			$warning_flag="";	//mrr_get_equipment_maint_notice_warning('truck',$row_truck['id']);				
     			for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
     			{
     				if($mrr_maint_truck_arr[ $rr ] == $row_truck['id'])		$warning_flag=trim($mrr_maint_truck_links[ $rr ]);
     			}
     			
     			if(strtotime($row_truck['linedate_last_moved']) > 0) 			$linedate = strtotime($row_truck['linedate_last_moved']);
     			
     			if((time() - $linedate) > (2 * 86400) && $linedate > 0) {
     				$show_alert = true;
     			} else {
     				$show_alert = false;
     			}
     			$use_dater="";
     			if($linedate > 0)		$use_dater=date("m-d-Y", $linedate);
     			
     			if($show_alert) {
     				$res.="<li><span>$row_truck[name_truck]</span>  <strong>".$use_dater." <img src='".$new_style_path."red_icon.png'  border='0' alt='' ></strong></li>";
     				
     				$arr_used[ $cntr ] = $row_truck['id'];
     				$cntr++;
     			}
			}
		}
		
		if(mysqli_num_rows($data_trucks)) mysqli_data_seek($data_trucks,0);
		
		$res.=	"</ul>";		
		$res.="</div>";
		
		if($cntr==0)	$res="";
		
		return $res;
	}
	function mrr_conard_component_load_available()
	{
		global $new_style_path;
		//global $defaultsarray;
		
		global $data_avail_loads;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span>Available Loads</span> ".show_help('index.php','Available Loads',$new_style_path,"question_mark.png")."</h3>";	// <em>No Date</em>
		$res.=	"<ul class='left_sec'>";
		
		$last_date = -1;
		$show_section = false;
		while($row_avail = mysqli_fetch_array($data_avail_loads)) {
			
			if($last_date != date("Y-m-d", strtotime($row_avail['linedate_pickup_eta']))) {
				$last_date = date("Y-m-d", strtotime($row_avail['linedate_pickup_eta']));
				
				if(strtotime($last_date) < 0) {
					$use_title = "No Date";
					$show_section = true;
				} else {
					$use_title = date("M j", strtotime($last_date));
					$show_section = false;
				}
				
				if($show_section) {
					
					$res.="<li><span><b>".$use_title."</b></span></li>";
				}
			}
						
			if($show_section) {
				
				$use_bg_color = '';
				
				if($row_avail['preplan']) {
					//$use_bg_color = '#ecffe2';
				} else {
					$use_bg_color = '';
				}
				/*
				echo "
					<div class='trailer_entry_available ".($row_avail['auto_created'] ? "entry_auto_created" : "")."'>
						<div style='background-color:#e5e9ff;width:100%'><a href='javascript:edit_entry_truck(0,0,$row_avail[id])'>($row_avail[id]) $row_avail[name_company]</a></div>
						".($row_avail['preplan'] == '1' ? "<div style='background-color:$use_bg_color'>&nbsp;&nbsp;&nbsp; PREPLAN - $row_avail[name_driver_first] $row_avail[name_driver_last]</div>" : "")."
						<div style='background-color:$use_bg_color;'>".( ? "" : "").( ? " - ". : "")."</div>
						<div style='background-color:$use_bg_color;'>".($row_avail['dest_city'] != '' ? "&nbsp;&nbsp;&nbsp; Dest - $row_avail[dest_city], $row_avail[dest_state]" : "").($row_avail['linedate_dropoff_eta'] > 0 ? " - ".date("H:i", strtotime($row_avail['linedate_dropoff_eta'])): "")."</div>
					</div>
				";
				*/
				
				$short_cust=$row_avail['name_company'];
				$titler="";
				if(strlen($short_cust)>=20)
				{
					$short_cust=substr($short_cust,0,17) . "...";
					$long_cust=trim($row_avail['name_company']);
					$long_cust=str_replace("'","&apos;",$long_cust);
					$titler=" title='".$long_cust."'";
				}
				
				$res.="<li><span><a href='javascript:edit_entry_truck(0,0,$row_avail[id])' style='color:white;'".$titler.">($row_avail[id]) ".$short_cust."</a></span></li>";
				if($row_avail['preplan'] == '1')
				{
					$res.="<li><span><div style='background-color:$use_bg_color'>&nbsp;&nbsp;&nbsp; PREPLAN - $row_avail[name_driver_first] $row_avail[name_driver_last]</div></span></li>";	
				}
				if($row_avail['origin_city'] != '' || $row_avail['linedate_pickup_eta'] > 0)
				{
					$res.="<li><span style='background-color:$use_bg_color;'>";
					if($row_avail['origin_city'] != '')
					{
						$res.="Orig - $row_avail[origin_city], $row_avail[origin_state]";
					}
					if($row_avail['linedate_pickup_eta'] > 0)
					{
						$res.=" - ".date("H:i", strtotime($row_avail['linedate_pickup_eta']))."";	
					}
					$res.="</span></li>";
				}
				
				if($row_avail['dest_city'] != '' || $row_avail['linedate_dropoff_eta'] > 0)
				{
					$res.="<li><span style='background-color:$use_bg_color;'>";
					if($row_avail['dest_city'] != '')
					{
						$res.="Dest - $row_avail[dest_city], $row_avail[dest_state]";
					}
					if($row_avail['linedate_dropoff_eta'] > 0)
					{
						$res.=" - ".date("H:i", strtotime($row_avail['linedate_dropoff_eta']))."";	
					}
					$res.="</span></li>";
				}
			}
		}
		$res.=	"</ul>";		
		$res.="</div>";
		
		return $res;
	}
	
	
	function mrr_conard_component_list_trailers()
	{
		global $new_style_path;
		//global $defaultsarray;
		
		global $trailer_available_array;
		global $trailer_array;
		global $data_trailers_used;
		global $data_trailers;
				
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span><a href='javascript:toggle_trailers()'>+ &nbsp; &nbsp; &nbsp; &nbsp;</a> Trailer List</span> ".show_help('index.php','Trailer List',$new_style_path,"question_mark.png")."</h3>";
		$res.=	"<ul class='left_sec pin'>";
		
		while($row_trailers = mysqli_fetch_array($data_trailers)) {
     		
     		$warning_flag="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_trailers['id']);				
     		for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
     		{
     			if($mrr_maint_trailer_arr[ $rr ] == $row_trailers['id'])		$warning_flag=trim($mrr_maint_trailer_links[ $rr ]);
     		}				
     		
     		$tmp_date = $startdate;
     		if(array_search($row_trailers['id'], $trailer_available_array) !== false) 
     		{       			
     			$use_attacher="<strong>$row_trailers[trailer_name]</strong>";
     			if($row_trailers['attached_trailer_id'])
     			{
     				$use_attacher="<strong>($row_trailers[name_driver_first] $row_trailers[name_driver_last])</strong>";
     			}
     			//".$warning_flag."
     			//<a href=\"javascript:update_location($row_trailers[id], '$row_trailers[trailer_name]')\">$row_trailers[trailer_name]</a>
     			
     			$res.="<li>
     					<span trailer_id='$row_trailers[id]' popup=\"Trailer: $row_trailers[trailer_name]<br>Location: $row_trailers[current_location]\">
     						<a href='javascript:void(0)' onclick=\"add_entry_truck_trailer('".date("Y-m-d", time())."',$row_trailers[id])\" style='color:#ABABAB; font-weight:normal;'>$row_trailers[trailer_name]</a>     						
     					</span> 
     					<a href='javascript:detach_trailer($row_trailers[attached_trailer_id],$row_trailers[driver_id])'>
     						<img src='".$new_style_path."red_circle.png' width='15' height='14' alt='red_circle'>
     					</a>
     					
     						".$use_attacher."
     					
     				</li>";
     				//<span class='attached_trailer_$row_trailers[attached_trailer_id]'>
     				//</span>
     			
     			//echo "<div class='trailer_entry_available'><span trailer_id='$row_trailers[id]' title=\"Trailer: $row_trailers[trailer_name]<br>Location: Unknown\">$row_trailers[trailer_name]</span></div>";
     		} 
     		else 
     		{
     			$array_pos = array_search($row_trailers['id'], $trailer_array);
     			mysqli_data_seek($data_trailers_used,$array_pos);
     			$row_trailers_used = mysqli_fetch_array($data_trailers_used);
     			
     			$use_attacher="<strong>$row_trailers[trailer_name]</strong>";
     			if($row_trailers['attached_trailer_id'])
     			{
     				$use_attacher="<strong>($row_trailers[name_driver_first] $row_trailers[name_driver_last])</strong>";
     				/*
     				<span class='attached_trailer_$row_trailers[attached_trailer_id]'>
     					<a href='javascript:detach_trailer($row_trailers[attached_trailer_id],$row_trailers[driver_id])'>
     						($row_trailers[name_driver_first] $row_trailers[name_driver_last])
     					</a>
     				</span>
     				*/
     			}
     			//".$warning_flag."
     			/*
     			$res.="<li>
     					<span trailer_id='$row_trailers[id]' popup=\"Trailer: $row_trailers[trailer_name]<br>$row_trailers_used[name_truck]<br>$row_trailers_used[location]<br>".date("l", strtotime($row_trailers_used['linedate']))."\">
     						$row_trailers[trailer_name]    						
     					</span> 
     					<a href='javascript:detach_trailer($row_trailers[attached_trailer_id],$row_trailers[driver_id])'>
     						<img src='".$new_style_path."red_circle.png' width='15' height='14' alt='red_circle'>
     					</a>
     					
     						".$use_attacher."
     					
     				</li>";
     				//<span class='attached_trailer_$row_trailers[attached_trailer_id]'>
     				//</span>
     			*/
     		}     		
     	}
		$res.=	"</ul>";		
		$res.="</div>";   
		
		return $res;
	}
	
			
     	
		
	function mrr_conard_component_list_trucks()
	{
		global $new_style_path;
		//global $defaultsarray;
				
		global $trucks_array;
		global $data_trucks;
		
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		$res="";
				
		$res.="<div class='left_box'>";
		$res.=	"<h3><span><a href='javascript:toggle_trucks()'>+ &nbsp; &nbsp; &nbsp; &nbsp;</a> Truck List</span> ".show_help('index.php','Truck List',$new_style_path,"question_mark.png")."</h3>";
		$res.=	"<ul class='left_sec'>";
		
		while($row_trucks = mysqli_fetch_array($data_trucks)) {
			
			$warning_flag="";	//mrr_get_equipment_maint_notice_warning('truck',$row_trucks['id']);				
			for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
			{
				if($mrr_maint_truck_arr[ $rr ] == $row_trucks['id'])		$warning_flag=trim($mrr_maint_truck_links[ $rr ]);
			}
			
			if(in_array($row_trucks['id'],$trucks_array)) 
			{
				
				$user_attacher="<strong>$row_trucks[name_truck]</strong>";
				if($row_trucks['attached_truck_id'])
				{						
					$user_attacher="<strong>($row_trucks[name_driver_first] $row_trucks[name_driver_last])</strong>";	
				}
				/*
				$res.="<li>
						<span>$row_trucks[name_truck] ".$warning_flag."</span> 
						<a href='javascript:detach_truck($row_trucks[attached_truck_id],$row_trucks[driver_id])'>
							<img src='".$new_style_path."red_circle.png' width='15' height='14' alt='red_circle'>
						</a>
						
							".$user_attacher."
							
						</li>";
						//<span class='attached_truck_$row_trucks[attached_truck_id]'>
						//</span>
				*/
			} 
			else 
			{				
				$namer="$row_trucks[name_driver_first] $row_trucks[name_driver_last]";
				$user_attacher="<strong></strong>";	
				if(trim($namer)!="")
				{
					$user_attacher="<strong>$namer</strong>";	
				}
				$res.="<li>
						<span><a href=\"javascript:add_entry_truck_id('".date("Y-m-d", time())."',$row_trucks[id])\" style='color:#ABABAB; font-weight:normal;'>$row_trucks[name_truck]</a> ".$warning_flag."</span> 
						<a href='javascript:detach_truck($row_trucks[attached_truck_id],$row_trucks[driver_id])'>
							<img src='".$new_style_path."red_circle.png' width='15' height='14' alt='red_circle'>
						</a>
						
							".$user_attacher."
							
						</li>";
						//<span class='attached_truck_$row_trucks[attached_truck_id]'>
						//</span>
				
			}
		}
		
		$res.=	"</ul>";
		
		$res.="</div>";
		
		return $res;
	}
	
	function mrr_conard_component_calendar($curmon,$curday,$curyear,$mon,$day,$year)
	{
		global $new_style_path;
		global $defaultsarray;
		$res="";
		
		if($defaultsarray['load_board_display_calendar'] ==0)			return $res;
		
		
		$dater="".$mon."/".$day."/".$year."";		
		$startdate=date("Y-m-d",strtotime($dater));
		$fullm=date("F",strtotime($startdate));
		$fulld=date("l",strtotime($startdate));
		//$wkday=date("w",strtotime($startdate));
		
		$dater2="".$mon."/01/".$year."";
		$startdate2=date("Y-m-d",strtotime($dater2));
		$wkday=date("w",strtotime($startdate2));
				
		for($i=0;$i < 43;$i++)
		{
			$box[$i]="&nbsp;";	
		}
		$cntr=1;
				
		for($i=$wkday;$i < 43;$i++)
		{
			$txt="";
			
			//$txt="<a href='index.php?mCheck=1&use_day=".$cntr."&use_mon=".$mon."&use_year=".$year."&cal_day=".$day."&cal_mon=".$mon."&cal_year=".$year."' style='border:0;'>".$cntr."</a>";
			$txt="<span class='mrr_link_like_on' onClick='mrr_get_fuel_surcharge_by_date(".$mon.",".$cntr.",".$year.");'>".$cntr."</span>";
			if( date("Ymd", strtotime("".$mon."/".$cntr."/".$year."")) > date("Ymd"))
			{	//greater than now...do not show this as an active "link"...		
				$txt="<span class='mrr_link_like_off'>".$cntr."</span>";
			}
			
			if($cntr>31)											$txt="&nbsp;";			
			if($cntr>30 && ($mon==4 || $mon==6 || $mon==9 || $mon==11))		$txt="&nbsp;";	
			if($cntr>29 && $mon==2 && $year%4==0)						$txt="&nbsp;";	
			if($cntr>28 && $mon==2)									$txt="&nbsp;";		
			$box[$i]=$txt;
			$cntr++;	
		}
		
		
		$res="";
		
		$next_month=($mon+1);
		$last_month=($mon-1);
		$next_year=$year;
		$last_year=$year;
		if($mon==1)
		{
			$last_month="12";	
			$last_year=($year-1);	
		}
		if($mon==12)
		{
			$next_month="1";
			$next_year=($year+1);			
		}
		
				
		$res.="<div id='calender'>";
		$res.=	"<div class='head'>";
		//$res.=		"<a href='#' class='plus'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=		"<div class='date_change'>";
		$res.=			"<a href='index.php?mCheck=1&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$day."&cal_mon=".$last_month."&cal_year=".$last_year."'><img src='".$new_style_path."left_changer.png' alt=''/></a>";
		$res.=			"<span>".$fullm." ".$year."</span>";
		$res.=			"<a href='index.php?mCheck=1&use_day=".$curday."&use_mon=".$curmon."&use_year=".$curyear."&cal_day=".$day."&cal_mon=".$next_month."&cal_year=".$next_year."'><img src='".$new_style_path."right_changer.png' alt=''/></a>";
		$res.=		"</div>";
		$res.=	"</div>";
		$res.=	"<div class='table_sec'>";
		$res.=		"<table width='100%' cellpadding='0' cellspacing='0'>";
		$res.=		"<tr>";
		$res.=			"<th class='none'>SUN</th>";
		$res.=			"<th>MON</th>";
		$res.=			"<th>TUE</th>";
		$res.=			"<th>WED</th>";
		$res.=			"<th>THU</th>";
		$res.=			"<th>FRI</th>";
		$res.=			"<th class='none'>SAT</th>";
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[0]."</td>";
		$res.=			"<td>".$box[1]."</td>";
		$res.=			"<td>".$box[2]."</td>";
		$res.=			"<td>".$box[3]."</td>";
		$res.=			"<td>".$box[4]."</td>";
		$res.=			"<td>".$box[5]."</td>";
		$res.=			"<td>".$box[6]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[7]."</td>";
		$res.=			"<td>".$box[8]."</td>";
		$res.=			"<td>".$box[9]."</td>";
		$res.=			"<td>".$box[10]."</td>";
		$res.=			"<td>".$box[11]."</td>";
		$res.=			"<td>".$box[12]."</td>";
		$res.=			"<td>".$box[13]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[14]."</td>";
		$res.=			"<td>".$box[15]."</td>";
		$res.=			"<td>".$box[16]."</td>";
		$res.=			"<td>".$box[17]."</td>";
		$res.=			"<td>".$box[18]."</td>";
		$res.=			"<td>".$box[19]."</td>";
		$res.=			"<td>".$box[20]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[21]."</td>";
		$res.=			"<td>".$box[22]."</td>";
		$res.=			"<td>".$box[23]."</td>";
		$res.=			"<td>".$box[24]."</td>";
		$res.=			"<td>".$box[25]."</td>";
		$res.=			"<td>".$box[26]."</td>";
		$res.=			"<td>".$box[27]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[28]."</td>";
		$res.=			"<td>".$box[29]."</td>";
		$res.=			"<td>".$box[30]."</td>";
		$res.=			"<td>".$box[31]."</td>";
		$res.=			"<td>".$box[32]."</td>";
		$res.=			"<td>".$box[33]."</td>";
		$res.=			"<td>".$box[34]."</td>";		
		$res.=		"</tr>";
		$res.=		"<tr>";
		$res.=			"<td>".$box[35]."</td>";
		$res.=			"<td>".$box[36]."</td>";
		$res.=			"<td>".$box[37]."</td>";
		$res.=			"<td>".$box[38]."</td>";
		$res.=			"<td>".$box[39]."</td>";
		$res.=			"<td>".$box[40]."</td>";
		$res.=			"<td>".$box[41]."</td>";		
		$res.=		"</tr>";
		$res.=		"</table>";
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
	} 	
	
	function mrr_conard_component_notes()
	{
		global $new_style_path;
		global $defaultsarray;
		
		$res="";
		
		if($defaultsarray['load_board_display_notes'] ==0)			return $res;
				
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar'>";
		$res.=		"<span class='notes'>Notes</span>";
		$res.=		"<a href='javascript:edit_note(0)'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=	"</div> ";
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul>";
		
		
		// check for any dispatches that have an invalid date
		$sql = "
			select *
			
			from trucks_log
			where linedate < '1970-01-01' 
				and deleted = 0
		";
		$data_invalid_date = simple_query($sql);
		
		if(mysqli_num_rows($data_invalid_date)) {
			
			$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>Dispatches with invalid dates</span></h3>";
			$res.=	"</li>";
			
			while($row_invalid_date = mysqli_fetch_array($data_invalid_date)) {
				
				$res.=	"<li>";
				$res.=		"<h3>
								<span>
									<a href=\"javascript:edit_entry_truck('',$row_invalid_date[id],0)\">$row_invalid_date[id]</a>
								</span>
								<a href=\"javascript:edit_entry_truck('',$row_invalid_date[id],$row_invalid_date[load_handler_id])\">$row_invalid_date[load_handler_id]</a>
							</h3>";				
				$res.=	"</li>";
			}			
		}
		// check for any stops that have an invalid completion date
		
		$vres=mrr_warning_of_improper_date_time_for_loads();
		if($vres['num'] > 0) 
		{		
			$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>".$vres['num']." Stop(s) with invalid completion dates have been located ...<a href='report_completion_date_errors.php' target='_blank'>View Report</a></span></h3>";
			$res.=	"</li>";						
		}
		
     	//check for any Visual Load Plus imported loads that need to be processed...
     	$sql_vlp = "
			select load_handler.* 
			from load_handler
			where deleted=0
				and vpl_imported>0
				and vpl_import_processed=0
			order by id desc
		";
		$data_vlp = simple_query($sql_vlp);
		$vlp_cntr=mysqli_num_rows($data_vlp);
		if($vlp_cntr>0) {
			
			$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>".$vlp_cntr." Imported Load(s) to Process ...<a href='conard_logistics_edi.php' target='_blank'>View Imported Loads</a></span></h3>";			
			$res.=	"</li>";					
		}
		
		
		//see if any trailers have been dropped more than once...
		
		$dropped_trailers=mrr_display_trailers_dropped_multi_times();
		if(trim($dropped_trailers['html2'])!="")
		{
			$res.=$dropped_trailers['html2'];
		}
		/**/	
		
		//regular notes...
     	$sql = "
     		select notes_general,
     			id
     		
     		from site_sections
     		where site_default = 1;
     	";
     	$data_notes = simple_query($sql);
     	$row_notes = mysqli_fetch_array($data_notes);
     		
     	
     	$sql = "
     		select notes.*,
     			users.username,
     			users.name_first,
     			users.name_last
     		
     		from notes,users
     		where notes.section_id = '$row_notes[id]'
     			and notes.deleted = 0
     			and notes.customer_id = 0
     			and notes.linedate = 0
     			and users.id=notes.user_id
     		order by notes.linedate_added
     	";
     	$data_notes_more = simple_query($sql);		// <b>trim($row_notes_more['username']) </b>
     	
     	if(trim($row_notes['notes_general'])=="")
     	{
     		$res.=	"<li>";
			$res.=		"<h3><span style='color:red;'>".$row_notes['notes_general']."</span></h3>
						<div id='truck_odometer_alert'></div>
						";
			$res.=	"</li>";	
     	}
     	while($row_notes_more = mysqli_fetch_array($data_notes_more)) 
     	{ 
			$res.=			"<li>";
			$res.=				"<h3><span>".date("m-d-y", strtotime($row_notes_more['linedate_added'])).": ".trim($row_notes_more['username'])."</span>";
			$res.=					"<a href='javascript:confirm_delete_note(".$row_notes_more['id'].")'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
			$res.=					"<a href='javascript:edit_note(".$row_notes_more['id'].")'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";
			$res.=				"</h3>";
			$res.=				"<p>".trim($row_notes_more['desc_long'])."</p>  ";
			$res.=			"</li>";			
		}
				
		$res.=		"</ul>";
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
		
	} 
	function mrr_conard_component_events()
	{
		global $new_style_path;
		global $defaultsarray;
		
		$result_limit = 10;
		$future_time = "30 day";	
		$res="";
		
		if($defaultsarray['load_board_display_events'] ==0)			return $res;
		
		$sql = "
			select linedate_birthday as linedate,				
				concat('Birthday: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>') as c_reason,
				0 as calendar_id
				
			from drivers 
			where DATE_FORMAT(linedate_birthday,'%m-%d') <= '".date("m-d", strtotime("+5 day", time()))."' 
				and DATE_FORMAT(linedate_birthday,'%m-%d')  >= '".date("m-d", strtotime("-1 day", time()))."' 				
				and deleted = 0
				and active = 1
			
			union
						
			select linedate_drugtest,
				concat('Physical due for: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_drugtest <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and linedate_drugtest > 0
				and deleted = 0
				and active = 1
			
			union 
			
			select linedate_license_expires,
				concat('License Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_license_expires > 0
				and linedate_license_expires <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				and active = 1
			
			union 
			
			select linedate_cov_expires,
				concat('COV Expires: <a href=\"admin_drivers.php?id=',id,'\" target=\"view_driver_',id,'\">', name_driver_first, ' ', name_driver_last,'</a>'),
				0
				
			from drivers
			where linedate_cov_expires > 0
				and linedate_cov_expires <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				and active = 1
							
			order by month(linedate), day(linedate)
			
			limit $result_limit
		";
		$data = simple_query($sql);
		$res="";
				
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar yellow'>";
		$res.=		"<span class='notes'>Events</span>";
		$res.=		"<a href='javascript:edit_event(0)'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=	"</div> ";
		
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul>";
		
		$test_date=date("Y-m-d")." 00:00:00";
						
		//build events calendar list...		
		while($row = mysqli_fetch_array($data)) 
		{
			$tag1="";
			$tag2="";
			if($row['linedate'] < $test_date)
			{
				$tag1="<span style='color:red;'><b>";
				$tag2="</b></span>";
				$row['c_reason']=str_replace("Expires","Expired",$row['c_reason']);	
			}
			$res.=	"<li>";
     		$res.=		"<h3>";
     		$res.=			"<span>".date("M, j", strtotime($row['linedate']))."</span>";
     		//$res.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     		//$res.=			"<a href='javascript:edit_event($row[calendar_id])'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";     			
     		$res.=		"</h3>";
     		$res.=		"<p>".$tag1."".trim($row['c_reason'])."".$tag2."</p> ";
     		$res.=	"</li>";
		}
		
		$res.=		"</ul>";
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
	} 
	function mrr_conard_component_timeoff()
	{
		global $new_style_path;
		global $defaultsarray;
		$res="";
		
		if($defaultsarray['load_board_display_timeoff'] ==0)			return $res;
		
		$result_limit = 10;
		$future_time = "30 day";	
		
		$sql2 = "
			select linedate_start as linedate,
				linedate_end as linedate_to,
				driver_id as driver,
				concat('Unavailable: <a href=\"admin_drivers.php?id=',drivers.id,'\" target=\"view_driver_',drivers.id,'\">', name_driver_first, ' ', name_driver_last,'</a> ',date_format(linedate_start, '%b %e'), ' - ', date_format(linedate_end, '%b %e'),': ',reason_unavailable) as c_reason,
				reason_unavailable as c_reason2,
				1 as from_calendar,
				drivers_unavailable.id as calendar_id,
				'none' as desc_long
				
			from drivers, drivers_unavailable
			where drivers.deleted = 0
				and drivers.active = 1
				and drivers_unavailable.deleted = 0
				and drivers.id = drivers_unavailable.driver_id
				and drivers_unavailable.linedate_end >= '".date("Y-m-d")."'
			
			union 
						
			select linedate,
				'0000-00-00 00:00:00',
				0,
				desc_short,
				'',
				0 as from_calendar,				
				id,
				desc_long
			
			from calendar
			where linedate > 0
				and linedate <= '".date("Y-m-d", strtotime($future_time, time()))."'
				and deleted = 0
				
			order by month(linedate), day(linedate)
			
			limit $result_limit
		";
		$data2 = simple_query($sql2);
		
		//Events <a href='javascript:edit_event(0)'><img src='images/add.gif' style='border:0'> Add new event</a>
		
		$res="";
				
		$res.="<div class='middle_container'>";
		$res.=	"";
		$res.=	"<div class='top_bar blue'>";
		$res.=		"<span class='notes'>Time Off</span>";
		$res.=		"";	//<a href='#'><img src='".$new_style_path."add.png' alt='add'></a>
		$res.=	"</div>";
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul>";
		
		
		//build time off ...add editing to Unavailable Driver on load board only.		
		while($row2 = mysqli_fetch_array($data2)) 
		{			
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
				else
				{
					$row2['c_reason']="<b>".$row2['c_reason'].":</b> ".trim($row2['desc_long']);
				}
											
				$res.=	"<li>";
				$res.=		"<h3><span>".date("M, j", strtotime($row2['linedate']))."</span>";
				$res.=			"<span onClick='mrr_edit_driver_unavailable(".$row2['calendar_id'].",".$row2['driver'].",\"".$tmp_from."\",\"".$tmp_to."\",\"".$tmp_reason."\");' style='margin-left:120px;'>
									<img src='".$new_style_path."blue_icon1.png' alt='add'>
								</span>";	
				$res.=			"<a href='javascript:".$use_java."'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";	
				$res.=		"</h3>";
				$res.=		"<p>".trim($row2['c_reason'])."</p> ";		
				$res.=	"</li>";
		}
		$res.=		"</ul>";		
		$res.=	"</div>";		
		$res.="</div>";
		
		return $res;
	} 	
	
	function mrr_conard_component_peoplenet_msgs()
	{
		global $new_style_path;
		global $defaultsarray;
		
		$res="";
		
		if($defaultsarray['load_board_display_peoplenet'] ==0)			return $res;
				
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar peoplenet'>";
		$res.=		"<span class='notes' title='Message Alerts for PeopleNet Tracking'>PN Messages</span>";
		$res.=		"<a href='peoplenet_messager.php' target='_blank'><img src='".$new_style_path."add.png' alt='add'></a>";
		$res.=	"</div> ";
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul id='new_pn_messages'>";
		
		$cntr=0;
		$sql="
			select truck_tracking_msg_history.*
			from truck_tracking_msg_history
			where user_id_read=0
				and no_response_needed='0'
				and msg_text NOT LIKE 'warning: %'
			order by linedate_created desc,
				truck_name asc,
				truck_id asc
		";
		$data=simple_query($sql);
		
		$offset_gmt=mrr_gmt_offset_val();	//mrr_get_server_time_offset();
     	//$offset_gmt=$offset_gmt * -1;
		
		$mydate=date("Y-m-d");		//today...
		
		while($row = mysqli_fetch_array($data)) 
		{
			$msg_id=$row['id'];
			$truck_id=$row['truck_id'];
			$truck_name=trim($row['truck_name']);
			$created=date("m/d/Y H:i:s",strtotime("-6 hours",strtotime($row['linedate_received'])));
			$recipient=trim($row['recipient_name']);
			$msg_txt=trim($row['msg_text']);
			
			$driver=mrr_find_pn_truck_drivers($truck_id,$mydate);
			
			$recipient=str_replace("!OIUser","<b>Dispatch</b>",$recipient);
						
			if(substr_count($msg_txt,"Warning: ")==0)
			{						
				$res.=	"<li>";
     			$res.=		"<h3>";
     			$res.=			"<span>".$created." --- <a href='peoplenet_messager.php?truck_id=".$truck_id."&reply_id=".$msg_id."'>".$truck_name."</a></span>";     	//admin_trucks.php?id=".$truck_id."
     			//$res.=			"<a href='javascript:delete_event($row[calendar_id])'><img src='".$new_style_path."red_icon1.png' alt='remove'></a>";
     			$res.=			"<a href='peoplenet_messager.php?truck_id=".$truck_id."&reply_id=".$msg_id."'><img src='".$new_style_path."blue_icon1.png' alt='add'></a>";		
     			$res.=		"</h3>";
     			$res.=		"<p>
     							Driver(s): ".$driver."<br>
     							<a href='peoplenet_messager.php?truck_id=".$truck_id."&reply_id=".$msg_id."'>Unread...Click here to read.</a> ".$recipient.": ".$msg_txt."
     							<span class='mrr_link_like_on' onClick='mrr_ignore_new_messages(".$msg_id.",".$_SESSION['user_id'].");' title='click to ignore, or if no response is needed.'>
     								<img src='/images/2012/red_icon1.png' alt='X' border='0' width='15' height='14'>
     							</span>
     							</p> ";
     			$res.=	"</li>";
     			
     			$cntr++;
     		}
		}		
		
		//key/legend for the PN and MSGS section... 
		$res.=	"<li>";
     	$res.=		"<h3>";
     	$res.=			"<br><span>PN and MSGS Legend</span>";    			
     	$res.=		"</h3>";
     	$res.=		"<p><b>PN</b>=PeopleNet Dispatch can be sent.</p> ";
     	$res.=		"<p><b>MSGS</b>=Message link for this truck.</p> ";
     	$res.=		"<p style='color:red; font-weight:bold;'>Dispatch has not been sent to truck.</p>";
     	$res.=		"<p style='color:orange; font-weight:bold;'>Updated since last send.</p>";
     	$res.=		"<p style='color:green; font-weight:bold;'>Dispatch sent to truck.</p>";
     	$res.=		"<p>Colors based on sent status. MSGS flag will always match PN link based on status of peoplenet dispatch.  MSGS is quick link to messages.</p> ";
     	$res.=		"<p>Hover on Load to see distance and current truck location.  <b>No Distance.</b> displays when dispatch has not been sent through PeopleNet system and no GPS coordinates have been calculated for the stops.  Send dispatch to fix this.</p> ";
     	$res.=	"</li>";		
		
		$res.=		"</ul>";
		$res.=	"</div>";		
		$res.="</div>";
		
		$cut_off_switch=0;	//
		if($cntr > 0 && $cut_off_switch==0)
		{
			$res.="
				<script type='text/javascript'>
					$.prompt('You have ".$cntr." unread items in the PN Messages.  Please check them.');
				</script>
			";	
		}
		
		
		//if($cntr == 0)		return "<div class='middle_container'>&nbsp;</div>";	//blank out this section...
		return $res;
	} 
	
	function mrr_conard_component_geofencing_msgs()
	{		
		global $new_style_path;
		global $defaultsarray;
		
		if($defaultsarray['peoplenet_geofencing_mph'] ==0 && $defaultsarray['peoplenet_geofencing_tolerance'] ==0)			return $res;
					
		$res.="<div class='middle_container'>";
		$res.=	"<div class='top_bar geofence'>";
		$res.=		"<span class='notes' title='Message for Geofence'>Geofence</span>";
		$res.=		"<a href='report_peoplenet_activity.php' target='_blank'><img src='".$new_style_path."add.png' alt='View'></a>";
		$res.=	"</div> ";
		$res.=	"<div class='middle_bar' style='max-height:300px; min-height:300px; overflow-x:hidden; overflow-y:scroll;'>";
		$res.=		"<ul id='new_pn_geofencing'>";
		
		$reporter=mrr_pull_all_active_geofencing_rows_alt1(1);	
		
		$res.=	$reporter;
					
		$res.=		"</ul>";
		$res.=	"</div>";		
		$res.="</div>";
		
		//if($cntr == 0)		return "<div class='middle_container'>&nbsp;</div>";	//blank out this section...
		return $res;
	} 
	
	
	
	function mrr_conard_component_display_company($mon,$day,$year,$name_company,$disp_type=0,$cal_mon,$cal_day,$cal_year)
	{
		
		global $new_style_path;
		global $defaultsarray;
          	
		global $driver_avail_array;		
		global $query_count;
		
		//added April 2012 so that Maint flags are all found in one group/set in one query instead of every point.	
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		
		$dater="".$mon."/".$day."/".$year."";
		$startdate=date("Y-m-d",strtotime($dater));
		$fullm=date("F",strtotime($startdate));
		$fulld=date("l",strtotime($startdate));
		
		//yesterday...previous day
		$prev_day=date("d",strtotime("-1 day", strtotime($dater)));
		$prev_mon=date("m",strtotime("-1 day", strtotime($dater)));
		$prev_year=date("Y",strtotime("-1 day", strtotime($dater)));
		$linker_prev="index.php?mCheck=1&use_day=".$prev_day."&use_mon=".$prev_mon."&use_year=".$prev_year."&cal_day=".$cal_day."&cal_mon=".$cal_mon."&cal_year=".$cal_year."";
		
		//tomorrow...next day
		$next_day=date("d",strtotime("+1 day", strtotime($dater)));
		$next_mon=date("m",strtotime("+1 day", strtotime($dater)));
		$next_year=date("Y",strtotime("+1 day", strtotime($dater)));
		$linker_next="index.php?mCheck=1&use_day=".$next_day."&use_mon=".$next_mon."&use_year=".$next_year."&cal_day=".$cal_day."&cal_mon=".$cal_mon."&cal_year=".$cal_year."";
				
		$id_array = Array();
		
		/*
		$sql = "
			select id,
				name_company
			
			from customers
			where separate_truck_section = 1
				and deleted = 0
				and active = 1
		";
		$data_truck_sections = simple_query($sql);
		
		while($row_truck_sections = mysqli_fetch_array($data_truck_sections)) {
			$id_array[] = $row_truck_sections['id'];
		}
		
		$id_list = implode(",",$id_array);
		*/	
		$sql = "
     		select *
     		
     		from drivers
     		where active = 1
     			and deleted = 0
     			and hide_available = 0
     			and id not in (
     							select driver_id
     							
     							from trucks_log 
     							where deleted = 0 
     								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
     								and dispatch_completed = 0
     						)
     
     			and id not in (
     							select driver2_id
     							
     							from trucks_log 
     							where deleted = 0 
     								and (trucks_log.driver_id = drivers.id or trucks_log.driver2_id = drivers.id) 
     								and dispatch_completed = 0
     						)
     
     			and id not in (
     							select driver_id
     							
     							from drivers_unavailable
     							where drivers_unavailable.deleted = 0
     								and drivers_unavailable.driver_id = drivers.id
     								and linedate_start <= '".date("Y-m-d",strtotime($startdate))." 00:00:00'
     								and linedate_end >= '".date("Y-m-d",strtotime($startdate))." 23:59:59'
     			)
     
     		order by name_driver_last, name_driver_first
     	";
     	$data_avail_drivers = simple_query($sql);
		
		$mrr_cntr_x=0;
		$mrr_cntr_y=0;
		$mrr_cntr_z=0;		
				
		$res="";
		$res1="";
		$res2="";
		$res3="";
		$res4="";
		$res5="";		
		
		$res.="<input type='hidden' name='use_mon' id='use_mon' value='".$mon."'>";
		$res.="<input type='hidden' name='use_day' id='use_day' value='".$day."'>";
		$res.="<input type='hidden' name='use_year' id='use_year' value='".$year."'>";
		$res.="<input type='hidden' name='cal_mon' id='cal_mon' value='".$cal_mon."'>";
		$res.="<input type='hidden' name='cal_day' id='cal_day' value='".$cal_day."'>";
		$res.="<input type='hidden' name='cal_year' id='cal_year' value='".$cal_year."'>";
				
		$res.="<div id='main-details'>";
		$res.="             <table width='100%' border='0' cellpadding='0' cellspacing='0' class='tbl-mrgn'>";
          $res.="                 <tr>";
          $res.="                     <td>";
          $res.="                         <table width='100%' border='0' cellpadding='0' cellspacing='0' class='detail-heading'>";
          $res.="                             <tr>";
          $res.="                                 <td width='325' class='fleft'>Trucks ".($name_company!="" ? "-".$name_company : "") ."</td>";
          $res.="                                 <td>";
          $res.="                                     <table width='100%' border='0' cellpadding='0' cellspacing='0'>";
          $res.="                                         <tr>";
          $res.="                                             <td><a href='".$linker_prev."' title='See Previous Day'><img src='".$new_style_path."left-arrow.png' alt='left-arrow'></a></td>";
          $res.="                                             <td class='txt-cntr'>".$fullm." ".(int)$day."</td>";
          $res.="                                             <td><img src='".$new_style_path."devider.png' alt='image'></td>";
          $res.="                                             <td class='txt-cntr'>".$fulld."</td>";
          $res.="                                             <td><a href='".$linker_next."' title='See next Day'><img src='".$new_style_path."right-aroe.png' alt='right-arrow'></a></td>";
          $res.="                                         </tr>";
          $res.="                                     </table>";
          $res.="                                 </td>";
          $res.="                                 <td width='325' class='fright'> 
          									<a href='/index.php?mCheck=1&mrr_calendar_mode=0' style='color:white; font-weight:bold;'>Small Calendar</a> 
          									&nbsp; 
          									<a href='/index.php?mCheck=1&mrr_calendar_mode=1' style='color:white; font-weight:bold;'>Full Calendar</a>          									 
          									<a href='javascript:new_load()'><img src='".$new_style_path."plus.png' alt='add'></a>
          								</td>";	//<a href='javascript:edit_note_date(0,$disp_type,'".date("Y-m-d", strtotime($startdate))."')'></a>
          $res.="                             </tr>"; 
          $res.="                         </table>";
          $res.="                     </td>";
          $res.="                 </tr>";
          $res.="                 <tr>";
          $res.="                     <td>";
          
          //Now create sections....for each part (drivers,loads,dropped trailers, etc.)
                    
          $res1.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res1.=	"<tr>";
		$res1.=		"<td class='miner-gray fleft' colspan='2'>AVAILABLE DRIVERS</td>";
		$res1.=		"<td class='miner-gray'>&nbsp;</td>";
		$res1.=		"<td class='miner-gray'>&nbsp;</td>";
		$res1.=		"<td class='miner-gray'>&nbsp;</td>";
		$res1.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res1.=	"</tr>";
		$res1.=	"<tr>";
          $res1.=		"<td nowrap class='dark-gray fleft' width='150'>Date</td>";
          $res1.=		"<td nowrap class='dark-gray'>Driver</td>";
          $res1.=		"<td nowrap class='dark-gray'>Location </td>";
          $res1.=		"<td nowrap class='dark-gray' width='125'>Truck No.</td>";
          $res1.=		"<td nowrap class='dark-gray' width='125'>Trailer No.</td>";
          $res1.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res1.=	"</tr>"; 
          
          $res2.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res2.=	"<tr>";
		$res2.=		"<td class='miner-gray fleft' colspan='2'>PREPLAN LOADS</td>";
		$res2.=		"<td class='miner-gray'>&nbsp;</td>";
		$res2.=		"<td class='miner-gray'>&nbsp;</td>";
		$res2.=		"<td class='miner-gray'>&nbsp;</td>";
		$res2.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res2.=	"</tr>";
		$res2.=	"<tr>";
          $res2.=		"<td nowrap class='dark-gray fleft' width='150'>Load</td>";
          $res2.=		"<td nowrap class='dark-gray'>Driver</td>";
          $res2.=		"<td nowrap class='dark-gray'>Company</td>";
          $res2.=		"<td nowrap class='dark-gray' width='125'>Origin</td>";
          $res2.=		"<td nowrap class='dark-gray' width='125'>Destination</td>";
          $res2.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res2.=	"</tr>"; 
          
          $res3.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res3.=	"<tr>";
		$res3.=		"<td class='miner-gray fleft' colspan='2'>LOADS</td>";
		$res3.=		"<td class='miner-gray'>&nbsp;</td>";
		$res3.=		"<td class='miner-gray'>&nbsp;</td>";
		$res3.=		"<td class='miner-gray'>&nbsp;</td>";
		$res3.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res3.=	"</tr>";
		$res3.=	"<tr>";
          $res3.=		"<td nowrap class='dark-gray fleft' width='150'>Load</td>";
          $res3.=		"<td nowrap class='dark-gray'>Driver</td>";
          $res3.=		"<td nowrap class='dark-gray'>Location </td>";
          $res3.=		"<td nowrap class='dark-gray' width='125'>Truck No.</td>";
          $res3.=		"<td nowrap class='dark-gray' width='125'>Trailer No.</td>";
          $res3.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res3.=	"</tr>"; 
                    
          $res4.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res4.=	"<tr>";
		$res4.=		"<td class='miner-gray fleft' colspan='2'>DROPPED TRAILERS</td>";
		$res4.=		"<td class='miner-gray'>&nbsp;</td>";
		$res4.=		"<td class='miner-gray'>&nbsp;</td>";
		$res4.=		"<td class='miner-gray'>&nbsp;</td>";
		$res4.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res4.=	"</tr>";
		$res4.=	"<tr>";
          $res4.=		"<td nowrap class='dark-gray fleft' width='150'>Link</td>";
          $res4.=		"<td nowrap class='dark-gray'>City</td>";
          $res4.=		"<td nowrap class='dark-gray'>State (Notes)</td>";
          $res4.=		"<td nowrap class='dark-gray' width='125'>Zip</td>";
          $res4.=		"<td nowrap class='dark-gray' width='125'>Trailer No.</td>";
          $res4.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res4.=	"</tr>"; 
          
          $res5.=" <table width='100%' border='0' cellpadding='0' cellspacing='0' class='grid-mrgn'>"; 
          $res5.=	"<tr>";
		$res5.=		"<td class='miner-gray fleft' colspan='2'>AVAILABLE DRIVERS</td>";
		$res5.=		"<td class='miner-gray'>&nbsp;</td>";
		$res5.=		"<td class='miner-gray'>&nbsp;</td>";
		$res5.=		"<td class='miner-gray'>&nbsp;</td>";
		$res5.=		"<td class='miner-gray  fright'>&nbsp;</td>";
		$res5.=	"</tr>";
		$res5.=	"<tr>";
          $res5.=		"<td nowrap class='dark-gray fleft' width='150'>Date</td>";
          $res5.=		"<td nowrap class='dark-gray'>Driver</td>";
          $res5.=		"<td nowrap class='dark-gray'>Location </td>";
          $res5.=		"<td nowrap class='dark-gray' width='125'>Truck No.</td>";
          $res5.=		"<td nowrap class='dark-gray' width='125'>Trailer No.</td>";
          $res5.=		"<td nowrap class='dark-gray fright' width='125'></td>";
          $res5.=	"</tr>"; 
          
			
		$sql_filter = "";
		$sql_id_filter = "";
		$sql_filter2 = "";
		$sql_id_filter2 = "";
		$sql_filter_drop_tl_cust = "";
		
		$mrr_adder="";
		$mrr_adder2="";
		if($name_company=="Dedicated Loads")
		{
			$mrr_adder=" and load_handler.dedicated_load='1'";
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1)=0";	
		}	
		else
		{
			$mrr_adder=" and load_handler.dedicated_load='0'";	
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1)=1";
		}	
		
		$cust_id_array = explode(",",$id_list);
		if($id_list != "") {
			$sql_filter = " and trucks_log.customer_id not in ($id_list) ";
			$sql_filter2 = " and load_handler.customer_id not in ($id_list) ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id not in ($id_list) ";
		}
		
		if($disp_type > 0) {
			$sql_id_filter = " and trucks_log.customer_id = '$disp_type' ";
			$sql_id_filter2 = " and load_handler.customer_id = '$disp_type' ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id = '$disp_type' ";
		}
		
		$sql = "
			select *
			
			from notes
			where deleted = 0
				and customer_id = '$disp_type'
				and linedate >= '".date("Y-m-d", $startdate)."'
				and linedate <= '".date("Y-m-d", strtotime("7 day", $startdate))."'
			order by linedate_added
		";
		$data_notes = simple_query($sql);
		while($row_notes = mysqli_fetch_array($data_notes)) {
			$note_day = date("j", strtotime($row_notes['linedate']));
			if(!isset($notes_day_array[$note_day])) $notes_day_array[$note_day] = Array();
			$notes_day_array[$note_day][] = $row_notes;
		}

						
          for($i=0;$i < 1;$i++) 
          {
               /* pull out all the trucks in the log to display */
               $sql = "
               select trailers.trailer_name,
               	trucks_log.location,
               	trucks_log.dropped_trailer,
               	trucks_log.load_handler_id,
               	trucks_log.driver2_id,
               	drivers.name_driver_first,
               	drivers.name_driver_last,
               	drivers.phone_cell,
               	trucks.name_truck,
               	trucks_log.id,
               	trailers.id as trailer_id,
               	trucks.id as truck_id,
               	trucks_log.linedate_updated,
               	trucks_log.linedate,
               	trucks_log.color,
               	trucks_log.has_load_flag,
               	trucks_log.dispatch_completed,
               	trucks_log.destination,
               	trucks_log.destination_state,
               	(select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) as dedicated_trailer,
               	load_handler_stops.shipper_name,
               	load_handler_stops.shipper_city,
               	load_handler_stops.shipper_state,
               	load_handler_stops.stop_type_id,
               	load_handler_stops.linedate_pickup_eta,
               	load_handler_stops.id as load_handler_stop_id,
               	load_handler_stops.linedate_completed,
               	load_handler.special_instructions,
               	(select max(id) as id from load_handler lh where lh.preplan = 1 and lh.preplan_driver_id = trucks_log.driver_id and lh.deleted = 0) as driver_preplanned,
               	(select count(*) from trucks_log_notes where deleted = 0 and truck_log_id = trucks_log.id) as note_count
               
               from trucks_log
               	left join drivers on drivers.id = trucks_log.driver_id
               	left join trucks on trucks.id = trucks_log.truck_id
               	left join trailers on trailers.id = trucks_log.trailer_id
               	left join load_handler_stops on load_handler_stops.trucks_log_id = trucks_log.id and load_handler_stops.deleted = 0
               	left join load_handler on load_handler.id = trucks_log.load_handler_id
               where trucks_log.linedate = '".date("Y-m-d", strtotime( $startdate))."'
               	and trucks_log.deleted = 0 
               	and dispatch_completed = 0
               	".$mrr_adder."																			
               	$sql_filter
               	$sql_id_filter
               	
               order by trucks_log.dropped_trailer, trucks.name_truck, load_handler_stops.load_handler_id, load_handler_stops.linedate_pickup_eta
               ";	//".$mrr_adder2."
               //if($_SERVER['REMOTE_ADDR'] == '50.76.161.186') d($sql);
               $data_log = simple_query($sql);
               
               $sql = "
               select load_handler.*,
               	customers.name_company,
               	drivers.name_driver_last,
               	drivers.name_driver_first
               
               from load_handler
               	left join customers on customers.id = load_handler.customer_id
               	left join drivers on drivers.id = load_handler.preplan_driver_id
               where load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime($startdate))."'
               	and load_handler.linedate_pickup_eta < '".date("Y-m-d", strtotime($startdate))." 23:59:59'
               	and load_handler.deleted = 0
               	and (
               		load_handler.load_available = 1
               		or 
               		(select count(*) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id) = 0
               	)
               	".$mrr_adder."	
               	$sql_filter2
               	$sql_id_filter2
               	
               order by load_handler.preplan, load_handler.linedate_pickup_eta, load_handler.id
               ";
               $data_available_loads = simple_query($sql);
                              
               $extra_class = '';
               if(date("Y-m-d", strtotime($startdate)) == date("Y-m-d", time())) 
               	$extra_class = 'calendar_today';
               
               //echo "<td align='left' class='truck_drop $extra_class' valign='top' nowrap linedate='".date("Y-m-d", strtotime($startdate))."'>";
               
               
               if(date("Y-m-d") == date("Y-m-d", strtotime($startdate))) 
               {               
                    //$res.=	"<tr><td class='miner-gray' colspan='6'><b>MSG: Dates: ".date("Y-m-d")."==".date("Y-m-d", strtotime($startdate)).". COUNT=(".count($driver_avail_array).")</b></td></tr>";
                    
                    // see if we have any available drivers on this day
                    $available_driver_count = 0;
                    foreach($driver_avail_array as $darray) 
                    {
                    	//$res.=	"<tr><td class='miner-gray' colspan='6'><b>MSG: Driver=".$darray['driver_id'].".</b></td></tr>";
                    	// get a list of pre-planed loads available for this driver
                    	$sql = "
                    		select load_handler.*,
                    			customers.name_company
                    		
                    		from load_handler, customers
                    		where load_handler.preplan = 1
                    			and customers.id = load_handler.customer_id
                    			and load_handler.deleted = 0
                    			".$mrr_adder."
                    			and load_handler.preplan_driver_id = '".sql_friendly($darray['driver_id'])."'
                    	";
                    	$data_preplan = simple_query($sql);
                    	
                    	//$res.=	"<tr><td class='miner-gray' colspan='6'><b>MSG: Company=".$name_company." Type=".$disp_type." and COUNT=(".count($darray).").</b></td></tr>";
                    	
                    	if(count($darray) > 1 && (($disp_type > 0 && $darray['customer_id'] == $disp_type) || ($disp_type == 0 && !in_array($darray['customer_id'], $cust_id_array)))) 
                    	{
                    		//if(date("Y-m-d", strtotime($darray['linedate_completed'])) == date("Y-m-d", strtotime("+$i days", $startdate))) {
                    		
                    		// get any attached trucks/trailers
                    		$sql = "
                    			select attached_truck_id,
                    				attached_trailer_id,
                    				trucks.name_truck,
                    				trailers.trailer_name
                    				
                    			from drivers
                    				left join trucks on trucks.id = drivers.attached_truck_id
                    				left join trailers on trailers.id = drivers.attached_trailer_id
                    			where drivers.id = '".sql_friendly($darray['driver_id'])."'
                    		";
                    		$data_attached = simple_query($sql);
                    		$row_attached = mysqli_fetch_array($data_attached);
                    		
                    		$last_load_dedicated=mrr_get_last_driver_load_dedicated($darray['driver_id']);
                    		
                    		//$res.=	"<tr><td class='miner-gray' colspan='6'><b>MSG: Company=".$name_company." Last Load=".$last_load_dedicated.".</b></td></tr>";
                    			
                    		
                    		// && $last_load_dedicated==0
                    		if(($name_company!="Dedicated Loads" && $last_load_dedicated==0) || ($name_company=="Dedicated Loads" && $last_load_dedicated==1) )
                    		{    // 	$name_company!="Dedicated Loads"
                    			
                    			$available_driver_count++;
                    			
                    			$truck_block="";
                    			$trailer_block="";
                    			$res1a="";
                    			$res1b="";
                    			
                    			$mrr_classy="gray";
                              	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                              	
                              	//get truck and trailer data for display in two modes...
                    			if($row_attached['attached_truck_id']) 
                    			{
                    				
                    				$warning_flag="";	//mrr_get_equipment_maint_notice_warning('truck',$row_attached['attached_truck_id']);		
                    				for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
                    				{
                    					if($mrr_maint_truck_arr[ $rr ] == $row_attached['attached_truck_id'])		
                    						$warning_flag=trim($mrr_maint_truck_links[ $rr ]);
                    				}
                    				/*
                    				echo "
                    					<div class='attached_truck_$row_attached[attached_truck_id]' 
                    								truck_name=\"$row_attached[name_truck]\" 
                    								driver_name=\"".$darray['name_driver_first'].' '.$darray['name_driver_last']."\">
                    						
                    					</div>
                    					<div style='clear:both'></div>
                    				";
                    				*/
                    				$res1a.=	"<tr onMouseOver='mrr_show_section(\"drivers\",$darray[driver_id])' onMouseOut='mrr_hide_section(\"drivers\",$darray[driver_id])' class='mrr_all_drivers mrr_drivers_$darray[driver_id]'>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'><a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'>[detach]</a> $row_attached[name_truck]".$warning_flag."</td>";
                                   	$res1a.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1a.=		"<td class='".$mrr_classy." fright'>
                                   					<img src='images/truck_info.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_truck_history($row_attached[attached_truck_id])\" alt='Truck History' title='Truck History'>
                                   					<a href='search_site.php?search_term=".$row_attached['name_truck']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg' width='18' height='18' border='0'></a>
                                   					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg' width='18' height='18' border='0'></a>
                                   					<a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'><img alt='Remove' src='".$new_style_path."grid-minus.jpg' width='18' height='18' border='0'></a>
                                   				</td>";
                                   	$res1a.=	"</tr>";
                                   	
                                   	$truck_block="<a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'>[detach]</a> $row_attached[name_truck]".$warning_flag."";
                    			}
                    			if($row_attached['attached_trailer_id']) 
                    			{
                    				
                    				$warning_flag="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_attached['attached_trailer_id']);	
                    				for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                    				{
                    					if($mrr_maint_trailer_arr[ $rr ] == $row_attached['attached_trailer_id'])		$warning_flag=trim($mrr_maint_trailer_links[ $rr ]);
                    				}
                    				/*
                    				echo "
                    					<div class='attached_trailer_$row_attached[attached_trailer_id]' 
                    								trailer_name=\"$row_attached[trailer_name]\" 
                    								driver_name=\"".$darray['name_driver_first'].' '.$darray['name_driver_last']."\">    													
                    					</div>
                    					<div style='clear:both'></div>
                    				";
                    				*/
                    				$res1b.=	"<tr onMouseOver='mrr_show_section(\"drivers\",$darray[driver_id])' onMouseOut='mrr_hide_section(\"drivers\",$darray[driver_id])' class='mrr_all_drivers mrr_drivers_$darray[driver_id]'>";
                                   	$res1b.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1b.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1b.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1b.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1b.=		"<td class='".$mrr_classy."'><a href='javascript:void(0)' onclick='detach_trailer($row_attached[attached_trailer_id],$darray[driver_id])'>[detach]</a> $row_attached[trailer_name] ".$warning_flag."</td>";
                                   	$res1b.=		"<td class='".$mrr_classy." fright'>
                                   					<a href='search_site.php?search_term=".$row_attached['trailer_name']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg' width='18' height='18' border='0'></a>
                                   					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg' width='18' height='18' border='0'></a>
                                   					<a href='javascript:void(0)' onclick='detach_trailer($row_attached[attached_trailer_id],$darray[driver_id])'><img alt='Remove' src='".$new_style_path."grid-minus.jpg' width='18' height='18' border='0'></a>
                                   				</td>";
                                   	$res1b.=	"</tr>";
                                   	
                                   	$trailer_block="<a href='javascript:void(0)' onclick='detach_trailer($row_attached[attached_trailer_id],$darray[driver_id])'>[detach]</a> $row_attached[trailer_name] ".$warning_flag."";
                    			}
                    			$use_namer=str_replace("'","", $darray['name_driver_first'].' '.$darray['name_driver_last']);
                    			
                              	$res1.=	"<tr onMouseOver='mrr_show_section(\"drivers\",$darray[driver_id])' onMouseOut='mrr_hide_section(\"drivers\",$darray[driver_id])'>";
                              	$res1.=		"<td class='".$mrr_classy."'>".date("n/d", strtotime($darray['linedate_completed']))." <a href=\"javascript:has_load_toggle($darray[driver_id])\">(Available)</a> </td>";
                              	$res1.=		"<td class='".$mrr_classy."'>
                              					<a href=\"javascript:remove_available_driver($darray[driver_id],'".str_replace("'","", $darray['name_driver_first'].' '.$darray['name_driver_last'])."')\">[x]</a>
                              					<a href=\"javascript:edit_driver_notes($darray[driver_id])\">$darray[name_driver_first] $darray[name_driver_last]</a>
                              				</td>";
                              	$res1.=		"<td class='".$mrr_classy."'>($darray[last_city])</td>";
                              	$res1.=		"<td class='".$mrr_classy."'>".$truck_block."</td>";
                              	$res1.=		"<td class='".$mrr_classy."'>".$trailer_block."</td>";
                              	$res1.=		"<td class='".$mrr_classy." fright'>
                              					<a href='search_site.php?search_term=".$use_namer."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg' width='18' height='18' border='0'></a>
                              					<a href='javascript:edit_driver_notes($darray[driver_id]);'><img alt='Edit' src='".$new_style_path."grid-wright.jpg' width='18' height='18' border='0'></a>
                              					<a href='javascript:remove_available_driver($darray[driver_id],\"".$use_namer."\");'><img alt='Remove' src='".$new_style_path."grid-minus.jpg' width='18' height='18' border='0'></a>
                              				</td>";
                              	$res1.=	"</tr>";
                              	$res1.=	$res1a;
                              	$res1.=	$res1b;
                              	// class='hover_for_details_drivers' style='background-color:#6b9cff;font-weight:bold;color:white' id='available_driver_holder_$darray[driver_id]'
                              	// title='Dedicated=".$last_load_dedicated."'
                    			//<img src='images/driver_small.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_driver_history($darray[driver_id])\" alt='Driver History' title='Driver History'>
                    			//<img src='images/inventory.png' id='driver_has_load_$darray[driver_id]' style='width:16px;height:16px;float:left;margin-left:5px;".(isset($darray['driver_has_load']) && $darray['driver_has_load'] ? "" : "display:none")."' alt='Driver Has Load' title='Driver Has Load'>
                    			//<img src='images/note.png' id='driver_available_notes_$darray[driver_id]' onclick='edit_driver_notes($darray[driver_id])'style='cursor:pointer;width:16px;height:16px;float:left;margin-left:5px;".(isset($darray['available_notes']) && $darray['available_notes'] != ''? "" : "display:none")."' alt='Notes' title='Notes'>
                    			
                    			//<div class='available_driver_details' driver_id='$darray[driver_id]'>
                    			
                    			
                    			while($row_preplan = mysqli_fetch_array($data_preplan)) 
                    			{     											
                    				$res1.=	"<tr onMouseOver='mrr_show_section(\"drivers\",$darray[driver_id])' onMouseOut='mrr_hide_section(\"drivers\",$darray[driver_id])' class='mrr_all_drivers mrr_drivers_$darray[driver_id]'>";
                                   	$res1.=		"<td class='".$mrr_classy."'></td>";
                                   	$res1.=		"<td class='".$mrr_classy."'><a href='javascript:edit_entry_truck(0,0,$row_preplan[id])'>$row_preplan[id]</a></td>";
                                   	$res1.=		"<td class='".$mrr_classy."'>$row_preplan[name_company]</td>";
                                   	$res1.=		"<td class='".$mrr_classy."'>$row_preplan[origin_city], $row_preplan[origin_state] ".date("H:i", strtotime($row_preplan['linedate_pickup_eta']))."</td>";
                                   	$res1.=		"<td class='".$mrr_classy."'>$row_preplan[dest_city], $row_preplan[dest_state]  ".date("H:i", strtotime($row_preplan['linedate_dropoff_eta']))."</td>";
                                   	$res1.=		"<td class='".$mrr_classy." fright'>
                                   					<a href='search_site.php?search_term=".$row_preplan['name_company']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg' width='18' height='18' border='0'></a>
                                   					<a href='javascript:edit_entry_truck(0,0,$row_preplan[id])'><img alt='Edit' src='".$new_style_path."grid-wright.jpg' width='18' height='18' border='0'></a>
                                   					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg' width='18' height='18' border='0'></a>
                                   				</td>";
                                   	$res1.=	"</tr>";
                    			}
                    			//</div>						
                    			
                    			$mrr_cntr_z++;
               			
               			}//end IF NOT DEDICATED LOAD check
               	
               		}
               	}
               	
               }	//end if (date check for today to show available drivers
               

                              
               $use_day = date("d", strtotime($startdate));
               if(isset($notes_day_array[$use_day])) 
               {
                    foreach($notes_day_array[$use_day] as $note_entry) 
                    {									
                    	// show notes for the day							
                    	$res2.=	"<tr>";		// class='note_entry'
                    	$res2.=		"<td class='miner-gray fleft'>NOTICE:</td>";
                    	$res2.=		"<td class='miner-gray' colspan='4'>
                    					<div note_id='$note_entry[id]' onclick='edit_note_date($note_entry[id],0,0)' class='note_entry_inside'>
                    						".trim_string($note_entry['desc_long'], 45)."
                    					</div>
                    				</td>";
                    	$res2.=		"<td class='miner-gray  fright'>&nbsp;</td>";
                    	$res2.=	"</tr>";
                    	
                         $mrr_classy="gray";
                         if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                         $res2.=	"<tr>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                         $res2.=		"<td class='".$mrr_classy."  fright'>&nbsp;</td>";
                         $res2.=	"</tr>";
                         $mrr_cntr_z++;
                    }
               }
                             
               $loop_count = 0;
               $lh_used_id = array();
               $preplan_count = 0;
               $regular_count = 0;
               while($row_available = mysqli_fetch_array($data_available_loads)) 
               {
                    if(!in_array($row_available['id'],$lh_used_id)) 
                    {                    	
                    	if($row_available['preplan'] == '1') 
                    	{
                    		// preplan
                    		$preplan_count++;
                    		if($preplan_count == 1) 
                    		{
                    			//preplan
                    		}
                    	} 
                    	else 
                    	{
                    		// regular available
                    		$regular_count++;
                    		if($regular_count == 1) 
                    		{
                    			//available
                    		}
                    	}
                    	
                    	$lh_used_id[] = $row_available['id'];
                    	// get a list of all the stops for this load
                    	$sql = "
                    		select *
                    		
                    		from load_handler_stops
                    		where load_handler_id = '".sql_friendly($row_available['id'])."'
                    			and deleted = 0
                    		order by linedate_pickup_eta
                    	";
                    	$data_avail_stops = simple_query($sql);
                    	
                    	$mrr_classy="gray";
                    	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                    	
                    	$stop_count = 0;
                    	$stop_var = "";
                    	while($row_avail_stops = mysqli_fetch_array($data_avail_stops)) 
                    	{
                    		$stop_count++;
                    		$loc_holder = "$row_avail_stops[shipper_city], $row_avail_stops[shipper_state]";
                    		if($stop_count == 1) 
                    		{
                    			$loc_start = $loc_holder;
                    		}
                    		
                    		$stop_var.=	"<tr onMouseOver='mrr_show_section(\"preplan\",".$row_available['id'].")' onMouseOut='mrr_hide_section(\"preplan\",".$row_available['id'].")' class='mrr_all_preplan mrr_preplan_".$row_available['id']."'>";
                         	$stop_var.=		"<td class='".$mrr_classy."'></td>";
                         	$stop_var.=		"<td class='".$mrr_classy."'></td>";
                         	$stop_var.=		"<td class='".$mrr_classy."'>".($row_avail_stops['linedate_pickup_eta'] > 0 ? date("M j, H:i", strtotime($row_avail_stops['linedate_pickup_eta'])) : "")."</td>";
                         	$stop_var.=		"<td class='".$mrr_classy."'>(".($row_avail_stops['stop_type_id'] == 1 ? "S" : "C").") ".$row_avail_stops['shipper_name'].": $row_avail_stops[shipper_city], $row_avail_stops[shipper_state]</td>";
                         	$stop_var.=		"<td class='".$mrr_classy."'></td>";
                         	$stop_var.=		"<td class='".$mrr_classy." fright'>
                         					<a href='search_site.php?search_term=".$row_available['id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                         					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                         					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                         				</td>";
                         	$stop_var.=	"</tr>";										
                    		
                    	}
                    	$loc_end = $loc_holder;
                    	
                    	//<div class='hover_for_details ".($row_available['preplan'] > 0 ? "preplan_load_entry" : "available_load_entry")."'>
                    	
                    	$res2.=	"<tr onMouseOver='mrr_show_section(\"preplan\",".$row_available['id'].")' onMouseOut='mrr_hide_section(\"preplan\",".$row_available['id'].")'>";
                    	$res2.=		"<td class='".$mrr_classy."'>
                    					<a href='manage_load.php?load_id=$row_available[id]' log_id='$row_available[id]' target='view_load_".$row_available['id']."'>
                    						($row_available[id])
                    					</a>                    					
                    				</td>";
                    	$res2.=		"<td class='".$mrr_classy."'>
                    					<a href='manage_load.php?load_id=$row_available[id]' log_id='$row_available[id]' target='view_load_".$row_available['id']."'>"
                    					.($row_available['preplan'] > 0 ? "$row_available[name_driver_first] $row_available[name_driver_last] " : "")."
                    					</a>
                    				</td>";
                    	$res2.=		"<td class='".$mrr_classy."'>$row_available[name_company]</td>";
                    	$res2.=		"<td class='".$mrr_classy."'>$loc_start</td>";
                    	$res2.=		"<td class='".$mrr_classy."'>$loc_end</td>";
                    	$res2.=		"<td class='".$mrr_classy." fright'>
                    					<a href='search_site.php?search_term=".$row_available['id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                    					<a href='manage_load.php?load_id=$row_available[id]' target='view_load_".$row_available['id']."'><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                    					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                    				</td>";
                    	$res2.=	"</tr>";
                    	$res2.=	$stop_var;	//<div class='load_stop_details' log_id='$row_available[id]'>$stop_var</div>
                    	$mrr_cntr_z++;
                    }
                    
               
               }	//end while ($row_available)
               
               $last_truck_log_id = 0;
               $loop_count = 0;
               $dropped_trailer_count = 0;
               while($row_log = mysqli_fetch_array($data_log)) 
               {
                    $loop_count++;
                    $last_truck_log_id = $row_log['id'];
                    
                    
                    $date_updated = strtotime(date("m/d/Y", strtotime($row_log['linedate_updated'])));
                    $date_current = strtotime(date("m/d/Y", time()));
                    
                    $extra_class = '';
                    $trailer_alert = false;
                    if($date_updated < $date_current || $date_updated == '') 
                    {
                    	// hasn't been updated today, show an 'alert' background color
                    	// if this is a weekend or a monday, allow the days to go back a ways
                    }
                    if(strtotime($row_log['linedate']) < strtotime(date("Y-m-d", time()))) 
                    {
                    	$extra_class = 'update_alert';
                    	$trailer_alert = true;
                    }
                    
                    if(!$trailer_alert && $row_log['dropped_trailer']) 
                    {
                    	$use_background_color = ";background-color:#33ff00";
                    } 
                    elseif($row_log['color'] != '' && !$trailer_alert && $row_log['color'] != '#f8f3e4') 
                    {
                    	$use_background_color = ";background-color:$row_log[color]";
                    } 
                    else 
                    {
                    	$use_background_color = '';
                    }
                    
                    if($row_log['location'] != '') 
                    {
                    	$use_location = $row_log['location'];
                    } 
                    else 
                    {
                    	$use_location = "$row_log[destination], $row_log[destination_state]";
                    }
                    
                    //(select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) as dedicated_trailer,
                    
                    if($row_log['dropped_trailer']) 
                    {
                    		//<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:300px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'></div>
                    		//<span truck_id='$row_log[truck_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'></span>
                    		
                    		$mrr_classy="gray";
                         	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                         	$res3.=	"<tr>";
                         	$res3.=		"<td class='".$mrr_classy."'>
                         					<a style='margin-right:10px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">TL DR</a>
                    					</td>";
                         	$res3.=		"<td class='".$mrr_classy."'></td>";
                         	$res3.=		"<td class='".$mrr_classy."'>".trim_string($use_location,45)."</td>";
                         	$res3.=		"<td class='".$mrr_classy."'></td>";
                         	$res3.=		"<td class='".$mrr_classy."'>$row_log[trailer_name]</td>";
                         	$res3.=		"<td class='".$mrr_classy." fright'>
                         					<a href='search_site.php?search_term=".$row_log['id']." target='_blank''><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                         					<a href='javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])'><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                         					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                         				</td>";
                         	$res3.=	"</tr>";
                         	$mrr_cntr_z++;
                    } 
                    else 
                    {
                    	$has_load_flag = 0;
                    	$has_preplan = 0;
                    	if($row_log['dispatch_completed']) 
                    	{
                    		$use_image = "images/good.png";
                    	} 
                    	elseif ($row_log['driver_preplanned']) 
                    	{
                    		$use_image = "images/inventory.png";
                    		$has_preplan = $row_log['driver_preplanned'];
                    	} 
                    	elseif ($row_log['has_load_flag']) 
                    	{
                    		$use_image = "images/inventory.png";
                    		$has_load_flag = 1;
                    	} 
                    	else 
                    	{
                    		$use_image = "images/note.png";
                    	}
                    	
                    	$warning_flag1="";	//mrr_get_equipment_maint_notice_warning('truck',$row_log['truck_id']);		
                    	for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
                    	{
                    		if($mrr_maint_truck_arr[ $rr ] == $row_log['truck_id'])		$warning_flag1=trim($mrr_maint_truck_links[ $rr ]);
                    	}
                    	
                    	$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_log['trailer_id']);
                    	for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                    	{
                    		if($mrr_maint_trailer_arr[ $rr ] == $row_log['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                    	}
                    	
                    	if($loop_count == 1) {
                    		// show any loads						
                    		//
                    	}
                    	
                    	/*									
                    	<div class='hover_for_details truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:480px;".
                    		($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'></div>
                    	*/
                    	//<span truck_id='$row_log[truck_id]' load_handler_id='$row_log[load_handler_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'></span>
                    	$mrr_classy="gray";
                    	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                    	$res3.=	"<tr onMouseOver='mrr_show_section(\"loads\",$row_log[load_handler_id])' onMouseOut='mrr_hide_section(\"loads\",$row_log[load_handler_id])'>";
                    	$res3.=		"<td class='".$mrr_classy."'>
                    					<a href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">
                    						($row_log[load_handler_id])  
                    					</a>                    					
                    					<img log_id='$row_log[id]' class='note_image' preplan_load_id='$has_preplan' has_load_flag='$has_load_flag' src='$use_image' onclick='add_note($row_log[id])' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;".($row_log['note_count'] > 0 || $row_log['special_instructions'] != '' ? "border:1px red solid;" : "")."''>
                    					<div id='note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;width:425px;background-color:#eeeeee;padding:5px'></div>
                    					".($row_log['load_handler_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/menu_system16.png' style='cursor:pointer;float:left' onclick=\"edit_entry_truck('',$row_log[id],0)\" alt='Edit Dispatch' title='Edit Dispatch'></div>" : "")."
                    					".($row_log['truck_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/truck_info.png' style='cursor:pointer;float:left' onclick=\"view_truck_history($row_log[truck_id])\" alt='Truck History' title='Truck History'></div>" : "")."
                    				</td>";
                    	$res3.=		"<td class='".$mrr_classy."'>
                    					<a href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">
                    						 $row_log[name_driver_last], $row_log[name_driver_first]
                    					</a>
                    				</td>";
                    	$res3.=		"<td class='".$mrr_classy."'>".trim_string($use_location,45)."</td>";
                    	$res3.=		"<td class='".$mrr_classy."'><b>$row_log[name_truck]".$warning_flag1."</b></td>";
                    	$res3.=		"<td class='".$mrr_classy."'>".$warning_flag2."$row_log[trailer_name]</td>";
                    	$res3.=		"<td class='".$mrr_classy." fright'>
                    					<a href='search_site.php?search_term=".$row_log['id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                    					<a href='javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])' log_id='$row_log[id]'><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                    					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                    				</td>";
                    	$res3.=	"</tr>";                    	
                    									
                    	//<div class='load_stop_details' log_id='$row_log[id]'>
                    	$res3.=	"<tr onMouseOver='mrr_show_section(\"loads\",$row_log[load_handler_id])' onMouseOut='mrr_hide_section(\"loads\",$row_log[load_handler_id])' class='mrr_all_loads mrr_loads_$row_log[load_handler_id]'>";
                    	$res3.=		"<td class='".$mrr_classy."'></td>";
                    	$res3.=		"<td class='".$mrr_classy."'>
                    					".($row_log['driver2_id'] > 0 ? "(TEAM DRIVER)" : "")."
                    					<span id='phone_cell_display_$row_log[truck_id]'>
                    						<input style='background-color:transparent;border:0;width:90px;height:10px;text-align:right;cursor:pointer' readonly value=\"$row_log[phone_cell]\" onclick=\"$(this).select()\">
                    					</span>
                    				</td>";
                    	$res3.=		"<td class='".$mrr_classy."'></td>";
                    	$res3.=		"<td class='".$mrr_classy."'></td>";
                    	$res3.=		"<td class='".$mrr_classy."'></td>";
                    	$res3.=		"<td class='".$mrr_classy." fright'>
                    					<a href='search_site.php?search_term=".$row_log['load_handler_id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                    					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                    					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                    				</td>";
                    	$res3.=	"</tr>";
                    	
                    	$counter_sub = 0;
                    	mysqli_data_seek($data_log,$loop_count-1);
                    	while($row_log_sub = mysqli_fetch_array($data_log)) 
                    	{
                    		$counter_sub++;
                    		//echo "($row_log[load_handler_id] | $row_log_sub[load_handler_id] | $row_log_sub[load_handler_stop_id] | loop_count: $loop_count | counter: $counter_sub)";
                    		if($row_log['id'] != $row_log_sub['id'] || $row_log_sub['id'] == 0 || $row_log_sub['id'] == '') 
                    		{               			
                    			if($counter_sub > 1) $counter_sub--;
                    			
                    			$loop_count = $loop_count + $counter_sub - 1;
                    			
                    			@mysqli_data_seek($data_log,$loop_count);
                    			break;
                    		} 
                    		else 
                    		{
                    			
                    			$dt_completer="";
                    			if($row_log_sub['linedate_completed'] > 0) 
                    			{
                    				$dt_completer="<span style='color:#888888'>".($row_log_sub['linedate_completed'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_completed'])) : "")."&nbsp;&nbsp;&nbsp</span>";
                    			} 
                    			else 
                    			{
                    				$dt_completer="&nbsp;&nbsp; <a href='javascript:update_stop_complete($row_log_sub[load_handler_stop_id],".$row_log_sub['stop_type_id'].")'>".
                    											($row_log_sub['linedate_pickup_eta'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_pickup_eta'])) : "")."&nbsp;&nbsp"
                    										."</a>";
                    			}											
                    			//<div style='float:left".($row_log_sub['linedate_completed'] > 0 ? ";color:#888888" : "")."'></div>
                    			$res3.=	"<tr onMouseOver='mrr_show_section(\"loads\",$row_log[load_handler_id])' onMouseOut='mrr_hide_section(\"loads\",$row_log[load_handler_id])' class='mrr_all_loads mrr_loads_$row_log[load_handler_id]'>";
                              	$res3.=		"<td class='".$mrr_classy."'></td>";
                              	$res3.=		"<td class='".$mrr_classy."'>".$dt_completer."</td>";
                              	$res3.=		"<td class='".$mrr_classy."'>(".($row_log_sub['stop_type_id'] == 1 ? "S" : "C").") ($row_log_sub[load_handler_stop_id]) ".$row_log_sub['shipper_name'].": $row_log_sub[shipper_city], $row_log_sub[shipper_state]</td>";
                              	$res3.=		"<td class='".$mrr_classy."'></td>";
                              	$res3.=		"<td class='".$mrr_classy."'></td>";
                              	$res3.=		"<td class='".$mrr_classy." fright'>
                              					<a href='search_site.php?search_term=".$row_log_sub['load_handler_id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                              					<a href=''><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                              					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                              				</td>";
                              	$res3.=	"</tr>";
                    		}
                    	}
                    	//@mysqli_data_seek($data_log, $loop_count-1);
                    	
                    	$mrr_cntr_z++;
                    } // end of dropped trailer if
                    
               } // end of while statement
               
               
               if(date("m/d/Y", strtotime($startdate)) == date("m/d/Y", time())   ) 
               {
                    // today's date                                                            && $name_company!="Dedicated Loads"
                    
                    // show any dropped trailer
                    //and trailers_dropped.linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
                    $mrr_message="";
                    $mrr_add_dedicated=' and trailers_dropped.dedicated_trailer=0';
                    if($name_company=="Dedicated Loads")
                    {
                    	$mrr_add_dedicated=' and trailers_dropped.dedicated_trailer>0';
                    	$mrr_message="Dedicated... ";
                    }								
                    $sql = "
                    	select trailers_dropped.*,
                    		trailers.trailer_name,
                    		customers.name_company
                    	
                    	from trailers_dropped, trailers, customers
                    	where trailers.id = trailers_dropped.trailer_id
                    		and trailers_dropped.customer_id=customers.id										
                    		and trailers_dropped.drop_completed = 0
                    		and trailers_dropped.deleted = 0
                    		".$mrr_add_dedicated."
                    		
                    		$sql_filter_drop_tl_cust	
                    	order by name_company asc,trailer_name asc							
                    ";
                    $last_stater="";
                    $data_trailers_dropped = simple_query($sql);
                    $dropped_trailer_count = 0;
                    while($row_trailer_dropped = mysqli_fetch_array($data_trailers_dropped)) 
                    {
                    	
                    	$dropped_trailer_count++;
                    	// dropped trailers are always shown last. If this is the first dropped trailer, then show a general toggle for them
                    	if($dropped_trailer_count == 1) 
                    	{
                    		/*
                    		echo "
                    			<div class='load_board_section_header' onclick='mrr_toggle_dropped_trailer_setter(".$_SESSION['toggle_dropped_trailer_on'].");' style='cursor:pointer;color:#c4ffc4'>
                    				Toggle Dropped Trailers
                    			</div>
                    		";			
                    		*/
                    		
                    		/*
                    		if($_SESSION['toggle_dropped_trailer_on']==1)
                    		{
                    			echo "<script type='text/javascript'>
                    					toggle_dropped_trailers();
                    					</script>";	
                    			$_SESSION['toggle_dropped_trailer_on']=1;
                    		}
                    		*/
                    	}
                    	
                    	$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_trailer_dropped['trailer_id']);
                    	for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                    	{
                    		if($mrr_maint_trailer_arr[ $rr ] == $row_trailer_dropped['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                    	}
                    	
                    	if($last_stater!="$row_trailer_dropped[name_company]")
                    	{
                    		$last_stater="$row_trailer_dropped[name_company]";	
                    		
                    		$mrr_classy="gray";
                    		if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                    		$res4.=	"<tr>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy."'>&nbsp;</td>";
                    		$res4.=		"<td class='".$mrr_classy." fright'>&nbsp;</td>";
                    		$res4.=	"</tr>";
                    		$mrr_cntr_z++;
                    	}
                    	
                    	//<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style=';min-width:300px;'></div>
                    	//<span trailer_id='$row_trailer_dropped[trailer_id]' class='entry_info' dropped_trailer_id='$row_trailer_dropped[id]'></span>
                    	$mrr_classy="gray";
                    	if($mrr_cntr_z%2==1)		$mrr_classy="gray-simple";
                    	$res4.=	"<tr>";
                    	$res4.=		"<td class='".$mrr_classy."'>
                    					<a href=\"javascript:edit_dropped_trailer($row_trailer_dropped[id])\">TL DR</a>
                    				</td>";
                    	$res4.=		"<td class='".$mrr_classy."'>$row_trailer_dropped[location_city]</td>";
                    	$res4.=		"<td class='".$mrr_classy."'>$row_trailer_dropped[location_state]</td>";
                    	$res4.=		"<td class='".$mrr_classy."'>$row_trailer_dropped[location_zip]</td>";
                    	$res4.=		"<td class='".$mrr_classy."'>".$mrr_message."".$warning_flag2."$row_trailer_dropped[trailer_name]</td>";
                    	$res4.=		"<td class='".$mrr_classy." fright'>
                    					<a href='search_site.php?search_term=".$row_trailer_dropped['trailer_id']."' target='_blank'><img alt='Find' src='".$new_style_path."grid-search.jpg'></a>
                    					<a href='javascript:edit_dropped_trailer($row_trailer_dropped[id])'><img alt='Edit' src='".$new_style_path."grid-wright.jpg'></a>
                    					<a href=''><img alt='Remove' src='".$new_style_path."grid-minus.jpg'></a>
                    				</td>";
                    	$res4.=	"</tr>";
                    	$mrr_cntr_z++;
                    }// end while loop
               }    //end date check if for today                   
              
          }
          $spacer="";	//"<tr><td>&nbsp;</td></tr>";		//<tr><td colspan='6'>&nbsp;</td></tr>
          
		$res1.="</table>".$spacer."";    	
		$res2.="</table>".$spacer."";
		$res3.="</table>".$spacer."";
		$res4.="</table>".$spacer."";
		$res5.="</table>".$spacer."";
		
		$res.=$res1;		//available drivers
		$res.=$res2; 		//preplan loads
		$res.=$res3; 		//loads
		$res.=$res4; 		//dropped trailers
		//$res.=$res5; 		//
          
          $res.="                     </td>";
          $res.="                 </tr>";
          $res.="                 <tr><td><img src='".$new_style_path."bottom.png' alt='image'></td></tr>";
          $res.="             </table>";
		$res.="</div>";
		
		return $res;
	}
	
	if(isset($_GET['use_day']))		$_POST['use_day']=$_GET['use_day'];
	if(isset($_GET['use_mon']))		$_POST['use_mon']=$_GET['use_mon'];
	if(isset($_GET['use_year']))		$_POST['use_year']=$_GET['use_year'];
	
	if(isset($_GET['cal_day']))		$_POST['cal_day']=$_GET['cal_day'];
	if(isset($_GET['cal_mon']))		$_POST['cal_mon']=$_GET['cal_mon'];
	if(isset($_GET['cal_year']))		$_POST['cal_year']=$_GET['cal_year'];
	
	if(!isset($_POST['use_day']))		$_POST['use_day']=date("d");
	if(!isset($_POST['use_mon']))		$_POST['use_mon']=date("m");
	if(!isset($_POST['use_year']))	$_POST['use_year']=date("Y");
	
	if(!isset($_POST['cal_day']))		$_POST['cal_day']=date("d");
	if(!isset($_POST['cal_mon']))		$_POST['cal_mon']=date("m");
	if(!isset($_POST['cal_year']))	$_POST['cal_year']=date("Y");
	
	//$mrr_debug_timer=time() - $mrr_debug_time1;
	//die('<br>Loaded in '.$mrr_debug_timer.' Seconds.<br>');
	
?>
<SCRIPT Language="Javascript">
	//setTimeout("location.reload();", (600 * 1000));		//ten minutes...600 seconds...1000=1 second
	$().ready(function() {
		mrr_hide_all_section();
		/*
		setInterval( function() 
          {
              	mrr_fetch_sent_messages_home(); 		//new_pn_messages
              	
              	mrr_fetch_geofencing_report_home();	//new_pn_geofencing
              	         	
          },(2000 * 60));		//1 minutes...1000=1 second
          */
	});	
	
	function mrr_ignore_new_messages(id,userid)
	{			
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_pn_update_incoming_message_ignore",
			   data: {
			   		"msg_id":id,
			   		"user_id":userid
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrr_fetch_sent_messages_home(); 		//new_pn_messages
			   }
		});
	}
	
	function mrr_fetch_geofencing_report_home()
	{			
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_truck_tracking_geofencing_report",
			   data: {
			   		
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrrtab=$(xml).find("mrrTab").text();
					
					$('#new_pn_geofencing').html(mrrtab);
			   }
		});
	}
	function mrr_fetch_sent_messages_home()
	{		
		mrr_trucker=0;
		if($('#truck_id').val() > 0)
		{
			mrr_trucker=$('#truck_id').val();	
		}
		
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_truck_tracking_messages_sent",
			   data: {"truck_id": mrr_trucker,
			   		"date_from": $('#date_from').val(),
			   		"date_to":  $('#date_to').val(),
			   		"truck_name": $('#truck_name').val(),
			   		"archived":0,
			   		"limit": 0,
			   		"dsiplay_mode":1
			   		},
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
					mrrtab=$(xml).find("mrrUnread").text();
					
					$('#new_pn_messages').html(mrrtab);
			   }
		});
	}
	
	function mrr_show_section(section,id)
	{
		$('.mrr_'+section+'_'+id+'').show();
	}
	function mrr_hide_section(section,id)
	{
		$('.mrr_'+section+'_'+id+'').hide();	
	}
	function mrr_hide_all_section()
	{	
		$('.mrr_all_drivers').hide();	
		$('.mrr_all_preplan').hide();
		$('.mrr_all_loads').hide();
	}	
</SCRIPT>
	<div class="wrapper">
          <div class="page">
               <div class="container_left_part">
               	<? 
               	
               	
               	
				//if($_SERVER['REMOTE_ADDR'] != '50.76.161.186') { 
				?>	
               	<? $page_timer_array[] = show_page_time(); ?>
					<?= mrr_conard_component_time() ?>
					<?= mrr_conard_component_fuel() ?>
					<?= mrr_conard_component_tool() ?>
					<?= mrr_conard_component_truck_movement() ?>
					<?= mrr_conard_component_load_available() ?>
					<?= mrr_conard_component_list_trailers() ?>
					<?= mrr_conard_component_list_trucks() ?>
				<? 
				//} 
				
				
				
				?>	
				<? $page_timer_array[] = show_page_time(); ?>
			</div>
               <div class="container_right_part">
               	<? 
				//if($_SERVER['REMOTE_ADDR'] != '50.76.161.186') { 
				?>	
					<?= mrr_conard_component_calendar($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year']) ?>
					
					<?= mrr_conard_component_geofencing_msgs() ?>
					<?= mrr_conard_component_timeoff() ?>
					<?= mrr_conard_component_events() ?>
					<?= mrr_conard_component_notes() ?>
				
					<?= mrr_conard_component_peoplenet_msgs() ?>				
				<? 
				
				//} 
				?>	
				<? $page_timer_array[] = show_page_time(); ?>
				
				<div style='clear:both'></div>		
				<?
				/*                                             //
				if($mrr_alt_mode==0)
				{
					$mrr_big_boards=truck_section_mrr_alt();
					echo $mrr_big_boards;
				}
				else
				{					
					$mrr_big_board_normal=mrr_conard_component_display_company($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],"",0,$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year']);
					$mrr_big_board_dedicated=mrr_conard_component_display_company($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],"Dedicated Loads",0,$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year']); 	
					
					echo $mrr_big_board_normal;
					echo $mrr_big_board_dedicated;
				}
				*/
				?>
				<? 
				//if($_SERVER['REMOTE_ADDR'] != '50.76.161.186') { 
					// && $_SERVER['REMOTE_ADDR'] != '50.76.161.186'
				?>		
				<?
				if($mrr_calendar_mode==0)
				{	//small calendar
					
					$mrr_big_boards=truck_section_mrr_alt();
					echo "".$mrr_big_boards;				//Small Calendar<br>					
				}
				elseif($mrr_calendar_mode==1)
				{	//full calendar
					$mrr_big_boards=truck_section_mrr_alt();
					echo "".$mrr_big_boards;				//Full Calendar<br>						
				}
				elseif($mrr_calendar_mode==2)
				{	//new board
					$mrr_big_board_normal=mrr_conard_component_display_company($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],"",0,$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year']);
					$mrr_big_board_dedicated=mrr_conard_component_display_company($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],"Dedicated Loads",0,$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year']); 	
					
					echo "".$mrr_big_board_normal;		//New Board<br>
					echo $mrr_big_board_dedicated;	
				}
				?>				
				<? 
				//} 
				?>				
				<? $page_timer_array[] = show_page_time(); ?>
			</div>
		</div>
	</div>	

<?
	
	$mrr_debug_timer=time() - $mrr_debug_time1;
	//die('<br>Loaded in '.$mrr_debug_timer.' Seconds.<br>');
	echo '<br>Loaded in '.$mrr_debug_timer.' Seconds.<br>';
	
	//new functions as of 12/07/2011.........................new functions do not use SEPARATED_BY_
	function truck_section_mrr_alt() {
		global $mrr_calendar_mode;
		
		$mrr_micro_seconds_partA=time();
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION 1a");		//make tracking log entry
    		
		$id_array = Array();
		
		/*
		$sql = "
			select id,
				name_company
			
			from customers
			where separate_truck_section = 1
				and deleted = 0
				and active = 1
		";
		$data_truck_sections = simple_query($sql);
		
		while($row_truck_sections = mysqli_fetch_array($data_truck_sections)) {
			$id_array[] = $row_truck_sections['id'];
		}
		
		$id_list = implode(",",$id_array);
		*/
		
		
		
		/* display the main truck section ----------------------------------------------------------------------------------------------------------- *****  */
		//if($_SERVER['REMOTE_ADDR'] != '50.76.161.186')	
		
		truck_section_display_mrr_alt(0, $id_list, "",1);
		
		
		truck_section_display_mrr_alt(0, $id_list, "Dedicated Loads",2);
		
		/* now, display all the additional truck sections (per customers) --------------------------------------------------------------------------- *****  */
		/*
		@mysqli_data_seek($data_truck_sections,0);
		while($row_truck_sections = mysqli_fetch_array($data_truck_sections)) {
			truck_section_display_mrr_alt($row_truck_sections['id'],"", $row_truck_sections['name_company']);
		}  
		*/
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION 2a");				//make tracking log entry
		
	}
	
	function truck_section_display_mrr_alt($disp_type, $id_list, $name_company,$comp_num=0) {
		
		
		
		global $mrr_calendar_mode;
		$show_full_calendar=$mrr_calendar_mode;
		$mrr_micro_seconds_partA=time();
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION Display 1a");		//make tracking log entry
		    	
		?>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width='100%'>				
				<span class='standard16' style='color:black;'><b>Trucks <? if($name_company != "") echo " - $name_company"; ?></b></span><?= show_help('index.php','Calendar') ?> &nbsp;&nbsp;&nbsp;&nbsp;				
				
				<a href='javascript:toggle_details()'>Toggle Details</a> &nbsp;&nbsp;&nbsp;&nbsp;				
				<?				
				if($mrr_calendar_mode==0)
				{
					echo "
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(false,true);' id='show_small_calendar'>SMALL CALENDAR</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(true,true);' id='show_full_calendar'>Show Full Calendar</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='/index.php?mCheck=1&mrr_calendar_mode=2' id='show_new_calendar'>Show New Board</a>&nbsp;&nbsp;&nbsp;&nbsp;  
					";	
				}
				elseif($mrr_calendar_mode==1)
				{
					echo "
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(false,true);' id='show_small_calendar'>Show Small Calendar</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(true,true);' id='show_full_calendar'>FULL CALENDAR</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='/index.php?mCheck=1&mrr_calendar_mode=2' id='show_new_calendar'>Show New Board</a>&nbsp;&nbsp;&nbsp;&nbsp;  
					";	
				}
				else
				{
					echo "
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(false,true);' id='show_small_calendar'>Show Small Calendar</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='javascript:toggle_calendar_full(true,true);' id='show_full_calendar'>Show Full Calendar</a>&nbsp;&nbsp;&nbsp;&nbsp;  				
						<a class='calendar_toggle_links' href='/index.php?mCheck=1&mrr_calendar_mode=2' id='show_new_calendar'>Show New Board</a>&nbsp;&nbsp;&nbsp;&nbsp;  
					";		
				}				
				?>
			</td>
		</tr>
		<tr>
			<td align='center' width='100%'>
				<?
						$startdate = date("w", time());
						$startdate = strtotime("-7 day", strtotime("-$startdate days"));
						$current_day = date("j");
						$cur_timer=time();
						$mrr_diff=(int)(($cur_timer - $startdate)/86400);		//find number of days....864000=10 days....86400=1 day											
				?>
				<div style='border-bottom:0px black solid;'>
				<table class='standard12 mrr_whiter' cellspacing='0' cellpadding='0' width='100%'>
				<? 
					$all_col_headers="";
					$all_col_info="";
						
						
						if(isset($_GET['disp_type'])) {
							// show the load board as a list (function is in the load_board_include.php file)
							truck_section_display_sub_list_mrr_alt($disp_type, $id_list, $startdate, $name_company);
						} else {
							// normal load board display type
							
							for($week=0;$week < 4;$week++) {
								$use_startdate = strtotime("+$week week", $startdate);
								$use_day = date("j", $use_startdate);
															
								$mrr_diff=(int)(($cur_timer - $use_startdate)/86400);		//find number of days....
								
								$mres=truck_section_display_sub_mrr_alt($disp_type, $id_list, $use_startdate, $name_company, $mrr_diff, $week,$comp_num);	
								
								$all_col_headers.=$mres['column_headers'];
								$all_col_info.=$mres['column_info'];
								
								/*								
								if($show_full_calendar>0)
								{
									truck_section_display_sub_mrr_alt($disp_type, $id_list, $use_startdate, $name_company, $mrr_diff);	
								}
								elseif($mrr_diff>=0 && $mrr_diff<=7)
								{
									if()
									{
										truck_section_display_sub_mrr_alt($disp_type, $id_list, $use_startdate, $name_company, $mrr_diff);	
									}
								}
								*/
								
								/*
								// code to only show the current week
								if(($current_day >= $use_day && $current_day <= ($use_day + 7)) || $show_full_calendar>0) 
								{	//
									truck_section_display_sub_mrr_alt($disp_type, $id_list, $use_startdate, $name_company);
								}
								*/
							}
						}
					
					echo "<tr>".$all_col_headers."</tr>";
					echo "<tr>".$all_col_info."</tr>";											
					?>
				<!--
				<tr>
					<td colspan='7' style='border-top:1px black solid'>
					</td>
				</tr>
				-->
				</table>
				</div>
				<br>
			</td>
		</tr>
		<?
	
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION Display 2a");		//make tracking log entry    
	}
	
	function truck_section_display_sub_mrr_alt($disp_type, $id_list, $startdate, $name_company, $mrr_days_from_start=0,$week=0,$comp_num=0) {
		

		
		global $driver_avail_array;
		global $query_count;
		
		//added April 2012 so that Maint flags are all found in one group/set in one query instead of every point.	
		global $mrr_maint_truck_cntr;
		global $mrr_maint_truck_arr;
		global $mrr_maint_truck_links;	
	
		global $mrr_maint_trailer_cntr;
		global $mrr_maint_trailer_arr;
		global $mrr_maint_trailer_links;
		
		
		$mrr_micro_seconds_partA=time();
		mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION Display Sub 1b");		//make tracking log entry
		
		$sql_filter = "";
		$sql_id_filter = "";
		$sql_filter2 = "";
		$sql_id_filter2 = "";
		$sql_filter_drop_tl_cust = "";
		
		$mrr_adder="";
		$mrr_adder2="";
		if($name_company=="Dedicated Loads")
		{
			$mrr_adder=" and load_handler.dedicated_load='1'";
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1)=0";	
		}	
		else
		{
			$mrr_adder=" and load_handler.dedicated_load='0'";	
			$mrr_adder2=" and (select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1)=1";
		}	
		
		$cust_id_array = explode(",",$id_list);
		if($id_list != "") {
			$sql_filter = " and trucks_log.customer_id not in ($id_list) ";
			$sql_filter2 = " and load_handler.customer_id not in ($id_list) ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id not in ($id_list) ";
		}
		
		if($disp_type > 0) {
			$sql_id_filter = " and trucks_log.customer_id = '$disp_type' ";
			$sql_id_filter2 = " and load_handler.customer_id = '$disp_type' ";
			$sql_filter_drop_tl_cust = " and trailers_dropped.customer_id = '$disp_type' ";
		}
		
		$sql = "
			select *
			
			from notes
			where deleted = 0
				and customer_id = '$disp_type'
				and linedate >= '".date("Y-m-d", $startdate)."'
				and linedate <= '".date("Y-m-d", strtotime("7 day", $startdate))."'
			order by linedate
		";
		$data_notes = simple_query($sql);
		while($row_notes = mysqli_fetch_array($data_notes)) {
			$note_day = date("j", strtotime($row_notes['linedate']));
			if(!isset($notes_day_array[$note_day])) $notes_day_array[$note_day] = Array();
			$notes_day_array[$note_day][] = $row_notes;
		}
		
		$classer=" class='mrr_full_calendar'";
		if($mrr_days_from_start>=0 && $mrr_days_from_start<=6)
		{
			$classer=" class='mrr_full_calendar mrr_small_calendar'";	
		}
		
		$date_headers="";
		$info_columns="";
		$today_checker=date("Ymd");
		
		
			
		//$date_headers.="<tr".$classer.">";
		
			/* find the closest sunday to today */
		
			for($i=0;$i < 7;$i++) {
				if($i < 5) {
					$use_border = 'border_no_right';
				} else {
					$use_border = 'border_solid';
				}
				
				$optional_day_flag = "";
				if($i == 0 || $i == 6 || date("W", strtotime("+$i days", $startdate)) != date("W", time())) 
				{
					$optional_day_flag = "optional_day";
					
				}
				if($week==0)	$optional_day_flag .= " optional_week";
				
				$no_header_flag="";
				$no_info_flag="";				
				
				$no_date_disp=date("Ymd", strtotime("+$i days", $startdate));
				
				if($no_date_disp < $today_checker)	
				{
					$no_header_flag=" mrr_hdr_".$comp_num."_id='".$no_date_disp."'";		//create the attribute for this column header... to select and remove.
					$no_info_flag=" date_in_past_header mrr_date_".$no_date_disp."";	
				}
				
				//<b><a href=\"javascript:add_entry_truck('".date("Y-m-d", strtotime("+$i days", $startdate))."')\">". date("l", strtotime("+$i days", $startdate)) ."</a></b>
				$date_headers.= "
					<td class='$use_border calendar_header $optional_day_flag".$no_info_flag."' nowrap align='center'".$no_header_flag.">
						<b>". date("l", strtotime("+$i days", $startdate)) ."</b>
						<br>
						<b>". date("n-j-Y", strtotime("+$i days", $startdate)) ."</b>
						<!---
						<br>
						<b><a href=\"javascript:edit_note_date(0,$disp_type,'".date("Y-m-d", strtotime("+$i days", $startdate))."')\">Note</a></b>
						--->
					</td>
				";
			}						
						
		//$date_headers.="</tr>";
		//$info_columns.="<tr".$classer.">";
					
						$dater=date("Y-m-d");
						$mrr_tlat[0]="0";
						$mrr_tlon[0]="0";
						$mrr_tloc[0]="";	
						$mrr_tarr[0]=0;
						$mrr_tcnt=0;
						$mrr_tdates[0]="";
						
						//if(date("Y-m-d", strtotime("+$i days", $startdate)) == $dater)
						//{
						
						/*
						if($mrr_days_from_start>=0 && $mrr_days_from_start<=6)
						{	
							$mloc_res=mrr_find_all_pn_truck_time_tracking_last_local($dater);
							//if($_SERVER['REMOTE_ADDR'] == '50.76.161.186') die("page time 1: ".show_page_time());
							$mrr_tlat=$mloc_res['latitude'];
     						$mrr_tlon=$mloc_res['longitude'];
     						$mrr_tloc=$mloc_res['location'];	
     						$mrr_tarr=$mloc_res['trucks'];
     						$mrr_tcnt=$mloc_res['num'];
     						$mrr_tdates=$mloc_res['date'];
     					}
     					*/					
						
						for($i=0;$i<7;$i++) 
						{
    							//}	
							$mrr_tot_disp_cntr=0;
							
							/* pull out all the trucks in the log to display */
							$sql = "
								select trailers.trailer_name,
									trucks_log.location,
									trucks_log.dropped_trailer,
									trucks_log.load_handler_id,
									trucks_log.driver2_id,
									trucks_log.customer_id,
									(select name_company from customers where customers.id=trucks_log.customer_id) as customer_namer,
									drivers.name_driver_first,
									drivers.name_driver_last,
									drivers.jit_driver_flag,
									drivers.phone_cell,
									trucks.name_truck,
									trucks.peoplenet_tracking,
									trucks_log.id,
									trailers.id as trailer_id,
									trucks.id as truck_id,
									trucks_log.miles,
									trucks_log.miles_deadhead,
									trucks_log.linedate_updated,
									trucks_log.linedate,
									trucks_log.color,
									trucks_log.has_load_flag,
									trucks_log.dispatch_completed,
									trucks_log.origin,
									trucks_log.origin_state,
									trucks_log.destination,
									trucks_log.destination_state,
									(select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) as dedicated_trailer,
									load_handler_stops.shipper_name,
									load_handler_stops.shipper_city,
									load_handler_stops.shipper_state,
									load_handler_stops.stop_type_id,
									load_handler_stops.linedate_pickup_eta,
									load_handler_stops.id as load_handler_stop_id,
									load_handler_stops.linedate_completed,
									load_handler_stops.appointment_window,
									load_handler_stops.linedate_appt_window_start,
									load_handler_stops.linedate_appt_window_end,									
									(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as mrr_start_trailer,
									(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as mrr_end_trailer,
									load_handler.special_instructions,
									load_handler.load_number,
									load_handler.deadhead_miles,
									load_handler.estimated_miles,
									load_handler.pickup_number,
									load_handler.linedate_pickup_eta as mrr_linedate_eta,
									(select max(id) as id from load_handler lh where lh.preplan = 1 and lh.preplan_driver_id = trucks_log.driver_id and lh.deleted = 0) as driver_preplanned,
									(select count(*) from trucks_log_notes where deleted = 0 and truck_log_id = trucks_log.id) as note_count
								
								from trucks_log
									left join drivers on drivers.id = trucks_log.driver_id
									left join trucks on trucks.id = trucks_log.truck_id
									left join trailers on trailers.id = trucks_log.trailer_id
									left join load_handler_stops on load_handler_stops.trucks_log_id = trucks_log.id and load_handler_stops.deleted = 0
									left join load_handler on load_handler.id = trucks_log.load_handler_id
								where trucks_log.linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
									and trucks_log.deleted = 0 
									and dispatch_completed = 0
									".$mrr_adder."																			
									$sql_filter
									$sql_id_filter
									
								order by trucks_log.dropped_trailer, drivers.name_driver_last, drivers.name_driver_first, trucks.name_truck, load_handler_stops.load_handler_id, load_handler_stops.linedate_pickup_eta
							";	//".$mrr_adder2."
							//if($_SERVER['REMOTE_ADDR'] == '50.76.161.186') d($sql);
							$data_log = simple_query($sql);
							
							$mrr_tot_disp_cntr+= mysqli_num_rows($data_log);
							
							$sql = "
								select load_handler.*,
									customers.name_company,
									drivers.name_driver_last,
									drivers.name_driver_first,
									drivers.jit_driver_flag
								
								from load_handler
									left join customers on customers.id = load_handler.customer_id
									left join drivers on drivers.id = load_handler.preplan_driver_id
								where load_handler.linedate_pickup_eta >= '".date("Y-m-d", strtotime("+$i days", $startdate))."'
									and load_handler.linedate_pickup_eta < '".date("Y-m-d 23:59:59", strtotime("+$i days", $startdate))."'
									and load_handler.deleted = 0
									and (
										load_handler.load_available = 1
										or 
										(select count(*) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id) = 0
									)
									".$mrr_adder."	
									$sql_filter2
									$sql_id_filter2
									
								order by load_handler.preplan, drivers.name_driver_last, drivers.name_driver_first, load_handler.linedate_pickup_eta, load_handler.id
							";							
							//if($_SERVER['REMOTE_ADDR'] == '69.137.72.167') echo("<p>$sql");
							$data_available_loads = simple_query($sql);
							
							$mrr_tot_disp_cntr+= mysqli_num_rows($data_available_loads);
							
							if($i < 5) {
								$use_border = 'border_left';
							} else {
								$use_border = 'border_both_sides';
							}
							
							$optional_day_flag = "";
							if($i == 0 || $i == 6 || date("W", strtotime("+$i days", $startdate)) != date("W", time())) $optional_day_flag = "optional_day";
							
							
							if($week==0 && $mrr_tot_disp_cntr == 0)
							{
								$optional_day_flag .= " optional_week_day";	
							}
							
							$no_header_flag="";	
							$no_info_flag="";
				
							$no_date_disp=date("Ymd", strtotime("+$i days", $startdate));
				
							if($no_date_disp < $today_checker  && $mrr_tot_disp_cntr == 0)
							{
								$no_header_flag=" mrr_col_".$comp_num."_id='".$no_date_disp."'";		//create the attribute for this column info... to select and remove.
								$no_info_flag=" date_in_past_info mrr_date_".$no_date_disp." ";									
							}
							
							$extra_class = '';
							if(date("Y-m-d", strtotime("+$i days", $startdate)) == date("Y-m-d", time())) $extra_class = 'calendar_today';
							$info_columns.="<td align='left' class='$use_border calendar_text truck_drop $extra_class $optional_day_flag".$no_info_flag."' valign='top' nowrap linedate='".date("Y-m-d", strtotime("+$i days", $startdate))."'".$no_header_flag.">";
							

							if(date("Y-m-d") == date("Y-m-d", strtotime("+$i days", $startdate))) {
							
								// see if we have any available drivers on this day
								$available_driver_count = 0;
								foreach($driver_avail_array as $darray) {
	
									// get a list of pre-planed loads available for this driver
									$sql = "
										select load_handler.*,
											customers.name_company
										
										from load_handler, customers
										where load_handler.preplan = 1
											and customers.id = load_handler.customer_id
											and load_handler.deleted = 0
											".$mrr_adder."
											and load_handler.preplan_driver_id = '".sql_friendly($darray['driver_id'])."'
									";
									$data_preplan = simple_query($sql);
																											
									//echo "(".$darray['customer_id'].")";
									if(count($darray) > 1 && (($disp_type > 0 && $darray['customer_id'] == $disp_type) || ($disp_type == 0 && !in_array($darray['customer_id'], $cust_id_array)))) {
										//if(date("Y-m-d", strtotime($darray['linedate_completed'])) == date("Y-m-d", strtotime("+$i days", $startdate))) {
										
										// get any attached trucks/trailers
										$sql = "
											select attached_truck_id,
												attached_trailer_id,
												trucks.name_truck,
												trucks.peoplenet_tracking,
												trailers.trailer_name,	
												drivers.jit_driver_flag											
											from drivers
												left join trucks on trucks.id = drivers.attached_truck_id
												left join trailers on trailers.id = drivers.attached_trailer_id
											where drivers.id = '".sql_friendly($darray['driver_id'])."'
										";
										
										$data_attached = simple_query($sql);
										$row_attached = mysqli_fetch_array($data_attached);
										
										$last_load_dedicated=mrr_get_last_driver_load_dedicated($darray['driver_id']);
										// && $last_load_dedicated==0
										if(($name_company!="Dedicated Loads" && $last_load_dedicated==0) || ($name_company=="Dedicated Loads" && $last_load_dedicated==1) )
										{    // 	$name_company!="Dedicated Loads"
											
											$available_driver_count++;
											if($available_driver_count == 1) {
												$info_columns.="<div class='load_board_section_header'>Available Drivers</div>";
											}
											
											$pn_trucker_msg="";
											if($row_attached['peoplenet_tracking'] > 0 )
											{
												//$mrr_pn_user_fun="preplan_note_image";
												//if($_SERVER['REMOTE_ADDR'] == '50.76.161.186')		$mrr_pn_user_fun="pn_note_image_full";	
												$mrr_pn_user_fun="truck_note_image_full";	
											
												$pn_trucker_msg="
													<img truck_id='".$row_attached['attached_truck_id']."' log_id='0' load_id='0' load_eta='".date("Y-m-d",time())." 00:00:00' class='".$mrr_pn_user_fun."' src='/images/note_msg.png' border='0' style='display:inline;height:16px;cursor:pointer;margin-left:5px;margin-right:5px;'>
													<div id='truck_note_holder_".$row_attached['attached_truck_id']."' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
												";	
											}
											
     										$info_columns.="
     											<div class='hover_for_details_drivers' style='background-color:#f9d42d;font-weight:bold;color:white' id='available_driver_holder_$darray[driver_id]'>
     												
     												<div style='float:left' title='Dedicated=".$last_load_dedicated."'>
     													<a href=\"javascript:remove_available_driver($darray[driver_id],'".str_replace("'","", $darray['name_driver_first'].' '.$darray['name_driver_last'])."')\">[x]</a>
     													<a href=\"javascript:has_load_toggle($darray[driver_id])\">(Available)</a> 
     													<a href=\"javascript:edit_driver_notes($darray[driver_id])\">".date("n/d", strtotime($darray['linedate_completed']))."<span class='".($row_attached['jit_driver_flag'] > 0 ? "jit" : "non_jit")."'> $darray[name_driver_first] $darray[name_driver_last]</span> ($darray[last_city])</a>
     													".( $row_attached['peoplenet_tracking'] > 0 ? "<a href=\"peoplenet_interface.php?find_load_id=0&find_truck_id=".$row_attached['attached_truck_id']."\" target='_blank' title='View PeopleNet Tracking'>PN</a>" : "")." 
     													".$pn_trucker_msg."     													 
     												</div>
     												<img src='images/driver_small.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_driver_history($darray[driver_id])\" alt='Driver History' title='Driver History'>
     												<img src='images/inventory.png' id='driver_has_load_$darray[driver_id]' style='width:16px;height:16px;float:left;margin-left:5px;".(isset($darray['driver_has_load']) && $darray['driver_has_load'] ? "" : "display:none")."' alt='Driver Has Load' title='Driver Has Load'>
     												
     												
     												
     												<div style='clear:both'></div>
     												
     												<div class='available_driver_details' driver_id='$darray[driver_id]'>
     										";	
     										
     										//<div style=''>".$row_preplan['estimated_miles']." mi + ".$row_preplan['deadhead_miles']." dh = ".($row_preplan['deadhead_miles']+$row_preplan['estimated_miles'])." Miles</div>
     										
     										
     										/*
     										<img src='images/note.png' id='driver_available_notes_$darray[driver_id]' onclick='edit_driver_notes($darray[driver_id])'style='cursor:pointer;width:16px;height:16px;float:left;margin-left:5px;".(isset($darray['available_notes']) && $darray['available_notes'] != ''? "" : "display:none")."' alt='Notes' title='Notes'>
     												
     										*/
     										if($row_attached['attached_truck_id']) {
     											
     											$warning_flag="";	//mrr_get_equipment_maint_notice_warning('truck',$row_attached['attached_truck_id']);		
												for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
                                        				{
                                        					if($mrr_maint_truck_arr[ $rr ] == $row_attached['attached_truck_id'])		$warning_flag=trim($mrr_maint_truck_links[ $rr ]);
                                        				}
                                        				
     											$info_columns.="
     												<div style='margin-left:30px;float:left' class='attached_truck_$row_attached[attached_truck_id]' truck_name=\"$row_attached[name_truck]\" driver_name=\"".$darray['name_driver_first'].' '.$darray['name_driver_last']."\">
     													<div style='float:left'><a href='javascript:void(0)' onclick='detach_truck($row_attached[attached_truck_id],$darray[driver_id])'>[detach]</a></div>
     													
     													<div style='float:left;margin-left:5px'>Truck: $row_attached[name_truck]</div>
     													<img src='images/truck_info.png' style='cursor:pointer;float:left;margin-left:5px' onclick=\"view_truck_history($row_attached[attached_truck_id])\" alt='Truck History' title='Truck History'> ".$warning_flag."
     													<div style='clear:both'></div>
     												</div>
     												<div style='clear:both'></div>
     											";
     										}
     										if($row_attached['attached_trailer_id']) {
     											
     											$warning_flag="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_attached['attached_trailer_id']);	
												for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                                        				{
                                        					if($mrr_maint_trailer_arr[ $rr ] == $row_attached['attached_trailer_id'])		$warning_flag=trim($mrr_maint_trailer_links[ $rr ]);
                                        				}
     											$info_columns.="
     												<div style='margin-left:30px;float:left' class='attached_trailer_$row_attached[attached_trailer_id]' trailer_name=\"$row_attached[trailer_name]\" driver_name=\"".$darray['name_driver_first'].' '.$darray['name_driver_last']."\">
     													<a href='javascript:void(0)' onclick='detach_trailer($row_attached[attached_trailer_id],$darray[driver_id])'>[detach]</a> 
     													Trailer: $row_attached[trailer_name] ".$warning_flag."
     												</div>
     												<div style='clear:both'></div>
     											";
     										}
     										while($row_preplan = mysqli_fetch_array($data_preplan)) {
     											
                    								$load_and_delivery_display="";
                                                       	if(isset($row_preplan['load_number']) && trim($row_preplan['load_number'])!="")
                                                       		$load_and_delivery_display.="<span class='load_and_delivery_display1'>LD# ".$row_preplan['load_number']."</span> ";
                                                       	if(isset($row_preplan['pickup_number']) && trim($row_preplan['pickup_number'])!="")
                                                       		$load_and_delivery_display.="<span class='load_and_delivery_display2'>PU# ".$row_preplan['pickup_number']."</span> ";
                                                       	
                                                       	if(trim($load_and_delivery_display)!="")		$load_and_delivery_display="<div class='load_and_delivery_display'>".$load_and_delivery_display."</div>";	
                    								//".$load_and_delivery_display."	
                    										
     											$info_columns.="
     												<div style='clear:both'></div>
     												
     												<div style='margin-left:30px'>
     													<a class='available_load_link' href='javascript:edit_entry_truck(0,0,$row_preplan[id])'>
     														$row_preplan[id] 
     														- $row_preplan[name_company] 
     														- $row_preplan[origin_city], $row_preplan[origin_state] ".date("H:i", strtotime($row_preplan['linedate_pickup_eta']))."
     														- $row_preplan[dest_city], $row_preplan[dest_state]  ".date("H:i", strtotime($row_preplan['linedate_dropoff_eta']))."
     													</a>
     													
     												</div>
     												<div style='clear:both'></div>
     											";
     										}
     										$info_columns.="
     												</div>
     											<div style='clear:both'></div>
     											</div>
     										";
										}//end IF NOT DEDICATED LOAD check
									
									}
								}
							}


							$use_day = date("d", strtotime("$i day", $startdate));
							if(isset($notes_day_array[$use_day])) {
								foreach($notes_day_array[$use_day] as $note_entry) {
									$info_columns.="
										<div class='note_entry'>
											<div note_id='$note_entry[id]' onclick='edit_note_date($note_entry[id],0,0)' class='note_entry_inside'>
											".trim_string($note_entry['desc_long'], 45)."
											</div>
										</div>
									";
								}
							}
							
							$loop_count = 0;
							$lh_used_id = array();
							$preplan_count = 0;
							$regular_count = 0;
							while($row_available = mysqli_fetch_array($data_available_loads)) {
								if(!in_array($row_available['id'],$lh_used_id)) {
									
									if($row_available['preplan'] == '1') {
										// preplan
										$preplan_count++;
										if($preplan_count == 1) {
											$info_columns.="
												<div class='load_board_section_header'>Preplan Loads</div>
											";
										}
									} else {
										// regular available
										$regular_count++;
										if($regular_count == 1) {
											$info_columns.="
												<div class='load_board_section_header'>Available Loads</div>
											";
										}
									}
									
									$load_and_delivery_display="";
                                        	if(isset($row_available['load_number']) && trim($row_available['load_number'])!="")
                                        		$load_and_delivery_display.="<span class='load_and_delivery_display1'>LD# ".$row_available['load_number']."</span> ";
                                        	if(isset($row_available['pickup_number']) && trim($row_available['pickup_number'])!="")
                                        	{
                                        		if($load_and_delivery_display!="")		$load_and_delivery_display.="<br>";
                                        		$load_and_delivery_display.="<span class='load_and_delivery_display2'>PU# ".$row_available['pickup_number']."</span> ";
                                        	}
                                        	if(trim($load_and_delivery_display)!="")		$load_and_delivery_display="<div class='load_and_delivery_display'>".$load_and_delivery_display."</div>";	
     								
									
									$lh_used_id[] = $row_available['id'];
									// get a list of all the stops for this load
									$sql = "
										select load_handler_stops.*,
											(select trailer_name from trailers where trailers.id=load_handler_stops.start_trailer_id) as start_trailer_name,
											(select trailer_name from trailers where trailers.id=load_handler_stops.end_trailer_id) as end_trailer_name
										
										from load_handler_stops
										where load_handler_id = '".sql_friendly($row_available['id'])."'
											and deleted = 0
										order by linedate_pickup_eta
									";
									
									$data_avail_stops = simple_query($sql);
									
									$stop_count = 0;
									$stop_var = "";
									
									$pn_driver_id=$row_available['preplan_driver_id'];
									$pn_driver2_id=$row_available['preplan_driver2_id'];
									$pn_leg2_stop_id=$row_available['preplan_leg2_stop_id'];
									$pn_driver3_id=$row_available['preplan_leg2_driver_id'];
									$pn_driver4_id=$row_available['preplan_leg2_driver2_id'];
									
									while($row_avail_stops = mysqli_fetch_array($data_avail_stops)) {
										$stop_count++;
										$loc_holder = "$row_avail_stops[shipper_city], $row_avail_stops[shipper_state]";
										if($stop_count == 1) {
											$loc_start = $loc_holder;
										}
										
										$tswitcher=$row_avail_stops['start_trailer_name'];
										if($row_avail_stops['start_trailer_id'] != $row_avail_stops['end_trailer_id'])
										{
											if($row_avail_stops['end_trailer_id'] > 0)
												$tswitcher.=" Dropped.";
											else	
												$tswitcher.=" Switched to ".$row_avail_stops['end_trailer_name'].".";
										}
										//&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Trailer: ".$tswitcher."
										
																				
										$new_leg="";
										if($pn_leg2_stop_id==$row_avail_stops['id'])
										{
											$driver3=mrr_get_driver_name($pn_driver3_id);
											$driver4="";
											if($pn_driver4_id > 0)	$driver4=" and ".mrr_get_driver_name($pn_driver4_id);
											
											$new_leg="
     											<div>
     												<div style='float:left'><span style='color:#FFFFFF;'><b>".$driver3."".$driver4."</b></span></div>
     												<div style='float:right'><span style='color:#FFFFFF;'><b>Leg 2 Starts Here</b></span></div>
     											</div>
     											<div style='clear:both'></div>
											";	
										}
										
										$stop_var .= "
											".$new_leg."
											<div>
												<div style='float:left'>
												(".($row_avail_stops['stop_type_id'] == 1 ? "S" : "C").")
												".$row_avail_stops['shipper_name'].": $row_avail_stops[shipper_city], $row_avail_stops[shipper_state]
												
												</div>
												<div style='float:right'>".($row_avail_stops['linedate_pickup_eta'] > 0 ? " - " . date("M j, H:i", strtotime($row_avail_stops['linedate_pickup_eta'])) : "")."</div>
											</div>
											<div style='clear:both'></div>
										";
									}
									$loc_end = $loc_holder;
									
									$pn_preplan="";
									$pn_preplan_msg="";
									
									
									$pn_truck_id=mrr_find_peoplenet_truck_id_by_driver($pn_driver_id);
									if($row_available['preplan'] > 0 && $pn_truck_id>0)
									{										
										$pn_class=mrr_peoplenet_link_class_by_date($pn_truck_id,$row_available['id'],1);
										
										$pn_preplan="<a class='".$pn_class."' href=\"peoplenet_interface.php?find_preplan=1&find_load_id=".$row_available['id']."&find_truck_id=".$pn_truck_id."&find_driver_id=".$pn_driver_id."\" target='_blank' title='View PeopleNet Tracking'>PN</a>";	
										
										$pn_preplan_msg="<a class='".$pn_class."' href=\"peoplenet_messager.php?truck_id=".$pn_truck_id."\" target='_blank' title='View PeopleNet Tracking Messages'>MSGS</a>";
									
									
										//$mrr_pn_user_fun="preplan_note_image";
										//if($_SERVER['REMOTE_ADDR'] == '50.76.161.186')		$mrr_pn_user_fun="pn_note_image_full";	
										$mrr_pn_user_fun="preplan_note_image_full";	
										
										$pn_preplan_msg="
											<img truck_id='".$pn_truck_id."' log_id='0' load_id='".$row_available['id']."' load_eta='".$row_available['linedate_pickup_eta']."' class='".$mrr_pn_user_fun."' src='/images/note_msg.png' border='0' style='display:inline;height:16px;cursor:pointer;margin-left:5px;margin-right:5px;'>
											<div id='preplan_note_holder_".$row_available['id']."' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
											";								
									}	
									$driver2="";
									if($pn_driver2_id > 0)		$driver2=" and ". mrr_get_driver_name($pn_driver2_id);																
									
									$info_columns.="
										<div class='hover_for_details ".($row_available['preplan'] > 0 ? "preplan_load_entry" : "available_load_entry")."'>
											<a href='manage_load.php?load_id=$row_available[id]' log_id='$row_available[id]' target='view_load_".$row_available['id']."'>"
												.($row_available['preplan'] > 0 ? "($row_available[id]) <span class='".($row_attached['jit_driver_flag'] > 0 ? "jit" : "non_jit")."'>$row_available[name_driver_first] $row_available[name_driver_last]".$driver2."</span>: " : "($row_available[id]):").
												"  $row_available[name_company]
											</a>
											$loc_start / $loc_end ".$pn_preplan." ".$pn_preplan_msg."
												
									";


									
									$info_columns.="<div class='load_stop_details' log_id='$row_available[id]'>".$load_and_delivery_display."$stop_var</div>";
									
									$info_columns.="
										<div style='clear:both'></div>
										</div>
										
									";
								}

							}
							
							$last_truck_log_id = 0;
							$loop_count = 0;
							$dropped_trailer_count = 0;
							
							$mrr_load_block_started="";
							$mrr_load_block_waiting="";
							
							while($row_log = mysqli_fetch_array($data_log)) {
								$loop_count++;
								$last_truck_log_id = $row_log['id'];
															
								$date_updated = strtotime(date("m/d/Y", strtotime($row_log['linedate_updated'])));
								$date_current = strtotime(date("m/d/Y", time()));

								$extra_class = '';
								$trailer_alert = false;
								if($date_updated < $date_current || $date_updated == '') {
									// hasn't been updated today, show an 'alert' background color
									// if this is a weekend or a monday, allow the days to go back a ways
								}
								if(strtotime($row_log['linedate']) < strtotime(date("Y-m-d", time()))) {
									$extra_class = 'update_alert';
									$trailer_alert = true;
								}
								
								if(!$trailer_alert && $row_log['dropped_trailer']) {
									$use_background_color = ";background-color:#33ff00";
								} elseif($row_log['color'] != '' && !$trailer_alert && $row_log['color'] != '#f8f3e4') {
									$use_background_color = ";background-color:$row_log[color]";
								} else {
									$use_background_color = '';
								}
								
								if($row_log['location'] != '') {
									$use_location = $row_log['location'];
								} else {
									$use_location = "<span class='mrr_origin_local_starter'>$row_log[origin], $row_log[origin_state]</span class='mrr_origin_local_ender'> to $row_log[destination], $row_log[destination_state]";												
								}
								
								//(select dedicated_trailer from trailers_dropped where trailers_dropped.trailer_id=trailers.id order by trailers_dropped.id desc limit 1) as dedicated_trailer,
								
								if($row_log['dropped_trailer']) {

									
     									$info_columns.="
     										<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:300px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
     											<span truck_id='$row_log[truck_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'>
     											<a style='margin-right:10px;float:left' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\">TL DR</a>
     											<span style='float:left;color:black;'>".trim_string($use_location,45)."</span>
     											<span style='float:right;color:black;'>$row_log[trailer_name]</span>&nbsp;
     											</span>
     											<div style='clear:both'></div>
     										</div>
     									";
								} else {
									$has_load_flag = 0;
									$has_preplan = 0;
									if($row_log['dispatch_completed']) {
										$use_image = "images/good.png";
									} elseif ($row_log['driver_preplanned']) {
										$use_image = "images/inventory.png";
										$has_preplan = $row_log['driver_preplanned'];
									} elseif ($row_log['has_load_flag']) {
										$use_image = "images/inventory.png";
										$has_load_flag = 1;
									} else {
										$use_image = "images/note.png";
									}
									
									$warning_flag1="";	//mrr_get_equipment_maint_notice_warning('truck',$row_log['truck_id']);		
									for($rr=0; $rr < $mrr_maint_truck_cntr ;$rr++)
                                        	{
                                        		if($mrr_maint_truck_arr[ $rr ] == $row_log['truck_id'])		$warning_flag1=trim($mrr_maint_truck_links[ $rr ]);
                                        	}
                                        	
									$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_log['trailer_id']);
									for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                                        	{
                                        		if($mrr_maint_trailer_arr[ $rr ] == $row_log['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                                        	}
                                        	
                                        	if($loop_count == 1) {
                                        		$info_columns.="<div class='load_board_section_header'>Loads</div>";
                                        	}
                                        	$pn_class=mrr_peoplenet_link_class_by_date($row_log['truck_id'],$row_log['id'],0);
                                        	
                                        	$dist_res="";
                                        	$dist_java="";
                                        	
                                        	/*
                                        	if($row_log['peoplenet_tracking'] > 0 && trim($pn_class)!="")
                                        	{	// && date("Y-m-d", strtotime("+$i days", $startdate)) == $dater && trim($pn_class)!="peoplenet_link_not_sent"
                                        		$mfound=0;
                                        		$mrrindex=0;
                                        		$longitude="0";
                                        		$latitude="0";
                                        		$mrrlocal="";
                                        		$mrrstamper="";
                                        		for($xx=0;$xx < $mrr_tcnt; $xx++)
                                        		{
                                        			if(	$mrr_tarr[$xx]==$row_log['truck_id'] )	
                                        			{
                                        				$mrrindex=$xx;		$mfound=1;
                                        			}
                                        		}
                                        		if($mfound > 0)
                                        		{
                                        			$latitude=$mrr_tlat[ $mrrindex ];
                                        			$longitude=$mrr_tlon[ $mrrindex ];                                        		
                                        			$mrrlocal=$mrr_tloc[ $mrrindex ];	
                                        			$mrrstamper=$mrr_tdates[ $mrrindex ];
                                        		}
                                        		
                                        		//$dist_res=mrr_peoplenet_get_last_position_distanct_and_location($row_log['truck_id'],$row_log['id']);
                                        		$dist_res=mrr_peoplenet_get_last_position_distanct_and_locationV2($row_log['truck_id'],$row_log['id'],$longitude,$latitude,$mrrlocal);       
                                        		if(trim($dist_res)!="")		$dist_res.="<br>As of ".$mrrstamper."";                 		
                                        	}
                                        	
                                        	*/
                                        	
                                        	if($row_log['peoplenet_tracking'] > 0 && trim($pn_class)!="")
                                        	{
                                        		//mrr_gen_truck_local_map_by_google($row_log['load_handler_id'],$row_log['name_truck'],$truck_lat,$truck_long);
                                        		
                                        		$dist_res="<div id='truck_local_".$row_log['load_handler_id']."' class='truck_".$row_log['truck_id']."_local'></div>";
                                        		$dist_java=" onMouseOver='mrr_find_truck_locator(".$row_log['truck_id'].",".$row_log['load_handler_id'].");'";
                                        	}
                                        	
                                        	$customer_linker="<a href='admin_customers.php?eid=".$row_log['customer_id']."' target='_blank'>".$row_log['customer_namer']."</a>";
                                        	
                                        	
                                        	//Added May 2013...sorting loads based on wtops started...(first/a stop completed)
                                        	$load_started_already=0;
                                        	$mrr_load_block="";
                                        	
                                        	
									
									$pn_msg_viewer="";	
									if($row_log['peoplenet_tracking'] > 0)
									{
										$mrr_pn_user_fun2="pn_note_image";
										//if($_SERVER['REMOTE_ADDR'] == '50.76.161.186')		$mrr_pn_user_fun="pn_note_image_full";	
										$mrr_pn_user_fun="pn_note_image_full";	
										
										$pn_msg_viewer="
											<img truck_id='".$row_log['truck_id']."' log_id='".$row_log['id']."' load_id='".$row_log['load_handler_id']."' load_eta='".$row_log['mrr_linedate_eta']."' class='".$mrr_pn_user_fun."' src='/images/note_msg.png' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;'>
											<span style='height:16px;cursor:pointer;margin-right:5px;float:left; color:#00CC00;' truck_id='".$row_log['truck_id']."' log_id='".$row_log['id']."' load_id='".$row_log['load_handler_id']."' load_eta='".$row_log['mrr_linedate_eta']."' class='".$mrr_pn_user_fun2."'>...History</span>
										";
										//
										
										/*
										$msg_date_from=date("Y-m-d",strtotime("-1 day", time()));
										$msg_date_to=date("Y-m-d",time());
										$msg_truck_id=$row_log['truck_id'];
										$msg_truck_name=trim($row_log['name_truck']);
										
										if($msg_truck_id>0)
										{
											$mrr_tab_sent="";
											$mrr_tab_recv="";
											
											$mrr_tab_sent=mrr_get_messages_sent_by_truck($msg_date_from, $msg_date_to, $msg_truck_id, $msg_truck_name, 0, 0, 3);
											$mrr_tab_recv=mrr_get_messages_sent_out_to_truck($msg_date_from, $msg_date_to,  $msg_truck_id, $msg_truck_name, 0, 0, 3);
											
											$mrr_load_block.= "
												<div class='pn_messages_block'>
													<table cellpadding='2' cellspacing='2' border='0'> 
														".$mrr_tab_sent."
													</table>
													<br>
													<table cellpadding='2' cellspacing='2' border='0'> 
														".$mrr_tab_recv."
													</table>
												</div>
												<div style='clear:both'></div>
											";
											
										}	
										*/
									}
                                        	//	
                                        	$load_and_delivery_display="";
                                        	if(isset($row_log['load_number']) && trim($row_log['load_number'])!="")
                                        		$load_and_delivery_display.="<span class='load_and_delivery_display1'>LD# ".$row_log['load_number']."</span> ";
                                        	if(isset($row_log['pickup_number']) && trim($row_log['pickup_number'])!="")
                                        		$load_and_delivery_display.="<span class='load_and_delivery_display2'>PU# ".$row_log['pickup_number']."</span> ";
                                        	
                                        	if(trim($load_and_delivery_display)!="")		$load_and_delivery_display="<div class='load_and_delivery_display'>".$load_and_delivery_display."</div>";
                                        	
                                        	//".trim_string($use_location,45)."
                                        	
									$mrr_load_block.= "
										<div class='hover_for_details truck_entry $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style='$use_background_color;min-width:480px;".($row_log['dispatch_completed'] == 1 ? 'display:none' : '').";'>
											<span truck_id='$row_log[truck_id]' load_handler_id='$row_log[load_handler_id]' trailer_id='$row_log[trailer_id]' class='entry_info' log_id='$row_log[id]'>
												<div style='float:left;width:110px;'>
													<img log_id='$row_log[id]' class='note_image' preplan_load_id='$has_preplan' has_load_flag='$has_load_flag' src='$use_image' onclick='add_note($row_log[id])' border='0' style='height:16px;cursor:pointer;margin-right:5px;float:left;".($row_log['note_count'] > 0 || $row_log['special_instructions'] != '' ? "border:1px red solid;" : "")."'>
													<div id='note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;width:425px;background-color:#eeeeee;padding:5px'></div>
													".($row_log['load_handler_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/menu_system16.png' style='cursor:pointer;float:left' onclick=\"edit_entry_truck('',$row_log[id],0)\" alt='Edit Dispatch' title='Edit Dispatch'></div>" : "")."
													".($row_log['truck_id'] > 0 ? "<div style='float:left;margin-right:5px'><img src='images/truck_info.png' style='cursor:pointer;float:left' onclick=\"view_truck_history($row_log[truck_id])\" alt='Truck History' title='Truck History'></div>" : "")."
													".( $row_log['peoplenet_tracking'] > 0 ? "<a class='".$pn_class."' href=\"peoplenet_interface.php?find_load_id=".$row_log['load_handler_id']."&find_truck_id=".$row_log['truck_id']."\" target='_blank' title='View PeopleNet Tracking'>PN</a>" : "")." 
													".$pn_msg_viewer."
													<div id='pn_note_holder_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
													<div id='pn_note_holder2_$row_log[id]' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>
												</div>												

												<a style='margin-right:5px;float:left;display:inline-block;width:160px;overflow:hidden' href=\"javascript:edit_entry_truck('".date("Y-d-m", strtotime($row_log['linedate']))."',$row_log[id],$row_log[load_handler_id])\" log_id='$row_log[id]'>($row_log[load_handler_id]) <div class='".($row_log['jit_driver_flag'] > 0 ? "jit2" : "non_jit2")."'>$row_log[name_driver_last], $row_log[name_driver_first]</div></a>
												<div style='float:left;color:black;'".$dist_java.">".$use_location."</div>
												<div style='float:right;margin-right:5px;font-weight:bold;color:black;'>
													
													".( $row_log['peoplenet_tracking'] > 0 ? "<a class='".$pn_class."' href=\"peoplenet_messager.php?truck_id=".$row_log['truck_id']."\" target='_blank' title='View PeopleNet Tracking Messages'>MSGS</a>" : "")." 
													<b>$row_log[name_truck]".$warning_flag1."</b> / ".$warning_flag2."$row_log[trailer_name]
																										
												</div>

												<div style='clear:both'></div>
											</span>
											<div class='load_stop_details' log_id='$row_log[id]' style='color:black;'>
												<div style='float:right;margin-right:5px;color:black;' id='phone_cell_display_$row_log[truck_id]'>
													<input style='background-color:transparent;border:0;width:90px;height:10px;text-align:right;cursor:pointer;color:black;' readonly value=\"$row_log[phone_cell]\" onclick=\"$(this).select()\">
												</div>
												".$customer_linker." ".($row_log['driver2_id'] > 0 ? "(TEAM DRIVER)" : "")." | ".$row_log['miles']." mi + ".$row_log['miles_deadhead']." dh = ".($row_log['miles'] + $row_log['miles_deadhead'])." miles. ".$dist_res."
												".$load_and_delivery_display."
												<div style='clear:both'></div>
									";
									
									//stops for this load...
									$counter_sub = 0;
									mysqli_data_seek($data_log,$loop_count-1);
									while($row_log_sub = mysqli_fetch_array($data_log)) {
										$counter_sub++;
										//$mrr_load_block.= "($row_log[load_handler_id] | $row_log_sub[load_handler_id] | $row_log_sub[load_handler_stop_id] | loop_count: $loop_count | counter: $counter_sub)";
																				
										if($row_log['id'] != $row_log_sub['id'] || $row_log_sub['id'] == 0 || $row_log_sub['id'] == '') {
											if($counter_sub > 1) $counter_sub--;
											$loop_count = $loop_count + $counter_sub - 1;
											
											@mysqli_data_seek($data_log,$loop_count);
											break;
										} else {
											
											//appointment window..........................................
                                        			$appt_window_tag1="";
                                        			$appt_window_tag2="";			
                                        			$appt_window=$row_log_sub['appointment_window'];
                                        			if($appt_window > 0)
                                        			{				
                                        				$ideal_time=date("M d, Y", strtotime($row_log_sub['linedate_pickup_eta']))." ".time_prep($row_log_sub['linedate_pickup_eta']);
                                        				$appt_window_start="";
                                        				$appt_window_start_time="";
                                        				$appt_window_end="";
                                        				$appt_window_end_time="";
                                        				
                                        				if(strtotime($row_log_sub['linedate_appt_window_start']) > 0)
                                        				{
                                        					$appt_window_start=date("M d, Y", strtotime($row_log_sub['linedate_appt_window_start']));
                                        					$appt_window_start_time=time_prep($row_log_sub['linedate_appt_window_start']);
                                        				}
                                        				if(strtotime($row_log_sub['linedate_appt_window_end']) > 0)
                                        				{
                                        					$appt_window_end=date("M d, Y", strtotime($row_log_sub['linedate_appt_window_end']));
                                        					$appt_window_end_time=time_prep($row_log_sub['linedate_appt_window_end']);
                                        				}
                                        				$appt_window_tag1.="<span class='mrr_link_like_on mrr_appt_windower' style='display: inline-block;' onMouseOver=\"mrr_appt_window_display(".$row_log_sub['load_handler_stop_id'].",'".$ideal_time."','".$appt_window_start." ".$appt_window_start_time."','".$appt_window_end." ".$appt_window_end_time."');\" onMouseOut=\"mrr_appt_window_no_display(".$row_log_sub['load_handler_stop_id'].");\">";				
                                        				$appt_window_tag2.=" [Window]</span> <div id='stop_".$row_log_sub['load_handler_stop_id']."_appt_window' style='display:none;position:absolute;border:1px black solid;margin-left:50px;background-color:#eeeeee;padding:5px;'></div>";
                                        			}			
                                        			//.......................................................................................
											
											$mrr_load_block.= "
													<div style='float:left".($row_log_sub['linedate_completed'] > 0 ? ";color:#888888;" : ";color:black;")."'>
													(".($row_log_sub['stop_type_id'] == 1 ? "S" : "C").") ($row_log_sub[load_handler_stop_id])
													".$appt_window_tag1."".$row_log_sub['shipper_name'].": $row_log_sub[shipper_city], $row_log_sub[shipper_state].".$appt_window_tag2."
													</div>
													<div style='float:right'>
											";											
											
												if($row_log_sub['linedate_completed'] > 0) {												
													$mrr_load_block.= "<span style='color:#888888;color:black;'>".($row_log_sub['linedate_completed'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_completed'])) : "")."&nbsp;&nbsp;&nbsp</span>";
													$load_started_already=1;
												} else {
													
													$mrr_load_block.= " Trailer ".$row_log_sub['mrr_start_trailer']."";
          											if($row_log_sub['mrr_end_trailer'] != $row_log_sub['mrr_start_trailer'] && $row_log_sub['mrr_end_trailer'] > 0)
          											{
          												$mrr_load_block.= " Switch to ".$row_log_sub['mrr_end_trailer']."";	
          											}
          											elseif($row_log_sub['mrr_end_trailer'] != $row_log_sub['mrr_start_trailer'] && $row_log_sub['mrr_end_trailer'] == 0)
          											{
          												$mrr_load_block.= " Drop";	
          											}
													
													$mrr_load_block.= "&nbsp;&nbsp; <a href='javascript:update_stop_complete($row_log_sub[load_handler_stop_id],".$row_log_sub['stop_type_id'].")'>".
														($row_log_sub['linedate_pickup_eta'] > 0 ? " " . date("M j, H:i", strtotime($row_log_sub['linedate_pickup_eta'])) : "")."&nbsp;&nbsp"
														."</a>";
												}
														
											$mrr_load_block.= "
													</div>
												
												<div style='clear:both'></div>
											";
										}
									}
									//@mysqli_data_seek($data_log, $loop_count-1);
									
									
									
									$mrr_load_block.= "
											</div>
										</div>
									";
									
									if($load_started_already > 0)
									{										
                                        		$mrr_load_block_started.=$mrr_load_block;									
									}
									else
									{										
										$mrr_load_block_waiting.=$mrr_load_block;		
									}
																	
									
								} // end of dropped trailer if
							} // end of while statement
							
							$mrr_load_block_started=str_replace("<span class='mrr_origin_local_starter'>","<span style='color:#999999; display:inline-block;'>",$mrr_load_block_started);	//<span style='color:#999999;'>
							$mrr_load_block_started=str_replace("</span class='mrr_origin_local_ender'>","</span>",$mrr_load_block_started);						//</span>
							
							$mrr_load_block_waiting=str_replace("<span class='mrr_origin_local_starter'>","",$mrr_load_block_waiting);
							$mrr_load_block_waiting=str_replace("</span class='mrr_origin_local_ender'>","",$mrr_load_block_waiting);
							
							//sub-divided loads based on if stop(s) are completed yet...Added May 2013 (sorted this way)
							if(trim($mrr_load_block_started)!="")		$info_columns.="<div class='mrr_load_div_titles'>Loads already in progress:</div>".$mrr_load_block_started."";
							if(trim($mrr_load_block_waiting)!="")		$info_columns.="<div class='mrr_load_div_titles'>Loads not started/dispatched:</div>".$mrr_load_block_waiting."";
							
							
							if($name_company!="Dedicated Loads")
							{	//this is the true section to display Dropped Trailers...
     							
     							if(date("m/d/Y", strtotime("+$i days", $startdate)) == date("m/d/Y", time())   ) {
     								// today's date                                                            && $name_company!="Dedicated Loads"
     								
     								// show any dropped trailer
     								//and trailers_dropped.linedate = '".date("Y-m-d", strtotime("+$i days", $startdate))."'
     								$mrr_message="";
     								$mrr_add_dedicated="";
     								
     								//$mrr_add_dedicated=' and trailers_dropped.dedicated_trailer=0';
     								if($name_company=="Dedicated Loads")
     								{
     									//$mrr_add_dedicated=' and trailers_dropped.dedicated_trailer>0';
     									//$mrr_message="Dedicated... ";
     								}								
     								$sql = "
     									select trailers_dropped.*,
     										trailers.trailer_name,
     										customers.name_company
     									
     									from trailers_dropped, trailers, customers
     									where trailers.id = trailers_dropped.trailer_id
     										and trailers_dropped.customer_id=customers.id										
     										and trailers_dropped.drop_completed = 0
     										and trailers_dropped.deleted = 0
     										".$mrr_add_dedicated."
     										
     										$sql_filter_drop_tl_cust	
     									order by location_city asc,location_state asc,location_zip asc,name_company asc,trailer_name asc							
     								";
     								$last_stater="";
     								$data_trailers_dropped = simple_query($sql);
     								$dropped_trailer_count = 0;
     								while($row_trailer_dropped = mysqli_fetch_array($data_trailers_dropped)) {
     									
     									$dropped_trailer_count++;
     									// dropped trailers are always shown last. If this is the first dropped trailer, then show a general toggle for them
     									if($dropped_trailer_count == 1) {
     										$info_columns.="
     											<div class='load_board_section_header' onclick='mrr_toggle_dropped_trailer_setter(".$_SESSION['toggle_dropped_trailer_on'].");' style='cursor:pointer;color:#c4ffc4'>
     												Toggle Dropped Trailers
     											</div>
     										";			//toggle_dropped_trailers()
     										/*
     										if($_SESSION['toggle_dropped_trailer_on']==1)
     										{
     											$info_columns.="<script type='text/javascript'>
     													toggle_dropped_trailers();
     													</script>";	
     											$_SESSION['toggle_dropped_trailer_on']=1;
     										}
     										*/
     									}
     									
     									$warning_flag2="";	//mrr_get_equipment_maint_notice_warning('trailer',$row_trailer_dropped['trailer_id']);
     									for($rr=0; $rr < $mrr_maint_trailer_cntr ;$rr++)
                                             	{
                                             		if($mrr_maint_trailer_arr[ $rr ] == $row_trailer_dropped['trailer_id'])		$warning_flag2=trim($mrr_maint_trailer_links[ $rr ]);
                                             	}
                                             	
                                             	if($last_stater!="$row_trailer_dropped[location_city]")
                                             	{
                                             		$last_stater="$row_trailer_dropped[location_city]";	
                                             		$info_columns.="<div>&nbsp;</div>";
                                             	}
                                             	
     									$info_columns.="
     										<div class='truck_entry dropped_trailer $extra_class' dispatch_completed_flag='$row_log[dispatch_completed]' style=';min-width:300px;'>
     											<span trailer_id='$row_trailer_dropped[trailer_id]' class='entry_info' dropped_trailer_id='$row_trailer_dropped[id]'>
     											<a style='margin-right:10px;float:left' href=\"javascript:edit_dropped_trailer($row_trailer_dropped[id])\">TL DR</a>
     											<span style='float:left;color:black;'>".trim_string("$row_trailer_dropped[location_city], $row_trailer_dropped[location_state] $row_trailer_dropped[location_zip]",45)."</span>
     											<span style='float:right;color:black;'>".$mrr_message."".$warning_flag2."$row_trailer_dropped[trailer_name]</span>&nbsp;
     											</span>
     											<div style='clear:both'></div>
     										</div>
     									";
     								}
     							}
							}
							
							$info_columns.="&nbsp;</td>";
							
							
						}
		//$info_columns.="</tr>";
		
		//echo $date_headers;
		//echo "</tr><tr>";
		//echo $info_columns;
		
		$res['column_headers']=$date_headers;
		$res['column_info']=$info_columns;
		return $res;
		//mrr_log_page_loads($mrr_micro_seconds_partA," TRUCK SECTION Display Sub 2b");		//make tracking log entry
	}
	
	
	
	

	
	function mrr_find_peoplenet_truck_id_by_driver($driver_id)
	{
		$truck_id=0;
		$sql="
     		select truck_id
     		from trucks_log,trucks
     		where trucks_log.truck_id=trucks.id
     			and trucks_log.driver_id='".sql_friendly($driver_id)."'
     			and trucks_log.deleted=0
     			and trucks_log.dispatch_completed=0
     			and trucks.peoplenet_tracking > 0
     		order by trucks_log.linedate_pickup_eta asc
     		limit 1
     	";
     	$data=simple_query($sql);
     	if($row=mysqli_fetch_array($data))
     	{
     		$truck_id=$row['truck_id'];
     	}
     	return $truck_id;	
	}	
	
?>
<? 
//if($_SERVER['REMOTE_ADDR'] != '50.76.161.186') { 
?>
<script type='text/javascript'>
	
	var mrr_window_sizer 	= 	'';
	var mrr_window_sizer_misc = 	'';
	var mrr_window_sizer_load = 	'';
	
	
			
	function add_entry_truck_id(linedate,truck_id) {
		windowname = window.open('add_entry_truck.php?linedate='+linedate+'&truck_id='+truck_id,'add_entry_truck',''+mrr_window_sizer+'');
		windowname.focus();
	}

	function add_entry_truck_trailer(linedate,trailer_id) {
		windowname = window.open('add_entry_truck.php?linedate='+linedate+'&trailer_id='+trailer_id,'add_entry_truck',''+mrr_window_sizer+'');
		windowname.focus();
	}
	
	function add_entry_truck(linedate) {
		windowname = window.open('add_entry_truck.php?linedate='+linedate,'add_entry_truck',''+mrr_window_sizer+'');
		windowname.focus();
	}
	
	function edit_entry_truck(linedate, id, load_handler_id) {
		if(load_handler_id) {
			windowname = window.open('manage_load.php?load_id='+load_handler_id,'add_entry_truck',mrr_window_sizer_load);
		} else {
			windowname = window.open('add_entry_truck.php?linedate='+linedate+'&id='+id,'add_entry_truck',mrr_window_sizer_load);
		}
		windowname.focus();
	}
	
	function add_entry_appointment() {
		windowname = window.open('add_entry_appointment.php','add_entry_appointment',''+mrr_window_sizer_misc+'');
		windowname.focus();
	}
	
	function edit_entry_appointment(id) {
		windowname = window.open('add_entry_appointment.php?id=' + id,'edit_entry_appointment',''+mrr_window_sizer_misc+'');
		windowname.focus();
	}
	
	function edit_event(id) {
		windowname = window.open('edit_event.php?id=' + id,'edit_event',''+mrr_window_sizer_misc+'');
		windowname.focus();
	}
	
	function edit_note(id) {
		windowname = window.open('edit_note.php?sid=<?=$row_notes['id']?>&id=' + id,'edit_entry_appointment',''+mrr_window_sizer_misc+'');
		windowname.focus();
	}
	
	function edit_note_date(id, customer_id, linedate) {
		windowname = window.open('edit_note_date.php?id=' + id + '&customer_id='+customer_id+'&linedate='+linedate,'edit_note_date',''+mrr_window_sizer_misc+'');
		windowname.focus();
	}
	

	

	
	function confirm_delete_note(id) {
		if(confirm("Are you sure you want to delete this note?")) {
			window.location = "<?=$SCRIPT_NAME?>?delnoteid="+id;
		}
	}
	
	function delete_event(id) {
		if(confirm("Are you sure you want to delete this event?")) {
			window.location = "<?=$SCRIPT_NAME?>?deleventid="+id;
		}
	}
	
	function delete_driver_unavailable(id) {
		if(confirm("Are you sure you want to remove this driver unavailable note?")) {
			window.location = "<?=$SCRIPT_NAME?>?deldriverunavailableid="+id;
		}
	}
	function delete_from_calendar(id) {
		if(confirm("Are you sure you want to remove this note from the calendar?")) {
			window.location = "<?=$SCRIPT_NAME?>?delcalendarid="+id;
		}	
	}
</script>
<?
//}
?>

<?
	$nowtime2=time();
	$nowtime=time();
				
	$gmt_off = (int) $defaultsarray['gmt_offset_peoplenet'];
	$gmt_off = $gmt_off * 60 * 60 * -1;
	$nowtime+=$gmt_off;			
	
	$nowtime3=$nowtime2+3600;
	$nowtime4=$nowtime2-3600;
	$nowtime5=$nowtime2-3600-3600;
	$nowtime6=$nowtime2+3600+3600;	
	
	$mrr_tz_gmt_day=date("Y-m-d",$nowtime);
	$mrr_tz_ast_day=date("Y-m-d",$nowtime6);
	$mrr_tz_est_day=date("Y-m-d",$nowtime3);
	$mrr_tz_cst_day=date("Y-m-d",$nowtime2);
	$mrr_tz_mst_day=date("Y-m-d",$nowtime4);
	$mrr_tz_pst_day=date("Y-m-d",$nowtime5);
	
	$mrr_tz_gmt=date("H:i",$nowtime);
	$mrr_tz_ast=date("H:i",$nowtime6);
	$mrr_tz_est=date("H:i",$nowtime3);
	$mrr_tz_cst=date("H:i",$nowtime2);
	$mrr_tz_mst=date("H:i",$nowtime4);
	$mrr_tz_pst=date("H:i",$nowtime5);
	
	$res_timex="";
	$res_timex.="<div>GMT...".$mrr_tz_gmt_day." ".$mrr_tz_gmt.".</div>";
	$res_timex.="<div>Atlantic...".$mrr_tz_ast_day." ".$mrr_tz_ast.".</div>";
	$res_timex.="<div>Eastern...".$mrr_tz_est_day." ".$mrr_tz_est.".</div>";
	$res_timex.="<div>Central...".$mrr_tz_cst_day." ".$mrr_tz_cst.".</div>";		
	$res_timex.="<div>Mountain...".$mrr_tz_mst_day." ".$mrr_tz_mst.".</div>";
	$res_timex.="<div>Pacific...".$mrr_tz_pst_day." ".$mrr_tz_pst.".</div>";	
	
	//echo $res_timex;
	
	
	$mrr_gen_grader_selector=mrr_select_load_stop_grades("popup_stop_grade_id",0,"Grade Stop","");	
	

	
?>
<? include('footer.php') ?>

<div class="form_tooltip"></div> 

<? 
//if($_SERVER['REMOTE_ADDR'] != '50.76.161.186') { 
?>

<script type='text/javascript'>
	
	var tooltips_loaded = false;
	var customer_surcharge_list_loaded = false;
	var show_details = false;
	var show_object_after_wait = "";
	
	var optional_week_days=0;
	
	$().ready(function() {
		$('#ajax_time_keeper').html('');
		var startTime = new Date();
		
		var startTime1 = new Date();
		mrr_window_sizer 	= 	'height='+mrr_default_window_size_dispatch_height+',width='+mrr_default_window_size_dispatch_width+',menubar=no,location=no,resizable=yes,status=no,scrollbars=yes';
		mrr_window_sizer_misc = 	'height='+mrr_default_window_size_misc_height+',width='+mrr_default_window_size_misc_width+',menubar=no,location=no,resizable=yes,status=no,scrollbars=yes';
		mrr_window_sizer_load = 	'height='+mrr_default_window_size_load_height+',width='+mrr_default_window_size_load_width+',menubar=no,location=no,resizable=yes,status=no,scrollbars=yes';
		var endTime1 = new Date();
		
		var startTime2 = new Date();
		//make_draggable();	
		<?
			if($_SESSION['toggle_dropped_trailer_on']==1)
			{
				echo "toggle_dropped_trailers();";	
				$_SESSION['toggle_dropped_trailer_on']=1;
			}
		?>	
		var endTime2 = new Date();
		
		var startTime3 = new Date();
		//remove the days from the calendar that have past and have no items on them...
		past_hdrs=0;
		optional_week_days=0;		
		$(".date_in_past_header").each(function(){
          	past_hdrs++; 
		});
		var endTime3 = new Date();
		
		var startTime4 = new Date();
		past_info=0;
		optional_week_days=0;		
		$(".date_in_past_info").each(function(){
			
			//get date in hidden attribute for column
			//mydater=$(this).attr('mrr_hdr_id');
			
			//now remove the column header and column if this is present...regular load board
			if($(this).attr('mrr_col_1_id'))
			{
				mydater=$(this).attr('mrr_col_1_id');
				$('td[mrr_hdr_1_id="'+mydater+'"]').hide();				
				$(this).hide();
			}
			
			//now remove the column header and column if this is present...DEDICATED load board
			if($(this).attr('mrr_col_2_id'))
			{
				mydater=$(this).attr('mrr_col_2_id');
				$('td[mrr_hdr_2_id="'+mydater+'"]').hide();				
				$(this).hide();
			}			
			
          	past_info++; 
		});
		var endTime4 = new Date();
		/*
		if(past_hdrs==past_info)
		{ 
			$(".date_in_past_header").hide();
			$(".date_in_past_info").hide();
		}
		*/
		
		/*
		optional_week_days=0;		
		$(".optional_week_day").each(function(){
          	optional_week_days++; 
		});
		
		if(optional_week_days==7 || optional_week_days==14)
		{ 
			$(".optional_week_day").hide();
			$(".optional_week").hide();
		}
		*/
		
		var endTime = new Date();		//getTime function returns milliseconds.  1 milli = 0.001 seconds
		mrr_cur_timer_diff=endTime.getTime()/1000 - startTime.getTime()/1000;
		
		mrr_cur_timer_diff1=endTime1.getTime()/1000 - startTime1.getTime()/1000;		
		mrr_cur_timer_diff2=endTime2.getTime()/1000 - startTime2.getTime()/1000;		
		mrr_cur_timer_diff3=endTime3.getTime()/1000 - startTime3.getTime()/1000;		
		mrr_cur_timer_diff4=endTime4.getTime()/1000 - startTime4.getTime()/1000;
			
		
		
		time_report="";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>Window Sizing Section Start Time = "+startTime1.getTime()/1000+".";		
		time_report= time_report + "<br>Window Sizing Section End Time = "+endTime1.getTime()/1000+".";
		time_report= time_report + "<br>Window Sizing Section Total Time = <b>"+mrr_cur_timer_diff1+"</b> Seconds.";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>Dropped Trailer Section Start Time = "+startTime2.getTime()/1000+".";		
		time_report= time_report + "<br>Dropped Trailer Section End Time = "+endTime2.getTime()/1000+".";
		time_report= time_report + "<br>Dropped Trailer Section Total Time = <b>"+mrr_cur_timer_diff2+"</b> Seconds.";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>Find Past Days Section Start Time = "+startTime3.getTime()/1000+".";		
		time_report= time_report + "<br>Find Past Days Section End Time = "+endTime3.getTime()/1000+".";
		time_report= time_report + "<br>Find Past Days Section Total Time = <b>"+mrr_cur_timer_diff3+"</b> Seconds.";
		time_report= time_report + "<br>";	
		time_report= time_report + "<br>Hide Past Days Section Start Time = "+startTime4.getTime()/1000+".";		
		time_report= time_report + "<br>Hide Past Days Section End Time = "+endTime4.getTime()/1000+".";
		time_report= time_report + "<br>Hide Past Days Section Total Time = <b>"+mrr_cur_timer_diff4+"</b> Seconds.";
		time_report= time_report + "<br><hr>";	
		time_report= time_report + "<br>Ajax Start Time = "+startTime.getTime()/1000+".";		
		time_report= time_report + "<br>Ajax End Time = "+endTime.getTime()/1000+".";
		time_report= time_report + "<br>Ajax Total Time = <b>"+mrr_cur_timer_diff+"</b> Seconds.";
		
		//$('#ajax_time_keeper').html('<b>Ajax Ready Time Report:</b> '+time_report+'');
	});	
	
	function mrr_quick_msg(load_id,dispatch_id,truck_id) 
	{
		/* 
		$.ajax({
			url:"ajax.php?cmd=get_detach_info",
			dataType: "xml",
			type: "post",
			async: false,
			data: {
				trailer_id: trailer_id,
				driver_id: driver_id
			},
			success: function(xml) {
				trailer_name = $(xml).find('TrailerName').text();
				driver_name = $(xml).find('DriverName').text();
			}
		});
		
		
		
		
		
		
		txt="";
		txt+="Enter your note for dispatch ID: "+load_id+", dispatch ID: "+dispatch_id+"<br>";
		txt+="<textarea style='width:400px;height:100px' name='new_note' id='new_note'></textarea><br><br>";
		$.prompt(txt, {
			buttons: {Okay: true, Cancel: false},
			loaded: function() {
				//$('#new_note').focus();
				//$('#added_note_deadline').datepicker();
			},
			submit: function(v, m, f) {
				if(v) {
					if(f.new_note == '') {
						$.prompt.close();
						return false;
					}
					
					$.ajax({
						url: "ajax.php?cmd=add_truck_note",
						dataType: "xml",
						type: "post",
						data: {
							dispatch_id: dispatch_id,
							note: f.new_note 
						},
						success: function(xml) {
							$.noticeAdd({text: "Success - note was saved to dispatch " + dispatch_id});
						}
					});
					
					
				}
			}
		});
		*/
	}
	
	
	$('.hover_for_details').hover(
		function() {
			if(!show_details) {
				show_details_action($(this),'.load_stop_details');
			}
		},
		function() {
			
		}
	);
	
	$('.hover_for_details_drivers').hover(
		function() {
			if(!show_details) {
				show_details_action($(this),'.available_driver_details');
			}
		},
		function() {

		}
	);
	
	function show_details_action(obj, use_class) {
		show_object_after_wait = obj;
		setInterval(function() {show_obj(obj, use_class)}, 500);
	}
	
	function show_obj(obj, use_class) {
		if(show_object_after_wait == obj) {
			$(use_class).hide();
			$(obj).find(use_class).show();
		}
	}
	
	
	
	function toggle_dropped_trailers() {
		$('.dropped_trailer').toggle();
		
	}
	
	
	function mrr_find_truck_locator(truckid,loadid)
	{
		$('#truck_local_'+loadid+'').html('Scanning PeopleNet');	//this load only
		//$('.truck_'+truckid+'_local').html('Scanning PeopleNet');	//all loads with this truck since the truck is only in one place...
				
		$.ajax({
			url: 'ajax.php?cmd=mrr_get_current_location_for_truck_id', 
			dataType: "xml",
			type: "post",
			data: {
				"truck_id": truckid,
				"load_id": loadid
			},
			cache: false,
			async: false,
			success: function(xml) {
				
				mytext=$(xml).find('mrrText').text();
				//$.prompt(mytext);
				
				$('#truck_local_'+loadid+'').html(mytext);	//this load only
				$('.truck_'+truckid+'_local').html(mytext);	//all loads with this truck since the truck is only in one place...				
			}
		});		
	}
	
	function mrr_toggle_dropped_trailer_setter(sesval)
	{	//set session variable so that toggle stays on or off.
		cmd="";
		if(sesval==1)	cmd="mrr_toggle_dropped_trailers_off";
		else			cmd="mrr_toggle_dropped_trailers_on";
		
		$.ajax({
			url: 'ajax.php?cmd='+cmd+'', 
			dataType: "xml",
			type: "post",
			data: {
				id: '0'
			},
			cache: false,
			async: false,
			success: function(xml) {
				
			}
		});	
		toggle_dropped_trailers();
	}
	
	function toggle_details() {
		if(show_details) {
			//$('.load_stop_details').hide();
			//$('.available_driver_details').hide();
			//show_details = false;
		} else {
			$('.load_stop_details').show();
			$('.available_driver_details').show();
			show_details = true;
		}
	}
	
	function mrr_get_fuel_surcharge_by_date(mon,day,year)
	{
		dater=""+mon+"/"+day+"/"+year+"";
		
		$.ajax({
			url: 'ajax.php?cmd=mrr_customer_fuel_surcharge_by_date', 
			dataType: "xml",
			type: "post",
			data: {
				"date_from": dater
			},
			cache: false,
			async: false,
			success: function(xml) {
				
				mytab=$(xml).find('mrrTab').text();
				$.prompt(mytab);
				
			}
		});
	}
	
	
	function get_driver_notes(driver_id) {
		var retval = new Array();
		$.ajax({
			url: 'ajax.php?cmd=get_driver_notes', 
			dataType: "xml",
			type: "post",
			data: {
				driver_id: driver_id
			},
			cache: false,
			async: false,
			success: function(xml) {
				retval.push($(xml).find('html').text());
				retval.push($(xml).find('modified_date').text());
			}
		});
		
		return retval;
	}
	
	function edit_driver_notes(driver_id) {
		
		driver_notes = get_driver_notes(driver_id);
		
		txt = "<b>Available Driver Notes</b>";
		if(driver_notes[0] != '') txt += "<br>Last Modified: " + driver_notes[1]+"<br>";
		txt += "<textarea name='available_driver_notes' id='available_driver_notes' style='width:100%;height:100px'>"+driver_notes[0]+"</textarea>";
		$.prompt(txt, {
			buttons: {Save: true, Cancel: false},
			loaded: function() {
				$('#available_driver_notes').focus();
			},
			submit: function(v, m, f) {
				if(v) {
					if($('#available_driver_notes').val() == '') {
						$('#driver_available_notes_'+driver_id).hide();
					} else {
						$('#driver_available_notes_'+driver_id).show();
					}
					
					$.ajax({
						url: "ajax.php?cmd=update_driver_notes",
						dataType: "xml",
						type: "post",
						data: {
							driver_id: driver_id,
							driver_notes: $('#available_driver_notes').val()
						},
						success: function() {
							$.noticeAdd({text: "Success - Driver notes saved"});
						}
					});
				}
			}
		});
	}
	$('.note_image').hover(
		function() {
			log_id = $(this).attr('log_id');
			
			$('#note_holder_'+log_id).html("<img src='images/loader.gif'> Loading note...");
			
			$('#note_holder_'+log_id).show();
			
			has_load_flag = $(this).attr('has_load_flag');
			preplan_load_id = $(this).attr('preplan_load_id');
			extra_html = "";
			
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=load_notes",
			   data: {"log_id":log_id},
			   success: function(data) {
			   		extra_html = "";
			   		if(has_load_flag == '1') extra_html += "<div class='format_alert'>Has Load</div>";
			   		if(preplan_load_id > 0) extra_html += "<div class='format_alert'>Preplan Load ID: "+preplan_load_id+"</div>";
			   		$('#note_holder_'+log_id).html(extra_html + data);
			   }
			 });
		},
		function() {
			$('#note_holder_'+$(this).attr('log_id')).hide();
		}
	);
	
	function mrr_send_quick_msg_form(truck_id,load_id,log_id,sectioner)
	{		
		msgtosend=$('#truck_msg_'+truck_id+'_'+load_id+'_'+log_id+'_'+sectioner+'').val();
		
		//alert('MSG Truck='+truck_id+', Load='+load_id+', Dispatch='+log_id+', Section='+sectioner+'.<br>Message:<br>"'+msgtosend+'"<br>...ready to send.');
		
		sender_out=1;
		if(msgtosend!="" && truck_id > 0 && sender_out==1)
		{
			$('#pn_sent_message_'+truck_id+'_'+load_id+'_'+log_id+'_'+sectioner+'').html('&nbsp;');	
			
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_quick_message_form_sender",
			   data: {
			   		"truck_id":truck_id, 
			   		"disp_id":log_id,
			   		"load_id":load_id,
			   		"driver_id":0,
			   		"message":msgtosend,
			   		"msg_id":0			   		
			   		},
			   success: function(data) {			   		
			   		$('#pn_sent_message_'+truck_id+'_'+load_id+'_'+log_id+'_'+sectioner+'').html(data);	//".$truck_id."_".$load_id."_".$dispatch_id."_".$disp_section."	
			   		$('#truck_msg_'+truck_id+'_'+load_id+'_'+log_id+'_'+sectioner+'').val('');	   		
			   }
			 });
		}
		else
		{
			$('#pn_sent_message_'+truck_id+'_'+load_id+'_'+log_id+'_'+sectioner+'').html('Message is blank or Truck not found.');	
		}
	}
		
	
	function mrr_close_truck_msg_displayer(truckid)
	{
		$('#truck_note_holder_'+truckid+'').hide();	
	}
	
	$('.truck_note_image_full').click(
		function() {
			truck_id = $(this).attr('truck_id');
			log_id = $(this).attr('log_id');
			loadid=$(this).attr('load_id');
			datefrom=$(this).attr('load_eta');
						
			$('#truck_note_holder_'+truck_id).html("<img src='images/loader.gif'> Loading PN recent messages...");
			
			$('#truck_note_holder_'+truck_id).show();
			
			extra_html = "";
			
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_quick_message_form_display",
			   data: {
			   		"truck_id":truck_id, 
			   		"disp_id":log_id,
			   		"load_id":loadid,
			   		"driver_id":0,
			   		"date_from":datefrom,
			   		"date_to":'',
			   		"msg_id":0			   		
			   		},
			   success: function(data) {
			   		extra_html = "";
			   		$('#truck_note_holder_'+truck_id).html(extra_html + data);
			   }
			 });
		},
		function() {
			$('#truck_note_holder_'+$(this).attr('truck_id')).hide();
		}
	);
		
	
	function mrr_close_preplan_msg_displayer(loadid)
	{
		$('#preplan_note_holder_'+loadid+'').hide();	
	}
	
	$('.preplan_note_image_full').click(
		function() {
			truck_id = $(this).attr('truck_id');
			log_id = $(this).attr('log_id');
			loadid=$(this).attr('load_id');
			datefrom=$(this).attr('load_eta');
						
			$('#preplan_note_holder_'+loadid).html("<img src='images/loader.gif'> Loading PN recent messages...");
			
			$('#preplan_note_holder_'+loadid).show();
			
			extra_html = "";
			
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_quick_message_form_display",
			   data: {
			   		"truck_id":truck_id, 
			   		"disp_id":log_id,
			   		"load_id":loadid,
			   		"driver_id":0,
			   		"date_from":datefrom,
			   		"date_to":'',
			   		"msg_id":0			   		
			   		},
			   success: function(data) {
			   		extra_html = "";
			   		$('#preplan_note_holder_'+loadid).html(extra_html + data);
			   }
			 });
		},
		function() {
			$('#preplan_note_holder_'+$(this).attr('load_id')).hide();
		}
	);
	
	function mrr_close_pn_msg_displayer(log_id)
	{
		$('#pn_note_holder_'+log_id+'').hide();	
	}
	
	$('.pn_note_image_full').click(
		function() {
			truck_id = $(this).attr('truck_id');
			log_id = $(this).attr('log_id');
			loadid=$(this).attr('load_id');
			datefrom=$(this).attr('load_eta');
						
			$('#pn_note_holder_'+log_id).html("<img src='images/loader.gif'> Loading PN recent messages...");
			
			$('#pn_note_holder_'+log_id).show();
			
			extra_html = "";
			
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_quick_message_form_display",
			   data: {
			   		"truck_id":truck_id, 
			   		"disp_id":log_id,
			   		"load_id":loadid,
			   		"driver_id":0,
			   		"date_from":datefrom,
			   		"date_to":'',
			   		"msg_id":0			   		
			   		},
			   success: function(data) {
			   		extra_html = "";
			   		$('#pn_note_holder_'+log_id).html(extra_html + data);
			   }
			 });
		},
		function() {
			$('#pn_note_holder_'+$(this).attr('log_id')).hide();
		}
	);
	
	$('.pn_note_image').hover(
		function() {
			truck_id = $(this).attr('truck_id');
			log_id = $(this).attr('log_id');
			
			$('#pn_note_holder2_'+log_id).html("<img src='images/loader.gif'> Loading PN recent messages...");
			
			$('#pn_note_holder2_'+log_id).show();
			
			extra_html = "";
			
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=load_recent_pn_messages",
			   data: {"truck_id":truck_id,"disp_id":log_id},
			   success: function(data) {
			   		extra_html = "";
			   		$('#pn_note_holder2_'+log_id).html(extra_html + data);
			   }
			 });
		},
		function() {
			$('#pn_note_holder2_'+$(this).attr('log_id')).hide();
		}
	);
	
	function mrr_appt_window_display(stop_id,appt_true,appt_start,appt_end)
	{
		if(stop_id>0)
		{
			$('#stop_'+stop_id+'_appt_window').html("<img src='images/loader.gif'> Loading Appt Window...");			
			$('#stop_'+stop_id+'_appt_window').show();
			
			txt="";
			txt = txt + "<div><b>Appointment Window for Stop "+stop_id+"</b></div>";
			txt = txt + "<div>Ideal Appt Time: "+appt_true+"</div>";
			txt = txt + "<div>&nbsp;</div>";
			txt = txt + "<div><b>Customer expects arrival within range below.</b></div>";
			txt = txt + "<div>Appt Window Start Time: "+appt_start+"</div>";
			txt = txt + "<div>Appt Window End Time: "+appt_end+"</div>";
			txt = txt + "<div>&nbsp;</div>";
									
			$('#stop_'+stop_id+'_appt_window').html(txt);		
		}
	}
	function mrr_appt_window_no_display(stop_id)
	{
		if(stop_id>0)
		{
			$('#stop_'+stop_id+'_appt_window').hide();	 	
		}
	}
	
	function update_location(trailer_id, trailer_name) {
		$.prompt("Enter the new location for trailer '" + trailer_name + "'<br><input id='trailer_location' name='trailer_location' value='' size='60'>",
			{
				callback: function(v, m, f) {
					if(f != undefined) {
						 $.ajax({
						   type: "POST",//lhgj2
						   url: "ajax.php?cmd=update_trailer_location",
						   data: {"trailer_id":trailer_id,
						   		"new_location": f.trailer_location}
						 });
						 $("span[trailer_id='"+trailer_id+"']").attr('popup',"Trailer: "+trailer_name+" <br>Location: " + f.trailer_location);
						 create_tooltips();
					}
					
				},
				loaded: function() {
					$('#trailer_location').focus();
				}
			}
		);
	}
	
	function make_draggable() {
		$('.note_entry').draggable('destroy');
		$('.note_entry').draggable({
			helper: "clone", 
			opacity: 0.7,
			autoSize: true
		});
		
		$('.truck_entry').draggable('destroy');
		
		$('.truck_entry').draggable({
			helper: "clone", 
			opacity: 0.7,
			autoSize: true
		});
	}	
	
	function toggle_map() {
		$('#map_shown').toggle();
		$('#map_hidden').toggle();
	}
	
	function create_tooltips() {

		$('.trailer_entry_in_use span[popup], .trailer_entry_available span[popup]').unbind('hover');
		
		$('.trailer_entry_in_use span[popup]').hover(
			function() {
				$(this).append("<div class='custom_popup_in_use' style='border:1px black solid;position:absolute;margin-left:"+($(this).width()+50)+"px;margin-top:-20px'>" + $(this).attr('popup') + "</div>");
			},
			function() {
				$('.custom_popup_in_use').remove();
			}
		);
		
		$('.trailer_entry_available span[popup]').hover(
			function() {
				$(this).append("<div class='custom_popup_available' style='border:1px black solid;position:absolute;margin-left:"+($(this).width()+50)+"px;margin-top:-20px'>" + $(this).attr('popup') + "</div>");
			},
			function() {
				$('.custom_popup_available').remove();
			}
		);
	}
	
	
	
	
	
	
	$('.truck_drop').droppable({
		accept: ".truck_entry, .note_entry",
		hoverClass: 'droppable_highlight',
		drop: function(ev, ui) { 
			//move_entry(ui, $(this));
			//alert('drop accepted');
			//alert($(ui.draggable).prev().html());
			
			if($(ui.draggable).parent().attr('linedate') == $(this).attr('linedate')) {
				return;
			}
			
			new_day = $(this).attr('linedate');
			
			if($(ui.helper).children().attr('note_id') != undefined) {
				// move note
				//alert("moving note");
				
				 $.ajax({
				   type: "POST",//lhgj2
				   url: "ajax.php?cmd=move_note_day",
				   data: {"note_id":$(ui.helper).children().attr('note_id'),
				   		"linedate": new_day}
				 });
				
				$(this).prepend("<div class='note_entry'>"+$(ui.helper).html()+"</div>");
			} else {
				// move truck
				
				log_id = $(ui.helper).children().attr('log_id');
				
				 $.ajax({
				   type: "POST",//lhgj2
				   url: "ajax.php?cmd=move_truck_day",
				   data: {"log_id":log_id,
				   		"linedate": new_day}
				 });
				
				$(this).prepend("<div class='truck_entry'>"+$(ui.helper).html()+"</div>");
			}
			


			setTimeout(function() { ui.draggable.remove(); }, 1); // yes, even 1ms is enough
			make_draggable();
			
		}		
	});
	
	debug = false;
	
	<? if($_SERVER['REMOTE_ADDR'] == '69.180.227.129') echo "debug = true;"; ?>
	
	$('.trailer_entry_in_use').hover(
		function() {
			$(this).addClass('trailer_hover');
			trailer_id = $(this).children("span").attr('trailer_id');
			//if(debug) alert($(this).children("span").attr('trailer_id'));
			$("span[trailer_id='"+trailer_id+"']").addClass("trailer_hover_other");
		},
		function() {
			trailer_id = $(this).children("span").attr('trailer_id');
			$(this).removeClass('trailer_hover');
			$("span[trailer_id='"+trailer_id+"']").removeClass("trailer_hover_other");
		}
	);
	
	$('.truck_entry_in_use').hover(
		function() {
			$(this).addClass('trailer_hover');
			truck_id = $(this).children("span").attr('truck_id');
			$("span[truck_id='"+truck_id+"']").addClass("trailer_hover_other");
		},
		function() {
			$(this).removeClass('trailer_hover');
			$("span[truck_id='"+truck_id+"']").removeClass("trailer_hover_other");
		}
	);
	
	$('#map_toggle').hover(
		function() {
			$('#map_holder').show();
		},
		function() {
			$('#map_holder').hide();
		}
	);
	
	function mrr_fuel_surcharge_show()
	{
		$('#fuel_surcharge_holder').show();	
	}
	function mrr_fuel_surcharge_hide()
	{
		$('#fuel_surcharge_holder').hide();	
	}
	
	$('.fuel_surcharge').hover(
		function() {
			mrr_fuel_surcharge_show();
		},
		function() {
			mrr_fuel_surcharge_hide();
		}
	);

	function toggle_all_dispatches(full_flag) {
		
		if(full_flag) {
			$('.show_open').show();
			$('.show_all').hide();
			$("div[dispatch_completed_flag='1']").show();
		} else {
			$('.show_open').hide();
			$('.show_all').show();			
			$("div[dispatch_completed_flag='1']").hide();
		}
		
		// store a session variable with the current setting so that we'll automatically show
		// what the user selected previously
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=set_dispatch_display_mode",
		   data: {"show_all":full_flag},
		   success: function(data) {
		   		
		   }
		 });
	}

	function toggle_calendar_full(full_flag,alt_flag) 
	{	
		var full_view;	
		var alt_view="index.php";	
		if(full_flag) {
			
			//$('.toggle_full').hide();
			//$('.toggle_small').show();
			//$('.optional_day').show();
			
			$('.mrr_full_calendar').show();
			$('.mrr_small_calendar').show();
			$('.optional_day').show();
			
			$('#show_small_calendar').html('Show Small Calendar');
			$('#show_full_calendar').html('FULL CALENDAR');
			$('#show_new_calendar').html('Show New Board');			
			
			full_view = 1;
			alt_view="index.php?mCheck=1&mrr_calendar_mode=1";
		} else {
			
			//$('.toggle_small').hide();
			//$('.toggle_full').show();
			//$('.optional_day').hide();
						
			$('.optional_day').hide();
			$('.mrr_full_calendar').hide();
			$('.mrr_small_calendar').show();	
			
			$('#show_small_calendar').html('SMALL CALENDAR');
			$('#show_full_calendar').html('Show Full Calendar');
			$('#show_new_calendar').html('Show New Board');			
			
			full_view = 0;
			alt_view="index.php?mCheck=1&mrr_calendar_mode=0";
		}
		
		/*		
		if(alt_flag)
		{
			alt_view="index.php?mCheck=1&mrr_calendar_mode=2;"	//&alt_mode=1";
		}
		// store a session variable with the current setting so that we'll automatically show
		// what the user selected previously
		*/
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=set_calendar_display_mode",
		   async: false,
		   data: {"full_view":full_view},
		   success: function(data) {
	   			//window.location = alt_view;
		   }
		 });
		
		
		
		//remove the days from the calendar that have past and have no items on them...
		past_hdrs=0;
		optional_week_days=0;		
		$(".date_in_past_header").each(function(){
          	past_hdrs++; 
		});
		
		past_info=0;
		optional_week_days=0;		
		$(".date_in_past_info").each(function(){
			
			//get date in hidden attribute for column
			//mydater=$(this).attr('mrr_hdr_id');
			
			//now remove the column header and column if this is present...regular load board
			if($(this).attr('mrr_col_1_id'))
			{
				mydater=$(this).attr('mrr_col_1_id');
				$('td[mrr_hdr_1_id="'+mydater+'"]').hide();				
				$(this).hide();
			}
			
			//now remove the column header and column if this is present...DEDICATED load board
			if($(this).attr('mrr_col_2_id'))
			{
				mydater=$(this).attr('mrr_col_2_id');
				$('td[mrr_hdr_2_id="'+mydater+'"]').hide();				
				$(this).hide();
			}			
			
          	past_info++; 
		});
		/*
		if(past_hdrs==past_info)
		{ 
			$(".date_in_past_header").hide();
			$(".date_in_past_info").hide();
		}
		*/
		
		/*
		optional_week_days=0;		
		$(".optional_week_day").each(function(){
          	optional_week_days++; 
		});
		
		if(optional_week_days==7 || optional_week_days==14)
		{ 
			$(".optional_week_day").hide();
			$(".optional_week").hide();
		}
		*/
	}
	
	function toggle_trailers() {
		$('.trailer_entry_in_use').toggle();
	}
	
	function toggle_trucks() {
		$('.truck_entry_in_use').toggle();
	}
	
	function update_stop_complete(load_handler_stop_id,stop_typer) {
		var mrr_date='<?= date("m/d/Y") ?>';
		var mrr_time='<?= date("H:i") ?>';
		var mrr_grader="<?=$mrr_gen_grader_selector ?>";
		
		var mrr_gmt_day="<?= $mrr_tz_gmt_day ?>";					var mrr_gmt_hrs="<?= $mrr_tz_gmt ?>";
		var mrr_ast_day="<?= $mrr_tz_ast_day ?>";					var mrr_ast_hrs="<?= $mrr_tz_ast ?>";
		var mrr_est_day="<?= $mrr_tz_est_day ?>";					var mrr_est_hrs="<?= $mrr_tz_est ?>";
		var mrr_cst_day="<?= $mrr_tz_cst_day ?>";					var mrr_cst_hrs="<?= $mrr_tz_cst ?>";
		var mrr_mst_day="<?= $mrr_tz_mst_day ?>";					var mrr_mst_hrs="<?= $mrr_tz_mst ?>";
		var mrr_pst_day="<?= $mrr_tz_pst_day ?>";					var mrr_pst_hrs="<?= $mrr_tz_pst ?>";
		
		
		fun="document.getElementById('popup_stop_date_completed').value='"+mrr_date+"'; document.getElementById('popup_stop_time_completed').value='"+mrr_time+"';";
		
		fun1="document.getElementById('popup_stop_date_completed').value='"+mrr_gmt_day+"'; document.getElementById('popup_stop_time_completed').value='"+mrr_gmt_hrs+"';";
		fun2="document.getElementById('popup_stop_date_completed').value='"+mrr_ast_day+"'; document.getElementById('popup_stop_time_completed').value='"+mrr_ast_hrs+"';";
		fun3="document.getElementById('popup_stop_date_completed').value='"+mrr_est_day+"'; document.getElementById('popup_stop_time_completed').value='"+mrr_est_hrs+"';";
		fun4="document.getElementById('popup_stop_date_completed').value='"+mrr_cst_day+"'; document.getElementById('popup_stop_time_completed').value='"+mrr_cst_hrs+"';";
		fun5="document.getElementById('popup_stop_date_completed').value='"+mrr_mst_day+"'; document.getElementById('popup_stop_time_completed').value='"+mrr_mst_hrs+"';";
		fun6="document.getElementById('popup_stop_date_completed').value='"+mrr_pst_day+"'; document.getElementById('popup_stop_time_completed').value='"+mrr_pst_hrs+"';";
		
		//fun2="document.getElementById('popup_stop_date_completed').value=''; document.getElementById('popup_stop_time_completed').value='';";
		
		prompt_txt = "<table border='0' cellpaddoing='0' cellspacing='0' width='100%'>";
		
		prompt_txt = prompt_txt + "<tr><td colspan='3'>Enter the Date Completed for this stop</td></tr>";
		
		prompt_txt = prompt_txt + "<tr>";
		prompt_txt = prompt_txt + "<td>Date Completed</td>";
		prompt_txt = prompt_txt + 	"<td>Time</td>";
		if(stop_typer!=1)
		{
			prompt_txt = prompt_txt + "<td><span class='mrr_link_like_on' title='click to switch out trailers or drop the current trailer at this stop' onClick='mrr_drop_switched_trailer_js_alt("+load_handler_stop_id+",1);'><b>Drop or Switch Trailers?</b></span></td>";
		}
		else
		{
			prompt_txt = prompt_txt +  "<td>&nbsp;</td>";		
		}
		
		//prompt_txt = prompt_txt +  "<td></td>";
		prompt_txt = prompt_txt + "</tr>";
		prompt_txt = prompt_txt + "<tr>";
		prompt_txt = prompt_txt + 	"<td><input name='popup_stop_date_completed' id='popup_stop_date_completed'></td>";
		prompt_txt = prompt_txt + 	"<td><input name='popup_stop_time_completed' id='popup_stop_time_completed'></td>";
		prompt_txt = prompt_txt +  	"<td>Now: {Click on the time zone below.}</td>";		
		//prompt_txt = prompt_txt + 	"<td><span class='mrr_link_like_on' title='click to put the current date and time' onClick=\""+fun+"\"><b>Now</b></span></td>";
		//prompt_txt = prompt_txt + "</td><span class='mrr_link_like_on' title='click to clear the current date and time' onClick=\""+fun2+"\"><b>Clear</b></span><td>";      
		prompt_txt = prompt_txt + "</tr>";
		
		prompt_txt = prompt_txt + "</tr>";
		prompt_txt = prompt_txt +  "<td>&nbsp;</td>";	
		prompt_txt = prompt_txt +  "<td>&nbsp;</td>";	
		prompt_txt = prompt_txt + 	"<td>";
		//prompt_txt = prompt_txt + 		" <span class='mrr_link_like_on' title='click to put the current GMT date and time.' onClick=\""+fun1+"\"><b>GMT</b></span>";
		prompt_txt = prompt_txt + 		" <span class='mrr_link_like_on' title='click to put the current AST date and time.' onClick=\""+fun2+"\"><b>Atlantic</b></span>";
		prompt_txt = prompt_txt + 		" <span class='mrr_link_like_on' title='click to put the current EST date and time.' onClick=\""+fun3+"\"><b>Eastern</b></span>";
		prompt_txt = prompt_txt + 		" <span class='mrr_link_like_on' title='click to put the current CST date and time.' onClick=\""+fun4+"\"><b>Central</b></span>";
		prompt_txt = prompt_txt + 		" <span class='mrr_link_like_on' title='click to put the current MST date and time.' onClick=\""+fun5+"\"><b>Mountain</b></span>";
		prompt_txt = prompt_txt + 		" <span class='mrr_link_like_on' title='click to put the current PST date and time.' onClick=\""+fun6+"\"><b>Pacific</b></span>";
		prompt_txt = prompt_txt + 	"</td>";
		prompt_txt = prompt_txt + "</tr>";
		
		prompt_txt = prompt_txt + "<tr>";
		prompt_txt = prompt_txt + 	"<td colspan='2'>Grade</td>";
		prompt_txt = prompt_txt + 	"<td>Grade Note/Reason</td>";  
		prompt_txt = prompt_txt + "</tr>";
		
		prompt_txt = prompt_txt + "<tr>";
		prompt_txt = prompt_txt + 	"<td colspan='2'>"+mrr_grader+"</td>";
		prompt_txt = prompt_txt + 	"<td><textarea name='popup_stop_grade_note' id='popup_stop_grade_note' rows='3' cols='30' wrap='virtual'></textarea></td>";  
		prompt_txt = prompt_txt + "</tr>";
				
		prompt_txt = prompt_txt + "</table>";
		
		function callbackfunc(v,m,f) {
			if(v) {
			      if(f != undefined && f.popup_stop_date_completed != '') {
					if(f.popup_stop_time_completed.length != 5) {
						$.prompt("You must enter the time this stop was completed");
						return false;
					}
					
					if(f.popup_stop_date_completed == '') {
						$.prompt("You must enter the date this stop was completed");
						return false;
					}
					
					if(f.popup_stop_grade_id == 0) {
						$.prompt("Please Grade this stop based on the date completed...");
						return false;
					}
					
					if((f.popup_stop_grade_id >= 10 || ( f.popup_stop_grade_id >= 1 && f.popup_stop_grade_id <= 4)) && f.popup_stop_grade_note.length < 4) {
						$.prompt("Please use the Grade Note/Reason section to indicate why this was late.");
						return false;
					}
					
					 $.ajax({
					   	type: 'POST',
					   	url: 'ajax.php?cmd=update_stop_completed_no_arrival',
					   	data: {
					   		stop_id:load_handler_stop_id,
					   		linedate_completed:f.popup_stop_date_completed,
					   		linedate_completed_time:f.popup_stop_time_completed,
					   		"stop_grade_id":f.popup_stop_grade_id,
					   		"stop_grade_note":f.popup_stop_grade_note
					   		},
					   	dataType: "xml",
				   		cache:false,					   	
					   	error: function() {
					   			//$.prompt("<span class='alert'>ERROR:</span> There was an error updating the date/time, please try again.");
							},
					  	success: function(xml)  {
					   		resulter=$(xml).find('rslt').text();
					   		if(resulter==1)
					   		{
					   			$.prompt("Stop Date Completed - Saved successfully");
					   		}
					   		else
					   		{
					   			$.prompt("<span class='alert'>ERROR:</span> "+$(xml).find('ErrorMsg').text()+".");	
					   		}					   		
					   }
					 });					
			     }	
			}
		}
		
		function loadedfunc() {
				$('#popup_stop_date_completed').datepicker();
				$('#popup_stop_date_completed').focus();
				$('#popup_stop_time_completed').blur(simple_time_check);
				$('#popup_stop_grade_id').val(0);
				$('#popup_stop_grade_note').val('');
		}
		
		$.prompt(prompt_txt,{
			buttons: { Ok: 'Ok', Cancel: false },
			callback:callbackfunc,
			loaded:loadedfunc
		});
	}
	
	create_tooltips();
	
	<?
	/*
	if(isset($_SESSION['full_calendar_view_flag']) && $_SESSION['full_calendar_view_flag']) {
		//echo " toggle_calendar_full(true); ";
		echo " $('.optional_day').show(); ";
	}
	*/
	
	if($mrr_calendar_mode==0)
	{
		echo " toggle_calendar_full(false,true); ";
	}
	elseif($mrr_calendar_mode==1)
	{
		echo " toggle_calendar_full(true,true); ";
	}
	elseif($mrr_calendar_mode==2)
	{
		//echo " toggle_calendar_full(false,false); ";
	}
	
	
	if(isset($_SESSION['show_all_dispatches']) && $_SESSION['show_all_dispatches']) {
		echo " toggle_all_dispatches(1); ";
	}
	?>
	

	
	function load_odometer_alert() {
		
		// load the odometer list
		 $.ajax({
				   type: "POST",
				   url: "ajax.php?cmd=truck_odometer_alert",
				   data: {},
				   dataType: "xml",
				   cache:false,
				   success: function(xml) {
				   		if($(xml).find("TruckCount").text() == '0') {
				   			// no trucks left needing an odometer reading, hide the alert
				   			$('#truck_odometer_alert').hide();
				   		} else {
				   			$('#truck_odometer_alert').show();
							$('#truck_odometer_alert').html($(xml).find('TruckList').text());
						}
				   }
			});
	}
	
	<? 
	if(date("t") - date("j") <= 4) {
		echo "load_odometer_alert();";
	} 
	?>
	
	function remove_available_driver(driver_id, driver_name) {
		txt = "<table>";
		txt += "<tr><td colspan='2'><label><input type='checkbox' name='remove_unavailable' id='remove_unavailable'> Remove '"+driver_name+"' from available list</label> <br>(option can be changed in the admin drivers page)</td></tr>";
		txt += "<tr><td colspan='2'><hr></td></tr>";
		txt += "<tr><td colspan='2'>Temporary Unavailable Date Range</td></tr>";
		txt += "<tr><td>Date From:</td><td><input name='date_unavailable_from' id='date_unavailable_from' class='popup_date_picker'></td></tr>";
		txt += "<tr><td>Date To:</td><td><input name='date_unavailable_to' id='date_unavailable_to' class='popup_date_picker'></td></tr>";
		txt += "</table>";
		$.prompt(txt,{
			buttons: {Okay: true, Cancel: false},
			loaded: function() {
				$('.popup_date_picker').datepicker();
			},
			submit: function(v, m, f) {
				if(v) {
					if($('#remove_unavailable').attr('checked')) {
						// remove permanently
						$.ajax({
							url: "ajax.php?cmd=driver_unavailable",
							dataType: "xml",
							type: "post",
							data: {
								driver_id: driver_id
							},
							success: function(xml) {
								$('#available_driver_holder_'+driver_id).hide();
								$.noticeAdd({text: "Success - '"+driver_name+"' removed from Unavailable List"});
							}
						});
					} else {
						if(f.date_unavailable_from == '' || f.date_unavailable_to == '') {
							$.prompt("You must enter the 'Date From' and 'Date To' that '"+driver_name+"' is unavailable");
							return false;
						}
						
						$.ajax({
							url: "ajax.php?cmd=add_driver_unavailability",
							dataType: "xml",
							type: "post",
							data: {
								driver_id: driver_id,
								linedate_start: f.date_unavailable_from,
								linedate_end: f.date_unavailable_to
							},
							success: function(xml) {
								$('#available_driver_holder_'+driver_id).hide();
								$.noticeAdd({text: "Success - '"+driver_name+"' removed from Unavailable List for the date range " + f.date_unavailable_from + " to " + f.date_unavailable_to + "."});
							}
						});
					}
					//alert('still being worked on.... - sorry');
				}
			}
		});
	}
	
	function view_driver_history(driver_id) {
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=load_driver_history",
			   data: {"driver_id":driver_id},
			   dataType: "xml",
			   cache:false,
			   success: function(xml) {
				$.prompt($(xml).find('DispHTML').text(),{
					buttons: {Close: true}
				});
			   }
			 });
	}
	
	function view_truck_history(truck_id) {
		$.ajax({
			url: "ajax.php?cmd=load_truck_history",
			type: "post",
			dataType: "xml",
			data: {
				truck_id: truck_id
			},
			success: function(xml) {
				$.prompt($(xml).find('DispHTML').text(),{
					buttons: {Close: true}

				});
			}
		});
	}
	
	function display_note_with_deadlines()
	{
		$.ajax({
			url: "ajax.php?cmd=mrr_truck_note_deadlined",
			type: "post",
			async: false,	
			dataType: "xml",     					
			data: {
				//POST variables needed for "page" to load for XML output
				test_id: ""
			},
			error: function() {
				alert('general error displaying notes.');
			},
			success: function(xml) {
				$(xml).find('Deadliner').each(function() {
					
          			my_id = $(xml).find('NoteID').text();
     				my_notice = $(xml).find('Notice').text();
     				my_deadline = $(xml).find('Deadline').text();
     				
     				txt="Deadline "+my_deadline+": "+my_notice+"";	
     				
     				alert(txt);
     				//$.noticeAdd({text: txt});	
				});
    			}
		});		
	}
	display_note_with_deadlines();
	
	function add_note(dispatch_id) {
		txt="";
		txt+="Enter your note for dispatch ID: "+dispatch_id+"<br>";
		txt+="<textarea style='width:400px;height:100px' name='new_note' id='new_note'></textarea><br><br>";
		$.prompt(txt, {
			buttons: {Okay: true, Cancel: false},
			loaded: function() {
				$('#new_note').focus();
				$('#added_note_deadline').datepicker();
			},
			submit: function(v, m, f) {
				if(v) {
					if(f.new_note == '') {
						$.prompt.close();
						return false;
					}
					
					$.ajax({
						url: "ajax.php?cmd=add_truck_note",
						dataType: "xml",
						type: "post",
						data: {
							dispatch_id: dispatch_id,
							note: f.new_note 
						},
						success: function(xml) {
							$.noticeAdd({text: "Success - note was saved to dispatch " + dispatch_id});
						}
					});
					
					
				}
			}
		});
	}
	
	function detach_truck(truck_id, driver_id) {
		var truck_name = "";
		var driver_name = "";
		
		$.ajax({
			url:"ajax.php?cmd=get_detach_info",
			dataType: "xml",
			type: "post",
			async: false,
			data: {
				truck_id: truck_id,
				driver_id: driver_id
			},
			success: function(xml) {
				truck_name = $(xml).find('TruckName').text();
				driver_name = $(xml).find('DriverName').text();
			}
		});
		
		$.prompt("Are you sure you want to detach truck '"+truck_name+"' from driver '"+driver_name+"'?", {
			buttons: {Yes: true, No: false},
			submit: function (v, m, f) {
				if(v) {
					$.ajax({
						url: "ajax.php?cmd=detach_truck",
						type: "post",
						dataType: "xml",
						data: {
							truck_id:truck_id,
							driver_id: driver_id
						},
						error: function() {
							$.prompt("Error detaching truck - please try again later");
						},
						success: function(xml) {
							$('.attached_truck_'+truck_id).remove();
							$.noticeAdd({text: "Success - Truck '"+truck_name+"' was detached from driver '"+driver_name+"' "});							
						}
					});
					

				}
			}
		});
	}
	
	function detach_trailer(trailer_id, driver_id) {
		
		var trailer_name = "";
		var driver_name = "";
		
		$.ajax({
			url:"ajax.php?cmd=get_detach_info",
			dataType: "xml",
			type: "post",
			async: false,
			data: {
				trailer_id: trailer_id,
				driver_id: driver_id
			},
			success: function(xml) {
				trailer_name = $(xml).find('TrailerName').text();
				driver_name = $(xml).find('DriverName').text();
			}
		});
		
		$.prompt("Are you sure you want to detach trailer '"+trailer_name+"' from driver '"+driver_name+"'?", {
			buttons: {Yes: true, No: false},
			submit: function (v, m, f) {
				if(v) {
					$.ajax({
						url: "ajax.php?cmd=detach_trailer",
						type: "post",
						dataType: "xml",
						data: {
							trailer_id:trailer_id,
							driver_id: driver_id
						},
						error: function() {
							$.prompt("Error detaching trailer - please try again later");
						},
						success: function(xml) {
							$('.attached_trailer_'+trailer_id).remove();
							$.noticeAdd({text: "Success - Trailer '"+trailer_name+"' was detached from driver '"+driver_name+"' "});							
						}
					});
				}
			}
		});
	}	

	function has_load_toggle(driver_id) {
		$.prompt("Change Driver Load Status<hr>Does this driver currently have a load?", {
			buttons: {Yes: 1, No: 0},
			submit: function(v, m, f) {
				if(v == 1 || v == 0) {
					$.ajax({
						url: "ajax.php?cmd=driver_load_flag",
						type: "post",
						dataType: "xml",
						data: {
							driver_id: driver_id,
							load_flag: v
						},
						success: function(xml) {
							$.noticeAdd({text: "Success - Driver load flag updated "});
							if(v) {
								$('#driver_has_load_'+driver_id).show()
							} else {
								$('#driver_has_load_'+driver_id).hide()
							}
						}
						
					});
				}
			}
		});
	}
	
	function mrr_load_background_distances()
	{
		$.ajax({
				url: "ajax.php?cmd=mrr_update_pn_mileage_values",
				type: "post",
				dataType: "xml",
				data: {
					'run':1
				},
				success: function(xml) {
					
				}						
		});	
	}
	
</script>
<?
//}
?>

	<div style='clear:both'></div>
	
	<center>
		<?
		
		if($_SERVER['REMOTE_ADDR'] == '50.76.161.186' || 1 == 1) {
			
			echo "
				query count: ($query_count)
				page load time: ".show_page_time()."
			"; 
			/*
			echo "<br><br>";
			foreach($page_timer_array as $key => $ptime) {
				echo "($key: $ptime)<br>";
			}
			*/
		}
		
		?>
	</center>
	
	<?
/* Page Load speed checking code. */
$mrr_debug_time_end=date("His");
echo "
	<br><b>PHP Page Load:</b>
	<br>Start Time: ".$mrr_debug_time_start."
	<br>End Time: ".$mrr_debug_time_end."
	<br>Load Time: ".number_format(($mrr_debug_time_end - $mrr_debug_time_start),4)." Seconds.
	<br><div id='ajax_time_keeper'></div>
";
?>
		