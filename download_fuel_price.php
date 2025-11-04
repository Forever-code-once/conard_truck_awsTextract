<? include('application.php') ?>
<?
	$filename="http://www.eia.doe.gov/dnav/pet/pet_pri_gnd_dcus_nus_w.htm";
	$alt_file_used=0;

	$handle = fopen($filename, "r");	

	$buffer = "";
	if ( $handle ) 
	{
		while (!feof($handle)) 
		{
		  $buffer .= fgets($handle, 4096);
		}
		fclose($handle);
	} 
	else 
	{
		echo "fopen failed for <b>".$filename."</b>.";
		
		$alt_file_used=1;
		$filename="http://www.fwccinc.com/doefuel.html";
		$filename="http://www.eia.gov/petroleum/gasdiesel/";
		$handle = fopen($filename, "r");
		if ( $handle ) 
		{
			while (!feof($handle)) 
			{
				$buffer .= fgets($handle, 4096);
			}
		}
	}	
	
	$buffer = strtolower($buffer);
	if($alt_file_used==0) 
	{
		// hop to the gasoline section
		$gpos = strpos(strtolower($buffer), "diesel (on-highway) - all types");
		$buffer = strtolower(substr($buffer, $gpos, strlen($buffer)));
		
		// find our marker right before the gas price
		$gpos = strpos($buffer, '"current2">');
		$buffer = substr($buffer, $gpos + 11, strlen($buffer));		
		
		// find our marker to end the gas price and we're left with the actual gas price
		$gpos = strpos($buffer, '<');
		$buffer = substr($buffer, 0, $gpos);
	}
	else
	{
		// hop to the gasoline section
		$gpos = strpos(strtolower($buffer), "u.s. on-highway diesel fuel prices");
		$buffer = strtolower(substr($buffer, $gpos, strlen($buffer)));
		
		// find our marker right before the gas price
		$gpos = strpos($buffer, '/dnav/pet/pet_pri_gnd_dcus_nus_w.htm');
		$buffer = substr($buffer, $gpos + 11, strlen($buffer));
		
		// find our marker right before the gas price
		$gpos = strpos($buffer, '</td>');
		$buffer = substr($buffer, $gpos + 5, strlen($buffer));
		
		$gpos = strpos($buffer, '</td>');
		$buffer = substr($buffer, $gpos + 5, strlen($buffer));
		
		$gpos = strpos($buffer, '</td>');
		$buffer = substr($buffer, $gpos + 5, strlen($buffer));
		
		// find our marker to end the gas price and we're left with the actual gas price
		$gpos = strpos($buffer, '</td>');
		$buffer = substr($buffer, 0, $gpos);
		
		if(!is_numeric(strip_tags($buffer))) 	die('<br>Alternate Mode Used <b>'.$filename.'</b>... '.strip_tags($buffer).'. <br>Captured Page<br>'.$buffer.'<br>');
		
		$buffer=strip_tags($buffer);
	}
		
	if(is_numeric($buffer)) 
	{
		$fuel_surcharge = $buffer;
		
		$sql = "
			update defaults			
			set xvalue_string = '$fuel_surcharge'
			where xname = 'fuel_surcharge'
		";
		$data_update = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
		
		$fuel_surcharge_last_update = date("m/d/Y");
		$sql = "
			update defaults			
			set xvalue_string = '$fuel_surcharge_last_update'
			where xname = 'fuel_surcharge_last_update'
		";
		$data_update = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
		
		$sql = "
			insert into log_fuel_updates
				(linedate_added,
				fuel_surcharge)				
			values (now(),
				'".sql_friendly($fuel_surcharge)."')
		";
		simple_query($sql);
		
		echo $fuel_surcharge;	
	} else {
		echo "Error, price was not numeric";
	}
	
?>
