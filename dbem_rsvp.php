<?php
$form_add_message = "";
$form_delete_message = "";
function dbem_add_booking_form($event_id) {
	global $form_add_message, $current_user;
 
	$bookerName="";
	$bookerEmail="";
	$dbem_rsvp_registered_users_only=get_option('dbem_rsvp_registered_users_only');
	$readonly="disabled=\"disabled\"";
	if ($dbem_rsvp_registered_users_only) {
		// we require a user to be WP registered to be able to book
		if (!is_user_logged_in()) {
			return;
		} else {
			get_currentuserinfo();
			$bookerName=$current_user->display_name;
			$bookerEmail=$current_user->user_email;
		}
	}
	$destination = "?".$_SERVER['QUERY_STRING']."#dbem-rsvp-message";

	// you can book the available number of seats, with a max of 10 per time
	$max = dbem_get_available_seats($event_id);
	if ($max > 10) {
		$max = 10;
	}
	// no seats anymore? No booking form then ...
	if ($max == 0) {
		$ret_string = "";
		if(!empty($form_add_message))
			$ret_string .= "<div id='dbem-rsvp-message' class='dbem-rsvp-message'>$form_add_message</div>";
		 return $ret_string."<div class='dbem-rsvp-message'>".__('Bookings no longer possible: no seats available anymore', 'dbem')."</div>";
	}

	$module = "<h3>".__('Book now!','dbem')."</h3><br/>";
	if(!empty($form_add_message))
		$module .= "<div id='dbem-rsvp-message' class='dbem-rsvp-message'>$form_add_message</div>";
	$booked_places_options = array();
	for ( $i = 1; $i <= $max; $i++) 
		array_push($booked_places_options, "<option value='$i'>$i</option>");
	
		$module  .= "<form id='dbem-rsvp-form' name='booking-form' method='post' action='$destination'>
			<table class='dbem-rsvp-form'>
				<tr><th scope='row'>".__('Name', 'dbem')."*:</th><td><input type='text' name='bookerName' value='$bookerName' $readonly /></td></tr>
				<tr><th scope='row'>".__('E-Mail', 'dbem')."*:</th><td><input type='text' name='bookerEmail' value='$bookerEmail' $readonly /></td></tr>
				<tr><th scope='row'>".__('Phone number', 'dbem')."*:</th><td><input type='text' name='bookerPhone' value='' /></td></tr>
				<tr><th scope='row'>".__('Seats', 'dbem')."*:</th><td><select name='bookedSeats' >";
		foreach($booked_places_options as $option) {
			$module .= $option."\n";
		}
		$module .= "</select></td></tr>
				<tr><th scope='row'>".__('Comment', 'dbem').":</th><td><textarea name='bookerComment'></textarea></td></tr>";
		if (get_option('dbem_captcha_for_booking')) {
			$module .= "
				<tr><th scope='row'>".__('Please fill in the code displayed here', 'dbem').":</th><td><img src='".DBEM_PLUGIN_URL."captcha.php'><br>
				      <input type='text' name='captcha_check' /></td></tr>
				";
		}
		// also add a honeypot field: if it gets completed with data, 
		// it's a bot, since a humand can't see this
		$module .= "<tr><input type='hidden' name='honeypot_check' value='' /></td></tr>";
		
		$module .= "
		</table>
		<p>".__('(* marks a required field)', 'dbem')."</p>
		<p><input type='submit' value='".__('Send your booking', 'dbem')."'/>
		 <input type='hidden' name='eventAction' value='add_booking'/></p>
		 <input type='hidden' name='event_id' value='$event_id'/></p>
	</form>";
	// $module .= "dati inviati: ";
	//  	$module .= dbem_sanitize_request($_POST['bookerName']);
	//print_r($_SERVER);
 
	//$module .= dbem_delete_booking_form();
	 
	return $module;
	
}

