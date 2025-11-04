<? include('application.php') ?>
<?
$use_title="Conard-QuadGraphicsInc Cron Job";
$mrr_bypass_login=1;
?>
<? include('header.php') ?>
<?
//Quad Graphics EDI auto-routine (cronjob)...  Added by MRR 3/12/2020...Sherrod Computers

//Unlike the other EDI files, this one just has to find loads, and if the load has a truck attached and Lading number (BOL) is set, then send the dispatch(es) to them in a CSV file.
$cust_id=929;
$comp_name="Quad Graphics Inc";
$conard_scac="CDPN";
$mc_number=533846;
$dot_number=1407133;

$uuid = "_".date("YmdHis",time());	//.createuuid();
$excel_filename = "QuadGraphicInc".$uuid.".csv";
$export_file = "";
$use_excel=1;

$cols=array("CustomerAccountNumber","SCAC",  "MC_NUMBER","DOT_NUMBER","CARRIER_ID",  "ORDER",       "BILL_OF_LADING",   "LICENSE_PLATE",    "VEHICLE_ID",  "MOBILE_PHONE_NUMBER");
$reqs=array("Required",            "Required","Required","Required",  "Optional",    "Optional",    "Required",        "Optional",         "Required",    "Optional");
//             V83754	          STJI	     861361	     2482262
//             Quad Graphics Inc   CDPN      533846         1407133						

$tab="<h2><a href='admin_customers.php?eid=".$cust_id."' target='_blank'>".$comp_name."</a>  EDI Auto-Routine Notification Untility. <a href='quad_graphics_edi_cron.php'>Reload Page</a></h2>";
$tab.="<table cellpadding='2' cellspacing='2' border='0' width='1400'>";
$tab.="<tr style='font-weight:bold;'>
        <td valign='top'>".$cols[0]."</td>
        <td valign='top'>".$cols[1]."</td>
        <td valign='top'>".$cols[2]."</td>
        <td valign='top'>".$cols[3]."</td>
        <td valign='top'>".$cols[4]."</td>
        <td valign='top'>".$cols[5]."</td>
        <td valign='top'>".$cols[6]."</td>
        <td valign='top'>".$cols[7]."</td>
        <td valign='top'>".$cols[8]."</td>
        <td valign='top'>".$cols[9]."</td>
    </tr>";
$tab.="<tr style='font-weight:bold;'>
        <td valign='top'><i>".$reqs[0]."</i></td>
        <td valign='top'><i>".$reqs[1]."</i></td>
        <td valign='top'><i>".$reqs[2]."</i></td>
        <td valign='top'><i>".$reqs[3]."</i></td>
        <td valign='top'><i>".$reqs[4]."</i></td>
        <td valign='top'><i>".$reqs[5]."</i></td>
        <td valign='top'><i>".$reqs[6]."</i></td>
        <td valign='top'><i>".$reqs[7]."</i></td>
        <td valign='top'><i>".$reqs[8]."</i></td>
        <td valign='top'><i>".$reqs[9]."</i></td>
    </tr>";
//$tab.="";
//$tab.="</table>";

$export_file .= "".$cols[0].",".
     "".$cols[1].",".
     "".$cols[2].",".
     "".$cols[3].",".
     "".$cols[4].",".
     "".$cols[5].",".
     "".$cols[6].",".
     "".$cols[7].",".
     "".$cols[8].",".
     "".$cols[9]."";
$export_file .= chr(13);
/*
$export_file .= "".$reqs[0].",".
     "".$reqs[1].",".
     "".$reqs[2].",".
     "".$reqs[3].",".
     "".$reqs[4].",".
     "".$reqs[5].",".
     "".$reqs[6].",".
     "".$reqs[7].",".
     "".$reqs[8].",".
     "".$reqs[9]."";
$export_file .= chr(13);
*/
$sql="
     select trucks_log.*,
        trucks.license_plate_no,
        trucks.vin,
        trucks.geotab_device_id,
        trucks.name_truck,
        load_handler.load_number
     from trucks_log
        left join load_handler on load_handler.id=trucks_log.load_handler_id
        left join trucks on trucks.id=trucks_log.truck_id
     where trucks_log.deleted=0 and load_handler.deleted=0
        and trucks_log.customer_id='".$cust_id."'
        and trucks_log.linedate_pickup_eta>='".date("Y-m-d",time())." 00:00:00'
        and (load_handler.load_number!='' or load_handler.pickup_number!='' or load_handler.delivery_number!='')
        and trucks_log.truck_id>0
        and trucks_log.dispatch_completed=0
     order by trucks_log.linedate_pickup_eta asc
