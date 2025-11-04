<? include('application.php') ?>
<? 
	$admin_page = 1;
	$max_packet=0;
	$serve_output2="";
	$max_msg_packet=0;
	$serve_output3="";
	
	$java_run_list="";
	
	$sdater=date("m/d/Y H:i:s");
	$diff_timer="";
	
	$serve_output="";
	$dispatch_section="";
	$mrr_message="";
	
	$map_bypass=0;

	$avg_states[0]="TN";		$avg_val[0]=0.00;	$avg_cnt[0]=0;		$avg_tot[0]=0.00;
	$avg_states[1]="KY";		$avg_val[1]=0.00;	$avg_cnt[1]=0;		$avg_tot[1]=0.00;
	$avg_states[2]="VA";		$avg_val[2]=0.00;	$avg_cnt[2]=0;		$avg_tot[2]=0.00;
	$avg_states[3]="AL";		$avg_val[3]=0.00;	$avg_cnt[3]=0;		$avg_tot[3]=0.00;
	$avg_states[4]="GA";		$avg_val[4]=0.00;	$avg_cnt[4]=0;		$avg_tot[4]=0.00;
	$avg_states[5]="MO";		$avg_val[5]=0.00;	$avg_cnt[5]=0;		$avg_tot[5]=0.00;
	$avg_states[6]="MS";		$avg_val[6]=0.00;	$avg_cnt[6]=0;		$avg_tot[6]=0.00;
	$avg_states[7]="NC";		$avg_val[7]=0.00;	$avg_cnt[7]=0;		$avg_tot[7]=0.00;
	$avg_states[8]="SC";		$avg_val[8]=0.00;	$avg_cnt[8]=0;		$avg_tot[8]=0.00;
	
	$sent_daily_ops_snapshot=0;
	
	$mrr_output="";
	if(!isset($_POST['import_block']))      $_POST['import_block']="";
	if(isset($_POST['import_it']))
    {
        //import data from spreadsheet...
         //$mrr_output=trim($_POST['import_block']);    
     
         $all_values=trim($_POST['import_block']);
     
         $all_values=str_replace(chr(9)," , ",$all_values);
         $all_values=str_replace(chr(10),"<br>",$all_values);
         $all_values=str_replace(chr(13),"",$all_values);
         
         /*  Columns A thru X
         1  A=Customer	
         2  B=Loves Store No.	
         3  C=City	
         4  D=State	
         5  E=Billing Card Station Code	
         6  F=OPIS Rack ID	
         7  G=Retail Price	
         8  H=Retail Minus Discount	
         9  I=Retail Minus Discounted Price	
         10 J=OPIS / NYMEX Rack	
         11 K=Pumping Fee	
         12 L=OPIS Discounted Price	
         13 M=Best Discounted Price	
         14 N=Total Taxes and Fees	
         15 O=Federal Taxes	
         16 P=State Taxes	
         17 Q=Other Taxes	
         18 R=Sales Tax	
         19 S=Freight Fee	
         20 T=Discounted Type Applied	
         21 U=Effective Date	
         22 V=DEF Retail Price	
         23 W=Latitude	
         24 X=Longitude
         */
         
         
         //clear all stores     
         $sqlu = "update prices_loves set deleted=1";
         simple_query($sqlu);
     
         $entries=0;
         $list="<b>Import Preview Section:</b><br><table>";
         $list.="
                <tr>
                    <td valign='top'><b>CNT</b></td>	
                    <td valign='top'><b>Loves Store No.</b></td>
                    <td valign='top'><b>City</b></td>
                    <td valign='top'><b>State</b></td>
                    <td valign='top'><b>Best Price</b></td>
                    <td valign='top'><b>Last Price</b></td>
                    <td valign='top'><b>+/- Price</b></td>
                    <td valign='top'><b>Latitude</b></td>
                    <td valign='top'><b>Longitude</b></td>
                </tr>
         ";
         $arr=explode("<br>",$all_values);
         foreach($arr as $lines)
         {
              $line = trim($lines);
              $col_headers=0;
              if(substr_count($line, "Loves Store") > 0 || substr_count($line, "Best Discounted") > 0)    $col_headers=1;
              
              $store_val="";
              $city_val="";
              $state_val="";
              $best_price =0.0000;
              $last_price =0.0000;
              $lat =0.0000;
              $long=0.0000;
     
              $parts=explode(" , ",$line);
          
              //$list.="<br><br>".($entries + 1).".  ";
              foreach($parts as $key => $value)
              {
                   //$list.="<br>--[".$key."] = ".trim($value)."";
               
                   if($key==1)		$store_val="".trim($value)."";     
                   if($key==2)		$city_val="".trim($value)."";
                   if($key==3)		$state_val="".trim($value)."";                   
                   
                   if($key==12)		$best_price=trim(str_replace("$","",trim($value)));	
                   
                   if($key==22)		$lat=trim($value);
                   if($key==23)		$long=trim($value);
              }
              
              if($col_headers==0) 
              {    //show if not a header line. 
     
                   $sqlx = "
                        select id,last_price
                        from prices_loves
                        where state='".sql_friendly($state_val)."' and city='".sql_friendly($city_val)."' and store_no='".sql_friendly($store_val)."'
                        order by id asc
                        limit 1
                   ";
                   $datax = simple_query($sqlx);
                   if($rowx = mysqli_fetch_array($datax))
                   {    //save changes to the existing one and unflag it for delete...
                        $sqlu = "
                            update prices_loves set
                                last_price=best_price,
                                best_price='".sql_friendly($best_price)."',
                                deleted=0
                            where id='".sql_friendly($rowx['id'])."'
                        ";
                        simple_query($sqlu);
                        $last_price = $rowx['last_price'];
                   }
                   else
                   {    //make a new row for this one.
                        $sqlu = "
                            insert into prices_loves 
                                (id,
                                store_no,
                                city,
                                state,
                                best_price,
                                last_price,
                                latitude,
                                longitude,                                
                                deleted)
                            values 
                                (NULL,
                                '".sql_friendly($store_val)."',
                                '".sql_friendly($city_val)."',
                                '".sql_friendly($state_val)."',
                                '".sql_friendly($best_price)."',
                                '".sql_friendly($best_price)."',
                                '".sql_friendly($lat)."',
                                '".sql_friendly($long)."',
                                0)
                        ";
                        simple_query($sqlu);
                        $last_price = $best_price;
                   }
     
                   $col_val="000000";
                   if(($best_price - $last_price) < 0)     $col_val="00CC00";
                   if(($best_price - $last_price) > 0)     $col_val="CC0000";
                  
                  $list .= "
                        <tr style='background-color:#" . ($entries % 2 == 0 ? "eeeeee" : "dddddd") . ";'>
                            <td valign='top'>" . ($entries + 1) . "</td>	
                            <td valign='top'>" . $store_val . "</td>
                            <td valign='top'>" . $city_val . "</td>
                            <td valign='top'>" . $state_val . "</td>
                            <td valign='top'>$" . number_format($best_price,2) . "</td>
                            <td valign='top'>$" . number_format($last_price,2) . "</td>
                            <td valign='top'>
                                <span style='color:#".$col_val.";'>
                                    <b>$" . number_format(($best_price - $last_price),2) . "</b>
                                </span>
                            </td>
                            <td valign='top'>" . $lat . "</td>
                            <td valign='top'>" . $long . "</td>
                        </tr>
                  ";
              }          
              $entries++;
         }
         $list.="</table>";
     
         //clear all stores values that have not been turned back on yet    
         $sqlu = "delete from prices_loves where deleted=1";
         //simple_query($sqlu);     
     
         $mrr_output.="<br>".$list;
	
	 $sent_daily_ops_snapshot=1;
    }
		
	
	//$truck_map=mrr_map_generator($_POST['truck_id']);
		
	$marker_image="images/2012/mrr_truck.png";	//images/truck_info.png
	$mrr_map_points="";
	$mrr_map_bounds="";
	$map_object="special_map";
	$home_lat="36.0012";
	$home_long="-86.5973";		
	$zoom_level=7;
	
	$pointer_type="google.maps.SymbolPath.CIRCLE";
	/*
	Symbol Types
	google.maps.SymbolPath.BACKWARD_CLOSED_ARROW 	A backward-pointing closed arrow.
	google.maps.SymbolPath.BACKWARD_OPEN_ARROW 		A backward-pointing open arrow.
	google.maps.SymbolPath.CIRCLE 				    A circle.
	google.maps.SymbolPath.FORWARD_CLOSED_ARROW 	A forward-pointing closed arrow.
	google.maps.SymbolPath.FORWARD_OPEN_ARROW 		A forward-pointing open arrow.	
	*/
	
	$map_type="google.maps.MapTypeId.ROADMAP";	
	/*
	Map Types:		
	google.maps.MapTypeId.ROADMAP 				    displays the default road map view
	google.maps.MapTypeId.SATELLITE 				displays Google Earth satellite images
	google.maps.MapTypeId.HYBRID 					displays a mixture of normal and satellite views
	google.maps.MapTypeId.TERRAIN 				    displays a physical map based on terrain information. 
	*/
		
	$speed_col1="purple";
	$speed_col2="black";
	$speed_col3="orange";
	$speed_col4="green";
	$speed_col5="red";
	
	$map_api_key=trim($defaultsarray['google_map_api_key']);
	//&key=YOUR_API_KEY
			
	$new_style_path="images/2012/";				//?truck_id=".$_POST['truck_id']."&service_type=loc_overview
	
    //$edater=date("m/d/Y H:i:s");
	//$diff_timer.="3. Point is ".(strtotime($edater) - strtotime($sdater)) ." seconds<br>";

    $icon_path="http://trucking.conardtransportation.com/images/";
    $icon_image="truck_info.png";


    $store_cnt=0;
    $mrr_map_points="";
    $mrr_map_bounds="";
    
    $sql = "
        select *
        from prices_loves
        where deleted=0
        order by state asc,city asc, store_no asc,id asc
    ";
    $data = simple_query($sql);
    $mn=mysqli_num_rows($data);
    while($row = mysqli_fetch_array($data))
    {
         //plot map point...add to javascript below.
         //$extra_tag="";
         //if($row['latitude']==$home_lat && $row['longitude']==$home_long)		$extra_tag="Home Sweet Home ... and ";
         
         //$extra_tag=str_replace("'","",$extra_tag);
         
         $diff_str="Is $".number_format($row['best_price'],4)." - (Was) $".number_format($row['last_price'],4)." =  (".(($row['best_price'] - $row['last_price']) > 0 ? "Up" : "Down").") $".number_format(($row['best_price'] - $row['last_price']),4)."";
         
         
         $stlocal=str_replace("'","","  ".$row['city']."  ".$row['state']."   ".$diff_str."  ");
         $stnum=str_replace("'","",$row['store_no']."");
         /*
         $col1="00CC00";      //BG color for point marker       00CC00 = green
         $col2="0000CC";      //Text for marker                 FFFFFF = white
         $col3="FFFFFF";      //Border color for point marker   0000FF = blue
         
         if(($row['best_price'] - $row['last_price']) > 0)      $col3="CC0000";
         */
     
         $col1="FFFFFF";      //BG color for point marker       00CC00 = green
         $col2="0000CC";      //Text for marker                 FFFFFF = white
         $col3="FFFFFF";      //Border color for point marker   0000FF = blue
     
         $icon_image="truck_info.png";                          //$icon_image="truck_info_yellow.png";
     
         if(($row['best_price'] - $row['last_price']) < 0)  {    $col1="88FF88";   $icon_image="truck_info_green.png";    }
         if(($row['best_price'] - $row['last_price']) > 0)  {    $col1="FF8888";   $icon_image="truck_info_red.png";    }
         
         $store_cnt++;																	// icon: marker_image,                     //$store_cnt
         $mrr_map_points.="					
                var pose".$store_cnt."  = new google.maps.LatLng(".round(floatval($row['latitude']),4).",".round(floatval($row['longitude']),4)."); 
                var store".$store_cnt." = new google.maps.Marker({ position: pose".$store_cnt.",  map: map, icon: '".$icon_path."".$icon_image."',  title: 'Store ".$stnum.":  ".$stlocal."' });
            ";                                                             //http://www.googlemapsmarkers.com/v1/".$stnum."/".$col1."/".$col2."/".$col3."/
         if($store_cnt==1)	$mrr_map_bounds.="store".$store_cnt."";
         else			$mrr_map_bounds.=",store".$store_cnt."";
          
    }        
