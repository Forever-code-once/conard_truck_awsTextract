<? include('application.php') ?>
<?
$usetitle = "Customer Loads";
$use_title = "Customer Loads";

if(!isset($_POST['date_from']))		$_POST['date_from']=date("m/1/Y");	
if(!isset($_POST['date_to']))			$_POST['date_to']=date("m/d/Y");	
if(!isset($_POST['sbox']))			$_POST['sbox']="";

if(!isset($_POST['cust_name']))		$_POST['cust_name']="";
if(!isset($_POST['cust_pass']))		$_POST['cust_pass']="";
if(!isset($_POST['cust_id']))			$_POST['cust_id']=0;

$_POST['cust_namer']="";

if(isset($_GET['u']))	$_POST['cust_name']=trim($_GET['u']);
if(isset($_GET['p']))	$_POST['cust_pass']=trim($_GET['p']);

$cres=mrr_find_customer_user_login($_POST['cust_name'],$_POST['cust_pass']);
$_POST['cust_id']=$cres['customer_id'];
$_POST['cust_namer']=trim($cres['customer_name']);

$comp_logo = trim($defaultsarray['peoplenet_hot_msg_logo']);
?>
<? include('header.php') ?>
<form action="<?=$SCRIPT_NAME?>?u=<?=$_POST['cust_name']?>&p=<?=$_POST['cust_pass']?>" method="post">
<input name="cust_name" id='cust_name' value="<?=$_POST['cust_name']?>" type='hidden'>
<input name="cust_pass" id='cust_pass' value="<?=$_POST['cust_pass']?>" type='hidden'>	
<input name="cust_id" id='cust_id' value="<?=$_POST['cust_id']?>" type='hidden'>
	
<table style='text-align:left; margin:5px; width:1400px;'>
<tr bgcolor='#000000' height='50'>
	<td valign='middle' colspan='2'><center><img src='<?=$comp_logo?>' border='0' width='154' height='43' alt='Conard Transportation'></center></td>
