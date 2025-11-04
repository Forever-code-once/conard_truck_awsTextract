<?
//functions for lane analyzer...Empty File Added Sept. 2013, code added 2014.

function mrr_process_zip_code_to_zip_code($zip1,$zip2,$city1="",$state1="",$city2="",$state2="",$no_api=0)
{			
	$zip1=trim($zip1);	
	$zip2=trim($zip2);
	
	if($zip1!="00000" && $zip1!="#####")
	{
     	$sql="
     		select city,state
     		from gps_to_zip_code
     		where zip_code='".sql_friendly((int)$zip1)."'
     		order by id desc		
     	";
     	$data=simple_query($sql);
          if($rowc=mysqli_fetch_array($data))
          {
          	$city1=trim($rowc['city']);			$state1=trim($rowc['state']);	
          }
	}
	if($zip2!="00000" && $zip2!="#####")
	{
     	$sql="
     		select city,state
     		from gps_to_zip_code
     		where zip_code='".sql_friendly((int)$zip2)."'
     		order by id desc		
     	";
     	$data=simple_query($sql);
          if($rowc=mysqli_fetch_array($data))
          {
          	$city2=trim($rowc['city']);			$state2=trim($rowc['state']);	
          }	
	}
	
	$found_stored=0;
	$miles=0;
	$timer="00:00";
     $sub="";
     $sub2="";
     $linker="";
     
     if($no_api > 0)		$found_stored=1;		//skip API call...used to prevent load board from running the API...should only used values if stored.
     
     //check if already stored in database for this trip...either direction
	if($zip1!="00000" && $zip1!="#####" && $zip2!="00000" && $zip2!="#####" && $zip1!=$zip2)
	{	
		$sql="
     		select miles,timer
     		from ".mrr_find_log_database_name()."zip_to_zip_trips
     		where deleted=0
     			and (
     				(zip_code_1='".sql_friendly((int)$zip1)."' and zip_code_2='".sql_friendly((int)$zip2)."')
     				or 
     				(zip_code_2='".sql_friendly((int)$zip1)."' and zip_code_1='".sql_friendly((int)$zip2)."')
     				)
     		order by id desc		
     	";
     	$data=simple_query($sql);
          if($rowc=mysqli_fetch_array($data))
          {
          	$miles=$rowc['miles'];
          	$timer=substr($rowc['timer'],0,5);
          	$found_stored=1;	//found stored value...no need for URL call/processing.
          	$linker="<b>Found Stored: (".$zip1." - ".$zip2.") = ".$miles.".</b>";
          }
	}
	
	//if they match, distance is 0 between zip codes.
	if($zip1==$zip2)
	{
		$found_stored=1;	//no need to find distance at all...
		$linker="<b>Zip Codes Match: ".$zip1." = ".$zip2.".</b>";
	}
		
	//use TruckMiles.com site to pull mileage.
	if($found_stored==0 && $zip1!=$zip2)
	{
     	$prime_url="http://www.truckmiles.com/prodrivf.exe";
     	
     	$curl_handle=curl_init();		
     	
     	curl_setopt($curl_handle, CURLOPT_URL,$prime_url);
     	//	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,10);
     	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);	
     		
     	$post_cmd="source=PRM&OriginCity=".$city1."&OriginState=".$state1."&OriginZip=".$zip1."&DestCity=".$city2."&DestState=".$state2."&DestZip=".$zip2."&Resultsin=Miles&RouteMethod=Truck_Practical".
     				"&PerMileRate=0.50&AvoidToll=OFF&Resultsas=RouteOnly&MPG=5.5&load_worth=300&my_cut=100&opcost=.55&B1.x=15&B1.y=10";
     							
     	curl_setopt($curl_handle, CURLOPT_POST,1);
     	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); // we don't want to stop the page from loading if there is ever an issue with the SSL
     	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_cmd);
     
     	$page = curl_exec($curl_handle);
     	curl_close($curl_handle);	
     	
     	$pos1=strpos($page,"Trip Results:",100);
     	$pos2=0;	
     	if($pos1 > 0)		$pos2=strpos($page,"Trip Charge:",$pos1);
     	
     	$sub2="";
     	if($pos1 > 0 && $pos2 > 0 && $pos2 > $pos1)
     	{
     		$sub=substr($page,$pos1,($pos2 - $pos1));
     		$sub=str_replace(chr(9)," ",$sub);
     		$sub=str_replace(chr(10)," ",$sub);
     		$sub=str_replace(chr(13)," ",$sub);
     		$sub=str_replace("|","",$sub);
     		$sub=str_replace("&nbsp;"," ",$sub);
     		$sub=trim(strip_tags($sub));
     		     			
     		$pos4=0;
     		$pos3=strpos($sub,"Miles:");
     		if($pos3 > 0)		$pos4=strpos($sub,"Time:",$pos3);
     		
     		$timer="00:00:00";
     		
     		if($pos3 > 0 && $pos4 > 0 && $pos4 > $pos3)
     		{
     			$sub2=substr($sub,$pos3,($pos4 - $pos3));	
     			$sub2=trim(str_replace("Miles:","",$sub2));
     			$sub2=trim(str_replace("Time:","",$sub2));
     			    			
     			$miles=$sub2;
     			
     			$sub3=substr($sub,$pos4);	
     			$sub3=trim(str_replace("Time:","",$sub3));
     			
     			$timer=$sub3;
     		}			
     	}
     	
     	$linker="<a href='".$prime_url."?".$post_cmd."' target='_blank'>".$prime_url."?".$post_cmd."</a>: [".$miles."]";
     	
     	if(is_numeric($miles) && $miles > 0)
     	{    //store this value for nex time... 	
          	$sql="
          		insert into ".mrr_find_log_database_name()."zip_to_zip_trips
          			(id,
          			linedate_added,
          			zip_code_1,
          			zip_code_2,
          			miles,
          			timer,
          			deleted)
          		values 
          			(NULL,
          			NOW(),
          			'".sql_friendly((int)$zip1)."',
          			'".sql_friendly((int)$zip2)."',
          			'".sql_friendly($miles)."',
          			'".sql_friendly($timer)."',
          			0)	
          	";
          	simple_query($sql);
     	}
	}
	$res['miles']=$miles;
	$res['time']=$timer;
	$res['sub']=$sub;
	$res['sub2']=$sub2;
	$res['link']=$linker;
	$res['city1']=$city1;
	$res['state1']=$state1;
	$res['city2']=$city2;
	$res['state2']=$state2;
	return $res;	
}

function mrr_calc_time_into_hours($time="")
{
	$hours=0;
	$minutes=0;
	
	$time=trim($time);
	if($time!="" && $time!="00:00" && $time!="0:00" && $time!="00:00:00" && $time!="0:00:00")
	{		
		if(strlen($time) > 5)		$time=substr($time,0,5);		//drop seconds...
		
		if(substr_count($time,":") > 1)
		{	//remove any extra text.
			$poser=strrpos($time,":");
			if($poser>0)	$time=trim(substr($time,0,$poser));	
		}
		
		if(substr_count($time,":00") == 0 && substr_count($time,":") > 0)
		{	//find minutes			
			$poser=strpos($time,":");			
			if($poser>0)
			{
				$hr=trim(substr($time,0,$poser));
				$min=trim(substr($time,($poser+1)));	
				
				if((int) $hr > 0)	$hours=(int) $hr;				
				if((int) $min > 0)	$hours+=(((int) $min)/ 60);
			}	
		}	
		else
		{
			$time=str_replace(":00","",$time);
			$hours=(int) $time;	
		}		
	}	
	return $hours;
}

