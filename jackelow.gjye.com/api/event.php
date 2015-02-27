<?
	/* RESTful queries can take the forms:
		(/)? 				-- display all events
		#(/)?				-- get event by eventID (full info)
		#/simple(/)?		-- get events for eventID (simple info)
		#/attendants(/)?	-- get attendants for eventID
		#/comments(/)?		-- get comments for eventID
		#/comments/#(/)?	-- get commentID
		
	 -- OR in any order (and any combo): 
		(sort/#)(/)?		-- get all events, sorting by sortID
		(category/#)(/)?	-- get all events matching categoryID
		(group/#)(/)?		-- get all events grouped (0 - false, 1 - true) 
		(country/#)(/)?		-- get all events matching countryID
		(show/#)(/)?		-- get all events matching eventType
	*/
	
	
	
	// We're returning JSON data 
	header('Content-Type: application/javascript');
	
	
	// Break up URI into tokens on "/" symbol 
	$queryArray = array_slice(array_filter(explode( "/", strtolower($_SERVER['REQUEST_URI']) )), 0);
	
	
	// First two elements should be /api/event(.php?)/
	if ( $queryArray[0] == "api" ) {
		array_shift($queryArray);
	}
	else {
		print_r("PATH ERROR!\n");
	}
	if ( $queryArray[0] == "event.php" || $queryArray[0] == "event" ) {
		array_shift($queryArray);
	}
	else {
		print_r("PATH ERROR!\n");
	}
	
	
	// Save the request method being used (GET, POST, PUT, DELETE) 
	$REST_vars = [ "method" => strtolower($_SERVER['REQUEST_METHOD']) ];
	
	
	// Display options for our API 
	$REST_strs_opts = [ "sort", "category", "group", "country", "show" ];
	
	
	// Prepare databases
	require $_SERVER['DOCUMENT_ROOT'] . "/db.php";
	$DBs = new Database();
	if ( is_null($DBs) ) {
		print_r("DB ERR");
		exit;
	}
	
	
	// No arguments are given 
	if ( count($queryArray) == 0 ) {
		print_r("DISPLAY ALL EVENTS (GET-POST)\n");
	}
	
	// Associated event ID given 
	elseif ( is_numeric($queryArray[0]) ) {
		
		$REST_vars{eventID} = intval(array_shift($queryArray));
		print_r("EVENT ID: " . $REST_vars["eventID"] . "\n");
		
		
		if ( count($queryArray) == 0 ) {
			print_r("FULL EVENT INFO (GET-PUT-DELETE)\n");
			$REST_vars["simple"] = 0;
			require "event-id.php";
			$handler = new EventID($REST_vars, $DBs);
		}
		elseif ( $queryArray[0] == "simple" ) {
			print_r("SIMPLE EVENT INFO\n");
			array_shift($queryArray);
			$REST_vars["simple"] = 1;
			require "event-id.php";
			$handler = new EventID($REST_vars, $DBs);
		}
		elseif ( $queryArray[0] == "attendants" ) {
			print_r("ATTENDANTS EVENT INFO (GET-POST-DELETE)\n");
			array_shift($queryArray);
		}
		elseif ( $queryArray[0] == "comments" ) {
			
			// Associated comment ID given 
			if ( is_numeric($queryArray[1]) ) {
				array_shift($queryArray);
				$REST_vars{commentID} = intval(array_shift($queryArray));
				print_r("COMMENT ID: " . $REST_vars["commentID"] . " (GET-DELETE)\n");
			}
			else {
				print_r("COMMENTS EVENT INFO (GET-POST)\n");
				array_shift($queryArray);
			}
			
		}
		else {
			print_r("PARSE ERROR (primary-eventID)!\n");
		}
		
	}
	
	// No eventID, but options given 
	else {
		
		print_r("DISPLAY ALL EVENTS (OPTIONS)\n");
		
		for($i = 0; $i < count($queryArray); $i = $i+2) {
			if ( in_array( $queryArray[$i], $REST_strs_opts ) && ($i + 1) < count($queryArray) ) {
				if ( is_numeric($queryArray[$i+1]) ) {
					print_r("OPTION GIVEN: " . $queryArray[$i] . ", VALUE: " . $queryArray[$i+1] . "\n");
					$REST_vars[$queryArray[$i]] = $queryArray[$i+1];
				}
				else {
					print_r("PARSE ERROR (secondary)!\n");
				}
			}
			else {
				print_r("PARSE ERROR (primary)!\n");
			}
		}
		
	}
	
	print_r($REST_vars);
	
?>