<tr>  
<tr>
	<td valign='top'>
		<table class='admin_menu1' style='width:100%'>
		<tr>
			<td valign='top' colspan='7'><div style='float:right;'><a href='customer_loads.php'>Log Out</a></div> <h1><?=$_POST['cust_namer'] ?> Loads</h1></td>	
		</tr>
		<tr>
			<td valign='top' colspan='7'><b>Search Loads</b></td>
		<tr>
		<tr>
			<td valign='top'>Find a Load Number</td>
			<td valign='top'><input name="sbox" value="<?=$_POST['sbox'] ?>" style='width:250px'></td>
			<td valign='top'>Pickup Range Date From</td>
			<td valign='top'><input name='date_from' id='date_from' value="<?=$_POST['date_from']?>" style='width:100px' class='datepicker'></td>
			<td valign='top'>Date To</td>
			<td valign='top'><input name='date_to' id='date_to' value="<?=$_POST['date_to']?>" style='width:100px' class='datepicker'></td>
			<td valign='top' align='right'><input type="submit" name='search_loads' value="Search"></td>
		</tr>
		<tr>
			<td valign='top' colspan='7'><br><hr><br></td>
		<tr>
		<? if($_POST['cust_id'] > 0) { ?>
     		<?
     		
     		$sql_filter="
     				and load_handler.linedate_pickup_eta>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'
     				and load_handler.linedate_pickup_eta<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
     		";
     		if(trim($_POST['sbox'])!="")	
     		{
     			$sql_filter="
     				and (
     					load_handler.load_number='".sql_friendly($_POST['sbox'])."'
     					or 
     					(
     						load_handler.load_number like '".sql_friendly($_POST['sbox'])."%'
     						and load_handler.linedate_pickup_eta>='".date("Y-m-d",strtotime($_POST['date_from']))." 00:00:00'
     						and load_handler.linedate_pickup_eta<='".date("Y-m-d",strtotime($_POST['date_to']))." 23:59:59'
     					)
     				)
     			";
     		}
     		
     		$sql="
     			select load_handler.*
     			from load_handler
     			where load_handler.deleted=0
     				and load_handler.customer_id='".sql_friendly($_POST['cust_id'])."'
     				".$sql_filter."
     				
     			order by load_handler.linedate_pickup_eta
     		";
     		$data=simple_query($sql);
     		$load_cntr=mysqli_num_rows($data);
     		$cntr=0;
     		?>
     		<tr>
     			<td valign='top' colspan='7'>     				
     				<div style='float:right;'>
     					
     					<span class='mrr_link_like_on' onClick='mrr_show_dispatches_for_load(0);'>Show All Dispatches</span>
     					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     					<span class='mrr_link_like_on' onClick='mrr_show_dispatches_for_load(1);'>Show Incomplete Dispatches</span>
     					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     					<span class='mrr_link_like_on' onClick='mrr_show_dispatches_for_load(2);'>Show Completed Dispatches</span>
     					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     					<br>    
     					<span class='mrr_link_like_on' onClick='mrr_hide_dispatches_for_load(0);'>Hide All Dispatches</span>
     					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  &nbsp;				
     					<span class='mrr_link_like_on' onClick='mrr_hide_dispatches_for_load(1);'>Hide Incomplete Dispatches</span>
     					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  &nbsp;
     					<span class='mrr_link_like_on' onClick='mrr_hide_dispatches_for_load(2);'>Hide Completed Dispatches</span>
     					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     				</div>
     				<b><?=$load_cntr ?> Load(s) Found</b>
     			</td>
     		<tr>
     		<tr>
     			<td valign='top' colspan='7'>
     				<table class='admin_menu2 tablesorter' style='width:100%'>
     					<thead>
     					<tr>
               				<th><b>Conard Load ID</b></th>
               				<th><b>Load No.</b></th>        
               				<th><b>Pickup ETA</b></th>
               				<th><b>Dropoff ETA</b></th>       				
               				<th><b>Origin City</b></th>
               				<th><b>Origin State</b></th>
               				<th><b>Dest City</b></th>
               				<th><b>Dest State</b></th>               				
               				<th><b>Pickup No.</b></th>
               				<th><b>Deliver No.</b></th>
               			</tr>
               			</thead>
               			<tbody>
               			<?
                         	while($row=mysqli_fetch_array($data))
                         	{                        		
                         		echo "
                         			<tr class='".($cntr%2==0 ? "even" : "odd")."'>
                         				<td valign='top'><span class='mrr_link_like_on' onClick='mrr_show_dispatches_for_load(".$row['id'].");'>".$row['id']."</span></td>
                         				<td valign='top'>".$row['load_number']."</td>   
                         				<td valign='top'>".date("M d, Y H:i", strtotime($row['linedate_pickup_eta']))."</td>
                         				<td valign='top'>".date("M d, Y H:i", strtotime($row['linedate_dropoff_eta']))."</td>                      				
                         				<td valign='top'>".$row['origin_city']."</td>
                         				<td valign='top'>".$row['origin_state']."</td>
                         				<td valign='top'>".$row['dest_city']."</td>
                         				<td valign='top'>".$row['dest_state']."</td>                         				
                         				<td valign='top'>".$row['pickup_number']."</td>
                         				<td valign='top'>".$row['delivery_number']."</td>
                         			</tr>";
                         		
                         		
                         		$sql2="
                         			select trucks_log.*,
                         				trucks.name_truck,
                         				trucks.peoplenet_tracking,
                         				trucks.geotab_current_location,
									trailers.trailer_name,
									drivers.name_driver_first,
									drivers.name_driver_last
									
                         			from trucks_log
                         				left join drivers on drivers.id=trucks_log.driver_id
									left join trucks on trucks.id=trucks_log.truck_id
									left join trailers on trailers.id=trucks_log.trailer_id 
									
                         			where trucks_log.deleted=0
                         				and trucks_log.load_handler_id='".sql_friendly($row['id'])."'
                         				
                         			order by trucks_log.linedate_pickup_eta
                         		";
                         		$data2=simple_query($sql2);
                         		$disp_cntr=mysqli_num_rows($data2);
                         		if($disp_cntr > 0)
                         		{
                         			
                         			echo "	
                              			<tr class='".($cntr%2==0 ? "even" : "odd")." all_disp_rows dispatch_row_".$row['id']."'>
                              				<td valign='top'>".$row['id']." <span class='mrr_link_like_on' onClick='mrr_hide_dispatches_for_load(".$row['id'].");'>Hide</span></td>
                              				<td valign='top'><b>Dispatch ID</b></td>
                              				<td valign='top'><b>Pickup ETA</b></td>
                              				<td valign='top'><b>Dropoff ETA</b></td>
                              				<td valign='top'><b>Origin</b></td>
                         					<td valign='top'><b>Destination</b></td>
                              				<td valign='top'><b>Truck</b></td>                         				
                              				<td valign='top'><b>Trailer</b></td>
                              				<td valign='top'><b>Driver</b>                              				
                              				<td valign='top'><b>Completed</b></td>
                              			</tr>
                              		";
                              		
                         		}
                         		//$dcntr=0;
                         		while($row2=mysqli_fetch_array($data2))
                         		{                                  			
                              		$incompleter=" incomplete";
                              		if($row2['dispatch_completed'] > 0)	$incompleter=" completed";	                              		
                              		
                              		echo "	
                              			<tr class='".($cntr%2==0 ? "even" : "odd")." all_disp_rows dispatch_row_".$row['id']."".$incompleter."'>
                              				<td valign='top'>".$row['id']."</td>
                              				<td valign='top'>".$row2['id']."</td>
                              				<td valign='top'>".date("M d, Y", strtotime($row2['linedate_pickup_eta']))."</td>
                              				<td valign='top'>".date("M d, Y", strtotime($row2['linedate_dropoff_eta']))."</td>
                              				<td valign='top'>".$row2['origin'].", ".$row2['origin_state']."</td>
                         					<td valign='top'>".$row2['destination'].", ".$row2['destination_state']."</td>
                              				<td valign='top'>".$row2['name_truck']."</td>                         				
                              				<td valign='top'>".$row2['trailer_name']."</td>
                              				<td valign='top'>".trim($row2['name_driver_first']." ".$row2['name_driver_last'])."</td>                              				
                              				<td valign='top'>".($row2['dispatch_completed'] > 0 ? "Yes" : "")."</td>
                              			</tr>
                              			";
                              		$stops_table=mrr_quick_display_all_load_handler_stops($row['id'],$row2['id'],0);	
                              		$tracking_location="";
                              		
                              		if(1==2 && $row2['peoplenet_tracking'] > 0 && $row2['dispatch_completed'] == 0 && $row2['truck_id'] > 0)
                              		{	//only get location from tracking if dispatch has not been completed (and truck and tracked).
                              			$truck_distance=0;
                              			$miles_distance=0;
                              			$res=mrr_peoplenet_email_processor_fetch_truck_lat_long($row2['truck_id'],date("m/d/Y",strtotime($row2['linedate_pickup_eta'])));
                              			$truck_lat=$res['lat'];
                              			$truck_long=$res['long'];
                              			$truck_age=$res['age'];
                              			$truck_date=$res['date'];
                              			$truck_heading=$res['closer'];
                              			$gps_location=$res['location'];	
                              			$truck_speed=$res['truck_speed'];	
                              			$truck_head=$res['truck_heading'];
                              				
                              			$head_mask="North";
                                        	if($truck_head == 1)		$head_mask="Northeast ";
                                        	if($truck_head == 2)		$head_mask="East";
                                        	if($truck_head == 3)		$head_mask="Southeast";
                                        	if($truck_head == 4)		$head_mask="South";
                                        	if($truck_head == 5)		$head_mask="Southwest";
                                        	if($truck_head == 6)		$head_mask="West";
                                        	if($truck_head == 7)		$head_mask="Northwest";	                              			
                                                            						
                              			if($truck_lat!="0" && $truck_long!="0")
                              			{
                              				$truck_distance=mrr_distance_between_gps_points($rowx['latitude'],$rowx['longitude'],$truck_lat,$truck_long,1);
                              				$truck_distance=abs($truck_distance);
                              				$miles_distance=$truck_distance / 5280;
                              			}
                              			
                              			$mrr_speed="".$truck_speed." MPH";
                              			$mrr_heading=" heading ".$head_mask."";
                              			//$mrr_location="Current Status of Truck: ".$mrr_speed."".$mrr_heading.". Truck is about ".number_format($miles_distance,2)." miles away.  Approximate Location: ".$gps_location."...";	
                              			
                              			$tracking_location="
                              				<b>Truck ".$row2['name_truck']." is".$mrr_heading." at ".$mrr_speed.".
                              				<br>Approximate Location: ".$gps_location."...</b>
                              			";	
                              		}
                              		
                              		$tracking_location="<b>Approximate Location of Truck ".$row2['name_truck'].": ".trim($row2["geotab_current_location"])."";
                              		
                              		echo "
                              			<tr class='".($cntr%2==0 ? "even" : "odd")." all_disp_rows dispatch_row_".$row['id']."".$incompleter."'>
                              				<td valign='top'>".$row['id']."</td>
                              				<td valign='top'>".$row2['id']."</td>
                              				<td valign='top' colspan='8'>".$stops_table."<br>".$tracking_location."</td>
                              			</tr>
                              		";
                              		
                         		}
                         		$cntr++;
                         	}
               			?> 
               			</tbody>
     				</table>	   				
     			</td>
     		</tr>
		<? } ?>
		</table>		
	</td>
</tr>
</table>
</form>
<script type='text/javascript'>
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
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
</script>
<? include('footer.php') ?>