<?php
/*
Plugin Name: Events Manager Extended
Version: 3.1.2
Plugin URI: http://www.e-dynamics.be/wordpress
Description: Manage events specifying precise spatial data (Location, Town, etc).
Author: Franky Van Liedekerke
Author URI: http://www.e-dynamics.be/
*/

/*
Copyright (c) 2009, Davide Benini.  $Revision: 1 $
Copyright (c) 2010, Franky Van Liedekerke.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/*************************************************/ 

// Setting constants
define('DBEM_PLUGIN_URL', WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))); //PLUGIN DIRECTORY
define('DBEM_PLUGIN_DIR', ABSPATH.PLUGINDIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))); //PLUGIN DIRECTORY
define('EVENTS_TBNAME','dbem_events'); //TABLE NAME
define('RECURRENCE_TBNAME','dbem_recurrence'); //TABLE NAME   
define('LOCATIONS_TBNAME','dbem_locations'); //TABLE NAME  
define('BOOKINGS_TBNAME','dbem_bookings'); //TABLE NAME
define('PEOPLE_TBNAME','dbem_people'); //TABLE NAME  
define('BOOKING_PEOPLE_TBNAME','dbem_bookings_people'); //TABLE NAME  
define('CATEGORIES_TBNAME', 'dbem_categories');
define('DEFAULT_EVENT_PAGE_NAME', 'Events');   
define('DBEM_PAGE','<!--DBEM_EVENTS_PAGE-->'); //EVENTS PAGE
define('MIN_CAPABILITY', 'edit_posts');	// Minimum user level to access calendars
define('SETTING_CAPABILITY', 'activate_plugins');	// Minimum user level to access calendars
define('DEFAULT_EVENT_LIST_ITEM_FORMAT', '<li>#j #M #Y - #H:#i<br/> #_LINKEDNAME<br/>#_TOWN </li>');
define('DEFAULT_SINGLE_EVENT_FORMAT', '<p>#j #M #Y - #H:#i</p><p>#_TOWN</p>'); 
define('DEFAULT_EVENTS_PAGE_TITLE',__('Events','dbem') ) ;
define('DEFAULT_EVENT_PAGE_TITLE_FORMAT', '#_NAME'); 
define('DEFAULT_RSS_DESCRIPTION_FORMAT',"#j #M #y - #H:#i <br/>#_LOCATION <br/>#_ADDRESS <br/>#_TOWN");
define('DEFAULT_RSS_TITLE_FORMAT',"#_NAME");
define('DEFAULT_MAP_TEXT_FORMAT', '<strong>#_LOCATION</strong><p>#_ADDRESS</p><p>#_TOWN</p>');     
define('DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT','<li>#_LINKEDNAME<ul><li>#j #M #y</li><li>#_TOWN</li></ul></li>');
define('DEFAULT_NO_EVENTS_MESSAGE', __('No events', 'dbem'));  
define('DEFAULT_SINGLE_LOCATION_FORMAT', '<p>#_ADDRESS</p><p>#_TOWN</p>'); 
define('DEFAULT_LOCATION_PAGE_TITLE_FORMAT', '#_NAME'); 
define('DEFAULT_LOCATION_BALLOON_FORMAT', "<strong>#_NAME</strong><br/>#_ADDRESS - #_TOWN<br/><a href='#_LOCATIONPAGEURL'>Details</a>");
define('DEFAULT_LOCATION_EVENT_LIST_ITEM_FORMAT', "<li>#_NAME - #j #M #Y - #H:#i</li>");
define('DEFAULT_LOCATION_NO_EVENTS_MESSAGE', __('<li>No events in this location</li>', 'dbem'));
define("IMAGE_UPLOAD_DIR", "wp-content/uploads/locations-pics");
define('DEFAULT_IMAGE_MAX_WIDTH', 700);  
define('DEFAULT_IMAGE_MAX_HEIGHT', 700);  
define('DEFAULT_IMAGE_MAX_SIZE', 204800); 
define('DEFAULT_FULL_CALENDAR_EVENT_FORMAT', '<li>#_LINKEDNAME</li>');    
define('DEFAULT_SMALL_CALENDAR_EVENT_TITLE_FORMAT', "#_NAME" );
define('DEFAULT_SMALL_CALENDAR_EVENT_TITLE_SEPARATOR', ", ");  
define('DEFAULT_USE_SELECT_FOR_LOCATIONS', false);      
define('DEFAULT_ATTRIBUTES_ENABLED', true);
define('DEFAULT_RECURRENCE_ENABLED', true);
define('DEFAULT_RSVP_ENABLED', true);
define('DEFAULT_CATEGORIES_ENABLED', true);
define('DEFAULT_GMAP_ENABLED', true);

// DEBUG constant for developing
// if you are hacking this plugin, set to TRUE, a log will show in admin pages
define('DEBUG', false);     

