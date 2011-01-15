<?php

function eme_if_shortcode($atts,$content) {
   extract ( shortcode_atts ( array ('tag' => '', 'value' => '', 'notvalue' => '', 'lt' => '', 'gt' => '' ), $atts ) );
   if (!empty($value)) {
      if ($tag===$value) return $content;
   } elseif (!empty($notvalue)) {
      if ($tag!==$value) return $content;
   } elseif (!empty($lt)) {
      if ($tag<$lt) return $content;
   } elseif (!empty($gt)) {
      if ($tag>$gt) return $content;
   } else {
      if (!empty($tag)) return $content;
   }
}
add_shortcode ( 'events_if', 'eme_if_shortcode');

?>
