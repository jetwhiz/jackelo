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



// Gets the eventID from the current webapp path // 
function getEventID() {
	var pathFilter = window.location.pathname.match(/^\/webapp\/event\/([0-9]+)/); // /webapp/event/#
	if ( !pathFilter ) {
		return NaN;
	}
	
	return parseInt(pathFilter[1]);
}
// * //
