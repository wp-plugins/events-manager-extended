<?php

function eme_if_shortcode($atts,$content) {
   extract ( shortcode_atts ( array ('tag' => '', value => '' ), $atts ) );
   if (!empty($value)) {
      if ($tag===$value) return $content;
   } else {
      if (!empty($tag)) return $content;
   }
}
add_shortcode ( 'events_if', 'eme_if_shortcode');

?>
