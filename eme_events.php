<?php

function eme_new_event_page() {
   // check the user is allowed to make changes
   if ( !current_user_can( MIN_CAPABILITY  ) ) {
      return;
   }

   $title = __ ( "Insert New Event", 'eme' );
   $event = array (
      "event_id" => '',
      "event_name" => '',
      "event_status" => 3,
      "event_date" => '',
      "event_day" => '',
      "event_month" => '',
      "event_year" => '',
      "event_end_date" => '',
      "event_start_date" => '',
      "event_start_time" => '',
      "event_start_12h_time" => '',
      "event_end_time" => '',
      "event_end_12h_time" => '',
      "event_notes" => '',
      "event_rsvp" => 0,
      "rsvp_number_days" => 0,
      "registration_requires_approval" => 0,
      "event_seats" => 0,
      "event_freq" => '',
      "location_id" => 0,
      "event_author" => 0,
      "event_contactperson_id" => 0,
      "event_category_ids" => '',
      "event_attributes" => '',
      "event_page_title_format" => '',
      "event_single_event_format" => '',
      "event_contactperson_email_body" => '',
      "event_respondent_email_body" => '',
      "recurrence_id" => 0,
      "recurrence_interval" => '',
      "recurrence_byweekno" => '',
      "recurrence_byday" => '',
      "location_name" => '',
      "location_address" => '',
      "location_town" => '',
      "location_latitude" => '',
      "location_longitude" => '',
      "location_image_url" => ''
   );
   eme_event_form ($event, $title, '');
}

function eme_events_subpanel() {
   global $wpdb;

   $action = isset($_GET ['action']) ? $_GET ['action'] : '';
   $event_ID = isset($_GET ['event_id']) ? intval($_GET ['event_id']) : '';
   $recurrence_ID = isset($_GET ['recurrence_id']) ? intval($_GET ['recurrence_id']) : '';
   $scope = isset($_GET ['scope']) ? $_GET ['scope'] : '';
   $offset = isset($_GET ['offset']) ? intval($_GET ['offset']) : '';
   $category = isset($_GET ['category']) ? intval($_GET ['category']) : 0;
   $order = isset($_GET ['order']) ? $_GET ['order'] : '';
   $selectedEvents = isset($_GET ['events']) ? $_GET ['events'] : '';
   
   // check the user is allowed to do anything
   if ( !current_user_can( MIN_CAPABILITY ) ) {
      $action="";
   }
   $current_userid=get_current_user_id();

   // Disable Hello to new user if requested
   if (current_user_can( SETTING_CAPABILITY ) && isset ( $_GET ['disable_hello_to_user'] ) && $_GET ['disable_hello_to_user'] == 'true')
      update_option('eme_hello_to_user', 0 );

   // do the UTF-8 conversion if wanted
   if (current_user_can( SETTING_CAPABILITY ) && isset ( $_GET ['do_character_conversion'] ) && $_GET ['do_character_conversion'] == 'true' && $wpdb->has_cap('collation')) {
                if ( ! empty($wpdb->charset)) {
                        $charset = "CHARACTER SET $wpdb->charset";
         $collate="";
         if ( ! empty($wpdb->collate) )
            $collate = "COLLATE $wpdb->collate";
                        eme_convert_charset(EVENTS_TBNAME,$charset,$collate);
                        eme_convert_charset(RECURRENCE_TBNAME,$charset,$collate);
                        eme_convert_charset(LOCATIONS_TBNAME,$charset,$collate);
                        eme_convert_charset(BOOKINGS_TBNAME,$charset,$collate);
                        eme_convert_charset(PEOPLE_TBNAME,$charset,$collate);
                        eme_convert_charset(BOOKING_PEOPLE_TBNAME,$charset,$collate);
                        eme_convert_charset(CATEGORIES_TBNAME,$charset,$collate);
                }
      update_option('eme_conversion_needed', 0 );
      print "<div id=\"message\" class=\"updated\">".__('Conversion done, please check your events and restore from backup if you see any sign of troubles.')."</div>";
   }
   
   if ($order != "DESC")
      $order = "ASC";
   if ($offset == "")
      $offset = "0";
   $event_table_name = $wpdb->prefix . EVENTS_TBNAME;
   // Debug code, to make sure I get the correct page
   
   // DELETE action
   if ($action == 'deleteEvents') {
      foreach ( $selectedEvents as $event_ID ) {
                        $tmp_event = array();
                        $tmp_event = eme_get_event ( $event_ID );
         if (current_user_can( EDIT_CAPABILITY) ||
             (current_user_can( MIN_CAPABILITY) && ($tmp_event['event_author']==$current_userid || $tmp_event['event_contactperson_id']==$current_userid))) {  
            if ($tmp_event['recurrence_id']>0) {
               eme_remove_recurrence ( $tmp_event['recurrence_id'] );
            } else {
               eme_delete_event ( $event_ID );
            }
         }
      }
      
      $events = eme_get_events ( 21, "future", $order, $offset );
      eme_events_table ( $events, 20, "Future events", "future", $offset );
   }

   // UPDATE or CREATE action
   if ($action == 'update_event' || $action == 'update_recurrence') {
      $event = array ();
      $location = array ();
      $event ['event_name'] = isset($_POST ['event_name']) ? stripslashes ( $_POST ['event_name'] ) : '';
      if (!current_user_can( AUTHOR_CAPABILITY)) {
         // user can create an event, but not approve it: status remains draft
         $event['event_status']=STATUS_DRAFT;   
      } else {
         $event ['event_status'] = isset($_POST ['event_status']) ? stripslashes ( $_POST ['event_status'] ) : STATUS_DRAFT;
      }
      // Set event end time to event time if not valid
      // if (!_eme_is_date_valid($event['event_end_date']))
      //    $event['event_end_date'] = $event['event-date'];
      $event ['event_start_date'] = isset($_POST ['event_date']) ? $_POST ['event_date'] : '';
      $event ['event_end_date'] = isset($_POST ['event_end_date']) ? $_POST ['event_end_date'] : '';
      // Trying to fix Alex's trouble
      if ($event ['event_end_date'] == '') 
         $event['event_end_date'] = $event['event_start_date'];
      // End of Alex's fix
      //$event['event_start_time'] = $_POST[event_hh].":".$_POST[event_mm].":00";
      //$event['event_end_time'] = $_POST[event_end_hh].":".$_POST[event_end_mm].":00";
      $event ['event_start_time'] = isset($_POST ['event_start_time']) ? $_POST ['event_start_time'] : '';
      $event ['event_start_time'] = date ( "G:i:00", strtotime ( $event ['event_start_time'] ) );
      $event ['event_end_time'] = isset($_POST ['event_end_time']) ? $_POST ['event_end_time'] : '';
      $event ['event_end_time'] = date ( "G:i:00", strtotime ( $event ['event_end_time'] ) );
      $recurrence ['recurrence_start_date'] = $event ['event_start_date'];
      $recurrence ['recurrence_end_date'] = $event ['event_end_date'];
      $recurrence ['recurrence_freq'] = isset($_POST['recurrence_freq']) ? $_POST['recurrence_freq'] : '';
      if ($recurrence ['recurrence_freq'] == 'weekly') {
         if (isset($_POST['recurrence_bydays'])) {
            $recurrence ['recurrence_byday'] = implode ( ",", $_POST['recurrence_bydays']);
         } else {
            $recurrence ['recurrence_byday'] = '';
         }
      } else {
         if (isset($_POST['recurrence_byday'])) {
            $recurrence ['recurrence_byday'] = $_POST['recurrence_byday'];
         } else {
            $recurrence ['recurrence_byday'] = '';
         }
      }
      $recurrence ['recurrence_interval'] = isset($_POST['recurrence_interval']) ? $_POST['recurrence_interval'] : 1;
      if ($recurrence ['recurrence_interval'] ==0)
         $recurrence['recurrence_interval']=1;
      $recurrence ['recurrence_byweekno'] = isset($_POST['recurrence_byweekno']) ? $_POST ['recurrence_byweekno'] : '';
      
      $event ['event_rsvp'] = (isset ($_POST ['event_rsvp']) && is_numeric($_POST ['event_rsvp'])) ? $_POST ['event_rsvp']:0;
      $event ['rsvp_number_days'] = (isset ($_POST ['rsvp_number_days']) && is_numeric($_POST ['rsvp_number_days'])) ? $_POST ['rsvp_number_days']:0;
      $event ['registration_requires_approval'] = (isset ($_POST ['registration_requires_approval']) && is_numeric($_POST ['registration_requires_approval'])) ? $_POST ['registration_requires_approval']:0;
      $event ['event_seats'] = (isset ($_POST ['event_seats']) && is_numeric($_POST ['event_seats'])) ? $_POST ['event_seats']:0;
      
      if (isset ( $_POST ['event_contactperson_id'] ) && $_POST ['event_contactperson_id'] != '' && $_POST ['event_contactperson_id'] != '-1') {
         $event ['event_contactperson_id'] = $_POST ['event_contactperson_id'];
      } else {
         $event ['event_contactperson_id'] = 0;
      }
      
      //if (! _eme_is_time_valid ( $event_end_time ))
      // $event_end_time = $event_time;
      
      $location ['location_name'] = isset($_POST ['location_name']) ? stripslashes($_POST ['location_name']) : '';
      $location ['location_address'] = isset($_POST ['location_address']) ? stripslashes($_POST ['location_address']) : '';
      $location ['location_town'] = isset($_POST ['location_town']) ? stripslashes($_POST ['location_town']) : '';
      $location ['location_latitude'] = isset($_POST ['location_latitude']) ? $_POST ['location_latitude'] : '';
      $location ['location_longitude'] = isset($_POST ['location_longitude']) ? $_POST ['location_longitude'] : '';
      $location ['location_description'] = "";
      //switched to WP TinyMCE field
      //$event ['event_notes'] = stripslashes ( $_POST ['event_notes'] );
      $event ['event_notes'] = isset($_POST ['content']) ? stripslashes($_POST ['content']) : '';
      $event ['event_page_title_format'] = stripslashes ( $_POST ['event_page_title_format'] );
      $event ['event_single_event_format'] = stripslashes ( $_POST ['event_single_event_format'] );
      $event ['event_contactperson_email_body'] = stripslashes ( $_POST ['event_contactperson_email_body'] );
      $event ['event_respondent_email_body'] = stripslashes ( $_POST ['event_respondent_email_body'] );
                if (isset ($_POST['event_category_ids'])) {
         // the category id's need to begin and end with a comma
         // this is needed so we can later search for a specific
         // cat using LIKE '%,$cat,%'
         $event ['event_category_ids']="";
         foreach ($_POST['event_category_ids'] as $cat) {
            if (is_numeric($cat)) {
               if (empty($event ['event_category_ids'])) {
                  $event ['event_category_ids'] = "$cat";
                              } else {
                  $event ['event_category_ids'] .= ",$cat";
                              }
            }
         }
                } else {
         $event ['event_category_ids']="";
         
      }
      $validation_result = eme_validate_event ( $event );
      
      /* Marcus Begin Edit */
      $event_attributes = array();
      for($i=1 ; isset($_POST["mtm_{$i}_ref"]) && trim($_POST["mtm_{$i}_ref"])!='' ; $i++ ){
         if(trim($_POST["mtm_{$i}_name"]) != ''){
            $event_attributes[$_POST["mtm_{$i}_ref"]] = stripslashes($_POST["mtm_{$i}_name"]);
         }
      }
      $event['event_attributes'] = serialize($event_attributes);
      /* Marcus End Edit */
      
      if ($validation_result == "OK") {
         // validation successful
         if(isset($_POST['location-select-id']) && $_POST['location-select-id'] != "") {
            $event ['location_id'] = $_POST['location-select-id'];
         } else {
            $related_location = eme_get_identical_location ( $location );
            // print_r($related_location); 
            if ($related_location) {
               $event ['location_id'] = $related_location ['location_id'];
            } else {
               $new_location = eme_insert_location ( $location );
               $event ['location_id'] = $new_location ['location_id'];
            }
         }
         if (! $event_ID && ! $recurrence_ID) {
            $event['event_author']=$current_userid;
            // new event or new recurrence
            if (isset($_POST ['repeated_event']) && $_POST ['repeated_event']) {
               //insert new recurrence
               eme_insert_recurrent_event ( $event, $recurrence );
               $feedback_message = __ ( 'New recurrent event inserted!', 'eme' );
            } else {
               // INSERT new event 
               $wpdb->show_errors(true);
               if (!$wpdb->insert ( $event_table_name, $event )) {
                  $wpdb->print_error();
                  $feedback_message = __ ( 'Database insert failed!', 'eme' );
               } else {
                  $feedback_message = __ ( 'New event successfully inserted!', 'eme' );
               }
            }
         } else {
            // something exists
            if ($recurrence_ID) {
               $tmp_recurrence = eme_get_recurrence ( $recurrence_ID );
               if (current_user_can( EDIT_CAPABILITY) ||
                   (current_user_can( MIN_CAPABILITY) && ($tmp_recurrence['event_author']==$current_userid || $tmp_recurrence['event_contactperson_id']==$current_userid))) {
                  // UPDATE old recurrence
                  $recurrence ['recurrence_id'] = $recurrence_ID;
                  //print_r($recurrence); 
                  if (eme_update_recurrence ($event, $recurrence ))
                     $feedback_message = __ ( 'Recurrence updated!', 'eme' );
                  else
                     $feedback_message = __ ( 'Something went wrong with the recurrence update...', 'eme' );
               } else {
                  $feedback_message = __('You have no right to update','eme'). " '" . $tmp_event ['event_name'] . "' !";
               }
            } else {
               $tmp_event = eme_get_event ( $event_ID );
               if (current_user_can( EDIT_CAPABILITY) ||
                   (current_user_can( MIN_CAPABILITY) && ($tmp_event['event_author']==$current_userid || $tmp_event['event_contactperson_id']==$current_userid))) {
                  if (isset($_POST ['repeated_event']) && $_POST ['repeated_event']) {
                     // we go from single event to recurrence: create the recurrence and delete the single event
                     eme_insert_recurrent_event ( $event, $recurrence );
                     eme_delete_event ( $event_ID );
                     $feedback_message = __ ( 'New recurrent event inserted!', 'eme' );
                  } else {
                     // UPDATE old event
                     // unlink from recurrence in case it was generated by one
                     $event ['recurrence_id'] = 0;
                     $where ['event_id'] = $event_ID;
                     $result = $wpdb->update ( $event_table_name, $event, $where );
                     $feedback_message = "'" . $event ['event_name'] . "' " . __ ( 'updated', 'eme' ) . "!";
                  }
               } else {
                  $feedback_message = __('You have no right to update','eme'). " '" . $tmp_event ['event_name'] . "' !";
               }
            }
         }
         
         //$wpdb->query($sql); 
         echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
         $events = eme_get_events ( 21, "future" );
         eme_events_table ( $events, 20, "Future events", "future", $offset );
      } else {
         // validation unsuccessful       
         echo "<div id='message' class='error '>
                  <p>" . __ ( "Ach, there's a problem here:", "eme" ) . " $validation_result</p>
              </div>";
         eme_event_form ( $event, "Edit event $event_ID", $event_ID );
      }
   }
   if ($action == 'edit_event') {
      if (! $event_ID) {
         $title = __ ( "Insert New Event", 'eme' );
         eme_event_form ( $event, $title, $event_ID );
      } else {
         $event = eme_get_event ( $event_ID );
         if (current_user_can( EDIT_CAPABILITY) ||
             (current_user_can( MIN_CAPABILITY) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
                     // UPDATE old event
            $title = __ ( "Edit Event", 'eme' ) . " '" . $event ['event_name'] . "'";
            eme_event_form ( $event, $title, $event_ID );
         } else {
            $feedback_message = __('You have no right to update','eme'). " '" . $event ['event_name'] . "' !";
            echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
            $events = eme_get_events ( 21, "future" );
            eme_events_table ( $events, 20, "Future events", "future", $offset );
         }
      }
      
   }

   //Add duplicate event if requested
   if ($action == 'duplicate_event') {
      $event = eme_get_event ( $event_ID );
      if (current_user_can( EDIT_CAPABILITY) ||
          (current_user_can( MIN_CAPABILITY) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid))) {
         eme_duplicate_event ( $event_ID );
      } else {
         $feedback_message = __('You have no right to update','eme'). " '" . $event ['event_name'] . "' !";
         echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
         $events = eme_get_events ( 21, "future" );
         eme_events_table ( $events, 20, "Future events", "future", $offset );
      }
   }
   if ($action == 'edit_recurrence') {
      $event_ID = intval($_GET ['recurrence_id']);
      $recurrence = eme_get_recurrence ( $event_ID );
      if (current_user_can( EDIT_CAPABILITY) ||
          (current_user_can( MIN_CAPABILITY) && ($recurrence['event_author']==$current_userid || $recurrence['event_contactperson_id']==$current_userid))) {
         $title = __ ( "Reschedule", 'eme' ) . " '" . $recurrence ['event_name'] . "'";
         eme_event_form ( $recurrence, $title, $event_ID );
      } else {
         $feedback_message = __('You have no right to update','eme'). " '" . $recurrence ['event_name'] . "' !";
         echo "<div id='message' class='updated fade'><p>".eme_trans_sanitize_html($feedback_message)."</p></div>";
         $events = eme_get_events ( 21, "future" );
         eme_events_table ( $events, 20, "Future events", "future", $offset );
      }
   }
   
   if ($action == "-1" || $action == "") {
      // No action, only showing the events list
      switch ($scope) {
         case "past" :
            $title = __ ( 'Past Events', 'eme' );
            break;
         case "all" :
            $title = __ ( 'All Events', 'eme' );
            break;
         default :
            $title = __ ( 'Future Events', 'eme' );
            $scope = "future";
      }
      $events = eme_get_events ( 21, $scope, $order, $offset, "", $category );
      eme_events_table ( $events, 20, $title, $scope, $offset, $category );
   }
}

