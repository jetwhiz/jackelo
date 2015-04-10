"use strict";


$(function() {
	
	// When page is loaded //
	$( window ).load(function() {
		
		
		// Handle header links onclick 
		$("#goto-page2").click(function() {
			$('html, body').animate({
				scrollTop: $("#page2").offset().top
			}, 2000);
		});
		$("#goto-page3").click(function() {
			$('html, body').animate({
				scrollTop: $("#page3").offset().top
			}, 2000);
		});
		$("#goto-page4").click(function() {
			$('html, body').animate({
				scrollTop: $("#page4").offset().top
			}, 2000);
		});
		
		
		// Save aspect ratio for iframes (youtube videos) 
		$("iframe").each(function() {
			$(this)
				.data('aspectRatio', $(this).width() / $(this).height())
				.removeAttr('height')
				.removeAttr('width');
		});
		
		
		// Unobfuscate e-mail addresses 
		$(".developer-contact").each(function() {
			var email = $(this).text().replace("+", "@");
			$(this).attr( "href", "mailto:" + email );
			$(this).text( email );
		});
		
		
		// Trigger resize() to force resizing of iframes (youtube videos) 
		$( window ).resize();
	});
	// * //
	
	
	// When window is resized //
	$( window ).resize(function() {
		var height = $(window).height() * 0.70;
		var width = $(window).width() * 0.70;
		
		$("iframe").each(function() {
			var $el = $(this);
			
			if ( height > width ) {
				height = width / $el.data('aspectRatio');
			}
			else {
				width = height * $el.data('aspectRatio');
			}
			
			$el
				.height(height)
				.width(width);
		});
	});
	// * //
	
});
