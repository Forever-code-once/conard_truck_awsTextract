<? include('application.php') ?>
<? $admin_page = 1 ?>
<?
include_once('logistics_trucking_functions.php');

//if(!isset($_GET['id']))				$_GET['id']=0;
//if(!isset($_GET['email_id']))			$_GET['email_id']=0;
//if(!isset($_GET['line_id']))			$_GET['line_id']=0;
//if(!isset($_GET['msg_id']))				$_GET['msg_id']=0;	
	
$msg="";

if(!isset($_POST['filter_date_from']))		$_POST['filter_date_from']=date("m/d/Y",time());
if(!isset($_POST['filter_date_to']))		$_POST['filter_date_to']=date("m/d/Y",time());
if(!isset($_POST['filter_comp']))			$_POST['filter_comp']=0;

if(trim($_POST['filter_date_from'])=="")	$_POST['filter_date_from']=date("m/d/Y",time());
if(trim($_POST['filter_date_to'])=="")		$_POST['filter_date_to']=date("m/d/Y",time());


if(!isset($_POST['filter_city']))			$_POST['filter_city']="";
if(!isset($_POST['filter_state']))			$_POST['filter_state']="";
if(!isset($_POST['filter_dest_city']))		$_POST['filter_dest_city']="";
if(!isset($_POST['filter_dest_state']))		$_POST['filter_dest_state']="";

if(isset($_GET['delid'])) 
{
	$sql = "
		update logistics_truck_listing set
			deleted = 1			
		where id = '$_GET[delid]'
	";
	$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );	
	
	//$mrr_activity_log_notes.="Deleted user $_GET[delid]. ";	
}

		
$usetitle="Logistics E-Mail Trucks: Available Units";
$use_bootstrap = true;

$tv_mode=0;
if(isset($_GET['tv_mode']))		$tv_mode=1;
?>
<? include('header.php'); ?>
<form action='' method='post' name='myform'>
	<style>
	.ui-datepicker-month {
		color:#000000;
	}
	.ui-datepicker-year {
		color:#000000;
	}	
	</style>
	<div class='container col-md-12'>
		<div class='col-md-11'>
			<div class="panel panel-primary">
				<div class="panel-heading"><?=$usetitle ?></div>
			  	<div class="panel-body">
			  		<? if($tv_mode==0) { ?>
     			  		<table class='table table-bordered well'>
     					<tr>
     						<td valign='top' nowrap><b>Date From:</b></td>
     						<td valign='top'><input type='text' name='filter_date_from' id='filter_date_from' value="<?=$_POST['filter_date_from'] ?>" style='width:100px;' class='form-control'></td>
     						<td valign='top' nowrap><b>Date To:</b></td>
     						<td valign='top'><input type='text' name='filter_date_to' id='filter_date_to' value="<?=$_POST['filter_date_to'] ?>" style='width:100px;' class='form-control'></td>
     						<td valign='top' nowrap><b>Company:</b></td>
     						<td valign='top' colspan='2'>
     							<select name='filter_comp' id='filter_comp' value="" class='form-control'>
     								<option value='0'<?=($_POST['filter_comp']==0 ? " selected" : "")?>>All Companies</option>
     								<?
     								$sql = "
                                        		select *
                                        		from logistics_truck_companies 
                                        		where deleted=0
                                        		order by company_name asc,id asc
                                        	";		
                                        	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
                                        	while($row = mysqli_fetch_array($data)) 
                                        	{
                         					echo "<option value='".$row['id']."' style='color:#".($row['active'] > 0 ? "000000" : "999999").";'".($_POST['filter_comp']==$row['id'] ? " selected" : "").">".trim($row['company_name'])."</option>";
                                        	}
     								?>
     							</select>
     						</td>
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
     					
     					<? if($_SERVER['REMOTE_ADDR'] == '50.76.161.186' && 1==2) { ?>
			  				<br><a href='logistics_trucking_controller.php?connect_key=bas82bad98fqhbnwga8shq34908asdhbn' target='_blank'>G-Mail Download</a>
			  			<? } ?>
						<br>
					<? } ?>				
					<!----
					<h3>Available Units</h3>
					---->
					<?
														//from,to,company,email_id,cust_view,city,state,dest-city,dest-state
						echo get_logistics_truck_listing_dated($_POST['filter_date_from'],$_POST['filter_date_to'],$_POST['filter_comp'],0,0,$_POST['filter_city'],$_POST['filter_state'],$_POST['filter_dest_city'],$_POST['filter_dest_state']);
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
		
		function confirm_del(id) 
		{
			//if(confirm("Are you sure you want to delete this Available Unit?")) {
				window.location = "<?=$SCRIPT_NAME?>?delid=" + id;
			//}
		}
		
		$('#filter_date_from').datepicker();
		$('#filter_date_to').datepicker();
	</script>
</form>
<? include('footer.php');  ?>