// array of all pages, bypasses the filter I set up :)
function eme_get_all_pages() {
   global $wpdb;
   $query = "SELECT id, post_title FROM " . $wpdb->prefix . "posts WHERE post_type = 'page' AND post_status='publish'";
   $pages = $wpdb->get_results ( $query, ARRAY_A );
   // get_pages() is better, but uses way more memory and it might be filtered by eme_filter_get_pages()
   //$pages = get_pages();
   $output = array ();
   $output [] = __( 'Please select a page','eme' );
   foreach ( $pages as $page ) {
      $output [$page ['id']] = $page ['post_title'];
   // $output [$page->ID] = $page->post_title;
   }
   return $output;
}

// Function composing the options subpanel
function eme_options_subpanel() {
   ?>
<div class="wrap">
<div id='icon-options-general' class='icon32'><br />
</div>
<h2><?php _e ( 'Event Manager Options', 'eme' ); ?></h2>
<?php admin_show_warnings();?>
<form id="eme_options_form" method="post" action="options.php">
<h3><?php _e ( 'General options', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Use dropdown for locations?' ), 'eme_use_select_for_locations', __ ( 'Select yes to select location from a drop-down menu; location selection will be faster, but you will lose the ability to insert locations with events.','eme' )."<br/>".__ ( 'When the qtranslate plugin is installed and activated, this setting will be ignored and always considered \'Yes\'.','eme' ) );
   eme_options_radio_binary ( __ ( 'Use recurrence?' ), 'eme_recurrence_enabled', __ ( 'Select yes to enable the possiblity to create recurrent events.','eme' ) ); 
   eme_options_radio_binary ( __ ( 'Use RSVP?' ), 'eme_rsvp_enabled', __ ( 'Select yes to enable the RSVP feature so people can register for an event and book places.','eme' ) );
   eme_options_radio_binary ( __ ( 'Use categories?' ), 'eme_categories_enabled', __ ( 'Select yes to enable the category features.','eme' ) );
   eme_options_radio_binary ( __ ( 'Use attributes?' ), 'eme_attributes_enabled', __ ( 'Select yes to enable the attributes feature.','eme' ) );
   eme_options_radio_binary ( __ ( 'Enable Google Maps integration?' ), 'eme_gmap_is_active', __ ( 'Check this option to enable Google Map integration.','eme' ) );
   eme_options_radio_binary ( __ ( 'Delete all EME data when uninstalling?', 'eme' ), 'eme_uninstall_drop_data', __ ( 'Check this option if you want to delete all EME data (database tables and options) when deactivating the plugin.', 'eme' ) );
   ?>
