<?php
class FES_Registration_Form extends FES_Form {

	/** @var string The form ID. */
	public $id = null;

	/** @var string Version of form */
	public $version = '1.0.0';		

	/** @var array Array of fields */
	public $fields = array();

	/** @var string The form's name (registration, contact etc). */
	public $name = 'registration';

	/** @var string Title of the form */
	public $title = 'Registration';

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
	public $characteristics = array( );

	/** @var array Array of notifications for the form. */
	public $notifications = array();

	public function extending_constructor() {
		add_filter( 'fes_templates_to_exclude_render_' . $this->name() . '_form_frontend', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_save_' . $this->name() . '_form_frontend', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_validate_' . $this->name() . '_form_frontend', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_render_' . $this->name() . '_form_admin', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_save_' . $this->name() . '_form_admin', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_validate_' . $this->name() . '_form_admin', array( $this, 'fes_templates_to_exclude' ) );		
		add_filter( 'fes_render_registration_form_args_admin', array( $this, 'fes_admin_submit_form_args' ), 10, 1 );
	}

	public function set_title() {
		$title = _x( 'Registration', 'FES Form title translation', 'edd_fes' );
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
		if ( is_user_logged_in() && !fes_is_admin() ) {
			array_push( $templates, 'password' );
			array_push( $templates, 'user_login' );
			array_push( $templates, 'user_email' );
		} else if ( fes_is_admin() ){
			array_push( $templates, 'password' );
			array_push( $templates, 'user_login' );
		}
		return $templates;
	}

	// prevalidate that any username and email picked is not already being used
	public function before_form_error_check_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		// if the user is not already logged in
		if ( !is_user_logged_in() ) {
			$username = '';
			$email = '';
			if ( !empty( $values['user_login'] ) ) {
				$username = $values['user_login'];
			} else {
				$output['message']     =  __( 'Please fill out the username field!', 'edd_fes' );
				return $output;
			}

			if ( !empty( $values['user_email'] ) ) {
				$email = $values['user_email'];
			} else {
				$output['message']     =  __( 'Please fill out the email field!', 'edd_fes' );
				return $output;
			}

			$user_by_username = get_user_by( 'login', $username );
			$user_by_email    = get_user_by( 'email', $email );
			$login_link       = add_query_arg( array( 'view' => 'login' ), get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) ) );

			if ( $user_by_username &&  is_object( $user_by_username ) ) {
				$output['message']     =  sprintf( _x( 'This username is already in use! Pick a different username or click %shere%s to login.', 'login link is the %s', 'edd_fes' ), '<a href="'. $login_link . '">', '</a>' );
				return $output;
			}