function dbem_delete_booking_form() {
	global $form_delete_message;
	
	$dbem_rsvp_registered_users_only=get_option('dbem_rsvp_registered_users_only');
	if ($dbem_rsvp_registered_users_only && !is_user_logged_in()) {
		return;
	}
	$destination = "?".$_SERVER['QUERY_STRING'];
	$module = "<h3>".__('Cancel your booking', 'dbem')."</h3><br/>";
	
	if(!empty($form_delete_message))
		$module .= "<div class='dbem-rsvp-message'>$form_delete_message</div>";

	$module  .= "<form name='booking-delete-form' method='post' action='$destination'>
			<table class='dbem-rsvp-form'>
				<tr><th scope='row'>".__('Name', 'dbem').":</th><td><input type='text' name='bookerName' value=''/></td></tr>
		  	<tr><th scope='row'>".__('E-Mail', 'dbem').":</th><td><input type='text' name='bookerEmail' value=''/></td></tr>
		  	<input type='hidden' name='eventAction' value='delete_booking'/>
		</table>
		<input type='submit' value='".__('Cancel your booking', 'dbem')."'/>
	</form>";
	// $module .= "dati inviati: ";
	//  	$module .= $_POST['bookerName'];

	return $module;
}


function dbem_catch_rsvp() {
	global $current_user;
 	global $form_add_message;
	global $form_delete_message; 
	$result = "";

	if (get_option('dbem_captcha_for_booking')) {
		// the captcha needs a session
		if (!session_id())
			session_start();
	}

	$dbem_rsvp_registered_users_only=get_option('dbem_rsvp_registered_users_only');
	if ($dbem_rsvp_registered_users_only && !is_user_logged_in()) {
		return;
	}

	if (isset($_POST['eventAction']) && $_POST['eventAction'] == 'add_booking') { 
		$result = dbem_book_seats();
		$form_add_message = $result;
  	} 

	if (isset($_POST['eventAction']) && $_POST['eventAction'] == 'delete_booking') { 
		if ($dbem_rsvp_registered_users_only) {
			// we require a user to be WP registered to be able to book
			get_currentuserinfo();
			$booker_wp_id=$current_user->ID;
			$booker = dbem_get_person_by_wp_id($booker_wp_id); 
		} else {
			$bookerName = $_POST['bookerName'];
			$bookerEmail = $_POST['bookerEmail'];
			$booker = dbem_get_person_by_name_and_email($bookerName, $bookerEmail); 
		}
	  	if ($booker) {
			$person_id = $booker['person_id'];
			$result = dbem_delete_booking_by_person_id($person_id);
		} else {
			$result = __('There are no bookings associated to this name and e-mail', 'dbem');
		}
		$form_delete_message = $result; 
  	} 
	return $result;
	
}
add_action('init','dbem_catch_rsvp');
 
function dbem_book_seats() {
	global $current_user;
	$bookerPhone = stripslashes($_POST['bookerPhone']); 
	$bookedSeats = intval($_POST['bookedSeats']);
	$bookerComment = stripslashes($_POST['bookerComment']);
	$honeypot_check = stripslashes($_POST['honeypot_check']);
	$event_id = intval($_POST['event_id']);
	$dbem_rsvp_registered_users_only=get_option('dbem_rsvp_registered_users_only');
	if ($dbem_rsvp_registered_users_only) {
		// we require a user to be WP registered to be able to book
		get_currentuserinfo();
		$booker_wp_id=$current_user->ID;
		// we also need name and email for sending the mail
		$bookerEmail = $current_user->user_email;
		$bookerName = $current_user->display_name;
		$booker = dbem_get_person_by_wp_id($booker_wp_id); 
	} else {
		$booker_wp_id=0;
		$bookerEmail = stripslashes($_POST['bookerEmail']);
		$bookerName = stripslashes($_POST['bookerName']);
		$booker = dbem_get_person_by_name_and_email($bookerName, $bookerEmail); 
	}
	
	$msg="";
	if (get_option('dbem_captcha_for_booking')) {
		$msg = response_check_captcha("captcha_check",1);
	}
  	if(!empty($msg)) {
		$result = __('You entered an incorrect code','dbem');
	} elseif ($honeypot_check != "") {
		// a bot fills this in, but a human never will, since it's
		// a hidden field
		$result = __('You are a bad boy','dbem');
  	} elseif (!$bookerName || !$bookerEmail || !$bookerPhone || !$bookedSeats) {
	// if any of name, email, phone or bookedseats are empty: return an error
		$result = __('Please fill in all the required fields','dbem');
	} else {
	   if ($bookedSeats && dbem_are_seats_available_for($event_id, $bookedSeats)) {
	   	if (!$booker) {
   			$booker = dbem_add_person($bookerName, $bookerEmail, $bookerPhone, $booker_wp_id);
	   	}
		dbem_record_booking($event_id, $booker['person_id'], $bookedSeats,$bookerComment);
		
		$result = __('Your booking has been recorded','dbem');
		$mailing_is_active = get_option('dbem_rsvp_mail_notify_is_active');
		if($mailing_is_active) {
			dbem_email_rsvp_booking($event_id,$bookerName,$bookerEmail,$bookerPhone,$bookedSeats,$bookerComment,"");
		} 
	   } else {
		$result = __('Booking cannot be made: not enough seats available!', 'dbem');
	   }
	}
	return $result;
}