// fix all superglobals
if (get_magic_quotes_gpc()) {
    $_GET = array_map('stripslashes_deep', $_GET);
    $_POST = array_map('stripslashes_deep', $_POST);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

// INCLUDES
include("captcha_check.php");
include("dbem_events.php");
include("dbem_calendar.php");      
include("dbem_widgets.php");
include("dbem_rsvp.php");     
include("dbem_locations.php"); 
include("dbem_people.php");
include("dbem_recurrence.php");    
include("dbem_UI_helpers.php");
include("dbem_categories.php");
include("dbem_attributes.php");

require_once("phpmailer/dbem_phpmailer.php") ;
//require_once("phpmailer/language/phpmailer.lang-en.php") ;
  
// Localised date formats as in the jquery UI datepicker plugin
$localised_date_formats = array("am" => "dd.mm.yy","ar" => "dd/mm/yy", "bg" => "dd.mm.yy", "ca" => "mm/dd/yy", "cs" => "dd.mm.yy", "da" => "dd-mm-yy", "de" =>"dd.mm.yy", "es" => "dd/mm/yy", "en" => "mm/dd/yy", "fi" => "dd.mm.yy", "fr" => "dd/mm/yy", "he" => "dd/mm/yy", "hu" => "yy-mm-dd", "hy" => "dd.mm.yy", "id" => "dd/mm/yy", "is" => "dd/mm/yy", "it" => "dd/mm/yy", "ja" => "yy/mm/dd", "ko" => "yy-mm-dd", "lt" => "yy-mm-dd", "lv" => "dd-mm-yy", "nl" => "dd.mm.yy", "no" => "yy-mm-dd", "pl" => "yy-mm-dd", "pt" => "dd/mm/yy", "ro" => "mm/dd/yy", "ru" => "dd.mm.yy", "sk" => "dd.mm.yy", "sv" => "yy-mm-dd", "th" => "dd/mm/yy", "tr" => "dd.mm.yy", "ua" => "dd.mm.yy", "uk" => "dd.mm.yy", "us" => "mm/dd/yy", "CN" => "yy-mm-dd", "TW" => "yy/mm/dd");

//required fields
$required_fields = array('event_name'); 
$location_required_fields = array("location_name" => __('The location name', 'dbem'), "location_address" => __('The location address', 'dbem'), "location_town" => __('The location town', 'dbem'));

add_action('init', 'dbem_load_textdomain');
function dbem_load_textdomain() {
	$thisDir = dirname( plugin_basename( __FILE__ ) );
	load_plugin_textdomain('dbem', false, $thisDir.'/langs'); 
}

// To enable activation through the activate function
register_activation_hook(__FILE__,'dbem_install');

// filters for general events field (corresponding to those of  "the _title")
add_filter('dbem_general', 'wptexturize');
add_filter('dbem_general', 'convert_chars');
add_filter('dbem_general', 'trim');
// filters for the notes field  (corresponding to those of  "the _content")   
add_filter('dbem_notes', 'wptexturize');
add_filter('dbem_notes', 'convert_smilies');
add_filter('dbem_notes', 'convert_chars');
add_filter('dbem_notes', 'wpautop');
add_filter('dbem_notes', 'prepend_attachment');
// RSS general filters
add_filter('dbem_general_rss', 'strip_tags');
add_filter('dbem_general_rss', 'ent2ncr', 8);
add_filter('dbem_general_rss', 'wp_specialchars');
// RSS content filter
add_filter('dbem_notes_rss', 'convert_chars', 8);    
add_filter('dbem_notes_rss', 'ent2ncr', 8);

add_filter('dbem_notes_map', 'convert_chars', 8);
add_filter('dbem_notes_map', 'js_escape');
      
/* Creating the wp_events table to store event data*/
function dbem_install() {
 	// Creates the events table if necessary
	dbem_create_events_table();
	dbem_create_recurrence_table();  
	dbem_create_locations_table();
  	dbem_create_bookings_table();
  	dbem_create_people_table();
	dbem_create_categories_table();
	dbem_add_options();
  	// if ANY 1.0 option is there  AND the version options hasn't been set yet THEN launch the update script 
	
	if (get_option('dbem_events_page') && !get_option('dbem_version')) 
		dbem_migrate_old_events();
  
  	update_option('dbem_version', 3); 
	// Create events page if necessary
 	$events_page_id = get_option('dbem_events_page')  ;
	if ($events_page_id != "" ) {
		query_posts("page_id=$events_page_id");
		$count = 0;
		while(have_posts()) { the_post();
	 		$count++;
		}
		if ($count == 0)
			dbem_create_events_page(); 
	  } else {
		  dbem_create_events_page(); 
	  }
		// wp-content must be chmodded 777. Maybe just wp-content.
	   if(!file_exists("../".IMAGE_UPLOAD_DIR))
				mkdir("../".IMAGE_UPLOAD_DIR, 0777);
}

function dbem_create_events_table() {
	global  $wpdb, $user_level;
	$version = get_option('dbem_version');
	
	$old_table_name = $wpdb->prefix."events";
	$table_name = $wpdb->prefix.EVENTS_TBNAME;
	
	if(!($wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") != $old_table_name)) { 
		// upgrading from previous versions             
		    
		$sql = "ALTER TABLE $old_table_name RENAME $table_name;";
		$wpdb->query($sql); 
	}
 
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		// check the user is allowed to make changes
		// get_currentuserinfo();
		// if ($user_level < 8) { return; }
	
		// Creating the events table
		$sql = "CREATE TABLE ".$table_name." (
			event_id mediumint(9) NOT NULL AUTO_INCREMENT,
			event_author mediumint(9) DEFAULT NULL,
			event_name text NOT NULL,
			event_start_time time NOT NULL,
			event_end_time time NOT NULL,
			event_start_date date NOT NULL,
			event_end_date date NULL, 
			event_notes longtext DEFAULT NULL,
			event_rsvp bool NOT NULL DEFAULT 0,
			event_seats mediumint(9),
			event_contactperson_id mediumint(9) NULL,  
			location_id mediumint(9) NOT NULL,
			recurrence_id mediumint(9) NULL,
  			event_category_id int(11) default NULL,
  			event_attributes text NULL, 
  			event_page_title_format text NULL, 
  			event_single_event_format text NULL, 
  			event_contactperson_email_body text NULL, 
  			event_respondent_email_body text NULL, 
			registration_requires_approval bool DEFAULT 0,
			UNIQUE KEY (event_id)
			);";
		
		$wpdb->query($sql); 
		//--------------  DEBUG CODE to insert a few events n the new table
		// get the current timestamp into an array
		$timestamp = time();
		$date_time_array = getdate($timestamp);

		$hours = $date_time_array['hours'];
		$minutes = $date_time_array['minutes'];
		$seconds = $date_time_array['seconds'];
		$month = $date_time_array['mon'];
		$day = $date_time_array['mday'];
		$year = $date_time_array['year'];

		// use mktime to recreate the unix timestamp
		// adding 19 hours to $hours
		$in_one_week = strftime('%Y-%m-%d', mktime($hours,$minutes,$seconds,$month,$day+7,$year));
		$in_four_weeks = strftime('%Y-%m-%d',mktime($hours,$minutes,$seconds,$month,$day+28,$year)); 
		$in_one_year = strftime('%Y-%m-%d',mktime($hours,$minutes,$seconds,$month,$day,$year+1)); 
		
		$wpdb->query("INSERT INTO ".$table_name." (event_name, event_start_date, event_start_time, event_end_time, location_id)
				VALUES ('Orality in James Joyce Conference', '$in_one_week', '16:00:00', '18:00:00', 1)");
		$wpdb->query("INSERT INTO ".$table_name." (event_name, event_start_date, event_start_time, event_end_time, location_id)
				VALUES ('Traditional music session', '$in_four_weeks', '20:00:00', '22:00:00', 2)");
		$wpdb->query("INSERT INTO ".$table_name." (event_name, event_start_date, event_start_time, event_end_time, location_id)
					VALUES ('6 Nations, Italy VS Ireland', '$in_one_year','22:00:00', '24:00:00', 3)");
	} else {  
		// eventual maybe_add_column() for later versions
		maybe_add_column($table_name, 'event_start_date', "alter table $table_name add event_start_date date NOT NULL;"); 
		maybe_add_column($table_name, 'event_end_date', "alter table $table_name add event_end_date date NULL;");
		maybe_add_column($table_name, 'event_start_time', "alter table $table_name add event_start_time time NOT NULL;"); 
		maybe_add_column($table_name, 'event_end_time', "alter table $table_name add event_end_time time NOT NULL;"); 
		maybe_add_column($table_name, 'event_rsvp', "alter table $table_name add event_rsvp bool NOT NULL DEFAULT 0;");
		maybe_add_column($table_name, 'event_seats', "alter table $table_name add event_seats mediumint(9) NULL;"); 
		maybe_add_column($table_name, 'location_id', "alter table $table_name add location_id mediumint(9) NOT NULL;");    
		maybe_add_column($table_name, 'recurrence_id', "alter table $table_name add recurrence_id mediumint(9) NULL;"); 
		maybe_add_column($table_name, 'event_contactperson_id', "alter table $table_name add event_contactperson_id mediumint(9) NULL;");
		maybe_add_column($table_name, 'event_attributes', "alter table $table_name add event_attributes text NULL;"); 
		maybe_add_column($table_name, 'event_page_title_format', "alter table $table_name add event_page_title_format text NULL;"); 
		maybe_add_column($table_name, 'event_single_event_format', "alter table $table_name add event_single_event_format text NULL;"); 
		maybe_add_column($table_name, 'event_contactperson_email_body', "alter table $table_name add event_contactperson_email_body text NULL;"); 
		maybe_add_column($table_name, 'event_respondent_email_body', "alter table $table_name add event_respondent_email_body text NULL;"); 
		maybe_add_column($table_name, 'registration_requires_approval', "alter table $table_name add registration_requires_approval bool DEFAULT 0;"); 
		
		// Fix buggy columns
		if ($version<3) {
			$wpdb->query("ALTER TABLE $table_name MODIFY event_name text;");
			$wpdb->query("ALTER TABLE $table_name MODIFY event_notes longtext;");
			$wpdb->query("ALTER TABLE $table_name MODIFY event_author mediumint(9);");
			$wpdb->query("ALTER TABLE $table_name MODIFY event_seats mediumint(9) NULL;");
		}
	}
}

function dbem_create_recurrence_table() {
	
	global  $wpdb, $user_level;
	$version = get_option('dbem_version');
	$table_name = $wpdb->prefix.RECURRENCE_TBNAME;

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		
		$sql = "CREATE TABLE ".$table_name." (
			recurrence_id mediumint(9) NOT NULL AUTO_INCREMENT,
			recurrence_name text NOT NULL,
			recurrence_start_date date NOT NULL,
			recurrence_end_date date NOT NULL,
			recurrence_start_time time NOT NULL,
			recurrence_end_time time NOT NULL,
			recurrence_notes longtext NOT NULL,
			location_id mediumint(9) NOT NULL,
			recurrence_interval tinyint NOT NULL, 
			recurrence_freq tinytext NOT NULL,
			recurrence_byday tinytext NOT NULL,
			recurrence_byweekno tinyint NOT NULL,
			event_contactperson_id mediumint(9) NULL,
  			event_category_id int(11) default NULL,
  			event_page_title_format text NULL, 
  			event_single_event_format text NULL, 
  			event_contactperson_email_body text NULL, 
  			event_respondent_email_body text NULL, 
			registration_requires_approval bool DEFAULT 0,
			UNIQUE KEY (recurrence_id)
			);";
		$wpdb->query($sql); 
	} else {
		maybe_add_column($table_name, 'event_category_id', "alter table $table_name add event_category_id int(11) default NULL;");    
		maybe_add_column($table_name, 'event_page_title_format', "alter table $table_name add event_page_title_format text NULL;"); 
		maybe_add_column($table_name, 'event_single_event_format', "alter table $table_name add event_single_event_format text NULL;"); 
		maybe_add_column($table_name, 'event_contactperson_email_body', "alter table $table_name add event_contactperson_email_body text NULL;"); 
		maybe_add_column($table_name, 'event_respondent_email_body', "alter table $table_name add event_respondent_email_body text NULL;"); 
		maybe_add_column($table_name, 'registration_requires_approval', "alter table $table_name add registration_requires_approval bool DEFAULT 0;"); 
		// Fix buggy columns
		if ($version<3) {
			$wpdb->query("ALTER TABLE $table_name MODIFY recurrence_byday tinytext NOT NULL ;");
			$wpdb->query("ALTER TABLE $table_name MODIFY recurrence_name text;");
			$wpdb->query("ALTER TABLE $table_name MODIFY recurrence_notes longtext;");
		}
	}
}

function dbem_create_locations_table() {
	
	global  $wpdb, $user_level;
	$version = get_option('dbem_version');
	$table_name = $wpdb->prefix.LOCATIONS_TBNAME;

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		
		// check the user is allowed to make changes
		// get_currentuserinfo();
		// if ($user_level < 8) { return; }

		// Creating the events table
		$sql = "CREATE TABLE ".$table_name." (
			location_id mediumint(9) NOT NULL AUTO_INCREMENT,
			location_name text NOT NULL,
			location_address tinytext NOT NULL,
			location_town tinytext NOT NULL,
			location_latitude float DEFAULT NULL,
			location_longitude float DEFAULT NULL,
			location_description text DEFAULT NULL,
			UNIQUE KEY (location_id)
			);";
		$wpdb->query($sql); 
		
		$wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude)
					VALUES ('Arts Millenium Building', 'Newcastle Road','Galway', 53.275, -9.06532)");
		$wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude)
					VALUES ('The Crane Bar', '2, Sea Road','Galway', 53.2692, -9.06151)");
		$wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude)
					VALUES ('Taaffes Bar', '19 Shop Street','Galway', 53.2725, -9.05321)");
	} else {
		if ($version<3) {
			$wpdb->query("ALTER TABLE $table_name MODIFY location_name text NOT NULL ;");
		}
	}
}

