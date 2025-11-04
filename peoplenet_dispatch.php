<? include('application.php') ?>
<? 
	$admin_page = 1;
	
	$offset_gmt=mrr_get_server_time_offset();
		
	if(isset($_GET['truck_id']))		$_POST['truck_id']=$_GET['truck_id'];
	if(isset($_GET['load_id']))		$_POST['load_id']=$_GET['load_id'];
	
	if(!isset($_POST['truck_id']))	$_POST['truck_id']=0;
	if(!isset($_POST['load_id']))		$_POST['load_id']=0;
	
	if(!isset($_POST['run_dispatch']))	$_POST['run_dispatch']=0;
	
	$truckname="1520428";
	$sql = "
     	select name_truck 
     	from trucks 
     	where id='".sql_friendly($_POST['truck_id'])."'
     ";
     $data=simple_query($sql);
	if($row=mysqli_fetch_array($data))
	{
		$truckname="".trim($row['name_truck'])."";	
	}
	$_POST['truck_name']=$truckname;
	
	$prime_url="http://open.peoplenetonline.com/scripts/open.dll";
	
	//global $defaultsarray;
     $pn_cid = $defaultsarray['peoplenet_account_number'];	
     $pn_pw = $defaultsarray['peoplenet_account_password'];
     $pn_cid=trim($pn_cid);
     $pn_pw=trim($pn_pw);
	
	$disp_reports="";
	
	
	//settings.....................................................................................
	$arriving_radius=52800;	// 10 miles
	$arrived_radius=2740;	// 1/2 mile
	$departing_radius=5280;	// 1 mile
	
	if(!isset($_POST['deliver']))				$_POST['deliver']=0;
	if(!isset($_POST['call_on_start']))		$_POST['call_on_start']=0;
	if(!isset($_POST['call_on_end']))			$_POST['call_on_end']=0;
	if(!isset($_POST['enable_auto_start']))		$_POST['enable_auto_start']=0;
	if(!isset($_POST['disable_driver_end']))	$_POST['disable_driver_end']=0;
	if(!isset($_POST['detention_warning']))		$_POST['detention_warning']=0;
	
	$_POST['deliver']=1;
	$_POST['call_on_start']=1;
	$_POST['call_on_end']=1;
	$_POST['enable_auto_start']=1;
	$_POST['disable_driver_end']=1;
	//$_POST['detention_warning']=15;
	
	$extra1="later";	if($_POST['deliver'] > 0)			$extra1="now";
	$extra2="";		if($_POST['call_on_start'] > 0)		$extra2="<call_on_start/>";
	$extra3="";		if($_POST['call_on_end'] > 0)			$extra3="<call_on_end/>";
	$extra4="";		if($_POST['enable_auto_start'] > 0)	$extra4="<enable_auto_start/>";
	$extra5="";		if($_POST['disable_driver_end'] > 0)	$extra5="<disable_driver_end/>";
	$extra6="";		if($_POST['detention_warning'] > 0)	$extra6="<detention_warning><interval>".$_POST['detention_warning']."</interval><method>1</method></detention_warning>";	
	//.............................................................................................	
	
	$output="<table class='admin_menu3' border='0' cellpadding='0' cellspacing='0' width='1600'>
			<tr>
				<td valign='top'><b>DispID</b></td>
				<td valign='top'><b>StopID</b></td>
				<td valign='top'><b>Truck</b></td>
				<td valign='top'><b>Trailer</b></td>
				<td valign='top'><b>StopType</b></td>
				<td valign='top'><b>Customer</b></td>
				<td valign='top'><b>StopPhone</b></td>
				
				<td valign='top'><b>Shipper</b></td>
				<td valign='top'><b>Address1</b></td>
				<td valign='top'><b>Address2</b></td>
				<td valign='top'><b>City</b></td>
				<td valign='top'><b>State</b></td>
				<td valign='top'><b>Zip</b></td>			
				
				<td valign='top'><b>PickupETA</b></td>
				
				<td valign='top'><b>Latitude</b></td>
				<td valign='top'><b>Longitude</b></td>
				
				<td valign='top'><b>PN ID</b></td>
				<td valign='top'><b>Stops</b></td>
			</tr>
		";
	if($_POST['load_id'] > 0)
	{
		$stops=0;
		$disp_stops=0;
		$stops_xml="";
		$first_stop_date=date("m/d/Y")." 00:00";
		$last_disp=0;
		
		$load_str=mrr_find_quick_load_string($_POST['load_id']);
		
		$sql="
			select load_handler_stops.*,
				 trucks_log.truck_id,
				 trucks_log.trailer_id,
				 trucks_log.dropped_trailer,
				 trucks_log.origin,
				 trucks_log.destination,
				 customers.name_company
			from load_handler_stops,
				trucks_log,
				customers
			where trucks_log.id=load_handler_stops.trucks_log_id
				and customers.id=trucks_log.customer_id
				and load_handler_stops.deleted=0
				and trucks_log.deleted=0
				and customers.deleted=0
				and trucks_log.truck_id='".sql_friendly($_POST['truck_id'])."'
				and load_handler_stops.load_handler_id='".sql_friendly($_POST['load_id'])."'
				and (ISNULL(load_handler_stops.linedate_completed) or load_handler_stops.linedate_completed='0000-00-00 00:00:00')
			order by trucks_log_id asc,load_handler_stops.linedate_pickup_eta asc, load_handler_stops.id asc		
		";	//
		$data=simple_query($sql);
		$mn=mysqli_num_rows($data);
		while($row=mysqli_fetch_array($data))
		{				
			if($last_disp==0)	$last_disp=$row['trucks_log_id'];
			
			$stamp =gmdate("m/d/Y H:i",strtotime("+".($offset_gmt  )." hours",strtotime($row['linedate_pickup_eta'])));		//arriving time     			
     		$stamp2=gmdate("m/d/Y H:i",strtotime("+".($offset_gmt+1)." hours",strtotime($row['linedate_pickup_eta'])));		//arrived time
     		$stamp3=gmdate("m/d/Y H:i",strtotime("+".($offset_gmt+2)." hours",strtotime($row['linedate_pickup_eta'])));		//departed time
			
			$trailer_name=mrr_find_quick_trailer_name($row['trailer_id']);
			$t_drop="";
			if($row['dropped_trailer'] > 0)	$t_drop="-Drop";
			
			
			if($row['trucks_log_id']!=$last_disp)
			{
				$mrr_res=mrr_find_truck_tracking_dispatch_record($row['trucks_log_id']);
          		$peoplenet_id=$mrr_res['peoplenet_id'];
          		$saved_stops=$mrr_res['stops'];
          		
          		$disp_status="";
          		if($peoplenet_id>0)
          		{
					$disp_status=mrr_peoplenet_find_data2("pnet_dispatch_status",$_POST,0,$peoplenet_id,0);	
				}
				$output.="
					<tr>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='16'>".$disp_status."</td>
						<td valign='top' align='right'><input type='button' value='Dispatch ".$last_disp."' onClick='mrr_run_dispatch(".$last_disp.");'></td>
					</tr>
				";			
          		          		
          		//if dispatch is already there, remove it first...
          		if($peoplenet_id>0)
          		{
          			$cmd_mode="pnet_dispatch_edit";	
          			
          			$stop_list="";
          			for($z=0; $z < $saved_stops; $z++)
          			{
          				$stop_list.="<stopid>".$z."</stopid>";	
          			}
          			
          			$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                         "<!DOCTYPE pnet_dispatch_edit PUBLIC '-//PeopleNet//pnet_dispatch_edit' 'http://open.peoplenetonline.com/dtd/pnet_dispatch_edit.dtd'>".
                         "<pnet_dispatch_edit>".
                            	"<cid>".$pn_cid."</cid>".
          				"<pw><![CDATA[".$pn_pw."]]></pw>".
                       		"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                            	"<deliver>now</deliver>".
                              "<dispid>".$peoplenet_id."</dispid>".
                            	"<remove_stops>".
                              	"".$stop_list."".
                            	"</remove_stops>".
                         "</pnet_dispatch_edit>";
                                                  
                         $disp_reports.="<br>XML: ".$xml."<br>";
                         
          			$page="";  //mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);
          			if($last_disp==$_POST['run_dispatch'])
                    	{
                    		$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
                    	}
          			
          			if(substr_count($page,"<pnet_dispatch_edit_response>") > 0)
          			{
          				$disp_reports.="<br>Removed PeopleNet Dispatch ID ".$page2."."; 
          			}
          			
          		}
				
                    //add new dispatch             		
          		$cmd_mode="pnet_dispatch";
          		$dispatch_message=" ".$load_str."";
          		
				$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                    "<!DOCTYPE pnet_dispatch PUBLIC '-//PeopleNet//pnet_dispatch' 'http://open.peoplenetonline.com/dtd/pnet_dispatch.dtd'>".
                    "<pnet_dispatch>".
                       	"<cid>".$pn_cid."</cid>".
          			"<pw><![CDATA[".$pn_pw."]]></pw>".
                       	"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                       	"<deliver>".$extra1."</deliver>".
                       	"<dispatch_name><![CDATA[Load ".$_POST['load_id'].": Trailer ".$trailer_name.": ".( trim($row['name_company'])!="" ? " Cust:".trim($row['name_company'])."" : "" )."]]></dispatch_name>".
                       	"<dispatch_description><![CDATA[From ".$row['origin']." To ".$row['destination'].".]]></dispatch_description>".
                       	"<dispatch_userdata><![CDATA[Load ".$_POST['load_id'].": Dispatch ".$row['trucks_log_id']."]]></dispatch_userdata>".
                       	"<trip_data>".
                         	"<trip_start_time><![CDATA[".$first_stop_date."]]></trip_start_time>".
                         	"".$extra2."".
                         	"".$extra3."".
                         	"".$extra4."".
                         	"".$extra5."".
                       	"</trip_data>".
                       	"".$stops_xml."".
                    "</pnet_dispatch>";	
                    
                    //$disp_reports.="<br>XML: ".$xml."<br>";
                    /*
                    Load ".$_POST['load_id'].": 
                    */                    
                    
                    $page="";		//mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);                    
                    if($last_disp==$_POST['run_dispatch'])
                    {
                    	$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
                    }
                    
                    if(substr_count($page,"<pnet_dispatch_response>") > 0)
                    {
                    	$peoplenet_id=0;
                    	
                    	$page2="";
          			
          			$poser1=strpos($page,"<dispid>");
          			$poser2=strpos($page,"</dispid>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<dispid>","",$page2);					$page2=str_replace("</dispid>","",$page2);
          				$peoplenet_id=$page2;
          			}
                    	
                    	$disp_reports.="<br>Added PeopleNet Dispatch ID ".$peoplenet_id."."; 
                    	                       	
                    	$logged=mrr_add_truck_tracking_dispatch_record($_POST['truck_id'],$row['trucks_log_id'],$disp_stops,$peoplenet_id);	
                    	
                    	/*
                    	//now send mesage:----------------------------------------------------------------------------------------------------------------------------------------
                    	$msg_xml=mrr_peoplenet_form_message($truckname,$dispatch_message,0,1);					//$truck,$message,$urgent=0,$delivery=0,$force_it=0,$force_mode=0
                    	$msg_page=mrr_peoplenet_get_file_contents($prime_url,$msg_xml,"imessage_send");
                    	
                    	$msg_sent_text=" <span class='alert'>Message Not Sent</span>";			
          			$msg_page=strip_tags($msg_page);	
          			if(substr_count($msg_page,"success") > 0)
          			{
          				$msg_page=str_replace("success","",$msg_page);	
          				
          				$msg_mess_num=trim($msg_page);				
          				$msg_sent_text=" <b>Message Sent: ID=".$dispatch_message."</b>";	
          				$msg_page="";
          				
          				$msg_sending=mrr_peoplenet_store_message($dispatch_message,$row['truck_id']);		//record of message to be sent			
          			}
          			else
          			{
          				$msg_page=str_replace("failure"," Failure: ",$msg_page);
          				$msg_sent_text.="".$msg_page."";		
          			}
                    	$disp_reports.=$msg_sent_text;
                    	
                    	mrr_peoplenet_use_the_force_call($truckname);	//log message
                    	//--------------------------------------------------------------------------------------------------------------------------------------------------------	
                    	*/
                    }
                    else
                    {
                    	$page2="";
          			
          			$poser1=strpos($page,"<pnet_response>");
          			$poser2=strpos($page,"</pnet_response>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<sendresult>","<b>Error:</b> ",$page2);		$page2=str_replace("</sendresult>","",$page2);
          				$page2=str_replace("<error_message>","",$page2);				$page2=str_replace("</error_message>","",$page2);
          			}
          			if(trim($page2)!="") 	$disp_reports.="<br><span class='alert'>ERROR: ".$page2.".</span>.";		
                    }                      
                    
                    //reset counter
				$disp_stops=0;	
				$stops_xml="";
				
				$last_disp=$row['trucks_log_id'];
			}
						
			if($disp_stops==0)	$first_stop_date=$stamp;	
						
			$stops++;
			$disp_stops++;
			$label="Stop ".$disp_stops.": Trailer ".$trailer_name."".$t_drop.": ".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')."";
			
			if($row['latitude']==0 || $row['longitude']==0)
			{	//not saved, get and save...
				$res=mrr_get_geocode_for_address($row['shipper_address1'],$row['shipper_address2'],$row['shipper_city'],$row['shipper_state'],$row['shipper_zip']);
				$latitude=$res['latitude'];
				$longitude=$res['longitude'];
				//$res['quality']=2;
								
				if($row['latitude']==0)		$row['latitude']=$latitude;
				if($row['longitude']==0)		$row['longitude']=$longitude;	
				
				$sql="
					update load_handler_stops set 
						latitude= '".sql_friendly($latitude)."',
						longitude='".sql_friendly($longitude)."'
					where load_handler_stops.id='".sql_friendly($row['id'])."'	
				";	//
				simple_query($sql);
			}
			
			$mrr_res=mrr_find_truck_tracking_dispatch_record($row['trucks_log_id']);
          	$peoplenet_id=$mrr_res['peoplenet_id'];
          	$saved_stops=$mrr_res['stops'];			
			
			$output.="
				<tr>
					<td valign='top'>".$row['trucks_log_id']."</td>
					<td valign='top'>".$row['id']."</td>
					<td valign='top'>".$truckname."</td>
					<td valign='top'>".$trailer_name."".$t_drop."</td>
					
					<td valign='top'>".($row['stop_type_id']==1 ? 'Shipper' : 'Consignee')."</td>
					<td valign='top'>".$row['name_company']."</td>
					<td valign='top'>".$row['stop_phone']."</td>
					
					<td valign='top'>".$row['shipper_name']."</td>
					<td valign='top'>".$row['shipper_address1']."</td>
					<td valign='top'>".$row['shipper_address2']."</td>
					<td valign='top'>".$row['shipper_city']."</td>
					<td valign='top'>".$row['shipper_state']."</td>
					<td valign='top'>".$row['shipper_zip']."</td>
										
					<td valign='top'>".$row['linedate_pickup_eta']."</td>
					
					<td valign='top'>".$row['latitude']."</td>
					<td valign='top'>".$row['longitude']."</td>
					
					<td valign='top'>".$peoplenet_id."</td>
					<td valign='top'>".$saved_stops."</td>
				</tr>
			";
			// ".$load_str."
			$stops_xml.=
				"<stop>".
               		"<stop_head>".                  
               			"<stop_userdata><![CDATA[".$label."]]></stop_userdata>".
               			"<custom_stop>".
         						"<name><![CDATA[".$row['shipper_name']."]]></name>".
         						"<description><![CDATA[Ph ".$row['stop_phone']."; ".$row['shipper_address1'].", ".$row['shipper_city'].", ".$row['shipper_state']." ".$row['shipper_zip'].". ".$row['directions'].".]]></description>".
         						"<latitude><![CDATA[".$row['latitude']."]]></latitude>".
         						"<longitude><![CDATA[".$row['longitude']."]]></longitude>".
       					"</custom_stop>".                 		
               		"</stop_head>".
               		"<advanced_actions>".
                           	"<arriving_action>".
                             		"<action_general>".
                               		"<radius_feet>".$arriving_radius."</radius_feet>".                    
                               		"<occur_by><![CDATA[".$stamp."]]></occur_by>".  
                               		"<call_on_occur/>".                           
                               		"<disp_message_on_late/>".                 
                             		"</action_general>".
                           	"</arriving_action>".
                           	"<arrived_action>".
                             		"<action_general>".
                               		"<radius_feet>".$arrived_radius."</radius_feet>".
                               		"<occur_by><![CDATA[".$stamp2."]]></occur_by>".
                               		"<call_on_occur/>".
                               		"<disp_message_on_late/>".
                             		"</action_general>".
                           	"</arrived_action>".
                           	"<departed_action>".
                             		"<action_general>".
                               		"<radius_feet>".$departing_radius."</radius_feet>".
                               		"<occur_by><![CDATA[".$stamp3."]]></occur_by>".
                               		"<call_on_occur/>".
                               		"<disp_message_on_late/>".
                             		"</action_general>".
                        		"</departed_action>".
                        	"</advanced_actions>".
             		"</stop>";
             		
               if($disp_stops>0 && $stops==$mn)
     		{     		
          		$mrr_res=mrr_find_truck_tracking_dispatch_record($row['trucks_log_id']);
          		$peoplenet_id=$mrr_res['peoplenet_id'];
          		$saved_stops=$mrr_res['stops'];
          		
          		$disp_status="";
          		if($peoplenet_id>0)
          		{
					$disp_status=mrr_peoplenet_find_data2("pnet_dispatch_status",$_POST,0,$peoplenet_id,0);	
				}
				$output.="
					<tr>
						<td valign='top'>&nbsp;</td>
						<td valign='top' colspan='16'>".$disp_status."</td>
						<td valign='top' align='right'><input type='button' value='Dispatch ".$last_disp."' onClick='mrr_run_dispatch(".$last_disp.");'></td>
					</tr>
				";
          		          		          		
          		//if dispatch is already there, remove it first...
          		if($peoplenet_id>0)
          		{
          			$cmd_mode="pnet_dispatch_edit";	
          			
          			$stop_list="";
          			for($z=0; $z < $saved_stops; $z++)
          			{
          				$stop_list.="<stopid>".$z."</stopid>";	
          			}
          			
          			$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                         "<!DOCTYPE pnet_dispatch_edit PUBLIC '-//PeopleNet//pnet_dispatch_edit' 'http://open.peoplenetonline.com/dtd/pnet_dispatch_edit.dtd'>".
                         "<pnet_dispatch_edit>".
                            	"<cid>".$pn_cid."</cid>".
          				"<pw><![CDATA[".$pn_pw."]]></pw>".
                       		"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                            	"<deliver>now</deliver>".
                              "<dispid>".$peoplenet_id."</dispid>".
                            	"<remove_stops>".
                              	"".$stop_list."".
                            	"</remove_stops>".
                         "</pnet_dispatch_edit>";
                         
                         $disp_reports.="<br>XML: ".$xml."<br>";
                         
          			$page="";  //mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);
          			if($last_disp==$_POST['run_dispatch'])
                    	{
                    		$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
                    	}
          			
          			if(substr_count($page,"<pnet_dispatch_edit_response>") > 0)
          			{
          				$disp_reports.="<br>Removed PeopleNet Dispatch ID ".$page2."."; 
          			}
          			
          		}
          		
          		//add new dispatch             		
          		$cmd_mode="pnet_dispatch";
          		$dispatch_message=" ".$load_str."";
          		
          		$xml="<?xml version='1.0' encoding='ISO-8859-1'?>".
                    "<!DOCTYPE pnet_dispatch PUBLIC '-//PeopleNet//pnet_dispatch' 'http://open.peoplenetonline.com/dtd/pnet_dispatch.dtd'>".
                    "<pnet_dispatch>".
                       	"<cid>".$pn_cid."</cid>".
          			"<pw><![CDATA[".$pn_pw."]]></pw>".
                       	"<vehicle_number><![CDATA[".$truckname."]]></vehicle_number>".
                       	"<deliver>".$extra1."</deliver>".  
                       	"<dispatch_name><![CDATA[Load ".$_POST['load_id'].": Trailer ".$trailer_name.": ".  ( trim($row['name_company'])!="" ? " Cust:".trim($row['name_company'])."" : "" )  ."]]></dispatch_name>".
                       	"<dispatch_description><![CDATA[From ".$row['origin']." To ".$row['destination'].".]]></dispatch_description>".
                       	"<dispatch_userdata><![CDATA[Load ".$_POST['load_id'].": Dispatch ".$row['trucks_log_id']."]]></dispatch_userdata>".
                       	"<trip_data>".
                         	"<trip_start_time><![CDATA[".$first_stop_date."]]></trip_start_time>".
                         	"".$extra2."".
                              "".$extra3."".
                              "".$extra4."".
                              "".$extra5."".
                       	"</trip_data>".
                       	"".$stops_xml."".
                    "</pnet_dispatch>";	
                    /*
                    	"<dispatch_name><![CDATA[Load ".$_POST['load_id'].": Trailer ".$trailer_name."]]></dispatch_name>".
                       	"<dispatch_description><![CDATA[".$row['name_company'].": ".$row['origin']." - ".$row['destination']." ".$load_str."]]></dispatch_description>".
                    	Load ".$_POST['load_id'].": 
                    */
                    
                    $disp_reports.="<br>XML: ".$xml."<br>";                   
                    
                    $page="";		//mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);                    
                    if($last_disp==$_POST['run_dispatch'])
                    {
                    	$page=mrr_peoplenet_get_file_contents($prime_url,$xml,$cmd_mode);	
                    }
                    
                    if(substr_count($page,"<pnet_dispatch_response>") > 0)
                    {
                    	$peoplenet_id=0;
                    	
                    	$page2="";
          			
          			$poser1=strpos($page,"<dispid>");
          			$poser2=strpos($page,"</dispid>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<dispid>","",$page2);					$page2=str_replace("</dispid>","",$page2);
          				$peoplenet_id=$page2;
          			}
                    	
                    	$disp_reports.="<br>Added PeopleNet Dispatch ID ".$peoplenet_id."."; 
                    	                       	
                    	$logged=mrr_add_truck_tracking_dispatch_record($_POST['truck_id'],$row['trucks_log_id'],$disp_stops,$peoplenet_id);
                    	/*
                    	//now send mesage:----------------------------------------------------------------------------------------------------------------------------------------
                    	$msg_xml=mrr_peoplenet_form_message($truckname,$dispatch_message,0,1);					//$truck,$message,$urgent=0,$delivery=0,$force_it=0,$force_mode=0
                    	$msg_page=mrr_peoplenet_get_file_contents($prime_url,$msg_xml,"imessage_send");
                    	
                    	$msg_sent_text=" <span class='alert'>Message Not Sent</span>";			
          			$msg_page=strip_tags($msg_page);	
          			if(substr_count($msg_page,"success") > 0)
          			{
          				$msg_page=str_replace("success","",$msg_page);	
          				
          				$msg_mess_num=trim($msg_page);				
          				$msg_sent_text=" <b>Message Sent: ID=".$dispatch_message."</b>";	
          				$msg_page="";
          				
          				$msg_sending=mrr_peoplenet_store_message($dispatch_message,$row['truck_id']);		//record of message to be sent			
          			}
          			else
          			{
          				$msg_page=str_replace("failure"," Failure: ",$msg_page);
          				$msg_sent_text.="".$msg_page."";		
          			}
                    	$disp_reports.=$msg_sent_text;
                    	
                    	mrr_peoplenet_use_the_force_call($truckname);	//log message
                    	//--------------------------------------------------------------------------------------------------------------------------------------------------------	
                    	*/
                    }
                    else
                    {
                    	$page2="";
          			
          			$poser1=strpos($page,"<pnet_response>");
          			$poser2=strpos($page,"</pnet_response>");
          			if($poser1 > 0 && $poser2 > 0)
          			{
          				$page2=substr($page, $poser1, ($poser2 - $poser1));
          				
          				$page2=str_replace("<sendresult>","<b>Error:</b> ",$page2);		$page2=str_replace("</sendresult>","",$page2);
          				$page2=str_replace("<error_message>","",$page2);				$page2=str_replace("</error_message>","",$page2);
          			}	
          			if(trim($page2)!="") 	$disp_reports.="<br><span class='alert'>ERROR: ".$page2.".</span>.";	
                    }                            
     		}
		}
		
		
	}
	$output.="</table>";
	
