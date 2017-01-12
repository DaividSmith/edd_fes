<?php
/**
 * FES Formbuilder Template Helpers
 *
 * Provides helper functions that every FES field
 * uses to make the formbuilder box for a particular
 * field easier to write.
 *
 * @package FES
 * @subpackage Formbuilder
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * Warning: This class is a candidate for deprecation starting with 2.4
 */

/**
 * FES Formbuilder Templates
 *
 * This class contains helper functions for commonly used
 * formbuilder template HTML blocks
 *
 * @since 2.0.0
 * @access public
 */
class FES_Formbuilder_Templates {

	/**
	 * Legend of a form item.
	 * 
	 * Shows the legend of pre-FES Field class field. Slated
	 * for removal in 2.4.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @deprecated 2.3.0
	 * @see  FES_Field::legend() The legend equivolent in FES Field fields.
	 * 
	 * @param string $title Title of the field.
	 * @param array $values The equivolent of FES Field characteristics.
	 * @param bool $removable Whether or not the field can be removed on the current form.
	 * @return void
	 */	
	public static function legend( $title = 'Field Name', $values = array(), $removable = true ) {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->legend', '2.3', 'FES_Field->legend' );
		$field_label = '';
		$legend      = '';
		if ( empty( $values['label'] ) && !empty( $values['class'] ) ) {
			$field       = new $values['class'];
			$title       = $field->supports['title'];
			$legend      = '<strong>'. esc_html( $title ) . '</strong>';
		} else if ( !empty( $values['label'] ) && !empty( $values['class'] ) ) {
				$field_label = $values['label'];
				$field       = new $values['class'];
				$title       = $field->supports['title'];
				if ( $title === $field_label ) {
					$legend  = '<strong>' . esc_html( $title)  . '</strong>';
				}
				else {
					$legend  = '<strong>' . esc_html( $title ). '</strong>: '. esc_html( $field_label);
				}
		} else {
			$field_label = $values && isset( $values['label'] ) ? $values['label'] : '';
			$title       = $title;
			if ( $title === $field_label ) {
				$legend      = '<strong>' . esc_html( $title)  . '</strong>';
			}
			else {
				$legend      = '<strong>' . esc_html( $title) . '</strong>: '. esc_html( $field_label);
			}
		} ?>
		<div class="fes-legend" title="<?php _e( 'Click and Drag to rearrange', 'edd_fes' ); ?>">
			<div class="fes-label"><?php echo esc_html( $legend); ?></div>
			<div class="fes-actions">
				<?php if ( $removable ) { ?>
				<a href="#" class="fes-remove"><?php _e( 'Remove', 'edd_fes' ); ?></a>
				<?php } ?>
				<a href="#" class="fes-toggle"><?php _e( 'Toggle', 'edd_fes' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Note: Deprecated since 2.3. Slated for removal in 2.4.
	 */
	/**
	 * Common Fields for a input field.
	 * 
	 * Contains required, label, meta_key, help text, css class name.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @deprecated 2.3.0
	 * @see  Formbuilder_Templates::standard() The new standard output for formbuilder fields.
	 * 
	 * @param int     $id               Order number of the field in formbuilder.
	 * @param string  $field_name_value The `name` attribute of the field.
	 * @param bool    $custom_field     If it is a custom field or not.
	 * @param array   $values           Saved characteristics of the field.
	 * @param bool    $force_required   If the field is required to be on
	 *                                  this formbuilder, disables the ability to remove the field.
	 * @param string  $template         The template for this field.
	 * @return void
	 */		
	public static function common( $id, $field_name_value = '', $custom_field = true, $values = array(), $force_required = false, $template = 'text' ) {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->common', '2.3', 'EDD_FES()->formbuilder_templates->standard' );
		$tpl = '%s[%d][%s]';
		$required_name = sprintf( $tpl, 'fes_input', $id, 'required' );
		$field_name    = sprintf( $tpl, 'fes_input', $id, 'name' );
		$label_name    = sprintf( $tpl, 'fes_input', $id, 'label' );
		$help_name     = sprintf( $tpl, 'fes_input', $id, 'help' );
		$css_name      = sprintf( $tpl, 'fes_input', $id, 'css' );

		$required_from_bool = isset( $values['required'] ) ? $values['required'] : 'yes';
		if ( $required_from_bool !== 'yes' && $required_from_bool !== 'no' ) {
			$required_from_bool = $values['required'] ? 'yes' : 'no';
		}

		$required           = isset( $values['required'] ) ? esc_attr( $required_from_bool ) : 'yes';
		$template           = !empty( $values['template'] ) ? $values['template'] : $template;
		$label_value        = isset( $values['label'] ) && ! empty( $values['label'] ) ? esc_attr( $values['label'] ) : esc_attr( ucwords( str_replace( '_', ' ', $template ) ) );
		$help_value         = isset( $values['help'] )? esc_textarea( $values['help'] ) : '';
		$css_value          = isset( $values['css'] )? esc_attr( $values['css'] ) : '';
		$meta_type          = "yes"; // for post meta on custom fields

		if ( $custom_field && $values ) {
			$field_name_value = trim( $values[ 'name' ] );
		}

		$exclude = array( 'email_to_use_for_contact_form', 'name_of_store' );
		if ( $custom_field && in_array( $field_name_value, $exclude ) ) {
			$custom_field = false;
		}

		do_action( 'fes_add_field_to_common_form_element', $tpl, 'fes_input', $id, $values ); ?>

		<div class="fes-form-rows required-field">
			<?php if ( !$force_required ) { ?>
				<label> <?php if ( !$force_required ) { ?><?php _e( 'Required', 'edd_fes' ); ?><?php } ?></label>
				<div class="fes-form-sub-fields">
					<input type="radio" name="<?php echo $required_name; ?>" <?php checked( $required, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?>
					<input type="radio" name="<?php echo $required_name; ?>" <?php checked( $required, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?>
				</div>
			<?php } else { ?>
				<input type="hidden" name="<?php echo $required_name; ?>" value="yes" checked />
			<?php } ?>
		</div>

		<div class="fes-form-rows">
			<label><?php _e( 'Field Label', 'edd_fes' ); ?></label>
			<input type="text" data-type="label" name="<?php echo $label_name; ?>" value="<?php echo esc_html( $label_value); ?>" class="smallipopInput" title="<?php _e( 'Enter a title of this field', 'edd_fes' ); ?>">
		</div>

		<?php if ( $custom_field ) { ?>
			<div class="fes-form-rows">
				<label><?php _e( 'Meta Key', 'edd_fes' ); ?></label>
				<input type="text" name="<?php echo $field_name; ?>" value="<?php echo esc_html( trim( $field_name_value ));?>" data-type="metakey" class="smallipopInput" title="<?php _e( 'Name of the meta key this field will save to', 'edd_fes' ); ?>">
			</div>
		<?php } else { ?>
			<input type="hidden" name="<?php echo $field_name; ?>" value="<?php echo esc_html( trim( $field_name_value )); ?>">
		<?php } ?>

		<div class="fes-form-rows">
			<label><?php _e( 'Help text', 'edd_fes' ); ?></label>
			<textarea name="<?php echo $help_name; ?>" class="smallipopInput" title="<?php _e( 'Give the user some information about this field', 'edd_fes' ); ?>"><?php echo esc_html( $help_value); ?></textarea>
		</div>
		<?php if ( !isset( $values['no_css'] ) || !$values['no_css'] ) { ?>
		<div class="fes-form-rows">
			<label><?php _e( 'CSS Class Name', 'edd_fes' ); ?></label>
			<input type="text" name="<?php echo $css_name; ?>" value="<?php echo esc_html( $css_value); ?>" class="smallipopInput" title="<?php _e( 'Add a CSS class name for this field', 'edd_fes' ); ?>">
		</div>
		<?php } ?>
		<?php
	}

	/**
	 * Common Fields for a input field.
	 *
	 * Contains required, label, meta_key, help text, css class name.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @param int        $id    Order number of the field in formbuilder.
	 * @param FES_Field  $field An FES Field object.
	 * @return void
	 */
	public static function standard( $index, $field ) {
		// this is a quick port from the old common function. We'll clean this function up in 2.4
		$field_name_value = $field->name();
		$custom_field     = $field->supports['position'] == 'custom';
		$values           = $field->characteristics;
		$required         = isset( $field->supports['permissions']['can_remove_from_formbuilder'] ) ? $field->supports['permissions']['can_remove_from_formbuilder'] : true;
		$force_required   = isset( $field->supports['permissions']['field_always_required'] ) ? $field->supports['permissions']['field_always_required'] : false;
		$template         = $field->characteristics['template'];
		$tpl              = '%s[%d][%s]';
		$required_name    = sprintf( $tpl, 'fes_input', $index, 'required' );
		$field_name       = sprintf( $tpl, 'fes_input', $index, 'name' );
		$label_name       = sprintf( $tpl, 'fes_input', $index, 'label' );
		$help_name        = sprintf( $tpl, 'fes_input', $index, 'help' );
		$css_name         = sprintf( $tpl, 'fes_input', $index, 'css' );

		if ( $force_required ) {
			$required = true;
		} else {
			// if saved field before
			if ( isset( $values['required'] ) ) {
				if (  $values['required'] === 'yes' ) {
					$required = true;
				} else {
					$required = false;
				}
			} else {
				// no change
			}
		}

		if ( $required ) {
			$required = 'yes';
		} else {
			$required = 'no';
		}

		$template           = !empty( $values['template'] ) ? $values['template'] : $template;
		$label_value        = isset( $values['label'] ) && ! empty( $values['label'] ) ? esc_attr( $values['label'] ) : esc_attr( ucwords( str_replace( '_', ' ', $template ) ) );
		$help_value         = isset( $values['help'] )? esc_textarea( $values['help'] ) : '';
		$css_value          = isset( $values['css'] )? esc_attr( $values['css'] ) : '';
		$meta_type          = "yes"; // for post meta on custom fields

		$exclude = array( 'email_to_use_for_contact_form', 'name_of_store', 'recaptcha' );
		if ( $custom_field && in_array( $field_name_value, $exclude ) ) {
			$custom_field = false;
		}

		do_action( 'fes_add_field_to_common_form_element', $tpl, 'fes_input', $index, $values ); ?>

		<div class="fes-form-rows required-field">
			<?php if ( !$force_required ) { ?>
				<label><?php _e( 'Required', 'edd_fes' ); ?></label>
				<div class="fes-form-sub-fields">
					<input type="radio" name="<?php echo esc_html( $required_name); ?>" value="yes" <?php checked( $required, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?>
					<input type="radio" name="<?php echo esc_html( $required_name); ?>" value="no" <?php checked( $required, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?>
				</div>
			<?php } else { ?>
				<input type="hidden" name="<?php echo esc_html( $required_name); ?>" value="yes" checked />
			<?php } ?>
		</div>

		<div class="fes-form-rows">
			<label><?php _e( 'Field Label', 'edd_fes' ); ?></label>
			<input type="text" data-type="label" name="<?php echo esc_html( $label_name); ?>" value="<?php echo esc_html( $label_value); ?>" class="smallipopInput" title="<?php _e( 'Enter a title of this field', 'edd_fes' ); ?>">
		</div>

		<?php
		if ( $custom_field && ( isset( $field->supports['permissions']['can_change_meta_key'] ) && $field->supports['permissions']['can_change_meta_key'] ) !== false ) { ?>
			<div class="fes-form-rows">
				<label><?php _e( 'Meta Key', 'edd_fes' ); ?></label>
				<input type="text" name="<?php echo $field_name; ?>" value="<?php echo esc_html( $field_name_value); ?>" data-type="metakey" class="smallipopInput" title="<?php _e( 'Name of the meta key this field will save to', 'edd_fes' ); ?>">
			</div>
		<?php } else { ?>
			<input type="hidden" name="<?php echo $field_name; ?>" value="<?php echo esc_html( $field_name_value); ?>">
		<?php } ?>

		<div class="fes-form-rows">
			<label><?php _e( 'Help text', 'edd_fes' ); ?></label>
			<textarea name="<?php echo $help_name; ?>" class="smallipopInput" title="<?php _e( 'Give the user some information about this field', 'edd_fes' ); ?>"><?php echo esc_html( $help_value); ?></textarea>
		</div>
		<?php if ( !isset( $values['no_css'] ) || !$values['no_css'] ) { ?>
		<div class="fes-form-rows">
			<label><?php _e( 'CSS Class Name', 'edd_fes' ); ?></label>
			<input type="text" name="<?php echo $css_name; ?>" value="<?php echo esc_html( $css_value); ?>" class="smallipopInput" title="<?php _e( 'Add a CSS class name for this field', 'edd_fes' ); ?>">
		</div>
		<?php } ?>
		<?php
	}

	/**
	 * Common fields for a text field.
	 *
	 * Contains items like size and placeholder of the textbox.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param  int        $id     Order number of the field in formbuilder.
	 * @param  array      $values Characteristics array from an FES Field object.
	 * @return void
	 */	
	public static function common_text( $id, $values = array() ) {
		$tpl  			    = '%s[%d][%s]';
		$placeholder_name   = sprintf( $tpl, 'fes_input', $id, 'placeholder' );
		$default_name       = sprintf( $tpl, 'fes_input', $id, 'default' );
		$size_name 		    = sprintf( $tpl, 'fes_input', $id, 'size' );
		$placeholder_value  = $values && isset( $values['placeholder'] ) 	  ? esc_attr( $values['placeholder'] ) : '';
		$default_value 	    = $values && isset( $values['default'] )  	 	  ? esc_attr( $values['default'] ) 	   : '';
		$size_value  	    = $values && isset( $values['size'] )  		 	  ? esc_attr( $values['size'] ) 	   : '40';
		$show_placeholder   = $values && empty( $values['show_placeholder'] ) ? false 							   : true;
		$show_default_value = $values && empty( $values['default_value'] )    ? false 							   : true;

		if ( $show_placeholder ) { ?>
		<div class="fes-form-rows">
			<label><?php _e( 'Placeholder text', 'edd_fes' ); ?></label>
			<input type="text" class="smallipopInput" name="<?php echo $placeholder_name; ?>" title="<?php esc_attr_e( 'Text for HTML5 placeholder attribute', 'edd_fes' ); ?>" value="<?php echo esc_html( $placeholder_value); ?>" />
		</div>
		<?php }
		if ( $show_default_value ) { ?>
		<div class="fes-form-rows">
			<label><?php _e( 'Default value', 'edd_fes' ); ?></label>
			<input type="text" class="smallipopInput" name="<?php echo $default_name; ?>" title="<?php esc_attr_e( 'The default value this field will have', 'edd_fes' ); ?>" value="<?php echo esc_html( $default_value); ?>" />
		</div>
		<?php } ?>
		<div class="fes-form-rows">
			<label><?php _e( 'Size', 'edd_fes' ); ?></label>
			<input type="text" class="smallipopInput" name="<?php echo $size_name; ?>" title="<?php esc_attr_e( 'Size of this input field', 'edd_fes' ); ?>" value="<?php echo esc_html( $size_value); ?>" />
		</div>
		<?php
	}

	/**
	 * Common fields for a textarea field.
	 *
	 * Contains items like size and placeholder of the textarea.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param  int        $id     Order number of the field in formbuilder.
	 * @param  array      $values Characteristics array from an FES Field object.
	 * @return void
	 */	
	public static function common_textarea( $id, $values = array() ) {
		$tpl = '%s[%d][%s]';
		$rows_name 			= sprintf( $tpl, 'fes_input', $id, 'rows' );
		$cols_name 			= sprintf( $tpl, 'fes_input', $id, 'cols' );
		$rich_name 			= sprintf( $tpl, 'fes_input', $id, 'rich' );
		$placeholder_name 	= sprintf( $tpl, 'fes_input', $id, 'placeholder' );
		$default_name 		= sprintf( $tpl, 'fes_input', $id, 'default' );
		$rows_value 		= $values && ! empty( $values['rows'] ) 	 ? esc_attr( $values['rows'] ) 		  : '5';
		$cols_value 		= $values && ! empty( $values['cols'] ) 	 ? esc_attr( $values['cols'] )  	  : '25';
		$rich_value 		= $values && ! empty( $values['rich'] ) 	 ? esc_attr( $values['rich'] )  	  : 'no';
		$placeholder_value  = $values && isset( $values['placeholder'] ) ? esc_attr( $values['placeholder'] ) : '';
		$default_value 		= $values && isset( $values['default'] ) 	 ? esc_attr( $values['default'] ) 	  : ''; ?>
		<div class="fes-form-rows">
			<label><?php _e( 'Rows', 'edd_fes' ); ?></label>
			<input type="text" class="smallipopInput" name="<?php echo esc_html( $rows_name); ?>" title="Number of rows in textarea" value="<?php echo esc_html( $rows_value); ?>" />
		</div>

		<div class="fes-form-rows">
			<label><?php _e( 'Columns', 'edd_fes' ); ?></label>
			<input type="text" class="smallipopInput" name="<?php echo esc_html( $cols_name); ?>" title="Number of columns in textarea" value="<?php echo esc_html( $cols_value); ?>" />
		</div>

		<div class="fes-form-rows">
			<label><?php _e( 'Placeholder text', 'edd_fes' ); ?></label>
			<input type="text" class="smallipopInput" name="<?php echo esc_html( $placeholder_name); ?>" title="text for HTML5 placeholder attribute" value="<?php echo esc_html( $placeholder_value); ?>" />
		</div>

		<div class="fes-form-rows">
			<label><?php _e( 'Default value', 'edd_fes' ); ?></label>
			<input type="text" class="smallipopInput" name="<?php echo esc_html( $default_name); ?>" title="the default value this field will have" value="<?php echo esc_html( $default_value); ?>" />
		</div>

		<div class="fes-form-rows">
			<label><?php _e( 'Textarea', 'edd_fes' ); ?></label>

			<div class="fes-form-sub-fields">
				<input type="radio" name="<?php echo $rich_name; ?>" value="no"<?php checked( $rich_value, 'no' ); ?>> <?php _e( 'Normal', 'edd_fes' ); ?>
				<input type="radio" name="<?php echo $rich_name; ?>" value="yes"<?php checked( $rich_value, 'yes' ); ?>> <?php _e( 'Rich textarea', 'edd_fes' ); ?>
				<input type="radio" name="<?php echo $rich_name; ?>" value="teeny"<?php checked( $rich_value, 'teeny' ); ?>> <?php _e( 'Teeny Rich textarea', 'edd_fes' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Hidden field helper function.
	 *
	 * Outputs a hidden field, which is commonly used in formbuilder fields
	 * to output things like the name (meta_key) of a field when it cannot
	 * be set.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param string  $name HTML name attribute for the hidden field.
	 * @param string  $value HTML value attribute for the hidden field.
	 * @return void
	 */
	public static function hidden_field( $name, $value = '' ) {
		printf( '<input type="hidden" name="%s" value="%s" />', 'fes_input' . esc_html( $name), esc_html( $value) );
	}

	/**
	 * Displays a radio custom field
	 *
	 * Makes a radio field display for use within a field's formbuilder
	 * template.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int     $field_id Order number of the field in formbuilder.
	 * @param string  $name
	 * @param array   $values Characteristics array from an FES Field object.
	 * @return void 
	 */
	public static function radio_fields( $field_id, $name, $values = array() ) {
		$selected_name 	= sprintf( '%s[%d][selected]', 'fes_input', $field_id );
		$input_name    	= sprintf( '%s[%d][%s]', 'fes_input', $field_id, $name );
		$selected_value = ( $values && isset( $values['selected'] ) ) ? $values['selected'] : '';
		$add 			= fes_assets_url .'img/add.png';
		$remove 		= fes_assets_url. 'img/remove.png';

		if ( $values && $values['options'] > 0 ) {
			foreach ( $values['options'] as $key => $value ) { ?>
				<div>
					<input type="radio" name="<?php echo esc_html( $selected_name) ?>" value="<?php echo esc_html( $value); ?>" <?php checked( $selected_value, $value ); ?>>
					<input type="text" name="<?php echo esc_html( $input_name); ?>[]" value="<?php echo esc_html( $value); ?>">
					<img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="fes-clone-field" src="<?php echo $add; ?>">
					<img style="cursor:pointer;" class="fes-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
				</div>
				<?php
			}
		} else { ?>
			<div>
				<input type="radio" name="<?php echo esc_html( $selected_name) ?>">
				<input type="text" name="<?php echo esc_html( $input_name); ?>[]" value="">
				<img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="fes-clone-field" src="<?php echo $add; ?>">
				<img style="cursor:pointer;" class="fes-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
			</div>
		<?php
		}
	}

	/**
	 * Displays a checkbox custom field
	 *
	 * Makes a checkbox field display for use within a field's formbuilder
	 * template.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int     $field_id Order number of the field in formbuilder.
	 * @param string  $name
	 * @param array   $values Characteristics array from an FES Field object.
	 * @return void 
	 */	
	public static function common_checkbox( $field_id, $name, $values = array() ) {
		$selected_name  = sprintf( '%s[%d][selected]', 'fes_input', $field_id );
		$input_name 	= sprintf( '%s[%d][%s]', 'fes_input', $field_id, $name );
		$selected_value = ( $values && isset( $values['selected'] ) ) ? $values['selected'] : array();
		$add 			= fes_assets_url .'img/add.png';
		$remove 		= fes_assets_url. 'img/remove.png';

		if ( $values && $values['options'] > 0 ) {
			foreach ( $values['options'] as $key => $value ) { ?>
				<div>
					<input type="checkbox" name="<?php echo esc_html( $selected_name) ?>[]" value="<?php echo esc_html( $value); ?>"<?php echo in_array( $value, $selected_value ) ? ' checked="checked"' : ''; ?> />
					<input type="text" name="<?php echo esc_html( $input_name); ?>[]" value="<?php echo esc_html( $value); ?>">
					<img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="fes-clone-field" src="<?php echo $add; ?>">
					<img style="cursor:pointer;" class="fes-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
				</div>
				<?php
			}
		} else { ?>
			<div>
				<input type="checkbox" name="<?php echo esc_html( $selected_name) ?>[]">
				<input type="text" name="<?php echo esc_html( $input_name); ?>[]" value="">
				<img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="fes-clone-field" src="<?php echo $add; ?>">
				<img style="cursor:pointer;" class="fes-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
			</div>
		<?php
		}
	}

	/**
	 * Field Div.
	 * 
	 * Wrapper div for a field's formbuilder metabox.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int     $index Index of field on formbuilder
	 * @param string  $name Name of the form.
	 * @param array   $characteristics Characteristics of the field
	 * @param bool    $insert Whether the field is being inserted.
	 * @return void
	 */
	public static function field_div( $index, $name, $characteristics, $insert = false ) {
		$open_by_default = apply_filters( 'fes_formbuilder_fields_open_by_default', false );
		if ( $insert || $open_by_default ){
			?><div class="fes-form-holder"><?php
		} else {
			?><div class="fes-form-holder" style="display: none;"><?php
		}
	}

	/**
	 * Show field on the frontend.
	 *
	 * The public attribute is used to determine
	 * whether or not a field's value is `public` and available
	 * for use in things like widgets and FES's automatic output.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @todo  Make this usable on profile and registration fields in 2.4.
	 * 
	 * @param int     $field_id Order number of the field in formbuilder.
	 * @param array   $values Characteristics array from an FES Field object.
	 * @param string  $name  Type of form the field is on.
	 * @param bool    $hidden Whether or not the public radio is hidden for a particular field.
	 * @return void
	 */
	public static function public_radio( $id, $values = array(), $name = 'login', $hidden = false ) {
		if ( $name !== 'submission' ) {
			return;
		}

		$tpl = '%s[%d][%s]';
		$field_name  = sprintf( $tpl, 'fes_input', $id, 'public' );
		$field_value = $values && isset( $values[ 'public' ] ) ? esc_attr( $values[ 'public' ] ) : false;
		if ( $field_value == 'on' ) {
			$field_value = true;
		}
		if ( $hidden ) { ?>
			<div class="fes-form-rows">
				<input type="hidden" id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, true ); ?> />
			</div>
			<?php
		} else { ?>
			<div class="fes-form-rows">
				<label><?php _e( 'Allow this field to be shown publicly', 'edd_fes' ); ?></label>
				<div class="fes-form-sub-fields">
					<label for="<?php esc_attr_e( $field_name ); ?>">
						<input type="checkbox" data-type="label" id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>" class="smallipopInput" <?php checked( $field_value, true ); ?>>
						<?php _e( "Append Public Fields: Selected fields on the Submission form will be output below the download's description.", 'edd_fes' ); ?>
					</label>
				</div>
			</div>
			<?php
		}
	}
}