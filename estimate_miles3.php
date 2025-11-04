<? include('header.php') ?>
<table>
<tr>
	<td valign='top'>
		<table class='admin_menu1' style='margin:10px;width:600px'>
		<tr>
			<td align='center' colspan='5'><b>ProMiles VERSION:</b></td>
		</tr>
		<tr>
			<td><b>Total Trip Miles: <span class='data_disp' id='total_miles'></span></b></td>
			<td align='right' colspan='4'><b><span class='data_disp' id='total_hours'></span> Total Hours</b></td>
		</tr>		
		<tr>
			<td colspan='5'><label><input type='checkbox' name='hub' id='hub'> Hub Run</label></td>
		</tr>
		<tr>
			<td align='left'><b>Location</b></td>
			<td align='left'><b>Zip</b></td>
			<td align='left'><b>City</b></td>
			<td align='left'><b>State</b></td>
			<td align='right'><b>Miles</b></td>
			<td align='right'><b>Total</b></td>
		</tr>
		<tr id='line_holder'>
			<td colspan='5'></td>
		</tr>
		<tr>
			<td align='center' colspan='5'>
				<input type='button' value='New Run' onclick='new_run()'>
				&nbsp;&nbsp;&nbsp;
				<input type='button' value='Calculate Run' onclick='calc_run()'>
				&nbsp;&nbsp;&nbsp;
				<input type='button' value='View Map' onclick='view_map()'>		
			</td>
		</tr>
		</table>
	</td>
	<td valign='top'>
		<div style='margin-top:15px;'>
			
			<iframe width="500" height="400" id="map_frame" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="" style="color:#0000FF;text-align:left;border:1px black solid"></iframe>
			
		</div>
	</td>
</tr>
</table>

