<?
	/* RESTful queries can take the forms:
		(/)?				-- get info for current user 
		#(/)?				-- get info for given userID 
	*/
	
	
	
	// Retrieve userID 
	if ( count($queryArray) && is_numeric($queryArray[0]) ) {
		// It was supplied as a token 
		$REST_vars["userID"] = intval(array_shift($queryArray), 10);
	}
	else {
		// Otherwise get info for current user
		$REST_vars["userID"] = $User->getID();
	}
	
	// Debug dump 
	if ($GLOBALS["DEBUG"]) {
		print_r("USER ID: " . $REST_vars["userID"] . "\n");
	}
	
	require "user-id.php";
	try {
		$handler = new UserID($REST_vars, $DBs, $User);
	} catch (Error $e) {
		$e->kill();
	}
	
	
	if ($GLOBALS["DEBUG"]) {
		print_r($REST_vars);
	}
	
?>