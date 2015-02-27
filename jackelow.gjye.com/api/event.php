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
	
	
	// Show debugging output 
	$GLOBALS["DEBUG"] = 1;
	
	
	// We're returning JSON data 
	header('Content-Type: application/javascript');
	
	
	// Pull in toolkit for all instances 
	require $_SERVER['DOCUMENT_ROOT'] . "/toolkit.php";
	
	
	// Break up URI into tokens on "/" symbol 
	$queryArray = explode( "/", strtolower($_SERVER['REQUEST_URI']) );
	$queryArray = array_filter($queryArray, function($v) {
		return !is_null($v) && isset($v) && $v != "";
	});
	$queryArray = array_slice($queryArray, 0);
	////
	
	
	// First two elements should be /api/event(.php?)/
	if ( $queryArray[0] == "api" ) {
		array_shift($queryArray);
	}
	else {
		if ($GLOBALS["DEBUG"]) {
			print_r("PATH ERROR!\n");
		}
		
		throw new Exception('RESTful Error: Request path incorrect.');
	}
	if ( $queryArray[0] == "event.php" || $queryArray[0] == "event" ) {
		array_shift($queryArray);
	}
	else {
		if ($GLOBALS["DEBUG"]) {
			print_r("PATH ERROR!\n");
		}
		
		throw new Exception('RESTful Error: Request path incorrect.');
	}
	////
	
	
	// Save the request method being used (GET, POST, PUT, DELETE) 
	$REST_vars = [ "method" => strtolower($_SERVER['REQUEST_METHOD']) ];
	
	
	// Prepare databases
	require $_SERVER['DOCUMENT_ROOT'] . "/db.php";
	$DBs = new Database();
	if ( is_null($DBs) ) {
		if ($GLOBALS["DEBUG"]) {
			print_r("DB ERR");
		}
		
		throw new Exception('Database Error: Could not establish connection.');
	}
	////
	
	
	// Display options for our API 
	$REST_strs_opts = [ "sort", "category", "group", "country", "show" ];
	
	
	// Get sort types from database 
	$GLOBALS["SortTypes"] = [];
	{
		$select = "
				SELECT `name`, `id`
				FROM `SortTypes` 
		";
		$binds = null;
		$res = $DBs->select($select, $binds);
		while ($row = $res->fetch_assoc()) {
			$GLOBALS["SortTypes"][ $row["name"] ] = $row["id"];
		}
	}
	
	
	
	// Associated event ID given 
	if ( count($queryArray) && is_numeric($queryArray[0]) ) {
		
		$REST_vars{eventID} = intval(array_shift($queryArray));
		if ($GLOBALS["DEBUG"]) {
			print_r("EVENT ID: " . $REST_vars["eventID"] . "\n");
		}
		
		
		if ( count($queryArray) == 0 ) {
			if ($GLOBALS["DEBUG"]) {
				print_r("FULL EVENT INFO (GET-PUT-DELETE)\n");
			}
			
			$REST_vars["simple"] = 0;
			require "event-id.php";
			$handler = new EventID($REST_vars, $DBs);
		}
		elseif ( $queryArray[0] == "simple" ) {
			if ($GLOBALS["DEBUG"]) {
				print_r("SIMPLE EVENT INFO\n");
			}
			
			array_shift($queryArray);
			$REST_vars["simple"] = 1;
			require "event-id.php";
			$handler = new EventID($REST_vars, $DBs);
		}
		elseif ( $queryArray[0] == "attendants" ) {
			if ($GLOBALS["DEBUG"]) {
				print_r("ATTENDANTS EVENT INFO (GET-POST-DELETE)\n");
			}
			
			array_shift($queryArray);
		}
		elseif ( $queryArray[0] == "comments" ) {
			
			// Associated comment ID given 
			if ( is_numeric($queryArray[1]) ) {
				array_shift($queryArray);
				$REST_vars["commentID"] = intval(array_shift($queryArray));
				
				if ($GLOBALS["DEBUG"]) {
					print_r("COMMENT ID: " . $REST_vars["commentID"] . " (GET-DELETE)\n");
				}
			}
			else {
				if ($GLOBALS["DEBUG"]) {
					print_r("COMMENTS EVENT INFO (GET-POST)\n");
				}
				
				array_shift($queryArray);
			}
			
		}
		else {
			if ($GLOBALS["DEBUG"]) {
				print_r("PARSE ERROR (primary-eventID)!\n");
			}
		}
		
	}
	
	// No eventID, but options given 
	else {
		
		if ($GLOBALS["DEBUG"]) {
			print_r("DISPLAY ALL EVENTS (OPTIONS)\n");
		}
		
		for($i = 0; $i < count($queryArray); $i = $i+2) {
			if ( in_array( $queryArray[$i], $REST_strs_opts ) && ($i + 1) < count($queryArray) ) {
				if ( is_numeric($queryArray[$i+1]) ) {
					if ($GLOBALS["DEBUG"]) {
						print_r("OPTION GIVEN: " . $queryArray[$i] . ", VALUE: " . $queryArray[$i+1] . "\n");
					}
					$REST_vars[$queryArray[$i]] = $queryArray[$i+1];
				}
				else {
					if ($GLOBALS["DEBUG"]) {
						print_r("PARSE ERROR (secondary)!  Not numeric '" . $queryArray[$i+1] . "'\n");
					}
				}
			}
			else {
				if ($GLOBALS["DEBUG"]) {
					print_r("PARSE ERROR (primary)!\n");
				}
			}
		}
		
		require "event-all.php";
		$handler = new EventAll($REST_vars, $DBs);
	}
	
	if ($GLOBALS["DEBUG"]) {
		print_r($REST_vars);
	}
	
?>