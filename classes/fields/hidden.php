<?php
class FES_Hidden_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';	
	
	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => true,
			'submission'       => true,
			'vendor-contact'   => true,
			'profile'          => true,
			'login'            => true,
		),
		'position'    => 'custom',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'   => 'hidden',
		'title'       => 'Hidden',
		'phoenix'    => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'   => 'hidden',
		'public'      => false,
		'required'    => false,
		'label'       => '',
		'meta_value'  => '',
	);


	public function set_title() {
		$title = _x( 'Hidden', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the Hidden to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		if ( $readonly ) {
			return '';
		}

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		ob_start(); ?>
		<div class="fes-fields">
			<?php
		$name       = $this->name();
		$meta_value = $this->characteristics['meta_value'];
		printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $name ), esc_attr( $meta_value ) );
		echo "\r\n"; ?>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Hidden to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		if ( $readonly ) {
			return '';
		}

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		ob_start(); ?>
		<div class="fes-fields">
			<?php
		$name       = $this->name();
		$meta_value = $this->characteristics['meta_value'];
		printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $name ), esc_attr( $meta_value ) );
		echo "\r\n"; ?>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Hidden to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$meta_name    = sprintf( '%s[%d][name]', 'fes_input', $index );
		$value_name   = sprintf( '%s[%d][meta_value]', 'fes_input', $index );
		$label_name   = sprintf( '%s[%d][label]', 'fes_input', $index );
		$meta_value   = esc_attr( $this->name() );
		$value_value  = esc_attr( $this->characteristics['meta_value'] );
		ob_start(); ?>
		<li class="custom-field custom_hidden_field">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<div class="fes-form-rows">
					<label><?php _e( 'Meta Key', 'edd_fes' ); ?></label>
					<input type="text" name="<?php echo esc_html( $meta_name); ?>" value="<?php echo esc_html( $meta_value); ?>" class="smallipopInput" title="<?php _e( 'Name of the meta key this field will save to', 'edd_fes' ); ?>">
					<input type="hidden" name="<?php echo $label_name; ?>" value="">
				</div>

				<div class="fes-form-rows">
					<label><?php _e( 'Meta Value', 'edd_fes' ); ?></label>
					<input type="text" class="smallipopInput" title="<?php esc_attr_e( 'Enter the meta value', 'edd_fes' ); ?>" name="<?php echo esc_html( $value_name ); ?>" value="<?php echo esc_html( $value_value ); ?>">
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;
		if ( !empty( $values[ $name ] ) ) {
			// no specific validation
		} else {
			$name = $this->name();
			$values[ $name ] = isset( $this->characteristics['meta_value'] ) ? $this->characteristics['meta_value'] : '';
		}
		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$values[ $name ] = isset( $this->characteristics['meta_value'] ) ? $this->characteristics['meta_value'] : '';
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}
}