<script type='text/javascript'>
	
	var line_count = 0;
	
	function add_line(add_after_line_no) {
		
		txt = "<tr id='line_holder_"+line_count+"'>";
		txt += "<td>";
		//txt += 	"<span onclick='javascript:add_line("+line_count+")' style='cursor:pointer' alt='Click to insert new line' title='Click to insert new line'> &nbsp;&darr;&nbsp; </span> ";
		txt += 	"<input line_number='"+line_count+"' id='local_"+line_count+"' class='local_entry' stop_id='"+line_count+"' value=\"\" style='width:200px;' onBlur='mrr_validate_address("+line_count+");'>";
		txt += "</td>";
		txt += "<td>";
		txt += 	"<input line_number='"+line_count+"' id='stop_"+line_count+"' class='stop_entry' stop_id='"+line_count+"' value=\"\" style='width:100px;'>";
		txt += "</td>";
		txt += "<td><span class='data_disp' id='city_"+line_count+"'></span><input type='hidden' name='lat_"+line_count+"' id='lat_"+line_count+"' value=''></td>";
		txt += "<td><span class='data_disp' id='state_"+line_count+"'></span><input type='hidden' name='long_"+line_count+"' id='long_"+line_count+"' value=''></td>";
		txt += "<td align='right'><span class='data_disp' id='miles_"+line_count+"'></span></td>";
		txt += "<td align='right'><span class='data_disp' id='total_"+line_count+"'></span></td>";
		txt += "</tr>";
		
		if(add_after_line_no == undefined) {
			$('#line_holder').before(txt);
		} else {
			// an insert line was specified, add it in where the line was specified  
			$('#line_holder_'+add_after_line_no).before(txt);
		}

		$('#stop_'+line_count).change(function() {
			//pcm_lookup_stop($(this))
			calc_run();
		});
		
		$('#local_'+line_count).autocomplete('ajax.php?cmd=search_gps_zip_codes&line_number='+line_count+'',{
																
																formatItem:formatItem, 
																onItemSelect:load_zip
		});	
		
		line_count++;
		

	}
	
	function mrr_validate_address(lnum)
	{
		zippy=$('#local_'+lnum).val();
		
		//alert('Zippy is '+zippy+'.');		
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_city_state_by_zip",
		   data: {
		   		"zip_code":zippy,
		   		'mode':1
		   	},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {		
			
			my_city=$(xml).find('City').text();
			my_state=$(xml).find('State').text();
			my_zip=$(xml).find('Zip').text();
			my_lat=$(xml).find('Lat').text();
			my_long=$(xml).find('Long').text();
			
			//alert('Line '+lnum+'= City '+my_city+'.  State '+my_state+'.  Zip '+my_zip+'.');	// (Extra0 '+extra[0]+' and Extra1 '+extra[1]+'.)
			
			if(my_zip != "" && my_zip != "0")
			{
				$('#local_'+lnum).val(''+my_city+', '+my_state+'')
				$('#stop_'+lnum).val(my_zip);
				$('#city_'+lnum).html(my_city);
				$('#state_'+lnum).html(my_state);
				$('#lat_'+lnum).val(my_lat);
				$('#long_'+lnum).val(my_long);
				calc_run();
			}
		   }
		 });	
	}
	
	
	function load_zip(row, input, extra) {
		
		lnum=extra[1];
		zippy=row;
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=load_city_state_by_zip",
		   data: {
		   		"zip_code":zippy,
		   		'mode':0
		   	},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {		
			
			my_city=$(xml).find('City').text();
			my_state=$(xml).find('State').text();
			my_zip=$(xml).find('Zip').text();
			my_lat=$(xml).find('Lat').text();
			my_long=$(xml).find('Long').text();
			
			//alert('Line '+lnum+'= City '+my_city+'.  State '+my_state+'.  Zip '+my_zip+'.');	// (Extra0 '+extra[0]+' and Extra1 '+extra[1]+'.)
			
			if(my_zip != "" && my_zip != "0")
			{
				$('#local_'+lnum).val(''+my_city+', '+my_state+'')
				$('#stop_'+lnum).val(my_zip);
				$('#city_'+lnum).html(my_city);
				$('#state_'+lnum).html(my_state);
				$('#lat_'+lnum).val(my_lat);
				$('#long_'+lnum).val(my_long);
				calc_run();
			}
		   }
		 });
	}
	
	
	
	for(i=0;i<10;i++) {
		add_line();
	}
	
	function new_run() {
		// clear the fields out
		$('.stop_entry').val('');
		$('.local_entry').val('');	
		$('.data_disp').html('');
		$('#stop_0').focus();
		$('#map_frame').attr('src','');
	}
	
	$('#hub').click(function() {
		calc_run();
	});
		
	function calc_run() {
				
		ziplist = "";
		citylist = "";
		statelist = "";
		latlist = "";
		longlist = "";
		
		$('.stop_entry').each(function() {		
			
			if($(this).val() != '' )
			{			// || $('#local_'+lnum).val()!=''
				lnum=$(this).attr('line_number');
				//$('#local_'+lnum).val()
				
				if(lnum >=0 )
				{
					city=$('#city_'+lnum+'').html();
					state=$('#state_'+lnum+'').html();
					mlat=$('#lat_'+lnum+'').val();
					mlong=$('#long_'+lnum+'').val();
					
					citylist += city+",";
					statelist += state+",";	
					latlist += mlat+",";
					longlist += mlong+",";	
				}
				else
				{
					citylist += ",";
					statelist += ",";	
					latlist += ",";
					longlist += ",";		
				}
								
				parray = $(this).val().split(" ");
				if(parray.length) {
					zip = parray[0];
					if(!isNaN(zip)) {
						ziplist += zip+",";
					}
				}
			}
		});
		
		

		$('.data_disp').html('');
		
		hub_run = 0
		if($('#hub').attr('checked')) hub_run = 1;
		
		//$.prompt("Zip Codes: "+ziplist+".");
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_build_run_miles_by_zip_alt",
		   data: {
		   		"ziplist":ziplist,
		   		"citylist":citylist,
		   		"statelist":statelist,
		   		"latlist":latlist,
		   		"longlist":longlist,	  		
		   		hub_run:hub_run
		   		},
		   dataType: "xml",
		   cache:false,
		   error: function() {
		   		// error
			},
		   success: function(xml) {
				$('#total_miles').html($(xml).find('Miles').text());
				$('#total_hours').html(parseFloat($(xml).find('TravelTime').text()).toFixed(2));

				total_miles = 0;
				total_timer = 0;
				
				mrr_cntr=0;
				
				$(xml).find("StopEntry").each(function() {
					//loadid = $(this).find('StopLine').text();
					//if($('#stop_'+mrr_cntr+'').val() > 0) 
					
					if(mrr_cntr==0)
					{
						$('#city_'+mrr_cntr).html($(this).find('StopLoc2').text());
						$('#state_'+mrr_cntr).html($(this).find('StopState2').text());
						$('#lat_'+mrr_cntr).val($(this).find('StopLat2').text());
						$('#long_'+mrr_cntr).val($(this).find('StopLong2').text());
					}
					else
					{
						miles = parseFloat($(this).find('StopDistance').text());
						total_miles += miles;
						
						timer = parseFloat($(this).find('StopHours').text());
						total_timer += timer;
						
						$('#city_'+mrr_cntr).html($(this).find('StopLoc2').text());
						$('#state_'+mrr_cntr).html($(this).find('StopState2').text());
						$('#lat_'+mrr_cntr).val($(this).find('StopLat2').text());
						$('#long_'+mrr_cntr).val($(this).find('StopLong2').text());
						
						$('#miles_'+mrr_cntr).html(miles.toFixed(2));
						$('#total_'+mrr_cntr).html(total_miles.toFixed(2));
					}
					mrr_cntr++;
				});
								
				map_link = generate_map_url(true);
				$('#map_frame').attr('src',map_link);
		   }
		 });		 
	}
	
	
	function generate_map_url(embed_flag) {
		address_array = new Array();
		counter = 0;
		$('.stop_entry').each(function() {
			if($(this).val() != '') {
				parray = $(this).val().split(" ");
				if(parray.length) {
					zip = parray[0];
					if(!isNaN(zip)) {
						address_array[counter] = $(this).val();
						counter++;
					}
				}
			}
		});		
		
		if(address_array.length == 1) {
			// only one address, so show exact spot, not driving directions
			map_link = "//maps.google.com/maps?f=q&q="+address_array[0];
		} else {
			
			map_link = "//maps.google.com/maps?f=d&source=s_d&saddr="+address_array[0];
			
			if(address_array.length > 1) {
				map_link += "&daddr=";
				for(i=1;i<address_array.length;i++) {
					if(i > 1) map_link += "+to:";
					map_link += address_array[i];
				}
			}		
		}
		
		if(address_array.length < 1) {
			return '';
		}
		
		if(embed_flag) map_link += "&output=embed";
		
		return map_link;
	}
	
	function view_map() {
		map_link = generate_map_url(false);
		
		if(map_link == '') {
			$.prompt("You must enter at least one valid stop in order to view the map");
			return false;
		}

		window.open(map_link);
	}
	
	
	
	/*
	

	
	
	
	
	
	
	
	function calc_run() {
		ziplist = "";
		$('.stop_entry').each(function() {
			if($(this).val() != '') {
				parray = $(this).val().split(" ");
				if(parray.length) {
					zip = parray[0];
					if(!isNaN(zip)) {
						ziplist += zip+",";
					}
				}
			}
		});

		$('.data_disp').html('');
		
		hub_run = 0
		if($('#hub').attr('checked')) hub_run = 1;
		
		 $.ajax({
		   type: "POST",
		   url: "pcmiler_ajax.php?cmd=build_run",
		   data: {"ziplist":ziplist,
		   		hub_run:hub_run},
		   dataType: "xml",
		   cache:false,
		   error: function() {
		   		// error
			},
		   success: function(xml) {
				$('#total_miles').html($(xml).find('Miles').text());
				$('#total_hours').html(parseFloat($(xml).find('TravelTime').text()).toFixed(2));

				total_miles = 0;
				next_line_mark = 1; // we want to skip the first line (0)
				
				$(xml).find("StopDistance").each(function() {
					miles = parseFloat($(this).text());
					
					value_found = 0;
					$('.stop_entry').each(function() {
						if($(this).val() != '') {
							value_found++;
							if(value_found > next_line_mark) {
								line_id = $(this).attr('stop_id');
								
								next_line_mark++;
								
								total_miles += miles;
								$('#miles_'+line_id).html(miles.toFixed(2));
								$('#total_'+line_id).html(total_miles.toFixed(2));
								
								return false;
							}
						}
					});

					
				});
				
				map_link = generate_map_url(true);
				$('#map_frame').attr('src',map_link);
		   }
		 });
	}
	
	
	*/
</script>
<? include('footer.php') ?>