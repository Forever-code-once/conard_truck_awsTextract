<?
$usetitle="Unit Backhaul Report";  
?>
<? include('header.php') ?>
<table style='text-align:left;width:1500px;margin:10px'>
<tr>
	<td valign='top'>
<?
	if(!isset($_POST['date_from']))			$_POST['date_from']=date("m/d/Y");
	if(!isset($_POST['date_to']))				$_POST['date_to']=date("m/d/Y",strtotime("+1 day", time()));	
	if(!isset($_POST['mrr_cust_zip']))			$_POST['mrr_cust_zip']="37086";
	if(!isset($_POST['mrr_radius']))			$_POST['mrr_radius']=0;
		
	if(trim($_POST['mrr_cust_zip'])=="")		$_POST['mrr_cust_zip']="37086";
	if(trim($_POST['mrr_radius'])=="")			$_POST['mrr_radius']=0;
	if(!is_numeric($_POST['mrr_radius']))		$_POST['mrr_radius']=0;
	
	$rfilter = new report_filter();
	$rfilter->show_customer 		= true;
	$rfilter->show_load_id 		= true;
	$rfilter->show_dispatch_id 	= true;	
	$rfilter->show_driver 		= true;
	$rfilter->show_truck 		= true;
	$rfilter->show_trailer 		= true;
	$rfilter->show_cust_addr1 	= true;
	$rfilter->show_cust_addr2 	= true;
	$rfilter->show_cust_city		= true;
	$rfilter->show_cust_state	= true;
	$rfilter->show_cust_zip		= true;
	$rfilter->show_radius_val 	= true;
	$rfilter->show_font_size		= true;
	$rfilter->show_filter();

	$sql = "
		select load_handler_stops.*,
			load_handler.customer_id,
			trucks_log.driver_id,
			trucks_log.driver2_id,
			trucks_log.truck_id,
			trucks_log.trailer_id,		
			trucks.name_truck,
			trailers.trailer_name,
			drivers1.name_driver_first as fname1,
			drivers1.name_driver_last as lname1,
			drivers2.name_driver_first as fname2,
			drivers2.name_driver_last as lname2,
			customers.name_company
		
		from load_handler_stops
			left join load_handler on load_handler.id=load_handler_stops.load_handler_id
			left join trucks_log on trucks_log.id=load_handler_stops.trucks_log_id
			left join drivers drivers1 on drivers1.id = trucks_log.driver_id
			left join drivers drivers2 on drivers2.id = trucks_log.driver2_id
			left join trucks on trucks.id = trucks_log.truck_id
			left join trailers on trailers.id = trucks_log.trailer_id
			left join customers on customers.id = load_handler.customer_id
			
		where load_handler_stops.deleted = 0
			and load_handler.deleted = 0 	
			and trucks_log.deleted = 0	
			".($_POST['customer_id'] ? " and customers.id = '".sql_friendly($_POST['customer_id'])."'" : '') ."
			".($_POST['truck_id'] ? " and trucks_log.truck_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
			".($_POST['trailer_id'] ? " and trucks_log.trailer_id = '".sql_friendly($_POST['driver_id'])."'" : '') ."
			".($_POST['driver_id'] ? " and (trucks_log.driver_id = '".sql_friendly($_POST['driver_id'])."' or trucks_log.driver2_id = '".sql_friendly($_POST['driver_id'])."')" : '') ."
			".($_POST['load_handler_id'] ? " and load_handler.id = '".sql_friendly($_POST['load_handler_id'])."'" : '') ."
			".($_POST['dispatch_id'] ? " and trucks_log.id = '".sql_friendly($_POST['dispatch_id'])."'" : '') ."
			
			".($_POST['mrr_cust_addr1'] ? " and load_handler_stops.shipper_address1 like '".sql_friendly($_POST['mrr_cust_addr1'])."%'" : '') ."
			".($_POST['mrr_cust_addr2'] ? " and load_handler_stops.shipper_address2 like '".sql_friendly($_POST['mrr_cust_addr2'])."%'" : '') ."
			".($_POST['mrr_cust_city'] ? " and load_handler_stops.shipper_city like '".sql_friendly($_POST['mrr_cust_city'])."%'" : '') ."
			".($_POST['mrr_cust_state'] ? " and load_handler_stops.shipper_state like '".sql_friendly($_POST['mrr_cust_state'])."%'" : '') ."
						
			and load_handler_stops.linedate_pickup_eta >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
			and load_handler_stops.linedate_pickup_eta <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
			
		order by load_handler_stops.linedate_pickup_eta asc, load_handler_stops.load_handler_id desc
	";	
			//".($_POST['mrr_cust_zip'] ? " and load_handler_stops.shipper_zip like '".sql_friendly($_POST['mrr_cust_zip'])."%'" : '') ."
	
	$use_radius= $_POST['mrr_radius'];
	
	$data = simple_query($sql);
