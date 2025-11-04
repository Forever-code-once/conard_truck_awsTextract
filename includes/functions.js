	function create_note_section(element_holder, section_id, xref_id) {
		
		uc_tmp = "<div id='note_container'>";
			uc_tmp += "<div class='inside_container'>";
				uc_tmp += "<div class='header'>Notes</div>";

			uc_tmp += "</div>";
			uc_tmp += "<div id='notes_holder' style='border: 1px solid grey;'>";
			uc_tmp += "</div>";
		uc_tmp += "</div>";
			
		$(element_holder).append(uc_tmp);
		
		if(section_id==10) 
		{
			display_notes_mr(section_id, xref_id);
		}
		else 
		{
			display_notes(section_id, xref_id);
		}	
	}

	function display_notes_mr(section_id, xref_id) {
		$.ajax({
			type: "POST",
			url: "ajax.php?cmd=display_notes_mr",
			data: {"section_id":section_id,
				"xref_id":xref_id},
			success: function(data) {
				$('#notes_holder').html(data);
			}
		});
	}	
	function display_notes(section_id, xref_id) {
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=display_notes",
		   data: {"section_id":section_id,
		   		"xref_id":xref_id},
		   success: function(data) {
		   		$('#notes_holder').html(data);
		   }
		 });
	}
	
	function mrr_update_note_access(id)
	{
		var note_access=parseInt($('#note_entry_'+id+'_access').val());
		
		if(note_access >= 0 && id > 0)
		{ 		
     		$.ajax({
          		url: "ajax.php?cmd=mrr_update_note_entry_access",
          		type: "post",
          		dataType: "xml",
          		data: {				
          			"id":id,
          			"access":note_access
          		},
          		error: function() {
          			$.prompt("Error: Cannot modify access level at this time.");
          		},
          		success: function(xml) {	     				
          			$.noticeAdd({text: "Access Level updated for note, and will be used on the next page load or refresh."});
          		}
          	});
     	}	
	}
	
	
	function mrr_destroy_upload_section(element_holder)
	{
		$(''+element_holder+'').html('');	
	}
	function create_upload_section(element_holder, section_id, xref_id) 
	{			
		uc_tmp = "<div id='upload_container'>";
			uc_tmp += "<div class='inside_container'>";
				uc_tmp += "<div class='header'>Attach File</div>";
				uc_tmp += "<div id='browse_holder'>";
					uc_tmp += "<input id='fileInput' name='fileInput' type='file' />";
				uc_tmp += "</div>";
			uc_tmp += "</div>";
			uc_tmp += "<div id='attachment_holder'></div>";
		uc_tmp += "</div>";
			
		$(element_holder).append(uc_tmp);
		
		display_files(section_id, xref_id);
		
		$('#fileInput').uploadify({
			'uploader'  : 'includes/uploadify/uploadify.swf',
			'script'    : 'uploadify.php',
			'cancelImg' : 'includes/uploadify/cancel.png',
			'sizeLimit' : 500 * 1024 * 1024,
			'fileExt'	  : global_ftype_array,
			'fileDesc'  : 'Documents',
			'method'    : 'post',
			'scriptData': {'user_id': global_user_id,
						'section_id': section_id,
						'xref_id': xref_id},
			'auto'      : true,
			'folder'    : 'documents',
			'onAllComplete' : function(event, data) {
				if(data.errors) {
					//alert('oops, an error came up');
				} else {
					display_files(section_id, xref_id);
				}
			}
		});
	}
	function create_upload_section_alt(element_holder, section_id, xref_id) 
	{		
		uc_tmp="";
		//uc_tmp = "<div id='upload_container'>";
		//	uc_tmp += "<div class='inside_container'>";
		//		uc_tmp += "<div class='header'>Attach File</div>";
		//		uc_tmp += "<div id='browse_holder'>";
		//			uc_tmp += "<input id='fileInput' name='fileInput' type='file' />";
		//		uc_tmp += "</div>";
		//	uc_tmp += "</div>";
			uc_tmp += "<div id='attachment_holder'></div>";
		//uc_tmp += "</div>";
			
		$(element_holder).append(uc_tmp);
		
		display_files(section_id, xref_id);
		/*
		$('#fileInput').uploadify({
			'uploader'  : 'includes/uploadify/uploadify.swf',
			'script'    : 'uploadify.php',
			'cancelImg' : 'includes/uploadify/cancel.png',
			'sizeLimit' : 500 * 1024 * 1024,
			'fileExt'	  : global_ftype_array,
			'fileDesc'  : 'Documents',
			'method'    : 'post',
			'scriptData': {'user_id': global_user_id,
						'section_id': section_id,
						'xref_id': xref_id},
			'auto'      : true,
			'folder'    : 'documents',
			'onAllComplete' : function(event, data) {
				if(data.errors) {
					//alert('oops, an error came up');
				} else {
					display_files(section_id, xref_id);
				}
			}
		});
		*/
	}
	
	function display_files(section_id, xref_id) {
		 
		 //alert("Section: "+section_id+" Attach Files for ID="+xref_id+".");
		 
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=display_attachments",
		   data: {"section_id":section_id,
		   		"xref_id":xref_id},
		   success: function(data) {
		   		$('#attachment_holder').html(data);
		   }
		 });
	}
	
	function delete_attachment(id) {
		if(confirm("Are you sure you want to delete this attachment?")) {
			$('#attachment_row_'+id).remove();
			
			 $.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=delete_attachment",
			   data: {"id":id},
			   success: function(data) {
			   		
			   }
		 	});
		}
	}
	
	function initialCap(field) {
		// capitalizes the first letts
	   field.value = field.value.substr(0, 1).toUpperCase() + field.value.substr(1);
	}
	
	function fullCap(field) {
		// capitalizes the first letts
	   field.value = field.value.toUpperCase();
	}
	
	function toTitleCase(field) {
		str = field.value;
	    field.value = str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	}
	
	function add_note_entry(section_id, xref_id, note_text,note_restriction) 
	{		
		if((xref_id == 0 || xref_id == '') && parseInt(section_id) > 1)
		{
			$.prompt("You must save this entry before adding notes to it");
			return false;
		}
		
		$('#note_entry_loading').show();
		
		 $.ajax({
		   type: 'POST',
		   url: 'ajax.php?cmd=add_note_entry',
		   data: {
		   		'section_id':section_id,
		   		xref_id:xref_id,
		   		note:note_text,
		   		"note_restrict":note_restriction,
		   		"sendit":0
		   		},
		   success: function(data) {
			   if(section_id==10)
			   {
				   display_notes_mr(section_id, xref_id);
			   }
			   else
			   {
				   display_notes(section_id, xref_id);
			   }				   
		   		$('#note_entry_loading').hide();
		   		$.noticeAdd({text: "Success - Note Created."});
		   }
		 });
	}
	function add_note_entry_send(section_id, xref_id, note_text, note_restriction) {
		
		if(xref_id == 0 || xref_id == '') {
			$.prompt("You must save this entry before adding notes to it");
			return false;
		}
		
		$('#note_entry_loading').show();
		
		 $.ajax({
		   type: 'POST',
		   url: 'ajax.php?cmd=add_note_entry',
		   data: {
		   		'section_id':section_id,
		   		xref_id:xref_id,
		   		note:note_text,
		   		"note_restrict":note_restriction,
		   		"sendit":1
		   		},
		   success: function(data) {
			   if(section_id==10)
			   {
				   display_notes_mr(section_id, xref_id);
			   }
			   else
			   {
				   display_notes(section_id, xref_id);
			   }
		   		$('#note_entry_loading').hide();
		   }
		 });
	}
	
	function delete_note_entry(note_id) {
		$.prompt("Are you sure you want to delete this note?", {
				buttons: {Yes: true, No:false},
				submit: function(v, m, f) {
					if(v) {
						 $.ajax({
						   type: 'POST',
						   url: 'ajax.php?cmd=delete_note_entry',
						   data: {note_id:note_id},
						   success: function(data) {
						   		$('#note_entry_'+note_id).remove();
						   }
						 });
					}
				}
			}
		);
	}
	
