<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include('application.php');
include('header.php');
include_once('functions_sicap.php');


$sql = "
	select *
	
	from
	trucks
	where deleted = 0
	order by name_truck asc, id asc
";
$data = simple_query($sql);

echo "<div style='width:1200px;'>";
echo "<br><b>ID= Truck Name</b>";

while($row = mysqli_fetch_array($data)) 
{
	$id=$row['id'];
	$name_truck=$row['name_truck'];	
	$rental=$row['rental'];
	$lease_from=$row['leased_from'];
	
	echo "<br><hr><br><b>".$id."= ".$name_truck."</b>";
	
	
	if( $rental>0 || trim($lease_from)!="")
	{	
		
		$tagged="(L)";
		if($rental>0)	$tagged="(R)";
		
		$tagged.=" ".$lease_from."";
		$tagged=trim($tagged);
		
		
		echo "<br><b>-----Active COA List:</b>";	
		
		$coa=mrr_sicap_get_accounts_for_truck_name($name_truck, 100);	//second argument is limit
		$coa_id[0]=0;
		$coa_num[0]="";
		$coa_name[0]="";
		$coa_cntr=0;
		
		foreach($coa as $item => $block )
		{
			//$prta=trim($item);		$tmpa=trim($block);
			//echo " .... ".$prta.":".$tmpa." ";
			if($item=="ChartEntry")
			{
     			foreach($block as $key => $value )
     			{
     				$prt=trim($key);		$tmp=trim($value);
     				
     				if($prt=="ID")			$coa_id[ $coa_cntr ]=(int)$tmp;
     				if($prt=="Name")		$coa_name[ $coa_cntr ]="".$tmp."";
     				if($prt=="Number")	{	$coa_num[ $coa_cntr ]="".$tmp."";	$coa_cntr++;	}
     			}
     			
			}	
		}			
		
		echo "<br><span style='color:purple; font-weight:bold;'>COA ID</span> 
					- <span style='color:green; font-weight:bold;'>COA NO.</span> 
					-- <span style='color:purple; font-weight:bold;'>COA NAME</span>";	
		for($i=0; $i < $coa_cntr ;$i++)
		{
			echo "<br><span style='color:purple; font-weight:bold;'>".$coa_id[ $i ]."</span> 
					- <span style='color:green; font-weight:bold;'>".$coa_num[ $i ]."</span> 
					-- <span style='color:orange; font-weight:bold;'>".$coa_name[ $i ]."</span>";	
					
					
			if($tagged!="" && substr_count($coa_name[ $i ],"(R)") == 0 && substr_count($coa_name[ $i ],"(L)") == 0)
			{
				$new_name="".$coa_name[ $i ]." ".$tagged."";
				
				$myid2=$coa_id[ $i ];
				$rannit="";
				///if($myid2==121)
				///{
					//mrr_update_truck_chart_names($myid2, $new_name);
					$rannit="...<b>updated</b>";
				///}
				echo "...<span style='color:red; font-weight:bold;'>".$myid2." RENTAL NOT FLAGGED...Renaming ".$new_name."</span> ".$rannit."";					
			}
		}			
	}
	else
	{
		echo "<br>SKIPPED! Not a Rental/Lease.";	
	}
	echo "<hr><br><br>";
}
echo "</div>";

?>