function dbem_get_booking($booking_id) {
	global $wpdb; 
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	$sql = "SELECT * FROM $bookings_table WHERE booking_id = '$booking_id';" ;
	$result = $wpdb->get_row($sql, ARRAY_A);
	return $result;
}

function dbem_get_bookings_by_person_id($person_id) {
	global $wpdb; 
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	$sql = "SELECT * FROM $bookings_table WHERE person_id = '$person_id';" ;
	$result = $wpdb->get_row($sql, ARRAY_A);
	return $result;
}

function dbem_record_booking($event_id, $person_id, $seats, $comment = "") {
	global $wpdb;
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	$event_id = intval($event_id);
	$person_id = intval($person_id);
	$seats = intval($seats);
	$comment = dbem_sanitize_request($comment);
	// checking whether the booker has already booked places
//	$sql = "SELECT * FROM $bookings_table WHERE event_id = '$event_id' and person_id = '$person_id'; ";
//	//echo $sql;
//	$previously_booked = $wpdb->get_row($sql);
//	if ($previously_booked) {
//		$total_booked_seats = $previously_booked->booking_seats + $seats;
//		$where = array();
//		$where['booking_id'] =$previously_booked->booking_id;
//		$fields['booking_seats'] = $total_booked_seats;
//	 	$wpdb->update($bookings_table, $fields, $where);
//	} else {
		$sql = "INSERT INTO $bookings_table (event_id, person_id, booking_seats,booking_comment) VALUES ($event_id, $person_id, $seats,'$comment')";
		$wpdb->query($sql);
//	}
} 
function dbem_delete_booking_by_person_id($person_id) {
	global $wpdb;
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
	$sql = "DELETE FROM $bookings_table WHERE person_id = $person_id";
	$wpdb->query($sql);
	return __('Booking deleted', 'dbem');
}
function dbem_delete_booking($booking_id) {
	global $wpdb;
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
	$sql = "DELETE FROM $bookings_table WHERE booking_id = $booking_id";
	$wpdb->query($sql);
	return __('Booking deleted', 'dbem');
}
function dbem_approve_booking($booking_id) {
	global $wpdb;
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
	$sql = "UPDATE $bookings_table SET booking_approved='1' WHERE booking_id = $booking_id";
	$wpdb->query($sql);
	return __('Booking approved', 'dbem');
}
function dbem_update_booking_seats($booking_id,$seats) {
	global $wpdb;
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
	$sql = "UPDATE $bookings_table SET booking_seats='$seats' WHERE booking_id = $booking_id";
	$wpdb->query($sql);
	return __('Booking approved', 'dbem');
}

function dbem_get_available_seats($event_id) {
	global $wpdb; 
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	$sql = "SELECT SUM(booking_seats) AS booked_seats FROM $bookings_table WHERE event_id = $event_id"; 
	$seats_row = $wpdb->get_row($sql, ARRAY_A);
	$booked_seats = $seats_row['booked_seats'];
	$event = dbem_get_event($event_id);
	$available_seats = $event['event_seats'] - $booked_seats;
	return ($available_seats);
}
function dbem_get_booked_seats($event_id) {
	global $wpdb; 
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	$sql = "SELECT SUM(booking_seats) AS booked_seats FROM $bookings_table WHERE event_id = $event_id"; 
	$seats_row = $wpdb->get_row($sql, ARRAY_A);
	$booked_seats = $seats_row['booked_seats'];
	return $booked_seats;
}
function dbem_are_seats_available_for($event_id, $seats) {
	$event = dbem_get_event($event_id);
	$available_seats = dbem_get_available_seats($event_id);
	$remaning_seats = $available_seats - $seats;
	return ($remaning_seats >= 0);
} 
 