?>
	</td>
	<td valign='top' align='right'>			
		<div id='special_map' style="width:900px; height:440px; margin-top:5px;"></div>
	</td>
</tr>
</table>

<div style='clear:both'></div>

<form action='' method='post'>

<div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
	<h3>Unit Backhauls</h3><br><span class='alert'><b>Please use the Date From, Date To, numeric Zip-Code, and Radius value in miles.</b></span> All other filters are optional but may narrow the focus.<br>
</div>
<div style='clear:both'></div>
<table class='admin_menu1 tablesorter font_display_section' id='table_sort1' style='text-align:left;width:1400px;margin:10px'>
<thead>
<tr>
	<th><b>Point</b></th>
	<th><b>Load</b></th>
	<th><b>Disp</b></th>
	<th><b>Stop</b></th>	
	<th><b>Customer</b></th>
	<th><b>Truck</b></th>
	<th><b>ETA</b></th>
	<th><b>Trailer</b></th>
	<th><b>Driver</b></th>
	<th><b>Driver2</b></th>
	<!--<th><b>Shipper</b></th>-->
	<th><b>Address1</b></th>
	<!--<th><b>Address2</b></th>-->
	<th><b>City</b></th>
	<th><b>State</b></th>
	<th><b>Zip</b></th>
	<th><b>Phone</b></th>
	<!--
	<th align='right'><b>Lat</b></th>
	<th align='right'><b>Long</b></th>
	-->
	<th align='right'><b>Miles</b></th>
	<th align='right'><b>Mode</b></th>
