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

EOHT;
	}
	
	
	
	// Param indicated "createEvent" 
	elseif ( count($queryArray) && $queryArray[0] == "createevent" ) {
		array_shift($queryArray);
		
		$headTags = "";
		$contentBodyWrapper = "";
	}
	
	
	
	// Param indicated "event" 
	elseif ( count($queryArray) && $queryArray[0] == "event" ) {
		array_shift($queryArray);
		
		// Ensure eventID given 
		if ( count($queryArray) && is_numeric($queryArray[0]) ) {
			$eventID = $queryArray[0];
			
			// Pull in main event data 
			$file = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/event/" . $eventID);
			if ( !$file ) {
				echo "can't connect to API";
				die(0);
			}
			$JSON = json_decode($file, true);
			if ( count($JSON["results"]) != 1 ){
				echo "invalid event ID";
				die(0);
			}
			
			$eventName = $JSON["results"][0]["name"];
			$ownerName = $JSON["results"][0]["username"];
			$dateStart = date("F d, Y", strtotime($JSON["results"][0]["datetimeStart"]));
			$datetimeEnd = date("F d, Y", strtotime($JSON["results"][0]["datetimeEnd"]));
			$eventType = $JSON["results"][0]["eventType"];
			$description = $JSON["results"][0]["description"];
			////
			
			
			// Pull in categories 
			$categories = [];
			foreach ( $JSON["results"][0]["categories"] as $category ) {
				$categories[] = "<a class='tags-link' href='/webapp/category/" . $category["categoryID"] . "/'>" . $category["name"] . "</a>";
			}
			$categoryStr = implode( ", ", $categories );
			////
			
			
			// Pull in destinations 
			$destinations = "";
			foreach ( $JSON["results"][0]["destinations"] as $destination ) {
				$city = $destination["cityName"];
				$country = $destination["countryName"];
				$ddateStart = date("F d, Y", strtotime($destination["datetimeStart"]));
				$ddatetimeEnd = date("F d, Y", strtotime($destination["datetimeEnd"]));
				$address = $destination["address"];
				$img = $destination["img"];
				$thumb = $destination["thumb"];
				
				$destinations .= <<<EOD
					<li class="destination">
						<div class="destination-thumb">
							<a href="$img" title="$city, $country" class="lightbox-image">
								<img src="$thumb" alt="$city, $country" title="$city, $country" class="thumb" />
							</a>
						</div>
						<div class="destination-body"> 
							<div class="dest-body-header">
								<h3>$city, $country</h3>
							</div>
							<div class="dest-body-subsect">
								$ddateStart &ndash; $ddatetimeEnd
							</div>
							<div class="dest-body-subsect">
								<span class="italic">Address:</span> $address 
							</div>
						</div>
						<br style="clear: both;" />
					</li>

EOD;
			}
			if ( $destinations != "" ) {
				$destinations = "<li class='italic'>Destinations: </li>$destinations";
			}
			////
			
			
			// Pull in attendants 
			$attendants = 0;
			$attending = "Attend";
			$attendants_raw = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/event/" . $eventID . "/attendants/");
			if ( !$attendants_raw ) {
				echo "can't connect to API";
				die(0);
			}
			$JSON_attendants = json_decode($attendants_raw, true);
			
			// Update # of attendants
			$attendants = count($JSON_attendants["results"]);
			
			// Figure out if the current user is attending already 
			if ( in_array( $User->getID(), $JSON_attendants["results"] ) ) {
				$attending = "Unattend";
			}
			////
			
			
			// Pull in event comments 
			$comments = "";
			$comments_raw = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/event/" . $eventID . "/comments/");
			if ( !$comments_raw ) {
				echo "can't connect to API";
				die(0);
			}
			$JSON_comments = json_decode($comments_raw, true);
			
			foreach ( $JSON_comments["results"] as $commentID ) {
				$comment_raw = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/event/" . $eventID . "/comments/" . $commentID );
				if ( !$comment_raw ) {
					echo "can't connect to API";
					die(0);
				}
				$JSON_comment = json_decode($comment_raw, true);
				
				$commentUsername = $JSON_comment["results"][0]["username"];
				$datetime = date("F d, Y, H:i:s T", strtotime($JSON_comment["results"][0]["datetime"]));
				$message = $JSON_comment["results"][0]["message"];
				
				$comments .= <<<EOC
					<li class="comment">
						<div class="comment-header">
							<span class="bold">$commentUsername</span> ($datetime) &mdash; 
							<a href="javascript: void(0)" class="remove-comment" commentID="$commentID">Delete</a>
						</div>
						<div class="comment-body">
							$message
						</div>
						<br style="clear: both;" />
					</li>

EOC;
			}
			////
			
			
			$headTags = <<<EOHT

				<link type="text/css" rel="stylesheet" href="/webapp/jquery/jquery.colorbox.css" />
				<script type="text/javascript" src="/webapp/jquery/jquery.colorbox-min.js"></script>
				
				<link type="text/css" rel="stylesheet" href="/webapp/event.css" />
				<script type="text/javascript" src="/webapp/event.js"></script>

EOHT;
			$contentBodyWrapper = <<<EOEP

				<div id="dialog-confirm" title="Delete?">Are you sure you want to delete this?</div>
				
				<div id="event-block">
					<div class="event-row">
						<div class="esubrow-left">
							<h2>$eventName</h2>
						</div>
						<div class="esubrow-right">
							($ownerName)<br />
							[ <a id='attend-event' type="$attending" href='javascript: void(0);'>$attending</a> - 
							 <a id='edit-event' href='javascript: void(0);'>Edit</a> - 
							 <a href="javascript: void(0)" id="remove-event">Delete</a> ]
						</div>
					</div>
					<div class="event-row">
						<div class="esubrow-left">
							$dateStart &ndash; $datetimeEnd
						</div>
						<div class="esubrow-right">
							$eventType
						</div>
					</div>
					<div class="event-row">
						<div class="esubrow-full">
							There are $attendants attendants so far.
						</div>
					</div>
					<div class="event-row">
						<div class="esubrow-full">
							$categoryStr
						</div>
					</div>
					<div class="event-row">
						<div class="esubrow-full">
							$description
						</div>
					</div>
					<div class="event-row">
						<ul id="destinations-block">
							$destinations
						</ul>
					</div>
					<div class="event-row">
						<div id="comments-header">
							Comments: 
						</div>
						<ul id="comments-block">
							$comments
							<li class="comment-add">
								<div id="create-comment">
									<form>
										<textarea name="message" class="comment-add-textbox"></textarea>
										<input type="submit" value="Comment" />
									</form>
								</div>
								<br style="clear: both;" />
							</li>
						</ul>
					</div>
				</div>

EOEP;
		}
		else {
			echo "no eventID";
			die(0);
		}
	}
	
	
	
	// Otherwise it is some flavor of index 
	else {
		$headTags = <<<EOHT

				<link type="text/css" rel="stylesheet" href="/webapp/index.css" />
				<script type="text/javascript" src="/webapp/index.js"></script>

EOHT;

		$contentBodyWrapper = <<<EOBW

			<h2>&nbsp;Welcome, $usrname!</h2>
			
			<ul id="sticky-injection-point"></ul>
			<ul id="injection-point"></ul>
			<div id="event-template">
				<li class="event-block">
					<div class="tr">
						<div class="thumb-cell"></div>
						<div class="info-cell">
							<div class="title-cell"></div>
							<div class="username-cell"></div>
							<div class="dates-cell"></div>
							<div class="tags-cell"></div>
							<div class="description-cell"></div>
							<br style="clear: both;" />
						</div>
						<br style="clear: both;" />
					</div>
					<br style="clear: both;" />
				</li>
			</div>

EOBW;
	}
	
	
	
	
	// Load template html 
	$template = file_get_contents("template.html");
	if ( !$template ) {
		echo "can't get template";
		die(0);
	}
	
	$template_pop = str_replace("#headTags#", $headTags, $template);
	$template_pop = str_replace("#contentBodyWrapper#", $contentBodyWrapper, $template_pop);
	
	
	// Declare we're returning HTML in UTF-8
	header('Content-Type: text/html; charset=utf-8');
	
	echo $template_pop;
?>
