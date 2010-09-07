$j_dbem_locations=jQuery.noConflict();   
// console.log("eventful: " + eventful + " scope " + scope);

$j_dbem_locations(document.body).unload(function() {
	GUnload();
});

$j_dbem_locations(document).ready(function() {
	loadMapScript();
});

function loadGMap() {
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

			var mapCenter = new google.maps.LatLng(45.4213477,10.952397);
			var myOptions = {
				zoom: 3,
				center: mapCenter,
				disableDoubleClickZoom: true,
				mapTypeControlOptions: {
					mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE]
				},
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}
			var map = new google.maps.Map(document.getElementById("dbem_global_map"), myOptions);
			var infowindow = new google.maps.InfoWindow();

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

			var locationsBound = new google.maps.LatLngBounds(new google.maps.LatLng(max_latitude + vertical_compensation,min_longitude),new google.maps.LatLng(min_latitude,max_longitude) );
			//console.log(locationsBound);
			map.fitBounds(locationsBound);
			map.setCenter(new google.maps.LatLng(center_lat + vertical_compensation,center_lon)); 
			var letters = new Array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O');

			$j_dbem_locations.each(locations, function(i, item) {
				var letter = letters[i];

				var li_element = "<li id='location-"+item.location_id
						 + "' style='list-style-type: upper-alpha'><a >"
						 + item.location_name+"</a></li>";
				var location_info = "<div class=\"dbem-location-balloon\"><strong>"+ item.location_name
						    + "</strong><br/>" + item.location_address + ", "
						    + item.location_town + "<br/><small><a href='" + events_page_link
						    + joiner + "location_id=" + item.location_id + "'>Details<a></div>";
				customIcon = "http://www.google.com/mapfiles/marker" + letter + ".png";
				var point = new google.maps.LatLng(parseFloat(item.location_latitude), parseFloat(item.location_longitude));
				var balloon_id = "dbem-location-balloon-id";
				var balloon_content = "<div id=\""+balloon_id+"\" class=\"dbem-location-balloon\">"+location_info+"</div>";
				infowindow.balloon_id = balloon_id;
				var marker = new google.maps.Marker({
					position: point,
					map: map,
					icon: customIcon,
					infowindow: infowindow,
					infowindowcontent: balloon_content
				});
				//var pixoff = new google.maps.Size(0,-32);
				//infowindow = new google.maps.InfoWindow();
				//var infowindow = new google.maps.InfoWindow({
				//		content: location_info,
				//		pixelOffset: pixoff
				//});
				$j_dbem_locations('ol#dbem_locations_list').append(li_element);
				$j_dbem_locations('li#location-'+item.location_id+' a').click(function() {
					infowindow.setContent(balloon_content);
					infowindow.open(map,marker);
				});
				google.maps.event.addListener(marker, "click", function() {
					// This also works, but relies on global variables:
					// infowindow.setContent(location_info);
					// infowindow.open(map,marker);
					// the content of marker is available via "this"
					this.infowindow.setContent(this.infowindowcontent);
					this.infowindow.open(this.map,this);
				});
			});
			// to remove the scrollbars: we unset the overflow
			// of the parent div of the infowindow
			google.maps.event.addListener(infowindow, 'domready', function() {
					document.getElementById(this.balloon_id).parentNode.style.overflow='';
					document.getElementById(this.balloon_id).parentNode.parentNode.style.overflow='';
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
			var point = new google.maps.LatLng(lat_id, lon_id);
			var mapCenter= new google.maps.LatLng(point.lat()+0.005, point.lng()-0.003);
			var myOptions = {
				zoom: 14,
				center: mapCenter,
				disableDoubleClickZoom: true,
				mapTypeControlOptions: {
					mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE]
				},
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}
			var s_map = new google.maps.Map(divs[i], myOptions);
			var s_balloon_id= "dbem-location-balloon-"+map_id;
			var s_infowindow = new google.maps.InfoWindow({
				content: "<div id=\"" + s_balloon_id +"\" class=\"dbem-location-balloon\">"+map_text_id+"</div>",
				balloon_id: s_balloon_id
			});
			// we add the infowinfow object to the marker object, then we can call it in the 
			// google.maps.event.addListener and it always has the correct content
			// we do this because we have multiple maps as well ...
			var s_marker = new google.maps.Marker({
				position: point,
				map: s_map,
				infowindow: s_infowindow
			});
			s_infowindow.open(s_map,s_marker);
			google.maps.event.addListener(s_marker, "click", function() {
				// the content of s_marker is available via "this"
				this.infowindow.open(this.map,this);
			});
			// to remove the scrollbars: we unset the overflow
			// of the parent div of the infowindow
			google.maps.event.addListener(s_infowindow, 'domready', function() {
				document.getElementById(this.balloon_id).parentNode.style.overflow='';
				document.getElementById(this.balloon_id).parentNode.parentNode.style.overflow='';
			});
      		}
	}
}

function loadMapScript() {
	var script = document.createElement("script");
//	script.setAttribute("src", "http://maps.google.com/maps?file=api&v=2.x&key=" + key + "&c&async=2&callback=loadGMap");
//	script.setAttribute("type", "text/javascript");
//	document.documentElement.firstChild.appendChild(script);
	script.type = "text/javascript";
	script.src = "http://maps.google.com/maps/api/js?v=3.1&sensor=false&callback=loadGMap";
	document.body.appendChild(script);
}
