<?
	
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
	
	
	// GT-Hosted URLs for GT Login 
	$GLOBALS["GATECH_WIDGET"] = "https://dev.m.gatech.edu/developer/cmunson3/widget/jackelo/";
	$GLOBALS["GATECH_LOGIN"] = "https://dev.m.gatech.edu/developer/cmunson3/api/jackelo/login/";
	
	
	// Amount of time to allow before login challenge expiration (in minutes)
	$GLOBALS["CHALLENGE_TO"] = 5;
	
	
	// Time before session cookie expires (in seconds)
	$GLOBALS["COOKIE_EXPR"] = 60*60*24*7;
	
	
	// Number of times login should fail before we refuse to login (loop detection) 
	$GLOBALS["LOOP_DETECT"] = 2;
	
	
	// Do not print errors/warnings unless debugging is set 
	if (!$GLOBALS["DEBUG"]) {
		error_reporting(0);
	}
	
?>