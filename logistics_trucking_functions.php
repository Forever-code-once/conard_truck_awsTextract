<?
	//ini_set("max_input_vars","20000");  Must change in INI file...
	error_reporting(0);
	
	date_default_timezone_set("America/Chicago");

	session_start();	
	
	include('config.php');
	
	$email_account="truckavailable@conardlogistics.com";
	$email_ps="7tb6ysbuD671";
			
	$show_dev_errors = 0;
     if($_SERVER['REMOTE_ADDR'] == '50.76.161.186' && $show_dev_errors > 0)
     {
         ini_set('display_errors', 1);
         ini_set('display_startup_errors', 1);
         error_reporting(E_ALL);
     }
     
     // MySQL connection
	$datasource = mysqli_connect($db_server, $db_username, $db_password, $db_name) or die("Could not connect to database server");
	
	
	//mail with attachment...
	function mrr_special_mail_attachment($mailto, $from_mail, $from_name, $replyto, $subject, $message, $filename, $path) 
	{
 		$result=0;
 		//file section...
 		$file = $path.$filename;
 		$file_size = filesize($file);
 		$handle = fopen($file, "r");
 		$content = fread($handle, $file_size);
 		fclose($handle);
 		$content = chunk_split(base64_encode($content));
 		$uid = md5(uniqid(time()));
 		
 		//file info collected, so sent the message.
 		$header = "From: ".$from_name." <".$from_mail.">\r\n";
 		$header .= "Reply-To: ".$replyto."\r\n";
 		$header .= "MIME-Version: 1.0\r\n";
 		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
 		$header .= "This is a multi-part message in MIME format.\r\n";
 		$header .= "--".$uid."\r\n";
 		$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
 		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
 		$header .= $message."\r\n\r\n";
 		$header .= "--".$uid."\r\n";
 		$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
 		$header .= "Content-Transfer-Encoding: base64\r\n";
 		$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
 		$header .= $content."\r\n\r\n";
 		$header .= "--".$uid."--";
 		
 		if (mail($mailto, $subject, "", $header)) 			$result=1;
 		
 		return $result;
	}
	
	
	//company storage.
	function get_logistics_truck_companies($id=0,$pre=0)
	{
		global $datasource;
		$tab="";	
		
		$cntr=0;
		$tab.="<table class='table table-bordered well'>";
		$tab.="
     		<tr>
     			<td valign='top'><b>ID</b></td>
     			<td valign='top'><b>Company</b></td>
     			<td valign='top'><b>E-Mail Address</b></td>
     			<td valign='top'><b>Phone</b></td>
     			<td valign='top'><b>Notes</b></td>
     			<td valign='top'><b>Username</b></td>
     			<td valign='top'><b>Password</b></td>     			
     			<td valign='top'><b>Status</b></td>	
     		</tr>
     	";
     	
     	$fill_act=" checked";
     	$fill_name="";
     	$fill_email="";
     	$fill_phone="";
     	$fill_notes="";
     	$linky="";
		
		// load any default vars specified in the database
     	$sql = "
     		select * 
     		from logistics_truck_companies 
     		where deleted=0 
     			".($id > 0 ? " and id='".(int) $id."'" : "")." 
     		order by company_name asc, 
     			id asc
     	";
     	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
     	while($row = mysqli_fetch_array($data)) 
     	{
     		if($pre==$row['id'])
     		{
     			$fill_name=trim($row['company_name']);
     			$fill_email=trim($row['company_email']);
     			$fill_phone=trim($row['company_phone']);
     			$fill_notes=trim($row['company_notes']);
     			$fill_user=trim($row['company_username']);
     			$fill_pass=trim($row['company_password']);
     			$fill_act="";
     			if($row['active'] > 0)		$fill_act=" checked";
     			
     			$linky="http://loads.conardlogistics.com/logistics_trucking_email_form.php?u=".$fill_user."&p=".$fill_pass."";
     		}
     		
     		$tab.="
     			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>
     				<td valign='top'><a href='logistics_trucking_admin.php?id=".$row['id']."'><b>".$row['id']."</b></a></td>
     				<td valign='top'>".$row['company_name']."</td>
     				<td valign='top'>".$row['company_email']."</td>
     				<td valign='top'>".$row['company_phone']."</td>
     				<td valign='top'>".$row['company_notes']."</td>
     				<td valign='top'>".$row['company_username']."</td>
     				<td valign='top'>".$row['company_password']."</td>
     				<td valign='top'>".($row['active'] > 0 ? "Active" : "&nbsp;")."</td>
     			</tr>
     		";
     		$cntr++;
     	}
     	$tab.="
     		<tr>
     			<td valign='top'><a href='logistics_trucking_admin.php?id=0'>New</a> <input type='hidden' name='id' id='id' value='".$pre."'></td>
     			<td valign='top'><input type='text' name='update_name' id='update_name' value='".$fill_name."' style='width:200px;' class='form-control'></td>
     			<td valign='top'><input type='text' name='update_email' id='update_email' value='".$fill_email."' style='width:200px;' class='form-control'></td>
     			<td valign='top'><input type='text' name='update_phone' id='update_phone' value='".$fill_phone."' style='width:100px;' class='form-control'></td>
     			<td valign='top'><input type='text' name='update_notes' id='update_notes' value='".$fill_notes."' style='width:200px;' class='form-control'></td>
     			<td valign='top'><input type='text' name='update_user' id='update_user' value='".$fill_user."' style='width:200px;' class='form-control'></td>
     			<td valign='top'><input type='text' name='update_pass' id='update_pass' value='".$fill_pass."' style='width:200px;' class='form-control'></td>
     			<td valign='top'><label><input type='checkbox' name='company_active' id='company_active' value='1'".$fill_act."> Active</label></td> 
     		</tr>
     		<tr>
     			<td valign='top'>&nbsp;</td>
     			<td valign='top' colspan='5' align='left'>".(trim($linky)!="" ? "<b>Customer should use this Link:</b> <a href='".$linky."' target='_blank'>".$linky."</a> " : "")."</td>  
     			<td valign='top' colspan='2' align='right'><button type='submit' name='update_company' id='update_company' class='btn btn-primary'><span class='glyphicon glyphicon-floppy-disk'></span> Update</button></td>     				
     		</tr>
     	";
		$tab.="</table>";
		
		
		return $tab;	
	}
	function find_logistics_truck_companies($email,$alt_email="")
	{
		global $datasource;
		
		$email=str_replace("\\","",$email);
		$email=str_replace("'","",$email);
		$email=trim($email);
		
		$email=str_replace("\\","",$email);
		$email=str_replace("'","",$email);
		$email=trim($email);
		
		$comp_id=0;
		$sql = "
     		select id 
     		from logistics_truck_companies 
     		where deleted=0      			 
     			".($alt_email!="" ? " and (company_email='".$email."' or company_notes='".$alt_email."')" : "and company_email='".$email."'")."
     		order by active asc,
     			company_name asc, 
     			id asc
     	";
     	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
     	if($row = mysqli_fetch_array($data)) 
     	{
     		$comp_id=$row['id'];		//exact match found.  use this one.
     	}
     	if($comp_id==0 && $email!="" && strlen($email) > 6 && trim($alt_email)=="")
     	{	//second chance...find email address from the same site...in case the name changes for the box it came from.  
     		//Ex: "@papetransfer.com" instead of "jjackoniski@papetransfer.com" if "jjackoniski" is no longer the one sending the email to Conard.
     		$poser=strpos($email,"@");
     		if($poser>0)	$email=substr($email,$poser);     		
     		
     		$sql = "
          		select id 
          		from logistics_truck_companies 
          		where deleted=0 
          			and company_email like '%".$email."' 
          		order by active asc,
          			company_name asc, 
          			id asc
          	";
          	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
          	if($row = mysqli_fetch_array($data)) 
          	{
          		$comp_id=$row['id'];
          	}
     	}    	
     	
		return $comp_id;	
	}
	
	
	//Email Storage...
	function input_form_logistics_truck_emails($id,$email,$subject,$body)
	{
		//global $datasource;
		$tab="";
		
		$tab.="<table class='table table-bordered well'>";		
		$tab.="
			<tr>
				<td valign='top' nowrap><b>Email ID:</b></td>
				<td valign='top'>".($id==0 ? "NEW" : "".$id."")." <input type='hidden' name='msg_id' id='msg_id' value=\"".$id."\"></td>
			</tr>
			<tr>
				<td valign='top' nowrap><b>E-Mail From:</b></td>
				<td valign='top'><input type='text' name='msg_email' id='msg_email' value=\"".trim($email)."\" style='width:400px;' class='form-control'></td>
			</tr>
			<tr>
				<td valign='top' nowrap><b>Subject Line:</b></td>
				<td valign='top'><input type='text' name='msg_sub' id='msg_sub' value=\"".trim($subject)."\" style='width:400px;' class='form-control'></td>
			</tr>
			<tr>
				<td valign='top' nowrap><b>Message Body:</b></td>
				<td valign='top'><textarea name='msg_body' id='msg_body' wrap='virtual' rows='20' cols='60' class='form-control'>".trim($body)."</textarea></td>
			</tr>
			<tr>
				<td valign='top' nowrap><a href='logistics_trucking_admin.php?msg_id=0'>Add New</a></td>
				<td valign='top'><button type='submit' name='save_msg' id='save_msg' class='btn btn-primary'><span class='glyphicon glyphicon-floppy-disk'></span> Save E-Mail Message</button></td>
			</tr>
		";
		$tab.="</table>";
		
		return $tab;
	}
	function set_logistics_truck_emails()
	{	//find all the emails that have been entered without the company ID set...and set them based on the email address.
		global $datasource;
		
		$sql = "
     		select *
     		from logistics_truck_emails 
     		where deleted=0 
     			and comp_id = 0
     			and email_address != ''
     		order by id asc
     	";		
     	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
     	while($row = mysqli_fetch_array($data)) 
     	{   
     		$email=trim($row['email_address']);
     		$comp_id=find_logistics_truck_companies($email);
     		if($comp_id > 0)
     		{
     			$sql = "
               		update logistics_truck_emails set
               			comp_id='".(int) $comp_id."' 
               		where id='".(int) $row['id']."' 
               	";
               	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );	
     		}
     		//availabletrucks=usflogistics.com@mail
     		//availabletrucks=usflogistics.com@mail
     		
     		if($comp_id==0 && substr_count(strtolower($email),"availabletrucks=usflogistics.com@mail") > 0)
     		{
     			$alt_email="availabletrucks=usflogistics.com@mail";	
     			$comp_id=find_logistics_truck_companies($email,$alt_email);
     			if($comp_id > 0)
     			{
     				$sql = "
               			update logistics_truck_emails set
               				comp_id='".(int) $comp_id."' 
               			where id='".(int) $row['id']."' 
               		";
               		$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );	
     			}
     		}
     	}	
	}
	function get_logistics_truck_emails($id=0,$proc=0)
	{
		global $datasource;
		$tab="<br><br>";	
		
		$cntr=0;
		$tab.="<table class='table table-bordered well'>";
		$tab.="
     		<tr>
     			<td valign='top'><b>ID</b></td>
     			<td valign='top'><b>Date</b></td>
     			<td valign='top'><b>Company</b></td>
     			<td valign='top'><b>Subject</b></td>
     			<td valign='top'><b>Processed</b></td>
     			<td valign='top'><b>Warning</b></td>
     			<td valign='top'><b>&nbsp;</b></td>  		
     		</tr>
     	";
     	
		
		// load any default vars specified in the database
     	$sql = "
     		select logistics_truck_emails.*,
     			 (select logistics_truck_companies.company_name from logistics_truck_companies where logistics_truck_companies.id=logistics_truck_emails.comp_id) as comp_name
     		from logistics_truck_emails 
     		where logistics_truck_emails.deleted=0 
     			".($proc >= 0 ? " and logistics_truck_emails.processed='".(int) $proc."'" : "")." 
     			".($id > 0 ? " and logistics_truck_emails.comp_id='".(int) $id."'" : "")." 
     			and logistics_truck_emails.comp_id > 0
     		order by logistics_truck_emails.linedate_added desc, 
     			logistics_truck_emails.id asc
     	";		
     	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
     	while($row = mysqli_fetch_array($data)) 
     	{     		
     		$processing=trim($row['email_msg']);
     		$processing=str_replace("\r","<br>",$processing);		$processing=str_replace(chr(10),"<br>",$processing);
     		$processing=str_replace("\n","<br>",$processing);		$processing=str_replace(chr(13),"<br>",$processing);
     		$processing=str_replace("\t","-----",$processing);	$processing=str_replace(chr(9),"-----",$processing);
     		
     		$tab.="
     			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>
     				<td valign='top'><a href='logistics_trucking_admin.php?id=".$row['comp_id']."&email_id=".$row['id']."'><b>".$row['id']."</b></a></td>
     				<td valign='top'>".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."</td>
     				<td valign='top'>".trim($row['comp_name'])."</td>     				
     				<td valign='top'>".trim($row['subject'])."</td>
     				<td valign='top'>".($row['processed'] > 0 ? "Done" : "&nbsp;")."</td>
     				<td valign='top'>".($row['dispatch_warning'] > 0 ? "<span style='color:#CC0000;'><b>Review Required</b></span>" : "&nbsp;")."</td>
     				<td valign='top'>&nbsp;</td>     			
     			</tr>
     			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>
     				<td valign='top'>&nbsp;</td>
     				<td valign='top' colspan='5'><div style='background-color:#FFFFFF; padding:10px; width:95%;".($row['processed'] > 0 ? " height:100px; overflow:auto;" : "")."'>".$processing."</div><br></td>
     				<td valign='top'>&nbsp;</td>     			
     			</tr>
     		";
     		$cntr++;
     	}
		$tab.="</table>";
				
		return $tab;	
	}
	function add_logistics_truck_emails($email,$sub,$body)
	{
		global $datasource;
		$comp_id=find_logistics_truck_companies(trim($email));
		
		$body=str_replace("48'","48ft",$body);				$body=str_replace("53'","53ft",$body);
		
		$sub=str_replace("\\","",$sub);					$sub=str_replace("'","",$sub);
		$body=str_replace("\\","",$body);					$body=str_replace("'","",$body);
		
		$email_id=0;
		$sql = "
     		insert into logistics_truck_emails 
     			(id,
     			linedate_added,
     			comp_id,
     			subject,
     			email_msg,     			
     			processed,
     			dispatch_warning,
     			deleted)
     		values
     			(NULL,
     			NOW(),
     			'".$comp_id."',
     			'".$sub."',
     			'".$body."',
     			0,
     			0,
     			0)
     	";
     	mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     	$email_id=mysqli_insert_id($datasource);
		return $email_id;	
	}
	
	
	//Truck Available Line Storage...
	function get_logistics_truck_listing_dated($date_from,$date_to,$comp_id=0,$id=0,$cust_view=0,$city="",$state="",$dest_city="",$dest_state="")
	{
		global $datasource;
		$tab="";	//<br><br>
		
		if(trim($date_from)=="")		$date_from=date("m/d/Y",time());
		if(trim($date_to)=="")		$date_to=date("m/d/Y",time());
		
		$cntr=0;
		$tab.="<table class='table table-bordered well'>";
		$tab.="
     		<tr>
     			<td valign='top'><b>Added</b></td>
     			<td valign='top'><b>Company</b></td>  
     			<td valign='top'><b>E-Mail</b></td> 
     			<td valign='top'><b>Phone</b></td>    			
     			<td valign='top'><b>Available Date</b></td>
     			
     			<td valign='top'><b>Location</b></td>
     			<td valign='top'><b>State</b></td>
     			<td valign='top'><b>Destination</b></td>
     			<td valign='top'><b>State</b></td>
     			<td valign='top'><b>Notes</b></td>	
     			".($cust_view ==0 ? "<td valign='top'><b>Delete</b></td>" : "")."
     		</tr>
     	";
     			/*
     			<td valign='top'><b>Truck</b></td>
     			<td valign='top'><b>Trailer</b></td>
     			
     			
     			<td valign='top'><b>ID</b></td>
     			<td valign='top'><b>EmailID</b></td>
     			
     			*/
     	
		
		// load any default vars specified in the database
     	$sql = "
     		select logistics_truck_listing.*,
     			 logistics_truck_companies.company_name as comp_name,
     			 logistics_truck_companies.company_email as comp_email,
     			 logistics_truck_companies.company_phone as comp_phone
     		from logistics_truck_listing 
     			left join logistics_truck_companies on logistics_truck_companies.id=logistics_truck_listing.comp_id
     		where logistics_truck_listing.deleted=0 
     			and logistics_truck_listing.linedate>='".date("Y-m-d",strtotime($date_from))." 00:00:00' 
     			and logistics_truck_listing.linedate<='".date("Y-m-d",strtotime($date_to))." 23:59:59'
     			".($comp_id > 0 || $cust_view > 0 ? " and logistics_truck_listing.comp_id='".(int) $comp_id."'" : "")." 
     			
     			".($id > 0 ? " and logistics_truck_listing.email_id='".(int) $id."'" : "")." 
     			
     			".(trim($city)!="" ? " and logistics_truck_listing.location like '%".sql_friendly(trim($city))."%'" : "")."
     			".(trim($state)!="" ? " and logistics_truck_listing.location_state like '%".sql_friendly(trim($state))."%'" : "")."
     			".(trim($dest_city)!="" ? " and logistics_truck_listing.dest_city like '%".sql_friendly(trim($dest_city))."%'" : "")."
     			".(trim($dest_state)!="" ? " and logistics_truck_listing.dest_state like '%".sql_friendly(trim($dest_state))."%'" : "")."     	
     					
     		order by logistics_truck_listing.linedate asc, 
     			logistics_truck_listing.id asc
     	";		
     	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
     	while($row = mysqli_fetch_array($data)) 
     	{     		
     		$tab.="
     			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>
     				<td valign='top'>".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."</td>
     				<td valign='top'>".trim($row['comp_name'])."</td>  
     				<td valign='top'>".trim($row['comp_email'])."</td> 
     				<td valign='top'>".trim($row['comp_phone'])."</td>    				
     				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate']))."</td>     				     				
     				
     				<td valign='top'>".trim($row['location'])."</td>
     				<td valign='top'>".trim($row['location_state'])."</td>
     				<td valign='top'>".trim($row['dest_city'])."</td>
     				<td valign='top'>".trim($row['dest_state'])."</td>
     				<td valign='top'>".trim($row['notes'])."</td>
     				".($cust_view ==0 ? "<td valign='top'><a href='logistics_trucking_email.php?delid=".$row['id']."'><span class='glyphicon glyphicon-trash'></span></a></td>" : "")."
     			</tr>
     		";
     				/*
     				 class='mrr_delete_access btn btn-danger btn-sm'
     				<button onclick='confirm_del(".$row['id'].");' class='mrr_delete_access btn btn-danger btn-sm'><span class='glyphicon glyphicon-trash'></span></button>
     				
     				<td valign='top'>".trim($row['truck_name'])."</td>
     				<td valign='top'>".trim($row['trailer_details'])."</td>
     				
     				
     				<td valign='top'><a href='logistics_trucking_admin.php?id=".$row['comp_id']."&email_id=".$row['email_id']."&line_id=".$row['id']."'><b>".$row['id']."</b></a></td>     				
     				<td valign='top'>".$row['email_id']."</td>
     				*/
     		$cntr++;
     	}
		$tab.="</table>";		//<br>Query:<br>".$sql ."<br>
				
		return $tab;	
	}
	
	function get_logistics_truck_listing($comp_id=0,$id=0)
	{
		global $datasource;
		$tab="<br><br>";	
		
		$cntr=0;
		$tab.="<table class='table table-bordered well'>";
		$tab.="
     		<tr>
     			<td valign='top'><b>ID</b></td>
     			<td valign='top'><b>EmailID</b></td>
     			<td valign='top'><b>Company</b></td>
     			<td valign='top'><b>Added</b></td>
     			<td valign='top'><b>Available Date</b></td>
     			
     			
     			<td valign='top'><b>Location</b></td>
     			<td valign='top'><b>Notes</b></td>	
     		</tr>
     	";
     		//<td valign='top'><b>Truck</b></td>
     		//<td valign='top'><b>Trailer</b></td>
     	
		
		// load any default vars specified in the database
     	$sql = "
     		select logistics_truck_listing.*,
     			 (select logistics_truck_companies.company_name from logistics_truck_companies where logistics_truck_companies.id=logistics_truck_listing.comp_id) as comp_name
     		from logistics_truck_listing 
     		where logistics_truck_listing.deleted=0 
     			".($comp_id > 0 ? " and logistics_truck_listing.comp_id='".(int) $comp_id."'" : "")." 
     			".($id > 0 ? " and logistics_truck_listing.email_id='".(int) $id."'" : "")." 
     		order by logistics_truck_listing.linedate_added desc, 
     			logistics_truck_listing.id asc
     	";
     	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
     	while($row = mysqli_fetch_array($data)) 
     	{     		
     		$tab.="
     			<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'>
     				<td valign='top'><a href='logistics_trucking_admin.php?id=".$row['comp_id']."&email_id=".$row['email_id']."&line_id=".$row['id']."'><b>".$row['id']."</b></a></td>     				
     				<td valign='top'>".$row['email_id']."</td>
     				<td valign='top'>".trim($row['comp_name'])."</td>
     				<td valign='top'>".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."</td>
     				<td valign='top'>".date("m/d/Y H:i",strtotime($row['linedate']))."</td>     				     				
     				
     				
     				<td valign='top'>".trim($row['location'])."</td>
     				<td valign='top'>".trim($row['notes'])."</td>
     			</tr>
     		";
     			//<td valign='top'>".trim($row['truck_name'])."</td>
     			//<td valign='top'>".trim($row['trailer_details'])."</td>
     		$cntr++;
     	}
		$tab.="</table>";
				
		return $tab;	
	}
	function add_logistics_truck_listing($comp_id,$email_id,$dater,$truck,$trailer,$local,$notes,$state="",$dest_city="",$dest_state="")
	{
		global $datasource;
		
		$truck=str_replace("\\","",$truck);				$truck=str_replace("'","",$truck);
		$trailer=str_replace("\\","",$trailer);				$trailer=str_replace("'","",$trailer);
		$local=str_replace("\\","",$local);				$local=str_replace("'","",$local);
		$notes=str_replace("\\","",$notes);				$notes=str_replace("'","",$notes);
		
		$state=str_replace("\\","",$state);				$state=str_replace("'","",$state);
		$dest_city=str_replace("\\","",$dest_city);			$dest_city=str_replace("'","",$dest_city);
		$dest_state=str_replace("\\","",$dest_state);		$dest_state=str_replace("'","",$dest_state);
		
		$line_id=0;
		$sql = "
     		insert into logistics_truck_listing 
     			(id,
     			comp_id,
     			email_id,
     			linedate_added,
     			linedate,
     			truck_name,
     			trailer_details,
     			location,
     			notes,
     			location_state,
     			dest_city,
     			dest_state,
     			deleted)
     		values
     			(NULL,
     			'".$comp_id."',
     			'".$email_id."',
     			NOW(),
     			'".date("Y-m-d H:i",strtotime($dater))."',
     			'".trim($truck)."',
     			'".trim($trailer)."',
     			'".trim($local)."',
     			'".trim($notes)."',
     			'".trim($state)."',
     			'".trim($dest_city)."',
     			'".trim($dest_state)."',
     			0)
     	";
     	mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     	$line_id=mysqli_insert_id($datasource);
		return $line_id;	
	}
	
	
	//processing functions...
	function process_waiting_logistics_truck_emails($id=0)
	{
		global $datasource;
		
		$rep="";
			
		$sql = "
     		select logistics_truck_emails.*,
     			 (select logistics_truck_companies.company_name from logistics_truck_companies where logistics_truck_companies.id=logistics_truck_emails.comp_id) as comp_name
     		from logistics_truck_emails 
     		where logistics_truck_emails.deleted=0 
     			and logistics_truck_emails.processed=0
     			and logistics_truck_emails.comp_id > 0
     			".($id > 0 ? " and logistics_truck_emails.comp_id='".(int) $id."'" : "")." 
     		order by logistics_truck_emails.linedate_added desc, 
     			logistics_truck_emails.id asc
     	";
     	$data = mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>" );
     	while($row = mysqli_fetch_array($data)) 
     	{     		
     		$processing=trim($row['email_msg']);
     		$processing=str_replace("\r","<br>",$processing);		$processing=str_replace(chr(10),"<br>",$processing);
     		$processing=str_replace("\n","<br>",$processing);		$processing=str_replace(chr(13),"<br>",$processing);
     		$processing=str_replace("\t","-----",$processing);	$processing=str_replace(chr(9),"-----",$processing);
     		
     		$processing=str_replace("48'","48ft",$processing);
     		$processing=str_replace("53'","53ft",$processing);
     		
     		$rep.="
     			<div style='text-align:left; width:100%;'>
     			<br>EmailID: <a href='logistics_trucking_admin.php?id=".$row['comp_id']."&email_id=".$row['id']."'><b>".$row['id']."</b></a>
     			<br>Added: ".date("m/d/Y H:i:s",strtotime($row['linedate_added']))."
     			<br>Company: ".trim($row['comp_name'])."
     			<br>Processed: ".($row['processed'] > 0 ? "Complete" : "Waiting")." | Special: ".($row['dispatch_warning'] > 0 ? "<span style='color:#CC0000;'><b>Review Required</b></span>" : "N/A")."
     			<br>Subject: ".trim($row['subject'])."
     		";     		
     		
     		$process=$processing;
     		$test_lines="";
     		
     		$mode=$row['comp_id'];
     		if($mode==1)
     		{
     			$cntr=0;
     			
     			$pos1=0;
     			$pos2=strpos($process,"Jill J.",$pos1);
     			
     			$process=substr($process,$pos1,($pos2 - $pos1));
     			
     			$cur_date=date("m/d/Y",time());		//always today
     			$cntr=0;
     			     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim($lines[$i]);
     				if($this_line!="")
     				{
     					$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     						
     					if(substr_count($this_line,"(empty now)") > 0)
     					{
     						$this_line=trim(str_replace("(empty now)","",$this_line));
     						$note="(Empty Now)";	
     					}
     					
     					$local=trim($this_line);
     					   						
     					$test_lines.="<br>".$cur_date." | T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
     					
     					$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$cur_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}		     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==2)
     		{
     			$cntr=0;
     			
     			$process=str_replace("Monday","",$process);
     			$process=str_replace("Tuesday","",$process);
     			$process=str_replace("Wednesday","",$process);
     			$process=str_replace("Thursday","",$process);
     			$process=str_replace("Friday","",$process);
     			$process=str_replace("Saturday","",$process);
     			$process=str_replace("Sunday","",$process);
     			     			
     			$pos1=0;
     			$pos2=strpos($process,"Best Regards,",$pos1);
     			if($pos2 == 0)		$pos2=strpos($process,"Adam Hyatt",$pos1);
     			if($pos2 == 0)		$pos2=strpos($process,"Paschall Truck Lines, Inc.",$pos1);
     			
     			$process=substr($process,$pos1,($pos2 - $pos1));
     			
     			$cur_date=date("m/d/Y",time());		//always today
     			$cntr=0;
     			     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim($lines[$i]);
     				if($this_line!="")
     				{
     					$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time=" 00:00:00";
     					
     					//extract time
     					if(substr_count($this_line," 0") > 0)
     					{
     						$pos3=strpos($this_line," 0");
     						if($pos3 > 0)
     						{
     							$time=substr($this_line,$pos3).":00";
     							$local=substr($this_line,0,$pos3);	
     						}
     					}
     					elseif(substr_count($this_line," 1") > 0)
     					{
     						$pos3=strpos($this_line," 1");
     						if($pos3 > 0)
     						{
     							$time=substr($this_line,$pos3).":00";
     							$local=substr($this_line,0,$pos3);	
     						}
     					}
     					else
     					{     					
     						$local=trim($this_line);
     					}
     					
     					$use_date=$cur_date.$time;
     										
     					$test_lines.="<br>".$use_date." | T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
     					
     					$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}	
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==26)
     		{	//same company that goes with mode==3 below, but written differently.
     			
     		}
     		elseif($mode==3)
     		{	
     			$cntr=0;
     			
     			$pos0=strpos($process,"Current list");
     			$pos1=strpos($process,"Monday");
     			$pos2=strpos($process,"Tuesday");
     			$pos3=strpos($process,"Wednesday");
     			$pos4=strpos($process,"Thursday");
     			$pos5=strpos($process,"Friday");
     			$pos6=strpos($process,"Saturday");
     			$pos7=strpos($process,"Sunday");
     			
     			if($pos1 > 0 && substr_count($process,"Current list as of Monday ")> 0)		{		$process=str_replace("Current list as of Monday","",$process);		$pos1=strpos($process,"Monday");		$pos0=$pos1;	}
     			if($pos2 > 0 && substr_count($process,"Current list as of Tuesday ")> 0)		{		$process=str_replace("Current list as of Tuesday","",$process);		$pos1=strpos($process,"Tuesday");		$pos0=$pos2;	}
     			if($pos3 > 0 && substr_count($process,"Current list as of Wednesday ")> 0)		{		$process=str_replace("Current list as of Wednesday","",$process);	$pos1=strpos($process,"Wednesday");	$pos0=$pos3;	}
     			if($pos4 > 0 && substr_count($process,"Current list as of Thursday ")> 0)		{		$process=str_replace("Current list as of Thursday","",$process);		$pos1=strpos($process,"Thursday");		$pos0=$pos4;	}
     			if($pos5 > 0 && substr_count($process,"Current list as of Friday ")> 0)		{		$process=str_replace("Current list as of Friday","",$process);		$pos1=strpos($process,"Friday");		$pos0=$pos5;	}
     			if($pos6 > 0 && substr_count($process,"Current list as of Saturday ")> 0)		{		$process=str_replace("Current list as of Saturday","",$process);		$pos1=strpos($process,"Saturday");		$pos0=$pos6;	}
     			if($pos7 > 0 && substr_count($process,"Current list as of Sunday ")> 0)		{		$process=str_replace("Current list as of Sunday","",$process);		$pos1=strpos($process,"Sunday");		$pos0=$pos7;	}
     			
     			$pos00=strpos($process,"________________________________",$pos0); 
     			if($pos00==0)		$pos00=strpos($process,"Tommy Chism",$pos0); 
     			if($pos00==0)		$pos00=strpos($process,"Mid South Transport Inc",$pos0); 	
     			
     			$process=substr($process,$pos0,($pos00 - $pos0));
     			
     			$process=str_replace("Monday","",$process);
     			$process=str_replace("Tuesday","",$process);
     			$process=str_replace("Wednesday","",$process);
     			$process=str_replace("Thursday","",$process);
     			$process=str_replace("Friday","",$process);
     			$process=str_replace("Saturday","",$process);
     			$process=str_replace("Sunday","",$process);
     			
     			$cur_date=date("m/d/Y",time());		//default to today...but cna change in the list.
     			     			     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/")==2)
     				{
     					$cur_date=date("m/d/Y",strtotime($this_line));
     				}
     				elseif($this_line!="")
     				{
     					$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time=" 00:00:00";
     					
     					//extract note...
     					if(substr_count($this_line,"---") > 0)
     					{
     						$pos3n=strpos($this_line,"---");
     						if($pos3n > 0)
     						{
     							$note=substr($this_line,$pos3n);	
     							$this_line=str_replace($note,"",$this_line);
     						}
     					}
     					
     					//extract time
     					if(substr_count($this_line,"@") > 0)
     					{
     						$pos3t=strpos($this_line,"@");
     						if($pos3t > 0)
     						{
     							$time=trim(substr($this_line,($pos3t+2)));
     							$this_line=str_replace("@ ".$time,"",$this_line);
     							
     							$time=str_replace("00",":00",$time);
     							$time=str_replace("30",":30",$time);
     							$time=str_replace("15",":15",$time);
     							$time=str_replace("45",":45",$time);
     							
     							$time.=":00";	
     							if(strpos($time,":") ==0 || substr_count($time,":")> 2)		$time=substr($time,1);
     						}
     					}
     					
     					$local=trim($this_line);
     					
     					
     					$use_date=$cur_date." ".$time;
     										
     					$test_lines.="<br>".$use_date." | T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
     					
     					$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}     			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==4)
     		{
     			$cntr=0;
     			
     			$pos0=strpos($process,"AVAILABLE TRUCKS");
     			$pos1=strpos($process,"CALL US AT");
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("AVAILABLE TRUCKS","",$process);
     			
     			$process=str_replace("48ft REEFER EMPTY IN","",$process);
     			$process=str_replace("53ft REEFER EMPTY IN","",$process);
     			
     			$process=str_replace("Monday,","",$process);
     			$process=str_replace("Tuesday,","",$process);
     			$process=str_replace("Wednesday,","",$process);
     			$process=str_replace("Thursday,","",$process);
     			$process=str_replace("Friday,","",$process);
     			$process=str_replace("Saturday,","",$process);
     			$process=str_replace("Sunday,","",$process);
     			
     			
     			$cur_date=date("m/d/Y",time());		//default to today...but can change in the list.
     			     			     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/")==2)
     				{
     					$cur_date=date("m/d/Y",strtotime($this_line));
     				}
     				elseif($this_line!="")
     				{
     					$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";
     					     					
     					$local=trim($this_line);
     					     					
     					$use_date=$cur_date." ".$time;
     										
     					$test_lines.="<br>".$use_date." | T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
     					
     					$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}
     			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==5)
     		{
     			$cntr=0;
     			
     			$pos0=strpos($process,"Notes");
     			//$pos1=strpos($process,"CALL US AT");
     			
     			$process=substr($process,($pos0 + 5));
     			
     			$cur_date=date("m/d/Y",time());		//default to today...but can change in the list.
     			
     			$split_year=0;
     			if(substr_count($process,"1/") > 0 && substr_count($process,"12/") > 0 && substr_count($process,"@") > 0)		$split_year=1;
     			$process=str_replace("@ AM","@ 00:00:00",$process);
     			$process=str_replace("@ PM","@ 12:00:00",$process);
     			     			     			
     			$sub_cntr=0;
     			$truck="(Misc. Truck)";
				$trailer="";
				$local="";
				$note="";
				$time="00:00:00";
     			     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if($this_line!="")
     				{
          				if($sub_cntr==0)
          				{	//location of trailer/truck
          					$local=trim($this_line);	
          					$sub_cntr++;
          				}
          				elseif($sub_cntr==1)
          				{    //time     				
               				if(substr_count($this_line,"/") > 0 && substr_count($this_line,"@") > 0)
               				{
               					$time="00:00:00";			if(substr_count($this_line,"@ 12:00:00") > 0)		$time="12:00:00";	
               					
               					$this_line=str_replace("@ 00:00:00","",$this_line);
               					$this_line=str_replace("@ 12:00:00","",$this_line);
               					
               					$cur_year=date("Y",time());		
               					if($split_year > 0 && substr_count($this_line,"1/") > 0)	{	$cur_year++;		$split_year=0;	}
               					
               					$this_line=trim($this_line);
               					//$trailer="".$this_line."/".$cur_year."";
               					
               					$cur_date=date("m/d/Y",strtotime($this_line."/".$cur_year));
               					$sub_cntr++;
               				}               				
          				}
          				elseif($sub_cntr==2)
          				{	//Desired location...to go...
          					$note="Desired Destination: ".trim($this_line)."";
          					$sub_cntr++;
          				}
          				elseif($sub_cntr==3)
          				{	//Notes (trailer size)
          					$note.=" - ".trim($this_line)."ft";
          					$sub_cntr++;
          				}
          				
          				if($sub_cntr==4)
          				{
          					$use_date=$cur_date." ".$time;
          					$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          					
          					$add_id=0;
     						$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     						if($add_id > 0)		$cntr++;
          					          					
          					
          					$truck="(Misc. Truck)";
     						$trailer="";
     						$local="";
     						$note="";
     						$time="00:00:00";
          					
          					$sub_cntr=0;
          				}
     				}
     			}
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==6)
     		{
     			$cntr=0;
     			
     			$process=str_replace("1st","1/",$process);
     			$process=str_replace("2nd","2/",$process);
     			$process=str_replace("3rd","3/",$process);
     			$process=str_replace("4th","4/",$process);
     			$process=str_replace("5th","5/",$process);
     			$process=str_replace("6th","6/",$process);
     			$process=str_replace("7th","7/",$process);
     			$process=str_replace("8th","8/",$process);
     			$process=str_replace("9th","9/",$process);
     			$process=str_replace("0th","0/",$process);
     			
     			$process=str_replace("Monday,","",$process);
     			$process=str_replace("Tuesday,","",$process);
     			$process=str_replace("Wednesday,","",$process);
     			$process=str_replace("Thursday,","",$process);
     			$process=str_replace("Friday,","",$process);
     			$process=str_replace("Saturday,","",$process);
     			$process=str_replace("Sunday,","",$process);
     			
     			$process=str_replace("January ","01/",$process);
     			$process=str_replace("February ","02/",$process);
     			$process=str_replace("March ","03/",$process);
     			$process=str_replace("April ","04/",$process);
     			$process=str_replace("May ","05/",$process);
     			$process=str_replace("June ","06/",$process);
     			$process=str_replace("July ","07/",$process);
     			$process=str_replace("August ","08/",$process);
     			$process=str_replace("September ","09/",$process);
     			$process=str_replace("October ","10/",$process);
     			$process=str_replace("November ","11/",$process);
     			$process=str_replace("December ","12/",$process);
     			
     			$process=str_replace("SOLO/DRY VAN","solo/dry van",$process);
     			     			
     			$pos0=strpos($process,"PLEASE REPLY ONLY TO: VANS@ZTLINC.COM");
     			$pos1=strpos($process,"ASK ABOUT OUR 24 HOUR ONLINE TRACKING",$pos0);
     			
     			$process=substr($process,($pos0 + 37),($pos1 - $pos0));
     			
     			$process=str_replace("ASK ABOUT OUR 24 HOUR ONLINE TRACKING","",$process);
     			
     			$split_year=0;
     			if(substr_count($process,"01/") > 0 && substr_count($process,"12/") > 0 && substr_count($process,"@") > 0)		$split_year=1;
     			$cur_year=date("Y",time());	     			
     			
     			
     			$cur_date=date("m/d/Y",time());		//default to today...but can change in the list.
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/")==2 && substr_count($this_line,"solo/dry van")==0)
     				{     						
               			if($split_year > 0 && substr_count($this_line,"01/") > 0)	{	$cur_year++;		$split_year=0;	}
               			
               			$this_line=str_replace(":",$cur_year,$this_line);
               			
     					$cur_date=date("m/d/Y",strtotime($this_line));
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";          				
          				
          				//extract note...
     					if(substr_count($this_line,"solo/dry van") > 0)
     					{
     						$pos3n=strpos($this_line,"solo/dry van");
     						if($pos3n > 0)
     						{
     							$note=trim(substr($this_line,$pos3n));	
     							$this_line=str_replace($note,"",$this_line);
     						}
     					}
          				
          				$local=trim($this_line);	
          				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}     	     			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==7)
     		{
     			$cntr=0;
     			
     			$pos0=strpos($process,"TIME");
     			$pos1=strpos($process,"Thank you.",$pos0);
     			$pos1+=6;
     			
     			$process=substr($process,($pos0 + 4),($pos1-$pos0));
     			
     			$process=str_replace("Thank you.","",$process);
     			$process=str_replace("empty-","0000eta empty-",$process);
     			
     			
     			$cur_date=date("m/d/Y",time());		//default to today...but can change in the list.
     			
     			$split_year=0;
     			if(substr_count($process,"-Jan") > 0 && substr_count($process,"-Dec") > 0)		$split_year=1;
     			$year=date("Y",time());
     			     			     			     			
     			$sub_cntr=0;
     			$truck="(Misc. Truck)";
				$trailer="";
				$local="";
				$note="";
				$time="00:00:00";
     			     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if($this_line!="")
     				{
          				if($sub_cntr==0 || substr_count($this_line,"-Jan") > 0 || substr_count($this_line,"-Feb") > 0 || substr_count($this_line,"-Mar") > 0 || substr_count($this_line,"-Apr") > 0
          					 || substr_count($this_line,"-May") > 0 || substr_count($this_line,"-Jun") > 0 || substr_count($this_line,"-Jul") > 0 || substr_count($this_line,"-Aug") > 0
          					  || substr_count($this_line,"-Sep") > 0 || substr_count($this_line,"-Oct") > 0 || substr_count($this_line,"-Nov") > 0 || substr_count($this_line,"-Dec") > 0
          					)
          				{	//date
          					//$test_date=trim($this_line);	
          					$month=0;
          					$day=0;          					
          					if(substr_count($this_line,"-Jan") > 0 && $split_year > 0)	{	$year=(int) $year + 1;		$split_year=0;		}
          					
          					if(substr_count($this_line,"-Jan") > 0)		{	$month=1;		$this_line=str_replace("-Jan","",$this_line);	}          					
          					if(substr_count($this_line,"-Feb") > 0)		{	$month=2;		$this_line=str_replace("-Feb","",$this_line);	}
          					if(substr_count($this_line,"-Mar") > 0)		{	$month=3;		$this_line=str_replace("-Mar","",$this_line);	}
          					if(substr_count($this_line,"-Apr") > 0)		{	$month=4;		$this_line=str_replace("-Apr","",$this_line);	}
          					if(substr_count($this_line,"-May") > 0)		{	$month=5;		$this_line=str_replace("-May","",$this_line);	}
          					if(substr_count($this_line,"-Jun") > 0)		{	$month=6;		$this_line=str_replace("-Jun","",$this_line);	}
          					if(substr_count($this_line,"-Jul") > 0)		{	$month=7;		$this_line=str_replace("-Jul","",$this_line);	}
          					if(substr_count($this_line,"-Aug") > 0)		{	$month=8;		$this_line=str_replace("-Aug","",$this_line);	}
          					if(substr_count($this_line,"-Sep") > 0)		{	$month=9;		$this_line=str_replace("-Sep","",$this_line);	}
          					if(substr_count($this_line,"-Oct") > 0)		{	$month=10;	$this_line=str_replace("-Oct","",$this_line);	}
          					if(substr_count($this_line,"-Nov") > 0)		{	$month=11;	$this_line=str_replace("-Nov","",$this_line);	}
          					if(substr_count($this_line,"-Dec") > 0)		{	$month=12;	$this_line=str_replace("-Dec","",$this_line);	}
          					
          					$this_line=str_replace("-Sept","",$this_line);	//just in case...
          					          					
          					$day=trim($this_line);          					
          					
          					$cur_date=$month."/".$day."/".$year;
          					$sub_cntr++;
          				}
          				elseif($sub_cntr==1)
          				{    //location   				
               				$local=trim($this_line);	
          					$sub_cntr++;            				
          				}
          				elseif($sub_cntr==2)
          				{	//Desired location...notes...
          					$note="".trim($this_line)."";
          					$sub_cntr++;
          				}
          				elseif($sub_cntr==3 || substr_count($this_line,"unit") > 0 || substr_count($this_line,"UNIT") > 0)
          				{	//trailer name
          					$trailer="".trim($this_line)."";
          					$sub_cntr++;
          				}
          				elseif($sub_cntr==4 || substr_count($this_line,"eta") > 0 || substr_count($this_line,"ETA") > 0 || substr_count($this_line,"appt") > 0 || is_numeric($this_line))
          				{	//time
          					if(strlen(trim($this_line)) < 4)		$this_line="0".trim($this_line);		//missing leading time digit
          					if(strlen(trim($this_line)) > 4)	
          					{	//has extra spacing...
          						$pos3=strpos(trim($this_line)," ");
          						if($pos3==0)		$pos3=strpos(trim($this_line),"eta");
          						if($pos3==0)		$pos3=strpos(trim($this_line),"ETA");
          						if($pos3==0)		$pos3=strpos(trim($this_line),"appt");
          						if($pos3==0)		$pos3=strpos(trim($this_line),"APPT");
          						
          						if($pos3==3)		$this_line="0".trim($this_line);
          					}
          					
          					$hr=substr(trim($this_line),0,2);
          					$min=substr(trim($this_line),2,2);
          					
          					if(strlen(trim($this_line)) > 4)	
          					{
          						$note.=" -- ".trim($this_line);		//add to notes
          						$note=str_replace("0000eta ","",$note);
          					}     					
          					$time="".$hr.":".$min.":00";
          					$sub_cntr++;
          				}
          				
          				if($sub_cntr==5)
          				{
          					$use_date=$cur_date." ".$time;
          					$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          					
          					$add_id=0;
     						$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     						if($add_id > 0)		$cntr++;
          					          					
          					
          					$truck="(Misc. Truck)";
     						$trailer="";
     						$local="";
     						$note="";
     						$time="00:00:00";
          					
          					$sub_cntr=0;
          				}
     				}
     			}     			
     			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==8)
     		{
     			$cntr=0;
     			     			
     			$pos0=0;
     			$pos1=strpos($process,"Ronda Hancock",$pos0);
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("Ronda Hancock","",$process);
     			$process=str_replace("St ","St.",$process);
     			
     			
     			$split_year=0;
     			if(substr_count($process,"1/") > 0 && substr_count($process,"12/") > 0)		$split_year=1;
     			$cur_year=date("Y",time());	 
     			
     			$cur_date=date("m/d/Y",time());		//default to today...but can change in the list.
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/") > 0)
     				{     						
               			if($split_year > 0 && substr_count($this_line,"1/") > 0 && substr_count($this_line,"11/") == 0)	{	$cur_year++;		$split_year=0;	}
               			
               			$this_line=trim($this_line)."/".$cur_year;
               			
     					$cur_date=date("m/d/Y",strtotime($this_line));
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";      
     					
     					//extract note...
     					if(substr_count(trim($this_line)," ") > 1)
     					{
     						$pos3=strpos(trim($this_line)," ");
     						$pos4=strpos(trim($this_line)," ",($pos3+2));
     						if($pos4 > 0)
     						{
     							$note=substr(trim($this_line),$pos4);	
     							if(strlen(trim($note)) > 2)
     							{
     								$this_line=str_replace($note,"",$this_line);
     							}
     							else
     							{
     								$note="";	
     							}
     						}	
     					}
     					
          				$local=trim($this_line);	
          				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}     			
     			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==9)
     		{
     			$cntr=0;
     			     			
     			$pos0=0;
     			$pos1=strpos($process,"Richard & Shannon Camden",$pos0);
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("Richard & Shannon Camden","",$process);
     			$process=str_replace("ST ","ST.",$process);
     			
     			
     			$split_year=0;
     			if(substr_count($process,"1/") > 0 && substr_count($process,"12/") > 0)		$split_year=1;
     			$cur_year=date("Y",time());	 
     			
     			$cur_date=date("m/d/Y",time());		//default to today...but can change in the list.
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/") > 0)
     				{     						
               			if($split_year > 0 && substr_count($this_line,"1/") > 0 && substr_count($this_line,"11/") == 0)	{	$cur_year++;		$split_year=0;	}
               			
               			$this_line=trim($this_line)."/".$cur_year;
               			
     					$cur_date=date("m/d/Y",strtotime($this_line));
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";      
     					
     					//extract note...
     					if(substr_count(trim($this_line)," ") > 1)
     					{
     						$pos3=strpos(trim($this_line)," ");
     						$pos4=strpos(trim($this_line)," ",($pos3+2));
     						if($pos4 > 0)
     						{
     							$note=substr(trim($this_line),$pos4);	
     							if(strlen(trim($note)) > 2)
     							{
     								$this_line=str_replace($note,"",$this_line);
     							}
     							else
     							{
     								$note="";	
     							}
     						}	
     					}
     					
          				$local=trim($this_line);	
          				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}   	     			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==10)
     		{
     			$cntr=0;
     			
     			$wk_day=date("w",time());
     			$cur_date=date("m/d/Y",time());
     			     			    			     			
     			$pos0=0;
     			$pos1=strpos($process,"Monday",5);		$off1=""; 
     			$pos2=strpos($process,"Tuesday",5);	$off2=""; 
     			$pos3=strpos($process,"Wednesday",5);	$off3=""; 
     			$pos4=strpos($process,"Thursday",5);	$off4=""; 
     			$pos5=strpos($process,"Friday",5);		$off5=""; 
     			$pos6=strpos($process,"Saturday",5);	$off6=""; 
     			$pos7=strpos($process,"Sunday",5);		$off7="";      			
     			
     			if($wk_day==0)	{	$off1="+1 day";	$off2="+2 day";	$off3="+3 day";	$off4="+4 day";	$off5="+5 day";	$off6="+6 day";	$off7="+7 day";	} 
     			if($wk_day==1)	{	$off1="+1 hour";	$off2="+1 day";	$off3="+2 day";	$off4="+3 day";	$off5="+4 day";	$off6="+5 day";	$off7="+6 day";	} 
     			if($wk_day==2)	{	$off1="-1 day";	$off2="+1 hour";	$off3="+1 day";	$off4="+2 day";	$off5="+3 day";	$off6="+4 day";	$off7="+5 day";	} 
     			if($wk_day==3)	{	$off1="-2 day";	$off2="-1 day";	$off3="+1 hour";	$off4="+1 day";	$off5="+2 day";	$off6="+3 day";	$off7="+4 day";	} 
     			if($wk_day==4)	{	$off1="-3 day";	$off2="-2 day";	$off3="-1 day";	$off4="+1 hour";	$off5="+1 day";	$off6="+2 day";	$off7="+3 day";	} 
     			if($wk_day==5)	{	$off1="-4 day";	$off2="-3 day";	$off3="-2 day";	$off4="-1 day";	$off5="+1 hour";	$off6="+1 day";	$off7="+2 day";	} 
     			if($wk_day==6)	{	$off1="-5 day";	$off2="-4 day";	$off3="-3 day";	$off4="-2 day";	$off5="-1 day";	$off6="+1 hour";	$off7="+1 day";	} 
     			
     			if($pos1 > 0 && $pos0==0)	{	$pos0=$pos1;	}
     			if($pos2 > 0 && $pos0==0)	{	$pos0=$pos2;	}
     			if($pos3 > 0 && $pos0==0)	{	$pos0=$pos3;	}
     			if($pos4 > 0 && $pos0==0)	{	$pos0=$pos4;	}
     			if($pos5 > 0 && $pos0==0)	{	$pos0=$pos5;	}
     			if($pos6 > 0 && $pos0==0)	{	$pos0=$pos6;	}
     			if($pos7 > 0 && $pos0==0)	{	$pos0=$pos7;	}
     			     			     			
     			$process=str_replace("Monday",   date("m/d/Y",strtotime("".$off1."",strtotime($cur_date))),$process);
     			$process=str_replace("Tuesday",  date("m/d/Y",strtotime("".$off2."",strtotime($cur_date))),$process);
     			$process=str_replace("Wednesday",date("m/d/Y",strtotime("".$off3."",strtotime($cur_date))),$process);
     			$process=str_replace("Thursday", date("m/d/Y",strtotime("".$off4."",strtotime($cur_date))),$process);
     			$process=str_replace("Friday",   date("m/d/Y",strtotime("".$off5."",strtotime($cur_date))),$process);
     			$process=str_replace("Saturday", date("m/d/Y",strtotime("".$off6."",strtotime($cur_date))),$process);
     			$process=str_replace("Sunday",   date("m/d/Y",strtotime("".$off7."",strtotime($cur_date))),$process);
     			
     			     			
     			$pos00=strpos($process,"Blake Bradley",$pos0);
     			
     			$process=substr($process,$pos0,($pos00 - $pos0));
     			
     			$process=str_replace("Blake Bradley","",$process);
     			$process=str_replace("Thanks!","",$process);
     			$process=str_replace("St ","St.",$process);
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/") > 1)
     				{    
     					$cur_date=trim($this_line);
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";      
     					
     					//extract note...
     					if(substr_count(trim($this_line)," ") > 1)
     					{
     						$pos3=strpos(trim($this_line)," ");
     						$pos4=strpos(trim($this_line)," ",($pos3+2));
     						if($pos4 > 0)
     						{
     							$note=substr(trim($this_line),$pos4);	
     							if(strlen(trim($note)) > 2)
     							{
     								$this_line=str_replace($note,"",$this_line);
     							}
     							else
     							{
     								$note="";	
     							}
     						}	
     					}
     					
          				$local=trim($this_line);	
          				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}   	     			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}     		
     		elseif($mode==11)
     		{	//delta freight ....attachments...always flag this one.
     			$cntr=0;
     			     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     			else
     			{
     				$sql = "update logistics_truck_emails set processed=2,dispatch_warning=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			}
     		}
     		elseif($mode==12)
     		{
     			$cntr=0;     			
     			
     			$pos0=0;
     			$pos1=strpos($process,"T.J. Pierce");
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("T.J. Pierce","",$process);
     			$process=str_replace("--","",$process);
     			     			     			
     			$cur_date=date("m/d/Y",time());		//default to today...but can change in the list.
     			
     			$split_year=0;
     			if(substr_count($process,"1/") > 0 && substr_count($process,"12/") > 0 && substr_count($process,"@") > 0)		$split_year=1;
     			$cur_year=date("Y",time());
     			    		  			
     			     			     			
     			$sub_cntr=0;
     			$truck="(Misc. Truck)";
				$trailer="";
				$local="";
				$note="";
				$time="00:00:00";
     			     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if($this_line!="")
     				{
          				if($sub_cntr==0)
          				{	//truck name
          					$truck=trim($this_line);	
          					$sub_cntr++;
          				}
          				elseif($sub_cntr==1)
          				{	//location
          					$local=trim($this_line);	
          					$sub_cntr++;
          				}
          				elseif($sub_cntr==2)
          				{    //date  				
               				if(substr_count($this_line,"/") > 0 && is_numeric(trim(str_replace("/","",$this_line))))
               				{     
               					if($split_year > 0 && substr_count($this_line,"1/") > 0)	{	$cur_year++;		$split_year=0;	}
               					
               					$cur_date=date("m/d/Y",strtotime(trim($this_line)."/".$cur_year));
               					$sub_cntr++;
               				}               				
          				}
          				elseif($sub_cntr==3)
          				{	//Desired location...to go...
          					$note="Destination: ".trim($this_line)."";
          					$sub_cntr++;
          				}
          				
          				
          				if($sub_cntr==4)
          				{
          					$use_date=$cur_date." ".$time;
          					$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          					
          					$add_id=0;
     						$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     						if($add_id > 0)		$cntr++;
          					       					
          					
          					$truck="(Misc. Truck)";
     						$trailer="";
     						$local="";
     						$note="";
     						$time="00:00:00";
          					
          					$sub_cntr=0;
          				}
     				}
     			}			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==13)
     		{
     			$cntr=0;
     			    			
     			$wk_day=date("w",time());
     			$cur_date=date("m/d/Y",time());
     			
     			$pos0=strpos($process,"Today");
     			$pos00=strpos($process,"Thanks");
     			$process=substr($process,$pos0,($pos00 - $pos0));
     			     			    			     		
     			$pos1=strpos($process,"Monday",5);		$off1=""; 
     			$pos2=strpos($process,"Tuesday",5);	$off2=""; 
     			$pos3=strpos($process,"Wednesday",5);	$off3=""; 
     			$pos4=strpos($process,"Thursday",5);	$off4=""; 
     			$pos5=strpos($process,"Friday",5);		$off5=""; 
     			$pos6=strpos($process,"Saturday",5);	$off6=""; 
     			$pos7=strpos($process,"Sunday",5);		$off7="";      			
     			
     			if($wk_day==0)	{	$off1="+1 day";	$off2="+2 day";	$off3="+3 day";	$off4="+4 day";	$off5="+5 day";	$off6="+6 day";	$off7="+7 day";	} 
     			if($wk_day==1)	{	$off1="+1 hour";	$off2="+1 day";	$off3="+2 day";	$off4="+3 day";	$off5="+4 day";	$off6="+5 day";	$off7="+6 day";	} 
     			if($wk_day==2)	{	$off1="-1 day";	$off2="+1 hour";	$off3="+1 day";	$off4="+2 day";	$off5="+3 day";	$off6="+4 day";	$off7="+5 day";	} 
     			if($wk_day==3)	{	$off1="-2 day";	$off2="-1 day";	$off3="+1 hour";	$off4="+1 day";	$off5="+2 day";	$off6="+3 day";	$off7="+4 day";	} 
     			if($wk_day==4)	{	$off1="-3 day";	$off2="-2 day";	$off3="-1 day";	$off4="+1 hour";	$off5="+1 day";	$off6="+2 day";	$off7="+3 day";	} 
     			if($wk_day==5)	{	$off1="-4 day";	$off2="-3 day";	$off3="-2 day";	$off4="-1 day";	$off5="+1 hour";	$off6="+1 day";	$off7="+2 day";	} 
     			if($wk_day==6)	{	$off1="-5 day";	$off2="-4 day";	$off3="-3 day";	$off4="-2 day";	$off5="-1 day";	$off6="+1 hour";	$off7="+1 day";	} 
     			     			
     			$process=str_replace("Today",$cur_date,$process);
     			$process=str_replace("Vans","",$process);    
     			 			     			
     			$process=str_replace("Monday",   date("m/d/Y",strtotime("".$off1."",strtotime($cur_date))),$process);
     			$process=str_replace("Tuesday",  date("m/d/Y",strtotime("".$off2."",strtotime($cur_date))),$process);
     			$process=str_replace("Wednesday",date("m/d/Y",strtotime("".$off3."",strtotime($cur_date))),$process);
     			$process=str_replace("Thursday", date("m/d/Y",strtotime("".$off4."",strtotime($cur_date))),$process);
     			$process=str_replace("Friday",   date("m/d/Y",strtotime("".$off5."",strtotime($cur_date))),$process);
     			$process=str_replace("Saturday", date("m/d/Y",strtotime("".$off6."",strtotime($cur_date))),$process);
     			$process=str_replace("Sunday",   date("m/d/Y",strtotime("".$off7."",strtotime($cur_date))),$process);
     			    			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/") > 1)
     				{    
     					$cur_date=trim($this_line);
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";      
     					
     					//extract note...
     					if(substr_count(trim($this_line)," ") > 1)
     					{
     						$pos3=strpos(trim($this_line)," ");
     						$pos4=strpos(trim($this_line)," ",($pos3+2));
     						if($pos4 > 0)
     						{
     							$note=substr(trim($this_line),$pos4);	
     							if(strlen(trim($note)) > 2)
     							{
     								$this_line=str_replace($note,"",$this_line);
     							}
     							else
     							{
     								$note="";	
     							}
     						}	
     					}
     					
          				$local=trim($this_line);	
          				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}   	     
     				
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==14)
     		{
     			$pos1=strpos($process,"LOGISTIC VANS");
     			if($pos1 > 0)		$pos1+=13;
     			$pos2=strpos($process,"Mario's Express Service Inc",$pos1);
     			if($pos2 > 0)		$pos2-=7;
     			$process=substr($process,$pos1,($pos2 - $pos1));
     			
     			$cur_date="";
     			$cntr=0;
     			     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim($lines[$i]);
     				if($this_line!="")
     				{
     					$type=0;
     					if(substr_count($this_line,"/")==2)
     					{
     						$type=1;	//date line.
     						$cur_date=date("m/d/Y",strtotime($this_line));	
     					}	
     					else
     					{     						
     						$truck="(Misc. Truck)";
     						$trailer="";
     						$local="";
     						$note="";
     						
     						if(substr_count($this_line,"EMPTY") > 0)
     						{
     							$this_line=trim(str_replace("EMPTY","",$this_line));
     							$note="EMPTY";	
     						}
     						
     						if(substr_count($this_line,"53ft") > 0)
     						{
     							$pos3=strpos($this_line,"53ft");
     							if($pos3 > 0)
     							{
     								$trailer=substr($this_line,$pos3);
     								$local=substr($this_line,0,$pos3);	
     							}	
     						}
     						elseif(substr_count($this_line,"48ft") > 0)
     						{
     							$pos3=strpos($this_line,"48ft");
     							if($pos3 > 0)
     							{
     								$trailer=substr($this_line,$pos3);
     								$local=substr($this_line,0,$pos3);	
     							}	
     						}
     						else
     						{
     							$local=trim($this_line);
     						}    						
     						$test_lines.="<br>".$cur_date." | T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
     						
     						$add_id=0;
     						$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$cur_date,$truck,$trailer,$local,$note);
     						if($add_id > 0)		$cntr++;
     					}
     				}	
     			}
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			}     			
     		}
     		elseif($mode==15)
     		{
     			$cntr=0;
     			
     			$pos0=strpos($process,"TODAY");
     			$pos1=strpos($process,"[",($pos0+5));
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("Today ","",$process);		$process=str_replace("TODAY ","",$process);
     			$process=str_replace("Monday ","",$process);		$process=str_replace("MONDAY ","",$process);
     			$process=str_replace("Tuesday ","",$process);	$process=str_replace("TUESDAY ","",$process);
     			$process=str_replace("Wednesday ","",$process);	$process=str_replace("WEDNESDAY ","",$process);
     			$process=str_replace("Thursday ","",$process);	$process=str_replace("THURSDAY ","",$process);
     			$process=str_replace("Friday ","",$process);		$process=str_replace("FRIDAY ","",$process);
     			$process=str_replace("Saturday ","",$process);	$process=str_replace("SATURDAY ","",$process);
     			$process=str_replace("Sunday ","",$process);		$process=str_replace("SUNDAY ","",$process);
     			
     			$split_year=0;
     			if(substr_count($process,"1/") > 0 && substr_count($process,"12/") > 0 && substr_count($process,"@") > 0)		$split_year=1;
     			$cur_year=date("Y",time());	     			
     			
     			
     			$cur_date=date("m/d/Y",time());		//default to today...but can change in the list.
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/")==1)
     				{     						
               			if($split_year > 0 && substr_count($this_line,"1/") > 0 && substr_count($this_line,"11/") ==0)		{	$cur_year++;		$split_year=0;	}
               			
     					$cur_date=date("m/d/Y",strtotime(trim($this_line)."/".$cur_year));
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";          				
          				
          				//extract note...
     					if(substr_count($this_line,"(") > 0)
     					{
     						$pos3=strpos($this_line,"(");
     						if($pos3 > 0)
     						{
     							$note=trim(substr($this_line,$pos3));	
     							$this_line=str_replace($note,"",$this_line);
     						}
     					}
          				
          				$local=trim($this_line);	
          				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}       			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==16)
     		{	//message is in the subject...or not based on message mode...
     			$cntr=0;
     			
     			if(strtolower(trim($row['subject'])) == "super service available vans" || substr_count(strtolower(trim($row['subject'])), "super service available vans") > 0)
     			{	//message body has the details, not the subject.
     				//$process=trim($row['subject']);
     				
     				$process=str_replace("Looking to send the","",$process);
     				
     				$pos0=strpos($process,"Good morning!");
     				if($pos0==0)		$pos0=strpos($process,"Good afternoon!");
     				if($pos0==0)		$pos0=strpos($process,"Good evening!");
     				if($pos0==0)		$pos0=strpos($process,"Good day!");
     				
     				$pos1=strpos($process,"Helen Frase",($pos0+5));
     			
     				$process=substr($process,$pos0,($pos1 - $pos0));
     				
          			$cur_date=date("m/d/Y",strtotime($row['linedate_added']));
          			          			
          			$process=str_replace("Good morning!","",$process);
          			$process=str_replace("Good afternoon!","",$process);
          			$process=str_replace("Good evening!","",$process);
          			$process=str_replace("Good day!","",$process);
          			
          			$process=str_replace("53ft VAN","",$process);
          			$process=str_replace("48ft VAN","",$process);
          			
          			$process=str_replace("needing","needing<br>",$process);	
          			$process=str_replace("ready now","ready now<br>",$process);	
          			
          			$process=str_replace("Super Service","",$process);	
          			$process=str_replace("available VANS","",$process);
          			
          			     			
          			$lines = explode("<br>",$process);
          			for($i=0; $i < count($lines); $i++)
          			{
          				$this_line=trim(strip_tags($lines[$i]));
          				if($this_line!="")
          				{          				
               				$truck="(Misc. Truck)";
          					$trailer="";
          					$local="";
          					$note="";
          					$time="00:00:00";          				
               				
               				//extract note...
          					if(substr_count($this_line,"empty") > 0)
          					{
          						$pos3=strpos($this_line,"empty");
          						if($pos3 > 0)
          						{
          							$note=trim(substr($this_line,$pos3));	
          							$this_line=str_replace($note,"",$this_line);
          						}
          					}
          					if(substr_count($this_line,"needing") > 0)
          					{
          						$pos3=strpos($this_line,"needing");
          						if($pos3 > 0)
          						{
          							$note=trim(substr($this_line,$pos3));	
          							$this_line=str_replace($note,"",$this_line);
          						}
          					}
          					if(substr_count($this_line,"ready now") > 0)
          					{
          						$pos3=strpos($this_line,"ready now");
          						if($pos3 > 0)
          						{
          							$note=trim(substr($this_line,$pos3));	
          							$this_line=str_replace($note,"",$this_line);
          						}
          					}
               				
               				$local=trim($this_line);	
               				
               				$use_date=$cur_date." ".$time;
               				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
               				
               				$add_id=0;
          					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
          					if($add_id > 0)		$cntr++;
          				}
          			}
     			}
     			else
     			{	//message is in the subject itself.
     				$process=trim($row['subject']);
          			$cur_date=date("m/d/Y",strtotime($row['linedate_added']));
          			
          			$process=str_replace("53ft VAN","",$process);
          			$process=str_replace("48ft VAN","",$process);
          			
          			$process=str_replace("needing","needing<br>",$process);	
          			$process=str_replace("ready now","ready now<br>",$process);	
          			
          			$process=str_replace("Super Service","",$process);	
          			$process=str_replace("available VANS","",$process);
          			
          			     			
          			$lines = explode("<br>",$process);
          			for($i=0; $i < count($lines); $i++)
          			{
          				$this_line=trim(strip_tags($lines[$i]));
          				if($this_line!="")
          				{          				
               				$truck="(Misc. Truck)";
          					$trailer="";
          					$local="";
          					$note="";
          					$time="00:00:00";          				
               				
               				//extract note...
          					if(substr_count($this_line,"empty") > 0)
          					{
          						$pos3=strpos($this_line,"empty");
          						if($pos3 > 0)
          						{
          							$note=trim(substr($this_line,$pos3));	
          							$this_line=str_replace($note,"",$this_line);
          						}
          					}
          					if(substr_count($this_line,"needing") > 0)
          					{
          						$pos3=strpos($this_line,"needing");
          						if($pos3 > 0)
          						{
          							$note=trim(substr($this_line,$pos3));	
          							$this_line=str_replace($note,"",$this_line);
          						}
          					}
          					if(substr_count($this_line,"ready now") > 0)
          					{
          						$pos3=strpos($this_line,"ready now");
          						if($pos3 > 0)
          						{
          							$note=trim(substr($this_line,$pos3));	
          							$this_line=str_replace($note,"",$this_line);
          						}
          					}
               				
               				$local=trim($this_line);	
               				
               				$use_date=$cur_date." ".$time;
               				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
               				
               				$add_id=0;
          					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
          					if($add_id > 0)		$cntr++;
          				}
          			} 
     			}     			     
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==17)
     		{
     			$cntr=0;   
     			
     			$pos0=strpos($process,"DRY VAN TRAILER");
     			$pos1=strpos($process,"Thank you",($pos0+5));
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("Today ","",$process);		$process=str_replace("TODAY ","",$process);
     			$process=str_replace("Monday ","",$process);		$process=str_replace("MONDAY ","",$process);
     			$process=str_replace("Tuesday ","",$process);	$process=str_replace("TUESDAY ","",$process);
     			$process=str_replace("Wednesday ","",$process);	$process=str_replace("WEDNESDAY ","",$process);
     			$process=str_replace("Thursday ","",$process);	$process=str_replace("THURSDAY ","",$process);
     			$process=str_replace("Friday ","",$process);		$process=str_replace("FRIDAY ","",$process);
     			$process=str_replace("Saturday ","",$process);	$process=str_replace("SATURDAY ","",$process);
     			$process=str_replace("Sunday ","",$process);		$process=str_replace("SUNDAY ","",$process);
     			
     			$trailer_type="";
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/")==2)
     				{    
     					$cur_date=date("m/d/Y",strtotime(trim($this_line)));
     				}
     				elseif(substr_count($this_line,"DRY VAN TRAILER") > 0)
     				{
     					$trailer_type="Dry Van Trailer";
     				}
     				elseif(substr_count($this_line,"REEFER TRAILER") > 0)
     				{
     					$trailer_type="Reefer Trailer";
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note=$trailer_type;
     					$time="00:00:00";          				
          				
          				$local=trim($this_line);	
          				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}   			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==18)
     		{
     			$cntr=0;
     			
     			$pos0=0;
     			$pos1=strpos($process,"If you have something",($pos0+5));
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			//$process=str_replace("Today ","",$process);		$process=str_replace("TODAY ","",$process);
     			//$process=str_replace("Monday ","",$process);		$process=str_replace("MONDAY ","",$process);
     			//$process=str_replace("Tuesday ","",$process);	$process=str_replace("TUESDAY ","",$process);
     			//$process=str_replace("Wednesday ","",$process);	$process=str_replace("WEDNESDAY ","",$process);
     			//$process=str_replace("Thursday ","",$process);	$process=str_replace("THURSDAY ","",$process);
     			//$process=str_replace("Friday ","",$process);		$process=str_replace("FRIDAY ","",$process);
     			//$process=str_replace("Saturday ","",$process);	$process=str_replace("SATURDAY ","",$process);
     			//$process=str_replace("Sunday ","",$process);		$process=str_replace("SUNDAY ","",$process);
     			
     			$split_year=0;
     			if(substr_count($process,"01/") > 0 && substr_count($process,"12/") > 0)		$split_year=1;
     			$cur_year=date("Y",time());	     			
     			
     			
     			$cur_date=date("m/d/Y",time());		//default to today...but can change in the list.
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/")==1)
     				{     						
               			if($split_year > 0 && substr_count($this_line,"01/") > 0)		{	$cur_year++;		$split_year=0;	}
               			
     					$cur_date=date("m/d/Y",strtotime(trim($this_line)."/".$cur_year));
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";          				
          				
          				//extract notes...
     					if(substr_count($this_line,"(x2)") > 0)		{	$note.=" (x2)";		$this_line=str_replace("(x2)","",$this_line);		}
     					if(substr_count($this_line,"(x3)") > 0)		{	$note.=" (x3)";		$this_line=str_replace("(x3)","",$this_line);		}
     					if(substr_count($this_line,"(x4)") > 0)		{	$note.=" (x4)";		$this_line=str_replace("(x4)","",$this_line);		}
     					if(substr_count($this_line,"(x5)") > 0)		{	$note.=" (x5)";		$this_line=str_replace("(x5)","",$this_line);		}
     					if(substr_count($this_line,"(x6)") > 0)		{	$note.=" (x6)";		$this_line=str_replace("(x6)","",$this_line);		}
     					if(substr_count($this_line,"(x7)") > 0)		{	$note.=" (x7)";		$this_line=str_replace("(x7)","",$this_line);		}
     					if(substr_count($this_line,"(x8)") > 0)		{	$note.=" (x8)";		$this_line=str_replace("(x8)","",$this_line);		}
     					if(substr_count($this_line,"(x9)") > 0)		{	$note.=" (x9)";		$this_line=str_replace("(x9)","",$this_line);		}
     					if(substr_count($this_line,"(x10)") > 0)	{	$note.=" (x10)";		$this_line=str_replace("(x10)","",$this_line);		}
     					
     					
     					for($v=0; $v < 24; $v++)
     					{
     						$mask="".$v."";				if($v < 10)		$mask="0".$v."";
     						
     						if(substr_count($this_line,"(".$mask."00)") > 0)	{	$time="".$mask.":00:00";		$this_line=str_replace("(".$mask."00)","",$this_line);		}
     						if(substr_count($this_line,"(".$mask."15)") > 0)	{	$time="".$mask.":15:00";		$this_line=str_replace("(".$mask."15)","",$this_line);		}
     						if(substr_count($this_line,"(".$mask."30)") > 0)	{	$time="".$mask.":30:00";		$this_line=str_replace("(".$mask."30)","",$this_line);		}
     						if(substr_count($this_line,"(".$mask."45)") > 0)	{	$time="".$mask.":45:00";		$this_line=str_replace("(".$mask."45)","",$this_line);		}
     					}     					
          				
          				if(substr_count($this_line,",")==2)
          				{
          					$pos3=strpos(trim($this_line),",");
          					$pos4=strpos(trim($this_line)," ",($pos3+2));
          					if($pos3 > 0 && $pos4 > 0)
          					{
          						$note.=substr(trim($this_line),$pos4);
          						$local=substr(trim($this_line),0,$pos4);	
          					}	
          				}        
          				else
          				{  				
          					$local=trim($this_line);	
          				}
          				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}       		
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     		}
     		elseif($mode==19)
     		{	//Cooley...Transport....sends word document.
     			$cntr=0;
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     			else
     			{
     				$sql = "update logistics_truck_emails set processed=2,dispatch_warning=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			}
     		}
     		elseif($mode==20)
     		{	//Roger Henson Trucking (RHT)....sends word document.
     			$cntr=0;
     			$pos0=strpos($process,"Vanfts:");
     			$pos1=strpos($process,"Thanks,",($pos0+5));
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			//$process=str_replace("======================================","",$process);	
     			$process=str_replace("See ","",$process);
     			$process=str_replace("FLATBEDftS","",$process);
     			$process=str_replace("Below","",$process);
     			$process=str_replace("=","",$process);
     			$process=str_replace("Vanfts:","",$process);	
     			$process=str_replace("Flatbedfts:","",$process);	
     			$process=str_replace("Thanks","",$process);	
     			
     			$wk_day=date("w",time());
     			$cur_date=date("m/d/Y",time());
     			     			    			     		
     			$pos1=strpos($process,"Monday",5);		$off1=""; 
     			$pos2=strpos($process,"Tuesday",5);	$off2=""; 
     			$pos3=strpos($process,"Wednesday",5);	$off3=""; 
     			$pos4=strpos($process,"Thursday",5);	$off4=""; 
     			$pos5=strpos($process,"Friday",5);		$off5=""; 
     			$pos6=strpos($process,"Saturday",5);	$off6=""; 
     			$pos7=strpos($process,"Sunday",5);		$off7="";      			
     			
     			if($wk_day==0)	{	$off1="+1 day";	$off2="+2 day";	$off3="+3 day";	$off4="+4 day";	$off5="+5 day";	$off6="+6 day";	$off7="+7 day";	} 
     			if($wk_day==1)	{	$off1="+1 hour";	$off2="+1 day";	$off3="+2 day";	$off4="+3 day";	$off5="+4 day";	$off6="+5 day";	$off7="+6 day";	} 
     			if($wk_day==2)	{	$off1="-1 day";	$off2="+1 hour";	$off3="+1 day";	$off4="+2 day";	$off5="+3 day";	$off6="+4 day";	$off7="+5 day";	} 
     			if($wk_day==3)	{	$off1="-2 day";	$off2="-1 day";	$off3="+1 hour";	$off4="+1 day";	$off5="+2 day";	$off6="+3 day";	$off7="+4 day";	} 
     			if($wk_day==4)	{	$off1="-3 day";	$off2="-2 day";	$off3="-1 day";	$off4="+1 hour";	$off5="+1 day";	$off6="+2 day";	$off7="+3 day";	} 
     			if($wk_day==5)	{	$off1="-4 day";	$off2="-3 day";	$off3="-2 day";	$off4="-1 day";	$off5="+1 hour";	$off6="+1 day";	$off7="+2 day";	} 
     			if($wk_day==6)	{	$off1="-5 day";	$off2="-4 day";	$off3="-3 day";	$off4="-2 day";	$off5="-1 day";	$off6="+1 hour";	$off7="+1 day";	} 
     			     			
     			$process=str_replace("Today",$cur_date,$process);
     			 			     			
     			$process=str_replace("Monday",   date("m/d/Y",strtotime("".$off1."",strtotime($cur_date))),$process);
     			$process=str_replace("Tuesday",  date("m/d/Y",strtotime("".$off2."",strtotime($cur_date))),$process);
     			$process=str_replace("Wednesday",date("m/d/Y",strtotime("".$off3."",strtotime($cur_date))),$process);
     			$process=str_replace("Thursday", date("m/d/Y",strtotime("".$off4."",strtotime($cur_date))),$process);
     			$process=str_replace("Friday",   date("m/d/Y",strtotime("".$off5."",strtotime($cur_date))),$process);
     			$process=str_replace("Saturday", date("m/d/Y",strtotime("".$off6."",strtotime($cur_date))),$process);
     			$process=str_replace("Sunday",   date("m/d/Y",strtotime("".$off7."",strtotime($cur_date))),$process);
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/")==2)
     				{    
     					$cur_date=trim($this_line);    
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";          				
          				
          				//extract notes...
     					if(substr_count($this_line,"(2 Trucks)") > 0)	{	$note.=" (x2 Trucks)";		$this_line=str_replace("(2 Trucks)","",$this_line);		}
     					if(substr_count($this_line,"(3 Trucks)") > 0)	{	$note.=" (x3 Trucks)";		$this_line=str_replace("(3 Trucks)","",$this_line);		}
     					if(substr_count($this_line,"(4 Trucks)") > 0)	{	$note.=" (x4 Trucks)";		$this_line=str_replace("(4 Trucks)","",$this_line);		}
     					if(substr_count($this_line,"(5 Trucks)") > 0)	{	$note.=" (x5 Trucks)";		$this_line=str_replace("(5 Trucks)","",$this_line);		}
     					if(substr_count($this_line,"(6 Trucks)") > 0)	{	$note.=" (x6 Trucks)";		$this_line=str_replace("(6 Trucks)","",$this_line);		}
     					if(substr_count($this_line,"(7 Trucks)") > 0)	{	$note.=" (x7 Trucks)";		$this_line=str_replace("(7 Trucks)","",$this_line);		}
     					if(substr_count($this_line,"(8 Trucks)") > 0)	{	$note.=" (x8 Trucks)";		$this_line=str_replace("(8 Trucks)","",$this_line);		}
     					if(substr_count($this_line,"(9 Trucks)") > 0)	{	$note.=" (x9 Trucks)";		$this_line=str_replace("(9 Trucks)","",$this_line);		}
     					if(substr_count($this_line,"(10 Trucks)") > 0)	{	$note.=" (x10 Trucks)";		$this_line=str_replace("(10 Trucks)","",$this_line);		}
     					
     					$local=trim($this_line);	
     					           				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}       	     			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     			else
     			{
     				$sql = "update logistics_truck_emails set processed=2,dispatch_warning=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			}
     		}
     		elseif($mode==21)
     		{	//Titan Transfer...sends word document.
     			$cntr=0;
     			
     			$pos0=strpos($process,"Available Trucks in your area:");
     			if(substr_count($process,"SEND US OPTIONS!!!") > 0)		$pos0=strpos($process,"SEND US OPTIONS!!!");	
     			$pos1=strpos($process,"[",($pos0+5));
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("Today ","",$process);		$process=str_replace("TODAY ","",$process);
     			$process=str_replace("Monday ","",$process);		$process=str_replace("MONDAY ","",$process);
     			$process=str_replace("Tuesday ","",$process);	$process=str_replace("TUESDAY ","",$process);
     			$process=str_replace("Wednesday ","",$process);	$process=str_replace("WEDNESDAY ","",$process);
     			$process=str_replace("Thursday ","",$process);	$process=str_replace("THURSDAY ","",$process);
     			$process=str_replace("Friday ","",$process);		$process=str_replace("FRIDAY ","",$process);
     			$process=str_replace("Saturday ","",$process);	$process=str_replace("SATURDAY ","",$process);
     			$process=str_replace("Sunday ","",$process);		$process=str_replace("SUNDAY ","",$process);
     			
     			$process=str_replace("Available Trucks in your area:","",$process);	
     			$process=str_replace("SEND US OPTIONS!!!","",$process);	
     			$process=str_replace("** ","",$process);
     			$process=str_replace(" **","",$process);
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"/")==2)
     				{    
     					$cur_date=trim($this_line);    
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";  
     					
     					if(substr_count($this_line," TO ") > 0)
     					{
     						$pos3=strpos($this_line," TO ");
     						if($pos3 > 0)
     						{
     							$note=substr($this_line,$pos3);
     							$this_line=str_replace($note,"",$this_line);
     							$note=str_replace(" TO "," to ",$note);
     						}
     					}        				
          				
          				//extract notes...
     					if(substr_count($this_line," X 2") > 0)		{	$note.=" (x2 Trucks)";		$this_line=str_replace(" X 2","",$this_line);		}
     					if(substr_count($this_line," X 3") > 0)		{	$note.=" (x3 Trucks)";		$this_line=str_replace(" X 3","",$this_line);		}
     					if(substr_count($this_line," X 4") > 0)		{	$note.=" (x4 Trucks)";		$this_line=str_replace(" X 4","",$this_line);		}
     					if(substr_count($this_line," X 5") > 0)		{	$note.=" (x5 Trucks)";		$this_line=str_replace(" X 5","",$this_line);		}
     					if(substr_count($this_line," X 6") > 0)		{	$note.=" (x6 Trucks)";		$this_line=str_replace(" X 6","",$this_line);		}
     					if(substr_count($this_line," X 7") > 0)		{	$note.=" (x7 Trucks)";		$this_line=str_replace(" X 7","",$this_line);		}
     					if(substr_count($this_line," X 8") > 0)		{	$note.=" (x8 Trucks)";		$this_line=str_replace(" X 8","",$this_line);		}
     					if(substr_count($this_line," X 9") > 0)		{	$note.=" (x9 Trucks)";		$this_line=str_replace(" X 9","",$this_line);		}
     					if(substr_count($this_line," X 10") > 0)	{	$note.=" (x10 Trucks)";		$this_line=str_replace(" X 10","",$this_line);		}
     					
     					$local=trim($this_line);	
     					           				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			}  
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     			else
     			{
     				$sql = "update logistics_truck_emails set processed=2,dispatch_warning=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			}
     		}
     		elseif($mode==22)
     		{	//T & L Freight, Inc.....sends word document.
     			$cntr=0;
     			
     			$pos0=0;
     			$pos1=strpos($process,"Brian McCune",($pos0+5));
     			if($pos1==0)		$pos1=strpos($process,"T & L Freight Inc",($pos0+5));
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace(" Road Trucks"," Road Trucks<br>",$process);
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if(substr_count($this_line,"-")==2 || substr_count($this_line,"Road Trucks") > 0)
     				{    
     					$cur_date=trim($this_line);    
     				}
     				elseif($this_line!="")
     				{          				
          				$truck="(Misc. Truck)";
     					$trailer="";
     					$local="";
     					$note="";
     					$time="00:00:00";  
     					
          				//extract notes...
          				if(substr_count($this_line,"( TEAM )") > 0)	{	$note.=" (Team)";			$this_line=str_replace("( TEAM )","",$this_line);		}
          				
     					//if(substr_count($this_line," X 2") > 0)		{	$note.=" (x2 Trucks)";		$this_line=str_replace(" X 2","",$this_line);		}
     					//if(substr_count($this_line," X 3") > 0)		{	$note.=" (x3 Trucks)";		$this_line=str_replace(" X 3","",$this_line);		}
     					//if(substr_count($this_line," X 4") > 0)		{	$note.=" (x4 Trucks)";		$this_line=str_replace(" X 4","",$this_line);		}
     					//if(substr_count($this_line," X 5") > 0)		{	$note.=" (x5 Trucks)";		$this_line=str_replace(" X 5","",$this_line);		}
     					//if(substr_count($this_line," X 6") > 0)		{	$note.=" (x6 Trucks)";		$this_line=str_replace(" X 6","",$this_line);		}
     					//if(substr_count($this_line," X 7") > 0)		{	$note.=" (x7 Trucks)";		$this_line=str_replace(" X 7","",$this_line);		}
     					//if(substr_count($this_line," X 8") > 0)		{	$note.=" (x8 Trucks)";		$this_line=str_replace(" X 8","",$this_line);		}
     					//if(substr_count($this_line," X 9") > 0)		{	$note.=" (x9 Trucks)";		$this_line=str_replace(" X 9","",$this_line);		}
     					//if(substr_count($this_line," X 10") > 0)	{	$note.=" (x10 Trucks)";		$this_line=str_replace(" X 10","",$this_line);		}
     					
     					$local=trim($this_line);	
     					           				
          				$use_date=$cur_date." ".$time;
          				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
          				
          				$add_id=0;
     					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
     					if($add_id > 0)		$cntr++;
     				}
     			} 
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     			else
     			{
     				$sql = "update logistics_truck_emails set processed=2,dispatch_warning=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			}
     		}
     		elseif($mode==23)
     		{	//Roadrunner Truckload
     			$cntr=0;
     			
     			$pos0=strpos($process,"Date Available");
     			$pos1=strpos($process,"[Image removed by sender.]",($pos0+5));
     			if($pos1==0)		$pos1=strpos($process,"STAY CONNECTED",($pos0+5));
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("Date Available","",$process);
     			$process=str_replace("Equipment","",$process);
     			$process=str_replace("Desired Origin","",$process);
     			$process=str_replace("Desired Destination","",$process);
     			$process=str_replace("Contacted By","",$process);
     			
     			$sec_cntr=0;
     			$truck="V";
          		$trailer="";
          		$local="";
          		$note="";
          		$time="00:00:00"; 
     			
     			$cur_date=date("m/d/Y",time());
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if($this_line!="")
     				{        		
          				if(trim($this_line)=="V" || trim($this_line)=="VZ")
          				{    //first item, not not a date...use last date and advance to next section.
          					if($sec_cntr==0)
          					{
          						$sec_cntr++;
          					}
          					elseif($sec_cntr>=2)
          					{
          						//not much info, so bypass the rest of them and add this one.
          						$time="00:00:00";
          						$use_date=$cur_date." ".$time;
               					$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
               					
               					$add_id=0;
          						$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
          						if($add_id > 0)		$cntr++;	
          					
          						$truck="V";
          						$trailer="";
          						$local="";
          						$note="";
          						$time="00:00:00";  
          						
          						$sec_cntr=1;			//this is actually the "V" or "VZ" for the next one, so save the previous and advance this past the date.
          					}          					
          				}          				          				
          				
          				if((substr_count($this_line,"/")==2 || $sec_cntr==0))
          				{    
          					$sec_cntr=0;
          					$cur_date=trim($this_line);   
          					$sec_cntr++;
          				}
          				elseif($sec_cntr==1)
          				{
          					$truck=trim($this_line);	
          					$sec_cntr++;
          				}
          				elseif($sec_cntr==2)
          				{
          					$local=trim($this_line);	
          					$sec_cntr++;
          				}
          				elseif($sec_cntr==3)
          				{
          					$note=trim($this_line);	
          					$sec_cntr++;
          				}
          				elseif($sec_cntr==4)
          				{
          					$trailer="Contact: ".trim($this_line)."";	
          					$sec_cntr++;
          				}
          				
          				if($sec_cntr==5)
          				{
          					$time="00:00:00";
          					$use_date=$cur_date." ".$time;
               				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
               				
               				$add_id=0;
          					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
          					if($add_id > 0)		$cntr++;	
          					
          					$truck="V";
          					$trailer="";
          					$local="";
          					$note="";
          					$time="00:00:00";  
          					
          					$sec_cntr=0;
          				}
     				}
     			}
     			
     			if($sec_cntr > 0)
     			{	//still have data from a partial row...go ahead and save it as well.
     				$time="00:00:00";
          			$use_date=$cur_date." ".$time;
               		$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
               		
               		$add_id=0;
          			$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
          			if($add_id > 0)		$cntr++;	
          				
          			$truck="V";
          			$trailer="";
          			$local="";
          			$note="";
          			$time="00:00:00";  
          			
          			$sec_cntr=0;
     			} 
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     			else
     			{
     				$sql = "update logistics_truck_emails set processed=2,dispatch_warning=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			}
     		}
     		elseif($mode==24)
     		{	//TransPro
     			$cntr=0;
     			
     			$process=str_replace("Today "," ",$process);		$process=str_replace("TODAY "," ",$process);
     			$process=str_replace("Monday "," ",$process);	$process=str_replace("MONDAY "," ",$process);
     			$process=str_replace("Tuesday "," ",$process);	$process=str_replace("TUESDAY "," ",$process);
     			$process=str_replace("Wednesday "," ",$process);	$process=str_replace("WEDNESDAY "," ",$process);
     			$process=str_replace("Thursday "," ",$process);	$process=str_replace("THURSDAY "," ",$process);
     			$process=str_replace("Friday "," ",$process);	$process=str_replace("FRIDAY "," ",$process);
     			$process=str_replace("Saturday "," ",$process);	$process=str_replace("SATURDAY "," ",$process);
     			$process=str_replace("Sunday "," ",$process);	$process=str_replace("SUNDAY "," ",$process);
     			
     			$process=str_replace(" 1/"," 01/",$process);
     			$process=str_replace(" 2/"," 02/",$process);
     			$process=str_replace(" 3/"," 03/",$process);
     			$process=str_replace(" 4/"," 04/",$process);
     			$process=str_replace(" 5/"," 05/",$process);
     			$process=str_replace(" 6/"," 06/",$process);
     			$process=str_replace(" 7/"," 07/",$process);
     			$process=str_replace(" 8/"," 08/",$process);
     			$process=str_replace(" 9/"," 09/",$process);
     			
     			$process=str_replace("▪","",$process);
     			
     			$pos0=strpos($process,date("Y",time()));
     			if($pos0 > 0)		$pos0=$pos0-6;
     			if($pos0 < 0)		$pos0=0;
     			
     			$pos1=strpos($process,"Call or email us!",($pos0+5));
     			if($pos1==0)		$pos1=strpos($process,"MC# 613321",($pos0+5));
     			if($pos1==0)		$pos1=strpos($process,"Email: ",($pos0+5));
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("Call or email us!","",$process);	
     			$process=str_replace("MC# 613321","",$process);	
     			$process=str_replace("Email: ","",$process);	
     			
     			$process=strip_tags($process,"<br>");
     			
     			
     			$truck="";
          		$trailer="";
          		$local="";
          		$note="";
          		$time="00:00:00"; 
     			
     			$cur_date=date("m/d/Y",time());
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if($this_line!="")
     				{        		
          				if(substr_count($this_line,"/")==2)
          				{    
          					$cur_date=trim($this_line); 
          				}
          				else
          				{
          					//extract notes...
          					if(substr_count($this_line,"(2x)") > 0)		{	$note.=" (x2 Trucks)";		$this_line=str_replace("(2x)","",$this_line);		}
     						if(substr_count($this_line,"(3x)") > 0)		{	$note.=" (x3 Trucks)";		$this_line=str_replace("(3x)","",$this_line);		}
     						if(substr_count($this_line,"(4x)") > 0)		{	$note.=" (x4 Trucks)";		$this_line=str_replace("(4x)","",$this_line);		}
     						if(substr_count($this_line,"(5x)") > 0)		{	$note.=" (x5 Trucks)";		$this_line=str_replace("(5x)","",$this_line);		}
     						if(substr_count($this_line,"(6x)") > 0)		{	$note.=" (x6 Trucks)";		$this_line=str_replace("(6x)","",$this_line);		}
     						if(substr_count($this_line,"(7x)") > 0)		{	$note.=" (x7 Trucks)";		$this_line=str_replace("(7x)","",$this_line);		}
     						if(substr_count($this_line,"(8x)") > 0)		{	$note.=" (x8 Trucks)";		$this_line=str_replace("(8x)","",$this_line);		}
     						if(substr_count($this_line,"(9x)") > 0)		{	$note.=" (x9 Trucks)";		$this_line=str_replace("(9x)","",$this_line);		}
     						if(substr_count($this_line,"(10x)") > 0)	{	$note.=" (x10 Trucks)";		$this_line=str_replace("(10x)","",$this_line);		}
          					
          					
          					$local=trim($this_line);	
          					
          					$time="00:00:00";
          					$use_date=$cur_date." ".$time;
               				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
               					
               				$add_id=0;
          					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
          					if($add_id > 0)		$cntr++;	
          					
          					//$truck="";
          					//$trailer="";
          					$local="";
          					$note="";
          					//$time="00:00:00";  
          				}
     				}
     			}     			
     			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     			else
     			{
     				$sql = "update logistics_truck_emails set processed=2,dispatch_warning=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			}
     		}
     		elseif($mode==25)
     		{	//Step-Out Logistics
     			$cntr=0;
     			
     			$pos0=0;
     			$pos1=strpos($process,"Thanks!",($pos0+5));
     			
     			$process=substr($process,$pos0,($pos1 - $pos0));
     			
     			$process=str_replace("Update:","",$process);
     			$process=str_replace("Update...","",$process);
     			
     			$wk_day=date("w",time());
     			$cur_date=date("m/d/Y",time());
     			     			    			     		
     			$pos1=strpos($process,"Monday",5);		$off1=""; 
     			$pos2=strpos($process,"Tuesday",5);	$off2=""; 
     			$pos3=strpos($process,"Wednesday",5);	$off3=""; 
     			$pos4=strpos($process,"Thursday",5);	$off4=""; 
     			$pos5=strpos($process,"Friday",5);		$off5=""; 
     			$pos6=strpos($process,"Saturday",5);	$off6=""; 
     			$pos7=strpos($process,"Sunday",5);		$off7="";      			
     			
     			if($wk_day==0)	{	$off1="+1 day";	$off2="+2 day";	$off3="+3 day";	$off4="+4 day";	$off5="+5 day";	$off6="+6 day";	$off7="+7 day";	} 	//Sunday
     			if($wk_day==1)	{	$off1="+1 hour";	$off2="+1 day";	$off3="+2 day";	$off4="+3 day";	$off5="+4 day";	$off6="+5 day";	$off7="+6 day";	} 	//Mon
     			if($wk_day==2)	{	$off1="+6 day";	$off2="+1 hour";	$off3="+1 day";	$off4="+2 day";	$off5="+3 day";	$off6="+4 day";	$off7="+5 day";	} 	//Tues
     			if($wk_day==3)	{	$off1="+5 day";	$off2="+6 day";	$off3="+1 hour";	$off4="+1 day";	$off5="+2 day";	$off6="+3 day";	$off7="+4 day";	} 	//Wed
     			if($wk_day==4)	{	$off1="+4 day";	$off2="+5 day";	$off3="+6 day";	$off4="+1 hour";	$off5="+1 day";	$off6="+2 day";	$off7="+3 day";	} 	//Thur
     			if($wk_day==5)	{	$off1="+3 day";	$off2="+4 day";	$off3="+5 day";	$off4="+6 day";	$off5="+1 hour";	$off6="+1 day";	$off7="+2 day";	} 	//Fri
     			if($wk_day==6)	{	$off1="+2 day";	$off2="+3 day";	$off3="+4 day";	$off4="+5 day";	$off5="+6 day";	$off6="+1 hour";	$off7="+1 day";	} 	//Saturday
     			     			
     			$process=str_replace("Today",$cur_date,$process);
     			 			     			
     			$process=str_replace("Monday",   date("m/d/Y",strtotime("".$off1."",strtotime($cur_date))),$process);
     			$process=str_replace("Tuesday",  date("m/d/Y",strtotime("".$off2."",strtotime($cur_date))),$process);
     			$process=str_replace("Wednesday",date("m/d/Y",strtotime("".$off3."",strtotime($cur_date))),$process);
     			$process=str_replace("Thursday", date("m/d/Y",strtotime("".$off4."",strtotime($cur_date))),$process);
     			$process=str_replace("Friday",   date("m/d/Y",strtotime("".$off5."",strtotime($cur_date))),$process);
     			$process=str_replace("Saturday", date("m/d/Y",strtotime("".$off6."",strtotime($cur_date))),$process);
     			$process=str_replace("Sunday",   date("m/d/Y",strtotime("".$off7."",strtotime($cur_date))),$process);
     			
     			$lines = explode("<br>",$process);
     			for($i=0; $i < count($lines); $i++)
     			{
     				$this_line=trim(strip_tags($lines[$i]));
     				if($this_line!="")
     				{        		
          				if(substr_count($this_line,"/")==2 && strpos($this_line," - Ready at ")==0)
          				{    
          					$cur_date=trim($this_line); 
          				}
          				else
          				{
          					//split location from time/notes section(s)
          					$time="00:00:00"; 
          					
          					$poser=strpos($this_line," - Ready at ");
          					$local=trim(substr($this_line,0,$poser));
          					
          					$temp_time=substr($this_line,$poser,(($poser + 16) - $poser));         					
          					
          					$note=trim(substr($this_line,($poser+16)));
          					          					
          					$temp_time=trim(str_replace(" - Ready at ","",$temp_time));
          					
          					if(strlen($temp_time)==4)
          					{
          						$hr=substr($temp_time,0,2);
          						$min=substr($temp_time,2);
          						$time="".$hr.":".$min.":00"; 
          					}
          					          					
          					$trailer=trim(" - Ready at ".$temp_time);
          					
          					$use_date=$cur_date." ".$time;
               				$test_lines.="<br>".$use_date."| T:".$truck." | L:".$local." | TR: ".$trailer." | N:".$note.".";	
               					
               				$add_id=0;
          					$add_id=add_logistics_truck_listing($row['comp_id'],$row['id'],$use_date,$truck,$trailer,$local,$note);
          					if($add_id > 0)		$cntr++;	
          					
          					$truck="";
          					$trailer="";
          					$local="";
          					$note="";
          					$time="00:00:00";  
          				}
     				}
     			}     
     			    			
     			if($cntr > 0)
     			{
     				$sql = "update logistics_truck_emails set processed=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			} 
     			else
     			{
     				$sql = "update logistics_truck_emails set processed=2,dispatch_warning=1 where id='".(int) $row['id']."'";
     				mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     			}
     		}
     		elseif($mode > 0)
     		{	//Catch all for companies entered but no processign block...		  			
     			//don't do anything yet, just leve it until code is added.
     		}
     		else
     		{	//no mode...so not way to know how to process this one...flag and mark it done for now.     			  			
     			$sql = "
     				update logistics_truck_emails set
     					processed=2,
     					dispatch_warning=1			     				
     				where id='".(int) $row['id']."' 
     			";
     			mysqli_query($datasource, $sql) or die("database query failed! <br>". mysqli_error() . "<pre>". $sql ."</pre>");	
     		}
     		
     		//".$processing."<br>  $process  
     		$rep.="
     			<br><b>Email Message:</b><br>
     			
     			<br><b>Processing:</b><br>
     			".$process."<br>
     			<br><b>Captured Lines:</b><br>
     			".$test_lines."<br>
     			</div>
     			<hr>
     		";
     	}	
     	if($rep=="")		$rep="No E-Mail Messages Awaiting Processing.<br>";	
		return $rep;
	}
	
	function mrr_pull_logistics_available_loads($date_from,$date_to,$customer_id=0,$local_city="",$local_state="")
	{
		//pull available loads
		$startdate=date("Y-m-d",strtotime($date_from));
		$enddate=date("Y-m-d",strtotime($date_to));
		
		$preplan_count=0;
		$regular_count=0;
		$cntr=0;
		
		$mrr_adder="";
		if($customer_id > 0)		$mrr_adder.=" and load_handler.customer_id='".$customer_id."'";
		if(trim($local_city)!="")	$mrr_adder.=" and (load_handler.origin_city like '".trim($local_city)."' or load_handler.dest_city like '".trim($local_city)."')";
		if(trim($local_state)!="")	$mrr_adder.=" and (load_handler.origin_state like '".trim($local_state)."' or load_handler.dest_state like '".trim($local_state)."')";
		
		$tab="";
		
		$tab.="<div style='height:200px; max-height:200px; overflow:auto;'>";
		$tab.="<table class='table table-bordered well'>";		
		$tab.="
				<tr style='font-weight:bold;'> 				
     				<td valign='top'>Load ID</td>     				     				
     				<td valign='top'>PickUp ETA</td>  
     				<td valign='top'>From City</td>  
     				<td valign='top'>From State</td> 
     				<td valign='top'>To City</td> 
     				<td valign='top'>To State</td> 
     				<td valign='top'>DropOff ETA</td>  
     			</tr>
		";
		
		$sql = "
     		select load_handler.*,
     			customers.name_company,
     			drivers.name_driver_last,
     			drivers.name_driver_first,
     			drivers.jit_driver_flag
     		
     		from load_handler
     			left join customers on customers.id = load_handler.customer_id
     			left join drivers on drivers.id = load_handler.preplan_driver_id
     		where load_handler.linedate_pickup_eta >= '".$startdate." 00:00:00'
     			and load_handler.linedate_pickup_eta <= '".$enddate." 23:59:59'
     			and load_handler.deleted = 0
     			and (
     				load_handler.load_available = 1
     				or 
     				(select count(*) from trucks_log where trucks_log.deleted = 0 and trucks_log.load_handler_id = load_handler.id) = 0
     			)
     			".$mrr_adder."	
     			
     		order by load_handler.preplan, drivers.name_driver_last, drivers.name_driver_first, load_handler.linedate_pickup_eta, load_handler.id
     	";	
     	$data = simple_query($sql);
     	while($row_available = mysqli_fetch_array($data)) 
     	{						
			$tab.="
				<tr style='background-color:#".($cntr%2==0 ? "eeeeee" : "dddddd" ).";'> 				
     				<td valign='top'>".$row_available['id']."</td>     				     				
     				<td valign='top'>".date("m/d/Y H:i",strtotime($row_available['linedate_pickup_eta']))."</td>  
     				<td valign='top'>".trim($row_available['origin_city'])."</td>  
     				<td valign='top'>".trim($row_available['origin_state'])."</td> 
     				<td valign='top'>".trim($row_available['dest_city'])."</td> 
     				<td valign='top'>".trim($row_available['dest_state'])."</td> 
     				<td valign='top'>".date("m/d/Y H:i",strtotime($row_available['linedate_dropoff_eta']))."</td>  
     			</tr>
			";
			$cntr++;
		}
		$tab.="</table></div>";	//<p>".$sql."</p>
		return $tab;
	}
?>