function formatCurrency(num) {
	num = num.toString().replace(/\$|\,/g,'');
	if(isNaN(num))
	num = "0";
	sign = (num == (num = Math.abs(num)));
	num = Math.floor(num*100+0.50000000001);
	cents = num%100;
	num = Math.floor(num/100).toString();
	if(cents<10)
	cents = "0" + cents;
	for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
	num = num.substring(0,num.length-(4*i+3))+','+
	num.substring(num.length-(4*i+3));
	return (((sign)?'':'-') + '$' + num + '.' + cents);
}
function mrrformatNumber(num) {
	num = num.toString().replace(/\$|\,/g,'');
	if(isNaN(num))
	num = "0";
	sign = (num == (num = Math.abs(num)));
	num = Math.floor(num*100+0.50000000001);
	cents = num%100;
	num = Math.floor(num/100).toString();
	if(cents<10)
	cents = "0" + cents;
	for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
	num = num.substring(0,num.length-(4*i+3))+','+
	num.substring(num.length-(4*i+3));
	return (((sign)?'':'-') + '' + num + '.' + cents);	//$
}

function get_amount(str_amount) {
	
	tmp_amount = str_amount.replace("$","");
	tmp_amount = tmp_amount.replace(",","");
	if(isNaN(tmp_amount) || tmp_amount == '') tmp_amount = 0;
	
	return parseFloat(tmp_amount);
}

function js_update_stop_odometer(stop_id, load_id) {
	
	 $.ajax({
	   type: 'POST',
	   url: 'ajax.php?cmd=update_stop_odometer',
	   data: {stop_id:stop_id,
	   		load_id: load_id,
	   		odometer_reading: $('#odometer_reading_'+stop_id).val()
	   },
	   success: function(data) {
	   		//load_dispatchs();
	   		//$.noticeAdd({text: "Success - Odometer Value Updated"});
	   }
	 });	
}

function js_update_predispatch(load_id) {
	 $.ajax({
	   type: 'POST',
	   url: 'ajax.php?cmd=update_predispatch',
	   data: {load_id: load_id,
	   		predispatch_odometer: $('#predispatch_odometer').val(),
	   		predispatch_city: $('#predispatch_city').val(),
	   		predispatch_state: $('#predispatch_state').val(),
	   		predispatch_zip: $('#predispatch_zip').val()
	   },
	   success: function(data) {
	   		//load_dispatchs();
	   		//$.noticeAdd({text: "Success - Odometer Value Updated"});
	   }
	 });	
}

