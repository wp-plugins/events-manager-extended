<?php
require_once('../../../wp-load.php');
if(isset($_GET['id']) && $_GET['id'] != "") {
	$location = dbem_get_location($_GET['id']);
	echo '{"id":"'.$location['location_id'].'" , "name"  : "'.dbem_trans_sanitize_html($location['location_name']).'","town" : "'.dbem_trans_sanitize_html($location['location_town']).'","address" : "'.dbem_trans_sanitize_html($location['location_address']).'" }';
	
} else {

	$locations = dbem_get_locations();
	$return = array();

	foreach($locations as $item) {
	  	$record = array();
	  	$record['id']      = $item['location_id'];
	  	$record['name']    = dbem_trans_sanitize_html($item['location_name']); 
		$record['address'] = dbem_trans_sanitize_html($item['location_address']);
		$record['town']    = dbem_trans_sanitize_html($item['location_town']); 
	  	$return[]  = $record;
	}

	$q = strtolower($_GET["q"]);
	if (!$q) return;
 
	foreach($return as $row) {

		if (strpos(strtolower($row['name']), $q) !== false) { 
			$location = array();
			$rows =array();
			foreach($row as $key => $value)
				$location[] = "'$key' : '".str_replace("'", "\'", $value)."'";
			echo ("{".implode(" , ", $location)." }\n");
		 }
		
	}

}
?>