function dbem_create_bookings_table() {
	global  $wpdb, $user_level;
	$version = get_option('dbem_version');
	$table_name = $wpdb->prefix.BOOKINGS_TBNAME;

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE ".$table_name." (
			booking_id mediumint(9) NOT NULL AUTO_INCREMENT,
			event_id mediumint(9) NOT NULL,
			person_id mediumint(9) NOT NULL, 
			booking_seats mediumint(9) NOT NULL,
			booking_approved bool DEFAULT 0,
			booking_comment text DEFAULT NULL,
			UNIQUE KEY  (booking_id)
			);";
		$wpdb->query($sql); 
	} else {
		maybe_add_column($table_name, 'booking_comment', "ALTER TABLE $table_name add booking_comment text DEFAULT NULL;"); 
		maybe_add_column($table_name, 'booking_approved', "ALTER TABLE $table_name add booking_approved bool DEFAULT 0;"); 
		if ($version<3) {
			$wpdb->query("ALTER TABLE $table_name MODIFY event_id mediumint(9) NOT NULL;");
			$wpdb->query("ALTER TABLE $table_name MODIFY person_id mediumint(9) NOT NULL;");
			$wpdb->query("ALTER TABLE $table_name MODIFY booking_seats mediumint(9) NOT NULL;");
		}
	}
}