</table>
<h3><?php _e ( 'Events page', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_select ( __ ( 'Events page' ), 'eme_events_page', eme_get_all_pages (), __ ( 'This option allows you to select which page to use as an events page.', 'eme' )."<br/><strong>".__ ( 'The content of this page (including shortcodes of any kind) will be ignored completely and dynamically replaced by events data.','eme' )."</strong>" );
   eme_options_radio_binary ( __ ( 'Show events page in lists?', 'eme' ), 'eme_list_events_page', __ ( 'Check this option if you want the events page to appear together with other pages in pages lists.', 'eme' )."<br/><strong>".__ ( 'This option should no longer be used, it will be deprecated. Using the [events_list] shortcode in a self created page is recommended.', 'eme' )."</strong>" ); 
   eme_options_radio_binary ( __ ( 'Display calendar in events page?', 'eme' ), 'eme_display_calendar_in_events_page', __ ( 'This options allows to display the calendar in the events page, instead of the default list. It is recommended not to display both the calendar widget and a calendar page.','eme' ) );
   ?>
</table>
<h3><?php _e ( 'Events format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_radio_binary ( __ ( 'Remove leading zeros from minutes?', 'eme' ), 'eme_time_remove_leading_zeros', __ ( 'PHP date/time functions have no notation to show minutes without leading zeros. Checking this option will return eg. 9 for 09 and empty for 00.', 'eme' ) ); 
   eme_options_textarea ( __ ( 'Default event list format header', 'eme' ), 'eme_event_list_item_format_header', __( 'This content will appear just above your code for the default event list format. Default is <code>&lt;ul class=\'eme_events_list\'&gt;</code>', 'eme' ) );
   eme_options_textarea ( __ ( 'Default event list format', 'eme' ), 'eme_event_list_item_format', __ ( 'The format of any events in a list.<br/>Insert one or more of the following placeholders: <code>#_NAME</code>, <code>#_LOCATION</code>, <code>#_ADDRESS</code>, <code>#_TOWN</code>, <code>#_NOTES</code>.<br/> Use <code>#_EXCERPT</code> to show <code>#_NOTES</code> until you place a <code>&lt;!&ndash;&ndash;more&ndash;&ndash;&gt;</code> marker.<br/> Use <code>#_LINKEDNAME</code> for the event name with a link to the given event page.<br/> Use <code>#_EVENTPAGEURL</code> to print the event page URL and make your own customised links.<br/> Use <code>#_LOCATIONPAGEURL</code> to print the location page URL and make your own customised links.<br/>Use <code>#_EDITEVENTLINK</code> to add add a link to edit page for the event, which will appear only when a user is logged in.<br/>To insert date and time values, use <a href="http://www.php.net/manual/en/function.date.php">PHP time format characters</a>  with a <code>#</code> symbol before them, i.e. <code>#m</code>, <code>#M</code>, <code>#j</code>, etc.<br/> For the end time, put <code>#@</code> in front of the character, ie. <code>#@h</code>, <code>#@i</code>, etc.<br/> You can also create a date format without prepending <code>#</code> by wrapping it in #_{} or #@_{} (e.g. <code>#_{d/m/Y}</code>). If there is no end date, the value is not shown.<br/>Use <code>#_12HSTARTTIME</code> and <code>#_12HENDTIME</code> for AM/PM starttime/endtime notation, idem <code>#_24HSTARTTIME</code> and <code>#_24HENDTIME</code>.<br/>Feel free to use HTML tags as <code>li</code>, <code>br</code> and so on.<br/>For custom attributes, you use <code>#_ATT{key}{alternative text}</code>, the second braces are optional and will appear if the attribute is not defined or left blank for that event. This key will appear as an option when adding attributes to your event.', 'eme' )."<br>".__('Use <code>#_PAST_FUTURE_CLASS</code> to return a class name indicating this event is future or past (<code>eme-future-event</code> or <code>eme-past-event</code>), use the returned value in eg. the li-statement for each event in the list of events','eme') );
   eme_options_textarea ( __ ( 'Default event list format footer', 'eme' ), 'eme_event_list_item_format_footer', __ ( 'This content will appear just below your code for the default event list format. Default is <code>&lt;/ul&gt;</code>', 'eme' ) );

   eme_options_input_text ( __ ( 'Single event page title format', 'eme' ), 'eme_event_page_title_format', __ ( 'The format of a single event page title. Follow the previous formatting instructions.', 'eme' ) );
   eme_options_textarea ( __ ( 'Default single event format', 'eme' ), 'eme_single_event_format', __ ( 'The format of a single event page.<br/>Follow the previous formatting instructions. <br/>Use <code>#_MAP</code> to insert a map.<br/>Use <code>#_CONTACTNAME</code>, <code>#_CONTACTEMAIL</code>, <code>#_CONTACTPHONE</code> to insert respectively the name, e-mail address and phone number of the designated contact person. <br/>Use <code>#_ADDBOOKINGFORM</code> to insert a form to allow the user to respond to your events reserving one or more places (RSVP).<br/> Use <code>#_REMOVEBOOKINGFORM</code> to insert a form where users, inserting their name and e-mail address, can remove their bookings.', 'eme' ).__('<br/> Use <code>#_DIRECTIONS</code> to insert a form so people can ask directions to the event.','eme').__('<br/> Use <code>#_CATEGORIES</code> to insert a comma seperated list of categories an event is in.','eme').__('<br/> Use <code>#_ATTENDEES</code> to get a list of the names attending the event.','eme') );
   eme_options_input_text ( __ ( 'Monthly period date format', 'eme' ), 'eme_show_period_monthly_dateformat', __ ( 'The format of the datestring used when you use showperiod=monthly as an option to &#91;the events_list] shortcode. Use php date() compatible settings.', 'eme') . __( ' The default is: '). DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT );
   eme_options_input_text ( __ ( 'Events page title', 'eme' ), 'eme_events_page_title', __ ( 'The title on the multiple events page.', 'eme' ) );
   eme_options_input_text ( __ ( 'No events message', 'eme' ), 'eme_no_events_message', __ ( 'The message displayed when no events are available.', 'eme' ) );
   ?>
</table>
<h3><?php _e ( 'Calendar format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_input_text ( __ ( 'Small calendar title', 'eme' ), 'eme_small_calendar_event_title_format', __ ( 'The format of the title, corresponding to the text that appears when hovering on an eventful calendar day.', 'eme' ) );
   eme_options_input_text ( __ ( 'Small calendar title separator', 'eme' ), 'eme_small_calendar_event_title_separator', __ ( 'The separator appearing on the above title when more than one events are taking place on the same day.', 'eme' ) );
   eme_options_input_text ( __ ( 'Full calendar events format', 'eme' ), 'eme_full_calendar_event_format', __ ( 'The format of each event when displayed in the full calendar. Remember to include <code>li</code> tags before and after the event.', 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'Locations format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_input_text ( __ ( 'Single location page title format', 'eme' ), 'eme_location_page_title_format', __ ( 'The format of a single location page title.<br/>Follow the previous formatting instructions.', 'eme' ) );
   eme_options_textarea ( __ ( 'Default single location page format', 'eme' ), 'eme_single_location_format', __ ( 'The format of a single location page.<br/>Insert one or more of the following placeholders: <code>#_NAME</code>, <code>#_ADDRESS</code>, <code>#_TOWN</code>, <code>#_DESCRIPTION</code>.<br/> Use <code>#_MAP</code> to display a map of the event location, and <code>#_IMAGE</code> to display an image of the location.<br/> Use <code>#_NEXTEVENTS</code> to insert a list of the upcoming events, <code>#_PASTEVENTS</code> for a list of past events, <code>#_ALLEVENTS</code> for a list of all events taking place in this location.', 'eme' ) );
   eme_options_textarea ( __ ( 'Default location balloon format', 'eme' ), 'eme_location_baloon_format', __ ( 'The format of of the text appearing in the balloon describing the location in the map.<br/>Insert one or more of the following placeholders: <code>#_NAME</code>, <code>#_ADDRESS</code>, <code>#_TOWN</code>, <code>#_DESCRIPTION</code> or <code>#_IMAGE</code>.', 'eme' ) . __('You can also use <code>#_DIRECTIONS</code> to insert a direction form in the balloon, but then you should add extra 6 &lt;br/&gt;-tags after it, otherwise the balloon will not be of the correct height.','eme') );
   eme_options_textarea ( __ ( 'Default location event list format', 'eme' ), 'eme_location_event_list_item_format', __ ( 'The format of the events the list inserted in the location page through the <code>#_NEXTEVENTS</code>, <code>#_PASTEVENTS</code> and <code>#_ALLEVENTS</code> element. <br/> Follow the events formatting instructions', 'eme' ) );
   eme_options_textarea ( __ ( 'Default no events message', 'eme' ), 'eme_location_no_events_message', __ ( 'The message to be displayed in the list generated by <code>#_NEXTEVENTS</code>, <code>#_PASTEVENTS</code> and <code>#_ALLEVENTS</code> when no events are available.', 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'RSS feed format', 'eme' ); ?></h3>
<table class="form-table">
   <?php
   eme_options_input_text ( __ ( 'RSS main title', 'eme' ), 'eme_rss_main_title', __ ( 'The main title of your RSS events feed.', 'eme' ) );
   eme_options_input_text ( __ ( 'RSS main description', 'eme' ), 'eme_rss_main_description', __ ( 'The main description of your RSS events feed.', 'eme' ) );
   eme_options_input_text ( __ ( 'RSS title format', 'eme' ), 'eme_rss_title_format', __ ( 'The format of the title of each item in the events RSS feed.', 'eme' ) );
   eme_options_input_text ( __ ( 'RSS description format', 'eme' ), 'eme_rss_description_format', __ ( 'The format of the description of each item in the events RSS feed. Follow the previous formatting instructions.', 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'RSVP: registrations and bookings', 'eme' ); ?></h3>
<table class='form-table'>
     <?php
   eme_options_select ( __ ( 'Default contact person', 'eme' ), 'eme_default_contact_person', eme_get_indexed_users (), __ ( 'Select the default contact person. This user will be employed whenever a contact person is not explicitly specified for an event', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Require WP membership to be able to register?', 'eme' ), 'eme_rsvp_registered_users_only', __ ( 'Check this option if you want that only WP registered users can book for an event.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'By default enable registrations for new events?', 'eme' ), 'eme_rsvp_reg_for_new_events', __ ( 'Check this option if you want to enable registrations by default for new events.', 'eme' ) );
   eme_options_input_text ( __ ( 'Default number of spaces', 'eme' ), 'eme_rsvp_default_number_spaces', __ ( 'The default number of spaces an event has.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Use captcha for booking form?', 'eme' ), 'eme_captcha_for_booking', __ ( 'Check this option if you want to use a captcha on the booking form, to thwart spammers a bit.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Hide fully booked events?', 'eme' ), 'eme_rsvp_hide_full_events', __ ( 'Check this option if you want to hide events that are fully booked from the calendar and events listing in the front.', 'eme' ) );
   eme_options_radio_binary ( __ ( 'Enable the RSVP e-mail notifications?', 'eme' ), 'eme_rsvp_mail_notify_is_active', __ ( 'Check this option if you want to receive an email when someone books places for your events.', 'eme' ) );
   eme_options_textarea ( __ ( 'Contact person email format', 'eme' ), 'eme_contactperson_email_body', __ ( 'The format of the email which will be sent to the contact person. Follow the events formatting instructions. <br/>Use <code>#_RESPNAME</code>, <code>#_RESPEMAIL</code> and <code>#_RESPPHONE</code> to display respectively the name, e-mail, address and phone of the respondent.<br/>Use <code>#_SPACES</code> to display the number of spaces reserved by the respondent. Use <code>#_COMMENT</code> to display the respondent\'s comment. <br/> Use <code>#_RESERVEDSPACES</code> and <code>#_AVAILABLESPACES</code> to display respectively the number of booked and available seats.', 'eme' ) );
   eme_options_textarea ( __ ( 'Respondent email format', 'eme' ), 'eme_respondent_email_body', __ ( 'The format of the email which will be sent to the respondent. Follow the events formatting instructions. <br/>Use <code>#_RESPNAME</code> to display the name of the respondent.<br/>Use <code>#_CONTACTNAME</code> and <code>#_PLAIN_CONTACTEMAIL</code> to display respectively the name and e-mail of the contact person.<br/>Use <code>#_SPACES</code> to display the number of spaces reserved by the respondent. Use <code>#_COMMENT</code> to display the respondent\'s comment.', 'eme' ) );
   eme_options_textarea ( __ ( 'Registration pending email format', 'eme' ), 'eme_registration_pending_email_body', __ ( 'The format of the email which will be sent to the respondent when the event requires registration approval.', 'eme' ) );
   eme_options_textarea ( __ ( 'Registration denied email format', 'eme' ), 'eme_registration_denied_email_body', __ ( 'The format of the email which will be sent to the respondent when the admin denies the registration request if the event requires registration approval.', 'eme' ) );
   eme_options_input_text ( __ ( 'Notification sender name', 'eme' ), 'eme_mail_sender_name', __ ( "Insert the display name of the notification sender.", 'eme' ) );
   eme_options_input_text ( __ ( 'Notification sender address', 'eme' ), 'eme_mail_sender_address', __ ( "Insert the address of the notification sender. It must corresponds with your gmail account user", 'eme' ) );
   eme_options_input_text ( __ ( 'Default notification receiver address', 'eme' ), 'eme_mail_receiver_address', __ ( "Insert the address of the receiver of your notifications", 'eme' ) );
   eme_options_input_text ( __ ( 'Add booking form submit text', 'eme' ), 'eme_rsvp_addbooking_submit_string', __ ( "The string of the submit button on the add booking form", 'eme' ) );
   eme_options_input_text ( __ ( 'Delete booking form submit text', 'eme' ), 'eme_rsvp_delbooking_submit_string', __ ( "The string of the submit button on the delete booking form", 'eme' ) );
   eme_options_input_text ( __ ( 'Attendees list format', 'eme' ), 'eme_attendees_list_format', __ ( "The format for the attendees list when using the <code>#_ATTENDEES</code> placeholder. Use <code>#_NAME</code>, <code>#_EMAIL</code>, <code>#_PHONE</code>, <code>#_ID</code>.", 'eme' ) );
   eme_options_select ( __ ( 'Mail sending method', 'eme' ), 'eme_rsvp_mail_send_method', array ('smtp' => 'SMTP', 'mail' => __ ( 'PHP mail function', 'eme' ), 'sendmail' => 'Sendmail', 'qmail' => 'Qmail' ), __ ( 'Select the method to send email notification.', 'eme' ) );
   eme_options_input_text ( 'Mail sending port', 'eme_rsvp_mail_port', __ ( "The port through which you e-mail notifications will be sent. Make sure the firewall doesn't block this port", 'eme' ) );
   eme_options_radio_binary ( __ ( 'Use SMTP authentication?', 'eme' ), 'eme_rsvp_mail_SMTPAuth', __ ( 'SMTP authentication is often needed. If you use GMail, make sure to set this parameter to Yes', 'eme' ) );
   eme_options_input_text ( 'SMTP host', 'eme_smtp_host', __ ( "The SMTP host. Usually it corresponds to 'localhost'. If you use GMail, set this value to 'ssl://smtp.gmail.com:465'.", 'eme' ) );
   eme_options_input_text ( __ ( 'SMTP username', 'eme' ), 'eme_smtp_username', __ ( "Insert the username to be used to access your SMTP server.", 'eme' ) );
   eme_options_input_password ( __ ( 'SMTP password', 'eme' ), "eme_smtp_password", __ ( "Insert the password to be used to access your SMTP server", 'eme' ) );
   ?>
</table>

<h3><?php _e ( 'Images size', 'eme' ); ?></h3>
<table class='form-table'>
   <?php
   eme_options_input_text ( __ ( 'Maximum width (px)', 'eme' ), 'eme_image_max_width', __ ( 'The maximum allowed width for images uploaded, in pixels', 'eme' ) );
   eme_options_input_text ( __ ( 'Maximum height (px)', 'eme' ), 'eme_image_max_height', __ ( "The maximum allowed height for images uploaded, in pixels", 'eme' ) );
   eme_options_input_text ( __ ( 'Maximum size (bytes)', 'eme' ), 'eme_image_max_size', __ ( "The maximum allowed size for images uploaded, in bytes", 'eme' ) );
   ?>
</table>

<p class="submit"><input type="submit" id="eme_options_submit" name="Submit" value="<?php _e ( 'Save Changes' )?>" /></p>
   <?php
   settings_fields ( 'eme-options' );
   ?> 
</form>
</div>
<?php

}

//This is the content of the event page
function eme_events_page_content() {
   global $wpdb;
   if (isset ( $_REQUEST ['location_id'] ) && $_REQUEST ['location_id'] |= '') {
      $location = eme_get_location ( intval($_REQUEST ['location_id']));
      $single_location_format = get_option('eme_single_location_format' );
      $page_body = eme_replace_locations_placeholders ( $single_location_format, $location );
      return $page_body;
   }
   //if (isset ( $_REQUEST ['event_id'] ) && $_REQUEST ['event_id'] != '') {
   if (eme_is_single_event_page()) {
      // single event page
      $event_ID = intval($_REQUEST ['event_id']);
      $event = eme_get_event ( $event_ID );
      $single_event_format = ( $event['event_single_event_format'] != '' ) ? $event['event_single_event_format'] : get_option('eme_single_event_format' );
      //$page_body = eme_replace_placeholders ( $single_event_format, $event, 'stop' );
      $page_body = eme_replace_placeholders ( $single_event_format, $event );
      return $page_body;
   } elseif (isset ( $_REQUEST ['calendar_day'] ) && $_REQUEST ['calendar_day'] != '') {
      $scope = eme_sanitize_request($_REQUEST ['calendar_day']);
      $events_N = eme_events_count_for ( $scope );
      if ($events_N > 1) {
         $stored_format = get_option('eme_event_list_item_format' );
         //Add headers and footers to the events list
         $single_event_format_header = get_option('eme_event_list_item_format_header' );
         $single_event_format_header = ( $single_event_format_header != '' ) ? $single_event_format_header : "<ul class='eme_events_list'>";
         $single_event_format_footer = get_option('eme_event_list_item_format_footer' );
         $single_event_format_footer = ( $single_event_format_footer != '' ) ? $single_event_format_footer : "</ul>";
         return $single_event_format_header .  eme_get_events_list ( 0, $scope, "ASC", $stored_format, $false ) . $single_event_format_footer;
      } else {
         $events = eme_get_events ( 0, $scope);
         $event = $events [0];
         $single_event_format = ( $event['event_single_event_format'] != '' ) ? $event['event_single_event_format'] : get_option('eme_single_event_format' );
         $page_body = eme_replace_placeholders ( $single_event_format, $event );
         return $page_body;
      }
   } else {
      // Multiple events page
      $_GET ['scope'] ? $scope = eme_sanitize_request($_GET ['scope']) : $scope = "future";
      $stored_format = get_option('eme_event_list_item_format' );
      if (get_option('eme_display_calendar_in_events_page' )){
         $events_body = eme_get_calendar ('full=1');
      }else{
         $single_event_format_header = get_option('eme_event_list_item_format_header' );
         $single_event_format_header = ( $single_event_format_header != '' ) ? $single_event_format_header : "<ul class='eme_events_list'>";
         $single_event_format_footer = get_option('eme_event_list_item_format_footer' );
         $single_event_format_footer = ( $single_event_format_footer != '' ) ? $single_event_format_footer : "</ul>";
         $events_body = $single_event_format_header . eme_get_events_list ( 10, $scope, "ASC", $stored_format, $false ) . $single_event_format_footer;
      }
      return $events_body;
   }
}

function eme_events_count_for($date) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;
   $conditions = array ();
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $conditions [] = "event_status IN (1,2)";
      } else {
         $conditions [] = "event_status=1";
      }
   }
   $conditions [] = "((event_start_date  like '$date') OR (event_start_date <= '$date' AND event_end_date >= '$date'))";
   $where = implode ( " AND ", $conditions );
   if ($where != "")
      $where = " WHERE " . $where;
   $sql = "SELECT COUNT(*) FROM  $table_name $where";
   return $wpdb->get_var ( $sql );
}

// filter function to call the event page when appropriate
function eme_filter_events_page($data) {
   // we change the content of the page only if we're "in the loop",
   // otherwise this filter also gets applied if eg. a widget calls
   // the_content or the_excerpt to get the content of a page
   if (in_the_loop() && eme_is_events_page()) {
      return eme_events_page_content ();
   } else {
      return $data;
   }
}
add_filter ( 'the_content', 'eme_filter_events_page' );

function eme_event_page_title($data) {
   $events_page_id = get_option('eme_events_page' );
   $events_page = get_page ( $events_page_id );
   $events_page_title = $events_page->post_title;
   
   //if (($data == $events_page_title) && (is_page ( $events_page_id ))) {
   if (($data == $events_page_title) && eme_is_events_page()) {
      if (isset ( $_REQUEST['calendar_day'] ) && $_REQUEST['calendar_day'] != '') {
         
         $date = eme_sanitize_request($_REQUEST['calendar_day']);
         $events_N = eme_events_count_for ( $date );
         
         if ($events_N == 1) {
            $events = eme_get_events ( 0, eme_sanitize_request($_REQUEST['calendar_day']));
            $event = $events [0];
            $stored_page_title_format = ( $event['event_page_title_format'] != '' ) ? $event['event_page_title_format'] : get_option('eme_event_page_title_format' );
            $page_title = eme_replace_placeholders ( $stored_page_title_format, $event );
            return $page_title;
         }
      }
      
      if (isset ( $_REQUEST['location_id'] ) && $_REQUEST['location_id'] |= '') {
         $location = eme_get_location ( intval($_REQUEST['location_id']));
         $stored_page_title_format = get_option('eme_location_page_title_format' );
         $page_title = eme_replace_locations_placeholders ( $stored_page_title_format, $location );
         return $page_title;
      }
      if (eme_is_single_event_page()) {
         // single event page
         $event_ID = intval($_REQUEST['event_id']);
         $event = eme_get_event ( $event_ID );
         $stored_page_title_format = ( $event['event_page_title_format'] != '' ) ? $event['event_page_title_format'] : get_option('eme_event_page_title_format' );
         $page_title = eme_replace_placeholders ( $stored_page_title_format, $event );
         return $page_title;
      } else {
         // Multiple events page
         $page_title = get_option('eme_events_page_title' );
         return $page_title;
      }
   } else {
      return $data;
   }
}
add_filter ( 'single_post_title', 'eme_event_page_title' );

function eme_filter_the_title($data) {
   if (in_the_loop() && eme_is_events_page()) {
      return eme_event_page_title($data);
   } else {
      return $data;
   }
}
add_filter ( 'the_title', 'eme_filter_the_title' );

// filter out the events page in the get_pages call
function eme_filter_get_pages($data) {
   $output = array ();
   $events_page_id = get_option('eme_events_page' );
   for($i = 0; $i < count ( $data ); ++ $i) {
      if(isset($data [$i])) {
         if ($data [$i]->ID == $events_page_id) {
            $list_events_page = get_option('eme_list_events_page' );
            if ($list_events_page) {
               $output [] = $data [$i];
            }
         } else {
            $output [] = $data [$i];
         }
      }
   }
   return $output;
}
add_filter ( 'get_pages', 'eme_filter_get_pages' );

//
// ADMIN CSS for debug
function eme_admin_css() {
   $css = "
   <style type='text/css'>
   .debug{
      color: green;
      background: #B7F98C;
      margin: 15px;
      padding: 10px;
      border: 1px solid #629948;
   }
   .switch-tab {
      background: #aaa;
      width: 100px;
      float: right;
      text-align: center;
      margin: 3px 1px 0 5px;
      padding: 2px;
   }
   .switch-tab a {
      color: #fff;
      text-decoration: none;
   }
   .switch-tab a:hover {
      color: #D54E21;
      
   } 
   #events-pagination {
      text-align: center; 
   }
   #events-pagination a {
      margin: 0 20px 0 20px;
      text-decoration: none;
      width: 80px;
      padding: 3px 0; 
      background: #FAF4B7;
      border: 1px solid #ccc;
      border-top: none;
   } 
   #new-event {
      float: left;
   }
   </style>";
   echo $css;
}

add_action ( 'admin_print_scripts', 'eme_admin_css' );

// TEMPLATE TAGS


// exposed function, for theme  makers
   //Added a category option to the get events list method and shortcode
