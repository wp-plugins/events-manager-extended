<?php

function eme_ical_link($justurl = 0, $echo = 1, $text = "ICAL") {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'echo' => 1, 'text' => 'ICAL' );

      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
      $echo = (bool) $r ['echo'];
   }
   if ($text == '')
      $text = "ICAL";
   $url = site_url ("/?eme_ical=public");
   $link = "<a href='$url'>$text</a>";

   if ($justurl)
      $result = $url;
   else
      $result = $link;
   if ($echo)
      echo $result;
   else
      return $result;
}

function eme_ical_link_shortcode($atts) {
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => 'ICAL' ), $atts ) );
   $result = eme_ical_link ( "justurl=$justurl&echo=0&text=$text" );
   return $result;
}
add_shortcode ( 'events_ical_link', 'eme_ical_link_shortcode' );

function eme_ical() {
   if (isset ( $_GET ['eme_ical'] ) && $_GET ['eme_ical'] == 'public') {
      header("Content-type: text/calendar; charset=utf-8");
      header("Content-Disposition: inline; filename=eme_public.ics");

      $events_page_link = eme_get_events_page(true, false);
      if (stristr ( $events_page_link, "?" ))
         $joiner = "&";
      else
         $joiner = "?";

      echo "BEGIN:VCALENDAR\r\n";
      echo "VERSION:2.0\r\n";
      echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
      $title_format = get_option('eme_event_page_title_format' );
      $description_format = get_option('eme_single_event_format');
      $events = eme_get_events ( 0 );
      foreach ( $events as $event ) {
         $title = eme_replace_placeholders ( $title_format, $event, "rss" );
         // no html tags allowed in ical
         $title = strip_tags($title);
         $description = eme_replace_placeholders ( $description_format, $event, "rss" );
         // no html tags allowed in ical, but we can convert br to escaped newlines to maintain readable output
         $description = strip_tags(preg_replace('/<br(\s+)?\/?>/i', "\\n", $description));
         $location = eme_replace_placeholders ( "#_LOCATION, #_ADDRESS, #_TOWN", $event, "rss" );
         $event_link = $events_page_link.$joiner."event_id=".$event['event_id'];
         $startstring=$event['event_start_date']." ".$event['event_start_time'];
         $dtstartdate=mysql2date("Ymd",$startstring);
         $dtstarthour=mysql2date("His",$startstring);
         //$dtstart=$dtstartdate."T".$dtstarthour."Z";
         // we'll use localtime, so no "Z"
         $dtstart=$dtstartdate."T".$dtstarthour;
         if ($event['event_end_date'] == "")
            $event['event_end_date'] = $event['event_start_date'];
         if ($event['event_end_time'] == "")
            $event['event_end_time'] = $event['event_start_time'];
         $endstring=$event['event_end_date']." ".$event['event_end_time'];
         $dtenddate=mysql2date("Ymd",$endstring);
         $dtendhour=mysql2date("His",$endstring);
         //$dtend=$dtenddate."T".$dtendhour."Z";
         // we'll use localtime, so no "Z"
         $dtend=$dtenddate."T".$dtendhour;
         echo "BEGIN:VEVENT\r\n";
         //echo "DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z\r\n";
         // we'll use localtime, so no "Z"
         echo "DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "\r\n";
         echo "DTSTART:$dtstart\r\n";
         echo "DTEND:$dtend\r\n";
         echo "UID:$dtstart-$dtend-".$event['event_id']."@".$_SERVER['SERVER_NAME']."\r\n";
         echo "SUMMARY:$title\r\n";
         echo "DESCRIPTION:$description\r\n";
         echo "URL:$event_link\r\n";
         echo "ATTACH:$event_link\r\n";
         echo "LOCATION:$location\r\n";
         echo "END:VEVENT\r\n";
      }
      echo "END:VCALENDAR\r\n";
      die ();
   }
}
add_action ( 'init', 'eme_ical' );

?>
