<?php
$form_add_message = "";
$form_delete_message = "";

function eme_add_booking_form($event_id) {
   global $form_add_message, $current_user;

   $bookerName="";
   $bookerEmail="";
   $event = eme_get_event($event_id);
   $eme_rsvp_registered_users_only=get_option('eme_rsvp_registered_users_only');
   if ($eme_rsvp_registered_users_only) {
      $readonly="disabled=\"disabled\"";
      // we require a user to be WP registered to be able to book
      if (!is_user_logged_in()) {
         return;
      } else {
         get_currentuserinfo();
         $bookerName=$current_user->display_name;
         $bookerEmail=$current_user->user_email;
      }
      $bookerPhone_required="";
   } else {
      $readonly="";
      $bookerPhone_required="*";
   }
   $destination = "?".$_SERVER['QUERY_STRING']."#eme-rsvp-message";

   $event_start_datetime = strtotime($event['event_start_date']." ".$event['event_start_time']);
   if (time()+$event['rsvp_number_days']*60*60*24 > $event_start_datetime ) {
      $ret_string = "";
      if(!empty($form_add_message))
         $ret_string .= "<div id='eme-rsvp-message' class='eme-rsvp-message'>$form_add_message</div>";
      return $ret_string."<div class='eme-rsvp-message'>".__('Bookings no longer allowed on this date.', 'eme')."</div>";
   }

   // you can book the available number of seats, with a max of 10 per time
   $max = eme_get_available_seats($event_id);
   if ($max > 10) {
      $max = 10;
   }
   // no seats anymore? No booking form then ...
   if ($max == 0) {
      $ret_string = "";
      if(!empty($form_add_message))
         $ret_string .= "<div id='eme-rsvp-message' class='eme-rsvp-message'>$form_add_message</div>";
       return $ret_string."<div class='eme-rsvp-message'>".__('Bookings no longer possible: no seats available anymore', 'eme')."</div>";
   }

   $module = "";
   if(!empty($form_add_message))
      $module .= "<div id='eme-rsvp-message' class='eme-rsvp-message'>$form_add_message</div>";
   $booked_places_options = array();
   for ( $i = 1; $i <= $max; $i++) 
      array_push($booked_places_options, "<option value='$i'>$i</option>");
   
      $module  .= "<form id='eme-rsvp-form' name='booking-form' method='post' action='$destination'>
         <table class='eme-rsvp-form'>
            <tr><th scope='row'>".__('Name', 'eme')."*:</th><td><input type='text' name='bookerName' value='$bookerName' $readonly /></td></tr>
            <tr><th scope='row'>".__('E-Mail', 'eme')."*:</th><td><input type='text' name='bookerEmail' value='$bookerEmail' $readonly /></td></tr>
            <tr><th scope='row'>".__('Phone number', 'eme')."$bookerPhone_required:</th><td><input type='text' name='bookerPhone' value='' /></td></tr>
            <tr><th scope='row'>".__('Seats', 'eme')."*:</th><td><select name='bookedSeats' >";
      foreach($booked_places_options as $option) {
         $module .= $option."\n";
      }
      $module .= "</select></td></tr>
            <tr><th scope='row'>".__('Comment', 'eme').":</th><td><textarea name='bookerComment'></textarea></td></tr>";
      if (get_option('eme_captcha_for_booking')) {
         $module .= "
            <tr><th scope='row'>".__('Please fill in the code displayed here', 'eme').":</th><td><img src='".EME_PLUGIN_URL."captcha.php'><br>
                  <input type='text' name='captcha_check' /></td></tr>
            ";
      }
      // also add a honeypot field: if it gets completed with data, 
      // it's a bot, since a humand can't see this
      $module .= "<tr><input type='hidden' name='honeypot_check' value='' /></td></tr>";
      
      $module .= "
      </table>
      <p>".__('(* marks a required field)', 'eme')."</p>
      <input type='hidden' name='eme_eventAction' value='add_booking'/>
      <input type='hidden' name='event_id' value='$event_id'/>
      <input type='submit' value='".get_option('eme_rsvp_addbooking_submit_string')."'/>
   </form>";
   // $module .= "dati inviati: ";
   //    $module .= eme_sanitize_request($_POST['bookerName']);
   //print_r($_SERVER);
 
   //$module .= eme_delete_booking_form();
    
   return $module;
   
}

