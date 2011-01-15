<?php

function eme_if_shortcode($atts,$content) {
   extract ( shortcode_atts ( array ('tag' => '', value => '' ), $atts ) );
   global $eme_placeholders;
   if (!empty($value)) {
      if (isset($eme_placeholders["#".$tag]) && ($eme_placeholders["#".$tag]===$value)) return $content;
   } else {
      if (isset($eme_placeholders["#".$tag]) && !empty($eme_placeholders["#".$tag])) return $content;
   }
}
add_shortcode ( 'events_if', 'eme_if_shortcode' );

?>