function dbem_bookings_table($event_id) {
	$bookings =  dbem_get_bookings_for($event_id);
	$destination = admin_url("edit.php"); 
	$result = "<form id='bookings-filter' method='get' action='$destination'>
						<input type='hidden' name='page' value='events-manager'/>
						<input type='hidden' name='action' value='edit_event'/>
						<input type='hidden' name='event_id' value='$event_id'/>
						<input type='hidden' name='secondaryAction' value='delete_bookings'/>
						<div class='wrap'>
							<h2>Bookings</h2>
						<table id='dbem-bookings-table' class='widefat post fixed'>";
	$result .="<thead>
							<tr><th class='manage-column column-cb check-column' scope='col'>&nbsp;</th><th class='manage-column ' scope='col'>Booker</th><th scope='col'>E-mail</th><th scope='col'>Phone number</th><th scope='col'>Seats</th></tr>
						</thead>" ;
	foreach ($bookings as $booking) {
		$result .= "<tr> <td><input type='checkbox' value='".$booking['booking_id']."' name='bookings[]'/></td>
										<td>".htmlspecialchars($booking['person_name'])."</td>
										<td>".htmlspecialchars($booking['person_email'])."</td>
										<td>".htmlspecialchars($booking['person_phone'])."</td>
										<td>".$booking['booking_seats']."</td></tr>";
	}
	$available_seats = dbem_get_available_seats($event_id);
	$booked_seats = dbem_get_booked_seats($event_id);
	$result .= "<tfoot><tr><th scope='row' colspan='4'>".__('Booked spaces','dbem').":</th><td class='booking-result' id='booked-seats'>$booked_seats</td></tr>
						 <tr><th scope='row' colspan='4'>".__('Available spaces','dbem').":</th><td class='booking-result' id='available-seats'>$available_seats</td></tr></tfoot>
							</table></div>
							<div class='tablenav'>
								<div class='alignleft actions'>
								 <input class=button-secondary action' type='submit' name='doaction2' value='Delete'/>
									<br class='clear'/>
								</div>
								<br class='clear'/>
						 	</div>
						</form>";
	echo $result;
}

function dbem_bookings_compact_table($event_id) {
	$bookings =  dbem_get_bookings_for($event_id);
	$destination = admin_url("edit.php"); 
	$available_seats = dbem_get_available_seats($event_id);
	$booked_seats = dbem_get_booked_seats($event_id);
	$printable_address = admin_url("/admin.php?page=events-manager-people&action=printable&event_id=$event_id");
	$count_respondents=count($bookings);
	if ($count_respondents>0) { 
		$table = 
		"<div class='wrap'>
				<h4>$count_respondents ".__('respondents so far').":</h4>
				<table id='dbem-bookings-table-$event_id' class='widefat post fixed'>
					<thead>
						<tr>
							<th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>
							<th class='manage-column ' scope='col'>".__('Respondent', 'dbem')."</th>
							<th scope='col'>".__('Spaces', 'dbem')."</th>
					 	</tr>
					</thead>
					<tfoot>
						<tr>
							<th scope='row' colspan='2'>".__('Booked spaces','dbem').":</th><td class='booking-result' id='booked-seats'>$booked_seats</td></tr>
			 			<tr><th scope='row' colspan='2'>".__('Available spaces','dbem').":</th><td class='booking-result' id='available-seats'>$available_seats</td>
						</tr>
					</tfoot>
					<tbody>" ;
		foreach ($bookings as $booking) {
			($booking['booking_comment']) ? $baloon = " <img src='".DBEM_PLUGIN_URL."images/baloon.png' title='".__('Comment:','dbem')." ".$booking['booking_comment']."' alt='comment'/>" : $baloon = "";
			$pending_string="";
			if (dbem_event_needs_approval($event_id) && !$booking['booking_approved']) {
				$pending_string=__('(pending)','dbem');
			}
			$table .= 
			"<tr id='booking-".$booking['booking_id']."'> 
				<td><a id='booking-check-".$booking['booking_id']."' class='bookingdelbutton'>X</a></td>
				<td><a title=\"".htmlspecialchars($booking['person_email'])." - ".htmlspecialchars($booking['person_phone'])."\">".htmlspecialchars($booking['person_name'])."</a>$baloon</td>
				<td>".$booking['booking_seats']." $pending_string </td>
			 </tr>";
		}
	 
		$table .=  "</tbody>
	 		</table>
	 		</div>
			<br class='clear'/>
		 	<div id='major-publishing-actions'>
			<div id='publishing-action'> 
				<a id='printable'  target='' href='$printable_address'>".__('Printable view','dbem')."</a>
				<br class='clear'/>
		 	</div>
			<br class='clear'/>
		 	</div> ";
	} else {
		$table = "<p><em>".__('No responses yet!')."</em></p>";
	} 
	echo $table;
}

