<?php
$feedback_message = "";
 
add_action('init', 'dbem_intercept_locations_actions');
function dbem_intercept_locations_actions() {
	if(isset($_GET['page']) && $_GET['page'] == "events-manager-locations" &&
  	   isset($_GET['doaction2']) && $_GET['doaction2'] == "Delete" &&
	   isset($_GET['action2']) && $_GET['action2'] == "delete") {
		$locations = $_GET['locations'];
		foreach($locations as $location_ID) {
		 	dbem_delete_location($location_ID);
		}
	}
}

function dbem_locations_page() {
	if(isset($_GET['action']) && $_GET['action'] == "edit") { 
		// edit location
		$location_id = $_GET['location_ID'];
		$location = dbem_get_location($location_id);
		dbem_locations_edit_layout($location);
	} elseif(isset($_POST['action']) && $_POST['action'] == "editedlocation") { 
		// location update required
		$location = array();
		$location['location_id'] = $_POST['location_ID'];
		$location['location_name'] = stripslashes($_POST['location_name']);
		$location['location_address'] = stripslashes($_POST['location_address']); 
		$location['location_town'] = stripslashes($_POST['location_town']); 
		$location['location_latitude'] = $_POST['location_latitude'];
		$location['location_longitude'] = $_POST['location_longitude'];
		$location['location_description'] = stripslashes($_POST['content']);
		
		if(empty($location['location_latitude'])) {
			$location['location_latitude']  = 0;
			$location['location_longitude'] = 0;
		}
		
		$validation_result = dbem_validate_location($location);
		if ($validation_result == "OK") {
			dbem_update_location($location); 
			if ($_FILES['location_image']['size'] > 0 )
				dbem_upload_location_picture($location);
			$message = __('The location has been updated.', 'dbem');
			$locations = dbem_get_locations();
			dbem_locations_table_layout($locations, $location, $message);
		} else {
			$message = $validation_result;
			dbem_locations_edit_layout($location, $message);
		}
	} elseif(isset($_POST['action']) && $_POST['action'] == "addlocation") {
		$location = array();
		$location['location_name'] = stripslashes($_POST['location_name']);
		$location['location_address'] = stripslashes($_POST['location_address']);
		$location['location_town'] = stripslashes($_POST['location_town']); 
		$location['location_latitude'] = $_POST['location_latitude'];
		$location['location_longitude'] = $_POST['location_longitude'];
		$location['location_description'] = stripslashes($_POST['content']);
		$validation_result = dbem_validate_location($location);
		if ($validation_result == "OK") {
			$new_location = dbem_insert_location($location);
			// uploading the image
			if ($_FILES['location_image']['size'] > 0 ) {
				dbem_upload_location_picture($new_location);
			}
					
			//RESETME $message = __('The location has been added.', 'dbem'); 
			$locations = dbem_get_locations();
			dbem_locations_table_layout($locations, null,$message);
		} else {
			$message = $validation_result;
			$locations = dbem_get_locations();
			dbem_locations_table_layout($locations, $location, $message);
		}
	} else {
		// no action, just a locations list
		$locations = dbem_get_locations();
		dbem_locations_table_layout($locations, null, "");
	}
}

