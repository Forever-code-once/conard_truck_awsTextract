<? include('application.php') ?>
<?
	$mrr_save_preloader=0;
     $mrr_run_preloader=0;
     if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' || $_SERVER['REMOTE_ADDR'] == '173.10.208.206')
     {
     	$mrr_save_preloader=0;
     	$mrr_run_preloader=1;
     }
?>
<? include('functions_index_page2vol.php') ?>
<?
	$sql = "update trailers set deleted = 1	where trailer_name='unkown'";
	simple_query($sql);
	
	$page_timer_array[] = "After application and initial includes: " . show_page_time();
	$mrr_debug_time_start=date("His");		//for page load speed checks...used at bottom of the page.
	
	if(isset($_SESSION['inventory_access'])) {
		javascript_redirect("login.php?out=1");
	}
	
	
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
	$enddate = strtotime("+6 days",$startdate);
	
	$display_start=date("m/d/Y",$startdate);
	$display_end=date("m/d/Y",$enddate);
	
	
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
	
	$page_timer_array[] = "After data_avail_loads: " . show_page_time();
	
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
	
	$page_timer_array[] = "After data_avail_drivers: " . show_page_time();
	
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
	
	$page_timer_array[] = "After data_trucks_used: " . show_page_time();
	
	/* build an array of truck IDs that are currently being used so we can quickly search them later */
	$trucks_array = array();
	while($row_trucks_used = mysqli_fetch_array($data_trucks_used)) {
		$trucks_array[] = $row_trucks_used['truck_id'];
	}
	
	/* grab our list of all 'non-deleted' trucks in our database */						// and drivers.deleted = 0 and drivers.active = 1
	// *** subquery is a potential slow point ***
	$sql = "
		select trucks.id,
			trucks.name_truck,
			trucks.in_the_shop,
			trucks.in_body_shop,
			trucks.hold_for_driver,
			trucks.in_shop_note,
			trucks.in_body_note,
			trucks.on_hold_note,
			
			(select drivers.attached_truck_id from drivers where drivers.attached_truck_id=trucks.id limit 1) as attached_truck_id,
			(select drivers.name_driver_first from drivers where drivers.attached_truck_id=trucks.id limit 1) as name_driver_first,
			(select drivers.name_driver_last from drivers where drivers.attached_truck_id=trucks.id limit 1) as name_driver_last,
			(select drivers.id from drivers where drivers.attached_truck_id=trucks.id limit 1) as driver_id,
						
			IFNULL((
               	SELECT count(*) 
               	FROM trucks_log t2
               	WHERE t2.deleted=0               		
               		AND t2.truck_id=trucks.id
               		AND t2.truck_id > 0
               		AND t2.linedate_pickup_eta >= '".date("Y-m-d", time())."'
               	ORDER BY t2.linedate_pickup_eta ASC
               	
               	),0) AS next_dispatch_id,
			
			IFNULL((
               	SELECT count(*) 
               	FROM load_handler lh
               		left join drivers on lh.preplan_driver_id=drivers.id
               	WHERE lh.deleted=0
               		AND lh.preplan > 0
               		AND lh.preplan_driver_id > 0
               		AND lh.linedate_pickup_eta >= '".date("Y-m-d", time())."'
               	ORDER BY lh.linedate_pickup_eta ASC
               	limit 1
               	),0) AS next_load_id,
			
			ifnull((
				select max(trucks_log.linedate)
				
				from trucks_log
				where truck_id = trucks.id
					and trucks_log.deleted = 0
					and trucks_log.linedate > '".date("Y-m-d", strtotime("-1 month", time()))."'
					and trucks_log.linedate < '".date("Y-m-d", strtotime("+2 day", time()))."'
			),0) as linedate_last_moved
			
		from trucks
			
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
	$page_timer_array[] = "After data_trucks: " . show_page_time();
	
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
	$page_timer_array[] = "After data_trailers_used: " . show_page_time();
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
	$page_timer_array[] = "After get_available_trailers: " . show_page_time();
	
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
	$page_timer_array[] = "After data_trailers: " . show_page_time();
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
	
	$page_timer_array[] = "After driver loop: " . show_page_time();