function dbem_get_bookings_for($event_ids,$pending=0) {
	global $wpdb; 
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	
	$booking_data = array();
	if (!$event_ids)
		return $booking_data;
	
	if (is_array($event_ids)) {
		$where="event_id IN (".join(",",$event_ids).")";
	} else {
		$where="event_id = $event_ids";
	}
	if ($pending) {
		$sql = "SELECT * FROM $bookings_table WHERE $where AND booking_approved=0";
	} else {
		$sql = "SELECT * FROM $bookings_table WHERE $where";
	}
	$bookings = $wpdb->get_results($sql, ARRAY_A);
	if ($bookings) {
		foreach ($bookings as $booking) {
			$person = dbem_get_person($booking['person_id']);
			$booking['person_name'] = $person['person_name']; 
			$booking['person_email'] = $person['person_email'];
			$booking['person_phone'] = $person['person_phone'];
			array_push($booking_data, $booking);
		}
	}
 	return $booking_data;
}

function dbem_get_bookings_list_for($event_id) {
	global $wpdb; 
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	$sql = "SELECT DISTINCT person_id FROM $bookings_table WHERE event_id = $event_id";
	$persons = $wpdb->get_results($sql, ARRAY_A);
	if ($persons) {
		$res="<ul class='dbem_bookings_list_ul'>";
		foreach ($persons as $person) {
			$attendee=dbem_get_person($person['person_id']);
			$res.="<li class='dbem_bookings_list_li'>".$attendee['person_name']."</li>";
		}
		$res.="</ul>";
	} else {
		$res="<p class='dbem_no_bookings'>".__('No responses yet!')."</p>";
	}
	return $res;
}

function dbem_intercept_bookings_delete() {
	//dbem_email_rsvp_booking();
	if(isset($_GET['bookings']))
		$bookings = $_GET['bookings'];
	
	if (isset($bookings)) {
		foreach($bookings as $booking_id) {
			dbem_delete_booking(intval($booking_id));
		}
	}
}
add_action('init', 'dbem_intercept_bookings_delete');

function dbem_email_rsvp_booking($event_id,$bookerName,$bookerEmail,$bookerPhone,$bookedSeats,$bookerComment,$action="") {

	$event = dbem_get_event($event_id);
	if($event['event_contactperson_id'] && $event['event_contactperson_id']>0) 
		$contact_id = $event['event_contactperson_id']; 
	else
		$contact_id = get_option('dbem_default_contact_person');

	$contact_name = dbem_get_user_name($contact_id);
	$contact_email = dbem_get_user_email($contact_id);
 	
	$contact_body = ( $event['event_contactperson_email_body'] != '' ) ? $event['event_contactperson_email_body'] : get_option ( 'dbem_contactperson_email_body' );
	$contact_body = dbem_replace_placeholders($contact_body, $event, "text");
	$booker_body = ( $event['event_respondent_email_body'] != '' ) ? $event['event_respondent_email_body'] : get_option ( 'dbem_respondent_email_body' );
	$booker_body = dbem_replace_placeholders($booker_body, $event, "text");
	$pending_body = get_option ( 'dbem_registration_pending_email_body' );
	$pending_body = dbem_replace_placeholders($pending_body, $event, "text");
	$denied_body = get_option ( 'dbem_registration_denied_email_body' );
	$denied_body = dbem_replace_placeholders($denied_body, $event, "text");
	
	// rsvp specific placeholders
	$placeholders = array('#_CONTACTPERSON'=> $contact_name, '#_PLAIN_CONTACTEMAIL'=> $contact_email, '#_RESPNAME' => $bookerName, '#_RESPEMAIL' => $bookerEmail, '#_RESPPHONE' => $bookerPhone, '#_SPACES' => $bookedSeats,'#_COMMENT' => $bookerComment );

  	foreach($placeholders as $key => $value) {
		$contact_body = str_replace($key, $value, $contact_body);
		$booker_body = str_replace($key, $value, $booker_body);
		$pending_body = str_replace($key, $value, $pending_body);
		$denied_body = str_replace($key, $value, $denied_body);
	}

	if($action!="") {
		if ($action == 'approveRegistration') {
			dbem_send_mail(__('Reservation confirmed','dbem'),$booker_body, $bookerEmail);
		} elseif ($action == 'denyRegistration') {
			dbem_send_mail(__('Reservation denied','dbem'),$denied_body, $bookerEmail);
		}
	} else {
		// send different mails depending on approval or not
		if ($event['registration_requires_approval']) {
			dbem_send_mail(__("Approval required for new booking",'dbem'), $contact_body, $contact_email);
			dbem_send_mail(__('Reservation pending','dbem'),$pending_body, $bookerEmail);
		} else {
			dbem_send_mail(__("New booking",'dbem'), $contact_body, $contact_email);
			dbem_send_mail(__('Reservation confirmed','dbem'),$booker_body, $bookerEmail);
		}
	}
} 

