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
	
	
	// First elements should be /webapp/ 
	if ( $queryArray[0] == "webapp" ) {
		array_shift($queryArray);
	}
	//// 
	
	
	// Ensure user is logged in 
	$Auth = new Authenticate();
	$User = $Auth->getUser();
	$usrname = $User->getUsername();
	
	
	
	$headTags = "";
	$contentBodyWrapper = "";
	
	
	
	// Param indicated "map" 
	if ( count($queryArray) && $queryArray[0] == "map" ) {
		array_shift($queryArray);
		
		$headTags = <<<EOHT
			<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD_a-dYE-RmPRjgAn1dVJfSJ9IAVvE-7rQ"></script>
			<script type="text/javascript" src="/webapp/map.js"></script>
			<link type="text/css" rel="stylesheet" href="/webapp/index.css" />

EOHT;
	}
	
	// Param indicated "event" 
	elseif ( count($queryArray) && $queryArray[0] == "event" ) {
		array_shift($queryArray);
		
		// Ensure eventID given 
		if ( count($queryArray) && is_numeric($queryArray[0]) ) {
			
		}
		else {
			echo "no eventID";
			die(0);
		}
	}
	
	
	// Otherwise it is some flavor of index 
	else {
		$headTags = <<<EOHT
			<script type="text/javascript" src="/webapp/index.js"></script>
			<link type="text/css" rel="stylesheet" href="/webapp/index.css" />

EOHT;

		$contentBodyWrapper = <<<EOBW
			<h2>&nbsp;Welcome, $usrname!</h2>
			
			<div id="injection-point"></div>
			<div id="event-template">
				<div class="event-block">
					<div class="tr">
						<div class="thumb-cell"></div>
						<div class="info-cell">
							<div class="title-cell"></div>
							<div class="username-cell"></div>
							<div class="dates-cell"></div>
							<div class="tags-cell"></div>
							<div class="description-cell"></div>
						</div>
					</div>
				</div>
				<br />
			</div>

EOBW;
	}
	
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Jackelo - GTL Events Manager</title>
		
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge;" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		
		<link type="text/css" rel="stylesheet" href="/webapp/base.css" />
		<link type="text/css" rel="stylesheet" href="/webapp/jquery/jquery-ui.min.css" />
		<script type="text/javascript" src="/webapp/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="/webapp/jquery/jquery-ui.min.js"></script>
		
		<? echo $headTags; ?>
		
	</head>
	<body>
		<div id="header">
			<a class="header-text" href="/webapp/">
				<h1>Jackelo</h1>
			</a>
			<a title="Add Event" class="addeventicon" href="javascript: void(0);">&nbsp;</a>
			<a title="Map View" class="mapicon" href="/webapp/map/">&nbsp;</a>
		</div>
		<div id="content-body">
			<div id="wrapper">
				<? echo $contentBodyWrapper; ?>
			</div>
		</div>
	</body>
</html>
