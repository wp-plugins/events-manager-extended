$j=jQuery.noConflict();   
//console.log("single location map");

$j(document.body).unload(function() {
	if (GBrowserIsCompatible()) {
		GUnload();
	}
});


$j(document).ready(function() {
	loadMapScript(GMapsKey);
});

function loadMapScript(key) {
	var script = document.createElement("script");
	script.setAttribute("src", "http://maps.google.com/maps?file=api&v=2.x&key=" + key + "&c&async=2&callback=loadGMap");
	script.setAttribute("type", "text/javascript");
	document.documentElement.firstChild.appendChild(script);
}

function loadGMap() {
	if (GBrowserIsCompatible()) {
		var divs = document.getElementsByTagName('div');
		for (var i = 0; i < divs.length; i++){                      
			var divname = divs[i].id; 
			if(divname.indexOf("dbem-location-map-") == 0) { 
		//		map = new GMap2(document.getElementById("dbem-location-map"));
				map_id = divname.replace("dbem-location-map-","");
				map = new GMap2(divs[i]);
				map.addControl(new GLargeMapControl3D());
				point = new GLatLng(latitude+map_id, longitude+map_id);
				//point = new GLatLng(latitude,longitude);
				mapCenter= new GLatLng(point.lat()+0.005, point.lng()-0.003);
				map.setCenter(mapCenter, 14);
				var marker = new GMarker(point);
				map.addOverlay(marker);
				marker.openInfoWindowHtml(map_text);
      			}//if
		}
	}
}
