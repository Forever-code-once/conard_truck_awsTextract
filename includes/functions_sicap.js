	function sicap_view_invoice(invoice_number) {
		window.open("accounting/invoice.php?invoice_number="+invoice_number);
	}
	
	function sicap_create_invoice(load_id, add_to_invoice_id) {
		
		if(add_to_invoice_id == undefined) add_to_invoice_id = 0;
		
		$('#sicap_invoice_holder_'+load_id).html("<img src='images/loader.gif'>");
		$.ajax({
			url:"ajax.php?cmd=ajax_sicap_create_invoice",
			type: "post",
			dataType: "xml",
			data: {
				load_id: load_id,
				invoice_id: add_to_invoice_id
				},
			error: function() {
				$('#sicap_invoice_holder_'+load_id).html('');
				$.prompt("General error creating invoice");
			},
			success: function(xml) {
				$('#sicap_invoice_holder_'+load_id).html('');
				if($(xml).find('rslt').text() == '0') {
					$.prompt("Error creating invoice: " + $(xml).find('rsltmsg').text());
				} else {
					$.noticeAdd({text: "Success - Invoice created for load ID: " + load_id});
					$('#sicap_create_holder_'+load_id).hide();
					invoice_number = $(xml).find('SICAPInvoiceNumber').text();
					invoice_date = $(xml).find('InvoiceDate').text();
					$('#sicap_invoice_number_holder_'+load_id).html(invoice_number);
					$('#invoice_number_'+load_id).val(invoice_number);
					$('#invoice_date_'+load_id).val(invoice_date);
					$('.create_update_invoice_'+load_id).hide();
					$('.delete_invoice_link_'+load_id).show();
				}
			}
		});
	}
	
	function sicap_add_to_invoice(load_id) {
		$.prompt("<h3>Add to an existing Invoice</h3>Enter the Invoice Number you would like to add this invoice to:<br><br> Invoice #: <input name='add_to_invoice' id='add_to_invoice'>", {
			buttons: {Save: true, Cancel: false},
			loaded: function() {
				$('#add_to_invoice').focus();
			},
			submit: function(v, m,f) {
				if(v) {
					if($('#add_to_invoice').val() == '') {
						$.prompt("Please enter the invoice number");
						return false;
					}
					
					sicap_create_invoice(load_id, $('#add_to_invoice').val());
				}
			}
		});
	}
	
	function sicap_delete_invoice(load_id) {
		$.prompt("Are you sure you want to delete the invoice from the accounting system?", {
			buttons: {Yes: true, No: false},
			submit: function(v, m, f) {
				if(v) {
					$.ajax({
						url:"ajax.php?cmd=ajax_sicap_delete_invoice",
						type: "post",
						dataType: "xml",
						data: {
							load_id: load_id
						},
						error: function() {
							$('#sicap_invoice_holder_'+load_id).html('');
							$.prompt("General error deleteing invoice");
						},
						success: function(xml) {
							
							if($(xml).find('rsltmsg').text() != '') {
								$.prompt($(xml).find('rsltmsg').text());
							}
							
							$('#sicap_invoice_holder_'+load_id).html('');
							$.noticeAdd({text: "Success - Invoice has been deleted for load ID: " + load_id});
							$('.create_update_invoice_'+load_id).show();
							$('.delete_invoice_link_'+load_id).hide();
							$('.invoice_number_holder_load_'+load_id).val('');
							$('.invoice_date_holder_load_'+load_id).val('');
							$('#sicap_invoice_number_holder_'+load_id).html('');
							$('#invoice_number_'+load_id).val('');
						}
					});
				}
			}
		});
	}