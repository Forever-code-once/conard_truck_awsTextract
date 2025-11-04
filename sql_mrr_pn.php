<? include('application.php')?>
<?		
	//Messages in next packet use the same basic processing... 
	$max_msg_packet=0;
	$npacket_label="";
	$sql="
		select next_msg_packet_id 
		from ".mrr_find_log_database_name()."truck_tracking_packets
		where next_msg_packet_id > 0
		order by id desc, next_msg_packet_id desc 
	";
	$data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$max_msg_packet=$row['next_msg_packet_id'];
	}
	
	if(isset($_GET['next_id']))		$max_msg_packet=(int)   $_GET['next_id'];	//USE THE URL to select a package to load...requires no code change.
	
	//$max_msg_packet=140607;											//CHANGE THIS MAX MSG PACKET id to the one you want to try next...
	
	//$max_msg_packet=1;
	echo "<br>Max Message Packet=".$max_msg_packet."<br>URL: <a href='sql_mrr_pn.php?next_id=".$max_msg_packet."'>sql_mrr_pn.php?next_id=".$max_msg_packet."</a><br>";	
	
	$save_blocker=0;
	
	$serve_output3="";
	$npacket_label="";
	
	$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_message_history",0,0,"",0,$max_msg_packet,0,0,$save_blocker);
	
	if($max_msg_packet > 0)
	{
     	$run_again=0;
     	$next_packet_run=$max_msg_packet + 1;	
     	
     	if($sres['more_data'] > 0)
     	{
     		$run_again=1;		
     		$npacket_label="<br> Next Message Packet from XML is ".$next_packet_run.".<br>URL: <a href='sql_mrr_pn.php?next_id=".$next_packet_run."'>sql_mrr_pn.php?next_id=".$next_packet_run."</a><br><br><br>";
     	}
     	while($run_again>0)
     	{	//turned off. PN limits requests to one per minute, so no point in doing this...will catch the rest next time.
     		$sres=mrr_peoplenet_find_data_for_cron_job("oi_pnet_message_history",0,0,"",0, $next_packet_run,0,0,$save_blocker);	
     		if($sres['more_data'] > 0)
     		{
     			$run_again=1;
     			$next_packet_run++;
     			$npacket_label="<br> Next Message Packet from XML is ".$next_packet_run.".<br>URL: <a href='sql_mrr_pn.php?next_id=".$next_packet_run."'>sql_mrr_pn.php?next_id=".$next_packet_run."</a><br><br><br>";			
     		}
     		else
     		{
     			$run_again=0;	
     		}		
     	}
     	$serve_output3=$sres['output']."".$npacket_label."<br>XML:<br><pre>".$sres['xml']."</pre><br>Done.<br>";
	}	
	echo $serve_output3."".$npacket_label."";	
?>