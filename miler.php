<? include('header.php') ?>
<table>
<tr>
	<td valign='top'>
		<table class='admin_menu1' style='margin:10px;width:550px'>
		<tr>
			<td><b>Total Trip Miles: <span class='data_disp' id='total_miles'></span></b></td>
			<td align='right' colspan='4'><b><span class='data_disp' id='total_hours'></span> Total Hours</b></td>
		</tr>
		<tr>
			<td><label><input type='checkbox' name='hub' id='hub'> Hub Run</label></td>
		</tr>
		<tr>
			<td><b>Zip or City/State</b></td>
			<td align='right'><b>Miles</b></td>
			<td align='right'><b>Total</b></td>
		</tr>
		<tr id='line_holder'>
			<td></td>
		</tr>

		<tr>
			<td>
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
			
			<iframe width="550" height="400" id="map_frame" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="" style="color:#0000FF;text-align:left;border:1px black solid"></iframe>
			
		</div>
		<div id='map_url_display'>MAP NOT FOUND</div><!-- style='display:none;' -->
	</td>
</tr>
</table>

<script type='text/javascript'>
	var line_count = 0;
	

	$('#hub').click(function() {
		calc_run();
	});
	
	function add_line(add_after_line_no) {
		
		txt = "<tr id='line_holder_"+line_count+"'>";
		txt += "<td>";
		txt += "<span onclick='javascript:add_line("+line_count+")' style='cursor:pointer' alt='Click to insert new line' title='Click to insert new line'> &nbsp;&darr;&nbsp; </span> ";
		txt += "<input line_number='"+line_count+"' id='stop_"+line_count+"' class='stop_entry' stop_id='"+line_count+"' style='width:300px'>";
		txt += "</td>";
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
			pcm_lookup_stop($(this));			//use for PC Miler as main system.
			//pcm_lookup_stop_older($(this));		//use for ProMiles as main system.
		});
		
		line_count++;
		

	}
	
	for(i=0;i<20;i++) {
		add_line();
	}
	
	
	function new_run() {
		// clear the fields out
		$('.stop_entry').val('');
		$('.data_disp').html('');
		$('#stop_0').focus();
		$('#map_frame').attr('src','');
	}
	
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
		
		//build_run_older......use this below for AJAX if ProMiles in USE
		//build_run......use this below for AJAX if PC Miler in use...
		
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
				$('#map_url_display').html('URL=<b>'+map_link+'</b>.');
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
			
			tmp_str=address_array[0].replace(' ',',');
			
			map_link = "//maps.google.com/maps?f=q&q="+tmp_str;
		} else {
			
			tmp_str=address_array[0].replace(' ',',');			
			
			map_link = "//maps.google.com/maps?f=d&source=s_d&saddr="+tmp_str;
			
			
			if(address_array.length > 1) {
				map_link += "&daddr=";
				for(i=1;i<address_array.length;i++) {
					if(i > 1) map_link += "+to:";
					
					tmp_str=address_array[i].replace(' ',',');
					
					map_link += tmp_str;
				}
			}		
		}
		
		if(address_array.length < 1) {
			return '';
		}
		
		if(embed_flag) map_link += "&output=embed&t=m&key=<?=$defaultsarray['google_map_api_key'] ?>";	//&mapclient=embed &path=weight:3%7Ccolor:orange%7Cenc:polyline_data
		//ll=35.983092,-87.109315&z=10&hl=en-US&gl=US
		
		return map_link;
	}
	
	function view_map() {
		map_link = generate_map_url(false);
		
		if(map_link == '') {
			$.prompt("You must enter at least one valid stop in order to view the map");
			return false;
		}
		
		$('#map_url_display').html('URL=<b>'+map_link+'</b>.');
		window.open(map_link);
	}
</script>
<? include('footer.php') ?>