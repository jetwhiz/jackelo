<?
	/* RESTful queries can take the forms:
		/event/ 				-- handle events
		/category/				-- handle categories
		/country/				-- handle countries
		/user/					-- handle users 
	*/
	
	
	// Show debugging output 
	$GLOBALS["DEBUG"] = 0;
	
	
	// Image folder
	$GLOBALS["IMG_DIR"] = 'https://'. $_SERVER['HTTP_HOST'] . "/imgs/";
	
	
	// Geolocation API URI
	$GLOBALS["GEOLOC"] = "http://maps.google.com/maps/api/geocode/json?address=";
	
	
	// Generate HTTP Status codes index 
	$GLOBALS["HTTP_STATUS"] = [
		"OK" => 200, "Not Found" => 404, "Created" => 201, 
		"Bad Request" => 400, "Forbidden" => 403, "Internal Error" => 500
	];
	
	
	// Do not print errors/warnings unless debugging is set 
	if (!$GLOBALS["DEBUG"]) {
		error_reporting(0);
	}
	
	// We're returning JSON data 
	header('Content-Type: application/javascript; charset=utf-8');
	
	
	// Pull in toolkits for all instances 
	require "toolkit.php";
	require "error.php";
	require "handler.php";
	
	
	// Break up URI into tokens on "/" symbol 
	$queryArray = Toolkit::array_clean(explode( "/", strtolower(urldecode($_SERVER['REQUEST_URI'])) ));
	////
	
	
	
	// Save the request method being used (GET, POST, PUT, DELETE) 
	$REST_vars = [ "method" => strtolower($_SERVER['REQUEST_METHOD']) ];
	
	
	// Prepare databases
	require "db.php";
	try {
		$DBs = new Database();
	} catch (Error $e) {
		$e->kill();
	}
	////
	
	
	// Prepare current user (and ensure they are logged in) 
	require "user.php";
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
		"country" => "country/country.php", "eventtype" => "eventType/eventType.php", 
		"session" => "session/session.php", "user" => "user/user.php"
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
		
		try {
			require $REST_types[$key];
		} catch (Error $e) {
			$e->kill();
		} catch (Exception $e) {
			$e = new Error($GLOBALS["HTTP_STATUS"]["Internal Error"], "RESTful Error: Unknown error.");
			$e->kill();
		}
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