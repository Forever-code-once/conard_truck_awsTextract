<? include('application.php') ?>
<? 
	$admin_page = 1; 
	$new_design=3;
?>
<? include('header.php') ?>
<div style='text-align:left;margin:10px'>
	<div class='section_heading'>Search Site </div>
	Enter any information to search various site components.  More can be added later.
</div>

<?
	//$rfilter = new report_filter();
	//$rfilter->show_date_range 		= false;
	//$rfilter->show_single_date 		= true;
	//$rfilter->maintenance_desc		= true;
	//$rfilter->search_notes_files		= true;
	//$rfilter->search_sort_by			= true;
	//$rfilter->show_filter();
	
	if(isset($_GET['search_term']))	
	{
		$_POST['search_term']=trim($_GET['search_term']);
		$_POST['build_report']=1;
	}
	if(!isset($_POST['search_term']))		$_POST['search_term']="";
	if(!isset($_POST['search_term2']))		$_POST['search_term2']="";
	if(!isset($_POST['search_term3']))		$_POST['search_term3']="";
	if(!isset($_POST['search_term4']))		$_POST['search_term4']="";
	if(!isset($_POST['search_term5']))		$_POST['search_term5']="";
	if(!isset($_POST['search_term6']))		$_POST['search_term6']="";
	if(!isset($_POST['search_term7']))		$_POST['search_term7']="";
	if(!isset($_POST['search_term8']))		$_POST['search_term8']="";
	
	if(!isset($_POST['build_report']))		$_POST['build_report']=0;
?>

<form action='' method='post'>
<input type='hidden' name='build_report' value='1'>
<table class='admin_menu2' border='0'>
	<tr>
		<td valign='top'>
		     <table class='admin_menu1' style='width:500px'>
               <tr>
               	<td valign='top'><b>Name/Company</b></td>
               	<td valign='top' align='right'><input type='text' name='search_term' id='search_term' value='<?= $_POST['search_term'] ?>' class='input_normal'></td>
               </tr>
               <tr>
               	<td valign='top'><b>Address 1</b></td>
               	<td valign='top' align='right'><input type='text' name='search_term2' id='search_term2' value='<?= $_POST['search_term2'] ?>' class='input_normal'></td>
               </tr>
               <tr>
               	<td valign='top'><b>Address 2</b></td>
               	<td valign='top' align='right'><input type='text' name='search_term3' id='search_term3' value='<?= $_POST['search_term3'] ?>' class='input_normal'></td>
               </tr>
               <tr>
               	<td valign='top'><b>City</b></td>
               	<td valign='top' align='right'><input type='text' name='search_term4' id='search_term4' value='<?= $_POST['search_term4'] ?>' class='input_normal'></td>
               </tr>
               <tr>
               	<td valign='top'><b>State</b></td>
               	<td valign='top' align='right'><input type='text' name='search_term5' id='search_term5' value='<?= $_POST['search_term5'] ?>' class='input_normal'></td>
               </tr>
               <tr>
               	<td valign='top'><b>Zip</b></td>
               	<td valign='top' align='right'><input type='text' name='search_term6' id='search_term6' value='<?= $_POST['search_term6'] ?>' class='input_normal'></td>
               </tr>
               <tr>
               	<td valign='top'><b>TBD</b></td>
               	<td valign='top' align='right'><input type='text' name='search_term7' id='search_term7' value='<?= $_POST['search_term7'] ?>' class='input_normal'></td>
               </tr>
               <tr>
               	<td valign='top'><b>TBD</b></td>
               	<td valign='top' align='right'><input type='text' name='search_term8' id='search_term8' value='<?= $_POST['search_term8'] ?>' class='input_normal'></td>
               </tr>
               
               <tr>
               	<td valign='top' colspan='2'>
               		<center>
               			<input type='button' name='search_site_button' id='search_site_button' value='Search Site' onClick='mrr_run_full_site_search();'>
               		</center>
               	</td>
               </tr>               
               </table>
		</td>
		<td valign='top' width='1000'>
			<div id='search_results'></div>
		</td>
	</tr>
</table>

</form>
<script type='text/javascript'>
	//$('.input_date').datepicker();	
	$('.tablesorter').tablesorter();
	$().ready(function() 
	{	
		<?
		if($_POST['build_report'] > 0)
		{
		?>
			mrr_run_full_site_search();
		<?
		}
		?>				
	});
	
	function mrr_run_full_site_search()
	{		
		$('#search_results').html('Searching...');
		st1=$('#search_term').val();
		st2=$('#search_term2').val();
		st3=$('#search_term3').val();
		st4=$('#search_term4').val();
		st5=$('#search_term5').val();
		st6=$('#search_term6').val();
		st7=$('#search_term7').val();
		st8=$('#search_term8').val();
		
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_full_search",
		   data: {
		   		"search_term":st1,
		   		"search_term2":st2,
		   		"search_term3":st3,
		   		"search_term4":st4,
		   		"search_term5":st5,
		   		"search_term6":st6,
		   		"search_term7":st7,
		   		"search_term8":st8
		   		},
		   dataType: "xml",
		   cache:false,
		   success: function(xml) {
		   		newtab=$(xml).find('mrrTab').text();
				if(newtab !="")
				{
					$('#search_results').html(newtab);	
					$('.tablesorter').tablesorter();		
				} 
		   }
		});	
	}
</script>
<? include('footer.php') ?>