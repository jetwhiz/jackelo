"use strict";


$(function() {
	
	// When page is loaded //
	$( window ).load(function() {
		$("iframe").each(function() {
			$(this)
				.data('aspectRatio', $(this).width() / $(this).height())
				.removeAttr('height')
				.removeAttr('width');
		});
		
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
