<?php

class WP_Widget_eme_list extends WP_Widget {
   function WP_Widget_eme_list() {
      $widget_ops = array('classname' => 'widget_eme_list', 'description' => __( 'Events List','eme' ) );
      $this->WP_Widget('eme_list', __('Events List','eme'), $widget_ops);
      $this->alt_option_name = 'widget_eme_list';
   }
   function widget( $args, $instance ) {
      extract($args);
      //$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Events','eme' ) : $instance['title'], $instance, $this->id_base);
      $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
      $limit = empty( $instance['limit'] ) ? 5 : $instance['limit'];
      $scope = empty( $instance['scope'] ) ? 'future' : $instance['scope'];
      $showperiod = empty( $instance['showperiod'] ) ? '' : $instance['showperiod'];
      $order = empty( $instance['order'] ) ? 'ASC' : $instance['order'];
      $header = empty( $instance['header'] ) ? '<ul>' : $instance['header'];
      $footer = empty( $instance['footer'] ) ? '</ul>' : $instance['footer'];
      if ($instance['authorid']==-1 ) {
         $author='';
      } else {
         $authinfo=get_userdata($instance['authorid']);
         $author=$authinfo->user_login;
      }
      $category = empty( $instance['category'] ) ? '' : $instance['category'];
      $format = empty( $instance['format'] ) ? DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT : $instance['format'];
      echo $before_widget;
      if ( $title)
         echo $before_title . $title . $after_title;

      $events_list = eme_get_events_list($limit,$scope,$order,$format,false,$category,$showperiod,$author);
      if ($events_list == __('No events', 'eme'))
         echo "<ul><li>$events_list</li></ul>";
      else
         echo $header.$events_list.$footer;
      echo $after_widget;
   }
   function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['limit'] = $new_instance['limit'];
      if ( in_array( $new_instance['scope'], array( 'future', 'all', 'past' ) ) ) {
         $instance['scope'] = $new_instance['scope'];
      } else {
         $instance['scope'] = 'future';
      }
      if ( in_array( $new_instance['showperiod'], array( 'daily', 'monthly' ) ) ) {
         $instance['showperiod'] = $new_instance['showperiod'];
      } else {
         $instance['showperiod'] = '';
      }
      if ( in_array( $new_instance['order'], array( 'ASC', 'DESC' ) ) ) {
         $instance['order'] = $new_instance['order'];
      } else {
         $instance['order'] = 'ASC';
      }
      $instance['category'] = $new_instance['category'];
      $instance['format'] = $new_instance['format'];
      $instance['authorid'] = $new_instance['authorid'];
      $instance['header'] = $new_instance['header'];
      $instance['footer'] = $new_instance['footer'];
      return $instance;
   }
   function form( $instance ) {
      //Defaults
      $instance = wp_parse_args( (array) $instance, array( 'limit' => 5, 'scope' => 'future', 'order' => 'ASC', 'format' => DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT, 'authorid' => '' ) );
      $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
      $limit = empty( $instance['limit'] ) ? 5 : $instance['limit'];
      $scope = empty( $instance['scope'] ) ? 'future' : $instance['scope'];
      $showperiod = empty( $instance['showperiod'] ) ? '' : $instance['showperiod'];
      $order = empty( $instance['order'] ) ? 'ASC' : $instance['order'];
      $header = empty( $instance['header'] ) ? '<ul>' : $instance['header'];
      $footer = empty( $instance['footer'] ) ? '</ul>' : $instance['footer'];
      $category = empty( $instance['category'] ) ? '' : $instance['category'];
      $authorid = empty( $instance['authorid'] ) ? '' : $instance['authorid'];
      $categories = eme_get_categories();
      $format = empty( $instance['format'] ) ? DEFAULT_WIDGET_EVENT_LIST_ITEM_FORMAT : $instance['format'];
?>
  <p>
   <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
   <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Number of events','eme'); ?>: </label>
    <input type="text" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $limit;?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('scope'); ?>"><?php _e('Scope of the events','eme'); ?>:</label><br />
   <select id="<?php echo $this->get_field_id('scope'); ?>" name="<?php echo $this->get_field_name('scope'); ?>">
         <option value="future" <?php selected( $scope, 'future' ); ?>><?php _e('Future events','eme'); ?></option>
         <option value="all" <?php selected( $scope, 'all' ); ?>><?php _e('All events','eme'); ?></option>
         <option value="past" <?php selected( $scope, 'past' ); ?>><?php _e('Past events','eme'); ?></option>
    </select>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('showperiod'); ?>"><?php _e('Show events per period','eme'); ?>:</label><br />
   <select id="<?php echo $this->get_field_id('showperiod'); ?>" name="<?php echo $this->get_field_name('showperiod'); ?>">
         <option value="" <?php selected( $showperiod, '' ); ?>><?php _e('Select...','eme'); ?></option>
         <option value="daily" <?php selected( $showperiod, 'daily' ); ?>><?php _e('Daily','eme'); ?></option>
         <option value="monthly" <?php selected( $showperiod, 'monthly' ); ?>><?php _e('Monthly','eme'); ?></option>
    </select>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order of the events','eme'); ?>:</label><br />
    <select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
         <option value="ASC" <?php selected( $order, 'ASC' ); ?>><?php _e('Ascendant','eme'); ?></option>
         <option value="DESC" <?php selected( $order, 'DESC' ); ?>><?php _e('Descendant','eme'); ?></option>
    </select>
  </p>
<?php
  if(get_option('eme_categories_enabled')) {
?>
  <p>
    <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category','eme'); ?>:</label><br />
    <select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
      <option value=""><?php _e ( 'Select...', 'eme' ); ?></option>
      <?php
      foreach ( $categories as $my_category ){
      ?>
         <option value="<?php echo $my_category['category_id']; ?>" <?php selected( $category,$my_category['category_id']); ?>><?php echo $my_category['category_name']; ?></option>
      <?php
      }
      ?>
    </select>
  </p>
<?php
  }
?>
  <p>
    <label for="<?php echo $this->get_field_id('authorid'); ?>"><?php _e('Author','eme'); ?>:</label><br />
<?php
wp_dropdown_users ( array ('id' => $this->get_field_id('authorid'), 'name' => $this->get_field_name('authorid'), 'show_option_none' => __ ( "Select...", 'eme' ), 'selected' => $authorid ) );
?>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('header'); ?>"><?php _e('List header format<br />(if empty &lt;ul&gt; is used)','eme'); ?>: </label>
    <input type="text" id="<?php echo $this->get_field_id('header'); ?>" name="<?php echo $this->get_field_name('header'); ?>" value="<?php echo $header;?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('format'); ?>"><?php _e('List item format','eme'); ?>:</label>
    <textarea id="<?php echo $this->get_field_id('format'); ?>" name="<?php echo $this->get_field_name('format'); ?>" rows="5" cols="24"><?php echo eme_sanitize_html($format);?></textarea>
  </p> 
  <p>
    <label for="<?php echo $this->get_field_id('footer'); ?>"><?php _e('List footer format<br />(if empty &lt;/ul&gt; is used)','eme'); ?>: </label>
    <input type="text" id="<?php echo $this->get_field_id('footer'); ?>" name="<?php echo $this->get_field_name('footer'); ?>" value="<?php echo $footer;?>" />
  </p>
<?php
    }
}     

