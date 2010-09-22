<?php

function dbem_recurrence_test() {
	echo "<h2>Recurrence iCalendar</h2>";   
	
	echo "<h3>Daily, every other day</h3>";  
	$recurrence = array('recurrence_start_date' => '2009-02-10', 'recurrence_end_date' => '2009-03-10', 'recurrence_freq'=>'daily' , 'recurrence_interval' => 2); 
	$matching_days = dbem_get_recurrence_days($recurrence);
	
	print_r($recurrence);    
  //echo "<br/>every_N = $every_N - month_position = $month_position";     
	echo "<br/>";
	echo "<ul>";
	foreach($matching_days as $day) {
		echo"<li>".date("D d M Y", $day)."</li>";
	}	          
	echo "</ul>";
	
	echo "<h3>Weekly</h3>";
	$recurrence = array('recurrence_start_date' => '2009-02-10', 'recurrence_end_date' => '2009-04-24', 'recurrence_freq'=>'weekly', 'recurrence_byday'=>7 , 'recurrence_interval' => 3); 
	$matching_days = dbem_get_recurrence_days($recurrence);
	
	print_r($recurrence);    
  //echo "<br/>every_N = $every_N - month_position = $month_position";     
	echo "<br/>";
	echo "<ul>";
	foreach($matching_days as $day) {
		echo"<li>".date("D d M Y", $day)."</li>";
	}	          
	echo "</ul>";  
	
	echo "<h3>Monthly, second week</h3>";
	$recurrence = array('recurrence_start_date' => '2009-02-10', 'recurrence_end_date' => '2009-04-24', 'recurrence_freq'=>'monthly', 'recurrence_byday' => 7, 'recurrence_byweekno'=>2 , 'recurrence_interval' => 1); 
	$matching_days = dbem_get_recurrence_days($recurrence);
	
	print_r($recurrence);    
  //echo "<br/>every_N = $every_N - month_position = $month_position";     
	echo "<br/>";
	echo "<ul>";
	foreach($matching_days as $day) {
		echo"<li>".date("D d M Y", $day)."</li>";
	}	          
	echo "</ul>"; 
	
	echo "<h3>Last week of the month</h3>";  
	$recurrence = array('recurrence_start_date' => '2009-02-10', 'recurrence_end_date' => '2009-04-24', 'recurrence_freq'=>'monthly', 'recurrence_byday' => 7, 'recurrence_byweekno'=> -1 , 'recurrence_interval' => 1); 
	$matching_days = dbem_get_recurrence_days($recurrence);
	
	print_r($recurrence);    
  //echo "<br/>every_N = $every_N - month_position = $month_position";     
	echo "<br/>";
	echo "<ul>";
	foreach($matching_days as $day) {
		echo"<li>".date("D d M Y", $day)."</li>";
	}	          
	echo "</ul>";
}         

function dbem_get_recurrence_days($recurrence){
	
	//print_r($recurrence);
	$start_date = mktime(0, 0, 0, substr($recurrence['recurrence_start_date'],5,2), substr($recurrence['recurrence_start_date'],8,2), substr($recurrence['recurrence_start_date'],0,4));
	$end_date = mktime(0, 0, 0, substr($recurrence['recurrence_end_date'],5,2), substr($recurrence['recurrence_end_date'],8,2), substr($recurrence['recurrence_end_date'],0,4));     
 
//	$every_keys = array('every' => 1, 'every_second' => 2, 'every_third' => 3, 'every_fourth' => 4);  
//	$every_N = $every_keys[$recurrence['recurrence_modifier']]; 
 	
//	$month_position_keys = array('first_of_month'=>1, 'second_of_month' => 2, 'third_of_month' => 3, 'fourth_of_month' => 4);
//	$month_position = $month_position_keys[$recurrence['recurrence_modifier']]; 
	
	$last_week_start = array(25, 22, 25, 24, 25, 24, 25, 25, 24, 25, 24, 25);
	
	$weekdays = explode(",", $recurrence['recurrence_byday']);
	//print_r($weekdays);
	
	$counter = 0;
	$daycounter = 0;
	$weekcounter = 0;
	$monthcounter=0;
	$start_monthday = date("j", $start_date);
	$cycle_date = $start_date;     
	$matching_days = array(); 
	$aDay = 86400;  // a day in seconds  
 
	while (date("d-M-Y", $cycle_date) != date('d-M-Y', $end_date + $aDay)) {
 	 //echo (date("d-M-Y", $cycle_date));
		$style = "";
		$monthweek =  floor(((date("d", $cycle_date)-1)/7))+1;   
		 if($recurrence['recurrence_freq'] == 'daily') {
			if($daycounter % $recurrence['recurrence_interval']== 0)
				array_push($matching_days, $cycle_date);
		}
	     
		if($recurrence['recurrence_freq'] == 'weekly') {
			if (!$recurrence['recurrence_byday']) {
			// no specific days given, so we use 7 days as interval
				if($daycounter % 7*$recurrence['recurrence_interval'] == 0 )
					array_push($matching_days, $cycle_date);
			} elseif (in_array(dbem_iso_N_date_value($cycle_date), $weekdays )) {
			// specific days, so we only check for those days
				if($weekcounter % $recurrence['recurrence_interval'] == 0 )
					array_push($matching_days, $cycle_date);
			}
		}

		if($recurrence['recurrence_freq'] == 'monthly') { 
			$monthday = date("j", $cycle_date); 
			$month = date("n", $cycle_date);      
			// if recurrence_byweekno=none ==> means to use the startday as repeating day
			if ( $recurrence['recurrence_byweekno'] == 'none') {
				if ($monthday == $start_monthday) {
					if ($monthcounter % $recurrence['recurrence_interval'] == 0)
						array_push($matching_days, $cycle_date);
					$counter++;
				}
			} elseif (in_array(dbem_iso_N_date_value($cycle_date), $weekdays )) {
		   		if(($recurrence['recurrence_byweekno'] == -1) && ($monthday >= $last_week_start[$month-1])) {
					if ($monthcounter % $recurrence['recurrence_interval'] == 0)
						array_push($matching_days, $cycle_date);
				} elseif($recurrence['recurrence_byweekno'] == $monthweek) {
					if ($monthcounter % $recurrence['recurrence_interval'] == 0)
						array_push($matching_days, $cycle_date);
			  	}
				$counter++;
			}
		}
		$cycle_date = $cycle_date + $aDay;         //adding a day       
		$daycounter++;
		if ($daycounter%7==0) {
			$weekcounter++;
		}
		if (date("j",$cycle_date)==1) {
			$monthcounter++;
		}
	}   
	
	return $matching_days ;
}