function eme_delete_booking_form($event_id) {
   global $form_delete_message;
   
   $event = eme_get_event($event_id);
   $eme_rsvp_registered_users_only=get_option('eme_rsvp_registered_users_only');
   if ($eme_rsvp_registered_users_only) {
      $readonly="disabled=\"disabled\"";
      // we require a user to be WP registered to be able to book
      if (!is_user_logged_in()) {
         return;
      } else {
         get_currentuserinfo();
         $bookerName=$current_user->display_name;
         $bookerEmail=$current_user->user_email;
      }
   } else {
      $readonly="";
      $bookerName="";
      $bookerEmail="";
   }
   $destination = "?".$_SERVER['QUERY_STRING']."#eme-rsvp-message";
   $module = "<h3>".__('Cancel your booking', 'eme')."</h3><br/>";
   
   $event_start_datetime = strtotime($event['event_start_date']." ".$event['event_start_time']);
   if (time()+$event['rsvp_number_days']*60*60*24 > $event_start_datetime ) {
      $ret_string = "";
      if(!empty($form_delete_message))
         $ret_string .= "<div id='eme-rsvp-message' class='eme-rsvp-message'>$form_delete_message</div>";
      return $ret_string."<div class='eme-rsvp-message'>".__('Bookings no longer allowed on this date.', 'eme')."</div>";
   }

   if(!empty($form_delete_message))
      $module .= "<div class='eme-rsvp-message'>$form_delete_message</div>";

   $module  .= "<form name='booking-delete-form' method='post' action='$destination'>
         <table class='eme-rsvp-form'>
            <tr><th scope='row'>".__('Name', 'eme').":</th><td><input type='text' name='bookerName' value='$bookerName' $readonly /></td></tr>
         <tr><th scope='row'>".__('E-Mail', 'eme').":</th><td><input type='text' name='bookerEmail' value='$bookerEmail' $readonly /></td></tr>
      </table>
      <input type='hidden' name='eme_eventAction' value='delete_booking'/>
      <input type='hidden' name='event_id' value='$event_id'/>
      <input type='submit' value='".get_option('eme_rsvp_delbooking_submit_string')."'/>
   </form>";
   // $module .= "dati inviati: ";
   //    $module .= $_POST['bookerName'];

   return $module;
}

function eme_catch_rsvp() {
   global $current_user;
   global $form_add_message;
   global $form_delete_message; 
   $result = "";

   if (get_option('eme_captcha_for_booking')) {
      // the captcha needs a session
      if (!session_id())
         session_start();
   }

   $event_id = intval($_POST['event_id']);
   $event = eme_get_event($event_id);
   $eme_rsvp_registered_users_only=get_option('eme_rsvp_registered_users_only');
   if ($eme_rsvp_registered_users_only && !is_user_logged_in()) {
      return;
   }

   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'add_booking') { 
      $result = eme_book_seats($event);
      $form_add_message = $result;
   } 

   if (isset($_POST['eme_eventAction']) && $_POST['eme_eventAction'] == 'delete_booking') { 
      if ($eme_rsvp_registered_users_only) {
         // we require a user to be WP registered to be able to book
         get_currentuserinfo();
         $booker_wp_id=$current_user->ID;
         $booker = eme_get_person_by_wp_id($booker_wp_id); 
      } else {
         $bookerName = eme_strip_tags($_POST['bookerName']);
         $bookerEmail = eme_strip_tags($_POST['bookerEmail']);
         $booker = eme_get_person_by_name_and_email($bookerName, $bookerEmail); 
      }
      if ($booker) {
         $person_id = $booker['person_id'];
         $booking = eme_get_booking_by_person_event_id($person_id,$event_id);
         if ( eme_delete_booking_by_person_event_id($person_id,$event_id) === false) {
            $result = __('Booking delete failed', 'eme');
         } else {
            $result = __('Booking deleted', 'eme');
            if($mailing_is_active) {
               eme_email_rsvp_booking($event_id,$bookerName,$bookerEmail,$booker['person_phone'],$booking['booking_seats'],"","cancelRegistration");
            } 
         }
      } else {
         $result = __('There are no bookings associated to this name and e-mail', 'eme');
      }
      $form_delete_message = $result; 
   } 
   return $result;
   
}
add_action('init','eme_catch_rsvp');
 
