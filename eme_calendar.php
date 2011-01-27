<?php
function eme_get_calendar_shortcode($atts) { 
   extract(shortcode_atts(array(
         'category' => 0,
         'full' => 0,
         'month' => '',
         'year' => '',
         'echo' => 0,
         'long_events' => 0,
         'author' => '',
         'contact_person' => '',
         'location_id' => ''
      ), $atts)); 
   $result = eme_get_calendar("full={$full}&month={$month}&year={$year}&echo={$echo}&long_events={$long_events}&category={$category}&author={$author}&contact_person={$contact_person}&location_id={$location_id}");
   return $result;
}
add_shortcode('events_calendar', 'eme_get_calendar_shortcode');

function eme_get_calendar($args="") {
   global $wp_locale;
   global $wpdb;
   // the calendar is being used, so we need the jquery for the calendar
   global $eme_need_calendar_js;
   $eme_need_calendar_js=1;

   $defaults = array(
      'category' => 0,
      'full' => 0,
      'month' => '',
      'year' => '',
      'echo' => 1,
      'long_events' => 0,
      'author' => '',
      'contact_person' => '',
      'location_id' => ''
   );
   $r = wp_parse_args( $args, $defaults );
   extract( $r );
   $echo = (bool) $r ['echo'];
   $long_events = $r ['long_events'];
   
   // this comes from global wordpress preferences
   $start_of_week = get_option('start_of_week');

   //if(isset($_GET['calmonth']) && $_GET['calmonth'] != '')   {
   // $month =  eme_sanitize_request($_GET['calmonth']) ;
   //} else {
      if ($month == '')
         $month = date('m'); 
   //}
   //if(isset($_GET['calyear']) && $_GET['calyear'] != '')   {
   // $year =  eme_sanitize_request($_GET['calyear']) ;
   //} else {
      if ($year == '')
         $year = date('Y');
   //}
   $date = mktime(0,0,0,$month, date('d'), $year); 
   $day = date('d', $date); 
   // $month = date('m', $date); 
   // $year = date('Y', $date);
   // Get the first day of the month 
   $month_start = mktime(0,0,0,$month, 1, $year);
   // Get friendly month name
   
   $month_name = date_i18n('M', strtotime("$year-$month-$day"));
   // Figure out which day of the week 
   // the month starts on. 
   $month_start_day = date('D', $month_start);

   switch($month_start_day){ 
      case "Sun": $offset = 0; break; 
      case "Mon": $offset = 1; break; 
      case "Tue": $offset = 2; break; 
      case "Wed": $offset = 3; break; 
      case "Thu": $offset = 4; break; 
      case "Fri": $offset = 5; break; 
      case "Sat": $offset = 6; break;
   }

   $offset -= $start_of_week;
   if($offset<0)
      $offset += 7;
   
   // determine how many days are in the last month. 
   if($month == 1) { 
      $num_days_last = eme_days_in_month(12, ($year -1)); 
   } else { 
     $num_days_last = eme_days_in_month(($month-1), $year); 
   }
   // determine how many days are in the current month. 
   $num_days_current = eme_days_in_month($month, $year);
   // Build an array for the current days 
   // in the month 
   for($i = 1; $i <= $num_days_current; $i++){ 
      $num_days_array[] = $i; 
   }
   // Build an array for the number of days 
   // in last month 
   for($i = 1; $i <= $num_days_last; $i++){ 
       $num_days_last_array[] = $i; 
   }
   // If the $offset from the starting day of the 
   // week happens to be Sunday, $offset would be 0, 
   // so don't need an offset correction. 

   if($offset > 0){ 
       $offset_correction = array_slice($num_days_last_array, -$offset, $offset); 
       $new_count = array_merge($offset_correction, $num_days_array); 
       $offset_count = count($offset_correction); 
   } 

   // The else statement is to prevent building the $offset array. 
   else { 
       $offset_count = 0; 
       $new_count = $num_days_array;
   }
   // count how many days we have with the two 
   // previous arrays merged together 
   $current_num = count($new_count); 

   // Since we will have 5 HTML table rows (TR) 
   // with 7 table data entries (TD) 
   // we need to fill in 35 TDs 
   // so, we will have to figure out 
   // how many days to appened to the end 
   // of the final array to make it 35 days. 

   if($current_num > 35){ 
      $num_weeks = 6; 
      $outset = (42 - $current_num); 
   } elseif($current_num < 35){ 
      $num_weeks = 5; 
      $outset = (35 - $current_num); 
   } 
   if($current_num == 35){ 
      $num_weeks = 5; 
      $outset = 0; 
   } 
   // Outset Correction 
   for($i = 1; $i <= $outset; $i++){ 
      $new_count[] = $i; 
   }
   // Now let's "chunk" the $all_days array 
   // into weeks. Each week has 7 days 
   // so we will array_chunk it into 7 days. 
   $weeks = array_chunk($new_count, 7); 

   $full ? $link_extra_class = "full-link" : $link_extra_class = '';
   $long_events ? $link_extra_class .= " long_events" : "";
   // the real links are created via jquery when clicking on the prev-month or next-month class-links
   $previous_link = "<a class='prev-month $link_extra_class' href=\"#\">&lt;&lt;</a>"; 
   $next_link = "<a class='next-month $link_extra_class' href=\"#\">&gt;&gt;</a>";

   $random = (rand(100,200));
   $full ? $class = 'eme-calendar-full' : $class='eme-calendar';
   $calendar="
      <div class='$class' id='eme-calendar-$random'>
      <div style='display:none' class='month_n'>$month</div>
      <div class='year_n' style='display:none' >$year</div>
      <div class='cat_chosen' style='display:none' >$category</div>
      <div class='author_chosen' style='display:none' >$author</div>
      <div class='contact_person_chosen' style='display:none' >$contact_person</div>
      <div class='location_chosen' style='display:none' >$location_id</div>";
   
   $weekdays = array(__('Sunday'),__('Monday'),__('Tuesday'),__('Wednesday'),__('Thursday'),__('Friday'),__('Saturday'));
   $n = 0 ;
   while( $n < $start_of_week ) {
      $last_day = array_shift($weekdays);
      $weekdays[]= $last_day; 
      $n++;
   }

   $days_initials = "";
   foreach($weekdays as $weekday) {
      if ($full)
         $days_initials .= "<td>".$wp_locale->get_weekday_abbrev($weekday)."</td>";
      else
         $days_initials .= "<td>".$wp_locale->get_weekday_initial($weekday)."</td>";
   } 

   if ($full) {
      $fullclass = 'fullcalendar';
      $head = "<td class='month_name' colspan='7'>$previous_link $next_link $month_name $year</td>\n";
   } else {
      $fullclass='';
      $head = "<td>$previous_link</td><td class='month_name' colspan='5'>$month_name $year</td><td>$next_link</td>\n";
   }
   // Build the heading portion of the calendar table
   $calendar .=  "<table class='eme-calendar-table $fullclass'>\n".
                 "<thead>\n<tr>\n".$head."</tr>\n</thead>\n".
                 "<tr class='days-names'>\n".$days_initials."</tr>\n";

   // Now we break each key of the array
   // into a week and create a new table row for each 
   // week with the days of that week in the table data 

   $i = 0; 
   $curr_day=date('j');
   $curr_month=date('m');
   $curr_year=date('Y');
   foreach ($weeks as $week) { 
      $calendar .= "<tr>\n"; 
      foreach ($week as $d) { 
         if ($i < $offset_count) { //if it is PREVIOUS month
            $calendar .= "<td class='eventless-pre'>$d</td>\n"; 
         }
         if (($i >= $offset_count) && ($i < ($num_weeks * 7) - $outset)) {
            // if it is THIS month
            if($d == $curr_day && $month == $curr_month && $year == $curr_year) {
               $calendar .= "<td class='eventless-today'>$d</td>\n"; 
            } else { 
               $calendar .= "<td class='eventless'>$d</td>\n"; 
            } 
         } elseif(($outset > 0)) {
            //if it is NEXT month
            if(($i >= ($num_weeks * 7) - $outset)) { 
               $calendar .= "<td class='eventless-post'>$d</td>\n"; 
            } 
         } 
         $i++; 
      } 
      $calendar .= "</tr>\n";
   } 
   
   $calendar .= " </table>\n</div>";
   
   // query the database for events in this time span
   if ($month == 1) {
      $month_pre=12;
      $month_post=2;
      $year_pre=$year-1;
      $year_post=$year;
   } elseif($month == 12) {
      $month_pre=11;
      $month_post=1;
      $year_pre=$year;
      $year_post=$year+1;
   } else {
      $month_pre=$month-1;
      $month_post=$month+1;
      $year_pre=$year;
      $year_post=$year;
   }

   // we'll look for events in the requested month and 7 days before and after
   $number_of_days_pre=eme_days_in_month($month_pre, $year_pre);
   $limit_pre=date("Y-m-d", mktime(0,0,0,$month_pre, $number_of_days_pre-7 , $year_pre));
   $limit_post=date("Y-m-d", mktime(0,0,0,$month_post, 7 , $year_post));
   $events = eme_get_events(0, "$limit_pre--$limit_post", "ASC", 0, $location_id, $category , $author , $contact_person );

//----- DEBUG ------------
//foreach($events as $event) { //DEBUG
// $calendar .= ("$event->event_day / $event->event_month_n - $event->event_name<br />");
//}
// ------------------

   $eventful_days= array();
   if($events){   
      //Go through the events and slot them into the right d-m index
      foreach($events as $event) {
         if ($event ['event_status'] == 2 && !is_user_logged_in()) {
            continue;
         }
         if ($event ['location_id'] ) {
            $this_location = eme_get_location ( $event ['location_id'] );
            $event ['location_name'] = $this_location ['location_name'];
            $event ['location_address'] = $this_location ['location_address'];
            $event ['location_town'] = $this_location ['location_town'];
         }

         if( $long_events ) {
            //If $long_events is set then show a date as eventful if there is an multi-day event which runs during that day
            $event_start_date = strtotime($event['event_start_date']);
            $event_end_date = strtotime($event['event_end_date']);
            if ($event_end_date < $event_start_date)
               $event_end_date=$event_start_date;
            while( $event_start_date <= $event_end_date ) {
               $event_eventful_date = date('Y-m-d', $event_start_date);
               //Only show events on the day that they start
               if(isset($eventful_days[$event_eventful_date]) &&  is_array($eventful_days[$event_eventful_date]) ) {
                  $eventful_days[$event_eventful_date][] = $event; 
               } else {
                  $eventful_days[$event_eventful_date] = array($event);
               }  
               $event_start_date += (60*60*24);          
            }
         } else {
            //Only show events on the day that they start
            if ( isset($eventful_days[$event['event_start_date']]) && is_array($eventful_days[$event['event_start_date']]) ) {
               $eventful_days[$event['event_start_date']][] = $event; 
            } else {
               $eventful_days[$event['event_start_date']] = array($event);
            }
         }
      }
   }

   $event_format = get_option('eme_full_calendar_event_format'); 
   $event_title_format = get_option('eme_small_calendar_event_title_format');
   $event_title_separator_format = get_option('eme_small_calendar_event_title_separator');
   $cells = array() ;
   foreach($eventful_days as $day_key => $events) {
      //Set the date into the key
      $event_date = explode('-', $day_key);
      $cells[$day_key]['day'] = ltrim($event_date[2],'0');
      $cells[$day_key]['month'] = $event_date[1];
      $events_titles = array();
      foreach($events as $event) { 
         $events_titles[] = eme_replace_placeholders($event_title_format, $event);
      }
      $link_title = implode($event_title_separator_format,$events_titles);
      
      $event_page_link = eme_get_events_page(true, false);
      if (stristr($event_page_link, "?"))
         $joiner = "&amp;";
      else
         $joiner = "?";
      
      
      $cells[$day_key]['cell'] = "<a title='$link_title' href='".$event_page_link.$joiner."calendar_day={$day_key}'>{$cells[$day_key]['day']}</a>";
      if ($full) {
         $cells[$day_key]['cell'] .= "<ul>";
      
         foreach($events as $event) {
            $cells[$day_key]['cell'] .= eme_replace_placeholders($event_format, $event);
         } 
         $cells[$day_key]['cell'] .= "</ul>";
         }
   }

   if($events){
      foreach($cells as $cell) {
         if ($cell['month'] == $month_pre) {
            $calendar=str_replace("<td class='eventless-pre'>".$cell['day']."</td>","<td class='eventful-pre event-day-".$cell['day']."'>".$cell['cell']."</td>",$calendar);
         } elseif ($cell['month'] == $month_post) {
            $calendar=str_replace("<td class='eventless-post'>".$cell['day']."</td>","<td class='eventful-post event-day-".$cell['day']."'>".$cell['cell']."</td>",$calendar);
         } elseif ($cell['day'] == $day && $cell['month'] == $month && $day == $curr_day && $month == $curr_month) {
            $calendar=str_replace("<td class='eventless-today'>".$cell['day']."</td>","<td class='eventful-today event-day-".$cell['day']."'>".$cell['cell']."</td>",$calendar);
         } elseif ($cell['month'] == $month) {
            $calendar=str_replace("<td class='eventless'>".$cell['day']."</td>","<td class='eventful event-day-".$cell['day']."'>".$cell['cell']."</td>",$calendar);
            }
      }
   }

   $output=$calendar;
   if ($echo)
      echo $output; 
   else
      return $output;
}

