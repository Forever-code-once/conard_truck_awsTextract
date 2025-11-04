<? include('application.php') ?>
<?php

$sql = "
	select *
	
	from log_email_alerts
	where id = '".sql_friendly($_GET['mid'])."'
";
$data = simple_query($sql);
$row = mysqli_fetch_array($data);

header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
	<? if(isset($_GET['response'])) { ?>
		<?
		if($_GET['Digits'] == '1') {
			echo "<Say>Thank you for handling this alert. Goodbye.</Say>";
		} else {
			echo "<Say>Okay, you cannot handle this. We will contact the next on call person. Goodbye.</Say>";
		}

		$sql = "
			update log_email_alerts_entries
			set linedate_response = now(),
				handle_flag = '".sql_friendly($_GET['Digits'])."'
				
			where id = '".sql_friendly($_GET['log_id'])."'
		";
		simple_query($sql);
		
		?>
	<? } else { ?>
	
		<?
		if(!isset($_GET['log_id'])) {
			$sql = "
				insert into log_email_alerts_entries
					(log_email_alert_id,
					linedate_added,
					user_id,
					method_id,
					phone)
					
				values ('".sql_friendly($_GET['mid'])."',
					now(),
					0,
					1,
					'".sql_friendly($_GET['phone_to'])."')
			";
			simple_query($sql);
			$log_id = mysqli_insert_id($datasource);
		} else {
			$log_id = $_GET['log_id'];
		}
		?>
	
	    <Gather  action="alert_call_handler.php?mid=<?=$_GET['mid']?>&amp;response=1&amp;log_id=<?=$log_id?>" method="GET">
	    	  <Say>Hello, this is the Conard Alert notification system. The alert subject is: <?=$row['subject']?>. Press 1 and the pound key to indicate you will handle this, press 2 and the pound key if you cannot handle this.</Say>
	    </Gather>
	    <Say>Sorry, I didn't get your response.</Say>
	    <Redirect>alert_call_handler.php?log_id=<?=$log_id?>&amp;mid=<?=$_GET['mid']?></Redirect>
	<? } ?>
</Response>