function eme_book_seats($event) {
   global $current_user;
   $bookedSeats = intval($_POST['bookedSeats']);
   $bookerPhone = eme_strip_tags($_POST['bookerPhone']); 
   $bookerComment = eme_strip_tags($_POST['bookerComment']);
   $honeypot_check = stripslashes($_POST['honeypot_check']);
   $event_id = $event['event_id'];
   $eme_rsvp_registered_users_only=get_option('eme_rsvp_registered_users_only');
   if ($eme_rsvp_registered_users_only) {
      // we require a user to be WP registered to be able to book
      get_currentuserinfo();
      $booker_wp_id=$current_user->ID;
      // we also need name and email for sending the mail
      $bookerEmail = $current_user->user_email;
      $bookerName = $current_user->display_name;
      $booker = eme_get_person_by_wp_id($booker_wp_id); 
   } else {
      $booker_wp_id=0;
      $bookerEmail = eme_strip_tags($_POST['bookerEmail']);
      $bookerName = eme_strip_tags($_POST['bookerName']);
      $booker = eme_get_person_by_name_and_email($bookerName, $bookerEmail); 
   }
   
   $msg="";
   if (get_option('eme_captcha_for_booking')) {
      $msg = response_check_captcha("captcha_check",1);
   }
   if(!empty($msg)) {
      $result = __('You entered an incorrect code','eme');
   } elseif ($honeypot_check != "") {
      // a bot fills this in, but a human never will, since it's
      // a hidden field
      $result = __('You are a bad boy','eme');
   } elseif (!$bookerName || !$bookerEmail || !$bookedSeats) {
      // if any of name, email or bookedseats are empty: return an error
      $result = __('Please fill in all the required fields','eme');
   } elseif (!$eme_rsvp_registered_users_only && !$bookerPhone) {
      // no member of wordpress: we need a phonenumber then
      $result = __('Please fill in all the required fields','eme');
   } else {
      if ($bookedSeats && eme_are_seats_available_for($event_id, $bookedSeats)) {
         if (!$booker) {
            $booker = eme_add_person($bookerName, $bookerEmail, $bookerPhone, $booker_wp_id);
         }
         eme_record_booking($event_id, $booker['person_id'], $bookedSeats,$bookerComment);
      
         $result = __('Your booking has been recorded','eme');
         $mailing_is_active = get_option('eme_rsvp_mail_notify_is_active');
         if($mailing_is_active) {
            eme_email_rsvp_booking($event_id,$bookerName,$bookerEmail,$bookerPhone,$bookedSeats,$bookerComment,"");
         } 
      } else {
         $result = __('Booking cannot be made: not enough seats available!', 'eme');
      }
   }
   return $result;
}

function eme_get_booking($booking_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT * FROM $bookings_table WHERE booking_id = '$booking_id';" ;
   $result = $wpdb->get_row($sql, ARRAY_A);
   return $result;
}

function eme_get_bookings_by_person_id($person_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT * FROM $bookings_table WHERE person_id = '$person_id';" ;
   $result = $wpdb->get_results($sql, ARRAY_A);
   return $result;
}

function eme_get_booking_by_person_event_id($person_id,$event_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "SELECT * FROM $bookings_table WHERE person_id = $person_id AND event_id= $event_id";
   $result = $wpdb->get_row($sql, ARRAY_A);
   return $result;
}

function eme_get_event_ids_by_booker_id($person_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT DISTINCT event_id FROM $bookings_table WHERE person_id = '$person_id';" ;
   $result = $wpdb->get_results($sql);
   return $result;
}