?>
<? include('header.php') ?>
<?
	//echo "<br><br>Page Load speed check: <br>".$diff_timer."<br>"; 
    //$dispatch_section
?>
<form name='pricing_loves' action='<?= $SCRIPT_NAME ?>' method='post'>
	
<table class='admin_menu2' style='width:1600px'>
<tr>
    <td valign='top'>
        <h2>Prices - Loves</h2>
    </td>
    <td valign='top' align="right">
        <input type='submit' name='refresh_it' id='refresh_it' value='Refresh Page'>
    </td>
</tr>
<tr>
    <td valign='top' colspan='2'>
        <div id='special_map' style="width:1500px; height:800px; margin-top:10px; margin-bottom:10px;"></div>
    </td>
</tr>
<tr>
    <td valign='top' colspan='2'>
        <h3><b>Import Utility</b> - Select all (CTRL A) data and Copy (CTRL C) Excel Spreadsheet data here (all colunms) and Paste (CTRL V) here.</h3>
        <br><br>
        <textarea name="import_block" id="import_block" rows="20" cols="200" wrap="virtual"><?= $_POST['import_block'] ?></textarea>
        <br><br>        
        <center><input type='submit' name='import_it' id='import_it' value='Import Prices'></center>        
        <br>     
        <br><hr><br>
        <b>Preview:</b><br><br>
        <pre><?=$mrr_output ?></pre>
        <br><hr><br>
    </td>
