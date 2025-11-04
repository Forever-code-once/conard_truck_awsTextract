<? include('application.php') ?>
<? $usetitle="PN Msg Packet Repair"; ?>
<? include('header.php') ?>
<?
	$packet_id=0;
	$stamp="";
	$max_msg_packet=0;
	$sql="
		select packet_id,linedate_added,next_msg_packet_id 
		from ".mrr_find_log_database_name()."truck_tracking_packets
		where next_msg_packet_id > 0
		order by id desc, next_msg_packet_id desc 
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$max_msg_packet=(int) $row['next_msg_packet_id'];
		$packet_id=(int) $row['packet_id'];
		$stamp=date("m/d/Y H:i:s",strtotime($row['linedate_added']));
	}
	
	$rep_val="Last Message Packet ID was <b>".$packet_id."</b> as of <b>".$stamp."</b>. Next Message Packet ID should be <span style='color:purple;'><b>".$max_msg_packet."</b></span>.";
	$rep_results="";
	
	if(!isset($_POST['next_packet_id']))		$_POST['next_packet_id']=$max_msg_packet;	
		
	$_POST['next_packet_id']=(int) $_POST['next_packet_id'];
	
	if($_POST['next_packet_id'] > 0 && isset($_POST['process_packet']))
	{
		$rep_results="<i>Attempting to process Message Packet ".$_POST['next_packet_id']."...</i><br>";
		
		$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_message_history",0,0,"",0,$_POST['next_packet_id'],0,0);
		if($sres['more_data'] > 0)
     	{	
     		$rep_results.="<br>More Data Found in Packet ID ".$sres['packet_id'].".  <b>Process Next Message Packet ID ".$sres['next_packet_id']." for more messages.</b><br>";
     	}
     	$rep_results.="<br>Found ".$sres['packet_cntr']." Items<br>";
     	$rep_results.=$sres['output'];
     	//$sres['packet_response_id']=$packet_response;
		//$sres['packet_response_id_tag']=$packet_response_tag;
		//$sres['xml']=$xml_page;
	}
?>
<table width='1200' border='0' bgcolor='#9999CC'>
<tr style='min-height:200px;'>
	<td align='center' valign='middle'>
     	<form name='mrr_form' action='peoplenet_msg_repair.php' method='post'>
               <table class='admin_menu1' style='text-align:left;margin:5px;'>
               <tr>
               	<td colspan='2'>		
               		<center>
               			<b>PeopleNet Message Packet Repair</b>
               			<br><?=$rep_val ?><br>
               		</center>
               		
               		<div style='color:#444444;margin:5px;padding:5px; border:1px solid #cc0000;'>
               			This utility will run only the Message Packet import from PeopleNet (PN) and is meant to "unclog" the pipes if the Messages stop coming in from PN.
               			<br><br>The "clog" is usually caused by the next packet ID (which is usually the next increment after the last successful packet processed) somehow being skipped on the PN side for some reason.
               			This causes the system to keep looking for the next packet until it finds it, but since it got skipped by PN, it never finds it.  
               			The response is usually just the number that there is more data or a packet number...or that there is an error on PN side retrieving anything.  
               			<br><br>Use this form to skip to another until it shows you the messages you are expecting to see.  
               			The Packet ID should always be higher than the Last Message Packet ID processed and usually is not too far above the Next Message Packet ID...and always a number.
               		</div>
               	</td>
               </tr>
               <tr>
               	<td valign='top' align='right' width='50%'>Enter Packet ID to Process:</td>
               	<td valign='top' align='left' width='50%'><input type='text' name='next_packet_id' id='next_packet_id' value='<?= (int) $_POST['next_packet_id'] ?>' style='width:100px; text-align:right;'></td>
               </tr>
               <tr>
               	<td valign='top' align='right'>Processing may take a few seconds...</td>
               	<td valign='top' align='left'><input type='submit' name='process_packet' id='process_packet' value='Process Packet'></td>
               </tr>
               <tr>
               	<td colspan='2'>		
               		<center>
               			<b>PeopleNet Message Packet Results</b>
               			<br><?=$rep_results ?><br>
               		</center>
               	</td>
               </tr>
               </table>
          </form>
	</td>
</tr>
</table>

<? include('footer.php') ?>