function eme_record_booking($event_id, $person_id, $seats, $comment = "") {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $event_id = intval($event_id);
   $person_id = intval($person_id);
   $seats = intval($seats);
   $comment = eme_sanitize_request($comment);
   $booking['event_id']=$event_id;
   $booking['person_id']=$person_id;
   $booking['booking_seats']=$seats;
   $booking['booking_comment']=$comment;
   // checking whether the booker has already booked places
// $sql = "SELECT * FROM $bookings_table WHERE event_id = '$event_id' and person_id = '$person_id'; ";
// //echo $sql;
// $previously_booked = $wpdb->get_row($sql);
// if ($previously_booked) {
//    $total_booked_seats = $previously_booked->booking_seats + $seats;
//    $where = array();
//    $where['booking_id'] =$previously_booked->booking_id;
//    $fields['booking_seats'] = $total_booked_seats;
//    $wpdb->update($bookings_table, $fields, $where);
// } else {
      //$sql = "INSERT INTO $bookings_table (event_id, person_id, booking_seats,booking_comment) VALUES ($event_id, $person_id, $seats,'$comment')";
      //$wpdb->query($sql);
      if ($wpdb->insert($bookings_table,$booking)) {
         $booking['booking_id'] =$wpdb->insert_id;
         if (has_action('eme_insert_rsvp_action')) do_action('eme_insert_rsvp_action',$booking);
         return $booking['booking_id'];
      } else {
         return false;
      }
// }
} 
function eme_delete_all_bookings_for_person_id($person_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "DELETE FROM $bookings_table WHERE person_id = $person_id";
   $wpdb->query($sql);
   $person = eme_get_person($person_id);
   return 1;
}
function eme_delete_booking_by_person_event_id($person_id,$event_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "DELETE FROM $bookings_table WHERE person_id = $person_id AND event_id= $event_id";
   return $wpdb->query($sql);
}
function eme_delete_booking($booking_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "DELETE FROM $bookings_table WHERE booking_id = $booking_id";
   $wpdb->query($sql);
   return __('Booking deleted', 'eme');
}
function eme_approve_booking($booking_id) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "UPDATE $bookings_table SET booking_approved='1' WHERE booking_id = $booking_id";
   $wpdb->query($sql);
   return __('Booking approved', 'eme');
}
function eme_update_booking_seats($booking_id,$seats) {
   global $wpdb;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME; 
   $sql = "UPDATE $bookings_table SET booking_seats='$seats' WHERE booking_id = $booking_id";
   $wpdb->query($sql);
   return __('Booking approved', 'eme');
}

function eme_get_available_seats($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT SUM(booking_seats) AS booked_seats FROM $bookings_table WHERE event_id = $event_id"; 
   $seats_row = $wpdb->get_row($sql, ARRAY_A);
   $booked_seats = $seats_row['booked_seats'];
   $event = eme_get_event($event_id);
   $available_seats = $event['event_seats'] - $booked_seats;
   return ($available_seats);
}
function eme_get_booked_seats($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT SUM(booking_seats) AS booked_seats FROM $bookings_table WHERE event_id = $event_id"; 
   $seats_row = $wpdb->get_row($sql, ARRAY_A);
   $booked_seats = $seats_row['booked_seats'];
   return $booked_seats;
}
function eme_are_seats_available_for($event_id, $seats) {
   #$event = eme_get_event($event_id);
   $available_seats = eme_get_available_seats($event_id);
   $remaning_seats = $available_seats - $seats;
   return ($remaning_seats >= 0);
} 
 
