Testing<br /><br />


<?
	// Can take the forms:
	//	(/)? 				-- display all events
	//	#/simple(/)?		-- get all events (simple info)
	//	#/attendants(/)?	-- get attendants for eventID
	//	#/comments(/)?		-- get comments for eventID
	//	#/comments/#(/)?	-- get commentID
	//	#(/)?				-- get event by eventID
	//
	// OR in any order (and any combo): 
	// 	(sort/#)(/)?		-- get all events, sorting by sortID
	//	(category/#)(/)?	-- get all events matching categoryID
	//	(groupby/#)(/)?		-- get all events grouped (0 - false, 1 - true) 
	//	(country/#)(/)?		-- get all events matching countryID
	//	(show/#)(/)?		-- get all events matching eventType
	
	
	parse_str( $_SERVER['QUERY_STRING'], $QUERY );
	$queryArray = array_filter(explode( "/", $QUERY{q} ));
	
	$REST_vars;
	$REST_strs_opts = [ "sort", "category", "group", "country", "show" ];
	
	
	// No arguments are given 
	if ( count($queryArray) == 0 ) {
		print_r("DISPLAY ALL EVENTS<br />");
	}
	
	// Associated event ID given 
	elseif ( is_numeric($queryArray[0]) ) {
		
		$REST_vars{eventID} = intval(array_shift($queryArray));
		print_r("EVENT ID: " . $REST_vars{eventID} . "<br />");
		
		if ( $queryArray[0] == "simple" ) {
			print_r("SIMPLE EVENT INFO<br />");
			array_shift($queryArray);
		}
		elseif ( $queryArray[0] == "attendants" ) {
			print_r("ATTENDANTS EVENT INFO<br />");
			array_shift($queryArray);
		}
		elseif ( $queryArray[0] == "comments" ) {
			
			// Associated comment ID given 
			if ( is_numeric($queryArray[1]) ) {
				array_shift($queryArray);
				$REST_vars{commentID} = intval(array_shift($queryArray));
				print_r("COMMENT ID: " . $REST_vars{commentID} . "<br />");
			}
			else {
				print_r("COMMENTS EVENT INFO<br />");
				array_shift($queryArray);
			}
			
		}
		else {
			print_r("FULL EVENT INFO<br />");
		}
		
	}
	
	// No eventID, but options given 
	else {
		
		print_r("DISPLAY ALL EVENTS (OPTIONS)<br />");
		
		for($i = 0; $i < count($queryArray); $i = $i+2) {
			if ( in_array( $queryArray[$i], $REST_strs_opts ) && ($i + 1) < count($queryArray) ) {
				if ( is_numeric($queryArray[$i+1]) ) {
					print_r("OPTION GIVEN: " . $queryArray[$i] . ", VALUE: " . $queryArray[$i+1] . "<br />");
					$REST_vars{$queryArray[$i]} = $queryArray[$i+1];
				}
				else {
					print_r("PARSE ERROR (secondary)!<br />");
				}
			}
			else {
				print_r("PARSE ERROR (primary)!<br />");
			}
		}
		
	}
	
?>

