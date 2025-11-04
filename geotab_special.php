<? include('application.php') ?>
<?
if(!isset($_GET['connect_key']) || $_GET['connect_key'] != 'bas82bad98fqhbnwga8shq34908asdhbn') 
{
	//die("You have reached this page incorrectly.");
}

echo "<h3>GeoTab SPECIAL Test Page: ".date("m/d/Y",time())."</h3>";

echo "<br>Acct: ".mrr_find_geotab_username()."";
echo "<br>Pass: ".mrr_find_geotab_password()."";
echo "<br>GeoTab DB: ".mrr_find_geotab_database()."";
echo "<br>GeoTab Server: ".mrr_find_geotab_server_name()."";
echo "<br>GeoTab Session ID: ".mrr_find_geotab_session_id()."";
echo "<br>Conard DB: ".mrr_find_geotab_database_name()."";

echo "<br>URL 0: ".mrr_find_geotab_url()."";
echo "<br>URL 1: ".mrr_find_geotab_url_1()."";
echo "<br>URL 2: ".mrr_find_geotab_url_2()."";

echo "<br>GeoTab vs PN Switch is <b>".($_SESSION['use_geotab_vs_pn']==1 ? "ON" : "off")."</b>.<br>";

echo "<br><h3>GeoTab URLs:</h3><br>";

echo "<br>URL: ".mrr_find_geotab_url()." --- Version ".mrr_get_geotab_version()."";
echo "<br>
		<a href='".mrr_find_geotab_url()."GetVersion' target='_blank'>
			".mrr_find_geotab_url()."GetVersion
		</a>
	<br><br>
	Use the link, <a href='geotab_cronjob.php'>GeoTab CronJob</a>, for Feed Menu Options.  Or use the normal Diagnostics...<a href='geotab.php'>GeoTab</a>
	<br><br>
	";	

$diagnostic_mode=1;
$diagnotics_save=1;

$email_from="geotab.alerts@conardtransportation.com";			
$email_from_name="GeoTab Alerts";	
$email_to_name="Lord Vader";
$email_to="".trim($defaultsarray['peoplenet_hot_msg_cc']);		//MRR

$trucks_updated="<br>".$email_to_name.",";
$trucks_updated.="<br><b>Trucks requiring manual updates... or D1 (Adjusted) Odometer Readings:</b><br><hr><br>";

$sql = "
	select id, name_truck, geotab_device_id, geotab_last_odometer_reading, geotab_last_odometer_date
	from trucks
	where deleted <= 0
		and geotab_odometer_update_mode > 0
		and active > 0
	order by name_truck asc
";
$data = simple_query($sql);
while($row = mysqli_fetch_array($data))
{
	$device_id=trim($row['geotab_device_id']);
	
	$trucks_updated.="<br>Truck ".$row['id']." -- ".$row['name_truck']." -- {Device ".$device_id."}. Odometer Reading: ".$row['geotab_last_odometer_reading']." as of ".date("m/d/Y H:i:s",strtotime($row['geotab_last_odometer_date']))."";
	
	
	if($diagnotics_save > 0)
	{
		echo "<br>Getting Device Diagnostics [".$diagnostic_mode."]:<br>
			Date From: ".trim($defaultsarray['geotab_last_odometer'])." or [1] ".trim($defaultsarray['geotab_last_odometer_special']).".<br>
			".mrr_get_geotab_get_device_info($device_id,$diagnostic_mode,$diagnotics_save)."<br>
		";		//second arg is mode (0=ALL, 1=odometer, etc.)
		
		$trucks_updated.=" ...<span style='color:#cc0000;'><b>UPDATED :)</b></span>";	
	}
}	

echo $trucks_updated;

//mrr_trucking_sendMail($email_to,$email_to_name,$email_from,$email_from_name,"","","Trucks Updated by GeoTab_Special.php file.",$trucks_updated,$trucks_updated);

echo "<br><br>Done.<br>";
?>