function dbem_locations_edit_layout($location, $message = "") {
	?>
	<div class="wrap">
		<div id="poststuff">
			<div id="icon-edit" class="icon32">
				<br/>
			</div>
				
			<h2><?php _e('Edit location', 'dbem') ?></h2>
	 		
			<?php if($message != "") : ?>
				<div id="message" class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
					<p><?php  echo $message ?></p>
				</div>
			<?php endif; ?>
			<div id="ajax-response"></div>
	
			<form enctype="multipart/form-data" name="editcat" id="editcat" method="post" action="admin.php?page=events-manager-locations" class="validate">
			<input type="hidden" name="action" value="editedlocation" />
			<input type="hidden" name="location_ID" value="<?php echo $location['location_id'] ?>"/>
			
			<!-- we need titlediv and title for qtranslate as ID -->
			<div id="titlediv" class="form-field form-required">
			  <label for="location_name"><?php _e('Location name', 'dbem') ?></label>
			  <input name="location_name" id="title" type="text" value="<?php echo htmlspecialchars($location['location_name']); ?>" size="40" />
			  <input type="hidden" name="translated_location_name" value="<?php echo dbem_sanitize_html($location['location_name']); ?>" />
			  <p><?php _e('The name of the location', 'dbem') ?>.</p>
			</div>
			<div class="form-field">
			   <label for="location_address"><?php _e('Location address', 'dbem') ?></label>
			   <input id="location_address" name="location_address" type="text" value="<?php echo htmlspecialchars($location['location_address']); ?>" size="40"  />
			   <p><?php _e('The address of the location', 'dbem') ?>.</p>
			</div>
 
			<div class="form-field ">
			   <label for="location_town"><?php _e('Location town', 'dbem') ?></label>
			   <input name="location_town" id="location_town" type="text" value="<?php echo htmlspecialchars($location['location_town']); ?>" size="40"  />
			   <p><?php _e('The town of the location', 'dbem') ?>.</p>
			</div>
								
			<div class="form-field" style="display:none;">
			   <label for="location_latitude">LAT</label>
			   <input id="location_latitude" name="location_latitude" type="text" value="<?php echo htmlspecialchars($location['location_latitude']); ?>" size="40"  />
			</div>
			<div class="form-field" style="display:none;">
			   <label for="location_longitude">LONG</label>
			   <input id="location_longitude" name="location_longitude" type="text" value="<?php echo htmlspecialchars($location['location_longitude']); ?>" size="40"  />
			</div>
			<div class="form-field">
			   <label for="location_image"><?php _e('Location image', 'dbem') ?></label>
			   <input id="location_image" name="location_image" type="file" size="35" />
			   <p><?php _e('Select an image to upload', 'dbem') ?>.</p>
			</div>
			<?php 
				$gmap_is_active = get_option('dbem_gmap_is_active');
               			if ($gmap_is_active) :
			 ?>	
			<div><?php 
				if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
					_e("Because qtranslate is active, the title of the location will not update automatically in the balloon, so don't panic there.");
				}
				?>
			</div>
		 	<div id="map-not-found" style="width: 450px; font-size: 140%; text-align: center; margin-top: 20px; display: hide"><p><?php _e('Map not found','dbem') ?></p></div>
	 		<div id="event-map" style="width: 450px; height: 300px; background: green; display: hide; margin-right:8px"></div>
	 		<br style="clear:both;" />
			<?php endif; ?>
			<div id="poststuff">
				<label for="location_description"><?php _e('Location description', 'dbem') ?></label>
				<div class="inside">
					<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
						<?php the_editor($location['location_description']); ?>
					</div>
					<?php _e('A description of the Location. You may include any kind of info here.', 'dbem') ?>
				</div>
			</div>
			<p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e('Update location', 'dbem') ?>" /></p>

			</form>
		</div>
	</div>
	<?php
}

