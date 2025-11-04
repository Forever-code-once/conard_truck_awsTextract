<? include('application.php') ?>
<?
$usetitle = "Available Trucks";
$use_title = "Available Trucks";

if(!isset($_POST['filter_date_from']))		$_POST['filter_date_from']=date("m/d/Y",time());
if(!isset($_POST['filter_date_to']))		$_POST['filter_date_to']=date("m/d/Y",time());

if(!isset($_POST['filter_city']))			$_POST['filter_city']="";
if(!isset($_POST['filter_state']))			$_POST['filter_state']="";
if(!isset($_POST['filter_dest_city']))		$_POST['filter_dest_city']="";
if(!isset($_POST['filter_dest_state']))		$_POST['filter_dest_state']="";

//if(!isset($_POST['sbox']))			$_POST['sbox']="";

if(!isset($_POST['cust_name']))		$_POST['cust_name']="";
if(!isset($_POST['cust_pass']))		$_POST['cust_pass']="";
if(!isset($_POST['cust_id']))			$_POST['cust_id']=0;

$_POST['cust_namer']="";
$_POST['cust_email']="";
$_POST['cust_phone']="";

if(isset($_GET['u']))	$_POST['cust_name']=trim($_GET['u']);
if(isset($_GET['p']))	$_POST['cust_pass']=trim($_GET['p']);

$cres=mrr_find_logistics_company_login($_POST['cust_name'],$_POST['cust_pass']);
$_POST['cust_id']=$cres['customer_id'];
$_POST['cust_namer']=trim($cres['customer_name']);
$_POST['cust_email']=trim($cres['customer_email']);
$_POST['cust_phone']=trim($cres['customer_phone']);

include_once('logistics_trucking_functions.php');

$max=20;

$cntr=0;

