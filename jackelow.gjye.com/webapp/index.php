<?
	
	// Pull in toolkits for all instances 
	require "../headers.php";
	require "../toolkit.php";
	require "../error.php";
	require "../user.php";
	require "../auth.php";
	
	
	
	// Break up URI into tokens on "/" symbol 
	$URI = $_SERVER['REQUEST_URI'];
	if ( strpos($URI, "?") !== false ) {
		$URI = strtok($URI,'?');
	}
	$queryArray = Toolkit::array_clean(explode( "/", strtolower(urldecode($URI)) ));
	////
	
	
	// Ensure user is logged in 
	$Auth = new Authenticate();
	$User = $Auth->getUser();
	$usrname = $User->getUsername();
	if ( is_null( $User ) ) {
		send("", "You must be logged in!", $GLOBALS["HTTP_STATUS"]["Forbidden"]);
	}
	////
	
	
	// Send cookies along with GET requests 
	$opts = array(
			'http' => array(
				'method' => "GET",
				'header' => "Accept-language: en\r\n" .
				"Cookie: sessionID=" . $_COOKIE["sessionID"] . "\r\n"
			)
		);
	$getContext = stream_context_create($opts);
	////
	
	
	// Display options for our web app 
	$REST_types = [ 
		"event" => "index-event.php", 
		"createevent" => "index-createevent.php", 
		"map" => "index-map.php"
	];
	////
	
	
	
	// First elements should be /webapp/ 
	if ( $queryArray[0] == "webapp" ) {
		array_shift($queryArray);
	}
	//// 
	
	
	// Second element should be as listed above 
	if ( array_key_exists($queryArray[0], $REST_types) ) {
		$key = array_shift($queryArray);
		if ( is_null($key) ){
			send("", "Error: Request path incorrect.", $GLOBALS["HTTP_STATUS"]["Not Found"]);
		}
		
		try {
			require $REST_types[$key];
		} catch (Exception $e) {
			send("", "Error: Unknown error.", $GLOBALS["HTTP_STATUS"]["Internal Error"]);
		}
	}
	// Or the main index page (list all events) 
	else {
		try {
			require "index-all.php";
		} catch (Exception $e) {
			send("", "Error: Unknown error.", $GLOBALS["HTTP_STATUS"]["Internal Error"]);
		}
	}
	////
	
	
	// Sends template page to the user, including required headers, body and return code 
	function send( $header, $body, $code ) {
		
		// Load template html 
		$template = file_get_contents("template.html");
		if ( !$template ) {
			$code = $GLOBALS["HTTP_STATUS"]["Internal Error"];
			$body = "Error: Can't get template";
		}
		
		// Print out header status 
		http_response_code($code);
		
		// Replace head and body in template 
		$template_pop = str_replace("#headTags#", $header, $template);
		$template_pop = str_replace("#contentBodyWrapper#", $body, $template_pop);
		
		// Declare we're returning HTML in UTF-8
		header('Content-Type: text/html; charset=utf-8');
		
		// Send to user and quit 
		echo $template_pop;
		die;
	}
	////
	
?>