function js_update_stop_commpleted(stop_id,use_date) {
	time_val = $('#linedate_completed_time_'+stop_id).val().replace(":","");
	thour = time_val.substr(0,2);
	tminute = time_val.substr(2,2);
	use_time = thour + ':' + tminute;
	
	time_val2 = $('#linedate_arrival_time_'+stop_id).val().replace(":","");
	thour2 = time_val2.substr(0,2);
	tminute2 = time_val2.substr(2,2);
	use_time2 = thour2 + ':' + tminute2;
	
	mrr_last_stop=parseInt($('#stop_dispatch_'+stop_id+'_last_stop').val());
	
	 $.ajax({
	   type: 'POST',
	   url: 'ajax.php?cmd=update_stop_completed',
	   data: {
	   	moder:0,
	   	"is_last_stop":mrr_last_stop,
	   	stop_id:stop_id,
	   	linedate_completed:$('#linedate_completed_'+stop_id).val(),
	   	linedate_completed_time:use_time,
	   	linedate_arrival:$('#linedate_arrival_'+stop_id).val(),
	   	linedate_arrival_time:use_time2,	
	   	"switch_dispatch_id": $('#switch_disp_id_'+stop_id+'').val(),   	
	   	"switch_customer": $('#switch_customer_'+stop_id).val(),
	   	"switch_driver": $('#switch_driver_'+stop_id).val(),
	   	"switch_local_city": $('#switch_local_city_'+stop_id).val(),
	   	"switch_local_state": $('#switch_local_state_'+stop_id).val(),
	   	"switch_local_zip": $('#switch_local_zip_'+stop_id).val(),
	   	"switch_notes": $('#switch_notes_'+stop_id).val(),
	   	"switch_linedate": $('#switch_linedate_'+stop_id).val(),
	   	"switch_dedicated_trailer": $('#switch_dedicated_trailer_'+stop_id).val(), 
	   	"starting_trailer_id": $('#starting_trailer_'+stop_id+'').val(),
	   	"drop_trailer_switcher": $('#switch_drop_trailer_'+stop_id+'').val(),
	   	"stop_grade_id": $('#stop_grade_id_'+stop_id+'').val(),
	   	"stop_grade_note": $('#stop_grade_note_'+stop_id+'').val()
	   	},
	   	dataType: "xml",
		cache:false,	
	   success: function(xml) {
	   			   		
	   		resulter=$(xml).find('rslt').text();
			if(resulter==1)
			{
				//$.prompt("Stop Date Completed - Saved successfully");
				$('#stop_grade_id_'+stop_id+'').val($(xml).find('StopGradeID').text());
				$('#stop_grade_note_'+stop_id+'').val($(xml).find('StopGradeNote').text());
				load_dispatchs();
			}
			else
			{
				$.prompt("<span class='alert'>ERROR:</span> "+$(xml).find('ErrorMsg').text()+".");	
			}  
					
	   }
	 });	
}
function js_update_stop_commpleted_time(stop_id,use_date) {
	time_val = $('#linedate_completed_time_'+stop_id).val().replace(":","");
	thour = time_val.substr(0,2);
	tminute = time_val.substr(2,2);
	use_time = thour + ':' + tminute;
	
	time_val2 = $('#linedate_arrival_time_'+stop_id).val().replace(":","");
	thour2 = time_val2.substr(0,2);
	tminute2 = time_val2.substr(2,2);
	use_time2 = thour2 + ':' + tminute2;
	
	mrr_last_stop=parseInt($('#stop_dispatch_'+stop_id+'_last_stop').val());
	
	 $.ajax({
	   type: 'POST',
	   url: 'ajax.php?cmd=update_stop_completed',
	   data: {
	   	moder:1,
	   	"is_last_stop":mrr_last_stop,
	   	stop_id:stop_id,
	   	linedate_completed:$('#linedate_completed_'+stop_id).val(),
	   	linedate_completed_time:use_time,
	   	linedate_arrival:$('#linedate_arrival_'+stop_id).val(),
	   	linedate_arrival_time:use_time2,	
	   	"switch_dispatch_id": $('#switch_disp_id_'+stop_id+'').val(),   	
	   	"switch_customer": $('#switch_customer_'+stop_id).val(),
	   	"switch_driver": $('#switch_driver_'+stop_id).val(),
	   	"switch_local_city": $('#switch_local_city_'+stop_id).val(),
	   	"switch_local_state": $('#switch_local_state_'+stop_id).val(),
	   	"switch_local_zip": $('#switch_local_zip_'+stop_id).val(),
	   	"switch_notes": $('#switch_notes_'+stop_id).val(),
	   	"switch_linedate": $('#switch_linedate_'+stop_id).val(),
	   	"switch_dedicated_trailer": $('#switch_dedicated_trailer_'+stop_id).val(), 
	   	"starting_trailer_id": $('#starting_trailer_'+stop_id+'').val(),
	   	"drop_trailer_switcher": $('#switch_drop_trailer_'+stop_id+'').val(),
	   	"stop_grade_id": $('#stop_grade_id_'+stop_id+'').val(),
	   	"stop_grade_note": $('#stop_grade_note_'+stop_id+'').val()
	   	},
	   	dataType: "xml",
		cache:false,	
	   success: function(xml) {
	   	
	   		resulter=$(xml).find('rslt').text();
			if(resulter==1)
			{
				//$.prompt("Stop Date Completed - Saved successfully");
				$('#stop_grade_id_'+stop_id+'').val($(xml).find('StopGradeID').text());
				$('#stop_grade_note_'+stop_id+'').val($(xml).find('StopGradeNote').text());
				load_dispatchs();
			}
			else
			{
				$.prompt("<span class='alert'>ERROR:</span> "+$(xml).find('ErrorMsg').text()+".");	
			}	 		
	   }
	 });	
	 
	 //turned off for Donovan (March 2015) now that switch system works....
	 /*
	 $.prompt("Do you want to drop or switch this trailer?", {
          			buttons: {Yes: true, No:false},
          			submit: function(v, m, f) {
          				if(v) {
          					 mrr_drop_switched_trailer_js(stop_id,1);
          				}
          			}
          		}
          	);
      */
}

function mrr_text_box_char_counter(mrrbox,charlim,counterfield)
{
	mrr_text=$('#'+mrrbox+'').val();		//assume input element/textarea
	
	$.ajax({
	   type: 'POST',
	   url: 'ajax.php?cmd=mrr_count_text_box_characters',
	   data: {
	   	"text_box":mrr_text,
	   	"char_limit":charlim
	   	},
	   async:false,
	   success: function(xml) {
	   		if($(xml).find('rslt').text() == '1') 
	   		{	   			
	   			limit_check=$(xml).find('mrrLim').text()
	   			
	   			if( limit_check == ""+charlim+"/"+charlim+"") 
	   			{	//limit reached, so change replace text box value with limited text.
	   				$('#'+mrrbox+'').val(  $(xml).find('mrrTxt').text() );			//assume input element/textarea
	   				$('#'+counterfield+'_alert').html('You have reached the character limit.');
	   			}
	   			else
	   			{
	   				$('#'+counterfield+'_alert').html('');		
	   			}
	   			$('#'+counterfield+'').html(  $(xml).find('mrrLim').text()  );			//assume div or span	   			
	   		} 
	   		else 
	   		{
	   			$.prompt("Error counting text box characters");
	   		}
	   }
	});	
}

function delete_dispatch_expense(id) {
	$.prompt("Are you sure you want to delete this expense?", {
			buttons: {Yes: true, No:false},
			submit: function(v, m, f) {
				if(v) {
					 $.ajax({
					   type: 'POST',
					   url: 'ajax.php?cmd=delete_dispatch_expense',
					   data: {expense_id:id},
					   success: function(data) {
					   		$('#row_expense_'+id).remove();
					   		load_dispatch_expenses();
					   }
					 });
				}
			}
		}
	);	
}

function formatItem(row) {
	return row[0] + "<br><i>" + row[1] + "</i>";
}

function simple_time_check() {
	time_val = $(this).val().replace(":","");
	
	if(time_val.length == 0) return false;
	
	if(time_val.length > 0 && time_val.length != 4) {
		$.prompt("Invalid time specified '"+time_val+"'. The time must be entered in a 4 digit, 24 hours format. <p>Example: '1545' would be equal to 15:45, or 3:45pm");
		return false;
	}
	
	thour = time_val.substr(0,2);
	tminute = time_val.substr(2,2);
	
	if(thour > 24 || thour < 0) {
		$.prompt("You entered an hour of '"+thour+"', valid hour ranges are between 0 and 23");
		return false;
	}
	
	if(tminute < 0 || tminute > 59) {
		$.prompt("You entered a minute entry of '"+tminute+"', valid minute ranges are from 0 to 59");
		return false;
	}
	
	$(this).val(thour + ':' + tminute);
}

function parent_window_refresh(close_after_refresh) {
	window.opener.focus();	
	window.opener.location.reload();
	
	//if(window.opener.focus())
	//{
	//	window.opener.location.reload();
	//}
	if(close_after_refresh) 
	{
		window.close();
	}
}

function parent_window_submit(close_after_refresh) {
	window.opener.focus();
	if(window.opener.document.forms[1] != undefined) {
		window.opener.document.forms[1].submit();
	} else {
	    // a form doesn't exist, so just reload the page
		window.opener.location.reload();
	}

	//if(window.opener.focus())
	//{
	//	if(window.opener.document.forms[1] != undefined) {
	//		window.opener.document.forms[1].submit();
	//	} else {
			// a form doesn't exist, so just reload the page
	//		window.opener.location.reload();
	//	}
	//}
	if(close_after_refresh) 
	{
		window.close();
	}
}