?>
<? include('header.php') ?>
<?
	if(!isset($new_style_path))
	{
		$new_style_path="images/2012/";	
	}
	
	$page_timer_array[] = "After header.php: " . show_page_time();
	
	//added April 2012 so that Maint flags are all found in one group/set in one query instead of every point.
	$mrr_maint_resx=mrr_get_equipment_maint_notice_warning_array('truck');
	$mrr_maint_truck_cntr=$mrr_maint_resx['num'];
	$mrr_maint_truck_arr=$mrr_maint_resx['arr'];
	$mrr_maint_truck_links=$mrr_maint_resx['links'];
	
	$mrr_maint_resy=mrr_get_equipment_maint_notice_warning_array('trailer');
	$mrr_maint_trailer_cntr=$mrr_maint_resy['num'];
	$mrr_maint_trailer_arr=$mrr_maint_resy['arr'];
	$mrr_maint_trailer_links=$mrr_maint_resy['links'];
	
	
	$page_timer_array[] = "After maint notices: " . show_page_time();

	//functions for new components...this is so they can be used in other places later...or called by AJAX functions...Added June 2012.

	
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
?>
<SCRIPT Language="Javascript">
	setTimeout("location.reload();", (600 * 1000));		//ten minutes...600 seconds...1000=1 second
	$().ready(function() {
		mrr_hide_all_section();
		
		setInterval( function() 
          {
              	mrr_fetch_sent_messages_home(); 		//new_pn_messages
              	
              	mrr_fetch_geofencing_report_home();	//new_pn_geofencing
              	         	
          },(2000 * 60));		//1 minutes...1000=1 second
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
               	
               	
               	
				//if($_SERVER['REMOTE_ADDR'] != '70.90.229.29') { 
				?>	
               	<? $page_timer_array[] = "Before mrr_conard_component sections: " . show_page_time(); ?>
					<?= mrr_conard_component_time() ?>
					<?= mrr_conard_component_fuel() ?>
					<?= mrr_conard_component_tool() ?>
					<?= mrr_conard_component_im_msgs() ?>
					<?= mrr_conard_component_truck_movement() ?>
					<?= mrr_conard_component_truck_last_load() ?>
					<?= mrr_conard_component_load_available() ?>
					<?= mrr_conard_component_list_trailers() ?>
					<?= mrr_conard_component_list_trucks() ?>
				<? 
				//} 
				
				
				
				?>	
				<? $page_timer_array[] = "After mrr_conard_component sections: " . show_page_time(); ?>
			</div>
               <div class="container_right_part">
               	<? 
				//if($_SERVER['REMOTE_ADDR'] != '70.90.229.29') { 
				?>	
					<?= mrr_conard_component_calendar($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year']) ?>
					<? $page_timer_array[] = "After mrr_conard_component_calendar: " . show_page_time(); ?>
					<?= mrr_conard_component_peoplenet_msgs() ?>
					<? $page_timer_array[] = "After mrr_conard_component_peoplenet_msgs: " . show_page_time(); ?>					
					<?= mrr_conard_component_timeoff() ?>
					<? $page_timer_array[] = "After mrr_conard_component_timeoff: " . show_page_time(); ?>
					<?= mrr_conard_component_events() ?>
					<? $page_timer_array[] = "After mrr_conard_component_events: " . show_page_time(); ?>
					<?= mrr_conard_component_notes() ?>
					<? $page_timer_array[] = "After mrr_conard_component_notes: " . show_page_time(); ?>
					<?= mrr_conard_component_geofencing_msgs() ?>
					<? $page_timer_array[] = "After mrr_conard_component_geofencing_msgs: " . show_page_time(); ?>
				<? 
				
				//} 
				?>	
				
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
				//if($_SERVER['REMOTE_ADDR'] != '70.90.229.29') { 
					// && $_SERVER['REMOTE_ADDR'] != '70.90.229.29'
				?>		
				<?
				
				$mrr_run_preloader=1;
				
				if($mrr_calendar_mode==0 || $mrr_calendar_mode==1)
				{	//small calendar
					
					$mrr_big_boards=truck_section_mrr_alt();
					
					$page_timer_array[] = "After truck_section_mrr_alt (1): " . show_page_time();
					echo "".$mrr_big_boards;				//Small Calendar<br>					
				}
				elseif($mrr_calendar_mode==1)
				{	//full calendar
					$mrr_big_boards=truck_section_mrr_alt();
					
					$page_timer_array[] = "After truck_section_mrr_alt (2): " . show_page_time();
					echo "".$mrr_big_boards;				//Full Calendar<br>						
				}
				elseif($mrr_calendar_mode==2)
				{	//new board
										
					$mrr_big_board_normal=mrr_conard_component_display_company($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],"",0,$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year']);
					$page_timer_array[] = "After mrr_conard_component_display_company: " . show_page_time();
					
					$mrr_big_board_dedicated=mrr_generate_dedicated_sections($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year']);
					
					//$mrr_big_board_dedicated=mrr_conard_component_display_company($_POST['use_mon'],$_POST['use_day'],$_POST['use_year'],"Dedicated Loads",1,$_POST['cal_mon'],$_POST['cal_day'],$_POST['cal_year']); 	
					
					$page_timer_array[] = "After mrr_conard_component_display_company: " . show_page_time();
					
						
					echo "".$mrr_big_board_normal;		//New Board<br>
					echo $mrr_big_board_dedicated;
					
					//NOTE: These are not the right board generator functions any more...see function truck_section_mrr_alt() in functions_index_page.php
				}
				?>				
				<? 
				//} 
				?>				
				<div class='mrr_force_reloader'>
					<span class='mrr_force_reloader_msg'></span>
					<br>
					<br>
					<br>
					<span class='mrr_force_reloader_button' onClick='mrr_force_index_reload();'>Force Data Reload</span>
				</div>
			</div>
		</div>
	</div>	

<?

	
?>
<? 
//if($_SERVER['REMOTE_ADDR'] != '70.90.229.29') { 
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
	

	
	function mrr_quick_quote(truckid,driverid,trailerid)
	{
		windowname = window.open('quote.php?truck_id=' + truckid + '&trailer_id='+trailerid+'&driver_id='+driverid ,'new_quote',''+mrr_window_sizer_quote+'');
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
	
	
	function mrr_force_index_reload()
	{
		$('.mrr_force_reloader_msg').html('...Wait for it....');	
		
		$.ajax({
		   type: "POST",
		   url: "mrr_index_preloader.php",
		   data: {},
		   async:false,
		   success: function(data) {
		   	$('.mrr_force_reloader_msg').html('Reloading the page.');	
		   	location.reload();
		   }
		 });		
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
//if($_SERVER['REMOTE_ADDR'] != '70.90.229.29') { 
?>

<script type='text/javascript'>
	
	var tooltips_loaded = false;
	var customer_surcharge_list_loaded = false;
	var show_details = false;
	var show_object_after_wait = "";
	var hide_details = false;
	var hide_object_after_wait = "";
	
	var optional_week_days=0;
	
	var my_date_from = "<?=$display_start ?>";
	var my_date_to = "<?=$display_end ?>";
		
	
	$().ready(function() {
		
		mrr_swap_section(1);
		
		$('#ajax_time_keeper').html('');
		var startTime = new Date();
		
		var startTime1 = new Date();
		mrr_window_sizer 	= 	'height='+mrr_default_window_size_dispatch_height+',width='+mrr_default_window_size_dispatch_width+',menubar=no,location=no,resizable=yes,status=no,scrollbars=yes';
		mrr_window_sizer_misc = 	'height='+mrr_default_window_size_misc_height+',width='+mrr_default_window_size_misc_width+',menubar=no,location=no,resizable=yes,status=no,scrollbars=yes';
		mrr_window_sizer_load = 	'height='+mrr_default_window_size_load_height+',width='+mrr_default_window_size_load_width+',menubar=no,location=no,resizable=yes,status=no,scrollbars=yes';
		mrr_window_sizer_quote = 'height='+mrr_default_window_size_load_height+',width=1700,menubar=no,location=no,resizable=yes,status=no,scrollbars=yes';
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
			
			//now remove the column header and column if this is present...DEDICATED load board Ingram
			if($(this).attr('mrr_col_2_id'))
			{
				mydater=$(this).attr('mrr_col_2_id');
				$('td[mrr_hdr_2_id="'+mydater+'"]').hide();				
				$(this).hide();
			}	
			
			//now remove the column header and column if this is present...DEDICATED load board Pace Runner
			if($(this).attr('mrr_col_3_id'))
			{
				mydater=$(this).attr('mrr_col_3_id');
				$('td[mrr_hdr_3_id="'+mydater+'"]').hide();				
				$(this).hide();
			}	
			
			//now remove the column header and column if this is present...DEDICATED load board 
			if($(this).attr('mrr_col_4_id'))
			{
				mydater=$(this).attr('mrr_col_4_id');
				$('td[mrr_hdr_4_id="'+mydater+'"]').hide();				
				$(this).hide();
			}
			
			//now remove the column header and column if this is present...DEDICATED load board 
			if($(this).attr('mrr_col_5_id'))
			{
				mydater=$(this).attr('mrr_col_5_id');
				$('td[mrr_hdr_5_id="'+mydater+'"]').hide();				
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
		
				
		//retrieve driver hours from ajax...
		$(".avail_driver_hours").each(function(){
						
			if($(this).attr('mrr_driver_id'))
			{
				myid=$(this).attr('mrr_driver_id');				
				mrr_load_driver_hours_for_week('avail',myid);				
			}
		});		
		
		//retrieve preplanned driver hours from ajax...
		$(".preplan_driver_hours").each(function(){
						
			if($(this).attr('mrr_driver_id'))
			{
				myid=$(this).attr('mrr_driver_id');				
				mrr_load_driver_hours_for_week('preplan',myid);
			}
		});
				
		
		//$('#ajax_time_keeper').html('<b>Ajax Ready Time Report:</b> '+time_report+'');
		
		mrr_set_marquee();
	});	
	
	function mrr_load_driver_hours_for_week(sect,myid)
	{
		if(parseInt(myid) > 0)
		{		
			$.ajax({
          			url: 'ajax.php?cmd=mrr_ajax_driver_hours_for_week', 
          			dataType: "xml",
          			type: "post",
          			data: {
          				"driver_id": myid,
          				"date_from": my_date_from,
          				"date_to":my_date_to
          			},
          			cache: false,
          			success: function(xml) {
          				
          				mytext="";          				
          				//dispatch version with no PN ties...
          				/*         				
          				my=$(xml).find('Driver').text();
          				my=$(xml).find('From').text();
          				my=$(xml).find('To').text();
          				          				
          				my=$(xml).find('Speeding').text();
          				my=$(xml).find('Shutdowns').text();
          				my=$(xml).find('Num').text();
          				my=$(xml).find('SQL').text();
          				*/
          				//$.prompt(mytext);
          				mytext=mytext + "Dispatch Week Hours: ";
          				//mytext=mytext + "Driven "+$(xml).find('DrivenHrs').text()+", ";
          				//mytext=mytext + "Rested "+$(xml).find('RestedHrs').text()+", ";
          				//mytext=mytext + "Worked "+$(xml).find('WorkedHrs').text()+". ";
          				//mytext=mytext + "Week: ";
          				mytext=mytext + "Driven "+$(xml).find('WkDrivenHrs').text()+", ";
          				mytext=mytext + "Rested "+$(xml).find('WkRestedHrs').text()+", ";
          				mytext=mytext + "Worked "+$(xml).find('WkWorkedHrs').text()+".";
          				
          				if(parseInt($(xml).find('Violations').text()) > 0)
          				{
          					mytext=mytext + " Warnings: ";
          					
          					if(parseInt($(xml).find('VioTen').text()) > 0)		mytext=mytext + "10HR ";
          					if(parseInt($(xml).find('VioEleven').text()) > 0)		mytext=mytext + "11HR ";
          					if(parseInt($(xml).find('VioForteen').text()) > 0)	mytext=mytext + "14HR ";
          					if(parseInt($(xml).find('VioThirty').text()) > 0)		mytext=mytext + "34HR ";
          					if(parseInt($(xml).find('VioSeventy').text()) > 0)	mytext=mytext + "70HR ";
          				}
          				
          				mytext2="";   
          				//below is the PN version...
          				/*
          				my=$(xml).find('PNDrivenHrs').text();
          				my=$(xml).find('PNRestedHrs').text();
          				my=$(xml).find('PNWorkedHrs').text();
          				my=$(xml).find('PNWkDrivenHrs').text();
          				my=$(xml).find('PNWkRestedHrs').text();
          				my=$(xml).find('PNWkWorkedHrs').text();
          				my=$(xml).find('PNVioTen').text();
          				my=$(xml).find('PNVioEleven').text();
          				my=$(xml).find('PNVioForteen').text();
          				my=$(xml).find('PNVioThirty').text();
          				my=$(xml).find('PNVioSeventy').text();
          				my=$(xml).find('PNViolations').text();
          				my=$(xml).find('PNSpeeding').text();
          				my=$(xml).find('PNShutdowns').text();
          				my=$(xml).find('PNNum').text();
          				my=$(xml).find('PNSQL').text();
          				*/
          				
          				mytext2=mytext2 + "PN Week Hours: ";
          				mytext2=mytext2 + "Driven "+$(xml).find('PNDrivenHrs').text()+", ";
          				mytext2=mytext2 + "Rested "+$(xml).find('PNRestedHrs').text()+", ";
          				mytext2=mytext2 + "Worked "+$(xml).find('PNWorkedHrs').text()+". ";
          				//mytext2=mytext2 + "Week: ";
          				//mytext2=mytext2 + "Driven "+$(xml).find('PNWkDrivenHrs').text()+", ";
          				//mytext2=mytext2 + "Rested "+$(xml).find('PNWkRestedHrs').text()+", ";
          				//mytext2=mytext2 + "Worked "+$(xml).find('PNWkWorkedHrs').text()+".";
          				
          				if(parseInt($(xml).find('PNViolations').text()) > 0)
          				{
          					mytext2=mytext2 + " Warnings: ";
          					
          					if(parseInt($(xml).find('PNVioTen').text()) > 0)		mytext2=mytext2 + "10HR ";
          					if(parseInt($(xml).find('PNVioEleven').text()) > 0)	mytext2=mytext2 + "11HR ";
          					if(parseInt($(xml).find('PNVioForteen').text()) > 0)	mytext2=mytext2 + "14HR ";
          					if(parseInt($(xml).find('PNVioThirty').text()) > 0)	mytext2=mytext2 + "34HR ";
          					if(parseInt($(xml).find('PNVioSeventy').text()) > 0)	mytext2=mytext2 + "70HR ";
          				}
          				
          				$('.'+sect+'_driver_'+myid+'').html(mytext2 + "<br>" + mytext);				
          			}
          	});	
          }	
	}
	
	
	function mrr_set_marquee()
	{
		/*
		$('.marquee').marquee();  
			
		$('.marquee').marquee({
                    //If you wish to always animate using jQuery
                    allowCss3Support: true,                    
                    css3easing: 'linear', 	//works when allowCss3Support is set to true - for full list see http://www.w3.org/TR/2013/WD-css3-transitions-20131119/#transition-timing-function                   
                    easing: 'linear',		//requires jQuery easing plugin. Default is 'linear'                    
                    delayBeforeStart: 1000,	//pause time before the next animation turn in milliseconds                    
                    direction: 'left',		//'left', 'right', 'up' or 'down'                    
                    duplicated: false,		//true or false - should the marquee be duplicated to show an effect of continues flow                    
                    duration: 5000,		//speed in milliseconds of the marquee in milliseconds                    
                    gap: 20,				//gap in pixels between the tickers
                    pauseOnCycle: false,	//on cycle pause the marquee                    
                    pauseOnHover: false		//on hover pause the marquee - using jQuery plugin https://github.com/tobia/Pause
          });
		*/
		
		cd=$('#mrr_pn_message_warning_cntr').val();
		txt=$('#mrr_pn_message_warning_txt').val();
		if(cd > 0)
		{
			$('#mrr_pn_message_warning').show();
			$('#mrr_pn_message_warning').html(txt);
		}
		else
		{
			$('#mrr_pn_message_warning').hide();
		}
	}
	
	function mrr_swap_section(sect)
	{
		if(sect==1)
		{
			$('#calender').show();
			$('#pmi_listing').hide();
		}
		else
		{
			$('#calender').hide();	
			$('#pmi_listing').show();	
		}
	}
	
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
	
	function mrr_show_this_dispatch(id)
	{
		setInterval(function() {  }, 500);	
		$('.load_stop_details_'+id+'').show();
	}
	function mrr_hide_this_dispatch(id)
	{	
		setInterval(function() {  }, 500);		
		$('.load_stop_details_'+id+'').hide();
	}
	
	function mrr_show_this_available(id)
	{
		setInterval(function() {  }, 500);	
		$('.available_driver_details_'+id+'').show();
	}
	function mrr_hide_this_available(id)
	{	
		setInterval(function() {  }, 500);		
		$('.available_driver_details_'+id+'').hide();
	}
	/*
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
	*/
	function hide_details_action(obj, use_class) {
		hide_object_after_wait = obj;
		setInterval(function() {hide_obj(obj, use_class)}, 500);
	}
	
	function hide_obj(obj, use_class) {
		if(hide_object_after_wait == obj) {
			$(use_class).hide();
			$(obj).find(use_class).hide();
		}
	}
	
	function show_details_action(obj, use_class) {
		show_object_after_wait = obj;
		setInterval(function() {show_obj(obj, use_class)}, 500);
	}
	
	function show_obj(obj, use_class) {
		if(show_object_after_wait == obj) {
			//$(use_class).hide();
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
			success: function(xml) {
				toggle_dropped_trailers();
			}
		});	
		
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
		$('#pn_note_mini_holder_'+log_id).hide();
	}
	function mrr_close_phone_msg_displayer(log_id)
	{
		$('#phone_note_holder_'+log_id+'').hide();	
	}
	
	
	function pn_msg_box_mini_reply(log_id,loadid,truck_id,datefrom)
	{	//this is for the small warnings on the page "PN Messages" box at the top....
		$('#pn_note_mini_holder_'+log_id).html("<img src='images/loader.gif'> Loading PN recent messages...");
			
		$('#pn_note_mini_holder_'+log_id).show();
			
		extra_html = "";
			
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_quick_message_form_display",
		   data: {
		   		"truck_id":truck_id, 
		   		"disp_id":log_id,
		   		"load_id":loadid,
		   		"driver_id":0,
		   		"mini_mode":1,
		   		"date_from":datefrom,
		   		"date_to":'',
		   		"msg_id":0			   		
		   		},
		   success: function(data) {
		   		extra_html = "";
		   		$('#pn_note_mini_holder_'+log_id).html(extra_html + data);
		   }
		 });
		 //$('#pn_note_holder_'+$(this).attr('log_id')).hide();
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
	
	$('.phone_note_image').hover(
		function() {
			truck_id = $(this).attr('truck_id');
			log_id = $(this).attr('log_id');
			load_id = $(this).attr('load_id');
			
			$('#phone_note_holder2_'+log_id).html("<img src='images/loader.gif'> Loading Phone recent messages...");
			
			$('#phone_note_holder2_'+log_id).show();
			
			extra_html = "";
			
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=load_recent_phone_only_messages",
			   data: {"truck_id":truck_id,"disp_id":log_id,"load_id":load_id},
			   success: function(data) {
			   		extra_html = "";
			   		$('#phone_note_holder2_'+log_id).html(extra_html + data);
			   }
			 });
		},
		function() {
			$('#phone_note_holder2_'+$(this).attr('log_id')).hide();
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
						
			//now remove the column header and column if this is present...DEDICATED ingram
			if($(this).attr('mrr_col_2_id'))
			{
				mydater=$(this).attr('mrr_col_2_id');
				$('td[mrr_hdr_2_id="'+mydater+'"]').hide();				
				$(this).hide();
			}	
			
			//now remove the column header and column if this is present...DEDICATED pace runner
			if($(this).attr('mrr_col_3_id'))
			{
				mydater=$(this).attr('mrr_col_3_id');
				$('td[mrr_hdr_3_id="'+mydater+'"]').hide();				
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
					   			//turned off by Donovan March 2015... so that less steps involved...
					   			//$.prompt("Stop Date Completed - Saved successfully");
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
	
	function add_note(dispatch_id) 
	{
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
	
	
	$().ready(function() {
		display_dispatch_im_msgs();		
				
		//setTimeout("display_dispatch_im_msgs();", (60 * 1000 * 1));		//1000=1 second * 60 = 1 minute * 1
	});
</script>
<? $page_timer_array[] = "End of page: " . show_page_time(); ?>
<?

//}
?>

	<div style='clear:both'></div>
	
	<center>
		<?
		
		if($_SERVER['REMOTE_ADDR'] == '70.90.229.29' || 1 == 1) {
			
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

if($_SERVER['REMOTE_ADDR'] == '70.90.229.29') 
{
	//echo "<pre>";
	//var_dump($page_timer_array);
	//echo "</pre>";
}
?>		