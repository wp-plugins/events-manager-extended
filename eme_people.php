<?php
function eme_people_page() {
	// Managing AJAX booking removal
 	if(isset($_GET['action']) && $_GET['action'] == 'remove_booking') {
		if(isset($_POST['booking_id']))
			eme_delete_booking($_POST['booking_id']);
	}
	?>
	
	<div class='wrap'> 
	<div id="icon-users" class="icon32"><br/></div>
	<h2>People</h2>
	<?php admin_show_warnings(); eme_people_table(); ?>
	</div> 

	<?php
}

add_action('init','eme_ajax_actions'); 
function eme_ajax_actions() {
 	if(isset($_GET['eme_ajax_action']) && $_GET['eme_ajax_action'] == 'booking_data') {
		if(isset($_GET['id']))
			echo "[ {bookedSeats:".eme_get_booked_seats($_GET['id']).", availableSeats:".eme_get_available_seats($_GET['id'])."}]"; 
		die();
	}
	if(isset($_GET['action']) && $_GET['action'] == 'printable'){
		if(isset($_GET['event_id']))
			eme_printable_booking_report(intval($_GET['event_id']));
	}
	
	if(isset($_GET['query']) && $_GET['query'] == 'GlobalMapData') { 
		eme_global_map_json($_GET['eventful'],$_GET['scope']);		
	 	die();
 	}
}

function eme_global_map_json($eventful = false, $scope = "all") {
	$json = '{"locations":[';
	$locations = eme_get_locations($eventful,$scope);
	$json_locations = array();
	foreach($locations as $location) {
		$json_location = array();
		foreach($location as $key => $value) {
			# no newlines allowed, otherwise no map is shown
			$value=preg_replace("/\r\n|\n\r|\n/","<br />",eme_trans_sanitize_html($value));
		 	$json_location[] = '"'.$key.'":"'.$value.'"';
		}
		$tmp_loc=eme_replace_locations_placeholders(get_option('eme_location_baloon_format'), $location);
		# no newlines allowed, otherwise no map is shown
		$tmp_loc=preg_replace("/\r\n|\n\r|\n/","<br />",$tmp_loc);
		$json_location[] = '"location_balloon":"'.eme_trans_sanitize_html($tmp_loc).'"';
		$json_locations[] = "{".implode(",",$json_location)."}";
	}
	$json .= implode(",", $json_locations); 
	$json .= "]}" ;
	echo $json;
}

function eme_printable_booking_report($event_id) {
	$event = eme_get_event($event_id);
	$bookings =  eme_get_bookings_for($event_id);
	$available_seats = eme_get_available_seats($event_id);
	$booked_seats = eme_get_booked_seats($event_id);
	$stylesheet = EME_PLUGIN_URL."events_manager.css";
	?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html>
		<head>
			<meta http-equiv="Content-type" content="text/html; charset=utf-8">
			<title>Bookings for <?php echo $event['event_name'];?></title>
			 <link rel="stylesheet" href="<?php echo $stylesheet; ?>" type="text/css" media="screen" />
			
		</head>
		<body id="printable">
			<div id="container">
			<h1>Bookings for <?php echo $event['event_name'];?></h1> 
			<p><?php echo eme_replace_placeholders("#d #M #Y", $event)?></p>
			<p><?php echo eme_replace_placeholders("#_LOCATION, #_ADDRESS, #_TOWN", $event)?></p>
			<h2><?php _e('Bookings data', 'eme');?></h2>
			<table id="bookings-table">
				<tr>
					<th scope='col'><?php _e('Name', 'eme')?></th>
					<th scope='col'><?php _e('E-mail', 'eme')?></th>
					<th scope='col'><?php _e('Phone number', 'eme')?></th> 
					<th scope='col'><?php _e('Seats', 'eme')?></th>
					<th scope='col'><?php _e('Comment', 'eme')?></th> 
				<?php
				foreach($bookings as $booking) {
					$pending_string="";
					if (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
						$pending_string=__('(pending)','eme');
					}
			       ?>
				<tr>
					<td><?php echo $booking['person_name']?></td> 
					<td><?php echo $booking['person_email']?></td>
					<td><?php echo $booking['person_phone']?></td>
					<td class='seats-number'><?php echo $booking['booking_seats']." ".$pending_string?></td>
					<td><?=$booking['booking_comment'] ?></td> 
				</tr>
			   	<?php } ?>
			  	<tr id='booked-seats'>
					<td colspan='3'>&nbsp;</td>
					<td class='total-label'><?php _e('Booked', 'eme')?>:</td>
					<td class='seats-number'><?php echo $booked_seats; ?></td>
				</tr>
				<tr id='available-seats'>
					<td colspan='3'>&nbsp;</td> 
					<td class='total-label'><?php _e('Available', 'eme')?>:</td>
					<td class='seats-number'><?php echo $available_seats; ?></td>
				</tr>
			</table>
			</div>
		</body>
		</html>
		<?php
		die();
 		
} 