var mrr_default_window_size_load_width=1600;
var mrr_default_window_size_load_height=700;
var mrr_default_window_size_dispatch_width=1600;
var mrr_default_window_size_dispatch_height=700;
var mrr_default_window_size_trailer_drop_width=1200;
var mrr_default_window_size_trailer_drop_height=700;
var mrr_default_window_size_misc_width=700;
var mrr_default_window_size_misc_height=500;	
//
//height=700,width=1200

function edit_dropped_trailer(drop_trailer_id) {
	windowname = window.open('trailer_drop.php?id='+drop_trailer_id,'drop_trailer','height='+mrr_default_window_size_trailer_drop_height+',width='+mrr_default_window_size_trailer_drop_width+',menubar=no,location=no,resizable=yes,status=no,scrollbars=yes');
	//windowname.focus();
}

function new_load() {
	windowname = window.open('manage_load.php','edit_note_date','height='+mrr_default_window_size_load_height+',width='+mrr_default_window_size_load_width+',menubar=no,location=no,resizable=yes,status=no,scrollbars=yes');
	//windowname.focus();
}

function confirm_delete_driver_expense(id) {
	
	$.prompt("Are you sure you want to delete this driver expense?", {
			buttons: {Yes: true, No:false},
			submit: function(v, m, f) {
				if(v) {
					 $.ajax({
					   type: 'POST',
					   url: 'ajax.php?cmd=delete_driver_expense',
					   data: {driver_expense_id:id},
					   success: function(data) {
					   		$('#driver_expense_entry_'+id).remove();
					   }
					 });
				}
			}
		}
	);
	
	
}

function add_driver_expense() {
	if($('#driver_id').val() == 0) {
		$.prompt("You must select the driver");
		return false;
	}
	
	if($('#linedate').val() == '') {
		$.prompt("You must enter the expense date");
		return false;
	}
	
	if($('#expense_type_id').val() == 0) {
		$.prompt("You must select the expense type");
		return false;
	}
	
	if(get_amount($('#amount_billable').val()) == 0) {
		$.prompt("You must enter a valid Billable pay amount");
		return false;
	}
	
	 $.ajax({
	   type: 'POST',
	   url: 'ajax.php?cmd=add_driver_expense',
	   data: {driver_id:$('#driver_id').val(),
	   	linedate:$('#linedate').val(),
	   	expense_type_id:$('#expense_type_id').val(),
	   	desc_long:$('#description').val(),
	   	amount:get_amount($('#amount').val()),
	   	mrr_chart_id:$('#mrr_chart_id').val(),
	   	mrr_chart_name:$('#mrr_chart_name').val(),
	   	payroll: ($('#payroll').attr('checked') ? 1 : 0),
	   	amount_billable:get_amount($('#amount_billable').val())
	   	},
	   async:false,
	   success: function(xml) {
			$('#driver_expense_history').html(load_driver_expenses($('#driver_id').val()));
			
			$('#expense_type_id').val(0);
			$('#description').val('');
			$('#amount').val('');
			$('#amount_billable').val('');
			$('#mrr_chart_name').val('');
			$('#mrr_chart_id').val(0);
	   }
	 });	
	
}

function confirm_delete_driver_unavailable(id) {
	
	$.prompt("Are you sure you want to delete this driver unavailability?", {
			buttons: {Yes: true, No:false},
			submit: function(v, m, f) {
				if(v) {
					 $.ajax({
					   type: 'POST',
					   url: 'ajax.php?cmd=delete_driver_unavailability',
					   data: {id:id},
					   success: function(data) {
					   		$('#driver_unavailability_entry_'+id).hide();
					   }
					 });
				}
			}
		}
	);
	
	
}

function mrr_edit_driver_unavailable(id,driver_id,datefrom,dateto,reas) {
	
	//alert('Id='+id+' Driver='+driver_id+' From='+datefrom+' To='+dateto+' Reason='+reas+'.');	
	
	txt = "<table>";
	txt = txt + "<tr><td colspan='2'><b>Edit Unavailable Driver Entry</b></td></tr>";
	txt = txt + "<tr><td>Unavailable Start:</td><td><input name='unavailable_from' id='unavailable_from' value='"+datefrom+"'> (mm/dd/yyyy)</td></tr>";
	txt = txt + "<tr><td>Unavailable End:</td><td><input name='unavailable_end' id='unavailable_end' value='"+dateto+"'> (mm/dd/yyyy)</td></tr>";
	txt = txt + "<tr><td>Unavailable Reason:</td><td><input name='unavailable_reason' id='unavailable_reason' value='"+reas+"' style='width:350px;'></td></tr>";
	txt = txt + "</table>";
	
	function update_driver_unavailable_callback(v, m, f) {
		
		if(v) {
			 $.ajax({
			   type: 'POST',
			   url: 'ajax.php?cmd=mrr_edit_driver_unavailability',
			   dataType:"xml",
			   async: false,
			   data: {
			   	"entry_id":id,
			   	"driver_id":driver_id,
			   	"c_reason":f.unavailable_reason,
			   	"linedate_start":f.unavailable_from,
			   	"linedate_end":f.unavailable_end},
			   	success: function(xml) {
					 window.location.reload();
			   	}
			 });

		}
		
	}
	
	$.prompt(txt, {
		buttons: { Okay: true, Cancel: false },
		loaded: function() {
			$('#unavailable_from').datepicker();
			$('#unavailable_end').datepicker();
		},
		submit: function(v,m,f) {
			if(v) {
				if(f.unavailable_from == '' || f.unavailable_end == '') {
					$.prompt("You must specify both the start and end date");
					return false;
				}
			}
		},
		callback: update_driver_unavailable_callback
	});
}
function add_driver_unavailable(driver_id) {
	txt = "<table>";
	txt = txt + "<tr><td>Unavailable Start:</td><td><input name='unavailable_from' id='unavailable_from'> (mm/dd/yyyy)</td></tr>";
	txt = txt + "<tr><td>Unavailable End:</td><td><input name='unavailable_end' id='unavailable_end'> (mm/dd/yyyy)</td></tr>";
	txt = txt + "<tr>";
	txt = txt + 	"<td>Unavailable Interval:</td>";
	txt = txt + 	"<td>";
	txt = txt + 		"<select name='unavailable_interval' id='unavailable_interval'>";
	txt = txt + 		"<option value='0'>N/A</option>";
	txt = txt + 		"<option value='1'>Every Monday</option>";
	txt = txt + 		"<option value='2'>Every Tuesday</option>";
	txt = txt + 		"<option value='3'>Every Wednesday</option>";
	txt = txt + 		"<option value='4'>Every Thursday</option>";
	txt = txt + 		"<option value='5'>Every Friday</option>";
	txt = txt + 		"<option value='6'>Every Saturday</option>";
	txt = txt + 		"<option value='7'>Every Sunday</option>";
	txt = txt + 		"</select> {Optional}";
	txt = txt + 	"</td>";
	txt = txt + "</tr>";	
	txt = txt + "<tr><td>Unavailable Reason:</td><td><input name='unavailable_reason' id='unavailable_reason' style='width:350px;'></td></tr>";
	txt = txt + "</table>";
	
	function add_driver_unavailable_callback(v, m, f) {
		
		if(v) {
			 $.ajax({
			   type: 'POST',
			   url: 'ajax.php?cmd=add_driver_unavailability',
			   dataType:"xml",
			   async: false,
			   data: {driver_id:driver_id,
			   	c_reason:f.unavailable_reason,
				unavailable_interval:f.unavailable_interval,   
			   	linedate_start:f.unavailable_from,
			   	linedate_end:f.unavailable_end},
			   	success: function(xml) {
					 $('#driver_unavailable_history').html(load_driver_unavailable(driver_id));
			   	}
			 });

		}
		
	}
	
	$.prompt(txt, {
		buttons: { Okay: true, Cancel: false },
		loaded: function() {
			$('#unavailable_from').datepicker();
			$('#unavailable_end').datepicker();
		},
		submit: function(v,m,f) {
			if(v) {
				if(f.unavailable_from == '' || f.unavailable_end == '') {
					$.prompt("You must specify both the start and end date");
					return false;
				}
			}
		},
		callback: add_driver_unavailable_callback
	});
}