function eme_days_in_month($month, $year) {
   return (date("t",mktime(0,0,0,$month,1,$year)));
}

function eme_ajaxize_calendar() {
   global $eme_need_calendar_js;

   if (isset($_GET['lang'])) {
      $jquery_override_lang=", lang: '".$_GET['lang']."'";
   } else {
      $jquery_override_lang="";
   }
   $load_js_in_header = get_option('eme_load_js_in_header' );
   if ($eme_need_calendar_js || $load_js_in_header) {
?>
   <script type='text/javascript'>
      $j_eme_calendar=jQuery.noConflict();
      $j_eme_calendar(document).ready( function() {
         initCalendar();
      });
      
      function initCalendar() {
         $j_eme_calendar('a.prev-month').click(function(e){
            e.preventDefault();
            tableDiv = $j_eme_calendar(this).closest('table').parent();
            ($j_eme_calendar(this).hasClass('full-link')) ? fullcalendar = 1 : fullcalendar = 0;
            ($j_eme_calendar(this).hasClass('long_events')) ? long_events = 1 : long_events = 0;
            prevMonthCalendar(tableDiv, fullcalendar, long_events);
         } );
         $j_eme_calendar('a.next-month').click(function(e){
            e.preventDefault();
            tableDiv = $j_eme_calendar(this).closest('table').parent();
            ($j_eme_calendar(this).hasClass('full-link')) ? fullcalendar = 1 : fullcalendar = 0;
            ($j_eme_calendar(this).hasClass('long_events')) ? long_events = 1 : long_events = 0;
            nextMonthCalendar(tableDiv, fullcalendar, long_events);
         } );
      }

      function prevMonthCalendar(tableDiv, fullcalendar, showlong_events) {
         if (fullcalendar === undefined) {
             fullcalendar = 0;
         }
         if (showlong_events === undefined) {
             showlong_events = 0;
         }
         month_n = tableDiv.children('div.month_n').text();
         year_n = tableDiv.children('div.year_n').text();
         cat_chosen = tableDiv.children('div.cat_chosen').text();
         author_chosen = tableDiv.children('div.author_chosen').text();
         contact_person_chosen = tableDiv.children('div.contact_person_chosen').text();
         location_chosen = tableDiv.children('div.location_chosen').text();
         parseInt(month_n) == 1 ? prevMonth = 12 : prevMonth = parseInt(month_n,10) - 1 ; 
         if (parseInt(month_n,10) == 1)
            year_n = parseInt(year_n,10) -1;
         $j_eme_calendar.get("<?php echo site_url(); ?>", {
            eme_ajaxCalendar: 'true',
            calmonth: prevMonth,
            calyear: year_n,
            full: fullcalendar,
            long_events: showlong_events,
            category: cat_chosen,
            author: author_chosen,
            contact_person: contact_person_chosen,
            location_id: location_chosen <?php echo $jquery_override_lang; ?>
         }, function(data){
            tableDiv.replaceWith(data);
            initCalendar();
         });
      }
      function nextMonthCalendar(tableDiv, fullcalendar, showlong_events) {
         if (fullcalendar === undefined) {
             fullcalendar = 0;
         }
         if (showlong_events === undefined) {
             showlong_events = 0;
         }
         month_n = tableDiv.children('div.month_n').text();
         year_n = tableDiv.children('div.year_n').text();
         cat_chosen = tableDiv.children('div.cat_chosen').text();
         author_chosen = tableDiv.children('div.author_chosen').text();
         contact_person_chosen = tableDiv.children('div.contact_person_chosen').text();
         location_chosen = tableDiv.children('div.location_chosen').text();
         parseInt(month_n,10) == 12 ? nextMonth = 1 : nextMonth = parseInt(month_n,10) + 1 ; 
         if (parseInt(month_n,10) == 12)
            year_n = parseInt(year_n,10) + 1;
         $j_eme_calendar.get("<?php echo site_url(); ?>", {
            eme_ajaxCalendar: 'true',
            calmonth: nextMonth,
            calyear: year_n,
            full : fullcalendar,
            long_events: showlong_events,
            category: cat_chosen,
            author: author_chosen,
            contact_person: contact_person_chosen,
            location_id: location_chosen <?php echo $jquery_override_lang; ?>
         }, function(data){
            tableDiv.replaceWith(data);
            initCalendar();
         });
      }
      function reloadCalendar(tableDiv, fullcalendar, showlong_events) {
         if (fullcalendar === undefined) {
            fullcalendar = 0;
         }
         if (showlong_events === undefined) {
            showlong_events = 0;
         }
         month_n = tableDiv.children('div.month_n').text();
         year_n = tableDiv.children('div.year_n').text();
         cat_chosen = tableDiv.children('div.cat_chosen').text();
         author_chosen = tableDiv.children('div.author_chosen').text();
         contact_person_chosen = tableDiv.children('div.contact_person_chosen').text();
         location_chosen = tableDiv.children('div.location_chosen').text();
         $j_eme_calendar.get("<?php echo site_url(); ?>", {
            eme_ajaxCalendar: 'true',
            calmonth: parseInt(month_n,10),
            calyear: parseInt(year_n,10),
            full : fullcalendar,
            long_events: showlong_events,
            category: cat_chosen,
            author: author_chosen,
            contact_person: contact_person_chosen,
            location_id: location_chosen <?php echo $jquery_override_lang; ?>
         }, function(data){
            tableDiv.replaceWith(data);
            initCalendar();
         });
      }
   </script>
   
<?php
   }
}
add_action('wp_footer', 'eme_ajaxize_calendar');