function dbem_registration_seats_page() {
        global $wpdb;

	// do the actions if required
        $action = isset($_POST ['action']) ? $_POST ['action'] : '';
	$bookings = isset($_POST ['bookings']) ? $_POST ['bookings'] : array();
	$bookings_seats = isset($_POST ['bookings_seats']) ? $_POST ['bookings_seats'] : array();
	foreach ( $bookings as $key=>$booking_id ) {
		// make sure the seats are integers
		$bookings_seats[$key]=intval($bookings_seats[$key]);
		$booking = dbem_get_booking ($booking_id);
		$person  = dbem_get_person ($booking['person_id']);
		// 0 seats is not possible, then you should remove the booking
		if ($bookings_seats[$key]==0)
			$bookings_seats[$key]=1;
		if ($action == 'approveRegistration' && $booking['booking_seats']!= $bookings_seats[$key]) {
			dbem_update_booking_seats($booking_id,$bookings_seats[$key]);
			dbem_email_rsvp_booking($booking['event_id'],$person['person_name'],$person['person_email'],$person['person_phone'],$bookings_seats[$key],$booking['booking_comment'],$action);
		} elseif ($action == 'denyRegistration') {
			dbem_delete_booking($booking_id);
			dbem_email_rsvp_booking($booking['event_id'],$person['person_name'],$person['person_email'],$person['person_phone'],$bookings_seats[$key],$booking['booking_comment'],$action);
		}
	}
	
	// now show the menu
	$event_id = isset($_POST ['event_id']) ? intval($_POST ['event_id']) : 0;
	dbem_registration_seats_form_table($event_id);
}

