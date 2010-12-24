<?php
function eme_people_page() {
   // Managing AJAX booking removal
   if (!current_user_can( SETTING_CAPABILITY) && isset($_REQUEST['action'])) {
      $message = __('You have no right to update people!','eme');
   } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'remove_booking') {
      if(isset($_REQUEST['booking_id']))
         eme_delete_booking(intval($_REQUEST['booking_id']));
   } elseif (isset ($_REQUEST['persons']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_people') {
         $persons = $_REQUEST['persons'];
         if(is_array($persons)){
            //Make sure the array is only numbers
            foreach ($persons as $person_id) {
               if (is_numeric($person_id)) {
                  $person=eme_get_person($person_id);
                  if (isset($_REQUEST['delete_assoc_bookings'])) {
                     $res=eme_delete_all_bookings_for_person_id($person_id);
                     if ($res) {
                        $message.=__("Deleted all bookings made by '".$person['person_name']."'", 'eme');
                        $message.="<br>";
                     }
                  }
                  $res.=eme_delete_person($person_id);
                  if ($res) {
                     $message.=__("Deleted '".$person['person_name']."'", 'eme');
                     $message.="<br>";
                  }
               }
            }
         } else {
               $validation_result = false;
               $message = __("Couldn't delete the people. Please try again.","eme");
         }
   }
   ?>
   
   <div class='wrap'> 
   <div id="icon-users" class="icon32"><br/></div>
   <h2>People</h2>
   <?php admin_show_warnings(); eme_people_table($message); ?>
   </div> 

   <?php
}

add_action('init','eme_ajax_actions'); 
function eme_ajax_actions() {
   if (isset($_GET['eme_ajax_action']) && $_GET['eme_ajax_action'] == 'booking_data') {
      if (isset($_GET['id']))
         echo "[ {bookedSeats:".eme_get_booked_seats(intval($_GET['id'])).", availableSeats:".eme_get_available_seats(intval($_GET['id']))."}]"; 
      die();
   }
   if (isset($_GET['action']) && $_GET['action'] == 'printable'){
      if (isset($_GET['event_id']))
         eme_printable_booking_report(intval($_GET['event_id']));
   }
   
   if (isset($_GET['query']) && $_GET['query'] == 'GlobalMapData') { 
      eme_global_map_json((bool) $_GET['eventful'],$_GET['scope']);
      die();
   }
}

function eme_global_map_json($eventful = false, $scope = "all") {
   $json = '{"locations":[';
   $locations = eme_get_locations((bool) $eventful,$scope);
   $json_locations = array();
   foreach($locations as $location) {
      $json_location = array();
      foreach($location as $key => $value) {
         # no newlines allowed, otherwise no map is shown
         $value=preg_replace("/\r\n|\n\r|\n/","<br />",eme_trans_sanitize_html($value));
         $json_location[] = '"'.$key.'":"'.$value.'"';
      }
      $tmp_loc=eme_replace_locations_placeholders(get_option('eme_location_baloon_format'), $location);
      # no newlines allowed, otherwise no map is shown
      $tmp_loc=preg_replace("/\r\n|\n\r|\n/","<br />",$tmp_loc);
      $json_location[] = '"location_balloon":"'.eme_trans_sanitize_html($tmp_loc).'"';
      $json_locations[] = "{".implode(",",$json_location)."}";
   }
   $json .= implode(",", $json_locations); 
   $json .= "]}" ;
   echo $json;
}