</tr>
<tbody>
<? 	
	$counter = 0;	
	$my_lat=0;
	$my_long=0;
	$zippy1=0;
	$zippy2=0;
	if(trim($_POST['mrr_cust_zip'])!="")
	{
		$_POST['mrr_cust_zip']=trim($_POST['mrr_cust_zip']);
		if(strlen($_POST['mrr_cust_zip']) > 5)		$_POST['mrr_cust_zip']=substr($_POST['mrr_cust_zip'],0,5);
		
		$res=mrr_find_gps_from_zip_code($_POST['mrr_cust_zip']);
		$my_lat=$res['lat'];
		$my_long=$res['long'];
		
		$zippy2=(int) trim($_POST['mrr_cust_zip']);
	}	
	
	$marker_image="images/2012/mrr_truck.png";	//images/truck_info.png
	$mrr_map_points="";
	$mrr_map_bounds="";
	$map_object="special_map";
	$home_lat=trim($my_lat);			//"36.001156";
	$home_long=trim($my_long);		//"-86.597328";	
	$home_long="-".abs($home_long);
		
	$zoom_level=8;
	
		
	$pointer_type="google.maps.SymbolPath.CIRCLE";
	/*
	Symbol Types
	google.maps.SymbolPath.BACKWARD_CLOSED_ARROW 	A backward-pointing closed arrow.
	google.maps.SymbolPath.BACKWARD_OPEN_ARROW 		A backward-pointing open arrow.
	google.maps.SymbolPath.CIRCLE 				A circle.
	google.maps.SymbolPath.FORWARD_CLOSED_ARROW 		A forward-pointing closed arrow.
	google.maps.SymbolPath.FORWARD_OPEN_ARROW 		A forward-pointing open arrow.	
	*/
	
	$map_type="google.maps.MapTypeId.ROADMAP";	
	/*
	Map Types:		
	google.maps.MapTypeId.ROADMAP 				displays the default road map view
	google.maps.MapTypeId.SATELLITE 				displays Google Earth satellite images
	google.maps.MapTypeId.HYBRID 					displays a mixture of normal and satellite views
	google.maps.MapTypeId.TERRAIN 				displays a physical map based on terrain information. 
	*/
	
	
	$truck_cnt=0;	
	
	$mrr_map_points.="					
					var pose".$truck_cnt."  = new google.maps.LatLng(".$my_lat.",".$my_long.");
					var truck".$truck_cnt." = new google.maps.Marker({ position: pose".$truck_cnt.",  map: map,  title: 'Your Focus Point' });
				";
	$mrr_map_bounds.="truck".$truck_cnt."";
			
	while($row = mysqli_fetch_array($data)) 
	{
		$counter++;
				
		$cust_link="<a href='admin_customers.php?eid=$row[customer_id]' target='_blank'>$row[name_company]</a>";
		$disp_link="<a href='add_entry_truck.php?id=$row[trucks_log_id]' target='_blank'>$row[trucks_log_id]</a>";
		$truck_link="<a href='admin_trucks.php?id=$row[truck_id]' target='_blank'>$row[name_truck]</a>";
		$trailer_link="<a href='admin_trailers.php?id=$row[trailer_id]' target='_blank'>$row[trailer_name]</a>";
		$driver1_link="<a href='admin_drivers.php?id=$row[driver_id]' target='_blank'>$row[fname1] $row[lname1]</a>";
		$driver2_link="<a href='admin_drivers.php?id=$row[driver2_id]' target='_blank'>$row[fname2] $row[lname2]</a>";
		
		if($row['trucks_log_id']==0)		
		{
			$disp_link="";
			$truck_link="";
			$trailer_link="";
			$driver1_link="";
			$driver2_link="";
		}
		elseif($row['driver2_id']==0)
		{
			$driver2_link="";
		}
		
		$miles=0;
		$dist_mode="N/A";
		
		if(trim($row['shipper_zip'])!="")
		{
			$row['shipper_zip']=trim($row['shipper_zip']);
			if(strlen($row['shipper_zip']) > 5)	$row['shipper_zip']=trim(substr($row['shipper_zip'],0,5));	
			$row['shipper_zip']=str_replace("-","",$row['shipper_zip']);
			
			$dmiles=mrr_process_zip_code_to_zip_code($row['shipper_zip'],$zippy2);
			$miles=$dmiles['miles'];
			$dist_mode=" Crow";
		}
		/**/
		if($miles==0 && $row['shipper_zip']!=$zippy2)
		{
			if($row['latitude']==0 || $row['longitude']==0)
     		{
     			$row['shipper_zip']=trim($row['shipper_zip']);
     			if(strlen($row['shipper_zip']) > 5)		$row['shipper_zip']=substr($row['shipper_zip'],0,5);
     			
     			$resx=mrr_find_gps_from_zip_code(trim($row['shipper_zip']));
     			$row['latitude']=$resx['lat'];
     			$row['longitude']=$resx['long'];
     		}	
     		
     		$miles=mrr_promiles_get_file_contents($my_lat,$my_long,$row['latitude'],$row['longitude']);
     		$dist_mode=" Pro";
     			
     		if($my_lat!=0 && $my_long!=0 && $row['latitude']!=0 && $row['longitude']!=0 && $miles==0)
     		{
     			$miles=mrr_distance_between_gps_points($my_lat,$my_long,abs($row['latitude']),abs($row['longitude']));
     			$miles=abs($miles);
     			$dist_mode=" Crow";
     		}
		}
		
		
		/*
		if($my_lat!=0 && $my_long!=0 && $row['latitude']!=0 && $row['longitude']!=0)
		{
			$miles=mrr_promiles_get_file_contents($my_lat,$my_long,$row['latitude'],$row['longitude']);
     		$dist_mode=" Pro";
		}
		*/
				
		$mrr_classy="red";
		if($miles <= abs($use_radius) && $dist_mode!="N/A")
		{
			$mrr_classy="green";
			
     		
			$tmp_long=trim($row['longitude']);
			$tmp_long="-".abs($tmp_long);
			
			if(abs($tmp_long) > 0 && abs($row['latitude']) > 0)
			{	
				$truck_cnt++;		
				$mrr_map_points.="					
						var pose".$truck_cnt."  = new google.maps.LatLng(".abs($row['latitude']).",".$tmp_long.");
						var truck".$truck_cnt." = new google.maps.Marker({ position: pose".$truck_cnt.",  map: map, icon: 'http://www.googlemapsmarkers.com/v1/".$truck_cnt."/00CC00/FFFFFF/0000FF/', title: 'Truck ".$row['name_truck'].": ".$row['shipper_address1']."' });
					";
							
				$mrr_map_bounds.=",truck".$truck_cnt."";			
			}
			echo "
     			<tr class='load_".$row['load_handler_id']."'>
     				<td><img src='http://www.googlemapsmarkers.com/v1/".$truck_cnt."/00CC00/FFFFFF/0000FF/' alt='".$truck_cnt."'></td>	
     				<td><a href='manage_load.php?load_id=$row[load_handler_id]' target='edit_load_$row[id]'>$row[load_handler_id]</a></td>
     				<td>".$disp_link."</td>	
     							
     				<td nowrap>$row[id]</td>				
     				<td nowrap>".$cust_link."</td>
     				<td nowrap>".$truck_link."</td>
     				<td nowrap>".date("m-d-Y", strtotime($row['linedate_pickup_eta']))."</td>
     				<td nowrap>".$trailer_link."</td>
     				<td nowrap>".$driver1_link."</td>
     				<td nowrap>".$driver2_link."</td>
     				
     				<td nowrap>".$row['shipper_address1']."</td>
     				
     				<td nowrap>".$row['shipper_city']."</td>
     				<td nowrap>".$row['shipper_state']."</td>
     				<td nowrap>".$row['shipper_zip']."</td>
     				<td nowrap>".$row['stop_phone']."</td>
     				    							
     				<td nowrap align='right'>".number_format($miles,2)."</td>
     				<td nowrap align='right'>".$dist_mode."</td>
     			</tr>
     		";	
				//<span style='color:".$mrr_classy.";'><b></b></span>
				//<td nowrap>".($row['stop_type_id']==1 ? "(S) " : "(C) ")."".$row['shipper_name']."</td>
				//<td nowrap>".$row['shipper_address2']."</td>
				//<td nowrap align='right'>".$row['latitude']."</td>
				//<td nowrap align='right'>".$row['longitude']."</td>					
		}
	}
