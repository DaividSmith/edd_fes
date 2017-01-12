<?php
class FES_Country_Field extends FES_Field {
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
		'template'   => 'country',
		'title'       => 'Country',
		'phoenix'   => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two country fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'   => 'country',
		'public'      => true,
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'options'     => array(),
		'first'       => ' - select -',
	);


	public function set_title() {
		$title = _x( 'Country', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_country_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_country_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_admin( $this->save_id, $user_id, $readonly );

		if ( $this->save_id ) {
			$selected = $this->get_meta( $this->save_id, $this->name(), $this->type  );
			$selected = $selected;
		} else {
			$selected = $this->characteristics['selected'];
			$selected = $selected;
		}

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );

		$data_type = 'select';
		$css       = '';
		ob_start(); ?>
		<div class="fes-fields">

			<select<?php echo $css; ?> name="<?php echo $this->name(); ?>[]" data-required="false" data-type="<?php echo $data_type; ?>">
				<?php if ( !empty( $this->characteristics['first'] ) ) { ?>
					<option value=""><?php echo $this->characteristics['first']; ?></option>
				<?php }
					if ( $this->characteristics['options'] && count( $this->characteristics['options'] ) > 0 ) {
						foreach ( $this->characteristics['options'] as $option ) {
							$current_select = selected( $selected, $option, false ); ?>
							<option value="<?php echo esc_attr( $option ); ?>"<?php echo $current_select; ?>><?php echo $option; ?></option><?php
						}
					} ?>
			</select>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_country_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_country_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$required  = $this->required( $readonly );

		if ( $this->save_id ) {
			$selected = $this->get_meta( $this->save_id, $this->name(), $this->type );
			$selected = $selected;
		} else {
			$selected = $this->characteristics['selected'];
			$selected = $selected;
		}
		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );

		$data_type = 'select';
		$css       = '';
		ob_start(); ?>
		<div class="fes-fields">

			<select<?php echo $css; ?> name="<?php echo $this->name(); ?>[]" data-required="<?php echo $required; ?>" data-type="<?php echo $data_type; ?>"<?php $this->required_html5( $readonly ); ?>>
				<?php if ( !empty( $this->characteristics['first'] ) ) { ?>
					<option value=""><?php echo $this->characteristics['first']; ?></option>
				<?php }
					if ( $this->characteristics['options'] && count( $this->characteristics['options'] ) > 0 ) {
						foreach ( $this->characteristics['options'] as $option ) {
							$current_select = selected( $selected, $option, false ); ?>
							<option value="<?php echo esc_attr( $option ); ?>"<?php echo $current_select; ?>><?php echo $option; ?></option><?php
						}
					} ?>
			</select>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		$first_name = sprintf( '%s[%d][first]', 'fes_input', $index );
		$first_value = $this->characteristics['first'];
		$values['options'] = empty( $this->characteristics['options'] ) ? edd_get_country_list() : $this->characteristics['options'];
		$values['label']   = $this->get_label() ? __( 'Vendor Country', 'edd_fes' ) : $this->get_label();
		$values['name']    = $this->name();
		$help = esc_attr( __( 'First element of the select dropdown. Leave this empty if you don\'t want to show this field', 'edd_fes' ) );
		ob_start(); ?>
		<li class="custom-field country">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
				<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>

				<div class="fes-form-rows">
					<label><?php _e( 'Select Text', 'edd_fes' ); ?></label>
					<input type="text" class="smallipopInput" name="<?php echo esc_html( $first_name ); ?>" value="<?php echo esc_html( $first_value ); ?>" title="<?php echo esc_html( $help ); ?>">
				</div>

				<div class="fes-form-rows">
					<label><?php _e( 'Countries', 'edd_fes' ); ?></label>

					<div class="fes-form-sub-fields">
						<?php FES_Formbuilder_Templates::radio_fields( $index, 'options', $values ); ?>
					</div>
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name         = $this->name();
		$return_value = false;
		if ( !empty( $values[ $name ] ) ) {
			// if the value is set

		} else {
			// if required but isn't present
			if ( $this->required() ) {
				$return_value = __( 'Please fill out this field.', 'edd_fes' );
			}
		}
		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ][0] ) ) {
			$values[ $name ] = trim( $values[ $name ][0] );
			$values[ $name ] = sanitize_text_field( $values[ $name ] );
		} else if ( isset( $values[ $name ][0] ) ){
			$values[ $name ] = '';
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}
}
