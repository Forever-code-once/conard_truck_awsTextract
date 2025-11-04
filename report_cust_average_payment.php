<?
ini_set("max_input_vars","10000");
?>
<? include('header.php') ?>
<?

	$use_title = "Report - Customer Average Payment";
	$usetitle = "Report - Customer Average Payment";
		
	//if(isset($_GET['date_from']))	
	//{
		//$_GET['date_from']=str_replace("_","/",$_GET['date_from']);	
		//$_POST['date_from']=$_GET['date_from'];
	//}
	//if(!isset($_POST['date_from'])) 		$_POST['date_from'] = date("m/d/Y");	//"-1 month", 
	//if(!isset($_POST['mrr_aging_from'])) 	$_POST['mrr_aging_from'] = 0;
	//if(!isset($_POST['mrr_aging_to'])) 	$_POST['mrr_aging_to'] = 99999;
	$sql2 = "     				
     	update customers set
     		
     		slow_pays='0'
     	";	//credit_hold='0',
     //simple_query($sql2);
?>	
	<table border='0'>
	<tr>
		<td valign='top'>
          	<table border='0' class='admin_menu1' width='1600'>
          	<tr>
          		<td valign='top' colspan='7'><center><b>Customer Average Payment with Remaining Invoice Balance</b></center></td>
          	</tr>
          	<!--
          	<tr>
          		<td valign='top' align='left' width='150'><b>Date</b></td>
          		<td valign='top' align='left' width='150'><input type='text' name='date_from' id='date_from' value='<?= $_POST['date_from'] ?>' class='input_medium' onChange='mrr_load_up_ar_details();'></td>
          		<td valign='top' align='left' width='150'><b>Aging (days)</b></td>
          		<td valign='top' align='left' width='150'><input type='text' name='mrr_aging_from' id='mrr_aging_from' value='<?= $_POST['mrr_aging_from'] ?>' class='input_medium' onChange='mrr_load_up_ar_details();'></td>
          		<td valign='top' align='left' width='150'>&nbsp; to &nbsp;</td>
          		<td valign='top' align='left' width='150'><input type='text' name='mrr_aging_to' id='mrr_aging_to' value='<?= $_POST['mrr_aging_to'] ?>' class='input_medium' onChange='mrr_load_up_ar_details();'></td>
          		<td valign='top' align='right'><span class='mrr_link_like_on' onClick='mrr_toggle_inv_display();'>Toggle Invoice List</span></td>
          	</tr>
          	-->
          	</table>
          	<br>          	
          	<table border='0' class='admin_menu2' width='1600'>
          	<tr>		
          		<td valign='top'><div id='mrr_report_view'></div></td>
          	</tr>
          	</table>
     	</td>
     </tr>
     </tr>
	</table>
<script type='text/javascript'>
	//$('#date_from').datepicker();
	//$('#date_to').datepicker();
	
	var mrr_now = '<?= date("m/d/Y"); ?>' ;
	
	$().ready(function() 
	{	
		mrr_load_up_cust_avg_payments();
		$('.customer_row').hide();		
	});
	
	function mrr_load_up_cust_avg_payments()
	{
		$('#mrr_report_view').html('Loading...Takes about 1 minute...');				//mrr_get_ar_detail_info_find
		
		$.ajax({
			url: "ajax.php?cmd=mrr_cust_average_pay_report",
			type: "post",
			dataType: "xml",
			data: {
				
			},
			error: function() {
				$.prompt("Error: Average Payment info could be found.");
				$('#mrr_report_view').html('Done. No information found.');
			},
			success: function(xml) {
				if($(xml).find('mrrTab').text() == '')
				{
					$('#mrr_report_view').html('Done. No information found.');
				}
				else
				{
					$('#mrr_report_view').html($(xml).find('mrrTab').text());	
					$('.tablesorter').tablesorter();		
				}
			}
		});
		
	}
	function mrr_api_aging_hunter(id,grp,fieldname)
	{		
		if(id > 0 && grp > 0) 
		{
			$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_get_ar_summary_info_find",
			   data: {
			   		"cust_id": id,
			   		"cust_name":'',
			   		"date_from":mrr_now,
			   		"date_to":mrr_now
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		if($(xml).find('mrrTab').text())
			   		{			   				
			   			tmp=$(xml).find('mrrTab').text();
			   			tmp2=$(xml).find('paymentNotes').text(); 
			   			
			   			tmpA=$(xml).find('mrrA').text();
			   			tmpB=$(xml).find('mrrB').text();
			   			tmpC=$(xml).find('mrrC').text();
			   			tmpD=$(xml).find('mrrD').text();
			   			
			   			txt="<div class='mrr_link_like_on' onClick='mrr_api_aging_hunter("+id+",0,\""+fieldname+"\");'>Close</div>";	//close section "link"
			   			
			   			$('.'+fieldname+'_row').show();
			   			$('#'+fieldname+'').show();
			   			if(grp==15)	$('#'+fieldname+'').html(tmpA+tmp2+txt);
			   			if(grp==30)	$('#'+fieldname+'').html(tmpB+tmp2+txt);
			   			if(grp==45)	$('#'+fieldname+'').html(tmpC+tmp2+txt);
			   			if(grp==46)	$('#'+fieldname+'').html(tmpD+tmp2+txt);
			   			
			   			$('.skip_if_inserted_elsewhere').attr('style','');
			   		}	
			   }	
			 });
		}
		else
		{
			$('#'+fieldname+'').html('');
			$('#'+fieldname+'').hide();	
			$('.'+fieldname+'_row').hide();
		}		
	}
</script>
<? include('footer.php') ?>