<? include('application.php') ?>
<? include('header.php') ?>
<?		
	ob_start();
			
	echo "<div style='display:none;'>".mrr_get_authenticate()."]]</div>";
	
	$mrr_login=ob_get_contents();
	ob_flush();
	ob_clean();
	
	$color_code="cc0000";
	$res_str="";
	if(substr_count($mrr_login,"SessionID=") > 0 && substr_count($mrr_login,"Database=") > 0)
	{
		$pos1=strpos($mrr_login,"SessionID=");
		$pos2=strpos($mrr_login,"Database=",$pos1);
		
		$res_str=trim(substr($mrr_login,$pos1,($pos2 - $pos1)));	
		
		$color_code="00cc00";
	}
	
	
	$mrr_login=str_replace("<div style='display:none;'>","",$mrr_login);
	$mrr_login=str_replace("]]</div>","",$mrr_login);
	
	
	$rep_val="<div style='border:1px solid #0000CC; margin:5px; padding:5px; width:1200;'>".$mrr_login."</div><br>Log-In: <span style='color:#".$color_code.";'><b>".$res_str."</b></span><br>";
?>
<table width='1200' height='200' border='0' bgcolor='#9999CC'>
<tr>
	<td align='center' valign='middle'>
		<p>
		Use this page to re-authenticate the GeoTab login.<br>
		If a Session ID is displayed, Login was successful and "should" be good for 30 days... until it drops again.<br>
		If not, the error message may be helpful to figure out why it failed.	
		</p>
          <table class='admin_menu1' style='text-align:left;margin:5px'>
          <tr>
          	<td colspan='2'>		
          		<center><a name='mrr_spec_ops'><b>GeoTab Authentication</b></a><br><?=$rep_val ?><br></center>
          	</td>
          </tr>
          </table>
	</td>
</tr>
</table>
<? include('footer.php') ?>