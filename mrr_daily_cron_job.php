<? 
if(isset($_GET['connect_key']))
{
	include('application.php');	
}
	/*******************************************************************************************\ 
	NOTICE:
	Page to get current packets from various daily scheduled tasks without user interface.  
	This page is run by cron-job or scheduled tasks. INCLUDED in daily_maint.php file.
	\*******************************************************************************************/
	$days=14;
	$cntr=0;
	
	$subject="Driver Alert(s) within ".$days." Days as of ".date("m/d/Y").":";
	
	$res="
		<table boder='0' cellpadding='1' cellspacing='1' width='800'>
		<tr>
			<td valign='top' colspan='5'><b>".$subject."</b></td>
		</tr>
		<tr>
			<td valign='top'><b>Driver</b></td>
			<td valign='top'><b>License</b></td>
			<td valign='top'><b>Medical</b></td>
			<td valign='top'><b>COV</b></td>
			<td valign='top'><b>Unavailable/Vacation</b></td>
			<td valign='top'><b>Misc.</b></td>
		</tr>
	";	
	
	$email_from="driver.alerts@conardtransportation.com";			
	
	$email_from_name="Driver Alerts";		
	
	$email_to_name="Dispatch";
	
	$email_to=trim($defaultsarray['insurance_report_email_address2']);	//diane,James, etc.
	
	$email_to=trim($defaultsarray['company_email_address']);			//Dispatch
	
	$email_to.=",".trim($defaultsarray['peoplenet_hot_msg_cc']);		//MRR
	
	$email_to.=",dconard@conardtransportation.com";					//Dale
	
	$email_to.=",kmullins@conardtransportation.com";					//Katee Mullins
	
	$my_now=date("Y-m-d")." 00:00:00";
			
	/* get the driver expiration list for email. */
	$sql = "
		select drivers.*,
			DATEDIFF(drivers.linedate_license_expires,'".$my_now."') as diff1,
			DATEDIFF(drivers.linedate_drugtest,'".$my_now."') as diff2,
			DATEDIFF(drivers.linedate_cov_expires,'".$my_now."') as diff3,
			DATEDIFF(drivers.linedate_misc_cert,'".$my_now."') as misc_due,
			(
			 	select CONCAT(drivers_unavailable.reason_unavailable, ' <b>starts</b> ' , DATE_FORMAT(drivers_unavailable.linedate_start, '%W %M %e %Y')) 
			 	from drivers_unavailable 
			 	where drivers_unavailable.driver_id=drivers.id 
			 		and drivers_unavailable.deleted=0
			 		and DATEDIFF(drivers_unavailable.linedate_start,'".$my_now."') < ".$days."
			 		and drivers_unavailable.linedate_start>= '".$my_now."'
			 	order by drivers_unavailable.linedate_start desc
			 	limit 1
			) as coming_vacation,
			(
				select CONCAT(drivers_unavailable.reason_unavailable, ' <b>ends</b> ' , DATE_FORMAT(drivers_unavailable.linedate_end, '%W %M %e %Y'))  
			 	from drivers_unavailable 
			 	where drivers_unavailable.driver_id=drivers.id 
			 		and drivers_unavailable.deleted=0
			 		and drivers_unavailable.linedate_start<= '".$my_now."'
			 		and drivers_unavailable.linedate_end > '".$my_now."'
			 	order by drivers_unavailable.linedate_start desc
			 	limit 1
			) as during_vacation			
			
		from drivers
		where drivers.deleted = 0 
			and drivers.active = 1
			and (
		 		DATEDIFF(drivers.linedate_license_expires,'".$my_now."') <= ".$days." or linedate_license_expires < '2014-01-01 00:00:00'
		 		or
		 		DATEDIFF(drivers.linedate_drugtest,'".$my_now."') <= ".$days." or linedate_drugtest < '2014-01-01 00:00:00'
		 		or
		 		DATEDIFF(drivers.linedate_cov_expires,'".$my_now."') <= ".$days." or linedate_cov_expires < '2014-01-01 00:00:00'
		 		or
		 		(
		 			(DATEDIFF(drivers.linedate_misc_cert,'".$my_now."') <= ".$days." or linedate_misc_cert < '2014-01-01 00:00:00') 
		 			and drivers.misc_cert_name!='' 
		 			and linedate_misc_cert!='0000-00-00 00:00:00'
		 		)
		 		or
		 		(
		 			select count(*) 
			 		from drivers_unavailable 
			 		where drivers_unavailable.driver_id=drivers.id 
			 			and drivers_unavailable.deleted=0
			 			and DATEDIFF(drivers_unavailable.linedate_start,'".$my_now."') < ".$days."
			 			and drivers_unavailable.linedate_start>= '".$my_now."'
		 		) > 0
		 		or
		 		(
		 			select count(*)
			 		from drivers_unavailable 
			 		where drivers_unavailable.driver_id=drivers.id 
			 			and drivers_unavailable.deleted=0
			 			and drivers_unavailable.linedate_start<= '".$my_now."'
			 			and drivers_unavailable.linedate_end > '".$my_now."'
		 		) > 0
		 	)
			
		order by drivers.name_driver_last, drivers.name_driver_first
	";
	$data = simple_query($sql);
	while($row=mysqli_fetch_array($data))
	{
		$alinker="<a href='http://trucking.conardtransportation.com/admin_drivers.php?id=".$row['id']."' target='_blank'>".$row['name_driver_last']." ".$row['name_driver_first']."</a>";
		$license="";
		$medical="";
		$cov="";
		$misc="";
		$vacation1="";
		$vacation2="";		
		
		if(trim($row['coming_vacation'])!="")		$vacation1="<b>Will be Unavailable:</b>".trim($row['coming_vacation']);
		if(trim($row['during_vacation'])!="")		$vacation2="<b>Currently on Time Off:</b>".trim($row['during_vacation']);
		
		if(!isset($row['diff1']) || !is_numeric($row['diff1']))		$row['diff1']=0;
		if(!isset($row['diff2']) || !is_numeric($row['diff2']))		$row['diff2']=0;
		if(!isset($row['diff3']) || !is_numeric($row['diff3']))		$row['diff3']=0;
		if(!isset($row['misc_due']) || !is_numeric($row['misc_due']))	$row['misc_due']=0;
		
		if($row['linedate_license_expires']!="0000-00-00 00:00:00")		$license=date("m/d/Y",strtotime($row['linedate_license_expires']));
		if($row['linedate_drugtest']!="0000-00-00 00:00:00")			$medical=date("m/d/Y",strtotime($row['linedate_drugtest']));
		if($row['linedate_cov_expires']!="0000-00-00 00:00:00")		$cov=date("m/d/Y",strtotime($row['linedate_cov_expires']));
		if($row['linedate_misc_cert']!="0000-00-00 00:00:00")			$misc=date("m/d/Y",strtotime($row['linedate_misc_cert']));
		
		if($row['diff1'] < $days || $row['diff2'] < $days || $row['diff3'] < $days || trim($vacation1)!="" || trim($vacation2)!="" || $row['misc_due'] < $days)
		{
			$res.="
				<tr style='background-color:#".($cntr %2==0 ? "eeeeee" : "dddddd").";'>
					<td valign='top'>".$alinker."</td>
					<td valign='top'>".$license."</td>
					<td valign='top'>".$medical."</td>
					<td valign='top'>".$cov."</td>
					<td valign='top'>".$vacation1."<br>".$vacation2."</td>
					<td valign='top'>".($misc!="" ? "".$misc.": ".trim($row['misc_cert_name'])."" : "")."</td>
				</tr>
			";
			$cntr++;
		}
	}
	$res.="
		<tr>
			<td valign='top' colspan='6'><b>".$cntr." Driver(s) found with alerts.</b></td>
		</tr>
		</table>
	";
	
	mrr_trucking_sendMail($email_to,$email_to_name,$email_from,$email_from_name,"","",$subject,$res,$res);
			
	echo '<br>
		<br><b>Driver Notifications:</b>
		<br> '.$res.'. 
		<br>		
		<br>---------------------------------------';
?>