class WP_Widget_eme_calendar extends WP_Widget {
   function WP_Widget_eme_calendar() {
      $widget_ops = array('classname' => 'widget_eme_calendar', 'description' => __( 'Events Calendar', 'eme' ) );
      $this->WP_Widget('eme_calendar', __('Events Calendar','eme'), $widget_ops);
      $this->alt_option_name = 'widget_eme_calendar';
   }
   function widget( $args, $instance ) {
      extract($args);
      //$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Calendar','eme' ) : $instance['title'], $instance, $this->id_base);
      $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
      $long_events = isset( $instance['long_events'] ) ? $instance['long_events'] : false;
      $category = empty( $instance['category'] ) ? '' : $instance['category'];
      if ($instance['authorid']==-1 ) {
         $author='';
      } else {
         $authinfo=get_userdata($instance['authorid']);
         $author=$authinfo->user_login;
      }
      echo $before_widget;
      if ( $title)
         echo $before_title . $title . $after_title;
      
      $options=array();
      $options['title'] = $title;
      $options['long_events'] = $long_events;
      $options['category'] = $category;
      $options['month'] = date("m");
      $options['author'] = $author;
      eme_get_calendar($options);
      echo $after_widget;
   }
   
   function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['category'] = $new_instance['category'];
      $instance['long_events'] = $new_instance['long_events'];
      $instance['authorid'] = $new_instance['authorid'];
      return $instance;
   }

   function form( $instance ) {
      //Defaults
      $instance = wp_parse_args( (array) $instance, array( 'long_events' => 0 ) );
      $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
      $category = empty( $instance['category'] ) ? '' : $instance['category'];
      $long_events = isset( $instance['long_events'] ) ? $instance['long_events'] : false;
      $authorid = isset( $instance['authorid'] ) ? $instance['authorid'] : '';
      $categories = eme_get_categories();
?>
  <p>
   <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
   <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
  </p>      
  <p>
    <label for="<?php echo $this->get_field_id('long_events'); ?>"><?php _e('Show Long Events?', 'eme'); ?>:</label>
    <input type="checkbox" id="<?php echo $this->get_field_id('long_events'); ?>" name="<?php echo $this->get_field_name('long_events'); ?>" value="1" <?php echo ($long_events) ? 'checked="checked"':'' ;?> />
  </p>
  <?php
      if(get_option('eme_categories_enabled')) {
  ?>
  <p>
    <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category','eme'); ?>:</label><br />
   <select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
      <option value=""><?php _e ( 'Select...', 'eme' ); ?>   </option>
      <?php
      foreach ( $categories as $my_category ){
      ?>
      <option value="<?php echo $my_category['category_id']; ?>" <?php selected( $category,$my_category['category_id']); ?>><?php echo $my_category['category_name']; ?></option>
      <?php
      }
      ?>
   </select>
  </p>
<?php
      }
?>
  <p>
    <label for="<?php echo $this->get_field_id('authorid'); ?>"><?php _e('Author','eme'); ?>:</label><br />
<?php
wp_dropdown_users ( array ('id' => $this->get_field_id('authorid'), 'name' => $this->get_field_name('authorid'), 'show_option_none' => __ ( "Select...", 'eme' ), 'selected' => $authorid ) );
?>
  </p>
<?php
   } 
}

function eme_load_widgets() {
   register_widget( 'WP_Widget_eme_list' );
   register_widget( 'WP_Widget_eme_calendar' );
}
add_action( 'widgets_init', 'eme_load_widgets' );

?>