function eme_printable_booking_report($event_id) {
   $event = eme_get_event($event_id);
   $current_userid=get_current_user_id();
   if (!(current_user_can( EDIT_CAPABILITY) ||
        (current_user_can( MIN_CAPABILITY) && ($event['event_author']==$current_userid || $event['event_contactperson_id']==$current_userid)))) {
        echo "No access";
        die;
   }

   $bookings =  eme_get_bookings_for($event_id);
   $available_seats = eme_get_available_seats($event_id);
   $booked_seats = eme_get_booked_seats($event_id);
   $stylesheet = EME_PLUGIN_URL."events_manager.css";
   ?>
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html>
      <head>
         <meta http-equiv="Content-type" content="text/html; charset=utf-8">
         <title>Bookings for <?php echo $event['event_name'];?></title>
          <link rel="stylesheet" href="<?php echo $stylesheet; ?>" type="text/css" media="screen" />
         
      </head>
      <body id="printable">
         <div id="container">
         <h1>Bookings for <?php echo $event['event_name'];?></h1> 
         <p><?php echo eme_replace_placeholders("#d #M #Y", $event)?></p>
         <p><?php echo eme_replace_placeholders("#_LOCATION, #_ADDRESS, #_TOWN", $event)?></p>
         <h2><?php _e('Bookings data', 'eme');?></h2>
         <table id="bookings-table">
            <tr>
               <th scope='col'><?php _e('Name', 'eme')?></th>
               <th scope='col'><?php _e('E-mail', 'eme')?></th>
               <th scope='col'><?php _e('Phone number', 'eme')?></th> 
               <th scope='col'><?php _e('Seats', 'eme')?></th>
               <th scope='col'><?php _e('Comment', 'eme')?></th> 
            <?php
            foreach($bookings as $booking) {
               $pending_string="";
               if (eme_event_needs_approval($event_id) && !$booking['booking_approved']) {
                  $pending_string=__('(pending)','eme');
               }
                ?>
            <tr>
               <td><?php echo $booking['person_name']?></td> 
               <td><?php echo $booking['person_email']?></td>
               <td><?php echo $booking['person_phone']?></td>
               <td class='seats-number'><?php echo $booking['booking_seats']." ".$pending_string?></td>
               <td><?=$booking['booking_comment'] ?></td> 
            </tr>
               <?php } ?>
            <tr id='booked-seats'>
               <td colspan='3'>&nbsp;</td>
               <td class='total-label'><?php _e('Booked', 'eme')?>:</td>
               <td class='seats-number'><?php echo $booked_seats; ?></td>
            </tr>
            <tr id='available-seats'>
               <td colspan='3'>&nbsp;</td> 
               <td class='total-label'><?php _e('Available', 'eme')?>:</td>
               <td class='seats-number'><?php echo $available_seats; ?></td>
            </tr>
         </table>
         </div>
      </body>
      </html>
      <?php
      die();
      
} 

function eme_people_table($message="") {
   $people = eme_get_people();
   $destination = admin_url("admin.php?page=events-manager-people");
   if (count($people) < 1 ) {
      _e("No people have responded to your events yet!", 'eme');
   } else { 
      $result = "<p>".__('This table shows the data about the people who responded to your events', 'eme')."</p>"; 
      if($message != "") {
            $result .= "
            <div id='message' class='updated fade below-h2' style='background-color: rgb(255, 251, 204);'>
               <p>$message</p>
            </div>";
      }

      $result .= "<form id='people-filter' method='post' action='".$destination."'>
                  <input type='hidden' name='action' value='delete_people'/>";
      $result .=" <table id='eme-people-table' class='widefat post fixed'>
            <thead>
            <tr>
            <th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>
            <th class='manage-column ' scope='col'>Name</th>
            <th scope='col'>E-mail</th>
            <th scope='col'>Phone number</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
            <th class='manage-column column-cb check-column' scope='col'>&nbsp;</th>
            <th class='manage-column ' scope='col'>Name</th>
            <th scope='col'>E-mail</th>
            <th scope='col'>Phone number</th>
            </tr>
            </tfoot>
         " ;
      foreach ($people as $person) {
            $result .= "<tr><td><input type='checkbox' class ='row-selector' value='".$person['person_id']."' name='persons[]'/></td>
                  <td>".$person['person_name']."</td>
                  <td>".$person['person_email']."</td>
                  <td>".$person['person_phone']."</td></tr>";
      }

      $result .= "</table>
                     <div class='tablenav'>
                        <div class='alignleft actions'>
                        <input type='checkbox' name='delete_assoc_bookings' value='1'>".__('Also delete associated bookings','eme')."
                        <input class='button-secondary action' type='submit' name='doaction' value='Delete'/>
                        <br class='clear'/>
                        </div>
                        <br class='clear'/>
                     </div>";

      echo $result;
   }
} 

