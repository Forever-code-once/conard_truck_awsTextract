<?
$usetitle = "AVI/Oil Change Log";
$use_title = "AVI/Oil Change Log";
?>
<? include('header.php') ?>
<?
if(isset($_GET['truck_id'])) {
     $_POST['truck_id'] = $_GET['truck_id'];
     $_POST['build_report'] = 1;
}

if(!isset($_POST['report_log_mode_oil']))     $_POST['report_log_mode_oil']=0;

$rfilter = new report_filter();
$rfilter->show_users		= true;
//$rfilter->show_customer 	= true;
//$rfilter->show_driver 		= true;
$rfilter->show_truck 		= true;
$rfilter->show_report_log_mode= true;
//$rfilter->show_trailer 		= true;
//$rfilter->show_load_id 		= true;
//$rfilter->show_load_only 	= true;
//$rfilter->show_dispatch_id 	= true;
//$rfilter->show_origin	 	= true;
//$rfilter->show_destination= true;
//$rfilter->show_stops	 	= true;
$rfilter->show_font_size	= true;
$rfilter->show_filter();

$mrr_table="";

if(isset($_POST['build_report']))
{
     $search_date_range = '';
     //if($_POST['dispatch_id'] != '' || $_POST['load_handler_id'] != '') {
     //} else {
          // we don't want to search by date range if the user is filtering by the load handler ID, or the dispatch ID
          $search_date_range = "
				and avi_oil_change_log.linedate_added >= '".date("Y-m-d", strtotime($_POST['date_from']))." 00:00:00'
				and avi_oil_change_log.linedate_added <= '".date("Y-m-d", strtotime($_POST['date_to']))." 23:59:59'
			";
     //}
     
     $where_clause="
			".$search_date_range."				
			".($_POST['truck_id'] ? " and avi_oil_change_log.truck_id = '".sql_friendly($_POST['truck_id'])."'" : '') ."				
			".($_POST['report_user_id'] ? " and avi_oil_change_log.user_id = '".sql_friendly($_POST['report_user_id'])."'" : '') ."	
			".($_POST['report_log_mode_oil']==1 ? " and avi_oil_change_log.oil_change>0" : '') ."
			".($_POST['report_log_mode_oil']==2 ? " and avi_oil_change_log.avi_mode>0" : '') ."
		";
     
     
     //$mrr_table=mrr_get_user_change_log($where_clause," order by avi_oil_change_log.linedate_added asc","",0,$loads_created);
     $tab="";
     
     $sql = "
			select avi_oil_change_log.*,
				users.name_first,
				users.name_last,
				trucks.name_truck
			from avi_oil_change_log
				left join users on users.id=avi_oil_change_log.user_id
				left join trucks on trucks.id=avi_oil_change_log.truck_id
			where avi_oil_change_log.deleted<=0
				$where_clause
			 order by avi_oil_change_log.linedate_added asc			
		";
     $data=simple_query($sql);
     
     $tab.="<div id='mrr_user_change_log_div'>";
     $tab.="<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
     $tab.="
			<tr>					
				<td valign='top' colspan='10'><h1>AVI/Oil Change Log</h1><br>This log was started on 10/23/2020, and going forward.  No earlier history is available.</td>
			<tr>
			<tr>					
				<td valign='top'><b>Added</b></td>	
				<td valign='top'><b>User</b></td>				
				<td valign='top' align='right'><b>Truck</b></td>
				<td valign='top' align='right'><b>Mode</b></td>					
				<td valign='top' align='right'><b>Interval</b></td>
				<td valign='top' align='right'><b>Last</b></td>
				<td valign='top' align='right'><b>Date</b></td>
				<td valign='top' align='right'><b>Next</b></td>
				<td valign='top'>&nbsp;&nbsp;<b>Note</b></td>
			<tr>
	";
     $cntr=0;
     
     while($row=mysqli_fetch_array($data)) 
     {
          $tab .= "
               <tr class='".($cntr%2==0 ? "even" : "odd")."'>					
                   <td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>	
                   <td valign='top'><a href='admin_users.php?eid=".$row['user_id']."' title='log ".$row['id'].".'>".$row['name_first']." ".$row['name_last']."</span></td>
                   <td valign='top' align='right'><a href='admin_trucks.php?id=".$row['truck_id']."' target='_blank'>".$row['name_truck']."</a></td>
                   <td valign='top' align='right'>" . ($row['avi_mode'] > 0 ? "AVI" : "Oil Change") . "</td>
                   <td valign='top' align='right'>" . $row['cur_interval'] . "</td>
                   <td valign='top' align='right'>" . $row['odometer'] . "</td>
                   <td valign='top' align='right'>" . ($row['linedate']!="0000-00-00" ? date("m/d/Y",strtotime($row['linedate'])) : "--N/A--") . "</td>
                   <td valign='top' align='right'>" . $row['odometer_next'] . "</td>
                   <td valign='top'>&nbsp;&nbsp;" . $row['notice'] . "</td>
               <tr>
          ";
          $cntr++;
     }
     $tab.="</table><br><center>".$cntr." Records Found.</center><br></div>";
     
}
?>
     
     <div style='margin-left:30px;margin-top:10px;display:inline;float:left;clear:right'>
          <!--
          <div style='color:purple;'>
               &nbsp;
          </div>
          -->
     </div>
     <div style='clear:both'></div>
     <div style='padding:10px; width:1200px; border:1px solid #000000;'>
          <?
          echo $tab;
          ?>
     </div>
     <br>
     <script type='text/javascript'>
         //$('.tablesorter').tablesorter();

         $().ready(function() {

         });
     
     </script>
<? include('footer.php') ?>