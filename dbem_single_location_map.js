$j=jQuery.noConflict();   
//console.log("single location map");

$j(document.body).unload(function() {
	if (GBrowserIsCompatible()) {
		GUnload();
	}
});


$j(document).ready(function() {
	loadMapScriptSingle(GMapsKey);
});

function loadMapScriptSingle(key) {
	var script = document.createElement("script");
	script.setAttribute("src", "http://maps.google.com/maps?file=api&v=2.x&key=" + key + "&c&async=2&callback=loadGMapSingle");
	script.setAttribute("type", "text/javascript");
	document.documentElement.firstChild.appendChild(script);
}

function loadGMapSingle() {
	if (GBrowserIsCompatible()) {
		var divs = document.getElementsByTagName('div');
		for (var i = 0; i < divs.length; i++){                      
			var divname = divs[i].id; 
			if(divname.indexOf("dbem-location-map_") == 0) { 
		//		map = new GMap2(document.getElementById("dbem-location-map"));
				var map_id = divname.replace("dbem-location-map_","");
				var lat_id = eval('latitude_'+map_id); 
				var lon_id = eval('longitude_'+map_id); 
				var map_text_id = eval('map_text_'+map_id); 
				var map = new GMap2(divs[i]);
				map.addControl(new GLargeMapControl3D());
				point = new GLatLng(lat_id, lon_id);
				//point = new GLatLng(latitude,longitude);
				mapCenter= new GLatLng(point.lat()+0.005, point.lng()-0.003);
				map.setCenter(mapCenter, 14);
				var marker = new GMarker(point);
				map.addOverlay(marker);
				marker.openInfoWindowHtml(map_text_id);
      			}//if
		}
	}
}
