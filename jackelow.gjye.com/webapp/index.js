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
	var months = new Array( "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" );
	var dS = results.datetimeStart.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2})/);
	var dSS = months[dS[2]-1] + " " + dS[3] + ", " + dS[1];
	var dE = results.datetimeEnd.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2})/);
	var dES = months[dE[2]-1] + " " + dE[3] + ", " + dE[1];
	
	$(elem).find('div.title-cell:first').text(results.name);
	$(elem).find('div.username-cell:first').text('(' + results.username + ')');
	$(elem).find('div.dates-cell:first').text(dSS + " - " + dES);
	$(elem).find('div.description-cell:first').text(results.description);
	
	// Populate thumbnail (or use generic) 
	if ( results.destinations.length > 0 ) {
		var img = results.destinations[0].thumb;
		var country = results.destinations[0].countryName;
		$(elem).find('div.thumb-cell:first').html('<img src="' + img + '" alt="' + country + '" title="' + country + '" class="thumb" />');
	}
	else {
		$(elem).find('div.thumb-cell:first').html('<img src="https://jackelow.gjye.com/imgs/default.png.thumb.jpg" alt="Generic city" title="Generic city" class="thumb" />');
	}
	
	
	// Populate categories (tags) 
	var categoryStr = [];
	for ( var i = 0; i < results.categories.length; ++i ) {
		categoryStr.push('<a class="tags-link" href="/webapp/category/' + results.categories[i].categoryID + '/">' + results.categories[i].name + '</a>'); 
	}
	$(elem).find('div.tags-cell:first').html(categoryStr.join(", "));
}
// * //



// Update event nodes when they get scrolled into view //
function updateChildren() {
	$('#injection-point').children('li').each(function () {
		// check if it is in view of the user 
		var inview = isInView($(this));
		if ( !inview ) return;
		
		// check if already loaded (only load once) 
		if ( $(this).data('loaded') ) return;
		
		// Go to event page on click 
		$(this).on("click", function() {
			document.location = "/webapp/event/" + $(this).attr('jk:eventID');
		});
		
		// otherwise load content 
		$.getJSON( "/api/event/"+ $(this).attr('jk:eventID'), 
		(function(elem) {
			return function( event ) {
				updateFields( $(elem), event.results[0] );
			}
		})($(this))
		);
		$(this).data('loaded', true);
	});
}
// * //



// Populate the page with events (starting at offset) //
function populate( offset ) {
	$(window).data('busy', true);
	
	
	// Determine if filters were requested 
	var pathFilters = "";
	var pathTokens = window.location.pathname.split("/");
	pathTokens = $.grep( pathTokens, function(n) {
		return (typeof n !== 'undefined' && n != "");
	});
	
	if ( pathTokens[0] && pathTokens[0] == "webapp" ) {
		pathTokens.shift();
	}
	
	while ( pathTokens.length ) {
		var token = pathTokens.shift();
		
		switch ( token ) {
			case "country":
				var countryID = pathTokens.shift();
				if ( !isNaN(countryID) ) 
					pathFilters += "country/" + countryID + "/";
				break;
			
			case "category":
				var categoryID = pathTokens.shift();
				if ( !isNaN(categoryID) ) 
					pathFilters += "category/" + categoryID + "/";
				break;
		}
	}
	////
	
	
	// Get offset of results
	var offset = $( "#injection-point" ).children().length;
	////
	
	
	// Load all events from API and populate page 
	$.getJSON( "/api/event/" + pathFilters, // "/api/event/start/#/" 
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
	});
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
		// Flood control check 
		if ( limitResize ) return;
		
		limitResize = 1;
		if ( typeof(setDialogSize) == "function" ) {
			setTimeout(setDialogSize, 250); 		// resize addevent dialog on window resize
		}
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