?>
</tbody>
</table>
</form>
<script type='text/javascript'>
	
	$().ready(function() {
		$('.tablesorter').tablesorter();
		
		//$('.datepicker').datepicker();
		map_initialize();	
	});
	
	//mapping functions....
	function map_initialize() 
	{		
     	var mapOptions = { center: new google.maps.LatLng(<?= $home_lat ?>, <?= $home_long ?>), zoom: <?= $zoom_level ?>, mapTypeId: <?= $map_type ?> };
        	var map = new google.maps.Map(document.getElementById('<?= $map_object ?>'), mapOptions);
        	
          //var marker_image = '{ path: < ?= $pointer_type ? >, scale: 10 }, draggable: true,'; 
          //var marker_image = '< ?= $marker_image ? >';   
          
        	//blue-ish styles
		var styleArray = [
            {
              featureType: "all",
              stylers: [
                { saturation: -80 }
              ]
            },{
              featureType: "road.arterial",
              elementType: "geometry",
              stylers: [
                { hue: "#00ffee" },
                { saturation: 50 }
              ]
            },{
              featureType: "poi.business",
              elementType: "labels",
              stylers: [
                { visibility: "off" }
              ]
            }
          ];
          //default
          var stylex = [
            {
              stylers: [
                { hue: "#00ffe6" },
                { saturation: -20 }
              ]
            },{
              featureType: "road",
              elementType: "geometry",
              stylers: [
                { lightness: 100 },
                { visibility: "simplified" }
              ]
            },{
              featureType: "road",
              elementType: "labels",
              stylers: [
                { visibility: "off" }
              ]
            }
          ];
          
          //map.setOptions({styles: stylex});
          //map.setOptions({styles: styleArray});                 

        	var marker = new google.maps.Marker({ position: map.getCenter(), map: map,	title: 'Center of Map...'	});
		<?= $mrr_map_points ?>
		var bounds = new google.maps.LatLngBounds(<?= $mrr_map_bounds ?>);
		map.fitBounds(bounds);	
     }
</script>
<? include('footer.php') ?>