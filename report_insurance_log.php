<? include('application.php') ?>
<?
	$use_title="Insurance Report Archive Log";
	
	if(isset($_GET['report_form_mode']))		$_POST['report_form_mode']=(int) $_GET['report_form_mode'];
	if(isset($_GET['report_print_mode']))		$_POST['report_print_mode']=(int) $_GET['report_print_mode'];
	if(isset($_GET['report_month']))			$_POST['report_month']=(int) $_GET['report_month'];
	if(isset($_GET['report_year']))			$_POST['report_year']=(int) $_GET['report_year'];
	
	if(!isset($_POST['report_form_mode']))		$_POST['report_form_mode']=0;
	if(!isset($_POST['report_print_mode']))		$_POST['report_print_mode']=0;
	if(!isset($_POST['report_month']))			$_POST['report_month']=date("m",time());
	if(!isset($_POST['report_year']))			$_POST['report_year']=date("Y",time());
	
?>
<? include('header.php') ?>
<form action='' id='report_form' method='post'>
<table class='font_display_section' style='text-align:left;width:1450px'>
<tr>
	<td valign='top'>
     	<div style='text-align:left;margin:10px'>
     		<div class='section_heading'>Insurance Report Archive Log</div>
     		<a href='report_insurance.php' target='_blank'>View "Classic" Insurance Report</a>
     		<br>
     		<a href='report_insurance2v.php' target='_blank'>Go to Calendar Year version of Insurance Report</a>
     		<br>     		
     		<a href='report_insurance_form.php' target='_blank'>or "New" Insurance Report Form</a>     		
     	</div>
     </td>
     <td valign='top' align='right'>     	
		<div style='width:300px; border:1px solid #999999; margin:5px; padding:10px; background-color:#ffffff'>
     		<b>Insurance Period:</b>
     		<select name='report_month' id='report_month'>
     			<option value='1'<?=($_POST['report_month']==1 ? " selected": "") ?>>January</option>
     			<option value='2'<?=($_POST['report_month']==2 ? " selected": "") ?>>February</option>
     			<option value='3'<?=($_POST['report_month']==3 ? " selected": "") ?>>March</option>
     			<option value='4'<?=($_POST['report_month']==4 ? " selected": "") ?>>April</option>
     			<option value='5'<?=($_POST['report_month']==5 ? " selected": "") ?>>May</option>
     			<option value='6'<?=($_POST['report_month']==6 ? " selected": "") ?>>June</option>
     			<option value='7'<?=($_POST['report_month']==7 ? " selected": "") ?>>July</option>
     			<option value='8'<?=($_POST['report_month']==8 ? " selected": "") ?>>August</option>
     			<option value='9'<?=($_POST['report_month']==9 ? " selected": "") ?>>September</option>
     			<option value='10'<?=($_POST['report_month']==10 ? " selected": "") ?>>October</option>
     			<option value='11'<?=($_POST['report_month']==11 ? " selected": "") ?>>November</option>
     			<option value='12'<?=($_POST['report_month']==12 ? " selected": "") ?>>December</option>
     		</select>
     		/
     		<select name='report_year' id='report_year'>
     			<?
     			$cur_year=date("Y",time());
     			for($i=2015; $i<=$cur_year; $i++)
     			{
     				?>
     				<option value='<?=$i ?>'<?=($_POST['report_year']==$i ? " selected": "") ?>><?=$i ?></option>
     				<?
     			}
     			?>
     		</select>
     		<br>
     		<br>
     		<b>Report Mode:</b>
          	<select name='report_print_mode' id='report_print_mode'>
     			<option value='0'<?=($_POST['report_print_mode']==0 ? " selected": "") ?>>All</option>
     			<option value='1'<?=($_POST['report_print_mode']==1 ? " selected": "") ?>>Emailed</option>
     			<option value='2'<?=($_POST['report_print_mode']==2 ? " selected": "") ?>>Print/Run</option>
     		</select>
     		
     		<select name='report_form_mode' id='report_form_mode'>
     			<option value='0'<?=($_POST['report_form_mode']==0 ? " selected": "") ?>>Any Form</option>
     			<option value='1'<?=($_POST['report_form_mode']==1 ? " selected": "") ?>>Classic</option>
     			<!---<option value='2'<?=($_POST['report_form_mode']==2 ? " selected": "") ?>>Calendar Year</option>--->
     			<option value='3'<?=($_POST['report_form_mode']==3 ? " selected": "") ?>>New Form</option>
     		</select>
     		<br>
     		<input type='submit' name='show_it' id='show_id' value='Run Report'>
		</div>
     </td>