</tr>
<tr>
	<td valign='top' colspan='2'>			
		<br>
		<div class='section_heading'>Prices - Loves Stations</div>
		<table class='tablesorter' width='100%'>
            <thead>
                <tr>
                    <th>Store No.</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Best Price</th>
                    <th>Last Price</th>
                    <th>+/- Price</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                </tr>
            </thead>
            <tbody>
             <?php
             $sql = "
                select *
                from prices_loves
                where deleted=0
                order by state asc,city asc, store_no asc,id asc
             ";
             $data = simple_query($sql);
             $mn=mysqli_num_rows($data);
             while($row = mysqli_fetch_array($data))
             {
                 $col_val="000000";
                 if(($row['best_price'] - $row['last_price']) < 0)     $col_val="00CC00";
                 if(($row['best_price'] - $row['last_price']) > 0)     $col_val="CC0000";
		
		 for($z=0; $z < 9; $z++)
		 {
			if($avg_states[$z] == $row['state'])
			{
				$avg_tot[$z]+=$row['best_price'];
				$avg_cnt[$z]++;
			}				
		 }
             ?>            
                <tr>
                    <td valign="top"><?=$row['store_no'] ?></td>
                    <td valign="top"><?=$row['city'] ?></td>
                    <td valign="top"><?=$row['state'] ?></td>
                    <td valign="top" align="right">$<?= number_format($row['best_price'],4) ?></td>
                    <td valign='top' align="right">$<?= number_format($row['last_price'],4) ?></td>
                    <td valign='top' align="right">
                                <span style='color:#<?= $col_val ?>;'>
                                    <b>$<?= number_format(($row['best_price'] - $row['last_price']),4) ?></b>
                                </span>
                    </td>
                    <td valign="top" align="right"><?=$row['latitude'] ?></td>
                    <td valign="top" align="right"><?=$row['longitude'] ?></td>
                </tr>
             <?php 
             } 
             ?>
            </tbody>		
		</table>
		
		<br>
		<center>
			<div class='section_heading'>Primary State Averages</div>
			<table cellspacing='2' cellpadding='0' border='1' width='400'>
			<tr>
				<td valign='top'>State</td>
				<td valign='top' align='right'>Stores</td>
				<td valign='top' align='right'>Average</td>
			</tr>
			<?
			//Clear out todays values only... since only today will get replaced.
			$sqlu = "delete from mrr_avg_state_values where linedate='".date("Y-m-d", time())."'";
                        simple_query($sqlu);

			$all_cnt=0;
			$all_tot=0;
 			for($z=0; $z < 9; $z++)
			{
				$avg_val[$z]=0;
				if($avg_cnt[$z] > 0)	$avg_val[$z]=($avg_tot[$z] / $avg_cnt[$z]);
				echo "
					<tr>
						<td valign='top'>".$avg_states[$z]."</td>
						<td valign='top' align='right'>".$avg_cnt[$z]."</td>
						<td valign='top' align='right'>$".number_format($avg_val[$z], 4)."</td>
					</tr>
				";
				$all_cnt+=$avg_cnt[$z];
				$all_tot+=$avg_tot[$z];	
                        	
				$sqlu = "
                            		insert into mrr_avg_state_values
                         		       (id,
                          		      store_state,
                          		      avg_rate,                                
                           		     linedate)
                            		values 
                        	        	(NULL,
                	                	'".sql_friendly($avg_states[$z])."',
        	                        	'".sql_friendly($avg_val[$z])."',
	                                	'".date("Y-m-d", time())."')
	                        ";
	                        simple_query($sqlu);
			}
			$all_val=0;
			if($all_cnt)	$all_val=($all_tot / $all_cnt);
			echo "
				<tr>
					<td valign='top'><b>Regional Average</b></td>
					<td valign='top' align='right'><b>".$all_cnt."</b></td>
					<td valign='top' align='right'><b>$".number_format($all_val, 4)."</b></td>
				</tr>
			";

                        $sqlu = "
                            insert into mrr_avg_state_values
                                (id,
                                store_state,
                                avg_rate,                                
                                linedate)
                            values 
                                (NULL,
                                'AVG',
                                '".sql_friendly($all_val)."',
                                '".date("Y-m-d", time())."')
                        ";
                        simple_query($sqlu);
			?>
			</table>
		</center>
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>	
	
	//mapping functions....
	function map_initialize() 
	{		     	
        var mapOptions = { center: new google.maps.LatLng(<?= round(floatval($home_lat),4) ?>,<?= round(floatval($home_long),4) ?>), zoom: <?= $zoom_level ?>, mapTypeId: <?= $map_type ?> };
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
 
        var marker = new google.maps.Marker({ position: map.getCenter(), map: map,	title: 'Loves Pricing'	});
        
        <?= $mrr_map_points ?>
        
        var bounds = new google.maps.LatLngBounds(<?= $mrr_map_bounds ?>);
        map.fitBounds(bounds);		
     }
     
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
        	$('.tablesorter').tablesorter();
	    	map_initialize();
        

        	//$('#date_from').datepicker();	
        	//$('#date_to').datepicker();
		
		<? if($sent_daily_ops_snapshot > 0) { ?>
			mrr_sent_daily_ops_snapshot();
		<? } ?>
	});
	
</script>
<? include('footer.php') ?>