function dbem_create_people_table() {
	global  $wpdb, $user_level;
	$table_name = $wpdb->prefix.PEOPLE_TBNAME;

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE ".$table_name." (
			person_id mediumint(9) NOT NULL AUTO_INCREMENT,
			person_name tinytext NOT NULL, 
			person_email tinytext NOT NULL,
			person_phone tinytext NOT NULL,
			UNIQUE KEY (person_id)
			);";
		$wpdb->query($sql); 
	}
} 

function dbem_create_categories_table() {
	global  $wpdb, $user_level;
	$table_name = $wpdb->prefix.CATEGORIES_TBNAME;

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE ".$table_name." (
			category_id int(11) NOT NULL auto_increment,
			category_name tinytext NOT NULL,
			PRIMARY KEY  (category_id)
			);";
		$wpdb->query($sql); 
	}
}

function dbem_migrate_old_events() {         
		global $wpdb;  
		
		$events_table = $wpdb->prefix.EVENTS_TBNAME;
		$sql = "SELECT event_id, event_time, event_venue, event_address, event_town FROM $events_table";
		//echo $sql;
		$events = $wpdb->get_results($sql, ARRAY_A);
		foreach($events as $event) {

			// Migrating location data to the location table
			$location = array('location_name' => $event['event_venue'], 'location_address' => $event['event_address'], 'location_town' => $event['event_town']);
			$related_location = dbem_get_identical_location($location); 
				 
				if ($related_location)  {
					$event['location_id'] = $related_location['location_id'];     
				}
				else {
			   	$new_location = dbem_insert_location($location);
				  $event['location_id']= $new_location['location_id'];
				}                                 
		 		// migrating event_time to event_start_date and event_start_time
				$event['event_start_date'] = substr($event['event_time'],0,10); 
		    	$event['event_start_time'] = substr($event['event_time'],11,8);
				$event['event_end_time'] = substr($event['event_time'],11,8);
				
				$where = array('event_id' => $event['event_id']); 
	   			$wpdb->update($events_table, $event, $where); 	
		}
}