function eme_bookings_table($event_id) {
   $bookings =  eme_get_bookings_for($event_id);
   $destination = admin_url("edit.php"); 
   $result = "<form id='bookings-filter' method='get' action='$destination'>
                  <input type='hidden' name='page' value='eme_registration_seats_page'/>
                  <input type='hidden' name='event_id' value='$event_id'/>
                  <input type='hidden' name='action' value='delete_bookings'/>
                  <div class='wrap'>
                     <h2>Bookings</h2>
                  <table id='eme-bookings-table' class='widefat post fixed'>";
   $result .="<thead>
                     <tr><th class='manage-column column-cb check-column' scope='col'>&nbsp;</th><th class='manage-column ' scope='col'>Booker</th><th scope='col'>E-mail</th><th scope='col'>Phone number</th><th scope='col'>Seats</th></tr>
                  </thead>" ;
   foreach ($bookings as $booking) {
      $result .= "<tr> <td><input type='checkbox' value='".$booking['booking_id']."' name='bookings[]'/></td>
                              <td>".eme_sanitize_html($booking['person_name'])."</td>
                              <td>".eme_sanitize_html($booking['person_email'])."</td>
                              <td>".eme_sanitize_html($booking['person_phone'])."</td>
                              <td>".$booking['booking_seats']."</td></tr>";
   }
   $available_seats = eme_get_available_seats($event_id);
   $booked_seats = eme_get_booked_seats($event_id);
   $result .= "<tfoot><tr><th scope='row' colspan='4'>".__('Booked spaces','eme').":</th><td class='booking-result' id='booked-seats'>$booked_seats</td></tr>
                   <tr><th scope='row' colspan='4'>".__('Available spaces','eme').":</th><td class='booking-result' id='available-seats'>$available_seats</td></tr></tfoot>
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

function eme_bookings_compact_table($event_id) {
   $bookings =  eme_get_bookings_for($event_id);
   $destination = admin_url("edit.php"); 
   $available_seats = eme_get_available_seats($event_id);
   $booked_seats = eme_get_booked_seats($event_id);
   $printable_address = admin_url("/admin.php?page=events-manager-people&amp;action=printable&amp;event_id=$event_id");
   $count_respondents=count($bookings);
   if ($count_respondents>0) { 
      $table = 
      "<div class='wrap'>
            <h4>$count_respondents ".__('respondents so far').":</h4>
            <table id='eme-bookings-table-$event_id' class='widefat post fixed'>
               <thead>
                  <tr>
                     <th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>
                     <th class='manage-column ' scope='col'>".__('Respondent', 'eme')."</th>
                     <th scope='col'>".__('Spaces', 'eme')."</th>
                  </tr>
               </thead>
               <tfoot>
                  <tr>
                     <th scope='row' colspan='2'>".__('Booked spaces','eme').":</th><td class='booking-result' id='booked-seats'>$booked_seats</td></tr>
                  <tr><th scope='row' colspan='2'>".__('Available spaces','eme').":</th><td class='booking-result' id='available-seats'>$available_seats</td>
                  </tr>
               </tfoot>
               <tbody>" ;
      foreach ($bookings as $booking) {
         ($booking['booking_comment']) ? $baloon = " <img src='".EME_PLUGIN_URL."images/baloon.png' title='".__('Comment:','eme')." ".$booking['booking_comment']."' alt='comment'/>" : $baloon = "";
         $pending_string="";
         if (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
            $pending_string=__('(pending)','eme');
         }
         $table .= 
         "<tr id='booking-".$booking['booking_id']."'> 
            <td><a id='booking-check-".$booking['booking_id']."' class='bookingdelbutton'>X</a></td>
            <td><a title=\"".eme_sanitize_html($booking['person_email'])." - ".eme_sanitize_html($booking['person_phone'])."\">".eme_sanitize_html($booking['person_name'])."</a>$baloon</td>
            <td>".$booking['booking_seats']." $pending_string </td>
          </tr>";
      }
    
      $table .=  "</tbody>
         </table>
         </div>
         <br class='clear'/>
         <div id='major-publishing-actions'>
         <div id='publishing-action'> 
            <a id='printable'  target='' href='$printable_address'>".__('Printable view','eme')."</a>
            <br class='clear'/>
         </div>
         <br class='clear'/>
         </div> ";
   } else {
      $table = "<p><em>".__('No responses yet!','eme')."</em></p>";
   } 
   echo $table;
}

function eme_get_bookings_for($event_ids,$pending=0) {
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
         $person = eme_get_person($booking['person_id']);
         $booking['person_name'] = $person['person_name']; 
         $booking['person_email'] = $person['person_email'];
         $booking['person_phone'] = $person['person_phone'];
         array_push($booking_data, $booking);
      }
   }
   return $booking_data;
}

function eme_get_bookings_list_for($event_id) {
   global $wpdb; 
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $sql = "SELECT DISTINCT person_id FROM $bookings_table WHERE event_id = $event_id";
   $persons = $wpdb->get_results($sql, ARRAY_A);
   if ($persons) {
      $res="<ul class='eme_bookings_list_ul'>";
      foreach ($persons as $person) {
         $attendee=eme_get_person($person['person_id']);
         $res.=eme_replace_attendees_placeholders(get_option('eme_attendees_list_format'),$attendee);
      }
      $res.="</ul>";
   } else {
      $res="<p class='eme_no_bookings'>".__('No responses yet!','eme')."</p>";
   }
   return $res;
}

function eme_replace_attendees_placeholders($format, $attendee, $target="html") {
   $attendee_string = $format;
   preg_match_all("/#@?_?[A-Za-z]+/", $format, $placeholders);
   foreach($placeholders[0] as $result) {
      if (preg_match('/#_(NAME|PHONE|ID|EMAIL)$/', $result)) {
         $field = "person_".ltrim(strtolower($result), "#_");
         $field_value = $attendee[$field];
         $field_value = eme_sanitize_html($field_value);
         if ($target == "html")
            $field_value = apply_filters('eme_general', $field_value); 
         else 
            $field_value = apply_filters('eme_general_rss', $field_value); 
         $attendee_string = str_replace($result, $field_value , $attendee_string ); 
      }
   }
   return $attendee_string;   
}

