<?php

function eme_cleanup_page() {
   global $wpdb;

   $bookings_table = $wpdb->prefix.BOOKINGS_TBNAME;
   $events_table = $wpdb->prefix . EVENTS_TBNAME;
   $recurrence_table = $wpdb->prefix.RECURRENCE_TBNAME;

   if (current_user_can( EDIT_CAPABILITY)) {
      // do the actions if required
      if (isset($_POST['eme_action']) && $_POST['eme_action'] == "eme_cleanup" && isset($_POST['eme_number']) && isset($_POST['eme_period'])) {
         $eme_number = intval($_POST['eme_number']);
         $eme_period = $_POST['eme_period'];
         if ( !in_array( $eme_period, array( 'day', 'week', 'month' ) ) ) 
            $eme_period = "month";
         $end_date=date('Y-m-d', strtotime("-$eme_number $eme_period"));
         $wpdb->query("DELETE FROM $bookings_table where event_id in (SELECT event_id from $events_table where event_end_date<'$end_date')";
         $wpdb->query"DELETE FROM $events_table where event_end_date<'$end_date'";
         $wpdb->query"DELETE FROM $recurrence_table where recurrence_end_date<'$end_date'";
      }
   }

   eme_cleanup_form();
}

function eme_cleanup_form() {
?>
<div class="wrap">
<div id="icon-events" class="icon32"><br />
</div>
<?php admin_show_warnings();?>
<h2><?php _e ('Cleanup: remove old events','eme'); ?></h2>
   <form id="posts-filter" action="" method="post">
<?php _e('Remove events older than','eme'); ?>
   <input type='hidden' name='page' value='events-manager-cleanup' />
   <input type='hidden' name='eme_action' value='eme_cleanup' />
   <div class="tablenav">

   <div class="alignleft actions">
   <input type="text" id="eme_number" name="eme_number" size=3>
   <select name="eme_period">
   <option value="day" selected="selected"><?php _e ( 'Day(s)','eme' ); ?></option>
   <option value="week"><?php _e ( 'Week(s)','eme' ); ?></option>
   <option value="month"><?php _e ( 'Month(s)','eme' ); ?></option>
   </select>
   <input type="submit" value="<?php _e ( 'Apply' ); ?>" name="doaction" id="eme_doaction" class="button-secondary action" />
   </div>

   <div class="clear"></div>
   </div>
   </form>
</div>
<?php
}

?>