function dbem_locations_table_layout($locations, $new_location, $message = "") {
	$destination = admin_url("admin.php");
	if (!is_array($new_location)) {
		$location = array();
		$location['location_name'] = '';
		$location['location_address'] = '';
		$location['location_town'] = '';
		$location['location_latitude'] = '';
		$location['location_longitude'] = '';
		$location['location_description'] = '';
	}

	ob_start();
	?>
		<div class="wrap nosubsub">
			<div id="icon-edit" class="icon32">
				<br/>
			</div>
 	 		<h2><?php _e('Locations', 'dbem') ?></h2>
	 		
			<?php if($message != "") : ?>
				<div id="message" class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
					<p><?php echo $message ?></p>
				</div>
			<?php endif; ?>
			<div id="col-container">
				<div id="col-right">
			 	 <div class="col-wrap">
				 	 <form id="bookings-filter" method="get" action="<?php echo $destination ?>">
						<input type="hidden" name="page" value="events-manager-locations"/>
						<input type="hidden" name="action" value="edit_location"/>
						
						<?php if (count($locations)>0) : ?>
						<table class="widefat">
							<thead>
								<tr>
									<th class="manage-column column-cb check-column" scope="col"><input type="checkbox" class="select-all" value="1"/></th>
									<th><?php _e('Name', 'dbem') ?></th>
									<th><?php _e('Address', 'dbem') ?></th>
									<th><?php _e('Town', 'dbem') ?></th>
								</tr> 
							</thead>
							<tfoot>
								<tr>
									<th class="manage-column column-cb check-column" scope="col"><input type="checkbox" class="select-all" value="1"/></th>
									<th><?php _e('Name', 'dbem') ?></th>
									<th><?php _e('Address', 'dbem') ?></th>
									<th><?php _e('Town', 'dbem') ?></th>
								</tr>
							</tfoot>
							<tbody>
								<?php foreach ($locations as $this_location) : ?>	
								<tr>
									<td><input type="checkbox" class ="row-selector" value="<?php echo $this_location['location_id'] ?>" name="locations[]"/></td>
									<td><a href="<?php echo admin_url("admin.php?page=events-manager-locations&action=edit&location_ID=".$this_location['location_id']); ?>"><?php echo dbem_sanitize_html($this_location['location_name']); ?></a></td>
									<td><?php echo dbem_sanitize_html($this_location['location_address']); ?></td>
									<td><?php echo dbem_sanitize_html($this_location['location_town']); ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>

						</table>

						<div class="tablenav">
							<div class="alignleft actions">
							<input type="hidden" name="action2" value="delete"/>
						 	<input class="button-secondary action" type="submit" name="doaction2" value="Delete"/>
							<br class="clear"/> 
							</div>
							<br class="clear"/>
						</div>
						<?php else: ?>
							<p><?php _e('No venues have been inserted yet!', 'dbem') ?></p>
						<?php endif; ?>
						</form>
					</div>
				</div>  <!-- end col-right -->
				
				<div id="col-left">
			  	<div class="col-wrap">
						<div class="form-wrap"> 
							<div id="ajax-response"/>
					  	<h3><?php _e('Add location', 'dbem') ?></h3>
							 <form enctype="multipart/form-data" name="addlocation" id="addlocation" method="post" action="admin.php?page=events-manager-locations" class="add:the-list: validate">
								<input type="hidden" name="action" value="addlocation" />
								<div id="titlediv" class="form-field form-required">
								  <label for="location_name"><?php _e('Location name', 'dbem') ?></label>
						 		  <input name="location_name" id="title" type="text" value="<?php echo htmlspecialchars($new_location['location_name']); ?>" size="40" />
			  					  <input type="hidden" name="translated_location_name" value="<?php echo dbem_sanitize_html($location['location_name']); ?>" />
								  <p><?php _e('The name of the location', 'dbem') ?>.</p>
								</div>

								<div class="form-field">
								   <label for="location_address"><?php _e('Location address', 'dbem') ?></label>
								   <input id="location_address" name="location_address" type="text" value="<?php echo htmlspecialchars($new_location['location_address']); ?>" size="40"  />
								   <p><?php _e('The address of the location', 'dbem') ?>.</p>
								</div>
 
								<div class="form-field ">
								   <label for="location_town"><?php _e('Location town', 'dbem') ?></label>
								   <input id="location_town" name="location_town" type="text" value="<?php echo htmlspecialchars($new_location['location_town']); ?>" size="40"  />
								   <p><?php _e('The town of the location', 'dbem') ?>.</p>
								</div>
								
								<div class="form-field" style="display:none;">
								   <label for="location_latitude">LAT</label>
								   <input id="location_latitude" name="location_latitude" type="text" value="<?php echo htmlspecialchars($new_location['location_latitude']); ?>" size="40"  />
								</div>
								<div class="form-field" style="display:none;">
								   <label for="location_longitude">LONG</label>
								   <input id="location_longitude" name="location_longitude" type="text" value="<?php echo htmlspecialchars($new_location['location_longitude']); ?>" size="40"  />
								</div>
								
								<div class="form-field">
								   <label for="location_image"><?php _e('Location image', 'dbem') ?></label>
								   <input id="location_image" name="location_image" type="file" size="35" />
								    <p><?php _e('Select an image to upload', 'dbem') ?>.</p>
								</div>
								<?php 
									$gmap_is_active = get_option('dbem_gmap_is_active');
                 					if ($gmap_is_active) :
								 ?>	
						 		 	<div id="map-not-found" style="width: 450px; font-size: 140%; text-align: center; margin-top: 20px; display: hide"><p><?php _e('Map not found','dbem') ?></p></div>
							 		<div id="event-map" style="width: 450px; height: 300px; background: green; display: hide; margin-right:8px"></div>
							 		<br style="clear:both;" />
								 <?php endif; ?>
									<div id="poststuff">
										<label for="location_description"><?php _e('Location description', 'dbem') ?></label>
										<div class="inside">
											<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
												<?php the_editor($new_location['location_description']); ?>
											</div>
											<?php _e('A description of the Location. You may include any kind of info here.', 'dbem') ?>
										</div>
									</div>
								 <p class="submit"><input type="submit" class="button" name="submit" value="<?php _e('Add location', 'dbem') ?>" /></p>
							 </form>

					  </div>
					</div> 
				</div>  <!-- end col-left -->
			</div> 
  	</div>
  	<?php
	echo ob_get_clean();
}