</tr>
<tr>
	<td colspan='2'>
	<?
	function mrr_decode_rep_month($mn=0)
	{
		$mn=(int) $mn;
		
		$month[0]="";
		$month[1]="January";
		$month[2]="February";
		$month[3]="March";
		$month[4]="April";
		$month[5]="May";
		$month[6]="June";
		$month[7]="July";
		$month[8]="August";
		$month[9]="September";
		$month[10]="October";
		$month[11]="November";
		$month[12]="December";
		return $month[$mn];
	}
	function mrr_decode_rep_version($ver=0)
	{
		$ver=(int) $ver;
		
		$version[0]="";
		$version[1]="Classic";
		$version[2]="Calendar";
		$version[3]="New Form";
		return $version[$ver];
	}
	
	$report_tab="";
	$show_report=1;
	
	if($show_report==1) 
	{	
		$report_tab.= "
			<h2>Insurance Report Archive Log: ".mrr_decode_rep_month($_POST['report_month'])." ".$_POST['report_year']."</h2>
			<table border='0' cellpadding='0' celspacing='0' width='1450'>
			<thead>
			<tr>
				<th><b>#</b></th>
				<th><b>Created</b></th>
				<th><b>Month</b></th>
				<th><b>Year</b></th>
				<th><b>User</b></th>
				<th><b>Form</b></th>
				<th><b>Mode</b></th>				
				<th><b>Email Address Sent</b></th>
				<th><b>&nbsp;</b></th>
				<th><b>&nbsp;</b></th>
			</tr>
			</thead>
			<tbody>
		";
		
     	$sql = "
			select insurance_log.*,
				(select username from users where users.id=insurance_log.user_id) as user_name 
			from insurance_log
			where user_id>0
				".($_POST['report_form_mode'] > 0 ? "and ins_form_flag='".(int) $_POST['report_form_mode']."'" : "")."
				".($_POST['report_print_mode'] > 0 ? "and mode='".(int) $_POST['report_print_mode']."'" : "")."
				".($_POST['report_month'] > 0 ? "and period_month='".(int) $_POST['report_month']."'" : "")."
				".($_POST['report_year'] > 0 ? "and period_year='".(int) $_POST['report_year']."'" : "")."
			order by linedate_added asc, id asc
		";
		$data=simple_query($sql);   
		$counter = 0;
		while($row = mysqli_fetch_array($data)) 
		{
			$html=trim($row['insurance_html']);
					
			$report_tab.= "
     				<tr class='".($counter%2==0 ? "even" : "odd")."'>
     					<td width='50'>".($counter+1)."</td>
     					<td width='150' nowrap>".date("m/d/Y H:i:s", strtotime($row['linedate_added']))."</td>
     					<td width='50'>".mrr_decode_rep_month($row['period_month'])."</td>
     					<td width='50'>".$row['period_year']."</td>
     					<td>".trim($row['user_name'])."</td>
     					<td width='100' nowrap>".mrr_decode_rep_version($row['ins_form_flag'])."</td>
     					<td width='50'>".($row['mode'] ==1 ? "Emailed" : "Run/Printed")."</td>     					
     					<td>".trim($row['email_address'])."</td>     					
     					<td width='50'><span class='mrr_link_like_on' onClick='mrr_show_ins_report(".$row['id'].");'><b>Show</b></span></td>
     					<td width='50'><span class='mrr_link_like_on' onClick='mrr_hide_ins_report(".$row['id'].");'><b>Hide</b></span></td>
     				</tr>
     				<tr class='".($counter%2==0 ? "even" : "odd")." all_ins_reps ins_rep_".$row['id']."'>
     					<td colspan='10' style='background-color:#ffffff; text-align:left;'><div style='width:1400px; border:1px solid #999999; margin:5px; padding:10px;'>".$html."</div></td>
     				</tr>
     			";			
			$counter++;
		}  	
		$report_tab.= "</tbody>
			</table>
		";
		
		echo $report_tab;
	}
	?>
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	$().ready(function() {
		$('.all_ins_reps').hide();
	});
	
	function mrr_show_ins_report(id)
	{
		$('.ins_rep_'+id+'').show();
	}
	function mrr_hide_ins_report(id)
	{
		$('.ins_rep_'+id+'').hide();
	}
</script>
<? include('footer.php') ?>