function dbem_add_options($reset=0) {
	$contact_person_email_body_localizable = __("#_RESPNAME (#_RESPEMAIL) will attend #_NAME on #m #d, #Y. He wants to reserve #_SPACES space(s).<br/>Now there are #_RESERVEDSPACES space(s) reserved, #_AVAILABLESPACES are still available.<br/><br/>Yours faithfully,<br/>Events Manager",'dbem') ;
	$respondent_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>you have successfully reserved #_SPACES space(s) for #_NAME.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'dbem');
	$registration_pending_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>your request to reserve #_SPACES space(s) for #_NAME is pending.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'dbem');
	$registration_denied_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>your request to reserve #_SPACES space(s) for #_NAME has been denied.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'dbem');
	
	$dbem_options = array('dbem_event_list_item_format' => DEFAULT_EVENT_LIST_ITEM_FORMAT,
	'dbem_display_calendar_in_events_page' => 0,
	'dbem_single_event_format' => DEFAULT_SINGLE_EVENT_FORMAT,
	'dbem_event_page_title_format' => DEFAULT_EVENT_PAGE_TITLE_FORMAT,
	'dbem_list_events_page' => 0,   
	'dbem_events_page_title' => DEFAULT_EVENTS_PAGE_TITLE,
	'dbem_no_events_message' => __('No events','dbem'),
	'dbem_location_page_title_format' => DEFAULT_LOCATION_PAGE_TITLE_FORMAT,
	'dbem_location_baloon_format' => DEFAULT_LOCATION_BALLOON_FORMAT,
	'dbem_location_event_list_item_format' => DEFAULT_LOCATION_EVENT_LIST_ITEM_FORMAT,
	'dbem_location_no_events_message' => DEFAULT_LOCATION_NO_EVENTS_MESSAGE,
	'dbem_single_location_format' => DEFAULT_SINGLE_LOCATION_FORMAT,
	'dbem_rss_main_title' => get_bloginfo('title')." - ".__('Events'),
	'dbem_rss_main_description' => get_bloginfo('description')." - ".__('Events'),
	'dbem_rss_description_format' => DEFAULT_RSS_DESCRIPTION_FORMAT,
	'dbem_rss_title_format' => DEFAULT_RSS_TITLE_FORMAT,
	'dbem_gmap_is_active'=> DEFAULT_GMAP_ENABLED,
	'dbem_default_contact_person' => 1,
	'dbem_captcha_for_booking' => 0 ,
	'dbem_rsvp_mail_notify_is_active' => 0 ,
	'dbem_contactperson_email_body' => preg_replace("/<br ?\/?>/", "\n", $contact_person_email_body_localizable),        
	'dbem_respondent_email_body' => preg_replace("/<br ?\/?>/", "\n", $respondent_email_body_localizable),
	'dbem_registration_pending_email_body' => preg_replace("/<br ?\/?>/", "\n", $registration_pending_email_body_localizable),
	'dbem_registration_denied_email_body' => preg_replace("/<br ?\/?>/", "\n", $registration_denied_email_body_localizable),
	'dbem_rsvp_mail_port' => 25,
	'dbem_smtp_host' => 'localhost',
	'dbem_mail_sender_name' => '',
	'dbem_rsvp_mail_send_method' => 'smtp',  
	'dbem_rsvp_mail_SMTPAuth' => 0,
	'dbem_image_max_width' => DEFAULT_IMAGE_MAX_WIDTH,
	'dbem_image_max_height' => DEFAULT_IMAGE_MAX_HEIGHT,
	'dbem_image_max_size' => DEFAULT_IMAGE_MAX_SIZE,
	'dbem_full_calendar_event_format' => DEFAULT_FULL_CALENDAR_EVENT_FORMAT,
	'dbem_small_calendar_event_title_format' => DEFAULT_SMALL_CALENDAR_EVENT_TITLE_FORMAT,
	'dbem_small_calendar_event_title_separator' => DEFAULT_SMALL_CALENDAR_EVENT_TITLE_SEPARATOR, 
	'dbem_hello_to_user' => 1,
	'dbem_use_select_for_locations' => DEFAULT_USE_SELECT_FOR_LOCATIONS,
	'dbem_attributes_enabled' => DEFAULT_ATTRIBUTES_ENABLED,
	'dbem_recurrence_enabled' => DEFAULT_RECURRENCE_ENABLED,
	'dbem_rsvp_enabled' => DEFAULT_RSVP_ENABLED,
	'dbem_categories_enabled' => DEFAULT_CATEGORIES_ENABLED);
	
	foreach($dbem_options as $key => $value){
		#if(preg_match('/$dbem/', $key)){
			dbem_add_option($key, $value, $reset);
		#}
	}
		
}
function dbem_add_option($key, $value, $reset) {
	$option = get_option($key);
	if (empty($option) || $reset)
		update_option($key, $value);
}      

function dbem_create_events_page(){
	global $wpdb;
	$postarr = array(
		'post_status'=> 'publish',
		'post_title' => DEFAULT_EVENT_PAGE_NAME,
		'post_name'  => $wpdb->escape(__('events','dbem')),
		'post_type'  => 'page',
	);
	if($int_post_id = wp_insert_post($postarr)){
		update_option('dbem_events_page', $int_post_id);
	}
}   