function eme_email_rsvp_booking($event_id,$bookerName,$bookerEmail,$bookerPhone,$bookedSeats,$bookerComment,$action="") {
   $event = eme_get_event($event_id);
   if($event['event_contactperson_id'] && $event['event_contactperson_id']>0) 
      $contact_id = $event['event_contactperson_id']; 
   else
      $contact_id = $event['event_author']; 
      //$contact_id = get_option('eme_default_contact_person');

   $contact_name = eme_get_user_name($contact_id);
   $contact_email = eme_get_user_email($contact_id);
   
   $contact_body = ( $event['event_contactperson_email_body'] != '' ) ? $event['event_contactperson_email_body'] : get_option('eme_contactperson_email_body' );
   $contact_body = eme_replace_placeholders($contact_body, $event, "text");
   $booker_body = ( $event['event_respondent_email_body'] != '' ) ? $event['event_respondent_email_body'] : get_option('eme_respondent_email_body' );
   $booker_body = eme_replace_placeholders($booker_body, $event, "text");
   $pending_body = get_option('eme_registration_pending_email_body' );
   $pending_body = eme_replace_placeholders($pending_body, $event, "text");
   $denied_body = get_option('eme_registration_denied_email_body' );
   $denied_body = eme_replace_placeholders($denied_body, $event, "text");
   $cancelled_body = get_option('eme_registration_cancelled_email_body' );
   $cancelled_body = eme_replace_placeholders($cancelled_body, $event, "text");
   
   // rsvp specific placeholders
   $placeholders = array('#_CONTACTPERSON'=> $contact_name, '#_PLAIN_CONTACTEMAIL'=> $contact_email, '#_RESPNAME' => $bookerName, '#_RESPEMAIL' => $bookerEmail, '#_RESPPHONE' => $bookerPhone, '#_SPACES' => $bookedSeats,'#_COMMENT' => $bookerComment );

   foreach($placeholders as $key => $value) {
      $contact_body = str_replace($key, $value, $contact_body);
      $booker_body = str_replace($key, $value, $booker_body);
      $pending_body = str_replace($key, $value, $pending_body);
      $denied_body = str_replace($key, $value, $denied_body);
      $cancelled_body = str_replace($key, $value, $cancelled_body);
   }

   if($action!="") {
      if ($action == 'approveRegistration') {
         eme_send_mail(__('Reservation confirmed','eme'),$booker_body, $bookerEmail);
      } elseif ($action == 'denyRegistration') {
         eme_send_mail(__('Reservation denied','eme'),$denied_body, $bookerEmail);
      } elseif ($action == 'cancelRegistration') {
         eme_send_mail(__('Reservation cancelled','eme'),$cancelled_body, $bookerEmail);
         eme_send_mail(__('A reservation has been cancelled','eme'), $contact_body, $contact_email);
      }
   } else {
      // send different mails depending on approval or not
      if ($event['registration_requires_approval']) {
         eme_send_mail(__("Approval required for new booking",'eme'), $contact_body, $contact_email);
         eme_send_mail(__('Reservation pending','eme'),$pending_body, $bookerEmail);
      } else {
         eme_send_mail(__("New booking",'eme'), $contact_body, $contact_email);
         eme_send_mail(__('Reservation confirmed','eme'),$booker_body, $bookerEmail);
      }
   }
} 

