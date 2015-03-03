<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Jackelo - GTL Events Manager</title>
		
		<meta charset="utf-8" />
		
		<style type="text/css">
			html { height: 100% }
			body { height: 100%; margin: 0; padding: 0 }
			#header { text-align: center; min-height: 50px; height: 5% }
			#map-canvas { height: 95% }
		</style>
		
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD_a-dYE-RmPRjgAn1dVJfSJ9IAVvE-7rQ"></script>
		<script type="text/javascript" src="jquery.js"></script>
		<script type="text/javascript">
			
			
			// Initialize the Google Map 
			function initialize() {
				var mapOptions = {
					center: { lat: 50, lng: 15}, 	// Europe 
					zoom: 5							// Make most of continent visible 
				};
				
				var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
				
				return map;
			}
			
			
			// Function to create clickable circles on map (for each event) 
			function placeEvent( map, country, count ) {
				var populationOptions = {
					strokeColor: '#FF0000',
					strokeOpacity: 0.8,
					strokeWeight: 2,
					fillColor: '#FF0000',
					fillOpacity: 0.35,
					map: map,
					cityName: country.name + " (" + count + ")",
					center: new google.maps.LatLng(country.latitude, country.longitude),
					radius: (Math.log(count)+1) * 50000
				};
				
				// Add the circle for this country to the map.
				var cityCircle = new google.maps.Circle(populationOptions);
				
				(function(marker) {
					google.maps.event.addListener(marker, 'click', function() {
						alert(marker["cityName"]);
					});
				})(cityCircle);
			}
			
			
			// Function factory -- returns functions to fix closure 
			function funcFactory( map, count ) {
				return function( country ) {
					placeEvent(map, country.results[0], count);
				}
			}
			
			
			// Populate the given map with events 
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
			
			google.maps.event.addDomListener(window, 'load', populate);
		</script>
	</head>
	<body>
		<div id="header"><h1>Jackelo</h1></div>
		<div id="map-canvas"></div>
	</body>
</html>
