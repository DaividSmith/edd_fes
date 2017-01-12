<?php
class FES_Toc_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';	
	
	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => true,
			'submission'       => true,
			'vendor-contact'   => false,
			'profile'          => true,
			'login'            => false,
		),
		'position'    => 'custom',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'   => 'toc',
		'title'       => 'Terms & Cond.',
		'phoenix'    => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'fes_accept_toc',
		'template'   => 'toc',
		'public'      => false,
		'required'    => true,
		'label'       => '',
		'css'         => '',
		'description' => '',
	);

	public function set_title() {
		$title = _x( 'Terms & Cond.', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	public function extending_constructor( ) {
		// exclude from render in admin
		add_filter( 'fes_templates_to_exclude_render_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_render_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_render_registration_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_render_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_render_vendor_contact_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		// exclude from sanitizing in admin
		add_filter( 'fes_templates_to_exclude_sanitize_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_sanitize_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_sanitize_registration_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_sanitize_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_sanitize_vendor_contact_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		// exclude from validating in admin
		add_filter( 'fes_templates_to_exclude_validate_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_registration_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_vendor_contact_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		// exclude from saving in admin
		add_filter( 'fes_templates_to_exclude_save_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_registration_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_vendor_contact_form_admin', array( $this, 'exclude_field' ), 10, 1  );				
	}

	public function exclude_field( $fields ) {
		array_push( $fields, 'toc' );
		return $fields;
	}	

	/** Returns the Toc to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		return '';
	}

	/** Returns the Toc to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$value = get_user_meta( $user_id, 'fes_accept_toc', true );

		if ( $value || $readonly ) {
			return '';
		}

		$user_id   = apply_filters( 'fes_render_toc_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_toc_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );

		$output    = '';
		$output   .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output   .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
			<span data-required="yes" data-type="radio"></span>
			<textarea rows="10" cols="40" disabled="disabled" name="toc"><?php echo $this->characteristics['description']; ?></textarea>
			<label>
				<input type="checkbox" name="fes_accept_toc" required="required" /> <?php echo $this->characteristics['checkbox_label']; ?>
			</label>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Toc to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable            = $this->can_remove_from_formbuilder();
		$title_name           = sprintf( '%s[%d][label]', 'fes_input', $index );
		$description_name     = sprintf( '%s[%d][description]', 'fes_input', $index );
		$checkbox_label_name  = sprintf( '%s[%d][checkbox_label]', 'fes_input', $index );
		$css_name             = sprintf( '%s[%d][css]', 'fes_input', $index );
		$title_value          = esc_attr( $this->get_label() );
		$description_value    = esc_attr( $this->characteristics['description'] );
		$checkbox_value       = esc_attr( $this->characteristics['checkbox_label'] );
		$css_value            = esc_attr( $this->css() ); ?>
		<li class="toc">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][name]", 'fes_accept_toc' ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<div class="fes-form-rows">
					<label><?php _e( 'Field Label', 'edd_fes' ); ?></label>
					<input type="text" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />
				</div>

				<div class="fes-form-rows">
					<label><?php _e( 'Terms & Conditions', 'edd_fes' ); ?></label>
					<textarea class="smallipopInput" title="<?php _e( 'Insert terms and condtions here.', 'edd_fes' ); ?>" name="<?php echo $description_name; ?>" rows="3"><?php echo esc_html( $description_value ); ?></textarea>
				</div>

				<div class="fes-form-rows">
					<label><?php _e( 'Checkbox Label', 'edd_fes' ); ?></label>
					<input type="text" name="<?php echo $checkbox_label_name; ?>" value="<?php echo esc_attr( $checkbox_value ); ?>" />
				</div>

				<div class="fes-form-rows">
					<label><?php _e( 'CSS Class Name', 'edd_fes' ); ?></label>
					<input type="text" name="<?php echo $css_name; ?>" value="<?php echo $css_value; ?>" class="smallipopInput" title="<?php _e( 'Add a CSS class name for this field', 'edd_fes' ); ?>">
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			// if the value is set
			// no specific validation
		} else {
			$value = get_user_meta( $user_id, 'fes_accept_toc', true );
			if ( !$value && !$this->readonly ) {
				return __( 'Please check this box', 'edd_fes' );
			}
		}
		return apply_filters( 'fes_validate_' . $this->template() . '_field', false, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = 1;
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}

	public function save_field_admin( $save_id = -2, $value = '', $user_id = -2 ) {
		update_user_meta( $user_id, 'fes_accept_toc', 'accepted' );
	}

	public function save_field_frontend( $save_id = -2, $value = '', $user_id = -2 ) {
		update_user_meta( $user_id, 'fes_accept_toc', 'accepted' );
	}		
}
