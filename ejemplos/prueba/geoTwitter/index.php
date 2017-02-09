<meta charset="utf-8">
<?php

require_once 'TwitterClass.php';

$twitter = new Twitter();

if(isset($_GET["city"])){
	$coor = $twitter->getCoordinates($_GET["city"]);
}else{
	$coor = $twitter->getCoordinates("Madrid");
}
$contenedorJSON = $twitter->getJsonGeoTweets($coor["latitud"],$coor["longitud"],"1km",200);
$data = $twitter->getInfoTwitter($contenedorJSON);

?>

<html>
<head>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
	<meta charset="utf-8">
	<title>Geotwitter</title>
	<style>
		html, body, #map-canvas {
			height: 100%;
			margin: 0px;
			padding: 0px
		}
		form,#banner{
			padding: 2px;
			cursor: pointer;
			display: block;
			width: 290px;
			margin: 0 auto;
			text-align: left;
		}
		img,h4{
			padding: 2px;
			cursor: pointer;
			display: block;
			margin: 0 auto;
			text-align: center;
		}
	</style>
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
	<script>
		// The following example creates complex markers to indicate beaches near
		// Sydney, NSW, Australia. Note that the anchor is set to
		// (0,32) to correspond to the base of the flagpole.
		function initialize() {
			var mapOptions = {
				zoom: 16,
				center: new google.maps.LatLng(<?php echo $coor["latitud"] ?>,<?php echo $coor["longitud"] ?>)
			}
			var map = new google.maps.Map(document.getElementById('map-canvas'),
				mapOptions);

			setMarkers(map, beaches);

		}

		/**
		 * Data for the markers consisting of a name, a LatLng and a zIndex for
		 * the order in which these markers should display on top of each
		 * other.
		 */
		var beaches = [
			<?php
			for($i=0;$i<count($data);$i++){
				//if($i==35) continue;
				$latitud = str_replace(",", ".", $data[$i]["latitud"]);
				$longitud = str_replace(",", ".", $data[$i]["longitud"]);
				$nombre = str_replace("'", "", $data[$i]["nombre"]);
				if($latitud == 0 && $longitud == 0){
					continue;
				}else{
					echo "['$nombre', $latitud, $longitud, $i],\n";
				}
			}
			?>
		];

		function setMarkers(map, locations) {
			// Add markers to the map

			// Marker sizes are expressed as a Size of X,Y
			// where the origin of the image (0,0) is located
			// in the top left of the image.

			// Origins, anchor positions and coordinates of the marker
			// increase in the X direction to the right and in
			// the Y direction down.
			var image = new Array();
			var tweet = new Array();
			var user = new Array();
			<?php
			$tweet = "";
			for($i=0;$i<count($data);$i++){
				echo "var image$i = {\n";
				//if($i==35) continue;
				$imagen = str_replace("'", "", $data[$i]["imagen_url"]);
				echo "url: '$imagen',\n";
				// This marker is 20 pixels wide by 32 pixels tall.
				echo "size: new google.maps.Size(100, 100),\n";
				// The origin for this image is 0,0.
				echo "origin: new google.maps.Point(0,0),\n";
				// The anchor for this image is the base of the flagpole at 0,32.
				echo "anchor: new google.maps.Point(25,0)\n";
				echo "};\n";
				echo "image.push(image$i);";

				$user = $data[$i]["nombre"];
				$user = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a target=\"_blank\" href=\"http://www.twitter.com/$1\">@$1</a>", $user);
				echo "user.push('".$user."');";

				$tweet = $data[$i]["tweet"];
				$tweet = str_replace("'", "", $tweet);
				$tweet = str_replace("\n", "", $tweet);
				//$tweet = preg_replace('([^A-Za-z0-9])', ' ', $tweet);
				//Convert urls to <a> links
				$tweet = preg_replace("/([\w]+\:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/", "<a target=\"_blank\" href=\"$1\">$1</a>", $tweet);

				//Convert hashtags to twitter searches in <a> links
				$tweet = preg_replace("/#([A-Za-z0-9\/\.]*)/", "<a target=\"_blank\" href=\"http://twitter.com/search?q=$1\">#$1</a>", $tweet);

				//Convert attags to twitter profiles in <a> links
				$tweet = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a target=\"_blank\" href=\"http://www.twitter.com/$1\">@$1</a>", $tweet);
				echo "tweet.push('".$tweet."');";
			}
			?>
			// Shapes define the clickable region of the icon.
			// The type defines an HTML &lt;area&gt; element 'poly' which
			// traces out a polygon as a series of X,Y points. The final
			// coordinate closes the poly by connecting to the first
			// coordinate.
			var shape = {
				coord: [1, 1, 1, 100,100, 100, 100 , 1],
				type: 'poly'
			};
			var markerArray = new Array();
			var posicionArray = new Array();

			for (var i = 0; i < locations.length; i++) {

				var beach = locations[i];

				var myLatLng = new google.maps.LatLng(beach[1], beach[2]);
				posicionArray.push(myLatLng);

				var marker = new google.maps.Marker({
					position: myLatLng,
					map: map,
					icon: image[i],
					shape: shape,
					title: beach[0],
					zIndex: beach[3]
				});
				markerArray.push(marker);

				markerEventListener(map,marker,user[i],tweet[i],image[i]);

			}

		}
		var infowindow = null;
		function markerEventListener(map,marker,user,tweet,image){
			//map.setZoom(16);
			//map.setCenter(posicion);
			var contenido = "<img src='"+image.url+"'></img><h4>"+user+"</h4><br>"+tweet;
			google.maps.event.addListener(marker, 'click', function(evt) {
				if(infowindow){
					infowindow.close();
				}
				infowindow = new google.maps.InfoWindow();
				infowindow.setContent(contenido);
				infowindow.setPosition(evt.latLng);
				infowindow.open(map);
			});
		}

		google.maps.event.addDomListener(window, 'load', initialize);

	</script>
</head>
<body>
<form action="" method="get">
	<input type="text" name="city" placeholder="City">
	<button type="submit">Search</button>
</form>
<div id="banner">
</div>

<div id="map-canvas"></div>
</body>
</html>