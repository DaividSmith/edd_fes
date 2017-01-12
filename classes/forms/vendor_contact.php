<?php
class FES_Vendor_Contact_Form extends FES_Form {

	/** @var string The form ID. */
	public $id = null;

	/** @var string Version of form */
	public $version = '1.0.0';		

	/** @var array Array of fields */
	public $fields = array();

	/** @var string The form's name (registration, contact etc). */
	public $name = 'vendor-contact';

	/** @var string Title of the form */
	public $title = 'Vendor Contact';

	/** @var int The id of the object the form value is saved to. For a submission form, the $save_id is the post's ID, etc. */
	public $save_id = null;

	/** @var unknown Type of form: 'user', 'post', 'custom'. Dictates where the fields save their values. */
	public $type = 'custom';

	/** @var bool Whether or not entire form is readonly */
	public $readonly = false;

	/** @var array Array of things it supports */
	public $supports = array(
		'multiple' => false, // Whether or not multiples of a form type can be made
	);

	/** @var array Array of characteristics of the form that need to be stored in the database */
	public $characteristics = array( );

	/** @var array Array of notifications for the form. */
	public $notifications = array();

	public function extending_constructor() {
		add_filter( 'fes_templates_to_exclude_render_' . $this->name() . '_form_frontend', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_save_' . $this->name() . '_form_frontend', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_validate_' . $this->name() . '_form_frontend', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_render_' . $this->name() . '_form_admin_form', '__return_false' );
	}

	public function set_title() {
		$title = _x( 'Vendor Contact', 'FES Form title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_form_title', $this->title );
		$this->title = $title;
	}

	public function fes_templates_to_exclude( $templates ) {
		if ( ! EDD_FES()->helper->get_option( 'fes-vendor-contact-captcha', false ) ) {
			array_push( $templates, 'recaptcha' );
		}
		return $templates;
	}

	public function before_form_error_check_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {

		$values['vendor_id'] = (int) $values['vendor_id'];

		if ( empty( $values['vendor_id'] ) ) {
			$output['message']     =  __( 'Invalid vendor id!', 'edd_fes' );
		} else {
			$values['vendor_id'] = absint( $values['vendor_id'] );

			$vendor_user = new FES_Vendor( $values['vendor_id'] );
			if ( ! is_object( $vendor_user ) ) {
				$output['message']     =  __( 'Invalid vendor id!', 'edd_fes' );
			}
		}

		do_action( 'fes_before_' . $this->name() . '_form_error_check_action_frontend', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_before_' . $this->name() . '_form_error_check_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function after_form_save_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$output['success']     = true;
		$output['redirect_to'] = '#';
		$output['message']     =  __( 'Email sent!', 'edd_fes' );

		do_action( 'fes_after_' . $this->name() . '_form_save_action_frontend', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_after_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function trigger_notifications_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {

		$vendor = new FES_Vendor( $save_id );
		$to 		 = get_user_meta( $vendor->user_id, 'email_to_use_for_contact_form', true );
		if ( ! $to ) {
			$to 	 = $vendor->email;
		}
		$to          = apply_filters( 'fes_vendor_contact_form_to', $to, $values );
		$from_name   = $values['name'];
		$from_email  = $values['email'];
		$subject     = apply_filters( 'fes_vendor_contact_form_subject', sprintf( __( 'Message from %s user', 'edd_fes' ), site_url() ), $save_id, $values, $user_id );
		$message     = apply_filters( 'fes_vendor_contact_form_message_opener', sprintf( __( 'From %s (%s): <br />', 'edd_fes' ), $from_name, $from_email ), $save_id, $values, $user_id );
		$message    .= $values['message'];
		EDD_FES()->emails->send_email( $to, $from_name, $from_email, $subject, $message, 'user', $save_id, array() );
	}

	public function can_render_form( $output = false, $is_admin = -2, $user_id = -2 ) {
		return true; // all can use the form
	}

	public function can_save_form( $output = false, $is_admin = -2, $user_id = -2 ) {
		return true; // all can use the form
	}

	public function get_submit_button_defaults( $form, $args ) {

		// extend in classes that need it
		$default = array(
			'user_id'   => get_current_user_id(),
			'page_id'   => 0,
			'form_id'   => $this->id,
			'vendor_id' => $this->save_id,
			'action'    => 'fes-submit-' . $this->name() . '-form'
		);
		$default[ 'action' ] = fes_dash_to_lower( $default[ 'action' ] );

		return $default;
	}
}
