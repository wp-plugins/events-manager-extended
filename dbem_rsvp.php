<?php
$form_add_message = "";        
$form_delete_message = "";
function dbem_add_booking_form($event_id) {                
	global $form_add_message;
	$base_url = get_bloginfo ( 'wpurl' );
	//$message = dbem_catch_rsvp();
 
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
				<tr><th scope='row'>".__('Name', 'dbem')."*:</th><td><input type='text' name='bookerName' value=''/></td></tr>
				<tr><th scope='row'>".__('E-Mail', 'dbem')."*:</th><td><input type='text' name='bookerEmail' value=''/></td></tr>
				<tr><th scope='row'>".__('Phone number', 'dbem')."*:</th><td><input type='text' name='bookerPhone' value=''/></td></tr>
				<tr><th scope='row'>".__('Seats', 'dbem')."*:</th><td><select name='bookedSeats' >";
		foreach($booked_places_options as $option) {
			$module .= $option."\n";                  
		}
		$module .= "</select></td></tr>
				<tr><th scope='row'>".__('Comment', 'dbem').":</th><td><textarea name='bookerComment'></textarea></td></tr>
				<tr><th scope='row'>".__('Please fill in the code displayed here', 'dbem').":</th><td><img src='$base_url/wp-content/plugins/events-manager-extended/captcha.php'><br>
				      <input type='text' name='captcha_check'></td></tr>
		</table>
		<p>".__('(* marks a required field)', 'dbem')."</p>   
		<p><input type='submit' value='".__('Send your booking', 'dbem')."'/>   
		 <input type='hidden' name='eventAction' value='add_booking'/></p>  
	</form>";   
	// $module .= "dati inviati: ";
	//  	$module .= dbem_sanitize_request($_POST['bookerName']);  
	//print_r($_SERVER);
 
	//$module .= dbem_delete_booking_form();
	 
	return $module;
	
}

function dbem_delete_booking_form() {                
	global $form_delete_message;
	
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
  	global $form_add_message;   
	global $form_delete_message; 
	$result = "";
	if (isset($_POST['eventAction']) && $_POST['eventAction'] == 'add_booking') { 
		$result = dbem_book_seats();
		$form_add_message = $result;
	  
		
  	} 

	if (isset($_POST['eventAction']) && $_POST['eventAction'] == 'delete_booking') { 
		
		$bookerName = dbem_sanitize_request($_POST['bookerName']);
		$bookerEmail = dbem_sanitize_request($_POST['bookerEmail']);
		$booker = dbem_get_person_by_name_and_email($bookerName, $bookerEmail); 
	  	if ($booker) {
			$booker_id = $booker['person_id'];
			$booking = dbem_get_booking_by_person_id($booker_id);
			$result = dbem_delete_booking($booking['booking_id']);
		} else {
			$result = __('There are no bookings associated to this name and e-mail', 'dbem');
		}
		$form_delete_message = $result; 
  	} 
	
	return $result;
	
}   
add_action('init','dbem_catch_rsvp');  
 
function dbem_book_seats() {
	$bookerName = dbem_sanitize_request($_POST['bookerName']);
	$bookerEmail = dbem_sanitize_request($_POST['bookerEmail']);
	$bookerPhone = dbem_sanitize_request($_POST['bookerPhone']); 
	$bookedSeats = intval($_POST['bookedSeats']);
	$bookerComment = dbem_sanitize_request($_POST['bookerComment']);   
	$event_id = intval($_GET['event_id']);
	$booker = dbem_get_person_by_name_and_email($bookerName, $bookerEmail); 
	
	// if any of name, email, phone or bookedseats are empty: return an error
	$msg = response_check_captcha("captcha_check",1);
  	if(!empty($msg)) {
		$result = __('You entered an incorrect code','dbem');  
  	} elseif (!$bookerName || !$bookerEmail || !$bookerPhone || !$bookedSeats) {
		$result = __('Please fill in all the required fields','dbem');  
	} else {
	   if (!$booker) {
   		$booker = dbem_add_person($bookerName, $bookerEmail, $bookerPhone);
	   }
	   if ($bookedSeats && dbem_are_seats_available_for($event_id, $bookedSeats)) {  
		dbem_record_booking($event_id, $booker['person_id'], $bookedSeats,$bookerComment);
		
		$result = __('Your booking has been recorded','dbem');  
		$mailing_is_active = get_option('dbem_rsvp_mail_notify_is_active');
		if($mailing_is_active) {
			dbem_email_rsvp_booking();
		} 
	   }	else {
		 $result = __('Booking cannot be made – not enough seats available!', 'dbem');
	   }  
	}  
	return $result;
}

