<?php
class FES_Recaptcha_Field extends FES_Field {
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
			'field_always_required'    => true,
		),
		'template'   => 'recaptcha',
		'title'       => 'reCAPTCHA',
		'phoenix'    => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'recaptcha',
		'template'   => 'recaptcha',
		'public'      => false,
		'required'    => true,
		'label'       => '',
		'html'     => '',
	);

	public function set_title() {
		$title = _x( 'reCAPTCHA', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	public function extending_constructor( ) {
		// exclude from submission form in admin
		add_filter( 'fes_templates_to_exclude_render_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		// exclude from profile form in admin
		add_filter( 'fes_templates_to_exclude_render_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		// exclude from registration form in admin
		add_filter( 'fes_templates_to_exclude_render_registration_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_registration_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_registration_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		// exclude from submission form in admin
		add_filter( 'fes_templates_to_exclude_render_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_profile_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		// exclude from vendor_contact form in admin
		add_filter( 'fes_templates_to_exclude_render_vendor_contact_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_vendor_contact_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_vendor_contact_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		// exclude from vendor_contact form in frontend
		add_filter( 'fes_templates_to_exclude_render_vendor_contact_form_frontend', array( $this, 'exclude_from_vendor_contact' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_vendor_contact_form_frontend', array( $this, 'exclude_from_vendor_contact' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_vendor_contact_form_frontend', array( $this, 'exclude_from_vendor_contact' ), 10, 1  );

		// exclude from login form in admin
		add_filter( 'fes_templates_to_exclude_render_login_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_login_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_login_form_admin', array( $this, 'exclude_field' ), 10, 1  );

		// exclude from login form in frontend
		add_filter( 'fes_templates_to_exclude_render_login_form_frontend', array( $this, 'exclude_from_login' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_login_form_frontend', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_login_form_frontend', array( $this, 'exclude_from_login' ), 10, 1  );
	}

	public function exclude_field( $fields ) {
		array_push( $fields, 'recaptcha' );
		return $fields;
	}

	public function exclude_from_login( $fields ) {
		$public_key          = EDD_FES()->helper->get_option( 'fes-recaptcha-public-key', '' );
		$private_key           = EDD_FES()->helper->get_option( 'fes-recaptcha-private-key', '' );
		$enabled_login         = (bool) EDD_FES()->helper->get_option( 'fes-login-captcha', false );
		if ( $public_key == '' || $private_key == '' || !$enabled_login ) {
			array_push( $fields, 'recaptcha' );
		}
		return $fields;
	}

	public function exclude_from_vendor_contact( $fields ) {
		$public_key          = EDD_FES()->helper->get_option( 'fes-recaptcha-public-key', '' );
		$private_key           = EDD_FES()->helper->get_option( 'fes-recaptcha-private-key', '' );
		$enabled_login         = EDD_FES()->helper->get_option( 'fes-vendor-contact-captcha', false );
		if ( $public_key == '' || $private_key == '' || !$enabled_login ) {
			array_push( $fields, 'recaptcha' );
		}
		return $fields;
	}


	/** Returns the Recaptcha to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		// we don't render reCAPTCHA in the backend
		return '';
	}

	/** Returns the Recaptcha to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		$public_key      = EDD_FES()->helper->get_option( 'fes-recaptcha-public-key', '' );
		$private_key     = EDD_FES()->helper->get_option( 'fes-recaptcha-private-key', '' );
		$theme       = apply_filters( 'fes_render_recaptcha_field_frontend_theme', 'light' ); // The color theme of the widget. Either dark or light
		$type       = apply_filters( 'fes_render_recaptcha_field_frontend_type', 'image' ); // The type of CAPTCHA to serve. Either audio or image
		$size        = apply_filters( 'fes_render_recaptcha_field_frontend_size', 'normal' ); // The size of the widget. Either compact  or normal
		if ( $public_key == '' || $private_key == '' ) {
			return '';
		}

		if ( $readonly ) {
			return '';
		}
		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output        .= $this->label( $readonly );
		$prefix   = is_ssl() ? "https" : "http";
		$url      = $prefix . '://www.google.com/recaptcha/api.js';
		ob_start(); ?>
		<div class="fes-fields">
			<?php wp_enqueue_script( 'recaptcha', $url ); ?>
			<div class="g-recaptcha" data-sitekey="<?php echo $public_key; ?>" data-theme="<?php echo $theme; ?>" data-type="<?php echo $type; ?>" data-size="<?php echo $size; ?>"></div>
			<noscript>
			  <div style="width: 302px; height: 422px;">
				<div style="width: 302px; height: 422px; position: relative;">
				  <div style="width: 302px; height: 422px; position: absolute;">
					<iframe src="https://www.google.com/recaptcha/api/fallback?k=<?php echo $public_key; ?>"
							frameborder="0" scrolling="no"
							style="width: 302px; height:422px; border-style: none;">
					</iframe>
				  </div>
				  <div style="width: 300px; height: 60px; border-style: none;
							  bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;
							  background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
					<textarea id="g-recaptcha-response" name="g-recaptcha-response"
							  class="g-recaptcha-response"
							  style="width: 250px; height: 40px; border: 1px solid #c1c1c1;
									 margin: 10px 25px; padding: 0px; resize: none;" >
					</textarea>
				  </div>
				</div>
			  </div>
			</noscript>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Recaptcha to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
?>
		<li class="recaptcha">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<div class="fes-form-rows">
					<label><b><?php _e( 'Important:', 'edd_fes' ); ?></b></label>

					<div class="fes-form-sub-fields">

						<div class="description" style="margin-top: 8px;">
							<?php _e( "In order for reCAPTCHA to work you must insert your site key and private key in the FES settings panel. <a href='https://www.google.com/recaptcha/admin#list' target='_blank'>Create a key</a> first if you don't have any keys.", 'edd_fes' ); ?>
						</div>
					</div>
				</div>
				<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
				<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	/** Validates field */
	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;

		if ( $this->readonly ) {
			return false;
		}

		if ( !empty( $values[ $name ] ) ) {
			$recap_challenge = isset( $values[ 'g-recaptcha-response' ] ) ? $values[ 'g-recaptcha-response' ] : '';
			$private_key     = EDD_FES()->helper->get_option( 'fes-recaptcha-private-key', '' );
			try {
				$prefix   = is_ssl() ? "https" : "http";
				$url      = $prefix . '://www.google.com/recaptcha/api/siteverify';
				$data     = array( 'secret' => $private_key, 'response' => $recap_challenge, 'remoteip' => $_SERVER['REMOTE_ADDR'] );
				$options  = array( 'http' => array( 'header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query( $data ) ) );
				$context  = stream_context_create( $options );
				$result   = file_get_contents( $url, false, $context );
				if ( json_decode( $result )->success == false ) {
					$return_value = __( 'reCAPTCHA validation failed.', 'edd_fes' );
				}
			}
			catch ( Exception $e ) {
				$return_value = __( 'reCAPTCHA validation failed', 'edd_fes' );
			}
		} else {
			// if the field is required but isn't present
			if ( $this->required() ) {
				$return_value = __( 'Please fill out this field.', 'edd_fes' );
			}
		}
		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ 'g-recaptcha-response' ] ) ) {
			$values[ $name ] = trim( $values[ 'g-recaptcha-response' ] );
			$values[ $name ] = sanitize_text_field( $values[ $name ] );
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