function dbem_get_locations($eventful = false, $scope="all") { 
	global $wpdb;
	$locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
	$events_table = $wpdb->prefix.EVENTS_TBNAME;

	$tzstring = get_option('timezone_string');
	if (!empty($tzstring) ) {
		@date_default_timezone_set ($tzstring);
	}

        $timestamp = time ();
        $date_time_array = getdate ( $timestamp );
        $hours = $date_time_array ['hours'];
        $minutes = $date_time_array ['minutes'];
        $seconds = $date_time_array ['seconds'];
        $month = $date_time_array ['mon'];
        $day = $date_time_array ['mday'];
        $year = $date_time_array ['year'];
        $today = strftime ( '%Y-%m-%d', mktime ( $hours, $minutes, $seconds, $month, $day, $year ) );

        $condition = "";
	if (($scope != "past") && ($scope != "all") && ($scope != "future"))
		$scope = "future";
	if ($scope == "future")
		//This is so events with future dates are counted too
		$condition = "AND ($events_table.event_start_date >= '$today' OR ($events_table.event_end_date >= '$today' AND $events_table.event_end_date != '0000-00-00' AND $events_table.event_end_date IS NOT NULL))";
	if ($scope == "past")
		$condition = "AND $events_table.event_start_date < '$today'";

	// for the query: we don't do "SELECT *" because the data returned from this function is also used in the function dbem_global_map_json()
	// and some fields from the events table contain carriage returns, which can't be passed along
	// The function dbem_global_map_json tries to remove these, but the data is not needed and better be safe than sorry
	if ($eventful == 'true') {
		$sql = "SELECT $locations_table.* from $locations_table JOIN $events_table ON $locations_table.location_id = $events_table.location_id WHERE $locations_table.location_name != '' $condition";
	} else {
		$sql = "SELECT * FROM $locations_table WHERE location_name != '' ORDER BY location_name";
	}
	$locations = $wpdb->get_results($sql, ARRAY_A); 
	return $locations;
}

function dbem_get_location($location_id) { 
	global $wpdb;

	$location=array();
	if (!$location_id) {
		$location ['location_id']='';
		$location ['location_name']='';
		$location ['location_address']='';
		$location ['location_town']='';
		$location ['location_latitude']='';
		$location ['location_longitude']='';
		$location ['location_image_url']='';
	} else {
		$locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
		$sql = "SELECT * FROM $locations_table WHERE location_id ='$location_id'";
		$location = $wpdb->get_row($sql, ARRAY_A);
		$location['location_image_url'] = dbem_image_url_for_location_id($location['location_id']);
	}
	return $location;
}