function dbem_get_booking_by_person_id($person_id) {
	global $wpdb; 
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	$sql = "SELECT * FROM $bookings_table WHERE person_id = '$person_id';" ;
	$result = $wpdb->get_row($sql, ARRAY_A);
	return $result;
}

function dbem_record_booking($event_id, $person_id, $seats, $comment = "") {
	global $wpdb;        
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	// checking whether the booker has already booked places
	$sql = "SELECT * FROM $bookings_table WHERE event_id = '$event_id' and person_id = '$person_id'; ";       
	//echo $sql;
	$previously_booked = $wpdb->get_row($sql);
	if ($previously_booked) {  
		  
		$total_booked_seats = $previously_booked->booking_seats + $seats;
		$where = array();
		$where['booking_id'] =$previously_booked->booking_id;
		$fields['booking_seats'] = $total_booked_seats;
	 	$wpdb->update($bookings_table, $fields, $where);
		
	} else {
		if(true) {
			$sql = "INSERT INTO $bookings_table (event_id, person_id, booking_seats,booking_comment) VALUES ($event_id, $person_id, $seats,'$comment')";  
			$wpdb->query($sql);
		}  
	}
} 
function dbem_delete_booking($booking_id) {
	global $wpdb;
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
	$sql = "DELETE FROM $bookings_table WHERE booking_id = $booking_id";
	$wpdb->query($sql);   
	return __('Booking deleted', 'dbem');
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
	$destination = get_bloginfo('wpurl')."/wp-admin/edit.php"; 
	$table = "<form id='bookings-filter' method='get' action='$destination'>
						<input type='hidden' name='page' value='events-manager'/>
						<input type='hidden' name='action' value='edit_event'/>
						<input type='hidden' name='event_id' value='$event_id'/>
						<input type='hidden' name='secondaryAction' value='delete_bookings'/>
						<div class='wrap'>
							<h2>Bookings</h2>\n
						<table id='dbem-bookings-table' class='widefat post fixed'>\n";
	$table .="<thead>\n
							<tr><th class='manage-column column-cb check-column' scope='col'>&nbsp;</th><th class='manage-column ' scope='col'>Booker</th><th scope='col'>E-mail</th><th scope='col'>Phone number</th><th scope='col'>Seats</th></tr>\n
						</thead>\n" ;
	foreach ($bookings as $booking) {
		$table .= "<tr> <td><input type='checkbox' value='".$booking['booking_id']."' name='bookings[]'/></td>
										<td>".$booking['person_name']."</td>
										<td>".$booking['person_email']."</td>
										<td>".$booking['person_phone']."</td>
										<td>".$booking['booking_seats']."</td></tr>";
	}
	$available_seats = dbem_get_available_seats($event_id);
	$booked_seats = dbem_get_booked_seats($event_id);
	$table .= "<tfoot><tr><th scope='row' colspan='4'>Booked seats:</th><td class='booking-result' id='booked-seats'>$booked_seats</td></tr>            
						 <tr><th scope='row' colspan='4'>Available seats:</th><td class='booking-result' id='available-seats'>$available_seats</td></tr></tfoot>
							</table></div>
							<div class='tablenav'>
								<div class='alignleft actions'>
								 <input class=button-secondary action' type='submit' name='doaction2' value='Delete'/>
									<br class='clear'/>
								</div>
								<br class='clear'/>
						 	</div>

						</form>";    
  echo $table;
}

function dbem_bookings_compact_table($event_id) {
	$bookings =  dbem_get_bookings_for($event_id);
	$destination = get_bloginfo('wpurl')."/wp-admin/edit.php"; 
	$available_seats = dbem_get_available_seats($event_id);
	$booked_seats = dbem_get_booked_seats($event_id);   
	$printable_address = get_bloginfo('wpurl')."/wp-admin/admin.php?page=events-manager-people&action=printable&event_id=$event_id";
	$count_respondents=count($bookings);
	if ($count_respondents>0) { 
		$table = 
		"<div class='wrap'>
				<h4>$count_respondents ".__('respondents so far').":</h4>\n  
			  
				<table id='dbem-bookings-table-$event_id' class='widefat post fixed'>\n
					<thead>\n
						<tr>
							<th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>\n
							<th class='manage-column ' scope='col'>".__('Respondent', 'dbem')."</th>\n
							<th scope='col'>".__('Spaces', 'dbem')."</th>\n
					 	</tr>\n
						</thead>\n
						<tfoot>
							<tr>
								<th scope='row' colspan='2'>".__('Booked spaces','dbem').":</th><td class='booking-result' id='booked-seats'>$booked_seats</td></tr>            
					 			<tr><th scope='row' colspan='2'>".__('Available spaces','dbem').":</th><td class='booking-result' id='available-seats'>$available_seats</td>
							</tr>
						</tfoot>
						<tbody>" ;
			foreach ($bookings as $booking) {  
				($booking['booking_comment']) ? $baloon = " <img src='../wp-content/plugins/events-manager-extended/images/baloon.png' title='".__('Comment:','dbem')." ".$booking['booking_comment']."' alt='comment'/>" : $baloon = "";  
				$table .= 
				"<tr id='booking-".$booking['booking_id']."'> 
					<td><a id='booking-check-".$booking['booking_id']."' class='bookingdelbutton'>X</a></td>
					<td><a title='".$booking['person_email']." - ".$booking['person_phone']."'>".$booking['person_name']."</a>$baloon</td>
					<td>".$booking['booking_seats']."</td>
				 </tr>";
			}
	 
			$table .=  "</tbody>\n
									
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
			$table .= "<p><em>".__('No responses yet!')."</em></p>";
		 } 
		    
  echo $table;
}

