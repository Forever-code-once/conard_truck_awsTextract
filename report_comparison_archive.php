<?
ini_set("max_input_vars","10000");
?>
<? include('header.php') ?>
<?

	$use_title = "Comparison Report Archive";
	$usetitle = "Comparison Report Archive";
	//mrr_add_print_ability_conard('printable_area1', $use_title);
	
	/*
	if(isset($_GET['driver_id'])) {
		$_POST['driver_id'] = $_GET['driver_id'];
		$_POST['build_report'] = 1;
	}
	*/
	if(isset($_GET['date_from']))	
	{
		$_GET['date_from']=str_replace("_","/",$_GET['date_from']);	
		$_POST['date_from']=$_GET['date_from'];
		
		$_POST['build_report']=1;
	}
	if(isset($_GET['date_to']))		
	{
		$_GET['date_to']=str_replace("_","/",$_GET['date_to']);
		$_POST['date_to']=$_GET['date_to'];
				
		$_POST['build_report']=1;
	}
	if(!isset($_POST['date_from'])) 	$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) 	$_POST['date_to'] = date("n/j/Y", time());
	//if(!isset($_POST['driver_id'])) $_POST['driver_id'] = 0;
	//if(!isset($_POST['employer_id'])) $_POST['employer_id'] = 0;
	
	
	$mrr_date_list="";
	
	//get all the month components for x months past current, including max number of days
	$cur_month=date("m");		$cur_year=date("Y");
	
	$months[0]="";				$maxdays[0]=0;
	$months[1]="January";		$maxdays[1]=31;
	$months[2]="February";		$maxdays[2]=28;
	$months[3]="March";			$maxdays[3]=31;
	$months[4]="April";			$maxdays[4]=30;
	$months[5]="May";			$maxdays[5]=31;
	$months[6]="June";			$maxdays[6]=30;
	$months[7]="July";			$maxdays[7]=31;
	$months[8]="August";		$maxdays[8]=31;
	$months[9]="September";		$maxdays[9]=30;
	$months[10]="October";		$maxdays[10]=31;
	$months[11]="November";		$maxdays[11]=30;
	$months[12]="December";		$maxdays[12]=31;
	
	if($cur_month==2 && $cur_year%4==0)	$maxdays[ $cur_month ]=29;
	
		
	$month_cont=(((int) date("Y") - 2012) * 12) + ( (int) date("m") ) - 1;
	$res="";
	if($month_cont > 0)
	{				
		$res="
			<div style='width:1800px; max-width:1800px; max-height:200px; overflow:auto;'>
			<table width='100%' cellpadding='0' cellspacing='0' border='0'>
			<tr>		
		";
		
		for($i=0; $i< $month_cont; $i++)
		{
			if($i%6==0 && $i > 0)
			{
				$res.="
					</tr>
					<tr>
				";		
			}
			if($i%12==0 && $i > 0)
			{
				$res.="
						<td valign='top'>&nbsp;</td>
					</tr>
					<tr>
				";		
			}
			
			$this_month=($cur_month - ($i + 1));
			$next_month=($cur_month - $i);
			if($next_month==1)
			{
				$cur_year-=1;
				$this_month=12;
			}
			if($this_month==2 && $cur_year%2==0)	$maxdays[ $this_month ]=29;
			
			$sel="";
			if(!isset($_POST["mrr_archive_included_".$i.""]))		$_POST["mrr_archive_included_".$i.""]=0;	
			if($_POST["mrr_archive_included_".$i.""] > 0 )		$sel=" checked";
			
			$res.="
				<td valign='top' width='16%'>
					<input type='checkbox' id='mrr_archive_included_".$i."' name='mrr_archive_included_".$i."' value='1'".$sel." onClick='submit();'>
					<input type='hidden' id='mrr_archive_start_".$i."' name='mrr_archive_start_".$i."' value='".$cur_year."-".$this_month."-01'>
					<input type='hidden' id='mrr_archive_ended_".$i."' name='mrr_archive_ended_".$i."' value='".$cur_year."-".$this_month."-".$maxdays[ $this_month ]."'>
					".$months[ $this_month ]." ".$cur_year."					
				</td>
			";					
		}	
		
		$res.="
			</tr>
			</table><input type='hidden' id='mrr_archive_tot' name='mrr_archive_tot' value='".$month_cont."'>
			</div>
			<br>
		";
	}
	$quick_links_reporting=$res;	
		
	function mrr_get_comparison_archive_group($date_from,$date_to)
	{
		$start_display=date("m/d/Y", strtotime($date_from));
		$ended_display=date("m/d/Y", strtotime($date_to));		
		
		//get settings
		$setting1[0]="";
		$setting1[1]="";
		$setting1[2]="";
		$setting1[3]="";
		$setting1[4]="";
		$setting1[5]="";
		
		$setting2[0]="";
		$setting2[1]="";
		$setting2[2]="";
		$setting2[3]="";
		$setting2[4]="";
		$setting2[5]="";
		
		$tot[0]=0;
		$tot[1]=0;
		$tot[2]=0;
		$tot[3]=0;
		$tot[4]=0;
		$tot[5]=0;	
				
		$sql="
			select *
			from comparison_archive
			where linedate_start>='".$date_from." 00:00:00' 
				and linedate_end<='".$date_to." 23:59:59'
				and section_id>=97
			order by section_id asc
		";
		$data = simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{			
			if($row['section_id']==97)
			{	//general settings
				$setting1[0]="Tot $".number_format($row['sales_percent'],2);
				$setting1[1]="Miles ".number_format($row['budget_value'],2);
				$setting1[2]="Inv $".number_format($row['actual_value'],2);
				$setting1[3]="Days(Run) ".number_format($row['variance_value'],2);
				$setting1[4]="Days(Act) ".number_format($row['difference'],2);
				//$setting1[5]=$row['difference_percent'],2);
			}
			elseif($row['section_id']==98)
			{	//general settings
				$setting2[0]="DC $".number_format($row['sales_percent'],2);
				$setting2[1]="Var $".number_format($row['budget_value'],2);
				$setting2[2]="Truck $".number_format($row['actual_value'],2);
				$setting2[3]="Trailer $".number_format($row['variance_value'],2);
				$setting2[4]="Admin $".number_format($row['difference'],2);
				$setting2[5]="Insur $".number_format($row['difference_percent'],2);
			}
			elseif($row['section_id']==99)
			{	//totals
				$tot[0]=$row['sales_percent'];
				$tot[1]=$row['budget_value'];
				$tot[2]=$row['actual_value'];
				$tot[3]=$row['variance_value'];
				$tot[4]=$row['difference'];
				$tot[5]=$row['difference_percent'];	
			}						
		}
		
		$tab_wide=" width='100'";
		$mrr_tab="
		<table border='0' width='100%' cellpadding='0' cellspacoing='0'>
			<tr>
				<td valign='top' align='left'><b>Accounts</b></td>
				<td valign='top' align='center'><b>From ".$start_display."<br>Settings</b></td>
				<td valign='top' align='center'><b>To ".$ended_display."<br>Settings</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Fuel</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Insurance</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Labor<br>(Drivers)</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Truck<br>Maint</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Tires</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Truck<br>Lease</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Trailer<br>Maint</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Truck<br>Rental</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Truck<br>Mileage<br>Expenses</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Admin<br>Expenses</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Misc.<br>Expenses</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Trailer<br>Rental</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Accident</b></td>
				<td valign='top' align='center'".$tab_wide."><b>Trailer<br>Mileage<br>Expenses</b></td>
				<td valign='top' align='right' width='125'><b>Total</b></td>	
			</tr>
		";
		
		//normal account rows
		$labels[0]="Sales Total";	// ($".number_format($sales_tot,2).")
		$labels[1]="Budgetary";
		$labels[2]="Actual";
		$labels[3]="Variance";
		$labels[4]="Over/Under";
		$labels[5]="O/U Percent";
		
		$slots[0][0]="";
		for($i=0;$i < 6; $i++)
		{	//rows
			for($j=0;$j <= 16; $j++)
			{	//columns
				if($j==4 || $j==11 || $j==13)	$j++;	//skip these sections 
				$slots[ $i ][ $j ]="0.00";
			}	
		}
		$sql="
			select *
			from comparison_archive
			where linedate_start='".$date_from." 00:00:00' 
				and linedate_end='".$date_to." 23:59:59'
				and section_id<97
			order by section_id asc
		";
		$data = simple_query($sql);
		while($row=mysqli_fetch_array($data))
		{
			$sales=$row['sales_percent'];
			$budget=$row['budget_value'];
			$actual=$row['actual_value'];
			$variance=$row['variance_value'];
			$diff=$row['difference'];
			$diff_perc=$row['difference_percent'];	
			
			$ind=$row['section_id'];
			
			$slots[ 0 ][ $ind ]=number_format($sales,2);
			$slots[ 1 ][ $ind ]=number_format($budget,2);
			$slots[ 2 ][ $ind ]=number_format($actual,2);
			$slots[ 3 ][ $ind ]=number_format($variance,2);
			$slots[ 4 ][ $ind ]=number_format($diff,2);
			$slots[ 5 ][ $ind ]=number_format($diff_perc,2);			
		}	
		for($i=0;$i < 6; $i++)
		{	//rows
			$mrr_tab.="<tr class='".( $i%2==0 ? 'even' : 'odd' )."'>";
			$mrr_tab.=	"<td valign='top' align='left'>".$labels[ $i ]."</td>";
			$mrr_tab.=	"<td valign='top' align='right'>".$setting1[ $i ]."</td>";
			$mrr_tab.=	"<td valign='top' align='right'>".$setting2[ $i ]."</td>";
			
			$sym1="$";
			$sym2="";
			if($i==5 || $i==0)
			{
				$sym1="";
				$sym2="%";	
			}
			for($j=0;$j <= 16; $j++)
			{	//columns	
				if($j==4 || $j==11 || $j==13)	$j++;	//skip these sections			
				$mrr_tab.=	"<td valign='top' align='right'>".$sym1."".$slots[ $i ][ $j ]."".$sym2."</td>";
			}	
			$mrr_tab.=	"<td valign='top' align='right'>".$sym1."".$tot[ $i ]."".$sym2."</td>";
			$mrr_tab.="</tr>";
		}
		
		$mrr_tab.="
			</table>
		";
		return $mrr_tab;
	}
	
	
	?>
	<form action='report_comparison_archive.php' method='post'>
	<div style='background-color:white;' width='1800'>
		<center><b>Comparison Report Archive:  (Include the selected Months)</b></center><br>	
		<?= $quick_links_reporting ?>
	</div>
	<div id='full_graph' style='background-color:white;' width='1800'></div>
	<br>	
	<table border='0' class='admin_menu1' width='1800'>
		<?
		if(!isset($_POST["mrr_archive_tot"]))		$_POST["mrr_archive_tot"]=0;	
						
		for($x=0;$x < $_POST["mrr_archive_tot"]; $x++)
		{
			if(!isset($_POST["mrr_archive_included_".$x.""]))		$_POST["mrr_archive_included_".$x.""]=0;	
			if($_POST["mrr_archive_included_".$x.""] > 0 )
			{
				$start_date=$_POST["mrr_archive_start_".$x.""];	//$start_display=date("m/d/Y", strtotime($_POST["mrr_archive_start_".$x.""]));
				$ended_date=$_POST["mrr_archive_ended_".$x.""];	//$ended_display=date("m/d/Y", strtotime($_POST["mrr_archive_ended_".$x.""]));
				
				$period=substr($start_date,0,7);
				$period=str_replace("-"," ",$period);
				$period=trim($period);
				$period=str_replace(" ","-",$period);
				
				$mrr_date_list.="".$period." ... ";	
				
				$value_disp=mrr_get_comparison_archive_group($start_date,$ended_date);				
				echo "
					<tr style='background-color:white;'>
						<td valign='top' align='left'>".$value_disp."<br></td>
					</tr>
				";
				
				
			}
		}
		?>		
	</table>
	<input type='hidden' name='month_list' id='month_list' value='<?= $mrr_date_list ?>'>
	</form>
	<?
	
?>
<script type='text/javascript'>	
	//$('#date_from').datepicker();
	//$('#date_to').datepicker();
	
	$().ready(function() 
	{	
		//$('.invoice_samples').hide();	
		
		mrr_full_graph_generator();	
	});
	
	function mrr_full_graph_generator()
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=mrr_full_graph_generator",
		   data: {
		   	"month_list": $('#month_list').val()
		   	},
		   dataType: "xml",
		   cache:false,
		   error: function() {
				$.prompt("Error: Cannot create Comparison Archive graph.");				
			},
		   success: function(xml) {				
				mytxt=$(xml).find('GraphHTML').text();						
				$('#full_graph').html(mytxt);		
			}		   
		 });
	}
		
</script>
<? include('footer.php') ?>