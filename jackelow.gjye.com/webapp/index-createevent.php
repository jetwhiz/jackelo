<?
	/*
	// When user requests /webapp/createEvent, the logic will be handled here
	//
	// @define headTags: the script/css headers that are needed to accompany this logic
	// @define contentBodyWrapper: the body output to provide to the user (may be HTML) 
	*/
	
	
	$headTags = "";
	$contentBodyWrapper = "";
	
	
	// Send the page to the user 
	send( $headTags, $contentBodyWrapper, $GLOBALS["HTTP_STATUS"]["OK"] );
?>