function dbem_registration_seats_form_table($event_id=0) {
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php _e ('Change reserved spaces or cancel registrations','dbem'); ?></h2>
<?php admin_show_warnings();?>
  	<form id="posts-filter" action="" method="post">
	<input type='hidden' name='page' value='events-manager-registration-seats' />
	<div class="tablenav">

	<div class="alignleft actions">
	<select name="action">
	<option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
	<option value="approveRegistration"><?php _e ( 'Update registration','dbem' ); ?></option>
	<option value="denyRegistration"><?php _e ( 'Deny registration','dbem' ); ?></option>
	</select>
	<input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
	<select name="event_id">
	<option value='0'><?php _e ( 'All events' ); ?></option>
	<?php
	$all_events=dbem_get_events(0,"future");
	$events_with_pending_bookings=array();
	foreach ( $all_events as $event ) {
		if (dbem_get_bookings_for($event['event_id'])) {
			$events_with_bookings[]=$event['event_id'];
			$selected = "";
			if ($event_id && ($event['event_id'] == $event_id))
				$selected = "selected='selected'";
			echo "<option value='".$event['event_id']."' $selected>".$event['event_name']."</option>  ";
		}
	}
	?>
	</select>
	<input id="post-query-submit" class="button-secondary" type="submit" value="<?php _e ( 'Filter' )?>" />
	</div>
	<div class="clear"></div>
	<table class="widefat">
	<thead>
		<tr>
			<th class='manage-column column-cb check-column' scope='col'><input
				class='select-all' type="checkbox" value='1' /></th>
			<th><?php _e ( 'Name', 'dbem' ); ?></th>
			<th><?php _e ( 'Date and time', 'dbem' ); ?></th>
			<th><?php _e ('Booker','dbem'); ?></th>
			<th><?php _e ('Seats','dbem'); ?></th>
		</tr>
	</thead>
	<tbody>
  	  <?php
		$i = 1;
		if ($event_id) {
			$bookings = dbem_get_bookings_for($event_id);
		} else {
			$bookings = dbem_get_bookings_for($events_with_bookings);
		}
		foreach ( $bookings as $event_booking ) {
			$event=dbem_get_event($event_booking['event_id']);
			$class = ($i % 2) ? ' class="alternate"' : '';
			// FIXME set to american
			$localised_start_date = mysql2date ( __ ( 'D d M Y' ), $event['event_start_date'] );
			$localised_end_date = mysql2date ( __ ( 'D d M Y' ), $event['event_end_date'] );
			$style = "";
			$today = date ( "Y-m-d" );
			
			if ($event['event_start_date'] < $today)
				$style = "style ='background-color: #FADDB7;'";
			?>
	  	<tr <?php echo "$class $style"; ?>>
			<td><input type='checkbox' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='bookings[]' /></td>
			<td><strong>
			<a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&action=edit_event&event_id=".$event_booking ['event_id']); ?>"><?php echo ($event ['event_name']); ?></a>
			</strong>
			</td>
			<td>
				<?php echo $localised_start_date; if ($localised_end_date !='') echo " - " . $localised_end_date; ?><br />
				<?php echo substr ( $event['event_start_time'], 0, 5 ) . " - " . substr ( $event['event_end_time'], 0, 5 ); ?>
			</td>
			<td>
				<?php echo $event_booking['person_name'] ."(".$event_booking['person_phone'].", ". $event_booking['person_email'].")";?>
			</td>
			<td>
				<input type="text" name="bookings_seats[]" value="<?php echo $event_booking['booking_seats'];?>" />
			</td>
		</tr>
		<?php
			$i++;
		}
		?>
	</tbody>
	</table>

	<div class='tablenav'>
	<div class="alignleft actions"><br class='clear' />
	</div>
	<br class='clear' />
	</div>

	</div>
	</form>
</div>
<?php
}
function dbem_registration_approval_page() {
        global $wpdb;

	// do the actions if required
        $action = isset($_POST ['action']) ? $_POST ['action'] : '';
	$pending_bookings = isset($_POST ['pending_bookings']) ? $_POST ['pending_bookings'] : array();
	$bookings_seats = isset($_POST ['bookings_seats']) ? $_POST ['bookings_seats'] : array();
	foreach ( $pending_bookings as $key=>$booking_id ) {
		$booking = dbem_get_booking ($booking_id);
		$person  = dbem_get_person ($booking['person_id']);
		// update the db
		if ($action == 'approveRegistration') {
			dbem_approve_booking($booking_id);
			// 0 seats is not possible, then you should remove the booking
			if ($bookings_seats[$key]==0)
				$bookings_seats[$key]=1;
			if ($booking['booking_seats']!= intval($bookings_seats[$key]))
				dbem_update_booking_seats($booking_id,intval($bookings_seats[$key]));
		} elseif ($action == 'denyRegistration') {
			dbem_delete_booking($booking_id);
		}
		// and then send the mail
		dbem_email_rsvp_booking($booking['event_id'],$person['person_name'],$person['person_email'],$person['person_phone'],$bookings_seats[$key],$booking['booking_comment'],$action);
	}
	// now show the menu
	$event_id = isset($_POST ['event_id']) ? intval($_POST ['event_id']) : 0;
	dbem_registration_approval_form_table($event_id);
}

