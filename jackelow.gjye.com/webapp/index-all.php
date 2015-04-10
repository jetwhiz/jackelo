<?
	/*
	// When user requests /webapp/{filters}, the logic will be handled here
	//
	// @define headTags: the script/css headers that are needed to accompany this logic
	// @define contentBodyWrapper: the body output to provide to the user (may be HTML) 
	*/
	
	
	// Get all filters chosen to display to user 
	$filtersObj = [];
	while ( count($queryArray) >= 2 ) {
		$command = array_shift($queryArray);
		$value = array_shift($queryArray);
		
		if ( $command == "country" && is_numeric($value) ) {
			$value = intval($value, 10);
			
			// Convert countryID to title 
			$country_raw = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/country/" . $value, false, $getContext);
			if ( !$country_raw ) {
				send( "", "ERROR: Can't connect to API", $GLOBALS["HTTP_STATUS"]["Internal Error"] );
			}
			$JSON = json_decode($country_raw, true);
			
			if ( count($JSON["results"]) == 1 ) {
				$filtersObj["country"] = [];
				$filtersObj["country"]["id"] = $value;
				$filtersObj["country"]["name"] = $JSON["results"][0]["name"];
			}
		}
		elseif ( $command == "category" && is_numeric($value) ) {
			$value = intval($value, 10);
			
			// Convert categoryID to title 
			$category_raw = file_get_contents("https://" . $_SERVER['HTTP_HOST'] . "/api/category/" . $value, false, $getContext);
			if ( !$category_raw ) {
				send( "", "ERROR: Can't connect to API", $GLOBALS["HTTP_STATUS"]["Internal Error"] );
			}
			$JSON = json_decode($category_raw, true);
			
			if ( count($JSON["results"]) == 1 ) {
				$filtersObj["category"] = [];
				$filtersObj["category"]["id"] = $value;
				$filtersObj["category"]["name"] = $JSON["results"][0]["name"];
			}
		}
	}
	
	// Make chosen filters pretty to display to user 
	$filters = [];
	$filtersStr = "<span class='bold'>Chosen filters: </span>";
	if ( count($filtersObj) ) {
		
		// If country filter was chosen 
		if ( count($filtersObj["country"]) ) {
			
			// Keep any specified category filters
			$filterKeepers = "";
			if ( count($filtersObj["category"]) ) {
				$filterKeepers = "category/" . $filtersObj["category"]["id"] . "/";
			}
			
			$filters[] = "<a href='/webapp/" . $filterKeepers . "' class='filter'>Country: " . $filtersObj["country"]["name"] . "</a>";
		}
		
		// If category filter was chosen 
		if ( count($filtersObj["category"]) ) {
			
			// Keep any specified country filters
			$filterKeepers = "";
			if ( count($filtersObj["country"]) ) {
				$filterKeepers = "country/" . $filtersObj["country"]["id"] . "/";
			}
			
			$filters[] = "<a href='/webapp/" . $filterKeepers . "' class='filter'>Category: " . $filtersObj["category"]["name"] . "</a>";
		}
		
		$filtersStr .= implode( ", ", $filters );
	}
	else {
		$filtersStr .= "<span class='italic'>None</span>";
	}
	
	
	$headTags = <<<EOHT

			<link type="text/css" rel="stylesheet" href="/webapp/index.css" />
			<script type="text/javascript" src="/webapp/index.js"></script>

EOHT;

	$contentBodyWrapper = <<<EOBW

		<h2>&nbsp;Welcome, $usrname!</h2>
		<div id="filters">$filtersStr</div>
		
		<ul id="sticky-injection-point"></ul>
		<a href="javascript: void(0);" id="load-infos" title="Load informational events">Load informational events</a>
		
		<ul id="premium-injection-point"></ul>
		
		<ul id="injection-point"></ul>
		<div class="endOfResults">End of results</div>
		
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
	
	
	// Send the page to the user 
	send( $headTags, $contentBodyWrapper, $GLOBALS["HTTP_STATUS"]["OK"] );
?>