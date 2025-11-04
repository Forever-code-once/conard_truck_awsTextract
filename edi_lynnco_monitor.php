<? include('application.php') ?>
<? error_reporting(E_ALL ^ E_DEPRECATED); ?>
<? include_once('functions_lynnco.php'); ?>
<? include('header.php') ?>
<?php
//Added by Michael...Sherrod Computers
if(!isset($_GET['load_no']))            $_GET['load_no']="";          $_GET['load_no']=trim($_GET['load_no']);
if(!isset($_GET['accepted']))           $_GET['accepted']=0;          $_GET['accepted']=(int) $_GET['accepted'];
if(!isset($_GET['declined']))           $_GET['declined']=0;          $_GET['declined']=(int) $_GET['declined'];

if($_GET['load_no']!="" && substr_count($_GET['load_no'],";")==0) 
{
     $accept=0;
     $decline=0;
     if($_GET['accepted'] > 0)          $accept=$_SESSION['user_id'];
     if($_GET['declined'] > 0)          $decline=$_SESSION['user_id'];
     
     $sql = "		
		update edi_accepted_loads set 
		     accepted_by='".sql_friendly($accept)."',
		     declined_by='".sql_friendly($decline)."'
		
		where load_no = '".sql_friendly(trim($_GET['load_no']))."'
		     and deleted = 0
     ";
     simple_query($sql);
}

getcwd();
$use_path = $defaultsarray['edi_lynnco_path'];
chdir($use_path);
chdir(".");

$mrr_monitor_sent = trim($defaultsarray['lynnco_monitor_email_sent']);
$mrr_monitor_last = trim($defaultsarray['lynnco_monitor_last_file']);
$mrr_monitor_cnt = (int) trim($defaultsarray['lynnco_monitor_last_cnt']);

$email_to="trucking@conardtransportation.com";
$email_to2="dconard@conardtransportation.com";
$email_to3="";      //$defaultsarray['special_email_monitor'];

$mrr_cur_sent = "";
$mrr_cur_last = "";
$mrr_cur_cnt = 0;

echo "<h2>LynnCO/MercuryGate EDI Loads Awaiting Approval:</h2>";
echo "<br>EDI Path: ".$use_path."<br>Current Directory is ".getcwd()."<br>";

echo "<br><hr><br><b>Contents of ".getcwd().":</b> ...<a href='edi_lynnco_monitor.php'>Reload Directory Listing.</a><br>";
echo "<form name='mainform' action='edi_lynnco_monitor.php' method='post'>";
echo "<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
echo "<tr>
            <td valign='top'><b>#</b></td>
            <td valign='top'><b>TYPE</b></td>
            <td valign='top'><b>NAME</b></td>
            <td valign='top'><b>LOAD</b></td>
            <td valign='top'><b>ALERT</b></td>
            <td valign='top'><b>DATES</b></td>
            <td valign='top'><b>ORIGIN</b></td>
            <td valign='top'><b>DATES</b></td>
            <td valign='top'><b>DEST</b></td>
            <td valign='top'><b>PROCESS</b></td>
      </tr>";

