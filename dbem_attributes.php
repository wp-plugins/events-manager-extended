<?php
function dbem_attributes_form($event) {
	$dbem_data = $event['event_attributes'];
	//We also get a list of attribute names and create a ddm list (since placeholders are fixed)
	$formats = 
		get_option ( 'dbem_event_list_item_format' ).
		get_option ( 'dbem_event_page_title_format' ).
		get_option ( 'dbem_full_calendar_event_format' ).
		get_option ( 'dbem_location_baloon_format' ).
		get_option ( 'dbem_location_event_list_item_format' ).
		get_option ( 'dbem_location_page_title_format' ).
		get_option ( 'dbem_rss_description_format' ).
		get_option ( 'dbem_rss_title_format' ).
		get_option ( 'dbem_single_event_format' ).
		get_option ( 'dbem_single_location_format' ).
		get_option ( 'dbem_contactperson_email_body' ).
		get_option ( 'dbem_respondent_email_body' );
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
		<h2>Attributes</h2>
		<p>Add attributes here</p>
		<table class="form-table">
			<thead>
				<tr valign="top">
					<td><strong>Attribute Name</strong></td>
					<td><strong>Value</strong></td>
				</tr>
			</thead>    
			<tfoot>
				<tr valign="top">
					<td colspan="3"><a href="#" id="mtm_add_tag">Add new tag</a></td>
				</tr>
			</tfoot>
			<tbody id="mtm_body">
				<?php
				$count = 1;
				if( is_array($dbem_data) and count($dbem_data) > 0){
					foreach( $dbem_data as $name => $value){
						?>
						<tr valign="top" id="mtm_<?php echo $count ?>">
							<td scope="row">
								<select name="mtm_<?php echo $count ?>_ref">
									<?php
									if( !in_array($name, $attributes) ){
										echo "<option value='$name'>$name (".__('Not defined in templates', 'dbem').")</option>";
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
								<a href="#" rel="<?php echo $count ?>">Remove</a>
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
								<a href="#" rel="<?php echo $count ?>">Remove</a>
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
							<?php _e('In order to use attributes, you must define some in your templates, otherwise they\'ll never show. Go to Events > Settings to add attribute placeholders.', 'dbem'); ?>
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
}
?>
