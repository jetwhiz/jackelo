<?
	/* RESTful queries can take the forms:
		/event/ 				-- handle events
		/category/				-- handle categories
		/country/				-- handle countries
		/type/					-- handle event types 
		/user/					-- handle users 
	*/
	
	
	// Pull in toolkits for all instances 
	require "../headers.php";
	require "../db.php";
	require "../toolkit.php";
	require "../error.php";
	require "../user.php";
	require "../auth.php";
	require "handler.php";
	
	
	// We're returning JSON data 
	header('Content-Type: application/javascript; charset=utf-8');
	
	
	// Break up URI into tokens on "/" symbol 
	$URI = $_SERVER['REQUEST_URI'];
	if ( strpos($URI, "?") !== false ) {
		$URI = strtok($URI,'?');
	}
	$queryArray = Toolkit::array_clean(explode( "/", strtolower(urldecode($URI)) ));
	////
	
	
	// Grab PUT data (assuming it is type application/x-www-form-urlencoded) 
	if ( $_SERVER['REQUEST_METHOD'] == "PUT" && !isset($GLOBALS["_PUT"]) ) {
		$putdata = file_get_contents("php://input");
		parse_str($putdata, $GLOBALS["_PUT"]);
	}
	
	
	// Save the request method being used (GET, POST, PUT, DELETE) 
	$REST_vars = [ "method" => strtolower($_SERVER['REQUEST_METHOD']) ];
	
	
	// Prepare databases
	try {
		$DBs = new Database();
	} catch (Error $e) {
		$e->kill();
	}
	////
	
	
	// Ensure user is logged in, get User object 
	$Auth = new Authenticate();
	$User = $Auth->getUser(false);
	if ( is_null( $User ) ) {
		$e = new Error($GLOBALS["HTTP_STATUS"]["Forbidden"], "RESTful Error: You must be logged in.");
		$e->kill();
	}
	////
	
	
	// Display options for our API 
	$REST_types = [ 
		"event" => "event/event.php", "category" => "category/category.php", 
		"country" => "country/country.php", "type" => "type/eventType.php", 
		"user" => "user/user.php"
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