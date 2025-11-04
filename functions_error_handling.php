<?

	function show_errors($show_errors_flag = true, $log_errors_flag = false) {
		
		global $show_errors;
		global $log_errors;
		
		$show_errors = $show_errors_flag;
		$log_errors = $log_errors_flag;
		
		if($show_errors) {
			ini_set('log_errors', false);
			ini_set('display_errors', 1);
			//restore_error_handler(); // for testing - DO NOT RUN THIS ON THE LIVE SITE
		} else {
			ini_set('log_errors', true);
			ini_set('display_errors', false);
			//restore_error_handler(); // for testing - DO NOT RUN THIS ON THE LIVE SITE
		}
		
		error_reporting(E_ALL ^ (E_DEPRECATED));
		
		
	}