// Create the Manage Events and the Options submenus 
add_action('admin_menu','dbem_create_events_submenu');     
function dbem_create_events_submenu () {
	  if(function_exists('add_submenu_page')) {
	  	add_object_page(__('Events', 'dbem'),__('Events', 'dbem'),MIN_CAPABILITY,'events-manager','dbem_events_subpanel', DBEM_PLUGIN_URL.'images/calendar-16.png');
	   	// Add a submenu to the custom top-level menu: 
		$plugin_page = add_submenu_page('events-manager', __('Edit'),__('Edit'),MIN_CAPABILITY,'events-manager','dbem_events_subpanel');
		add_action( 'admin_head-'. $plugin_page, 'dbem_admin_general_script' );
		$plugin_page = add_submenu_page('events-manager', __('Add new', 'dbem'), __('Add new','dbem'), MIN_CAPABILITY, 'events-manager-new_event', "dbem_new_event_page");
		add_action( 'admin_head-'. $plugin_page, 'dbem_admin_general_script' ); 
		$plugin_page = add_submenu_page('events-manager', __('Locations', 'dbem'), __('Locations', 'dbem'), MIN_CAPABILITY, 'events-manager-locations', "dbem_locations_page");
		add_action( 'admin_head-'. $plugin_page, 'dbem_admin_general_script' );
		$plugin_page = add_submenu_page('events-manager', __('Event Categories','dbem'),__('Categories','dbem'), SETTING_CAPABILITY, "events-manager-categories", 'dbem_categories_subpanel');
                add_action( 'admin_head-'. $plugin_page, 'dbem_admin_general_script' );
		$plugin_page = add_submenu_page('events-manager', __('People', 'dbem'), __('People', 'dbem'), MIN_CAPABILITY, 'events-manager-people', "dbem_people_page");
		add_action( 'admin_head-'. $plugin_page, 'dbem_admin_general_script' ); 
		$plugin_page = add_submenu_page('events-manager', __('Pending Approvals', 'dbem'), __('Pending Approvals', 'dbem'), MIN_CAPABILITY, 'events-manager-registration-approval', "dbem_registration_approval_page");
		add_action( 'admin_head-'. $plugin_page, 'dbem_admin_general_script' ); 
		$plugin_page = add_submenu_page('events-manager', __('Change Registration', 'dbem'), __('Change Registration', 'dbem'), MIN_CAPABILITY, 'events-manager-registration-seats', "dbem_registration_seats_page");
		add_action( 'admin_head-'. $plugin_page, 'dbem_admin_general_script' ); 
		//add_submenu_page('events-manager', 'Test ', 'Test ', 8, 'test', 'dbem_recurrence_test');
		$plugin_page = add_submenu_page('events-manager', __('Events Manager Settings','dbem'),__('Settings','dbem'), SETTING_CAPABILITY, "events-manager-options", 'dbem_options_subpanel');
		add_action( 'admin_head-'. $plugin_page, 'dbem_admin_general_script' );
  	}
}