function load_driver_unavailable(driver_id) {
	return_html = '';

	 $.ajax({
	   type: 'POST',
	   url: 'ajax.php?cmd=load_driver_unavailable',
	   data: {driver_id:driver_id},
	   async:false,
	   success: function(xml) {
	   		if($(xml).find('rslt').text() == '1') {
	   			return_html = $(xml).find('html').text();
	   		} else {
	   			return_html = 'Error loading unavailability';
	   		}
	   }
	 });	
	 
	 return return_html;
}

function load_driver_expenses(driver_id) {
	
	return_html = '';
	
	 $.ajax({
	   type: 'POST',
	   url: 'ajax.php?cmd=load_driver_expenses',
	   data: {driver_id:driver_id},
	   async:false,
	   success: function(xml) {
	   		if($(xml).find('rslt').text() == '1') {
	   			return_html = $(xml).find('html').text();
	   		} else {
	   			return_html = 'Error loading expenses';
	   		}
	   }
	 });	
	 
	 return return_html;
}



function selectElementText(el, win) {
    win = win || window;
    var doc = win.document, sel, range;
    if (win.getSelection && doc.createRange) {
        sel = win.getSelection();
        range = doc.createRange();
        range.selectNodeContents(el);
        sel.removeAllRanges();
        sel.addRange(range);
    } else if (doc.body.createTextRange) {
        range = doc.body.createTextRange();
        range.moveToElementText(el);
        range.select();
    }
}

function enter_odo(truck_id, truck_name, old_odom ) {
	
	var now = new Date();
	var last_odom=old_odom;
	prompt_txt = "<table>";	
	prompt_txt += "<tr><td colspan='2'>Please enter the odometer reading for truck '"+truck_name+"'</td></tr>";
	prompt_txt += "<tr><td colspan='2'>The last odometer reading for this truck  was '"+last_odom+"'</td></tr>";
	prompt_txt += "<tr><td>Miles:</td><td><input name='odometer' id='odometer'></td></tr>";
	prompt_txt += "<tr><td>Date of reading:</td><td><input name='odometer_linedate' id='odometer_linedate' value='"+(now.getMonth()+1)+"/"+now.getDate()+"/"+now.getFullYear()+"'></td></tr></table>";
	$.prompt(prompt_txt,{
			buttons: {Okay:1, Cancel:0},
			callback: function(v,m,f) {
				if(v != undefined && v) {
					if(f.odometer == '') {
						$.prompt("Odometer miles is a required field");
						return false;
					}
					if(isNaN(f.odometer)) {
						$.prompt("Invalid odometer. Please enter a number only");
						return false;
					}
					if(f.odometer < last_odom) {
						$.prompt("Invalid odometer. This or the last odometer reading is in error.");
						return false;
					}
					
					 $.ajax({
					   type: "POST",
					   url: "ajax.php?cmd=save_odometer_reading",
					   data: {"truck_id":truck_id,
					   		odometer:f.odometer,
					   		linedate:f.odometer_linedate},
					   dataType: "xml",
					   cache:false,
					   success: function(xml) {
							load_odometer_alert();
					   }
					 });
				}
			},
			loaded: function() {
				$('#odometer').focus();
				$('#odometer_linedate').datepicker();
			}
		});
}


function pcm_lookup_stop(use_this) {
	//if($(use_this).val() == '') return false;
	
	loc_holder = $(use_this);
	
	 $.ajax({
	   type: "POST",
	   url: "pcmiler_ajax.php?cmd=verify_stop",
	   data: {"location":$(use_this).val()},
	   dataType: "xml",
	   cache:false,
	   error: function() {
	   		// error
		},
	   success: function(xml) {
			use_loc = $(xml).find("UseLocation").text();
			
			loc_holder.val(use_loc);
			//alert(use_loc);
			calc_run();
	   }
	 });
}
function pcm_lookup_stop_older(use_this) {
	//if($(use_this).val() == '') return false;
	
	loc_holder = $(use_this);
	
	 $.ajax({
	   type: "POST",
	   url: "pcmiler_ajax.php?cmd=verify_stop_older",
	   data: {"location":$(use_this).val()},
	   dataType: "xml",
	   cache:false,
	   error: function() {
	   		// error
		},
	   success: function(xml) {
			use_loc = $(xml).find("UseLocation").text();
			
			loc_holder.val(use_loc);
			//alert(use_loc);
			calc_run();
	   }
	 });
}

//added from printing ability like Accounting Program...
function HtmlEncode(s)
{
  var el = document.createElement("div");
  el.innerText = el.textContent = s;
  s = el.innerHTML;
  delete el;
  return s;
}