function eme_people_table() {
	$people = eme_get_people();
	if (count($people) < 1 ) {
		_e("No people have responded to your events yet!", 'eme');
	} else { 
		$table = "<p>".__('This table collects the data about the people who responded to your events', 'eme')."</p>";	
		$table .=" <table id='eme-people-table' class='widefat post fixed'>
				<thead>
				<tr>
				<th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>
				<th class='manage-column ' scope='col'>Name</th>
				<th scope='col'>E-mail</th>
				<th scope='col'>Phone number</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
				<th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>
				<th class='manage-column ' scope='col'>Name</th>
				<th scope='col'>E-mail</th>
				<th scope='col'>Phone number</th>
				</tr>
				</tfoot>
			" ;
		foreach ($people as $person) {
				$table .= "<tr> <td>&nbsp;</td>
						<td>".$person['person_name']."</td>
						<td>".$person['person_email']."</td>
						<td>".$person['person_phone']."</td></tr>";
		}

		$table .= "</table>";
		echo $table;
	}
} 

function eme_get_person_by_name_and_email($name, $email) {
	global $wpdb; 
	$people_table = $wpdb->prefix.PEOPLE_TBNAME;
	$name = eme_sanitize_request($name);
	$email = eme_sanitize_request($email);
	$sql = "SELECT person_id, person_name, person_email, person_phone FROM $people_table WHERE person_name = '$name' AND person_email = '$email' ;" ;
	$result = $wpdb->get_row($sql, ARRAY_A);
	return $result;
}

function eme_get_person_by_wp_id($wp_id) {
	global $wpdb; 
	$people_table = $wpdb->prefix.PEOPLE_TBNAME;
	$wp_id = eme_sanitize_request($wp_id);
	$sql = "SELECT person_id, person_name, person_email, person_phone FROM $people_table WHERE wp_id = '$wp_id';" ;
	$result = $wpdb->get_row($sql, ARRAY_A);
	return $result;
}

function eme_get_person($person_id) {
	global $wpdb; 
	$people_table = $wpdb->prefix.PEOPLE_TBNAME;
	$sql = "SELECT person_id, person_name, person_email, person_phone FROM $people_table WHERE person_id = '$person_id';" ;
	$result = $wpdb->get_row($sql, ARRAY_A);
	return $result;
}

function eme_get_people() {
	global $wpdb; 
	$people_table = $wpdb->prefix.PEOPLE_TBNAME;
	$sql = "SELECT *  FROM $people_table";
	$result = $wpdb->get_results($sql, ARRAY_A);
	return $result;
}

function eme_add_person($name, $email, $phone, $wp_id) {
	global $wpdb; 
	$people_table = $wpdb->prefix.PEOPLE_TBNAME;
	$name = eme_sanitize_request($name);
	$email = eme_sanitize_request($email);
	$phone = eme_sanitize_request($phone);
	$wp_id = eme_sanitize_request($wp_id);
	$sql = "INSERT INTO $people_table (person_name, person_email, person_phone, wp_id) VALUES ('$name', '$email', '$phone', '$wp_id');";
	$wpdb->query($sql);
	if ($eme_rsvp_registered_users_only) {
		$new_person = eme_get_person_by_wp_id($wp_id);
	} else {
		$new_person = eme_get_person_by_name_and_email($name, $email);
	}
	return ($new_person);
}

add_action('edit_user_profile', 'eme_phone_field') ;
function eme_phone_field() {
	?>
	<h3><?php _e('Phone number', 'eme')?></h3>
	<table class='form-table'>
		<tr>
			<th><?php _e('Phone number','eme');?></th>
			<td><input id="eme_phone" class="regular-text" type="text" value="" name="eme_phone"/> <br/>
			<?php _e('The phone number used by Events Manager when the user is indicated as the contact person for an event.','eme');?></td>
		</tr>
	</table>
	<?php
}

add_action('profile_update','eme_update_phone');
function eme_update_phone($user_ID) {
	if(isset($_POST['eme_phone']) && $_POST['eme_phone'] != '') {
		update_usermeta($user_ID,'eme_phone', $_POST['eme_phone']);
	}
	
}

function eme_get_indexed_users() {
	global $wpdb;
	$sql = "SELECT display_name, ID FROM $wpdb->users";
	$users = $wpdb->get_results($sql, ARRAY_A);
	$indexed_users = array();
	foreach($users as $user) 
		$indexed_users[$user['ID']] = $user['display_name'];
 	return $indexed_users;
}
?>
