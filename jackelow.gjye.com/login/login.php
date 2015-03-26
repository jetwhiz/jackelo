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
	
	
	
	// First elements should be /login/ 
	if ( $queryArray[0] == "login" ) {
		array_shift($queryArray);
	}
	//// 
	
	
	
	// No options given 
	if ( !count($queryArray) ) {
		$Auth = new Authenticate();
		if ( $Auth->isLoggedIn() ) {
			header("Location: https://" . $_SERVER['HTTP_HOST'] . $GLOBALS["WEBAPP_PATH"]);
			die;
		}
		
		$head = "";
		$demoLoginURL = $GLOBALS["DEMOLOGIN_PATH"];
		$mobLoginURL = $GLOBALS["MOBLOGIN_PATH"];
		$gtLoginURL = $GLOBALS["GTLOGIN_PATH"];
		$body = <<<EOBT
			<div id='login-opts'>
				<h1>Choose a login method: </h1>
				<a href='$gtLoginURL' title='GT Login' id='gt-login'><img src='/imgs/GTlogo.png' alt='GT' />&nbsp;GT Login</a>
				<a href='$demoLoginURL' title='Demo Login' id='demo-login'><img src='/imgs/guest.png' alt='GT' />&nbsp;Demo Login</a>
			</div>

EOBT;
		send($body, $GLOBALS["HTTP_STATUS"]["Forbidden"]);
	}
	// Type was GT Login
	elseif ( $queryArray[0] == "gt" ) {
		$key = array_shift($queryArray);
		
		$Auth = new Authenticate();
		$User = $Auth->getUser();
		
		// Go home 
		header("Location: https://" . $_SERVER['HTTP_HOST'] . $GLOBALS["WEBAPP_PATH"]);
		die;
	}
	// Type was Mobile Login (initiate)
	elseif ( $queryArray[0] == "mobile" ) {
		$key = array_shift($queryArray);
		
		$Auth = new Authenticate(true);
		$User = $Auth->getUser();
		
		// Already logged in - redirect to mobile app 
		header("Location: jackelo://?sessionID=" . $_COOKIE["sessionID"]);
		die;
	}
	// Type was Demo Login
	elseif ( $queryArray[0] == "demo" ) {
		$key = array_shift($queryArray);
		
		// Create demo user (should redirect home upon success) 
		$Auth = new Authenticate();
		if ( !$Auth->isLoggedIn() ) {
			$Auth->createDemo();
		}
		
		// Go home 
		header("Location: https://" . $_SERVER['HTTP_HOST'] . $GLOBALS["WEBAPP_PATH"]);
		die;
	}
	// Or it was invalid 
	else {
		echo "Invalid parameter supplied";
		die;
	}
	////
	
	
	
	// Sends template page to the user, including required headers, body and return code 
	function send( $content, $code ) {
		
		// Load template html 
		$template = <<<EOT
			<!DOCTYPE html>
			<html lang="en">
				<head>
					<title>Jackelo - Login</title>
					
					<meta charset="utf-8" />
					<meta http-equiv="X-UA-Compatible" content="IE=edge;" />
					<meta name="viewport" content="width=device-width, initial-scale=1" />
					
					<link type="text/css" rel="stylesheet" href="/webapp/base.css" />
				</head>
				<body>
					#contentBodyWrapper#
				</body>
			</html>

EOT;
		
		// Print out header status 
		http_response_code($code);
		
		// Replace content in template 
		$template_pop = str_replace("#contentBodyWrapper#", $content, $template);
		
		// Declare we're returning HTML in UTF-8
		header('Content-Type: text/html; charset=utf-8');
		
		// Protect from framing/XSS
		header("X-Frame-Options: DENY");
		header("Content-Security-Policy: frame-src 'none'; object-src 'none'; connect-src 'self'");
		
		// Send to user and quit 
		echo $template_pop;
		die;
	}
	////
	
?>