function dbem_image_url_for_location_id($location_id) {
	$file_name= ABSPATH.IMAGE_UPLOAD_DIR."/location-".$location_id;
	$mime_types = array('gif','jpg','png');foreach($mime_types as $type) { 
		$file_path = "$file_name.$type";
		if (file_exists($file_path)) {
			$result = site_url("/".IMAGE_UPLOAD_DIR."/location-$location_id.$type");
  			return $result;
		}
	}
	return '';
}

function dbem_get_identical_location($location) { 
	global $wpdb;
	$locations_table = $wpdb->prefix.LOCATIONS_TBNAME; 
	//$sql = "SELECT * FROM $locations_table WHERE location_name ='".$location['location_name']."' AND location_address ='".$location['location_address']."' AND location_town ='".$location['location_town']."';";
  $prepared_sql=$wpdb->prepare("SELECT * FROM $locations_table WHERE location_name = %s AND location_address = %s AND location_town = %s", stripcslashes($location['location_name']), stripcslashes($location['location_address']), stripcslashes($location['location_town']) );
	//$wpdb->show_errors(true);
	$cached_location = $wpdb->get_row($prepared_sql, ARRAY_A);
	return $cached_location;
}

function dbem_validate_location($location) {
	global $location_required_fields;
	$troubles = "";
	foreach ($location_required_fields as $field => $description) {
		if ($location[$field] == "" ) {
			$troubles .= "<li>".$description.__(" is missing!", "dbem")."</li>";
		}
	}
	if ($_FILES['location_image']['size'] > 0 ) { 
		if (is_uploaded_file($_FILES['location_image']['tmp_name'])) {
 	 		$mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
			$maximum_size = get_option('dbem_image_max_size'); 
			if ($_FILES['location_image']['size'] > $maximum_size) 
	     			$troubles = "<li>".__('The image file is too big! Maximum size:', 'dbem')." $maximum_size</li>";
	  		list($width, $height, $type, $attr) = getimagesize($_FILES['location_image']['tmp_name']);
			$maximum_width = get_option('dbem_image_max_width'); 
			$maximum_height = get_option('dbem_image_max_height'); 
	  		if (($width > $maximum_width) || ($height > $maximum_height)) 
	     			$troubles .= "<li>". __('The image is too big! Maximum size allowed:')." $maximum_width x $maximum_height</li>";
	  		if (($type!=1) && ($type!=2) && ($type!=3)) 
		      		$troubles .= "<li>".__('The image is in a wrong format!')."</li>";
  		} 
	}

	if ($troubles == "") {
		return "OK";
	} else {
		$message = __('Ach, some problems here:', 'dbem')."<ul>\n$troubles</ul>";
		return $message; 
	}
}

function dbem_update_location($location) {
	global $wpdb;
	$locations_table = $wpdb->prefix.LOCATIONS_TBNAME;
	$location=dbem_sanitize_request($location);
	$sql="UPDATE ".$locations_table. 
	" SET location_name='".$location['location_name']."', ".
		"location_address='".$location['location_address']."',".
		"location_town='".$location['location_town']."', ".
		"location_latitude=".$location['location_latitude'].",". 
		"location_longitude=".$location['location_longitude'].",".
		"location_description='".$location['location_description']."' ". 
		"WHERE location_id='".$location['location_id']."';";
	$wpdb->query($sql);
}

function dbem_insert_location($location) {
	global $wpdb;	
	$table_name = $wpdb->prefix.LOCATIONS_TBNAME; 
	$location=dbem_sanitize_request($location);
	// if GMap is off the hidden fields are empty, so I add a custom value to make the query work
	if (empty($location['location_longitude'])) 
		$location['location_longitude'] = 0;
	if (empty($location['location_latitude'])) 
		$location['location_latitude'] = 0;
	$sql = "INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude, location_description)
		VALUES ('".$location['location_name']."','".$location['location_address']."','".$location['location_town']."',".$location['location_latitude'].",".$location['location_longitude'].",'".$location['location_description']."')"; 
	$wpdb->query($sql);
	$new_location = dbem_get_location(mysql_insert_id());
	return $new_location;
}