function mrr_compute_load_tracking_avgs($date_from="",$date_to="",$customer_id=0)
{
	$loads=0;
	$stops=0;
	$avg=0;
	$score=0;
	
	//subcategories come from decoder function ...  mrr_load_stop_grade_decoder($id=0).... in functions.php file.
	$ungraded=0;																										//0 ...Ungraded
	$epic_fail=0;		$epic_truck=0;		$epic_trailer=0;	$epic_driver=0;	$epic_traffic=0;	$epic_pickup=0;	$epic_other=0;		//1	for($i=10; $i<= 15; $i++)	...Epic Fail		...reasons are the sub cats...
	$past_due=0;		$past_truck=0;		$past_trailer=0;	$past_driver=0;	$past_traffic=0;	$past_pickup=0;	$past_other=0;		//2	for($i=20; $i<= 25; $i++)	...Past Due		...reasons are the sub cats...
	$very_late=0;		$very_truck=0;		$very_trailer=0;	$very_driver=0;	$very_traffic=0;	$very_pickup=0;	$very_other=0;		//3	for($i=30; $i<= 35; $i++)	...Very late		...reasons are the sub cats...
	$late_stop=0;		$late_truck=0;		$late_trailer=0;	$late_driver=0;	$late_traffic=0;	$late_pickup=0;	$late_other=0;		//4  for($i=40; $i<= 45; $i++)	...Late			...reasons are the sub cats...
	$on_time=0;																										//5 ...On Time
	$in_window=0;																										//6 ...Within Window
	$early_stop=0;																										//7 ...Early
	$very_early=0;																										//8 ...Very Early
	$cancelled=0;																										//9 ...Canceled
	
	$score_none=0;
	$score_good=100;
	$score_late=75;
	$score_very=50;
	$score_past=25;
	$score_epic=0;
	
	$label="<h2>";
	if($customer_id > 0)
	{
     	$sql="
          	select name_company
          	from customers
          	where id='".sql_friendly($customer_id)."'	
          ";
          $data=simple_query($sql);
          if($row=mysqli_fetch_array($data))
          {     
              	$label.=trim($row['name_company']);
     	}
	}
	if($date_from!="" && $date_to!="")		$label.=" from ".$date_from." to ".$date_to."";
	elseif($date_from!="")				$label.=" from ".$date_from."";
	elseif($date_to!="")				$label.=" to ".$date_to."";
	$label.="</h2>";
	
	
	
	//get info and tallies here...
	$last_load=0;
	$sql="
     	select load_handler_stops.stop_grade_id,
     		load_handler_stops.grade_fault_id,
     		load_handler_stops.load_handler_id
     	from load_handler_stops
     		left join load_handler on load_handler.id=load_handler_stops.load_handler_id
     	where load_handler_stops.deleted=0
     		and load_handler.deleted=0
     		and load_handler_stops.load_handler_id > 0
     		".($customer_id > 0  ? " and load_handler.customer_id='".sql_friendly($customer_id)."'" : "")."
     		".($date_from!=""  ? " and load_handler_stops.linedate_pickup_eta>='".date("Y-m-d",strtotime($date_from))." 00:00:00'" : "")."
     		".($date_to!=""  ? " and load_handler_stops.linedate_pickup_eta<='".date("Y-m-d",strtotime($date_to))." 23:59:59'" : "")."
     	order by load_handler_stops.load_handler_id asc,
     		load_handler_stops.linedate_pickup_eta asc		
     ";
     $res['sql']=$sql;
     $data=simple_query($sql);
     while($row=mysqli_fetch_array($data))
     {         	
         	if($row['load_handler_id']!=$last_load)		$loads++;
         	$last_load=$row['load_handler_id'];
         	
         	$cur_score=0;
         	$grade_id=$row['stop_grade_id'];	
         	
         	//set the score based on the grade set...
         	if($grade_id==0  ) 		{	$cur_score=$score_none;		$ungraded++;		}	//0 ...Ungraded
         	elseif($grade_id==1  ) 	{	$cur_score=$score_epic;		$epic_fail++;		}	//1 ...Epic Fail
         	elseif($grade_id==10  ) 	{	$cur_score=$score_epic;		$epic_truck++;		}
         	elseif($grade_id==11  ) 	{	$cur_score=$score_epic;		$epic_trailer++;	}
         	elseif($grade_id==12  ) 	{	$cur_score=$score_epic;		$epic_driver++;	}
         	elseif($grade_id==13  ) 	{	$cur_score=$score_epic;		$epic_traffic++;	}
         	elseif($grade_id==14  ) 	{	$cur_score=$score_epic;		$epic_pickup++;	}
         	elseif($grade_id==15  ) 	{	$cur_score=$score_epic;		$epic_other++;		}
         	elseif($grade_id==2  ) 	{	$cur_score=$score_past;		$past_due++;		}	//2 ...Past Due
         	elseif($grade_id==20  ) 	{	$cur_score=$score_past;		$past_truck++;		}
         	elseif($grade_id==21  ) 	{	$cur_score=$score_past;		$past_trailer++;	}
         	elseif($grade_id==22  ) 	{	$cur_score=$score_past;		$past_driver++;	}
         	elseif($grade_id==23  ) 	{	$cur_score=$score_past;		$past_traffic++;	}
         	elseif($grade_id==24  ) 	{	$cur_score=$score_past;		$past_pickup++;	}
         	elseif($grade_id==25  ) 	{	$cur_score=$score_past;		$past_other++;		}
         	elseif($grade_id==3  ) 	{	$cur_score=$score_very;		$very_late++;		}	//3 ...Very late
         	elseif($grade_id==30  ) 	{	$cur_score=$score_very;		$very_truck++;	}
         	elseif($grade_id==31  ) 	{	$cur_score=$score_very;		$very_trailer++;	}
         	elseif($grade_id==32  ) 	{	$cur_score=$score_very;		$very_driver++;	}
         	elseif($grade_id==33  ) 	{	$cur_score=$score_very;		$very_traffic++;	}
         	elseif($grade_id==34  ) 	{	$cur_score=$score_very;		$very_pickup++;	}
         	elseif($grade_id==35  ) 	{	$cur_score=$score_very;		$very_other++;		}
         	elseif($grade_id==4  ) 	{	$cur_score=$score_late;		$late_stop++;		}	//4 ...Late
         	elseif($grade_id==40  ) 	{	$cur_score=$score_late;		$late_truck++;		}
         	elseif($grade_id==41  ) 	{	$cur_score=$score_late;		$late_trailer++;	}
         	elseif($grade_id==42  ) 	{	$cur_score=$score_late;		$late_driver++;	}
         	elseif($grade_id==43  ) 	{	$cur_score=$score_late;		$late_traffic++;	}
         	elseif($grade_id==44  ) 	{	$cur_score=$score_late;		$late_pickup++;	}
         	elseif($grade_id==45  ) 	{	$cur_score=$score_late;		$late_other++;		}
         	elseif($grade_id==5  ) 	{	$cur_score=$score_good;		$on_time++;		}	//5 ...On Time
         	elseif($grade_id==6  ) 	{	$cur_score=$score_good;		$in_window++;		}	//6 ...Within Window
         	elseif($grade_id==7  ) 	{	$cur_score=$score_good;		$early_stop++;		}	//7 ...Early
         	elseif($grade_id==8  ) 	{	$cur_score=$score_good;		$very_early++;		}	//8 ...Very Early
         	elseif($grade_id==9  ) 	{	$cur_score=$score_none;		$cancelled++;		}	//9 ...Cancelled
         	        	
         	$score+=$cur_score;
         	
         	$stops++;
     }
     
     $no_score=$cancelled + $ungraded;
     $use_stops=$stops - $no_score;
     $avg=0;
	if($use_stops > 0)		$avg= ($score / $use_stops);
	
	//display
	$tab="";
	
	$tab.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
	$tab.="
		<tr>
			<td valign='top' colspan='10'>".$label."</td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top'><b>Stops</b></td>
			<td valign='top'><b>Grade</b></td>
			<td valign='top' align='right'><b>Truck Breakdown</b></td>
			<td valign='top' align='right'><b>Trailer Breakdown</b></td>
			<td valign='top' align='right'><b>Driver Issue</b></td>
			<td valign='top' align='right'><b>Traffic</b></td>
			<td valign='top' align='right'><b>Pickup Delayed</b></td>
			<td valign='top' align='right'><b>Other (Specify)</b></td>
			<td valign='top' align='right'><b>Unspecified</b></td>
			<td valign='top' align='right'><b>Total</b></td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top'><b>Ungraded</b></td>
			<td valign='top'>".$score_none."% - Excluded</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>".$ungraded."</td>
		</tr>
	";	
	
	$tab.="
		<tr>
			<td valign='top'><b>Epic Fail</b></td>
			<td valign='top'>".$score_epic."</td>
			<td valign='top' align='right'>".$epic_truck."</td>
			<td valign='top' align='right'>".$epic_trailer."</td>
			<td valign='top' align='right'>".$epic_driver."</td>
			<td valign='top' align='right'>".$epic_traffic."</td>
			<td valign='top' align='right'>".$epic_pickup."</td>
			<td valign='top' align='right'>".$epic_other."</td>
			<td valign='top' align='right'>".$epic_fail."</td>
			<td valign='top' align='right'>".($epic_fail + $epic_truck + $epic_trailer + $epic_driver + $epic_traffic + $epic_pickup + $epic_other)."</td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top'><b>Past Due</b></td>
			<td valign='top'>".$score_past."</td>
			<td valign='top' align='right'>".$past_truck."</td>
			<td valign='top' align='right'>".$past_trailer."</td>
			<td valign='top' align='right'>".$past_driver."</td>
			<td valign='top' align='right'>".$past_traffic."</td>
			<td valign='top' align='right'>".$past_pickup."</td>
			<td valign='top' align='right'>".$past_other."</td>
			<td valign='top' align='right'>".$past_due."</td>
			<td valign='top' align='right'>".($past_due + $past_truck + $past_trailer + $past_driver + $past_traffic + $past_pickup + $past_other)."</td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top'><b>Very Late</b></td>
			<td valign='top'>".$score_very."%</td>
			<td valign='top' align='right'>".$very_truck."</td>
			<td valign='top' align='right'>".$very_trailer."</td>
			<td valign='top' align='right'>".$very_driver."</td>
			<td valign='top' align='right'>".$very_traffic."</td>
			<td valign='top' align='right'>".$very_pickup."</td>
			<td valign='top' align='right'>".$very_other."</td>
			<td valign='top' align='right'>".$very_late."</td>
			<td valign='top' align='right'>".($very_late + $very_truck + $very_trailer + $very_driver + $very_traffic + $very_pickup + $very_other)."</td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top'><b>Late</b></td>
			<td valign='top'>".$score_late."%</td>
			<td valign='top' align='right'>".$late_truck."</td>
			<td valign='top' align='right'>".$late_trailer."</td>
			<td valign='top' align='right'>".$late_driver."</td>
			<td valign='top' align='right'>".$late_traffic."</td>
			<td valign='top' align='right'>".$late_pickup."</td>
			<td valign='top' align='right'>".$late_other."</td>
			<td valign='top' align='right'>".$late_stop."</td>
			<td valign='top' align='right'>".($late_stop + $late_truck + $late_trailer + $late_driver + $late_traffic + $late_pickup + $late_other)."</td>
		</tr>
	";	
	
	$tab.="
		<tr>
			<td valign='top'><b>On Time</b></td>
			<td valign='top'>".$score_good."%</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>".$on_time."</td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top'><b>Within Window</b></td>
			<td valign='top'>".$score_good."%</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>".$in_window."</td>
		</tr>
	";		
	$tab.="
		<tr>
			<td valign='top'><b>Early</b></td>
			<td valign='top'>".$score_good."%</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>".$early_stop."</td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top'><b>Very Early</b></td>
			<td valign='top'>".$score_good."%</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>".$very_early."</td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top'><b>Cancelled</b></td>
			<td valign='top'>".$score_none."% - Excluded</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>&nbsp;</td>
			<td valign='top' align='right'>".$cancelled."</td>
		</tr>
	";	
	
	$tab.="
		<tr>
			<td valign='top' colspan='10'><hr></td>
		</tr>
	";		
	$tab.="
		<tr>
			<td valign='top' colspan='2'><b>Totals</b></td>
			<td valign='top' align='right'>".($epic_truck + $past_truck + $very_truck + $late_truck)."</td>
			<td valign='top' align='right'>".($epic_trailer + $past_trailer + $very_trailer + $late_trailer)."</td>
			<td valign='top' align='right'>".($epic_driver + $past_driver + $very_driver +  $late_driver)."</td>
			<td valign='top' align='right'>".($epic_traffic + $past_traffic + $very_traffic + $late_traffic)."</td>
			<td valign='top' align='right'>".($epic_pickup + $past_pickup + $very_pickup + $late_pickup)."</td>
			<td valign='top' align='right'>".($epic_other + $past_other + $very_other + $late_other)."</td>
			<td valign='top' align='right'>".($epic_fail + $past_due + $very_late + $late_stop)."</td>
			<td valign='top' align='right'>".($stops)."</td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top' colspan='10'>&nbsp;</td>
		</tr>
	";		
	$tab.="
		<tr>
			<td valign='top' colspan='2'><b>Score</b></td>
			<td valign='top' align='right' colspan='7'>&nbsp;</td>
			<td valign='top' align='right'>".($score)."</td>
		</tr>
	";	
	$tab.="
		<tr>
			<td valign='top' colspan='2'><b>Average</b></td>
			<td valign='top' align='right' colspan='7'>&nbsp;</td>
			<td valign='top' align='right'>".number_format($avg,2)."%</td>
		</tr>
	";	
	$tab.="</table>";
	
	$res['loads']=$loads;	
	$res['stops']=$stops;
	$res['score']=$score;
	$res['avg']=$avg;
		
	$res['Ungraded']=$ungraded;
	$res['Epic_Fail']=($epic_fail + $epic_truck + $epic_trailer + $epic_driver + $epic_traffic + $epic_pickup + $epic_other);
	$res['Past_Due']=($past_due + $past_truck + $past_trailer + $past_driver + $past_traffic + $past_pickup + $past_other);
	$res['Very_Late']=($very_late + $very_truck + $very_trailer + $very_driver + $very_traffic + $very_pickup + $very_other);
	$res['Late']=($late_stop + $late_truck + $late_trailer + $late_driver + $late_traffic + $late_pickup + $late_other);
	$res['On_Time']=$on_time;
	$res['Within_Window']=$in_window;
	$res['Early']=$early_stop;
	$res['Very_Early']=$very_early;
	$res['Cancelled']=$cancelled;
	
	$res['Basic']=($epic_fail + $past_due + $very_late + $late_stop);
	$res['Truck_Breakdown']=($epic_truck + $past_truck + $very_truck + $late_truck);
	$res['Trailer_Breakdown']=($epic_trailer + $past_trailer + $very_trailer + $late_trailer);
	$res['Driver_Issue']=($epic_driver + $past_driver + $very_driver +  $late_driver);
	$res['Traffic']=($epic_traffic + $past_traffic + $very_traffic + $late_traffic);
	$res['Pickup_Delayed']=($epic_pickup + $past_pickup + $very_pickup + $late_pickup);
	$res['Other_Specify']=($epic_other + $past_other + $very_other + $late_other);
	
	$res['tab']=$tab;
	
	return $res;
}

function mrr_find_equip_current_location($type,$id=0)
{
	$location="Conard Lot?";	
	if($id==0)		return $location;
	
	if($type==1 || $type==58)
	{	//trucks
		/*
		//Disabled the PN version.
		$res=mrr_find_only_location_of_this_truck($id);

		//$long=$res['longitude'];
		//$lat=$res['longitude'];
		$location=$res['location'];
		//$truck_name=$res['truck_name'];
		//$map="";
		//$map=mrr_gen_truck_local_map_by_google($load_id,$truck_name,$lat,$long,$truck_id);

		//$txt="Current Position: ".$location."".$map."";	// Lat: (".$lat.") and Long: (".$long.")
		*/
		
		$sql = "
			select geotab_current_location
			from trucks
			where deleted = 0
				and trucks.id = '".sql_friendly($id) ."'
		";
		$data = simple_query($sql);
		if($row = mysqli_fetch_array($data))
		{
			$location=trim($row['geotab_current_location']);
		}	
	}
	elseif($type==2 || $type==59)
	{	//trailers
		$sql = "
			select trailers_dropped.*,
				customers.name_company
				
			from trailers_dropped
				left join customers on customers.id = trailers_dropped.customer_id
			where trailer_id = '".sql_friendly($id)."'
				and trailers_dropped.drop_completed = 0
				and trailers_dropped.deleted = 0
				and (
					(linedate_completed='0000-00-00 00:00:00' and linedate <='".date("Y-m-d")." 23:59:59')
					or
					(linedate <='".date("Y-m-d")." 23:59:59' or linedate_completed >='".date("Y-m-d")." 00:00:00')
				)
		";
		$data_dropped = simple_query($sql);     		
		if(mysqli_num_rows($data_dropped)) 
		{
			$row_dropped = mysqli_fetch_array($data_dropped);
			$location = "".$row_dropped['name_company'].": ".$row_dropped['location_city'].", ".$row_dropped['location_state']." ".$row_dropped['location_zip'].". ".trim($row_dropped['notes'])."";
		}
	}			
	return trim($location);
}
function mrr_find_equip_current_pmi_fed($type,$id=0)
{
	$res['tires']="";	
	$res['pmi']="";	
	$res['fed']="";	
	if($id==0)		return $res;
	
	if($type==1 || $type==58)
	{	//trucks
		$sql = "
			select tire_size,
				pm_inspection_date,
				fd_inspection_date
			from trucks
			where id = '".sql_friendly($id)."'
		";
		$data = simple_query($sql);     		
		if(mysqli_num_rows($data)) 
		{
			$row = mysqli_fetch_array($data);
			
			$res['tires']=trim($row['tire_size']);	
			$res['pmi']="";		if(trim($row['pm_inspection_date']) != "0000-00-00 00:00:00")		$res['pmi']=date("m/d/Y",strtotime($row['pm_inspection_date']));	
			$res['fed']="";		if(trim($row['fd_inspection_date']) != "0000-00-00 00:00:00")		$res['pmi']=date("m/d/Y",strtotime($row['fd_inspection_date']));	
		}
	}
	elseif($type==2 || $type==59)
	{	//trailers
		$sql = "
			select pmi_test_ignore,
				trailer_tire_size,
				linedate_last_pmi,
				fed_test_ignore,
				linedate_last_fed		
			from trailers
			where id = '".sql_friendly($id)."'
		";
		$data = simple_query($sql);     		
		if(mysqli_num_rows($data)) 
		{
			$row = mysqli_fetch_array($data);
			
			$res['tires']=trim($row['trailer_tire_size']);	
			$res['pmi']="";		if(trim($row['linedate_last_pmi']) != "0000-00-00 00:00:00")		$res['pmi']=date("m/d/Y",strtotime($row['linedate_last_pmi']));	
			$res['fed']="";		if(trim($row['linedate_last_fed']) != "0000-00-00 00:00:00")		$res['fed']=date("m/d/Y",strtotime($row['linedate_last_fed']));	
			
			if($row['pmi_test_ignore'] > 0)		$res['pmi']="N/A";
			if($row['fed_test_ignore'] > 0)		$res['fed']="N/A";
		}
	}			
	return $res;
}