function dbem_registration_approval_form_table($event_id=0) {
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php _e ('Pending Approvals','dbem'); ?></h2>
<?php admin_show_warnings();?>
  	<form id="posts-filter" action="" method="post">
	<input type='hidden' name='page' value='events-manager-registration-approval' />
	<div class="tablenav">

	<div class="alignleft actions">
	<select name="action">
	<option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
	<option value="approveRegistration"><?php _e ( 'Approve registration','dbem' ); ?></option>
	<option value="denyRegistration"><?php _e ( 'Deny registration','dbem' ); ?></option>
	</select>
	<input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
	<select name="event_id">
	<option value='0'><?php _e ( 'All events' ); ?></option>
	<?php
	$all_events=dbem_get_events(0,"future");
	$events_with_pending_bookings=array();
	foreach ( $all_events as $event ) {
		if ($event['registration_requires_approval'] && dbem_get_bookings_for($event['event_id'],1)) {
			$events_with_pending_bookings[]=$event['event_id'];
			$selected = "";
			if ($event_id && ($event['event_id'] == $event_id))
				$selected = "selected='selected'";
			echo "<option value='".$event['event_id']."' $selected>".$event['event_name']."</option>  ";
		}
	}
	?>
	</select>
	<input id="post-query-submit" class="button-secondary" type="submit" value="<?php _e ( 'Filter' )?>" />
	</div>
	<div class="clear"></div>
	<table class="widefat">
	<thead>
		<tr>
			<th class='manage-column column-cb check-column' scope='col'><input
				class='select-all' type="checkbox" value='1' /></th>
			<th><?php _e ( 'Name', 'dbem' ); ?></th>
			<th><?php _e ( 'Date and time', 'dbem' ); ?></th>
			<th><?php _e ('Booker','dbem'); ?></th>
			<th><?php _e ('Seats','dbem'); ?></th>
		</tr>
	</thead>
	<tbody>
  	  <?php
		$i = 1;
		if ($event_id) {
			$pending_bookings = dbem_get_bookings_for($event_id,1);
		} else {
			$pending_bookings = dbem_get_bookings_for($events_with_pending_bookings,1);
		}
		foreach ( $pending_bookings as $event_booking ) {
			$event=dbem_get_event($event_booking['event_id']);
			$class = ($i % 2) ? ' class="alternate"' : '';
			// FIXME set to american
			$localised_start_date = mysql2date ( __ ( 'D d M Y' ), $event['event_start_date'] );
			$localised_end_date = mysql2date ( __ ( 'D d M Y' ), $event['event_end_date'] );
			$style = "";
			$today = date ( "Y-m-d" );
			
			if ($event['event_start_date'] < $today)
				$style = "style ='background-color: #FADDB7;'";
			?>
	  	<tr <?php echo "$class $style"; ?>>
			<td><input type='checkbox' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='pending_bookings[]' /></td>
			<td><strong>
			<a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&action=edit_event&event_id=".$event_booking ['event_id']); ?>"><?php echo ($event ['event_name']); ?></a>
			</strong>
			</td>
			<td>
				<?php echo $localised_start_date; if ($localised_end_date !='') echo " - " . $localised_end_date; ?><br />
				<?php echo substr ( $event['event_start_time'], 0, 5 ) . " - " . substr ( $event['event_end_time'], 0, 5 ); ?>
			</td>
			<td>
				<?php echo $event_booking['person_name'] ."(".$event_booking['person_phone'].", ". $event_booking['person_email'].")";?>
			</td>
			<td>
				<input type="text" name="bookings_seats[]" value="<?php echo $event_booking['booking_seats'];?>" />
			</td>
		</tr>
		<?php
			$i++;
		}
		?>
	</tbody>
	</table>

	<div class='tablenav'>
	<div class="alignleft actions"><br class='clear' />
	</div>
	<br class='clear' />
	</div>

	</div>
	</form>
</div>
<?php
}

function dbem_get_user_email($user_id) {
	global $wpdb;
	$sql = "SELECT user_email FROM $wpdb->users WHERE ID = $user_id"; 
	return $wpdb->get_var( $wpdb->prepare($sql) );
}
function dbem_get_user_name($user_id) {
	global $wpdb;
	$sql = "SELECT display_name FROM $wpdb->users WHERE ID = $user_id"; 
 	return $wpdb->get_var( $wpdb->prepare($sql) );
}
function dbem_get_user_phone($user_id) {
	return get_usermeta($user_id, 'dbem_phone');
}

// got from http://davidwalsh.name/php-email-encode-prevent-spam
function dbem_ascii_encode($e) {
    $output = "";
    for ($i = 0; $i < strlen($e); $i++) { $output .= '&#'.ord($e[$i]).';'; }
    return $output;
}

function dbem_event_needs_approval($event_id) {
	global $wpdb;
	$events_table = $wpdb->prefix . EVENTS_TBNAME;
	$sql = "SELECT registration_requires_approval from $events_table where event_id=$event_id";
	return $wpdb->get_var( $wpdb->prepare($sql) );
}

?>