function dbem_delete_location($location) {
	global $wpdb;	
	$table_name = $wpdb->prefix.LOCATIONS_TBNAME;
	$sql = "DELETE FROM $table_name WHERE location_id = '$location';";
	$wpdb->query($sql);
	dbem_delete_image_files_for_location_id($location);
}

function dbem_location_has_events($location_id) {
	global $wpdb;	
	$events_table = $wpdb->prefix.EVENTS_TBNAME;
	$sql = "SELECT event_id FROM $events_table WHERE location_id = $location_id";
 	$affected_events = $wpdb->get_results($sql);
	return (count($affected_events) > 0);
}

function dbem_upload_location_picture($location) {
  	if(!file_exists("../".IMAGE_UPLOAD_DIR))
				mkdir("../".IMAGE_UPLOAD_DIR, 0777);
	dbem_delete_image_files_for_location_id($location['location_id']);
	$mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
	list($width, $height, $type, $attr) = getimagesize($_FILES['location_image']['tmp_name']);
	$image_path = "../".IMAGE_UPLOAD_DIR."/location-".$location['location_id'].".".$mime_types[$type];
	if (!move_uploaded_file($_FILES['location_image']['tmp_name'], $image_path)) 
		$msg = "<p>".__('The image could not be loaded','dbem')."</p>";
}

function dbem_delete_image_files_for_location_id($location_id) {
	$file_name= "../".IMAGE_UPLOAD_DIR."/location-".$location_id;
	$mime_types = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
	foreach($mime_types as $type) { 
		if (file_exists($file_name.".".$type))
  		unlink($file_name.".".$type);
	}
}

function dbem_global_map($atts) {
	if (get_option('dbem_gmap_is_active') == '1') {
	extract(shortcode_atts(array(
			'eventful' => "false",
			'scope' => 'all',
			'width' => 450,
			'height' => 300
		), $atts));
	$events_page_link = dbem_get_events_page(true, false);
	if (stristr($events_page_link, "?"))
		$joiner = "&";
	else
		$joiner = "?";

	$result = "";
	$result .= "<div id='dbem_global_map' style='width: {$width}px; height: {$height}px'>map</div>";
	$result .= "<script type='text/javascript'>
	<!--// 
	  eventful = $eventful;
	  scope = '$scope';
	  events_page_link = '$events_page_link';
	  location_infos = '$location_infos';
	  joiner = '$joiner'
	//-->
	</script>";
	//$result .= "<script src='".DBEM_PLUGIN_URL."dbem_global_map.js' type='text/javascript'></script>";
	$result .= "<ol id='dbem_locations_list'></ol>"; 
	
	} else {
		$result = "";
	}
	return $result;
}
add_shortcode('locations_map', 'dbem_global_map'); 