//Trailer Inspections
function mrr_delete_trailer_inspection($id)
{
	if($id>0)
	{
		$sql = "
			update maint_inspect_trailers set
				deleted=1
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);
	}	
}
function mrr_pass_trailer_inspection($id,$passed=0,$maint_id=0,$trailer_id=0)
{
	if($id>0)
	{
		$sql = "
			update maint_inspect_trailers set
				passed='".sql_friendly($passed)."',
				user_updated='".sql_friendly($_SESSION['user_id'])."',
				linedate_updated=NOW()
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);
		
		if($passed > 0 && $maint_id > 0 && $trailer_id > 0)
		{
			$res=mrr_form_trailer_inspection_list($maint_id);		//$res['used_pmi'] > 0        $res['used_fed'] > 0
			if($passed==1 || $passed==3)
			{
				$sql = "
          			update trailers set
          				linedate_last_pmi='".date("Y-m-d",time())." 00:00:00'
          			where id='".sql_friendly($trailer_id)."'
          		";
          		simple_query($sql);
			}
			if($passed==2 || $passed==3)
			{
				$sql = "
          			update trailers set
          				linedate_last_fed='".date("Y-m-d",time())." 00:00:00'
          			where id='".sql_friendly($trailer_id)."'
          		";
          		simple_query($sql);	
			}
		}
	}	
}
function mrr_update_trailer_inspection($id,$trailer_id,$maint_id,$ckbxs,$cds,$gen,$tread,$brake)
{
	global $datasource;

	if($id==0)
	{
		$sql = "
			insert into maint_inspect_trailers
				(id,
				linedate_added,
				inspector_id,
				trailer_id,
				maint_id,				
				passed,
				deleted)
			values 
				(NULL,
				NOW(),
				'".sql_friendly($_SESSION['user_id'])."',
				'".sql_friendly($trailer_id)."',
				'".sql_friendly($maint_id)."',				
				0,
				0)
		";
		simple_query($sql);
		$id=mysqli_insert_id($datasource);
	}
	
	if($id>0)
	{
		$sql = "
			update maint_inspect_trailers set
				
				qualify_section_396_19='".sql_friendly($gen['qualify_section_396_19'])."',
                    qualify_section_396_25='".sql_friendly($gen['qualify_section_396_25'])."',
                    
                    inspect_ck_reg='".sql_friendly($ckbxs['inspect_ck_reg'])."',
                    inspect_ck_body='".sql_friendly($ckbxs['inspect_ck_body'])."',
                    inspect_ck_frame='".sql_friendly($ckbxs['inspect_ck_frame'])."',
                    inspect_ck_rear='".sql_friendly($ckbxs['inspect_ck_rear'])."',
                    inspect_ck_susp='".sql_friendly($ckbxs['inspect_ck_susp'])."',
                    inspect_ck_brake='".sql_friendly($ckbxs['inspect_ck_brake'])."',
                    inspect_ck_wheel='".sql_friendly($ckbxs['inspect_ck_wheel'])."',
                    inspect_ck_tires='".sql_friendly($ckbxs['inspect_ck_tires'])."',
                    inspect_ck_light='".sql_friendly($ckbxs['inspect_ck_light'])."',
                    inspect_ck_decal='".sql_friendly($ckbxs['inspect_ck_decal'])."',
                    
                    inspect_ck_bpm_items='".sql_friendly($ckbxs['inspect_ck_bpm_items'])."',
                    inspect_ck_cpm_items='".sql_friendly($ckbxs['inspect_ck_cpm_items'])."',
                    inspect_ck_annual='".sql_friendly($ckbxs['inspect_ck_annual'])."',
                    inspect_ck_attach='".sql_friendly($ckbxs['inspect_ck_attach'])."',
                    
                    inspect_cd_reg='".sql_friendly($cds['inspect_cd_reg'])."',
                    inspect_cd_body='".sql_friendly($cds['inspect_cd_body'])."',
                    inspect_cd_frame='".sql_friendly($cds['inspect_cd_frame'])."',
                    inspect_cd_rear='".sql_friendly($cds['inspect_cd_rear'])."',
                    inspect_cd_susp='".sql_friendly($cds['inspect_cd_susp'])."',
                    inspect_cd_brake='".sql_friendly($cds['inspect_cd_brake'])."',                 
                    inspect_cd_wheel='".sql_friendly($cds['inspect_cd_wheel'])."',                   
                    inspect_cd_tires='".sql_friendly($cds['inspect_cd_tires'])."',             
                    inspect_cd_light='".sql_friendly($cds['inspect_cd_light'])."',              
                    inspect_cd_decal='".sql_friendly($cds['inspect_cd_decal'])."',
                    
                    inspect_cd_bpm_items='".sql_friendly($cds['inspect_cd_bpm_items'])."',                
                    inspect_cd_cpm_items='".sql_friendly($cds['inspect_cd_cpm_items'])."',                   
                    inspect_cd_annual='".sql_friendly($cds['inspect_cd_annual'])."',              
                    inspect_cd_attach='".sql_friendly($cds['inspect_cd_attach'])."',
                    
                    brake_left_front='".sql_friendly($brake['brake_left_front'])."',
                    brake_right_front='".sql_friendly($brake['brake_right_front'])."',
                    brake_left_rear='".sql_friendly($brake['brake_left_rear'])."',
                    brake_right_rear='".sql_friendly($brake['brake_right_rear'])."',
                    
                    tread_lfo='".sql_friendly($tread['tread_lfo'])."',
                    tread_lfi='".sql_friendly($tread['tread_lfi'])."',
                    tread_rfi='".sql_friendly($tread['tread_rfi'])."',
                    tread_rfo='".sql_friendly($tread['tread_rfo'])."',
                    tread_lro='".sql_friendly($tread['tread_lro'])."',
                    tread_lri='".sql_friendly($tread['tread_lri'])."',
                    tread_rri='".sql_friendly($tread['tread_rri'])."',
                    tread_rro='".sql_friendly($tread['tread_rro'])."',
				
				user_updated='".sql_friendly($_SESSION['user_id'])."',
				linedate_updated=NOW()
				
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);
	}
	return $id;	
}
function mrr_form_trailer_inspection($id)
{
	$tab="<input type='hidden' name='inspect_id' id='inspect_id' value='".$id."'>";
	$left_col="";
	$right_col="";
	$right_col1="";
	$right_col2="";
	
	$trailer_name="";
	if($id > 0)
	{
		$sql = "
     		select trailers.trailer_name,trailers.nick_name
     		from trailers
     			left join maint_inspect_trailers on maint_inspect_trailers.trailer_id=trailers.id
     		where maint_inspect_trailers.id='".sql_friendly($id)."'
     	";
     	$data=simple_query($sql);
     	if($row=mysqli_fetch_array($data))
     	{     		
     		$trailer_name=trim($row['nick_name']);	
     		
     		if($trailer_name=="")		$trailer_name=trim($row['trailer_name']);
     	}
	}
	$tab.="<div class='hide_from_printer'><span class='mrr_link_like_on' onClick='mrr_inspection_display(1);'>Show Inspection</span> or <span class='mrr_link_like_on' onClick='mrr_inspection_display(0);'>Hide Inspection</span></div>";
	$tab.="<table cellpadding='0' cellspacing='0' border='0' width='100%' class='trailer_inspection_form'>";
	$tab.="<tr><td colspan='2' align='center'><h2>Conard Transportation, Inc. Trailer <b>".$trailer_name."</b> PM/AVI Inspection</h2></td></tr>";
	
	$sql_21="select id as use_val,fvalue as use_disp from option_values where cat_id=21 and deleted=0 order by fvalue asc";
	$sql_22="select id as use_val,fname as use_disp from option_values where cat_id=22 and deleted=0 order by fname asc";
		
	if($id > 0)
	{	
		$sql = "
     		select maint_inspect_trailers.*,
     			(select username from users where users.id=maint_inspect_trailers.inspector_id) as created_by,
     			(select username from users where users.id=maint_inspect_trailers.user_updated) as updated_by
     		from maint_inspect_trailers
     		where id='".sql_friendly($id)."'
     	";
     	$data=simple_query($sql);
     	$row=mysqli_fetch_array($data);
     	
     	$left_col.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<b>Trailer B-PM Instructions (PMI)</b>
     				</td>";
     	$left_col.=	"<td valign='top'>
     					<b>Inspected</b>
     				</td>";
     	$left_col.=	"<td valign='top'>
     					<b>Repair Code</b>
     				</td>";
     	$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_reg'>1. Registration and Inspection</label> ".show_help('maint.php','Registration Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_reg' id='inspect_ck_reg' value='1'".($row['inspect_ck_reg'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_reg',$row['inspect_cd_reg'],'__','')."</td>";
     	$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_body'>2. Body Inspection</label> ".show_help('maint.php','Body Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_body' id='inspect_ck_body' value='1'".($row['inspect_ck_body'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_body',$row['inspect_cd_body'],'__','')."</td>";
     	$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_frame'>3. Frame Inspection</label> ".show_help('maint.php','Frame Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_frame' id='inspect_ck_frame' value='1'".($row['inspect_ck_frame'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_frame',$row['inspect_cd_frame'],'__','')."</td>";
     	$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_rear'>4. Rear Coupler Inspection</label> ".show_help('maint.php','Rear Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_rear' id='inspect_ck_rear' value='1'".($row['inspect_ck_rear'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_rear',$row['inspect_cd_rear'],'__','')."</td>";
     	$left_col.="</tr>";     	
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_susp'>5. Suspension</label> ".show_help('maint.php','Suspension Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_susp' id='inspect_ck_susp' value='1'".($row['inspect_ck_susp'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_susp',$row['inspect_cd_susp'],'__','')."</td>";
     	$left_col.="</tr>";
     	
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_brake'>6. Inspect Brakes</label> ".show_help('maint.php','Brake Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_brake' id='inspect_ck_brake' value='1'".($row['inspect_ck_brake'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_brake',$row['inspect_cd_brake'],'__','')."</td>";
     	$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_wheel'>7. Wheels and Hubs</label> ".show_help('maint.php','Wheel Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_wheel' id='inspect_ck_wheel' value='1'".($row['inspect_ck_wheel'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_wheel',$row['inspect_cd_wheel'],'__','')."</td>";
     	$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_tires'>8. Tires</label> ".show_help('maint.php','Tires Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_tires' id='inspect_ck_tires' value='1'".($row['inspect_ck_tires'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_tires',$row['inspect_cd_tires'],'__','')."</td>";
     	$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_light'>9. Light and Reflectors</label> ".show_help('maint.php','Light Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_light' id='inspect_ck_light' value='1'".($row['inspect_ck_light'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_light',$row['inspect_cd_light'],'__','')."</td>";
     	$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'>
     					<label for='inspect_ck_decal'>10. Install a PM Decal</label> ".show_help('maint.php','Decal Inspection')."
     				</td>";
     	$left_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_decal' id='inspect_ck_decal' value='1'".($row['inspect_ck_decal'] > 0 ? " checked" : "")."></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_decal',$row['inspect_cd_decal'],'__','')."</td>";
     	$left_col.="</tr>";
		$left_col.="</table>";
		
		$right_col.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top'>
     					<b>Trailer C-PM Instructions (FED)</b>
     				</td>";
     	$right_col.=	"<td valign='top'>
     					<b>Inspected</b>
     				</td>";
     	$right_col.=	"<td valign='top'>
     					<b>Repair Code</b>
     				</td>";
     	$right_col.="</tr>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top'>
     					<label for='inspect_ck_bpm_items'>1. Complete all of the B-PM Service Items</label> ".show_help('maint.php','Completed B-PM Inspection')."
     				</td>";
     	$right_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_bpm_items' id='inspect_ck_bpm_items' value='1'".($row['inspect_ck_bpm_items'] > 0 ? " checked" : "")."></td>";
     	$right_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_bpm_items',$row['inspect_cd_bpm_items'],'__','')."</td>";
     	$right_col.="</tr>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top'>
     					<label for='inspect_ck_cpm_items'>2. Federal Annual Inspection requirements</label> ".show_help('maint.php','Annual Inspection')."
     				</td>";
     	$right_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_cpm_items' id='inspect_ck_cpm_items' value='1'".($row['inspect_ck_cpm_items'] > 0 ? " checked" : "")."></td>";
     	$right_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_cpm_items',$row['inspect_cd_cpm_items'],'__','')."</td>";
     	$right_col.="</tr>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top'>
     					<label for='inspect_ck_annual'>3. Install Federal Annual Inspection decal</label> ".show_help('maint.php','Federal Decal Inspection')."
     				</td>";
     	$right_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_annual' id='inspect_ck_annual' value='1'".($row['inspect_ck_annual'] > 0 ? " checked" : "")."></td>";
     	$right_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_annual',$row['inspect_cd_annual'],'__','')."</td>";
     	$right_col.="</tr>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top'>
     					<label for='inspect_ck_attach'>4. Attach PM sheet to invoice for payment...</label> ".show_help('maint.php','Attach Inspection')."
     				</td>";
     	$right_col.=	"<td valign='top'><input type='checkbox' name='inspect_ck_attach' id='inspect_ck_attach' value='1'".($row['inspect_ck_attach'] > 0 ? " checked" : "")."></td>";
     	$right_col.=	"<td valign='top'>".select_box_disp($sql_21,'inspect_cd_attach',$row['inspect_cd_attach'],'__','')."</td>";
     	$right_col.="</tr>";
     	$right_col.="</table>";
     	
     	
     	$right_col1.="<div style='border:1px solid black; padding:10px; margin:10px;'>";
     	$right_col1.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
     	$right_col1.="<tr>";
     	$right_col1.=	"<td valign='top' colspan='4' align='center'>
     					Brake Lining Remaining:
     					<br>
     					List brake lining as:
     					<br>New, 3/4\", 1/2\", 3/8\", or 1/4\" thick
     				</td>";
     	$right_col1.="</tr>";
     	$right_col1.="<tr>";
     	$right_col1.=	"<td valign='top'><b>Left Front</b></td>";
     	$right_col1.=	"<td valign='top'>".select_box_disp($sql_22,'brake_left_front',$row['brake_left_front'],'__','')."</td>";
     	$right_col1.=	"<td valign='top'><b>Right Front</b></td>";
     	$right_col1.=	"<td valign='top'>".select_box_disp($sql_22,'brake_right_front',$row['brake_right_front'],'__','')."</td>";
     	$right_col1.="</tr>";
     	$right_col1.="<tr>";
     	$right_col1.=	"<td valign='top'><b>Left Rear</b></td>";
     	$right_col1.=	"<td valign='top'>".select_box_disp($sql_22,'brake_left_rear',$row['brake_left_rear'],'__','')."</td>";
     	$right_col1.=	"<td valign='top'><b>Right Rear</b></td>";
     	$right_col1.=	"<td valign='top'>".select_box_disp($sql_22,'brake_right_rear',$row['brake_right_rear'],'__','')."</td>";
     	$right_col1.="</tr>";
     	$right_col1.="</table>";
     	$right_col1.="</div>";
     	
     	$right_col2.="<div style='border:0px solid black; padding:10px; margin:10px;'><h3>Tire Tread</h3><br>";
     	$right_col2.="<table cellpadding='0' cellspacing='0' border='1' width='100%'>";
     	$right_col2.="<tr>";
     	$right_col2.=	"<td valign='top' colspan='5' align='center'>Front Trailer Axle</td>";
     	$right_col2.="</tr>";
     	$right_col2.="<tr>";
     	$right_col2.=	"<td valign='top' colspan='2' align='center'>LEFT</td>";
     	$right_col2.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	$right_col2.=	"<td valign='top' colspan='2' align='center'>RIGHT</td>";
     	$right_col2.="</tr>";
     	$right_col2.="<tr>";
     	$right_col2.=	"<td valign='top' align='center'><input type='text' name='tread_lfo' id='tread_lfo' value='".$row['tread_lfo']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_lfo\");'><hr><b>32</b></td>";
     	$right_col2.=	"<td valign='top' align='center'><input type='text' name='tread_lfi' id='tread_lfi' value='".$row['tread_lfi']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_lfi\");'><hr><b>32</b></td>";
     	$right_col2.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	$right_col2.=	"<td valign='top' align='center'><input type='text' name='tread_rfi' id='tread_rfi' value='".$row['tread_rfi']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_rfi\");'><hr><b>32</b></td>";
     	$right_col2.=	"<td valign='top' align='center'><input type='text' name='tread_rfo' id='tread_rfo' value='".$row['tread_rfo']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_rfo\");'><hr><b>32</b></td>";
     	$right_col2.="</tr>";	     			
     	$right_col2.="<tr>";
     	$right_col2.=	"<td valign='top' colspan='5' align='center'>&nbsp;</td>";
     	$right_col2.="</tr>";
     	$right_col2.="<tr>";
     	$right_col2.=	"<td valign='top' align='center'><input type='text' name='tread_lro' id='tread_lro' value='".$row['tread_lro']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_lro\");'><hr><b>32</b></td>";
     	$right_col2.=	"<td valign='top' align='center'><input type='text' name='tread_lri' id='tread_lri' value='".$row['tread_lri']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_lri\");'><hr><b>32</b></td>";
     	$right_col2.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	$right_col2.=	"<td valign='top' align='center'><input type='text' name='tread_rri' id='tread_rri' value='".$row['tread_rri']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_rri\");'><hr><b>32</b></td>";
     	$right_col2.=	"<td valign='top' align='center'><input type='text' name='tread_rro' id='tread_rro' value='".$row['tread_rro']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_rro\");'><hr><b>32</b></td>";
     	$right_col2.="</tr>";
     	$right_col2.="<tr>";
     	$right_col2.=	"<td valign='top' colspan='2' align='center'>LEFT</td>";
     	$right_col2.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	$right_col2.=	"<td valign='top' colspan='2' align='center'>RIGHT</td>";
     	$right_col2.="</tr>";
     	$right_col2.="<tr>";
     	$right_col2.=	"<td valign='top' colspan='5' align='center'>Rear Trailer Axle</td>";
     	$right_col2.="</tr>";
     	$right_col2.="</table>";
     	$right_col2.="</div>";
     	
		
		
		$tab.="<tr><td colspan='2'><br><hr><br></td></tr>";
     	$tab.="<tr>
     			<td valign='top'>
     				* Trailer B-PM Service must be completed every 90 days and includes greasing the trailer. <b>(PMI)</b>
     				<br>
     				* Trailer C-PM includes the federal annual inspection that must be completed every 12 months. <b>(FED)</b>
     				<br>
     				Check the Inspected box when the item has been inspected, and select the repair code to make it pass. 
     				<br>
     				<b>* Repair Codes: {  R=Repairs Needed-Outside  |   R-In=Needs Repairs-Internal   |  Ok=Serviceable }</b>
     			</td>
     			<td valign='top' align='right'>
     				Inspection ".$row['id']."
     				<br>Created by ".$row['created_by']."
     				<br>On ".date("m/d/Y H:i",strtotime($row['linedate_added']))."
     			</td>
     		</tr>";
     	$tab.="<tr><td colspan='2'><br><hr><br></td></tr>";
		
		$tab.="<tr><td colspan='2'>
     				This Inspector meets the qualifications requirements in Section 396.19 
     				<input type='checkbox' name='qualify_section_396_19' id='qualify_section_396_19' value='1'".($row['qualify_section_396_19'] > 0 ? " checked" : "").">
     				<label for='qualify_section_396_19'>Yes Inspector Qualifications</label>
     		</td></tr>";
     	
     	$tab.="<tr><td colspan='2'>
     				This Inspector meets the qualifications requirements in Section 396.25 
     				<input type='checkbox' name='qualify_section_396_25' id='qualify_section_396_25' value='1'".($row['qualify_section_396_25']  > 0 ? " checked" : "").">
     				<label for='qualify_section_396_25'>Yes Brake Qualifications</label>
     		</td></tr>";
     	
     	$tab.="<tr><td colspan='2'><br>&nbsp;<br></td></tr>";
     	
     	$tab.="<tr><td valign='top' width='60%'>".$left_col."</td><td valign='top' width='40%'>".$right_col."<br>".$right_col1."<br>".$right_col2."</td></tr>";
     	
     	$tab.="<tr><td colspan='2'><br>&nbsp;<br></td></tr>";
     	/*    	
     	$tab.="<tr><td colspan='2'>
     				Certification: This vehicle has passed all the inspection items for the annual vehicle inspection in accordance with 49 CFR 396:
     				<input type='checkbox' name='passed' id='passed' value='1'".($row['passed']  > 0 ? " checked" : "").">
     				<label for='passed'>Yes, Passed</label>
     		</td></tr>";
     	*/	
     	
     	$selpassed="";     	
     	$selpassed.="<select name='passed' id='passed'>";
     	$selpassed.=	"<option value='0'".($row['passed']==0 ? " selected" : "").">Inspection Not Passed</option>";
     	$selpassed.=	"<option value='1'".($row['passed']==1 ? " selected" : "").">Passed B-PM Only</option>";
     	$selpassed.=	"<option value='2'".($row['passed']==2 ? " selected" : "").">Passed C-PM Only</option>";
     	$selpassed.=	"<option value='3'".($row['passed']==3 ? " selected" : "").">Passed B-PM and C-PM</option>";
     	$selpassed.="</select>";
     	
     	$tab.="<tr><td colspan='2'>
     				Certification: This vehicle has passed all the inspection items for the annual vehicle inspection in accordance with 49 CFR 396: ".$selpassed."
     		</td></tr>";
     	
     	$tab.="<tr class='hide_from_printer'><td colspan='2'><br>&nbsp;<br></td></tr>";
		
		$tab.="<tr class='hide_from_printer'>
				<td valign='top'>
					<input type='button' name='inspect_updater' id='inspect_updater' value='Update Inspection' onClick='mrr_update_this_inspection();'>
					&nbsp; &nbsp; &nbsp; &nbsp; 
					<input type='button' name='inspect_printer' id='inspect_printer' value='Print Inspection' onClick='mrr_print_this_inspection();'>
				</td>
				<td valign='top' align='right'>Last update by ".$row['updated_by']." on ".date("m/d/Y H:i",strtotime($row['linedate_updated']))."</td>
			</tr>";
		
		$tab.="<tr><td colspan='2'><br>&nbsp;<br></td></tr>";
	}
	else
	{
		$tab.="<tr><td colspan='2' align='center'>Please save Maint Request to enter the Trailer PM/AVI Inspection</td></tr>";
	}
	$tab.="</table>";
	
	return $tab;
}
function mrr_find_last_inspection($trailer_id)
{
	$maint_id=0;
	if($trailer_id > 0)
	{
		$sql = "
     		select maint_requests.id
     		from maint_requests
     			left join maint_inspect_trailers on maint_inspect_trailers.maint_id=maint_requests.id
     		where maint_requests.equip_type='59' 
     			and maint_requests.ref_id='".sql_friendly($trailer_id)."' 
     			and maint_requests.deleted=0
     			and maint_inspect_trailers.passed=0
     			and maint_inspect_trailers.linedate_updated > maint_inspect_trailers.linedate_added
     		order by maint_inspect_trailers.linedate_updated desc
     		limit 1
     	";
     	$data=simple_query($sql);
     	if($row=mysqli_fetch_array($data))
     	{
     		$maint_id=$row['id'];	
     	}	
     }
     return $maint_id;
}
function mrr_form_trailer_inspection_list($id)
{
	$res['used']=0;			//is there an inspection being used...
	$res['passed']=0;
	$res['created_by']="";
	$res['updated_by']="";
	$res['updated']="";
	$res['created']="";
	$res['inspection']=0;
	$res['used_pmi']=0;			//is there an inspection being used...PMI B-PM
	$res['used_fed']=0;			//is there an inspection being used...FED C-PM
	
	if($id > 0)
	{	
		$sql = "
     		select maint_inspect_trailers.*,
     			(select username from users where users.id=maint_inspect_trailers.inspector_id) as created_by,
     			(select username from users where users.id=maint_inspect_trailers.user_updated) as updated_by
     		from maint_inspect_trailers
     		where maint_id='".sql_friendly($id)."'
     	";
     	$data=simple_query($sql);
     	$row=mysqli_fetch_array($data);
     	
     	$res['inspection']=$row['id'];
     	$res['passed']=$row['passed'];
		$res['created_by']=trim($row['created_by']);		//$row['inspector_id']
		$res['updated_by']=trim($row['updated_by']);		//$row['user_updated']
		$res['updated']=date("m/d/Y",strtotime($row['linedate_updated']));
		$res['created']=date("m/d/Y H:i",strtotime($row['linedate_added']));     	
		
		//see if anything is used...if not, no inspection to show.  If anything is used, mark as such for display purposes.
		$is_used=0;	
		$pmi_used=0;	
		$fed_used=0;	
		
		$is_used+=$row['inspect_ck_reg'];			$pmi_used+=$row['inspect_ck_reg'];			//$is_used+=$row['inspect_cd_reg'];
		$is_used+=$row['inspect_ck_body'];			$pmi_used+=$row['inspect_ck_body'];		//$is_used+=$row['inspect_cd_body'];
		$is_used+=$row['inspect_ck_frame'];		$pmi_used+=$row['inspect_ck_frame'];		//$is_used+=$row['inspect_cd_frame'];
		$is_used+=$row['inspect_ck_rear'];			$pmi_used+=$row['inspect_ck_rear'];		//$is_used+=$row['inspect_cd_rear'];
		$is_used+=$row['inspect_ck_susp'];			$pmi_used+=$row['inspect_ck_susp'];		//$is_used+=$row['inspect_cd_susp'];
		$is_used+=$row['inspect_ck_brake'];		$pmi_used+=$row['inspect_ck_brake'];		//$is_used+=$row['inspect_cd_brake'];
		$is_used+=$row['inspect_ck_wheel'];		$pmi_used+=$row['inspect_ck_wheel'];		//$is_used+=$row['inspect_cd_wheel'];
		$is_used+=$row['inspect_ck_tires'];		$pmi_used+=$row['inspect_ck_tires'];		//$is_used+=$row['inspect_cd_tires'];
		$is_used+=$row['inspect_ck_light'];		$pmi_used+=$row['inspect_ck_light'];		//$is_used+=$row['inspect_cd_light'];
		$is_used+=$row['inspect_ck_decal'];		$pmi_used+=$row['inspect_ck_decal'];		//$is_used+=$row['inspect_cd_decal'];	
				
		$is_used+=$row['inspect_ck_bpm_items'];     	$fed_used+=$row['inspect_ck_bpm_items'];     //$is_used+=$row['inspect_cd_bpm_items'];
     	$is_used+=$row['inspect_ck_cpm_items'];     	$fed_used+=$row['inspect_ck_cpm_items'];     //$is_used+=$row['inspect_cd_cpm_items'];
     	$is_used+=$row['inspect_ck_annual'];     	$fed_used+=$row['inspect_ck_annual'];     	//$is_used+=$row['inspect_cd_annual'];
     	$is_used+=$row['inspect_ck_attach'];     	$fed_used+=$row['inspect_ck_attach'];     	//$is_used+=$row['inspect_cd_attach'];
     	     	
     	$is_used+=$row['brake_left_front'];   		$pmi_used+=$row['brake_left_front'];   	
     	$is_used+=$row['brake_right_front'];		$pmi_used+=$row['brake_right_front'];
     	$is_used+=$row['brake_left_rear'];			$pmi_used+=$row['brake_left_rear'];     	
     	$is_used+=$row['brake_right_rear'];		$pmi_used+=$row['brake_right_rear'];
     	
     	//$is_used+=$row['tread_lfo'];				$pmi_used+=$row['tread_lfo'];
     	//$is_used+=$row['tread_lfi'];				$pmi_used+=$row['tread_lfi'];
     	//$is_used+=$row['tread_rfi'];				$pmi_used+=$row['tread_rfi'];
     	//$is_used+=$row['tread_rfo'];				$pmi_used+=$row['tread_rfo'];
     	//$is_used+=$row['tread_lro'];				$pmi_used+=$row['tread_lro'];
     	//$is_used+=$row['tread_lri'];				$pmi_used+=$row['tread_lri'];
     	//$is_used+=$row['tread_rri'];				$pmi_used+=$row['tread_rri'];
     	//$is_used+=$row['tread_rro'];				$pmi_used+=$row['tread_rro'];
		
		$is_used+=$row['qualify_section_396_19'];
		$is_used+=$row['qualify_section_396_25']; 
		
		$res['used']=$is_used;
		$res['used_pmi']=$pmi_used;			
		$res['used_fed']=$fed_used;	
	}
	return $res;
}


