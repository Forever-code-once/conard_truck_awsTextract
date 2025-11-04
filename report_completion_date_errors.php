<? include('application.php') ?>
<? $admin_page = 1 ?>
<? include('header.php') ?>

<!---
<a href='download_fuel_price.php' target='_blank'>[Click here to Update Fuel Surcharge]</a>
--->
<form action="<?=$SCRIPT_NAME?>" method="post">
<table class='admin_menu1' style='text-align:left;margin:5px' width='1400'>
<tr>
	<td>
		<?  
			$res=mrr_warning_of_improper_date_time_for_loads();
			echo "<b>".$res['num']." Completion Date Error(s) Found!</b><br>";	
			echo $res['html'];	
		 ?>
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>
	//$('#date_from').datepicker();
	//$('#date_to').datepicker();
	
</script>

<? include('footer.php') ?>