function eme_get_events_list($limit = 10, $scope = "future", $order = "ASC", $format = '', $echo = 1, $category = '',$showperiod = '', $long_events = 0, $author = '', $paging=0) {
   global $post;
   if (strpos ( $limit, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('limit' => 10, 'scope' => 'future', 'order' => 'ASC', 'format' => '', 'echo' => 1 , 'category' => '', 'showperiod' => '', $author => '', $paging=0,'long_events' => 0);
      
      $r = wp_parse_args ( $limit, $defaults );
      extract ( $r );
      $echo = (bool) $r ['echo'];
      // for AND categories: the user enters "+" and this gets translated to " " by wp_parse_args
      $category = ( preg_match('/^([0-9][, ]?)+$/', $r ['category'] ) ) ? $r ['category'] : '' ;
      // authorID filter: you can use "1,3", but not "1+3" since an event can have only one author
      //$authorID = ( preg_match('/^([0-9],?)+$/', $r ['authorID'] ) ) ? $r ['authorID'] : '' ;
      $author = $r ['author'] ? $r ['author'] : '' ;
   }
   if ($scope == "")
      $scope = "future";
   if ($order != "DESC")
      $order = "ASC";
   if ($format == ''){
      $orig_format = true;
      $format = get_option('eme_event_list_item_format' );
   } else {
      $orig_format = false;
   }
   if ($limit>0 && $paging==1 && isset($_GET['eme_offset'])) {
      $offset=intval($_GET['eme_offset']);
   } else {
      $offset=0;
   }

   // for browsing: if limit=0,paging=1 and only for this_week,this_month or today
   if ($limit==0 && $paging==1) {
      $scope_offset=0;
	if (isset($_GET['eme_offset']))
	   $scope_offset=$_GET['eme_offset'];
	$prev_offset=$scope_offset-1;
	$next_offset=$scope_offset+1;
      if ($scope=="this_week") {
	$scope = date('Y-m-d',strtotime("last Sunday $scope_offset weeks"))."--".date('Y-m-d',strtotime("next Saturday $scope_offset weeks"));
      }
      if ($scope=="this_month") {
        $number_of_days_month=eme_days_in_month($month,$year);
	$scope = date('Y-m-d',strtotime("first day of this month $scope_offset months"))."--".date('Y-m-d',strtotime("last day of this month $scope_offset months"));
      }
      if ($scope=="today") {
	$scope = date('Y-m-d',strtotime("$scope_offset days"));
      }
   }
   // We request $limit+1 events, so we know if we need to show the pagination link or not.
   if ($limit==0) {
      $events = eme_get_events ( 0, $scope, $order, $offset, 0, $category, $author );
   } else {
      $events = eme_get_events ( $limit+1, $scope, $order, $offset, 0, $category, $author );
   }
   $events_count=count($events);

   // get the paging output ready
   $pagination_top = "<div id='events-pagination-top'> ";
   if ($paging==1 && $limit>0) {
      $this_page_url=get_permalink($post->ID);
      if (stristr($this_page_url, "?"))
         $joiner = "&amp;";
      else
         $joiner = "?";
      if ($events_count > $limit) {
         $forward = $offset + $limit;
         $backward = $offset - $limit;
         if ($backward >= 0)
            $pagination_top.= "<a style='eme_nav_left float: left' href='" . $this_page_url.$joiner."eme_offset=$backward'>&lt;&lt;</a>";
         $pagination_top.= "<a style='eme_nav_right float: right' href='" . $this_page_url.$joiner."eme_offset=$forward'>&gt;&gt;</a>";
      }
      if ($events_count <= $limit && $offset>0) {
         $backward = $offset - $limit;
         if ($backward >= 0)
            $pagination_top.= "<a style='eme_nav_left float: left' href='" . $this_page_url.$joiner."eme_offset=$backward'>&lt;&lt;</a>";
      }
   }
   if ($paging==1 && $limit==0) {
      $this_page_url=get_permalink($post->ID);
      if (stristr($this_page_url, "?"))
         $joiner = "&amp;";
      else
         $joiner = "?";
      $pagination_top.= "<a style='eme_nav_left float: left' href='" . $this_page_url.$joiner."eme_offset=$prev_offset'>&lt;&lt;</a>";
      $pagination_top.= "<a style='eme_nav_right float: right' href='" . $this_page_url.$joiner."eme_offset=$next_offset'>&gt;&gt;</a>";
   }
   $pagination_top.= "</div>";
   $pagination_bottom = str_replace("events-pagination-top","events-pagination-bottom",$pagination_top);

   $output = "";
   if (! empty ( $events )) {
      # if we want to show events per period, we first need to determine on which days events occur
      # this code is identical to that in eme_calendar.php for "long events"
      if (! empty ( $showperiod )) {
         $eventful_days= array();
         $i=1;
         foreach ( $events as $event ) {
            // we requested $limit+1 events, so we need to break at the $limit, if reached
            if ($limit>0 && $i>$limit)
               break;
            $event_start_date = strtotime($event['event_start_date']);
            $event_end_date = strtotime($event['event_end_date']);
            if ($event_end_date < $event_start_date)
               $event_end_date=$event_start_date;
            if ($long_events) {
               //Show events on every day that they are still going on
               while( $event_start_date <= $event_end_date ) {
                  $event_eventful_date = date('Y-m-d', $event_start_date);
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
            $i++;
         }

         # now that we now the days on which events occur, loop through them
         $curmonth="";
         $curday="";
         foreach($eventful_days as $day_key => $day_events) {
            foreach($day_events as $event) {
               $themonth = date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime($day_key));
               $theday = date_i18n (get_option('date_format'), strtotime($day_key));
               if ($showperiod == "monthly" && $themonth != $curmonth) {
                  $output .= "<li class='eme_period'>$themonth</li>";
                  $curmonth=$themonth;
               } elseif ($showperiod == "daily" && $theday != $curday) {
                  $output .= "<li class='eme_period'>$theday</li>";
                  $curday=$theday;
               }
               $output .= eme_replace_placeholders ( $format, $event );
            }
         }
      } else {
         $i=1;
         foreach ( $events as $event ) {
            // we requested $limit+1 events, so we need to break at the $limit, if reached
            if ($limit>0 && $i>$limit)
               break;
            $output .= eme_replace_placeholders ( $format, $event );
            $i++;
         }
      } // end if (! empty ( $showperiod )) {

      //Add headers and footers to output
      if( $orig_format ){
         $eme_event_list_item_format_header = get_option('eme_event_list_item_format_header' );
         $eme_event_list_item_format_header = ( $eme_event_list_item_format_header != '' ) ? $eme_event_list_item_format_header : "<ul class='eme_events_list'>";
         $eme_event_list_item_format_footer = get_option('eme_event_list_item_format_footer' );
         $eme_event_list_item_format_footer = ( $eme_event_list_item_format_footer != '' ) ? $eme_event_list_item_format_footer : "</ul>";
         $output =  $eme_event_list_item_format_header .  $output . $eme_event_list_item_format_footer;
      }
   } else {
      $output = "<ul class='eme-no-events'><li>" . get_option('eme_no_events_message' ) . "</li></ul>";
   }
   $events_count=count($events);

   // now add the pagination
   $output = $pagination_top . $output . $pagination_bottom;
  
   // now see how to return the output
   if ($echo)
      echo $output;
   else
      return $output;
}

function eme_get_events_list_shortcode($atts) {
   extract ( shortcode_atts ( array ('limit' => 3, 'scope' => 'future', 'order' => 'ASC', 'format' => '', 'category' => '', 'showperiod' => '', 'author' => '', 'paging' => 0, 'long_events' => 0 ), $atts ) );
   $result = eme_get_events_list ( "limit=$limit&scope=$scope&order=$order&format=$format&echo=0&category=$category&showperiod=$showperiod&author=$author&paging=$paging&long_events=$long_events" );
   return $result;
}
add_shortcode ( 'events_list', 'eme_get_events_list_shortcode' );

function eme_display_single_event_shortcode($atts){
   extract ( shortcode_atts ( array ('id'=>''), $atts ) );
   $event = eme_get_event ( $id );
   $single_event_format = get_option('eme_single_event_format' );
   $page_body = eme_replace_placeholders ($single_event_format, $event);
   return $page_body;
}
add_shortcode('display_single_event', 'eme_display_single_event_shortcode');

function eme_get_events_page($justurl = 0, $echo = 1, $text = '') {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'text' => '', 'echo' => 1 );
      
      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
      $echo = (bool) $r ['echo'];
   }
   
   $page_link = get_permalink ( get_option ( 'eme_events_page' ) );
   if ($justurl) {
      $result = $page_link;
   } else {
      if ($text == '')
         $text = get_option ( 'eme_events_page_title' );
      $result = "<a href='$page_link' title='$text'>$text</a>";
   }
   if ($echo)
      echo $result;
   else
      return $result;
}

function eme_get_events_page_shortcode($atts) {
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => '' ), $atts ) );
   $result = eme_get_events_page ( "justurl=$justurl&text=$text&echo=0" );
   return $result;
}
add_shortcode ( 'events_page', 'eme_get_events_page_shortcode' );

function eme_are_events_available($scope = "future") {
   if ($scope == "")
      $scope = "future";
   $events = eme_get_events ( 1, $scope );
   
   if (empty ( $events ))
      return FALSE;
   else
      return TRUE;
}

// Returns true if the page in question is the events page
function eme_is_events_page() {
   $events_page_id = get_option('eme_events_page' );
   return is_page ( $events_page_id );
}

function eme_is_single_event_page() {
   return (eme_is_events_page () && (isset ( $_REQUEST ['event_id'] ) && $_REQUEST ['event_id'] != ''));
}

function eme_is_multiple_events_page() {
   return (eme_is_events_page () && ! (isset ( $_REQUEST ['event_id'] ) && $_REQUEST ['event_id'] != ''));
}

