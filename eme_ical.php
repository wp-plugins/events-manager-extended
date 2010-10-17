<?php
function eme_ical() {
        if (isset ( $_REQUEST ['eme_ical'] ) && $_REQUEST ['eme_ical'] == 'public') {
                header ( "Content-type: text/calendar; charset=utf-8" );
		header("Content-Disposition: inline; filename=eme_public.ics");

                $events_page_link = eme_get_events_page(true, false);
                if (stristr ( $events_page_link, "?" ))
                        $joiner = "&";
                else
                        $joiner = "?";

		echo "BEGIN:VCALENDAR\r\n";
		echo "VERSION:2.0\r\n";
		echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
                $title_format = get_option('eme_rss_title_format' );
                $description_format = get_option('eme_rss_description_format');
                $events = eme_get_events ( 0 );
                foreach ( $events as $event ) {
                        $title = eme_replace_placeholders ( $title_format, $event, "rss" );
			$description = eme_replace_placeholders ( $description_format, $event, "rss" );
			// ical format doesn't support html code, but we can convert br to escaped newlines to maintain readable description output
			$description = strip_tags(preg_replace('/<br(\s+)?\/?>/i', "\\n", $description));
			$event_link = $events_page_link.$joiner."event_id=".$event['event_id'];
			$startstring=$event['event_start_date']." ".$event['event_start_time'];
			$dtstartdate=mysql2date("Ymd",$startstring);
			$dtstarthour=mysql2date("His",$startstring);
			$dtstart=$dtstartdate."T".$dtstarthour."Z";
			if ($event['event_end_date'] == "")
				$event['event_end_date'] = $event['event_start_date'];
			if ($event['event_end_time'] == "")
				$event['event_end_time'] = $event['event_start_time'];
			$endstring=$event['event_end_date']." ".$event['event_end_time'];
			$dtenddate=mysql2date("Ymd",$endstring);
			$dtendhour=mysql2date("His",$endstring);
			$dtend=$dtenddate."T".$dtendhour."Z";
			echo "BEGIN:VEVENT\r\n";
                        echo "DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z\r\n";
			echo "DTSTART:$dtstart\r\n";
			echo "DTEND:$dtend\r\n";
			echo "UID:$dtstart-$dtend-".$event['event_id']."@".$_SERVER['SERVER_NAME']."\r\n";
                        echo "SUMMARY:$title\r\n";
                        echo "DESCRIPTION:$description\r\n";
                        echo "URL:$event_link\r\n";
                        echo "ATTACH:$event_link\r\n";
			echo "LOCATION:".$event['location_name'].", ".$event['location_address'].", ".$event['location_town']."\r\n";
                        echo "END:VEVENT\r\n";
                }
		echo "END:VCALENDAR\r\n";
                die ();
        }
}
add_action ( 'init', 'eme_ical' );

?>
