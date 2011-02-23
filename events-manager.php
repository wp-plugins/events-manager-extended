<?php
/*
Plugin Name: Events Manager Extended
Version: 3.3.1
Plugin URI: http://www.e-dynamics.be/wordpress
Description: Description: Manage and display events. Includes recurring events; locations; widgets; Google maps; RSVP; ICAL and RSS feeds. <a href="admin.php?page=events-manager-options">Settings</a> | <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=SMGDS4GLCYWNG&lc=BE&item_name=To%20support%20development%20of%20EME&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted">Donate</a>
Author: Franky Van Liedekerke
Author URI: http://www.e-dynamics.be/
*/

/*
Copyright (c) 2009, Davide Benini.  $Revision: 1 $
Copyright (c) 2010, Franky Van Liedekerke.
Copyright (c) 2011, Franky Van Liedekerke.

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
define('EME_DB_VERSION', 15);
define('EME_PLUGIN_URL', plugins_url('',plugin_basename(__FILE__)).'/'); //PLUGIN DIRECTORY
define('EME_PLUGIN_DIR', ABSPATH.PLUGINDIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__))); //PLUGIN DIRECTORY
define('EVENTS_TBNAME','dbem_events'); //TABLE NAME
define('RECURRENCE_TBNAME','dbem_recurrence'); //TABLE NAME
define('LOCATIONS_TBNAME','dbem_locations'); //TABLE NAME
define('BOOKINGS_TBNAME','dbem_bookings'); //TABLE NAME
define('PEOPLE_TBNAME','dbem_people'); //TABLE NAME
define('BOOKING_PEOPLE_TBNAME','dbem_bookings_people'); //TABLE NAME
define('CATEGORIES_TBNAME', 'dbem_categories');
define('DEFAULT_EVENT_PAGE_NAME', 'Events');
define('MIN_CAPABILITY', 'edit_posts');   // Minimum user level to edit own events
define('AUTHOR_CAPABILITY', 'publish_posts');   // Minimum user level to put an event in public/private state
define('EDIT_CAPABILITY', 'edit_others_posts'); // Minimum user level to edit any event
define('SETTING_CAPABILITY', 'activate_plugins');  // Minimum user level to edit settings
define('DEFAULT_EVENT_LIST_ITEM_FORMAT', '<li>#j #M #Y - #H:#i<br /> #_LINKEDNAME<br />#_TOWN </li>');
define('DEFAULT_SINGLE_EVENT_FORMAT', '<p>#j #M #Y - #H:#i</p><p>#_TOWN</p><p>#_NOTES</p>'); 
define('DEFAULT_EVENTS_PAGE_TITLE',__('Events','eme') ) ;
define('DEFAULT_EVENT_PAGE_TITLE_FORMAT', '#_NAME'); 
define('DEFAULT_RSS_DESCRIPTION_FORMAT',"#j #M #y - #H:#i <br />#_LOCATION <br />#_ADDRESS <br />#_TOWN");
define('DEFAULT_RSS_TITLE_FORMAT',"#_NAME");
define('DEFAULT_MAP_TEXT_FORMAT', '<strong>#_LOCATION</strong><p>#_ADDRESS</p><p>#_TOWN</p>');
define('DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT','<li>#_LINKEDNAME<ul><li>#j #M #y</li><li>#_TOWN</li></ul></li>');
define('DEFAULT_NO_EVENTS_MESSAGE', __('No events', 'eme'));
define('DEFAULT_SINGLE_LOCATION_FORMAT', '<p>#_ADDRESS</p><p>#_TOWN</p>'); 
define('DEFAULT_LOCATION_PAGE_TITLE_FORMAT', '#_NAME'); 
define('DEFAULT_LOCATION_BALLOON_FORMAT', "<strong>#_NAME</strong><br />#_ADDRESS - #_TOWN<br /><a href='#_LOCATIONPAGEURL'>Details</a>");
define('DEFAULT_LOCATION_EVENT_LIST_ITEM_FORMAT', "<li>#_NAME - #j #M #Y - #H:#i</li>");
define('DEFAULT_LOCATION_NO_EVENTS_MESSAGE', __('<li>No events in this location</li>', 'eme'));
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
define('DEFAULT_RSVP_ADDBOOKINGFORM_SUBMIT_STRING', __('Send your booking', 'eme'));
define('DEFAULT_RSVP_DELBOOKINGFORM_SUBMIT_STRING', __('Cancel your booking', 'eme'));
define('DEFAULT_ATTENDEES_LIST_FORMAT','<li>#_NAME</li>');
define('DEFAULT_CATEGORIES_ENABLED', true);
define('DEFAULT_GMAP_ENABLED', true);
define('DEFAULT_GMAP_ZOOMING', true);
define('DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT', "F, Y");
define('DEFAULT_SHOW_PERIOD_YEARLY_DATEFORMAT', "Y");
define('DEFAULT_FILTER_FORM_FORMAT', "#_FILTER_CATS #_FILTER_LOCS");
define('STATUS_PUBLIC', 1);
define('STATUS_PRIVATE', 2);
define('STATUS_DRAFT', 5);
$upload_info = wp_upload_dir();
define("IMAGE_UPLOAD_DIR", $upload_info['basedir']."/locations-pics");
define("IMAGE_UPLOAD_URL", $upload_info['baseurl']."/locations-pics");


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

// Localised date formats as in the jquery UI datepicker plugin
$localised_date_formats = array("am" => "dd.mm.yy","ar" => "dd/mm/yy", "bg" => "dd.mm.yy", "ca" => "mm/dd/yy", "cs" => "dd.mm.yy", "da" => "dd-mm-yy", "de" =>"dd.mm.yy", "es" => "dd/mm/yy", "en" => "mm/dd/yy", "fi" => "dd.mm.yy", "fr" => "dd/mm/yy", "he" => "dd/mm/yy", "hu" => "yy-mm-dd", "hy" => "dd.mm.yy", "id" => "dd/mm/yy", "is" => "dd/mm/yy", "it" => "dd/mm/yy", "ja" => "yy/mm/dd", "ko" => "yy-mm-dd", "lt" => "yy-mm-dd", "lv" => "dd-mm-yy", "nb" => "yy-mm-dd", "nl" => "dd.mm.yy", "nn" => "yy-mm-dd", "no" => "yy-mm-dd", "pl" => "yy-mm-dd", "pt" => "dd/mm/yy", "ro" => "mm/dd/yy", "ru" => "dd.mm.yy", "sk" => "dd.mm.yy", "sv" => "yy-mm-dd", "th" => "dd/mm/yy", "tr" => "dd.mm.yy", "ua" => "dd.mm.yy", "uk" => "dd.mm.yy", "us" => "mm/dd/yy", "CN" => "yy-mm-dd", "TW" => "yy/mm/dd");

add_action('init', 'eme_load_textdomain');
function eme_load_textdomain() {
   $thisDir = dirname( plugin_basename( __FILE__ ) );
   load_plugin_textdomain('eme', false, $thisDir.'/langs'); 
}

//required fields
$required_fields = array('event_name'); 
$location_required_fields = array("location_name" => __('The location name', 'eme'), "location_address" => __('The location address', 'eme'), "location_town" => __('The location town', 'eme'));

// To enable activation through the activate function
register_activation_hook(__FILE__,'eme_install');
// when deactivation is needed
register_deactivation_hook(__FILE__,'eme_uninstall');
// when a new blog is added for network installation and the plugin is network activated
add_action( 'wpmu_new_blog', 'eme_new_blog', 10, 6);      

// filters for general events field (corresponding to those of  "the_title")
add_filter('eme_general', 'wptexturize');
add_filter('eme_general', 'convert_chars');
add_filter('eme_general', 'trim');
// filters for the notes field  (corresponding to those of  "the_content")
add_filter('eme_notes', 'wptexturize');
add_filter('eme_notes', 'convert_smilies');
add_filter('eme_notes', 'convert_chars');
add_filter('eme_notes', 'wpautop');
add_filter('eme_notes', 'prepend_attachment');
// RSS general filters
add_filter('eme_general_rss', 'strip_tags');
add_filter('eme_general_rss', 'ent2ncr', 8);
//add_filter('eme_general_rss', 'esc_html');
// RSS content filter
add_filter('eme_notes_rss', 'convert_chars', 8);
add_filter('eme_notes_rss', 'ent2ncr', 8);

add_filter('eme_notes_map', 'convert_chars', 8);
add_filter('eme_notes_map', 'js_escape');
 
// we only want the google map javascript to be loaded if needed, so we set a global
// variable to 0 here and if we detect #_MAP, we set it to 1. In a footer filter, we then
// check if it is 1 and if so: include it
$eme_need_gmap_js=0;

// we only want the jquery for the calendar to load if/when needed
$eme_need_calendar_js=0;

// set the timezone
$tzstring = get_option('timezone_string');
if (!empty($tzstring) ) {
   @date_default_timezone_set ($tzstring);
}

// enable shortcodes in widgets, if wanted
if (!is_admin() && get_option('eme_shortcodes_in_widgets')) {
   add_filter('widget_text', 'do_shortcode', 11);
}

add_filter('rewrite_rules_array','eme_insertMyRewriteRules');
add_filter('query_vars','eme_insertMyRewriteQueryVars');
// Remember to flush_rules() when adding rules
function eme_flushRules() {
	global $wp_rewrite;
   $wp_rewrite->flush_rules();
}

// Adding a new rule
function eme_insertMyRewriteRules($rules) {
   // the following causes an error with php 5.0.4
   // $events_page=get_page(get_option ( 'eme_events_page' ));
   // so we need to split it in 2 lines:
   $option_eme_events_page=get_option ( 'eme_events_page' );
   $events_page=get_page($option_eme_events_page);
   $page_name=$events_page->post_name;
   $newrules = array();
   $newrules['events/(\d{4})-(\d{2})-(\d{2})'] = 'index.php?pagename='.$page_name.'&calendar_day=$matches[1]-$matches[2]-$matches[3]';
   $newrules['events/(\d*)/'] = 'index.php?pagename='.$page_name.'&event_id=$matches[1]';
   $newrules['locations/(\d*)/'] = 'index.php?pagename='.$page_name.'&location_id=$matches[1]';
   return $newrules + $rules;
}

// Adding the id var so that WP recognizes it
function eme_insertMyRewriteQueryVars($vars) {
    array_push($vars, 'event_id');
    array_push($vars, 'location_id');
    array_push($vars, 'calendar_day');
    return $vars;
}

// INCLUDES
// We let the includes happen at the end, so all init-code is done
// (like eg. the load_textdomain). Some includes do stuff based on _GET
// so they need the correct info before doing stuff
include("captcha_check.php");
include("eme_functions.php");
include("eme_filters.php");
include("eme_events.php");
include("eme_calendar.php");
include("eme_widgets.php");
include("eme_rsvp.php");
include("eme_locations.php"); 
include("eme_people.php");
include("eme_recurrence.php");
include("eme_UI_helpers.php");
include("eme_categories.php");
include("eme_attributes.php");
include("eme_ical.php");
include("eme_cleanup.php");

require_once("phpmailer/eme_phpmailer.php") ;
//require_once("phpmailer/language/phpmailer.lang-en.php") ;

function eme_install() {
   global $wpdb;
   if (function_exists('is_multisite') && is_multisite()) {
      // check if it is a network activation - if so, run the activation function for each blog id
      if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
         $old_blog = $wpdb->blogid;
         // Get all blog ids
         $blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
         foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            _eme_install();
         }
         switch_to_blog($old_blog);
         return;
      }  
   } 
   // executed if no network activation
   _eme_install();     
}

// the private function; for activation
function _eme_install() {
   global $wpdb;
   // check the user is allowed to make changes
   if ( !current_user_can( SETTING_CAPABILITY  ) ) {
      return;
   }

   // Creates the events table if necessary
   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   $charset="";
   $collate="";
   if ( $wpdb->has_cap('collation') ) {
      if ( ! empty($wpdb->charset) )
         $charset = "DEFAULT CHARACTER SET $wpdb->charset";
      if ( ! empty($wpdb->collate) )
         $collate = "COLLATE $wpdb->collate";
   }
   $db_version = get_option('eme_version');
   if (!$db_version && get_option('dbem_version')) {
      $db_version = get_option('dbem_version');
   }
   #if (!$db_version) {
   #  eme_drop_tables();
   #}
   eme_create_events_table($charset,$collate);
   eme_create_recurrence_table($charset,$collate);
   eme_create_locations_table($charset,$collate);
   eme_create_bookings_table($charset,$collate);
   eme_create_people_table($charset,$collate);
   eme_create_categories_table($charset,$collate);
   eme_add_options();
   
   if ($db_version && $db_version<7) {
      update_option('eme_conversion_needed', 1); 
   }
   update_option('eme_version', EME_DB_VERSION); 

   // always reset the drop data option
   update_option('eme_uninstall_drop_data', 0); 
   
   // always reset the donation option
   update_option('eme_donation_done', 0); 

   // Create events page if necessary
   $events_page_id = get_option('eme_events_page');
   if (!$events_page_id && get_option('dbem_events_page')) {
      $events_page_id = get_option('dbem_events_page')  ;
      update_option('eme_events_page', $events_page_id); 
   }

   if ($events_page_id != "" ) {
      query_posts("page_id=$events_page_id");
      $count = 0;
      while(have_posts()) { the_post();
         $count++;
      }
      if ($count == 0)
         eme_create_events_page(); 
     } else {
        eme_create_events_page(); 
     }
      // wp-content must be chmodded 777. Maybe just wp-content.
      if(!file_exists(IMAGE_UPLOAD_DIR))
            mkdir(IMAGE_UPLOAD_DIR, 0777);

    // SEO rewrite rules
    eme_flushRules();
}

function eme_uninstall() {
   global $wpdb;

   if (function_exists('is_multisite') && is_multisite()) {
      // check if it is a network activation - if so, run the activation function for each blog id
      if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
         $old_blog = $wpdb->blogid;
         // Get all blog ids
         $blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
         foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            _eme_uninstall();
         }
         switch_to_blog($old_blog);
         return;
      }  
   } 
   // executed if no network activation
   _eme_uninstall();
}

function _eme_uninstall() {
   $drop_data = get_option('eme_uninstall_drop_data');
   if ($drop_data) {
      eme_drop_table(EVENTS_TBNAME);
      eme_drop_table(RECURRENCE_TBNAME);
      eme_drop_table(LOCATIONS_TBNAME);
      eme_drop_table(BOOKINGS_TBNAME);
      eme_drop_table(PEOPLE_TBNAME);
      eme_drop_table(BOOKING_PEOPLE_TBNAME);
      eme_drop_table(CATEGORIES_TBNAME);
      eme_delete_events_page();
      eme_options_delete();
   }

    // SEO rewrite rules
    eme_flushRules();
}

function eme_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
   global $wpdb;
 
   if (is_plugin_active_for_network('events-manager-extended/events-manager.php')) {
      $old_blog = $wpdb->blogid;
      switch_to_blog($blog_id);
      _eme_install();
      switch_to_blog($old_blog);
   }
}

function eme_drop_table($table) {
   global $wpdb;
   $table = $wpdb->prefix.$table;
   $wpdb->query("DROP TABLE IF EXISTS $table");
}

function eme_convert_charset($table,$charset,$collate) {
   global $wpdb;
   $table = $wpdb->prefix.$table;
   $sql = "ALTER TABLE $table CONVERT TO $charset $collate;";
   $wpdb->query($sql);
}

function eme_create_events_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   
   $old_table_name = $wpdb->prefix."events";
   $table_name = $wpdb->prefix.EVENTS_TBNAME;
   
   if(!($wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") != $old_table_name)) { 
      // upgrading from previous versions
      $sql = "ALTER TABLE $old_table_name RENAME $table_name;";
      $wpdb->query($sql); 
   }
 
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      // Creating the events table
      $sql = "CREATE TABLE ".$table_name." (
         event_id mediumint(9) NOT NULL AUTO_INCREMENT,
         event_status mediumint(9) DEFAULT 1,
         event_author mediumint(9) DEFAULT 0,
         event_name text NOT NULL,
         event_url text default NULL,
         event_start_time time NOT NULL,
         event_end_time time NOT NULL,
         event_start_date date NOT NULL,
         event_end_date date NULL, 
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         event_notes longtext DEFAULT NULL,
         event_rsvp bool DEFAULT 0,
         rsvp_number_days tinyint unsigned DEFAULT 0,
         event_seats mediumint(9) DEFAULT 0,
         event_contactperson_id mediumint(9) DEFAULT 0,
         location_id mediumint(9) DEFAULT 0,
         recurrence_id mediumint(9) DEFAULT 0,
         event_category_ids text default NULL,
         event_attributes text NULL, 
         event_page_title_format text NULL, 
         event_single_event_format text NULL, 
         event_contactperson_email_body text NULL, 
         event_respondent_email_body text NULL, 
         registration_requires_approval bool DEFAULT 0,
         registration_wp_users_only bool DEFAULT 0,
         UNIQUE KEY (event_id)
         ) $charset $collate;";
      
      dbDelta($sql);
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
      maybe_add_column($table_name, 'event_status', "alter table $table_name add event_status mediumint(9) DEFAULT 1;"); 
      maybe_add_column($table_name, 'event_start_date', "alter table $table_name add event_start_date date NOT NULL;"); 
      maybe_add_column($table_name, 'event_end_date', "alter table $table_name add event_end_date date NULL;");
      maybe_add_column($table_name, 'event_start_time', "alter table $table_name add event_start_time time NOT NULL;"); 
      maybe_add_column($table_name, 'event_end_time', "alter table $table_name add event_end_time time NOT NULL;"); 
      maybe_add_column($table_name, 'event_rsvp', "alter table $table_name add event_rsvp bool DEFAULT 0;");
      maybe_add_column($table_name, 'rsvp_number_days', "alter table $table_name add rsvp_number_days tinyint unsigned DEFAULT 0;");
      maybe_add_column($table_name, 'event_seats', "alter table $table_name add event_seats mediumint(9) DEFAULT 0;");
      maybe_add_column($table_name, 'location_id', "alter table $table_name add location_id mediumint(9) DEFAULT 0;");
      maybe_add_column($table_name, 'recurrence_id', "alter table $table_name add recurrence_id mediumint(9) DEFAULT 0;"); 
      maybe_add_column($table_name, 'event_contactperson_id', "alter table $table_name add event_contactperson_id mediumint(9) DEFAULT 0;");
      maybe_add_column($table_name, 'event_attributes', "alter table $table_name add event_attributes text NULL;"); 
      maybe_add_column($table_name, 'event_url', "alter table $table_name add event_url text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'event_page_title_format', "alter table $table_name add event_page_title_format text NULL;"); 
      maybe_add_column($table_name, 'event_single_event_format', "alter table $table_name add event_single_event_format text NULL;"); 
      maybe_add_column($table_name, 'event_contactperson_email_body', "alter table $table_name add event_contactperson_email_body text NULL;"); 
      maybe_add_column($table_name, 'event_respondent_email_body', "alter table $table_name add event_respondent_email_body text NULL;"); 
      maybe_add_column($table_name, 'registration_requires_approval', "alter table $table_name add registration_requires_approval bool DEFAULT 0;"); 
      $registration_wp_users_only=get_option('eme_rsvp_registered_users_only');
      maybe_add_column($table_name, 'registration_wp_users_only', "alter table $table_name add registration_wp_users_only bool DEFAULT $registration_wp_users_only;"); 
      maybe_add_column($table_name, 'event_author', "alter table $table_name add event_author mediumint(9) DEFAULT 0;"); 
      maybe_add_column($table_name, 'creation_date', "alter table $table_name add creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'creation_date_gmt', "alter table $table_name add creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date', "alter table $table_name add modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date_gmt', "alter table $table_name add modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_name text;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_notes longtext;");
      }
      if ($db_version<4) {
         $wpdb->query("ALTER TABLE $table_name CHANGE event_category_id event_category_ids text default NULL;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_author mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_contactperson_id mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_seats mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY location_id mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY recurrence_id mediumint(9) DEFAULT 0;");
         $wpdb->query("ALTER TABLE $table_name MODIFY event_rsvp bool DEFAULT 0;");
      }
      if ($db_version<5) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_rsvp bool DEFAULT 0;");
      }
      if ($db_version<11) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN event_author;");
         $wpdb->query("ALTER TABLE $table_name CHANGE event_creator_id event_author mediumint(9) DEFAULT 0;");
      }
   }
}

function eme_create_recurrence_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.RECURRENCE_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE ".$table_name." (
         recurrence_id mediumint(9) NOT NULL AUTO_INCREMENT,
         recurrence_start_date date NOT NULL,
         recurrence_end_date date NOT NULL,
         creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
         recurrence_interval tinyint NOT NULL, 
         recurrence_freq tinytext NOT NULL,
         recurrence_byday tinytext NOT NULL,
         recurrence_byweekno tinyint NOT NULL,
         UNIQUE KEY (recurrence_id)
         ) $charset $collate;";
      dbDelta($sql);
   } else {
      maybe_add_column($table_name, 'creation_date', "alter table $table_name add creation_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'creation_date_gmt', "alter table $table_name add creation_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date', "alter table $table_name add modif_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      maybe_add_column($table_name, 'modif_date_gmt', "alter table $table_name add modif_date_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00';"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY recurrence_byday tinytext NOT NULL ;");
      }
      if ($db_version<4) {
         $wpdb->query("ALTER TABLE $table_name DROP COLUMN recurrence_name, DROP COLUMN recurrence_start_time, DROP COLUMN recurrence_end_time, DROP COLUMN recurrence_notes, DROP COLUMN location_id, DROP COLUMN event_contactperson_id, DROP COLUMN event_category_id, DROP COLUMN event_page_title_format, DROP COLUMN event_single_event_format, DROP COLUMN event_contactperson_email_body, DROP COLUMN event_respondent_email_body, DROP COLUMN registration_requires_approval ");
      }
      if ($db_version<13) {
         $wpdb->query("UPDATE $table_name set creation_date=NOW() where creation_date='0000-00-00 00:00:00'");
         $wpdb->query("UPDATE $table_name set modif_date=NOW() where modif_date='0000-00-00 00:00:00'");
      }
   }
}

function eme_create_locations_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.LOCATIONS_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         location_id mediumint(9) NOT NULL AUTO_INCREMENT,
         location_name text NOT NULL,
         location_address tinytext NOT NULL,
         location_town tinytext NOT NULL,
         location_latitude float DEFAULT NULL,
         location_longitude float DEFAULT NULL,
         location_description text DEFAULT NULL,
         location_author mediumint(9) DEFAULT 0,
         UNIQUE KEY (location_id)
         ) $charset $collate;";
      dbDelta($sql);
      
      $wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude)
               VALUES ('Arts Millenium Building', 'Newcastle Road','Galway', 53.275, -9.06532)");
      $wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude)
               VALUES ('The Crane Bar', '2, Sea Road','Galway', 53.2692, -9.06151)");
      $wpdb->query("INSERT INTO ".$table_name." (location_name, location_address, location_town, location_latitude, location_longitude)
               VALUES ('Taaffes Bar', '19 Shop Street','Galway', 53.2725, -9.05321)");
   } else {
      maybe_add_column($table_name, 'location_author', "alter table $table_name add location_author mediumint(9) DEFAULT 0;"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY location_name text NOT NULL ;");
      }
   }
}

function eme_create_bookings_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
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
         ) $charset $collate;";
      dbDelta($sql);
   } else {
      maybe_add_column($table_name, 'booking_comment', "ALTER TABLE $table_name add booking_comment text DEFAULT NULL;"); 
      maybe_add_column($table_name, 'booking_approved', "ALTER TABLE $table_name add booking_approved bool DEFAULT 0;"); 
      if ($db_version<3) {
         $wpdb->query("ALTER TABLE $table_name MODIFY event_id mediumint(9) NOT NULL;");
         $wpdb->query("ALTER TABLE $table_name MODIFY person_id mediumint(9) NOT NULL;");
         $wpdb->query("ALTER TABLE $table_name MODIFY booking_seats mediumint(9) NOT NULL;");
      }
   }
}

function eme_create_people_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.PEOPLE_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         person_id mediumint(9) NOT NULL AUTO_INCREMENT,
         person_name tinytext NOT NULL, 
         person_email tinytext NOT NULL,
         person_phone tinytext DEFAULT NULL,
         wp_id bigint(20) unsigned DEFAULT NULL,
         UNIQUE KEY (person_id)
         ) $charset $collate;";
      dbDelta($sql);
   } else {
      maybe_add_column($table_name, 'wp_id', "ALTER TABLE $table_name add wp_id bigint(20) unsigned DEFAULT NULL;"); 
      if ($db_version<10) {
         $wpdb->query("ALTER TABLE $table_name MODIFY person_phone tinytext DEFAULT 0;");
      }
   }
} 

function eme_create_categories_table($charset,$collate) {
   global $wpdb;
   $db_version = get_option('eme_version');
   $table_name = $wpdb->prefix.CATEGORIES_TBNAME;

   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $sql = "CREATE TABLE ".$table_name." (
         category_id int(11) NOT NULL auto_increment,
         category_name tinytext NOT NULL,
         PRIMARY KEY  (category_id)
         ) $charset $collate;";
      dbDelta($sql);
   }
}

function eme_add_options($reset=0) {
   $contact_person_email_body_localizable = __("#_RESPNAME (#_RESPEMAIL) will attend #_NAME on #m #d, #Y. He wants to reserve #_SPACES space(s).<br/>Now there are #_RESERVEDSPACES space(s) reserved, #_AVAILABLESPACES are still available.<br/><br/>Yours faithfully,<br/>Events Manager",'eme') ;
   $respondent_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>you have successfully reserved #_SPACES space(s) for #_NAME.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'eme');
   $registration_pending_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>your request to reserve #_SPACES space(s) for #_NAME is pending.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'eme');
   $registration_cancelled_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>your request to reserve #_SPACES space(s) for #_NAME has been cancelled.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'eme');
   $registration_denied_email_body_localizable = __("Dear #_RESPNAME,<br/><br/>your request to reserve #_SPACES space(s) for #_NAME has been denied.<br/><br/>Yours faithfully,<br/>#_CONTACTPERSON",'eme');
   
   $eme_options = array('eme_event_list_item_format' => DEFAULT_EVENT_LIST_ITEM_FORMAT,
   'eme_display_calendar_in_events_page' => 0,
   'eme_single_event_format' => DEFAULT_SINGLE_EVENT_FORMAT,
   'eme_event_page_title_format' => DEFAULT_EVENT_PAGE_TITLE_FORMAT,
   'eme_show_period_monthly_dateformat' => DEFAULT_SHOW_PERIOD_MONTHLY_DATEFORMAT,
   'eme_show_period_yearly_dateformat' => DEFAULT_SHOW_PERIOD_YEARLY_DATEFORMAT,
   'eme_filter_form_format' => DEFAULT_FILTER_FORM_FORMAT,
   'eme_list_events_page' => 0,
   'eme_events_page_title' => DEFAULT_EVENTS_PAGE_TITLE,
   'eme_no_events_message' => __('No events','eme'),
   'eme_location_page_title_format' => DEFAULT_LOCATION_PAGE_TITLE_FORMAT,
   'eme_location_baloon_format' => DEFAULT_LOCATION_BALLOON_FORMAT,
   'eme_location_event_list_item_format' => DEFAULT_LOCATION_EVENT_LIST_ITEM_FORMAT,
   'eme_location_no_events_message' => DEFAULT_LOCATION_NO_EVENTS_MESSAGE,
   'eme_single_location_format' => DEFAULT_SINGLE_LOCATION_FORMAT,
   'eme_rss_main_title' => get_bloginfo('title')." - ".__('Events'),
   'eme_rss_main_description' => get_bloginfo('description')." - ".__('Events'),
   'eme_rss_description_format' => DEFAULT_RSS_DESCRIPTION_FORMAT,
   'eme_rss_title_format' => DEFAULT_RSS_TITLE_FORMAT,
   'eme_gmap_is_active'=> DEFAULT_GMAP_ENABLED,
   'eme_gmap_zooming'=> DEFAULT_GMAP_ZOOMING,
   'eme_default_contact_person' => -1,
   'eme_captcha_for_booking' => 0 ,
   'eme_rsvp_mail_notify_is_active' => 0 ,
   'eme_contactperson_email_body' => preg_replace("/<br ?\/?>/", "\n", $contact_person_email_body_localizable),
   'eme_respondent_email_body' => preg_replace("/<br ?\/?>/", "\n", $respondent_email_body_localizable),
   'eme_registration_pending_email_body' => preg_replace("/<br ?\/?>/", "\n", $registration_pending_email_body_localizable),
   'eme_registration_cancelled_email_body' => preg_replace("/<br ?\/?>/", "\n", $registration_cancelled_email_body_localizable),
   'eme_registration_denied_email_body' => preg_replace("/<br ?\/?>/", "\n", $registration_denied_email_body_localizable),
   'eme_rsvp_mail_port' => 25,
   'eme_smtp_host' => 'localhost',
   'eme_mail_sender_name' => '',
   'eme_rsvp_mail_send_method' => 'smtp',
   'eme_rsvp_mail_SMTPAuth' => 0,
   'eme_attendees_list_format' => DEFAULT_ATTENDEES_LIST_FORMAT,
   'eme_image_max_width' => DEFAULT_IMAGE_MAX_WIDTH,
   'eme_image_max_height' => DEFAULT_IMAGE_MAX_HEIGHT,
   'eme_image_max_size' => DEFAULT_IMAGE_MAX_SIZE,
   'eme_full_calendar_event_format' => DEFAULT_FULL_CALENDAR_EVENT_FORMAT,
   'eme_small_calendar_event_title_format' => DEFAULT_SMALL_CALENDAR_EVENT_TITLE_FORMAT,
   'eme_small_calendar_event_title_separator' => DEFAULT_SMALL_CALENDAR_EVENT_TITLE_SEPARATOR, 
   'eme_hello_to_user' => 1,
   'eme_shortcodes_in_widgets' => 0,
   'eme_load_js_in_header' => 0,
   'eme_donation_done' => 0,
   'eme_conversion_needed' => 0,
   'eme_events_admin_limit' => 20,
   'eme_use_select_for_locations' => DEFAULT_USE_SELECT_FOR_LOCATIONS,
   'eme_attributes_enabled' => DEFAULT_ATTRIBUTES_ENABLED,
   'eme_recurrence_enabled' => DEFAULT_RECURRENCE_ENABLED,
   'eme_rsvp_enabled' => DEFAULT_RSVP_ENABLED,
   'eme_rsvp_addbooking_submit_string' => DEFAULT_RSVP_ADDBOOKINGFORM_SUBMIT_STRING,
   'eme_rsvp_addbooking_min_spaces' => 1,
   'eme_rsvp_addbooking_max_spaces' => 10,
   'eme_rsvp_delbooking_submit_string' => DEFAULT_RSVP_DELBOOKINGFORM_SUBMIT_STRING,
   'eme_categories_enabled' => DEFAULT_CATEGORIES_ENABLED);
   
   foreach($eme_options as $key => $value){
      eme_add_option($key, $value, $reset);
   }
      
}
function eme_add_option($key, $value, $reset) {
   $option_val = get_option($key,"non_existing");
   if ($option_val=="non_existing" || $reset) {
      $old_key=preg_replace("/^eme_/","dbem_",$key);
      $old_value = get_option($old_key,"non_existing");
      if ($old_value=="non_existing" || $reset) {
         update_option($key, $value);
      } else {
         update_option($key, $old_value);
      }
   }
}

////////////////////////////////////
// WP options registration/deletion
////////////////////////////////////
function eme_options_delete() {
   $options = array ('eme_events_page', 'eme_display_calendar_in_events_page', 'eme_event_list_item_format_header', 'eme_event_list_item_format', 'eme_event_list_item_format_footer', 'eme_event_page_title_format', 'eme_single_event_format', 'eme_list_events_page', 'eme_events_page_title', 'eme_no_events_message', 'eme_location_page_title_format', 'eme_location_baloon_format', 'eme_single_location_format', 'eme_location_event_list_item_format', 'eme_show_period_monthly_dateformat','eme_show_period_yearly_dateformat', 'eme_location_no_events_message', 'eme_gmap_is_active', 'eme_gmap_zooming', 'eme_rss_main_title', 'eme_rss_main_description', 'eme_rss_title_format', 'eme_rss_description_format', 'eme_rsvp_mail_notify_is_active', 'eme_contactperson_email_body', 'eme_respondent_email_body', 'eme_mail_sender_name', 'eme_smtp_username', 'eme_smtp_password', 'eme_default_contact_person','eme_captcha_for_booking', 'eme_mail_sender_address', 'eme_mail_receiver_address', 'eme_smtp_host', 'eme_rsvp_mail_send_method', 'eme_rsvp_mail_port', 'eme_rsvp_mail_SMTPAuth', 'eme_rsvp_registered_users_only', 'eme_rsvp_reg_for_new_events', 'eme_rsvp_default_number_spaces', 'eme_rsvp_addbooking_submit_string', 'eme_rsvp_delbooking_submit_string', 'eme_image_max_width', 'eme_image_max_height', 'eme_image_max_size', 'eme_full_calendar_event_format', 'eme_use_select_for_locations', 'eme_attributes_enabled', 'eme_recurrence_enabled','eme_rsvp_enabled','eme_categories_enabled','eme_small_calendar_event_title_format','eme_small_calendar_event_title_seperator','eme_registration_pending_email_body','eme_registration_denied_email_body','eme_registration_cancelled_email_body','eme_attendees_list_format','eme_uninstall_drop_tables','eme_uninstall_drop_data','eme_time_remove_leading_zeros','eme_rsvp_hide_full_events','eme_events_admin_limit','eme_conversion_needed','eme_donation_done','eme_hello_to_user','eme_filter_form_format','eme_rsvp_addbooking_min_seats','eme_rsvp_addbooking_max_seats','eme_shortcodes_in_widgets','eme_load_js_in_header');
   foreach ( $options as $opt ) {
      delete_option ( $opt );
      $old_opt=preg_replace("/eme_/","dbem_",$opt);
      delete_option ( $old_opt );
   }
}

function eme_options_register() {

   // only the options you want changed in the Settings page, not eg. eme_hello_to_user, eme_donation_done,eme_conversion_needed

   $options = array ('eme_events_page', 'eme_display_calendar_in_events_page', 'eme_event_list_item_format_header', 'eme_event_list_item_format', 'eme_event_list_item_format_footer', 'eme_event_page_title_format', 'eme_single_event_format', 'eme_list_events_page', 'eme_events_page_title', 'eme_no_events_message', 'eme_location_page_title_format', 'eme_location_baloon_format', 'eme_single_location_format', 'eme_location_event_list_item_format', 'eme_show_period_monthly_dateformat','eme_show_period_yearly_dateformat', 'eme_location_no_events_message', 'eme_gmap_is_active', 'eme_gmap_zooming', 'eme_rss_main_title', 'eme_rss_main_description', 'eme_rss_title_format', 'eme_rss_description_format', 'eme_rsvp_mail_notify_is_active', 'eme_contactperson_email_body', 'eme_respondent_email_body', 'eme_mail_sender_name', 'eme_smtp_username', 'eme_smtp_password', 'eme_default_contact_person','eme_captcha_for_booking', 'eme_mail_sender_address', 'eme_smtp_host', 'eme_rsvp_mail_send_method', 'eme_rsvp_mail_port', 'eme_rsvp_mail_SMTPAuth', 'eme_rsvp_registered_users_only', 'eme_rsvp_reg_for_new_events', 'eme_rsvp_default_number_spaces', 'eme_rsvp_addbooking_submit_string', 'eme_rsvp_delbooking_submit_string', 'eme_image_max_width', 'eme_image_max_height', 'eme_image_max_size', 'eme_full_calendar_event_format', 'eme_use_select_for_locations', 'eme_attributes_enabled', 'eme_recurrence_enabled','eme_rsvp_enabled','eme_categories_enabled','eme_small_calendar_event_title_format','eme_small_calendar_event_title_seperator','eme_registration_pending_email_body','eme_registration_denied_email_body','eme_registration_cancelled_email_body','eme_attendees_list_format','eme_uninstall_drop_data','eme_time_remove_leading_zeros','eme_rsvp_hide_full_events','eme_events_admin_limit','eme_filter_form_format','eme_rsvp_addbooking_min_spaces','eme_rsvp_addbooking_max_spaces','eme_shortcodes_in_widgets','eme_load_js_in_header');
   foreach ( $options as $opt ) {
      register_setting ( 'eme-options', $opt, '' );
   }
}
add_action ( 'admin_init', 'eme_options_register' );

function eme_create_events_page() {
   global $wpdb;
   $postarr = array(
      'post_status'=> 'publish',
      'post_title' => DEFAULT_EVENT_PAGE_NAME,
      'post_name'  => $wpdb->escape(__('events','eme')),
      'post_type'  => 'page',
   );
   if ($int_post_id = wp_insert_post($postarr)) {
      update_option('eme_events_page', $int_post_id);
   }
}

function eme_delete_events_page() {
   $events_page_id = get_option('eme_events_page' );
   if ($events_page_id)
      wp_delete_post($events_page_id);
}

// Create the Manage Events and the Options submenus 
add_action('admin_menu','eme_create_events_submenu');
function eme_create_events_submenu () {
   // let's check if deactivation is needed
   $db_version = get_option('eme_version');
   if ($db_version && $db_version < EME_DB_VERSION)
      add_action('admin_notices', "eme_explain_deactivation_needed");

   if(function_exists('add_submenu_page')) {
      add_object_page(__('Events', 'eme'),__('Events', 'eme'),MIN_CAPABILITY,'events-manager','eme_events_subpanel', EME_PLUGIN_URL.'images/calendar-16.png');
      // Add a submenu to the custom top-level menu: 
      $plugin_page = add_submenu_page('events-manager', __('Edit'),__('Edit'),MIN_CAPABILITY,'events-manager','eme_events_subpanel');
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      $plugin_page = add_submenu_page('events-manager', __('Add new', 'eme'), __('Add new','eme'), MIN_CAPABILITY, 'events-manager-new_event', "eme_new_event_page");
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' ); 
      $plugin_page = add_submenu_page('events-manager', __('Locations', 'eme'), __('Locations', 'eme'), EDIT_CAPABILITY, 'events-manager-locations', "eme_locations_page");
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      if (get_option('eme_categories_enabled')) {
         $plugin_page = add_submenu_page('events-manager', __('Event Categories','eme'),__('Categories','eme'), SETTING_CAPABILITY, "events-manager-categories", 'eme_categories_subpanel');
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      }
      if (get_option('eme_rsvp_enabled')) {
         $plugin_page = add_submenu_page('events-manager', __('People', 'eme'), __('People', 'eme'), MIN_CAPABILITY, 'events-manager-people', "eme_people_page");
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' ); 
         $plugin_page = add_submenu_page('events-manager', __('Pending Approvals', 'eme'), __('Pending Approvals', 'eme'), EDIT_CAPABILITY, 'events-manager-registration-approval', "eme_registration_approval_page");
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' ); 
         $plugin_page = add_submenu_page('events-manager', __('Change Registration', 'eme'), __('Change Registration', 'eme'), EDIT_CAPABILITY, 'events-manager-registration-seats', "eme_registration_seats_page");
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' ); 
         $plugin_page = add_submenu_page('events-manager', __('Cleanup', 'eme'), __('Cleanup', 'eme'), SETTING_CAPABILITY, 'events-manager-cleanup', "eme_cleanup_page");
         add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
      }
      $plugin_page = add_submenu_page('events-manager', __('Events Manager Settings','eme'),__('Settings','eme'), SETTING_CAPABILITY, "events-manager-options", 'eme_options_subpanel');
      add_action( 'admin_head-'. $plugin_page, 'eme_admin_general_script' );
   }
}

function eme_replace_placeholders($format, $event, $target="html") {
   global $eme_need_gmap_js;

   // some variables we'll use further down more than once
   $current_userid=get_current_user_id();
   $person_id=eme_get_person_id_by_wp_id($current_userid);
   $rsvp_is_active = get_option('eme_rsvp_enabled'); 

   // first we do the custom attributes, since these can contain other placeholders
   preg_match_all("/#(ESC|URL)?_ATT\{.+?\}(\{.+?\})?/", $format, $results);
   foreach($results[0] as $resultKey => $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";
      //Strip string of placeholder and just leave the reference
      $attRef = substr( substr($result, 0, strpos($result, '}')), 6 );
      if (isset($event['event_attributes'][$attRef])) {
         $replacement = $event['event_attributes'][$attRef];
      }
      if( trim($replacement) == ''
         && isset($results[2][$resultKey])
         && $results[2][$resultKey] != '' ) {
         //Check to see if we have a second set of braces;
         $replacement = substr( $results[2][$resultKey], 1, strlen(trim($results[2][$resultKey]))-2 );
      }

      if ($need_escape) {
         $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
      } elseif ($need_urlencode) {
         $replacement = rawurlencode($replacement);
      }
      $format = str_replace($orig_result, $replacement ,$format );
   }

   // and now all the other placeholders
   preg_match_all("/#(ESC|URL)?@?_?[A-Za-z0-9_\[\]]+/", $format, $placeholders);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($placeholders[0],'sort_stringlenth');

   foreach($placeholders[0] as $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      $found = 1;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = "";
      // matches all fields placeholder
      if (preg_match('/#_EDITEVENTLINK$/', $result)) { 
         if(is_user_logged_in())
            $replacement = "<a href=' ".admin_url("admin.php?page=events-manager&amp;action=edit_event&amp;event_id=".$event['event_id'])."'>".__('Edit')."</a>";

      } elseif (preg_match('/#_24HSTARTTIME$/', $result)) { 
         $replacement = substr($event['event_start_time'], 0,5);

      } elseif (preg_match('/#_24HENDTIME$/', $result)) { 
         $replacement = substr($event['event_end_time'], 0,5);

      } elseif (preg_match('/#_PAST_FUTURE_CLASS$/', $result)) { 
         if (strtotime($event['event_start_time']) > time()) {
            $replacement="eme-future-event";
         } else {
            $replacement="eme-past-event";
         }

      } elseif (preg_match('/#_12HSTARTTIME$/', $result)) {
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
         $replacement = "$hour:$minute $AMorPM";

      } elseif (preg_match('/#_12HENDTIME$/', $result)) {
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
         $replacement = "$hour:$minute $AMorPM";

      } elseif (preg_match('/#_MAP$/', $result)) {
         $location = eme_get_location($event['location_id']);
         $replacement = eme_single_location_map($location);

      } elseif (preg_match('/#_DIRECTIONS$/', $result)) {
         $location = eme_get_location($event['location_id']);
         $replacement = eme_add_directions_form($location);

      } elseif (preg_match('/#_EVENTS_FILTERFORM$/', $result)) {
         if ($target == "rss" || eme_is_single_event_page()) {
            $replacement = "";
         } else {
            $replacement = eme_filter_form();
         }

      } elseif (preg_match('/#_ADDBOOKINGFORM$/', $result)) {
         if ($target == "rss") {
            $replacement = "";
         } elseif ($rsvp_is_active && $event['event_rsvp']) {
            $replacement = eme_add_booking_form($event['event_id']);
         }

      } elseif (preg_match('/#_ADDBOOKINGFORM_IF_NOT_REGISTERED$/', $result)) {
         if ($target == "rss") {
            $replacement = "";
         } elseif ($rsvp_is_active && $event['event_rsvp']
                   && is_user_logged_in()
                   && $event['registration_wp_users_only']) {
            if (!eme_get_booking_by_person_event_id($person_id,$event['event_id']))
               $replacement = eme_add_booking_form($event['event_id']);
         } else {
            $replacement = "";
         }

      } elseif (preg_match('/#_REMOVEBOOKINGFORM$/', $result)) {
         if ($target == "rss") {
            $replacement = "";
         } elseif ($rsvp_is_active && $event['event_rsvp']) {
            $replacement = eme_delete_booking_form($event['event_id']);
         }

      } elseif (preg_match('/#_REMOVEBOOKINGFORM_IF_REGISTERED$/', $result)) {
         if ($target == "rss") {
            $replacement = "";
         } elseif ($rsvp_is_active && $event['event_rsvp']
                   && is_user_logged_in()
                   && $event['registration_wp_users_only']) {
            if (eme_get_booking_by_person_event_id($person_id,$event['event_id']))
               $replacement = eme_delete_booking_form($event['event_id']);
         }

      } elseif (preg_match('/#_(AVAILABLESPACES|AVAILABLESEATS)$/', $result)) {
         if ($rsvp_is_active && $event['event_rsvp']) {
            $replacement = eme_get_available_seats($event['event_id']);
         }

      } elseif (preg_match('/#_(RESERVEDSPACES|BOOKEDSEATS)$/', $result)) {
         if ($rsvp_is_active && $event['event_rsvp']) {
            $replacement = eme_get_booked_seats($event['event_id']);
         }

      } elseif (preg_match('/#_USER_(RESERVEDSPACES|BOOKEDSEATS)$/', $result)) {
         if ($rsvp_is_active && $event['event_rsvp']
             && is_user_logged_in()) {
            $replacement = eme_get_booked_seats_by_person_event_id($person_id,$event['event_id']);
         }

      } elseif (preg_match('/#_LINKEDNAME$/', $result)) {
         $event_link = eme_event_url($event);
         $replacement="<a href='$event_link' title='".eme_trans_sanitize_html($event['event_name'])."'>".eme_trans_sanitize_html($event['event_name'])."</a>";

      } elseif (preg_match('/#_ICALLINK$/', $result)) {
         $url = site_url ("/?eme_ical=public_single&amp;event_id=".$event['event_id']);
         $replacement = "<a href='$url'>ICAL</a>";

      } elseif (preg_match('/#_ICALURL$/', $result)) {
         $replacement = site_url ("/?eme_ical=public_single&amp;event_id=".$event['event_id']);

      } elseif (preg_match('/#_EVENTPAGEURL\[(.+)\]/', $result, $matches)) {
         $events_page_link = eme_get_events_page(true, false);
         if (stristr($events_page_link, "?"))
            $joiner = "&amp;";
         else
            $joiner = "?";
         $replacement = $events_page_link.$joiner."event_id=".intval($matches[1]);

      } elseif (preg_match('/#_EVENTPAGEURL/', $result)) {
         $replacement = eme_event_url($event);

      } elseif (preg_match('/#_(DETAILS|NOTES|EXCERPT)$/', $result)) {
         $field = "event_".ltrim(strtolower($result), "#_");
         // DETAILS is an alternative for NOTES
         if ($field == "event_details")
            $field = "event_notes";
         
         // when on the single event page, never show just the excerpt
         if ($field == "event_excerpt" && eme_is_single_event_page()) {
            $field = "event_notes";
         }

         $replacement = $event[$field];

         if ($target == "html") {
            //If excerpt, we use more link text
            if ($field == "event_excerpt") {
               $matches = explode('<!--more-->', $event['event_notes']);
               $replacement = $matches[0];
               $replacement = apply_filters('eme_notes', $replacement);
            } else {
               $replacement = apply_filters('eme_notes', $replacement);
            }
            //$field_value = apply_filters('the_content', $field_value); - chucks a wobbly if we do this.
            // we call the sanitize_html function so the qtranslate
            // does it's thing anyway
            $replacement = eme_trans_sanitize_html($replacement,0);
         } else {
            if ($target == "map") {
               $replacement = apply_filters('eme_notes_map', $replacement);
            } else {
               if ($field == "event_excerpt"){
                  $matches = explode('<!--more-->', $event['event_notes']);
                  $replacement = eme_trans_sanitize_html($matches[0]);
                  $replacement = apply_filters('eme_notes_rss', $replacement);
               } else {
                  $replacement = apply_filters('eme_notes_rss', $replacement);
               }
               $replacement = apply_filters('the_content_rss', $replacement);
            }
         }

      } elseif (preg_match('/#_NAME$/', $result)) {
         $field = "event_name";
         $replacement = $event[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } else {
            $replacement = apply_filters('eme_general_rss', $replacement);
         }

      } elseif (preg_match('/#_(ADDRESS|TOWN)$/', $result)) {
         $field = "location_".ltrim(strtolower($result), "#_");
         if (isset($event[$field]))  $replacement = $event[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } else { 
            $replacement = apply_filters('eme_general_rss', $replacement); 
         }

      } elseif (preg_match('/#_LOCATIONPAGEURL$/', $result)) { 
         $events_page_link = eme_get_events_page(true, false);
         if (stristr($events_page_link, "?"))
            $joiner = "&amp;";
         else
            $joiner = "?";
         $replacement = $events_page_link.$joiner."location_id=".$event['location_id'];

      } elseif (preg_match('/#_EVENTID$/', $result)) {
         $field = "event_id";
         $replacement = $event[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } else {
            $replacement = apply_filters('eme_general_rss', $replacement); 
         }

      } elseif (preg_match('/#_LOCATIONID$/', $result)) {
         $field = "location_id";
         $replacement = $event[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } else {
            $replacement = apply_filters('eme_general_rss', $replacement); 
         }

      } elseif (preg_match('/#_LOCATION$/', $result)) {
         $field = "location_name";
         $replacement = $event[$field];
         $replacement = eme_trans_sanitize_html($replacement);
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } else {
            $replacement = apply_filters('eme_general_rss', $replacement); 
         }

      } elseif (preg_match('/#_ATTENDEES$/', $result)) {
         if ($rsvp_is_active && $event['event_rsvp']) {
            $replacement=eme_get_bookings_list_for($event['event_id']);
            if ($target == "html") {
               $replacement = apply_filters('eme_general', $replacement); 
            } else {
               $replacement = apply_filters('eme_general_rss', $replacement); 
            }
         }

      } elseif (preg_match('/#_CONTACTNAME$/', $result)) {
         $contact = eme_get_contact($event);
         $replacement = $contact->display_name;

      } elseif (preg_match('/#_CONTACTEMAIL$/', $result)) {
         $contact = eme_get_contact($event);
         // ascii encode for primitive harvesting protection ...
         $replacement = eme_ascii_encode($contact->user_email);

      } elseif (preg_match('/#_CONTACTPHONE$/', $result)) {
         $contact = eme_get_contact($event);
         $phone = eme_get_user_phone($contact->ID);
         // ascii encode for primitive harvesting protection ...
         $replacement=eme_ascii_encode($phone);

      } elseif (preg_match('/#_IMAGE$/', $result)) {
         if ($event['location_image_url'] != '')
              $replacement = "<img src='".$event['location_image_url']."' alt='".$event['location_name']."'/>";

      } elseif (preg_match('/^#[A-Za-z]$/', $result)) {
         // matches all PHP date placeholders for startdate-time
         $replacement=date_i18n( ltrim($result,"#"), strtotime( $event['event_start_date']." ".$event['event_start_time']));
         if (get_option('eme_time_remove_leading_zeros') && $result=="#i") {
            $replacement=ltrim($replacement,"0");
         }

      } elseif (preg_match('/^#@[A-Za-z]$/', $result)) {
         // matches all PHP time placeholders for enddate-time
         $replacement=date_i18n( ltrim($result,"#@"), strtotime( $event['event_end_date']." ".$event['event_end_time']));
         if (get_option('eme_time_remove_leading_zeros') && $result=="#@i") {
            $replacement=ltrim($replacement,"0");
         }

      } elseif (preg_match('/^#_CATEGORIES$/', $result) && get_option('eme_categories_enabled')) {
         $categories = eme_get_event_categories($event['event_id']);
         $replacement = eme_trans_sanitize_html(join(", ",$categories));
         if ($target == "html") {
            $replacement = apply_filters('eme_general', $replacement); 
         } else {
            $replacement = apply_filters('eme_general_rss', $replacement);
         }

      } elseif (preg_match('/#_IS_SINGLE_EVENT/', $result)) {
         if (eme_is_single_event_page())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_LOGGED_IN/', $result)) {
         if (is_user_logged_in())
            $replacement = 1;
         else
            $replacement = 0;

      } elseif (preg_match('/#_IS_ADMIN_PAGE/', $result)) {
         if (is_admin())
            $replacement = 1;
         else
            $replacement = 0;

      } else {
         $found = 0;
      }

      if ($need_escape) {
         $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
      } elseif ($need_urlencode) {
         $replacement = rawurlencode($replacement);
      }
      if ($found)
         $format = str_replace($orig_result, $replacement ,$format );
   }

   // for extra date formatting, eg. #_{d/m/Y}
   preg_match_all("/#(ESC|URL)?@?_\{[A-Za-z0-9 -\/,\.\\\]+\}/", $format, $results);
   // make sure we set the largest matched placeholders first, otherwise if you found e.g.
   // #_LOCATION, part of #_LOCATIONPAGEURL would get replaced as well ...
   usort($results[0],'sort_stringlenth');
   foreach($results[0] as $result) {
      $need_escape = 0;
      $need_urlencode = 0;
      $orig_result = $result;
      if (strstr($result,'#ESC')) {
         $result = str_replace("#ESC","#",$result);
         $need_escape=1;
      } elseif (strstr($result,'#URL')) {
         $result = str_replace("#URL","#",$result);
         $need_urlencode=1;
      }
      $replacement = '';
      if(substr($result, 0, 3 ) == "#@_") {
         $my_date = "event_end_date";
         $my_time = "event_end_time";
         $offset = 4;
      } else {
         $my_date = "event_start_date";
         $my_time = "event_start_time";
         $offset = 3;
      }

      $replacement = date_i18n(substr($result, $offset, (strlen($result)-($offset+1)) ), strtotime($event[$my_date]." ".$event[$my_time]));

      if ($need_escape) {
         $replacement = eme_sanitize_request(preg_replace('/\n|\r/','',$replacement));
      } elseif ($need_urlencode) {
         $replacement = rawurlencode($replacement);
      }
      $format = str_replace($orig_result, $replacement ,$format );
   }
   return do_shortcode($format);   
}

function eme_sanitize_request( $value ) {
#  if( get_magic_quotes_gpc() ) 
#     $value = stripslashes( $value );

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

function sort_stringlenth($a,$b){
   return strlen($b)-strlen($a);
}


function eme_trans_sanitize_html( $value, $do_convert=1 ) {
   if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) $value = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($value);
   if ($do_convert) {
      return eme_sanitize_html($value);
   } else {
      return $value;
   }
}

function eme_sanitize_html( $value ) {
   //return htmlentities($value,ENT_QUOTES,get_option('blog_charset'));
   return htmlspecialchars($value,ENT_QUOTES);
}

function eme_strip_tags ( $value ) {
   return preg_replace("/^\s*$/","",strip_tags(stripslashes($value)));
}

function admin_show_warnings() {
   $db_version = get_option('eme_version');
   $old_db_version = get_option('dbem_version');
   if ($db_version && $db_version < EME_DB_VERSION) {
      // the warning is already given via admin_notice, we just want
      // to prevent people to do anything in EME without deactivation/activation first
      // But we allow access to the settings page ...
      if ((isset($_GET['page']) && $_GET['page'] != 'events-manager-options'))
         exit(1);
   } elseif (!$db_version && $old_db_version) {
      // transfer from dbem to eme warning
      $advice = __("You have installed the new version of Events Manager Extended. This version has among other things switched from 'dbem' to 'eme' for API calls (used in templates) and for CSS. So if you use these, please replace the string 'dbem' by 'eme' in your custom templates and/or CSS. After that, please deacticate/reactivate the plugin to adjust for the new version.",'eme');
      ?>
      <div id="message" class="updated"><p> <?php echo $advice; ?> </p></div>
      <?php
   } else {
      if ($old_db_version && $db_version==8) {
         // transfer from dbem to eme warning
         $advice = __("You have installed the new version of Events Manager Extended. This version has among other things switched from 'dbem' to 'eme' for API calls (used in templates) and for CSS. So if you use these, please replace the string 'dbem' by 'eme' in your custom templates and/or CSS.",'eme');
         ?>
         <div id="message" class="updated"><p> <?php echo $advice; ?> </p></div>
         <?php
      }

      // now the normal warnings
      $donation_done = get_option('eme_donation_done' );
      if ($donation_done == 0)
         eme_explain_donation ();

      // now the normal warnings
      $say_hello = get_option('eme_hello_to_user' );
      if ($say_hello == 1)
         eme_hello_to_new_user ();

      $conversion_needed = get_option('eme_conversion_needed' );
      if ($conversion_needed == 1)
         eme_explain_conversion_needed ();
   }
}

function eme_explain_deactivation_needed() {
   $advice = __("It seems you upgraded Events Manager Extended but your events database hasn't been updated accordingly yet. Please deactivate/activate the plugin for this to happen.",'eme')."<br />".__("<strong>Warning:</strong> make sure the option 'Delete all EME data when upgrading or deactivating' is not activated if you don't want to lose all existing event data!",'eme');
   ?>
<div id="message" class="error"><p> <?php echo $advice; ?> </p></div>
<?php
}

function eme_explain_conversion_needed() {
   $advice = sprintf(__("It seems your Events Database is not yet converted to the correct characterset used by Wordpress, if you want this done: <strong>TAKE A BACKUP OF YOUR DB</strong> and then click <a href=\"%s\" title=\"Conversion link\">here</a>.",'eme'),admin_url("admin.php?page=events-manager&amp;do_character_conversion=true"));
   ?>
<div id="message" class="updated"><p> <?php echo $advice; ?> </p></div>
<?php
}

function eme_explain_donation() {
   ?>
<div style="padding: 10px 10px 10px 10px; border: 1px solid #ddd; background-color:#FFFFE0;">
    <div>
        <h3><?php echo __('Donate', 'eme'); ?></h3>
<?php
_e('If you find this plugin useful to you, please consider making a small donation to help contribute to my time invested and to further development. Thanks for your kind support!', 'eme');
?>
  <br /><br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHPwYJKoZIhvcNAQcEoIIHMDCCBywCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYCMdFm7KQ32WfqTnPlBvAYkyldCfENPogludyK+VXxu1KC6+sS4Rgy4FbimhwWBUoyF4GKgI8rzr4vDP30yAhK63B7wV/RVN+4TqPI66RIMkbVjA0Q3WahkgST77COLlAlhuSFgp2PdXzE3mDjj/FjaFHiZEnkQq5dPl+9E4bQ/nTELMAkGBSsOAwIaBQAwgbwGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIy2T+AYRc6zyAgZg6z1W2OuKxaEuCxOvo0SXEr5dBCsbRl0hmgKbX61UW4kXnGPzZalfE9N+Rv7hriPUoOppL8Q6w5CGjmBitc5GM5Aa2owrL0MJZUoK3ETbmJEOvr9u0Az2HkqumYi6NpMq+Zy1+pcb1JRLrm2Gdep4UVw7jVgqbh4FptDGJJ8p2mWiIKNMRQzk3B1IztehAtgsAxdC5wnqIVqCCA4cwggODMIIC7KADAgECAgEAMA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTAeFw0wNDAyMTMxMDEzMTVaFw0zNTAyMTMxMDEzMTVaMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAwUdO3fxEzEtcnI7ZKZL412XvZPugoni7i7D7prCe0AtaHTc97CYgm7NsAtJyxNLixmhLV8pyIEaiHXWAh8fPKW+R017+EmXrr9EaquPmsVvTywAAE1PMNOKqo2kl4Gxiz9zZqIajOm1fZGWcGS0f5JQ2kBqNbvbg2/Za+GJ/qwUCAwEAAaOB7jCB6zAdBgNVHQ4EFgQUlp98u8ZvF71ZP1LXChvsENZklGswgbsGA1UdIwSBszCBsIAUlp98u8ZvF71ZP1LXChvsENZklGuhgZSkgZEwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAgV86VpqAWuXvX6Oro4qJ1tYVIT5DgWpE692Ag422H7yRIr/9j/iKG4Thia/Oflx4TdL+IFJBAyPK9v6zZNZtBgPBynXb048hsP16l2vi0k5Q2JKiPDsEfBhGI+HnxLXEaUWAcVfCsQFvd2A1sxRr67ip5y2wwBelUecP3AjJ+YcxggGaMIIBlgIBATCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwCQYFKw4DAhoFAKBdMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTExMDExOTE0MzU0NFowIwYJKoZIhvcNAQkEMRYEFKi6BynDfzarMWLtPReeeGpOfxi2MA0GCSqGSIb3DQEBAQUABIGAifGWMzPLVJ3Q+EcZ1lsnAZi+ATnUrz2mDCNi2Endh7oJEgZOa7iP08MgAJJHvRi8GIkt9aVquYa7KzEYr7JwLhJnhEoZ6YdG/EQC8xBlR6pe41aneNeR8GPBY8WC8S11OpsuQ4K3RdD5wvZFmTAuAjdSGIExS8Zyzj1tqk8/yas=-----END PKCS7-----
">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<?php
echo sprintf ( __ ( "<a href=\"%s\" title=\"I already donated\">I already donated.</a>", 'eme' ), admin_url("admin.php?page=events-manager&amp;disable_donate_message=true") );
?>
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

   </div>
</div>

<?php
}

function eme_hello_to_new_user() {
   global $current_user;
   get_currentuserinfo();
   $advice = sprintf ( __ ( "<p>Hey, <strong>%s</strong>, welcome to <strong>Events Manager Extended</strong>! We hope you like it around here.</p> 
   <p>Now it's time to insert events lists through  <a href=\"%s\" title=\"Widgets page\">widgets</a>, <a href=\"%s\" title=\"Template tags documentation\">template tags</a> or <a href=\"%s\" title=\"Shortcodes documentation\">shortcodes</a>.</p>
   <p>By the way, have you taken a look at the <a href=\"%s\" title=\"Change settings\">Settings page</a>? That's where you customize the way events and locations are displayed.</p>
   <p>What? Tired of seeing this advice? I hear you, <a href=\"%s\" title=\"Don't show this advice again\">click here</a> and you won't see this again!</p>", 'eme' ), $current_user->display_name, admin_url("widgets.php"), 'http://www.e-dynamics.be/wordpress/#template-tags', 'http://www.e-dynamics.be/wordpress/#shortcodes', admin_url("admin.php?page=events-manager-options"), admin_url("admin.php?page=events-manager&amp;disable_hello_to_user=true") );
   ?>
<div id="message" class="updated">
      <?php
   echo $advice;
   ?>
   </div>
<?php
}

?>