			if ( $user_by_email &&  is_object( $user_by_email ) ) {
				$output['message']     =  sprintf( _x( 'This email address is already in use! Pick a different username or click %shere%s to login.', 'login link is the %s', 'edd_fes' ), '<a href="'. $login_link . '">', '</a>' );
				return $output;
			}
		}

		if ( is_user_logged_in() ) {
			if ( !(bool) EDD_FES()->helper->get_option( 'fes-allow-applications', false ) ) {
				$output['message']     =  __( 'Sorry! Applications to become a vendor are currently disabled at this time!', 'edd_fes' );
			}
		} else {
			if ( !(bool) EDD_FES()->helper->get_option( 'fes-allow-registrations', false ) ) {
				$output['message']     =  __( 'Sorry! Registration is currently disabled at this time!', 'edd_fes' );
			}
		}

		do_action( 'fes_before_' . $this->name() . '_form_error_check_action_frontend', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_before_' . $this->name() . '_form_error_check_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}


	public function render_form_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		EDD_FES()->setup->enqueue_form_assets();

		$readonly = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_readonly', $readonly, $this, $user_id );
		$user_id  = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_user_id', $user_id, $this );
		$form     = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_form', true, $this );
		$allow_registrations = (bool) EDD_FES()->helper->get_option( 'fes-allow-registrations', false ); // allow new users to become vendors
		$allow_applications  = (bool) EDD_FES()->helper->get_option( 'fes-allow-applications', false ); // allow existing users to become vendors
		$is_pending = (bool) EDD_FES()->vendors->user_is_status( 'pending', $user_id ); // user is pending vendor
		$is_suspended = (bool) EDD_FES()->vendors->user_is_status( 'suspended', $user_id ); // user is pending vendor
		$is_vendor = (bool) EDD_FES()->vendors->user_is_vendor( $user_id ); // user is vendor
		$logged_in = is_user_logged_in();
		$output = '';
		if ( $logged_in && $is_vendor ) {
			// if we are a vendor, but not in the backend, don't show this form
		} else if ( $logged_in && $is_pending ) {
			// if we are pending vendor but not in the backend
			$output .= '<div class="fes-vendor-pending fes-info">';
			$output .= apply_filters( 'fes_application_pending_message', __( 'Your application is pending', 'edd_fes' ) );
			$output .= '</div>';
		} else if ( $logged_in && $is_suspended ) {
			// if we are suspended vendor but not in the backend
			$output .= '<div class="fes-vendor-suspended fes-info">';
			$output .= apply_filters( 'fes_application_suspended_message', __( 'You are currently suspended', 'edd_fes' ) );
			$output .= '</div>';
		} else if ( $logged_in && !$allow_applications ) {
			// if existing user wants to become a vendor, and applications are off, and not in backend
			$output .= '<div class="fes-info">';
			$output .=  __( 'Vendor applications are currently closed', 'edd_fes' );
			$output .= '</div>';
		} else if ( !$logged_in && !$allow_registrations && $allow_applications ) {
			// if user is logged out and we allow applications, tell them they can login and apply
			$output .= '<div class="fes-info">';
			$output .=  __( 'Vendor registration is currently closed. If you have an existing account, you may login and apply to become a vendor.', 'edd_fes' );
			$output .= '</div>';
		} else if ( !$logged_in && !$allow_registrations && !$allow_applications ) {
			// if user is logged out and we allow applications, tell them they can login and apply
			$output .= '<div class="fes-info">';
			$output .=  __( 'Vendor registration is currently closed.', 'edd_fes' );
			$output .= '</div>';
		} else {
			$output = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_output_before_fields', $output, $this, $user_id, $readonly );
			do_action( 'fes_render_' . $this->name() . '_form_frontend_before_fields', $this, $user_id, $readonly );

			$fields = $this->fields;
			$fields = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_fields', $fields, $this, $user_id, $readonly );

			$count = 0;
			foreach ( $fields as $field ) {
				$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_render_' . $this->name() . '_form_frontend', array() );
				if ( is_object( $field ) && is_array( $templates_to_exclude ) && in_array( $field->template(), $templates_to_exclude ) ) {
					continue;
				} else {
					$count++;
				}
			}

			if ( !empty( $fields ) && $count > 0 ) {
				if ( !$readonly && $form ) {
					$output .= '<form class="fes-ajax-form fes-' . $this->name() . '-form" action="" name="fes-' . $this->name() . '-form" method="post">';
				}
				$output .= '<div class="fes-form fes-' . $this->name() . '-form-div">';

				foreach ( $fields as $field ) {
					if ( ! is_object( $field ) ) {
						continue;
					}
					$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_render_' . $this->name() . '_form_frontend', array() );
					if ( is_array( $templates_to_exclude ) && in_array( $field->supports['template'], $templates_to_exclude ) ) {
						continue;
					}
					$output .= apply_filters( 'fes_render_' . $this->name() . '_form_frontend_fields_before_field', '', $field, $this, $user_id, $readonly );
					$output .= $field->render_field_frontend( $user_id, $readonly );
					$output .= apply_filters( 'fes_render_' . $this->name() . '_form_frontend_fields_after_field', '', $field, $this, $user_id, $readonly );
				}

				if ( !$readonly ) {
					$label     = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_submit_button_label', '', $this, $user_id );
					$show_args = apply_filters( 'fes_render_' . $this->name() . '_form_show_args_frontend', true, $this, $user_id, $readonly );
					$args      = apply_filters( 'fes_render_' . $this->name() . '_form_args_frontend', array(), $this, $user_id, $readonly );
					$output   .= $this->submit_button( $label, $form, $show_args, $args );
				}

				$output .= '</div>';

				if ( !$readonly && $form ) {
					$output .= '</form>';
				}
			} else {
				$output .= __( 'The form has no fields!', 'edd_fes' );
			}

			do_action( 'fes_render_' . $this->name() . '_form_frontend_after_fields', $this, $user_id, $readonly );
			$output = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_output_after_fields', $output, $this, $user_id, $readonly );
		}
		return $output;
	}

	// login the user on the frontend if they aren't already logged in
	public function before_form_save_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {

		// if the user is not already logged in
		if ( !is_user_logged_in() ) {
			$user_id  = $this->create_user( $values );
			$vendor   = new FES_Vendor( $user_id, true );
			$this->change_save_id( $vendor->user_id );

			// login new user
			wp_set_auth_cookie( $user_id, true );
			wp_set_current_user( $user_id, $vendor->username );
			$user = get_user_by( 'login', $vendor->username );
			do_action( 'wp_login', $vendor->username, $user );
		} else {
			$user_id = $this->create_vendor( $save_id );
			$vendor  = new FES_Vendor( $user_id, true );
			$this->change_save_id( $vendor->user_id );
		}

		do_action( 'fes_before_' . $this->name() . '_form_save_action_frontend', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_before_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function after_form_save_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		// if auto approved
		if ( $save_id === -2 || $save_id < 1 ) {
			$save_id = $this->save_id;
		}

		$redirect_to = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) );
		$output      = array();

		// if autoapproved
		if ( (bool) EDD_FES()->helper->get_option( 'fes-auto-approve-vendors', false ) ) {

			$vendor = new FES_Vendor( $save_id, true );

			$vendor->change_status( 'approved', false );

			// redirect to dashboard
			$response = array(
				'redirect_to' => $redirect_to,
				'title' => __( 'Success!', 'edd_fes' ),
				'message' => __( 'Your Application has been Approved!', 'edd_fes' ),
				'success' => true,
			);

			$output = apply_filters( 'fes_register_form_frontend_vendor', $response, $user_id, $values );

			do_action( 'fes_registration_form_frontend_vendor', $vendor->user_id, $values );
			// else pending vendor
		} else {

			$vendor = new FES_Vendor( $save_id, true );

			// redirect to app under view
			$response = array(
				'redirect_to' => $redirect_to,
				'title' => __( 'Success!', 'edd_fes' ),
				'message' => __( 'Application has been submitted!', 'edd_fes' ),
				'success' => true,
			);
			$output = apply_filters( 'fes_register_form_pending_vendor', $response, $vendor->user_id, $values );

			do_action ( 'fes_registration_form_pending_vendor', $user_id, $values );			
		}
		do_action( 'fes_after_' . $this->name() . '_form_save_action', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_after_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function after_form_save_admin( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$output['success'] = true;
		$output['title'] = __( 'Success', 'edd_fes' );
		$output['message'] = __( 'Application Successfully Updated!', 'edd_fes' );
		$output['redirect_to'] = '#';

		do_action( 'fes_after_' . $this->name() . '_form_save_admin_action', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_after_' . $this->name() . '_form_save_admin', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function create_user( $values = array() ) {
		$save_id = 0;
		$userdata = array();

		if ( isset( $values['first_name'] ) ) {
			$userdata['first_name'] = $values['first_name'];
		}

		if ( isset( $values['last_name'] ) ) {
			$userdata['last_name'] = $values['last_name'];
		}

		if ( isset( $values['user_login'] ) ) {
			$userdata['user_login'] = $values['user_login'];
		} else {
			return 0;
		}

		$send_pass = false;

		if ( isset( $values['user_pass'] ) ) {
			$userdata['user_pass'] = $values['user_pass'];
		} else {
			$userdata['user_pass'] = wp_generate_password();
			$send_pass = true;
		}

		if ( isset( $values['user_email'] ) ) {
			$userdata['user_email'] = $values['user_email'];
		} else {
			return 0;
		}

		if ( isset( $values['display_name'] ) ) {
			$userdata['display_name'] = $values['display_name'];
		}

		if ( isset( $values['user_url'] ) ) {
			$userdata['user_url'] = $values['user_url'];
		}

		if ( isset( $values['user_bio'] ) ) {
			$userdata['description'] = $values['user_bio'];
		}

		$userdata['role'] = 'subscriber';
		$userdata['user_registered'] = date( 'Y-m-d H:i:s' );

		$save_id = wp_insert_user( $userdata );
		$user    = new WP_User( $save_id );
		wp_new_user_notification( $save_id );

		$db_user = new FES_DB_Vendors();

		if ( !$db_user->exists( 'email', $user->user_email ) ) {
			$db_user->add( array(
					'user_id'        => $user->ID,
					'email'          => $user->user_email,
					'username'       => $user->user_login,
					'name'           => $user->display_name,
					'product_count'  => 0,
					'status'         => 'pending',
				) );
		}

		return $save_id;
	}

	public function create_vendor( $save_id = -2 ) {
		$user    = new WP_User( $save_id );
		$db_user = new FES_DB_Vendors();

		if ( !$db_user->exists( 'email', $user->user_email ) ) {
			$db_user->add( array(
					'user_id'        => $user->ID,
					'email'          => $user->user_email,
					'username'       => $user->user_login,
					'name'           => $user->display_name,
					'product_count'  => 0,
					'status'         => 'pending',
				) );
		}

		return $save_id;
	}

	public function trigger_notifications_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$user    = new WP_User( $save_id );
		if ( ! isset( $values['user_email'] ) ) {
			$values['user_email'] = $user->user_email;
		}
		// if auto approved
		if ( (bool)EDD_FES()->helper->get_option( 'fes-auto-approve-vendors', false ) ) {
			// email user
			$to = apply_filters( 'fes_registration_form_frontend_vendor_to_vendor', $values['user_email'], $values );
			$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
			$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
			$subject = apply_filters( 'fes_registration_form_to_vendor_accepted_subject', __( 'Application Approved', 'edd_fes' ) );
			$message = EDD_FES()->helper->get_option( 'fes-vendor-new-auto-vendor-email', '' );
			$type = "user";
			$id = $save_id;
			$args = array( 'permissions' => 'fes-vendor-new-auto-vendor-email-toggle' );

			EDD_FES()->emails->send_email( $to, $from_name, $from_email, $subject, $message, $type, $id );

			do_action ( 'fes_register_form_frontend_autoapproved_vendor_email', $save_id, $values );
			// else pending vendor
		} else {
			// email admin
			$to = apply_filters( 'fes_registration_form_pending_vendor_to_admin', edd_get_admin_notice_emails(), $values );
			$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
			$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
			$subject = apply_filters( 'fes_registration_form_to_admin_subject', __( 'New Vendor Application Received', 'edd_fes' ) );
			$message = EDD_FES()->helper->get_option( 'fes-admin-new-app-email', '' );
			$type = "user";
			$id = $save_id;
			$args = array( 'permissions' => 'fes-admin-new-app-email-toggle' );
			EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id );

			// email user
			$to = apply_filters( 'fes_registration_form_pending_vendor_to_vendor', $values['user_email'], $values );
			$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
			$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
			$subject = apply_filters( 'fes_registration_form_to_vendor_received_subject', __( 'Application Received', 'edd_fes' ) );
			$message = EDD_FES()->helper->get_option( 'fes-vendor-new-app-email', '' );
			$type = "user";
			$id = $save_id;
			$args = array( 'permissions' => 'fes-vendor-new-app-email-toggle' );
			EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id );

			do_action ( 'fes_register_form_frontend_pending_vendor_email', $save_id, $values );
		}
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

		$is_a_admin  = EDD_FES()->vendors->user_is_admin( $user_id );

		if ( $is_admin ) {
			if ( !$is_a_admin ) {
				if ( $output ) {
					return __( 'Access Denied: You are not an admin!', 'edd_fes' );
				} else {
					return false;
				}
			}
		} else {
			if ( is_user_logged_in() ) {
				// is application disabled?
				if ( !(bool) EDD_FES()->helper->get_option( 'fes-allow-applications', false ) ) {
					if ( $output ) {
						return sprintf( _x( 'Sorry! Applications to become a %s are currently disabled at this time!', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
				// if is frontend vendor
				if ( EDD_FES()->vendors->user_is_status( 'approved', $this->save_id ) ) {
					if ( $output ) {
						return sprintf( _x( 'You\'re already approved!', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
				// if is suspended vendor
				if ( EDD_FES()->vendors->user_is_status( 'suspended', $this->save_id ) ) {
					if ( $output ) {
						return sprintf( _x( 'Sorry! You\'re currently suspended!', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
				// if is pending vendor
				if ( EDD_FES()->vendors->user_is_status( 'pending', $this->save_id ) ) {
					if ( $output ) {
						return sprintf( _x( 'Your application is still being processed!', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}


			} else {
				// is registration disabled?
				if ( !(bool) EDD_FES()->helper->get_option( 'fes-allow-registrations', false ) ) {
					if ( $output ) {
						return sprintf( __( 'Sorry! Registration to become a %s is currently disabled at this time!', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
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

		$is_a_admin  = EDD_FES()->vendors->user_is_admin( $user_id );

		if ( $is_admin ) {
			if ( !$is_a_admin ) {
				if ( $output ) {
					return __( 'Access Denied: You are not an admin!', 'edd_fes' );
				} else {
					return false;
				}
			}
		} else {
			if ( is_user_logged_in() ) {
				// is application disabled?
				if ( !(bool) EDD_FES()->helper->get_option( 'fes-allow-applications', false ) ) {
					if ( $output ) {
						return sprintf( __( 'Sorry! Applications to become a %s are currently disabled at this time!', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
				// if is frontend vendor
				if ( EDD_FES()->vendors->user_is_status( 'approved', $this->save_id ) ) {
					if ( $output ) {
						return sprintf( __( 'You\'re already approved!', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
				// if is suspended vendor
				if ( EDD_FES()->vendors->user_is_status( 'suspended', $this->save_id ) ) {
					if ( $output ) {
						return sprintf( __( 'Sorry! You\'re currently suspended!', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
				// if is pending vendor
				if ( EDD_FES()->vendors->user_is_status( 'pending', $this->save_id ) ) {
					if ( $output ) {
						return sprintf( __( 'Your application is still being processed!', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
			} else {
				// is registration disabled?
				if ( !(bool) EDD_FES()->helper->get_option( 'fes-allow-registrations', false ) ) {
					if ( $output ) {
						return sprintf( __( 'Sorry! Registration to become a %s is currently disabled at this time!', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
			}
		}
		return true;
	}
}