function eme_registration_seats_page() {
        global $wpdb;

   if (current_user_can( EDIT_CAPABILITY)) {
      // do the actions if required
      if (isset($_GET['action']) && $_GET['action'] == "delete_bookings" && isset($_GET['bookings'])) {
         $bookings = $_GET['bookings'];
         if (is_array($bookings)) {
            foreach($bookings as $booking_id) {
               eme_delete_booking(intval($booking_id));
            }
         }
      } else {
               $action = isset($_POST ['action']) ? $_POST ['action'] : '';
         $bookings = isset($_POST ['bookings']) ? $_POST ['bookings'] : array();
         $selected_bookings = isset($_POST ['selected_bookings']) ? $_POST ['selected_bookings'] : array();
         $bookings_seats = isset($_POST ['bookings_seats']) ? $_POST ['bookings_seats'] : array();
         foreach ( $bookings as $key=>$booking_id ) {
            if (!in_array($booking_id,$selected_bookings)) {
               continue;
            }
            // make sure the seats are integers
            $bookings_seats[$key]=intval($bookings_seats[$key]);
            $booking = eme_get_booking ($booking_id);
            $person  = eme_get_person ($booking['person_id']);
            // 0 seats is not possible, then you should remove the booking
            if ($bookings_seats[$key]==0)
               $bookings_seats[$key]=1;
            if ($action == 'approveRegistration' && $booking['booking_seats']!= $bookings_seats[$key]) {
               eme_update_booking_seats($booking_id,$bookings_seats[$key]);
               eme_email_rsvp_booking($booking['event_id'],$person['person_name'],$person['person_email'],$person['person_phone'],$bookings_seats[$key],$booking['booking_comment'],$action);
            } elseif ($action == 'denyRegistration') {
               eme_delete_booking($booking_id);
               eme_email_rsvp_booking($booking['event_id'],$person['person_name'],$person['person_email'],$person['person_phone'],$bookings_seats[$key],$booking['booking_comment'],$action);
            }
         }
      }
   }
   
   // now show the menu
   $event_id = isset($_POST ['event_id']) ? intval($_POST ['event_id']) : 0;
   eme_registration_seats_form_table($event_id);
}

function eme_registration_seats_form_table($event_id=0) {
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php _e ('Change reserved spaces or cancel registrations','eme'); ?></h2>
<?php admin_show_warnings();?>
   <form id="posts-filter" action="" method="post">
   <input type='hidden' name='page' value='events-manager-registration-seats' />
   <div class="tablenav">

   <div class="alignleft actions">
   <select name="action">
   <option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
   <option value="approveRegistration"><?php _e ( 'Update registration','eme' ); ?></option>
   <option value="denyRegistration"><?php _e ( 'Deny registration','eme' ); ?></option>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
   <select name="event_id">
   <option value='0'><?php _e ( 'All events' ); ?></option>
   <?php
   $all_events=eme_get_events(0,"future");
   $events_with_bookings=array();
   foreach ( $all_events as $event ) {
      if (eme_get_bookings_for($event['event_id'])) {
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
         <th><?php _e ( 'Name', 'eme' ); ?></th>
         <th><?php _e ( 'Date and time', 'eme' ); ?></th>
         <th><?php _e ('Booker','eme'); ?></th>
         <th><?php _e ('Seats','eme'); ?></th>
      </tr>
   </thead>
   <tbody>
     <?php
      $i = 1;
      if ($event_id) {
         $bookings = eme_get_bookings_for($event_id);
      } else {
         $bookings = eme_get_bookings_for($events_with_bookings);
      }
      foreach ( $bookings as $event_booking ) {
         $event=eme_get_event($event_booking['event_id']);
         $class = ($i % 2) ? ' class="alternate"' : '';
         $localised_start_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event['event_start_date']));
         $localised_end_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event['event_end_date']));
         $style = "";
         $today = date ( "Y-m-d" );
         
         if ($event['event_start_date'] < $today)
            $style = "style ='background-color: #FADDB7;'";
         ?>
      <tr <?php echo "$class $style"; ?>>
         <td><input type='checkbox' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='selected_bookings[]' />
             <input type='hidden' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='bookings[]' /></td>
         <td><strong>
         <a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&amp;action=edit_event&amp;event_id=".$event_booking ['event_id']); ?>"><?php echo ($event ['event_name']); ?></a>
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
function eme_registration_approval_page() {
        global $wpdb;

   if (current_user_can( EDIT_CAPABILITY)) {
      // do the actions if required
      $action = isset($_POST ['action']) ? $_POST ['action'] : '';
      $pending_bookings = isset($_POST ['pending_bookings']) ? $_POST ['pending_bookings'] : array();
      $selected_bookings = isset($_POST ['selected_bookings']) ? $_POST ['selected_bookings'] : array();
      $bookings_seats = isset($_POST ['bookings_seats']) ? $_POST ['bookings_seats'] : array();
      foreach ( $pending_bookings as $key=>$booking_id ) {
         if (!in_array($booking_id,$selected_bookings)) {
            continue;
         }
         $booking = eme_get_booking ($booking_id);
         $person  = eme_get_person ($booking['person_id']);
         // update the db
         if ($action == 'approveRegistration') {
            eme_approve_booking($booking_id);
            // 0 seats is not possible, then you should remove the booking
            if ($bookings_seats[$key]==0)
               $bookings_seats[$key]=1;
            if ($booking['booking_seats']!= intval($bookings_seats[$key]))
               eme_update_booking_seats($booking_id,intval($bookings_seats[$key]));
         } elseif ($action == 'denyRegistration') {
            eme_delete_booking($booking_id);
         }
         // and then send the mail
         eme_email_rsvp_booking($booking['event_id'],$person['person_name'],$person['person_email'],$person['person_phone'],$bookings_seats[$key],$booking['booking_comment'],$action);
      }
   }
   // now show the menu
   $event_id = isset($_POST ['event_id']) ? intval($_POST ['event_id']) : 0;
   eme_registration_approval_form_table($event_id);
}