function eme_get_person_by_name_and_email($name, $email) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $name = eme_sanitize_request($name);
   $email = eme_sanitize_request($email);
   $sql = "SELECT * FROM $people_table WHERE person_name = '$name' AND person_email = '$email' ;" ;
   $result = $wpdb->get_row($sql, ARRAY_A);
   return $result;
}

function eme_get_person_by_wp_id($wp_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $wp_id = eme_sanitize_request($wp_id);
   $sql = "SELECT * FROM $people_table WHERE wp_id = '$wp_id';" ;
   $result = $wpdb->get_row($sql, ARRAY_A);
   if (!is_null($result['wp_id']) && $result['wp_id']) {
      $user_info = get_userdata($result['wp_id']);
      $result['person_name']=$user_info->display_name;
      $result['person_email']=$user_info->user_email;
   }
   return $result;
}

function eme_delete_person($person_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = "DELETE FROM $people_table WHERE person_id = '$person_id';" ;
   $wpdb->query($sql);
   return 1;
}

function eme_get_person($person_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = "SELECT * FROM $people_table WHERE person_id = '$person_id';" ;
   $result = $wpdb->get_row($sql, ARRAY_A);
   if (!is_null($result['wp_id']) && $result['wp_id']) {
      $user_info = get_userdata($result['wp_id']);
      $result['person_name']=$user_info->display_name;
      $result['person_email']=$user_info->user_email;
   }
   return $result;
}

function eme_get_people() {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $sql = "SELECT *  FROM $people_table";
   $lines = $wpdb->get_results($sql, ARRAY_A);
   $result = array();
   foreach ($lines as $line) {
      if (!is_null($line['wp_id']) && $line['wp_id']) {
         $user_info = get_userdata($line['wp_id']);
         $line['person_name']=$user_info->display_name;
         $line['person_email']=$user_info->user_email;
      }
      $result[]=$line;
   }
   return $result;
}

function eme_add_person($name, $email, $phone, $wp_id) {
   global $wpdb; 
   $people_table = $wpdb->prefix.PEOPLE_TBNAME;
   $name = eme_sanitize_request($name);
   $email = eme_sanitize_request($email);
   $phone = eme_sanitize_request($phone);
   $wp_id = eme_sanitize_request($wp_id);
   $sql = "INSERT INTO $people_table (person_name, person_email, person_phone, wp_id) VALUES ('$name', '$email', '$phone', '$wp_id');";
   $wpdb->query($sql);
   $eme_rsvp_registered_users_only=get_option('eme_rsvp_registered_users_only');
   if ($eme_rsvp_registered_users_only) {
      $new_person = eme_get_person_by_wp_id($wp_id);
   } else {
      $new_person = eme_get_person_by_name_and_email($name, $email);
   }
   return ($new_person);
}

// when editing other profiles then your own
add_action('edit_user_profile', 'eme_phone_field') ;
// when editing your own profile
add_action('show_user_profile', 'eme_phone_field') ;

function eme_phone_field($user) {
   //$eme_phone=get_user_meta($user,'eme_phone',true);
   $eme_phone=$user->eme_phone;
   ?>
   <h3><?php _e('Phone number', 'eme')?></h3>
   <table class='form-table'>
      <tr>
         <th><label for="eme_phone"><?php _e('Phone number','eme');?></label></th>
         <td><input type="text" name="eme_phone" id="eme_phone" value="<?php echo $eme_phone; ?>" class="regular-text" /> <br/>
         <?php _e('The phone number used by Events Manager Extended when the user is indicated as the contact person for an event.','eme');?></td>
      </tr>
   </table>
   <?php
}

// when editing other profiles then your own
add_action('edit_user_profile_update','eme_update_phone');
// when editing your own profile
add_action('personal_options_update','eme_update_phone');

function eme_update_phone($user_ID) {
   if(isset($_POST['eme_phone']) && $_POST['eme_phone'] != '') {
      update_user_meta($user_ID,'eme_phone', $_POST['eme_phone']);
   }
   
}

function eme_get_indexed_users() {
   global $wpdb;
   $sql = "SELECT display_name, ID FROM $wpdb->users";
   $users = $wpdb->get_results($sql, ARRAY_A);
   $indexed_users = array();
   foreach($users as $user) 
      $indexed_users[$user['ID']] = $user['display_name'];
   return $indexed_users;
}
?>
