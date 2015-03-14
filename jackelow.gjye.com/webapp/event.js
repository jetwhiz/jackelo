"use strict";


$(function() {
	
	// Confirmation box (general purpose) // 
	var confirm = $( "#dialog-confirm" ).dialog({
		autoOpen: false,
		resizable: false,
		height: 175,
		modal: true,
		buttons: {
			"Delete": function() {
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	// * //
	
	
	
	// Attach event for when user clicks the "remove-" and "add-" buttons //
	$( "#remove-event" ).on( "click", removeEvent);
	$( ".remove-comment" ).on( "click", removeComment);
	// * //
	
	
	
	// Disable default functionality for comment form (we want to handle it manually) //
	$("#create-comment").find( "form" ).on( "submit", function( event ) {
		event.preventDefault();
		submitComment(event);
	});
	// * //
	
	
	
	// When user clicks to submit comment on event //
	function submitComment(obj) {
		
		// Ensure we have access to the form object 
		if ( !obj.target ) {
			alert("ERROR: Cannot find comment form!");
			return false;
		}
		
		// Get user's message 
		var message = obj.target["message"].value;
		if ( message == "" ) {
			alert("Please enter a message!");
		}
		
		// Get eventID
		var eventNum = getEventID();
		if ( isNaN(eventNum) ) {
			alert("Cannot get event number!");
			return false;
		}
		
		// POST to server 
		$.ajax({
			type: "POST",
			data: "message=" + encodeURIComponent(message), 
			dataType: "json",
			url: "/api/event/" + eventNum + "/comments/", 
			success: function( data, status, xhr ) {
				if ( typeof data.results["commentID"] == 'undefined' ) {
					alert("ERROR: Failed to create comment!\r\n" + data.message);
					return false;
				}
				
				// Refresh page & reset form 
				obj.target.reset();
				location.reload();
			}
		}).fail(function( xhr, status, error ) {
			alert( "ERROR: Failed to send request!\r\n" + status );
		});
		
		
		return true;
	}
	// * //



	// Remove comment functionality (with confirmation) //  
	function removeComment(that) {
		if ( ! that.target ) {
			alert("ERROR: Can't find comment to remove");
			return false;
		}
		
		
		// Pull commentID from anchor attribute 
		var commentID = $(that.target).attr("commentID");
		
		
		// Ensure we're given a good comment ID 
		if ( isNaN(parseInt(commentID)) ) {
			alert("Cannot get comment ID!");
			return false;
		}
		
		// Get eventID
		var eventNum = getEventID();
		if ( isNaN(eventNum) ) {
			alert("Cannot get event number!");
			return false;
		}
		
		// Customize confirmation for comment removal 
		$( "#dialog-confirm" ).dialog( "option", "title", "Delete comment?" );
		$( "#dialog-confirm" ).dialog( "option", "buttons", {
			"Delete": function() {
				
				// Delete comment 
				$.ajax({
					type: "DELETE",
					dataType: "json",
					url: "/api/event/" + eventNum + "/comments/" + commentID,
					success: function( data, status, xhr ) {
						if ( data.code != 200 ) {
							alert("ERROR: Failed to delete comment!\r\n" + data.message);
							return false;
						}
						
						// Refresh page
						location.reload();
					}
				}).fail(function( xhr, status, error ) {
					alert( "ERROR: Failed to send request!\r\n" + status );
				});
				
				$( "#dialog-confirm" ).dialog( "close" );
			},
			Cancel: function() {
				$( "#dialog-confirm" ).dialog( "close" );
			}
		});
		
		$( "#dialog-confirm" ).dialog( "open" );
	}
	// * //



	// Remove event functionality (with confirmation) //  
	function removeEvent() {
		
		// Get eventID
		var eventNum = getEventID();
		if ( isNaN(eventNum) ) {
			alert("Cannot get event number!");
			return false;
		}
		
		// Customize confirmation for comment removal 
		$( "#dialog-confirm" ).dialog( "option", "title", "Delete event?" );
		$( "#dialog-confirm" ).dialog( "option", "buttons", {
			"Delete": function() {
				
				// Delete event 
				$.ajax({
					type: "DELETE",
					dataType: "json",
					url: "/api/event/" + eventNum + "/",
					success: function( data, status, xhr ) {
						if ( data.code != 200 ) {
							alert("ERROR: Failed to delete event!\r\n" + data.message);
							return false;
						}
						
						// Back home 
						document.location = "/webapp/";
					}
				}).fail(function( xhr, status, error ) {
					alert( "ERROR: Failed to send request!\r\n" + status );
				});
				
				$( "#dialog-confirm" ).dialog( "close" );
			},
			Cancel: function() {
				$( "#dialog-confirm" ).dialog( "close" );
			}
		});
		
		$( "#dialog-confirm" ).dialog( "open" );
	}
	// * //
	
	
	
	// Attach event for when user clicks the "attend/unattend event" button //
	$( "#attend-event" ).on( "click", function(obj) {
		if ( ! obj.target ) {
			alert("ERROR: Can't find attend button");
			return false;
		}
		
		var eventNum = getEventID();
		var anchor = $(obj.target);
		if ( $(anchor).attr("type") == "Attend" ) {
			// POST to server 
			$.ajax({
				type: "POST",
				data: "", 
				dataType: "json",
				url: "/api/event/" + eventNum + "/attendants/", 
				success: function( data, status, xhr ) {
					if ( data.code > 300 ) {
						alert("ERROR: Failed to attend event!\r\n" + data.message);
						return false;
					}
					
					// Refresh page 
					location.reload();
				}
			}).fail(function( xhr, status, error ) {
				alert( "ERROR: Failed to send request!\r\n" + status );
			});
		}
		else if ( $(anchor).attr("type") == "Unattend" ) {
			// DELETE to server 
			$.ajax({
				type: "DELETE",
				dataType: "json",
				url: "/api/event/" + eventNum + "/attendants/",
				success: function( data, status, xhr ) {
					if ( data.code > 300 ) {
						alert("ERROR: Failed to unattend event!\r\n" + data.message);
						return false;
					}
					
					// Refresh page 
					location.reload();
				}
			}).fail(function( xhr, status, error ) {
				alert( "ERROR: Failed to send request!\r\n" + status );
			});
		}
		else {
			alert("ERROR: Can't determine what to do");
			return false;
		}
	});
	// * //
	
	
	
	// When page is loaded //
	var limitResize = 0;
	$( window ).load(function() {
		$(".lightbox-image").colorbox({maxWidth:"90%", maxHeight:"90%"});
	});
	// * //
	
});