function eme_registration_approval_form_table($event_id=0) {
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php _e ('Pending Approvals','eme'); ?></h2>
<?php admin_show_warnings();?>
   <form id="posts-filter" action="" method="post">
   <input type='hidden' name='page' value='events-manager-registration-approval' />
   <div class="tablenav">

   <div class="alignleft actions">
   <select name="action">
   <option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
   <option value="approveRegistration"><?php _e ( 'Approve registration','eme' ); ?></option>
   <option value="denyRegistration"><?php _e ( 'Deny registration','eme' ); ?></option>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
   <select name="event_id">
   <option value='0'><?php _e ( 'All events' ); ?></option>
   <?php
   $all_events=eme_get_events(0,"future");
   $events_with_pending_bookings=array();
   foreach ( $all_events as $event ) {
      if ($event['registration_requires_approval'] && eme_get_bookings_for($event['event_id'],1)) {
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
         <th><?php _e ( 'Name', 'eme' ); ?></th>
         <th><?php _e ( 'Date and time', 'eme' ); ?></th>
         <th><?php _e ('Booker','eme'); ?></th>
         <th><?php _e ('Seats','eme'); ?></th>
      </tr>
   </thead>
   <tbody>
     <?php
      $i = 1;
      if ($event_id) {
         $pending_bookings = eme_get_bookings_for($event_id,1);
      } else {
         $pending_bookings = eme_get_bookings_for($events_with_pending_bookings,1);
      }
      foreach ( $pending_bookings as $event_booking ) {
         $event=eme_get_event($event_booking['event_id']);
         $class = ($i % 2) ? ' class="alternate"' : '';
         $localised_start_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event['event_start_date']));
         $localised_end_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event['event_end_date']));
         $style = "";
         $today = date ( "Y-m-d" );
         
         if ($event['event_start_date'] < $today)
            $style = "style ='background-color: #FADDB7;'";
         ?>
      <tr <?php echo "$class $style"; ?>>
         <td><input type='checkbox' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='selected_bookings[]' /></td>
             <input type='hidden' class='row-selector' value='<?php echo $event_booking ['booking_id']; ?>' name='pending_bookings[]' /></td>
         <td><strong>
         <a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&amp;action=edit_event&amp;event_id=".$event_booking ['event_id']); ?>"><?php echo ($event ['event_name']); ?></a>
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

function eme_get_user_email($user_id) {
   global $wpdb;
   $sql = "SELECT user_email FROM $wpdb->users WHERE ID = $user_id"; 
   return $wpdb->get_var( $wpdb->prepare($sql) );
}
function eme_get_user_name($user_id) {
   global $wpdb;
   $sql = "SELECT display_name FROM $wpdb->users WHERE ID = $user_id"; 
   return $wpdb->get_var( $wpdb->prepare($sql) );
}
function eme_get_user_phone($user_id) {
   return get_usermeta($user_id, 'eme_phone');
}

// got from http://davidwalsh.name/php-email-encode-prevent-spam
function eme_ascii_encode($e) {
    $output = "";
    for ($i = 0; $i < strlen($e); $i++) { $output .= '&#'.ord($e[$i]).';'; }
    return $output;
}

// template function
function eme_is_event_rsvpable() {
   if (eme_is_single_event_page() && isset($_REQUEST['event_id'])) {
      $event = eme_get_event(intval($_REQUEST['event_id']));
      if($event)
         return $event['event_rsvp'];
   }
   return 0;
}

function eme_event_needs_approval($event_id) {
   global $wpdb;
   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $sql = "SELECT registration_requires_approval from $events_table where event_id=$event_id";
   return $wpdb->get_var( $wpdb->prepare($sql) );
}

?>