?>
<? include('header.php') ?>
<form name='peoplenet_dipatcher' action='<?= $SCRIPT_NAME ?>' method='post'>
<table class='admin_menu2' style='width:1600px'>
<tr>
	<td valign='top' colspan='3'>
		<div class='section_heading'>PeopleNet Dispatch(es)</div>
	</td>
	<td valign='top' align='right'>
		<input type='button' value='Check Status' onClick='mrr_get_dispatch();'>
	</td>
</tr>
<tr>
	<td valign='top'>
		Truck ID: (<?= $_POST['truck_id'] ?>) <input type='hidden' id='truck_id' name='truck_id' value='<?= $_POST['truck_id'] ?>'>
	</td>
	<td valign='top'>
		Truck Name: <?= $_POST['truck_name'] ?> <input type='hidden' id='truck_name' name='truck_name' value='<?= $_POST['truck_name'] ?>'>
	</td>
	<td valign='top'>
		Load ID: <?= $_POST['load_id'] ?><input type='hidden' id='load_id' name='load_id' value='<?= $_POST['load_id'] ?>'>
	</td>
	<td valign='top'>
		Process Dispatch ID: <?= $_POST['run_dispatch'] ?><input type='hidden' id='run_dispatch' name='run_dispatch' value='<?= $_POST['run_dispatch'] ?>'>
	</td>
</tr>
<tr>
	<td valign='top' colspan='4'>
		<?= $output ?>
	</td>	
</tr>
<tr>
	<td valign='top' colspan='4'>
		<?= $disp_reports ?>
	</td>	
</tr>
<tr>
	<td valign='top' colspan='4'>
		<?=mrr_find_truck_tracking_dispatch_record_all($_POST['truck_id'],0,0)  ?>
	</td>	
</tr>
</table>
</form>
<script type='text/javascript'>
	function mrr_run_dispatch(id)
	{
		$('#run_dispatch').val(id);
		document.peoplenet_dipatcher.submit();
	}
	function mrr_get_dispatch()
	{
		$('#run_dispatch').val('0');
		document.peoplenet_dipatcher.submit();	
	}
</script>
<? include('footer.php') ?>