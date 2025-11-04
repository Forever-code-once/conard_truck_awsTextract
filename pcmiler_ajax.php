<? include('application.php')?>
<?

	switch ($_GET['cmd']) {
		case 'verify_stop':
			verify_stop();
			break;
		case 'build_run':
			build_run();
			break;
		case 'verify_stop_old':
			verify_stop_old();
			break;
		case 'build_run_old':
			build_run_old();
			break;
		case 'verify_stop_older':
			verify_stop_older();
			break;
		case 'build_run_older':
			build_run_older();
			break;
	}
	
	//first two cases are the actively used versions...others are other versions that are in testing or depricated...but kept just in case.
	
	//last two functions are currently being used....and are the only PC Miler functions.
	function verify_stop_older() {
			
		$use_location = '';
		$rslt = 0;
		$mylocation=trim($_POST['location']);
		
		if(substr_count($mylocation,",") > 0)
		{
			$poser=strpos($mylocation,",");
			
			$sub1=substr($mylocation,0,$poser);
			$sub2=substr($mylocation,$poser);
			
			$sub1=trim(str_replace(",","",$sub1));
			$sub2=trim(str_replace(",","",$sub2));
			
			$mycity=$sub1;
			//now get state
			if(substr_count($sub2," ") > 0)
			{	//state and zip code here
				$pose2=strpos($sub2," ");
				$sub3=substr($sub2,0,$pose2);
				$sub4=substr($sub2,$pose2);
				
				$sub3=trim(str_replace(",","",$sub3));
				$sub4=trim(str_replace(",","",$sub4));
				     					
				$mystate=$sub3;
				
				if(strlen($sub4) <= 5 && substr_count($sub4,"-")==0)
				{
					$myzip=$sub4;
				}
				elseif(strlen($sub4) <= 9 && substr_count($sub4,"-") > 0)
				{
					$myzip=$sub4;
				}     					
			}
			else
			{
				$mystate=$sub2;
			}	
			
			
			if($mycity!="" && $mystate!="")
			{
				$res=mrr_get_promiles_gps_from_address('',trim($mycity),trim($mystate),trim($myzip));
				if($res['status']==1)
				{				
					$use_location =trim($mylocation);
					$rslt = 1;
				}
				else
				{
					$rslt_msg = "error with location";	
				}
			} 							
		}
		else
		{
			$rslt_msg = "error with location";	
		}				
		display_xml_response("<rslt>$rslt</rslt><UseLocation><![CDATA[$use_location]]></UseLocation>");		
	}
	
	function build_run_older() 
	{		
		$localarray = explode(";",$_POST['ziplist']);
				
		$local_cntr=count($localarray);		
				
		$rslt=0;
		$traveldist = 0;
		$travel_time = 0;
		$stoparray_dist = array();
		$stoparray_time = array();
		$stoparray_full_1 = array();
		$stoparray_full_2 = array();
		$stoparray_loc1 = array();
		$stoparray_loc2 = array();
		$stoparray_st1 = array();
		$stoparray_st2 = array();
		$stoparray_hours = array();
		
		$stoparray_lat1 = array();
		$stoparray_lat2 = array();
		$stoparray_long1 = array();
		$stoparray_long2 = array();
				
		$stop_counter = 0;
		$counter = 0;
		$stop_minutes = 0;
				
		$gps[0]['city']="";				
		$gps[0]['zip']="";	
		$gps[0]['state']="";	
		$gps[0]['lat']="";	
		$gps[0]['long']="";	
		
		$last_local = "";
		$last_city = "";
		$last_state = "";
		$last_zip = "";
		$last_lat = "";
		$last_long = "";
		
		$mph=35;
		$calc_errors="";
		
		for($i=0; $i < $local_cntr; $i++)
		{
			$mylocation=$localarray[$i];
			$mycity="";
			$mystate="";
			$myzip="";
			$mylat="";
			$mylong="";
			$dist=0;
			
			if(trim($mylocation)!="")
			{     			
     			if(substr_count($mylocation,",") > 0)
     			{
     				$poser=strpos($mylocation,",");
     				
     				$sub1=substr($mylocation,0,$poser);
     				$sub2=substr($mylocation,$poser);
     				
     				$sub1=trim(str_replace(",","",$sub1));
     				$sub2=trim(str_replace(",","",$sub2));
     				
     				$mycity=$sub1;
     				//now get state
     				if(substr_count($sub2," ") > 0)
     				{	//state and zip code here
     					$pose2=strpos($sub2," ");
     					$sub3=substr($sub2,0,$pose2);
     					$sub4=substr($sub2,$pose2);
     					
     					$sub3=trim(str_replace(",","",$sub3));
     					$sub4=trim(str_replace(",","",$sub4));
     					     					
     					$mystate=$sub3;
     					
     					if(strlen($sub4) <= 5 && substr_count($sub4,"-")==0)
     					{
     						$myzip=$sub4;
     					}
     					elseif(strlen($sub4) <= 9 && substr_count($sub4,"-") > 0)
     					{
     						$myzip=$sub4;
     					}     					
     				}
     				else
     				{
     					$mystate=$sub2;
     				}								
     			}
     			else
     			{
     				$mycity=$mylocation;
     				$calc_errors.="No State Found in (".$mycity.").  ";
     			}		
     			
     			if($mycity!="" && $mystate!="")
     			{
     				$res=mrr_get_promiles_gps_from_address('',trim($mycity),trim($mystate),trim($myzip));
     				if($res['status']==1)
     				{				
     					//$mycity=$res['city'];			$gps[$i]['city']=$res['city'];
     					//$mystate=$res['state'];		$gps[$i]['state']=$res['state'];	
     					//$myzip=$res['zip'];			$gps[$i]['zip']=$res['zip'];
     					$mylat=$res['lat'];				$gps[$i]['lat']=$res['lat'];	
     					$mylong=$res['long'];			$gps[$i]['long']=$res['long'];
     				}
     				if(trim($res['error'])!="")
     				{
     					$calc_errors.=" ...Geocode Error [".trim($res['error'])."]...  ";
     				}
     			}        			
     			
     			if(trim($mylat)!="" && trim($mylong)!="")
     			{
     				if($i == 0)
     				{	//start trip...0 for distance and time.
     					$stoparray_dist[$counter]=0;
     					$stoparray_time[$counter]=0;
     					$stoparray_loc1[$counter]=$mycity;
     					$stoparray_loc2[$counter]=$mycity;
     					$stoparray_st1[$counter]=$mystate;
     					$stoparray_st2[$counter]=$mystate;
     					
     					$stoparray_full_1[$counter] =trim($mylocation);
						$stoparray_full_2[$counter] =trim($mylocation);
     					
     					$comp_hrs=0;
     					
     					$stoparray_hours[$counter]=$comp_hrs;
     					$travel_time+=$comp_hrs;
     					$traveldist+=0;
     					
     					$stoparray_lat1[$counter]=$mylat;
     					$stoparray_lat2[$counter]=$mylat;
     					$stoparray_long1[$counter]=$mylong;
     					$stoparray_long2[$counter]=$mylong;
     				}
     				else
     				{
     					$dist=mrr_promiles_get_file_contents($mylat,$mylong,$last_lat,$last_long);	
     					$comp_hrs=number_format(($dist / $mph),2);
     					$comp_hrs=str_replace(",","",$comp_hrs);
     					
     					$stoparray_dist[$counter]=$dist;
     					$stoparray_time[$counter]=$comp_hrs;
     					$stoparray_loc1[$counter]=$last_city;
     					$stoparray_loc2[$counter]=$mycity;
     					$stoparray_st1[$counter]=$last_state;
     					$stoparray_st2[$counter]=$mystate;
     					
     					$stoparray_full_1[$counter] =trim($last_local);
						$stoparray_full_2[$counter] =trim($mylocation);
     					
     					$stoparray_hours[$counter]=$comp_hrs;
     					$travel_time+=$comp_hrs;
     					$traveldist+=$dist;	
     					
     					$stoparray_lat1[$counter]=$last_lat;
     					$stoparray_lat2[$counter]=$mylat;
     					$stoparray_long1[$counter]=$last_long;
     					$stoparray_long2[$counter]=$mylong;
     				}
     				$stop_counter++;	
     				
     				$last_local=$mylocation;
     				$last_city = $mycity;
					$last_state = $mystate;
					$last_zip = $myzip;
					$last_lat = $mylat;
					$last_long = $mylong;	
					$counter++;
					$rslt=1;
     			}
     			else
     			{
					$stoparray_dist[$counter]=0;
					$stoparray_time[$counter]=0;
					$stoparray_loc1[$counter]="";
					$stoparray_loc2[$counter]="";
					$stoparray_st1[$counter]="";
					$stoparray_st2[$counter]="";
					
					$stoparray_full_1[$counter] ="";
					$stoparray_full_2[$counter] ="";
					
					$comp_hrs=0;
					
					$stoparray_hours[$counter]=$comp_hrs;
					$travel_time+=$comp_hrs;
					$traveldist+=0;
					
					$stoparray_lat1[$counter]="";
					$stoparray_lat2[$counter]="";
					$stoparray_long1[$counter]="";
					$stoparray_long2[$counter]="";
					$counter++;
     			}     			  			
			}		
		}
		
		$return_val = "
			<rslt>$rslt</rslt>
			<Miles>$traveldist</Miles>
			<TravelTime>$travel_time</TravelTime>
			<Errors><![CDATA[$calc_errors]]></Errors>
		";
		
		$my_cntr=count($stoparray_dist);
		for($i=0;$i < $my_cntr; $i++)
		{
			$return_val .= "
				<StopEntry>
					<StopDistance>".$stoparray_dist[$i]."</StopDistance>
					<StopLoc1><![CDATA[".$stoparray_loc1[$i]."]]></StopLoc1>
					<StopLoc2><![CDATA[".$stoparray_loc2[$i]."]]></StopLoc2>
					<StopState1><![CDATA[".$stoparray_st1[$i]."]]></StopState1>
					<StopState2><![CDATA[".$stoparray_st2[$i]."]]></StopState2>
					<StopFull1><![CDATA[".$stoparray_full_1[$i]."]]></StopFull1>
					<StopFull2><![CDATA[".$stoparray_full_2[$i]."]]></StopFull2>
					<StopTime>".$stoparray_time[$i]."</StopTime>
					<StopHours>".$stoparray_hours[$i]."</StopHours>
					<StopLine>".$i."</StopLine>
				</StopEntry>
			";	
		}		
		
		display_xml_response($return_val);		
	}
	
	function verify_stop_old() {
			
		$use_location = '';
		$rslt = 0;
		
		$zip=trim($_POST['location']);
		if(substr_count($zip," ") > 0)
		{
			$poser=strrpos($zip," ");
			if($poser > 0)		$zip=substr($zip,$poser,6);
			$zip=trim($zip);	
		}
		
		if(is_numeric($zip))
		{
			$dres=mrr_process_zip_code_to_zip_code($zip,$zip);
			/*
     		$stoparray_dist[$counter]=ceil($dres['miles']);
     		$stoparray_time[$counter]=$dres['time'];
     		$stoparray_loc1[$counter]=$dres['city1'].", ".$dres['state1'];
     		$stoparray_loc2[$counter]=$dres['city2'].", ".$dres['state2'];
     		
     		$comp_hrs=mrr_calc_time_into_hours($dres['time']);
     		
     		$stoparray_hours[$counter]=$comp_hrs;
     		$travel_time+=$comp_hrs;
     		$traveldist+=ceil($dres['miles']);
     		*/			
			
			$rslt = 1;
			$use_location =$dres['city2'].", ".$dres['state2'];
		}
		else
		{
			$rslt_msg = "error with location";	
		}		
		display_xml_response("<rslt>$rslt</rslt><UseLocation><![CDATA[$use_location]]></UseLocation>");		
	}
	
	function build_run_old() {
		
		$ziparray = explode(",",$_POST['ziplist']);
		$rslt=0;
		$traveldist = 0;
		$travel_time = 0;
		$stoparray_dist = array();
		$stoparray_time = array();
		$stoparray_loc1 = array();
		$stoparray_loc2 = array();
		$stoparray_hours = array();
				
		$last_zip = "";
		$stop_counter = 0;
		$counter = 0;
		$first_stop = "";
		$stop_minutes = 0;
		foreach($ziparray as $zip) 
		{			
			if($first_stop == '' && $zip != '') 	$first_stop = $zip;
			
			if($zip != '') 
			{
				/*
				if($stop_counter) 
				{
					if($_POST['hub_run'] == '1') 
					{
						//$stoparray_dist[$counter] = $pcm->CalcDistance3($first_stop, $zip, 0, $stop_minutes) / 10;						
					} 
					else 
					{
						//$stoparray_dist[$counter] = $pcm->CalcDistance3($last_zip, $zip, 0, $stop_minutes) / 10;											
					}					
				}
				*/
				
				if($last_zip=="")
				{
					$dres=mrr_process_zip_code_to_zip_code($first_stop,$zip);
					$stoparray_dist[$counter]=ceil($dres['miles']);
					$stoparray_time[$counter]=$dres['time'];
					$stoparray_loc1[$counter]=$dres['city1'].", ".$dres['state1'];
					$stoparray_loc2[$counter]=$dres['city2'].", ".$dres['state2'];
					
					$comp_hrs=mrr_calc_time_into_hours($dres['time']);
					
					$stoparray_hours[$counter]=$comp_hrs;
					$travel_time+=$comp_hrs;
					$traveldist+=ceil($dres['miles']);
				}
				else
				{
					$dres=mrr_process_zip_code_to_zip_code($last_zip,$zip);	
					$stoparray_dist[$counter]=ceil($dres['miles']);	
					$stoparray_time[$counter]=$dres['time'];
					$stoparray_loc1[$counter]=$dres['city1'].", ".$dres['state1'];
					$stoparray_loc2[$counter]=$dres['city2'].", ".$dres['state2'];
					
					$comp_hrs=mrr_calc_time_into_hours($dres['time']);
					
					$stoparray_hours[$counter]=$comp_hrs;
					$travel_time+=$comp_hrs;
					$traveldist+=ceil($dres['miles']);
				}
				
				$last_zip = $zip;
				$stop_counter++;
			}
			$counter++;
			$rslt=1;
		}		
		
		$return_val = "
			<rslt>$rslt</rslt>
			<Miles>$traveldist</Miles>
			<TravelTime>$travel_time</TravelTime>
		";
		
		$my_cntr=count($stoparray_dist);
		for($i=0;$i < $my_cntr; $i++)
		{
			$return_val .= "
				<StopEntry>
					<StopDistance>".$stoparray_dist[$i]."</StopDistance>
					<StopLoc1>".$stoparray_loc1[$i]."</StopLoc1>
					<StopLoc2>".$stoparray_loc2[$i]."</StopLoc2>
					<StopTime>".$stoparray_time[$i]."</StopTime>
					<StopHours>".$stoparray_hours[$i]."</StopHours>
					<StopLine></StopLine>
				</StopEntry>
			";	
		}	
		
		display_xml_response($return_val);		
	}
	
	//older...
	function verify_stop() {
		
		$pcm = new COM("PCMServer.PCMServer") or die ("connection create fail");
		//global $pcm;
		
		$use_location = '';
		$rslt = 0;
		try {
			$plist = $pcm->GetPickList($_POST['location'], "NA", 0);
			
			if(count($plist) == 0) {
				$rslt_msg = "invalid location";
				
			} else {
				$use_location = $plist->Entry(0);
				$rslt = 1;
			}
		} catch (Exception $e) {
			$rslt_msg = "error with location";
		}
		
		
		display_xml_response("<rslt>$rslt</rslt><UseLocation><![CDATA[$use_location]]></UseLocation><rsltMsg>$rslt_msg</rsltMsg>");
		
	}
	
	function build_run() {
		
		$pcm = new COM("PCMServer.PCMServer") or die ("connection create fail");
		//global $pcm;
		
		$ziparray = explode(",",$_POST['ziplist']);
		
		$traveldist = 0;
		$travel_time = 0;
		$stoparray_dist = array();
		$stoparray_time = array();
		try {
			$trip = $pcm->NewTrip("NA");
			
			$last_zip = "";
			$stop_counter = 0;
			$counter = 0;
			$first_stop = "";
			$stop_minutes = 0;
			foreach($ziparray as $zip) {
				if($first_stop == '' && $zip != '') $first_stop = $zip;
				if($zip != '') {
					if($stop_counter) {
						if($_POST['hub_run'] == '1') {
							$stoparray_dist[$counter] = $pcm->CalcDistance3($first_stop, $zip, 0, $stop_minutes) / 10;
						} else {
							$stoparray_dist[$counter] = $pcm->CalcDistance3($last_zip, $zip, 0, $stop_minutes) / 10;
						}
					}
					$trip->AddStop($zip);
					$last_zip = $zip;
					$stop_counter++;
				}
				$counter++;
			}
			
			$options = $trip->GetOptions();
			$options->RouteType = 0;
			$options->Hub = ($_POST['hub_run'] == '1' ? true : false);
			
			$traveldist = $trip->TravelDistance() / 10;
			$travel_time = $trip->TravelTime() / 60.0;
			
			$rslt = 1;
		} catch (Exception $e) {
			$rslt = 0;
		}
		
		$return_val = "
			<rslt>$rslt</rslt>
			<Miles>$traveldist</Miles>
			<TravelTime>$travel_time</TravelTime>
		";
		
		foreach($stoparray_dist as $stop) {
			$return_val .= "
				<StopEntry>
					<StopDistance>$stop</StopDistance>
					<StopLine></StopLine>
				</StopEntry>
			";
		}
		
		display_xml_response($return_val);
		
	}

?>