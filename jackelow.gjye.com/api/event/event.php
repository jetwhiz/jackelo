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
	
	
	
	// Display options for our API 
	$REST_strs_opts = [ "category", "country", "show", "start" ];
	
	
	
	// Get event types from database 
	$GLOBALS["EventTypes"] = [];
	{
		$select = "
				SELECT `name`, `id`
				FROM `EventTypes` 
		";
		$binds = null;
		
		$res = $DBs->select($select, $binds);
		if ( is_null($res) ) {
			$e = new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "Event Error: Failed to retrieve internal request.");
			$e->kill();
		}
		
		while ($row = $res->fetch_assoc()) {
			$GLOBALS["EventTypes"][ $row["name"] ] = $row["id"];
		}
	}
	
	
	// Associated event ID given 
	if ( count($queryArray) && is_numeric($queryArray[0]) ) {
		
		// Retrieve eventID (it will always be the first token) 
		$REST_vars["eventID"] = intval(array_shift($queryArray), 10);
		if ($GLOBALS["DEBUG"]) {
			print_r("EVENT ID: " . $REST_vars["eventID"] . "\n");
		}
		
		// No other options specified (give all info about given eventID) 
		if ( count($queryArray) == 0 ) {
			if ($GLOBALS["DEBUG"]) {
				print_r("FULL EVENT INFO (GET-PUT-DELETE)\n");
			}
			
			$REST_vars["simple"] = 0;
			require "event-id.php";
			try {
				$handler = new EventID($REST_vars, $DBs, $User);
			} catch (Error $e) {
				$e->kill();
			}
		}
		
		// Simple:  Only give basic info about given eventID 
		elseif ( $queryArray[0] == "simple" ) {
			if ($GLOBALS["DEBUG"]) {
				print_r("SIMPLE EVENT INFO\n");
			}
			
			array_shift($queryArray);
			$REST_vars["simple"] = 1;
			require "event-id.php";
			try {
				$handler = new EventID($REST_vars, $DBs, $User);
			} catch (Error $e) {
				$e->kill();
			}
		}
		
		// Attendants: Provide array of attendants (as userID) to given eventID 
		elseif ( $queryArray[0] == "attendants" ) {
			if ($GLOBALS["DEBUG"]) {
				print_r("ATTENDANTS EVENT INFO (GET-POST-DELETE)\n");
			}
			
			array_shift($queryArray);
			require "event-attendants.php";
			try {
				$handler = new EventAttendants($REST_vars, $DBs, $User);
			} catch (Error $e) {
				$e->kill();
			}
		}
		
		// Comments: Provide comment(s) to given eventID or commentID (if supplied) 
		elseif ( $queryArray[0] == "comments" ) {
			
			// Associated comment ID given 
			if ( is_numeric($queryArray[1]) ) {
				array_shift($queryArray);
				$REST_vars["commentID"] = intval(array_shift($queryArray), 10);
				
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
			
			require "event-comments.php";
			try {
				$handler = new EventComments($REST_vars, $DBs, $User);
			} catch (Error $e) {
				$e->kill();
			}
		}
		
		// Invalid token -- skip it 
		else {
			if ($GLOBALS["DEBUG"]) {
				print_r("PARSE ERROR (primary-eventID)!\n");
			}
			$e = new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "RESTful Error: Bad request.");
			$e->kill();
		}
		
	}
	
	// Group 
	elseif ( count($queryArray) && $queryArray[0] == "group" ) {
		$REST_vars["group"] = 1;
		
		if ($GLOBALS["DEBUG"]) {
			print_r("GROUPED EVENT INFO (GET)\n");
		}
		
		array_shift($queryArray);
		require "event-all.php";
		try {
			$handler = new EventAll($REST_vars, $DBs, $User);
		} catch (Error $e) {
			$e->kill();
		}
	}	
	
	// No eventID, but options given 
	else {
		
		if ($GLOBALS["DEBUG"]) {
			print_r("DISPLAY ALL EVENTS (OPTIONS)\n");
		}
		
		// parse all options given in URI (only keep valid ones) 
		for($i = 0; $i < count($queryArray); $i = $i+2) {
			
			// Supplied token is valid (must next validate proceeding token) 
			if ( in_array( $queryArray[$i], $REST_strs_opts ) && ($i + 1) < count($queryArray) ) {
				
				// Proceeding token is a number (correct!) -- save pair 
				if ( is_numeric($queryArray[$i+1]) ) {
					if ($GLOBALS["DEBUG"]) {
						print_r("OPTION GIVEN: " . $queryArray[$i] . ", VALUE: " . $queryArray[$i+1] . "\n");
					}
					
					$REST_vars[$queryArray[$i]] = $queryArray[$i+1];
				}
				
				// Proceeding token was not a number -- reject pair 
				else {
					if ($GLOBALS["DEBUG"]) {
						print_r("PARSE ERROR (secondary)!  Not numeric '" . $queryArray[$i+1] . "'\n");
					}
					$e = new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "RESTful Error: Bad request.");
					$e->kill();
				}
			}
			
			// Invalid token (pair?) supplied (note: this gobbles up next token!) 
			else {
				if ($GLOBALS["DEBUG"]) {
					print_r("PARSE ERROR (primary)!\n");
				}
				$e = new Error($GLOBALS["HTTP_STATUS"]["Bad Request"], "RESTful Error: Bad request.");
				$e->kill();
			}
		}
		
		require "event-all.php";
		try {
			$handler = new EventAll($REST_vars, $DBs, $User);
		} catch (Error $e) {
			$e->kill();
		}
	}
	
	if ($GLOBALS["DEBUG"]) {
		print_r($REST_vars);
	}
	
?>