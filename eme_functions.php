<?php

function eme_if_shortcode($atts,$content) {
   extract ( shortcode_atts ( array ('tag' => '', 'value' => '', 'notvalue' => '', 'lt' => '', 'gt' => '' ), $atts ) );
   if (is_numeric($value) || !empty($value)) {
      if ($tag==$value) return do_shortcode($content);
   } elseif (is_numeric($notvalue) || !empty($notvalue)) {
      if ($tag!=$notvalue) return do_shortcode($content);
   } elseif (is_numeric($lt) || !empty($lt)) {
      if ($tag<$lt) return do_shortcode($content);
   } elseif (is_numeric($gt) || !empty($gt)) {
      if ($tag>$gt) return do_shortcode($content);
   } else {
      if (!empty($tag)) return do_shortcode($content);
   }
}
add_shortcode ( 'events_if', 'eme_if_shortcode');
add_shortcode ( 'events_if2', 'eme_if_shortcode');
add_shortcode ( 'events_if3', 'eme_if_shortcode');

// Returns true if the page in question is the events page
function eme_is_events_page() {
   $events_page_id = get_option('eme_events_page' );
   if ($events_page_id) {
      return is_page ( $events_page_id );
   } else {
      return false;
   }
}

function eme_is_single_event_page() {
   return (eme_is_events_page () && (isset ( $_REQUEST ['event_id'] ) && $_REQUEST ['event_id'] != ''));
}

function eme_is_multiple_events_page() {
   return (eme_is_events_page () && ! (isset ( $_REQUEST ['event_id'] ) && $_REQUEST ['event_id'] != ''));
}

function eme_is_single_location_page() {
   return (eme_is_events_page () && (isset ( $_REQUEST ['location_id'] ) && $_REQUEST ['location_id'] != ''));
}

function eme_is_multiple_locations_page() {
   return (eme_is_events_page () && ! (isset ( $_REQUEST ['location_id'] ) && $_REQUEST ['location_id'] != ''));
}

function eme_get_contact($event) {
   $event['event_contactperson_id'] ? $contact_id = $event['event_contactperson_id'] : $contact_id = get_option('eme_default_contact_person');
   // suppose the user has been deleted ...
   if (!get_userdata($contact_id)) $contact_id = get_option('eme_default_contact_person');
   if ($contact_id == -1)
      $contact_id = $event['event_author'];
   $userinfo=get_userdata($contact_id);
   return $userinfo;
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

?>
