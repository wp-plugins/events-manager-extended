$j=jQuery.noConflict();   
//console.log("single location map");

$j(document.body).unload(function() {
	if (GBrowserIsCompatible()) {
		GUnload();
	}
});


$j(document).ready(function() {
	if (typeof GMapsKey != "undefined") {
		loadMapScriptSingle(GMapsKey);
	}
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
		var maps=new Array();
		for (var i = 0; i < divs.length; i++){                      
			divname = divs[i].id; 
			if(divname.indexOf("dbem-location-map_") == 0) { 
		//		map = new GMap2(document.getElementById("dbem-location-map"));
				var map_id = divname.replace("dbem-location-map_","");
				//var lat_id = eval('latitude_'+map_id); 
				//var lon_id = eval('longitude_'+map_id); 
				//var map_text_id = eval('map_text_'+map_id); 
				var lat_id = window['latitude_'+map_id]; 
				var lon_id = window['longitude_'+map_id]; 
				var map_text_id = window['map_text_'+map_id]; 
				maps[i] = new GMap2(divs[i]);
				//maps[i].addControl(new GLargeMapControl3D());
				maps[i].addControl(new GSmallMapControl());
				maps[i].removeMapType(G_HYBRID_MAP);
				maps[i].addControl(new GMapTypeControl());
				var point = new GLatLng(lat_id, lon_id);
				//point = new GLatLng(latitude,longitude);
				var mapCenter= new GLatLng(point.lat()+0.005, point.lng()-0.003);
				maps[i].setCenter(mapCenter, 14);
				marker = new GMarker(point);
				marker.clickable=false;
				maps[i].addOverlay(marker);
				marker.openInfoWindowHtml(map_text_id);
      			}//if
		}
	}
}
