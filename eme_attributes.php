<?php
function eme_attributes_form($event) {
   $eme_data = $event['event_attributes'];
   //We also get a list of attribute names and create a ddm list (since placeholders are fixed)
   $formats = 
      get_option('eme_event_list_item_format' ).
      get_option('eme_event_page_title_format' ).
      get_option('eme_full_calendar_event_format' ).
      get_option('eme_location_baloon_format' ).
      get_option('eme_location_event_list_item_format' ).
      get_option('eme_location_page_title_format' ).
      get_option('eme_rss_description_format' ).
      get_option('eme_rss_title_format' ).
      get_option('eme_single_event_format' ).
      get_option('eme_single_location_format' ).
      get_option('eme_contactperson_email_body' ).
      get_option('eme_respondent_email_body' );
   //We now have one long string of formats
   preg_match_all("/#_ATT\{.+?\}(\{.+?\})?/", $formats, $placeholders);
   $attributes = array();
   //Now grab all the unique attributes we can use in our event.
   foreach($placeholders[0] as $result) {
      $attribute = substr( substr($result, 0, strpos($result, '}')), 6 );
      if( !in_array($attribute, $attributes) ){       
         $attributes[] = $attribute ;
      }
   }
   ?>
   <div class="wrap">
      <h2><?php _e('Attributes','eme'); ?></h2>
   <?php if( count( $attributes ) > 0 ) { ?> 
      <p><?php _e('Add attributes here','eme'); ?></p>
      <table class="form-table">
         <thead>
            <tr valign="top">
               <td><strong><?php _e('Attribute Name','eme'); ?></strong></td>
               <td><strong><?php _e('Value','eme'); ?></strong></td>
            </tr>
         </thead>    
         <tfoot>
            <tr valign="top">
               <td colspan="3"><a href="#" id="mtm_add_tag"><?php _e('Add new tag','eme'); ?></a></td>
            </tr>
         </tfoot>
         <tbody id="mtm_body">
            <?php
            $count = 1;
            if( is_array($eme_data) and count($eme_data) > 0){
               foreach( $eme_data as $name => $value){
                  ?>
                  <tr valign="top" id="mtm_<?php echo $count ?>">
                     <td scope="row">
                        <select name="mtm_<?php echo $count ?>_ref">
                           <?php
                           if( !in_array($name, $attributes) ){
                              echo "<option value='$name'>$name (".__('Not defined in templates', 'eme').")</option>";
                           }
                           foreach( $attributes as $attribute ){
                              if( $attribute == $name ) {
                                 echo "<option selected='selected'>$attribute</option>";
                              }else{
                                 echo "<option>$attribute</option>";
                              }
                           }
                           ?>
                        </select>
                        <a href="#" rel="<?php echo $count ?>"><?php _e('Remove','eme'); ?></a>
                     </td>
                     <td>
                        <input type="text" size="40" name="mtm_<?php echo $count ?>_name" value="<?php echo $value ?>" />
                     </td>
                  </tr>
                  <?php
                  $count++;
               }
            }else{
               if( count( $attributes ) > 0 ){
                  ?>
                  <tr valign="top" id="mtm_<?php echo $count ?>">
                     <td scope="row">
                        <select name="mtm_<?php echo $count ?>_ref">
                           <?php
                           foreach( $attributes as $attribute ){
                              echo "<option>$attribute</option>";
                           }
                           ?>
                        </select>
                        <a href="#" rel="<?php echo $count ?>"><?php _e('Remove','eme'); ?></a>
                     </td>
                     <td>
                        <input type="text" size="40" name="mtm_<?php echo $count ?>_name" value="" />
                     </td>
                  </tr>
                  <?php
               }else{
                  ?>
                  <tr valign="top">
                     <td scope="row" colspan='2'>
                     <?php _e('In order to use attributes, you must define some in your templates, otherwise they\'ll never show. Go to Events > Settings to add attribute placeholders.', 'eme'); ?>
                     </td>
                  </tr>
                  <?php
                  
               }
            }
            ?>
         </tbody>
      </table>
   </div>
   <?php
   } else {
   ?>
      <p><?php _e('No attributes defined yet. If you want attributes, you first need to define/use some in the Settings page. See the section about custom attributes on the documention site for more info.','eme'); ?></p>
   <?php
   } //endif count attributes
}
?>
