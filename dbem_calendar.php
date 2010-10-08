<?php
function dbem_get_calendar_shortcode($atts) { 
	extract(shortcode_atts(array(
			'category' => 0,
			'full' => 0,
			'month' => '',
			'year' => '',
			'echo' => 0,
			'long_events' => 0
		), $atts)); 
	$result = dbem_get_calendar("full={$full}&month={$month}&year={$year}&echo={$echo}&long_events={$long_events}&category={$category}");
	return $result;
}
add_shortcode('events_calendar', 'dbem_get_calendar_shortcode');

function dbem_get_calendar($args="") {
	global $wp_locale;

	$defaults = array(
		'category' => 0,
		'full' => 0,
		'month' => '',
		'year' => '',
		'echo' => 1,
		'long_events' => 0
	);
	$r = wp_parse_args( $args, $defaults );
	extract( $r );
	$echo = (bool) $r ['echo'];
	
	// this comes from global wordpress preferences
	$start_of_week = get_option('start_of_week');

 	global $wpdb;
	//if(isset($_GET['calmonth']) && $_GET['calmonth'] != '')   {
	//	$month =  dbem_sanitize_request($_GET['calmonth']) ;
	//} else {
		if ($month == '')
			$month = date('m'); 
	//}
	//if(isset($_GET['calyear']) && $_GET['calyear'] != '')   {
	//	$year =  dbem_sanitize_request($_GET['calyear']) ;
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
	
	$month_name = mysql2date('M', "$year-$month-$day 00:00:00");
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
	   $num_days_last = dbem_days_in_month(12, ($year -1)); 
	} else { 
	  $num_days_last = dbem_days_in_month(($month-1), $year); 
	}
	// determine how many days are in the current month. 
	$num_days_current = dbem_days_in_month($month, $year);
	// Build an array for the current days 
	// in the month 
	for($i = 1; $i <= $num_days_current; $i++){ 
	   $num_days_array[] = mktime(0,0,0,$month, $i, $year); 
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
	$full ? $class = 'dbem-calendar-full' : $class='dbem-calendar';
	$calendar="<div class='$class' id='dbem-calendar-$random'><div style='display:none' class='month_n'>$month</div><div class='year_n' style='display:none' >$year</div><div class='cat_chosen' style='display:none' >$category</div>";
	
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

	$full ? $fullclass = 'fullcalendar' : $fullclass='';
	// Build the heading portion of the calendar table 
	$calendar .=  "<table class='dbem-calendar-table $fullclass'>\n". 
	   	"<thead>\n<tr>\n".
		"<td>$previous_link</td><td class='month_name' colspan='5'>$month_name $year</td><td>$next_link</td>\n". 
		"</tr>\n</thead>\n".	
		"<tr class='days-names'>\n". 
		$days_initials. 
		"</tr>\n"; 

	// Now we break each key of the array
	// into a week and create a new table row for each 
	// week with the days of that week in the table data 

	$i = 0; 
	foreach($weeks as $week){ 
		$calendar .= "<tr>\n"; 
		foreach($week as $d){ 
	   		if ($i < $offset_count) { //if it is PREVIOUS month
	      			$calendar .= "<td class='eventless-pre'>$d</td>\n"; 
	      		}
			if(($i >= $offset_count) && ($i < ($num_weeks * 7) - $outset)){ // if it is THIS month
				$fullday=$d;
				$d=date('j', $d);
				$day_link = "$d";
			  	// original :
				//if($date == mktime(0,0,0,$month,$d,$year)){
		 	  	// proposed patch (http://davidebenini.it/events-manager-forum/topic.php?id=73 )
			  	// if(($date == mktime(0,0,0,$month,$d,$year)) && (date('F') == $month_name)) {
			  	// my solution:
			  	if($d == date('j') && $month == date('m') && $year == date('Y')) {
	        			$calendar .= "<td class='eventless-today'>$d</td>\n"; 
	        		} else { 
	         			$calendar .= "<td class='eventless'>$day_link</td>\n"; 
	        		} 
	        	} elseif(($outset > 0)) { //if it is NEXT month
	         		if(($i >= ($num_weeks * 7) - $outset)){ 
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
	$limit_pre=date("Y-m-d", mktime(0,0,0,$month_pre, 1 , $year_pre));
	$number_of_days_post=dbem_days_in_month($month_post, $year_post);
	$limit_post=date("Y-m-d", mktime(0,0,0,$month_post, $number_of_days_post , $year_post));
	$events_table = $wpdb->prefix.EVENTS_TBNAME; 
	if ($category && get_option('dbem_categories_enabled')) {
		//show a specific category
		if ($category != '' && is_numeric($category)){
			$cat_condition = "AND FIND_IN_SET($category,event_category_ids)";
		}elseif( preg_match('/^([0-9],?)+$/', $category) ){
			$category = explode(',', $category);
			$category_conditions = array();
			foreach($category as $cat){
				$category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
			}
			$cat_condition = "AND (".implode(' OR', $category_conditions).")";
		}
	} else {
		$cat_condition = "";
	}
	$sql = "SELECT event_id, 
		event_name, 
	 	event_start_date,
		event_start_time, 
		event_end_date,
		event_category_ids,
		location_id,
		DATE_FORMAT(event_start_date, '%w') AS 'event_weekday_n',
		DATE_FORMAT(event_start_date, '%e') AS 'event_day',
		DATE_FORMAT(event_start_date, '%c') AS 'event_month_n',
		DATE_FORMAT(event_start_time, '%Y') AS 'event_year',
		DATE_FORMAT(event_start_time, '%k') AS 'event_hh',
		DATE_FORMAT(event_start_time, '%i') AS 'event_mm'

		FROM $events_table 
		WHERE ((event_start_date BETWEEN '$limit_pre' AND '$limit_post') OR (event_end_date BETWEEN '$limit_pre' AND '$limit_post')) $cat_condition ORDER BY event_start_date ASC, event_start_time ASC";

	$events=$wpdb->get_results($sql, ARRAY_A);

//----- DEBUG ------------
//foreach($events as $event) { //DEBUG
//	$calendar .= ("$event->event_day / $event->event_month_n - $event->event_name<br/>");
//}
// ------------------

	$eventful_days= array();
	if($events){	
		//Go through the events and slot them into the right d-m index
		foreach($events as $event) {
			if ($event ['location_id'] ) {
				$this_location = dbem_get_location ( $event ['location_id'] );
				$event ['location_name'] = $this_location ['location_name'];
				$event ['location_address'] = $this_location ['location_address'];
				$event ['location_town'] = $this_location ['location_town'];
			}

			if( $long_events ){
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
			}else{
				//Only show events on the day that they start
				if( isset($eventful_days[$event['event_start_date']]) && is_array($eventful_days[$event['event_start_date']]) ) {
					$eventful_days[$event['event_start_date']][] = $event; 
				} else {
					$eventful_days[$event['event_start_date']] = array($event);
				}
			}
		}
	}

	$event_format = get_option('dbem_full_calendar_event_format'); 
	$event_title_format = get_option('dbem_small_calendar_event_title_format');
	$event_title_separator_format = get_option('dbem_small_calendar_event_title_separator');
	$cells = array() ;
	foreach($eventful_days as $day_key => $events) {
		//Set the date into the key
		$event_date = explode('-', $day_key);
		$cells[$day_key]['day'] = ltrim($event_date[2],'0');
		$cells[$day_key]['month'] = $event_date[1];
		$events_titles = array();
		foreach($events as $event) { 
			$events_titles[] = dbem_replace_placeholders($event_title_format, $event);
		}
		$link_title = implode($event_title_separator_format,$events_titles);
		
		$event_page_link = dbem_get_events_page(true, false);
		if (stristr($event_page_link, "?"))
			//$joiner = "&amp;";
			$joiner = "&";
		else
			$joiner = "?";
		
		
		$cells[$day_key]['cell'] = "<a title='$link_title' href='".$event_page_link.$joiner."calendar_day={$day_key}'>{$cells[$day_key]['day']}</a>";
		if ($full) {
			$cells[$day_key]['cell'] .= "<ul>";
		
			foreach($events as $event) {
				$cells[$day_key]['cell'] .= dbem_replace_placeholders($event_format, $event);
			} 
			$cells[$day_key]['cell'] .= "</ul>";
   		}
	}

//	print_r($cells);

	if($events){
		foreach($cells as $cell) {
			if ($cell['month'] == $month_pre) {
			 	$calendar=str_replace("<td class='eventless-pre'>".$cell['day']."</td>","<td class='eventful-pre'>".$cell['cell']."</td>",$calendar);
			} elseif($cell['month'] == $month_post) {
			 	$calendar=str_replace("<td class='eventless-post'>".$cell['day']."</td>","<td class='eventful-post'>".$cell['cell']."</td>",$calendar);
			} elseif($cell['day'] == $day && $cell['month'] == date('m')) {
  			 	$calendar=str_replace("<td class='eventless-today'>".$cell['day']."</td>","<td class='eventful-today'>".$cell['cell']."</td>",$calendar);
			} elseif( $cell['month'] == $month ) {
		    	$calendar=str_replace("<td class='eventless'>".$cell['day']."</td>","<td class='eventful'>".$cell['cell']."</td>",$calendar);
	   		}
		}
	}

	$output=$calendar;
	if ($echo)
		echo $output; 
	else
		return $output;
}

function dbem_days_in_month($month, $year) {
	return (date("t",mktime(0,0,0,$month,1,$year)));
}

function dbem_ajaxize_calendar() {
?>
	<script type='text/javascript'>
		$j_dbem_calendar=jQuery.noConflict();
		$j_dbem_calendar(document).ready( function() {
		   initCalendar();
		});
		
		function initCalendar() {
			$j_dbem_calendar('a.prev-month').click(function(e){
				e.preventDefault();
				tableDiv = $j_dbem_calendar(this).closest('table').parent();
				($j_dbem_calendar(this).hasClass('full-link')) ? fullcalendar = 1 : fullcalendar = 0;
				($j_dbem_calendar(this).hasClass('long_events')) ? long_events = 1 : long_events = 0;
				prevMonthCalendar(tableDiv, fullcalendar, long_events);
			} );
			$j_dbem_calendar('a.next-month').click(function(e){
				e.preventDefault();
				tableDiv = $j_dbem_calendar(this).closest('table').parent();
				($j_dbem_calendar(this).hasClass('full-link')) ? fullcalendar = 1 : fullcalendar = 0;
				($j_dbem_calendar(this).hasClass('long_events')) ? long_events = 1 : long_events = 0;
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
			month_n = tableDiv.children('div.month_n').html();
			year_n = tableDiv.children('div.year_n').html();
			cat_chosen = tableDiv.children('div.cat_chosen').html();
			parseInt(month_n) == 1 ? prevMonth = 12 : prevMonth = parseInt(month_n,10) - 1 ; 
		   	if (parseInt(month_n,10) == 1)
				year_n = parseInt(year_n,10) -1;
			$j_dbem_calendar.get("<?php echo site_url(); ?>", {ajaxCalendar: 'true', calmonth: prevMonth, calyear: year_n, full: fullcalendar, long_events: showlong_events, category: cat_chosen}, function(data){
				tableDiv.html(data);
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
			month_n = tableDiv.children('div.month_n').html();
			year_n = tableDiv.children('div.year_n').html();
			cat_chosen = tableDiv.children('div.cat_chosen').html();
			parseInt(month_n,10) == 12 ? nextMonth = 1 : nextMonth = parseInt(month_n,10) + 1 ; 
		   	if (parseInt(month_n,10) == 12)
				year_n = parseInt(year_n,10) + 1;
			$j_dbem_calendar.get("<?php echo site_url(); ?>", {ajaxCalendar: 'true', calmonth: nextMonth, calyear: year_n, full : fullcalendar, long_events: showlong_events, category: cat_chosen}, function(data){
				tableDiv.html(data);
				initCalendar();
			});
		}
		
		// function reloadCalendar(e) {
		// 	// e.preventDefault();
		//  	console.log($j_dbem_calendar(this).parents('table'));
		//     $j_dbem_calendar.get("<?php site_url(); ?>", {ajax: 'true'}, function(data){
		// 		tableDiv = table.parent();
		// 		tableDiv.html(data);
		//             });
		// }
		//
		
	</script>
	
<?php
}
add_action('wp_head', 'dbem_ajaxize_calendar');

function dbem_filter_calendar_ajax() {
	if(isset($_GET['ajaxCalendar']) && $_GET['ajaxCalendar'] == true) {
		(isset($_GET['full']) && $_GET['full'] == 1) ? $full = 1 : $full = 0;
		(isset($_GET['long_events']) && $_GET['long_events'] == 1) ? $long_events = 1 : $long_events = 0;
		(isset($_GET['category'])) ? $category = intval($_GET['category']) : $category = 0;
		(isset($_GET['calmonth'])) ? $month = dbem_sanitize_request($_GET['calmonth']) : $month = ''; 
		(isset($_GET['calyear'])) ? $year = dbem_sanitize_request($_GET['calyear']) : $year = ''; 
		// $calyear = dbem_sanitize_request($_GET['calyear']);
		dbem_get_calendar('echo=1&full='.$full.'&long_events='.$long_events.'&category='.$category.'&month='.$month.'&year='.$year);
		die();
	}
}
add_action('init','dbem_filter_calendar_ajax');

?>
