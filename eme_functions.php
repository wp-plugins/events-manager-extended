<?php

function eme_if_shortcode($atts,$content) {
   extract ( shortcode_atts ( array ('tag' => '', 'value' => '', 'notvalue' => '', 'lt' => '', 'gt' => '' ), $atts ) );
   if (is_numeric($value) || !empty($value)) {
      if ($tag==$value) return $content;
   } elseif (is_numeric($notvalue) || !empty($notvalue)) {
      if ($tag!=$notvalue) return $content;
   } elseif (is_numeric($lt) || !empty($lt)) {
      if ($tag<$lt) return $content;
   } elseif (is_numeric($gt) || !empty($gt)) {
      if ($tag>$gt) return $content;
   } else {
      if (!empty($tag)) return $content;
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

?>
