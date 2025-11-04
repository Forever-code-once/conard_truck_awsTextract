<? include('application.php') ?>
<?
	$handle = fopen("http://www.eia.doe.gov/dnav/pet/pet_pri_gnd_dcus_nus_w.htm", "r");

	$buffer = "";
	if ( $handle ) {
		while (!feof($handle)) {
		  $buffer .= fgets($handle, 4096);
		}
		fclose($handle);
	} else {
		die( "fopen failed for $filename" );
	}
	$buffer = strtolower($buffer);
	
	/* hop to the gasoline section */
	$gpos = strpos(strtolower($buffer),"diesel (on-highway) - all types");
	$buffer = strtolower(substr($buffer,$gpos,strlen($buffer)));
	
	/* find our marker right before the gas price */
	$gpos = strpos($buffer,'"current2">');
	$buffer = substr($buffer,$gpos+11,strlen($buffer));
	
	
	
	/* find our marker to end the gas price and we're left with the actual gas price */
	$gpos = strpos($buffer,'<');
	$buffer = substr($buffer,0,$gpos);
		
	if(is_numeric($buffer)) {
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