if(isset($_POST['add_trucks_available']))
{
	$comp_id=$_POST['cust_id'];
	$comp_email=trim($_POST['cust_email']);
	$comp_name=trim($_POST['cust_namer']);
	
	$from=trim($defaultsarray['company_email_address']);
	$from_name=trim($defaultsarray['company_name']);
	
	$from="loads@conardlogistics.com";
	$from_name="Conard Logistics";
	
	$sql = "
		insert into logistics_truck_emails 
			(id,
			linedate_added,
			processed,
			comp_id,
			subject,
			email_msg,
			dispatch_warning,     					
			email_address,
			deleted)
		values
			(NULL,
			NOW(),
			1,
			'".$comp_id."',     					
			'Manual Entry',
			'These Entries were entered manually.',
			0,
			'".$comp_email."',
			0)
	";
	simple_query($sql);	
	$email_id=mysqli_insert_id($datasource);
	
	$export_file = "";
	
	$export_file .= "".$comp_name." Available Units".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9).
			"".chr(9);
	$export_file .= chr(13);	
		
	$export_file .= "".chr(9).
			"Available Date".chr(9).
			"Location".chr(9).
			"State".chr(9).
			"Destination".chr(9).
			"State".chr(9).
			"Notes".chr(9);
	$export_file .= chr(13);	
	
	
	$tab="<h2>".$comp_name." Available Units</h2>";
	$tab.="<table class='table table-bordered well'>";
	$tab.="
    		<tr>
     		<td valign='top'><b>Available Date</b></td>     			
     		<td valign='top'><b>Location</b></td>
     		<td valign='top'><b>State</b></td>
     		<td valign='top'><b>Destination</b></td>
     		<td valign='top'><b>State</b></td>
     		<td valign='top'><b>Notes</b></td>	
     	</tr>
     ";
     		//<td valign='top'><b>Truck</b></td>
     		//<td valign='top'><b>Trailer</b></td>
     	
	for($i=0;$i < $max; $i++)
	{		
		$cur_date=trim($_POST["avail_truck_".$i."_date"]);
		$truck="";	//trim($_POST["avail_truck_".$i."_truck"]);
		$trailer="";	//trim($_POST["avail_truck_".$i."_trail"]);
		$local=trim($_POST["avail_truck_".$i."_local"]);
		$local_state=trim($_POST["avail_truck_".$i."_local_state"]);
		$dest_city=trim($_POST["avail_truck_".$i."_dest_city"]);
		$dest_state=trim($_POST["avail_truck_".$i."_dest_state"]);
		$note=trim($_POST["avail_truck_".$i."_notes"]);
		
		if($cur_date=="")		$cur_date=date("m/d/Y",time())." 00:00:00";
		
		if($cur_date!="" && ($truck!="" || $trailer!="" || $local!="" || $local_state!="" || $dest_city!="" || $dest_state!=""))
		{
			$add_id=0;
     		$add_id=add_logistics_truck_listing($comp_id,$email_id,$cur_date,$truck,$trailer,$local,$note,$local_state,$dest_city,$dest_state);
     		if($add_id > 0)
     		{     		
     			echo "<br>Line ".$cntr.": ";
     			
     			$tab.="
          			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>  				
          				<td valign='top'>".date("m/d/Y H:i",strtotime($cur_date))."</td> 
          				<td valign='top'>".trim($local)."</td>
          				<td valign='top'>".trim($local_state)."</td>
          				<td valign='top'>".trim($dest_city)."</td>
          				<td valign='top'>".trim($dest_state)."</td>
          				<td valign='top'>".trim($note)."</td>
          			</tr>
     			";
     			
     			$export_file .= "".chr(9).
					"".date("m/d/Y H:i",strtotime($cur_date))."".chr(9).
					"".trim($local)."".chr(9).
					"".trim($local_state)."".chr(9).
					"".trim($dest_city)."".chr(9).
					"".trim($dest_state)."".chr(9).
					"".trim($note)."".chr(9);
				$export_file .= chr(13);	
     			
     			$cntr++;
     		}
     	}
     	    	
	}
	$tab.="</table>";
	
	if($cntr > 0)
	{
		//create the PDF file for this company. && $comp_id==27
		$tab2=$tab;
		$tab2=str_replace("</tr>","</tr>\r\n",$tab2);
		$tab2=strip_tags($tab2);
		
		$use_excel=1;
		$use_attchment=0;
		
		$uuid = createuuid();
		$excel_filename = "available_trucks_$uuid.xls";
		
		$prefix1="";
		$prefix2="";
		if($use_excel > 0) 
		{
			$fp = fopen(getcwd() . "/temp/$excel_filename", "w");
			fwrite($fp, $export_file); 
			fclose($fp);
			
			$prefix1="\r\n \r\n Download Excel: http://trucking.conardlogistics.com/temp/".$excel_filename." \r\n \r\n";
			$prefix2="<br><br><a href=\"http://trucking.conardlogistics.com/temp/".$excel_filename."\" target='_blank'>Download Excel</a><br><br>";
		}
				
		$subject="Available Trucks";
		$msg1="".$comp_name." has the following Available Units:\r\n \r\n ".$tab2." \r\n \r\n";
		$msg2="<b>".$comp_name." has the following Available Units:</b><br><br> ".$tab." <br><br>";
			
		if($use_attchment > 0)	
		{				
			$sentit=mrr_special_mail_attachment($comp_email, $from, $from_name, $from, $subject, $msg1, $excel_filename, getcwd()."/temp/");
		}
		else
		{		
			$msg1.=$prefix1;
			$msg2.=$prefix2;
			//$sentit=mrr_trucking_sendMail($_POST['mrr_email_addr'],$_POST['mrr_email_addr_name'],$From,$user_name,'','',$Subject,$txt,$html);
			$sentit=mrr_trucking_sendMail($comp_email,'Available Trucks',$from,$from_name,'','',$subject, $msg1, $msg2);	
		}
	}
	
	header("Location: logistics_trucking_email_form.php?u=".$_POST['cust_name']."&p=".$_POST['cust_pass']."");
	die();
}

$comp_logo = trim($defaultsarray['peoplenet_hot_msg_logo']);

