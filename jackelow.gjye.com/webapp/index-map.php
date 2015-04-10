<?
	/*
	// When user requests /webapp/map, the logic will be handled here
	//
	// @define headTags: the script/css headers that are needed to accompany this logic
	// @define contentBodyWrapper: the body output to provide to the user (may be HTML) 
	*/
	
	
	$headTags = <<<EOHT

			<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD_a-dYE-RmPRjgAn1dVJfSJ9IAVvE-7rQ"></script>
			<script type="text/javascript" src="/webapp/map.js"></script>

EOHT;
	
	$contentBodyWrapper = "";
	
	
	// Send the page to the user 
	send( $headTags, $contentBodyWrapper, $GLOBALS["HTTP_STATUS"]["OK"] );
?>