$file_cntr=0;
$files_list = scandir(getcwd());
foreach ($files_list as $key => $value)
{
     $type="File";
     if($value=="." || $value=="..")       $type="N/A";
     
     if($value=="backup" || $value=="other" || $value=="out")         $type="Folder";
     
     if($type=="File" || $type=="Folder")
     {
          $file_cntr++;
          $link1="";
          $link2="";
          $date1="";
          $date2="";
          $date3="";
          $date4="";
          $origin="";
          $dest="";
          $load_no="";
          if($type=="File") 
          {    
               $contents = file_get_contents($value);
               $contents=str_replace("~","<br>",$contents);
               
               $pos1=strpos($contents,"B2**CDPN**",10);
               $pos2=strpos($contents,"**TP",$pos1);
               if($pos1 > 0 && $pos2 > $pos1)
               {
                    $load_no=trim(substr($contents,$pos1,($pos2 - $pos1)));
                    $load_no=str_replace("B2**CDPN**","",$load_no);
               }
     
               $pos0=strpos($contents,"S5*",$pos2);
               
               
     
               $pos1=strpos($contents,"G62*37*",$pos0);
               $pos2=strpos($contents,"G62*38*",$pos1);
               if($pos1 > 0 && $pos2 > $pos1)
               {
                    $date1=trim(substr($contents,$pos1,($pos2 - $pos1)));
                    $date1=str_replace("G62*37*","",$date1);
                    $date1=str_replace("*I*"," ",$date1);
                    if(strlen($date1) > 13)        $date1=substr($date1,0,13);
                    
                    $yr=substr($date1,0,4);
                    $mo=substr($date1,4,2);
                    $dy=substr($date1,6,2);
     
                    $hr=substr($date1,9,2);
                    $mi=substr($date1,11,2);
                    //$sc=substr($date1,13,2);
                    
                    $date1="".$yr."-".$mo."-".$dy." ".$hr.":".$mi.":00";         //".$sc."                    
               }
               
               $pos1=strpos($contents,"G62*38*",$pos2);
               $pos2=strpos($contents,"AT8*G",$pos1);
               if($pos1 > 0 && $pos2 > $pos1)
               {
                    $date2=trim(substr($contents,$pos1,($pos2 - $pos1)));
                    $date2=str_replace("G62*38*","",$date2);
                    $date2=str_replace("*K*"," ",$date2);
                    if(strlen($date2) > 13)        $date2=substr($date2,0,13);
          
                    $yr=substr($date2,0,4);
                    $mo=substr($date2,4,2);
                    $dy=substr($date2,6,2);
          
                    $hr=substr($date2,9,2);
                    $mi=substr($date2,11,2);
                    //$sc=substr($date2,13,2);
          
                    $date2="".$yr."-".$mo."-".$dy." ".$hr.":".$mi.":00";         //".$sc."                    
               }
     
               $pos1=strpos($contents,"N4*",$pos2);
               $pos2=strpos($contents,"*USA",$pos1);
               if($pos1 > 0 && $pos2 > $pos1)
               {
                    $origin=trim(substr($contents,$pos1,($pos2 - $pos1)));
                    $origin=str_replace("N4*","",$origin);
                    $origin=str_replace("*"," ",$origin);
                    
                    $pos3=strrpos($origin," ");
                    if($pos3 > 0) 
                    {
                         $zipper = trim(substr($origin,$pos3));
                         if(strlen($zipper) > 5) 
                         {
                              $zipper_old=$zipper;
                              $zipper=substr($zipper_old,0,5)."-".substr($zipper_old,5);
                              $origin=str_replace($zipper_old,$zipper,$origin);
                         }
                    }
               }
     
               $pos1=strpos($contents,"G62*53*",$pos2);
               $pos2=strpos($contents,"G62*54*",$pos1);
               if($pos1 > 0 && $pos2 > $pos1)
               {
                    $date3=trim(substr($contents,$pos1,($pos2 - $pos1)));
                    $date3=str_replace("G62*53*","",$date3);
                    $date3=str_replace("*G*"," ",$date3);
                    if(strlen($date3) > 13)        $date3=substr($date3,0,13);
          
                    $yr=substr($date3,0,4);
                    $mo=substr($date3,4,2);
                    $dy=substr($date3,6,2);
          
                    $hr=substr($date3,9,2);
                    $mi=substr($date3,11,2);
                    //$sc=substr($date3,13,2);
          
                    $date3="".$yr."-".$mo."-".$dy." ".$hr.":".$mi.":00";         //".$sc."                    
               }
     
               $pos1=strpos($contents,"G62*54*",$pos2);
               $pos2=strpos($contents,"AT8*G",$pos1);
               if($pos1 > 0 && $pos2 > $pos1)
               {
                    $date4=trim(substr($contents,$pos1,($pos2 - $pos1)));
                    $date4=str_replace("G62*54*","",$date4);
                    $date4=str_replace("*L*"," ",$date4);
                    if(strlen($date4) > 13)        $date4=substr($date4,0,13);
          
                    $yr=substr($date4,0,4);
                    $mo=substr($date4,4,2);
                    $dy=substr($date4,6,2);
          
                    $hr=substr($date4,9,2);
                    $mi=substr($date4,11,2);
                    //$sc=substr($date4,13,2);
          
                    $date4="".$yr."-".$mo."-".$dy." ".$hr.":".$mi.":00";         //".$sc."                    
               }
     
               $pos1=strpos($contents,"N4*",$pos2);
               $pos2=strpos($contents,"*USA",$pos1);
               if($pos1 > 0 && $pos2 > $pos1)
               {
                    $dest=trim(substr($contents,$pos1,($pos2 - $pos1)));
                    $dest=str_replace("N4*","",$dest);
                    $dest=str_replace("*"," ",$dest);
     
                    $pos3=strrpos($dest," ");
                    if($pos3 > 0)
                    {
                         $zipper = trim(substr($dest,$pos3));
                         if(strlen($zipper) > 5)
                         {
                              $zipper_old=$zipper;
                              $zipper=substr($zipper_old,0,5)."-".substr($zipper_old,5);
                              $dest=str_replace($zipper_old,$zipper,$dest);
                         }
                    }
               }
               
               $link1="<a href='edi_lynnco_monitor.php?load_no=".trim($load_no)."&accepted=1' style='color:#00CC00; cursor:pointer;'><b>Accept Load</b></a>";
               $link2="<a href='edi_lynnco_monitor.php?load_no=".trim($load_no)."&declined=1' style='color:#CC0000; cursor:pointer;'><b>Decline Load</b></a>";
               
               $sql = "
			     select accepted_by,declined_by,id			
			     from edi_accepted_loads
			     where load_no = '".sql_friendly($load_no)."'
				    and deleted = 0
		      ";
               $data = simple_query($sql);
               if($row = mysqli_fetch_array($data))
               {
                    // found it, so it is already in the system...  see if already accepted, or declined, and change the links accordingly to show it is marked...
                    if($row['accepted_by'] > 0)         $link1="<span style='color:purple;'><b>Accept Pending</b></span>";
                    if($row['declined_by'] > 0)         $link2="<span style='color:orange;'><b>Decline Pending</b></span>";
               }
               elseif(trim($load_no)!="")
               {
                    //make record of this file for updates later...
                    
                    $sql = "
			         insert into edi_accepted_loads
			             (id,
			             filename,
			             load_no,
			             linedate_added,
			             dates,
			             origin,
			             dest,
			             accepted_by,
			             declined_by,
			             response_990,
			             deleted)
			         values 
			             (NULL,
			             '".sql_friendly(trim($value))."',
			             '".sql_friendly($load_no)."',
			             NOW(),
			             '".sql_friendly(trim("Pickup From ".$date1." To ".$date2.", Dropoff From ".$date3." To ".$date4.""))."',
			             '".sql_friendly($origin)."',
			             '".sql_friendly($dest)."',
			             0,
			             0,
			             '',
			             0)
		          ";
                    simple_query($sql);
               }              
          }
     
          $alerter="";
          if(substr_count(strtolower($value),"-cancel-") > 0)       $alerter="<b><i>CANCELLED</i></b>";
          
          if(trim($load_no)!="") 
          {     
               echo "
                    <tr class='" . ($file_cntr % 2 == 0 ? "even" : "odd") . "'>
                         <td valign='top'>[" . $file_cntr . "]</td>
                         <td valign='top'>" . $type . "</td>
                         <td valign='top'>" . ($type == "Folder" ? "<b><i>" . $value . "</i></b>" : "" . $value . "") . "</td>
                         <td valign='top'>" . $load_no . "</td>
                         <td valign='top'>" . $alerter . "</td>
                         <td valign='top'>" . ($type == "File" ? "From " . $date1 . "<br>----To " . $date2 . "" : "") . "</td>
                         <td valign='top'>" . $origin . "</td>
                         <td valign='top'>" . ($type == "File" ? "From " . $date3 . "<br>----To " . $date4 . "" : "") . "</td>
                         <td valign='top'>" . $dest . "</td>
                         <td valign='top'>" . ($type == "File" ? "" . $link1 . " or  " . $link2 . "" : "<i>DIRECTORY</i>") . "</td>
                    </tr>               
                ";
               if($type == "File" && 1 == 2) 
               {
                    echo "
                         <tr class='" . ($file_cntr % 2 == 0 ? "even" : "odd") . "'>
                             <td valign='top'> </td>
                             <td valign='top' colspan='8'>" . $contents . "</td>
                             <td valign='top'> </td>
                         </tr>
                    ";
               }
               $mrr_cur_sent = $date1;
               $mrr_cur_last = trim($load_no);
               $mrr_cur_cnt++;
          }
          
     }          
}
echo "</table><br><br>";