$use_bootstrap = true;
?>
<? include('header.php') ?>
<?
$comp_logo="/images/2012/logo.png";
?>
<form action="<?=$SCRIPT_NAME?>?u=<?=$_POST['cust_name'] ?>&p=<?=$_POST['cust_pass'] ?>" method='post' name='myform'>
	<input name="cust_name" id='cust_name' value="<?=$_POST['cust_name']?>" type='hidden'>
	<input name="cust_pass" id='cust_pass' value="<?=$_POST['cust_pass']?>" type='hidden'>	
	<input name="cust_id" id='cust_id' value="<?=$_POST['cust_id']?>" type='hidden'>
	
	<div style='text-align:center; width:100%; padding-top:10px; padding-bottom:10px; margin-bottom:10px; background-color:#000000;'><img src='<?=$comp_logo ?>' border='0' width='154' height='43' alt='Conard Logistics'></div>
	
	<div class='container col-md-12'>
		<div class='col-md-11'>
			<div class="panel panel-primary">
				<div class="panel-heading">Welcome.  Please enter the Available Units you would like to offer Conard Logistics.</div>
			  	<div class="panel-body">
			  		<h3><?=$usetitle ?></h3>
					<p><?=($cntr > 0 ? "".$cntr." Available Units have been added." : "") ?></p>
					<table class='table table-bordered well'>
					<tr>  			
     					<td valign='top'><b>#</b></td>
     					<td valign='top'><b>Available Date</b></td>
     					
     					
     					<td valign='top'><b>Location/City</b></td>
     					<td valign='top'><b>State</b></td>
     					<td valign='top'><b>Destination</b></td>
     					<td valign='top'><b>State</b></td>
     					<td valign='top'><b>Notes</b></td>	
     				</tr>
					<?
						//<td valign='top'><b>Truck/Unit</b></td>
						//<td valign='top'><b>Trailer/Unit</b></td>
					for($i=0; $i < $max; $i++)
					{
						echo "
                    			<tr style='background-color:#".($i%2==0 ? "eeeeee" : "dddddd" ).";'> 			
                    				<td valign='top'>".($i+1)."</td>   	
                    				<td valign='top'><input type='text' name='avail_truck_".$i."_date' id='avail_truck_".$i."_date' value=\"\"  style='width:100px;' class='form-control mrr_datepicker' placeholder='mm/dd/yyyy'></td>     				     				
                    				
                    				
                    				<td valign='top'><input type='text' name='avail_truck_".$i."_local' id='avail_truck_".$i."_local' value=\"\" style='width:200px;' class='form-control' placeholder='location city'></td>
                    				<td valign='top'><input type='text' name='avail_truck_".$i."_local_state' id='avail_truck_".$i."_local_state' value=\"\" style='width:100px;' class='form-control' placeholder='state'></td>
                    				<td valign='top'><input type='text' name='avail_truck_".$i."_dest_city' id='avail_truck_".$i."_dest_city' value=\"\" style='width:200px;' class='form-control' placeholder='destination city'></td>
                    				<td valign='top'><input type='text' name='avail_truck_".$i."_dest_state' id='avail_truck_".$i."_dest_state' value=\"\" style='width:100px;' class='form-control' placeholder='state'></td>
                    				<td valign='top'><input type='text' name='avail_truck_".$i."_notes' id='avail_truck_".$i."_notes' value=\"\" style='width:300px;' class='form-control' placeholder='notes'></td>
                    			</tr>
                    		";	
                    			//<td valign='top'><input type='text' name='avail_truck_".$i."_truck' id='avail_truck_".$i."_truck' value=\"\" style='width:150px;' class='form-control' placeholder='truck name'></td>
                    			//<td valign='top'><input type='text' name='avail_truck_".$i."_trail' id='avail_truck_".$i."_trail' value=\"\" style='width:150px;' class='form-control' placeholder='trailer/details'></td>
					}
					?>
					<tr>  			
     					<td valign='top' align='center' colspan='7'>
     						<button type='submit' name='add_trucks_available' id='add_trucks_available' class='btn btn-success'><span class='glyphicon glyphicon-plus'></span> Submit</button>
     					</td>	
     				</tr>
					</table>
			  		<br>	
			  		<table class='table table-bordered well'>
					<tr>
						<td valign='top' nowrap><b>Date From:</b></td>
						<td valign='top'><input type='text' name='filter_date_from' id='filter_date_from' value="<?=$_POST['filter_date_from'] ?>" style='width:100px;' class='form-control'></td>
						<td valign='top' nowrap><b>Date To:</b></td>
						<td valign='top'><input type='text' name='filter_date_to' id='filter_date_to' value="<?=$_POST['filter_date_to'] ?>" style='width:100px;' class='form-control'></td>
						<td valign='top' nowrap><b>Company:</b></td>
						<td valign='top' align='right' colspan='2'><?=$_POST['cust_namer'] ?><br> <b>Phone: </b><?=$_POST['cust_phone']?> <b>E-Mail: </b><?=$_POST['cust_email']?></td>
						<td valign='top' align='right'><button type='submit' name='filter_date_items' id='filter_date_items' class='btn btn-success'><span class='glyphicon glyphicon-search'></span> Search</button></td>
					</tr>
					<tr>
						<td valign='top' nowrap><b>Location:</b><br>{Optional}</td>
						<td valign='top'><input type='text' name='filter_city' id='filter_city' value="<?=$_POST['filter_city'] ?>" style='width:200px;' class='form-control'></td>
						<td valign='top' nowrap><b>Location State:</b><br>{Optional}</td>
						<td valign='top'><input type='text' name='filter_state' id='filter_state' value="<?=$_POST['filter_state'] ?>" style='width:100px;' class='form-control'></td>
						<td valign='top' nowrap><b>Destination:</b><br>{Optional}</td>
						<td valign='top'><input type='text' name='filter_dest_city' id='filter_dest_city' value="<?=$_POST['filter_dest_city'] ?>" style='width:200px;' class='form-control'></td>
						<td valign='top' nowrap><b>Destination State:</b><br>{Optional}</td>
						<td valign='top'><input type='text' name='filter_dest_state' id='filter_dest_state' value="<?=$_POST['filter_dest_state'] ?>" style='width:100px;' class='form-control'></td>
					</tr>
					</table>					
					<?
						//<h3>Conard Available Loads</h3>
						//echo mrr_pull_logistics_available_loads($_POST['filter_date_from'],$_POST['filter_date_to'],0,"","");		//$days=0,$customer_id=0,$local_city="",$local_state=0
					?>
					<br>	
					<h3>Saved Available Units</h3>
					<?
														//from,to,company,email_id,cust_view,city,state,dest-city,dest-state
						echo get_logistics_truck_listing_dated($_POST['filter_date_from'],$_POST['filter_date_to'],$_POST['cust_id'],0,1,$_POST['filter_city'],$_POST['filter_state'],$_POST['filter_dest_city'],$_POST['filter_dest_state']);	
					?>				
			  	</div>
			</div>
		</div>
		<div class='col-md-1'>
			<!--
			<div class="panel panel-info">
				<div class="panel-heading">&nbsp;</div>
				<div class="panel-body">
					&nbsp;
				</div>
			</div>
			-->
			&nbsp;
		</div>
	</div>
	<script type='text/javascript'>
		$('#filter_date_from').datepicker();
		$('#filter_date_to').datepicker();
		$('.mrr_datepicker').datepicker();
		
		$('.mrr_datepicker').on(
        		'dp.show',
        		function(e) {
        			$(".bootstrap-datetimepicker-widget").css(
        			"background-color", "#CC0000");
        	});
		
	</script>
</form>
<script type='text/javascript'>
	//$('#date_from').datepicker();
	//$('#date_to').datepicker();
	/*
	$('.tablesorter').tablesorter();
	
	$().ready(function() {
		
		$('.all_disp_rows').hide();
		$('.completed').hide();
		$('.incomplete').show();
	});
		
	function mrr_show_dispatches_for_load(id)
	{
		$('.all_disp_rows').hide();
		if(id==0)		$('.all_disp_rows').show();
		if(id==1)		$('.incomplete').show();
		if(id==2)		$('.completed').show();
		if(id >2)		$('.dispatch_row_'+id+'').show();
	}
	
	function mrr_hide_dispatches_for_load(id)
	{		
		if(id==0)		$('.all_disp_rows').hide();
		if(id==1)		$('.incomplete').hide();
		if(id==2)		$('.completed').hide();
		if(id >2)		$('.dispatch_row_'+id+'').hide();
	}
	*/
</script>
<? include('footer.php?notime=1') ?>