///////////////////////////////////////////////

function dbem_insert_recurrent_event($event, $recurrence ){
	global $wpdb;
	$recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
		
	// never try to update a autoincrement value ...
	if (isset($recurrence['recurrence_id']))
		unset ($recurrence['recurrence_id']);

	//$wpdb->show_errors(true);
	$wpdb->insert($recurrence_table, $recurrence);
	//print_r($recurrence);

 	$recurrence['recurrence_id'] = mysql_insert_id();
 	$event['recurrence_id'] = $recurrence['recurrence_id'];
	dbem_insert_events_for_recurrence($event,$recurrence);
}

function dbem_insert_events_for_recurrence($event,$recurrence) {
	global $wpdb;
	$events_table = $wpdb->prefix.EVENTS_TBNAME;   
	$matching_days = dbem_get_recurrence_days($recurrence);
	//print_r($matching_days);	 
	sort($matching_days);

	foreach($matching_days as $day) {
		$event['event_start_date'] = date("Y-m-d", $day); 
		$event['event_end_date'] = $event['event_start_date']; 
	//$wpdb->show_errors(true);
		$wpdb->insert($events_table, $event);
 	}
}

function dbem_update_events_for_recurrence($event,$recurrence) {
	global $wpdb;
	$events_table = $wpdb->prefix.EVENTS_TBNAME;   
	$matching_days = dbem_get_recurrence_days($recurrence);
	//print_r($matching_days);	 
	sort($matching_days);

	// 2 steps for updating events for a recurrence:
	// First step: check the existing events and if they still match the recurrence days, update them
	// 		otherwise delete the old event
	// Reason for doing this: we want to keep possible booking data for a recurrent event as well
	// and just deleting all current events for a recurrence and inserting new ones would break the link
	// between booking id and event id
	// Second step: check all days of the recurrence and if no event exists yet, insert it
        $sql = "SELECT * FROM $events_table WHERE recurrence_id = '".$recurrence['recurrence_id']."';";
	$events = $wpdb->get_results($sql, ARRAY_A);
	// Doing step 1
	foreach($events as $existing_event) {
		$update_needed=0;
		foreach($matching_days as $day) {
			if (!$update_needed && $existing_event['event_start_date'] == date("Y-m-d", $day)) {
				$update_needed=1; 
			}
		}
		if ($update_needed==1) {
			$where=array('event_id' => $existing_event['event_id']);
			$event['event_start_date'] = $existing_event['event_start_date'];
			$event['event_end_date'] = $event['event_start_date'];
			$wpdb->update($events_table, $event, $where); 
		} else {
        		$sql = "DELETE FROM $events_table WHERE event_id = '".$existing_event['event_id']."';";
			$wpdb->query($sql);
		}
 	}
	// Doing step 2
	foreach($matching_days as $day) {
		$insert_needed=1;
		$event['event_start_date'] = date("Y-m-d", $day);
		$event['event_end_date'] = $event['event_start_date'];
		foreach($events as $existing_event) {
			if ($insert_needed && $existing_event['event_start_date'] == $event['event_start_date']) {
				$insert_needed=0;
			}
		}
		if ($insert_needed==1) {
			$wpdb->insert($events_table, $event);			
		}
	}
	return 1;
}

