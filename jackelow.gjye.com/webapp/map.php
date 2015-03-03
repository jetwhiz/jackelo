<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<style type="text/css">
			html { height: 100% }
			body { height: 100%; margin: 0; padding: 0 }
			#header {height: 10% }
			#map-canvas { height: 90% }
		</style>
		<script type="text/javascript"
		  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD_a-dYE-RmPRjgAn1dVJfSJ9IAVvE-7rQ">
		</script>
		<script type="text/javascript" src="jquery.js"></script>
		<script type="text/javascript">
			
			function initialize() {
				var mapOptions = {
					center: { lat: 50, lng: 15},
					zoom: 5
				};
				var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
				
				return map;
			}
			
			
			
			function funcFactory( map, count ) {
				return function( country ) {
					placeEvent(map, country.results[0], count);
				}
			}
			function populate(map) {
				map = initialize();
				
				$.get( "/api/event/group/", function( groups ) {
					for ( var result in groups.results ) {
						$.get( "/api/country/" + groups.results[result].countryID + "/", 
						funcFactory( map, groups.results[result].count ),
						"json" );
						
					}
				}, "json" );
			}
			
			
			
			function placeEvent( map, city, count ) {
				
				var populationOptions = {
					strokeColor: '#FF0000',
					strokeOpacity: 0.8,
					strokeWeight: 2,
					fillColor: '#FF0000',
					fillOpacity: 0.35,
					map: map,
					cityName: city.name + " (" + count + ")",
					center: new google.maps.LatLng(city.latitude, city.longitude),
					radius: count * 100000
				};
				
				// Add the circle for this city to the map.
				var cityCircle = new google.maps.Circle(populationOptions);
				
				(function(marker) {
					google.maps.event.addListener(marker, 'click', function() {
						alert(marker["cityName"]);
					});
				})(cityCircle);
				
			}
			
			//google.maps.event.addDomListener(window, 'load', initialize);
			google.maps.event.addDomListener(window, 'load', populate);
		</script>
	</head>
	<body>
		<div id="header"></div>
		<div id="map-canvas"></div>
	</body>
</html>