//Truck Inspections:
function mrr_update_truck_inspection($id,$truck_id,$maint_id,$vtype,$brake,$tread,$driver_id=0,$rep_num='',$id_type=0,$id_num='',$inspect_name='',$inspect_local='',$pass=0,$meets_396_19_items=0)
{	
	global $defaultsarray;
	global $datasource;
	
	if($id==0)
	{
		$sql = "
			insert into maint_inspect_trucks
				(id,
				user_id,
				linedate_added,
				truck_id,
				report_number,
				driver_id,
				operator_name,
				operator_addr,
				operator_city,
				operator_state,
				operator_zip,
				vehicle_type,
				linedate,
				inspector_name,
				unit_id_type,
				unit_id_number,
				inspector_location,
				meets_396_19_items,
				user_signed,				
				maint_id,				
				passed,
				deleted)
			values 
				(NULL,
				'".sql_friendly($_SESSION['user_id'])."',
				NOW(),
				'".sql_friendly($truck_id)."',
				'".sql_friendly($rep_num)."',
				'".sql_friendly($driver_id)."',
				'".sql_friendly($defaultsarray['company_name'])."',
				'".sql_friendly($defaultsarray['company_address1'])."',
				'".sql_friendly($defaultsarray['company_city'])."',
				'".sql_friendly($defaultsarray['company_state'])."',
				'".sql_friendly($defaultsarray['company_zip'])."',
				'".sql_friendly($vtype)."',
				NOW(),
				'',
				0,
				'',
				'',
				0,
				0,				
				'".sql_friendly($maint_id)."',				
				0,
				0)
		";
		simple_query($sql);
		$id=mysqli_insert_id($datasource);    
	}
	
	if($id>0)
	{
          if(!isset($tread['tread_lso']))         $tread['tread_lso']="";
          if(!isset($tread['tread_rso']))         $tread['tread_rso']="";
          if(!isset($tread['tread_lfo']))         $tread['tread_lfo']="";
          if(!isset($tread['tread_lfi']))         $tread['tread_lfi']="";
          if(!isset($tread['tread_rfi']))         $tread['tread_rfi']="";
          if(!isset($tread['tread_rfo']))         $tread['tread_rfo']="";
          if(!isset($tread['tread_lro']))         $tread['tread_lro']="";
          if(!isset($tread['tread_lri']))         $tread['tread_lri']="";
          if(!isset($tread['tread_rri']))         $tread['tread_rri']="";
          if(!isset($tread['tread_rro']))         $tread['tread_rro']="";
          
	     $sql = "
			update maint_inspect_trucks set
							
				inspector_name='".sql_friendly($inspect_name)."',
				unit_id_type='".sql_friendly($id_type)."',
				unit_id_number='".sql_friendly($id_num)."',
				inspector_location='".sql_friendly($inspect_local)."',
				meets_396_19_items='".sql_friendly($meets_396_19_items)."',	
				
				brake_left_steering='".sql_friendly($brake['brake_left_steering'])."',
                    brake_right_steering='".sql_friendly($brake['brake_right_steering'])."',
				brake_left_front='".sql_friendly($brake['brake_left_front'])."',
                    brake_right_front='".sql_friendly($brake['brake_right_front'])."',
                    brake_left_rear='".sql_friendly($brake['brake_left_rear'])."',
                    brake_right_rear='".sql_friendly($brake['brake_right_rear'])."',
                    
                    tread_lso='".sql_friendly($tread['tread_lso'])."',
                    tread_rso='".sql_friendly($tread['tread_rso'])."',
                    tread_lfo='".sql_friendly($tread['tread_lfo'])."',
                    tread_lfi='".sql_friendly($tread['tread_lfi'])."',
                    tread_rfi='".sql_friendly($tread['tread_rfi'])."',
                    tread_rfo='".sql_friendly($tread['tread_rfo'])."',
                    tread_lro='".sql_friendly($tread['tread_lro'])."',
                    tread_lri='".sql_friendly($tread['tread_lri'])."',
                    tread_rri='".sql_friendly($tread['tread_rri'])."',
                    tread_rro='".sql_friendly($tread['tread_rro'])."',
                    					
				user_signed='".sql_friendly($_SESSION['user_id'])."',
				linedate=NOW()
				
			where id='".sql_friendly($id)."'
		";	//	passed='".sql_friendly($pass)."',			
		simple_query($sql);
		
		//clear older entires that have been deleted for this inspection.
		$sql = "delete from maint_inspect_truck_entries where inspect_id='".sql_friendly($id)."' and deleted > 0";			
		simple_query($sql);
	}
	return $id;	
}
function mrr_decode_truck_inspect_type($type=0)
{
	$arr[0]="";
	
	$arr[58]="Tractor";
	$arr[59]="Trailer";
	$arr[1]="Truck";
	$arr[2]="Trailer";
	$arr[3]="Bus";
	$arr[4]="Other";
	
	return $arr[$type];
}
function mrr_decode_truck_id_type($type=0)
{
	$arr[0]="";
	
	$arr[1]="LIC. PLATE NO.";
	$arr[2]="VIN";
	$arr[3]="OTHER";
	
	return $arr[$type];
}
function mrr_truck_inspect_id_type_selector($field,$pre)
{
	$html="<select name='".$field."' id='".$field."'>";
			
	$selector="";		if($pre==0 || $pre=="")	$selector=" selected";
	$html.="<option value='0'".$selector."></option>";	
	
	$selector="";		if($pre==1)		$selector=" selected";
	$html.="<option value='1'".$selector.">LIC. PLATE NO.</option>";	
	
	$selector="";		if($pre==2)		$selector=" selected";
	$html.="<option value='2'".$selector.">VIN</option>";	
	
	$selector="";		if($pre==3)		$selector=" selected";
	$html.="<option value='3'".$selector.">OTHER</option>";	
	
	$html.="</select>";		
	return $html;
}
function mrr_truck_inspect_meets_binary_selector($field,$pre,$section=0,$sub_id=0)
{
	$html="<select name='".$field."' id='".$field."' onChange='mrr_auto_update_inspect_setting(".$section.",".$sub_id.");'>";
			
	$selector="";		if($pre==0 || $pre=="")	$selector=" selected";
	$html.="<option value='0'".$selector."></option>";	
	
	$selector="";		if($pre==1)			$selector=" selected";
	$html.="<option value='1'".$selector.">Y</option>";	
	
	$selector="";		if($pre==2)			$selector=" selected";
	$html.="<option value='2'".$selector.">X</option>";	
	
	$selector="";		if($pre==3)			$selector=" selected";
	$html.="<option value='3'".$selector.">NA</option>";	
	
	$html.="</select>";		
	return $html;
}
function mrr_fetch_maint_inspect_truck_entry_values($id, $sect_id=0,$sub_id=0)
{
	$res['okay']=0;	
    	$res['need']=0;	
    	$res['date']="";
	$res['note']="";	
	
	$sql = "
     	select *
     	from maint_inspect_truck_entries
     	where deleted=0
     		and inspect_id='".sql_friendly($id)."'
     		and sect_id='".sql_friendly($sect_id)."'
     		and sub_id='".sql_friendly($sub_id)."'
     ";
     $data=simple_query($sql);
     if($row=mysqli_fetch_array($data))
     {     		
     	$res['okay']=$row['okay_value'];	
     	$res['need']=$row['repairs'];	
     	$res['date']="";		if($row['repairs_date']!="0000-00-00 00:00:00")		$res['date']=date("m/d/Y",strtotime($row['repairs_date']));	
		$res['note']=trim($row['repairs_notes']);			
     }
     $res['sql']=$sql;
     return $res;
}