function eme_filter_calendar_ajax() {
   if(isset($_GET['eme_ajaxCalendar']) && $_GET['eme_ajaxCalendar'] == true) {
      (isset($_GET['full']) && $_GET['full'] == 1) ? $full = 1 : $full = 0;
      (isset($_GET['long_events']) && $_GET['long_events'] == 1) ? $long_events = 1 : $long_events = 0;
      (isset($_GET['category'])) ? $category = $_GET['category'] : $category = 0;
      (isset($_GET['calmonth'])) ? $month = eme_sanitize_request($_GET['calmonth']) : $month = ''; 
      (isset($_GET['calyear'])) ? $year = eme_sanitize_request($_GET['calyear']) : $year = ''; 
      (isset($_GET['author'])) ? $author = eme_sanitize_request($_GET['author']) : $author = ''; 
      (isset($_GET['contact_person'])) ? $contact_person = eme_sanitize_request($_GET['contact_person']) : $contact_person = ''; 
      (isset($_GET['location_id'])) ? $location_id = eme_sanitize_request($_GET['location_id']) : $location_id = '';
      // $calyear = eme_sanitize_request($_GET['calyear']);
      eme_get_calendar('echo=1&full='.$full.'&long_events='.$long_events.'&category='.$category.'&month='.$month.'&year='.$year.'&author='.$author.'&contact_person='.$contact_person.'&location_id='.$location_id);
      die();
   }
}
add_action('init','eme_filter_calendar_ajax');

?>
