<? include('application.php') ?>
<? include_once('functions_load_edis.php') ?>
<?

if(!isset($_GET['connect_key']) || $_GET['connect_key'] != 'bas82bad98fqhbnwga8shq34908asdhbn') {
	die("You have reached this page incorrectly.");
}

//this page pulls all new XML files from /vlp/ folder (Visual Load Plus) application and processes them to dynamically create new loads from the XML data.... Added Oct. 2013
//http://trucking.conardlogistics.com/conard_logistics_edi_cron_job.php?connect_key=bas82bad98fqhbnwga8shq34908asdhbn

$base_dir=getcwd();
$base_dir=str_replace("\\www","\\vlp",$base_dir);

echo "<br>CWD: ".$base_dir."";


$fetch_ftp=1;
if($fetch_ftp==1)
{
     //download the file from FTP server first.  save it to local server and process that as before.
     $local_file = "vlp_".date("YmdHis",time()).".xml";	
     $conn_id = ftp_connect("ftp.vlponline.com");	
     
     $login_result = ftp_login($conn_id, "conard-trk", '$ruDet4d');
         	
     $list = ftp_nlist($conn_id, ".");
	 
	 if($list){
          
     for($i=0; $i < count($list); $i++)
     {
     	$server_file = trim($list[$i]);
     	
     	echo "<br>".$i."--".$server_file."";
         	 
          if(ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) 
          {
              	if(copy($local_file, $base_dir."\\".$local_file))
              	{
              	     echo " ...<b>Successfully copied file $local_file to VLP directory.</b> ";
                    
                   	if (ftp_delete($conn_id, $server_file)) 
                    {
                     	echo " ...$server_file deleted successful from FTP site.<br>";
                    } 
                    else 
                    {
                     	echo " ...but could not delete $server_file from FTP site.<br>";
                    }                    
          	}
          } 
          else 
          {
              	echo "...<b>There was a problem getting the file.</b><br>";
          }          
	}	

	}else{
		echo 'FTP Not connected';
	}
	ftp_close($conn_id);
}


//now process from local directory.
echo "<br><b>Contents:</b><br>";

$sent_notice=0;

$cntr=0;
$rd=opendir($base_dir);
while($filename=readdir($rd))
{	
	$typer="File";
	if(is_dir("".$base_dir."/".$filename.""))
	{
		$typer="<b>Directory</b> ";	
	}
	if($typer=="File")
	{
		
		echo "<br>".($cntr+1)." [".$typer."] ".$filename."";
		
		$fnamer="".$filename."";	
		$xml_file="".$base_dir."/".$filename."";
		
		$mrr_msg="";
		$page="";
		$load_listing="";
		$auto_save=1;
		
		if(substr_count(strtolower($fnamer),".xml") > 0)
		{
			$mrr_msg.="<br>Processing:...<br>";				
			$xml = simplexml_load_file($xml_file);
			
			$res=mrr_process_visual_load_plus_edi($xml,7,$auto_save);		//7=Conard Logistics, 0 or 1 for auto-save
			$page=trim($res['page']);			
			$loads=$res['loads'];
			$load_ids=$res['load_arr'];
			$dups=$res['duplicates'];
			
			$load_listing="".$loads." Load(s) Created: ";
			echo " ...".$loads." Load(s) Created: ";
			
			$first_load=0;
			
			for($z=0; $z < $loads; $z++)
			{
				if($z > 0)	$load_listing.=", ";
				else			$first_load=$load_ids[$z];
				
				$load_listing.="<a href='manage_load.php?load_id=".$load_ids[$z]."' target='_blank'>Load ".$load_ids[$z]."</a>";	
				
				echo " ...<a href='manage_load.php?load_id=".$load_ids[$z]."' target='_blank'>Load ".$load_ids[$z]."</a>";
			}
			
			if($loads > 0)
			{
				@ rename($xml_file, "".$base_dir."/completed/Load_".$first_load."_".$filename."");
				$sent_notice++;	
			}
			elseif($dups > 0)
			{
				echo " ...<b>DUPLICATE LOAD</b> ...see <a href='manage_load.php?load_id=".$dups."' target='_blank'>Load ".$dups."</a>";		
				@ rename($xml_file, "".$base_dir."/completed/Load_".$dups."_DUP_".$filename."");
			}
		}
		else
		{
			$mrr_msg.="<br>Error: This utility is only set up for properly formed XML files.<br>";	
			echo " ...Error: This utility is only set up for properly formed XML files.";
		}
		
		$cntr++;
	}
}
echo "<br><br>".$cntr." file(s) found in ".$base_dir."/ directory.<br>";
closedir($rd);

if($sent_notice > 0)		mrr_send_vlp_notice();

?>