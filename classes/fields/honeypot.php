<?php
class FES_Honeypot_Field extends FES_Field {
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
			'vendor-contact'   => true,
			'profile'          => true,
			'login'            => true,
		),
		'position'    => 'custom',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => false,
			'can_add_to_formbuilder'      => true,
		),
		'template'   => 'honeypot',
		'title'       => 'Honeypot',
		'phoenix'   => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'honeypot',
		'template'   => 'honeypot',
		'public'      => false,
		'required'    => false,
		'label'       => '',
		'description' => '',
		'css'     => '',
	);


	public function set_title() {
		$title = _x( 'Honeypot', 'FES Field title translation', 'edd_fes' );
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

		// exclude from saving in frontend
		add_filter( 'fes_templates_to_exclude_save_submission_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_profile_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_registration_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_profile_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_vendor_contact_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
	}

	public function exclude_field( $fields ) {
		array_push( $fields, 'honeypot' );
		return $fields;
	}


	/** don't renter in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		return '';
	}

	/** Returns the Honeypot to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		ob_start(); ?>
			<input type="hidden" name="honeypot" value=""/>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Honeypot to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="custom-field honeypot">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php _e( 'There are no settings required for this field', 'edd_fes' ); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	/** Validates field */
	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			return __( 'Nice try Mr. Spammer, don\'t touch our honey', 'edd_fes' );
		}
		return apply_filters( 'fes_validate_' . $this->template() . '_field', false, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = '1';
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}

	public function get_field_value_admin( $save_id = -2, $user_id = -2, $public = -2 ) {
		return ''; // don't get field value
	}

	public function get_field_value_frontend( $save_id = -2, $user_id = -2, $public = -2 ) {
		return ''; // don't get field value
	}

	public function save_field_admin( $save_id = -2, $value = array(), $user_id = -2 ) {
		// don't save field value
	}

	public function save_field_frontend( $save_id = -2, $value = array(), $user_id = -2 ) {
		// don't save field value
	}
}