echo "<center><a href='edi_lynnco_in.php' target='_blank'>Click here to Process Accepted (and Declined) EDI Loads</a></center>";

echo "<center><br><b>NOTES:</b>";
echo "<br>Processing link will take all the ones marked as either Accepted or Declined and run them through the correct route.  THIS IS NO LONGER RUN AUTOMATICALLY, so please use the link to run it.<br><br>";
echo "<br><span style='color:#00CC00;'><b>Accept Load</b></span>: Accepting the EDI Load will create the Load and send the Acceptance via 990 Response.";
echo "<br><span style='color:#CC0000;'><b>Decline Load</b></span>: Declining the EDI Load will create ONLY 990 Response that shows it was Rejected/Declined/Removed for the load.";
echo "<br><span style='color:purple;'><b>Accept Pending</b></span>: Load is set to get Approved.  If this was a mistake, click on Decline Load link to switch.";
echo "<br><span style='color:orange;'><b>Decline Pending</b></span>: Load is set to get Declined. If this was a mistake, click on Accept Load link to switch.";
echo "</center>";

echo "</form>";

//send alert emails if needed...

if($mrr_cur_cnt > 0 && ($mrr_cur_cnt!=$mrr_monitor_cnt || $mrr_cur_last!=$mrr_monitor_last || $mrr_cur_sent!=$mrr_monitor_sent))
{
     //send the email out...
     $subj="Lynnco/MercuryGate EDI Loads Awaiting";
     $msg="
            Looks like there are ".$mrr_cur_cnt." Lynnco/MercuryGate EDI Load(s) to be reviewed and processed... 
            <br>Click the link (assuming you are logged in) and process them, please.
            <br><br>
            <a href='https://trucking.conardtransportation.com/edi_lynnco_monitor.php'>https://trucking.conardtransportation.com/edi_lynnco_monitor.php</a>
            <br>
            <br>Thank you. <br>
     ";
     
     
     mrr_trucking_sendMail($email_to,'EDI Monitor',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subj,$msg,$msg);
     if(trim($email_to2)!="")		mrr_trucking_sendMail(trim($email_to2),'EDI Monitor',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subj,$msg,$msg);
     if(trim($email_to3)!="")		mrr_trucking_sendMail(trim($email_to3),'EDI Monitor',"system@conardtransportation.com",$defaultsarray['company_name'],'','',$subj,$msg,$msg);
     
     
     $sqlu="update defaults set xvalue_string='".sql_friendly($mrr_cur_sent)."' where xname='lynnco_monitor_email_sent'";         simple_query($sqlu);
     $sqlu="update defaults set xvalue_string='".sql_friendly($mrr_cur_last)."' where xname='lynnco_monitor_last_file'";          simple_query($sqlu);
     $sqlu="update defaults set xvalue_string='".sql_friendly($mrr_cur_cnt)."' where xname='lynnco_monitor_last_cnt'";            simple_query($sqlu);
}
?>
<? include('footer.php') ?>