function get_html_translation_table(table, quote_style) {
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: noname
    // %          note: It has been decided that we're not going to add global
    // %          note: dependencies to php.js. Meaning the constants are not
    // %          note: real constants, but strings instead. integers are also supported if someone
    // %          note: chooses to create the constants themselves.
    // %          note: Table from http://www.the-art-of-web.com/html/character-codes/
    // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
    // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
    
    var entities = {}, histogram = {}, decimal = 0, symbol = '';
    var constMappingTable = {}, constMappingQuoteStyle = {};
    var useTable = {}, useQuoteStyle = {};
    
    useTable      = (table ? table.toUpperCase() : 'HTML_SPECIALCHARS');
    useQuoteStyle = (quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT');
    
    // Translate arguments
    constMappingTable[0]      = 'HTML_SPECIALCHARS';
    constMappingTable[1]      = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';
    
    // Map numbers to strings for compatibilty with PHP constants
    if (!isNaN(useTable)) {
        useTable = constMappingTable[useTable];
    }
    if (!isNaN(useQuoteStyle)) {
        useQuoteStyle = constMappingQuoteStyle[useQuoteStyle];
    }
    
    if (useQuoteStyle != 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';
    }
 
    if (useQuoteStyle == 'ENT_QUOTES') {
        entities['39'] = '&#039;';
    }
 
    if (useTable == 'HTML_SPECIALCHARS') {
        // ascii decimals for better compatibility
        //entities['38'] = '&amp;';
        entities['60'] = '&lt;';
        entities['62'] = '&gt;';
    } else if (useTable == 'HTML_ENTITIES') {
        // ascii decimals for better compatibility
      entities['38']  = '&amp;';
      entities['60']  = '&lt;';
      entities['62']  = '&gt;';
      entities['160'] = '&nbsp;';
      entities['161'] = '&iexcl;';
      entities['162'] = '&cent;';
      entities['163'] = '&pound;';
      entities['164'] = '&curren;';
      entities['165'] = '&yen;';
      entities['166'] = '&brvbar;';
      entities['167'] = '&sect;';
      entities['168'] = '&uml;';
      entities['169'] = '&copy;';
      entities['170'] = '&ordf;';
      entities['171'] = '&laquo;';
      entities['172'] = '&not;';
      entities['173'] = '&shy;';
      entities['174'] = '&reg;';
      entities['175'] = '&macr;';
      entities['176'] = '&deg;';
      entities['177'] = '&plusmn;';
      entities['178'] = '&sup2;';
      entities['179'] = '&sup3;';
      entities['180'] = '&acute;';
      entities['181'] = '&micro;';
      entities['182'] = '&para;';
      entities['183'] = '&middot;';
      entities['184'] = '&cedil;';
      entities['185'] = '&sup1;';
      entities['186'] = '&ordm;';
      entities['187'] = '&raquo;';
      entities['188'] = '&frac14;';
      entities['189'] = '&frac12;';
      entities['190'] = '&frac34;';
      entities['191'] = '&iquest;';
      entities['192'] = '&Agrave;';
      entities['193'] = '&Aacute;';
      entities['194'] = '&Acirc;';
      entities['195'] = '&Atilde;';
      entities['196'] = '&Auml;';
      entities['197'] = '&Aring;';
      entities['198'] = '&AElig;';
      entities['199'] = '&Ccedil;';
      entities['200'] = '&Egrave;';
      entities['201'] = '&Eacute;';
      entities['202'] = '&Ecirc;';
      entities['203'] = '&Euml;';
      entities['204'] = '&Igrave;';
      entities['205'] = '&Iacute;';
      entities['206'] = '&Icirc;';
      entities['207'] = '&Iuml;';
      entities['208'] = '&ETH;';
      entities['209'] = '&Ntilde;';
      entities['210'] = '&Ograve;';
      entities['211'] = '&Oacute;';
      entities['212'] = '&Ocirc;';
      entities['213'] = '&Otilde;';
      entities['214'] = '&Ouml;';
      entities['215'] = '&times;';
      entities['216'] = '&Oslash;';
      entities['217'] = '&Ugrave;';
      entities['218'] = '&Uacute;';
      entities['219'] = '&Ucirc;';
      entities['220'] = '&Uuml;';
      entities['221'] = '&Yacute;';
      entities['222'] = '&THORN;';
      entities['223'] = '&szlig;';
      entities['224'] = '&agrave;';
      entities['225'] = '&aacute;';
      entities['226'] = '&acirc;';
      entities['227'] = '&atilde;';
      entities['228'] = '&auml;';
      entities['229'] = '&aring;';
      entities['230'] = '&aelig;';
      entities['231'] = '&ccedil;';
      entities['232'] = '&egrave;';
      entities['233'] = '&eacute;';
      entities['234'] = '&ecirc;';
      entities['235'] = '&euml;';
      entities['236'] = '&igrave;';
      entities['237'] = '&iacute;';
      entities['238'] = '&icirc;';
      entities['239'] = '&iuml;';
      entities['240'] = '&eth;';
      entities['241'] = '&ntilde;';
      entities['242'] = '&ograve;';
      entities['243'] = '&oacute;';
      entities['244'] = '&ocirc;';
      entities['245'] = '&otilde;';
      entities['246'] = '&ouml;';
      entities['247'] = '&divide;';
      entities['248'] = '&oslash;';
      entities['249'] = '&ugrave;';
      entities['250'] = '&uacute;';
      entities['251'] = '&ucirc;';
      entities['252'] = '&uuml;';
      entities['253'] = '&yacute;';
      entities['254'] = '&thorn;';
      entities['255'] = '&yuml;';
    } else {
        throw Error("Table: "+useTable+' not supported');
        return false;
    }
    
    // ascii decimals to real symbols
    for (decimal in entities) {
        symbol = String.fromCharCode(decimal)
        histogram[symbol] = entities[decimal];
    }
    
    return histogram;
}

function htmlentities (string, quote_style) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: nobbler
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // -    depends on: get_html_translation_table
    // *     example 1: htmlentities('Kevin & van Zonneveld');
    // *     returns 1: 'Kevin &amp; van Zonneveld'
 
    var histogram = {}, symbol = '', tmp_str = '', entity = '';
    tmp_str = string.toString();
    
    if (false === (histogram = get_html_translation_table('HTML_SPECIALCHARS', quote_style))) {
        return false;
    }
    
    for (symbol in histogram) {
        entity = histogram[symbol];
        tmp_str = tmp_str.split(symbol).join(entity);
    }
    
    return tmp_str;
}

