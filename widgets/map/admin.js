var my_map, my_marker;
function findMe() {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(pos) {
			var myLatlng = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
			my_map.setCenter(myLatlng);
			my_marker.setPosition(myLatlng);
			mapChanged();
		});
	}
}
function findLocation() {
	var search = prompt(MAP_SEARCH_TITLE, 'Bankok');
	if (search !== null && search !== '') {
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({
			address : search
		}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var myLatlng = results[0].geometry.location;
				my_map.setCenter(myLatlng);
				my_marker.setPosition(myLatlng);
				mapChanged();
			} else {
				alert(MAP_SEARCH_ERROR);
			}
		});
	}
}
function inintMapDemo() {
	var myLatlng = new google.maps.LatLng($E('map_latigude').value, $E('map_lantigude').value);
	var o = {
		zoom : floatval($E("map_zoom").value),
		center : myLatlng,
		mapTypeId : google.maps.MapTypeId.ROADMAP
	};
	my_map = new google.maps.Map($E("map_canvas"), o);
	google.maps.event.addListener(my_map, "zoom_changed", function() {
		var p = my_marker.getPosition();
		my_map.panTo(p);
		mapChanged();
	});
	google.maps.event.addListener(my_map, "dragend", function() {
		mapChanged();
	});
	var info = new google.maps.LatLng($E('info_latigude').value, $E('info_lantigude').value);
	my_marker = new google.maps.Marker({
		position : info,
		map : my_map,
		draggable : true,
		title : MAP_MARKER_TITLE
	});
	google.maps.event.addListener(my_marker, "dragend", function() {
		var p = my_marker.getPosition();
		my_map.panTo(p);
		mapChanged();
	});
	if (navigator.geolocation) {
		$E('find_me').disabled = false;
		callClick("find_me", findMe);
		callClick("map_search", findLocation);
	}
}
function mapChanged() {
	var p = my_marker.getPosition();
	$E("info_latigude").value = p.lat();
	$E("info_lantigude").value = p.lng();
	var c = my_map.getCenter();
	$E("map_latigude").value = c.lat();
	$E("map_lantigude").value = c.lng();
	$E("map_zoom").value = my_map.getZoom();
};