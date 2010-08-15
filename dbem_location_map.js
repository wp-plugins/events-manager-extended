$j_dbem_locations=jQuery.noConflict();   
// console.log("eventful: " + eventful + " scope " + scope);

$j_dbem_locations(document.body).unload(function() {
	if (GBrowserIsCompatible()) {
		GUnload();
	}
});

$j_dbem_locations(document).ready(function() {
	if (typeof GMapsKey != "undefined") {
		loadMapScript(GMapsKey);
	}
});

function loadGMap() {
	if (GBrowserIsCompatible()) {

		// first the global map (if present)
		if (document.getElementById("dbem_global_map")) {
			var locations;
			$j_dbem_locations.getJSON(document.URL,{ajax: 'true', query:'GlobalMapData', eventful:eventful, scope:scope}, function(data) {
				locations = data.locations;    
				var latitudes = new Array();
				var longitudes = new Array();
				var max_latitude = -500.1;
				var min_latitude = 500.1;
				var max_longitude = -500.1;
				var min_longitude = 500.1;    

				map = new GMap2(document.getElementById("dbem_global_map"));
				//map.addControl(new GLargeMapControl3D());
				map.addControl(new GSmallMapControl());
				map.removeMapType(G_HYBRID_MAP);
				map.addControl(new GMapTypeControl());
				map.setCenter(new GLatLng(45.4213477,10.952397), 3);

				$j_dbem_locations.each(locations, function(i, item) {
					latitudes.push(item.location_latitude);
					longitudes.push(item.location_longitude);
					if (parseFloat(item.location_latitude) > max_latitude)
						max_latitude = parseFloat(item.location_latitude);
					if (parseFloat(item.location_latitude) < min_latitude)
						min_latitude = parseFloat(item.location_latitude);
					if (parseFloat(item.location_longitude) > max_longitude)
						max_longitude = parseFloat(item.location_longitude);
					if (parseFloat(item.location_longitude) < min_longitude)
						min_longitude = parseFloat(item.location_longitude); 
				});

				//console.log("Latitudes: " + latitudes + " MAX: " + max_latitude + " MIN: " + min_latitude);
				//console.log("Longitudes: " + longitudes +  " MAX: " + max_longitude + " MIN: " + min_longitude);    

				center_lat = min_latitude + (max_latitude - min_latitude)/2;
				center_lon = min_longitude + (max_longitude - min_longitude)/2;
				//console.log("center: " + center_lat + " - " + center_lon) + min_longitude;

				lat_interval = max_latitude - min_latitude;
	
				//vertical compensation to fit in the markers
				vertical_compensation = lat_interval * 0.1;

				var locationsBound = new GLatLngBounds(new GLatLng(max_latitude + vertical_compensation,min_longitude),new GLatLng(min_latitude,max_longitude) );
				//console.log(locationsBound);
				var locationsZoom = map.getBoundsZoomLevel(locationsBound);
				map.setCenter(new GLatLng(center_lat + vertical_compensation,center_lon), locationsZoom); 
				var letters = new Array('A','B','C','D','E','F','G','H');
				var customIcon = new GIcon(G_DEFAULT_ICON);

				$j_dbem_locations.each(locations, function(i, item) {
					var letter = letters[i];

					customIcon.image = "http://www.google.com/mapfiles/marker" + letter + ".png";
	
					markerOption = { icon:customIcon };
					var point = new GLatLng(parseFloat(item.location_latitude), parseFloat(item.location_longitude));
					var marker = new GMarker(point, markerOption);
					map.addOverlay(marker);
					var li_element = "<li id='location-"+item.location_id+"' style='list-style-type: upper-alpha'><a >"+ item.location_name+"</a></li>";
					$j_dbem_locations('ol#dbem_locations_list').append(li_element);
					$j_dbem_locations('li#location-'+item.location_id+' a').click(function() {
						displayLocationInfo(marker, item);
					});
					GEvent.addListener(marker, "click", function() {
						displayLocationInfo(marker, item);
					});
				});
			});
		}

		// and now for the normal maps (if any)
		var divs = document.getElementsByTagName('div');
		for (var i = 0; i < divs.length; i++){                      
			divname = divs[i].id; 
			if(divname.indexOf("dbem-location-map_") == 0) { 
				var map_id = divname.replace("dbem-location-map_","");
				var lat_id = window['latitude_'+map_id]; 
				var lon_id = window['longitude_'+map_id]; 
				var map_text_id = window['map_text_'+map_id]; 
				var smap = new GMap2(divs[i]);
				//map.addControl(new GLargeMapControl3D());
				smap.addControl(new GSmallMapControl());
				smap.removeMapType(G_HYBRID_MAP);
				smap.addControl(new GMapTypeControl());
				var point = new GLatLng(lat_id, lon_id);
				var mapCenter= new GLatLng(point.lat()+0.005, point.lng()-0.003);
				smap.setCenter(mapCenter, 14);
				marker = new GMarker(point);
				smap.addOverlay(marker);
				marker.openInfoWindowHtml(map_text_id);
      			}//if
		}
	}
}

function loadMapScript(key) {
	var script = document.createElement("script");
	script.setAttribute("src", "http://maps.google.com/maps?file=api&v=2.x&key=" + key + "&c&async=2&callback=loadGMap");
	script.setAttribute("type", "text/javascript");
	document.documentElement.firstChild.appendChild(script);
}

function displayLocationInfo(marker, location) {
	var location_infos = "<strong>"+ location.location_name +"</strong><br/>" + location.location_address + ", " + location.location_town + "<br/><small><a href='" + events_page + "&location_id=" + location.location_id + "'>Details<a>";
	window.map.openInfoWindowHtml(marker.getLatLng(),location.location_baloon,  {pixelOffset: new GSize(0,-32)});
}
