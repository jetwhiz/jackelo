"use strict";


// Determine if a given node is in view of the user // 
function isInView(elem) {
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();
	
    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();
	
    return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom));
}
// * // 



// For each event, populate the event box on the page // 
function updateFields( elem, results ) {
	$(elem).find('div.title-cell:first').text(results.name);
	$(elem).find('div.username-cell:first').text('(' + results.username + ')');
	$(elem).find('div.dates-cell:first').text(results.datetimeStart + " - " + results.datetimeEnd);
	$(elem).find('div.description-cell:first').text(results.description);
	
	// Populate thumbnail 
	var img = results.destinations[0].thumb;
	var country = results.destinations[0].countryName;
	$(elem).find('div.thumb-cell:first').html('<img src="' + img + '" alt="' + country + '" class="thumb" />');
	
	// Populate categories (tags) 
	var categoryStr = [];
	for ( var i = 0; i < results.categories.length; ++i ) {
		categoryStr.push('#"' + results.categories[i].name + '"'); 
	}
	$(elem).find('div.tags-cell:first').text(categoryStr.join(", "));
}
// * //



// Update event nodes when they get scrolled into view //
function updateChildren() {
	$('#injection-point').children('div').each(function () {
		
		// check if it is in view of the user 
		var inview = isInView($(this));
		if ( !inview ) return;
		
		// check if already loaded (only load once) 
		if ( $(this).data('loaded') ) return;
		
		// otherwise load content 
		$.get( "/api/event/"+ $(this).attr('jk:eventID'), 
		(function(elem) {
			return function( event ) {
				updateFields( $(elem), event.results[0] );
			}
		})($(this))
		, "json" );
		$(this).data('loaded', true);
	});
}
// * //



// Populate the page with events (starting at offset) //
function populate( offset ) {
	$(window).data('busy', true);
	
	// Load all events from API and populate page 
	$.get( "/api/event/", // "/api/event/start/" 
	function( event ) {
		for (var i = 0; i < event.results.length; ++i) {
			var $template = $( "#event-template" ).children().first().clone();
			$template.show();
			$template.data('loaded', false);
			$template.attr({"jk:eventID" : event.results[i]});
			$template.appendTo( "#injection-point" );
		}
		
		// Force load of visible children before scrolling happens 
		updateChildren();
		$(window).data('busy', false);
	}, "json" );
}
// * //



// When user hits bottom of content body, load next set of results in //
function infiniScroll() {
	if ($(window).data('busy') == true) return;
	
	var wrapperHeight = $("#wrapper").height() - 100;
	var scrollPos = $("#content-body").scrollTop() + $("#content-body").height();
	if (scrollPos > wrapperHeight) {
		populate( $(".event-block").length );
	}
}
// * //



// General handler attacher // 
function attachHandlers() {
	// Attach onscroll event to content body 
	$("#content-body").scroll( function(e) {
		if ( limitScroll ) return;
		
		limitScroll = 1;
		setTimeout(updateChildren, 250); 			// load events when scrolled into view 
		setTimeout(infiniScroll, 250); 				// infinite-scroll
		setTimeout(function(){limitScroll=0}, 750);	// flood control 
	});
	
	// Attach onresize event to window 
	$( window ).resize( function(e) {
		if ( limitResize ) return;
		
		limitResize = 1;
		setTimeout(updateChildren, 250); 			// load events when scrolled into view 
		setTimeout(infiniScroll, 250); 				// infinite-scroll
		setTimeout(function(){limitResize=0}, 750);	// flood control 
	});
}
// * //



// When page is loaded //
var limitScroll = 0;
var limitResize = 0;
$( window ).load(function() {
	$(window).data('busy', false);
	populate(0);
	attachHandlers();
});
// * //
