<?
	/* RESTful queries can take the forms:
		(/)?				-- get all event types 
		#(/)?				-- get type for given eventTypeID  
	*/
	
	
	// Retrieve eventTypeID 
	if ( count($queryArray) && is_numeric($queryArray[0]) ) {
		
		// event type ID was supplied as a token 
		$REST_vars["eventTypeID"] = intval(array_shift($queryArray), 10);
		
		// Debug dump 
		if ($GLOBALS["DEBUG"]) {
			print_r("EVENT TYPE ID: " . $REST_vars["eventTypeID"] . "\n");
		}
		
		require "eventType-id.php";
		try {
			$handler = new EventTypeID($REST_vars, $DBs, $User);
		} catch (Error $e) {
			$e->kill();
		}
		
	}
	else {
		// Otherwise get all event types
		
		require "eventType-all.php";
		try {
			$handler = new EventTypeAll($REST_vars, $DBs, $User);
		} catch (Error $e) {
			$e->kill();
		}
	}
	
	
	
	if ($GLOBALS["DEBUG"]) {
		print_r($REST_vars);
	}
	
?>