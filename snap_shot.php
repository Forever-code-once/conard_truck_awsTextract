<? include('header.php') ?>
<div style='text-align:left;margin:10px'>
	<div class='section_heading'>Company Snapshot</div>
</div>
<?
	
?>
<table class='admin_menu2' border='0' width='1600'>
	<tr>
		<td valign='top' width='50%'><div class='box1' style='max-height:400px; overflow:auto; margin:5px;' id='bad_customers'></div></td>
		<td valign='top' width='50%'><div class='box2' style='max-height:400px; overflow:auto; margin:5px;' id='sales'></div></td>
	</tr>
	<tr>
		<td valign='top'><div class='box2' style='max-height:400px; overflow:auto; margin:5px;' id='miles_run'></div></td>
		<td valign='top'><div class='box1' style='max-height:400px; overflow:auto; margin:5px;' id='website_links'></div></td>
	</tr>
</table>

<script type='text/javascript'>
	//$('.input_date').datepicker();	
	$().ready(function() 
	{	
			$('#bad_customers').html('Loading...');	
			$('#sales').html('Loading...');	
			$('#miles_run').html('Loading...');	
			$('#website_links').html('Loading...');	
			
			mrr_get_snap_shot('A/R Payment Report','bad_customers');
			mrr_get_snap_shot('Sales','sales');
			mrr_get_snap_shot('Miles Run','miles_run');
			mrr_get_snap_shot('Different Website Links','website_links');		
	});
	
	function mrr_get_snap_shot(sect_label,mode_code)
	{		
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_snap_shot",
		   data: {
		   		"label":sect_label,
		   		"mode":mode_code
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		newtab=$(xml).find('mrrTab').text();
				if(newtab !="")
				{
					$('#'+mode_code+'').html(newtab);	
					$('.tablesorter').tablesorter();		
				} 
		   }
		});	
	}
</script>
<? include('footer.php') ?>