// main function querying the database event table
function eme_get_events($o_limit = 10, $scope = "future", $order = "ASC", $o_offset = 0, $location_id = "", $category = '', $author = '') {
   global $wpdb;

   $events_table = $wpdb->prefix.EVENTS_TBNAME;
   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   if ($o_limit > 0) {
      $limit = "LIMIT ".intval($o_limit);
   } else {
      $limit="";
   }
   if ($o_offset >0) {
      if ($o_limit == 0) {
          $limit = "LIMIT ".intval($o_offset);
      }
      $offset = "OFFSET ".intval($o_offset);
   } else {
      $offset="";
   }

   if ($order != "DESC")
      $order = "ASC";
   
   $tzstring = get_option('timezone_string');
   if (!empty($tzstring) ) {
      @date_default_timezone_set ($tzstring);
   }
   $today = date("Y-m-d");
   
   $conditions = array ();
   // if we're not in the admin itf, we don't want draft events
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $conditions [] = "event_status IN (1,2)";
      } else {
         $conditions [] = "event_status=1";
      }
      if (get_option('eme_rsvp_hide_full_events')) {
         $conditions [] = "(event_rsvp=0 OR (event_rsvp=1 AND event_seats > (SELECT SUM(booking_seats) AS booked_seats FROM $bookings_table WHERE $bookings_table.event_id = $events_table.event_id)))";
      }
   }
   if (preg_match ( "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $scope )) {
      //$conditions [] = " event_start_date like '$scope'";
      $conditions [] = " (event_start_date  like '$scope') OR (event_start_date <= '$scope' AND event_end_date >= '$scope')";
   } elseif (preg_match ( "/^0000-([0-9]{2})$/", $scope, $matches )) {
      $year=date('Y');
      $month=$matches[1];
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-00";
      $limit_end   = "$year-$month-$number_of_days_month";
      $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end'))";
   } elseif ($scope == "this_week") {
      $limit_start = date('Y-m-d',strtotime("last Sunday"));
      $limit_end   = date('Y-m-d',strtotime("next Saturday"));
      $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end'))";
   } elseif ($scope == "this_month") {
      $year=date('Y');
      $month=date('m');
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-00";
      $limit_end   = "$year-$month-$number_of_days_month";
      $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end'))";
   } elseif ($scope == "next_month") {
      $year=date('Y', strtotime("+1 month"));
      $month=date('m', strtotime("+1 month"));
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-00";
      $limit_end   = "$year-$month-$number_of_days_month";
      $conditions [] = " ((event_start_date BETWEEN '$limit_start' AND '$limit_end') OR (event_end_date BETWEEN '$limit_start' AND '$limit_end'))";
   } elseif (preg_match ( "/^([0-9]{4}-[0-9]{2}-[0-9]{2})--([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $scope, $matches )) {
      $conditions [] = " ((event_start_date BETWEEN '$matches[1]' AND '$matches[2]') OR (event_end_date BETWEEN '$matches[1]' AND '$matches[2]'))";
   } else {
      if (($scope != "past") && ($scope != "all") && ($scope != "today"))
         $scope = "future";
      if ($scope == "future")
         //This is so events with future dates are counted too
         $conditions [] = " (event_start_date >= '$today' OR (event_end_date >= '$today' AND event_end_date != '0000-00-00' AND event_end_date IS NOT NULL))";
      if ($scope == "past")
         $conditions [] = " event_start_date < '$today'";
      if ($scope == "today")
         $conditions [] = " (event_start_date = '$today' OR (event_start_date <= '$today' AND event_end_date >= '$today'))";
   }
   
   if ($location_id != "")
      $conditions [] = " location_id = ".intval($location_id);
      
   if (get_option('eme_categories_enabled')) {
      if (is_numeric($category)) {
         if ($category>0)
            $conditions [] = " FIND_IN_SET($category,event_category_ids)";
      } elseif ( preg_match('/^([0-9],?)+$/', $category) ) {
         $category = explode(',', $category);
         $category_conditions = array();
         foreach ($category as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions [] = "(".implode(' OR', $category_conditions).")";
      } elseif ( preg_match('/^([0-9] ?)+$/', $category) ) {
         $category = explode(' ', $category);
         $category_conditions = array();
         foreach ($category as $cat) {
            if (is_numeric($cat) && $cat>0)
               $category_conditions[] = " FIND_IN_SET($cat,event_category_ids)";
         }
         $conditions [] = "(".implode(' AND ', $category_conditions).")";
      }
   }

   // now filter the author ID
   if ($author != '' && !preg_match('/,/', $author)){
      $authinfo=get_userdatabylogin($author);
      $conditions [] = " event_author = ".$authinfo->ID;
   }elseif( preg_match('/,/', $author) ){
      $author = explode(',', $author);
      $author_conditions = array();
      foreach($author as $authname) {
            $authinfo=get_userdatabylogin($author);
            $author_conditions[] = " event_author = ".$authinfo->ID;
      }
      $conditions [] = "(".implode(' OR ', $author_conditions).")";
   }

   // extra conditions for authors: if we're in the admin itf, return only the events for which you have the right to change anything
   $current_userid=get_current_user_id();
   if (is_admin() && !current_user_can( EDIT_CAPABILITY) && current_user_can( MIN_CAPABILITY)) {
      $conditions [] = "(event_author = $current_userid OR event_contactperson_id= $current_userid)";
   }
   
   $where = implode ( " AND ", $conditions );
   if ($where != "")
      $where = " WHERE " . $where;
   
   $sql = "SELECT *, 
         DATE_FORMAT(event_start_date, '%e') AS 'event_day',
         DATE_FORMAT(event_start_date, '%Y') AS 'event_year',
         DATE_FORMAT(event_start_time, '%k') AS 'event_hh',
         DATE_FORMAT(event_start_time, '%i') AS 'event_mm',
         DATE_FORMAT(event_end_date, '%e') AS 'event_end_day',
         DATE_FORMAT(event_end_date, '%Y') AS 'event_end_year',
         DATE_FORMAT(event_end_time, '%k') AS 'event_end_hh',
         DATE_FORMAT(event_end_time, '%i') AS 'event_end_mm'
         FROM $events_table
         $where
         ORDER BY event_start_date $order , event_start_time $order
         $limit 
         $offset";
   $wpdb->show_errors = true;
   $events = $wpdb->get_results ( $sql, ARRAY_A );
   if (! empty ( $events )) {
      //$wpdb->print_error(); 
      $inflated_events = array ();
      foreach ( $events as $this_event ) {
         if ($this_event ['event_status'] == STATUS_PRIVATE && !is_user_logged_in()) {
            continue;
         }
         // if we're not in the admin itf, we don't want draft events
         if (!is_admin() && $this_event ['event_status'] == STATUS_DRAFT) {
            continue;
         }
         
         if ($this_event ['location_id'] ) {
            $this_location = eme_get_location ( $this_event ['location_id'] );
            $this_event ['location_name'] = $this_location ['location_name'];
            $this_event ['location_address'] = $this_location ['location_address'];
            $this_event ['location_town'] = $this_location ['location_town'];
         }

         $this_event ['event_attributes'] = @unserialize($this_event ['event_attributes']);
         $this_event ['event_attributes'] = (!is_array($this_event ['event_attributes'])) ?  array() : $this_event ['event_attributes'] ;
         array_push ( $inflated_events, $this_event );
      }
      return $inflated_events;
   } else {
      return null;
   }
}

function eme_get_event($event_id) {
   global $wpdb;
   $event_id = intval($event_id);
   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $conditions = array ();
   $conditions [] = "event_id = $event_id";

   // if we're not in the admin itf, we don't want draft events
   if (!is_admin()) {
      if (is_user_logged_in()) {
         $conditions [] = "event_status IN (1,2)";
      } else {
         $conditions [] = "event_status=1";
      }
   }
   $where = implode ( " AND ", $conditions );
   if ($where != "")
      $where = " WHERE " . $where;
   $sql = "SELECT *, 
            DATE_FORMAT(event_start_date, '%Y-%m-%e') AS 'event_date', 
         DATE_FORMAT(event_start_date, '%e') AS 'event_day',
         DATE_FORMAT(event_start_date, '%m') AS 'event_month',
         DATE_FORMAT(event_start_date, '%Y') AS 'event_year',
         DATE_FORMAT(event_start_time, '%k') AS 'event_hh',
         DATE_FORMAT(event_start_time, '%i') AS 'event_mm',
         DATE_FORMAT(event_start_time, '%h:%i%p') AS 'event_start_12h_time', 
         DATE_FORMAT(event_start_time, '%H:%i') AS 'event_start_24h_time', 
         DATE_FORMAT(event_end_date, '%Y-%m-%e') AS 'event_end_date', 
         DATE_FORMAT(event_end_date, '%e') AS 'event_end_day',
         DATE_FORMAT(event_end_date, '%m') AS 'event_end_month',
         DATE_FORMAT(event_end_date, '%Y') AS 'event_end_year',
         DATE_FORMAT(event_end_time, '%k') AS 'event_end_hh',
         DATE_FORMAT(event_end_time, '%i') AS 'event_end_mm',
         DATE_FORMAT(event_end_time, '%h:%i%p') AS 'event_end_12h_time',
         DATE_FORMAT(event_end_time, '%H:%i') AS 'event_end_24h_time'
      FROM $events_table
      $where";
   
   //$wpdb->show_errors(true);
   $event = $wpdb->get_row ( $sql, ARRAY_A );
   //$wpdb->print_error();
   $location = eme_get_location ( $event ['location_id'] );
   $event ['location_name'] = $location ['location_name'];
   $event ['location_address'] = $location ['location_address'];
   $event ['location_town'] = $location ['location_town'];
   $event ['location_latitude'] = $location ['location_latitude'];
   $event ['location_longitude'] = $location ['location_longitude'];
   $event ['location_image_url'] = $location ['location_image_url'];

   $event ['event_attributes'] = @unserialize($event ['event_attributes']);
   $event ['event_attributes'] = (!is_array($event ['event_attributes'])) ?  array() : $event ['event_attributes'] ;
   return $event;
}

function eme_duplicate_event($event_id) {
   global $wpdb, $EZSQL_ERROR;
   //First, duplicate.
   $event_table_name = $wpdb->prefix . EVENTS_TBNAME;
   $eventArray = $wpdb->get_row("SELECT * FROM {$event_table_name} WHERE event_id={$event_id}", ARRAY_A );
   // unset the old event id
   unset($eventArray['event_id']);
   // set the new authorID
   $current_userid=get_current_user_id();
   $eventArray['event_author']=$current_userid;
   $result = $wpdb->insert($event_table_name, $eventArray);
   if( $result !== false) {
      //Get the ID of the new item
      $event_ID = $wpdb->insert_id;
      $event = eme_get_event ( $event_ID );
      $event['event_id'] = $event_ID;
      //Now we edit the duplicated item
      $title = __ ( "Edit Event", 'eme' ) . " '" . $event ['event_name'] . "'";
      echo "<div id='message' class='updated below-h2'>You are now editing the duplicated event.</div>";
      eme_event_form ( $event, $title, $event_ID );
   } else {
      echo "<div class='error'><p>There was an error duplicating the event. Try again maybe? Here are the errors:</p>";
      foreach ($EZSQL_ERROR as $errorArray) {
         echo "<p>{$errorArray['error_str']}</p>";
      }  
      echo "</div>";
      $scope = $_GET ['scope'];
      $offset = intval($_GET ['offset']);
      $order = $_GET ['order'];
      $events = eme_get_events ( 21, $scope, $order, $offset );
      eme_events_table ( $events, 20, $title, $scope, $offset );
   }
}

function eme_events_table($events, $limit, $title, $scope="future", $offset=0, $o_category=0) {
   $events_count = count ( $events );
   ?>

<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<h2><?php echo $title; ?></h2>
   <?php
      admin_show_warnings();
   ?>
   <!--<div id='new-event' class='switch-tab'><a href="<?php
   echo admin_url("admin.php?page=events-manager&amp;action=edit_event")?>><?php
   _e ( 'New Event ...', 'eme' );
   ?></a></div>-->
      <?php
   
   $scope_names = array ();
   $scope_names ['past'] = __ ( 'Past events', 'eme' );
   $scope_names ['all'] = __ ( 'All events', 'eme' );
   $scope_names ['future'] = __ ( 'Future events', 'eme' );

   $event_status_array = status_array ();
   ?> 
      
   <form id="posts-filter" action="" method="get">
   <input type='hidden' name='page' value='events-manager' />
   <ul class="subsubsub">
      <li><?php _e ( 'Total', 'eme' ); ?> <span class="count">(<?php if ($events_count>$limit) echo $limit; else echo count($events); echo " ". __('Events','eme'); ?>)</span></li>
   </ul>

   <div class="tablenav">

   <div class="alignleft actions">
   <select name="action">
   <option value="-1" selected="selected"><?php _e ( 'Bulk Actions' ); ?></option>
   <option value="deleteEvents"><?php _e ( 'Delete selected','eme' ); ?></option>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
   <select name="scope">
   <?php
   foreach ( $scope_names as $key => $value ) {
      $selected = "";
      if ($key == $scope)
         $selected = "selected='selected'";
      echo "<option value='$key' $selected>$value</option>  ";
   }
   ?>
   </select>
   <select name="category">
   <option value='0'><?php _e('All categories','eme'); ?></option>
   <?php
   $categories = eme_get_categories();
   foreach ( $categories as $category) {
      $selected = "";
      if ($o_category == $category['category_id'])
         $selected = "selected='selected'";
      echo "<option value='".$category['category_id']."' $selected>".$category['category_name']."</option>";
   }
   ?>
   </select>

   <input id="post-query-submit" class="button-secondary" type="submit" value="<?php _e ( 'Filter' )?>" />
   </div>
   <div class="clear"></div>

   </div>
   <?php
   if (empty ( $events )) {
      _e ('No events', 'eme');
   } else {
      ?>
      
   <table class="widefat">
   <thead>
      <tr>
         <th class='manage-column column-cb check-column' scope='col'><input
            class='select-all' type="checkbox" value='1' /></th>
         <th><?php _e ( 'Name', 'eme' ); ?></th>
            <th><?php _e ( 'Status', 'eme' ); ?></th>
            <th></th>
            <th><?php _e ( 'Location', 'eme' ); ?></th>
         <th colspan="2"><?php _e ( 'Date and time', 'eme' ); ?></th>
      </tr>
   </thead>
   <tbody>
     <?php
      $i = 1;
      foreach ( $events as $event ) {
         if ($i>$limit)
            break;
         $class = ($i % 2) ? ' class="alternate"' : '';
         $localised_start_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event ['event_start_date']));
         $localised_end_date = date_i18n ( __ ( 'D d M Y' ), strtotime($event ['event_end_date']));
         $style = "";
         $today = date ( "Y-m-d" );
         
         $location_summary = "<b>" . eme_trans_sanitize_html($event ['location_name']) . "</b><br/>" . eme_trans_sanitize_html($event ['location_address']) . " - " . eme_trans_sanitize_html($event ['location_town']);
         
         if ($event ['event_start_date'] < $today)
            $style = "style ='background-color: #FADDB7;'";
         ?>
     <tr <?php echo "$class $style"; ?>>
         <td><input type='checkbox' class='row-selector' value='<?php echo $event ['event_id']; ?>' name='events[]' /></td>
         <td><strong>
         <a class="row-title" href="<?php echo admin_url("admin.php?page=events-manager&amp;action=edit_event&amp;event_id=".$event ['event_id']); ?>"><?php echo eme_trans_sanitize_html($event ['event_name']); ?></a>
         </strong>
         <?php
         $categories = explode(',', $event ['event_category_ids']);
         foreach($categories as $cat){
            $category = eme_get_category($cat);
            if($category)
               echo "<br/><span title='".__('Category','eme').": ".eme_trans_sanitize_html($category['category_name'])."'>".eme_trans_sanitize_html($category['category_name'])."</span>";
         }
         if ($event ['event_rsvp']) {
            $printable_address = admin_url("/admin.php?page=events-manager-people&amp;action=printable&amp;event_id=".$event['event_id']);
            $available_seats = eme_get_available_seats($event['event_id']);
            $total_seats = $event ['event_seats'];
            echo "<br/>".__('RSVP Info: ','eme').__('Free: ','eme' ).$available_seats.", ".__('Max: ','eme').$total_seats;
            echo " (<a id='printable'  target='' href='$printable_address'>".__('Printable view','eme')."</a>)";
         }
         ?> 
         </td>
         <td>
         <?php
         if (isset ($event_status_array[$event['event_status']])) {
            echo $event_status_array[$event['event_status']];
         }
         ?> 
         </td>
         <td>
         <a href="<?php echo admin_url("admin.php?page=events-manager&amp;action=duplicate_event&amp;event_id=".$event ['event_id']); ?>" title="<?php _e ( 'Duplicate this event', 'eme' ); ?>"><strong>+</strong></a>
         </td>
         <td>
             <?php echo $location_summary; ?>
         </td>
         <td>
            <?php echo $localised_start_date; if ($localised_end_date !='') echo " - " . $localised_end_date; ?><br />
            <?php echo substr ( $event ['event_start_time'], 0, 5 ) . " - " . substr ( $event ['event_end_time'], 0, 5 ); ?>
         </td>
         <td>
             <?php
            if ($event ['recurrence_id']) {
               $recurrence_desc = eme_get_recurrence_desc ( $event ['recurrence_id'] );
            ?>
               <b><?php echo $recurrence_desc; ?>
            <br />
            <a href="<?php echo admin_url("admin.php?page=events-manager&amp;action=edit_recurrence&amp;recurrence_id=".$event ['recurrence_id']); ?>"><?php _e ( 'Reschedule', 'eme' ); ?></a></b>
            <?php
            }
            ?>
         </td>
   </tr>
      <?php
         $i ++;
      }
      ?>
   
   </tbody>
   </table>
   <?php
   } // end of table
   ?>

   </form>

<?php
   if ($events_count > $limit) {
      $forward = $offset + $limit;
      $backward = $offset - $limit;
      echo "<div id='events-pagination'> ";
      echo "<a style='float: right' href='" . admin_url("admin.php?page=events-manager&amp;scope=$scope&amp;category=$o_category&amp;offset=$forward")."'>&gt;&gt;</a>";
      if ($backward >= 0)
         echo "<a style='float: left' href='" . admin_url("admin.php?page=events-manager&amp;scope=$scope&amp;category=$o_category&amp;offset=$backward")."'>&lt;&lt;</a>";
      echo "</div>";
   }
   if ($events_count <= $limit && $offset>0) {
      $backward = $offset - $limit;
      echo "<div id='events-pagination'> ";
      if ($backward >= 0)
         echo "<a style='float: left' href='" . admin_url("admin.php?page=events-manager&amp;scope=$scope&amp;category=$o_category&amp;offset=$backward")."'>&lt;&lt;</a>";
      echo "</div>";
   }
   ?>

</div>
<?php
}

