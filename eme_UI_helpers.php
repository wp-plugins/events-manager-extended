<?php 
function eme_option_items($array, $saved_value) {
   $output = "";
   foreach($array as $key => $item) {
      $selected ='';
      if ($key == $saved_value)
         $selected = "selected='selected'";
      $output .= "<option value='$key' $selected >$item</option>\n";
   
   } 
   echo $output;
}

function eme_checkbox_items($name, $array, $saved_values, $horizontal = true) { 
   $output = "";
   foreach($array as $key => $item) {
      
      $checked = "";
      if (in_array($key, $saved_values))
         $checked = "checked='checked'";
      $output .=  "<input type='checkbox' name='$name' value='$key' $checked /> $item ";
      if(!$horizontal)  
         $output .= "<br/>\n";
   }
   echo $output;
   
}

function eme_options_input_text($title, $name, $description) {
   $value= preg_replace("/\r\n|\n\r|\n/","<br />",get_option($name));
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
      <th scope="row"><?php _e($title, 'eme') ?></th>
       <td>
         <input name="<?php echo $name ?>" type="text" id="<?php echo $name ?>" style="width: 95%" value="<?php echo eme_sanitize_html($value); ?>" size="45" /><br />
                  <?php _e($description, 'eme') ?>
         </td>
      </tr>
   <?php
}
function eme_options_input_password($title, $name, $description) {
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
      <th scope="row"><?php _e($title, 'eme') ?></th>
       <td>
         <input name="<?php echo $name ?>" type="password" id="<?php echo $name ?>" style="width: 95%" value="<?php echo get_option($name); ?>" size="45" /><br />
                  <?php echo $description; ?>
         </td>
      </tr>
   <?php
}

function eme_options_textarea($title, $name, $description) {
   ?>
   <tr valign="top" id='<?php echo $name;?>_row'>
      <th scope="row"><?php _e($title,'eme')?></th>
         <td><textarea name="<?php echo $name ?>" id="<?php echo $name ?>" rows="6" cols="60"><?php echo eme_sanitize_html(get_option($name));?></textarea><br/>
            <?php echo $description; ?></td>
      </tr>
   <?php
}

function eme_options_radio_binary($title, $name, $description) {
      $list_events_page = get_option($name); ?>
       
         <tr valign="top" id='<?php echo $name;?>_row'>
            <th scope="row"><?php _e($title,'eme'); ?></th>
            <td>
            <input id="<?php echo $name ?>_yes" name="<?php echo $name ?>" type="radio" value="1" <?php if($list_events_page) echo "checked='checked'"; ?> /><?php _e('Yes'); ?> <br />
            <input  id="<?php echo $name ?>_no" name="<?php echo $name ?>" type="radio" value="0" <?php if(!$list_events_page) echo "checked='checked'"; ?> /><?php _e('No'); ?> <br />
            <?php echo $description; ?>
         </td>
         </tr>
<?php 
}
function eme_options_select($title, $name, $list, $description) {
      $option_value = get_option($name); ?>
    
         <tr valign="top" id='<?php echo $name;?>_row'>
            <th scope="row"><?php _e($title,'eme'); ?></th>
            <td>
            <select name="<?php echo $name; ?>" > 
               <?php foreach($list as $key => $value) {
                  "$key" == $option_value ? $selected = "selected='selected' " : $selected = '';
             echo "<option value='$key' $selected>$value</option>";
              } ?>
            </select> <br/>
            <?php echo $description; ?>
         </td>
         </tr>
<?php 
}

?>