function dbem_get_bookings_for($event_id) {  
	global $wpdb; 
	$bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
	$sql = "SELECT * FROM $bookings_table WHERE event_id = $event_id";
	$bookings = $wpdb->get_results($sql, ARRAY_A);  
	$booking_data = array();
	if ($bookings) {
		foreach ($bookings as $booking) {  
			$booking;
			$person = dbem_get_person($booking['person_id']);
			$booking['person_name'] = $person['person_name']; 
			$booking['person_email'] = $person['person_email'];   
			$booking['person_phone'] = $person['person_phone'];
			array_push($booking_data, $booking);
		}
 		return $booking_data;
  } else {
	return null;
	}
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

function dbem_email_rsvp_booking(){  
	$booker = array();
	$bookerName = dbem_sanitize_request($_POST['bookerName']);
	$bookerEmail = dbem_sanitize_request($_POST['bookerEmail']);
	$bookerPhone = dbem_sanitize_request($_POST['bookerPhone']);    
	$bookedSeats = intval($_POST['bookedSeats']);
	$bookerComment = dbem_sanitize_request($_POST['bookerComment']);      
	$event_id = intval($_GET['event_id']);

	$event = dbem_get_event($event_id);
	$available_seats = dbem_get_available_seats($event_id);
	$reserved_seats = dbem_get_booked_seats($event_id);

	if($event['event_contactperson_id'] != "") 
		$contact_id = $event['event_contactperson_id']; 
	else
		$contact_id = get_option('dbem_default_contact_person');
  
	$contact_name = dbem_get_user_name($contact_id);      
	$contact_email = dbem_get_user_email($contact_id);
 	
	$tmp_contact_body = ( $event['event_contactperson_email_body'] != '' ) ? $event['event_contactperson_email_body'] : get_option ( 'dbem_contactperson_email_body' );
	$contact_body = dbem_replace_placeholders($tmp_contact_body, $event);
	$tmp_booker_body = ( $event['event_respondent_email_body'] != '' ) ? $event['event_respondent_email_body'] : get_option ( 'dbem_respondent_email_body' );
	$booker_body = dbem_replace_placeholders($tmp_booker_body, $event);
	
	// rsvp specific placeholders
	$placeholders = array('#_CONTACTPERSON'=> $contact_name, '#_PLAIN_CONTACTEMAIL'=> $contact_email, '#_RESPNAME' =>  $bookerName, '#_RESPEMAIL' => $bookerEmail, '#_RESPPHONE' => $bookerPhone,   '#_SPACES' => $bookedSeats,'#_COMMENT' => $bookerComment, '#_RESERVEDSPACES' => $reserved_seats, '#_BOOKEDSEATS' => $reserved_seats, '#_AVAILABLESPACES' => $available_seats, '#_AVAILABLESEATS' => $available_seats);
  
  	foreach($placeholders as $key => $value) {
		$contact_body= str_replace($key, $value, $contact_body);  
		$booker_body= str_replace($key, $value, $booker_body);
	}
	dbem_send_mail(__("New booking",'dbem'), $contact_body, $contact_email);
	dbem_send_mail(__('Reservation confirmed','dbem'),$booker_body, $bookerEmail);

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
function dbem_ascii_encode($e)  
{  
    for ($i = 0; $i < strlen($e); $i++) { $output .= '&#'.ord($e[$i]).';'; }  
    return $output;  
}

function dbem_is_event_rsvpable() {
	if (dbem_is_single_event_page()) {
		$event = dbem_get_event(intval($_GET['event_id']));
		if($event)
			return $event['event_rsvp']; 
	} 
	return 0;
}

?>
