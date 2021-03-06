"use strict";


$(function() {
	
	// Events (children) that have been added to page (IDs)
	var children = { "event" : [], "info" : [], "sponsor" : [] };
	
	
	// Should be loaded in dynamically 
	var eventTypes = { "Local Event": 1, "GTL Event": 2, "Trip": 3, "Info": 4, "Sponsored": 5 };
	
	
	// For each event, populate the event box on the page // 
	function updateChildFields( elem, results ) {
		
		// Prep date range
		var months = new Array( "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" );
		var dS = results.datetimeStart.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2})/);
		var dSS = months[dS[2]-1] + " " + dS[3] + ", " + dS[1];
		var dE = results.datetimeEnd.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2})/);
		var dES = months[dE[2]-1] + " " + dE[3] + ", " + dE[1];
		$(elem).find('div.dates-cell:first').text(dSS + " - " + dES);
		
		// Prep description 
		var description = results.description;
		if ( description.length > 500 ) {
			description = description.substr(0, 500) + " &hellip;";
		}
		$(elem).find('div.description-cell:first').html(description);
		
		// Prep title 
		var title = results.name;
		$(elem).find('div.title-cell:first').html(title);
		
		// Username
		$(elem).find('div.username-cell:first').text('(' + results.networkAbbr + '-' + results.username + ')');
		
		// Check if sponsored event -- if so, add flair 
		if ( results.eventTypeID == eventTypes["Sponsored"] ) {
			$(elem).addClass("premium-block");
			$(elem).removeClass("event-block");
		}
		
		// Populate thumbnail (or use generic) 
		if ( results.destinations.length > 0 ) {
			var img = results.destinations[0].thumb;
			var country = results.destinations[0].countryName;
			$(elem).find('div.thumb-cell:first').html('<img src="' + img + '" alt="' + country + '" title="' + country + '" class="thumb" />');
		}
		else if ( results.eventTypeID == eventTypes["GTL Event"] ) {
			$(elem).find('div.thumb-cell:first').html('<img src="https://jackelow.gjye.com/imgs/GTL-event.jpg.thumb.jpg" alt="GTL Event" title="GTL Event" class="thumb" />');
		}
		else if ( results.eventTypeID == eventTypes["Local Event"] ) {
			$(elem).find('div.thumb-cell:first').html('<img src="https://jackelow.gjye.com/imgs/local-event.jpg.thumb.jpg" alt="Local Event" title="Local Event" class="thumb" />');
		}
		else {
			$(elem).find('div.thumb-cell:first').html('<img src="https://jackelow.gjye.com/imgs/default.png.thumb.jpg" alt="Generic city" title="Generic city" class="thumb" />');
		}
		
		
		// Populate categories (tags) 
		var categoryStr = [];
		var existingPath = getPathFilters("category");
		for ( var i = 0; i < results.categories.length; ++i ) {
			categoryStr.push('<a class="tags-link" href="/webapp/' + existingPath + 'category/' + results.categories[i].categoryID + '/">' + results.categories[i].name + '</a>'); 
		}
		$(elem).find('div.tags-cell:first').html(categoryStr.join(", "));
	}
	// * //



	// Update event nodes when they get scrolled into view //
	function updateChildren(infoType) {
		var container = "injection-point";
		
		// Which type of injection point should we use? 
		switch ( infoType ) {
			case "info":
				container = "sticky-injection-point";
				break;
			case "sponsor":
				container = "premium-injection-point";
				break;
			default:
				container = "injection-point";
		}
		
		
		$('#' + container).children('li').each(function () {
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
			var eventID = $(this).attr('jk:eventID');
			$.ajax({
				type: "GET",
				dataType: "json",
				url: "/api/event/" + eventID, 
				success: (function(elem) {
					return function( data, status, xhr ) {
						if ( !data ) {
							alert("ERROR: Cannot load eventID " + eventID );
							return false;
						}
						
						updateChildFields( $(elem), data.results[0] );
					}
				})($(this))
			}).fail(function( xhr, status, error ) {
				if ( window.console && console.log ) {
					console.log( "ERROR: Failed to send request!\r\n" + status );
				}
			});
			$(this).data('loaded', true);
		});
	}
	// * //
	
	
	
	// Determine any path filters that were requested 
	function getPathFilters(skip) {
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
					if ( skip != "country" && !isNaN(countryID) ) 
						pathFilters += "country/" + countryID + "/";
					break;
				
				case "category":
					var categoryID = pathTokens.shift();
					if ( skip != "category" && !isNaN(categoryID) ) 
						pathFilters += "category/" + categoryID + "/";
					break;
			}
		}
		
		return pathFilters;
	}
	// * //
	
	
	
	// Determine if an event (child) has already been injected into the page (return index) //
	function getChildIndexById(id, infoType) {
		var index = -1;
		var type = "event";
		
		// Which type of child should we use? 
		switch ( infoType ) {
			case "info":
				type = "info";
				break;
			case "sponsor":
				type = "sponsor";
				break;
			default:
				type = "event";
		}
		
		//console.log(array);
		index = $.inArray( id, children[type] );
		
		return index;
	}
	// * //
	
	
	
	// Get the child node at index "index" (for appending/removal) //
	function getChildNodeByIndex(index, infoType) {
		var node = null;
		var injectionPoint = "injection-point";

		// Which type of child should we use? 
		switch ( infoType ) {
			case "info":
				injectionPoint = "sticky-injection-point";
				break;
			case "sponsor":
				injectionPoint = "premium-injection-point";
				break;
			default:
				injectionPoint = "injection-point";
		}
		
		// Loop through all children until we hit the correct index 
		$( "#" + injectionPoint ).children().each( function(i) {
			if ( index == i ) {
				node = $(this);
				return false;
			}
		});
		
		return node;
	}
	// * //
	
	
	
	// Notify user when there are new events //
	function unblockNotifications() {
		if ( $("#notify-sound").length ) {
			var a = $("#notify-sound")[0];
			a.load();
			
			$(window).unbind('touchstart mousedown scroll', unblockNotifications);
		}
	}
	function clearNotifications() {
		document.title = "Jackelo - GTL Events Manager";
	}
	function notifyUser() {
		
		// Play notification sound 
		if ( $("#notify-sound").length ) {
			var a = $("#notify-sound")[0];
			a.currentTime = 0;
			a.load();
			a.play();
		}
		
		// Update title 
		if ( document.title.charAt(0) == '(' && document.title.indexOf(')') > -1 ) {
			var oldCount = document.title.substring(1, document.title.indexOf(')'));
			clearNotifications();
			//count += parseInt(oldCount);
		}
		document.title = "(*) " + document.title;
		
		// If browser does not support Notifications API, don't continue 
		if (!("Notification" in window)) {
			return;
		}
		
		// User has already granted permission to use notifications 
		else if (Notification.permission === "granted") {
			var notification = new Notification("Events have been updated");
		}
		
		// Ask the user for permission
		else if (Notification.permission !== 'denied') {
			Notification.requestPermission(function (permission) {
				if (permission === "granted") {
					var notification = new Notification("Events have been updated");
				}
			});
		}
	}
	// * //
	
	
	
	// Populate the page with events (starting at offset) //
	function populate(infoType, offset, limit) {
		$(window).data('busy', true);
		//console.log("Populate called: " + (infoType ? "info" : "event"));
		
		// If not specified, set limit to default (10) 
		limit = typeof limit !== 'undefined' ? limit : 10;
		if ( limit == 0 ) {
			return;
		}
		
		
		// Determine if filters were requested 
		var pathFilters = getPathFilters();		
		
		
		// Differentiate between info and event types 
		var injectionPoint = "injection-point";
		var childType = "event";
		var noResult = "event-noresult";
		var typeFilter = "";
		
		// Which type of child should we use? 
		switch ( infoType ) {
			case "info":
				injectionPoint = "sticky-injection-point";
				childType = "info";
				noResult = "sticky-noresult";
				typeFilter = "type/" + eventTypes["Info"] + "/";
				break;
			case "sponsor":
				injectionPoint = "premium-injection-point";
				childType = "sponsor";
				noResult = "sponsor-noresult";
				typeFilter = "type/" + eventTypes["Sponsored"] + "/";
				break;
			default:
				injectionPoint = "injection-point";
				childType = "event";
				noResult = "event-noresult";
				typeFilter = "";
		}
		
		
		// Get number of results
		var numResults = $( "#" + injectionPoint ).children().length;
		
		// If not specified, default offset to number of results (append mode) 
		offset = typeof offset !== 'undefined' ? offset : numResults;
		
		// If offset == number of results, this is append mode 
		var appendMode = ( offset == numResults );
		
		
		// Load all events from API and populate page 
		$.ajax({
			type: "GET",
			dataType: "json",
			url: "/api/event/" + typeFilter + pathFilters + "start/" + offset + "/limit/" + limit,
			success: function( data, status, xhr ) {
				
				// Ensure we're received a good response 
				if ( data.code != 200 ) {
					alert("ERROR: Bad response pulling events!");
					return;
				}
				
				// If no more results 
				if ( !data.results.length ) {
					//console.log("End of event results");
					$(window).data(noResult, true);
				}
				
				// Determine if there are new events to alert 
				var newEventsAlert = false;
				if ( !appendMode ) {
					var oldNew = $(children[childType]).not(data.results).get();
					var newOld = $(data.results).not(children[childType]).get();
					if ( oldNew.length || newOld.length ) {
						newEventsAlert = true;
					}
				}
				
				for (var i = 0; i < data.results.length; ++i) {
					var thisID = data.results[i];
					
					if ( appendMode ) {
						// Make sure it isn't already added 
						if ( getChildIndexById(thisID, infoType) != -1 ) {
							alert("ERROR: Attempted to double-insert an event into page!");
							continue;
						}
						
						var $template = $( "#event-template" ).children().first().clone();
						$template.show();
						$template.data('loaded', false);
						$template.attr({"jk:eventID" : thisID});
						
						// Change style to correct type (instead of event) 
						switch ( infoType ) {
							case "info":
								$template.addClass("info-block");
								$template.removeClass("event-block");
								break;
							case "sponsor":
								$template.addClass("premium-block");
								$template.removeClass("event-block");
								break;
							default:
						}
						
						$template.appendTo( "#" + injectionPoint );
						children[childType].push(thisID); // add to children list 
					}
					else {
						
						//console.log("Working on thisID: " + thisID);
						
						// If this is an already-existing child 
						if ( getChildIndexById(thisID, infoType) != -1 ) {
							
							// Prune existing list of non-existant children 
							while ( children[childType].length 
									&& thisID != children[childType][i] 
								) {
									
								// Get node to remove from page 
								var node = getChildNodeByIndex(i, infoType);
								if (!node) {
									alert("ERROR: Node does not exist!  Cannot remove it!");
									return false;
								}
								
								// Remove from page and array 
								//console.log("Removing node " + i + " from page. ID: " + $(node).attr('jk:eventID'));
								$(node).remove();
								//console.log("Array before: " + children[childType]);
								children[childType].splice(i, 1);
								//console.log("Array after: " + children[childType]);
							}
							
						}
						
						// Otherwise it's a new item! 
						else {
							//console.log("Inserting new node " + i + " into page with ID " + thisID);
							
							// Create new node 
							var $template = $( "#event-template" ).children().first().clone();
							$template.show();
							$template.data('loaded', false);
							$template.attr({"jk:eventID" : thisID});
							
							// Change style to correct type (instead of event) 
							switch ( infoType ) {
								case "info":
									$template.addClass("info-block");
									$template.removeClass("event-block");
									break;
								case "sponsor":
									$template.addClass("premium-block");
									$template.removeClass("event-block");
									break;
								default:
							}
							
							//console.log("Array before: " + children[childType]);
							
							
							// Append to end of page 
							if ( i >= children[childType].length ) {
								$template.appendTo( "#" + injectionPoint );
								children[childType].push(thisID); // add to children list 
							}
							
							// Insert inside page 
							else {
								// Get node at position i (we need to insert before it) 
								var node = getChildNodeByIndex(i, infoType);
								if (!node) {
									alert("ERROR: Node does not exist!  Cannot insert before it!");
									return false;
								}
								
								// Insert new node before existing node 
								node.before( $template );
								children[childType].splice(i, 0, thisID);
							}
							
							
							//console.log("Array after: " + children[childType]);
						}
						
					}
					
				}
				
				// Force load of visible children before scrolling happens 
				updateChildren(infoType);
				
				// Nofity user if there are new events 
				if ( newEventsAlert ) {
					notifyUser();
				}
				
				// Populate is no longer busy 
				$(window).data('busy', false);
			}
		}).fail(function( xhr, status, error ) {
			if ( window.console && console.log ) {
				console.log( "ERROR: Failed to send request!\r\n" + status );
			}
		});
		
	}
	// * //



	// When user hits bottom of content body, load next set of results in //
	function infiniScroll() {
		if ($(window).data('busy') == true) return;
		
		// Always try to load more sponsored events (unless we hit the end) 
		if (!$(window).data('sponsor-noresult')) {
			populate("sponsor");
		}
		
		// Always try to load more info events (unless we hit the end) 
		if (!$(window).data('sticky-noresult') && !$( "#load-infos" ).is(":visible")) {
			populate("info");
		}
		
		// Don't bother grabbing more events if we hit the end 
		if (!$(window).data('event-noresult')) {
			var wrapperHeight = $("#wrapper").height() - 100;
			var scrollPos = $("#content-body").scrollTop() + $("#content-body").height();
			if (scrollPos > wrapperHeight) {
				populate("event");
			}
		}
	}
	// * //
	
	
	
	// Attach event for when user clicks the "load infos" button //
	$( "#load-infos" ).on( "click", function() {
		populate("info");
		$(this).hide();
	});
	// * //
	
	
	
	// General maintenance functions (to be run periodically) //
	var maintenanceTimer = 0;
	var maintenanceInterval = 10000;
	function throttle() {
		//console.log("Throttling maintenance run");
		
		if ( maintenanceTimer ) {
			clearTimeout(maintenanceTimer);
		}
		
		if ( !document.hidden ) {
			maintenanceTimer = setInterval(maintenance, maintenanceInterval);
		}
	}
	function maintenance() {
		//console.log("Starting maintenance run");
		
		populate("sponsor", 0, $( "#sponsored-injection-point" ).children().length);
		populate("info", 0, $( "#sticky-injection-point" ).children().length);
		populate("event", 0, $( "#injection-point" ).children().length);
	}
	// * //
	
	
	
	// General handler attacher // 
	var limitScroll = 0;
	var limitResize = 0;
	function attachHandlers() {
		
		// Run mainenance every few seconds (for compatible browsers) 
		if ( typeof document.hidden !== "undefined" ) {
			throttle();
			$(document).on("visibilitychange", throttle);
		}
		
		// Attach onscroll event to content body 
		$("#content-body").scroll( function(e) {
			if ( limitScroll ) return;
			
			limitScroll = 1;
			setTimeout(updateChildren, 250); 			// load events when scrolled into view 
			setTimeout(infiniScroll, 250); 				// infinite-scroll
			setTimeout(clearNotifications, 250); 		// clear notifications 
			setTimeout(function(){limitScroll=0}, 750);	// flood control 
		});
		
		$(window).bind('touchstart mousedown scroll', unblockNotifications);
		
		// Attach onresize event to window 
		$( window ).resize( function(e) {
			// Flood control check 
			if ( limitResize ) return;
			
			limitResize = 1;
			setTimeout(updateChildren, 250); 			// load events when scrolled into view 
			setTimeout(infiniScroll, 250); 				// infinite-scroll
			setTimeout(clearNotifications, 250); 		// clear notifications 
			setTimeout(function(){limitResize=0}, 750);	// flood control 
		});
	}
	// * //
	
	
	
	// When page is loaded //
	$( window ).load(function() {
		$(window).data('busy', false);
		$(window).data('sticky-noresult', false);
		$(window).data('event-noresult', false);
		$(window).data('sponsor-noresult', false);
		populate("sponsor");
		populate("event");
		attachHandlers();
		clearNotifications();
	});
	// * //
	
});
