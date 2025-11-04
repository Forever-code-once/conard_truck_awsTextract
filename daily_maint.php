<? include('application.php') ?>
<?

if(!isset($_GET['connect_key']) || $_GET['connect_key'] != 'bas82bad98fqhbnwga8shq34908asdhbn') {
	die("You have reached this page incorrectly.");
}

//echo mrr_test_PN_last_msg_sent_date();


//remove older HOS (Hours of Service) files from Driver Attachments section older than 6 months by start date....was upload date.
$sql = "
	update attachments set
		deleted=1
	where cat_id='4' and section_id='1' 
		and linedate_start<'".date("Y-m-d",strtotime("-6 month",time()))." 00:00:00' and linedate_start!='0000-00-00 00:00:00'
";
simple_query($sql);	


$found_raises=mrr_process_scheduled_pay_raises(1);

$found_requests=mrr_send_email_list_of_maint_requests(1);		//send Maint Request list to Dispatch...testing here.

$found_reviews=mrr_send_email_list_of_driver_reviews(15);		//0=days before now to find reviews due.
			
echo '
	<br><b>Maint Requests Found for Email Notice:</b> '.$found_requests.'.<br>
	<br><b>Driver Reviews for Email Notice:</b> '.$found_reviews.'.<br>
	<br><b>Driver Raises Processed:</b> '.$found_raises.'.<br>
';

$res=mrr_pmi_fed_trucks_due_soon(7,0);
echo "<h2>Truck Oil/PM/FED Due Report: (".$res['num']." Found)</h2><br>".$res['report']."<br>";

$res=mrr_pmi_fed_trailers_due_soon(7);
echo "<h2>Trailer PMI/FED Due Report: (".$res['num']." Found)</h2><br>".$res['report']."<br>";

echo "<b>Garbage Collector:</b>:<br><br>";
$mrr_gc=mrr_site_garbage_collector(1);
echo $mrr_gc;

include('mrr_daily_cron_job.php');

function mrr_site_garbage_collector($del_mode=0)
{	//if $del_mode > 0  ... actually delete the items...  Same function can be used to display contents and run delete operation
	global $defaultsarray;
	$base_path = trim($defaultsarray['base_path']);	
	$dir="www/temp";
	
	$listing="<b>Files in ".$base_path."".$dir."/:</b></br>";
	$limit=1000;			//force stop in loop so that the file is 
	$cntr=0;
	$dcntr=0;
	$dater=date("Ymd",strtotime("-15 day",time()));	//two weeks...half month.  Anything older should be removed.
	
	$listing.="<b>(Removing Files older than ".$dater.")</b></br>";
	
	if($d=opendir($base_path."".$dir))
	{		
		while (false !== ($str = readdir($d)) && $cntr<=$limit) 
		{        		
        		if ($str != "." && $str != "..")
        		{
        			$color="green";
        			$type="File";
        			$mdate="00000000";
        			$size=0;
        			$operation="";
        			
        			if(is_dir($str))
        			{
        				$color="red";
        				$type="Dir.";	
        			}
        			else
        			{
        				$type=filetype($base_path."".$dir."/".$str);	
        				$size=filesize($base_path."".$dir."/".$str);
        				$mdate=date("Ymd",filemtime($base_path."".$dir."/".$str));
        				
        				if($mdate < $dater)
        				{	//remove it if older than max days to keep...
        					if($del_mode > 0)
        					{
        						@unlink($base_path."".$dir."/".$str);
        						$dcntr++;	
        					}
        					$operation=" ...<span style='color:red;'><b>DELETE</b></span>";        					
        				}
        			}        			
        			$listing.="".($cntr+ 1).". <span style='color:".$color.";'>".$type.": ".$str."</span> - ".$mdate." [".$size." Bytes]".$operation."</br>";	
        			$cntr++;
        		}	
   		}
   		closedir($d);
	}	
	$listing.="<b>".$cntr." Files Found.</b></br>";
	$listing.="<b>".$dcntr." <span style='color:red;'>DELETED</span>.</b></br>";
	return $listing;
}

//update timezones for stop locations...
$resultmrr=mrr_update_stop_GPS_timezones();
echo "<br><b>Update TimeZone for updated Stops:</b><br><br>".$resultmrr."<br>";
?>