<? include('header.php') ?>
<table>
<tr>
	<td valign='top'>
		<table class='admin_menu1' style='margin:10px;width:600px'>
		<tr>
			<td align='center' colspan='4'><b>ProMiles VERSION:</b></td>
		</tr>
		<tr>
			<td><b>Total Trip Miles: <span class='data_disp' id='total_miles'></span></b></td>
			<td align='right' colspan='3'><b><span class='data_disp' id='total_hours'></span> Total Hours</b></td>
		</tr>		
		<tr>
			<td colspan='4'><label><input type='checkbox' name='hub' id='hub'> Hub Run</label></td>
		</tr>
		<tr>
			<td align='left'><b>Location (City and State).  Zip is Optional.</b></td>
			<td align='left'><b>City</b></td>
			<!--<td align='left'><b>State</b></td>-->
			<td align='right'><b>Miles</b></td>
			<td align='right'><b>Total</b></td>
		</tr>
		<tr>
			<td colspan='4'><div id='mrr_msg'></div></td>
		</tr>
		<tr id='line_holder'><td colspan='4'></td></tr>
		<tr>
			<td align='center' colspan='4'>
				<input type='button' value='New Run' onclick='new_run()'>
				&nbsp;&nbsp;&nbsp;
				<input type='button' value='Calculate Run' onclick='calc_run();'>
				&nbsp;&nbsp;&nbsp;
				<input type='button' value='View Map' onclick='view_map()'>		
			</td>
		</tr>
		</table>
	</td>
	<td valign='top'>
		<div style='margin-top:15px;'>
			
			<iframe width="550" height="400" id="map_frame" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="" style="color:#0000FF;text-align:left;border:1px black solid"></iframe>
			
		</div>
	</td>
</tr>
</table>

<script type='text/javascript'>
	
	var line_count = 0;
	var map_on = 0;
	
	function add_line(add_after_line_no) 
	{		
		txt = "<tr id='line_holder_"+line_count+"'>";
		txt += "<td><input line_number='"+line_count+"' id='local_"+line_count+"' class='local_entry' stop_id='"+line_count+"' value=\"\" style='width:200px;' onChange='calc_run();'></td>";
		txt += "<td><span class='data_disp' id='city_"+line_count+"'></span><input type='hidden' name='lat_"+line_count+"' id='lat_"+line_count+"' value=''></td>";
		txt += "<td align='right'><span class='data_disp' id='miles_"+line_count+"'></span></td>";
		txt += "<td align='right'><span class='data_disp' id='total_"+line_count+"'></span></td>";
		txt += "</tr>";
		
		if(add_after_line_no == undefined) {
			$('#line_holder').before(txt);
		} else {
			// an insert line was specified, add it in where the line was specified  
			$('#line_holder_'+add_after_line_no).before(txt);
		}
		/*
		$('#local_'+line_count).autocomplete('ajax.php?cmd=search_gps_zip_codes2&line_number='+line_count+'',{																
																formatItem:formatItem, 
																onItemSelect:load_zip
		});	
		*/		
		line_count++;
	}
		
	function load_zip(row, input, extra) {
		//
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
		
	function calc_run() 
	{		
		if(map_on == 1)		return;
		
		map_on = 1;
		
		$('#mrr_msg').html("<span class='alert'><b>Recalculating...</b></span>");
		$('.data_disp').html('');
		
		locallist="";
		$('.local_entry').each(function() {	
			if($(this).val() != '' )
			{				
				locallist += $(this).val()+";";			
				
			}
			else
			{
				locallist += ";";		
			}
		});
		
		hub_run = 0
		if($('#hub').attr('checked')) hub_run = 1;
		
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_build_run_miles_by_zip_alt2",
		   data: {
		   		"locallist":locallist,	  		
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
					
					if($(this).find('Errors').text()!="")
					{
						map_on = 0;	
						
						//$.prompt("Opps! "+$(this).find('Errors').text()+"  Try again.");
						
						
						$('#mrr_msg').html("<span class='alert'><b>Opps! "+$(this).find('Errors').text()+"  Try again.</b></span>");					
						return;
					}
					
					
					if(mrr_cntr==0)
					{
						$('#city_'+mrr_cntr).html($(this).find('StopFull2').text());
						//$('#city_'+mrr_cntr).html($(this).find('StopLoc2').text());
						//$('#state_'+mrr_cntr).html($(this).find('StopState2').text());
						$('#lat_'+mrr_cntr).val($(this).find('StopLat2').text());
						$('#long_'+mrr_cntr).val($(this).find('StopLong2').text());
					}
					else
					{
						miles = parseFloat($(this).find('StopDistance').text());
						total_miles += miles;
						
						timer = parseFloat($(this).find('StopHours').text());
						total_timer += timer;
						
						$('#city_'+mrr_cntr).html($(this).find('StopFull2').text());
						//$('#city_'+mrr_cntr).html($(this).find('StopLoc2').text());
						//$('#state_'+mrr_cntr).html($(this).find('StopState2').text());
						$('#lat_'+mrr_cntr).val($(this).find('StopLat2').text());
						$('#long_'+mrr_cntr).val($(this).find('StopLong2').text());
						
						$('#miles_'+mrr_cntr).html(miles.toFixed(2));
						$('#total_'+mrr_cntr).html(total_miles.toFixed(2));
					}
					mrr_cntr++;
				});
							
				map_link = generate_map_url(true);
				$('#map_frame').attr('src',map_link);
				
				$('#mrr_msg').html("<span style='color:#00CC00;'><b>Route Planned Done.</b></span>");
				map_on = 0;
		   }
		 });		 
	}
	
	
	function generate_map_url(embed_flag) {
		address_array = new Array();
		counter = 0;
						
		$('.local_entry').each(function() {	
			if($(this).val() != '' )
			{				
				address_array[counter] = $(this).val();
				counter++;	
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
</script>
<? include('footer.php') ?>