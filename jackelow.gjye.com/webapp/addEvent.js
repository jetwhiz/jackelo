
// When user clicks to add a new destination 
function addDestination() {
	// Clone template and add to DOM 
	var $template = $( "#destination-template" ).clone();
	$template.show();
	$template.removeAttr('id');
	$template.appendTo( "#destination-injection-point" );
	
	
	// Do datepicker for start/end dates for this destination 
	$template.find( ".datetimeStart" ).datepicker({
		defaultDate: "+1d",
		changeMonth: true,
		numberOfMonths: 1,
		minDate: $( "#datetimeStart" ).datepicker('getDate'),
		maxDate: $( "#datetimeEnd" ).datepicker('getDate'),
		dateFormat: "yy-mm-dd"
	});
	$template.find( ".datetimeEnd" ).datepicker({
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
				alert( "Create new city: " + ui.item.label + " in country " + $(country).val() );
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
}



// When user clicks to remove a destination 
function removeDestination(that) {
	$(that).parent().remove();
}



// When user clicks to remove a category 
function removeCategory(that) {
	$(that).remove();
}



// All stuff that doesn't need to be called globally 
$(function() {
	var dialog, form;
	
	// Helpers 
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
	
	
	
	// Function to handle form submission 
	function submitEvent() {
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
		queryString += "category=" + encodeURIComponent(categories.join(",")) + "&";
		
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
		
		
		
		if ( valid ) {
			alert(queryString);
			
			$.post( "/api/event/", 
				queryString, 
				function( data, status, xhr ) {
					if ( typeof data.results["eventID"] == 'undefined' ) {
						alert("ERROR: Failed to create event!");
						return false;
					}
					
					document.location = "/webapp/event/" + data.results["eventID"];
				}, "json");
			
			dialog.dialog( "close" );
		}
		
		return valid;
	}
	
	
	
	// Create dialog box to populate form in 
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
	
	
	
	// Disable default functionality for form (we want to handle it manually) 
	form = dialog.find( "form" ).on( "submit", function( event ) {
		event.preventDefault();
		submitEvent();
	});
	
	
	
	// Add datepicker to (main) start and end times for event 
	$( "#datetimeStart" ).datepicker({
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
	
	
	
	// Prettify the eventTypeID radios 
	$( "#eventTypeID" ).buttonset();
	
	
	
	// Attach event for when user clicks the "new event" button 
	$( "#add-event" ).on( "click", function() {
		// Determine appropriate dialog size 
		var wWidth = $(window).width();
		var wHeight = $(window).height();
		wWidth = (wWidth > 500 ? 500 : wWidth*0.75);
		wHeight = (wHeight > 500 ? 500 : wHeight*0.75);
		
		// redirect if size is too small 
		// document.location = "/webapp/newEvent/";
		
		dialog.dialog( "option", "height", wHeight );
		dialog.dialog( "option", "width", wWidth );
		dialog.dialog( "open" );
	});
	
	
	
	// Load all countries from API and populate page 
	$.get( "/api/country/",
	function( event ) {
		for (var i = 0; i < event.results.length; ++i) {
			$( ".countryID" ).append('<option value="' + event.results[i].id + '">' + event.results[i].name + '</option>');
		}
		
		//$( ".countryID" ).selectmenu();
	}, "json" );
	
	
	
	// Autocomplete for category (with cache) 
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
			// Not already existing -- we have to create it 
			if ( ui.item.value == -1 ) {
				alert( "Create new category: " + ui.item.label );
				$.post( "/api/category/", 
					"name=" + encodeURIComponent(ui.item.label), 
					function( data, status, xhr ) {
						if ( typeof data.results["categoryID"] == 'undefined' ) {
							alert("ERROR: Failed to create category!");
							return false;
						}
						//alert(data.results["categoryID"] + " " + ui.item.label);
						
						$( "#categories" ).append("<a title='Remove' onclick='removeCategory(this)' class='category' href='javascript: void(0);' catID='" + data.results["categoryID"] + "'>" + ui.item.label + "</a>");
						this.value = "";
						
						cache = {}; // invalidate cache 
					}, "json");
			}
			else {
				//alert("Existed: " + ui.item.value + " " + ui.item.label);
				
				$( "#categories" ).append("<a title='Remove' onclick='removeCategory(this)' class='category' href='javascript: void(0);' catID='" +  ui.item.value + "'>" + ui.item.label + "</a>");
				this.value = "";
			}
			
			return false;
		}
	});
	
});