function dbem_replace_locations_placeholders($format, $location, $target="html") {
	$location_string = $format;
	preg_match_all("/#@?_?[A-Za-z]+/", $format, $placeholders);
	foreach($placeholders[0] as $result) {
		// echo "RESULT: $result <br>";
		// matches alla fields placeholder
		if (preg_match('/#_MAP/', $result)) {
		 	$map_div = dbem_single_location_map($location);
		 	$location_string = str_replace($result, $map_div , $location_string ); 
		}
//		if (preg_match('/#_GOOGLEDIRECTIONS/', $result)) {
//			$google_directions = "Get Directions";
//			$location_string = str_replace($result, $google_directions , $location_string );
//		}
		if (preg_match('/#_PASTEVENTS/', $result)) {
		 	$list = dbem_events_in_location_list($location, "past");
		 	$location_string = str_replace($result, $list , $location_string ); 
		}
		if (preg_match('/#_NEXTEVENTS/', $result)) {
		 	$list = dbem_events_in_location_list($location);
		 	$location_string = str_replace($result, $list , $location_string ); 
		}
		if (preg_match('/#_ALLEVENTS/', $result)) {
		 	$list = dbem_events_in_location_list($location, "all");
		 	$location_string = str_replace($result, $list , $location_string ); 
		}

		if (preg_match('/#_(NAME|ADDRESS|TOWN|DESCRIPTION)/', $result)) {
			$field = "location_".ltrim(strtolower($result), "#_");
		 	$field_value = $location[$field];
		
			if ($field == "location_description") {
				// no real sanitizing needed, but possible translation
				// this is the same as for an event in fact
				$field_value = dbem_sanitize_html($field_value,0);
				if ($target == "html")
					$field_value = apply_filters('dbem_notes', $field_value);
				else
				  if ($target == "map")
					$field_value = apply_filters('dbem_notes_map', $field_value);
				  else
				 	$field_value = apply_filters('dbem_notes_rss', $field_value);
		  	} else {
				$field_value = dbem_sanitize_html($field_value);
				if ($target == "html")
					$field_value = apply_filters('dbem_general', $field_value); 
				else 
					$field_value = apply_filters('dbem_general_rss', $field_value); 
			}
			$location_string = str_replace($result, $field_value , $location_string ); 
	 	}

		if (preg_match('/#_(IMAGE)/', $result)) {
        		if($location['location_image_url'] != '')
				$location_image = "<img src='".$location['location_image_url']."' alt='".dbem_sanitize_html($location['location_name'])."'/>";
			else
				$location_image = "";
			$location_string = str_replace($result, $location_image , $location_string ); 
		}
		if (preg_match('/#_(LOCATIONPAGEURL)/', $result)) {
			$events_page_link = dbem_get_events_page(true, false);
			if (stristr($events_page_link, "?"))
				$joiner = "&";
			else
				$joiner = "?";
			$venue_page_link = $events_page_link.$joiner."location_id=".$location['location_id'];
			$location_string = str_replace($result, $venue_page_link , $location_string ); 
		}
	}
	return $location_string;	
}

function dbem_add_directions_form($location) {
	$res = '
<form action="http://maps.google.com/maps" method="get" target="_blank" style="text-align:left;">
<label for="saddr">'.__('Your Street Address','dbem').'</label>
<input type="text" name="saddr" id="saddr" value="" />
<input type="submit" value="'.__('Get Directions','dbem').'" />
<input type="hidden" name="daddr" value="'.$location['location_address'].', '.$location['location_town'].'" />
<input type="hidden" name="hl" value="'.$locale_code.'" />
</form>';
	return $res;
}

function dbem_single_location_map($location) {
	$gmap_is_active = get_option('dbem_gmap_is_active'); 
	$map_text = addslashes(dbem_replace_locations_placeholders(get_option('dbem_location_baloon_format'), $location));
	$map_text = preg_replace("/\r\n|\n\r|\n/","<br />",$map_text);
	// if gmap is not active: we don't show the map
	// if the location name is empty: we don't show the map
	if ($gmap_is_active && !empty($location['location_name']) && !empty($location['location_address']) && !empty($location['location_town'])) {
		//$id_base = $location['location_id'];
		// we can't create a unique <div>-id based on location id, because you can have multiple maps on the sampe page for
		// different events but they can go to the same location...
		$id_base = preg_replace("/\./","_",microtime(1));
		$id="dbem-location-map_".$id_base;
		$latitude_string="latitude_".$id_base;
		$longitude_string="longitude_".$id_base;
		$map_text_string="map_text_".$id_base;
		#$latitude_string="latitude";
		#$longitude_string="longitude";
   		//$map_div = "<div id='$id' style=' background: green; width: 400px; height: 300px'></div>" ;
   		$map_div = "<div id='$id' class=\"dbem-location-map\"></div>" ;
   		$map_div .= "<script type='text/javascript'>
  			<!--// 
  		$latitude_string = parseFloat('".$location['location_latitude']."');
  		$longitude_string = parseFloat('".$location['location_longitude']."');
  		$map_text_string = '$map_text';
		//-->
		</script>";
		// $map_div .= "<script src='".DBEM_PLUGIN_URL."dbem_single_location_map.js' type='text/javascript'></script>";
	} else {
		$map_div = "";
	}
	return $map_div;
}

