<?
	/*
		query_string_remove.cfm will strip the cgi.query_string variable of the url variable specified in string_to_remove 
			and return the variablue use.query_string as the new query_string;
			you must pass the string_to_remove variable before include this function
	*/
			
	
	function query_string_remove($query_string,$removestring)
	{
	
		
		$uQueryString = split("&",$query_string);
		$query_string = '';
		
		
		foreach($uQueryString as $uVar)
		{
			
			if(preg_match("[^". $removestring ."]",$uVar) == 0)
				{
					
					$query_string .= "&".$uVar;	
				}
		}
		return substr($query_string,1);
	}

?>
