<?
$usetitle = "Report - Dispatch IM History";
$use_title = "Report - Dispatch IM History";
?>
<? include('header.php') ?>
<?
	$rfilter = new report_filter();
	$rfilter->show_users 			= true;
	$rfilter->show_users2 			= true;
	$rfilter->show_font_size			= true;
	$rfilter->show_filter();
		
		
 	if(isset($_POST['build_report'])) 
 	{ 	
		$search_date_range = '';
		
		// we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
		$search_date_range = "
			and dispatch_im.linedate_added >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
			and dispatch_im.linedate_added <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
		";
		
		
		$sql = "
			select dispatch_im.*,
				(select users.username from users where users.id=dispatch_im.user_id) as from_username,
				(select users.username from users where users.id=dispatch_im.to_user_id) as to_username			
			from dispatch_im
			where dispatch_im.deleted >='0'
				".$search_date_range."
				
				".($_POST['report_user_id']  ? " and (dispatch_im.user_id = '".sql_friendly($_POST['report_user_id']) ."' or dispatch_im.to_user_id = '".sql_friendly($_POST['report_user_id']) ."')" : '') ."
				".($_POST['report_user_id2'] ? " and (dispatch_im.user_id = '".sql_friendly($_POST['report_user_id2'])."' or dispatch_im.to_user_id = '".sql_friendly($_POST['report_user_id2'])."')" : '') ."
				
			order by dispatch_im.linedate_added desc,id desc
		";
		$data = simple_query($sql);
	?>
	<table class='admin_menu2 font_display_section' style='margin:0 10px;width:1050px;text-align:left'>
	<tr>
		<td colspan='15' align='left'>
			<center><span class='section_heading'><?=$use_title ?></span></center>			
		</td>
	</tr>
	<tr style='font-weight:bold'>
		<td nowrap>Msg</td>
		<td nowrap>From</td>
		<td nowrap>Date</td>
		<td nowrap>To</td>
		<td>Posted Message</td>
	</tr>
	<?
		$counter = 0;
		
		while($row = mysqli_fetch_array($data)) 
		{			
			//$row['im_read']
			$del_opt="Deleted";
			if($row['deleted'] ==0)		$del_opt="<span class='alert' onClick='mrr_dispatch_im_msg_kill2(".$row['id'].");' style='cursor:pointer;'><b>X</b></span>";
			
			echo "
				<tr class='".($counter % 2 == 1 ? 'odd' : 'even')." msg_id_".$row['id']."'>
					<td>".$del_opt."</td>
					<td>".trim($row['from_username'])."</td>
					<td>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
					<td>".trim($row['to_username'])."</td>
					<td>".trim($row['im_msg'])."</td>
				</tr>
			";	
			$counter++;		
		}		
	?>	
	<tr style='font-weight:bold'>
		<td nowrap><?=$counter ?></td>
		<td colspan='4'>Posted Messages</td>
	</tr>
	</table>
<? } ?>
<script type='text/javascript'>
	
</script>
<? include('footer.php') ?>