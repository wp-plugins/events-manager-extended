<?php

function eme_if_shortcode($atts,$content) {
   extract ( shortcode_atts ( array ('tag' => '', 'value' => '', 'notvalue' => '', 'lt' => '', 'gt' => '', 'contains'=>'' ), $atts ) );
   if (is_numeric($value) || !empty($value)) {
      if ($tag==$value) return do_shortcode($content);
   } elseif (is_numeric($notvalue) || !empty($notvalue)) {
      if ($tag!=$notvalue) return do_shortcode($content);
   } elseif (is_numeric($lt) || !empty($lt)) {
      if ($tag<$lt) return do_shortcode($content);
   } elseif (is_numeric($gt) || !empty($gt)) {
      if ($tag>$gt) return do_shortcode($content);
   } elseif (is_numeric($contains) || !empty($contains)) {
      if (strstr($tag,"$contains")) return do_shortcode($content);
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
   global $wp_query;
   return (eme_is_events_page () && (isset ( $wp_query->query_vars ['event_id'] ) && $wp_query->query_vars ['event_id'] != ''));
}

function eme_is_multiple_events_page() {
   global $wp_query;
   return (eme_is_events_page () && ! (isset ( $wp_query->query_vars ['event_id'] ) && $wp_query->query_vars ['event_id'] != ''));
}

function eme_is_single_location_page() {
   global $wp_query;
   return (eme_is_events_page () && (isset ( $wp_query->query_vars ['location_id'] ) && $wp_query->query_vars ['location_id'] != ''));
}

function eme_is_multiple_locations_page() {
   global $wp_query;
   return (eme_is_events_page () && ! (isset ( $wp_query->query_vars ['location_id'] ) && $wp_query->query_vars ['location_id'] != ''));
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

function eme_permalink_convert ($val) {
   $val=strtr($val, "ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ","SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");
   $val=strtolower(strtr($val, " ","-"));
   return urlencode($val);
}

function eme_event_url($event) {
   global $wp_rewrite;
   $events_page_link = eme_get_events_page(true, false);
   if (stristr ( $events_page_link, "?" ))
      $joiner = "&amp;";
   else
      $joiner = "?";

   if ($event['event_url'] != '') {
      $event_link = $event['event_url'];
   } else {
      if (isset($wp_rewrite) && $wp_rewrite->using_permalinks()) {
	 $name=eme_permalink_convert($event['event_name']);
         $event_link = site_url()."/events/".$event['event_id']."/".$name;
      } else {
         $event_link = $events_page_link.$joiner."event_id=".$event['event_id'];
      }
   }
   return $event_link;
}

function eme_location_url($location) {
   global $wp_rewrite;
   $events_page_link = eme_get_events_page(true, false);
   if (stristr ( $events_page_link, "?" ))
      $joiner = "&amp;";
   else
      $joiner = "?";

   if (isset($wp_rewrite) && $wp_rewrite->using_permalinks()) {
      $name=eme_permalink_convert($location['location_name']);
      $location_link = site_url()."/locations/".$location['location_id']."/".$name;
   } else {
      $location_link = $events_page_link.$joiner."location_id=".$location['location_id'];
   }
   return $location_link;
}


?>
