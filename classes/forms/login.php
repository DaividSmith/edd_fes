<?php
class FES_Login_Form extends FES_Form {

	/** @var string The form ID. */
	public $id = null;

	/** @var string Version of form */
	public $version = '1.0.0';		

	/** @var array Array of fields */
	public $fields = array();

	/** @var string The form's name (registration, contact etc). */
	public $name = 'login';

	/** @var string Title of the form */
	public $title = 'Login';

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
		$title = _x( 'Login', 'FES Form title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_form_title', $this->title );
		$this->title = $title;		
	}	

	public function fes_templates_to_exclude( $templates ) {
		if ( ! EDD_FES()->helper->get_option( 'fes-login-captcha', false ) ) {
			array_push( $templates, 'recaptcha' );
		}
		return $templates;
	}

	public function before_form_error_check_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$username = '';
		$password = '';
		if ( !empty( $values['user_login'] ) ) {
			$username = $values['user_login'];
		} else {
			$output['message']     =  __( 'Please fill out the username field!', 'edd_fes' );
		}

		if ( !empty( $values['user_pass'] ) ) {
			$password = $values['user_pass'];
		} else {
			$output['message']     =  __( 'Please fill out the password field!', 'edd_fes' );
		}

		$user = get_user_by( 'login', $username );
		if ( $user &&  is_object( $user ) ) {
			$password = wp_check_password( $password, $user->data->user_pass, $user->ID );
			if ( $password ) {
				// pass validation
			} else {
				$output['message'] =  __( 'Password is wrong!', 'edd_fes' );
			}
		} else {
			$output['message'] =  __( 'Invalid username!', 'edd_fes' );
		}

		do_action( 'fes_before_' . $this->name() . '_form_error_check_action_frontend', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_before_' . $this->name() . '_form_error_check_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function after_form_save_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$username = '';
		$username = $values['user_login'];
		$user = get_user_by( 'login', $username );
		wp_set_auth_cookie( $user->ID, true );
		wp_set_current_user( $user->ID, $username );
		do_action( 'wp_login', $username, $user );	
		do_action( 'fes_login_form' );
		$url = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) );

		$output['success']     = true;
		$output['skipswal']    = true;
		$output['redirect_to'] = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) );
		$output['title']       = __( 'Success', 'edd_fes' );
		$output['message']     =  __( 'You\'ve been logged in!', 'edd_fes' );
		$output = apply_filters( 'fes_login_form_success_redirect', $output, $save_id, $this->id );

		do_action( 'fes_after_' . $this->name() . '_form_save_action_frontend', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_after_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}


	public function trigger_notifications_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {

	}

	public function trigger_notifications_admin( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {

	}

	public function can_render_form( $output = false, $is_admin = -2, $user_id = -2 ) {
		if ( is_user_logged_in() ) {
			if ( $output ) {
				return __( 'You\'re already logged in!', 'edd_fes' );
			} else {
				return false;
			}
		}
		return true;
	}

	public function can_save_form( $output = false, $is_admin = -2, $user_id = -2 ) {
		if ( is_user_logged_in() ) {
			if ( $output ) {
				return __( 'You\'re already logged in!', 'edd_fes' );
			} else {
				return false;
			}
		}
		return true;
	}
}
