"use strict";


$(function() {

	// When user clicks to add a new destination // 
	function addDestination() {
		// Clone template and add to DOM 
		var $template = $( "#destination-template" ).clone();
		$template.show();
		$template.removeAttr('id');
		$template.appendTo( "#destination-injection-point" );
		$template.find( ".remove-destination" ).on( "click", removeDestination);
		
		
		// Do datepicker for start/end dates for this destination 
		$template.find( ".datetimeStart" ).datepicker({
			showOn: "button",
			buttonImage: "/webapp/jquery/images/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Select date",
			defaultDate: "+1d",
			changeMonth: true,
			numberOfMonths: 1,
			minDate: $( "#datetimeStart" ).datepicker('getDate'),
			maxDate: $( "#datetimeEnd" ).datepicker('getDate'),
			dateFormat: "yy-mm-dd"
		});
		$template.find( ".datetimeEnd" ).datepicker({
			showOn: "button",
			buttonImage: "/webapp/jquery/images/calendar.gif",
			buttonImageOnly: true,
			buttonText: "Select date",
			defaultDate: "+1d",
			changeMonth: true,
			numberOfMonths: 1,
			minDate: $( "#datetimeStart" ).datepicker('getDate'),
			maxDate: $( "#datetimeEnd" ).datepicker('getDate'),
			dateFormat: "yy-mm-dd"
		});
		
		
		// Autocomplete for city (with cache) 
		var cache = {};
		var country = $template.find( ".countryID" );
		var cityID = $template.find( ".cityID" );
		var cityName = $template.find( ".cityName" );
		
		$template.find( ".cityName" ).autocomplete({
			minLength: 2,
			source: function( request, response ) {
				var term = request.term;
				if ( term in cache ) {
					response( cache[ term ] );
					return;
				}
				
				// Make sure we have a countryID 
				if ( !$(country).val() ) {
					return false;
				}
				
				$.getJSON( "/api/country/" + $(country).val() + "/city/filter/" + term, function( data, status, xhr ) {
					var bifur = {};
					var i = 0;
					for ( ; i < data.results.length; ++i) {
						bifur[i] = {};
						bifur[i]["label"] = data.results[i].name;
						bifur[i]["value"] = data.results[i].id;
					}
					
					// Give option to create new city (if no exact match) 
					if ( i == 0 || bifur[0]["label"] != term ) {
						bifur[i] = {
							"label" : term,
							"value" : -1
						};
					}
					
					cache[ term ] = bifur;
					response( bifur );
				});
			}, 
			select: function( event, ui ) {
				// Make sure we have a countryID 
				if ( !$(country).val() ) {
					return false;
				}
				
				// Not already existing -- we have to create it 
				if ( ui.item.value == -1 ) {
					//alert( "Create new city: " + ui.item.label + " in country " + $(country).val() );
					$.post( "/api/country/" + $(country).val() + "/city/", 
						"name=" + encodeURIComponent(ui.item.label), 
						function( data, status, xhr ) {
							if ( typeof data.results["cityID"] == 'undefined' ) {
								alert("ERROR: Failed to create city!");
								$(cityID).val("");
								return false;
							}
							//alert(data.results["cityID"] + " " + ui.item.label);
							
							$(cityID).val(data.results["cityID"]);
							$(cityName).val(ui.item.label);
							cache = {}; // invalidate cache 
						}, "json");
				}
				else {
					//alert("Existed: " + ui.item.value + " " + ui.item.label);
					$(cityID).val(ui.item.value);
					$(cityName).val(ui.item.label);
				}
				
				return false;
			}
		});
		
		return $template;
	}
	// * //



	// When user clicks to remove a destination // 
	function removeDestination(that) {
		if ( ! that.target ) {
			alert("ERROR: Can't find remove button");
			return false;
		}
		
		$(that.target).parent().remove();
	}
	// * //



	// Is this category id already selected for new event?  // 
	function categorySelected(id) {
		var exists = false;
		$( "#categories" ).children().each( function(index) {
			if ( id == $(this).attr("catID") ) {
				exists = true;
			}
		});
		return exists;
	}
	// * //



	// Handler for adding new categories //
	function addCategory(that, value, label) {

		// Make sure category selection is not already in list 
		if ( value > 0 && categorySelected(value) ) {
			that.value = "";
			return false;
		}
		
		// Not already existing -- we have to create it 
		if ( value == -1 ) {
			//alert( "Create new category: " + label );
			$.post( "/api/category/", 
				"name=" + encodeURIComponent(label), 
				function( data, status, xhr ) {
					if ( typeof data.results["categoryID"] == 'undefined' ) {
						alert("ERROR: Failed to create category!");
						return false;
					}
					//alert(data.results["categoryID"] + " " + label);
					
					$( "#categories" ).append("<a title='Remove' class='category' href='javascript: void(0);' catID='" + data.results["categoryID"] + "'>" + label + "</a>");
					$( "#categories" ).find( ".category" ).on( "click", removeCategory);
					that.value = "";
					
					cache = {}; // invalidate cache 
				}, "json");
		}
		else {
			//alert("Existed: " + value + " " + label);
			
			$( "#categories" ).append("<a title='Remove' class='category' href='javascript: void(0);' catID='" +  value + "'>" + label + "</a>");
			$( "#categories" ).find( ".category" ).on( "click", removeCategory);
			that.value = "";
		}
		
	}
	// * //



	// When user clicks to remove a category // 
	function removeCategory(that) {
		if ( ! that.target ) {
			alert("ERROR: Can't find category to remove");
			return false;
		}
		
		$(that.target).remove();
	}
	// * //



	// Dynamically change the dialog dimensions according to display size // 
	function setDialogSize() {
		var wWidth = $(window).width();
		var wHeight = $(window).height();
		
		if ( (document.URL.indexOf("/createEvent") == -1) && wWidth > 750 && wHeight > 750 ) {
			wWidth = 600;
			wHeight = 600;
		}
		else {
			wWidth = wWidth*0.95;
			wHeight = wHeight*0.95;
		}
		
		$( "#dialog-form" ).dialog( "option", "height", wHeight );
		$( "#dialog-form" ).dialog( "option", "width", wWidth );
		
		
		// Re-focus form element (if one is selected) 
		var elem = document.activeElement;
		if ( elem.nodeName.toLowerCase() === 'input' && !isInView(elem) ) {
			var elemPos = $(elem).position().top;
			var containerPos = $( "#dialog-form" ).find('fieldset:first').offset().top;
			$( "#dialog-form" ).scrollTop(elemPos - containerPos);
		}
	}
	// * //



	// For edit event, auto-populate all fields //
	function populateFields() {
		var eventNum = getEventID();
		if ( isNaN(eventNum) ) {
			alert("Cannot get event number!");
			return false;
		}
		
		
		$.getJSON( "/api/event/" + eventNum, // "/api/event/#/" 
		function( events ) {
			// Should only have one event upon success 
			var event = events.results[0];
			
			
			// Strip time from datetime
			var dS = event.datetimeStart.match(/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})/);
			var dE = event.datetimeEnd.match(/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})/);
			
			
			// Set boundaries for calendar popups 
			$( "#datetimeStart" ).datepicker( "option", "maxDate", dE[1] );
			$( "#datetimeEnd" ).datepicker( "option", "minDate", dS[1] );
			
			
			// Populate main input fields 
			$( "#name" ).val($('<textarea />').html(event.name).text());
			$( "#datetimeStart" ).val(dS[1]);
			$( "#datetimeEnd" ).val(dE[1]);
			$( "#description" ).val($('<textarea />').html(event.description).text());
			
			
			// Populate categories
			for ( var i = 0; i < event.categories.length; ++i ) {
				addCategory( $( "#category" ), event.categories[i].categoryID, event.categories[i].name );
			}
			
			
			// Update event type ID 
			$("#eventType" + parseInt(event.eventTypeID)).prop("checked", true);
			$("#eventTypeID").buttonset("refresh");
			
			
			// Add destinations 
			for ( var i = 0; i < event.destinations.length; ++i ) {
				var ddS = event.destinations[i].datetimeStart.match(/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})/);
				var ddE = event.destinations[i].datetimeEnd.match(/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})/);
				
				var destination = addDestination();
				destination.find( ".address" ).val( $('<textarea />').html(event.destinations[i].address).text() );
				destination.find( ".datetimeStart" ).val( ddS[1] );
				destination.find( ".datetimeEnd" ).val( ddE[1] );
				destination.find( ".countryID" ).val( event.destinations[i].countryID );
				destination.find( ".cityID" ).val( event.destinations[i].cityID );
				destination.find( ".cityName" ).val( event.destinations[i].cityName );
			}
		});
		
		return true;
	}
	// * //
	
	
	
	// Helpers -- alerts user that they forgot something in the form // 
	var dialog, form;
	var name = $( "#name" ),
	datetimeStart = $( "#datetimeStart" ),
	datetimeEnd = $( "#datetimeEnd" ),
	description = $( "#description" ),
	allFields = $( [] ).add( name ).add( datetimeStart ).add( datetimeEnd ).add( description ),
	tips = $( ".validateTips" );
	function updateTips( t ) {
		tips
			.text( t )
			.addClass( "ui-state-highlight" );
		setTimeout(function() {
			tips.removeClass( "ui-state-highlight", 1500 );
		}, 500 );
	}
	function checkLength( o, n, min, max ) {
		if ( o.val().length > max || o.val().length < min ) {
			o.addClass( "ui-state-error" );
			//updateTips( "Length of " + n + " must be between " + min + " and " + max + "." );
			updateTips( "Field '" + n + "' is required!" );
			return false;
		} else {
			return true;
		}
	}
	////
	
	
	
	// Function to handle form submission //
	function editEvent() {
		submitEvent(null, true);
	}
	function submitEvent(obj, isEdit) {
		isEdit = typeof isEdit !== 'undefined' ? isEdit : false;
		
		var queryString = "";
		var valid = true;
		allFields.removeClass( "ui-state-error" );
		
		
		// Scroll to top so they can see warnings 
		$( "#dialog-form" ).scrollTop(0);
		
		
		
		// Bulk check 
		valid = valid && checkLength( name, "name", 3, 255 );
		valid = valid && checkLength( datetimeStart, "start date", 3, 255 );
		valid = valid && checkLength( datetimeEnd, "end date", 3, 255 );
		valid = valid && checkLength( description, "description", 3, 255 );
		
		// Don't bother continuing if the main stuff isn't good 
		if ( !valid ) {
			return false;
		}
		
		// Wrap up in query string to send 
		queryString += "name=" + encodeURIComponent( name.val() ) + "&";
		queryString += "datetimeStart=" + encodeURIComponent( datetimeStart.val() ) + "&";
		queryString += "datetimeEnd=" + encodeURIComponent( datetimeEnd.val() ) + "&";
		queryString += "description=" + encodeURIComponent( description.val() ) + "&";
		
		
		
		// Process categories 
		var categories = [];
		$( "#categories" ).children().each( function(index) {
			if ( $(this).attr("catID") ) {
				categories.push($(this).attr("catID"));
			}
		});
		if ( categories.length == 0 ) {
			updateTips( "At least one category is required!" );
		}
		queryString += "categoryID=" + encodeURIComponent(categories.join(",")) + "&";
		
		// Ensure good event type given 
		var eventTypeID = $('[name="eventTypeID"]:checked').val();
		if ( !eventTypeID ) {
			updateTips( "An event type is required!" );
		}
		queryString += "eventTypeID=" + encodeURIComponent(eventTypeID) + "&";
		
		// Don't bother continuing if the main stuff isn't good 
		valid = valid && categories.length;
		valid = valid && (eventTypeID);
		if ( !valid ) {
			return false;
		}
		
		
		
		// Process destinations 
		$( "#destination-injection-point" ).children().each( function(index) {
			var address = $(this).find('[name="destination[][address]"]').val();
			var datetimeStart = $(this).find('[name="destination[][datetimeStart]"]').val();
			var datetimeEnd = $(this).find('[name="destination[][datetimeEnd]"]').val();
			var countryID = $(this).find('[name="destination[][countryID]"]').val();
			var cityID = $(this).find('[name="destination[][cityID]"]').val();
			
			// Check required fields 
			valid = valid && (countryID) && (cityID);
			if ( !countryID || !cityID ) {
				updateTips( "For each destination, city and country are required!" );
				return false;
			}
			
			// Include in query string 
			queryString += encodeURIComponent("destination[" + index + "][address]") + "=" + encodeURIComponent(address) + "&";
			queryString += encodeURIComponent("destination[" + index + "][datetimeStart]") + "=" + encodeURIComponent(datetimeStart) + "&";
			queryString += encodeURIComponent("destination[" + index + "][datetimeEnd]") + "=" + encodeURIComponent(datetimeEnd) + "&";
			queryString += encodeURIComponent("destination[" + index + "][countryID]") + "=" + encodeURIComponent(countryID) + "&";
			queryString += encodeURIComponent("destination[" + index + "][cityID]") + "=" + encodeURIComponent(cityID) + "&";
		});
		
		
		// Don't continue if we haven't validated 
		if ( !valid ) {
			return false;
		}
		
		
		// If we're editing, we need to do a PUT request 
		if ( isEdit ) {
			var eventNum = getEventID();
			if ( isNaN(eventNum) ) {
				alert("Cannot get event number!");
				return false;
			}
			
			console.log(eventNum);
			//alert(queryString);
			
			$.ajax({
				type: "PUT",
				url: "/api/event/" + eventNum + "/", 
				data: queryString, 
				success: function( data, status, xhr ) {
					if ( typeof data.results["eventID"] == 'undefined' ) {
						console.log(data);
						alert("ERROR: Failed to edit event!\r\n" + data.message);
						return false;
					}
					
					// Go to newly-created event
					document.location = "/webapp/event/" + eventNum;
				}, 
				dataType: "json"
			});
		}
		
		// Otherwise POST the new event 
		else {
			$.post( "/api/event/", 
				queryString, 
				function( data, status, xhr ) {
					if ( typeof data.results["eventID"] == 'undefined' ) {
						console.log(data);
						alert("ERROR: Failed to create event!");
						return false;
					}
					
					// Go to newly-created event
					document.location = "/webapp/event/" + data.results["eventID"];
				}, "json");
		}
		
		//dialog.dialog( "close" );
		return valid;
	}
	// * //
	
	
	
	// Create dialog box to populate form in //
	dialog = $( "#dialog-form" ).dialog({
		autoOpen: false,
		height: 500,
		width: 500,
		modal: true,
		buttons: {
			"Create event": submitEvent,
			Cancel: function() {
				dialog.dialog( "close" );
			}
		},
		close: function() {
			form[ 0 ].reset();
			$( "#destination-injection-point" ).empty();
			$( "#categories" ).empty();
			allFields.removeClass( "ui-state-error" );
		}
	});
	// * //
	
	
	
	// Disable default functionality for form (we want to handle it manually) //
	form = dialog.find( "form" ).on( "submit", function( event ) {
		event.preventDefault();
		submitEvent();
	});
	// * //
	
	
	
	// Add datepicker to (main) start and end times for event //
	$( "#datetimeStart" ).datepicker({
		showOn: "button",
		buttonImage: "/webapp/jquery/images/calendar.gif",
		buttonImageOnly: true,
		buttonText: "Select date",
		defaultDate: "+1d",
		changeMonth: true,
		numberOfMonths: 1,
		minDate: "+0d",
		dateFormat: "yy-mm-dd",
		onClose: function( selectedDate ) {
			$( "#datetimeEnd" ).datepicker( "option", "minDate", selectedDate );
			$( ".datetimeStart" ).datepicker( "option", "minDate", selectedDate );
			$( ".datetimeEnd" ).datepicker( "option", "minDate", selectedDate );
		}
	});
	$( "#datetimeEnd" ).datepicker({
		showOn: "button",
		buttonImage: "/webapp/jquery/images/calendar.gif",
		buttonImageOnly: true,
		buttonText: "Select date",
		defaultDate: "+1d",
		changeMonth: true,
		numberOfMonths: 1,
		minDate: "+0d",
		dateFormat: "yy-mm-dd",
		onClose: function( selectedDate ) {
			$( "#datetimeStart" ).datepicker( "option", "maxDate", selectedDate );
			$( ".datetimeStart" ).datepicker( "option", "maxDate", selectedDate );
			$( ".datetimeEnd" ).datepicker( "option", "maxDate", selectedDate );
		}
	});
	// * //
	
	
	
	// Prettify the eventTypeID radios //
	$( "#eventTypeID" ).buttonset();
	// * //
	
	
	
	// Attach event for when user clicks the "new event" button //
	$( "#add-event" ).on( "click", function() {
		// Determine appropriate dialog size 
		setDialogSize();
		
		dialog.dialog( "option", "title", "Create event" );
		dialog.dialog( "option", "buttons", {
			"Create event": submitEvent,
			Cancel: function() {
				dialog.dialog( "close" );
			}
		});
		dialog.dialog( "open" );
	});
	if ( document.URL.indexOf("/createEvent") > -1 ) {
		$( "#add-event" ).trigger( "click" );
	}
	// * //
	
	
	
	// Attach event for when user clicks the "edit event" button //
	$( "#edit-event" ).on( "click", function() {
		// Determine appropriate dialog size 
		setDialogSize();
		populateFields();
		
		dialog.dialog( "option", "title", "Edit event" );
		dialog.dialog( "option", "buttons", {
			"Edit event": editEvent,
			Cancel: function() {
				dialog.dialog( "close" );
			}
		});
		dialog.dialog( "open" );
	});
	// * //
	
	
	
	// Attach event for when user clicks the "remove-" and "add-" buttons //
	$( "#add-destination" ).on( "click", addDestination);
	$( ".remove-destination" ).on( "click", removeDestination);
	$( ".category" ).on( "click", removeCategory);
	// * //
	
	
	
	// Load all countries from API and populate page //
	$.getJSON( "/api/country/",
	function( event ) {
		for (var i = 0; i < event.results.length; ++i) {
			$( ".countryID" ).append('<option value="' + event.results[i].id + '">' + event.results[i].name + '</option>');
		}
		
		//$( ".countryID" ).selectmenu();
	});
	// * //
	
	
	
	// Autocomplete for category (with cache) // 
	var cache = {};
	$( "#category" ).autocomplete({
		minLength: 2,
		source: function( request, response ) {
			var term = request.term;
			if ( term in cache ) {
				response( cache[ term ] );
				return;
			}
			
			$.getJSON( "/api/category/filter/" + term, function( data, status, xhr ) {
				var bifur = {};
				var i = 0;
				for ( ; i < data.results.length; ++i) {
					bifur[i] = {};
					bifur[i]["label"] = data.results[i].name;
					bifur[i]["value"] = data.results[i].id;
				}
				
				// Give option to create new city (if no exact match) 
				if ( i == 0 || bifur[0]["label"] != term ) {
					bifur[i] = {
						"label" : term,
						"value" : -1
					};
				}
				
				cache[ term ] = bifur;
				response( bifur );
			});
		}, 
		select: function( event, ui ) {
			
			addCategory( this, ui.item.value, ui.item.label );
			
			return false;
		}
	});
	// * //
	
	

	// General handler attacher // 
	function attachHandlers() {
		// Attach onresize event to window 
		$( window ).resize( function(e) {
			// Flood control check 
			if ( limitResize ) return;
			
			limitResize = 1;
			setTimeout(setDialogSize, 250); 		// resize addevent dialog on window resize
			setTimeout(function(){limitResize=0}, 750);	// flood control 
		});
	}
	// * //
	
	
	
	// When page is loaded //
	var limitResize = 0;
	$( window ).load(function() {
		attachHandlers();
	});
	// * //
	
});
