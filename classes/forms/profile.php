<?php
class FES_Profile_Form extends FES_Form {

	/** @var string The form ID. */
	public $id = null;

	/** @var string Version of form */
	public $version = '1.0.0';		

	/** @var array Array of fields */
	public $fields = array();

	/** @var string The form's name (registration, contact etc). */
	public $name = 'profile';

	/** @var string Title of the form */
	public $title = 'Profile';

	/** @var int The id of the object the form value is saved to. For a submission form, the $save_id is the post's ID, etc. */
	public $save_id = null;

	/** @var unknown Type of form: 'user', 'post', 'custom'. Dictates where the fields save their values. */
	public $type = 'user';

	/** @var bool Whether or not entire form is readonly */
	public $readonly = false;

	/** @var array Array of things it supports */
	public $supports = array(
		'formbuilder' => array(
			'fields' => array(
				'public' => true, // can the fields be shown on the frontend publicly ( like on a download post ). Triggers public radio toggle on fields for backwards compat.
			),
			'settings' => array( // array of settings for the field

			),
			'notifications' => array(
				'supports' => array( // what type of notifications does this form support
					'sms'   => true,
					'email' => true, // pushover will hook in here to add notification type
				),
				'actions'  => array( // what actions can be used for triggering notifications?

				),
			)
		),
		'multiple' => false, // Whether or not multiples of a form type can be made
	);

	/** @var array Array of characteristics of the form that need to be stored in the database */
	public $characteristics = array();

	/** @var array Array of notifications for the form. */
	public $notifications = array();

	public function extending_constructor() {
		add_filter( 'fes_password_field_required', array( $this, 'fes_exclude_password_required' ), 10, 2 );
		add_filter( 'fes_templates_to_exclude_render_' . $this->name() . '_form_admin', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_save_' . $this->name() . '_form_admin', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_validate_' . $this->name() . '_form_admin', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_render_profile_form_args_admin', array( $this, 'fes_admin_submit_form_args' ), 10, 1 );
	}

	public function set_title() {
		$title = _x( 'Profile', 'FES Form title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_form_title', $this->title );
		$this->title = $title;		
	}	

	public function fes_admin_submit_form_args( $args ){
		if ( isset( $_REQUEST['id'] ) ){
			$vendor = new FES_DB_Vendors();
			$vendor = $vendor->get_vendor_by( 'id', intval( $_REQUEST['id'] ) ); 
			$args['vendor_id'] = $vendor->user_id;
		}
		return $args;
	}

	public function fes_templates_to_exclude( $templates ) {
		array_push( $templates, 'password' );
		array_push( $templates, 'user_login' );
		return $templates;
	}

	public function fes_exclude_user_pass_required( $required, $object ) {
		return false;
	}

	public function after_form_save_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$output['success'] = true;
		$output['title'] = __( 'Success', 'edd_fes' );
		$output['message'] = __( 'Profile Successfully Updated!', 'edd_fes' );
		$output['redirect_to'] = '#';

		do_action( 'fes_after_' . $this->name() . '_form_save_frontend_action', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_after_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function after_form_save_admin( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$output['success'] = true;
		$output['title'] = __( 'Success', 'edd_fes' );
		$output['message'] = __( 'Profile Successfully Updated!', 'edd_fes' );
		$output['redirect_to'] = '#';

		do_action( 'fes_after_' . $this->name() . '_form_save_admin_action', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_after_' . $this->name() . '_form_save_admin', $output, $save_id, $values, $user_id );
		return $output;
	}



	public function trigger_notifications_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {

	}

	public function trigger_notifications_admin( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {

	}

	public function can_render_form( $output = false, $is_admin = -2, $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		if ( $is_admin === -2 ) {
			if ( fes_is_admin() ) {
				$is_admin = true;
			}
			else {
				$is_admin = false;
			}
		}

		$is_a_vendor = EDD_FES()->vendors->user_is_vendor( $user_id );
		$is_a_admin  = EDD_FES()->vendors->user_is_admin( $user_id );

		if ( $is_admin ) {
			if ( !$is_a_admin ) {
				if ( $output ) {
					return __( 'Access Denied: You are not an admin', 'edd_fes' );
				} else {
					return false;
				}
			}
		} else {
			$user = $this->save_id;
			if ( !$is_a_admin && !$is_a_vendor && !( $is_a_vendor && $user == $user_id ) ) {
				if ( $output ) {
					return sprintf( _x( 'Access Denied: You are not an admin or the %s associated with this profile.', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
				} else {
					return false;
				}
			}
		}
		return true;
	}

	public function can_save_form( $output = false, $is_admin = -2, $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		if ( $is_admin === -2 ) {
			if ( fes_is_admin() ) {
				$is_admin = true;
			}
			else {
				$is_admin = false;
			}
		}

		$is_a_vendor = EDD_FES()->vendors->user_is_vendor( $user_id );
		$is_a_admin  = EDD_FES()->vendors->user_is_admin( $user_id );

		if ( $is_admin ) {
			if ( !$is_a_admin ) {
				if ( $output ) {
					return __( 'Access Denied: You are not an admin', 'edd_fes' );
				} else {
					return false;
				}
			}
		} else {
			$user = $this->save_id;
			if ( !$is_a_admin && !$is_a_vendor && !( $is_a_vendor && $user == $user_id ) ) {
				if ( $output ) {
					return sprintf( _x( 'Access Denied: You are not an admin or the %s associated with this profile.', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
				} else {
					return false;
				}
			}
		}
		return true;
	}
}
