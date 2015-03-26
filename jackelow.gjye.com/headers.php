<?
	
	// Internal paths
	$GLOBALS["WEBAPP_PATH"] = "/webapp/";
	$GLOBALS["LOGIN_PATH"] = "/login/";
	$GLOBALS["GTLOGIN_PATH"] = "/login/gt/";
	$GLOBALS["MOBLOGIN_PATH"] = "/login/mobile/";
	$GLOBALS["DEMOLOGIN_PATH"] = "/login/demo/";
	
	
	// Show debugging output 
	$GLOBALS["DEBUG"] = 0;
	
	
	// Image folder
	$GLOBALS["IMG_DIR"] = 'https://'. $_SERVER['HTTP_HOST'] . "/imgs/";
	
	
	// Geolocation API URI
	$GLOBALS["GEOLOC"] = "http://maps.google.com/maps/api/geocode/json?address=";
	
	
	// Generate HTTP Status codes index 
	$GLOBALS["HTTP_STATUS"] = [
		"OK" => 200, "Not Found" => 404, "Created" => 201, "Not Modified" => 304, 
		"Bad Request" => 400, "Forbidden" => 403, "Internal Error" => 500
	];
	
	
	// GT-Hosted URLs for GT Login 
	$GLOBALS["GATECH_WIDGET"] = "https://dev.m.gatech.edu/developer/cmunson3/widget/jackelo/";
	$GLOBALS["GATECH_LOGIN"] = "https://dev.m.gatech.edu/developer/cmunson3/api/jackelo/login/";
	
	
	// Amount of time to allow before login challenge expiration (in seconds)
	$GLOBALS["CCHALLENGE_TO"] = 300;
	$GLOBALS["SCHALLENGE_TO"] = 30;
	
	
	// Time before session cookie expires (in seconds)
	$GLOBALS["COOKIE_EXPR"] = 60*60*24*7;
	
	
	// Number of times login should fail before we refuse to login (loop detection) 
	$GLOBALS["LOOP_DETECT"] = 2;
	
	
	// Results per page 
	$GLOBALS["NUM_RESULTS"] = 10;
	
	
	// Max field lengths for various inputs
	$GLOBALS["MAX_LENGTHS"] = [
		"category_name" => 35,
		"event_description" => 2500,
		"event_name" => 100,
		"destination_address" => 100,
		"comment_length" => 1000
	];
	
	
	// Do not print errors/warnings unless debugging is set 
	if (!$GLOBALS["DEBUG"]) {
		error_reporting(0);
	}
	
?>