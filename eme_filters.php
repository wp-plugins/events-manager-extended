<?php

function eme_filter_form_shortcode($atts) {
   extract ( shortcode_atts ( array ('multiple' => 0, 'multisize' => 5, 'scope_count' => 12 ), $atts ) );

   $content=eme_replace_filter_form_placeholders(get_option('eme_filter_form_format'),$multiple,$multisize,$scope_count);
   #$content=eme_replace_filter_form_placeholders("#_FILTER_CATS #_FILTER_LOCS #_FILTER_TOWNS",$multiple,$multisize,$scope_count);
   $this_page_url=get_permalink($post->ID);
   $form = "<form action=$this_page_url method='POST'>";
   $form .= "<input type='hidden' name='eme_eventAction' value='filter' />";
   $form .= $content;
   $form .= "<input type='submit' value='Submit' /></form>";
   return $form;
}
add_shortcode ( 'events_filterform', 'eme_filter_form_shortcode' );

function eme_create_week_scope($count) {
   $day_offset=date('w');
   $start_day=time()-$day_offset*86400;
   $end_day=$start_day+6*86400;
   $scope=array();
   $scope[0] = "";
   for ( $i = 0; $i < $count; $i++) {
      $this_scope=date('Y-m-d',$start_day+$i*7*86400)."--".date('Y-m-d',$end_day+$i*7*86400);
      $scope_text = date_i18n (get_option('date_format'),$start_day+$i*7*86400)." -- ".date_i18n (get_option('date_format'),$end_day+$i*7*86400);
      $scope[$this_scope] = $scope_text;
   }
   return $scope;
}

function eme_create_month_scope($count) {
   $day_offset=date('j')-1;
   $scope=array();
   $scope[0] = "";
   for ( $i = 0; $i < $count; $i++) {
      $year=date('Y', strtotime("$i month")-$day_offset*86400);
      $month=date('m', strtotime("$i month")-$day_offset*86400);
      $number_of_days_month=eme_days_in_month($month,$year);
      $limit_start = "$year-$month-01";
      $limit_end   = "$year-$month-$number_of_days_month";
      $this_scope = "$limit_start--$limit_end";
      $scope_text = date_i18n (get_option('eme_show_period_monthly_dateformat'), strtotime("$i month")-$day_offset*86400);
      $scope[$this_scope] = $scope_text;
   }
   return $scope;
}

function eme_replace_filter_form_placeholders($format, $multiple, $multisize, $scope_count) {
   preg_match_all("/#_[A-Za-z0-9_\[\]]+/", $format, $placeholders);
   usort($placeholders[0],'sort_stringlenth');
   $cat_post_name="eme_cat_filter";
   $loc_post_name="eme_loc_filter";
   $town_post_name="eme_town_filter";
   $scope_post_name="eme_scope_filter";

   if (isset($_POST[$scope_post_name])) {
      $selected_scope = $_POST[$scope_post_name];
   } else {
      $selected_scope = "";
   }

   if (isset($_POST[$loc_post_name])) {
      $selected_location=$_POST[$loc_post_name];
   } else {
      $selected_location="";
   }

   if (isset($_POST[$town_post_name])) {
      $selected_town=$_POST[$town_post_name];
   } else {
      $selected_town="";
   }

   if (isset($_POST[$cat_post_name])) {
      $selected_category=$_POST[$cat_post_name];
   } else {
      $selected_category="";
   }

   foreach($placeholders[0] as $result) {
      $replacement = "";
      if (preg_match('/^#_FILTER_CATS$/', $result) && get_option('eme_categories_enabled')) {
         $categories = eme_get_categories();
         if ($categories) {
            $cat_list = array();
            $cat_list[0]="---";
            foreach ($categories as $this_category) {
               $id=$this_category['category_id'];
               $cat_list[$id]=eme_trans_sanitize_html($this_category['category_name']);
            }
            if ($multiple)
               $replacement = eme_ui_multiselect($selected_category,$cat_post_name,$cat_list,$multisize);
            else
               $replacement = eme_ui_select($selected_category,$cat_post_name,$cat_list);
         }

      } elseif (preg_match('/^#_FILTER_LOCS$/', $result)) {
         $locations = eme_get_locations();
         if ($locations) {
            $loc_list = array();
            $loc_list[0]="---";
            foreach ($locations as $this_location) {
               $id=$this_location['location_id'];
               $loc_list[$id]=eme_trans_sanitize_html($this_location['location_name']);
            }
            if ($multiple)
               $replacement = eme_ui_multiselect($selected_location,$loc_post_name,$loc_list,$multisize);
            else
               $replacement = eme_ui_select($selected_location,$loc_post_name,$loc_list);
         }

      } elseif (preg_match('/^#_FILTER_TOWNS$/', $result)) {
         $towns = eme_get_locations();
         if ($towns) {
            $town_list = array();
            $town_list[0]="---";
            foreach ($towns as $this_town) {
               $id=eme_trans_sanitize_html($this_town['location_town']);
               $town_list[$id]=$id;
            }
            if ($multiple)
               $replacement = eme_ui_multiselect($selected_location,$loc_post_name,$loc_list,$multisize);
            else
               $replacement = eme_ui_select($selected_town,$town_post_name,$town_list);
         }

      } elseif (preg_match('/^#_FILTER_WEEKS$/', $result)) {
         $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_week_scope($scope_count));
      } elseif (preg_match('/^#_FILTER_MONTHS$/', $result)) {
         $replacement = eme_ui_select($selected_scope,$scope_post_name,eme_create_month_scope($scope_count));
      } 

      $format = str_replace($result, $replacement ,$format );
   }

   return do_shortcode($format);
}

?>
