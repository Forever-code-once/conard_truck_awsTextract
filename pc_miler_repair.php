<? include('application.php') ?>

<? include('header.php') ?>
<?
	$rep_val=mrr_super_repair_pc_miler_app();
?>
<table width='1200' height='200' border='0' bgcolor='#9999CC'>
<tr>
	<td align='center' valign='middle'>

          <table class='admin_menu1' style='text-align:left;margin:5px'>
          <tr>
          	<td colspan='2'>		
          		<center><a name='mrr_spec_ops'><b>PC MILER REPAIR</b></a><br><?=$rep_val ?><br></center>
          	</td>
          </tr>
          </table>
	</td>
</tr>
</table>

<? include('footer.php') ?>