function mrr_form_truck_inspection($id)
{
	$tab="<input type='hidden' name='inspect_id' id='inspect_id' value='".$id."'>";
	$left_col="";
	$right_col="";
	$right_col1="";
	$right_col2="";
	
	$truck_name="";
	$plate="";
	$vin="";
	if($id > 0)
	{
		$sql = "
     		select trucks.name_truck,trucks.vin,trucks.license_plate_no
     		from trucks
     			left join maint_inspect_trucks on maint_inspect_trucks.truck_id=trucks.id
     		where maint_inspect_trucks.id='".sql_friendly($id)."'
     	";
     	$data=simple_query($sql);
     	if($row=mysqli_fetch_array($data))
     	{     		
     		$truck_name=trim($row['name_truck']);	
     		$plate=trim($row['license_plate_no']);	
			$vin=trim($row['vin']);	
     	}
	}
	$tab.="<div class='hide_from_printer'><span class='mrr_link_like_on' onClick='mrr_inspection_display(1);'>Show Inspection</span> or <span class='mrr_link_like_on' onClick='mrr_inspection_display(0);'>Hide Inspection</span></div>";
	$tab.="<table cellpadding='0' cellspacing='0' border='0' width='100%' class='trailer_inspection_form'>";
	
	
	//$sql_21="select id as use_val,fvalue as use_disp from option_values where cat_id=21 and deleted=0 order by fvalue asc";
	//$sql_22="select id as use_val,fname as use_disp from option_values where cat_id=22 and deleted=0 order by fname asc";
		
	if($id > 0)
	{	
		$sql = "
     		select maint_inspect_trucks.*,
     			(select username from users where users.id=maint_inspect_trucks.user_id) as created_by,
     			(select concat(users.name_first,' ',users.name_last) from users where users.id=maint_inspect_trucks.user_signed) as updated_by
     		from maint_inspect_trucks
     		where id='".sql_friendly($id)."'
     	";
     	$data=simple_query($sql);
     	$row=mysqli_fetch_array($data);
     	
     	$row_height=25;
     	
     	$tab.="
			<tr>
				<td valign='top' width='50%'>
					<h2><b>Annual Vehicle Inspection Report</b></h2>
				</td>
				<td valign='top' width='50%'>
					<table cellpadding='0' cellspacing='0' border='1' width='100%'>
					<tr>
						<td valign='top' colspan='2' align='center'><b>Vehicle History Record</b></td>
					</tr>
					<tr>
						<td valign='top' align='center'>Report Number</td>
						<td valign='top' align='center'>Fleet Unit Number</td>
					</tr>
					<tr>
						<td valign='top' align='center'><b>".$row['id']."</b></td>
						<td valign='top' align='center'><b>".$truck_name."</b></td>
					</tr>
					<tr>
						<td valign='top' align='center'>Date</td>
						<td valign='top' align='center'>".date("m/d/Y H:i",strtotime($row['linedate']))."</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td valign='top' width='50%'>
					<table cellpadding='0' cellspacing='0' border='1' width='100%'>
					<tr style='height:".$row_height."px; min-height:".$row_height."px;'>
						<td valign='top'>Motor Carrier Operator</td>
						<td valign='top'>".$row['operator_name']."</td>
					</tr>
					<tr style='height:".$row_height."px; min-height:".$row_height."px;'>
						<td valign='top'>Address</td>
						<td valign='top'>".$row['operator_addr']."</td>
					</tr>
					<tr style='height:".$row_height."px; min-height:".$row_height."px;'>
						<td valign='top'>City, State, Zip Code</td>
						<td valign='top'>".$row['operator_city'].", ".$row['operator_state']." ".$row['operator_zip']."</td>
					</tr>
					<tr style='height:".$row_height."px; min-height:".$row_height."px;'>
						<td valign='top'>Vehicle Type</td>
						<td valign='top'>".mrr_decode_truck_inspect_type($row['vehicle_type'])."</td>
					</tr>
					</table>
				</td>
				<td valign='top' width='50%'>
					<table cellpadding='0' cellspacing='0' border='1' width='100%'>
					<tr style='height:".$row_height."px; min-height:".$row_height."px;'>
						<td valign='top'>Inspector's Name</td>
						<td valign='top'><input type='text' id='inspector_name' name='inspector_name' value=\"".(trim($row['inspector_name'])!="" ? trim($row['inspector_name']) : trim($row['updated_by']))."\" style='width:200px;'></td>
					</tr>
					<tr style='height:".$row_height."px; min-height:".$row_height."px;'>
						<td valign='top' colspan='2'>
							This Inspector meets the qualifications requirements in Section 396.19 
							".mrr_truck_inspect_meets_binary_selector('meets_396_19_items',$row['meets_396_19_items'])."
						</td>
					</tr>					
					<tr style='height:".$row_height."px; min-height:".$row_height."px;'>
						<td valign='top'>Vehicle Identification</td>
						<td valign='top'>
							".mrr_truck_inspect_id_type_selector('unit_type_id',$row['unit_id_type'])."
							<input type='text' id='unit_type_number' name='unit_type_number' value=\"".trim($row['unit_id_number'])."\" style='width:150px;'>
							
							<input type='hidden' id='unit_type_plate' name='unit_type_plate' value=\"".trim($plate)."\">
							<input type='hidden' id='unit_type_vin' name='unit_type_vin' value=\"".trim($vin)."\">
						</td>
					</tr>
					<tr style='height:".$row_height."px; min-height:".$row_height."px;'>
						<td valign='top'>Inspection Agency/Location</td>
						<td valign='top'><input type='text' id='inspector_local' name='inspector_local' value=\"".$row['inspector_location']."\" style='width:200px;'></td>
					</tr>
					</table>
				</td>
			</tr>
		";			//".mrr_decode_truck_id_type($row['unit_type_id'])." ".trim($row['unit_type_number'])."
     	
     	$sectors[0]="";
     	
     	$sectors[1]="Brake System";
     	$sectors[2]="Coupling Devices";
     	$sectors[3]="Exhaust System";
     	$sectors[4]="Fuel System";
     	$sectors[5]="Lighting Devices";
     	
     	$sectors[6]="Safe Loading";
     	$sectors[7]="Steering Mechanism";
     	$sectors[8]="Suspension";
     	$sectors[9]="Frame";
     	
     	$sectors[10]="Tires";
     	$sectors[11]="Wheels and Rims";
     	$sectors[12]="Windshield Glazing";
     	$sectors[13]="Windshield Wipers";
     	$sectors[14]="Motorcoach Seats";
     	$sectors[15]="Other";
     	
     	$col_1="<table cellpadding='0' cellspacing='0' border='0' width='400'>";
     	$col_2="<table cellpadding='0' cellspacing='0' border='0' width='400'>";
     	$col_3="<table cellpadding='0' cellspacing='0' border='0' width='400'>";
     	
     	for($x=1; $x < 6; $x++)
     	{
     		//maint_inspect_truck_settings
     		
     		$col_1.="<tr><td valign='top' colspan='4' align='center' style='border:1px solid #000000; font-weight:bold;'>".$x.". ".strtoupper($sectors[ $x ])."</td></tr>";
     		
     		$sqlx="
     			select * 
     			from maint_inspect_truck_settings
     			where deleted=0 and section_id='".$x."'
     			order by sub_order asc
     		";
     		$datax=simple_query($sqlx);
     		while($rowx=mysqli_fetch_array($datax))
     		{
     			$sub_let=trim($rowx['sub_char']);
     			$sub_item=trim($rowx['sub_item']);
     			     			
     			$res=mrr_fetch_maint_inspect_truck_entry_values($id, $x, $rowx['id']);
     			
     			$ans_ok=mrr_truck_inspect_meets_binary_selector("ok_".$x."_".$rowx['id']."",$res['okay'],$x,$rowx['id']);
     			$ans_repairs=mrr_truck_inspect_meets_binary_selector("repairs_".$x."_".$rowx['id']."",$res['need'],$x,$rowx['id']);
     			$ans_date="<input type='text' name='rdate_".$x."_".$rowx['id']."' id='rdate_".$x."_".$rowx['id']."' value='".$res['date']."' class='datepicker' style='width:75px;' onChange='mrr_auto_update_inspect_setting(".$x.",".$rowx['id'].");'>";
     			
     			$col_1.="
     				<tr>
     					<td valign='top' width='50'>".$ans_ok."</td>
						<td valign='top' width='50'>".$ans_repairs."</td>
						<td valign='top' width='80'>".$ans_date."</td>
						<td valign='top' width='220'>&nbsp;".($sub_let!="" ? "".$sub_let.". " : "")."".$sub_item."</td>
     				</tr>
     			";
     		}
     	}
     	for($x=6; $x < 10; $x++)
     	{
     		$col_2.="<tr><td valign='top' colspan='4' align='center' style='border:1px solid #000000; font-weight:bold;'>".$x.". ".strtoupper($sectors[ $x ])."</td></tr>";
     		
     		$sqlx="
     			select * 
     			from maint_inspect_truck_settings
     			where deleted=0 and section_id='".$x."'
     			order by sub_order asc
     		";
     		$datax=simple_query($sqlx);
     		while($rowx=mysqli_fetch_array($datax))
     		{
     			$sub_let=trim($rowx['sub_char']);
     			$sub_item=trim($rowx['sub_item']);
     			     			
     			$res=mrr_fetch_maint_inspect_truck_entry_values($id, $x, $rowx['id']);
     			
     			$ans_ok=mrr_truck_inspect_meets_binary_selector("ok_".$x."_".$rowx['id']."",$res['okay'],$x,$rowx['id']);
     			$ans_repairs=mrr_truck_inspect_meets_binary_selector("repairs_".$x."_".$rowx['id']."",$res['need'],$x,$rowx['id']);
     			$ans_date="<input type='text' name='rdate_".$x."_".$rowx['id']."' id='rdate_".$x."_".$rowx['id']."' value='".$res['date']."' class='datepicker' style='width:75px;' onChange='mrr_auto_update_inspect_setting(".$x.",".$rowx['id'].");'>";
     			
     			$col_2.="
     				<tr>
     					<td valign='top' width='50'>".$ans_ok."</td>
						<td valign='top' width='50'>".$ans_repairs."</td>
						<td valign='top' width='80'>".$ans_date."</td>
						<td valign='top' width='220'>&nbsp;".($sub_let!="" ? "".$sub_let.". " : "")."".$sub_item."</td>
     				</tr>
     			";
     		}
     	}
     	for($x=10; $x < 15; $x++)
     	{
     		$col_3.="<tr><td valign='top' colspan='4' align='center' style='border:1px solid #000000; font-weight:bold;'>".$x.". ".strtoupper($sectors[ $x ])."</td></tr>";
     		
     		$sqlx="
     			select * 
     			from maint_inspect_truck_settings
     			where deleted=0 and section_id='".$x."'
     			order by sub_order asc
     		";
     		$datax=simple_query($sqlx);
     		while($rowx=mysqli_fetch_array($datax))
     		{
     			$sub_let=trim($rowx['sub_char']);
     			$sub_item=trim($rowx['sub_item']);
     			     			
     			$res=mrr_fetch_maint_inspect_truck_entry_values($id, $x, $rowx['id']);
     			
     			$ans_ok=mrr_truck_inspect_meets_binary_selector("ok_".$x."_".$rowx['id']."",$res['okay'],$x,$rowx['id']);
     			$ans_repairs=mrr_truck_inspect_meets_binary_selector("repairs_".$x."_".$rowx['id']."",$res['need'],$x,$rowx['id']);
     			$ans_date="<input type='text' name='rdate_".$x."_".$rowx['id']."' id='rdate_".$x."_".$rowx['id']."' value='".$res['date']."' class='datepicker' style='width:75px;' onChange='mrr_auto_update_inspect_setting(".$x.",".$rowx['id'].");'>";
     			
     			$col_3.="
     				<tr>
     					<td valign='top' width='50'>".$ans_ok."</td>
						<td valign='top' width='50'>".$ans_repairs."</td>
						<td valign='top' width='80'>".$ans_date."</td>
						<td valign='top' width='220'>&nbsp;".($sub_let!="" ? "".$sub_let.". " : "")."".$sub_item."</td>
     				</tr>
     			";
     		}
     	}
     	
     	
     	$col_3.="<tr><td valign='top' colspan='4' align='center' style='border:1px solid #000000; font-weight:bold;'>15.".strtoupper($sectors[ 15 ])."</td></tr>";
     	$x=15;
     	
     	$sqlx="
			select * 
			from maint_inspect_truck_settings
			where deleted=0 and section_id='".$x."'
			order by sub_order asc
		";
		$datax=simple_query($sqlx);
		while($rowx=mysqli_fetch_array($datax))
		{
			$sub_let=trim($rowx['sub_char']);
			$sub_item=trim($rowx['sub_item']);
			     			
			$res=mrr_fetch_maint_inspect_truck_entry_values($id, $x, $rowx['id']);
			
			$ans_ok=mrr_truck_inspect_meets_binary_selector("ok_".$x."_".$rowx['id']."", $res['okay'],$x,$rowx['id']);
			$ans_repairs=mrr_truck_inspect_meets_binary_selector("repairs_".$x."_".$rowx['id']."", $res['need'],$x,$rowx['id']);
			$ans_date="<input type='text' name='rdate_".$x."_".$rowx['id']."' id='rdate_".$x."_".$rowx['id']."' value='".$res['date']."' class='datepicker' style='width:75px;' onChange='mrr_auto_update_inspect_setting(".$x.",".$rowx['id'].");'>";
			$ans_notes="<textarea name='custom_".$x."_".$rowx['id']."' id='custom_".$x."_".$rowx['id']."' rows='15' cols='25' onBlur='mrr_auto_update_inspect_setting(".$x.",".$rowx['id'].");'>".$res['note']."</textarea>";
			
			$col_3.="
				<tr>
					<td valign='top' width='50'>".$ans_ok."</td>
					<td valign='top' width='50'>".$ans_repairs."</td>
					<td valign='top' width='80'>".$ans_date."</td>
					<td valign='top' width='220'>&nbsp;".($sub_let!="" ? "".$sub_let.". " : "")."".$sub_item."<br>".$ans_notes."</td>
				</tr>
			";		//<br>Query: ".$res['sql']."
		}
     	
     	
     	$col_1.="</table>";
     	$col_2.="</table>";
     	$col_3.="</table>";
     	
     	
     	$sql_22="select id as use_val,fname as use_disp from option_values where cat_id=22 and deleted=0 order by fname asc";
     	
     	//Brake section...
     	$left_col="<div style='border:1px solid black; padding:0px; margin:0px;'>";
     	$left_col.="<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top' colspan='4' align='center'>
     					<b>Brake Lining Remaining:</b>
     					<br><br>
     					List brake lining as: 
     					<br>
     					New, 3/4\", 1/2\", 3/8\", or 1/4\" thick
     				</td>";
     	$left_col.="</tr>";
     	//$left_col.="<tr>";
     	//$left_col.=	"<td valign='top' colspan='4'><br>&nbsp;<br></td>";
     	//$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'><b>Left Steering</b></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_22,'brake_left_steering',$row['brake_left_steering'],'__','')."</td>";
     	$left_col.=	"<td valign='top'><b>Right Steering</b></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_22,'brake_right_steering',$row['brake_right_steering'],'__','')."</td>";
     	$left_col.="</tr>";
     	//$left_col.="<tr>";
     	//$left_col.=	"<td valign='top' colspan='4'><br>&nbsp;<br></td>";
     	//$left_col.="</tr>";
     	//$left_col.="<tr>";
     	//$left_col.=	"<td valign='top' colspan='4'><br>&nbsp;<br></td>";
     	//$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'><b>Left Front</b></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_22,'brake_left_front',$row['brake_left_front'],'__','')."</td>";
     	$left_col.=	"<td valign='top'><b>Right Front</b></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_22,'brake_right_front',$row['brake_right_front'],'__','')."</td>";
     	$left_col.="</tr>";
     	//$left_col.="<tr>";
     	//$left_col.=	"<td valign='top' colspan='4'><br>&nbsp;<br></td>";
     	//$left_col.="</tr>";
     	$left_col.="<tr>";
     	$left_col.=	"<td valign='top'><b>Left Rear</b></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_22,'brake_left_rear',$row['brake_left_rear'],'__','')."</td>";
     	$left_col.=	"<td valign='top'><b>Right Rear</b></td>";
     	$left_col.=	"<td valign='top'>".select_box_disp($sql_22,'brake_right_rear',$row['brake_right_rear'],'__','')."</td>";
     	$left_col.="</tr>";
     	$left_col.="</table>";
     	$left_col.="</div>";
     	
     	//Tire Section.
     	$right_col="<div style='border:0px solid black; padding:0px; margin:0px;'>";
     	$right_col.="<table cellpadding='0' cellspacing='0' border='1' width='100%'>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top' colspan='5' align='center'>Truck <b>Steering Axle</b> Tire Tread</td>";
     	$right_col.="</tr>";
     	//$right_col.="<tr>";
     	//$right_col.=	"<td valign='top' colspan='2' align='center'>LEFT</td>";
     	//$right_col.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	//$right_col.=	"<td valign='top' colspan='2' align='center'>RIGHT</td>";
     	//$right_col.="</tr>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_lso' id='tread_lso' value='".$row['tread_lso']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_lso\");'>/<b>32</b></td>";
     	$right_col.=	"<td valign='top' align='center'>LEFT</td>";
     	$right_col.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	$right_col.=	"<td valign='top' align='center'>RIGHT</td>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_rso' id='tread_rso' value='".$row['tread_rso']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_rso\");'>/<b>32</b></td>";
     	$right_col.="</tr>";	     			
     	//$right_col.="<tr>";
     	//$right_col.=	"<td valign='top' colspan='5' align='center'>&nbsp;</td>";
     	//$right_col.="</tr>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top' colspan='5' align='center'>Truck <b>Front Drive Axle</b> Tire Tread</td>";
     	$right_col.="</tr>";
     	//$right_col.="<tr>";
     	//$right_col.=	"<td valign='top' colspan='2' align='center'>LEFT</td>";
     	//$right_col.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	//$right_col.=	"<td valign='top' colspan='2' align='center'>RIGHT</td>";
     	//$right_col.="</tr>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_lfo' id='tread_lfo' value='".$row['tread_lfo']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_lfo\");'> /<b>32</b></td>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_lfi' id='tread_lfi' value='".$row['tread_lfi']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_lfi\");'> /<b>32</b></td>";
     	$right_col.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_rfi' id='tread_rfi' value='".$row['tread_rfi']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_rfi\");'> /<b>32</b></td>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_rfo' id='tread_rfo' value='".$row['tread_rfo']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_rfo\");'> /<b>32</b></td>";
     	$right_col.="</tr>";	     			
     	//$right_col.="<tr>";
     	//$right_col.=	"<td valign='top' colspan='5' align='center'>&nbsp;</td>";
     	//$right_col.="</tr>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_lro' id='tread_lro' value='".$row['tread_lro']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_lro\");'> /<b>32</b></td>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_lri' id='tread_lri' value='".$row['tread_lri']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_lri\");'> /<b>32</b></td>";
     	$right_col.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_rri' id='tread_rri' value='".$row['tread_rri']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_rri\");'> /<b>32</b></td>";
     	$right_col.=	"<td valign='top' align='center'><input type='text' name='tread_rro' id='tread_rro' value='".$row['tread_rro']."' style='width:50px; text-align:right;' onBlur='mrr_max_tread_value(\"tread_rro\");'> /<b>32</b></td>";
     	$right_col.="</tr>";
     	//$right_col.="<tr>";
     	//$right_col.=	"<td valign='top' colspan='2' align='center'>LEFT</td>";
     	//$right_col.=	"<td valign='top' align='center' style='background-color:#000000;'>&nbsp;</td>";
     	//$right_col.=	"<td valign='top' colspan='2' align='center'>RIGHT</td>";
     	//$right_col.="</tr>";
     	$right_col.="<tr>";
     	$right_col.=	"<td valign='top' colspan='5' align='center'>Truck <b>Rear Drive Axle</b> Tire Tread</td>";
     	$right_col.="</tr>";
     	$right_col.="</table>";
     	$right_col.="</div>";
     	
     	//	<tr><td valign='top' colspan='2' align='center'>&nbsp;</td></tr>
     	$tab.="			
			<tr>
				<td colspan='2'>
					<table cellpadding='0' cellspacing='0' border='1' width='100%'>
					<tr>
						<td valign='top' colspan='12' align='center'><b>Vehicle Components Inspected</b></td>
					</tr>
					<tr>
						<td valign='top' align='center' width='50'>Ok</td>
						<td valign='top' align='center' width='50'>Needs<br>Repairs</td>
						<td valign='top' align='center' width='80'>Repaired<br>Date</td>
						<td valign='top' align='center' width='220'>Item</td>
						
						<td valign='top' align='center' width='50'>Ok</td>
						<td valign='top' align='center' width='50'>Needs<br>Repairs</td>
						<td valign='top' align='center' width='80'>Repaired<br>Date</td>
						<td valign='top' align='center' width='220'>Item</td>
						
						<td valign='top' align='center' width='50'>Ok</td>
						<td valign='top' align='center' width='50'>Needs<br>Repairs</td>
						<td valign='top' align='center' width='80'>Repaired<br>Date</td>
						<td valign='top' align='center' width='220'>Item</td>
					</tr>
					<tr>
						<td valign='top' align='center' colspan='4'>".$col_1."</td>
						
						<td valign='top' align='center' colspan='4'>".$col_2."</td>
						
						<td valign='top' align='center' colspan='4'>".$col_3."</td>
					</tr>
					</table>
				</td>
			</tr>
			
			<tr>
				<td valign='top' align='left'>".$left_col."</td>
				<td valign='top' align='right'>".$right_col."</td>
			</tr>
			
			<tr>
				<td valign='top' colspan='2'>
					INSTRUCTIONS: Mark Column Entries to verify Inspection: <u>Y</u> OK <u>X</u> Needs Repairs <u>NA</u> if items do not apply. <u>&nbsp; &nbsp;</u> Repaired Date
				</td>
			</tr>
			
		";	
		//<tr><td valign='top' colspan='2' align='center'>&nbsp;	</td></tr>
		//<tr><td valign='top' colspan='2' align='center'>&nbsp;</td></tr>
		//<tr><td valign='top' colspan='2' align='center'>&nbsp;</td></tr>
     	     	
     	$selpassed="";     	
     	$selpassed.="<select name='passed' id='passed'>";
     	$selpassed.=	"<option value='0'".($row['passed']==0 ? " selected" : "").">Inspection Not Passed</option>";
     	$selpassed.=	"<option value='1'".($row['passed']==1 ? " selected" : "").">Passed PM Only</option>";
     	$selpassed.=	"<option value='2'".($row['passed']==2 ? " selected" : "").">Passed FED Only</option>";
     	$selpassed.=	"<option value='3'".($row['passed']==3 ? " selected" : "").">Passed PM and FED</option>";
     	$selpassed.="</select>";
     	     	
     	$tab.="<tr><td colspan='2'>
     				Certification: This vehicle has passed all the inspection items for the annual vehicle inspection in accordance with 49 CFR 396: ".$selpassed."
     		</td></tr>";
     	
     	$tab.="<tr class='hide_from_printer'><td colspan='2'><br>&nbsp;<br></td></tr>";
		
		$tab.="<tr class='hide_from_printer'>
				<td valign='top'>
					<input type='button' name='inspect_updater' id='inspect_updater' value='Update Inspection' onClick='mrr_update_truck_inspection();'>
					&nbsp; &nbsp; &nbsp; &nbsp; 
					<input type='button' name='inspect_printer' id='inspect_printer' value='Print Inspection' onClick='mrr_print_this_inspection();'>
				</td>
				<td valign='top' align='right'>Last update by ".$row['updated_by']." on ".date("m/d/Y H:i",strtotime($row['linedate']))."</td>
			</tr>";
		
		//$tab.="<tr><td colspan='2'><br>&nbsp;<br></td></tr>";
	}
	else
	{
		$tab.="<tr><td colspan='2' align='center'>Please save Maint Request to enter the Truck PM/AVI Inspection</td></tr>";
	}
	$tab.="</table>";
	
	return $tab;
}
function mrr_delete_truck_inspection($id)
{
	if($id>0)
	{
		$sql = "
			update maint_inspect_trucks set
				deleted=1
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);
	}	
}
function mrr_pass_truck_inspection($id,$passed=0,$maint_id=0,$truck_id=0)
{
	if($id>0)
	{
		$sql = "
			update maint_inspect_trucks set
				passed='".sql_friendly($passed)."',
				user_signed='".sql_friendly($_SESSION['user_id'])."',
				linedate=NOW()
			where id='".sql_friendly($id)."'
		";
		simple_query($sql);
		
		if($passed > 0 && $maint_id > 0 && $truck_id > 0)
		{
			$res=mrr_form_truck_inspection_list($maint_id);	
			if($passed==1 || $passed==3)
			{
				$sql = "
          			update truck set
          				pm_inspection_date='".date("Y-m-d",time())." 00:00:00'
          			where id='".sql_friendly($trailer_id)."'
          		";
          		simple_query($sql);
			}
			if($passed==2 || $passed==3)
			{
				$sql = "
          			update truck set
          				fd_inspection_date='".date("Y-m-d",time())." 00:00:00'
          			where id='".sql_friendly($trailer_id)."'
          		";
          		simple_query($sql);	
			}
		}
	}	
}











