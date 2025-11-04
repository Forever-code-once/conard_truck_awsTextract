$(function(){

	init_upload();
});

function init_upload() {
	$('.upload').each(function() {
		
		    var ul = $(this).find('ul');
		    var upload_obj = $(this);
		    //console.log(ul);
		    
			$(this).find('.drop a, button').unbind('click');
			
		    $(this).find('.drop a, button').click(function(){
		        // Simulate a click on the file input button
		        // to show the file browser dialog
		        $(this).parent().find('input').click();
		    });
		    
		    //$(this).fileupload('destroy');
		
		    // Initialize the jQuery File Upload plugin
		    $(this).fileupload({
		
		        // This element will accept file drag/drop uploading
		        dropZone: $(this).find('.drop'),

		
		        // This function is called when a file is added to the queue;
		        // either via the browse button, or via drag/drop:
		        add: function (e, data) {
		
		            var tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"'+
		                ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p></p><span></span></li>');
		
		            // Append the file name and file size
		            tpl.find('p').text(data.files[0].name).append('<i>' + formatFileSize(data.files[0].size) + '</i>');
		
		            // Add the HTML to the UL element
		            data.context = tpl.appendTo(ul);
		
		            // Initialize the knob plugin
		            tpl.find('input').knob();
		
		            // Listen for clicks on the cancel icon
		            tpl.find('span').click(function(){
		
		                if(tpl.hasClass('working')){
		                    jqXHR.abort();
		                }
		
		                tpl.fadeOut(function(){
		                    tpl.remove();
		                });
		
		            });
		
		            // Automatically upload the file once it is added to the queue
		            var jqXHR = data.submit();
		        },
		
		        progress: function(e, data){
		
		            // Calculate the completion percentage of the upload
		            var progress = parseInt(data.loaded / data.total * 100, 10);
		
		            // Update the hidden input field and trigger a change
		            // so that the jQuery knob plugin knows to update the dial
		            data.context.find('input').val(progress).change();
		
		            if(progress == 100){
		                data.context.removeClass('working');
		                data.context.hide();
			            console.log(e);
			            console.log(data);
			            //console.log(data.textStatus + " | " + data.total);
			            //$(this).find('li').hide();
			            //alert(data.result.status);
		            }
		        },
				success: function(e, data) {
					
					json_e = JSON.parse(e);
					
					if(json_e.status_code == 0) {
						msgbox('Error: ' + json_e.msg, 'Upload Error');
						
					} else {
						console.log("extra_params (callback_function): " + json_e.extra_params.callback_function);
						if(json_e.extra_params.callback_function != undefined) eval(json_e.extra_params.callback_function);
												
						//if(json_e.extra_params.show_success_notice) show_notice('Success, your file was uploaded!');
						
						//if(json_e.extra_params.show_success_notice) msgbox('Success, your file was uploaded!', 'File uploaded');
					}
				},
				error: function(e, data) {
					console.log(data);
				},
		        fail:function(e, data){
		            // Something has gone wrong!
		            console.log(e);
		            console.log(data);
		            data.context.addClass('error');
		        }
		
		    });
		
		
		    // Prevent the default action when a file is dropped on the window
		    $(document).on('drop dragover', function (e) {
		        e.preventDefault();
		    });
		
		    // Helper function that formats the file sizes
		    function formatFileSize(bytes) {
		        if (typeof bytes !== 'number') {
		            return '';
		        }
		
		        if (bytes >= 1000000000) {
		            return (bytes / 1000000000).toFixed(2) + ' GB';
		        }
		
		        if (bytes >= 1000000) {
		            return (bytes / 1000000).toFixed(2) + ' MB';
		        }
		
		        return (bytes / 1000).toFixed(2) + ' KB';
		    }
		  });

}