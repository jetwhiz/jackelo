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
		
		<script type="text/javascript" src="/webapp/addEvent.js"></script>
		<link type="text/css" rel="stylesheet" href="/webapp/addEvent.css" />
		
	</head>
	<body>
		<div id="header">
			<a class="header-text" href="/webapp/">
				<h1>Jackelo</h1>
			</a>
			<a title="Add Event" id="add-event" class="addeventicon" href="javascript: void(0);">&nbsp;</a>
			<a title="Map View" class="mapicon" href="/webapp/map/">&nbsp;</a>
		</div>
		<div id="content-body">
			<div id="wrapper">
				<? echo $contentBodyWrapper; ?>
			</div>
		</div>
		<div id="dialog-form" title="Create new event">
			<p class="validateTips">All form fields are required.</p>
			
			<form>
				<fieldset>
					<label for="name">Event name</label>
					<input type="text" name="name" id="name" value="" class="text ui-widget-content ui-corner-all" />
					
					<label for="datetimeStart">Start date</label>
					<input type="text" id="datetimeStart" name="datetimeStart" class="text ui-widget-content ui-corner-all">
					
					<label for="datetimeEnd">End date</label>
					<input type="text" id="datetimeEnd" name="datetimeEnd" class="text ui-widget-content ui-corner-all">
					
					<label for="description">Description</label>
					<input type="text" name="description" id="description" value="" class="text ui-widget-content ui-corner-all" />
					
					<label for="category">Categories</label>
					<div id="categories"></div>
					<input type="text" name="category" id="category" value="" class="text ui-widget-content ui-corner-all" />
					
					<!-- These need to be dynamically populated! -->
					<div id="eventTypeID">
						<input type="radio" id="eventType1" name="eventTypeID" value="1" /><label for="eventType1">Local Event</label>
						<input type="radio" id="eventType2" name="eventTypeID" value="2" /><label for="eventType2">GTL Event</label>
						<input type="radio" id="eventType3" name="eventTypeID" value="3" checked="checked" /><label for="eventType3">Trip</label>
						<input type="radio" id="eventType4" name="eventTypeID" value="4" /><label for="eventType4">Info</label>
					</div>
					
					
					<div id="destination-injection-point"></div>
					
					<a href="javascript: void(0);" onclick="addDestination()" style="display: block; padding: 10px; " title="Add destination">Add destination</a>
					
					<fieldset id="destination-template" style="display: none; background-color: #efefef;">
						<legend>Destination</legend>
						
						<label>Address</label>
						<input type="text" name="destination[][address]" value="" class="text ui-widget-content ui-corner-all" />
						
						<label>Start date</label>
						<input type="text" name="destination[][datetimeStart]" class="datetimeStart text ui-widget-content ui-corner-all">
						
						<label>End date</label>
						<input type="text" name="destination[][datetimeEnd]" class="datetimeEnd text ui-widget-content ui-corner-all">
						
						<div>
							<label>Country</label>
							<select name="destination[][countryID]" class="countryID ui-widget-content ui-corner-all"></select>
							
							<label>City</label>
							<input type="text" name="destination[][cityName]" class="cityName text ui-widget-content ui-corner-all">
							<input type="hidden" name="destination[][cityID]" class="cityID" /> <!-- cityID (from city) -->
						</div>
						
						<a href="javascript: void(0);" onclick="removeDestination(this)" style="display: block;" title="Remove">Remove</a>
					</fieldset>
					
					<!-- Allow form submission with keyboard without duplicating the dialog button -->
					<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
				</fieldset>
			</form>
		</div>
	</body>
</html>
