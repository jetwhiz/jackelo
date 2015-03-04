$(function() {
	$.ajax({
		url: "https://dev.m.gatech.edu/developer/cmunson3/api/jackelo/login/" + location.search.substr(1),
		dataType: "json",
		async: true,
		success: function(data, textStatus, jqXHR) {
			$( "#session" ).val(data.session);
			$( "#target" ).submit();
			//window.location.replace("https://jackelow.gjye.com/webapp/?session=" + encodeURIComponent(data.session));
		}
	});
});
