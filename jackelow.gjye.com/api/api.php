<?
	/* RESTful queries can take the forms:
		/event/ 				-- handle events
		/category/				-- handle categories
		/city/					-- handle cities 
		/country/				-- handle countries
		/eventType/				-- handle event types (unnecessary?)
		/sortType/				-- handle sort types 
		/session/				-- handle sessions
		/user/					-- handle users 
	*/
	
	
	// Show debugging output 
	$GLOBALS["DEBUG"] = 1;
	
	
	// Generate HTTP Status codes index 
	$GLOBALS["HTTP_STATUS"] = [
		"OK" => 200, "Not Found" => 404, "Created" => 201, 
		"Bad Request" => 400, "Forbidden" => 403, "Internal Error" => 500
	];
	
	
	// We're returning JSON data 
	header('Content-Type: application/javascript; charset=utf-8');
	
	
	// Pull in toolkits for all instances 
	require $_SERVER['DOCUMENT_ROOT'] . "/toolkit.php";
	require $_SERVER['DOCUMENT_ROOT'] . "/error.php";
	
	
	// Break up URI into tokens on "/" symbol 
	$queryArray = Toolkit::array_clean(explode( "/", strtolower($_SERVER['REQUEST_URI']) ));
	////
	
	
	
	// Save the request method being used (GET, POST, PUT, DELETE) 
	$REST_vars = [ "method" => strtolower($_SERVER['REQUEST_METHOD']) ];
	
	
	// Prepare databases
	require $_SERVER['DOCUMENT_ROOT'] . "/db.php";
	try {
		$DBs = new Database();
	} catch (Error $e) {
		$e->kill();
	}
	////
	
	
	// Prepare current user (and ensure they are logged in) 
	require $_SERVER['DOCUMENT_ROOT'] . "/user.php";
	try {
		$User = new User($DBs, 1); // fixed to userID=1 for now (later, pass sessionID) 
	} catch (Error $e) {
		// TODO: If returned error was "Forbidden", send them to login page 
		
		$e->kill();
	}
	////
	
	
	// Display options for our API 
	$REST_types = [ 
		"event" => "event/event.php", "category" => "category/category.php", "city" => "city/city.php", 
		"country" => "country/country.php", "eventType" => "eventType.php", 
		"sortType" => "sortType.php", "session" => "session/session.php", "user" => "user/user.php"
	];
	
	
	// First elements should be /api/ 
	if ( $queryArray[0] == "api" ) {
		array_shift($queryArray);
	}
	else {
		if ($GLOBALS["DEBUG"]) {
			print_r("RESTful Error: Request path incorrect\n");
		}
		
		$e = new Error($GLOBALS["HTTP_STATUS"]["Not Found"], "RESTful Error: Request path incorrect.");
		$e->kill();
	}
	////
	
	
	// Second element should be as listed above 
	if ( array_key_exists($queryArray[0], $REST_types) ) {
		$key = array_shift($queryArray);
		if ( is_null($key) ){
			$e = new Error($GLOBALS["HTTP_STATUS"]["Not Found"], "RESTful Error: Request path incorrect.");
			$e->kill();
		}
		
		require $REST_types[$key];
	}
	else {
		if ($GLOBALS["DEBUG"]) {
			print_r("PATH ERROR!\n");
		}
		
		if ($GLOBALS["DEBUG"]) {
			print_r($queryArray);
		}
		
		$e = new Error($GLOBALS["HTTP_STATUS"]["Not Found"], "RESTful Error: Request path incorrect.");
		$e->kill();
	}
	////
	
?>