function html_entity_decode( string, quote_style ) {
    // http://kevin.vanzonneveld.net
    // +   original by: john (http://www.jd-tech.net)
    // +      input by: ger
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: marc andreu
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // -    depends on: get_html_translation_table
    // *     example 1: html_entity_decode('Kevin &amp; van Zonneveld');
    // *     returns 1: 'Kevin & van Zonneveld'
    // *     example 2: html_entity_decode('&amp;lt;');
    // *     returns 2: '&lt;'
 
    var histogram = {}, symbol = '', tmp_str = '', entity = '';
    tmp_str = string.toString();
    
    if (false === (histogram = get_html_translation_table('HTML_SPECIALCHARS', quote_style))) {
        return false;
    }
 
    // &amp; must be the last character when decoding!
    delete(histogram['&']);
    histogram['&'] = '&amp;';
 
    for (symbol in histogram) {
        entity = histogram[symbol];
        tmp_str = tmp_str.split(entity).join(symbol);
    }
    
    return tmp_str;
}

function display_nice_dialog_search(wider,higher,titler,contents,termer)
{
	$("<div>"+contents+"</div>").dialog({
		modal: true,
		width: wider,
		height: higher,
		title: ""+titler+"",
		buttons: {
			"Advanced Search": function() {
				window.location = 'search_site.php?search_term='+termer+'';
			},			
			Close: function() {
				$(this).dialog("close");
			}
			
		}
	});	
}
function display_nice_dialog_alert(wider,higher,titler,contents)
{
	$("<div>"+contents+"</div>").dialog({
		modal: true,
		width: wider,
		height: higher,
		title: ""+titler+"",
		buttons: {
			Ok: function() {
				$(this).dialog("close");
			}
		}
	});	
}
function display_nice_dialog_prompt(wider,higher,titler,contents)
{
	$("<div>"+contents+"</div>").dialog({
		modal: true,
		width: wider,
		height: higher,
		title: ""+titler+"",
		buttons: {
			Ok: function() {
				//alert('okay hit');
				$(this).dialog("close");
				return true;
			},
			Close: function() {
				$(this).dialog("close");
				return false;
			}
		}
	});	
}


function mrr_drop_switched_trailer_js(id,md)
{
	wider=600;
	higher=300
	titler="Trailer Switch Drop and Stop: Stop "+id+"";
	
	$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_switch_drop_trailer_on_stop",
		   data: {
		   		"stop_id":id
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		mrrtxt=$(xml).find('DispHTML').text();
		   		
               	$("<div id='temp_switch_box'>"+mrrtxt+"</div>").dialog({
               		modal: false,
               		width: wider,
               		height: higher,
               		title: ""+titler+"",
               		buttons: {
               			"Switch/Drop": function() 
               			{
               				mrrt1=$('#stop_and_drop_trailer1').val();
               				mrrt2=$('#stop_and_drop_trailer2').val();
               				mrrt3=$('#new_stop_and_drop_trailer').val();
               				               				
               				//txt="Are you sure you want to drop this trailer? <div>(Attempting to switch Stop "+id+". S Trailer="+mrrt1+", E Trailer="+mrrt2+", and New Trailer="+mrrt3+".)</div>";
               				//alert(txt);
               				
               				mrr_drop_switched_trailer_action_js(id,mrrt1,mrrt2,mrrt3);
               				
               				//$(this).dialog("close");
               				//return true;
               			},
               			"Close": function() 
               			{
               				if(md==1)	
               				{
               					load_stops();		//manage load page
               					load_dispatchs();	//manage load page
               				}
               				$('#stop_and_drop_trailer1').val(0);
               				$('#stop_and_drop_trailer2').val(0);
               				$('#new_stop_and_drop_trailer').val(0);
               				$('#stop_n_drop_trailer').val(0);               				
               				
               				$('#temp_switch_box').html("You have already switched/dropped this trailer...");   
               				$(this).dialog("close");
               				return false;
               			}
               		}
               	});			   			
		   }	
	});
}
function mrr_drop_switched_trailer_js_alt(id,md)
{	//load board version stripped down since no stop details form is present...
	wider=600;
	higher=300
	titler="Trailer Switch Drop and Stop: Stop "+id+"";
	
	$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_switch_drop_trailer_on_stop",
		   data: {
		   		"stop_id":id
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		mrrtxt=$(xml).find('DispHTML').text();
		   		
               	$("<div id='temp_switch_box'>"+mrrtxt+"</div>").dialog({
               		modal: false,
               		width: wider,
               		height: higher,
               		title: ""+titler+"",
               		buttons: {
               			"Switch/Drop": function() 
               			{
               				mrrt1=$('#stop_and_drop_trailer1').val();
               				mrrt2=$('#stop_and_drop_trailer2').val();
               				mrrt3=$('#new_stop_and_drop_trailer').val();
               				               				
               				//txt="Are you sure you want to drop this trailer? <div>(Attempting to switch Stop "+id+". S Trailer="+mrrt1+", E Trailer="+mrrt2+", and New Trailer="+mrrt3+".)</div>";
               				//alert(txt);
               				
               				mrr_drop_switched_trailer_action_js(id,mrrt1,mrrt2,mrrt3);
               				
               				//$(this).dialog("close");
               				//return true;
               			},
               			"Close": function() 
               			{               				
               				$('#stop_and_drop_trailer1').val(0);
               				$('#stop_and_drop_trailer2').val(0);
               				$('#new_stop_and_drop_trailer').val(0);
               				$('#stop_n_drop_trailer').val(0);               				
               				
               				$('#temp_switch_box').html("You have already switched/dropped this trailer...");   
               				$(this).dialog("close");
               				return false;
               			}
               		}
               	});			   			
		   }	
	});
}

function mrr_drop_switched_trailer_action_js(id,trailer1,trailer2,trailer3)
{
	//alert("MRR Attempting to switch Stop "+id+". S Trailer="+trailer1+", E Trailer="+trailer2+", and New Trailer="+trailer3+".");	
		
	$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_drop_switched_trailer_action",
		   data: {
		   		"stop_id":id,
		   		"start_trailer_id":trailer1,
		   		"end_trailer_id":trailer2,
		   		"new_trailer_id":trailer3
		   		},		   
		   dataType: "xml",
		   cache:false,
		   async:false,
		   success: function(xml) {
		   		restxt="No Switch or Drop Performed.";
		   		
		   		if(trailer3 == 0)	restxt="Trailer Drop Performed.";
		   		if(trailer3 > 0)	restxt="Trailer Switch Performed."; 	
		   				   		             				
               	$('#switcher_result').html("<span style='color:green;'><b>"+restxt+"</b></span>");   				   			
		   }	
	});		
}

function display_files_alt(section_id, xref_id) 
{
 	$.ajax({
	   	type: "POST",
	   	url: "ajax.php?cmd=display_attachments_alt",
	   	data: {"section_id":section_id,
	   			"xref_id":xref_id},
	   	dataType: "xml",
	   	cache:false,
		success: function(xml) {
			
	   		mrr_tab=$(xml).find('mrrTab').text();
	   		
	   		$('#attachment_holder').html(mrr_tab);
	   	}
	});
}
	