function dbem_replace_placeholders($format, $event, $target="html") {

	// first we do the custom attributes, since these can contain other placeholders
	/* Marcus Begin Edit */
	//This is for the custom attributes
	preg_match_all("/#_ATT\{.+?\}(\{.+?\})?/", $format, $results);
	foreach($results[0] as $resultKey => $result) {
		//Strip string of placeholder and just leave the reference
		$attRef = substr( substr($result, 0, strpos($result, '}')), 6 );
		$attString = $event['event_attributes'][$attRef];
		if( trim($attString) == '' && $results[1][$resultKey] != '' ){
			//Check to see if we have a second set of braces;
			$attString = substr( $results[1][$resultKey], 1, strlen(trim($results[1][$resultKey]))-2 );
		}
		$format = str_replace($result, $attString ,$format );
	}
	/* Marcus End Edit */

	// and now all the other placeholders
 	$event_string = $format;
	preg_match_all("/#@?_?[A-Za-z0-9]+/", $format, $placeholders);
	foreach($placeholders[0] as $result) {
		// echo "RESULT: $result <br>";
		// matches all fields placeholder  
		//TODO CUSTOM FIX FOR Brian
		// EVENTUALLY REMOVE 
		if (preg_match('/#_JCCSTARTTIME/', $result)) { 
			$time = substr($event['event_start_time'], 0,5);
			$event_string = str_replace($result, $time , $event_string );		
			} 
		// END of REMOVE
		if (preg_match('/#_EDITEVENTLINK/', $result)) { 
			$link = "";
			if(is_user_logged_in())
				$link = "<a href=' ".admin_url("admin.php?page=events-manager&action=edit_event&event_id=".$event['event_id'])."'>".__('Edit')."</a>";
			$event_string = str_replace($result, $link , $event_string );
		}
		if (preg_match('/#_24HSTARTTIME/', $result)) { 
			$time = substr($event['event_start_time'], 0,5);
			$event_string = str_replace($result, $time , $event_string );
		}
		if (preg_match('/#_24HENDTIME/', $result)) { 
			$time = substr($event['event_end_time'], 0,5);
			$event_string = str_replace($result, $time , $event_string );
		}
		
		if (preg_match('/#_12HSTARTTIME/', $result)) {
			$AMorPM = "AM"; 
			$hour = substr($event['event_start_time'], 0,2);
			$minute = substr($event['event_start_time'], 3,2);
			// 12:00 in 24H time = 12:00PM
			// 13:01 in 24H time = 01:01PM
			// 00:00 in 24H time = 12:00AM
			if ($hour >= 12) {
				$hour = $hour-12;
				$AMorPM = "PM";
			}
			// hour 0 does not exist in AM/PM notation
			if ($hour == 0) $hour=12;
			$time = "$hour:$minute $AMorPM";
			$event_string = str_replace($result, $time , $event_string );		
		}
		if (preg_match('/#_12HENDTIME/', $result)) {
			$AMorPM = "AM"; 
			$hour = substr($event['event_end_time'], 0,2);   
			$minute = substr($event['event_end_time'], 3,2);
			// 12:00 in 24H time = 12:00PM
			// 13:01 in 24H time = 01:01PM
			// 00:00 in 24H time = 12:00AM
			if ($hour >= 12) {
				$hour = $hour-12;
				$AMorPM = "PM";
			}
			if ($hour == 0) $hour=12;
			$time = "$hour:$minute $AMorPM";
			$event_string = str_replace($result, $time , $event_string );		
		}		
		
		if (preg_match('/#_MAP/', $result)) {
			$location = dbem_get_location($event['location_id']);
			$map_div = dbem_single_location_map($location);
		  	$event_string = str_replace($result, $map_div , $event_string ); 
		}
		if (preg_match('/#_DIRECTIONS/', $result)) {
			$location = dbem_get_location($event['location_id']);
			$directions_form = dbem_add_directions_form($location);
		  	$event_string = str_replace($result, $directions_form , $event_string ); 
		}
		if (preg_match('/#_ADDBOOKINGFORM/', $result)) {
		 	$rsvp_is_active = get_option('dbem_rsvp_enabled'); 
			if ($event['event_rsvp']) {
				$rsvp_add_module .= dbem_add_booking_form($event['event_id']);
			} else {
				$rsvp_add_module .= "";
			}
		 	$event_string = str_replace($result, $rsvp_add_module , $event_string );
		}
		if (preg_match('/#_REMOVEBOOKINGFORM/', $result)) {
		 	$rsvp_is_active = get_option('dbem_rsvp_enabled'); 
			if ($event['event_rsvp']) {
				$rsvp_delete_module .= dbem_delete_booking_form();
			} else {
				$rsvp_delete_module .= "";
			}
		 	$event_string = str_replace($result, $rsvp_delete_module , $event_string );
		}
		if (preg_match('/#_AVAILABLESPACES|#_AVAILABLESEATS/', $result)) {
                        $rsvp_is_active = get_option('dbem_rsvp_enabled');
                        if ($event['event_rsvp']) {
				$available_seats .= dbem_get_available_seats($event['event_id']);
                        } else {
                                $available_seats .= "";
                        }
                        $event_string = str_replace($result, $available_seats , $event_string );
                }
		if (preg_match('/#_(RESERVEDSPACES|BOOKEDSEATS)/', $result)) {
                        $rsvp_is_active = get_option('dbem_rsvp_enabled');
                        if ($event['event_rsvp']) {
				$booked_seats .= dbem_get_booked_seats($event['event_id']);
                        } else {
                                $booked_seats .= "";
                        }
                        $event_string = str_replace($result, $booked_seats , $event_string );
                }
		if (preg_match('/#_(AVAILABLESPACES|AVAILABLESEATS)/', $result)) {
                        $rsvp_is_active = get_option('dbem_rsvp_enabled');
                        if ($event['event_rsvp']) {
				$availble_seats .= dbem_get_available_seats($event['event_id']);
                        } else {
                                $availble_seats .= "";
                        }
                        $event_string = str_replace($result, $availble_seats , $event_string );
                }
		if (preg_match('/#_LINKEDNAME/', $result)) {
			$events_page_link = dbem_get_events_page(true, false);
			if (stristr($events_page_link, "?"))
				$joiner = "&";
			else
				$joiner = "?";
			$event_string = str_replace($result, "<a href='".$events_page_link.$joiner."event_id=".$event['event_id']."' title='".dbem_sanitize_html($event['event_name'])."'>".dbem_sanitize_html($event['event_name'])."</a>" , $event_string );
		} 
		if (preg_match('/#_EVENTPAGEURL(\[(.+\)]))?/', $result)) {
			$events_page_link = dbem_get_events_page(true, false);
			if (stristr($events_page_link, "?"))
				$joiner = "&";
			else
				$joiner = "?";
			$event_string = str_replace($result, $events_page_link.$joiner."event_id=".$event['event_id'] , $event_string );
		}
	 	if (preg_match('/#_(NOTES|EXCERPT)/', $result)) {
			$field = "event_".ltrim(strtolower($result), "#_");
		 	$field_value = $event[$field];      
			
			if ($target == "html") {
				//If excerpt, we use more link text
				if($field == "event_excerpt") {
					$matches = explode('<!--more-->', $event['event_notes']);
					$field_value = $matches[0];
					$field_value = apply_filters('dbem_notes_excerpt', $field_value);
				} else {
					$field_value = apply_filters('dbem_notes', $field_value);
				}
				//$field_value = apply_filters('the_content', $field_value); - chucks a wobbly if we do this.
				// we call the sanitize_html function so the qtranslate
				// does it's thing anyway
				$field_value = dbem_sanitize_html($field_value,0);
			} else {
				if ($target == "map") {
					$field_value = apply_filters('dbem_notes_map', $field_value);
				} else {
		  			if($field == "event_excerpt"){
						$matches = explode('<!--more-->', $event['event_notes']);
						$field_value = htmlentities($matches[0]);
						$field_value = apply_filters('dbem_notes_rss', $field_value);
					}else{
						$field_value = apply_filters('dbem_notes_rss', $field_value);
					}
					$field_value = apply_filters('the_content_rss', $field_value);
				}
			}
			$event_string = str_replace($result, $field_value , $event_string ); 
	 	}
	 	if (preg_match('/#_NAME/', $result)) {
			$field = "event_".ltrim(strtolower($result), "#_");
		 	$field_value = $event[$field];      
			$field_value = dbem_sanitize_html($field_value);
			if ($target == "html") {    
				$field_value = apply_filters('dbem_general', $field_value); 
		  	} else {
				$field_value = apply_filters('dbem_general_rss', $field_value);
			}
			$event_string = str_replace($result, $field_value , $event_string ); 
	 	}  
	  
		if (preg_match('/#_(ADDRESS|TOWN)/', $result)) {
			$field = "location_".ltrim(strtolower($result), "#_");
		 	$field_value = $event[$field];      
			$field_value = dbem_sanitize_html($field_value);
			if ($target == "html") {
				$field_value = apply_filters('dbem_general', $field_value); 
			} else { 
				$field_value = apply_filters('dbem_general_rss', $field_value); 
			}
			$event_string = str_replace($result, $field_value , $event_string ); 
	 	}
	  
		if (preg_match('/#_(LOCATION)$/', $result)) {
			$field = "location_name";
		 	$field_value = $event[$field];     
			$field_value = dbem_sanitize_html($field_value);
			if ($target == "html") {
				$field_value = apply_filters('dbem_general', $field_value); 
			} else {
				$field_value = apply_filters('dbem_general_rss', $field_value); 
			}
			
			$event_string = str_replace($result, $field_value , $event_string ); 
	 	}
	 	if (preg_match('/#_CONTACTNAME$/', $result)) {
      		$event['event_contactperson_id'] ? $user_id = $event['event_contactperson_id'] : $user_id = get_option('dbem_default_contact_person');
			$name = dbem_get_user_name($user_id);
			$event_string = str_replace($result, $name, $event_string );
		}
		if (preg_match('/#_CONTACTEMAIL$/', $result)) {         
			$event['event_contactperson_id'] ? $user_id = $event['event_contactperson_id'] : $user_id = get_option('dbem_default_contact_person');
      			$email = dbem_get_user_email($user_id);
			// ascii encode for primitive harvesting protection ...
			$event_string = str_replace($result, dbem_ascii_encode($email), $event_string );
		}
		if (preg_match('/#_CONTACTPHONE$/', $result)) {   
			$event['event_contactperson_id'] ? $user_id = $event['event_contactperson_id'] : $user_id = get_option('dbem_default_contact_person');
      			$phone = dbem_get_user_phone($user_id);
			// ascii encode for primitive harvesting protection ...
			$event_string = str_replace($result, dbem_ascii_encode($phone), $event_string );
		}	
		if (preg_match('/#_(IMAGE)/', $result)) {
			if($event['location_image_url'] != '')
				  $location_image = "<img src='".$event['location_image_url']."' alt='".$event['location_name']."'/>";
				else
					$location_image = "";
				$event_string = str_replace($result, $location_image , $event_string ); 
		 	}
	  
		 if (preg_match('/#_(LOCATIONPAGEURL)/', $result)) { 
			$events_page_link = dbem_get_events_page(true, false);
			if (stristr($events_page_link, "?"))
			 	$joiner = "&";
			else
			 	$joiner = "?";
			$venue_page_link = $events_page_link.$joiner."location_id=".$event['location_id'];
			$event_string = str_replace($result, $venue_page_link , $event_string ); 
		}

		// matches all PHP date placeholders for startdate
		if (preg_match('/^#[dDjlNSwzWFmMntLoYy]$/', $result)) {
			$event_string = str_replace($result, mysql2date(ltrim($result, "#"), $event['event_start_date']),$event_string );  
		}
		
		// matches all PHP time placeholders for enddate
		if (preg_match('/^#@[dDjlNSwzWFmMntLoYy]$/', $result)) {
			$event_string = str_replace($result, mysql2date(ltrim($result, "#@"), $event['event_end_date']), $event_string ); 
	 	}		    
		
		// matches all PHP time placeholders for starttime
		if (preg_match('/^#[aABgGhHisueIOPTZcrU]$/', $result)) {   
			// mysql2date expects a date-part in the string as well, since the value $event['event_start_time'] does not have this,
			// we add a default date to it (2010-10-10)
			$event_string = str_replace($result, mysql2date(ltrim($result, "#"), "2010-10-10 ".$event['event_start_time']),$event_string ); 
			//echo $event['event_start_time'];
			//echo mysql2date('h:i A', '2010-10-10 23:35:00')."<br/>"; 
			// echo $event_string;  
		}
		
		// matches all PHP time placeholders for endtime
		if (preg_match('/^#@[aABgGhHisueIOPTZcrU]$/', $result)) {
			$event_string = str_replace($result, mysql2date(ltrim($result, "#@"), "2010-10-10 ".$event['event_end_time']),$event_string );  
		}
		
		//Add a placeholder for categories
		if (preg_match('/^#_CATEGORY$/', $result)) {
	      		$category = (dbem_get_event_category($event['event_id']));
			$field_value = dbem_sanitize_html($category['category_name']);
			$event_string = str_replace($result, $field_value, $event_string );
		}
	}
		     
	// for extra date formatting, eg. #_{d/m/Y}
	preg_match_all("/#@?_\{[A-Za-z0-9 -\/,\.\\\]+\}/", $format, $results);
	foreach($results[0] as $result) {
		if(substr($result, 0, 3 ) == "#@_"){
			$date = 'event_end_date';
			$offset = 4;
		}else{
			$date = 'event_start_date';
			$offset = 3;
		}
		if( $date == 'event_end_date' && $event[$date] == $event['event_start_date'] ){
			$event_string = str_replace($result, '', $event_string);
		}else{
			$event_string = str_replace($result, mysql2date(substr($result, $offset, (strlen($result)-($offset+1)) ), $event[$date]),$event_string );
		}
	}
	return $event_string;	
}

function dbem_date_to_unix_time($date) {
		$unix_time = mktime(0, 0, 0, substr($date,5,2), substr($date,8,2), substr($date,0,4));
		return $unix_time;   
}   
function dbem_sanitize_request( $value ) {
#	if( get_magic_quotes_gpc() ) 
#		$value = stripslashes( $value );

	//check if this function exists
	if( function_exists( "mysql_real_escape_string" ) ) {
		//$value = mysql_real_escape_string( $value );
		if (is_array($value)) {
			array_walk_recursive($value, 'escapeMe');
		} else {
			$value = mysql_real_escape_string($value);
		}
	} else {
		//for PHP version < 4.3.0 use addslashes
		$value = addslashes( $value );
	}
	return $value;
}
function escapeMe(&$val) {
	$val = mysql_real_escape_string($val);
}

function dbem_sanitize_html( $value, $do_convert=1 ) {
	if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) $value = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($value);
	if ($do_convert) {
		return htmlspecialchars($value,ENT_QUOTES);
	} else {
		return $value;
	}
}
?>