function dbem_remove_recurrence($recurrence_id) {
        global $wpdb;
	$events_table = $wpdb->prefix.EVENTS_TBNAME;
	$sql = "DELETE FROM $events_table WHERE recurrence_id = '$recurrence_id';";
	$wpdb->query($sql);
	$recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
	$sql = "DELETE FROM $recurrence_table WHERE recurrence_id = '$recurrence_id';";
	$wpdb->query($sql);
}

function dbem_update_recurrence($event, $recurrence) {
	global $wpdb;
	$recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
	$where = array('recurrence_id' => $recurrence['recurrence_id']);
	$wpdb->show_errors(true);
	$wpdb->update($recurrence_table, $recurrence, $where); 
 	$event['recurrence_id'] = $recurrence['recurrence_id'];
	dbem_update_events_for_recurrence($event,$recurrence); 
	return 1;
}

function dbem_remove_events_for_recurrence_id($recurrence_id) {
	global $wpdb;
	$events_table = $wpdb->prefix.EVENTS_TBNAME;
	$sql = "DELETE FROM $events_table WHERE recurrence_id = '$recurrence_id';";
	$wpdb->query($sql);
}

function dbem_get_recurrence($recurrence_id) {
	global $wpdb;
	$events_table = $wpdb->prefix.EVENTS_TBNAME;
	$recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
	$sql = "SELECT * FROM $recurrence_table WHERE recurrence_id = $recurrence_id;";
        $recurrence = $wpdb->get_row($sql, ARRAY_A);

        // now add the info that has no column in the recurrence table
        $sql = "SELECT event_id FROM $events_table WHERE recurrence_id = '$recurrence_id' LIMIT 1;";
	$event_id = $wpdb->get_var($sql);
	$event = dbem_get_event($event_id);
	foreach ($event as $key=>$val) {
		$recurrence[$key]=$val;
	}

        // now add the location info
        $location = dbem_get_location($recurrence['location_id']);
        $recurrence['location_name'] = $location['location_name'];
        $recurrence['location_address'] = $location['location_address'];
        $recurrence['location_town'] = $location['location_town'];
        $recurrence['recurrence_description'] = dbem_get_recurrence_desc($recurrence_id);
        return $recurrence;
}

function dbem_get_recurrence_desc($recurrence_id) {
	global $wpdb;
	$events_table = $wpdb->prefix.EVENTS_TBNAME;
	$recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;
	$sql = "SELECT * FROM $recurrence_table WHERE recurrence_id = $recurrence_id;";
	$recurrence = $wpdb->get_row($sql, ARRAY_A);

	$weekdays_name = array(__('Monday'),__('Tuesday'),__('Wednesday'),__('Thursday'),__('Friday'),__('Saturday'),__('Sunday'));
	$monthweek_name = array('1' => __('the first %s of the month', 'dbem'),'2' => __('the second %s of the month', 'dbem'), '3' => __('the third %s of the month', 'dbem'), '4' => __('the fourth %s of the month', 'dbem'), '-1' => __('the last %s of the month', 'dbem'));
	$output = sprintf (__('From %1$s to %2$s', 'dbem'),  $recurrence['recurrence_start_date'], $recurrence['recurrence_end_date']).", ";
	if ($recurrence['recurrence_freq'] == 'daily')  {
	  
		$freq_desc =__('everyday', 'dbem');
		if ($recurrence['recurrence_interval'] > 1 ) {
			$freq_desc = sprintf (__("every %s days", 'dbem'), $recurrence['recurrence_interval']);
		}
	}
	if ($recurrence['recurrence_freq'] == 'weekly')  {
		$weekday_array = explode(",", $recurrence['recurrence_byday']);
		$natural_days = array();
		foreach($weekday_array as $day)
			array_push($natural_days, $weekdays_name[$day-1]);
		$output .= implode(" and ", $natural_days);
		if ($recurrence['recurrence_interval'] > 1 ) {
			$freq_desc = ", ".sprintf (__("every %s weeks", 'dbem'), $recurrence['recurrence_interval']);
		}
		
	} 
	if ($recurrence['recurrence_freq'] == 'monthly')  {
		 $weekday_array = explode(",", $recurrence['recurrence_byday']);
			$natural_days = array();
			foreach($weekday_array as $day)
				array_push($natural_days, $weekdays_name[$day-1]);
			$freq_desc = sprintf (($monthweek_name[$recurrence['recurrence_byweekno']]), implode(" and ", $natural_days));
		if ($recurrence['recurrence_interval'] > 1 ) {
			$freq_desc .= ", ".sprintf (__("every %s months",'dbem'), $recurrence['recurrence_interval']);
		}
		
	}
	$output .= $freq_desc;
	return  $output;
}

function dbem_iso_N_date_value($date) {
	// date("N", $cycle_date)
	$n = date("w", $date);
	if ($n == 0)
		$n = 7;
	return $n;
}
?>