function eme_event_form($event, $title, $element) {
   
   global $localised_date_formats;
   admin_show_warnings();

   $use_select_for_locations = get_option('eme_use_select_for_locations');
   // qtranslate there? Then we need the select, otherwise locations will be created again...
   if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
      $use_select_for_locations=1;
   }
   $event_status_array = status_array ();
   $saved_bydays = array();

   // let's determine if it is a new event, handy
   if (! $element) {
      $is_new_event=1;
   } else {
      $is_new_event=0;
   }

   $show_recurrent_form = 0;
   // change prefix according to event/recurrence
   if (isset($_GET ['action']) && $_GET ['action'] == "edit_recurrence") {
      $pref = "recurrence_";
      $form_destination = "admin.php?page=events-manager&amp;action=update_recurrence&amp;recurrence_id=" . $element;
      $saved_bydays = explode ( ",", $event ['recurrence_byday'] );
      $show_recurrent_form = 1;
   } else {
      $pref = "event_";
      $form_destination = "admin.php?page=events-manager&amp;action=update_event&amp;event_id=" . $element;
      if ($event ['recurrence_id']) {
         # editing a single event of an recurrence: don't show the recurrence form
         $show_recurrent_form = 0;
      } else {
         $show_recurrent_form = 1;
      }
   }
   
   $locale_code = substr ( get_locale (), 0, 2 );
   if (isset($localised_date_formats [$locale_code])) {
      $localised_date_format = $localised_date_formats [$locale_code];
   } else {
      $localised_date_format = $localised_date_formats ["en"];
   }

   $hours_locale = "24";
   // Setting 12 hours format for those countries using it
   if (preg_match ( "/en|sk|zh|us|uk/", $locale_code ))
      $hours_locale = "12";
   
   $localised_example = str_replace ( "yy", "2008", str_replace ( "mm", "11", str_replace ( "dd", "28", $localised_date_format ) ) );
   $localised_end_example = str_replace ( "yy", "2008", str_replace ( "mm", "11", str_replace ( "dd", "28", $localised_date_format ) ) );
   
   if ($event [$pref . 'start_date'] != "") {
      preg_match ( "/(\d{4})-(\d\d?)-(\d\d?)/", $event [$pref. 'start_date'], $matches );
      $year = $matches [1];
      $month = sprintf("%02d",$matches [2]);
      $day = sprintf("%02d",$matches [3]);
      $localised_date = str_replace ( "yy", $year, str_replace ( "mm", $month, str_replace ( "dd", $day, $localised_date_format ) ) );
   } else {
      $localised_date = "";
   }
   if ($event [$pref . 'end_date'] != "") {
      preg_match ( "/(\d{4})-(\d\d?)-(\d\d?)/", $event [$pref . 'end_date'], $matches );
      $end_year = $matches [1];
      $end_month = sprintf("%02d",$matches [2]);
      $end_day = sprintf("%02d",$matches [3]);
      $localised_end_date = str_replace ( "yy", $end_year, str_replace ( "mm", $end_month, str_replace ( "dd", $end_day, $localised_date_format ) ) );
   } else {
      $localised_end_date = "";
   }
   //if($event[$pref.'rsvp'])
    //   echo (eme_bookings_table($event[$pref.'id']));
   

   $freq_options = array ("daily" => __ ( 'Daily', 'eme' ), "weekly" => __ ( 'Weekly', 'eme' ), "monthly" => __ ( 'Monthly', 'eme' ) );
   $days_names = array (1 => __ ( 'Mon' ), 2 => __ ( 'Tue' ), 3 => __ ( 'Wed' ), 4 => __ ( 'Thu' ), 5 => __ ( 'Fri' ), 6 => __ ( 'Sat' ), 7 => __ ( 'Sun' ) );
   $weekno_options = array ("1" => __ ( 'first', 'eme' ), '2' => __ ( 'second', 'eme' ), '3' => __ ( 'third', 'eme' ), '4' => __ ( 'fourth', 'eme' ), '5' => __ ( 'fifth', 'eme' ), '-1' => __ ( 'last', 'eme' ), "none" => __('Start day') );
   
   // for new events, check the setting wether or not to enable RSVP
   if ($is_new_event) {
      if (get_option('eme_rsvp_reg_for_new_events'))
         $event_RSVP_checked = "checked='checked'";
      $event_number_spaces=intval(get_option('eme_rsvp_default_number_spaces'));
   } else {
      $event ['event_rsvp'] ? $event_RSVP_checked = "checked='checked'" : $event_RSVP_checked = '';
      $event_number_spaces=$event ['event_seats'];
   }
   $event ['registration_requires_approval'] ? $registration_requires_approval = "checked='checked'" : $registration_requires_approval = '';
   
   ?>
   <form id="eventForm" method="post"  action="<?php echo $form_destination; ?>">
      <div class="wrap">
         <div id="icon-events" class="icon32"><br /></div>
         <h2><?php echo eme_trans_sanitize_html($title); ?></h2>
         <?php
         if ($event ['recurrence_id']) {
            ?>
         <p id='recurrence_warning'>
            <?php
               if (isset ( $_GET ['action'] ) && ($_GET ['action'] == 'edit_recurrence')) {
                  _e ( 'WARNING: This is a recurrence.', 'eme' )?>
            <br />
            <?php
                  _e ( 'Modifying these data all the events linked to this recurrence will be rescheduled', 'eme' );
               
               } else {
                  _e ( 'WARNING: This is a recurring event.', 'eme' );
                  _e ( 'If you change these data and save, this will become an independent event.', 'eme' );
               }
               ?>
         </p>
         <?php
         }
         ?>
         <div id="poststuff" class="metabox-holder has-right-sidebar">
            <!-- SIDEBAR -->
            <div id="side-info-column" class='inner-sidebar'>
               <div id='side-sortables' class="meta-box-sortables">
                  <?php if(current_user_can( AUTHOR_CAPABILITY)) { ?>
                  <!-- status postbox -->
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Event Status', 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                        <p><?php _e('Status','eme'); ?>
                        <select id="event_status" name="event_status">
                        <?php
                           foreach ( $event_status_array as $key=>$value) {
                              if ($event['event_status'] && ($event['event_status']==$key)) {
                                 $selected = "selected='selected'";
                              } else {
                                 $selected = "";
                              }
                              echo "<option value='$key' $selected>$value</option>";
                           }
                        ?>
                        </select>
                        <?php
                           _e('Private events are only visible for logged in users, draft events are not visible from the front end.','eme');
                        ?>
                        </p>
                     </div>
                  </div>
                  <?php } ?>
                  <?php if(get_option('eme_recurrence_enabled') && $show_recurrent_form ) : ?>
                  <!-- recurrence postbox -->
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( "Recurrence", 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                        <?php 
                           $recurrence_YES = "";
                           if ($event ['recurrence_id'])
                              $recurrence_YES = "checked='checked' disabled='disabled'";
                        ?>
                        <p>
                           <input id="event-recurrence" type="checkbox" name="repeated_event"
                              value="1" <?php echo $recurrence_YES; ?> />
                        </p>
                        <div id="event_recurrence_pattern">
                           <p>Frequency:
                              <select id="recurrence-frequency" name="recurrence_freq">
                                 <?php eme_option_items ( $freq_options, $event [$pref . 'freq'] ); ?>
                              </select>
                           </p>
                           <p>
                              <?php _e ( 'Every', 'eme' )?>
                              <input id="recurrence-interval" name='recurrence_interval'
                                size='2' value='<?php echo $event ['recurrence_interval']; ?>' />
                              <span class='interval-desc' id="interval-daily-singular">
                              <?php _e ( 'day', 'eme' )?>
                              </span> <span class='interval-desc' id="interval-daily-plural">
                              <?php _e ( 'days', 'eme' ) ?>
                              </span> <span class='interval-desc' id="interval-weekly-singular">
                              <?php _e ( 'week', 'eme' )?>
                              </span> <span class='interval-desc' id="interval-weekly-plural">
                              <?php _e ( 'weeks', 'eme' )?>
                              </span> <span class='interval-desc' id="interval-monthly-singular">
                              <?php _e ( 'month', 'eme' )?>
                              </span> <span class='interval-desc' id="interval-monthly-plural">
                              <?php _e ( 'months', 'eme' )?>
                              </span> </p>
                           <p class="alternate-selector" id="weekly-selector">
                              <?php eme_checkbox_items ( 'recurrence_bydays[]', $days_names, $saved_bydays ); ?>
                              <br />
                              <?php _e ( 'If you leave this empty, the event start date will be used as a reference.', 'eme' )?>
                           </p>
                           <p class="alternate-selector" id="monthly-selector">
                              <?php _e ( 'Every', 'eme' )?>
                              <select id="monthly-modifier" name="recurrence_byweekno">
                                 <?php eme_option_items ( $weekno_options, $event ['recurrence_byweekno'] ); ?>
                              </select>
                              <select id="recurrence-weekday" name="recurrence_byday">
                                 <?php eme_option_items ( $days_names, $event ['recurrence_byday'] ); ?>
                              </select>
                              <?php _e ( 'Day of month', 'eme' )?>
                              <br />
                              <?php _e ( 'If you use "Start day" as day of the month, the month day of the event start date will be used as a reference.', 'eme' )?>
                              &nbsp;</p>
                        </div>
                        <p id="recurrence-tip">
                           <?php _e ( 'Check if your event happens more than once according to a regular pattern', 'eme' )?>
                        </p>
                     </div>
                  </div>
                  <?php endif; ?>

                  <?php if($event['event_author']) : ?>
                  <!-- owner postbox -->
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Author', 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                        <p><?php _e('Author of this event: ','eme'); ?>
                           <?php
                           $owner_user_info = get_userdata($event['event_author']);
                           echo eme_sanitize_html($owner_user_info->display_name);
                           ?>
                        </p>
                     </div>
                  </div>
                  <?php endif; ?>
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Contact Person', 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                        <p><?php _e('Contact','eme'); ?>
                           <?php
                           wp_dropdown_users ( array ('name' => 'event_contactperson_id', 'show_option_none' => __ ( "Select...", 'eme' ), 'selected' => $event ['event_contactperson_id'] ) );
                           ?>
                        </p>
                     </div>
                  </div>
                  <?php if(get_option('eme_rsvp_enabled')) : ?>
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span><?php _e('RSVP','eme'); ?></span></h3>
                     <div class="inside">
                        <p>
                           <input id="rsvp-checkbox" name='event_rsvp' value='1' type='checkbox' <?php echo $event_RSVP_checked; ?> />
                           <?php _e ( 'Enable registration for this event', 'eme' )?>
                        </p>
                        <div id='rsvp-data'>
                           <p>
                              <input id="approval_required-checkbox" name='registration_requires_approval' value='1' type='checkbox' <?php echo $registration_requires_approval; ?> />
                              <?php _e ( 'Require approval for registration','eme' ); ?>
                           <br />
                              <?php _e ( 'Spaces','eme' ); ?> :
                              <input id="seats-input" type="text" name="event_seats" size='5' value="<?php echo $event_number_spaces; ?>" />
                           <br />
                              <?php _e ( 'Allow RSVP until ','eme' ); ?>
                              <input id="rsvp_number_days" type="text" name="rsvp_number_days" maxlength='2' size='2' value="<?php echo $event ['rsvp_number_days']; ?>" />
                              <?php _e ( ' days before the event starts.','eme' ); ?>
                           </p>
                           <?php if ($event ['event_rsvp']) {
                                 eme_bookings_compact_table ( $event['event_id'] );
                              }
                           ?>
                        </div>
                     </div>
                  </div>
                  <?php endif; ?>
                  <?php if(get_option('eme_categories_enabled')) :?>
                  <div class="postbox ">
                     <div class="handlediv" title="Click to toggle."><br />
                     </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Category', 'eme' ); ?>
                        </span></h3>
                     <div class="inside">
                     <?php
                     $categories = eme_get_categories();
                     foreach ( $categories as $category) {
                        if ($event['event_category_ids'] && in_array($category['category_id'],explode(",",$event['event_category_ids']))) {
                           $selected = "checked='checked'";
                        } else {
                           $selected = "";
                        }
                     ?>
<input type="checkbox" name="event_category_ids[]" value="<?php echo $category['category_id']; ?>" <?php echo $selected ?> /><?php echo $category['category_name']; ?><br />
                     <?php
                     }
                     ?>
                     </div>
                  </div> 
                  <?php endif; ?>
               </div>
            </div>
            <!-- END OF SIDEBAR -->
            <div id="post-body">
               <div id="post-body-content" class="meta-box-sortables">
                  <!-- we need titlediv for qtranslate as ID -->
                  <div id="titlediv" class="stuffbox">
                     <h3>
                        <?php _e ( 'Name', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <!-- we need title for qtranslate as ID -->
                        <input type="text" id="title" name="event_name" value="<?php echo eme_sanitize_html($event ['event_name']); ?>" />
                        <br />
                        <?php _e ( 'The event name. Example: Birthday party', 'eme' )?>
                     </div>
                  </div>
                  <div id="div_event_start_date" class="stuffbox">
                     <h3 id='event-date-title'>
                        <?php _e ( 'Event date', 'eme' ); ?>
                     </h3>
                     <h3 id='recurrence-dates-title'>
                        <?php _e ( 'Recurrence dates', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <input id="localised-date" type="text" name="localised_event_date" value="<?php echo $localised_date?>" style="display: none;" readonly="readonly" />
                        <input id="date-to-submit" type="text" name="event_date" value="<?php echo $event [$pref . 'start_date']?>" style="background: #FCFFAA" />
                        <input id="localised-end-date" type="text" name="localised_event_end_date" value="<?php echo $localised_end_date?>" style="display: none;" readonly="readonly" />
                        <input id="end-date-to-submit" type="text" name="event_end_date" value="<?php echo $event [$pref . 'end_date']?>" style="background: #FCFFAA" />
                        <br />
                        <span id='event-date-explanation'>
                        <?php
                           _e ( 'The event date.', 'eme' );
                           echo " ";
                           _e ( 'When not recurring, this event spans between the beginning and end date.', 'eme' );
                        ?>
                        </span><span id='recurrence-dates-explanation'>
                        <?php _e ( 'The recurrence beginning and end date.', 'eme' ); ?>
                        </span> </div>
                  </div>
                  <div id="div_event_end_day" class="stuffbox">
                     <h3>
                        <?php _e ( 'Event time', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <input id="start-time" type="text" size="8" maxlength="8" name="event_start_time" value="<?php echo $event ['event_start_' . $hours_locale . "h_time"]; ?>" />
                        -
                        <input id="end-time" type="text" size="8" maxlength="8" name="event_end_time" value="<?php echo $event ['event_end_' . $hours_locale . "h_time"]; ?>" />
                        <br />
                        <?php _e ( 'The time of the event beginning and end', 'eme' )?>
                        . </div>
                  </div>
                  <div id="div_location_coordinates" class="stuffbox" style='display: none;'>
                     <h3>
                        <?php _e ( 'Coordinates', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <input id='location_latitude' name='location_latitude' type='text' value='<?php echo $event ['location_latitude']; ?>' size='15' />
                        -
                        <input id='location_longitude' name='location_longitude' type='text' value='<?php echo $event ['location_longitude']; ?>' size='15' />
                     </div>
                  </div>
                  <div id="div_event_page_title_format" class="postbox <?php if ($event['event_page_title_format']=="") echo "closed"; ?>">
                                                        <div class="handlediv" title="Click to toggle">
                        <br />
                                                        </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Single Event Title Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_page_title_format" id="event_page_title_format" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_page_title_format']);?></textarea>
                        <br />
                        <p><?php _e ( 'The format of the single event title.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_event_single_event_format" class="postbox <?php if ($event['event_single_event_format']=="") echo "closed"; ?>">
                                                        <div class="handlediv" title="Click to toggle">
                        <br />
                                                        </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Single Event Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_single_event_format" id="event_single_event_format" rows="6" cols="60"><?php echo eme_sanitize_html($event ['event_single_event_format']);?></textarea>
                        <br />
                        <p><?php _e ( 'The format of the single event page.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_event_contactperson_email_body" class="postbox <?php if ($event['event_contactperson_email_body']=="") echo "closed"; ?>">
                                                        <div class="handlediv" title="Click to toggle">
                        <br />
                                                        </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Contact Person Email Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_contactperson_email_body" id="event_contactperson_email_body" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_contactperson_email_body']);?></textarea>
                        <br />
                        <p><?php _e ( 'The format of the email which will be sent to the contact person.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_event_respondent_email_body" class="postbox <?php if ($event['event_respondent_email_body']=="") echo "closed"; ?>">
                                                        <div class="handlediv" title="Click to toggle">
                        <br />
                                                        </div>
                     <h3 class='hndle'><span>
                        <?php _e ( 'Respondent Email Format', 'eme' ); ?>
                        </span>
                     </h3>
                     <div class="inside">
                        <textarea name="event_respondent_email_body" id="event_respondent_email_body" rows="6" cols="60"><?php echo eme_sanitize_html($event['event_respondent_email_body']);?></textarea>
                        <br />
                        <p><?php _e ( 'The format of the email which will be sent to the respondent.','eme');?>
                        <br />
                        <?php _e ('Only fill this in if you want to override the default settings.', 'eme' );?>
                        </p>
                     </div>
                  </div>
                  <div id="div_location_name" class="stuffbox">
                     <h3>
                        <?php _e ( 'Location', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <table id="eme-location-data">
                           <tr>
                           <?php  if($use_select_for_locations) {
			      $location_0['location_id']=0;
			      $location_0['location_name']= '';
                              $locations = eme_get_locations();
                           ?>
                              <th><?php _e('Location','eme') ?></th>
                              <td> 
                                 <select name="location-select-id" id='location-select-id' size="1">
         <option value="<?php echo $location_0['location_id'] ?>" ><?php echo eme_trans_sanitize_html($location_0['location_name']) ?></option>
                                 <?php 
                                 $selected_location=$location_0;
                                 foreach($locations as $location) :
                                    $selected = "";
                                    if(isset($event['location_id']))  { 
                                       $location_id =  $event['location_id'];
                                       if ($location_id == $location['location_id']) {
                                          $selected_location=$location;
                                          $selected = "selected='selected' ";
                                       }
                                    }
                                 ?>
         <option value="<?php echo $location['location_id'] ?>" <?php echo $selected ?>><?php echo eme_trans_sanitize_html($location['location_name']) ?></option>
                                 <?php endforeach; ?>
                                 </select>
                                 <input type='hidden' name='location-select-name' value='<?php echo eme_trans_sanitize_html($selected_location['location_name'])?>'/>
                                 <input type='hidden' name='location-select-town' value='<?php echo eme_trans_sanitize_html($selected_location['location_town'])?>'/>
                                 <input type='hidden' name='location-select-address' value='<?php echo eme_trans_sanitize_html($selected_location['location_address'])?>'/>      
                              </td>
                           <?php } else { ?>
                              <th><?php _e ( 'Name','eme' )?>
                                 &nbsp;</th>
                              <td><input name="translated_location_name" type="hidden" value="<?php echo eme_trans_sanitize_html($event ['location_name'])?>" /><input id="location_name" type="text" name="location_name" value="<?php echo eme_trans_sanitize_html($event ['location_name'])?>" /></td>
                           <?php } ?>
                           <?php
                              $gmap_is_active = get_option('eme_gmap_is_active' );
                              if ($gmap_is_active) {
                           ?>
                              <td rowspan='6'><div id='map-not-found'
               style='width: 400px; font-size: 140%; text-align: center; margin-top: 100px; display: hide'>
                                    <p>
                                       <?php _e ( 'Map not found','eme' ); ?>
                                    </p>
                                 </div>
                                 <div id='event-map'
               style='width: 400px; height: 300px; background: green; display: hide; margin-right: 8px'></div></td>
                              <?php
         }
         ; // end of IF_GMAP_ACTIVE ?>
                           </tr>
                            <?php  if(!$use_select_for_locations) : ?>
                           <tr>
<td colspan='2'><p><?php _e ( 'The name of the location where the event takes place. You can use the name of a venue, a square, etc', 'eme' );?>
<br />
      <?php _e ( 'If you leave this empty, the map will NOT be shown for this event', 'eme' );?></p></td>
                           </tr>
                           <?php else: ?>
                           <tr >
<td colspan='2'  rowspan='5' style='vertical-align: top'>
                                    <p><?php
   _e ( 'Select a location for your event', 'eme' )?></p>
</td>
                           </tr>
                           <?php endif; ?>
                            <?php  if(!$use_select_for_locations) : ?> 
                           <tr>
                              <th><?php _e ( 'Address:' )?> &nbsp;</th>
                              <td><input id="location_address" type="text" name="location_address" value="<?php echo $event ['location_address']; ?>" /></td>
                           </tr>
                           <tr>
                              <td colspan='2'><p>
                                    <?php _e ( 'The address of the location where the event takes place. Example: 21, Dominick Street', 'eme' )?>
                              </p></td>
                           </tr>
                           <tr>
                              <th><?php _e ( 'Town:' )?> &nbsp;</th>
                              <td><input id="location_town" type="text" name="location_town" value="<?php echo $event ['location_town']?>" /></td>
                           </tr>
                           <tr>
                              <td colspan='2'><p>
                                    <?php _e ( 'The town where the location is located. If you\'re using the Google Map integration and want to avoid geotagging ambiguities include the country in the town field. Example: Verona, Italy.', 'eme' )?>
                                 </p></td>
                           </tr>
                           <?php endif; ?>
                        </table>
                     </div>
                  </div>
                  <div id="div_event_notes" class="postbox">
                     <h3>
                        <?php _e ( 'Details', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
                           <?php the_editor($event ['event_notes']); ?>
                        </div>
                        <br />
                        <?php _e ( 'Details about the event', 'eme' )?>
                     </div>
                  </div>
                  <?php if(get_option('eme_attributes_enabled')) : ?>
                  <div id="div_event_attributes" class="postbox">
                     <h3>
                        <?php _e ( 'Attributes', 'eme' ); ?>
                     </h3>
                     <div class="inside">
                        <?php eme_attributes_form($event) ?>
                     </div>
                  </div>
                  <?php endif; ?>
               </div>
               <p class="submit">
                  <input type="submit" name="events_update" value="<?php _e ( 'Submit Event', 'eme' ); ?> &raquo;" />
               </p>

               </div>
            </div>
         </div>
      </div>
   </form>
<?php
}

function eme_validate_event($event) {
   // Only for emergencies, when JS is disabled
   // TODO make it fully functional without JS
   global $required_fields;
   $errors = Array ();
   foreach ( $required_fields as $field ) {
      if ($event [$field] == "") {
         $errors [] = $field;
      }
   }
   $error_message = "";
   if (count ( $errors ) > 0)
      $error_message = __ ( 'Missing fields: ','eme' ) . implode ( ", ", $errors ) . ". ";
   if (isset($_POST ['repeated_event']) && $_POST ['repeated_event'] == "1" && (!isset($_POST ['event_end_date']) || $_POST ['event_end_date'] == ""))
      $error_message .= __ ( 'Since the event is repeated, you must specify an event date.', 'eme' );
   if ($error_message != "")
      return $error_message;
   else
      return "OK";

}

function _eme_is_date_valid($date) {
   $year = substr ( $date, 0, 4 );
   $month = substr ( $date, 5, 2 );
   $day = substr ( $date, 8, 2 );
   return (checkdate ( $month, $day, $year ));
}
function _eme_is_time_valid($time) {
   $result = preg_match ( "/([01]\d|2[0-3])(:[0-5]\d)/", $time );
   
   return ($result);
}
// Enqueing jQuery script to make sure it's loaded
function eme_enqueue_scripts() {
   wp_enqueue_script ( 'jquery' );
   // wp_enqueue_script('datepicker',EME_PLUGIN_URL.'jquery-ui-datepicker/jquery-ui-personalized-1.6b.js', array('jquery') );
}
add_action ( 'template_redirect', 'eme_enqueue_scripts' );

// General script to make sure hidden fields are shown when containing data
function eme_admin_general_script() {
   ?>
<script src="<?php echo EME_PLUGIN_URL; ?>eme.js" type="text/javascript"></script>
<script src="<?php echo EME_PLUGIN_URL; ?>js/jquery-ui-datepicker/ui.datepicker.js" type="text/javascript"></script>
<script src="<?php echo EME_PLUGIN_URL; ?>js/timeentry/jquery.timeentry.js" type="text/javascript"></script>
<?php
   
   // Check if the locale is there and loads it
   $locale_code = substr ( get_locale (), 0, 2 );
   
   $show24Hours = 'true';
   // Setting 12 hours format for those countries using it
   if (preg_match ( "/en|sk|zh|us|uk/", $locale_code ))
      $show24Hours = 'false';
   
   $locale_file = EME_PLUGIN_URL. "/js/jquery-ui-datepicker/i18n/ui.datepicker-$locale_code.js";
   // for english, no translation code is needed
   if ($locale_code != "en") {
      ?>
<script src="<?php echo EME_PLUGIN_URL; ?>js/jquery-ui-datepicker/i18n/ui.datepicker-<?php echo $locale_code; ?>.js" type="text/javascript"></script>
<?php
   }
   ?>


<style type='text/css' media='all'>
@import
   "<?php echo EME_PLUGIN_URL; ?>js/jquery-ui-datepicker/ui.datepicker.css"
   ;
</style>
<script type="text/javascript">
   //<![CDATA[
   // TODO: make more general, to support also latitude and longitude (when added)
$j_eme_event=jQuery.noConflict();

function updateIntervalDescriptor () { 
   $j_eme_event(".interval-desc").hide();
   var number = "-plural";
   if ($j_eme_event('input#recurrence-interval').val() == 1 || $j_eme_event('input#recurrence-interval').val() == "")
   number = "-singular"
   var descriptor = "span#interval-"+$j_eme_event("select#recurrence-frequency").val()+number;
   $j_eme_event(descriptor).show();
}
function updateIntervalSelectors () {
   $j_eme_event('p.alternate-selector').hide();
   $j_eme_event('p#'+ $j_eme_event('select#recurrence-frequency').val() + "-selector").show();
   //$j_eme_event('p.recurrence-tip').hide();
   //$j_eme_event('p#'+ $j_eme_event(this).val() + "-tip").show();
}
function updateShowHideRecurrence () {
   if($j_eme_event('input#event-recurrence').attr("checked")) {
      $j_eme_event("#event_recurrence_pattern").fadeIn();
      //Edited this and the one below so dates always can have an end date
      //$j_eme_event("input#localised-end-date").fadeIn();
      $j_eme_event("#event-date-explanation").hide();
      $j_eme_event("#recurrence-dates-explanation").show();
      $j_eme_event("h3#recurrence-dates-title").show();
      $j_eme_event("h3#event-date-title").hide();
   } else {
      $j_eme_event("#event_recurrence_pattern").hide();
      //$j_eme_event("input#localised-end-date").hide();
      $j_eme_event("#recurrence-dates-explanation").hide();
      $j_eme_event("#event-date-explanation").show();
      $j_eme_event("h3#recurrence-dates-title").hide();
      $j_eme_event("h3#event-date-title").show();
   }
}

function updateShowHideRsvp () {
   if($j_eme_event('input#rsvp-checkbox').attr("checked")) {
      $j_eme_event("div#rsvp-data").fadeIn();
   } else {
      $j_eme_event("div#rsvp-data").hide();
   }
}

$j_eme_event(document).ready( function() {
   locale_format = "ciao";
 
   $j_eme_event("#recurrence-dates-explanation").hide();
   $j_eme_event("#localised-date").show();
   $j_eme_event("#localised-end-date").show();

   $j_eme_event("#date-to-submit").hide();
   $j_eme_event("#end-date-to-submit").hide(); 
   $j_eme_event("#localised-date").datepicker($j_eme_event.extend({},
      ($j_eme_event.datepicker.regional["<?php echo $locale_code; ?>"], 
      {altField: "#date-to-submit", 
      altFormat: "yy-mm-dd"})));
   $j_eme_event("#localised-end-date").datepicker($j_eme_event.extend({},
      ($j_eme_event.datepicker.regional["<?php echo $locale_code; ?>"], 
      {altField: "#end-date-to-submit", 
      altFormat: "yy-mm-dd"})));

   $j_eme_event("#start-time").timeEntry({spinnerImage: '', show24Hours: <?php echo $show24Hours; ?> });
   $j_eme_event("#end-time").timeEntry({spinnerImage: '', show24Hours: <?php echo $show24Hours; ?>});

   $j_eme_event('input.select-all').change(function(){
      if($j_eme_event(this).is(':checked'))
         $j_eme_event('input.row-selector').attr('checked', true);
      else
         $j_eme_event('input.row-selector').attr('checked', false);
   });

   // if any of event_single_event_format,event_page_title_format,event_contactperson_email_body,event_respondent_email_body
   // is empty: display default value on focus, and if the value hasn't changed from the default: empty it on blur

   $j_eme_event('textarea#event_page_title_format').focus(function(){
      var tmp_value='<?php echo addslashes(preg_replace("/\r\n|\r|\n/","___",get_option('eme_event_page_title_format' ))); ?>';
      tmp_value=tmp_value.replace(/___/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   }); 
   $j_eme_event('textarea#event_page_title_format').blur(function(){
      var tmp_value='<?php echo addslashes(preg_replace("/\r\n|\r|\n/","___",get_option('eme_event_page_title_format' ))); ?>';
      tmp_value=tmp_value.replace(/___/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   }); 
   $j_eme_event('textarea#event_single_event_format').focus(function(){
      var tmp_value='<?php echo addslashes(preg_replace("/\r\n|\r|\n/","___",get_option('eme_single_event_format' ))); ?>';
      tmp_value=tmp_value.replace(/___/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   }); 
   $j_eme_event('textarea#event_single_event_format').blur(function(){
      var tmp_value='<?php echo addslashes(preg_replace("/\r\n|\r|\n/","___",get_option('eme_single_event_format' ))); ?>';
      tmp_value=tmp_value.replace(/___/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   }); 
   $j_eme_event('textarea#event_contactperson_email_body').focus(function(){
      var tmp_value='<?php echo addslashes(preg_replace("/\r\n|\r|\n/","___",get_option('eme_contactperson_email_body' ))); ?>';
      tmp_value=tmp_value.replace(/___/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   })
   $j_eme_event('textarea#event_contactperson_email_body').blur(function(){
      var tmp_value='<?php echo addslashes(preg_replace("/\r\n|\r|\n/","___",get_option('eme_contactperson_email_body' ))); ?>';
      tmp_value=tmp_value.replace(/___/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   }); 
   $j_eme_event('textarea#event_respondent_email_body').focus(function(){
      var tmp_value='<?php echo addslashes(preg_replace("/\r\n|\r|\n/","___",get_option('eme_respondent_email_body' ))); ?>';
      tmp_value=tmp_value.replace(/___/g,"\n");
      if($j_eme_event(this).val() == '')
         $j_eme_event(this).val(tmp_value);
   }); 
   $j_eme_event('textarea#event_respondent_email_body').blur(function(){
      var tmp_value='<?php echo addslashes(preg_replace("/\r\n|\r|\n/","___",get_option('eme_respondent_email_body' ))); ?>';
      tmp_value=tmp_value.replace(/___/g,"\n");
      if($j_eme_event(this).val() == tmp_value)
         $j_eme_event(this).val('');
   }); 

   if ($j_eme_event('[name=eme_rsvp_mail_send_method]').val() != "smtp") {
      $j_eme_event('tr#eme_smtp_host_row').hide();
      $j_eme_event('tr#eme_rsvp_mail_SMTPAuth_row').hide();
      $j_eme_event('tr#eme_smtp_username_row').hide(); 
      $j_eme_event('tr#eme_smtp_password_row').hide();
   }
   $j_eme_event('[name=eme_rsvp_mail_send_method]').change(function() {
      if($j_eme_event(this).val() == "smtp") {
         $j_eme_event('tr#eme_smtp_host_row').show();
         $j_eme_event('tr#eme_rsvp_mail_SMTPAuth_row').show();
         $j_eme_event('tr#eme_smtp_username_row').show(); 
         $j_eme_event('tr#eme_smtp_password_row').show(); 
         $j_eme_event('tr#eme_rsvp_mail_port_row').show(); 
      } else {
         $j_eme_event('tr#eme_smtp_host_row').hide();
         $j_eme_event('tr#eme_rsvp_mail_SMTPAuth_row').hide();
         $j_eme_event('tr#eme_smtp_username_row').hide(); 
         $j_eme_event('tr#eme_smtp_password_row').hide();
         $j_eme_event('tr#eme_rsvp_mail_port_row').hide(); 
      }
   });
   if ($j_eme_event('input[name=eme_rsvp_mail_SMTPAuth]:checked').val() != 1) {
      $j_eme_event('tr#eme_smtp_username_row').hide(); 
      $j_eme_event('tr#eme_smtp_password_row').hide();
   }
   $j_eme_event('input[name=eme_rsvp_mail_SMTPAuth]').change(function() {
      if($j_eme_event(this).val() == 1) {
         $j_eme_event('tr#eme_smtp_username_row').show(); 
         $j_eme_event('tr#eme_smtp_password_row').show(); 
      } else {
         $j_eme_event('tr#eme_smtp_username_row').hide(); 
         $j_eme_event('tr#eme_smtp_password_row').hide();
      }
   });
   updateIntervalDescriptor(); 
   updateIntervalSelectors();
   updateShowHideRecurrence();
   updateShowHideRsvp();
   $j_eme_event('input#event-recurrence').change(updateShowHideRecurrence);
   $j_eme_event('input#rsvp-checkbox').change(updateShowHideRsvp);
   // recurrency elements
   $j_eme_event('input#recurrence-interval').keyup(updateIntervalDescriptor);
   $j_eme_event('select#recurrence-frequency').change(updateIntervalDescriptor);
   $j_eme_event('select#recurrence-frequency').change(updateIntervalSelectors);

   // Add a "+" to the collapsable postboxes
   //jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');

   // hiding or showing notes according to their content 
   //          if(jQuery("textarea[@name=event_notes]").val()!="") {
      //    jQuery("textarea[@name=event_notes]").parent().parent().removeClass('closed');
      // }
   //jQuery('#event_notes h3').click( function() {
   //       jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
        //});

   // users cannot submit the event form unless some fields are filled
      function validateEventForm(){
         errors = "";
      var recurring = $j_eme_event("input[name=repeated_event]:checked").val();
      //requiredFields= new Array('event_name', 'localised_event_date', 'location_name','location_address','location_town');
      requiredFields= new Array('event_name', 'localised_event_date');
      var localisedRequiredFields = {'event_name':"<?php _e ( 'Name', 'eme' )?>",
                      'localised_event_date':"<?php _e ( 'Date', 'eme' )?>"
                     };
      
      missingFields = new Array;
      for (var i in requiredFields) {
         if ($j_eme_event("input[name=" + requiredFields[i]+ "]").val() == 0) {
            missingFields.push(localisedRequiredFields[requiredFields[i]]);
            $j_eme_event("input[name=" + requiredFields[i]+ "]").css('border','2px solid red');
         } else {
            $j_eme_event("input[name=" + requiredFields[i]+ "]").css('border','1px solid #DFDFDF');
         }
         }
   
      //    alert('ciao ' + recurring+ " end: " + $j_eme_event("input[@name=localised_event_end_date]").val());
         if (missingFields.length > 0) {
          errors = "<?php echo _e ( 'Some required fields are missing:', 'eme' )?> " + missingFields.join(", ") + ".\n";
      }
      if(recurring && $j_eme_event("input[name=localised_event_end_date]").val() == "") {
         errors = errors +  "<?php _e ( 'Since the event is repeated, you must specify an end date', 'eme' )?>."; 
         $j_eme_event("input[name=localised_event_end_date]").css('border','2px solid red');
      } else {
         $j_eme_event("input[name=localised_event_end_date]").css('border','1px solid #DFDFDF');
      }
      if(errors != "") {
         alert(errors);
         return false;
      }
      return true;
   }

   $j_eme_event('#eventForm').bind("submit", validateEventForm);
      
});
//]]>
</script>

<?php
}

function eme_admin_map_script() {
   if ((isset ( $_REQUEST ['event_id'] ) && $_REQUEST ['event_id'] != '') || (isset ( $_GET ['page'] ) && $_GET ['page'] == 'events-manager-locations') || (isset ( $_GET ['page'] ) && $_GET ['page'] == 'events-manager-new_event') || (isset ( $_REQUEST ['action'] ) && $_REQUEST ['action'] == 'edit_recurrence')) {
      if (! (isset ( $_REQUEST ['action'] ) && $_REQUEST ['action'] == 'eme_delete')) {
         // single event page

         if (isset($_REQUEST ['event_id']))
            $event_ID = intval($_REQUEST ['event_id']);
         else
            $event_ID =0;
         $event = eme_get_event ( $event_ID );
         
         if (isset($event ['location_town']) || (isset ( $_GET ['page'] ) && $_GET ['page'] == 'events-manager-locations') || (isset($_GET['page']) && $_GET['page'] == 'events-manager-new_event')) {
            if (isset($event ['location_address']) && $event ['location_address'] != "") {
               $search_key = $event ['location_address'] . ", " . $event ['location_town'];
            } else {
               $search_key = $event ['location_name'] . ", " . $event ['location_town'];
            }
            
            ?>
<style type="text/css">
/* div#location_town, div#location_address, div#location_name {
               width: 480px;
            }
            table.form-table {
               width: 50%;
            }     */
</style>
<script src="http://maps.google.com/maps/api/js?v=3.1&amp;sensor=false" type="text/javascript"></script>
<script type="text/javascript">
         //<![CDATA[
            $j_eme_admin=jQuery.noConflict();
      
         function loadMap(location, town, address) {
            var latlng = new google.maps.LatLng(-34.397, 150.644);
            var myOptions = {
               zoom: 13,
               center: latlng,
               disableDoubleClickZoom: true,
               mapTypeControlOptions: {
                  mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE]
               },
               mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            var map = new google.maps.Map(document.getElementById("event-map"), myOptions);
            var geocoder = new google.maps.Geocoder();
            if (address !="") {
               searchKey = address + ", " + town;
            } else {
               searchKey =  location + ", " + town;
            }
               
            var search = "<?php echo $search_key?>" ;
            geocoder.geocode( { 'address': searchKey}, function(results, status) {
               if (status == google.maps.GeocoderStatus.OK) {
                  map.setCenter(results[0].geometry.location);
                  var marker = new google.maps.Marker({
                     map: map, 
                     position: results[0].geometry.location
                  });
                  var infowindow = new google.maps.InfoWindow({
                     content: '<div class=\"eme-location-balloon\"><strong>' + location +'</strong><p>' + address + '</p><p>' + town + '</p></div>',
                  });
                  infowindow.open(map,marker);
                  $j_eme_admin('input#location_latitude').val(results[0].geometry.location.lat());
                  $j_eme_admin('input#location_longitude').val(results[0].geometry.location.lng());
                  $j_eme_admin("#event-map").show();
                  $j_eme_admin('#map-not-found').hide();
               } else {
                  $j_eme_admin("#event-map").hide();
                  $j_eme_admin('#map-not-found').show();
               }
            });
            }
 
         $j_eme_admin(document).ready(function() {
            <?php 
            // if we're creating a new event, or editing an event *AND*
            // the use_select_for_locations options is on or qtranslate is installed
            // then we do the select thing
            // We check on the new/edit event because this javascript is also executed for editing locations, and then we don't care
            // about the use_select_for_locations parameter
            if (
               ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_event') || (isset($_GET['page']) && $_GET['page'] == 'events-manager-new_event')) && 
                     (get_option('eme_use_select_for_locations') || function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'))) { ?>
            eventLocation = $j_eme_admin("input[name='location-select-name']").val(); 
            eventTown = $j_eme_admin("input[name='location-select-town']").val();
            eventAddress = $j_eme_admin("input[name='location-select-address']").val(); 
   
               <?php } else { ?>
            eventLocation = $j_eme_admin("input[name='translated_location_name']").val(); 
            eventTown = $j_eme_admin("input#location_town").val(); 
            eventAddress = $j_eme_admin("input#location_address").val();
               <?php } ?>
            
            loadMap(eventLocation, eventTown, eventAddress);
         
            $j_eme_admin("input[name='location_name']").blur(function(){
                  newEventLocation = $j_eme_admin("input[name='location_name']").val();
                  if (newEventLocation !=eventLocation) {
                     loadMap(newEventLocation, eventTown, eventAddress); 
                     eventLocation = newEventLocation;
                  }
            });
            $j_eme_admin("input#location_town").blur(function(){
                  newEventTown = $j_eme_admin("input#location_town").val(); 
                  if (newEventTown !=eventTown) {
                     loadMap(eventLocation, newEventTown, eventAddress); 
                     eventTown = newEventTown;
                  } 
            });
            $j_eme_admin("input#location_address").blur(function(){
                  newEventAddress = $j_eme_admin("input#location_address").val(); 
                  if (newEventAddress != eventAddress) {
                     loadMap(eventLocation, eventTown, newEventAddress);
                     eventAddress = newEventAddress; 
                  }
            });
            }); 
            $j_eme_admin(document).unload(function() {
            GUnload();
         });
          //]]>
      </script>
<?php
         }
      }
   }
}
$gmap_is_active = get_option('eme_gmap_is_active' );
if ($gmap_is_active) {
   add_action ( 'admin_head', 'eme_admin_map_script' );

}

function eme_rss_link($justurl = 0, $echo = 1, $text = "RSS", $scope="future", $order = "ASC",$category='',$author='',$limit=5) {
   if (strpos ( $justurl, "=" )) {
      // allows the use of arguments without breaking the legacy code
      $defaults = array ('justurl' => 0, 'echo' => 1, 'text' => 'RSS', 'scope' => 'future', 'order' => 'ASC', 'category' => '', 'author' => '', 'limit' => 5 );
      
      $r = wp_parse_args ( $justurl, $defaults );
      extract ( $r );
      $echo = (bool) $r ['echo'];
   }
   if ($text == '')
      $text = "RSS";
   $url = site_url ("/?eme_rss=main&scope=$scope&order=$order&category=$category&author=$author&limit=$limit");
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

function eme_rss_link_shortcode($atts) {
   extract ( shortcode_atts ( array ('justurl' => 0, 'text' => 'RSS', 'scope' => 'future', 'order' => 'ASC', 'category' => '', 'author' => '', 'limit' => 5 ), $atts ) );
   $result = eme_rss_link ( "justurl=$justurl&echo=0&text=$text&limit=$limit&scope=$scope&order=$order&category=$category&showperiod=$showperiod&author=$author" );
   return $result;
}
add_shortcode ( 'events_rss_link', 'eme_rss_link_shortcode' );

function eme_rss() {
   if (isset ( $_GET ['eme_rss'] ) && $_GET ['eme_rss'] == 'main') {
      if (isset($_GET['limit'])) {
         $limit=intval($_GET['limit']);
      } else {
         $limit=5;
      }
      if (isset($_GET['author'])) {
         $author=$_GET['author'];
      } else {
         $author="";
      }
      if (isset($_GET['order'])) {
         $order=$_GET['order'];
      } else {
         $order="order";
      }
      if (isset($_GET['category'])) {
         $category=$_GET['category'];
      } else {
         $category="category";
      }
      if (isset($_GET['scope'])) {
         $scope=$_GET['scope'];
      } else {
         $scope="future";
      }
      header ( "Content-type: text/xml" );
      echo "<?xml version='1.0'?>\n";
      
      $events_page_link = eme_get_events_page(true, false);
      if (stristr ( $events_page_link, "?" ))
         $joiner = "&amp;";
      else
         $joiner = "?";
      
      ?>
<rss version="2.0">
<channel>
<title><?php
      echo get_option('eme_rss_main_title' );
      ?></title>
<link><?php
      echo $events_page_link;
      ?></link>
<description><?php
      echo get_option('eme_rss_main_description' );
      ?></description>
<docs>
http://blogs.law.harvard.edu/tech/rss
</docs>
<generator>
Weblog Editor 2.0
</generator>
<?php
      $title_format = get_option('eme_rss_title_format' );
      $description_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", get_option('eme_rss_description_format' ) ) );
      $events = eme_get_events ( $limit, $scope, $order, 0, "", $category, $author );
      foreach ( $events as $event ) {
         $title = eme_replace_placeholders ( $title_format, $event, "rss" );
         $description = eme_replace_placeholders ( $description_format, $event, "rss" );
         echo "<item>";
         echo "<title>$title</title>\n";
         echo "<link>$events_page_link" . $joiner . "event_id=" . $event ['event_id'] . "</link>\n ";
         echo "<description>$description</description>\n";
         if (get_option('eme_categories_enabled')) {
            $categories = eme_replace_placeholders ( "#_CATEGORIES", $event, "rss" );
            echo "<categories>$categories</categories>\n";
         }
         echo "</item>";
      }
      ?>

</channel>
</rss>

<?php
      die ();
   }
}
add_action ( 'init', 'eme_rss' );
function substitute_rss($data) {
   if (isset ( $_REQUEST ['event_id'] ))
      return site_url ("/?eme_rss=main");
   else
      return $data;
}
function eme_general_css() {
   echo "<link rel='stylesheet' href='".EME_PLUGIN_URL."events_manager.css' type='text/css'/>\n";
   $file_name= EME_PLUGIN_DIR."myown.css";
   if (file_exists($file_name)) {
      echo "<link rel='stylesheet' href='".EME_PLUGIN_URL."myown.css' type='text/css'/>\n";
   }
   $file_name= get_stylesheet_directory()."/eme.css";
   if (file_exists($file_name)) {
      echo "<link rel='stylesheet' href='".get_stylesheet_directory_uri()."/eme.css' type='text/css'/>\n";
   }
}
function eme_admin_general_css() {
   echo "<link rel='stylesheet' href='".EME_PLUGIN_URL."events_manager.css' type='text/css'/>\n";
   $file_name= EME_PLUGIN_DIR."/events-manager-extended/myown.css";
   if (file_exists($file_name)) {
      echo "<link rel='stylesheet' href='".EME_PLUGIN_URL."myown.css' type='text/css'/>\n";
   }
   $file_name= get_stylesheet_directory()."/eme.css";
   if (file_exists($file_name)) {
      echo "<link rel='stylesheet' href='".get_stylesheet_directory_uri()."/eme.css' type='text/css'/>\n";
   }
}
add_action ( 'wp_head', 'eme_general_css' );
add_action ( 'admin_head', 'eme_admin_general_css' );
//add_filter('feed_link','substitute_rss')

function eme_general_footer() {
   global $eme_need_gmap_js;
   $gmap_is_active = get_option('eme_gmap_is_active' );
   // we only include the map js if wanted/needed
   if ($gmap_is_active && $eme_need_gmap_js) {
      echo "<script type='text/javascript' src='".EME_PLUGIN_URL."eme_location_map.js'></script>\n";
   }
}
add_action('wp_footer', 'eme_general_footer');

function eme_delete_event($event_id) {
   global $wpdb;
   $table_name = $wpdb->prefix . EVENTS_TBNAME;
   $sql = "DELETE FROM $table_name WHERE event_id = '$event_id';";
   $wpdb->query ( $sql );

}
add_filter ( 'favorite_actions', 'eme_favorite_menu' );

function eme_favorite_menu($actions) {
   // add quick link to our favorite plugin
   $actions ['admin.php?page=events-manager-new_event'] = array (__ ( 'Add an event', 'eme' ), MIN_CAPABILITY );
   return $actions;
}

function eme_alert_events_page() {
   $events_page_id = get_option('eme_events_page' );
   if (strpos ( $_SERVER ['SCRIPT_NAME'], 'post.php' ) && isset ( $_GET ['action'] ) && $_GET ['action'] == 'edit' && isset ( $_GET ['post'] ) && $_GET ['post'] == "$events_page_id") {
      $message = sprintf ( __ ( "This page corresponds to <strong>Events Manager Extended</strong> events page. Its content will be overriden by <strong>Events Manager Extended</strong>. If you want to display your content, you can can assign another page to <strong>Events Manager Extended</strong> in the the <a href='%s'>Settings</a>. ", 'eme' ), 'admin.php?page=events-manager-options' );
      $notice = "<div class='error'><p>$message</p></div>";
      echo $notice;
   }
}
add_action ( 'admin_notices', 'eme_alert_events_page' );

//This adds the tinymce editor
function eme_tinymce(){
   global $plugin_page;
   if ( in_array( $plugin_page, array('events-manager-locations', 'events-manager-new_event', 'events-manager') ) ) {
      add_action( 'admin_print_footer_scripts', 'wp_tiny_mce', 25 );
      wp_enqueue_script('post');
      if ( user_can_richedit() )
         wp_enqueue_script('editor');
      add_thickbox();
      wp_enqueue_script('media-upload');
      wp_enqueue_script('word-count');
      wp_enqueue_script('quicktags');  
   }
}
add_action ( 'admin_init', 'eme_tinymce' );

function status_array() {
   $event_status_array = array();
   $event_status_array[STATUS_PUBLIC] = __ ( 'Public', 'eme' );
   $event_status_array[STATUS_PRIVATE] = __ ( 'Private', 'eme' );
   $event_status_array[STATUS_DRAFT] = __ ( 'Draft', 'eme' );
   return $event_status_array;
}
?>