function mrr_update_load_profit_setting($load_id,$save_me=0)
{
	global $defaultsarray;
	
	$tab="";
	if($load_id>0)
	{
		$sql = "
			select load_handler.*,
				(select ifnull(sum(trucks_log.cost),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as mrr_cost,
				(load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount - load_handler.actual_total_cost) as load_profit
			from load_handler
			where load_handler.id = '".sql_friendly($load_id)."'
		";			
		$data = simple_query($sql);	
		if($row = mysqli_fetch_array($data))
		{
			$tot_coster=$row['mrr_cost'];
			$billed=$row['actual_bill_customer'];	
			$flat_rate_fuel_charge=$row['flat_fuel_rate_amount'];
			$sales=$flat_rate_fuel_charge + $billed;
			
			$ld_cost_tot=$row['actual_total_cost'];
			$ld_cost_tot2=$ld_cost_tot;
			
			$ld_profit_tot=$row['load_profit'];
			
			$fuel_per_mile = $row['actual_fuel_charge_per_mile'];
			
			$tab.="
				<br><b>Load <a href='manage_load.php?load_id=".$load_id."' target='_blank'>".$load_id."</a>:</b>
				<br>Billed $".$billed." + Fuel Flat Rate $".$flat_rate_fuel_charge." = Sales Amount $".$sales.".
				<br>
				<br>Load Cost $".$ld_cost_tot.".
				<br>Load Profit $".$ld_profit_tot.".
			";
			
			$sql2="
				select * 
				from trucks_log
				where deleted='0' 
					and load_handler_id='".sql_friendly($load_id)."'
				order by linedate_pickup_eta asc, 
					id asc
			";
			$data2 = simple_query($sql2);
			while($row2 = mysqli_fetch_array($data2)) 
			{
				// get any variable expenses
				$variable_expenses_total = 0;
          		$sql = "
          			select *          			
          			from dispatch_expenses
          			where dispatch_id = '".sql_friendly($row2['id'])."'
          				and deleted = 0
          		";
          		$data_expenses = simple_query($sql);              		
          		while($row_expense = mysqli_fetch_array($data_expenses)) 
          		{
          			$variable_expenses_total += $row_expense['expense_amount'];
          		}
				
				
				$tractor_maint_per_mile = ($row2['tractor_maint_per_mile'] > 0 ? $row2['tractor_maint_per_mile'] : $defaultsarray['tractor_maint_per_mile']);
				$trailer_maint_per_mile = ($row2['trailer_maint_per_mile'] > 0 ? $row2['trailer_maint_per_mile'] : $defaultsarray['trailer_maint_per_mile']);
				$mrr_other_per_mile=0;		
     		
     			$tires_per_mile = ($row2['tires_per_mile'] > 0 ? $row2['tires_per_mile'] : $defaultsarray['tires_per_mile']);
     			$accidents_per_mile = ($row2['accidents_per_mile'] > 0 ? $row2['accidents_per_mile'] : $defaultsarray['truck_accidents_per_mile']);
     			$mile_exp_per_mile = ($row2['mile_exp_per_mile'] > 0 ? $row2['mile_exp_per_mile'] : $defaultsarray['mileage_expense_per_mile']);
     		
     			$trailer_mile_exp_per_mile=0;
     			//$trailer_mile_exp_per_mile = ($row2['trailer_exp_per_mile'] > 0 ? $row2['trailer_exp_per_mile'] : $defaultsarray['trailer_mile_exp_per_mile']);
     			//$trailer_mile_exp_per_mile = $row2['trailer_exp_per_mile'];		//$defaultsarray['trailer_mile_exp_per_mile']
     		
     			$misc_per_mile = ($row2['misc_per_mile'] > 0 ? $row2['misc_per_mile'] : $defaultsarray['misc_expense_per_mile']);
     			$mrr_other_per_mile=$tires_per_mile + $accidents_per_mile + $mile_exp_per_mile + $misc_per_mile + $trailer_mile_exp_per_mile;
     			
				$total_maint_per_mile = $tractor_maint_per_mile + $trailer_maint_per_mile + $mrr_other_per_mile;
				
				$per_mile=$fuel_per_mile + $total_maint_per_mile;
				
				$ld_disp_per=0;
				
				$coster=$row2['cost'];
				$coster2=($row2['labor_per_hour'] * $row2['hours_worked']) + ($per_mile * $row2['loaded_miles_hourly']);	// + $variable_expenses_total;
				
				$update_adder="";
				
				if($coster2 > $coster)	
				{	//dispatch
					$diff_cost=$coster2-$coster;
					$ld_cost_tot+=$diff_cost;
					
					$ld_profit_tot=$sales - $ld_cost_tot;
					
					
					$tab.="
						<br><b>Additional Cost Found...New cost is $".$coster2." instead of $".$coster."!</b> (Diff $".$diff_cost.")
					";										
					/*
					
					$tab.="
						<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".($row2['labor_per_hour'] * $row2['hours_worked'])." Hourly ".$row2['hours_worked']." hours x $".$row2['labor_per_hour'] ."/hr.  
						<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".($row2['loaded_miles_hourly'] * $per_mile)." Mileage ".$row2['loaded_miles_hourly']." miles x $".$per_mile."/mi.
						<br>
					";	
					
					*/		
					
					$tab.="
						<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Profit should be ".$ld_profit_tot.".  Percentage should be (".$coster2." / ".$ld_cost_tot.")
						<br>
					";	
					
					$update_adder=",cost='".sql_friendly($coster2)."'";			
										
					$coster=$coster2;						
				}
				
				if($ld_cost_tot > 0)		$ld_disp_per=($coster/ $ld_cost_tot);
				
				$div_profit=$ld_profit_tot * $ld_disp_per;
								
				$tab.="			
					<br>Disp <a href='add_entry_truck.php?load_id=".$load_id."&id=".$row2['id']."' target='_blank'>".$row2['id']."</a>
						".date("M j, Y", strtotime($row2['linedate_pickup_eta']))." ---
						Cost $".money_format('',$coster)."
						 ".number_format(($ld_disp_per * 100), 2)."% of Load Cost |
						 Profit <i>$".money_format('',$row2['profit'])."</i>
						 NEW PROFIT <b>$".money_format('',$div_profit)."</b>.
				";		//$row2[origin], $row2[origin_state]
						//$row2[destination], $row2[destination_state]
						//".($row2['miles'] + $row2['miles_deadhead'])."
						//".number_format($row2['hours_worked'], 2)."
										
				if($save_me > 0)
				{	//update the profit based on the cost...
					$sqlu = "update trucks_log set profit='".sql_friendly($div_profit)."'".$update_adder." where id='".sql_friendly($row2['id'])."'";	
					simple_query($sqlu); 	
				}
			}
			
			
			if($ld_cost_tot!=$ld_cost_tot2 && $save_me > 0)
			{	//update the load...new cost found.
				$sqlu = "update load_handler set actual_total_cost='".sql_friendly($ld_cost_tot)."' where id='".sql_friendly($load_id)."'";	
				simple_query($sqlu); 
			}
			
			
		}
	}	
	return $tab;
}

function mrr_auto_process_load_saves($max_loads=0,$min_load_id=0)
{
	$rep="";
     
     global $defaultsarray;
     $excluder=trim($defaultsarray['auto_exclude_loads']);
	
	$min_date="2021-01-01 00:00:00";
	$min_date="".date("Y-m-d",strtotime("-5 day",time()))." 00:00:00";
	
	if($max_loads==0)		$max_loads=3;
	$cntr=0;
	$sql = "
		select load_handler.*,
			load_handler.actual_bill_customer + load_handler.flat_fuel_rate_amount - load_handler.actual_total_cost as load_profit,
			(select ifnull(sum(trucks_log.cost),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0) as mrr_cost
		
		from load_handler
		where load_handler.deleted = 0
			and
				(
				load_handler.actual_total_cost != 
					(select ifnull(sum(trucks_log.cost),0) from trucks_log where trucks_log.load_handler_id = load_handler.id and trucks_log.deleted = 0)
				
				or load_handler.auto_save_requested > 0
				)
			".($min_load_id > 0 ? "and load_handler.id >= '".$min_load_id."'" : "")."
			".($excluder!=""? "and load_handler.id not in (".$excluder.")" : "")."
			and load_handler.linedate_pickup_eta >= '".sql_friendly($min_date)."'	
		order by load_handler.id
	";	
	$data = simple_query($sql);	
	while($row = mysqli_fetch_array($data)) 
	{
		if(number_format($row['mrr_cost'],2) != number_format($row['actual_total_cost'],2) || $row['auto_save_requested'] > 0)
		{
			$cntr++;	
			
			$repair_link="manage_load.php?load_id=".$row['id']."&auto_save_trigger=1";
			
			//$tmp=mrr_simple_curl_background($repair_link);
						
			$rep.="<br>".$cntr." <a href='".$repair_link."' target='_blank'>Repair ".$row['id']."</a>. <input type='hidden' name='loader_".$cntr."' id='loader_".$cntr."' value='".$row['id']."' class='auto_load_runner'>";		
		}	
		if($cntr==$max_loads) 	break;
	}
	return $rep;
}

function mrr_simple_curl_background($url)
{	
	$fullurl="http://trucking.conardtransportation.com/".$url;
	
	ob_start();
		
	$curl_handle=curl_init();			
	curl_setopt($curl_handle, CURLOPT_URL,$fullurl);
	//curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,TRUE);	
	curl_setopt($curl_handle, CURLOPT_HEADER, false);
	
	//curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER,FALSE); 		// we don't want to stop the page from loading if there is ever an issue with the SSL
	
	//curl_setopt($curl_handle, CURLOPT_POST,1);
	//$post_cmd="service=".$cmd."&xml=".$xml."";
	//curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_cmd);
	
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);	
	//echo "($buffer)";			
	//die('<br><br><br>'.$fullurl);
	
	$tmp=ob_get_contents();
	ob_end_clean();
	//return $buffer;
}

//stored truck profit...so load baord only has to grab the one.
function mrr_add_truck_profit_history($truck_id,$date,$disp_id,$profit)
{	//DATE value should always be the linedate_pickup_eta of the load...and should thus always be in hte right format.
	if($truck_id > 0)
	{
		$sql = "
			insert into truck_profit_history
				(id,truck_id,linedate_added,linedate,dispatch_id,profit)
			values
				(NULL,'".sql_friendly($truck_id)."',NOW(),'".$date."','".sql_friendly($disp_id)."','".sql_friendly($profit)."')
		";	
		simple_query($sql);		
	}	
}
function mrr_fetch_truck_profit_history($truck_id,$date="",$disp_id=0)
{	//DATE value should always be the linedate_pickup_eta of the load...and should thus always be in hte right format.
	$res['profit']="0.00";
	$res['date']="";
	if($truck_id > 0)
	{		
		$sql = "
			select profit,linedate
			from truck_profit_history
			where truck_id='".sql_friendly($truck_id)."'
				".($disp_id > 0 ? " and dispatch_id='".sql_friendly($disp_id)."'" : "")."
				".(trim($date)!="" ? " and linedate='".$date."'" : "")."
			order by linedate desc, linedate_added desc, id desc
		";	
		$data=simple_query($sql);	
		if($row = mysqli_fetch_array($data))
		{
			$res['profit']=$row['profit'];
			$res['date']=$row['linedate'];
		}	
	}	
	return $res;
}
function mrr_clear_truck_profit_history($truck_id,$date="",$disp_id=0)
{	//DATE value should always be the linedate_pickup_eta of the load...and should thus always be in hte right format.
	if($truck_id > 0)
	{		
		$sql = "
			delete from truck_profit_history
			where truck_id='".sql_friendly($truck_id)."'
				".($disp_id > 0 ? " and dispatch_id='".sql_friendly($disp_id)."'" : "")."
				".(trim($date)!="" ? " and linedate='".$date."'" : "")."
		";	
		simple_query($sql);	
	}	
}
function mrr_double_check_driver_has_load($driver_id,$truck_id=0,$disp_id=0,$load_id=0)
{
	$has_load['load_id']=0;	
	$has_load['disp_id']=0;
	
	if($driver_id==211 || $driver_id==345 || $driver_id==405)		return $has_load;
	
	
	//dispatch check first
	$sql = "
		select trucks_log.id,trucks_log.load_handler_id
		
		from trucks_log
		where trucks_log.dispatch_completed = 0
			and trucks_log.deleted = 0
			".($disp_id  > 0 ? " and trucks_log.id!='".sql_friendly($disp_id)."'" : "" )."
			and trucks_log.linedate_pickup_eta>NOW()
			and (trucks_log.driver_id = '".sql_friendly($driver_id)."' or trucks_log.driver2_id = '".sql_friendly($driver_id)."')
	";
	$data = simple_query($sql);
	if($row = mysqli_fetch_array($data))
	{
		$has_load['load_id']=$row['load_handler_id'];
		$has_load['disp_id']=$row['id'];
	}
	
	
	if($has_load ==0)
	{	// get a list of pre-planed loads available for this driver
		$sqlp = "
			select load_handler.id
			
			from load_handler
			where load_handler.preplan = 1
				and load_handler.deleted = 0
				".($load_id  > 0 ? " and load_handler.id!='".sql_friendly($load_id)."'" : "" )."
				and load_handler.linedate_pickup_eta>NOW()
				and (load_handler.preplan_driver_id = '".sql_friendly($driver_id)."' or load_handler.preplan_driver2_id = '".sql_friendly($driver_id)."')
		";
		$datap = simple_query($sqlp);
		if($rowp = mysqli_fetch_array($datap))
		{
			$has_load['load_id']=$rowp['id'];
		}
	}
	return $has_load;	
}

function mrr_zip_files($files = array(),$destination = '',$overwrite = false)
{
	$destination = getcwd().'/'.$destination;
	$destination=str_replace("/","\\",$destination);
	
     //if the zip file already exists and overwrite is false, return false
     if(file_exists($destination) && !$overwrite)      return false; 
     
     $reporter="Begin Function (MRR_ZIP_FILES)... ".getcwd()." is CWD";
     
     $valid_files = array();
     //if files were passed in...
     if(is_array($files)) 
     {    //cycle through each file
          $reporter.=" Array valid...";
          foreach($files as $file) 
          {    //make sure the file exists
               $file=str_replace("/","\\",getcwd().$file);
               $reporter.=" 
                    Testing file ".$file."...
               ";
               if(file_exists($file)) 
               {
                    $valid_files[] = $file;
                    $reporter.=" 
                         Found file ".$file."...
                    ";
               }
          }
     }
     $reporter.=" Files to make in ZIP: ".count($valid_files)."...";
     //if we have good files...
     if(count($valid_files))
     {
          //chdir( sys_get_temp_dir() ); // Zip always get's created in current working dir so move to tmp.
          $zip = new ZipArchive();
     
          $reporter.=" ZIP Class valid... send to ". $destination ."...";
     $reporter .= 'send to '. $destination .' and overwrite at '. $overwrite .'===';
          if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) 
          {
               $reporter.=" ZIP ".$destination." failed to open...";
               return false;
          }
          //add the files
          $added_files=0;
          foreach($valid_files as $file) 
          {
			  //echo 'adding file: '. getcwd().$file .' with basename .'. basename($file) .'<br>';
			  
               $zip->addFile($file,basename($file));
               $added_files++;
               $reporter.=" ZIP File ".$added_files." Added to folder...";
          }
          //debug
          //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
     
          //close the zip -- done!
          $zip->close();
          $reporter.=" Closing ZIP File, and addign headers...";
          //header( 'Content-Type: application/zip' );
          //header( 'Content-disposition: attachment; filename=' . basename($destination) );
          //header( 'Content-Length: ' . filesize( $destination ) );
          //header("Pragma: no-cache");
          //header("Expires: 0");
          $reporter.=" ZIP File should be created... Flushing and preparing to read it back...";
          //flush();
          //readfile( $destination );
          //unlink($destination);
     
          //check to make sure the file exists
          $zipmate=file_exists($destination);
          $reporter.=" ZIP File Found=".$zipmate.".";
          return $reporter;
     }
     else
     {
          $reporter.=" NO Files found to ZIP (".count($valid_files).").";
          return $reporter;
     }     
}
?>