function dbem_events_in_location_list($location, $scope = "") {
	$events = dbem_get_events(0,$scope,"","",$location['location_id']);
	$list = "";
	if (count($events) > 0) {
		foreach($events as $event)
			$list .= dbem_replace_placeholders(get_option('dbem_location_event_list_item_format'), $event);
	} else {
		$list = get_option('dbem_location_no_events_message');
	}
	return $list;
}

add_action ('admin_head', 'dbem_locations_autocomplete');

function dbem_locations_autocomplete() {
        $use_select_for_locations = get_option('dbem_use_select_for_locations');
	// qtranslate there? Then we need the select
	if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
		$use_select_for_locations=1;
	}

	if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_event') || (isset($_GET['page']) && $_GET['page'] == 'events-manager-new_event')) {
		?>
		<link rel="stylesheet" href="<?php echo DBEM_PLUGIN_URL; ?>js/jquery-autocomplete/jquery.autocomplete.css" type="text/css"/>

		<script src="<?php echo DBEM_PLUGIN_URL; ?>js/jquery-autocomplete/lib/jquery.bgiframe.min.js" type="text/javascript"></script>
		<script src="<?php echo DBEM_PLUGIN_URL; ?>js/jquery-autocomplete/lib/jquery.ajaxQueue.js" type="text/javascript"></script> 
		<script src="<?php echo DBEM_PLUGIN_URL; ?>js/jquery-autocomplete/jquery.autocomplete.min.js" type="text/javascript"></script>

		<script type="text/javascript">
		//<![CDATA[
		jQuery.noConflict();

		jQuery(document).ready(function($) {
			var gmap_enabled = <?php echo get_option('dbem_gmap_is_active'); ?>; 

		   <?php if(!$use_select_for_locations) :?>
			$("input#location_name").autocomplete("<?php echo DBEM_PLUGIN_URL; ?>locations-search.php", {
				width: 260,
				selectFirst: false,
				formatItem: function(row) {
					item = eval("(" + row + ")");
					return item.name+'<br/><small>'+item.address+' - '+item.town+ '</small>';
				},
				formatResult: function(row) {
					item = eval("(" + row + ")");
					return item.name;
				} 

			});
			$('input#location_name').result(function(event,data,formatted) {
				item = eval("(" + data + ")"); 
				$('input#location_address').val(item.address);
				$('input#location_town').val(item.town);
				if(gmap_enabled) {
					eventLocation = $("input#location_name").val(); 
					eventTown = $("input#location_town").val(); 
					eventAddress = $("input#location_address").val();
					loadMap(eventLocation, eventTown, eventAddress)
				} 
			});
			<?php else : ?>
			$('#location-select-id').change(function() {
				$.getJSON("<?php echo DBEM_PLUGIN_URL; ?>locations-search.php",{id: $(this).val()}, function(data){
					eventLocation = data.name;
					eventAddress = data.address;
					eventTown = data.town;
					$("input[name='location-select-name']").val(eventLocation);
					$("input[name='location-select-address']").val(eventAddress); 
					$("input[name='location-select-town']").val(eventTown); 
					loadMap(eventLocation, eventTown, eventAddress)
			   	})
			});
			<?php endif; ?>
		});	
		//]]> 

		</script>

		<?php

	}
}


function dbem_cache_location($event){
	$related_location = dbem_get_location_by_name($event['location_name']);
	if (!$related_location) {
		dbem_insert_location_from_event($event);
		return;
	} 
	if ($related_location->location_address != $event['location_address'] || $related_location->location_town != $event['location_town']  ) {
		dbem_insert_location_from_event($event);
	}
}

function dbem_get_location_by_name($name) {
	global $wpdb;	
	$sql = "SELECT location_id, 
	location_name, 
	location_address,
	location_town
	FROM ".$wpdb->prefix.LOCATIONS_TBNAME.
	" WHERE location_name = '$name'";
	$event = $wpdb->get_row($sql);	

	return $event;
}

function dbem_insert_location_from_event($event) {
	global $wpdb;	
	$table_name = $wpdb->prefix.LOCATIONS_TBNAME;
	$wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town)
	VALUES ('".$event['location_name']."', '".$event['location_address']."','".$event['location_town']."')");

}
