<?
	/*
	// When user requests /webapp/event, the logic will be handled here
	//
	// @define headTags: the script/css headers that are needed to accompany this logic
	// @define contentBodyWrapper: the body output to provide to the user (may be HTML) 
	*/
	
	
	// Ensure eventID given 
	if ( !count($queryArray) || !is_numeric($queryArray[0]) ) {
		send( "", "ERROR: No event ID supplied", $GLOBALS["HTTP_STATUS"]["Bad Request"] );
	}
	
	
	// Get eventID from query string 
	$eventID = $queryArray[0];
	
	
	// Pull in main event data 
	$file = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/event/" . $eventID, false, $getContext);
	if ( !$file ) {
		send( "", "ERROR: Can't connect to API", $GLOBALS["HTTP_STATUS"]["Internal Error"] );
	}
	$JSON = json_decode($file, true);
	if ( count($JSON["results"]) != 1 ){
		send( "", "ERROR: Invalid event ID", $GLOBALS["HTTP_STATUS"]["Bad Request"] );
	}
	
	
	$eventName = $JSON["results"][0]["name"];
	$ownerName = $JSON["results"][0]["username"];
	$ownerNetworkAbbr = $JSON["results"][0]["networkAbbr"];
	$ownerID = $JSON["results"][0]["ownerID"];
	$dateStart = date("F d, Y", strtotime($JSON["results"][0]["datetimeStart"]));
	$datetimeEnd = date("F d, Y", strtotime($JSON["results"][0]["datetimeEnd"]));
	$eventType = $JSON["results"][0]["eventType"];
	
	// Convert newlines to breaks, allow clickable links    
	$description = Toolkit::clickify($JSON["results"][0]["description"]);
	$description = nl2br($description);
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
	$attendants_raw = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/event/" . $eventID . "/attendants/", false, $getContext);
	if ( !$attendants_raw ) {
		send( "", "ERROR: Can't connect to API", $GLOBALS["HTTP_STATUS"]["Internal Error"] );
	}
	$JSON_attendants = json_decode($attendants_raw, true);
	
	// Update # of attendants
	$attendants = count($JSON_attendants["results"]);
	
	// Figure out if the current user is attending already 
	foreach ( $JSON_attendants["results"] as $attendant ) {
		if ( $attendant["id"] == $User->getID() ) {
			$attending = "Unattend";
		}
	}
	////
	
	
	// Pull in event comments 
	$comments = "";
	$comments_raw = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/event/" . $eventID . "/comments/", false, $getContext);
	if ( !$comments_raw ) {
		send( "", "ERROR: Can't connect to API", $GLOBALS["HTTP_STATUS"]["Internal Error"] );
	}
	$JSON_comments = json_decode($comments_raw, true);
	
	foreach ( $JSON_comments["results"] as $commentID ) {
		$comment_raw = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/event/" . $eventID . "/comments/" . $commentID, false, $getContext );
		if ( !$comment_raw ) {
			send( "", "ERROR: Can't connect to API", $GLOBALS["HTTP_STATUS"]["Internal Error"] );
		}
		$JSON_comment = json_decode($comment_raw, true);
		
		if ( count($JSON_comment["results"]) == 1 ) {
			$commentUsername = $JSON_comment["results"][0]["username"];
			$commentNetworkAbbr = $JSON_comment["results"][0]["networkAbbr"];
			
			// Convert newlines to breaks, allow clickable links 
			$message = Toolkit::clickify($JSON_comment["results"][0]["message"]);
			$message = nl2br($message);
			
			// Convert comment timestamp to Paris timezone 
			$tz = new DateTimeZone('Europe/Paris');
			$dateServer = new DateTime($JSON_comment["results"][0]["datetime"]);
			$dateServer->setTimeZone($tz);					
			$datetime = $dateServer->format("F d, Y, H:i:s T");
			
			$commentDeleteLink = "";
			if ( $JSON_comment["results"][0]["ownerID"] == $User->getID() ) {
				$commentDeleteLink = " &mdash; <a href='javascript: void(0)' class='remove-comment' commentID='$commentID'>Delete</a>";
			}
			$comments .= <<<EOC
				<li class="comment">
					<div class="comment-header">
						<span class="bold">$commentNetworkAbbr-$commentUsername</span> ($datetime)
						$commentDeleteLink
					</div>
					<div class="comment-body">
						$message
					</div>
					<br style="clear: both;" />
				</li>

EOC;
		}
	
	}
	////
	
	
	
	// Show attending / edit / delete buttons dynamically 
	$controlLinks = "<a id='attend-event' type='$attending' href='javascript: void(0);'>$attending</a>";
	
	if ( $eventType == "Info" || $ownerID == $User->getID() ) {
		$controlLinks .= " - <a id='edit-event' href='javascript: void(0);'>Edit</a>";
	}
	if ( $ownerID == $User->getID() ) {
		$controlLinks .= " - <a href='javascript: void(0)' id='remove-event'>Delete</a>";
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
			<div class="event-head event-row">
				<div class="esubrow-left">
					<h2>$eventName</h2>
				</div>
				<div class="esubrow-right">
					<span class='bold'>Owner: </span> $ownerNetworkAbbr-$ownerName<br />
					<span class='bold'>Type: </span> $eventType<br />
					$controlLinks
				</div>
				<br style="clear: both;" />
			</div>
			<div class="event-row">
				<div class="esubrow-full">
					$dateStart &ndash; $datetimeEnd
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
				<div class="description esubrow-full">
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
	
	
	// Send the page to the user 
	send( $headTags, $contentBodyWrapper, $GLOBALS["HTTP_STATUS"]["OK"] );
?>