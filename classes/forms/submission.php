<?php
class FES_Submission_Form extends FES_Form {

	/** @var string The form ID. */
	public $id = null;

	/** @var string Version of form */
	public $version = '1.0.0';		

	/** @var array Array of fields */
	public $fields = array();

	/** @var string The form's name (registration, contact etc). */
	public $name = 'submission';

	/** @var string Title of the form */
	public $title = 'Submission';

	/** @var int The id of the object the form value is saved to. For a submission form, the $save_id is the post's ID, etc. */
	public $save_id = null;

	/** @var unknown Type of form: 'user', 'post', 'custom'. Dictates where the fields save their values. */
	public $type = 'post';

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
		add_filter( 'fes_templates_to_exclude_render_' . $this->name() . '_form_admin', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_save_' . $this->name() . '_form_admin', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_templates_to_exclude_validate_' . $this->name() . '_form_admin', array( $this, 'fes_templates_to_exclude' ) );
		add_filter( 'fes_render_' . $this->name() . '_form_admin_form', '__return_false' );
		add_filter( 'fes_render_' . $this->name() . '_form_show_args_admin', '__return_false' );
		
		add_action( 'fes_render_form_above_' . $this->name() . '_form', array( $this, 'set_session' ), 10, 2 );
		add_filter( 'fes_render_' . $this->name() . '_form_args_frontend', array( $this, 'set_post_id' ), 10, 4 );
	}

	public function set_title() {
		$title = _x( 'Submission', 'FES Form title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_form_title', $this->title );
		$this->title = $title;		
	}
	
	public function set_post_id( $args, $formobject, $user_id, $readonly ){
		if ( !isset( $args['post_id'] ) ) {
			$args['post_id'] = isset( $_REQUEST['post_id'] ) && absint( $_REQUEST['post_id'] )  ? absint( $_REQUEST['post_id'] ) : -2;
		}
		return $args;
	}

	public function fes_templates_to_exclude( $templates ) {
		array_push( $templates, 'download_format' );
		array_push( $templates, 'download_category' );
		array_push( $templates, 'download_tag' );
		array_push( $templates, 'featured_image' );
		array_push( $templates, 'post_title' );
		array_push( $templates, 'post_excerpt' );
		array_push( $templates, 'post_content' );
		array_push( $templates, 'multiple_pricing' );
		return $templates;
	}

	public function set_session( $save_id, $readonly ){

		if( empty( $save_id ) || $save_id < 0 ) {
			EDD()->session->set( 'fes_is_new', true );
		}

		EDD()->session->set( 'edd_fes_post_id', $save_id );
	}

	public function before_form_save( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$new     = true;
		$pending = false;

		$status  = 'publish';
		if ( $save_id > 0 ) {
			$new     = false;
		}
		
		if ( $new ) {
			if ( ! (bool) EDD_FES()->helper->get_option( 'fes-auto-approve-submissions', false ) ) {
				$status = 'pending';
				$pending = true;
				// new is true
			} else {
				// status is publish
				// pending is true
				// new is true
			}
		} else {
			$current_status = get_post_status( $save_id );
			if ( 'publish' !== $current_status ){
				$pending = true;
				$status  = $current_status;
			} else {
				if ( ! (bool) EDD_FES()->helper->get_option( 'fes-auto-approve-edits', false ) ) {
					$status = 'pending';
					$pending = true;
					// new is false 
				} else{
					$status = 'publish';
					$pending = false;
					// new is false 
				}
			}
		}

		if ( ! ( fes_is_admin() ) ) {
			$save_id = $this->create_or_update_object( $this->type, $values, $user_id, $status, $new );
		}

		EDD()->session->set( 'fes_is_new', $new );
		EDD()->session->set( 'fes_is_pending', $pending );
		do_action( 'fes_before_' . $this->name() . '_form_save_action', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_before_' . $this->name() . '_form_save', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function after_form_save_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$new = EDD()->session->get( 'fes_is_new' );
		if ( EDD_FES()->integrations->is_commissions_active() && $new === true ) {
			$commission = array(
				'user_id' => get_current_user_id()
			);
			update_post_meta( $save_id, '_edd_commission_settings', $commission );
			update_post_meta( $save_id, '_edd_commisions_enabled', '1' );
		}
		do_action( 'fes_submit_submission_form_bottom', $save_id );

		$redirect_to = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', false ) );
		if ( EDD_FES()->vendors->vendor_can_edit_product( $save_id ) ) {
			$redirect_to = add_query_arg( array(
					'task' => 'edit-product'
				), $redirect_to );
			$redirect_to = add_query_arg( array(
					'post_id' => $save_id
				), $redirect_to );
		}
		else {
			$redirect_to = add_query_arg( array(
					'task' => 'dashboard'
				), $redirect_to );
		}

		$output['success'] = true;
		if ( $new ) {
			$output['title'] = __( 'Success', 'edd_fes' );
			$output['message'] = sprintf( _x( 'New %s submitted successfully!', 'FES lowercase singular setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = false ) );
		} else {
			$output['title'] = __( 'Success', 'edd_fes' );
			$output['message'] = sprintf( _x( '%s edited successfully!', 'FES uppercase singular setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) );
		}
		$output['redirect_to'] = $redirect_to;
		if ( $new ) {
			$output['redirect_to'] = apply_filters( 'fes_submission_post_new_redirect', $output['redirect_to'], $save_id, $this->id );
		} else {
			$output['redirect_to'] = apply_filters( 'fes_submission_post_edit_redirect', $output['redirect_to'], $save_id, $this->id );
		}
		$output = apply_filters( 'fes_add_post_redirect', $output, $save_id, $this->id );

		EDD()->session->set( 'edd_fes_post_id', '' );
		
		do_action( 'fes_after_' . $this->name() . '_form_save_frontend_action', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_after_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function create_or_update_object( $type = -2, $values = array(), $user_id = -2, $status = 'pending', $new = true ) {
		if ( $type === -2 ) {
			$type = $this->type;
		}

		$post_author = get_current_user_id();
		$postarr = array(
			'post_type' => 'download',
			'post_status' => $status,
			'post_author' => $post_author,
			'post_title' => isset( $values[ 'post_title' ] ) ? sanitize_text_field( trim( $values[ 'post_title' ] ) ) : '',
			'post_content' => isset( $values[ 'post_content' ] ) ? wp_kses( $values[ 'post_content' ], fes_allowed_html_tags() ) : '',
			'post_excerpt' => isset( $values[ 'post_excerpt' ] ) ? wp_kses( $values[ 'post_excerpt' ], fes_allowed_html_tags() ) : ''
		);
		if ( isset( $values[ 'category' ] ) ) {
			$category                   = $values[ 'category' ];
			$postarr[ 'post_category' ] = is_array( $category ) ? $category : array(
				$category
			);
		}
		if ( isset( $values[ 'tags' ] ) ) {
			$postarr[ 'tags_input' ] = explode( ',', $values[ 'tags' ] );
		}
		$postarr = apply_filters( 'fes_add_post_args', $postarr, $this->id );
		$post_id = 0;
		if ( $new ) {
			$post_id = wp_insert_post( $postarr );
			$this->change_save_id( $post_id );
		} else {

			$postarr['ID'] = $this->save_id;
			wp_update_post( $postarr );
		}

		return $this->save_id;
	}

	public function trigger_notifications_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$new      = EDD()->session->get( 'fes_is_new' );
		$pending  = EDD()->session->get( 'fes_is_pending' );
		$post_id  = $this->save_id;
		if ( $new ) {
			if ( $pending ) {
				// email admin
				$to = apply_filters( 'fes_submission_form_pending_to_admin', edd_get_admin_notice_emails(), $post_id );
				$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
				$from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
				$subject = apply_filters( 'fes_submission_form_to_admin_subject', __( 'New Submission Received', 'edd_fes' ) );
				$message = EDD_FES()->helper->get_option( 'fes-admin-new-submission-email', '' );
				$type = "post";
				$id = $post_id;
				$args = array( 'permissions' => 'fes-admin-new-submission-email-toggle' );
				EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );

				// email user
				$user = new WP_User( $user_id );
				$to = $user->user_email;
				$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
				$from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
				$subject = apply_filters( 'fes_submission_new_form_to_vendor_subject', __( 'Submission Received', 'edd_fes' ) );
				$message = EDD_FES()->helper->get_option( 'fes-vendor-new-submission-email', '' );
				$type = "post";
				$id = $post_id;
				$args = array( 'permissions' => 'fes-vendor-new-submission-email-toggle' );
				EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );
				do_action( 'fes_submission_form_new_pending', $post_id );
			}
			else {
				do_action( 'fes_submission_form_new_published', $post_id );
			}
		} else {
			// submission heading to pending
			if ( $pending ) {
				// email admin
				$to = apply_filters( 'fes_submission_form_published_to_admin', edd_get_admin_notice_emails(), $post_id );
				$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
				$from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
				$subject = apply_filters( 'fes_submission_form_edit_to_admin_subject', __( 'New Submission Edit Received', 'edd_fes' ) );
				$message = EDD_FES()->helper->get_option( 'fes-admin-new-submission-edit-email', '' );
				$type = "post";
				$id = $post_id;
				$args = array( 'permissions' => 'fes-admin-new-submission-edit-email-toggle' );
				EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );
				do_action( 'fes_submission_form_edit_pending', $post_id );
			}
			else {
				do_action( 'fes_submission_form_edit_published', $post_id );
			}
		}
	}

	public function trigger_notifications_admin( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		$new      = EDD()->session->get( 'fes_is_new' );
		$pending  = EDD()->session->get( 'fes_is_pending' );
		$post_id  = $this->save_id;
		if ( $new ) {
			if ( $pending ) {
				// email admin
				$to = apply_filters( 'fes_submission_form_pending_to_admin', edd_get_admin_notice_emails(), $post_id );
				$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
				$from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
				$subject = apply_filters( 'fes_submission_form_to_admin_subject', __( 'New Submission Received', 'edd_fes' ) );
				$message = EDD_FES()->helper->get_option( 'fes-admin-new-submission-email', '' );
				$type = "post";
				$id = $post_id;
				$args = array( 'permissions' => 'fes-admin-new-submission-email-toggle' );
				EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );

				// email user
				$user = new WP_User( $user_id );
				$to = $user->user_email;
				$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
				$from_email = edd_get_option( 'from_email', get_option( 'admin_email' ) );
				$subject = apply_filters( 'fes_submission_new_form_to_vendor_subject', __( 'Submission Received', 'edd_fes' ) );
				$message = EDD_FES()->helper->get_option( 'fes-vendor-new-submission-email', '' );
				$type = "post";
				$id = $post_id;
				$args = array( 'permissions' => 'fes-vendor-new-submission-email-toggle' );
				EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );
				do_action( 'fes_submission_form_new_pending', $post_id );
			}
			else {
				do_action( 'fes_submission_form_new_published', $post_id );
			}
		} else {
			// submission heading to pending
			if ( $pending ) {
				// email admin
				$to = apply_filters( 'fes_submission_form_published_to_admin', edd_get_admin_notice_emails(), $post_id );
				$from_name = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
				$from_email = edd_get_option( 'from_email' , get_option( 'admin_email' ) );
				$subject = apply_filters( 'fes_submission_form_edit_to_admin_subject', __( 'New Submission Edit Received', 'edd_fes' ) );
				$message = EDD_FES()->helper->get_option( 'fes-admin-new-submission-edit-email', '' );
				$type = "post";
				$id = $post_id;
				$args = array( 'permissions' => 'fes-admin-new-submission-edit-email-toggle' );
				EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );
				do_action( 'fes_submission_form_edit_pending', $post_id );
			}
			else {
				do_action( 'fes_submission_form_edit_published', $post_id );
			}
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

		$is_a_vendor = EDD_FES()->vendors->user_is_vendor( $user_id );
		$is_a_admin  = EDD_FES()->vendors->user_is_admin( $user_id );

		if ( $is_admin ) {
			if ( $this->save_id ) {
				$post = get_post( $this->save_id );
				$post_author = $post->post_author;
				// if they are not admin, in the admin, or the author of the post
				if ( !$is_a_admin && ( $post_author !== $user_id ) ) {
					if ( $output ) {
						return sprintf( _x( 'Access Denied: You are not an admin or the %s assigned to this %s', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
			}
			else if ( !$is_a_admin && !$is_a_vendor ) {
					if ( $output ) {
						return sprintf( _x( 'Access Denied: You are not an admin or a %s', 'fes setting for lowercase singular vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
		} else {
			if ( !$is_a_admin && !$is_a_vendor ) {
				if ( $output ) {
					return sprintf( _x( 'Access Denied: You are not an admin or a %s', 'fes setting for lowercase singular vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
				} else {
					return false;
				}
			}

			if ( $this->save_id > 0 && $this->save_id ) {
				if ( !EDD_FES()->vendors->vendor_can_edit_product( $this->save_id ) ) {
					if ( $output ) {
						return sprintf( _x( 'Access Denied: You cannot edit this %s', 'FES lowercase singular setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
			} else {
				if ( !EDD_FES()->vendors->vendor_can_create_product() ) {
					if ( $output ) {
						return sprintf( _x( 'Access Denied: You cannot create %s', 'FES lowercase plural setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = false ) );
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

		$is_a_vendor = EDD_FES()->vendors->user_is_vendor( $user_id );
		$is_a_admin  = EDD_FES()->vendors->user_is_admin( $user_id );

		if ( $is_admin ) {
			if ( $this->save_id ) {
				$post = get_post( $this->save_id );
				$post_author = $post->post_author;
				// if they are not admin, in the admin, or the author of the post
				if ( !$is_a_admin && ( $post_author !== $user_id ) ) {
					if ( $output ) {
						return sprintf( __( 'Access Denied: You are not an admin or the %s assigned to this %s', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
			}
			else if ( !$is_a_admin && !$is_a_vendor ) {
					if ( $output ) {
						return sprintf( _x( 'Access Denied: You are not an admin or a %s', 'fes setting for lowercase singular vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
		} else {
			if ( !$is_a_admin && !$is_a_vendor ) {
				if ( $output ) {
					return sprintf( _x( 'Access Denied: You are not an admin or a %s', 'fes setting for lowercase singular vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
				} else {
					return false;
				}
			}

			if ( $this->save_id ) {
				if ( $this->save_id > 0 && !EDD_FES()->vendors->vendor_can_edit_product( $this->save_id ) ) {
					if ( $output ) {
						return sprintf( _x( 'Access Denied: You cannot edit this %s', 'FES lowercase singular setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = false ) );
					} else {
						return false;
					}
				}
			} else {
				if ( !EDD_FES()->vendors->vendor_can_create_product() ) {
					if ( $output ) {
						return sprintf( _x( 'Access Denied: You cannot create %s', 'FES lowercase plural setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = false ) );
					} else {
						return false;
					}
				}
			}
		}
		return true;
	}
}