function mrr_rename_attachment(section_id, xref_id, id, old_name, cat_id, old_date)
{
	prompt_txt = "<table>";	
	prompt_txt += 		"<tr><td colspan='4'>Please enter a new name for file attachment '"+old_name+"'</td></tr>";
	prompt_txt += 		"<tr><td valign='top'>New Name:</td><td valign='top'><input name='mrr_new_file_name' id='mrr_new_file_name' value=\""+old_name+"\" size='50'></td>";
	
	if(section_id==1)
	{
		sel0=" selected";
		sel1="";
		sel2="";
		sel3="";
		sel4="";
		if(cat_id==1)
		{	//Personnel
			sel0="";
			sel1=" selected";
			sel2="";
			sel3="";
			sel4="";
		}
		if(cat_id==2)
		{	//Payroll
			sel0="";
			sel1="";
			sel2=" selected";
			sel3="";
			sel4="";
		}
		if(cat_id==3)
		{	//Insurance
			sel0="";
			sel1="";
			sel2="";
			sel3=" selected";
			sel4="";
		}
		if(cat_id==4)
		{	//Insurance
			sel0="";
			sel1="";
			sel2="";
			sel3="";
			sel4=" selected";
		}
		
		prompt_txt += 		"<td valign='top'>Category:<br>HOS Start:</td>";
		prompt_txt += 		"<td valign='top'>";
		prompt_txt += 			"<select name='mrr_file_cat_id' id='mrr_file_cat_id'>";
		prompt_txt += 				"<option value='0'"+sel0+">DOT File</option>";
		prompt_txt += 				"<option value='1'"+sel1+">Personnel</option>";
		prompt_txt += 				"<option value='2'"+sel2+">Payroll</option>";
		prompt_txt += 				"<option value='3'"+sel3+">Insurance</option>";
		prompt_txt += 				"<option value='4'"+sel4+">HOS</option>";
		prompt_txt += 			"</select>";
		prompt_txt += 			"<br><input name='mrr_new_file_date' id='mrr_new_file_date' value=\""+old_date+"\" size='10'>";
		prompt_txt += 		"</td>";
	}
	else
	{
		prompt_txt += 		"<td colspan='2'><input type='hidden' name='mrr_file_cat_id' id='mrr_file_cat_id' value=\""+cat_id+"\"><input type='hidden' name='mrr_new_file_date' id='mrr_new_file_date' value=\""+old_date+"\"></td>";		
	}
	prompt_txt += 		"</tr>";
	
	
	prompt_txt += "</table>";
	$.prompt(prompt_txt,{
		buttons: {"Okay":1, "Cancel":0},
		callback: function(v,m,f) {
			if(v != undefined && v) {
				if(f.mrr_new_file_name == '') {
					$.prompt("Invalid: Please give this attachment a name");
					return false;
				}
				 $.ajax({
				   type: "POST",
				   url: "ajax.php?cmd=update_file_attachment_name",
				   data: {
				   		"id":id,
				   		"new_name":f.mrr_new_file_name,
				   		"new_date":f.mrr_new_file_date,
				   		"new_cat_id":f.mrr_file_cat_id
				   		},
				   dataType: "xml",
				   cache:false,
				   success: function(xml) {
				   		$.noticeAdd({text: "Success - File updated."});	
						display_files_alt(section_id, xref_id);
				   }
				 });
			}
		},
		loaded: function() {
			$('#mrr_new_file_name').focus();
			$('#mrr_new_file_date').datepicker();
		}
	});	
}

var dispatch_im_proc;

function display_dispatch_im_msgs() 
{
 	$.ajax({
	   	type: "POST",
	   	url: "ajax.php?cmd=display_dispatch_im_msgs",
	   	data: {
	   		
	   		},
	   	dataType: "xml",
	   	cache:false,
		success: function(xml) {
			
	   		mrr_tab=$(xml).find('DispMsg').text();	   		
	   		$('#dispatch_im_holder').html(mrr_tab);
	   		
	   		dispatch_im_proc=setTimeout("display_dispatch_im_msgs();", (60 * 1000 * 1 / 4));		//1000=1 second * 60 = 1 minute * 1 / 2 ...every 30 seconds
	   		
	   		if(parseInt( $(xml).find('DispSound').text() ) > 0)	
	   		{
	   			$("#im_sound_affect").get(0).play();
	   			$.prompt("There is a new Dispatch IM Message...");
	   		}
	   	}
	});
}
function mrr_dispatch_im_msg_send()
{
	touserid=$('#mrr_dispatch_im_msg_to').val();
	im_msg=$('#mrr_dispatch_im_msg_box').val();
		
	$.ajax({
		url: "ajax.php?cmd=add_dispatch_im_msg",
		type: "post",
		dataType: "xml",
		data: {
			"user_id":touserid,
			"im_msg": im_msg
		},
		error: function() {
			$.prompt("Error adding Dispatch IM  Message - please try again later");
		},
		success: function(xml) {
			$('#mrr_dispatch_im_msg_box').val('');
			//$('#mrr_dispatch_im_msg_to').val('0');
			
			clearTimeout(dispatch_im_proc);
			
			display_dispatch_im_msgs();
			$.noticeAdd({text: "Success - Dispatch IM  Message posted."});							
		}
	});	
}
function mrr_dispatch_im_msg_kill(id)
{		
	$.ajax({
		url: "ajax.php?cmd=kill_dispatch_im_msg",
		type: "post",
		dataType: "xml",
		data: {
			"msg_id":id
		},
		error: function() {
			$.prompt("Error removing Dispatch IM Message - please try again later");
		},
		success: function(xml) {
			clearTimeout(dispatch_im_proc);
			
			display_dispatch_im_msgs();
			$.noticeAdd({text: "Success - Dispatch IM  Message removed."});							
		}
	});	
}
function mrr_dispatch_im_msg_kill2(id)
{	//admin report mode...no message area
	$.ajax({
		url: "ajax.php?cmd=kill_dispatch_im_msg",
		type: "post",
		dataType: "xml",
		data: {
			"msg_id":id
		},
		error: function() {
			$.prompt("Error removing Dispatch IM Message from report - please try again later");
		},
		success: function(xml) {
			
			document.getElementById("report_form").submit();						
		}
	});	
}


function mrr_mini_menu_item_kill2(id)
{	//mini-menu items
	$.ajax({
		url: "ajax.php?cmd=kill_mini_menu_item",
		type: "post",
		dataType: "xml",
		data: {
			"item_id":id
		},
		error: function() {
			$.prompt("Error removing Item from your Mini-Menu - please try again later");
		},
		success: function(xml) {
			
			document.getElementById("mini_menu_form").submit();						
		}
	});	
}