";
$data=simple_query($sql);
while($row = mysqli_fetch_array($data)) 
{
     $bol=trim($row['load_number']);
     if($bol=="")        $bol=trim($row['pickup_number']);
     if($bol=="")        $bol=trim($row['delivery_number']);
     
     $tab.="<tr>
        <td valign='top'>".$comp_name."</td>
        <td valign='top'>".$conard_scac."</td>
        <td valign='top'>".$mc_number."</td>
        <td valign='top'>".$dot_number."</td>
        <td valign='top'>".$row['load_handler_id']."</td>
        <td valign='top'>".$row['id']."</td>
        <td valign='top'>".$bol."</td>
        <td valign='top'>".trim($row['license_plate_no'])."</td>
        <td valign='top'>".trim($row['name_truck'])."</td>
        <td valign='top'></td>
    </tr>";
     //"CustomerAccountNumber","SCAC",  "MC_NUMBER","DOT_NUMBER","CARRIER_ID",  "ORDER",       "BILL_OF_LADING",   "LICENSE_PLATE",    "VEHICLE_ID",  "MOBILE_PHONE_NUMBER");
     //"Required",            "Required","Required","Required",  "Optional",    "Optional",    "Required",        "Optional",         "Required",    "Optional");     
     $export_file .= "".$comp_name.",".
          "".$conard_scac.",".
          "".$mc_number.",".
          "".$dot_number.",".
          "".$row['load_handler_id'].",".
          "".$row['id'].",".
          "".$bol.",".
          "".trim($row['license_plate_no']).",".
          "".trim($row['name_truck']).",".
          "";
     $export_file .= chr(13);
}
$tab.="</table>";
echo $tab;

$uid = md5(uniqid(time()));
$fileatt_type = "text/csv";             //application/vnd.openxmlformats-officedocument.spreadsheetml.sheet    ....for ".xlsx" format
$myfile = "/temp/".$excel_filename."";
$file_size=0;
$fcontent="";

$prefix="";
if($use_excel > 0)
{
     //make the file
     $fp = fopen(getcwd().$myfile, "w");
     fwrite($fp, $export_file);
     fclose($fp);
     
     $From=trim($defaultsarray['company_email_address']);
     $FromName=trim($defaultsarray['company_name']);
     $email_to_name="";
     $email_to="1153_".$conard_scac."@parse.project44integrations.com";
     $email_to="upload@p44-fileserver.com";
     
     //$email_to=$defaultsarray['special_email_monitor'];         $email_to_name="Lord Vader";
     $message="Loads for ".$comp_name." as of ".date("m/d/Y H:i",time())."";
     
     $prefix="<br><center><a href=\"/temp/".$excel_filename."\" target='_blank'>Click for CSV file</a><center><br>";
     
     $my_files = array();
     $my_files[0] = $myfile;    //getcwd().
     
     /*
     //now make sure the file has been created and get the file.
     $file_size = filesize(getcwd().$myfile);
     $handle = fopen(getcwd().$myfile, "r");
     $fcontent = fread($handle, $file_size);
     fclose($handle);     
     
     $fcontent = chunk_split(base64_encode($fcontent));    
     
     $headers = 'From: system@conardtransportation.com' . "\r\n" .'Reply-To: system@conardtransportation.com' . "\r\n" .'X-Mailer: PHP/' . phpversion() . "\r\n";
     $headers .= "MIME-Version: 1.0\r\n";
     $headers .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
     $headers .= "This is a multi-part message in MIME format.\r\n";
     $headers .= "--".$uid."\r\n";
     $headers .= "Content-type:text/html; charset=iso-8859-1\r\n";
     $headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
     $headers .= $message."\r\n\r\n";
     $headers .= "--".$uid."\r\n";
     $headers .= "Content-Type: text/csv; name=\"".$excel_filename."\"\r\n"; // use diff. tyoes here
     $headers .= "Content-Transfer-Encoding: base64\r\n";
     $headers .= "Content-Disposition: attachment; filename=\"".$excel_filename."\"\r\n\r\n";
     $headers .= $fcontent."\r\n\r\n";
     $headers .= "--".$uid."--";     
     
     @ mail($email_to,"".$comp_name." Loads",$message,$headers);
     */
     echo mrr_sendMail_attachment($From,$FromName,$email_to,$email_to_name,"".$comp_name." Loads",$message,$message,'','',$my_files,1);
     
     //$email_to=$defaultsarray['special_email_monitor'];         $email_to_name="Darth Vader";
     //echo mrr_sendMail_attachment($From,$FromName,$email_to,$email_to_name,"".$comp_name." Loads",$message,$message,'','',$my_files,1);
}
else
{
     $export_file=str_replace(chr(13),"<br>",$export_file);
     echo "<br>Displaying File Contents of /temp/$excel_filename:<br><div style='border: 1px solid purple; margin:10px; padding:10px;'>".$export_file."</div><br>";
}
echo "<br><br>".$prefix."<br><br>";

?>
<? include('footer.php') ?>